<?php

namespace backend\modules\v1\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "tokens".
 *
 * @property int $id
 * @property string $token
 * @property string $expiry
 * @property int $user_id
 * @property string $created_on
 * @property string $modified_on
 *
 * @property Users $user
 */
class tokens extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tokens';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['token', 'user_id'], 'required'],
            [['expiry', 'created_on', 'modified_on'], 'safe'],
            [['user_id'], 'integer'],
            [['token'], 'string', 'max' => 250],
            [['token'], 'unique'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => users::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'token' => 'Token',
            'expiry' => 'Expiry',
            'user_id' => 'User ID',
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

    public function generateToken($access_token,$user_id,$expiry)
    {
        $model_token = new tokens();
        $model_token->token =$access_token;
        $model_token->user_id = $user_id;
        $model_token->created_on=date('Y-m-d H:i:s');
        $model_token->modified_on=date('Y-m-d H:i:s');
        $model_token->expiry = $expiry;
                
        if ($model_token->save()) {
            return $model_token;
        } else {
        return false;
        }
    }
}
