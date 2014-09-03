# Расширение для работы с API cервиса AlphaSMS

### Установка расширения

### Конфигурация приложения

Пример конфигурации:

`
'components' => [
    ...
    'alphaSms' => [
        'class' => 'richweber\alpha\sms\AlphaSms',
        'sender' => 'AlphaName',
        'login' => '380505550505',
        'password' => 'password',
        or
        'key' => '184452c06ft1e2f548aa18243fb6226h79764563',
    ],
    ...
],
`

Для работы с сервисом AlphaSMS используется четыре основных метода:

- `message()`
- `status()`
- `delete()`
- `balance()`

В качестве аргументов метода выступает массив $data с необходимыми параметрами.

`
$data = [
    'text' => 'Text message',
    'recipient' => 380505550505,
    'sender' => 'AlphaName',
    'type' => 0,
    'date_beg' => '1409770256',
    'date_end' => '1409770291',
    'url' => 'http://mysite.com',
];
`

### License

**yii2-alpha-sms** is released under the BSD 3-Clause License. See the bundled `LICENSE.md` for details.