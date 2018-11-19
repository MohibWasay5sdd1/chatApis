<?php



namespace backend\modules\v1\components;



use Yii;

use yii\base\Component;

class MyComponent extends Component{

	public function send_notification($registration_id, $message){
		//IN FUNCTION PARAMETER
		//$registration_ID = $registration_id;
		//$messages = $_POST['messages'];

// 'vibrate' available in GCM, but not in FCM
		$fcmMsg = array(
			'body' => $message,
			'title' => 'Match Notification',
			'sound' => "boxing.wav"
		);

		$fcmFields = array(
			'registration_ids' => $registration_id,
		        'priority' => 'high',
			'notification' => $fcmMsg
		);

		$headers = array(
			'Authorization: key=' . \Yii::$app->params['server_key'],
			'Content-Type: application/json'
		);
		 
		$ch = curl_init();
		curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
		curl_setopt( $ch,CURLOPT_POST, true );
		curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
		curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fcmFields ) );
		$result = curl_exec($ch );
		curl_close( $ch );
		//echo $result . "\n\n";
		return true;

	}

}