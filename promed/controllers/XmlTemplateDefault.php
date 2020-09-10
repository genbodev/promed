<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * XmlTemplateDefault - контроллер для работы с Xml-шаблонами по умолчанию
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *-
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2010 Swan Ltd.
 * @author       Пермяков Александр Михайлович
 * @version      31.10.2014
 *
 * @property XmlTemplateDefault_model dbmodel
 */

class XmlTemplateDefault extends swController
{
	/**
	 * construct
	 */
	function __construct() {
		parent::__construct();
		$this->load->database();
		$this->load->model('XmlTemplateDefault_model', 'dbmodel');
		$this->inputRules = array();
	}

	/**
	 * Сохранение идентификатора шаблона по умолчанию
	 * На выходе: JSON-строка
	 * Используется: формa /jscore/Forms/Common/swTemplSearchWindow.js
	 */
	function save()
	{
		$this->inputRules['save'] = $this->dbmodel->getInputRules(swModel::SCENARIO_DO_SAVE);
		$data = $this->ProcessInputData('save', true);
		if (!$data) { return false; }
		$response = $this->dbmodel->save($data);
		$this->ProcessModelSave($response, true,'При записи идентификатора шаблона по умолчанию произошла ошибка.')->ReturnData();
		return true;
	}

	/**
	 * Получение идентификатора шаблона по умолчанию
	 */
	function getXmlTemplateId()
	{
		$this->inputRules['getXmlTemplateId'] = $this->dbmodel->getInputRules('search');
		$data = $this->ProcessInputData('getXmlTemplateId', true);
		if (!$data) { return false; }
		$response = $this->dbmodel->getXmlTemplateId($data);
		$this->ProcessModelList($response, true, true,'При получении идентификатора шаблона по умолчанию возникла ошибка.')
			->ReturnData();
		return true;
	}

	/**
	 * Получение идентификатора шаблона по умолчанию для услуги
	 */
	function getXmlTemplateIdByUsluga()
	{
		$this->inputRules['getXmlTemplateIdByUsluga'] = $this->dbmodel->getInputRules('searchByUsluga');
		$data = $this->ProcessInputData('getXmlTemplateIdByUsluga', true);
		if (!$data) { return false; }
		$response = $this->dbmodel->getXmlTemplateIdByUsluga($data);
		$this->ProcessModelList($response, true, true,'При получении идентификатора шаблона по умолчанию возникла ошибка.')
			->ReturnData();
		return true;
	}
}
