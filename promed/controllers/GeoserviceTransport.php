<?php

defined( 'BASEPATH' ) or die( '404. Script not found.' ) ;

/**
 * @class GeoserviceTransport
 * 
 * @author Miyusov Aleksandr
 * @since 08.2015
 */
class GeoserviceTransport extends swController {

	public $inputRules = array(
		'getGeoserviceTransportListWithCoords' => array(
			array( 'field' => 'geoservice_type' , 'label' => 'Тип геосервиса' , 'rules' => '' , 'type' => 'string' , 'default' => 'Wialon' )
		) ,
		'getGroupList' => array(
			array( 'field' => 'geoservice_type' , 'label' => 'Тип геосервиса' , 'rules' => '' , 'type' => 'string' , 'default' => 'Wialon' )
		) ,
	) ;
	
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	
		$this->inputRules = array(
			'getGeoserviceTransportListWithCoords' => array(
				array( 'field' => 'geoservice_type' , 'label' => 'Тип геосервиса' , 'rules' => '' , 'type' => 'string' , 'default' => 'Wialon' ),
				array( 'field' => 'filtertransport_ids' , 'label' => 'Список трекеров' , 'rules' => '' , 'type' => 'json_array' )
			) ,
			'getGroupList' => array(
				array( 'field' => 'geoservice_type' , 'label' => 'Тип геосервиса' , 'rules' => '' , 'type' => 'string' , 'default' => 'Wialon' )
			)
		) ;
		
		$this->load->database();
		$this->load->model('GeoserviceTransport_model', 'dbmodel');
	}
	
	/**
	 * Список доступных для получения данных геосервисов
	 * @var type 
	 */
	
	protected $_geoservice_list = array( 'Wialon' , 'TNC' ) ;
	
	
	/**
	 * Получение геосервиса
	 */
	protected function _getGeoserviceType($data) 
	{
		if(!isset($_SESSION['CurMedService_id']) && empty($data['MedService_id'])){
			return false;
		}
		
		$response = $this->dbmodel->getGeoserviceType($data);
		$region = getRegionNick();
		
		if($response && isset($response[0]) && is_array($response[0])){
			$geoservice_type = $response[0]['ApiServiceType_Name']; 
		}
		else{
			//по умолчанию костыль для регионов
			switch ( $region ) {
				case 'krym':
					$geoservice_type = 'TNC';
					break ;
				default:
					$geoservice_type = 'Wialon';
			}
			
			//$geoservice_type =( in_array( $data[ 'geoservice_type' ] , $this->_geoservice_list ) ) ? $data[ 'geoservice_type' ] : 'Wialon' ;
		}	
		return $geoservice_type;
	}
	
	/**
	 * Метод получения списка транспортных средств с координатами
	 * @return boolean
	 */
	
	public function getGeoserviceTransportListWithCoords()
	{

		$data = $this->ProcessInputData( 'getGeoserviceTransportListWithCoords' , true ) ;
		if ( $data === false )
			return false ;

		if(!empty($_SESSION['CurMedService_id'])){
			$arrMedServices = array(array('MedService_id' => $_SESSION['CurMedService_id']));
		}else{
			$this->load->model('CmpCallCard_model4E');
			$arrMedServices = $this->CmpCallCard_model4E->loadSmpUnitsFromOptions(array(), true);
		}

		$objects = array();
		foreach($arrMedServices as $medServ) {
			$geoservice_type = $this->_getGeoserviceType(array('MedService_id' => $medServ['MedService_id']));

			switch ($geoservice_type) {
				case 'Wialon':

					$this->load->model('Wialon_model');

					$accessData = $this->Wialon_model->getAccessDataByMedService(array('MedService_id' => $medServ['MedService_id']));

					$_SESSION['wialon']['user'] = $accessData['MedService_WialonLogin'];
					$_SESSION['wialon']['password'] = $accessData['MedService_WialonPasswd'];
					$_SESSION['wialon']['WialonToken'] = $accessData['MedService_WialonToken'];


					$result = $this->Wialon_model->init();
					$result = $this->Wialon_model->getAllAvlUnitsWithCoords(FALSE /* output */, TRUE/* assoc */);

					$result = $this->_processWialonData( $result ) ;

					if(is_array($result))
						$objects = array_merge($objects, $result);

					break;
				case 'TNC':
					$this->load->model('TNC_model');
					$filterTransportIds = isset($data['filtertransport_ids']) ? $data['filtertransport_ids'] : null;

					$response = $this->TNC_model->getLastTransportCoords(false, $filterTransportIds);

					$result = $this->_processTNCData( $response ) ;

					if(is_array($result))
						$objects = array_merge($objects, $result);

					break;
				default:
					break;
			}
		}

		//Удаляем дубли объектов
		if(count($objects) > 0){
			$objects = array_unique($objects, SORT_REGULAR);
		}

		$this->ProcessModelList( $objects , TRUE , TRUE )->ReturnData() ;
	}

	/**
	 * Метод приведения данных ТНЦ к общему виду
	 * @param type $data
	 * @return array
	 */
	protected function _processTNCData( $data = array() ) 
	{

		$result = array( ) ;
		
		if (!is_array( $data )) {
			return $result;
		}
		
		foreach ( $data as $item) {
			/**
			 *  'id' => int 9365918
				'name' => string 'HYUNDAI R14 OW-7 002' (length=20)
				'deviceId' => int 44862300
				'lat' => float 58.052332479519
				'lng' => float 38.83423315946
				'direction' => null
			 */
			$result[ ] = array(
				'GeoserviceTransport_name' => (!empty( $item[ 'name' ] )) ? $item[ 'name' ] : null ,
				'GeoserviceTransport_id' => (!empty( $item[ 'id' ] )) ? $item[ 'id' ] : null ,
				'lat' => (!empty( $item[ 'lat' ] )) ? $item[ 'lat' ] : null ,
				'lng' => (!empty( $item[ 'lon' ] )) ? $item[ 'lon' ] : null ,
				'direction' => (!empty( $item[ 'direction' ] )) ? $item[ 'direction' ] : null,
				'groups'=>( !empty( $item[ 'groupCode' ] ) ) ? array($item[ 'groupCode' ]) : array(), 
			) ;
			
		}

		return $result ;
	}

	/**
	 * Метод приведения данных Wialon к общему виду
	 * @param type $data
	 * @return array
	 */
	protected function _processWialonData( $data ) 
	{
		$result = array( ) ;

		if ( empty( $data[ 'items' ] ) || !is_array( $data[ 'items' ] ) ) {
			return $result ;
		}

		foreach ( $data[ 'items' ] as $item ) {

			$result[ ] = array(
				'GeoserviceTransport_name' => (!empty( $item[ 'nm' ] )) ? $item[ 'nm' ] : null ,
				'GeoserviceTransport_id' => (!empty( $item[ 'id' ] )) ? $item[ 'id' ] : null ,
				'lat' => (!empty( $item[ 'pos' ][ 'y' ] )) ? $item[ 'pos' ][ 'y' ] : null ,
				'lng' => (!empty( $item[ 'pos' ][ 'x' ] )) ? $item[ 'pos' ][ 'x' ] : null ,
				'direction' => (!empty( $item[ 'pos' ][ 'c' ] )) ? $item[ 'pos' ][ 'c' ] : null,
				'groups'=>( !empty( $item[ 'ugs' ] ) ) ? $item[ 'ugs' ] : array(), 
			) ;
			
		}

		return $result ;
	}
	/**
	 * Метод получения списка групп транспортных средств геосервиса
	 * @return array
	 */
	public function getGroupList() 
	{
		
		$data = $this->ProcessInputData( 'getGroupList' , true ) ;
		if ( $data === false )
			return false ;
		
		$geoservice_type = $this->_getGeoserviceType($data);
		//$geoservice_type = ( in_array( $data[ 'geoservice_type' ] , $this->_geoservice_list ) ) ? $data[ 'geoservice_type' ] : 'Wialon' ;

		$result = array( ) ;

		switch ( $geoservice_type ) {
			case 'Wialon':
				$this->load->model('Wialon_model');
				$result = $this->Wialon_model->getAllAvlGroupUnits(  FALSE /* output */ , TRUE/* assoc */ ) ;
				
				$result = $this->_processWialonGroupData( $result ) ;

				break ;
			case 'TNC':
				$this->load->model('TNC_model');
				$response = $this->TNC_model->getTransportGroupList( false ) ;

				if (is_array($response) && (!isset($response[0]['success'])) ) {
					$result = $this->_processTNCGroupData( $response ) ;
				}
				break ;
		}

		$this->ProcessModelList( $result , TRUE , TRUE )->ReturnData() ;
		
	}
	
	/**
	 * Метод приведения данных о группах Wialon к общему виду
	 * @param type $data
	 * @return array
	 */
	protected function _processWialonGroupData($data)
	{
		$result = array();
		
		if (empty($data['items'])) {
			return $result;
		}
		
		foreach ($data['items'] as $group) {
			$result[] = array(
				'id' => $group['id'],
				'name' => $group['nm']
			);
		}
		
		return $result;
		
	}
	
	/**
	 * Метод приведения данных о группах ТНЦ к общему виду
	 * @param type $data
	 * @return array
	 */
	protected function _processTNCGroupData($data){
		$result = array();

		if (!is_array($data)) {
			return $result;
		}

		foreach ($data as $group) {
			$result[] = array(
				'id' => $group['code'],
				'name' => $group['description']
			);
		}

		return $result;
	}
}

?>
