<?php
class Dicom_model extends swModel {
	
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}
	
	const MYAETITLE = 'PROMED';
	
	/*
	private $_servers = array(
		
		// Promed Central PACS
		'PROMED' => array(
			// Application Entity Title
			'aetitle'		=> 'PROMED', 
			// Description just for listening test
			'description'	=> 'Promed Central PACS',
			// Hostname
			'hostname'		=> '',
			// IP Address
			'ip_address'	=> '192.168.37.116',
			// Port
			'port'			=> '11112',
			// Allow Dicom Commands From My AE
			'commands'		=> array(
				'C-STORE' => array(
					'enable' => 1,
					'institution_name_filters'		=> '',
					'referring_physician_filters'	=> '',
					'reading_physician_filters'		=> '',
				),
				'C-FIND' => array(
					'enable' => 1,
					'institution_name_filters'		=> '',
					'referring_physician_filters'	=> '',
					'reading_physician_filters'		=> '',
				),
				'C-MOVE' => array(
					'enable' => 1,
					'institution_name_filters'		=> '',
					'referring_physician_filters'	=> '',
					'reading_physician_filters'		=> '',
				),
				'WORKLIST-FIND' => array(
					'enable' => 1,
					'institution_name_filters'		=> '',
					'referring_physician_filters'	=> '',
					'reading_physician_filters'		=> '',
				),
			),
		),
		
		// Test server
		'DCM4CHEE' => array(
			// Application Entity Title
			'aetitle'		=> 'DCM4CHEE', 
			// Description just for listening test
			'description'	=> 'Just for listening test',
			// Hostname
			'hostname'		=> '',
			// IP Address
			'ip_address'	=> '192.168.36.159',
			// Port
			'port'			=> '11112',
			// Allow Dicom Commands From My AE
			'commands'		=> array(
				'C-STORE' => array(
					'enable' => 1,
					'institution_name_filters'		=> '',
					'referring_physician_filters'	=> '',
					'reading_physician_filters'		=> '',
				),
				'C-FIND' => array(
					'enable' => 1,
					'institution_name_filters'		=> '',
					'referring_physician_filters'	=> '',
					'reading_physician_filters'		=> '',
				),
				'C-MOVE' => array(
					'enable' => 1,
					'institution_name_filters'		=> '',
					'referring_physician_filters'	=> '',
					'reading_physician_filters'		=> '',
				),
				'WORKLIST-FIND' => array(
					'enable' => 1,
					'institution_name_filters'		=> '',
					'referring_physician_filters'	=> '',
					'reading_physician_filters'		=> '',
				),
			),
		),
	);
	*/
	
	/*
	protected $_remote_aetitle = null;
	
	
	public function setRemoteAetitle( $aetitle ){
		if ( !array_key_exists( $aetitle, $this->_servers ) ) {
			die('Задан не существующий сервер');
		}
		
		$this->_server_aetitle = $aetitle;
		unset( $aetitle );
	}
	
	
	public function getRemoteAetitle(){
		if ( $this->_server_aetitle === null ) {
			return false;
		}
		return $this->_server_aetitle;
	}
	
	
	public function setMyTitle( $mytitle ){
		$this->_mytitle = $mytitle;
		unset( $mytitle );
	}
	
	
	*/

	/**
	 * Функция получения ссылки на внешний просмотровщик диком
	 * @param type $ip
	 * @param type $wado_port
	 * @param type $study_uid
	 * @param type $region
	 * @return type
	 */
	protected function _getViewerLink( $ip , $wado_port, $study_uid, $region, $forEmkLink = false ) {
		if (defined('PROMED_PACS_IP') && defined('PROMED_PACS_EXTERNAL_IP') && ($ip == PROMED_PACS_IP || isCurrentARMType('remoteconsultcenter'))) {
			$ip = PROMED_PACS_EXTERNAL_IP;
		}
		if($forEmkLink)	{
			return "<a target='_blank' href=\"http://{$ip}:{$wado_port}/weasis-pacs-connector/viewer.jnlp?studyUID={$study_uid}\" class='' style='float:right;display:block;'> Внешняя программа </a>";
		}
		switch ($region) {
			case 91:
			case 66:
			case 02:
			case 59:
			case 101:
				return "<a target='_blank' href=\"http://{$ip}:{$wado_port}/weasis-pacs-connector/viewer.jnlp?studyUID={$study_uid}\" class='additionalGridRowHoverIcon'> Внешняя программа </a>";
				break;

			default:
				return "<a target='_blank' href=\"http://{$ip}:{$wado_port}/oviyam/oviyam?studyUID={$study_uid}\" class='additionalGridRowHoverIcon'> Внешняя программа </a>";
				break;
		}
	}
	
	/**
	 * Список PACS устройств привязанных к МО
	 *
	 * @param integer $lpu_id ID МО
	 * @return array
	 */
	public function getLpuPacsList( $lpu_id ){
		$lpu_id = intval( $lpu_id );
		if ( !$lpu_id ) {
			return array();
		}
		$sql = "
			SELECT
				LS.LpuSection_FullName,
				ISNULL(LpuPacs_id,0) as LpuPacs_id,
				LP.LpuPacs_aetitle,
				LP.LpuPacs_desc,
				LP.LpuPacs_ip,
				LP.LpuPacs_port,
				LP.LpuPacs_wadoPort
			FROM
				v_LpuSection LS with (nolock)
				LEFT JOIN v_LpuPacs LP with (nolock) ON( LP.LpuSection_id = LS.LpuSection_id )
			WHERE
				LS.Lpu_id = :Lpu_id AND
				LpuPacs_id > 0
		";
		$query = $this->db->query($sql,array(
			'Lpu_id' => $lpu_id
		));
		if ( is_object( $query ) ) {
			return $query->result_array();
		} else {
			return array();
		}
	}
	
	/**
	 * Получение данных для доступа к PACS-станции
	 * 
	 * @param LpuEquipmentPacs_id ID устройства PACS
	 * @return boolean или array
	 */
	public function getPacsSettings($data) {
		if ( !array_key_exists( 'LpuEquipmentPacs_id', $data ) && empty($data['LpuEquipmentPacs_id']) ) {
			return array( array( 'Error_Msg' => 'Не указан идентификатор PACS' ) );
		}
		$sql= "
			SELECT
				LEP.Lpu_id,
				LEP.PACS_ip_vip,
				LEP.PACS_ip_local,
				LEP.PACS_aet,
				LEP.PACS_port,
				LEP.PACS_wado
			FROM
				v_LpuEquipmentPacs LEP with(nolock)
			WHERE
				LEP.LpuEquipmentPacs_id = :LpuEquipmentPacs_id
			";
		$query = $this->db->query($sql,array(
			'LpuEquipmentPacs_id' => $data['LpuEquipmentPacs_id']
		));
		if ( is_object( $query ) ) {
			if ( ( $result = $query->result_array() ) ) {
				return $result;
			}
		} else {
			return false;
		}
	}
	
	/**
	 * Полуение данных по конкретному исследованию
	 * 
	 * @return array
	 */
	
	public function getStudyData($data) {
		
		if ( !array_key_exists( 'LpuEquipmentPacs_id', $data ) && empty($data['LpuEquipmentPacs_id']) ) {
			return array( array('success'=>false, 'Error_Msg' => 'Не указан идентификатор PACS' ) );
		}
		if ( !array_key_exists( 'study_uid', $data ) && empty($data['study_uid']) ) {
			return array( array('success'=>false,  'Error_Msg' => 'Не указан идентификатор исследования' ) );
		}
		
		$PACS_settings = $this->getPacsSettings($data);

		if (!$PACS_settings||empty($PACS_settings)||empty($PACS_settings[0])||!empty($PACS_settings[0]['Error_Msg'])) {
			if (is_null($PACS_settings)) {
				return array(array('success'=>false,'Error_Msg'=>'Параметры PACS устройства были удалены из системы'));
			}
			return $PACS_settings;
		}
		$PACS_settings = $PACS_settings[0];
		$studies = $this->remoteSeries(array(
			'LpuEquipmentPacs_id'=>$data['LpuEquipmentPacs_id'],
			'Study_uid'=>$data['study_uid'],
			'mode'=>'count'
		));

		$servPort = isset($_SERVER['SERVER_PORT'])?$_SERVER['SERVER_PORT']:null;
		
		if ($studies && empty($studies[0]['Error_Msg'])) {
			//$PACS_settings['PACS_wado'] = (in_array($servPort, array(443,444,445) ))?(defined('PROMED_PACS_HTTPS_WADOPORT')?PROMED_PACS_HTTPS_WADOPORT:'8443'):$PACS_settings['PACS_wado'];
			if ($data['Lpu_id'] == $PACS_settings['Lpu_id']) {
				//ищем лоакльно в больнице
				return $this->processPacsServiceRequest(array(
					'study_uid'=>$data['study_uid'],
					'seriesUID'=>(isset($data['seriesUID']))?$data['seriesUID']:0,
					'Pacs_host_IP'=>$PACS_settings['PACS_ip_vip'],
					'Port'=>$PACS_settings['PACS_port'],
					'AeTitle'=>$PACS_settings['PACS_aet'],
					'Img_host_IP'=>$PACS_settings['PACS_ip_local'],
					'WadoPort'=>$PACS_settings['PACS_wado'],
					'urlPrefix'=>'',
					'queryType'=>(isset($data['queryType']))?$data['queryType']:''
				));
			} else {
				//ищем лоакльно в др. больнице
				return $this->processPacsServiceRequest(array(
					'study_uid'=>$data['study_uid'],
					'seriesUID'=>(isset($data['seriesUID']))?$data['seriesUID']:0,
					'Pacs_host_IP'=>$PACS_settings['PACS_ip_vip'],
					'Port'=>$PACS_settings['PACS_port'],
					'AeTitle'=>$PACS_settings['PACS_aet'],
					'Img_host_IP'=>$PACS_settings['PACS_ip_vip'],
					'WadoPort'=>$PACS_settings['PACS_wado'],
					'urlPrefix'=>'provi/',
					'queryType'=>(isset($data['queryType']))?$data['queryType']:''
				));
			}
		} else {
			//Ищем на глобальном
			if (!defined('PROMED_PACS_IP')||!defined('PROMED_PACS_WADOPORT')||!defined('PROMED_PACS_AETITLE')||!defined('PROMED_PACS_PORT')) {
				return array(array('success'=>false,'Error_Msg'=>'Не определены константы глобального PACS'));
			}
			return $this->processPacsServiceRequest(array(
					'study_uid'=>$data['study_uid'],
					'seriesUID'=>(isset($data['seriesUID']))?$data['seriesUID']:0,
					'Pacs_host_IP'=>PROMED_PACS_IP,
					'Port'=>PROMED_PACS_PORT,
					'AeTitle'=>PROMED_PACS_AETITLE,
					'Img_host_IP'=>PROMED_PACS_IP,
					'WadoPort'=>PROMED_PACS_WADOPORT,
					'urlPrefix'=>'provi/',
					'queryType'=>(isset($data['queryType']))?$data['queryType']:''
				));
		}
	}
	/**
	 * Выполнение запроса к PACS сервису
	 * @param type $data
	 * @return type
	 */
	public function processPacsServiceRequest($data) {
		
		if ( !array_key_exists( 'study_uid', $data ) && empty($data['study_uid']) ) {
			return array( array('success'=>false,   'Error_Msg' => 'Не указан идентификатор исследования' ) );
		}
		if ( !array_key_exists( 'Pacs_host_IP', $data ) && empty($data['Pacs_host_IP']) ) {
			return array( array('success'=>false,   'Error_Msg' => 'Не указан хост PACS-устройства' ) );
		}
		if ( !array_key_exists( 'Img_host_IP', $data ) && empty($data['Img_host_IP']) ) {
			return array( array('success'=>false,   'Error_Msg' => 'Не указан хост получения изображений' ) );
		}
		if ( !array_key_exists( 'WadoPort', $data ) && empty($data['WadoPort']) ) {
			return array( array('success'=>false,   'Error_Msg' => 'Не указан wabo-порт получения изображений' ) );
		}		
		if ( !array_key_exists( 'Port', $data ) && empty($data['Port']) ) {
			return array( array('success'=>false,   'Error_Msg' => 'Не указан порт PACS-устройства' ) );
		}
		if ( !array_key_exists( 'AeTitle', $data ) && empty($data['AeTitle']) ) {
			return array( array('success'=>false,   'Error_Msg' => 'Не указан AETITLE PACS-устройства' ) );
		}
		if ( !array_key_exists( 'urlPrefix', $data ) && empty($data['urlPrefix']) ) {
			$data['urlPrefix']='';
		}		
		if (!defined('PACS_SERVICE_IP')||!defined('PACS_SERVICE_PORT')) {
			return array(array('Error_Msg'=>'Не определены константы доступа к PACS-сервису'));
		}
		
		$paramsQueryString = http_build_query(array('StudyID'=>$data['study_uid'], 'HostName'=>$data['Pacs_host_IP'], 'Port'=>$data['Port'], 'AeTitle'=>$data['AeTitle'],'DCMProtocol'=>'DICOM'));
		$queryString = 'http://'.PACS_SERVICE_IP.':'.PACS_SERVICE_PORT.'/'.((defined('PACS_SERVICE_NAME'))?PACS_SERVICE_NAME:'DCMWebService').'/rest/PatientInstanceInfo?'.$paramsQueryString;
		if (!empty($_REQUEST['dicom_debug'])) {
			var_dump($queryString);
		}
		$StudyDataJson = file_get_contents($queryString);
		if (!empty($_REQUEST['dicom_debug'])) {
			var_dump($StudyDataJson);
		}
		
		$StudyDataUngrouped = json_decode($StudyDataJson, true);
		
		if (($StudyDataUngrouped == NULL)||(!isset($StudyDataUngrouped['objectInstance']))) {
			return array(array('Error_Msg'=>'Неверно сформированный ответ PACS-сервиса'));
		}
		if (sizeof($StudyDataUngrouped['objectInstance'])==0) {
			return array(array('Error_Msg'=>'Не найдено ни одного изображения для исследования'));
		}
		$StudyDataUngrouped = $StudyDataUngrouped['objectInstance'];
		$StudyDataGroupedBySeries = array();
		
		$InstancesDataInSeries = array();
		
		switch( $data['queryType'] ) {
			case 'series':
				{
				foreach ($StudyDataUngrouped as $instance) {
					if (!isset($StudyDataGroupedBySeries[$instance['seriesNumber']])) {
						
						$StudyDataGroupedBySeries[$instance['seriesNumber']] = array(
							'seriesNumber'=>(isset($instance['seriesNumber']))?$instance['seriesNumber']:0,
							'seriesDescription'=>(isset($instance['seriesDescription']))?$instance['seriesDescription']:'',
							'studyUID'=>$data['study_uid'],
							'modality'=>(isset($instance['modality']))?$instance['modality']:'',
							'numberOfInstances'=>(isset($instance['numberOfInstances']))?$instance['numberOfInstances']:'0',
							'seriesUID'=>(isset($instance['seriesUID']))?$instance['seriesUID']:'0',
							'instances'=>array()
						);
						
					}
					//модальность экг?
					if( (isset($instance['modality'])) && ($instance['modality']=='ECG') ){
						
						$svgcontext = file_get_contents((($data['urlPrefix']!='')?$data['urlPrefix']:'http://')."{$data['Img_host_IP']}:{$data['WadoPort']}/rid/IHERetrieveDocument?requestType=DOCUMENT&documentUID={$instance['sopIUID']}&preferredContentType=image%2Fsvg%2Bxml");
						
						array_push($StudyDataGroupedBySeries[$instance['seriesNumber']]['instances'], array(
							'numberOfFrames'=>(isset($instance['numberOfFrames']))?$instance['numberOfFrames']:'1',
							'rows'=>(isset($instance['rows']))?$instance['rows']:'',
							'sopUID'=>(isset($instance['sopIUID']))?$instance['sopIUID']:'',
							'src'=>'../img/dicomViewer/default-ecg.png',
							'smallsrc'=>'../img/dicomViewer/default-ecg.png',
							'svg'=>$svgcontext
						));
					}
					else{
						array_push($StudyDataGroupedBySeries[$instance['seriesNumber']]['instances'], array(
							'numberOfFrames'=>(isset($instance['numberOfFrames']))?$instance['numberOfFrames']:'1',
							'rows'=>(isset($instance['rows']))?$instance['rows']:'',
							'sopUID'=>(isset($instance['sopIUID']))?$instance['sopIUID']:'',
							'src'=>(($data['urlPrefix']!='')?$data['urlPrefix']:'http://')."{$data['Img_host_IP']}:{$data['WadoPort']}/wado?requestType=WADO&studyUID=&seriesUID=&objectUID={$instance['sopIUID']}&frameNumber=1",
							'smallsrc'=>(($data['urlPrefix']!='')?$data['urlPrefix']:'http://')."{$data['Img_host_IP']}:{$data['WadoPort']}/wado?requestType=WADO&studyUID=&seriesUID=&objectUID={$instance['sopIUID']}&frameNumber=1&rows=100"
		
						));
					}
				}
				break;
				}
			
			case 'instances':
			{
				foreach ($StudyDataUngrouped as $instance) {
					if ( (isset($instance['seriesUID'])) && ($instance['seriesUID'] == $data['seriesUID']) ) {
						array_push($InstancesDataInSeries, array(
							'rows'=>(isset($instance['rows']))?$instance['rows']:'',
							'numberOfFrames'=>(isset($instance['numberOfFrames']))?$instance['numberOfFrames']:'1',
							'src'=>(($data['urlPrefix']!='')?$data['urlPrefix']:'http://')."{$data['Img_host_IP']}:{$data['WadoPort']}/wado?requestType=WADO&studyUID=&seriesUID=&objectUID={$instance['	']}&frameNumber=1"
						));
					}
				}
				return $InstancesDataInSeries;
				break;
			}
			/*$StudyDataGroupedBySeries[$instance['seriesNumber']][] = array(
				'numberOfFrames'=>(isset($instance['numberOfFrames']))?$instance['numberOfFrames']:'1',
				'rows'=>(isset($instance['rows']))?$instance['rows']:'',
				
				'src'=>(($data['urlPrefix']!='')?$data['urlPrefix']:'http://')."{$data['Img_host_IP']}:{$data['WadoPort']}/wado?requestType=WADO&studyUID=&seriesUID=&objectUID={$instance['sopIUID']}&frameNumber=1"
			);*/
			
			default:
			{
				/*foreach ($StudyDataUngrouped as $instance) {	
					if (!isset($StudyDataGroupedBySeries[$instance['seriesNumber']])) {
						$StudyDataGroupedBySeries[$instance['seriesNumber']] = array();
					}
					$StudyDataGroupedBySeries[$instance['seriesNumber']]['instances'] = array(
						'numberOfFrames'=>(isset($instance['numberOfFrames']))?$instance['numberOfFrames']:'1',
						'rows'=>(isset($instance['rows']))?$instance['rows']:'',
						'src'=>(($data['urlPrefix']!='')?$data['urlPrefix']:'http://')."{$data['Img_host_IP']}:{$data['WadoPort']}/wado?requestType=WADO&studyUID=&seriesUID=&objectUID={$instance['sopIUID']}&frameNumber=1"
					);
				}*/
				foreach ($StudyDataUngrouped as $instance) {
					if (!isset($StudyDataGroupedBySeries[$instance['seriesNumber']])) {		
						$StudyDataGroupedBySeries[$instance['seriesNumber']] = array(
							'seriesNumber'=>(isset($instance['seriesNumber']))?$instance['seriesNumber']:0,
							'seriesDescription'=>(isset($instance['seriesDescription']))?$instance['seriesDescription']:'',
							'studyUID'=>$data['study_uid'],
							'modality'=>(isset($instance['modality']))?$instance['modality']:'',
							'numberOfInstances'=>(isset($instance['numberOfInstances']))?$instance['numberOfInstances']:'0',
							'seriesUID'=>(isset($instance['seriesUID']))?$instance['seriesUID']:'0',
							'instances'=>array()
						);
					}
					array_push($StudyDataGroupedBySeries[$instance['seriesNumber']]['instances'], array(
						'numberOfFrames'=>(isset($instance['numberOfFrames']))?$instance['numberOfFrames']:'1',
						'rows'=>(isset($instance['rows']))?$instance['rows']:'',
						'sopUID'=>(isset($instance['sopIUID']))?$instance['sopIUID']:'',
						'src'=>(($data['urlPrefix']!='')?$data['urlPrefix']:'http://')."{$data['Img_host_IP']}:{$data['WadoPort']}/wado?requestType=WADO&studyUID=&seriesUID=&objectUID={$instance['sopIUID']}&frameNumber=1",
						'smallsrc'=>(($data['urlPrefix']!='')?$data['urlPrefix']:'http://')."{$data['Img_host_IP']}:{$data['WadoPort']}/wado?requestType=WADO&studyUID=&seriesUID=&objectUID={$instance['sopIUID']}&frameNumber=1&rows=100"
					));
				}
				break;
			}
		}

		return $StudyDataGroupedBySeries;
	}

	/**
	 * Локальный PACS сервер привязанный к МО
	 * 
	 * @param integer $Lpu_id ID МО
	 * @param integer $MedService_id ID Службы
	 * @return boolean|array
	 */
	public function getLpuLocalPacs( $Lpu_id = null, $MedService_id = null ) {
		if ( empty($Lpu_id) || empty($MedService_id) ) {
			return false;
		}

		$response_arr = $this->queryResult("
			SELECT
				LEP.LpuEquipmentPacs_id,
				LEP.PACS_ip_vip,
				LEP.PACS_ip_local,
				LEP.PACS_name,
				LEP.PACS_aet,
				LEP.PACS_port,
				LEP.PACS_wado
			FROM
				v_LpuEquipmentPacs LEP with(nolock)
			WHERE
				LEP.Lpu_id = :Lpu_id
		", [
			'Lpu_id' => $Lpu_id
		]);

		if ( $response_arr === false || !is_array($response_arr) ||  count($response_arr) > 1 ) {
			$response_arr = $this->queryResult("
				SELECT
					LEP.LpuEquipmentPacs_id,
					LEP.PACS_ip_vip,
					LEP.PACS_ip_local,
					LEP.PACS_name,
					LEP.PACS_aet,
					LEP.PACS_port,
					LEP.PACS_wado
				FROM
					v_MedService MS with(nolock)
					left join v_LpuEquipmentPacs LEP with(nolock) on LEP.LpuEquipmentPacs_id = MS.LpuEquipmentPacs_id
				WHERE
					MS.MedService_id = :MedService_id
			", [
				'MedService_id' => $MedService_id
			]);
		}

		return $response_arr;
	}
	
	/**
	 * Выполнение запроса C-FIND к указанному PACS сереверу или устройству
	 * 
	 * @param array $connection
	 * @param string $studyid
	 * @param string $date
	 * @param string $accession
	 * @param string $referdoc
	 * @return array
	 */
	public function performCFindToPacs( $connection, $studyid='', $date='', $accession='', $referdoc='', $patientid='' ) {
		$connection['ip_address'] = preg_replace('#\.0{1,2}[^.]#','.',$connection['ip_address']);
		require_once APPPATH.'libraries/Dicom/dicom.php';
		$assoc = new Association( $connection['ip_address'], $connection['hostname'], $connection['port'], $connection['aetitle'], $connection['myaetitle'] );
		if ( $assoc == false ) {
			return array( array( 'Error_Msg' => 'Не удалось подключиться к серверу '.$connection['aetitle'].' ('.$connection['ip_address'].':'.$connection['port'].').' ) );
		}
		if ( strlen( $patientid ) ) {			
			$identifier = new CFindIdentifierPatient( $patientid, $studyid, $date, $accession, $referdoc );
		} else {
			$identifier = new CFindIdentifierStudyRoot( $studyid, $date, $accession, $referdoc );
		}
		$matches = $assoc->find( $identifier, $error );
		
		unset($assoc);
		
		if ( strlen( $error ) ) {
			return array( array( 'Error_Msg' => 'Во время запроса списка исследований произошла ошибка: '.$error.'.' ) );
		} else {
			return $this->outputRemoteStudies( $identifier, $matches );
		}		
	}
	
	/**
	 * Полуение списка серий для конкретного исследования
	 * 
	 * @return array
	 */
	
	public function remoteSeries($params=array()) {
		if ( !isset( $params['LpuEquipmentPacs_id'] ) || !$params['LpuEquipmentPacs_id'] ) {
			return array( array( 'success'=>false, 'Error_Msg' => 'Отсутствует обязательный параметр идентификатор устройства PACS' ) );
		}
		
		$this->load->model('LpuPassport_model','LpuPassport_model');
		
		$lpu_local_pacs = $this->LpuPassport_model->loadLpuEquipment(array(
			'LpuEquipmentPacs_id'=>$params['LpuEquipmentPacs_id'],
			'Lpu_id' => isset($params['Lpu_id']) ? $params['Lpu_id'] : null,
		));
		
		if (!$lpu_local_pacs || !isset($lpu_local_pacs[0]) || isset($lpu_local_pacs[0]["Error_Msg"])) {
			return $lpu_local_pacs;
		}
		
		$lpu_local_pacs=$lpu_local_pacs[0];
		
		if ( !is_array( $lpu_local_pacs ) && empty($lpu_local_pacs['LpuEquipmentPacs_id'])) {
			//@TODO Юзать константы
			return array( array( 'success'=>false, 'Error_Msg' => 'В МО не зарегистрирован локальный PACS.' ) );
		}
		
		$patientid = '';
		if ( array_key_exists( 'patient_id', $params ) ) {
			$patientid = urldecode( $params['patient_id'] );
		}
		$studyuid = '';
		if ( array_key_exists( 'Study_uid', $params ) ) {
			$studyuid = urldecode( $params['Study_uid'] );
		}
		$seriesuid = '';
		if ( array_key_exists( 'series_uid', $params ) ) {
			$seriesuid = urldecode( $params['series_uid'] );
		}
		$mode = '';
		if ( array_key_exists( 'mode', $params ) ) {
			$mode = urldecode( $params['mode'] );
		} else {
			$mode = 'series';
		}
		
		$connection = array(
			'aetitle' => $lpu_local_pacs['PACS_aet'],
			'ip_address' => $lpu_local_pacs['PACS_ip_vip'],
			'hostname' => '',
			'port' => $lpu_local_pacs['PACS_port'],
			'myaetitle' => static::MYAETITLE
		);
		
		$result = $this->performCFindSeriesToPacs($connection,$patientid,$studyuid,$seriesuid,$mode);
		if ( isset( $result[0]['Error_Msg'] ) ) {
			return $result;
		}
		
		return $result;
		
	}
	
	/**
	 * Выполнение команды C-FIND к PACS
	 * 
	 * @return array
	 */
	
	public function performCFindSeriesToPacs($connection,$patientid='',$studyuid='',$seriesuid='',$mode='series') {
		
		$connection['ip_address'] = preg_replace('#\.0{1,2}[^.]#','.',$connection['ip_address']);
		require_once APPPATH.'libraries/Dicom/dicom.php';
		$assoc = new Association( $connection['ip_address'], $connection['hostname'], $connection['port'], $connection['aetitle'], $connection['myaetitle'] );
		if ( $assoc === false ) {
			return array( array('success'=>false,  'Error_Msg' => 'Не удалось подключиться к серверу '.$connection['aetitle'].' ('.$connection['ip_address'].':'.$connection['port'].').' ) );
		}
		
		$identifier = new CFindIdentifierSeries($patientid, $studyuid, $seriesuid);
		$matches = $assoc->find( $identifier, $error );
		unset($assoc);
		
		if ( strlen( $error ) ) {
			return array( array('success'=>false, 'Error_Msg' => 'Во время запроса списка исследований произошла ошибка: '.$error.'.' ) );
		} else {
			return $this->outputRemoteSeries( $identifier, $matches, $mode );
		}		
		
	}

	/**
	 * Module for querying remote studies
	 * 
	 * @param string $aetitle Имя DICOM сервера
	 * @return array
	 */
	
	public function remoteStudy( $params=array() ){
		
		if ( !isset( $params['Lpu_id'] ) || !$params['Lpu_id'] ) {
			return array( array( 'Err_MSg') );
		}
		
		// Временно отключаем поиск по устройствам
		//		$lpu_pacs_list = $this->getLpuPacsList( $params['Lpu_id'] );
		//		if ( !sizeof( $lpu_pacs_list ) ) {
		//			return array( array( 'Error_Msg' => 'В МО не зарегистрировано ни одного устройства.' ) );
		//		}
		
		$lpu_local_pacs_all = $this->getLpuLocalPacs( $params['Lpu_id'], $params['MedService_id'] ); //#146135
		if ( !is_array( $lpu_local_pacs_all ) || count($lpu_local_pacs_all) == 0 ) {
			return [[ 'Error_Message' => 'В МО не зарегистрирован локальный PACS.' ]];
		}
		else if ( empty($lpu_local_pacs_all[0]['LpuEquipmentPacs_id']) ) {
			return [[ 'Error_Msg' => 'Для службы не указан PACS Сервер. Заполните поле "PACS Сервер" для службы с типом "Диагностика" в структуре МО' ]];
		} // если в МО не один PACS
		//
		// Настройки запроса
		//
		
		$patientid = '';
		if ( array_key_exists( 'patient_id', $params ) ) {
			$patientid = urldecode( $params['patient_id'] );
		}
		
		$studyid = '';
		if ( array_key_exists( 'study_id', $params ) ) {
			$studyid = urldecode( $params['study_id'] );
		}
		
		$accession = '';
		if ( array_key_exists( 'accession', $params ) ) {
			$accession = urldecode( $params['accession'] );
		}
		
		$accession = '';
		if ( array_key_exists( 'accession', $params ) ) {
			$accession = urldecode( $params['accession'] );
		}
		
		$referdoc = '';
		if ( array_key_exists( 'doclast', $params ) && strlen( $params['doclast'] ) ) {
			$referdoc .= urldecode( $params['doclast'] );
		}
		if ( array_key_exists( 'docfirst', $params ) && strlen( $params['docfirst'] ) ) {
			$referdoc .= '^'.urldecode( $params['docfirst'] );
		}

		$params['date'] = $date = '';
		if ( array_key_exists( 'begDate', $params ) ) {
			$params['date'] = $params['begDate'];
			$params['date_type'] = 3;
			if ( array_key_exists( 'endDate', $params ) ) {
				$params['date_type'] = 4;
				$params['from'] = $params['begDate'];
				$params['to'] = $params['endDate'];
			}
		}
		if ( array_key_exists( 'date_type', $params ) || array_key_exists( 'date', $params ) ) {
			switch( $params['date_type'] ) {
				case 1:
					$date = date('Ymd');
				break;
			
				case 2:
					$date = date('Ymd',strtotime('-1 day'));
				break;
			
				case 3:
					$date = urldecode( $params['date'] );
					$time = strtotime( $date );
					$date = date('Ymd',$time);
				break;
			
				case 4:
					// convert to "YYYYMMDD-YYYYMMDD" format
					$from = '';
					if ( strlen( $params['from'] ) ) {
						$from = urldecode( $params['from'] );
						$from = date('Ymd',strtotime($from));
					}
					$to = '';
					if ( strlen( $params['to'] ) ) {
						$to = urldecode( $params['to'] );
						$to = date('Ymd',strtotime($to));
					}
					$date = $from.'-'.$to;
				break;
				
				default:
					return array( array( 'Error_Msg' => 'Формат даты указан неверно: '.$date.'.' ) );
				break;
			}
		}
		
		$result_all = array();
		foreach ($lpu_local_pacs_all as $lpu_local_pacs) {
			if (empty($lpu_local_pacs['LpuEquipmentPacs_id'])) {
				continue;
			}

			$connection = array(
				'aetitle' => $lpu_local_pacs['PACS_aet'],
				'ip_address' => $lpu_local_pacs['PACS_ip_vip'],
				'hostname' => '',
				'port' => $lpu_local_pacs['PACS_port'],
				'myaetitle' => static::MYAETITLE
			);
			
			//$result = $this->performCFindToPacs($connection,$studyid,$date,$accession,$referdoc,$patientid);
			//if ( isset($result[0]) && isset($result[0]['Error_Msg']) ) {
			//	return $result;
			//}			
			//$result =toAnsiR($result);
			$resp = $this->queryResult("
				select 
					top 1
					EU.EvnUslugaPar_id
				FROM dbo.v_EvnUslugaPar EU with(nolock)
					left join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = EU.UslugaComplex_id
					where 
					(1=1) 
					and (uc.UslugaComplex_Code like '%A05.10.002%' or uc.UslugaComplex_Code like '%A05.10.006%' or uc.UslugaComplex_Code like '%A05.10.004%')
					and EU.EvnUslugaPar_id = :EvnUslugaPar_id
				", array(
					'EvnUslugaPar_id' => $params['EvnUslugaPar_id']
			));
			if (!empty($resp[0]['EvnUslugaPar_id']) && getRegionNick() == 'ufa') {
				$paramsQueryString = http_build_query(array(
					'PatientID'=>$params['EvnUslugaPar_id'],
					'PatientName'=>'',
					'PatientBirthDate'=>'',
					'StudyDate'=>$date,
					'StudyModality'=>'',
					'StudyNumber'=>'',
					'AeTitle'=>$lpu_local_pacs['PACS_aet'],
					'HostName'=>$lpu_local_pacs['PACS_ip_vip'],
					'Port'=>$lpu_local_pacs['PACS_port'],
					'WadoPort'=>$lpu_local_pacs['PACS_wado'],
					'DCMProtocol'=>'DICOM'));
			} else {
				$paramsQueryString = http_build_query(array(
					'PatientID'=>'',
					'PatientName'=>'',
					'PatientBirthDate'=>'',
					'StudyDate'=>$date,
					'StudyModality'=>'',
					'StudyNumber'=>'',
					'AeTitle'=>$lpu_local_pacs['PACS_aet'],
					'HostName'=>$lpu_local_pacs['PACS_ip_vip'],
					'Port'=>$lpu_local_pacs['PACS_port'],
					'WadoPort'=>$lpu_local_pacs['PACS_wado'],
					'DCMProtocol'=>'DICOM'));
			}
				
			$queryString = 'http://'.PACS_SERVICE_IP.':'.PACS_SERVICE_PORT.'/'.((defined('PACS_SERVICE_NAME'))?PACS_SERVICE_NAME:'DCMWebService').'/rest/PatientStudyInfo?'.$paramsQueryString;

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $queryString);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_ENCODING, 1);
			$result = curl_exec($ch);
			curl_close($ch);

			$result = json_decode($result, true);
			if (($result == NULL)||(!isset($result['objectStudy']))) {
				return array(array('Error_Msg'=>'Неверно сформированный ответ PACS-сервиса'));
			}
			$result = $result['objectStudy'];

			if (count($result)!=0) {
				$ARFilterParams = array();
				$ARSqlParamsArray = array();
				$prmName = 'param';				
				foreach( $result as $k => $v ) {
					$result[$k]['study_uid'] = $v['studyID'];
					$result[$k]['study_date'] = preg_replace('#^([0-9]{4})([0-9]{2})([0-9]{2})$#', '$3.$2.$1', $v['studyDate']);
					$result[$k]['modality'] = $v['modalitiesInStudy'];
					$result[$k]['study_description'] = $v['studyDescription'];
					$result[$k]['patient_name'] = $v['patientName'];
					$result[$k]['patient_id'] = $v['patientID'];
					//$result[ $k ]['study_time'] = preg_replace( '#^([0-9]{2})([0-9]{2})([0-9]{2})((\.\d*)|$)#', '$1:$2:$3', $v['study_time'] );
					$result[$k]['LpuEquipmentPacs_id'] = $lpu_local_pacs['LpuEquipmentPacs_id'];
					//Добавим генерацию ссылки в модель, чтобы на клиенте меньше времени тратить на это при загрузке данных
					$region = (empty($params['session']['region']['number'])) ? 0 : $params['session']['region']['number'];
					$result[$k]['link_to_oviyam'] = $this->_getViewerLink($lpu_local_pacs['PACS_ip_local'], $lpu_local_pacs['PACS_wado'], $result[$k]['studyID'], $region);
				}

				foreach( $result as $k => $v ) {
					$ARFilterParams[] = ":" . $prmName . $k;
					$ARSqlParamsArray[$prmName . $k] = $result[$k]['study_uid'];

					if (count($ARFilterParams) >= 1000) { // насобирали 1000 параметров, пора выполнять запрос
						//AR- Associated Researches
						$ARFilter = "EUPAR.Study_uid IN (" . implode(",", $ARFilterParams) . ")";

						if (!empty($params['EvnUslugaPar_id'])) {
							$ARFilter .= 'AND (EUPAR.EvnUslugaPar_id != :EvnUslugaPar_id)';
							$ARSqlParamsArray['EvnUslugaPar_id'] = $params['EvnUslugaPar_id'];
						}

						$ARsql = "
							Select
								EUPAR.Study_uid as study_uid
							From
								EvnUslugaParAssociatedResearches EUPAR with(nolock)
								inner join v_EvnUslugaPar EUP with(nolock) on EUP.EvnUslugaPar_id = EUPAR.EvnUslugaPar_id
							Where
								{$ARFilter}
						";

						$ARResult = $this->db->query($ARsql, $ARSqlParamsArray);
						if (is_object($ARResult)) {
							$ARResultArray = $ARResult->result('array');
							if ((!empty($ARResultArray[0])) && (!empty($ARResultArray[0]['Error_Msg']))) {
								return $ARResultArray;
							}

							foreach ($result as $key => $PACSResearch) {
								$foundAndDeleted = false;
								for ($i = 0; ($i < count($ARResultArray)) && (!$foundAndDeleted); $i++) {
									if ($PACSResearch['study_uid'] == $ARResultArray[$i]['study_uid']) {
										$foundAndDeleted = true;
										unset($result[$key]);
									}
								}
							}

							$result_all = array_merge($result_all, $result);
						} else {
							return false;
						}

						$ARFilterParams = array();
						$ARSqlParamsArray = array();
					}
				}

				if (count($ARFilterParams) > 0) { // если есть параметры, выполняем запрос
					//AR- Associated Researches
					$ARFilter = "EUPAR.Study_uid IN (" . implode(",", $ARFilterParams) . ")";

					if (!empty($params['EvnUslugaPar_id'])) {
						$ARFilter .= ' AND (EUPAR.EvnUslugaPar_id != :EvnUslugaPar_id)';
						$ARSqlParamsArray['EvnUslugaPar_id'] = $params['EvnUslugaPar_id'];
					}

					$ARsql = "
						Select
							EUPAR.Study_uid as study_uid
						From
							EvnUslugaParAssociatedResearches EUPAR with(nolock)
							inner join v_EvnUslugaPar EUP with(nolock) on EUP.EvnUslugaPar_id = EUPAR.EvnUslugaPar_id
						Where
							{$ARFilter}
					";

					$ARResult = $this->db->query($ARsql, $ARSqlParamsArray);
					if (is_object($ARResult)) {
						$ARResultArray = $ARResult->result('array');
						if ((!empty($ARResultArray[0])) && (!empty($ARResultArray[0]['Error_Msg']))) {
							return $ARResultArray;
						}

						foreach ($result as $key => $PACSResearch) {
							$foundAndDeleted = false;
							for ($i = 0; ($i < count($ARResultArray)) && (!$foundAndDeleted); $i++) {
								if ($PACSResearch['study_uid'] == $ARResultArray[$i]['study_uid']) {
									$foundAndDeleted = true;
									unset($result[$key]);
								}
							}
						}

						$result_all = array_merge($result_all, $result);
					} else {
						return false;
					}
				}
			}
			 
		}
		return $result_all;
		
		
		/*
		$transParams = array();
		$transParams['action'] = 'cfind';
		$transParams['data'] = array();
		$transParams['data']['AETReceiver'] = $LpuRecieverPACS['Lpu_LocalPacsServerAetitle'];//'DCM4CHEE'; //
		$transParams['data']['begDate'] = $params['begDate'];
		$transParams['data']['endDate'] = $params['endDate'];
		$transParams['data']['WorkStations'] = $LpuPACSresult;
		array_walk_recursive($transParams, 'ConvertFromWin1251ToUTF8');
		$transParams['data'] = json_encode($transParams['data']);
		
		$query = "
			SELECT
				EUP.Study_uid
			FROM
				v_EvnUslugaPar EUP with (nolock)
			WHERE
				ISNULL(Study_uid,'0') <> '0'
			";
		$result = $this->db->query($query);
		if ( is_object($result) ) {
			$existedStudies = $result->result('array');
		} else {
			return false;
		}
		
		$existedStudyUIDs = array();
		foreach ($existedStudies as $value) {
			$existedStudyUIDs[]=$value['Study_uid'];
		}
		
		
		$this->load->helper('PostRequest');
		var_dump( $transParams );exit;
		$postRequestResult = PostRequest($transParams, $LpuRecieverPACS['Lpu_LocalPacsServerIP'], $LpuRecieverPACS['Lpu_LocalPacsServerPort']);//'pacsdicom.com','80'); //
		if (isset($postRequestResult[0])&&isset($postRequestResult[0]['success'])&&$postRequestResult[0]['success']) {
			$postRequestResult[0]['data'] = json_decode($postRequestResult[0]['data'], true);
			
			foreach ($postRequestResult[0]['data'] as $key => $value) {
				if (in_array($value['Study_UID'], $existedStudyUIDs) ) {
					unset($postRequestResult[0]['data'][$key]);
				}
			}
			
			array_walk_recursive($postRequestResult, 'ConvertFromUTF8ToWin1251');
			return $postRequestResult[0]['data'];
		} else {
			return $postRequestResult[0];
		}
		*/
	}
	
	/**
	 * Возвращает обработанный список серий определенного исследования, полученных с сервера или устройства
	 * 
	 * @param type $identifier
	 * @param type $matches
	 * @param type $mode ['series','count']
	 * @return type
	 */
	public function outputRemoteSeries( $identifier, $matches, $mode){
		$mode = (in_array($mode, array('series','count')))?$mode:'series';
		if ($mode == 'count') {
			return count($matches);
		} else {
			return false;
			//@TODO: Если будет необходимость выводить серии, можно модифицировать метод displayRemoteImage() из models\Dicom\display.php
		}
		
	}
	
	
	/**
	 * Возвращает обработанный список полученных с сервера или устройства исследования
	 * 
	 * @param type $identifier
	 * @param type $matches
	 * @return type
	 */
	public function outputRemoteStudies( $identifier, $matches/*, $existedStudyUIDs*/ ){
		
		$output = array();	
		
		$count = 0;
		// some AEs (e.g., CONQUEST) returns 2 C-FIND datasets with one of them being empty
		foreach ( $matches as $match ) if ( sizeof( $match->attrs ) ) {
			$count++;
		}
		if ( $count ) {
			$checkbox = 1;
		}
		
		//
		// Получаем заголовки столбцов
		//
		
		$attrs = $identifier->attrs;
		foreach ( $attrs as $attr ) {
			if ( $attr == 0x00200010 ) {
				continue;
			}
			$name = $identifier->getAttributeName( $attr );
			// display Study ID instead of Study UID
			if ( $attr == 0x0020000d ) {
				$name = pacsone_gettext("Study ID");
			}
		}
		
		//
		// Получаем значения столбцов построчно
		//
		
		$i = 0;
		foreach ( $matches as $match ) {
			if ( !sizeof( $match->attrs ) ) {
				continue;
			}
			
			$output[ $i ] = array();
			
			$level = $match->getQueryLevel();
			//print "<input type='hidden' name='level[]' value=$level>";
			if ( $checkbox ) {
				$uid = urlencode( $match->getStudyUid() );
				$output[ $i ]['study_uid'] = $uid;
			}
			
			foreach ( $attrs as $key ) {
				if ( $key == 0x00200010 ) {
					continue;
				}
				if ( $match->hasKey( $key ) ) {
					$value = $match->attrs[ $key ];
					if ( $key == 0x0020000d ) {
						$patientid = urlencode( $match->getPatientId() );
						//$href = "remoteSeries.php?aetitle=$aetitle&patientid=$patientid&uid=$value";
						$value = "Study Details";
						if ( isset( $match->attrs[ 0x00200010 ] ) && strlen( $match->attrs[ 0x00200010 ] ) ) {
							$value = $match->attrs[ 0x00200010 ];
						}
						//print "<td><a href=$href>$value</a></td>";
					} else {
						$value = trim( $value );
						if ( strlen( $value ) ) {
							//print "<td>$value</td>";
						} else {
							//print "<td>" . pacsone_gettext("N/A") . "</td>";
							$value = pacsone_gettext("N/A");
						}
					}
				} else {
					//print "<td>" . pacsone_gettext("N/A") . "</td>";
					$value = pacsone_gettext("N/A");
				}
				
				// Для вывода в грид определим имена ключей более понятным языком
				switch( $key ){
					// Study Date
					case 0x00080020: $grid_key = 'study_date';break;
					// Study Date
					case 0x00080030: $grid_key = 'study_time';break;
					// Study Description
					case 0x00081030: $grid_key = 'study_description'; break;
					// Patient's Name
					case 0x00100010: $grid_key = 'patient_name'; break;
					// Patient ID
					case 0x00100020: $grid_key = 'patient_id'; break;
					// display Study ID instead of Study UID
					case 0x0020000d: $grid_key = 'study_id'; break;
					// Number of Study Related Instances
					case 0x00201208: $grid_key = 'number_of_study_related_instances'; break;
					//modality
					case 0x00080060: $grid_key = 'modality'; break;
					default: $grid_key = null; break;
				}
				if ( $grid_key !== null ) {
					$output[ $i ][ $grid_key ] = $value;
				}
			}
			
			$i++;		
		}
		return $output;
	}
	
	/**
	 * Удаление всех ранее привязанных к услуге исследований
	 * 
	 * @param array $data
	 * @return array
	 */
	public function removeAssociatedResearches($data) {
		if ( !array_key_exists( 'EvnUslugaPar_id', $data ) || !$data['EvnUslugaPar_id'] ) {
			return array( array( 'Error_Msg' => 'Не указан идентификатор оказываемой услуги.' ) );
		}
		$sql = "
			DECLARE
				@EvnUslugaPar_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
				
			SET @EvnUslugaPar_id = :EvnUslugaPar_id;

			EXEC p_EvnUslugaParAssociatedResearches_delByEvnUslugaParId
				@EvnUslugaPar_id = @EvnUslugaPar_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg
		";
		$query = $this->db->query($sql,array(
			'EvnUslugaPar_id' => $data['EvnUslugaPar_id']
		));
		if ( is_object( $query ) ) {
			return $query->result_array();
		} else {
			return false;
		}
	}
	/**
	 *
	 */
	public function file_get_contents_utf8($fn) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $fn);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_ENCODING, 1);
		return curl_exec($ch);
		curl_close($ch);
   }
   /**
	* Получить строку между
	*/
	function getStringsBetween($str, $start='[', $end=']', $with_from_to=true){
		$arr = [];
		$last_pos = 0;
		$last_pos = strpos($str, $start, $last_pos);
		while ($last_pos !== false) {
			$t = strpos($str, $end, $last_pos);
			$arr[] = ($with_from_to ? $start : '').substr($str, $last_pos + 1, $t - $last_pos - 1).($with_from_to ? $end : '');
			$last_pos = strpos($str, $start, $last_pos+1);
		}
		return $arr;
	}
	/**
	 * Удаление всех ранее привязанных к услуге исследований
	 * 
	 * @param array $data
	 * @return array
	 */
	public function AssociateResearcheWithEvnUslugaPar($data) {
		
		if ( !array_key_exists( 'EvnUslugaPar_id', $data ) || !$data['EvnUslugaPar_id'] ) {
			return array( array( 'Error_Msg' => 'Не указан идентификатор оказываемой услуги.' ) );
		}
		if ( !array_key_exists( 'study_uid', $data ) || !$data['study_uid'] ) {
			return array( array( 'Error_Msg' => 'Не указан идентификатор исследования.' ) );
		}
		
		if ( !array_key_exists( 'LpuEquipmentPacs_id', $data ) || !$data['LpuEquipmentPacs_id'] ) {
			return array( array( 'Error_Msg' => 'Не указан идентификатор станции исследования.' ) );
		}
		
		$data['EvnUslugaParAssociatedResearches_id']=(!array_key_exists( 'EvnUslugaParAssociatedResearches_id', $data )||!$data['EvnUslugaParAssociatedResearches_id'])?null:$data['EvnUslugaParAssociatedResearches_id'];
		$data['study_date'] = (!array_key_exists( 'study_date', $data )||!$data['study_date'])?null:$data['study_date'];
		$data['study_time'] = (!array_key_exists( 'study_time', $data )||!$data['study_time'])?null:$data['study_time'];
		$data['patient_name'] = (!array_key_exists( 'patient_name', $data )||!$data['patient_name'])?null:$data['patient_name'];
		$data['LpuEquipmentPacs_id'] = (!array_key_exists( 'LpuEquipmentPacs_id', $data )||!$data['LpuEquipmentPacs_id'])?null:$data['LpuEquipmentPacs_id'];
		
		$resp = $this->queryResult("
				select 
					top 1
					EU.EvnUslugaPar_id
				FROM dbo.v_EvnUslugaPar EU with(nolock)
					left join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = EU.UslugaComplex_id
					where 
					(1=1) 
					and (uc.UslugaComplex_Code like '%A05.10.002%' or uc.UslugaComplex_Code like '%A05.10.006%' or uc.UslugaComplex_Code like '%A05.10.004%')
					and EU.EvnUslugaPar_id = :EvnUslugaPar_id
				", array(
					'EvnUslugaPar_id' => $data['EvnUslugaPar_id']
		));
		$data['EvnUslugaParConclusion'] = null;
		if (!empty($resp[0]['EvnUslugaPar_id']) && getRegionNick() == 'ufa') {
			$StudyData = $this->getStudyData($data);

			foreach ($StudyData as $instance) {
				$sopUID = $instance['instances'][0]['sopUID'];
			}

			$PACS_settings = $this->getPacsSettings($data);
			$PACS_settings = $PACS_settings[0];
			$context = "http://{$PACS_settings['PACS_ip_vip']}:{$PACS_settings['PACS_wado']}/wado?requestType=WADO&studyUID=&seriesUID=&objectUID={$sopUID}&contentType=application%2Fdicom";
			$rescontext = @file_get_contents($context);
				if ($rescontext == false) {
					$context = "http://".PROMED_PACS_IP.":".PROMED_PACS_WADOPORT."/wado?requestType=WADO&studyUID=&seriesUID=&objectUID={$sopUID}&contentType=application%2Fdicom";
					$rescontext = file_get_contents($context);
				}
			$signature = substr($rescontext, 128, 4);
			if (strcmp($signature, "DICM") == 0) {
				$order = array("\n");
				$replace = '<br/>';
				$ConclusionECG = $this->getStringsBetween($rescontext, 'LT', 'UI', false );
				$ConclusionECG = str_replace($order, $replace, (substr($ConclusionECG[0], 3)));
				$ConclusionECG = preg_replace("/[^ \-а-яёa-z0-9,.<>]/iu", '', $ConclusionECG);
				$ConclusionECG = str_replace('LT', '', $ConclusionECG);
				$ConclusionECG = preg_replace('/\s+/', ' ', $ConclusionECG);
				$data['EvnUslugaParConclusion'] = trim($ConclusionECG);
				if ($data['EvnUslugaParConclusion'] == '') {
					$data['EvnUslugaParConclusion'] = null;
				}
			}
		}

		$sql = "
			DECLARE
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
				
			SET @Res = :EvnUslugaParAssociatedResearches_id;

			EXEC p_EvnUslugaParAssociatedResearches_ins
				@EvnUslugaParAssociatedResearches_id = @Res output,
				@EvnUslugaPar_id  = :EvnUslugaPar_id,
				@Study_uid  = :study_uid,
				@Study_date  = :study_date ,
				@Study_time  = :study_time,
				@Patient_Name  = :patient_name,
				@EvnUslugaParConclusion = :EvnUslugaParConclusion,
				@LpuEquipmentPacs_id  = :LpuEquipmentPacs_id,
				@pmUser_id = :pmUser_id,			
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as EvnUslugaParAssociatedResearches_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg
		";
		
		$query = $this->db->query($sql,array(
			'EvnUslugaParAssociatedResearches_id' => $data['EvnUslugaParAssociatedResearches_id'],
			'EvnUslugaPar_id' => $data['EvnUslugaPar_id'],
			'study_uid' => $data['study_uid'],
			'study_date' => $data['study_date'],
			'study_time' => $data['study_time'],
			'patient_name' => $data['patient_name'],
			'EvnUslugaParConclusion' => $data['EvnUslugaParConclusion'],
			'LpuEquipmentPacs_id' => $data['LpuEquipmentPacs_id'],
			'pmUser_id' => $data['pmUser_id']
		));
		if ( is_object( $query ) ) {
			return $query->result_array();
		} else {
			return false;
		}
	}
	
	/**
	 * Получение списка прикрепленных к услуге исследований
	 * 
	 * @param array $data
	 * @return array
	 */
	public function getAssociatedResearches($data) {
		if ( !array_key_exists( 'EvnUslugaPar_id', $data ) || !$data['EvnUslugaPar_id'] ) {
			return array( array( 'Error_Msg' => 'Не указан идентификатор оказываемой услуги.' ) );
		}
		$sql='
			SELECT
				EUPAR.EvnUslugaParAssociatedResearches_id,
				EUPAR.Study_uid as study_uid,
				EUPAR.Study_date as study_date,
				EUPAR.Study_time as study_time,
				EUPAR.Patient_name as patient_name,
				EUPAR.LpuEquipmentPacs_id as LpuEquipmentPacs_id,
				LEP.Lpu_id
			FROM
				v_EvnUslugaParAssociatedResearches EUPAR with(nolock)
				left join v_LpuEquipmentPacs LEP with (nolock) ON( LEP.LpuEquipmentPacs_id = EUPAR.LpuEquipmentPacs_id )
			WHERE
				EUPAR.EvnUslugaPar_id = :EvnUslugaPar_id
			';
		
		$query = $this->db->query($sql,array(
			'EvnUslugaPar_id' => $data['EvnUslugaPar_id']
		));
		
		if ( is_object( $query ) ) {
			$researches = $query->result_array();
		} else {
			return false;
		}

		$checkDigiPacsViewer = $this->queryResult("
			SELECT DataStorage_Value as \"DataStorage_Value\"
			FROM DataStorage
			WHERE Lpu_id = :Lpu_id and DataStorage_Name = 'digiPacsAddress'
		", [
			'Lpu_id' => $data['Lpu_id']
		]
		);
		$digiPacs = '';

		if(!empty($checkDigiPacsViewer) || !empty($checkDigiPacsViewer[0]['DataStorage_Value'])) {
			$digiPacs = $checkDigiPacsViewer[0]['DataStorage_Value'];
		}
		
		$resultArray=array();
		
		foreach ($researches as $ind => $research) {
			
			$studies = $this->remoteSeries(array(
				'LpuEquipmentPacs_id'=>$research['LpuEquipmentPacs_id'],
				'Study_uid'=>$research['study_uid'],
				'mode'=>'count'
			));
			if (!empty($_REQUEST['dicom_debug'])) {
				var_dump($studies);
			}
			
			$link_to_oviyam = '';
			
			$region = (empty($params['session']['region']['number']))?(empty($_SESSION['region']['number'])?0:$_SESSION['region']['number']):$params['session']['region']['number'];

			if ($studies && empty($studies[0]['Error_Msg'])) {
				$this->load->model('LpuPassport_model','LpuPassport_model');
				$LpuEquipmentPacsService = $this->LpuPassport_model->loadLpuEquipment(array(
					'LpuEquipmentPacs_id'=>$research['LpuEquipmentPacs_id'],
					'Lpu_id' => $research['Lpu_id'],
				));

				if (!$LpuEquipmentPacsService || !isset($LpuEquipmentPacsService[0]) || isset($LpuEquipmentPacsService[0]['Error_Msg'])) {
					return $LpuEquipmentPacsService;
				}

				if ($data['Lpu_id']==$research['Lpu_id']) {
					//ищем лоакльно в больнице
					$link_to_oviyam = $this->_getViewerLink($LpuEquipmentPacsService[0]['PACS_ip_local'], $LpuEquipmentPacsService[0]['PACS_wado'], $research['study_uid'], $region);
				} else {
					//ищем лоакльно в др. больнице
					$link_to_oviyam = $this->_getViewerLink($LpuEquipmentPacsService[0]['PACS_ip_vip'], $LpuEquipmentPacsService[0]['PACS_wado'], $research['study_uid'], $region);
				}
			} else {
				//Ищем на глобальном
				if (!defined('PROMED_PACS_IP')||!defined('PROMED_PACS_WADOPORT')||!defined('PROMED_PACS_AETITLE')||!defined('PROMED_PACS_PORT')) {
					return array(array('success'=>false,'Error_Msg'=>'Не определены константы глобального PACS'));
				}
				$link_to_oviyam = $this->_getViewerLink(PROMED_PACS_IP, PROMED_PACS_WADOPORT, $research['study_uid'], $region);
			}
			$researches["$ind"]['link_to_oviyam'] = $link_to_oviyam;
			$researches["$ind"]['digiPacs_ip'] = $digiPacs;
		}
		
		return $researches;
	}

	/**
	 * Проверка синтаксиса выражения CRON
	 * 
	 * @param type $expr
	 * @return boolean
	 */
	public function checkCronExpression($expr) {
		/*
		 * Описание ключей массива
		 *	0 - секунды
		 *  1 - минуты
		 *	2 - часы
		 *  3 - дни месяца
		 *	4 - месяцы
		 *  5 - дни недели
		 *  6 - годы
		 * 
		 * Сведем проверку каждого типа времени к проверке морфологии, и синтаксиса
		 * Проверку семантики оставим за парсером CRON в стороннем сервисе, куда мы отправляем запрос
		 * Хотя, как выяснилось, семантика там проверяется плохо или не проверяется вообще.
		 */
		
		
		//Флаг: неопределено ли поле День месяца
		$empty_day_of_month = false;
		//Флаг: неопределено ли поле День недели
		$empty_day_of_week = false;
		//Делим строку CRON-запроса на поля
		$field_array = preg_split("/[\s]+/",$expr);
		
		if (sizeof( $field_array) < 6 || sizeof( $field_array) > 7) 
			return $this->createError(null, 'Крон выражение должно содержать от 6 до 7 параметров, разделенных одиночными пробелами');

		foreach ($field_array as $ind => $field) {
			
			switch ($ind) {
				case 0:
					//Секунды
					if ($ind == 0) {
						$field_name = 'Секунды';
						//Разрешенные числовые значения
						$numeric_regex_part = '(\d)|([1-4]\d)|(5\d)';
						//Правое пограничное значение
						$limit = '59';
						//Левое пограничное значение
						$start = '0';
						//Выражения, к которым нельзя принимать шаг (<значение|интервал>/<шаг>)
						$single_interval_only = null;
						//Разрешенные входные символы
						$symbol_check_regex = '/^([\*,\/\-0-9]+)$/';
						//Разрешенные выражения для шага (<значение|интервал>/<шаг>)
						$step_regex = "/^({$numeric_regex_part})$/";
						$step_error = ": шаг должен представлять из себя значение от {$start} до {$limit}, дано: ";
						
						//Разрешенные выражения для значения (<значение|интервал>/<шаг>)
						$single_interval_regex = "/^((\*)|({$numeric_regex_part}))$/";
						$single_interval_error = ": значение задается символом '*' или значением от {$start} до {$limit}, дано: ";
						
						//Разрешенные выражения для интервала (<значение|интервал>/<шаг>)
						$multiple_interval_regex = "/^(({$numeric_regex_part})-({$numeric_regex_part}))$/";
						$multiple_interval_error = ": интервал задается по схеме <значение от {$start} до {$limit}>-<значение от {$start} до {$limit}> дано: ";
					}
				case 1:
					//Минуты
					if ($ind == 1) {
						$field_name = 'Минуты';
						//Разрешенные числовые значения
						$numeric_regex_part = '(\d)|([1-4]\d)|(5\d)';
						//Правое пограничное значение
						$limit = '59';
						//Левое пограничное значение
						$start = '0';
						//Выражения, к которым нельзя принимать шаг (<значение|интервал>/<шаг>)
						$single_interval_only = null;
						//Разрешенные входные символы
						$symbol_check_regex = '/^([\*,\/\-0-9]+)$/';
						//Разрешенные выражения для шага (<значение|интервал>/<шаг>)
						$step_regex = "/^({$numeric_regex_part})$/";
						$step_error = ": шаг должен представлять из себя значение от {$start} до {$limit}, дано: ";
						//Разрешенные выражения для значения (<значение|интервал>/<шаг>)
						$single_interval_regex = "/^((\*)|({$numeric_regex_part}))$/";
						$single_interval_error = ": значение задается символом '*' или значением от {$start} до {$limit}, дано: ";
						//Разрешенные выражения для интервала (<значение|интервал>/<шаг>)
						$multiple_interval_regex = "/^(({$numeric_regex_part})-({$numeric_regex_part}))$/";
						$multiple_interval_error = ": интервал задается по схеме <значение от {$start} до {$limit}>-<значение от {$start} до {$limit}> дано: ";
					}
				case 2:
					//Часы
					if ($ind == 2) {
						$field_name = 'Часы';
						//Разрешенные числовые значения
						$numeric_regex_part = '(\d)|(1\d)|(2[0-3])';
						//Правое пограничное значение
						$limit = '23';
						//Левое пограничное значение
						$start = '0';
						//Выражения, к которым нельзя принимать шаг (<значение|интервал>/<шаг>)
						$single_interval_only = null;
						//Разрешенные входные символы
						$symbol_check_regex = '/^([\*,\/\-0-9]+)$/';
						//Разрешенные выражения для шага (<значение|интервал>/<шаг>)
						$step_regex = "/^({$numeric_regex_part})$/";
						$step_error = ": шаг должен представлять из себя значение от {$start} до {$limit}, дано: ";
						
						//Разрешенные выражения для значения (<значение|интервал>/<шаг>)
						$single_interval_regex = "/^((\*)|({$numeric_regex_part}))$/";
						$single_interval_error = ": значение задается символом '*' или значением от {$start} до {$limit}, дано: ";
						//Разрешенные выражения для интервала (<значение|интервал>/<шаг>)
						$multiple_interval_regex = "/^(({$numeric_regex_part})-({$numeric_regex_part}))$/";
						$multiple_interval_error = ": интервал задается по схеме <значение от {$start} до {$limit}>-<значение от {$start} до {$limit}> дано: ";
					}
				case 3:
					//Дни месяца
					if ($ind == 3) {
						
						if ($empty_day_of_month = ($field == '?')) {
							break;
						}
						
						$field_name = 'Дни месяца';
						//Разрешенные числовые значения
						$numeric_regex_part = '([1-9])|([1-2]\d)|(3[0-1])';
						//Правое пограничное значение
						$limit = '31';
						//Левое пограничное значение
						$start = '1';
						//Выражения, к которым нельзя принимать шаг (<значение|интервал>/<шаг>)
						$single_interval_only = '/^(LW?)$/';
						//Разрешенные входные символы
						$symbol_check_regex = '/^([\*,\/\-0-9LW]+)$/';						
						//Разрешенные выражения для шага (<значение|интервал>/<шаг>)
						$step_regex = "/^({$numeric_regex_part})$/";
						$step_error = ": шаг должен представлять из себя значение от {$start} до {$limit}, дано: ";
						
						//Разрешенные выражения для значения (<значение|интервал>/<шаг>)
						$single_interval_regex = '/^((\*)|(('.$numeric_regex_part.'|L)W?))$/';
						$single_interval_error = ": значение задается символом '*', значением от {$start}[W] до {$limit}[W], L[W], дано: ";
						//Разрешенные выражения для интервала (<значение|интервал>/<шаг>)
						$multiple_interval_regex = "/^(({$numeric_regex_part})-({$numeric_regex_part}))$/";
						$multiple_interval_error = ": интервал задается по схеме <значение от {$start} до {$limit}>-<значение от {$start} до {$limit}> дано: ";
						
					}
				case 4:
					//Месяцы
					
					if ($ind == 4) {
						$field_name = 'Месяцы';
						//Разрешенные числовые значения
						$numeric_regex_part = '([1-9])|(1[1-2])';
						//Правое пограничное значение
						$limit = '12';
						//Левое пограничное значение
						$start = '1';
						//Разрешенные текстовые значения
						$text_values_regex = '(JAN)|(FEB)|(MAR)|(APR)|(MAY)|(JUN)|(JUL)|(AUG)|(SEP)|(NOV)|(DEC)';
						//Разрешенные входные символы
						$symbol_check_regex = "/^(([\*,\/\-0-9])|{$text_values_regex})+$/";
						//Разрешенные выражения для шага (<значение|интервал>/<шаг>)
						$step_regex = "/^({$numeric_regex_part})$/";
						$step_error = ": шаг должен представлять из себя значение от {$start} до {$limit}, дано: ";
						//Выражения, к которым нельзя принимать шаг (<значение|интервал>/<шаг>)
						$single_interval_only = null;
						//Разрешенные выражения для значения (<значение|интервал>/<шаг>)
						$single_interval_regex = "/^((\*)|(({$numeric_regex_part}|{$text_values_regex})?))$/";
						$single_interval_error = ": значение задается символом '*', значением от {$limit} до {$limit} или от JAN до DEC, дано: ";
						//Разрешенные выражения для интервала (<значение|интервал>/<шаг>)
						$multiple_interval_regex = "/^(({$numeric_regex_part})-({$numeric_regex_part})|($text_values_regex)-($text_values_regex))$/";
						$multiple_interval_error = ": интервал задается по схеме <значение от {$start} до {$limit}>-<значение от {$start} до {$limit}>, либо <от JAN до DEC>-<от JAN до DEC>, дано: ";
					}
				
				case 5:
					//Дни недели
					if ($ind == 5) {
						//Согласно мануалу CRON должно быть определено только одно значение: либо день недели, либо ден месяца
						//Однако восхитительный сервис, к которому мы обращаемся, не может обработать даже это
						if ($empty_day_of_week = ($field == '?')) {
							if ($empty_day_of_month) {
								return $this->createError(null, 'Дни недели и дни месяца не могут быть не определены (?) одновременно');
							}
							break;
						} else {
							if (!$empty_day_of_month) {
								return $this->createError(null, 'Дни недели и дни месяца не могут быть нопределены одновременно');
							}
						}
						
						$field_name = 'Дни недели';
						//Разрешенные числовые значения
						$numeric_regex_part = '([1-7])';
						//Правое пограничное значение
						$limit = '7';
						//Левое пограничное значение
						$start = '1';
						//Разрешенные текстовые значения
						$text_values_regex = '(MON)|(TUE)|(WED)|(THU)|(FRI)|(SAT)|(SUN)';
						//Разрешенные входные символы
						$symbol_check_regex = "/^(([\*,\/\-1-7L#])|{$text_values_regex})+$/";						
						//Разрешенные выражения для шага (<значение|интервал>/<шаг>)
						$step_regex = "/^({$numeric_regex_part})$/";
						$step_error = ": шаг должен представлять из себя значение от {$start} до {$limit}, дано: ";
						//Выражения, к которым нельзя принимать шаг (<значение|интервал>/<шаг>)
						$single_interval_only = "/^(({$numeric_regex_part}|{$text_values_regex})#{$numeric_regex_part})$/";
						//Разрешенные выражения для значения (<значение|интервал>/<шаг>)
						$single_interval_regex = "/^((\*)|(({$numeric_regex_part}|{$text_values_regex})?L?)|(({$numeric_regex_part}|{$text_values_regex})#[1-5]))$/";
						$single_interval_error = ": значение задается символом '*', значением от {$start}[L] до {$limit}[L], от MON[L] до SUN[L], L, или выражением типа 
							<день недели в числовом ({$start}-{$limit}) или текстовом (MON-SUN) виде >#<значение от 1 до 5>, 
							дано: ";
						//Разрешенные выражения для интервала (<значение|интервал>/<шаг>)
						$multiple_interval_regex = "/^((({$numeric_regex_part})-({$numeric_regex_part}))|(({$text_values_regex})-({$text_values_regex})))$/";
						$multiple_interval_error = ": интервал задается по схеме <значение от {$start} до {$limit}>-<значение от {$start} до {$limit}>, либо <от MON до SUN>-<от MON до SUN>, дано: ";
					}
				
				case 6:
					//Годы
					if ($ind == 6) {
						
						$field_name = 'Годы';
						//Разрешенные числовые значения
						$numeric_regex_part = '(19[7-9]\d)|(2[0-1]\d\d)';
						//Правое пограничное значение
						$limit = '2199';
						//Левое пограничное значение
						$start = '1970';
						//Разрешенные входные символы
						$symbol_check_regex = '/^([\*,\/\-0-9]+)$/';
						//Выражения, к которым нельзя принимать шаг (<значение|интервал>/<шаг>)
						$single_interval_only = null;
						//Разрешенные выражения для шага (<значение|интервал>/<шаг>)
						$step_regex = '/^((1?)(\d?)([1-9]))$/';
						$step_error = ": шаг должен представлять из себя значение от 1 до 199, дано: ";
						//Разрешенные выражения для значения (<значение|интервал>/<шаг>)
						$single_interval_regex = "/^((\*)|({$numeric_regex_part}))$/";
						$single_interval_error = ": значение задается символом '*' или значением от {$start} до {$limit}, дано: ";
						//Разрешенные выражения для интервала (<значение|интервал>/<шаг>)
						$multiple_interval_regex = "/^(({$numeric_regex_part})-({$numeric_regex_part}))$/";
						$multiple_interval_error = ": интервал задается по схеме <значение от {$start} до {$limit}>-<значение от {$start} до {$limit}>, дано: ";
					}
					
					//Проверяем на недопустимые символы
					if  (!(bool)preg_match($symbol_check_regex, $field)) {
						return $this->createError(null, 'Поле "'.$field_name.'" содержит недопустимые символы');
					}
					$value_array = preg_split("/,/",$field);
					
					foreach ($value_array as $value) {
						//Проверяем разделенные запятыми участки параметра
						if ($value==''){
							return $this->createError(null, $field_name.': Пустая строка между запятыми');
						}
						
						/* Проверяем части выражения, разделенные слешем, где:
						 * первая часть - интервал или начальное значение
						 * вторая часть - шаг
						 */
						$interval_step_array = preg_split("#/#",$value);
						
						
						$has_step = false;
						switch (sizeof($interval_step_array)) {
							case 2:
								$step = ($interval_step_array[1]);
								
								if (!empty($single_interval_only)&&((bool)preg_match($single_interval_only, $interval_step_array[0]))) {
									return $this->createError(null, $field_name.": интервал {$interval_step_array[0]} водится без шага, дано: {$value}");
								}
								
								if (!(bool)preg_match($step_regex, $step)) {
									return $this->createError(null, $field_name.$step_error.$step);
								}
								
								$has_step=true;
							case 1:
								$interval = $interval_step_array[0];
								
								if ($interval=='') {
									return $this->createError(null, $field_name.": Ожидался интервал, дана пустая строка: '<место_для_интервала>{$value}'");
								}
								$interval_parts = preg_split("#-#",$interval);
								
								switch (sizeof($interval_parts)) {
									
									// выражение типа <значение>[/<шаг>]
									case 1:
										if (!(bool)preg_match($single_interval_regex, $interval_parts[0])) {
											return $this->createError(null, $field_name.$single_interval_error.$interval_parts[0]);
										}
										break;	
									// выражение типа <значение1>-<значение2>[/<шаг>]
									case 2:
										if (!(bool)preg_match($multiple_interval_regex, $interval)){
											return $this->createError(null, $field_name.$multiple_interval_error.$interval);
										}
										break;
									default:
										return $this->createError(null, $field_name.': дефисом ("-") разделяется 2 числовых значения, '.sizeof($interval_step_array).' дано: '.implode(',', $interval_step_array));
								}
								break;	
							default:
								return $this->createError(null, $field_name.': слешем ("/") разделяется 2 выражения, '.sizeof($interval_step_array).' дано: '.implode(',', $interval_step_array));
						}
					}
					break;
				default :
					break;
			}
		}
		return array(array('success'=>true));
	}
	
	
	/**
	 *  * Установка параметров форвардинга изображений для срвиса
	 * 
	 * @param type $params
	 * @return type
	 */
	public function setForvardSettings( $params ){
		
		$requestSettings=array();
		
		//Заполняем массив параметров
		
		//Проверяем и устанавливаем SourceAET и DestAET
		if ( (!isset( $params['PACS_aet']))||(!defined('PROMED_PACS_AETITLE'))) {
			return array( array( 'success'=>false,'Error_Msg' => (!isset( $params['PACS_aet']))?'Не задан AETitle PACS-источника':'Не задан AETitle PACS-приемника') );
		}
		
		$requestSettings['SourceAET'] = $params['PACS_aet'];
		$requestSettings['DestAET'] = PROMED_PACS_AETITLE;

		//Проверяем и устанавливаем TaskInterval
		if ( isset( $params['PACS_Interval']) && isset( $params['PACS_Interval_TimeType_id']) ) 
		{
			if ((0>=(int)$params['PACS_Interval'])||((int)$params['PACS_Interval']>=100)) {
				return array( array( 'success'=>false,'Error_Msg' => 'Интервал должен находиться в диапазоне от 1 до 99' ) );
			}
			if (! ($interval_TimeType = $this->getTimeCodeById(array('TimeType_id'=>$params['PACS_Interval_TimeType_id'])))) {
				return array( array( 'success'=>false,'Error_Msg' => 'Ошибка при получении единицы времени интервала' ) );
			}
			$cronInterval ='';
			if (isset( $params['PACS_ExcludeTimeFrom']) && isset( $params['PACS_ExcludeTimeTo'])) {
				if ((0>=(int)$params['PACS_ExcludeTimeFrom'])||((int)$params['PACS_ExcludeTimeFrom']>=24)) {
					return array( array( 'success'=>false,'Error_Msg' => 'Крон интервал ОТ должен находиться в диапазоне от 1 до 24' ) );
				}
				if ((0>=(int)$params['PACS_ExcludeTimeTo'])||((int)$params['PACS_ExcludeTimeTo']>=24)) {
					return array( array( 'success'=>false,'Error_Msg' => 'Крон интервал ДО должен находиться в диапазоне от 1 до 24' ) );
				}
				if ((int)$params['PACS_ExcludeTimeTo']>(int)$params['PACS_ExcludeTimeTo']) {
					return array( array( 'success'=>false,'Error_Msg' => 'Крон интервал ОТ должен быть меньше или равен Крон интервалу ДО' ) );
				}
				$cronInterval = '!'.(int)$params['PACS_ExcludeTimeFrom'].'-'.(int)$params['PACS_ExcludeTimeTo'];
			}			
			
			$requestSettings['TaskInterval'] = (int)$params['PACS_Interval'].$interval_TimeType.$cronInterval;
		}
		
		
		//Проверяем и устанавливаем CronExpression
		if (isset($params['PACS_CronRequests'])) {

			if ( array_key_exists( 'PACS_CronRequests', $params ) && 
				!empty( $params['PACS_CronRequests'] ) &&
				(!is_null($cron_requests = json_decode( iconv( 'cp1251', 'utf-8',$params['PACS_CronRequests']), true )))
			) {
				$requestSettings['CronExpression'] = '';
				foreach ($cron_requests as $idx => $request) {
					$request['LpuEquipmentPacsCron_request'] = trim($request['LpuEquipmentPacsCron_request']);
					if ($request['LpuEquipmentPacsCron_request'] == '') {
						continue;
					}
					$checkCronResult = $this->checkCronExpression($request['LpuEquipmentPacsCron_request']);
					if ((!$checkCronResult)||!isset($checkCronResult[0])||!isset($checkCronResult[0]['success'])||!$checkCronResult[0]['success']) {
						if (isset($checkCronResult[0]['Error_Msg'])) {
							$checkCronResult[0]['Error_Msg'] = 'Ошибка в CRON-выражении №'.($idx+1).': '.$checkCronResult[0]['Error_Msg'];
						}
						return $checkCronResult;
					} else {
						$requestSettings['CronExpression'] .= $request['LpuEquipmentPacsCron_request'].';';
					}
				}

			} else {
				return array( array( 'success'=>false,'Error_Msg' => 'Ошибка получения крон-таймера' ) );
			}
		}
		
		//Проверяем и устанавливаем CodecName
		if (isset($params['LpuPacsCompressionType_id'])) {
			if (! ($requestSettings['CodecName'] = $this->getCompressionById(array('LpuPacsCompressionType_id'=>$params['LpuPacsCompressionType_id'])))) {
				return array( array( 'success'=>false,'Error_Msg' => 'Ошибка при получении единицы времени интервала' ) );
			}
		}
		
		//Проверяем и устанавливаем Delay
		if ( isset( $params['PACS_StudyAge']) && isset( $params['PACS_Age_TimeType_id'])) {
			if ((0>=(int)$params['PACS_StudyAge'])||((int)$params['PACS_StudyAge']>=100)) {
				return array( array( 'success'=>false,'Error_Msg' => 'Значение возраста должно находиться в диапазоне от 1 до 99' ) );
			}
			if (! ($age_TimeType = $this->getTimeCodeById(array('TimeType_id'=>$params['PACS_Age_TimeType_id'])))) {
				return array( array( 'success'=>false,'Error_Msg' => 'Ошибка при получении единицы времени возраста' ) );
			}
			
			$requestSettings['Delay'] = (int)$params['PACS_StudyAge'].$age_TimeType;
		}
		
		//Проверяем и устанавливаем DeleteStudyFromDB
		if (isset($params['PACS_DeleteFromDb'])) {
			$requestSettings['DeleteStudyFromDB'] = (bool)($params['PACS_DeleteFromDb']==='on');
		}
		
		//Проверяем и устанавливаем DeletePatientWithoutObjects
		if (isset($params['PACS_DeletePatientsWithoutStudys'])) {
			$requestSettings['DeletePatientWithoutObjects'] = (bool)($params['PACS_DeletePatientsWithoutStudys']==='on');
		}
		
		
		$src = 'http://'.$params['PACS_ip_vip'].':'.$params['PACS_wado'].'/dcm4che-cmd-servlet?'.http_build_query(array('request'=>  json_encode($requestSettings)));
		
		$ResultDataJson = @file_get_contents($src);
		if (!$ResultDataJson) {
			return array(array('success'=>false, 'Error_Msg'=>'Невозможно получить ответ PACS-сервера. Пожалуйста, проверьте настройки и доступность PACS-сервера'));
		}
		$responseData = json_decode($ResultDataJson, true);
		
		if (($responseData == NULL)||(!isset($responseData['ResponseStatus']))||(!in_array($responseData['ResponseStatus'],array('OK','ERROR')))) {
			return array(array('success'=>false, 'Error_Msg'=>'Неверно сформированный ответ PACS-сервиса форвардинга исследований. Про'));
		}
		
		if ($responseData['ResponseStatus']=='ERROR') {
			
			if (!isset($responseData['ErrorStatus'])|| !in_array($responseData['ErrorStatus'],array('Mailformed request','Internal error') )) {
				return array(array('success'=>false, 'Error_Msg'=>'Неизвестный тип ошибки: '.$responseData['ErrorStatus']));
			}
			
			if ($responseData['ErrorStatus']=='Mailformed request') {
				return array(array('success'=>false, 'Error_Msg'=>'Неверно сформированный запрос к сервису форвардинга: '.($responseData['ErrorInfo']?$responseData['ErrorInfo']:'Подробная информация отсутствует')));
			}
			
			if ($responseData['ErrorStatus']=='Internal error') {
				return array(array('success'=>false, 'Error_Msg'=>'Внутренняя ошибка сервиса форвардинга: '.($responseData['ErrorInfo']?$responseData['ErrorInfo']:'Подробная информация отсутствует')));
			}
			
		}

		if ($responseData['ResponseStatus']=='OK') {
			return array(array('success'=>true, 'Error_Msg'=>''));
		}
			
		/*
		 * ResponseStatus - всегда есть в ответе. Принимает два значения OK или ERROR. Только в случае ошибки в ответе появляются следующие два параметра.
		ErrorStatus - статус ошибки, принимает два значения Mailformed request - означает что вы неправильно сформировали запрос к сервлету, Internal error - ознаяает внутреннюю ошибку
		ErrorInfo - детальное описание ошибки
		 */
		
	}
	/**
	 * Получение типа единицы времени по идентификатору единицы времени в справочнике
	 * @param type $params
	 * @return boolean
	 */
			
	public function getTimeCodeById($params) {
		if ( !isset( $params['TimeType_id'] )) {
			return array( array(  'success'=>false,'Error_Msg' => 'Не указан идентификатор единицы времени.' ) );
		}
		$query = '
			select
				TT.TimeType_Code
			from
				v_TimeType TT with (nolock)
			where
				TT.TimeType_id = :TimeType_id
			';
		$queryParams = array(
			'TimeType_id'=>$params['TimeType_id']
		);
		
		
		$res = $this->db->query($query, $queryParams);
		if ( is_object($res) ) {
			$respArr = $res->result('array');
			return $respArr[0]['TimeType_Code'];
		}
		else {
			return false;
		}
		
	}
	/**
	 * Получение типа компрессии по идентификатору типа компрессии в справочнике
	 * @param type $params
	 * @return boolean
	 */
	public function getCompressionById($params) {
		if ( !isset( $params['LpuPacsCompressionType_id'] )) {
			return array( array(  'success'=>false,'Error_Msg' => 'Не указан идентификатор типа компрессии.' ) );
		}
		$query = '
			select
				LPCT.LpuPacsCompressionType_Name
			from
				v_LpuPacsCompressionType LPCT with(nolock)
			where
				LPCT.LpuPacsCompressionType_id = :LpuPacsCompressionType_id
			';
		$queryParams = array(
			'LpuPacsCompressionType_id'=>$params['LpuPacsCompressionType_id']
		);
		
		
		$res = $this->db->query($query, $queryParams);
		if ( is_object($res) ) {
			$respArr = $res->result('array');
			return $respArr[0]['LpuPacsCompressionType_Name'];
		}
		else {
			return false;
		}
	}


	/**
	 * Привязка Study UID к параклинической услуге
	 * 
	 * @param array $params
	 * @return array
	 */
	public function addStudyToEvnUslugaPar( $params ){
		if ( !array_key_exists( 'EvnUslugaPar_id', $params ) || !$params['EvnUslugaPar_id'] ) {
			return array( array( 'Error_Msg' => 'Не указан идентификатор оказываемой услуги.' ) );
		}
		if ( !array_key_exists( 'study_uid', $params ) || !strlen( $params['study_uid'] ) ) {
			return array( array( 'Error_Msg' => 'Не указан Study UID.' ) );
		}
		if ( !array_key_exists( 'pmUser_id', $params ) || !strlen( $params['pmUser_id'] ) ) {
			return array( array( 'Error_Msg' => 'Не указан идентификатор пользователя' ) );
		}
		$query = "			
			DECLARE
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			SET @Res = :EvnUslugaPar_id;			
			EXEC p_EvnUslugaPar_setstudy
				@EvnUslugaPar_id = @Res,
				@Study_uid = :Study_uid,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;			
			SELECT @Res as EvnUslugaPar_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$res = $this->db->query($query,array(
			'EvnUslugaPar_id' => $params['EvnUslugaPar_id'],
			'Study_uid' => $params['study_uid'],
			'pmUser_id' => $params['pmUser_id']
		));
		if ( is_object( $res ) ) {
			return $res->result_array();
			
		} else {
			return false;
		}
	}

	/**
	 * Отображение прикрепленных исследований в ЭМК
	 * @param type $data
	 * @return type
	 */
	public function getStudiesEmkView($data) {
		if ( !array_key_exists( 'EvnUslugaPar_id', $data ) || !$data['EvnUslugaPar_id'] ) {
			return array( array( 'Error_Msg' => 'Не указан идентификатор оказываемой услуги.' ) );
		}		 
		if ( !array_key_exists( 'Lpu_id', $data ) || !$data['Lpu_id'] ) {
			return array( array( 'Error_Msg' => 'Не указан идентификатор МО.' ) );
		}
		
		$researches = $this->getAssociatedResearches($data);
		
		if (!$researches || !is_array($researches)) {
			return array('success'=>false);
		}
		
		$resultArray=array();
		
		foreach ($researches as $research) {
			
			$studies = $this->remoteSeries(array(
				'LpuEquipmentPacs_id'=>$research['LpuEquipmentPacs_id'],
				'Study_uid'=>$research['study_uid'],
				'mode'=>'count'
			));

			$src = "";
			$servPort = isset($_SERVER['SERVER_PORT'])?$_SERVER['SERVER_PORT']:null;
			$src = (in_array($servPort, array(443,444,445))?'https':'http')."://";

			if ($studies && empty($studies[0]['Error_Msg'])) {

				$Lpu_Pacs_config = $this->getPacsSettings(array(
					'LpuEquipmentPacs_id'=>$research['LpuEquipmentPacs_id']
				));
				
				if (!$Lpu_Pacs_config || isset($Lpu_Pacs_config[0])&&isset($Lpu_Pacs_config[0]['Error_Msg'])) {
					return $Lpu_Pacs_config;
				}
				
				$Lpu_Pacs_config = $Lpu_Pacs_config[0];


				//$Lpu_Pacs_config['PACS_wado'] = (in_array($servPort, array(443,444,445) ))?(defined('PROMED_PACS_HTTPS_WADOPORT')?PROMED_PACS_HTTPS_WADOPORT:'8443'):$Lpu_Pacs_config['PACS_wado'];

				$this->load->model('LpuPassport_model','LpuPassport_model');
				$LpuEquipmentPacsService = $this->LpuPassport_model->loadLpuEquipment(array(
					'LpuEquipmentPacs_id'=>$research['LpuEquipmentPacs_id'],
					'Lpu_id' => $research['Lpu_id'],
				));

				if (!$LpuEquipmentPacsService || !isset($LpuEquipmentPacsService[0]) || isset($LpuEquipmentPacsService[0]['Error_Msg'])) {
					return $LpuEquipmentPacsService;
				}

				if ($data['Lpu_id'] == $research['Lpu_id']) {
					//ищем лоакльно в больнице

					$StudyData = $this->processPacsServiceRequest(array(
						'study_uid'=>$research['study_uid'],
						'Pacs_host_IP'=>$LpuEquipmentPacsService[0]['PACS_ip_vip'],
						'Port'=>$LpuEquipmentPacsService[0]['PACS_port'],
						'AeTitle'=>$LpuEquipmentPacsService[0]['PACS_aet'],
						'Img_host_IP'=>$LpuEquipmentPacsService[0]['PACS_ip_local'],
						'WadoPort'=>$LpuEquipmentPacsService[0]['PACS_wado'],
						'urlPrefix'=>'',
						'queryType'=>(isset($data['queryType']))?$data['queryType']:''
					));
					$link = $this->_getViewerLink( $LpuEquipmentPacsService[0]['PACS_ip_local'] , $LpuEquipmentPacsService[0]['PACS_wado'], $research['study_uid'], $this->getRegionNumber(),true);
					//$src .= $Lpu_Pacs_config['PACS_ip_local'].':'.(($Lpu_Pacs_config['PACS_wado'])?$Lpu_Pacs_config['PACS_wado']:'8080')."/oviyam/oviyam?studyUID={$object_data[0]['Study_uid']}";
				} else {
					//ищем лоакльно в др. больнице
					$StudyData = $this->processPacsServiceRequest(array(
						'study_uid'=>$research['study_uid'],
						'Pacs_host_IP'=>$LpuEquipmentPacsService[0]['PACS_ip_vip'],
						'Port'=>$LpuEquipmentPacsService[0]['PACS_port'],
						'AeTitle'=>$LpuEquipmentPacsService[0]['PACS_aet'],
						'Img_host_IP'=>$LpuEquipmentPacsService[0]['PACS_ip_vip'],
						'WadoPort'=>$LpuEquipmentPacsService[0]['PACS_wado'],
						'urlPrefix'=>'provi/',
						'queryType'=>(isset($data['queryType']))?$data['queryType']:''
					));
					$link = $this->_getViewerLink( $LpuEquipmentPacsService[0]['PACS_ip_vip'] , $LpuEquipmentPacsService[0]['PACS_wado'], $research['study_uid'], $this->getRegionNumber(),true);
					//$src = "provi/{$Lpu_Pacs_config['PACS_ip_vip']}:{$Lpu_Pacs_config['PACS_wado']}/oviyam/oviyam?".http_build_query(array('studyUID'=>$object_data[0]['Study_uid']));
				}
			} else {
				//Ищем на глобальном
				if (!defined('PROMED_PACS_IP')||!defined('PROMED_PACS_WADOPORT')||!defined('PROMED_PACS_AETITLE')||!defined('PROMED_PACS_PORT')) {
					return array(array('success'=>false,'Error_Msg'=>'Не определены константы глобального PACS'));
				}
				$StudyData = $this->processPacsServiceRequest(array(
						'study_uid'=>$research['study_uid'],
						'Pacs_host_IP'=>PROMED_PACS_IP,
						'Port'=>PROMED_PACS_PORT,
						'AeTitle'=>PROMED_PACS_AETITLE,
						'Img_host_IP'=>PROMED_PACS_IP,
						'WadoPort'=>PROMED_PACS_WADOPORT,
						'urlPrefix'=>'provi/',
						'queryType'=>(isset($data['queryType']))?$data['queryType']:''
					));
				$link = $this->_getViewerLink( PROMED_PACS_IP , PROMED_PACS_WADOPORT, $research['study_uid'], $this->getRegionNumber(),true);
				//$src .= 'provi/'.(defined('PROMED_PACS_IP')?PROMED_PACS_IP:'192.168.36.159').':'.((in_array($servPort, array(443,444,445)))?(defined('PROMED_PACS_HTTPS_WADOPORT')?PROMED_PACS_HTTPS_WADOPORT:'8443'):(defined('PROMED_PACS_WADOPORT')?PROMED_PACS_WADOPORT:'8080')).'/oviyam/oviyam?'.http_build_query(array('studyUID'=>$object_data[0]['Study_uid']));						
			}			
			if (!$StudyData || isset($StudyData[0])&&isset($StudyData[0]['Error_Msg'])) {
				return $StudyData;
			}
			
			$this->load->library('parser');

			$seriesView='';
			$instancesView='';

			$firstStudy = true;
			ksort($StudyData);

			foreach ($StudyData as $seriesNumber => $series) {
				
				// Показываем оригинал https://redmine.swan.perm.ru/issues/75349
				//$rowlimit = ($series['instances'][0]['rows']>1000)?'&rows=512':'';
				$rowlimit = '';

				$seriesView .= $this->parser->parse('evn_uslugapar_research_view_emk_header_item', array(
					'first'=>$firstStudy,
					'src'=>(isset($series['instances'][0]['src']) )?($series['instances'][0]['src'].'&rows=100'):false,
					'seriesNum'=>$seriesNumber,
					'study_uid'=>$research['study_uid'],
					'LpuEquipmentPacs_id'=>$research['LpuEquipmentPacs_id'],
					'EMK'=>1
				),true);

				if ($firstStudy) {
					$firstInstance = true;
					foreach ($series['instances'] as $instance) {
						$instancesView .= $this->parser->parse('evn_uslugapar_research_view_sidebar_item', array(
							'first'=>$firstInstance,
							'src_original'=>$instance['src'],
							'src_minified'=>$instance['src'].'&rows=100',
							'numberOfFrames'=>$instance['numberOfFrames'],
							'sidebar_postfix'=>'_emk',
							'id'=>str_replace('.', '_', $research['study_uid']),
						),true);
						if ($firstInstance) {
							$firstImgSrc = $instance['src'].$rowlimit;
						}	
						$firstInstance = false;
					}
				}
				$firstStudy = false;
			}
			
			$contentView = $this->parser->parse('evn_uslugapar_research_view_emk_content', array(
				'instances'=>$instancesView,
				'firstImgSrc'=>$firstImgSrc,
				'id'=>str_replace('.', '_', $research['study_uid']),
			),true);

			$html = $this->parser->parse('evn_uslugapar_research_view_emk', array(
				'series'=>$seriesView,
				'content'=>$contentView,
				'study_uid'=>$research['study_uid'],
				'id'=>str_replace('.', '_', $research['study_uid']),
			),true);
			
			$digiPacsLink = 'http://' . $research["digiPacs_ip"] . '/#/viewer/token-auth?token=user&n=%2Fviewer%2Fredirect-to-image-view%3FStudy%3D' . $research["study_uid"] . '%26serverName%3DPACS';

			//$resultArray[] = iconv('windows-1251', 'utf-8', $html);
			$reslt = array('view'=>$html,'link'=>$link, 'newLink' => $digiPacsLink);
			$resultArray[] = $reslt;
			
		}
		return $resultArray;
		
	}
	/**
	 * Получение отображения серии исследования
	 */
	public function getSeriesView($data) {
		
		if ( !array_key_exists( 'study_uid', $data ) || !$data['study_uid'] ) {
			return array( array( 'Error_Msg' => 'Не указан UID исследования.' ) );
		}		 
		if ( !array_key_exists( 'LpuEquipmentPacs_id', $data ) || !$data['LpuEquipmentPacs_id'] ) {
			return array( array( 'Error_Msg' => 'Не указан идентификатор устройства PACS.' ) );
		}
		if ( !array_key_exists( 'seriesNum', $data ) || !$data['seriesNum'] ) {
			return array( array( 'Error_Msg' => 'Не указан номер серии.' ) );
		}
		
		$data['EMK'] = ( !array_key_exists( 'EMK', $data ) || !$data['EMK'] )?false:true;
				
		$StudyData = $this->getStudyData($data);
		if (!$StudyData|| (isset($StudyData[0])&&isset($StudyData[0]['Error_Msg'])&&!empty($StudyData[0]['Error_Msg']))) {
			return $StudyData;
		} elseif (count($StudyData)==0) {
			return array(array('Error_Msg'=>'По данному идентификатору исследования не найдено ни одного изображения'));
		} elseif (!isset($StudyData[$data['seriesNum']])||empty($StudyData[$data['seriesNum']])||(count($StudyData[$data['seriesNum']])==0)) {
			return array(array('Error_Msg'=>'В серии не найдено ни одного изображения'));
		}
		
		$this->load->library('parser');
		
		$dataFirst = current($StudyData);	
				
		$firstInstance = true;
		$instancesView = '';
		
		$sidebar_postfix = (!empty($data['EMK'])?'_emk':'');
		
		foreach ($StudyData[$data['seriesNum']]['instances'] as $instance) {
			//$addit = ($instance['rows'] < 512)?'&rows=512':'';
			$addit = '';
			$instancesView .= $this->parser->parse('evn_uslugapar_research_view_sidebar_item', array(
				'first'=>$firstInstance,
				//'src_original'=>$instance['src'],
				'src_original'=>str_replace('&frameNumber', $addit.'&frameNumber', $instance['src']),
				'src_minified'=>$instance['src'].'&rows=100',
				'numberOfFrames'=>$instance['numberOfFrames'],
				'id'=>str_replace('.', '_', $data['study_uid']),
				'sidebar_postfix'=>$sidebar_postfix,
			),true);
			if ($firstInstance) {
				$firstImgSrc = $instance['src'];
				$firstImgRows = $instance['rows'];
			}	
			$firstInstance = false;
		}
		
		$sidebar_item = ($data['EMK'])?'evn_uslugapar_research_view_emk_content':'evn_uslugapar_research_view_content';
		$sidebar_postfix = (($data['EMK'])?'_emk':'');
		//list($width, $height, $type, $attr) = getimagesize($firstImgSrc);												
		//$addit = ($firstImgRows < 512)?'&rows=512':'';
		$addit = '';
		$contentView = $this->parser->parse($sidebar_item, array(
			'instances'=>$instancesView,
			'firstImgSrc'=>str_replace('&frameNumber', $addit.'&frameNumber', $firstImgSrc),
			'id'=>str_replace('.', '_', $data['study_uid']),
			'sidebar_postfix'=>$sidebar_postfix,
		),true);
		
		//$contentView = iconv('windows-1251', 'utf-8', $contentView);
		$contentView = $contentView;
		
		return array('success'=>true, 'html' => $contentView);

	 }
	 /**
	  * Получение отображения исследования для АРМ ФД
	  * @param type $data
	  * @return type
	  */
	public function getStudyView($data) {
		if ( !array_key_exists( 'study_uid', $data ) || !$data['study_uid'] ) {
			return array( array( 'Error_Msg' => 'Не указан UID исследования.' ) );
		}		 
		if ( !array_key_exists( 'LpuEquipmentPacs_id', $data ) || !$data['LpuEquipmentPacs_id'] ) {
			return array( array( 'Error_Msg' => 'Не указан идентификатор устройства PACS.' ) );
		}
		
		$StudyData = $this->getStudyData($data);
		
		if (!$StudyData|| (isset($StudyData[0])&&isset($StudyData[0]['Error_Msg'])&&!empty($StudyData[0]['Error_Msg']))) {
			return $StudyData;
		} elseif (count($StudyData)==0) {
			return array(array('Error_Msg'=>'По данному идентификатору исследования не найдено ни одного изображения'));
		}
	
		$this->load->library('parser');
		
		$seriesView='';
		$instancesView='';

		$firstStudy = true;
		ksort($StudyData);
		$dataFirst = current($StudyData);		
		
					
		foreach ($StudyData as $seriesNumber => $series) {
			$seriesView .= $this->parser->parse('evn_uslugapar_research_view_header_item', array(
				'first'=>$firstStudy,
				'src'=>$series["instances"][0]['src'],
				'seriesNum'=>$seriesNumber,
				'study_uid'=>$data['study_uid'],
				'id'=>str_replace('.', '_', $data['study_uid']),
				'LpuEquipmentPacs_id'=>$data['LpuEquipmentPacs_id'],
				'EMK'=>0
			),true);
								
			if ($firstStudy) {
				$firstInstance = true;
				foreach ($series["instances"] as $instance) {
					$addit = ($instance['rows'] < 512)?'&rows=512':'';
					$instancesView .= $this->parser->parse('evn_uslugapar_research_view_sidebar_item', array(
						'first'=>$firstInstance,						
						'src_original'=>str_replace('&frameNumber', $addit.'&frameNumber', $instance['src']),
						'src_minified'=>$instance['src'].'&rows=100',
						'numberOfFrames'=>$instance['numberOfFrames'],
						'study_uid'=>$data['study_uid'],
						'id'=>str_replace('.', '_', $data['study_uid']),						
						'sidebar_postfix'=>''
					),true);
					if ($firstInstance) {
						$firstImgSrc = str_replace('&frameNumber', $addit.'&frameNumber', $instance['src']);
					}	
					$firstInstance = false;
				}				
			}
			$firstStudy = false;
		}
		
		$contentView = $this->parser->parse('evn_uslugapar_research_view_content', array(
			'instances'=>$instancesView,
			'firstImgSrc'=>$firstImgSrc,
			'id'=>str_replace('.', '_', $data['study_uid']),
		),true);
		
		$html = $this->parser->parse('evn_uslugapar_research_view_emk', array(
			'series'=>$seriesView,
			'content'=>$contentView,
			'study_uid'=>$data['study_uid'],
			'id'=>str_replace('.', '_', $data['study_uid']),
		),true);
		
		//$html = iconv('windows-1251', 'utf-8', $html);
		$result = array('success'=>true, 'html' => $html);
		
		return $result;	
		
	 }	
	 
	 /**
	 * Получениие серий исследований для АРМ ФД
	 * 
	 *
	 */
	public function getSeriesForDicomViewer($data) {
		if ( !array_key_exists( 'study_uid', $data ) || !$data['study_uid'] ) {
			return array( array( 'Error_Msg' => 'Не указан UID исследования.' ) );
		}		 
		if ( !array_key_exists( 'LpuEquipmentPacs_id', $data ) || !$data['LpuEquipmentPacs_id'] ) {
			return array( array( 'Error_Msg' => 'Не указан идентификатор устройства PACS.' ) );
		}
		$data['queryType'] = 'series';
		$StudyData = $this->getStudyData($data);
		$resultArr = array();
		if(isset($StudyData[0]['Error_Msg'])){
			return $StudyData;
		};

		foreach ($StudyData as $instance) {
			array_push($resultArr,$instance);
		}
		
		$result = array('success'=>true, 'data' => $resultArr);
		return $result;
	}
	
	 /**
	 * Получениие исследования для АРМ ФД
	 *
	 *
	 */
	public function getInstancesForDicomViewer($data) {
		if ( !array_key_exists( 'study_uid', $data ) || !$data['study_uid'] ) {
			return array( array( 'Error_Msg' => 'Не указан UID исследования.' ) );
		}		 
		if ( !array_key_exists( 'LpuEquipmentPacs_id', $data ) || !$data['LpuEquipmentPacs_id'] ) {
			return array( array( 'Error_Msg' => 'Не указан идентификатор устройства PACS.' ) );
		}
		if ( !array_key_exists( 'seriesUID', $data ) || !$data['seriesUID'] ) {
			return array( array( 'Error_Msg' => 'Не указан seriesUID.' ) );
		}
		$data['queryType'] = 'series';
		$StudyData = $this->getStudyData($data);
		
		$resultArr = array();
		foreach ($StudyData as $instance) {
			foreach ($instance['instances'] as $n=>$pic){
				//$instance['instances'][$n]['src'] = '/?c=dicom&m=getImage&imageurl='.urlencode($pic['src']);
				//$instance['instances'][$n]['smallsrc'] = '/?c=dicom&m=getImage&imageurl='.urlencode($pic['src'].'&rows=100');
				$instance['instances'][$n]['src'] = $pic['src'];
				$instance['instances'][$n]['smallsrc'] = $pic['src'].'&rows=100';
			}
			array_push($resultArr,$instance);
		}
		$result = array('success'=>true, 'data' => $resultArr);
		
		$data['queryType'] = 'instances';
		$SeriesData = $this->getStudyData($data);
		$resultArr = array();
		/*foreach ($SeriesData as $instance) {
			//$instance['src'] = '/?c=dicom&m=getImage&imageurl='.urlencode($instance['src']);
			$instance['src'] = $instance['src'];
			$instance['smallsrc'] = $instance['src'].'&rows=100';
			array_push($resultArr,$instance);
		}
		$result = array('success'=>true, 'data' => $resultArr);
		return $result;
		*/
		return $SeriesData;
	}
	
	/**
	* Сохранение аннотации svg
	*
	*
	*/
	public function saveDicomSvgAnnotation($data){
		
		if ( !array_key_exists( 'study_uid', $data ) || !$data['study_uid'] ) {
			return array( array( 'Error_Msg' => 'Не указан UID исследования.' ) );
		}
		if ( !array_key_exists( 'seriesUID', $data ) || !$data['seriesUID'] ) {
			return array( array( 'Error_Msg' => 'Не указан идентификатор серии' ) );
		}
		
		$params = array
		(
			'DicomStudyNote_UID' => $data['study_uid'],
			'DicomStudyNote_SeriesUID'  => $data['seriesUID'],
			'DicomStudyNote_PictureUID' => $data['sopIUID'],
			'DicomStudyNote_AttachFrames' => $data['attachFrames'],
			'pmUser_id' => $data['pmUser_id'],
			'DicomStudyNote_XmlData' => $data['canvasXmlData']
		);
		
		if ( !array_key_exists( 'DicomStudyNote_id', $data ) || !$data['DicomStudyNote_id'] ) {
			$proc = 'p_DicomStudyNote_ins';
			$params[ 'DicomStudyNote_id' ] = null;
		} else {
			$params[ 'DicomStudyNote_id' ] = $data['DicomStudyNote_id'];

			$proc = 'p_DicomStudyNote_upd';	
		}
		
		$query = "
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000),
				@DicomStudyNote_id bigint = :DicomStudyNote_id,
                @DicomStudyNote_UID varchar(255),
                @DicomStudyNote_SeriesUID varchar(255),
                @DicomStudyNote_PictureUID varchar(255),
                @DicomStudyNote_AttachFrames varchar(255),
                @pmUser_id bigint,
                @DicomStudyNote_XmlData xml;
			
			exec {$proc}
            	@pmUser_id = :pmUser_id,
				@DicomStudyNote_UID  = :DicomStudyNote_UID,
				@DicomStudyNote_id = @DicomStudyNote_id output,
                @DicomStudyNote_SeriesUID  = :DicomStudyNote_SeriesUID,
                @DicomStudyNote_PictureUID  = :DicomStudyNote_PictureUID,
                @DicomStudyNote_AttachFrames  = :DicomStudyNote_AttachFrames,
                @DicomStudyNote_XmlData = :DicomStudyNote_XmlData,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
				SELECT 	
					@Error_Code as Error_Code, @Error_Message as Error_Msg, DSN.DicomStudyNote_id, DSN.DicomStudyNote_XmlData,
					(PMC.PMUser_surName+' '+SUBSTRING(PMC.PMUser_firName,1,1)+' '+SUBSTRING(PMC.PMUser_secName,1,1)) as Person_FIO
				FROM
					v_DicomStudyNote DSN with (nolock)
				LEFT JOIN v_pmUserCache PMC with (nolock) on PMC.PMUser_id = DSN.pmUser_updID
				WHERE
					DSN.DicomStudyNote_id = @DicomStudyNote_id
		";
		
		$result = $this->db->query($query, $params);
		
		if ( is_object($result) )
		{
			return $result->result('array');
		}
		else
		{
			return false;
		}
	}
	
	/**
	* Загрузка аннотаций для изображений / серии
	*
	*
	*/
	public function loadDicomSvgAnnotation($data){
		
		if ( !array_key_exists( 'study_uid', $data ) || !$data['study_uid'] ) {
			return array( array( 'Error_Msg' => 'Не указан UID исследования.' ) );
		}
		if ( !array_key_exists( 'seriesUID', $data ) || !$data['seriesUID'] ) {
			return array( array( 'Error_Msg' => 'Не указан идентификатор серии' ) );
		}
		
		$params = array
		(
			'DicomStudyNote_UID' => $data['study_uid'],
			'DicomStudyNote_SeriesUID'  => $data['seriesUID']
		);
		
		$filter = "
			DSN.DicomStudyNote_UID = :DicomStudyNote_UID
			AND DSN.DicomStudyNote_SeriesUID = :DicomStudyNote_SeriesUID
		";
		
		if ( array_key_exists( 'sopIUID', $data ) && $data['sopIUID'] ) {
			$filter .=" AND DSN.DicomStudyNote_PictureUID = :DicomStudyNote_PictureUID";
			$params['DicomStudyNote_PictureUID'] = $data['sopIUID'];
		};
		
		$query = "
			SELECT
				DSN.DicomStudyNote_id,
				DSN.DicomStudyNote_UID,
				DSN.DicomStudyNote_SeriesUID,
				DSN.DicomStudyNote_PictureUID,
				DSN.DicomStudyNote_AttachFrames,
				DSN.pmUser_insID,
				DSN.DicomStudyNote_XmlData,
				PMC.MedPersonal_id,
				(PMC.PMUser_surName+' '+SUBSTRING(PMC.PMUser_firName,1,1)+' '+SUBSTRING(PMC.PMUser_secName,1,1)) as Person_FIO
			FROM DicomStudyNote DSN
			LEFT JOIN v_pmUserCache PMC with (nolock) on PMC.PMUser_id = DSN.pmUser_updID
			WHERE
			{$filter}
		";
		
		$result = $this->db->query($query, $params);
		
		if ( is_object($result) )
		{
			return $result->result('array');
		}
		else
		{
			return false;
		}
	}
	
	
	/**
	* Удаление аннотации изображений / серии
	*
	*
	*/
	public function deleteDicomSvgAnnotation($data){
		
		if ( !array_key_exists( 'DicomStudyNote_id', $data ) || !$data['DicomStudyNote_id'] ) {
			return array( array( 'Error_Msg' => 'Не указан ID аннотации.' ) );
		}
		
		$params = array
		(
			'DicomStudyNote_id' => $data['DicomStudyNote_id']
		);
		
		$query = "
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000),
				@DicomStudyNote_id bigint = :DicomStudyNote_id;
			
			exec p_DicomStudyNote_del
				@DicomStudyNote_id = @DicomStudyNote_id,					
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
				select @Error_Code as Error_Code, @Error_Message as Error_Msg;
			";
		$result = $this->db->query($query, $params);
		
		if ( is_object($result) ){return $result->result('array');}
		else return false;		
	}
}