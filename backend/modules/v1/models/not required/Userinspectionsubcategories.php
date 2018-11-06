<?php

namespace api\modules\v1\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "userinspectionsubcategories".
 *
 * @property int $Id
 * @property int $inspectionId
 * @property int $subCategoryId
 * @property string $createdOn
 * @property string $modifiedOn
 *
 * @property Userinspectionanswers[] $userinspectionanswers
 * @property Userinspections $inspection
 * @property Equipmentsubcategories $subCategory
 */
class Userinspectionsubcategories extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'userinspectionsubcategories';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['inspectionId', 'subCategoryId', 'createdOn', 'modifiedOn'], 'required'],
            [['inspectionId', 'subCategoryId'], 'integer'],
            [['createdOn', 'modifiedOn'], 'safe'],
            [['inspectionId'], 'exist', 'skipOnError' => true, 'targetClass' => Userinspections::className(), 'targetAttribute' => ['inspectionId' => 'inspectionId']],
            [['subCategoryId'], 'exist', 'skipOnError' => true, 'targetClass' => Equipmentsubcategories::className(), 'targetAttribute' => ['subCategoryId' => 'equipmentSubCategoryId']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'Id' => 'ID',
            'inspectionId' => 'Inspection ID',
            'subCategoryId' => 'Sub Category ID',
            'createdOn' => 'Created On',
            'modifiedOn' => 'Modified On',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserinspectionanswers()
    {
        return $this->hasMany(Userinspectionanswers::className(), ['inspectionSubCategoryId' => 'Id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getInspection()
    {
        return $this->hasOne(Userinspections::className(), ['inspectionId' => 'inspectionId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSubCategory()
    {
        return $this->hasOne(Equipmentsubcategories::className(), ['equipmentSubCategoryId' => 'subCategoryId']);
    }
}
