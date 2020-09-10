<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * @property-read EvnSectionNarrowBed_model $dbmodel
 */
class EvnSectionNarrowBed extends swController
{
	public $inputRules = array(
		'deleteEvnSectionNarrowBed' => array(
			array(
				'field' => 'EvnSectionNarrowBed_id',
				'label' => 'Идентификатор узких коек',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadEvnSectionNarrowBedGrid' => array(
			array(
				'field' => 'EvnSectionNarrowBed_pid',
				'label' => 'Идентификатор родительского события',
				'rules' => 'required',
				'type' => 'id'
			)
		),
	);


	/**
	 * construct
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model('EvnSectionNarrowBed_model', 'dbmodel');
	}


	/**
	*  Удаление узких коек
	*  Входящие данные: $_POST['EvnSectionNarrowBed_id']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования карты выбывшего из стационара
	*/
	function deleteEvnSectionNarrowBed() {
		$data = $this->ProcessInputData('deleteEvnSectionNarrowBed', true);
		if ($data === false) return false;
		$response = $this->dbmodel->doDelete($data);
		$this->ProcessModelSave($response, true, 'При удалении узких коек возникли ошибки')->ReturnData();
		return true;
	}


	/**
	*  Получение списка случаев движения пациента в стационаре
	*  Входящие данные: $_POST['EvnSectionNarrowBed_pid']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования карты выбывшего из стационара
	*/
	function loadEvnSectionNarrowBedGrid() {
		$data = $this->ProcessInputData('loadEvnSectionNarrowBedGrid', true);
		if ($data === false) return false;
		$response = $this->dbmodel->loadEvnSectionNarrowBedGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return false;
	}


	/**
	 *  Сохранение узких коек
	 *  Входящие данные: ...
	 *  На выходе: JSON-строка
	 *  Используется: форма редактирования узких коек
	 */
	function saveEvnSectionNarrowBed()
	{
		$this->inputRules['saveEvnSectionNarrowBed'] = $this->dbmodel->getInputRules(swModel::SCENARIO_DO_SAVE);
		$data = $this->ProcessInputData('saveEvnSectionNarrowBed', true);
		if ($data === false) return false;
		$data['scenario'] = swModel::SCENARIO_DO_SAVE;
		$response = $this->dbmodel->doSave($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении узких коек')->ReturnData();
		return true;
	}
}
