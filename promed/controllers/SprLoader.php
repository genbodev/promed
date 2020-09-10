<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * SprLoader - проверка и загрузка справочников при входе в Промед
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2009 Swan Ltd.
 * @author           Stas Bykov aka Savage (savage1981@gmail.com)
 * @version			?
 */
class SprLoader extends swController {
	/**
	 * SprLoader constructor.
	 */
	function __construct() {
		parent::__construct();
		$this->inputRules = array(
			'getSyncTables' => array(
				array(
					'field' => 'version',
					'label' => 'Версия локальной базы',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'mode',
					'label' => 'Модуль',
					'rules' => '',
					'type' => 'string',
					'default' => 'promed'
				)
			),
			'createNewVersionLocalDB' => array(
				array(
					'field' => 'mode',
					'label' => 'Модуль',
					'rules' => '',
					'type' => 'string',
					'default' => 'promed'
				)
			)
		);
	}

	/**
	 * @return bool
	 */
	function index() {
		return false;
	}

	/**
	 * Some function
	 */
	function getPromedSprSyncTable() {
		$this->getSprSyncTable("promed");
	}

	/**
	 * Some function
	 */
	function getFarmacySprSyncTable() {
		$this->getSprSyncTable("farmacy");
	}
	
	/**
	 * Получение синхронизационной таблицы
	 */
	function getSprSyncTable($mode) {
		$this->load->database();
		$this->load->model('SprLoader_model', 'sprmodel');
		
		$data = $this->ProcessInputData(NULL, true, true);
		$response = $this->sprmodel->getSprSyncTable($mode);
		$this->ProcessModelList($response)->ReturnData();
		return true;
	}

	/**
	 * Создание новой версии локальной базы данных для разных модулей (promed|farmacy)
	 */
	function createNewVersionLocalDB() {
		$this->load->database();
		$this->load->model('SprLoader_model', 'sprmodel');
		$data = $this->ProcessInputData('createNewVersionLocalDB', true, true);
		$response = $this->sprmodel->createNewVersionLocalDB($data);
		$this->ProcessModelList($response)->ReturnData();
		return true;
	}
	
	/**
	 * Получение таблиц для синхронизации в данной версии
	 */
	function getSyncTables() {
		$this->load->database();
		$this->load->model('SprLoader_model', 'sprmodel');
		$data = $this->ProcessInputData('getSyncTables', true, true);
		$response = $this->sprmodel->getSyncTables($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
}
?>