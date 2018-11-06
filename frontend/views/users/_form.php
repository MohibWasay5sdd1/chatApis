<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model frontend\models\users */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="users-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'user_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'first_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'last_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'full_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'password')->passwordInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'phone_number')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'dob')->textInput() ?>

    <?= $form->field($model, 'status')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'reset_token')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'reset_expiry')->textInput() ?>

    <?= $form->field($model, 'profile_pic_url')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'last_login')->textInput() ?>

    <?= $form->field($model, 'role_id')->textInput() ?>

    <?= $form->field($model, 'created_on')->textInput() ?>

    <?= $form->field($model, 'modified_on')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
