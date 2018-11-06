<?php

namespace api\modules\v1\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "inspectionremarks".
 *
 * @property int $Id
 * @property string $remark
 * @property int $inspectionId
 * @property int $equipmentQuestionId
 *
 * @property Inspections $inspection
 * @property Equipmentquestions $equipmentQuestion
 */
class InspectionRemarks extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'inspectionremarks';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['remark', 'inspectionId', 'equipmentQuestionId'], 'required'],
            [['inspectionId', 'equipmentQuestionId'], 'integer'],
            [['remark'], 'string', 'max' => 255],
            [['inspectionId'], 'exist', 'skipOnError' => true, 'targetClass' => Inspections::className(), 'targetAttribute' => ['inspectionId' => 'inspectionId']],
            [['equipmentQuestionId'], 'exist', 'skipOnError' => true, 'targetClass' => Equipmentquestions::className(), 'targetAttribute' => ['equipmentQuestionId' => 'equipmentQuestionId']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'Id' => 'ID',
            'remark' => 'Remark',
            'inspectionId' => 'Inspection ID',
            'equipmentQuestionId' => 'Equipment Question ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getInspection()
    {
        return $this->hasOne(Inspections::className(), ['inspectionId' => 'inspectionId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEquipmentQuestion()
    {
        return $this->hasOne(Equipmentquestions::className(), ['equipmentQuestionId' => 'equipmentQuestionId']);
    }
}
