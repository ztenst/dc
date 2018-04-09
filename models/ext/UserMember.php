<?php

namespace app\models\ext;

use Yii;
use app\helpers\Storage;

class UserMember extends \app\models\UserMember
{
    const SEX_UNKNOW = 0;//微信接口可能会返回未知
    const SEX_MAN = 1;
    const SEX_WOMAN = 2;

    private $ordersCount;

    /**
     * 性别选项列表
     * @return array
     */
    public static function getSexOptionList()
    {
        return [
            self::SEX_UNKNOW => '未知',
            self::SEX_MAN => '男',
            self::SEX_WOMAN => '女'
        ];
    }

    public static function findByUnionid($unionid)
    {
        return self::find()->where(['unionid' => $unionid])->one();
    }

    public function getOrders()
    {
        return $this->hasMany(UserOrderUser::className(),['user_id'=>'id']);
    }

    public function getOrdersCount()
    {
        if($this->isNewRecord){
            return null;
        }
        if($this->ordersCount === null){
            $this->ordersCount = $this->getOrders()->innerJoinWith(['order'=>function($query){
                $query->andWhere([UserOrder::tableName().'.status' => UserOrder::STATUS_PAID]);
            }])->count();
        }
        return $this->ordersCount;
    }

    public function getAvatar()
    {
        return $this->avatar;
    }
}