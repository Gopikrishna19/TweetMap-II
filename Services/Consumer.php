<?php
    class Consumer extends OauthPhirehose {
        private $listener;
        public function __construct() {         
            parent::__construct(Keys::T_KEY, Keys::T_S_KEY, Phirehose::METHOD_SAMPLE);

            $this->setConsumerKeys(Keys::C_KEY, Keys::C_S_KEY);
        }

        public function onStatus($listener) {
            $this->listener = $listener;
        }

        public function stopConsume() {
            echo "\nStopping stream ... ";
            $this->disconnect();
            echo "done.\n";
        }

        public function enqueueStatus($status) {
            $data = json_decode($status);
            $cond = !isset($data->delete) && !isset($data->warning);

            // filter data
            $cond = $cond && !($data->id == 0 || trim($data->user->name) == "" || trim($data->text) == "" || $data->geo == NULL);
            if($cond && $this->listener) {
                call_user_func($this->listener, $data);
            }
        }
    }
?>
