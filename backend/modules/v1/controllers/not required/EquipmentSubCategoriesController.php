<?php

namespace api\modules\v1\controllers;

use Yii;
use yii\filters\auth\HttpBasicAuth;
use api\modules\v1\models\Userinspectionsubcategories;
use api\modules\v1\models\Userinspectionanswers;
use api\modules\v1\models\Equipmentquestions;
use api\modules\v1\models\EquipmentquestionsSearch;
use api\modules\v1\models\Inspectionremarks;
use api\modules\v1\models\InspectionremarksSearch;
use api\modules\v1\models\Equipmentsubcategories;
use api\modules\v1\models\EquipmentsubcategoriesSearch;
use api\modules\v1\models\Equipmentcategories;
use api\modules\v1\models\Tokens;
use api\modules\v1\models\EquipmentcategoriesSearch;
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

class EquipmentSubCategoriesController extends ActiveController
{
    public function beforeAction($action)
    {
        $flag=0;
        if (parent::beforeAction($action)) {
            if ($this->action->id == 'create'||$this->action->id == 'view' ||
            $this->action->id == 'index' ||$this->action->id == 'update'||
            $this->action->id == 'delete' ) {
              
                Url::remember();
                $headers = Yii::$app->request->headers;
                $accept = $headers->get('access_token');
                $userid = $headers->get('user_id');
                $model = Tokens::findOne([
                    'token' => $accept,]);      
                if ($model) {
                       
                    $current=date('Y-m-d H:i:s');
                 
                    if  ($model->userId==$userid)   {
                        if  ($model->expiry>=$current) {
    
                            $model->expiry = date("Y-m-d H:i:s",strtotime(date("Y-m-d H:i:s")." +7 days"));
                            $model->modifiedOn=date('Y-m-d H:i:s');
                      
                            $model->save(); 
                            if ($model->save()) {
                                $flag=1; 
                            } else {
                                // Yii::$app->response->statusCode=400;
                                // echo json_encode(array('status'=>400,'error'=>array('message'=>"Something went wrong. Try again.")));
                                // exit(0);
                            }
    
                        } else {
                            // Yii::$app->response->statusCode=401;
                            // echo json_encode(array('status'=>401,'error'=>array('message'=>"Your session has expired. Sign in again.")));
                            // exit(0);
                         }
                    } else {
                        //  // echo json_encode( array($current,$model->expiry));
                        //  Yii::$app->response->statusCode=401;
                        //   echo json_encode(array('status'=>401,'error'=>array('message'=>"This token is not assigned to this user.")));
                        //   exit(0);
                    }
                  
                } else {
                    // Yii::$app->response->statusCode=400;
                    // echo json_encode(array('status'=>400,'error'=>array('message'=>"This token does not exist.")));
                    // exit(0);
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
                    'view'   => ['GET','OPTIONS'],
                    'create' => [ 'POST','OPTIONS'],
                    'update' => ['PUT', 'POST','OPTIONS'],
                    'delete' => ['POST', 'DELETE','OPTIONS'],
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

    public $modelClass = 'api\modules\v1\models\Equipmentsubcategories';   
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
       
    public function actionIndex($id)
    {
        $status='Active';
        $rows = (new Query())
            ->select('*')
            ->from('equipmentcategories')
            ->where(['equipmentCategoryId' => $id])
            ->all();
        if (empty($rows)) {
            Yii::$app->response->statusCode=400;
            echo json_encode(array('status'=>400,'error'=>array('message'=>"Category ID is invalid")));
        } else {

            $status='Active';
            $rows = (new Query())
                ->select('*')
                ->from('equipmentsubcategories')
                ->where([
                'equipmentCategoryId' => $id])
                ->andFilterWhere(['status' =>$status])
                ->all();
            $query = Equipmentsubcategories::find()    
                ->where(['equipmentCategoryId' => $id]);
            $count = $query->count();
            $pagination = new Pagination(['totalCount' => $count, 'defaultPageSize' => 1]);
            $models = $query->offset($pagination->offset)
                ->limit($pagination->limit)
                ->all();
            if (count($rows)>0) {

                Yii::$app->response->headers->set('Content-type', ['application/json']);
                Yii::$app->response->statusCode=200;   
                $data=array('status'=>200,'data'=>array_filter($rows)); 
                Yii::$app->response->data = $data;                               
            
            } else {
                Yii::$app->response->headers->set('Content-type', ['application/json']);
                Yii::$app->response->statusCode=200;                             
                echo json_encode(array('status'=>200,'data'=>'No sub categories found for this category.'),JSON_PRETTY_PRINT);
            }
        }
    }
    
    public function actionView($id,$sid)
    {
        $rows = (new Query())
            ->select('*')
            ->from('equipmentcategories')
            ->where(['equipmentCategoryId' => $id])
            ->all();
        if (empty($rows)) {
            Yii::$app->response->statusCode=400;
            echo json_encode(array('status'=>400,'error'=>array('message'=>"Category ID is invalid")));
        } else {
            $rows = (new Query())
                ->select('*')
                ->from('equipmentsubcategories')
                ->where(['equipmentSubCategoryId' => $sid])
                ->all();
            if ($rows) {
                Yii::$app->response->headers->set('Content-type', ['application/json']);
                Yii::$app->response->statusCode=200;        
                echo json_encode(array('status'=>200,'data'=>array_filter($rows)),JSON_PRETTY_PRINT);
            } else {
                Yii::$app->response->statusCode=400;        
                echo json_encode(array('status'=>400,'error'=>array('message'=>'This sub category does not exist.')),JSON_PRETTY_PRINT);
            }
        }
    }

    public function actionCreate($id)
    {
        $request = Yii::$app->request;
        $status = "Active";

        $model = new Equipmentsubcategories();
        $eqname = $request->post('equipmentSubCategoryName');
        $rows = (new Query())
            ->select('*')
            ->from('equipmentcategories')
            ->where(['equipmentCategoryId' => $id])
            ->all();
        if (empty($rows)) {
            Yii::$app->response->statusCode=400;
            echo json_encode(array('status'=>400,'error'=>array('message'=>"Category ID is invalid")));
        } else {

            if (empty($eqname)) {
                Yii::$app->response->statusCode=400;
                echo json_encode(array('status'=>400,'error'=>array('message'=>"Subcategory name required.")));
            } else {
                  
                $rows = (new Query())
                    ->select('*')
                    ->from('equipmentsubcategories')
                    ->where(['equipmentCategoryId' => $id])
                    ->andFilterWhere(['equipmentSubCategoryName' =>$eqname])
                    ->andFilterWhere(['status' =>$status])
                    ->one();

                if($eqname==$rows['equipmentSubCategoryName']){
                    Yii::$app->response->statusCode=400;
                    echo json_encode(array('status'=>400,'errors'=>array('message'=>'This sub category already exists')),JSON_PRETTY_PRINT);
                         
                } else {
            
                    $model->equipmentSubCategoryName = $eqname;
                    $date=$model->createdOn=date('Y-m-d H:i:s');
                    $model->modifiedOn=date('Y-m-d H:i:s');
                    $model->equipmentCategoryId=$id;
                    $model->status='Active';

       
                    $model->save();
                    if ($model->save()) {
                                      
                        Yii::$app->response->statusCode=200;
                        echo json_encode(array('status'=>200,'data'=>array_filter($model->attributes)),JSON_PRETTY_PRINT);
                    } else {
                        echo json_encode(array('status'=>400,'errors'=>$model->errors),JSON_PRETTY_PRINT);
                    }
                }
            }
        }
    }


    public function actionUpdate($id,$sid)
    {
        $status="Active";
        $request = Yii::$app->request;
        $post = $request->post();
        $questionIds =array();
        $updatequestionIds = array();
        $rows = (new Query())
            ->select('*')
            ->from('equipmentquestions')
            ->where(['equipmentSubCategoryId' => $sid])
            ->andFilterWhere(['status' =>$status])
            ->all();
        foreach ($rows as $row) {
            array_push($questionIds, (int)$row['equipmentQuestionId']);

        }

        foreach ($post['questions']['questions'] as $row) {
               
            if($row['questionId'] == null){
            
                $model = new Equipmentquestions();
                $model->equipmentQuestionTitle=$row['questionTitle'];
             
                $model->equipmentSubCategoryId=$sid;
                $date=$model->createdOn=date('Y-m-d H:i:s');
                $model->modifiedOn=date('Y-m-d H:i:s');
      
                $model->status='Active'; 
                $model->save();

            } else {
                array_push($updatequestionIds, (int)$row['questionId']);
                try {
                    $connection = Yii::$app->db;
                    $update =  $connection->createCommand()->update('equipmentquestions', ['equipmentQuestionTitle'=>$row['questionTitle']], ['equipmentQuestionId' => $row['questionId']])->execute();
                } catch (Exception $e) {
                    Yii::$app->response->statusCode=400;
                    echo json_encode(array('status'=>400,'errors'=>array('message' =>"Update failed" )),JSON_PRETTY_PRINT);
                }

            }
        }
        $deleteIds=array_diff($questionIds,$updatequestionIds);  
 
        foreach ($deleteIds as $row) {
            $model = Equipmentquestions::findOne($row);
            $model->status='Deleted';
            $model->save();

        }

        $connection = Yii::$app->db;
        $update =  $connection->createCommand()->update('equipmentsubcategories', ['equipmentSubCategoryName'=>$post['equipmentSubCategoryName']], ['equipmentSubCategoryId' => $sid])->execute();
        Yii::$app->response->statusCode=200;
        echo json_encode(array('status'=>200,'data'=>array('message' =>"Update Completed" )),JSON_PRETTY_PRINT);
    }

    public function actionDelete($id,$sid)
    {
        $rows = (new Query())
            ->select('*')
            ->from('equipmentcategories')
            ->where(['equipmentCategoryId' => $id])
            ->all();
        if(empty($rows))
        {
            Yii::$app->response->statusCode=400;
            echo json_encode(array('status'=>400,'error'=>array('message'=>"Category ID is invalid")));
        } else {

            $model = $this->findModel($sid);
            if ($model) { 
                $rows2= Equipmentquestions::findAll([
                    'equipmentSubCategoryId' => $sid,]);  
                
                foreach ($rows2 as $row ) {
    
                    $row->status='Deleted';
                    $row->save();
                //echo json_encode($row->status);
                }  
            //  $rows3= Userinspectionsubcategories::findAll([
            //     'subCategoryId' => $sid,]);

            //  foreach ($rows3 as $urow ) {

            //     $answerid= $urow->Id;
            //     //echo json_encode("answerId> ".$answerid." ".$sid);

            //     $rows4= Userinspectionanswers::findAll([
            //         'inspectionSubCategoryId' => $answerid,]);

            //     foreach ($rows4 as $arow ) {
            //     $arow->status="Deleted";
            //     $arow->save();


            //   }
            //   $urow->status="Deleted";
            //   $urow->save();
            // }

                $model->status='Deleted';
                if ($model->save()) {
      
                    echo json_encode(array('status'=>1,'data'=>
                        array('status'=>1,'message'=>'Deleted')),JSON_PRETTY_PRINT);
                } else {
    
                    echo json_encode(array('status'=>0,'error_code'=>400,'errors'=>$model->errors),JSON_PRETTY_PRINT);
                }
            }
        }
    }

    protected function findModel($id)
    {
        if (($model = Equipmentsubcategories::findOne($id)) !== null) {
            return $model;
        }

        Yii::$app->response->statusCode=400;
        echo json_encode(array('status'=>400,'errors'=>array('message'=>'This subcategory does not exist')),JSON_PRETTY_PRINT);    
        exit(0);
    }
}
