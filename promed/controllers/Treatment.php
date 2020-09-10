<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Treatment - журнал регистрации обращений: жалобы, предложения, благодарности
* Заодно тут же работа с редактируемыми справочниками журнала
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Promed
* @access       public
* @copyright    Copyright (c) 2010 Swan Ltd.
* @author       Permyakov Alexander <permjakov-am@mail.ru>
* @version      19.07.2010
*/

class Treatment extends swController {
	public $inputRules = array(
		'saveTreatment' => array(
			array(
				'field' => 'Treatment_id',
				'label' => 'Идентификатор обращения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Treatment_Reg',
				'label' => 'Номер регистрации',
				'length' => 50,
				'rules' => 'trim|required',
				'type' => 'string'
			),
			array(
				'field' => 'Treatment_DateReg',
				'label' => 'Дата регистрации',
				'rules' => 'trim|required',
				'type' => 'date'
			),
			array(
				'field' => 'TreatmentUrgency_id',
				'label' => 'Срочность обращения',
				'rules' => 'trim|required',
				'type' => 'id'
			),
			array(
				'field' => 'TreatmentMultiplicity_id',
				'label' => 'Кратность обращения',
				'rules' => 'trim|required',
				'type' => 'id'
			),
			array(
				'field' => 'TreatmentSenderType_id',
				'label' => 'Тип инициатора',
				'rules' => 'trim|required',
				'type' => 'id'
			),
			array(
				'field' => 'Treatment_SenderDetails',
				'label' => 'Инициатор обращения',
				'length' => 255,
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Инициатор обращения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'TreatmentType_id',
				'label' => 'Тип обращения',
				'rules' => 'trim|required',
				'type' => 'id'
			),
			array(
				'field' => 'TreatmentCat_id',
				'label' => 'Категория обращения',
				'rules' => 'trim|required',
				'type' => 'id'
			),
			array(
				'field' => 'TreatmentRecipientType_id',
				'label' => 'Адресат обращения',
				'rules' => 'trim|required',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_rid',
				'label' => 'Адресат обращения (ЛПУ)',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'TreatmentSubjectType_id',
				'label' => 'Тип субъекта обращения',
				'rules' => 'trim|required',
				'type' => 'id'
			),
			array(
				'field' => 'Org_sid',
				'label' => 'Организация (субъект)',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonal_sid',
				'label' => 'Врач (субъект)',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonal_Lpu_sid',
				'label' => 'Место работы врача',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_sid',
				'label' => 'ЛПУ (субъект)',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'TreatmentMethodDispatch_id',
				'label' => 'Способ получения обращения',
				'rules' => 'trim|required',
				'type' => 'id'
			),
			array(
				'field' => 'Treatment_Text',
				'label' => 'Текст сообщения',
				'length' => 8000,
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Treatment_Document',
				'label' => 'Прикрепить документ(ы)',
				'length' => 4000,
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Treatment_Comment',
				'label' => 'Примечание',
				'length' => 8000,
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'TreatmentReview_id',
				'label' => 'Результат рассмотрения',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'Treatment_DateReview',
				'label' => 'Дата рассмотрения',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'Treatment_SenderPhone',
				'label' => 'Телефон инициатора',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'TreatmentFeedback_Document',
				'label' => 'Прикрепить документ(ы)',
				'length' => 4000,
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'TreatmentFeedback_Message',
				'label' => 'Текст сообщения',
				'length' => 8000,
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'TreatmentFeedback_Note',
				'label' => 'Текст сообщения',
				'length' => 8000,
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'TreatmentMethodDispatch_fid',
				'label' => 'Способ получения обращения',
				'rules' => '',
				'type' => 'id'
			)
		),
		'saveTreatmentFeedback' => array (
			array(
				'field' => 'Treatment_id',
				'label' => 'Идентификатор обращения',
				'rules' => 'trim|required',
				'type' => 'id'
			),
			array(
				'field' => 'TreatmentFeedback_Document',
				'label' => 'Прикрепить документ(ы)',
				'length' => 4000,
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'TreatmentFeedback_Message',
				'label' => 'Текст сообщения',
				'length' => 8000,
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'TreatmentFeedback_Note',
				'label' => 'Текст сообщения',
				'length' => 8000,
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'TreatmentMethodDispatch_id',
				'label' => 'Способ получения обращения',
				'rules' => '',
				'type' => 'id'
			)
		),
		'delTreatment' => array (
			array(
				'field' => 'Treatment_id',
				'label' => 'Идентификатор обращения',
				'rules' => 'trim|required',
				'type' => 'id'
			)
		),
		'setStatusTreatment' => array (
			array(
				'field' => 'Treatment_id',
				'label' => 'Идентификатор обращения',
				'rules' => 'trim|required',
				'type' => 'id'
			),
			array(
				'field' => 'TreatmentReview_id',
				'label' => 'Результат рассмотрения',
				'rules' => 'trim',
				'type' => 'id'
			),
		),
		'getTreatment' => array (
			array('field' => 'Treatment_id','label' => 'Идентификатор обращения','rules' => 'trim|required','type' => 'id')
		),
		'getTreatmentReportForm' => array (
			array('field' => 'node','label' => '','rules' => '','type' => 'string')
		),
		'uploadFile' => array (
			/*array('field' => 'Object','label' => 'Object','length' => 255,'rules' => '','type' => 'string'),*/
			array('field' => 'FileDescr','label' => 'Примечание','length' => 255,'rules' => '','type' => 'string'),
			array('field' => 'FilesData','label' => 'Json-строка с данными прикрепленных файлов','length' => 4000,'rules' => '','type' => 'string')
		),
		'deleteFile' => array (
			array('field' => 'file','label' => 'Имя файла на сервере','length' => 255,'rules' => 'required','type' => 'string'),
			array('field' => 'data','label' => 'Json-строка с данными прикрепленных файлов','length' => 4000,'rules' => 'required','type' => 'string')
		),
		'getTreatmentList' => array (
			array('default' => 100,'field' => 'limit','label' => 'Лимит записей','rules' => '','type' => 'int'),
			array('default' => 0,'field' => 'start','label' => 'Начальная запись','rules' => '','type' => 'int'),
			array('default' => 0,'field' => 'TreatmentType_id','label' => 'Тип обращения','rules' => 'trim','type' => 'id'),
			array('field' => 'Treatment_DateReg','label' => 'Дата регистрации','rules' => 'trim','type' => 'string')
		),
		'printTreatment' => array (
			array(
				'field' => 'Treatment_id',
				'label' => 'Идентификатор обращения',
				'rules' => '',
				'type' => 'id'
			)
		),
		'getTreatmentReport' => array (
			array('default' => '','field' => 'node','label' => '','rules' => 'required','type' => 'string'),
			array('field' => 'Treatment_DateReg_Start','label' => 'Дата регистрации с (начало диапазона)','rules' => 'trim|required','type' => 'date'),
			array('field' => 'Treatment_DateReg_End','label' => 'Дата регистрации по (конец диапазона)','rules' => 'trim|required','type' => 'date'),
			array('field' => 'Lpu_sid', 'label' => 'ЛПУ (субъект)', 'rules' => 'trim', 'type' => 'id'),
			array('field' => 'TreatmentMethodDispatch_id','label' => 'Способ получения обращения','rules' => '','type' => 'id'),
			array('field' => 'TreatmentMultiplicity_id','label' => 'Кратность обращения','rules' => '','type' => 'id'),
			array('field' => 'TreatmentType_id','label' => 'Тип обращения','rules' => '','type' => 'id'),
			array('field' => 'TreatmentCat_id','label' => 'Категория обращения','rules' => '','type' => 'id'),
			array('field' => 'TreatmentRecipientType_id','label' => 'Адресат обращения','rules' => '','type' => 'id'),
			array('field' => 'TreatmentReview_id','label' => 'Результат рассмотрения','rules' => '','type' => 'id'),
			array('field' => 'Treatment_DateReview_Start','label' => 'Дата рассмотрения с (начало диапазона)','rules' => 'trim','type' => 'date'),
			array('field' => 'Treatment_DateReview_End','label' => 'Дата рассмотрения по (конец диапазона)','rules' => 'trim','type' => 'date')			
		),
		'getTreatmentSearchList' => array (
			array('default' => 0,'field' => 'pmUser','label' => 'Создатель','rules' => '','type' => 'id'),
			array('default' => 100,'field' => 'limit','label' => 'Лимит записей','rules' => '','type' => 'int'),
			array('default' => 0,'field' => 'start','label' => 'Начальная запись','rules' => '','type' => 'int'),
			array('default' => '','field' => 'Treatment_DateReg_Start','label' => 'Дата регистрации с (начало диапазона)','rules' => 'trim','type' => 'date'),
			array('default' => '','field' => 'Treatment_DateReg_End','label' => 'Дата регистрации по (конец диапазона)','rules' => 'trim','type' => 'date'),
			array('default' => '','field' => 'Treatment_Reg','label' => 'Номер регистрации','length' => 50,'rules' => 'trim','type' => 'string'),
			array('default' => 0,'field' => 'TreatmentUrgency_id','label' => 'Срочность обращения','rules' => '','type' => 'id'),
			array('default' => 0,'field' => 'TreatmentMultiplicity_id','label' => 'Кратность обращения','rules' => '','type' => 'id'),
			array('default' => 0,'field' => 'TreatmentSenderType_id','label' => 'Тип инициатора','rules' => '','type' => 'id'),
			array('default' => '','field' => 'Treatment_SenderDetails','label' => 'Инициатор обращения','length' => 255,'rules' => 'trim','type' => 'string'),
			array('default' => '','field' => 'Person_id','label' => 'Инициатор обращения','type' => 'id'),
			array('default' => 0,'field' => 'TreatmentType_id','label' => 'Тип обращения','rules' => '','type' => 'id'),
			array('default' => 0,'field' => 'TreatmentCat_id','label' => 'Категория обращения','rules' => '','type' => 'id'),
			array('default' => 0,'field' => 'TreatmentRecipientType_id','label' => 'Адресат обращения','rules' => '','type' => 'id'),
			array('default' => 0,'field' => 'Lpu_rid','label' => 'Адресат обращения (ЛПУ)','rules' => '','type' => 'id'),
			array('default' => 0,'field' => 'TreatmentMethodDispatch_id','label' => 'Способ получения обращения','rules' => '','type' => 'id'),
			array('default' => 0,'field' => 'TreatmentReview_id','label' => 'Результат рассмотрения','rules' => '','type' => 'id'),
			array('default' => '','field' => 'Treatment_DateReview_Start','label' => 'Дата рассмотрения с (начало диапазона)','rules' => 'trim','type' => 'date'),
			array('default' => '','field' => 'Treatment_DateReview_End','label' => 'Дата рассмотрения по (конец диапазона)','rules' => 'trim','type' => 'date')
		)
	);

	/**
	 * Description
	 */
	function __construct() {
		parent::__construct();
		// Проверка права доступа. Доступ имеют только пользователи МЗ и суперадминистраторы
		/*
		if ( !isMinZdrav() && !isSuperadmin() && !havingGroup('OuzSpec') )
		{
			$this->ReturnError('У вас нет доступа к данному функционалу!', 700);
			return false;
		}
		*/
		$this->load->database();
		$this->load->model("Treatment_model", "treatmentmodel");
	}

	/**
	 * Обработка загрузки файлов
	 * Входящие данные: файл + данные о предыдущих загруженных файлах в json-строке
	 * На выходе: данные о загруженных файлах в json-строке
	 */
	function uploadFile()
	{
		
		$upload_path = IMPORTPATH_ROOT;
		$allowed_types = explode('|','pdf|xls|xlsx|xl|rtf|txt|word|doc|docx|jpg|jpe|jpeg|png|bmp|tiff|tif|gif|odt|ods');

		$val  = array();
		
		$data = $this->ProcessInputData('uploadFile', false);
		if ( $data === false ) { return false; }
		
		// $_FILES['userfile'] установлен?
		if ( ! isset($_FILES['userfile']))
		{
			$this->ReturnData( array('success' => false, 'Error_Code' => 701 , 'Error_Msg' => toUTF('Вы не выбрали файл для загрузки.') ) );
			return false;
		}
		// Файл загружен?
		if ( ! is_uploaded_file($_FILES['userfile']['tmp_name']))
		{
			$error = ( ! isset($_FILES['userfile']['error'])) ? 4 : $_FILES['userfile']['error'];
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
					$message = 'Вы не выбрали файл для загрузки.';
					break;
			}
			$this->ReturnData( array('success' => false, 'Error_Code' => 702 , 'Error_Msg' => toUTF($message) ) );
			return false;
		}

		// Тип файла разрешен к загрузке?
		$x = explode('.', $_FILES['userfile']['name']);
		$file_data['file_ext'] = end($x);
		if ( ! in_array($file_data['file_ext'], $allowed_types) )
		{
			$this->ReturnData( array('success' => false, 'Error_Code' => 703 , 'Error_Msg' => toUTF('Вы пытаетесь загрузить запрещенный тип файла.') ) );
			return false;
		}

		// Правильно ли указана директория для загрузки?
		if ( ! @is_dir($upload_path))
		{
			$this->ReturnData( array('success' => false, 'Error_Code' => 704 , 'Error_Msg' => toUTF('Путь для загрузки файлов некорректен.') ) );
			return false;
		}

		// Имеет ли директория для загрузки права на запись?
		if ( ! is_writable($upload_path))
		{
			$this->ReturnData( array('success' => false, 'Error_Code' => 705 , 'Error_Msg' => toUTF('Директория, в которую загружается файл не имеет прав на запись.') ) );
			return false;
		}

		// Подготовка данных о файле
		$file_data['orig_name'] = toUTF($_FILES['userfile']['name']);
		$file_data['file_size'] = $_FILES['userfile']['size'];
		$file_data['file_name'] = md5( $_FILES['userfile']['name'] . time() ) . '.' . $file_data['file_ext'];

		// Сохраняем файл на сервере, возвращаем данные о файле
		if ( move_uploaded_file ( $_FILES['userfile']['tmp_name'] , $upload_path . $file_data['file_name'] ) )
		{
			if ( ! empty($data['FileDescr']) )
			{
				$file_data['file_descr'] = toUTF($data['FileDescr']);
			}
			else
			{
				$file_data['file_descr'] = '';
			}
			if ( ! empty($data['FilesData']) )
			{
				$files_data = json_decode($data['FilesData'], true);
				$files_data[] = $file_data;
			}
			else
			{
				$files_data = array(0 => $file_data);
			}
			$val['data'] = json_encode($files_data);
			$val['Error_Code'] = '';
			$val['Error_Msg'] = '';
			$val['success'] = true;
			$this->ReturnData($val);

			return true;
		}
		else
		{
			$this->ReturnData( array('success' => false, 'Error_Code' => 706 , 'Error_Msg' => toUTF('Невозможно скопировать файл в указанное место после его загрузки.') ) );
			return false;
		}
	}

	/**
	 * Обработка удаления файлов файлов
	 * Входящие данные: имя файла для удаления + данные о загруженных файлах в json-строке
	 * На выходе: данные о загруженных файлах в json-строке
	 */
	function deleteFile()
	{
		$val  = array();
		
		$data = $this->ProcessInputData('deleteFile', false);
		if ( $data === false ) { return false; }
		
		// Проверяем корректность имени файла
		if ( !preg_match("/^([0-9a-z\.]+)$/i", $data['file']) )
		{
			$this->ReturnData( array('success' => false, 'Error_Code' => 101 , 'Error_Msg' => toUTF('Имя файла имеет некорректный вид') ) );
			return false;
		}

		$filename = './uploads/' . $data['file'];

		if ( !is_file($filename) ) 
		{
			$files_data = json_decode($data['data'], true);
			// Удаляем данные о файле из массива
			foreach ($files_data as $i => $file_data) {
				if ($file_data['file_name'] == $data['file'])
				{
					unset( $files_data[$i] );
					break;
				}
			}
			// Возвращаем обновленные данные о прикрепленных файлах и сообщение об ошибке
			$this->ReturnData( array('success' => false, 'Error_Code' => 102 , 'Error_Msg' => toUTF('Файл не найден!') , 'data' => json_encode($files_data) , 'file' => $data['file'] ) );
			return false;
		}

		if ( !is_writable($filename) )
		{
			$this->ReturnData( array('success' => false, 'Error_Code' => 103 , 'Error_Msg' => toUTF('Файл не может быть удален, т.к. нет прав на запись') ) );
			return false;
		}

		// Удаляем файл
		if ( unlink($filename) )
		{
			$files_data = json_decode($data['data'], true);
			// Удаляем данные о файле из массива
			foreach ($files_data as $i => $file_data) {
				if ($file_data['file_name'] == $data['file'])
				{
					unset( $files_data[$i] );
					break;
				}
			}
			// Возвращаем обновленные данные о прикрепленных файлах
			$val['file'] = $data['file'];
			$val['data'] = json_encode($files_data);
			$val['Error_Code'] = '';
			$val['Error_Msg'] = '';
			$val['success'] = true;
			$this->ReturnData($val);
			return true;
		}
		else
		{
			$this->ReturnData( array('success' => false, 'Error_Code' => 104 , 'Error_Msg' => toUTF('Попытка удалить файл провалилась.') ) );
			return false;
		}
	}

	/**
	 * Получение и вывод списка обращений по фильтру в Grid
	 * в форму ПОТОЧНОГО ВВОДА регистрации обращений
	 * Входные данные поступают методом GET
	 * Поля параметров поточного ввода:
	 *	Дата регистрации (календарное поле)
	 *	Тип обращения. Комбобокс.
	 * Возвращаемые данные (в json-строке):
	 *	Создатель
	 *	Номер регистрации
	 *	Дата регистрации
	 *	Тип обращения
	 *	Тип инициатора обращения.
	 *	Инициатор обращения.
	 *	Адресат обращения.
	 */
	function getTreatmentList()
	{
		$data = $this->ProcessInputData('getTreatmentList', true);
		if ( $data === false ) { return false; }
		
		$response = $this->treatmentmodel->getTreatmentList($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}


	/**
	 * Получение данных одного обращения
	 * в форму ПОТОЧНОГО ВВОДА регистрации обращений
	 * Входные данные поступают методом
	 * ID обращения.
	 * Возвращаемые данные (в json-строке):
	 */
	function getTreatment()
	{
		$data = $this->ProcessInputData('getTreatment', true);
		if ( $data === false ) { return false; }
		
		$response = $this->treatmentmodel->getTreatment($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		
		return true;
	}

	/**
	 * Получение и вывод списка обращений по фильтру в Grid
	 * в форму ПОИСКА обращений
	 * Входные данные поступают методом GET
	 * Поля поиска:
	 * 	Создатель.
	 * 	Номер регистрации.
	 * 	Дата регистрации с (календарное поле) по (календарное поле)
	 * 	Тип инициатора обращения.
	 * 	Адресат обращения.
	 * 	ЛПУ (адресат обращения)
	 * 	Способ получения обращения.
	 * 	Срочность.
	 * 	Кратность обращения.
	 * 	Тип обращения.
	 * 	Категория обращения.
	 * 	Дата рассмотрения с (календарное поле) по (календарное поле)
	 * 	Результат рассмотрения.
	 * Возвращаемые данные (в json-строке):
	 *	Создатель
	 *	Номер регистрации
	 *	Дата регистрации
	 *	Тип обращения
	 *	Тип инициатора обращения.
	 *	Инициатор обращения.
	 *	Адресат обращения.
	 *	Дата рассмотрения
	 *	Результат рассмотрения
	 */
	function getTreatmentSearchList()
	{
		$data = $this->ProcessInputData('getTreatmentSearchList', true);
		if ( $data === false ) { return false; }
		
		// Проверка обязательности ЛПУ, если адресат ЛПУ
		if (isset($data['TreatmentRecipientType_id']) && $data['TreatmentRecipientType_id'] == 1) // ЛПУ
		{
			if (empty($data['Lpu_rid']))
			{
				$this->ReturnData(array('success' => false, 'Error_Code' => 666 , 'Error_Msg' => toUTF('Выберите ЛПУ (адресата обращения)!')));
				return false;
			}
		}
		
		$response = $this->treatmentmodel->getTreatmentSearchList($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	
		return true;
	}

	/**
	 * Печать карточки обращения
	 * Входящие данные: $_GET['Treatment_id']
	 * На выходе: HTML
	 * Используется: представление Печать карточки обращения
	 */
	function printTreatment()
	{
		$this->load->library('parser'); 
		
		$data = $this->ProcessInputData('printTreatment', true);
		if ( $data === false ) { return false; }

		$response = $this->treatmentmodel->printTreatment($data['Treatment_id']);
		
		if ( is_array($response) && count($response) > 0 ) {
			$template = 'print_treatment';
			$parse_data = array(
				'Treatment_Reg' => $response[0]['Treatment_Reg'],
				'Treatment_DateReg' => $response[0]['Treatment_DateReg'],
				'TreatmentUrgency' => $response[0]['TreatmentUrgency'],
				'TreatmentMultiplicity' => $response[0]['TreatmentMultiplicity'],
				'TreatmentSenderType' => $response[0]['TreatmentSenderType'],
				'TreatmentMethodDispatch' => $response[0]['TreatmentMethodDispatch'],
				'TreatmentType' => $response[0]['TreatmentType'],
				'TreatmentCat' => $response[0]['TreatmentCat'],
				'TreatmentReview' => $response[0]['TreatmentReview'],
				'TreatmentRecipientType' => $response[0]['TreatmentRecipientType'],
				'TreatmentSubjectType' => $response[0]['TreatmentSubjectType'],
				'Lpu_r' => ( empty($response[0]['Lpu_r']) ) ? " не указано " : $response[0]['Lpu_r'] ,
				'Org' => ( empty($response[0]['Org']) ) ? " не указано " : $response[0]['Org'] ,
				'Lpu_s' => ( empty($response[0]['Lpu_s'] ) ) ? " не указано " : $response[0]['Lpu_s'] ,
				'MedPersonal' => ( empty($response[0]['MedPersonal']) ) ? " не указано " : $response[0]['MedPersonal'] ,
				'Lpu_m' => ( empty($response[0]['Lpu_m']) ) ? " не указано " : $response[0]['Lpu_m'] ,
				'Treatment_SenderDetails' => ( empty($response[0]['Treatment_SenderDetails']) ) ? " не указано " : $response[0]['Treatment_SenderDetails'] ,
				'Treatment_Text' => ( empty($response[0]['Treatment_Text']) ) ? " не указано " : $response[0]['Treatment_Text'] ,
				'Treatment_Comment' => ( empty($response[0]['Treatment_Comment'] ) ) ? " не указано " : $response[0]['Treatment_Comment'] ,
				'Treatment_DateReview' => ( empty($response[0]['Treatment_DateReview']) ) ? " не указано " : $response[0]['Treatment_DateReview']
			);
			$this->parser->parse($template, $parse_data);
			return true;
		}
	}

	/**
	 * Редактирование Карточки обращения или Регистрация Обращения
	 * Входящие данные: $_POST
	 * На выходе: JSON-строка
	 * Используется: форма
	 */
	function saveTreatment()
	{
		
		$data = $this->ProcessInputData('saveTreatment', true);
		if ( $data === false ) { return false; }
		
		$response = $this->treatmentmodel->saveTreatment($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		
		return true;
	}

	/**
	 * Установка статуса обращения, статус пишется в историю статусов TreatmentReviewHistory
	 * Входящие данные: $_POST
	 * На выходе: JSON-строка
	 * Используется: форма
	 */
	function setStatusTreatment()
	{
		$data = $this->ProcessInputData('setStatusTreatment', true);
		if ( $data === false ) { return false; }

		$response = $this->treatmentmodel->setStatusTreatment($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
	}

	/**
	 * Сохранение ответа на обращение
	 * Входящие данные: $_POST
	 * На выходе: JSON-строка
	 * Используется: форма
	 */
	function saveTreatmentFeedback()
	{
		$data = $this->ProcessInputData('saveTreatmentFeedback', true);
		if ( $data === false ) { return false; }

		$response = $this->treatmentmodel->saveTreatmentFeedback($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
	}

	/**
	 * Формирование списка отчетов по обращениям для TreePanel
	 */
	function getTreatmentReportTree()
	{
		$reports = array(
			array(
				'text' => toUTF('Количество обращений по типам'),
				'id' => 'TRW_number',
				'leaf' => true,
				'cls' => 'file'
			),
			array(
				'text' => toUTF('Категории обращений'),
				'id' => 'TRW_cat',
				'leaf' => true,
				'cls' => 'file'
			),
			array(
				'text' => toUTF('Инициаторы обращений'),
				'id' => 'TRW_sender',
				'leaf' => true,
				'cls' => 'file'
			),
			array(
				'text' => toUTF('Рассмотрение обращений'),
				'id' => 'TRW_review',
				'leaf' => true,
				'cls' => 'file'
			),
			array(
				'text' => toUTF('Субъекты обращения ЛПУ'),
				'id' => 'TRW_subjectLpu',
				'leaf' => true,
				'cls' => 'file'
			),
			array(
				'text' => toUTF('Субъекты обращения Врачи'),
				'id' => 'TRW_subjectMedpersonal',
				'leaf' => true,
				'cls' => 'file'
			),/* */
			array(
				'text' => toUTF('Кратность обращений'),
				'id' => 'TRW_multiplicity',
				'leaf' => true,
				'cls' => 'file'
			)
		);
		$this->ReturnData($reports);
		return false;
	}

	/**
	 * Получение полей форм для формирования отчетов
	 * http://promed/?c=Treatment&m=getTreatmentReport&node=TRW_subjectLpu&Treatment_DateReg_Start=13.07.2010&Treatment_DateReg_End=13.07.2010&TreatmentMethodDispatch_id=1
	 */
	function getTreatmentReportForm()
	{
	
		$data = $this->ProcessInputData('getTreatmentReportForm', true);
		if ( $data === false ) { return false; }
		
		$default_form = array(
			'TRW_number' => array(
				array(
					'label' => toUTF('Параметры:'),
					'type' => 'fieldset',
					'labelWidth' => 150,
					'items' => array(
						array(
							'fieldLabel' => toUTF('С даты регистрации'),
							'allowBlank' => false,
							'disabled' => false,
							'tabIndex' => 10401,
							'id' => 'TRW_Treatment_DateReg_Start',
							'name' => 'Treatment_DateReg_Start',
							'format' => 'd.m.Y',
							'width' => 180,
							'xtype' => 'swdatefield'
						),
						array(
							'fieldLabel' => toUTF('По дату регистрации'),
							'allowBlank' => false,
							'disabled' => false,
							'tabIndex' => 10402,
							'id' => 'TRW_Treatment_DateReg_End',
							'name' => 'Treatment_DateReg_End',
							'format' => 'd.m.Y',
							'width' => 180,
							'xtype' => 'swdatefield'
						)
						/*array(
							'fieldLabel' => toUTF('Диапазон дат'),
							'disabled' => false,
							'allowBlank' => false,
							'tabIndex'  => 11000,
							'name' => 'range',
							'id' => 'TRW_range',
							'width' => 170,
							'xtype' => 'daterangefield'
						)*/
					)
				)
			),
			'TRW_subjectLpu' => array(
				array(
					'label' => toUTF('Параметры:'),
					'type' => 'fieldset',
					'labelWidth' => 200,
					'items' => array(
						array(
							'fieldLabel' => toUTF('С даты регистрации'),
							'allowBlank' => false,
							'disabled' => false,
							'tabIndex' => 10401,
							'id' => 'TRW_Treatment_DateReg_Start',
							'name' => 'Treatment_DateReg_Start',
							'format' => 'd.m.Y',
							'width' => 180,
							'xtype' => 'swdatefield'
						),
						array(
							'fieldLabel' => toUTF('По дату регистрации'),
							'allowBlank' => false,
							'disabled' => false,
							'tabIndex' => 10402,
							'id' => 'TRW_Treatment_DateReg_End',
							'name' => 'Treatment_DateReg_End',
							'format' => 'd.m.Y',
							'width' => 180,
							'xtype' => 'swdatefield'
						),
						array(
							'fieldLabel' => toUTF('Способ получения обращения'),
							'comboSubject' => 'TreatmentMethodDispatch',
							'idPrefix' => 'TRW_',
							'allowBlank' => true,
							'tabIndex' => 10404,
							'width' => 300,
							'value' => '',
							'xtype' => 'swcommonsprcombo'
						),
						array(
							'fieldLabel' => toUTF('Кратность обращения'),
							'allowBlank' => true,
							'disabled' => false,
							'comboSubject' => 'TreatmentMultiplicity',
							'idPrefix' => 'TRW_',
							'tabIndex' => 10405,
							'width' => 300,
							'value' => '',
							'xtype' => 'swtreatmentcombo'
						),
						array(
							'fieldLabel' => toUTF('Тип обращения'),
							'allowBlank' => true,
							'disabled' => false,
							'comboSubject' => 'TreatmentType',
							'idPrefix' => 'TRW_',
							'tabIndex' => 10406,
							'width' => 300,
							'value' => '',
							'xtype' => 'swtreatmentcombo'
						),
						array(
							'fieldLabel' => toUTF('Категория обращения'),
							'comboSubject' => 'TreatmentCat',
							'value' => '',
							'width' => 300,
							'allowBlank' => true,
							'idPrefix' => 'TRW_',
							'tabIndex' => 10407,
							'xtype' => 'swcommonsprcombo'
						),
						array(
							'fieldLabel' => toUTF('Адресат обращения'),
							'comboSubject' => 'TreatmentRecipientType',
							'idPrefix' => 'TRW_',
							'allowBlank' => true,
							'value' => '',
							'tabIndex' => 10408,
							'width' => 300,
							'xtype' => 'swcommonsprcombo'
						),
						array(
							'fieldLabel' => toUTF('Статус обращения'),
							'allowBlank' => true,
							'disabled' => false,
							'comboSubject' => 'TreatmentReview',
							'tabIndex' => 10409,
							'width' => 300,
							'value' => '',
							'idPrefix' => 'TRW_',
							'id' => 'TRW_TreatmentReview_id',
							'xtype' => 'swtreatmentcombo'
						),
						array(
							'fieldLabel' => toUTF('С даты рассмотрения'),
							'allowBlank' => true,
							'disabled' => true,
							'tabIndex' => 10410,
							'id' => 'TRW_Treatment_DateReview_Start',
							'name' => 'Treatment_DateReview_Start',
							'format' => 'd.m.Y',
							'width' => 180,
							'xtype' => 'swdatefield'
						),
						array(
							'fieldLabel' => toUTF('По дату рассмотрения'),
							'allowBlank' => true,
							'disabled' => true,
							'tabIndex' => 10411,
							'id' => 'TRW_Treatment_DateReview_End',
							'name' => 'Treatment_DateReview_End',
							'format' => 'd.m.Y',
							'width' => 180,
							'xtype' => 'swdatefield'
						)
					)
				)
			),
			'TRW_subjectMedpersonal' => array(
				array(
					'label' => toUTF('Параметры:'),
					'type' => 'fieldset',
					'labelWidth' => 200,
					'items' => array(
						array(
							'fieldLabel' => toUTF('С даты регистрации'),
							'allowBlank' => false,
							'disabled' => false,
							'tabIndex' => 10401,
							'id' => 'TRW_Treatment_DateReg_Start',
							'name' => 'Treatment_DateReg_Start',
							'format' => 'd.m.Y',
							'width' => 180,
							'xtype' => 'swdatefield'
						),
						array(
							'fieldLabel' => toUTF('По дату регистрации'),
							'allowBlank' => false,
							'disabled' => false,
							'tabIndex' => 10402,
							'id' => 'TRW_Treatment_DateReg_End',
							'name' => 'Treatment_DateReg_End',
							'format' => 'd.m.Y',
							'width' => 180,
							'xtype' => 'swdatefield'
						),
						array(
							'fieldLabel' => toUTF('ЛПУ (оставьте это поле пустым для выборки по всем ЛПУ)'),
							'allowBlank' => true,
							//'emptyText' => 'По всем ЛПУ',
							//'editable' => false,
							//'value' => '',
							'disabled' => false,
							'tabIndex' => 10403,
							'width' => 300,
							'autoLoad' => true,
							'id' => 'TRW_Lpu_sid',
							//'name' => 'Lpu_sid',
							'hiddenName' => 'Lpu_sid',
							'xtype' => 'swlpulocalcombo'
						),
						array(
							'fieldLabel' => toUTF('Способ получения обращения'),
							'comboSubject' => 'TreatmentMethodDispatch',
							'idPrefix' => 'TRW_',
							'allowBlank' => true,
							'tabIndex' => 10404,
							'width' => 300,
							'value' => '',
							'xtype' => 'swcommonsprcombo'
						),
						array(
							'fieldLabel' => toUTF('Кратность обращения'),
							'allowBlank' => true,
							'disabled' => false,
							'comboSubject' => 'TreatmentMultiplicity',
							'idPrefix' => 'TRW_',
							'tabIndex' => 10405,
							'width' => 300,
							'value' => '',
							'xtype' => 'swtreatmentcombo'
						),
						array(
							'fieldLabel' => toUTF('Тип обращения'),
							'allowBlank' => true,
							'disabled' => false,
							'comboSubject' => 'TreatmentType',
							'idPrefix' => 'TRW_',
							'tabIndex' => 10406,
							'width' => 300,
							'value' => '',
							'xtype' => 'swtreatmentcombo'
						),
						array(
							'fieldLabel' => toUTF('Категория обращения'),
							'comboSubject' => 'TreatmentCat',
							'value' => '',
							'width' => 300,
							'allowBlank' => true,
							'idPrefix' => 'TRW_',
							'tabIndex' => 10407,
							'xtype' => 'swcommonsprcombo'
						),
						array(
							'fieldLabel' => toUTF('Адресат обращения'),
							'comboSubject' => 'TreatmentRecipientType',
							'idPrefix' => 'TRW_',
							'allowBlank' => true,
							'value' => '',
							'tabIndex' => 10408,
							'width' => 300,
							'xtype' => 'swcommonsprcombo'
						),
						array(
							'fieldLabel' => toUTF('Статус обращения'),
							'allowBlank' => true,
							'disabled' => false,
							'comboSubject' => 'TreatmentReview',
							'tabIndex' => 10409,
							'width' => 300,
							'value' => '',
							'idPrefix' => 'TRW_',
							'id' => 'TRW_TreatmentReview_id',
							'xtype' => 'swtreatmentcombo'
						),
						array(
							'fieldLabel' => toUTF('С даты рассмотрения'),
							'allowBlank' => true,
							'disabled' => true,
							'tabIndex' => 10410,
							'id' => 'TRW_Treatment_DateReview_Start',
							'name' => 'Treatment_DateReview_Start',
							'format' => 'd.m.Y',
							'width' => 180,
							'xtype' => 'swdatefield'
						),
						array(
							'fieldLabel' => toUTF('По дату рассмотрения'),
							'allowBlank' => true,
							'disabled' => true,
							'tabIndex' => 10411,
							'id' => 'TRW_Treatment_DateReview_End',
							'name' => 'Treatment_DateReview_End',
							'format' => 'd.m.Y',
							'width' => 180,
							'xtype' => 'swdatefield'
						)
					)
				)
			)
		);
		
		if(isset($data['node'])){ $node = $data['node']; }else {$node = '';}
		
		switch ($node) {
			case 'TRW_subjectLpu': 
				$options[$node] = $default_form['TRW_subjectLpu'];
				break;
			case 'TRW_subjectMedpersonal':
				$options[$node] = $default_form['TRW_subjectMedpersonal'];
				break;
			default:
				$options[$node] = $default_form['TRW_number'];
				break;
		}
		$this->ReturnData($options);
	}

	/**
	 * Получение отчетов
	 * Входящие данные: node, range	07.07.2010 - 07.07.2010
	 */
	function getTreatmentReport()
	{
		$this->load->library('parser'); 
		$val  = array();
		
		$data = $this->ProcessInputData('getTreatmentReport', true);
		if ( $data === false ) { return false; }
		
		$response = $this->treatmentmodel->getTreatmentReport($data);
		if ( is_array($response) && count($response) > 0 ) {
			$val = $response;
			if ( (isset($response['Error_Msg'])) && (strlen($response['Error_Msg']) > 0) )
			{
				echo $response['Error_Msg'];
				return false;
			}
			else
			{
				switch ($data['node']) {
					// Отчет «Количество обращений» (Типы обращений)
					case 'TRW_number':
						$template = 'print_treatment_report_number';
						$parse_data = array(
							'title' => 'Отчет «Количество обращений по типам»',
							'date_start' => $_GET['Treatment_DateReg_Start'],
							'date_end' => $_GET['Treatment_DateReg_End'],
							'all_item' => $response['data'][0]['all_item'],
							'number_1' => $response['data'][0]['number_1'],
							'number_2' => $response['data'][0]['number_2'],
							'number_3' => $response['data'][0]['number_3'],
							'number_4' => $response['data'][0]['number_4']
						);
						break;
					// Отчет «Категории обращений»
					case 'TRW_cat':
						$all_data = array();
						$data1 = array();
						$data2 = array();
						$data3 = array();
						$data4 = array();
						foreach ($response as $i => $table) // i == 5
						{
							foreach ($table as $k => $item)
							{
								if (0 == $i) $all_data[$k]=$item;
								if (1 == $i) $data1[$k]=$item; // По предложениям
								if (2 == $i) // По благодарностям
								{
									if (1 == $k OR 2 == $k) continue;
									$data2[$k]=$item; 
								}
								if (3 == $i) $data3[$k]=$item; // По заявлениям
								if (4 == $i) $data4[$k]=$item; // Жалобы
							}
						}
						$template = 'print_treatment_report_cat';
						$parse_data = array(
							'title' => 'Отчет «Категории обращений»',
							'date_start' => $_GET['Treatment_DateReg_Start'],
							'date_end' => $_GET['Treatment_DateReg_End'],
							'all_data' => $all_data,
							'data1' => $data1,
							'data2' => $data2,
							'data3' => $data3,
							'data4' => $data4
						);
						break;
					// Отчет «Инициаторы обращений»
					case 'TRW_sender':
						$template = 'print_treatment_report_sender';
						$parse_data = array(
							'title' => 'Отчет «Инициаторы обращений»',
							'date_start' => $_GET['Treatment_DateReg_Start'],
							'date_end' => $_GET['Treatment_DateReg_End'],
							'all_item' => $response['data'][0]['all_item'],
							'patientes' => $response['data'][0]['patientes'],
							'org' => $response['data'][0]['org'],
							'com' => $response['data'][0]['com'],
							'glav_vrach' => $response['data'][0]['glav_vrach'],
							'zav_otd' => $response['data'][0]['zav_otd'],
							'vrach' => $response['data'][0]['vrach'],
							'sister' => $response['data'][0]['sister'],
							'medpersonal' => $response['data'][0]['zav_otd'] + $response['data'][0]['vrach'] + $response['data'][0]['sister'],
							'other' => $response['data'][0]['other']
						);
						break;
					// Отчет «Рассмотрение обращений»
					case 'TRW_review':
						$template = 'print_treatment_report_review';
						$pr_review = 0;
						$pr_notreview = 0;
						if ( $response['data'][0]['all_item'] > 0 )
						{
							$pr_review = 100 * $response['data'][0]['review'] / $response['data'][0]['all_item'];
							$pr_notreview = 100 * $response['data'][0]['notreview'] / $response['data'][0]['all_item'];
						}
						$parse_data = array(
							'title' => 'Отчет «Рассмотрение обращений»',
							'date_start' => $_GET['Treatment_DateReg_Start'],
							'date_end' => $_GET['Treatment_DateReg_End'],
							'all_item' => $response['data'][0]['all_item'],
							'review' => $response['data'][0]['review'],
							'notreview' => $response['data'][0]['notreview'],
							'pr_review' => $pr_review,
							'pr_notreview' => $pr_notreview 
						);
						break;
					// Отчет «Кратность обращений»
					case 'TRW_multiplicity':
						$template = 'print_treatment_report_multiplicity';
						$parse_data = array(
							'title' => 'Отчет «Кратность обращений»',
							'date_start' => $_GET['Treatment_DateReg_Start'],
							'date_end' => $_GET['Treatment_DateReg_End'],
							'all_item' => $response['data'][0]['all_item'],
							'first' => $response['data'][0]['first'],
							'doubl' => $response['data'][0]['doubl']
						);
						break;
					// Отчет Субъекты обращения ЛПУ
					case 'TRW_subjectLpu':
						$template = 'print_treatment_report_subjectlpu';
						$parse_data = array(
							'title' => 'Отчет «Субъекты обращения ЛПУ»',
							'date_start' => $_GET['Treatment_DateReg_Start'],
							'date_end' => $_GET['Treatment_DateReg_End'],
							'TreatmentMethodDispatch' => $response['params']['TreatmentMethodDispatch'],
							'TreatmentMultiplicity' => $response['params']['TreatmentMultiplicity'],
							'TreatmentType' => $response['params']['TreatmentType'],
							'TreatmentCat' => $response['params']['TreatmentCat'],
							'TreatmentRecipientType' => $response['params']['TreatmentRecipientType'],
							'TreatmentReview' => $response['params']['TreatmentReview'],
							'dateReview' => '',
							'data' => $response['data']
						);
						if ( isset($_GET['Treatment_DateReview_Start']) )
							$parse_data['dateReview'] .= " с " . $_GET['Treatment_DateReview_Start'];
						if ( isset($_GET['Treatment_DateReview_End']) )
							$parse_data['dateReview'] .= " до " . $_GET['Treatment_DateReview_End'];
						if ( empty($parse_data['dateReview']) )
							$parse_data['dateReview'] = " не указано ";
						break;
					// Отчет Субъекты обращения врачи
					case 'TRW_subjectMedpersonal':
						$template = 'print_treatment_report_subjectmedpersonal';
						$parse_data = array(
							'title' => 'Отчет «Субъекты обращения Врачи»',
							'date_start' => $_GET['Treatment_DateReg_Start'],
							'date_end' => $_GET['Treatment_DateReg_End'],
							'Lpu' => $response['params']['Lpu'],
							'TreatmentMethodDispatch' => $response['params']['TreatmentMethodDispatch'],
							'TreatmentMultiplicity' => $response['params']['TreatmentMultiplicity'],
							'TreatmentType' => $response['params']['TreatmentType'],
							'TreatmentCat' => $response['params']['TreatmentCat'],
							'TreatmentRecipientType' => $response['params']['TreatmentRecipientType'],
							'TreatmentReview' => $response['params']['TreatmentReview'],
							'dateReview' => '',
							'data' => $response['data']
						);
						if ( isset($_GET['Treatment_DateReview_Start']) )
							$parse_data['dateReview'] .= " с " . $_GET['Treatment_DateReview_Start'];
						if ( isset($_GET['Treatment_DateReview_End']) )
							$parse_data['dateReview'] .= " до " . $_GET['Treatment_DateReview_End'];
						if ( empty($parse_data['dateReview']) )
							$parse_data['dateReview'] = " не указано ";
						break;
					default:
						echo 'Неправильный параметр node';
						return false;
				}
				$this->parser->parse($template, $parse_data);
				return true;
			}
		}
		echo 'От сервера получены неправильные данные';
		return false;
	}
}