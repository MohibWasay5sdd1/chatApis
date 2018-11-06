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
class contactLists extends ActiveRecord
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
            [['status', 'user_id'], 'required'],
            [['created_on', 'modified_on'], 'safe'],
            [['user_id'], 'integer'],
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

    public function createContactList($id,$status)
    {
        $model_list = new contactLists();
        $model_list->user_id = $id;
        $model_list->status = $status;
        $model_list->created_on=date('Y-m-d H:i:s');
        $model_list->modified_on=date('Y-m-d H:i:s');
        
                
        if ($model_list->save()) {
            return $model_list;
        } else {
        return false;
        }
    }
}
