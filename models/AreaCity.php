<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "area_city".
 *
 * @property integer $id
 * @property string $name
 * @property integer $province_id
 * @property integer $sort
 * @property integer $status
 * @property integer $is_deleted
 * @property integer $updated
 * @property integer $created
 */
class AreaCity extends \app\base\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'area_city';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'province_id', 'status', 'created'], 'required'],
            [['province_id', 'sort', 'status', 'is_deleted', 'updated', 'created'], 'integer'],
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
     * @return \app\models\queries\AreaCityQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\queries\AreaCityQuery(get_called_class());
    }
}
