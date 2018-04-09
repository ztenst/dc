<?php
namespace app\traits;

use Yii;

trait SideMenu
{
    /**
     * 将指定的route设为激活状态，当访问这些指定的route页面时，这些route为激活状态
     * 可在菜单item元素中设置active元素使用，如['active'=>$this->setRouteActive(['admin/site/index'])]
     * @param array $routes
     */
    public function setRouteActive(array $routes)
    {
        $route = Yii::$app->controller->route;
        foreach($routes as $r) {
            $r = str_replace('*', '', $r);
            if(strpos($route, $r)!==false) {
                return true;
            }
        }
        return false;
    }
}
