# ALICE-TO-MQTT

Шлюз между Яндекс.Колонкой и MQTT сервером / умным домом
___

##  I. Установка
1. Копируем файлы проекта на хостинг с поддержкой PHP и настроенным SSL;
2. Изменяем пин-код и параметры MQTT сервера (я использую [CloudMQTT](https://www.cloudmqtt.com/)) в файле config.php;
3. Публикуем приватный навык на платформе [Яндекс.Диалоги](https://dialogs.yandex.ru/developer). В качестве Webhook URL указываем адрес:
`https://<url_сайта>/<путь_к_проекту>/alice.php`
4. Дожидаемся одобрения модерации.

##  II. Настройка
Все устройства, которыми необходимо управлять, настраиваются в файле config.php (см.переменную *$SMART_DEVICES*). Указывается имя MQTT топика и активационные названия.

Со стороны умного дома приведу пример интеграции с [Home Assistant](https://github.com/home-assistant/home-assistant).

Если в вашей системе используется Mosquitto, то необходимо настроить в нем режим моста для внешнего брокера:

	connection cloudmqtt
	address xxx.cloudmqtt.com:28093
	remote_username user
	remote_password password
	remote_clientid cloudmqtt
	bridge_protocol_version mqttv311
	try_private true
	notifications false
	start_type automatic
	topic # both 2 /cloudmqtt/
	bridge_cafile /etc/mosquitto/ca_certificates/AddTrustExternalCARoot.cer
	bridge_insecure false
	cleansession false
	local_clientid mosquitto

Соответственно *address*, *remote_username* и *remote_password* заменить на свои. В каталог */etc/mosquitto/ca_certificates/* положить файл сертификата [AddTrustExternalCARoot.cer](http://www.tbs-x509.com/AddTrustExternalCARoot.crt).

Если же локальный брокер не используется, то можно подключаться непосредственно к CloudMQTT. Читаем про это на [странице документации](https://www.home-assistant.io/docs/mqtt/broker).

В Home Assistant для каждого из подключенных к навыку устройств необходимо прописать автоматизации на включение и отключение:

	- id: '1549298936175'
	  alias: 'Алиса: Свет в спальне (вкл)'
	  trigger:
	  - payload: 'on'
	    platform: mqtt
	    topic: /cloudmqtt/bedroom-light
	  condition:
	  - condition: state
	    entity_id: light.bedroom_light
	    state: 'off'
	  action:
	  - data:
	      entity_id: light.bedroom_light
	    service: light.turn_on

	- id: '1549298970158'
	  alias: 'Алиса: Свет в спальне (выкл)'
	  trigger:
	  - payload: 'off'
	    platform: mqtt
	    topic: /cloudmqtt/bedroom-light
	  condition:
	  - condition: state
	    entity_id: light.bedroom_light
	    state: 'on'
	  action:
	  - data:
	      entity_id: light.bedroom_light
	    service: light.turn_off

##  III. Управление
Данный шлюз реализован в виде приватного навыка и для получения к нему доступа необходимо произвести авторизацию на каждом из используемых устройств.

Произнесите "*Алиса, попроси <имя_навыка> запомнить это устройство*" для запуска этой процедуры. На запрос системы введите пин-код, указанный в файле настроек.

После прохождения авторизации вам становятся доступными функции включения и отключения настроенных устройств умного дома. Например:

	Алиса, попроси <имя_навыка> включить свет в ванной

Или, команда непосредственно внутри запущенного навыка:

	Выключи весь свет в доме

##  IV. Используемые библиотеки
YANDEX-DIALOGS-PHP: https://github.com/thesoultaker48/yandex-dialogs-php

phpMQTT: https://github.com/bluerhinos/phpMQTT
