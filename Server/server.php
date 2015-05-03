<?php
    include_once "Common.php";
    include_once "../Services/AWSSDK/autoload.php";
    include_once "../Services/SQSClient.php";
    include_once "../Services/Twitter/Keys.php";
    include_once "../Services/Twitter/OauthPhirehose.php";
    include_once "../Services/Consumer.php";
    include_once "../Services/Server.php";
    include_once "../Services/Socket.php";
    include_once "../Services/TweetDB.php";

    set_time_limit(MaxTime);
    date_default_timezone_set('America/New_York');

    $server = new Server();

    $f = fopen(Flag, "w"); fwrite($f, "next"); fclose($f);
    echo "\n\n---=<( ".date('Y-m-d H:i:s')." )>=---\n\n";

    $server->startServer();
?>