<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* PersonMediaData - контроллер работы с файлами человека
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*-
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @author       Пермяков Александр
* @version      декабрь 2010 года
*/

class PersonMediaData extends swController {

	private $model_name = 'PersonMediaData_model';

	function __construct() {
		parent::__construct();

		$this->inputRules = array(
			'getMediaData' => array (
				array('field' => 'PersonMediaData_id','label' => 'Идентификатор файла','rules' => 'required','type' => 'id')
			),
			'uploadPersonPhoto' => array (
				array('field' => 'Person_id','label' => 'Идентификатор человека','rules' => 'required','type' => 'id')
			)
		);
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
		$this->load->model($this->model_name, 'dbmodel');
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
		$this->load->model($this->model_name, 'dbmodel');
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
		$file_data['PersonMediaData_FileName'] = $_FILES['userfile']['name'];
		$file_data['PersonMediaData_FilePath'] = $data['Person_id'].'.'.$file_data['file_ext'];
		$file_path = $person_files_path . $file_data['PersonMediaData_FilePath'];
		$file_data['PersonMediaData_Comment'] = '';
		$file_data['Person_id'] = $data['Person_id'];
		$file_data['pmUser_id'] = $data['pmUser_id'];
		
		//Если файл уже существует, он будет перезаписан.
		if ( move_uploaded_file ($_FILES['userfile']['tmp_name'], $file_path ))
		{
			// добавлять или обновлять?
			$response = $this->dbmodel->getPersonPhoto(array('Person_id' => $data['Person_id']));
			if(is_array($response))
			{
				if (count($response) > 0 && !empty($response[0]['PersonMediaData_id']))
				{
					$file_data['PersonMediaData_id'] = $response[0]['PersonMediaData_id'];
				}
			}
			else
			{
				$this->ReturnError('Ошибка запроса к БД при проверке наличия фотографии человека!', 776);
				return false;
			}
			//сохранение данных о файле в БД
			$response = $this->dbmodel->savePersonMediaData($file_data);
			if (!empty($response['Error_Msg']))
			{
				$this->ReturnError($response['Error_Msg'], 777);
				return false;
			}
			// создаем миниатюру фотографии
			$person_thumbs_path = $this->dbmodel->getPathPersonThumbs($data, true);
			$thumb_name = $person_thumbs_path . $file_data['PersonMediaData_FilePath'];
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
			$val['data'] = array('person_thumbs_src' => $this->dbmodel->getUrlPersonThumbs($data) . $file_data['PersonMediaData_FilePath']);
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
