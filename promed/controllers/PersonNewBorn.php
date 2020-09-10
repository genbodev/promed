<?php	defined('BASEPATH') or die ('No direct script access allowed');
class PersonNewBorn extends swController {
	public $inputRules = array(
		'loadPersonNewBornData' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPS_id',
				'label' => 'Идентификатор КВС',
				'rules' => '',
				'type' => 'id'
			)
		),
		'getPersonBirthTraumaEditWindow' => array(
			array(
				'field' => 'PersonBirthTrauma_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'delLink'=>array(
			array(
				'field' => 'Person_id',
				'label' => 'Person_id',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'loadMonitorBirthSpecGrid'=>array(
			array(
				'field' => 'Lpu_bid',
				'label' => 'Lpu_bid',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_hid',
				'label' => 'Lpu_hid',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_lid',
				'label' => 'Lpu_lid',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Period_DateRange',
				'label' => 'Period_DateRange',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'PersonNewBorn_IsHighRisk',
				'label' => 'PersonNewBorn_IsHighRisk',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'PersonNewBorn_IsNeonatal',
				'label' => 'PersonNewBorn_IsNeonatal',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_FIO',
				'label' => 'Person_FIO',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'State_id',
				'label' => 'State_id',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 100,
				'field' => 'limit',
				'label' => 'Количество записей',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 0,
				'field' => 'start',
				'label' => 'Начальная запись',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Type',
				'label' => 'Тип таблицы',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'DispensaryObservation',
				'label' => 'Диспансерное наблюдение',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'Hospitalization',
				'label' => 'Направлен на госпитализацию',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'Lpu_tid',
				'label' => 'Lpu_tid',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_ttid',
				'label' => 'Lpu_ttid',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_hhid',
				'label' => 'Lpu_hhid',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Period_DDateRange',
				'label' => 'Period_DDateRange',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'Person_SurName',
				'label' => 'Фамилия',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Person_FirName',
				'label' => 'Имя',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Person_SecName',
				'label' => 'Отчество',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'default' => 400,
				'field' => 'NumberList_id',
				'label' => 'Возраст(мес.)',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Lpu_pid',
				'label' => 'Lpu_pid',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 400,
				'field' => 'NumberList_idd',
				'label' => 'Возраст(мес.)',
				'rules' => '',
				'type' => 'int'
			),
			array(
				//'default' => 400,
				'field' => 'DirType_id',
				'label' => 'Тип направления',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 400,
				'field' => 'NumberList_aid',
				'label' => 'Возраст(лет)',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Type',
				'label' => 'Тип таблицы',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'DispensaryObservation',
				'label' => 'Диспансерное наблюдение',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'Hospitalization',
				'label' => 'Направлен на госпитализацию',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'Lpu_tid',
				'label' => 'Lpu_tid',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_ttid',
				'label' => 'Lpu_ttid',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_hhid',
				'label' => 'Lpu_hhid',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Period_DDateRange',
				'label' => 'Period_DDateRange',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'Person_SurName',
				'label' => 'Фамилия',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Person_FirName',
				'label' => 'Имя',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Person_SecName',
				'label' => 'Отчество',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'default' => 400,
				'field' => 'NumberList_id',
				'label' => 'Возраст(мес.)',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Lpu_pid',
				'label' => 'Lpu_pid',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 400,
				'field' => 'NumberList_idd',
				'label' => 'Возраст(мес.)',
				'rules' => '',
				'type' => 'int'
			),
			array(
				//'default' => 400,
				'field' => 'DirType_id',
				'label' => 'Тип направления',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 400,
				'field' => 'NumberList_aid',
				'label' => 'Возраст(лет)',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'DegreeOfPrematurity_id',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonNewBorn_IsBCG',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonNewBorn_IsHepatit',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Diag_Code_From',
				'label' => 'Основной диагноз с',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Diag_Code_To',
				'label' => 'Основной диагноз по',
				'rules' => 'trim',
				'type' => 'string'
			),
		),
		'loadPersonBirthTraumaGrid' => array(
			array(
				'field' => 'PersonNewBorn_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'BirthTraumaType_id',
				'label' => 'Идентификатор вида травмы',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'savePersonBirthTraumaEditWindow'=>array(
			array(
				'field' => 'PersonNewBorn_id',
				'label' => 'PersonNewBorn_id',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PersonBirthTrauma_setDate',
				'label' => 'PersonBirthTrauma_setDate',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'BirthTraumaType_id',
				'label' => 'BirthTraumaType_id',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PersonBirthTrauma_id',
				'label' => 'PersonBirthTrauma_id',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Server_id',
				'label' => 'Server_id',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'Diag_id',
				'label' => 'Diag_id',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PersonBirthTrauma_Comment',
				'label' => 'PersonBirthTrauma_Comment',
				'rules' => '',
				'type' => 'string'
			),
		),
		
		'deletePersonBirthTrauma'=>array(
			array(
				'field' => 'id',
				'label' => 'id',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'deleteNewbornApgarRate'=>array(
			array(
				'field' => 'id',
				'label' => 'id',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'updateNewbornApgarRate'=>array(
			array(
				'field' => 'NewbornApgarRate_id',
				'label' => 'NewbornApgarRate_id',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'NewbornApgarRate_Time',
				'label' => 'NewbornApgarRate_Time',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'NewbornApgarRate_Heartbeat',
				'label' => 'NewbornApgarRate_Heartbeat',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'NewbornApgarRate_Breath',
				'label' => 'NewbornApgarRate_Breath',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'NewbornApgarRate_SkinColor',
				'label' => 'NewbornApgarRate_SkinColor',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'NewbornApgarRate_ToneMuscle',
				'label' => 'NewbornApgarRate_ToneMuscle',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'NewbornApgarRate_Reflex',
				'label' => 'NewbornApgarRate_Reflex',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'NewbornApgarRate_Values',
				'label' => 'NewbornApgarRate_Values',
				'rules' => '',
				'type' => 'int'
			),array(
				'field' => 'PersonNewBorn_id',
				'label' => 'PersonNewBorn_id',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Server_id',
				'label' => 'Server_id',
				'rules' => 'required',
				'type' => 'id'
			)
			
		),
		'loadNewbornApgarRateGrid'=>array(
			array(
				'field' => 'PersonNewBorn_id',
				'label' => 'PersonNewBorn_id',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'AddApgarRate'=>array(
			array(
				'field' => 'PersonNewBorn_id',
				'label' => 'PersonNewBorn_id',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Server_id',
				'label' => 'Server_id',
				'rules' => 'required',
				'type' => 'int'
			)
		),
		'chekPersonNewBorn'=>array(
			array(
				'field' => 'EvnPS_id',
				'label' => 'Идентификатор КВС',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'addDeputy'=>array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Server_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'DeputyPerson_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'savePersonNewBorn' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'ApgarData',
				'label' => 'JSON-массив ApgarRate',
				'rules' => '',
				'type' => 'json_array',
				'assoc' => true
			),
			array(
				'field' => 'PersonBirthTraumaData',
				'label' => 'JSON-массив PersonBirthTraumaData',
				'rules' => '',
				'type' => 'json_array',
				'assoc' => true
			),
			array(
				'field' => 'EvnSection_mid',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnSection_id',
				'label' => 'Идентификатор движения',
				'rules' => '',
				'type' => 'id'
			),			
			array(
				'field' => 'EvnPS_id',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonNewBorn_id',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'FeedingType_id',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ChildTermType_id',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonNewBorn_IsAidsMother',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonNewBorn_IsBCG',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonNewBorn_BCGSer',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'PersonNewBorn_BCGNum',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'PersonNewBorn_BCGDate',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'PersonNewBorn_IsHepatit',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonNewBorn_HepatitSer',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'PersonNewBorn_HepatitNum',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'PersonNewBorn_Weight',
				'label' => 'PersonNewBorn_Weight',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'PersonNewBorn_Breast',
				'label' => 'PersonNewBorn_Breast',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'PersonNewBorn_Height',
				'label' => 'PersonNewBorn_Height',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'PersonNewBorn_Head',
				'label' => 'PersonNewBorn_Head',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'PersonNewBorn_HepatitDate',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'PersonNewBorn_CountChild',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Server_id',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'ChildPositionType_id',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'BirthSpecStac_id',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonNewBorn_IsRejection',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonNewBorn_IsHighRisk',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonNewBorn_IsAudio',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonNewBorn_IsNeonatal',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonNewBorn_IsBleeding',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'NewBornWardType_id',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonNewBorn_IsBreath',
				'label' => 'Дыхание',
				'rules' => '',
				'type' => 'swcheckbox'
			),
			array(
				'field' => 'PersonNewBorn_IsHeart',
				'label' => 'Сердцебиение',
				'rules' => '',
				'type' => 'swcheckbox'
			),
			array(
				'field' => 'PersonNewBorn_IsPulsation',
				'label' => 'Пульсация пуповины',
				'rules' => '',
				'type' => 'swcheckbox'
			),
			array(
				'field' => 'PersonNewBorn_IsMuscle',
				'label' => 'Произвольное сокращение мускулатуры',
				'rules' => '',
				'type' => 'swcheckbox'
			),
			array(
				'field' => 'PersonNewborn_BloodBili',
				'label' => 'Общий билирубин',
				'rules' => '',
				'type' => 'float'
			),
			array(
				'field' => 'PersonNewborn_BloodHemoglo',
				'label' => 'Гемоглобин',
				'rules' => '',
				'type' => 'float'
			),
			array(
				'field' => 'PersonNewborn_BloodEryth',
				'label' => 'Эритроциты',
				'rules' => '',
				'type' => 'float'
			),
			array(
				'field' => 'PersonNewborn_BloodHemato',
				'label' => 'Гематокрит',
				'rules' => '',
				'type' => 'float'
			),
			array(
				'field' => 'RefuseType_pid',
				'label' => 'Тип отвода от пробы',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'RefuseType_aid',
				'label' => 'Тип отвода от аудиоскрининга',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'RefuseType_bid',
				'label' => 'Тип отвода от БЦЖ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'RefuseType_gid',
				'label' => 'Тип отвода от гепатита',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadNewBornBlood' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			)
		)
	);


	/**
	 * @construct
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('PersonNewBorn_model', 'dbmodel');
	}
	/**
	 * @sdfsdf
	 */
	function savePersonNewBorn(){
		$data = $this->ProcessInputData('savePersonNewBorn', true);
		if ($data === false) return false;

		$response = $this->dbmodel->savePersonNewBorn($data);
		$this->ProcessModelSave($response, true)->ReturnData();
	}
	
	/**
	 *
	 * @return type 
	 */
	function savePersonBirthTraumaEditWindow(){
		$data = $this->ProcessInputData('savePersonBirthTraumaEditWindow', true);
		if ($data === false) return false;

		$response = $this->dbmodel->savePersonBirthTraumaEditWindow($data);
		$this->ProcessModelSave($response, true)->ReturnData();
	}

	/**
	 *
	 * @return type 
	 */
	function loadMonitorBirthSpecGrid(){
		// Получаем сессионные переменные
		$data = $this->ProcessInputData('loadMonitorBirthSpecGrid', true);
		if ($data === false) return false;

		$response = $this->dbmodel->loadMonitorBirthSpecGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		
		return true;
	}
	/**
	 *
	 * @return type 
	 */
	function loadPersonBirthTraumaGrid() {
		// Получаем сессионные переменные
		$data = $this->ProcessInputData('loadPersonBirthTraumaGrid', true);
		if ($data === false) return false;

		$response = $this->dbmodel->loadPersonBirthTraumaGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		
		return true;
	}
	/**
	 * @comment
	 */
	function loadNewbornApgarRateGrid() {
		// Получаем сессионные переменные
		$data = $this->ProcessInputData('loadNewbornApgarRateGrid', true);
		if ($data === false) return false;

		$response = $this->dbmodel->loadNewbornApgarRateGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		
		return true;
	}
	
	/**
	 *
	 * @return type 
	 */
	function getPersonBirthTraumaEditWindow() {
		// Получаем сессионные переменные
		$data = $this->ProcessInputData('getPersonBirthTraumaEditWindow', true);
		if ($data === false) return false;

		$response = $this->dbmodel->getPersonBirthTraumaEditWindow($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		
		return true;
	}
	/**
	*  Получение данных для фломы редактирования сведений о новорожденном
	*  Входящие данные: $_POST['Person_id']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования КВС
     * @return bool
     */
	function loadPersonNewBornData() {
		// Получаем сессионные переменные
		$data = $this->ProcessInputData('loadPersonNewBornData', true);
		if ($data === false) return false;

		$response = $this->dbmodel->loadPersonNewBornData($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		
		return true;
	}
	/**
	 * @comment
	 */
	function AddApgarRate() {
		// Получаем сессионные переменные
		$data = $this->ProcessInputData('AddApgarRate', true);
		if ($data === false) return false;

		$response = $this->dbmodel->AddApgarRate($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		
		return true;
	}
	/**
	 * @comment
	 */
	function addDeputy() {
		// Получаем сессионные переменные
		$data = $this->ProcessInputData('addDeputy', true);
		if ($data === false) return false;

		$response = $this->dbmodel->addDeputy($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		
		return true;
	}

	/**
	 *
	 * @return type 
	 */
	function deleteNewbornApgarRate() {
		// Получаем сессионные переменные
		$data = $this->ProcessInputData('deleteNewbornApgarRate', true);
		if ($data === false) return false;

		$response = $this->dbmodel->deleteNewbornApgarRate($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		
		return true;
	}
	/**
	 * @reee
	 */
	function deletePersonBirthTrauma() {
		// Получаем сессионные переменные
		$data = $this->ProcessInputData('deletePersonBirthTrauma', true);
		if ($data === false) return false;

		$response = $this->dbmodel->deletePersonBirthTrauma($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		
		return true;
	}
	/**
	 * @reee
	 */
	function delLink() {
		// Получаем сессионные переменные
		$data = $this->ProcessInputData('delLink', true);
		if ($data === false) return false;

		$response = $this->dbmodel->delLink($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		
		return true;
	}
	
	/**
	 * @comment
	 */
	function updateNewbornApgarRate() {
		// Получаем сессионные переменные
		$data = $this->ProcessInputData('updateNewbornApgarRate', true);
		if ($data === false) return false;

		$response = $this->dbmodel->updateNewbornApgarRate($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		
		return true;
	}
	/**
	 *
	 * @return type 
	 */
	function chekPersonNewBorn() {
		// Получаем сессионные переменные
		$data = $this->ProcessInputData('chekPersonNewBorn', true);
		if ($data === false) return false;

		$response = $this->dbmodel->chekPersonNewBorn($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		
		return true;
	}

	function loadNewBornBlood() {
		// Получаем сессионные переменные
		$data = $this->ProcessInputData('loadNewBornBlood', true);
		if ($data === false) return false;

		$response = $this->dbmodel->loadNewBornBlood($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		
		return true;
	}
	
}
