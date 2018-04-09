<?php

namespace app\models\ext;

use Yii;

/**
 * This is the model class for table "area_province".
 * @vserion 2017-05-11 14:11:22通过gii生成
 */
class AreaProvince extends \app\models\AreaProvince
{
    /**
     * 启用状态
     */
    const STATUS_ACTIVE = 1;
    /**
     * 禁用状态
     */
    const STATUS_INACTIVE = 0;

    /**
     * 获得状态列表
     * @return array
     */
    public static function getStatusList()
    {
        return [
            self::STATUS_ACTIVE => '启用',
            self::STATUS_INACTIVE => '禁用',
        ];
    }

    public function rules()
    {
        return array_merge([
            //需要cityCount属性<=0时，才能通过验证
            [['cityCount'], 'compare', 'compareValue'=>0, 'operator'=>'<=', 'message'=>'该省份下有数据，请先删除下级数据']
        ], parent::rules());
    }

    /**
     * 字段labels
     * @return array
     */
    public function attributeLabels()
    {
        $attributeLabels = parent::attributeLabels();
        return array_merge($attributeLabels, [
            'status' => '状态',
            'statusText' => '状态',
            'name' => '名称',
        ]);
    }

    /**
     * 获取状态文字
     * @return string 状态文字
     */
    public function getStatusText()
    {
        $statusList = self::getStatusList();
        return $statusList[$this->status];
    }

    /**
     * 重要删除逻辑修改
     * 由于该表的重要性，所有删除逻辑改为逻辑删除
     * 该函数会影响到[[delete()]]、[[deleteInternal()]]等删除函数
     */
    public static function deleteAll($condition = '', $params = [])
    {
        $command = static::getDb()->createCommand();
        $command->update(static::tableName(), ['is_deleted'=>1, 'status'=>self::STATUS_INACTIVE, 'updated'=>time()], $condition, $params);

        return $command->execute();
    }

    /**
     * 下属城市
     * @return Query
     */
    public function getCities()
    {
        return $this->hasMany(AreaCity::className(), ['province_id'=>'id']);
    }

    /**
     * 获取下属城市数量
     * @return integer
     */
    public function getCityCount()
    {
        return $this->getCities()->count();
    }

    /**
     * 重写find()以设置默认作用域
     */
    public static function find()
    {
        return parent::find()->undeleted();
    }
}
