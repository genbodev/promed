<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnUslugaOrder - контроллер для работы с заказами услуг параклиники
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2013 Swan Ltd.
 * @author       Alexander Permyakov
 * @version      08.2013
 *
 * @property EvnUslugaOrder_model dbmodel
 */

class EvnUslugaOrder extends swController {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
		
		$this->inputRules = array(
			'exec' => array(
				array('field' => 'EvnUslugaPar_id','label' => 'Заказ','rules' => 'required','type' => 'id'),
				array('field' => 'MedPersonal_uid','label' => 'Врач','rules' => 'required','type' => 'id'),
				array('field' => 'LpuSection_uid','label' => 'Отделение','rules' => 'required','type' => 'id'),
				array('field' => 'Lpu_uid','label' => 'МО','rules' => 'required','type' => 'id'),
			),
			'loadList' => array(
				array(
					'field' => 'begDate',
					'label' => 'Дата начала периода',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'endDate',
					'label' => 'Дата конца периода',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'Person_SurName',
					'label' => 'Фамилия',
					'rules' => 'trim|ban_percent',
					'type' => 'string'
				),
				array(
					'field' => 'Person_FirName',
					'label' => 'Имя',
					'rules' => 'trim|ban_percent',
					'type' => 'string'
				),
				array(
					'field' => 'Person_SecName',
					'label' => 'Отчество',
					'rules' => 'trim|ban_percent',
					'type' => 'string'
				),
				array(
					'field' => 'Person_BirthDay',
					'label' => 'Дата рождения',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'EvnDirection_Num',
					'label' => 'Номер направления',
					'rules' => 'trim|ban_percent',
					'type' => 'string'
				),
				array(
					'field' => 'UslugaComplex_id',
					'label' => 'Услуга',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedService_id',
					'label' => 'Служба',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'loadQueue',
					'label' => 'Очередь',
					'rules' => '',
					'type' => 'id'
				),
			),
		);
		$this->load->database();
		$this->load->model('EvnUslugaOrder_model', 'dbmodel');
	}
	
	/**
	 *  Установка статуса "Выполнено" для заказа услуги
	 *  Используется: форма АРМа консультационного приёма
	 */
	function exec() {
		$data = $this->ProcessInputData('exec', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->exec($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}

	/**
	 *  Получение списка заказов
	 *  Используется: форма АРМа консультационного приёма
	 */
	function loadList() {
		$data = $this->ProcessInputData('loadList', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->loadList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

}