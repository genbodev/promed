<?php
defined('BASEPATH') or die('404. Script not found.');

/**
 * @class EmergencyTeam
 * 
 * Бригада СМП
 * 
 * @author Dyomin Dmitry
 * @since 09.2012
 */

class EmergencyTeam extends swController {
	
	public $inputRules = array(
		'saveEmergencyTeam' => array(
			array(
				'field'	=> 'EmergencyTeam_id',
				'label'	=> 'Идентификатор бригады',
				'rules'	=> '',
				'type'	=> 'id',
			),
			array(
				'field'	=> 'EmergencyTeam_Num',
				'label'	=> 'Номер бригады',
				'rules'	=> 'required',
				'type'	=> 'string',
			),
			array(
				'field'	=> 'EmergencyTeam_CarNum',
				'label'	=> 'Номер кареты',
				'rules'	=> '',
				'type'	=> 'string',
			),
			array(
				'field'	=> 'EmergencyTeam_CarBrand',
				'label'	=> 'Марка авто',
				'rules'	=> '',
				'type'	=> 'string',
			),
			array(
				'field'	=> 'EmergencyTeam_CarModel',
				'label'	=> 'Модель авто',
				'rules'	=> '',
				'type'	=> 'string',
			),
			array(
				'field'	=> 'EmergencyTeam_PortRadioNum',
				'label'	=> 'Номер рации',
				'rules'	=> '',
				'type'	=> 'string',
			),
			array(
				'field'	=> 'EmergencyTeam_GpsNum',
				'label'	=> 'Номер GPS/Глонасс',
				'rules'	=> '',
				'type'	=> 'string',
			),
			array(
				'field'	=> 'LpuBuilding_id',
				'label'	=> 'Ид. базовой подстанции',
				'rules'	=> '',
				'type'	=> 'int',
			),
			array(
				'field'	=> 'EmergencyTeam_BaseStationNum',
				'label'	=> 'Номер базовой подстанции',
				'rules'	=> '',
				'type'	=> 'int',
			),
			array(
				'field'	=> 'EmergencyTeamSpec_id',
				'label'	=> 'Профиль бригады',
				'rules'	=> '',
				'type'	=> 'int',
			),
			array(
				'field'	=> 'EmergencyTeam_HeadShift',
				'label'	=> 'Старший бригады',
				'rules'	=> '',
				'type'	=> 'int',
			),
			array(
				'field'	=> 'EmergencyTeam_Driver',
				'label'	=> 'Водитель',
				'rules'	=> '',
				'type'	=> 'int',
			),
			array(
				'field'	=> 'EmergencyTeam_Assistant1',
				'label'	=> 'Первый помощник',
				'rules'	=> '',
				'type'	=> 'int',
			),
			array(
				'field'	=> 'EmergencyTeam_Assistant2',
				'label'	=> 'Второй помощник',
				'rules'	=> '',
				'type'	=> 'int',
			),
			array(
				'field'	=> 'ARMType',
				'label'	=> '',
				'rules'	=> '',
				'type'	=> 'string',
			),
		),
		
		'deleteEmergencyTeam' => array(
			array(
				'field'	=> 'EmergencyTeam_id',
				'label'	=> 'Идентификатор бригады',
				'rules'	=> 'required',
				'type'	=> 'id',
			),
		),
		
		'loadEmergencyTeamOperEnv' => array(
			array(
				'field'	=> 'Lpu_id',
				'label'	=> 'Идентификатор ЛПУ',
				'rules'	=> '',
				'type'	=> 'id',
			),
			array(
				'field'	=> 'closeHide',
				'label'	=> 'Спрятать закрытые бригады',
				'rules'	=> '',
				'type'	=> 'int',
			),
			array(
				'field'	=> 'CmpCallCard',
				'label'	=> 'Карта вызова',
				'rules'	=> '',
				'type'	=> 'int',
			),
			array(
				'field'	=> 'teamTime',
				'label'	=> 'Время бригады на смене',
				'rules'	=> '',
				'type'	=> 'string',
			),
			array(
				'field'	=> 'LpuBuilding_id',
				'label'	=> 'Станция, отделение',
				'rules'	=> '',
				'type'	=> 'int',
			)
		),
		
		'loadEmergencyTeamCCC' => array(
			array(
				'field'	=> 'Lpu_id',
				'label'	=> 'Идентификатор ЛПУ',
				'rules'	=> '',
				'type'	=> 'id',
			),
			array(
				'field'	=> 'AcceptTime',
				'label'	=> 'Время приема вызова',
				'rules'	=> '',
				'type'	=> 'string',
			),
			array(
				'field'	=> 'CmpCallCard_id',
				'label'	=> 'id card call',
				'rules'	=> '',
				'type'	=> 'id',
			)
		),
		
		'saveEmergencyTeamDutyTime' => array(
			array(
				'field'	=> 'EmergencyTeam_id',
				'label'	=> 'Идентификатор бригады',
				'rules'	=> 'required',
				'type'	=> 'id',
			),
			array(
				'field' => 'EmergencyTeamDuty_DateStart',
				'label' => 'Дата и время начала смены',
				'rules' => '',
				'type' => 'array',
				'is_array' => true,
			),
			array(
				'field' => 'EmergencyTeamDuty_DateFinish',
				'label' => 'Дата и время окончания смены',
				'rules' => '',
				'type' => 'array',
				'is_array' => true,
			),
		),
		
		'loadDispatchOperEnv' => array(
			array(
				'field'	=> 'Lpu_id',
				'label'	=> 'Идентификатор ЛПУ',
				'rules'	=> '',
				'type'	=> 'id',
			),
			array(
				'field'	=> 'online',
				'label'	=> 'Статус онлайн',
				'rules'	=> '',
				'type'	=> 'string',
			)
		),

		'loadEmergencyTeam' => array(
			array(
				'field'	=> 'EmergencyTeam_id',
				'label'	=> 'Идентификатор бригады',
				'rules'	=> 'required',
				'type'	=> 'id',
			),
		),
		
		'setEmergencyTeamStatus' => array(
			array(
				'field'	=> 'EmergencyTeam_id',
				'label'	=> 'Идентификатор бригады',
				'rules'	=> 'required',
				'type'	=> 'id',
			),
			array(
				'field'	=> 'EmergencyTeamStatus_id',
				'label'	=> 'Статус бригады',
				'rules'	=> 'required',
				'type'	=> 'int',
			),
		),
		
		'loadEmergencyTeamDutyTimeGrid' => array(
			array(
				'field'	=> 'EmergencyTeam_id',
				'label'	=> 'Идентификатор бригады',
				'rules'	=> 'required',
				'type'	=> 'id',
			),
			array(
				'field' => 'dateStart',
				'label' => 'Дата начала поиска смен',
				'rules' => 'required',
				'type' => 'date',
			),
			array(
				'field' => 'dateFinish',
				'label' => 'Дата окончания поиска смен',
				'rules' => 'required',
				'type' => 'date',
			),
		),
		
		'setEmergencyTeamWorkComingMedPersonal' => array(
			array(
				'field'	=> 'MedPersonal_id',
				'label'	=> 'Идентификатор врача',
				'rules'	=> 'required',
				'type'	=> 'id',
			)
		),
		
		'loadEmergencyTeamByMedPersonal' => array(			
			array(
				'field' => 'dateStart',
				'label' => 'Дата начала поиска смен',
				'rules' => '',
				'type' => 'date',
			),
			array(
				'field' => 'dateFinish',
				'label' => 'Дата окончания поиска смен',
				'rules' => '',
				'type' => 'date',
			)
		),
		
		'setEmergencyTeamSession' => array(
			array(
				'field'	=> 'EmergencyTeamDuty_id',
				'label'	=> 'Идентификатор смены бригады',
				'rules'	=> 'required',
				'type'	=> 'id',
			),
			array(
				'field'	=> 'EmergencyTeam_id',
				'label'	=> 'Идентификатор бригады',
				'rules'	=> 'required',
				'type'	=> 'int',
			)
		),
		
		'setEmergencyTeamWorkComing' => array(
			array(
				'field'	=> 'EmergencyTeamDuty_id',
				'label'	=> 'Идентификатор смены бригады',
				'rules'	=> 'required',
				'type'	=> 'id',
			),
			array(
				'field'	=> 'EmergencyTeam_id',
				'label'	=> 'Идентификатор бригады',
				'rules'	=> 'required',
				'type'	=> 'int',
			),
			array(
				'field'	=> 'EmergencyTeamDuty_isComesToWork',
				'label'	=> 'Флаг выхода на смену бригады',
				'rules'	=> 'required',
				'type'	=> 'int',
			),
		),
		
		'loadEmergencyTeamCombo' => array(
			array(
				'field'	=> 'LpuBuilding_id',
				'label'	=> 'Станция, отделение',
				'rules'	=> '',
				'type'	=> 'int',
			),
			array(
				'field'	=> 'begDate',
				'label'	=> 'Дата начала',
				'rules'	=> '',
				'type'	=> 'date',
			),
			array(
				'field'	=> 'endDate',
				'label'	=> 'Дата окончания',
				'rules'	=> '',
				'type'	=> 'date',
			)
		),
		'getEmergencyTeamProposalLogic' =>array(

			array('field' => 'EmergencyTeamProposalLogic_id', 'label' => 'Ид. правила', 'rules' => '', 'type' => 'id' ),
		),
		'getEmergencyTeamProposalLogicRuleSpecSequence'=>array(
			array('field' => 'EmergencyTeamProposalLogic_id', 'label' => 'Ид. правила', 'rules' => '', 'type' => 'id' )
		),
		'saveEmergencyTeamProposalLogicRule'=>array(
			array('field' => 'EmergencyTeamProposalLogic_id', 'label' => 'Ид. правила', 'rules' => '', 'type' => 'id' ),
			array('field' => 'CmpReason_id', 'label' => 'Ид. повод вызова', 'rules' => 'required', 'type' => 'id' ),
			array('field' => 'Sex_id', 'label' => 'Ид. пола пациента', 'rules' => '', 'type' => 'id' ),
			array('field' => 'EmergencyTeamProposalLogic_AgeFrom', 'label' => 'Возраст от', 'rules' => '', 'type' => 'int' ),
			array('field' => 'EmergencyTeamProposalLogic_AgeTo', 'label' => 'Возраст до', 'rules' => '', 'type' => 'int' ),
			array('field' => 'EmergencyTeamProposalLogicRule_Sequence', 'label' => 'Последовательность профилей', 'rules' => 'required', 'type' => 'string' )
		),
		'deleteEmergencyTeamProposalLogicRule'=>array(
			array('field' => 'EmergencyTeamProposalLogic_id', 'label' => 'Ид. правила', 'rules' => 'required', 'type' => 'id' )
		),
		'getEmergencyTeamPostKind'=>array(
			array(
				'field'	=> 'EmergencyTeam_id',
				'label'	=> 'Идентификатор бригады',
				'rules'	=> 'required',
				'type'	=> 'int',
			)			
		)
	);
	
	
	/**
	 * @desc Инициализация
	 * 
	 * @return void
	 */
	public function __construct(){
		parent::__construct();
		
		$this->load->database();
		$this->load->model('EmergencyTeam_model', 'dbmodel');
	}
	
	
	/**
	 * @desc Сохранение бригады СМП
	 * 
	 * @return bool
	 */
	public function saveEmergencyTeam() {
		$data = $this->ProcessInputData( 'saveEmergencyTeam', true );
		if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->saveEmergencyTeam( $data );

		$this->ProcessModelSave( $response, true, 'Ошибка при сохранении бригады СМП' )->ReturnData();

		return true;
	}
	
	
	/**
	 * @desc Возвращает данные по оперативной обстановке бригад СМП
	 * 
	 * @return bool
	 */
	public function loadEmergencyTeamOperEnv(){
		$data = $this->ProcessInputData( 'loadEmergencyTeamOperEnv', true );
		if ( $data === false ) {
			return false;
		}
		
		$response = $this->dbmodel->loadEmergencyTeamOperEnv( $data );
	
		$this->ProcessModelList( $response, true, true )->ReturnData();
		
		return true;
	}
	
	/**
	 * Возвращает список бригад для выбора в карте закрытия и поточном вводе
	 * 
	 * @return bool
	 */
	public function loadEmergencyTeamCCC(){
		$data = $this->ProcessInputData('loadEmergencyTeamCCC', true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->loadEmergencyTeamCCC($data);

		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * @desc Сохраняет заданную дату и время начала и окончания смены
	 * 
	 * @return bool
	 */
	public function saveEmergencyTeamDutyTime(){
		$data = $this->ProcessInputData( 'saveEmergencyTeamDutyTime', true );
		if ( $data === false ) {
			return false;
		}

		$response = $this->dbmodel->saveEmergencyTeamDutyTime( $data );

		$this->ProcessModelSave( $response, true )->ReturnData();

		return true;
	}
	
	
	/**
	 * @desc Возвращает данные по оперативной обстановке бригад СМП
	 * 
	 * @return bool
	 */
	public function loadDispatchOperEnv(){
		/*
		// У нас пока нет входящих данных
		$data = $this->ProcessInputData( 'loadDispatchOperEnv', true );
		if ( $data === false ) {
			return false;
		}
		*/

		
		$data = $this->ProcessInputData( 'loadDispatchOperEnv', true );

		$this->load->helper('NodeJS');
		$params = array('action'=>'getOnlineUsersDNDV'
			,'Lpu_id'=>$data['Lpu_id']
		);
		array_walk($params, 'ConvertFromWin1251ToUTF8');



		$postSendResult = NodePostRequest($params);
		
		if ($postSendResult[0]['success']==true && 1==2) {
			//нод жив
			$responseData = json_decode($postSendResult[0]['data'],true);
			
			if ($responseData["success"]===true) {
				$response = $this->dbmodel->loadDispatchOperEnv($data);
				
				//$response['online'] = $responseData['data'];
				
				$restextend = $response;
				foreach ($response as $key => $value)
				{
					//var_dump($responseData['data']);
					$result = array_intersect($restextend[$key], $responseData['data']);
					$restextend[$key]['online'] = false;
					if (count($result))
					{
						$restextend[$key]['online'] = true;
					}
				}
				$this->ProcessModelList( $restextend, true, true )->ReturnData();
			} else {
				$this->ProcessModelList(array(0=>array('success'=>false,'Err_Msg'=>'Нет онлайновых пользователей')), true)->ReturnData();
			}
		} else {
			//нод мертв
			$response = $this->dbmodel->loadDispatchOperEnv($data);
			$this->ProcessModelList( $response, true, true )->ReturnData();
			//$this->ProcessModelSave($postSendResult, true)->ReturnData();
		}
	
		//$data = array();
		
		//$response = $this->dbmodel->loadDispatchOperEnv( $data );
	
		//$this->ProcessModelList( $response, true, true )->ReturnData();
		
		//return true;
	}
	
	
	/**
	 * @desc Возвращает данные указанной бригады
	 * 
	 * @return bool
	 */
	public function loadEmergencyTeam(){
		$data = $this->ProcessInputData( 'loadEmergencyTeam', true );
		if ( $data === false ) {
			return false;
		}
		
		$response = $this->dbmodel->loadEmergencyTeam( $data );

		$this->ProcessModelList( $response, true )->ReturnData();

		return true;
	}
	
	
	/**
	 * @desc Удаляет бригаду СМП
	 * 
	 * @return bool
	 */
	public function deleteEmergencyTeam() {
		$data = $this->ProcessInputData( 'deleteEmergencyTeam', true );
		if ( $data === false ) {
			return false;
		}
		
		$response = $this->dbmodel->deleteEmergencyTeam( $data );
		
		$this->ProcessModelSave( $response, true, true )->ReturnData();

		return true;
	}
	
	
	/**
	 * @desc Изменение статуса бригады СМП
	 * 
	 * @return bool
	 */
	function setEmergencyTeamStatus() {
		$data = $this->ProcessInputData( 'setEmergencyTeamStatus', true );
		if ( $data === false ) {
			return false;
		}
	
		$response = $this->dbmodel->setEmergencyTeamStatus($data);
		
		$this->ProcessModelSave( $response, true )->ReturnData();
		
		return true;
	}
	
	
	/**
	 * @desc Получение списка смен указанной бригады для графика нарядов
	 * 
	 * @return bool
	 */
	function loadEmergencyTeamDutyTimeGrid() {
		$data = $this->ProcessInputData( 'loadEmergencyTeamDutyTimeGrid', true );
		if ( $data === false ) {
			return false;
		}
		
		$response = $this->dbmodel->loadEmergencyTeamDutyTimeGrid( $data );

		$this->ProcessModelMultiList( $response, true, true )->ReturnData();
		
		return true;
	}
	
	
	/**
	 * @desc Отметка о выходе или невыходе на работу по врачу
	 * 
	 * @return bool
	 */
	public function setEmergencyTeamWorkComingMedPersonal() {
		$data = $this->ProcessInputData( 'setEmergencyTeamWorkComingMedPersonal', true );
		if ( $data === false ) {
			return false;
		}		
		$response = $this->dbmodel->setEmergencyTeamWorkComingMedPersonal( $data );		
		$this->ProcessModelSave( $response, true, true )->ReturnData();
		return true;
	}
	
	
	/**
	 * @desc Отметка о выходе или невыходе на работу по врачу
	 * 
	 * @return bool
	 */
	public function loadEmergencyTeamByMedPersonal() {
		$data = $this->ProcessInputData( 'loadEmergencyTeamByMedPersonal', true );
		if ( $data === false ) {
			return false;
		}		
		$response = $this->dbmodel->loadEmergencyTeamByMedPersonal( $data );	
		$this->ProcessModelMultiList( $response, true, true )->ReturnData();		
		return true;
	}
		
	
	
	/**
	 * @desc Отметка о выходе или невыходе на работу
	 * 
	 * @return bool
	 */
	public function setEmergencyTeamWorkComing() {
		$data = $this->ProcessInputData( 'setEmergencyTeamWorkComing', true );
		if ( $data === false ) {
			return false;
		}
		
		$response = $this->dbmodel->setEmergencyTeamWorkComing( $data );
		
		if ($data['EmergencyTeamDuty_isComesToWork'] == 2) {			
			session_write_close();
			session_start();			
			$_SESSION['CurrentEmergencyTeamDuty_id'] = $data['EmergencyTeamDuty_id'];
			$_SESSION['CurrentEmergencyTeam_id'] = $data['EmergencyTeam_id'];				
		}
			
		$this->ProcessModelSave( $response, true, true )->ReturnData();

		return true;
	}
	
	
	
	/**
	 * @desc Сохранение старшего бригады в сессию
	 * 
	 * @return bool
	 */
	public function setEmergencyTeamSession() {
		$data = $this->ProcessInputData( 'setEmergencyTeamSession', true );
		if ( $data === false ) {
			return false;
		}
		
		session_write_close();
		session_start();			
		$_SESSION['CurrentEmergencyTeamDuty_id'] = $data['EmergencyTeamDuty_id'];
		$_SESSION['CurrentEmergencyTeam_id'] = $data['EmergencyTeam_id'];
		$response = array('success' => true);
		//$this->ProcessModelSave( $response, true, true )->ReturnData();
		$this->ProcessModelList(array(0=>array('success'=>true,'Err_Msg'=>null)), true)->ReturnData();
		return true;
	}
	
	
	/**
	 * @desc Возращает список для справочника списка бригад СМП
	 * 
	 * @return JSON
	 */
    function loadEmergencyTeamCombo(){
		$data = $this->ProcessInputData( 'loadEmergencyTeamCombo', true, true );
		if ( $data === false ) {
			return false;
		}
		
		$response = $this->dbmodel->loadEmergencyTeamCombo( $data );
		
		$this->ProcessModelList( $response, true, true )->ReturnData();
		
		return true;
    }
	
	
	/**
	 * @desc Получение списка правил, описывающих логику предложения бригад на вызов
	 * 
	 * @return JSON
	 */
    function getEmergencyTeamProposalLogic(){
		$data = $this->ProcessInputData( 'getEmergencyTeamProposalLogic', true, true );
		if ( $data === false ) {
			return false;
		}
		$response = $this->dbmodel->getEmergencyTeamProposalLogic( $data );
		$this->ProcessModelMultiList( $response, true, true )->ReturnData();
		return true;
    }
	
	
	/**
	 * @desc Получение последовательноси профилей бригад в том порядке, в котором они будут предлагаться по определенному правилу
	 * 
	 * 
	 * @return JSON
	 */
    function getEmergencyTeamProposalLogicRuleSpecSequence(){
		$data = $this->ProcessInputData( 'getEmergencyTeamProposalLogicRuleSpecSequence', true, true );
		if ( $data === false ) { 
			return false;
		}
		$response = $this->dbmodel->getEmergencyTeamProposalLogicRuleSpecSequence( $data );
		$this->ProcessModelMultiList( $response, true, true )->ReturnData();
		return true;
    }
	
	/**
	* @desc Сохранение правли для логики предложения бригады на вызов
	* 
	* @return bool
	*/
	public function saveEmergencyTeamProposalLogicRule() {
		$data = $this->ProcessInputData( 'saveEmergencyTeamProposalLogicRule', true );
		if ( $data === false ) {
			return false;
		}
		
		$sequence = json_decode($data['EmergencyTeamProposalLogicRule_Sequence'],true);
		
		$responseRule = $this->dbmodel->saveEmergencyTeamProposalLogicRule( $data );
		if ( (!$responseRule)||(count($responseRule)==0 )||(!isset($responseRule[0]['EmergencyTeamProposalLogic_id'])) ) {
			$this->ProcessModelSave( $responseRule, true, true )->ReturnData();
		} else {
			$continue =true;
			$EmergencyTeamProposalLogic_id = $responseRule[0]['EmergencyTeamProposalLogic_id'];
			for ($i=0;$i<count($sequence)&&$continue;$i++) {
				$ruleData = array();
				$ruleData['pmUser_id'] = $data['pmUser_id'];
				$ruleData['EmergencyTeamProposalLogic_id'] = $EmergencyTeamProposalLogic_id;
				$ruleData['EmergencyTeamProposalLogicRule_SequenceNum'] = $i;
				$ruleData['EmergencyTeamSpec_id'] = $sequence[$i]['EmergencyTeamSpec_id'];
				$ruleData['EmergencyTeamProposalLogicRule_id'] = $sequence[$i]['EmergencyTeamProposalLogicRule_id'];
				$responseSequence = $this->dbmodel->saveEmergencyTeamProposalLogicRuleSequence( $ruleData );
				if (!isset($responseSequence[0]['EmergencyTeamProposalLogicRule_id'])){
					$this->ProcessModelSave( $responseSequence, true, true )->ReturnData();
					$continue = false;
				}
			}
			if ($continue) {
				$this->ProcessModelSave( $responseRule, true, true )->ReturnData();
			}
		}
		
		return true;
	}
	
	/**
	* @desc Описание хз функции
	* 
	* @return bool
	*/
	public function deleteEmergencyTeamProposalLogicRule() {
		$data = $this->ProcessInputData( 'deleteEmergencyTeamProposalLogicRule', true );
		if ( $data === false ) {
			return false;
		}
		$response = $this->dbmodel->deleteEmergencyTeamProposalLogicRule( $data );
		$this->ProcessModelSave( $response, true, true )->ReturnData();		
	}
	
	/**
	 * Возвращает Вид должности старшего бригады
	 */
	public function getEmergencyTeamPostKind(){
		$data = $this->ProcessInputData( 'getEmergencyTeamPostKind', true );
		if ( $data === false ) {
			return false;
		}
		$response = $this->dbmodel->getEmergencyTeamPostKind( $data );
		$this->ProcessModelList($response)->ReturnData();	
		return true;
	}
	/**
	 * Возвращает Вид должности старшего бригады
	 */
	public function getEmergencyTeam(){
		$data = $this->ProcessInputData( 'getEmergencyTeam', true );
		if ( $data === false ) {
			return false;
		}
		$response = $this->dbmodel->getEmergencyTeam( $data );
		$this->ProcessModelList($response)->ReturnData();	
		return true;
	}
}
