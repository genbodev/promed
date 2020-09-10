<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * CoeffIndex - контроллер для работы с коэффициентами индексации
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			29.10.2013
 *
 * @property CoeffIndex_model dbmodel
 */

class CoeffIndex extends swController {
	protected  $inputRules = array(
		'loadCoeffIndexGrid' => array(
			array(
				'field' => 'CoeffIndex_id',
				'label' => 'Идентификатор коэффициента индексации',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadCoeffIndexTariffGrid' => array(
			array(
				'field' => 'CoeffIndexTariff_id',
				'label' => 'Идентификатор значения коэффициента индексации',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'TariffClass_id',
				'label' => 'Идентификатор вида тарифа',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'CoeffIndex_id',
				'label' => 'Идентификатор коэффициента индексации',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_id',
				'label' => 'Идентификатор отделения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'CoeffIndexTariff_begDate',
				'label' => 'Дата начала действия значения коэффициента индексации',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'CoeffIndexTariff_endDate',
				'label' => 'Дата окончания действия значения коэффициента индексации',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'isClose',
				'label' => 'Флаг закрытия',
				'rules' => '',
				'type' => 'int'
			)
		),
		'loadCoeffIndexEditForm' => array(
			array(
				'field' => 'CoeffIndex_id',
				'label' => 'Идентификатор коэффициента индексации',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadCoeffIndexTariffEditForm' => array(
			array(
				'field' => 'CoeffIndexTariff_id',
				'label' => 'Идентификатор значения коэффициента индексации',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadCoeffIndexList' => array(
			array(
				'field' => 'query',
				'label' => 'Запрос',
				'rules' => '',
				'type' => 'string'
			)
		),
		'saveCoeffIndex' => array(
			array(
				'field' => 'CoeffIndex_id',
				'label' => 'Идентификатор коэффициента индексации',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'CoeffIndex_Code',
				'label' => 'Код коэффициента индексации',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'CoeffIndex_SysNick',
				'label' => 'Краткое наименование коэффициента индексации',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'CoeffIndex_Name',
				'label' => 'Полное наименование коэффициента индексации',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'CoeffIndex_Min',
				'label' => 'Минимальное значение коэффициента индексации',
				'rules' => '',
				'type' => 'float'
			),
			array(
				'field' => 'CoeffIndex_Max',
				'label' => 'Максимальное значение коэффициента индексации',
				'rules' => '',
				'type' => 'float'
			)
		),
		'saveCoeffIndexTariff' => array(
			array(
				'field' => 'CoeffIndexTariff_id',
				'label' => 'Идентификатор значения коэффициента индексации',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'TariffClass_id',
				'label' => 'Идентификатор вида тарифа',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'CoeffIndex_id',
				'label' => 'Идентификатор коэффициента индексации',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'CoeffIndexTariff_Value',
				'label' => 'Значение коэффициента индексации',
				'rules' => 'required',
				'type' => 'float'
			),
			array(
				'field' => 'CoeffIndexTariff_begDate',
				'label' => 'Дата начала действия значения коэффициента индексации',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'CoeffIndexTariff_endDate',
				'label' => 'Дата окончания действия значения коэффициента индексации',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'LpuSection_id',
				'label' => 'Идентификатор отделения',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'deleteCoeffIndex' => array(
			array(
				'field' => 'CoeffIndex_id',
				'label' => 'Идентификатор коэффициента индексации',
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
		$this->load->model('CoeffIndex_model', 'dbmodel');
	}

	/**
	 * Возвращает список коэффициентов индексации
	 * @return bool
	 */
	function loadCoeffIndexGrid()
	{
		$data = $this->ProcessInputData('loadCoeffIndexGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadCoeffIndexGrid($data);
		$this->ProcessModelMultiList($response, true, 'При получении данных возникли ошибки')->ReturnData();
		return true;
	}

	/**
	 * Возвращает список значений коэффициентов индексации
	 * @return bool
	 */
	function loadCoeffIndexTariffGrid()
	{
		$data = $this->ProcessInputData('loadCoeffIndexTariffGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadCoeffIndexTariffGrid($data);
		$this->ProcessModelMultiList($response, true, 'При получении данных возникли ошибки')->ReturnData();
		return true;
	}

	/**
	 * Возвращает данные для редактирования коэффициента индексации
	 * @return bool
	 */
	function loadCoeffIndexEditForm()
	{
		$data = $this->ProcessInputData('loadCoeffIndexEditForm', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadCoeffIndexEditForm($data);
		$this->ProcessModelList($response,true,true)->ReturnData();
		return true;
	}

	/**
	 * Возвращает данные для редактирования значения коэффициента индексации
	 * @return bool
	 */
	function loadCoeffIndexTariffEditForm()
	{
		$data = $this->ProcessInputData('loadCoeffIndexTariffEditForm', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadCoeffIndexTariffEditForm($data);
		$this->ProcessModelList($response,true,true)->ReturnData();
		return true;
	}

	/**
	 * Возвращает список коэффициентов индексации
	 * @return bool
	 */
	function loadCoeffIndexList()
	{
		$data = $this->ProcessInputData('loadCoeffIndexList', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadCoeffIndexList($data);
		$this->ProcessModelList($response,true,true)->ReturnData();
		return true;
	}

	/**
	 * Сохранение коэффициента индексации
	 * @return bool
	 */
	function saveCoeffIndex()
	{
		$data = $this->ProcessInputData('saveCoeffIndex', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveCoeffIndex($data);
		$this->ProcessModelSave($response, true, 'При сохранении возникли ошибки')->ReturnData();
		return true;
	}

	/**
	 * Сохранение значения коэффициента индексации
	 * @return bool
	 */
	function saveCoeffIndexTariff()
	{
		$data = $this->ProcessInputData('saveCoeffIndexTariff', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveCoeffIndexTariff($data);
		$this->ProcessModelSave($response, true, 'При сохранении возникли ошибки')->ReturnData();
		return true;
	}

	/**
	 * Удаление коэффициента индексации
	 * @return bool
	 */
	function deleteCoeffIndex()
	{
		$data = $this->ProcessInputData('deleteCoeffIndex', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->deleteCoeffIndex($data);
		$this->ProcessModelSave($response, true, 'При удалении возникли ошибки')->ReturnData();
		return true;
	}
}