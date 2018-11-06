<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "contact_lists".
 *
 * @property int $id
 * @property int $user_id
 * @property string $status
 * @property string $created_on
 * @property string $modified_on
 *
 * @property Users $user
 * @property UserContacts[] $userContacts
 */
class contactlists extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'contact_lists';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'status'], 'required'],
            [['user_id'], 'integer'],
            [['created_on', 'modified_on'], 'safe'],
            [['status'], 'string', 'max' => 25],
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserContacts()
    {
        return $this->hasMany(UserContacts::className(), ['contact_list_id' => 'id']);
    }
}
