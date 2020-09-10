<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Khak_PersonDopDispPlan_model - модель для работы с планом диспансеризации (Хакасия)
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package            Common
 * @access            public
 * @copyright        Copyright (c) 2020 Swan Ltd.
 * @author            Valery Bondarev
 */

require_once(APPPATH . 'models/_pgsql/PersonDopDispPlan_model.php');

class Khak_PersonDopDispPlan_model extends PersonDopDispPlan_model
{

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Экспорт планов
	 */
	function exportPersonDopDispPlan($data)
	{

		$links = array();

		if (!empty($data['OrgSMO_id'])) {
			$orgsmo_ids = explode(',', $data['OrgSMO_id']);
			foreach ($orgsmo_ids as $orgsmo_id) {
				$data_one = $data;
				$data_one['OrgSMO_id'] = $orgsmo_id;
				$res = $this->_exportPersonDopDispPlan($data_one);
				if (!empty($res['Error_Msg'])) {
					return $res;
				} else {
					$links[] = $res['link'];
				}
			}
		} else {
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
	function _exportPersonDopDispPlan($data)
	{

		$CC = 'PROF';

		$LpuInfo = $this->queryResult("
			select Lpu_f003mcod as \"Lpu_f003mcod\" from v_Lpu where Lpu_id = :Lpu_id limit 1
		", array(
			'Lpu_id' => $data['Lpu_id']
		));

		$Pi = 'M';
		if (!empty($LpuInfo[0]['Lpu_f003mcod'])) {
			$Ni = str_pad($LpuInfo[0]['Lpu_f003mcod'], 6, '0', STR_PAD_LEFT);
		} else {
			$Ni = '';
		}
		$Pp = 'T';
		$Np = '19';

		$YY = mb_substr(date('Y'), 2, 2);
		$N = $data['PacketNumber'];
		// $N = str_pad($data['PacketNumber'], 2, '0', STR_PAD_LEFT);

		$K = 1;
		if (!empty($data['DispCheckPeriod_id'])) {
			$resp_dcp = $this->queryResult("
				select
					EXTRACT(YEAR FROM dcp.DispCheckPeriod_begDate) as \"YEAR\",
					case
						when EXTRACT(MONTH FROM dcp.DispCheckPeriod_begDate) <= 3 then 1
						when EXTRACT(MONTH FROM dcp.DispCheckPeriod_begDate) <= 6 then 2
						when EXTRACT(MONTH FROM dcp.DispCheckPeriod_begDate) <= 9 then 3
						when EXTRACT(MONTH FROM dcp.DispCheckPeriod_begDate) <= 12 then 4
					end as \"QUART\"
				from
					v_DispCheckPeriod dcp
				where
					dcp.DispCheckPeriod_id = :DispCheckPeriod_id
			", array(
				'DispCheckPeriod_id' => $data['DispCheckPeriod_id']
			));

			if (!empty($resp_dcp[0]['YEAR'])) {
				$YY = mb_substr($resp_dcp[0]['YEAR'], 2, 2);
			}

			if (!empty($resp_dcp[0]['QUART'])) {
				$K = $resp_dcp[0]['QUART'];
			}
		}

		$filename = $CC . '_' . $Pi . $Ni . $Pp . $Np . '_' . $YY . $K . $N;
		$zipfilename = $filename . '.zip';
		$xmlfilename = $filename . '.xml';

		$out_dir = "pddp_xml_" . time() . "_" . $data['Lpu_id'];
		if (!is_dir(EXPORTPATH_REGISTRY . $out_dir)) mkdir(EXPORTPATH_REGISTRY . $out_dir);

		$zipfilepath = EXPORTPATH_REGISTRY . $out_dir . "/" . $zipfilename;
		$xmlfilepath = EXPORTPATH_REGISTRY . $out_dir . "/" . $xmlfilename;

		$filter = "";
		$queryParams = array();
		if (!empty($data['OrgSMO_id'])) {
			$filter .= " and p.OrgSMO_id = :OrgSMO_id";
			$queryParams['OrgSMO_id'] = $data['OrgSMO_id'];
		}

		$year = $this->getFirstResultFromQuery("select PersonDopDispPlan_Year as \"PersonDopDispPlan_Year\" from v_PersonDopDispPlan where PersonDopDispPlan_id = :PersonDopDispPlan_id", array(
			'PersonDopDispPlan_id' => $data['PersonDopDispPlan_ids'][0]
		));

		$this->beginTransaction();

		// Создаём файл
		$resp_pddpe = $this->savePersonDopDispPlanExport(array(
			'PersonDopDispPlanExport_FileName' => $filename,
			'PersonDopDispPlanExport_PackNum' => $data['PacketNumber'],
			'OrgSmo_id' => $data['OrgSMO_id'],
			'Lpu_id' => $data['Lpu_id'],
			'PersonDopDispPlanExport_Year' => null,
			'PersonDopDispPlanExport_Month' => null,
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
				ppl.PlanPersonList_id as \"NOMER_Z\",
				'' as \"PERSON_ID\",
				os.Orgsmo_f002smocod as \"SMOCOD\",
				ps.Person_EdNum as \"ENP\",
				ps.Person_SurName as \"FAM\",
				ps.Person_FirName as \"IM\",
				case when ps.Person_SecName is not null then ps.Person_SecName else 'НЕТ' end as \"OT\",
				to_char(ps.Person_BirthDay, 'yyyy-mm-dd') as \"DR\",
				case when ps.Sex_id = 2 then 2 else 1 end as \"W\",
				DT.DocumentType_Code as \"DOCTYPE\",
				D.Document_Ser as \"DOCSER\",
				D.Document_Num as \"DOCNUM\",
				case when PS.Person_Snils IS NOT NULL and LENGTH(ps.Person_Snils) = 11 then (SUBSTRING(PS.Person_Snils, 1,3) || '-' || SUBSTRING(PS.Person_Snils,4,3) || '-' || SUBSTRING(PS.Person_Snils,7,3)  || '-' || SUBSTRING(PS.Person_Snils, 10,2)) else '' end as \"SNILS\",
				pt.PolisType_CodeF008 as \"VPOLIS\",
				p.Polis_Ser as \"SPOLIS\",
				p.Polis_Num as \"NPOLIS\",
				rtrim(COALESCE(pi.PersonInfo_InternetPhone, ps.Person_Phone)) as \"TEL\",
				case when msf.Person_Snils IS NOT NULL and len(msf.Person_Snils) = 11 then (SUBSTRING(msf.Person_Snils, 1,3) || '-' || SUBSTRING(msf.Person_Snils,4,3) || '-' || SUBSTRING(msf.Person_Snils,7,3)  || '-' || SUBSTRING(msf.Person_Snils, 10,2)) else '' end as \"IDDOKT\",
				pa.Address_Address as \"ADRES\",
				case
					when pp.PrivilegeType_Code = '11' then 1 
					when pp.PrivilegeType_Code = '50' then 18
					when pp.PrivilegeType_Code = '150' then 21
				end as \"KAT_LG\",
				pddp.PersonDopDispPlan_Year as \"YEAR\",
				'' as \"COMMENT\",
				case
					when EXTRACT(MONTH FROM dcp.DispCheckPeriod_begDate) <= 3 then 1
					when EXTRACT(MONTH FROM dcp.DispCheckPeriod_begDate) <= 6 then 2
					when EXTRACT(MONTH FROM dcp.DispCheckPeriod_begDate) <= 9 then 3
					when EXTRACT(MONTH FROM dcp.DispCheckPeriod_begDate) <= 12 then 4
				end as \"QUART\",
				case when pddp.DispClass_id = 1 then 'ДВ4' else 'ОПВ' end as \"DISP\"
			from
				v_PersonDopDispPlan pddp
				inner join v_DispCheckPeriod DCP on DCP.DispCheckPeriod_id = PDDP.DispCheckPeriod_id
				inner join v_PlanPersonList ppl on pddp.PersonDopDispPlan_id = ppl.PersonDopDispPlan_id
				inner join v_PersonState ps on ps.Person_id = ppl.Person_id
				left join lateral (
					select
						pp.PrivilegeType_Code
					from
						v_PersonPrivilege pp
					where
						pp.Person_id = ps.Person_id
						and pp.PrivilegeType_Code in ('11','50','150')
						and pp.PersonPrivilege_begDate <= dbo.tzGetDate()
						and COALESCE(pp.PersonPrivilege_endDate, dbo.tzGetDate()) >= dbo.tzGetDate()
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
				left join v_Address_all pa on pa.Address_id = ps.PAddress_id
				left join v_PersonCardState pcs on pcs.Person_id = ppl.Person_id and PCS.LpuAttachType_id = 1
				left join lateral (
					select
						msf.Person_Snils
					from
						v_MedStaffRegion msr
						inner join v_MedStaffFact msf on msf.MedStaffFact_id = msr.MedStaffFact_id
					where
						msr.LpuRegion_id = pcs.LpuRegion_id and msf.Person_Snils is not null
					order by
						case when msr.MedStaffRegion_isMain = 2 then 0 else 1 end asc
					limit 1
				) msf on true
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

		foreach ($resp as &$respone) {
			// для всех записей сущностей «Человек в плане» устанавливается статус «Отправлен в ТФОМС»
			$this->setPlanPersonListStatus(array(
				'PlanPersonList_id' => $respone['PlanPersonList_id'],
				'PersonDopDispPlanExport_id' => $resp_pddpe[0]['PersonDopDispPlanExport_id'],
				'PlanPersonListStatusType_id' => 2, // Отправлена в ТФОМС
				'pmUser_id' => $data['pmUser_id']
			));

			// смотрим соотсветсвует ли телефон формату (Если номер определяется и соответствует маске, тогда выгружаем,если нет - не выгружаем.)
			if (mb_strlen($respone['TEL']) >= 10 && preg_match('/^[0-9]+$/ui', $respone['TEL'])) {
				$respone['TEL'] = '+7' . mb_substr($respone['TEL'], mb_strlen($respone['TEL']) - 10);
			} else {
				$respone['TEL'] = '';
			}

			if (preg_match('/[А-Яа-я]+/ui', $respone['OT']) == 0) {
				$respone['OT'] = 'НЕТ';
			}

			array_walk($respone, 'ConvertFromUTF8ToWin1251', true);
		}

		// формируем XML
		$this->load->library('parser');

		$xml = "<?xml version=\"1.0\" encoding=\"windows-1251\" standalone=\"yes\"?>\r\n" . $this->parser->parse_ext('export_xml/export_dispplan_khak', array(
				'ZGLV' => array(array(
					'FILENAME' => $filename,
					'FIRSTNAME' => '',
					'CODMOF' => $Ni
				)),
				'SV_PR_MER' => $resp
			), true, false, array(), true);
		file_put_contents($xmlfilepath, $xml);

		// запаковываем
		$zip = new ZipArchive();
		$zip->open($zipfilepath, ZIPARCHIVE::CREATE);
		$zip->AddFile($xmlfilepath, $xmlfilename);
		$zip->close();
		//unlink($xmlfilepath);

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

		// отдаём
		return array('Error_Msg' => '', 'link' => $zipfilepath);
	}
}