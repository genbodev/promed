<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * @property EvnUslugaTelemed_model $dbmodel
 */
class EvnUslugaTelemed extends swController
{
	public $inputRules = array(
		'unExec' => array(
			array(
				'field' => 'id',
				'label' => 'Идентификатор направления',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadWorkPlaceGrid' => array(
			array('field' => 'begDate','label' => 'Дата с','rules' => 'trim|required','type' => 'date'),
			array('field' => 'endDate','label' => 'Дата по','rules' => 'trim|required','type' => 'date'),
			array('field' => 'MedService_id','label' => 'Идентификатор службы ЦУК','rules' => 'trim|required','type' => 'id'),
			array('field' => 'EvnDirection_id','label' => 'Новое направление','rules' => 'trim','type' => 'id'),
			array('field' => 'EvnDirection_Num','label' => 'Номер направления','rules' => 'ban_percent|trim','type' => 'string'),
			array('field' => 'Person_BirthDay','label' => 'ДР','rules' => 'trim','type' => 'date'),
			array('field' => 'Person_SurName','label' => 'Фамилия','rules' => 'ban_percent|trim','type' => 'string'),
			array('field' => 'Person_FirName','label' => 'Имя','rules' => 'ban_percent|trim','type' => 'string'),
			array('field' => 'Person_SecName','label' => 'Отчество','rules' => 'ban_percent|trim','type' => 'string'),
			array('field' => 'Diag_Code_From','label' => 'Код диагноза с','rules' => 'ban_percent|trim','type' => 'string'),
			array('field' => 'Diag_Code_To','label' => 'по','rules' => 'ban_percent|trim','type' => 'string'),
			array('field' => 'RemoteConsultCause_id','label' => 'Цель консультирования','rules' => 'trim','type' => 'id'),
			array('field' => 'LpuCombo_id','label' => 'МО','rules' => 'trim','type'=>'id'),
		),
		'loadParentEvnDirection' => array(
			array('field' => 'EvnDirection_id', 'label' => 'Идентификатор направления', 'rules'=>'trim','type'=>'id')
		),
		'loadReceptKardioPanel' => array(
			array('field' => 'Evn_id', 'label' => 'Идентификатор родительского события', 'rules' => 'required', 'type' => 'id')
		)
	);

	/**
	 * construct
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model('EvnUslugaTelemed_model', 'dbmodel');
	}

	/**
	 *  Загрузка журнала рабочего места службы "Центр удалённой консультации"
	 */
	function loadWorkPlaceGrid()
	{
		$data = $this->ProcessInputData('loadWorkPlaceGrid', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->loadWorkPlaceGrid($data);
		$this->ProcessModelList($response, true, true)
			->ReturnData();
		return true;
	}

	/**
	 * Отменить выполнение
	 */
	function unExec()
	{
		$data = $this->ProcessInputData('unExec', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->unExec($data);
		$this->ProcessModelSave($response, true, 'При удалении случая оказания телемедицинской услуги')
			->ReturnData();
		return true;
	}

	/**
	 *  Сохранение случая оказания телемедицинской услуги
	 */
	function doSave()
	{
		$this->inputRules['saveEvnUslugaTelemed'] = $this->dbmodel->getInputRules(swModel::SCENARIO_DO_SAVE);
		$data = $this->ProcessInputData('saveEvnUslugaTelemed', true);
		if ($data === false) { return false; }
		if (empty($data['isAutoCreate'])) {
			$data['scenario'] = swModel::SCENARIO_DO_SAVE;
		} else {
			$data['scenario'] = swModel::SCENARIO_AUTO_CREATE;
		}
		
		$response = $this->dbmodel->doSave($data);		
		//рассылка уведомлений врачам о выполнении телемедицинской услуги 
		//в соответствии с настройками у каждого врача
		$this->load->helper('PersonNotice');
		$PersonNotice = new PersonNoticeEvn($data['Person_id'], 'EvnUslugaTelemed', $response['EvnUslugaTelemed_id'], true);
		$PersonNotice->loadPersonInfo();
		$PersonNotice->processStatusChange(true);//рассылаем
		
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении случая оказания телемедицинской услуги')
			->ReturnData();
		return true;
	}

	/**
	 * Загрузка формы редактирования случая оказания телемедицинской услуги
	 */
	function loadEditForm()
	{
		$this->inputRules['loadEditForm'] = $this->dbmodel->getInputRules(swModel::SCENARIO_LOAD_EDIT_FORM);
		$data = $this->ProcessInputData('loadEditForm', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->loadEditForm($data);
		$this->ProcessModelList($response, true, false)
			->ReturnData();
		return true;
	}
	
	/**
	 * Получить информацию по связанному направлению
	 */
	function loadParentEvnDirection()
	{
		$data = $this->ProcessInputData('loadParentEvnDirection', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->loadParentEvnDirection($data);
		$this->ProcessModelSave($response, true, true)
			->ReturnData();
		return true;
	}

	/**
	 *  списка рецептов для формы редактирования услуги
	 */
	function loadReceptKardioPanel()
	{
		$data = $this->ProcessInputData('loadReceptKardioPanel', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->loadReceptKardioPanel($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
}
