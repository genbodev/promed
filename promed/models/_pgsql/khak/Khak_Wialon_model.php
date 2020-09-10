<?php
/**
 * Khak_Wialon_model - модель для работы с Wialon
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2016 Swan Ltd.
 */

require_once(APPPATH.'models/_pgsql/Wialon_model.php');

class Khak_Wialon_model extends Wialon_model {
    /**
     * @var string Хост с изображениями
     */
    //protected $_image_host = 'http://wialon.03.perm.ru:8022';
    protected $_image_host = WIALON_IMAGE_HOST;

    /**
     * @var stting Wialon API URL
     */
    //protected $_api_url = 'http://wialonweb.mis30.local/wialon/ajax.html';
    protected $_api_url = WIALON_API_URL;
    /**
     * @var string Ключ сессии
     */
    protected $_session_key = 'sid';

    /**
     * Конструктор
     */
    function __construct()
    {
        parent::__construct();
    }

    /**
     * Авторизация
     *
     * @return output JSON
     */
    public function login($credentials = array()){

        // если авторизация через выбранную службу, а не через выполнившего вход пользователя СМП
        if ($credentials) {
            // переопределяем учетные данные
            $this->_user = $credentials['wialon_login'];
            $this->_password = $credentials['wialon_passwd'];
            $this->_token = $credentials['wialon_token'];
        }

        if(!$this->_token) return false;
        $data = array(
            'svc' => 'token/login',
            'params' => json_encode( array(
                //'user' => $this->_user,
                //'password' => $this->_password
                'token' => $this->_token
                //'token' => '3646e3d33894e4b8e370d13ddd327dbfF54FA5B5F01A8636994D8A2BA30526A3CD35F2D8'
                //'token' => '3646e3d33894e4b8e370d13ddd327dbf1339EA4EF62269D0246100B7DA0B60049180CE38'
            ) )
        );

        /*
        запрос на регистрацию нового токена
        $data = array(
            'svc' => 'token/update',
            'params' => json_encode( array(
                'callMode' =>'create',
                'app' => 'РМИАС',
                'userId' => '41',
                'at' => 0,
                'dur' => 0,
                'fl' => 0x200,
                'p' => "{}",
                'items' => array()
            ) )
        );*/
        $result = json_decode( $this->_httpQuery( $data ) );
        //var_dump($result);exit;
        if (empty($result) || array_key_exists( 'error', $result ) ) {
            return false;
        } else {
            $_SESSION['wialon'][$this->_session_key] = (string) $result->eid;
            return true;
        }
    }

    /**
     * Проверка авторизации в Виалоне
     * метод является копией контроллера perm_Wialon
     * @todo Вынести проверку авторизации в Виалоне из контроллеров в модель
     */
    public function checkAuth(){
        $auth = false;
        if ( isset( $_SESSION['wialon']['user'] ) && isset( $_SESSION['wialon']['password'] ) ) {
            $this->_user = $_SESSION['wialon']['user'];
            $this->_password = $_SESSION['wialon']['password'];
            if ( $this->login() ) {
                $auth = true;
            } else {
                unset( $_SESSION['wialon']['user'], $_SESSION['wialon']['password'] );
            }
        }

        // Не удалось авторизоваться по данным из сессии?
        if ( !$auth ) {

            // Флаг авторизации через прокси
            $auth_through_proxy = false;

            // Получаем данные для авторизации
            // Если включено проксирование, то получаем данные из удаленного Промеда
            if ( $this->config->load('proxy_queries','proxy_queries',true) ) {
                // Прокисрование разрешено? А значит и настроено
                if ( $this->config->config['proxy_queries']['enable'] ) {
                    $auth_through_proxy = true;

                    $proxy =& load_class( 'swProxyQueries', 'libraries', null );
                    $proxy->init( $this->config->config['proxy_queries']['settings'] );
                    $proxy->setCookies( $proxy->restoreCookies() );
                    $result = $proxy->forward( '/?c=Wialon&m=retriveAccessData', false );
                    $proxy->rememberCookies( $result['cookies'] );

                    $headers = explode( "\n", $result['headers'] );
                    foreach( $headers as $header ) if ( strpos( $header, ":" ) !== false ) {
                        list( $h, $v ) = explode( ':', $header );
                        if ( $h == 'Content-Encoding' ) {
                            if ( trim( $v ) == 'gzip' ) {
                                $result['body'] = $proxy->gunzip( $result['body'] );
                            }
                        }
                    }

                    $result = (array)json_decode( $result['body'] );
                    if ( $result ) {
                        $this->_user = $result['MedService_WialonLogin'];
                        $this->_password = $result['MedService_WialonPasswd'];
                    }
                }
            }

            // Если данные для авторизации не были получены через прокси, сделаем запрос к базе
            if ( !$auth_through_proxy ) {
                $result = $this->retrieveAccessData( $_SESSION );
                $this->_user = $result['MedService_WialonLogin'];
                $this->_password = $result['MedService_WialonPasswd'];
                $this->_token = $result['MedService_WialonToken'];
            }

            if ( $this->login() ) {
                $_SESSION['wialon']['user'] = $this->_user;
                $_SESSION['wialon']['password'] = $this->_password;
                $_SESSION['wialon']['authorized'] = true;
                $_SESSION['wialon']['WialonToken'] = $result['MedService_WialonToken'];

            } else {
                $_SESSION['wialon']['authorized'] = false;
                if ($this->exit_on_auth_err) {
                    return array( 'error' => 'Wialon: не удалось авторизоваться.' );
                }
            }
        } else {
            $_SESSION['wialon']['authorized'] = true;
        }
    }

    /**
     * Получение отчета 'СВОДНЫЙ ОТЧЕТ STD пробег' за промежуток времени
     * @param type $data
     */
    public function getSummaryReportStdMileage($param) {
        if( !$param['tarnsportID'] || !$param['GoTime'] || !$param['EndTime'] ) return false;
        $name = 'Пробег график'; // название отчета, который мы хотим получить из wialon
        /*
        $reportParam = array(
            'name' => $name ,
            'type' => 'avl_unit'
        );
        // получим ID шаблона
        $template_id = $this->_getSummaryReportStdMileagetTemplateId($reportParam);
         */
        $template_id = 1; // ID отчета 'Пробег график'
        $resource_id = $this->getResourceId();
        if(!$template_id || !$resource_id) return false;

        $report_result = $this->getReport( array(
            'reportResourceId' => $resource_id ,
            'reportTemplateId' => $template_id ,
            'reportObjectId' => $param['tarnsportID'],
            'reportObjectSecId' => 0 ,
            'interval' => array(
                'from' => $param['GoTime']->getTimestamp() ,
                'to' => $param['EndTime']->getTimestamp() ,
                'flags' => 0
            ),
            "tzOffset"=>$param['GoTime']->getOffset(),
            "lang"=>"ru"
        ) );

        if(!empty($report_result['error'])){
            return false;
        }else{
            $consolidatedReport = array();
            $consolidatedReport['km'] = '';
            if($report_result['reportResult']['stats']){
                foreach ($report_result['reportResult']['stats'] as $value) {
                    if($value[0] == "Пробег по всем сообщениям") {$consolidatedReport['km'] = $value[1];}
                    $consolidatedReport[$value[0]] = $value[1];
                }
            }
            return $consolidatedReport;
        }
    }

    /**
     * возвращает пройденое расстояние из отчета 'Пробег график'
     */
    public function getTheDistanceTraveled($param){
        //получим нужный нам отчет
        $report = $this->getSummaryReportStdMileage($param);
        // возьмем необходимый параметр - пройденное расстояние
        if( !empty($report['km']) && $report['km'] ){
            return $report['km'];
        }else{
            return false;
        }
    }
}