<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
* BirthSpecStac - Контроллер для работы со спецификой рождений
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* @package      Stac
* @access       public
* @copyright    Copyright (c) 2009-2011 Swan Ltd.
* @author       gabdushev
* @version      11 2011
 * @property BirthSpecStac_model dbmodel
*/
class BirthSpecStac extends swController {

	public $inputRules = array(
		'loadBirthSpecStac' => array(
			array(
				'field' => 'EvnSection_id',
				'label' => 'Идентификатор случая движения пациента в стационаре',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'deletePerson'=>array(
			array(
				'field' => 'Person_id',
				'label' => 'Person_id',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'deleteChild' =>array(
			array(
				'field' => 'ChildEvnPS_id',
				'label' => 'ChildEvnPS_id',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'type',
				'label' => 'type',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'PersonNewBorn_id',
				'label' => 'PersonNewBorn_id',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Person_id',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnLink_id',
				'label' => 'EvnLink_id',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PntDeathSvid_id',
				'label' => 'PntDeathSvid_id',
				'rules' => '',
				'type' => 'id'
			)
		),
		'deleteChildren' =>array(
			array(
				'field' => 'PersonNewBorn_ids',
				'label' => 'Список идентификаторов детей для удаления',
				'rules' => 'required',
				'type' => 'json_array'
			),
		),
		'loadBirthSpecStacChildGrid' => array(
			array(
				'field' => 'EvnSection_pid',
				'label' => 'Идентификатор КВС пациента',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'BirthSpecStac_id',
				'label' => '',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_id',
				'label' => '',
				'rules' => '',
				'type' => 'id'
			),
		),
		'loadChildDeathGrid' => array(
			array(
				'field' => 'BirthSpecStac_id',
				'label' => '',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnSection_id',
				'label' => '',
				'rules' => '',
				'type' => 'id'
			)
		),
		'checkChild' => array(
			array(
				'field' => 'childEvnPS_id',
				'label' => 'Идентификатор КВС ребенка',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'motherEvnPS_id',
				'label' => 'Идентификатор КВС матери',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'motherEvnSection_id',
				'label' => 'Идентификатор Движения матери',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'mother_Person_id',
				'label' => 'Идентификатор матери',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'child_Person_id',
				'label' => 'Идентификатор ребенка',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'BirthSpecStac_OutcomeDate',
				'label' => 'Дата исхода беременности',
				'rules' => '',
				'type' => 'date'
			)
		)
	);

	/**
	 * @construct
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('BirthSpecStac_model', 'dbmodel');
	}

	/**
	*  Получение данных для формы редактирования сведений о новорожденном
	*  Входящие данные: $_POST['']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования КВС
	*/
	function load() {
		$data = $this->ProcessInputData('loadBirthSpecStac', true);
		if ( $data === false ) { return false; }

		$evnSectionId = $data['EvnSection_id'];

		$response = $this->dbmodel->load($evnSectionId);
		$outdata = $this->ProcessModelList($response, true, true)->ReturnData();
		
		return true;
	}

	/**
	 * @comment
	 */
	function deleteChild(){
		$data = $this->ProcessInputData('deleteChild', true);
		if ( $data === false ) { return false; }
		$response = $this->dbmodel->deleteChild($data);
		$this->ProcessModelSave($response, true, 'При удалении возникли ошибки')->ReturnData();
		
		return true;
	}

	/**
	 * Удаление детей из списка
	 */
	function deleteChildren(){
		$data = $this->ProcessInputData('deleteChildren', true);
		if ( $data === false ) { return false; }
		$response = $this->dbmodel->deleteChildren($data);
		$this->ProcessModelSave($response, true, 'При удалении возникли ошибки')->ReturnData();

		return true;
	}

	/**
	 * Загрузка грида мертворожденных
	 */
	function loadChildDeathGridData() {
		$data = $this->ProcessInputData('loadChildDeathGrid', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadChildDeathGridData($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return false;
	}

	/**
	 * Загрузка грида рожденных
	 */
	function loadChildGridData() {
		$data = $this->ProcessInputData('loadBirthSpecStacChildGrid', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadChildGridData($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return false;
	}

	/**
	 * Проверка ребенка
	 * Может ли человек являться ребенком этой роженицы
	 * на входе КВС ребенка, КВС матери
	 * На выходе JSON (в котором сообщение об ошибке, если есть)
	 */
	function checkChild(){
		$data = $this->ProcessInputData('checkChild', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->checkChild($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return false;
	}

}