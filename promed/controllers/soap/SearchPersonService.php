<?php

/**
 * Class SearchPersonService
 * soap сервис поиска пациентов
 */

class SearchPersonService extends swController
{
    var $LinkedWDSL = "SearchPersonService.wsdl";
    var $NeedCheckLogin = false;

    /**
     * Запуск SOAP
     */
    function index(){

        global $_SOAP_Server;

        if ( strpos($_SERVER['REQUEST_URI'], '?wsdl') ) {
            $this->wsdl();
        } else {
			ini_set("soap.wsdl_cache_enabled", 0);
            $xml = file_get_contents("php://input");
            libxml_use_internal_errors(true);

            require_once(APPPATH . 'libraries/DebugSoapServer.php');
            $_SOAP_Server = new DebugSoapServer(
                WSDL_PATH . $this->LinkedWDSL,
                array('uri' => 'urn:NAMESPACEOFWEBSERVICE', 'encoding' => SOAP_ENCODED)
            );
            $_SOAP_Server->setClass('SearchPersonService');
            ob_start();
            $_SOAP_Server->handle();
            /*$debug = $_SOAP_Server->getAllDebugValues();
            soap_log_message(
                'info',
                "Время выполнения: ".$debug['CallDuration']."\r\n".
                "Запрос: \n".$debug['RequestString']."\r\n".
                "Ответ: \r\n".$debug['ResponseString']."\r\n"
            );*/
        }
    }

    /**
     * Выдает сам WSDL
     */
    function wsdl()
    {
        header('Content-type: text/xml; charset=utf-8');
        echo file_get_contents(WSDL_PATH . $this->LinkedWDSL);
    }

    /**
     * @param $message
     * @return SoapFault
     * Поиск пациента
     */
    public function searchPersonServices($message){

        $this->load->database();
        $this->load->model('SearchPersonService_model');

        try {
            $resp = $this->SearchPersonService_model->SearchPersonService( $message->searchRequestServicesWrap->patientSearchParams);

            return $resp;
        } catch (Exception $e) {
            return new SoapFault("Server", $e->getMessage());
        }

    }

}