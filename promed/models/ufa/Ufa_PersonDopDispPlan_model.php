<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Ufa_PersonDopDispPlan_model - модель для работы с планом диспансеризации (Уфа)
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

class Ufa_PersonDopDispPlan_model extends PersonDopDispPlan_model {

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

		$X = 'DP';

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
		$YY = mb_substr(date('Y'), 2, 2);
		$MM = date('m');
		$N = $data['PacketNumber'];

		$filename = $X.$Pi.$Ni.'T02'.$YY.$MM.$N;
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
				row_number() over(ORDER BY ppl.PlanPersonList_id) as ID_PAC,
				ppl.PlanPersonList_id,
				ps.Person_SurName as FAM,
				ps.Person_FirName as IM,
				ps.Person_SecName as OT,
				case when ps.Sex_id = 2 then 2 else 1 end as W,
				convert(varchar(10), ps.Person_BirthDay, 120) as DR,
				baddr.Address_Address as MR,
				DT.DocumentType_Code as DOCTYPE,
				D.Document_Ser as DOCSER,
				D.Document_Num as DOCNUM,
				case when ps.Person_Snils IS NOT NULL and len(ps.Person_Snils) = 11 then (SUBSTRING(ps.Person_Snils, 1,3) + '-' + SUBSTRING(ps.Person_Snils,4,3) + '-' + SUBSTRING(ps.Person_Snils,7,3)  + ' ' + SUBSTRING(ps.Person_Snils, 10,2)) else '' end as SNILS,			
				per.BDZ_Guid as COMENTP,
				ps.Person_edNum as POLISNUM,
				null as RESULT,
				null as SMO,
				case when pddp.DispClass_id = 1 then 'ДВ4' else 'ОПВ' end as DISP
			from
				v_PersonDopDispPlan pddp with (nolock)
				inner join v_DispCheckPeriod DCP with (nolock) on DCP.DispCheckPeriod_id = PDDP.DispCheckPeriod_id
				inner join v_PlanPersonList ppl with (nolock) on pddp.PersonDopDispPlan_id = ppl.PersonDopDispPlan_id
				inner join v_PersonState ps with (nolock) on ps.Person_id = ppl.Person_id
				inner join v_Person per with (nolock) on per.Person_id = ps.Person_id
				left join v_PersonBirthPlace pbp with (nolock) on ps.Person_id = pbp.Person_id
				left join v_Address baddr with (nolock) on pbp.Address_id = baddr.Address_id
				left join v_Document d with (nolock) on d.Document_id = ps.Document_id
				left join v_DocumentType dt with (nolock) on dt.DocumentType_id = d.DocumentType_id
				left join v_Polis p with (nolock) on p.Polis_id = ps.Polis_id
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
		}
		
		// формируем XML
		$this->load->library('parser');

		array_walk_recursive($resp, 'ConvertFromUTF8ToWin1251', true);

		$xml =  "<?xml version=\"1.0\" encoding=\"windows-1251\" standalone=\"yes\"?>\r\n" . $this->parser->parse_ext('export_xml/export_dispplan_ufa', array(
			'ZGLV' => array(array(
				'FILENAME' => $filename,
				'FILENAME1' => $Ni
			)),
			'PERS' => $resp
		), true, false, array(), true);
		file_put_contents($xmlfilepath, $xml);

		// запаковываем
		$zip = new ZipArchive();
		$zip->open($zipfilepath, ZIPARCHIVE::CREATE);
		$zip->AddFile($xmlfilepath, $xmlfilename);
		$zip->close();
		unlink($xmlfilepath);

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