<?php

namespace api\modules\v1\controllers;

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
use api\modules\v1\models\Users;
use api\modules\v1\models\Tokens;
use api\modules\v1\models\Equipmentquestions;
use api\modules\v1\models\EquipmentquestionsSearch;
use api\modules\v1\models\Inspectionremarks;
use api\modules\v1\models\InspectionremarksSearch;
use api\modules\v1\models\Equipmentsubcategories;
use api\modules\v1\models\EquipmentsubcategoriesSearch;
use api\modules\v1\models\Equipmentcategories;
use api\modules\v1\models\Role;
use api\modules\v1\models\ResetPasswordForm;
use api\modules\v1\models\UsersSearch;
require_once('config.php');

class UsersController extends ActiveController implements ViewContextInterface
{

    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        $flag=0;
        if (parent::beforeAction($action)) {
            if ($this->action->id == 'update'||$this->action->id == 'view' ||
            $this->action->id == 'delete' ||  $this->action->id == 'change-password' ||  
            $this->action->id == 'view-invoice') {
                Url::remember();
                $headers = Yii::$app->request->headers;
                $accept = $headers->get('access_token');
                $userid = $headers->get('user_id');
          
                $model = Tokens::findOne([
                'token' => $accept,]);      
        
                if ($model) {
                    $flag=1;
                    $current=date('Y-m-d H:i:s');
         
                    if ($model->userId==$userid) {
                        if ($model->expiry>=$current) {
                            $model->expiry = date("Y-m-d H:i:s",strtotime(date("Y-m-d H:i:s")." +7 days"));
                            $model->modifiedOn=date('Y-m-d H:i:s');
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
    
            if ($flag==1|| $this->action->id == 'index'|| $this->action->id == 'create'|| 
            $this->action->id == 'options' || $this->action->id == 'login' ||$this->action->id == 'request-password-reset' ||
            $this->action->id == 'reset-password'|| $this->action->id == 'test-post' || 
            $this->action->id == 'verify-code' || $this->action->id == 'code-change-password' || 
            $this->action->id == 'invoice' || $this->action->id == 'pay-invoice') {
                
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
                    'Origin' => ['http://localhost:8100','http://clients3.5stardesigners.net','*'],
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

    public $modelClass = 'api\modules\v1\models\Users';   
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
  
    public function actionPayInvoice()
    {     
        $connection = Yii::$app->db;
        $input = @file_get_contents('php://input');
        $event = json_decode($input);
      
        if ($event->type== 'charge.succeeded') {

            $customer_id = $event->data->object->customer;
            $customer = \Stripe\Customer::retrieve($customer_id);
            echo json_encode($customer->email);
            $update =  $connection->createCommand()->update('users', 
                ['subscription_type'=> 'Premium',
                'status' => 'Active',
                'modifiedOn' => date('Y-m-d H:i:s')
            ],
            ['userEmail' => $customer->email])->execute();
         
        }
        else if ($event->type== 'charge.failed') {
          
            $customer_id = $event->data->object->customer;
            $customer = \Stripe\Customer::retrieve($customer_id);
         
            $rows = (new Query())
                ->select('*')
                ->from('users')
                ->where(['userEmail' => $customer->email])
                ->one();
            $user_id = $rows['userId'];
            $update =  $connection->createCommand()->update('users', 
                ['status'=> 'Inactive',
                'subscription_type'=> '',
                'modifiedOn' => date('Y-m-d H:i:s')
                ],
                ['userEmail' => $customer->email])->execute();
            $connection->createCommand()
                ->delete('tokens', 'userId ='.$user_id)
                ->execute();
        }
    }
  
    public function actionLogin()
    {
        $connection = Yii::$app->db;
        $request = Yii::$app->request;
        $useremail = $request->post('userEmail');
        $password = $request->post('userPassword');
        $model = Users::findOne([
            'userEmail' => $useremail,]);
       
        if ($model!== null) {
            $password_db=$model->userPassword;
            $isPasswordCorrect = password_verify($password, $password_db);

            if  ($isPasswordCorrect) {
                if ($model->status=='Inactive') {
                    $userid = $model->userId;
                    $invoiceId = $model->invoiceId;
                    $invoice = \Stripe\Invoice::retrieve($invoiceId);
                    $invoice->closed = true;
                    $invoice->save();
                    $invoice_status = $invoice->closed;
                    $customer_id = $invoice->customer;
              
                    \Stripe\InvoiceItem::create([
                        'amount' => 100,
                        'currency' => 'usd',
                        'customer' => $customer_id,
                        'description' => 'One-time setup fee',
                    ]);
                
                    $charge = \Stripe\Invoice::create(array(
                        'customer' => $customer_id,
                        'billing' => 'send_invoice',
                        'days_until_due' => 30
                    ));
                    $invoiceId= $charge->id;
                    $invoiceExpiry =  date('Y-m-d',$charge->due_date);
                    $update =  $connection->createCommand()->update('users', 
                        ['invoiceId'=> $invoiceId,
                        'invoiceExpiry' => $invoiceExpiry,
                        'modifiedOn' => date('Y-m-d H:i:s')
                        ],
                        ['userId' => $userid])->execute();
              
                    Yii::$app->response->statusCode=200;
                    echo json_encode(array('status'=>200,
                        'error'=>array(
                            'message'=>"Inactive"
                            )
                        )
                    );
                } else {
                    $userId=$model->userId;
                    $access_token=Yii::$app->security->generateRandomString();
                    $modelToken = new Tokens();
                    $modelToken->token =$access_token;
                    $modelToken->userId = $userId;
                    $modelToken->createdOn=date('Y-m-d H:i:s');
                    $modelToken->modifiedOn=date('Y-m-d H:i:s');
                    $modelToken->expiry = date("Y-m-d H:i:s",strtotime(date("Y-m-d H:i:s")." +7 days"));

                    $modelToken->save(); 
        
                    if ($modelToken->save()) {
                        $response = array(
                            'userId'=>$model->userId,
                            'userName'=>$model->userName, 
                            'userFirstName'=>$model->userFirstName, 
                            'userLastName'=>$model->userLastName, 
                            'userCompany'=>$model->userCompany,
                            'userDepartment'=>$model->userDepartment,
                            'token'=>$modelToken->token,
                            'expiry'=>$modelToken->expiry,
                            'companyLogo'=>$model->companyLogo,
                            'profilePicture'=>$model->profilePicture,
                            'roleId'=>$model->roleId
                        );
                
                        Yii::$app->response->statusCode=200;
                        echo json_encode(array(
                            'status'=>200,
                            'data'=>array_filter($response)),JSON_PRETTY_PRINT
                        );
                            
                    } else {
                        echo json_encode(array(
                        'status'=>400,
                        'errors'=>$modelToken->errors),JSON_PRETTY_PRINT
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
            Yii::$app->response->statusCode=400;
            echo json_encode(array(
                'status'=>400,
                'error'=>array(
                    'message'=>"No email address found"
                    )
                )
            );
        }

    }
  
    public function actionViewInvoice()
    {
        $headers = Yii::$app->request->headers;
        $userid = $headers->get('user_id');
       
        $rows = (new Query())
            ->select('*')
            ->from('users')
            ->where(['userId' => $userid])
            ->one();
        $invoiceId = $rows['invoiceId'];
        $invoice = \Stripe\Invoice::retrieve($invoiceId);
        $invoice_url = str_replace("\\","",$invoice->hosted_invoice_url);
        if ($invoice_url) {
            Yii::$app->response->statusCode=200;
            echo json_encode(array(
               'status'=>200,
               'data'=>$invoice_url),JSON_UNESCAPED_SLASHES
            );
        } else {
            Yii::$app->response->statusCode=400;
            echo json_encode(array(
               'status'=>400,
               'error'=>array('message'=>"No result found"
              )),JSON_PRETTY_PRINT
           );
        }
    }
        
    public function actionView($id)
    {
        $rows = (new Query())
            ->select('*')
            ->from('users')
            ->where(['userId' => $id])
            ->one();
        $model = $this->findModel($id);
                     
        if ($rows) {
            Yii::$app->response->headers->set('Content-type', ['application/json']);
            $userid = $rows['userId'];
            $response = array(
                'userId'=>$model->userId,
                'userName'=>$model->userName,
                'userFirstName'=>$model->userFirstName,
                'userLastName'=>$model->userLastName,
                'userEmail'=>$model->userEmail,
                'userCompany'=>$model->userCompany,
                'userDepartment'=>$model->userDepartment,
                'nameToReceiveReport'=>$model->nameToReceiveReport,
                'emailToReceiveReport'=>$model->emailToReceiveReport,
                'companyLogo'=>$model->companyLogo,
                'profilePicture'=>$model->profilePicture,
                'roleId'=>$model->roleId
            );
            Yii::$app->response->statusCode=200;
            echo json_encode(array(
                'status'=>200,
                'data'=>array_filter($response)),JSON_PRETTY_PRINT
            );
        } else {
            Yii::$app->response->statusCode=400;
            echo json_encode(array(
                'status'=>400,
                'error'=>array(
                    'message'=>"No result found"
                    )
                )
            );
        }
    }

	public function actionCreate()
    {	
        $connection = Yii::$app->db;
		$flag=0;
		$model = new Users();
		$request = Yii::$app->request;
        if ($model->load(Yii::$app->request->post(),'')) {
			$fullpath		=	null;
			$fullpath2 		= 	null;
			$username 		= 	$request->post('userName');
			$userfname 		= 	$request->post('userFirstName');
			$userlname 		= 	$request->post('userLastName');
			$companyname 	= 	$request->post('userCompany');
			$pass 			= 	$request->post('userPassword');
			$email 			= 	$request->post('userEmail');
			$dept 			= 	$request->post('userDepartment');
			$rname 			=	$request->post('nameToReceiveReport');
			$remail 		=	$request->post('emailToReceiveReport');
			$company_logo 	= 	$request->post('companyLogo');
			$profile_pic 	= 	$request->post('profilePicture');
            if (empty($pass) || empty($email) || empty($rname) || empty($remail)) {
			    Yii::$app->response->statusCode=400;
				echo json_encode(array('status'=>400,'error'=>array('message'=>"Some fields are missing.")));
			} else {
                $sql  = "SELECT * FROM users WHERE userEmail= :email";
                $command = $connection->createCommand($sql);
                $command->bindValue(':email' , $email);
                $rows = $command->queryOne();
				// $rows = (new Query())
				// 	->select('*')
				// 	->from('users')
				// 	->where(['userEmail' => $email])
				// 	->one();
				   
				    if ($rows) {
						Yii::$app->response->statusCode=400;
						echo json_encode(array(
                                'status'=>400,
                                'errors'=>array(
                                  'message'=>'This email already exists'
                                  )
                                ),JSON_PRETTY_PRINT);
					} else {
				// start image uploads if there is any
    				if(!empty($company_logo))
    				{
    					$filename1 		=	(isset($companyname)) ? 'companyLogo_'.strtolower(preg_replace('/\s+/', '_', $companyname)).'.jpg' : 'companylogo_'.time().'.jpg';
    					$path1 			= 	"uploads/CompanyLogos/" . $filename1;
    					$fullpath 		=	$_SERVER['HTTP_HOST']."/safetyapp/api/web/uploads/CompanyLogos/".$filename1;
    					$company_logo	=	str_replace('data:image/jpeg;base64,','',$company_logo);		
    					$company_logo	=	str_replace('data:image/jpg;base64,','',$company_logo);		
    					$company_logo	=	str_replace(' ','+',$company_logo);		
    					$company_logo	=	base64_decode($company_logo);
    					file_put_contents($path1, $company_logo);
    				}	
				
    				if(!empty($profile_pic))
    				{
    					$filename2 		=	(isset($username)) ? 'username_'.strtolower(preg_replace('/\s+/', '_', $username)).'.jpg' : '$username_'.time().'.jpg';
    					$path2 			=	"uploads/ProfilePictures/" . $filename2;
    					$fullpath2 		=	$_SERVER['HTTP_HOST']."/safetyapp/api/web/uploads/ProfilePictures/".$filename2;
    					$profile_pic	=	str_replace('data:image/jpeg;base64,','',$profile_pic);		
    					$profile_pic	=	str_replace('data:image/jpg;base64,','',$profile_pic);		
    					$profile_pic	=	str_replace(' ','+',$profile_pic);		
    					$profile_pic	=	base64_decode($profile_pic);
    					file_put_contents($path2, $profile_pic);
    				}	
												
    				$model->userName 		=	(empty($username)) ? "" : $username;
    				$model->userFirstName	= 	(empty($userfname)) ? "" : $userfname;
    				$model->userLastName	=	(empty($userlname)) ? "" : $userlname;
    				$model->companyLogo=  $fullpath;
    				$model->profilePicture=  $fullpath2;         
    				$password = $request->post('userPassword');
    				$model->userPassword = password_hash($password, PASSWORD_DEFAULT);
    				$date=$model->createdOn=date('Y-m-d H:i:s');
    				$model->modifiedOn=date('Y-m-d H:i:s');
    				$model->lastLogin=date('Y-m-d H:i:s');
    				$model->roleId = 1; // 1 for users 
    				$model->status = 'Active';
    				$model->subscription_type = 'Free';
    				$model->save();

    				if ($model->save()) {
    				    $useremail = $model->userEmail;
                          
                        $customer = \Stripe\Customer::create(array(
                        "email" => $useremail,
                        "description" => "Customer for ".$useremail,
                        ));
                    
                        \Stripe\InvoiceItem::create([
                        'amount' => 100,
                        'currency' => 'usd',
                        'customer' => $customer->id,
                        'description' => 'One-time setup fee',
                        ]);
                    
                        $charge = \Stripe\Invoice::create(array(
                        'customer' => $customer->id,
                        'billing' => 'send_invoice',
                        'days_until_due' => 30
                        ));
                        
                        $invoiceId= $charge->id;
                        $invoiceExpiry =  date('Y-m-d',$charge->due_date);
                        $model->invoiceId = $invoiceId;
                        $model->invoiceExpiry = $invoiceExpiry;
                        $model->save();
                        $value= Yii::$app->mailer->compose(['html'=>'passwordResetToken'], ['model' => $model])
                        ->setTo($useremail)
                        ->setFrom(['5sstardesigners@gmail.com' =>'Support'])
                        ->setSubject('Password Reset for ' . \Yii::$app->name)
                        ->send();
    					$response = null;
    					$response = array(
    						'userId'=>$model->userId,
    						'userName'=>$model->userName, 
    						'userFirstName'=>$model->userFirstName, 
    						'userLastName'=>$model->userLastName,
    						'userEmail'=>$model->userEmail,
    						'userCompany'=>$model->userCompany,
    						'userDepartment'=>$model->userDepartment,
    						'nameToReceiveReport'=>$model->nameToReceiveReport,
    						'emailToReceiveReport'=>$model->emailToReceiveReport,
    						'companyLogo'=>$model->companyLogo,
    						'profilePicture'=>$model->profilePicture,
    						'roleId'=>$model->roleId,
    						//	'invoiceId' => $model->invoiceId,
    						//	'invoiceExpiry' => $model->invoiceExpiry
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
        
    				$modelid=$model->userId;
    				$status='Active';
                    $sql  = "SELECT * FROM defaultequipmentcategories WHERE defaultCategoryStatus= :status";
                    $command = $connection->createCommand($sql);
                    $command->bindValue(':status' , $status);
                    $rows = $command->queryAll();
                    $sqlsubcategory  = "SELECT * FROM defaultequipmentsubcategories WHERE defaultCategoryId= :id AND defaultSubCategoryStatus= :status";
                    $sqlquestions  = "SELECT * FROM defaultequipmentquestions WHERE defaultSubCategoryId= :id AND defaultQuestionStatus= :status";
    				foreach ($rows as $row) 
    				{
    				    $model = new Equipmentcategories();
    					$model->equipmentCategoryName = $row['defaultCategoryName'];
    					$model->status = $row['defaultCategoryStatus'];
    					$model->createdOn=date('Y-m-d H:i:s');
    					$model->modifiedOn=date('Y-m-d H:i:s');
    					$model->userId = $modelid;
    					$model->save();
    					$catId=$model->equipmentCategoryId;
                        $command = $connection->createCommand($sqlsubcategory);
                        $command->bindValue(':id' , $row['defaultCategoryId']);
                        $command->bindValue(':status' , $status);
                        $rowssub = $command->queryAll();
    
    					// $rowssub = (new Query())
    					// 	->select('*')
    					// 	->from('defaultequipmentsubcategories')
    					// 	->where([
    					// 	'defaultCategoryId' => $row['defaultCategoryId']])
    					// 	->andFilterWhere(['defaultSubCategoryStatus' =>$status])
    					// 	->all();
    
                        foreach ($rowssub as $srow) 
    					{
    						$model = new Equipmentsubcategories();
                            $model->equipmentSubCategoryName = $srow['defaultSubCategoryName'];
                            $model->status = $srow['defaultSubCategoryStatus'];
                            $model->createdOn=date('Y-m-d H:i:s');
                            $model->modifiedOn=date('Y-m-d H:i:s');
                            $model->equipmentCategoryId = $catId;
                            $model->save();
                            $subCatId = $model->equipmentSubCategoryId;
                            $command = $connection->createCommand($sqlquestions);
                            $command->bindValue(':id' , $srow['defaultSubCategoryId']);
                            $command->bindValue(':status' , $status);
                            $rowsques = $command->queryAll();
           //                  $rowsques = (new Query())
    							// ->select('*')
    							// ->from('defaultequipmentquestions')
    							// ->where([
    							// 	'defaultSubCategoryId' => $srow['defaultSubCategoryId']])
    							// ->andFilterWhere(['defaultQuestionStatus' =>$status])
    							// ->all();
    
                            foreach ($rowsques as $qrow) 
    						{
    						    $model = new Equipmentquestions();
                                $model->equipmentQuestionTitle = $qrow['defaultQuestionTitle'];
                                $model->status = $qrow['defaultQuestionStatus'];
                                $model->createdOn=date('Y-m-d H:i:s');
                                $model->modifiedOn=date('Y-m-d H:i:s');
                                $model->equipmentSubCategoryId = $subCatId;
                                $model->save();
                            }
    					}
    				}
			    }	
    		} 
    	}
	}

    public function actionUpdatess($id){
        error_log("Update Reached");
        $request = Yii::$app->request;
        $fullpath   = null;
        $fullpath2    =   null;
        $username = $request->post('userName');
        $userfname = $request->post('userFirstName');
        $userlname = $request->post('userLastName');
        $companyname = $request->post('userCompany');
        $email = $request->post('userEmail');
        $dept = $request->post('userDepartment');
        $rname =$request->post('nameToReceiveReport');
        $remail =$request->post('emailToReceiveReport');
        $company_logo   =   $request->post('companyLogo');
        $profile_pic  =   $request->post('profilePicture');
        
        echo json_encode($username);
          
    }
    
    public function actionUpdate($id)
    {
        error_log("Update Reached");
        $request = Yii::$app->request;
        $fullpath   = null;
        $fullpath2    =   null;
        $username = $request->post('userName');
        $userfname = $request->post('userFirstName');
        $userlname = $request->post('userLastName');
        $companyname = $request->post('userCompany');
        $email = $request->post('userEmail');
        $dept = $request->post('userDepartment');
        $rname =$request->post('nameToReceiveReport');
        $remail =$request->post('emailToReceiveReport');
        $company_logo   =   $request->post('companyLogo');
        $profile_pic  =   $request->post('profilePicture');

        if (empty($email) || empty($rname) || empty($remail))
        {
            Yii::$app->response->statusCode=400;
            echo json_encode(array(
                'status'=>400,
                'error'=>array(
                  'message'=>"Some fields are empty or missing."
                  )
                )
            );
        } else {
    	    $server_company = ".jpg";
            if (!empty($company_logo)) {
        	    if(strpos($company_logo, $server_company) !== false ) {
        	        $company_logo=str_replace("http://", "", $company_logo);	
        	        $fullpath =$company_logo;
        	    }
        	    else
        	    {
        	        $filename1    = (isset($companyname)) ? 'companyLogo_'.strtolower(preg_replace('/\s+/', '_', $companyname)).$id.'.jpg' : 'companylogo_'.time().'.jpg';
        	        $path1      =   "uploads/CompanyLogos/" . $filename1;
        	        $fullpath     = $_SERVER['HTTP_HOST']."/safetyapp/api/web/uploads/CompanyLogos/".$filename1;
        	        $company_logo = str_replace('data:image/jpeg;base64,','',$company_logo);    
        	        $company_logo = str_replace('data:image/jpg;base64,','',$company_logo);   
        	        $company_logo = str_replace(' ','+',$company_logo);   
        	        $company_logo = base64_decode($company_logo);
        	        file_put_contents($path1, $company_logo);
                }
            } else {
                $fullpath="";
            } 
        
            if(!empty($profile_pic))
            {
                if (strpos($profile_pic, $server_company) !== false ) {
            	    $profile_pic = str_replace("http://", "", $profile_pic);
    	        	$fullpath2 =$profile_pic;
    	        } else {
    
    		        $filename2    = (isset($username)) ? 'username_'.strtolower(preg_replace('/\s+/', '_', $username)).$id.'.jpg' : '$username_'.time().'.jpg';
    		        $path2      = "uploads/ProfilePictures/" . $filename2;
    		        $fullpath2    = $_SERVER['HTTP_HOST']."/safetyapp/api/web/uploads/ProfilePictures/".$filename2;
    		        $profile_pic  = str_replace('data:image/jpeg;base64,','',$profile_pic);   
    		        $profile_pic  = str_replace('data:image/jpg;base64,','',$profile_pic);    
    		        $profile_pic  = str_replace(' ','+',$profile_pic);    
    		        $profile_pic  = base64_decode($profile_pic);
    		        file_put_contents($path2, $profile_pic);
          		}
            } else {
            	$fullpath2="";
            }
            try
            {
                $connection = Yii::$app->db;
                $update =  $connection->createCommand()->update('users', 
                  ['userName'=>$username,
                  'userFirstName'=>$userfname,
                  'userLastName'=>$userlname,
                  'userCompany'=>$companyname,
                  'userDepartment'=>$dept,
                  'userEmail' => $email,
                  'nameToReceiveReport' => $rname,
                  'emailToReceiveReport' => $remail,
                  'companyLogo' => $fullpath,'profilePicture' => $fullpath2
                  ],
                  ['userId' => $id])->execute();
         
                $model = $this->findModel($id);
                $response = array(
                  'userId'=>$model->userId,
                  'userName'=>$model->userName,
                  'userFirstName'=>$model->userFirstName,
                  'userLastName'=>$model->userLastName,
                  'userEmail'=>$model->userEmail,
                  'userCompany'=>$model->userCompany,
                  'userDepartment'=>$model->userDepartment,
                  'nameToReceiveReport'=>$model->nameToReceiveReport,
                  'emailToReceiveReport'=>$model->emailToReceiveReport,
                  'companyLogo'=>$model->companyLogo,
                  'profilePicture'=>$model->profilePicture,
                  'roleId'=>$model->roleId
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

    public function actionChangePassword()
    {
        $request = Yii::$app->request;
        $headers = Yii::$app->request->headers;
       
        $accept = $headers->get('user_id');
        $password = $request->post('userPassword');
        $newpassword = $request->post('newPassword');
          
        if(empty($newpassword))
        {
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
                $password_db=$model->userPassword;
                $isPasswordCorrect = password_verify($password, $password_db);
                    
                if ($isPasswordCorrect) {
                    $model->userPassword=password_hash($newpassword,PASSWORD_DEFAULT);
                    $model->save();
                        
                    if ($model->save()) {
                        $model->resetCode = '';
                        $model->resetCodeExpiry= '';
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
  
    public function actionCodeChangePassword()
    {
        $request = Yii::$app->request;
        $newpassword = $request->post('newPassword');
        $useremail = $request->post('userEmail');
           
        if (empty($newpassword)) {
            Yii::$app->response->statusCode=400;
            echo json_encode(array(
                'status'=>400,
                'error'=>array(
                    'message'=>"New password field is empty. Please enter new password "
                    )
                ),JSON_PRETTY_PRINT
            );
        } else {
            $model = Users::findOne([
                'userEmail' => $useremail,]);
              
            if ($model!==null) {
                 
                $model->userPassword=password_hash($newpassword,PASSWORD_DEFAULT);
                $model->save();
                
                if ($model->save()) {
                    $model->resetCode = '';
                    $model->resetCodeExpiry= '';
                    $model->save();
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

    public function actionRequestPasswordReset()
    {
        $request = Yii::$app->request;
        $useremail = $request->post('userEmail');
        $model = Users::findOne([
            'userEmail' => $useremail,
            'status' => 'Active']);
        
        if ($model!==null) {
            $password_db=$model->userPassword;
            $model->resetCode=rand(1,999999);
            $model->resetCodeExpiry=date("Y-m-d H:i:s",strtotime(date("Y-m-d H:i:s")." +1 day"));
               
            if ($model->save()) {
    
                $value= Yii::$app->mailer->compose(['html'=>'passwordResetToken'], ['model' => $model])
                  ->setTo($useremail)
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

    public function actionVerifyCode()
    {
        $request = Yii::$app->request;
        $email = $request->post('userEmail');
        $code=$request->post('resetCode');
        $current = date('Y-m-d H:i:s');
        $model = Users::findOne([
            'userEmail' => $email,]);
              
        if ($model!==null) {
            $vcode=$model->resetCode;
                    
            if ($vcode==$code) {
                if ($model->resetCodeExpiry>=$current) {
                    Yii::$app->response->statusCode=200;
                    echo json_encode(array(
                        'status'=>200,
                        'error'=>array('message'=>'Success.')),JSON_PRETTY_PRINT
                    );
                } else {
                    Yii::$app->response->statusCode=400;
                    echo json_encode(array(
                        'status'=>400,
                        'error'=>array(
                            'message'=>'This code is expired. Kindly request for a new code.')),JSON_PRETTY_PRINT
                    );
                }
            } else {
                Yii::$app->response->statusCode=400;
                echo json_encode(array(
                    'status'=>400,
                    'error'=>array(
                        'message'=>'This code does not exist.')),JSON_PRETTY_PRINT
                );
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

    public function actionDelete($id)
    {
       
    }

    protected function findModel($id)
    {
        if (($model = Users::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
