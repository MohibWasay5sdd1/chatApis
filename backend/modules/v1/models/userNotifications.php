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

    public function getNotificationById($id)
    {
        $connection = Yii::$app->db;
        $sql  = "SELECT * FROM user_notifications WHERE id = :id ";
        $command = $connection->createCommand($sql);
        $command->bindValue(':id' , $id);
        $rows = $command->queryOne();
        if ($rows) {
            return $rows; 
        } else {
            return false;
        }
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
    public function getUnreadCount($id)
    {
        $un_read_count = 0;
        $connection = Yii::$app->db;
        $sql  = "SELECT * FROM user_notifications WHERE user_id = :id ";
        $command = $connection->createCommand($sql);
        $command->bindValue(':id' , $id);
        $rows_notifications = $command->queryAll();
        if($rows_notifications) {
            foreach($rows_notifications as $row) {
                if($row['is_read'] == 'No') {
                    $un_read_count++;
                }
            }
            return $un_read_count;
        } else {
            return false;
        }
        
    }

    public function getNotifications($id)
    {
        $un_read_count = 0;
        $connection = Yii::$app->db;
        $sql  = "SELECT * FROM user_notifications WHERE user_id = :id ";
        $command = $connection->createCommand($sql);
        $command->bindValue(':id' , $id);
        $rows_notifications = $command->queryAll();
        if($rows_notifications) {
           
            $un_read_count  = $this->getUnreadCount($id);
            $notifications['un_read_count'] = $un_read_count;
            $notifications['notifications'] = $rows_notifications;
            return $notifications;
        } else {
            return false;
        }
    }

    public function updateNotifications($id)
    {
        $connection = Yii::$app->db;
        $status = 'Yes';
        $sql  = "UPDATE user_notifications SET is_read = :status, modified_on = NOW() WHERE id = :id";
        $command = $connection->createCommand($sql);
        $command->bindValue(':status' , $status);
        $command->bindValue(':id' , $id);
        $rows = $command->execute();
        if ($rows) {
            $model = $this->getNotificationById($id);
            if($model) {
                return $model;
            } else {
                return false;
            } 
        }else {
                return false;
        }
    
    }

}
