<?php	defined('BASEPATH') or die ('No direct script access allowed');

require_once(APPPATH.'models/EvnPS_model.php');

class Penza_EvnPS_model extends EvnPS_model {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Выгрузка данных для ТФОМС и СМО
	 */
	public function exportHospDataForTfomsToXml($data) {
		$params = array(
			'DateFrom' => $data['Period'][0],
			'DateTo' => $data['Period'][1],
			'Lpu_id' => $data['ExportLpu_id'],
			'startTime' => '20:00',
			'finalTime' => '19:59:59',
		);
		$response = array(
			'success' => true,
			'Error_Msg' => '',
			'Link' => '',
		);

		$exportForPeriod = ($data['Period'][0] != $data['Period'][1]);

		// Список МО
		$lpu_arr = $this->queryResult("
			select
				lp.Lpu_id,
				lp.Lpu_f003mcod as fcode
			from v_Lpu lp with(nolock)
			where
				len(isnull(lp.Lpu_f003mcod, '')) > 0
				and lp.Lpu_f003mcod != '0'
				and (:Lpu_id is null or lp.Lpu_id = :Lpu_id)
		", $params);

		if ( $lpu_arr === false || !is_array($lpu_arr) || count($lpu_arr) == 0 ) {
			$response['success'] = false;
			$response['Error_Msg'] = 'Ошибка при получении данных МО';
			return $response;
		}


		$path = EXPORTPATH_ROOT . "hosp_data_for_tfoms/";

		if ( !file_exists($path) ) {
			mkdir($path);
		}

		$out_dir = "hospDataForTfoms_" . time();

		if ( !file_exists($path . $out_dir) ) {
			mkdir($path . $out_dir);
		}

		$zipFileArray = array();

		if ($params['DateFrom'] >= '2018-04-01') {

		} else {

		}

		// 1. Сведения о выданных направлениях на госпитализацию
		$query = "
			declare @finalTime datetime = cast(:DateTo + ' ' + :finalTime as datetime);
			declare @Lpu_id bigint = :Lpu_id;
			declare @startTime datetime = dateadd(day, -1, cast(:DateFrom + ' ' + :startTime as datetime));
			declare @PayType_id bigint = (select top 1 PayType_id from v_PayType with (nolock) where PayType_SysNick = 'oms');

			select
				-- Перс. данные
				 case when dbo.AgeYMD(pe.Person_BirthDay, ed.EvnDirection_setDT, 2) < 3 and doct.DocumentType_id is null then 'НЕТ' else pe.Person_SurName end as FAM
				,pe.Person_FirName as IM
				,case when dbo.AgeYMD(pe.Person_BirthDay, ed.EvnDirection_setDT, 2) < 3 and doct.DocumentType_id is null then 'НЕТ' else pe.Person_SecName end as OT
				,case when pe.Sex_id = 3 then 1 else pe.Sex_id end as W
				,convert(varchar(10), pe.Person_BirthDay, 120) as DR
				,case when dbo.AgeYMD(pe.Person_BirthDay, ed.EvnDirection_setDT, 2) < 3 then d_pe.Person_SurName else null end as FAM_P
				,case when dbo.AgeYMD(pe.Person_BirthDay, ed.EvnDirection_setDT, 2) < 3 then d_pe.Person_FirName else null end as IM_P
				,case when dbo.AgeYMD(pe.Person_BirthDay, ed.EvnDirection_setDT, 2) < 3 then d_pe.Person_SecName else null end as OT_P
				,case when dbo.AgeYMD(pe.Person_BirthDay, ed.EvnDirection_setDT, 2) < 3 then 
					case when d_pe.Sex_id = 3 then 1 else d_pe.Sex_id end
				 else null
				 end as W_P
				,case when dbo.AgeYMD(pe.Person_BirthDay, ed.EvnDirection_setDT, 2) < 3 then convert(varchar(10), d_pe.Person_BirthDay, 120) else null end as DR_P
				,pe.Person_Snils as SNILS
				,pt.PolisType_CodeF008 as VPOLIS
				,case when pt.PolisType_CodeF008 in (1, 2) then po.Polis_Ser else null end as SPOLIS
				,case when pt.PolisType_CodeF008 = 3 then polisInfo.edNum else po.Polis_Num end as NPOLIS
				,polisInfo.edNum as ENP
				,case when doct.DocumentType_Code in (25, 26, 27, 28, 99) then 18 else doct.DocumentType_Code end as DOCTYPE
				,doc.Document_Ser as DOCSER
				,doc.Document_Num as DOCNUM
				,smo.Orgsmo_f002smocod as SMO
				,case
					when dbo.AgeYMD(pe.Person_BirthDay, ed.EvnDirection_setDT, 2) <= 3
						then 
							case when isnull(pe.Sex_id, 1) = 1 then '1' else '2' end +
							RIGHT(convert(varchar(10), pe.Person_BirthDay, 120), 2) +
							SUBSTRING(convert(varchar(10), pe.Person_BirthDay, 120), 6, 2) +
							SUBSTRING(convert(varchar(10), pe.Person_BirthDay, 120), 3, 2) +
							CAST(COALESCE(PChild.PersonChild_CountChild, BirthSvid.BirthSvid_ChildCount, 1) as char(1))
						else '0'
				 end as NOVOR
				,case when ost.OmsSprTerr_Code = 1156 then '56000' else '0' end as SUBJ
				,case when len(isnull(pe.PersonPhone_Phone, '')) = 0 then '' else pe.PersonPhone_Phone end as PHONE

				-- Данные направления
				,l.Lpu_f003mcod + '_' + cast(ed.EvnDirection_Num as varchar) as NUM_NPR
				,case when ISNULL(lud.LpuUnitType_id, ed.LpuUnitType_id) = 1 then 'S' else 'D' end as C_SERV
				,l.Lpu_f003mcod as MO_SRC
				,ld.Lpu_f003mcod as MO_DST
				,lsp.LpuSectionProfile_Code as PROFIL
				,trtl.TreatmentType_id as USL_TIP
				,case
					when ed.MedicalCareFormType_id = 1 then 3
					when ed.MedicalCareFormType_id = 2 then 2
					when ed.MedicalCareFormType_id = 3 then 1
				 end as FOR_POM
				,convert(varchar(19), ed.EvnDirection_setDT, 120) as DATE_NPR
				,convert(varchar(10), case
					when ed.EvnQueue_id is not null and tt.TimetableStac_id is null and ed.EvnDirection_desDT is not null then ed.EvnDirection_desDT
					when tt.TimetableStac_setDate is not null then tt.TimetableStac_setDate
					else ed.EvnDirection_setDT
				 end, 120) as DATE_HOSP
				,case when ed.DirFailType_id is null and esh.EvnStatusCause_id is null and eps.PrehospWaifRefuseCause_id is null then 0 else 1 end as OTKAZ
				,case
					when esc.EvnStatusCause_Code in (1, 2) then 4
					when esc.EvnStatusCause_Code in (3, 4, 8, 9, 10, 11, 13, 14, 15, 16, 17, 19, 20, 21, 28) then 10
					when esc.EvnStatusCause_Code in (5, 23) then 9
					when esc.EvnStatusCause_Code in (6) then 2
					when esc.EvnStatusCause_Code in (7) then 3
					when esc.EvnStatusCause_Code in (12, 24) then 1
					when esc.EvnStatusCause_Code in (18) then 7
					when esc.EvnStatusCause_Code in (22) then 8
					when dft.DirFailType_Code in (1) then 2
					when dft.DirFailType_Code in (2) then 3
					when dft.DirFailType_Code in (4, 7, 8, 9, 10, 11, 12, 14, 15, 16) then 10
					when dft.DirFailType_Code in (5) then 4
					when dft.DirFailType_Code in (6) then 1
					when dft.DirFailType_Code in (13) then 9
					when dft.DirFailType_Code in (17) then 7
					when pwrc.PrehospWaifRefuseCause_Code in (1) then 3
					when pwrc.PrehospWaifRefuseCause_Code in (2) then 4
					when pwrc.PrehospWaifRefuseCause_Code in (3) then 2
					when pwrc.PrehospWaifRefuseCause_Code in (4, 6, 7, 8) then 10
					when pwrc.PrehospWaifRefuseCause_Code in (5) then 1
					when pwrc.PrehospWaifRefuseCause_Code in (9) then 8
					when pwrc.PrehospWaifRefuseCause_Code in (10) then 9
					when pwrc.PrehospWaifRefuseCause_Code in (11) then 7
					else null
				 end as REASON
				,d.Diag_Code as DS0
				,null as COMMENT
				,mp.Person_Snils as CODE_MD
			from v_EvnDirection ed (nolock)
				inner join v_DirType dt (nolock) on dt.DirType_id = ed.DirType_id
				inner join v_Lpu l (nolock) on l.Lpu_id = ed.Lpu_id
				left join v_MedStaffFact msf (nolock) on msf.MedStaffFact_id = ed.MedStaffFact_id
				outer apply (
					select top 1
						mp.Person_Snils
					from
						v_MedPersonal mp (nolock)
					where
						ISNULL(msf.MedPersonal_id, ed.MedPersonal_id) = mp.MedPersonal_id and mp.Person_Snils is not null				
				) mp
				left join r58.v_TreatmentTypeLink trtl (nolock) on trtl.EvnDirection_id = ed.EvnDirection_id
				left join v_TimetableStac_lite tt (nolock) on tt.EvnDirection_id = ed.EvnDirection_id
				left join v_Diag d (nolock) on d.Diag_id = ed.Diag_id
				left join v_Lpu ld (nolock) on ld.Lpu_id = ed.Lpu_did
				left join v_LpuSection lsd (nolock) on lsd.LpuSection_id = ed.LpuSection_did
				left join v_LpuUnit lud (nolock) on lud.LpuUnit_id = lsd.LpuUnit_id
				left join v_LpuSectionProfile lsp with (nolock) on lsp.LpuSectionProfile_id = isnull(lsd.LpuSectionProfile_id, ed.LpuSectionProfile_id)
				left join v_EvnPS eps with (nolock) on eps.EvnDirection_id = ed.EvnDirection_id
				outer apply (
					select top 1 EvnStatusCause_id
					from v_EvnStatusHistory with (nolock)
					where Evn_id = ed.EvnDirection_id
						and EvnStatusHistory_begDate <= @finalTime
					order by EvnStatusHistory_begDate desc
				) esh
				left join v_EvnStatusCause esc with (nolock) on esc.EvnStatusCause_id = esh.EvnStatusCause_id
				left join v_DirFailType dft with (nolock) on dft.DirFailType_id = ed.DirFailType_id
				left join v_PrehospWaifRefuseCause pwrc with (nolock) on pwrc.PrehospWaifRefuseCause_id = eps.PrehospWaifRefuseCause_id
				-- пациент
				inner join v_Person_all pe (nolock) on pe.PersonEvn_id = ed.PersonEvn_id
					and pe.Server_id = ed.Server_id
				-- представитель пациента
				left join v_PersonDeputy pd (nolock) on pd.Person_id = pe.Person_id
				outer apply (
					select top 1
						Person_id,
						Polis_id,
						Person_EdNum,
						Person_SurName,
						Person_FirName,
						Person_SecName,
						Sex_id,
						Person_BirthDay
					from v_Person_all with(nolock)
					where Person_id = pd.Person_pid
						and PersonEvn_begDT <= ed.EvnDirection_setDT
					order by PersonEvn_insDT desc
				) d_pe
				-- идентификатор полиса пациента или его представителя
				outer apply(
					select 
						case when dbo.Age2(pe.Person_BirthDay, ed.EvnDirection_setDT) < 18 and pe.Polis_id is null and d_pe.Person_id is not null
						then d_pe.Polis_id else pe.Polis_id end as Polis_id,
						case when dbo.Age2(pe.Person_BirthDay, ed.EvnDirection_setDT) < 18 and pe.Polis_id is null and d_pe.Person_id is not null
						then d_pe.Person_EdNum else pe.Person_EdNum end as edNum
				) polisInfo
				-- данные полиса
				left join v_Polis po (nolock) on po.Polis_id = polisInfo.Polis_id
				left join v_PolisType pt (nolock) on pt.PolisType_id = po.PolisType_id and isnull(pt.PolisType_CodeF008, 0) <> 0
				left join v_OrgSMO smo (nolock) on smo.OrgSMO_id = po.OrgSmo_id
				left join v_OmsSprTerr ost (nolock) on ost.OmsSprTerr_id = po.OmsSprTerr_id
				left join v_Document doc (nolock) on doc.Document_id = pe.Document_id
				left join v_DocumentType doct (nolock) on doct.DocumentType_id = doc.DocumentType_id
				outer apply (
					select top 1 PersonChild_CountChild
					from PersonChild with (nolock) 
					where Person_id = ed.Person_id
					order by PersonChild_id desc
				) PChild
				outer apply (
					select top 1 BirthSvid_ChildCount
					from v_BirthSvid with (nolock) 
					where Person_id = ed.Person_id
					order by BirthSvid_id desc
				) BirthSvid
			where
				ed.DirType_id in (1, 5)
				and ed.PayType_id = @PayType_id
				and (@Lpu_id is null or ed.Lpu_id = @Lpu_id)
				and ed.EvnDirection_setDT between @startTime and @finalTime
				and ISNULL(lud.LpuUnitType_id, ed.LpuUnitType_id) IN (1, 6, 7, 9)
				and ISNULL(ED.EvnStatus_id, 16) not in (12,13)
				and ed.EvnClass_id = 27
		";

		foreach ( $lpu_arr as $lpu ) {
			$params['Lpu_id'] = $lpu['Lpu_id'];

			$queryReponse = $this->queryResult($query, $params);

			if ( $queryReponse === false || !is_array($queryReponse) /*|| count($queryReponse) == 0*/ ) {
				continue;
			}

			$exportData = array(
				'S' => array(),
				'D' => array(),
			);
			$N_ZAP = array(
				'S' => 0,
				'D' => 0,
			);

			foreach ( $queryReponse as $key => $row ) {
				$N_ZAP[$row['C_SERV']]++;
				$row['N_ZAP'] = $N_ZAP[$row['C_SERV']];

				if ( !empty($row['SNILS']) ) {
					if ( strlen($row['SNILS']) == 11 ) {
						$row['SNILS'] = substr($row['SNILS'], 0, 3) . '-' . substr($row['SNILS'], 3, 3) . '-' . substr($row['SNILS'], 6, 3) . ' ' . substr($row['SNILS'], -2);
					}
					else if ( strlen($row['SNILS']) != 14 ) {
						$row['SNILS'] = '';
					}
				}

				if ( !empty($row['CODE_MD']) ) {
					if ( strlen($row['CODE_MD']) == 11 ) {
						$row['CODE_MD'] = substr($row['CODE_MD'], 0, 3) . '-' . substr($row['CODE_MD'], 3, 3) . '-' . substr($row['CODE_MD'], 6, 3) . ' ' . substr($row['CODE_MD'], -2);
					}
					else if ( strlen($row['CODE_MD']) != 14 ) {
						$row['CODE_MD'] = '';
					}
				}

				$exportData[$row['C_SERV']][] = $row;
				unset($queryReponse[$key]);
			}

			foreach ( $exportData as $key => $exportDataByStacType ) {
				/*if ( count($exportDataByStacType) == 0 ) {
					continue;
				}*/

				$fileSign = $key . 'PMT' . ($exportForPeriod == true ? 'D' : '' ). $lpu['fcode'] . '_' . substr(str_replace('-', '', $data['Date']), 2, 6);
				$filePath = $path . $out_dir . "/" . $fileSign . ".xml";
				$zipFilePath = $path . $out_dir . "/" . $fileSign . ".zip";

				$hosp_data = array();
				$hosp_data['VERSION'] = '1.0';
				$hosp_data['DATA'] = $data['Date'];
				$hosp_data['FILENAME'] = $fileSign;
				$hosp_data['ZAP'] = $exportDataByStacType;

				$xml = "<?xml version=\"1.0\" encoding=\"windows-1251\"?>\r\n" . toAnsi($this->parser->parse('export_xml/hosp_data_for_tfoms_penza_f1', $hosp_data, true), true);
				file_put_contents($filePath, $xml);
				unset($xml);

				$zip = new ZipArchive();
				$zip->open($zipFilePath, ZIPARCHIVE::CREATE);
				$zip->AddFile($filePath, $fileSign . ".xml");
				$zip->close();

				$zipFileArray[$fileSign] = $zipFilePath;

				unlink($filePath);
			}
		}

		// 1.1. Сведения о выданных направлениях на ВМП
		$query = "
			declare @finalTime datetime = cast(:DateTo + ' ' + :finalTime as datetime);
			declare @Lpu_id bigint = :Lpu_id;
			declare @startTime datetime = dateadd(day, -1, cast(:DateFrom + ' ' + :startTime as datetime));
			declare @PayType_id bigint = (select top 1 PayType_id from v_PayType with (nolock) where PayType_SysNick = 'oms');

			select
				-- Перс. данные
				 case when dbo.AgeYMD(pe.Person_BirthDay, ed.EvnDirectionHTM_directDate, 2) < 3 and doct.DocumentType_id is null then 'НЕТ' else pe.Person_SurName end as FAM
				,case when dbo.AgeYMD(pe.Person_BirthDay, ed.EvnDirectionHTM_directDate, 2) < 3 and doct.DocumentType_id is null then 'НЕТ' else pe.Person_FirName end as IM
				,case when dbo.AgeYMD(pe.Person_BirthDay, ed.EvnDirectionHTM_directDate, 2) < 3 and doct.DocumentType_id is null then 'НЕТ' else pe.Person_SecName end as OT
				,case when pe.Sex_id = 3 then 1 else pe.Sex_id end as W
				,convert(varchar(10), pe.Person_BirthDay, 120) as DR
				,case when dbo.AgeYMD(pe.Person_BirthDay, ed.EvnDirectionHTM_directDate, 2) < 3 then d_pe.Person_SurName else null end as FAM_P
				,case when dbo.AgeYMD(pe.Person_BirthDay, ed.EvnDirectionHTM_directDate, 2) < 3 then d_pe.Person_FirName else null end as IM_P
				,case when dbo.AgeYMD(pe.Person_BirthDay, ed.EvnDirectionHTM_directDate, 2) < 3 then d_pe.Person_SecName else null end as OT_P
				,case when dbo.AgeYMD(pe.Person_BirthDay, ed.EvnDirectionHTM_directDate, 2) < 3 then 
					case when d_pe.Sex_id = 3 then 1 else d_pe.Sex_id end
				 else null
				 end as W_P
				,case when dbo.AgeYMD(pe.Person_BirthDay, ed.EvnDirectionHTM_directDate, 2) < 3 then convert(varchar(10), d_pe.Person_BirthDay, 120) else null end as DR_P
				,pe.Person_Snils as SNILS
				,pt.PolisType_CodeF008 as VPOLIS
				,case when pt.PolisType_CodeF008 in (1, 2) then po.Polis_Ser else null end as SPOLIS
				,case when pt.PolisType_CodeF008 = 3 then polisInfo.edNum else po.Polis_Num end as NPOLIS
				,polisInfo.edNum as ENP
				,case when doct.DocumentType_Code in (25, 26, 27, 28, 99) then 18 else doct.DocumentType_Code end as DOCTYPE
				,doc.Document_Ser as DOCSER
				,doc.Document_Num as DOCNUM
				,'' as SMO -- не заполнять
				,case
					when dbo.AgeYMD(pe.Person_BirthDay, ed.EvnDirectionHTM_directDate, 2) <= 3
						then 
							case when isnull(pe.Sex_id, 1) = 1 then '1' else '2' end +
							RIGHT(convert(varchar(10), pe.Person_BirthDay, 120), 2) +
							SUBSTRING(convert(varchar(10), pe.Person_BirthDay, 120), 6, 2) +
							SUBSTRING(convert(varchar(10), pe.Person_BirthDay, 120), 3, 2) +
							CAST(COALESCE(PChild.PersonChild_CountChild, BirthSvid.BirthSvid_ChildCount, 1) as char(1))
						else '0'
				 end as NOVOR
				,case when ost.OmsSprTerr_Code = 1156 then '56000' else '0' end as SUBJ
				,case when len(isnull(pe.PersonPhone_Phone, '')) = 0 then '' else pe.PersonPhone_Phone end as PHONE

				-- Данные направления
				,l.Lpu_f003mcod + '_' + cast(ed.EvnDirectionHTM_Num as varchar) as NUM_NPR
				,nnm.NUM_NPR_MAIN
				,ed.EvnDirectionHTM_TalonNum as TALON_VMP
				,l.Lpu_f003mcod as MO_SRC
				,ld.LpuHTM_f003mcod as MO_DST
				,RIGHT('000' + htmcc.HTMedicalCareClass_Code, 3) + RIGHT('000' + lsp.LpuSectionProfile_Code, 3) as PROFIL
				,trtl.TreatmentType_id as USL_TIP
				,case
					when ed.PrehospType_did IN (1,3) then 3
					when ed.PrehospType_did = 2 then 1
				 end as FOR_POM
				,convert(varchar(19), ed.EvnDirectionHTM_directDate, 120) as DATE_NPR
				,convert(varchar(10), case
					when ed.EvnQueue_id is not null and tt.TimetableStac_id is null and ed.EvnDirectionHTM_desDT is not null then ed.EvnDirectionHTM_desDT
					when tt.TimetableStac_setDate is not null then tt.TimetableStac_setDate
					else ed.EvnDirectionHTM_directDate
				 end, 120) as DATE_HOSP
				,case when ed.DirFailType_id is null and esh.EvnStatusCause_id is null and eps.PrehospWaifRefuseCause_id is null then 0 else 1 end as OTKAZ
				,d.Diag_Code as DS0
				,null as COMMENT
				,mp.Person_Snils as CODE_MD
			from v_EvnDirectionHTM ed (nolock)
				inner join v_DirType dt (nolock) on dt.DirType_id = 19
				inner join v_Lpu l (nolock) on l.Lpu_id = ed.Lpu_id
				left join v_MedStaffFact msf (nolock) on msf.MedStaffFact_id = ed.MedStaffFact_id
				outer apply (
					select top 1
						mp.Person_Snils
					from
						v_MedPersonal mp (nolock)
					where
						ISNULL(msf.MedPersonal_id, ed.MedPersonal_id) = mp.MedPersonal_id and mp.Person_Snils is not null				
				) mp
				left join r58.v_TreatmentTypeLink trtl (nolock) on trtl.EvnDirection_id = ed.EvnDirectionHTM_id
				left join v_TimetableStac_lite tt (nolock) on tt.EvnDirection_id = ed.EvnDirectionHTM_id
				left join v_HTMedicalCareClass htmcc with (nolock) on htmcc.HTMedicalCareClass_id = ed.HTMedicalCareClass_id
				left join v_Diag d (nolock) on d.Diag_id = ed.Diag_id
				left join v_LpuHTM ld (nolock) on ld.LpuHTM_id = ed.LpuHTM_id
				left join v_LpuSection lsd (nolock) on lsd.LpuSection_id = ed.LpuSection_did
				left join v_LpuUnit lud (nolock) on lud.LpuUnit_id = lsd.LpuUnit_id
				left join v_LpuSectionProfile lsp with (nolock) on lsp.LpuSectionProfile_id = isnull(lsd.LpuSectionProfile_id, ed.LpuSectionProfile_id)
				left join v_EvnPS eps with (nolock) on eps.EvnDirection_id = ed.EvnDirectionHTM_id
				outer apply (
					select top 1 EvnStatusCause_id
					from v_EvnStatusHistory with (nolock)
					where Evn_id = ed.EvnDirectionHTM_id
						and EvnStatusHistory_begDate <= @finalTime
					order by EvnStatusHistory_begDate desc
				) esh
				-- пациент
				inner join v_Person_all pe (nolock) on pe.PersonEvn_id = ed.PersonEvn_id
					and pe.Server_id = ed.Server_id
				-- представитель пациента
				left join v_PersonDeputy pd (nolock) on pd.Person_id = pe.Person_id
				outer apply (
					select top 1
						Person_id,
						Polis_id,
						Person_EdNum,
						Person_SurName,
						Person_FirName,
						Person_SecName,
						Sex_id,
						Person_BirthDay
					from v_Person_all with(nolock)
					where Person_id = pd.Person_pid
						and PersonEvn_begDT <= ed.EvnDirectionHTM_directDate
					order by PersonEvn_insDT desc
				) d_pe
				-- идентификатор полиса пациента или его представителя
				outer apply(
					select 
						case when dbo.Age2(pe.Person_BirthDay, ed.EvnDirectionHTM_directDate) < 18 and pe.Polis_id is null and d_pe.Person_id is not null
						then d_pe.Polis_id else pe.Polis_id end as Polis_id,
						case when dbo.Age2(pe.Person_BirthDay, ed.EvnDirectionHTM_directDate) < 18 and pe.Polis_id is null and d_pe.Person_id is not null
						then d_pe.Person_EdNum else pe.Person_EdNum end as edNum
				) polisInfo
				-- данные полиса
				left join v_Polis po (nolock) on po.Polis_id = polisInfo.Polis_id
				left join v_PolisType pt (nolock) on pt.PolisType_id = po.PolisType_id and isnull(pt.PolisType_CodeF008, 0) <> 0
				left join v_OrgSMO smo (nolock) on smo.OrgSMO_id = po.OrgSmo_id
				left join v_OmsSprTerr ost (nolock) on ost.OmsSprTerr_id = po.OmsSprTerr_id
				left join v_Document doc (nolock) on doc.Document_id = pe.Document_id
				left join v_DocumentType doct (nolock) on doct.DocumentType_id = doc.DocumentType_id
				outer apply (
					select top 1 PersonChild_CountChild
					from PersonChild with (nolock) 
					where Person_id = ed.Person_id
					order by PersonChild_id desc
				) PChild
				outer apply (
					select top 1 BirthSvid_ChildCount
					from v_BirthSvid with (nolock) 
					where Person_id = ed.Person_id
					order by BirthSvid_id desc
				) BirthSvid
				outer apply (
					select top 1
						case
							when ed2.EvnDirection_Num is not null and ISNULL(ed2.EvnDirection_IsAuto, 1) = 1 then ld.Lpu_f003mcod + '_' + cast(ed2.EvnDirection_Num as varchar)
							when eps.EvnDirection_Num is not null and eps.PrehospDirect_id = 1 then l.Lpu_f003mcod + '_' + cast(eps.EvnDirection_Num as varchar)
							when eps.EvnDirection_Num is not null and ld_eps.Lpu_f003mcod is not null then ld_eps.Lpu_f003mcod + '_' + cast(eps.EvnDirection_Num as varchar)
							when eps.EvnDirection_Num is not null and ld_ls.Lpu_f003mcod is not null then ld_ls.Lpu_f003mcod + '_' + cast(eps.EvnDirection_Num as varchar)
							when ed2.EvnDirection_Num is not null and ISNULL(ed2.EvnDirection_IsAuto, 1) = 2 then ld.Lpu_f003mcod + '_' + cast(ed2.EvnDirection_Num as varchar)
							else l.Lpu_f003mcod + '_Э' + cast(eps.EvnPS_id as varchar)
						 end as NUM_NPR_MAIN
					from
						v_EvnPS eps (nolock)
						left join v_EvnDirection_all ed2 with (nolock) on ed2.EvnDirection_id = eps.EvnDirection_id
						inner join v_Lpu l (nolock) on l.Lpu_id = eps.Lpu_id
						left join v_Lpu ld (nolock) on ld.Lpu_id = ed2.Lpu_id
						left join v_Lpu ld_eps (nolock) on ld_eps.Org_id = eps.Org_did
						left join v_LpuSection ls_eps (nolock) on ls_eps.LpuSection_id = eps.Lpusection_did
						left join v_Lpu ld_ls (nolock) on ld_ls.Lpu_id = ls_eps.Lpu_id
						left join v_EvnPrescrVK epvk (nolock) on epvk.EvnPrescrVK_pid = eps.EvnPS_id
						left join v_EvnVK evk (nolock) on evk.EvnPrescrVK_id = epvk.EvnPrescrVK_id
					where
						eps.Person_id = ed.Person_id 
						and ed.EvnDirectionHTM_pid = evk.EvnVK_id
				) nnm
			where
				(@Lpu_id is null or ed.Lpu_id = @Lpu_id)
				and ed.EvnDirectionHTM_directDate between @startTime and @finalTime
				and ISNULL(ED.EvnStatus_id, 16) not in (12,13)
		";

		foreach ( $lpu_arr as $lpu ) {
			$params['Lpu_id'] = $lpu['Lpu_id'];

			$queryReponse = $this->queryResult($query, $params);

			if ( $queryReponse === false || !is_array($queryReponse) /*|| count($queryReponse) == 0*/ ) {
				continue;
			}

			$exportData = array();
			$N_ZAP = 0;

			foreach ( $queryReponse as $key => $row ) {
				$N_ZAP++;
				$row['N_ZAP'] = $N_ZAP;

				if ( !empty($row['SNILS']) ) {
					if ( strlen($row['SNILS']) == 11 ) {
						$row['SNILS'] = substr($row['SNILS'], 0, 3) . '-' . substr($row['SNILS'], 3, 3) . '-' . substr($row['SNILS'], 6, 3) . ' ' . substr($row['SNILS'], -2);
					}
					else if ( strlen($row['SNILS']) != 14 ) {
						$row['SNILS'] = '';
					}
				}

				if ( !empty($row['CODE_MD']) ) {
					if ( strlen($row['CODE_MD']) == 11 ) {
						$row['CODE_MD'] = substr($row['CODE_MD'], 0, 3) . '-' . substr($row['CODE_MD'], 3, 3) . '-' . substr($row['CODE_MD'], 6, 3) . ' ' . substr($row['CODE_MD'], -2);
					}
					else if ( strlen($row['CODE_MD']) != 14 ) {
						$row['CODE_MD'] = '';
					}
				}

				$exportData[] = $row;
				unset($queryReponse[$key]);
			}

			$fileSign = 'PVMP' . ($exportForPeriod == true ? 'D' : '' ). $lpu['fcode'] . '_' . substr(str_replace('-', '', $data['Date']), 2, 6);
			$filePath = $path . $out_dir . "/" . $fileSign . ".xml";
			$zipFilePath = $path . $out_dir . "/" . $fileSign . ".zip";

			$hosp_data = array();
			$hosp_data['VERSION'] = '1.0';
			$hosp_data['DATA'] = $data['Date'];
			$hosp_data['FILENAME'] = $fileSign;
			$hosp_data['ZAP'] = $exportData;

			$xml = "<?xml version=\"1.0\" encoding=\"windows-1251\"?>\r\n" . toAnsi($this->parser->parse('export_xml/hosp_data_for_tfoms_penza_f4', $hosp_data, true), true);
			file_put_contents($filePath, $xml);
			unset($xml);

			$zip = new ZipArchive();
			$zip->open($zipFilePath, ZIPARCHIVE::CREATE);
			$zip->AddFile($filePath, $fileSign . ".xml");
			$zip->close();

			$zipFileArray[$fileSign] = $zipFilePath;

			unlink($filePath);
		}

		// 2. Сведения о движении пациентов
		$query = "
			declare @finalTime datetime = cast(:DateTo + ' ' + :finalTime as datetime);
			declare @Lpu_id bigint = :Lpu_id;
			declare @startTime datetime = dateadd(day, -1, cast(:DateFrom + ' ' + :startTime as datetime));
			declare @PayType_id bigint = (select top 1 PayType_id from v_PayType with (nolock) where PayType_SysNick = 'oms');

			with EvnSectionList as (
				-- Госпитализация в приемном
				-- #120862 Исключить попадание в файл записей по дате поступления в приемное
				/*select
					1 as MotionType,
					EvnSection_id,
					EvnSection_pid,
					EvnSection_Count,
					EvnSection_Index,
					EvnSection_IsPriem,
					EvnSection_setDT,
					EvnSection_disDT,
					Lpu_id,
					LpuSection_id,
					LpuSectionProfile_id,
					Person_id,
					PersonEvn_id,
					Server_id,
					Diag_id
				from v_EvnSection with (nolock)
				where
					PayType_id = @PayType_id
					and (@Lpu_id is null or Lpu_id = @Lpu_id)
					and EvnSection_setDT between @startTime and @finalTime
					and ISNULL(EvnSection_IsPriem, 1) = 2
					and HTMedicalCareClass_id is null

				union all*/

				-- Госпитализация в профильное отделение
				select
					1 as MotionType,
					EvnSection_id,
					EvnSection_pid,
					EvnSection_Count,
					EvnSection_Index,
					EvnSection_IsPriem,
					EvnSection_setDT,
					EvnSection_disDT,
					Lpu_id,
					LpuSection_id,
					LpuSectionProfile_id,
					Person_id,
					PersonEvn_id,
					Server_id,
					Diag_id
				from v_EvnSection with (nolock)
				where
					PayType_id = @PayType_id
					and (@Lpu_id is null or Lpu_id = @Lpu_id)
					and EvnSection_setDT between @startTime and @finalTime
					and ISNULL(EvnSection_IsPriem, 1) = 1
					and HTMedicalCareClass_id is null

				union all

				-- Отказ в приемном
				select
					2 as MotionType,
					es.EvnSection_id,
					es.EvnSection_pid,
					es.EvnSection_Count,
					es.EvnSection_Index,
					es.EvnSection_IsPriem,
					es.EvnSection_setDT,
					es.EvnSection_disDT,
					es.Lpu_id,
					es.LpuSection_id,
					es.LpuSectionProfile_id,
					es.Person_id,
					es.PersonEvn_id,
					es.Server_id,
					es.Diag_id
				from v_EvnSection es with (nolock)
					inner join v_EvnPS eps with (nolock) on eps.EvnPS_id = es.EvnSection_pid
				where
					es.PayType_id = @PayType_id
					and (@Lpu_id is null or es.Lpu_id = @Lpu_id)
					and es.EvnSection_disDT between @startTime and @finalTime
					and ISNULL(es.EvnSection_IsPriem, 1) = 2
					and eps.PrehospWaifRefuseCause_id is not null

				union all

				-- Выписка из профильного отделения
				select
					2 as MotionType,
					es.EvnSection_id,
					es.EvnSection_pid,
					es.EvnSection_Count,
					es.EvnSection_Index,
					es.EvnSection_IsPriem,
					es.EvnSection_setDT,
					es.EvnSection_disDT,
					es.Lpu_id,
					es.LpuSection_id,
					es.LpuSectionProfile_id,
					es.Person_id,
					es.PersonEvn_id,
					es.Server_id,
					es.Diag_id
				from v_EvnSection es with (nolock)
					inner join v_EvnPS eps with (nolock) on eps.EvnPS_id = es.EvnSection_pid
				where
					es.PayType_id = @PayType_id
					and (@Lpu_id is null or es.Lpu_id = @Lpu_id)
					and es.EvnSection_disDT between @startTime and @finalTime
					and ISNULL(es.EvnSection_IsPriem, 1) = 1
					and es.EvnSection_Index = es.EvnSection_Count - 1
					and eps.EvnPS_disDT is not null
					and es.HTMedicalCareClass_id is null
			)

			select
				-- Перс. данные
				 case when dbo.AgeYMD(pe.Person_BirthDay, ed.EvnDirection_setDT, 2) < 3 and doct.DocumentType_id is null then 'НЕТ' else pe.Person_SurName end as FAM
				,pe.Person_FirName as IM
				,case when dbo.AgeYMD(pe.Person_BirthDay, ed.EvnDirection_setDT, 2) < 3 and doct.DocumentType_id is null then 'НЕТ' else pe.Person_SecName end as OT
				,case when pe.Sex_id = 3 then 1 else pe.Sex_id end as W
				,convert(varchar(10), pe.Person_BirthDay, 120) as DR
				,case when dbo.AgeYMD(pe.Person_BirthDay, ed.EvnDirection_setDT, 2) < 3 then d_pe.Person_SurName else null end as FAM_P
				,case when dbo.AgeYMD(pe.Person_BirthDay, ed.EvnDirection_setDT, 2) < 3 then d_pe.Person_FirName else null end as IM_P
				,case when dbo.AgeYMD(pe.Person_BirthDay, ed.EvnDirection_setDT, 2) < 3 then d_pe.Person_SecName else null end as OT_P
				,case when dbo.AgeYMD(pe.Person_BirthDay, ed.EvnDirection_setDT, 2) < 3 then 
					case when d_pe.Sex_id = 3 then 1 else d_pe.Sex_id end
				 else null
				 end as W_P
				,case when dbo.AgeYMD(pe.Person_BirthDay, ed.EvnDirection_setDT, 2) < 3 then convert(varchar(10), d_pe.Person_BirthDay, 120) else null end as DR_P
				,pe.Person_Snils as SNILS
				,pt.PolisType_CodeF008 as VPOLIS
				,case when pt.PolisType_CodeF008 in (1, 2) then po.Polis_Ser else null end as SPOLIS
				,case when pt.PolisType_CodeF008 = 3 then polisInfo.edNum else po.Polis_Num end as NPOLIS
				,polisInfo.edNum as ENP
				,case when doct.DocumentType_Code in (25, 26, 27, 28, 99) then 18 else doct.DocumentType_Code end as DOCTYPE
				,doc.Document_Ser as DOCSER
				,doc.Document_Num as DOCNUM
				,smo.Orgsmo_f002smocod as SMO
				,case
					when dbo.AgeYMD(pe.Person_BirthDay, ed.EvnDirection_setDT, 2) <= 3
						then 
							case when isnull(pe.Sex_id, 1) = 1 then '1' else '2' end +
							RIGHT(convert(varchar(10), pe.Person_BirthDay, 120), 2) +
							SUBSTRING(convert(varchar(10), pe.Person_BirthDay, 120), 6, 2) +
							SUBSTRING(convert(varchar(10), pe.Person_BirthDay, 120), 3, 2) +
							CAST(COALESCE(PChild.PersonChild_CountChild, BirthSvid.BirthSvid_ChildCount, 1) as char(1))
						else '0'
				 end as NOVOR
				,eps.EvnPS_NumCard as NHISTORY
				,case when ost.OmsSprTerr_Code = 1156 then '56000' else '0' end as SUBJ

				-- Данные направления
				,case
					when ed.EvnDirection_Num is not null and ISNULL(ed.EvnDirection_IsAuto, 1) = 1 then ld.Lpu_f003mcod + '_' + cast(ed.EvnDirection_Num as varchar)
					when eps.EvnDirection_Num is not null and eps.PrehospDirect_id = 1 then l.Lpu_f003mcod + '_' + cast(eps.EvnDirection_Num as varchar)
					when eps.EvnDirection_Num is not null and ld_eps.Lpu_f003mcod is not null then ld_eps.Lpu_f003mcod + '_' + cast(eps.EvnDirection_Num as varchar)
					when eps.EvnDirection_Num is not null and ld_ls.Lpu_f003mcod is not null then ld_ls.Lpu_f003mcod + '_' + cast(eps.EvnDirection_Num as varchar)
					when ed.EvnDirection_Num is not null and ISNULL(ed.EvnDirection_IsAuto, 1) = 2 then ld.Lpu_f003mcod + '_' + cast(ed.EvnDirection_Num as varchar)
					else l.Lpu_f003mcod + '_Э' + cast(eps.EvnPS_id as varchar)
				 end as NUM_NPR
				,case when lu.LpuUnitType_id = 1 then 'S' else 'D' end as C_SERV
				,case
			 		when payt.PayType_SysNick = 'bud' then 1
			 		when payt.PayType_SysNick = 'oms' then 2
			 		when payt.PayType_SysNick = 'dms' then 3
			 		when payt.PayType_SysNick = 'money' then 4
			 		when payt.PayType_SysNick = 'ovd' then 5
			 		when payt.PayType_SysNick = 'fbud' then 6
			 	end as IST_FIN
				,COALESCE(ld.Lpu_f003mcod, ld_eps.Lpu_f003mcod, ld_ls.Lpu_f003mcod, lp.Lpu_f003mcod) as MO_SRC
				,l.Lpu_f003mcod as MO_DST
				,case when es.EvnSection_IsPriem = 2 then lsp1.LpuSectionProfile_Code else lsp.LpuSectionProfile_Code end as PROFIL
				,USL_TIP.USL_TIP as USL_TIP
				,case when eps.PrehospWaifRefuseCause_id is not null then 1 else 0 end as OTKAZ
				,case
					when pwrc.PrehospWaifRefuseCause_Code in (1) then 3
					when pwrc.PrehospWaifRefuseCause_Code in (2) then 4
					when pwrc.PrehospWaifRefuseCause_Code in (3) then 2
					when pwrc.PrehospWaifRefuseCause_Code in (4, 6, 7, 8) then 10
					when pwrc.PrehospWaifRefuseCause_Code in (5) then 1
					when pwrc.PrehospWaifRefuseCause_Code in (9) then 8
					when pwrc.PrehospWaifRefuseCause_Code in (10) then 9
					when pwrc.PrehospWaifRefuseCause_Code in (11) then 7
					else null
				 end as REASON
				,case
					when eps.MedicalCareFormType_id = 1 then 3
					when eps.MedicalCareFormType_id = 2 then 2
					when eps.MedicalCareFormType_id = 3 then 1
				 end as FOR_POM
				,case
					when eps.PrehospWaifRefuseCause_id is not null then 5
					when es.MotionType = 1 and isnull(es.EvnSection_IsPriem,1) = 1 and es.EvnSection_Index in (0, 1) and es.EvnSection_setDT between @startTime and @finalTime then 1
					when es.MotionType = 1 and es.EvnSection_Index > 1 then 3
					when es.MotionType = 2 and es.EvnSection_IsPriem = 2 then 5
					when es.MotionType = 2 then 2
				 end as TYPE_MOTION
				,convert(varchar(19), case when es.MotionType = 2 then es.EvnSection_disDT else es.EvnSection_setDT end, 120) as DATE_MOTION
				,d.Diag_Code as DS1
				,null as COMMENT
			from EvnSectionList es
				inner join v_EvnPS eps with (nolock) on eps.EvnPS_id = es.EvnSection_pid
				inner join v_PayType payt with (nolock) on payt.PayType_id = eps.PayType_id and payt.PayType_SysNick in ('bud','oms','dms','money','ovd','fbud')
				inner join v_Lpu l (nolock) on l.Lpu_id = eps.Lpu_id
				inner join v_LpuSection ls (nolock) on ls.LpuSection_id = es.LpuSection_id
				inner join v_LpuUnit lu (nolock) on lu.LpuUnit_id = ls.LpuUnit_id
				inner join v_LpuSectionProfile lsp with (nolock) on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
				left join v_LpuSectionProfile lsp1 with (nolock) on lsp1.LpuSectionProfile_id = es.LpuSectionProfile_id
				left join v_EvnDirection_all ed with (nolock) on ed.EvnDirection_id = eps.EvnDirection_id 
				outer apply (
					select top 1 Lpu_id
					from v_PersonCard_all with (nolock)
					where
						Person_id = es.Person_id
						and PersonCard_begDate <= @finalTime
						and (PersonCard_endDate is null or PersonCard_endDate >= @startTime)
						and LpuAttachType_id = 1
				) pc
				outer apply (--USL_TIP
					select top 1 
						case
							when MO.MorbusOnko_id is not null then case -- в движении есть специфика по онкологии
								when OC.OnkoConsultResult_id in (1, 2, 3) then OC.OnkoHealType_id --Консилиум проведён
								else case
									when EUcount.EvnUsluga_Count = 1 then case --в специфике только один тип услуги
										when EUspec.EvnClass_SysNick = 'EvnUslugaOnkoSurg' then 1
										when EUspec.EvnClass_SysNick = 'EvnUslugaOnkoChem' then 2
										when EUspec.EvnClass_SysNick = 'EvnUslugaOnkoGormun' then 3
										when EUspec.EvnClass_SysNick = 'EvnUslugaOnkoBeam' then 4
										else 5
									end
									else 5
								end						
							end
							else case -- у движения нет специфики
								when EUmov.EvnUsluga_id is not null then 1 --в движении есть оперативная услуга
								else 5
							end		
						end as USL_TIP
					from 
						v_EvnSection ES2  (nolock)
						left join v_Morbus M (nolock) on M.Morbus_id = ES2.Morbus_id or (M.Person_id = ES2.Person_id and M.Diag_id = ES2.Diag_id)
						left join v_MorbusOnko MO (nolock) on MO.Morbus_id = M.Morbus_id
						left join v_OnkoConsult OC (nolock) on OC.MorbusOnko_id = MO.MorbusOnko_id
						outer apply (--услуги в специфике
							select top 1 *
							from v_EvnUsluga EU (nolock)
							where 
								EU.Morbus_id = M.Morbus_id 
								and (not exists(select * from v_Evn with(nolock) where Evn_id = ES2.EvnSection_id) 
									or EU.EvnUsluga_pid = ES2.EvnSection_id)
								and EU.EvnClass_SysNick in ('EvnUslugaOnkoSurg', 'EvnUslugaOnkoChem', 'EvnUslugaOnkoGormun', 'EvnUslugaOnkoBeam')
						) as EUspec
						outer apply (--подсчитываем количество разных типов услуг в специфике
							select count(distinct EU.EvnClass_SysNick) as EvnUsluga_Count
							from v_EvnUsluga EU (nolock)
							where 
								EU.Morbus_id = M.Morbus_id 
								and (not exists(select * from v_Evn with(nolock) where Evn_id = ES2.EvnSection_id) 
									or EU.EvnUsluga_pid = ES2.EvnSection_id)
								and EU.EvnClass_SysNick in ('EvnUslugaOnkoSurg', 'EvnUslugaOnkoChem', 'EvnUslugaOnkoGormun', 'EvnUslugaOnkoBeam')
						) as EUcount
						outer apply (--оперативная услуга в движении
							select top 1 EvnUsluga_id
							from v_EvnUsluga EU (nolock)
							where EU.EvnUsluga_pid = ES.EvnSection_id and EU.EvnClass_SysNick = 'EvnUslugaOper'
						) as EUmov
					where
						ES.EvnSection_id = ES2.EvnSection_id
				) as USL_TIP
				left join v_Diag d (nolock) on d.Diag_id = es.Diag_id
				left join v_Lpu ld (nolock) on ld.Lpu_id = ed.Lpu_id
				left join v_Lpu ld_eps (nolock) on ld_eps.Org_id = eps.Org_did
				left join v_LpuSection ls_eps (nolock) on ls_eps.LpuSection_id = eps.Lpusection_did
				left join v_Lpu ld_ls (nolock) on ld_ls.Lpu_id = ls_eps.Lpu_id
				left join v_Lpu lp (nolock) on lp.Lpu_id = pc.Lpu_id
				left join v_PrehospWaifRefuseCause pwrc with (nolock) on pwrc.PrehospWaifRefuseCause_id = eps.PrehospWaifRefuseCause_id
				-- пациент
				inner join v_Person_all pe (nolock) on pe.PersonEvn_id = es.PersonEvn_id
					and pe.Server_id = es.Server_id
				-- представитель пациента
				left join v_PersonDeputy pd (nolock) on pd.Person_id = pe.Person_id
				outer apply (
					select top 1
						Person_id,
						Polis_id,
						Person_EdNum,
						Person_SurName,
						Person_FirName,
						Person_SecName,
						Sex_id,
						Person_BirthDay
					from v_Person_all with(nolock)
					where Person_id = pd.Person_pid
						and PersonEvn_begDT <= ed.EvnDirection_setDT
					order by PersonEvn_insDT desc
				) d_pe
				-- идентификатор полиса пациента или его представителя
				outer apply(
					select 
						case when dbo.Age2(pe.Person_BirthDay, ed.EvnDirection_setDT) < 18 and pe.Polis_id is null and d_pe.Person_id is not null
						then d_pe.Polis_id else pe.Polis_id end as Polis_id,
						case when dbo.Age2(pe.Person_BirthDay, ed.EvnDirection_setDT) < 18 and pe.Polis_id is null and d_pe.Person_id is not null
						then d_pe.Person_EdNum else pe.Person_EdNum end as edNum
				) polisInfo
				-- данные полиса
				left join v_Polis po (nolock) on po.Polis_id = polisInfo.Polis_id
				left join v_PolisType pt (nolock) on pt.PolisType_id = po.PolisType_id and isnull(pt.PolisType_CodeF008, 0) <> 0
				left join v_OrgSMO smo (nolock) on smo.OrgSMO_id = po.OrgSmo_id
				left join v_OmsSprTerr ost (nolock) on ost.OmsSprTerr_id = po.OmsSprTerr_id
				left join v_Document doc (nolock) on doc.Document_id = pe.Document_id
				left join v_DocumentType doct (nolock) on doct.DocumentType_id = doc.DocumentType_id
				outer apply (
					select top 1 PersonChild_CountChild
					from PersonChild with (nolock) 
					where Person_id = ed.Person_id
					order by PersonChild_id desc
				) PChild
				outer apply (
					select top 1 BirthSvid_ChildCount
					from v_BirthSvid with (nolock) 
					where Person_id = ed.Person_id
					order by BirthSvid_id desc
				) BirthSvid
			where
				eps.PayType_id = @PayType_id
				and (eps.PrehospWaifRefuseCause_id is null or es.MotionType = 2)
		";

		foreach ( $lpu_arr as $lpu ) {
			$params['Lpu_id'] = $lpu['Lpu_id'];

			$queryReponse = $this->queryResult($query, $params);

			if ( $queryReponse === false || !is_array($queryReponse) /*|| count($queryReponse) == 0*/ ) {
				continue;
			}

			$exportData = array(
				'S' => array(),
				'D' => array(),
			);
			$N_ZAP = array(
				'S' => 0,
				'D' => 0,
			);

			foreach ( $queryReponse as $key => $row ) {
				if ( empty($row['NUM_NPR']) ) {
					continue;
				}

				$N_ZAP[$row['C_SERV']]++;
				$row['N_ZAP'] = $N_ZAP[$row['C_SERV']];

				if ( !empty($row['SNILS']) ) {
					if ( strlen($row['SNILS']) == 11 ) {
						$row['SNILS'] = substr($row['SNILS'], 0, 3) . '-' . substr($row['SNILS'], 3, 3) . '-' . substr($row['SNILS'], 6, 3) . ' ' . substr($row['SNILS'], -2);
					}
					else if ( strlen($row['SNILS']) != 14 ) {
						$row['SNILS'] = '';
					}
				}

				$exportData[$row['C_SERV']][] = $row;
				unset($queryReponse[$key]);
			}

			foreach ( $exportData as $key => $exportDataByStacType ) {
				/*if ( count($exportDataByStacType) == 0 ) {
					continue;
				}*/

				$fileSign = $key . 'SMT' . ($exportForPeriod == true ? 'D' : '' ) . $lpu['fcode'] . '_' . substr(str_replace('-', '', $data['Date']), 2, 6);
				$filePath = $path . $out_dir . "/" . $fileSign . ".xml";
				$zipFilePath = $path . $out_dir . "/" . $fileSign . ".zip";

				$hosp_data = array();
				$hosp_data['VERSION'] = '1.0';
				$hosp_data['DATA'] = $data['Date'];
				$hosp_data['FILENAME'] = $fileSign;
				$hosp_data['ZAP'] = $exportDataByStacType;

				$xml = "<?xml version=\"1.0\" encoding=\"windows-1251\"?>\r\n" . toAnsi($this->parser->parse('export_xml/hosp_data_for_tfoms_penza_f2', $hosp_data, true), true);
				file_put_contents($filePath, $xml);
				unset($xml);

				$zip = new ZipArchive();
				$zip->open($zipFilePath, ZIPARCHIVE::CREATE);
				$zip->AddFile($filePath, $fileSign . ".xml");
				$zip->close();

				$zipFileArray[$fileSign] = $zipFilePath;

				unlink($filePath);
			}
		}

		// 2.1 Сведения о движении пациентов по ВМП
		$query = "
			declare @finalTime datetime = cast(:DateTo + ' ' + :finalTime as datetime);
			declare @Lpu_id bigint = :Lpu_id;
			declare @startTime datetime = dateadd(day, -1, cast(:DateFrom + ' ' + :startTime as datetime));

			with EvnSectionList as (
				-- Госпитализация в приемном
				-- #120862 Исключить попадание в файл записей по дате поступления в приемное
				/*select
					1 as MotionType,
					EvnSection_id,
					EvnSection_pid,
					EvnSection_Count,
					EvnSection_Index,
					EvnSection_IsPriem,
					EvnSection_setDT,
					EvnSection_disDT,
					Lpu_id,
					LpuSection_id,
					LpuSectionProfile_id,
					Person_id,
					PersonEvn_id,
					Server_id,
					Diag_id,
					HTMedicalCareClass_id
				from v_EvnSection es with (nolock)
					inner join v_PayType pt with (nolock) on pt.PayType_id = es.PayType_id and pt.PayType_SysNick in ('bud','oms','dms','money','ovd','fbud')
				where
					(@Lpu_id is null or Lpu_id = @Lpu_id)
					and EvnSection_setDT between @startTime and @finalTime
					and ISNULL(EvnSection_IsPriem, 1) = 2
					and HTMedicalCareClass_id is not null

				union all*/

				-- Госпитализация в профильное отделение
				select
					1 as MotionType,
					EvnSection_id,
					EvnSection_pid,
					EvnSection_Count,
					EvnSection_Index,
					EvnSection_IsPriem,
					EvnSection_setDT,
					EvnSection_disDT,
					Lpu_id,
					LpuSection_id,
					LpuSectionProfile_id,
					Person_id,
					PersonEvn_id,
					Server_id,
					Diag_id,
					HTMedicalCareClass_id
				from v_EvnSection es with (nolock)
					inner join v_PayType pt with (nolock) on pt.PayType_id = es.PayType_id and pt.PayType_SysNick in ('bud','oms','dms','money','ovd','fbud')
				where
					(@Lpu_id is null or Lpu_id = @Lpu_id)
					and EvnSection_setDT between @startTime and @finalTime
					and ISNULL(EvnSection_IsPriem, 1) = 1
					and HTMedicalCareClass_id is not null

				union all

				-- Отказ в приемном
				select
					2 as MotionType,
					es.EvnSection_id,
					es.EvnSection_pid,
					es.EvnSection_Count,
					es.EvnSection_Index,
					es.EvnSection_IsPriem,
					es.EvnSection_setDT,
					es.EvnSection_disDT,
					es.Lpu_id,
					es.LpuSection_id,
					ed.LpuSectionProfile_id,
					es.Person_id,
					es.PersonEvn_id,
					es.Server_id,
					es.Diag_id,
					ed.HTMedicalCareClass_id
				from v_EvnSection es with (nolock)
					inner join v_PayType pt with (nolock) on pt.PayType_id = es.PayType_id and pt.PayType_SysNick in ('bud','oms','dms','money','ovd','fbud')
					inner join v_EvnPS eps with (nolock) on eps.EvnPS_id = es.EvnSection_pid
					inner join v_EvnDirectionHTM ed with (nolock) on ed.EvnDirectionHTM_id = eps.EvnDirection_id
				where
					(@Lpu_id is null or es.Lpu_id = @Lpu_id)
					and es.EvnSection_disDT between @startTime and @finalTime
					and ISNULL(es.EvnSection_IsPriem, 1) = 2
					and eps.PrehospWaifRefuseCause_id is not null
					and ed.HTMedicalCareClass_id is not null

				union all

				-- Выписка из профильного отделения
				select
					2 as MotionType,
					es.EvnSection_id,
					es.EvnSection_pid,
					es.EvnSection_Count,
					es.EvnSection_Index,
					es.EvnSection_IsPriem,
					es.EvnSection_setDT,
					es.EvnSection_disDT,
					es.Lpu_id,
					es.LpuSection_id,
					es.LpuSectionProfile_id,
					es.Person_id,
					es.PersonEvn_id,
					es.Server_id,
					es.Diag_id,
					es.HTMedicalCareClass_id
				from v_EvnSection es with (nolock)
					inner join v_PayType pt with (nolock) on pt.PayType_id = es.PayType_id and pt.PayType_SysNick in ('bud','oms','dms','money','ovd','fbud')
					inner join v_EvnPS eps with (nolock) on eps.EvnPS_id = es.EvnSection_pid
				where
					(@Lpu_id is null or es.Lpu_id = @Lpu_id)
					and es.EvnSection_disDT between @startTime and @finalTime
					and ISNULL(es.EvnSection_IsPriem, 1) = 1
					and es.EvnSection_Index = es.EvnSection_Count - 1
					and eps.EvnPS_disDT is not null
					and es.HTMedicalCareClass_id is not null
			)

			select
				-- Перс. данные
				 case when dbo.AgeYMD(pe.Person_BirthDay, ed.EvnDirectionHTM_directDate, 2) < 3 and doct.DocumentType_id is null then 'НЕТ' else pe.Person_SurName end as FAM
				,case when dbo.AgeYMD(pe.Person_BirthDay, ed.EvnDirectionHTM_directDate, 2) < 3 and doct.DocumentType_id is null then 'НЕТ' else pe.Person_FirName end as IM
				,case when dbo.AgeYMD(pe.Person_BirthDay, ed.EvnDirectionHTM_directDate, 2) < 3 and doct.DocumentType_id is null then 'НЕТ' else pe.Person_SecName end as OT
				,case when pe.Sex_id = 3 then 1 else pe.Sex_id end as W
				,convert(varchar(10), pe.Person_BirthDay, 120) as DR
				,case when dbo.AgeYMD(pe.Person_BirthDay, ed.EvnDirectionHTM_directDate, 2) < 3 then d_pe.Person_SurName else null end as FAM_P
				,case when dbo.AgeYMD(pe.Person_BirthDay, ed.EvnDirectionHTM_directDate, 2) < 3 then d_pe.Person_FirName else null end as IM_P
				,case when dbo.AgeYMD(pe.Person_BirthDay, ed.EvnDirectionHTM_directDate, 2) < 3 then d_pe.Person_SecName else null end as OT_P
				,case when dbo.AgeYMD(pe.Person_BirthDay, ed.EvnDirectionHTM_directDate, 2) < 3 then 
					case when d_pe.Sex_id = 3 then 1 else d_pe.Sex_id end
				 else null
				 end as W_P
				,case when dbo.AgeYMD(pe.Person_BirthDay, ed.EvnDirectionHTM_directDate, 2) < 3 then convert(varchar(10), d_pe.Person_BirthDay, 120) else null end as DR_P
				,pe.Person_Snils as SNILS
				,pt.PolisType_CodeF008 as VPOLIS
				,case when pt.PolisType_CodeF008 in (1, 2) then po.Polis_Ser else null end as SPOLIS
				,case when pt.PolisType_CodeF008 = 3 then polisInfo.edNum else po.Polis_Num end as NPOLIS
				,polisInfo.edNum as ENP
				,case when doct.DocumentType_Code in (25, 26, 27, 28, 99) then 18 else doct.DocumentType_Code end as DOCTYPE
				,doc.Document_Ser as DOCSER
				,doc.Document_Num as DOCNUM
				,'' as SMO
				,case
					when dbo.AgeYMD(pe.Person_BirthDay, ed.EvnDirectionHTM_directDate, 2) <= 3
						then 
							case when isnull(pe.Sex_id, 1) = 1 then '1' else '2' end +
							RIGHT(convert(varchar(10), pe.Person_BirthDay, 120), 2) +
							SUBSTRING(convert(varchar(10), pe.Person_BirthDay, 120), 6, 2) +
							SUBSTRING(convert(varchar(10), pe.Person_BirthDay, 120), 3, 2) +
							CAST(COALESCE(PChild.PersonChild_CountChild, BirthSvid.BirthSvid_ChildCount, 1) as char(1))
						else '0'
				 end as NOVOR
				,eps.EvnPS_NumCard as NHISTORY
				,case when ost.OmsSprTerr_Code = 1156 then '56000' else '0' end as SUBJ

				-- Данные направления
				,case
					when ed.EvnDirectionHTM_Num is not null and ISNULL(ed.EvnDirectionHTM_IsAuto, 1) = 1 then ld.Lpu_f003mcod + '_' + cast(ed.EvnDirectionHTM_Num as varchar)
					when eps.EvnDirection_Num is not null and eps.PrehospDirect_id = 1 then l.Lpu_f003mcod + '_' + cast(eps.EvnDirection_Num as varchar)
					when eps.EvnDirection_Num is not null and ld_eps.Lpu_f003mcod is not null then ld_eps.Lpu_f003mcod + '_' + cast(eps.EvnDirection_Num as varchar)
					when eps.EvnDirection_Num is not null and ld_ls.Lpu_f003mcod is not null then ld_ls.Lpu_f003mcod + '_' + cast(eps.EvnDirection_Num as varchar)
					when ed.EvnDirectionHTM_Num is not null and ISNULL(ed.EvnDirectionHTM_IsAuto, 1) = 2 then ld.Lpu_f003mcod + '_' + cast(ed.EvnDirectionHTM_Num as varchar)
					else l.Lpu_f003mcod + '_Э' + cast(eps.EvnPS_id as varchar)
				 end as NUM_NPR
			 	,nnm.NUM_NPR_MAIN
			 	,eps.EvnPS_HTMTicketNum as TALON_VMP
			 	,case
			 		when payt.PayType_SysNick = 'bud' then 1
			 		when payt.PayType_SysNick = 'oms' then 2
			 		when payt.PayType_SysNick = 'dms' then 3
			 		when payt.PayType_SysNick = 'money' then 4
			 		when payt.PayType_SysNick = 'ovd' then 5
			 		when payt.PayType_SysNick = 'fbud' then 6
			 	end as IST_FIN
				,case when lu.LpuUnitType_id = 1 then 'S' else 'D' end as C_SERV
				,COALESCE(ld.Lpu_f003mcod, ld_eps.Lpu_f003mcod, ld_ls.Lpu_f003mcod, lp.Lpu_f003mcod) as MO_SRC
				,l.Lpu_f003mcod as MO_DST
				,RIGHT('000' + htmcc.HTMedicalCareClass_Code, 3) + RIGHT('000' + lsp.LpuSectionProfile_Code, 3) as PROFIL
				,USL_TIP.USL_TIP as USL_TIP
				,case when eps.PrehospWaifRefuseCause_id is not null then 1 else 0 end as OTKAZ
				,case
					when pwrc.PrehospWaifRefuseCause_Code in (1) then 3
					when pwrc.PrehospWaifRefuseCause_Code in (2) then 4
					when pwrc.PrehospWaifRefuseCause_Code in (3) then 2
					when pwrc.PrehospWaifRefuseCause_Code in (4, 6, 7, 8) then 10
					when pwrc.PrehospWaifRefuseCause_Code in (5) then 1
					when pwrc.PrehospWaifRefuseCause_Code in (9) then 8
					when pwrc.PrehospWaifRefuseCause_Code in (10) then 9
					when pwrc.PrehospWaifRefuseCause_Code in (11) then 7
					else null
				 end as REASON
				,case
					when eps.MedicalCareFormType_id = 1 then 3
					when eps.MedicalCareFormType_id = 2 then 2
					when eps.MedicalCareFormType_id = 3 then 1
				 end as FOR_POM
				,case
					when eps.PrehospWaifRefuseCause_id is not null then 5
					when es.MotionType = 1 and es.EvnSection_Index in (0, 1) then 1
					when es.MotionType = 1 and es.EvnSection_Index > 1 then 3
					when es.MotionType = 2 and es.EvnSection_IsPriem = 2 then 5
					when es.MotionType = 2 then 2
				 end as TYPE_MOTION
				,convert(varchar(19), case when es.MotionType = 2 then es.EvnSection_disDT else es.EvnSection_setDT end, 120) as DATE_MOTION
				,convert(varchar(19), nnm.EvnPS_setDT, 120) as DATE_BEG
				,d.Diag_Code as DS1
				,null as COMMENT
			from EvnSectionList es
				inner join v_EvnPS eps with (nolock) on eps.EvnPS_id = es.EvnSection_pid
				inner join v_PayType payt with (nolock) on payt.PayType_id = eps.PayType_id and payt.PayType_SysNick in ('bud','oms','dms','money','ovd','fbud')
				inner join v_Lpu l (nolock) on l.Lpu_id = eps.Lpu_id
				inner join v_LpuSection ls (nolock) on ls.LpuSection_id = es.LpuSection_id
				inner join v_LpuUnit lu (nolock) on lu.LpuUnit_id = ls.LpuUnit_id
				inner join v_LpuSectionProfile lsp with (nolock) on lsp.LpuSectionProfile_id = ISNULL(es.LpuSectionProfile_id, ls.LpuSectionProfile_id)
				left join v_EvnDirectionHTM ed with (nolock) on ed.EvnDirectionHTM_id = eps.EvnDirection_id
				left join v_HTMedicalCareClass htmcc with (nolock) on htmcc.HTMedicalCareClass_id = es.HTMedicalCareClass_id
				outer apply (
					select top 1 Lpu_id
					from v_PersonCard_all with (nolock)
					where
						Person_id = es.Person_id
						and PersonCard_begDate <= @finalTime
						and (PersonCard_endDate is null or PersonCard_endDate >= @startTime)
						and LpuAttachType_id = 1
				) pc
				outer apply (--USL_TIP
					select top 1 
						case
							when MO.MorbusOnko_id is not null then case -- в движении есть специфика по онкологии
								when OC.OnkoConsultResult_id in (1, 2, 3) then OC.OnkoHealType_id --Консилиум проведён
								else case
									when EUcount.EvnUsluga_Count = 1 then case --в специфике только один тип услуги
										when EUspec.EvnClass_SysNick = 'EvnUslugaOnkoSurg' then 1
										when EUspec.EvnClass_SysNick = 'EvnUslugaOnkoChem' then 2
										when EUspec.EvnClass_SysNick = 'EvnUslugaOnkoGormun' then 3
										when EUspec.EvnClass_SysNick = 'EvnUslugaOnkoBeam' then 4
										else 5
									end
									else 5
								end						
							end
							else case -- у движения нет специфики
								when EUmov.EvnUsluga_id is not null then 1 --в движении есть оперативная услуга
								else 5
							end		
						end as USL_TIP
					from 
						v_EvnSection ES2  (nolock)
						left join v_Morbus M (nolock) on M.Morbus_id = ES2.Morbus_id or (M.Person_id = ES2.Person_id and M.Diag_id = ES2.Diag_id)
						left join v_MorbusOnko MO (nolock) on MO.Morbus_id = M.Morbus_id
						left join v_OnkoConsult OC (nolock) on OC.MorbusOnko_id = MO.MorbusOnko_id
						outer apply (--услуги в специфике
							select top 1 *
							from v_EvnUsluga EU (nolock)
							where 
								EU.Morbus_id = M.Morbus_id 
								and (not exists(select * from v_Evn with(nolock) where Evn_id = ES2.EvnSection_id) 
									or EU.EvnUsluga_pid = ES2.EvnSection_id)
								and EU.EvnClass_SysNick in ('EvnUslugaOnkoSurg', 'EvnUslugaOnkoChem', 'EvnUslugaOnkoGormun', 'EvnUslugaOnkoBeam')
						) as EUspec
						outer apply (--подсчитываем количество разных типов услуг в специфике
							select count(distinct EU.EvnClass_SysNick) as EvnUsluga_Count
							from v_EvnUsluga EU (nolock)
							where 
								EU.Morbus_id = M.Morbus_id 
								and (not exists(select * from v_Evn with(nolock) where Evn_id = ES2.EvnSection_id) 
									or EU.EvnUsluga_pid = ES2.EvnSection_id)
								and EU.EvnClass_SysNick in ('EvnUslugaOnkoSurg', 'EvnUslugaOnkoChem', 'EvnUslugaOnkoGormun', 'EvnUslugaOnkoBeam')
						) as EUcount
						outer apply (--оперативная услуга в движении
							select top 1 EvnUsluga_id
							from v_EvnUsluga EU (nolock)
							where EU.EvnUsluga_pid = ES.EvnSection_id and EU.EvnClass_SysNick = 'EvnUslugaOper'
						) as EUmov
					where
						ES.EvnSection_id = ES2.EvnSection_id
				) as USL_TIP
				left join v_Diag d (nolock) on d.Diag_id = es.Diag_id
				left join v_Lpu ld (nolock) on ld.Lpu_id = ed.Lpu_id
				left join v_Lpu ld_eps (nolock) on ld_eps.Org_id = eps.Org_did
				left join v_LpuSection ls_eps (nolock) on ls_eps.LpuSection_id = eps.Lpusection_did
				left join v_Lpu ld_ls (nolock) on ld_ls.Lpu_id = ls_eps.Lpu_id
				left join v_Lpu lp (nolock) on lp.Lpu_id = pc.Lpu_id
				left join v_PrehospWaifRefuseCause pwrc with (nolock) on pwrc.PrehospWaifRefuseCause_id = eps.PrehospWaifRefuseCause_id
				-- пациент
				inner join v_Person_all pe (nolock) on pe.PersonEvn_id = es.PersonEvn_id
					and pe.Server_id = es.Server_id
				-- представитель пациента
				left join v_PersonDeputy pd (nolock) on pd.Person_id = pe.Person_id
				outer apply (
					select top 1
						Person_id,
						Polis_id,
						Person_EdNum,
						Person_SurName,
						Person_FirName,
						Person_SecName,
						Sex_id,
						Person_BirthDay
					from v_Person_all with(nolock)
					where Person_id = pd.Person_pid
						and PersonEvn_begDT <= ed.EvnDirectionHTM_directDate
					order by PersonEvn_insDT desc
				) d_pe
				-- идентификатор полиса пациента или его представителя
				outer apply(
					select 
						case when dbo.Age2(pe.Person_BirthDay, ed.EvnDirectionHTM_directDate) < 18 and pe.Polis_id is null and d_pe.Person_id is not null
						then d_pe.Polis_id else pe.Polis_id end as Polis_id,
						case when dbo.Age2(pe.Person_BirthDay, ed.EvnDirectionHTM_directDate) < 18 and pe.Polis_id is null and d_pe.Person_id is not null
						then d_pe.Person_EdNum else pe.Person_EdNum end as edNum
				) polisInfo
				-- данные полиса
				left join v_Polis po (nolock) on po.Polis_id = polisInfo.Polis_id
				left join v_PolisType pt (nolock) on pt.PolisType_id = po.PolisType_id and isnull(pt.PolisType_CodeF008, 0) <> 0
				left join v_OrgSMO smo (nolock) on smo.OrgSMO_id = po.OrgSmo_id
				left join v_OmsSprTerr ost (nolock) on ost.OmsSprTerr_id = po.OmsSprTerr_id
				left join v_Document doc (nolock) on doc.Document_id = pe.Document_id
				left join v_DocumentType doct (nolock) on doct.DocumentType_id = doc.DocumentType_id
				outer apply (
					select top 1 PersonChild_CountChild
					from PersonChild with (nolock) 
					where Person_id = ed.Person_id
					order by PersonChild_id desc
				) PChild
				outer apply (
					select top 1 BirthSvid_ChildCount
					from v_BirthSvid with (nolock) 
					where Person_id = ed.Person_id
					order by BirthSvid_id desc
				) BirthSvid
				outer apply (
					select top 1
						case
							when ed2.EvnDirection_Num is not null and ISNULL(ed2.EvnDirection_IsAuto, 1) = 1 then ld.Lpu_f003mcod + '_' + cast(ed2.EvnDirection_Num as varchar)
							when eps.EvnDirection_Num is not null and eps.PrehospDirect_id = 1 then l.Lpu_f003mcod + '_' + cast(eps.EvnDirection_Num as varchar)
							when eps.EvnDirection_Num is not null and ld_eps.Lpu_f003mcod is not null then ld_eps.Lpu_f003mcod + '_' + cast(eps.EvnDirection_Num as varchar)
							when eps.EvnDirection_Num is not null and ld_ls.Lpu_f003mcod is not null then ld_ls.Lpu_f003mcod + '_' + cast(eps.EvnDirection_Num as varchar)
							when ed2.EvnDirection_Num is not null and ISNULL(ed2.EvnDirection_IsAuto, 1) = 2 then ld.Lpu_f003mcod + '_' + cast(ed2.EvnDirection_Num as varchar)
							else l.Lpu_f003mcod + '_Э' + cast(eps.EvnPS_id as varchar)
						 end as NUM_NPR_MAIN,
						 eps.EvnPS_setDT
					from
						v_EvnPS eps (nolock)
						left join v_EvnDirection_all ed2 with (nolock) on ed2.EvnDirection_id = eps.EvnDirection_id
						inner join v_Lpu l (nolock) on l.Lpu_id = eps.Lpu_id
						left join v_Lpu ld (nolock) on ld.Lpu_id = ed2.Lpu_id
						left join v_Lpu ld_eps (nolock) on ld_eps.Org_id = eps.Org_did
						left join v_LpuSection ls_eps (nolock) on ls_eps.LpuSection_id = eps.Lpusection_did
						left join v_Lpu ld_ls (nolock) on ld_ls.Lpu_id = ls_eps.Lpu_id
						left join v_EvnPrescrVK epvk (nolock) on epvk.EvnPrescrVK_pid = eps.EvnPS_id
						left join v_EvnVK evk (nolock) on evk.EvnPrescrVK_id = epvk.EvnPrescrVK_id
					where
						eps.EvnPS_id = es.EvnSection_pid
						and ed.EvnDirectionHTM_pid = evk.EvnVK_id
				) nnm
		";

		foreach ( $lpu_arr as $lpu ) {
			$params['Lpu_id'] = $lpu['Lpu_id'];

			$queryReponse = $this->queryResult($query, $params);

			if ( $queryReponse === false || !is_array($queryReponse) /*|| count($queryReponse) == 0*/ ) {
				continue;
			}

			$exportData = array();
			$N_ZAP = 0;

			foreach ( $queryReponse as $key => $row ) {
				if ( empty($row['NUM_NPR']) ) {
					continue;
				}

				$N_ZAP++;
				$row['N_ZAP'] = $N_ZAP;

				if ( !empty($row['SNILS']) ) {
					if ( strlen($row['SNILS']) == 11 ) {
						$row['SNILS'] = substr($row['SNILS'], 0, 3) . '-' . substr($row['SNILS'], 3, 3) . '-' . substr($row['SNILS'], 6, 3) . ' ' . substr($row['SNILS'], -2);
					}
					else if ( strlen($row['SNILS']) != 14 ) {
						$row['SNILS'] = '';
					}
				}

				$exportData[] = $row;
				unset($queryReponse[$key]);
			}

			/*if ( count($exportData) == 0 ) {
				continue;
			}*/

			$fileSign = 'SVMP' . ($exportForPeriod == true ? 'D' : '' ) . $lpu['fcode'] . '_' . substr(str_replace('-', '', $data['Date']), 2, 6);
			$filePath = $path . $out_dir . "/" . $fileSign . ".xml";
			$zipFilePath = $path . $out_dir . "/" . $fileSign . ".zip";

			$hosp_data = array();
			$hosp_data['VERSION'] = '1.0';
			$hosp_data['DATA'] = $data['Date'];
			$hosp_data['FILENAME'] = $fileSign;
			$hosp_data['ZAP'] = $exportData;

			$xml = "<?xml version=\"1.0\" encoding=\"windows-1251\"?>\r\n" . toAnsi($this->parser->parse('export_xml/hosp_data_for_tfoms_penza_f5', $hosp_data, true), true);
			file_put_contents($filePath, $xml);
			unset($xml);

			$zip = new ZipArchive();
			$zip->open($zipFilePath, ZIPARCHIVE::CREATE);
			$zip->AddFile($filePath, $fileSign . ".xml");
			$zip->close();

			$zipFileArray[$fileSign] = $zipFilePath;

			unlink($filePath);
		}

		// 3. Сведения о движении коечного фонда
		$query = "
			declare @finalTime datetime = cast(:DateTo + ' ' + :finalTime as datetime);
			declare @Lpu_id bigint = :Lpu_id;
			declare @startTime datetime = dateadd(day, -1, cast(:DateFrom + ' ' + :startTime as datetime));

			select
				 l.Lpu_f003mcod as MO
				,case when lu.LpuUnitType_id = 1 then 'S' else 'D' end as C_SERV
				,lsp.LpuSectionProfile_Code as PROFIL
				,ISNULL(sum(case when ISNULL(lsw.Sex_id, 1) = 1 and ISNULL(ls.LpuSectionAge_id, 1) in (1, 3) then lsw.LpuSectionWard_BedCount else null end), 0) as FREE_M
				,ISNULL(sum(case when ISNULL(lsw.Sex_id, 2) = 2 and ISNULL(ls.LpuSectionAge_id, 1) in (1, 3) then lsw.LpuSectionWard_BedCount else null end), 0) as FREE_W
				,ISNULL(sum(case when ISNULL(ls.LpuSectionAge_id, 2) = 2 then lsw.LpuSectionWard_BedCount else null end), 0) as FREE_CH
				,null as VOLUME
				,null as COMMENT
			from v_LpuSection ls with (nolock)
				inner join v_LpuUnit lu (nolock) on lu.LpuUnit_id = ls.LpuUnit_id
				inner join v_Lpu l (nolock) on l.Lpu_id = ls.Lpu_id
				inner join v_LpuSectionProfile lsp with (nolock) on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
				left join v_LpuSectionWard lsw with (nolock) on lsw.LpuSection_id = ls.LpuSection_id
					and (lsw.LpuSectionWard_setDate is null or lsw.LpuSectionWard_setDate <= :DateTo)
					and (lsw.LpuSectionWard_disDate is null or lsw.LpuSectionWard_disDate >= :DateFrom)
			where
				ls.Lpu_id = @Lpu_id
				and lu.LpuUnitType_id in (1, 6, 7, 9)
				and (ls.LpuSection_setDate is null or ls.LpuSection_setDate <= @finalTime)
				and (ls.LpuSection_disDate is null or ls.LpuSection_disDate >= @startTime)
			group by
				l.Lpu_f003mcod,
				case when lu.LpuUnitType_id = 1 then 'S' else 'D' end,
				lsp.LpuSectionProfile_Code
		";

		foreach ( $lpu_arr as $lpu ) {
			$params['Lpu_id'] = $lpu['Lpu_id'];

			$queryReponse = $this->queryResult($query, $params);

			if ( $queryReponse === false || !is_array($queryReponse) /*|| count($queryReponse) == 0*/ ) {
				continue;
			}

			$exportData = array(
				'S' => array(),
				'D' => array(),
			);

			foreach ( $queryReponse as $key => $row ) {
				$exportData[$row['C_SERV']][] = $row;
				unset($queryReponse[$key]);
			}

			foreach ( $exportData as $key => $exportDataByStacType ) {
				/*if ( count($exportDataByStacType) == 0 ) {
					continue;
				}*/

				$fileSign = $key . 'DKM' . ($exportForPeriod == true ? 'D' : '' ) . $lpu['fcode'] . '_' . substr(str_replace('-', '', $data['Date']), 2, 6);
				$filePath = $path . $out_dir . "/" . $fileSign . ".xml";
				$zipFilePath = $path . $out_dir . "/" . $fileSign . ".zip";

				$hosp_data = array();
				$hosp_data['VERSION'] = '1.0';
				$hosp_data['DATA'] = $data['Date'];
				$hosp_data['FILENAME'] = $fileSign;
				$hosp_data['ZAP'] = $exportDataByStacType;

				$xml = "<?xml version=\"1.0\" encoding=\"windows-1251\"?>\r\n" . toAnsi($this->parser->parse('export_xml/hosp_data_for_tfoms_penza_f3', $hosp_data, true), true);
				file_put_contents($filePath, $xml);
				unset($xml);

				$zip = new ZipArchive();
				$zip->open($zipFilePath, ZIPARCHIVE::CREATE);
				$zip->AddFile($filePath, $fileSign . ".xml");
				$zip->close();

				$zipFileArray[$fileSign] = $zipFilePath;

				unlink($filePath);
			}
		}

		// Собераем единый ZIP-файл
		if ( count($zipFileArray) > 0 ) {
			$zipFilePath = $path . $out_dir . "/output" . time() . ".zip";

			$zip = new ZipArchive();
			$zip->open($zipFilePath, ZIPARCHIVE::CREATE);

			foreach ( $zipFileArray as $sign => $file ) {
				$zip->AddFile($file, $sign . ".zip");
			}

			$zip->close();

			foreach ( $zipFileArray as $sign => $file ) {
				unlink($file);
			}

			$response['Link'] = $zipFilePath;
		}
		else {
			$response['success'] = false;
			$response['Error_Msg'] = 'Нет данных для выгрузки';
		}

		return $response;
	}
}