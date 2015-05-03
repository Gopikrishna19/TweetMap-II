<?php
    use Aws\Common\Aws;
    
    class SQSClient {
        private $queueName = 'TestQueue';

        public function __construct() {
            $aws = Aws::factory('aws-config.php');
            try {
                $sqs = $aws->get('Sqs');
                $ques = $sqs->listQueues(['QueueNamePrefix' => $this->queueName]);
                $urls = $ques->get('QueueUrls');
    
                if($urls == NULL) {
                    $qurl = $this->createQueue($sqs);
                    echo "New: ";
                    sleep(5);
                } else {
                    echo "Old: ";
                    $qurl = $urls[0];
                }
                $this->sqs = $sqs;
                $this->qurl = $qurl;
                echo $qurl."\n\n";
            } catch(Exception $e) {
                echo "Error: {$e->getMessage()}";
            }
        }

        public function sendMessage($id) {
            return $this->sqs->sendMessage([
                'QueueUrl' => $this->qurl, 
                'MessageBody' => json_encode(array('code' => '1002', 'id' => $id))
            ]);
        }

        public function getMessages() {
            return $this->sqs->receiveMessage(['QueueUrl' => $this->qurl, 'MaxNumberOfMessages' => 10]);
        }

        public function deleteMessage($handle) {
            return $this->sqs->deleteMessage(['QueueUrl' => $this->qurl, 'ReceiptHandle' => $handle]);
        }
    
        private function createQueue(&$sqs) {
            return $sqs->createQueue(array(
                    'QueueName' => $this->queueName,
                    'Attributes' => array(
                        'MessageRetentionPeriod' => 60,
                        'VisibilityTimeout' => 60
                    )
                ))->get('QueueUrl');
        }
    }
    
?>