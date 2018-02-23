<?php

function get_base64_encoded_bearer_token_credentials($key, $secret)
{
	$bearer_token_credentials = $key . ":" . $secret;
	$base64_encoded_bearer_token_credentials = base64_encode($bearer_token_credentials);
	return $base64_encoded_bearer_token_credentials;
}

function get_post_resultat($url, $fields, $headers){
	// http://php.net/manual/fr/function.curl-setopt.php
	//open connection
	$ch = curl_init();
	
	$fields_string = (is_array($fields)) ? http_build_query($fields) : $fields; 

	//set the url, number of POST vars, POST data
	curl_setopt($ch,CURLOPT_URL,$url);
	curl_setopt($ch,CURLOPT_POST,count($fields));
	curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);
	curl_setopt($ch,CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER, true); 

	//execute post
	$result = curl_exec($ch);
	return $result;
}



function get_bearer_token($basic_token){
	// url https://developer.twitter.com/en/docs/basics/authentication/overview/application-only
	$url = "https://api.twitter.com/oauth2/token";
	
	// requeste body
	$fields = array(
            'grant_type'=>urlencode("client_credentials")
        );
		
	// requeste header
	$headers = array(
    'User-Agent: My Twitter App v1.0.23',
    'Authorization: Basic '.$basic_token,
	'Content-Type: application/x-www-form-urlencoded;charset=UTF-8',
	'Content-Length: 29',
	'Accept-Encoding: gzip');
	
	$result = get_post_resultat($url, $fields, $headers);
	$resultat_decompress = gzdecode ($result);
	$bearer_token_array = (json_decode($resultat_decompress));
	$bearer_token = $bearer_token_array->access_token;
	return $bearer_token;
}

?>