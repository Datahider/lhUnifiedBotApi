<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of lhUBA
 *
 * @author user
 */
require_once __DIR__ . '/../interface/lhUBAInterface.php';
class lhUBA implements lhUBAInterface {
    
    protected $known_secrets;

    public function __construct($known_secrets) {
        $this->known_secrets = $known_secrets;
    }
    
    public function sendTextWithHints($prefixed_recipient, $message_data) {
        if (preg_match("/^(tgu|fbu)(.*)$/",  $prefixed_recipient, $matches)) {
            switch ($matches[1]) {
                case 'tgu':
                    $this->sendMessageTG($matches[2], $message_data);
                    return;
                case 'fbu':
                    $this->sendMessageFB($matches[2], $message_data);
                    return;
                default:
                    throw new Exception("Unknown recipient prefix");
            }
        }
    }
    
    public function getUserData($prefixed_user_id) {
        if (preg_match("/^(tgu|fbu)(.*)$/",  $prefixed_user_id, $matches)) {
            switch ($matches[1]) {
                case 'tgu':
                    return $this->getTGUserData($matches[2]);
                case 'fbu':
                    return $this->getFBUserData($matches[2]);
                default:
                    throw new Exception("Unknown user prefix");
            }
        }    
    }
    
    protected function getTGUserData($user_id) {
        $raw_data = $this->apiQueryTG('getChat', ['chat_id' =>$user_id]);
        if (!$raw_data->ok) {
            throw new Exception($raw_data->description, $raw_data->error_code);
        }
        $user_data['first_name'] = $raw_data->result->first_name;
        $user_data['last_name'] = $raw_data->result->last_name;
        switch ($raw_data->result->type) {
            case 'private':
                $user_data['full_name'] = "$user_data[first_name] $user_data[last_name]";
                break;
            case 'group':
                $user_data['full_name'] = $raw_data->result->title;
                break;
        }
        return $user_data;
    }
    
    protected function getFBUserData($psid) {
        $raw_data = $this->apiGetProfileFB($psid);
        if (isset($raw_data->error)) {
            throw new Exception($raw_data->error->message, $raw_data->error->code);
        }
        $user_data['first_name'] = $raw_data->first_name;
        $user_data['last_name'] = $raw_data->last_name;
        $user_data['full_name'] = "$user_data[first_name] $user_data[last_name]";
        return $user_data;
    }


    protected function sendMessageTG($chat, $message_data) {
        $api_result = $this->apiQueryTG('sendMessage', [
            'text' => $message_data['text'],
            'chat_id' => $chat,
            'parse_mode' => 'HTML',
            'reply_markup' => $this->makeKeyboardTG($message_data)
        ]);
        if (!$api_result->ok) {
            throw new Exception($api_result->description, $api_result->error_code);
        }
    }
    
    protected function sendMessageFB($chat, $message_data) {
        $send_data = [
            'messaging_type' => 'RESPONSE',
            'recipient' => [ 'id' => $chat ],
        ];
        if (isset($message_data['hints']) && count($message_data['hints'])) {
            $send_data['message'] = [
                'attachment' => [
                    'type' => 'template',
                    'payload' => [
                        'template_type' => 'button',
                        'text' => $message_data['text'],
                        'buttons' => $this->makeButtonsFB($message_data)
                    ]
                ]
            ];
        } else {
            $send_data['message'] = [ 'text' => $message_data['text'] ];
        }
        $api_result = $this->apiSendFB($send_data);
        if (isset($api_result->error)) {
            throw new Exception($api_result->error->message, $api_result->error->code);
        }
    }

    protected function makeKeyboardTG($message_data) {
        $count = isset($message_data['hints']) ? count($message_data['hints']) : 0;
        if ($count) {
            foreach ($message_data['hints'] as $hint) {
                $hints[] = [[ 'text' => $hint ]];
            }
            $keyb = [
                'resize_keyboard' => true,
                'one_time_keyboard' => true,
                'keyboard' => $hints
            ];
        } else {
            $keyb = [ 'remove_keyboard' => true ];
        }
        return json_encode($keyb);
    }
    
    protected function makeButtonsFB($message_data) {
        $buttons = [];
        foreach ($message_data['hints'] as $hint) {
            $buttons[] = [
                'type' => 'postback',
                'title' => $hint,
                'payload' => $hint
            ];
        }
        return $buttons;
    }

    protected function apiQueryTG($func, $data) {
        $ch = curl_init('https://api.telegram.org/bot'.$this->known_secrets['tgu'].'/'.$func);
        if ( $ch ) {
            if (curl_setopt_array( $ch, array(
                CURLOPT_POST => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POSTFIELDS => $data
            ))) {    
                $content=curl_exec($ch);
                if (curl_errno($ch)) throw new Exception (curl_error ($ch).' Content provided: '.$content);
                curl_close($ch);
                return json_decode($content);
            }
            throw new Exception('curl_setopt_array returned false');
        }
        throw new Exception('curl_init returned false');
    }
    
    protected function apiSendFB($data) {
        $ch = curl_init('https://graph.facebook.com/v2.6/me/messages?access_token='.$this->known_secrets['fbu']);
        if ( $ch ) {
            if (curl_setopt_array( $ch, array(
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POSTFIELDS => json_encode($data)
            ))) {    
                $content=curl_exec($ch);
                if (curl_errno($ch)) throw new Exception (curl_error ($ch).' Content provided: '.$content);
                curl_close($ch);
                return json_decode($content);
            }
            throw new Exception('curl_setopt_array returned false');
        }
        throw new Exception('curl_init returned false');
    }

    protected function apiGetProfileFB($psid) {
        $ch = curl_init("https://graph.facebook.com/$psid?fields=first_name,last_name&access_token=".$this->known_secrets['fbu']);
        if ( $ch ) {
            if (curl_setopt_array( $ch, array(
                CURLOPT_RETURNTRANSFER => true
            ))) {    
                $content=curl_exec($ch);
                if (curl_errno($ch)) throw new Exception (curl_error ($ch).' Content provided: '.$content);
                curl_close($ch);
                return json_decode($content);
            }
            throw new Exception('curl_setopt_array returned false');
        }
        throw new Exception('curl_init returned false');
    }
}
