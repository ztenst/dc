<?php

namespace app\modules\distribution\base;

use app\traits\MetronicHelpers;

/**
 * distribution模块控制器的基类
 * @version 2017-05-12 */
class Controller extends \app\base\Controller
{
    use MetronicHelpers;

    public function init()
    {
        parent::init();
    }

    public function behaviors()
    {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'rules' => array_merge([
                    [
                        'allow' => true,
                        'controllers' => ['distribution/default'],
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
