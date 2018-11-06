<?php

namespace backend\modules\v1\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;


/**
 * This is the model class for table "roles".
 *
 * @property int $id
 * @property string $name
 * @property string $created_on
 * @property string $modified_on
 *
 * @property Users[] $users
 */
class roles extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'roles';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['created_on', 'modified_on'], 'safe'],
            [['name'], 'string', 'max' => 30],
            [['name'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'created_on' => 'Created On',
            'modified_on' => 'Modified On',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(Users::className(), ['role_id' => 'id']);
    }

    public function getRoleId($user_type)
    {
        $connection = Yii::$app->db;
        $sql  = "SELECT * FROM roles WHERE name= :usertype";
        $command = $connection->createCommand($sql);
        $command->bindValue(':usertype' , $user_type);
        $rows_role = $command->queryOne();
        if ($rows_role) {
            return $rows_role['id']; 
        } else {
            return false;
        }
    }
}
