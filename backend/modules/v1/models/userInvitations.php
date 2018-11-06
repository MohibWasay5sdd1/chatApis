<?php

namespace backend\modules\v1\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "user_invitations".
 *
 * @property int $id
 * @property int $user_id
 * @property string $email
 * @property string $token
 * @property string $expiry
 * @property string $status
 * @property string $created_on
 * @property string $modified_on
 *
 * @property Users $user
 */
class userInvitations extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_invitations';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'email', 'token', 'status'], 'required'],
            [['user_id'], 'integer'],
            [['expiry', 'created_on', 'modified_on'], 'safe'],
            [['email'], 'string', 'max' => 50],
            [['token'], 'string', 'max' => 250],
            [['status'], 'string', 'max' => 30],
            [['token'], 'unique'],
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
            'email' => 'Email',
            'token' => 'Token',
            'expiry' => 'Expiry',
            'status' => 'Status',
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

    public function invite($id,$user_email,$expiry,$token,$status)
    {
        $model_invitation = new userInvitations();
        $model_invitation->user_id = $id;
        $model_invitation->email = $user_email;
        $model_invitation->token =$token;
        $model_invitation->expiry = $expiry;
        $model_invitation->status = $status;
        $model_invitation->created_on = date('Y-m-d H:i:s');
        $model_invitation->modified_on = date('Y-m-d H:i:s');
                
        if ($model_invitation->save()) {
            return $model_invitation;
        } else {
        return false;
        }
    }
}
