<?php defined('BASEPATH') or die('No direct script access allowed');
/**
 * GeriatricsQuestion - гериатрия
 * сделано в режиме совместимости с OnkoCtrl
 *
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 *  
 * @property GeriatricsQuestion_model $dbmodel
 */
class GeriatricsQuestion extends swController {
	/**
	 * Описание правил для входящих параметров
	 * @var array
	 */
	public $inputRules = array();
	public $model_name = "GeriatricsQuestion_model";

	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model($this->model_name, "dbmodel");

		$this->inputRules = array(
			'GetOnkoCtrlProfileJurnal' => array(
				array('field' => 'Filter', 'label' => 'Json строка для фильтра', 'rules' => '', 'type' => 'string'),
				array('field' => 'Lpu_id', 'label' => 'Идентификатор ЛПУ', 'rules' => '', 'type' => 'int'),
				array('default' => 0, 'field' => 'start', 'label' => 'Начальный номер записи', 'rules' => 'trim', 'type' => 'int'),
				array('default' => 100, 'field' => 'limit', 'label' => 'Количество возвращаемых записей', 'rules' => 'trim', 'type' => 'int'),
				array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => '', 'type' => 'id' ),
				array('field' => 'FirName', 'label' => 'Имя', 'rules' => '', 'type' => 'string'),
				array('field' => 'SurName', 'label' => 'Фамилия', 'rules' => '', 'type' => 'string'),
				array('field' => 'SecName', 'label' => 'Отчество', 'rules' => '', 'type' => 'string'),
				array('field' => 'BirthDayRange', 'label' => 'Период Дата рождения', 'rules' => 'trim', 'type' => 'daterange'), 
				array('field' => 'BirthDay', 'label' => 'Дата рождения', 'rules' => '', 'type' => 'date'), 
				array('field' => 'PeriodRange', 'label' => 'Период анкетирования', 'rules' => 'trim', 'type' => 'daterange'),
				array('field' => 'Doctor', 'label' => 'Врач', 'rules' => '', 'type' => 'int'),
				array('field' => 'StatusOnkoProfile_id', 'label' => 'Статус', 'rules' => '', 'type' => 'int'), 
				array('field' => 'OnkoQuestions_id', 'label' => 'Результат', 'rules' => '', 'type' => 'int'), 
				array('field' => 'Monitored', 'label' => 'Онкоконтроль', 'rules' => '', 'type' => 'int'), 
				array('field' => 'Uch', 'label' => 'Участок', 'rules' => '', 'type' => 'string'),
				array('field' => 'Sex_id', 'label' => 'Пол', 'rules' => '', 'type' => 'int'),
				array('field' => 'OnkoType_id', 'label' => 'Тип данных', 'rules' => '', 'type' => 'int'), 
				array('field' => 'AgeNotHindrance_id', 'label' => 'Статус', 'rules' => '', 'type' => 'id'), 
				array('field' => 'Empty', 'label' => 'Empty', 'rules' => '', 'type' => 'int'),
			),
			'deleteOnkoProfile' => array(
				array('field' => 'PersonOnkoProfile_id', 'label' => 'id анкеты', 'rules' => 'required|trim', 'type' => 'id')
			),
			'loadOnkoContrProfileFormInfo' => array(
				array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'PersonOnkoProfile_id', 'label' => 'Идентификатор анкеты', 'rules' => '','type' => 'id')
			),
			'getOnkoQuestions' => array(
				array('field' => 'PersonOnkoProfile_id', 'label' => 'Идентификатор анкеты', 'rules' => 'trim', 'type' => 'id')
			),
			 'savePersonOnkoProfile' => array(
				array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => 'trim', 'type' => 'id'),
				array('field' => 'PersonOnkoProfile_id', 'label' => 'Идентификатор анкеты', 'rules' => '', 'type' => 'id'),
				array('field' => 'Profile_Date', 'label' => 'Дата анкетирования', 'rules' => 'trim', 'type' => 'date'),
				array('field' => 'MorbusGeriatrics_id', 'label' => 'Идентификатор специфики заболевания', 'rules' => '', 'type' => 'id'), 
				array('field' => 'MedStaffFact_id', 'label' => 'Идентификатор врача', 'rules' => '', 'type' => 'id'), 
				array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => '', 'type' => 'id'),
				array('field' => 'QuestionAnswer', 'label' => 'Строка идентификаторов', 'rules' => '', 'type' => 'json_array'),
			),
		);
	}
   
	/**
	 * Журнал анкет
	 */
	public function GetOnkoCtrlProfileJurnal() {
		$data = $this->ProcessInputData('GetOnkoCtrlProfileJurnal', true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->GetOnkoCtrlProfileJurnal($data);
		if(is_array($response)) {
			$this->ProcessModelMultiList($response, true, true)->ReturnData();
		} else {
			return false;
		}
	}
	
	/**
	 * Удаление анкеты
	 */
	public function deleteOnkoProfile() {
		$data = $this->ProcessInputData('deleteOnkoProfile', true);
		if ($data === false) {
			return false;
		}

		$val = array();
		$response = $this->dbmodel->deleteOnkoProfile($data);

		foreach ($response as $row) {
			array_walk($row, 'ConvertFromWin1251ToUTF8');
			$val[] = $row;
		}

		echo json_encode(array('success' => true, 'rows' => $val));
		return true;
	}
	
	/**
	 * Загрузка доп инфы для формы анкетирования
	 */
	public function loadOnkoContrProfileFormInfo() {
		log_message('debug', 'loadOnkoContrProfileFormInfo');
		$data = $this->ProcessInputData('loadOnkoContrProfileFormInfo', true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->loadOnkoContrProfileFormInfo($data);
		array_walk_recursive($response, 'ConvertFromWin1251ToUTF8');
		$this->ReturnData(array('data' => $response));
	}
	
	/**
	 * Получение списка вопросов для анкеты
	 */
	public function getOnkoQuestions() {
		$data = $this->ProcessInputData('getOnkoQuestions', true); 
		$response = $this->dbmodel->getOnkoQuestions($data);
		if(is_array($response)) {
			$this->ProcessModelList($response, true, true)->ReturnData();
		} else {
			return false;
		}                        
	}

	/**
	 * сохранение информации об анкетировании пациента
	 */
	public function savePersonOnkoProfile() {
		$data = $this->ProcessInputData('savePersonOnkoProfile', true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->savePersonOnkoProfile($data);

		array_walk_recursive($response, 'ConvertFromWin1251ToUTF8');
		echo json_encode(array('success' => true, 'rows' => $response));
		return true;
	}
}
