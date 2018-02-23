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


$connect_1and1 = mysqli_connect($mysql_host_name,
    $mysql_user_name,
    $mysql_password,
    $mysql_database);

$twitter = new Twitter($twitter_consumer_key, $twitter_consumer_secret);
$twitter->set_twitter_name($twitter_sreen_name);
$twitter->api_users_show();
$twitter->save_user_shows($mysql_host_name,
    $mysql_user_name,
    $mysql_password,
    $mysql_database);



		
//url-ify the data for the POST
// foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
// $fields_string = rtrim($fields_string,'&');

//









/*
$script = "select id from hao_socialnetwork_meetics limit 10;";
$results_serveur = mysqli_query($connect_1and1, $script) or die (mysqli_error($connect_1and1));
foreach($results_serveur as $result_serveur)
{
    print $result_serveur['id']." teste \n";
}
*/

mysqli_close($connect_1and1);

?>