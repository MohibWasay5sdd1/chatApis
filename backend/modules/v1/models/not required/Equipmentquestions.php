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
 * @property string $status
 *
 * @property Userinspectionanswers[] $userinspectionanswers
 */
class Equipmentquestions extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'equipmentquestions';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['equipmentQuestionTitle', 'equipmentSubCategoryId', 'status'], 'required'],
            [['equipmentSubCategoryId'], 'integer'],
            [['equipmentQuestionTitle'], 'string', 'max' => 300],
            [['status'], 'string', 'max' => 25],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'equipmentQuestionId' => 'Equipment Question ID',
            'equipmentQuestionTitle' => 'Equipment Question Title',
            'equipmentSubCategoryId' => 'Equipment Sub Category ID',
            'status' => 'Status',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserinspectionanswers()
    {
        return $this->hasMany(Userinspectionanswers::className(), ['inspectionAnswerQuestionId' => 'equipmentQuestionId']);
    }
}
