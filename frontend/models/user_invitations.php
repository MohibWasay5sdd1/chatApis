<?php

namespace frontend\models;

use Yii;

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
class user_invitations extends \yii\db\ActiveRecord
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
}
