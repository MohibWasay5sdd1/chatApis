<?php

namespace backend\modules\v1\controllers;

use Yii;
use yii\filters\autsth\HttpBasicAuth;
use yii\rest\ActiveController;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\filters\VerbFilter;
use yii\db\Query;
use yii\web\Response;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\ContentNegotiator;
use yii\data\Pagination;
use yii\helpers\Url;
use yii\web\ServerErrorHttpException;
use yii\helpers\Html;
use yii\base\ViewContextInterface;
use yii\base\Exception;

use backend\modules\v1\models\roles;
use backend\modules\v1\models\users;
use backend\modules\v1\models\tokens;
use backend\modules\v1\models\userInvitations;
use backend\modules\v1\models\contactLists;

class UserInvitationsController extends ActiveController
{
        public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        $flag=0;
        if (parent::beforeAction($action)) {
            date_default_timezone_set("Asia/Karachi");
            if ($this->action->id == 'index'|| $this->action->id == 'update'|| 
                $this->action->id == 'invitation' || $this->action->id == 'delete' 
                || $this->action->id == 'view'||  $this->action->id == 'create' ) {
                
                Url::remember();
                $headers = Yii::$app->request->headers;
                $accept = $headers->get('access_token');
                $userid = $headers->get('user_id');
              
                $model = Tokens::findOne([
                        'token' => $accept,]);      
            
                if ($model) {
               
                   
                    $current=date('Y-m-d H:i:s');
                    //echo json_encode($model->user_id." ".$userid." ".$current." ".$model->expiry);
                    if ($model->user_id==$userid) {
                
                        if ($model->expiry>=$current) {
                            $flag=1;
                            $model->expiry = date("Y-m-d H:i:s",strtotime(date("Y-m-d H:i:s")." +7 days"));
                            $model->modified_on=date('Y-m-d H:i:s');
                  
                            $model->save(); 
                            if ($model->save()) {
                               
                            } else {

                            }
                        } else {

                        }
                    } else {
  
                    }
                } else {

                }
            }
        
            if ($flag==1 ||  $this->action->id == 'options' ) {
               
                return true;
           
            } else {

                Yii::$app->response->statusCode=401;
                echo json_encode(array(
                                  'status'=>401,
                                  'error'=>array(
                                    'message'=>"You are not authorized to perform this action.".$this->action->id
                                    )
                                  )
                                );

                return false;
            }

        }

    }
    public function behaviors()
    {

        return [
            'corsFilter' => [
                'class' => \yii\filters\Cors::className(),
                'cors' => [
                    'Origin' => ['http://localhost:8100','http://clients2.5stardesigners.net','*'],
                    'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                    'Access-Control-Request-Headers' => ['*','access_token','user_id'],
                    'Access-Control-Allow-Headers' => ['*','access_token','user_id'],
                    'Access-Control-Allow-Credentials' => null,
                    'Access-Control-Max-Age' => 84600,
                    'Access-Control-Expose-Headers' => ['X-Pagination-Current-Page'],
                    'Access-Control-Allow-Origin'   => ['*'],
                ],

            ],  

            'verbs' => [
                'class' => \yii\filters\VerbFilter::className(),
                'actions' => [
                    'index'  => ['GET'],
                    'view'   => ['GET'],
                    'create' => [ 'POST'],
                    'update' => ['PUT', 'POST'],
                    'delete' => ['POST', 'DELETE'],
                ],
            ],
          
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ], 
             
        ];

    }

    public $modelClass = 'api\modules\v1\models\users';   
    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'items',
    ]; 

    public function actions()
    {
        $actions = parent::actions();
        unset(
            $actions['index'], 
            $actions['view'], 
            $actions['create'], 
            $actions['update'],
            $actions['delete'],
            $actions['options']
              
        );
        return $actions;
    }

    public function actionCreate($id)
    {
        $request = Yii::$app->request;
        $user_id = $request->post('invited_user_id');

        $user = new users();
        $status = "Active";
        $model = $user->getUserById($user_id,$status);
        if ($model) {
                        
            $invitation = new userInvitations();
            //$token= Yii::$app->security->generateRandomString() . '_' . time();
            //$expiry=date("Y-m-d H:i:s",strtotime(date("Y-m-d H:i:s")." +1 day"));
            $status = "Invited";
            $invite = $invitation->invite($id,$user_id,$status);
            if($invite){

                //$notification = \Yii::$app->MyComponent->send_notification($registration_id,$message);

                Yii::$app->response->statusCode=200;
                echo json_encode(array(
                    'status'=>200,
                    'error'=>array('message'=>"Invitation sent."
                        )),JSON_PRETTY_PRINT
                );
            } else {
                Yii::$app->response->statusCode=400;
                echo json_encode(array(
                    'status'=>400,
                    'error'=>array('message'=>"Error occured.")),JSON_PRETTY_PRINT
                );            
            }
        } else {
            Yii::$app->response->statusCode=400;
            echo json_encode(array(
                'status'=>400,
                'error'=>array('message'=>"User not found.")),JSON_PRETTY_PRINT
            );            
        }

    }

    public function actionIndex($id) {

        $invitation = new userInvitations();
        $model = $invitation->getInvitations($id);
        if($model) {
            Yii::$app->response->statusCode=200;
            echo json_encode(array(
                'status'=>200,
                'data'=>$model),JSON_PRETTY_PRINT
            );
        } else {
            Yii::$app->response->statusCode=400;
            echo json_encode(array(
                'status'=>400,
                'error'=>array('message'=>"No invitation found.")),JSON_PRETTY_PRINT
            );  
        
        }
    }      
    
    public function actionUpdate($id)
    {
        $invitation = new userInvitations();
        $request = Yii::$app->request;
        $status = $request->post('status');

        $model = $invitation->updateInvitation($id, $status);
        if($model) {
            Yii::$app->response->statusCode=200;
            echo json_encode(array(
                'status'=>200,
                'data'=>$model),JSON_PRETTY_PRINT
            );
        } else {
            Yii::$app->response->statusCode=400;
            echo json_encode(array(
                'status'=>400,
                'error'=>array('message'=>"No invitation found.")),JSON_PRETTY_PRINT
            );  
        
        }
    }

    public function actionDelete($id)
    {

    }


    protected function findModel($id)
    {
        if (($model = users::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
