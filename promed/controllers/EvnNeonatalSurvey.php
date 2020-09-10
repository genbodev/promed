<?php	defined('BASEPATH') or die ('No direct script access allowed');


/**
 * EvnNeonatalSurvey - контроллер для работы с Наблюдение состояния младенца
 *
 * @author Muskat Boris 
 * @version			24.01.2020
 */
//function getParamsENSWindow() - Формирование параметров для окна Наблюдение состояния младенца
//function getEvnNeonatalSurvey() - Формирование данных Наблюдение состояния младенца]
//function EvnNeonatalSurvey_Save() - Сохранение в БД данных конкретного реанимационного наблюдения состояния - 
//function EvnNeonatalSurvey_Delete() -	удаление Наблюдения состояния младенца]
//function getSedationMedicat()- извлечение параметров медикаментозной седации

class EvnNeonatalSurvey extends swController {

	public $inputRules = array(
		'getParamsENSWindow' => array(
			array(
				'field' => 'EvnNeonatalSurvey_pid',
				'label' => 'Идентификатор родительского события',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnNeonatalSurvey_id',
				'label' => 'Идентификатор наблюдения состояния младенца',
				'rules' => '',
				'type' =>  'id'
			),
			array(
				'field' => 'action',
				'label' => 'Действие',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'LpuSection_id',
				'label' => 'Идентификатор отделения',
				'rules' => '',
				'type' =>  'id'
			),
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор ЛПУ',
				'rules' => '',
				'type' =>  'id'
			)
		),
		'getEvnNeonatalSurvey' => array(
			array(
				'field' => 'EvnNeonatalSurvey_id',
				'label' => 'Идентификатор Наблюдение состояния младенца',
				'rules' => 'required',
				'type' => 'id'
			),
		),

		
		'EvnNeonatalSurvey_Save' => array(
			array(
				'field' => 'EvnNeonatalSurvey_id',
				'label' => 'Идентификатор наблюдения состояния младенца',
				'rules' => '',
				'type' =>  'id'
			),
			array(
				'field' => 'EvnNeonatalSurvey_pid',
				'label' => 'Идентификатор родительского события - реанимационного периода',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnNeonatalSurvey_rid',
				'label' => 'Идентификатор прародительского события - КВС',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор ЛПУ',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PersonEvn_id',
				'label' => 'Идентификатор события пациента',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Server_id',
				'label' => 'Идентификатор сервера',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'EvnNeonatalSurvey_setDate',
				'label' => 'Дата события наблюдения состояния младенца',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'EvnNeonatalSurvey_setTime',
				'label' => 'Время события наблюдения состояния младенца',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'EvnNeonatalSurvey_disDate',
				'label' => 'Дата окончания события наблюдения состояния младенца',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'EvnNeonatalSurvey_disTime',
				'label' => 'Время окончания события наблюдения состояния младенца',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'ReanimStageType_id',
				'label' => 'Этап - дкумент реанимационного наблюдения состояния',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'ReanimConditionType_id',
				'label' => 'Состояние по реанимационному наблюдению',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ReanimArriveFromType_id',
				'label' => 'Поступил из',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnNeonatalSurvey_Conclusion',
				'label' => 'Заключение',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EvnNeonatalSurvey_Doctor',
				'label' => 'ФИО врача, подписывающего документ',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'NeonatalSurveyParam',
				'label' => 'Прочие параметры обследования младенца',
				'rules' => '',
				'type' => 'string'
			)
			,
			array(
				'field' => 'BreathAuscultative',
				'label' => 'Аускультативно',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'NeonatalTrauma',
				'label' => 'Травма',
				'rules' => '',
				'type' => 'string'
			)
		),
		'EvnNeonatalSurvey_Delete' => array(
			array(
				'field' => 'EvnNeonatalSurvey_id',
				'label' => 'Идентификатор Наблюдение состояния младенца',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'getSedationMedicat' => array(
			array(
				'field' => 'EvnNeonatalSurvey_pid',
				'label' => 'Идентификатор родительского события - реанимационного периода',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnNeonatalSurvey_setDate',
				'label' => 'Дата начала наблюдения младенца',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'EvnNeonatalSurvey_setTime',
				'label' => 'Время начала наблюдения младенца',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'EvnNeonatalSurvey_disDate',
				'label' => 'Дата окончания наблюдения младенца',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'EvnNeonatalSurvey_disTime',
				'label' => 'Время окончания наблюдения младенца',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'ReanimatActionType_SysNick',
				'label' => 'Тип реанимационного мероприятия',
				'rules' => '',
				'type' => 'string'
			)


			

		),
		'loadNeonatalSurveyGrid' => array(
			array(
				'field' => 'EvnPS_id',
				'label' => 'Идентификатор КВС',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор Пациента',
				'rules' => '',
				'type' => 'id'
			)
		)
	);
	
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		
		
		//$this->load->database('bdwork');
		$this->load->database();
		$this->load->model('EvnNeonatalSurvey_model', 'dbmodel');
	}

	 /**
     *  Формирование параметров для окна Наблюдение состояния младенца
	 * BOB - 24.01.2020
     */
    function getParamsENSWindow()
    {
		$data = $this->ProcessInputData('getParamsENSWindow', false);

		if ($data === false) return false;

		$response = $this->dbmodel->getParamsENSWindow($data);
        
        return $this->ReturnData($response);
    }
	 /**
     *  Формирование данных Наблюдение состояния младенца]
	 * BOB - 27.01.2020
     */
    function getEvnNeonatalSurvey()
    {
		$data = $this->ProcessInputData('getEvnNeonatalSurvey', false);

		if ($data === false) return false;

		$response = $this->dbmodel->getEvnNeonatalSurvey($data);
        
        return $this->ReturnData($response);
    }
	

	/**
	 * BOB - 29.01.2020
	 * Сохранение в БД данных конкретного реанимационного наблюдения состояния - 
	 */
	function EvnNeonatalSurvey_Save() {
		$data = $this->ProcessInputData('EvnNeonatalSurvey_Save', true);		
		if ($data === false) return false;		
		$response = $this->dbmodel->EvnNeonatalSurvey_Save($data);
		$this->ReturnData($response);		
		return true;		
	}

	 /**
     *  удаление Наблюдения состояния младенца]
	 * BOB - 20.02.2020
     */
    function EvnNeonatalSurvey_Delete()
    {
		$data = $this->ProcessInputData('EvnNeonatalSurvey_Delete', false);

		if ($data === false) return false;

		$response = $this->dbmodel->EvnNeonatalSurvey_Delete($data);
        
        return $this->ReturnData($response);
    }
	 /**
     * извлечение параметров медикаментозной седации
	 * BOB - 13.03.2020
     */
    function getSedationMedicat()
    {
		$data = $this->ProcessInputData('getSedationMedicat', false);

		if ($data === false) return false;

		$response = $this->dbmodel->getSedationMedicat($data);
        
        return $this->ReturnData($response);
    }

	/**
	 * Получение списка наблюдений
	 */
	function loadNeonatalSurveyGrid() {
		$data = $this->ProcessInputData('loadNeonatalSurveyGrid');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadNeonatalSurveyGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
}