<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Тарифы и объёмы
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2015 Swan Ltd.
 * @author       DimICE
 * @version
 * @property TariffVolumes_model TariffVolumes_model
 */

class TariffVolumes_model extends swPgModel
{
	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Импорт объёмов по КСГ
	 */
	function importKSGVolumes($data) {
		// открываем excel файл
		set_time_limit(0); // может выполняться весьма долго...

		$upload_path = './'.IMPORTPATH_ROOT.'importKSGData/';
		$allowed_types = explode('|','xlsx|xlsm');

		if (!isset($_FILES['ImportFile'])) {
			return array('Error_Msg' => 'Нет файла для импорта');
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
			return array('Error_Msg' => $message);
		}

		// Тип файла разрешен к загрузке?
		$x = explode('.', $_FILES['ImportFile']['name']);
		$file_data['file_ext'] = end($x);
		if (!in_array(strtolower($file_data['file_ext']), $allowed_types)) {
			return array('Error_Msg' => 'Данный тип файла не разрешен.');
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
			return array('Error_Msg' => 'Путь для загрузки файлов некорректен.');
		}

		// Имеет ли директория для загрузки права на запись?
		if (!is_writable($upload_path)) {
			return array('Error_Msg' => 'Загрузка файла не возможна из-за прав пользователя.');
		}

		$filename = 'file_'.time().'.xlsm';
		if (!move_uploaded_file($_FILES["ImportFile"]["tmp_name"], $upload_path.$filename)){
			return array('Error_Msg' => 'Не удаётся переместить файл.');
		}

		$AttributeVision_TablePKey = $this->getFirstResultFromQuery("select VolumeType_id as \"VolumeType_id\" from v_VolumeType where VolumeType_Code = '2016-03КСГ' limit 1");
		if (empty($AttributeVision_TablePKey)) {
			return array('Error_Msg' => 'Не найден объём 2016-03КСГ');
		}

		$fields = array();
		$columns = $this->getColumnsOnTable(array('AttributeVision_TableName' => 'dbo.VolumeType', 'AttributeVision_TablePKey' => $AttributeVision_TablePKey));
		foreach($columns as $column) {
			if ($column['AttributeVision_IsKeyValue'] == 2) {
				$fields['value'] = $column['name'];
			} else if ($column['Attribute_SysNick'] == 'prim') {
				$fields['prim'] = $column['name'];
			} else {
				$fields[$column['Attribute_TableName']] = $column['name'];
			}
		}

		$xlsFile = $upload_path.$filename;
		$logFile = $upload_path.$filename.'.txt';
		file_put_contents($logFile, "Начинаем импорт" . PHP_EOL, FILE_APPEND);

		require_once('vendor/autoload.php');

		ini_set("memory_limit", "1024M");

		// 1. Читаем названия вкладок, чтобы потом идти по ним циклам и читать только по 1 вкладке, чтобы памяти меньше ело.
		$worksheetNames = array();
		try {
			$inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($xlsFile);
			$objectReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
			$worksheetNames = $objectReader->listWorksheetNames($xlsFile);
		} catch(Exception $e) {
			return array('Error_Msg' => 'Ошибка чтения файла');
		}

		// получаем список всех МО, чтобы не выполнять каждый раз запрос к БД
		$Lpus = array();
		$resp_lpu = $this->queryResult("
			select
				Lpu_id as \"Lpu_id\"
				Lpu_Nick as \"Lpu_Nick\"
				Lpu_f003mcod as \"Lpu_f003mcod\"
			from v_Lpu where Lpu_f003mcod is not null
		");
		foreach($resp_lpu as $one_lpu) {
			if (!empty($one_lpu['Lpu_f003mcod'])) {
				$Lpus[$one_lpu['Lpu_f003mcod']] = $one_lpu;
			}
		}

		// получаем список всех КСГ, чтобы не выполнять каждый раз запрос к БД
		$MesOlds = array();
		$resp_mes = $this->queryResult("
			select
				Mes_id as \"Mes_id\",
				Mes_Code as \"Mes_Code\"
			from
				v_MesOld
			where
				MesType_id = :MesType_id
				and coalesce(Mes_begDT, :StartDate) <= :StartDate
				and coalesce(Mes_endDT, :StartDate) >= :StartDate
		", array(
			'MesType_id' => $data['MesType_id'],
			'StartDate' => $data['StartDate']
		));
		foreach($resp_mes as $one_mes) {
			if (!empty($one_mes['Mes_Code'])) {
				$MesOlds[$one_mes['Mes_Code']] = $one_mes;
			}
		}

		// 2. Идём циклом по вкладкам и читаем каждую в отдельности
		foreach($worksheetNames as $oneSheet) {
			try {
				$objectReader->setReadDataOnly(true);
				$objectReader->setLoadSheetsOnly(array($oneSheet));
				$objPHPExcel = $objectReader->load($xlsFile);
			} catch (Exception $e) {
				return array('Error_Msg' => 'Ошибка чтения файла');
			}

			file_put_contents($logFile, "Перешли на страницу {$oneSheet}" . PHP_EOL, FILE_APPEND);

			// идём циклом по страницам
			$sheetCount = $objPHPExcel->getSheetCount();
			for ($i = 0; $i < $sheetCount; $i++) {
				$objWorksheet = $objPHPExcel->setActiveSheetIndex($i);

				$highestColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($objWorksheet->getHighestDataColumn());
				$columnToLoad = 0;
				// ищем номер МО в диапазоне B5-B20 (в A5-A20 должен быть знак №)
				$Lpu_f003mcod = null;
				for ($j = 5; $j <= 20; $j++) {
					$label = $objWorksheet->getCell("A" . $j)->getValue();
					if (trim($label) == '№') {
						$number = $objWorksheet->getCell("B" . $j)->getValue();
						if (!empty($number)) {
							$Lpu_f003mcod = str_replace(' ', '', $number);
						}
					}

					if (trim($label) == '№ п/п') {
						// идём по колонкам, с целью найти последний столбец (столбец "ЗАГРУЗКА")
						for ($h = 0; $h <= $highestColumn; $h++) {
							$label_two = $objWorksheet->getCellByColumnAndRow($h, $j)->getValue();
							if (trim($label_two) == 'ЗАГРУЗКА') {
								$columnToLoad = $h;
								break;
							}
						}
						break;
					}
				}

				if ($columnToLoad == 0) {
					file_put_contents($logFile, "Не найден столбец 'ЗАГРУЗКА', пропускаем страницу..." . PHP_EOL, FILE_APPEND);
					continue;
				}

				if (empty($Lpu_f003mcod)) {
					// если не указан реестровый номер МО, то пропускаем страницу
					file_put_contents($logFile, "Не указан реестровый номер МО, пропускаем страницу..." . PHP_EOL, FILE_APPEND);
					continue;
				}

				// определяем МО по реестровому номеру
				$Lpu_id = null;
				$Lpu_Nick = null;
				if (!empty($Lpus[$Lpu_f003mcod])) {
					$Lpu_id = $Lpus[$Lpu_f003mcod]['Lpu_id'];
					$Lpu_Nick = $Lpus[$Lpu_f003mcod]['Lpu_Nick'];
				} else if (!empty($Lpus['0'.$Lpu_f003mcod])) {
					$Lpu_id = $Lpus['0'.$Lpu_f003mcod]['Lpu_id'];
					$Lpu_Nick = $Lpus['0'.$Lpu_f003mcod]['Lpu_Nick'];
				}

				if (empty($Lpu_id)) {
					// не нашли такую МО в БД
					file_put_contents($logFile, "Не найдена МО с кодом {$Lpu_f003mcod}" . PHP_EOL, FILE_APPEND);
					continue;
				}

				// идём по строкам
				$highestRow = $objWorksheet->getHighestRow();
				for ($j = 0; $j <= $highestRow; $j++) {
					$cellA = $objWorksheet->getCell("A" . $j);
					$cellB = $objWorksheet->getCell("B" . $j);
					$Mes_Code = $cellA->getValue();
					if (empty($Mes_Code) || !is_numeric($Mes_Code)) {
						// если путсая или не число, то пропускаем строку
						continue;
					}

					$changeValue = $objWorksheet->getCellByColumnAndRow($columnToLoad - 1, $j)->getValue();
					if (!is_numeric($changeValue)) {
						$changeValue = $objWorksheet->getCellByColumnAndRow($columnToLoad - 1, $j)->getOldCalculatedValue();
					}
					$value = $objWorksheet->getCellByColumnAndRow($columnToLoad, $j)->getValue();
					if (!is_numeric($value)) {
						$value = $objWorksheet->getCellByColumnAndRow($columnToLoad, $j)->getOldCalculatedValue();
					}

					if (!is_numeric($value)) {
						continue;
					}

					if (!empty($changeValue)) {
						// определяем Mes_id по коду, типу и дате
						$Mes_id = null;
						if (!empty($MesOlds[$Mes_Code])) {
							$Mes_id = $MesOlds[$Mes_Code]['Mes_id'];
						}

						if (empty($Mes_id)) {
							// не нашли такую КСГ
							file_put_contents($logFile, "Не найдена действующая на дату {$data['StartDate']} КСГ с кодом {$Mes_Code}" . PHP_EOL, FILE_APPEND);
							continue;
						}

						// произошли изменения, ищем старый объём
						$resp_vol = $this->queryResult("						
							SELECT 
								av.AttributeValue_id as \"AttributeValue_id\",
								av.AttributeValue_ValueFloat as \"value\",
								to_char(av.AttributeValue_begDate, 'yyyy-mm-dd') as \"AttributeValue_begDate\",
								to_char(av.AttributeValue_endDate, 'yyyy-mm-dd') as \"AttributeValue_endDate\",
								descr.AttributeValue_ValueString as \"descr\"
							FROM
								v_AttributeVision avis
								inner join v_AttributeValue av on av.AttributeVision_id = avis.AttributeVision_id
								inner join v_Attribute a on a.Attribute_id = av.Attribute_id
								inner join lateral(
									select 
										av2.AttributeValue_ValueIdent
									from
										v_AttributeValue av2
										inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
									where
										av2.AttributeValue_rid = av.AttributeValue_id
										and a2.Attribute_TableName = 'dbo.Lpu'
										and av2.AttributeValue_ValueIdent = :Lpu_id
									limit 1
								) MOFILTER on true
								inner join lateral(
									select 
										av2.AttributeValue_ValueIdent
									from
										v_AttributeValue av2
										inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
									where
										av2.AttributeValue_rid = av.AttributeValue_id
										and a2.Attribute_TableName = 'dbo.MesOld'
										and av2.AttributeValue_ValueIdent = :Mes_id
									limit 1
								) MFILTER on true
								left join lateral (
									select 
										av2.AttributeValue_ValueString
									from
										v_AttributeValue av2
										inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
									where
										av2.AttributeValue_rid = av.AttributeValue_id
										and a2.Attribute_SysNick = 'prim'
									limit 1
								) descr on true
							WHERE
								avis.AttributeVision_TableName = 'dbo.VolumeType'
								-- and av.AttributeValue_ValueIdent = 21
								and avis.AttributeVision_TablePKey = :AttributeVision_TablePKey
								and avis.AttributeVision_IsKeyValue = 2
								and coalesce(av.AttributeValue_begDate, :StartDate) <= :StartDate
								and coalesce(av.AttributeValue_endDate, :StartDate) >= :StartDate
							limit 1
						", array(
							'Lpu_id' => $Lpu_id,
							'Mes_id' => $Mes_id,
							'AttributeVision_TablePKey' => $AttributeVision_TablePKey,
							'StartDate' => $data['StartDate']
						));

						if (!empty($resp_vol[0]['AttributeValue_id']) && $value == $resp_vol[0]['value']) {
							// значения совпадают, пропускаем
							file_put_contents($logFile, "дубль|{$Lpu_f003mcod}|{$Lpu_Nick}|{$Mes_Code}|{$resp_vol[0]['value']}|{$resp_vol[0]['descr']}|{$resp_vol[0]['AttributeValue_begDate']}|{$resp_vol[0]['AttributeValue_endDate']}|Успешно" . PHP_EOL, FILE_APPEND);
							continue;
						}

						$needNew = true;
						if (!empty($resp_vol[0]['AttributeValue_id'])) {
							if ($resp_vol[0]['AttributeValue_begDate'] != $data['StartDate']) { // если дата не равна текущей, то закрываем
								// закрываем старый объём
								$endDate = date('Y-m-d', strtotime($data['StartDate'] . "-1 days"));
								$this->db->query("
									update AttributeValue
									set AttributeValue_endDate = :AttributeValue_endDate,
									AttributeValue_updDT = GETDATE(),
									pmUser_updID = :pmUser_updID
									where AttributeValue_id = :AttributeValue_id;
									
									update AttributeValue
									set AttributeValue_endDate = :AttributeValue_endDate,
									AttributeValue_updDT = GETDATE(),
									pmUser_updID = :pmUser_updID
									where AttributeValue_rid = :AttributeValue_id;
								", array(
									'AttributeValue_id' => $resp_vol[0]['AttributeValue_id'],
									'AttributeValue_endDate' => $endDate,
									'pmUser_updID' => $data['pmUser_id']
								));
								file_put_contents($logFile, "закрытие|{$Lpu_f003mcod}|{$Lpu_Nick}|{$Mes_Code}|{$resp_vol[0]['value']}|{$resp_vol[0]['descr']}|{$resp_vol[0]['AttributeValue_begDate']}|{$endDate}|Успешно" . PHP_EOL, FILE_APPEND);
							} else { // иначе обновляем существующий объём
								$needNew = false;
								if (!empty($value)) {
									// обновляем старый объём
									$this->db->query("update AttributeValue set AttributeValue_ValueFloat = :AttributeValue_ValueFloat, AttributeValue_updDT = GETDATE(), pmUser_updID = :pmUser_updID where AttributeValue_id = :AttributeValue_id", array(
										'AttributeValue_id' => $resp_vol[0]['AttributeValue_id'],
										'AttributeValue_ValueFloat' => $value,
										'pmUser_updID' => $data['pmUser_id']
									));
									file_put_contents($logFile, "обновление|{$Lpu_f003mcod}|{$Lpu_Nick}|{$Mes_Code}|{$value}|{$resp_vol[0]['descr']}|{$resp_vol[0]['AttributeValue_begDate']}|{$resp_vol[0]['AttributeValue_endDate']}|Успешно" . PHP_EOL, FILE_APPEND);
								} else {
									// ошибка
									file_put_contents($logFile, "обновление|{$Lpu_f003mcod}|{$Lpu_Nick}|{$Mes_Code}|{$resp_vol[0]['value']}|{$resp_vol[0]['descr']}|{$resp_vol[0]['AttributeValue_begDate']}|{$resp_vol[0]['AttributeValue_endDate']}|Ошибка, существует откытый объём с такой же датой" . PHP_EOL, FILE_APPEND);
								}
							}
						}

						if ($needNew && !empty($value)) {
							// создаём новый объём
							$this->saveAttributeValue(array(
								'AttributeVision_TableName' => 'dbo.VolumeType',
								'AttributeVision_TablePKey' => $AttributeVision_TablePKey,
								'AttributeValue_begDate' => $data['StartDate'],
								'AttributeValue_endDate' => null,
								'pmUser_id' => $data['pmUser_id']
							), $columns, array(
								$fields['dbo.Lpu'] => $Lpu_id,
								$fields['dbo.MesType'] => $data['MesType_id'],
								$fields['dbo.MesOld'] => $Mes_id,
								$fields['value'] => $value,
								$fields['prim'] => $data['Descr']
							));

							file_put_contents($logFile, "открытие|{$Lpu_f003mcod}|{$Lpu_Nick}|{$Mes_Code}|{$value}|{$data['Descr']}|{$data['StartDate']}|null|Успешно" . PHP_EOL, FILE_APPEND);
						}
					}
				}

				unset($objWorksheet);
			}

			$objPHPExcel->disconnectWorksheets();
			$objPHPExcel->__destruct();
			unset($objPHPExcel);
		}

		file_put_contents($logFile, "Импорт успешно завершён" . PHP_EOL, FILE_APPEND);

		return array('Error_Msg' => '', 'log_link' => $logFile);
	}

	/**
	 * Загрузка списка типов тарифов
	 */
	function loadTariffClassGrid($data)
	{
		$filter = "";
		if (!empty($data['TariffClass_begDate_From'])) {
			$filter .= " and TariffClass_begDT >= :TariffClass_begDate_From";
			$params['TariffClass_begDate_From'] = $data['TariffClass_begDate_From'];
		}

		if (!empty($data['TariffClass_begDate_To'])) {
			$filter .= " and TariffClass_begDT <= :TariffClass_begDate_To";
			$params['TariffClass_begDate_To'] = $data['TariffClass_begDate_To'];
		}

		if (!empty($data['TariffClass_endDate_From'])) {
			$filter .= " and TariffClass_endDT >= :TariffClass_endDate_From";
			$params['TariffClass_endDate_From'] = $data['TariffClass_endDate_From'];
		}

		if (!empty($data['TariffClass_endDate_To'])) {
			$filter .= " and TariffClass_endDT <= :TariffClass_endDate_To";
			$params['TariffClass_endDate_To'] = $data['TariffClass_endDate_To'];
		}

		if (!empty($data['TariffClass_Code'])) {
			$filter .= " and TariffClass_Code like '%'||:TariffClass_Code||'%'";
			$params['TariffClass_Code'] = $data['TariffClass_Code'];
		}

		if (!empty($data['TariffClass_noKeyValue']) && $data['TariffClass_noKeyValue'] == 'true') {
			$filter .= " and not exists (
				select
					AttributeVision_id
				from v_AttributeVision
				where AttributeVision_TableName = 'dbo.TariffClass'
					and AttributeVision_IsKeyValue = 2
					and TariffClass_id = AttributeVision_TablePKey
				limit 1)";
		}

		if (!empty($data['isClose'])) {
			if ($data['isClose'] == 2) {
				$filter .= " and TariffClass_endDT <= dbo.tzGetDate()";
			} else {
				$filter .= " and coalesce(TariffClass_endDT, dbo.tzGetDate()) >= dbo.tzGetDate()";
			}
		}

		$query = "
			select
				-- select
				TariffClass_id as \"TariffClass_id\",
				TariffClass_Code as \"TariffClass_Code\",
				TariffClass_Name as \"TariffClass_Name\",
				to_char(TariffClass_begDT, 'dd.mm.yyyy') as \"TariffClass_begDT\",
				to_char(TariffClass_endDT, 'dd.mm.yyyy') as \"TariffClass_endDT\"
				-- end select
			from
				-- from
				v_TariffClass
				-- end from
			where
				-- where
				1=1
				{$filter}
				-- end where
			order by
				-- order by
				TariffClass_id
				-- end order by
		";

		return $this->getPagingResponse($query, $data, $data['start'], $data['limit'], true);
	}

	/**
	 * Загрузка списка типов объемов
	 */
	function loadVolumeTypeGrid($data)
	{
		$filter = "";
		if (!empty($data['VolumeType_begDate_From'])) {
			$filter .= " and VolumeType_begDate >= :VolumeType_begDate_From";
			$params['VolumeType_begDate_From'] = $data['VolumeType_begDate_From'];
		}

		if (!empty($data['VolumeType_begDate_To'])) {
			$filter .= " and VolumeType_begDate <= :VolumeType_begDate_To";
			$params['VolumeType_begDate_To'] = $data['VolumeType_begDate_To'];
		}

		if (!empty($data['VolumeType_endDate_From'])) {
			$filter .= " and VolumeType_endDate >= :VolumeType_endDate_From";
			$params['VolumeType_endDate_From'] = $data['VolumeType_endDate_From'];
		}

		if (!empty($data['VolumeType_endDate_To'])) {
			$filter .= " and VolumeType_endDate <= :VolumeType_endDate_To";
			$params['VolumeType_endDate_To'] = $data['VolumeType_endDate_To'];
		}

		if (!empty($data['VolumeType_Code'])) {
			$filter .= " and VolumeType_Code like '%'||:VolumeType_Code||'%'";
			$params['VolumeType_Code'] = $data['VolumeType_Code'];
		}

		if (!empty($data['VolumeType_noKeyValue']) && $data['VolumeType_noKeyValue'] == 'true') {
			$filter .= " and not exists (select AttributeVision_id from v_AttributeVision where AttributeVision_TableName = 'dbo.VolumeType' and AttributeVision_IsKeyValue = 2 and VolumeType_id = AttributeVision_TablePKey limit 1)";
		}

		if (!empty($data['isClose'])) {
			if ($data['isClose'] == 2) {
				$filter .= " and VolumeType_endDate <= dbo.tzGetDate()";
			} else {
				$filter .= " and coalesce(VolumeType_endDate, dbo.tzGetDate()) >= dbo.tzGetDate()";
			}
		}

		$query = "
			select
				-- select
				VolumeType_id as \"VolumeType_id\",
				VolumeType_Code as \"VolumeType_Code\",
				VolumeType_Name as \"VolumeType_Name\",
				to_char(VolumeType_begDate, 'dd.mm.yyyy') as \"VolumeType_begDate\",
				to_char(VolumeType_endDate, 'dd.mm.yyyy') as \"VolumeType_endDate\"
				-- end select
			from
				-- from
				v_VolumeType
				-- end from
			where
				-- where
				1=1
				{$filter}
				-- end where
			order by
				-- order by
				VolumeType_id
				-- end order by
		";

		return $this->getPagingResponse($query, $data, $data['start'], $data['limit'], true);
	}

	/**
	 * Загрузка значений
	 */
	function loadValuesGrid($data)
	{
		$params = array(
			'AttributeVision_TableName' => $data['AttributeVision_TableName'],
			'AttributeVision_TablePKey' => $data['AttributeVision_TablePKey']
		);

		$filter = "";
		
		// Исправление от 09.07.20 izabunyan с оптимизацией
		$this->load->library('mutual/MTariffVolumes_model');
		MTariffVolumes_model::dateControl($data,$filter,$params);

		if (!empty($data['isClose'])) {
			if ($data['isClose'] == 2) {
				$filter .= " and av.AttributeValue_endDate <= dbo.tzGetDate()";
			} else {
				$filter .= " and coalesce(av.AttributeValue_endDate, dbo.tzGetDate()) >= dbo.tzGetDate()";
			}
		}

		$select = "";
		$join = "";

		$counterFilters = 0;
		foreach($data['filters'] as $key => $one) {
			if (!empty($one) && !is_array($one) && preg_match('/^atrib_([0-9]*)$/ui', $key, $matches)) {
				$counterFilters++;
				$params["F{$counterFilters}_Attribute_id"] = $matches[1];
				$params["F{$counterFilters}_AttributeValue_Value"] = $one;

				// определяем тип атрибута
				$query = "
					select
						AttributeValueType_id as \"AttributeValueType_id\"
					from
						v_Attribute
					where
						Attribute_id = :Attribute_id
				";
				$resp = $this->queryResult($query, array(
					'Attribute_id' => $params["F{$counterFilters}_Attribute_id"]
				));

				$field = 'AttributeValue_ValueInt';
				if (!empty($resp[0]['AttributeValueType_id'])) {
					switch($resp[0]['AttributeValueType_id']) {
						case 1:
							$field = 'AttributeValue_ValueInt';
							break;
						case 2:
							$field = 'AttributeValue_ValueFloat';
							break;
						case 3:
							$field = 'AttributeValue_ValueFloat';
							break;
						case 4:
							$field = 'AttributeValue_ValueBoolean';
							break;
						case 5:
							$field = 'AttributeValue_ValueString';
							break;
						case 6:
							$field = 'AttributeValue_ValueIdent';
							break;
						case 7:
							$field = 'AttributeValue_ValueDate';
							$params["F{$counterFilters}_AttributeValue_Value"] = date('Y-m-d', strtotime($params["F{$counterFilters}_AttributeValue_Value"]));
							break;
						case 8:
							$field = 'AttributeValue_ValueIdent';
							break;
					}
				}

				$join .= "
					inner join lateral(
						select 
							av2.AttributeValue_id
						from
							v_AttributeValue av2
						where
							(
								(av.Attribute_id = :F{$counterFilters}_Attribute_id and av2.AttributeValue_id = av.AttributeValue_id)
								or
								(av.Attribute_id <> :F{$counterFilters}_Attribute_id and av2.AttributeValue_rid = av.AttributeValue_id)
							)
							and av2.Attribute_id = :F{$counterFilters}_Attribute_id
							and av2.{$field} = :F{$counterFilters}_AttributeValue_Value
						limit 1
					) F{$counterFilters}  on true
				";
			}
		}

		if ( in_array($this->regionNick, ['ekb', 'ufa', 'adygeya']) ) {
			$join .= "
				left join lateral (
					select t1.AttributeValue_ValueIdent
					from v_AttributeValue t1
						inner join v_Attribute t2 on t2.Attribute_id = t1.Attribute_id
					where t1.AttributeValue_rid = av.AttributeValue_id
						and t2.Attribute_SysNick = 'Lpu'
					limit 1
				) AttributeLpu on true
			";
			$select .= "
				case
					when a.Attribute_SysNick = 'Lpu' then av.AttributeValue_ValueIdent
					else AttributeLpu.AttributeValue_ValueIdent
				end as \"Lpu_id\",
			";
		}

		// проверяем тип атрибута
		$resp_a = $this->queryResult("
			select
				a.AttributeValueType_id as \"AttributeValueType_id\",
				a.Attribute_TableName as \"Attribute_TableName\"
			from
				v_AttributeVision avis
				inner join v_Attribute a on a.Attribute_id = avis.Attribute_id
			where
				avis.AttributeVision_TableName = :AttributeVision_TableName
				and avis.AttributeVision_TablePKey = :AttributeVision_TablePKey
				and avis.AttributeVision_IsKeyValue = 2 
		", $params);

		$value = "cast(av.AttributeValue_ValueString as varchar)";
		if (!empty($resp_a[0]['AttributeValueType_id'])) {
			switch($resp_a[0]['AttributeValueType_id']) {
				case 1:
					$value = "cast(av.AttributeValue_ValueInt as varchar)";
					break;
				case 2:
				case 3:
					$value = "cast(av.AttributeValue_ValueFloat as varchar)";
					break;
				case 4:
					$value = "cast(av.AttributeValue_ValueBoolean as varchar)";
					break;
				case 5:
					$value = "cast(av.AttributeValue_ValueString as varchar)";
					break;
				case 6:
					// надо выводить код из справочника
					$tableParams = $this->getTableParams($resp_a[0]['Attribute_TableName'], 'spr.', true);
					$join .= " left join {$tableParams['scheme']}.v_{$tableParams['table']} spr on spr.{$tableParams['tableField']}_id = av.AttributeValue_ValueIdent";
					$value = "cast({$tableParams['valueField']} as varchar)";
					break;
				case 7:
					$value = "to_char(av.AttributeValue_ValueDate, 'dd.mm.yyyy')";
					break;
				case 8:
					$value = "cast(av.AttributeValue_ValueIdent as varchar)";
					break;
			}
		}
		/**
		 * Оптимизация возникшая при решении задачи:
		 * https://jira.is-mis.ru/browse/PROMEDWEB-9022 от 09.07.2020  ggegamyan/izabunyan
		 * Ограничение по МО - АРМ администратора МО
		 * выдача только соответствующего МО, вполне возможно, что стоит применять фильтр и на других АРМ - но это не точно!!!!
		 * На данный момент используется только в АРМ Администратор МО и Администратор ЦОД
		 */
		MTariffVolumes_model::filterArm($this->getSessionParams(),$filter,$params);
		$query = "
			select
				-- select
				av.AttributeValue_id as \"AttributeValue_id\",
				{$select}
				{$value} as \"AttributeValue_Value\",
				to_char(av.AttributeValue_begDate, 'dd.mm.yyyy') as \"AttributeValue_begDate\",
				to_char(av.AttributeValue_endDate, 'dd.mm.yyyy') as \"AttributeValue_endDate\",
				av.AttributeValue_ValueText as \"AttributeValue_ValueText\"
				-- end select
			from
				-- from
				v_AttributeVision avis
				inner join v_AttributeValue av on av.AttributeVision_id = avis.AttributeVision_id
				inner join v_Attribute a on a.Attribute_id = av.Attribute_id
				{$join}
				-- end from
			where
				-- where
				avis.AttributeVision_TableName = :AttributeVision_TableName
				and avis.AttributeVision_TablePKey = :AttributeVision_TablePKey
				and avis.AttributeVision_IsKeyValue = 2
				{$filter}
				-- end where
			order by
				-- order by
				av.AttributeValue_id
				-- end order by
		";

		return $this->getPagingResponse($query, $params, $data['start'], $data['limit'], true);
	}

	/**
	 * Получение списка тариф по МО
	 */
	public function getTariffClassListByLpu($data) {
		$queryParams = array(
			'Lpu_oid' => $data['Lpu_oid'],
			'Date' => $data['Date']
		);

		$query = "
			select distinct
				TC.TariffClass_SysNick as \"TariffClass_SysNick\"
			from
				v_AttributeVision avis
				inner join v_AttributeValue av on av.AttributeVision_id = avis.AttributeVision_id
				inner join v_Attribute a on a.Attribute_id = av.Attribute_id
				inner join v_TariffClass TC on TC.TariffClass_id = avis.AttributeVision_TablePKey
				left join lateral(
					select 
						av2.AttributeValue_ValueIdent
					from
						v_AttributeValue av2
						inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
					where
						av2.AttributeValue_rid = av.AttributeValue_id
						and a2.Attribute_TableName = 'dbo.Lpu'
					limit 1
				) MOFILTER on true
			where
				avis.AttributeVision_TableName = 'dbo.TariffClass'
				and avis.AttributeVision_IsKeyValue = 2
				and coalesce(MOFILTER.AttributeValue_ValueIdent, 0) = :Lpu_oid
				and ( (coalesce(av.AttributeValue_endDate,:Date)>=:Date) )
				and ( (coalesce(av.AttributeValue_begDate,:Date)<=:Date) )
		";
		$result = $this->queryResult($query, $queryParams);

		$tariff_class_list = array();
		if ($result) {
			foreach($result as $item) {
				$tariff_class_list[] = $item['TariffClass_SysNick'];
			}
		}
		return $tariff_class_list;
	}

	/**
	 * Получение списка идентификаторов МО по объемам профиля отделения
	 * @param array $data
	 * @return array|bool
	 */
	public function getDiagListByLpuSectionProfile($data) {
		$params = array(
			'LpuSectionProfile_id' => $data['LpuSectionProfile_id'],
			'Date' => $data['Date'],
		);

		$query = "
			select distinct
				d.Diag_id as \"Diag_id\",
				d.Diag_pid as \"Diag_pid\"
			from
				v_AttributeVision avis
				inner join v_VolumeType vt on vt.VolumeType_id = avis.AttributeVision_TablePKey
				inner join v_AttributeValue av on av.AttributeVision_id = avis.AttributeVision_id
				inner join v_Attribute a on a.Attribute_id = av.Attribute_id
				left join v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = av.AttributeValue_ValueIdent
				left join v_Diag d on d.Diag_id = av.AttributeValue_ValueIdent
				inner join lateral(
					select 
						av2.AttributeValue_ValueIdent
					from
						v_AttributeValue av2
						inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
					where
						av2.AttributeValue_rid = av.AttributeValue_id
						and a2.Attribute_TableName = 'dbo.LpuSectionProfile'
					limit 1
				) PROFILEFILTER on true
			where
				avis.AttributeVision_TableName = 'dbo.VolumeType'
				and avis.AttributeVision_IsKeyValue = 2
				and vt.VolumeType_Code = 'Проф_диагн'
				and PROFILEFILTER.AttributeValue_ValueIdent = :LpuSectionProfile_id
				and (coalesce(av.AttributeValue_endDate,:Date)>=:Date)
				and (coalesce(av.AttributeValue_begDate,:Date)<=:Date)
		";

		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			return false;
		}

		$ids = array();
		foreach($resp as $item) {
			if (!in_array($item['Diag_id'], $ids)) {
				$ids[] = $item['Diag_id'];
			}
			if (!in_array($item['Diag_pid'], $ids)) {
				$ids[] = $item['Diag_pid'];
			}
		}

		return $ids;
	}

	/**
	 * Метод проверки наличия у ЛПУ объёма с кодом СМП_сокр
	 * @param type $data
	 */
	public function checkLpuHasSmpSokrVolume($data) {
		if (empty($data['session']['lpu_id'])) {
			return $this->createError('', 'Не задан обязательный параметр: идентификатор ЛПУ ');
		}

		$queryParams = array(
			'AttributeVision_TableName' => 'dbo.VolumeType',
			'AttributeVision_TablePKey' => 14, //СМП_Сокр
			'Lpu_id' => $data['session']['lpu_id']
		);

		$query = "
			select
				-- select
				av.AttributeValue_id as \"AttributeValue_id\",
				case
					when a.AttributeValueType_id = 1 then cast(av.AttributeValue_ValueInt as varchar)
					when a.AttributeValueType_id = 2 then cast(av.AttributeValue_ValueFloat as varchar)
					when a.AttributeValueType_id = 3 then cast(av.AttributeValue_ValueFloat as varchar)
					when a.AttributeValueType_id = 4 then cast(av.AttributeValue_ValueBoolean as varchar)
					when a.AttributeValueType_id = 5 then cast(av.AttributeValue_ValueString as varchar)
					when a.AttributeValueType_id = 6 then cast(av.AttributeValue_ValueIdent as varchar)
					when a.AttributeValueType_id = 7 then to_char(av.AttributeValue_ValueDate, 'dd.mm.yyyy')
					when a.AttributeValueType_id = 8 then cast(av.AttributeValue_ValueIdent as varchar)
				end as \"AttributeValue_Value\",
				to_char(av.AttributeValue_begDate, 'dd.mm.yyyy') as \"AttributeValue_begDate\",
				to_char(av.AttributeValue_endDate, 'dd.mm.yyyy') as \"AttributeValue_endDate\",
				av.AttributeValue_ValueText as \"AttributeValue_ValueText\"
				-- end select
			from
				-- from
				v_AttributeVision avis
				inner join v_AttributeValue av on av.AttributeVision_id = avis.AttributeVision_id
				inner join v_Attribute a on a.Attribute_id = av.Attribute_id
				left join lateral(
					select 
						av2.AttributeValue_ValueIdent
					from
						v_AttributeValue av2
						inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
					where
						av2.AttributeValue_rid = av.AttributeValue_id
						and a2.Attribute_TableName = 'dbo.Lpu'
					limit 1
				) MOFILTER on true
				-- end from
			where
				-- where
				avis.AttributeVision_TableName = :AttributeVision_TableName
				and avis.AttributeVision_TablePKey = :AttributeVision_TablePKey
				and avis.AttributeVision_IsKeyValue = 2
				and coalesce(MOFILTER.AttributeValue_ValueIdent, 0) = :Lpu_id
				and ( (coalesce(av.AttributeValue_endDate,dbo.tzGetDate())>=dbo.tzGetDate()) )
				and ( (coalesce(av.AttributeValue_begDate,dbo.tzGetDate())<=dbo.tzGetDate()) )
				-- end where
			order by
				-- order by
				av.AttributeValue_id
				-- end order by
			";

		return $this->queryResult($query, $queryParams);

	}

	/**
	 * @param $data
	 * @return array|false
	 * Метод проверки наличия у МО объёма
	 */
	public function checkLpuHasVolume(array $data = []) {
		$queryParams = [
			'AttributeVision_TableName' => 'dbo.VolumeType',
			'VolumeType_Code' => $data['VolumeType_Code'],
			'Registry_endDate' => $data['Registry_endDate'],
			'Lpu_id' => $data['Lpu_id'],
			'AttributeVision_TablePKey' => $this->getFirstResultFromQuery("
				select VolumeType_id from v_VolumeType where VolumeType_Code = :VolumeType_Code limit 1
			", [
				'VolumeType_Code' => $data['VolumeType_Code']
			]),
		];

		$query = "
			select
				-- select
				av.AttributeValue_id as \"AttributeValue_id\",
				case
					when a.AttributeValueType_id = 1 then cast(av.AttributeValue_ValueInt as varchar)
					when a.AttributeValueType_id = 2 then cast(av.AttributeValue_ValueFloat as varchar)
					when a.AttributeValueType_id = 3 then cast(av.AttributeValue_ValueFloat as varchar)
					when a.AttributeValueType_id = 4 then cast(av.AttributeValue_ValueBoolean as varchar)
					when a.AttributeValueType_id = 5 then cast(av.AttributeValue_ValueString as varchar)
					when a.AttributeValueType_id = 6 then cast(av.AttributeValue_ValueIdent as varchar)
					when a.AttributeValueType_id = 7 then to_char(av.AttributeValue_ValueDate, 'dd.mm.yyyy')
					when a.AttributeValueType_id = 8 then cast(av.AttributeValue_ValueIdent as varchar)
				end as \"AttributeValue_Value\",
				to_char(av.AttributeValue_begDate, 'dd.mm.yyyy') as \"AttributeValue_begDate\",
				to_char(av.AttributeValue_endDate, 'dd.mm.yyyy') as \"AttributeValue_endDate\",
				av.AttributeValue_ValueText as \"AttributeValue_ValueText\"
				-- end select
			from
				-- from
				v_AttributeVision as avis
				inner join v_AttributeValue as av on av.AttributeVision_id = avis.AttributeVision_id
				inner join v_Attribute as a on a.Attribute_id = av.Attribute_id
				left join lateral (
					select
						av2.AttributeValue_ValueIdent
					from
						v_AttributeValue as av2
						inner join v_Attribute as a2 on a2.Attribute_id = av2.Attribute_id
					where
						av2.AttributeValue_rid = av.AttributeValue_id
						and a2.Attribute_TableName = 'dbo.Lpu'
					limit 1
				) as MOFILTER on true
				-- end from
			where
				-- where
				avis.AttributeVision_TableName = :AttributeVision_TableName
				and avis.AttributeVision_TablePKey = :AttributeVision_TablePKey
				and avis.AttributeVision_IsKeyValue = 2
				and COALESCE(MOFILTER.AttributeValue_ValueIdent, 0) = :Lpu_id
				and ((COALESCE(av.AttributeValue_endDate, :Registry_endDate) >= :Registry_endDate))
				and ((COALESCE(av.AttributeValue_begDate, :Registry_endDate) <= :Registry_endDate))
				-- end where
			order by
				-- order by
				av.AttributeValue_id
				-- end order by
			";

		return $this->queryResult($query, $queryParams);
	}

	/**
	 * Метод проверки наличия у ЛПУ объёма с кодом 2015-06Проф_Цель
	 * @param type $data
	 */
	public function checkVizitCodeHasVolume($data) {
		$params = array(
			'UslugaComplex_id' => $data['UslugaComplex_id'],
		);
		$EvnClass_SysNick = $data['EvnClass_SysNick'];
		$volumesjoin = '';
		$volumesfilter = '';
		$needvolumeattribute = false;
		$error = '';

		$date = date_create(date('Y-m-d'));
		if (!empty($data['UslugaComplex_Date'])) {
			$date = ($data['UslugaComplex_Date'] instanceof DateTime)?$data['UslugaComplex_Date']:date_create($data['UslugaComplex_Date']);
		}

		if ($EvnClass_SysNick == 'EvnVizitPL' && $date < date_create('2016-01-01')) {
			$error = 'Указанный код посещения не соответствует связке Профиль-Цель посещения-Вид посещения';
			$filters  = "";

			if (!empty($data['UslugaComplex_Date'])) {
				$params['UslugaComplex_Date'] = $data['UslugaComplex_Date'];
			}

			if (!empty($data['LpuSectionProfile_id'])) {
				$params['LpuSectionProfile_id'] = $data['LpuSectionProfile_id'];
				$datefilters = "";
				if (!empty($data['UslugaComplex_Date'])) {
					$datefilters = "
						and ( (coalesce(UCP.UslugaComplexProfile_endDate, :UslugaComplex_Date)>= :UslugaComplex_Date) )
						and ( (coalesce(UCP.UslugaComplexProfile_begDate, :UslugaComplex_Date)<= :UslugaComplex_Date) )
					";
				}
				$filters .= "
					and exists(
						select * from v_UslugaComplexProfile UCP
						where UCP.UslugaComplex_id = uc.UslugaComplex_id
						and UCP.LpuSectionProfile_id = :LpuSectionProfile_id
						{$datefilters}
					)
				";
			}
			if (!empty($data['VizitType_id'])) {
				$params['VizitType_id'] = $data['VizitType_id'];
				$datefilters = "";
				if (!empty($data['UslugaComplex_Date'])) {
					$datefilters = "
						and ( (coalesce(UCA.UslugaComplexAttribute_endDate, :UslugaComplex_Date)>= :UslugaComplex_Date) )
						and ( (coalesce(UCA.UslugaComplexAttribute_begDate, :UslugaComplex_Date)<= :UslugaComplex_Date) )
					";
				}
				$filters .= "
					and exists(
						select * from v_UslugaComplexAttribute UCA
						inner join v_UslugaComplexAttributeType UCAT on 
							UCAT.UslugaComplexAttributeType_id = UCA.UslugaComplexAttributeType_id
						where UCAT.UslugaComplexAttributeType_SysNick like 'vizittype'
						and UCA.UslugaComplex_id = uc.UslugaComplex_id 
						and UCA.UslugaComplexAttribute_DBTableID = :VizitType_id
						{$datefilters}
					)
				";
			}
			if (!empty($data['VizitClass_id'])) {
				$params['VizitClass_id'] = $data['VizitClass_id'];
				$datefilters = "";
				if (!empty($data['UslugaComplex_Date'])) {
					$datefilters = "
						and ( (coalesce(UCA.UslugaComplexAttribute_endDate, :UslugaComplex_Date)>= :UslugaComplex_Date) )
						and ( (coalesce(UCA.UslugaComplexAttribute_begDate, :UslugaComplex_Date)<= :UslugaComplex_Date) )
					";
				}
				$filters .= "
					and exists(
						select * from v_UslugaComplexAttribute UCA
						inner join v_UslugaComplexAttributeType UCAT on 
							UCAT.UslugaComplexAttributeType_id = UCA.UslugaComplexAttributeType_id
						where UCAT.UslugaComplexAttributeType_SysNick like 'vizitclass'
						and UCA.UslugaComplex_id = uc.UslugaComplex_id 
						and UCA.UslugaComplexAttribute_DBTableID = :VizitClass_id
						{$datefilters}
					)
				";
			}

			if (!empty($filters)) {
				$query = "
					select uc.UslugaComplex_id as \"UslugaComplex_id\"
					from v_UslugaComplex uc
					where uc.UslugaComplex_id = :UslugaComplex_id
					{$filters}
				";
				$resp = $this->queryResult($query, $params);
				if (!is_array($resp)) {
					return $this->createError(500, 'Ошибка при проверке кода посещения');
				}
				if (count($resp) == 0) {
					return $this->createError(400, $error);
				}
			}
		}

		if ($date >= date_create('2016-01-01') && $date <= date_create('2017-12-31')) {
			$needvolumeattribute = true;

			if (strtolower($EvnClass_SysNick) == strtolower('EvnVizitPLStom')) {
				$volumesfilter .= " and vt.VolumeType_Code = '2016-01Проф_ВидОбрСт'";
				$error = 'Указанный код посещения не соответствует связке МО-Профиль-Первично в текущем году-Вид обращения';

				if (!empty($data['isPrimaryVizit'])) {
					$params['isPrimaryVizit'] = $data['isPrimaryVizit'];
					$volumesjoin .= "
						inner join lateral(
							select 
								av2.AttributeValue_ValueIdent
							from
								v_AttributeValue av2
								inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
							where
								av2.AttributeValue_rid = av.AttributeValue_id
								and a2.Attribute_SysNick = 'PrimaryVizit'
								and coalesce(av2.AttributeValue_ValueIdent,:isPrimaryVizit) = :isPrimaryVizit
							limit 1
						) VCFILTER on true
					";
				}
			} else {
				$volumesfilter .= " and vt.VolumeType_Code = '2016-01Проф_ВидОбр'";
				$error = 'Указанный код посещения не соответствует связке МО-Профиль-Вид посещения-Вид обращения';

				if (!empty($data['VizitClass_id'])) {
					$params['VizitClass_id'] = $data['VizitClass_id'];
					$volumesjoin .= "
						inner join lateral(
							select 
								av2.AttributeValue_ValueIdent
							from
								v_AttributeValue av2
								inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
							where
								av2.AttributeValue_rid = av.AttributeValue_id
								and a2.Attribute_TableName = 'dbo.VizitClass'
								and coalesce(av2.AttributeValue_ValueIdent,:VizitClass_id) = :VizitClass_id
							limit 1
						) VCFILTER on true
					";
				}
			}

			if (!empty($data['Lpu_id'])) {
				$params['Lpu_id'] = $data['Lpu_id'];
				$volumesjoin .= "
					inner join lateral(
						select 
							av2.AttributeValue_ValueIdent
						from
							v_AttributeValue av2
							inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
						where
							av2.AttributeValue_rid = av.AttributeValue_id
							and a2.Attribute_TableName = 'dbo.Lpu'
							and coalesce(av2.AttributeValue_ValueIdent,:Lpu_id) = :Lpu_id
						limit 1
					) LFILTER on true
				";
			}
			if (!empty($data['LpuSectionProfile_id'])) {
				$params['LpuSectionProfile_id'] = $data['LpuSectionProfile_id'];
				$volumesjoin .= "
					inner join lateral(
						select 
							av2.AttributeValue_ValueIdent
						from
							v_AttributeValue av2
							inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
						where
							av2.AttributeValue_rid = av.AttributeValue_id
							and a2.Attribute_TableName = 'dbo.LpuSectionProfile'
							and coalesce(av2.AttributeValue_ValueIdent,:LpuSectionProfile_id) = :LpuSectionProfile_id
						limit 1
					) LSPFILTER on true
				";
			}
			if (!empty($data['TreatmentClass_id'])) {
				$params['TreatmentClass_id'] = $data['TreatmentClass_id'];
				$volumesjoin .= "
					inner join lateral(
						select 
							av2.AttributeValue_ValueIdent
						from
							v_AttributeValue av2
							inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
							left join v_TreatmentClass TC on TC.TreatmentClass_id = av2.AttributeValue_ValueIdent
							left join v_TreatmentClass TC1 on TC1.TreatmentClass_id = :TreatmentClass_id
						where
							av2.AttributeValue_rid = av.AttributeValue_id
							and a2.Attribute_TableName = 'dbo.TreatmentClass'
							and (
								coalesce(TC.TreatmentClass_Code,TC1.TreatmentClass_Code) = TC1.TreatmentClass_Code
								or TC.TreatmentClass_Code = '2' and TC1.TreatmentClass_Code like '2.%'
							)
						limit 1
					) TCFILTER on true
				";
			}
		}

		if ($date >= date_create('2018-01-01')) {
			$needvolumeattribute = true;

			if (strtolower($EvnClass_SysNick) == strtolower('EvnVizitPLStom')) {
				$volumetypecode = '2018-01Спец_ВидОбрСт';
				$volumesfilter .= " and vt.VolumeType_Code = '{$volumetypecode}'";
				$error = 'Указанный код посещения не соответствует связке МО-Специальность-Первично в текущем году-Вид обращения';

				if (!empty($data['isPrimaryVizit'])) {
					$params['isPrimaryVizit'] = $data['isPrimaryVizit'];
					$volumesjoin .= "
						inner join lateral(
							select 
								av2.AttributeValue_ValueIdent
							from
								v_AttributeValue av2
								inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
							where
								av2.AttributeValue_rid = av.AttributeValue_id
								and a2.Attribute_SysNick = 'PrimaryVizit'
								and coalesce(av2.AttributeValue_ValueIdent,:isPrimaryVizit) = :isPrimaryVizit
							limit 1
						) VCFILTER on true
					";
				}
			} else {
				if(in_array($data['PayType_SysNick'], array('oms', 'ovd'))) {
					$volumetypecode = '2018-01Спец_ВидОбр';
				} else {
					$volumetypecode = '2018-01Спец_ВидОбрБюджет';
				}
				$volumesfilter .= " and vt.VolumeType_Code = '{$volumetypecode}'";
				$error = 'Указанный код посещения не соответствует связке МО-Специальность-Вид посещения-Вид обращения';

				if (!empty($data['VizitClass_id'])) {
					$params['VizitClass_id'] = $data['VizitClass_id'];
					$volumesjoin .= "
						inner join lateral(
							select 
								av2.AttributeValue_ValueIdent
							from
								v_AttributeValue av2
								inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
							where
								av2.AttributeValue_rid = av.AttributeValue_id
								and a2.Attribute_TableName = 'dbo.VizitClass'
								and coalesce(av2.AttributeValue_ValueIdent,:VizitClass_id) = :VizitClass_id
							limit 1
						) VCFILTER on true
					";
				}
			}

			if (!empty($data['Lpu_id'])) {
				$params['Lpu_id'] = $data['Lpu_id'];
				$volumesjoin .= "
					inner join lateral(
						select 
							av2.AttributeValue_ValueIdent
						from
							v_AttributeValue av2
							inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
						where
							av2.AttributeValue_rid = av.AttributeValue_id
							and a2.Attribute_TableName = 'dbo.Lpu'
							and coalesce(av2.AttributeValue_ValueIdent,:Lpu_id) = :Lpu_id
						limit 1
					) LFILTER on true
				";
			}
			if (/*!empty($data['FedMedSpec_id'])*/true) {
				$params['FedMedSpec_id'] = !empty($data['FedMedSpec_id'])?$data['FedMedSpec_id']:0;
				$volumesjoin .= "
					left join lateral(
						select 
							av2.AttributeValue_ValueIdent
						from
							v_AttributeValue av2
							inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
						where
							av2.AttributeValue_rid = av.AttributeValue_id
							and a2.Attribute_TableName = 'fed.MedSpec'
							and coalesce(av2.AttributeValue_ValueIdent,:FedMedSpec_id) = :FedMedSpec_id
						limit 1
					) LSPFILTER on true
				";
				if ($volumetypecode == '2018-01Спец_ВидОбрБюджет') {
					$volumesfilter .= " and coalesce(LSPFILTER.AttributeValue_ValueIdent, :FedMedSpec_id) = :FedMedSpec_id";
				} else {
					$volumesfilter .= " and LSPFILTER.AttributeValue_ValueIdent is not null";
				}
			}
			if (!empty($data['TreatmentClass_id'])) {
				$params['TreatmentClass_id'] = $data['TreatmentClass_id'];
				$volumesjoin .= "
					inner join lateral(
						select 
							av2.AttributeValue_ValueIdent
						from
							v_AttributeValue av2
							inner join v_Attribute a2 on a2.Attribute_id = av2.Attribute_id
							left join v_TreatmentClass TC on TC.TreatmentClass_id = av2.AttributeValue_ValueIdent
							left join v_TreatmentClass TC1 on TC1.TreatmentClass_id = :TreatmentClass_id
						where
							av2.AttributeValue_rid = av.AttributeValue_id
							and a2.Attribute_TableName = 'dbo.TreatmentClass'
							and (
								coalesce(TC.TreatmentClass_Code,TC1.TreatmentClass_Code) = TC1.TreatmentClass_Code
								or TC.TreatmentClass_Code = '2' and TC1.TreatmentClass_Code like '2.%'
							)
						limit 1
					) TCFILTER on true
				";
			}
		}

		if ($needvolumeattribute) {
			if (!empty($data['UslugaComplex_Date'])) {
				$params['UslugaComplex_Date'] = $data['UslugaComplex_Date'];
				$volumesfilter .= "
				and ( (coalesce(av.AttributeValue_endDate, :UslugaComplex_Date)>= :UslugaComplex_Date) )
				and ( (coalesce(av.AttributeValue_begDate, :UslugaComplex_Date)<= :UslugaComplex_Date) )
			";
			}

			$query = "
			select 
				av.AttributeValue_id as \"AttributeValue_id\"
			from
				v_AttributeVision avis
				inner join v_VolumeType vt on vt.VolumeType_id = avis.AttributeVision_TablePKey
				inner join v_AttributeValue av on av.AttributeVision_id = avis.AttributeVision_id
				inner join v_Attribute a on a.Attribute_id = av.Attribute_id
				{$volumesjoin}
			where
				avis.AttributeVision_TableName = 'dbo.VolumeType'
				and avis.AttributeVision_IsKeyValue = 2
				and av.AttributeValue_ValueIdent = :UslugaComplex_id
				{$volumesfilter}
			limit 1
			";

			$resp = $this->queryResult($query, $params);
			if (!is_array($resp)) {
				return $this->createError(500, 'Ошибка при проверке наличия объемов для кода посещения');
			}
			if (empty($resp[0]['AttributeValue_id'])) {
				return $this->createError(400, $error);
			}
		}

		return array(array('success' => true));
	}

	/**
	 *	Возвращает список колонок в таблице AttributeVision_TableName / AttributeVision_TablePKey
	 */
	function getColumnsOnTable($data) {
		if( !isset($data) || !is_array($data) || !isset($data['AttributeVision_TableName']) || !isset($data['AttributeVision_TablePKey']) ) {
			return false;
		}
		$query = "
			select
				a.Attribute_id as \"Attribute_id\",
				avis.AttributeVision_IsKeyValue as \"AttributeVision_IsKeyValue\",
				avis.AttributeVision_TableName as \"AttributeVision_TableName\",
				avis.AttributeVision_TablePKey as \"AttributeVision_TablePKey\",
				avis.AttributeVision_id as \"AttributeVision_id\",
				'atrib_' || cast(a.Attribute_id as varchar) as \"name\",
				avt.AttributeValueType_SysNick as \"AttributeValueType_SysNick\",
				a.Attribute_Name as \"descr\",
				a.Attribute_TableName as \"Attribute_TableName\",
				a.Attribute_SysNick as \"Attribute_SysNick\"
			from
				v_AttributeVision avis
				inner join v_Attribute a on a.Attribute_id = avis.Attribute_id
				inner join v_AttributeValueType avt on avt.AttributeValueType_id = a.AttributeValueType_id
			where
				avis.AttributeVision_TableName = :AttributeVision_TableName
				and avis.AttributeVision_TablePKey = :AttributeVision_TablePKey
			order by avis.AttributeVision_Sort
		";
		//echo getDebugSql($query, $data); die();
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *	Получение данных выбранной записи
	 */
	function getDirectoryRecord($data) {
		$query = "
			select
				to_char(av.AttributeValue_begDate, 'dd.mm.yyyy') as \"AttributeValue_begDate\",
				to_char(av.AttributeValue_endDate, 'dd.mm.yyyy') as \"AttributeValue_endDate\",
				a.Attribute_id as \"Attribute_id\",
				case
					when a.AttributeValueType_id = 1 then cast(avpid.AttributeValue_ValueInt as varchar)
					when a.AttributeValueType_id = 2 then cast(avpid.AttributeValue_ValueFloat as varchar)
					when a.AttributeValueType_id = 3 then cast(avpid.AttributeValue_ValueFloat as varchar)
					when a.AttributeValueType_id = 4 then cast(avpid.AttributeValue_ValueBoolean as varchar)
					when a.AttributeValueType_id = 5 then avpid.AttributeValue_ValueString
					when a.AttributeValueType_id = 6 then cast(avpid.AttributeValue_ValueIdent as varchar)
					when a.AttributeValueType_id = 7 then to_char(avpid.AttributeValue_ValueDate, 'dd.mm.yyyy')
					when a.AttributeValueType_id = 8 then cast(avpid.AttributeValue_ValueIdent as varchar)
				end as \"AttributeValue\"
			from
				v_AttributeValue av
				inner join v_AttributeValue avpid on avpid.AttributeValue_rid = av.AttributeValue_id OR avpid.AttributeValue_id = av.AttributeValue_id
				inner join v_Attribute a on a.Attribute_id = avpid.Attribute_id
			where
				av.AttributeValue_id = :AttributeValue_id
		";
		$result = $this->db->query($query, $data);

		$response = array();

		if ( is_object($result) ) {
			$resp = $result->result('array');
			foreach($resp as $respone) {
				$response['AttributeValue_begDate'] = $respone['AttributeValue_begDate'];
				$response['AttributeValue_endDate'] = $respone['AttributeValue_endDate'];
				$response['atrib_'.$respone['Attribute_id']] = $respone['AttributeValue'];
			}
		}

		return $response;
	}

	/**
	 * Получение параметров таблицы
	 */
	function getTableParams($Attribute_TableName, $tableAlias = "", $getCode = false) {
		$tableParams = explode('.', $Attribute_TableName);

		if ( count($tableParams) == 1 ) {
			$scheme = 'dbo';
			$table = $tableParams[0];
		}
		else if ( count($tableParams) == 2 ) {
			$scheme = $tableParams[0];
			$table = $tableParams[1];
		}
		else {
			$scheme = 'dbo';
			$table = preg_replace('/.*\./','', $Attribute_TableName); // убираем всё до точки вместе с точкой
		}

		$tableField = $table;
		if ($table == 'MesOld') {
			$tableField = 'Mes';
		}

		if ($getCode) {
			$valueField = "{$tableAlias}{$tableField}_Code";
		} else {
			$valueField = "{$tableAlias}{$tableField}_Name";

			if (getRegionNick() == 'kareliya' && in_array($table, array('Diag', 'UslugaComplex'))) { // для услуг и диагнозов выводим код.
				$valueField = "{$tableAlias}{$tableField}_Code";
			}

			if (in_array($table, array('HTMedicalCareClass'))) {
				$valueField = "coalesce({$tableAlias}{$tableField}_Code, '') || '. ' || coalesce({$tableAlias}{$tableField}_Name, '') || coalesce(' (Группа: ' || {$tableAlias}{$tableField}_GroupCode || ')', '')";
			}

			if (in_array($table, array('MesOld'))) {
				$valueField = "coalesce({$tableAlias}{$tableField}_Code, '') || '. ' || coalesce({$tableAlias}{$tableField}Old_Num, '') || '. ' || coalesce({$tableAlias}{$tableField}_Name, '')";
			}
		}

		if (in_array($table, array('Lpu'))) {
			$valueField = "{$tableAlias}{$tableField}_Nick";
		}

		if (in_array($table, array('MedStaffFact'))) {
			$valueField = "{$tableAlias}Person_Fin";
		}

		return array(
			'scheme' => $scheme,
			'table' => $table,
			'valueField' => $valueField,
			'tableField' => $tableField
		);
	}

	/**
	 * Сохранение
	 */
	function saveAttributeValue($data, $columns = null, $request = null) {
		if (!in_array($this->regionNick, array('ekb', 'vologda', 'ufa', 'adygeya')) && !isSuperadmin()) {
			return array('Error_Msg' => 'Функционал только для суперадмина!');
		}

		// Костыльная проверка по конкретному идешнику справочника dbo.VolumeType
		if ( $this->regionNick == 'ekb' && !isSuperAdmin() && $data['AttributeVision_TableName'] == 'dbo.VolumeType' && $data['AttributeVision_TablePKey'] != 118 ) {
			return array('Error_Msg' => 'Функционал только для суперадмина!');
		}

		// Получаем все поля таблицы
		//dbo.VolumeType 116
		if (empty($columns)) {
			$columns = $this->getColumnsOnTable(array('AttributeVision_TableName' => $data['AttributeVision_TableName'], 'AttributeVision_TablePKey' => $data['AttributeVision_TablePKey']));
		}

		if (empty($request)) {
			$request = $_REQUEST;
		}

		// Ищем главную, её будем сохранять в первую очередь, на неё будут ссылаться остальные.
		$main = null;
		foreach($columns as $column) {
			if ($column['AttributeVision_IsKeyValue'] == 2) {
				$main = $column;
			}
		}

		if (empty($main)) {
			return array('Error_Msg' => 'Не найдено поле для сохранения значения, сохранение невозможно');
		}

		$checksData = array(
			'MP_OTK_doubles' => array(
				'allow' => false,
				'descr' => 'Проверка пересечения по периоду действия для записей с одной МО и одним осмотром/исследованием',
				'errorText' => 'Для выбранного Осмотра/исследования есть запись с пересекающимся периодом действия',
				'task' => 'https://redmine.swan.perm.ru/issues/112028',
				'query' => "
					select
						av.AttributeValue_id as \"AttributeValue_id\"
					from v_AttributeValue av
						inner join lateral (
							select
								AttributeValue_id
							from v_AttributeValue
							where coalesce(AttributeValue_rid, AttributeValue_id) = av.AttributeValue_id
								and Attribute_id = 16
								and AttributeValue_ValueIdent = :Lpu_id
							limit 1
						) lpu on true
						inner join lateral (
							select
								AttributeValue_id
							from v_AttributeValue
							where coalesce(AttributeValue_rid, AttributeValue_id) = av.AttributeValue_id
								and Attribute_id = 105
								and AttributeValue_ValueIdent = :SurveyType_id
							limit 1
						) surveytype on true
					where
						av.AttributeValue_TableName = 'dbo.VolumeType'
						and av.AttributeValue_TablePKey = 118
						and av.AttributeValue_rid is null
						and av.AttributeValue_id != coalesce(:AttributeValue_id, 0)
						and av.AttributeValue_begDate <= coalesce(cast(:AttributeValue_endDate as date), av.AttributeValue_begDate)
						and coalesce(av.AttributeValue_endDate, cast(:AttributeValue_begDate as date)) >= cast(:AttributeValue_begDate as date)
					limit 1
				",
				'queryParams' => array(
					'AttributeValue_id' => (!empty($data['AttributeValue_id']) ? $data['AttributeValue_id'] : null),
					'AttributeValue_begDate' => $data['AttributeValue_begDate'],
					'AttributeValue_endDate' => $data['AttributeValue_endDate'],
				),
				'queryParamsCount' => 5,
			),
			'MP_OTK_Pol_doubles' => array(
				'allow' => false,
				'descr' => 'Проверка пересечения по периоду действия для записей с одной МО, МЭС и услугой',
				'errorText' => 'Для выбранных МО, МЭС, услуги уже имеется запись с пересекающимся периодом действия',
				'task' => 'https://redmine.swan.perm.ru//issues/112237',
				'query' => "
					select
						av.AttributeValue_id as \"AttributeValue_id\"
					from v_AttributeValue av
						inner join lateral (
							select
								AttributeValue_id
							from v_AttributeValue
							where coalesce(AttributeValue_rid, AttributeValue_id) = av.AttributeValue_id
								and Attribute_id = 16 -- МО
								and AttributeValue_ValueIdent = :Lpu_id
							limit 1
						) lpu on true
						inner join lateral (
							select
								AttributeValue_id
							from v_AttributeValue
							where coalesce(AttributeValue_rid, AttributeValue_id) = av.AttributeValue_id
								and Attribute_id = 112 -- Код МЭС
								and AttributeValue_ValueString = :Mes_Code
							limit 1
						) mes on true
						inner join lateral (
							select
								AttributeValue_id
							from v_AttributeValue
							where coalesce(AttributeValue_rid, AttributeValue_id) = av.AttributeValue_id
								and Attribute_id = 53 -- Услуга
								and AttributeValue_ValueIdent = :UslugaComplex_id
							limit 1
						) uc on true
					where
						av.AttributeValue_TableName = 'dbo.VolumeType'
						and av.AttributeValue_TablePKey = 121
						and av.AttributeValue_rid is null
						and av.AttributeValue_id != coalesce(:AttributeValue_id, 0)
						and av.AttributeValue_begDate <= coalesce(cast(:AttributeValue_endDate as date), av.AttributeValue_begDate)
						and coalesce(av.AttributeValue_endDate, cast(:AttributeValue_begDate as date)) >= cast(:AttributeValue_begDate as date)
					limit 1
				",
				'queryParams' => array(
					'AttributeValue_id' => (!empty($data['AttributeValue_id']) ? $data['AttributeValue_id'] : null),
					'AttributeValue_begDate' => $data['AttributeValue_begDate'],
					'AttributeValue_endDate' => $data['AttributeValue_endDate'],
				),
				'queryParamsCount' => 6,
			),
			'Common_doubles' => array(
				'allow' => false,
				'descr' => 'Проверка пересечения по периоду действия для записей с одинаковыми атрибутами',
				'errorText' => 'Объём с такими атрибутами уже есть в системе',
				'task' => 'https://redmine.swan.perm.ru//issues/114203',
				'query' => "
					select
						av.AttributeValue_id as \"AttributeValue_id\"
					from v_AttributeValue av
						{join_block}
					where
						av.AttributeValue_TableName = 'dbo.VolumeType'
						and av.AttributeValue_TablePKey = :AttributeValue_TablePKey
						and av.AttributeValue_rid is null
						and av.AttributeValue_id != coalesce(:AttributeValue_id, 0)
						and av.AttributeValue_begDate <= coalesce(cast(:AttributeValue_endDate as date), av.AttributeValue_begDate)
						and coalesce(av.AttributeValue_endDate, cast(:AttributeValue_begDate as date)) >= cast(:AttributeValue_begDate as date)
					limit 1
				",
				'queryJoinList' => array(),
				'queryParams' => array(
					'AttributeValue_id' => (!empty($data['AttributeValue_id']) ? $data['AttributeValue_id'] : null),
					'AttributeValue_TablePKey' => $main['AttributeVision_TablePKey'],
					'AttributeValue_begDate' => $data['AttributeValue_begDate'],
					'AttributeValue_endDate' => $data['AttributeValue_endDate'],
				),
				'queryParamsCount' => 4,
			),
		);

		// @task https://redmine.swan.perm.ru/issues/112028
		if ( $this->regionNick == 'ekb' && $data['AttributeVision_TableName'] == 'dbo.VolumeType' && $data['AttributeVision_TablePKey'] == 118 ) {
			$checksData['MP_OTK_doubles']['allow'] = true;
		}

		// @task https://redmine.swan.perm.ru/issues/112237
		if ( $this->regionNick == 'ekb' && $data['AttributeVision_TableName'] == 'dbo.VolumeType' && $data['AttributeVision_TablePKey'] == 121 ) {
			$checksData['MP_OTK_Pol_doubles']['allow'] = true;
		}

		// @task https://redmine.swan.perm.ru/issues/114203
		if ( $this->regionNick == 'penza' && $data['AttributeVision_TableName'] == 'dbo.VolumeType' && in_array($data['AttributeVision_TablePKey'], array(130, 131, 132)) ) {
			if ( $data['AttributeVision_TablePKey'] == 130 ) {
				$checkFields = false;

				foreach ( $columns as $column ) {
					if ( in_array($column['name'], array('atrib_16', 'atrib_24')) && !empty($request[$column['name']]) ) {
						$checkFields = true;
						break;
					}
				}

				if ( $checkFields === false ) {
					return array('Error_Msg' => 'Хотя бы один из двух атрибутов – «Диагноз», «МО» – должен быть заполнен.');
				}
			}

			$checksData['Common_doubles']['allow'] = true;
		}

		// 1. сохраняем главную запись
		$this->load->model('Attribute_model');
		// собираем текстовое описание всех атрибутов
		$valuetext = "";
		foreach($columns as $column) {
			if ( $column['Attribute_TableName'] == 'dbo.Lpu' && !empty($request[$column['name']]) ) {
				$checksData['MP_OTK_doubles']['queryParams']['Lpu_id'] = $request[$column['name']];
				$checksData['MP_OTK_Pol_doubles']['queryParams']['Lpu_id'] = $request[$column['name']];
			}

			if ( $column['Attribute_TableName'] == 'dbo.SurveyType' && !empty($request[$column['name']]) ) {
				$checksData['MP_OTK_doubles']['queryParams']['SurveyType_id'] = $request[$column['name']];
			}

			if ( $column['Attribute_TableName'] == 'dbo.UslugaComplex' && !empty($request[$column['name']]) ) {
				$checksData['MP_OTK_Pol_doubles']['queryParams']['UslugaComplex_id'] = $request[$column['name']];
			}

			if ( empty($column['Attribute_TableName']) && $column['Attribute_SysNick'] == 'MesOld' && !empty($request[$column['name']]) ) {
				$checksData['MP_OTK_Pol_doubles']['queryParams']['Mes_Code'] = $request[$column['name']];
			}

			if ($column['name'] != $main['name']) {
				if (!empty($valuetext)) {
					$valuetext .= ", ";
				}

				$value = "";
				switch($column['AttributeValueType_SysNick']) {
					case 'bool':
						$value = !empty($request[$column['name']])?"Да":"Нет";
						break;
					case 'ident':
						$tableParams = $this->getTableParams($column['Attribute_TableName']);

						$tableid = !empty($request[$column['name']])?$request[$column['name']]:null;

						$value = $this->getFirstResultFromQuery("
							select 
								{$tableParams['valueField']} as \"{$tableParams['valueField']}\"
							from
								{$tableParams['scheme']}.v_{$tableParams['table']}
							where
								{$tableParams['tableField']}_id = :tableid
							limit 1
						", array(
							'tableid' => $tableid
						));

						if (empty($value)) {
							$value = "";
						}
						break;
					case 'date':
						$value = !empty($request[$column['name']])?date('d.m.Y', strtotime($request[$column['name']])):"";
						break;
					default:
						$value = !empty($request[$column['name']])?$request[$column['name']]:"";
						break;
				}

				$valuetext .= "{$column['descr']} = {$value}";
			}

			switch ( $column['AttributeValueType_SysNick'] ) {
				case 'bool':
					$AttributeValueDefault = "''";
					$AttributeValueField = 'AttributeValue_ValueBoolean';
					break;
				case 'date':
					$AttributeValueDefault = "0";
					$AttributeValueField = 'AttributeValue_ValueDate';
					break;
				case 'ident':
					$AttributeValueDefault = "0";
					$AttributeValueField = 'AttributeValue_ValueIdent';
					break;
				case 'int':
					$AttributeValueDefault = "0";
					$AttributeValueField = 'AttributeValue_ValueInt';
					break;
				default:
					$AttributeValueDefault = "''";
					$AttributeValueField = 'AttributeValue_ValueString';
					break;
			}

			if ( $column['name'] == $main['name'] ) {
				$checksData['Common_doubles']['query'] .= "
					and coalesce(av.{$AttributeValueField}, {$AttributeValueDefault}) = coalesce(:{$column['name']}_value, {$AttributeValueDefault})
				";
				$checksData['Common_doubles']['queryParams'][$column['name'] . '_value'] = (!empty($request[$column['name']]) ? $request[$column['name']] : null);
				$checksData['Common_doubles']['queryParamsCount'] += 1;
			}
			else {
				$checksData['Common_doubles']['queryJoinList'][] = "
					inner join lateral (
						select
							AttributeValue_id
						from v_AttributeValue
						where coalesce(AttributeValue_rid, AttributeValue_id) = av.AttributeValue_id
							and Attribute_id = :{$column['name']}
							and coalesce({$AttributeValueField}, {$AttributeValueDefault}) = coalesce(:{$column['name']}_value, {$AttributeValueDefault})
						limit 1
					) {$column['name']} on true
				";
				$checksData['Common_doubles']['queryParams'][$column['name']] = $column['Attribute_id'];
				$checksData['Common_doubles']['queryParams'][$column['name'] . '_value'] = (!empty($request[$column['name']]) ? $request[$column['name']] : null);
				$checksData['Common_doubles']['queryParamsCount'] += 2;
			}
		}

		foreach ( $checksData as $check ) {
			if ( $check['allow'] == false || $check['queryParamsCount'] != count($check['queryParams']) ) {
				continue;
			}

			$query = $check['query'];

			if ( array_key_exists('queryJoinList', $checksData['Common_doubles']) ) {
				$query = str_replace('{join_block}', implode(PHP_EOL, $checksData['Common_doubles']['queryJoinList']), $query);
			}

			$checkResult = $this->getFirstResultFromQuery($query, $check['queryParams']);

			if ( $checkResult !== false && !empty($checkResult) ) {
				return array('Error_Msg' => $check['errorText']);
			}
		}

		$data['AttributeValue_ValueText'] = $valuetext;

		$data['Attribute_id'] = preg_replace('/atrib\_/','', $main['name']);
		$data['AttributeValue_TableName'] = $main['AttributeVision_TableName'];
		$data['AttributeValue_TablePKey'] = $main['AttributeVision_TablePKey'];
		$data['AttributeVision_id'] = $main['AttributeVision_id'];
		$data['AttributeValueType_SysNick'] = $main['AttributeValueType_SysNick'];
		$data['AttributeValue_Value'] = !empty($request[$main['name']])?$request[$main['name']]:null;
		$data['AttributeValue_pid'] = null;
		$data['AttributeValue_rid'] = null;
		$result = $this->Attribute_model->saveAttributeValue($data);

		if (!empty($result[0]['AttributeValue_id'])) {
			// сохраняем все остальные
			foreach($columns as $column) {
				if ($column['name'] != $main['name']) {
					$data['Attribute_id'] = preg_replace('/atrib\_/', '', $column['name']);
					$data['AttributeValue_TableName'] = $column['AttributeVision_TableName'];
					$data['AttributeValue_TablePKey'] = $column['AttributeVision_TablePKey'];
					$data['AttributeVision_id'] = $column['AttributeVision_id'];
					$data['AttributeValueType_SysNick'] = $column['AttributeValueType_SysNick'];
					$data['AttributeValue_Value'] = !empty($request[$column['name']]) ? $request[$column['name']] : null;
					$data['AttributeValue_pid'] = null;
					$data['AttributeValue_rid'] = $result[0]['AttributeValue_id'];

					// проверям есть ли уже такой атрибут
					$data['AttributeValue_id'] = $this->getFirstResultFromQuery("
						select 
							AttributeValue_id as \"AttributeValue_id\"
						from
							v_AttributeValue
						where
							AttributeValue_rid = :AttributeValue_rid
							and Attribute_id = :Attribute_id
						limit 1
					", $data);
					// сохраняем
					$this->Attribute_model->saveAttributeValue($data);
				}
			}
		}

		return array('Error_Msg' => '');
	}

	/**
	 * Загрузка списка полей для формы
	 */
	function getValuesFields($data) {
		// Получаем все поля таблицы
		$columns = $this->getColumnsOnTable(array('AttributeVision_TableName' => $data['AttributeVision_TableName'], 'AttributeVision_TablePKey' => $data['AttributeVision_TablePKey']));

		$tableFields = array();
		foreach($columns as $column) {
			$tableFields[$column['Attribute_TableName']] = $column['name'];
		}

		$isOtkazMesField = false;
		$isOtkazVolume = false;
		$isOtkazPolVolume = false;
		$isPerehodVolume = false;
		$VolumeType_Code = '';

		if ($data['AttributeVision_TableName'] == 'dbo.VolumeType') {
			$VolumeType_Code = $this->getFirstResultFromQuery("select VolumeType_Code as \"VolumeType_Code\" from v_VolumeType where VolumeType_id = :VolumeType_id limit 1", array(
				'VolumeType_id' => $data['AttributeVision_TablePKey']
			));
			if ( $VolumeType_Code === false ) {
				return array(
					'Error_Msg' => 'Ошибка при определении кода вида объема'
				);
			}
			if ($VolumeType_Code == 'МР_ОТК') {
				$isOtkazVolume = true;
			}
			else if ($VolumeType_Code == 'МР_ОТК_Пол') {
				$isOtkazMesField = true;
				$isOtkazVolume = true;
				$isOtkazPolVolume = true;
			}
			else if ($VolumeType_Code == 'ПерехСлучСтац') {
				$isPerehodVolume = true;
			}
		}

		if( !empty($data['AttributeValue_id']) ) {
			$record = $this->getDirectoryRecord($data);
		}

		// Массив исключений - это те поля которые НЕ нужны на форме
		$exc_tables = array('Server', 'Region', 'Evn');
		$fields = array();

		if (empty($data['getFilters'])) {
			$fields[] = array('xtype' => 'swdatefield', 'width' => 100, 'anchor' => '', 'plugins' => "[ new Ext.ux.InputTextMask('99.99.9999', false) ]", 'name' => 'AttributeValue_begDate', 'allowBlank' => false, 'fieldLabel' => toUtf('Дата начала'), 'value' => isset($record) && isset($record['AttributeValue_begDate']) ? toUtf($record['AttributeValue_begDate']) : null, 'UslugaComplexComboName' => (!empty($tableFields['dbo.UslugaComplex']) ? $tableFields['dbo.UslugaComplex'] : null));
			$fields[] = array('xtype' => 'swdatefield', 'width' => 100, 'anchor' => '', 'plugins' => "[ new Ext.ux.InputTextMask('99.99.9999', false) ]", 'name' => 'AttributeValue_endDate', 'fieldLabel' => toUtf('Дата окончания'), 'value' => isset($record) && isset($record['AttributeValue_endDate']) ? toUtf($record['AttributeValue_endDate']) : null);
		}

		foreach($columns as $column) {
			$additional = array();
			switch($column['AttributeValueType_SysNick']) {
				case 'int':
					$additional['xtype'] = 'numberfield';
					$additional['allowDecimals'] = false;
					break;
				case 'float':
				case 'money':
					$additional['xtype'] = 'numberfield';
					$additional['decimalPrecision'] = 4;
					$additional['allowDecimals'] = true;
					break;
				case 'bool':
					$additional['xtype'] = 'checkbox';
					break;
				case 'ident':
					$additional['xtype'] = "textfield";
					$table = preg_replace('/.*\./','',$column['Attribute_TableName']); // убираем всё до точки вместе с точкой
					$scheme = preg_replace('/\..*/','',$column['Attribute_TableName']); // убираем всё после точки вместе с точкой
					if (empty($scheme)) {
						$scheme = 'dbo';
					}
					if ( !in_array($table, $exc_tables) ) {
						// маппинг компонентов
						switch ($scheme.".".$table) {
							case 'dbo.MesOld':
								$additional['xtype'] = 'swksgcombo';

								//Захардкодил связь по описанию, по идее это неправильно, и надо дбавлять дополнительный параемтр при добавлении атрибута, но как экспрес решение пойдёт - т.к. ничего не ломает
								if (!empty($column['descr'])){
									switch($column['descr']){
										case 'КСГ':
											$additional['mesType'] = 'KSG';

											// @task https://redmine.swan.perm.ru/issues/81634
											// @task https://redmine.swan.perm.ru/issues/84801
											if ( $this->regionNick == 'astra' ) {
												$additional['startYear'] = 2016;
											}
											else if ( $this->regionNick == 'penza' && in_array($VolumeType_Code, array('2017КСГ-СтрогУсл', '2017КСГ-НеСтрогУсл', '2017КСГ-Запрет')) ) {
												$additional['mesType'] = 'KSG_KSS';
											}
											break;
										case 'КПГ':
											$additional['mesType'] = 'KPG';
											break;
										case 'Стоматологическая КСГ':
											$additional['mesType'] = 'STOMATKSG';
											break;
									}
								}

								if ( in_array($VolumeType_Code, array('2017КСГ-СтрогУсл', '2017КСГ-НеСтрогУсл', '2017КСГ-Запрет')) ) {
									$additional['allowBlank'] = false;
								}
								break;
							case 'dbo.UslugaComplex':
								if ($isOtkazVolume && $isOtkazMesField) {
									$additional['isOtkazVolume'] = true;
									$additional['xtype'] = 'swuslugacomplexformescombo';
								}
								else {
									$additional['xtype'] = 'swuslugacomplexnewcombo';
								}
								break;
							case 'dbo.Lpu':
								if ($isOtkazVolume) {
									$additional['isOtkazVolume'] = true;
									$additional['LpuSectionComboName'] = $tableFields['dbo.LpuSection'];

									if (getRegionNick() == 'ekb' && !isSuperAdmin() && isLpuAdmin($data['Lpu_id'])) {
										$additional['disabled'] = true;

										if ( empty($data['AttributeValue_id']) ) {
											$additional['value'] = $data['Lpu_id'];
										}
									}
								}
								$additional['ctxSerach'] = true;
								$additional['xtype'] = 'swlpucombo';
								if (getRegionNick() != 'ufa') {
									$additional['loadParams'] = array(
										'params' => array(
											'where' => ' where Lpu_endDate is null'
										)
									);
								}
								if ( in_array($VolumeType_Code, array('2017КСГ-НеСтрогУсл')) ) {
									$additional['allowBlank'] = false;
								}
								//if (getRegionNick() == 'ekb' && in_array($VolumeType_Code, array('ПерехСлучСтац')) ) {
								if (getRegionNick() == 'ekb' && $isPerehodVolume) {
									$additional['isPerehodVolume'] = true;

									if ( !empty($tableFields['dbo.LpuSection']) ) {
										$additional['LpuSectionComboName'] = $tableFields['dbo.LpuSection'];
									}

									if (!isSuperAdmin() && isLpuAdmin($data['Lpu_id'])) {
										$additional['disabled'] = true;

										if ( empty($data['AttributeValue_id']) ) {
											$additional['value'] = $data['Lpu_id'];
										}
									}
								}
								break;
							case 'dbo.LpuUnit':
								$additional['xtype'] = 'swlpuunitcombo';
								break;
							case 'dbo.Diag':
								if ( in_array($VolumeType_Code, array('2017КСГ-НеСтрогУсл', '2017КСГ-Запрет')) ) {
									$additional['allowBlank'] = false;
								}
								$additional['xtype'] = 'swdiagcombo';
								break;
							case 'dbo.Org':
								$additional['xtype'] = 'sworgcomboex';
								break;
							case 'DBO.ORGSMO':
								$additional['xtype'] = 'sworgsmocombo';
								break;
							case 'nsi.HTMedicalCareClass':
								$additional['xtype'] = 'swhtmedicalcareclassfedcombo';
								break;
							case 'fed.MedSpec':
								$additional['xtype'] = 'swfedmedspeccombo';
								break;
							case 'dbo.LpuSectionProfile':
								if ($isOtkazVolume) {
									$additional['xtype'] = 'swlpusectionprofiledopremotecombo';
								} else {
									$additional['xtype'] = 'swlpusectionprofilecombo';
								}

								if ( in_array($VolumeType_Code, array('2017КСГ-СтрогУсл', '2017КСГ-НеСтрогУсл', '2017КСГ-Запрет')) ) {
									$additional['allowBlank'] = false;
								}
								break;
							case 'dbo.LpuSection':
								if ($isOtkazVolume) {
									$additional['isOtkazVolume'] = true;
									$additional['LpuSectionProfileComboName'] = $tableFields['dbo.LpuSectionProfile'];
									$additional['MedStaffFactComboName'] = $tableFields['dbo.MedStaffFact'];
								}
								$additional['xtype'] = 'swlpusectioncombo';
								break;
							case 'dbo.LpuSectionCode':
								$additional['moreFields'] = array(
									array('name' => 'LpuSectionCode_begDT', 'mapping' => 'LpuSectionCode_begDT'),
									array('name' => 'LpuSectionCode_endDT', 'mapping' => 'LpuSectionCode_endDT')
								);
								$additional['xtype'] = 'swcommonsprcombo';
								$additional['comboSubject'] = $table;
								$additional['listWidth'] = 800;
								break;
							case 'dbo.MedStaffFact':
								$additional['xtype'] = 'swmedstafffactglobalcombo';
								break;
							case 'rls.DrugNomen':
								$additional['xtype'] = 'swdrugnomencombo';
								$additional['listWidth'] = 800;
								break;
							case 'dbo.SurveyType':
								if ($isOtkazVolume) {
									$this->load->model('EvnPLDisp_model', 'EvnPLDisp_model');
									$surveyTypeList = $this->EvnPLDisp_model->getSurveyTypesByDispClass(array('dispClassList' => '1,2,3,4,5,6,7,8,9,10,11,12'));
									$additional['loadParams'] = array(
										'params' => array(
											'where' => ' where SurveyType_id in (' . implode(',', $surveyTypeList) . ')'
										)
									);
									$additional['isOtkazVolume'] = true;
									$additional['UslugaComplexComboName'] = $tableFields['dbo.UslugaComplex'];
								}
								$additional['xtype'] = 'swcommonsprcombo';
								$additional['comboSubject'] = $table;
								break;
							default:
								$additional['xtype'] = 'swcommonsprcombo';
								$additional['comboSubject'] = $table;
								$additional['listWidth'] = 800;
								if ($scheme != 'dbo') {
									$additional['suffix'] = ucfirst($scheme);
								}

								if ( in_array($scheme.".".$table, array('dbo.HTMedicalCareClass', 'fed.HTMedicalCareClass')) ) {
									$additional['moreFields'] = array(
										array('name' => $table . '_begDate', 'mapping' => $table . '_begDate', 'type' => 'date'),
										array('name' => $table . '_endDate', 'mapping' => $table . '_endDate', 'type' => 'date'),
										array('name' => $table . '_GroupCode', 'mapping' => $table . '_GroupCode', 'type' => 'int'),
									);

									if ( in_array($this->regionNick, array('kareliya', 'perm')) ) {
										$additional['tpl'] = '<tpl for="."><div class="x-combo-list-item">' .
											'<span style="color: red">{HTMedicalCareClass_Code}</span>&nbsp;{HTMedicalCareClass_Name}' .
											' (Группа: {HTMedicalCareClass_GroupCode})' .
											'</div></tpl>';
									}
								}
								break;
						}

						$additional['table'] = $table;
						$additional['hiddenName'] = $column['name'];
					}
					break;
				case 'date':
					$additional['xtype'] = "swdatefield";
					$additional['width'] = 100;
					$additional['anchor'] = "";
					$additional['plugins'] = "[ new Ext.ux.InputTextMask('99.99.9999', false) ]";
					if (isset($record) && is_object($record[$column['name']])) {
						$record[$column['name']] = $record[$column['name']]->format('d.m.Y');
					}
					break;
				default:
					if ( $column['Attribute_SysNick'] == 'MesOld' && $isOtkazVolume === true ) {
						$additional['isOtkazVolume'] = true;
						$additional['UslugaComplexComboName'] = $tableFields['dbo.UslugaComplex'];
					}
					$additional['xtype'] = "textfield";
					break;
			}

			if ( $isOtkazPolVolume === true ) {
				$additional['allowBlank'] = false;
			}

			$comp = array_merge(array(
				'allowBlank' => 'true'
			,'name' => $column['name']
			,'fieldLabel' => toUtf($column['descr'])
			,'value' => isset($record) && isset($record[$column['name']]) ? toUtf($record[$column['name']]) : null
			), $additional);

			$fields[] = $comp;
		}

		return array(
			'data' => $fields,
			'totalCount' => count($fields),
			'Error_Msg' => ''
		);
	}

	/**
	 * Удаление значения
	 */
	function deleteValue($data) {
		if (!in_array($this->getRegionNick(), array('ekb', 'vologda', 'ufa', 'adygeya')) && !isSuperadmin()) {
			return array('Error_Msg' => 'Функционал только для суперадмина!');
		}

		// достаём все дочерние значения по pid / rid и удаляем их
		$query = "
			select
				AttributeValue_id as \"AttributeValue_id\"
			from
				v_AttributeValue
			where
				AttributeValue_rid = :AttributeValue_id OR AttributeValue_pid = :AttributeValue_id
		";
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			$resp = $result->result('array');
			foreach($resp as $respone) {
				$this->deleteValue(array(
					'AttributeValue_id' => $respone['AttributeValue_id']
				));
			}
		}

		// удаляем основное значение
		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_AttributeValue_del(
				AttributeValue_id := :AttributeValue_id
			)
		";

		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}

		return false;
	}

	/**
	 * Получение значениий атрибутов тарифа. Метод для API.
	 */
	function getAttributeValueForAPI($data) {
		$queryParams = array();
		$filter = "";

		if (!empty($data['TariffClass_id'])) {
			$filter .= " and tc.TariffClass_id = :TariffClass_id";
			$queryParams['TariffClass_id'] = $data['TariffClass_id'];
		}

		if (!empty($data['TariffClass_sysNick'])) {
			$filter .= " and tc.TariffClass_sysNick = :TariffClass_sysNick";
			$queryParams['TariffClass_sysNick'] = $data['TariffClass_sysNick'];
		}

		if (!empty($data['TariffClass_Name'])) {
			$filter .= " and tc.TariffClass_Name = :TariffClass_Name";
			$queryParams['TariffClass_Name'] = $data['TariffClass_Name'];
		}

		if (!empty($data['Attribute_id'])) {
			$filter .= " and a.Attribute_id = :Attribute_id";
			$queryParams['Attribute_id'] = $data['Attribute_id'];
		}

		if (!empty($data['Attribute_sysNick'])) {
			$filter .= " and a.Attribute_sysNick = :Attribute_sysNick";
			$queryParams['Attribute_sysNick'] = $data['Attribute_sysNick'];
		}

		if (!empty($data['Attribute_Name'])) {
			$filter .= " and a.Attribute_Name = :Attribute_Name";
			$queryParams['Attribute_Name'] = $data['Attribute_Name'];
		}

		if (!empty($data['Date_DT'])) {
			$filter .= "
				and coalesce(av.AttributeValue_begDate, :Date_DT) <= :Date_DT
				and coalesce(av.AttributeValue_endDate, :Date_DT) >= :Date_DT
			";
			$queryParams['Date_DT'] = $data['Date_DT'];
		}

		if (empty($filter)) {
			return array();
		}

		return $this->queryResult("
			select
				tc.TariffClass_id as \"TariffClass_id\",
				tc.TariffClass_sysNick as \"TariffClass_sysNick\",
				tc.TariffClass_Name as \"TariffClass_Name\",
				a.Attribute_id as \"Attribute_id\",
				a.Attribute_sysNick as \"Attribute_sysNick\",
				to_char(av.AttributeValue_begDate, 'yyyy-mm-dd') as \"AttributeValue_begDate\",
				to_char(av.AttributeValue_endDate, 'yyyy-mm-dd') as \"AttributeValue_endDate\",
				av.AttributeValue_ValueFloat as \"AttributeValue_ValueFloat\"
			from
				v_AttributeVision avis
				inner join v_AttributeValue av on av.AttributeVision_id = avis.AttributeVision_id
				inner join v_Attribute a on a.Attribute_id = av.Attribute_id
				inner join v_TariffClass tc on tc.TariffClass_id = avis.AttributeVision_TablePKey
			where
				avis.AttributeVision_TableName = 'dbo.TariffClass'
				{$filter}
		", $queryParams);
	}
}
