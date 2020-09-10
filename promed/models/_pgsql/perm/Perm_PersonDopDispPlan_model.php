<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Perm_PersonDopDispPlan_model - модель для работы с планом диспансеризации (Пермь)
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

class Perm_PersonDopDispPlan_model extends PersonDopDispPlan_model {

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

    /**
     * Экспорт планов
     * @throws Exception
     */
	function exportPersonDopDispPlan($data) {

		set_time_limit(0);
		$params = [
            "PersonDopDispPlanExport_PackNum" => $data["PacketNumber"],
            "Lpu_id" => $data["Lpu_id"],
            "PersonDopDispPlanExport_Year" => $data["PersonDopDispPlanExport_Year"],
            "PersonDopDispPlanExport_Month" => $data["PersonDopDispPlanExport_Month"],
        ];

		// производится проверка на уникальность порядкового номера пакета по МО, отчетный период, порядковый номер пакета
        $query = "
			select
				pddpe.PersonDopDispPlanExport_id as \"PersonDopDispPlanExport_id\",
				to_char(pddpe.PersonDopDispPlanExport_insDT, 'DD.MM.YYYY HH24:MI') as \"PersonDopDispPlanExport_Date\",
				pu.PMUser_Name as \"PMUser_Name\"
			from
				v_PersonDopDispPlanExport pddpe
				inner join v_pmUserCache pu on pu.PMUser_id = pddpe.pmUser_insID
			where
				pddpe.PersonDopDispPlanExport_Year = :PersonDopDispPlanExport_Year
				and pddpe.PersonDopDispPlanExport_Month = :PersonDopDispPlanExport_Month
				and pddpe.Lpu_id = :Lpu_id
				and pddpe.PersonDopDispPlanExport_PackNum = :PersonDopDispPlanExport_PackNum
			limit 1
		";
		$resp_check = $this->queryResult($query, $params);
		if (!empty($resp_check[0]["PersonDopDispPlanExport_id"])) {
		    throw new Exception("Порядковый номер пакета должен быть уникальным в отчетном периоде. Пакет с указанным номером был создан {$resp_check[0]['PMUser_Name']} {$resp_check[0]['PersonDopDispPlanExport_Date']}. Измените номер пакета или удалите ранее созданный файл экспорта");
		}

		$links = [];

		if (!empty($data["OrgSMO_id"])) {
			$orgsmo_ids = explode(",", $data["OrgSMO_id"]);
			foreach($orgsmo_ids as $orgsmo_id) {
				$data_one = $data;
				$data_one["OrgSMO_id"] = $orgsmo_id;
				$res = $this->_exportPersonDopDispPlan($data_one);
				if (!empty($res['Error_Msg'])) {
					return $res;
				} else {
					$links[] = $res["link"];
				}
			}
		}
		else {
			$res = $this->_exportPersonDopDispPlan($data);
			if (!empty($res["Error_Msg"])) {
				return $res;
			} else {
				$links[] = $res["link"];
			}
		}

		return ["Error_Msg" => '', "link" => $links];
	}

    /**
     * Экспорт планов
     * @throws Exception
     */
	function _exportPersonDopDispPlan($data) {

		$X = "PROF";

		$LpuInfo = $this->queryResult("
			select Lpu_f003mcod as \"Lpu_f003mcod\" from v_Lpu where Lpu_id = :Lpu_id limit 1
		", [
			"Lpu_id" => $data["Lpu_id"]
		]);

		$Pi = "M";
		if (!empty($LpuInfo[0]["Lpu_f003mcod"])) {
			$Ni = $LpuInfo[0]["Lpu_f003mcod"];
		} else {
			$Ni = '';
		}
		$YY = mb_substr($data["PersonDopDispPlanExport_Year"], 2, 2);
		$MM = str_pad($data["PersonDopDispPlanExport_Month"], 2, '0', STR_PAD_LEFT);
		$N = $data["PacketNumber"];

		$S = '';
		if (!empty($data['OrgSMO_id'])) {
			$OrgSMOInfo = $this->queryResult("
				select Orgsmo_f002smocod as \"Orgsmo_f002smocod\" 
				from v_OrgSMO where OrgSMO_id = :OrgSMO_id limit 1
			", array(
				"OrgSMO_id" => $data["OrgSMO_id"]
			));
			if (!empty($OrgSMOInfo[0]["Orgsmo_f002smocod"])) {
				$S = "S".$OrgSMOInfo[0]["Orgsmo_f002smocod"];
			}
		}

		$filename = $X."_".$Pi.$Ni."T59".$S."_".$YY.$MM.$N;
		$zipfilename = $filename . ".zip";
		$xmlfilename = $filename . ".xml";

		$out_dir = "pddp_xml_".time()."_".$data["Lpu_id"];
		if(!is_dir(EXPORTPATH_REGISTRY.$out_dir)) mkdir( EXPORTPATH_REGISTRY.$out_dir );

		$zipfilepath = EXPORTPATH_REGISTRY.$out_dir."/".$zipfilename;
		$xmlfilepath = EXPORTPATH_REGISTRY.$out_dir."/".$xmlfilename;

		$filter = "";
		$queryParams = array();
		if (!empty($data["OrgSMO_id"])) {
			$filter .= " and p.OrgSMO_id = :OrgSMO_id";
			$queryParams["OrgSMO_id"] = $data["OrgSMO_id"];

			// Если поле «СМО» не пустое, то в файл попадают записи со статусом «Принято ТФОМС»
			$filter .= " and pddps.PlanPersonListStatusType_id = 3";
		} else {
			// Иначе, в файл должны попадать записи со статусом «Новые».
			$filter .= " and coalesce(PDDPS.PlanPersonListStatusType_id, 1) = 1";
		}

		$year = $this->getFirstResultFromQuery("
			select
			    PersonDopDispPlan_Year as \"PersonDopDispPlan_Year\" 
			from
			    v_PersonDopDispPlan 
			where
			    PersonDopDispPlan_id = :PersonDopDispPlan_id
		", [
			"PersonDopDispPlan_id" => $data["PersonDopDispPlan_ids"][0]
		]);

		$this->beginTransaction();

		// Создаём файл
		$resp_pddpe = $this->savePersonDopDispPlanExport([
			"PersonDopDispPlanExport_FileName" => $filename,
			"PersonDopDispPlanExport_PackNum" => $data["PacketNumber"],
			"OrgSmo_id" => $data["OrgSMO_id"],
			"Lpu_id" => $data["Lpu_id"],
			"PersonDopDispPlanExport_Year" => $data["PersonDopDispPlanExport_Year"],
			"PersonDopDispPlanExport_Month" => $data["PersonDopDispPlanExport_Month"],
			"pmUser_id" => $data["pmUser_id"]
		]);

		if (empty($resp_pddpe[0]["PersonDopDispPlanExport_id"])) {
			$this->rollbackTransaction();
			throw new Exception("Ошибка сохранения данных экспорта");
		}

		// Блокируем файл
		$this->setPersonDopDispPlanExportIsUsed([
			"PersonDopDispPlanExport_id" => $resp_pddpe[0]["PersonDopDispPlanExport_id"],
			"PersonDopDispPlanExport_IsUsed" => 1
		]);

		// достаём данные
        $query = "			
			select
				ppl.PlanPersonList_id as \"PlanPersonList_id\",
				ppl.PlanPersonList_id as \"NOMER_Z\",
				'' as \"PERSON_ID\",
				os.Orgsmo_f002smocod as \"SMOCOD\",
				ps.Person_EdNum as \"ENP\",
				ps.Person_SurName as \"FAM\",
				ps.Person_FirName as \"IM\",
				case when ps.Person_SecName is not null then ps.Person_SecName else 'НЕТ' end as \"OT\",
				to_char(ps.Person_BirthDay, 'YYYY-MM-DD HH24:MI:SS') as \"DR\",
				case when ps.Sex_id = 2 then 2 else 1 end as \"W\",
				DT.DocumentType_Code as \"DOCTYPE\",
				D.Document_Ser as \"DOCSER\",
				D.Document_Num as \"DOCNUM\",
				case when PS.Person_Snils IS NOT NULL and length(ps.Person_Snils) = 11 then (SUBSTRING(PS.Person_Snils, 1,3) || '-' || SUBSTRING(PS.Person_Snils,4,3) || '-' || SUBSTRING(PS.Person_Snils,7,3)  || ' ' || SUBSTRING(PS.Person_Snils, 10,2)) else '' end as \"SNILS\",
				pt.PolisType_CodeF008 as \"VPOLIS\",
				p.Polis_Ser as \"SPOLIS\",
				p.Polis_Num as \"NPOLIS\",
				rtrim(coalesce (pi.PersonInfo_InternetPhone, ps.Person_Phone)) as \"TEL\",
				case
					when pp.PrivilegeType_Code = '11' then 1 
					when pp.PrivilegeType_Code = '50' then 18
					when pp.PrivilegeType_Code = '150' then 21
				end as \"KAT_LG\",
				pddp.PersonDopDispPlan_Year as \"YEAR\",
				'' as \"COMMENT\",
				case
					when date_part('month', dcp.DispCheckPeriod_begDate) <= 3 then 1
					when date_part('month', dcp.DispCheckPeriod_begDate) <= 6 then 2
					when date_part('month', dcp.DispCheckPeriod_begDate) <= 9 then 3
					when date_part('month', dcp.DispCheckPeriod_begDate) <= 12 then 4
				end as \"QUART\",
				case when pddp.DispClass_id = 1 then 'ДВ4' else 'ОПВ' end as \"DISP\"
			from
				v_PersonDopDispPlan pddp
				inner join v_PlanPersonList ppl on pddp.PersonDopDispPlan_id = ppl.PersonDopDispPlan_id
				left join v_PlanPersonListStatus pddps on pddps.PlanPersonListStatus_id = PPL.PlanPersonListStatus_id
				left join v_DispCheckPeriod dcp on dcp.DispCheckPeriod_id = pddp.DispCheckPeriod_id
				inner join v_PersonState ps on ps.Person_id = ppl.Person_id
				inner join v_Person pe on pe.Person_id = ps.Person_id
				left join lateral (
					select
						pp.PrivilegeType_Code
					from
						v_PersonPrivilege pp
					where
						pp.Person_id = ps.Person_id
						and pp.PrivilegeType_Code in ('11','50','150')
						and pp.PersonPrivilege_begDate <= dbo.tzGetDate()
						and coalesce(pp.PersonPrivilege_endDate, dbo.tzGetDate()) >= dbo.tzGetDate()
					limit 1
				) PP on true
				left join v_Polis p on p.Polis_id = ps.Polis_id
				left join v_PolisType pt on pt.PolisType_id = p.PolisType_id
				left join v_OrgSMO os on os.OrgSMO_id = p.OrgSMO_id
				left join v_Document d on d.Document_id = ps.Document_id
				left join v_DocumentType dt on dt.DocumentType_id = d.DocumentType_id
				left join lateral (
					select PersonInfo_InternetPhone
					from v_PersonInfo
					where Person_id = ps.Person_id
					order by PersonInfo_id desc
					limit 1
				) pi on true
			where
				pddp.PersonDopDispPlan_id in ('" . implode("','", $data['PersonDopDispPlan_ids']) . "')
				{$filter}
		";
		$resp = $this->queryResult($query, $queryParams);

		foreach ($data['PersonDopDispPlan_ids'] as $PersonDopDispPlan_id) {
			// Сохраняем линки
			$this->savePersonDopDispPlanLink(array(
				"PersonDopDispPlan_id" => $PersonDopDispPlan_id,
				"PersonDopDispPlanExport_id" => $resp_pddpe[0]["PersonDopDispPlanExport_id"],
				"pmUser_id" => $data["pmUser_id"]
			));
		}

		// формируем XML
		$this->load->library("parser");

		foreach ($resp as &$respone) {
			// для всех записей сущностей «Человек в плане» устанавливается статус «Отправлен в ТФОМС»
			$this->setPlanPersonListStatus([
				"PlanPersonList_id" => $respone["PlanPersonList_id"],
				"PersonDopDispPlanExport_id" => $resp_pddpe[0]["PersonDopDispPlanExport_id"],
				"PlanPersonList_ExportNum" => $respone["NOMER_Z"],
				"PlanPersonListStatusType_id" => 2, // Отправлена в ТФОМС
				"pmUser_id" => $data["pmUser_id"]
			]);

			// смотрим соотсветсвует ли телефон формату (Если номер определяется и соответствует маске, тогда выгружаем,если нет - не выгружаем.)
			if (mb_strlen($respone["TEL"]) >= 10 && preg_match("/^[0-9]+$/ui", $respone["TEL"])) {
				$respone["TEL"] = mb_substr($respone["TEL"], mb_strlen($respone["TEL"]) - 10);
			} else {
				$respone["TEL"] = '';
			}

			if (preg_match("/[А-Яа-я]+/ui", $respone["OT"]) == 0) {
				$respone["OT"] = "НЕТ";
			}

			array_walk($respone, "ConvertFromUTF8ToWin1251", true);
		}

		$xml = "<?xml version=\"1.0\" encoding=\"windows-1251\" standalone=\"yes\"?>\r\n" . $this->parser->parse_ext('export_xml/export_dispplan_perm', [
				'ZGLV' => [[
					'FILENAME' => $filename,
					'FILENAME1' => '',
					'CODMOF' => $Ni,
					'YEAR' => $year,
					'QUART' => $data['PersonDopDispPlanExport_Quart']
				]],
				'SV_PR_MER' => $resp
			], true, false, [], true);

		file_put_contents($xmlfilepath, $xml);

		// запаковываем
		$zip = new ZipArchive();
		$zip->open($zipfilepath, ZIPARCHIVE::CREATE);
		$zip->AddFile($xmlfilepath, $xmlfilename);
		$zip->close();

		// Пишем ссылку
		$query = "
            update 
                PersonDopDispPlanExport
            set
                PersonDopDispPlanExport_DownloadLink = :PersonDopDispPlanExport_DownloadLink
            where
                PersonDopDispPlanExport_id = :PersonDopDispPlanExport_id
        ";
		$this->db->query($query, [
			"PersonDopDispPlanExport_id" => $resp_pddpe[0]["PersonDopDispPlanExport_id"],
			"PersonDopDispPlanExport_DownloadLink" => $zipfilepath
		]);

		// Снимаем блокировку
		$this->setPersonDopDispPlanExportIsUsed([
			"PersonDopDispPlanExport_id" => $resp_pddpe[0]["PersonDopDispPlanExport_id"],
			"PersonDopDispPlanExport_IsUsed" => null
		]);

		$this->commitTransaction();

		// отдаём юзверю
		return ['Error_Msg' => '', 'link' => $zipfilepath];
	}
}