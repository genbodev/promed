<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * TODO: complete explanation, preamble and describing
 * Контроллер для объектов Анализатор
 *
 * @package	  Common
 * @access	   public
 * @copyright	Copyright (c) 2011 Swan Ltd.
 * @author	   gabdushev
 * @version
 * @property Analyzer_model Analyzer_model
 */

class Analyzer extends swController
{
	/**
	 *	Конструктор контроллера Analyzer
	 */
	function __construct(){
		parent::__construct();
		$this->inputRules = array(
			'saveAnalyzerField' => array(
				array(
					'field' => 'Analyzer_id',
					'label' => 'Идентификатор анализатора',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Analyzer_IsNotActive',
					'label' => 'Неактивный',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Analyzer_2wayComm',
					'label' => 'Использование двусторонней связи',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Analyzer_IsUseAutoReg',
					'label' => 'Использование автоматического учёта',
					'rules' => '',
					'type' => 'int'
				)
			),
			'saveUslugaFromModelToAnalyzer' => array(
				array(
					'field' => 'Analyzer_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'AnalyzerTest_pid',
					'label' => 'Ид Исследования',
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
					'field' => 'AnalyzerModel_id',
					'label' => 'Модель анализатора',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'UslugaComplex_id',
					'label' => 'Ид Услуги',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'save' => array(
				array(
					'field' => 'Analyzer_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'Analyzer_Name',
					'label' => 'Наименование анализатора',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'Analyzer_Code',
					'label' => 'Код',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'AnalyzerModel_id',
					'label' => 'Модель анализатора',
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
					'field' => 'equipment_id',
					'label' => 'Анализатор ЛИС',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Test_JSON',
					'label' => 'Тесты анализатора ЛИС',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Analyzer_begDT',
					'label' => 'Дата открытия',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'Analyzer_endDT',
					'label' => 'Дата закрытия',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'Analyzer_LisClientId',
					'label' => 'Id клиента',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Analyzer_LisCompany',
					'label' => 'Наименование ЛПУ',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Analyzer_LisLab',
					'label' => 'Наименование лаборатории',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Analyzer_LisMachine',
					'label' => 'Название машины в ЛИС',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Analyzer_LisLogin',
					'label' => 'Логин в ЛИС',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Analyzer_LisPassword',
					'label' => 'Пароль',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Analyzer_LisNote',
					'label' => 'Примечание',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Analyzer_2wayComm',
					'label' => 'Использование двусторонней связи',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'Analyzer_IsManualTechnic',
					'label' => 'Ручные методики',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'Analyzer_IsUseAutoReg',
					'label' => 'Использование автоматического учета',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'Analyzer_IsNotActive',
					'label' => 'Неактивный',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'Analyzer_IsAutoOk',
					'label' => 'Автоодобрение',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'Analyzer_IsAutoGood',
					'label' => 'Автоодобрение без патологий',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'AutoOkType',
					'label' => 'Тип автоодобрения',
					'rules' => '',
					'type' => 'int'
				)
			),
			'load' => array(
				array(
					'field' => 'Analyzer_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'loadList' => array(
				array(
					'field' => 'Analyzer_id',
					'label' => 'Идентификатор',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Analyzer_Name',
					'label' => 'Наименование анализатора',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Analyzer_Code',
					'label' => 'Код',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'AnalyzerModel_id',
					'label' => 'Модель анализатора',
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
					'field' => 'Analyzer_begDT',
					'label' => 'Дата открытия',
					'rules' => '',
					'type' => 'datetime'
				),
				array(
					'field' => 'Analyzer_endDT',
					'label' => 'Дата закрытия',
					'rules' => '',
					'type' => 'datetime'
				),
				array(
					'field' => 'Analyzer_LisClientId',
					'label' => 'Id клиента',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Analyzer_LisCompany',
					'label' => 'Наименование ЛПУ',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Analyzer_LisLab',
					'label' => 'Наименование лаборатории',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Analyzer_LisMachine',
					'label' => 'Название машины в ЛИС',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Analyzer_LisLogin',
					'label' => 'Логин в ЛИС',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Analyzer_LisPassword',
					'label' => 'Пароль',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Analyzer_LisNote',
					'label' => 'Примечание',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Analyzer_2wayComm',
					'label' => 'Использование двусторонней связи',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Analyzer_IsUseAutoReg',
					'label' => 'Использование автоматического учета',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Analyzer_IsNotActive',
					'label' => 'Признак неактивности',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Analyzer_IsAutoOk',
					'label' => 'Автоодобрение',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'Analyzer_IsAutoGood',
					'label' => 'Автоодобрение без патологий',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'EvnLabSample_id',
					'label' => 'Проба',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnLabSamples',
					'label' => 'Пробы',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'hideRuchMetodiki',
					'label' => 'Скрыть ручные методики',
					'rules' => '',
					'type' => 'checkbox'
				)
			),
			'delete' => array(
				array(
					'field' => 'Analyzer_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'addUslugaComplexMedServiceFromTest' => array(
				array(
					'field' => 'UslugaComplexMedService_pid',
					'label' => 'Идентификатор родительской услуги на службе',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'test_id',
					'label' => 'Идентификатор теста',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'getAnalyzerCode' => array(
				array(
					'field' => 'MedService_id',
					'label' => 'Служба',
					'rules' => '',
					'type' => 'int'
				)
			),
			'checkIfFromExternalMedService' => [
				[
					'field' => 'Analyzer_id',
					'label' => 'Служба',
					'rules' => '',
					'type' => 'id'
				]
			]
		);
		$this->load->database();
		$this->load->model('Analyzer_model', 'Analyzer_model');
	}

	/**
	 *	Сохранение признака(активности, связи, учёта) анализатора
	 */
	function saveAnalyzerField() {
		$data = $this->ProcessInputData('saveAnalyzerField', true);
		if ($data === false) { return false; }

		$response = $this->Analyzer_model->saveAnalyzerField($data);
		$this->ReturnData(['success' => $response]);

		return true;
	}

	/**
	 *	Сохранение услуги на экземпляре анализатора (из модели)
	 */
	function saveUslugaFromModelToAnalyzer() {
		$data = $this->ProcessInputData('saveUslugaFromModelToAnalyzer', true);
		if ($data === false) { return false; }

		$response = $this->Analyzer_model->saveUslugaFromModelToAnalyzer($data);
		$this->ReturnData(array('success' => true));
	}

	/**
	 *	Сохранение анализатора
	 */
	function save() {
		$data = $this->ProcessInputData('save', true);
		if ($data === false) { return false; }

		if (!empty($data['equipment_id'])) {
			// проверка наличия связи с другим анализатором
			if ($this->Analyzer_model->checkAnalyzerHasLinkAllready($data)) {
				$this->ReturnError('Данный анализатор ЛИС уже связан с другим анализатором в системе промед');
				return false;
			}
		}

		if (empty($data['Analyzer_IsManualTechnic']) && empty($data['AnalyzerModel_id'])) {
			$this->ReturnError('Не указана модель анализатора');
			return false;
		}
		if (!empty($data['AnalyzerModel_id'])) {
			$this->load->model('AnalyzerModel_model');
			if (!$this->AnalyzerModel_model->AModelHasType($data)) {
				$this->ReturnError('У выбранной модели анализатора не заполнен тип оборудования! Обратитесь к Администратору системы.');
				return false;
			}
		}

		$response = $this->Analyzer_model->save($data);

		//убираем копирование услуг для анализаторов с автоучетом реактивов:
		if ( !(isset($data['Analyzer_IsUseAutoReg']) && $data['Analyzer_IsUseAutoReg']) ) {
			// добавляем из ЛИС -> копируем услуги (только при первом добавлении анализатора)
			if (empty($data['Analyzer_id']) && !empty($response[0]['Analyzer_id']) && !empty($data['equipment_id']) && !empty($data['MedService_id'])) {
				$data['Test_JSON'] = json_decode($data['Test_JSON'], true);
				if (is_array($data['Test_JSON']) && count($data['Test_JSON']) > 0) {
					// получаем все услуги связанные с анализатором в лис и сохраняем у нас + добавляем на службу
					$this->Analyzer_model->getAndSaveUslugaCodesForEquipment(array(
						'MedService_id' => $data['MedService_id'],
						'Analyzer_id' => $response[0]['Analyzer_id'],
						'Test_JSON' => $data['Test_JSON'],
						'equipment_id' => $data['equipment_id'],
						'pmUser_id' => $data['pmUser_id'],
						'Server_id' => $data['Server_id'],
						'session' => $data['session']
					));
				}
			}

			// добавляем из Промед -> копируем услуги (только при первом добавлении анализатора)
			if (empty($data['Analyzer_id']) && !empty($response[0]['Analyzer_id']) && empty($data['equipment_id']) && !empty($data['AnalyzerModel_id']) && !empty($data['MedService_id'])) {
				// получаем все услуги связанные с модеью анализатора и сохраняем на анализатор + добавляем на службу
				$this->Analyzer_model->getAndSaveUslugaCodesForAnalyzerModel(array(
					'MedService_id' => $data['MedService_id'],
					'Analyzer_id' => $response[0]['Analyzer_id'],
					'AnalyzerModel_id' => $data['AnalyzerModel_id'],
					'pmUser_id' => $data['pmUser_id'],
					'Server_id' => $data['Server_id'],
					'session' => $data['session']
				));
			}
		}

		$this->ProcessModelSave($response, true, 'Ошибка при сохранении Анализатор')->ReturnData();

		return true;
	}

	/**
	 *	Загрузка данных анализатора
	 */
	function load() {
		$data = $this->ProcessInputData('load', true);
		if ($data){
			$this->Analyzer_model->setAnalyzer_id($data['Analyzer_id']);
			$response = $this->Analyzer_model->load();
			$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *	Получение списка анализаторов
	 */
	function loadList() {
		$data = $this->ProcessInputData('loadList', true);
		if ($data) {
			$filter = $data;
			$response = $this->Analyzer_model->loadList($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 *	Удаление анализатора
	 */
	function delete() {
		$data = $this->ProcessInputData('delete', true, true);
		if ($data === false) { return false; }

		$response = $this->Analyzer_model->delete($data);
		$this->ProcessModelSave($response, true, $response)->ReturnData();

		return true;
	}

	/**
	 * Добавление услуги в состав на службу
	 */
	function addUslugaComplexMedServiceFromTest() {
		$data = $this->ProcessInputData('addUslugaComplexMedServiceFromTest', true, true);
		if ($data === false) { return false; }

		$response = $this->Analyzer_model->addUslugaComplexMedServiceFromTest($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении услуги из теста')->ReturnData();

		return true;
	}

	/**
	 *  Генерирует код анализатора
	 */
	function getAnalyzerCode(){
		$data = $this->ProcessInputData('getAnalyzerCode', true);
		if ($data)
		{
			$response = $this->Analyzer_model->getAnalyzerCode($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Проверка, является ли служба, на которой заведен анализатор, внешней
	*/
	function checkIfFromExternalMedService()
	{
		$data = $this->ProcessInputData('checkIfFromExternalMedService', true);
		if ($data == false) return;

		$response = $this->Analyzer_model->checkIfFromExternalMedService($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
}