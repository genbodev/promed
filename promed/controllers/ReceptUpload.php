<?php	defined('BASEPATH') or die ('No direct script access allowed');


class ReceptUpload extends swController {
	public $inputRules = array(
		'loadReceptUploadLogList' => array(
			array('field' => 'start', 'label' => '', 'rules' => '', 'type' => 'int', 'default' => 0 ),
			array('field' => 'limit', 'label' => '', 'rules' => '', 'type' => 'int', 'default' => 50 ),
			array('field' => 'begDate', 'label' => '', 'rules' => '', 'type' => 'date' ),
			array('field' => 'endDate', 'label' => '', 'rules' => '', 'type' => 'date' ),
			array('field' => 'Contragent_id', 'label' => 'Поставщик', 'rules' => '', 'type' => 'id' ),
			array('field' => 'ReceptUploadType_id', 'label' => 'Типы загружаемых данных', 'rules' => '', 'type' => 'id' ),
			array('field' => 'ReceptUploadLog_setDT_Range', 'label' => 'Период', 'rules' => 'trim', 'type' => 'daterange')
		),
		'uploadReceptUploadLog' => array(
			array('field' => 'ReceptUploadLog_id', 'label' => 'Ид. записи в журнале загруженных данных', 'rules' => '', 'type' => 'id' ),
			array('field' => 'ReceptUploadDeliveryType_id', 'label' => 'Виды доставки данных', 'rules' => '', 'type' => 'id' ),
			array('field' => 'ReceptUploadType_id', 'label' => 'Типы загружаемых данных', 'rules' => '', 'type' => 'id' ),
			array('field' => 'Contragent_id', 'label' => 'Поставщик', 'rules' => '', 'type' => 'id' ),
			array('field' => 'ReceptUploadLog_InFail', 'label' => 'Ссылка на файл загрузки', 'rules' => '', 'type' => 'string' ),
			array('field' => 'ReceptUploadStatus_id', 'label' => 'Статусы загружаемых данных', 'rules' => '', 'type' => 'id' ),
			array('field' => 'ReceptUploadLog_Act', 'label' => 'Ссылка на акт', 'rules' => '', 'type' => 'string' ),
			array('field' => 'ReceptUploadLog_OutFail', 'label' => 'Ссылка на файлы, возвращаемые поставщику', 'rules' => '', 'type' => 'string' )
		),
		'deleteReceptUploadLog' => array(
			array('field' => 'ReceptUploadLog_id', 'label' => 'Ид. записи в журнале загруженных данных', 'rules' => 'required', 'type' => 'id' )
		),
		'importAndExpertise' => array(
			array('field' => 'ReceptUploadLog_id', 'label' => 'Ид. записи в журнале загруженных данных', 'rules' => 'required', 'type' => 'id' )
		),
		'sendActAboutImport' => array(
			array('field' => 'ReceptUploadLog_id', 'label' => 'Ид. записи в журнале загруженных данных', 'rules' => 'required', 'type' => 'id' )
		)
	);
	
	private $mapFieldsAssoc = array(
		'RegistryRecept' => array(
			'SS' => 'RegistryRecept_Snils',
			'OKATO_REG' => 'RegistryRecept_UAddOKATO',
			'C_OGRN' => 'RegistryRecept_LpuOGRN',
			'MCOD' => 'RegistryRecept_LpuMod',
			'PCOD' => 'RegistryRecept_MedPersonalCode',
			'DS' => 'RegistryRecept_Diag',
			'SN_LR' => 'RegistryRecept_Recent',
			'DATE_VR' => 'RegistryRecept_setDT',
			'C_FINL' => 'RegistryRecept_RecentFinance',
			'PR_LR' => 'RegistryRecept_Persent',
			'A_COD' => 'RegistryRecept_FarmacyACode',
			'NOMK_LS' => 'RegistryRecept_DrugNomCode',
			'KO_ALL' => 'RegistryRecept_DrugKolvo',
			'DOZ_ME' => 'RegistryRecept_DrugDose',
			'C_PFS' => 'RegistryRecept_DrugCode',
			'DATE_OBR' => 'RegistryRecept_obrDate',
			'DATE_OTP' => 'RegistryRecept_otpDate',
			'SL_ALL' => 'RegistryRecept_Price',
			'TYPE_SCHET' => 'RegistryRecept_SchetType',
			'FO_OGRN' => 'RegistryRecept_FarmacyOGRN',
			'P_KEK' => 'RegistryRecept_ProtoKEK',
			'D_TYPE' => 'RegistryRecept_SpecialCase',
			'RECIPEID' => 'RegistryRecept_ReceptId',
			'GK_NUM' => 'RegistryRecept_SupplyNum',
			'ISDISCARD' => 'RegistryRecept_IsDiscard'
		),
		'RegistryReceptPerson' => array(
			'SS' => 'RegistryReceptPerson_Snils',
			'SN_POL' => 'RegistryReceptPerson_Polis',
			'FAM' => 'RegistryReceptPerson_SurName',
			'IM' => 'RegistryReceptPerson_FirName',
			'OT' => 'RegistryReceptPerson_SecName',
			'W' => 'RegistryReceptPerson_Sex',
			'DR' => 'RegistryReceptPerson_BirthDay',
			'C_KAT' => 'RegistryReceptPerson_Privilege',
			'SN_DOC' => 'RegistryReceptPerson_Document',
			'C_DOC' => 'RegistryReceptPerson_DocumentType',
			'OKATO_OMS' => 'RegistryReceptPerson_OmsSprTerrOKATO',
			'QM_OGRN' => 'RegistryReceptPerson_SmoOGRN',
			'OKATO_REG' => 'RegistryReceptPerson_UAddOKATO',
			'D_TYPE' => 'RegistryReceptPerson_SpecialCase'
		),
		'Invoice' => array(
			'METHOD' => 'Invoice_MethodType',
			'DOC_CODE' => 'Invoice_DocC',
			'DOC_N' => 'Invoice_DocN',
			'DTYPE_CODE' => 'Invoice_DTypeCode',
			'DATE_DOC' => 'Invoice_DateDoc',
			'C_FINL' => 'Invoice_CFinl',
			'GK' => 'Invoice_gk',
			'SUPL_COD' => 'Invoice_SuplCode',
			'SUPL_OGRN' => 'Invoice_SuplOgrn',
			'RECIP_COD' => 'Invoice_RecipCode',
			'RECIP_OGRN' => 'Invoice_RecipOgrn',
			'FACTUR' => 'Invoice_Factur',
			'AKT' => 'Invoice_AktNum'
		),
		'InvoiceDrug' => array(
			'DOC_CODE' => 'InvoiceDrug_DocC',
			'NOMK_LS' => 'InvoiceDrug_NomkLs',
			'KO_ALL' => 'InvoiceDrug_KoAll',
			'PRICE' => 'InvoiceDrug_Price',
			'SUM' => 'InvoiceDrug_Sum',
			'NDS' => 'InvoiceDrug_NDS',
			'SUM_NDS' => 'InvoiceDrug_SumNDS',
			'SERIES' => 'InvoiceDrug_Series',
			'REGNUM' => 'InvoiceDrug_SertReg',
			'EAN' => 'InvoiceDrug_EAN13'
		)
	);

	/**
	 * Конструктор.
	 */
	function __construct() {
		parent::__construct();
		$this->load->database();
		$this->load->model('ReceptUpload_model', 'dbmodel');
	}
	
	/**
	 *	Чтение загруженных данных "Результаты ФЛК"
	 */
	function loadReceptUploadLogList() {
		$data = $this->ProcessInputData('loadReceptUploadLogList', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->loadReceptUploadLogList($data);
		if( isset($response['data']) ) {
			foreach($response['data'] as &$row) {
				$row['isHisRecord'] = (int)is_file($row['ReceptUploadLog_InFail']);
				$row['file_name'] = (is_file($row['ReceptUploadLog_InFail'])) ? basename($row['ReceptUploadLog_InFail']) : null;
				$row['file_size'] = (is_file($row['ReceptUploadLog_InFail'])) ? round(filesize($row['ReceptUploadLog_InFail'])/1024, 2).' KB' : 0;
			}
		}
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}

	/**
	 * Сохранение данных в журнал загрузок
	 */
	function uploadReceptUploadLog() {
		$data = $this->ProcessInputData('uploadReceptUploadLog', true);
		if ($data === false) { return false; }
		//print_r($_FILES); die();
		
		$root_dir = IMPORTPATH_ROOT . 'recept_uploads/';
		if( !is_dir($root_dir) ) {
			if( !mkdir($root_dir) ) {
				return $this->ReturnError('Не удалось создать директорию для хранения загружаемых данных!');
			}
		}
		
		if( !isset($_FILES['uploadfilefield']) ) {
			return $this->ReturnError('Ошибка! Отсутствует файл! (поле uploadfilefield)');
		}
		
		$file = $_FILES['uploadfilefield'];
		if( $file['error'] > 0 ) {
			return $this->ReturnError('Ошибка при загрузке файла!', $file['error']);
		}
		
		$fileFullName = $root_dir . $file['name'];
		if( is_file($fileFullName) ) {
			$fileFullName = $root_dir . time() . '_' . $file['name'];
		}
		
		if( !rename($file['tmp_name'], $fileFullName) ) {
			return $this->ReturnError('Не удалось создать файл ' . $fileFullName);
		}
		
		$data['ReceptUploadLog_InFail'] = $fileFullName;
		$response = $this->dbmodel->saveReceptUploadLog($data);
		//var_dump($fileFullName); die();
		if( !is_array($response) || ( isset($response[0]) && strlen($response[0]['Error_Msg']) > 0 ) ) {
			unlink($fileFullName);
		} elseif (count($response) > 0 && $response[0]['ReceptUploadLog_id'] > 0) {
			//импорт аптек
			if ($data['ReceptUploadType_id'] == 4) { //Об Аптеках
				$data['ReceptUploadLog_id'] =  $response[0]['ReceptUploadLog_id'];
				$import_res = $this->importOrgFaramacy($data);
				$import_msg = "Обработано записей: {$import_res['record_cnt']}.";
				$import_msg .= $import_res['breaked'] > 0 ? " Не пригодных для сохранения записей: {$import_res['breaked']}." : "";
				$import_msg .= $import_res['saved'] > 0 ? " Сохранено записей: {$import_res['saved']}." : "";
				$import_msg .= $import_res['updated'] > 0 ? " Обновлено записей: {$import_res['updated']}." : "";

				$response[0]['farmacy_import_msg'] = $import_msg;
			}
		}
		
		$this->ProcessModelSave($response, true)->ReturnData();
	}
	
	/**
	 *	Удаление записи из журнала загрузки данных (с файлами если есть)
	 */
	function deleteReceptUploadLog() {
		$data = $this->ProcessInputData('deleteReceptUploadLog', true);
		if ($data === false) { return false; }
		
		$rrData = $this->dbmodel->getRegistryReceptOnReceptUploadLog($data);
		$rrpData = $this->dbmodel->getRegistryReceptPersonOnReceptUploadLog($data);
		if( $rrData === false || $rrpData === false ) {
			return $this->ReturnError('Ошибка БД!');
		}
		
		$this->dbmodel->beginTransaction();
		foreach( $rrData as $rr ) {
			$response = $this->dbmodel->deleteRegistryRecept(array_merge(array('pmUser_id' => $data['pmUser_id']), $rr));
			if( $response === false ) {
				$this->dbmodel->rollbackTransaction();
				return $this->ReturnError('Ошибка БД!');
			}
			if( !empty($response[0]['Error_Msg']) ) {
				$this->dbmodel->rollbackTransaction();
				return $this->ReturnError($response[0]['Error_Msg']);
			}
		}
		
		foreach( $rrpData as $rrp ) {
			$response = $this->dbmodel->deleteRegistryReceptPerson(array_merge(array('pmUser_id' => $data['pmUser_id']), $rrp));
			if( $response === false ) {
				$this->dbmodel->rollbackTransaction();
				return $this->ReturnError('Ошибка БД!');
			}
			if( !empty($response[0]['Error_Msg']) ) {
				$this->dbmodel->rollbackTransaction();
				return $this->ReturnError($response[0]['Error_Msg']);
			}
		}
		
		$file = $this->dbmodel->getFieldValue('ReceptUploadLog_InFail', $data['ReceptUploadLog_id']);
		if( $file === false ) return false;
		
		$response = $this->dbmodel->deleteReceptUploadLog($data);
		if( $response === false ) {
			$this->dbmodel->rollbackTransaction();
			return $this->ReturnError('Ошибка БД!');
		}
		if( !empty($response[0]['Error_Msg']) ) {
			$this->dbmodel->rollbackTransaction();
			return $this->ReturnError($response[0]['Error_Msg']);
		}
		
		if( !empty($file) && is_file($file) ) {
			unlink($file);
		}
		$this->dbmodel->commitTransaction();
		$this->ProcessModelSave($response, true)->ReturnData();
	}

	/**
	 * Получение массива соответствий.
	 */
	private function getFieldsAssoc($o) {
		return $this->mapFieldsAssoc[$o];
	}
	
	/**
	 *	Читает содержимое всех файлов архива и возвращает данные в виде ассоциативного массива
	 */
	private function readArchive($data, $archive, $forAct = false) {
		if( !$archive ) return false;
		$extensions = array('zip', 'rar'); // Поддерживаемые расширения
		
		$archive = pathinfo($archive);
		//print_r($archive); die();
		if( !in_array($archive['extension'], $extensions) ) {
			return array('Error_Msg' => 'Расширение '.$archive['extension'].' не поддерживается!');
		}
		
		// создадим временную директорию, в которую распакуем архив
		$__dir = $archive['dirname'].'/extracted_'.time() . "_" . $data['ReceptUploadLog_id'];
		if( !is_dir($__dir) ) {
			if( !mkdir($__dir) ) {
				return array('Error_Msg' => 'Не удалось создать директорию '.$__dir.'!');
			}
		}
		
		switch($archive['extension']) {
			case 'rar':
				$rf = rar_open($archive['dirname'].'/'.$archive['basename']);
				if( !$rf ) {
					rmdir($__dir);
					return array('Error_Msg' => 'Не удалось открыть архив '.$archive['basename'].'!');
				}
				$files = rar_list($rf);
				foreach($files as $file) {
					$file->extract($__dir);
				}
				rar_close($rf);
				break;
			case 'zip':
				$zip = new ZipArchive;		
				if( $zip->open($archive['dirname'].'/'.$archive['basename']) === true ) {
					$zip->extractTo($__dir);
					$zip->close();
				} else {
					rmdir($__dir);
					return array('Error_Msg' => 'Не удалось открыть архив '.$archive['basename'].'!');
				}
				break;
		}
		
		$outData = array();
		
		// проходим по всем файлам, читаем, заполняем массив данными и сразу удаляем файлы
		$i = 0;
		if( $h = opendir($__dir) ) {
			while(false !== ($file = readdir($h))) {
				$f = $__dir.'/'.$file;
				if( is_file($f) ) {
					$fData = pathinfo($f);
					if ($forAct) {
						$i++;
						$outData[] = array(
							'number' => $i,
							'filename' => $fData['basename'],
							'filesize' => filesize($f),
							'crc' => hash_file("crc32b", $f)
						);
					} else {
						switch($fData['extension']) {
							case 'dbf':
								$db = dbase_open($f, 0);
								if( $db ) {
									$outData[$file] = array();
									for($i=0; $i<=dbase_numrecords($db); $i++) {
										$outData[$file][$i] = dbase_get_record_with_names($db, $i);
										unset($outData[$file][$i]['deleted']);
									}
									array_shift($outData[$file]);
									dbase_close($db);
								}
								break;
						}
					}
					unlink($__dir.'/'.$file);
				}
			}
			closedir($h);
		}
		rmdir($__dir);
		return $outData;
	}
	
	/**
	 *	формирование акта
	 */
	function sendActAboutImport() {
		$this->load->library('parser');
		
		$data = $this->ProcessInputData('sendActAboutImport', true);
		if ($data === false) { return false; }
		
		$receptUploadLogData = $this->dbmodel->getReceptUploadLogData($data);
		if ($receptUploadLogData === false) {
			$this->ReturnError('Ошибка получения данных о типе загрузки');
			return false;		
		}

		// Если акт не сформирован – сформировать
		if ($receptUploadLogData['ReceptUploadStatus_Code'] == 6)
		{
			$this->ReturnError('Акт уже сформирован');
			return false;
		}
		
		// 1.	Проверить статус записи загрузки. Если статус «данные получены», то вывести сообщение «Экспертиза не проводилась»
		if ($receptUploadLogData['ReceptUploadStatus_Code'] == 1)
		{
			$this->ReturnError('Экспертиза не проводилась');
			return false;
		}
		
		if (empty($receptUploadLogData['ReceptUploadType_Code']) || !in_array($receptUploadLogData['ReceptUploadType_Code'], array(1,2,3))) {
			$this->ReturnError('Передача акта возможна только для типов данных "о поставке в АУ", "реестры рецептов" и "сводные реестры"');
			return false;
		}
		
		$printdata = $receptUploadLogData;
		
		switch($receptUploadLogData['ReceptUploadType_Code']) {
			case 1:
				// Читаем архив
				$printdata['files'] = $this->readArchive($data, $receptUploadLogData['ReceptUploadLog_InFail'], true);
				if (isset($printdata['files']['Error_Msg'])) {
					$printdata['files'] = array();
				}
				$printdata['errors'] = '';
				
				// Получить различные суммы для таблицы "Результаты экспертизы данных"
				$resultdata = $this->dbmodel->getInvoiceResultData($data);
				$printdata = array_merge($printdata, $resultdata);
				
				// Сформировать акт и протокол импорта и экспертизы данных загрузки
				// каталог в котором лежат выгружаемые файлы
				$out_dir = "recuplinvoice_" . time() . "_" . $data['ReceptUploadLog_id'];
				mkdir(EXPORTPATH_REGISTRY . $out_dir);
				
				$file_act = EXPORTPATH_REGISTRY . $out_dir . "/act.html";

				$html = $this->parser->parse('print_invoice_act', $printdata, true);
				
				if (!empty($resultdata['ReceptUploadLog_Act']) && file_exists($resultdata['ReceptUploadLog_Act']) ) {
					$html .= "<br><pre>".file_get_contents($resultdata['ReceptUploadLog_Act'])."</pre>";
				}
				
				file_put_contents($file_act, $html);
				$this->dbmodel->setScheme('dbo')->setObject('ReceptUploadLog')->setRow($data['ReceptUploadLog_id']);
				// html-ку записываем в файл и в поле ReceptUploadLog_Act
				$this->dbmodel->setValue('ReceptUploadLog_Act', $file_act);
				
				// Подготовить для Поставщика результаты экспертизы - данные накладных текущей загрузки, имеющиеся в таблице загрузки накладных нужно выгрузить в dbf-формате
				$file_zip = EXPORTPATH_REGISTRY . $out_dir . "/arch.zip";
				
				$zip=new ZipArchive();
				$zip->open($file_zip, ZIPARCHIVE::CREATE);
				
				// файл с Invoice'ами
				$add_ok = true;
				$resp = $this->dbmodel->getInvoiceData($data);
				$filename = 'DOK.dbf';
				$data_def = array(
					array("METHOD","C",7,0),
					array("DOC_CODE","N",10,0),
					array("DOC_N","C",18,0),
					array("DTYPE_CODE","N",10,0),
					array("DATE_DOC","D",8,0),
					array("C_FINL","N",5,0),
					array("GK","C",31,0),
					array("SUPL_COD","N",8,0),
					array("SUPL_OGRN","C",14,0),
					array("RECIP_COD","N",8,0),
					array("RECIP_OGRN","C",14,0),
					array("FACTUR","N",6,0),
					array("AKT","N",3,0),
					array("STATUS","C",20,0),
					array("CAUSE","C",200,0)
				);
				$dbf_full_name = EXPORTPATH_REGISTRY . $out_dir . '/' . $filename;
				
				$h = dbase_create($dbf_full_name, $data_def);
				if (!$h) {
					throw new Exception('dbase_create() fails ' . $dbf_full_name);
				}
				
				foreach($resp as $record) {
					$add_ok = $add_ok && dbase_add_record($h, array_values($record));
					if (!$add_ok) {
						throw new Exception('Ошибка добавления записи в DBF (' . implode(', ', $record) . ')');
					}
				}
				
				if (!dbase_close($h)) {
					throw new Exception('Не удалось сохранить изменения в ' . $dbf_full_name);
				}
				
				$zip->AddFile( $dbf_full_name, $filename );
				
				// файл с IvoiceDrug'ами
				$add_ok = true;
				$resp = $this->dbmodel->getInvoiceDrugData($data);
				$filename = 'DOK_SP.dbf';
				$data_def = array(
					array("DOC_CODE","N",13,0),
					array("NOMK_LS","N",8,0),
					array("KO_ALL","N",7,0),
					array("PRICE","N",10,0),
					array("SUM","N",10,0),
					array("NDS","N",4,0),
					array("SUM_NDS","N",11,0),
					array("SERIES","C",14,0),
					array("REGNUM","C",41,0),
					array("EAN","C",4,0),
					array("STATUS","C",20,0),
					array("CAUSE","C",200,0)
				);
				$dbf_full_name = EXPORTPATH_REGISTRY . $out_dir . '/' . $filename;
				
				$h = dbase_create($dbf_full_name, $data_def);
				if (!$h) {
					throw new Exception('dbase_create() fails ' . $dbf_full_name);
				}
				
				foreach($resp as $record) {
					$add_ok = $add_ok && dbase_add_record($h, array_values($record));
					if (!$add_ok) {
						throw new Exception('Ошибка добавления записи в DBF (' . implode(', ', $record) . ')');
					}
				}
				
				if (!dbase_close($h)) {
					throw new Exception('Не удалось сохранить изменения в ' . $dbf_full_name);
				}
				
				$zip->AddFile( $dbf_full_name, $filename );
				
				$zip->close();
				$this->dbmodel->setValue('ReceptUploadLog_OutFail', $file_zip);
			break;
			
			case 2:
			case 3:
				// Читаем архив
				$printdata['files'] = $this->readArchive($data, $receptUploadLogData['ReceptUploadLog_InFail'], true);
				if (isset($printdata['files']['Error_Msg'])) {
					$printdata['files'] = array();
				}

				// Читаем ошибки
				$printdata['errors'] = $this->dbmodel->getReceptUploadLogErrors($data);
				
				// Получение результатов
				$printdata['results'] = $this->dbmodel->getReceptUploadLogExpertResults($data);

				// Получение данных о сформировоанных реестрах на оплату
				$printdata['payment'] = $this->dbmodel->getReceptUploadLogPaymentData($data);
				if (count($printdata['payment']) > 0 && count($printdata['files']) > 0) {
					$printdata['payment'][0]['File_Name'] = $printdata['files'][0]['filename'];
					$printdata['payment'][0]['File_Size'] = $printdata['files'][0]['filesize'];
					$printdata['payment'][0]['File_CRC'] = $printdata['files'][0]['crc'];
				}
				
				// Сформировать акт и протокол импорта и экспертизы данных загрузки
				// каталог в котором лежат выгружаемые файлы
				$out_dir = "recupl_" . time() . "_" . $data['ReceptUploadLog_id'];
				mkdir(EXPORTPATH_REGISTRY . $out_dir);
				
				$file_act = EXPORTPATH_REGISTRY . $out_dir . "/act.html";

				$view_name = $receptUploadLogData['ReceptUploadType_Code'] == 2 ? 'print_receptupload_act' : 'print_receptuploadsvod_act';
				$html = $this->parser->parse($view_name, $printdata, true);
				file_put_contents($file_act, $html);
				
				$this->dbmodel->setScheme('dbo')->setObject('ReceptUploadLog')->setRow($data['ReceptUploadLog_id']);
				// html-ку записываем в файл и в поле ReceptUploadLog_Act
				$this->dbmodel->setValue('ReceptUploadLog_Act', $file_act);
				
				// создаём dbf-ку + засовываем в архив, записываем в поле ReceptUploadLog_InFail (исходный DBF + поля ReceptStatusFLKMEK_id + ReceptOtov_id + ReceptStatusType_Name 
				$file_zip = EXPORTPATH_REGISTRY . $out_dir . "/arch.zip";
				
				$zip=new ZipArchive();
				$zip->open($file_zip, ZIPARCHIVE::CREATE);
				
				set_time_limit(0);
				$archiveData = $this->readArchive($data, $receptUploadLogData['ReceptUploadLog_InFail']);
				$add_ok = true;
				foreach($archiveData as $k=>$row) {
					$firstSym = strtoupper(substr($k, 0, 1));
					
					$data_def = array();
					
					switch($firstSym) {
						case 'L':
							$data_def = array(
								array("SS", "C",14,0),
								array("OKATO_REG", "N",5,0),
								array("C_OGRN", "C",15,0),
								array("MCOD", "C",7,0),
								array("PCOD", "C",22,0),
								array("DS", "C",7,0),
								array("SN_LR", "C",20,0),
								array("DATE_VR", "D",8,0),
								array("C_FINL", "N",1,0),
								array("PR_LR", "N",3,0),
								array("A_COD", "C",21,0),
								array("NOMK_LS", "N",13,0),
								array("KO_ALL", "N",7,3),
								array("DOZ_ME", "N",5,0),
								array("C_PFS", "N",8,0),
								array("DATE_OBR", "D",8,0),
								array("DATE_OTP", "D",8,0),
								array("SL_ALL", "N",11,2),
								array("TYPE_SCHET", "N",1,0),
								array("FO_OGRN", "C",15,0),
								array("P_KEK", "N",1,0),
								array("D_TYPE", "C",3,0),
								array("RECIPEID", "C",20,0),
								array("GK_NUM", "C",50,0),
								array("ISDISCARD", "C",50,0),
								array("STATUS", "C",20,0),
								array("RECEPTOTOV", "C",20,0),
								array("STPROVIDE", "C",20,0)
							);
							
							foreach($row as &$record) {
								// получаем данные экспертизы и аттачим их к записи
								$params = array();
								$params['ReceptUploadLog_id'] = $data['ReceptUploadLog_id'];
								$params['RegistryRecept_ReceptId'] = $record['RECIPEID'];
								$expdata = $this->dbmodel->getRegistryReceptExpertData($params);
								if (!empty($expdata)) {
									$record['STATUS'] = $expdata['STATUS'];
									$record['RECEPTOTOV'] = $expdata['RECEPTOTOV'];
									$record['STPROVIDE'] = $expdata['STPROVIDE'];
								} else {
									$record['STATUS'] = '';
									$record['RECEPTOTOV'] = '';
									$record['STPROVIDE'] = '';						
								}
							}
							break;
						case 'P':
							$data_def = array(
								array("SS", "C",14,0),
								array("SN_POL", "C",25,0),
								array("FAM", "C",40,0),
								array("IM", "C",40,0),
								array("OT", "C",40,0),
								array("W", "C",1,0),
								array("DR", "C",10,0),
								array("C_KAT", "C",3,0),
								array("SN_DOC", "C",16,0),
								array("C_DOC", "N",2,0),
								array("OKATO_OMS", "N",5,0),
								array("QM_OGRN", "C",15,0),
								array("OKATO_REG", "N",5,0),
								array("D_TYPE", "C",3,0)
							);
							break;
					}
					
					if (!empty($data_def)) {
						$dbf_full_name = EXPORTPATH_REGISTRY . $out_dir . '/' . $k;
						
						$h = dbase_create($dbf_full_name, $data_def);
						if (!$h) {
							throw new Exception('dbase_create() fails ' . $dbf_full_name);
						}
						
						foreach($row as $record) {
							$add_ok = $add_ok && dbase_add_record($h, array_values($record));
							if (!$add_ok) {
								throw new Exception('Ошибка добавления записи в DBF (' . implode(', ', $record) . ')');
							}
						}
						
						if (!dbase_close($h)) {
							throw new Exception('Не удалось сохранить изменения в ' . $dbf_full_name);
						}
						
						$zip->AddFile( $dbf_full_name, $k );
					}
				}

				//Выгрузка списка ошибок по реестру рецептов
				$err_data = $this->dbmodel->getReceptErrorDataForDBF($data);

				if ($err_data) {
					$dbf_name = 'ERR.dbf';
					$dbf_full_name = EXPORTPATH_REGISTRY . $out_dir . '/' . $dbf_name;

					$data_def = array(
						array("SN_LR", "C",20,0),
						array("ERR_ID", "C",20,0),
						array("ERRTYPE_ID", "C",20,0),
						array("ERRTYPE_C", "C",20,0),
						array("ERRTYPE_N", "C",150,0),
						array("ERRTYPE_D", "C",150,0)
					);

					$h = dbase_create($dbf_full_name, $data_def);
					if (!$h) {
						throw new Exception('dbase_create() fails ' . $dbf_full_name);
					}

					foreach($err_data as $record) {
						$add_ok = $add_ok && dbase_add_record($h, array_values($record));
						if (!$add_ok) {
							throw new Exception('Ошибка добавления записи в DBF (' . implode(', ', $record) . ')');
						}
					}

					if (!dbase_close($h)) {
						throw new Exception('Не удалось сохранить изменения в ' . $dbf_full_name);
					}

					$zip->AddFile( $dbf_full_name, $dbf_name );
				}
				
				$zip->close();
				
				$this->dbmodel->setValue('ReceptUploadLog_OutFail', $file_zip);
			break;
		}
		// 3.	Записи в журнале загрузки присвоить статус «Акт передан»
		$this->dbmodel->setValue('ReceptUploadStatus_id', 6);
		
		$this->ReturnData(array('success' => true));
	}
	
	/**
	 *	импортирует данные архива в БД
	 */
	function importAndExpertise() {
		$this->load->model("Farmacy_model", "f_model");

		$data = $this->ProcessInputData('importAndExpertise', true);
		if ($data === false) { return false; }
		
		$receptUploadLogData = $this->dbmodel->getReceptUploadLogData($data);
		if ($receptUploadLogData === false) {
			$this->ReturnError('Ошибка получения данных о типе загрузки');
			return false;		
		}

		if (empty($receptUploadLogData['ReceptUploadType_Code']) || !in_array($receptUploadLogData['ReceptUploadType_Code'], array(1,2,3))) {
			$this->ReturnError('Импорт и экспертиза данных возможна только для типов данных "о поставке в АУ", "реестры рецептов" и "сводные реестры рецептов"');
			return false;
		}
		
		// Если статус записи журнала загрузки «Акт передан» (ReceptUploadStatus_Code = 6), то вывести сообщение «Акт уже передан, проведение экспертизы невозможно»
		if ($receptUploadLogData['ReceptUploadStatus_Code'] == 6)
		{
			$this->ReturnError('Акт уже передан, проведение экспертизы невозможно');
			return false;
		}

		// Запрет на повторную экспертизу сводных реестров рецептов
		if ($receptUploadLogData['ReceptUploadType_Code'] == 3 && $receptUploadLogData['ReceptUploadStatus_Code'] != 1)
		{
			$this->ReturnError('Повторная экспертиза сводных реестров рецептов недоступна');
			return false;
		}
		
		set_time_limit(0);
		$this->dbmodel->beginTransaction();
		
		// Если статус записи журнала загрузки «данные получены» - выполнить процедуру импорта
		if ($receptUploadLogData['ReceptUploadStatus_Code'] == 1)
		{
			// Читаем архив
			$archiveData = $this->readArchive($data, $receptUploadLogData['ReceptUploadLog_InFail']);
			// var_dump($archiveData); die();
			if( isset($archiveData['Error_Msg']) ) {
				return $this->ReturnError($archiveData['Error_Msg']);
			}
			
			switch($receptUploadLogData['ReceptUploadType_Code']) {
				case 1: //Накладные
					if (empty($archiveData)) {
						$this->dbmodel->rollbackTransaction();
						$this->dbmodel->setScheme('dbo')->setObject('ReceptUploadLog')->setRow($data['ReceptUploadLog_id']);
						$this->dbmodel->setValue('ReceptUploadStatus_id', 3);
						return $this->ReturnError('Не удалось произвести импорт данных накладных');
					}
					
					$dok_data = array();
					$dok_sp_data = array();
					
					foreach($archiveData as $k=>$row) {
						switch(strtoupper($k)) {
							case 'DOK.DBF':
								$dok_data = $row;
							break;
							
							case 'DOK_SP.DBF':
								$dok_sp_data = $row;
							break;
						}
					}
					
					unset($archiveData);
					
					if (empty($dok_data) || empty($dok_sp_data)) {
						$this->dbmodel->rollbackTransaction();
						// если другие файлы то выдаем сообщение об ошибке
						//$this->dbmodel->setScheme('dbo')->setObject('ReceptUploadLog')->setRow($data['ReceptUploadLog_id']);
						//$this->dbmodel->setValue('ReceptUploadStatus_id', 3);
						return $this->ReturnError('Не удалось произвести импорт данных накладных');
					}
					
					$object = "Invoice";
					$dParams = array(
						'Invoice_id' => null,
						'pmUser_id' => $data['pmUser_id'],
						'ReceptUploadLog_id' => $data['ReceptUploadLog_id'],
						'InvoiceStatus_id' => 1, // в обработке
						'InvoiceCause_id' => NULL
					);
					
					$assoc = $this->getFieldsAssoc($object);
					$method = "save".$object;

					if (count($dok_data) > 0 && count($assoc) != count($dok_data[0])) {
						$this->dbmodel->rollbackTransaction();
						return $this->ReturnError('Структура файла не соответствует установленным требованиям');
					}

					// массив сохраненных идентификаторов для связи с Invoice с InvoiceDrug
					$saved_idents = array();
					foreach($dok_data as &$r) {
						array_walk($r, 'ConvertFromWin866ToCp1251');
						$record = array_combine(array_values($assoc), array_values($r));
						$response = $this->dbmodel->$method(array_merge($record, $dParams));
						if( $response === false ) {
							$this->dbmodel->rollbackTransaction();
							return $this->ReturnError('Ошибка БД!');
						}
						if( !empty($response[0]['Error_Msg']) ) {
							$this->dbmodel->rollbackTransaction();
							return $this->ReturnError($response[0]['Error_Msg']);
						}
						if (!empty($response[0]['Invoice_id'])) {
							$saved_idents[$record['Invoice_DocC']] = $response[0]['Invoice_id'];
						}
					}
					
					unset($dok_data);
					
					$object = "InvoiceDrug";
					$dParams = array(
						'Invoice_id' => null,
						'InvoiceDrug_PriceNDS' => null,
						'InvoiceDrug_SertN' => null,
						'InvoiceDrug_SertD' => null,
						'InvoiceDrug_CMnn' => null,
						'InvoiceDrug_NameMnn' => null,
						'InvoiceDrug_Product' => null,
						'InvoiceDrug_Producer' => null,
						'InvoiceDrug_SrokS' => null,
						'InvoiceDrug_packNx' => null,
						'InvoiceDrug_id' => null,
						'pmUser_id' => $data['pmUser_id'],
						'ReceptUploadLog_id' => $data['ReceptUploadLog_id'],
						'InvoiceStatus_id' => 1, // в обработке
						'InvoiceCause_id' => NULL
					);

						
					$assoc = $this->getFieldsAssoc($object);
					$method = "save".$object;

					if (count($dok_sp_data) > 0 && count($assoc) != count($dok_sp_data[0])) {
						$this->dbmodel->rollbackTransaction();
						return $this->ReturnError('Структура файла не соответствует установленным требованиям');
					}
						
					foreach($dok_sp_data as &$r) {
						array_walk($r, 'ConvertFromWin866ToCp1251');
						$record = array_combine(array_values($assoc), array_values($r));
						if (!empty($saved_idents[$record['InvoiceDrug_DocC']])) {
							$dParams['Invoice_id'] = $saved_idents[$record['InvoiceDrug_DocC']];
							$response = $this->dbmodel->$method(array_merge($record, $dParams));
							if( $response === false ) {
								$this->dbmodel->rollbackTransaction();
								return $this->ReturnError('Ошибка БД!');
							}
							if( !empty($response[0]['Error_Msg']) ) {
								$this->dbmodel->rollbackTransaction();
								return $this->ReturnError($response[0]['Error_Msg']);
							}
						}
					}
				break;
				case 2:
				case 3:
					foreach($archiveData as $k=>$row) {
						$firstSym = strtoupper(substr($k, 0, 1));
						switch($firstSym) {
							case 'L':
								$object = "RegistryRecept";
								$dParams = array(
									'RegistryRecept_id' => null,
									'RegistryReceptType_id' => $receptUploadLogData['ReceptUploadType_Code'] == 2 ? 1 : 2, //RegistryReceptType_id: 1 - Реестры; 2 - Сводные реестры;
									'pmUser_id' => $data['pmUser_id'],
									'ReceptUploadLog_id' => $data['ReceptUploadLog_id'],
									'ReceptStatusFLKMEK_id' => 1 // в обработке
								);
								break;
							case 'P':
								$object = "RegistryReceptPerson";
								$dParams = array(
									'RegistryReceptPerson_id' => null,
									'RegistryReceptType_id' => $receptUploadLogData['ReceptUploadType_Code'] == 2 ? 1 : 2, //RegistryReceptType_id: 1 - Реестры; 2 - Сводные реестры;
									'pmUser_id' => $data['pmUser_id'],
									'ReceptUploadLog_id' => $data['ReceptUploadLog_id']
								);
								break;
						}
						
						if (empty($object)) {
							$this->dbmodel->rollbackTransaction();
							// если другие файлы то выдаем сообщение об ошибке
							//$this->dbmodel->setScheme('dbo')->setObject('ReceptUploadLog')->setRow($data['ReceptUploadLog_id']);
							//$this->dbmodel->setValue('ReceptUploadStatus_id', 3);
							return $this->ReturnError('Не удалось произвести импорт данных реестров рецептов');
						}
					
						$assoc = $this->getFieldsAssoc($object);
						$method = "save".$object;

						if (count($row) > 0 && count($assoc) != count($row[0])) {
							$this->dbmodel->rollbackTransaction();
							return $this->ReturnError('Структура файла не соответствует установленным требованиям');
						}

						foreach($row as &$r) {
							array_walk($r, 'ConvertFromWin866ToCp1251');
							$record = array_combine(array_values($assoc), array_values($r));

							//чистим даты от лишних пробелов
							if (isset($record['RegistryRecept_obrDate'])) {
								$record['RegistryRecept_obrDate'] = rtrim(ltrim($record['RegistryRecept_obrDate']));
								if (empty($record['RegistryRecept_obrDate'])) {
									$record['RegistryRecept_obrDate'] = null;
								}
							}
							if (isset($record['RegistryRecept_otpDate'])) {
								$record['RegistryRecept_otpDate'] = rtrim(ltrim($record['RegistryRecept_otpDate']));
								if (empty($record['RegistryRecept_otpDate'])) {
									$record['RegistryRecept_otpDate'] = null;
								}
							}

							$response = $this->dbmodel->$method(array_merge($record, $dParams));
							if( $response === false ) {
								$this->dbmodel->rollbackTransaction();
								return $this->ReturnError('Ошибка БД!');
							}
							if( !empty($response[0]['Error_Msg']) ) {
								$this->dbmodel->rollbackTransaction();
								return $this->ReturnError($response[0]['Error_Msg']);
							}
						}
					}
				break;
			}
		}

		// проведение экспертизы
		switch($receptUploadLogData['ReceptUploadType_Code']) {
			case 1: //Накладные
				// 1. Создаём файл для записи протокола/лога экспертизы
				$out_dir = "expertise_" . time() . "_" . $data['ReceptUploadLog_id'];
				mkdir(EXPORTPATH_REGISTRY . $out_dir);
				
				$file_prot = EXPORTPATH_REGISTRY . $out_dir . "/protocol.txt";
				$fprot = fopen($file_prot, 'w');
				
				// получаем накладные и все данные необходимые для экспертизы
				$importedInvoices = $this->dbmodel->getImportedInvoices($data);
				$preFLKcount = count($importedInvoices);

				// проведение ФЛК каждой записи
				$last_id = 0;
				$msg_count = 0;
				$invoice_cause = $this->dbmodel->getInvoiceCauseList();

				$inv_cnt = count($importedInvoices); //счетчик для определения конца массива
				$fail_k_arr = array(); //массив ключей ошибочных накладных
				$fail_invoice = false; //признак отказа по накладной

				foreach( $importedInvoices as $k=>&$record ) {
					$fail_k_arr[] = $k;

					foreach ($record as &$rc) {
						$rc = trim($rc);
					}
					
					$record = array_merge($record, array('pmUser_id' => $data['pmUser_id']));
					$flkResult = $this->dbmodel->execInvoiceFLK($record);

					// идентифицированным прописываем Drug_id и PrepSeries_id, Contragent_tid, Contragent_sid
					$this->dbmodel->updateInvoiceData($record, $last_id);

					if (is_array($flkResult)) {
						if( !empty($flkResult['Error_Msg']) ) {
							$this->dbmodel->rollbackTransaction();
							return $this->ReturnError($flkResult['Error_Msg']);
							break;
						} elseif( $record['success'] === false ) {
							$fail_invoice = true;
						}
						if (!empty($flkResult['logmessage']) || !empty($flkResult['InvoiceCause_id'])) {
							// разбить $logmessage на несколько строк
							$log = explode(' ', $flkResult['logmessage']);
							$logmessage = '';
							$logonemessage = '';
							foreach($log as $logone) {
								$logonemessage .= ' '.$logone;
								if (strlen($logonemessage) > 80) {
									$logmessage .= $logonemessage . "\r\n";
									$logonemessage = '';
								}
							}
							$logmessage .= $logonemessage;
							$flkResult['logmessage'] = $logmessage;
							
							// запись в лог $flkResult['logmessage']
							$msg = (++$msg_count).". ";
							$msg .= isset($invoice_cause[$flkResult['InvoiceCause_id']]) ? $invoice_cause[$flkResult['InvoiceCause_id']] : 'Ошибка';
							$msg .= "\r\n".str_repeat(' ', strlen($msg_count)+2);
							$msg .= $flkResult['logmessage'];
							$msg .= "\r\n";
							fwrite($fprot, $msg);
						}
					}

					if (--$inv_cnt == 0 || $record['Invoice_id'] != $importedInvoices[$k+1]['Invoice_id']) { //последняя запись в массиве или в текущей накладной
						//если отказ по накладной, чистим массив от записей с её медикаментами
						if ($fail_invoice) {
							foreach($fail_k_arr as $kk) {
								unset($importedInvoices[$kk]);
							}
						}

						//сбрасываем переменные
						$fail_invoice = false;
						$fail_k_arr = array();
					}
				}
				
				$postFLKcount = count($importedInvoices);

				$this->dbmodel->setScheme('dbo')->setObject('ReceptUploadLog')->setRow($data['ReceptUploadLog_id']);

				if( $postFLKcount == 0 ) {
					$this->dbmodel->setValue('ReceptUploadStatus_id', 3);
				}
				
				$preMEKcount = count($importedInvoices);
				$docUcCreated = 0;
				// проведение МЭК каждой записи (из оставшихся после ФЛК)
				$drug_array = array(); //список медикаментов
				$fail_id = 0; //ид последней ошибочной накладной
				$importedInvoices = array_values($importedInvoices); // переназначаем id-шники массива чтобы шли по порядку, иначе дальнейший код не работает.
				foreach($importedInvoices as $k=>&$record ) {
					foreach ($record as &$rc) {
						$rc = trim($rc);
					}

					$mekResult = $this->dbmodel->execInvoiceMEK($record);

					if (is_array($mekResult)) {
						if( !empty($mekResult['Error_Msg']) ) {
							$this->dbmodel->rollbackTransaction();
							return $this->ReturnError($mekResult['Error_Msg']);
							break;
						} else{
							if( $record['success'] === false ) {
								$fail_id = $record['Invoice_id']; //запоминаем ид накладной с ошибкой
								$drug_array = array();
								unset($importedInvoices[$k]);
							} else { //если проверки МЭК пройдены, готовим данные для документа учета
								$drug_array[] = $record['InvoiceDrug_id'];
								if ($k == $preMEKcount - 1 || $record['Invoice_id'] != $importedInvoices[$k+1]['Invoice_id']) {
									//если в накладной не замечено ошибок, создаем документ учета и производим пересчет остатков
									if ($record['Invoice_id'] != $fail_id) {
										$docResult = $this->createInvoiceDocumentUc($record, $drug_array);
										if (is_array($docResult)) {
											if(!empty($docResult['Error_Msg'])) {
												return $this->ReturnError($docResult['Error_Msg']);
												break;
											}
											if (!empty($docResult['DocumentUc_id'])) {
												$docUcCreated++;
												//сохраняем ид документа учета в накладной
												$this->dbmodel->updateInvoiceData(array(
													'Invoice_id' => $record['Invoice_id'],
													'DocumentUc_id' => $docResult['DocumentUc_id']
												), $last_id);
											}
										}
									}
									$drug_array = array();
								}
							}
						}
						if (!empty($mekResult['logmessage']) || !empty($mekResult['InvoiceCause_id'])) {
							// разбить $logmessage на несколько строк
							$log = explode(' ', $mekResult['logmessage']);
							$logmessage = '';
							$logonemessage = '';
							foreach($log as $logone) {
								$logonemessage .= ' '.$logone;
								if (strlen($logonemessage) > 80) {
									$logmessage .= $logonemessage . "\r\n";
									$logonemessage = '';
								}
							}
							$logmessage .= $logonemessage;
							$mekResult['logmessage'] = $logmessage;
							
							// запись в лог $mekResult['logmessage']
							$msg = (++$msg_count).". ";
							$msg .= isset($invoice_cause[$mekResult['InvoiceCause_id']]) ? $invoice_cause[$mekResult['InvoiceCause_id']] : 'Ошибка';
							$msg .= "\r\n".str_repeat(' ', strlen($msg_count)+2);
							$msg .= $mekResult['logmessage'];
							$msg .= "\r\n";
							fwrite($fprot, $msg);
						}
					}
				}
				$postMEKcount = count($importedInvoices);

				if( $docUcCreated > 0 && $postMEKcount > 0 && $preFLKcount > $postMEKcount ) {
					$this->dbmodel->setValue('ReceptUploadStatus_id', 5);
				} elseif( $docUcCreated > 0 && $postMEKcount > 0 && $preFLKcount == $postMEKcount ) {
					$this->dbmodel->setValue('ReceptUploadStatus_id', 4);
				} else {
					$this->dbmodel->setValue('ReceptUploadStatus_id', 3);
				}
				
				fclose($fprot);
				// записываем протокол в поле ReceptUploadLog_Act
				$this->dbmodel->setScheme('dbo')->setObject('ReceptUploadLog')->setRow($data['ReceptUploadLog_id']);
				$this->dbmodel->setValue('ReceptUploadLog_Act', $file_prot);
			break;
			
			case 2: //Реестры рецептов
				$importedRecepts = $this->dbmodel->getImportedRegistryRecepts($data);
				$allowed_methods = $this->dbmodel->getExpertiseAllowedMethods();
				$preFLKcount = count($importedRecepts);

				//print_r($importedRecepts); die();
				// проведение ФЛК каждой записи
				foreach( $importedRecepts as $k=>&$record ) {
					$record = array_merge($record, array('pmUser_id' => $data['pmUser_id']));
					$flkResult = $this->dbmodel->execFLK($record, $allowed_methods);
					if( is_array($flkResult) && !empty($flkResult['Error_Msg']) ) {
						$this->dbmodel->rollbackTransaction();
						return $this->ReturnError($flkResult['Error_Msg']);
						break;
					}
					if( $flkResult === true && $record['success'] === false ) {
						unset($importedRecepts[$k]);
					}
				}

				$postFLKcount = count($importedRecepts);

				//print_r($importedRecepts); die();

				// проведение МЭК каждой записи (из оставшихся после ФЛК)
				$preMEKcount = count($importedRecepts);
				foreach($importedRecepts as $k=>&$record) {
					if ($record['RegistryRecept_IsDiscard'] != 2) { // МЭК не производится для рецептов снятых с обслуживания
						$mekResult = $this->dbmodel->execMEK($record, $allowed_methods);
						if( is_array($mekResult) && !empty($mekResult['Error_Msg']) ) {
							$this->dbmodel->rollbackTransaction();
							return $this->ReturnError($mekResult['Error_Msg']);
						}
						if( $mekResult === true && $record['success'] === false ) {
							unset($importedRecepts[$k]);
						}
					}
				}
				$postMEKcount = count($importedRecepts);

				//в зависимости от результатов МЭК и ФЛК проставляем статус
				$this->dbmodel->setScheme('dbo')->setObject('ReceptUploadLog')->setRow($data['ReceptUploadLog_id']);
				if ($preFLKcount > 0) {
					if($postFLKcount == 0 || $postMEKcount == 0) {
						$this->dbmodel->setValue('ReceptUploadStatus_id', 3); //отказ: ошибки ФЛК/МЭК
					}

					//cохраняем документы учета
					foreach($importedRecepts as $k=>$record) {
						if ($record['RegistryRecept_IsDiscard'] != 2) { // Сохранение документов не производится для рецептов снятых с обслуживания
							$doc_id = 0;
							$err_msg = null;
							$res = $this->dbmodel->saveDocumentUc(array_merge($record, array('DrugDocumentStatus_id' => 2))); //2 - исполнен
							if( $res === false ) {
								$err_msg = 'Ошибка БД!';
							}
							if(is_array($res)) {
								if (!empty($res[0]['Error_Msg'])) {
									$err_msg = $res[0]['Error_Msg'];
								} else {
									$doc_id = $res[0]['DocumentUc_id'];
								}
							}
							if (!empty($err_msg)) {
								$this->dbmodel->rollbackTransaction();
								return $this->ReturnError($err_msg);
							}
							if ($doc_id > 0) {
								$sum = $record['RegistryRecept_Price'] > 0 && $record['RegistryRecept_DrugKolvo'] > 0 ? $record['RegistryRecept_Price']*$record['RegistryRecept_DrugKolvo'] : 0;
								$save_data = array(
									'DocumentUcStr_id' => null,
									'DocumentUcStr_oid' => null,
									'DocumentUc_id' => $doc_id,
									'Drug_id' => $record['Drug_id'],
									'DrugFinance_id' => $record['DrugFinance_id'],
									'DocumentUcStr_Price' => $record['RegistryRecept_Price'],
									'DocumentUcStr_PriceR' => $record['RegistryRecept_Price'],
									'DocumentUcStr_Count' => $record['RegistryRecept_DrugKolvo'],
									'DrugNds_id' => null,
									'DocumentUcStr_EdCount' => null,
									'DocumentUcStr_SumR' => $sum,
									'DocumentUcStr_Sum' => $sum,
									'DocumentUcStr_SumNds' => $sum,
									'DocumentUcStr_SumNdsR' => $sum,
									'DocumentUcStr_godnDate' => null,
									'DocumentUcStr_Ser' => null,
									'DocumentUcStr_NZU' => null,
									'DocumentUcStr_IsLab' => null,
									'DrugProducer_id' => null,
									'DrugLabResult_Name' => null,
									'DocumentUcStr_CertNum' => null,
									'PrepSeries_id' => null,
									'ReceptOtov_id' => $record['ReceptOtov_id'],
									'EvnRecept_id' => $record['EvnRecept_id'],
									'pmUser_id' => $record['pmUser_id']
								);
								$result = $this->f_model->saveDocumentUcStr($save_data);
								if (is_array($result) && !empty($result[0]['Error_Msg'])) {
									$this->dbmodel->rollbackTransaction();
									return $this->ReturnError($result[0]['Error_Msg']);
								}
								$result = $this->dbmodel->unsetFarmacyDrugOstat(array(
									'WhsDocumentUc_Num' => $record['RegistryRecept_SupplyNum'],
									'Contragent_Code' => $this->dbmodel->getCode($record['RegistryRecept_FarmacyACode'], 'con'),
									'DrugFinance_id' => $record['DrugFinance_id'],
									'WhsDocumentCostItemType_id' => $record['WhsDocumentCostItemType_id'],
									'Drug_id' => $record['Drug_id'],
									'Drug_Kolvo' => $record['RegistryRecept_DrugKolvo'],
									'pmUser_id' => $record['pmUser_id']
								));
								if (is_array($result) && !empty($result[0]['Error_Msg'])) {
									$this->dbmodel->rollbackTransaction();
									return $this->ReturnError($result[0]['Error_Msg']);
								}
							}
						}
					}

					if($postMEKcount == $preFLKcount) {
						$this->dbmodel->setValue('ReceptUploadStatus_id', 4); //приняты
					} elseif($postMEKcount > 0) {
						$this->dbmodel->setValue('ReceptUploadStatus_id', 5); //частично приняты
					}

					// Меняем статус для рецептов снятых с обслуживания
					foreach($importedRecepts as $k=>$record) {
						if ($record['RegistryRecept_IsDiscard'] == 2 && $record['EvnRecept_id'] > 0) {
							$this->dbmodel->setScheme('dbo')->setObject('EvnRecept')->setRow($record['EvnRecept_id']);
							$this->dbmodel->setValue('ReceptDelayType_id', 3); //отказ
						}
					}

					//$this->dbmodel->rollbackTransaction();
					//return $this->ReturnError("Окончание экспертизы РР.");
				}
			break;

			case 3: //Сводные реестры рецептов
				$importedRecepts = $this->dbmodel->getImportedRegistryRecepts($data);
				$payment_array = array();

				// проведение МЭК каждой записи (из оставшихся после ФЛК)
				foreach($importedRecepts as &$record) {
					$record = array_merge($record, array('pmUser_id' => $data['pmUser_id']));
					$mekResult = $this->dbmodel->execSvodMEK($record);

					//собираем данные для реестров на оплату
					if($record['DrugFinance_id'] > 0 && $record['WhsDocumentCostItemType_id'] > 0) {
						$fin_str = $record['DrugFinance_id'].'_'.$record['WhsDocumentCostItemType_id'];
						if (!in_array($fin_str, array_keys($payment_array))) {
							$payment_array[$fin_str] = array(
								'id' => 0,
								'DrugFinance_id' => $record['DrugFinance_id'],
								'WhsDocumentCostItemType_id' => $record['WhsDocumentCostItemType_id'],
								'Org_id' => $receptUploadLogData['Org_id'],
								'total_cnt' => 0,
								'total_sum' => 0,
								'to_payment_cnt' => 0,
								'to_payment_sum' => 0
							);
						}
						$payment_array[$fin_str]['total_cnt'] += 1;
						$payment_array[$fin_str]['total_sum'] += $record['RegistryRecept_DrugKolvo']*$record['RegistryRecept_Price'];
						if ($record['to_payment']) {
							$payment_array[$fin_str]['to_payment_cnt'] += 1;
							$payment_array[$fin_str]['to_payment_sum'] += $record['RegistryRecept_DrugKolvo']*$record['RegistryRecept_Price'];
						}
					}

					if( is_array($mekResult) && !empty($mekResult['Error_Msg']) ) {
						$this->dbmodel->rollbackTransaction();
						return $this->ReturnError($mekResult['Error_Msg']);
					}
				}

				//Чистим итоговый массив с реестрами на оплату от пустых значений
				$pa_cnt = count($payment_array);
				foreach($payment_array as $key=>$value) {
					if ($value['to_payment_cnt'] < 1) {
						unset($payment_array[$key]);
					}
				}

				$ReceptUploadStatus_id = 3; //отказ: ошибки ФЛК/МЭК

				//Формируем реестры на оплату
				if (count($payment_array) > 0) {
					$regResult = $this->createSvodRegistry($payment_array, $importedRecepts);
					if (is_array($regResult)) {
						if(!empty($regResult['Error_Msg'])) {
							return $this->ReturnError($regResult['Error_Msg']);
							break;
						}
					}

					$ReceptUploadStatus_id = 5; //частично приняты
					if (count($payment_array) == $pa_cnt) {
						$ReceptUploadStatus_id = 4; //приняты
					}
				}

				// сохранение статуса данных в журнале загрузки
				$this->dbmodel->setScheme('dbo')->setObject('ReceptUploadLog')->setRow($data['ReceptUploadLog_id']);
				$this->dbmodel->setValue('ReceptUploadStatus_id', $ReceptUploadStatus_id);
			break;
		}

		$this->dbmodel->commitTransaction();
		$this->ReturnData(array('success' => true));
	}

	/**
	 *	создание документов учета для накладных
	 */
	function createInvoiceDocumentUc($invoice, $drug_array) {
		$this->load->model("Farmacy_model", "f_model");

		$doc_id = 0;
		$res = array();

		//Создаем документ учета
		$date = !empty($invoice['Invoice_DateDoc']) ? join(array_reverse(preg_split('/[.]/',$invoice['Invoice_DateDoc'])),'-') : null;
		$save_data = array(
			'DocumentUc_Num' => $invoice['Invoice_DocN'],
			'DocumentUc_setDate' => $date,
			'DocumentUc_didDate' => $date,
			'DocumentUc_DogNum' => $invoice['Invoice_gk'],
			'DocumentUc_DogDate' => $invoice['WhsDocumentUc_Date'],
			'Lpu_id' => null,
			'Org_id' => $invoice['FarmacyOrg_id'], // id аптеки
			'SubAccountType_sid' => 1, // доступно
			'SubAccountType_tid' => 1, // доступно
			'Contragent_id' => $invoice['Contragent_tid'], //аптека
			'Contragent_sid' => $invoice['Contragent_sid'], //поставщик
			'Mol_sid' => null,
			'Contragent_tid' => $invoice['Contragent_tid'], //аптека
			'Mol_tid' => null,
			'DrugDocumentType_SysNick' => 'DokNak', //Приходная накладная
			'DrugFinance_id' => $invoice['DrugFinance_id'],
			'WhsDocumentCostItemType_id' => $invoice['WhsDocumentCostItemType_id'],
			'DrugDocumentStatus_id' => 2, //исполнен
			'pmUser_id' => $invoice['pmUser_id']
		);

		$result = $this->f_model->saveDocumentUc($save_data);
		if (is_array($result)) {
			if (!empty($result[0]['Error_Msg'])) {
				return array('Error_Msg' => $result[0]['Error_Msg']);
			} else {
				if (isset($result[0]['DocumentUc_id']) && $result[0]['DocumentUc_id'] > 0)
					$doc_id = $result[0]['DocumentUc_id'];
			}
		}

		if ($doc_id > 0) {
			//Получаем данные для спецификации документа учета
			$invoice_drug = $this->dbmodel->getInvoiceDocumentUcStrData(array('Invoice_id' => $invoice['Invoice_id']));

			//Cохраняем медикаменты в созданный документ
			foreach($invoice_drug as $drug) {
				if (in_array($drug['InvoiceDrug_id'], $drug_array)) {
					$save_data = array(
						'DocumentUcStr_id' => null,
						'DocumentUcStr_oid' => null,
						'DocumentUc_id' => $doc_id,
						'Drug_id' => $drug['Drug_id'],
						'DrugFinance_id' => $invoice['DrugFinance_id'],
						'DocumentUcStr_Price' => $drug['InvoiceDrug_Price'],
						'DocumentUcStr_PriceR' => $drug['InvoiceDrug_Price'],
						'DocumentUcStr_Count' => $drug['InvoiceDrug_KoAll'],
						'DrugNds_id' => $drug['DrugNds_id'],
						'DocumentUcStr_EdCount' => null,
						'DocumentUcStr_SumR' => $drug['InvoiceDrug_Sum'],
						'DocumentUcStr_Sum' => $drug['InvoiceDrug_Sum'],
						'DocumentUcStr_SumNds' => $drug['InvoiceDrug_Sum'],
						'DocumentUcStr_SumNdsR' => $drug['InvoiceDrug_Sum'],
						'DocumentUcStr_godnDate' => !empty($drug['InvoiceDrug_SrokS']) ? join(array_reverse(preg_split('/[.]/',$drug['InvoiceDrug_SrokS'])),'-') : null,
						'DocumentUcStr_Ser' => $drug['InvoiceDrug_Series'] != '-' ? $drug['InvoiceDrug_Series'] : null,
						'DocumentUcStr_NZU' => null,
						'DocumentUcStr_IsLab' => null,
						'DrugProducer_id' => null,
						'DrugLabResult_Name' => null,
						'DocumentUcStr_CertNum' => $drug['InvoiceDrug_SertN'],
						'PrepSeries_id' => $drug['PrepSeries_id'],
						'pmUser_id' => $invoice['pmUser_id']
					);
					$result = $this->f_model->saveDocumentUcStr($save_data);
					if (is_array($result) && !empty($result[0]['Error_Msg'])) {
						return array('Error_Msg' => $result[0]['Error_Msg']);
					}
				}
			}

			//Производим перерасчет отстаков для поставщика и аптеки
			$result = $this->dbmodel->recalculateInvoiceDrugOstat(array(
				'Invoice_id' => $invoice['Invoice_id'],
				'WhsDocumentSupply_id' => $invoice['WhsDocumentSupply_id'],
				'SupplierOrg_id' => $invoice['SupplierOrg_id'],
				'FarmacyOrg_id' => $invoice['FarmacyOrg_id'],
				'Contragent_tid' => $invoice['Contragent_tid'],
				'SupplyOrg_rid' => $invoice['SupplyOrg_rid'],
				'pmUser_id' => $invoice['pmUser_id']
			), $drug_array);
			if (is_array($result)) {
				if (!empty($result[0]['Error_Msg'])) {
					return array('Error_Msg' => $result[0]['Error_Msg']);
				}
			}

			$res['DocumentUc_id'] = $doc_id;
		}
		return $res;
	}

	/**
	 *	создание реестров на оплату
	 */
	function createSvodRegistry($registry_array, $recept_array) {
		$this->load->model("SvodRegistry_model", "SvodRegistry_model");

		//создаем реестры на оплату
		foreach ($registry_array as $key => $registry) {
			$this->SvodRegistry_model->setRegistry_id(0);//ид реестра
			$this->SvodRegistry_model->setRegistryType_id(3);//тип реестра; 3 - рецепты;
			$this->SvodRegistry_model->setRegistryStatus_id(1);//идентификатор статуса реестра; 1 - Сформированные;
			$this->SvodRegistry_model->setRegistry_Sum($registry['total_sum']);//Registry_Sum
			$this->SvodRegistry_model->setRegistry_SumPaid($registry['to_payment_sum']);//Registry_SumPaid
			$this->SvodRegistry_model->setRegistry_ErrorCount($registry['total_cnt'] - $registry['to_payment_cnt']);//количество ошибок в реестре
			$this->SvodRegistry_model->setRegistry_RecordCount($registry['total_cnt']);//количество записей
			$this->SvodRegistry_model->setRegistry_RecordPaidCount($registry['to_payment_cnt']);//Registry_RecordPaidCount
			$this->SvodRegistry_model->setRegistryCheckStatus_id($this->dbmodel->getRegistryCheckStatusByCode($registry['total_cnt'] > 0 && $registry['total_cnt'] == $registry['to_payment_cnt'] ? 4 : 5));//Статус проверки реестра; 4 - принят полностью; 5 - принят частично;
			$this->SvodRegistry_model->setDrugFinance_id($registry['DrugFinance_id']);//Источник финансирования
			$this->SvodRegistry_model->setWhsDocumentCostItemType_id($registry['WhsDocumentCostItemType_id']);//Статья расхода
			$this->SvodRegistry_model->setOrg_id($registry['Org_id']);//Организация

			$result = $this->SvodRegistry_model->save();
			if (is_array($result)) {
				if (!empty($result[0]['Error_Msg'])) {
					return array('Error_Msg' => $result[0]['Error_Msg']);
				} else {
					//сохраняем идентификатор созданого реестра в массив реестров
					if (isset($result[0]['Registry_id']) && $result[0]['Registry_id'] > 0)
						$registry_array[$key]['id'] = $result[0]['Registry_id'];
				}
			}
		}

		//сохраняем рецепты в реестрах на оплату
		$ok_status_id = $this->dbmodel->getReceptStatusFLKMEKIdByCode(3); //годен к оплате
		$err_status_id = $this->dbmodel->getReceptStatusFLKMEKIdByCode(4); //отказ в оплате

		foreach ($recept_array as $key => $recept) {
			if ($recept['DrugFinance_id'] > 0 && $recept['WhsDocumentCostItemType_id'] > 0) {
				$fin_str = $recept['DrugFinance_id'].'_'.$recept['WhsDocumentCostItemType_id'];
				$save_data = array(
					'RegistryDataRecept_id' => 0,
					'Registry_id' => $registry_array[$fin_str]['id'],
					'Lpu_OGRN' => $recept['RegistryRecept_LpuOGRN'],
					'RegistryDataRecept_Ser' => $recept['RegistryRecept_Ser'],
					'RegistryDataRecept_Num' => $recept['RegistryRecept_Num'],
					'RegistryDataRecept_DrugKolvo' => $recept['RegistryRecept_DrugKolvo'],
					'RegistryDataRecept_Price' => $recept['RegistryRecept_Price'],
					'RegistryDataRecept_setDT' => $recept['RegistryRecept_setDT'],
					'WhsDocumentSupply_id' => $recept['WhsDocumentSupply_id'],
					'RegistryType_id' => 3, //Рецепты
					'RegistryRecept_id' => $recept['RegistryRecept_id'],
					'RegistryRecept_pid' => $recept['OldRegistryRecept_id'],
					'ReceptStatusFLKMEK_id' => $recept['to_payment'] ? $ok_status_id : $err_status_id,
					'pmUser_id' => $recept['pmUser_id']
				);
				$result = $this->SvodRegistry_model->saveRegistryDataRecept($save_data);
				if (is_array($result)) {
					if (!empty($result[0]['Error_Msg'])) {
						return array('Error_Msg' => $result[0]['Error_Msg']);
					}
				}
			}
		}

		return true;
	}

	/**
	 * Импорт данных об аптеках
	 */
	function importOrgFaramacy($data) {
		$this->load->model('Org_model', 'Org_model');
		$this->load->model('Farmacy_model', 'Farmacy_model');
		$statistics = array(
			'record_cnt' => 0,
			'breaked' => 0,
			'saved' => 0,
			'updated' => 0
		);

		$import_field_list = array(
			'C_OGRN',
			'AU_NAMES',
			'AU_NAMEF',
			'FAM_GV',
			'IM_GV',
			'OT_GV',
			'FAM_BUX',
			'IM_BUX',
			'OT_BUX',
			'TEL',
			'E_MAIL',
			'A_COD',
			'ADRES',
			'LICENSE'
		);

		// Получение данных о загрузке
		$receptUploadLogData = $this->dbmodel->getReceptUploadLogData($data);
		if ($receptUploadLogData === false) {
			$this->ReturnError('Ошибка получения данных о типе загрузки');
			return false;
		}

		$archiveData = @$this->readArchive($data, $receptUploadLogData['ReceptUploadLog_InFail']);

		foreach($archiveData as $file_data) { //перебор файлов
			// Проверка наличия необходимых полей в файле
			if(count($file_data) > 0) {
				$correct_file = true;
				foreach($import_field_list as $field) {
					if (!isset($file_data[0][$field])) {
						$correct_file = false;
						break;
					}
				}
				if (!$correct_file) {
					$statistics['record_cnt'] += count($file_data);
					$statistics['breaked'] += count($file_data);
					continue;
				}
			}

			foreach($file_data as $org_data) { //перебор организаций
				$statistics['record_cnt']++;
				array_walk($org_data, 'ConvertFromWin866ToCp1251');

				// Проверка заполнения необходимых полей
				if (empty($org_data['LICENSE']) || empty($org_data['C_OGRN'])) {
					$statistics['breaked']++;
					continue;
				}

				//ищем среди существующих по номеру лицензии
				$org_is_found = false;
				$org_data['Org_id'] = 0;
				$response = $this->Org_model->getOrgByLicenceRegNum($org_data['LICENSE']);
				if (is_array($response) && count($response) > 0) {
					$org_data['Org_id'] = $response[0]['Org_id'];
					$org_is_found = true;
				}

				//сохранение или апдейт организации
				$params = array();

				//параметры организации
				$params['Org_id'] = $org_data['Org_id'];
				$params['Org_OGRN'] = ltrim(rtrim($org_data['C_OGRN']));
				$params['Org_Nick'] = ltrim(rtrim($org_data['AU_NAMES']));
				$params['Org_Name'] = ltrim(rtrim($org_data['AU_NAMEF']));
				$params['Org_Rukovod'] = !empty($org_data['FAM_GV']) ? ltrim(rtrim($org_data['FAM_GV'])) : "";
				$params['Org_Rukovod'] .= !empty($org_data['IM_GV']) ? " ".ltrim(rtrim($org_data['IM_GV'])) : "";
				$params['Org_Rukovod'] .= !empty($org_data['OT_GV']) ? " ".ltrim(rtrim($org_data['OT_GV'])) : "";
				$params['Org_Buhgalt'] = !empty($org_data['FAM_BUX']) ? ltrim(rtrim($org_data['FAM_BUX'])) : "";
				$params['Org_Buhgalt'] .= !empty($org_data['IM_BUX']) ? " ".ltrim(rtrim($org_data['IM_BUX'])) : "";
				$params['Org_Buhgalt'] .= !empty($org_data['OT_BUX']) ? " ".ltrim(rtrim($org_data['OT_BUX'])) : "";
				$params['Org_Phone'] = ltrim(rtrim($org_data['TEL']));
				$params['Org_Email'] = ltrim(rtrim($org_data['E_MAIL']));
				$params['OrgType_id'] = 4; //Аптека
				$params['Org_Code'] = $this->dbmodel->getNextCode('dbo.Org', 'Org_Code');
				$params['Server_id'] = $data['Server_id'] > 0 ? $data['Server_id'] : 1;
				$params['pmUser_id'] = $data['pmUser_id'];

				//очистка пустых значений
				foreach($params as $key=>$value) {
					if (empty($value)) {
						unset($params[$key]);
					}
				}

				$response = $this->dbmodel->saveObjectData('Org', $params);
				if (is_array($response) && count($response) > 0 && isset($response[0]['Org_id'])) {
					$org_data['Org_id'] = $response[0]['Org_id'];
				}

				if ($org_data['Org_id'] > 0) {
					//увеличиваем счетчик в статистике
					$statistics[$org_is_found ? 'updated' : 'saved']++;

					$data['OrgFarmacy_id'] = null;
					$response = $this->Farmacy_model->getOrgFarmacyByOrgId($org_data['Org_id']);
					if (is_array($response) && count($response) > 0) {
						$data['OrgFarmacy_id'] =  $response[0]['OrgFarmacy_id'];
					}

					//параметры аптеки
					$this->dbmodel->saveObjectData('OrgFarmacy', array(
						'OrgFarmacy_id' => $data['OrgFarmacy_id'],
						'Org_id' => $org_data['Org_id'],
						'OrgFarmacy_ACode' => $org_data['A_COD'] > 0 ? $org_data['A_COD'] : $this->dbmodel->getNextCode('dbo.OrgFarmacy', 'OrgFarmacy_ACode'),
						'OrgFarmacy_HowGo' => $org_data['ADRES'],
						'pmUser_id' => $data['pmUser_id']
					));

					//настраиваемся на текущую организацию
					$this->dbmodel->setScheme('dbo')->setObject('Org')->setRow($org_data['Org_id']);

					//редактируем адрес
					if (!empty($org_data['ADRES'])) {
						$response = $this->dbmodel->saveObjectData('Address', array(
							'Address_id' => $this->dbmodel->getValue('PAddress_id'),
							'Address_Address' => $org_data['ADRES'],
							'Server_id' => $data['Server_id'] > 0 ? $data['Server_id'] : 1,
							'pmUser_id' => $data['pmUser_id']
						));
						if (is_array($response) && count($response) > 0 && $response[0]['Address_id'] > 0) {
							//сохраняем адрес для редактируемой ораганизации
							$this->dbmodel->setValue('PAddress_id', $response[0]['Address_id']);
						}
					}

					if (!$org_is_found) {
						//сохраняем данные лицензии
						$this->dbmodel->saveObjectData('OrgLicence', array(
							'Org_id' => $org_data['Org_id'],
							'OrgLicence_RegNum' => $org_data['LICENSE'],
							'Server_id' => $data['Server_id'] > 0 ? $data['Server_id'] : 1,
							'pmUser_id' => $data['pmUser_id']
						));
					}
				} else {
					$statistics['breaked']++;
				}
			}
		}

		if( isset($archiveData['Error_Msg']) ) {
			return $this->ReturnError($archiveData['Error_Msg']);
		}
		return $statistics;
	}
}
