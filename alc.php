<?php
    $origin = $_SERVER['HTTP_ORIGIN'];
    if ( strrpos($http_origin, "localhost") ){  
        header("Access-Control-Allow-Origin: $http_origin");
    }

    header('Content-Type: application/json');

    ini_set('display_errors', 1);
    require_once('TwitterAPIExchange.php');
 
    /** Set access tokens here - see: https://dev.twitter.com/apps/ **/
    $settings = array(
        'oauth_access_token' => "xxxxxxxxxxxxxxxxxxx",
        'oauth_access_token_secret' => "xxxxxxxxxxxxxxxxxxx",
        'consumer_key' => "xxxxxxxxxxxxxxxxxxx",
        'consumer_secret' => "xxxxxxxxxxxxxxxxxxx"
    );
 
 
    /** Perform a GET request and echo the response **/
    /** Note: Set the GET field BEFORE calling buildOauth(); **/
 
    $url = 'https://api.twitter.com/1.1/search/tweets.json';
    $getfield = '?'.$_SERVER['QUERY_STRING'];
    $requestMethod = 'GET';
    $twitter = new TwitterAPIExchange($settings);
 
    $api_response = $twitter ->setGetfield($getfield)
                     ->buildOauth($url, $requestMethod)
                     ->performRequest();
 
    echo $api_response;
?>