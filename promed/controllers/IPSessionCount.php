<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * CoeffIndex - контроллер для работы с исключениями для количества параллельных сессий (?кто придумает лучше, можете поменять?)
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Yavorskiy Maksim (m.yavorskiy@swan.perm.ru)
 * @version			16.10.2019
 *
 * @property IPSessionCount_model dbmodel
 */

class IPSessionCount extends swController {
	protected  $inputRules = array(
		'loadIPSessionCountGrid' => array(
			array(
				'field' => 'IPSessionCount_id',
				'label' => 'Идентификатор ip адреса',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadIPSessionCountEditForm' => array(
			array(
				'field' => 'IPSessionCount_id',
				'label' => 'Идентификатор ip адреса',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'saveIPSessionCount' => array(
			array(
				'field' => 'IPSessionCount_id',
				'label' => 'Идентификатор ip адреса',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'IPSessionCount_IP',
				'label' => 'ip-адрес',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'IPSessionCount_Max',
				'label' => 'Кол-во паралелльных сессий для ip',
				'rules' => 'required',
				'type' => 'string'
			)
		),
		'deleteIPSessionCount' => array(
			array(
				'field' => 'IPSessionCount_id',
				'label' => 'Идентификатор ip адреса',
				'rules' => 'required',
				'type' => 'id'
			)
		)
	);

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model('IPSessionCount_model', 'dbmodel');
	}

	/**
	 * Возвращает список IP
	 * @return bool
	 */
	function loadIPSessionCountGrid()
	{
		$data = $this->ProcessInputData('loadIPSessionCountGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadIPSessionCountGrid($data);
		$this->ProcessModelMultiList($response, true, 'При получении данных возникли ошибки')->ReturnData();
		return true;
	}

	/**
	 * Возвращает данные для редактирования IP
	 * @return bool
	 */
	function loadIPSessionCountEditForm()
	{
		$data = $this->ProcessInputData('loadIPSessionCountEditForm', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadIPSessionCountEditForm($data);
		$this->ProcessModelList($response,true,true)->ReturnData();
		return true;
	}

	/**
	 * Сохранение ip-адреса
	 * @return bool
	 */
	function saveIPSessionCount()
	{
		$data = $this->ProcessInputData('saveIPSessionCount', true);
		if ($data === false) { return false; }

		if ( empty($data['IPSessionCount_Max']) || empty($data['IPSessionCount_IP']) ) {
			$response['Error_Msg'] = 'Все поля обязательны для заполнения.';
			return array($response);
		}

		$response = $this->dbmodel->saveIPSessionCount($data);
		$this->ProcessModelSave($response, true, 'При сохранении возникли ошибки')->ReturnData();
		return true;
	}


}