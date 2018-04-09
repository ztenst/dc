<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "area_district".
 *
 * @property integer $id
 * @property string $name
 * @property integer $city_id
 * @property integer $province_id
 * @property integer $sort
 * @property integer $status
 * @property integer $is_deleted
 * @property integer $updated
 * @property integer $created
 */
class AreaDistrict extends \app\base\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'area_district';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'city_id', 'province_id', 'status', 'created'], 'required'],
            [['city_id', 'province_id', 'sort', 'status', 'is_deleted', 'updated', 'created'], 'integer'],
            [['name'], 'string', 'max' => 25],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'city_id' => 'City ID',
            'province_id' => 'Province ID',
            'sort' => 'Sort',
            'status' => 'Status',
            'is_deleted' => 'Is Deleted',
            'updated' => 'Updated',
            'created' => 'Created',
        ];
    }

    /**
     * @inheritdoc
     * @return \app\models\queries\AreaDistrictQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\queries\AreaDistrictQuery(get_called_class());
    }
}
