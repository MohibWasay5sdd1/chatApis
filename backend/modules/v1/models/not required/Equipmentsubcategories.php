<?php

namespace api\modules\v1\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "equipmentsubcategories".
 *
 * @property int $equipmentSubCategoryId
 * @property string $equipmentSubCategoryName
 * @property int $equipmentCategoryId
 * @property string $createdOn
 * @property string $modifiedOn
 * @property string $status
 *
 * @property Equipmentcategories $equipmentCategory
 * @property Userinspectionsubcategories[] $userinspectionsubcategories
 */
class Equipmentsubcategories extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'equipmentsubcategories';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            //[['equipmentSubCategoryName', 'equipmentCategoryId', 'createdOn', 'modifiedOn', 'status'], 'required'],
            [['equipmentCategoryId'], 'integer'],
            [['createdOn', 'modifiedOn'], 'safe'],
            [['equipmentSubCategoryName'], 'string', 'max' => 255],
            [['status'], 'string', 'max' => 25],
            [['equipmentCategoryId'], 'exist', 'skipOnError' => true, 'targetClass' => Equipmentcategories::className(), 'targetAttribute' => ['equipmentCategoryId' => 'equipmentCategoryId']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'equipmentSubCategoryId' => 'Equipment Sub Category ID',
            'equipmentSubCategoryName' => 'Equipment Sub Category Name',
            'equipmentCategoryId' => 'Equipment Category ID',
            'createdOn' => 'Created On',
            'modifiedOn' => 'Modified On',
            'status' => 'Status',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEquipmentCategory()
    {
        return $this->hasOne(Equipmentcategories::className(), ['equipmentCategoryId' => 'equipmentCategoryId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserinspectionsubcategories()
    {
        return $this->hasMany(Userinspectionsubcategories::className(), ['subCategoryId' => 'equipmentSubCategoryId']);
    }
}
