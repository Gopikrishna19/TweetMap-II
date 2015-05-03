<?php
    class TweetDB {
        private $conn;
        public function open() {
            echo "Opening db ... ";
            $this->conn = new mysqli("", "root", "password", "tweetdb");
            echo "done. \n";
        }

        public function insert($id, $text, $lat, $lng) {            
            $stmt = $this->conn->prepare('insert into tweets(id, text, lat, lng) values(?, ?, ?, ?)');
            $stmt->bind_param('ssdd', $id, $text, $lat, $lng);
            $stmt->execute();
            $stmt->close();
        }

        public function update($id, $sentiment, $score) {
            $stmt = $this->conn->prepare('update tweets set sentiment=?, score=? where id=?');
            echo $score."\n";
            $stmt->bind_param('sds', $sentiment, $score, $id);
            $stmt->execute();
            $stmt->close();
        }

        public function get($id) {
            $stmt = $this->conn->prepare('select text, lat, lng, score  from tweets where id=?');
            $stmt->bind_param('s', $id);
            $stmt->execute();
            $stmt->bind_result($text, $lat, $lng, $score);
            $stmt->fetch();
            $res = ['text' => $text, 'lat' => $lat, 'lng' => $lng, 'score' => $score];
            $stmt->close();
            return $res;
        }

        public function close() {
            echo "Closing db ... ";
            $this->conn->close();
            echo "done. \n";
        }
    }
?>
