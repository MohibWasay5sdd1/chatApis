<?php

namespace api\modules\v1\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
/**
/**
 * This is the model class for table "defaultequipmentsubcategories".
 *
 * @property int $defaultSubCategoryId
 * @property string $defaultSubCategoryName
 * @property int $defaultCategoryId
 * @property string $defaultSubCategoryStatus
 * @property string $createdOn
 * @property string $modifiedOn
 *
 * @property Defaultequipmentquestions[] $defaultequipmentquestions
 * @property Defaultequipmentcategories $defaultCategory
 */
class Defaultequipmentsubcategories extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'defaultequipmentsubcategories';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['defaultSubCategoryName', 'defaultCategoryId', 'defaultSubCategoryStatus', 'createdOn', 'modifiedOn'], 'required'],
            [['defaultCategoryId'], 'integer'],
            [['createdOn', 'modifiedOn'], 'safe'],
            [['defaultSubCategoryName'], 'string', 'max' => 255],
            [['defaultSubCategoryStatus'], 'string', 'max' => 25],
            [['defaultCategoryId'], 'exist', 'skipOnError' => true, 'targetClass' => Defaultequipmentcategories::className(), 'targetAttribute' => ['defaultCategoryId' => 'defaultCategoryId']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'defaultSubCategoryId' => 'Default Sub Category ID',
            'defaultSubCategoryName' => 'Default Sub Category Name',
            'defaultCategoryId' => 'Default Category ID',
            'defaultSubCategoryStatus' => 'Default Sub Category Status',
            'createdOn' => 'Created On',
            'modifiedOn' => 'Modified On',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDefaultequipmentquestions()
    {
        return $this->hasMany(Defaultequipmentquestions::className(), ['defaultSubCategoryId' => 'defaultSubCategoryId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDefaultCategory()
    {
        return $this->hasOne(Defaultequipmentcategories::className(), ['defaultCategoryId' => 'defaultCategoryId']);
    }
}
