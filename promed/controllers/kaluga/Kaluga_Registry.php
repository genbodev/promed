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
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Bykov Stas aka Savage (savage@swan.perm.ru)
* @version      12.11.2009
*/
require_once(APPPATH.'controllers/Registry.php');

class Kaluga_Registry extends Registry {
	var $scheme = "r40";
	var $model_name = "Registry_model";
	
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
		// Инициализация класса и настройки

		$this->inputRules['exportUnionRegistryToDBFCheckExist'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => 'required',
				'type' => 'id'
			)
		);

		$this->inputRules['exportUnionRegistryToDBF'] = array(
			array(
				'field' => 'UseNewExport',
				'label' => 'Флаг использования новой выгрзки',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'UseDebug',
				'label' => 'Флаг вывода отладочной информации',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'OverrideControlFlkStatus',
				'label' => 'Флаг пропуска контроля на статус Проведен контроль ФЛК',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'OverrideExportOneMoreOrUseExist',
				'label' => 'Флаг использования существующего или экспорта нового DBF',
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
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'RegistryType_id',
				'label' => 'Тип реестра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => null,
				'field' => 'KatNasel_id',
				'label' => 'Категория населения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'send',
				'label' => 'Флаг',
				'rules' => '',
				'type' => 'string'
			)
		);

		$this->inputRules['loadRegistryNoPolis'] = array_merge($this->inputRules['loadRegistryNoPolis'], array(
			array(
				'field' => 'Person_OrgSmo',
				'label' => 'СМО',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Person_Polis',
				'label' => '№ полиса',
				'rules' => '',
				'type' => 'string'
			)
		));

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
				'default' => null,
				'field' => 'KatNasel_id',
				'label' => 'Категория населения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => null,
				'field' => 'PayType_id',
				'label' => 'Вид оплаты',
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
			)
		);
		
		$this->inputRules['checkRegistryHasPaidInside'] = array(
			array(
				'field' => 'Registry_id',
				'label' => 'Идентификатор реестра',
				'rules' => 'required',
				'type' => 'id'
			)
		);
		
		$this->inputRules['getUnionRegistryNumber'] = array(
			array(
				'field' => 'Lpu_id',
				'label' => 'ЛПУ',
				'rules' => '',
				'type' => 'id'
			)
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
		
		$this->inputRules['loadUnionRegistryChildGrid'] = array(
			array(
				'field' => 'Registry_pid',
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
				'field' => 'OrgSMO_id',
				'label' => 'СМО',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_id',
				'label' => 'Лпу',
				'rules' => '',
				'type' => 'id'
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
			),
			array(
					'field' => 'path',
					'label' => 'Путь к уже загруженному файлу',
					'rules' => '',
					'type' => 'string'
			)
		);
	}

	/**
	 * Импорт реестра из ТФОМС
	 */
	function importRegistryFromTFOMS()
	{
		$upload_path = './'.IMPORTPATH_ROOT.$_SESSION['lpu_id'].'/';
		$allowed_types = explode('|','dbf');

		set_time_limit(0);

		$data = $this->ProcessInputData('importRegistryFromTFOMS', true);
		if ($data === false) { return false; }
		
		if (empty($data['path'])) { // Если файл еще не загружен и это первый вызов функции, то загружаем
			if (!isset($_FILES['RegistryFile'])) {
				$this->ReturnData( array('success' => false, 'Error_Code' => 100011 , 'Error_Msg' => 'Не выбран файл реестра!') ) ;
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
				$this->ReturnData(array('success' => false, 'Error_Code' => 100012 , 'Error_Msg' => $message));
				return false;
			}

			// Тип файла разрешен к загрузке?
			$x = explode('.', $_FILES['RegistryFile']['name']);
			$file_data['file_ext'] = end($x);
			if (!in_array(strtolower($file_data['file_ext']), $allowed_types)) {
				$this->ReturnData(array('success' => false, 'Error_Code' => 100013 , 'Error_Msg' => 'Данный тип файла не разрешен.'));
				return false;
			}

			// Правильно ли указана директория для загрузки?
			if (!@is_dir($upload_path))
			{
				mkdir( $upload_path );
			}
			if (!@is_dir($upload_path)) {
				$this->ReturnData(array('success' => false, 'Error_Code' => 100014 , 'Error_Msg' => 'Путь для загрузки файлов некорректен.'));
				return false;
			}

			// Имеет ли директория для загрузки права на запись?
			if (!is_writable($upload_path)) {
				$this->ReturnData( array('success' => false, 'Error_Code' => 100015 , 'Error_Msg' => 'Загрузка файла не возможна из-за прав пользователя.'));
				return false;
			}

			$dbffile = $_FILES['RegistryFile']['name'];
			if (!move_uploaded_file($_FILES["RegistryFile"]["tmp_name"], $upload_path.$dbffile)){
				$this->ReturnData( array('success' => false, 'Error_Code' => 100015 , 'Error_Msg' => 'Не удаётся переместить файл.'));
				return false;
			}

			if (empty($dbffile)) {
				$this->ReturnData( array('success' => false, 'Error_Code' => 100015 , 'Error_Msg' => 'Файл не является архивом реестра.'));
				return false;
			}

			// Проверяем соответствие имения файла реестру
			// @task https://redmine.swan.perm.ru/issues/76193
			$res = $this->dbmodel->GetUnionRegistryDBFExport($data);

			if ( !is_array($res) || count($res) == 0 ) {
				$this->ReturnError('Произошла ошибка при чтении реестра. Сообщите об ошибке разработчикам.');
				return false;
			}

			$fileName = "W" . sprintf('%03d', $res[0]['Lpu_Code']) . substr($res[0]['Registry_endDate']->format('Y'), -1) . sprintf('%02d', $res[0]['Registry_endDate']->format('m')) . $res[0]['Registry_FileNum'] . ".DBF";

			if ( mb_strtolower($fileName) != mb_strtolower($dbffile) ) {
				$this->ReturnError('Выбранный файл не соответствует реестру.');
				return false;
			}

			// Передадим информацию об загруженном файле
			echo json_encode(array('success' => true, 'Registry_id' => $data['Registry_id'], 'Message' => 'Обработка ответа ТФОМС производится в фоновом режиме.', 'path'=>$upload_path.$dbffile));
		} else {
			// Если дошли до этого места, то значит файл загрузили 
			// посылаем ответ клиенту...
			ignore_user_abort(true);
			
			// посылаем ответ клиенту...
			/*ignore_user_abort(true);
			ob_start();
			

			$size = ob_get_length();

			ob_flush();
			flush();
			ob_end_flush();

			if ( session_id() ) {
				session_write_close();
			}*/

			// ... и продолжаем выполнять скрипт на стороне сервера

			$this->dbmodel->setRegistryCheckStatus(array(
				'Registry_id' => $data['Registry_id'],
				'pmUser_id' => $data['pmUser_id'],
				'RegistryCheckStatus_id' => 41 // в работе
			));
			$path = $data['path']; // $upload_path.$dbffile

			// разбираем DBF
			$h = dbase_open($path, 0);
			$r = dbase_numrecords($h);

			$orgSmoList = array();
			$OmsSprTerr_id = $this->dbmodel->getFirstResultFromQuery("select top 1 OmsSprTerr_id from v_OmsSprTerr with (nolock) where OmsSprTerr_Code = 1");
			// Список идентификаторов пациентов, для которых обновленный полис уже добавлен
			$polisInfoUpdated = array();

			$this->dbmodel->deleteRegistryNoPolis($data);

			for ($i=1; $i <= $r; $i++) {
				$record = dbase_get_record_with_names($h, $i);
				array_walk($record, 'ConvertFromWin866ToUtf8');
				$record = array_map('trim', $record);

				// получаем все случаи по данному пациенту из реестра
				$evnDatas = $this->dbmodel->checkErrorDataInRegistry(array(
					'Registry_id' => $data['Registry_id'],
					'Person_id' => $record['PID']
				));
				if ($evnDatas === false) {
					// Пациент не обнаружен в реестре
					continue;
				}

				if (!empty($record['QM_OGRN'])) {
					if ( !array_key_exists($record['QM_OGRN'], $orgSmoList) ) {
						$orgSmoList[$record['QM_OGRN']] = $this->dbmodel->identifyOrgSMO($record);
					}

					$record['OrgSMO_id'] = $orgSmoList[$record['QM_OGRN']];
				}

				$Person_id = $record['PID'];
				$PolisType_id = null;

				// https://redmine.swan.perm.ru/issues/76193
				// 1) Поле с серией не заполнено, а в поле с номером 16 цифр - это полис нового образца.
				if ( empty($record['SER_POL']) && mb_strlen($record['NUM_POL']) == 16 ) {
					$PolisType_id = 4;
				}
				// 2) Поле с серией не заполнено, а в поле с номером 11 цифр - это полис нового образца.
				if ( empty($record['SER_POL']) && mb_strlen($record['NUM_POL']) == 11 ) {
					$PolisType_id = 4;
					$record['NUM_POL'] = $record['ENP'];
				}
				// 3) Поле с серией не заполнено, а в поле с номером 9 цифр - это временно свидетельство.
				else if ( empty($record['SER_POL']) && mb_strlen($record['NUM_POL']) == 9 ) {
					$PolisType_id = 3;
				}
				// 4) В поле с серией 7 цифр, а в поле с номером 6 цифр - это полис старого образца
				else if ( mb_strlen($record['SER_POL']) == 7 && mb_strlen($record['NUM_POL']) == 6 ) {
					$PolisType_id = 1;
				}

				foreach($evnDatas as $evnData) {
					// При обнаружении пациента без полиса данные включаются во вкладку «4. Незастрахованные»
					if (empty($record['SER_POL']) && empty($record['NUM_POL'])) {
						$params = array();
						$params['Evn_id'] = $evnData['Evn_id'];
						$params['Registry_id'] = $evnData['Registry_id'];
						$params['RegistryType_id'] = $evnData['RegistryType_id'];
						$params['pmUser_id'] = $data['pmUser_id'];
						$this->dbmodel->setRegistryDataNoPolis($params);

						continue;
					}

					if ( empty($record['OrgSMO_id']) || $record['OrgSMO_id'] === false ) {
						continue;
					}

					// проверяем совпадение полисных данных с хранящимися на случае
					if (
						!in_array($Person_id, $polisInfoUpdated) // это условие необходимо, т.к. в $evnDatas может быть несколько записей по одному пациенту
						&& ( $evnData['Polis_Ser'] != $record['SER_POL']
							|| $evnData['Polis_Num'] != $record['NUM_POL']
							|| $evnData['OrgSMO_id'] != $record['OrgSMO_id']
							|| $evnData['Polis_begDate'] != $record['DATE_B']
							|| $evnData['Polis_endDate'] != $record['DATE_E']
						)
					) {
						unset($this->db);
						$this->load->database('default');

						$newPolisParams = array(
							'Person_id' => $Person_id,
							'Server_id' => 0,
							'OmsSprTerr_id' => $OmsSprTerr_id,
							'OrgSMO_id' => $record['OrgSMO_id'],
							'PolisType_id' => $PolisType_id,
							'Polis_begDate' => $record['DATE_B'],
							'Polis_endDate' => (empty($record['DATE_E']) || $record['DATE_E'] == '19000101' ? null : $record['DATE_E']),
							'Polis_Ser' => $record['SER_POL'],
							'Polis_Num' => $record['NUM_POL'],
							'pmUser_id' => $data['pmUser_id'],
						);
						if(empty($newPolisParams['Polis_endDate']) || $newPolisParams['Polis_endDate'] >= $newPolisParams['Polis_begDate']){
							$resp = $this->dbmodel->addNewPolisToPerson($newPolisParams);
						}

						unset($this->db);
						$this->load->database('registry');

						$polisInfoUpdated[] = $Person_id;
					}
				}
			}

			$this->dbmodel->setRegistryCheckStatus(array(
				'Registry_id' => $data['Registry_id'],
				'pmUser_id' => $data['pmUser_id'],
				'RegistryCheckStatus_id' => 42 // идентифицирован
			));
			$msgData = array(
				'autotype' => 1
				,'User_rid' => $data['pmUser_id']
				,'pmUser_id' => $data['pmUser_id']
				,'type' => 1
				,'title' => 'Импорт реестра из ТФОМС'
				,'text' => 'Ответ ТФОМС по реестру успешно загружен.'
			);
			$this->load->model('Messages_model', 'Messages_model');
			$msgResponse = $this->Messages_model->autoMessage($msgData);
			// Пишем информацию об импорте в историю
			$this->dbmodel->dumpRegistryInformation($data, 3);
			$this->ReturnData(array('success' => true, 'Registry_id' => $data['Registry_id'], 'Message' => 'Ответ ТФОМС успешно загружен.'));
		}
		return true;
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
	 * Получение номера объединённого реестра
	 */
	function getUnionRegistryNumber()
	{
		$data = $this->ProcessInputData('getUnionRegistryNumber', true);
		if ($data === false) { return false; }
		
		$Registry_Num = $this->dbmodel->getUnionRegistryNumber($data);
		$this->ReturnData(array(
			'UnionRegistryNumber' => $Registry_Num
		));
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
						'text'=>trim($rows[$field['name']]),
						'id'=>$field['object'].".".$lvl.".".$rows[$field['id']].$node_id,
						'object'=>$field['object'],
						'object_id'=>$field['id'],
						'object_value'=>$rows[$field['id']],
						'leaf'=>$field['leaf'],
						'iconCls'=>$field['iconCls'],
						'cls'=>$field['cls']
					);
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
			case 1: // Уровень 1. Объединённые реестры
			{
				$childrens = array(
					array('RegistryType_id' => 13, 'RegistryType_Name' => 'Объединённые реестры'),
				);
				$field = Array('object' => "RegistryType",'id' => "RegistryType_id", 'name' => "RegistryType_Name", 'iconCls' => 'regtype-16', 'leaf' => false, 'cls' => "folder");
				$c_one = getRegistryTreeChild($childrens, $field, $data['level']);
				break;
			}
			case 2: // Уровень 2. Типочки
			{
				$childrens = $this->dbmodel->loadRegistryTypeNode($data);
				$field = Array('object' => "RegistryType",'id' => "RegistryType_id", 'name' => "RegistryType_Name", 'iconCls' => 'regtype-16', 'leaf' => false, 'cls' => "folder");
				$c_one = getRegistryTreeChild($childrens, $field, $data['level']);
				break;
			}
			case 3: // Уровень 3. Статусы реестров
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
	 * Проверка наличия в объединенном реестре оплаченных реестров
	 */
	function checkRegistryHasPaidInside() {
		$data = $this->ProcessInputData('checkRegistryHasPaidInside', true);
		if ($data === false) { return false; }

		if ($this->dbmodel->hasRegistryPaid($data['Registry_id'])) {
			$this->ReturnData( array('success' => true, 'existPaid' => true) ) ;
			return true;
		}

		$this->ReturnData( array('success' => true, 'existPaid' => false) ) ;
		return true;
	}

	/**
	 * Функция проверяет, есть ли уже выгруженный файл реестра
	 * Входящие данные: _POST (Registry_id)
	 * На выходе: JSON-строка.
	 */
	function exportUnionRegistryToDBFCheckExist() {
		$data = $this->ProcessInputData('exportUnionRegistryToDBFCheckExist', true);
		if ($data === false) { return false; }

		$res = $this->dbmodel->GetUnionRegistryDBFExport($data);

		if ( is_array($res) && count($res) > 0 ) {
			if ( $res[0]['Registry_xmlExportPath'] == '1' ) {
				$this->ReturnData(array('success' => true, 'exportfile' => 'inprogress'));
				return true;
			}
			else if ( !empty($res[0]['Registry_xmlExportPath']) ) {
				$this->ReturnData(array('success' => true, 'exportfile' => ($res[0]['RegistryCheckStatus_Code'] == 2 ? 'only' : '') . 'exists'));
				return true;
			}
			else {
				$this->ReturnData(array('success' => true, 'exportfile' => 'empty'));
				return true;
			}
		}
		else {
			$this->ReturnError('Ошибка получения данных по реестру');
			return false;
		}
	}

	/**
	 * Функция формирует файлы в DBF формате для выгрузки данных
	 * Входящие данные: _POST (Registry_id)
	 * На выходе: JSON-строка
	 */
	function exportUnionRegistryToDBF() {
		if ( !extension_loaded('dbase') ) {
			$this->ReturnError('Не загружен модулья для работы с DBF');
			return false;
		}

		$data = $this->ProcessInputData('exportUnionRegistryToDBF', true);
		if ( $data === false ) { return false; }

		$this->load->library('textlog', array('file'=>'exportUnionRegistryToDBF.log'));
		$this->textlog->add('');
		$this->textlog->add('exportUnionRegistryToDBF: Запуск');

		// Определяем надо ли при успешном формировании проставлять статус и, соответсвенно, не выводить ссылки
		if (!isset($data['send']))
			$data['send'] = 0;

		// Проверяем наличие и состояние реестра, и если уже сформирован и send = 1, то делаем попытку отправить реестр
		$this->textlog->add('exportUnionRegistryToDBF: GetRegistryDBFExport: Проверяем наличие и состояние реестра, и если уже сформирован и send = 1, то делаем попытку отправить реестр');
		$res = $this->dbmodel->GetUnionRegistryDBFExport($data);
		$this->textlog->add('exportUnionRegistryToDBF: GetRegistryDBFExport: Проверка закончена');

		if ( !is_array($res) || count($res) == 0 ) {
			$this->ReturnError('Произошла ошибка при чтении реестра. Сообщите об ошибке разработчикам.');
			return false;
		}

		// Запрет экспорта и отправки в ТФОМС реестра, нуждающегося в переформировании (refs #13648)
		if ( !empty($res[0]['Registry_IsNeedReform']) && $res[0]['Registry_IsNeedReform'] == 2 ) {
			$this->textlog->add('exportUnionRegistryToDBF: Выход с сообщением: Реестр нуждается в переформировании, отправка и экспорт невозможны. Переформируйте реестр и повторите действие.');
			$this->ReturnError('Реестр нуждается в переформировании, отправка и экспорт невозможны. Переформируйте реестр и повторите действие.');
			return false;
		}

		// Запрет экспорта реестра при 0 записей
		if ( empty($res[0]['RegistryData_Count']) ) {
			$this->textlog->add('exportUnionRegistryToDBF: Выход с сообщением: Нет записей в реестре.');
			$this->ReturnError('Экспорт невозможен. Нет случаев в реестре.', 13);
			return false;
		}

		$data['Registry_endMonth'] = $res[0]['Registry_endDate']->format('m');
		$data['Registry_endYear'] = $res[0]['Registry_endDate']->format('Y');

		$fileName = sprintf('%03d', $res[0]['Lpu_Code']) . substr($data['Registry_endYear'], -1) . sprintf('%02d', $data['Registry_endMonth']);

		$this->textlog->add('exportUnionRegistryToDBF: Получили путь из БД: ' . $res[0]['Registry_xmlExportPath']);

		if ( $res[0]['Registry_xmlExportPath'] == '1' ) {
			$this->textlog->add('exportUnionRegistryToDBF: Выход с сообщением: Реестр уже экспортируется. Пожалуйста, дождитесь окончания экспорта (в среднем 1-10 мин).');
			$this->ReturnError('Реестр уже экспортируется. Пожалуйста, дождитесь окончания экспорта (в среднем 1-10 мин).');
			return false;
		}
		// если уже выгружен реестр
		else if ( !empty($res[0]['Registry_xmlExportPath']) ) {
			$this->textlog->add('exportUnionRegistryToDBF: Реестр уже выгружен');

			if ( empty($data['OverrideExportOneMoreOrUseExist']) ) {
				$this->textlog->add('exportUnionRegistryToDBF: Выход с вопросом: Файл реестра существует на сервере. Если вы хотите сформировать новый файл выберете (Да), если хотите скачать файл с сервера нажмите (Нет)');
				$this->ReturnError('Файл реестра существует на сервере. Если вы хотите сформировать новый файл выберете (Да), если хотите скачать файл с сервера нажмите (Нет)', 11);
				return false;
			}
			else if ( $data['OverrideExportOneMoreOrUseExist'] == 1 ) {
				$link = $res[0]['Registry_xmlExportPath'];
				$usePrevXml = '';

				if ( empty($data['onlyLink']) ) {
					$usePrevXml = 'usePrevXml: true, ';
				}

				echo "{'success':true, $usePrevXml'Link':'$link'}";
				$this->textlog->add('exportUnionRegistryToDBF: Выход с передачей ссылкой: '.$link);

				return true;
			}
			// Запрет экспорта реестра, находящегося в процессе идентификации
			// @task https://redmine.swan.perm.ru/issues/76193
			else if ( !empty($res[0]['RegistryCheckStatus_Code']) && $res[0]['RegistryCheckStatus_Code'] == 2 ) {
				$this->textlog->add('exportUnionRegistryToDBF: Выход с сообщением: Реестр в процессе идентификации, переформирование невозможно.');
				$this->ReturnError('Реестр в процессе идентификации, переформирование невозможно.');
				return false;
			}
		}

		$this->textlog->add('exportUnionRegistryToDBF: refreshRegistry: Пересчитываем реестр');

		// Удаление помеченных на удаление записей и пересчет реестра
		if ( $this->refreshRegistry($data) === false ) {
			// выход с ошибкой
			$this->textlog->add('exportUnionRegistryToDBF: refreshRegistry: При обновлении данных реестра произошла ошибка.');
			$this->ReturnError('При обновлении данных реестра произошла ошибка.');
			return false;
		}

		$this->textlog->add('exportUnionRegistryToDBF: refreshRegistry: Реестр пересчитали');

		// Формирование DBF в зависимости от типа
		// В случае возникновения ошибки - необходимо снять статус равный 1 - чтобы при следующей попытке выгрузить Dbf не приходилось обращаться к разработчикам
		try {
			$data['RegistryCheckStatus_id'] = null; // сбрасываем статус при новом экспорте
			$data['Status'] = '1';

			$this->dbmodel->SetExportStatus($data);
			$this->textlog->add('exportUnionRegistryToDBF: SetExportStatus: Установили статус реестра в 1');
			set_time_limit(0); // обязательно, иначе на больших объемах выгружаемых данных до конца не выполнится

			// каталог в котором лежат выгружаемые файлы
			$out_dir = "re_dbf_" . time() . "_".$data['Registry_id'];
			while ( file_exists(EXPORTPATH_REGISTRY . $out_dir) ) {
				$out_dir = "re_dbf_" . time() . "_".$data['Registry_id'];
			}

			mkdir(EXPORTPATH_REGISTRY . $out_dir);

			$fileNum = $this->dbmodel->SetRegistryFileNum($data);

			if ( empty($fileNum) ) {
				throw new Exception('Ошибка при получении номера выгружаемого файла');
			}

			$fileName .= $fileNum;

			$exportParams = array(
				'person' => array(
					'fileName' => 'P' . $fileName . '.dbf',
					'storedProcedure' => 'p_Registry_Untd_expPac',
					'DBF' => array(
						array("PID",		"C",	10, 0),
						array("SS",			"C",	14, 0),
						array("FAM",		"C",	40, 0),
						array("IM",			"C",	40, 0),
						array("OT",			"C",	40, 0),
						array("DR",			"D",	8, 0),
						array("W",			"C",	1, 0),
						array("OSSL",		"C",	5, 0),
						array("STAT_SOC",	"C",	3, 0),
						array("SER_POL",	"C",	12, 0),
						array("NUM_POL",	"C",	20, 0),
						array("DATE_B",		"D",	8, 0),
						array("DATE_E",		"D",	8, 0),
						array("IZM_OMC",	"C",	10, 0),
						array("PID_OMC",	"C",	10, 0),
						array("QM_OGRN",	"C",	15, 0),
						array("SMO",		"C",	50, 0),
						array("OKATO_OMS",	"C",	5, 0),
						array("C_DOC",		"N",	2, 0),
						array("SER_DOC",	"C",	8, 0),
						array("NUM_DOC",	"C",	8, 0),
						array("KLADR",		"C",	17, 0),
						array("CNTRY",		"C",	3, 0),
						array("STATE",		"C",	40, 0),
						array("RAYON",		"C",	40, 0),
						array("TOWN",		"C",	40, 0),
						array("NP",			"C",	50, 0),
						array("STREET",		"C",	50, 0),
						array("DOM",		"C",	8, 0),
						array("KOR",		"C",	2, 0),
						array("KV",			"C",	4, 0),
						array("MR",			"C",	150, 0),
						array("FAM1",		"C",	40, 0),
						array("IM1",		"C",	40, 0),
						array("OT1",		"C",	40, 0),
						array("W1",			"C",	1, 0),
						array("DR1",		"D",	8, 0),
						array("TMO",		"C",	3, 0),
						array("TMO_SK",		"C",	3, 0),
					)
				),
				'polka' => array(
					'fileName' => 'C' . $fileName . '.dbf',
					'storedProcedure' => 'p_Registry_EvnPL_expVizit',
					'DBF' => array(
						array("NPP",		"N",	8, 0),
						array("PID",		"C",	10, 0),
						array("TYPE_LPU",	"C",	1, 0),
						array("LPU",		"C",	3, 0),
						array("NMK",		"C",	7, 0),
						array("DOCTOR",		"C",	20, 0),
						array("SPEC",		"C",	10, 0),
						array("MKB",		"C",	6, 0),
						array("USL",		"C",	20, 0),
						array("RESULT",		"C",	3, 0),
						array("DT2",		"D",	8, 0),
						array("KDP",		"N",	7, 2),
						array("STOIM",		"N",	12, 2),
						array("PAY",		"N",	12, 2),
						array("DEF_LPU",	"C",	5, 0),
						array("VID_OPL",	"C",	2, 0),
						array("PLAT",		"C",	3, 0),
						array("PURPOSE",	"C",	3, 0),
						array("ISHOD",		"C",	3, 0),
						array("ZS",			"C",	1, 0),
						array("LNAPR",		"C",	3, 0),
					)
				),
				'stac' => array(
					'fileName' => 'A' . $fileName . '.dbf',
					'storedProcedure' => 'p_Registry_EvnPS_expVizit',
					'DBF' => array(
						array("NPP",		"N",	8, 0),
						array("PID",		"C",	10, 0),
						array("TYPE_LPU",	"C",	1, 0),
						array("LPU",		"C",	3, 0),
						array("NIB",		"C",	7, 0),
						array("DOCTOR",		"C",	20, 0),
						array("OTD",		"C",	2, 0),
						array("PROFOTD",	"C",	3, 0),
						array("PROFKOY",	"C",	3, 0),
						array("OGZ",		"C",	10, 0),
						array("MKB",		"C",	6, 0),
						array("USL",		"C",	20, 0),
						array("FINAL",		"C",	3, 0),
						array("DT1",		"D",	8, 0),
						array("DT2",		"D",	8, 0),
						array("KDP",		"N",	7, 2),
						array("STOIM",		"N",	12, 2),
						array("PAY",		"N",	12, 2),
						array("DEF_LPU",	"C",	5, 0),
						array("VID_OPL",	"C",	2, 0),
						array("PLAT",		"C",	3, 0),
						array("LNAPR",		"C",	3, 0),
						array("GOSP",		"C",	1, 0),
						array("PEPO",		"C",	1, 0),
						array("ZSLNK",		"N",	8, 0),
						array("ISHOD",		"C",	3, 0),
					)
				)
			);

			foreach ( $exportParams as $exportParam ) {
				$this->textlog->add("exportUnionRegistryToDBF: Формируем файл " . $exportParam['fileName']);

				$h = dbase_create(EXPORTPATH_REGISTRY . $out_dir . "/" . $exportParam['fileName'], $exportParam['DBF']);

				$res = $this->dbmodel->getDataForExport($exportParam['storedProcedure'], $data['Registry_id']);

				while ( $row = $res->_fetch_assoc() ) {
					array_walk($row, 'ConvertFromUtf8ToCp866');
					dbase_add_record($h, array_values($row));
				}

				dbase_close($h);

				$this->textlog->add("exportUnionRegistryToDBF: Файл " . $exportParam['fileName'] . ' успешно сформирован');
			}

			$file_zip_name = EXPORTPATH_REGISTRY . $out_dir . "/" . $fileName . ".zip";
			$zip = new ZipArchive();
			$zip->open($file_zip_name, ZIPARCHIVE::CREATE);

			foreach ( $exportParams as $exportParam ) {
				$zip->AddFile(EXPORTPATH_REGISTRY . $out_dir . "/" . $exportParam['fileName'], $exportParam['fileName']);
			}

			$zip->close();

			$this->textlog->add('exportUnionRegistryToDBF: Упаковали в ZIP ' . $file_zip_name);

			foreach ( $exportParams as $exportParam ) {
				unlink(EXPORTPATH_REGISTRY . $out_dir . "/" . $exportParam['fileName']);
			}

			$this->textlog->add('exportUnionRegistryToDBF: Почистили папку');

			if ( file_exists($file_zip_name) ) {
				$data['Status'] = $file_zip_name;

				$this->dbmodel->SetExportStatus($data);

				$this->textlog->add('exportUnionRegistryToDBF: Передача ссылки: ' . $file_zip_name);
				// echo "{'success':true,'Link':'{$file_zip_name}'}";

				$this->textlog->add("exportUnionRegistryToDBF: Передали строку на клиент {'success':true,'Link':'$file_zip_name'}");

				// Пишем информацию о выгрузке в историю
				$this->dbmodel->dumpRegistryInformation($data, 2);
			}
			else{
				throw new Exception('Ошибка создания архива реестра!');
			}

			$this->textlog->add("exportUnionRegistryToDBF: Финиш");
		}
		catch ( Exception $e ) {
			$data['Status'] = '';
			$this->dbmodel->SetExportStatus($data);
			$this->textlog->add("exportUnionRegistryToDBF: " . $e->getMessage());
			$this->ReturnError($e->getMessage());
		}

		return true;
	}
}
