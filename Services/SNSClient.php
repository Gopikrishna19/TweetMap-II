<?php
    use Aws\Common\Aws;

    class SNSClient {
        private $topic = 'tweetmap-sns';
        private $endpoint = 'http://tweetmap-cc.elasticbeanstalk.com/snsrecv.php';
        // private $endpoint = 'http://fa6c8e24.ngrok.io/snsrecv.php';
        private $sns;
        private $arn;

        public function __construct() {
            $aws = Aws::factory('aws-config.php');
            try {
                $this->sns = $aws->get('Sns');
                $this->getArn();
            } catch(Exception $e) {
                throw $e;
            }
        }

        private function getArn() {
            $topics = $this->sns->listTopics()->get('Topics');
            
            $topicfound = FALSE;
            $subfound = FALSE;

            if($topics != NULL && count($topics) != 0) {
                foreach($topics as $topic) {
                    if(preg_match('/tweetmap-sns/', $topic['TopicArn']) == 1) {
                        $topicfound = TRUE;
                        $topicArn = $topic['TopicArn'];
                        echo "Old: ".$topicArn."\n";

                        $subs = $this->sns->listSubscriptionsByTopic(['TopicArn' => $topicArn])->get('Subscriptions');

                        if($subs != NULL && count($subs) != 0) {
                            foreach($subs as $sub) {
                                if($sub['SubscriptionArn'] != 'PendingConfirmation' && $sub['Endpoint'] == $this->endpoint) {
                                    $subfound = TRUE;
                                    echo "Everything setup\n";
                                    break;
                                }
                            }
                        } 
                        break;
                    }
                }
            }

            if(!$topicfound) {
                echo "No topic found. Creating new.\n";
                $topicArn = $this->createTopic();
                echo "New: ".$topicArn."\n";
            }

            $this->arn = $topicArn;

            if(!$subfound) {
                echo "No subscription found. Creating new.\n";
                $this->createSubscription();
            }
        }

        private function createSubscription() {
            $this->sns->subscribe(array(
                'TopicArn' => $this->arn,
                'Protocol' => 'http',
                'Endpoint' => $this->endpoint
            ));
        }

        private function createTopic() {
            return $this->sns->createTopic(array('Name' => $this->topic))->get('TopicArn');
        }

        public function publish($msg) {
            $this->sns->publish(array(
                'TopicArn' => $this->arn,
                'Message' => $msg
            ));
        }
    }
?>
