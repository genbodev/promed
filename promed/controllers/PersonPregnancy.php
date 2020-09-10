<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PersonPregnancy - контроллер для регистра беременных
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      PersonPregnancy
 * @access       public
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @author       Dmitriy Vlasenko
 * @version      11 2014
 *
 * @property PersonPregnancy_model $dbmodel
 */
class PersonPregnancy extends swController {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('PersonPregnancy_model', 'dbmodel');
		$this->inputRules = $this->dbmodel->inputRules;
	}

	/**
	 * Получение списка
	 */
	function loadList() {
		$data = $this->ProcessInputData('loadList', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadList($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Получение списка
	 */
	function loadListRecommRouter() {
		$data = $this->ProcessInputData('loadListRecommRouter', true);
		if ($data === false) { return false; }

		//echo "<pre>".print_r($data, 1)."</pre>";
		
		//$this->db = null;
		//$this->load->database('bdwork');
		
		$response = $this->dbmodel->loadListRecommRouter($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}
	
	/**
	 * Получение списка
	 */
	function loadListMonitorCenter() {
		$data = $this->ProcessInputData('loadListMonitorCenter', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->loadListMonitorCenter($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}	
	
        /**
	 * Получение списка
	 */
	function loadTrimesterListMO() {
		$data = $this->ProcessInputData('loadTrimesterListMO', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadTrimesterListMO($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}
	
	/**
	 * Получение списка
	 */
	function loadNotIncludeList() {
		$data = $this->ProcessInputData('loadNotIncludeList', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadNotIncludeList($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Получение списка
	 */
	function loadFinishedList() {
		$data = $this->ProcessInputData('loadFinishedList', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadFinishedList($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Получение списка
	 */
	function loadInterruptedList() {
		$data = $this->ProcessInputData('loadInterruptedList', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadInterruptedList($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Получение дерева для сведений о беременности
	 */
	function loadPersonPregnancyTree() {
		$data = $this->ProcessInputData('loadPersonPregnancyTree', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadPersonPregnancyTree($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Получение данных о беременности
	 */
	function loadPersonPregnancy() {
		$data = $this->ProcessInputData('loadPersonPregnancy', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadPersonPregnancy($data);
		$this->ProcessModelList($response)->ReturnData();

		return true;
	}

	/**
	 * Получение списка результатов предыдущих беременностей
	 */
	function loadPersonPregnancyResultGrid() {
		$data = $this->ProcessInputData('loadPersonPregnancyResultGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadPersonPregnancyResultGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Получение данных для гравидограммы
	 */
	function loadPersonPregnancyGravidogramData() {
		$data = $this->ProcessInputData('loadPersonPregnancyGravidogramData', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadPersonPregnancyGravidogramData($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Сохранение данных для записи регистра беременных
	 */
	function savePersonPregnancy() {
		$data = $this->ProcessInputData('savePersonPregnancy', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->savePersonPregnancy($data);
		$this->ProcessModelSave($response)->ReturnData();

		return true;
	}

	/**
	 * Проставление отметки об удалении записи из регистра беременных
	 */
	function deletePersonRegister() {
		$data = $this->ProcessInputData('deletePersonRegister', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->deletePersonRegister($data);
		$this->ProcessModelSave($response)->ReturnData();

		return true;
	}

	/**
	 * Удаление анкеты по беременности
	 */
	function deletePersonPregnancy() {
		$data = $this->ProcessInputData('deletePersonPregnancy', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->deletePersonPregnancy($data);
		$this->ProcessModelSave($response)->ReturnData();

		return true;
	}

	/**
	 * Получение идентфикатора записи из регистра беременных
	 */
	function getPersonRegisterByEvnVizitPL() {
		$data = $this->ProcessInputData('getPersonRegisterByEvnVizitPL', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getPersonRegisterByEvnVizitPL($data);
		$this->ProcessModelSave($response)->ReturnData();

		return true;
	}

	/**
	 * Получение идентфикатора записи из регистра беременных
	 */
	function getPersonRegisterByEvnSection() {
		$data = $this->ProcessInputData('getPersonRegisterByEvnSection', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getPersonRegisterByEvnSection($data);
		$this->ProcessModelSave($response)->ReturnData();

		return true;
	}

	/**
	 * Сохранение скрининга беременности
	 */
	function savePregnancyScreen() {
		$data = $this->ProcessInputData('savePregnancyScreen', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->savePregnancyScreen($data);
		$this->ProcessModelSave($response)->ReturnData();

		return true;
	}

	/**
	 * Удаление скрининга беременности
	 */
	function deletePregnancyScreen() {
		$data = $this->ProcessInputData('deletePregnancyScreen', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->deletePregnancyScreen($data);
		$this->ProcessModelSave($response)->ReturnData();

		return true;
	}

	/**
	 * Получение скрининга беременности
	 */
	function loadPregnancyScreen() {
		$data = $this->ProcessInputData('loadPregnancyScreen', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadPregnancyScreen($data);
		$this->ProcessModelList($response)->ReturnData();

		return true;
	}

	/**
	 * Получение списка сопутствующих диагнозов в скрининге беременности
	 */
	function loadPregnancyScreenSopDiagGrid() {
		$data = $this->ProcessInputData('loadPregnancyScreenSopDiagGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadPregnancyScreenSopDiagGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Получение списка случаев лечения в течении периода мониторинга беременности
	 */
	function loadPersonPregnancyEvnGrid() {
		$data = $this->ProcessInputData('loadPersonPregnancyEvnGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadPersonPregnancyEvnGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Получение списка консультацый, проведенных в течении периода мониторинга беременности
	 */
	function loadConsultationGrid() {
		$data = $this->ProcessInputData('loadConsultationGrid', true);
		if ($data === false) { return false; }

		$data['AttributeList'] = array('consult');

		$response = $this->dbmodel->loadPersonPregnancyEvnUslugaGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Получение списка исследований, проведенных в течении периода мониторинга беременности
	 */
	function loadResearchGrid() {
		$data = $this->ProcessInputData('loadResearchGrid', true);
		if ($data === false) { return false; }

		$data['AttributeList'] = array('lab','func');

		$response = $this->dbmodel->loadPersonPregnancyEvnUslugaGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Получение данных исхода беременности по умолчанию
	 */
	function loadBirthSpecStacDefaults() {
		$data = $this->ProcessInputData('loadBirthSpecStac', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadBirthSpecStacDefaults($data);
		$this->ProcessModelList($response)->ReturnData();

		return true;
	}

	/**
	 * Получение данных исхода беременности
	 */
	function loadBirthSpecStac() {
		$data = $this->ProcessInputData('loadBirthSpecStac', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadBirthSpecStac($data);
		$this->ProcessModelList($response)->ReturnData();

		return true;
	}

	/**
	 * Сохранение данных исхода беременности
	 */
	function saveBirthSpecStac() {
		$data = $this->ProcessInputData('saveBirthSpecStac', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveBirthSpecStac($data);

		if (isset($response[0]) && !empty($response[0]['Error_Msg']) && $response[0]['Error_Msg'] == 'YesNo') {
			$response[0]['Alert_Msg'] = $this->dbmodel->getAlertMsg();
		}

		$this->ProcessModelSave($response)->ReturnData();

		return true;
	}

	/**
	 * Выполение проверок перед удалением исхода беременности (в движении)
	 */
	function beforeDeleteBirthSpecStac() {
		$data = $this->ProcessInputData('beforeDeleteBirthSpecStac', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->beforeDeleteBirthSpecStac($data);
		$this->ProcessModelSave($response)->ReturnData();

		return true;
	}

	/**
	 * Удаление данных исхода беременности
	 */
	function deleteBirthSpecStac() {
		$data = $this->ProcessInputData('deleteBirthSpecStac', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->deleteBirthSpecStac($data);
		$this->ProcessModelSave($response)->ReturnData();

		return true;
	}

	/**
	 * Получение данных случая материнской смертности
	 */
	function loadDeathMother() {
		$data = $this->ProcessInputData('loadDeathMother', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadDeathMotherForm($data);
		$this->ProcessModelList($response)->ReturnData();

		return true;
	}

	/**
	 * Сохранение случая материнской смертности
	 */
	function saveDeathMother() {
		$data = $this->ProcessInputData('saveDeathMother', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveDeathMother($data);
		$this->ProcessModelSave($response)->ReturnData();

		return true;
	}

	/**
	 * Получение данных родового сертификата
	 */
	function loadBirthCertificate() {
		$data = $this->ProcessInputData('loadBirthCertificate', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadBirthCertificateForm($data);
		$this->ProcessModelList($response)->ReturnData();

		return true;
	}

	/**
	 * Сохранение родового сертификата
	 */
	function saveBirthCertificate() {
		$data = $this->ProcessInputData('saveBirthCertificate', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveBirthCertificate($data);
		$this->ProcessModelSave($response)->ReturnData();

		return true;
	}

	/**
	 * Удаление родового сертификата
	 */
	function deleteBirthCertificate() {
		$data = $this->ProcessInputData('deleteBirthCertificate', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->deleteBirthCertificate($data);
		$this->ProcessModelSave($response)->ReturnData();

		return true;
	}

	/**
	 * Исключение записи из регистра беременных
	 */
	function doPersonPregnancyOut() {
		$data = $this->ProcessInputData('doPersonPregnancyOut', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->doPersonPregnancyOut($data);
		$this->ProcessModelSave($response)->ReturnData();

		return true;
	}

	/**
	 * Отмена исключения записи из регистра беременных
	 */
	function cancelPersonPregnancyOut() {
		$data = $this->ProcessInputData('cancelPersonPregnancyOut', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->cancelPersonPregnancyOut($data);
		$this->ProcessModelSave($response)->ReturnData();

		return true;
	}

	/**
	 * Получение данных для информациионной панели в окне редактирования записи регистра беременных
	 */
	function getPersonRegisterInfo() {
		$data = $this->ProcessInputData('getPersonRegisterInfo', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getPersonRegisterInfo($data);
		$this->ProcessModelSave($response)->ReturnData();

		return true;
	}
	
	/**
	 * Сохранение взаимосвязи с регистром ЭКО
	 */
	function savelinkEco() {
		$data = $this->ProcessInputData('savelinkEco', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->savelinkEco($data);

		return true;
	}

    /**
     *  Список комбобокса Иное МО
     */
    function getDifferentLpu()
    {
        $data = $this->ProcessInputData('getDifferentLpu', true);

        if ($data === false) {
            return false;
        }

        $list = $this->dbmodel->getDifferentLpu($data);
        return $this->ReturnData($list);
    }

    /**
     *  Список комбобокса МО с лицензией акушерства
     */
    function getEcoLpuId()
    {
        $list = $this->dbmodel->getEcoLpuId();
        return $this->ReturnData($list);
    }

    /**
     * Удаление Иное МО
     */
    function deleteDifferentLpu() {
        $data = $this->ProcessInputData('deleteDifferentLpu', true);
        if ($data === false) { return false; }

        $response = $this->dbmodel->deleteDifferentLpu($data);
        $this->ProcessModelSave($response)->ReturnData();

        return true;
    }

	/**
	 * Получение данных из раздела Анкеты для раздела Скрининга
	 */
	function getAnketaForScreen() {
		$data = $this->ProcessInputData('getAnketaForScreen', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getAnketaForScreen($data);
		$this->ProcessModelSave($response)->ReturnData();

		return true;
	}
}