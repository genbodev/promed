<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* TreatmentCat - контроллер для получения данных, сохранения, удаления записей
* редактируемых справочников обращений
* TreatmentCat, TreatmentMethodDispatch, TreatmentRecipientType
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Promed
* @access       public
* @copyright    Copyright (c) 2010 Swan Ltd.
* @author       Permyakov Alexander <permjakov-am@mail.ru>
* @version      02.07.2010
*/
class TreatmentCat extends swController {

	public $inputRules = array();

	/**
	 * TreatmentCat constructor.
	 */
	function __construct()  {
		parent::__construct();
		/*
		if ( !isMinZdrav() && !isSuperadmin() && !havingGroup('OuzSpec') )
		{
			$this->ReturnError('У вас нет доступа к данному функционалу!', 600);
			return false;
		}
		*/
		$this->load->database();
		$this->load->model("TreatmentCat_model", "dbmodel");

		$this->inputRules = array(
			'saveItem' => array(
				array(
					'field' => 'TreatmentCat_id',
					'label' => 'Идентификатор записи справочника',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'default' => 0,
					'field' => 'TreatmentCat_Code',
					'label' => 'Код',
					'rules' => 'trim|required',
					'type' => 'int'
				),
				array(
					'field' => 'TreatmentCat_Name',
					'label' => 'Наименование',
					'length' => 50,
					'rules' => 'trim|required',
					'type' => 'string'
				),
				array(
					'default' => 1,
					'field' => 'TreatmentCat_IsDeletes',
					'label' => 'Удаление запрещено/разрешено',
					'rules' => 'trim|required',
					'type' => 'id'
				),
				array(
					'field' => 'Object',
					'label' => 'Идентификатор справочника (параметр comboSubject комбобокса)',
					'length' => 50,
					'rules' => 'trim|required',
					'type' => 'string'
				)
			),
			'getItem' => array (
				array(
					'field' => 'id',
					'label' => 'Идентификатор записи справочника',
					'rules' => 'trim|required',
					'type' => 'id'
				),
				array(
					'field' => 'Object',
					'label' => 'Идентификатор справочника (параметр comboSubject комбобокса)',
					'length' => 50,
					'rules' => 'trim|required',
					'type' => 'string'
				)
			),
			'getList' => array (
				array('field' => 'TreatmentCat_id','label' => 'Идентификатор значения справочника Категория обращения','rules' => 'trim','type' => 'id'),
				array('field' => 'TreatmentCat_Code','label' => 'Код значения справочника Категория обращения','rules' => 'trim','type' => 'int'),
				array('field' => 'TreatmentCat_Name','label' => 'Наименование значения справочника Категория обращения','rules' => 'trim','type' => 'string'),
				array('field' => 'TreatmentRecipientType_id','label' => 'Идентификатор значения справочника Адресат обращения','rules' => 'trim','type' => 'id'),
				array('field' => 'TreatmentRecipientType_Code','label' => 'Код значения справочника Адресат обращения','rules' => 'trim','type' => 'int'),
				array('field' => 'TreatmentRecipientType_Name','label' => 'Наименование значения справочника Адресат обращения','rules' => 'trim','type' => 'string'),
				array('field' => 'TreatmentMethodDispatch_id','label' => 'Идентификатор значения справочника Способ получения обращения','rules' => 'trim','type' => 'id'),
				array('field' => 'TreatmentMethodDispatch_Code','label' => 'Код значения справочника Способ получения обращения','rules' => 'trim','type' => 'int'),
				array('field' => 'TreatmentMethodDispatch_Name','label' => 'Наименование значения справочника Способ получения обращения','rules' => 'trim','type' => 'string'),
				array(
					'field' => 'Object',
					'label' => 'Идентификатор справочника (параметр comboSubject комбобокса)',
					'length' => 50,
					'rules' => 'trim|required',
					'type' => 'string'
				)
			),
			'delItem' => array (
				array(
					'field' => 'id',
					'label' => 'Идентификатор записи справочника',
					'rules' => 'trim|required',
					'type' => 'id'
				),
				array(
					'field' => 'Object',
					'label' => 'Идентификатор справочника (параметр comboSubject комбобокса)',
					'length' => 50,
					'rules' => 'trim|required',
					'type' => 'string'
				)
			),
			'getMaxItemCode' => array (
				array(
					'field' => 'Object',
					'label' => 'Идентификатор справочника (параметр comboSubject комбобокса)',
					'length' => 50,
					'rules' => 'trim|required',
					'type' => 'string'
				)
			)
		);
	}

	/**
	 * @return bool
	 */
	function Index() {
		return false;
	}

	/**
	 * Получение списка для комбобокса
	 * 
	 */
	function getList() {
		$data = $this->ProcessInputData('getList', true);
		if ($data) {
			$response = $this->dbmodel->getList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение максимального кода существующей записи
	 * для автозаполнения поля кода нового элемента справочника
	 */
	function getMaxItemCode() {
		$data = $this->ProcessInputData('getMaxItemCode', true);
		if ($data) {
			$val  = array();
			$item_data = $this->dbmodel->getMaxItemCode($data);
			if ( is_array($item_data) && count($item_data) > 0 ) {
				array_walk($item_data[0], 'ConvertFromWin1251ToUTF8');
				$val = $item_data[0];
			}
			$this->ReturnData($val);
			return true;
		} else {
			return false;
		}
	}

	//
	/**
	 * Сохранение записи
	 */
	function saveItem() {
		
		$data = array();
		$val  = array();
		$err = getInputParams($data, $this->inputRules['saveItem']);
		if ( strlen($err) > 0 )
		{
			echo json_return_errors($err);
			return false;
		}
		$data = array_merge($data, getSessionParams());
		$response = $this->dbmodel->saveItem($data);
		if ( is_array($response) && count($response) > 0 ) {
			array_walk($response[0], 'ConvertFromWin1251ToUTF8');
			$val = $response[0];
			if ( strlen($val['Error_Msg']) == 0 )
			{
				$val['success'] = true;
			}
			else
			{
				$val['success'] = false;
			}
		}
		$this->ReturnData($val);
		return true;
	}

	/**
	*  Получение данных записи
	*/
	function getItem() {
		
		$data = array();
		$val  = array();
		$err = getInputParams($data, $this->inputRules['getItem']);
		if ( strlen($err) > 0 ) {
			echo json_return_errors($err);
			return false;
		}
		$response = $this->dbmodel->getItem($data);
		if ( is_array($response) && count($response) > 0 ) {
			array_walk($response[0], 'ConvertFromWin1251ToUTF8');
			$val[] = $response[0];
		}
		$this->ReturnData($val);
		return true;
	}

	/**
	*  Удаление записи
	*/
	function delItem() {
		
		$data = array();
		$val  = array();
		$err = getInputParams($data, $this->inputRules['delItem']);
		if ( strlen($err) > 0 ) {
			echo json_return_errors($err);
			return false;
		}
		$data = array_merge($data, getSessionParams());
		// Проверка что запись нигде не используется
		$response = $this->dbmodel->checkUseItem($data);
		if ( is_array($response) ) {
			array_walk($response, 'ConvertFromWin1251ToUTF8');
			$val = $response;
			$this->ReturnData($val);
			return true;
		}

		$response = $this->dbmodel->delItem($data);
		if ( is_array($response) && count($response) > 0 ) {
			array_walk($response[0], 'ConvertFromWin1251ToUTF8');
			$val[] = $response[0];
		}
		$this->ReturnData($val);
		return true;
	}
}
