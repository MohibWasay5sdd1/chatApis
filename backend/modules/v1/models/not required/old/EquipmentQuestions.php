<?php

namespace api\modules\v1\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "equipmentquestions".
 *
 * @property int $equipmentQuestionId
 * @property string $equipmentQuestionTitle
 * @property int $equipmentSubCategoryId
 *
 * @property Equipmentsubcategories $equipmentSubCategory
 * @property Inspectionremarks[] $inspectionremarks
 */
class EquipmentQuestions extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'equipmentquestions';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['equipmentQuestionTitle', 'equipmentSubCategoryId'], 'required'],
            [['equipmentSubCategoryId'], 'integer'],
            [['equipmentQuestionTitle'], 'string', 'max' => 300],
            [['status'], 'string'],
            [['equipmentSubCategoryId'], 'exist', 'skipOnError' => true, 'targetClass' => Equipmentsubcategories::className(), 'targetAttribute' => ['equipmentSubCategoryId' => 'equipmentSubCategoryId']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function fields()
{
    $fields = parent::fields();

    // remove fields that contain sensitive information
    unset($fields['status']);

    return $fields;
}
    public function attributeLabels()
    {
        return [
            'equipmentQuestionId' => 'Equipment Question ID',
            'equipmentQuestionTitle' => 'Equipment Question Title',
            'equipmentSubCategoryId' => 'Equipment Sub Category ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEquipmentSubCategory()
    {
        return $this->hasOne(Equipmentsubcategories::className(), ['equipmentSubCategoryId' => 'equipmentSubCategoryId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getInspectionremarks()
    {
        return $this->hasMany(Inspectionremarks::className(), ['equipmentQuestionId' => 'equipmentQuestionId']);
    }
}
