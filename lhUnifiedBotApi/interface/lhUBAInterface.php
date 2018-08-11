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
     * __construct - cоздает экземпляр объекта универсального интерфейса ботов
     * 
     * @param Array $known_secrets - ассоциативный массив содержащий токены
     *      поддерживаемых платформ. Индексы массива:
     *      tgu - бот токен телеграма
     *      fbu - токен доступа к странице фейсбука
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

    
}
