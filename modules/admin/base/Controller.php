<?php
namespace app\modules\admin\base;

use app\traits\MetronicHelpers;

class Controller extends \app\base\Controller
{
    use MetronicHelpers;

    public function behaviors()
    {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'rules' => array_merge([
                    [
                        'allow' => true,
                        'controllers' => ['admin/default'],
                        'actions' => ['login', 'logout']
                    ],
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ]
                ],$this->rbacRules())
            ]
        ];
    }

    /**
     * rbacRules规则
     * @return array
     */
    public function rbacRules()
    {
        return [];
    }
}
