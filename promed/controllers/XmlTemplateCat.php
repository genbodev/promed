<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * XmlTemplateCat - контроллер для работы с папками Xml-шаблонов
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2010 Swan Ltd.
 * @author       Пермяков Александр Михайлович
 * @version      31.10.2014
 *
 * @property XmlTemplateCat_model $dbmodel
 */
class XmlTemplateCat extends swController
{
	/**
	 * construct
	 */
	function __construct() {
		parent::__construct();
		$this->load->database();
		$this->load->model('XmlTemplateCat_model', 'dbmodel');
		$this->inputRules = array();
	}

	/**
	 * Функция чтения папки для формы редактирования
	 * На выходе: JSON-строка
	 * Используется: формa /jscore/Forms/Common/swXmlTemplateCatEditWindow.js
	 */
	function loadForm() {
		$this->inputRules['loadForm'] = $this->dbmodel->getInputRules(swModel::SCENARIO_LOAD_EDIT_FORM);
		$data = $this->ProcessInputData('loadForm', true);
		if ($data) {
			$response = $this->dbmodel->loadEditForm($data);
			$this->ProcessModelList($response, true, true)
				->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Создание папки по умолчанию
	 * На выходе: JSON-строка
	 * Используется: формa /jscore/Forms/Common/swXmlTemplateCatEditWindow.js
	 */
	function createDefault() {
		$this->inputRules['createDefault'] = $this->dbmodel->getInputRules('createDefault');
		$data = $this->ProcessInputData('createDefault', true);
		if ($data) {
			$response = $this->dbmodel->search($data);
			if (empty($response)) {
				$response = $this->dbmodel->createDefault($data);
			} else if (empty($response[0]['Error_Msg'])) {
				$response = $response[0];
				$response['Error_Msg'] = null;
			}
			$this->ProcessModelSave($response, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Функция записи папки
	 * На выходе: JSON-строка
	 * Используется: формa /jscore/Forms/Common/swXmlTemplateCatEditWindow.js
	 */
	function save()
	{
		$this->inputRules['save'] = $this->dbmodel->getInputRules(swModel::SCENARIO_DO_SAVE);
		$data = $this->ProcessInputData('save', true);
		if ($data) {
			$data['scenario'] = swModel::SCENARIO_DO_SAVE;
			$response = $this->dbmodel->doSave($data);
			$this->ProcessModelSave($response, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Удаление папки
	 * На выходе: JSON-строка
	 * Используется: формa /jscore/Forms/Common/swTemplSearchWindow.js
	 */
	public function destroy()
	{
		$this->inputRules['destroy'] = $this->dbmodel->getInputRules(swModel::SCENARIO_DELETE);
		$data = $this->ProcessInputData('destroy',true);
		if ($data === false) {return false;}
		$response = $this->dbmodel->doDelete($data);
		$this->ProcessModelSave($response,true)->ReturnData();
		return true;
	}

	/**
	 * Функция чтения списка папок для комбобокса
	 * На выходе: JSON-строка
	 * Используется:
	 * формa /jscore/Forms/Common/swTemplEditWindow.js
	 * формa /jscore/Forms/Common/swXmlTemplateCatEditWindow.js
	 */
	function loadCombo()
	{
		$this->inputRules['loadCombo'] = $this->dbmodel->getInputRules(swModel::SCENARIO_LOAD_COMBO_BOX);
		$data = $this->ProcessInputData('loadCombo', true);
		if ($data) {
			$response = $this->dbmodel->loadCombo($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
}
