<?php

use yii\db\Migration;

class m170518_055023_shop_rbac_init extends \yii\rbac\DbManager\m140506_102106_rbac_init
{
    protected function getAuthManager()
    {
        return Yii::$app->shopAuthManager;
    }
}
