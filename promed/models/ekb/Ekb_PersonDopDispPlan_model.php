<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Perm_PersonDopDispPlan_model - модель для работы с планом диспансеризации (Екб)
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 */

require_once(APPPATH.'models/PersonDopDispPlan_model.php');

class Ekb_PersonDopDispPlan_model extends PersonDopDispPlan_model {

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Экспорт планов
	 */
	function exportPersonDopDispPlan($data) {

		set_time_limit(0);
		
		// Проверка периодов
		$resp_check = $this->queryResult("
			select
				case
					when MONTH(dcp.DispCheckPeriod_begDate) <= 3 then 1
					when MONTH(dcp.DispCheckPeriod_begDate) <= 6 then 2
					when MONTH(dcp.DispCheckPeriod_begDate) <= 9 then 3
					when MONTH(dcp.DispCheckPeriod_begDate) <= 12 then 4
				end as PLAN_Q
			from
				v_PersonDopDispPlan pddp with (nolock)
				inner join v_DispCheckPeriod dcp with (nolock) on dcp.DispCheckPeriod_id = pddp.DispCheckPeriod_id
			where
				pddp.PersonDopDispPlan_id in ('" . implode("','", $data['PersonDopDispPlan_ids']) . "')
		");
		foreach ($resp_check as &$respone) {
			if ($respone['PLAN_Q'] < $data['PersonDopDispPlanExport_Quart']) {
				return array('Error_Msg' => 'Периоды в выгружаемом плане не могут быть меньше квартала загрузки');
			}
		}
		
		// производится проверка на уникальность порядкового номера пакета по МО, отчетный период, порядковый номер пакета
		$resp_check = $this->queryResult("
			select top 1
				pddpe.PersonDopDispPlanExport_id,
				convert(varchar(10), pddpe.PersonDopDispPlanExport_insDT, 104) + ' ' + convert(varchar(5), pddpe.PersonDopDispPlanExport_insDT, 108) as PersonDopDispPlanExport_Date,
				pu.PMUser_Name
			from
				v_PersonDopDispPlanExport pddpe (nolock)
				inner join v_pmUserCache pu (nolock) on pu.PMUser_id = pddpe.pmUser_insID
			where
				pddpe.PersonDopDispPlanExport_DownloadQuarter = :PersonDopDispPlanExport_DownloadQuarter
				and pddpe.Lpu_id = :Lpu_id
				and pddpe.PersonDopDispPlanExport_PackNum = :PersonDopDispPlanExport_PackNum
		", array(
			'PersonDopDispPlanExport_PackNum' => $data['PacketNumber'],
			'Lpu_id' => $data['Lpu_id'],
			'PersonDopDispPlanExport_DownloadQuarter' => $data['PersonDopDispPlanExport_Quart'],
		));
		if (!empty($resp_check[0]['PersonDopDispPlanExport_id'])) {
			return array('Error_Msg' => "Порядковый номер пакета должен быть уникальным в отчетном периоде. Пакет с указанным номером был создан {$resp_check[0]['PMUser_Name']} {$resp_check[0]['PersonDopDispPlanExport_Date']}. Измените номер пакета или удалите ранее созданный файл экспорта");
		}
		
		$links = array();
		$res = $this->_exportPersonDopDispPlan($data);
		if (!empty($res['Error_Msg'])) {
			return $res;
		} else {
			$links[] = $res['link'];
		}
		
		return array('Error_Msg' => '', 'link' => $links);
	}

	/**
	 * Экспорт планов
	 */
	function _exportPersonDopDispPlan($data) {

		$X = 'DL';

		$LpuInfo = $this->queryResult("
			select top 1 Lpu_f003mcod from v_Lpu (nolock) where Lpu_id = :Lpu_id
		", array(
			'Lpu_id' => $data['Lpu_id']
		));

		if (!empty($LpuInfo[0]['Lpu_f003mcod'])) {
			$Ni = $LpuInfo[0]['Lpu_f003mcod'];
		} else {
			$Ni = '';
		}
		
		$year = $this->getFirstResultFromQuery("select PersonDopDispPlan_Year from v_PersonDopDispPlan (nolock) where PersonDopDispPlan_id = :PersonDopDispPlan_id", array(
			'PersonDopDispPlan_id' => $data['PersonDopDispPlan_ids'][0]
		));
		
		$YY = mb_substr($year, 2, 2);
		$N = $data['PacketNumber'];
		$Q = $data['PersonDopDispPlanExport_Quart'];

		$filename = $X.$Ni.'_'.$YY.$Q.$N;
		
		$zipfilename = $filename . '.zip';
		$xmlfilename = $filename . '.xml';

		$out_dir = "pddp_xml_".time()."_".$data['Lpu_id'];
		if(!is_dir(EXPORTPATH_REGISTRY.$out_dir)) mkdir( EXPORTPATH_REGISTRY.$out_dir );

		$zipfilepath = EXPORTPATH_REGISTRY.$out_dir."/".$zipfilename;
		$xmlfilepath = EXPORTPATH_REGISTRY.$out_dir."/".$xmlfilename;
		
		$this->beginTransaction();

		// Создаём файл
		$resp_pddpe = $this->savePersonDopDispPlanExport(array(
			'PersonDopDispPlanExport_FileName' => $filename,
			'PersonDopDispPlanExport_PackNum' => $data['PacketNumber'],
			'OrgSmo_id' => null,
			'Lpu_id' => $data['Lpu_id'],
			'PersonDopDispPlanExport_Year' => $year,
			'PersonDopDispPlanExport_Month' => null,
			'PersonDopDispPlanExport_DownloadQuarter' => $data['PersonDopDispPlanExport_Quart'],
			'pmUser_id' => $data['pmUser_id']
		));

		if (empty($resp_pddpe[0]['PersonDopDispPlanExport_id'])) {
			$this->rollbackTransaction();
			return array('Error_Msg' => 'Ошибка сохранения данных экспорта');
		}
			
		// Блокируем файл
		$this->setPersonDopDispPlanExportIsUsed(array(
			'PersonDopDispPlanExport_id' => $resp_pddpe[0]['PersonDopDispPlanExport_id'],
			'PersonDopDispPlanExport_IsUsed' => 1
		));
		
		// достаём данные
		$resp = $this->queryResult("
			select
				row_number() over(ORDER BY ppl.PlanPersonList_id) as N_ZAP,
				ppl.PlanPersonList_id,
				pe.BDZ_id as ID,
				case
					when MONTH(dcp.DispCheckPeriod_begDate) <= 3 then 1
					when MONTH(dcp.DispCheckPeriod_begDate) <= 6 then 2
					when MONTH(dcp.DispCheckPeriod_begDate) <= 9 then 3
					when MONTH(dcp.DispCheckPeriod_begDate) <= 12 then 4
				end as PLAN_Q
			from
				v_PersonDopDispPlan pddp with (nolock)
				inner join v_PlanPersonList ppl with (nolock) on pddp.PersonDopDispPlan_id = ppl.PersonDopDispPlan_id
				left join v_PlanPersonListStatus pddps (nolock) on pddps.PlanPersonListStatus_id = PPL.PlanPersonListStatus_id
				left join v_DispCheckPeriod dcp with (nolock) on dcp.DispCheckPeriod_id = pddp.DispCheckPeriod_id
				inner join v_PersonState ps with (nolock) on ps.Person_id = ppl.Person_id
				inner join v_Person pe with (nolock) on pe.Person_id = ps.Person_id
			where
				pddp.PersonDopDispPlan_id in ('" . implode("','", $data['PersonDopDispPlan_ids']) . "')
				and pe.BDZ_id is not null
				and ISNULL(PDDPS.PlanPersonListStatusType_id, 1) = 1
		");
			
		foreach ($data['PersonDopDispPlan_ids'] as $PersonDopDispPlan_id) {
			// Сохраняем линки
			$this->savePersonDopDispPlanLink(array(
				'PersonDopDispPlan_id' => $PersonDopDispPlan_id,
				'PersonDopDispPlanExport_id' => $resp_pddpe[0]['PersonDopDispPlanExport_id'],
				'pmUser_id' => $data['pmUser_id']
			));
		}

		// формируем XML
		$this->load->library('parser');

		foreach ($resp as &$respone) {
			// для всех записей сущностей «Человек в плане» устанавливается статус «Отправлен в ТФОМС»
			$this->setPlanPersonListStatus(array(
				'PlanPersonList_id' => $respone['PlanPersonList_id'],
				'PersonDopDispPlanExport_id' => $resp_pddpe[0]['PersonDopDispPlanExport_id'],
				'PlanPersonList_ExportNum' => $respone['N_ZAP'],
				'PlanPersonListStatusType_id' => 2, // Отправлена в ТФОМС
				'pmUser_id' => $data['pmUser_id']
			));

			array_walk($respone, 'ConvertFromUTF8ToWin1251', true);
		}

		$xml = "<?xml version=\"1.0\" encoding=\"windows-1251\" standalone=\"yes\"?>\r\n" . $this->parser->parse_ext('export_xml/export_dispplan_ekb', array(
				'FILENAME' => $filename,
				'CODE_ORG' => $Ni,
				'YEAR' => $year,
				'QUART' => $data['PersonDopDispPlanExport_Quart'],
				'NRECORDS' => count($resp),
				'ZAP' => $resp
			), true, false, array(), true);

		file_put_contents($xmlfilepath, $xml);

		// запаковываем
		$zip = new ZipArchive();
		$zip->open($zipfilepath, ZIPARCHIVE::CREATE);
		$zip->AddFile($xmlfilepath, $xmlfilename);
		$zip->close();
		
		// Пишем ссылку
		$query = "update PersonDopDispPlanExport set PersonDopDispPlanExport_DownloadLink = :PersonDopDispPlanExport_DownloadLink where PersonDopDispPlanExport_id = :PersonDopDispPlanExport_id";
		$this->db->query($query, array(
			'PersonDopDispPlanExport_id' => $resp_pddpe[0]['PersonDopDispPlanExport_id'],
			'PersonDopDispPlanExport_DownloadLink' => $zipfilepath
		));
		
		// Снимаем блокировку
		$this->setPersonDopDispPlanExportIsUsed(array(
			'PersonDopDispPlanExport_id' => $resp_pddpe[0]['PersonDopDispPlanExport_id'],
			'PersonDopDispPlanExport_IsUsed' => null
		));
		
		$this->commitTransaction();

		// отдаём юзверю
		return array('Error_Msg' => '', 'link' => $zipfilepath);
	}

	/**
	 * Импорт данных плана
	 */
	function importPersonDopDispPlan($data) {

		$LpuInfo = $this->queryResult("
			select top 1 Lpu_f003mcod from v_Lpu (nolock) where Lpu_id = :Lpu_id
		", array(
			'Lpu_id' => $data['Lpu_id']
		));

		if (!empty($LpuInfo[0]['Lpu_f003mcod'])) {
			$Lpu_f003mcod = $LpuInfo[0]['Lpu_f003mcod'];
		} else {
			return false;
		}
		
		$upload_path = './'.IMPORTPATH_ROOT.$data['Lpu_id'].'/';
		$allowed_types = explode('|','zip|xml');

		set_time_limit(0);

		if ( !isset($_FILES['File'])) {
			return array('Error_Msg' => 'Не выбран файл!');
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

			return array('Error_Msg' => $message);
		}

		// Тип файла разрешен к загрузке?
		$x = explode('.', $_FILES['File']['name']);
		$file_data['file_ext'] = end($x);
		if ( !in_array(strtolower($file_data['file_ext']), $allowed_types) ) {
			return array('Error_Msg' => 'Данный тип файла не разрешен.');
		}

		// Правильно ли указана директория для загрузки?
		if ( !@is_dir($upload_path) ) {
			mkdir( $upload_path );
		}

		if ( !@is_dir($upload_path) ) {
			return array('Error_Msg' => 'Путь для загрузки файлов некорректен.');
		}

		// Имеет ли директория для загрузки права на запись?
		if ( !is_writable($upload_path) ) {
			return array('Error_Msg' => 'Загрузка файла невозможна из-за прав пользователя.');
		}

		$fileList = array();

		if ( strtolower($file_data['file_ext']) == 'xml' ) {
			$fileList[] = $_FILES['File']['name'];

			if ( !move_uploaded_file($_FILES["File"]["tmp_name"], $upload_path.$_FILES['File']['name']) ) {
				return array('Error_Msg' => 'Не удаётся переместить файл.');
			}
		}
		else {
			$zip = new ZipArchive;

			if ( $zip->open($_FILES["File"]["tmp_name"]) === TRUE ) {
				for ( $i = 0; $i < $zip->numFiles; $i++ ) {
					$fileList[] = $zip->getNameIndex($i);
				}

				$zip->extractTo( $upload_path );
				$zip->close();
			}

			unlink($_FILES["File"]["tmp_name"]);
		}

		$xmlfile = '';

		libxml_use_internal_errors(true);

		foreach ( $fileList as $filename ) {
			$xmlfile = $filename;
		}

		if ( empty($xmlfile) ) {
			return array('Error_Msg' => 'Файл не является файлом для импорта ошибок плана проф. мероприятий.');
		}
		
		if (!preg_match('/PDN'.$Lpu_f003mcod.'\_([0-9]{4})/ui', $xmlfile, $match)) {
			return array('Error_Msg' => 'Ошибка при загрузке файла. Имя файла не соответствует установленному формату. Выберите другой файл.');
		}

		$xml_string = file_get_contents($upload_path . $xmlfile);

		// Структура должна соответствовать xsd схеме для файла-ошибок.
		// xsd пока нет
		/*$xml = new DOMDocument();
		$xml->loadXML($xml_string);
		$xsd_tpl = $_SERVER['DOCUMENT_ROOT'].'/documents/xsd/pddp_err.xsd';
		if (!$xml->schemaValidate($xsd_tpl)) {
			return array('Error_Msg' => 'Ошибка при загрузке файла. Структура файла не соответствует установленному формату. Выберите другой файл.');
		}
		unset($xml);*/

		$xml = new SimpleXMLElement($xml_string);
		
		$fname = $xml->FILENAME->__toString();

		// o Поиск в БД записи сущности «Файл экспорта». по тегу FILENAME
		if (preg_match('/PDN'.$Lpu_f003mcod.'\_([0-9]{2})([0-9]{1})([0-9]{1})/ui', $fname, $match)) {

			$PersonDopDispPlanExport_Year = '20'.$match[1];
			$PersonDopDispPlanExport_DownloadQuarter = $match[2];
			$PersonDopDispPlanExport_PackNum = $match[3];

			$resp_pddpe = $this->queryResult("
				select top 1
					pddpe.PersonDopDispPlanExport_id
				from
					v_PersonDopDispPlanExport pddpe (nolock)
					inner join v_Lpu l (nolock) on l.Lpu_id = pddpe.Lpu_id 
				where
					l.Lpu_f003mcod = :Lpu_f003mcod
					and l.Lpu_id = :Lpu_id
					and pddpe.PersonDopDispPlanExport_Year = :PersonDopDispPlanExport_Year
					and pddpe.PersonDopDispPlanExport_DownloadQuarter = :PersonDopDispPlanExport_DownloadQuarter
					and pddpe.PersonDopDispPlanExport_PackNum = :PersonDopDispPlanExport_PackNum
			", array(
				'Lpu_f003mcod' => $Lpu_f003mcod,
				'Lpu_id' => $data['Lpu_id'],
				'PersonDopDispPlanExport_Year' => $PersonDopDispPlanExport_Year,
				'PersonDopDispPlanExport_DownloadQuarter' => $PersonDopDispPlanExport_DownloadQuarter,
				'PersonDopDispPlanExport_PackNum' => $PersonDopDispPlanExport_PackNum
			));

			if (!empty($resp_pddpe[0]['PersonDopDispPlanExport_id'])) {
				// o Если запись сущности найдена, то устанавливается дата импорта=текущая дата.
				$this->db->query("update PersonDopDispPlanExport with (rowlock) set PersonDopDispPlanExport_impDate = dbo.tzGetDate() where PersonDopDispPlanExport_id = :PersonDopDispPlanExport_id", array(
					'PersonDopDispPlanExport_id' => $resp_pddpe[0]['PersonDopDispPlanExport_id']
				));

				foreach ( $xml->REP as $oneoshib ) {
					// Для записей из плана (поиск по порядковому номеру по записям сущности «Человек в плане» значения тега N_ZAP)
					$N_ZAP = $oneoshib->N_ZAP->__toString();
					$CODE_ERP = $oneoshib->CODE_ERP->__toString();
					$COMMENT = $oneoshib->COMMENT->__toString();

					// ищем запись
					$resp_ppl = $this->queryResult("
						select
							PlanPersonList_id
						from
							v_PlanPersonList (nolock)
						where
							PersonDopDispPlanExport_id = :PersonDopDispPlanExport_id
							and PlanPersonList_ExportNum = :PlanPersonList_ExportNum
					", array(
						'PersonDopDispPlanExport_id' => $resp_pddpe[0]['PersonDopDispPlanExport_id'],
						'PlanPersonList_ExportNum' => $N_ZAP
					));

					if (!empty($resp_ppl[0]['PlanPersonList_id'])) {
						if ($CODE_ERP == 2) {
							// Сохранить ошибки
							$this->saveExportErrorPlanDD(array(
								'PersonDopDispPlanExport_id' => $resp_pddpe[0]['PersonDopDispPlanExport_id'],
								'ExportErrorPlanDDType_id' => null,
								'ExportErrorPlanDD_Description' => $COMMENT,
								'PlanPersonList_id' => $resp_ppl[0]['PlanPersonList_id'],
								'pmUser_id' => $data['pmUser_id']
							));
							
							// статус ошибка
							$this->setPlanPersonListStatus(array(
								'PlanPersonList_id' => $resp_ppl[0]['PlanPersonList_id'],
								'PlanPersonListStatusType_id' => 4,
								'pmUser_id' => $data['pmUser_id']
							));
						} else {
							// статус принято ТФОМС
							$this->setPlanPersonListStatus(array(
								'PlanPersonList_id' => $resp_ppl[0]['PlanPersonList_id'],
								'PlanPersonListStatusType_id' => 3,
								'pmUser_id' => $data['pmUser_id']
							));
						}
					}
				}
			} else {
				// Иначе показать сообщение об ошибке «Файл экспорта не найден или удален»
				return array('Error_Msg' => 'Файл экспорта не найден или удален');
			}
		} else {
			return array('Error_Msg' => 'Ошибка при загрузке файла. Имя или структура файла не соответствует установленному формату. Выберите другой файл');
		}

		return array('Error_Msg' => '');
	}

	/**
	 * Возвращает список ошибок экспортов планов
	 */
	function loadExportErrorPlanDDList($data) {

		$filter = '(1 = 1)';
		$params = array();

		if (!empty($data['PersonDopDispPlanExport_id'])) {
			$params['PersonDopDispPlanExport_id'] = $data['PersonDopDispPlanExport_id'];
			$filter .= ' and eepdd.PersonDopDispPlanExport_id = :PersonDopDispPlanExport_id';
		}

		$query = "
			select
				-- select
				eepdd.ExportErrorPlanDD_id,
				ppl.PlanPersonList_ExportNum,
				ISNULL(PS.Person_SurName, '') + ISNULL(' ' + PS.Person_FirName, '') + ISNULL(' ' + PS.Person_SecName, '') as Person_Fio,
				null as ExportErrorPlanDDType_Code,
				eepdd.ExportErrorPlanDD_Description as ExportErrorPlanDDType_Name
				-- end select
			from
				-- from
				v_ExportErrorPlanDD eepdd with (nolock)
				inner join v_PlanPersonList ppl with (nolock) on ppl.PlanPersonList_id = eepdd.PlanPersonList_id
				inner join v_PersonState ps with (nolock) on ps.Person_id = ppl.Person_id
				-- end from
			where
				-- where
				{$filter}
				-- end where
			order by
				-- order by
				eepdd.ExportErrorPlanDD_insDT desc
				-- end order by
		";

		return $this->getPagingResponse($query, $params, $data['start'], $data['limit'], true);
	}
}