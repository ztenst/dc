<?php

namespace app\models\ext;

use Yii;

/**
 * This is the model class for table "area_district".
 * @vserion 2017-05-11 14:11:40通过gii生成
 */
class AreaDistrict extends \app\models\AreaDistrict
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

    /**
     * 验证规则
     * @return array
     */
    public function rules()
    {
        return array_merge([
            [['province_id'], 'default', 'value'=>function($model,$attribute) {
                if($city = AreaCity::findOne($model->city_id)) {
                    return $city->province_id;
                }
            }]
        ],parent::rules());
    }

    /**
     * 字段labels
     * @return array
     */
    public function attributeLabels()
    {
        $attributeLabels = parent::attributeLabels();
        return array_merge($attributeLabels, [
            'city_id' => '城市',
            'status' => '状态',
            'statusText' => '状态',
            'name' => '地区名称',
        ]);
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
     * 获取状态文字
     * @return string 状态文字
     */
    public function getStatusText()
    {
        $statusList = self::getStatusList();
        return $statusList[$this->status];
    }

    /**
     * 重写find()以设置默认作用域
     */
    public static function find()
    {
        return parent::find()->undeleted();
    }

    /**
     * 获取所属城市
     * @return Query
     */
    public function getCity()
    {
        return $this->hasOne(AreaCity::className(), ['id'=>'city_id']);
    }

    /**
     * 获取所属省份
     * @return Query
     */
    public function getProvince()
    {
        return $this->getCity()->getProvince();
    }
}
