<?php

namespace api\modules\v1\controllers;

use Yii;
use yii\filters\auth\HttpBasicAuth;
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

class EquipmentQuestionsController extends ActiveController
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
             
                    if($model->userId==$userid){
                        if($model->expiry>=$current){

                            $model->expiry = date("Y-m-d H:i:s",strtotime(date("Y-m-d H:i:s")." +7 days"));
                            $model->modifiedOn=date('Y-m-d H:i:s');
                            $model->save(); 
                            if ($model->save()) {
                                $flag=1; 
                            } else {
                    //             Yii::$app->response->statusCode=400;
                    //   echo json_encode(array('status'=>400,'error'=>array('message'=>"Something went wrong. Try again.")));
                    //   exit(0);
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
            //   Yii::$app->response->statusCode=400;
            //     echo json_encode(array('status'=>400,'error'=>array('message'=>"This token does not exist.")));
            //     exit(0);
                }
            }
            if($flag==1 ) {
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
        $behaviors = parent::behaviors();

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
    
    public $modelClass = 'api\modules\v1\models\EquipmentQuestions';   
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
            ->from('equipmentsubcategories')
            ->where(['equipmentSubCategoryId' => $id])
            ->all();
        if (empty($rows)) {
            Yii::$app->response->statusCode=400;
            echo json_encode(array('status'=>400,'error'=>array('message'=>"Sub Category ID is invalid")));
        } else {
            $rows = (new Query())
                ->select('*')
                ->from('equipmentquestions')
                ->where(['equipmentSubCategoryId' => $id])
                ->andFilterWhere(['status' =>$status])
                ->all();
            $query = EquipmentQuestions::find()
                ->where(['equipmentSubCategoryId' => $id])
                ->andFilterWhere(['status' =>$status]);
     
            $count = $query->count();

            if (count($rows)>0) {
                Yii::$app->response->statusCode=200;  
                $data=array('status'=>200,'data'=>array_filter($rows));                         
                Yii::$app->response->data = $data;
            } else {
                Yii::$app->response->headers->set('Content-type', ['application/json']);
                Yii::$app->response->statusCode=200;                             
                echo json_encode(array('status'=>200,'data'=>'No questions found for this subcategory.'),JSON_PRETTY_PRINT);
            }
        }
    }

    public function actionCreate($id)
    {
        $rowcount = 0;
        $rows = (new Query())
            ->select('*')
            ->from('equipmentsubcategories')
            ->where(['equipmentSubCategoryId' => $id])
            ->all();
        if (empty($rows)) {
            Yii::$app->response->statusCode=400;
            echo json_encode(array('status'=>400,'error'=>array('message'=>"Sub Category ID is invalid")));
        } else {
            $questions=Yii::$app->request->post();
            $count = count($questions['questions']);
            foreach ($questions['questions'] as $row) {
                $model = new Equipmentquestions();
                $model->equipmentQuestionTitle=$row['name'];
             
                $model->equipmentSubCategoryId=$id;
                $date=$model->createdOn=date('Y-m-d H:i:s');
                $model->modifiedOn=date('Y-m-d H:i:s');
                $rowcount++;
                $model->status='Active'; 
                $model->save();
            }
            if ($rowcount==$count) {
                Yii::$app->response->statusCode=200;
                echo json_encode(array('status'=>200,'data'=>array('message'=>"Question List Created")));
            } else { 
                Yii::$app->response->statusCode=400;
                echo json_encode(array('status'=>400,'message'=>"Some error occurred."));
            }
        }
    }

//     public function actionUpdate($id)
//     {
//         $request = Yii::$app->request;
   
//         $model = $this->findModel($id);
//         if ($model->load(Yii::$app->request->post(),'') ) {
//             $model->attributes=$params;
//           //  $model = $this->findModel($id);
//             // echo json_encode($model->attributes);

//             $model->modifiedOn=date('Y-m-d H:i:s');
//             $model->save();
//  //echo json_encode($request);
//              if ($model->save() )
//              {
//          //$this->setHeader(200);
//                   echo json_encode(array('status'=>1,'data'=>array_filter($model->attributes)),JSON_PRETTY_PRINT);
//             //echo json_encode($name);
//              }else
//               {
//                              // $this->setHeader(400);
//                  echo json_encode(array('status'=>0,'error_code'=>400,'errors'=>$model->errors),JSON_PRETTY_PRINT);
//               }
//       }
//     }

    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    protected function findModel($id)
    {
        if (($model = Equipmentquestions::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
