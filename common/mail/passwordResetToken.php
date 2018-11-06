<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $user common\models\User */

//$resetLink = Yii::$app->urlManager->createAbsoluteUrl(['v1/users/app/reset-password', 'token' => $model->resetToken]);
?>
<div class="password-reset">
    <p>Hello <?= Html::encode($name) ?>,</p>

    <p>Below is your six digit code to reset your password:</p>

    <p><strong><?= Html::encode($token) ?></strong></p>
</div>
