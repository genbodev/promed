<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
* PersonDispReg - контроллер для управления регистром заболеваний
* Вынесено из dlo_ivp.php
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package			Polka
* @access			public
* @copyright		Copyright (c) 2009 Swan Ltd.
* @author			Petukhov Ivan aka Lich (megatherion@list.ru)
* @originalauthor	Pshenitcyn Ivan aka IvP (ipshon@rambler.ru)
* @version			24.07.2009
*/

class PersonDispReg extends swController {

	public $inputRules = array(
		'getPersonDispReg' => array(
			array(
				'field' => 'PersonDispReg_id',
				'label' => 'Идентификатор записи в регистре',
				'rules' => 'trim|required',
				'type' => 'id'
			)
		),
		'savePersonDispReg' => array(
			array(
				'field' => 'mode',
				'label' => 'Режим работы: добавление/редактирование',
				'rules' => 'trim|required',
				'type' => 'string'
			),
			array(
				'field' => 'PersonDispReg_id',
				'label' => 'Идентификатор записи в регистре',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'Server_id',
				'label' => 'Идентификатор сервера',
				'rules' => 'trim|required',
				'type' => 'int'
			),
			array(
				'field' => 'Sickness_id',
				'label' => 'Идентификатор категории заболевания',
				'rules' => 'trim|required',
				'type' => 'id'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => 'trim|required',
				'type' => 'id'
			),
			array(
				'field' => 'Diag_id',
				'label' => 'Идентификатор дигноза',
				'rules' => 'trim|required',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_id',
				'label' => 'Идентификатор отделения',
				'rules' => 'trim|required',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonal_id',
				'label' => 'Идентификатор врача',
				'rules' => 'trim|required',
				'type' => 'id'
			),
			array(
				'field' => 'Sickness_Date',
				'label' => 'Дата добавления в регистр',
				'rules' => 'trim|required',
				'type' => 'date'
			),
			array(
				'field' => 'Sickness_Date_End',
				'label' => 'Дата исключения из регистра',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'DispOutType_id',
				'label' => 'Идентификатор причины исключения из регистра',
				'rules' => 'trim',
				'type' => 'id'
			)
		),
		'getPersonDispRegListBySickness' => array(
			array(
				'field' => 'node',
				'label' => 'Позиция в дереве категорий заболеваний',
				'rules' => 'trim|required',
				'type' => 'id'
			)
		),
		'dropPersonDispReg' => array(
			array(
				'field' => 'PersonDispReg_id',
				'label' => 'Идентификатор записи в регистре',
				'rules' => 'trim|required',
				'type' => 'id'
			)
		)
	);

	/**
	 * PersonDispReg constructor.
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * @return bool
	 */
	function Index() {
		return false;
	}

	/**
	 * Получение информации о человеке в регистре заболеваний
	 */
	function getPersonDispReg() {
		$this->load->database();
		$this->load->model("PersonDispReg_model", "dbmodel");

		$data = array();
		// Получаем сессионные переменные
		$data = $this->ProcessInputData('getPersonDispReg', true);
		if ($data) {
			$response = $this->dbmodel->getPersonDispReg($data);
			if (is_array($response) && count($response) > 0) {
				foreach ($response as $row) {
					array_walk($row, 'ConvertFromWin1251ToUTF8');
					$val[] = $row;
				}
				$this->ReturnData($val);
			}
			else
				ajaxErrorReturn();
		}
	}

	/**
	 * Сохранение человека в регистре заболеваний
	 */
	function savePersonDispReg() {
		$this->load->database();
		$this->load->model("PersonDispReg_model", "dbmodel");

		$data = array();
		// Получаем сессионные переменные
		$data = $this->ProcessInputData('savePersonDispReg', true);
		if ($data) {
			$response = $this->dbmodel->savePersonDispReg($data);
			if (is_array($response) && count($response) > 0) {
				foreach ($response as $row) {
					array_walk($row, 'ConvertFromWin1251ToUTF8');
					$val[] = $row;
				}
				$this->ReturnData($val);
			}
			else
				ajaxErrorReturn();
		}
	}

	/**
	 * Получение категорий регистра заболеваний
	 */
	function getSicknessTree() {
		$this->load->database();
		$this->load->model("PersonDispReg_model", "dbmodel");

		$response = $this->dbmodel->getSicknessTree();
		$val = array();
		if (is_array($response) && count($response) > 0) {
			foreach ($response as $row) {
				array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val[] = array (
					'text' => $row['Sickness_Name'],
					'id' => $row['Sickness_id'],
					'leaf' => true,
					'cls' => 'file'
				);
			}
			$this->ReturnData($val);
		}
		else
			ajaxErrorReturn();
	}

	/**
	 * Получение людей в регистре по выбранной категории заболеваний
	 */
	function getPersonDispRegListBySickness() {
		$this->load->database();
		$this->load->model("PersonDispReg_model", "dbmodel");

		$data = array();
		// Получаем сессионные переменные
		$data = $this->ProcessInputData('getPersonDispRegListBySickness', true);
		if ($data) {
			$response = $this->dbmodel->getPersonDispRegListBySickness($data);
			$val = array();
			if (is_array($response) && count($response) > 0) {
				foreach ($response as $row) {
					array_walk($row, 'ConvertFromWin1251ToUTF8');
					$val[] = $row;
				}
				$this->ReturnData($val);
			}
			else
				ajaxErrorReturn();
		}
	}

	/**
	 * Исключение человека из регистра заболеваний
	 */
	function dropPersonDispReg() {
		$this->load->database();
		$this->load->model("PersonDispReg_model", "dbmodel");

		$data = array();
		// Получаем сессионные переменные
		$data = $this->ProcessInputData('dropPersonDispReg', true);
		if ($data) {
			$info = $this->dbmodel->dropPersonDispReg($data);
			$val[] = array('success'=>'true');
			$this->ReturnData($val);
		}
	}

}
?>
