<?php
    require_once 'Services/AWSSDK/autoload.php';
    require_once 'Services/TweetDB.php';
    include_once "Server/Common.php";
    
    use Aws\Sns\MessageValidator\Message;
    use Aws\Sns\MessageValidator\MessageValidator;
    use Guzzle\Http\Client;
    
    //if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); die; }
    
    try {
        $message = Message::fromRawPostData();
        $validator = new MessageValidator();
        $validator->validate($message);
    } catch (Exception $e) {
        http_response_code(404);
        die;
    }
    
    if ($message->get('Type') === 'SubscriptionConfirmation')  (new Client)->get($message->get('SubscribeURL'))->send();
    else {
        $msg = $message->get('Message');
        $data = json_decode($msg);

        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if(@socket_connect($socket, ServerIP, FeedPort) !== FALSE) {
            $new_msg = "";
            if($data->code == 1009) {
                $new_msg = json_encode(['code' => 1008]);
            } elseif($data->code == 1003) {
                $db = new TweetDB();
                $db->open();
                $row = $db->get($data->id);
                $row['code'] = 1004;
                $new_msg = json_encode($row);
            } else {
                $new_msg = json_encode(['code' => 1007]);
            }
            $new_msg = strlen($new_msg).$new_msg;
            socket_write($socket, $new_msg, strlen($new_msg));
            socket_close($socket);
        }
    }
?>