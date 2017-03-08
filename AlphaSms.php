<?php

/**
 * Расширение Yii Framework 2 для работы с API cервиса AlphaSMS.
 *
 * @copyright Copyright &copy; Roman Bahatyi, richweber.net, 2014
 * @package yii2-alpha-sms
 * @version 1.0.0
 */

namespace richweber\alpha\sms;

use stdClass;
use SimpleXMLElement;
use yii\base\InvalidConfigException;

/**
 * Расширение для работы с API cервиса AlphaSMS.
 *
 * Для работы с сервисом AlphaSMS используется
 * четыре основных метода:
 *
 * - `message()`
 * - `status()`
 * - `delete()`
 * - `balance()`
 *
 * В качестве аргументов метода выступает
 * массив $data с необходимыми параметрами
 *
 * Пример конфигурации:
 * ~~~
 * 'components' => [
 *       ...
 *       'alphaSms' => [
 *           'class' => 'richweber\alpha\sms\AlphaSms',
 *           'sender' => 'AlphaName',
 *           'login' => '380505550505',
 *           'password' => 'password',
 *           or
 *           'key' => '184452c06ft1e2f548aa18243fb6226h79764563',
 *       ],
 *       ...
 *   ],
 * ~~~
 *
 * @author Roman Bahatyi <rbagatyi@gmail.com>
 * @since 1.0
 *
 */

class AlphaSms
{
    /**
     * @var string Логин пользователя Alpha-SMS
     */
    private $login;

    /**
     * @var string Пароль для входа
     *
     */
    private $password;

    /**
     * @var string API-ключ
     *
     */
    private $key;

    /**
     * @var string Альфа-имя отправителя сообщения
     *
     */
    public $sender;

    /**
     * @var string Имя метода к выполнению
     */
    private $operation;

    /**
     * @var integer Тип сообщения (смотри константы)
     */
    private $typeMessage = self::TYPE_MESSAGE_SMS;

    /**
     * @var string URL-адрес сервиса
     */
    private $service = 'http://alphasms.com.ua/api/xml.php';

    /**
     * @var integer Код обработки запроса сервисом
     */
    private $responseStatusCode;

    /**
     * @var object Объект ответа на запрос
     */
    private $responseObject;

    const TYPE_MESSAGE_SMS = 0;
    const TYPE_MESSAGE_FLASH = 1;
    const TYPE_MESSAGE_PUSH = 2;
    const TYPE_MESSAGE_VOICE = 3;

    /**
     * AlphaSms constructor.
     *
     * @param null $login
     * @param null $password
     * @param null $apiKey
     * @param null $sender
     *
     * @throws InvalidConfigException
     */
    public function __construct($login = null, $password = null, $apiKey = null, $sender = null)
    {
        $this->login = $login;
        $this->password = $password;
        $this->key = $apiKey;
        $this->sender = $sender;

        if ($this->login === null && $this->password === null && $this->key === null) {
            throw new InvalidConfigException('Invalid configuration');
        } elseif ($this->key === null && ($this->login === null || $this->password === null)) {
            throw new InvalidConfigException('Invalid login or password');
        }
    }

    /**
     * Отправляем сообщение
     *
     * Время отправки и окончания срока жизни SMS
     * необходимо указывать в формате UTC
     *
     * Пример массива:
     * ~~~
     * $data = [
     *    'text' => 'Text message',
     *    'recipient' => 380505550505,
     *    'sender' => 'AlphaName',
     *    'type' => 0,
     *    'date_beg' => '1409770256',
     *    'date_end' => '1409770291',
     *    'url' => 'http://mysite.com',
     * ];
     * ~~~
     *
     * @param array $data Массив параметров для тела запроса
     * @throws InvalidConfigException
     * @return object Объект ответа
     *
     */
    public function message($data)
    {
        if (isset($data['sender'])) {
            $this->sender = $data['sender'];
        }
        if (!$this->sender) {
            throw new InvalidConfigException("Invalid 'sender'.");
        } else {
            if (strlen($this->sender) > 11) {
                throw new InvalidConfigException("Invalid 'sender'.");
            }
        }

        if (!isset($data['text']) || !$data['text']) {
            throw new InvalidConfigException("Invalid 'text'.");
        }

        // TODO: провести проверку номера телефона
        if (!isset($data['recipient']) || !$data['recipient']) {
            throw new InvalidConfigException("Invalid 'recipient'.");
        }

        if (!isset($data['id']) || !$data['recipient']) {
            $data['id'] = $this->generateUnicId();
        }

        if (!isset($data['type']) || !$data['type']) {
            $this->typeMessage = self::TYPE_MESSAGE_SMS;
        } else {
            $this->typeMessage = $data['type'];
        }

        if (
            ($this->typeMessage == self::TYPE_MESSAGE_PUSH && !isset($data['url']))
            || ($this->typeMessage == self::TYPE_MESSAGE_PUSH && !$data['url'])
        ) {
            throw new InvalidConfigException("Invalid 'url'.");
        }

        if (
            (isset($data['date_beg']) && $data['date_beg'])
            && (isset($data['date_end']) && $data['date_end'])
        ) {
            if ($data['date_beg'] >= $data['date_end']) {
                throw new InvalidConfigException("The 'date_end' parameter must exceed 'date_beg'.");
            }
        }

        $this->operation = 'message';
        $xml = $this->getRequestString($data);
        $this->run($xml);

        return $this->responseObject;
    }

    /**
     * Получаем статус SMS
     *
     * Массив $data должен содержать минимум
     * один из параметров: id или sms_id
     *
     * @param array $data Массив параметров для тела запроса
     * @throws InvalidConfigException Если не указан ни один идентификатор SMS
     * @return object Объект ответа
     */
    public function status($data)
    {
        if (!isset($data['id']) && !isset($data['sms_id'])) {
            throw new InvalidConfigException("Must be specified one of the parameters 'id' or 'sms_id'.");
        }

        $this->operation = 'status';
        $xml = $this->getRequestString($data);
        $this->run($xml);

        return $this->responseObject;
    }

    /**
     * Удаляем SMS с очереди на отправку
     *
     * Массив $data должен содержать минимум
     * один из параметров: id или sms_id
     *
     * @param array $data Массив параметров для тела запроса
     * @throws InvalidConfigException Если не указан ни один идентификатор SMS
     * @return object Объект ответа
     */
    public function delete($data)
    {
        if (!isset($data['id']) && !isset($data['sms_id'])) {
            throw new InvalidConfigException("Must be specified one of the parameters 'id' or 'sms_id'.");
        }

        $this->operation = 'delete';
        $xml = $this->getRequestString($data);
        $this->run($xml);

        return $this->responseObject;
    }

    /**
     * Получение баланса счета отправителя
     *
     * @return object Объект ответа
     */
    public function balance()
    {
        $this->operation = 'balance';
        $xml = $this->getRequestString();
        $this->run($xml);

        return $this->responseObject;
    }

    /**
     * Генерирование уникального номера для СМС
     * в системе учета отправителя
     *
     * @return integer Уникальный номер
     */
    public function generateUnicId()
    {
        $time = microtime();
        $int = substr($time, 11);
        $flo = substr($time, 2, 5);

        return $int . $flo;
    }

    /**
     * Формируем тело запроса
     *
     * @param array $data Массив параметров для тела запроса
     * @return string  XML-строка
     */
    private function getRequestString($data = false)
    {
        $requestObject = $this->initRequestBody();
        $operation = $requestObject->addChild($this->operation);

        if ($this->operation == 'status') {
            $msg = $operation->addChild('msg');

            if (isset($data['id'])) $msg->addAttribute('id', $data['id']);
            if (isset($data['sms_id'])) $msg->addAttribute('sms_id', $data['sms_id']);
        }

        if ($this->operation == 'delete') {
            $msg = $operation->addChild('msg');

            if (isset($data['id'])) $msg->addAttribute('id', $data['id']);
            if (isset($data['sms_id'])) $msg->addAttribute('sms_id', $data['sms_id']);
        }

        if ($this->operation == 'message') {
            $msg = $operation->addChild('msg', $data['text']);

            $msg->addAttribute('recipient', $data['recipient']);
            $msg->addAttribute('sender', $this->sender);
            $msg->addAttribute('type', $this->typeMessage);

            if (isset($data['id']) && $data['id']) $msg->addAttribute('id', $data['id']);
            if (
                isset($data['url']) && $data['url']
                && $this->typeMessage == self::TYPE_MESSAGE_PUSH
            ) {
                $msg->addAttribute('url', $data['url']);
            }
            if (isset($data['date_beg']) && $data['date_beg']) {
                $msg->addAttribute('date_beg', date(DATE_ISO8601, $data['date_beg']));
            }
            if (isset($data['date_end']) && $data['date_end']) {
                $msg->addAttribute('date_end', date(DATE_ISO8601, $data['date_end']));
            }
        }

        $requestString = $this->formatXML($requestObject);

        return $requestString;
    }

    /**
     * Формируем основу запроса
     * и в завимимости от конфигурации
     * передаем параметры для авторизации
     *
     * @return object Класс SimpleXMLElement
     */
    private function initRequestBody()
    {
        $requestObject = new SimpleXMLElement('<package></package>');

        if ($this->key) {
            $requestObject->addAttribute('key', $this->key);
        } else {
            $requestObject->addAttribute('login', $this->login);
            $requestObject->addAttribute('password', $this->password);
        }

        return $requestObject;
    }

    /**
     * Получаем объект класса DOMElement
     * из объекта класса SimpleXMLElement
     *
     * @param object $simpleXMLObject класс SimpleXMLElement
     * @return object XML-объект
     */
    private function formatXML($simpleXMLObject)
    {
        $dom = dom_import_simplexml($simpleXMLObject)->ownerDocument;
        $dom->formatOutput = true;

        return $dom->saveXML();
    }

    /**
     * Метод отправляет запрос на сервис
     * и в случае успешной обработки
     * возвращает ответ как объект
     *
     * @param string $xml XML-строка запроса
     */
    private function run($xml)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->service);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($ch, CURLOPT_POST, 1);

        $response = curl_exec($ch);
        $this->responseStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($this->responseStatusCode === 200) {
            libxml_use_internal_errors(true);
            if (simplexml_load_string($response)) {
                $this->responseObject = simplexml_load_string($response);
            } else {
                die('Bad response: <br>' . htmlspecialchars($response));
            }
        }

        $errno = curl_errno($ch);
        $error = curl_error($ch);

        curl_close($ch);

        if ($errno) {
            die('cURL error #' . $errno . ' - ' . $error);
        }
    }
}
