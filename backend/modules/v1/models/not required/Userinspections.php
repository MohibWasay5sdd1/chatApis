<?php

namespace api\modules\v1\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;


/**
 * This is the model class for table "userinspections".
 *
 * @property int $inspectionId
 * @property int $userId
 * @property string $inspectionDescription
 * @property string $equipmentInspectedImageUrl
 * @property string $equipmentInspectedImageType
 * @property int $categoryId
 * @property string $createdOn
 * @property string $modifiedOn
 *
 * @property Userinspectionreport[] $userinspectionreports
 * @property Users $user
 * @property Equipmentcategories $category
 * @property Userinspectionsubcategories[] $userinspectionsubcategories
 */
class Userinspections extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'userinspections';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // [['userId', 'inspectionDescription', 'equipmentInspectedImageUrl', 'equipmentInspectedImageType', 'categoryId', 'createdOn', 'modifiedOn'], 'required'],
            [['userId', 'categoryId'], 'integer'],
            [['createdOn', 'modifiedOn'], 'safe'],
            [['inspectionDescription'], 'string', 'max' => 400],
            [['equipmentInspectedImageUrl'], 'string', 'max' => 255],
            [['equipmentInspectedImageType'], 'string', 'max' => 25],
            [['userId'], 'exist', 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['userId' => 'userId']],
            [['categoryId'], 'exist', 'skipOnError' => true, 'targetClass' => Equipmentcategories::className(), 'targetAttribute' => ['categoryId' => 'equipmentCategoryId']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'inspectionId' => 'Inspection ID',
            'userId' => 'User ID',
            'inspectionDescription' => 'Inspection Description',
            'equipmentInspectedImageUrl' => 'Equipment Inspected Image Url',
            'equipmentInspectedImageType' => 'Equipment Inspected Image Type',
            'categoryId' => 'Category ID',
            'createdOn' => 'Created On',
            'modifiedOn' => 'Modified On',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserinspectionreports()
    {
        return $this->hasMany(Userinspectionreport::className(), ['inspectionId' => 'inspectionId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(Users::className(), ['userId' => 'userId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(Equipmentcategories::className(), ['equipmentCategoryId' => 'categoryId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserinspectionsubcategories()
    {
        return $this->hasMany(Userinspectionsubcategories::className(), ['inspectionId' => 'inspectionId']);
    }
}
