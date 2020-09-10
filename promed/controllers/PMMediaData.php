<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* pmMediaData - контроллер для работы с прикрепленными файлами
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Parka
* @access       public
* @copyright    Copyright (c) 2009-2011 Swan Ltd.
* @author       Salakhov Rustam (копипаст EvnMediaFiles с доработками под иную структуру таблицы)
* @version      10.08.2012
*/

class PMMediaData extends swController {
	/**
	 * Description
	 */
	function __construct() {
		parent::__construct();
		$this->inputRules = array(
			'uploadFile' => array (
				array('field' => 'ObjectName','label' => 'Наименование объекта','rules' => '','type' => 'string'),
				array('field' => 'ObjectID','label' => 'Идентификатор объекта','rules' => '','type' => 'id'),
				array('field' => 'FileDescr','label' => 'Примечание','length' => 255,'rules' => '','type' => 'string'),
				array('field' => 'saveOnce','label' => 'Признак немедленного сохранения','rules' => '','type' => 'string')
			),
			'saveChanges' => array (
				array('field' => 'ObjectName','label' => 'Наименование объекта','rules' => '','type' => 'string'),
				array('field' => 'ObjectID','label' => 'Идентификатор объекта','rules' => '','type' => 'id'),
				array('field' => 'changedData','label' => 'Мнформация о измененных файлах','rules' => '','type' => 'string')
			),
			'deleteFile' => array (
				array('field' => 'id','label' => 'Идентификатор файла в базе данных','rules' => '','type' => 'id')
			),
			'loadpmMediaDataListGrid' => array (
				array('field' => 'ObjectName','label' => 'Наименование объекта','rules' => '','type' => 'string'),
				array('field' => 'ObjectID','label' => 'Идентификатор объекта','rules' => '','type' => 'id')
			),
			
			///////
			'getMediaData' => array (
				array('field' => 'PersonMediaData_id','label' => 'Идентификатор файла','rules' => 'required','type' => 'id')
			),
			'uploadPersonPhoto' => array (
				array('field' => 'Person_id','label' => 'Идентификатор человека','rules' => 'required','type' => 'id')
			)
		);
	}

	/**
	*  Обработка загрузки файлов
	*  Входящие данные: файл + комментарий
	*  На выходе: данные о загруженных файлах в json-строке
	*/
	function uploadFile() {	
		$this->load->database();
		$this->load->model('PMMediaData_model', 'dbmodel');
		
		$upload_path = './'.PMMEDIAPATH;
		$allowed_types = explode('|','pdf|xls|xlsx|xl|txt|rtf|word|doc|docx|jpg|jpe|jpeg|png|bmp|tiff|tif|gif|dcm|odt|ods');

		$data = array();
		$file_data = array();
		$val  = array();
			
		// Получаем сессионные переменные
		$data = array_merge($data, getSessionParams());	
		$err = getInputParams($data, $this->inputRules['uploadFile'], false);
		
		if ( strlen($err) > 0 ) {
			echo json_return_errors($err);
			return false;
		}
		
		// $_FILES['userfile'] установлен?
		if ( ! isset($_FILES['userfile']))
		{
			echo json_encode( array('success' => false, 'Error_Code' => 701 , 'Error_Msg' => toUTF('Вы не выбрали файл для загрузки.') ) );
			return false;
		}
		// Файл загружен?
		if ( ! is_uploaded_file($_FILES['userfile']['tmp_name']))
		{
			$error = ( ! isset($_FILES['userfile']['error'])) ? 4 : $_FILES['userfile']['error'];
			switch($error) {
				case 1:
					$message = 'Загружаемый файл превышает максимально допустимый размер, определённый в вашем файле конфигурации PHP.';
					break;
				case 2:
					$message = 'Загружаемый файл превышает максимально допустимый размер, заданный формой.';
					break;
				case 3:
					$message = 'Этот файл был загружен не полностью.';
					break;
				case 4:
					$message = 'Вы не выбрали файл для загрузки.';
					break;
				case 6:
					$message = 'Временная директория не найдена.';
					break;
				case 7:
					$message = 'Файл не может быть записан на диск.';
					break;
				case 8:
					$message = 'Неверный формат файла.';
					break;
				default :
					$message = 'Вы не выбрали файл для загрузки.';
					break;
			}
			echo json_encode( array('success' => false, 'Error_Code' => 702 , 'Error_Msg' => toUTF($message) ) );
			return false;
		}

		// Тип файла разрешен к загрузке?
		$x = explode('.', $_FILES['userfile']['name']);
		$file_data['file_ext'] = end($x);
		if ( ! in_array(strtolower($file_data['file_ext']), $allowed_types) ) {
			echo json_encode( array('success' => false, 'Error_Code' => 703 , 'Error_Msg' => toUTF('Вы пытаетесь загрузить запрещенный тип файла.') ) );
			return false;
		}

		// Правильно ли указана директория для загрузки?
		if (!@is_dir($upload_path)) {
			@mkdir($upload_path);
		}
		if (!@is_dir($upload_path)) {
			echo json_encode(array('success' => false, 'Error_Code' => 704, 'Error_Msg' => toUTF('Путь для загрузки файлов некорректен.')));
			return false;
		}

		// Имеет ли директория для загрузки права на запись?
		if ( ! is_writable($upload_path)) {
			echo json_encode( array('success' => false, 'Error_Code' => 705 , 'Error_Msg' => toUTF('Директория, в которую загружается файл не имеет прав на запись.') ) );
			return false;
		}

		// Подготовка данных о файле
		$file_data['orig_name'] = $_FILES['userfile']['name'];
		$file_data['file_size'] = $_FILES['userfile']['size'];
		$file_data['ObjectName'] = $data['ObjectName'];
		$file_data['ObjectID'] = $data['ObjectID'];
		$file_data['description'] = !empty($data['FileDescr']) ? $data['FileDescr'] : '';
		$file_data['pmUser_id'] = $data['pmUser_id'];
		$file_data['file_name'] = md5($file_data['orig_name'].time()).'.'.$file_data['file_ext'];
		
		if ( move_uploaded_file ($_FILES['userfile']['tmp_name'], $upload_path.$file_data['file_name'])) {
			if ($data['saveOnce'] && $data['saveOnce'] == 'true') {			
				//сохранение данных о файле в БД
				$response = $this->dbmodel->savepmMediaData($file_data);
				if (!empty($response['Error_Msg']))
				{
					echo json_encode( array('success' => false, 'Error_Code' => 777 , 'Error_Msg' => toUTF($response['Error_Msg']) ) );
					return false;
				}
			}
			array_walk($file_data, 'ConvertFromWin1251ToUTF8');		
			$files_data[] = $file_data;
			$val['data'] = json_encode($files_data);
			$val['Error_Code'] = '';
			$val['Error_Msg'] = '';
			$val['success'] = true;
			$this->ReturnData($val);
			return true;
		} else {
			echo json_encode( array('success' => false, 'Error_Code' => 706 , 'Error_Msg' => toUTF('Невозможно скопировать файл в указанное место после его загрузки.') ) );
			return false;
		}
	}
	
	/**
	 * Description
	 */
	function saveChanges() {//перенос загруженных файлов из временной папки в папку для аплоада, а также схоранение информации в бд
		$this->load->database();
		$this->load->model('PMMediaData_model', 'dbmodel');

		$response = array('success' => true, 'Error_Code' => '', 'Error_Msg' => '');
		$data = array();
		$ObjectName = '';
		$ObjectID = 0;

		// Получаем сессионные переменные
		$data = array_merge($data, getSessionParams());		
		$err  = getInputParams($data, $this->inputRules['saveChanges']);
		
		if (isset($data['ObjectName']) && $data['ObjectName'] != '' && isset($data['ObjectID']) && $data['ObjectID'] > 0) {
			$ObjectName = $data['ObjectName'];
			$ObjectID = $data['ObjectID'];
		} else {
			return false;
		}
		
		ConvertFromWin1251ToUTF8($data['changedData']);
		$dt = (array) json_decode($data['changedData']);	
		foreach($dt as $val) {
			$val = ((array) $val);
			$file_name = $val['pmMediaData_FilePath'];
			array_walk($val, 'ConvertFromUTF8ToWin1251');
			$file_data = array(
				'orig_name' => $val['pmMediaData_FileName'],
				'file_name' => $val['pmMediaData_FilePath'],
				'ObjectName' => $ObjectName,
				'ObjectID' => $ObjectID,
				'description' => $val['pmMediaData_Comment'],
				'pmUser_id' => $data['pmUser_id']
			);
			//сохранение данных о файле в БД
			if ($val['state'] == 'add') {
				$res = $this->dbmodel->savepmMediaData($file_data);
				if ($res['Error_Msg'] != '') {
					$response['success'] = false;					
					$response['Error_Code'] = $res['Error_Code'];
					$response['Error_Msg'] = $res['Error_Msg'];
				}
			}
			if ($val['state'] == 'delete') {
				$this->delFile(array(
					'id' => $val['pmMediaData_id'],
					'file_name' => $file_name
				));
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
	function deleteFile() {
		$this->load->database();
		$this->load->model('PMMediaData_model', 'dbmodel');

		$data = array();
		$val  = array();

		// Получаем сессионные переменные
		$data = array_merge($data, getSessionParams());		
		$err  = getInputParams($data, $this->inputRules['deleteFile']);
		
		if ( strlen($err) > 0 ) {
			echo json_return_errors($err);
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
			$data['pmMediaData_id'] = $data['id'];
			$response = $this->dbmodel->getpmMediaData($data);			
			$data['file'] = isset($response[0]) && isset($response[0]['pmMediaData_FilePath']) ? $response[0]['pmMediaData_FilePath'] : '';
		} else {
			$data['file'] = $data['file_name'];
		}
		
		// Проверяем корректность имени файла
		if ( !preg_match("/^([0-9a-z\.]+)$/i", $data['file']) ) {
			echo json_encode(array('success' => false, 'Error_Code' => 101 , 'Error_Msg' => toUTF('Имя файла имеет некорректный вид')));
			return false;
		}
			
		$filename = './'.PMMEDIAPATH.$data['file'];

		if (!is_file($filename)) {				
			// Удаляем данные о файле из бд			
			$response = $this->dbmodel->deletepmMediaData($data);
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
			$response = $this->dbmodel->deletepmMediaData($data);
			return true;
		} else {
			echo json_encode(array('success' => false, 'Error_Code' => 104 , 'Error_Msg' => toUTF('Попытка удалить файл провалилась.')));
			return false;
		}		
	}
	
	/**
	 * Description
	 */
	function loadpmMediaDataListGrid() {
		$this->load->database();
		$this->load->model('PMMediaData_model', 'dbmodel');

		$data = array();
		$val  = array();

		// Получаем сессионные переменные
		$data = array_merge($data, getSessionParams());		
		$err  = getInputParams($data, $this->inputRules['loadpmMediaDataListGrid']);
		
		if ( strlen($err) > 0 ) {
			echo json_return_errors($err);
			return false;
		}

		$response = $this->dbmodel->loadpmMediaDataListGrid($data);

		if (is_array($response)) {
			foreach ($response as $row) {
				array_walk($row, 'ConvertFromWin1251ToUTF8');
				$row['pmMediaData_FileLink'] = $this->createLinks($row['pmMediaData_FilePath'], $row['pmMediaData_FileName']);
				$val[] = $row;
			}
		}

		$this->ReturnData($val);
		return true;
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
			return '<a target="_blank" href="http://'.$_SERVER["SERVER_NAME"].':8080/weasis-start/?cdb=/weasis&obj=$name">'.$name.'</a> &raquo; <a target="_blank"
			href="http://'.$_SERVER["SERVER_NAME"].':8080/dcm2img/?file=http://192.168.36.61/uploads/'.$name.'&format=png">Быстрый просмотр</a>';
		} else {
			return '<a href="/'.PMMEDIAPATH.$path.'" target="_blank">'.$name.'</a>';
		}
	}
	
	/**
	* Отдает указанный файл или html-строку в случае ошибки.
	* Возвращается файл, к примеру, тот же самый, как если бы было обращение /uploads/persons/532423/532423.jpg
	* Но при этом можно контролировать доступ к файлу
	*/
	function getMediaData() {
		/*
		ob_start();
		пока у ProcessInputData проблемы с проверкой параметров в $_GET
		$data = $this->ProcessInputData('getMediaData', false);
		if ($data == false)
		{
			//перехватываем поток и выводим ошибку по человечески в html-строке
			$contents = ob_get_contents();
			ob_end_clean();
			echo 'Ошибка запроса';
			return false;
		}
		*/
		$data = array();
		if (empty($_GET['PersonMediaData_id']) || !is_numeric($_GET['PersonMediaData_id']))
		{
			echo 'В запросе отсутствует идентификатор файла или он неправильный.';
			return false;
		}
		ob_start();
		$data['PersonMediaData_id'] = $_GET['PersonMediaData_id'];
		$this->load->database();
		$this->load->model('PMMediaData_model', 'dbmodel');
		$response = $this->dbmodel->getMediaData($data);
		//var_dump($response);
		if(is_array($response) && count($response) > 0)
		{
			//проверить существование файла
			$path = $this->dbmodel->getPathPersonFiles($response[0]);
			$file_name = $path.$response[0]['PersonMediaData_FilePath'];
			if(file_exists($file_name))
			{
				ob_end_clean();
				header('Content-type: '.mime_content_type($file_name)); //extension=php_mime_magic.dll
				echo file_get_contents($file_name);
				return true;
			}
			else
			{
				$contents = ob_get_contents();
				ob_end_clean();
				header("HTTP/1.0 404 Not Found"); 
				header("HTTP/1.1 404 Not Found"); 
				header("Status: 404 Not Found"); 
				die;
				/*
				echo 'файл не найден';
				echo $contents;
				*/
				return false;
			}
		}
		else
		{
			$contents = ob_get_contents();
			ob_end_clean();
			header("HTTP/1.0 404 Not Found"); 
			header("HTTP/1.1 404 Not Found"); 
			header("Status: 404 Not Found");
			die;
			/*
			echo 'запись о файле не найдена';
			echo $contents;
			*/
			return false;
		}
	}

	/**
	* Сохраняет фото человека для отображения в сигнальной информации. 
	*/
	function uploadPersonPhoto() {
		$this->load->database();
		$this->load->model('PMMediaData_model', 'dbmodel');
		//подключаем библиотеку для работы с файлами

		$data = $this->ProcessInputData('uploadPersonPhoto', true);
		if ($data == false)
		{
			return false;
		}
		
		//(GD не поддерживает GIF-формат, потому что алгоритм сжатия, применяемый там, находится под защитой авторских прав)
		$allowed_types = explode('|','jpg|jpe|jpeg|png');//|gif
		$max_size = 1048578; //1 Mb
		$file_data = array();

		// $_FILES['userfile'] установлен?
		if ( ! isset($_FILES['userfile']))
		{
			$this->ReturnError('Вы не выбрали файл для загрузки.', 701);
			return false;
		}
		// Файл загружен?
		if ( ! is_uploaded_file($_FILES['userfile']['tmp_name']))
		{
			$error = ( ! isset($_FILES['userfile']['error'])) ? 4 : $_FILES['userfile']['error'];
			switch($error) {
				case 1:
					$message = 'Загружаемый файл превышает максимально допустимый размер, определённый в вашем файле конфигурации PHP.';
					break;
				case 2:
					$message = 'Загружаемый файл превышает максимально допустимый размер, заданный формой.';
					break;
				case 3:
					$message = 'Этот файл был загружен не полностью.';
					break;
				case 4:
					$message = 'Вы не выбрали файл для загрузки.';
					break;
				case 6:
					$message = 'Временная директория не найдена.';
					break;
				case 7:
					$message = 'Файл не может быть записан на диск.';
					break;
				case 8:
					$message = 'Неверный формат файла.';
					break;
				default :
					$message = 'Вы не выбрали файл для загрузки.';
					break;
			}
			$this->ReturnError($message, 702);
			return false;
		}

		// Проверка размера файла
		if ( $_FILES['userfile']['size'] > $max_size )
		{
			$this->ReturnError('Размер файла превышает установленный максимальный размер в '. ($max_size/1024) .'кб.', 761);
			return false;
		}

		// Тип файла разрешен к загрузке?
		$x = explode('.', $_FILES['userfile']['name']);
		$file_data['file_ext'] = strtolower(end($x));
		if ( ! in_array($file_data['file_ext'], $allowed_types) ) {
			$this->ReturnError('Вы пытаетесь загрузить запрещенный тип файла.', 703);
			return false;
		}

		$person_files_path = $this->dbmodel->getPathPersonFiles($data, true);
		// Правильно ли указана директория для загрузки?
		if ( ! @is_dir($person_files_path)) {
			$this->ReturnError('Путь для загрузки файлов некорректен.', 704);
			return false;
		}

		// Имеет ли директория для загрузки права на запись?
		if ( ! is_writable($person_files_path)) {
			$this->ReturnError('Директория, в которую загружается файл не имеет прав на запись.', 705);
			return false;
		}

		// Подготовка данных о файле
		$file_data['orig_name'] = $_FILES['userfile']['name'];
		$file_data['file_name'] = $data['Person_id'].'.'.$file_data['file_ext'];
		$file_path = $person_files_path . $file_data['file_name'];
		$file_data['description'] = '';
		
		$file_data['ObjectID'] = $data['Person_id'];
		$file_data['ObjectName'] = 'PersonPhoto';
		$file_data['pmUser_id'] = $data['pmUser_id'];
		
		//Если файл уже существует, он будет перезаписан.
		if ( move_uploaded_file ($_FILES['userfile']['tmp_name'], $file_path ))
		{
			// добавлять или обновлять?
			$response = $this->dbmodel->getpmMediaData(array('ObjectID' => $data['Person_id']));
			if(is_array($response))
			{
				if (count($response) > 0 && !empty($response[0]['pmMediaData_id']))
				{
					$file_data['pmMediaData_id'] = $response[0]['pmMediaData_id'];
				}
			}
			else
			{
				$this->ReturnError('Ошибка запроса к БД при проверке наличия фотографии человека!', 776);
				return false;
			}
			//сохранение данных о файле в БД
			$response = $this->dbmodel->savepmMediaData($file_data);
			if (!empty($response['Error_Msg']))
			{
				$this->ReturnError($response['Error_Msg'], 777);
				return false;
			}
			// создаем миниатюру фотографии
			$person_thumbs_path = $this->dbmodel->getPathPersonThumbs($data, true);
			$thumb_name = $person_thumbs_path . $file_data['file_name'];
			$thumb_w = 68;
			$thumb_h = 106;
			$thumb_pr = $thumb_w/$thumb_h;
			$thumb_s = $thumb_w*$thumb_h;
			$thumb_q = 100;// качество сжатия jpeg
			$is_png = ('png' == $file_data['file_ext']);
			$img = ($is_png)?imagecreatefrompng($file_path):imagecreatefromjpeg($file_path);
			$img_w = imagesx($img); 
			$img_h = imagesy($img);
			$img_pr = $img_w/$img_h;
			$img_s = $img_w*$img_h;
			$thumb = imagecreatetruecolor($thumb_w,$thumb_h);
			if($img_s != $thumb_s)
			{
				// размеры отличаются
				if($img_pr == $thumb_pr)
				{
					//масштабирование без вырезок
					imagecopyresampled($thumb, $img, 0, 0, 0, 0, $thumb_w, $thumb_h, $img_w, $img_h); 
				}
				else if($img_pr > $thumb_pr)
				{
					// вырезаем серединку по x, если фото более горизонтальное, чем миниатюра
					$km = $img_h/$thumb_h;
					$img_w_new = round($km*$thumb_w);
					imagecopyresampled($thumb, $img, 0, 0, round(($img_w-$img_w_new)/2), 0, $thumb_w, $thumb_h, $img_w_new, $img_h); 
				}
				else
				{
					// вырезаем серединку по y, если фото более вертикальное, чем миниатюра
					$km = $img_w/$thumb_w;
					$img_h_new = round($km*$thumb_h);
					imagecopyresampled($thumb, $img, 0, 0, 0, round(($img_h-$img_h_new)/2), $thumb_w, $thumb_h, $img_w, $img_h_new); 
				}
			}
			else
			{
				//размер $img совпадает с размерами $thumb
				$thumb = $img;
			}
			if($is_png)
			{
				imagepng($thumb,$thumb_name);
			}
			else
			{
				imagejpeg($thumb,$thumb_name,$thumb_q);
			}
			imagedestroy($img);
			imagedestroy($thumb);
			
			//array_walk($file_data, 'ConvertFromWin1251ToUTF8');
			$val = array();
			$val['data'] = array('person_thumbs_src' => $this->dbmodel->getUrlPersonThumbs($data) . $file_data['file_name']);
			$val['Error_Code'] = '';
			$val['Error_Msg'] = '';
			$val['success'] = true;
			$this->ReturnData($val);
			return true;
		}
		else
		{
			$this->ReturnError('Невозможно скопировать файл в указанное место после его загрузки.', 706);
			return false;
		}
	}
	
}
