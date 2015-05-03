<?php
    class Alchemy {
        private $apikey;
        private $url;

        public function __construct() {
            $this->apikey = '';
            $this->url = 'http://access.alchemyapi.com/calls/text/TextGetTextSentiment';
        }

        public function analyze($text) {
            $params = array(
                'apikey' => $this->apikey,
                'outputMode' => 'json',
                'text' => $text
            );
            $header = array('http' => array(
                               'method' => 'POST',
                               'header'=>'Content-Type: application/x-www-form-urlencode',
                               'content'=> http_build_query($params)
                      )
            );

            try {
                $fp = @fopen($this->url, 'rb', FALSE, stream_context_create($header));
                $res = @stream_get_contents($fp);
                fclose($fp);
                print_r($res);
                $res = json_decode($res);
                if($res->status == "OK") {
                    $sent = $res->docSentiment;
                    if($sent->type == "neutral") $sent->score = 0;
                    return $sent;
                }
                else
                    return NULL;
            } catch(Exception $e) {
                print_r($e);
            }
        }
    }
?>