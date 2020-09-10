<?php


class RemoteConsult_model extends swModel
{
	/**
	 * Схема в БД
	 */
	protected $_scheme = "dbo";

	/**
	 * Это Doc-блок
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Метод загрузки протокола удаленной консультации
	 * формирует файл по пути вида:
	 * uploads/orgs/protocols/[lpu_id]/LpuSection/[LpuSection_id_File_name].(doc|pdf)
	 */
	function uploadRemoteProtocol($data, $files) {
		/**
		 * Создание каталогов
		 */
		function createDir($path) {
			if(!is_dir($path)) { // Если нет корневой папки для хранения файлов организаций
				// то создадим ее
				$success = mkdir($path, 0777);
				if(!$success) {
					DieWithError('Не удалось создать папку "'.$path.'"');
					return false;
				}
			}
			return true;
		}
		
		if (!defined('ORGSPATH') || !defined('ORGSPROTOCOLPATH')) {
			return array('success' => false, 'Error_Msg'=>'Необходимо задать константы с указанием папок для загрузки файлов (config/promed,php): ORGSPATH и ORGSPROTOCOLPATH');
		}

		if(!isset($files['userfile'])) {
			return array('success' => false, 'Error_Msg'=>'Не удалось загрузить файл.');
		}

		$source = $files['userfile']['tmp_name'];
		if(is_uploaded_file($source)) {
			// Наименование файла
			$flname = $files['userfile']['name'];
			$ext = pathinfo($flname, PATHINFO_EXTENSION);
			if($ext == 'exe') {
				return array('success' => false, 'Error_Msg'=>'Неверное расширение файла.');
			}
			if ($data['Lpu_id']>0) {
				$name = $flname;

				// Создание директорий, если нужно
				createDir(ORGSPATH);
				createDir(ORGSPROTOCOLPATH); 
				$orgDir = ORGSPROTOCOLPATH.$data['Lpu_id']."/";
				createDir($orgDir);
				if ($data['LpuSection_id']>0) {
					$orgDir .= "LpuSection/";
					createDir($orgDir);
					$orgDir .= $data['LpuSection_id']. "/";
					createDir($orgDir);
				}
				$name = $flname;

				move_uploaded_file($source, $orgDir.$name);
				
				$res = $this->saveFilePath([
					'filePath' => $orgDir . $name,
					'EvnDirection_id' => $data['EvnDirection_id'],
					'pmUser_id' => $data['pmUser_id']
				]);
			
				if (!$res) {
					return array('success' => false, 'Error_Msg'=>'Не удалось сохранить файл!');
				}

				return array(
					'success' => true,
					'file_url' => $orgDir . $name."?t=".time() // добавляем параметр, чтобы не застывал в кеше
				);
			} else {
				return array('success' => false, 'Error_Msg'=>'Не удалось загрузить файл, т.к. МО не определена!');
			}
		}
		else {
			return array('success' => false, 'Error_Msg'=>'Не удалось загрузить файл!');
		}
	}
	
	function saveFilePath($data) {
		
		$query = "
		declare
			@RemoteConsultProtocol_id bigint = null,
			@Error_Code bigint = 0,
			@Error_Message varchar(4000) = '';
		exec p_RemoteConsultProtocol_ins
			@RemoteConsultProtocol_id = @RemoteConsultProtocol_id output,
			@EvnDirection_id = :EvnDirection_id,
			@RemoteConsultProtocol_FilePath = :RemoteConsultProtocol_FilePath,
			
			@pmUser_id = :pmUser_id,
			@Error_Code = @Error_Code output,
			@Error_Message = @Error_Message output;
		select @RemoteConsultProtocol_id as RemoteConsultProtocol_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;";

		$result = $this->db->query($query, [
			'EvnDirection_id' => $data['EvnDirection_id'],
			'RemoteConsultProtocol_FilePath' => $data['filePath'],
			'pmUser_id' => $data['pmUser_id']
		]);
		
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	function deleteRemoteProtocol($data) {

		$filePath = $this->getRemoteProtocolFilePath($data);
		
		$query = "
			DECLARE
				@Error_Code bigint,
				@Error_Message varchar(4000);
			EXEC p_RemoteConsultProtocol_del
				@RemoteConsultProtocol_id = :RemoteConsultProtocol_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			SELECT @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		$result = $this->db->query($query, [
			'RemoteConsultProtocol_id' => $data['RemoteConsultProtocol_id']
		]);

		if (is_object($result)) {
			if(file_exists($filePath)) {
				unlink($filePath);
			}
			
			return ['success' => true];
		}
		return false;
	}
	
	function getRemoteProtocolFilePath($data){
		$query = "
			SELECT RemoteConsultProtocol_FilePath
			FROM v_RemoteConsultProtocol with (nolock)
			WHERE RemoteConsultProtocol_id = :RemoteConsultProtocol_id
		";

		$filePath = $this->getFirstResultFromQuery($query, [
			'RemoteConsultProtocol_id' => $data['RemoteConsultProtocol_id']
		]);
		
		if (!empty($filePath)) {
			return $filePath;
		}
		
		return '';
	}
	
	function downloadRemoteProtocol($filepath) {
		if (!file_exists($filepath)) die('Файл не найден ' . $filepath);

		$tmp = explode('/', $filepath);
		$i = count($tmp)-1;
		if (isset($tmp[$i])){
			$filename = $tmp[$i];
		} else {
			$filename = 'default_filename.txt';
		}
		
		header("Content-Description: File Transfer");
		header("Content-Disposition: attachment; filename=$filename");
		header("Content-Type: application/zip");

		// read the file from disk
		readfile($filepath);
		exit;
	}

}