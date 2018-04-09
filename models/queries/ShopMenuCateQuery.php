<?php

namespace app\models\queries;
use app\models\ext\ShopMenuCate;
use app\models\ext\ShopMenu;
use app\traits\StatusQueryTrait;

/**
 * This is the ActiveQuery class for [[ShopMenuCate]].
 *
 * @see ShopMenuCate
 */
class ShopMenuCateQuery extends \app\base\ActiveQuery
{
    use StatusQueryTrait;

    public function shop($shop_id)
    {
        $this->andWhere(['shop_id'=>$shop_id]);
        return $this;
    }

    /**
     * 增加对分类下菜品量的统计
     * 注意：调用此方法后需要显示指定select的字段，不指定select字段则无法取出其他字段数据
     * 显示调用[addSelect()]方法，例如：ShopMenuCate::find()->addSelect('*')->countMenus()->all();
     * @return ShopMenuCateQuery
     */
    public function countMenus()
    {
        $menuQuery = ShopMenu::find()->select('count(id)')->where('shop_menu.cate_id=shop_menu_cate.id');
        return $this->addSelect(['menuCount'=>$menuQuery]);
    }

}
