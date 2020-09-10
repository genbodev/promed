<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Mse
* @access       public
* @copyright    Copyright (c) 2019 Swan Ltd.
*/

class MseAutoInteract_model extends swModel
{
	protected $conn;
	protected $hasErrors;
	protected $conf;
	protected $ServiceList_id;
	protected $log;

	/**
	 *	Method description
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 *	Method description
	 */
	function runService($data) {
		set_time_limit(0);
		
		$this->load->model('Mse_model');
		$this->load->model('ServiceList_model');
		$this->load->helper('ServiceListLog');
		$this->load->model('ObjectSynchronLog_model', 'sync');
		
		$this->ServiceList_id = $this->ServiceList_model->getServiceListId('MSEExp');
		$this->log = new ServiceListLog($this->ServiceList_id, $data['pmUser_id']);
		$this->log->start();
		$this->hasErrors = false;
		
		$this->conf = $this->config->item('MSEExp');
		if (!is_array($this->conf)) {
			$this->saveErrorPackage("Не найден конфиг MSEExp");
			$this->log->finish(false);
			return false;
		}
		
		$this->conn = $this->doConnect();
		
		if (!$this->conn) {
			$this->saveErrorPackage('Ошибка при подключению к серверу');
			$this->log->finish(false);
			return false;
		}
		
		if (!ftp_login($this->conn, $this->conf['user'], $this->conf['password'])) {
			$this->saveErrorPackage('Ошибка при авторизации');
			$this->log->finish(false);
			return false;
		}
		
		ftp_pasv($this->conn, true);
		
		try {
			$this->runExport();
		} catch(Exception $e) {
			$this->saveErrorPackage('Ошибка при экспорте: ' . $e->getMessage());
			$this->log->finish(false);
			return false;
		}
		
		$this->log->finish(true);
		
		// --- заканчиваем экспорт, начинаем импорт -----
		// соединение остаётся то же, но лог другой
		
		$this->ServiceList_id = $this->ServiceList_model->getServiceListId('MSEImp');
		$this->log = new ServiceListLog($this->ServiceList_id, $data['pmUser_id']);
		$this->log->start();
		$this->hasErrors = false;
		
		try {
			$this->runImport();
		} catch(Exception $e) {
			$this->saveErrorPackage('Ошибка при импорте: ' . $e->getMessage());
			$this->log->finish(false);
			return false;
		}
		
		ftp_close($this->conn);
		$this->log->finish(true);
	}
	
	/**
	 *	экспорт направлений
	 */
	function runExport() {
		
		if (empty($this->conf['export_folder'])) {
			throw new Exception('Не задана папка для экспорта');
		}
		
		$this->sync->setServiceSysNick('MSEExp');
		
		$dt = new DateTime();
		$dt = $dt->modify('-1 day')->format('Y-m-d');
		
		$data = [
			'ExportAllRecords' => true,
			'EvnStatus_id' => 28,
			'ExportDateRange' => [$dt, $dt]
		];
		
		if (ftp_nlist($this->conn, $this->conf['export_folder']) === false) {
			throw new Exception('Папки для экспорта не существует или к ней нет доступа');
		}
		
		$local_files = $this->Mse_model->exportEvnPrescrMse($data, true);
		
		if (!$this->isSuccessful($local_files)) {
			throw new Exception($local_files[0]['Error_Msg']);
		}
		
		foreach($local_files as $file) {
			
			$resp = $this->log->addPackage('EvnPrescrMse', $file['EvnPrescrMse_id'], $file['G_Code']);
			if (!$this->isSuccessful($resp)) throw new Exception($resp[0]['Error_Msg']);
			
			if ($file['isError']) {
				$this->hasErrors = true;
				$this->log->add(false, json_encode(['msg' => "Файл с идентификатором направления {$file['G_Code']} имеет ошибки", 'file' => $file['path']]), $resp[0]['ServiceListPackage_id']);
				continue;
			}
			
			$load_res = $this->doUploadFile($this->conf['export_folder'].'/'.basename($file['path']), $file['path']);
			if(!$load_res) {
				$this->hasErrors = true;
				$this->log->add(false, json_encode(['msg' => "Не удалось загрузить файл с идентификатором направления {$file['G_Code']} в папку обмена", 'file' => $file['path']]), $resp[0]['ServiceListPackage_id']);
			} else {
				$this->sync->saveObjectSynchronLog('EvnPrescrMse', $file['EvnPrescrMse_id'], $file['G_Code']);
				unlink($file['path']);
			}
		}
	}
	
	/**
	 *	импорт талонов
	 */
	function runImport() {
		
		if (empty($this->conf['import_folder'])) {
			throw new Exception('Не задана папка для импорта');
		}
		
		$this->sync->setServiceSysNick('MSEImp');
		
		$upload_path = './'.IMPORTPATH_ROOT.'importEvnMse/';
		
		$path = '';
		$folders = explode('/', $upload_path);
		for($i=0; $i<count($folders); $i++) {
			if ($folders[$i] == '') {continue;}
			$path .= $folders[$i].'/';
			if (!@is_dir($path)) {
				mkdir( $path );
			}
		}
		
		if (!@is_dir($upload_path)) {
			throw new Exception('Локальный путь для загрузки файлов некорректен');
		}

		if (!is_writable($upload_path)) {
			throw new Exception('Загрузка файла в локальную папку не возможна из-за прав пользователя');
		}
		
		$files = ftp_nlist($this->conn, $this->conf['import_folder']);
		
		if ($files === false) {
			throw new Exception('Папки для импорта не существует или к ней нет доступа');
		}
		
		foreach($files as $file) {
			
			$filename = basename($file);
			$local_path = $upload_path.$filename;
			
			if (pathinfo($file, PATHINFO_EXTENSION) != 'xml') {
				continue;
			}
			
			$load_res = ftp_get($this->conn, $local_path, $file, FTP_BINARY);
			if(!$load_res) {
				$this->saveErrorPackage('Не удалось загрузить файл '.$filename);
			} else {
				$import_res = $this->Mse_model->_importEvnMse($local_path);
				if ($this->isSuccessful($import_res)) {
					$resp = $this->log->addPackage('EvnMse', $import_res[0]['EvnMse_id'], $import_res[0]['EvnMse_ImportedCouponGUID']);
					if (!$this->isSuccessful($resp)) throw new Exception($resp[0]['Error_Msg']);
					$this->sync->saveObjectSynchronLog('EvnMse', $import_res[0]['EvnMse_id'], $import_res[0]['EvnMse_ImportedCouponGUID']);
					unlink($local_path);
				} else {
					$this->saveErrorPackage("Не удалось импортировать Обратный талон {$filename}: {$import_res['Error_Msg']}", $local_path);
				}
				ftp_delete($this->conn, $file);
			}
		}
	}
	
	/**
	 *	Подключение, 3 попытки
	 */
	function doConnect() {
		
		$this->conn = ftp_connect($this->conf['host'], $this->conf['port']);
		
		if (!$this->conn) {
			sleep(5);
			$this->conn = ftp_connect($this->conf['host'], $this->conf['port']);
		}
		
		if (!$this->conn) {
			sleep(10);
			$this->conn = ftp_connect($this->conf['host'], $this->conf['port']);
		}
		
		return $this->conn;
	}
	
	/**
	 *	Загрузка файла, несколько попыток, рекурсивно
	 */
	function doUploadFile($server_path, $local_path, $xtry = 3) {
		
		$xtry--;
		
		$res = ftp_put($this->conn, $server_path, $local_path, FTP_BINARY);
		
		if ($res !== false || $xtry == 0) return $res;
		
		sleep(5);
		
		return $this->doUploadFile($server_path, $local_path, $xtry);
	}
	
	/**
	 *	в логе отображаются только пакеты
	 *  для отображения ошибки добавляем пустой пакет и ошибку в него
	 */
	function saveErrorPackage($msg, $file_link = '') {
		$this->hasErrors = true;
		$resp = $this->log->addPackage('DummyPackage', null);
		if (!$this->isSuccessful($resp)) throw new Exception($resp[0]['Error_Msg']);
		$this->log->add(false, json_encode(['msg' => $msg, 'file' => $file_link]), $resp[0]['ServiceListPackage_id']);
	}
}
