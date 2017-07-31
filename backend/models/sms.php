<?php

// Api для отправки смс

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