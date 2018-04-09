<?php
namespace app\base;
/**
 * 项目基类AR类
 * @author weibaqiu
 * @version 2016-11-17
 */
use Yii;

class ActiveRecord extends \yii\db\ActiveRecord
{
    public function init()
    {
        parent::init();
        $this->on(self::EVENT_BEFORE_VALIDATE, [$this, 'modifyCreatedAndUpdated']);
    }

    public function attributeLabels()
    {
        $attributeLabels = parent::attributeLabels();
        return array_merge($attributeLabels, [
            'created' => '添加时间',
            'updated' => '最后更新时间'
        ]);
    }

    final public function modifyCreatedAndUpdated()
    {
        if($this->canSetProperty('created') && $this->canSetProperty('updated')) {
            if($this->getIsNewRecord()) {
                $this->created = time();
            } else {
                $this->updated = time();
            }
        }
    }

    /**
     * 加载单条记录模型
     * @param  integer  $id          要查找模型的id
     * @param  boolean $createIfNull 如果未查找到记录是否创建新的对象
     * @return ActiveRecord|null
     */
    public static function loadModel($id, $createIfNull=false)
    {
        if($id>0 && $ar = static::findOne($id)) {
            return $ar;
        } elseif($createIfNull) {
            return new static;
        }
    }

    public function getFormatCreated(array $format=['dateTime','php:Y-m-d H:i:s'])
    {
        return Yii::$app->formatter->format($this->created, $format);
    }
}
