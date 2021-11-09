<?php

/*
	Шлюз между Яндекс.Колонкой и MQTT сервером
	Author: Sergey Avdeev <avdeevsv91@gmail.com>
	URL: https://github.com/kasitoru/alice-to-mqtt
*/


include_once 'config.php';

ini_set('error_reporting', 0);
ini_set('display_errors', 0);

include_once 'yandex-dialogs.class.php';
include_once 'libraries/phpMQTT.php';

$alice = new YandexDialog();

if($alice->get_request()) {

	// Проверка прав доступа
	if($alice->get_user_data('access') != 'allow') {
		
		// Если пользователь временно забанен
		$invalid_pin = $alice->get_user_data('invalid_pin');
		if($invalid_pin && $invalid_pin['count'] >= INVALID_PIN_CODE_LIMIT && time() - $invalid_pin['last'] < INVALID_PIN_CODE_TIMEOUT) {
			
			$alice->add_message('Из-за многочисленных ошибок при вводе пин-кода доступ к данному навыку временно ограничен!');
			$alice->end_session();
			
		} else {
			
			// Снимаем бан
			if($invalid_pin && time() - $invalid_pin['last'] > INVALID_PIN_CODE_TIMEOUT) {
				$alice->set_user_data('invalid_pin', null);
			}

			// Команда запомнить пользователя
			function _remember_user($percent, $alice) {
				$alice->add_message('Для выполнения этого действия введите пин-код.');
				$alice->set_session_data('action', 'wait_pin_code');
			}
			$alice->bind_percentage_action(array(array('запомни', 'сохрани', 'запиши', 'добавь', 'запомнить', 'сохранить', 'записать', 'добавить'), array('пользователя', 'устройство', 'колонку', 'телефон')), 100, '_remember_user');

			// Действие по-умолчанию
			function _no_access($alice) {
				if($alice->get_session_data('action') == 'wait_pin_code') {
					
					// Получаем пин-код
					$pin_code = null;
					foreach($alice->request['request']['nlu']['tokens'] as $token) {
						if(is_numeric($token)) {
							$pin_code .= $token;
						}
					}
					
					// Проверяем пин-код
					if($pin_code == ACCESS_ALLOW_PIN_CODE) {
						$alice->add_message('Данное устройство успешно запомнено. Теперь вы можете управлять умным домом!');
						$alice->set_session_data('action', null);
						$alice->set_user_data('invalid_pin', null);
						$alice->set_user_data('access', 'allow');
					} else {
						if($invalid_pin = $alice->get_user_data('invalid_pin')) {
							$invalid_pin['count']++;
						} else {
							$invalid_pin = array('count' => 1);
						}
						$invalid_pin['last'] = time();
						$alice->set_user_data('invalid_pin', $invalid_pin);
						$attempts = INVALID_PIN_CODE_LIMIT - $invalid_pin['count'];
						if($attempts > 0) {
							$alice->add_message('Неверный пин-код! У вас '.pluralForm($attempts, array('осталась', 'осталось', 'осталось'), false).' '.($attempts==1?'одна':$attempts).' '.pluralForm($attempts, array('попытка', 'попытки', 'попыток'), false).'.');
						} else {
							$alice->add_message('Из-за многочисленных ошибок при вводе пин-кода доступ к навыку будет ограничен на один час!');
							$alice->end_session();
						}
					}
					
				} else {
					
					$alice->add_message('Извините, но данный навык является приватным и недоступен для широкого круга пользователей!');
					$alice->end_session();
					
				}

			}
			$alice->bind_default_action('_no_access');

		}
	} else {

		// Новая сессия
		function _new_session($alice) {
			if(empty($alice->request['request']['command'])) { // Навык запущен без команды
				$alice->add_button('Помощь');
				$alice->add_message('Для управления устройствами умного дома произнесите соответствующую команду.');
			} else { // При запуске навыка ему была передана команда
				$alice->end_session();
			}
		}
		$alice->bind_new_action('_new_session');

		// Включить устройство
		function _turn_on($token, $alice) {
			$answer = _do_action($alice->request['request']['command'], 'on');
			$alice->add_message($answer);
		}
		$alice->bind_words_action(array('включи', 'включить', 'активируй', 'активировать', 'зажги', 'зажечь', 'запусти', 'запустить'), '_turn_on');

		// Отключить устройство
		function _turn_off($token, $alice) {
			$answer = _do_action($alice->request['request']['command'], 'off');
			$alice->add_message($answer);
		}
		$alice->bind_words_action(array('выключи', 'выключить', 'отключи', 'отключить', 'деактивируй', 'деактивировать', 'потуши', 'потушить', 'погаси', 'погасить', 'останови', 'остановить'), '_turn_off');

		// Помощь
		function _help($token, $alice) {
			$alice->add_message('С помощью данного навыка можно включать и отключать электрические приборы, интегрированные в систему умного дома. Просто скажите что нужно сделать и я с радостью это исполню. Для завершения диалога используйте ключевое слово "хватит".');
		}
		$alice->bind_words_action(array('помощь', 'помоги', 'умеешь'), '_help');
		
		// Завершение диалога
		function _end($token, $alice) {
			$alice->add_message('Хорошо. До скорого!');
			$alice->end_session();
		}
		$alice->bind_words_action(array('нет', 'хватит', 'закончили', 'стоп', 'достаточно', 'пока', 'перестань', 'выйти', 'выключи', 'выключить'), '_end');

		// Благодарности
		function _thanks($token, $alice) {
			$alice->add_message('Пожалуйста! Я могу еще чем нибудь помочь?');
		}
		$alice->bind_words_action(array('спасибо', 'благодарю', 'молодец', 'умничка', 'умница', 'молодчина', 'благодарствую', 'респект'), '_thanks');
		
		// Неизвестная команда
		function _default($alice) {
			$alice->add_button('Помощь');
			$alice->add_message('Извините, но я не понимаю что вы от меня хотите. Для получения справки используйте ключевое слово "помощь".');
		}
		$alice->bind_default_action('_default');

	}

    // Отправляем ответ
	$alice->finish(true);
}

// Выполнение действия
function _do_action($command, $state=null) {
	global $SMART_DEVICES;
	// Выполняем действие
	$result = 'Я не знаю такой команды или устройства!';
	foreach($SMART_DEVICES as $key=>$value) {
		foreach($value as $i=>$item) {
			if(mb_stripos($command, $item)!==false) {
				$mqtt = new Bluerhinos\phpMQTT(MQTT_SERVER, MQTT_PORT, 'alice-skill');
				if($mqtt->connect(true, null, MQTT_LOGIN, MQTT_PASSWORD)) {
					$mqtt->publish($key, $state, 0);
					$mqtt->close();
					if($state == 'on') {
						$result = 'Включаю '.$value[$i].'...';
					} else {
						$result = 'Выключаю '.$value[$i].'...';
					}
				} else {
					$result = 'Во время выполнения команды произошла ошибка! Пожалуйста, попробуйте позже.';
				}
				break 2;
			}
		}
	}
	
	return $result;
}

// Корректное склонение слов для числительных
function pluralForm($number, $after, $return_num=true) {
	$cases = array(2, 0, 1, 1, 1, 2);
	return ($return_num?$number.' ':'').$after[($number%100>4 && $number%100<20)?2:$cases[min($number%10, 5)]];
}
