<?php
namespace api\modules\shop\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use app\models\ext\UserOrderUserRel;
use app\models\ext\ShopUser;
use app\models\ext\UserMember;
use app\models\ext\UserOrder;
use \Exception;

class UserController extends \api\modules\shop\base\Controller
{
    /**
     * 用户会员业务逻辑对象
     */
    private $_userService;
    /**
     * 用户会员业务逻辑对象
     */
    public function getUserService()
    {
        if($this->_userService===null) {
            $this->_userService = new \api\modules\shop\services\UserService(['context'=>$this]);
        }
        return $this->_userService;
    }

    /**
     * 会员列表
     * GET请求参数：
     * - page: 分页参数
     * - type: 筛选类型，可选值有"id"、"username"、"phone"
     * - str: 筛选值
     */
    public function actionList($type=null, $str=null)
    {
        $id = $username = $phone = null;
        switch($type) {
            case 'id': $id = $str;break;
            case 'phone': $phone = $str;break;
            case 'username': $username = $str;break;
        }
        $list = [];
        //以下注释的是另一种查询方式，当需要涉及到排序时需要用这种
        // $baseQuery = UserOrder::find()->where(UserOrder::tableName().'.user_id='.ShopUser::tableName().'.user_id');
        // $query = ShopUser::find()->with(['user','orders'])->addSelect(['*','consumptionMoney'=>$baseQuery->select('sum(total_price)')]);

        $query = ShopUser::find()->innerJoinWith([
            'user'=>function($query) use($username){
                $username && $query->andFilterWhere(['like','nickname',$username.'%', false]);
            }])->with([
            'orders'=>function($query){
            $query->select('id,total_price,created')->andWhere(['status'=>UserOrder::STATUS_PAID])->orderBy('id desc')
            ->shop($this->currentShopId);
        }])->shop($this->currentShopId);
        $query->andFilterWhere([ShopUser::tableName().'.phone'=>$phone]);
        $query->andFilterWhere(['user_id'=>$id]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 15
            ]
        ]);

        $models = $dataProvider->getModels();

        foreach($models as $user) {
            $list[] = [
                'id' => $user->user_id,
                'username' => $user->user->nickname,
                'phone' => $user->phone,
                'consumptionTimes' => count($user->orders),
                'totalPrice' => array_sum(ArrayHelper::map($user->orders, 'id', 'total_price')),
                'lastTime' => ArrayHelper::getValue($user->orders, function($obj, $default){
                    return $obj ? date('Y-m-d H:i', $obj[0]->created) : $default;
                }, '-')
            ];
        }

        return [
            'list' => $list,
            'totalPage' => $dataProvider->pagination->pageCount,
        ];
    }

    /**
     * 用户详情
     * GET请求参数：
     * - id: 用户id
     */
    public function actionUserDetail($id)
    {
        $data = [];

        $user = ShopUser::find()->shop($this->currentShopId)
                                ->with(['orders'=>function($query) {
                                    $query->with('activeDesk')->orderBy('created desc');
                                }])
                                ->andWhere(['user_id'=>$id])
                                ->one();

        if(!$user) {
            throw new Exception('用户不存在');
        }

        foreach($user->orders as $order) {
            $data[] = [
                'orderNumber' => $order->trade_no,
                'time' => date('Y-m-d H:i', $order->created),
                'deskNumber' => $order->desk_number,
                'operateUser' => $order->shopAdminUsername,
                'itemNumber' => $order->menuNum,
                'totalPrice' => $order->total_price,
                'peopleNumber' => $order->activeDesk->people_num,
            ];
        }

        return $data;
    }

    /**
     * 会员信息编辑接口
     * POST请求参数：
     * - id: 用户id
     * - sex: 性别
     * - birthday: 生日
     * GET请求参数：
     * - id: 用户id
     */
    public function actionUserEdit($id=0)
    {
        $extra = $editInfo = [];
        $msg = '获取成功';
        $menu = null;
        if(Yii::$app->request->getIsPost()) {
            $post = Yii::$app->request->post();
            if($birthday = ArrayHelper::getValue($post, 'birthday')) {
                if($birthTime = strtotime($birthday)) {
                    $post['birthday'] = $birthTime;
                } else {
                    throw new Exception('生日数据格式错误');
                }
            }
            $user = $this->userService->saveUser($post);
            $msg = '保存成功';
        }elseif($id>0) {
            $user = $this->userService->findUser($id);
            if(!$user) {
                throw new Exception('用户不存在');
            }
            $extra['sex'] = [];
            foreach(UserMember::getSexOptionList() as $value=>$name) {
                $extra['sex'][] = [
                    'name' => $name,
                    'value' => $value,
                ];
            }
        } else {
            throw new Exception('请求方式或参数错误');
        }

        $editInfo = [
            'id' => $user->user_id,
            'sex' => $user->sex,
            'birthday' => date('Y-m-d', $user->birthday),
        ];
        $return = [
            'editInfo' => $editInfo,
        ];
        if($extra) {
            $return = array_merge($return, $extra);
        }
        return $return;
    }
}
