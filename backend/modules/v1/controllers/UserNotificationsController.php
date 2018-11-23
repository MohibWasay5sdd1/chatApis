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
use backend\modules\v1\models\userNotifications;
use backend\modules\v1\models\contactLists;

class UserNotificationsController extends ActiveController
{
    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        $flag=0;
        if (parent::beforeAction($action)) {
            date_default_timezone_set("Asia/Karachi");
            if ($this->action->id == 'index'|| $this->action->id == 'update'|| 
                $this->action->id == 'invitation' || $this->action->id == 'delete' || $this->action->id =='search-user'
                || $this->action->id == 'view'|| 
                $this->action->id == 'change-password' ||  $this->action->id == 'show-invitations' ||
                $this->action->id == 'random-entry' || $this->action->id == 'create' ||
                $this->action->id == 'random-entry-of-user' || $this->action->id == 'create-date') {
                
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
        
            if ($flag==1 ||  $this->action->id == 'options' || $this->action->id == 'create'
                ) {
               
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

    public function actionCreate()
    {

    }

    public function actionIndex($id)
    {
        //echo json_encode($id);
        $user_notification = new userNotifications();

        $list = $user_notification->getNotifications($id);
        if ($list) {
            Yii::$app->response->statusCode=200;
            echo json_encode(array(
                'status'=>200,
                'data'=>$list),JSON_PRETTY_PRINT
            );
        } else {
            Yii::$app->response->statusCode=400;
            echo json_encode(array(
                'status'=>400,
                'error'=>array('message'=>"No contact found.")),JSON_PRETTY_PRINT
            );  
        
        }

    }

    public function actionUpdate($id)
    {
        $user_notification = new userNotifications();
        $update = $user_notification->updateNotifications($id);
        if ($update) {
            Yii::$app->response->statusCode=200;
            echo json_encode(array(
                'status'=>200,
                'data'=>array('message' => 'Success')),JSON_PRETTY_PRINT
            );
        } else {
            Yii::$app->response->statusCode=400;
            echo json_encode(array(
                'status'=>400,
                'error'=>array('message'=>"No notifications found.")),JSON_PRETTY_PRINT
            );  
        
        }
    }

    /**
     * Deletes an existing users model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }


    protected function findModel($id)
    {
        if (($model = users::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
