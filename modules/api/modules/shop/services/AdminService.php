<?php
namespace api\modules\shop\services;

use Yii;
use yii\helpers\Arrayhelper;
use app\models\ext\ShopAdmin;
use \Exception;

class AdminService extends Service
{

    /**
     * 编辑\新增员工
     * @param array $post 修改参数字段
     * - id: 帐号id
     * - account:账号名
     * - password: 密码
     */
    public function saveStaff($post)
    {
        $id = ArrayHelper::remove($post, 'id');
        if($id===null) {
            $admin = new ShopAdmin([
                'scenario' => ShopAdmin::SCENARIO_SHOP_REGISTER,
                'shop_id' => $this->context->currentShopId,
            ]);
            $admin->loadDefaultValues();
        } else {
            $admin = ShopAdmin::find()->shop($this->context->currentShopId)->andWhere(['id'=>$id])->one();
            $admin->scenario = ShopAdmin::SCENARIO_SHOP_EDIT;
        }
        if($admin->load($post, '') && !$admin->save()) {
            throw new Exception($this->getModelError($admin, '保存失败'));
        }
        return $admin;
    }

    /**
     * 删除指定职员帐号
     * @param integer $id 商家帐号id
     */
    public function deleteStaff($id)
    {
        $admin = ShopAdmin::find()->shop($this->context->currentShopId)->andWhere(['id'=>$id,'role'=>ShopAdmin::ROLE_STAFF])->one();
        if(!$admin) {
            throw new Exception('帐号不存在');
        }
        if(!$admin->delete()) {
            throw new Exception($this->getModelError($admin,'删除失败'));
        }
    }
}
