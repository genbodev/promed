<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Rmis - контроллер
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			15.04.2015
 *
 * @property Rmis_model dbmodel
 */

class Rmis extends swController {
	protected  $inputRules = array(
		'getRefbook' => array(
			array(
				'field' => 'tableName',
				'label' => 'Наименование справочника',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'partNumber',
				'label' => 'Номер части',
				'rules' => '',
				'type' => 'int'
			),
		),
		'getRefbookVersionList' => array(
			array(
				'field' => 'tableName',
				'label' => 'Наименование справочника',
				'rules' => 'required',
				'type' => 'string'
			),
		),
		'createIndividual' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'searchIndividual' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_SurName',
				'label' => 'Фамилия человека',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Person_FirName',
				'label' => 'Имя человека',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Person_SecName',
				'label' => 'Отчество человека',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Person_BirthDay',
				'label' => 'День рождение человека',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'Person_deadDT',
				'label' => 'Дата смерти человека',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'Sex_id',
				'label' => 'Пол',
				'rules' => '',
				'type' => 'id'
			),
		),
		'searchPlaces' => array(
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор МО',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_OGRN',
				'label' => 'ОГРН',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Lpu_INN',
				'label' => 'ИНН',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Lpu_OKATO',
				'label' => 'ОКАТО',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Lpu_f003mcod',
				'label' => 'МКОД',
				'rules' => '',
				'type' => 'int'
			),
		),
		'searchDepartments' => array(
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор МО',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'execCommand' => array(
			array(
				'field' => 'serviceType',
				'label' => 'Тип сервиса',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'command',
				'label' => 'Команда',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'params',
				'label' => 'Команда',
				'rules' => '',
				'type' => 'json_array',
				'assoc' => true
			),
			array(
				'field' => 'printResult',
				'label' => 'Печать резульатат',
				'rules' => '',
				'type' => 'checkbox'
			),
		),
	);

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->database();

		set_time_limit(0);

		ini_set("max_execution_time", "0");
		ini_set("default_socket_timeout", "999");

		$this->load->model('Rmis_model', 'dbmodel');
	}

	/**
	 * Сохранение ассоциативного масива данных в xlsx файл
	 */
	function saveToXlsx($fileName, $data) {
		require_once('vendor/autoload.php');
		$xls = new PhpOffice\PhpSpreadsheet\Spreadsheet();

		$sheet = $xls->setActiveSheetIndex(0);

		$colN = 0;
		foreach($data[0] as $key => $value) {
			$sheet->setCellValueByColumnAndRow($colN, 1, $key);
			$sheet->getColumnDimensionByColumn($colN)->setAutoSize(true);
			$colN++;
		}

		for($rowN=0; $rowN<count($data); $rowN++) {
			$colN=0;
			foreach($data[$rowN] as $key => $value) {
				$sheet->setCellValueByColumnAndRow($colN, $rowN+2, $value);
				$colN++;
			}
		}

		$objWriter = new PhpOffice\PhpSpreadsheet\Writer\Xlsx($xls);
		$objWriter->save(EXPORTPATH_ROOT.'refbooks/'.$fileName.'.xlsx');
	}

	/**
	 * Получение списка справочников
	 */
	function getRefbookList() {

		$result = $this->dbmodel->getRefbookList();

		echo '<pre>';
		print_r($result);
		echo '</pre>';
	}

	/**
	 * Получение справочника
	 */
	function getRefbook() {
		$data = $this->ProcessInputData('getRefbook');
		if (!$data) {
			return;
		}

		$result = $this->dbmodel->getRefbook($data);

		echo '<pre>';
		print_r($result);
		echo '</pre>';
	}

	/**
	 * Получение списка версий справочников
	 */
	function getRefbookVersionList() {
		$data = $this->ProcessInputData('getRefbookVersionList');
		if (!$data) {
			return;
		}

		$result = $this->dbmodel->getRefbookVersionList($data);

		echo '<pre>';
		print_r($result);
		echo '</pre>';
	}

	/**
	 * Создание физ. лица
	 */
	function createIndividual() {
		$data = $this->ProcessInputData('createIndividual');
		if (!$data) {
			return;
		}

		$Person_Code = $this->dbmodel->createIndividual($data);

		echo '<pre>';
		print_r($Person_Code);
		echo '</pre>';
	}

	/**
	 * Поиск физ. лица
	 */
	function searchIndividual() {
		$data = $this->ProcessInputData('searchIndividual');
		if (!$data) {
			return;
		}

		$person_code_list = $this->dbmodel->searchIndividual($data);

		$response = array();
		$index = 0;
		foreach ($person_code_list as $code) {
			$response[$index] = $this->dbmodel->getIndividual(array('Person_Code' => $code));
			$response[$index]['code'] = $code;
			$index++;
		}

		echo '<pre>';
		print_r($response);
		echo '</pre>';
	}

	/**
	 * Поиск МО
	 */
	function searchPlaces() {
		$data = $this->ProcessInputData('searchPlaces', false);
		if (!$data) {
			return;
		}

		$clinicIds = $this->dbmodel->searchPlaces($data);

		if (!empty($clinicIds[0]['Error_Msg'])) {
			echo '<pre>';
			print_r($clinicIds);
			echo '</pre>';
			return;
		}

		$clinics = array();
		foreach($clinicIds as $clinicId) {
			$clinics[] = $this->dbmodel->getPlace($clinicId);
		}

		echo '<pre>';
		print_r($clinics);
		echo '</pre>';
	}

	/**
	 * Поиск отделений
	 */
	function searchDepartments() {
		$data = $this->ProcessInputData('searchDepartments', false);
		if (!$data) {
			return;
		}

		$response = $this->dbmodel->searchDepartments($data);

		echo '<pre>';
		print_r($response);
		echo '</pre>';
	}

	/**
	 * Сохранение справочников из списка в xlsx файлы
	 */
	function saveRefbooksInFile() {
		$str = file_get_contents(EXPORTPATH_ROOT.'refbooks/list.txt');

		$list = explode("\n", $str);
		foreach($list as &$item) {
			$item = trim($item);
		}

		$response = $this->dbmodel->getRefbooks($list);

		foreach ($response as $table_name => $data) {
			if (count($data) > 0) {
				$this->saveToXlsx($table_name, $data);
			}
		}

		echo '<pre>';
		print_r($response);
		echo '</pre>';
	}

	/**
	 * Проверка подключения к РМИС
	 */
	function checkConnection() {
		$response = $this->dbmodel->initSoapOptions($_SESSION, true);

		echo '<pre>';
		print_r($response);
		echo '</pre>';
	}

	/**
	 * Выполнение запроса к soap-сервису РМИС
	 */
	function execCommand() {
		$data = $this->ProcessInputData('execCommand', true);
		if (!$data) {
			return false;
		}

		if (!isSuperadmin()) {
			$this->ReturnError('Недостаточно прав для выполнениея метода');
			return false;
		}

		$this->dbmodel->setExecIterationDelay(2);
		$initResp = $this->dbmodel->initSoapOptions($data['session'], true);
		if (!empty($initResp['Error_Msg'])) {
			print_r($initResp);
			$this->ReturnError($initResp['Error_Msg'], $initResp['Error_Code']);
			return false;
		}

		try {
			$result = $this->dbmodel->exec($data['serviceType'], $data['command'], !empty($params)?$params:null);
		} catch(Exception $e) {
			$this->ReturnError($e->getMessage(), $e->getCode());
			return false;
		}

		if ($data['printResult']) {
			echo '<pre>'.print_r($result, true).'</pre>';
		} else {
			$response = array('success' => true, 'result' => json_encode($result));
			$this->ReturnData($response);
		}

		return true;
	}

	/*function test() {
		$this->dbmodel->test();
	}*/
}
