<?php
namespace app\modules\admin\controllers;

use app\modules\admin\base\Controller;

class  RbacController extends Controller
{
    public function actionList()
    {
        return $this->render('list');
    }

    /**
     * 角色\权限组编辑和添加
     */
    public function actionEdit()
    {
        return $this->render('edit');
    }
}
