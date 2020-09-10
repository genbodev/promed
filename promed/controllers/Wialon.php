<?php defined( 'BASEPATH' ) or die( '404. Script not found.' );

/**
 * @class Wialon
 * 
 * Работа с Wialon API
 * 
 * @author Dyomin Dmitry
 * @since 07.2013
 */
class Wialon extends swController {
	/**
	 * @var Системное имя обязательной таблицы для отчета по ГСМ
	 */
	const GAS_REP_TBL_NAME = 'unit_generic';
	
	/**
	 * @var Название обязательной таблицы для отчета по ГСМ
	 */
	const GAS_REP_TBL_DISPL_NAME = 'Сводка';

	/**
	 * @var Название шаблона для получения отчета по ГСМ
	 */
	const GAS_REPORT_TEMPLATE_NAME = 'Расход топлива для РИАМС';
	
	/**
	 * @var Тип шаблона для получения отчета по ГСМ
	 */
	const GAS_REPORT_TEMPLATE_TYPE = 'avl_unit';
	
	/**
	 * @var Размер иконки объекта
	 */
	const ICON_MAX_BORDER = 32;

	/**
	 * @var bool Флаг авторизации по подразделению
	 */
	public $authByLoggedUser = false;

	/**
	 * @var bool Флаг авторизации по подразделению
	 */
	public $authorized = false;

	/**
	 * @var array Правила
	 */
	public $inputRules = array(
		'mergeEmergencyTeam' => array(
			array(
				'field' => 'EmergencyTeam_id',
				'label' => 'Идентификатор бригады скорой помощи',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'WialonEmergencyTeamId',
				'label' => 'Идентификатор бригады скорой помощи Wialon',
				'rules' => '',
				'type' => 'int'
			)
		),
		'getWialonCredentialsByLpuDepartment' => array(
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор МО',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Sub_SysNick',
				'label' => 'Тип подразделения',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'LpuDepartment_id',
				'label' => 'Идентификатор подразделения ЛПУ',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'LpuBuilding_id',
				'label' => 'Идентификатор здания ЛПУ',
				'rules' => '',
				'type' => 'int'
			)
		),
		'loadEmergencyTeamRelList' => array( ),
		'geocodeCoords' => array(
			array(
				'field' => 'coords', 'label' => 'Координаты для кодировки', 'rules' => 'required',	'type' => 'string'
			)
		),
		'saveEmergencyTeamRel' => array(
			array(
				'field' => 'data',
				'label' => 'Данные для сохранения грида',
				'rules' => 'required',
				'type' => 'string' // на самом деле JSON
			)
		),
		'getUnitIdByEmergencyTeamId' => array(
			array(
				'field' => 'EmergencyTeam_id',
				'label' => 'Идентификатор бригады скорой помощи',
				'rules' => 'required',
				'type' => 'int'
			),
		),
		'getWayBillGasReport' =>array(
			//array( 'field' => 'reportResourceId' , 'label' => 'ID ресурса' , 'rules' => 'required' , 'type' => 'string' ),
			//array( 'field' => 'reportTemplateId' , 'label' => 'ID шаблона' , 'rules' => '' , 'type' => 'string', 'default'=>0  ),
			array( 'field' => 'reportObjectId' , 'label' => 'ID автомобиля' , 'rules' => 'required' , 'type' => 'string' ),
			//array( 'field' => 'reportObjectSecId' , 'label' => 'ID подэлемента' , 'rules' => '' , 'type' => 'string', 'default'=>0 ),
			array( 'field' => 'from' , 'label' => 'начало интервала' , 'rules' => '' , 'type' => 'datetime', 'default'=> ''),
			array( 'field' => 'to' , 'label' => 'окончание интервала' , 'rules' => '' , 'type' => 'datetime', 'default'=> ''),
			//array( 'field' => 'flags' , 'label' => 'флаги интервала' , 'rules' => '' , 'type' => 'string'),
			//array( 'field' => 'reportTemplate' , 'label' => 'JSON шаблона отчета' , 'rules' => '' , 'type' => 'string'),
		),
		'loginTest' => array(
			array( 'field' => 'login' , 'label' => 'user' , 'rules' => '' , 'type' => 'string' ),
			array( 'field' => 'password' , 'label' => 'password' , 'rules' => '' , 'type' => 'string' ),
			array( 'field' => 'token' , 'label' => 'token' , 'rules' => '' , 'type' => 'string' ),
			array( 'field' => 'loginTest' , 'label' => 'nameRegion' , 'rules' => '' , 'type' => 'string' ),
		)
	);

	/**
	 * Конструктор
	 * 
	 * @return void
	 */
	public function __construct($config=array()){
		parent::__construct();

		$this->load->database();
		$this->load->model( 'Wialon_model', 'dbmodel' );

		$this->configure($config);

		$this->authByLoggedUser = isset($_REQUEST['GlonassAuthByDepartment'])
								? ($_REQUEST['GlonassAuthByDepartment'])
									? false
									: true
								: true;

		if (!$this->authByLoggedUser) {

			//сбросим сессию
			$this->logout();

			$data = $this->ProcessInputData('getWialonCredentialsByLpuDepartment', true);
			$result = $this->dbmodel->init(false, $data);

		} else {
			$result = $this->dbmodel->init();
		}

		if (!empty($result) && is_array($result)) {
			if( !filter_input(INPUT_POST, "loginTest") ){
				echo json_encode($result);
				exit;
			}
		}
	}
	
	/**
	 * Конфигурация атрибутов объекта
	 * @param array $config
	 */
	public function configure($config = array()) {
		
		if (  is_array( $config )) {
			foreach ( $config as $key => $value ) {
				if (  property_exists( $this , $key )) {
					$this->$key = $value;
				}
			}
		}
		
	}

	/**
	 * Возвращает данные авторизации в Виалоне для текущего авторизованного пользователя
	 *
	 * @return array
	 */
	public function retriveAccessData( $output=true ){
		$result = $this->dbmodel->retrieveAccessData( $_SESSION );
		if ( $output ) {
			$this->ReturnData( $result );
		} else {
			return $result;
		}
	}
	
	/**
	 * получение uid для карты
	 */
	public function getMapUid(){

		$ch = curl_init();		
		curl_setopt($ch, CURLOPT_URL, 'http://kit-api.wialon.com/wialon/ajax.html?svc=core/login&params={user:kitdemo,password:kitdemo}');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		session_write_close();
		$fp = curl_exec($ch);
		session_start();

		// ... если не получается, возможно мы сидим через прокси
		if (!$fp && $this->config->item( 'IS_DEBUG' ) ) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, 'http://kit-api.wialon.com/wialon/ajax.html?svc=core/login&params={user:kitdemo,password:kitdemo}');
			curl_setopt($ch, CURLOPT_PROXYUSERPWD, ''.':'.'');
			curl_setopt($ch, CURLOPT_PROXYPORT, 8080);
			curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_NTLM);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_PROXY, '192.168.36.167:3128');
			$fp = curl_exec($ch);
		}

		curl_close($ch);


		$result = json_decode( $fp );
		if ( !empty($result) && is_object($result) && !empty($result->user) ) {
			echo $result->user->id;
		}
		return false;
	}

	/**
	 * @return JSON Список объектов
	 */
	public function getAllAvlUnits() {
		$result = $this->dbmodel->getAllAvlUnits(1, true, false);
		$this->ReturnData( $result );
	}
	
	/**
	 * @return JSON Список групп объектов
	 */
	public function getAllAvlGroups() {
		$result = $this->dbmodel->getAllAvlGroups(1, true, false);
		$this->ReturnData( $result );
	}
	
	/**
	* конверт массива координат
	* возвращает адреса
	*/
	public function geocodeCoords(){
		$data = $this->ProcessInputData('geocodeCoords', true );
		$result = $this->dbmodel->geocodeCoords($data);
		$this->ReturnData( $result );
	}

	/**
	 * @return output JSON Список объектов с координатами
	 */
	public function getAllAvlUnitsWithCoords(){
		$result = $this->dbmodel->getAllAvlUnitsWithCoords(true, false);
		$this->ReturnData( $result );
	}
	
	/**
	 * @return output JSON Список uhegg объектов
	 */
	public function getAllAvlGroupUnits(){
		$result = $this->dbmodel->getAllAvlGroupUnits(true, false);
		$this->ReturnData( $result );
	}

	/**
	 * @return output JSON Список объектов для привязки бригад
	 */
	public function getAllAvlUnitsForMerge(){

		$units = $this->dbmodel->getAllAvlUnitsForMerge();
		$ret = array();

		if (gettype($units) == 'object')
			if (isset($units->items))
				$ret = $units->items;

		$this->ReturnData(array('data' => $ret));
	}

	/**
	 * @return output image Изображение объекта
	 */
	public function avlItemImage(){
		$item_id = isset( $_REQUEST['id'] ) ? (int) $_REQUEST['id'] : 0;
		if ( !$item_id ) {
			return;
		}
		$name = '1.png';
		try {
			$image = file_get_contents( $this->_image_host.'/avl_icon/get/'.$item_id.'/'.self::ICON_MAX_BORDER.'/'.$name );
			header( "Content-Type: image/png" );
			echo $image;
		} catch ( Exception $e ) {
			
		}
	}
	
	/**
	 * @return output wialon
	 */
	public function getWialonWindow(){	
		$this->load->library('parser');
		$template = 'wialon_window';
        $print_data = array(
            'url' => $_GET['url']            
        );
		return $this->parser->parse($template, $print_data);
	}

	/**
	 * Связывает бригаду скорой помощи с Виалоновской
	 * @return boolean
	 */
	public function mergeEmergencyTeam(){
		$data = $this->ProcessInputData( 'mergeEmergencyTeam', true );
		if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->mergeEmergencyTeam( $data );
		$this->ProcessModelSave( $response, true, true )->ReturnData();
	}

	/**
	 * Возвращает данные связи указанной бригады с Wialon
	 */
	public function loadEmergencyTeamRelList(){
		$data = $this->ProcessInputData( 'loadEmergencyTeamRelList', true );
		if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->loadEmergencyTeamRelList( $data );
		$this->ProcessModelList( $response, true, true )->ReturnData();
	}

	/**
	 * Сохранение связи бригад
	 * @return boolean
	 */
	public function saveEmergencyTeamRel(){
		$data = $this->ProcessInputData( 'saveEmergencyTeamRel', true );
		if ( $data === false ) {
			return false;
		}
		$data['data'] = json_decode( $data['data'] );

		$response = $this->dbmodel->saveEmergencyTeamRel( $data );
		$this->ProcessModelSave( $response, true, true )->ReturnData();
	}
	
	/**
	 * Сохранение связи бригад
	 * @return boolean
	 */
	public function getUnitIdByEmergencyTeamId(){
		$data = $this->ProcessInputData( 'getUnitIdByEmergencyTeamId', true );
		if ( $data === false ) {
			return false;
		}

		$result = $this->dbmodel->getUnitIdByEmergencyTeamId( $data );
		$this->ProcessModelList( $result, true, true )->ReturnData();
	}


	/**
	 * Парсер координат по адресу
	 */
	public function parseAddressCoordinates(){
		$this->dbmodel->parseAddressCoordinates();
	}


	/**
	 * Привязка адресов к подразделениям ЛПУ
	 * путем проверки вхождения координатам адреса в геозону подразделения
	 */
	public function parseGeozonesInfo(){
		set_time_limit(0);
		
		// ID города в КЛАДРе
		$kl_city_id = 3310; // Пермь
		
		//$xml = simplexml_load_file( $_SERVER['DOCUMENT_ROOT'].'/uploads/geozones_perm.kml');
		//$xml = simplexml_load_file( $_SERVER['DOCUMENT_ROOT'].'/uploads/novoskor.kml');
		$xml = simplexml_load_file( $_SERVER['DOCUMENT_ROOT'].'/uploads/geozones/geo_perm_v2.kml');

		$page = 1;
		while( $page !== false ){
			$houses = $this->dbmodel->getHousesCoordsListByCityId( $kl_city_id, $page );

			foreach( $houses as $house ) {
				if ( !$house['KLHouseCoords_LatLng'] ) {
					echo 'Empty coordinates '.var_export( $house, true ).'<br />';
					continue;
				}
				list( $lat, $lng ) = explode( ' ', $house['KLHouseCoords_LatLng'] );
				$point = array(
					'lat' => $lat,
					'lng' => $lng,
				);

				$placeMarkArrays = (!empty($xml->Document->Folder->Placemark)) ? $xml->Document->Folder->Placemark : $xml->Document->Placemark;

				foreach( $placeMarkArrays as $Placemark ) {

					$name = (!empty($Placemark->name )) ? trim( (string)$Placemark->name ) : trim( (string)$Placemark->description );

					if ( $name == "Территория обсл. подстанции №1" ) { // Подстанция центральная
						$LpuBuilding_id = 1958;
					} elseif ( $name == "Территория обсл. подстанции №2" ) { // Подстанция Свердловская
						$LpuBuilding_id = 1959;
					} elseif ( $name == "Территория обсл. подстанции №3" ) { // Подстанция Индустриальная
						$LpuBuilding_id = 1960;
					} elseif ( $name == "Территоирия обсл. подстанции №4" ) { // Подстанция Мотовилихинская
						$LpuBuilding_id = 1961;
					} elseif ( $name == "Территория обсл. подстанции №5" ) { // Подстанция коминтерна
						$LpuBuilding_id = 1962;
					} elseif ( $name == "Территория обсл. подстанции №6" ) { // Подстанция Галперина
						$LpuBuilding_id = 1963;
					} elseif ( $name == "Территория обсл. подстанции №7" ) { // Подстанция Старикова
						$LpuBuilding_id = 1964;
					} elseif ( $name == "Территория обсл. подстанции №8" ) { // Подстанция Писарева
						$LpuBuilding_id = 1965;
					} elseif ( $name == "Территория обсл. подстанции №9" ) { // Подстанция Гашкова
						$LpuBuilding_id = 1966;
					} elseif ( $name == "Территория обсл. подстанции №10" ) { // Подстанция Транспортная
						$LpuBuilding_id = 1967;
					} elseif ( $name == "Территория обсл. подстанции №12" ) { // Подстанция Ляды
						$LpuBuilding_id = 1968;
					}

					if ( $name == "Новоскор-Прикамье (Ленинский район)" ) {
						$LpuBuilding_id = 2490;
					} else if ( $name == "Новоскор-Прикамье (Свердловский район)" ) {
						$LpuBuilding_id = 2490;
					}

					if ( $name == "Центральная подстанция" ) {
						$LpuBuilding_id = 1958;
					} else if ( $name == "Мотовилихинская посдтанция" ) {
						$LpuBuilding_id = 1961;
					} else if ( $name == "Свердловская посдтанция" ) {
						$LpuBuilding_id = 1959;
					} else if ( $name == "Гайвинская подстанция" ) {
						$LpuBuilding_id = 3118;
					} else if ( $name == "Кировская подстанция" ) {
						$LpuBuilding_id = 3119;
					} else if ( $name == "Индустриальная подстанция" ) {
						$LpuBuilding_id = 1960;
					} else if ( $name == "Орджоникидзевская подстанция" ) {
						$LpuBuilding_id = 3121;
					} else if ( $name == "Подстанция Вышка-2" ) {
						$LpuBuilding_id = 3123;
					} else if ( $name == "Дзержинская подстанция" ) {
						$LpuBuilding_id = 3122;
					}

					$polygon = array();
					$vertices = explode( ' ', $Placemark->Polygon->outerBoundaryIs->LinearRing->coordinates );

					foreach( $vertices as $v ){
						list( $lng, $lat ) = explode( ',', $v );
						$polygon[] = array(
							'lat' => trim( $lat ),
							'lng' => trim( $lng ),
						);
					}

					$result = $this->dbmodel->isPointInPolygon( $polygon, $point );

					if ( $result == true ) {
						$this->dbmodel->bindLpuBuildingHouse( $LpuBuilding_id, $house['KLHouseCoords_id'] );
					}
				}
			}

			if ( sizeof( $houses ) ) {
				echo "Page: ".$page."<br />";
				$page++;
			} else {
				$page = false;
			}
		}

		echo 'End';
	}
	
	/**
	 * Получение списка ресурсов
	 * @param type $output
	 * @return type
	 */
	public function getAllResources() {
		$result = $this->dbmodel->getAllResources(true);
		$this->ReturnData( $result );
	}

	/**
	 * Получение отчета для путеводного листа
	 * @param type $data
	 */
	public function getWayBillGasReport() {
		
		$data = $this->ProcessInputData( 'getWayBillGasReport' , false ) ;
		if (!$data) return false ;
		
		//
		// Получаем идентификатор шаблона 
		//
		
		$template_id = $this->dbmodel->_getWaybillGasReportTemplateId();
		if (is_array($template_id) && !empty($template_id['Error_Msg'])) {
			$this->ReturnError($template_id['Error_Msg']);
			return false;
		}
		
		if ( $template_id === FALSE ) {
			return false;
		}
		
		//
		// Проверяем шаблон на корректность
		//
		
		$check = $this->dbmodel->_checkRequiredTablesInGasReportTemplate( $template_id );
		if (is_array($check) && !empty($check['Error_Msg'])) {
			$this->ReturnError($check['Error_Msg']);
			return false;
		}
		
		//
		// Получаем идентификатор ресурса
		//
		
		$report_resource_id = $this->dbmodel->getResourceId();
		
		if (!$report_resource_id) {		
			$this->ReturnError('При получении идентификатора ресурса Wialon произошла ошибка. Обратитесь к администратору');
			return false ;
		}
		
		//
		// Получаем отчет
		//
		
		$report_result = $this->dbmodel->getReport( array(
			'reportResourceId' => $report_resource_id ,
			'reportTemplateId' => $template_id ,
			'reportObjectId' => $data[ 'reportObjectId' ],
			'reportObjectSecId' => 0 ,
			'interval' => array(
				'from' => $data[ 'from' ]->getTimestamp() ,
				'to' => $data[ 'to' ]->getTimestamp() ,
				'flags' => 0
			) ,
			"tzOffset"=>$data[ 'from' ]->getOffset(),
			"lang"=>"ru"
		) ) ;
		
		// Обрабатываем результат
		
		if (empty($report_result['reportResult']['tables']) || !is_array( $report_result['reportResult']['tables'] ) || !sizeof( $report_result['reportResult']['tables'] )) {
			$this->ReturnError('Нет данных','nodata');
			return false ;
		}
		
		$rep_tables = $report_result['reportResult']['tables'];
		$gas_report_table_found = false;
		
		$gas_tbl = NULL; //Необходимо запомнить массив с данными по нужной таблице, чтобы связать данные из строк с их значением
		$gas_tbl_idx = NULL;
		
		//Получаем порядковый индекс нужной таблицы для получения результата
		reset($rep_tables);
		while ( ( list( $idx , $tbl) = each( $rep_tables ) ) && !$gas_report_table_found ) {
			if ($tbl[ 'nm' ] === self::GAS_REP_TBL_NAME) {
				$gas_report_table_found = true ;
				$gas_tbl = $tbl;
				$gas_tbl_idx = $idx;
			}
		}
		
		//Получаем все строки (а в нужной нам таблице всего 1 строка с результатом)
		$rows_result = $this->dbmodel->_getReportRows($gas_tbl_idx,0,0);
		
		if (empty($rows_result[0]['c']) || !is_array( $rows_result[0]['c']) || !sizeof($rows_result[0]['c']) ||
			empty($gas_tbl['h']) || !is_array( $gas_tbl['h']) || !sizeof($gas_tbl['h']) ||
			sizeof ($gas_tbl['h']) !== sizeof($rows_result[0]['c']) ) 
		{
			$this->ReturnError('Ошибка получения данных отчета','reporterror');
			return false ;	
		}
		
		$result = array();
		
		$columns = $this->dbmodel->_getGasReportRequiredTblColumns();
		
		foreach ( $gas_tbl['h'] as $idx => $label ) {
			$result[array_search($label,$columns)] = array(
				'display_name'=>$label,
				'value'=>$rows_result[0]['c']["$idx"]
			);
		}
		
		$this->ReturnData( array(
			'success'=>true,
			'data'=>$result
		) );
		
		return true;
	}

	/**
	 * Метод создания шаблона для отчета по ГСМ
	 * @return array
	 */
	public function createWayBillGasReportTemplate() {
		$result = $this->dbmodel->createWayBillGasReportTemplate();
		
		$this->ReturnData( array(
			'success'=>true,
			'data'=>$result
		) );
		
		return true;
	}

	/**
	 * Авторизация
	 *
	 * @return output JSON
	 */
	public function login($credentials = array()){

		$this->dbmodel->login($credentials);
	}

	/**
	 * Выход
	 */
	public function logout(){
		$this->dbmodel->logout();
	}

	/**
	 * Тест
	 */
	public function loginTest() {
		//сделал для астрахани, для тестирования подключения.
		$data = $this->ProcessInputData( 'loginTest', true );
		$result = $this->dbmodel->loginTest($data);
		$success=false; $str='';
		if(empty($result)){
			$str='ERROR';
		}elseif ( is_array($result) && (array_key_exists( 'error', $result ) || array_key_exists( 'fp', $result )) ) {
			$str=$result;
		}else{
			$success=true;
			$str=$result;
		}
		$this->ReturnData( array(
			'success'=>$success,
			'data'=>$str
		) );
	}
}