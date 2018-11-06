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
 *
 * @property Equipmentquestions[] $equipmentquestions
 * @property Equipmentcategories $equipmentCategory
 * @property Inspections[] $inspections
 */
class EquipmentSubCategories extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'equipmentsubcategories';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['equipmentSubCategoryName', 'equipmentCategoryId', 'createdOn', 'modifiedOn'], 'required'],
            [['equipmentCategoryId'], 'integer'],
            [['createdOn', 'modifiedOn'], 'safe'],
            [['equipmentSubCategoryName'], 'string', 'max' => 255],
             [['status'], 'string'],
            [['equipmentCategoryId'], 'exist', 'skipOnError' => true, 'targetClass' => Equipmentcategories::className(), 'targetAttribute' => ['equipmentCategoryId' => 'equipmentCategoryId']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'equipmentSubCategoryId' => 'Equipment Sub Category ID',
            'equipmentSubCategoryName' => 'Equipment Sub Category Name',
            'equipmentCategoryId' => 'Equipment Category ID',
            'createdOn' => 'Created On',
            'modifiedOn' => 'Modified On',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEquipmentquestions()
    {
        return $this->hasMany(Equipmentquestions::className(), ['equipmentSubCategoryId' => 'equipmentSubCategoryId']);
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
    public function getInspections()
    {
        return $this->hasMany(Inspections::className(), ['inspectedEqSubCategoryId' => 'equipmentSubCategoryId']);
    }
}
