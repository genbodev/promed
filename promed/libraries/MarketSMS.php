<?php

/**
 * Отправка SMS через marketsms.ru
 *
 * @author Alexander "Alf" Arefyev <avaref@gmail.com>
 * @author Lich (old k-vrachu)
 */
require_once(APPPATH . "libraries/SMS.php");

class MarketSMS extends SMS {

    /**
     * Логин для сервиса
     *
     * @var string
     */
    protected $login;

    /**
     * Пароль для сервиса
     * 
     * @var string 
     */
    protected $password;

    /**
     * Настройки прокси
     * 
     * @var mixed
     */
    public $proxy;

    /**
     * Информация об отправке
     * 
     * @var mixed
     */
    public $information = NULL;

    /**
     * Состояние отправки
     *
     * @var mixed
     */
    public $status = NULL;

    /**
     * Сервис
     * 
     * @var string
     */
    private $url = "http://marketsms.ru/mm/send.php";

    /**
     * Отправитель
     *
     * @var string
     */
    protected $sender = "promedweb";

    /**
     * Конструктор SMS для отправки
     *
     * @param array $login Логин для сервиса
     * @param array $password Пароль для сервиса
     * @param string $sender Идентификатор отправителя
     */
    public function __construct($login, $password, $sender = NULL) {
        parent::__construct($login, $password, $sender);
    }

    /**
     * Отправка сообщения
     *
     * @param array $message Сообщение
     * @return int Код ошибки или 0
     */
    public function send($message) {
        $number = '7' . $message['number'];

        if (mb_strlen($message['text']) > 70) {
            $message['text'] = $this->rus2translit($message['text']);
        }
        $message['text'] = toUTF($message['text']);
        
        $xml = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>
                <request uid=\"{$message['id']}\" sender=\"" . $this->sender . "\">
                    <message>
                        <text>" . $message['text'] . "</text>
                        <abonent phone=\"{$number}\" />
                    </message>
                    <security>
                        <login value=\"" . $this->login . "\"/>
                        <sign value=\"" . md5($message['id'] . $this->password) . "\"/>
                    </security>
                </request>";

        // Параметры подключения
        $http_options = array(
            'method' => 'POST',
            'content' => $xml,
        );

        if (!empty($this->proxy)) {
            // Соединение через прокси
            $proxy_options = array(
                'proxy' => 'tcp://' . $this->proxy->address . ':' . $this->proxy->port,
                'request_fulluri' => true
            );

            if (!empty($this->proxy->login) && !empty($this->proxy->password)) {
                $proxy_options = array_merge(
                        $proxy_options, array('header' => 'Proxy-Authorization: Basic ' . base64_encode($this->proxy->login . ':' . $this->proxy->password))
                );
            }

            $http_options = array_merge($http_options, $proxy_options);
        }

        // Создать контекст и инициализировать POST запрос
        $context = stream_context_create(array('http' => $http_options));

        //Послать запрос
        $response = @file_get_contents($this->url, false, $context);

        $try = 0;
        while ($response === false && $try < 3) {
            // не получен ответ от СМС шлюза
            // пытаемся отправить еще несколько раз
            sleep(2);
            $response = @file_get_contents($this->url, false, $context);
            $try++;
        }

        if ($response === false) {
            $this->information = 'The server is not responding';
            $this->status = 1;
            log_message('error','MarketSMS lib The server is not responding' );
            return FALSE;
        }

        // Обработка ответа
        $XMLResp = new SimpleXMLElement($response);
        log_message('error','MarketSMS lib XMLResp =' . $XMLResp->asXML());
        $this->information = $XMLResp->information;

        if ($XMLResp->information['code'][0] == 0) {
            $this->status = 0;
            return TRUE;
        }

        $this->status = $XMLResp->information['code'][0];

        return FALSE;
    }

}

?>
