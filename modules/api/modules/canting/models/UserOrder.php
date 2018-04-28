<?php
namespace api\modules\canting\models;

class UserOrder extends \app\models\ext\UserOrder
{
    
    public function fields()
    {
        return [
            'id',
            'desk_number',
            'total_price',
            'trade_no',
            'created' => function($model){
                return $model->getFormatCreated();
            },
            'status_msg' => function($model){
                return self::$statusArray[$model->status];
            },
            'status',
            'shop'=>function($model){
                return $model->shop->name;
            },
            'menus'=>function($model){
                $menus = [
                    'menus'=>[],
                    'add_menus'=>[],
                    'total'=>0 ,
                    'total_price' => 0
                ];
                foreach ($model->menus as $menu) {
                    if ($menu) {
                        $item = [
                            'name' => $menu->menu_name,
                            'number' => $menu->menu_num,
                            'price' => $menu->menu_price,
                            'attrs' => $menu->menu_attr_info
                        ];
                        $menus['total_price'] += $menu->menu_price * $menu->menu_num;
                        $menus['total'] += $menu->menu_num;
                        array_push($menus['menus'], $item);
                    }
                }
                return $menus;
            }
        ];
    }

}
