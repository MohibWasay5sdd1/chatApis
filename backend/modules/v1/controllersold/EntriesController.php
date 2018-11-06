<?php

namespace api\modules\v1\controllers;

use Yii;
use yii\filters\auth\HttpBasicAuth;
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
use  yii\base\ViewContextInterface;
use yii\base\Exception;
use api\modules\v1\models\users;
use api\modules\v1\models\entries;
use api\modules\v1\models\tokens;
use api\modules\v1\models\roles;
use api\modules\v1\models\Usersettings;
use api\modules\v1\models\Snapcount;
use api\modules\v1\models\Userstreaks;


class EntriesController extends ActiveController
{
    
    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        $flag=0;
        if (parent::beforeAction($action)) {
            date_default_timezone_set("Asia/Karachi");
            if ($this->action->id == 'index'|| $this->action->id == 'update'|| 
                $this->action->id == 'view' || $this->action->id == 'delete' || 
                $this->action->id == 'change-password' ||  $this->action->id == 'update-settings') {
                
                Url::remember();
                $headers = Yii::$app->request->headers;
                $accept = $headers->get('access_token');
                $userid = $headers->get('user_id');
              
                $model = Tokens::findOne([
                        'token' => $accept,]);      
            
                if ($model) {
               
                    $flag=1;
                    $current=date('Y-m-d H:i:s');
                    //echo json_encode($model->user_id." ".$current." ".$model->expiry);
                    if ($model->user_id==$userid) {
                
                        if ($model->expiry>=$current) {
                            
                            $model->expiry = date("Y-m-d H:i:s",strtotime(date("Y-m-d H:i:s")." +7 days"));
                            $model->modified_on=date('Y-m-d H:i:s');
                  
                            $model->save(); 
                            if ($model->save()) {
                               
                            } else {

                                Yii::$app->response->statusCode=401;
                                echo json_encode(array(
                                  'status'=>401,
                                  'error'=>array(
                                    'message'=>"Something went wrong. Try again."
                                    )
                                  )
                                );
                                exit(0);
                            }
                        } else {
             
                        Yii::$app->response->statusCode=401;
                        echo json_encode(array(
                          'status'=>401,
                          'error'=>array(
                            'message'=>"Your session has expired. Sign in again."
                            )
                          )
                        );
                        exit(0);
                        }
                    } else {
              
                        Yii::$app->response->statusCode=401;
                        echo json_encode(array(
                          'status'=>401,
                          'error'=>array(
                            'message'=>"This token is not assigned to this user.".$userid." ".$accept
                            )
                          )
                        );
                        exit(0);
                    }
                } else {
                    Yii::$app->response->statusCode=401;
                    echo json_encode(array(
                      'status'=>401,
                      'error'=>array(
                        'message'=>"This token does not exist.".$accept."hello"
                        )
                      )
                    );
                    exit(0);
                }
            }
        
            if ($flag==1 ||  $this->action->id == 'create'|| $this->action->id == 'options' || $this->action->id == 'login' ||
               $this->action->id == 'request-password-reset' || $this->action->id == 'reset-password' || 
               $this->action->id == 'test-post' || $this->action->id == 'verify-code' || $this->action->id == 'code-change-password') {
               
                return true;
           
            } else {

                Yii::$app->response->statusCode=401;
                echo json_encode(array(
                                  'status'=>401,
                                  'error'=>array(
                                    'message'=>"You are not authorized to perform this action."
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
                    // restrict access to
                      'Origin' => ['http://localhost:8100','http://clients3.5stardesigners.net','*'],
                      'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                     // Allow only POST and PUT methods
                      'Access-Control-Request-Headers' => ['*','access_token','user_id'],
                      'Access-Control-Allow-Headers' => ['*','access_token','user_id'],
                    // Allow only headers 'X-Wsse'
                      'Access-Control-Allow-Credentials' => null,
                    // Allow OPTIONS caching
                      'Access-Control-Max-Age' => 84600,
                    // Allow the X-Pagination-Current-Page header to be exposed to the browser.
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


    public $modelClass = 'api\modules\v1\models\entries';   
    public $serializer = [
      'class' => 'yii\rest\Serializer',
      'collectionEnvelope' => 'items',
    ]; 

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index'], 
              $actions['view'], 
              $actions['create'], 
              $actions['update'],
              $actions['delete'],
              $actions['options']
        );
        return $actions;
    }

    public function actionIndex($id){
        $rows = (new Query())
            ->select('*')
            ->from('entries')
            ->where(['user_id' => $id])
            ->all();
             
        Yii::$app->response->statusCode=200;
        echo json_encode(array(
                'status'=>200,
                'data'=>array_filter($rows)),JSON_PRETTY_PRINT
        );

    }
        
    public function actionView($id)
    {
   
        $model = $this->findModel($id);
                     
        if ($model) {
            $rows = (new Query())
                        ->select('*')
                        ->from('user_settings')
                        ->where(['user_id' => $id])
                        ->one();
            $reminder_time = date("H:i:s",strtotime($rows['reminder_time']));
            $is_entry_visible = $rows['is_entry_visible'];
            $is_notification_allowed = $rows['is_notification_allowed'];
      
            $response = array(
              'userId'=>$model->id,
               'userName'=>$model->user_name, 
               'userFirstName'=>$model->first_name, 
               'userLastName'=>$model->last_name,
               'userEmail'=>$model->user_email,
               'roleId'=>$model->role_id,
               'lastLogin'=>$model->last_login,
               'snapCountInRow'=>$model->snap_count_in_row,
               'snapCountPerDay'=>$model->snap_count_per_day,
               'reminderTime' =>$reminder_time,
               'isEntryVisible' =>$is_entry_visible,
               'isNotificationAllowed' =>$is_notification_allowed
              );

            Yii::$app->response->statusCode=200;
            echo json_encode(array(
                'status'=>200,
                'data'=>array_filter($response)),JSON_PRETTY_PRINT
            );
        } 
    }

    public function actionCreate($id)
    {   

        $request = Yii::$app->request;
        
      
        $post =   $request->post();
            //echo json_encode($post);

        foreach ($post['entries'] as $row) {
                   // echo json_encode($row['title']);
                //echo json_encode("hello");
                
            $model = new Entries();
            $model->user_id = $id;
            $model->entry_title =   $row['title'];
            $model->entry_description  =   $row['description'];
            $model->country =   $row['country'];
            $model->state  =   $row['state'];
            $model->city  =   $row['city'];
            $model->longitude  =   $row['longitude'];
            $model->latitude  =   $row['latitude'];
            $date=$model->created_on = date('Y-m-d H:i:s');
            $model->modified_on = date('Y-m-d H:i:s');
            $model->entry_image_url = $row['entryImageUrl'];
            $model->entry_image_type = $row['entryImageType'];
                 
            if ($model->save()) {
                $date = date("Y-m-d",strtotime($model->created_on));
                $status='Active';
                $rows = (new Query())
                        ->select('*')
                        ->from('user_streaks')
                        ->where(['start_date' => $date])
                        ->andFilterWhere(['user_id' =>$id])
                        ->andFilterWhere(['status' =>$status])
                        ->one();
                        echo json_encode($rows);
                if ($rows) {
                  echo json_encode("found row");
                  //   $end_date = date("Y-m-d",strtotime($row['end_date']));
                  //   $check_date=date("Y-m-d",strtotime(date("Y-m-d")." -1 day"));
                    
                  //   if ($end_date==$check_date || $end_date==date('Y-m-d')) {

                  //       $count  = (int) $rows['count'];
                  //       $count++;
                  //       $connection = Yii::$app->db;
                  //       $update =  $connection->createCommand()->update('user_streaks', 
                  //             ['streak_count' => $count,
                  //              'end_date' => $date,
                  //              'modified_on' => date('Y-m-d H:i:s'),
                  //             ],
                  //             ['user_id' => $id,
                  //              'start_date' =>$date,
                  //              'status'] => $status)->execute();
                  // } else {
                  //       $connection = Yii::$app->db;
                  //       $update =  $connection->createCommand()->update('user_streaks', 
                  //             ['status' => 'Finished',
                  //              'modified_on' => date('Y-m-d H:i:s'),
                  //             ],
                  //             ['user_id' => $id,
                  //              'start_date' =>$date,
                  //              'status'] => $status)->execute();
                  //       $model_count = new Userstreaks();
                  //       $model_count->user_id = $id;
                  //       $model_count->start_date = $date;
                  //       $model_count->end_date = $date;
                  //       $model_count->streak_count = 1;
                  //       $model_count->status = 'Active';
                  //       $model_count->created_on = date('Y-m-d H:i:s');
                  //       $model_count->modified_on = date('Y-m-d H:i:s');
                  //       $model_count->save();
                  // } 
                  
                } else {
                  echo json_encode('not found');
                    $model_count = new Userstreaks();
                    $model_count->user_id = $id;
                    $model_count->start_date = $date;
                    $model_count->end_date = $date;
                    $model_count->streak_count = 1;
                    $model_count->status = 'Active';
                    $model_count->created_on = date('Y-m-d H:i:s');
                    $model_count->modified_on = date('Y-m-d H:i:s');
                    $model_count->save();
                }

            }


        }
        Yii::$app->response->statusCode=200;
        echo json_encode(array(
            'status'=>200,
            'data'=>array('message'=>'Entries created.')),JSON_PRETTY_PRINT
        );  
        
    }
  
    public function actionUpdate($id)
    {
    
        error_log("Update Reached");
        $request = Yii::$app->request;
        $username = $request->post('userName');
        $userfname = $request->post('userFirstName');
        $userlname = $request->post('userLastName');
        
        if (empty($username)) {
            Yii::$app->response->statusCode=400;
            echo json_encode(array(
                'status'=>400,
                'error'=>array(
                  'message'=>"Some fields are empty or missing."
                  )
                )
              );
        } else {
            try
            {
                $connection = Yii::$app->db;
                $update =  $connection->createCommand()->update('users', 
                          ['user_name' => $username,
                           'modified_on' => date('Y-m-d H:i:s'),
                          ],
                          ['Id' => $id])->execute();
         
                $model = $this->findModel($id);
                $response = array(
                    'userName'=>$model->user_name, 
                  );
                Yii::$app->response->statusCode=200;
                echo json_encode(array(
                  'status'=>200,
                  'data'=>array_filter($response)),JSON_PRETTY_PRINT
                );  
            } catch(Exception $e) {
                  Yii::$app->response->statusCode=400;
                  echo json_encode(array('status'=>400,'errors'=>"ss"),JSON_PRETTY_PRINT);
            }
        }
    }

    public function actionUpdateSettings($id)
    {
        $rows = (new Query())
                        ->select('*')
                        ->from('user_settings')
                        ->where(['user_id' => $id])
                        ->one();
                 
        $reminder_time = date("H:i:s",strtotime($rows['reminder_time']));
        $is_entry_visible = $rows['is_entry_visible'];
        $is_notification_allowed = $rows['is_notification_allowed'];
        $settings_id = $rows['Id'];

        //echo json_encode($reminder_time." ".$is_entry_visible." ".$is_notification_allowed);
        
        $request = Yii::$app->request;
        $post_reminder_time = (empty($request->post('reminderTime'))) ? date("Y-m-d H:i:s",strtotime($reminder_time)) : date("Y-m-d H:i:s",strtotime($request->post('reminderTime')));
        $post_is_entry_visible = (empty($request->post('isEntryVisible'))) ? $is_entry_visible : $request->post('isEntryVisible');
        $post_is_notification_allowed = (empty($request->post('isNotificationAllowed'))) ? $is_notification_allowed : $request->post('isNotificationAllowed');

        //echo json_encode(" post> ".$post_reminder_time." ".$post_is_entry_visible." ".$post_is_notification_allowed);
        try
        {
            $connection = Yii::$app->db;
            $update =  $connection->createCommand()->update('user_settings', 
                          ['reminder_time'=>$post_reminder_time,
                           'is_entry_visible'=>$post_is_entry_visible,
                           'is_notification_allowed'=>$post_is_notification_allowed,
                           'modified_on' => date('Y-m-d H:i:s'),
                          ],
                          ['Id' => $settings_id])->execute();
         
            $model_settings = $this->findSettingsModel($settings_id);
            $response = array(
                      'reminderTime' =>$model_settings->reminder_time,
                      'isEntryVisible' =>$model_settings->is_entry_visible,
                      'isNotificationAllowed' =>$model_settings->is_notification_allowed
                      );
            Yii::$app->response->statusCode=200;
            echo json_encode(array(
                  'status'=>200,
                  'data'=>array_filter($response)),JSON_PRETTY_PRINT
                );  
        } catch(Exception $e) {
                  Yii::$app->response->statusCode=400;
                  echo json_encode(array('status'=>400,'errors'=>"ss"),JSON_PRETTY_PRINT);
        }
        
    }

    public function actionDelete($id)
    {
        
    }

  
    protected function findModel($id)
    {
        if (($model = Users::findOne($id)) !== null) {

            return $model;
        } else {

            Yii::$app->response->statusCode=400;
            echo json_encode(array(
              'status'=>400,
              'error'=>array('message'=>"No result found"
                )
              )
            );
        }
    }
    protected function findSettingsModel($id)
    {
        if (($model = Usersettings::findOne($id)) !== null) {

            return $model;
        } else {

            Yii::$app->response->statusCode=400;
            echo json_encode(array(
              'status'=>400,
              'error'=>array('message'=>"No result found"
                )
              )
            );
        }
    }
}
