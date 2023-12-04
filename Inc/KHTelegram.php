<?php

class KHTelegram {
    private $token;
    private $user;
    private $message;

    public function __construct() {
        // Initialize your API data here or you can set it using setter methods.
        $this->token = get_option('telegram_token_setting');
        error_log('token '.$this->token);
        //$this->token = '5904544528:AAFzpi5cK9GSdhePYzAQo1LbYkGlb3IupUY'; // Replace with your bot token
        $this->user = get_option('telegram_chat_id_setting');
        error_log('chat id '.$this->user);

        //$this->user = '5636326568'; // Replace with your chat ID
        $this->message = date('H:i');
    }

    public function setToken($token) {
        $this->token = $token;
    }

    public function setUser($user) {
        $this->user = $user;
    }

    public function setMessage($message) {
        $this->message = $message;
    }

    public function send_khwpforms_message(array $serialized_data, array $entry_id) {
        $message = "🚀 You have received a new submission from:\n\n Form id: '{$entry_id['id']}':\n";

        foreach($serialized_data as $key => $value) {
            // Skip empty values
            if(empty($value)) {
                continue;
            }

            $message .= "\n$key: $value\n";
            //}
            $this->setMessage($message);
        }
    }
    public function sendNotification() {
        $url = 'https://api.telegram.org/bot'.urlencode($this->token).
            '/sendMessage?chat_id='.urlencode($this->user).'&text='.
            urlencode($this->message);

        $response = file_get_contents($url);

        if($response === false) {
            return 'Failed to send the notification.';
        }

        $response = json_decode($response, true);

        if($response['ok']) {
            return 'Notification sent successfully!';
        } else {
            return 'Failed to send the notification. Error: '.$response['description'];
        }
    }
}

?>