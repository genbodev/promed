<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Buryatiya_PersonDopDispPlan_model - модель для работы с планом диспансеризации (Бурятия)
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

class Buryatiya_PersonDopDispPlan_model extends PersonDopDispPlan_model {

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

		$X = ($data['DispClass_id'] == '1') ? 'DISP' : 'PROF';

		$LpuInfo = $this->queryResult("select Lpu_f003mcod as \"Lpu_f003mcod\" from v_Lpu where Lpu_id = :Lpu_id limit 1", ['Lpu_id' => $data['Lpu_id']]);
		$Ni = (!empty($LpuInfo[0]['Lpu_f003mcod'])) ? $LpuInfo[0]['Lpu_f003mcod'] : '';

		if (!empty($data['OrgSMO_id'])) {
			$OrgSMOInfo = $this->queryResult("select Orgsmo_f002smocod as \"Orgsmo_f002smocod\" from v_OrgSMO where OrgSMO_id = :OrgSMO_id limit 1", ['OrgSMO_id' => $data['OrgSMO_id']]);
		}
		$S = (!empty($OrgSMOInfo[0]['Orgsmo_f002smocod'])) ? 'S'.$OrgSMOInfo[0]['Orgsmo_f002smocod'] : '';
		
		$filename = $X.$Ni.'_'.date('y.m.d').'_'.$data['PacketNumber'];
		$zipfilename = $filename . '.zip';
		$xmlfilename = $filename . '.xml';

		$out_dir = "pddp_xml_".time()."_".$data['Lpu_id'];
		if(!is_dir(EXPORTPATH_REGISTRY.$out_dir)) mkdir( EXPORTPATH_REGISTRY.$out_dir );

		$zipfilepath = EXPORTPATH_REGISTRY.$out_dir."/".$zipfilename;
		$xmlfilepath = EXPORTPATH_REGISTRY.$out_dir."/".$xmlfilename;

		$filter = "";
		$queryParams = [];
		if (!empty($data['OrgSMO_id'])) {
			$filter .= " and p.OrgSMO_id = :OrgSMO_id";
			$queryParams['OrgSMO_id'] = $data['OrgSMO_id'];
			// Если поле «СМО» не пустое, то в файл попадают записи со статусом «Принято ТФОМС»
			$filter .= " and pddps.PlanPersonListStatusType_id = 3";
		} else {
			// Иначе, в файл должны попадать записи со статусом «Новые».
			$filter .= " and coalesce(PDDPS.PlanPersonListStatusType_id, 1) = 1";
		}
		
		$this->beginTransaction();

		// Создаём файл
		$resp_pddpe = $this->savePersonDopDispPlanExport([
			'PersonDopDispPlanExport_FileName' => $filename,
			'PersonDopDispPlanExport_PackNum' => $data['PacketNumber'],
			'OrgSmo_id' => $data['OrgSMO_id'],
			'Lpu_id' => $data['Lpu_id'],
			'pmUser_id' => $data['pmUser_id']
		]);

		if (empty($resp_pddpe[0]['PersonDopDispPlanExport_id'])) {
			$this->rollbackTransaction();
			return array('Error_Msg' => 'Ошибка сохранения данных экспорта');
		}
			
		// Блокируем файл
		$this->setPersonDopDispPlanExportIsUsed([
			'PersonDopDispPlanExport_id' => $resp_pddpe[0]['PersonDopDispPlanExport_id'],
			'PersonDopDispPlanExport_IsUsed' => 1
		]);

		// достаём данные
		$resp = $this->queryResult("
			select
				ppl.PlanPersonList_id as \"PlanPersonList_id\",
				uuid_generate_v4() as \"ID_PAC\",
				ps.Person_SurName as \"FAM\",
				ps.Person_FirName as \"IM\",
				case when ps.Person_SecName is not null then ps.Person_SecName else '' end as \"OT\",
				to_char(ps.Person_BirthDay, 'YYYY-MM-DD HH24:MI:SS') as \"DR\",
				ps.Person_EdNum as \"ENP\",
				pt.PolisType_CodeF008 as \"VPOLIS\",
				p.Polis_Ser as \"SPOLIS\",
				p.Polis_Num as \"NPOLIS\",
				case 
					when PS.Person_Snils is not null and length(ps.Person_Snils) = 11 
					then (SUBSTRING(PS.Person_Snils, 1,3) || '-' || SUBSTRING(PS.Person_Snils,4,3) || '-' || SUBSTRING(PS.Person_Snils,7,3) || '-' || SUBSTRING(PS.Person_Snils, 10,2)) 
					else '' 
				end as \"SNILS\",
				'' as \"CONTACTS\",
				case 
					when rtrim(pi.PersonInfo_InternetPhone) is not null and length(rtrim(pi.PersonInfo_InternetPhone)) = 11 
					then (SUBSTRING(rtrim(pi.PersonInfo_InternetPhone), 1,3) || '-' || SUBSTRING(rtrim(pi.PersonInfo_InternetPhone),4,3) || '-' || SUBSTRING(rtrim(pi.PersonInfo_InternetPhone),7,3) || '-' || SUBSTRING(rtrim(pi.PersonInfo_InternetPhone),10,2)) 
					else rtrim(pi.PersonInfo_InternetPhone) 
				end as \"IPHONE\",
				case 
					when rtrim(ps.Person_Phone) is not null and length(rtrim(ps.Person_Phone)) = 11 
					then (SUBSTRING(rtrim(ps.Person_Phone), 1,3) || '-' || SUBSTRING(rtrim(ps.Person_Phone),4,3) || '-' || SUBSTRING(rtrim(ps.Person_Phone),7,3) || '-' || SUBSTRING(rtrim(ps.Person_Phone),10,2)) 
					else rtrim(ps.Person_Phone) 
				end as \"PHONE\",
				rtrim(a.Address_Nick) as \"ADDRESS\",
				pddp.PersonDopDispPlan_Year as \"YEAR\",
				pddp.PersonDopDispPlan_Month as \"MONTH\",
				'' as \"TYPE\",
				case when pddp.DispClass_id = 1 then 'ДВ1' else 'ОПВ' end as \"IDDT\"
			from
				v_PersonDopDispPlan pddp
				inner join v_PlanPersonList ppl on pddp.PersonDopDispPlan_id = ppl.PersonDopDispPlan_id
				left join v_PlanPersonListStatus pddps on pddps.PlanPersonListStatus_id = ppl.PlanPersonListStatus_id
				left join v_DispCheckPeriod dcp on dcp.DispCheckPeriod_id = pddp.DispCheckPeriod_id
				inner join v_PersonState ps on ps.Person_id = ppl.Person_id
				left join v_Address a on ps.PAddress_id = a.Address_id
				inner join v_Person pe on pe.Person_id = ps.Person_id
				left join v_Polis p on p.Polis_id = ps.Polis_id
				left join v_PolisType pt on pt.PolisType_id = p.PolisType_id
				left join v_OrgSMO os on os.OrgSMO_id = p.OrgSMO_id
				left join lateral (
					select PersonInfo_InternetPhone from v_PersonInfo where Person_id = ps.Person_id order by PersonInfo_id desc limit 1
				) pi on true
			where
				pddp.PersonDopDispPlan_id in ('" . implode("','", $data['PersonDopDispPlan_ids']) . "')
				{$filter}
		", $queryParams);

		foreach ($resp as $key => $item){
			// для всех записей сущностей «Человек в плане» устанавливается статус «Отправлен в ТФОМС»
			$this->setPlanPersonListStatus([
				'PlanPersonList_id' => $item['PlanPersonList_id'],
				'PersonDopDispPlanExport_id' => $resp_pddpe[0]['PersonDopDispPlanExport_id'],
				'PlanPersonList_ExportNum' => $item['PlanPersonList_id'],
				'PlanPersonListStatusType_id' => 2, // Отправлена в ТФОМС
				'pmUser_id' => $data['pmUser_id']
			]);
			
			$contacts = [];
			if(!empty($item['IPHONE']))
				$contacts[] = ['CONTACT' => $item['IPHONE'],  'TYPE' => '3'];
			
			if(!empty($item['PHONE']))
				$contacts[] = ['CONTACT' => $item['PHONE'],   'TYPE' => '3'];
			
			if(!empty($item['ADDRESS']))
				$contacts[] = ['CONTACT' => $item['ADDRESS'], 'TYPE' => '1'];
			
			$resp[$key]['CONTACTS'] = $contacts;
		}
		array_walk_recursive($resp, 'ConvertFromUTF8ToWin1251', true);
		
		
		foreach ($data['PersonDopDispPlan_ids'] as $PersonDopDispPlan_id) {
			// Сохраняем линки
			$this->savePersonDopDispPlanLink([
				'PersonDopDispPlan_id' => $PersonDopDispPlan_id,
				'PersonDopDispPlanExport_id' => $resp_pddpe[0]['PersonDopDispPlanExport_id'],
				'pmUser_id' => $data['pmUser_id']
			]);
		}

		$OrgInfo = $this->queryResult("
				select Org_f003mcod as \"Org_f003mcod\" from v_Lpu l left join v_Org o on l.Org_id = o.Org_id where Lpu_id = :Lpu_id limit 1
			", ['Lpu_id' => $data['Lpu_id']]);
		$Org = (!empty($OrgInfo[0]['Org_f003mcod'])) ? $OrgInfo[0]['Org_f003mcod'] : '' ;
		
		// формируем XML
		$this->load->library('parser');

		$xml = "<?xml version=\"1.0\" encoding=\"windows-1251\" standalone=\"yes\"?>\r\n" . 
			$this->parser->parse_ext('export_xml/export_dispplan_buryatiya', 
				[	
					'ZGLV' => [[
						'FILENAME' => $xmlfilename,
						'MCOD' => $Org,
						'SMO' => $S
					]],
					'ZL' => $resp
				], true, false, [], true);

		file_put_contents($xmlfilepath, $xml);

		// запаковываем
		$zip = new ZipArchive();
		$zip->open($zipfilepath, ZIPARCHIVE::CREATE);
		$zip->AddFile($xmlfilepath, $xmlfilename);
		$zip->close();
		
		// Пишем ссылку
		$query = "update PersonDopDispPlanExport set PersonDopDispPlanExport_DownloadLink = :PersonDopDispPlanExport_DownloadLink where PersonDopDispPlanExport_id = :PersonDopDispPlanExport_id";
		$this->db->query($query, [
			'PersonDopDispPlanExport_id' => $resp_pddpe[0]['PersonDopDispPlanExport_id'],
			'PersonDopDispPlanExport_DownloadLink' => $zipfilepath
		]);
		
		// Снимаем блокировку
		$this->setPersonDopDispPlanExportIsUsed([
			'PersonDopDispPlanExport_id' => $resp_pddpe[0]['PersonDopDispPlanExport_id'],
			'PersonDopDispPlanExport_IsUsed' => null
		]);
		
		$this->commitTransaction();

		// отдаём юзверю
		return ['Error_Msg' => '', 'link' => $zipfilepath];
	}
}