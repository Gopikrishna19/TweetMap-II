<?php
    class Server {        
        public function __construct() {
            $this->stopper = FALSE;
        }
    
        public function startServer() {
            $soc = new Socket();
            $soc->setServer(ServerIP);
            $soc->setPort(ServerPort);
            $read = $soc->startListen();

            $db = new TweetDB();
            $db->open();

            $sqs = new SQSClient();
    
            $consumer = new Consumer();
            $consumer->onStatus(function($data) use($soc, $consumer, $sqs, $db) {
                $id = $data->id_str;
                $text = $data->text;
                $coord = $data->coordinates->coordinates;
                $lat = $coord[1];
                $lng = $coord[0];

                echo "$id \n";

                $db->insert($id, $text, $lat, $lng);
                
                $soc->refreshClients();

                $sqs->sendMessage($id);

                $f = fopen(Flag, "r"); $s = @fread($f, filesize(Flag)); fclose($f);
                
                if(time() > TimeLimit || (trim($s) != "" && ($s == "stop" || $s == "stopped"))) {
                    $consumer->stopConsume();
                    $soc->stopListen();
                    $db->close();
                    $f = fopen(Flag, "w"); fwrite($f, "stopped"); fclose($f);
                }
            });
            $consumer->setLang("en");
            $consumer->consume();
            if($soc->getSocket()) $soc->stopListen();
        }
    }
?>