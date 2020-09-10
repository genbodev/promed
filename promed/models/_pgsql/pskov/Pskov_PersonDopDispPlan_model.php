<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Pskov_PersonDopDispPlan_model - модель для работы с планом диспансеризации (Псков)
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2018 Swan Ltd.
 */

require_once(APPPATH.'models/_pgsql/PersonDopDispPlan_model.php');

class Pskov_PersonDopDispPlan_model extends PersonDopDispPlan_model {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Экспорт планов
	 */
	public function exportPersonDopDispPlan($data) {
		set_time_limit(0);

		$response = array(
			'Error_Msg' => '',
			'link' => '',
			'count' => 0,
		);
		if($data['ExportByOrgSMO_flag']=='true') {
			$res = $this->_exportPersonDopDispPlanSMO($data);
			$response['Error_Msg'] = $res['Error_Msg'];
			$response['link'] = implode('|', $res['link']);
			$response['count'] = $res['count'];
		}
		else
		try {
			// Поиск среди файлов экспорта по МО записей с такой же отчетной датой и с пустой датой импорта ошибок. Если запись найдена, то ошибка Пользователю:
			// «В Системе уже есть файл экспорта на указанную отчетную дату, по которому не производился импорт ответа ТФОМС. Для продолжения необходимо удалить
			// ранее добавленный файл или загрузить ответ от ТФОМС». Дальнейшие действия не выполняются. Форма экспорта остается открытой. 
			$checkResult = $this->getFirstResultFromQuery("
				select PersonDopDispPlanExport_id as \"PersonDopDispPlanExport_id\"
				from v_PersonDopDispPlanExport 
				where
					PersonDopDispPlanExport_expDate = :PersonDopDispPlanExport_expDate
					and PersonDopDispPlanExport_impDate is null
					and Lpu_id = :Lpu_id
			", array(
				'PersonDopDispPlanExport_expDate' => $data['PersonDopDispPlanExport_expDate'],
				'Lpu_id' => $data['Lpu_id'],
			), true);

			if ( !empty($checkResult) ) {
				throw new Exception('В Системе уже есть файл экспорта на указанную отчетную дату, по которому не производился импорт ответа ТФОМС. Для продолжения необходимо удалить ранее добавленный файл или загрузить ответ от ТФОМС');
			}

			$res = $this->_exportPersonDopDispPlanTFOMS($data);

			if ( !empty($res['Error_Msg']) ) {
				throw new Exception($res['Error_Msg']);
			}

			$response['link'] = $res['link'];
			$response['count'] = 1;
		}
		catch ( Exception $e ) {
			$response['Error_Msg'] = $e->getMessage();
		}

		return $response;
	}

	/**
	 * Экспорт планов ТФОМС
	 */
	protected function _exportPersonDopDispPlanTFOMS($data) {
		$response = array(
			'Error_Msg' => '',
			'link' => '',
		);

		try {
			$this->beginTransaction();

			$Ni = $this->getFirstResultFromQuery("
				select Lpu_f003mcod  as \"Lpu_f003mcod\" from v_Lpu  where Lpu_id = :Lpu_id
			", array(
				'Lpu_id' => $data['Lpu_id']
			), true);

			$YYYYMMDD = str_replace('-', '', $data['PersonDopDispPlanExport_expDate']);

			$filename = 'D' . $Ni . $YYYYMMDD;

			$csvfilename = $filename . '.csv';
			$zipfilename = $filename . '.zip';

			$out_dir = "pddp_csv_" . time() . "_" . $data['Lpu_id'];

			if ( !is_dir(EXPORTPATH_REGISTRY . $out_dir) ) {
				mkdir(EXPORTPATH_REGISTRY . $out_dir);
			}

			$csvfilepath = EXPORTPATH_REGISTRY . $out_dir . "/" . $csvfilename;
			$zipfilepath = EXPORTPATH_REGISTRY . $out_dir . "/" . $zipfilename;

			// Создаём файл
			$resp = $this->savePersonDopDispPlanExport(array(
				'Lpu_id' => $data['Lpu_id'],
				'PersonDopDispPlanExport_FileName' => $filename,
				'PersonDopDispPlanExport_PackNum' => 1,
				'PersonDopDispPlanExport_IsUsed' => 1,
				'PersonDopDispPlanExport_expDate' => $data['PersonDopDispPlanExport_expDate'],
				'PersonDopDispPlanExport_Year' => $data['PersonDopDispPlanExport_Year'],
				'pmUser_id' => $data['pmUser_id'],
			));

			if ( empty($resp[0]['PersonDopDispPlanExport_id']) ) {
				throw new Exception('Ошибка сохранения данных экспорта');
			}

			$filter = "";
			$PersonDopDispPlanExport_id = $resp[0]['PersonDopDispPlanExport_id'];
			$queryParams = array('MOcod'=>$Ni);

			// Достаём данные
			$resp = $this->queryResult("
				select
					pt.PolisType_CodeF008 as \"VPOLIS\",
					p.Polis_Ser as \"SPOLIS\",
					p.Polis_Num as \"NPOLIS\",
					ps.Person_SurName as \"FAM\",
					ps.Person_FirName as \"IM\",
					ps.Person_SecName as \"OT\",
					to_char(ps.Person_BirthDay, 'YYYYMMDD') as \"DR\",
					rtrim(COALESCE(pi.PersonInfo_InternetPhone, ps.Person_Phone)) as \"TEL\",
					:MOcod as \"MO_CODE\",
					pddp.PersonDopDispPlan_Year as \"YEAR\",
					case
						when date_part('MONTH',dcp.DispCheckPeriod_begDate) <= 3 then 1
						when date_part('MONTH',dcp.DispCheckPeriod_begDate) <= 6 then 2
						when date_part('MONTH',dcp.DispCheckPeriod_begDate) <= 9 then 3
						when date_part('MONTH',dcp.DispCheckPeriod_begDate) <= 12 then 4
					end as \"QUART\",
					to_char(pm.attachdate, 'YYYYMMDD') as \"ATTACH_DATE\",
					pm.snils as \"MP_SNILS\",
					null as \"ERR\",
					ps.Person_id as \"Person_id\",
					ppl.PlanPersonList_id as \"PlanPersonList_id\"
				from
					v_PersonDopDispPlan pddp 
					inner join v_PlanPersonList ppl  on pddp.PersonDopDispPlan_id = ppl.PersonDopDispPlan_id
					left join v_PlanPersonListStatus pddps  on pddps.PlanPersonListStatus_id = ppl.PlanPersonListStatus_id
					left join v_DispCheckPeriod dcp  on dcp.DispCheckPeriod_id = pddp.DispCheckPeriod_id
					inner join v_PersonState ps  on ps.Person_id = ppl.Person_id
					left join v_Polis p  on p.Polis_id = ps.Polis_id
					left join v_PolisType pt  on pt.PolisType_id = p.PolisType_id
					left join v_OrgSMO os  on os.OrgSMO_id = p.OrgSMO_id
					LEFT JOIN LATERAL (
						select pc.PersonCard_LpuBegDate as attachdate, msf.Person_Snils as snils
						from v_PersonCard pc  
							left join v_MedStaffRegion ms  on ms.LpuRegion_id = pc.LpuRegion_id
							left join v_MedStaffFact msf  on msf.MedStaffFact_id = ms.MedStaffFact_id
						where pc.Person_id=ps.Person_id AND pc.PersonCard_endDate is null AND pc.LpuAttachType_id=1
                        limit 1
					) pm ON true
					LEFT JOIN LATERAL (
						select PersonInfo_InternetPhone
						from v_PersonInfo 
						where Person_id = ps.Person_id
						order by PersonInfo_id desc
                        limit 1
					) pi ON true
				where
					pddp.PersonDopDispPlan_id in ('" . implode("','", $data['PersonDopDispPlan_ids']) . "')
					{$filter}
			", $queryParams);

			foreach ( $data['PersonDopDispPlan_ids'] as $PersonDopDispPlan_id ) {
				// Сохраняем линки
				$this->savePersonDopDispPlanLink(array(
					'PersonDopDispPlan_id' => $PersonDopDispPlan_id,
					'PersonDopDispPlanExport_id' => $PersonDopDispPlanExport_id,
					'pmUser_id' => $data['pmUser_id'],
				));
			}

			$f = fopen($csvfilepath, 'a');

			foreach ( $resp as $row ) {
				// для всех записей сущностей «Человек в плане» устанавливается статус «Отправлен в ТФОМС»
				$this->setPlanPersonListStatus(array(
					'PlanPersonList_id' => $row['PlanPersonList_id'],
					'PersonDopDispPlanExport_id' => $PersonDopDispPlanExport_id,
					'PlanPersonListStatusType_id' => 2, // Отправлена в ТФОМС
					'pmUser_id' => $data['pmUser_id'],
				));

				// смотрим соотсветсвует ли телефон формату (Если номер определяется и соответствует маске, тогда выгружаем,если нет - не выгружаем.)
				if ( mb_strlen($row['TEL']) >= 10 && preg_match('/^[0-9]+$/ui', $row['TEL']) ) {
					$row['TEL'] = mb_substr($row['TEL'], mb_strlen($row['TEL']) - 10);
				} else {
					$row['TEL'] = '';
				}

				array_pop($row);
				array_walk($row, 'ConvertFromUTF8ToWin1251', true);

				fputs($f, '"' . implode('";"', $row) . '"' . PHP_EOL);
			}

			fclose($f);

			// Запаковываем
			$zip = new ZipArchive();
			$zip->open($zipfilepath, ZIPARCHIVE::CREATE);
			$zip->AddFile($csvfilepath, $csvfilename);
			$zip->close();

			unlink($csvfilepath);

			// Пишем ссылку
			$this->queryResult("
					update PersonDopDispPlanExport
					set PersonDopDispPlanExport_DownloadLink = :PersonDopDispPlanExport_DownloadLink
					where PersonDopDispPlanExport_id = :PersonDopDispPlanExport_id;
				select '' as \"Error_Code\", '' as \"Error_Msg\";
			", array(
				'PersonDopDispPlanExport_id' => $PersonDopDispPlanExport_id,
				'PersonDopDispPlanExport_DownloadLink' => $zipfilepath,
			));
			
			// Снимаем блокировку
			$this->setPersonDopDispPlanExportIsUsed(array(
				'PersonDopDispPlanExport_id' => $PersonDopDispPlanExport_id,
				'PersonDopDispPlanExport_IsUsed' => null,
			));

			$this->commitTransaction();

			$response['link'] = $zipfilepath;
		}
		catch ( Exception $e ) {
			$this->rollbackTransaction();
			$response['Error_Msg'] = $e->getMessage();
		}

		return $response;
	}

	/**
	 * Экспорт планов по СМО
	 */
	protected function _exportPersonDopDispPlanSMO($data) {
		$response = array(
			'Error_Msg' => '',
			'link' => array(),
			'count' => 0,
		);

		try {
			$smo_list=array();

			if(empty($data['OrgSMO_id'])) {
				$sql = "SELECT 
					os.OrgSMO_id as \"SMO_id\",
					Orgsmo_f002smocod as \"SMO_number\"
				FROM v_OrgSMO os 
				WHERE os.KLRgn_id=60";
				$resp = $this->queryResult($sql,array());
				foreach($resp as $res) {
					$smo_list[]=$res['SMO_id'];
				}
			} else {
				$smo_list=explode(',', $data['OrgSMO_id']);
			}

			foreach($smo_list as $smo_id) {
				//берем данные по этой СМО
				$queryParams = array('OrgSMO_id'=>$smo_id, 'Lpu_id' => $data['Lpu_id']);
				$filter = array();
				$sql = "select
					pt.PolisType_CodeF008 as \"VPOLIS\",
					p.Polis_Ser as \"SPOLIS\",
					p.Polis_Num as \"NPOLIS\",
					ps.Person_SurName as \"FAM\",
					ps.Person_FirName as \"IM\",
					ps.Person_SecName as \"OT\",
					to_char(ps.Person_BirthDay, 'YYYYMMDD') as \"DR\",
					rtrim(COALESCE(pi.PersonInfo_InternetPhone, ps.Person_Phone)) as \"TEL\",
					l.Lpu_f003mcod as \"MO_CODE\",
					pddp.PersonDopDispPlan_Year as \"YEAR\",
					case
						when date_part('MONTH', dcp.DispCheckPeriod_begDate) <= 3 then 1
						when date_part('MONTH', dcp.DispCheckPeriod_begDate) <= 6 then 2
						when date_part('MONTH', dcp.DispCheckPeriod_begDate) <= 9 then 3
						when date_part('MONTH', dcp.DispCheckPeriod_begDate) <= 12 then 4
					end as \"QUART\",
					to_char(pm.attachdate, 'YYYYMMDD') as \"ATTACH_DATE\",
					pm.snils as \"MP_SNILS\",
					null as \"ERR\",
					ps.Person_id as \"Person_id\"
				from
					v_PersonDopDispPlan pddp 
					inner join v_PlanPersonList ppl  on pddp.PersonDopDispPlan_id = ppl.PersonDopDispPlan_id
					left join v_PlanPersonListStatus pddps  on pddps.PlanPersonListStatus_id = ppl.PlanPersonListStatus_id
					left join v_DispCheckPeriod dcp  on dcp.DispCheckPeriod_id = pddp.DispCheckPeriod_id
					inner join v_PersonState ps  on ps.Person_id = ppl.Person_id
					left join v_Polis p  on p.Polis_id = ps.Polis_id
					left join v_PolisType pt  on pt.PolisType_id = p.PolisType_id
					left join v_OrgSMO os  on os.OrgSMO_id = p.OrgSMO_id
					left join v_Lpu l  on l.Lpu_id = :Lpu_id
					LEFT JOIN LATERAL (
						select pc.PersonCard_LpuBegDate as attachdate, msf.Person_Snils as snils
						from v_PersonCard pc  
							left join v_MedStaffRegion ms  on ms.LpuRegion_id = pc.LpuRegion_id
							left join v_MedStaffFact msf  on msf.MedStaffFact_id = ms.MedStaffFact_id
						where pc.Person_id=ps.Person_id AND pc.PersonCard_endDate is null AND pc.LpuAttachType_id=1
                        limit 1
					) pm ON true
					LEFT JOIN LATERAL (
						select PersonInfo_InternetPhone
						from v_PersonInfo 
						where Person_id = ps.Person_id
						order by PersonInfo_id desc
                        limit 1
					) pi ON true
				where
					pddp.PersonDopDispPlan_id in ('" . implode("','", $data['PersonDopDispPlan_ids']) . "')
					AND os.OrgSMO_id = :OrgSMO_id and pddps.PlanPersonListStatusType_id=3
				";
				$smodata = $this->queryResult($sql, $queryParams);

				if( count($smodata) > 0) { //файл создавать только если есть записи на эту СМО !
					$SMO_info = $this->queryResult("
						select Orgsmo_f002smocod as \"SMO_number\", OrgSMO_Nick  as \"OrgSMO_Nick\" from v_OrgSMO  where OrgSMO_id = :OrgSMO_id
						", array('OrgSMO_id' => $smo_id), true);
					//данные получены, работаем с файлом:
					$YYYYMMDD = str_replace('-', '', $data['PersonDopDispPlanExport_expDate']);
					$filename = 'D' . $SMO_info[0]['SMO_number'] . $YYYYMMDD;
					$csvfilename = $filename . '.csv';
					$zipfilename = $filename . '.zip';

					$out_dir = "pddp_csv_" . time() . "_" . $data['Lpu_id'];

					if ( !is_dir(EXPORTPATH_REGISTRY . $out_dir) ) {
						mkdir(EXPORTPATH_REGISTRY . $out_dir);
					}

					$csvfilepath = EXPORTPATH_REGISTRY . $out_dir . "/" . $csvfilename;
					$zipfilepath = EXPORTPATH_REGISTRY . $out_dir . "/" . $zipfilename;

					// Создаём файл
					$f = fopen($csvfilepath, 'a');
					foreach ( $smodata as $row ) {
						if ( mb_strlen($row['TEL']) >= 10 && preg_match('/^[0-9]+$/ui', $row['TEL']) ) {
							$row['TEL'] = mb_substr($row['TEL'], mb_strlen($row['TEL']) - 10);
						} else {
							$row['TEL'] = '';
						}

						array_walk($row, 'ConvertFromUTF8ToWin1251', true);

						fputs($f, '"' . implode('";"', $row) . '"' . PHP_EOL);
					}
					fclose($f);

					// Запаковываем
					$zip = new ZipArchive();
					$zip->open($zipfilepath, ZIPARCHIVE::CREATE);
					$zip->AddFile($csvfilepath, $csvfilename);
					$zip->close();

					unlink($csvfilepath);

					$response['link'][] = $zipfilepath;
					$response['count'] += 1;
				}
			}
		}
		catch ( Exception $e ) {
			$response['Error_Msg'] = $e->getMessage();
		}

		return $response;
	}

	/**
	 * Импорт данных плана
	 */
	public function importPersonDopDispPlan($data) {
		set_time_limit(0);

		$allowed_types = explode('|','csv');
		$response = array(
			'Error_Msg' => ''
		);
		$upload_path = './' . IMPORTPATH_ROOT . $data['Lpu_id'] . '/';

		try {
			$this->beginTransaction();

			if ( !isset($_FILES['File'])) {
				throw new Exception('Не выбран файл!');
			}

			if ( !is_uploaded_file($_FILES['File']['tmp_name']) ) {
				$error = (!isset($_FILES['File']['error'])) ? 4 : $_FILES['File']['error'];

				switch ( $error ) {
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

				throw new Exception($message);
			}

			$Lpu_f003mcod = $this->getFirstResultFromQuery("
				select Lpu_f003mcod  as \"Lpu_f003mcod\" from v_Lpu  where Lpu_id = :Lpu_id
			", array(
				'Lpu_id' => $data['Lpu_id']
			));

			if ( $Lpu_f003mcod === false ) {
				throw new Exception('Ошибка при определении кода МО.');
			}

			// Проверка имени выбранного файла (ODNiYYYYMMDD.CSV): имя файла должно содержать константу «OD»; Если имя файла не содержит необходимую константу,
			// то сообщение об ошибке: «Ошибка загрузки файла. Имя файла должно начинаться на «OD». ОК.»
			if ( substr($_FILES['File']['name'], 0, 2) != 'OD' ) {
				throw new Exception('Ошибка загрузки файла. Имя файла должно начинаться на «OD».');
			}

			// Тип файла разрешен к загрузке?
			$x = explode('.', $_FILES['File']['name']);
			$file_data['file_ext'] = strtolower(end($x));

			if ( !in_array($file_data['file_ext'], $allowed_types) ) {
				throw new Exception('Данный тип файла не разрешен.');
			}

			// Правильно ли указана директория для загрузки?
			if ( !@is_dir($upload_path) ) {
				mkdir($upload_path);
			}

			if ( !@is_dir($upload_path) ) {
				throw new Exception('Путь для загрузки файлов некорректен.');
			}

			// Имеет ли директория для загрузки права на запись?
			if ( !is_writable($upload_path) ) {
				throw new Exception('Загрузка файла невозможна из-за прав пользователя.');
			}

			$fileName = $_FILES['File']['name'];

			if ( !move_uploaded_file($_FILES["File"]["tmp_name"], $upload_path . $fileName) ) {
				throw new Exception('Не удаётся переместить файл.');
			}

			if ( !preg_match('/^OD([0-9]{6})([0-9]{8})\.CSV/ui', $fileName, $match) ) {
				throw new Exception('Ошибка при загрузке файла. Имя файла не соответствует установленному формату. Выберите другой файл.');
			}

			if ( $match[1] != $Lpu_f003mcod ) {
				throw new Exception('Ошибка при загрузке файла. Файл импорта содержит данные по другой МО.');
			}

			$record = $this->getFirstRowFromQuery("
				select 
					PersonDopDispPlanExport_id as \"PersonDopDispPlanExport_id\",
					case when PersonDopDispPlanExport_impDate is not null then 1 else 0 end as \"importDone\"
				from v_PersonDopDispPlanExport 
				where
					Lpu_id = :Lpu_id
					and PersonDopDispPlanExport_FileName = :PersonDopDispPlanExport_FileName
				limit 1
			", array(
				'Lpu_id' => $data['Lpu_id'],
				'PersonDopDispPlanExport_FileName' => 'D' . $match[1] . $match[2],
			));

			if ( $record === false || !is_array($record) || count($record) == 0 ) {
				throw new Exception('Файл экспорта не найден или удален');
			}
			else if ( $record['importDone'] == 1 ) {
				throw new Exception('Импорт уже был произведен');
			}

			// Если запись сущности найдена, то устанавливается дата импорта=текущая дата.
			$rsp = $this->getFirstRowFromQuery("
					update PersonDopDispPlanExport
					set PersonDopDispPlanExport_impDate = dbo.tzGetDate()
					where PersonDopDispPlanExport_id = :PersonDopDispPlanExport_id;
				select '' as \"Error_Code\", '' as \"Error_Msg\";
			", array(
				'PersonDopDispPlanExport_id' => $record['PersonDopDispPlanExport_id'],
			));

			if ( $rsp === false ) {
				throw new Exception('Ошибка при обновлении даты импорта');
			}
			else if ( !empty($rsp['Error_Msg']) ) {
				throw new Exception($rsp['Error_Msg']);
			}

			$fileHandler = fopen($upload_path . $fileName, 'r');

			while ( !feof($fileHandler) ) {
				$s = fgets($fileHandler);

				if ( empty($s) ) {
					continue;
				}

				ConvertFromWin1251ToUTF8($s, null, true);

				$s = trim($s);
				$s = trim($s, '"');

				$row = explode('";"', $s);

				if ( !is_array($row) || count($row) < 14 ) {
					continue;
				}

				if ( empty($row[14]) && !empty($row[3]) && !empty($row[4]) && !empty($row[6]) ) {
					$PlanPersonList_id = $this->getFirstResultFromQuery("
						select 
							ppl.PlanPersonList_id as \"PlanPersonList_id\"
						from
							v_PlanPersonList ppl 
							inner join v_PersonState ps  on ps.Person_id = ppl.Person_id
						where
							ppl.PersonDopDispPlanExport_id = :PersonDopDispPlanExport_id
							and ps.Person_Surname = :Person_Surname
							and ps.Person_Firname = :Person_Firname
							and COALESCE(ps.Person_Secname, '') = COALESCE(:Person_Secname, '')
							and ps.Person_Birthday = :Person_Birthday
						limit 1
					", array(
						'PersonDopDispPlanExport_id' => $record['PersonDopDispPlanExport_id'],
						'Person_Surname' => !empty($row[3]) ? $row[3] : null,
						'Person_Firname' => !empty($row[4]) ? $row[4] : null,
						'Person_Secname' => !empty($row[5]) ? $row[5] : null,
						'Person_Birthday' => !empty($row[6]) ? $row[6] : null,
					));

					if ( $PlanPersonList_id !==false && !empty($PlanPersonList_id) ) {
						$row[14] = $PlanPersonList_id;
					}
				}

				if ( empty($row[14]) ) {
					throw new Exception('Не удалось определить идентификатор записи');
				}

				if ( $row[13] == 'Ок' ) {
					// устанавливается статус «Принят ТФОМС», если для записи вернулся ответ «Ок».
					$this->setPlanPersonListStatus(array(
						'PlanPersonList_id' => $row[14],
						'PlanPersonListStatusType_id' => 3,
						'pmUser_id' => $data['pmUser_id'],
					));

					if ( $rsp === false ) {
						throw new Exception('Ошибка при изменении статуса записи');
					}
					else if ( !empty($rsp[0]['Error_Msg']) ) {
						throw new Exception($rsp[0]['Error_Msg']);
					}
				}
				else {
					// изменить статус на «Ошибки», если вернулся ответ отличный от «Ок» или ответ не вернулся
					$rsp = $this->saveExportErrorPlanDD(array(
						'PersonDopDispPlanExport_id' => $record['PersonDopDispPlanExport_id'],
						'ExportErrorPlanDDType_id' => null,
						'ExportErrorPlanDD_Description' => $row[13],
						'PlanPersonList_id' => $row[14],
						'pmUser_id' => $data['pmUser_id'],
					));

					if ( $rsp === false ) {
						throw new Exception('Ошибка при добавлении ошибки');
					}
					else if ( !empty($rsp[0]['Error_Msg']) ) {
						throw new Exception($rsp[0]['Error_Msg']);
					}

					// статус ошибка
					$rsp = $this->setPlanPersonListStatus(array(
						'PlanPersonList_id' => $row[14],
						'PlanPersonListStatusType_id' => 4,
						'pmUser_id' => $data['pmUser_id'],
					));

					if ( $rsp === false ) {
						throw new Exception('Ошибка при изменении статуса записи');
					}
					else if ( !empty($rsp[0]['Error_Msg']) ) {
						throw new Exception($rsp[0]['Error_Msg']);
					}
				}
			}

			fclose($fileHandler);

			$this->commitTransaction();
		}
		catch ( Exception $e ) {
			$this->rollbackTransaction();
			$response['Error_Msg'] = $e->getMessage();

			if ( isset($fileHandler) ) {
				fclose($fileHandler);
			}
		}

		return $response;
	}
}