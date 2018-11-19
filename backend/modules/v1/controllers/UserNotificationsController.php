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

class UsersController extends ActiveController
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
        
            if ($flag==1 ||  $this->action->id == 'options' || $this->action->id == 'create' || $this->action->id =='request-registration-token' || $this->action->id == 'verify-registration' || $this->action->id == 'login' || $this->action->id == 'request-password-reset' || $this->action->id == 'verify-token' || $this->action->id == 'reset-password' 
                ) {
               
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
        $flag=0;
        $user = new users();
        $request = Yii::$app->request;
        $role = new roles();

        $user_name       =   $request->post('user_name');
        $user_first_name      =   $request->post('first_name');
        $user_last_name      =   $request->post('last_name');
        $user_full_name    =   $request->post('full_name');
        $user_pass           =   $request->post('user_password');
        $user_email          =   $request->post('user_email');
        $user_type = 'user';

        if (empty($user_pass) || empty($user_email) || empty($user_name) || empty($user_full_name)) {
            Yii::$app->response->statusCode=400;
                echo json_encode(array('status'=>400,'error'=>array('message'=>"Some fields are missing.")));
        } else {
            $status = "Active";

            $rows_email = $user->getUserByEmail($user_email,$status);
            $rows_username = $user->getUserByUsername($user_name,$status);
            if ($rows_email || $rows_username) {
                    Yii::$app->response->statusCode=400;
                    echo json_encode(array(
                            'status'=>400,
                            'errors'=>array(
                              'message'=>'This email already exists or this username is taken'
                              )
                            ),JSON_PRETTY_PRINT);
            } else {
                $role_id = $role->getRoleId($user_type);
                if (!$role_id) {
                    Yii::$app->response->statusCode=400;
                    echo json_encode(array(
                            'status'=>400,
                            'errors'=>array(
                              'message'=>'Undefined User Type.'
                              )
                            ),JSON_PRETTY_PRINT);

                } else {
                    $response = $user->createUser($user_name,$user_first_name,$user_last_name,$user_full_name,$user_email,$user_pass,$role_id);

                    if ($response) {
                        Yii::$app->response->statusCode=200;
                        echo json_encode(array(
                            'status'=>200,
                            'errors'=>array(
                              'message'=>'User created.'
                              )
                            ),JSON_PRETTY_PRINT);
                    } else {
                        Yii::$app->response->statusCode=400;
                        echo json_encode(array(
                            'status'=>400,
                            'errors'=>array(
                              'message'=>'Error occurred.'
                              )
                            ),JSON_PRETTY_PRINT);
                    }
                }
            }
        }
    }

    public function actionRequestRegistrationToken()
    {
        $user = new users();
        $request = Yii::$app->request;
        $user_email = $request->post('user_email');
        $status = 'Unverified';
        $model = $user->getUserByEmail($user_email, $status);
 
        if ($model) {
            $reset_token= Yii::$app->security->generateRandomString() . '_' . time();
            $reset_expiry=date("Y-m-d H:i:s",strtotime(date("Y-m-d H:i:s")." +1 day"));
            $status = null;
            $model = $user->updateToken($user_email,$reset_token,$reset_expiry,$status);
               
            if ($model) {

                $value= Yii::$app->mailer->compose(['html'=>'verificationMail'], ['token' => $model['reset_token'], 'email' => $model['user_email'],'name' => $model['full_name']])
                        ->setTo($user_email)
                        ->setFrom(['5sstardesigners@gmail.com' =>'Support'])
                        ->setSubject('Registration Verification for ' . \Yii::$app->name)
                        ->send();
                      
                if ($value) {

                    Yii::$app->response->statusCode=200;
                    echo json_encode(array(
                        'status'=>200,
                        'data'=>array('message'=>'Mail sent.',
                                )
                    ),JSON_PRETTY_PRINT
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

    public function actionVerifyRegistration()
    {
        $connection = Yii::$app->db;
        $request = Yii::$app->request;
        $user_email = $request->get('email');
        $code=$request->get('token');
        $status = 'Active';
        $current_status = 'Unverified';
        $current = date('Y-m-d H:i:s');
        $user = new users();
        $model = $user->getUserByEmail($user_email, $current_status);
  
        if ($model) {
            if ($model['reset_token']!==null && $model['reset_token'] == $code) {
                 if ($model['reset_expiry']>=$current) {
                    $reset_token =null;
                    $reset_expiry = null;
                    $token_update = $user->updateToken($user_email,$reset_token,$reset_expiry,$status);
                    if($token_update) {
                        $id = $token_update['id'];
                        $list_status = "Active";
                        $contact_list = new contactLists();

                        $data =  $contact_list->createContactList($id,$list_status);



                        header("Location: http://localhost:85/test.php");
                        exit();
                    } else {
                        Yii::$app->response->statusCode=400;
                        echo json_encode(array(
                            'status'=>400,
                            'errors'=>array(
                              'message'=>$model->getErrors()
                              )
                            ),JSON_PRETTY_PRINT);  
                    }
                } else {
                    Yii::$app->response->statusCode=400;
                    echo json_encode(array(
                        'status'=>400,
                        'errors'=>array(
                          'message'=>$model->getErrors()
                          )
                        ),JSON_PRETTY_PRINT);
                }
            } else {
                Yii::$app->response->statusCode=400;
                echo json_encode(array(
                    'status'=>400,
                    'errors'=>array(
                      'message'=>$model->getErrors()
                      )
                    ),JSON_PRETTY_PRINT);
            }
        } else {
             Yii::$app->response->statusCode=400;
                        echo json_encode(array(
                            'status'=>400,
                            'errors'=>array(
                              'message'=>"Error occured."
                              )
                            ),JSON_PRETTY_PRINT);  
           
        }
    }

    public function actionLogin()
    {
        $request = Yii::$app->request;
        $user_email = $request->post('user_email');
        $password = $request->post('user_password');
        $status = 'Unverified';
        $c_status = "Active";
        $user = new users();
        $model=$user->getUserByEmail($user_email,$c_status);

        if ($model) {

            $password_db=$model['user_password'];
            $is_password_correct = password_verify($password, $password_db);

            if ($is_password_correct) {
                if ($model['status']== 'Unverified') {
                     Yii::$app->response->statusCode=200;
                        echo json_encode(array('status'=>200,
                                               'data'=>array(
                                                  'userEmail'=>"Unverified"
                                                  )
                                                )
                                              );
                }else{
                    $user_id=$model['id'];
                    $token = new tokens();
                    $access_token=Yii::$app->security->generateRandomString();
                    $expiry = date("Y-m-d H:i:s",strtotime(date("Y-m-d H:i:s")." +7 days"));
                    $model_token = $token->generateToken($access_token,$user_id,$expiry);
            
                    if ($model_token) {
                        $userdata = $user->getUserByEmail($user_email,$c_status);

                        $response = array(
                                    'userId'=>$userdata['id'],
                                    'userName'=>$userdata['user_name'], 
                                    'userFirstName'=>$userdata['first_name'],
                                    'userFullName'=>$userdata['full_name'], 
                                    'userLastName'=>$userdata['last_name'],
                                    'userEmail'=>$userdata['user_email'],
                                    'lastLogin'=>$userdata['last_login'],
                                    'token' => $model_token->token,
                                    'expiry' => $model_token->expiry,
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
            $model=$user->getUserByUsername($user_email,$c_status);
            if ($model) {
                //echo json_encode()
                $password_db=$model['user_password'];
                $is_password_correct = password_verify($password, $password_db);

                if ($is_password_correct) {
                    if ($model['status']== 'Unverified') {
                         Yii::$app->response->statusCode=200;
                            echo json_encode(array('status'=>200,
                                                   'data'=>array(
                                                      'userEmail'=>"Unverified"
                                                      )
                                                    )
                                                  );
                    }else{
                        $user_id=$model['id'];
                        $token = new tokens();
                        $access_token=Yii::$app->security->generateRandomString();
                        $expiry = date("Y-m-d H:i:s",strtotime(date("Y-m-d H:i:s")." +7 days"));
                        $model_token = $token->generateToken($access_token,$user_id,$expiry);
                
                        if ($model_token) {
                            $userdata = $user->getUserByUsername($user_email,$c_status);

                            $response = array(
                                        'userId'=>$userdata['id'],
                                        'userName'=>$userdata['user_name'], 
                                        'userFirstName'=>$userdata['first_name'],
                                        'userFullName'=>$userdata['full_name'], 
                                        'userLastName'=>$userdata['last_name'],
                                        'userEmail'=>$userdata['user_email'],
                                        'lastLogin'=>$userdata['last_login'],
                                        'token' => $model_token->token,
                                        'expiry' => $model_token->expiry,
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
                    }      
                } else {

                    Yii::$app->response->statusCode=400;
                    echo json_encode(array('status'=>400,
                                           'error'=>array(
                                              'message'=>"Password incorrect username"
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
    }

    public function actionRequestPasswordReset()
    {
        $request = Yii::$app->request;
        $user_email = $request->post('user_email');
        $user = new users();
        $status = 'Active';
        $model = $user->getUserByEmail($user_email, $status);
        if ($model) {
        
            $reset_token= rand(100000,999999);
            $reset_expiry=date("Y-m-d H:i:s",strtotime(date("Y-m-d H:i:s")." +1 day"));
            $status = null;
            $model = $user->updateToken($user_email,$reset_token,$reset_expiry,$status);
                  
            if ($model) {
                $value= Yii::$app->mailer->compose(['html'=>'passwordResetToken'], ['token' => $model['reset_token'], 'email' => $model['user_email'],'name' => $model['full_name']])
                    ->setTo($user_email)
                    ->setFrom(['info@clients2.5stardesigners.net' =>'Support'])
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

    public function actionVerifyToken()
    {
        $request = Yii::$app->request;
        $code = $request->post('code');
        $user_email = $request->post('user_email');
        $user = new users(); 
        $current=date('Y-m-d H:i:s');
        $status = "Active";
        $model = $user->getUserByEmail($user_email,$status);
        
        if ($model['reset_token']!==null && $model['reset_token'] == $code) {
            
            if ($model['reset_expiry']>=$current) {
                $reset_token =null;
                $reset_expiry = null;
                $status = null;
                $token_update = $user->updateToken($user_email,$reset_token,$reset_expiry,$status);
                if ($token_update) {
                
                    Yii::$app->response->statusCode=200;
                    echo json_encode(array('status'=>200,
                                        'data'=>array('message'=>'Success.')),JSON_PRETTY_PRINT
                    );  
                } else {
                    Yii::$app->response->statusCode=400;
                    echo json_encode(array('status'=>400,
                                  'error'=>array(
                                      'message'=>"Error occurred."
                                      )
                                )
                    );  
                }    
            } else {
                Yii::$app->response->statusCode=400;
                echo json_encode(array('status'=>400,
                                  'error'=>array(
                                      'message'=>"This token is expired. Try again."
                                      )
                                )
                );  
            }

         } else {
            Yii::$app->response->statusCode=400;
            echo json_encode(array('status'=>400,
                                  'error'=>array(
                                      'message'=>"This token is already used. Try again."
                                      )
                            )
            ); 
        }
    }

    public function actionResetPassword()
    {
        
        $request = Yii::$app->request;
        $new_password = $request->post('new_password');
        $user_email = $request->post('user_email');
        $user = new users();
       
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
            $status = "Active";
            $model = $user->getUserByEmail($user_email,$status);
          
            if ($model) {
                $password = password_hash($new_password,PASSWORD_DEFAULT);
                $update = $user->updateUserPassword($user_email,$password);
        
                if ($update) {
        
                    Yii::$app->response->statusCode=200;
                    echo json_encode(array('status'=>200,
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
                   'error'=>array('message'=>"User not found.")),JSON_PRETTY_PRINT
                );
            }
        }
    }

    public function actionSearchUser($id)
    {
        $request = Yii::$app->request;
        $key = $request->post('key');        
        $user = new users();
        $data = $user->search($key,$id);
        if($data){
            Yii::$app->response->statusCode=200;
            echo json_encode(array(
                'status'=>200,
                'data'=>$data),JSON_PRETTY_PRINT
            );
        } else {
            Yii::$app->response->statusCode=400;
            echo json_encode(array(
                'status'=>400,
                'error'=>array('message'=>"User not found.")),JSON_PRETTY_PRINT
            );  
        }
    }

    public function actionView($id)
    {
        //echo json_encode($id);
        $user = new users();
        $list = $user->getContacts($id);
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
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
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
