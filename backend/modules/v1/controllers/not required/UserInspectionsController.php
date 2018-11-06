<?php

namespace api\modules\v1\controllers;

use Yii;
use yii\filters\auth\HttpBasicAuth;
use api\modules\v1\models\Userinspectionsubcategories;
use api\modules\v1\models\Userinspectionanswers;
use api\modules\v1\models\Userinspectionreport;
use api\modules\v1\models\Userinspections;
use api\modules\v1\models\UserinspectionsSearch;
use api\modules\v1\models\Equipmentquestions;
use api\modules\v1\models\EquipmentquestionsSearch;
use api\modules\v1\models\Equipmentsubcategories;
use api\modules\v1\models\EquipmentsubcategoriesSearch;
use api\modules\v1\models\Equipmentcategories;
use api\modules\v1\models\Tokens;
use api\modules\v1\models\Users;
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

class UserInspectionsController extends ActiveController
{
    public function beforeAction($action)
    {
        $flag=0;
        if (parent::beforeAction($action)) {
            
            if ($this->action->id == 'create'||$this->action->id == 'view' ||
            $this->action->id == 'index' || $this->action->id == 'online-inspections' ||
            $this->action->id == 'update'|| $this->action->id == 'delete' || 
            $this->action->id == 'create-answer' || $this->action->id == 'create-report' || 
            $this->action->id == 'remarks'  || $this->action->id == 'test-post'|| 
            $this->action->id == 'category-remarks') {
        
                Url::remember();
                $headers = Yii::$app->request->headers;
                $accept = $headers->get('access_token');
                $userid = $headers->get('user_id');
               
                $model = Tokens::findOne([
                'token' => $accept,]);      
               
                if ($model) {
                    $flag=1;
                    $current=date('Y-m-d H:i:s');
          
                    if($model->userId==$userid) {
                        if($model->expiry>=$current) {
                            $model->expiry = date("Y-m-d H:i:s",strtotime(date("Y-m-d H:i:s")." +7 days"));
                            $model->modifiedOn=date('Y-m-d H:i:s');
              
                            $model->save(); 
                            if ($model->save()) {
                            } else {
                                // Yii::$app->response->statusCode=400;
                                // echo json_encode(array(
                                //     'status'=>400,
                                //     'error'=>array(
                                //     'message'=>"Something went wrong. Try again."
                                //   )
                                //   )
                                // );
                                // exit(0);
                            }
                        } else {
                            // Yii::$app->response->statusCode=401;
                            // echo json_encode(array(
                            //     'status'=>401,
                            //     'error'=>array(
                            //         'message'=>"Your session has expired. Sign in again."
                            //         )
                            //     )
                            // );
                            // exit(0);
                        }
                    } else {
                        // Yii::$app->response->statusCode=401;
                        // echo json_encode(array(
                        //   'status'=>401,
                        //   'error'=>array(
                        //     'message'=>"This token is not assigned to this user."
                        //     )
                        //   )
                        // );
                        // exit(0);
                    }
                } else {
                    // Yii::$app->response->statusCode=400;
                    // echo json_encode(array(
                    //     'status'=>400,
                    //     'error'=>array(
                    //         'message'=>"This token does not exist."
                    //         )
                    //     )
                    // );
                    // exit(0);
                }
            }  
              
            if ($flag==1  || $this->action->id == 'create-answer-test' ) {
                return true;
            } else {
                Yii::$app->response->statusCode=400;
                echo json_encode(array(
                  'status'=>400,
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
    
        $behaviors = parent::behaviors();
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

    public $modelClass = 'api\modules\v1\models\Userinspections';   
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
    
        public function actionOnlineInspections($userid)
    {
        $status = "Active";
        $inspection=array();
        $request = Yii::$app->request;
        
        $start = $request->get('start');
        $limit = $request->get('limit');
        
        $inspections = (new Query())
            ->select('*')
            ->from('userinspections')
            ->where(['userId' => $userid])
            ->andFilterWhere(['status' =>$status])
            ->limit($limit)
            ->offset($start)
            ->orderBy(['inspectionId' => SORT_DESC])
            ->all();
        $all_inspections  = count($inspections);
      
        if (!$inspections) {
            Yii::$app->response->statusCode=400;
            echo json_encode(array('status'=>400,'error'=>array('message'=>"User ID or Inspection ID is invalid")),JSON_PRETTY_PRINT
            );

        } else {
            Yii::$app->response->statusCode=200;
      
            foreach ($inspections as $rows)
            {
                $id=$rows['inspectionId'];
                $category=$rows['categoryId'];
                $rowscategory = (new Query())
                    ->select('*')
                    ->from('equipmentcategories')
                    ->where(['equipmentcategoryId' => $category])
                    ->andFilterWhere(['userId' => $userid])
                    ->one();
               // $path = $rows['equipmentInspectedImageUrl'];
               // $path = 'uploads/Inspections/EquipmentImages/equipmentInspection1537799036.jpg';            
                //$remove ="\\";
                //$path = str_replace($remove,'/',$path);
               // $path= str_replace("http:\\\\","",$path);
                //echo json_encode($path);
                //$type = pathinfo($path, PATHINFO_EXTENSION);
                //$data = file_get_contents($path);
            //    $rows['equipmentInspectedImageUrl'] = 'data:image/' . $type . ';base64,' . base64_encode($data);
              //  echo json_encode($rows['equipmentInspectedImageUrl']);
                $rowinssubcat = (new Query())
                    ->select('*')
                    ->from('userinspectionsubcategories')
                    ->where(['inspectionId' => $id])
                    ->andFilterWhere(['status' =>$status])
                    ->all();
                $subid=array();
                $question=array();
                $answer=array();

                foreach($rowinssubcat as $row)
                {
                    $subcategory=$row['subCategoryId'];
                    $rowsquestions = (new Query())
                        ->select('*')
                        ->from('equipmentquestions')
                        ->where(['equipmentSubCategoryId' => $row['subCategoryId']])
                        ->all();

                    array_push($question,$rowsquestions);
                    $ansSubcategory = $row['Id'];
                    $rowsanswers = (new Query())
                        ->select('*')
                        ->from('userinspectionanswers')
                        ->where(['inspectionSubCategoryId' => $row['Id']])
                        ->all();
                    array_push($answer,$rowsanswers);
                    array_push($subid, (int)$row['subCategoryId']);  
                }
                $rowsubcat = (new Query())
                    ->select('*')
                    ->from('equipmentsubcategories')
                    ->where(['equipmentSubCategoryId' => $subid])
                    ->all();

                $rowsreport = (new Query())
                    ->select('*')
                    ->from('userinspectionreport')
                    ->where(['inspectionId' => $id])
                    ->one();
                $dataIns=array(
                    'inspection'=>array(
                        'data' => $rows,
                        'inspectionSubCategories' => $rowinssubcat, 
                        'answers' => $answer,
                        'report'=>$rowsreport
                    ),
                    'category'=>array(
                        'data'=>$rowscategory,
                        'subCategories' => $rowsubcat,
                        'questions' => $question
                    )
                );
                array_push($inspection,$dataIns);
            }

            Yii::$app->response->statusCode=200;
            $data =array('inspections'=>array_filter($inspection));
            Yii::$app->response->data = $data;
        }
    }
 
    public function actionIndex($userid)
    {
        $status = "Active";
        $inspection=array();
        $inspections = (new Query())
            ->select('*')
            ->from('userinspections')
            ->where(['userId' => $userid])
            ->andFilterWhere(['status' =>$status])
            ->all();
        //echo json_encode(count($inspections));
      
        if (!$inspections) {
            Yii::$app->response->statusCode=400;
            echo json_encode(array('status'=>400,'error'=>array('message'=>"User ID or Inspection ID is invalid")),JSON_PRETTY_PRINT
            );

        } else {
            Yii::$app->response->statusCode=200;
      
            foreach ($inspections as $rows)
            {
                $id=$rows['inspectionId'];
                $category=$rows['categoryId'];
                $rowscategory = (new Query())
                    ->select('*')
                    ->from('equipmentcategories')
                    ->where(['equipmentcategoryId' => $category])
                    ->andFilterWhere(['userId' => $userid])
                    ->one();
            //    if (!empty($rows['equipmentInspectedImageUrl']) || $rows['equipmentInspectedImageUrl']!= null) {
                    //$path = $rows['equipmentInspectedImageUrl'];
            //    $path = 'uploads/Inspections/EquipmentImages/equipmentInspection1537799036.jpg';            
                    //$remove ="\\";
                    //$path = str_replace($remove,'/',$path);
                    //$path= str_replace("\\","\\",$path);
                    //echo json_encode($path);
                    //$type = pathinfo($path, PATHINFO_EXTENSION);
                    //$data = file_get_contents($path);
                    //$rows['equipmentInspectedImageUrl'] = 'data:image/' . $type . ';base64,' . base64_encode($data);
              //echo json_encode($rows['equipmentInspectedImageUrl']);
                //}
                $rowinssubcat = (new Query())
                    ->select('*')
                    ->from('userinspectionsubcategories')
                    ->where(['inspectionId' => $id])
                    ->andFilterWhere(['status' =>$status])
                    ->all();
                $subid=array();
                $question=array();
                $answer=array();

                foreach($rowinssubcat as $row)
                {
                    $subcategory=$row['subCategoryId'];
                    $rowsquestions = (new Query())
                        ->select('*')
                        ->from('equipmentquestions')
                        ->where(['equipmentSubCategoryId' => $row['subCategoryId']])
                        ->all();

                    array_push($question,$rowsquestions);
                    $ansSubcategory = $row['Id'];
                    $rowsanswers = (new Query())
                        ->select('*')
                        ->from('userinspectionanswers')
                        ->where(['inspectionSubCategoryId' => $row['Id']])
                        ->all();
                    array_push($answer,$rowsanswers);
                    array_push($subid, (int)$row['subCategoryId']);  
                }
                $rowsubcat = (new Query())
                    ->select('*')
                    ->from('equipmentsubcategories')
                    ->where(['equipmentSubCategoryId' => $subid])
                    ->all();

                $rowsreport = (new Query())
                    ->select('*')
                    ->from('userinspectionreport')
                    ->where(['inspectionId' => $id])
                    ->one();
                $dataIns=array(
                    'inspection'=>array(
                        'data' => $rows,
                        'inspectionSubCategories' => $rowinssubcat, 
                        'answers' => $answer,
                        'report'=>$rowsreport
                    ),
                    'category'=>array(
                        'data'=>$rowscategory,
                        'subCategories' => $rowsubcat,
                        'questions' => $question
                    )
                );
                array_push($inspection,$dataIns);
            }

            Yii::$app->response->statusCode=200;
            $data =array('inspections'=>array_filter($inspection));
            Yii::$app->response->data = $data;
        }
    }

    public function actionView($userid,$id)
    {
        $status = "Active";
        $rows = (new Query())
            ->select('*')
            ->from('userinspections')
            ->where(['userId' => $userid])
            ->andFilterWhere(['inspectionId' => $id])
            ->one();
        if (empty($rows)) {
            Yii::$app->response->statusCode=400;
            echo json_encode(array('status'=>400,'error'=>array('message'=>"User ID or Inspection ID is invalid")));
            //exit(0);
        } else {
            $category=$rows['categoryId'];
            $rowscategory = (new Query())
                ->select('*')
                ->from('equipmentcategories')
                ->where(['equipmentcategoryId' => $category])
                ->andFilterWhere(['userId' => $userid])
                ->one();
            $rowinssubcat = (new Query())
                ->select('*')
                ->from('userinspectionsubcategories')
                ->where(['inspectionId' => $id])
                ->andFilterWhere(['status' =>$status])
                ->all();
            $subid=array();
            $question=array();
            $answer=array();
            $result=array();
    
            foreach($rowinssubcat as $row)
            {
                $subcategory=$row['subCategoryId'];
                $rowsquestions = (new Query())
                    ->select('*')
                    ->from('equipmentquestions')
                    ->where(['equipmentSubCategoryId' => $row['subCategoryId']])
                    ->all();

                array_push($question,$rowsquestions);
                $ansSubcategory = $row['Id'];
                $rowsanswers = (new Query())
                    ->select('*')
                    ->from('userinspectionanswers')
                    ->where(['inspectionSubCategoryId' => $row['Id']])
                    ->all();
                array_push($answer,$rowsanswers);
                array_push($subid, (int)$row['subCategoryId']);
            }
            $rowsubcat = (new Query())
                ->select('*')
                ->from('equipmentsubcategories')
                ->where(['equipmentSubCategoryId' => $subid])
                ->all();
            $rowsreport = (new Query())
                ->select('*')
                ->from('userinspectionreport')
                ->where(['inspectionId' => $id])
                ->one();

            Yii::$app->response->statusCode=200;
            $data=array(
                'inspection'=>array(
          	        'data' => $rows,
          	        'inspectionSubCategories' => $rowinssubcat, 
          	        'answers' => $answer,
          	        'report'=>$rowsreport
                ),
                'category'=>array(
          	        'data' => $rowscategory,
          	        'subCategories' => $rowsubcat,
          	        'questions' => $question)
            );

            Yii::$app->response->data = $data;
          
        }
    }

    public function actionCategoryRemarks($id)
    {
        $request = Yii::$app->request;
        $subQueryCategories =$request->get('category_id');
        $catArray =explode(",",$subQueryCategories);
        $status="Active";
        $dataArray = array();
       
        //echo json_encode($catArray);
        
        foreach($catArray as $row){
            $rowcat = (new Query())
                ->select('*')
                 ->from('equipmentcategories')
                 ->where(['equipmentCategoryId' => $row])
                 ->andFilterWhere(['status' =>$status])
                 ->one();

             $CategoryName = $rowcat['equipmentCategoryName'];
             $tempArray['categoryId'] = $rowcat['equipmentCategoryId'];
             $tempArray['categoryName'] = $rowcat['equipmentCategoryName'];
             $rowsubcat = (new Query())
                ->select('*')
                ->from('equipmentsubcategories')
                ->where(['equipmentCategoryId' => $row])
                ->andFilterWhere(['status' =>$status])
                ->all();
             $subCategories = array();
             foreach($rowsubcat as $subcat){
                 
                 //echo json_encode($subcat['equipmentSubCategoryName']);
                 $tempSubCategories['subcategoryId'] = $subcat['equipmentSubCategoryId'];
                 $tempSubCategories['subcategoryName'] = $subcat['equipmentSubCategoryName'];
                 $rowsquestions = (new Query())
                 ->select('*')
                 ->from('equipmentquestions')
                 ->where(['equipmentSubCategoryId' => $subcat['equipmentSubCategoryId']])
                 ->andFilterWhere(['status' =>$status])
                 ->all();
                 $questions = array();
                    foreach($rowsquestions as $qrow)
             {
                 $tempquestion = array(
                     'questionId' =>$qrow['equipmentQuestionId'],
                     'questionTitle' =>$qrow['equipmentQuestionTitle']);
                 array_push($questions, $tempquestion);
             }
             $tempSubCategories['questions']=$questions;
                 
                 //echo json_encode($rowsquestions);
                 //echo json_encode("hello");
                 
                 
                 
                 array_push($subCategories,$tempSubCategories);
             }
             $tempArray['userSubCategories'] = $subCategories; 
             
             
             array_push($dataArray,$tempArray);
            // echo json_encode($CategoryName);
        }
        Yii::$app->response->data = $dataArray;
    }
    public function actionRemarks($id)
    {
        $request = Yii::$app->request;
        $subQueryCategories =$request->get('sub_category_id');
        $subCategories = array();
        $subCategory = array();
        $status="Active";
        $rowcat = (new Query())
            ->select('*')
            ->from('equipmentcategories')
            ->where(['equipmentCategoryId' => $id])
            ->one();
        $categoryName=$rowcat['equipmentCategoryName'];
        $subarray =explode(",",$subQueryCategories);

        foreach($subarray as $row)
        {
            $subid=$row;
            $rowsubcat = (new Query())
                ->select('*')
                ->from('equipmentsubcategories')
                ->where(['equipmentSubCategoryId' => $row])
                ->one();

            $subCategoryName = $rowsubcat['equipmentSubCategoryName'];
                     
            $rowsquestions = (new Query())
                ->select('*')
                ->from('equipmentquestions')
                ->where(['equipmentSubCategoryId' => $row])
                ->andFilterWhere(['status' =>$status])
                ->all();
            $tempdata = array(
                'subCategoryId'=>$row, 
                'subCategoryName' => $subCategoryName
            );
            $questions = array();
            foreach($rowsquestions as $qrow)
            {
                $tempquestion = array(
                    'questionId' =>$qrow['equipmentQuestionId'],
                    'questionTitle' =>$qrow['equipmentQuestionTitle']);
                array_push($questions, $tempquestion);
            }
            $tempdata['questions']=$questions;
            array_push($subCategories, $tempdata);
        }

        $data=array(
            'data'=>array(
                'categoryId'=>$id,
                'categoryName' =>$categoryName,
                'userSubCategories' => $subCategories
            ),
        );
        Yii::$app->response->data = $data;
    }
    public function actionTestPostss($id){
        $request = Yii::$app->request->post();
        Yii::$app->response->data = $request;
    }
    
    public function actionTestPost($id)
    {
        $status = 'Active';

        $rows = (new Query())
            ->select('*')
            ->from('users')
            ->where(['userId' => $id])
            ->andFilterWhere(['status' =>$status])
            ->one();
        if (empty($rows)) {
            Yii::$app->response->statusCode=400;
            echo json_encode(array(
                'status'=>400,
                'error'=>array(
                  'message'=>"User ID is invalid")));
        } else {
            $request = Yii::$app->request->post();
            $inspections = Yii::$app->request->post('inspections');
            $count = count($inspections);
            $mailcount = 0;
            foreach($inspections as $rows){
                if(empty($rows['inspectionDescription']) || empty($rows['subCategory']) || empty($rows['answers'])) {
                    Yii::$app->response->statusCode=400;
                    echo json_encode(array('status'=>400,'error'=>array('message'=>"Some fields are missing.")));
                    break;
                } else {
                    $answers = $rows['answers'];
                    $category_id = $answers['categoryId'];
                    
                    $rows_check = (new Query())
                        ->select('*')
                        ->from('equipmentcategories')
                        ->where(['equipmentCategoryId' => $category_id])
                        ->andFilterWhere(['userId' =>$id])
                        ->one();
                    if (empty($rows_check)) {
                        Yii::$app->response->statusCode=400;
                        echo json_encode(array('status'=>400,'error'=>array('message'=>"Category ID is invalid")));
                        break;
                    
                    } else {
                        $model = new Userinspections();
                        $fullpath   = null;
                        $fullpath2    =   null;
                        $flag=0;

                        $desc = $rows['inspectionDescription'];
                        $scat = $rows['subCategory'];
                        $rtype = $rows['reportType'];
                        if(!empty($rows['observationDescription'])) {
                            $odesc = $rows['observationDescription'];
                        } else {
                            $odesc = null;
                        }
                        //$odesc = $rows['observationDescription'];
                        if(!empty($rows['signatureUrl'])) {
                            $signature = $rows['signatureUrl'];
                        } else {
                            $signature = null;
                        }
                        if(!empty($rows['mediaUrl'])) {
                            $media = $rows['mediaUrl'];
                        } else {
                            $media = null;
                        }
                        if (!empty($rows['equipmentInspectedImageUrl'])) {
                                
                            $equipment_image   =   $rows['equipmentInspectedImageUrl'];
                            $filename2 = 'equipmentInspection'.time().'.jpg';
                            $path2      = "uploads/Inspections/EquipmentImages/" . $filename2;
                            $fullpath2    = addslashes("http:\\").$_SERVER['HTTP_HOST']."/safetyapp/api/web/uploads/Inspections/EquipmentImages/".$filename2;
                            $equipment_image  = str_replace('data:image/jpeg;base64,','',$equipment_image);   
                            $equipment_image  = str_replace('data:image/jpg;base64,','',$equipment_image);    
                            $equipment_image  = str_replace(' ','+',$equipment_image);    
                            $equipment_image  = base64_decode($equipment_image);
                            file_put_contents($path2, $equipment_image);
                                 
                        } else {
                            $fullpath2 = "null";
                            
                        }
                        $model->equipmentInspectedImageUrl=  $fullpath2;
                        $model->equipmentInspectedImageType = 'jpg';
                        $headers = Yii::$app->request->headers;
                        $accept = $headers->get('access_token');
                        $date=$model->createdOn=date('Y-m-d H:i:s');
                        $model->modifiedOn=date('Y-m-d H:i:s');
                        $model->inspectionDescription= $desc;
                        $model->userId=$id;
                        $model->categoryId=$category_id;
                        $model->status='Active';
                        $model->inspectionStatus = 'Incomplete';
                        if ($model->save()) {
                            $flag=1;
                            $inspection=   $model->inspectionId;
                            $rowcount=0;
                            $subcount= count($scat);
                            foreach ($scat as $row) {
                                $smodel = new Userinspectionsubcategories();
                                $smodel->inspectionId=$inspection;
                                $smodel->subCategoryId=$row;
                                $date=$smodel->createdOn=date('Y-m-d H:i:s');
                                $smodel->modifiedOn=date('Y-m-d H:i:s');
                                $smodel->status = "Active";

                                $smodel->save();
                                $rowcount++;             
                            }
                            if ($rowcount==$subcount && $flag==1) {
                                if (array_key_exists('userSubCategories',$answers)) {
                                    $anscount = count($answers['userSubCategories']);
                                    $ansrowcount =0;
                                    foreach ($answers['userSubCategories'] as $ansrow) {
                        
                                        $modelsub=Userinspectionsubcategories::findOne([
                                            'subCategoryId' => $ansrow['subCategoryId'],
                                            'inspectionId'=>$inspection,
                                        ]);

                                        foreach($ansrow['questions'] as $qrow){  
                                            $model=new Userinspectionanswers();
                                            $model->inspectionSubCategoryId = $modelsub->Id;
                                            $model->inspectionAnswerQuestionId = $qrow['questionId'];
                                            $model->status = "Active";
                                            if (array_key_exists('answer',$qrow)) {
                                                if ($qrow['answer']!='') {
                              		                $model->inspectionAnswer = $qrow['answer'];
                          	                    } else {
                              		                $model->inspectionAnswer = 'N/A';
                          	                    }
                                            } else {
                                                $model->inspectionAnswer = 'N/A';
                                            }
                                            $date=$model->createdOn=date('Y-m-d H:i:s');
                                            $model->modifiedOn=date('Y-m-d H:i:s'); 
                                            $model->save();
                                           
                                        }
                                        $ansrowcount++;
                                    }
                                    if($anscount == $ansrowcount){
                                       // echo json_encode("Hello");
                                        $value = $this->createOfflineReport($inspection,$rtype,$odesc,$signature,$media);
                                        if($value){
                                            $mailcount++;
                                        }
                                        
                                    } else {
                                        Yii::$app->response->statusCode=400;
                                        $data = array('status'=>400,'error'=>array('message'=>"Some error occurred."));
                                        Yii::$app->response->data = $data;
                                    }
                                
                                } else {
                                    Yii::$app->response->statusCode=400;
                                    $data = array('status'=>400,'error'=>array('message'=>"Some error occurred."));
                                    Yii::$app->response->data = $data;
                                }
                            } else {
                                Yii::$app->response->statusCode=400;
                                $data = array('status'=>400,'error'=>array('message'=>"Some error occurred."));
                                Yii::$app->response->data = $data;
                            }
                            
                        } else {
                            echo json_encode(array('status'=>0,'error_code'=>400,'errors'=>$model->errors),JSON_PRETTY_PRINT);
                        }
                        
                    }
                }
            }
            if ($count == $mailcount) {
                Yii::$app->response->statusCode=200;
                $data = array('status'=>200,'data'=>array('message'=>"Inspections Created"));
                Yii::$app->response->data = $data;
            } else {
                Yii::$app->response->statusCode=400;
                $data = array('status'=>400,'error'=>array('message'=>"Some error occurred."));
                Yii::$app->response->data = $data;
            }
        }
        
        
        //Yii::$app->response->data = $request;
        
        
    }
    public function createOfflineReport($id,$rtype,$odesc,$signature,$media)
    {
        $model = new Userinspectionreport();
       // $post=Yii::$app->request->post();
    //    $request = Yii::$app->request;
        if (!empty($id) ) {
            $fullpath   = null;
            $fullpath2    =   null;
            $media_url   =   $media;
            $signature_url  =   $signature;
            if (!empty($media_url)) {
                
                $filename1    = 'observationImage_'.time().'.jpg';
                $path1      =   "uploads/Inspections/FaultImages/" . $filename1;
                $fullpath     = 	addslashes("http:\\").$_SERVER['HTTP_HOST']."/safetyapp/api/web/uploads/Inspections/FaultImages/".$filename1;
                $media_url = str_replace('data:image/jpeg;base64,','',$media_url);    
                $media_url = str_replace('data:image/jpg;base64,','',$media_url);   
                $media_url = str_replace(' ','+',$media_url);   
                $media_url = base64_decode($media_url);
                file_put_contents($path1, $media_url);
                $model->mediaUrl=  $fullpath;
                $model->mediaType = 'jpg';
            } else {
                $model->mediaUrl = "";
                $model->mediaType = "";
            }
            if (empty($signature_url)){

                $model->signatureUrl ="";
                $connection = Yii::$app->db;
                $update =  $connection->createCommand()->update('userinspections', ['inspectionStatus'=>'Completed'],['inspectionId' => $id])->execute();
            } else {
                $connection = Yii::$app->db;
                $filename2    = 'signature_'.time().'.png';
                $path2      = "uploads/Inspections/Signatures/" . $filename2;
                $fullpath2    = 	addslashes("http:\\").$_SERVER['HTTP_HOST']."/safetyapp/api/web/uploads/Inspections/Signatures/".$filename2;
                $signature_url  = str_replace('data:image/png;base64,','',$signature_url);   
                $signature_url  = str_replace(' ','+',$signature_url);    
                $signature_url  = base64_decode($signature_url);
                file_put_contents($path2, $signature_url);
 
                $model->signatureUrl=  $fullpath2; 
                $update =  $connection->createCommand()->update('userinspections', ['inspectionStatus'=>'Signed'], ['inspectionId' => $id])->execute();
            }          
            $model->inspectionId = $id;
            $model->observationDescription = $odesc;
            $model->reportType= $rtype;
            $model->status= "Active";
            $model->createdOn=date('Y-m-d H:i:s');
            $model->modifiedOn=date('Y-m-d H:i:s');
            $model->save();
            if ($model->save()) {
                $rid = $model->inspectionReportId;
                $type=$model->reportType;
                if ($type=='safe') {
                    $subject='Passed';
                    $type="Passed and is safe to use";
                } else if ($type=='observation') {
                    $subject='Observation';
                    $type="Pass but with an observation";
                } else if ($type=='critical') {
                    $subject='Failed';
                    $type="Failed due to safety critical issue";
                }
                $oDescription=$model->observationDescription;
                if ($oDescription=="") {
                    $oDescription="N/A";
                }
                $signature= $model->signatureUrl;
                $mediaUrl = $model->mediaUrl;
                $mediaType = $model->mediaType;
                $row =  Userinspections::findOne([
                    'inspectionId' => $id,]);   
                $user = $row->userId;
                $inspectionDescription = $row->inspectionDescription;
                $eqid= $row->categoryId;
                //$inspectiondate=date("Y-m-d",strtotime($row->createdOn));
                $inspectiondate=$row->createdOn;
                $equipmentImage=$row->equipmentInspectedImageUrl;
            } else {
                Yii::$app->response->statusCode=400;
                echo json_encode(array('status'=>0,'error_code'=>400,'errors'=>$model->errors),JSON_PRETTY_PRINT);
            }
        }
        $rowuser =  Users::findOne([
            'userId' => $user,]);  
        $company = $rowuser->userCompany;
        $department = $rowuser->userDepartment; 
        $username = $rowuser->userName;
        $toreport = $rowuser->nameToReceiveReport;
        $toemail = $rowuser->emailToReceiveReport;
        
        
        $rowcat =  Equipmentcategories::findOne([
                                         'equipmentCategoryId' => $eqid,]);
        $catname = $rowcat->equipmentCategoryName;
        
        //start of report making
        
        $mpdf = new \Mpdf\Mpdf();
        $mpdf->SetDisplayMode('fullpage');
        $mpdf->list_indent_first_level = 0;  // 1 or 0 - whether to indent the first level of a list

        $html = '<!DOCTYPE html>
                    <html>
                        <head>
                            <style>
                                html, body {
                                    max-width: 98%;
                                }
                                /* bodypadding */
                                .customBodyPadding{
                                        color: #000;
                                        background-color: white;
                                        position: absolute;
                    
                                        left: 0;
                                        top: 0;
                                        position: relative;
                                        display: block;
                                        width: 100%;
                                        height: 100%;
                                        contain: layout size style;
                                }
                                /* customrow */
                                .customRow[justify-content-center] {
                                    -webkit-box-pack: center;
                                    -webkit-justify-content: center;
                                    -ms-flex-pack: center;
                                    justify-content: center;
                                }
                                
                                .customRow[align-items-center] {
                                    -webkit-box-align: center;
                                    -webkit-align-items: center;
                                    -ms-flex-align: center;
                                    align-items: center;
                                }
                                .customRow {
                                    display: -webkit-box;
                                    display: -webkit-flex;
                                    display: -ms-flexbox;
                                    display: flex;
                                    -webkit-flex-wrap: wrap;
                                    -ms-flex-wrap: wrap;
                                    flex-wrap: wrap;
                                }
                                /* customcol */
                                .customCol{
                                    padding: 5px;
                                    position: relative;
                                    width: 100%;
                                    margin: 0;
                                    min-height: 1px;
                                    -webkit-flex-basis: 0;
                                    -ms-flex-preferred-size: 0;
                                    flex-basis: 0;
                                    -webkit-box-flex: 1;
                                    -webkit-flex-grow: 1;
                                    -ms-flex-positive: 1;
                                    flex-grow: 1;
                                    max-width: 100%;
                                }
                                .customCol-4{
                                    -webkit-box-flex: 0;
                                    -webkit-flex: 0 0 33.33333%;
                                    -ms-flex: 0 0 33.33333%;
                                    flex: 0 0 33.33333%;
                                    width: 32.33333%;
                                    max-width: 32.33333%;
                                    padding: 0px;
                                }
                                .customCol-8{
                                    -webkit-box-flex: 0;
                                    -webkit-flex: 0 0 66.66667%;
                                    -ms-flex: 0 0 66.66667%;
                                    flex: 0 0 66.66667%;
                                    width: 65.66667%;
                                    max-width: 65.66667%;
                                    padding: 0px;
                                }

                                .customSeperator{
                                    position: relative;
                                    margin-top: 0px;
                                    border: 1px solid #bdbdbd;
                                }
                    
                                P{
                                    float: left;
                                    font-size: 0.8em;
                                }
                                .starterBackground{
                                    background-color: #F6F6F6;
                                    max-width: 100%;
                                    padding-left: 10px;
                                    border: 1px solid #F6F6F6;
                                    margin-top: 40px;
                                }
                                 .signatureBackground{
                                    background-color: #FFFFFF;
                                    
                                    padding-left: 10px;
                                    border: 1px solid #FFFFFF;
                                    margin-top: 40px;
                                }
                                /* mainpageheader */
                                .mainPageHeader{
                                    left: 0;
                                    top: 0;
                                    position: absolute;
                                    z-index: 10;
                                    display: block;
                                    width: 100%;
                                    text-align: center
                                }

                                /* mainpageheadertitle */
                                .mainPageHeaderTitle{
                                    overflow: hidden;
                                    -webkit-box-orient: horizontal;
                                    -webkit-box-direction: normal;
                                    -webkit-flex-direction: row;
                                    -ms-flex-direction: row;
                                    flex-direction: row;
                                    -webkit-box-align: center;
                                    -webkit-align-items: center;
                                    -ms-flex-align: center;
                                    align-items: center;
                                    -ms-flex-pack: justify;
                                    font-size: 2em;
                                    font-weight: 700;
                                    max-width: 100%;
                                }
                                img{
                                    width: 45%;
                                    height: 45%;
                                }
                                .signature{
                                    width: 20%;
                                    height: 20%;
                                }
                    
                                .header .col {
                                    background-color:lightgrey;
                                    }
                    
                                    .col {
                                    border: solid 1px grey;
                                    border-bottom-style: none;
                                    border-right-style: none;
                                    }
                    
                                    .col:last-child {
                                    border-right: solid 1px grey;
                                    }
                    
                                    .row:last-child .col {
                                    border-bottom: solid 1px grey;
                                    }
                                    h5{
                                        font-size: 0.8em;
                                    }
                            </style>
                        </head>
                        <body class="customBodyPadding">
                            <div class="mainPageHeader">
                                <div class="mainPageHeaderTitle">
                                    Pre-Use Inspection Report
                                </div>
                            </div>
                            <div class="starterBackground">
                                <table style="width:100%;">
                                    <tr>
                                        <td style="text-align: left; font-weight: 600; width: 33%;">Inspection Description :</td>
                                        <td style="text-align: left; font-weight: 600;">'.$inspectionDescription.' </td> 
                                    </tr>
                                    <tr>
                                        <td style="text-align: left; font-weight: 600; width: 33%;">Equipment Category :</td>
                                        <td style="text-align: left; font-weight: 600;">'.$catname.'</td> 
                                    </tr>
                                    <tr>
                                        <td style="text-align: left; font-weight: 600; width: 33%;">Inspected by :</td>
                                        <td style="text-align: left; font-weight: 600;">'.$username.'</td> 
                                    </tr>
                                    <tr>
                                        <td style="text-align: left; font-weight: 600; width: 33%;">Company :</td>
                                        <td style="text-align: left; font-weight: 600;">'.$company.'</td> 
                                    </tr>
                                    <tr>
                                        <td style="text-align: left; font-weight: 600; width: 33%;">Department :</td>
                                        <td style="text-align: left; font-weight: 600;">'.$department.'</td> 
                                    </tr>
                                    <tr>
                                        <td style="text-align: left; font-weight: 600; width: 33%;">Inspection Date :</td>
                                        <td style="text-align: left; font-weight: 600;">'.$inspectiondate.'</td> 
                                    </tr>
                                    <tr>
                                        <td style="text-align: left; font-weight: 600; width: 33%;">Final Remark :</td>
                                        <td style="text-align: left; font-weight: 600;">'. $type.'</td> 
                                    </tr>
                                    <tr>
                                        <td style="text-align: left; font-weight: 600; width: 33%;">Fault Description : </td>
                                        <td style="text-align: left; font-weight: 600;">'. $oDescription.'</td> 
                                    </tr>
                                </table>
                                <div class="customSeperator"></div>
                                <table style="width:100%;">
                                    <tr>
                                        <td style="text-align: left; font-weight: 600; width: 33%;">Report Sent To :</td>
                                        <td style="text-align: left; font-weight: 600;">'.$toreport.'</td> 
                                    </tr>
                                </table>
                                </div>
                                <div class="starterBackground">
                                    <table style="width:100%;">
                                        <tr>
                                            <td style="text-align: left; font-weight: 600; width: 33%;"><h1>Equipment Images</h1></td>
                                        </tr>
                                        <tr>
                                            <td style="text-align: left; font-weight: 600; width: 33%; "><span style="margin: 0 0 10px 0; line-height: 1.2em;">Equipment Image</span><br><br><img height =100px width=100px src="'.$equipmentImage.'"></td>
                                            <td style="text-align: left; font-weight: 600; width: 33%; "><span style="margin: 0 0 10px 0; line-height: 1.2em;">Fault Image</span><br><br><img src="'.$mediaUrl.'"></td>
                                           
                                        </tr>
                                    </table>
                                </div>
                                <br/>';
        $mpdf->WriteHTML($html);            
        $rows = (new Query())
            ->select('*')
            ->from('userinspectionsubcategories')
            ->where(['inspectionId' => $id])
            ->all();
        $rowcount=count($rows);    
        foreach ($rows as $row)  
        {
            $subid=$row['subCategoryId'];
            $sub = (new Query())
                ->select('*')
                ->from('equipmentsubcategories')
                ->where(['equipmentSubCategoryId' => $subid])
                ->one();

            $table= 
            '<h4>'.$catname."/".$sub['equipmentSubCategoryName'].'</h4>
            <table style="width:100%; border: 1px solid; margin-top: 10px;">
                <tr style="text-align: center; background-color: grey; color: white;">
                    <th>SI. NO</th>
                    <th>Questions</th> 
                    <th>Remarks</th>
                </tr>';


            $ansname = array();
            $rowsques = (new Query())
                ->select('*')
                ->from('equipmentquestions')
                ->where(['equipmentSubCategoryId' => $row['subCategoryId']])
                ->all();
            $rowsans = (new Query())
                ->select('*')
                ->from('userinspectionanswers')
                ->where(['inspectionSubCategoryId' => $row['Id']])
                ->all();
            foreach ($rowsans as $row) {
                 
                array_push($ansname, $row['inspectionAnswer']);

            }
                  
            $count=1; 
            $qname="Chief Safety";
            $i=0;
            $color = 'color:#FE0000';
            $mpdf->WriteHTML($table);  
            foreach ($rowsques as $qrow ) {
                if($ansname[$i]=='N/A'){
                    $color = 'color:#A9A9A9';
                }
                if($ansname[$i]=='Pass'){
                    $color = 'color:#4CAF50';
                }
                if($ansname[$i]=='Fail'){
                    $color = 'color:#FE0000';
                }
                $tablerows='
                    <tr style="text-align: center; font-weight: 500;">
                        <td style="border: 1px solid;">'.$count++.'</td>
                        <td style="border: 1px solid;">'.$qrow['equipmentQuestionTitle'].'</td>
                        
                        <td class="answers" style="border: 1px solid;'.$color.';">'.$ansname[$i].'</td>
                    </tr>';
                $mpdf->WriteHTML($tablerows);
                $i++;
            }                          
            
            $tableend='   </table>';
            $mpdf->WriteHTML($tableend);   
        }
        $end = ' 
                    <br/><br/> 
                    <div class="signatureBackground">
                        <table style="width:100%;">
                        
                            <tr>
                                <td style="text-align: left; font-weight: 60; width: 33%;"><h4>Signature of Inspector</h4></td>
                            </tr>
                            <tr>
                                <td style="text-align: left; font-weight: 60;"><img class ="signature" height =100px width=100px src="'.$signature.'"></td>
                            </tr>
                        </table>
                    </div>
                </body>
            </html>';


        $mpdf->WriteHTML($end,2);
      
        $filename=time().$id;         
        $mpdf->Output('attachments/'.$filename.'.pdf','F');
        $model=Userinspectionreport::findOne([
            'inspectionReportId' => $rid,]);
        if ($model) {
            $model->reportUrl  = 	addslashes("http:\\").$_SERVER['HTTP_HOST']."/safetyapp/api/web/attachments/".$filename;
            $model->save();
        }   
        $value= Yii::$app->mailer->compose(['html'=>'inspectionReport'], ['model' => $rowuser])
            ->setTo($toemail)
            ->setFrom(['info@clients2.5stardesigners.net' =>'Support'])
            ->setSubject('Inspection Report For ' . \Yii::$app->name.' ('.$subject.')')
            ->attach('attachments/'.$filename.'.pdf')
            ->send();
        if ($value) {
            return true;
            // Yii::$app->response->statusCode=200;
            // echo json_encode(array('status'=>200,'data'=>array('report'=>array_filter($model->attributes), 'message'=> "Mail sent.")),JSON_PRETTY_PRINT);
        } else {
            // Yii::$app->response->statusCode=400;
            // echo json_encode(array('status'=>400,'error'=>array('message'=>'Mail not sent due to weak network.Try Again')),JSON_PRETTY_PRINT);
            return false;
        }
    }
    
    public function actionCreate($userid,$id)
    {
        $status = 'Active';
        $request = Yii::$app->request->post();
      
        $rows = (new Query())
            ->select('*')
            ->from('users')
            ->where(['userId' => $userid])
            ->andFilterWhere(['status' =>$status])
            ->one();
        if (empty($rows)) {
            Yii::$app->response->statusCode=400;
            echo json_encode(array(
                'status'=>400,
                'error'=>array(
                  'message'=>"User ID is invalid")));
           // exit(0);
        }
        $rows_check = (new Query())
            ->select('*')
            ->from('equipmentcategories')
            ->where(['equipmentCategoryId' => $id])
            ->one();
        if (empty($rows_check)) {
            Yii::$app->response->statusCode=400;
            echo json_encode(array('status'=>400,'error'=>array('message'=>"Category ID is invalid")));
            //exit(0);
        }
        $rows_checknew = (new Query())
            ->select('*')
            ->from('equipmentcategories')
            ->where(['userId' => $userid])
            ->andFilterWhere(['status' =>$status])
            ->andFilterWhere(['equipmentCategoryId' =>$id])
            ->one();
        if (empty($rows_checknew)) {
            Yii::$app->response->statusCode=400;
            echo json_encode(array('status'=>400,'error'=>array('message'=>"This category does not exist for this user")));
            //exit(0);
        }
        
        $model = new Userinspections();
        $request = Yii::$app->request;
        $fullpath   = null;
        $fullpath2    =   null;
        $desc = $request->post('inspectionDescription');
        $scat = $request->post('subCategory');
        $answers = $request->post('answers');
        $equipment_image   =   $request->post('equipmentInspectedImageUrl');
        $flag=0;
        
        if (empty($desc) || empty($scat) || empty($answers)) {
            Yii::$app->response->statusCode=400;
            echo json_encode(array('status'=>400,'error'=>array('message'=>"Some fields are missing.")));
            //exit(0);
        } else {
            if (!empty($equipment_image)) {
                  // $filename2    = (isset($username)) ? 'username_'.strtolower(preg_replace('/\s+/', '_', $username)).'.jpg' : '$username_'.time().'.jpg';
                $filename2 = 'equipmentInspection'.time().'.jpg';
                $path2      = "uploads/Inspections/EquipmentImages/" . $filename2;
                $fullpath2    = addslashes("http:\\").$_SERVER['HTTP_HOST']."/safetyapp/api/web/uploads/Inspections/EquipmentImages/".$filename2;
                $equipment_image  = str_replace('data:image/jpeg;base64,','',$equipment_image);   
                $equipment_image  = str_replace('data:image/jpg;base64,','',$equipment_image);    
                $equipment_image  = str_replace(' ','+',$equipment_image);    
                $equipment_image  = base64_decode($equipment_image);
                file_put_contents($path2, $equipment_image);
            }
            $model->equipmentInspectedImageUrl=  $fullpath2;
            $model->equipmentInspectedImageType = 'jpg';
            $headers = Yii::$app->request->headers;
            $accept = $headers->get('access_token');
            $date=$model->createdOn=date('Y-m-d H:i:s');
            $model->modifiedOn=date('Y-m-d H:i:s');
            $model->inspectionDescription= $desc;
            $model->userId=$userid;
            $model->categoryId=$id;
            $model->status='Active';
            $model->inspectionStatus = 'Incomplete';
            if ($model->save()) {
                $flag=1;
                $inspection=   $model->inspectionId;
                $rowcount=0;
                $count= count($scat);
                foreach ($scat as $row) {
                    $smodel = new Userinspectionsubcategories();
                    $smodel->inspectionId=$inspection;
                    $smodel->subCategoryId=$row;
                    $date=$smodel->createdOn=date('Y-m-d H:i:s');
                    $smodel->modifiedOn=date('Y-m-d H:i:s');
                    $smodel->status = "Active";

                    $smodel->save();
                    $rowcount++;             
                }

                if ($rowcount==$count && $flag==1) {
                    if (array_key_exists('userSubCategories',$answers)) {
                        foreach ($answers['userSubCategories'] as $row) {
                        
                            $modelsub=Userinspectionsubcategories::findOne([
                             'subCategoryId' => $row['subCategoryId'],
                             'inspectionId'=>$inspection,
                            ]);

                            foreach($row['questions'] as $qrow){  
                                $model=new Userinspectionanswers();
                                $model->inspectionSubCategoryId = $modelsub->Id;
                                $model->inspectionAnswerQuestionId = $qrow['questionId'];
                                $model->status = "Active";
                                if (array_key_exists('answer',$qrow)) {
                                    if ($qrow['answer']!='') {
                              		    $model->inspectionAnswer = $qrow['answer'];
                          	        } else {
                              		    $model->inspectionAnswer = 'N/A';
                          	        }
                                } else {
                                    $model->inspectionAnswer = 'N/A';
                                }
                                $date=$model->createdOn=date('Y-m-d H:i:s');
                                $model->modifiedOn=date('Y-m-d H:i:s'); 
                                $model->save();
                                $rowcount++;
                           }
                        }
                        Yii::$app->response->statusCode=200;
                        $data = array('status'=>200,'data'=>array('message'=>"Answer List Created",
                        'inspectionId' =>$inspection));
                        Yii::$app->response->data = $data;
                    } else {
                        Yii::$app->response->statusCode=400;
                        $data = array('status'=>400,'error'=>array('message'=>"Some error occurred."));
                        Yii::$app->response->data = $data;
                    }
                } else {
                    Yii::$app->response->statusCode=400;
                    $data = array('status'=>400,'error'=>array('message'=>"Some error occurred."));
                    Yii::$app->response->data = $data;
               }
            } else {
                echo json_encode(array('status'=>0,'error_code'=>400,'errors'=>$model->errors),JSON_PRETTY_PRINT);
            }
        }
    }

    public function actionCreateReport($id)
    {
        $model = new Userinspectionreport();
        $post=Yii::$app->request->post();
        $request = Yii::$app->request;
        if ($model->load(Yii::$app->request->post(),'') ) {
            $fullpath   = null;
            $fullpath2    =   null;
            $media_url   =   $request->post('mediaUrl');
            $signature_url  =   $request->post('signatureUrl');
            if (!empty($media_url)) {
                
                $filename1    = 'observationImage_'.time().'.jpg';
                $path1      =   "uploads/Inspections/FaultImages/" . $filename1;
                $fullpath     = 	addslashes("http:\\").$_SERVER['HTTP_HOST']."/safetyapp/api/web/uploads/Inspections/FaultImages/".$filename1;
                $media_url = str_replace('data:image/jpeg;base64,','',$media_url);    
                $media_url = str_replace('data:image/jpg;base64,','',$media_url);   
                $media_url = str_replace(' ','+',$media_url);   
                $media_url = base64_decode($media_url);
                file_put_contents($path1, $media_url);
                $model->mediaUrl=  $fullpath;
                $model->mediaType = 'jpg';
            } else {
                $model->mediaUrl = "";
                $model->mediaType = "";
            }
            if (empty($signature_url)){

                $model->signatureUrl ="";
                $connection = Yii::$app->db;
                $update =  $connection->createCommand()->update('userinspections', ['inspectionStatus'=>'Completed'],['inspectionId' => $id])->execute();
            } else {
                $connection = Yii::$app->db;
                $filename2    = 'signature_'.time().'.png';
                $path2      = "uploads/Inspections/Signatures/" . $filename2;
                $fullpath2    = 	addslashes("http:\\").$_SERVER['HTTP_HOST']."/safetyapp/api/web/uploads/Inspections/Signatures/".$filename2;
                $signature_url  = str_replace('data:image/png;base64,','',$signature_url);   
                $signature_url  = str_replace(' ','+',$signature_url);    
                $signature_url  = base64_decode($signature_url);
                file_put_contents($path2, $signature_url);
 
                $model->signatureUrl=  $fullpath2; 
                $update =  $connection->createCommand()->update('userinspections', ['inspectionStatus'=>'Signed'], ['inspectionId' => $id])->execute();
            }          
            $model->inspectionId = $id;
            
            $model->status= "Active";
            $model->createdOn=date('Y-m-d H:i:s');
            $model->modifiedOn=date('Y-m-d H:i:s');
            $model->save();
            if ($model->save()) {
                $rid = $model->inspectionReportId;
                $type=$model->reportType;
                if ($type=='safe') {
                    $subject='Passed';
                    $type="Passed and is safe to use";
                } else if ($type=='observation') {
                    $subject='Observation';
                    $type="Pass but with an observation";
                } else if ($type=='critical') {
                    $subject='Failed';
                    $type="Failed due to safety critical issue";
                }
                $oDescription=$model->observationDescription;
                if ($oDescription=="") {
                    $oDescription="N/A";
                }
                $signature= $model->signatureUrl;
                $mediaUrl = $model->mediaUrl;
                $mediaType = $model->mediaType;
                $row =  Userinspections::findOne([
                    'inspectionId' => $id,]);   
                $user = $row->userId;
                $inspectionDescription = $row->inspectionDescription;
                $eqid= $row->categoryId;
                //$inspectiondate=date("Y-m-d",strtotime($row->createdOn));
                $inspectiondate=$row->createdOn;
                $equipmentImage=$row->equipmentInspectedImageUrl;
            } else {
                Yii::$app->response->statusCode=400;
                echo json_encode(array('status'=>0,'error_code'=>400,'errors'=>$model->errors),JSON_PRETTY_PRINT);
            }
        }
        $rowuser =  Users::findOne([
            'userId' => $user,]);  
        $company = $rowuser->userCompany;
        $department = $rowuser->userDepartment; 
        $username = $rowuser->userName;
        $toreport = $rowuser->nameToReceiveReport;
        $toemail = $rowuser->emailToReceiveReport;
        
        
        $rowcat =  Equipmentcategories::findOne([
                                         'equipmentCategoryId' => $eqid,]);
        $catname = $rowcat->equipmentCategoryName;
        
        //start of report making
        
        $mpdf = new \Mpdf\Mpdf();
        $mpdf->SetDisplayMode('fullpage');
        $mpdf->list_indent_first_level = 0;  // 1 or 0 - whether to indent the first level of a list

        $html = '<!DOCTYPE html>
                    <html>
                        <head>
                            <style>
                                html, body {
                                    max-width: 98%;
                                }
                                /* bodypadding */
                                .customBodyPadding{
                                        color: #000;
                                        background-color: white;
                                        position: absolute;
                    
                                        left: 0;
                                        top: 0;
                                        position: relative;
                                        display: block;
                                        width: 100%;
                                        height: 100%;
                                        contain: layout size style;
                                }
                                /* customrow */
                                .customRow[justify-content-center] {
                                    -webkit-box-pack: center;
                                    -webkit-justify-content: center;
                                    -ms-flex-pack: center;
                                    justify-content: center;
                                }
                                
                                .customRow[align-items-center] {
                                    -webkit-box-align: center;
                                    -webkit-align-items: center;
                                    -ms-flex-align: center;
                                    align-items: center;
                                }
                                .customRow {
                                    display: -webkit-box;
                                    display: -webkit-flex;
                                    display: -ms-flexbox;
                                    display: flex;
                                    -webkit-flex-wrap: wrap;
                                    -ms-flex-wrap: wrap;
                                    flex-wrap: wrap;
                                }
                                /* customcol */
                                .customCol{
                                    padding: 5px;
                                    position: relative;
                                    width: 100%;
                                    margin: 0;
                                    min-height: 1px;
                                    -webkit-flex-basis: 0;
                                    -ms-flex-preferred-size: 0;
                                    flex-basis: 0;
                                    -webkit-box-flex: 1;
                                    -webkit-flex-grow: 1;
                                    -ms-flex-positive: 1;
                                    flex-grow: 1;
                                    max-width: 100%;
                                }
                                .customCol-4{
                                    -webkit-box-flex: 0;
                                    -webkit-flex: 0 0 33.33333%;
                                    -ms-flex: 0 0 33.33333%;
                                    flex: 0 0 33.33333%;
                                    width: 32.33333%;
                                    max-width: 32.33333%;
                                    padding: 0px;
                                }
                                .customCol-8{
                                    -webkit-box-flex: 0;
                                    -webkit-flex: 0 0 66.66667%;
                                    -ms-flex: 0 0 66.66667%;
                                    flex: 0 0 66.66667%;
                                    width: 65.66667%;
                                    max-width: 65.66667%;
                                    padding: 0px;
                                }

                                .customSeperator{
                                    position: relative;
                                    margin-top: 0px;
                                    border: 1px solid #bdbdbd;
                                }
                    
                                P{
                                    float: left;
                                    font-size: 0.8em;
                                }
                                .starterBackground{
                                    background-color: #F6F6F6;
                                    max-width: 100%;
                                    padding-left: 10px;
                                    border: 1px solid #F6F6F6;
                                    margin-top: 40px;
                                }
                                 .signatureBackground{
                                    background-color: #FFFFFF;
                                    
                                    padding-left: 10px;
                                    border: 1px solid #FFFFFF;
                                    margin-top: 40px;
                                }
                                /* mainpageheader */
                                .mainPageHeader{
                                    left: 0;
                                    top: 0;
                                    position: absolute;
                                    z-index: 10;
                                    display: block;
                                    width: 100%;
                                    text-align: center
                                }

                                /* mainpageheadertitle */
                                .mainPageHeaderTitle{
                                    overflow: hidden;
                                    -webkit-box-orient: horizontal;
                                    -webkit-box-direction: normal;
                                    -webkit-flex-direction: row;
                                    -ms-flex-direction: row;
                                    flex-direction: row;
                                    -webkit-box-align: center;
                                    -webkit-align-items: center;
                                    -ms-flex-align: center;
                                    align-items: center;
                                    -ms-flex-pack: justify;
                                    font-size: 2em;
                                    font-weight: 700;
                                    max-width: 100%;
                                }
                                img{
                                    width: 45%;
                                    height: 45%;
                                }
                                .signature{
                                    width: 20%;
                                    height: 20%;
                                }
                    
                                .header .col {
                                    background-color:lightgrey;
                                    }
                    
                                    .col {
                                    border: solid 1px grey;
                                    border-bottom-style: none;
                                    border-right-style: none;
                                    }
                    
                                    .col:last-child {
                                    border-right: solid 1px grey;
                                    }
                    
                                    .row:last-child .col {
                                    border-bottom: solid 1px grey;
                                    }
                                    h5{
                                        font-size: 0.8em;
                                    }
                            </style>
                        </head>
                        <body class="customBodyPadding">
                            <div class="mainPageHeader">
                                <div class="mainPageHeaderTitle">
                                    Pre-Use Inspection Report
                                </div>
                            </div>
                            <div class="starterBackground">
                                <table style="width:100%;">
                                    <tr>
                                        <td style="text-align: left; font-weight: 600; width: 33%;">Inspection Description :</td>
                                        <td style="text-align: left; font-weight: 600;">'.$inspectionDescription.' </td> 
                                    </tr>
                                    <tr>
                                        <td style="text-align: left; font-weight: 600; width: 33%;">Equipment Category :</td>
                                        <td style="text-align: left; font-weight: 600;">'.$catname.'</td> 
                                    </tr>
                                    <tr>
                                        <td style="text-align: left; font-weight: 600; width: 33%;">Inspected by :</td>
                                        <td style="text-align: left; font-weight: 600;">'.$username.'</td> 
                                    </tr>
                                    <tr>
                                        <td style="text-align: left; font-weight: 600; width: 33%;">Company :</td>
                                        <td style="text-align: left; font-weight: 600;">'.$company.'</td> 
                                    </tr>
                                    <tr>
                                        <td style="text-align: left; font-weight: 600; width: 33%;">Department :</td>
                                        <td style="text-align: left; font-weight: 600;">'.$department.'</td> 
                                    </tr>
                                    <tr>
                                        <td style="text-align: left; font-weight: 600; width: 33%;">Inspection Date :</td>
                                        <td style="text-align: left; font-weight: 600;">'.$inspectiondate.'</td> 
                                    </tr>
                                    <tr>
                                        <td style="text-align: left; font-weight: 600; width: 33%;">Final Remark :</td>
                                        <td style="text-align: left; font-weight: 600;">'. $type.'</td> 
                                    </tr>
                                    <tr>
                                        <td style="text-align: left; font-weight: 600; width: 33%;">Fault Description : </td>
                                        <td style="text-align: left; font-weight: 600;">'. $oDescription.'</td> 
                                    </tr>
                                </table>
                                <div class="customSeperator"></div>
                                <table style="width:100%;">
                                    <tr>
                                        <td style="text-align: left; font-weight: 600; width: 33%;">Report Sent To :</td>
                                        <td style="text-align: left; font-weight: 600;">'.$toreport.'</td> 
                                    </tr>
                                </table>
                                </div>
                                <div class="starterBackground">
                                    <table style="width:100%;">
                                        <tr>
                                            <td style="text-align: left; font-weight: 600; width: 33%;"><h1>Equipment Images</h1></td>
                                        </tr>
                                        <tr>
                                            <td style="text-align: left; font-weight: 600; width: 33%; "><span style="margin: 0 0 10px 0; line-height: 1.2em;">Equipment Image</span><br><br><img height =100px width=100px src="'.$equipmentImage.'"></td>
                                            <td style="text-align: left; font-weight: 600; width: 33%; "><span style="margin: 0 0 10px 0; line-height: 1.2em;">Fault Image</span><br><br><img src="'.$mediaUrl.'"></td>
                                           
                                        </tr>
                                    </table>
                                </div>
                                <br/>';
        $mpdf->WriteHTML($html);            
        $rows = (new Query())
            ->select('*')
            ->from('userinspectionsubcategories')
            ->where(['inspectionId' => $id])
            ->all();
        $rowcount=count($rows);    
        foreach ($rows as $row)  
        {
            $subid=$row['subCategoryId'];
            $sub = (new Query())
                ->select('*')
                ->from('equipmentsubcategories')
                ->where(['equipmentSubCategoryId' => $subid])
                ->one();

            $table= 
            '<h4>'.$catname."/".$sub['equipmentSubCategoryName'].'</h4>
            <table style="width:100%; border: 1px solid; margin-top: 10px;">
                <tr style="text-align: center; background-color: grey; color: white;">
                    <th>SI. NO</th>
                    <th>Questions</th> 
                    <th>Remarks</th>
                </tr>';


            $ansname = array();
            $rowsques = (new Query())
                ->select('*')
                ->from('equipmentquestions')
                ->where(['equipmentSubCategoryId' => $row['subCategoryId']])
                ->all();
            $rowsans = (new Query())
                ->select('*')
                ->from('userinspectionanswers')
                ->where(['inspectionSubCategoryId' => $row['Id']])
                ->all();
            foreach ($rowsans as $row) {
                 
                array_push($ansname, $row['inspectionAnswer']);

            }
                  
            $count=1; 
            $qname="Chief Safety";
            $i=0;
            $color = 'color:#FE0000';
            $mpdf->WriteHTML($table);  
            foreach ($rowsques as $qrow ) {
                if($ansname[$i]=='N/A'){
                    $color = 'color:#A9A9A9';
                }
                if($ansname[$i]=='Pass'){
                    $color = 'color:#4CAF50';
                }
                if($ansname[$i]=='Fail'){
                    $color = 'color:#FE0000';
                }
                $tablerows='
                    <tr style="text-align: center; font-weight: 500;">
                        <td style="border: 1px solid;">'.$count++.'</td>
                        <td style="border: 1px solid;">'.$qrow['equipmentQuestionTitle'].'</td>
                        
                        <td class="answers" style="border: 1px solid;'.$color.';">'.$ansname[$i].'</td>
                    </tr>';
                $mpdf->WriteHTML($tablerows);
                $i++;
            }                          
            
            $tableend='   </table>';
            $mpdf->WriteHTML($tableend);   
        }
        $end = ' 
                    <br/><br/> 
                    <div class="signatureBackground">
                        <table style="width:100%;">
                        
                            <tr>
                                <td style="text-align: left; font-weight: 60; width: 33%;"><h4>Signature of Inspector</h4></td>
                            </tr>
                            <tr>
                                <td style="text-align: left; font-weight: 60;"><img class ="signature" height =100px width=100px src="'.$signature.'"></td>
                            </tr>
                        </table>
                    </div>
                </body>
            </html>';


        $mpdf->WriteHTML($end,2);
      
        $filename=time().$id;         
        $mpdf->Output('attachments/'.$filename.'.pdf','F');
        $model=Userinspectionreport::findOne([
            'inspectionReportId' => $rid,]);
        if ($model) {
            $model->reportUrl  = 	addslashes("http:\\").$_SERVER['HTTP_HOST']."/safetyapp/api/web/attachments/".$filename;
            $model->save();
        }   
        $value= Yii::$app->mailer->compose(['html'=>'inspectionReport'], ['model' => $rowuser])
            ->setTo($toemail)
            ->setFrom(['info@clients2.5stardesigners.net' =>'Support'])
            ->setSubject('Inspection Report For ' . \Yii::$app->name.' ('.$subject.')')
            ->attach('attachments/'.$filename.'.pdf')
            ->send();
        if ($value) {
            Yii::$app->response->statusCode=200;
            echo json_encode(array('status'=>200,'data'=>array('report'=>array_filter($model->attributes), 'message'=> "Mail sent.")),JSON_PRETTY_PRINT);
        } else {
            Yii::$app->response->statusCode=400;
            echo json_encode(array('status'=>400,'error'=>array('message'=>'Mail not sent due to weak network.Try Again')),JSON_PRETTY_PRINT);
        }
    }

 
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
         
        if ($model) { 
 
            $rows2= Userinspectionsubcategories::findAll([
                'inspectionId' => $id,]);  
            foreach ($rows2 as $row ) {
                $usubid = $row->Id;
                $rows3= Userinspectionanswers::findAll([
                    'inspectionSubCategoryId' => $usubid,]);  
    
                foreach ($rows3 as $srow ) {
                   
                     $srow->status='Deleted';
                     $srow->save();
                            
                 }  
                 $row->status='Deleted';
                 $row->save();
                
            }
            
            $rows4= Userinspectionreport::findOne([
                'inspectionId' => $id,]);  
    
            $rows4->status="Deleted";
            $filename = $rows4->reportUrl.".pdf";
            //unlink($filename);
            $rows4->save();
            $model->status='Deleted';
            if ($model->save()) {
                echo json_encode(array('status'=>1,'data'=>
                    array('status'=>1,'message'=>'Deleted')),JSON_PRETTY_PRINT);
            } else {
                echo json_encode(array('status'=>0,'error_code'=>400,'errors'=>$model->errors),JSON_PRETTY_PRINT);
            }
        }
    }

    protected function findModel($id)
    {
        if (($model = Userinspections::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
