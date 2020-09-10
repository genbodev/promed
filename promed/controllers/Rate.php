<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Rate - контроллер для работы с показателями
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package		Common
* @access		public
* @copyright	Copyright (c) 2009-2010 Swan Ltd.
* @author		Salakhov Rustam
* @version		07.07.2010
*/
class Rate extends swController {
	/**
	 * Rate constructor.
	 */
	function __construct()
	{
		parent::__construct();
		$this->inputRules = array(
			'loadRateList' => array(),
			'loadRateValueList' => array(
				array('field' => 'ratetype_id','label' => 'Идентификатор типа показателя','rules' => 'trim|required','type' => 'id')
			),
			'loadRateListGrid' => array(
				array('field' => 'rate_subid','label' => 'Идентификатор измерения/услуги','rules' => 'trim|required','type' => 'id'),
				array('field' => 'rate_type','label' => 'Тип показателя','rules' => 'trim|required','type' => 'string')
			),
			'autoLoadRateValueList' => array(
				array('field' => 'ratetype_id','label' => 'Тип показателя','rules' => 'trim|required','type' => 'id')
			),
			'getPersonMeasure' => array(
				array('field' => 'person_id','label' => 'Идентификатор человека','rules' => 'trim|required','type' => 'id')
			),
			'savePersonMeasures' => array(
				array('field' => 'Person_id','label' => 'Идентификатор человека','rules' => 'trim|required','type' => 'id'),
				array('field' => 'data','label' => 'Данные по показателям','rules' => '','type' => 'string')
			),
			'loadPersonMeasureList' => array(
				array('default' => 100,'field' => 'limit','label' => 'Лимит записей','rules' => '','type' => 'int'),
				array('default' => 0,'field' => 'start','label' => 'Начальная запись','rules' => '','type' => 'int'),
				array('field' => 'person_id','label' => 'Идентификатор человека','rules' => 'trim|required','type' => 'id')
			),
			'deleteRate' => array(
				array('field' => 'ratetype_id','label' => 'Идентификатор типа показателя','rules' => 'trim|required','type' => 'id'),
				array('field' => 'rate_subid','label' => 'Идентификатор измерения/услуги','rules' => 'trim|required','type' => 'id'),
				array('field' => 'rate_type','label' => 'Тип показателя','rules' => 'trim|required','type' => 'string')
			)
		);
	}


	/**
	*  Получение списка показателей
	*  Входящие данные: нет
	*  На выходе: JSON-строка
	*/
	function loadRateList() {
		$this->load->database();
		$this->load->model('Rate_model', 'dbmodel');

		$data = $this->ProcessInputData('loadRateList', true);
		
		if ($data) {

			$response = $this->dbmodel->loadRateList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			
			return true;
		} else {
			return false;
		}
	}
	
	
	/**
	*  Получение списка значений показателя
	*  Входящие данные: идентификатор вида показателя
	*  На выходе: JSON-строка
	*/
	function loadRateValueList() {
		$this->load->database();
		$this->load->model('Rate_model', 'dbmodel');

		$data = $this->ProcessInputData('loadRateValueList', true);
		
		if ($data) {

			$response = $this->dbmodel->loadRateValueList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @return bool
	 */
	function autoLoadRateValueList() {
		$this->load->database();
		$this->load->model('Rate_model', 'dbmodel');

		$data = $this->ProcessInputData('autoLoadRateValueList', true);
		
		if ($data) {

			$response = $this->dbmodel->loadRateValueList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			
			return true;
		} else {
			return false;
		}
	}
	
	/**
	*  Получение списка показателе
	*  Входящие данные: нет
	*  На выходе: JSON-строка
	*/
	function loadRateListGrid() {
		$this->load->database();
		$this->load->model('Rate_model', 'dbmodel');
		
		$data = $this->ProcessInputData('loadRateListGrid', true);
		
		if ($data) {

			$response = $this->dbmodel->loadRateListGrid($data);
			$this->ProcessModelMultiList($response, true, true)->ReturnData();
			
			return true;
		} else {
			return false;
		}
	}
	
	/**
	*  Получение данных по факту измерения
	*  Входящие данные: person_id (идентификатор человека)
	*  На выходе: JSON-строка
	*/
	function getPersonMeasure() {
		$this->load->database();
		$this->load->model('Rate_model', 'dbmodel');

		$data = $this->ProcessInputData('getPersonMeasure', true);
		
		if ($data) {

			$response = $this->dbmodel->getPersonMeasure($data);
			$this->ProcessModelList($response, true, true);
			$this->ReturnData($this->GetOutData(0));
			
			return true;
		} else {
			return false;
		}
	}
	
	/**
	*  Сохранение данных по факту измерения
	*  Входящие данные: person_id (идентификатор человека), данные по измерению, данные по показателям
	*  На выходе: ---
	*/
	function savePersonMeasures() {
		$this->load->database();
		$this->load->model('Rate_model', 'dbmodel');

		$data = $this->ProcessInputData('savePersonMeasures', true);
		
		if ($data) {

			$response = $this->dbmodel->savePersonMeasures($data);
			$this->ProcessModelSave($response, true)->ReturnData();
			
			return true;
		} else {
			return false;
		}
	}
	
	/**
	*  Получение списка измерений
	*  Входящие данные: Person_id (идентификатор человека)
	*  На выходе: JSON-строка
	*/
	function loadPersonMeasureList() {
		$this->load->database();
		$this->load->model('Rate_model', 'dbmodel');

		$data = $this->ProcessInputData('loadPersonMeasureList', true);
		
		if ($data) {

			$response = $this->dbmodel->loadPersonMeasureList($data);
			$this->ProcessModelMultiList($response, true, true)->ReturnData();
			
			return true;
		} else {
			return false;
		}
	}

	
	/**
	*  Удаление показателя
	*  Входящие данные: тип показателя, идентивикатор вида показателя, идентиыикатор измерения/услуги
	*  На выходе: ---
	*/
	function deleteRate() {
		$this->load->database();
		$this->load->model('Rate_model', 'dbmodel');
		
		$data = $this->ProcessInputData('deleteRate', true);
		
		if ($data) {

			$response = $this->dbmodel->deleteRate($data);
			$this->ProcessModelSave($response, true)->ReturnData();
			
			return true;
		} else {
			return false;
		}
	}
}

?>