<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\gii\generators\model;

use Yii;
use app\base\ActiveQuery;
use yii\gii\CodeFile;

/**
 * This generator will generate one or multiple ActiveRecord classes for the specified database table.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Generator extends \yii\gii\generators\model\Generator
{
    public $generateQuery = true;
    public $queryNs = 'app\models\queries';

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'Model生成器';
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return '该模块生成器不是Yii自带的，若需要修改直接编辑' . __FILE__;
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'ns' => '命名空间',
            'db' => '数据库组件id标识',
            'tableName' => '数据表名',
            'modelClass' => 'Model Class类名',
            'baseClass' => '基类类名',
            'generateRelations' => '生成Relations',
            'generateLabelsFromComments' => '用数据库字段注释生成Labels',
            'generateQuery' => '生成ActiveQuery',
            'queryNs' => 'ActiveQuery Namespace',
            'queryClass' => 'ActiveQuery Class',
            'queryBaseClass' => 'ActiveQuery Base Class',
            'useSchemaName' => 'Use Schema Name',
        ]);
    }

    public function requiredTemplates()
    {
        // @todo make 'query.php' to be required before 2.1 release
        $required = parent::requiredTemplates();
        return array_merge($required, ['modelext.php']);
    }

    public function generate()
    {
        $files = parent::generate();


        //生成model_ext模型
        $extNs = 'app\models\ext';
        foreach ($this->getTableNames() as $tableName) {
            $modelClassName = $this->generateClassName($tableName);
            $queryClassName = $this->generateQueryClassName($modelClassName);

            //生成的query就不要再生成了，以免覆盖
            foreach($files as $k=>$file) {
                if(file_exists($file->path) && strpos($file->path, $queryClassName)!==false) {
                    unset($files[$k]);
                }
            }

            //生成ext/model文件
            $filePath = Yii::getAlias('@' . str_replace('\\', '/', $extNs)) . '/' . $modelClassName . '.php';
            if(file_exists($filePath)) continue;
            $params = [
                'tableName' => $tableName,
                'className' => $modelClassName,
                'extNs' => $extNs,
            ];
            $files[] = new CodeFile(
                $filePath,
                $this->render('modelext.php', $params)
            );
        }

        return $files;
    }
}
