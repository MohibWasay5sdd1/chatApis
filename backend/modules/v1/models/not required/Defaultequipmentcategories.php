<?php

namespace api\modules\v1\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
/**

/**
 * This is the model class for table "defaultequipmentcategories".
 *
 * @property int $defaultCategoryId
 * @property string $defaultCategoryName
 * @property string $defaultCategoryStatus
 * @property string $createdOn
 * @property string $modifiedOn
 *
 * @property Defaultequipmentsubcategories[] $defaultequipmentsubcategories
 */
class Defaultequipmentcategories extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'defaultequipmentcategories';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['defaultCategoryName', 'defaultCategoryStatus', 'createdOn', 'modifiedOn'], 'required'],
            [['createdOn', 'modifiedOn'], 'safe'],
            [['defaultCategoryName'], 'string', 'max' => 255],
            [['defaultCategoryStatus'], 'string', 'max' => 25],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'defaultCategoryId' => 'Default Category ID',
            'defaultCategoryName' => 'Default Category Name',
            'defaultCategoryStatus' => 'Default Category Status',
            'createdOn' => 'Created On',
            'modifiedOn' => 'Modified On',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDefaultequipmentsubcategories()
    {
        return $this->hasMany(Defaultequipmentsubcategories::className(), ['defaultCategoryId' => 'defaultCategoryId']);
    }
}
