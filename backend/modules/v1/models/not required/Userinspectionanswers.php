<?php

namespace api\modules\v1\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;


/**
 * This is the model class for table "userinspectionanswers".
 *
 * @property int $inspectionAnswerId
 * @property int $inspectionSubCategoryId
 * @property int $inspectionAnswerQuestionId
 * @property string $inspectionAnswer
 * @property string $createdOn
 * @property string $modifiedOn
 *
 * @property Equipmentquestions $inspectionAnswerQuestion
 * @property Userinspectionsubcategories $inspectionSubCategory
 */
class Userinspectionanswers extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'userinspectionanswers';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['inspectionSubCategoryId', 'inspectionAnswerQuestionId', 'inspectionAnswer', 'createdOn', 'modifiedOn'], 'required'],
            [['inspectionSubCategoryId', 'inspectionAnswerQuestionId'], 'integer'],
            [['createdOn', 'modifiedOn'], 'safe'],
            [['inspectionAnswer'], 'string', 'max' => 255],
            [['inspectionAnswerQuestionId'], 'exist', 'skipOnError' => true, 'targetClass' => Equipmentquestions::className(), 'targetAttribute' => ['inspectionAnswerQuestionId' => 'equipmentQuestionId']],
            [['inspectionSubCategoryId'], 'exist', 'skipOnError' => true, 'targetClass' => Userinspectionsubcategories::className(), 'targetAttribute' => ['inspectionSubCategoryId' => 'Id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'inspectionAnswerId' => 'Inspection Answer ID',
            'inspectionSubCategoryId' => 'Inspection Sub Category ID',
            'inspectionAnswerQuestionId' => 'Inspection Answer Question ID',
            'inspectionAnswer' => 'Inspection Answer',
            'createdOn' => 'Created On',
            'modifiedOn' => 'Modified On',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getInspectionAnswerQuestion()
    {
        return $this->hasOne(Equipmentquestions::className(), ['equipmentQuestionId' => 'inspectionAnswerQuestionId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getInspectionSubCategory()
    {
        return $this->hasOne(Userinspectionsubcategories::className(), ['Id' => 'inspectionSubCategoryId']);
    }
}
