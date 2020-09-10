<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Registry - операции с реестрами
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2013 Swan Ltd.
* @author       Bykov Stas aka Savage (savage@swan.perm.ru)
* @version      10.06.2013
*/
require_once(APPPATH.'controllers/Registry.php');

class Astra_Registry extends Registry {
	var $scheme = "r30";
	var $model_name = "Registry_model";

	/**
	 *	Конструктор
	 */
	function __construct() {
		parent::__construct();
		// Инициализация класса и настройки

		$this->inputRules['saveRegistry'] = array(
			array(
				'field' => 'OrgSMO_id',
				'label' => 'СМО',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => null,
				'field' => 'DispClass_id',
				'label' => 'Тип дисп-ции/медосмотра:',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'KatNasel_id',
				'label' => 'Категория населения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => Null,
				'field' => 'LpuBuilding_id',
				'label' => 'Подразделение',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Registry_Num',
				'label' => 'Номер счета',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'RegistryType_id',
				'label' => 'Тип реестра',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryStatus_id',
				'label' => 'Статус реестра',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryStacType_id',
				'label' => 'Тип реестра стационара',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Registry_IsActive',
				'label' => 'Признак активного регистра',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'OrgRSchet_id',
				'label' => 'Расчетный счет',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Registry_accDate',
				'label' => 'Дата счета',
				'rules' => 'trim|required',
				'type' => 'date'
			),
			array(
				'field' => 'Registry_begDate',
				'label' => 'Начало периода',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'Registry_endDate',
				'label' => 'Окончание периода',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'Registry_IsFinanc',
				'label' => 'Подушевое финансирование',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Registry_IsZNO',
				'label' => 'ЗНО',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Registry_IsOnceInTwoYears',
				'label' => 'Признак "Раз в 2 года"',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'OMSSprTerr_id',
				'label' => 'Список территорий страхования',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'default' => null,
				'field' => 'PayType_id',
				'label' => 'Вид оплаты',
				'rules' => '',
				'type' => 'id'
			)
		);
		
		$this->inputRules['importRegistryFromXml'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryFile',
				'label' => 'Файл',
				'rules' => '',
				'type' => 'string'
			)
		);
		
		$this->inputRules['importRegistryFromTFOMS'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryFile',
				'label' => 'Файл',
				'rules' => '',
				'type' => 'string'
			)
		);

		$this->inputRules['exportRegistryToXml'] = array(
			array(
				'field' => 'OverrideControlFlkStatus',
				'label' => 'Флаг пропуска контроля на статус Проведен контроль ФЛК',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'OverrideExportOneMoreOrUseExist',
				'label' => 'Флаг использования существующего или экспорта нового XML',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'onlyLink',
				'label' => 'Флаг вывода только ссылки',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryType_id',
				'label' => 'Тип реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'forSign',
				'label' => 'Флаг',
				'rules' => '',
				'type' => 'int'
			)
		);

		$this->inputRules['exportRegistryInog'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryType_id',
				'label' => 'Тип реестра',
				'rules' => '',
				'type' => 'id'
			),
		);

		$this->inputRules['loadUnionRegistryErrorTFOMS'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_FIO',
				'label' => 'ФИО',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Person_SurName',
				'label' => 'Фамилия',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Person_FirName',
				'label' => 'Имя',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Person_SecName',
				'label' => 'Отчество',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'RegistryErrorType_Code',
				'label' => 'Код ошибки',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'RegistryErrorClass_id',
				'label' => 'Вид ошибки',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Evn_id',
				'label' => 'ИД случая',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryErrorTFOMS_Comment',
				'label' => 'Комментарий',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'default' => 0,
				'field' => 'start',
				'label' => 'Начальный номер записи',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 100,
				'field' => 'limit',
				'label' => 'Количество возвращаемых записей',
				'rules' => '',
				'type' => 'int'
			)
		);

		$this->inputRules['saveUnionRegistry'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор объединённого реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Registry_Num',
				'label' => 'Номер',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'Registry_accDate',
				'label' => 'Дата счета',
				'rules' => 'trim|required',
				'type' => 'date'
			),
			array(
				'field' => 'Registry_begDate',
				'label' => 'Начало периода',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'Registry_endDate',
				'label' => 'Окончание периода',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'KatNasel_id',
				'label' => 'Категория населения',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryGroupType_id',
				'label' => 'Тип объединенного реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_id',
				'label' => 'Лпу',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ignoreExistTFOMSError',
				'label' => 'Признак игнорирования наличия ошибок ТФОМС',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Registry_IsZNO',
				'label' => 'ЗНО',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Registry_IsOnceInTwoYears',
				'label' => 'Признак "Раз в 2 года"',
				'rules' => '',
				'type' => 'id'
			),
		);

		$this->inputRules['loadUnionRegistryGrid'] = array(
			array(
				'field' => 'Lpu_id',
				'label' => 'ЛПУ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'start',
				'label' => 'Начальный номер записи',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 100,
				'field' => 'limit',
				'label' => 'Количество возвращаемых записей',
				'rules' => '',
				'type' => 'int'
			)
		);

		$this->inputRules['getUnionRegistryNumber'] = array(
			array(
				'field' => 'Lpu_id',
				'label' => 'ЛПУ',
				'rules' => 'required',
				'type' => 'id'
			)
		);

		$this->inputRules['loadUnionRegistryChildGrid'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор объединённого реестра',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'start',
				'label' => 'Начальный номер записи',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 100,
				'field' => 'limit',
				'label' => 'Количество возвращаемых записей',
				'rules' => '',
				'type' => 'int'
			)
		);

		$this->inputRules['loadUnionRegistryEditForm'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор объединённого реестра',
				'rules' => 'required',
				'type' => 'id'
			)
		);

		$this->inputRules['deleteUnionRegistry'] = array(
			array(
				'field' => 'id',
				'label' => 'Идентификатор объединённого реестра',
				'rules' => 'required',
				'type' => 'id'
			)
		);

		$this->inputRules['setUnionRegistryStatus'] = array(
			array(
				'default' => null,
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryStatus_id',
				'label' => 'Статус реестра',
				'rules' => 'required',
				'type' => 'id'
			)
		);

		$this->inputRules['checkRegistryImportAvailable'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => 'required',
				'type' => 'id'
			)
		);

		$this->inputRules['loadUnionRegistryData'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuBuilding_id',
				'label' => 'Подразделение',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonal_id',
				'label' => 'Врач',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_id',
				'label' => 'Отделение',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSectionProfile_id',
				'label' => 'Профиль',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Человек',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'NumCard',
				'label' => 'Номер документа',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Evn_id',
				'label' => 'ИД случая',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryType_id',
				'label' => 'Тип реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_SurName',
				'label' => 'Фамилия',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Person_FirName',
				'label' => 'Имя',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Person_SecName',
				'label' => 'Отчество',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Polis_Num',
				'label' => 'Полис',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'default' => 0,
				'field' => 'start',
				'label' => 'Начальный номер записи',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 100,
				'field' => 'limit',
				'label' => 'Количество возвращаемых записей',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'RegistryStatus_id',
				'label' => 'Статус реестра',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'filterRecords',
				'label' => 'Фильтр записей (1 - все, 2 - оплаченые, 3 - не оплаченые)',
				'rules' => '',
				'default' => 1,
				'type' => 'int'
			)
		);
	}

	/**
	 * Экспорт реестра по иногородним
	 */
	public function exportRegistryInog() {
		set_time_limit(0); // обязательно, иначе на больших объемах выгружаемых данных до конца не выполнится

		$data = $this->ProcessInputData('exportRegistryInog', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->exportRegistryInog($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
	}
	/**
	 * Импорт реестра по иногородним
	 */
	public function importRegistryInog() {

		$upload_path = './'.IMPORTPATH_ROOT.'importRegistryFromXml/'.$_SESSION['lpu_id'].'/';
		$allowed_types = explode('|','zip|rar|dbf');

		set_time_limit(0); // обязательно, иначе на больших объемах выгружаемых данных до конца не выполнится

		$this->load->library('textlog', array('file'=>'importRegistryInog_'.date( 'Y-m-d', time()).'.log'));
		$this->textlog->add('');
		$this->textlog->add('importRegistryInog: Запуск');

		$data = getSessionParams();

		if (!isset($_FILES['ImportFile'])) {
			$this->textlog->add('importRegistryInog: Не выбран файл реестра!');
			$this->ReturnData( array('success' => false, 'Error_Code' => 100011 , 'Error_Msg' => toUTF('Не выбран файл реестра!') ) ) ;
			return false;
		}

		if (!is_uploaded_file($_FILES['ImportFile']['tmp_name']))
		{
			$error = (!isset($_FILES['ImportFile']['error'])) ? 4 : $_FILES['ImportFile']['error'];
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
			$this->textlog->add('importRegistryInog: ' . $message);
			$this->ReturnData(array('success' => false, 'Error_Code' => 100012 , 'Error_Msg' => toUTF($message)));
			return false;
		}

		// Тип файла разрешен к загрузке?
		$x = explode('.', $_FILES['ImportFile']['name']);
		$file_data['file_ext'] = end($x);
		if (!in_array(strtolower($file_data['file_ext']), $allowed_types)) {
			$this->textlog->add('importRegistryInog: Данный тип файла не разрешен.');
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
			$this->textlog->add('importRegistryInog: Путь для загрузки файлов некорректен.');
			$this->ReturnData(array('success' => false, 'Error_Code' => 100014 , 'Error_Msg' => toUTF('Путь для загрузки файлов некорректен.')));
			return false;
		}

		// Имеет ли директория для загрузки права на запись?
		if (!is_writable($upload_path)) {
			$this->textlog->add('importRegistryInog: Загрузка файла не возможна из-за прав пользователя.');
			$this->ReturnData( array('success' => false, 'Error_Code' => 100015 , 'Error_Msg' => toUTF('Загрузка файла не возможна из-за прав пользователя.')));
			return false;
		}

		if (strtolower($file_data['file_ext'] == 'dbf')) {
			$dbffile = $_FILES['ImportFile']['name'];
			if (!move_uploaded_file($_FILES["ImportFile"]["tmp_name"], $upload_path.$dbffile)){
				$this->ReturnData( array('success' => false, 'Error_Code' => 100015 , 'Error_Msg' => toUTF('Не удаётся переместить файл.')));
				return false;
			}
		} else {
			// там должен быть файл .dbf, если его нет -> файл не является ответом сегмента
			$zip = new ZipArchive;
			if ($zip->open($_FILES["ImportFile"]["tmp_name"]) === TRUE) {
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
			unlink($_FILES["ImportFile"]["tmp_name"]);
		}

		if (empty($dbffile))
		{
			$this->textlog->add('importRegistryInog: Файл не является архивом ответа.');
			$this->ReturnData( array('success' => false, 'Error_Code' => 100015 , 'Error_Msg' => toUTF('Файл не является архивом ответа.')));
			return false;
		}

		$handler = dbase_open($upload_path.$dbffile, 0);
		if (!$handler) {
			$this->textlog->add('importRegistryInog: Не удается открыть dbf-файл.');
			return array('success' => false, 'Error_Msg' => 'Не удается открыть dbf-файл!');
		}
		$addRecs = 0;
		$errRecs = 0;
		$record_count = dbase_numrecords($handler);
		for($i=1; $i<=$record_count; $i++) {
			$record = dbase_get_record_with_names($handler, $i);
			array_walk($record, 'ConvertFromWin866ToUtf8');
			$record = array_map('trim',$record);

			if(empty($record['EERP'])){

				$OrgSMO_id = $this->dbmodel->getFirstResultFromQuery("
					select top 1
						OS.OrgSMO_id as OrgSmo_id
					from v_OrgSMO OS with (nolock)
					where Orgsmo_f002smocod = :orgSmoCode
				", array('orgSmoCode' => $record['RCOD']));

				$Server_id = $this->dbmodel->getFirstResultFromQuery("
					select top 1 Server_id
					from v_PersonState with (nolock)
					where Person_id = :Person_id
				", array('Person_id' => $record['RECID']));

				$OmsSprTerr_id = $this->dbmodel->getFirstResultFromQuery("
					select top 1 OMSSprTerr_id
					from v_OmsSprTerr with (nolock)
					where OMSSprTerr_OKATO = :OMSSprTerr_OKATO
				", array('OMSSprTerr_OKATO' => intval($record['ROKATO'])));

				$params = array(
					'Person_id' => $record['RECID'],
					'PolisType_id' => $record['ROPDOC'] ? $record['ROPDOC'] : 4,
					'Polis_Ser' => $record['RSPOL'] ? $record['RSPOL'] : null,
					'Polis_Num' => $record['RNPOL'] ? $record['RNPOL'] : null,
					'PersonPolisEdNum_EdNum' => $record['RENP'],
					'Polis_begDate' => $record['RDBEG'] ? DateTime::createFromFormat('Ymd', $record['RDBEG']) : null,
					'Polis_endDate' => $record['RDEND'] ? DateTime::createFromFormat('Ymd', $record['RDEND']) : null,
					'Server_id' => $Server_id ? $Server_id : $data['Server_id'],
					'OrgSMO_id' => $OrgSMO_id,
					'OmsSprTerr_id' => $OmsSprTerr_id ? $OmsSprTerr_id : null,
					'pmUser_id' => $data['pmUser_id']
				);

				$query = "
						declare @ErrCode int,
						@PersonPolis_id bigint,
						@ErrMsg varchar(400);

					exec p_PersonPolis_ins
						@PersonPolis_id = @PersonPolis_id output,
						@Server_id = :Server_id,
						@Person_id = :Person_id,
						@OmsSprTerr_id = :OmsSprTerr_id,
						@PolisType_id = :PolisType_id,
						@OrgSMO_id = :OrgSMO_id,
						@Polis_Ser = :Polis_Ser,
						@Polis_Num = :Polis_Num,
						@Polis_begDate = :Polis_begDate,
						@Polis_endDate = :Polis_endDate,
						@PersonPolis_insDT = :Polis_begDate,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMsg output;

					select @PersonPolis_id as PersonPolis_id";

				$result = $this->db->query($query, $params);
				$resp = $result->result('array');


				// если вставили открытый полис, то все остальные открытые закрываем датой открытия нового минус один день
				if (!empty($resp[0]['PersonPolis_id']) && empty($record['RDEND'])) {
					$query = "
						update
							p with (rowlock)
						set
							p.Polis_endDate = :Polis_endDate
						from
							Polis p
							inner join v_PersonPolis pp (nolock) on pp.Polis_id = p.Polis_id
						where
							pp.Person_id = :Person_id and pp.PersonPolis_id <> :PersonPolis_id and p.Polis_endDate is null
				";

					$this->db->query($query, array(
						'PersonPolis_id' => $resp[0]['PersonPolis_id'],
						'Person_id' => $params['Person_id'],
						'Polis_endDate' => date('Y-m-d', (strtotime($params['Polis_endDate']) - 60*60*24))
					));
				}

				if(!empty($record['RENP'])){
					$query = "
							declare @ErrCode int,
								@PersonPolisEdNum_id bigint,
								@ErrMsg varchar(400);

							exec p_PersonPolisEdNum_ins
								@PersonPolisEdNum_id = @PersonPolisEdNum_id output,
								@Server_id = :Server_id,
								@Person_id = :Person_id,
								@PersonPolisEdNum_EdNum = :PersonPolisEdNum_EdNum,
								@PersonPolisEdNum_begDT = :Polis_begDate,
								@PersonPolisEdNum_insDT = :Polis_begDate,
								@pmUser_id = :pmUser_id,
								@Error_Code = @ErrCode output,
								@Error_Message = @ErrMsg output;

							select @PersonPolisEdNum_id as PersonPolisEdNum_id;
						";

					$this->db->query($query, $params);
				}
				$addRecs++;

			}else{
				$this->textlog->add('importRegistryInog: Запись RECID = ' . $record['RECID'] . ' вернулась с ошибкой: ' . $record['EERP'] . ' ' . $record['REPL']);
				$errRecs++;
			}
		}

		$this->textlog->add('importRegistryInog: Обновлено ' . $addRecs . ' записей');

		$this->ReturnData(array('success' => true, 'Message' => 'Импорт успешно завершен', 'addRecs' => $addRecs, 'errRecs' => $errRecs));
		return true;


	}

	/**
	 * Проверка входит ли реестр в объединенный
	 */
	function checkRegistryImportAvailable() {
		$data = $this->ProcessInputData('checkRegistryImportAvailable', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->checkRegistryImportAvailable($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
	}


	/**
	 *	Экспорт реестра для ТФОМС
	 */
	function exportRegistryToXml() {
		set_time_limit(0); // обязательно, иначе на больших объемах выгружаемых данных до конца не выполнится

		$data = $this->ProcessInputData('exportRegistryToXml', true);
		if ( $data === false ) { return false; }

		if ( empty($data['Registry_id']) ) {
			$this->ReturnError('Ошибка. Неверно задан идентификатор счета!');
			return false;
		}

		$this->load->library('textlog', array('file'=>'exportRegistryToXml_' . date('Y_m_d') . '.log'));
		$this->textlog->add('');
		$this->textlog->add('Запуск');

		// Проверяем наличие и состояние реестра
		$this->textlog->add('GetRegistryXmlExport: Проверяем наличие и состояние реестра');
		$res = $this->dbmodel->GetRegistryXmlExport($data);
		$this->textlog->add('GetRegistryXmlExport: Проверка закончена');

		if ( !is_array($res) || count($res) == 0 ) {
			$this->ReturnError('Произошла ошибка при чтении реестра. Сообщите об ошибке разработчикам.');
			return false;
		}

		$data['Registry_endMonth'] = $res[0]['Registry_endMonth'];
		$data['Registry_endYear'] = $res[0]['Registry_endYear'];
		$data['Registry_IsFinanc'] = $res[0]['Registry_IsFinanc'];
		$data['RegistryGroupType_id'] = $res[0]['RegistryGroupType_id'] ?? null;
		$data['Registry_IsZNO'] = $res[0]['Registry_IsZNO'];
		$DispClass_id = $res[0]['DispClass_id'];
		$type = $res[0]['RegistryType_id'];

		$registryIsUnion = ($type == 13);

		// Запрет экспорта и отправки в ТФОМС реестра нуждающегося в переформировании (refs #13648)
		if ( !empty($res[0]['Registry_IsNeedReform']) && $res[0]['Registry_IsNeedReform'] == 2 ) {
			$this->textlog->add('Выход с сообщением: Реестр нуждается в переформировании, отправка и экспорт невозможны. Переформируйте реестр и повторите действие.');
			if ( $registryIsUnion ) {
				$this->ReturnError('Часть реестров нуждается в переформировании, экспорт невозможен.');
			}
			else {
				$this->ReturnError('Реестр нуждается в переформировании, экспорт невозможен.');
			}

			return false;
		}
		
		// Запрет экспорта и отправки в ТФОМС реестра при несовпадении суммы всем случаям (SUMV) итоговой сумме (SUMMAV). (refs #13806)
		if ( !empty($res[0]['Registry_SumDifference']) ) {
			$this->textlog->add('Выход с сообщением: Неверная сумма по счёту и реестрам.');
			// добавляем ошибку
			// $data['RegistryErrorType_Code'] = 3;
			// $res = $this->dbmodel->addRegistryErrorCom($data);
			$this->ReturnError('Экспорт невозможен. Неверная сумма по счёту и реестрам.', '12');
			return false;
		}
		
		// Запрет экспорта и отправки в ТФОМС реестра при 0 записей
		if ( empty($res[0]['RegistryData_Count']) ) {
			$this->textlog->add('Выход с сообщением: Нет записей в реестре.');
			$this->ReturnError('Экспорт невозможен. Нет случаев в реестре.', '13');
			return false;
		}
		
		$this->textlog->add('Получили путь из БД:' . $res[0]['Registry_xmlExportPath']);

		if ( $res[0]['Registry_xmlExportPath'] == '1' ) {
			$this->textlog->add('Выход с сообщением: Реестр уже экспортируется. Пожалуйста, дождитесь окончания экспорта (в среднем 1-10 мин).');
			$this->ReturnError('Реестр уже экспортируется. Пожалуйста, дождитесь окончания экспорта (в среднем 1-10 мин).');
			return false;
		}
		else if ( strlen($res[0]['Registry_xmlExportPath']) > 0 ) { // если уже выгружен реестр
			$this->textlog->add('Реестр уже выгружен');

			if ( empty($data['OverrideExportOneMoreOrUseExist']) ) {
				$this->textlog->add('Выход с вопросом: Файл реестра существует на сервере. Если вы хотите сформировать новый файл выберете (Да), если хотите скачать файл с сервера нажмите (Нет)');
				$this->ReturnError('Файл реестра существует на сервере. Если вы хотите сформировать новый файл выберете (Да), если хотите скачать файл с сервера нажмите (Нет)', 11);
				return false;
			}
			else if ( $data['OverrideExportOneMoreOrUseExist'] == 1 ) {
				if (!empty($data['forSign'])) {
					$this->textlog->add('exportRegistryToXml: Возвращаем пользователю файл в base64');
					$filebase64 = base64_encode(file_get_contents($res[0]['Registry_xmlExportPath']));
					$this->ReturnData(array('success' => true, 'filebase64' => $filebase64));
					return true;
				}
				$link = $res[0]['Registry_xmlExportPath'];
				$usePrevXml = '';

				if ( empty($data['onlyLink']) ) {
					$usePrevXml = 'usePrevXml: true, ';
				}

				echo "{'success':true, $usePrevXml'Link':'$link'}";
				$this->textlog->add('Выход с передачей ссылкой: '.$link);

				return true;
			}
			// Запрет переформирования заблокированного реестра
			// @task https://redmine.swan.perm.ru/issues/74209
			else if ( !empty($res[0]['RegistryCheckStatus_Code']) && $res[0]['RegistryCheckStatus_Code'] == 1 ) {
				$this->textlog->add('Выход с сообщением: Реестр заблокирован, переформирование невозможно.');
				$this->ReturnError('Реестр заблокирован, переформирование невозможно.');
				return false;
			}
		}

		$data['PayType_SysNick'] = null; 

		// Если вернули тип оплаты реестра, то будем его использовать 
		if ( isset($res[0]['PayType_SysNick']) ) {
			$data['PayType_SysNick'] = $res[0]['PayType_SysNick'];
		}

		$this->textlog->add('Тип оплаты реестра: ' . $data['PayType_SysNick']);
		$this->textlog->add('refreshRegistry: Пересчитываем реестр');

		// Удаление помеченных на удаление записей и пересчет реестра 
		if ( $this->refreshRegistry($data) === false ) {
			// выход с ошибкой
			$this->textlog->add('refreshRegistry: При обновлении данных реестра произошла ошибка.');
			$this->ReturnError('При обновлении данных реестра произошла ошибка.');
			return false;
		}

		$this->textlog->add('refreshRegistry: Реестр пересчитали');
		$this->textlog->add('Тип реестра: ' . $type);
		if (in_array($data['PayType_SysNick'], array('bud', 'fbud'))) {
			$exportVersion = '1.0';
		} else {
			$exportVersion = '3.1';
		}

		$this->textlog->add('Версия экспорта: ' . $exportVersion);

		// Формирование XML в зависимости от типа. 
		// В случае возникновения ошибки - необходимо снять статус равный 1 - чтобы при следующей попытке выгрузить Dbf не приходилось обращаться к разработчикам
		try {
			$data['Status'] = '1';
			$this->dbmodel->SetXmlExportStatus($data);
			$this->textlog->add('SetXmlExportStatus: Установили статус реестра в 1');

			// Объединенные реестры могут содержать данные любого типа
			// Получаем список типов реестров, входящих в объединенный реестр
			if ( $registryIsUnion ) {
				$registrytypes = $this->dbmodel->getUnionRegistryTypes($data['Registry_id']);

				if ( !is_array($registrytypes) || count($registrytypes) == 0 ) {
					// выход с ошибкой
					$this->textlog->add('getUnionRegistryTypes: При получении списка типов реестров, входящих в объединенный реестр, произошла ошибка.');
					throw new Exception('При получении списка типов реестров, входящих в объединенный реестр, произошла ошибка.');
				}
			}
			else {
				$registrytypes = array($type);
			}

			if (!empty($res[0]['KatNasel_SysNick']) && in_array($res[0]['KatNasel_SysNick'], array('inog', 'allinog')) && in_array(2, $registrytypes) && !in_array(1, $registrytypes)) {
				$registrytypes[] = 1;
			}

			$number = 0; // счётчик IDCASE
			$nznumber = 0; // счётчик N_ZAP
			$Registry_EvnNum = array();
			$SCHET = $this->dbmodel->loadRegistrySCHETForXmlUsing($data, $type);

			$altkeys = array(
				'NPR_MO_ZSL' => 'NPR_MO',
				'NPR_DATE_ZSL' => 'NPR_DATE',
				'NPR_N_ZSL' => 'NPR_N',
				'NPR_P_ZSL' => 'NPR_P',
				'LPU_U' => 'LPU',
				'LPU_1_U' => 'LPU_1',
				'PODR_U' => 'PODR',
				'PROFIL_U' => 'PROFIL',
				'DET_U' => 'DET',
				'DS_U' => 'DS',
				'PRVS_U' => 'PRVS',
				'P_OTK_U' => 'P_OTK',
				'DS2_SL' => 'DS2',
				'PRN_MO_ZSL' => 'PRN_MO',
				'NPL_USL' => 'NPL',
				'TARIF_U' => 'TARIF',
				'Z_SL_KOEF' => 'Z_SL',
			);
			$registry_data_res = array('ZAP'=>array(), 'PACIENT'=>array());
			foreach($registrytypes as $typeq) {
				$registry_data_res2 = $this->dbmodel->loadRegistryDataForXmlUsing($typeq, $data, $number, $nznumber, $Registry_EvnNum, $registryIsUnion);

				if ( $registry_data_res2 === false ) {
					$data['Status'] = '';
					$this->dbmodel->SetXmlExportStatus($data);
					$this->textlog->add('Ошибка при выгрузке данных');
					$this->ReturnError('Ошибка при выгрузке данных');
					return false;
				}

				$registry_data_res['ZAP'] = array_merge($registry_data_res['ZAP'], $registry_data_res2['ZAP']);
				$registry_data_res['PACIENT'] = $registry_data_res['PACIENT'] + $registry_data_res2['PACIENT'];
			}

			$this->textlog->add('loadRegistryDataForXmlUsingCommon: Выбрали данные');

			if ( $registry_data_res === false ) {
				$data['Status'] = '';
				$this->dbmodel->SetXmlExportStatus($data);
				$this->textlog->add('Выход с ошибкой дедлока');
				$this->ReturnError($this->error_deadlock);
				return false;
			}
			else if ( !is_array($registry_data_res) || count($registry_data_res) == 0 ) {
				$data['Status'] = '';
				$this->dbmodel->SetXmlExportStatus($data);
				$this->textlog->add('Выход с ошибкой: Данных по требуемому реестру нет в базе данных.');
				$this->ReturnError('Данных по требуемому реестру нет в базе данных.');
				return false;
			}

			$this->textlog->add('Получили все данные из БД ');
			$this->load->library('parser');

			// каталог в котором лежат выгружаемые файлы
			$out_dir = "re_xml_" . time() . "_" . $data['Registry_id'];
			mkdir(EXPORTPATH_REGISTRY . $out_dir);

			$this->textlog->add('Запуск SetXmlPackNum');
			$packNum = $this->dbmodel->SetXmlPackNum($data);

			if ( empty($packNum) ) {
				$data['Status'] = '';
				$this->dbmodel->SetXmlExportStatus($data);
				$this->textlog->add('Выход с ошибкой: Ошибка при получении номера выгружаемого пакета.');
				$this->ReturnError('Ошибка при получении номера выгружаемого пакета.');
				return false;
			}

			$first_code = '';
			$needNHIST = false;
			$packNum = sprintf('%02d', $packNum);

			$this->textlog->add('packNum = ' . $packNum);

			if (in_array($data['PayType_SysNick'], array('bud', 'fbud'))) {
				switch ($type) {
					case 1: // stac
						$first_code = 'S';
						break;
					case 2: // polka
						$first_code = 'P';
						break;
					case 14: // вмп
						$first_code = 'V';
						break;

					default:
						return false;
						break;
				}
			} else {
				if ($registryIsUnion) {
					switch ($res[0]['RegistryGroupType_id']) {
						case 2: // Оказание высокотехнологичной медицинской помощи
							$first_code = 'T';
							break;
						case 3: // Дисп-ция взр. населения 1-ый этап
							$first_code = 'DP';
							break;
						case 4: // Дисп-ция взр. населения 2-ый этап
							$first_code = 'DV';
							break;
						case 10: // Профилактические осмотры взрослого населения
							$first_code = 'DO';
							break;
						case 21: // СМП
							$first_code = 'S';
							break;
						case 22: // Подушевое финансирование
							$first_code = $data['Registry_IsZNO'] == 2 ? 'PC' : 'P';
							break;
						case 23: // Неподушевое финансирование
							$first_code = $data['Registry_IsZNO'] == 2 ? 'C' : 'H';
							break;
						case 24: // Все
							$first_code = 'X';
							break;
						case 27: // Дисп-ция детей-сирот стационарных 1-ый этап
						case 28: // Дисп-ция детей-сирот стационарных 2-ой этап
							$first_code = 'DS';
							break;
						case 29: // Дисп-ция детей-сирот усыновленных 1-ый этап
						case 30: // Дисп-ция детей-сирот усыновленных 2-ой этап
							$first_code = 'DU';
							break;
						case 31: // Профилактические осмотры несовершеннолетних 1-ый этап
						case 32: // Профилактические осмотры несовершеннолетних 2-ой этап
							$first_code = 'DF';
							break;
						case 34: // Взаиморасчёты
						case 35: // Неподушевое финансирование и взаиморасчёты 
							$first_code = $data['Registry_IsZNO'] == 2 ? 'C' : 'H';
							break;
						case 15: // Параклинические услуги
							$first_code = 'H';
							$needNHIST = true;
							break;
						default:
							$data['Status'] = '';
							$this->dbmodel->SetXmlExportStatus($data);
							$this->textlog->add('Выход с ошибкой: Ошибка при определении параметра, означающего передаваемые данные, в имени файла.');
							$this->ReturnError('Ошибка при определении параметра, означающего передаваемые данные, в имени файла.');
							break;
					}
				} else {
					switch ($type) {
						case 1: // stac
						case 2: // polka
							if ($data['Registry_IsFinanc'] == 2) {
								$first_code = $data['Registry_IsZNO'] == 2 ? 'PC' : 'P';
							} else {
								$first_code = $data['Registry_IsZNO'] == 2 ? 'C' : 'H';
							}
							break;

						case 6: // smp
							$first_code = 'S';
							break;

						case 7: // двн
							$first_code = $DispClass_id == 1 ? 'DP' : 'DV';
							break;

						case 9: // ддс
							if (in_array($DispClass_id, array(3, 4))) {
								$first_code = 'DS';
							} else if (in_array($DispClass_id, array(7, 8))) {
								$first_code = 'DU';
							}
							break;

						case 11: // повн
							$first_code = 'DO';
							break;

						case 12: // мон
							if ($exportVersion == '3.1') {
								$first_code = 'DF';
							} else {
								if (in_array($DispClass_id, array(10, 12))) {
									$first_code = 'DF';
								} else if (in_array($DispClass_id, array(9, 11))) {
									$first_code = 'DD';
								} else if ($DispClass_id == 6) {
									$first_code = 'DR';
								}
							}
							break;

						case 14: // htm
							$first_code = 'T';
							break;

						case 15: // параклиника
							$first_code = 'H';
							$needNHIST = true;
							break;

						case 20: //Взаиморасчёты
							$first_code = $data['Registry_IsZNO'] == 2 ? 'C' : 'H';
							break;

						default:
							return false;
							break;
					}
				}
			}

			$p_code = 'T';
			if (in_array($data['PayType_SysNick'], array('bud', 'fbud'))) {
				$p_code = 'Z';
			}
			if (in_array($data['PayType_SysNick'], array('bud', 'fbud'))) {
				$f_type = 'F'; // федеральный
				if ($data['PayType_SysNick'] == 'bud') {
					$f_type = 'L'; // местный
				}
				$zname = $f_type . "_" . $first_code . "_HM" . $SCHET[0]['CODE_MO'] . $p_code . $data['session']['region']['number'] . "_" . $data['Registry_endMonth'] . $packNum;
			} else {
				$zname = $first_code . "M" . $SCHET[0]['CODE_MO'] . $p_code . $data['session']['region']['number'] . "_" . $data['Registry_endMonth'] . $packNum;
			}

			$rname = "HM" . $SCHET[0]['CODE_MO'] . $p_code . $data['session']['region']['number'] . "_" . $data['Registry_endMonth'] . $packNum;
			$pname = "LM" . $SCHET[0]['CODE_MO'] . $p_code . $data['session']['region']['number'] . "_" . $data['Registry_endMonth'] . $packNum;

			$file_re_data_sign = $rname;
			$file_re_pers_data_sign = $pname;

			// файл-тело реестра
			$file_re_data_name = EXPORTPATH_REGISTRY . $out_dir . "/" . $file_re_data_sign . ".xml";

			// файл-перс. данные
			$file_re_pers_data_name = EXPORTPATH_REGISTRY . $out_dir . "/" . $file_re_pers_data_sign . ".xml";

			if (in_array($data['PayType_SysNick'], array('bud', 'fbud'))) {
				$templ_header = 'registry_astra_pl_header_bud';
				$templ_body = 'registry_astra_pl_body_bud';
				$templ_footer = 'registry_astra_pl_footer';
			}
			else {
				$templ_header = 'registry_astra_pl_header';
				$templ_body = 'registry_astra_pl_body_2019';
				$templ_footer = 'registry_astra_pl_footer';
			}

			$SCHET[0]['VERSION'] = $exportVersion;
			$SCHET[0]['FILENAME'] = $file_re_data_sign;
			$SCHET[0]['SD_Z'] = count($registry_data_res['ZAP']);
			$ZGLV = array();
			$ZGLV[0]['FILENAME'] = $file_re_pers_data_sign;
			$ZGLV[0]['FILENAME1'] = $file_re_data_sign;

			if ( in_array($data['PayType_SysNick'], array('bud', 'fbud')) ) {
				$ZGLV[0]['VERSION'] =  '1.0';
			}
			else {
				$ZGLV[0]['VERSION'] =  '3.1';
			}

			$templ_person_header = "registry_astra_person_header";
			$templ_person_body = "registry_astra_person_body";
			$templ_person_footer = "registry_astra_person_footer";

			$this->textlog->add('Пишем заголовки для файлов: ' . $file_re_data_name . ', ' . $file_re_pers_data_name);
			
			// Заголовок для файла со случаями
			$xml = "<?xml version=\"1.0\" encoding=\"windows-1251\" standalone=\"yes\"?>\r\n" . $this->parser->parse_ext('export_xml/' . $templ_header, $SCHET[0], true);
			$xml = str_replace('&', '&amp;', $xml);
			file_put_contents($file_re_data_name, $xml);

			// Заголовок для файла с пациентами
			$xml_pers = "<?xml version=\"1.0\" encoding=\"windows-1251\" standalone=\"yes\"?>\r\n" . $this->parser->parse_ext('export_xml/' . $templ_person_header, $ZGLV[0], true);
			$xml_pers = str_replace('&', '&amp;', $xml_pers);
			file_put_contents($file_re_pers_data_name, $xml_pers);

			$this->textlog->add('Формируем тело файла ' . $file_re_data_name);

			// Пишем в файл со случаями по 1000 записей
			$registryData = array();
			$i = 0;

			$outputEmptyTags = false;
			if ($needNHIST) {
				// для параклинических услуг выгружаем тег NHISTORY, даже если он пустой
				$outputEmptyTags = array('NHISTORY');
			}

			foreach ( $registry_data_res['ZAP'] as $key => $array ) {
				$i++;
				$registryData[] = $array;
				unset($registry_data_res['ZAP'][$key]);

				if ( count($registryData) == 1000 ) {
					$this->textlog->add('Сформировали 1000 записей');
					$xml = $this->parser->parse_ext('export_xml/' . $templ_body, array('ZAP' => $registryData), true, false, $altkeys, $outputEmptyTags);
					$this->textlog->add('Распарсили');
					$xml = str_replace('&', '&amp;', $xml);
					$xml = preg_replace("/\R\s*\R/", "\r\n", $xml);
					file_put_contents($file_re_data_name, $xml, FILE_APPEND);
					unset($xml);
					$registryData = array();
					$this->textlog->add('Записали в файл 1000 записей');
				}
			}

			if ( count($registryData) > 0 ) {
				$this->textlog->add('Осталось записей: ' . count($registryData));
				$xml = $this->parser->parse_ext('export_xml/' . $templ_body, array('ZAP' => $registryData), true, false, $altkeys, $outputEmptyTags);
				$this->textlog->add('Распарсили');
				$xml = str_replace('&', '&amp;', $xml);
				$xml = preg_replace("/\R\s*\R/", "\r\n", $xml);
				file_put_contents($file_re_data_name, $xml, FILE_APPEND);
				unset($xml);
				$this->textlog->add('Записали в файл ' . count($registryData) . ' записей');
			}

			unset($registryData);

			// Конец для файла со случаями
			$xml = $this->parser->parse_ext('export_xml/' . $templ_footer, array(), true);
			$xml = str_replace('&', '&amp;', $xml);
			file_put_contents($file_re_data_name, $xml, FILE_APPEND);

			$this->textlog->add('Формируем тело файла ' . $file_re_pers_data_name);

			// Пишем в файл с пациентами по 1000 записей
			$personData = array();
			$i = 0;

			foreach ( $registry_data_res['PACIENT'] as $key => $array ) {
				$i++;
				$personData[] = $array;
				unset($registry_data_res['PACIENT'][$key]);

				if ( count($personData) == 1000 ) {
					$this->textlog->add('Сформировали 1000 записей');
					$xml_pers = $this->parser->parse_ext('export_xml/' . $templ_person_body, array('PACIENT' => $personData), true);
					$this->textlog->add('Распарсили');
					$xml_pers = str_replace('&', '&amp;', $xml_pers);
					$xml_pers = preg_replace("/\R\s*\R/", "\r\n", $xml_pers);
					file_put_contents($file_re_pers_data_name, $xml_pers, FILE_APPEND);
					unset($xml_pers);
					$personData = array();
					$this->textlog->add('Записали в файл 1000 записей');
				}
			}

			if ( count($personData) > 0 ) {
				$this->textlog->add('Осталось записей: ' . count($personData));
				$xml_pers = $this->parser->parse_ext('export_xml/' . $templ_person_body, array('PACIENT' => $personData), true);
				$this->textlog->add('Распарсили');
				$xml_pers = str_replace('&', '&amp;', $xml_pers);
				$xml_pers = preg_replace("/\R\s*\R/", "\r\n", $xml_pers);
				file_put_contents($file_re_pers_data_name, $xml_pers, FILE_APPEND);
				unset($xml_pers);
				$this->textlog->add('Записали в файл ' . count($personData) . ' записей');
			}

			unset($personData);

			// Конец для файла с пациентами
			$xml_pers = $this->parser->parse_ext('export_xml/' . $templ_person_footer, array(), true);
			$xml_pers = str_replace('&', '&amp;', $xml_pers);
			file_put_contents($file_re_pers_data_name, $xml_pers, FILE_APPEND);

			unset($registry_data_res);

			$file_zip_sign = $zname;
			$file_zip_name = EXPORTPATH_REGISTRY . $out_dir . "/" . $file_zip_sign . ".mp";
			if (!empty($res[0]['KatNasel_SysNick']) && in_array($res[0]['KatNasel_SysNick'], array('inog', 'allinog'))) {
				$file_zip_name = EXPORTPATH_REGISTRY . $out_dir . "/" . $file_zip_sign . ".mpi";
			}
			$this->textlog->add('Создали XML-файлы: (' . $file_re_data_name . ' и ' . $file_re_pers_data_name . ')');

			$zip = new ZipArchive();
			$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
			$zip->AddFile($file_re_data_name, $file_re_data_sign . ".xml");
			$zip->AddFile($file_re_pers_data_name, $file_re_pers_data_sign . ".xml");
			$zip->close();
			$this->textlog->add('Упаковали в ZIP ' . $file_zip_name);

			if ( false && $registryIsUnion ) { // оказалось, что это не нужно #126256
				// Для автоматизированной обработки все архивные файлы необходимо упаковывать в один архивный пакет формата ZIP, с расширением PAKET – (далее Пакет), имя которого формируется по следующему принципу
				// BNi_YYMMN.PAKET
				$file_union_zip_sign = 'B' . $SCHET[0]['CODE_MO'] . '_' . $data['Registry_endMonth'] . $packNum;
				$file_union_zip_name = EXPORTPATH_REGISTRY . $out_dir . "/" . $file_union_zip_sign . ".PAKET";
				$zip = new ZipArchive();
				$zip->open($file_union_zip_name, ZIPARCHIVE::CREATE);
				$zip->AddFile($file_zip_name, $file_zip_sign . ".mp");
				$zip->close();

				@unlink($file_zip_name);

				$this->textlog->add('Упаковали в ZIP ' . $file_union_zip_name);

				$file_zip_name = $file_union_zip_name;
			}
			
			$PersonData_registryValidate = true;
			$EvnData_registryValidate = true;
			if(array_key_exists('check_implementFLK', $data['session']['setting']['server']) && $data['session']['setting']['server']['check_implementFLK'] && $res[0]){
				$upload_path = 'RgistryFields/';
				// если включена проверка ФЛК в параметрах системы
				// получим xsd шаблон для проверки
				$settingsFLK = $this->dbmodel->loadRegistryEntiesSettings($res[0]);
				if($settingsFLK && count($settingsFLK) > 0){
					//если запись найдена
					$settingsFLK = $settingsFLK[0];
					$tplEvnDataXSD = ($settingsFLK['FLKSettings_EvnData']) ? $settingsFLK['FLKSettings_EvnData'] : false;
					$tplPersonDataXSD = ($settingsFLK['FLKSettings_PersonData']) ? $settingsFLK['FLKSettings_PersonData'] : false;	

					//Проверка со случаями
					if($tplEvnDataXSD){
						//название файла имеет вид ДИРЕКТОРИЯ_ИМЯ.XSD
						$dirTpl = explode('_', $tplEvnDataXSD); $dirTpl = $dirTpl[0];
						//путь к файлу XSD
						$fileEvnDataXSD = IMPORTPATH_ROOT.$upload_path.$dirTpl."/".$tplEvnDataXSD;
						//Файл с ошибками					
						$validateEvnData_err_file = EXPORTPATH_REGISTRY.$out_dir."/err_EvnData_".$dirTpl.'.html';						
						if(file_exists($fileEvnDataXSD)) {
							$EvnData_registryValidate = $this->dbmodel->Reconciliation($file_re_data_name, $fileEvnDataXSD, 'file', $validateEvnData_err_file);
						}
					}
					//Проверка с персональными данными
					if($tplPersonDataXSD){
						//название файла имеет вид ДИРЕКТОРИЯ_ИМЯ.XSD
						$dirTpl = explode('_', $tplPersonDataXSD); $dirTpl = $dirTpl[0];
						//путь к файлу XSD
						$filePersonDataXSD = IMPORTPATH_ROOT.$upload_path.$dirTpl."/".$tplPersonDataXSD;
						//Файл с ошибками					
						$validatePersonData_err_file = EXPORTPATH_REGISTRY.$out_dir."/err_PersonData_".$dirTpl.'.html';
						if(file_exists($filePersonDataXSD)) {
							$PersonData_registryValidate = $this->dbmodel->Reconciliation($file_re_pers_data_name, $filePersonDataXSD, 'file', $validatePersonData_err_file);
						}
					}
				}
			}
			
			if($PersonData_registryValidate) unlink($file_re_data_name);
			if($EvnData_registryValidate) unlink($file_re_pers_data_name);
			if($PersonData_registryValidate || $EvnData_registryValidate) $this->textlog->add('Почистили папку за собой');
			/*
			unlink($file_re_data_name);
			unlink($file_re_pers_data_name);
			$this->textlog->add('Почистили папку за собой');
			*/
			
			if(!$PersonData_registryValidate || !$EvnData_registryValidate){
				$data['Status'] = '';
				$this->dbmodel->SetXmlExportStatus($data);
				$this->ReturnError('<p>Реестр не прошёл проверку ФЛК:</p> <br><a target="_blank" href="'.$validateEvnData_err_file.'">отчет об ошибках в файле со случаями</a>
						<br><a target="_blank" href="'.$file_re_data_name.'">файл со случаями</a>
						<br><a target="_blank" href="'.$validatePersonData_err_file.'">отчет об ошибках в файле с персональными данными</a>
						<br><a target="_blank" href="'.$file_re_pers_data_name.'">файл с персональными данными</a>
						<br><a href="'.$file_zip_name.'" target="_blank">zip архив с файлами реестра</a>');
			}elseif (!$PersonData_registryValidate) {
				$data['Status'] = '';
				$this->dbmodel->SetXmlExportStatus($data);
				$this->ReturnError('<p>Реестр не прошёл проверку ФЛК:</p> <br>
						<br><a target="_blank" href="'.$validatePersonData_err_file.'">отчет об ошибках в файле с персональными данными</a> 
						<br><a target="_blank" href="'.$file_re_pers_data_name.'">файл с персональными данными</a>
						<br><a href="'.$file_zip_name.'" target="_blank">zip архив с файлами реестра</a>');
			} elseif (!$EvnData_registryValidate) {
				$data['Status'] = '';
				$this->dbmodel->SetXmlExportStatus($data);
				$this->ReturnError('<p>Реестр не прошёл проверку ФЛК:</p><br><a target="_blank" href="'.$validateEvnData_err_file.'">отчет об ошибках в файле со случаями</a>
						<br><a target="_blank" href="'.$file_re_data_name.'">файл со случаями</a>
						<br><a href="'.$file_zip_name.'" target="_blank">zip архив с файлами реестра</a>');
			}elseif ( file_exists($file_zip_name) ) {
				$data['Status'] = $file_zip_name;
				$data['Registry_EvnNum'] = json_encode($Registry_EvnNum);
				$this->dbmodel->SetXmlExportStatus($data);

				// Пишем информацию о выгрузке в историю
				$this->dbmodel->dumpRegistryInformation($data, 2);

				if (!empty($data['forSign'])) {
					$this->textlog->add('exportRegistryToXml: Возвращаем пользователю файл в base64');
					$filebase64 = base64_encode(file_get_contents($file_zip_name));
					$this->ReturnData(array('success' => true, 'filebase64' => $filebase64));
					return true;
				} else {
					$this->textlog->add('exportRegistryToXml: Передача ссылки: '.$file_zip_name);
				}
			}
			else{
				$this->textlog->add("Ошибка создания архива реестра!");
				$this->ReturnError('Ошибка создания архива реестра!');
			}

			$this->textlog->add("Финиш");
		}
		catch ( Exception $e ) {
			$data['Status'] = '';
			$this->dbmodel->SetXmlExportStatus($data);
			$this->textlog->add($e->getMessage());
			$this->ReturnError($e->getMessage());
		}

		return true;
	}


	/**
	 * Импорт реестра из СМО
	 */
	function importRegistryFromXml()
	{
		
		$upload_path = './'.IMPORTPATH_ROOT.'importRegistryFromXml/'.$_SESSION['lpu_id'].'/';
		$allowed_types = explode('|','zip|rar|xml');
		
		$data = $this->ProcessInputData('importRegistryFromXml', true);
		if ($data === false) { return false; }

		if (!isset($_FILES['RegistryFile'])) {
			$this->ReturnData( array('success' => false, 'Error_Code' => 100011 , 'Error_Msg' => toUTF('Не выбран файл реестра!') ) ) ;
			return false;
		}
		
		if (!is_uploaded_file($_FILES['RegistryFile']['tmp_name']))
		{
			$error = (!isset($_FILES['RegistryFile']['error'])) ? 4 : $_FILES['RegistryFile']['error'];
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
		$x = explode('.', $_FILES['RegistryFile']['name']);
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

		if ($file_data['file_ext'] == 'xml') {
			$xmlfile = $_FILES['RegistryFile']['name'];
			if (!move_uploaded_file($_FILES["RegistryFile"]["tmp_name"], $upload_path.$xmlfile)){
				$this->ReturnData( array('success' => false, 'Error_Code' => 100015 , 'Error_Msg' => toUTF('Не удаётся переместить файл.')));
				return false;
			}
		} else {
			// там должен быть файл .xml, если его нет -> файл не является архивом реестра
			$zip = new ZipArchive;
			if ($zip->open($_FILES["RegistryFile"]["tmp_name"]) === TRUE) {
				$xmlfile = "";

				for($i=0; $i<$zip->numFiles; $i++){
					$filename = $zip->getNameIndex($i);
					if ( preg_match('/.*.xml/', $filename) > 0 ) {
						$xmlfile = $filename;
					}
				}

				$zip->extractTo( $upload_path );
				$zip->close();
			}
			unlink($_FILES["RegistryFile"]["tmp_name"]);
		}
				
				
		if (empty($xmlfile))
		{
			$this->ReturnData( array('success' => false, 'Error_Code' => 100015 , 'Error_Msg' => toUTF('Файл не является архивом реестра.')));
			return false;
		}
		
		// получаем данные реестра
		$registrydata = $this->dbmodel->loadRegistry($data);		
		if (!is_array($registrydata) || !isset($registrydata[0])) {
			$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Ошибка чтения данных реестра')));
			return false;
		} else {
			$registrydata = $registrydata[0];
		}
		
		// Удаляем ответ по этому реестру, если он уже был загружен
		$this->dbmodel->deleteRegistryErrorTFOMS($data);

		$recall = 0;
		
		libxml_use_internal_errors(true);
		
		$dom = new DOMDocument();
		$res = $dom->load($upload_path.$xmlfile);
		
		foreach (libxml_get_errors() as $error) {
			$this->ReturnData( array('success' => false, 'Error_Code' => 100015 , 'Error_Msg' => toUTF('Файл не является архивом реестра.')));
			return false;
		}
		libxml_clear_errors();
		
		$dom_flkp = $dom->getElementsByTagName('FLK_P');
		foreach($dom_flkp as $dom_oneflkp) {
			/*	номер реестра
			$dom_nschet = $dom_oneschet->getElementsByTagName('NSCHET');
			foreach ($dom_nschet as $dom_onenschet) {
				if ($registrydata['Registry_Num'] != $dom_onenschet->nodeValue) {
					$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Не совпадает номер реестра и импортируемого файла, импорт не произведен')));
					return false;
				}
			}
			
			$dom_dschet = $dom_oneschet->getElementsByTagName('DSCHET');
			foreach ($dom_dschet as $dom_onedschet) {
				if ($registrydata['Registry_accDate'] != date('d.m.Y',strtotime($dom_onedschet->nodeValue))) {
					$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Не совпадает дата реестра и импортируемого файла, импорт не произведен')));
					return false;
				}
			}
			*/
			// идём по ошибкам
			$dom_pr = $dom_oneflkp->getElementsByTagName('PR');
			foreach ($dom_pr as $dom_onepr) {
				$recall++;
				
				$data['N_ZAP'] = 0;
				$data['OSHIB'] = 0;
				$data['IM_POL'] = '';
				$data['BAS_EL'] = '';
				$data['COMMENT'] = '';
				
				// берём ID
				$dom_nzap = $dom_onepr->getElementsByTagName('N_ZAP');
				foreach($dom_nzap as $dom_onenzap) {
					$data['N_ZAP'] = $dom_onenzap->nodeValue;
				}
				$data['N_ZAP'] = trim($data['N_ZAP']);
				
				$evnData = $this->dbmodel->checkErrorDataInRegistry($data);
				if ($evnData === false) {
					$this->dbmodel->deleteRegistryErrorTFOMS($data);
					$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Номер записи "'.$data['N_ZAP'].'" обнаружен в импортируемом файле, но отсутствует в реестре, импорт не произведен')));
					return false;
				}
				
				$dom_oshib = $dom_onepr->getElementsByTagName('OSHIB');
				foreach($dom_oshib as $dom_oneoshib) {
					$data['OSHIB'] = $dom_oneoshib->nodeValue;
				}
				$data['OSHIB'] = trim($data['OSHIB']);
				
				$dom_impol = $dom_onepr->getElementsByTagName('IM_POL');
				foreach($dom_impol as $dom_oneimpol) {
					$data['IM_POL'] = $dom_oneimpol->nodeValue;
				}
				$data['IM_POL'] = trim($data['IM_POL']);
				
				$dom_basel = $dom_onepr->getElementsByTagName('BAS_EL');
				foreach($dom_basel as $dom_onebasel) {
					$data['BAS_EL'] = $dom_onebasel->nodeValue;
				}
				$data['BAS_EL'] = trim($data['BAS_EL']);
				
				$dom_comment = $dom_onepr->getElementsByTagName('COMMENT');
				foreach($dom_comment as $dom_onecomment) {
					$data['COMMENT'] = toAnsi($dom_onecomment->nodeValue);
				}
				$data['COMMENT'] = trim($data['COMMENT']);
				
				$response = $this->dbmodel->setErrorFromImportRegistry($data);
				if (!is_array($response)) 
				{
					$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Ошибка при обработке реестра!')));
					return false;
				} elseif (!empty($response[0]) && !empty($response[0]['Error_Msg'])) {
					$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF($response[0]['Error_Msg'])));
					return false;
				}
			}
		}
		
		if ($recall>0)
		{
			// $rs = $this->dbmodel->setVizitNotInReg($data['Registry_id']);
		}
		
		$params = array();
		$params['RegistryData_isPaid'] = ($recall>0)?1:2;
		$params['Registry_id'] = $data['Registry_id'];
		$this->dbmodel->setRegistryDataIsPaid($params);

		// Пишем информацию об импорте в историю
		$this->dbmodel->dumpRegistryInformation($data, 3);

		$this->ReturnData(array('success' => true, 'Registry_id' => $data['Registry_id'], 'recAll'=>$recall, 'Message' => 'Реестр успешно загружен.'));
		return true;
	}
	
	
	/**
	 * Импорт реестра из ТФОМС
	 */
	function importRegistryFromTFOMS()
	{
		$upload_path = './'.IMPORTPATH_ROOT.'importRegistryFromTFOMS/'.$_SESSION['lpu_id'].'/';
		$allowed_types = explode('|','zip|rar|xml|mp|dbf');

		set_time_limit(0); // обязательно, иначе на больших объемах выгружаемых данных до конца не выполнится

		$this->load->library('textlog', array('file'=>'importRegistryFromTFOMS_'.date( 'Y-m-d', time()).'.log'));
		$this->textlog->add('');
		$this->textlog->add('importRegistryFromTFOMS: Запуск');

		$data = $this->ProcessInputData('importRegistryFromTFOMS', true);
		if ($data === false) { return false; }

		$resp_check = $this->dbmodel->checkRegistryImportAvailable(array(
			'Registry_id' => $data['Registry_id']
		));
		if (!empty($resp_check['Error_Msg'])) {
			$this->textlog->add('importRegistryFromTFOMS: '.$resp_check['Error_Msg']);
			$this->ReturnError($resp_check['Error_Msg'], __LINE__);
			return false;
		}

		if (!isset($_FILES['RegistryFile'])) {
			$this->textlog->add('importRegistryFromTFOMS: Не выбран файл реестра! Выходим.');
			$this->ReturnError('Не выбран файл реестра!', __LINE__) ;
			return false;
		}

		$this->textlog->add('importRegistryFromTFOMS: Проводим базовые проверки.');
		if (!is_uploaded_file($_FILES['RegistryFile']['tmp_name']))
		{
			$error = (!isset($_FILES['RegistryFile']['error'])) ? 4 : $_FILES['RegistryFile']['error'];
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
			$this->textlog->add('importRegistryFromTFOMS: '.toUTF($message).' Выходим.');
			$this->ReturnError($message, __LINE__);
			return false;
		}
		
		// Тип файла разрешен к загрузке?
		$x = explode('.', $_FILES['RegistryFile']['name']);
		$file_data['file_ext'] = end($x);
		if (!in_array(strtolower($file_data['file_ext']), $allowed_types)) {
			$this->textlog->add('importRegistryFromTFOMS: Данный тип файла не разрешен. Выходим.');
			$this->ReturnError('Данный тип файла не разрешен.', __LINE__);
			return false;
		}

		// Правильно ли указана директория для загрузки?
		$path = '';
		$folders = explode('/', $upload_path);
		for($i=0; $i<count($folders); $i++) {
			$path .= $folders[$i].'/';
			if (!@is_dir($path)) {
				mkdir( $path );
			}
		}
		if (!@is_dir($upload_path)) {
			$this->textlog->add('importRegistryFromTFOMS: Путь для загрузки файлов некорректен. Выходим.');
			$this->ReturnError('Путь для загрузки файлов некорректен.', __LINE__);
			return false;
		}
		
		// Имеет ли директория для загрузки права на запись?
		if (!is_writable($upload_path)) {
			$this->textlog->add('importRegistryFromTFOMS: Загрузка файла не возможна из-за прав пользователя. Выходим.');
			$this->ReturnError('Загрузка файла не возможна из-за прав пользователя.', __LINE__);
			return false;
		}

		$this->textlog->add('importRegistryFromTFOMS: Перемещаем файл реестра.');
		if (in_array(strtolower($file_data['file_ext']), array('xml','dbf'))) {
			$xmlfile = $_FILES['RegistryFile']['name'];
			$this->textlog->add('importRegistryFromTFOMS: директория - '.$upload_path.$xmlfile.'.');
			if (!move_uploaded_file($_FILES["RegistryFile"]["tmp_name"], $upload_path.$xmlfile)){
				$this->textlog->add('importRegistryFromTFOMS: Не удаётся переместить файл. Выходим.');
				$this->ReturnError('Не удаётся переместить файл.', __LINE__);
				return false;
			}
		} else {
			// там должен быть файл .xml, если его нет -> файл не является архивом реестра
			$zip = new ZipArchive();
			if ($zip->open($_FILES["RegistryFile"]["tmp_name"]) === TRUE) {
				$this->textlog->add('importRegistryFromTFOMS: Распаковываем архив, перемещаем в директрорию '.$upload_path.'.');
				$xmlfile = "";

				for($i=0; $i<$zip->numFiles; $i++){
					$filename = $zip->getNameIndex($i);
					if ( preg_match('/zm.*\.dbf|hm.*\.xml/', strtolower($filename)) > 0 ) {
						$xmlfile = $filename;
					}
				}

				$zip->extractTo( $upload_path );
				$zip->close();
			}
			unlink($_FILES["RegistryFile"]["tmp_name"]);
		}


		if (empty($xmlfile)) {
			$this->textlog->add('importRegistryFromTFOMS: Файл не является архивом реестра. Выходим.');
			$this->ReturnError('Файл не является архивом реестра.', __LINE__);
			return false;
		}

		if (strtolower($file_data['file_ext']) == 'dbf') {
			//Импорт из dbf
			$this->textlog->add('importRegistryFromTFOMS: Импорт из dbf.');
			$response = $this->importRegistryFromDBF($data, $upload_path.$xmlfile);
			$this->ReturnData($response);
			return true;
		}

		$s00000 = false;
		if ( preg_match('/hm.*s00000.*\.xml/', strtolower($xmlfile)) > 0 ) {
			$s00000 = true;
		}

		// получаем данные реестра
		$this->textlog->add('importRegistryFromTFOMS: Получаем данные реестра');
		$registrydata = $this->dbmodel->loadRegistry($data);
		if (!is_array($registrydata) || !isset($registrydata[0])) {
			$this->textlog->add('importRegistryFromTFOMS: Ошибка чтения данных реестра. Выходим.');
			$this->ReturnError('Ошибка чтения данных реестра', __LINE__);
			return false;
		} else {
			$registrydata = $registrydata[0];
		}

		$data['RegistryType_id'] = $registrydata['RegistryType_id'];
		$registryIsUnion = ($registrydata['RegistryType_id'] == 13);

		// Удаляем ответ по этому реестру, если он уже был загружен
		// $this->dbmodel->deleteRegistryErrorTFOMS($data);

		$recerr = 0;
		$recall = 0;

		libxml_use_internal_errors(true);

		$xml = new SimpleXMLElement(file_get_contents($upload_path . $xmlfile));

		foreach (libxml_get_errors() as $error) {
			$this->textlog->add('importRegistryFromTFOMS: Файл не является архивом реестра. Выходим.');
			$this->ReturnError('Файл не является архивом реестра.', __LINE__);
			return false;
		}

		libxml_clear_errors();

		// проверка соответствия файла реестру
		$this->textlog->add('importRegistryFromTFOMS: Проверяем соответствия файла реестру.');

		if ( !property_exists($xml, 'SCHET') || !property_exists($xml, 'ZAP') ) {
			$this->textlog->add('importRegistryFromTFOMS: Файл не является архивом реестра. Выходим.');
			$this->ReturnError('Файл не является архивом реестра.', __LINE__);
			return false;
		}

		$DSCHET = $xml->SCHET->DSCHET->__toString();
		$NSCHET = $xml->SCHET->NSCHET->__toString();

		if ( !in_array($NSCHET, array($registrydata['Registry_Num'], $registrydata['Registry_Num'] . '/7', $registrydata['Registry_Num'] . '/15'))) {
			$this->textlog->add('importRegistryFromTFOMS: Не совпадает номер реестра (' . $registrydata['Registry_Num'] . ') и импортируемого файла (' . $NSCHET . '), импорт не произведен. Выходим.');
			$this->ReturnError('Не совпадает номер реестра (' . $registrydata['Registry_Num'] . ') и импортируемого файла (' . $NSCHET . '), импорт не произведен.', __LINE__);
			return false;
		}

		if ( $registrydata['Registry_accDate'] != date('d.m.Y', strtotime($DSCHET)) ) {
			$this->textlog->add('importRegistryFromTFOMS: Не совпадает дата реестра и импортируемого файла, импорт не произведен.');
			$this->ReturnError('Не совпадает дата реестра и импортируемого файла, импорт не произведен', __LINE__);
			return false;
		}

		$resp = $this->dbmodel->getFirstResultFromQuery("
			select top 1 Registry_EvnNum from {$this->scheme}.v_Registry (nolock) where Registry_id = :Registry_id
		", array(
			'Registry_id' => $data['Registry_id']
		));

		if ( empty($resp) ) {
			$this->textlog->add('importRegistryFromTFOMS: Ошибка при получении данных о связи реестра и случаев, импорт не произведен.');
			$this->ReturnError('Ошибка при получении данных о связи реестра и случаев, импорт не произведен', __LINE__);
			return false;
		}

		$data['Registry_EvnNum'] = json_decode($resp, true);

		$this->dbmodel->setRegistryEvnNumByNZAP($data);

		// идём по случаям
		$this->textlog->add('importRegistryFromTFOMS: Идём по случаям реестра.');

		$params = array();
		$params['pmUser_id'] = $data['pmUser_id'];

		foreach ( $xml->ZAP as $onezap ) {
			$N_ZAP = $onezap->N_ZAP->__toString();

			$N_ZAP_data = $this->dbmodel->getRegistryEvnNumByNZAP($N_ZAP);

			if ( !is_array($N_ZAP_data) ) {
				continue;
			}

			foreach ( $onezap->Z_SL as $onezsl ) {
				$addedErrors = 0;

				$recall++;
				$this->textlog->add('importRegistryFromTFOMS: Обрабатываем случай ' . $recall . '.');

				if ( !property_exists($onezsl, 'SANK') && $s00000 === false ) {
					continue;
				}

				foreach ( $N_ZAP_data as $N_ZAP_row ) {
					$check = $this->dbmodel->checkErrorDataFromTFOMSInRegistryTest(array(
						'RegistryType_id' => $data['RegistryType_id'],
						'Registry_pid' => $data['Registry_id'],
						'Registry_id' => $N_ZAP_row['Registry_id'],
						'SL_ID' => $N_ZAP_row['SL_ID'],
					));

					if ( empty($check) ) {
						$this->textlog->add('importRegistryFromTFOMS: Запись SL_ID = "' . $N_ZAP_row['SL_ID'] . '" отсутствует в реестре, импорт не произведен');
						$this->ReturnError('Запись SL_ID = "' . $N_ZAP_row['SL_ID'] . '" отсутствует в реестре, импорт не произведен', __LINE__);
						return false;
					}

					$d = array();

					$d['COMMENT_CALC'] = null;
					$d['Evn_id'] = $check['Evn_id'];
					$d['Registry_id'] = $check['Registry_id'];
					$d['RegistryType_id'] = $check['RegistryType_id'];

					if ( $s00000 === true ) {
						// При загрузке данных из файла с именем “…S00000…”(пример имени файла HM300050S00000_160502) кроме ошибок указанных в файле, дополнительно надо для всех записей добавлять ошибку “10090 Неидентифицирован ТФОМС”.
						$response = $this->dbmodel->setErrorFromTFOMSImportRegistry(array(
							'Evn_id' => $d['Evn_id'],
							'Registry_id' => $d['Registry_id'],
							'RegistryType_id' => $d['RegistryType_id'],
							'S_DOP' => '10090',
						), $data);

						if ( !is_array($response) ) {
							$this->textlog->add('importRegistryFromTFOMS: Ошибка при обработке реестра!');
							$this->ReturnError('Ошибка при обработке реестра!', __LINE__);
							return false;
						}
						else if ( !empty($response[0]) && !empty($response[0]['Error_Msg']) ) {
							$this->textlog->add('importRegistryFromTFOMS: ' . $response[0]['Error_Msg']);
							$this->ReturnError($response[0]['Error_Msg'], __LINE__);
							return false;
						}

						$addedErrors++;
					}

					// идём по ошибкам на уровне законеченного случая
					foreach ( $onezsl->SANK as $onesank ) {
						$SL_ID_ARRAY = array();

						if ( property_exists($onesank, 'SL') ) {
							foreach ( $onesank->SL as $onesanksl ) {
								$SL_ID_ARRAY[] = $onesanksl->__toString();
							}
						}

						$S_COM = $onesank->S_COM->__toString();
						$S_DOP = trim($onesank->S_DOP->__toString());

						if ( empty($S_DOP) || (count($SL_ID_ARRAY) > 0 && !in_array($N_ZAP_row['Evn_id'], $SL_ID_ARRAY)) ) {
							continue;
						}

						// Код ошибки из четырех цифр. Пр.: Если в xml 2х значный код 71, то будет строка 0071
						$S_DOP = sprintf("%04d", trim($S_DOP));

						$d['COMMENT'] = !empty($S_COM) ? trim($S_COM) : null;
						$d['S_DOP'] = !empty($S_DOP) ? $S_DOP : null;
						$d['RegistryErrorType_Code'] = $S_DOP;

						$error_exists = $this->dbmodel->existsErrorTypeInRegistry($d);

						if ( !empty($error_exists['Error_Msg']) ) {
							$this->textlog->add('importRegistryFromTFOMS: ' . $error_exists['Error_Msg']);
							$this->ReturnError($error_exists['Error_Msg'], __LINE__);
							return false;
						}

						// Если ошибки указанного типа нет в реестре, то добавляем
						if ( $error_exists === false ) {
							$response = $this->dbmodel->setErrorFromTFOMSImportRegistry($d, $data);

							if ( !is_array($response) ) {
								$this->textlog->add('importRegistryFromTFOMS: Ошибка при обработке реестра!');
								$this->ReturnError('Ошибка при обработке реестра!', __LINE__);
								return false;
							}
							else if ( !empty($response[0]) && !empty($response[0]['Error_Msg']) ) {
								$this->textlog->add('importRegistryFromTFOMS: ' . $response[0]['Error_Msg']);
								$this->ReturnError($response[0]['Error_Msg'], __LINE__);
								return false;
							}

							$addedErrors++;
						}
						else {
							$this->textlog->add('importRegistryFromTFOMS: Ошибка уже есть');
						}
					}
				}

				if ( $addedErrors > 0 ) {
					$recerr++; // записей с ошибками
				}
			}
		}

		if ( $recall > 0 ) {
			$this->dbmodel->afterImportRegistryFromTFOMS(array(
				'Registry_id' => $data['Registry_id'],
				'pmUser_id' => $data['pmUser_id']
			));
		}

		// Пишем информацию об импорте в историю
		$this->dbmodel->dumpRegistryInformation($data, 3);

		$this->textlog->add('importRegistryFromTFOMS: Обнаружено ' . $recerr . ' записей с ошибками.');
		$this->textlog->add('importRegistryFromTFOMS: Реестр успешно загружен.');
		$this->ReturnData(array('success' => true, 'Registry_id' => $data['Registry_id'], 'recAll' => $recall, 'recErr' => $recerr, 'Message' => 'Реестр успешно загружен.'));
		return true;
	}

	/**
	 * Импорт реестра из ТФОМС в формате dbf
	 */
	function importRegistryFromDBF($data, $filepath) {
		$recall = 0;
		$recerr = 0;

		// получаем данные реестра
		$registrydata = $this->dbmodel->loadRegistry($data);
		if (!is_array($registrydata) || !isset($registrydata[0])) {
			return array('success' => false, 'Error_Msg' => toUTF('Ошибка чтения данных реестра'));
		} else {
			$registrydata = $registrydata[0];
		}

		$handler = dbase_open($filepath, 0);
		if (!$handler) {
			return array('success' => false, 'Error_Msg' => 'Не удается открыть dbf-файл!');
		}

		$record = dbase_get_record_with_names($handler, 1);
		array_walk($record, 'ConvertFromWin866ToUtf8');
		$date1 = trim($record['DSCHET']);
		$date2 = ConvertDateEx($registrydata['Registry_accDate'],'.','');

		if (trim($record['NSCHET']) != $registrydata['Registry_Num']) {
			return array('success' => false, 'Error_Msg' => 'Не совпадает номер реестра и импортируемого файла, импорт не произведен');
		}
		if ($date1 != $date2) {
			return array('success' => false, 'Error_Msg' => 'Не совпадает дата реестра и импортируемого файла, импорт не произведен');
		}

		$data['Registry_EvnNum'] = array();
		$data['RegistryType_id'] = $registrydata['RegistryType_id'];

		$resp = $this->dbmodel->queryResult("
			select top 1 Registry_EvnNum from {$this->scheme}.v_Registry (nolock) where Registry_id = :Registry_id
		", array(
			'Registry_id' => $data['Registry_id']
		));

		if (!empty($resp[0]['Registry_EvnNum'])) {
			$data['Registry_EvnNum'] = json_decode($resp[0]['Registry_EvnNum'], true);
		}

		$record_count = dbase_numrecords($handler);
		for($i=1; $i<=$record_count; $i++) {
			$record = dbase_get_record_with_names($handler, $i);
			array_walk($record, 'ConvertFromWin866ToUtf8');

			$d = array();
			$d['N_ZAP'] = trim($record['N_ZAP']);
			$data['N_ZAP'] = trim($record['N_ZAP']);

			$check = $this->dbmodel->checkErrorDataFromTFOMSInRegistry($data);
			if (!$check && $recerr == 0) {
				return array('success' => false, 'Error_Msg' => 'Идентификатор "'.$d['N_ZAP'].'" обнаружен в импортируемом файле, но отсутствует в реестре, импорт не произведен');
			}

			$d['Evn_id'] = $check['Evn_id'];
			$d['Registry_id'] = $check['Registry_id'];

			$errors = explode('_', trim($record['EXPERT2']));
			if (empty($errors[0])) {
				$errors = array();
			}

			foreach($errors as $error) {
				if ( empty($error) ) {
					continue;
				}

				if ( preg_match("/^\d{1,3}$/", $error) ) {
					$d['S_DOP'] = sprintf("%04d", $error);
				}
				else {
					$d['S_DOP'] = $error;
				}

				$error_exists = $this->dbmodel->existsErrorTypeInRegistry(array(
					'Registry_id' => $data['Registry_id'],
					'Evn_id' => $d['Evn_id'],
					'RegistryErrorType_Code' => $d['S_DOP']
				));
				if (isset($error_exists['Error_Msg'])) {
					return array('success' => false, 'Error_Msg' => $error_exists['Error_Msg']);
				}
				//Если ошибки указанного типа нет в реестре, то добавляем
				if ($error_exists === false) {
					$response = $this->dbmodel->setErrorFromTFOMSImportRegistry($d, $data);
					if (!is_array($response))
					{
						return array('success' => false, 'Error_Msg' => 'Ошибка при обработке реестра!');
					} elseif (!empty($response[0]) && !empty($response[0]['Error_Msg'])) {
						return array('success' => false, 'Error_Msg' => $response[0]['Error_Msg']);
					}
				}
			}

			$recall++;
			if (count($errors) > 0) {
				$recerr++;
			}
		}

		return array('success' => true, 'Registry_id' => $data['Registry_id'], 'recAll'=>$recall, 'recErr'=>$recerr, 'Message' => 'Реестр успешно загружен.');
	}

	/**
	 * Функция возвращает данные для дерева реестров в json-формате
	 */
	function loadRegistryTree()
	{
		/**
		 *	Получение ветки дерева реестров
		 */
		function getRegistryTreeChild($childrens, $field, $lvl, $node_id = "")
		{
			$val = array();
			$i = 0;
			if (!empty($node_id))
			{
				$node_id = "/".$node_id;
			}
			if ( $childrens != false && count($childrens) > 0 )
			{
				foreach ($childrens as $rows)
				{
					$node = array(
						'text'=>toUTF(trim($rows[$field['name']])),
						'id'=>$field['object'].".".$lvl.".".$rows[$field['id']].$node_id,
						//'new'=>$rows['New'],
						'object'=>$field['object'],
						'object_id'=>$field['id'],
						'object_value'=>$rows[$field['id']],
						'leaf'=>$field['leaf'],
						'iconCls'=>$field['iconCls'],
						'cls'=>$field['cls']
					);
					//$val[] = array_merge($node,$lrt,$lst);
					$val[] = $node;
				}

			}
			return $val;
		}

		// TODO: Тут надо поменять на ProcessInputData
		$data = array();
		$data = $_POST;
		$data = array_merge($data, getSessionParams());
		$c_one = array();
		$c_two = array();

		// Текущий уровень
		if ((!isset($data['level'])) || (!is_numeric($data['level'])))
		{
			$val = array();//gabdushev: $val не определена в этом scope, добавил определение чтобы не было ворнинга, не проверял.
			$this->ReturnData($val);
			return;
		}

		$node = "";
		if (isset($data['node']))
		{
			$node = $data['node'];
		}

		if (mb_strpos($node, 'PayType.1.bud') !== false) {
			if ($data['level'] >= 2) {
				$data['level']++; // для бюджета нет объединённых реестров
			}
			$data['PayType_SysNick'] = 'bud';
		}

		$response = array();

		Switch ($data['level'])
		{
			case 0: // Уровень Root. ЛПУ
				{
					$this->load->model("LpuStructure_model", "lsmodel");
					$childrens = $this->lsmodel->GetLpuNodeList($data);

					$field = Array('object' => "Lpu",'id' => "Lpu_id", 'name' => "Lpu_Name", 'iconCls' => 'lpu-16', 'leaf' => false, 'cls' => "folder");
					$c_one = getRegistryTreeChild($childrens, $field, $data['level']);
					break;
				}
			case 1: // Уровень 1. ОМС или бюджет
				{
					$childrens = array(
						array('PayType_SysNick' => 'oms', 'PayType_Name' => 'ОМС'),
						array('PayType_SysNick' => 'bud', 'PayType_Name' => 'Местный и федеральный бюджет')
					);
					$field = Array('object' => "PayType",'id' => "PayType_SysNick", 'name' => "PayType_Name", 'iconCls' => 'regtype-16', 'leaf' => false, 'cls' => "folder");
					$c_one = getRegistryTreeChild($childrens, $field, $data['level']);
					break;
				}
			case 2: // Уровень 2. Объединённые реестры
				{
					$childrens = array(
						array('RegistryType_id' => 13, 'RegistryType_Name' => 'Объединённые реестры'),
					);
					$field = Array('object' => "RegistryType",'id' => "RegistryType_id", 'name' => "RegistryType_Name", 'iconCls' => 'regtype-16', 'leaf' => false, 'cls' => "folder");
					$c_one = getRegistryTreeChild($childrens, $field, $data['level']);
					break;
				}
			case 3: // Уровень 3. Типочки
				{
					$childrens = $this->dbmodel->loadRegistryTypeNode($data);
					$field = Array('object' => "RegistryType",'id' => "RegistryType_id", 'name' => "RegistryType_Name", 'iconCls' => 'regtype-16', 'leaf' => false, 'cls' => "folder");
					$c_one = getRegistryTreeChild($childrens, $field, $data['level'], $node);
					break;
				}
			case 4: // Уровень 4. Статусы реестров
				{
					$childrens = $this->dbmodel->loadRegistryStatusNode($data);
					$field = Array('object' => "RegistryStatus",'id' => "RegistryStatus_id", 'name' => "RegistryStatus_Name", 'iconCls' => 'regstatus-16', 'leaf' => true, 'cls' => "file");
					$c_one = getRegistryTreeChild($childrens, $field, $data['level'], $node);
					break;
				}
		}
		if ( count($c_two)>0 )
		{
			$c_one = array_merge($c_one,$c_two);
		}

		$this->ReturnData($c_one);
	}

	/**
	 * Загрузка списка объединённых реестров
	 */
	function loadUnionRegistryGrid()
	{
		$data = $this->ProcessInputData('loadUnionRegistryGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadUnionRegistryGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}

	/**
	 * Получение номера объединенного реестра
	 */
	function getUnionRegistryNumber()
	{
		$data = $this->ProcessInputData('getUnionRegistryNumber', true);
		if ($data === false) { return false; }

		$LpuRegNum = $this->dbmodel->getFirstResultFromQuery('select top 1 ISNULL(Lpu_f003mcod, Lpu_interCode) from v_Lpu where Lpu_id = :Lpu_id', $data);

		$previous_month = date('m', strtotime('first day of previous month'));
		$year_of_previous_month = date('Y', strtotime('first day of previous month'));
		$Registry_Num =$LpuRegNum.'_'.$previous_month.$year_of_previous_month;
		$this->ReturnData(array(
			'UnionRegistryNumber' => $Registry_Num
		));
	}

	/**
	 * Загрузка списка обычных реестров, входящих в объединённый
	 */
	function loadUnionRegistryChildGrid()
	{
		$data = $this->ProcessInputData('loadUnionRegistryChildGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadUnionRegistryChildGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}

	/**
	 * Сохранение объединённого реестра
	 */
	function saveUnionRegistry()
	{
		$data = $this->ProcessInputData('saveUnionRegistry', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveUnionRegistry($data);
		$this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
	}

	/**
	 * Загрузка формы редактирования объединённого реестра
	 */
	function loadUnionRegistryEditForm()
	{
		$data = $this->ProcessInputData('loadUnionRegistryEditForm', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadUnionRegistryEditForm($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Удаление объединённого реестра
	 */
	function deleteUnionRegistry()
	{
		$data = $this->ProcessInputData('deleteUnionRegistry', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->deleteUnionRegistry($data);
		$this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
	}

	/**
	 * Получение списка данных объединённого реестра
	 */
	function loadUnionRegistryData()
	{
		$data = $this->ProcessInputData('loadUnionRegistryData', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadUnionRegistryData($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}

	/**
	 * Функция возвращает ошибки данных реестра по версии ТФОМС :)
	 * Входящие данные: _POST (Registry_id),
	 * На выходе: строка в JSON-формате
	 */
	function loadUnionRegistryErrorTFOMS()
	{
		$data = $this->ProcessInputData('loadUnionRegistryErrorTFOMS', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadUnionRegistryErrorTFOMS($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}
}
