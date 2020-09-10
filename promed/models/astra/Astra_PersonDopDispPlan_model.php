<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Astra_PersonDopDispPlan_model - модель для работы с планом диспансеризации (Астрахань)
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

class Astra_PersonDopDispPlan_model extends PersonDopDispPlan_model {

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

		$X = 'ND';

		$LpuInfo = $this->queryResult("
			select top 1 Lpu_f003mcod from v_Lpu (nolock) where Lpu_id = :Lpu_id
		", array(
			'Lpu_id' => $data['Lpu_id']
		));

		$Pi = 'M';
		if (!empty($LpuInfo[0]['Lpu_f003mcod'])) {
			$Ni = $LpuInfo[0]['Lpu_f003mcod'];
		} else {
			$Ni = '';
		}

		$Pp = 'T';
		$Np = '30';
		if (!empty($data['OrgSMO_id'])) {
			$Pp = 'S';
			$Np = '';
			$OrgSMOInfo = $this->queryResult("
				select top 1 Orgsmo_f002smocod from v_OrgSMO (nolock) where OrgSMO_id = :OrgSMO_id
			", array(
				'OrgSMO_id' => $data['OrgSMO_id']
			));
			if (!empty($OrgSMOInfo[0]['Orgsmo_f002smocod'])) {
				$Np = $OrgSMOInfo[0]['Orgsmo_f002smocod'];
			}
		}

		$YY = mb_substr(date('Y'), 2, 2);
		$MM = date('m');
		$N = $data['PacketNumber'];

		$filename = $X.$Pi.$Ni.$Pp.$Np.$YY.$MM.$N;
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
		}
		
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
			declare @curdate datetime = dbo.tzGetDate();
			
			select
				ROW_NUMBER() over (order by ppl.PlanPersonList_id) as IDCASE,
				ppl.PlanPersonList_id,
				MONTH(dcp.DispCheckPeriod_begDate) as PERIOD,
				case
					when pddp.DispClass_id = 1 then 'DP'
					when pddp.DispClass_id = 3 then 'DS'
					when pddp.DispClass_id = 5 then 'DO'
					when pddp.DispClass_id = 6 then 'DR'
					when pddp.DispClass_id = 7 then 'DU'
					when pddp.DispClass_id = 9 then 'DD'
					when pddp.DispClass_id = 10 then 'DF'
					else ''
				end as TIP_DATA,
				RTRIM(ps.Person_SurName) as FAM,
				RTRIM(ps.Person_FirName) as IM,
				RTRIM(ps.Person_SecName) as OT,
				case when ps.Sex_id = 2 then 2 else 1 end as W,
				convert(varchar(10), ps.Person_BirthDay, 120) as DR,
				case when PS.Person_Snils IS NOT NULL and len(ps.Person_Snils) = 11 then (SUBSTRING(PS.Person_Snils, 1,3) + '-' + SUBSTRING(PS.Person_Snils,4,3) + '-' + SUBSTRING(PS.Person_Snils,7,3)  + '-' + SUBSTRING(PS.Person_Snils, 10,2)) else '' end as SNILS,
				DT.DocumentType_Code as DOCTYPE,
				D.Document_Ser as DOCSER,
				D.Document_Num as DOCNUM,
				CONVERT(varchar(10), D.Document_begDate, 120) as DOCDATE,
				ps.Person_EdNum as ENP,
				pt.PolisType_CodeF008 as VPOLIS,
				case when ps.Person_EdNum is null then p.Polis_Ser else '' end as SPOLIS,
				case when ps.Person_EdNum is null then p.Polis_Num else '' end as NPOLIS,
				os.Orgsmo_f002smocod as SMO,
				case when msf.Person_Snils IS NOT NULL and len(msf.Person_Snils) = 11 then (SUBSTRING(msf.Person_Snils, 1,3) + '-' + SUBSTRING(msf.Person_Snils,4,3) + '-' + SUBSTRING(msf.Person_Snils,7,3)  + '-' + SUBSTRING(msf.Person_Snils, 10,2)) else '' end as IDDOKT,
				L.Lpu_f003mcod as CODE_MO
			from
				v_PersonDopDispPlan pddp with (nolock)
				inner join v_PlanPersonList ppl with (nolock) on pddp.PersonDopDispPlan_id = ppl.PersonDopDispPlan_id
				left join v_Lpu l with (nolock) on l.Lpu_id = pddp.Lpu_id
				left join v_DispCheckPeriod dcp with (nolock) on dcp.DispCheckPeriod_id = pddp.DispCheckPeriod_id
				inner join v_PersonState ps with (nolock) on ps.Person_id = ppl.Person_id
				left join v_Polis p with (nolock) on p.Polis_id = ps.Polis_id
				left join v_PolisType pt with (nolock) on pt.PolisType_id = p.PolisType_id
				left join v_OrgSMO os with (nolock) on os.OrgSMO_id = p.OrgSMO_id
				left join v_Document d with (nolock) on d.Document_id = ps.Document_id
				left join v_DocumentType dt with (nolock) on dt.DocumentType_id = d.DocumentType_id
				left join v_PersonCardState pcs with (nolock) on pcs.Person_id = ppl.Person_id and PCS.LpuAttachType_id = 1
				left join v_MedStaffFact msf with (nolock) on msf.MedStaffFact_id = pcs.MedStaffFact_id
			where
				pddp.PersonDopDispPlan_id in ('" . implode("','", $data['PersonDopDispPlan_ids']) . "')
				{$filter}
			order by
				ppl.PlanPersonList_id
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
		}

		// формируем XML
		$this->load->library('parser');

		array_walk_recursive($resp, 'ConvertFromUTF8ToWin1251', true);

		$xml =  "<?xml version=\"1.0\" encoding=\"windows-1251\" standalone=\"yes\"?>\r\n" . $this->parser->parse_ext('export_xml/export_dispplan_astra', array(
			'ZGLV' => array(array(
				'FILENAME' => $filename
			)),
			'NPR' => $resp
		), true, false, array(), false);

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
}