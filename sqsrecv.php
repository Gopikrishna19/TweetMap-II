<?php
    require_once "Services/AWSSDK/autoload.php";
    require_once "Services/SNSClient.php";
    require_once "Services/Alchemy.php";
    require_once "Services/TweetDB.php";

    $msg = file_get_contents("php://input");
    
    $data = json_decode($msg);
    $id = $data->id;
    
    $alc = new Alchemy();
    $sns = new SNSClient();
    $db = new TweetDB();

    $db->open();
    
    $text = $db->get($id)['text'];
    $sent = $alc->analyze($text);

    $f = fopen('_temp', 'a'); fwrite($f, print_r($sent, TRUE)."\n--\n".$text."\n\n"); fclose($f);

    if($sent != NULL) {
        $db->update($id, $sent->type, $sent->score);
        $sns->publish(json_encode(['code' => 1003, 'id' => $id]));
    } else {
        $sns->publish(json_encode(['code' => 1009]));
    }

    $db->close();
?>