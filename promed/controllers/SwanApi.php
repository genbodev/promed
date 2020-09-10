<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * SwanApi - контроллер для работы с swan-api
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2015 Swan Ltd.
 * @author			Tokarev Sergey (tokarev@swan.perm.ru)
 * @version			30.06.2016
 *
 */

class SwanApi extends swController {

	/**
	 * Метод-конструктор
	 */
	public function __construct() {
		parent::__construct();
		/*
		оставлю пока здесь, вдруг понадобится подключение к базе
		$this->load->database();
		$this->load->model( 'TNC_model', 'dbmodel' );
		*/
	}
	
	/**
	* Метод определения адреса апи
	*
	*/
	protected function _getSwanApi_url(){
		
		$region = $_SESSION['region']['nick'] ;
		if ($this->config->item( 'IS_DEBUG' ) )
		{
		    if($region == 'ufa') {
                $apiUrl = '192.168.37.24:8380';
            } else {
                $apiUrl = '192.168.37.24:8080';
            }
			return $apiUrl.'/swan-api/rest-api';
		}
		
		switch ( $region ) {
			case 'perm':
				$apiUrl = '192.168.37.24:8080';
				break ;
			case 'ufa':
				$apiUrl = '10.62.16.11:8180';
				break ;
			default:
				$apiUrl = '192.168.37.24:8080';
		}
		
		return $apiUrl.'/swan-api/rest-api';
	}
	
	/**
	* Метод логина в апи
	*
	*/
	protected function _loginSwanApi(){
		
		$params = array(
			'user' => $_SESSION['login'],
			'pass' => str_replace("{MD5}", "", $_SESSION['pass']),
			'passType' => 'md5'
		);

        $request = $this->_doRequest($params, 'login', 'GET');
		if(isset($request["responseData"]["session"])){
			$session_id = $request["responseData"]["session"];
		
			return($session_id);
		};
		
		return false;
	}
	/*
	 * Бронирование бирки операционной
	 */
	public function bookOperationTable() {
        $key = '/timetable/TimetableResource/bookResource'; //200897 - коронараграфия
        $data = $_POST;

        $params = array(
            'person.id' => $data['person_id'],
            'resource.id' => $data['resource_id'],
            'uslugaComplex.id' => $data['uslugacomplex_id'],
            'lpu.id' => $data['lpu_id'],
            'diag.id' => $data['diag_id'],
            'medStafffact.id' => $data['medstafffact_id'],
            'lpuSection.id' => $data['lpusection_id'],
            'lpuBuilding.id' => $data['lpubuilding_id'],
            'medService.id' => $data['medservice_id'],
            'timetable.begdt' => $data['timetable_begdt'],
            'timetable.time' => 90,
        );

        $session_id = ($key === 'login')? '' : ( '?session-id='.$this->_loginSwanApi() );

        $requestUrl = $this->_getSwanApi_url() . '/' . $key . $session_id;

        $getParams = '';
        foreach ($params as $k => $v) {	$getParams .= '&'.$k.'='.$v; }

        if($key === 'login') {
            $requestUrl .='?'.substr($getParams,1);
        }
        else {
            $requestUrl .= $getParams;
        }

        $ch = curl_init() ;
        curl_setopt( $ch , CURLOPT_URL , $requestUrl ) ;
        curl_setopt( $ch , CURLOPT_RETURNTRANSFER , 1 ) ;
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt( $ch , CURLOPT_CUSTOMREQUEST , 'POST' ) ;
        session_write_close() ;

        $fp = curl_exec($ch);
        session_start() ;

        curl_close( $ch ) ;

        if ( $fp ) {
            $req =  json_decode( $fp , TRUE ) ;
            if(!empty($req['responseData']) && $req['success'] == true ) {
                echo json_encode(array('success' => true));
            } else {
                echo json_encode(array('success' => false));
            }
            if(!empty($params['cmpcallcard.id']) && !empty($params['lpu.id'])) {
                $cmpcallcard_params = array(
                    'CmpCallCard_id' => $params['cmpcallcard.id'],
                    'Lpu_hid' => $params['lpu.id']
                );
                $this->swUpdate('CmpCallCard', $cmpcallcard_params, false);
            }
            return;
        } else {
            throw new Exception( "Error loading '" . $requestUrl ) ;
        }





    }
	/*
	 * Получения списка свободных операционных
	 */
    public function getEmergencyLpuHospitalization() {
        $key = '/timetable/TimetableResource/freeTimeRes'; //200897 - коронараграфия

        $params = array(
            'usluga.code' => 'A06.10.006',
        );

        $req = $this->_doRequest($params, $key, 'GET');

        if(!empty($req['responseData'])) {
            for($i=0;$i<sizeof($req['responseData']);$i++) {
               $req['responseData'][$i]['id'] = $i;
            }
            echo json_encode($req);
            return;
        }
    }
	/**
	* Метод сохранения человека через апи
	*
	*/
	public function savePerson($data){
		
		//$session_id = $this->_loginSwanApi($data);
		
		$params = array(			
			//'session-id' => $session_id,
			
			'surname' => $data["Person_SurName"],
			'firname' => $data["Person_FirName"],
			'secname' => $data["Person_SecName"],				
			'birthday' => $data["Person_BirthDay"],
			'snils' => $data["Person_SNILS"],
			'sex.id' => $data["PersonSex_id"],
			/* ХЗ(адрес регистрации, код КЛАДР) 
			'uaddress.kladr' => $data["Person_SurName"],*/
			'uaddress.region.id' => $data["UKLRGN_id"],
			'uaddress.subregion.id' => $data["UKLSubRGN_id"],
			'uaddress.city.id' => $data["UKLCity_id"],
			'uaddress.town.id' => $data["UKLTown_id"],
			'uaddress.street.id' => $data["UKLStreet_id"],
			'uaddress.zip' => $data["UAddress_Zip"],
			'uaddress.house' => $data["UAddress_House"],
			'uaddress.corpus' => $data["UAddress_Corpus"],
			'uaddress.flat' => $data["UAddress_Flat"],
			/* ХЗ (адрес проживания, код КЛАДР)
			'paddress.kladr' => $data["Person_SurName"],*/
			'paddress.region.id' => $data["PKLRGN_id"],
			'paddress.subregion.id' => $data["PKLSubRGN_id"],
			'paddress.city.id' => $data["PKLCity_id"],
			'paddress.town.id' => $data["PKLTown_id"],
			'paddress.street.id' => $data["PKLStreet_id"],
			'paddress.zip' => $data["PAddress_Zip"],
			'paddress.house' => $data["PAddress_House"],
			'paddress.corpus' => $data["PAddress_Corpus"],
			'paddress.flat' => $data["PAddress_Flat"],
			'polis.series' => $data["Polis_Ser"],
			'polis.number' => $data["Polis_Num"],
			'polis.smo.id' => $data["OrgSMO_id"],
			'polis.polisType.id' => $data["PolisType_id"],
			'polis.begDate' => $data["Polis_begDate"]?$data["Polis_begDate"]:null,
			'polis.endDate' => $data["Polis_endDate"]?$data["Polis_endDate"]:null,
			'document.series' => $data["Document_Ser"],				
			'document.number' => $data["Document_Num"],
			'document.documentType.id' => $data["DocumentType_id"],
			'document.begDate' => $data["Document_begDate"]?$data["Document_begDate"]:null,
			/* ХЗ(дата окончания действия документа)
			'document.endDate  ' => $data["Person_SurName"]*/
			/* ХЗ(адрес рождения, код КЛАДР)
			'baddress.kladr  ' => $data["Person_SurName"]*/
			'baddress.region.id' => $data["BKLRGN_id"],
			'baddress.subregion.id' =>  $data["BKLSubRGN_id"],
			'baddress.city.id' =>  $data["BKLCity_id"],
			'baddress.town.id' =>  $data["BKLTown_id"],
			'baddress.street.id' =>  $data["BKLStreet_id"],
			'baddress.zip' =>  $data["BAddress_Zip"],
			'baddress.house' =>  $data["BAddress_House"],
			'baddress.corpus' =>  $data["BAddress_Corpus"],
			'baddress.flat' =>  $data["BAddress_Flat"],			
			'document.citizenship.id' =>  $data["PersonNationality_id"],
			'polis.polisFormType.id' =>  $data["PolisFormType_id"],
			'polis.omsSprTerr.id' =>  $data["OMSSprTerr_id"],
			/* ХЗ(адрес рождения, код КЛАДР)
			'smo.id' =>  $data["BAddress_Flat"],*/		
			'socStatus.id' =>  $data["SocStatus_id"]
		);
		
		//var_dump($params); exit;
		if(empty($params['polis.endDate']) || $params['polis.endDate']>=$params['polis.begDate']){
			if($data['mode'] == 'edit'){
				$params['id'] = $data["Person_id"];
				$request = $this->_doRequest($params, 'person/PersonState', 'PUT');

			}
			else{			
				$request = $this->_doRequest($params, 'person/PersonState', 'POST');
			}
		} else {
			$request = false;
		}
		
		return $request;
		//throw new Exception( "Ошибка api запроса!" ) ;
		//$this->createError('','Ошибка запроса api запроса!');
		
	}
	
	/**
	* Метод отправки запроса 
	* params - параметры запроса (array)
	* key - метод в строке запроса (string), пример - login или evn/EvnPS
	* method - HTTP метод (string), пример - POST GET PUT DELETE
	*
	*/
	protected function _doRequest($params, $key, $method = "GET"){
		
		$session_id = ($key === 'login')? '' : ( '?session-id='.$this->_loginSwanApi() );

		$requestUrl = $this->_getSwanApi_url() . '/' . $key . $session_id;

        if($method == 'GET')
		{
			$getParams = '';
			foreach ($params as $k => $v) {	$getParams .= '&'.$k.'='.$v; }
			
			if($key === 'login') {
				$requestUrl .='?'.substr($getParams,1);
			}
			else {
				$requestUrl .= $getParams;
			}
		}

		
		$ch = curl_init() ;
		curl_setopt( $ch , CURLOPT_URL , $requestUrl ) ;
		curl_setopt( $ch , CURLOPT_RETURNTRANSFER , 1 ) ;		
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
		
		if($method != 'GET'){
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt( $ch , CURLOPT_CUSTOMREQUEST , $method ) ;

		}
		
		if (defined('USE_HTTP_PROXY') && USE_HTTP_PROXY) {
			curl_setopt($ch, CURLOPT_PROXYUSERPWD, '' . ':' . '');
			curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_NTLM);
			curl_setopt($ch, CURLOPT_PROXY, '192.168.36.31:3128');
		}

		session_write_close() ;
		
		$fp = curl_exec($ch);
		session_start() ;

		curl_close( $ch ) ;
		
		if ( $fp ) {
			return json_decode( $fp , TRUE ) ;
		} else {
			throw new Exception( "Error loading '" . $requestUrl ) ;
		}
	}
}

?>