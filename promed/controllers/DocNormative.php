<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * DocNormative - контроллер для работы с нормативными документами
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2015 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			23.03.2015
 *
 * @property DocNormative_model dbmodel
 */

class DocNormative extends swController {
	protected  $inputRules = array(
		'loadDocNormativeList' => array(
			array('field' => 'query', 'label' => 'Строка поиска', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'DocNormative_id', 'label' => 'Идентификатор нормативного документа', 'rules' => '', 'type' => 'id'),
			array('field' => 'DocNormativeType_id', 'label' => 'Идентификатор типа нормативного документа', 'rules' => '', 'type' => 'id'),
		),
		'uploadDocNormativeFile' => array(
			array('field' => 'File', 'label' => 'Файл нормативного документа', 'rules' => '', 'type' => 'string'),
		),
		'loadDocNormativeGrid' => array(
			array('field' => 'DocNormativeType_id', 'label' => 'Тип нормативного документа', 'rules' => '', 'type' => 'id'),
			array('field' => 'DocNormative_Editor', 'label' => 'Издатель нормативного документа', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'DocNormative_Num', 'label' => 'Номер нормативного документа', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'DocNormative_Name', 'label' => 'Наименование нормативного документа', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'DocNormative_DateRange', 'label' => 'Период действия нормативного документа', 'rules' => 'trim', 'type' => 'daterange'),
			array('field' => 'start', 'label' => 'Начало', 'rules' => '', 'type' => 'int', 'default' => 0),
			array('field' => 'limit', 'label' => 'Количество', 'rules' => '', 'type' => 'int', 'default' => 100),
		),
		'loadDocNormativeForm' => array(
			array('field' => 'DocNormative_id', 'label' => 'Идентификатор нормативного документа', 'rules' => 'required', 'type' => 'id'),
		),
		'saveDocNormative' => array(
			array('field' => 'DocNormative_id', 'label' => 'Идентификатор нормативного документа', 'rules' => '', 'type' => 'id'),
			array('field' => 'DocNormativeType_id', 'label' => 'Тип нормативного документа', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'DocNormative_Editor', 'label' => 'Издатель нормативного документа', 'rules' => 'required|trim', 'type' => 'string'),
			array('field' => 'DocNormative_Num', 'label' => 'Номер нормативного документа', 'rules' => 'required|trim', 'type' => 'string'),
			array('field' => 'DocNormative_begDate', 'label' => 'Дата начала действия нормативного документа', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'DocNormative_endDate', 'label' => 'Дата окончания действия нормативного документа', 'rules' => '', 'type' => 'date'),
			array('field' => 'DocNormative_Name', 'label' => 'Наименование нормативного документа', 'rules' => 'required|trim', 'type' => 'string'),
			array('field' => 'DocNormative_File', 'label' => 'Файл нормативного документа', 'rules' => '', 'type' => 'string'),
		),
	);

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model('DocNormative_model', 'dbmodel');
	}

	/**
	 * Получение списка нормативных докуметнов
	 */
	function loadDocNormativeList() {
		$data = $this->ProcessInputData('loadDocNormativeList', false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadDocNormativeList($data);

		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Закрузка файла для прикрепления к нормативному документу
	 */
	function uploadDocNormativeFile() {
		$data = $this->ProcessInputData('uploadDocNormativeFile', false);
		if ($data === false) { return false; }

		$upload_path = IMPORTPATH_ROOT.'doc_normative_files/';

		if (!isset($_FILES['File'])) {
			$this->ReturnData( array('success' => false, 'Error_Code' => 100011 , 'Error_Msg' => toUTF('Не выбран файл нормативного документа!') ) ) ;
			return false;
		}

		if (!is_uploaded_file($_FILES['File']['tmp_name']))
		{
			$error = (!isset($_FILES['File']['error'])) ? 4 : $_FILES['File']['error'];
			switch($error)
			{
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
					$message = 'При загрузке файла произошла ошибка.';
					break;
			}
			$this->ReturnData(array('success' => false, 'Error_Code' => 100012 , 'Error_Msg' => toUTF($message)));
			return false;
		}

		// Правильно ли указана директория для загрузки?
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
			$this->ReturnData(array('success' => false, 'Error_Code' => 100014 , 'Error_Msg' => toUTF('Путь для загрузки файлов некорректен.')));
			return false;
		}

		// Имеет ли директория для загрузки права на запись?
		if (!is_writable($upload_path)) {
			$this->ReturnData( array('success' => false, 'Error_Code' => 100015 , 'Error_Msg' => toUTF('Загрузка файла не возможна из-за прав пользователя.')));
			return false;
		}

		$file_name = $_FILES['File']['name'];

		if ( move_uploaded_file ($_FILES['File']['tmp_name'], $upload_path.$file_name)) {
			$val['DocNormative_File'] = $upload_path.$file_name;
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
	 * Получение списка нормативных документов
	 */
	function loadDocNormativeGrid() {
		$data = $this->ProcessInputData('loadDocNormativeGrid', false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadDocNormativeGrid($data);

		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение данных для редактирования нормативного документа
	 */
	function loadDocNormativeForm() {
		$data = $this->ProcessInputData('loadDocNormativeForm', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadDocNormativeForm($data);

		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Сохранение нормативного документа
	 */
	function saveDocNormative() {
		$data = $this->ProcessInputData('saveDocNormative', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveDocNormative($data);

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}
}
