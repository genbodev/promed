<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * TestStat - Контроллер статистики принятых тестов из АСМЛО
 * 
 * @package	  common
 * @access	  public
 * @author	  Arslanov Azat
 */
class TestStat extends swController {
	/**
	 * конструктор
	 */
	function __construct(){
		parent::__construct();
		$this->inputRules = array(
			'saveTestStat' => array(
				array(
					'field' => 'TestStat_id',
					'label' => 'Запись таблицы статистики принятых тестов',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'TestStat_labCode',
					'label' => 'Код лаборатории',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'TestStat_analyzerCode',
					'label' => 'Код анализатора',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'TestStat_testDate',
					'label' => 'Дата выполнения теста',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'TestStat_testCode',
					'label' => 'Код теста',
					'rules' => 'required',
					'type' => 'string'
				),
			),
		);
		$this->load->database();
		$this->load->model('TestStat_model', 'dbmodel');
	}

	/**
	 *  Сохранение Записи таблицы статистики принятых тестов
	 */
	function saveTestStat() {
		$data = $this->ProcessInputData('saveTestStat', true);
		if ($data){
			if (isset($data['TestStat_id'])) {
				$this->dbmodel->setTestStat_id($data['TestStat_id']);
			}
			if (isset($data['TestStat_labCode'])) {
				$this->dbmodel->setLabCode($data['TestStat_labCode']);
			}
			if (isset($data['TestStat_analyzerCode'])) {
				$this->dbmodel->setAnalyzerCode($data['TestStat_analyzerCode']);
			}
			if (isset($data['TestStat_testDate'])) {
				$this->dbmodel->setTestDate($data['TestStat_testDate']);
			}
			if (isset($data['TestStat_testCode'])) {
				$this->dbmodel->setTestCode($data['TestStat_testCode']);
			}
			//if (isset($data['TestStat_testCount'])) {
			//	$this->dbmodel->setTestCount($data['TestStat_testCount']);
			//}

			$response = $this->dbmodel->saveTestStat();
			$this->ProcessModelSave($response, true, 'Ошибка при сохранении в таблице статистики принятых тестов')->ReturnData();
			return true;
		} else {
			return false;
		}
	}
}
