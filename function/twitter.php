<?php

class Twitter{
    private $twitter_consumer_key = null;
    private $twitter_consumer_secret = null;
    private $base64_encoded_bearer_token_credentials = null;
    private $bearer_token = null;
    private $twitter_id = null;
    private $twitter_name = null;
    private $twitter_user_show = null;

    /**
     * construct the object with consumer key and consumer key secret
     * Twitter constructor.
     * @param $twitter_consumer_key
     * @param $twitter_consumer_secret
     */
    public function __construct($twitter_consumer_key, $twitter_consumer_secret)
    {
        if($twitter_consumer_key === null)
            throw new InvalidArgumentException('Invalided Twitter consumer key');
        if($twitter_consumer_secret === null)
            throw new InvalidArgumentException('Invalided Twitter consumer key secret');

        $this ->twitter_consumer_key = $twitter_consumer_key;
        $this->twitter_consumer_secret = $twitter_consumer_secret;

        $this->base64_encoded_bearer_token_credentials = $this->get_base64_encoded_bearer_token_credentials($twitter_consumer_key,
            $twitter_consumer_secret);

         $this->bearer_token = $this->api_bearer_token($this->base64_encoded_bearer_token_credentials);
    }


    /**
     * return the bearer token
     * @return mixed|null
     */
    public function get_token(){
        return $this->bearer_token;
    }


    /**
     * Set twitter id of witch we are going to retrieve the information
     * @param $twitter_id
     */
    public function set_twitter_id($twitter_id){
        $this->twitter_id = $twitter_id;
    }


    /**
     * Get twitter id
     * @return null
     */
    public function get_twitter_id(){
        return $this ->twitter_id;
    }


    /**
     * Set twitter name
     * @param $twitter_name
     */
    public function set_twitter_name($twitter_name){
        $this->twitter_name = $twitter_name;
    }

    /**
     * Get twitter name
     * @return null
     */
    public function get_twitter_name(){
        return $this->twitter_name;
    }

        /**
     * encode consumer key and consumer key secret with base 64 encoded
     * @param $key
     * @param $secret
     * @return string
     */
    private function get_base64_encoded_bearer_token_credentials($key, $secret)
    {
        $bearer_token_credentials = $key . ":" . $secret;
        $base64_encoded_bearer_token_credentials = base64_encode($bearer_token_credentials);
        return $base64_encoded_bearer_token_credentials;
    }


    /**
     * send the post request with request header and body
     * @param $url
     * @param $fields
     * @param $headers
     * @return mixed
     */
    private function http_post_resultat($url, $fields, $headers){
        // http://php.net/manual/fr/function.curl-setopt.php
        //open connection

        if($url === null)
            throw new InvalidArgumentException('Invalided URL');

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

    /**
     * @param $url
     * @param $headers
     * @return mixed
     */
    private function http_get_resultat($url, $headers){
        if($url === null)
            throw new InvalidArgumentException('Invalided URL');
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);
        return $result;
    }



    /**
     * Call API for getting the bearer token from the basic token
     * @param $basic_token
     * @return mixed
     */
    private function api_bearer_token($basic_token){
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

        $result = $this->http_post_resultat($url, $fields, $headers);
        $resultat_decompress = gzdecode ($result);
        $bearer_token_array = (json_decode($resultat_decompress));
        $bearer_token = $bearer_token_array->access_token;
        return $bearer_token;
    }


    /**
     * Get basic information of a Twitter account
     * https://developer.twitter.com/en/docs/accounts-and-users/follow-search-get-users/api-reference/get-users-show
     */
    public function api_users_show(){
        $url = "https://api.twitter.com/1.1/users/show.json";

        if($this->twitter_id != null){
            $requet = "user_id=".$this->twitter_id;
        }
        elseif ($this->twitter_name!= null){
            $requet = "screen_name=".$this->twitter_name;
        }
        else
            throw new LogicException('You must initial twitter id or twitter screen name before calling API');

        $requet_url = $url."?".$requet;
        $requet_header = array(
            'User-Agent: My Twitter App v1.0.23',
            'Authorization: Bearer '.$this->bearer_token,
            'Accept-Encoding: gzip'
        );
        $resultat = $this->http_get_resultat($requet_url, $requet_header);
        if($resultat === null || $resultat ==="")
            throw new RuntimeException("No return date");
        $resultat_unzip = gzdecode($resultat);
        $resultat_unzip_jsoncode = json_decode($resultat_unzip);
        $this->twitter_user_show = $resultat_unzip_jsoncode;
    }


    /**
     * Save the user_show datas in the database
     */
    public function save_user_shows($mysql_host_name,
                                    $mysql_user_name,
                                    $mysql_password,
                                    $mysql_database){
        if($this->twitter_user_show===null)
            throw new \LogicException('You must call api_users_show method to get the data of the current account');

        $connect_1and1 = mysqli_connect($mysql_host_name,
            $mysql_user_name,
            $mysql_password,
            $mysql_database);

        $profile_location = $this->twitter_user_show->profile_location;


        $id             = $this->twitter_user_show->id;
        $id_str         = $this->twitter_user_show->id_str;
        $name           = $this->twitter_user_show->name;
        $screen_name    = $this->twitter_user_show->screen_name;
        $description    = $this->twitter_user_show->description;
        $url            = $this->twitter_user_show->url;
        $protected      = $this->twitter_user_show->protected;
        $followers_count = $this->twitter_user_show->followers_count;
        $friends_count  = $this->twitter_user_show->friends_count;
        $listed_count   = $this->twitter_user_show->listed_count;

        mysqli_close($connect_1and1);
    }
}
?>