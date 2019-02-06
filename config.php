<?php

// Параметры авторизации
define('ACCESS_ALLOW_PIN_CODE',		'1234');	// Пин-код для добавления новых устройств
define('INVALID_PIN_CODE_LIMIT',	5);			// Лимит неправильных попыток ввода пин-кода
define('INVALID_PIN_CODE_TIMEOUT',	3600);		// Время бана в секундах

// MQTT
define('MQTT_SERVER',	'xxx.cloudmqtt.com');	// Адрес
define('MQTT_PORT',		18093);					// Порт
define('MQTT_LOGIN',	'login');				// Логин
define('MQTT_PASSWORD',	'password');			// Пароль

// Устройства
$SMART_DEVICES = array(
	// Весь свет в квартире
	'all-light' => array('весь свет', 'все освещение', 'все лампочки'),
	// Спальня
	'bedroom-light' => array('свет в спальне', 'освещение в спальне', 'люстру в спальне'),
	// Зал
	'lounge-light' => array('свет в зале', 'освещение в зале', 'люстру в зале'),
	// Кухня
	'kitchen-light' => array('свет на кухне', 'освещение на кухне', 'люстру на кухне'),
	// Ванная
	'bathroom-light' => array('свет в ванной', 'свет в ванне', 'освещение в ванной', 'освещение в ванне'),
);
