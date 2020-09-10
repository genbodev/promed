<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * MorfoHistologicRefuse - контроллер для работы с отказами от вскрытия тел умерших (АРМ Патологоанатома)
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @access       public
 * @author       Shekunov Dmitriy
 *
 * @property MorfoHistologicRefuse_model $dbmodel
 */

class MorfoHistologicRefuse extends swController {
	public $inputRules = array(
		'saveMorfoHistologicRefuse' => array(
			array(
				'field' => 'RefuseType_id',
				'label' => 'Тип отказа от вскрытия',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'MorfoHistologic_refuseDate',
				'label' => 'Дата отказа от вскрытия',
				'rules' => 'trim|required',
				'type' => 'date'
			),
			array(
				'field' => 'EvnDirectionMorfoHistologic_id',
				'label' => 'Идентификатор направления',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'MorfoHistologicRefuse_id',
				'label' => 'Идентификатор записи об отказе от вскрытия',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadMorfoHistologicRefuseEditForm' => array(
			array(
				'field' => 'MorfoHistologicRefuse_id',
				'label' => 'Идентификатор записи об отказе от вскрытия',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'deleteMorfoHistologicRefuse' => array(
			array(
				'field' => 'MorfoHistologicRefuse_id',
				'label' => 'Идентификатор записи об отказе',
				'rules' => 'required',
				'type' => 'id'
			)
		)
	);


	/**
	 * comment
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('MorfoHistologicRefuse_model', 'dbmodel');
	}
	
	/**
	 *  Получение данных списка типов отказа от вскрытия
	 *  На выходе: JSON-строка
	 *  Используется: форма редактирования сведений об отказе от вскрытия тел умерших
	 */
	function getMorfoHistologicRefuseTypeList() {

		$response = $this->dbmodel->getMorfoHistologicRefuseTypeList();
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 *  Получение данных для формы редактирования сведений об отказе от вскрытия тел умерших
	 *  Входящие данные: $_POST['MorfoHistologicRefuse_id']
	 *  На выходе: JSON-строка
	 *  Используется: форма редактирования сведений об отказе от вскрытия тел умерших
	 */
	function loadMorfoHistologicRefuseEditForm() {
		$data = $this->ProcessInputData('loadMorfoHistologicRefuseEditForm', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadMorfoHistologicRefuseEditForm($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 *  Сохранение сведений об отказе от вскрытия тела умершего по патоморфогистологическому направлению
	 *  Входящие данные: <поля формы>
	 *  На выходе: JSON-строка
	 *  Используется: форма редактирования сведений об отказе от вскрытия тел умерших
	 */
	function saveMorfoHistologicRefuse() {
		$data = $this->ProcessInputData('saveMorfoHistologicRefuse', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->saveMorfoHistologicRefuse($data);
		$this->ProcessModelSave($response, true, 'Error')->ReturnData();

		return true;
	}

	/**
	 *  Удаление  сведений об отказе от вскрытия тела умершего по патоморфогистологическому направлению
	 *  Входящие данные: $_POST['MorfoHistologicRefuse_id']
	 *  На выходе: JSON-строка
	 *  Используется: журнал рабочего места патологоанатома
	 */
	function deleteMorfoHistologicRefuse() {
		$data = $this->ProcessInputData('deleteMorfoHistologicRefuse', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->deleteMorfoHistologicRefuse($data);
		$this->ProcessModelSave($response, true, 'При удалении записи возникли ошибки')->ReturnData();

		return true;
	}

}