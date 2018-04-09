<?php
namespace app\widgets;

use Yii;
use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\helpers\Html;

class SideMenu extends \yii\widgets\Menu
{
    public $options = [
        'class'=>'page-sidebar-menu page-header-fixed',
        'data-keep-expanded' => 'false',
        'data-auto-scroll' => 'true',
        'data-slide-speed' => '200',
        'style' => 'padding-top: 20px',
    ];

    public $itemOptions = [
        'class' => 'nav-item',
        'arrowClass' => 'arrow'
    ];
    /**
     * 菜单分隔区块模板
     * @var string
     */
    public $divisionTextTemplate = "<h3>{label}</h3>";
    public $submenuTemplate = "\n<ul class='sub-menu'>\n{items}\n</ul>\n";
    /**
     * @var string 可展开菜单的链接模板（指有子级菜单的链接）
     * the template used to render the body of a menu which is a link.
     * In this template, the token `{url}` will be replaced with the corresponding link URL;
     * while `{label}` will be replaced with the link text.
     * This property will be overridden by the `template` option set in individual menu items via [[items]].
     * 如果定义的item中没有url元素，则默认使用javascript:;代替链接
     * 提供占位符{icon}表示菜单元素后的图标
     * 提供占位符{arrow}表示菜单元素后的箭头
     */
    public $expandedLinkTemplate = '<a href="{url}" class="nav-link nav-toggle">{icon}<span class="title">{label}</span>{arrow}</a>';
    /**
     * @var string 不可展开的链接模板（指没有子级菜单的链接）
     */
    public $unexpandedLinkTemplate = '<a href="{url}" class="nav-link">{icon}<span class="title">{label}</span>{arrow}</a>';
    /**
     * @var string 链接icon图标的模板，用占位符{icon}表示图标的class样式名
     */
    public $iconTemplate = '<i class="{icon}"></i>';
    /**
     * @var string 箭头html模板结构
     */
    public $arrowTemplate = '<span class="{arrowClass}"></span>';

    public $arrowClass = 'arrow';

    public $activateParents = true;

    public function run()
    {
        if ($this->route === null && Yii::$app->controller !== null) {
            $this->route = Yii::$app->controller->getRoute();
        }
        if ($this->params === null) {
            $this->params = Yii::$app->request->getQueryParams();
        }
        $items = $this->normalizeItems($this->items, $hasActiveChild);
        // var_dump($items);die;
        if (!empty($items)) {
            $options = $this->options;
            $tag = ArrayHelper::remove($options, 'tag', 'ul');

            echo Html::tag($tag, $this->renderItems($items), $options);
        }
    }

    protected function renderItems($items)
    {
        $n = count($items);
        $lines = [];
        foreach ($items as $i => $item) {
            $options = array_merge($this->itemOptions, ArrayHelper::getValue($item, 'options', []));
            $tag = ArrayHelper::remove($options, 'tag', 'li');
            $class = [];
            if ($item['active']) {
                $class[] = $this->activeCssClass;
            }
            if ($i === 0 && $this->firstItemCssClass !== null) {
                $class[] = $this->firstItemCssClass;
            }
            if ($i === $n - 1 && $this->lastItemCssClass !== null) {
                $class[] = $this->lastItemCssClass;
            }
            if (!empty($class)) {
                if (empty($options['class'])) {
                    $options['class'] = implode(' ', $class);
                } else {
                    $options['class'] .= ' ' . implode(' ', $class);
                }
            }

            $menu = $this->renderItem($item);
            if (!empty($item['items'])) {
                $submenuTemplate = ArrayHelper::getValue($item, 'submenuTemplate', $this->submenuTemplate);
                $menu .= strtr($submenuTemplate, [
                    '{items}' => $this->renderItems($item['items']),
                ]);
            }
            $lines[] = Html::tag($tag, $menu, $options);
        }

        return implode("\n", $lines);
    }

    protected function renderItem($item)
    {
        $linkTemplate = $this->unexpandedLinkTemplate;
        //a标签url处理
        if (!isset($item['url'])) {
            if(!isset($item['items'])) {//既没url，也没子级，视作分割区块
               return strtr($this->divisionTextTemplate, ['{label}'=>$item['label']]);
           }
            $item['url'] = 'javascript:;';
        }
        //可展开菜单元素的处理
        $arrowClass = ArrayHelper::getValue($item, 'arrowClass', '') . ' ' .$this->arrowClass;
        $arrow = '';
        if (isset($item['items'])) {
            $arrow = strtr($this->arrowTemplate, ['{arrowClass}' => $arrowClass]);//加箭头
            $linkTemplate = $this->expandedLinkTemplate;//设置可展开
        }
        //a标签图标处理
        $icon = '';
        if(!empty($item['icon'])) {
            $icon = strtr($this->iconTemplate, [
                '{icon}' => $item['icon'],
            ]);
        }
        $template = ArrayHelper::getValue($item, 'template', $linkTemplate);

        return strtr($template, [
            '{url}' => Html::encode(Url::to($item['url'])),
            '{label}' => $item['label'],
            '{icon}' => $icon,
            '{arrow}' => $arrow,
        ]);

    }

    protected function normalizeItems($items, &$active)
    {
        foreach ($items as $i => $item) {
            if (isset($item['visible']) && !$item['visible']) {
                unset($items[$i]);
                continue;
            }
            if (!isset($item['label'])) {
                $item['label'] = '';
            }
            $encodeLabel = isset($item['encode']) ? $item['encode'] : $this->encodeLabels;
            $items[$i]['label'] = $encodeLabel ? Html::encode($item['label']) : $item['label'];
            $hasActiveChild = false;
            if (isset($item['items'])) {
                $items[$i]['items'] = $this->normalizeItems($item['items'], $hasActiveChild);
                if (empty($items[$i]['items']) && $this->hideEmptyItems) {
                    unset($items[$i]['items']);
                    if (!isset($item['url'])) {
                        unset($items[$i]);
                        continue;
                    }
                }
            }

            if ($this->activateParents && $hasActiveChild || $this->activateItems && $this->isItemActive($item) || isset($item['active'])&&$item['active']) {//根据子菜单激活状态判断 || 根据直接设定的active属性判断
                $active = $items[$i]['active'] = true;
                if($hasActiveChild) {
                    $items[$i]['options']['class'] = 'open ' . ArrayHelper::getValue($this->itemOptions, 'class', '');
                    $items[$i]['arrowClass'] = 'open';
                }
            } else {
                $items[$i]['active'] = false;
            }

            // if (!isset($item['active'])) {
            //     if ($this->activateParents && $hasActiveChild || $this->activateItems && $this->isItemActive($item)) {
            //         $active = $items[$i]['active'] = true;
            //         if($hasActiveChild) {
            //             $items[$i]['options']['class'] = 'open ' . ArrayHelper::getValue($this->itemOptions, 'class', '');
            //             $items[$i]['arrowClass'] = 'open';
            //         }
            //     } else {
            //         $items[$i]['active'] = false;
            //     }
            // } elseif ($item['active']) {
            //     $active = true;
            // }
        }

        return array_values($items);
    }

    /**
     * Checks whether a menu item is active.
     * This is done by checking if [[route]] and [[params]] match that specified in the `url` option of the menu item.
     * When the `url` option of a menu item is specified in terms of an array, its first element is treated
     * as the route for the item and the rest of the elements are the associated parameters.
     * Only when its route and parameters match [[route]] and [[params]], respectively, will a menu item
     * be considered active.
     * @param array $item the menu item to be checked
     * @return boolean whether the menu item is active
     */
    protected function isItemActive($item)
    {
        if (isset($item['url']) && is_array($item['url']) && isset($item['url'][0])) {
            $route = Yii::getAlias($item['url'][0]);
            if ($route[0] !== '/' && Yii::$app->controller) {
                $route = Yii::$app->controller->module->getUniqueId() . '/' . $route;
            }
            if (ltrim($route, '/') !== $this->route) {
                return false;
            }
            unset($item['url']['#']);
            if (count($item['url']) > 1) {
                $params = $item['url'];
                unset($params[0]);
                foreach ($params as $name => $value) {
                    if ($value !== null && (!isset($this->params[$name]) || $this->params[$name] != $value)) {
                        return false;
                    }
                }
            }

            return true;
        }

        return false;
    }
}
