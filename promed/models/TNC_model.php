<?php

class TNC_model extends swModel {
	/**
	 * @var string URL получение списка транспортных средств
	 */
	protected $_getTransport_url = 'http://gostrans.bashkortostan.ru:47227/vms-ws/rest/TransportUIWS/getList';

	protected $_getLastTransportCoords_url = 'http://gostrans.bashkortostan.ru:47227/vms-ws/rest/NDDataWS/getLastCachedData';

	protected $_getTransportGroupList_url = 'http://gostrans.bashkortostan.ru:47227/vms-ws/rest/TransportGroupWS/getList';

	/**protected $_username = 'Svan';

	protected $_password = '6Afx/PgtEy+bsBjKZzihnw==';**/
	
	//protected $_username = 'RMIAS SSMP Ufa CentrPod';
	//protected $_username = 'RMIAS Oktyabrskiy';/* Для показа . После показа вернуть.*/
	//protected $_password = '6Afx/PgtEy+bsBjKZzihnw=='; //'e807f1fcf82d132f9bb018ca6738a19f';//'1234567890';*/
	//protected $_password = 'NTo/hHg20g2WfXU6/ojMtw=='; /* Для показа . После показа вернуть.*/
	protected $_username;
	
	protected $_password;
	
	/**
	 *  конструктор	 *
	 */
	function __construct()
	{
		parent::__construct();

		//убрал в контроллер
		//$this->TNCloginPass();
	}

	/**
	 * Авторизация по выбранному подразделению
	 */
	public function authByDepartment($data){

		$auth = false;
		$response = $this->getTNCCredentialsByLpuDepartment($data);

		if (count($response) > 0) {

			if (isset($response[0]['MedService_WialonLogin'])) {

				$wialon_login = trim($response[0]['MedService_WialonLogin']);
				$wialon_passwd = trim($response[0]['MedService_WialonPasswd']);

				if (!empty($wialon_login)) {

					$credentials = array(

						'wialon_login' => $wialon_login,
						'wialon_passwd' => $wialon_passwd
					);

					$this->TNCloginPass($credentials);
					$auth = true;
				}
			}
		}

		return $auth;
	}

	/**
	* Метод сохранения связи бригады и транспорта ТНЦ
	*/
	public function mergeEmergencyTeam($data) {
		
		$rules = array(
			array( 'field' => 'EmergencyTeam_id', 'label' => 'Идентификатор подстанции', 'rules' => 'required', 'type' => 'int'),
			array( 'field' => 'TNCTransport_id', 'label' => 'Идентификатор транспорта ТНЦ', 'rules' => 'required', 'type' => 'int'),
			array( 'field' => 'EmergencyTeamTNCRel_id', 'label' => 'Идентификатор связи бригады и транспорта', 'rules' => '', 'type' => 'int', 'default'=>null),
			array( 'field' => 'pmUser_id', 'label'=>'Идентификатор пользователя', 'rules' =>'required', 'type' => 'id'),
		);
		
		$queryParams = $this->_checkInputData($rules , $data, $err);
		if (!empty($err)) {
			return $err;
		}
		
		//
		// Проверяем наличие у указанной бригады предыдущих связей с транспортом
		//
		
		if (empty($queryParams['EmergencyTeamTNCRel_id'])) {
			$sql = "SELECT EmergencyTeamTNCRel_id FROM v_EmergencyTeamTNCRel with(nolock) WHERE EmergencyTeam_id=:EmergencyTeam_id";
			
			$query = $this->db->query( $sql, $queryParams );
			$result = $query->first_row('array');
			$queryParams['EmergencyTeamTNCRel_id'] = !empty($result) && sizeof( $result ) && $result['EmergencyTeamTNCRel_id'] ? $result['EmergencyTeamTNCRel_id'] : null;
		}
		
		$procedure = (empty($queryParams['EmergencyTeamTNCRel_id'])) ? 'p_EmergencyTeamTNCRel_ins'  : 'p_EmergencyTeamTNCRel_upd';
		
		$query = "
			
			DECLARE
				@Res int,
				@ErrCode int,
				@ErrMessage varchar(4000)

			SET @Res = :EmergencyTeamTNCRel_id;

			EXEC $procedure
				@EmergencyTeamTNCRel_id = @Res output,
				@EmergencyTeam_id = :EmergencyTeam_id,
				@TNCTransport_id = :TNCTransport_id,

				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			SELECT @Res as EmergencyTeamTNCRel_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		
		return $this->queryResult($query, $queryParams);
	}

	/**
	 * Метод получения списка транспортных средств ТНЦ
	 */
	public function getTransportGroupList($output = true) {
		$result = array();

		try {
			$response_string = $this->_getGroupListRequest() ;
			$result = $this->_processGetTransporGroupListRequestResponse( $response_string ) ;
		} catch ( Exception $e ) {
			$result = $this->createError( NULL , $e->getMessage() ) ;
		}

		return $result;
	}

	/**
	 * Метод выполнения запроса на получения списка групп транспортных средств к ТНЦ
	 * @return type
	 * @throws Exception
	 */
	protected function _getGroupListRequest() {

		$data = array(
			array(
				'userName' => $this->_username ,
				'password' => $this->_password ,
			) ,
			array(
				'beginIndex' => 0 ,
				'count' => 2147483647 ,
				'loadDeletedItems' => 0
			)
		) ;

		$data_string = json_encode( $data ) ;

		$ch = curl_init() ;
		curl_setopt( $ch , CURLOPT_URL , $this->_getTransportGroupList_url ) ;
		curl_setopt( $ch , CURLOPT_RETURNTRANSFER , 1 ) ;
		curl_setopt( $ch , CURLOPT_CUSTOMREQUEST , "POST" ) ;
		curl_setopt( $ch , CURLOPT_POSTFIELDS , $data_string ) ;
		curl_setopt( $ch , CURLOPT_HTTPHEADER , array(
			'Accept: */*' ,
			'Accept-Charset: UTF-8,*;q=0.5' ,
			'Accept-Encoding: deflate' ,
			'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.6,en;q=0.4'
		) ) ;

		if ( $this->config->item( 'IS_DEBUG' ) ) {
			curl_setopt( $ch, CURLOPT_TIMEOUT, 15 ); // http request timeout 5 seconds
		}

		if (defined('USE_HTTP_PROXY') && USE_HTTP_PROXY) {
			curl_setopt($ch, CURLOPT_PROXYUSERPWD, '' . ':' . '');
			curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_NTLM);
			curl_setopt($ch, CURLOPT_PROXY, '192.168.36.167:3128');
			//curl_setopt($ch, CURLOPT_PROXY, '192.168.37.18:8080');
		}

		session_write_close() ;

		$fp = curl_exec( $ch ) ;
		session_start() ;
		curl_close( $ch ) ;

		if ( $fp ) {
			return $fp ;
		} else {
			throw new Exception( "Error loading '" . $this->_getTransportGroupList_url/* ."', ".$php_errormsg */ ) ;
		}
	}


	/**
	 * Метод обработки строки-ответа запроса на получение списка ТС
	 * @param type $response_string
	 * @return type
	 */
	protected function _processGetTransporGroupListRequestResponse($response_string) {

		$response = json_decode( $response_string , TRUE ) ;

		if ( $response === NULL || empty( $response[ 'objList' ] ) || !is_array( $response[ 'objList' ] ) ) {
			return $this->createError( NULL , 'Неверный ответ от сервера ТНЦ.' ) ;
		}



		$result = array( ) ;

		foreach ( $response[ 'objList' ] as $group ) {

			if ( empty( $group[ 'id' ] ) || empty( $group[ 'id' ] ) ) {
				return $this->createError( NULL , 'Невозможно получить данные объекта' ) ;
			}

			if (empty( $group[ 'code' ])||empty($group['description'])) {
				continue ;
			}

			$result[ ] = array(
				'id' => $group[ 'id' ] ,
				'code' => $group[ 'code' ] ,
				'description' => $group[ 'description' ] ,
			) ;
		}

		return $result ;
	}

	/**
	 * Метод получения списка транспортных средств ТНЦ
	 */
	public function getTransportList($output = true, $filerIds = null) {

		$result = array();

		try {
			if(!$this->_username || !$this->_password){
				if( !$this->TNCloginPass() ) return false;
			}
			$response_string = $this->_getTransportListRequest($filerIds) ;
			$result = $this->_processGetTransportListRequestResponse( $response_string ) ;

		} catch ( Exception $e ) {
			$result = $this->createError( NULL , $e->getMessage() ) ;
		}

		if ($output) {
			$device_list = array();
			$transport_list = $result;
			//Получаем из списка транспорта список устройств
			foreach ($transport_list as $transport) {
				if (!empty($transport['deviceId'])) {
					$device_list[] = $transport['deviceId'];
				}
			}

			//Получаем данные устройств
			$response_string = $this->_getLastTransportCoordsRequest($device_list);
			$coords_list = $this->_processGetLastTransportCoordsRequestResponse($response_string);

			//Связываем полученные с устройств данные с транспорторм
			$result = $this->_associateCoordsWithTransport($transport_list, $coords_list);

			return $result;
		} else {
			return $result;
		}
	}

	/**
	 * Метод выполнения запроса на получения списка транспортных средств к ТНЦ
	 * @return type
	 * @throws Exception
	 */
	protected function _getTransportListRequest($filerIds = null) {

		$config = array(
			'loadNotDiscardedOnly' => 1 ,
			'applyPrimaryGroupFilter' => 1 ,
			'beginIndex' => 0 ,
			'count' => 2147483647 ,
			'loadDeletedItems' => 0
		);

		if ($filerIds)
			$config['idList'] = $filerIds;

		$data = array(

			array(
				'userName' => $this->_username ,
				'password' => $this->_password ,
			), $config
		);

		$data_string = json_encode( $data ) ;

		$ch = curl_init() ;
		curl_setopt( $ch , CURLOPT_URL , $this->_getTransport_url ) ;
		curl_setopt( $ch , CURLOPT_RETURNTRANSFER , 1 ) ;
		curl_setopt( $ch , CURLOPT_CUSTOMREQUEST , "POST" ) ;
		curl_setopt( $ch , CURLOPT_POSTFIELDS , $data_string ) ;
		curl_setopt( $ch , CURLOPT_HTTPHEADER , array(
			'Accept: */*' ,
			'Accept-Charset: UTF-8,*;q=0.5' ,
			'Accept-Encoding: deflate' ,
			'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.6,en;q=0.4'
		) ) ;

		if ( $this->config->item( 'IS_DEBUG' ) ) {
			curl_setopt( $ch, CURLOPT_TIMEOUT, 15 ); // http request timeout 5 seconds
		}

		if (defined('USE_HTTP_PROXY') && USE_HTTP_PROXY) {
			curl_setopt($ch, CURLOPT_PROXYUSERPWD, '' . ':' . '');
			curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_NTLM);
			curl_setopt($ch, CURLOPT_PROXY, '192.168.36.167:3128');
			//curl_setopt($ch, CURLOPT_PROXY, '192.168.37.18:8080');
		}

		session_write_close() ;
		$fp = curl_exec( $ch ) ;
		session_start() ;

		curl_close( $ch ) ;

		if ( $fp ) {
			return $fp ;
		} else {
			throw new Exception( "Error loading '" . $this->_getTransport_url/* ."', ".$php_errormsg */ ) ;
		}
	}

	/**
	 * Метод обработки строки-ответа запроса на получение списка ТС
	 * @param type $response_string
	 * @return type
	 */
	protected function _processGetTransportListRequestResponse($response_string) {

		$response = json_decode( $response_string , TRUE ) ;

		if ( $response === NULL || empty( $response[ 'objList' ] ) || !is_array( $response[ 'objList' ] ) ) {
			return $this->createError( NULL , 'Неверный ответ от сервера ТНЦ.' ) ;
		}



		$result = array( ) ;

		foreach ( $response[ 'objList' ] as $ts ) {

			if ( empty( $ts[ 'id' ] ) || empty( $ts[ 'garageNum' ] ) ) {
				return $this->createError( NULL , 'Невозможно получить данные объекта' ) ;
			}

			if (empty( $ts[ 'deviceId' ])) {
				continue ;
			}

			$result[ ] = array(
				'id' => $ts[ 'id' ] ,
				'name' => (!empty( $ts[ 'transportTypeDescription' ] ) ? ($ts[ 'transportTypeDescription' ] . ' ') : '') . $ts[ 'garageNum' ] ,
				'deviceId' => $ts[ 'deviceId' ] ,
				'groupDescription' => (!empty( $ts[ 'groupDescription' ] ) ? $ts[ 'groupDescription' ] : '') ,
				'groupCode' => (!empty( $ts[ 'groupCode' ] ) ?  $ts[ 'groupCode' ] : '')
			) ;
		}

		return $result ;

		/**
		 *	На случай возможного появления необходимости отображать или хранить
		 * ещё какие-либо данные, оставлю список параметров и их значений
		 *
		 * 		id: '',
		transportTypeCode: 'ГАЗ 32214',
		transportTypeDescription: 'ГАЗ 32214',
		regNum: 'С283КМ102',
		description: 'Медицина',
		groupCode: '111',
		groupDescription: 'ГБУЗ РБ Белорецкая ЦРКБ',
		garageNum: 'С283КМ102',
		codeM: 'ГАЗ 32214',
		deviceId: 1866503457,
		deviceCode: '7617',
		phone: '9378337721',
		phone2: 'Медицина',
		primaryGroupName: 'ГБУЗ РБ Белорецкая ЦРКБ (ГБУЗ РБ Белорецкая ЦРКБ)',
		transmitDataToTN: 0,
		codevirt: '',
		lastData: 'Jun 10, 2015 4:26:17 PM',
		softVersion: '>REV 07.627.023',
		 *
		 */
	}

	/**
	 * Метод получения списка последних полученных координат для ТС сервиса ТНЦ
	 * @param type $output флаг вывода результата пользователю
	 * @return type
	 */
	public function getLastTransportCoords($output = true, $filerIds = null ) {
		//Получаем список транспорта

		$transport_list = $this->getTransportList(FALSE, $filerIds);
		if( !$transport_list || !is_array($transport_list) ) return false;
		$device_list = array();

		//Получаем из списка транспорта список устройств
		foreach ( $transport_list as $transport) {
			if (!empty($transport['deviceId'])) {
				$device_list[] = $transport['deviceId'];
			}
		}

		//Получаем данные устройств
		$response_string = $this->_getLastTransportCoordsRequest($device_list);

		$coords_list = $this->_processGetLastTransportCoordsRequestResponse($response_string);

		//Связываем полученные с устройств данные с транспорторм
		$result = $this->_associateCoordsWithTransport($transport_list, $coords_list);

		return $result;
	}

	/**
	 * Метод выполнения запроса на получения списка последних координат ТС
	 * @param type $device_list
	 * @return type
	 * @throws Exception
	 */
	protected function _getLastTransportCoordsRequest($device_list=array()) {
		if(!$this->_username && !$this->_password ) {return false;}
		if (  sizeof( $device_list) == 0) {
			return array();
		}
		$data = array(
			array(
				'userName' => $this->_username ,
				'password' => $this->_password ,
			) ,
			array(
				'deviceIdList' => $device_list ,
			)
		) ;

		$data_string = json_encode( $data ) ;

		$ch = curl_init() ;
		curl_setopt( $ch , CURLOPT_URL , $this->_getLastTransportCoords_url ) ;
		curl_setopt( $ch , CURLOPT_RETURNTRANSFER , 1 ) ;
		curl_setopt( $ch , CURLOPT_CUSTOMREQUEST , "POST" ) ;
		curl_setopt( $ch , CURLOPT_POSTFIELDS , $data_string ) ;
		curl_setopt( $ch , CURLOPT_HTTPHEADER , array(
			'Accept: */*' ,
			'Accept-Charset: UTF-8,*;q=0.5' ,
			'Accept-Encoding: deflate' ,
			'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.6,en;q=0.4'
		) ) ;

		if ( $this->config->item( 'IS_DEBUG' ) ) {
			curl_setopt( $ch, CURLOPT_TIMEOUT, 15 ); // http request timeout 5 seconds
		}

		if (defined('USE_HTTP_PROXY') && USE_HTTP_PROXY) {
			curl_setopt($ch, CURLOPT_PROXYUSERPWD, '' . ':' . '');
			curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_NTLM);
			curl_setopt($ch, CURLOPT_PROXY, '192.168.36.167:3128');
			//curl_setopt($ch, CURLOPT_PROXY, '192.168.37.18:8080');
		}

		session_write_close() ;
		$fp = curl_exec( $ch ) ;
		session_start() ;

		curl_close( $ch ) ;

		if ( $fp ) {
			return $fp ;
		} else {
			throw new Exception( "Error loading '" . $this->_getTransport_url/* ."', ".$php_errormsg */ ) ;
		}

	}

	/**
	 * Метод обработки строки-ответа запроса на получение списка последних координат ТС
	 * @param type $response_string
	 * @return type
	 */
	protected function _processGetLastTransportCoordsRequestResponse($response_string) {
		if (!is_array($response_string)) {
			$response = json_decode( $response_string , TRUE ) ;
		} else {
			$response = $response_string;
		}
		if ( $response === NULL || !is_array( $response ) ) {
			return $this->createError( NULL , 'Неверный ответ от сервера ТНЦ.' ) ;
		}

		return $response;
	}

	/**
	 * Метод объединения данных о координатах с данными о транспортных средствах
	 * @param type $transport_list
	 * @param type $coords_list
	 * @return boolean
	 */
	protected function _associateCoordsWithTransport($transport_list = array(), $coords_list = array()) {

		if (!is_array( $coords_list ) || !is_array( $transport_list)) {
			return false;
		}

		$result = array();

		foreach ( $coords_list as $coords ) {
			foreach ( $transport_list as $transport ) {

				if (isset($coords['deviceId']) && isset($transport['deviceId'])) {

					if ($coords['deviceId'] == $transport['deviceId']) {
						$result[] = array_merge($transport, array(
							'lat' => (!empty($coords['lat'])) ? $coords['lat'] : NULL,
							'lon' => (!empty($coords['lon'])) ? $coords['lon'] : NULL,
							'direction' => (!empty($coords['direction'])) ? $coords['direction'] : NULL,
						));
					}
				}
			}
		}

		return $result;
	}
	
	/**
	 * Возвращает данные для аутентификации
	 *
	 * @return array or false
	 */
	public function retrieveAccessData(){

		$data = $_SESSION;
		$filter = "";
		$params = array();

		if (!empty($data['medpersonal_id']) && $data['medpersonal_id']){

			$filter = "msf.MedPersonal_id=:MedPersonal_id";
			$params['MedPersonal_id'] = $data['medpersonal_id'];

		} elseif (!empty ($data['MedStaffFact']) && $data['MedStaffFact'][0]) {

			$filter = "msf.MedStaffFact_id=:MedStaffFact_id";
			$params['MedStaffFact_id'] = $data['MedStaffFact'][0];
		}
		
		if($filter){
			$filter .= " AND (mst.MedServiceType_Code in (18,19,53) )
				AND ast.ApiServiceType_Code = 2";

			if (isset ($data['CurMedService_id'])){
				$filter .= " AND ms.MedService_id = :CurMedService_id";
				$params['CurMedService_id'] = ( !empty($data['CurMedService_id']) ) ? $data['CurMedService_id'] : null;
			}
			$filter .= " ORDER BY ms.MedService_id DESC";

			$sql = "
				SELECT TOP 1
					ms.MedService_id,
					ms.MedService_WialonLogin,
					ms.MedService_WialonPasswd,
					ms.MedService_WialonToken
				FROM
					v_MedService ms with(nolock)
					INNER JOIN v_MedServiceMedPersonal msmp with(nolock) ON( msmp.MedService_id=ms.MedService_id )
					INNER JOIN v_MedStaffFact msf with(nolock) ON( msf.MedPersonal_id=msmp.MedPersonal_id )
					left JOIN v_MedServiceType mst with(nolock) ON( ms.MedServiceType_id=mst.MedServiceType_id )
					LEFT JOIN v_ApiServiceType ast with(nolock) ON(ms.ApiServiceType_id=ast.ApiServiceType_id)
				WHERE
					".$filter."
			";

			$query = $this->db->query( $sql, $params);
			if ( isset($query) && is_object( $query ) ) {
				return $query->row_array();
			} 
		}
		return false;
	}
	
	/**
	 * Устанавливает $this->_username и $this->_password для аутентификации TNC
	 *
	 * @return true or false
	 */
	public function TNCloginPass($credentials = array()) {

		// если авторизация через выбранную службу, а не через выполнившего вход пользователя СМП
		if ($credentials) {

			// переопределяем учетные данные
			$this->_username = $credentials['wialon_login'];
			$this->_password = $credentials['wialon_passwd'];

		} else {

			if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}
			//			$rules = array(
			//				array( 'field' => 'LpuBuilding_id', 'label' => 'Идентификатор подразделения', 'rules' => '', 'type' => 'int')
			//			);
			//			$data = $_POST;
			//			$queryParams = $this->_checkInputData($rules, $data, $err);
			//			if($queryParams['LpuBuilding_id']){
			//				/*
			//                 * в структуре МО теперь есть возможность менять логин/пароль для каждой подстанции
			//                 * а в паспорте МО привязать автомобиль,
			//                 * выпадающий список автомобилей должен быть в соответсвии с настройками
			//                 * тут мы и получим логин/пароль запрошенной станции
			//                */
			//				$result = $this->retrieveAccessData($queryParams);
			//				if (is_array($result)) {
			//					$this->_username = array_key_exists('MedService_WialonLogin', $result)?$result['MedService_WialonLogin']:null;
			//					$this->_password = array_key_exists('MedService_WialonPasswd', $result)?$result['MedService_WialonPasswd']:null;
			//					return true;
			//				}
			//			}else
			if( empty($_SESSION['TNC']) ){
				$result = $this->retrieveAccessData();
				if (is_array($result)) {
					$this->_username = array_key_exists('MedService_WialonLogin', $result)?$result['MedService_WialonLogin']:null;
					$this->_password = array_key_exists('MedService_WialonPasswd', $result)?$result['MedService_WialonPasswd']:null;
				}
				if($this->_password && $this->_username){
					$_SESSION['TNC'] = array();
					$_SESSION['TNC']['username'] = $this->_username;
					$_SESSION['TNC']['password'] = $this->_password;
					return true;
				}
			} elseif ( isset($_SESSION['TNC']['username']) && $_SESSION['TNC']['username'] && isset($_SESSION['TNC']['password']) && $_SESSION['TNC']['password']) {
				$this->_username = $_SESSION['TNC']['username'];
				$this->_password = $_SESSION['TNC']['password'];
				return true;
			}
			$this->_username = null;
			$this->_password = null;

			return false;
		}
	}

	/**
	 * @return array Возвращает логин и пароль (TNC) выбранного подразделения МО
	 */
	public function getTNCCredentialsByLpuDepartment($data){

		$params = array(
			'Lpu_id' => $data['Lpu_id']
		);

		$level='lpu';
		$filters = '';

		if (!empty($data['Sub_SysNick']))
		{
			$level = strtolower($data['Sub_SysNick']);

			switch($level){
				case 'lpubuilding';

					$filters .= ' and MS.LpuBuilding_id = :LpuBuilding_id';
					$params['LpuBuilding_id'] = $data['LpuDepartment_id'];

					break;
				case 'lpuunittype';

					$filters .= ' and MS.LpuUnitType_id = :LpuUnitType_id';
					$params['LpuUnitType_id'] = $data['LpuDepartment_id'];

					break;
				case 'lpuunit';

					$filters .= ' and MS.LpuUnit_id = :LpuUnit_id';
					$params['LpuUnit_id'] = $data['LpuDepartment_id'];

					break;
				case 'lpusection';

					$filters .= ' and MS.LpuSection_id = :LpuSection_id';
					$params['LpuSection_id'] = $data['LpuDepartment_id'];
					break;
			}
		}

		$query = "
			SELECT TOP 1
				MS.MedService_id,
				MS.MedService_Name,
				-- поля называются именно так, хотя сервис называется по другому, WTF!?
				MS.MedService_WialonLogin,
				MS.MedService_WialonPasswd
			FROM
				v_MedService MS with (NOLOCK)
				left join v_MedServiceType MST with (NOLOCK) on MS.MedServiceType_id = MST.MedServiceType_id
			where
					MS.Lpu_id = :Lpu_id
					and (MST.MedServiceType_Code in (18,19,53))
					-- только TNC
					and MS.ApiServiceType_id = 4
					and MS.MedService_WialonLogin IS NOT NULL
				{$filters}
		";

		$result = $this->db->query($query, $params);
		if (is_object($result))
		{
			return $result->result('array');
		}
		return false;
	}
}

?>