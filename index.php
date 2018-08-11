<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once 'secrets.php';
require_once 'lhUnifiedBotApi/classes/lhUBA.php';

echo "Проверка отправки в Телеграм\n";
echo 'Введите полученный код> ';
$uba = new lhUBA($mysecrets);

$code = rand();
$uba->sendTextWithHints($mytgchatid, ['text' => "Код: $code", 'hints' => ['Кнопка1', 'Кнопка2']]);

$user_said = trim(fread(STDIN, 256));

if ($user_said != $code) {
    echo "FAIL!!! - Ожидалось \"$code\", получено \"$user_said\"\n";
}
echo "Ok\n";

echo "Проверка отправки в Фейсбук\n";
echo "Введите полученный код> ";
$code = rand();

$uba->sendTextWithHints($myfbchatid, ['text' => "Код: $code", 'hints' => ['Кнопка1', 'Кнопка2']]);

$user_said = trim(fread(STDIN, 256));

if ($user_said != $code) {
    echo "FAIL!!! - Ожидалось \"$code\", получено \"$user_said\"\n";
}
echo "Ok\n";
