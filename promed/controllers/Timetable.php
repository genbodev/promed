<?php

defined('BASEPATH') or die('No direct script access allowed');

/**
* Timetable - общие методы для работы с расписанием
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * 
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Petukhov Ivan aka Lich (megatherion@list.ru)
 * @version      30.11.2009
 *
 * @property Timetable_model $dbmodel
 * @property EvnUsluga_model $ordermodel
 */
class Timetable extends swController {


	/**
	* Конструктор
	*/
	function __construct() {
		parent::__construct();
		
		$this->inputRules = array(
			'lock' => array(
				array(
					'field' => 'TimetableGraf_id',
					'label' => 'Идентификатор бирки поликлиники',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'TimetableStac_id',
					'label' => 'Идентификатор бирки стационара',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'TimetableMedService_id',
					'label' => 'Идентификатор бирки службы',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'TimetableResource_id',
					'label' => 'Идентификатор бирки ресурса',
					'rules' => '',
					'type' => 'id'
				),
			),
			'unlock' => array(
				array(
					'field' => 'TimetableGraf_id',
					'label' => 'Идентификатор бирки',
					'rules' => '',
					'type' => 'id'
				),
                array(
					'field' => 'TimetableStac_id',
					'label' => 'Идентификатор бирки стационара',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'TimetableMedService_id',
					'label' => 'Идентификатор бирки службы',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'TimetableResource_id',
					'label' => 'Идентификатор бирки ресурса',
					'rules' => '',
					'type' => 'id'
				),
			),
			'checkQueueExists' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор пациента',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'LpuUnit_did',
					'label' => 'Идентификатор группы отделений',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSectionProfile_id',
					'label' => 'Идентификатор профиля отделения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnDirection_id',
					'label' => 'Идентификатор направления',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedService_id',
					'label' => 'Идентификатор службы',
					'rules' => '',
					'type' => 'id'
				),
			),
		);
	}

    /**
	 * Создает временную блокировку бирки
	 */
	function lock() {
        $this->load->database();
        $this->load->model('Timetable_model', 'dbmodel');
		$data = $this->ProcessInputData('lock', true, true);
		if ($data) {
			$response = $this->dbmodel->lock($data);
			$this->ProcessModelSave($response, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Снимает временную блокировку с бирки
	 */
	function unlock() {
        $this->load->database();
        $this->load->model('Timetable_model', 'dbmodel');
		$data = $this->ProcessInputData('unlock', true, true);
		if ($data) {
			$response = $this->dbmodel->unlock($data);
			$this->ProcessModelSave($response, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Проверка существования записи в очередь перед созданием записи на бирку
	 */
	function checkQueueExists() {
		$this->load->database();
		$this->load->model('Timetable_model', 'dbmodel');
		$data = $this->ProcessInputData('checkQueueExists', true, true);
		if ($data) {
			$response = $this->dbmodel->checkQueueExists($data);
			$this->ProcessModelSave($response, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
}

?>