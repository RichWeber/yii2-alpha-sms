# Расширение для работы с API cервиса AlphaSMS

[АльфаSMS](http://alphasms.ua/?ref=vk) - ведущая компания на украинском рынке SMS-услуг для корпоративных и частных  заказчиков.

Компания использует прямые подключения к национальным GSM-операторам, международным SMS-gate'ам, обеспечиваем доставку сообщений абонентам сотовых сетей Украины, СНГ и [других стран мира](http://alphasms.ua/services/world/).

### Установка расширения

Желательно устанавливать расширение с помощью [composer](http://getcomposer.org/download/).

Выполните команду

```
php composer.phar require richweber/yii2-alpha-sms "*"
```

или добавьте

```
"richweber/yii2-alpha-sms":"*"
```

в раздел `require` вашего `composer.json` файла.

### Конфигурация приложения

Пример конфигурации:

```php
'components' => [
    ...
    'alphaSms' => [
        'class' => 'richweber\alpha\sms\AlphaSms',
        'sender' => 'AlphaName',
        'login' => '380505550505',
        'password' => 'password',
        // or
        // 'key' => '184452c06ft1e2f548aa18243fb6226h79764563',
    ],
    ...
],
```

Для работы с сервисом [AlphaSMS](http://alphasms.ua/?ref=vk) используется четыре основных метода:

- `message()`
- `status()`
- `delete()`
- `balance()`

В качестве аргументов метода выступает массив $data с необходимыми параметрами.

```php
$data = [
    'text' => 'Text message',
    'recipient' => 380505550505,
    'sender' => 'AlphaName',
    'type' => 0,
    'date_beg' => '1409770256',
    'date_end' => '1409770291',
    'url' => 'http://mysite.com',
];
```

### License

**yii2-alpha-sms** is released under the BSD 3-Clause License. See the bundled `LICENSE.md` for details.