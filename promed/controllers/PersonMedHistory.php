<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
* PersonMedHistory - This is a controller
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
* 
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @author       A. Permyakov
* @version      02 2012
*/

/**
 * @property PersonMedHistory_model $dbmodel 
*/
class PersonMedHistory extends swController {
	/**
	 * PersonMedHistory constructor.
	 */
	function __construct() {
		parent::__construct();
		$this->load->database();
		$this->load->model('PersonMedHistory_model', 'dbmodel');
		$this->inputRules = array(
			'savePersonMedHistory' => array(
				array('field' => 'PersonMedHistory_id','label' => 'PersonMedHistory_id','rules' => '','type' => 'id'),
				array('field' => 'Person_id','label' => 'Person_id','rules' => 'required','type' => 'id'),
				array('field' => 'PersonMedHistory_Descr','label' => 'PersonMedHistory_Descr','rules' => 'required','type' => 'string'),
				array('field' => 'PersonMedHistory_setDT','label' => 'PersonMedHistory_setDT','rules' => 'required','type' => 'date')
			),
			'deletePersonMedHistory' => array(
				array('field' => 'PersonMedHistory_id', 'label' => 'PersonMedHistory_id', 'rules' => 'required', 'type' => 'id')
			),
			'loadPersonMedHistoryPanel' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор пациента',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadPersonMedHistoryEditForm' => array(
				array('field' => 'PersonMedHistory_id','label' => 'PersonMedHistory_id','rules' => 'required','type' => 'id')
			),
			'getPersonMedHistoryViewData' => array(
				array('field' => 'Person_id','label' => 'Person_id','rules' => 'required','type' => 'id')
			)
		);
	}
	/**
	 * Получение данных для панели просмотра ЭМК
	 *
	 * @return bool
	 */
	function getPersonMedHistoryViewData() {
		$data = $this->ProcessInputData('getPersonMedHistoryViewData', true);
		if ($data) {
			$response = $this->dbmodel->getPersonMedHistoryViewData($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка формы редактирования
	 *
	 * @return bool
	 */
	function loadPersonMedHistoryEditForm() {
		$data = $this->ProcessInputData('loadPersonMedHistoryEditForm', true);
		if ($data) {
			$response = $this->dbmodel->loadPersonMedHistoryEditForm($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Сохранение
	 *
	 * @return bool
	*/
	function savePersonMedHistory() {
		$data = $this->ProcessInputData('savePersonMedHistory', true);
		if ($data) {
			$response = $this->dbmodel->savePersonMedHistory($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Удаление
	 *
	 * @return bool
	*/
	function deletePersonMedHistory() {
		$data = $this->ProcessInputData('deletePersonMedHistory', true);
		if ($data) {
			$response = $this->dbmodel->deletePersonMedHistory($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение списка анамнеза жизни пациента для ЭМК
	 */
	function loadPersonMedHistoryPanel() {
		$data = $this->ProcessInputData('loadPersonMedHistoryPanel', true, true, true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadPersonMedHistoryPanel($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return false;
	}
}
