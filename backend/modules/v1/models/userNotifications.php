<?php

namespace backend\modules\v1\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Query;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "user_notifications".
 *
 * @property int $id
 * @property int $user_id
 * @property string $body
 * @property string $subject
 * @property string $image_link
 * @property string $is_read
 * @property string $created_on
 * @property string $modified_on
 *
 * @property Users $user
 */
class userNotifications extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_notifications';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'body'], 'required'],
            [['user_id'], 'integer'],
            [['created_on', 'modified_on'], 'safe'],
            [['body', 'image_link'], 'string', 'max' => 250],
            [['subject'], 'string', 'max' => 100],
            [['is_read'], 'string', 'max' => 25],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'body' => 'Body',
            'subject' => 'Subject',
            'image_link' => 'Image Link',
            'is_read' => 'Is Read',
            'created_on' => 'Created On',
            'modified_on' => 'Modified On',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(Users::className(), ['id' => 'user_id']);
    }
    public function createNotification($invited_by_id,$message)
    {

        $model_notification = new userNotifications();
        $model_notification->user_id = $invited_by_id;
        $model_notification->body = $message;
        $model_notification->is_read = 'No';
        $model_notification->created_on = date('Y-m-d H:i:s');
        $model_notification->modified_on = date('Y-m-d H:i:s');
                
        if ($model_notification->save()) {
            return $model_notification;
        } else {
        return false;
        }
    }
}
