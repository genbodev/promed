<?php

/**
 * Базовый класс для отравки SMS
 */
abstract class SMS {

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
    private $url = null;

    /**
     * Отправитель
     *
     * @var string
     */
    protected $sender = null;

    /**
     * Конструктор SMS для отправки
     *
     * @param array $login Логин для сервиса
     * @param array $password Пароль для сервиса
     * @param string $sender Идентификатор отправителя
     */
    public function __construct($login, $password, $sender = NULL) {
        $this->login = $login;
        $this->password = $password;
        
        if ( isset($sender) ) {
            $this->sender = $sender;
        }
    }

    /**
     * Отправка сообщения
     *
     * @param array $message Сообщение
     * @return int Код ошибки или 0
     */
    abstract function send($message);
    
    /**
     * Транслитерация текста из русского в латиницу
     */
    function rus2translit($string)
    {
        $converter = array(
            'а' => 'a',   'б' => 'b',   'в' => 'v',
            'г' => 'g',   'д' => 'd',   'е' => 'e',
            'ё' => 'jo',   'ж' => 'zh',  'з' => 'z',
            'и' => 'i',   'й' => 'y',   'к' => 'k',
            'л' => 'l',   'м' => 'm',   'н' => 'n',
            'о' => 'o',   'п' => 'p',   'р' => 'r',
            'с' => 's',   'т' => 't',   'у' => 'u',
            'ф' => 'f',   'х' => 'kh',   'ц' => 'c',
            'ч' => 'ch',  'ш' => 'sh',  'щ' => 'sch',
            'ь' => "'",  'ы' => 'y',   'ъ' => "'",
            'э' => 'e',   'ю' => 'ju',  'я' => 'ja',
     
            'А' => 'A',   'Б' => 'B',   'В' => 'V',
            'Г' => 'G',   'Д' => 'D',   'Е' => 'E',
            'Ё' => 'Jo',   'Ж' => 'Zh',  'З' => 'Z',
            'И' => 'I',   'Й' => 'Y',   'К' => 'K',
            'Л' => 'L',   'М' => 'M',   'Н' => 'N',
            'О' => 'O',   'П' => 'P',   'Р' => 'R',
            'С' => 'S',   'Т' => 'T',   'У' => 'U',
            'Ф' => 'F',   'Х' => 'Kh',   'Ц' => 'C',
            'Ч' => 'Ch',  'Ш' => 'Sh',  'Щ' => 'Sch',
            'Ь' => "'",  'Ы' => 'Y',   'Ъ' => "'",
            'Э' => 'E',   'Ю' => 'Ju',  'Я' => 'Ja',
        );
        return strtr($string, $converter);
    }
}

?>
