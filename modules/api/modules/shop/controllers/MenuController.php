<?php
namespace api\modules\shop\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use app\models\ext\ShopMenu;
use app\models\ext\ShopMenuUnit;
use app\models\ext\ShopMenuCate;
use app\models\ext\ShopAttr;
use api\modules\shop\models\ShopMenuSearch;
use \Exception;

class MenuController extends \api\modules\shop\base\Controller
{
    /**
     * 餐桌相关业务逻辑层对象
     * @var DeskService
     */
    private $_menuService;

    /**
     * 餐桌相关业务逻辑层对象
     * @return DeskService
     */
    public function getMenuService()
    {
        if($this->_menuService===null) {
            return $this->_menuService = new \api\modules\shop\services\MenuService(['context'=>$this]);
        }
        return $this->_menuService;
    }

    /**
     * 菜单列表页|菜品其他固定数据
     */
    public function actionMenuListInfo()
    {
        $status = $units = $cateStatus = $menuCates = [];
        $menuCateData = ShopMenuCate::find()->shop($this->currentShopId)->select(['id','name'])->all();
        $menuCates['inputName'] = 'cate_id';
        $menuCates['placeholder'] = '菜品分类';
        $menuCates['list'][] = [
            'name' => '全部分类',
            'value' => '',
        ];
        foreach($menuCateData as $cate) {
            $menuCates['list'][] = [
                'value' => $cate->id,
                'name' => $cate->name,
            ];
        }
        $status['inputName'] = 'status';
        $status['placeholder'] = '菜品状态';
        $status['list'][] = [
            'name' => '全部状态',
            'value' => '',
        ];
        foreach(ShopMenu::getStatusList() as $k=>$v) {
            $status['list'][] = [
                'value' => $k,
                'name' => $v,
            ];
        }

        $data = [
            'menuCate' => $menuCates,
            'menuStatus' => $status,
        ];
        return $data;
    }
    /**
     * 菜单列表页|菜单列表页菜单数据
     * GET请求参数
     * cate_id:分类id
     * status: 状态
     * name: 菜品名
     * page: 翻页参数
     */
    public function actionMenuList()
    {
        $menuSearch = new ShopMenuSearch;
        $dataProvider = new ActiveDataProvider([
            'query' => $menuSearch->search(Yii::$app->request->get())->shop($this->currentShopId),
            'pagination' => [
                'pageSize' => 21
            ]
        ]);

        $list = [];

        $units = ShopMenuUnit::find()->shop($this->currentShopId)->indexBy('id')->all();

        foreach($dataProvider->getModels() as $menu) {
            $unit = ArrayHelper::getValue($units, $menu->unit_id, '');
            $list[] = [
                'id' => $menu->id,
                'cate_id' => $menu->cate_id,
                'name' => $menu->name,
                'price' => $menu->price,
                'unit' => $unit ? '/'.$unit->name  : $unit->name,
                'image' => $menu->getImage(),
                'status' => $menu->status,
                'statusText' => $menu->statusText,
            ];
        }

        return [
            'list' => $list,
            'totalPage' => $dataProvider->pagination->pageCount,
        ];
    }

    /**
     * 菜品编辑页|其他固定信息接口
     */
    public function actionMenuEditInfo()
    {
        $cates = ArrayHelper::map(ShopMenuCate::find()->shop($this->currentShopId)->all(), 'id', 'name');
        $units = ArrayHelper::map(ShopMenuUnit::find()->shop($this->currentShopId)->all(), 'id', 'name');
        $attributes = ArrayHelper::map(ShopAttr::find()->shop($this->currentShopId)->all(), 'id', 'name');

        return [
            'cates' => $cates,
            'units' => $units,
            'attributes' => $attributes,
        ];
    }

    /**
     * 菜品编辑页|新增/编辑菜品接口
     * POST请求参数：
     * - id: 菜品id
     * - name: 菜品名称
     * - price: 菜品单价
     * - cate_id: 所属分类id
     * - image: 图片地址key
     * - unit_id: 单位id
     * - attrs: 规格属性字符串json对象，与数据库保存格式一样
     * - stock: 每日库存
     * - cplb: 菜品类别json数组
     * [
     *      {
     *      "inputName": "isRecommend",
     *      "value": 0
     *      },
     *      {
     *      "inputName": "isNew",
     *      "value": 0
     *      }
     * ]
     */
    public function actionMenuEdit($id=0)
    {
        $extra = [];
        $msg = '获取成功';
        $menu = null;
        if(Yii::$app->request->getIsPost()) {
            if(!($post = Yii::$app->request->post())) {
                throw new Exception('参数错误');
            }
            $attrs = ArrayHelper::remove($post, 'attrs');
            if($attrs && ($attrs = Json::decode($attrs))) {
                $post['attrs'] = $attrs;
            }
            $cplb = ArrayHelper::remove($post, 'cplb');
            if($cplb && ($cplb = Json::decode($cplb))) {
                $cplb = ArrayHelper::index($cplb, 'inputName');
                $post = array_merge($post, ArrayHelper::map($cplb, 'inputName', 'value'));
            }
            $menu = $this->getMenuService()->saveMenu($post);
            $msg = '保存成功';
        }else{
            $extra['sizeAttrName'] = ShopMenu::getSizeAttrName();
            $menuCates = ShopMenuCate::find()->shop($this->currentShopId)->select('id,name')->all();
            $extra['menuCates']['list'] = $extra['units']['list'] = $extra['status']['list'] = [];
            $extra['menuCates']['inputName'] = 'cate_id';
            $extra['menuCates']['placeholder'] = '分类';
            foreach($menuCates as $menuCate) {
                $extra['menuCates']['list'][] = [
                    'name' => $menuCate->name,
                    'value' => $menuCate->id,
                ];
            }
            $units = ShopMenuUnit::find()->shop($this->currentShopId)->select('id,name')->all();
            $extra['units']['inputName'] = 'unit_id';
            $extra['units']['placeholder'] = '单位';
            foreach($units as $unit) {
                $extra['units']['list'][] = [
                    'name' => $unit->name,
                    'value' => $unit->id,
                ];
            }
            $extra['status']['inputName'] = 'status';
            $extra['status']['placeholder'] = '状态';
            foreach(ShopMenu::getStatusList() as $value=>$name) {
                $extra['status']['list'][] = [
                    'name' => $name,
                    'value' => $value,
                ];
            }
            //傻逼产品设计，明明可以分开两行做radio选择是和否，却放一行做个checkbox
            $extra['cplb'] = [
                'placeholder' => '菜品类别',
                'list' => [
                    ['name'=>'推荐菜品', 'inputName'=>'isRecommend','value'=>ShopMenu::RECOMMEND_NO],
                    ['name'=>'新上菜品', 'inputName'=>'isNew','value'=>ShopMenu::NEW_NO],
                ]
            ];
        }
        if($id>0) {
            $menu = ShopMenu::find()->shop($this->currentShopId)->andWhere(['id'=>$id])->one();
            if(!$menu) {
                throw new Exception('菜品不存在');
            }
        }
        return $this->menuEditReturn($menu, $msg, $extra);
    }

    /**
     * 菜单编辑返回格式化函数
     */
    private function menuEditReturn($menu, $msg, $extra=[])
    {
        $editInfo = [];
        if($menu) {
            $editInfo = [
                'id' => $menu->id,
                'name' => $menu->name,
                'cate_id' => $menu->cate_id,
                'image' => $menu->image,
                'price' => $menu->price,
                'unit_id' => $menu->unit_id,
                'attrs' => $menu->getConfigField('attrs'),
                'stock' => $menu->stock,
                'status' => $menu->status,
                'cplb' => [
                    [
                        'inputName' => 'isNew',
                        'value' => $menu->isNew,
                    ],
                    [
                        'inputName' => 'isRecommend',
                        'value' => $menu->isRecommend,
                    ]
                ]
            ];
        }

        $return = [
            'msg' => $msg,
            'editInfo' => $editInfo,
        ];
        if($extra) {
            $return = array_merge($return, $extra);
        }
        return $return;
    }

    /**
     * 删除菜品接口
     * POST参数：
     * - id: 要删除菜品的id
     */
    public function actionMenuDelete()
    {
        $id = Yii::$app->request->post('id');
        if(!$id) {
            throw new Exception('请求方式或参数错误');
        }
        $this->menuService->deleteMenu($id);
        return '操作成功';
    }

    /**
     * 菜单分类列表页|菜单分类列表数据接口
     */
    public function actionMenuCateList()
    {
        $cates = ShopMenuCate::find()->shop($this->currentShopId)->countMenus()->addSelect('shop_menu_cate.*')->all();

        $data = [];
        foreach($cates as $cate) {
            $data[] = [
                'id' => $cate->id,
                'name' => $cate->name,
                'menuCount' => $cate->menuCount,
                'showType' => $cate->showTypeText,
            ];
        }
        return $data;
    }

    /**
     * 菜单分类编辑页|新增\编辑菜单分类接口
     * POST请求参数：
     * id: 需要编辑的分类id（可选，新增时无此参数）
     * name: 分类名称
     * status: 状态
     */
    public function actionMenuCateEdit($id=0)
    {
        $extra = [];
        $menuCate = null;
        $msg = '获取成功';
        if(Yii::$app->request->getIsPost()) {
            $post = Yii::$app->request->post();
            $menuCate = $this->getMenuService()->saveMenuCate($post);
            $msg = '保存成功';
        } else{
            $extra['showType']['inputName'] = 'show_type';
            $extra['showType']['placeholder'] = '状态';
            foreach(ShopMenuCate::getShowTypeList() as $value=>$name) {
                $extra['showType']['list'][] = [
                    'name' => $name,
                    'value' => $value,
                ];
            }
        }
        if($id>0) {
            $menuCate = ShopMenuCate::find()->shop($this->currentShopId)->andWhere(['id'=>$id])->one();
            if(!$menuCate) {
                throw new Exception('菜品分类数据未找到');
            }
        }

        return $this->menuCateEditReturn($menuCate, $msg, $extra);
    }

    /**
     * 分类编辑返回函数
     */
    private function menuCateEditReturn($menuCate, $msg, $extra=[])
    {
        $menuInfo = [];
        if($menuCate) {
            $menuInfo = [
                'id' => $menuCate->id,
                'name' => $menuCate->name,
                'show_type' => $menuCate->show_type,
            ];
        }

        $return = [
            'msg' => $msg,
            'editInfo' => $menuInfo,
        ];
        if($extra) {
            $return = array_merge($return, $extra);
        }
        return $return;
    }

    /**
     * 菜单分类列表页|删除指定菜单分类
     * POST参数：
     * id: 菜单分类id
     */
    public function actionMenuCateDelete()
    {
        if(($id = Yii::$app->request->post('id', null))===null) {
            throw new Exception('请求方式或参数错误');
        }
        $this->menuService->deleteMenuCate($id);
        return '删除成功';
    }

    /**
     * 菜品编辑页|获得菜品详情信息
     */
    public function actionMenuDetail($id)
    {
        if($id<=0) {
            throw new Exception('参数错误');
        }
        $model = $this->loadShopMenu($id);

        $data = [
            'name' => $model->name,
            'cate_id' => $model->cate_id,
            'price' => $model->price,
            'unit_id' => $model->unit_id,
        ];
    }

    /**
     * 加载获取菜品模型对象
     * @param  integer $id 菜品id，编辑时用
     * @throws Exception 找不到帐号时抛出异常
     * @return ShopmMenu 菜品信息对象
     */
    private function loadShopMenu($id)
    {
        $id = intval($id);
        if($id>0) {
            if(($model=ShopMenu::find()->where(['id'=>$id])->shop($this->currentShopId)->one())===null) {
                throw new Exception('找不到该菜品');
            }
        } else {
            $model = new ShopMenu([
                'shop_id' => $this->currentShopId,
            ]);
        }
        return $model->loadDefaultValues();
    }

    /**
     * 菜品单位列表页
     */
    public function actionUnitList()
    {
        $units = ShopMenuUnit::find()->shop($this->currentShopId)->select('id,name')->all();
        return $units;
    }

    /**
     * 新增\编辑菜品单位
     * POST参数：
     * - id: 单位id（可选），修改时必须
     * - name: 单位名称
     */
    public function actionUnitEdit()
    {
        if(!Yii::$app->request->getIsPost()) {
            throw new Exception('请求方式或参数错误');
        }
        $unit = $this->menuService->saveMenuUnit(Yii::$app->request->post());
        return [
            'id' => $unit->id,
            'msg' => '保存成功',
        ];
    }

    /**
     * 删除菜品单位
     * POST请求数据：
     * - id: 要删除的菜品单位id
     */
    public function actionUnitDelete()
    {
        if(($id = Yii::$app->request->post('id', null))===null) {
            throw new Exception('请求方式或参数错误');
        }
        $this->menuService->deleteMenuUnit($id);
        return '删除成功';
    }
}
