<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PacketPrescr - пакетами назначений
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2018 Swan Ltd.
 * @author			Dmitry Vlasenko
 * @version			07.05.2018
 * 
 * @property PacketPrescr_model $dbmodel  
 */
class PacketPrescr extends swController{
	public $inputRules = array(
		'createPacketPrescr' => array(
			array(
				'field' => 'Evn_id',
				'label' => 'Идентификатор случая',
				'rules' => '',
				'type'  => 'id'
			),
			array(
				'field' => 'PacketPrescr_id',
				'label' => 'Идентификатор пакета',
				'rules' => '',
				'type'  => 'id'
			),
			array(
				'field' => 'PacketPrescr_Name',
				'label' => 'Название пакета',
				'rules' => 'required',
				'type'  => 'string'
			),
			array(
				'field' => 'PacketPrescr_Descr',
				'label' => 'Краткое описание',
				'rules' => '',
				'type'  => 'string'
			),
			array(
				'field' => 'PacketPrescrVision_id',
				'label' => 'Видимость',
				'rules' => 'required',
				'type'  => 'id'
			),
			array(
				'field' => 'Diag_id',
				'label' => 'Диагноз',
				'rules' => '',
				'type'  => 'string'
			),
			array(
				'field' => 'PacketPrescr_SaveLocation',
				'label' => 'Запоминать места оказания услуга',
				'rules' => '',
				'type'  => 'checkbox'
			),
			array(
				'field' => 'MedPersonal_id',
				'label' => 'Идентификатор сотрудника',
				'rules' => 'required',
				'type'  => 'id'
			),
			array(
				'field' => 'PersonAgeGroup_id',
				'label' => 'Идентификатор возрастной группы',
				'rules' => '',
				'type'  => 'id'
			),
			array(
				'field' => 'Sex_id',
				'label' => 'Идентификатор пола пациента',
				'rules' => '',
				'type'  => 'id'
			)
		),
		'loadPacketPrescrList' => array(
			array(
				'field' => 'Diag_id',
				'label' => 'Диагноз',
				'rules' => '',
				'type'  => 'id'
			),
			array(
				'field' => 'Evn_id',
				'label' => 'Идентификатор случая',
				'rules' => '',
				'type'  => 'id'
			),
			array(
				'field' => 'mode',
				'label' => 'Режим загрузки',
				'rules' => '',
				'type'  => 'string'
			),
			array(
				'field' => 'node',
				'label' => 'Режим загрузки',
				'rules' => '',
				'type'  => 'string'
			),
			array(
				'field' => 'onlyFavor',
				'label' => 'Режим загрузки',
				'rules' => '',
				'type'  => 'string'
			),
			array(
				'field' => 'MedPersonal_id',
				'label' => 'Идентификатор сотрудника',
				'rules' => 'required',
				'type'  => 'id'
			),
			array(
				'field' => 'query',
				'label' => 'строка фильтра',
				'rules' => '',
				'type'  => 'string'
			),
			array(
				'field' => 'Sex_Code',
				'label' => 'Пол',
				'rules' => '',
				'type'  => 'id'
			),
			array(
				'field' => 'PersonAgeGroup_Code',
				'label' => 'Возрастная группа',
				'rules' => '',
				'type'  => 'id'
			),

		),
		'loadPMUserForShareList' => array(
			array(
				'field' => 'PacketPrescr_id',
				'label' => 'Идентификатор пакета',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'query',
				'label' => 'Запрос',
				'rules' => 'trim',
				'type' => 'string'
			),
		),
		'sharePacketPrescr' => array(
			array(
				'field' => 'PacketPrescr_id',
				'label' => 'Идентификатор пакета',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'shareTo',
				'label' => 'JSON-строка',
				'rules' => 'required|trim',
				'type' => 'string'
			),
		),
		'loadCureStandartList' => array(
			array(
				'field' => 'Diag_id',
				'label' => 'Диагноз',
				'rules' => '',
				'type'  => 'id'
			),
			array(
				'field' => 'Evn_id',
				'label' => 'Идентификатор случая',
				'rules' => '',
				'type'  => 'id'
			),
			array(
				'field' => 'mode',
				'label' => 'Режим загрузки',
				'rules' => '',
				'type'  => 'string'
			)
		),
		'loadPacketForPrescrList' => array(
			array(
				'field' => 'CureStandart_id',
				'label' => 'Идентификатор МЭС',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PacketPrescr_id',
				'label' => 'Идентификатор пакета назначений',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'objectPrescribe',
				'label' => 'Вид назначения',
				'rules' => 'required',
				'type' => 'string'
			)
		),
		'savePacketPrescrForm' => array(
			array('field' => 'PacketPrescr_id', 'label' => 'Идентификатор пакета', 'rules' => '', 'type' => 'int'),
			array('field' => 'save_data', 'label' => 'Выделенные назначения', 'rules' => '', 'type' => 'string'),
			array('field' => 'parentEvnClass_SysNick', 'label' => 'Системное имя события, породившего назначение', 'rules' => '', 'default' => 'EvnSection', 'type' => 'string'),
			array('field' => 'Evn_pid', 'label' => 'Идентификатор события, породившего назначение', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'PersonEvn_id', 'label' => 'Идентификатор', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Server_id', 'label' => 'Идентификатор сервера', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'mode', 'label' => 'Режим сохранения/применения', 'rules' => 'required', 'type'  => 'string'),
			array('field' => 'LpuSection_id','label' => 'Идентификатор ','rules' => '','type' => 'id'),
			array('field' => 'checkDrug', 'label' => 'Режим проверки назначений', 'rules' => '', 'type'  => 'string'),
			array('field' => 'Person_id', 'label' => 'Пациент', 'rules' => '', 'type' => 'id')
		),
		'applySelectedPrescribe' => array(
			array('field' => 'save_data', 'label' => 'Выделенные назначения', 'rules' => '', 'type' => 'string'),
			array('field' => 'parentEvnClass_SysNick', 'label' => 'Системное имя события, породившего назначение', 'rules' => '', 'default' => 'EvnSection', 'type' => 'string'),
			array('field' => 'Evn_pid', 'label' => 'Идентификатор события, породившего назначение', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'PersonEvn_id', 'label' => 'Идентификатор', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Server_id', 'label' => 'Идентификатор сервера', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'loadComposition', 'label' => 'необходимость грузить состав', 'rules' => '', 'type' => 'string')
		),
		'setPacketFavorite' => array(
			array('field' => 'PacketPrescr_id', 'label' => 'Идентификатор пакета', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Packet_IsFavorite', 'label' => '', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'MedPersonal_id', 'label' => 'Идентификатор сотрудника', 'rules' => 'required', 'type'  => 'id'),
		),
		'setPacketReaded' => array(
			array('field' => 'PacketPrescrShare_id', 'label' => 'Идентификатор пакета', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'PacketPrescrShare_IsReaded', 'label' => '', 'rules' => 'required', 'type' => 'int'),
		),
		'loadEditPacketForm' => array (
			array('field' => 'PacketPrescr_id', 'label' => 'Идентификатор пакета', 'rules' => 'required', 'type' => 'id')
		),
		'deletePacket' => array (
			array('field' => 'PacketPrescr_id', 'label' => 'Идентификатор пакета', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'PacketPrescrShare_id', 'label' => 'Идентификатор отправки пакета', 'rules' => '', 'type' => 'id')
		),
		'copyPacket' => array (
			array('field' => 'PacketPrescr_id', 'label' => 'Идентификатор пакета', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'PacketPrescr_Name', 'label' => 'Название пакета', 'rules' => 'required', 'type' => 'string')
		),
		'loadDrugTemplateList' => array(
			array(
				'field' => 'MedPersonal_id',
				'label' => 'Идентификатор сотрудника',
				'rules' => 'required',
				'type'  => 'id'
			)
		),
		'loadLastSelectedDrugList' => array(
			array(
				'field' => 'MedPersonal_id',
				'label' => 'Идентификатор сотрудника',
				'rules' => 'required',
				'type'  => 'id'
			),
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор МО',
				'rules' => '',
				'type'  => 'id'
			)
		),
		'loadPacketCourseTreatEditForm' => array(
			array(
				'field' => 'PacketPrescrTreat_id',
				'label' => 'Идентификатор шаблона курса',
				'rules' => 'required',
				'type'  => 'id'
			)
		),
		'saveDrugTemplate' => array(
			array('field' => 'Template_Name','label' => 'Название шаблона','rules' => 'required','type' => 'string'),
			array('field' => 'MedPersonal_id', 'label' => 'Идентификатор сотрудника', 'rules' => 'required', 'type'  => 'id'),

			array('field' => 'EvnCourseTreat_id','label' => 'Идентификатор курса','rules' => '','type' => 'id'),
			array('field' => 'LpuSection_id','label' => 'Идентификатор ','rules' => 'required','type' => 'id'),
			array('field' => 'Morbus_id','label' => 'Идентификатор ','rules' => '','type' => 'id'),
			array('field' => 'EvnCourseTreat_setDate','label' => 'Начать','rules' => 'required','type' => 'date'),
			array('field' => 'EvnCourseTreat_CountDay','label' => 'Приемов в сутки','rules' => 'required','type' => 'int'),
			array('field' => 'EvnCourseTreat_Duration','label' => 'Продолжительность','rules' => 'required','type' => 'int'),
			array('field' => 'DurationType_id','label' => 'Тип продолжительности','rules' => 'required','type' => 'id'),
			array('field' => 'EvnCourseTreat_ContReception','label' => 'Непрерывный прием','rules' => 'required','type' => 'int'),
			array('field' => 'DurationType_recid','label' => 'Тип Непрерывный прием','rules' => 'required','type' => 'id'),
			array('field' => 'EvnCourseTreat_Interval','label' => 'Перерыв','rules' => '','type' => 'int'),
			array('field' => 'DurationType_intid','label' => 'Тип Перерыв','rules' => '','type' => 'id'),
			array('field' => 'ResultDesease_id','label' => 'Идентификатор','rules' => '','type' => 'id'),
			array('field' => 'PerformanceType_id','label' => 'Исполнение','rules' => '','type' => 'id'),
			array('field' => 'PrescriptionIntroType_id','label' => 'Способ применения','rules' => 'required','type' => 'id'),
			array('field' => 'PrescriptionTreatType_id','label' => 'Идентификатор','rules' => '','type' => 'id'),
			array('field' => 'DrugListData','label' => 'Медикаменты','rules' => 'required','type' => 'string'),
			array('field' => 'EvnPrescrTreat_IsCito','label' => 'Cito','rules' => '','type' => 'string'),
			array('field' => 'EvnPrescrTreat_Descr','label' => 'Комментарий','rules' => 'trim','type' => 'string'),
			array('field' => 'PersonEvn_id','label' => 'Идентификатор','rules' => 'required','type' => 'id'),
			array('field' => 'Server_id','label' => 'Идентификатор сервера','rules' => 'required','type' => 'int'),
			array('field' => 'PrescrDose','label' => 'Описание дозы','rules' => 'trim','type' => 'string')
		),
		'deletePacketPrescrTreat' => array(
			array(
				'field' => 'PacketPrescrTreat_id',
				'label' => 'Идентификатор шаблона курса',
				'rules' => 'required',
				'type'  => 'id'
			)
		),
		'createPacketPrescrRegime' => array(
			array('field' => 'PacketPrescr_id', 'label' => 'Идентификатор пакета', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'PrescriptionRegimeType_id', 'label' => 'Тип режима', 'rules' => 'required', 'type'  => 'id')
		),
		'createPacketPrescrDiet' => array(
			array('field' => 'PacketPrescr_id', 'label' => 'Идентификатор пакета', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'PrescriptionDietType_id', 'label' => 'Тип режима', 'rules' => 'required', 'type'  => 'id')
		),
		'createPacketPrescrProc' => array(
			array('field' => 'PacketPrescr_id', 'label' => 'Идентификатор пакета', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'PrescriptionType_Code', 'label' => 'Тип назначения', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'UslugaComplex_id', 'label' => 'Тип режима', 'rules' => 'required', 'type'  => 'id'),
			array('field' => 'PacketPrescrUsluga_Count', 'label' => 'Тип режима', 'rules' => '', 'type'  => 'id', 'default' => 1),
			array('field' => 'StudyTarget_id', 'label' => 'Тип режима', 'rules' => '', 'type'  => 'id', 'default' => 1),
			array('field' => 'MedService_id', 'label' => 'Тип режима', 'rules' => '', 'type'  => 'id')
		),
		'createPacketPrescrUsl' => array(
			array('field' => 'PacketPrescr_id', 'label' => 'Идентификатор пакета', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'PrescriptionType_Code', 'label' => 'Тип назначения', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'UslugaComplex_id', 'label' => 'Тип режима', 'rules' => 'required', 'type'  => 'id'),
			array('field' => 'PacketPrescrUsluga_Count', 'label' => 'Тип режима', 'rules' => '', 'type'  => 'id', 'default' => 1),
			array('field' => 'StudyTarget_id', 'label' => 'Тип режима', 'rules' => '', 'type'  => 'id', 'default' => 1),
			array('field' => 'MedService_id', 'label' => 'Тип режима', 'rules' => '', 'type'  => 'id')
		),
		'saveDrugTemplateToPacket' => array(
			//array('field' => 'Template_Name','label' => 'Название шаблона','rules' => 'required','type' => 'string'),
			array('field' => 'PacketPrescr_id','label' => 'Идентификатор пакета','rules' => 'required','type' => 'id'),
			array('field' => 'MedPersonal_id', 'label' => 'Идентификатор сотрудника', 'rules' => 'required', 'type'  => 'id'),

			array('field' => 'EvnCourseTreat_id','label' => 'Идентификатор курса','rules' => '','type' => 'id'),
			array('field' => 'LpuSection_id','label' => 'Идентификатор ','rules' => 'required','type' => 'id'),
			array('field' => 'Morbus_id','label' => 'Идентификатор ','rules' => '','type' => 'id'),
			array('field' => 'EvnCourseTreat_setDate','label' => 'Начать','rules' => 'required','type' => 'date'),
			array('field' => 'EvnCourseTreat_CountDay','label' => 'Приемов в сутки','rules' => 'required','type' => 'int'),
			array('field' => 'EvnCourseTreat_Duration','label' => 'Продолжительность','rules' => 'required','type' => 'int'),
			array('field' => 'DurationType_id','label' => 'Тип продолжительности','rules' => 'required','type' => 'id'),
			array('field' => 'EvnCourseTreat_ContReception','label' => 'Непрерывный прием','rules' => 'required','type' => 'int'),
			array('field' => 'DurationType_recid','label' => 'Тип Непрерывный прием','rules' => 'required','type' => 'id'),
			array('field' => 'EvnCourseTreat_Interval','label' => 'Перерыв','rules' => '','type' => 'int'),
			array('field' => 'DurationType_intid','label' => 'Тип Перерыв','rules' => '','type' => 'id'),
			array('field' => 'ResultDesease_id','label' => 'Идентификатор','rules' => '','type' => 'id'),
			array('field' => 'PerformanceType_id','label' => 'Исполнение','rules' => '','type' => 'id'),
			array('field' => 'PrescriptionIntroType_id','label' => 'Способ применения','rules' => 'required','type' => 'id'),
			array('field' => 'PrescriptionTreatType_id','label' => 'Идентификатор','rules' => '','type' => 'id'),
			array('field' => 'DrugListData','label' => 'Медикаменты','rules' => 'required','type' => 'string'),
			array('field' => 'EvnPrescrTreat_IsCito','label' => 'Cito','rules' => '','type' => 'string'),
			array('field' => 'EvnPrescrTreat_Descr','label' => 'Комментарий','rules' => 'trim','type' => 'string'),
			array('field' => 'PersonEvn_id','label' => 'Идентификатор','rules' => 'required','type' => 'id'),
			array('field' => 'Server_id','label' => 'Идентификатор сервера','rules' => 'required','type' => 'int'),
			array('field' => 'PrescrDose','label' => 'Описание дозы','rules' => 'trim','type' => 'string'),
			array('field' => 'manyDrug','label' => 'Составное лекарственное назначение','rules' => '','type' => 'string')
		),
		'deletePrescrInPacket' => array(
			array('field' => 'value','label' => 'Идентификатор назначения','rules' => 'required','type' => 'id'),
			array('field' => 'object','label' => 'Тип назначения','rules' => 'required','type' => 'string')
		)
	);

	/**
	 * PacketPrescr constructor.
	 */
	function __construct(){
		parent::__construct();
		$this->load->database();
		$this->load->model('PacketPrescr_model','dbmodel');
	}

	/**
	 * Создание нового пакета назначений
	 */
	function createPacketPrescr(){
		$data = $this->ProcessInputData('createPacketPrescr',true);
		if ($data === false) { return false; }
		if(empty($data['Evn_id']) && empty($data['PacketPrescr_id']))
			return array('Error_Msg' => 'Идентификатор случая или пакета обязателен');
		if(!empty($data['Evn_id']))
			$response = $this->dbmodel->createPacketPrescr($data);
		else
			$response = $this->dbmodel->updatePacketPrescr($data);
		$this->ProcessModelSave($response, true, 'При создании нового пакета назначений произошла ошибка')->ReturnData();

		return true;
	}

	/**
	 * Создание нового пакета назначений
	 */
	function createEmptyPacketPrescr(){
		$data = $this->ProcessInputData('createPacketPrescr',true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->createEmptyPacketPrescr($data);

		$this->ProcessModelSave($response, true, 'При создании нового пакета назначений произошла ошибка')->ReturnData();

		return true;
	}

	/**
	 * Получение списка пакетов
	 */
	function loadPacketPrescrList(){
		$data = $this->ProcessInputData('loadPacketPrescrList',true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadPacketPrescrList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * @return bool
	 */
	function loadPMUserForShareList() {
		$data = $this->ProcessInputData('loadPMUserForShareList', true);
		if ($data === false) return false;

		$response = $this->dbmodel->loadPMUserForShareList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * @return bool
	 */
	function sharePacketPrescr() {
		$data = $this->ProcessInputData('sharePacketPrescr', true);
		if ($data === false) return false;

		$response = $this->dbmodel->sharePacketPrescr($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}
	/**
	 * Получение списка клин. рекомендаций
	 */
	function loadCureStandartList(){
		$data = $this->ProcessInputData('loadCureStandartList',true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadCureStandartList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Получение списка назначений на основе пакета или стандарта
	 */
	function loadPacketForPrescrList(){
		$data = $this->ProcessInputData('loadPacketForPrescrList',true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadPacketForPrescrList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}
	/**
	 * Сохраняет назначения выделенные в шаблоне
	 * @return bool
	 */
	function savePacketPrescrForm() {
		$data = $this->ProcessInputData('savePacketPrescrForm', true);
		if ($data === false) {
			return false;
		}

		if(!empty($data['save_data'])){
			$data['encode_data'] = json_decode($data['save_data'], true);
		}

		switch ($data['mode']){
			case 'apply':
				$response = $this->dbmodel->applyPacketPrescr($data);
				break;
			case 'savePacket':
				$response = $this->dbmodel->editPacketPrescr($data);
				break;
			case 'applyAllPacket':
			default:
				$response = $this->dbmodel->applyAllPacketPrescr($data);
		}
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}
	/**
	 * Постановка/снятие избранности у пакета
	 */
	function setPacketFavorite(){
		$data = $this->ProcessInputData('setPacketFavorite',true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->setPacketFavorite($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}
	/**
	 * Постановка/снятие избранности у пакета
	 */
	function setPacketReaded(){
		$data = $this->ProcessInputData('setPacketReaded',true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->setPacketReaded($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}
	/**
	 * Загрузка формы редактирования пакета назначений
	 * @return bool
	 * @throws Exception
	 */
	function loadEditPacketForm() {
		$data = $this->ProcessInputData('loadEditPacketForm', true);
		if ( $data === false ) {
			return false;
		}
		$response = $this->dbmodel->loadEditPacketForm($data);
		$this->ProcessModelList( $response, true, true)->ReturnData();

		return true;
	}
	/**
	 * Постановка/снятие избранности у пакета
	 */
	function deletePacket(){
		$data = $this->ProcessInputData('deletePacket',true);
		if ($data === false) { return false; }
		if(!empty($data['PacketPrescrShare_id']))
			$response = $this->dbmodel->deletePacketPrescrShare($data);
		else
			$response = $this->dbmodel->deletePacket($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}
	/**
	 * Получение списка шаблонов лек. назначений для врача
	 */
	function loadDrugTemplateList(){
		$data = $this->ProcessInputData('loadDrugTemplateList',true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadDrugTemplateList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}
	/**
	 * Получение списка последних добавленных лекарственных назначений
	 */
	function loadLastSelectedDrugList(){
		$data = $this->ProcessInputData('loadLastSelectedDrugList',true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadLastSelectedDrugList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}
	/**
	 *  Получение данных для формы редактирования курса с типом "Лекарственное лечение"
	 *  Входящие данные: $_POST['EvnPrescrTreat_id']
	 *  На выходе: JSON-строка
	 *  Используется: форма редактирования назначения с типом "Лекарственное лечение"
	 */
	function loadPacketCourseTreatEditForm() {
		$data = $this->ProcessInputData('loadPacketCourseTreatEditForm',true);
		if ($data === false) { return false; }



		$response = $this->dbmodel->loadPacketCourseTreatEditForm($data);
		$this->ProcessModelList($response, false, false)->ReturnData();
		return true;
	}
	/**
	 * Создание шаблона лекарственного назначения
	 */
	function saveDrugTemplate(){
		$data = $this->ProcessInputData('saveDrugTemplate',true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->saveDrugTemplate($data);
		$this->ProcessModelSave($response, true, 'При создании нового пакета назначений произошла ошибка')->ReturnData();

		return true;
	}
	/**
	 * Удаление шаблона лекарственного назначения
	 */
	function deletePacketPrescrTreat(){
		$data = $this->ProcessInputData('deletePacketPrescrTreat',true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->deletePacketPrescrTreat($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}
	/**
	 * Сохраняет назначения выделенные в шаблоне
	 * @return bool
	 */
	function applySelectedPrescribe() {
		$data = $this->ProcessInputData('applySelectedPrescribe', true);
		if ($data === false) {
			return false;
		}

		if(!empty($data['save_data'])){
			$data['encode_data'] = json_decode($data['save_data'], true);
		}

		$response = $this->dbmodel->applyPacketPrescr($data);

		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}
	/**
	 * Создание копии пакета
	 */
	function copyPacket(){
		$data = $this->ProcessInputData('copyPacket',true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->copyPacket($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}
	/**
	 * Добавление режима в пакет
	 */
	function createPacketPrescrRegime(){
		$data = $this->ProcessInputData('createPacketPrescrRegime',true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->createPacketPrescrRegime($data);

		$this->ProcessModelSave($response, true, 'При добавлении режима в пакет произошла ошибка')->ReturnData();

		return true;
	}
	/**
	 * Добавление диеты в пакет
	 */
	function createPacketPrescrDiet(){
		$data = $this->ProcessInputData('createPacketPrescrDiet',true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->createPacketPrescrDiet($data);

		$this->ProcessModelSave($response, true, 'При добавлении режима в пакет произошла ошибка')->ReturnData();

		return true;
	}
	/**
	 * Добавление процедуры в пакет
	 */
	function createPacketPrescrProc(){
		$data = $this->ProcessInputData('createPacketPrescrProc',true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->createPacketPrescrProc($data);

		$this->ProcessModelSave($response, true, 'При добавлении режима в пакет произошла ошибка')->ReturnData();

		return true;
	}
	/**
	 * Добавление услуги в пакет
	 */
	function createPacketPrescrUsl(){
		$data = $this->ProcessInputData('createPacketPrescrUsl',true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->createPacketPrescrUsl($data);

		$this->ProcessModelSave($response, true, 'При добавлении режима в пакет произошла ошибка')->ReturnData();

		return true;
	}
	/**
	 * Добавление лек. назначения в пакет
	 */
	function saveDrugTemplateToPacket(){
		$data = $this->ProcessInputData('saveDrugTemplateToPacket',true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveDrugTemplate($data);

		$this->ProcessModelSave($response, true, 'При добавлении режима в пакет произошла ошибка')->ReturnData();

		return true;
	}
	/**
	 * Удаление назначения из пакета
	 */
	function deletePrescrInPacket(){
		$data = $this->ProcessInputData('deletePrescrInPacket',true);
		if ($data === false) { return false; }


		$response = $this->dbmodel->deletePrescrInPacket($data['object'], array($data['value']));
		$this->ProcessModelSave($response, true, 'При удалении назначения из пакета произошла ошибка')->ReturnData();
		//$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}
}