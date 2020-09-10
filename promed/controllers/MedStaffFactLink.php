<?php   defined('BASEPATH') or die ('No direct script access allowed');
/**
 * MedStaffFactLink - контроллер для работы со связками мед. персонала
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Bykov Stanislav (savage@swan.perm.ru)
 * @version			08.10.2013
 */

class MedStaffFactLink extends swController {
	/**
	 * @var array
	 */
	protected  $inputRules = array(
		'deleteMedStaffFactLink' => array(
			array('field' => 'MedStaffFactLink_id', 'label' => 'Идентификатор связки мест работы', 'rules' => 'required', 'type' => 'id')
		),
		'loadMedStaffFactLinkEditForm' => array(
			array('field' => 'MedStaffFactLink_id', 'label' => 'Идентификатор связки мест работы', 'rules' => 'required', 'type' => 'id')
		),
		'loadMedStaffFactLinkGrid' => array(
			array('field' => 'MedStaffFact_id', 'label' => 'Идентификатор места работы врача', 'rules' => 'required', 'type' => 'id')
		),
		'saveMedStaffFactLink' => array(
			array('field' => 'MedStaffFactLink_id', 'label' => 'Идентификатор связки мест работы', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedStaffFact_id', 'label' => 'Идентификатор места работы врача', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'MedStaffFact_sid', 'label' => 'Идентификатор места работы среднего мед. персонала', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'MedStaffFactLink_begDT', 'label' => 'Дата начала действия', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'MedStaffFactLink_endDT', 'label' => 'Дата окончания действия', 'rules' => '', 'type' => 'date')
		)
	);

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model('MedStaffFactLink_model', 'dbmodel');
	}

	/**
	 * Удаление связки мест работы
	 * @return bool
	 */
	function deleteMedStaffFactLink()
	{
		$data = $this->ProcessInputData('deleteMedStaffFactLink',true);
		if ($data === false) {return false;}

		$response = $this->dbmodel->deleteMedStaffFactLink($data);

		$this->ProcessModelList($response,true,true)->ReturnData();
		return true;
	}

	/**
	 * Возвращает данные о связке мест работы для формы редактирования
	 * @return bool
	 */
	function loadMedStaffFactLinkEditForm()
	{
		$data = $this->ProcessInputData('loadMedStaffFactLinkEditForm',true);
		if ($data === false) {return false;}

		$response = $this->dbmodel->loadMedStaffFactLinkEditForm($data);

		$this->ProcessModelSave($response,true,true)->ReturnData();
		return true;
	}

	/**
	 * Возвращает список связанных мест работы
	 * @return bool
	 */
	function loadMedStaffFactLinkGrid()
	{
		$data = $this->ProcessInputData('loadMedStaffFactLinkGrid',true);
		if ($data === false) {return false;}

		$response = $this->dbmodel->loadMedStaffFactLinkGrid($data);

		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Сохранение связки мест работы врача и среднего мед. персонала
	 * @return bool
	 */
	function saveMedStaffFactLink()
	{
		$data = $this->ProcessInputData('saveMedStaffFactLink', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveMedStaffFactLink($data);

		$this->ProcessModelSave($response, true, 'При сохранении возникли ошибки')->ReturnData();
		return true;
	}
}