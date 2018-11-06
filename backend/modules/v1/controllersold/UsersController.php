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
use api\modules\v1\models\tokens;
use api\modules\v1\models\roles;
use api\modules\v1\models\Usersettings;
use api\modules\v1\models\Userstreaks;

class UsersController extends ActiveController implements ViewContextInterface
{
    
    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        $flag=0;
        if (parent::beforeAction($action)) {
            date_default_timezone_set("Asia/Karachi");
            if ($this->action->id == 'update'|| $this->action->id == 'view' || $this->action->id == 'delete' || 
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
        
            if ($flag==1 ||  $this->action->id == 'create'|| $this->action->id == 'check-user'|| $this->action->id == 'options' || $this->action->id == 'login' || $this->action->id == 'request-password-reset' || $this->action->id == 'reset-password' || 
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


    public $modelClass = 'api\modules\v1\models\Users';   
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
    public function actionCheckUser()
    {
        $request = Yii::$app->request;
        $user_email = $request->post('userEmail');  

          $model = Users::findOne([
                  'user_email' => $user_email,]);
          if($model){
               Yii::$app->response->statusCode=400;
               echo json_encode(array(
                                    'status'=>400,
                                    'errors'=>array('message'=>'This email already exist')),JSON_PRETTY_PRINT
                                  );  
          } else {
             Yii::$app->response->statusCode=200;
               echo json_encode(array(
                                    'status'=>200,
                                    'data'=>array('message'=>'Success')),JSON_PRETTY_PRINT
                                  ); 
          }


    }

    public function actionLogin()
    {
        $request = Yii::$app->request;
        $user_email = $request->post('userEmail');
        $password = $request->post('userPassword');
    // error_log("We get ".$useremail." and ".$password);
    //   // echo json_encode($password);
        $model = Users::findOne([
                  'user_email' => $user_email,]);
       
        if ($model!== null) {

            $password_db=$model->user_password;
            $is_password_correct = password_verify($password, $password_db);

            if ($is_password_correct) {
                
                $user_id=$model->id;
                $access_token=Yii::$app->security->generateRandomString();
                $model_token = new tokens();
                $model_token->token =$access_token;
                $model_token->user_id = $user_id;
                $model_token->created_on=date('Y-m-d H:i:s');
                $model_token->modified_on=date('Y-m-d H:i:s');
                $model_token->expiry = date("Y-m-d H:i:s",strtotime(date("Y-m-d H:i:s")." +7 days"));
                $model->last_login = date('Y-m-d H:i:s');
                $model->save();

                $model_token->save(); 
        
                if ($model_token->save()) {
                    $rows = (new Query())
                            ->select('*')
                            ->from('user_settings')
                            ->where(['user_id' => $user_id])
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
                                'token' => $model_token->token,
                                'expiry' => $model_token->expiry,
                                'reminderTime' =>$reminder_time,
                                'isEntryVisible' =>$is_entry_visible,
                                'isNotificationAllowed' =>$is_notification_allowed
                    );
                
                    Yii::$app->response->statusCode=200;
                    echo json_encode(array(
                                    'status'=>200,
                                    'data'=>array_filter($response)),JSON_PRETTY_PRINT
                                  );                
                } else {

                    echo json_encode(array(
                                    'status'=>400,
                                    'errors'=>$model_token->errors),JSON_PRETTY_PRINT
                                  );
                }      
            } else {

                Yii::$app->response->statusCode=400;
                echo json_encode(array('status'=>400,
                                       'error'=>array(
                                          'message'=>"Password incorrect"
                                          )
                                        )
                                      );
            }
        } else {
        
            Yii::$app->response->statusCode=400;
            echo json_encode(array('status'=>400,
                                   'error'=>array(
                                      'message'=>"No email address found"
                                      )
                                    )
                                  );
        }
    }

    public function actionSocialLogin()
    {
        $request = Yii::$app->request;
        $user_email = $request->post('userEmail');
        //$password = $request->post('userPassword');
    // error_log("We get ".$useremail." and ".$password);
    //   // echo json_encode($password);
        $model = Users::findOne([
                  'user_email' => $user_email,]);
       
        if ($model!== null) {

            $password_db=$model->user_password;
            $is_password_correct = password_verify($password, $password_db);

            if ($is_password_correct) {
                
                $user_id=$model->id;
                $access_token=Yii::$app->security->generateRandomString();
                $model_token = new tokens();
                $model_token->token =$access_token;
                $model_token->user_id = $user_id;
                $model_token->created_on=date('Y-m-d H:i:s');
                $model_token->modified_on=date('Y-m-d H:i:s');
                $model_token->expiry = date("Y-m-d H:i:s",strtotime(date("Y-m-d H:i:s")." +7 days"));
                $model->last_login = date('Y-m-d H:i:s');
                $model->save();

                $model_token->save(); 
        
                if ($model_token->save()) {
                    $rows = (new Query())
                            ->select('*')
                            ->from('user_settings')
                            ->where(['user_id' => $user_id])
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
                                'token' => $model_token->token,
                                'expiry' => $model_token->expiry,
                                'reminderTime' =>$reminder_time,
                                'isEntryVisible' =>$is_entry_visible,
                                'isNotificationAllowed' =>$is_notification_allowed
                    );
                
                    Yii::$app->response->statusCode=200;
                    echo json_encode(array(
                                    'status'=>200,
                                    'data'=>array_filter($response)),JSON_PRETTY_PRINT
                                  );                
                } else {

                    echo json_encode(array(
                                    'status'=>400,
                                    'errors'=>$model_token->errors),JSON_PRETTY_PRINT
                                  );
                }      
            } else {

                Yii::$app->response->statusCode=400;
                echo json_encode(array('status'=>400,
                                       'error'=>array(
                                          'message'=>"Password incorrect"
                                          )
                                        )
                                      );
            }
        } else {
        
            Yii::$app->response->statusCode=400;
            echo json_encode(array('status'=>400,
                                   'error'=>array(
                                      'message'=>"No email address found"
                                      )
                                    )
                                  );
        }
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

    public function actionCreate()
    {	
		
        $flag=0;
		    $model = new Users();
		    $request = Yii::$app->request;
		
        if ($model->load(Yii::$app->request->post(),'') ) {
			
			      $user_name 		= 	$request->post('userName');
			      $user_fname 		= 	$request->post('userFirstName');
			      $user_lname 		= 	$request->post('userLastName');
		  	    $pass 			= 	$request->post('userPassword');
			      $email 			= 	$request->post('userEmail');
			 
            if (empty($pass) || empty($email) || empty($user_name) ) {
				    
                Yii::$app->response->statusCode=400;
				        echo json_encode(array(
                    'status'=>400,
                    'error'=>array(
                      'message'=>"Some fields are missing.")
                    )
                );

			      } else {

				        $rows = (new Query())
              					->select('*')
              					->from('users')
              					->where(['user_email' => $email])
              					->one();
              				   
				        if ($rows) {
        						Yii::$app->response->statusCode=400;
        						echo json_encode(array(
                                        'status'=>400,
                                        'errors'=>array(
                                          'message'=>'This email already exists'
                                          )
                                        ),JSON_PRETTY_PRINT);
                                      exit(0);
					      }
							  $model->user_email =  $email;
				        $model->user_name 		=	(empty($user_name)) ? "" : $user_name;
				        $model->first_name	= 	(empty($user_fname)) ? "" : $user_fname;
				        $model->last_name	=	(empty($user_lname)) ? "" : $user_lname;
				        $model->user_password = password_hash($pass, PASSWORD_DEFAULT);
				        $date=$model->created_on = date('Y-m-d H:i:s');
				        $model->modified_on = date('Y-m-d H:i:s');
                $model->last_login = date('Y-m-d H:i:s');
                $model->snap_count_per_day = "";
                $model->snap_count_in_row = "";
                $model->reset_token = "";
 				        $model->reset_expiry = "";
				        $model->role_id = 2; // 1 for users 
				        $model->status = 'Active';
				        $model->save();

				        if ($model->save()) {
                    date_default_timezone_set("Asia/Karachi");
					          $response = null;
                    $user_id=$model->id;
                    $access_token=Yii::$app->security->generateRandomString();
                    $model_token = new tokens();
                    $model_token->token =$access_token;
                    $model_token->user_id = $user_id;
                    $model_token->created_on=date('Y-m-d H:i:s');
                    $model_token->modified_on=date('Y-m-d H:i:s');
                    $model_token->expiry = date("Y-m-d H:i:s",strtotime(date("Y-m-dH:i:s")." +7 days"));
                    $model_token->save();

                    $model_settings = new Usersettings();
                    $model_settings->user_id = $user_id;
                    $model_settings->reminder_time = date("Y-m-d H:i:s",strtotime("18:00:00"));
                    $model_settings->is_entry_visible = "False";
                    $model_settings->is_notification_allowed = "True";
                    $model_settings->created_on=date('Y-m-d H:i:s');
                    $model_settings->modified_on=date('Y-m-d H:i:s');
                    $model_settings->save();

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
                      'token' => $model_token->token,
                      'expiry' => $model_token->expiry,
                      'reminderTime' =>$model_settings->reminder_time,
                      'isEntryVisible' =>$model_settings->is_entry_visible,
                      'isNotificationAllowed' =>$model_settings->is_notification_allowed
    								);
                    Yii::$app->response->statusCode=200;
                    echo json_encode(array(
                      'status'=>200,
                      'data'=>array_filter($response)),JSON_PRETTY_PRINT
                    );
                } else {
          					Yii::$app->response->statusCode=400;
                    echo json_encode(array('status'=>400,
                      'errors'=> array(
                        'message'=>'User not created. Try again. Sorry for the inconvenience.')),JSON_PRETTY_PRINT
                    );
                }
            }
        }
    }

    public function actionSocialAccountCreate()
    { 
    
        $flag=0;
        $model = new Users();
        $request = Yii::$app->request;
    
        if ($model->load(Yii::$app->request->post(),'') ) {
      
            $user_name    =   $request->post('userName');
            $user_fname     =   $request->post('userFirstName');
            $user_lname     =   $request->post('userLastName');
            $pass       =   $request->post('userPassword');
            $email      =   $request->post('userEmail');
       
            if (empty($pass) || empty($email) || empty($user_name) ) {
            
                Yii::$app->response->statusCode=400;
                echo json_encode(array(
                    'status'=>400,
                    'error'=>array(
                      'message'=>"Some fields are missing.")
                    )
                );

            } else {

                $rows = (new Query())
                        ->select('*')
                        ->from('users')
                        ->where(['user_email' => $email])
                        ->one();
                         
                if ($rows) {
                    Yii::$app->response->statusCode=400;
                    echo json_encode(array(
                                        'status'=>400,
                                        'errors'=>array(
                                          'message'=>'This email already exists'
                                          )
                                        ),JSON_PRETTY_PRINT);
                                      exit(0);
                }
                $model->user_email =  $email;
                $model->user_name     = (empty($user_name)) ? "" : $user_name;
                $model->first_name  =   (empty($user_fname)) ? "" : $user_fname;
                $model->last_name = (empty($user_lname)) ? "" : $user_lname;
                $model->user_password = password_hash($pass, PASSWORD_DEFAULT);
                $date=$model->created_on = date('Y-m-d H:i:s');
                $model->modified_on = date('Y-m-d H:i:s');
                $model->last_login = date('Y-m-d H:i:s');
                $model->snap_count_per_day = "";
                $model->snap_count_in_row = "";
                $model->reset_token = "";
                $model->reset_expiry = "";
                $model->role_id = 2; // 1 for users 
                $model->status = 'Active';
                $model->save();

                if ($model->save()) {
                    date_default_timezone_set("Asia/Karachi");
                    $response = null;
                    $user_id=$model->id;
                    $access_token=Yii::$app->security->generateRandomString();
                    $model_token = new tokens();
                    $model_token->token =$access_token;
                    $model_token->user_id = $user_id;
                    $model_token->created_on=date('Y-m-d H:i:s');
                    $model_token->modified_on=date('Y-m-d H:i:s');
                    $model_token->expiry = date("Y-m-d H:i:s",strtotime(date("Y-m-dH:i:s")." +7 days"));
                    $model_token->save();

                    $model_settings = new Usersettings();
                    $model_settings->user_id = $user_id;
                    $model_settings->reminder_time = date("Y-m-d H:i:s",strtotime("18:00:00"));
                    $model_settings->is_entry_visible = "False";
                    $model_settings->is_notification_allowed = "True";
                    $model_settings->created_on=date('Y-m-d H:i:s');
                    $model_settings->modified_on=date('Y-m-d H:i:s');
                    $model_settings->save();

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
                      'token' => $model_token->token,
                      'expiry' => $model_token->expiry,
                      'reminderTime' =>$model_settings->reminder_time,
                      'isEntryVisible' =>$model_settings->is_entry_visible,
                      'isNotificationAllowed' =>$model_settings->is_notification_allowed
                    );
                    Yii::$app->response->statusCode=200;
                    echo json_encode(array(
                      'status'=>200,
                      'data'=>array_filter($response)),JSON_PRETTY_PRINT
                    );
                } else {
                    Yii::$app->response->statusCode=400;
                    echo json_encode(array('status'=>400,
                      'errors'=> array(
                        'message'=>'User not created. Try again. Sorry for the inconvenience.')),JSON_PRETTY_PRINT
                    );
                }
            }
        }
    }

    public function actionChangePassword()
    {
    
        $request = Yii::$app->request;
        $headers = Yii::$app->request->headers;
        $accept = $headers->get('user_id');
        $password = $request->post('userPassword');
        $new_password = $request->post('newPassword');
      
        if (empty($new_password)) {

            Yii::$app->response->statusCode=400;
            echo json_encode(array(
              'status'=>400,
              'error'=>array(
                'message'=>"New password field is empty. Please enter new password "
                )
              )
            );
        } else {
      
            $model = $this->findModel($accept);
          
            if ($model!==null) {
                
                $password_db=$model->user_password;
                $is_password_correct = password_verify($password, $password_db);
            
                if ($is_password_correct) {
                    
                    $model->userPassword=password_hash($newpassword,PASSWORD_DEFAULT);
                    $model->save();
                
                    if ($model->save()) {

                        $model->reset_token = '';
                        $model->reset_expiry= '';
                        $model->save();
                        Yii::$app->response->statusCode=200;
                        echo json_encode(array(
                          'status'=>200,
                          'data'=>array(
                            'message'=>'Reset password completed')),JSON_PRETTY_PRINT
                        );
                    
                    } else {

                        Yii::$app->response->statusCode=400;
                        echo json_encode(array(
                          'status'=>400,
                          'error'=>array(
                            'message'=>'Error occured while resetting password.Try again.')),JSON_PRETTY_PRINT
                        );
                    }
                } else {

                    Yii::$app->response->statusCode=400;
                    echo json_encode(array(
                      'status'=>400,
                      'error'=>array(
                        'message'=>'Password does not exist. Enter correct password.'.$accept)),JSON_PRETTY_PRINT
                    );
                }
            } else {

                Yii::$app->response->statusCode=400;
                echo json_encode(array(
                  'status'=>400,
                  'error'=>array(
                    'message'=>"User not found.")),JSON_PRETTY_PRINT
                );
            }
        }    
    }

    public function actionRequestPasswordReset()
    {
        $request = Yii::$app->request;
        $user_email = $request->post('userEmail');
        $model = Users::findOne([
              'user_email' => $user_email,]);
    
        if ($model!==null) {

            $model->reset_token=Yii::$app->security->generateRandomString();
            $model->reset_expiry=date("Y-m-d H:i:s",strtotime(date("Y-m-d H:i:s")." +1 day"));
               
            if ($model->save()) {
                Yii::$app->response->statusCode=200;
                echo json_encode(array(
                    'status'=>200,
                    'data'=>array('message'=>'Mail sent.')),JSON_PRETTY_PRINT
                );
        //code to be uncommented when on live server

                $value= Yii::$app->mailer->compose(['html'=>'passwordResetToken'], ['model' => $model])
                        ->setTo($useremail)
                        ->setFrom(['5sstardesigners@gmail.com' =>'Support'])
                        ->setSubject('Password Reset for ' . \Yii::$app->name)
                        ->send();
                      
                if ($value) {

                    Yii::$app->response->statusCode=200;
                    echo json_encode(array(
                        'status'=>200,
                        'data'=>array('message'=>'Mail sent.')),JSON_PRETTY_PRINT
                    );
                } else {
                      
                    Yii::$app->response->statusCode=400;
                    echo json_encode(array(
                        'status'=>400,
                        'error'=>array(
                          'message'=>'Mail not sent due to weak network.Try Again')),JSON_PRETTY_PRINT
                    );
                            
                }
            }
        } else {
                
            Yii::$app->response->statusCode=400;
            echo json_encode(array(
                'status'=>400,
                'error'=>array(
                  'message'=>'This email address does not exist.')),JSON_PRETTY_PRINT
            );
        }  
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
