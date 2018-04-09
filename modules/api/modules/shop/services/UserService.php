<?php
namespace api\modules\shop\services;

use Yii;
use app\models\ext\ShopUser;
use yii\helpers\ArrayHelper;
use \Exception;

class UserService extends Service
{
    /**
     * 修改商家会员信息
     * @param array $post POST请求参数，允许以下参数
     * - sex: 性别
     * - birthday: 生日
     */
    public function saveUser($post)
    {
        $id = ArrayHelper::remove($post, 'id');
        if(!$id) {
            throw new Exception('参数错误');
        }
        $user = $this->findUser($id);
        if(!$user) {
            throw new Exception('用户不存在');
        }
        if(!($user->load($post, '') && $user->save())) {
            throw new Exception($this->getModelError($user, '保存失败'));
        }
        return $user;
    }

    /**
     * 根据用户id查找用户
     * @return null|ShopUser
     */
    public function findUser($id)
    {
        return ShopUser::find()->shop($this->context->currentShopId)
                                ->andWhere(['user_id'=>$id])
                                ->one();
    }
}
