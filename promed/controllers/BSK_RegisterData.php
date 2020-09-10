<?php
defined('BASEPATH') or die('No direct script access allowed');
/**
 * BSK_RegisterData - контроллер для анкет БСК
 *
 *
 * @package         BSK
 * @author
 * @version         01.12.2019
 */

class BSK_RegisterData extends swController
{
	var $model = "BSK_RegisterData_model";
	
	
	/**
	 * comment
	 */
	function __construct()
	{
		$this->result = array();
		$this->start  = true;
		
		parent::__construct();
		
		
		
		$this->load->database();
		$this->load->model($this->model, 'dbmodel');
		
		$this->inputRules = array(
			'setIsBrowsed' => array(
				array(
					'field' => 'BSKRegistry_id',
					'label' => '',
					'rules' => '',
					'type' => 'int'
				)
			),
			'loadBSKObjectTree' => array(
				array(
					'field' => 'Person_id',
					'label' => '',
					'rules' => '',
					'type' => 'id'
				)
			),
			'loadBSKrootTree' => array(
				array(
					'field' => 'node',
					'label' => 'Нода дерева',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'object',
					'label' => 'Объект',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Person_id',
					'label' => '',
					'rules' => '',
					'type' => 'id'
				)
			),
			'loadBSKObjectListTree' => array(
				array(
					'field' => 'MorbusType_id',
					'label' => 'Предмет наблюдения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'BSKRegistry_id',
					'label' => 'Анкета',
					'rules' => '',
					'type' => 'id'
				)
			),
			'getBSKRegistryFormTemplate' => array(
				array(
					'field' => 'Person_id',
					'label' => '',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MorbusType_id',
					'label' => 'Предмет наблюдения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'BSKRegistry_id',
					'label' => 'Анкета',
					'rules' => '',
					'type' => 'id'
				)
			),
			'getBSKRegistryElementValues' => array(
				array(
					'field' => 'MorbusType_id',
					'label' => 'Предмет наблюдения',
					'rules' => '',
					'type' => 'id'
				)
			),
			'getPersonElementValuesDiag' => array(
				array(
					'field' => 'Person_id',
					'label' => '',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MorbusType_id',
					'label' => 'Предмет наблюдения',
					'rules' => '',
					'type' => 'id'
				)
			),
			'getBSKObjectWithoutAnket' => array(
				array(
					'field' => 'MorbusType_id',
					'label' => 'Предмет наблюдения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Person_id',
					'label' => '',
					'rules' => '',
					'type' => 'id'
				)
			),
			'loadBSKEvnGrid' => array(
				array(
					'field' => 'MorbusType_id',
					'label' => 'Предмет наблюдения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Person_id',
					'label' => '',
					'rules' => '',
					'type' => 'id'
				)
			),
			'saveBSKRegistry' => array(
				array(
					'field' => 'Sex_id',
					'label' => 'Пол',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Age',
					'label' => 'Возраст',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Пациент',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'MorbusType_id',
					'label' => 'Предмет наблюдения',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'PersonRegister_id',
					'label' => 'Пациент в регистре',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'BSKRegistryFormTemplate_id',
					'label' => 'Шаблон',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'BSKRegistry_setDate',
					'label' => 'Дата анкеты',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'BSKRegistry_id',
					'label' => 'Анкета',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'QuestionAnswer',
					'label' => 'Список вопросов и ответов',
					'rules' => '',
					'type' => 'json_array'
				),
				array(
					'field' => 'BSKRegistry_nextDate',
					'label' => 'Дата следующего осмотра',
					'rules' => '',
					'type' => 'string'
				)
			),
			'getListUslugforEvents' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор пациента',
					'rules' => '',
					'type' => 'int'
				)
			),
			'getListPersonCureHistoryPL' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор пациента',
					'rules' => '',
					'type' => 'int'
				)
			),
			'getListPersonCureHistoryPS' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор пациента',
					'rules' => '',
					'type' => 'int'
				)
			),
			'getListPersonCureHistoryDiagSop' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор пациента',
					'rules' => '',
					'type' => 'int'
				)
			),
			'getListPersonCureHistoryDiagKardio' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор пациента',
					'rules' => '',
					'type' => 'int'
				)
			),
			'getLabResearch' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор пациента',
					'rules' => '',
					'type' => 'int'
				)
			),
			'getLabSurveys' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор пациента',
					'rules' => '',
					'type' => 'int'
				)
			),
			'getRecomendationByDate' => array(
				array(
					'field' => 'MorbusType_id',
					'label' => '',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Person_id',
					'label' => '',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Sex_id',
					'label' => '',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'BSKRegistry_id',
					'label' => '',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'BSKObservRecomendationType_id',
					'label' => '',
					'rules' => '',
					'type' => 'id'
				)
			),
			'getDrugs' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор пациента',
					'rules' => '',
					'type' => 'int'
				)
			),
			'getCompare' => array(
				array(
					'field' => 'Person_id',
					'label' => '',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MorbusType_id',
					'label' => '',
					'rules' => '',
					'type' => 'id'
				)
			),
			'saveInOKS' => array(
				array(
					'field' => 'Registry_method',
					'label' => 'Метод',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Person_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'CmpCallCard_id',
					'label' => 'Идентификатор карты вызова',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'ArrivalDT',
					'label' => 'Время прибытия к больному',
					'rules' => '',
					'type' => 'string',
				),  
				array(
					'field' => 'PainDT',
					'label' => 'Время начала болевых симптомов',
					'rules' => '',
					'type' => 'string',
				),
				array(
					'field' => 'ECGDT',
					'label' => 'Время проведения ЭКГ',
					'rules' => '',
					'type' => 'string',
				),  
				array(
					'field' => 'ResultECG',
					'label' => 'Результат ЭКГ',
					'rules' => '',
					'type' => 'string',
				),  
				array(
					'field' => 'TLTDT',
					'label' => 'Время проведения ТЛТ',
					'rules' => '',
					'type' => 'string',
				),
				array(
					'field' => 'FailTLT',
					'label' => 'Причина отказа от ТЛТ',
					'rules' => '',
					'type' => 'string',
				),
				array(
					'field' => 'LpuDT',
					'label' => 'Время прибытия в медицинскую организацию',
					'rules' => '',
					'type' => 'string',
				),  
				array(
					'field' => 'AbsoluteList',
					'label' => 'Абсолютные противопоказания к проведению ТЛТ',
					'rules' => '',
					'type' => 'string', //json
				),
				array(
					'field' => 'RelativeList',
					'label' => 'Относительные противопоказания к проведению ТЛТ',
					'rules' => '',
					'type' => 'string', //json
				),  
				array(
					'field' => 'ZonaMO',
					'label' => 'Зона ответственности МО',
					'rules' => '',
					'type' => 'string'
				),  
				array(
					'field' => 'ZonaCHKV',
					'label' => 'Зона ответственности проведения ЧКВ',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'MorbusType_id',
					'label' => 'окс=19',
					'rules' => '',
					'type' => 'string'
				),  
				array(
					'field' => 'Diag_id',
					'label' => 'Код диагноза',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'DiagOKS',
					'label' => 'код+диагноз',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'MOHospital',
					'label' => 'МО госпитализации',
					'rules' => '',
					'type' => 'string'
				), 
				/**
				 *  дополнительно
				 */ 
				array(
					'field' => 'MedStaffFact_num',
					'label' => 'Номер фельдшера принявшего вызов',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'LpuBuilding_name',
					'label' => 'Станция (подстанция), отделения',
					'rules' => '',
					'type' => 'string'
				),  
				array(
					'field' => 'EmergencyTeam_number',
					'label' => 'Бригада скорой медицинской помощи',
					'rules' => '',
					'type' => 'string'
				),   
				array(
					'field' => 'AcceptTime',
					'label' => 'Время приема вызова',
					'rules' => '',
					'type' => 'string'
				),  
				array(
					'field' => 'TransTime',
					'label' => 'Передача вызова бригаде СМП',
					'rules' => '',
					'type' => 'string'
				), 
				array(
					'field' => 'GoTime',
					'label' => 'Время выезда на вызов',
					'rules' => '',
					'type' => 'string'
				),   
				array(
					'field' => 'TransportTime',
					'label' => 'Начало транспортировки больного',
					'rules' => '',
					'type' => 'string'
				),  
				array(
					'field' => 'EndTime',
					'label' => 'Время отзвона / Окончание вызова',
					'rules' => '',
					'type' => 'string'
				),  
				array(
					'field' => 'BackTime',
					'label' => 'Время возвращения на подстанцию',
					'rules' => '',
					'type' => 'string'
				),  
				array(
					'field' => 'SummTime',
					'label' => 'Время, затраченное, на выполнение вызова',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'UslugaTLT',
					'label' => 'Проведение ТЛТ (код услуги)',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'TLTres',
					'label' => 'Проведение ТЛТ',
					'rules' => '',
					'type' => 'string'
				)
			),
			'saveKvsInOKS' => array(
				array(
					'field' => 'MorbusType_id',
					'label' => 'Тип заболевания',
					'rules' => '',
					'type' => 'int'
				),  
				array(
					'field' => 'Diag_id',
					'label' => 'Код диагноза',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Registry_method',
					'label' => 'Метод',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Person_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'EvnPS_NumCard',
					'label' => '№ медицинской карты',
					'rules' => '',
					'type' => 'string'
				), 
				array(
					'field' => 'PainDT',
					'label' => 'Время начала болевых симптомов',
					'rules' => '',
					'type' => 'string',
				),
				array(
					'field' => 'ECGDT',
					'label' => 'Время проведения ЭКГ',
					'rules' => '',
					'type' => 'string',
				),
				array(
					'field' => 'EcgUsluga_id',
					'label' => 'Идентификатор услуги экг',
					'rules' => '',
					'type' => 'string',
				),
				array(
					'field' => 'ResultECG',
					'label' => 'Результат ЭКГ',
					'rules' => '',
					'type' => 'string',
				),  
				array(
					'field' => 'TLTDT',
					'label' => 'Время проведения ТЛТ',
					'rules' => '',
					'type' => 'string',
				),
				array(
					'field' => 'LpuDT',
					'label' => 'Время прибытия в медицинскую организацию',
					'rules' => '',
					'type' => 'string',
				),
				array(
					'field' => 'DiagOKS',
					'label' => 'код+диагноз',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'MOHospital',
					'label' => 'МО госпитализации',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'ZonaCHKV',
					'label' => 'Зона ответственности проведения ЧКВ',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'TimeFromEnterToChkv',
					'label' => 'Время от начала болевого синдрома до ЧКВ, мин',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'LeaveType_Name',
					'label' => 'Исход госпитализации',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'CmpCallCard_id',
					'label' => 'Карта вызова',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'diagDir',
					'label' => 'Диагноз направившего учреждения',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'diagPriem',
					'label' => 'Диагноз приемного отделения',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'LpuSection',
					'label' => 'Отделение госпитализации',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'KAGDT',
					'label' => 'Дата/время проведения КАГ',
					'rules' => '',
					'type' => 'string'
				)
			),   
			'getOksId'=>array(
				array(
					'field' => 'Person_id',
					'label' => 'Person_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'EvnPS_NumCard',
					'label' => 'EvnPS_NumCard',
					'rules' => '',
					'type' => 'string'
				)
			),
			'savePrognosDiseases'=>array(
				array(
					'field' => 'PrognosOslDiagList',
					'label' => 'Осложнения основного заболевания',
					'rules' => '',
					'type' => 'json_array'
				),
				array(
					'field' => 'BSKDiagPrognos_DescriptDiag',
					'label' => 'Уточнение основного заболевания по МКБ',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Person_id',
					'rules' => '',
					'type' => 'int'
				)
			),
			'loadPrognosDiseases' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Person_id',
					'rules' => '',
					'type' => 'id'
				)
			),
			'saveInPersonRegister' => array(
				array(
					'field' => 'PersonRegister_id',
					'label' => 'PersonRegister_id',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Mode',
					'label' => 'Mode',
					'rules' => 'trim',
					'type' => 'string'

				),
				array(
					'field' => 'Person_id',
					'label' => 'Человек',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'MorbusType_id',
					'label' => 'Тип регистра',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Morbus_id',
					'label' => 'Morbus_id',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Diag_id',
					'label' => 'Диагноз',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'PersonRegister_Code',
					'label' => 'Код записи',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'PersonRegister_setDate',
					'label' => 'PersonRegister_setDate',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'PersonRegister_disDate',
					'label' => 'PersonRegister_disDate',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'PersonRegisterOutCause_id',
					'label' => 'Причина исключения из регистра',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedPersonal_iid',
					'label' => 'Добавил человека в регистр - врач',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_iid',
					'label' => 'Добавил человека в регистр - ЛПУ',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'MedPersonal_did',
					'label' => 'Кто исключил человека из регистра - врач',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_did',
					'label' => 'Кто исключил человека из регистра - ЛПУ',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnNotifyBase_id',
					'label' => 'EvnNotifyBase_id',
					'rules' => '',
					'type' => 'id'
				)
			),
			'checkPersonInRegister' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Person_id',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MorbusType_id',
					'label' => 'MorbusType_id',
					'rules' => '',
					'type' => 'id'
				)
			)
		);
		
	}
	/**
	 *  Объём талии в перцентелях
	 */
	function getWaistPercentel($data)
	{
		return $this->dbmodel->getWaistPercentel($data);
	}
	/**
	 * Проставление признака просмотра анкеты
	 */
	function setIsBrowsed() {

		$data = $this->ProcessInputData('setIsBrowsed', true);
		if ($data === false) return false;
		$result = $this->dbmodel->setIsBrowsed($data);
		$this->ReturnData($result);
	}

	function loadBSKObjectTree() {
		$data = $this->ProcessInputData('loadBSKrootTree', true);
		if ($data === false) { return false; }

		$nodes = array();
		$BSKObject = array();

		if ($data['node'] == 'root') {
			// $items = array(
			// 	'BSKObject' => array('id' => 'BSKObject', 'object' => 'BSKObject', 'text' => 'Предметы наблюдения', 'leaf' => false)
			// 	);
			// $nodes = array_values($items);
			$list = $this->dbmodel->loadBSKObjectTree($data);
			foreach($list as $k => $v) {
				$riskGroup = $v['riskGroup']? ' ('.$v['riskGroup'].')' : '';
				$BSKObject[] = array(
					'id' => $v['BSKObject_id'],
					'object' => $v['MorbusType_id'],
					'MorbusType_id' => $v['MorbusType_id'],
					'Person_id' => $v['Person_id'],
					'text' => $v['MorbusType_Name'].$riskGroup,
					'leaf' => false,
					'grid'=>false,
					'isLast' => 2,
					'BSKRegistry_setDate' => $v['BSKRegistry_setDate'],
					'riskGroup' => $v['riskGroup']
				);
			}
			$nodes = array_values($BSKObject);
		} else {
			switch($data['object']) {
				case 'BSKObject':
					// $list = $this->dbmodel->loadBSKObjectTree($data);
					// foreach($list as $k => $v) {
					// 	$riskGroup = $v['riskGroup']? ' ('.$v['riskGroup'].')' : '';
					// 	$BSKObject[] = array(
					// 		'id' => $v['BSKObject_id'],
					// 		'object' => $v['MorbusType_id'],
					// 		'MorbusType_id' => $v['MorbusType_id'],
					// 		'Person_id' => $v['Person_id'],
					// 		'text' => $v['MorbusType_Name'].$riskGroup,
					// 		'leaf' => false,
					// 		'grid'=>false
					// 	);
					// }
					// $nodes = array_values($BSKObject);
				break;
				default: 
					$data['MorbusType_id'] = $data['object'];
					$list = $this->dbmodel->loadBSKObjectListTree($data);
					foreach($list as $k => $v) {
						$iconCls = '';
						if ($v['BSKObservElementGroup_id'] == 32) $iconCls = 'hospitalization16';
						if ($v['BSKObservElementGroup_id'] == 44) $iconCls = 'ambulance16';
						$BSKObject[] = array(
							'BSKRegistry_id' => $v['BSKRegistry_id'],
							'object' => $v['BSKRegistry_id'],
							'MorbusType_id' => $v['MorbusType_id'],
							'Person_id' => $v['Person_id'],
							'text' => $v['BSKRegistry_setDate'],
							'iconCls' => $iconCls,
							'leaf' => true,
							'isLast' => $v['isLast'],
							'BSKRegistry_setDate' => $v['BSKRegistry_setDateFormat'],
							'riskGroup' => $v['BSKRegistry_riskGroup'],
							'BSKObject_id' => $v['BSKObject_id']
						);
					}
					$nodes = array_values($BSKObject);
				break;
			}
		}

		return $this->ReturnData($nodes);
	}

	function getBSKRegistryFormTemplate() {
		$data = $this->ProcessInputData('getBSKRegistryFormTemplate', true);
		if ($data === false) { return false; }
		$list = $this->dbmodel->getBSKRegistryFormTemplate($data);
		$BSKRegistry = $this->dbmodel->loadBSKObjectListTree($data);//для проверки на существующие анкеты
		$result = array();
		$groups = array();
		$groupsOKS = array();
		foreach ($list as $k => $v) {
			if (isset($v['Person_id'])) {
				$result['BSKRegistry_setDateFormat'] = $v['BSKRegistry_setDateFormat'];
				$result['BSKRegistry_setDate']       = $v['BSKRegistry_setDate'];
				$result['BSKRegistry_id']            = $v['BSKRegistry_id'];
				$result['BSKRegistry_isBrowsed']     = $v['BSKRegistry_isBrowsed'];
				$result['MorbusType_Name']           = $v['MorbusType_Name'];
				$result['BSKRegistry_nextDate']      = $v['BSKRegistry_nextDate'];
				$result['BSKRegistry_riskGroup']     = $v['BSKRegistry_riskGroup'];
			}
			if ($data['MorbusType_id'] == 19) {
				if (isset($v['BSKRegistryData_id']) && $v['BSKObservElementGroup_id'] !== '29') //для вывода всех групп ОКС, если непонятно откуда анкета
					$groups[$v['BSKRegistryFormTemplateData_GroupNum']] = array(
						'id' => $v['BSKObservElementGroup_id'],
						'group' => $v['BSKObservElementGroup_name']
					);
				else $groupsOKS[$v['BSKRegistryFormTemplateData_GroupNum']] = array(
					'id' => $v['BSKObservElementGroup_id'],
					'group' => $v['BSKObservElementGroup_name']
				);
			} else
				$groups[$v['BSKRegistryFormTemplateData_GroupNum']] = array(
					'id' => $v['BSKObservElementGroup_id'],
					'group' => $v['BSKObservElementGroup_name']
				);
		}
		$result['questions'] = $list;
		$result['groups'] = count($groups)>0?$groups:$groupsOKS;//специально для нередактируемых и пустых ОКС
		$result['BSKRegistry'] = $BSKRegistry;
	
		return $this->ReturnData($result);

	}

	function getBSKRegistryElementValues() {
		$data = $this->ProcessInputData('getBSKRegistryElementValues', true);
		if ($data === false) { return false; }
		$list = $this->dbmodel->getBSKRegistryElementValues($data);
		$result = array();
		foreach ($list as $k => $v) {
			if ($v['BSKObservDict_id'] !== null && $v['ReferenceECGResult_id'] == null && $v['Lpu_id'] == null) {
				$BSKObservElementValues_id = $v['BSKObservDict_id'];
				$BSKObservElementValues_data = $v['BSKObservDict_name'];
			} else if ($v['BSKObservElementValues_id'] == null) {
				$BSKObservElementValues_id = $v['ReferenceECGResult_id'] == null? $v['Lpu_id'] : $v['ReferenceECGResult_id'];
				$BSKObservElementValues_data = $v['ReferenceECGResult_id'] == null? $v['Lpu_Nick'] : $v['ReferenceECGResult_Name'];
			} else { 
				$BSKObservElementValues_id = $v['BSKObservElementValues_id'];
				$BSKObservElementValues_data = $v['BSKObservElementValues_data'];
			}
			$result[$v['BSKObservElement_id']][] = array(
				0 => $BSKObservElementValues_id,
				1 => $BSKObservElementValues_data,
				2 => $v['BSKObservElementSign_id'],
				3 => $v['BSKObservElementSign_name'],
				4 => $v['Diag_id'],
				5 => $v['BSKObservDict_id']
			);
		}
	
		return $this->ReturnData($result);

	}

	function getPersonElementValuesDiag() {
		$data = $this->ProcessInputData('getPersonElementValuesDiag', true);
		if ($data === false) { return false; }
		$list = $this->dbmodel->getPersonElementValuesDiag($data);
		$result = array();
		foreach ($list as $k => $v) {
			$result[$v['BSKObservElement_id']] = array(
				'Diag_FullName' => $v['Diag_FullName'],
				'BSKObservElementValues_id' => $v['BSKObservElementValues_id'],
				'Diag_id' => $v['Diag_id']
			);
		}
	
		return $this->ReturnData($result);

	}

	function getBSKObjectWithoutAnket() {

		$data = $this->ProcessInputData('getBSKObjectWithoutAnket', true);

		if ($data === false) {
			return false;
		}
		$list = $this->dbmodel->getBSKObjectWithoutAnket($data);

		return $this->ReturnData($list);
	}

	function loadBSKEvnGrid() {

		$data = $this->ProcessInputData('loadBSKEvnGrid', true);

		if ($data === false) {
			return false;
		}
		$list = $this->dbmodel->loadBSKEvnGrid($data);

		return $this->ReturnData($list);
	}

	function saveBSKRegistry() {

		$data = $this->ProcessInputData('saveBSKRegistry', true);

		if ($data === false) {
			return false;
		}
		$list = '';
		$BSKRegistryData = array();
		//расчет группы риска для скрининга, для диагнозов можно переписать на использование BSKObservElementSign_id в ответе
		$signs = array(
			'ПП' => null,
			'КЗ' => null,
			'КФ' => null,
			'PN' => null,
			'' => null
		);
		$sex_id = $data['Sex_id'];
		$age = $data['Age'];
		foreach ($data['QuestionAnswer'] as $answers) {
			//не будем сохранять пустые значения, для обновления пустые значения тоже нужны - если уже есть сохраненный ответ
			if ($answers[2] !== '' || ($data['BSKRegistry_id'] !== null && $answers[7] !==null)) {
				//сохранение в нужное поле в зависимости от формата
				$format_id = $answers[3];
				switch ((int) $format_id) {
					//Combobox
					case 8:
						$BSKRegistryData[] = array (
							'BSKObservElement_id' => $answers[0],
							'BSKObservElementValues_id' => (int) $answers[1],
							'BSKRegistryData_AnswerText' => null,
							'BSKRegistryData_AnswerInt' => null,
							'BSKRegistryData_AnswerFloat' => null,
							'BSKRegistryData_AnswerDT' => null,
							'BSKRegistryData_id' => $answers[7]
						);
					break;
					//TextfieldInt
					case 11:
						$BSKRegistryData[] = array (
							'BSKObservElement_id' => $answers[0],
							'BSKObservElementValues_id' => null,
							'BSKRegistryData_AnswerText' => null,
							'BSKRegistryData_AnswerInt' => (int) $answers[1],
							'BSKRegistryData_AnswerFloat' => null,
							'BSKRegistryData_AnswerDT' => null,
							'BSKRegistryData_id' => $answers[7]
						);
					break;
					//TextfieldFloat
					case 12:
						$BSKRegistryData[] = array (
							'BSKObservElement_id' => $answers[0],
							'BSKObservElementValues_id' => null,
							'BSKRegistryData_AnswerText' => null,
							'BSKRegistryData_AnswerInt' => null,
							'BSKRegistryData_AnswerFloat' => $answers[1],
							'BSKRegistryData_AnswerDT' => null,
							'BSKRegistryData_id' => $answers[7]
						);
					break;
					//все остальное в текст
					default:
						$BSKRegistryData[] = array (
							'BSKObservElement_id' => $answers[0],
							'BSKObservElementValues_id' => null,
							'BSKRegistryData_AnswerText' => $answers[1],
							'BSKRegistryData_AnswerInt' => null,
							'BSKRegistryData_AnswerFloat' => null,
							'BSKRegistryData_AnswerDT' => null,
							'BSKRegistryData_id' => $answers[7]
						);
					break;
				}
				//расчет группы риска
				if ($answers[2] !== '') {
					$BSKObservElement_id = (int) $answers[0];
					$value = $answers[2];
					$unit = $answers[4];
					//признак ответа из БД
					if ($answers[5] !== '') {
						$signs[$answers[6]] += 1;
					}
					
					switch ($BSKObservElement_id) {
						//Пол и возраст
						case 25:
							if ($sex_id == 1 && $age > 30) {
								$signs['ПП'] += 1;
							} elseif ($sex_id == 2 && $age > 40) {
								$signs['ПП'] += 1;
							}
						break;
						//Диабет
						case 34:
							$p = "#^(E10\.0|E10\.1|E10\.8|E10\.9|E11\.0|E11\.1|E11\.8|E14\.0|E14\.8|E12\.0|E13\.0)#i";
							if (preg_match($p, $value)) {
								$signs['КЗ'] += 1;
							}
							$p = "#^(E10\.2|E10\.3|E10\.4|E10\.6|E11\.2|E11\.4|E11\.6|E14\.2|E14\.2|E14\.3|E14\.4|E14\.5|E14\.7|N18\.0|N18\.9|N19)#i";
							if (preg_match($p, $value)) {
								$signs['КФ'] += 1;
							}
						break;
						//Хроническое заболевание почек
						case 35:
							$p = "#^(N18\.8|N00\.2|N00\.4|N00\.5|N01\.2|N01\.5|N02\.2|N02\.4|N02\.7|N03\.3|N03\.5|N03\.7|N04\.3|N04\.5|N05\.2|N05\.4|N05\.7|N11\.8|N11\.9|N11\.0|E14\.4|E14\.5|E14\.7|N18\.0|N18\.9|N19)#i";
							if (preg_match($p, $value)) {
								$signs['КЗ'] += 1;
							}
							$p = "#^(N18\.0)#";
							if (preg_match($p, $value)) {
								$signs['КФ'] += 1;
							}
						break;
						//Щитовидка
						case 36:
							$p = "#^(E02|E03\.0|E03\.1|E03\.3|E03\.8)#i";
							if (preg_match($p, $value)) {
								$signs['ПП'] += 1;
							}
						break;
						//аутоиммунные заболевания
						case 37:
							$p = "#^(С90\.0|С88\.0|М32\.0|М32\.8|М32\.9|L10\.1|L10\.2|L10\.3|L10\.4|L10\.5|L10\.8|L10\.9|L40\.0|L40\.1|L40\.2|L40\.3|L40\.4|L40\.5|L40\.8|L40\.9)#i";
							if (preg_match($p, $value)) {
								$signs['ПП'] += 1;
							}
						break;
						//Неалкогольная жировая болезнь печени
						case 43:
						//Наличие у пациента камней в желчном пузыре
						case 44:
							$p = "#^(K76\.0|К80\.2|К80\.4|К80\.8)#i";
							if (preg_match($p, $value)) {
								$signs['ПП'] += 1;
							}
						break;
						//Болезнь накопления гликогена
						case 79:
							$p = "#^(E74\.0)#";
							if (preg_match($p, $value)) {
								$signs['ПП'] += 1;
							}
						break;
						//Объём талии
						case 109:
							if ($age < 16) {
								$dataWP = array(
									'age' => $age,
									'waist' => $value,
									'Sex_id' => $sex_id
								);
								
								$prc = $this->getWaistPercentel($dataWP);
								
								if ((isset($prc[0]['prc']) ? $prc[0]['prc'] : 95) > 90) {
									$signs['ПП'] += 1;
								}
							}
							//старше 16 лет в см
							elseif ($age >= 16) {
								if ($sex_id == 1 && $value >= 94) {
									$signs['ПП'] += 1;
								} elseif ($sex_id == 2 && $value >= 80) {
									$signs['ПП'] += 1;
								}
							}
						break;
						//Индекс массы тела
						case 110:
							if ($value > 30) {
								$signs['ПП'] += 1;
							}
						break;
						//Липопротеины низкой плотности
						case 88:
							$value = strtr($value, array(
								',' => '.'
							));
							if ($value > 4.9) {
								$signs['КФ'] += 1;
							} elseif ($value > 4) {
								$signs['КФ'] += 1;
							}
							if ($value > 3) {
								$signs['КЗ'] += 1;
							} elseif ($value >= 2.85) {
								$signs['КЗ'] += 1;
							}
							
						break;
						//Общий холестерин
						case 89:
							$value = strtr($value, array(
								',' => '.'
							));
							if ($value > 7.5) {
								$signs['КФ'] += 1;
							} elseif ($value > 6.7) {
								$signs['КФ'] += 1;
							} elseif ($value > 5) {
								$signs['КЗ'] += 1;
							} elseif ($value >= 4.4) {
								$signs['КЗ'] += 1;
							}
						break;
						//Липопротеины высокой плотности
						case 90:
							$value = strtr($value, array(
								',' => '.'
							));
							if ($age >= 2 && $age <= 16) {
								if ($value < 0.9 || $value >= 1.6) {
									$signs['КЗ'] += 1;
								}
							} elseif ($age > 16 && $sex_id == 1) {
								if ($value < 0.7 || $value >= 1.6) {
									$signs['КЗ'] += 1;
								}
							} elseif ($age > 16 && $sex_id == 2) {
								if ($value < 0.9 || $value >= 1.6) {
									$signs['КЗ'] += 1;
								}
							}
						break;
						//Триглицериды
						case 91:
							$value = strtr($value, array(
								',' => '.'
							));
							if ($age >= 2 && $age <= 16) {
								if ($value >= 0.85) {
									$signs['КЗ'] += 1;
								}
							} elseif ($age > 16) {
								if ($value > 1.7) {
									$signs['КЗ'] += 1;
								}
							}
						break;
						//АпоВ-100 (вредный холестерин)
						//используем только ммоль/л
						case 92:
							$value = strtr($value, array(
								',' => '.'
							));
							$koef  = ($unit == 'ммоль/л') ? 1 : 40;
							//echo '<h1>' . $value . '</h1>';
							if ($age >= 2 && $age <= 16) {
								if ($value >= 90 * $koef) {
									$signs['КЗ'] += 1;
								}
							} elseif ($age > 16) {
								if ($value > 100 / $koef) {
									$signs['КЗ'] += 1;
								}
							}
						break;
						//Апо А1 (полезный холестерин)
						//используем только ммоль/л
						case 93:
							$value = strtr($value, array(
								',' => '.'
							));
							$koef  = ($unit == 'ммоль/л') ? 1 : 40;
							
							if ($value <= 110 * $koef) {
								$signs['КЗ'] += 1;
							}
							
						break;
						//Липопротеин (а)
						//используем только ммоль/л
						case 94:
							$value = strtr($value, array(
								',' => '.'
								));
							if ($value <= 1.25) {
								$signs['КЗ'] += 1;
							}
						break;
						//СРБ вч (Ц-реактивный белок высокочувствительным методом)
						case 95:
							$value = strtr($value, array(
								',' => '.'
							));
							
							if ($value >= 2) {
								$signs['КЗ'] += 1;
							}
							
						break;
						//Почечные пробы.Скорость клубочковой фильтрации.
						case 96:
							$value = strtr($value, array(
								',' => '.'
							));
							
							if ($age >= 2 && $age <= 16) {
								if ($value < 60) {
									$signs['КЗ'] += 1;
								}
							} elseif ($age > 16) {
								if ($value < 30) {
									$signs['КЗ'] += 1;
								}
							}
						break;
						//Почечные пробы.Микроальбуминурия)/протеинурия (МАУ).
						case 97:
							$value = strtr($value, array(
								',' => '.'
							));
							if ($unit == 'кв. м.') {
								if ($age >= 2 && $age <= 16) {
									if ($value >= 30) {
										$signs['КФ'] += 1;
									}
								} elseif ($age > 16) {
									if ($value > 300) {
										$signs['КФ'] += 1;
									}
								}
							}
							if ($unit == 'мл/мин/1,73 кв.м') {
								if ($age >= 2 && $age <= 16) {
									if ($value >= 3.4 && $value <= 34) {
										$signs['КЗ'] += 1;
									}
								} elseif ($age > 16) {
									if ($value > 34) {
										$signs['КФ'] += 1;
									}
								}
							}
						break;
						//Почечные пробы. Соотношение микроальбумина/ креатинина в моче
						case 98:
							$value = strtr($value, array(
								',' => '.'
							));
							
							if ($age >= 2 && $age <= 16) {
								if ($value > 30 && $value < 300) {
									$signs['КЗ'] += 1;
								}
							} elseif ($age > 16) {
								if ($value > 300) {
									$signs['КФ'] += 1;
								}
							}
						break;
						//Глюкоза в крови (капиллярная)
						case 99:
							$value = strtr($value, array(
								',' => '.'
							));
						
							if ($age >= 2 && $age <= 16) {
								if ($value >= 5.6 && $value < 7) {
									$signs['КЗ'] += 1;
								}
							} elseif ($age > 16) {
								if ($value >= 7) {
									$signs['КФ'] += 1;
								}
							}
						break;
						//Глюкоза в крови (венозная)
						case 100:
							$value = strtr($value, array(
								',' => '.'
							));
							
							if ($age >= 2 && $age <= 16) {
								if ($value >= 6.1) {
									$signs['КЗ'] += 1;
								}
							} elseif ($age > 16) {
								if ($value >= 7) {
									$signs['КФ'] += 1;
								}
							}
						break;
						//Через 2 часа после перорального глюкозо –толерантного теста
						case 101:
							$value = strtr($value, array(
								',' => '.'
							));
							
							if ($age >= 2 && $age <= 16) {
								if ($value >= 7.8) {
									$signs['КЗ'] += 1;
								}
							} elseif ($age > 16) {
								if ($value >= 11.1) {
									$signs['КФ'] += 1;
								}
							}
						break;
						//Ветви дуги аорты. Толщина комплекса интима-медиа
						// Активация ПН "АВДА"
						case 102:
							if ($age >= 16 && $age < 40) {
								if ($value > 1.3) {
									$signs['PN'] += 1;
								} elseif ($value > 0.7) {
									$signs['КЗ'] += 1;
								}
							} elseif ($age >= 40 && $age < 50 && $sex_id == 1) {
								if ($value > 1.3) {
									$signs['PN'] += 1;
								} elseif ($value > 0.8) {
									$signs['КЗ'] += 1;
								}
							} elseif ($age >= 50 && $sex_id == 1) {
								if ($value > 1.3) {
									$signs['PN'] += 1;
								} elseif ($value > 0.9) {
									$signs['КЗ'] += 1;
								}
							} elseif ($age >= 40 && $age < 60 && $sex_id == 2) {
								if ($value > 1.3) {
									$signs['PN'] += 1;
								} elseif ($value > 0.8) {
									$signs['КЗ'] += 1;
								}
							} elseif ($age >= 60 && $sex_id == 2) {
								if ($value > 1.3) {
									$signs['PN'] += 1;
								} elseif ($value > 0.9) {
									$signs['КЗ'] += 1;
								}
							}
						break;
							
						//Артерии нижних конечностей. Толщина комплекса интим
						case 104:
							if ($value > 1.2) {
								$signs['КЗ'] += 1;
							} elseif ($value >= 1.1) {
								$signs['КЗ'] += 1;
							}
						break;
						//Индекс кальцификации коронарных артерий (индекса
						//Активация активируется ПН "ИБС"
						case 106:
							if ($age >= 16) {
								if ($value >= 400) {
									$signs['PN'] += 1;
								} elseif ($value >= 100 && $value < 400) {
									$signs['КФ'] += 1;
								} elseif ($value >= 11 && $value < 99) {
									$signs['КЗ'] += 1;
								}
							}
						break;
							
						case 110:
							if ($age >= 2) {
								if ($value < 19 || $value >= 30) {
									$signs['ПП'] += 1;
								}
							}
						break;
						default:
						break;
					}
				}
			}
		}
		if (isset($signs['PN']) && $signs['PN'] > 0)
		$riskGroup = 3;
		else {
			$kzPP = (isset($signs['КЗ']) && $signs['КЗ'] > 0) ? $signs['КЗ'] * 3.1 : null;
			$kfPP = (isset($signs['КФ']) && $signs['КФ'] > 0) ? $signs['КФ'] * 8 : null;
			$PP   = (isset($signs['ПП']) && $signs['ПП'] > 0) ? $signs['ПП'] : null;
		
			$allPP = (isset($kzPP)||isset($kfPP)||isset($PP)) ? $kzPP + $kfPP + $PP : null;
			//не нужен риск для ответов без признака, только для скрининга
			if (isset($allPP) || $data['MorbusType_id'] == '84') {
				if ($allPP < 3.5)
				$riskGroup = 1;
				elseif ($allPP >= 3.5 && $allPP < 7.5) {
					$riskGroup = 2;
				} else {
					$riskGroup = 3;
				}
			} else
				$riskGroup = null;
		}
		$BSKRegistry = array(
			'Person_id' => $data['Person_id'],
			'MorbusType_id' => $data['MorbusType_id'],
			'PersonRegister_id' => $data['PersonRegister_id'],
			'BSKRegistryFormTemplate_id' => $data['BSKRegistryFormTemplate_id'],
			'BSKRegistry_setDate' => $data['BSKRegistry_setDate'],
			'BSKRegistry_riskGroup' => $riskGroup,
			'BSKRegistry_id' => $data['BSKRegistry_id'],
			'BSKRegistry_nextDate' => $data['BSKRegistry_nextDate']
		);
		if ($data['BSKRegistry_id'] == null) {
			$result = $this->dbmodel->saveBSKRegistry($data, $BSKRegistry, $BSKRegistryData);
		} else {
			$result = $this->dbmodel->updateBSKRegistry($data, $BSKRegistry, $BSKRegistryData);
		}
		$this->ReturnData($result);

	}
	/**
	* Таблица «Услуги». Отображаются операционные и общие услуги, проведённые пациенту 
	*/
	function getListUslugforEvents() {

		$data = $this->ProcessInputData('getListUslugforEvents', true);

		if ($data === false) {
			return false;
		}
		$list = $this->dbmodel->getListUslugforEvents($data);

		return $this->ReturnData($list);
	}
	/**
	* Таблица «Случаи оказания амбулаторно-поликлинической медицинской помощи»
	*/
	function getListPersonCureHistoryPL() {

		$data = $this->ProcessInputData('getListPersonCureHistoryPL', true);

		if ($data === false) {
			return false;
		}
		$list = $this->dbmodel->getListPersonCureHistoryPL($data);

		return $this->ReturnData($list);
	}
	/**
	* Таблица «Случаи оказания стационарной медицинской помощи»
	*/
	function getListPersonCureHistoryPS() {

		$data = $this->ProcessInputData('getListPersonCureHistoryPS', true);

		if ($data === false) {
			return false;
		}
		$list = $this->dbmodel->getListPersonCureHistoryPS($data);

		return $this->ReturnData($list);
	}
	/**
	* Таблица «Сопутствующие диагнозы»
	*/
	function getListPersonCureHistoryDiagSop() {

		$data = $this->ProcessInputData('getListPersonCureHistoryDiagSop', true);

		if ($data === false) {
			return false;
		}
		$list = $this->dbmodel->getListPersonCureHistoryDiagSop($data);

		return $this->ReturnData($list);
	}
	/**
	* Таблица «Постинфарктный кардиосклероз»
	*/
	function getListPersonCureHistoryDiagKardio() {

		$data = $this->ProcessInputData('getListPersonCureHistoryDiagKardio', true);

		if ($data === false) {
			return false;
		}
		$list = $this->dbmodel->getListPersonCureHistoryDiagKardio($data);

		return $this->ReturnData($list);
	}
	/**
	* Вкладка «Исследования»
	*/
	function getLabResearch() {

		$data = $this->ProcessInputData('getLabResearch', true);

		if ($data === false) {
			return false;
		}
		$list = $this->dbmodel->getLabResearch($data);

		return $this->ReturnData($list);
	}
	 /**
	* Вкладка «Обследования»
	*/
	function getLabSurveys() {

		$data = $this->ProcessInputData('getLabSurveys', true);

		if ($data === false) {
			return false;
		}
		$list = $this->dbmodel->getLabSurveys($data);

		return $this->ReturnData($list);
	}
	/**
	 * Получение рекомендаций по регистрам
	 */
	function getRecomendationByDate() {
		$data = $this->ProcessInputData('getRecomendationByDate', true);
		
		if ($data === false) {
			return false;
		}
		
		$list = $this->dbmodel->getRecomendationByDate($data);
		
		$this->ReturnData($list);
	}
	/**
	 * Получение сведений о лекарственном лечении
	 */
	function getDrugs() {
		$data = $this->ProcessInputData('getDrugs', true);
		
		if ($data === false) {
			return false;
		}
		
		$list = $this->dbmodel->getDrugs($data);
		$result = array();
		$groups = array();
		foreach ($list as $k => $v) {
				$groups[$v['num']] = array(
					'id' => $v['MorbusType_id'],
					'group' => $v['MorbusType_Name']
				);
		}
		$result['questions'] = $list;
		$result['groups'] = $groups;
		
		$this->ReturnData($list);
	}
	/**
	 * Получение данных регистра для сравнения
	 */ 
	function getCompare() {
		$data = $this->ProcessInputData('getCompare', true);
		
		if ($data === false) {
			return false;
		}
		
		$list = $this->dbmodel->getCompare($data);
		
		$this->ReturnData($list);
	}
	/**
	 *  Запись в регистр БСК в ПН ОКС с АРМ Админситратора СМП / ... / Подстанции СМП
	 */
	function saveInOKS() {
		$data = $this->ProcessInputData('saveInOKS', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->saveInOKS($data);

		$this->ReturnData($response);  
	}
	/**
	 *  Запись в регистр БСК в ПН ОКС с АРМ Админситратора СМП / ... / Подстанции СМП
	 */
	function saveKvsInOKS() {
		$data = $this->ProcessInputData('saveKvsInOKS', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->saveKvsInOKS($data);

		$this->ReturnData($response);  
	}
	/**
	*  Данные из регистра БСК по номеру КВС и Person_id
	*/ 
	function getOksId(){
		$data = $this->ProcessInputData('getOksId', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->getOksId($data);

		$this->ReturnData($response);
	}
	/**
	 *	Чтение данных для прогнозируемых осложнений основного заболевания
	 */
	function loadPrognosDiseases() {
		$data = $this->ProcessInputData('loadPrognosDiseases', true);
		if($data){
			$response = $this->dbmodel->loadPrognosDiseases($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
		}
	}
	/**
	* Сохранение наличие прогнозируемых осложнений основного заболевания
	*/
	function savePrognosDiseases() {
		$data = $this->ProcessInputData('savePrognosDiseases', true);

		if ($data === false) {
			return false;
		}

		$result = $this->dbmodel->savePrognosDiseases($data);

		$this->ReturnData($result);
	}
	 /**
	  * Добавления пациента в PersonRegister
	  */
	 function saveInPersonRegister() {

		$checkPersonInRegister = $this->checkPersonInRegister();

		if ($checkPersonInRegister !== false) {
			return $this->ReturnData(array(
				'success' => false,
				'Error_Msg' => toUTF('Данный пациент уже присутствует в регистре БСК по данному предмету наблюдения!')
			));
		}

		$data = $this->ProcessInputData('saveInPersonRegister', true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->saveInPersonRegister($data);
		$this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
	}
	/**
	 *  Проверка наличия пациента в регистре по предмету наблюдения
	*/
	function checkPersonInRegister() {
		$data = $this->ProcessInputData('checkPersonInRegister', true);

		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->checkPersonInRegister($data);

		return $response;
	}

}
