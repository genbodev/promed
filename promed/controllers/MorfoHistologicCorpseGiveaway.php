<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * MorfoHistologicCorpseGiveaway - контроллер для работы с выдачей тел умерших (АРМ Патологоанатома)
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @access       public
 * @author       Shekunov Dmitriy
 *
 * @property MorfoHistologicCorpseGiveaway_model $dbmodel
 */

class MorfoHistologicCorpseGiveaway extends swController {
	public $inputRules = array(
		'saveMorfoHistologicCorpseGiveaway' => array(
			array(
				'field' => 'MedPersonal_id',
				'label' => 'Медицинский работник',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'MorfoHistologicCorpse_giveawayDate',
				'label' => 'Дата выдачи тела',
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
				'field' => 'MorfoHistologicCorpseGiveaway_id',
				'label' => 'Идентификатор записи о выдаче тела',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadMorfoHistologicCorpseGiveawayEditForm' => array(
			array(
				'field' => 'MorfoHistologicCorpseGiveaway_id',
				'label' => 'Идентификатор записи о выдаче тела',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'deleteMorfoHistologicCorpseGiveaway' => array(
			array(
				'field' => 'MorfoHistologicCorpseGiveaway_id',
				'label' => 'Идентификатор записи о выдаче тела',
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
		$this->load->model('MorfoHistologicCorpseGiveaway_model', 'dbmodel');
	}

	/**
	 *  Получение данных для формы редактирования сведений о выдаче тел умерших
	 *  Входящие данные: $_POST['MorfoHistologicCorpseGiveaway_id']
	 *  На выходе: JSON-строка
	 *  Используется: форма редактирования сведений о выдаче тел умерших 
	 */
	function loadMorfoHistologicCorpseGiveawayEditForm() {
		$data = $this->ProcessInputData('loadMorfoHistologicCorpseGiveawayEditForm', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadMorfoHistologicCorpseGiveawayEditForm($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 *  Сохранение сведений о выдаче тела умершего по патоморфогистологическому направлению
	 *  Входящие данные: <поля формы>
	 *  На выходе: JSON-строка
	 *  Используется: форма редактирования сведений о выдаче тел умерших
	 */
	function saveMorfoHistologicCorpseGiveaway() {
		$data = $this->ProcessInputData('saveMorfoHistologicCorpseGiveaway', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->saveMorfoHistologicCorpseGiveaway($data);
		$this->ProcessModelSave($response, true, 'Error')->ReturnData();

		return true;
	}

	/**
	 *  Удаление сведений о поступлении тела умершего по патоморфогистологическому направлению
	 *  Входящие данные: $_POST['MorfoHistologicCorpseReciept_id']
	 *  На выходе: JSON-строка
	 *  Используется: журнал рабочего места патологоанатома
	 */
	function deleteMorfoHistologicCorpseGiveaway() {
		$data = $this->ProcessInputData('deleteMorfoHistologicCorpseGiveaway', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->deleteMorfoHistologicCorpseGiveaway($data);
		$this->ProcessModelSave($response, true, 'При удалении записи возникли ошибки')->ReturnData();

		return true;
	}

}