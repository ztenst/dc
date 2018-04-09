<?php
namespace api\modules\shop\services;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\data\ActiveDataProvider;
use app\models\ext\ShopMenu;
use app\models\ext\ShopMenuCate;
use app\models\ext\ShopMenuUnit;
use \Exception;

/**
 * 菜单\菜品\菜品分类相关业务逻辑处理服务层
 */
class MenuService extends Service
{
    /**
     * 新增/编辑菜品
     * @param array $post POST提交参数数组
     * id: 需要编辑的菜品数据id（可选，新增时无此参数）
     * name: 菜品名称
     * price: 单价
     * cate_id: 分类id
     * image: 图片封面地址key
     * unit_id: 单位id
     * attrs: 规格数据， 需要前端处理成json字符串，json格式见[normalizeAttrs()]注释
     * status: 状态标识id
     * isRecommend: 是否推荐菜，默认否
     * isNew: 是否新品，默认否
     * @throws 保存失败时抛出异常
     * @return ShopMenu
     */
    public function saveMenu($post)
    {
        $id = ArrayHelper::remove($post, 'id', 0);
        $attrs = ArrayHelper::remove($post, 'attrs');
        $post['isNew'] = ArrayHelper::getValue($post, 'isNew', ShopMenu::NEW_NO);
        $post['isRecommend'] = ArrayHelper::getValue($post, 'isRecommend', ShopMenu::RECOMMEND_NO);
        if($id>0) {
            if(!($menu = ShopMenu::find()->shop($this->context->currentShopId)->andWhere(['id'=>$id])->one())) {
                throw new Exception('菜品不存在');
            }
        } else {
            $menu = new ShopMenu;
            $menu->loadDefaultValues();
        }
        $menu->scenario = ShopMenu::SCENARIO_SHOP_EDIT;
        $menu->shop_id = $this->context->currentShopId;
        if($attrs) {
            $attrs = $menu->normalizeAttrs($attrs);
            $menu->setConfigField('attrs', $attrs);
        }
        if(!($menu->load($post,'') && $menu->save())) {
            throw new Exception($this->getModelError($menu, '保存失败'));
        }
        return $menu;
    }

    /**
     * 删除菜品
     * @param integer $id 菜品id
     */
    public function deleteMenu($id)
    {
        $menu = ShopMenu::find()->shop($this->context->currentShopId)->andWhere(['id'=>$id])->one();
        if(!$menu) {
            throw new Exception('数据不存在');
        }
        if(!$menu->delete()) {
            throw new Exception($this->getModelError($menu, '删除失败'));
        }
    }

    /**
     * 编辑菜单分类
     * @param array $post POST提交参数数组
     * id: 需要编辑的分类id（可选，新增时无此参数）
     * name: 分类名称
     * status: 状态
     * @throws 保存失败时抛出异常
     * @return ShopMenuCate
     */
    public function saveMenuCate($post)
    {
        $id = ArrayHelper::remove($post, 'id', 0);
        if($id>0) {
            $menuCate = ShopMenuCate::find()->andWhere(['id'=>$id])
                                            ->shop($this->context->currentShopId)
                                            ->one();
            if(!$menuCate) {
                throw new Exception('数据不存在');
            }
        } else {
            $menuCate = new ShopMenuCate([
                'shop_id' => $this->context->currentShopId,
            ]);
            $menuCate->loadDefaultValues();
        }
        $menuCate->scenario = ShopMenuCate::SCENARIO_SHOP_EDIT;
        if(!($menuCate->load($post,'') && $menuCate->save())) {
            throw new Exception($this->getModelError($menuCate, '保存失败'));
        }
        return $menuCate;
    }

    /**
     * 删除指定菜单分类
     * @param integer $id 菜单分类id
     */
    public function deleteMenuCate($id)
    {
        $menuCate = ShopMenuCate::find()->where(['id'=>$id, 'shop_id'=>$this->context->currentShopId])->one();
        if(!$menuCate) {
            throw new Exception('数据不存在');
        }
        try {
            Yii::$app->db->transaction(function() use($menuCate){
                foreach($menuCate->menus as $menu) {
                    $menu->delete();
                }
                $menuCate->delete();
            });
        } catch(Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * 保存修改菜品单位数据
     * @param array $post post参数数组，最多包含id和name两个字段
     * @return ShopMenuUnit
     */
    public function saveMenuUnit($post)
    {
        if($id = ArrayHelper::remove($post, 'id')) {
            $unit = ShopMenuUnit::find()->shop($this->context->currentShopId)
                                        ->andWhere(['id'=>$id])
                                        ->one();
            if(!$unit) {
                throw new Exception('单位数据未找到');
            }
        } else {
            $unit = new ShopMenuUnit;
            $unit->loadDefaultValues();
        }
        $unit->shop_id = $this->context->currentShopId;
        $unit->scenario = ShopMenuUnit::SCENARIO_SHOP_EDIT;
        if((!$post || $unit->load($post, '')) && $unit->save()) {
            return $unit;
        }
        throw new Exception($this->getModelError($unit, '保存失败'));
    }

    /**
     * 删除菜品单位
     * @param integer $id 菜品单位id
     */
    public function deleteMenuUnit($id)
    {
        $unit = ShopMenuUnit::find()->shop($this->context->currentShopId)
                                    ->andWhere(['id'=>$id])
                                    ->one();
        if(!$unit) {
            throw new Exception('单位数据未找到');
        }
        if(!$unit->delete()) {
            throw new Exception($this->getModelError($unit, '删除失败'));
        }
    }
}
