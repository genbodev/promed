<?php	defined('BASEPATH') or die ('No direct script access allowed');

class NoticeModeSettings extends swController {
	public $inputRules = [
		'loadNoticeModeSettingsGrid' => [],
		'checkLpuSettingsExist' => [
			['field' => 'Lpu_sid', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id']
		],
		'checkNotifySettingsExist' => [
			['field' => 'Lpu_sid', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id'],
			['field' => 'NoticeModesType_id', 'label' => 'Режим', 'rules' => 'required', 'type' => 'id'],
			['field' => 'NoticeModeLink_Frequency', 'label' => 'Частота', 'rules' => 'required', 'type' => 'int'],
			['field' => 'NoticeFreqUnitsType_id', 'label' => 'Единицы измерения', 'rules' => 'required', 'type' => 'id']
		],
		'loadNoticeModeSettingsForm' => [
			['field' => 'NoticeModeSettings_id', 'label' => 'Идентификатор настроек', 'rules' => 'required', 'type' => 'id']
		],
		'loadNoticeModeLinkGrid' => [
			['field' => 'NoticeModeSettings_id', 'label' => 'Идентификатор настроек', 'rules' => 'required', 'type' => 'id']
		],
		'loadNoticeModeLinkForm' => [
			['field' => 'NoticeModeLink_id', 'label' => 'Идентификатор режима уведомлений', 'rules' => 'required', 'type' => 'id']
		],
		'saveNoticeModeSettings' => [
			['field' => 'Lpu_sid', 'label' => 'Идентификатор ЛПУ', 'rules' => '', 'type' => 'id'],
			['field' => 'NoticeModeSettings_id', 'label' => 'Идентификатор настроек', 'rules' => '', 'type' => 'id'],
			['field' => 'NoticeModeSettings_IsSMS', 'label' => 'Флаг СМС','rules' => '', 'type' => 'boolean'],
			['field' => 'NoticeModeSettings_IsEmail', 'label' => 'Флаг Email', 'rules' => '', 'type' => 'boolean']
		],
		'saveNoticeModeLink' => [
			['field' => 'NoticeModeLink_id', 'label' => 'Идентификатор режима уведомлений', 'rules' => '', 'type' => 'id'],
			['field' => 'NoticeModeSettings_id', 'label' => 'Идентификатор настроек', 'rules' => 'required', 'type' => 'id'],
			['field' => 'NoticeModesType_id', 'label' => 'Режим', 'rules' => '', 'type' => 'id'],
			['field' => 'NoticeModeLink_Frequency', 'label' => 'Частота', 'rules' => '', 'type' => 'string'],
			['field' => 'NoticeFreqUnitsType_id', 'label' => 'Единицы измерения', 'rules' => '', 'type' => 'id']
		],
		'deleteNoticeModeSettings' => [
			['field' => 'NoticeModeSettings_id', 'label' => 'Идентификатор настроек', 'rules' => 'required', 'type' => 'id']
		]
	];
	
	/**
	 * constructor.
	 */
	function __construct() {
		parent::__construct();
		
		$this->load->database();
		$this->load->model('NoticeModeSettings_model', 'dbmodel');
	}
	
	
	/**
	 *  Загрузка данных формы
	 */
	function loadNoticeModeSettingsForm() {
		$data = $this->ProcessInputData('loadNoticeModeSettingsForm', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->loadNoticeModeSettingsForm($data);
		$this->ProcessModelList($response)->ReturnData();
		
		return true;
	}
	
	/**
	 *  Загрузка данных формы
	 */
	function loadNoticeModeLinkForm() {
		$data = $this->ProcessInputData('loadNoticeModeLinkForm', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->loadNoticeModeLinkForm($data);
		$this->ProcessModelList($response)->ReturnData();
		
		return true;
	}
	
	/**
	 *  Сохранение режима уведомлений
	 */
	function saveNoticeModeSettings() {
		$data = $this->ProcessInputData('saveNoticeModeSettings', true);
		//echo '<pre>',print_r($data),'</pre>'; die();
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->saveNoticeModeSettings($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		
		return true;
	}
	
	/**
	 *  Сохранение режима уведомлений
	 */
	function saveNoticeModeLink() {
		$data = $this->ProcessInputData('saveNoticeModeLink', true);
		//echo '<pre>',print_r($data),'</pre>'; die();
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->saveNoticeModeLink($data);
		
		$this->ProcessModelSave($response, true)->ReturnData();
		
		return true;
	}
	
	/**
	 *  * Получение списка режима уведомлений
	 */
	function loadNoticeModeSettingsGrid() {
		$data = $this->ProcessInputData('loadNoticeModeSettingsGrid', false);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->loadNoticeModeSettingsGrid($data);
		
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
	
	/**
	 *  * Получение списка режима уведомлений
	 */
	function loadNoticeModeLinkGrid() {
		$data = $this->ProcessInputData('loadNoticeModeLinkGrid', false);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->loadNoticeModeLinkGrid($data);
		
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Проверяем есть ли в базе уведомлений данное МО
	 */
	function checkLpuSettingsExist() {
		$data = $this->ProcessInputData('checkLpuSettingsExist', false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->checkLpuSettingsExist($data);

		$this->ProcessModelSave($response, true, true)->ReturnData();
		return true;
	}
	
	/**
	 * Проверяем есть ли такие же настройки уведомлений для МО
	 */
	function checkNotifySettingsExist() {
		$data = $this->ProcessInputData('checkNotifySettingsExist', false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->checkNotifySettingsExist($data);

		$this->ProcessModelSave($response, true, true)->ReturnData();
		return true;
	}
}
