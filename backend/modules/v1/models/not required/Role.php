<?php

namespace api\modules\v1\models;

use Yii;

/**
 * This is the model class for table "role".
 *
 * @property int $roleId
 * @property string $roleName
 * @property string $roleDescription
 * @property string $createdOn
 * @property string $modifiedOn
 *
 * @property Users[] $users
 */
class Role extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'role';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['roleName', 'roleDescription', 'createdOn', 'modifiedOn'], 'required'],
            [['createdOn', 'modifiedOn'], 'safe'],
            [['roleName', 'roleDescription'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'roleId' => 'Role ID',
            'roleName' => 'Role Name',
            'roleDescription' => 'Role Description',
            'createdOn' => 'Created On',
            'modifiedOn' => 'Modified On',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(Users::className(), ['roleId' => 'roleId']);
    }
}
