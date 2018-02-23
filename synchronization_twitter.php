<?php
/**
 * Created by PhpStorm.
 * User: Hao
 * Date: 2018/2/22
 * Time: 21:51
 */

include ("./connection/mysql.php");
include ("./connection/twitter.php");
include("./function/twitter.php");

// Get base64 encoded bearer token from the consumer key and consumer key secret
$bearer_token_credentials = $twitter_consumer_key . ":" . $twitter_consumer_secret;
$base64_encoded_bearer_token_credentials = get_base64_encoded_bearer_token_credentials($twitter_consumer_key,
											$twitter_consumer_secret);
											
// Get bear_token from the base64 encoded token
// $bear_token = get_bearer_token($base64_encoded_bearer_token_credentials);
$bear_token = "AAAAAAAAAAAAAAAAAAAAAOPb1gAAAAAA%2FLsCIif43SrsX9JX3202ntbAei4%3DYiHTij0LjdjxHOurvkZ6xTIPFKEKb6BL4iimkecibLBLPlr3HW";
print $bear_token;





		
//url-ify the data for the POST
// foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
// $fields_string = rtrim($fields_string,'&');

//








$connect_1and1 = mysqli_connect($mysql_host_name,
    $mysql_user_name,
    $mysql_password,
    $mysql_database);
/*
$script = "select id from hao_socialnetwork_meetics limit 10;";
$results_serveur = mysqli_query($connect_1and1, $script) or die (mysqli_error($connect_1and1));
foreach($results_serveur as $result_serveur)
{
    print $result_serveur['id']." teste \n";
}
*/

?>