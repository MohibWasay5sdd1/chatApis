<?php

namespace api\modules\v1\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "inspections".
 *
 * @property int $inspectionId
 * @property string $inspectionDescription
 * @property string $inspectedEqImage
 * @property int $inspectedEqSubCategoryId
 * @property string $createdOn
 * @property string $modifiedOn
 * @property int $userId
 * @property string $finalRemark
 * @property string $faultImage
 * @property string $faultDescription
 *
 * @property Inspectionremarks[] $inspectionremarks
 * @property Users $user
 * @property Equipmentsubcategories $inspectedEqSubCategory
 */
class Inspections extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'inspections';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['inspectionDescription', 'inspectedEqImage', 'inspectedEqSubCategoryId', 'createdOn', 'modifiedOn', 'userId', 'finalRemark', 'faultImage', 'faultDescription'], 'required'],
            [['inspectedEqSubCategoryId', 'userId'], 'integer'],
            [['createdOn', 'modifiedOn'], 'safe'],
            [['inspectionDescription', 'inspectedEqImage', 'finalRemark', 'faultImage', 'faultDescription'], 'string', 'max' => 255],
             [['status'], 'string'],
            [['userId'], 'exist', 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['userId' => 'userId']],
            [['inspectedEqSubCategoryId'], 'exist', 'skipOnError' => true, 'targetClass' => Equipmentsubcategories::className(), 'targetAttribute' => ['inspectedEqSubCategoryId' => 'equipmentSubCategoryId']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'inspectionId' => 'Inspection ID',
            'inspectionDescription' => 'Inspection Description',
            'inspectedEqImage' => 'Inspected Eq Image',
            'inspectedEqSubCategoryId' => 'Inspected Eq Sub Category ID',
            'createdOn' => 'Created On',
            'modifiedOn' => 'Modified On',
            'userId' => 'User ID',
            'finalRemark' => 'Final Remark',
            'faultImage' => 'Fault Image',
            'faultDescription' => 'Fault Description',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getInspectionremarks()
    {
        return $this->hasMany(Inspectionremarks::className(), ['inspectionId' => 'inspectionId']);
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
    public function getInspectedEqSubCategory()
    {
        return $this->hasOne(Equipmentsubcategories::className(), ['equipmentSubCategoryId' => 'inspectedEqSubCategoryId']);
    }
}
