<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model frontend\models\usersSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="users-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'user_name') ?>

    <?= $form->field($model, 'first_name') ?>

    <?= $form->field($model, 'last_name') ?>

    <?= $form->field($model, 'full_name') ?>

    <?php // echo $form->field($model, 'email') ?>

    <?php // echo $form->field($model, 'password') ?>

    <?php // echo $form->field($model, 'phone_number') ?>

    <?php // echo $form->field($model, 'dob') ?>

    <?php // echo $form->field($model, 'status') ?>

    <?php // echo $form->field($model, 'reset_token') ?>

    <?php // echo $form->field($model, 'reset_expiry') ?>

    <?php // echo $form->field($model, 'profile_pic_url') ?>

    <?php // echo $form->field($model, 'last_login') ?>

    <?php // echo $form->field($model, 'role_id') ?>

    <?php // echo $form->field($model, 'created_on') ?>

    <?php // echo $form->field($model, 'modified_on') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
