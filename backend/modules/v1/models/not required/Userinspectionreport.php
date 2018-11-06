<?php

namespace api\modules\v1\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;


/**
 * This is the model class for table "userinspectionreport".
 *
 * @property int $inspectionReportId
 * @property int $inspectionId
 * @property string $reportType
 * @property string $status
 * @property string $signatureUrl
 * @property string $inspectionDescription
 * @property string $mediaUrl
 * @property string $mediaType
 * @property string $createdOn
 * @property string $modifiedOn
 *
 * @property Userinspections $inspection
 */
class Userinspectionreport extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'userinspectionreport';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // [['inspectionId', 'reportType', 'status', 'signatureUrl', 'mediaUrl', 'mediaType', 'createdOn', 'modifiedOn'], 'required'],
            [['inspectionId'], 'integer'],
            [['createdOn', 'modifiedOn'], 'safe'],
            [['reportType', 'status', 'mediaType'], 'string', 'max' => 25],
            [['signatureUrl', 'mediaUrl'], 'string', 'max' => 255],
            [['observationDescription'], 'string', 'max' => 400],
            [['inspectionId'], 'exist', 'skipOnError' => true, 'targetClass' => Userinspections::className(), 'targetAttribute' => ['inspectionId' => 'inspectionId']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'inspectionReportId' => 'Inspection Report ID',
            'inspectionId' => 'Inspection ID',
            'reportType' => 'Report Type',
            'status' => 'Status',
            'signatureUrl' => 'Signature Url',
            'inspectionDescription' => 'Inspection Description',
            'mediaUrl' => 'Media Url',
            'mediaType' => 'Media Type',
            'createdOn' => 'Created On',
            'modifiedOn' => 'Modified On',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getInspection()
    {
        return $this->hasOne(Userinspections::className(), ['inspectionId' => 'inspectionId']);
    }
}
