<?php
/**
 * Created by PhpStorm.
 * User: MagafurovSM
 * Date: 27.07.2018
 * Time: 9:04
 */

/**
 * @package     All
 * @access      public
 * @copyright   Copyright (c) 2018 EMSIS.
 * @author      Salavat Magafurov
 * @version     27.07.2018
 */
require_once(APPPATH . 'controllers/MorbusNephro.php');

class Ufa_MorbusNephro extends MorbusNephro
{
	/**
	 * Конструктор.
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	* Сохранение результата расчета СКФ
	*/
	function saveCkdEpiResult() {
		$this->inputRules['saveCkdEpiResult'] = array(
			array('field' => 'NephroCkdEpi_id',      'label' => 'Идентификатор',         'rules' => '',          'type' => 'int'),
			array('field' => 'MorbusNephroRate_id',  'label' => 'MorbusNephroDisp_id',   'rules' => 'required',  'type' => 'int'),
			array('field' => 'CreatinineUnitType_id','label' => 'Единица измерения',     'rules' => 'required',  'type' => 'int'),
			array('field' => 'NephroCkdEpi_value',   'label' => 'Результат СКФ',         'rules' => 'required',  'type' => 'int'),
			array('field' => 'MorbusNephro_id',      'label' => 'Ид записи регистра',    'rules' => 'required',  'type' => 'int')
		);

		$this->load->model('MorbusNephroDisp_model', 'nephroDisp');
		$data = $this->ProcessInputData('saveCkdEpiResult', true);
		if ($data === false) { return false; }
		$result = $this->nephroDisp->saveCkdEpiResult($data);
		//$this->ProcessModelSave($response, true, 'При сохранении возникли ошибки')->ReturnData();
		$this->ReturnData($result);
	}


	/**
	 * Загрузка данных для формы "Тип доступа"
	 */
	function doLoadEditFormNephroAccess() {
		$this->load->model('NephroAccess_model', 'nephroAccess');
		$this->inputRules['doLoadEditFormNephroAccess'] = $this->nephroAccess->getInputRules(swModel::SCENARIO_LOAD_EDIT_FORM);
		$data = $this->ProcessInputData('doLoadEditFormNephroAccess', true);
		if ($data === false) { return false; }
		$response = $this->nephroAccess->doLoadEditFormNephroAccess($data);
		$this->ReturnData($response);
	}

	/**
	 * Сохранение формы "Тип доступа" 
	 */
	function nephroAccessSave() {
		$this->load->model('NephroAccess_model', 'nephroAccess');
		$this->inputRules['nephroAccessSave'] = $this->nephroAccess->getInputRules(swModel::SCENARIO_DO_SAVE);
		$data = $this->ProcessInputData('nephroAccessSave', true);
		if ($data === false) { return false; }
		$this->nephroAccess->setScenario(swModel::SCENARIO_DO_SAVE);
		$response = $this->nephroAccess->doSave($data);
		$this->ProcessModelSave($response, true, 'При сохранении возникли ошибки')->ReturnData();
	}
	
	/**
	 * Загрузка данных для формы "Комиссия МЗ РБ"
	 */
	function doLoadEditFormNephroCommission() {
		$this->load->model('NephroCommission_model', 'nephroCommission');
		$this->inputRules['doLoadEditFormNephroCommission'] = $this->nephroCommission->getInputRules(swModel::SCENARIO_LOAD_EDIT_FORM);
		$data = $this->ProcessInputData('doLoadEditFormNephroCommission', true);
		if ($data === false) { return false; }
		$response = $this->nephroCommission->doLoadEditFormNephroCommission($data);
		$this->ReturnData($response);
	}

	/**
	 * Сохранение формы "Комиссия МЗ РБ"
	 */
	function nephroCommissionSave() {
		$this->load->model('NephroCommission_model', 'nephroCommission');
		$this->inputRules['nephroCommissionSave'] = $this->nephroCommission->getInputRules(swModel::SCENARIO_DO_SAVE);
		$data = $this->ProcessInputData('nephroCommissionSave', true);
		if ($data === false) { return false; }
		$this->nephroCommission->setScenario(swModel::SCENARIO_DO_SAVE);
		$response = $this->nephroCommission->doSave($data);
		$this->ProcessModelSave($response, true, 'При сохранении возникли ошибки')->ReturnData();
	}

	/**
	* Загрузка файла для формы "Загрузка фала"
	*/
	function uploadFile() {
		$this->load->model('NephroDocument_model', 'nephroDocument');
		$this->inputRules['uploadFile'] = $this->nephroDocument->getInputRules('uploadFile');
		$data = $this->ProcessInputData('uploadFile', true);
		if ( $data === false ) { return false; }

		if ( !isset($_FILES['userfile']) ) {
			$this->ReturnError('Вы не выбрали файл для загрузки.', 701);
			return false;
		}

		$response = $this->nephroDocument->uploadFile($_FILES['userfile'], $data);

		if (ob_get_level()) {
			ob_end_clean();
		}

		$response = array();
		$response['success'] = true;
		$response['Error_Msg'] = null;
		$response['Error_Code'] = null;
		$response['data'] = 'daar';

		return $this->ProcessModelSave($response, true)->ReturnData();
	}

	/**
	* Скачивание документа
	*/
	function getDocument() {
		$this->load->model('NephroDocument_model', 'nephroDocument');
		$this->inputRules['getDocument'] = $this->nephroDocument->getInputRules('getDocument');
		$data = $this->ProcessInputData('getDocument', true);
		if ( $data === false ) { return false; }

		$response = $this->nephroDocument->getDocument($data);

		header("Content-type: ".$response[0]['type']);
		header("Content-length:".$response[0]['size']);
		header("Content-Disposition: attachment; filename=".$response[0]['filename']);
		header ("Content-Description: PHP Generated Data");

		ob_end_clean();
		flush();
		echo base64_decode($response[0]['content']);
	}

	/**
	 * Получение последней записи "Креатинин крови"
	 */
	function getLastRate() {
		$this->inputRules['getLastRate'] = array(
			array('field' => 'MorbusNephro_id',     'label' => 'Заболевание', 'rules' => 'required', 'type' => 'int' )
		);

		$this->load->model('MorbusNephroDisp_model', 'nephroDisp');
		$data = $this->ProcessInputData('getLastRate', true);
		if ( $data === false ) { return false; }
		$response = $this->nephroDisp->getLastRate($data);
		$this->ReturnData($response);
	}

	/**
	 *  Загрузка для формы редактирования объекта списка "назначенное лечение"
	 */
	function doLoadEditFormMorbusNephroDrug()
	{
		$this->load->model('MorbusNephroDrug_model', 'model');
		$this->inputRules['doLoadEditFormMorbusNephroDrug'] = $this->model->getInputRules(swModel::SCENARIO_LOAD_EDIT_FORM);
		$data = $this->ProcessInputData('doLoadEditFormMorbusNephroDrug', true);
		if ($data === false) { return false; }
		$response = $this->model->doLoadEditForm($data);
		$this->ProcessModelList($response, true, false)->ReturnData();
		return true;
	}

	/**
	 *  Сохранение объекта списка "Назначенное лечение"
	 */
	function doSaveMorbusNephroDrug()
	{
		$this->load->model('MorbusNephroDrug_model', 'morbusNephroDrug');
		$this->inputRules['doSaveMorbusNephroDrug'] = $this->morbusNephroDrug->getInputRules(swModel::SCENARIO_DO_SAVE);
		$data = $this->ProcessInputData('doSaveMorbusNephroDrug', true);
		if ($data === false) { return false; }
		$this->morbusNephroDrug->setScenario(swModel::SCENARIO_DO_SAVE);
		$response = $this->morbusNephroDrug->doSave($data);
		$this->ProcessModelSave($response, true, 'При сохранении возникли ошибки')->ReturnData();
		return true;
	}

	/**
	 * Получение данных динамического наблюдения по MorbusNephro_id
	 */
	function doLoadDispList() {
		$this->load->model('MorbusNephroDisp_model', 'dbmodel');
		$this->inputRules['doLoadDispList'] = $this->dbmodel->getInputRules('doLoadDispList');
		$data = $this->ProcessInputData('doLoadDispList', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->doLoadDispList($data);
		$this->ReturnData($response);
	}

	/**
	 * Получение списка схем
	 */
	function doLoadSchemeList() {
		$this->load->model('MorbusNephroDrug_model', 'dbmodel');
		$this->inputRules['doLoadSchemeList'] = $this->dbmodel->getInputRules('doLoadSchemeList');
		$data = $this->ProcessInputData('doLoadSchemeList', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->doLoadSchemeList($data);
		$this->ReturnData($response);
	}

	/**
	 * Получение списка правил схем
	 */
	function doLoadSchemeRuleList() {
		$this->load->model('MorbusNephroDrug_model', 'dbmodel');
		$this->inputRules['doLoadSchemeRuleList'] = $this->dbmodel->getInputRules('doLoadSchemeRuleList');
		$data = $this->ProcessInputData('doLoadSchemeRuleList', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->doLoadSchemeRuleList($data);
		$this->ReturnData($response);
	}

	/**
	 * Получение списка использованных схем
	 */
	function doLoadUsedSchemeList() {
		$this->load->model('MorbusNephroDrug_model', 'dbmodel');
		$this->inputRules['doLoadUsedSchemeList'] = $this->dbmodel->getInputRules('doLoadUsedSchemeList');
		$data = $this->ProcessInputData('doLoadUsedSchemeList', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->doLoadUsedSchemeList($data);
		$this->ReturnData($response);
	}

	/**
	 * Получение списка медикаментов
	 */
	function doLoadMnnList() {
		$this->load->model('MorbusNephroDrug_model', 'dbmodel');
		$this->inputRules['doLoadMnnList'] = $this->dbmodel->getInputRules('doLoadMnnList');
		$data = $this->ProcessInputData('doLoadMnnList', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->doLoadMnnList($data);
		$this->ReturnData($response);
	}

	/**
	 * Получение списка из таблицы NephroDrugSchemeParent
	 */
	function doLoadParentList() {
		$this->load->model('MorbusNephroDrug_model', 'dbmodel');
		$this->inputRules['doLoadParentList'] = $this->dbmodel->getInputRules('doLoadParentList');
		$data = $this->ProcessInputData('doLoadParentList', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->doLoadParentList($data);
		$this->ReturnData($response);
	}

	/**
	 * Получение списка из таблицы NephroDrugSchemeNoeffect
	 */
	function doLoadNoeffectList() {
		$this->load->model('MorbusNephroDrug_model', 'dbmodel');
		$this->inputRules['doLoadNoeffectList'] = $this->dbmodel->getInputRules('doLoadNoeffectList');
		$data = $this->ProcessInputData('doLoadNoeffectList', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->doLoadNoeffectList($data);
		$this->ReturnData($response);
	}

	/**
	 * Получение списка протоколов ВК
	 */
	function doLoadVKProtocolList() {
		$this->load->model('MorbusNephroDrug_model', 'dbmodel');
		$this->inputRules['doLoadVKProtocolList'] = $this->dbmodel->getInputRules('doLoadVKProtocolList');
		$data = $this->ProcessInputData('doLoadVKProtocolList', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->doLoadVKProtocolList($data);
		$this->ReturnData($response);
	}
}