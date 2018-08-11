<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * lhUBAInterface - интерфейс Unified Bot API для универсального обращения
 *
 * @author user
 */
interface lhUBAInterface {
    
    /**
     * __construct - creates an instance of Unified Bot Api Object
     * 
     * @param Array $known_secrets - associative array that contains tokens
     *      of supported platforms. Array indexes:
     *      tgu - bot token for access Telegram bot API
     *      fbu - Page access token for Facebook App
     */
    public function __construct($known_secrets);
    
    /**
     * sendTextWithHints - отправляет сообщение возможно с кнопками 
     * (не более трех, т.к. это ограничение фейсбук)
     * 
     * @param type $prefixed_recipient - любая строка, допускаемая платформой 
     *      для передачи в качестве id получателя с установленным префиксом
     *      платформы (tgu для телеграм, fbu для фейсбук)
     * @param type $message_data - сообщение в универсальном формате
     *      массив [ 'text' => 'Сообщение', 'hints' => ['Кнопка1', ...] ]
     */
    public function sendTextWithHints($prefixed_recipient, $message_data);

    /**
     * getUserData - retrives data of an user
     * 
     * @param type $prefixed_user_id
     */
    public function getUserData($prefixed_user_id);
}
