# ALICE-TO-MQTT

Шлюз между Яндекс.Колонкой и MQTT сервером
___

##  I. Установка
1. Копируем файлы проекта на хостинг с поддержкой PHP и настроенным SSL;
2. Изменяем пин-код и параметры MQTT сервера (я использую [CloudMQTT](https://www.cloudmqtt.com/)) в файле config.php;
3. Публикуем навык на платформе [Яндекс.Диалоги](https://dialogs.yandex.ru/developer). В качестве Webhook URL указываем адрес "https://<url_сайта>/<путь_к_проекту>/alice.php";
4. Дожидаемся одобрения модерации.

##  II. Настройка
Все устройства, которыми необходимо управлять, настраиваются в файле config.php (см.переменную $SMART_DEVICES). Указывается имя MQTT топика и активационные названия.

##  II. Управление

##  III. Используемые библиотеки
YANDEX-DIALOGS-PHP: https://github.com/thesoultaker48/yandex-dialogs-php
phpMQTT: https://github.com/bluerhinos/phpMQTT
