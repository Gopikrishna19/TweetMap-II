<?php
    class Socket {
        private $socket;
        private $fsocket;
        private $server;
        private $port;
        private $clients;
        private $feeds;

        public function __construct() {
            $this->server = "localhost";
            $this->port = 7765;

            echo "Creating socket ... ";
            $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1);

            $this->fsocket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            socket_set_option($this->fsocket, SOL_SOCKET, SO_REUSEADDR, 1);
            echo "done. \n";
        }

        public function setServer($url) { $this->server = $url; }

        public function setPort($port) {
            $this->port = $port;
            $this->fport = $port + 1;
        }

        public function getSocket() { return $this->socket; }

        public function startListen() {
            echo "Opening socket ... ";
            socket_bind($this->socket, $this->server, $this->port);
            socket_listen($this->socket);
            $this->clients = array($this->socket);

            socket_bind($this->fsocket, $this->server, $this->fport);
            socket_listen($this->fsocket);
            $this->feeds = array($this->fsocket);

            echo "listening. \n";
        }

        public function stopListen() {
            $this->refreshClients();

            echo "Closing feeds ... ";
            foreach($this->feeds as $feed) {
                if($feed != $this->fsocket) {
                    unset($this->feeds[array_search($feed, $this->feeds)]);
                    @socket_close($feed);
                }
            }
            echo "done. \n";

            echo "Closing clients ... ";
            foreach($this->clients as $client) {
                if($client != $this->socket) {
                    $this->sendMessageOne(json_encode(["code" => 1010, "msg" => "Closing Connection"]), $client);
                    unset($this->clients[array_search($client, $this->clients)]);
                    @socket_close($client);
                }
            }
            echo "done. \n";

            echo "Closing socket ... ";
            @socket_close($this->socket);
            @socket_close($this->fsocket);
            $this->socket = NULL;
            echo "done. \n";
        }

        public function refreshClients() {
            $clients = $this->clients;
            $feeds = $this->feeds;

            socket_select($clients, $null, $null, 0, 10);
            socket_select($feeds, $null, $null, 0, 10);

            if(in_array($this->socket, $clients)) {
                echo "\nFound new client. \n\n";
                $socket = socket_accept($this->socket);
                @$this->acceptClient(socket_read($socket, 1024), $socket);
                $this->clients[] = $socket;
                
                $this->sendMessageOne(json_encode(["code" => 1001, "msg" => "Welcome, $socket!"]), $socket);

                unset($clients[array_search($this->socket, $clients)]);
            }

            if(in_array($this->fsocket, $feeds)) {
                echo "\nFound new Feed. \n\n";
                $socket = socket_accept($this->fsocket);
                $this->feeds[] = $socket;

                unset($feeds[array_search($this->fsocket, $feeds)]);
            }

            foreach($clients as $client) {
                $buf = @socket_read($client, 1024, PHP_NORMAL_READ);
                if ($buf === false) {
                    unset($this->clients[array_search($client, $this->clients)]);
                    echo "\nClient closed. \n\n";
                }
            }

            foreach($feeds as $feed) {
                while(socket_recv($feed, $buf, 1024, 0) >= 1) {
                    while($buf != "") {
                        preg_match("/^\d+/", $buf, $matches);
                        $len = $matches[0];
                        $obj = substr($buf, strlen($len), $len);
                        $buf = substr($buf, strlen($len) + $len);
                        echo $obj." ".$len."\n";
                        $this->sendMessage($obj, $feed);
                    }
                    break 2;
                }

                $buf = @socket_read($feed, 1024, PHP_NORMAL_READ);
                if ($buf === false) {
                    unset($this->feeds[array_search($feed, $this->feeds)]);
                    echo "\nFeed Closed. \n\n";
                }
            }
        }

        private function acceptClient($header, $client) {
            $host = "localhost";
            $port = 2428;

            $secAccept = $this->getAcceptKey($header);

            $upgrade  = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" .
                        "Upgrade: websocket\r\n" .
                        "Connection: Upgrade\r\n" .
                        "WebSocket-Origin: $host\r\n" .
                        "WebSocket-Location: ws://$host:$port/index.php\r\n".
                        "Sec-WebSocket-Accept: $secAccept\r\n\r\n";

            @socket_write($client, $upgrade, strlen($upgrade));
        }

        public function sendMessage($msg, $origclient = NULL) {
            $this->refreshClients();
            $msg = Socket::mask($msg);
            foreach($this->clients as $client) {
                if($origclient != $client) $this->sendMessageOne($msg, $client, TRUE);
            }
        }

        public function sendMessageOne($msg, $client, $masked = FALSE) {
            if(!$masked) $msg = Socket::mask($msg);
            @socket_write($client, $msg, strlen($msg));
        }

        private function getAcceptKey($header) {
            $headers = array();
            $lines = preg_split("/\r\n/", $header);
            foreach($lines as $line) {
                $line = chop($line);
                if(preg_match('/\A(\S+): (.*)\z/', $line, $matches)) {
                    $headers[$matches[1]] = $matches[2];
                }
            }
            $secKey = $headers['Sec-WebSocket-Key'];
            return base64_encode(pack('H*', sha1($secKey.'258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
        }

        public static function mask($text) {
            $b1 = 0x80 | (0x1 & 0x0f);
            $length = strlen($text);
            if($length <= 125) $header = pack('CC', $b1, $length);
            elseif($length > 125 && $length < 65536) $header = pack('CCn', $b1, 126, $length);
            elseif($length >= 65536) $header = pack('CCNN', $b1, 127, $length);
            return $header.$text;
        }

        public static function unmask($text) {
            $length = ord($text[1]) & 127;
            if($length == 126) {
                $masks = substr($text, 4, 4);
                $data = substr($text, 8);
            } elseif($length == 127) {
                $masks = substr($text, 10, 4);
                $data = substr($text, 14);
            } else {
                $masks = substr($text, 2, 4);
                $data = substr($text, 6);
            }
            $text = "";
            for ($i = 0; $i < strlen($data); ++$i) {
                $text .= $data[$i] ^ $masks[$i%4];
            }
            return $text;
        }
    }
?>