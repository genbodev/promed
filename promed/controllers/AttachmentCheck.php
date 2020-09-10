<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * AttachmentCheck - контроллер для работы с параметрами проверки прикреплений
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 * @author			Max Sysolin (max.sysolin@gmail.com)
 * @version			20.04.2017
 *
 * @property AttachmentCheck_model dbmodel
 */

class AttachmentCheck extends swController {

    protected $inputRules = array(
        'loadAttachmentCheckGrid' => array(
            array(
                'field' => 'AttachmentCheck_id',
                'label' => 'Идентификатор проверки прикрепления',
                'rules' => '',
                'type' => 'id'
            )
        ),
        'getLpuSectionProfiles' => array(
            array(
                'field' => 'lpu_id_filter',
                'label' => 'Идентификатор МО',
                'rules' => '',
                'type' => 'id'
            ),
            array(
                'field' => 'LpuAttachType_id',
                'label' => 'Идентификатор типа прикрепеления',
                'rules' => '',
                'type' => 'id'
            )
        ),
        'getMedSpecs' => array(
            array(
                'field' => 'lpu_id_filter',
                'label' => 'Идентификатор МО',
                'rules' => '',
                'type' => 'id'
            ),
            array(
                'field' => 'LpuAttachType_id',
                'label' => 'Идентификатор типа прикрепеления',
                'rules' => '',
                'type' => 'id'
            )
        ),
        'getAttachmentCheckRecord' => array(
            array(
                'field' => 'AttachmentCheck_id',
                'label' => 'Идентификатор проверки прикрепления',
                'rules' => '',
                'type' => 'id'
            )
        ),
        'deleteAttachmentCheck' => array(
            array(
                'field' => 'AttachmentCheck_id',
                'label' => 'Идентификатор проверки прикрепления',
                'rules' => '',
                'type' => 'id'
            )
        ),
        'saveAttachmentCheckRecord' => array(
            array(
                'field' => 'AttachmentCheck_id',
                'label' => 'Идентификатор проверки прикрепления',
                'rules' => '',
                'type' => 'id'
            ),
            array(
                'field' => 'LpuAttachType_id',
                'label' => 'Идентификатор типа прикрепления',
                'rules' => '',
                'type' => 'id'
            ),
            array(
                'field' => 'ACEW_Lpu_id',
                'label' => 'Идентификатор МО',
                'rules' => '',
                'type' => 'id'
            ),
            array(
                'field' => 'LpuSectionProfile_id',
                'label' => 'Идентификатор профиля',
                'rules' => '',
                'type' => 'id'
            ),
            array(
                'field' => 'MedSpecOms_id',
                'label' => 'Идентификатор специальности',
                'rules' => '',
                'type' => 'id'
            ),
            array(
                'field' => 'AttachmentCheck_CheckOn',
                'label' => 'Проверка прикрепления включена',
                'rules' => '',
                'type' => 'int'
            ),
            array(
                'field' => 'dateRange',
                'label' => 'Дата начала проверки прикрепления',
                'rules' => '',
                'type' => 'string'
            ),
            array(
                'field' => 'AttachmentCheck_Period',
                'label' => 'Дата начала проверки прикрепления',
                'rules' => '',
                'type' => 'string'
            )
        ),
		'uploadFileImport' => array(
			array(
                'field' => 'actions',
                'label' => 'действия',
                'rules' => '',
                'type' => 'string'
            ),
			array(
                'field' => 'currentForm',
                'label' => 'Форма из которой происходит импорт',
                'rules' => '',
                'type' => 'string'
            ),
		),
		'exportAttachmentsApplications' => array(
			array(
                'field' => 'Lpu_id',
                'label' => 'МО',
                'rules' => 'required',
                'type' => 'id'
            ),
			array(
                'field' => 'PackageNumber',
                'label' => 'Номер пакета',
                'rules' => 'required',
                'type' => 'string'
            ),
			array(
                'field' => 'OrgSMO_id',
                'label' => 'СМО',
                'rules' => 'required',
                'type' => 'id'
            ),
			array(
                'field' => 'begDate',
                'label' => 'Дата начала периода',
                'rules' => 'required',
                'type' => 'date'
            ),
			array(
                'field' => 'endDate',
                'label' => 'Дата окончания периода',
                'rules' => 'required',
                'type' => 'date'
            ),
		)
    );

    /**
     * Конструктор
     */
    function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('AttachmentCheck_model', 'dbmodel');
    }

    /**
     * Возвращает список параметров проверки прикреплений
     * @return bool
     */
    function loadAttachmentCheckGrid()
    {
        $data = $this->ProcessInputData('loadAttachmentCheckGrid', true);
        if ($data === false) { return false; }

        $response = $this->dbmodel->loadAttachmentCheckGrid($data);
        $this->ProcessModelMultiList($response, true, 'При получении данных возникли ошибки')->ReturnData();
        return true;
    }

    /**
     * Возвращает запись проверки прикрепления
     * @return bool
     */
    function getAttachmentCheckRecord()
    {
        $data = $this->ProcessInputData('getAttachmentCheckRecord', true);
        if ($data === false) { return false; }

        $response = $this->dbmodel->getAttachmentCheckRecord($data);
        $this->ProcessModelList($response,true,true)->ReturnData();
        return true;
    }

    /**
     * Сохраняет запись проверки прикрепления
     * @return bool
     */
    function saveAttachmentCheckRecord()
    {
        $data = $this->ProcessInputData('saveAttachmentCheckRecord', true);
        if ($data === false) { return false; }

        $response = $this->dbmodel->saveAttachmentCheckRecord($data);
        $this->ProcessModelSave($response, true, 'При сохранении возникли ошибки')->ReturnData();
        return true;
    }

    /**
     * Удаляет запись проверки прикрепления
     * @return bool
     */
    function deleteAttachmentCheck()
    {
        $data = $this->ProcessInputData('deleteAttachmentCheck', true);
        if ($data === false) { return false; }

        $response = $this->dbmodel->deleteAttachmentCheck($data);
        $this->ProcessModelSave($response, true, 'При удалении возникли ошибки')->ReturnData();
        return true;
    }

    /**
     * Возвращает список профилей для указанного типа прикрепления и МО
     * @return bool
     */
    function getLpuSectionProfiles()
    {
        $data = $this->ProcessInputData('getLpuSectionProfiles', true);
        if ($data === false) { return false; }

        $response = $this->dbmodel->getLpuSectionProfiles($data);
        $this->ProcessModelList($response, true, 'При получении данных возникли ошибки')->ReturnData();
        return true;
    }

    /**
     * Возвращает список мед. специальностей для указанного типа прикрепления и МО
     * @return bool
     */
    function getMedSpecs()
    {
        $data = $this->ProcessInputData('getMedSpecs', true);
        if ($data === false) { return false; }

        $response = $this->dbmodel->getMedSpecs($data);
        $this->ProcessModelList($response, true, 'При получении данных возникли ошибки')->ReturnData();
        return true;
    }
	
	/**
	 * Экспорт прикреплений / заявлений
	 */
	function exportAttachmentsApplications(){
		$data = $this->ProcessInputData('exportAttachmentsApplications', true);
		
		$put = EXPORTPATH_ATACHED_LIST.getRegionNick().'_exportAttachmentsApplications_'.date("dmY").'/';
		
		if ( ! file_exists($put)){
			mkdir( $put );
		}
		
		set_time_limit(0); //обязательно, иначе на больших объемах выгружаемых данных до конца не выполнится
		
		$this->load->model('Polka_PersonCard_model', 'Polka_PersonCard_model');
		$res = $this->Polka_PersonCard_model->exportAttachmentsApplicationsDBF($data);
		$result = $res['result'];
		$nameFile = $res['nameFille'];
		$attachesFields = $res['attachesFields'];
		$count = 0;
		$nameFileZ = $put.$nameFile.'Z.dbf';
		$nameFileA = $put.$nameFile.'A.dbf';
        
		// формируем DBF
		$z = dbase_create( $nameFileZ, $attachesFields );
		$a = dbase_create( $nameFileA, $attachesFields );
		if (is_object($result)) {
			while ($row = $result->_fetch_assoc()) {
				if ($row['OP'] != 'Р') {
					$row['LPUDZ'] = '';
				}

				if ($row['OP'] != 'И') {
					$row['LPUDU'] = '';
				}

				array_walk($row, 'ConvertFromUtf8ToCp866');

				if(empty($row['FAM_DOC'])){
					//Прикрепления без врача, попадают в лог с ошибками
				}else if($row['file_package'] == 'Z'){
					unset($row['file_package']);
					dbase_add_record( $z, array_values($row) );
				}else{
					unset($row['file_package']);
					dbase_add_record( $a, array_values($row) );
				}
				$count++;
			}
		}
		dbase_close ($z);
		dbase_close ($a);
		if ($count == 0){
			$this->ReturnData(array('success' => true, 'link' => false, 'count' => $count));
			return false;
		}

		$zip = new ZipArchive();
		$file_zip_name = $put.$nameFile."_".time().".zip";
		$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
		$zip->AddFile( $nameFileZ, $nameFile.'Z.dbf' );
		$zip->AddFile( $nameFileA, $nameFile.'A.dbf' );
		$zip->close();
		unlink($nameFileZ);
		unlink($nameFileA);
		if (file_exists($file_zip_name))
		{
			$this->ReturnData(array('success' => true, 'link' => $file_zip_name, 'count' => $count));
		}
		else
		{
			$this->ReturnError('Ошибка создания архива экспорта');
		}
	}
	
	/**
	 * используется форма загрузки файла для функционала: Импорт ошибок ФЛК по ЗЛ, Импорт ответа от СМО по ЗЛ, Импорт территориальных прикреплений/ откреплений
	 */
	function uploadFileImport(){
		$data = $this->ProcessInputData('uploadFileImport', true);
		
		$namesImport = array(
			'ImportErrors_FLKforZL' => 'Импорт ошибок ФЛК по ЗЛ',
			'ImportResponseFrom_SMOforPL' => 'Импорт ответа от СМО по ЗЛ',
			'ImportOfTerritorialAttachmentsDetachments' => 'Импорт территориальных прикреплений/ откреплений'
		);
		
		$nameLogFile = 'uploadFileImport';
		
		if(empty($data['actions']) || empty($namesImport[$data['actions']])) return false;
		$action = $namesImport[$data['actions']];
		
		$upload_path = './'.IMPORTPATH_ROOT.getRegionNick().'_uploadFileImport/'.date("dmY").'/';
		$allowed_types = explode('|','zip|rar|dbf');

		set_time_limit(0); // обязательно, иначе на больших объемах выгружаемых данных до конца не выполнится
		
		if (!isset($_FILES['file'])) {
			$this->ReturnData( array('success' => false, 'Error_Code' => 100011 , 'Error_Msg' => toUTF('Не выбран файл') ) ) ;
			return false;
		}

		if (!is_uploaded_file($_FILES['file']['tmp_name']))
		{
			$error = (!isset($_FILES['file']['error'])) ? 4 : $_FILES['file']['error'];
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
		
		// Тип файла разрешен к загрузке?
		$x = explode('.', $_FILES['file']['name']);
		$file_data['file_ext'] = end($x);
		if (!in_array(strtolower($file_data['file_ext']), $allowed_types)) {
			$this->ReturnData(array('success' => false, 'Error_Code' => 100013 , 'Error_Msg' => toUTF('Данный тип файла не разрешен.')));
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

		if (strtolower($file_data['file_ext'] == 'dbf')) {
			$dbffile = $_FILES['file']['name'];
			if (!move_uploaded_file($_FILES["file"]["tmp_name"], $upload_path.$dbffile)){
				$this->ReturnData( array('success' => false, 'Error_Code' => 100015 , 'Error_Msg' => toUTF('Не удаётся переместить файл.')));
				return false;
			}
		} else {
			// там должен быть файл .dbf, если его нет -> файл не является ответом сегмента
			$zip = new ZipArchive;
			if ($zip->open($_FILES["file"]["tmp_name"]) === TRUE) {
				$dbffile = "";

				for($i=0; $i<$zip->numFiles; $i++){
					$filename = $zip->getNameIndex($i);
					if ( preg_match('/\.dbf/', strtolower($filename)) > 0 ) {
						$dbffile = $filename;
					}
				}

				$zip->extractTo( $upload_path );
				$zip->close();
			}
			unlink($_FILES["file"]["tmp_name"]);
		}

		if (empty($dbffile))
		{
			$this->ReturnData( array('success' => false, 'Error_Code' => 100015 , 'Error_Msg' => toUTF('Файл не является архивом ответа.')));
			return false;
		}
		
		$nameLogFile = $dbffile.'_log';
		
		try {
			error_reporting('E_WARNING');
			$handler = dbase_open($upload_path.$dbffile, 0);
			if(!$handler) throw new Exception('Не получается открыть файл !!!');
		} catch (Exception $ex) {
			$this->ReturnData(array('success' => false, 'action' => $data['actions'], 'Error_Code' => 6, 'Error_Msg' => $ex));
			return false;
		}
		
		$this->load->model('Polka_PersonCard_model', 'Polka_PersonCard_model');
		
		switch ($data['actions']) {
			case 'ImportErrors_FLKforZL':
				//Импорт ошибок ФЛК по ЗЛ
				$result = $this->Polka_PersonCard_model->verificationImportErrors_FLKforZL_DBF($handler, $data, $nameLogFile.'.txt');
				break;
			case 'ImportResponseFrom_SMOforPL':
				//Импорт ответа от СМО по ЗЛ
				$result = $this->Polka_PersonCard_model->verificationImportResponseFrom_SMOforPL_DBF($handler, $data, $nameLogFile.'.txt');
				break;
			case 'ImportOfTerritorialAttachmentsDetachments':
				//Импорт территориальных прикреплений/ откреплений
				$result = $this->Polka_PersonCard_model->verificationImportOfTerritorialAttachmentsDetachmentsDBF($handler, $data, $nameLogFile.'.txt');
				break;
			default:
				break;
		}
		$fileLog = (!empty($result['fileLog'])) ? $fileLog = $result['fileLog'] : '';
		if($result['success']){
			$this->ReturnData(array('success' => true, 'action' => $data['actions'], 'Error_Code' => 0, 'Link' => $fileLog));
			return true;
		}else{
			$this->ReturnData(array('success' => false, 'Error_Code' => 7, 'action' => $data['actions'], 'Link' => $fileLog, 'Error_Msg' => toUTF($result['Err_msg'])));
			return false;
		}
	}
}