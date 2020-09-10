<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * SystemMonitor - контроллер для работы мониторинга системы
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			04.04.2014
 *
 * @property SystemMonitor_model dbmodel
 */

class SystemMonitor extends swController {
	protected  $inputRules = array(
		'saveSystemMonitorQuery' => array(
			array(
				'field' => 'SystemMonitorQuery_id',
				'label' => 'Идентификатор запроса',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'SystemMonitorQuery_Name',
				'label' => 'Название запроса',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'SystemMonitorQuery_Query',
				'label' => 'Строка запроса',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'SystemMonitorQuery_RepeatCount',
				'label' => 'Количества выполнений',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'SystemMonitorQuery_TimeLimit',
				'label' => 'Превышение',
				'rules' => '',
				'type' => 'float'
			)
		),
		'saveSystemMonitorQueryLog' => array(
			array(
				'field' => 'SystemMonitorQueryLog_id',
				'label' => 'Идентификатор строки лога',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'SystemMonitorQueryLog_Num',
				'label' => '',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'SystemMonitorQuery_id',
				'label' => 'Идентификатор запроса',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'SystemMonitorQuery_Name',
				'label' => 'Название запроса',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'SystemMonitorQueryLog_Date',
				'label' => 'Дата формирование строки лога',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'SystemMonitorQueryLog_Time',
				'label' => 'Время формирование строки лога',
				'rules' => 'required',
				'type' => 'time'
			),
			array(
				'field' => 'SystemMonitorQueryLog_RunCount',
				'label' => 'Количество запусков запроса',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'SystemMonitorQuery_TimeLimit',
				'label' => 'Превышение',
				'rules' => '',
				'type' => 'float'
			),
			array(
				'field' => 'SystemMonitorQueryLog_minRunTime',
				'label' => 'Мин. время выполениния запроса (сек)',
				'rules' => 'required',
				'type' => 'float'
			),
			array(
				'field' => 'SystemMonitorQueryLog_maxRunTime',
				'label' => 'Макс. время выполениния запроса (сек)',
				'rules' => 'required',
				'type' => 'float'
			),
			array(
				'field' => 'SystemMonitorQueryLog_avgRunTime',
				'label' => 'Средн. время выполениния запроса (сек)',
				'rules' => 'required',
				'type' => 'float'
			),
			array(
				'field' => 'SystemMonitorQueryLog_isError',
				'label' => 'Флаг ошибки',
				'rules' => 'required',
				'type' => 'int'
			)
		),
		'deleteSystemMonitorQuery' => array(
			array(
				'field' => 'SystemMonitorQuery_id',
				'label' => 'Идентификатор запроса',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadSystemMonitorQueryList' => array(
			array(
				'field' => 'SystemMonitorQuery_id',
				'label' => 'Идентификатор запроса',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadSystemMonitorQueryLogGrid' => array(
			array(
				'field' => 'SystemMonitorQuery_id',
				'label' => 'Идентификатор запроса',
				'rules' => '',
				'type' => 'id'
			)
		),
		'exportSystemMonitorQueryLog' => array(
			array(
				'field' => 'SystemMonitorQuery_id',
				'label' => 'Идентификатор запроса',
				'rules' => '',
				'type' => 'id'
			)
		),
		'loadSystemMonitorQueryForm' => array(
			array(
				'field' => 'SystemMonitorQuery_id',
				'label' => 'Идентификатор запроса',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'clearSystemMonitorQueryLog' => array(
			array(
				'field' => 'SystemMonitorQueryLog_id',
				'label' => 'Идентификатор строки лога',
				'rules' => '',
				'type' => 'id'
			)
		),
		'barcodeScannerLogging' => array(
			array(
				'field' => 'pmUser_id',
				'label' => 'Идентификатор пользователя',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonal_id',
				'label' => 'Идентификатор мед.работника',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'polisNum',
				'label' => 'Номер полиса',
				'rules' => '',
				'type' => 'string'
			)
		)
	);

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model('SystemMonitor_model', 'dbmodel');
	}

	/**
	 * Сохранение запроса для логирования
	 */
	function saveSystemMonitorQuery()
	{
		$data = $this->ProcessInputData('saveSystemMonitorQuery', false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveSystemMonitorQuery($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}

	/**
	 * Сохранение строки лога
	 * @return bool
	 */
	function saveSystemMonitorQueryLog()
	{
		$data = $this->ProcessInputData('saveSystemMonitorQueryLog', false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveSystemMonitorQueryLog($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}

	/**
	 * Удаление запроса для логировния
	 */
	function deleteSystemMonitorQuery()
	{
		$data = $this->ProcessInputData('deleteSystemMonitorQuery', false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->deleteSystemMonitorQuery($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}

	/**
	 * Возвращает список запросов для мониторинга
	 * @return bool
	 */
	function loadSystemMonitorQueryLogGrid()
	{
		$data = $this->ProcessInputData('loadSystemMonitorQueryLogGrid', false);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->loadSystemMonitorQueryLogGrid($data);

		$this->ProcessModelMultiList($response, false, 'При получении данных возникли ошибки')->ReturnData();
		return true;
	}
	


	/**
	 * Возвращает список запросов для мониторинга
	 * @return bool
	 */
	function loadSystemMonitorQueryList()
	{
		$data = $this->ProcessInputData('loadSystemMonitorQueryList', false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadSystemMonitorQueryList($data);
		$this->ProcessModelMultiList($response, false, 'При получении данных возникли ошибки')->ReturnData();
		return true;
	}

	/**
	 * Получения данных запроса для редактирвания
	 * @return bool
	 */
	function loadSystemMonitorQueryForm()
	{
		$data = $this->ProcessInputData('loadSystemMonitorQueryForm', false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadSystemMonitorQueryForm($data);
		$this->ProcessModelList($response, false, 'При получении данных возникли ошибки')->ReturnData();
		return true;
	}

	/**
	 * Удаление лога из mongodb
	 * @return bool
	 */
	function clearSystemMonitorQueryLog()
	{
		// Меняем подключение к php_log
		unset($this->db);
		$this->load->database('phplog');

		//Записываем данные в php_log, перед тем как удалить из монги 
		$dataLoad = $this->dbmodel->loadSystemMonitorQueryLogGrid(null);
		if ($dataLoad == false) {return false;}
		$response = $this->dbmodel->dumpSystemMonitorQueryLog($dataLoad);
		if ($response == false) {return false;}
		//--------------//
		$data = $this->ProcessInputData('clearSystemMonitorQueryLog', false);
		if ($data === false) { return false; }
		$response = $this->dbmodel->clearSystemMonitorQueryLog($data);
		$this->ProcessModelSave($response, false)->ReturnData();
		return true;
	}

	/**
	 * Экспорт лога в csv
	 * @return bool
	 */
	function exportSystemMonitorQueryLog() {
		$data = $this->ProcessInputData('exportSystemMonitorQueryLog', true);
		if ($data === false) { return false; }

		$data['export'] = true;
		$response = $this->dbmodel->loadSystemMonitorQueryLogGrid($data);
		if( !is_array($response) || count($response) == 0 ) {
			DieWithError("Нет данных");
		}

		set_time_limit(0);

		$path = EXPORTPATH_ROOT.'system_monitor_query_log/';

		if(!is_dir($path)) {
			if (!mkdir($path)) {
				DieWithError("Ошибка при создании директории ".$path."!");
			}
		}

		$f_name = "query_log_".time();
		$file_name = $path.$f_name.".csv";
		$archive_name = $path.$f_name.".zip";
		if( is_file($archive_name) ) {
			unlink($archive_name);
		}

		try {
			$h = fopen($file_name, 'w');
			if(!$h) {
				DieWithError("Ошибка при попытке открыть файл!");
			}
			$str_result = "";
			$str_result .= "№;";
			$str_result .= "Дата;";
			$str_result .= "Время;";
			$str_result .= "Запрос;";
			$str_result .= "Количество запусков;";
			$str_result .= "Мин., сек;";
			$str_result .= "Макс., сек;";
			$str_result .= "Сред., сек;";
			$str_result .= "Сервер\n";

			array_walk_recursive($response, 'ConvertFromUTF8ToWin1251');

			foreach($response['data'] as $row) {
				$str_result .= $row['SystemMonitorQueryLog_Num'].";";
				$str_result .= $row['SystemMonitorQueryLog_Date'].";";
				$str_result .= $row['SystemMonitorQueryLog_Time'].";";
				$str_result .= $row['SystemMonitorQuery_Name'].";";
				$str_result .= $row['SystemMonitorQueryLog_RunCount'].";";
				$str_result .= $row['SystemMonitorQueryLog_minRunTime'].";";
				$str_result .= $row['SystemMonitorQueryLog_maxRunTime'].";";
				$str_result .= $row['SystemMonitorQueryLog_avgRunTime'].";";
				$str_result .= ((isset($row['SystemMonitorQueryLog_serverIP']))?$row['SystemMonitorQueryLog_serverIP']:'')."\n";
			}
            $str_result = mb_convert_encoding($str_result,'cp-1251',mb_detect_encoding($str_result));
			fwrite($h, $str_result);
			fclose($h);

			$zip = new ZipArchive();
			$zip->open($archive_name, ZIPARCHIVE::CREATE);
			$zip->AddFile($file_name, basename($file_name));
			$zip->close();
			unlink($file_name);

			$this->ReturnData(array('success' => true, 'url' => $archive_name));
		} catch (Exception $e) {
			DieWithError($e->getMessage());
			$this->ReturnData(array('success' => false));
		}

		if(is_file($file_name)) {
			@unlink($file_name);
		}
	}
	
	/**
	 * Логирвание работы сканера штрихкода в БД
	 */
	function barcodeScannerLogging(){
		$data = $this->ProcessInputData('barcodeScannerLogging', true, true);
		
		$response = $this->dbmodel->barcodeScannerLogging($data);
		if($response['ScannerHistory_id']){
			$this->ReturnData(array('success' => true, 'ScannerHistory_id' => $response['ScannerHistory_id']));
		}else{
			$this->ReturnData(array('success' => false));
		}
	}
}