<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Vologda_PersonDopDispPlan_model - модель для работы с планом диспансеризации (Вологда)
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 */

require_once(APPPATH.'models/_pgsql/PersonDopDispPlan_model.php');

class Vologda_PersonDopDispPlan_model extends PersonDopDispPlan_model {

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Возвращает список экспортов планов
	 */
	function loadPersonDopDispPlanExportList($data) {

		$filter = '(1 = 1)';
		$params = array();

		if (empty($data['PersonDopDispPlan_id']) && empty($data['PersonDopDispPlan_ids'])) {
			return array('Error_Msg' => 'Не указан идентификатор плана');
		}

		if (!empty($data['PersonDopDispPlan_id'])) {
			$params['PersonDopDispPlan_id'] = $data['PersonDopDispPlan_id'];
			$filter .= ' and pddpl.PersonDopDispPlan_id = :PersonDopDispPlan_id';
		}
		elseif (!empty($data['PersonDopDispPlan_ids'])) {
			$filter .= ' and pddpl.PersonDopDispPlan_id in ('.join(',',$data['PersonDopDispPlan_ids']).')';
		}

		if (!empty($data['PersonDopDispPlanExport_expDateRange'][0])) {
			$params['PersonDopDispPlanExport_expDate_From'] = $data['PersonDopDispPlanExport_expDateRange'][0];
			$filter .= ' and cast(pddpe.PersonDopDispPlanExport_expDate as date) >= :PersonDopDispPlanExport_expDate_From';
		}
		if (!empty($data['PersonDopDispPlanExport_expDateRange'][1])) {
			$params['PersonDopDispPlanExport_expDate_To'] = $data['PersonDopDispPlanExport_expDateRange'][1];
			$filter .= ' and cast(pddpe.PersonDopDispPlanExport_expDate as date) <= :PersonDopDispPlanExport_expDate_To';
		}

		$query = "
			select
				-- select
				pddpe.PersonDopDispPlanExport_id as \"PersonDopDispPlanExport_id\",
				pddpe.PersonDopDispPlanExport_FileName as \"PersonDopDispPlanExport_FileName\",
				to_char(pddpe.PersonDopDispPlanExport_expDate, 'DD.MM.YYYY') as \"PersonDopDispPlanExport_expDate\",
				ppl.cnt as \"PersonDopDispPlanExport_Count\",
				to_char(pddpe.PersonDopDispPlanExport_impDate, 'DD.MM.YYYY') as \"PersonDopDispPlanExport_impDate\",
				pddpe.PersonDopDispPlanExport_Year as \"PersonDopDispPlanExport_Year\",
				pddpe.PersonDopDispPlanExport_Month as \"PersonDopDispPlanExport_Month\",
				dcp.DispCheckPeriod_Name as \"PersonDopDispPlanExport_Period\",
				eepdd.cnt as \"PersonDopDispPlanExport_CountErr\",
				pddpe.PersonDopDispPlanExport_isUsed as \"PersonDopDispPlanExport_isUsed\",
				pddpe.PersonDopDispPlanExport_DownloadLink as \"PersonDopDispPlanExport_DownloadLink\",
				pddpe.PersonDopDispPlanExport_PackNum as \"PersonDopDispPlanExport_PackNum\",
				pplst.PlanPersonListStatusType_id as \"PersonDopDispPlanExportStatus_id\",
				pplst.PlanPersonListStatusType_Code as \"PersonDopDispPlanExportStatus_Code\",
				pplst.PlanPersonListStatusType_Name as \"PersonDopDispPlanExportStatus_Name\",
				pddpe.PersonDopDispPlanExport_IsExportPeriod as \"PersonDopDispPlanExport_IsExportPeriod\"
				-- end select
			from
				-- from
				v_PersonDopDispPlanLink pddpl
				inner join v_PersonDopDispPlanExport pddpe on pddpe.PersonDopDispPlanExport_id = pddpl.PersonDopDispPlanExport_id
				inner join v_DispCheckPeriod dcp on dcp.DispCheckPeriod_id = pddpe.DispCheckPeriod_id
				left join lateral (
					select
						count(*) as cnt
					from
						v_PlanPersonList ppl
						inner join v_PlanPersonListStatus ppls on ppls.PlanPersonListStatus_id = ppl.PlanPersonListStatus_id
					where
						ppl.PersonDopDispPlanExport_id = pddpe.PersonDopDispPlanExport_id
						and ppl.PersonDopDispPlan_id = pddpl.PersonDopDispPlan_id
						and ppls.PlanPersonListStatusType_id in (2,3)
					limit 1
				) ppl on true
				left join lateral (
					select
						count(*) as cnt
					from
						v_ExportErrorPlanDD eepdd
					where
						eepdd.PersonDopDispPlanExport_id = pddpe.PersonDopDispPlanExport_id
					limit 1
				) eepdd on true
				left join lateral (
					select case 
						when ppl.cnt > 0 and exists(
							select * from v_PlanPersonList ppl
							inner join v_PlanPersonListStatus ppls on ppls.PlanPersonListStatus_id = ppl.PlanPersonListStatus_id
							where ppl.PersonDopDispPlanExport_id = pddpe.PersonDopDispPlanExport_id and ppls.PlanPersonListStatusType_id = 2
						) then 2
						when ppl.cnt > 0 and not exists(
							select * from v_PlanPersonList ppl
							inner join v_PlanPersonListStatus ppls on ppls.PlanPersonListStatus_id = ppl.PlanPersonListStatus_id
							where ppl.PersonDopDispPlanExport_id = pddpe.PersonDopDispPlanExport_id and ppls.PlanPersonListStatusType_id <> 3
						) then 3
					end as PlanPersonListStatusType_id
					limit 1
				) pddpes on true
				left join v_PlanPersonListStatusType pplst on pplst.PlanPersonListStatusType_id = pddpes.PlanPersonListStatusType_id
				-- end from
			where
				-- where
				{$filter}
				-- end where
			order by
				-- order by
				pddpe.PersonDopDispPlanExport_expDate desc
				-- end order by
		";

		return $this->getPagingResponse($query, $params, $data['start'], $data['limit'], true);
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
				eepdd.ExportErrorPlanDD_id as \"ExportErrorPlanDD_id\",
				ppl.PlanPersonList_ExportNum as \"PlanPersonList_ExportNum\",
				dt.DispClass_Name as \"DispClass_Name\",
				MONTH(dcp.DispCheckPeriod_begDate) as \"PersonDopDispPlan_Month\",
				coalesce(PS.Person_SurName, '') || coalesce(' ' || PS.Person_FirName, '') || coalesce(' ' || PS.Person_SecName, '') as \"Person_Fio\",
				eepddt.ExportErrorPlanDDType_Code as \"ExportErrorPlanDDType_Code\",
				eepdd.ExportErrorPlanDD_Description as \"ExportErrorPlanDDType_Name\"
				-- end select
			from
				-- from
				v_ExportErrorPlanDD eepdd
				left join v_ExportErrorPlanDDType eepddt on eepddt.ExportErrorPlanDDType_id = eepdd.ExportErrorPlanDDType_id
				left join v_PlanPersonList ppl on ppl.PlanPersonList_id = eepdd.PlanPersonList_id
				left join v_PersonState ps on ps.Person_id = ppl.Person_id
				left join v_PersonDopDispPlan pddp on pddp.PersonDopDispPlan_id = ppl.PersonDopDispPlan_id
				left join v_DispCheckPeriod dcp on dcp.DispCheckPeriod_id = pddp.DispCheckPeriod_id
				left join v_DispClass dt on dt.DispClass_id = pddp.DispClass_id
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

	/**
	 * Экспорт планов
	 */
	function exportPersonDopDispPlan($data) {

		set_time_limit(0);

		$endDtSelect = $data['PersonDopDispPlanExport_IsExportPeriod'] != 2
			? "DispCheckPeriod_endDate"
			: "cast(year(DispCheckPeriod_endDate) as varchar) + '-12-31'";

		if ($data['ignoreCheck'] != 2) {
			$resp_check = $this->queryList("
				with dates as (
                      select 
                          DispCheckPeriod_begDate as begDt,
                          {$endDtSelect} as endDt
                      from DispCheckPeriod
                      where DispCheckPeriod_id = :DispCheckPeriod_id
                ),
				t as (
					select
						dc.DispClass_id,
						dc.DispClass_Name,
						dcp.DispCheckPeriod_begDate,
						rtrim(replace(dcp.DispCheckPeriod_Name, cast(year(dcp.DispCheckPeriod_begDate) as varchar), '')) as DispCheckPeriod_Name
					from DispCheckPeriod dcp
					inner join DispClass dc on dc.DispClass_id in (1,5,28)
					where 
						dcp.PeriodCap_id = 4
						and dcp.DispCheckPeriod_begDate >= (select begDt from dates)
						and dcp.DispCheckPeriod_begDate <= (select endDt from dates)
						and not exists (
							select PersonDopDispPlan_id 
							from v_PersonDopDispPlan pddp 
							where 
								pddp.DispCheckPeriod_id = dcp.DispCheckPeriod_id
								and pddp.DispClass_id = dc.DispClass_id 
								and pddp.Lpu_id = :Lpu_id
						)
				)
				
				select distinct 
					' - ' || DispClass_Name || ': ' || (
						select  string_agg(DispCheckPeriod_Name,', ')
						from t t1
						where t.DispClass_id = t1.DispClass_id
                    ) as \"DispCheckPeriod_Name\",
					DispClass_id as \"DispClass_id\"
				from t 
				order by DispClass_id
			", $data);
			if (count($resp_check)) {
				if (count($resp_check) == 1) $msg = "Не найдены планы для типа профилактического мероприятия: <br>";
				else $msg = "Не найдены планы для типов профилактических мероприятий: <br>";
				return [
					'success' => true,
					'Error_Msg' => 'YesNo',
					'Error_Code' => '100',
					'Alert_Msg' => $msg.join(" <br>", $resp_check)." <br>Продолжить формирование файла экспорта?"
				];
			}
		}

		$data['PersonDopDispPlan_ids'] = $this->queryList("
			with dates as (
				  select 
					  DispCheckPeriod_begDate as begDt,
					  {$endDtSelect} as endDt
				  from DispCheckPeriod
				  where DispCheckPeriod_id = :DispCheckPeriod_id
			)
			
			select PersonDopDispPlan_id as \"PersonDopDispPlan_id\"
			from v_PersonDopDispPlan pddp
			inner join DispCheckPeriod dcp on dcp.DispCheckPeriod_id = pddp.DispCheckPeriod_id
			where 
				pddp.Lpu_id = :Lpu_id and 
				pddp.DispClass_id in (1,5,28) and 
				dcp.DispCheckPeriod_begDate >= (select begDt from dates) and 
				dcp.DispCheckPeriod_endDate <= (select endDt from dates)
		", $data);
		
		$links = array();
		
		if (!empty($data['OrgSMO_id'])) {
			$orgsmo_ids = explode(',', $data['OrgSMO_id']);
			foreach($orgsmo_ids as $orgsmo_id) {
				$data_one = $data;
				$data_one['OrgSMO_id'] = $orgsmo_id;
				$res = $this->_exportPersonDopDispPlan($data_one);
				if (!empty($res['Error_Msg'])) {
					return $res;
				} else {
					$links[] = $res['link'];
				}
			}
		}
		else {
			$res = $this->_exportPersonDopDispPlan($data);
			if (!empty($res['Error_Msg'])) {
				return $res;
			} else {
				$links[] = $res['link'];
			}
		}
		
		return array('Error_Msg' => '', 'link' => $links);
	}

	/**
	 * Экспорт планов
	 */
	function _exportPersonDopDispPlan($data) {

		$D = 'D';

		$LpuInfo = $this->queryResult("
			select Lpu_f003mcod  as \"Lpu_f003mcod\" from v_Lpu  where Lpu_id = :Lpu_id limit 1
		", array(
			'Lpu_id' => $data['Lpu_id']
		));

		$Pi = 'M';
		if (!empty($LpuInfo[0]['Lpu_f003mcod'])) {
			$Ni = $LpuInfo[0]['Lpu_f003mcod'];
		} else {
			$Ni = '';
		}
		$YYYY = $data['PersonDopDispPlanExport_Year'];
		$R = $data['PacketNumber'];

		if (!empty($data['OrgSMO_id'])) {
			$Pp = 'S';
			$Np = '';
			$OrgSMOInfo = $this->queryResult("
				select Orgsmo_f002smocod  as \"Orgsmo_f002smocod\" from v_OrgSMO  where OrgSMO_id = :OrgSMO_id limit 1
			", array(
				'OrgSMO_id' => $data['OrgSMO_id']
			));
			if (!empty($OrgSMOInfo[0]['Orgsmo_f002smocod'])) {
				$Np = $OrgSMOInfo[0]['Orgsmo_f002smocod'];
			}
		} else {
			$Pp = 'F';
			$Np = '35';
		}

		$filename = $D.'-'.$Pi.$Ni.'-'.$Pp.$Np.'-'.$YYYY.'-'.$R;
		$zipfilename = $filename . '.zip';
		$xmlfilename = $filename . '.xml';

		$out_dir = "pddp_xml_".time()."_".$data['Lpu_id'];
		if(!is_dir(EXPORTPATH_REGISTRY.$out_dir)) mkdir( EXPORTPATH_REGISTRY.$out_dir );

		$zipfilepath = EXPORTPATH_REGISTRY.$out_dir."/".$zipfilename;
		$xmlfilepath = EXPORTPATH_REGISTRY.$out_dir."/".$xmlfilename;

		$filter = "";
		$queryParams = array();
		if (!empty($data['OrgSMO_id'])) {
			$filter .= " and p.OrgSMO_id = :OrgSMO_id";
			$queryParams['OrgSMO_id'] = $data['OrgSMO_id'];

			// Если поле «СМО» не пустое, то в файл попадают записи со статусом «Принято ТФОМС»
			$filter .= " and pddps.PlanPersonListStatusType_id = 3";
		} else {
			// Иначе, в файл должны попадать записи со статусом «Новые».
			$filter .= " and COALESCE(PDDPS.PlanPersonListStatusType_id, 1) = 1";
		}
		
		$this->beginTransaction();

		// Создаём файл
		$resp_pddpe = $this->savePersonDopDispPlanExport(array(
			'PersonDopDispPlanExport_FileName' => $filename,
			'PersonDopDispPlanExport_PackNum' => $data['PacketNumber'],
			'OrgSmo_id' => $data['OrgSMO_id'],
			'Lpu_id' => $data['Lpu_id'],
			'PersonDopDispPlanExport_Year' => $data['PersonDopDispPlanExport_Year'],
			'DispCheckPeriod_id' => $data['DispCheckPeriod_id'],
			'PersonDopDispPlanExport_IsExportPeriod' => $data['PersonDopDispPlanExport_IsExportPeriod'],
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
				ppl.PlanPersonList_id as \"PlanPersonList_id\",
				ppl.PlanPersonList_id as \"N_ZAP\",
				pddp.DispClass_id as \"DispClass_id\",
				'' as \"PERSON_ID\",
				os.Orgsmo_f002smocod as \"SMOCOD\",
				ps.Person_SurName as \"FAM\",
				ps.Person_FirName as \"IM\",
				case when ps.Person_SecName is not null then ps.Person_SecName else 'НЕТ' end as \"OT\",
				case when ps.Sex_id = 2 then 2 else 1 end as \"W\",
				to_char(ps.Person_BirthDay, 'YYYY-MM-DD') as \"DR\",
				p.Polis_Num as \"NPOLIS\",
				rtrim(COALESCE(ps.Person_Phone, pi.PersonInfo_InternetPhone)) as \"PHONE\",
				pddp.PersonDopDispPlan_Year as \"YEAR\",
				to_char(pd.PersonDisp_begDate, 'YYYY-MM-DD') as \"DAT_INC\",
				pdvcount.D_PERIOD as \"D_PERIOD\",
				case
					when pd.Person_Snils is not null then 
						LEFT(pd.Person_Snils, 3) + '-' + 
						SUBSTRING(pd.Person_Snils, 4, 3) + '-' + 
						SUBSTRING(pd.Person_Snils, 7, 3) + ' ' + 
						RIGHT(pd.Person_Snils, 2)
					else ''
				end as \"MCODE\",
				to_char(lapdv.PersonDispVizit_NextFactDate, 'YYYY-MM-DD') as \"DAT_PREV\",
				date_part('MONTH', dcp.DispCheckPeriod_begDate) as \"MES\",
				0 as \"MDP\",
				null as \"DS\",
				case
					when pddp.DispClass_id = 28 then '3' -- диспансерное наблюдение
					when pddp.DispClass_id = 1 then '4' -- двн
					when pddp.DispClass_id = 5 then '5' -- повн
				end as \"DISP_TYP\"
			from
				v_PersonDopDispPlan pddp 
				inner join v_PlanPersonList ppl  on pddp.PersonDopDispPlan_id = ppl.PersonDopDispPlan_id
				left join v_PlanPersonListStatus pddps  on pddps.PlanPersonListStatus_id = PPL.PlanPersonListStatus_id
				left join v_DispCheckPeriod dcp  on dcp.DispCheckPeriod_id = pddp.DispCheckPeriod_id
				inner join v_PersonState ps  on ps.Person_id = ppl.Person_id
				inner join v_Person pe  on pe.Person_id = ps.Person_id
				left join v_Polis p  on p.Polis_id = ps.Polis_id
				left join v_OrgSMO os  on os.OrgSMO_id = p.OrgSMO_id
				LEFT JOIN LATERAL (
					select PersonInfo_InternetPhone
					from v_PersonInfo 
					where Person_id = ps.Person_id
					order by PersonInfo_id desc
                    limit 1
				) pi ON true
				LEFT JOIN LATERAL (
					select
						pd.PersonDisp_id,
						pd.PersonDisp_begDate,
						mp.Person_Snils
					from v_PersonDisp pd
					left join v_MedPersonal mp with on pd.MedPersonal_id = mp.MedPersonal_id and pd.Lpu_id = mp.Lpu_id
					where pd.Person_id = ps.Person_id
					order by PersonDisp_id desc
                    limit 1
				) pd ON true
				LEFT JOIN LATERAL (
					select pdv.PersonDispVizit_NextFactDate 
					from v_PersonDispVizit pdv 
					where 
						pdv.PersonDisp_id = pd.PersonDisp_id and 
						pdv.PersonDispVizit_NextFactDate < dcp.DispCheckPeriod_begDate
					order by pdv.PersonDispVizit_NextFactDate desc
                    limit 1
				) lapdv ON true
				LEFT JOIN LATERAL (
					select count(*) as D_PERIOD
					from v_PersonDispVizit pdv 
					where 
						pdv.PersonDisp_id = pd.PersonDisp_id and 
						pdv.PersonDispVizit_NextDate > dcp.DispCheckPeriod_begDate
				) pdvcount ON true
			where
				pddp.PersonDopDispPlan_id in ('" . implode("','", $data['PersonDopDispPlan_ids']) . "')
				{$filter}
		", $queryParams);
			
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

			// смотрим соотсветсвует ли телефон формату (Если номер определяется и соответствует маске, тогда выгружаем,если нет - не выгружаем.)
			if (mb_strlen($respone['PHONE']) >= 10 && preg_match('/^[0-9]+$/ui', $respone['PHONE'])) {
				$respone['PHONE'] = '+7'.mb_substr($respone['PHONE'], mb_strlen($respone['PHONE']) - 10);
			} else {
				$respone['PHONE'] = '';
			}

			if (preg_match('/[А-Яа-я]+/ui', $respone['OT']) == 0) {
				$respone['OT'] = 'НЕТ';
			}

			if ($respone['DispClass_id'] != 28) {
				$respone['DAT_INC'] = null;
				$respone['D_PERIOD'] = null;
				$respone['MCODE'] = null;
				$respone['DAT_PREV'] = null;
			}

			array_walk($respone, 'ConvertFromUTF8ToWin1251', true);
		}

		$xml = "<?xml version=\"1.0\" encoding=\"windows-1251\" standalone=\"yes\"?>\r\n" . $this->parser->parse_ext('export_xml/export_dispplan_vologda', array(
				'ZGLV' => array(array(
					'FILENAME' => $filename,
					'CODE_MO' => $Ni,
					'YEAR' => $data['PersonDopDispPlanExport_Year'],
					'R' => $R
				)),
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
			select Lpu_f003mcod  as \"Lpu_f003mcod\" from v_Lpu  where Lpu_id = :Lpu_id limit 1
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

		if (!preg_match('/D\-F35\-M'.$Lpu_f003mcod.'\-.*/ui', $xmlfile, $match)) {
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

		$fname = $xml->ZGLV->FILENAME->__toString();

		// o Поиск в БД записи сущности «Файл экспорта». по тегу FILENAME
		if (preg_match('/D\-F35\-M'.$Lpu_f003mcod.'\-([0-9]{4})\-([0-9]{1})/ui', $fname, $match)) {

			$PersonDopDispPlanExport_Year = $match[1];
			$PersonDopDispPlanExport_PackNum = $match[2];

			$resp_pddpe = $this->queryResult("
				select 
					pddpe.PersonDopDispPlanExport_id as \"PersonDopDispPlanExport_id\"
				from
					v_PersonDopDispPlanExport pddpe 
					inner join v_Lpu l  on l.Lpu_id = pddpe.Lpu_id 
				where
					l.Lpu_f003mcod = :Lpu_f003mcod
					and l.Lpu_id = :Lpu_id
					and pddpe.PersonDopDispPlanExport_Year = :PersonDopDispPlanExport_Year
					and pddpe.PersonDopDispPlanExport_PackNum = :PersonDopDispPlanExport_PackNum
                limit 1
			", array(
				'Lpu_f003mcod' => $Lpu_f003mcod,
				'Lpu_id' => $data['Lpu_id'],
				'PersonDopDispPlanExport_Year' => $PersonDopDispPlanExport_Year,
				'PersonDopDispPlanExport_PackNum' => $PersonDopDispPlanExport_PackNum
			));

			if (!empty($resp_pddpe[0]['PersonDopDispPlanExport_id'])) {
				// o Если запись сущности найдена, то устанавливается дата импорта=текущая дата.
				$this->db->query("update PersonDopDispPlanExport set PersonDopDispPlanExport_impDate = dbo.tzGetDate() where PersonDopDispPlanExport_id = :PersonDopDispPlanExport_id", array(
					'PersonDopDispPlanExport_id' => $resp_pddpe[0]['PersonDopDispPlanExport_id']
				));

				foreach ( $xml->ZAP as $onezap ) {
					// Для записей из плана (поиск по порядковому номеру по записям сущности «Человек в плане» значения тега N_ZAP)
					$N_ZAP = $onezap->N_ZAP->__toString();
					$PROV = $onezap->PROV;

					// ищем запись
					$resp_ppl = $this->queryResult("
						select
							PlanPersonList_id as \"PlanPersonList_id\"
						from
							v_PlanPersonList 
						where
							PersonDopDispPlanExport_id = :PersonDopDispPlanExport_id
							and PlanPersonList_ExportNum = :PlanPersonList_ExportNum
					", array(
						'PersonDopDispPlanExport_id' => $resp_pddpe[0]['PersonDopDispPlanExport_id'],
						'PlanPersonList_ExportNum' => $N_ZAP
					));

					if (!empty($resp_ppl[0]['PlanPersonList_id'])) {
						if (!empty($PROV)) {
							$COMENTZ = $PROV->COMMENTZ->__toString();
							if (!empty($COMENTZ)) {
								// Сохранить ошибки
								$ENP = $PROV->ENP->__toString();
								$MO = $PROV->COMMENTZ->__toString();
								$SMO = $PROV->SMO->__toString();
								$stat = $PROV->ENDR->__toString() == 0 ? 'действующая' : 'полис закрыт';
								$err_msg = "ЕНП: {$ENP}, Статус записи в регистре: {$stat}, СМО: {$SMO}, МО: {$MO}, Ошибки: {$COMENTZ}";
								$this->saveExportErrorPlanDD(array(
									'PersonDopDispPlanExport_id' => $resp_pddpe[0]['PersonDopDispPlanExport_id'],
									'ExportErrorPlanDDType_id' => null,
									'ExportErrorPlanDD_Description' => $err_msg,
									'PlanPersonList_id' => $resp_ppl[0]['PlanPersonList_id'],
									'pmUser_id' => $data['pmUser_id']
								));
							}

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
	 * Автоматичекское заполнение плана
	 * @param $data
	 */
	function autoCreatePlan($data) {

		$this->sendImportResponse();

		$person_list = $this->queryList("
			select 
				pd.Person_id as \"Person_id\"
			from v_PersonDisp pd
			inner join v_PersonState ps on pd.Person_id = ps.Person_id			
			inner join v_Lpu vl on pd.Lpu_id = vl.Lpu_id
			where 
				pd.Lpu_id = :Lpu_id 
				and pd.PersonDisp_begDate <= :DispCheckPeriod_endDate  
				and coalesce(pd.PersonDisp_endDate, :DispCheckPeriod_begDate) >= :DispCheckPeriod_begDate 
				and (
					vl.MesAgeLpuType_id != 1 -- Детские больницы не могут выгружать планы с таким ограничением #PROMEDWEB-10551
					or dbo.Age2(ps.Person_BirthDay, :DispCheckPeriod_begDate) >= 18

				)
				and exists (
					select pdv.PersonDispVizit_id
					from v_PersonDispVizit pdv
					where 
						pdv.PersonDisp_id = pd.PersonDisp_id
						and pdv.PersonDispVizit_NextDate between :DispCheckPeriod_begDate and :DispCheckPeriod_endDate
						and pdv.PersonDispVizit_NextDate <= coalesce(pd.PersonDisp_endDate, :DispCheckPeriod_endDate)
						and pdv.PersonDispVizit_NextFactDate is null
					limit 1
				)
				and not exists (
					select pddp.PersonDopDispPlan_id
					from v_PersonDopDispPlan pddp 
					inner join v_PlanPersonList ppl on pddp.PersonDopDispPlan_id = ppl.PersonDopDispPlan_id
					inner join v_PlanPersonListStatus ppls on ppls.PlanPersonListStatus_id = ppl.PlanPersonListStatus_id
					where 
						pddp.DispCheckPeriod_id = :DispCheckPeriod_id
						and ppl.Person_id = pd.Person_id
						and ppls.PlanPersonListStatusType_id != 5
					limit 1
				)
		", $data);

		if (!is_array($person_list)) return;

		foreach ($person_list as $person_id) {
			$this->saveNewPlanPersonList([
				'PlanPersonList_id' => null,
				'PersonDopDispPlan_id' => $data['PersonDopDispPlan_id'],
				'Person_id' => $person_id,
				'Lpu_id' => $data['lpu_id'],
				'pmUser_id' => $data['pmUser_id']
			]);
		}
	}

	/**
	 * Разрыв соединения c клиентом после запуска
	 */
	function sendImportResponse() {
		ignore_user_abort(true);

		if (function_exists('fastcgi_finish_request')) {
			echo json_encode(array("success" => "true", 'background' => 'true'));
			if (session_id()) session_write_close();
			fastcgi_finish_request();
		} else {
			ob_start();
			echo json_encode(array("success" => "true", 'background' => 'true'));

			$size = ob_get_length();

			header("Content-Length: $size");
			header("Content-Encoding: none");
			header("Connection: close");

			ob_end_flush();
			ob_flush();
			flush();

			if (session_id()) session_write_close();
		}
	}
}