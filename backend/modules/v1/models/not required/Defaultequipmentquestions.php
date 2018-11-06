<?php

namespace api\modules\v1\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
/**
/**
 * This is the model class for table "defaultequipmentquestions".
 *
 * @property int $defaultQuestionId
 * @property string $defaultQuestionTitle
 * @property int $defaultSubCategoryId
 * @property string $defaultQuestionStatus
 * @property string $createdOn
 * @property string $modifiedOn
 *
 * @property Defaultequipmentsubcategories $defaultSubCategory
 */
class Defaultequipmentquestions extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'defaultequipmentquestions';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['defaultQuestionTitle', 'defaultSubCategoryId', 'defaultQuestionStatus', 'createdOn', 'modifiedOn'], 'required'],
            [['defaultSubCategoryId'], 'integer'],
            [['createdOn', 'modifiedOn'], 'safe'],
            [['defaultQuestionTitle'], 'string', 'max' => 300],
            [['defaultQuestionStatus'], 'string', 'max' => 25],
            [['defaultSubCategoryId'], 'exist', 'skipOnError' => true, 'targetClass' => Defaultequipmentsubcategories::className(), 'targetAttribute' => ['defaultSubCategoryId' => 'defaultSubCategoryId']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'defaultQuestionId' => 'Default Question ID',
            'defaultQuestionTitle' => 'Default Question Title',
            'defaultSubCategoryId' => 'Default Sub Category ID',
            'defaultQuestionStatus' => 'Default Question Status',
            'createdOn' => 'Created On',
            'modifiedOn' => 'Modified On',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDefaultSubCategory()
    {
        return $this->hasOne(Defaultequipmentsubcategories::className(), ['defaultSubCategoryId' => 'defaultSubCategoryId']);
    }
}
