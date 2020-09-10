<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * @property Person_model $Person_model
 */

require_once(APPPATH.'models/PersonIdentPackage_model.php');

class Krym_PersonIdentPackage_model extends PersonIdentPackage_model {
	/**
	 * construct
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Получение списка сформированных пакетов для идентификации
	 * @param array $data
	 * @return array
	 */
	function loadPersonIdentPackageGrid($data) {
		$params = array();
		$filters = array('1=1');

		$filters[] = "PIP.PersonIdentPackage_Name <> 'PersonIdentPackage_Name'";
		$filters[] = "PIP.PersonIdentPackage_Name like '%.DBF'";

		if (isset($data['PersonIdentPackage_DateRange']) && !empty($data['PersonIdentPackage_DateRange'][0]) && !empty($data['PersonIdentPackage_DateRange'][1])) {
			$filters[] = "PIP.PersonIdentPackage_begDate between :PersonIdentPackage_begDateRange and :PersonIdentPackage_endDateRange";
			$params['PersonIdentPackage_begDateRange'] = $data['PersonIdentPackage_DateRange'][0];
			$params['PersonIdentPackage_endDateRange'] = $data['PersonIdentPackage_DateRange'][1];
		}

		if (!empty($data['PersonIdentPackage_IsResponseRetrieved'])) {
			$filters[] = "isnull(PIP.PersonIdentPackage_IsResponseRetrieved, 1) = :PersonIdentPackage_IsResponseRetrieved";
			$params['PersonIdentPackage_IsResponseRetrieved'] = $data['PersonIdentPackage_IsResponseRetrieved'];
		}

		$filters_str = implode("\nand ", $filters);

		$query = "
			select
				-- select
				PIP.PersonIdentPackage_id,
				PIP.PersonIdentPackage_Name,
				convert(varchar(10), PIP.PersonIdentPackage_begDate, 104) as PersonIdentPackage_begDate,
				PIP.PersonIdentPackage_IsResponseRetrieved,
				ActualCount.Value as PersonIdentPackage_ActualCount,
				ErrorCount.Value as PersonIdentPackage_ErrorCount
				-- end select
			from
				-- from
				v_PersonIdentPackage PIP with(nolock)
				outer apply (
					select top 1 count(distinct PIPP.Person_id) as Value
					from v_PersonIdentPackagePos PIPP with(nolock)
					where PIPP.PersonIdentPackage_id = PIP.PersonIdentPackage_id
					and PIPP.PersonIdentState_id = 1
				) ActualCount
				outer apply (
					select top 1 count(distinct PIPP.Person_id) as Value
					from v_PersonIdentPackagePos PIPP with(nolock)
					where PIPP.PersonIdentPackage_id = PIP.PersonIdentPackage_id
					and exists(
						select * from v_PersonIdentPackagePosError with(nolock)
						where PersonIdentPackagePos_id = PIPP.PersonIdentPackagePos_id
					)
				) ErrorCount
				-- end from
			where
				-- where
				{$filters_str}
				-- end where
			order by
				-- order by
				PIP.PersonIdentPackage_begDate
				-- end order by
		";

		$result = $this->queryResult(/*getLimitSQLPH($query, $data['start'], $data['limit'])*/$query, $params);
		$count_result = $this->queryResult(getCountSQLPH($query), $params);

		if (!is_array($result) || !is_array($count_result)) {
			return false;
		}

		$response = array(
			'data' => $result,
			'totalCount' => $count_result[0]['cnt'],
		);

		return $response;
	}

	/**
	 * Получение списка записей в пакете для идентификации
	 * @param array $data
	 * @return array
	 */
	function loadPersonIdentPackagePosGrid($data) {
		$params = array(
			'PersonIdentPackage_id' => $data['PersonIdentPackage_id']
		);

		$query = "
			select
				PIPP.PersonIdentPackagePos_id,
				PIPP.PersonIdentPackage_id,
				PS.Person_id,
				PS.Person_SurName,
				PS.Person_FirName,
				PS.Person_SecName,
				Sex.Sex_id,
				Sex.Sex_Code,
				Sex.Sex_Name,
				convert(varchar(10), PS.Person_BirthDay, 104) as Person_BirthDay,
				convert(varchar(10), PIPP.PersonIdentPackagePos_identDT, 104) as PersonIdentPackagePos_identDT,
				convert(varchar(10), PIPP.PersonIdentPackagePos_identDT2, 104) as PersonIdentPackagePos_identDT2,
				E.Evn_id,
				E.Evn_pid,
				E.Evn_rid,
				E.EvnClass_Name,
				/*case 
					when EvnClass_SysNick = 'EvnPL' then 'ТАП' 
					when EvnClass_SysNick = 'EvnPS' then 'КВС'
					else E.EvnClass_Name
				end*/ E.EvnClass_Name as EvnClass_Nick,
				E.EvnClass_SysNick,
				isnull(EPL.EvnPL_NumCard, EPS.EvnPS_NumCard) as Evn_NumCard,
				PIS.PersonIdentState_id,
				PIS.PersonIdentState_Code,
				PIS.PersonIdentState_Name,
				null as Errors
			from
				v_PersonIdentPackagePos PIPP with(nolock)
				inner join v_PersonState PS with(nolock) on PS.Person_id = PIPP.Person_id
				inner join v_Sex Sex with(nolock) on Sex.Sex_id = PS.Sex_id
				left join v_PersonIdentState PIS with(nolock) on PIS.PersonIdentState_id = PIPP.PersonIdentState_id
				left join v_Evn E with(nolock) on E.Evn_id = PIPP.Evn_id
				left join v_EvnPL EPL with(nolock) on EPL.EvnPL_Id = E.Evn_id
				left join v_EvnPS EPS with(nolock) on EPS.EvnPS_Id = E.Evn_id
			where
				PIPP.PersonIdentPackage_id = :PersonIdentPackage_id
		";

		$result = $this->queryResult($query, $params);
		if (!is_array($result)) {
			return false;
		}

		$ids = array();
		$responseData = array();
		foreach($result as $item) {
			$key = $item['PersonIdentPackagePos_id'];
			$ids[] = $key;
			$responseData[$key] = $item;
		}

		if (count($ids) > 0) {
			$ids_str = implode(",", $ids);

			$query = "
				select
					PersonIdentPackagePos_id,
					isnull(PersonIdentPackagePosError_ErrCode+'. ','')+PersonIdentPackagePosError_ErrDescr as PersonIdentPackagePosError
				from v_PersonIdentPackagePosError with(nolock)
				where PersonIdentPackagePos_id in ({$ids_str})
			";
			$result = $this->queryResult($query);
			if (!is_array($result)) {
				return false;
			}

			$errorsByPos = array();
			foreach($result as $item) {
				$key = $item['PersonIdentPackagePos_id'];
				$errorsByPos[$key][] = $item['PersonIdentPackagePosError'];
			}

			foreach($errorsByPos as $key => $errors) {
				$responseData[$key]['Errors'] = implode("<br/>", $errors);
			}
		}

		$response = array(
			'data' => array_values($responseData),
		);

		return $response;
	}

	/**
	 * @param int $mcode
	 * @param DateTime $date
	 * @return array
	 */
	function createPersonIdentFileParams($mcode, $date) {
		$out_dir = EXPORTPATH_IDENT_PACKAGE;
		$out_dir_arr = explode("/", $out_dir);
		$tmp_dir = "";
		foreach($out_dir_arr as $dir) {
			if (empty($dir)) continue;
			$tmp_dir .= $dir.'/';
			if (!file_exists($tmp_dir)) {
				mkdir($tmp_dir);
			}
		}
		$package_sign = sprintf('%s_%s_', $mcode, $date->format('dmY'));
		$package_day_num = $this->getFirstResultFromQuery("
			select top 1 count(*)+1 as num
			from v_PersonIdentPackage with(nolock)
			where PersonIdentPackage_Name like '{$out_dir}{$package_sign}%.DBF'
		");
		$package_sign .= $package_day_num;
		$package_name = $package_sign.'.DBF';

		return array(
			'out_dir' => $out_dir,
			'package_sign' => $package_sign,
			'package_name' => $package_name,
			'package_path' => $out_dir.$package_name,
		);
	}

	/**
	 * Создание пакета на идентификацию
	 * @param array $data
	 * @return array
	 */
	function createPersonIdentPackages() {
		set_time_limit(0);

		$response = array(
			'success' => true,
			'PackagePosCount' => 0,
			'PackagePosCountInFile' => 0,
			'PackageCount' => 0,
			'PackageList' => array(),
		);

		$package_size = 200;

		$selector = "
			select
				-- select
				PIPP.*
				-- end select
			from 
				-- from
				v_PersonIdentPackagePos PIPP with(nolock)
				left join v_Evn E with(nolock) on E.Evn_id = PIPP.Evn_id 
				-- end from
			where
				-- where
				PIPP.PersonIdentPackage_id is null
				and isnull(E.Lpu_id, PIPP.Lpu_id) = :Lpu_id
				-- end where
			order by
				-- order by
				PIPP.PersonIdentPackage_id
				-- end order by
		";

		$queryIdentData = "
			select
				PIPP.PersonIdentPackagePos_id as id,
				PS.Person_SurName as FAM,
				PS.Person_FirName as IM,
				PS.Person_SecName as OT,
				PS.Person_BirthDay as DR,
				Sex.Sex_fedid as W,
				(
					substring(PS.Person_Snils,1,3)+'-'+
					substring(PS.Person_Snils,4,3)+'-'+
					substring(PS.Person_Snils,7,3)+' '+
					substring(PS.Person_Snils,10,2)
				) as SS,
				DT.DocumentType_Code as C_DOC,
				D.Document_Ser as S_DOC,
				D.Document_Num as N_DOC,
				PT.PolisType_CodeF008 as OPDOC,
				P.Polis_Ser as SPOL,
				P.Polis_Num as NPOL,
				PS.Person_EdNum as ENP,
				PIPP.PersonIdentPackagePos_identDT as DIN,
				PIPP.PersonIdentPackagePos_identDT as DOUT
			from
				v_PersonIdentPackagePos PIPP with(nolock)
				cross apply (
					select top 1
						*
					from
						v_Person_all with(nolock)
					where
						Person_id = PIPP.Person_id
						and PersonEvn_insDT <= PIPP.PersonIdentPackagePos_identDT
					order by
						PersonEvn_insDT desc
				) PS
				left join v_Sex Sex with(nolock) on Sex.Sex_id = PS.Sex_id
				left join v_Document D with(nolock) on D.Document_id = PS.Document_id
				left join v_DocumentType DT with(nolock) on DT.DocumentType_id = D.DocumentType_id
				left join v_Polis P with(nolock) on P.Polis_id = PS.Polis_id
				left join v_PolisType PT with(nolock) on PT.PolisType_id = P.PolisType_id
			where
				PersonIdentPackage_id = :PersonIdentPackage_id
		";

		$queryCount = function($query, $params = array()) {
			return $this->getFirstResultFromQuery(getCountSQLPH($query), $params);
		};

		$dateConverter = function(&$value) {
			if ($value instanceof DateTime) $value = $value->format('Ymd');
		};

		$dbfFieldsCfg = array(
			array('FAM', 'C', 60),
			array('IM', 'C', 60),
			array('OT', 'C', 60),
			array('DR', 'D', 8),
			array('W', 'N', 1, 0),
			array('SS', 'C', 14),
			array('C_DOC', 'N', 2, 0),
			array('S_DOC', 'C', 10),
			array('N_DOC', 'C', 30),
			array('OPDOC', 'N', 1, 0),
			array('SPOL', 'C', 10),
			array('NPOL', 'C', 19),
			array('ENP', 'C', 16),
			array('DIN', 'D', 8),
			array('DOUT', 'D', 8),
		);
		$dbfFields = array_column($dbfFieldsCfg, 0);

		$allowedCominations = array(
			array('FAM', 'DR', 'W', 'SS'),
			array('FAM', 'DR', 'W', 'C_DOC'),
			array('FAM', 'DR', 'W', 'OPDOC'),
		);
		$isAllowedCombination = function($item, $combination) {
			foreach($combination as $field) {
				if (empty($item[$field])) {
					return false;
				}
			}
			return true;
		};
		$isAllowed = function($item) use($allowedCominations, $isAllowedCombination) {
			foreach($allowedCominations as $combination) {
				if ($isAllowedCombination($item, $combination)) {
					return true;
				}
			}
			return false;
		};

		$setPackagePosNumRecQuery = function($packagePos, $index) {
			$id = $packagePos['id'];
			$numRec = $index + 1;
			return "
				update PersonIdentPackagePos with(rowlock)
				set PersonIdentPackagePos_NumRec = {$numRec}
				where PersonIdentPackagePos_id = {$id}
			";
		};

		try {
			$lpuList = $this->queryResult("
				select
					Lpu_id,
					Lpu_f003mcod as mcode
				from v_Lpu with(nolock)
				where nullif(rtrim(Lpu_f003mcod),'') is not null
			");
			if (!is_array($lpuList)) {
				throw new Exception('Ошибка при получении списка МО');
			}

			foreach($lpuList as $lpu) {
				while ($queryCount($selector, $lpu) > 0) {
					$this->beginTransaction();

					//Формирование названия файла
					$date = date_create();
					$fileParams = $this->createPersonIdentFileParams($lpu['mcode'], $date);

					//Добавление нового пустого пакета для идентификации
					$resp = $this->savePersonIdentPackage(array(
						'PersonIdentPackage_id' => null,
						'PersonIdentPackage_Name' => $fileParams['package_path'],
						'PersonIdentPackage_begDate' => $date->format('Y-m-d'),
						'PersonIdentPackage_IsResponseRetrieved' => 1,
						'pmUser_id' => 1,
					));
					if (!$this->isSuccessful($resp)) {
						throw new Exception($resp[0]['Error_Msg']);
					}
					$PersonIdentPackage_id = $resp[0]['PersonIdentPackage_id'];

					//Набирает записи для пакета, прописывает им идентификатор пакета
					$params = array(
						'PersonIdentPackage_id' => $PersonIdentPackage_id,
						'Lpu_id' => $lpu['Lpu_id'],
					);
					$query = getLimitSQLPH($selector, 0, $package_size);
					$this->db->query("
						with pos_list as ({$query})
						update PersonIdentPackagePos with(rowlock)
						set PersonIdentPackage_id = :PersonIdentPackage_id
						where PersonIdentPackagePos_id in (select PersonIdentPackagePos_id from pos_list)
					", $params);

					//Получение данные для формирование файла
					$identItems = $this->queryResult($queryIdentData, $params);
					if (!is_array($identItems)) {
						throw new Exception('Ошибка при запросе данных для формирования файла');
					}

					$allowedItems = array();
					$notAllowedItems = array();
					foreach($identItems as $item) {
						if ($isAllowed($item)) {
							$allowedItems[] = $item;
						} else {
							$notAllowedItems[] = $item;
						}
					}
					unset($identItems);

					//Установка статуса "Ошибка. Не все необходимые данные заполнены"
					if (count($notAllowedItems) > 0) {
						$notAllowedIds = array_column($notAllowedItems, 'id');
						$notAllowedIds_str = implode(",", $notAllowedIds);
						$this->db->query("
							declare @dt datetime = dbo.tzGetDate();
							update PersonIdentPackagePos with(rowlock)
							set
								PersonIdentState_id = 5,
								PersonIdentPackagePos_updDT = @dt
							where PersonIdentPackagePos_id in ({$notAllowedIds_str})
						");
					}
					//Сохранение номера записи в файле
					if (count($allowedItems) > 0) {
						$queries = array_map($setPackagePosNumRecQuery, $allowedItems, array_keys($allowedItems));
						$this->db->query(join($queries));
					}

					//Добавление записей в dbf-файл
					$dbf = dbase_create($fileParams['package_path'], $dbfFieldsCfg);
					if (!$dbf) {
						throw new Exception('Ошибка при создании dbf-файла');
					}
					foreach ($allowedItems as $item) {
						$record = array();
						foreach($dbfFields as $field) {
							$record[] = isset($item[$field])?$item[$field]:null;
						}
						array_walk($record, $dateConverter);
						array_walk($record, 'ConvertFromUtf8ToCp866');

						if (!dbase_add_record($dbf, $record)) {
							throw new Exception('Ошибка при добавлении данных в dbf-файл');
						}
					}
					dbase_close($dbf);

					$response['PackagePosCount'] += count($allowedItems) + count($notAllowedItems);
					$response['PackagePosCountInFile'] += count($allowedItems);
					$response['PackageCount'] += 1;
					$response['PackageList'][] = array(
						'id' => $PersonIdentPackage_id,
						'name' => $fileParams['package_name'],
						'path' => $fileParams['package_path'],
					);

					$this->commitTransaction();
				}
			}
		} catch (Exception $e) {
			return $this->createError($e->getCode(), $e->getMessage());
		}

		return array($response);
	}

	/**
	 * @param array $data
	 * @return array
	 */
	function autoImportPersonIdentPackagesResponse($data) {
		set_time_limit(0);

		$path = IMPORTPATH_IDENT_PACKAGE;
		$regexp = '/^\d+_\d+_\d+\.DBF/';
		$response = array('success' => true);
		$files_filter = function($file) use($regexp) {
			return preg_match($regexp, $file);
		};

		if (!is_dir($path)) {
			return array($response);
		}

		$response_files = array_filter(scandir($path), $files_filter);

		foreach($response_files as $file) {
			$this->importPersonIdentPackageResponse($data, array(
				'name' => $file,
				'tmp_name' => IMPORTPATH_IDENT_PACKAGE.$file,
			));
		}

		return array($response);
	}

	/**
	 * Импорт файла ответа на запрос идентифициции
	 * @param array $data
	 * @param array $file
	 * @return array
	 */
	function importPersonIdentPackageResponse($data, $file) {
		set_time_limit(0);

		$response = array('success' => true);
		$requestFile = null;

		try {
			$this->beginTransaction();

			//Получение имени файла и проверка его на соответствие формату
			$package_name = null;
			$regexp = '/^(\d+)_(\d+)_(\d+)\.(DBF)/';
			if (preg_match($regexp, mb_strtoupper($file['name']), $match)) {
				$mcode = $match[1];
				$date = $match[2];
				$num = $match[3];
				$package_name = "{$mcode}_{$date}_{$num}";
			}
			if (empty($package_name)) {
				throw new Exception('Загружаемый файл не соответствует формату');
			}

			//Поиск пакета по названию загружаемого файла
			$query = "
				select top 1
					PIP.PersonIdentPackage_id,
					PIP.PersonIdentPackage_Name,
					convert(varchar(10), PIP.PersonIdentPackage_begDate, 120) as PersonIdentPackage_begDate,
					PIP.PersonIdentPackage_IsResponseRetrieved,
					case when L.Lpu_f003mcod = :mcode then 1 else 0 end as checkMcode
				from v_PersonIdentPackage PIP with(nolock)
				left join v_Lpu L with(nolock) on L.Lpu_id = :Lpu_id
				where PIP.PersonIdentPackage_Name like '%'+:package_name+'.DBF'
				order by PersonIdentPackage_insDT desc
			";
			$params = array(
				'package_name' => $package_name,
				'mcode' => $mcode,
				'Lpu_id' => $data['Lpu_id'],
			);
			$package = $this->getFirstRowFromQuery($query, $params, true);
			if ($package === false) {
				throw new Exception('Ошибка при поиске пакета с данными для идентфикации');
			}
			if (empty($package)) {
				throw new Exception("В системе не найден файл запроса {$package_name}");
			}
			if (isset($data['ARMType']) && $data['ARMType'] == 'lpuadmin' && !$package['checkMcode']) {
				throw new Exception("У текущего пользователя нет прав для работы с файлами идентификации МО с реестровым номером {$mcode}");
			}
			if ($package['PersonIdentPackage_IsResponseRetrieved'] == 2 && empty($_REQUEST['getDebug'])) {
				throw new Exception("Для файла {$package_name} обработка ответа ТФОМС произведена ранее");
			}

			if (!$dbf = dbase_open($file['tmp_name'], 0)) {
				throw new Exception('Не удалось открыть dbf-файл');
			}

			//Чтение данных из файла
			$count = dbase_numrecords($dbf);
			$responseData = array();
			for ($num = 1; $num <= $count; $num++) {
				$responseData[] = dbase_get_record_with_names($dbf, $num);
			}
			dbase_close($dbf);
			array_walk_recursive($responseData, 'ConvertFromWin866ToUtf8');

			//Обработка полученных данных
			$tmpTableName = $this->saveResponseDataInTmpTable($responseData);
			$this->identResponseData($tmpTableName, $package);
			$this->processResponseData($tmpTableName, array_merge($data, $package));

			//Проставление отметки у пакета о загруки ответа
			$resp = $this->savePersonIdentPackage(array_merge($package, array(
				'PersonIdentPackage_IsResponseRetrieved' => 2,
				'PersonIdentPackage_resDate' => date_create()->format('Y-m-d'),
				'pmUser_id' => $data['pmUser_id']
			)));
			if (!$this->isSuccessful($resp)) {
				throw new Exception($resp[0]['Error_Msg']);
			}

			if (!empty($_REQUEST['getDebug'])) {
				$this->rollbackTransaction();
			} else {
				$this->commitTransaction();
			}
		} catch(Exception $e) {
			return $this->createError($e->getCode(), $e->getMessage());
		}

		//Удаляются файлы запроса и ответа
		unlink(EXPORTPATH_IDENT_PACKAGE.$file['name']);
		unlink($file['tmp_name']);

		return array($response);
	}

	/**
	 * Сохранение данных из импортируемого файла во временную таблицу
	 * @param array $responseData
	 * @return string
	 * @throws Exception
	 */
	function saveResponseDataInTmpTable($responseData) {
		$configFields = array(
			'FAM' => 'varchar(60)',
			'IM' => 'varchar(60)',
			'OT' => 'varchar(60)',
			'DR' => 'date',
			'W' => 'int',
			'SS' => 'varchar(14)',
			'DOCTP' => 'int',
			'DOCS' => 'varchar(10)',
			'DOCN' => 'varchar(30)',
			'OPDOC' => 'int',
			'SPOL' => 'varchar(10)',
			'NPOL' => 'varchar(19)',
			'ENP' => 'varchar(16)',
			'DIN' => 'date',
			'DOUT' => 'date',
			'EERP' => 'varchar(604)',
			'REPL' => 'varchar(1534)',
			'RQ' => 'varchar(34)',	//Реестровый номер МО
			'ROKATO' => 'int',
			'RQOGRN' => 'varchar(94)',
			'ROPDOC' => 'int',
			'RSPOL' => 'varchar(10)',
			'RNPOL' => 'varchar(19)',
			'RENP' => 'varchar(16)',
			'RDBEG' => 'date',
			'RDEND' => 'date',
			'NREC' => 'int',
		);

		$tmpTableName = '#tmp'.time();

		$tableFieldFn = function($field, $type){return "$field $type";};
		$tableFieldsStr = implode(",\n", array_map($tableFieldFn, array_keys($configFields), $configFields));
		$createTmpTableQuery = "
			declare @Error_Code bigint = null
			declare @Error_Message varchar(4000) = ''
			set nocount on
			begin try
				IF OBJECT_ID(N'tempdb..{$tmpTableName}', N'U') IS NOT NULL
					DROP TABLE {$tmpTableName};
	
				create table {$tmpTableName} (
					{$tableFieldsStr},
					PersonIdentPackage_id bigint,
					PersonIdentPackagePos_id bigint,
					Person_id bigint,
					PersonEvn_id bigint,
					Server_id bigint,
					processed int
				)
			end try
			begin catch
				set @Error_Code = error_number()
				set @Error_Message = error_message()
			end catch
			set nocount off
			select @Error_Code as Error_Code, @Error_Message as Error_Msg
		";

		$insertValuesFn = function($fields, $params){
			return array_map(function($field) use($params) {
				$value = isset($params[$field])?trim($params[$field]):null;
				return (!empty($value) || $value === '0')?"'".str_replace("'", "''", $value)."'":'null';
			}, $fields);
		};

		$insertQuery = function($tmpTableName, $fields, $values){
			return "
				declare @Error_Code bigint = null
				declare @Error_Message varchar(4000) = ''
				set nocount on
				begin try
					insert into {$tmpTableName}
					({$fields})
					values
					{$values}
				end try
				begin catch
					set @Error_Code = error_number()
					set @Error_Message = error_message()
				end catch
				set nocount off
				select @Error_Code as Error_Code, @Error_Message as Error_Msg
			";
		};

		$execInsertQuery = function($insertArr) use($tmpTableName, $configFields, $insertQuery) {
			$fields = implode(",", array_keys($configFields));
			$values = implode(",\n", $insertArr);
			$resp = $this->queryResult($insertQuery($tmpTableName, $fields, $values));
			if (!is_array($resp)) {
				return $this->createError('Ошибка при заполенении временной таблицы данными из файла');
			}
			return $resp;
		};

		$resp = $this->queryResult($createTmpTableQuery);
		if (!$this->isSuccessful($resp)) {
			throw new Exception($resp[0]['Error_Msg']);
		}

		$insertArr = array();
		foreach($responseData as $index => $item) {
			$item['NREC'] = $index + 1;
			$insertArr[] = "(".implode(",", $insertValuesFn(array_keys($configFields), $item)).")";
			if (count($insertArr) == 200) {
				$resp = $execInsertQuery($insertArr);
				if (!$this->isSuccessful($resp)) {
					throw new Exception($resp[0]['Error_Msg']);
				}
				$insertArr = array();
			}
		}

		if (count($insertArr) > 0) {
			$resp = $execInsertQuery($insertArr);
			if (!$this->isSuccessful($resp)) {
				throw new Exception($resp[0]['Error_Msg']);
			}
		}

		return $tmpTableName;
	}

	/**
	 * Идентификация записей, импортируемых из файла
	 * @param string $tmpTableName
	 * @param array $data
	 * @throws Exception
	 */
	function identResponseData($tmpTableName, $data) {
		$params = array(
			'PersonIdentPackage_id' => $data['PersonIdentPackage_id'],
		);
		$query = "
			declare @PersonIdentPackage_id bigint = :PersonIdentPackage_id
			declare @Error_Code bigint = null
			declare @Error_Message varchar(4000) = ''
			set nocount on
			begin try
				update {$tmpTableName}
				set PersonIdentPackage_id = pipp.PersonIdentPackage_id,
					PersonIdentPackagePos_id = pipp.PersonIdentPackagePos_id,
					Person_id = pipp.Person_id,
					PersonEvn_id = pipp.PersonEvn_id,
					Server_id = pipp.Server_id
				from
					{$tmpTableName} pl
					cross apply(
						select top 1
							pipp.PersonIdentPackage_id,
							pipp.PersonIdentPackagePos_id,
							pe.Person_id,
							pe.PersonEvn_id,
							pe.Server_id
						from 
							v_PersonIdentPackagePos pipp with(nolock)
							inner join v_Person_all pe with(nolock) on pe.Person_id = pipp.Person_id
							inner join v_Sex Sex with(nolock) on Sex.Sex_id = pe.Sex_id
							left join v_Document d with(nolock) on d.Document_id = pe.Document_id
							left join v_DocumentType dt with(nolock) on DT.DocumentType_id = d.DocumentType_id
							left join v_Polis p with(nolock) on p.Polis_id = pe.Polis_id
							left join v_PolisType pt with(nolock) on pt.PolisType_id = p.PolisType_id
						where
							pe.Person_SurName like pl.FAM
							and isnull(pe.Person_FirName,'') like isnull(pl.IM,'')
							and isnull(pe.Person_SecName,'') like isnull(pl.OT,'')
							and pe.Person_BirthDay = pl.DR
							and Sex.Sex_fedid = pl.W
							and isnull(pe.Person_Snils,'') = isnull(pl.SS,'')
							and isnull(dt.DocumentType_Code,0) = isnull(pl.DOCTP,0)
							and isnull(d.Document_Ser,'') = isnull(pl.DOCS,0)
							and isnull(d.Document_Num,'') = isnull(pl.DOCN,0)
							and isnull(pt.PolisType_CodeF008,0) = isnull(pl.OPDOC,0)
							and isnull(p.Polis_Ser,'') = isnull(pl.SPOL,'')
							and isnull(p.Polis_Num,'') = isnull(pl.NPOL,'')
							and isnull(pe.Person_EdNum,'') = isnull(pl.ENP,'')
							and pipp.PersonIdentPackagePos_identDT = pl.DIN
							and pipp.PersonIdentPackagePos_identDT = pl.DOUT
							and pe.PersonEvn_insDT <= pl.DIN
						order by
							pe.PersonEvn_insDT desc
					) pipp
				where
					pl.PersonEvn_id is null
			end try
			begin catch
				set @Error_Code = error_number()
				set @Error_Message = error_message()
			end catch
			set nocount off
			select @Error_Code as Error_Code, @Error_Message as Error_Msg
		";
		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			throw new Exception('Ошибка при поиске периодики');
		}
		if (!$this->isSuccessful($resp)) {
			throw new Exception($resp[0]['Error_Msg']);
		}
	}

	/**
	 * @param string $tmpTableName
	 * @param array $data
	 * @throws Exception
	 */
	function processResponseData($tmpTableName, $data) {
		$this->load->model('Person_model');

		$createError = function($code, $descr) {
			return array(
				'code' => $code,
				'descr' => $descr
			);
		};

		$getErrorsFromRepl = function($repl) use($createError) {
			return array_map(function($error) use($createError) {
				$arr = explode("=", $error);
				if (count($arr) == 1) {
					return $createError('-1', $arr[0]);
				}
				return $createError($arr[0], $arr[1]);
			}, array_filter(explode(";", $repl)));
		};

		$setIdentStateQuery = "
			declare
				@PersonIdentPackagePos_id bigint = :PersonIdentPackagePos_id,
				@PersonIdentState_id bigint = :PersonIdentState_id,
				@pmUser_id bigint = :pmUser_id,
				@dt datetime = dbo.tzGetDate(),
				@Error_Code bigint,
				@Error_Message varchar(4000);
			set nocount on;
			begin try
				update
					PersonIdentPackagePos with(rowlock)
				set
					PersonIdentState_id = @PersonIdentState_id,
					PersonIdentPackagePos_updDT = @dt,
					PersonIdentPackagePos_REPL = :PersonIdentPackagePos_REPL,
					pmUser_updID = @pmUser_id
				where
					PersonIdentPackagePos_id = @PersonIdentPackagePos_id;
					
				if @PersonIdentState_id = 1
				begin
					declare @Person_id bigint = (
						select top 1 pl.Person_id
						from {$tmpTableName} pl
						where pl.PersonIdentPackagePos_id = @PersonIdentPackagePos_id
					)
					declare @Polis_begDate date = (
						select top 1 pl.RDBEG as Polis_begDate
						from {$tmpTableName} pl
						where pl.PersonIdentPackagePos_id = @PersonIdentPackagePos_id
					)
					declare @ActualPolis_begDate date = (
						select top 1 p.Polis_begDate
						from {$tmpTableName} pl
						inner join v_PersonState ps with(nolock) on ps.Person_id = pl.Person_id
						inner join v_Polis p with(nolock) on p.Polis_id = ps.Polis_id
						where p.Polis_id = ps.Polis_id
					)
					if @ActualPolis_begDate = @Polis_begDate
					update Person with(rowlock)
					set Person_IsInErz = 2
					where Person_id = @Person_id
				end
			end try
			begin catch
				set @Error_Code = error_number();
				set @Error_Message = error_message();
			end catch
			set nocount off;
			select @Error_Code as Error_Code, @Error_Message as Error_Msg
		";

		$queryResponseData = "
			select
			-- select
				pl.PersonIdentPackage_id,
				pl.PersonIdentPackagePos_id,
				pl.Person_id,
				pl.PersonEvn_id,
				pl.Server_id,
				pl.EERP,
				pl.REPL,
				pl.RQ as Orgsmo_f002smocod,
				pl.RQOGRN as Org_OGRN,
				pl.RSPOL as Polis_Ser,
				pl.RNPOL as Polis_Num,
				pl.RENP as Federal_Num,
				convert(varchar(10), pl.RDBEG, 120) as Polis_begDate,
				convert(varchar(10), pl.RDEND, 120) as Polis_endDate,
				pt.PolisType_id,
				ost.OMSSprTerr_id,
				1 as PersonIdentState_id,
				2 as Person_IsInErz
			-- end select
			from
			-- from
				{$tmpTableName} pl
				left join v_PolisType pt with(nolock) on pt.PolisType_CodeF008 = pl.ROPDOC
				left join v_OmsSprTerr ost with(nolock) on ost.OMSSprTerr_OKATO = pl.ROKATO
			-- end from
			where
			-- where
				pl.PersonEvn_id is not null
			-- end where
			order by
			-- order by
				pl.Person_id
			-- end order by
		";

		$start = 0;
		$limit = 200;
		$count = $this->getFirstResultFromQuery(getCountSQLPH($queryResponseData));
		if ($count === false) {
			throw new Exception('Ошибка при запросе данных для обновления идентификации');
		}

		while($start < $count) {
			$responseData = $this->queryResult(getLimitSQLPH($queryResponseData, $start, $limit));
			if (!is_array($responseData)) {
				throw new Exception('Ошибка при запросе данных для обновления идентификации');
			}

			foreach($responseData as $item) {
				$errors = array();

				if (!empty($item['EERP'])) {
					$errors = array_merge($errors, $getErrorsFromRepl($item['REPL']));	//Получение ошибок из файла
					$item['PersonIdentState_id'] = 2;	//Не идентифицирован
				} else {
					try {
						if (empty($item['PolisType_id'])) {
							throw new Exception("Отсутствует тип полиса: ROPDOC={$item['ROPDOC']}");
						}

						$OrgSMO_ids = $this->queryList("
							select OS.OrgSMO_id
							from v_OrgSMO OS with(nolock)
							inner join v_Org O with(nolock) on O.Org_id = OS.Org_id
							where 1=1
							and O.Org_OGRN = :Org_OGRN
							and isnull(OS.Orgsmo_f002smocod,0) = isnull(:Orgsmo_f002smocod,0)
						", $item);

						if (count($OrgSMO_ids) != 1) {
							throw new Exception("Не удалось определить страховую принадлежность: RQ={$item['Orgsmo_f002smocod']}, RQOGRN={$item['Org_OGRN']}");
						}

						$OrgSMO_ids = array(7);

						$params = array(
							'EvnType' => 'Polis',
							'pmUser_id' => $data['pmUser_id'],
							'Server_id' => $item['Server_id'],
							'session' => $data['session'],
							'Person_id' => $item['Person_id'],
							'PersonEvn_id' => $item['PersonEvn_id'],
							'PersonIdentState_id' => $item['PersonIdentState_id'],
							'OMSSprTerr_id' => $item['OMSSprTerr_id'],
							'PolisType_id' => $item['PolisType_id'],
							'PolisFormType_id' => null,
							'OrgSMO_id' => $OrgSMO_ids[0],
							'Polis_Ser' => $item['Polis_Ser'],
							'Polis_Num' => $item['Polis_Num'],
							'Federal_Num' => $item['Federal_Num'],
							'Polis_begDate' => $item['Polis_begDate'],
							'Polis_endDate' => $item['Polis_endDate'],
						);

						$this->Person_model->exceptionOnValidation = true;    //Создает исключение при ошибке
						$resp = $this->Person_model->editPersonEvnAttributeNew($params);
						if (!empty($resp[0]['Error_Msg'])) {
							//throw new Exception($resp[0]['Error_Msg']);
							throw new Exception("Не удалось обработать пересечение полисных данных");
						}
					} catch(Exception $e) {
						$errors[] = $createError(null, $e->getMessage());
						$item['PersonIdentState_id'] = 2;	//Не идентифицирован
					}
				}

				foreach($errors as $error) {
					$resp = $this->savePersonIdentPackagePosError(array(
						'PersonIdentPackagePosError_id' => null,
						'PersonIdentPackagePos_id' => $item['PersonIdentPackagePos_id'],
						'PersonIdentPackagePosError_ErrCode' => $error['code'],
						'PersonIdentPackagePosError_ErrDescr' => $error['descr'],
						'pmUser_id' => $data['pmUser_id'],
					));
					if (!$this->isSuccessful($resp)) {
						throw new Exception($resp[0]['Error_Msg']);
					}
				}

				//Обновление статуса идентификации
				$params = array(
					'PersonIdentPackagePos_id' => $item['PersonIdentPackagePos_id'],
					'PersonIdentPackagePos_REPL' => $item['REPL'],
					'PersonIdentState_id' => $item['PersonIdentState_id'],
					'pmUser_id' => $data['pmUser_id'],
				);
				$resp = $this->queryResult($setIdentStateQuery, $params);
				if (!is_array($resp)) {
					throw new Exception('Ошибка при сохранении статуса идентификации');
				}
				if (!$this->isSuccessful($resp)) {
					throw new Exception($resp[0]['Error_Msg']);
				}
			}

			$start += $limit;
		}

		//Обработка записей, по которым не пришел ответ в файле
		$ids = $this->queryList("
			select
				PersonIdentPackagePos_id
			from
				v_PersonIdentPackagePos pipp with(nolock)
			where
				pipp.PersonIdentPackage_id = :PersonIdentPackage_id
				and pipp.PersonIdentState_id is null
		", array(
			'PersonIdentPackage_id' => $data['PersonIdentPackage_id']
		));
		if (!is_array($ids)) {
			throw new Expcetion('Ошибка при получении записей, по которым не пришел ответ в файле');
		}

		foreach($ids as $id) {
			$params = array(
				'PersonIdentPackagePos_id' => $id,
				'PersonIdentPackagePos_REPL' => null,
				'PersonIdentState_id' => 2,
				'pmUser_id' => $data['pmUser_id'],
			);
			$resp = $this->queryResult($setIdentStateQuery, $params);

			$resp = $this->savePersonIdentPackagePosError(array(
				'PersonIdentPackagePosError_id' => null,
				'PersonIdentPackagePos_id' => $id,
				'PersonIdentPackagePosError_ErrCode' => null,
				'PersonIdentPackagePosError_ErrDescr' => 'Данные от ТФОМС не получены',
				'pmUser_id' => $data['pmUser_id'],
			));
			if (!$this->isSuccessful($resp)) {
				throw new Exception($resp[0]['Error_Msg']);
			}
		}
	}

	/**
	 * @param array $data
	 * @return array
	 */
	function deletePersonIdentPackage($data) {
		$params = array('PersonIdentPackage_id' => $data['PersonIdentPackage_id']);
		$response = array(array('success' => true));

		$this->beginTransaction();

		try {
			$package_dir = null;
			$package_sign = null;
			$package_name = null;
			$package_path = $this->getFirstResultFromQuery("
				select top 1 PersonIdentPackage_Name
				from v_PersonIdentPackage with(nolock)
				where PersonIdentPackage_id = :PersonIdentPackage_id
			", $params, true);
			if ($package_name === false) {
				throw new Exception('Ошибка при получении данных пакета');
			}

			if (preg_match('/^(.+)\/(\d+_\d+_\d+)\.DBF/', $package_path, $match)) {
				$package_dir = $match[1];
				$package_sign = $match[2];
				$package_name = $match[2].'.DBF';
			}

			$query = "
				declare @Error_Code int;
				declare @Error_Message varchar(4000);
				set nocount on;
				begin try
					delete PersonIdentPackagePosError
					where PersonIdentPackagePos_id in (
						select PIPP.PersonIdentPackagePos_id
						from v_PersonIdentPackagePos PIPP with(nolock)
						where PIPP.PersonIdentPackage_id = :PersonIdentPackage_id
					)
					
					update PersonIdentPackage with(rowlock)
					set PersonIdentPackage_Name = 'deleted'
					where PersonIdentPackage_id = :PersonIdentPackage_id
				end try
				begin catch
					set @Error_Code = error_number();
					set @Error_Message = error_message();
				end catch
				set nocount off;
				select @Error_Code as Error_Code, @Error_Message as Error_Msg
			";
			$resp = $this->queryResult($query, $params);
			if (!is_array($resp)) {
				throw new Exception('Ошибка при удалении записей пакета');
			}
			if (!$this->isSuccessful($resp)) {
				throw new Exception($resp[0]['Error_Msg']);
			}

			unlink(EXPORTPATH_IDENT_PACKAGE.$package_name);
			unlink(IMPORTPATH_IDENT_PACKAGE.$package_name);
		} catch (Exception $e) {
			$this->rollbackTransaction();
			return $this->createError($e->getCode(), $e->getMessage());
		}

		$this->commitTransaction();

		return $response;
	}
}