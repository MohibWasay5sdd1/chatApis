<?php

namespace api\modules\v1\controllers;

use Yii;
use yii\filters\auth\HttpBasicAuth;
use api\modules\v1\models\Equipmentquestions;
use api\modules\v1\models\EquipmentquestionsSearch;
use api\modules\v1\models\Inspectionremarks;
use api\modules\v1\models\InspectionremarksSearch;
use api\modules\v1\models\Defaultequipmentsubcategories;
use api\modules\v1\models\DefaultequipmentsubcategoriesSearch;
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

class DefaultEquipmentSubCategoriesController extends ActiveController
{
    public function behaviors()
    {
        return [
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
            $actions['delete']
        );
        return $actions;
    }
    
    public function actionCreate($id)
    {
        $request = Yii::$app->request;
        $model = new Defaultequipmentsubcategories();
        if ($model->load(Yii::$app->request->post(),'') ) {

            $eqid = $request->get('id');
            $date=$model->createdOn=date('Y-m-d H:i:s');
            $model->modifiedOn=date('Y-m-d H:i:s');
            $model->defaultCategoryId =$eqid;
            $model->defaultSubCategoryStatus= 'Active';
            $model->save();
            if ($model->save()) {

                echo json_encode(array('status'=>1,'data'=>array_filter($model->attributes)),JSON_PRETTY_PRINT);
            } else {
                echo json_encode(array('status'=>0,'error_code'=>400,'errors'=>$model->errors),JSON_PRETTY_PRINT);
            }
        } else
            echo json_encode($model->getErrors());
       
        }

    protected function findModel($id)
    {
        if (($model = Equipmentsubcategories::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
