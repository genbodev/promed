<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * MorfoHistologicCorpseReciept - контроллер для работы с поступлениями тел умерших (АРМ Патологоанатома)
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @access       public
 * @author       Shekunov Dmitriy
 *
 * @property MorfoHistologicCorpseReciept_model $dbmodel
 */

class MorfoHistologicCorpseReciept extends swController {
	public $inputRules = array(
		'saveMorfoHistologicCorpseReciept' => array(
			array(
				'field' => 'MedPersonal_id',
				'label' => 'Медицинский работник',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'MorfoHistologicCorpse_recieptDate',
				'label' => 'Дата поступления тела',
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
				'field' => 'MorfoHistologicCorpseReciept_id',
				'label' => 'Идентификатор записи о поступлении тела',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadMorfoHistologicCorpseRecieptEditForm' => array(
			array(
				'field' => 'MorfoHistologicCorpseReciept_id',
				'label' => 'Идентификатор записи о поступлении тела',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'deleteMorfoHistologicCorpseReciept' => array(
			array(
				'field' => 'MorfoHistologicCorpseReciept_id',
				'label' => 'Идентификатор записи о поступлении тела',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getMorfoHistologicCorpseRecieptDate' => array(
			array(
				'field' => 'EvnDirectionMorfoHistologic_id',
				'label' => 'Идентификатор направления',
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
		$this->load->model('MorfoHistologicCorpseReciept_model', 'dbmodel');
	}

	/**
	 *  Получение данных для формы редактирования сведений о поступлении тел умерших
	 *  Входящие данные: $_POST['MorfoHistologicCorpseReciept_id']
	 *  На выходе: JSON-строка
	 *  Используется: форма редактирования сведений о поступлении тел умерших 
	 */
	function loadMorfoHistologicCorpseRecieptEditForm() {
		$data = $this->ProcessInputData('loadMorfoHistologicCorpseRecieptEditForm', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadMorfoHistologicCorpseRecieptEditForm($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 *  Сохранение сведений о поступлении тела умершего по патоморфогистологическому направлению
	 *  Входящие данные: <поля формы>
	 *  На выходе: JSON-строка
	 *  Используется: форма редактирования сведений о поступлении тел умерших
	 */
	function saveMorfoHistologicCorpseReciept() {
		$data = $this->ProcessInputData('saveMorfoHistologicCorpseReciept', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->saveMorfoHistologicCorpseReciept($data);
		$this->ProcessModelSave($response, true, 'Error')->ReturnData();

		return true;
	}

	/**
	 *  Удаление сведений о поступлении тела умершего по патоморфогистологическому направлению
	 *  Входящие данные: $_POST['MorfoHistologicCorpseReciept_id']
	 *  На выходе: JSON-строка
	 *  Используется: журнал рабочего места патологоанатома
	 */
	function deleteMorfoHistologicCorpseReciept() {
		$data = $this->ProcessInputData('deleteMorfoHistologicCorpseReciept', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->deleteMorfoHistologicCorpseReciept($data);
		$this->ProcessModelSave($response, true, 'При удалении записи возникли ошибки')->ReturnData();

		return true;
	}

	/**
	 *  Получение даты поступления тела
	 *  Входящие данные: $_POST['EvnDirectionMorfoHistologic_id']
	 *  На выходе: JSON-строка
	 *  Используется: форма редактирования протокола патоморфогистологического исследования
	 */
	function getMorfoHistologicCorpseRecieptDate() {

		$data = $this->ProcessInputData('getMorfoHistologicCorpseRecieptDate', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->getMorfoHistologicCorpseRecieptDate($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}
}