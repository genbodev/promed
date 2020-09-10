<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * XmlTemplateCatDefault - контроллер для работы с папками Xml-шаблонов по умолчанию
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2010 Swan Ltd.
 * @author       Пермяков Александр Михайлович
 * @version      05.2013
 *
 * @property XmlTemplateCatDefault_model $dbmodel
 */
class XmlTemplateCatDefault extends swController
{

	/**
	 * construct
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model('XmlTemplateCatDefault_model', 'dbmodel');
		$this->inputRules = array();
	}

	/**
	 * Сохранение идентификатора папки по умолчанию
	 * На выходе: JSON-строка
	 * Используется: формa /jscore/Forms/Common/swTemplSearchWindow.js
	 */
	function save()
	{
		$this->inputRules['save'] = $this->dbmodel->getInputRules(swModel::SCENARIO_DO_SAVE);
		$data = $this->ProcessInputData('save', true);
		if (!$data) { return false; }
		$response = $this->dbmodel->save($data);
		$this->ProcessModelSave($response, true,'При записи идентификатора папки по умолчанию произошла ошибка.')->ReturnData();
		return true;
	}

	/**
	 * Получение идентификатора папки по умолчанию
	 * или пути к ближайшей папке, доступной для редактирования
	 * На выходе: JSON-строка
	 * Используется: формa /jscore/Forms/Common/swTemplSearchWindow.js
	 */
	function getPath()
	{
		$this->inputRules['getPath'] = $this->dbmodel->getInputRules('search');
		$data = $this->ProcessInputData('getPath', true);
		if (!$data) { return false; }
		$response = $this->dbmodel->getPath($data);
		$this->ProcessModelList($response, true, true,'При получении идентификатора папки по умолчанию возникла ошибка.')->ReturnData();
		return true;
	}

}
