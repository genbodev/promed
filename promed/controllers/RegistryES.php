<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * RegistryES - контроллер для работы с реестрами ЛВН
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package     Mse
 * @access      public
 * @copyright   Copyright (c) 2014 Swan Ltd.
 * @author		Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version     29.09.2014
 *
 * @property RegistryES_model dbmodel
 */

class RegistryES extends swController {
	public $inputRules = array(
		'loadRegistryESGrid' => array(
			array('field' => 'RegistryES_id', 'label' => 'Идентификатор реестра ЛВН', 'rules' => '', 'type' => 'id'),
			array('field' => 'RegistryES_DateRange', 'label' => 'Диапазон дат реестров', 'rules' => '', 'type' => 'daterange'),
			array('field' => 'RegistryES_Num', 'label' => 'Номер реестра', 'rules' => '', 'type' => 'int'),
			array('field' => 'EvnStick_Num', 'label' => 'Номер ЛВН', 'rules' => '', 'type' => 'int'),
			array('field' => 'RegistryESStatus_id', 'label' => 'Идентификатор статуса реестра', 'rules' => '', 'type' => 'id'),
			array('field' => 'RegistryESType_id', 'label' => 'Тип реестра', 'rules' => '', 'type' => 'id'),

			array('field' => 'LpuBuilding_id', 'label' => 'Подразделение', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSection_id', 'label' => 'Отделение', 'rules' => '', 'type' => 'id'),
		),
		'loadRegistryESDataGrid' => array(
			array('field' => 'RegistryES_id', 'label' => 'Идентификатор реестра ЛВН', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnStick_Num', 'label' => 'Номер ЛВН', 'rules' => '', 'type' => 'string'),
			array('field' => 'Person_Fio', 'label' => 'ФИО пациента', 'rules' => '', 'type' => 'string'),
			array('field' => 'RegistryESType_id', 'label' => 'ТАП/КВС', 'rules' => '', 'type' => 'id'),
			array('field' => 'RegistryESDataStatus_id', 'label' => 'Статус ЛВН', 'rules' => '', 'type' => 'id'),
		),
		'loadRegistryESErrorGrid' => array(
			array('field' => 'RegistryES_id', 'label' => 'Идентификатор реестра ЛВН', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'RegistryESErrorStageType_Code', 'label' => 'Код стадии ошибок по реестрам ЛВН', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'EvnStick_Num', 'label' => 'Номер ЛВН', 'rules' => '', 'type' => 'string' ),
			array('field' => 'Person_Fio', 'label' => 'ФИО пациента', 'rules' => '', 'type' => 'string'),
			array('field' => 'RegistryESType_id', 'label' => 'ТАП/КВС', 'rules' => '', 'type' => 'id'),
			array('field' => 'RegistryESDataStatus_id', 'label' => 'Статус ЛВН', 'rules' => '', 'type' => 'id'),
		),
		'saveRegistryES' => array(
			array('field' => 'RegistryES_id', 'label' => 'Идентификатор реестра ЛВН', 'rules' => '', 'type' => 'id'),
			array('field' => 'RegistryES_Num', 'label' => 'Номер реестра ЛВН', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'LpuBuilding_id', 'label' => 'Подразделение', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSection_id', 'label' => 'Отделение', 'rules' => '', 'type' => 'id'),
			array('field' => 'RegistryES_begDate', 'label' => 'Дата реестра ЛВН', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'RegistryES_RegRecCount', 'label' => 'Кол-во записей в реестре', 'rules' => '', 'type' => 'int'),
			array('field' => 'RegistryESType_id', 'label' => 'Тип реестра', 'rules' => 'required', 'type' => 'id')
		),
		'deleteRegistryES' => array(
			array('field' => 'id', 'label' => 'Идентификатор реестра ЛВН', 'rules' => 'required', 'type' => 'id'),
		),
		'loadRegistryESForm' => array(
			array('field' => 'RegistryES_id', 'label' => 'Идентификатор реестра ЛВН', 'rules' => 'required', 'type' => 'id'),
		),
		'deleteRegistryESData' => array(
			array('field' => 'RegistryES_ids', 'label' => 'Идентификаторы реестра ЛВН', 'rules' => 'required', 'type' => 'json_array', 'assoc' => true),
		),
		'getNewRegistryESNum' => array(
			array('field' => 'RegistryES_id', 'label' => 'Идентификатор реестра ЛВН', 'rules' => '', 'type' => 'id'),
			array('field' => 'RegistryES_begDate', 'label' => 'Дата реестра ЛВН', 'rules' => 'required', 'type' => 'date'),
		),
		'exportRegistryESToXml' => array(
			array('field' => 'RegistryES_id', 'label' => 'Идентификатор реестра ЛВН', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'certbase64', 'label' => 'Сертификат', 'rules' => '', 'type' => 'string'),
			array('field' => 'needHash', 'label' => 'Признак необходимости подсчёта хэша', 'rules' => '', 'type' => 'int')
		),
		'exportRegistryESDataForCheckInFSS' => array(
			array('field' => 'RegistryES_id', 'label' => 'Идентификатор реестра ЛВН', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Evn_id', 'label' => 'Идентификатор ЛВН', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'certbase64', 'label' => 'Сертификат', 'rules' => '', 'type' => 'string'),
			array('field' => 'needHash', 'label' => 'Признак необходимости подсчёта хэша', 'rules' => '', 'type' => 'int')
		),
		'exportRegistryESDataForCheckInFSSList' => array(
			array('field' => 'RegistryES_Data', 'label' => 'Идентификатоы реестра ЛВН', 'rules' => 'required', 'type' => 'json_array', 'assoc' => true),
			array('field' => 'certbase64', 'label' => 'Сертификат', 'rules' => '', 'type' => 'string'),
			array('field' => 'needHash', 'label' => 'Признак необходимости подсчёта хэша', 'rules' => '', 'type' => 'int')
		),
		'doFLKControl' => array(
			array('field' => 'RegistryES_id', 'label' => 'Идентификатор реестра ЛВН', 'rules' => 'required', 'type' => 'id'),
		),
		'requestRegistryESToFss' => array(
			array('field' => 'RegistryES_id', 'label' => 'Идентификатор реестра ЛВН', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'xmls', 'label' => 'Данные для отправки', 'rules' => '', 'type' => 'string'),
			array('field' => 'certbase64', 'label' => 'Сертификат', 'rules' => '', 'type' => 'string'),
			array('field' => 'certhash', 'label' => 'Хэш сертификата', 'rules' => '', 'type' => 'string'),
		),
		'checkRegistryESDataInFSS' => array(
			array('field' => 'RegistryES_id', 'label' => 'Идентификатор реестра ЛВН', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'xmls', 'label' => 'Данные для отправки', 'rules' => '', 'type' => 'string'),
			array('field' => 'certbase64', 'label' => 'Сертификат', 'rules' => '', 'type' => 'string'),
			array('field' => 'certhash', 'label' => 'Хэш сертификата', 'rules' => '', 'type' => 'string'),
		),
		'sendRegistryESToFss' => array(
			array('field' => 'RegistryES_id', 'label' => 'Идентификатор реестра ЛВН', 'rules' => '', 'type' => 'id'),
		),
		'doFLKControlForAll' => array(
			array('field' => 'RegistryES_id', 'label' => 'Идентификатор реестра ЛВН', 'rules' => '', 'type' => 'id'),
		),
		'showFiles' => array(
			array('field' => 'RegistryES_id', 'label' => 'Идентификатор реестра ЛВН', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Evn_id', 'label' => 'Идентификатор случая ЛВН', 'rules' => '', 'type' => 'id')
		),
		'fixRegistryES' => array(
			array('field' => 'RegistryES_id', 'label' => 'Идентификатор реестра ЛВН', 'rules' => 'required', 'type' => 'id')
		),
		'importRegistryESFromXML' => array(
			array('field' => 'RegistryES_id', 'label' => 'Идентификатор реестра ЛВН', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'RegistryESFile', 'label' => 'Файл', 'rules' => '', 'type' => 'string'),
		),
		'loadRegistryESIndividCertGrid' => array(
			array('field' => 'RegistryES_id', 'label' => 'Идентификатор реестра ЛВН', 'rules' => 'required', 'type' => 'id'),
		),
		'saveRegistryESIndividCert' => array(
			array('field' => 'RegistryES_id', 'label' => 'Идентификатор реестра ЛВН', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'RegistryESIndividCertGridData', 'label' => 'Выбранные сертификаты', 'rules' => 'required', 'type' => 'json_array', 'assoc' => true),
		),
		'getUnsignedData' => array(
			array('field' => 'RegistryES_id', 'label' => 'Идентификатор реестра ЛВН', 'rules' => 'required', 'type' => 'id'),
		),
		'parseXmls' => array(

		),
		'UploadXML' => array()
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->load->database('registry_es');
		$this->load->model('RegistryES_model', 'dbmodel');
	}

	/**
	 * Получение списка для подписи ЛВН в реестре
	 */
	public function loadRegistryESIndividCertGrid() {
		$data = $this->ProcessInputData('loadRegistryESIndividCertGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadRegistryESIndividCertGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Формирование реестра ЛВН
	 */
	public function saveRegistryES() {
		$data = $this->ProcessInputData('saveRegistryES', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveRegistryES($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
	}

	/**
	 * Удаление реестра ЛВН
	 */
	public function deleteRegistryES() {
		$data = $this->ProcessInputData('deleteRegistryES', true);
		if ($data === false) { return false; }
		$data['RegistryES_id'] = $data['id'];

		$response = $this->dbmodel->deleteRegistryES($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Получение данных для формы переформирования ЛВН
	 */
	public function loadRegistryESForm() {
		$data = $this->ProcessInputData('loadRegistryESForm', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadRegistryESForm($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение списка реестров ЛВН
	 */
	public function loadRegistryESGrid() {
		$data = $this->ProcessInputData('loadRegistryESGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadRegistryESGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение данных ЛВН в реестре
	 */
	public function loadRegistryESDataGrid() {
		$data = $this->ProcessInputData('loadRegistryESDataGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadRegistryESDataGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение списка ошибок ФЛК/ФСС в реестре
	 */
	public function loadRegistryESErrorGrid() {
		$data = $this->ProcessInputData('loadRegistryESErrorGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadRegistryESErrorGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение номера для нового ЛВН
	 */
	public function getNewRegistryESNum() {
		$data = $this->ProcessInputData('getNewRegistryESNum', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getNewRegistryESNum($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Удаление записи реестра ЛВН
	 */
	public function deleteRegistryESData() {
		$data = $this->ProcessInputData('deleteRegistryESData', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->deleteRegistryESData($data);
		$this->ProcessModelSave($response, true, 'Ошибка удаления записи реестра ЛВН')->ReturnData();
		return true;
	}

	/**
	 *  Контроль ФЛК
	 */
	public function doFLKControl() {
		$data = $this->ProcessInputData('doFLKControl', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->doFLKControl($data);

		$this->ReturnData($response);
		return true;
	}

	/**
	 * Экспорт реестра ЛВН в xml
	 */
	public function exportRegistryESToXml() {
		$data = $this->ProcessInputData('exportRegistryESToXml', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->exportRegistryESToXml($data);
		if (!empty($response['Error_Msg'])) {
			$this->ReturnError($response['Error_Msg']);
		} else {
			$this->ProcessModelList($response)->ReturnData();
		}

		return true;
	}

	/**
	 * Экспорт данных для проверки в ФСС
	 */
	public function exportRegistryESDataForCheckInFSS() {
		$data = $this->ProcessInputData('exportRegistryESDataForCheckInFSS', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->exportRegistryESDataForCheckInFSS($data);
		if (!empty($response['Error_Msg'])) {
			$this->ReturnError($response['Error_Msg']);
		} else {
			$this->ProcessModelList($response)->ReturnData();
		}

		return true;
	}

	/**
	 * Экспорт данных для проверки в ФСС
	 */
	public function exportRegistryESDataForCheckInFSSList() {
		$data = $this->ProcessInputData('exportRegistryESDataForCheckInFSSList', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->exportRegistryESDataForCheckInFSSList($data);
		if (!empty($response['Error_Msg'])) {
			$this->ReturnError($response['Error_Msg']);
		} else {
			$this->ProcessModelList($response)->ReturnData();
		}

		return true;
	}

	/**
	 * Ручной экспорт реестра ЛВН в xml
	 */
	public function exportRegistryESToXmlManual() {
		$data = $this->ProcessInputData('exportRegistryESToXml', true);
		if ($data === false) { return false; }

		$response = $this->doExportRegistryESToXml($data);
		$this->ReturnData($response);

		return true;
	}


	/**
	 * Экспорт реестра ЛВН в xml
	 */
	private function doExportRegistryESToXml($data) {
		set_time_limit(0);

		$registry = $this->dbmodel->getRegistryESExport($data);
		if ( !is_array($registry) && count($registry) == 0 ) {
			return array('success' => false, 'Error_Msg' => toUtf('Произошла ошибка при чтении реестра. Сообщите об ошибке разработчикам.'));
		}
		$registry = $registry[0];

		$registry_data = $this->dbmodel->loadRegistryESDataForXmlManual($data, 'export');

		if ( !is_array($registry_data) ) {
			return array('success' => false, 'Error_Msg' => toUtf('Произошла ошибка при чтении данных из реестра. Сообщите об ошибке разработчикам.'));
		}
		if (count($registry_data) == 0) {
			return array('success' => false, 'Error_Msg' => toUtf('Реестр не содержит ЛВН.'));
		}

		$this->load->library('parser');
		$template = 'export_registry_es_manual';

		$path = EXPORTPATH_ROOT."registry_es/";

		if (!file_exists($path)) {
			mkdir( $path );
		}

		$number = 1;

		$out_dir = "re_xml_".date("Y_m_d", strtotime($registry['RegistryES_Date']));
		if (!file_exists($path.$out_dir)) {
			mkdir( $path.$out_dir );
		} else {
			$files = scandir($path.$out_dir);
			if (is_array($files)) {
				foreach($files as $file) {
					if (preg_match('/\w+.zip$/', $file) > 0) {
						$number++;
					}
				}
			}
		}

		$file_name = 'L_'.$registry['Lpu_FSSRegNum'].'_'.date("Y_m_d", strtotime($registry['RegistryES_Date'])).'_'.sprintf("%02d",$number);
		$file_path = $path.$out_dir."/".$file_name.".xml";

		$file_zip_sign = $file_name;
		$file_zip_name = $path.$out_dir."/".$file_zip_sign.".zip";

		$zip = new ZipArchive();

		$xml_data['ROW'] = $registry_data;
		$xml_data['LPU_OGRN'] = $registry['Lpu_OGRN'];
		$xml_data['email'] = $registry['RegistryES_UserEmail'];
		$xml_data['phone'] = $registry['RegistryES_UserPhone'];
		$xml_data['author'] = $registry['RegistryES_UserFIO'];
		$xml_data['version_software'] = '';
		$xml_data['software'] = '';
		$xml_data['version'] = '1.0';

		$xml = "<?xml version=\"1.0\" encoding=\"windows-1251\"?>\r\n" . toAnsi($this->parser->parse('export_xml/'.$template, $xml_data, true), true);

		file_put_contents($file_path, $xml);

		$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
		$zip->AddFile( $file_path, $file_name . ".xml" );
		$zip->close();

		unlink($file_path);

		if (file_exists($file_zip_name))
		{
			$this->dbmodel->setRegistryESExport(array(
				'RegistryES_id' => $data['RegistryES_id'],
				'RegistryES_Export' => $file_zip_name
			));
			return array('success' => true,'Link' => $file_zip_name);
		}
		else {
			return array('success' => false, 'Error_Msg' => toUtf('Ошибка создания архива!'));
		}
	}

	/**
	 * Импорт ответа от ФСС
	 */
	/*function importRegistryESFromXML() {
		$data = $this->ProcessInputData('importRegistryESFromXML', true);
		if ($data === false) { return false; }

		$upload_path = './'.IMPORTPATH_ROOT.$_SESSION['lpu_id'].'/';
		$allowed_types = explode('|','zip|rar|xml');

		set_time_limit(0);

		if (!isset($_FILES['RegistryESFile'])) {
			$this->ReturnData( array('success' => false, 'Error_Msg' => toUTF('Не выбран файл реестра!') ) ) ;
			return false;
		}

		if (!is_uploaded_file($_FILES['RegistryESFile']['tmp_name']))
		{
			$error = (!isset($_FILES['RegistryESFile']['error'])) ? 4 : $_FILES['RegistryESFile']['error'];
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
			$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF($message)));
			return false;
		}

		// Тип файла разрешен к загрузке?
		$x = explode('.', $_FILES['RegistryESFile']['name']);
		$file_data['file_ext'] = end($x);
		if (!in_array(strtolower($file_data['file_ext']), $allowed_types)) {
			$this->ReturnData(array('success' => false, 'Error_Code' => 100013 , 'Error_Msg' => toUTF('Данный тип файла не разрешен.')));
			return false;
		}

		// Правильно ли указана директория для загрузки?
		if (!@is_dir($upload_path))
		{
			mkdir( $upload_path );
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

		if ($file_data['file_ext'] == 'xml') {
			$xmlfile = $_FILES['RegistryESFile']['name'];
			if (!move_uploaded_file($_FILES["RegistryESFile"]["tmp_name"], $upload_path.$xmlfile)){
				$this->ReturnData( array('success' => false, 'Error_Code' => 100016 , 'Error_Msg' => toUTF('Не удаётся переместить файл.')));
				return false;
			}
		} else {
			// там должен быть файл .xml, если его нет -> файл не является архивом реестра
			$zip = new ZipArchive;
			if ($zip->open($_FILES["RegistryESFile"]["tmp_name"]) === TRUE)
			{

				$xmlfile = "";
				for($i=0; $i<$zip->numFiles; $i++){
					$filename = $zip->getNameIndex($i);
					if ( preg_match('/OUT.*.xml/', $filename) > 0 ) {
						$xmlfile = $filename;
					}
				}

				$zip->extractTo( $upload_path );
				$zip->close();
			}
			unlink($_FILES["RegistryESFile"]["tmp_name"]);
		}

		if (empty($xmlfile))
		{
			$this->ReturnData( array('success' => false, 'Error_Code' => 100017 , 'Error_Msg' => toUTF('Файл не является архивом реестра.')));
			return false;
		}

		// Удаляем ответ по этому реестру, если он уже был загружен
		$this->dbmodel->deleteRegistryESError($data, 'fss');

		$recAll = $this->dbmodel->getRegistryESDataCount($data);
		$recErr = 0;

		libxml_use_internal_errors(true);

		$dom = new DOMDocument();
		$res = $dom->load($upload_path.$xmlfile);

		foreach (libxml_get_errors() as $error) {
			$this->ReturnData( array('success' => false, 'Error_Code' => 100018 , 'Error_Msg' => toUTF('Файл не является архивом реестра.')));
			return false;
		}
		libxml_clear_errors();

		$params = array();
		$params['RegistryES_id'] = $data['RegistryES_id'];
		$params['pmUser_id'] = $data['pmUser_id'];

		$ROW_list = $dom->getElementsByTagName('ROW');
		foreach($ROW_list as $ROW) {
			$params['EvnStick_Num'] = '';
			$params['RegistryESDataStatus_id'] = 3;
			$params['Error_Code'] = '';
			$params['Error_Message'] = '';

			$RESULT_list = $ROW->getElementsByTagName('RESULT');
			foreach($RESULT_list as $RESULT) {
				$STATUS_list = $RESULT->getElementsByTagName('STATUS');
				foreach($STATUS_list as $STATUS) {
					if ($STATUS->nodeValue == 0) {
						$params['RegistryESDataStatus_id'] = 2;
					}
				}

				if ($params['RegistryESDataStatus_id'] == 3) {
					$ERROE_CODE_list = $RESULT->getElementsByTagName('ERROR_CODE');
					foreach($ERROE_CODE_list as $ERROE_CODE) {
						$params['Error_Code'] = $ERROE_CODE->nodeValue;
					}
					$ERROE_MESSAGE_list = $RESULT->getElementsByTagName('ERROR_MESSAGE');
					foreach($ERROE_MESSAGE_list as $ERROE_MESSAGE) {
						$params['Error_Message'] = $ERROE_MESSAGE->nodeValue;
					}

					$LN_CODE_list = $RESULT->parentNode->getElementsByTagName('LN_CODE');
					foreach($LN_CODE_list as $LN_CODE) {
						$params['EvnStick_Num'] = $LN_CODE->nodeValue;

						$registry_data = $this->dbmodel->getRegistryESDataForImport($params);
						if (!is_array($registry_data) || count($registry_data) == 0 || empty($registry_data[0]['Evn_id'])) {
							$this->dbmodel->deleteRegistryESError($data, 'fss');
							$this->ReturnData(array('success' => false, 'Error_Msg' => "ЛВН № {$params['EvnStick_Num']} обнаружен в импортируемом файле, но отсутствует в реестре, импорт не произведен"));
							return false;
						}
						$params['Evn_id'] = $registry_data[0]['Evn_id'];

						$recErr++;
						$resp = $this->dbmodel->setRegistryESError($params);
						if (!empty($resp[0]['Error_Msg'])) {
							$this->dbmodel->deleteRegistryESError($data, 'fss');
							$this->ReturnData(array('success' => false, 'Error_Msg' => $resp[0]['Error_Msg']));
							return false;
						}
					}
				}

				$resp = $this->dbmodel->setRegistryESDataStatus($params);
				if (!empty($resp[0]['Error_Msg'])) {
					$this->dbmodel->deleteRegistryESError($data, 'fss');
					$this->ReturnData(array('success' => false, 'Error_Msg' => $resp[0]['Error_Msg']));
					return false;
				}
			}
		}

		$resp = $this->dbmodel->setAllRegistryESDataStatus(array(
			'RegistryES_id' => $data['RegistryES_id'],
			'RegistryESDataStatus_id' => 2
		));
		if (!empty($resp[0]['Error_Msg'])) {
			$this->dbmodel->deleteRegistryESError($data, 'fss');
			$this->ReturnData(array('success' => false, 'Error_Msg' => $resp[0]['Error_Msg']));
			return false;
		}

		$resp = $this->dbmodel->setRegistryESErrorCount(array(
			'RegistryES_id' => $data['RegistryES_id'],
			'RegistryES_ErrorCount' => $recErr
		));
		if (!empty($resp[0]['Error_Msg'])) {
			$this->dbmodel->deleteRegistryESError($data, 'fss');
			$this->ReturnData(array('success' => false, 'Error_Msg' => $resp[0]['Error_Msg']));
			return false;
		}

		$resp = $this->dbmodel->updateEvnStickIsInReg(array(
			'RegistryES_id' => $data['RegistryES_id']
		));
		if (!empty($resp[0]['Error_Msg'])) {
			$this->dbmodel->deleteRegistryESError($data, 'fss');
			$this->ReturnData(array('success' => false, 'Error_Msg' => $resp[0]['Error_Msg']));
			return false;
		}

		$response = $this->dbmodel->setRegistryESStatus(array(
			'RegistryES_id' => $data['RegistryES_id'],
			'RegistryESStatus_id' => ($recErr<$recAll) ? 5 : 6
		));
		if (!empty($response[0]['Error_Msg'])) {
			$this->dbmodel->deleteRegistryESError($data, 'fss');
			$this->ReturnData(array('success' => false, 'Error_Msg' => $response[0]['Error_Msg']));
			return false;
		}

		$this->ReturnData(array('success' => true, 'RegistryES_id' => $data['RegistryES_id'], 'recErr' => $recErr, 'recAll'=>$recAll, 'Message' => 'Реестр успешно загружен.'));
		return true;
	}*/

	/**
	 * Импорт ответа от ФСС
	 */
	public function importRegistryESFromXML() {
		$data = $this->ProcessInputData('importRegistryESFromXML', true);
		if ($data === false) { return false; }

		$upload_path = './'.IMPORTPATH_ROOT.$_SESSION['lpu_id'].'/';
		$allowed_types = explode('|','zip|rar|xml');

		set_time_limit(0);

		if (!isset($_FILES['RegistryESFile'])) {
			$this->ReturnData( array('success' => false, 'Error_Msg' => toUTF('Не выбран файл реестра!') ) ) ;
			return false;
		}

		if (!is_uploaded_file($_FILES['RegistryESFile']['tmp_name']))
		{
			$error = (!isset($_FILES['RegistryESFile']['error'])) ? 4 : $_FILES['RegistryESFile']['error'];
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
			$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF($message)));
			return false;
		}

		// Тип файла разрешен к загрузке?
		$x = explode('.', $_FILES['RegistryESFile']['name']);
		$file_data['file_ext'] = end($x);
		if (!in_array(strtolower($file_data['file_ext']), $allowed_types)) {
			$this->ReturnData(array('success' => false, 'Error_Code' => 100013 , 'Error_Msg' => toUTF('Данный тип файла не разрешен.')));
			return false;
		}

		// Правильно ли указана директория для загрузки?
		if (!@is_dir($upload_path))
		{
			mkdir( $upload_path );
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

		if ($file_data['file_ext'] == 'xml') {
			$xmlfile = $_FILES['RegistryESFile']['name'];
			if (!move_uploaded_file($_FILES["RegistryESFile"]["tmp_name"], $upload_path.$xmlfile)){
				$this->ReturnData( array('success' => false, 'Error_Code' => 100016 , 'Error_Msg' => toUTF('Не удаётся переместить файл.')));
				return false;
			}
		} else {
			// там должен быть файл .xml, если его нет -> файл не является архивом реестра
			$zip = new ZipArchive;
			if ($zip->open($_FILES["RegistryESFile"]["tmp_name"]) === TRUE)
			{

				$xmlfile = "";
				for($i=0; $i<$zip->numFiles; $i++){
					$filename = $zip->getNameIndex($i);
					if ( preg_match('/OUT.*.xml/', $filename) > 0 ) {
						$xmlfile = $filename;
					}
				}

				$zip->extractTo( $upload_path );
				$zip->close();
			}
			unlink($_FILES["RegistryESFile"]["tmp_name"]);
		}

		if (empty($xmlfile))
		{
			$this->ReturnData( array('success' => false, 'Error_Code' => 100017 , 'Error_Msg' => toUTF('Файл не является архивом реестра.')));
			return false;
		}

		// Удаляем ответ по этому реестру, если он уже был загружен
		$this->dbmodel->deleteRegistryESError($data, 'fss');

		$recAll = $this->dbmodel->getRegistryESDataCount($data);
		$recErr = 0;

		libxml_use_internal_errors(true);

		$dom = new DOMDocument();
		$res = $dom->load($upload_path.$xmlfile);

		foreach (libxml_get_errors() as $error) {
			$this->ReturnData( array('success' => false, 'Error_Code' => 100018 , 'Error_Msg' => toUTF('Файл не является архивом реестра.')));
			return false;
		}
		libxml_clear_errors();

		$params = array();
		$params['RegistryES_id'] = $data['RegistryES_id'];
		$params['pmUser_id'] = $data['pmUser_id'];

		$ROW_list = $dom->getElementsByTagName('ROW');
		foreach($ROW_list as $ROW) {
			$params['EvnStick_Num'] = '';
			$params['RegistryESDataStatus_id'] = 3;
			$params['Error_Code'] = '';
			$params['Error_Message'] = '';

			$RESULT_list = $ROW->getElementsByTagName('RESULT');
			foreach($RESULT_list as $RESULT) {
				$STATUS_list = $RESULT->getElementsByTagName('STATUS');
				foreach($STATUS_list as $STATUS) {
					if ($STATUS->nodeValue == 0) {
						$params['RegistryESDataStatus_id'] = 2;
					}
				}

				if ($params['RegistryESDataStatus_id'] == 3) {
					$ERROE_CODE_list = $RESULT->getElementsByTagName('ERROR_CODE');
					foreach($ERROE_CODE_list as $ERROE_CODE) {
						$params['Error_Code'] = $ERROE_CODE->nodeValue;
					}
					$ERROE_MESSAGE_list = $RESULT->getElementsByTagName('ERROR_MESSAGE');
					foreach($ERROE_MESSAGE_list as $ERROE_MESSAGE) {
						$params['Error_Message'] = $ERROE_MESSAGE->nodeValue;
					}

					$LN_CODE_list = $RESULT->parentNode->getElementsByTagName('LN_CODE');
					foreach($LN_CODE_list as $LN_CODE) {
						$params['EvnStick_Num'] = $LN_CODE->nodeValue;

						$registry_data = $this->dbmodel->getRegistryESDataForImport($params);
						if (!is_array($registry_data) || count($registry_data) == 0 || empty($registry_data[0]['Evn_id'])) {
							$this->dbmodel->deleteRegistryESError($data, 'fss');
							$this->ReturnData(array('success' => false, 'Error_Msg' => "ЛВН № {$params['EvnStick_Num']} обнаружен в импортируемом файле, но отсутствует в реестре, импорт не произведен"));
							return false;
						}
						$params['Evn_id'] = $registry_data[0]['Evn_id'];

						$recErr++;
						$resp = $this->dbmodel->setRegistryESError($params);
						if (!empty($resp[0]['Error_Msg'])) {
							$this->dbmodel->deleteRegistryESError($data, 'fss');
							$this->ReturnData(array('success' => false, 'Error_Msg' => $resp[0]['Error_Msg']));
							return false;
						}
					}
				}

				$resp = $this->dbmodel->setRegistryESDataStatus($params);
				if (!empty($resp[0]['Error_Msg'])) {
					$this->dbmodel->deleteRegistryESError($data, 'fss');
					$this->ReturnData(array('success' => false, 'Error_Msg' => $resp[0]['Error_Msg']));
					return false;
				}
			}
		}

		$resp = $this->dbmodel->setAllRegistryESDataStatus(array(
			'RegistryES_id' => $data['RegistryES_id'],
			'RegistryESDataStatus_id' => 2
		));
		if (!empty($resp[0]['Error_Msg'])) {
			$this->dbmodel->deleteRegistryESError($data, 'fss');
			$this->ReturnData(array('success' => false, 'Error_Msg' => $resp[0]['Error_Msg']));
			return false;
		}

		$resp = $this->dbmodel->setRegistryESErrorCount(array(
			'RegistryES_id' => $data['RegistryES_id'],
			'RegistryES_ErrorCount' => $recErr
		));
		if (!empty($resp[0]['Error_Msg'])) {
			$this->dbmodel->deleteRegistryESError($data, 'fss');
			$this->ReturnData(array('success' => false, 'Error_Msg' => $resp[0]['Error_Msg']));
			return false;
		}

		$resp = $this->dbmodel->updateEvnStickIsInReg(array(
			'RegistryES_id' => $data['RegistryES_id']
		));
		if (!empty($resp[0]['Error_Msg'])) {
			$this->dbmodel->deleteRegistryESError($data, 'fss');
			$this->ReturnData(array('success' => false, 'Error_Msg' => $resp[0]['Error_Msg']));
			return false;
		}

		$response = $this->dbmodel->setRegistryESStatus(array(
			'RegistryES_id' => $data['RegistryES_id'],
			'RegistryESStatus_id' => ($recErr<$recAll) ? 5 : 6
		));
		if (!empty($response[0]['Error_Msg'])) {
			$this->dbmodel->deleteRegistryESError($data, 'fss');
			$this->ReturnData(array('success' => false, 'Error_Msg' => $response[0]['Error_Msg']));
			return false;
		}

		$this->ReturnData(array('success' => true, 'RegistryES_id' => $data['RegistryES_id'], 'recErr' => $recErr, 'recAll'=>$recAll, 'Message' => 'Реестр успешно загружен.'));
		return true;
	}

	/**
	 * Подготовка реестра к отправке в ФСС
	 */
	public function requestRegistryESToFss() {
		$data = $this->ProcessInputData('requestRegistryESToFss', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->requestRegistryESToFss($data);
		$this->ProcessModelSave($response, true, 'Ошибка при подготовке к отправке запроса в ФСС')->ReturnData();

		return true;
	}

	/**
	 * Фикс реестров не до конца обработанных при отправке реестра в ФСС.
	 */
	public function fixRegistryES() {
		$data = $this->ProcessInputData('fixRegistryES', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->fixRegistryES($data);
		$this->ProcessModelSave($response, true, 'Ошибка при исправлении данных реестра')->ReturnData();

		return true;
	}

	/**
	 * Отправка запросов на проверку данных в ФСС
	 */
	public function checkRegistryESDataInFSS() {
		$data = $this->ProcessInputData('checkRegistryESDataInFSS', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->checkRegistryESDataInFSS($data);
		$this->ProcessModelSave($response, true, 'Ошибка при отправке запроса в ФСС')->ReturnData();

		return true;
	}

	/**
	 * Просмотр файлов отправленных в ФСС по реестру
	 */
	public function showFiles() {
		$data = $this->ProcessInputData('showFiles', true);
		if ($data === false) { return false; }

		$this->dbmodel->showFiles($data);

		return true;
	}

	/**
	 * Отправка всех реестров в ФСС
	 */
	public function sendRegistryESToFss() {
		$data = $this->ProcessInputData('sendRegistryESToFss', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->sendRegistryESToFss($data);
		$this->ProcessModelSave($response, true, 'Ошибка при отправке запроса в ФСС')->ReturnData();

		return true;
	}

	/**
	 * Проверка ФЛК для всех реестров
	 */
	public function doFLKControlForAll() {
		$data = $this->ProcessInputData('doFLKControlForAll', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->doFLKControlForAll($data);
		$this->ProcessModelSave($response, true, 'Ошибка при проверка ФЛК')->ReturnData();

		return true;
	}

	/**
	 * Сохранение выбранных сертификатов для подписи
	 */
	public function saveRegistryESIndividCert() {
		$data = $this->ProcessInputData('saveRegistryESIndividCert', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveRegistryESIndividCert($data);
		$this->ProcessModelSave($response, true, 'Ошибка сохранения выбранных сертификатов для подписи')->ReturnData();

		return true;
	}

	/**
	 * Получение данных для подписи
	 */
	public function getUnsignedData() {
		$data = $this->ProcessInputData('getUnsignedData', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getUnsignedData($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Распарсивание XML-ек принятых реестров
	 */
	public function parseXmls() {
		if (!isSuperadmin()) {
			$this->ReturnError('Недостаточно прав для выполнениея метода');
			return false;
		}

		$data = $this->ProcessInputData('parseXmls', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->parseXmls($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
	}

	/**
	 * Сохраняет данные из XML в StickFssDataGet
	 */
	public function UploadXML() {
		$is_debug = $this->config->item('IS_DEBUG');
		if( empty($is_debug) ) {
			throw new Exception("Доступно только в тестовой среде");
		}
		if (!isset($_FILES['userfile'])) {
			throw new Exception("Не выбран файл");
		}
		$data = $this->ProcessInputData('UploadXML', true);
		if ($data === false) { return false; }
		$data['uploadTestFile'] = true;
		$xml = file_get_contents($_FILES['userfile']['tmp_name']);

		$result = array();
		$data['xml'] = $xml;
		$response = $this->dbmodel->XMLtoStickFssDataGet($data);
		if (is_array($response)) {
			$result = $response;
		}
		$result['success'] = empty($result['Error_Msg']);

		$this->ReturnData($result);
		return true;
	}
}