<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * LpuFSSContract - контроллер
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 *
 * @property LpuFSSContract_model dbmodel
 */

class LpuFSSContract extends swController {
	protected  $inputRules = array(
		'delete' => array(
			array(
				'field' => 'id',
				'label' => 'Идентификатор договора',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'loadList' => array(
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'type' => 'id', 'rules' => 'required'),
			array('field' => 'LpuFSSContractType_id', 'label' => '', 'type' => 'id', 'rules' => ''),
			array('field' => 'LpuFSSContract_Num', 'label' => '', 'type' => 'string', 'rules' => 'trim'),
			array('field' => 'isClose', 'label' => '', 'type' => 'id', 'rules' => ''),
		),
		'load' => array(
			array(
				'field' => 'LpuFSSContract_id',
				'label' => 'Идентификатор договора',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'save' => array(
			array(
				'field' => 'LpuFSSContract_id',
				'label' => 'Идентификатор договора',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор МО',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'LpuFSSContractType_id',
				'label' => 'Тип договора',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'LpuFSSContract_Num',
				'label' => 'Номер договора',
				'rules' => 'trim|required',
				'type' => 'string'
			),
			array(
				'field' => 'LpuFSSContract_begDate',
				'label' => 'Период действия',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'LpuFSSContract_endDate',
				'label' => 'Период действия',
				'rules' => '',
				'type' => 'date'
			),
		),
	);

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model('LpuFSSContract_model', 'dbmodel');
	}

	/**
	 * Удаление договора
	 */
	function delete()
	{
		$data = $this->ProcessInputData('delete');
		if ($data === false) { return false; }

		$response = $this->dbmodel->delete($data);
		$this->ProcessModelSave($response, true, 'Ошибка при удалении договора')->ReturnData();
		return true;
	}

	/**
	 * Возвращает список договоров
	 */
	function loadList()
	{
		$data = $this->ProcessInputData('loadList');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Возвращает договор
	 */
	function load()
	{
		$data = $this->ProcessInputData('load');
		if ($data === false) { return false; }

		$response = $this->dbmodel->load($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Сохранение договора
	 */
	function save()
	{
		$data = $this->ProcessInputData('save');
		if ($data === false) { return false; }

		if ( !empty($data['LpuFSSContract_begDate']) && !empty($data['LpuFSSContract_endDate']) && $data['LpuFSSContract_begDate'] > $data['LpuFSSContract_endDate'] ) {
			$this->ReturnData(array(
				'Error_Msg' => 'Дата начала договора не может быть позже даты окончания договора',
				'Error_Code' => 146,
				'success' => false
			));
			return false;
		}

		$response = $this->dbmodel->save($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}
}