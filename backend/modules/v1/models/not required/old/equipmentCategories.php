<?php

namespace api\modules\v1\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "equipmentcategories".
 *
 * @property int $equipmentCategoryId
 * @property string $equipmentCategoryName
 * @property string $createdOn
 * @property string $modifiedOn
 * @property int $userId
 *
 * @property Users $user
 * @property Equipmentsubcategories[] $equipmentsubcategories
 */
class equipmentCategories extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'equipmentcategories';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['equipmentCategoryName', 'createdOn', 'modifiedOn', 'userId'], 'required'],
            [['createdOn', 'modifiedOn'], 'safe'],
            [['userId'], 'integer'],
            [['equipmentCategoryName'], 'string', 'max' => 255],
            [['status'], 'string'],
            [['userId'], 'exist', 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['userId' => 'userId']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'equipmentCategoryId' => 'Equipment Category ID',
            'equipmentCategoryName' => 'Equipment Category Name',
            'createdOn' => 'Created On',
            'modifiedOn' => 'Modified On',
            'userId' => 'User ID',
        ];
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
    public function getEquipmentsubcategories()
    {
        return $this->hasMany(Equipmentsubcategories::className(), ['equipmentCategoryId' => 'equipmentCategoryId']);
    }
}
