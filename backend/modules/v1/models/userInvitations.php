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
            [['invited_by_id', 'invited_id', 'status'], 'required'],
            [['invited_by_id', 'invited_id'], 'integer'],
            [['created_on', 'modified_on'], 'safe'],
            //[['token'], 'string', 'max' => 250],
            [['status'], 'string', 'max' => 30],
            //[['token'], 'unique'],
            [['invited_by_id'], 'exist', 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['invited_by_id' => 'id']],
            [['invited_id'], 'exist', 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['invited_id' => 'id']],
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
            //'token' => 'Token',
            //'expiry' => 'Expiry',
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

    public function getInvitationById($id) 
    {
        $connection = Yii::$app->db;
       
        $sql  = "SELECT * FROM user_invitations WHERE id = :id ";
        $command = $connection->createCommand($sql);
        $command->bindValue(':id' , $id);
        $rows_invitations = $command->queryOne();
        if ($rows_invitations) {
            return $rows_invitations; 
        } else {
            return false;
        } 
    }
    public function invite($id,$user_id,$status)
    {
        $model_invitation = new userInvitations();
        $model_invitation->invited_by_id = $id;
        $model_invitation->invited_id = $user_id;
        $model_invitation->status = $status;
        $model_invitation->created_on = date('Y-m-d H:i:s');
        $model_invitation->modified_on = date('Y-m-d H:i:s');
        //return $user_id;
                
        if ($model_invitation->save()) {
            return $model_invitation;
        } else {
        return false;
        }
    }

    public function getInvitations($id) 
    {
        $user = new users();
        $status = "Invited";
        $user_status = "Active";
        $i = 0;
        $connection = Yii::$app->db;
       
        $sql  = "SELECT * FROM user_invitations WHERE invited_id = :id AND status = :status";
        $command = $connection->createCommand($sql);
        $command->bindValue(':id' , $id);
        $command->bindValue(':status' , $status);
        $rows_invitations = $command->queryAll();
        if ($rows_invitations) {
            foreach ($rows_invitations as $row) {

                $row_id = $user->getUserById($row['invited_by_id'],$user_status);
                $rows_invitation[$i]['invitation_id'] = $row['id'];
                $rows_invitation[$i]['user_id'] = $row['invited_id'];
                $rows_invitation[$i]['sent_by_full_name'] = $row_id['full_name'];
                $rows_invitation[$i]['sent_by_user_name'] = $row_id['user_name'];
                $i++;
            }
           return $rows_invitation;
        } else {
            return false;
        }
    }
    public function updateInvitation($id,$status)
    {
        $users = new users();
        $user_status = 'Active';
        $connection = Yii::$app->db;
        $sql  = "UPDATE user_invitations SET status = :status, modified_on = NOW() WHERE id = :id";
        $command = $connection->createCommand($sql);
        $command->bindValue(':id' , $id);
        $command->bindValue(':status' , $status);
        $rows_invite = $command->execute();
        if($rows_invite) {
            $model = $this->getInvitationById($id);
            if($model['status'] =='Accepted') {
                $contact_list = new contactLists();
                //getting contact list of invited and invitee
                $model_contact_invited_by = $contact_list->getContactListByUserId($model['invited_by_id']);
                $contact_invited_by_list_id = $model_contact_invited_by['id'];
                
                $model_contact_invited = $contact_list->getContactListByUserId($model['invited_id']);
                $contact_invited_list_id = $model_contact_invited['id'];

                //adding both of them to each other conatcts using their contact lisst ids

                $user_contact = new userContacts();

                $add_invited_to_invitee = $user_contact->addContact($contact_invited_by_list_id, $model['invited_id']);
                $add_invitee_to_invited = $user_contact->addContact($contact_invited_list_id, $model['invited_by_id']);

                //create notifcation for invitee
                $user_invited = $users->getUserById($model['invited_id'],$user_status);
                $message = $user_invited['full_name']. " has accepted your request.";
                $user_notification = new userNotifications();
                $create_notification = $user_notification->createNotification($model['invited_by_id'],$message);

                
            }
            if($model) {
                return $model;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}
