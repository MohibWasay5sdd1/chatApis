<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $user common\models\User */

$reset_link = Yii::$app->urlManager->createAbsoluteUrl(['v1/users/verify-registration', 'token' => $token, 'email' =>$email]);
?>
<div class="password-reset">
    <p>Hello <?= Html::encode($name) ?>,</p>

    <p>Please click on the button below to verify your account:</p>

    <button><a href = '<?= Html::encode($reset_link) ?>'>Verify</a></button>
</div>
