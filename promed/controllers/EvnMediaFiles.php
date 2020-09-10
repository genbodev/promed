<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* EvnMediaFiles - контроллер для работы с прикрепленными файлами
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Parka
* @access       public
* @copyright    Copyright (c) 2009-2011 Swan Ltd.
* @author       Salakhov Rustam
* @version      10.03.2011
*/

class EvnMediaFiles extends swController {
	/**
	 * Description
	 */
	function __construct() {
		parent::__construct();
		$this->inputRules = array(
			'uploadFile' => array (
				array('field' => 'filterType','label' => 'Фильтр по типам файлов','rules' => 'trim|strtolower','type' => 'string', 'default' => 'all'),
				array('field' => 'Evn_id','label' => 'Идентификатор события','rules' => '','type' => 'id'),
				array('field' => 'Evn_sid','label' => 'Идентификатор события','rules' => '','type' => 'id'),
				array('field' => 'FileDescr','label' => 'Примечание','length' => 255,'rules' => '','type' => 'string'),
				array('field' => 'fromQueryEvn','label' => '','rules' => '','type' => 'id'),
				array('field' => 'saveOnce','label' => 'Признак немедленного сохранения','rules' => '','type' => 'string'),
				array('field' => 'isForDoc','label' => 'Признак загрузки картинки для вставки в документ','rules' => '','type' => 'int'),
			),
			'saveChanges' => array (
				array('field' => 'Evn_id','label' => 'Идентификатор события','rules' => '','type' => 'id'),
				array('field' => 'changedData','label' => 'Мнформация о измененных файлах','rules' => '','type' => 'string')
			),
			'deleteFile' => array (
				array('field' => 'id','label' => 'Идентификатор файла в базе данных','rules' => 'required','type' => 'id')
			),
			'remove' => array (
				array('field' => 'EvnMediaData_id','label' => 'Идентификатор файла в базе данных','rules' => 'required','type' => 'id')
			),
			'loadEvnMediaFilesListGrid' => array (
				array('field' => 'filterType','label' => 'Фильтр по типам файлов','rules' => 'trim|strtolower','type' => 'string', 'default' => 'all'),
				array('field' => 'Evn_id','label' => 'Идентификатор события','rules' => '','type' => 'id'),
				array('field' => 'EvnXml_id','label' => 'Идентификатор документа','rules' => '','type' => 'id')
			),
			'loadEvnMediaDataPanel' => array (
				array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => '', 'type' => 'id'),
				array('default' => 100,'field' => 'limit','label' => 'Лимит записей','rules' => '','type' => 'int'),
				array('default' => 0,'field' => 'start','label' => 'Начальная запись','rules' => '','type' => 'int')
			),
			'loadList' => array (
				array('field' => 'filterType','label' => 'Фильтр по типам файлов','rules' => 'trim|strtolower','type' => 'string', 'default' => 'all'),
				array('field' => 'Evn_id','label' => 'Идентификатор события','rules' => '','type' => 'id'),
				array('field' => 'EvnXml_id','label' => 'Идентификатор документа','rules' => '','type' => 'id')
			),
			'getEvnByEvnXml' => array (
				array('field' => 'EvnXml_id','label' => 'Идентификатор документа','rules' => 'required','type' => 'id')
			),
			'deleteEvnMediaFile' => array(
				array('field' => 'EvnMediaData_id','label' => 'Идентификатор файла','rules' => 'required','type' => 'id'),
				array('field' => 'file_name','label' => 'Имя файла','rules' => 'required','type' => 'string')
			),
			'linkFilesToEvn' => array(
				array('field' => 'Evn_id', 'label' => 'Идентификатор события', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'EvnMediaDataIds', 'label' => 'Массив идентификаторов файлов', 'rules' => 'required', 'type' => 'string')
			)
		);
		$this->load->database();
		$this->load->model('EvnMediaFiles_model', 'dbmodel');
	}

	/**
	 * Определение Evn_id ТАП, КВС по идентификатору документа EvnXml_id
	 * На выходе: JSON-строка
	 * Используется: формы на которых используется комбобокс выбора файла sw.Promed.SwEvnMediaDataCombo
	 */
	function getEvnByEvnXml() {
		$data = $this->ProcessInputData('getEvnByEvnXml', true);
		if ($data) {
			$response = $this->dbmodel->getEvnByEvnXml($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Функция чтения списка файлов для комбобокса, не больше 100
	 * На выходе: JSON-строка
	 * Используется: формы на которых используется комбобокс выбора файла sw.Promed.SwEvnMediaDataCombo
	 */
	function loadList() {
		$data = $this->ProcessInputData('loadList', true);
		if ($data) {
			$response = $this->dbmodel->loadList($data);
			$this->ProcessModelList($response, true, true, 'При запросе возникла ошибка.', function ($row, $thas) {
				$row['EvnMediaData_Src'] = '/' . EVNMEDIAPATH . $row['EvnMediaData_FilePath'];
				return $row;
			})->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	
	/**
	*  Обработка загрузки файлов
	*  Входящие данные: файл + комментарий
	*  На выходе: данные о загруженных файлах в json-строке
	*/
	function uploadFile() {
		$data = $this->ProcessInputData('uploadFile', true);
		if ( $data === false ) { return false; }

		if ( !isset($_FILES['userfile']) ) {
			$this->ReturnError('Вы не выбрали файл для загрузки.', 701);
			return false;
		}

		$files = array();

		if( is_array($_FILES['userfile']['name']) )
		{
			foreach ($_FILES['userfile'] as $key => $array)
			{
				foreach ($array as $num => $value)
				{
					$files[$num][$key] = $value;
				}
			}

			$response = $this->dbmodel->uploadSeveralFiles($files, $data);

		} else if ( is_string($_FILES['userfile']['name']) )
		{
			$response = $this->dbmodel->uploadFile($_FILES['userfile'], $data);
		}
		
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}
	/**
	 * Description
	 */
	function saveChanges() {//перенос загруженных файлов из временной папки в папку для аплоада, а также схоранение информации в бд
		$response = array('success' => true, 'Error_Code' => '', 'Error_Msg' => '');
		$data = array();
		$evn_id = 0;

		// Получаем сессионные переменные
		$data = $this->ProcessInputData('saveChanges', true);
		if ($data === false) { return false; }

		if (isset($data['Evn_id']) && $data['Evn_id'] > 0) {
			$evn_id = $data['Evn_id'];
		} else {
			return false;
		}
		
		ConvertFromWin1251ToUTF8($data['changedData']);
		$dt = (array) json_decode($data['changedData']);		
		foreach($dt as $val) {
			$val = ((array) $val);
			$file_name = $val['EvnMediaData_FilePath'];
			array_walk($val, 'ConvertFromUTF8ToWin1251');
			$file_data = array(
				'orig_name' => $val['EvnMediaData_FileName'],
				'file_name' => $val['EvnMediaData_FilePath'],
				'Evn_id' => $evn_id,
				'description' => (!empty($val['EvnMediaData_Comment'])?$val['EvnMediaData_Comment']:''),
				'session' => $data['session'],
				'pmUser_id' => $data['pmUser_id']
			);
			//сохранение данных о файле в БД
			if ($val['state'] == 'add') {
				$res = $this->dbmodel->saveEvnMediaData($file_data);
				if ( !empty($res['Error_Msg']) ) {
					$response['success'] = false;					
					$response['Error_Code'] = $res['Error_Code'];
					$response['Error_Msg'] = $res['Error_Msg'];
					die(json_encode($response));
				}
			}
			else if ($val['state'] == 'delete') {
				if (
					$this->delFile(array(
						'id' => $val['EvnMediaData_id'],
						'file_name' => $file_name,
						'session' => $data['session']
					)) === false
				) {
					return false;
				}
			}
		}

		echo json_encode($response);
	}
	
	/**
	 * Description
	 */
	function saveFile($file_data) {

	}
	
	/**
	 * Description
	 */
	function remove() {
		$data = $this->ProcessInputData('remove', true);
		if ($data === false) {
			return false;
		}
		$data['id'] = $data['EvnMediaData_id'];
		if ($this->delFile($data)) {
			$val['Error_Code'] = '';
			$val['Error_Msg'] = '';
			$val['success'] = true;
			$this->ReturnData($val);
			return true;
		} else {
			return false;
		}
	}
	/**
	 * Удаление файла 
	 */
	public function deleteEvnMediaFile() {
		$data = $this->ProcessInputData('deleteEvnMediaFile', true);
		if ($data === false) {
			return false;
		}	
		
		$fileData = $this->dbmodel->getEvnMediaDataByNameAndId(array(
			'EvnMediaData_id'=>$data['EvnMediaData_id'],
			'fileName'=>$data['file_name']
		));
		
		if (!$this->dbmodel->isSuccessful($fileData) || !sizeof($fileData)) {
			return false;
		}
		
		if ($this->delFile(array(
			'id' => $data['EvnMediaData_id'],
			'session' => $data['session']
		))) {
			$val['Error_Code'] = '';
			$val['Error_Msg'] = '';
			$val['success'] = true;
			$this->ReturnData($val);
			return true;
		} else {
			return false;
		}
	}
	/**
	 * Description
	 */
	function deleteFile() {
		$data = $this->ProcessInputData('deleteFile', true);
		if ($data === false) {
			return false;
		}	
		if ($this->delFile($data)) {
			$val['Error_Code'] = '';
			$val['Error_Msg'] = '';
			$val['success'] = true;
			$this->ReturnData($val);
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Description
	 */
	private function delFile($data) {	
		if ($data['id'] > 0) {
			$data['EvnMediaData_id'] = $data['id'];
			$response = $this->dbmodel->getEvnMediaData($data);			
			$data['file'] = isset($response[0]) && isset($response[0]['EvnMediaData_FilePath']) ? $response[0]['EvnMediaData_FilePath'] : '';
		} else {
			$data['file'] = $data['file_name'];
		}
		
		// Проверяем корректность имени файла
		if ( !preg_match("/^([0-9a-z\.]+)$/i", $data['file']) ) {
			echo json_encode(array('success' => false, 'Error_Code' => 101 , 'Error_Msg' => toUTF('Имя файла имеет некорректный вид')));
			return false;
		}
			
		$filename = './'.EVNMEDIAPATH.$data['file'];

		if (!is_file($filename)) {				
			// Удаляем данные о файле из бд			
			$response = $this->dbmodel->deleteEvnMediaData($data);
			echo json_encode(array('success' => false, 'Error_Code' => 102 , 'Error_Msg' => toUTF('Файл не найден!')));
			return false;
		}

		if (!is_writable($filename)){
			echo json_encode(array('success' => false, 'Error_Code' => 103 , 'Error_Msg' => toUTF('Файл не может быть удален, т.к. нет прав на запись')));
			return false;
		}

		// Удаляем файл
		if (unlink($filename)) {				
			// Удаляем данные о файле из бд
			$response = $this->dbmodel->deleteEvnMediaData($data);
			return true;
		} else {
			echo json_encode(array('success' => false, 'Error_Code' => 104 , 'Error_Msg' => toUTF('Попытка удалить файл провалилась.')));
			return false;
		}
	}
	
	/**
	 * Description
	 */
	function loadEvnMediaFilesListGrid() {
		$data = $this->ProcessInputData('loadEvnMediaFilesListGrid', true);
		if ($data) {
			$response = $this->dbmodel->loadEvnMediaFilesListGrid($data);
			$this->ProcessModelList($response, true, true, 'При запросе возникла ошибка.', function ($row, $thas) {
				$row['EvnMediaData_FileLink'] = $thas->createLinks($row['EvnMediaData_FilePath'], $row['EvnMediaData_FileName']);
				return $row;
			})->ReturnData();
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Description
	 */
	function createLinks($path, $name) {
		if (strtolower(substr($name, -3)) == 'dcm') {
			//return '';
			//return '<a href="http://demo.swan.perm.ru:8080/weasis-start/?cdb=/weasis&obj=$name">'.$name.'</a> &raquo; <a href="javascript:Ext.getCmp("swDicomViewerWindow").show({link: "http://demo.swan.perm.ru:8080/dcm2img/?file=http://demo.swan.perm.ru/uploads/'.$name.'&format=png&h=600"})">Быстрый просмотр</a>';
			$http = (isset($_SERVER["HTTP_X_FORWARDED_PROTO"])?$_SERVER["HTTP_X_FORWARDED_PROTO"].'://':'http://');
			$ha = $http.$_SERVER["SERVER_NAME"].':'.$_SERVER["SERVER_PORT"];
			return '<a target="_blank" href="http://'.$_SERVER["SERVER_NAME"].':8080/weasis-start/?cdb=/weasis&obj=$name">'.$name.'</a> &raquo; <a target="_blank" href="http://'.$_SERVER["SERVER_NAME"].':8080/dcm2img/?file=http://192.168.36.61/uploads/'.$name.'&format=png">Быстрый просмотр</a>';
		} else {
			return '<a href="/'.EVNMEDIAPATH.$path.'" target="_blank">'.$name.'</a>';
		}
	}
	
	
	/**
	 * Функция выдачи файла с изначально сохраненным именем по EvnMediaData_id и fileName (чтобы все подряд файлы нельзя было смотреть)
	 * @return booleanъ
	 */
	public function getFile(){
		/**
		 * Функция выдачи файла
		 */
		function file_force_download($fileName, $fileAlias) {
			if (file_exists($fileName)) {
				// сбрасываем буфер вывода PHP, чтобы избежать переполнения памяти выделенной под скрипт
				// если этого не сделать файл будет читаться в память полностью!
				if (ob_get_level()) {
					ob_end_clean();
				}
				
				//В зависимости от расширения файла будем предлагать разные действия
				
				if ( getimagesize($fileName) !== FALSE ) {
					//Открываем в бразуере изображение
					$content_type = 'mime';
				} 
				else {
					$x = explode('.', $fileName);
					$ext = strtolower(end($x));
					
					if ($ext === 'pdf') {
						$content_type = 'application/pdf';
					} else {
						$content_type = 'application/x-file-to-save';//force-download //octet-stream
					}
					
				}
				
				
				header('Content-Description: File Transfer');
				header('Content-Type: '.$content_type);
				header("pragma: private");
				header('Content-Disposition: inline; filename=' . $fileAlias.';');
				header('Content-Transfer-Encoding: binary');
				header('Expires: 0');
				header('Cache-Control: must-revalidate');
				header('Content-Length: ' . filesize($fileName));
				// читаем файл и отправляем его пользователю
				if ($fd = fopen($fileName, 'rb')) {
					while (!feof($fd)) {
						print fread($fd, 1024);
					}
					fclose($fd);
				}
				exit;
			}
		}
		
		if (empty($_GET['EvnMediaData_id']) || empty($_GET['fileName'])) {
			return false;
		}
		
		$fileData = $this->dbmodel->getEvnMediaDataByNameAndId(array(
			'EvnMediaData_id'=>$_GET['EvnMediaData_id'],
			'fileName'=>$_GET['fileName']
		));
		
		if (!$this->dbmodel->isSuccessful($fileData) || !sizeof($fileData)) {
			return false;
		}
		
		$upload_path = './'.EVNMEDIAPATH;
		
		file_force_download($upload_path.$_GET['fileName'],$fileData[0]['EvnMediaData_FileName']);
		
		
		return true;
	}

	/**
	 *  Получение списка файлов в ЭМК
	 */
	function loadEvnMediaDataPanel() {
		$data = $this->ProcessInputData('loadEvnMediaDataPanel', true, true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadEvnMediaDataPanel($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * linkFilesToEvn
	 */
	function linkFilesToEvn()
	{
		$data = $this->ProcessInputData('linkFilesToEvn', false);


		$response = $this->dbmodel->linkFilesToEvn($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}
}
