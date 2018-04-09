<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "area_province".
 *
 * @property integer $id
 * @property string $name
 * @property integer $sort
 * @property integer $status
 * @property integer $is_deleted
 * @property integer $updated
 * @property integer $created
 */
class AreaProvince extends \app\base\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'area_province';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'status', 'created'], 'required'],
            [['sort', 'status', 'is_deleted', 'updated', 'created'], 'integer'],
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
            'sort' => 'Sort',
            'status' => 'Status',
            'is_deleted' => 'Is Deleted',
            'updated' => 'Updated',
            'created' => 'Created',
        ];
    }

    /**
     * @inheritdoc
     * @return \app\models\queries\AreaProvinceQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\queries\AreaProvinceQuery(get_called_class());
    }
}
