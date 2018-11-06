<?php

namespace api\modules\v1\controllers;

use Yii;
use yii\filters\auth\HttpBasicAuth;
use api\modules\v1\models\Equipmentquestions;
use api\modules\v1\models\EquipmentquestionsSearch;
use api\modules\v1\models\Inspectionremarks;
use api\modules\v1\models\InspectionremarksSearch;
use api\modules\v1\models\Equipmentsubcategories;
use api\modules\v1\models\Userinspectionsubcategories;
use api\modules\v1\models\Userinspectionanswers;
use api\modules\v1\models\Userinspections;
use api\modules\v1\models\EquipmentsubcategoriesSearch;
use api\modules\v1\models\Equipmentcategories;
use api\modules\v1\models\Tokens;
use api\modules\v1\models\Users;
use api\modules\v1\models\EquipmentcategoriesSearch;
use yii\rest\ActiveController;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\ServerErrorHttpException;
use yii\filters\VerbFilter;
use yii\db\Query;
use yii\web\Response;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\ContentNegotiator;
use yii\data\Pagination;
use yii\helpers\Url;

class EquipmentCategoriesController extends ActiveController
{
    
    public function beforeAction($action)
    {
        $flag=0;
        if (parent::beforeAction($action)) {
            if ($this->action->id == 'create'||$this->action->id == 'view' ||$this->action->id == 'index' ||$this->action->id == 'update'||$this->action->id == 'delete' ) {
              
                Url::remember();
                $headers = Yii::$app->request->headers;
                $accept = $headers->get('access_token');
                $userid = $headers->get('user_id');

                $model = Tokens::findOne([
                    'token' => $accept,]);      
                if ($model) {
                   
                    $current=date('Y-m-d H:i:s');
                    if ($model->userId==$userid) {
                        if ($model->expiry>=$current) {

                            $model->expiry = date("Y-m-d H:i:s",strtotime(date("Y-m-d H:i:s")." +7 days"));
                            $model->modifiedOn=date('Y-m-d H:i:s');
                            $model->save(); 
                            if ($model->save()) {
                                $flag=1; 
                            } else {
                        //   Yii::$app->response->statusCode=400;
                        //   echo json_encode(array('status'=>400,'error'=>array('message'=>"Something went wrong. Try again.")));
                        //   exit(0);
                            }
                        } else {
                //             Yii::$app->response->statusCode=401;
                //   echo json_encode(array('status'=>401,'error'=>array('message'=>"Your session has expired. Sign in again.")));
                //   exit(0);
                        }
                    } else {
                //  Yii::$app->response->statusCode=401;
                //   echo json_encode(array('status'=>401,'error'=>array('message'=>"This token is not assigned to this user.")));
                //   exit(0);
                    }
              
                } else {
    //   Yii::$app->response->statusCode=400;
    //     echo json_encode(array('status'=>400,'error'=>array('message'=>"This token does not exist.")));
    //     exit(0);
                }
            }
            if ($flag==1 ) {
                return true;
            } else {
                Yii::$app->response->statusCode=400;
                echo json_encode(array('status'=>400,'error'=>array('message'=>"You are not authorized to perform this action.")));
                return false;
            }
        }
    }

    public function behaviors()
    {
        
        return [
            'verbs' => [
                'class' => \yii\filters\VerbFilter::className(),
                'actions' => [
                    'index'  => ['GET','OPTIONS'],
                    'view'   => ['GET', 'OPTIONS'],
                    'create' => [ 'POST', 'OPTIONS'],
                    'update' => ['PUT', 'POST', 'OPTIONS'],
                    'delete' => ['POST', 'DELETE', 'OPTIONS'],
                ],
            ],
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ], 
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
        ];
    }

    public $modelClass = 'api\modules\v1\models\Equipmentcategories';   
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
    public function actionIndex($userid)
    {
        $status='Active';
        $rows = (new Query())
			->select('*')
			->from('users')
			->where(['userId' => $userid])
			->all();
        
		if  (empty($rows))  {
            Yii::$app->response->statusCode=400;
            echo json_encode(array('status'=>400,'error'=>array('message'=>"User ID is invalid")));
        } else {
      
			$rows = (new Query())
				->select('*')
				->from('equipmentcategories')
				->where(['userId' => $userid])
				->andFilterWhere(['status' =>$status])
				->all();
				
			$query = Equipmentcategories::find()
				->where(['userId' => $userid]);
			$count = $query->count();
			$pagination = new Pagination(['totalCount' => $count, 'defaultPageSize' => 2]);
			$models = $query->offset($pagination->offset)
				->limit($pagination->limit)
				->all();
			
			if (count($rows)>0) {
                Yii::$app->response->headers->set('Content-type', ['application/json']);
                Yii::$app->response->statusCode=200; 
				echo(json_encode(array('status'=>200,'data'=>array_filter($rows)),JSON_PRETTY_PRINT)); exit;
			}
			else{
				//Yii::$app->response->headers->set('Content-type', ['application/json']);
                Yii::$app->response->statusCode=200;                             
                echo json_encode(array('status'=>200,'data'=>'No categories found for this user.'),JSON_PRETTY_PRINT);
				//exit;
			}
		}
	}
    
    public function actionView($userid,$id)
    {
        $rows = (new Query())
            ->select('*')
            ->from('users')
            ->where(['userId' => $userid])
            ->all();
        
        if (empty($rows)) {
            Yii::$app->response->statusCode=400;
            echo json_encode(array('status'=>400,'error'=>array('message'=>"User ID is invalid")));
        } else {
      
            $rows = (new Query())
                ->select('*')
                ->from('equipmentcategories')
                ->where(['equipmentCategoryId' => $id])
                ->all();
            if ($rows) {
                $rows = (array) $rows;
                Yii::$app->response->headers->set('Content-type', ['application/json']);
                Yii::$app->response->statusCode=200;        
                echo json_encode(array('status'=>200,'data'=>$rows),JSON_PRETTY_PRINT);
            } else {
                Yii::$app->response->statusCode=400;        
                echo json_encode(array('status'=>400,'error'=>array('message'=>'This category does not exist.')),JSON_PRETTY_PRINT);
            }
        }
    }
    
    public function actionCreate($userid)
    {
        $model = new Equipmentcategories();
        $request = Yii::$app->request;
        $eqname = $request->post('equipmentCategoryName');
        
        $rows = (new Query())
            ->select('*')
            ->from('users')
            ->where(['userId' => $userid])
            ->all();
        if (empty($rows)) {
            Yii::$app->response->statusCode=400;
            echo json_encode(array('status'=>400,'error'=>array('message'=>"User ID is invalid")));
        } else {
            $status="Active";
            if(empty($eqname)){
                Yii::$app->response->statusCode=400;
                echo json_encode(array('status'=>400,'error'=>array('message'=>"Category name required.")));
            } else {
                
                $rows = (new Query())
                    ->select('*')
                    ->from('equipmentcategories')
                    ->where(['userId' => $userid])
                    ->andFilterWhere(['equipmentCategoryName' =>$eqname])
                    ->andFilterWhere(['status' =>$status])
                    ->one();     
                if ($eqname==$rows['equipmentCategoryName']) {
                    Yii::$app->response->statusCode=400;
                    echo json_encode(array('status'=>400,'errors'=>array('message'=>'This category already exists')),JSON_PRETTY_PRINT);
                    //exit(0);
                } else {
                     
                    $model->equipmentCategoryName = $eqname;
                    $date=$model->createdOn=date('Y-m-d H:i:s');
                    $model->modifiedOn=date('Y-m-d H:i:s');
                    $model->userId=$userid;
                    $model->status='Active';
                    $model->save();
                    if ($model->save()) {
                                  
                        Yii::$app->response->statusCode=200;
                        echo json_encode(array('status'=>200,'data'=>array_filter($model->attributes)),JSON_PRETTY_PRINT);
                                
                    } else {
                        echo json_encode(array('status'=>0,'error_code'=>400,'errors'=>$model->errors),JSON_PRETTY_PRINT);
                    }
                } 
            }
        }
    }

    public function actionUpdate($userid,$id)
    {
        $request = Yii::$app->request;
        $request = Yii::$app->request;
        $catname = $request->post('equipmentCategoryName');
        $status="Active";
        if (empty($catname)) {
            Yii::$app->response->statusCode=400;
            echo json_encode(array('status'=>400,'error'=>array('message'=>"Category name required.")));
        } else {
            $rows = (new Query())
                ->select('*')
                ->from('users')
                ->where(['userId' => $userid])
                ->all();
            if (empty($rows)) {
                Yii::$app->response->statusCode=400;
                echo json_encode(array('status'=>400,'error'=>array('message'=>"User ID is invalid")));
            } else {
      
                $model = $this->findModel($id);
                if ($model) {
                    try 
                    {
                        $connection = Yii::$app->db;
                        $update =  $connection->createCommand()->update('equipmentcategories', ['equipmentCategoryName'=>$catname], ['equipmentCategoryId' => $id])->execute();
                        $model = $this->findModel($id);
                        Yii::$app->response->statusCode=200;
                        echo json_encode(array('status'=>200,'data'=>array_filter($model->attributes)),JSON_PRETTY_PRINT);
                    } catch(Exception $e) {
                        Yii::$app->response->statusCode=400;
                        echo json_encode(array('status'=>400,'errors'=>"ss"),JSON_PRETTY_PRINT);
                    }
                }
            }
        }
    }

    public function actionDelete($userid,$id)
    {
        $rows = (new Query())
            ->select('*')
            ->from('users')
            ->where(['userId' => $userid])
            ->all();
      
        if (empty($rows)) {
            Yii::$app->response->statusCode=400;
            echo json_encode(array('status'=>400,'error'=>array('message'=>"User ID is invalid")));
        } else {
            $model = $this->findModel($id);
         
            if ($model) { 

                $rows = (new Query())
                    ->select('*')
                    ->from('equipmentsubcategories')
                    ->where(['equipmentCategoryId' => $id])
                    ->all();
                $rows2= Equipmentsubcategories::findAll([
                    'equipmentCategoryId' => $id,]);  
                foreach ($rows2 as $row ) 
                {
                    $sid=$row->equipmentSubCategoryId;
                    $rows3= Equipmentquestions::findAll([
                        'equipmentSubCategoryId' => $sid,]);  

                    foreach ($rows3 as $srow ) 
                    {
               
                        $qid=$srow->equipmentQuestionId;
            
                        $row['status']='Deleted';
                        $srow->status='Deleted';
                        $srow->save();
                          // echo json_encode($srow->status);
                    }
                // $rows4= Userinspectionsubcategories::findAll([
                //         'subCategoryId' => $sid,]);
                // foreach ($rows4 as $urow ) 
                // {
                //     $answerid= $urow->Id;
                //     //echo json_encode("<br\>".$answerid." ".$sid);
                //     //echo " Hello";
                //     $rows5= Userinspectionanswers::findAll([
                //         'inspectionSubCategoryId' => $answerid,]);

                //     foreach ($rows5 as $arow ) 
                //     {
                //         $arow->status="Deleted";
                //         $arow->save();
                //     }
                //     $urow->status="Deleted";
                //     $urow->save();
                // }

                // $row['status']='Deleted';
                // $row->status='Deleted';
                // $row->save();
            
                }
            //  $rowsins= Userinspections::findAll([
            //   'categoryId' => $id,]);  

            //  foreach ($rowsins as $insrow ) 
            //         {
            //             $insrow->status="Deleted";
            //             $insrow->save();
            //         }

                $model->status='Deleted';
                if ($model->save())
                {
                    Yii::$app->response->statusCode=200;
                    echo json_encode(array('status'=>200,'data'=>
                        array('message'=>'Deleted')),JSON_PRETTY_PRINT);
      
                } else {
                    echo json_encode(array('status'=>400,'errors'=>array('message'=>"Something went wrong.Try Again")),JSON_PRETTY_PRINT);
                }
            }
        }
    }

    protected function findModel($id)
    {
        if (($model = Equipmentcategories::findOne($id)) !== null) {
            return $model;
        }

        Yii::$app->response->statusCode=400;
        echo json_encode(array('status'=>400,'errors'=>array('message'=>'This category does not exist')),JSON_PRETTY_PRINT);
    }
    protected function findUserModel($id)
    {
        if (($model = Users::findOne($id)) !== null) {
            return $model;
        }
    }
}
