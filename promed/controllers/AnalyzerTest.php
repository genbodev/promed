<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Контроллер для объектов Тесты анализаторов
 *
 * @package	  Common
 * @access	   public
 * @copyright	Copyright (c) 2011 Swan Ltd.
 * @author	   gabdushev
 * @version
 * @property AnalyzerTest_model AnalyzerTest_model
 */
class AnalyzerTest extends swController
{
	/**
	 * Конструктор
	 */
	function __construct(){
		parent::__construct();
		$this->inputRules = array(
			'deleteUslugaComplexMedServiceDouble' => array(
				array(
					'field' => 'Lpu_id',
					'label' => 'МО',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'MedService_id',
					'label' => 'Служба',
					'rules' => '',
					'type' => 'id'
				)
			),
			'fixAnalyzerTest' => array(
				array(
					'field' => 'Lpu_id',
					'label' => 'МО',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedService_id',
					'label' => 'Служба',
					'rules' => '',
					'type' => 'id'
				)
			),
			'getSysNickForAnalyzerTest' => array(
				array(
					'field' => 'Analyzer_id',
					'label' => 'Анализатор',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'UslugaComplex_id',
					'label' => 'Услуга',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'linkUslugaComplexMedService' => array(
				array(
					'field' => 'Analyzer_id',
					'label' => 'Анализатор',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'MedService_id',
					'label' => 'Идентификатор службы',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'UslugaComplexMedService_ids',
					'label' => 'Список услуг',
					'rules' => 'required',
					'type' => 'string'
				)
			),
			'getAnalyzerTestReagent' => array(
				array(
					'field' => 'Analyzer_id',
					'label' => 'Анализатор',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'UslugaComplex_Code',
					'label' => 'Код услуги',
					'rules' => 'trim',
					'type' => 'string'
				)
			),
			'saveAnalyzerTestNotActive' => array(
				array(
					'field' => 'AnalyzerTest_id',
					'label' => 'Идентификатор теста',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'AnalyzerTest_IsNotActive',
					'label' => 'Неактивный',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'getUnlinkedUslugaComplexMedServiceCount' => array(
				array(
					'field' => 'MedService_id',
					'label' => 'Идентификатор службы',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'getUnlinkedUslugaComplexMedServiceGrid' => array(
				array(
					'field' => 'MedService_id',
					'label' => 'Идентификатор службы',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'UslugaComplexMedService_pid',
					'label' => 'Идентификатор родительской услуги',
					'rules' => '',
					'type' => 'id'
				)
			),
			'save' => array(
				array(
					'field' => 'AnalyzerTest_id',
					'label' => '',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'AnalyzerTest_Name',
					'label' => 'Наименование',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'AnalyzerTest_SysNick',
					'label' => 'Мнемоника',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'AnalyzerTest_pid',
					'label' => 'Родительский тест',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'AnalyzerModel_id',
					'label' => 'Модель анализатора',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Analyzer_id',
					'label' => 'Анализатора',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'AnalyzerTest_isTest',
					'label' => 'Признак теста',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'UslugaComplex_id',
					'label' => 'Услуга теста',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'postUslugaComplex_id',
					'label' => 'услуга теста',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'UslugaComplexMedService_id',
					'label' => 'Связь с услугой службы',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'AnalyzerTest_begDT',
					'label' => 'Дата начала',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'AnalyzerTest_endDT',
					'label' => 'Дата окончания',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'AnalyzerTest_SortCode',
					'label' => 'Приоритет',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'AnalyzerTestType_id',
					'label' => 'Тип теста',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Unit_id',
					'label' => 'Единица измерения',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'ReagentNormRate_id',
					'label' => 'Запись Расхода реактива',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'UslugaComplex_Code',
					'label' => 'Код услуги',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'LabTest_id',
					'label' => 'Код теста НСИ',
					'rules' => '',
					'type' => 'id'
				)
			),
			'load' => array(
				array(
					'field' => 'AnalyzerTest_id',
					'label' => '',
					'rules' => 'required',
					'type' => 'int'
				),
			),
			'loadAnalyzerTestGrid' => array(
				array(
					'field' => 'AnalyzerTest_id',
					'label' => '',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'AnalyzerTest_pid',
					'label' => 'Родительский тест',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'AnalyzerModel_id',
					'label' => 'Модель анализатора',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Analyzer_id',
					'label' => 'Анализатор',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'UslugaComplex_id',
					'label' => 'Услуга теста',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedService_id',
					'label' => 'Служба',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'IsActive',
					'label' => 'Выбрать только активные',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'AnalyzerTestType_id',
					'label' => 'Тип теста',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Unit_id',
					'label' => 'Единица измерения',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'AnalyzerWorksheetType_id',
					'label' => '',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'start',
					'label' => 'Номер стартовой записи',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'limit',
					'label' => 'Количество записей',
					'rules' => '',
					'type' => 'id'
				)
			),
			'loadList' => array(
				array(
					'field' => 'AnalyzerTest_id',
					'label' => '',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'AnalyzerTest_pid',
					'label' => 'Родительский тест',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'AnalyzerModel_id',
					'label' => 'Модели анализаторов',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'AnalyzerTest_Code',
					'label' => '',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'AnalyzerTest_Name',
					'label' => '',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'AnalyzerTest_SysNick',
					'label' => '',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'AnalyzerTestType_id',
					'label' => 'Тип теста',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Unit_id',
					'label' => 'Единица измерения',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'AnalyzerTest_Deleted',
					'label' => '',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'AnalyzerWorksheetType_id',
					'label' => '',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'UslugaComplex_id',
					'label' => 'Услуга',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'MedService_id',
					'label' => 'Служба',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Analyzer_id',
					'label' => 'Анализатор',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'AnalyzerTest_isTest',
					'label' => 'Тест',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'mode',
					'label' => 'Режим работы формы',
					'rules' => '',
					'type' => 'string'
				)
			),
			'delete' => array(
				array(
					'field' => 'AnalyzerTest_id',
					'label' => '',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'Analyzer_id',
					'label' => '',
					'rules' => '',
					'type'  => 'int'
				),
				array(
					'field' => 'AnalyzerTest_pid',
					'label' => '',
					'rules' => '',
					'type' => 'int'
				),
			),
			'checkAnalyzerTestBegDate' => array(
				array(
					'field' => 'AnalyzerTest_id',
					'label' => '',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'AnalyzerTest_begDT',
					'label' => '',
					'rules' => '',
					'type' => 'string'
				)
			),
			'checkAnalyzerTestIsExists' => array(
				array(
					'field' => 'Analyzer_id',
					'label' => 'Анализатор',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'AnalyzerModel_id',
					'label' => 'Модель анализатора',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'AnalyzerTest_pid',
					'label' => 'Исследование',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'UslugaComplex_id',
					'label' => 'услуга',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadLabTestList' => array(
				array(
					'field' => 'UslugaComplex_id',
					'label' => 'Услуга',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'query',
					'label' => 'Маска поиска',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'relateLabTest',
					'label' => 'Наличие связи',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'LabTest_id',
					'label' => 'Идентификатор несвязанного теста',
					'rules' => '',
					'type' => 'id'
				)
			),
			'loadAnalyzerTestType' => array(
				array(
					'field' => 'Analyzer_id',
					'label' => 'Идентификатор анализатора',
					'rules' => '',
					'type' => 'id'
				)
			)
		);
		$this->load->database();
		$this->load->model('AnalyzerTest_model', 'AnalyzerTest_model');
	}

	/**
	 *	Связь услуг с анализатором
	 */
	function linkUslugaComplexMedService() {
		$data = $this->ProcessInputData('linkUslugaComplexMedService', true);
		if ($data === false) { return false; }

		$response = $this->AnalyzerTest_model->linkUslugaComplexMedService($data);
		$this->ReturnData(array('success' => true));

		return true;
	}

	/**
	 *	Получение реагента, привязанного к тесту анализатора
	 */
	function getAnalyzerTestReagent() {
		$data = $this->ProcessInputData('getAnalyzerTestReagent', true);
		if ($data) {
			//$response = $this->dbmodel->getEvnLabSample($data);
			$response = $this->AnalyzerTest_model->getAnalyzerTestReagent($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *	Сохранение признака неактивности теста анализатора
	 */
	function saveAnalyzerTestNotActive() {
		$data = $this->ProcessInputData('saveAnalyzerTestNotActive', true);
		if ($data === false) { return false; }

		$response = $this->AnalyzerTest_model->saveAnalyzerTestNotActive($data);
		$this->ReturnData(array('success' => true));

		return true;
	}

	/**
	 * Сохранение
	 */
	function save() {
		$data = $this->ProcessInputData('save', true);
		if ($data === false) { return false; }
		if ( !empty($data['postUslugaComplex_id']) ) {
			$data['UslugaComplex_id'] = $data['postUslugaComplex_id'];
		}

		$response = $this->AnalyzerTest_model->save($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении Тесты анализаторов')->ReturnData();
		return true;
	}

	/**
	 * Получение мнемоники
	 */
	function getSysNickForAnalyzerTest() {
		$data = $this->ProcessInputData('getSysNickForAnalyzerTest', true);
		if ($data === false) { return false; }

		$response = $this->AnalyzerTest_model->getSysNickForAnalyzerTest($data);
		$this->ProcessModelSave($response, true, 'Ошибка при получении мнемоники теста')->ReturnData();

		return true;
	}

	/**
	 * Фикс тестов анализаторов
	 */
	function fixAnalyzerTest() {
		$data = $this->ProcessInputData('fixAnalyzerTest', true);
		if ($data === false) { return false; }

		$response = $this->AnalyzerTest_model->fixAnalyzerTest($data);
		$this->ProcessModelSave($response, true, 'Ошибка при исправлении тестов анализаторов')->ReturnData();

		return true;
	}

	/**
	 * Удаление дублей UslugaComplexMedService
	 */
	function deleteUslugaComplexMedServiceDouble() {
		$data = $this->ProcessInputData('deleteUslugaComplexMedServiceDouble', true);
		if ($data === false) { return false; }

		$response = $this->AnalyzerTest_model->deleteUslugaComplexMedServiceDouble($data);
		$this->ProcessModelSave($response, true, 'Ошибка при исправлении тестов анализаторов')->ReturnData();

		return true;
	}

	/**
	 * Загрузка
	 */
	function load() {
		$data = $this->ProcessInputData('load', true);
		if ($data){
			$response = $this->AnalyzerTest_model->load($data);
			$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение количества несвязанных услуг на службе с анализаторами
	 */
	function getUnlinkedUslugaComplexMedServiceCount() {
		$data = $this->ProcessInputData('getUnlinkedUslugaComplexMedServiceCount', true);
		if ($data){
			$response = $this->AnalyzerTest_model->getUnlinkedUslugaComplexMedServiceCount($data);
			$this->ProcessModelSave($response, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение несвязанных услуг на службе с анализаторами
	 */
	function getUnlinkedUslugaComplexMedServiceGrid() {
		$data = $this->ProcessInputData('getUnlinkedUslugaComplexMedServiceGrid', true);
		if ($data){
			$response = $this->AnalyzerTest_model->getUnlinkedUslugaComplexMedServiceGrid($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка
	 */
	function loadAnalyzerTestGrid() {
		$data = $this->ProcessInputData('loadAnalyzerTestGrid', true);
		if ($data) {
			$response = $this->AnalyzerTest_model->loadAnalyzerTestGrid($data);
			$this->ProcessModelMultiList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Удаление
	 */
	function delete() {
		$data = $this->ProcessInputData('delete', true, true);
		if ($data) {

			// удаление формул, если тест расчетный
			if(!empty($data['Analyzer_id']) && !empty($data['AnalyzerTest_pid']))
			{
				$this->load->model('Ufa_AnalyzerTestFormula_model');
				$this->Ufa_AnalyzerTestFormula_model->AnalyzerTestFormulaAll_del($data);
			}

			$response = $this->AnalyzerTest_model->delete($data);
			$this->ProcessModelSave($response, true, $response)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка тестов
	 */
	function loadList() {
		$data = $this->ProcessInputData('loadList', true);
		if ($data) {
			$filter = $data;
			$response = $this->AnalyzerTest_model->loadList($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Проверка даты начала теста
	 */
	function checkAnalyzerTestBegDate() {
		$data = $this->ProcessInputData('checkAnalyzerTestBegDate', true, true);
		if ($data) {
			$response = $this->AnalyzerTest_model->checkAnalyzerTestBegDate($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Проверка теста на дубликат в списке тестов для модели или для экземпляра анализатора
	 */
	function checkAnalyzerTestIsExists() {
		$data = $this->ProcessInputData('checkAnalyzerTestIsExists', true, true);
		if ($data) {
			$response = $this->AnalyzerTest_model->checkAnalyzerTestIsExists($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	/**
	 *	Получение списка тестов НСИ, связанных и несвязанных с выбранной услугой
	 */
	function loadLabTestList()
	{
		$data = $this->ProcessInputData('loadLabTestList', true);
		if ($data) {
			// А.И.Г. 25.02.2020 Поддержка #194077
			if (empty($data['UslugaComplex_id'])) {
				return $this->ReturnData(array(
					'success' => false,
					'Error_Msg' => "Не указана услуга!"
				));
			};
			//основной запрос
			if (empty($data['relateLabTest']) || $data['relateLabTest'] == "true") {
				$response = $this->AnalyzerTest_model->loadLabTestList($data);
				$this->ProcessModelList($response, true, true)->ReturnData();
				return true;
			};

			if (!empty($data['relateLabTest']) || $data['relateLabTest'] == "false") {
				$response = $this->AnalyzerTest_model->loadLabTestNoList($data);
				$this->ProcessModelList($response, true, true)->ReturnData();
				return true;
			}
		} else {
			return false;
		}
	}

	function loadAnalyzerTestType () {
		$data = $this->ProcessInputData('loadAnalyzerTestType', false);
		if ($data === false) { return false; }

		$response = $this->AnalyzerTest_model->loadAnalyzerTestType($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;

	}

	/**
	 * Формирование элементов дерева из записей таблицы
	 */
	function getTreeNodes($nodes) {
		$val = [];
		$nodes = $nodes['data'];

		if ( is_array($nodes) && count($nodes) > 0 ) {
			foreach ( $nodes as $rows ) {
				$node = array(
					'id' => $rows['AnalyzerTest_id'],
					'object' => 'AnalyzerTest',
					'object_id' => 'AnalyzerTest_id',
					'object_value' => $rows['AnalyzerTest_id'],
					'object_code' => $rows['AnalyzerTest_Code'],
					'UslugaComplex_id' => $rows['UslugaComplex_id'],
					'text' => $rows['AnalyzerTest_Code'] .' '. $rows['AnalyzerTest_Name'],
					'leaf' => ($rows['AnalyzerTest_isTest'] == 2),
				);

				$val[] = $node;
			}
		}

		return $val;
	}

	/**
	 * Получение дерева исследований - тестов
	 */
	function loadAnalyzerTestTree() {
		$data = $this->ProcessInputData('loadAnalyzerTestGrid', true);
		if ($data === false) { return false; }

		$response = $this->AnalyzerTest_model->loadAnalyzerTestGrid($data);

		$this->ProcessModelMultiList($response, true, true);
		$this->ReturnData($this->getTreeNodes($this->OutData));

		return true;
	}
}
