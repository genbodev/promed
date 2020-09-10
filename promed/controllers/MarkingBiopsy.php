<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * MarkingBiopsy - контроллер для работы с маркировкой материала
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      EvnDirectionHistologic
 * @access       public
 * @copyright    Copyright (c) 2009-2018 Swan Ltd.
 * @author       SWAN developers
 * @version      12.09.2018
 */
 
class MarkingBiopsy extends swController {
	public $inputRules = array(
		'deleteMarkingBiopsy' => array(
			array('field' => 'MarkingBiopsy_id', 'label' => 'Идентификатор маркировки материала', 'rules' => 'required', 'type' => 'id'),
		),
		'loadMarkingBiopsyEditForm' => array(
			array('field' => 'MarkingBiopsy_id', 'label' => 'Идентификатор маркировки материала', 'rules' => 'required', 'type' => 'id'),
		),
		'loadMarkingBiopsyGrid' => array(
			array('field' => 'EvnDirectionHistologic_id', 'label' => 'Идентификатор направления на патологогистологическое исследование', 'rules' => 'required', 'type' => 'id'),
		),
		'saveMarkingBiopsy' => array(
			array('field' => 'MarkingBiopsy_id', 'label' => 'Идентификатор маркировки материала', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnDirectionHistologic_id', 'label' => 'Идентификатор направления на патологогистологическое исследование', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'MarkingBiopsy_NumBot', 'label' => 'Номер флакона', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'MarkingBiopsy_LocalProcess','label' => 'Локализация патологического процесса (орган, топография)', 'rules' => 'trim|required', 'type' => 'string'),
			array('field' => 'MarkingBiopsy_NatureProcess','label' => 'Характер патологического процесса (эрозия, язва, полип, пятно, узел, внешне неизмененная ткань, отношение к окружающим тканям)', 'rules' => 'trim|required', 'type' => 'string'),
			array('field' => 'MarkingBiopsy_ObjKolvo', 'label' => 'Количество объектов', 'rules' => 'required', 'type' => 'id'),
		),
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('MarkingBiopsy_model', 'dbmodel');
	}

	/**
	 * Удаление маркировки материала
	 */
	public function deleteMarkingBiopsy() {
		$data = $this->ProcessInputData('deleteMarkingBiopsy', true);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->deleteMarkingBiopsy($data);
		$this->ProcessModelSave($response, true, 'При удалении маркировки материала возникли ошибки')->ReturnData();

		return true;
	}

	/**
	 * Получение данных для формы редактирования маркировки материала
	 */
	public function loadMarkingBiopsyEditForm() {
		$data = $this->ProcessInputData('loadMarkingBiopsyEditForm', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadMarkingBiopsyEditForm($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Получение списка записей для раздела "Маркировка материала"
	 */
	public function loadMarkingBiopsyGrid() {
		$data = $this->ProcessInputData('loadMarkingBiopsyGrid', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadMarkingBiopsyGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		
		return true;
	}

	/**
	 * Сохранение маркировки материала
	 */
	public function saveMarkingBiopsy() {
		$data = $this->ProcessInputData('saveMarkingBiopsy', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->saveMarkingBiopsy($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении маркировки материала')->ReturnData();
		
		return true;
	}
}