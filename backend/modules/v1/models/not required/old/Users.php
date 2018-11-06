<?php

namespace api\modules\v1\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * Users Model
 *
 * @author 
 */



/**
 * This is the model class for table "users".
 *
 * @property int $userId
 * @property string $userName
 * @property string $userEmail
 * @property string $userPassword
 * @property string $userCompany
 * @property string $userDepartment
 * @property string $nameToReceiveReport
 * @property string $emailToReceiveReport
 * @property string $createdOn
 * @property string $modifiedOn
 *
 * @property Equipmentcategories[] $equipmentcategories
 * @property Inspections[] $inspections
 * @property Tokens[] $tokens
 */
class Users extends ActiveRecord implements IdentityInterface

{
    public $companylogo;
    public $profilepicture;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
       return '{{%users}}';
    }

    /**
     * @inheritdoc
     */
      public function rules()
    {
        return [
            [['userName', 'userEmail', 'userPassword', 'userCompany', 'userDepartment', 'nameToReceiveReport', 'emailToReceiveReport', 'companyLogo', 'profilePicture', 'createdOn', 'modifiedOn'], 'required'],
            [['createdOn', 'modifiedOn'], 'safe'],
            [['userName', 'userEmail', 'userPassword', 'nameToReceiveReport', 'emailToReceiveReport'], 'string', 'max' => 255],
             [['userEmail'], 'email','message'=>"The email isn't correct"],
             [['userEmail'], 'unique','message'=>"Email already exists!"],
            [['companylogo','profilepicture'],'file'],
            [['userCompany', 'userDepartment', 'companyLogo', 'profilePicture'], 'string', 'max' => 300],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'userId' => 'User ID',
            'userName' => 'User Name',
            'userEmail' => 'User Email',
            'userPassword' => 'User Password',
            'userCompany' => 'User Company',
            'userDepartment' => 'User Department',
            'nameToReceiveReport' => 'Name To Receive Report',
            'emailToReceiveReport' => 'Email To Receive Report',
            'companyLogo' => 'Company Logo',
            'profilePicture' => 'Profile Picture',
            'createdOn' => 'Created On',
            'modifiedOn' => 'Modified On',
        ];
    }


    /**
     * {@inheritdoc}
     */

    
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }


    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (empty($token)) {
            return null;
        }

        return static::findOne([
            'resetToken' => $token,

        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return bool
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {

    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->userPassword);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEquipmentcategories()
    {
        return $this->hasMany(Equipmentcategories::className(), ['userId' => 'userId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getInspections()
    {
        return $this->hasMany(Inspections::className(), ['userId' => 'userId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTokens()
    {
        return $this->hasMany(Tokens::className(), ['userId' => 'userId']);
    }
}
