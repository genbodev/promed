<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* TODO: complete explanation, preamble and describing
* Контроллер для объектов Регистр
*
* @package      PersonRegister
* @access       public
* @copyright    Copyright (c) 2011 Swan Ltd.
* @author       Alexander Chebukin 
* @version
 * @property PersonRegister_model PersonRegister_model
 * @property EvnNotifyRegister_model $EvnNotifyRegister_model
 * @property PersonRegisterNolos_model $PersonRegisterNolos_model
*/

class PersonRegister extends swController
{
	/**
	 * Description
	 */
	function __construct() {
		parent::__construct();
		$this->inputRules = array(
			'save' => array(
				array(
					'field' => 'PersonRegister_id',
					'label' => 'PersonRegister_id',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'ignoreCheckAnotherDiag',
					'label' => 'Флаг игнорирования проверки на наличие записей с другим диагнозом',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Mode',
					'label' => 'Mode',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'PersonRegisterType_id',
					'label' => 'PersonRegisterType_id',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'PersonRegisterType_SysNick',
					'label' => 'PersonRegisterType_SysNick',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Человек',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'MorbusType_SysNick',
					'label' => 'Тип регистра',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'Morbus_id',
					'label' => 'Morbus_id',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Diag_id',
					'label' => 'Диагноз',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'MorbusProfDiag_id',
					'label' => 'Заболевание',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'PersonRegister_Code',
					'label' => 'Код записи',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'PersonRegister_setDate',
					'label' => 'PersonRegister_setDate',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'PersonRegister_disDate',
					'label' => 'PersonRegister_disDate',
					'rules' => '',
					'type' => 'date'
				),				
				array(
					'field' => 'PersonRegisterOutCause_id',
					'label' => 'Причина исключения из регистра',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedPersonal_iid',
					'label' => 'Добавил человека в регистр - врач',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_iid',
					'label' => 'Добавил человека в регистр - ЛПУ',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'MedPersonal_did',
					'label' => 'Кто исключил человека из регистра - врач',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_did',
					'label' => 'Кто исключил человека из регистра - ЛПУ',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnNotifyBase_id',
					'label' => 'EvnNotifyBase_id',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'PersonRegister_Alcoholemia',
					'label' => 'PersonRegister_Alcoholemia',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'field' => 'Morbus_confirmDate',
					'label' => 'Morbus_confirmDate',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'Morbus_EpidemCode',
					'label' => 'Morbus_EpidemCode',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'autoExcept',
					'label' => 'autoExcept',
					'rules' => '',
					'type' => 'id'
				)
			),
			'loadEvnNotifyRegisterInclude'=>array(
				array(
					'field' => 'EvnNotifyRegister_id',
					'label' => 'EvnNotifyRegister_id',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'out' => array(
				array(
					'field' => 'PersonRegister_id',
					'label' => 'PersonRegister_id',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'PersonRegister_disDate',
					'label' => 'PersonRegister_disDate',
					'rules' => 'required',
					'type' => 'date'
				),				
				array(
					'field' => 'PersonRegisterOutCause_id',
					'label' => 'Причина исключения из регистра',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'PersonDeathCause_id',
					'label' => 'Причина смерти',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedPersonal_did',
					'label' => 'Кто исключил человека из регистра - врач',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_did',
					'label' => 'Кто исключил человека из регистра - ЛПУ',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'back' => array(
				array(
					'field' => 'PersonRegister_id',
					'label' => 'PersonRegister_id',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Diag_id',
					'label' => 'Diag_id',
					'rules' => '',
					'type' => 'id'
				),				
				array(
					'field' => 'Morbus_id',
					'label' => 'Morbus_id',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnNotifyBase_id',
					'label' => 'EvnNotifyBase_id',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'PersonRegister_setDate',
					'label' => 'PersonRegister_setDate',
					'rules' => '',
					'type' => 'date'
				)
			),
			'load' => array(
				array(
					'field' => 'PersonRegister_id',
					'label' => 'PersonRegister_id',
					'rules' => 'required',
					'type' => 'id'
				),
			),
			'loadList' => array(
				array(
					'field' => 'PersonRegister_id',
					'label' => 'PersonRegister_id',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'PersonRegisterType_id',
					'label' => 'PersonRegisterType_id',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'PersonRegister_Date',
					'label' => 'PersonRegister_Date',
					'rules' => '',
					'type'	=> 'string'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Person_id',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MorbusType_id',
					'label' => 'MorbusType_id',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Diag_id',
					'label' => 'Diag_id',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'PersonRegister_Code',
					'label' => 'PersonRegister_Code',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'PersonRegister_setDate',
					'label' => 'PersonRegister_setDate',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'PersonRegister_disDate',
					'label' => 'PersonRegister_disDate',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'Morbus_id',
					'label' => 'Morbus_id',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'PersonRegisterOutCause_id',
					'label' => 'PersonRegisterOutCause_id',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedPersonal_iid',
					'label' => 'MedPersonal_iid',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_iid',
					'label' => 'Lpu_iid',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedPersonal_did',
					'label' => 'MedPersonal_did',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_did',
					'label' => 'Lpu_did',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnNotifyBase_id',
					'label' => 'EvnNotifyBase_id',
					'rules' => '',
					'type' => 'id'
				),
			),
			'delete' => array(
				array(
					'field' => 'PersonRegister_id',
					'label' => 'PersonRegister_id',
					'rules' => 'required',
					'type' => 'id'
				),
			),
			'notinclude' => array(
				array(
					'field' => 'EvnNotifyBase_id',
					'label' => 'EvnNotifyBase_id',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'PersonRegisterFailIncludeCause_id',
					'label' => 'PersonRegisterFailIncludeCause_id',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_niid',
					'label' => 'Lpu_niid',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'MedPersonal_niid',
					'label' => 'MedPersonal_niid',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnOnkoNotify_Comment',
					'label' => 'Комментарий',
					'rules' => '',
					'type' => 'string'
				)
			),
			'loadPersonRegisterTypeGrid' => array(
			),
			'loadPersonRegisterHistList' => array(
				array(
					'field' => 'PersonRegister_id',
					'label' => 'Идентификатор записи регистра',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadPersonRegisterHistForm' => array(
				array(
					'field' => 'PersonRegister_id',
					'label' => 'Идентификатор записи регистра',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'createPersonRegisterHist' => array(
				array(
					'field' => 'PersonRegister_id',
					'label' => 'Идентификатор записи регистра',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'PersonRegisterHist_NumCard',
					'label' => 'Номер записи регистра',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'PersonRegisterHist_begDate',
					'label' => 'Дата начала',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedPersonal_id',
					'label' => 'Идентификатор врача',
					'rules' => '',
					'type' => 'id'
				),
			),
			'simpleCheckPersonRegisterExist' => [
				['field' => 'Person_id', 'label' => 'Идентификатор пациента', 'type' => 'id', 'rules' => 'required'],
				['field' => 'PersonRegisterType_Code', 'label' => 'Тип регистра', 'type' => 'id', 'rules' => 'required'],
				['field' => 'PersonRegister_setDate', 'label' => 'Дата', 'type' => 'date', 'rules' => ''],
			],
		);
		$this->load->database();
		$this->load->model('PersonRegister_model', 'PersonRegister_model');
	}

	/**
	 * Description
	 */
	function save() {
		$data = $this->ProcessInputData('save', true);
		if ($data) {
			if (isset($data['PersonRegister_id'])) {
				$this->PersonRegister_model->setPersonRegister_id($data['PersonRegister_id']);
			}
			if (isset($data['Mode'])) {
				$this->PersonRegister_model->setMode($data['Mode']);
			}
			if (isset($data['Person_id'])) {
				$this->PersonRegister_model->setPerson_id($data['Person_id']);
			}
			if (isset($data['PersonRegisterType_id'])) {
				$this->PersonRegister_model->setPersonRegisterType_id($data['PersonRegisterType_id']);
			}
			if (!empty($data['PersonRegisterType_SysNick'])) {
				$this->PersonRegister_model->setPersonRegisterType_SysNick($data['PersonRegisterType_SysNick']);
			}
			if (isset($data['MorbusType_SysNick'])) {
				$this->PersonRegister_model->setMorbusType_SysNick($data['MorbusType_SysNick']);
			}
			if (isset($data['Diag_id'])) {
				$this->PersonRegister_model->setDiag_id($data['Diag_id']);
			}
			if (isset($data['MorbusProfDiag_id'])) {
				$this->PersonRegister_model->setMorbusProfDiag_id($data['MorbusProfDiag_id']);
			}
			if (isset($data['Morbus_confirmDate'])) {
				$this->PersonRegister_model->setMorbus_confirmDate($data['Morbus_confirmDate']);
			}
			if (isset($data['Morbus_EpidemCode'])) {
				$this->PersonRegister_model->setMorbus_EpidemCode($data['Morbus_EpidemCode']);
			}
			if (isset($data['ignoreCheckAnotherDiag'])) {
				$this->PersonRegister_model->setignoreCheckAnotherDiag($data['ignoreCheckAnotherDiag']);
			}
			if (isset($data['PersonRegister_Code'])) {
				$this->PersonRegister_model->setPersonRegister_Code($data['PersonRegister_Code']);
			}
			if (isset($data['PersonRegister_setDate'])) {
				$this->PersonRegister_model->setPersonRegister_setDate($data['PersonRegister_setDate']);
			}
			if (isset($data['PersonRegister_disDate'])) {
				$this->PersonRegister_model->setPersonRegister_disDate($data['PersonRegister_disDate']);
			}
			if (isset($data['Morbus_id'])) {
				$this->PersonRegister_model->setMorbus_id($data['Morbus_id']);
			}
			if (isset($data['PersonRegisterOutCause_id'])) {
				$this->PersonRegister_model->setPersonRegisterOutCause_id($data['PersonRegisterOutCause_id']);
			}
			if (isset($data['PersonRegister_Alcoholemia'])) {
				$this->PersonRegister_model->setPersonRegister_Alcoholemia($data['PersonRegister_Alcoholemia']);
			}
			if (isset($data['MedPersonal_iid'])) {
				$this->PersonRegister_model->setMedPersonal_iid($data['MedPersonal_iid']);
			}
			if (isset($data['Lpu_iid'])) {
				$this->PersonRegister_model->setLpu_iid($data['Lpu_iid']);
			}
			if (isset($data['MedPersonal_did'])) {
				$this->PersonRegister_model->setMedPersonal_did($data['MedPersonal_did']);
			}
			if (isset($data['Lpu_did'])) {
				$this->PersonRegister_model->setLpu_did($data['Lpu_did']);
			}
			if (isset($data['EvnNotifyBase_id'])) {
				$this->PersonRegister_model->setEvnNotifyBase_id($data['EvnNotifyBase_id']);
			}
			if (!empty($data['autoExcept'])) {
				$this->PersonRegister_model->setAutoExcept(true);
			}
			$this->PersonRegister_model->setSessionParams($data['session']);
			$response = $this->PersonRegister_model->save();
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении регистра')->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Description
	 */
	function load() {
		$data = $this->ProcessInputData('load', true);
		if ($data){
			$this->PersonRegister_model->setPersonRegister_id($data['PersonRegister_id']);
			$response = $this->PersonRegister_model->load();
			$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
			return true;
		} else {
			return false;
		}
	}
		
	/**
	 * Description
	 */
	function loadList() {
		$data = $this->ProcessInputData('loadList', true);
		if ($data) {
			$filter = $data;
			$response = $this->PersonRegister_model->loadList($filter);
			//var_dump($response);die;
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
		
	/**
	 * Запись причины невключения в регистр
	 */
	function notinclude()
	{
		if (empty($_POST['PersonRegisterType_SysNick'])) {
			// по старому
			$data = $this->ProcessInputData('notinclude', true, true);
			if ($data) {
				$response = $this->PersonRegister_model->notinclude($data);
				$this->ProcessModelSave($response, true)->ReturnData();
				return true;
			} else {
				return false;
			}
		} else {
			// по новому
			/**
			 * @var EvnNotifyRegister_model $instance
			 */
			$instance = $this->loadEvnNotifyRegisterModel(1);
			if (empty($instance)) {
				return false;
			}
			$this->inputRules['notinclude'] = $instance->getInputRules('notInclude');
			if (empty($_POST['EvnNotifyRegister_id']) && isset($_POST['EvnNotifyBase_id'])) {
				$_POST['EvnNotifyRegister_id'] = $_POST['EvnNotifyBase_id'];
			}
			$data = $this->ProcessInputData('notinclude', true, true);
			if ($data) {
				// из-за того, что в $data добавляется много лишнего в getSessionParams()
				// приходится его чистить, чтобы оно не затирало данные
				$response = $instance->doSave(array(
					'scenario' => 'notInclude',
					'EvnNotifyRegister_id' => $data['EvnNotifyBase_id'],
					'PersonRegisterFailIncludeCause_id' => $data['PersonRegisterFailIncludeCause_id'],
					'Lpu_niid' => $data['Lpu_niid'],
					'MedPersonal_niid' => $data['MedPersonal_niid'],
					'session' => $data['session'],
				));
				$this->ProcessModelSave($response, true)->ReturnData();
				return true;
			} else {
				return false;
			}
		}
	}
	/**
	 *
	 * @return type 
	 */
	function loadEvnNotifyRegisterInclude(){
		$data = $this->ProcessInputData('loadEvnNotifyRegisterInclude', true);
		if ($data){
			$response = $this->PersonRegister_model->loadEvnNotifyRegisterInclude($data);
			$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	/**
	 * Исключение из регистра по старому
	 */
	function out() {
		$data = $this->ProcessInputData('out', true, true);
		if ($data) {
			$response = $this->PersonRegister_model->out($data);
			$this->ProcessModelSave($response, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Вернуть в регистр (после исключения из регистра по некоторым причинам)
	 * Вызывается при включении человека в регистр по извещению или при ручном добавлении в регистр
	 */
	function back()
	{

		if (empty($_POST['PersonRegisterType_SysNick'])) {
			// по старому
			$data = $this->ProcessInputData('back', true, true);
			if ($data) {
				$response = $this->PersonRegister_model->back($data);
				$this->ProcessModelSave($response, true)->ReturnData();
				return true;
			} else {
				return false;
			}
		} else {
			// по новому
			/**
			 * @var PersonRegisterBase_model $instance
			 */
			$instance = $this->loadModel();
			if (empty($instance)) {
				return false;
			}
			$this->inputRules['back'] = $instance->getInputRules('back');
			$data = $this->ProcessInputData('back', true, true);
			if ($data) {
				$response = $instance->doBack($data);
				$this->ProcessModelSave($response, true)->ReturnData();
				return true;
			} else {
				return false;
			}
		}
	}

	/**
	 * Создание объекта «Направление на включение в регистр»
	 */
	function createEvnNotifyRegisterInclude()
	{
		/**
		 * @var EvnNotifyRegister_model $instance
		 */
		$instance = $this->loadEvnNotifyRegisterModel(1);
		if (empty($instance)) {
			return false;
		}
		$this->inputRules['createEvnNotifyRegisterInclude'] = $instance->getInputRules(swModel::SCENARIO_DO_SAVE);
		$data = $this->ProcessInputData('createEvnNotifyRegisterInclude', true, true);
		if ($data) {
			if(
				!empty($_POST['PersonRegisterType_SysNick']) 
				&& in_array($_POST['PersonRegisterType_SysNick'],array('orphan','nolos'))
				&& empty($instance->sessionParams['lpu_id'])
				&& (!empty($data['Lpu_oid']) || !empty($data['Lpu_did']))
			) {
				if(!empty($data['Lpu_oid'])){
					$instance->Lpu_id = $data['Lpu_oid'];
				} else {
					$instance->Lpu_id = $data['Lpu_did'];
				}
			}
			$data['scenario'] = swModel::SCENARIO_DO_SAVE;
			$response = $instance->doSave($data);
			$this->ProcessModelSave($response, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Добавление в регистр оператором в поточном вводе
	 */
	function create()
	{
		/**
		 * @var PersonRegisterBase_model $instance
		 */
		$instance = $this->loadModel();
		if (empty($instance)) {
			return false;
		}
		$this->inputRules['create'] = $instance->getInputRules(swModel::SCENARIO_DO_SAVE);
		$data = $this->ProcessInputData('create', true, true);
		if ($data) {
			if(
				!empty($_POST['PersonRegisterType_SysNick']) 
				&& in_array($_POST['PersonRegisterType_SysNick'],array('orphan'))
				&& empty($instance->sessionParams['lpu_id'])
				&& (!empty($data['Lpu_iid']))
			) {
				$instance->Lpu_id = $data['Lpu_iid'];
			}
			$data['scenario'] = 'create';
			$response = $instance->doSave($data);
			$this->ProcessModelSave($response, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Исключение из регистра
	 */
	function doExcept()
	{
		/**
		 * @var PersonRegisterBase_model $instance
		 */
		$instance = $this->loadModel();
		if (empty($instance)) {
			return false;
		}
		$this->inputRules['except'] = $instance->getInputRules('except');
		$data = $this->ProcessInputData('except', true, true);
		if ($data) {
			$data['scenario'] = 'except';
			$response = $instance->doSave($data);
			//в Карелии одновременно с исключением из регистра ВЗН удаляем льготы, связанные с заболеванием; задача https://redmine.swan-it.ru/issues/138346
			if(getRegionNick() == 'kareliya'){
				$this->load->model('Privilege_model', 'dbmodel');
				$responseCloseVZNPrivilege = $this->dbmodel->closeVZNPrivilege($data);
				if(!empty($responseCloseVZNPrivilege)) {
					$response = array_merge($response, $responseCloseVZNPrivilege);
				}
			}

			$this->ProcessModelSave($response, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Включение в регистр по направлению/извещению
	 */
	function doInclude()
	{
		/**
		 * @var PersonRegisterBase_model $instance
		 */
		$instance = $this->loadModel();
		if (empty($instance)) {
			return false;
		}
		$this->inputRules['include'] = $instance->getInputRules('include');
		$data = $this->ProcessInputData('include', true, true);
		if ($data) {
			$data['scenario'] = 'include';
			$response = $instance->doSave($data);
			$this->ProcessModelSave($response, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Обновление какого-либо атрибута записи регистра
	 */
	function updateField()
	{
		/**
		 * @var PersonRegisterBase_model $instance
		 */
		$instance = $this->loadModel();
		if (empty($instance)) {
			return false;
		}
		$this->inputRules['updateField'] = $instance->getInputRules('updateField');
		$data = $this->ProcessInputData('updateField', true, true);
		if ($data) {
			// проверяем наличие метода у модели
			// имя метода должно быть в верблюжьем стиле!
			// и начинаться с update
			$parts = explode('_', $data['field_name']);
			$method = 'update';
			foreach($parts as $word) {
				$method .= ucfirst($word);
			}
			if (!method_exists($instance, $method)) {
				throw new Exception('Указанное поле нельзя изменить', 400);
			}
			// устанавливаем сценарий и параметры
			$instance->setScenario(swModel::SCENARIO_SET_ATTRIBUTE);
			$instance->setParams($data);
			// вызываем метод у модели
			$response = call_user_func(array($instance, $method), $data['PersonRegister_id'], $data['field_value']);
			$this->ProcessModelSave($response, true, 'При записи атрибута записи регистра поликлиники возникли ошибки');
			$this->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Печать направления/извещения
	 */
	function printEvnNotifyRegister()
	{
		if (empty($_REQUEST['NotifyType_id']) || !in_array($_REQUEST['NotifyType_id']+0, array(1,2,3))) {
			echo toUTF('Неправильный тип извещения');
			return false;
		}
		/**
		 * @var EvnNotifyRegister_model $instance
		 */
		$instance = $this->loadEvnNotifyRegisterModel($_REQUEST['NotifyType_id']);
		$this->inputRules['printEvnNotifyRegister'] = $instance->getInputRules('doLoadPrintForm');
		$data = $this->ProcessInputData('printEvnNotifyRegister', true, true);
		if ($data) {
			echo $instance->doLoadPrintForm($data);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Удаление направления/извещения
	 */
	function deleteEvnNotifyRegister()
	{
		if (empty($_POST['NotifyType_id']) || !in_array($_POST['NotifyType_id'], array(1,2,3))) {
			$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Неправильный тип извещения')));
			return false;
		}
		/**
		 * @var EvnNotifyRegister_model $instance
		 */
		$instance = $this->loadEvnNotifyRegisterModel($_POST['NotifyType_id']);
		$this->inputRules['deleteEvnNotifyRegister'] = $instance->getInputRules(swModel::SCENARIO_DELETE);
		$this->inputRules['deleteEvnNotifyRegister']['Lpu_did'] = array('field' => 'Lpu_did', 'label' => 'Идентификатор лпу', 'rules' => '', 'type' => 'id');
		$data = $this->ProcessInputData('deleteEvnNotifyRegister', true, true);
		if ($data) {
			if(
				!empty($_POST['PersonRegisterType_SysNick']) 
				&& in_array($_POST['PersonRegisterType_SysNick'],array('orphan','nolos'))
				&& empty($instance->sessionParams['lpu_id'])
				&& (!empty($data['Lpu_did']))
			) {
				$instance->Lpu_id = $data['Lpu_did'];
			}
			$response = $instance->doDelete($data);
			$this->ProcessModelSave($response, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получаем данные для проверки наличия «Направление на включение в регистр»
	 */
	function checkExistsEvnNotifyRegisterInclude()
	{
		/**
		 * @var EvnNotifyRegister_model $instance
		 */
		$instance = $this->loadEvnNotifyRegisterModel(1);
		if (empty($instance)) {
			return false;
		}
		$this->inputRules['checkExistsEvnNotifyRegisterInclude'] = $instance->getInputRules('loadDataCheckExists');
		$data = $this->ProcessInputData('checkExistsEvnNotifyRegisterInclude', true, true);
		if ($data) {
			$response = $instance->checkExistsEvnNotifyRegisterInclude($data['Person_id'], $data['Diag_id']);
			$this->ProcessModelSave($response, true, 'Возникла ошибка проверки наличия Направления на включение в регистр')->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Возвращает экземпляр записи регистра в зависимости от её типа
	 * @return bool|PersonRegisterBase_model
	 */
	private function loadModel()
	{
		if (empty($_REQUEST['PersonRegisterType_SysNick'])) {
			$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Не указан тип регистра')));
			return false;
		}
		$this->load->library('swPersonRegister');
		if (false == swPersonRegister::isAllow($_REQUEST['PersonRegisterType_SysNick'])) {
			$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Работа с этим типом регистра не доступна')));
			return false;
		}
		$instanceModelName = swPersonRegister::getModelName($_REQUEST['PersonRegisterType_SysNick']);
		$this->load->model($instanceModelName);
		// могут быть региональные модели типа ufa_PersonRegisterNolos_model, наследующие PersonRegisterNolos_model
		$className = get_class($this->{$instanceModelName});
		return new $className($_REQUEST['PersonRegisterType_SysNick']);
	}

	/**
	 * Возвращает экземпляр извещения/направления в зависимости от типа записи регистра
	 * @param $type
	 * @return bool|EvnNotifyRegister_model
	 */
	private function loadEvnNotifyRegisterModel($type)
	{
		if (empty($_REQUEST['PersonRegisterType_SysNick'])) {
			$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Не указан тип регистра')));
			return false;
		}
		$this->load->library('swPersonRegister');
		if (false == swPersonRegister::isAllow($_REQUEST['PersonRegisterType_SysNick'])) {
			$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Работа с этим типом регистра не доступна')));
			return false;
		}
		$instanceModelName = swPersonRegister::getEvnNotifyRegisterModelName($_REQUEST['PersonRegisterType_SysNick']);
		$this->load->model($instanceModelName);
		// могут быть региональные модели типа ufa_EvnNotifyRegister_model, наследующие EvnNotifyRegister_model
		$className = get_class($this->{$instanceModelName});
		return new $className($_REQUEST['PersonRegisterType_SysNick'], $type);
	}


	/**
	 * Загружаем список протоколов ВК для направлений/извещений
	 */
	function loadEvnVKList()
	{
		$this->load->model('PersonRegisterNolos_model');
		$this->inputRules['loadEvnVKList'] = $this->PersonRegisterNolos_model->getInputRules('loadEvnVKList');
		$data = $this->ProcessInputData('loadEvnVKList', true, true);
		if ($data) {
			$response = $this->PersonRegisterNolos_model->loadEvnVKList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Помечает объект «Запись регистра» на удаление
	 */
	function delete()
	{
		if (empty($_POST['PersonRegisterType_SysNick'])) {
			// по старому
			$data = $this->ProcessInputData('delete', true, true);
			if ($data) {
				$response = $this->PersonRegister_model->doDelete($data);
				$this->ProcessModelSave($response, true)->ReturnData();
				return true;
			} else {
				return false;
			}
		} else {
			// по новому
			/**
			 * @var PersonRegisterBase_model $instance
			 */
			$instance = $this->loadModel();
			if (empty($instance)) {
				return false;
			}
			$this->inputRules['delete'] = $instance->getInputRules(swModel::SCENARIO_DELETE);
			$data = $this->ProcessInputData('delete', true, true);
			if ($data) {
				$response = $instance->doDelete($data);
				$this->ProcessModelSave($response, true)->ReturnData();
				return true;
			} else {
				return false;
			}
		}
	}

	/**
	 * Выгрузка в федеральный регистр регионального сегмента регистра
	 */
	function export()
	{
		/**
		 * @var PersonRegisterBase_model $instance
		 */
		$instance = $this->loadModel();
		if (empty($instance)) {
			return false;
		}
		$this->inputRules['export'] = $instance->getInputRules('export');
		$data = $this->ProcessInputData('export', true, true);
		if (false == $data) { return false; }
		$response = $instance->doExport($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}

	/**
	 * Получение списка видов регистров людей
	 */
	function loadPersonRegisterTypeGrid() {
		$data = $this->ProcessInputData('loadPersonRegisterTypeGrid', true);
		if ($data === false) { return false; }

		$response = $this->PersonRegister_model->loadPersonRegisterTypeGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Получение истории изменения данных в регистре
	 */
	function loadPersonRegisterHistList() {
		$data = $this->ProcessInputData('loadPersonRegisterHistList', true);
		if ($data === false) { return false; }

		$response = $this->PersonRegister_model->loadPersonRegisterHistList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Получение последених данных из истории изменения регистра для редактирования
	 */
	function loadPersonRegisterHistForm() {
		$data = $this->ProcessInputData('loadPersonRegisterHistForm', true);
		if ($data === false) { return false; }

		$response = $this->PersonRegister_model->loadPersonRegisterHistForm($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Создание записи в истории изменения данных регистра
	 */
	function createPersonRegisterHist() {
		$data = $this->ProcessInputData('createPersonRegisterHist', false);
		if ($data === false) { return false; }

		$session_data = getSessionParams();
		$data['session'] = $session_data['session'];
		$data['pmUser_id'] = $session_data['pmUser_id'];

		$response = $this->PersonRegister_model->createPersonRegisterHist($data);
		$this->ProcessModelSave($response)->ReturnData();

		return true;
	}

	/**
	 *  Импорт данных регистра ВЗН из xls файла.
	function importVznRegistryFromXls() {
		$data = array();

		$root_dir = IMPORTPATH_ROOT;
		if( !is_dir($root_dir) ) {
			if( !mkdir($root_dir) ) {
				return $this->ReturnError('Не удалось создать директорию для хранения загружаемых данных!');
			}
		}

		if( !isset($_FILES['uploadfilefield']) ) {
			return $this->ReturnError('Ошибка! Отсутствует файл! (поле uploadfilefield)');
		}

		$file = $_FILES['uploadfilefield'];
		if( $file['error'] > 0 ) {
			return $this->ReturnError('Ошибка при загрузке файла!', $file['error']);
		}

		//вычисляем расширение из названия файла
		$ext = explode('.', $file['name']);
		if (count($ext) > 0) {
			$ext = strtolower($ext[count($ext)-1]);
		} else {
			$ext = null;
		}
		if( $ext != 'xls' ) {
			return $this->ReturnError('Необходим файл с расширением xls.');
		}

		$fileFullName = $root_dir.$file['name'];
		if( is_file($file['tmp_name']) ) {
			$fileFullName = $root_dir.time().'_'.$file['name'];
		}

		if( !rename($file['tmp_name'], $fileFullName) ) {
			return $this->ReturnError('Не удалось создать файл ' . $fileFullName);
		}
		$data['FileFullName'] = $fileFullName;

		$response = $this->PersonRegister_model->importVznRegistryFromXls($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		unlink($fileFullName);
		return true;
	}
	*/

	/**
	 * Метод выгружает регистр ВЗН в виде файла CSV
	 * Без параметров
	 */
	function downloadVznRegisterCsv()
	{
		// Выгрузка регистра разрешена только следующим АРМам
		$allowedArms = array('minzdravdlo','adminllo','spec_mz');

		// Берем текущий АРМ из сессии
		$arm = $_SESSION['CurArmType'];

		// Если АРМА нет в списке, то прекращаем
		if ( ! in_array($arm, $allowedArms) )
		{
			return false;
		}


		$this->load->model('PersonRegister_model', 'PersonRegister_model');

		return $this->PersonRegister_model->downloadVznRegisterCsv();

	}

	/**
	 * Простой вариант проверки наличия записи регистра
	 */
	function simpleCheckPersonRegisterExist() {
		$data = $this->ProcessInputData('simpleCheckPersonRegisterExist', false);
		if ($data === false) { return false; }
		
		$data['PersonRegister_setDate'] = $data['PersonRegister_setDate'] ?? date('Y-m-d');

		$response = $this->PersonRegister_model->simpleCheckPersonRegisterExist($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}
}