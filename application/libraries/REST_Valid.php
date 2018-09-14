<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class REST_Valid {

	public function post_validation($param_object, $param_received)
	{
		$required_param = $this->required_param($param_object) ;
		$received_param = $this->received_param($param_received) ;

		$result = true ;
		$message_error = array() ;

		/*Param Required*/
		foreach ($required_param as $key => $value) {
			if (!in_array($value, $received_param)) {
				array_push($message_error, "Parameter '$value' dibutuhkan ! ") ;
			}
		}

		if (count($message_error) > 0) {
			$result = false ;
		}

		return array(
			"status" => ($result == true) ? "OK" : "ERROR",
			"message" => $message_error
		) ;
	}


	private function required_param($param_object)
	{	
		$required_param = array() ;
		foreach ($param_object as $key => $value) {
			if (isset($value['required'])) {
				if ($value['required'] == true) {
					array_push($required_param, $value['name']) ;
				}
			}
		}
		return $required_param;
	}

	private function received_param($param_object)
	{
		$received_param = array() ;
		foreach ($param_object as $key => $value) {
			array_push($received_param, $key) ;
		}
		return $received_param;
	}

}

/* End of file REST_Valid.php */
/* Location: ./application/libraries/REST_Valid.php */