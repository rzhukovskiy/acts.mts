<?php

// Api для отправки смс

/**
 * Sends request to API
 * @param $request - associative array to pass to API, "format" 
 * key will be overridden
 * @param $cookie - cookies string to be passed
 * @return
 * * NULL - communication to API failed
 * * ($err_code, $data) if response was OK, $data is an associative
 * array, $err_code is an error numeric code 
 */

function _smsapi_communicate($request, $cookie=NULL) {
	$request['format'] = "json";
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, "http://api2.ssms.su/");
//	curl_setopt($curl, CURLOPT_URL, "https://ssl.bs00.ru/"); // раскомментируйте, если хотите отправлять по HTTPS
	curl_setopt($curl, CURLOPT_POST, True);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, True);
	curl_setopt($curl, CURLOPT_TIMEOUT, 40);
	if(!is_null($cookie)){
		curl_setopt($curl, CURLOPT_COOKIE, $cookie);
	}
	$data = curl_exec($curl);	
	if($data === False){
		$ERROR = curl_error($curl); //код ошибки запроса
		curl_close($curl);
		return array("err_code" => 1, "err_message" => $ERROR);
	}
	curl_close($curl);
	$js = json_decode($data, $assoc=True);
	return array("err_code" => 0, "data" => $js);
}


/**
 * Sends a message via ssms_su api, combining authenticating and sending
 * message in one request.
 * @param $email, $passwrod - login info
 * @param $phone - recipient phone number in international format (like 7xxxyyyzzzz)
 * @param $text - message text, ASCII or UTF-8.
 * @param $params - additional parameters as key => value array, see API doc.
 * @return 
 * * NULL if API communication went a wrong way
 * * array(>0) - if an error has occurred (see API error codes)
 * * array(0, n_raw_sms, credits) - number of SMS parts in message and 
 * price for a single part
 */
function smsapi_push_msg_nologin($email, $password, $phone, $text, $params = NULL){
	$req = array(
		"method" => "push_msg",
		"api_v"=>"2.0",
		"email"=>$email,
		"password"=>$password,
		"phone"=>$phone,
		"text"=>$text);
	if(!is_null($params)){
		$req = array_merge($req, $params);
	}
	$resp = _smsapi_communicate($req);
	if($resp['err_code'] > 0) {
	return array ( "response" => array ( "msg" => array ( "err_code" => $resp["err_code"], "text" => $resp["err_message"], "type" => "error" ), "data" => null ) )	;
	} else return $resp['data'];
}

function smsapi_push_msg_nologin_key($key, $phone, $text, $params = NULL){
	$req = array(
		"method" => "push_msg",
		"api_v"=>"2.0",
		"key"=>@$key,
		"phone"=>@$phone,
		"text"=>@$text);
	if(!is_null($params)){
		$req = array_merge($req, $params);
	}
	$resp = _smsapi_communicate($req);
	if($resp['err_code'] > 0) {
	return array ( "response" => array ( "msg" => array ( "err_code" => $resp["err_code"], "text" => $resp["err_message"], "type" => "error" ), "data" => null ) )	;
	} else return $resp['data'];
}

function smsapi_add_number_to_base_nologin_key($key, $phone, $id_base, $params = NULL) {
	$req = array(
		"method" => "add_number_to_base",
		"api_v"=>"2.0",
		"key"=>@$key,
		"phone"=>@$phone,
		"id_base"=>@$id_base);
	if(!is_null($params)){
		$req = array_merge($req, $params);
	}
	$resp = _smsapi_communicate($req);
	if($resp['err_code'] > 0) {
	return array ( "response" => array ( "msg" => array ( "err_code" => $resp["err_code"], "text" => $resp["err_message"], "type" => "error" ), "data" => null ) )	;
	} else return $resp['data'];
}

function smsapi_get_list_base_nologin_key($key, $params = NULL) {
	$req = array(
		"method" => "get_list_base",
		"api_v"=>"2.0",
		"key"=>@$key);
	if(!is_null($params)){
		$req = array_merge($req, $params);
	}
	$resp = _smsapi_communicate($req);
	if($resp['err_code'] > 0) {
	return array ( "response" => array ( "msg" => array ( "err_code" => $resp["err_code"], "text" => $resp["err_message"], "type" => "error" ), "data" => null ) )	;
	} else return $resp['data'];	
}
?>