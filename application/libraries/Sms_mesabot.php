<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

#PEMANGGILAN
// $this->load->library("sms_mesabot");
// $phoneNumber = '081222810904';
// $text 		 = 'Test Amren';
// $send 		 = $this->sms_mesabot->sendSms($phoneNumber,$text);


class Sms_mesabot{
	private static $clientId, $clientSecret;
	public function __construct(){
		// $CI = & get_instance();
	 	// $CI->load->model('app_mdl');
	 	// $this->ci = $CI;

		self::$clientId 	= 'Ucim6LwY';
		self::$clientSecret = 'zbVhKvvi';

	}

	public function sendSms($phoneNumber,$text){

		$clientId 		= self::$clientId;
		$clientSecret 	= self::$clientSecret;

		$data = [
		  "destination" => [$phoneNumber],
		  "text" => $text,
		];

		$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => "https://mesabot.com/api/v2/send",
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_POSTFIELDS => json_encode($data),
		  CURLOPT_HTTPHEADER => array(
		    "client-id: $clientId",
		    "client-secret: $clientSecret",
		    "content-type: application/json",
		  ),
		));

		$response = curl_exec($curl);
		curl_close ($curl);
		return($response);
	}

}