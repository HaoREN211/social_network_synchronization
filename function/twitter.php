<?php

class Twitter{
    private $twitter_consumer_key = null;
    private $twitter_consumer_secret = null;
    private $base64_encoded_bearer_token_credentials = null;
    private $bearer_token = null;
    private $twitter_id = null;
    private $twitter_name = null;
    private $twitter_user_show = null;

    private $mysql_host_name = null;
    private $mysql_user_name = null;
    private $mysql_password = null;
    private $mysql_database = null;
    private $mysql_connection = null;

    const table_profile_location = 'hao_socialnetwork_twitter_profile_locations';
    const table_user = 'hao_socialnetwork_twitter_users';
    const table_user_statistic = 'hao_socialnetwork_twitter_user_statistics';

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

//         $this->bearer_token = $this->api_bearer_token($this->base64_encoded_bearer_token_credentials);
        $this->bearer_token = "AAAAAAAAAAAAAAAAAAAAAOPb1gAAAAAA%2FLsCIif43SrsX9JX3202ntbAei4%3DYiHTij0LjdjxHOurvkZ6xTIPFKEKb6BL4iimkecibLBLPlr3HW";
    }



    /**
     * Close the connection of MySQL
     */
    public function __destruct()
    {
        // TODO: Implement __destruct() method.
        if($this->mysql_host_name != null)
            mysqli_close($this->mysql_connection);
    }



    /**
     * Initial all of the parameters for the connection of server MySQL
     * @param $mysql_host_name
     * @param $mysql_user_name
     * @param $mysql_password
     * @param $mysql_database
     */
    public function set_mysql_bdd($mysql_host_name,
                                  $mysql_user_name,
                                  $mysql_password,
                                  $mysql_database){
        if($mysql_host_name === null)
            throw new InvalidArgumentException('Invalided MySQL host name');

        if($mysql_user_name === null)
            throw new InvalidArgumentException('Invalided MySQL user name');

        if($mysql_database === null)
            throw new InvalidArgumentException('Invalided MySQL database name');

        $this->mysql_database   = $mysql_database;
        $this->mysql_host_name  = $mysql_host_name;
        $this->mysql_password   = $mysql_password;
        $this->mysql_user_name  = $mysql_user_name;
        $this->mysql_connection = mysqli_connect($mysql_host_name,
            $mysql_user_name,
            $mysql_password,
            $mysql_database);

        if($this->mysql_connection){
            print "connection with MySQL server has succeed";
        }

        mysqli_set_charset($this->mysql_connection, 'utf8');
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
    public function save_user_shows(){
        if($this->twitter_user_show===null)
            throw new \LogicException('You must call api_users_show method to get the data of the current account');

        if(!$this->mysql_connection)
            throw new \LogicException('You must call set_mysql_bdd method to set the connection of the MySQL server');

        $this->save_profile_location();

        $id             =$this->retrieve_object_property($this->twitter_user_show, 'id_str');
        $inexist = $this->verify_inexist_bdd_data(self::table_user, 'id', $id);

        if($inexist){
            $name               =$this->retrieve_object_property($this->twitter_user_show, 'name', true, false);
            $screen_name        =$this->retrieve_object_property($this->twitter_user_show, 'screen_name', true, false);
            $location        =$this->retrieve_object_property($this->twitter_user_show, 'location', true, false);
            $profile_location  = $this->twitter_user_show->profile_location;
            if(is_object($profile_location) && property_exists($profile_location, 'id') && $profile_location->id != null)
            {
                $profile_location_id = '\''.$profile_location->id.'\'';
            }
            else
            {
                $profile_location_id = null;
            }

            $description        =$this->retrieve_object_property($this->twitter_user_show, 'description', true, false);
            $url                =$this->retrieve_object_property($this->twitter_user_show, 'url', true, false);
            $protected          =$this->retrieve_object_property($this->twitter_user_show, 'protected', true, true);
            $followers_count    =$this->retrieve_object_property($this->twitter_user_show, 'followers_count', true, true);
            $friends_count      =$this->retrieve_object_property($this->twitter_user_show, 'friends_count', true, true);
            $listed_count       =$this->retrieve_object_property($this->twitter_user_show, 'listed_count', true, true);
            $created_at         =$this->retrieve_object_property($this->twitter_user_show, 'created_at', true, true);
            $favourites_count   =$this->retrieve_object_property($this->twitter_user_show, 'favourites_count', true, true);
            $utc_offset         =$this->retrieve_object_property($this->twitter_user_show, 'utc_offset', true, true);
            $time_zone          =$this->retrieve_object_property($this->twitter_user_show, 'time_zone', true, false);
            $geo_enabled        =$this->retrieve_object_property($this->twitter_user_show, 'geo_enabled', true, true);
            $verified           =$this->retrieve_object_property($this->twitter_user_show, 'verified', true, true);
            $statuses_count     =$this->retrieve_object_property($this->twitter_user_show, 'statuses_count', true, true);
            $lang               =$this->retrieve_object_property($this->twitter_user_show, 'lang', true, false);

            $contributors_enabled  =$this->retrieve_object_property($this->twitter_user_show, 'contributors_enabled', true, true);
            $is_translator         =$this->retrieve_object_property($this->twitter_user_show, 'is_translator', true, true);
            $is_translation_enabled=$this->retrieve_object_property($this->twitter_user_show, 'is_translation_enabled', true, true);
            $profile_background_color           =$this->retrieve_object_property($this->twitter_user_show, 'profile_background_color', true, false);
            $profile_background_image_url       =$this->retrieve_object_property($this->twitter_user_show, 'profile_background_image_url', true, false);
            $profile_background_image_url_https =$this->retrieve_object_property($this->twitter_user_show, 'profile_background_image_url_https', true, false);
            $profile_background_tile            =$this->retrieve_object_property($this->twitter_user_show, 'profile_background_image_url_https', true, true);
            $profile_image_url                  =$this->retrieve_object_property($this->twitter_user_show, 'profile_image_url', true, false);
            $profile_image_url_https            =$this->retrieve_object_property($this->twitter_user_show, 'profile_image_url_https', true, false);
            $profile_link_color                 =$this->retrieve_object_property($this->twitter_user_show, 'profile_link_color', true, false);
            $profile_sidebar_border_color       =$this->retrieve_object_property($this->twitter_user_show, 'profile_sidebar_border_color', true, false);
            $profile_sidebar_fill_color         =$this->retrieve_object_property($this->twitter_user_show, 'profile_sidebar_fill_color', true, false);
            $profile_text_color                 =$this->retrieve_object_property($this->twitter_user_show, 'profile_text_color', true, false);
            $profile_use_background_image       =$this->retrieve_object_property($this->twitter_user_show, 'profile_use_background_image', true, true);
            $has_extended_profile               =$this->retrieve_object_property($this->twitter_user_show, 'has_extended_profile', true, true);
            $default_profile                    =$this->retrieve_object_property($this->twitter_user_show, 'default_profile', true, true);
            $default_profile_image              =$this->retrieve_object_property($this->twitter_user_show, 'default_profile_image', true, true);
            $following                          =$this->retrieve_object_property($this->twitter_user_show, 'following', true, false);
            $follow_request_sent                =$this->retrieve_object_property($this->twitter_user_show, 'follow_request_sent', true, false);
            $notifications                      =$this->retrieve_object_property($this->twitter_user_show, 'notifications', true, false);
            $translator_type                    =$this->retrieve_object_property($this->twitter_user_show, 'translator_type', true, false);

            // http://php.net/manual/fr/datetime.formats.date.php
            $time = DateTime::createFromFormat("D M d H:i:s O Y", $created_at);
            $created_at = '\''.$time->format("Y-m-d H:i:s").'\'';


            $script = 'INSERT INTO `'.self::table_user.'`(`id`, `name`, `screen_name`, `location`, `profile_location_id`, `description`, `url`, `protected`, `followers_count`, `friends_count`, `listed_count`, `created_at`, `favourites_count`, `utc_offset`, `time_zone`, `geo_enabled`, `verified`, `statuses_count`, `lang`, `contributors_enabled`, `is_translator`, `is_translation_enabled`, `profile_background_color`, `profile_background_image_url`, `profile_background_image_url_https`, `profile_background_tile`, `profile_image_url`, `profile_image_url_https`, `profile_link_color`, `profile_sidebar_border_color`, `profile_sidebar_fill_color`, `profile_text_color`, `profile_use_background_image`, `has_extended_profile`, `default_profile`, `default_profile_image`, `following`, `follow_request_sent`, `notifications`, `translator_type`, `API`) VALUES (
                        '.$id.','.$name.','.$screen_name.','.$location.','.$profile_location_id.','.$description.','.$url.','.$protected.',
                        '.$followers_count.','.$friends_count.','.$listed_count.','.$created_at.','.$favourites_count.','.$utc_offset.',
                        '.$time_zone.','.$geo_enabled.','.$verified.','.$statuses_count.','.$lang.','.$contributors_enabled.','.$is_translator.',
                        '.$is_translation_enabled.','.$profile_background_color.','.$profile_background_image_url.','.$profile_background_image_url_https.',
                        '.$profile_background_tile.','.$profile_image_url.','.$profile_image_url_https.','.$profile_link_color.','.$profile_sidebar_border_color.',
                        '.$profile_sidebar_fill_color.','.$profile_text_color.','.$profile_use_background_image.','.$has_extended_profile.','.$default_profile.',
                        '.$default_profile_image.','.$following.','.$follow_request_sent.','.$notifications.','.$translator_type.',\'user_show\');';

            $result = mysqli_query($this->mysql_connection, $script);
            if(!$result)
                throw new RuntimeException("Can not insert new user");
        }
    }


    /**
     *
     */
    public function save_user_statistic(){
        if($this->twitter_user_show===null)
            throw new \LogicException('You must call api_users_show method to get the data of the current account');

        if(!$this->mysql_connection)
            throw new \LogicException('You must call set_mysql_bdd method to set the connection of the MySQL server');

        $id =$this->retrieve_object_property($this->twitter_user_show, 'id_str');

        $inexist  = $this->verify_inexist_bdd_data(self::table_user, 'id', $id);
        if($inexist)
            throw new \LogicException('You must call save_user method to set the the user');
        else
        {
            $followers_count    =$this->retrieve_object_property($this->twitter_user_show, 'followers_count', true, true);
            $friends_count      =$this->retrieve_object_property($this->twitter_user_show, 'friends_count', true, true);
            $listed_count       =$this->retrieve_object_property($this->twitter_user_show, 'listed_count', true, true);
            $favourites_count   =$this->retrieve_object_property($this->twitter_user_show, 'favourites_count', true, true);
            $statuses_count     =$this->retrieve_object_property($this->twitter_user_show, 'statuses_count', true, true);
            $now = new DateTime();
            $observation_date = '\''.$now->format("Y-m-d H:i:s").'\'';

            $script = 'INSERT INTO `hao_socialnetwork_twitter_user_statistics`(`id`, `followers_count`, `friends_count`, `listed_count`, `favourites_count`, `statuses_count`, `observation_date`) VALUES (
                        '.$id.','.$followers_count.','.$friends_count.','.$listed_count.',
                        '.$favourites_count.','.$statuses_count.','.$observation_date.');';

            $result = mysqli_query($this->mysql_connection, $script);
            if(!$result)
                throw new RuntimeException("Can not insert new user statistic");
        }

    }


    /**
     *
     */
    public function save_profile_location(){
        if($this->twitter_user_show===null)
            throw new \LogicException('You must call api_users_show method to get the data of the current account');

        if(!$this->mysql_connection)
            throw new \LogicException('You must call set_mysql_bdd method to set the connection of the MySQL server');

        $profile_location = $this->twitter_user_show->profile_location;

        if(is_object($profile_location)
            && $profile_location!= null
            && property_exists($profile_location, 'id')){
                $id = $this->retrieve_object_property($profile_location, 'id', true, false);
                $inexist = $this->verify_inexist_bdd_data(self::table_profile_location,
                    'id', $id);
                if($inexist)
                {
                    $url = $this->retrieve_object_property($profile_location, 'url', true, false);
                    $place_type = $this->retrieve_object_property($profile_location, 'place_type', true, false);
                    $name = $this->retrieve_object_property($profile_location, 'name', true, false);
                    $full_name = $this->retrieve_object_property($profile_location, 'full_name', true, false);
                    $country_code = $this->retrieve_object_property($profile_location, 'country_code', true, false);
                    $country = $this->retrieve_object_property($profile_location, 'country', true, false);
                    $contained_within = $this->retrieve_object_property($profile_location, 'contained_within', true, false);
                    $bounding_box = $this->retrieve_object_property($profile_location, 'bounding_box', true, false);
                    $attributes = $this->retrieve_object_property($profile_location, 'attributes', true, false);

                    $script = 'INSERT INTO `hao_socialnetwork_twitter_profile_locations`(`id`, `url`, `place_type`, `name`, `full_name`, `country_code`, `country`, `contained_within`, `bounding_box`, `attributes`, `API`) 
                        VALUES ('.$id.',
                          '.$url.',
                          '.$place_type.',
                          '.$name.',
                          '.$full_name.',
                          '.$country_code.',
                          '.$country.',
                          '.$contained_within.',
                          '.$bounding_box.',
                          '.$attributes.',
                          \'user_show\');
                    ';

                    $result = mysqli_query($this->mysql_connection, $script);
                    if(!$result)
                        throw new RuntimeException("Can not insert new profile location");


                }
        }
    }


    /**
     * Check if am object has a property
     * @param $object
     * @param $property
     * @return mixed
     */
    private function retrieve_object_property($object, $property, $sql=false, $is_int=true){
        if(is_object($object)){
            if(property_exists($object, $property)){
                $resultat = $object->$property;
                if($resultat == null || $resultat=="")
                {
                    if($sql)
                        return 'null';
                    else
                        return null;
                }
                else{
                    if(is_array($resultat))
                        $resultat = implode(', ', $resultat);

                    if(is_object($resultat))
                        $resultat = print_r($resultat, true);

                    if($sql && !$is_int)
                    {
                        return '\''.$resultat.'\'';
                    }
                    else
                        return $resultat;
                }
            }

            else
                throw new \LogicException('The object has no property named '.$property);
        }
        else
        {
            throw new \LogicException('The parameter \'object\' is not a object');
        }
    }


    /**
     * Verify if a data exist in the table of the BDD
     * @param $table
     * @param $key
     * @param $value
     * @return bool
     */
    private function verify_inexist_bdd_data($table, $key, $value){
        if(!$this->mysql_connection)
            throw new \LogicException('You must call set_mysql_bdd method to set the connection of the MySQL server');

        if($table === null || $this==='')
            throw new InvalidArgumentException('Invalided table name');

        if($key === null || $key==='')
            throw new InvalidArgumentException('Invalided column name');

        if($value===null || $value==='')
            throw new InvalidArgumentException('Invalided column value');

        $script = "select `".$key."` from `".$table."` where `".$table."`.`".$key."`=".$value.";";

        $results_serveur = mysqli_query($this->mysql_connection, $script);

        $nb_resultat = (int)mysqli_num_rows($results_serveur);

        mysqli_free_result($results_serveur);

        if($nb_resultat ===(int)0)
            return true;
        else
            return false;
    }
}
?>