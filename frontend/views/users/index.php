<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel frontend\models\usersSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Users';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="users-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Users', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'user_name',
            'first_name',
            'last_name',
            'full_name',
            //'email:email',
            //'password',
            //'phone_number:ntext',
            //'dob',
            //'status',
            //'reset_token',
            //'reset_expiry',
            //'profile_pic_url:url',
            //'last_login',
            //'role_id',
            //'created_on',
            //'modified_on',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
