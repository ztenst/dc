<?php
/**
 * This is the template for generating a controller class within a module.
 */

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\module\Generator */

echo "<?php\n";
?>

namespace <?= $generator->getBaseNamespace() ?>;

/**
 * <?= $generator->moduleID ?>模块控制器的基类
 * @version <?= date('Y-m-d'); ?>
 */
class Controller extends \app\base\Controller
{
    public function init()
    {
        parent::init();
    }
}
