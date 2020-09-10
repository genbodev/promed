<?php	defined('BASEPATH') or die ('No direct script access allowed');

require_once(APPPATH.'models/EvnPS_model.php');

class Vologda_EvnPS_model extends EvnPS_model {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}
	
	/**
	 * Выгрузка данных для ТФОМС и СМО
	 */
	function exportHospDataForTfomsToXml($data) {
		$params = array(
			'Date' => $data['Date'],
			'Lpu_id' => $data['Lpu_id'],
			'startTime' => '20:00',
			'finalTime' => '19:59',
		);

		if ($data['ARMType'] == 'superadmin') {
			$params['Lpu_id'] = null;
		}
		
		$lpu_arr = $this->getMoRegion($params);
		if(!$lpu_arr) return false;
		
		$hosp_data_xml_arr = array();
		
		foreach($lpu_arr as $lpu) {
			$params['Lpu_id'] = $lpu['Lpu_id'];
			$params['hasStac'] = $lpu['hasStac'];
			$params['hasPolka'] = $lpu['hasPolka'];			
			$hosp_data_xml_arr[$lpu['fcode']] = $this->getDataForExport($params);
		}

		return $hosp_data_xml_arr;
	}
	
	/*
	 * Получение данных МО для выгрузки
	 */
	function getMoRegion($data){
		$query = "
			select
			lp.Lpu_id,
			RIGHT(lp.Lpu_f003mcod, 4) as fcode,
			case when exists(
				select * from v_LpuUnit t with(nolock)
				where t.Lpu_id = lp.Lpu_id and t.LpuUnitType_SysNick = 'polka'
			) then 1 else 0 end as hasPolka,
			case when exists(
				select * from v_LpuUnit t with(nolock)
				where t.Lpu_id = lp.Lpu_id and t.LpuUnitType_SysNick IN ('stac', 'dstac', 'pstac', 'hstac')
			) then 1 else 0 end as hasStac
			from v_Lpu lp with(nolock)
			where
			lp.Lpu_f003mcod is not null
			and lp.Lpu_f003mcod <> '0'
			and (:Lpu_id is null or lp.Lpu_id = :Lpu_id)
		";
		
		$res = $this->db->query($query, $data);
		if (is_object($res)) {
			$lpu_arr = $res->result('array');
			return $lpu_arr;
		} else {
			return false;
		}
	}
	
	/*
	 * получить данные дял экспорта
	 */
	function getDataForExport($data){
		$query = "
			declare @Lpu_id bigint = :Lpu_id

			declare @date datetime = :Date
			declare @startTime datetime = dateadd(day, -2, cast(@date+' '+:startTime as datetime))
			declare @finalTime datetime = dateadd(day, -1, cast(@date+' '+:finalTime as datetime))
			declare @hasPolka int = :hasPolka
			declare @hasStac int = :hasStac

			declare @S1 VARCHAR (MAX)=''
			declare @S2 VARCHAR (MAX)=''
			declare @S3 VARCHAR (MAX)=''
			declare @S4 VARCHAR (MAX)=''
			declare @S5 VARCHAR (MAX)=''
			declare @S6 VARCHAR (MAX)=''

			declare @PayType_id bigint;
			set @PayType_id = (select top 1 PayType_id from v_PayType with (nolock) where PayType_SysNick = 'oms');

			--GetReferToHosp
			if @hasPolka = 1
			SET @S1 = isnull((
				Select
					ISNULL(cast(RIGHT(lp.Lpu_f003mcod, 4) as varchar)
				+ RIGHT('000000'+CAST(ed.EvnDirection_Num AS VARCHAR),6), '') as NOM_NAP,
					cast(ed.EvnDirection_setDT as date) as DTA_NAP,
					case 
						WHEN dt.DirType_Code = 5 then 2
						WHEN dt.DirType_Code = 1 then 3
					END as FRM_MP, -- Int - Форма оказания медицинской помощи
					RIGHT('0000'+CAST(ISNULL(lp.Lpu_f003mcod, '') AS VARCHAR),4) as MCOD_NAP, -- МО, направившая на госпитализацию	(реестровый номер, F003)
					RIGHT('0000'+CAST(ISNULL(lp1.Lpu_f003mcod, '') AS VARCHAR),4) as MCOD_STC, -- МО, куда направлен пациент	(реестровый номер, F003)
					pt.PolisType_CodeF008 as VPOLIS,-- numeric(1, 0) , -- Тип документа, подтверждающего факт страхования по ОМС (F008)
					ISNULL(case when pt.PolisType_CodeF008 in (1, 2) then po.Polis_Ser else null end, '') as SPOLIS, -- Серия документа
					CASE 
						WHEN pt.PolisType_CodeF008 = 3 THEN pe.Person_EdNum 
						WHEN pt.PolisType_CodeF008 in (1, 2) THEN po.Polis_Num 
						ELSE ''
					END as NPOLIS, -- Numeric(20) - Номер документа,
					ISNULL(org.Org_OGRN, '') AS SMO_OGRN, --T(15)
					ISNULL(org.Org_OKATO, '') as ST_OKATO, --Т(5) ОКАТО территории страхования
					ISNULL(pe.Person_SurName, '') as FAM, --varchar(30) – Фамилия
					ISNULL(pe.Person_FirName, '') as IM, --varchar(30) – Имя
					ISNULL(pe.Person_SecName, '') as OT, --varchar(30) – Отчество
					case when pe.Sex_id=3 then 1 else pe.Sex_id end as W, --numeric(1) – пол (1 - муж, 2 - жен)
					CAST(pe.Person_BirthDay as date) as DR, --date – дата рождения
						--dbo.Age2(pe.Person_BirthDay, @date) as PersonAge,
						--fedLSBP.PersonAgeGroup_id,
					isnull(pe.PersonPhone_Phone, '-') as TLF, --Varchar(36) - Контактная информация
					di.Diag_Code as DS, --Char(4) - Код диагноза по МКБ
					ISNULL(lspf.LpuSectionProfile_Code, '') as KOD_PFO, --numeric(4, 0) - Код профиля
					ISNULL(fedLSBP.LpuSectionBedProfile_Code, '') AS KOD_PFK,
					case when LEN(mps.Person_Snils) = 11 then SUBSTRING(mps.Person_Snils,1,3)+'-'+SUBSTRING(mps.Person_Snils,4,3)+'-'+SUBSTRING(mps.Person_Snils,7,3)+'-'+SUBSTRING(mps.Person_Snils,10,2) else '' end as KOD_DCT,
					CAST(COALESCE(tt.TimeTableStac_setDate, ed.EvnDirection_setDate) AS DATE)  AS DTA_PLN,
					case
						when LUT1.LpuUnitType_Code = 2 then 1 -- круглосуточный
						ELSE 2
					end as USL_OK
				from dbo.v_EvnDirection ed (nolock)
					left join dbo.v_DirType dt (nolock) on dt.DirType_id = ed.DirType_id
					inner join v_lpu lp (nolock) on lp.Lpu_id = ed.Lpu_id
					inner join v_lpu lp1 (nolock) on lp1.Lpu_id = ed.Lpu_did
					inner join dbo.v_Person_all pe (nolock) on pe.PersonEvn_id = ed.PersonEvn_id and pe.Server_id = ed.Server_id
					left join dbo.v_Polis po (nolock) on po.Polis_id = pe.Polis_id
					left join dbo.PolisType pt (nolock) on pt.PolisType_id = po.PolisType_id and pt.PolisType_CodeF008 is not NULL
					left join dbo.v_OrgSMO smo (nolock) on smo.OrgSMO_id = po.OrgSmo_id
					left join dbo.v_Org org (nolock) on org.Org_id = smo.Org_id
					inner join dbo.v_Diag di (nolock) on di.Diag_id = ed.Diag_id
					left join dbo.v_LpuSection ls (nolock) on ls.LpuSection_id = ed.LpuSection_id
					left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = ed.LpuSectionProfile_id
					left join fed.v_LpuSectionProfile lspf (nolock) on lspf.LpuSectionProfile_id = lsp.LpuSectionProfile_fedid
					inner join dbo.v_MedPersonal mp (nolock) on ed.MedPersonal_id = mp.MedPersonal_id and mp.Lpu_id = ed.Lpu_id
					inner join dbo.v_PersonState mps (nolock) on mps.Person_id = mp.Person_id
					left join dbo.v_TimeTableStac_lite tt (nolock) on tt.EvnDirection_id = ed.EvnDirection_id
					inner join dbo.v_LpuSection ls1 (nolock) on ls1.LpuSection_id = ed.LpuSection_did
					inner join dbo.v_LpuUnit lu1 (nolock) on ls1.LpuUnit_id = lu1.LpuUnit_id
					left join dbo.v_LpuUnitType LUT1 (nolock) on LUT1.LpuUnitType_id = lu1.LpuUnitType_id
					OUTER APPLY(
						SELECT TOP 1 LSBP.LpuSectionBedProfile_Code, PAG.PersonAgeGroup_id, PAG.PersonAgeGroup_Code
						FROM fed.v_LpuSectionBedProfile LSBP with (NOLOCK)
							INNER JOIN fed.v_LpuSectionBedProfileLink LSBPLink WITH(NOLOCK) on LSBP.LpuSectionBedProfile_id = LSBPLink.LpuSectionBedProfile_fedid
							LEFT JOIN dbo.v_LpuSectionBedProfile dboLSBP with (nolock) on dboLSBP.LpuSectionBedProfile_id = LSBPLink.LpuSectionBedProfile_id
							LEFT JOIN PersonAgeGroup PAG on PAG.PersonAgeGroup_id = dboLSBP.PersonAgeGroup_id
						WHERE 
							dboLSBP.LpuSectionBedProfile_id = ls1.LpuSectionBedProfile_id
							AND PAG.PersonAgeGroup_Code = CASE WHEN dbo.Age2(pe.Person_BirthDay, @date)>18 THEN 1 ELSE 2 END 
						ORDER BY LSBP.LpuSectionBedProfile_begDT DESC
					) fedLSBP
				where (1=1)
					and ed.DirType_id in (1,5)	
					and ISNULL(ed.PayType_id, @PayType_id) = @PayType_id	-- Вид оплаты ОМС
					and (@Lpu_id is null or ed.Lpu_id=@Lpu_id)
					and ed.EvnDirection_setDT>=@startTime and ed.EvnDirection_setDT<=@finalTime
				for xml path('ZAP')
			),'')

			--GetHospPlan
			if @hasStac = 1
			SET @S2= isnull((
				select
					ISNULL(cast(RIGHT(lp.Lpu_f003mcod, 4) as varchar)
					+ RIGHT('000000'+CAST(ed.EvnDirection_Num AS VARCHAR),6), '') as NOM_NAP,
					cast(ed.EvnDirection_setDT as date) as DTA_NAP,
					'3' as FRM_MP, -- Int - Форма оказания медицинской помощи
					RIGHT('0000'+CAST(ISNULL(lp1.Lpu_f003mcod, '') AS VARCHAR),4) as MCOD_STC, -- МО, куда направлен пациент	(реестровый номер, F003)
					RIGHT('0000'+CAST(ISNULL(lp.Lpu_f003mcod, '') AS VARCHAR),4) as MCOD_NAP, -- МО, направившая на госпитализацию	(реестровый номер, F003)
					cast(eps.EvnPS_setDate as date) as DTA_FKT, --Дата фактической госпитализации
					case when len(DATENAME(hour, eps.EvnPS_setTime)) = 1 then '0'+DATENAME(hour, eps.EvnPS_setTime) else DATENAME(hour, eps.EvnPS_setTime) end+'-'
						+case when len(DATENAME(minute, eps.EvnPS_setTime)) = 1 then '0'+DATENAME(minute, eps.EvnPS_setTime) else DATENAME(minute, eps.EvnPS_setTime) end
					as TIM_FKT, --Время фактической госпитализации
					pt.PolisType_CodeF008 as VPOLIS,
					ISNULL(case when pt.PolisType_CodeF008 in (1, 2) then po.Polis_Ser else null end, '') as SPOLIS, -- Серия документа
					CASE 
						WHEN pt.PolisType_CodeF008 = 3 THEN pe.Person_EdNum 
						WHEN pt.PolisType_CodeF008 in (1, 2) THEN po.Polis_Num 
						ELSE ''
					END as NPOLIS, -- Numeric(20) - Номер документа,
					ISNULL(pe.Person_SurName, '') as FAM, --varchar(30) – Фамилия
					ISNULL(pe.Person_FirName, '') as IM, --varchar(30) – Имя
					ISNULL(pe.Person_SecName, '') as OT, --varchar(30) – Отчество
					case when pe.Sex_id=3 then 1 else pe.Sex_id end as W, --numeric(1) – пол (1 - муж, 2 - жен)
					CAST(pe.Person_BirthDay as date) as DR, --date – дата рождения

					EvnSec.LpuSectionBedProfile_Code as KOD_PFK, --numeric(10, 0) - Профиль койки
					EvnSec.LpuSectionProfile_Code as KOD_PFO, --numeric(3, 0) - Код отделения (профиль)
					ISNULL(eps.EvnPS_NumCard, '') as NHISTORY, --Номер карты стационарного больного
					COALESCE(DiagPID.Diag_Code, EvnSec.Diag_Code) AS DS --Диагноз приемного отделения
				from dbo.v_EvnPS eps (nolock)
					INNER JOIN dbo.v_EvnDirection ed (nolock) on ed.EvnDirection_id = eps.EvnDirection_id
					INNER JOIN v_lpu lp (nolock) on lp.Lpu_id = ed.Lpu_id
					INNER JOIN v_lpu lp1 (nolock) on lp1.Lpu_id = ed.Lpu_did
					INNER JOIN dbo.v_Person_all pe (nolock) on pe.PersonEvn_id = ed.PersonEvn_id and pe.Server_id = ed.Server_id
					LEFT JOIN dbo.v_PrehospType prt (nolock) on eps.PrehospType_id=prt.PrehospType_id
					LEFT JOIN dbo.v_Polis po (nolock) on po.Polis_id = pe.Polis_id
					LEFT JOIN dbo.PolisType pt (nolock) on pt.PolisType_id = po.PolisType_id and pt.PolisType_CodeF008 is not NULL ---Информация по пациентам с полисом ДМС в выгрузку не попадает
					LEFT JOIN dbo.v_LpuSection ls (nolock) on ls.LpuSection_id = eps.LpuSection_id
					LEFT JOIN dbo.v_LpuSectionBedProfile lsb (nolock) on lsb.LpuSectionBedProfile_id = ls.LpuSectionBedProfile_id
					LEFT JOIN v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
					LEFT JOIN v_Diag DiagPID with(nolock) on DiagPID.Diag_id = eps.Diag_pid
					outer apply(
						select top 1 
							es.EvnSection_id,
							lses.LpuSection_Code, 
							ISNULL(lses.LpuSectionProfile_Code, '') AS LpuSectionProfile_Code, 
							ISNULL(d.Diag_Code, '') AS Diag_Code, 
							ISNULL(LSBP.LpuSectionBedProfile_Code, 0) AS LpuSectionBedProfile_Code
						from v_EvnSection es with(nolock)
							inner join v_Lpusection lses with(nolock) on lses.Lpusection_id = es.lpusection_id
							LEFT JOIN fed.v_LpuSectionBedProfileLink LSBPL with(nolock) ON LSBPL.LpuSectionBedProfileLink_id = es.LpuSectionBedProfile_id
							LEFT JOIN fed.v_LpuSectionBedProfile LSBP with(nolock) on LSBP.LpuSectionBedProfile_id = LSBPL.LpuSectionBedProfile_fedid
							LEFT JOIN v_Diag d with(nolock) on d.Diag_id = es.Diag_id
						where es.Evnsection_pid = eps.EvnPS_id and isnull(EvnSection_isPriem,1)!=2
						order by EvnSection_setDate desc
					)EvnSec
				where (1=1)
					and (@Lpu_id is null or eps.Lpu_id=@Lpu_id)
					and eps.EvnPS_setDT>=@startTime
					and eps.EvnPS_setDT<=@finalTime
					AND EvnSec.EvnSection_id IS NOT null --создано движение в профильном отделении.
					and eps.PrehospWaifRefuseCause_id is null
					and prt.PrehospType_Code = 1	--Тип госпитализации «1. Планово»
					and eps.PayType_id = @PayType_id
					for xml path('ZAP')
			), '')

			--GetHospEmerg
			if @hasStac = 1
			SET @S3= isnull
			((
				select
					RIGHT('0000'+CAST(ISNULL(lp1.Lpu_f003mcod, '') AS VARCHAR),4) as MCOD_STC, -- МО, куда направлен пациент	(реестровый номер, F003)
					cast(eps.EvnPS_setDate as date) as DTA_FKT, --Дата фактической госпитализации
					case when len(DATENAME(hour, eps.EvnPS_setTime)) = 1 then '0'+DATENAME(hour, eps.EvnPS_setTime) else DATENAME(hour, eps.EvnPS_setTime) end+'-'
						+case when len(DATENAME(minute, eps.EvnPS_setTime)) = 1 then '0'+DATENAME(minute, eps.EvnPS_setTime) else DATENAME(minute, eps.EvnPS_setTime) end
					as TIM_FKT, --Время фактической госпитализации
					pt.PolisType_CodeF008 as VPOLIS,
					ISNULL(case when pt.PolisType_CodeF008 in (1, 2) then po.Polis_Ser else null end, '') as SPOLIS, -- Серия документа
					CASE 
						WHEN pt.PolisType_CodeF008 = 3 THEN pe.Person_EdNum 
						WHEN pt.PolisType_CodeF008 in (1, 2) THEN po.Polis_Num 
						ELSE ''
					END as NPOLIS, -- Numeric(20) - Номер документа
					org.Org_OGRN AS SMO_OGRN, -- T(15) ОГРН СМО 
					org.Org_OKATO as ST_OKATO, --Т(5) ОКАТО территории страхования
					ISNULL(pe.Person_SurName, '') as FAM, --varchar(30) – Фамилия
					ISNULL(pe.Person_FirName, '') as IM, --varchar(30) – Имя
					ISNULL(pe.Person_SecName, '') as OT, --varchar(30) – Отчество
					case when pe.Sex_id=3 then 1 else pe.Sex_id end as W, --numeric(1) – пол (1 - муж, 2 - жен)
					CAST(pe.Person_BirthDay as date) as DR, --date – дата рождения
					EvnSec.LpuSectionBedProfile_Code as KOD_PFK, --numeric(10, 0) - Профиль койки
					EvnSec.LpuSectionProfile_Code as KOD_PFO, --numeric(3, 0) - Код отделения (профиль)
					ISNULL(eps.EvnPS_NumCard, '') as NHISTORY, --Номер карты стационарного больного
					COALESCE(DiagPID.Diag_Code, EvnSec.Diag_Code) AS DS --Диагноз приемного отделения
				from dbo.v_EvnPS eps (nolock)
					inner join dbo.v_EvnDirection ed (nolock) on ed.EvnDirection_id = eps.EvnDirection_id
					left join dbo.v_PrehospType prt (nolock) on eps.PrehospType_id=prt.PrehospType_id
					INNER JOIN v_lpu lp (nolock) on lp.Lpu_id = ed.Lpu_id
					INNER JOIN v_lpu lp1 (nolock) on lp1.Lpu_id = ed.Lpu_did
					INNER JOIN dbo.v_Person_all pe (nolock) on pe.PersonEvn_id = ed.PersonEvn_id and pe.Server_id = ed.Server_id
					LEFT JOIN dbo.v_Polis po (nolock) on po.Polis_id = pe.Polis_id
					LEFT JOIN dbo.PolisType pt (nolock) on pt.PolisType_id = po.PolisType_id and isnull(pt.PolisType_CodeF008, 0) <> 0
					left join dbo.v_OrgSMO smo (nolock) on smo.OrgSMO_id = po.OrgSmo_id
					left join dbo.v_Org org (nolock) on org.Org_id = smo.Org_id
					LEFT JOIN v_Diag DiagPID with(nolock) on DiagPID.Diag_id = eps.Diag_pid
					outer apply(
						select top 1 
							es.EvnSection_id,
							lses.LpuSection_Code, 
							ISNULL(lses.LpuSectionProfile_Code, '') AS LpuSectionProfile_Code, 
							ISNULL(d.Diag_Code, '') AS Diag_Code, 
							ISNULL(LSBP.LpuSectionBedProfile_Code, 0) AS LpuSectionBedProfile_Code
						from v_EvnSection es with(nolock)
							inner join v_Lpusection lses with(nolock) on lses.Lpusection_id = es.lpusection_id
							LEFT JOIN fed.v_LpuSectionBedProfileLink LSBPL with(nolock) ON LSBPL.LpuSectionBedProfileLink_id = es.LpuSectionBedProfile_id
							LEFT JOIN fed.v_LpuSectionBedProfile LSBP with(nolock) on LSBP.LpuSectionBedProfile_id = LSBPL.LpuSectionBedProfile_fedid
							LEFT JOIN v_Diag d with(nolock) on d.Diag_id = es.Diag_id
						where es.Evnsection_pid = eps.EvnPS_id and isnull(EvnSection_isPriem,1)!=2
						order by EvnSection_setDate desc
					)EvnSec
				where (1=1)
					AND (@Lpu_id is null or eps.Lpu_id=@Lpu_id)
					AND eps.EvnPS_setDT>=@startTime and eps.EvnPS_setDT<=@finalTime
					AND eps.PrehospWaifRefuseCause_id is null 
					AND prt.PrehospType_Code in (2,3) --тип госпитализации «2. Экстренно» или «3. Экстренно по хирургическим показаниям»
					AND eps.PayType_id = @PayType_id
					AND EvnSec.EvnSection_id IS NOT null --создано движение в профильном отделении.
					for xml path('ZAP')
			), '')

			--GetCancelReferToHosp
			if @hasPolka = 1 or @hasStac = 1
			SET @S4= isnull((
				select
					t.NOM_NAP,
					t.DTA_NAP,
					t.IST_ANL,
					t.ACOD,
					t.PR_ANL
					from (
						SELECT
							ISNULL(cast(RIGHT(lp.Lpu_f003mcod, 4) as varchar)
								+ RIGHT('000000'+CAST(ed.EvnDirection_Num AS VARCHAR),6), '') as NOM_NAP,
							cast(ed.EvnDirection_setDT as date) as DTA_NAP,
							ISNULL(CASE
								WHEN ed.Lpu_id =  ed.Lpu_cid THEN lUnit.sourceType
								WHEN puc.Lpu_id = ed.Lpu_cid then '2'
								when puc.Lpu_id = ed.Lpu_id then '3'
							end, '') as IST_ANL, -- Источник аннулирования
							RIGHT('0000'+LpuCID.Lpu_f003mcod, 4) AS ACOD,-- numeric(6, 0) МО, отменившая направление (реестровый номер, F003)
							case
								when ECS.EvnStatusCause_id = 18 then 1 --Неявка пациента
								when ECS.EvnStatusCause_id = 22 then 2 --Непредоставление необходимого пакета документов
								when ECS.EvnStatusCause_id = 1 then 3 --Отказ пациента
								when ECS.EvnStatusCause_id = 5 then 4 --Смерть пациента
								else 5
							end	as	PR_ANL-- int  Причина отмены направления
						from dbo.v_EvnDirection ed (nolock)
							LEFT JOIN v_lpu LpuCID WITH(NOLOCK) ON LpuCID.Lpu_id = ed.Lpu_cid
							inner join v_lpu lp (nolock) on lp.Lpu_id = ed.Lpu_id
							inner join dbo.pmUserCache puc (nolock) on ed.pmUser_failID = puc.PMUser_id
							inner join dbo.v_Person_all pe (nolock) on pe.PersonEvn_id = ed.PersonEvn_id and pe.Server_id = ed.Server_id
							inner join dbo.v_Polis po (nolock) on po.Polis_id = pe.Polis_id
							inner join dbo.PolisType pt (nolock) on pt.PolisType_id = po.PolisType_id and pt.PolisType_CodeF008 is not null -- без полисов ДМС
							outer apply (
								select 
									LUT.LpuUnitType_SysNick as groupType,
									CASE WHEN LUT.LpuUnitType_SysNick = 'stac' THEN 2
										ELSE 3
									END AS sourceType
								from 
									v_MedStaffFact MSF
									left join v_LpuSection  LS (nolock) on LS.LpuSection_id = MSF.LpuSection_id
									left join LpuUnit LU (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
									left join LpuUnitType LUT (nolock) on LUT.LpuUnitType_id = LU.LpuUnitType_id
								where MSF.MedStaffFact_id = ed.MedStaffFact_fid
							) lUnit
							outer apply (
								select top 1
									EvnStatusCause_id
								from
									v_EvnStatusHistory
								where
									Evn_id = ed.EvnDirection_id
								order by
									EvnStatusHistory_insDT desc
							) ecs
						where(1=1)
							and ed.DirType_id in (1,5)
							and ed.EvnStatus_id in (12, 13)
							and (@Lpu_id is null or ed.Lpu_id=@Lpu_id)
							and ed.EvnDirection_failDT>=@startTime and ed.EvnDirection_failDT<=@finalTime
							and ISNULL(ed.PayType_id, @PayType_id) = @PayType_id
					UNION
						SELECT
							ISNULL(cast(RIGHT(lp.Lpu_f003mcod, 4) as varchar)
								+ RIGHT('000000'+CAST(ed.EvnDirection_Num AS VARCHAR),6), '') as NOM_NAP,
							cast(ed.EvnDirection_setDT as date) as DTA_NAP,
							2 as IST_ANL, --Источник аннулирования
							RIGHT('0000'+LpuCID.Lpu_f003mcod, 4) AS ACOD,-- numeric(6, 0) МО, отменившая направление (реестровый номер, F003)
							case
								when ECS.EvnStatusCause_id = 18 then 1 --Неявка пациента
								when ECS.EvnStatusCause_id = 22 then 2 --Непредоставление необходимого пакета документов
								when ECS.EvnStatusCause_id = 1 then 3 --Отказ пациента
								when ECS.EvnStatusCause_id = 5 then 4 --Смерть пациента
								else 5
							end	as	PR_ANL-- int  Причина отмены направления
						from dbo.v_EvnPS eps (nolock)
							inner join dbo.v_EvnDirection ed (nolock) on ed.EvnDirection_id = eps.EvnDirection_id
							inner join v_LpuSection ls (nolock) on ls.LpuSection_id = eps.LpuSection_pid
							inner join v_Lpu lp (nolock) on lp.Lpu_id = ls.Lpu_id
							inner join dbo.v_Person_all pe (nolock) on pe.PersonEvn_id = ed.PersonEvn_id and pe.Server_id = ed.Server_id
							inner join dbo.v_Polis po (nolock) on po.Polis_id = pe.Polis_id
							inner join dbo.PolisType pt (nolock) on pt.PolisType_id = po.PolisType_id and pt.PolisType_CodeF008 is not null -- без полисов ДМС
							LEFT JOIN v_lpu LpuCID WITH(NOLOCK) ON LpuCID.Lpu_id = ed.Lpu_cid
							outer apply (
								select 
									LUT.LpuUnitType_SysNick as groupType,
									CASE WHEN LUT.LpuUnitType_SysNick = 'stac' THEN 2
										ELSE 3
									END AS sourceType
								from 
									v_MedStaffFact MSF
									left join v_LpuSection  LS (nolock) on LS.LpuSection_id = MSF.LpuSection_id
									left join LpuUnit LU (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
									left join LpuUnitType LUT (nolock) on LUT.LpuUnitType_id = LU.LpuUnitType_id
								where MSF.MedStaffFact_id = ed.MedStaffFact_fid
							) lUnit
							outer apply (
								select top 1
									EvnStatusCause_id
								from
									v_EvnStatusHistory
								where
									Evn_id = ed.EvnDirection_id
								order by
									EvnStatusHistory_insDT desc
							) ecs
						where(1=1)
							and ed.DirType_id in (1,5)
							and ed.EvnStatus_id in (12, 13)
							and (@Lpu_id is null or ed.Lpu_id=@Lpu_id)
							and eps.EvnPS_OutcomeDT>=@startTime and eps.EvnPS_OutcomeDT<=@finalTime
							and eps.PayType_id = @PayType_id
					) as t
			for xml path('ZAP')
			), '')

			--GetExitHosp
			if @hasStac = 1
			SET @S5= isnull((
				select
					ISNULL(cast(RIGHT(lp1.Lpu_f003mcod, 4) as varchar)
						+ RIGHT('000000'+CAST(ed.EvnDirection_Num AS VARCHAR),6), '') as NOM_NAP,
					cast(ISNULL(ed.EvnDirection_setDT, eps.EvnDirection_setDT) as date) as DTA_NAP, --Дата направления	= дата создания направления
					CASE
						WHEN prt.PrehospType_Code = 1 THEN 3 --Тип госпитализации «1. Планово»
						WHEN dt.DirType_Code = 5 THEN 2
						WHEN  prt.PrehospType_Code IN (2,3) THEN 1
					END as FRM_MP, -- Int - Форма оказания медицинской помощи
					RIGHT(lp.Lpu_f003mcod, 4) as MCOD_STC,
					cast(es.EvnSection_setDate as date) AS DTA_FKT, --Дата госпитализации
					cast (es.EvnSection_disDT as date) AS DTA_END, -- datetime - Дата выбытия
					pe.Person_SurName as FAM, --varchar(30) – Фамилия
					pe.Person_FirName as IM, --varchar(30) – Имя
					pe.Person_SecName as OT, --varchar(30) – Отчество
					case when pe.Sex_id=3 then 1 else pe.Sex_id end as W, --numeric(1) – пол (1 - муж, 2 - жен)
					cast(pe.Person_BirthDay as date) as DR, --date – дата рождения
					ISNULL(lsb.LpuSectionBedProfile_Code, '0') as KOD_PFK, --numeric(4, 0) - Профиль койки
					ISNULL(lspf.LpuSectionProfile_Code, '') as KOD_PFO, --numeric(4, 0) - Код отделения (профиль)
					ISNULL(eps.EvnPS_NumCard, '') as NHISTORY --Номер карты стационарного больного
				from dbo.v_EvnSection es (nolock)
					inner join dbo.v_EvnPS eps (nolock) on eps.EvnPS_id = es.EvnSection_pid
					inner join dbo.v_EvnLeaveBase elb with (nolock) on elb.EvnLeaveBase_pid = es.EvnSection_id
					left join dbo.v_EvnDirection ed (nolock) on ed.EvnDirection_id = eps.EvnDirection_id
					left join dbo.v_DirType dt (nolock) on dt.DirType_id = ed.DirType_id
					left join dbo.v_PrehospType prt (nolock) on eps.PrehospType_id=prt.PrehospType_id
					left join dbo.v_PrehospDirect pd (nolock) on eps.PrehospDirect_id=pd.PrehospDirect_id
					inner join v_lpu lp (nolock) on lp.Lpu_id = es.Lpu_id
					inner join dbo.v_LpuSection ls (nolock) on ls.LpuSection_id = es.LpuSection_id
					inner join dbo.v_LpuUnit lu (nolock) on ls.LpuUnit_id=lu.LpuUnit_id
					inner join dbo.v_LpuBuilding lb (nolock) on lb.LpuBuilding_id = lu.LpuBuilding_id
					left join dbo.v_LpuSectionBedProfile lsb (nolock) on lsb.LpuSectionBedProfile_id = ls.LpuSectionBedProfile_id
						left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
						left join fed.v_LpuSectionProfile lspf (nolock) on lspf.LpuSectionProfile_id = lsp.LpuSectionProfile_fedid
					inner join v_lpu_all lp1 (nolock) on lp1.Org_id = eps.Org_did or lp1.Lpu_id = case
						when ed.EvnDirection_id is not null then ed.Lpu_id
						when pd.PrehospDirect_SysNick like 'lpusection' then eps.Lpu_id
						when pd.PrehospDirect_SysNick like 'lpu' then eps.Lpu_did
					end
					inner join dbo.v_Person_all pe (nolock) on pe.PersonEvn_id = elb.PersonEvn_id and pe.Server_id = elb.Server_id
					left join dbo.v_Polis po (nolock) on po.Polis_id = pe.Polis_id
					left join dbo.PolisType pt (nolock) on pt.PolisType_id = po.PolisType_id
					left join dbo.v_OrgSMO smo (nolock) on smo.OrgSMO_id = po.OrgSmo_id
					inner join dbo.v_LeaveType lt (nolock) on lt.LeaveType_id = es.LeaveType_id
				where (1=1)
					and (@Lpu_id is null or es.Lpu_id=@Lpu_id)
					and es.EvnSection_disDT>=@startTime and es.EvnSection_disDT<=@finalTime
					and lt.LeaveType_Code <> 5
					and es.PayType_id = @PayType_id
			for xml path('ZAP')
			), '')

			--GetCouchInfo
			if @hasStac = 1
			SET @S6= isnull((
			select
			t.*
			from rpt35.Han_hosp_enable(@Lpu_id,null,@date) t
			for xml path('ZAP')
			), '')

			SELECT
			@S1 as T1,	--GetReferToHosp
			@S2 as T2,	--GetHospPlan
			@S3 as T3,	--GetHospEmerg,
			@S4 as T4,	--GetCancelReferToHosp,
			@S5 as T5,	--GetExitHosp,
			@S6 as T6	--GetCouchInfo
		";
		//echo getDebugSQL($query, $data); die();
		$result = $this->getFirstRowFromQuery($query, $data);
		return $result;
	}
	
		
	/**
	 * @param $data
	 * @return bool
	 */
	public function getEvnPSFields($data = []) {
		$query = "
			select top 1
				 ISNULL(EPS.EvnPS_NumCard, '') as EvnPS_NumCard
				,RTRIM(ISNULL(Lpu.Lpu_Name, '')) as Lpu_Name
				,RTRIM(ISNULL(PLST.PolisType_Name, '')) as PolisType_Name
				,CASE WHEN PLST.PolisType_Code = 4 then '' ELSE RTRIM(ISNULL(PLS.Polis_Ser, '')) END as Polis_Ser
				,CASE WHEN PLST.PolisType_Code = 4 then isnull(RTRIM(PS.Person_EdNum), '') ELSE RTRIM(ISNULL(PLS.Polis_Num, '')) END AS Polis_Num
				--,RTRIM(ISNULL(PLS.Polis_Num, '')) as Polis_Num
				--,RTRIM(ISNULL(PLS.Polis_Ser, '')) as Polis_Ser
				,ISNULL('код терр. ' + cast(OST.OMSSprTerr_Code as varchar(5)), '') as OMSSprTerr_Code
				,ISNULL('выдан ' + OrgSmo.OrgSMO_Nick, '') as OrgSmo_Name
				,ISNULL(OS.Org_OKATO, '') as OrgSmo_OKATO
				,PS.Person_id
				,RTRIM(RTRIM(ISNULL(PS.Person_Surname, '')) + ' ' + RTRIM(ISNULL(PS.Person_Firname, '')) + ' ' + RTRIM(ISNULL(PS.Person_Secname, ''))) as Person_Fio
				,LEFT(ISNULL(SX.Sex_Name, ''), 3) as Sex_Name
				,convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday
				,dbo.Age2(PS.Person_Birthday, EPS.EvnPS_setDT) as Person_AgeYears
				,RTRIM(ISNULL(D.Document_Num, '')) as Document_Num
				,RTRIM(ISNULL(D.Document_Ser, '')) as Document_Ser
				,RTRIM(ISNULL(DT.DocumentType_Name, '')) as DocumentType_Name
				,RTRIM(ISNULL(KLAT.KLAreaType_Name, '')) as KLAreaType_Name
				,RTRIM(ISNULL(KLAT.KLAreaType_id, '')) as KLAreaType_id
				,RTRIM(ISNULL(PS.Person_Phone, '')) as Person_Phone
				,RTRIM(ISNULL(PAddr.Address_Address, '')) as PAddress_Name
				,RTRIM(ISNULL(UAddr.Address_Address, '')) as UAddress_Name
				,ISNULL(MSF.MedPersonal_TabCode, '') as MedPersonalPriem_Code
				,ISNULL(MSF.Person_Fio, '') as MedPersonalPriem_FIO
				,RTRIM(ISNULL(PT.PayType_Name, '')) as PayType_Name
				,RTRIM(ISNULL(PT.PayType_Code, '')) as PayType_Code
				,RTRIM(ISNULL(SS.SocStatus_Name, '')) as SocStatus_Name
				,RTRIM(ISNULL(SS.SocStatus_Code, '')) as SocStatus_Code
				,RTRIM(ISNULL(SS.SocStatus_SysNick, '')) as SocStatus_SysNick
				,IT.PrivilegeType_id
				,ISNULL(IT2.PrivilegeType_Code, '') as PrivilegeType_Code
				,CASE
					WHEN street.KLStreet_id is not null and street.KLAdr_Ocatd is not null THEN street.KLAdr_Ocatd
					WHEN town.KLArea_id is not null and town.KLAdr_Ocatd is not null THEN town.KLAdr_Ocatd
					WHEN city.KLArea_id is not null and city.KLAdr_Ocatd is not null THEN city.KLAdr_Ocatd
					WHEN srgn.KLArea_id is not null and srgn.KLAdr_Ocatd is not null THEN srgn.KLAdr_Ocatd
					WHEN rgn.KLArea_id is not null and rgn.KLAdr_Ocatd is not null THEN rgn.KLAdr_Ocatd
					WHEN country.KLArea_id is not null and country.KLAdr_Ocatd is not null THEN country.KLAdr_Ocatd
					ELSE ''
				END as Person_OKATO
				,RTRIM(COALESCE(PHLS.LpuSection_Name, PreHospLpu.Lpu_Name, PHOM.OrgMilitary_Name, PHO.Org_Name,PD.PrehospDirect_Name, '')) as PrehospOrg_Name
				,RTRIM(ISNULL(PA.PrehospArrive_Name, '')) as PrehospArrive_Name
				,ISNULL(DiagH.Diag_Code, '') as PrehospDiag_Code
				,ISNULL(DiagH.Diag_Name, '') as PrehospDiag_Name
				,ISNULL(DiagP.Diag_Code, '') as AdmitDiag_Code
				,RTRIM(ISNULL(PHTX.PrehospToxic_Name, '')) as PrehospToxic_Name
				,RTRIM(ISNULL(PHTX.PrehospToxic_Code, '')) as PrehospToxic_Code
				,RTRIM(ISNULL(LSTT.LpuSectionTransType_Name, '')) as LpuSectionTransType_Name
				,RTRIM(ISNULL(LSTT.LpuSectionTransType_Code, '')) as LpuSectionTransType_Code
				,case
					when PHT.PrehospType_Code is null then null
					when PHT.PrehospType_Code = 1 then 4
					else 3
				 end as PrehospType_Code
				,case
					when PHT.PrehospType_Code is null then ''
					when PHT.PrehospType_Code = 1 then 'в плановом порядке'
					else 'по экстренным показаниям'
				 end as PrehospType_Name
				,case when ISNULL(EPS.EvnPS_HospCount, 1) = 1 then 1 else 2 end as EvnPS_HospCountCode
				,case when ISNULL(EPS.EvnPS_HospCount, 1) = 1 then 'первично' else 'повторно' end as EvnPS_HospCountName
				,EPS.EvnPS_TimeDesease
				,ISNULL(ED.EvnDirection_Num, EPS.EvnDirection_Num) as EvnDirection_Num 
				,convert(varchar(10), ISNULL(ED.EvnDirection_setDT, EPS.EvnDirection_setDT), 104) as EvnDirection_setDate 
				,EPS.EvnPS_CodeConv
				,EPS.EvnPS_NumConv
				,convert(varchar(10), EPS.EvnDirection_SetDT, 104) as EvnDirection_SetDT
				,RTRIM(PC.PersonCard_Code) as PersonCard_Code
				,RTRIM(ISNULL(PHTR.PrehospTrauma_Name, '')) as PrehospTrauma_Name
				,RTRIM(ISNULL(PHTR.PrehospTrauma_Code, '')) as PrehospTrauma_Code
				,convert(varchar(10), EPS.EvnPS_setDate, 104) as EvnPS_setDate
				,EPS.EvnPS_setTime
				,ISNULL(LSFirst.LpuSection_Code, '') as LpuSectionFirst_Code
				,ISNULL(LSFirst.LpuSection_Name, '') as LpuSectionFirst_Name
				,RTRIM(ISNULL(LSBPFirst.LpuSectionBedProfile_Name, '')) as LpuSectionBedProfile_Name
				,RTRIM(ISNULL(MPFirst.MedPersonal_TabCode, '')) as MedPersonal_TabCode
				,RTRIM(ISNULL(MPFirst.MedPersonal_Code, '')) as MPFirst_Code
				,RTRIM(ISNULL(MPFirst.Person_Fio, '')) as MedPerson_FIO
				,RTRIM(ISNULL(OHMP.Person_Fio,'')) as OrgHead_FIO
				,RTRIM(ISNULL(OHMP.MedPersonal_TabCode,'')) as OrgHead_Code
				,convert(varchar(10), ESFirst.EvnSection_setDT, 104) as EvnSectionFirst_setDate
				,ESFirst.EvnSection_setTime as EvnSectionFirst_setTime
				,convert(varchar(10), EPS.EvnPS_disDate, 104) as EvnPS_disDate
				,EPS.EvnPS_disTime
				,LUTLast.LpuUnitType_SysNick
				,case when LUTLast.LpuUnitType_SysNick = 'stac'
					then datediff(day, EPS.EvnPS_setDate, EPS.EvnPS_disDate) + abs(sign(datediff(day, EPS.EvnPS_setDate, EPS.EvnPS_disDate)) - 1) -- круглосуточные
				  	else (datediff(day, EPS.EvnPS_setDate, EPS.EvnPS_disDate) + 1) -- дневные
				 end as EvnPS_KoikoDni
				,RTRIM(ISNULL(LT.LeaveType_Name, '')) as LeaveType_Name
				,LT.LeaveType_Code
				,LT.LeaveType_SysNick
				,RTRIM(ISNULL(RD.ResultDesease_Name, '')) as ResultDesease_Name
				,RD.ResultDesease_Code
				,RD.ResultDesease_Code as ResultDesease_sCode
				,case
					when LT.LeaveType_SysNick = 'die' then 6
					when RD.ResultDesease_SysNick in ('kszdor','dszdor') then 1
					when RD.ResultDesease_SysNick in ('dsuluc','ksuluc') then 2
					when RD.ResultDesease_SysNick in ('dsbper','ksbper','noteff') then 3
					when RD.ResultDesease_SysNick in ('dsuchud','ksuchud') then 4
					when RD.ResultDesease_SysNick in ('dszdor','kszdor') then 5
					else null
				end as ResultDesease_aCode
				,convert(varchar(10), EST.EvnStick_setDT, 104) as EvnStick_setDate
				,convert(varchar(10), EST.EvnStick_disDT, 104) as EvnStick_disDate
				,ESTCP.Person_Age as PersonCare_Age
				,ESTCP.Sex_Name as PersonCare_SexName
				,ESTCP.Sex_id as PersonCare_SexId
				,DG.Diag_Code as LeaveDiag_Code
				,DG.Diag_Name as LeaveDiag_Name
				,DGA.Diag_Code as LeaveDiagAgg_Code
				,DGA.Diag_Name as LeaveDiagAgg_Name
				,DGS.Diag_Code as LeaveDiagSop_Code
				,DGS.Diag_Name as LeaveDiagSop_Name
				,PAD.Diag_Code as AnatomDiag_Code
				,PAD.Diag_Name as AnatomDiag_Name
				,PADA.Diag_Code as AnatomDiagAgg_Code
				,PADA.Diag_Name as AnatomDiagAgg_Name
				,PADS.Diag_Code as AnatomDiagSop_Code
				,PADS.Diag_Name as AnatomDiagSop_Name
				,case when EPS.EvnPS_IsImperHosp = 2 then '1; ' else null end as EvnPS_IsImperHosp
				,case when EPS.EvnPS_IsShortVolume = 2 then '2; ' else null end as EvnPS_IsShortVolume
				,case when EPS.EvnPS_IsWrongCure = 2 then '3; ' else null end as EvnPS_IsWrongCure
				,case when EPS.EvnPS_IsDiagMismatch = 2 then '4; ' else null end as EvnPS_IsDiagMismatch
				,LC.LeaveCause_Code
				,EPS.PrehospWaifRefuseCause_id
				,IsRW.YesNo_Name as IsRW
				,IsAIDS.YesNo_Name as IsAIDS
				,PEH.PersonEncrypHIV_Encryp
			from dbo.v_EvnPS as EPS with (nolock)
				inner join dbo.v_Lpu as Lpu on Lpu.Lpu_id = EPS.Lpu_id
				inner join dbo.v_PersonState as PS on PS.Person_id = EPS.Person_id
				left join dbo.v_EvnDirection as ED on ED.EvnDirection_id = EPS.EvnDirection_id
				left join dbo.v_EvnSection as ESLast on ESLast.EvnSection_pid = EPS.EvnPS_id
					and ESLast.EvnSection_Index = ESLast.EvnSection_Count - 1
				left join dbo.v_EvnSection as ESFirst on ESFirst.EvnSection_pid = EPS.EvnPS_id
					and ESFirst.EvnSection_Index = 1
				left join dbo.v_LpuSection as LSLast on LSLast.LpuSection_id = ESLast.LpuSection_id
				left join dbo.LpuUnit as LULast on LULast.LpuUnit_id = LSLast.LpuUnit_id
				left join dbo.LpuUnitType as LUTLast on LUTLast.LpuUnitType_id = LULast.LpuUnitType_id
				left join dbo.v_PrehospDirect as PD on EPS.PrehospDirect_id = PD.PrehospDirect_id
				left join dbo.v_EvnLeave as ELeave on ELeave.EvnLeave_pid = ESLast.EvnSection_id
				left join dbo.LeaveCause as LC on LC.LeaveCause_id = ELeave.LeaveCause_id
				left join dbo.v_Polis as PLS on PLS.Polis_id = PS.Polis_id
				left join dbo.v_OmsSprTerr as OST on OST.OmsSprTerr_id = PLS.OmsSprTerr_id
				left join dbo.v_PolisType as PLST on PLST.PolisType_id = PLS.PolisType_id
				left join dbo.v_OrgSmo as OrgSmo on OrgSmo.OrgSmo_id = PLS.OrgSmo_id
				left join dbo.v_Org as OS on OS.Org_id = OrgSmo.Org_id
				left join dbo.v_Address as UAddr on UAddr.Address_id = PS.UAddress_id
				left join dbo.KLArea as country on country.KLArea_id = UAddr.KLCountry_id
				left join dbo.KLArea as rgn on rgn.KLArea_id = UAddr.KLRgn_id
				left join dbo.KLArea as srgn on srgn.KLArea_id = UAddr.KLSubRgn_id
				left join dbo.KLArea as city on city.KLArea_id = UAddr.KLCity_id
				left join dbo.KLArea as town on town.KLArea_id = UAddr.KLSubRgn_id
				left join dbo.KLStreet as street on street.KLStreet_id = UAddr.KLStreet_id
				left join dbo.v_Address as PAddr on PAddr.Address_id = PS.PAddress_id
				left join dbo.v_KLAreaType as KLAT on KLAT.KLAreaType_id = PAddr.KLAreaType_id
				left join dbo.v_Document as D on D.Document_id = PS.Document_id
				left join dbo.v_DocumentType as DT on DT.DocumentType_id = D.DocumentType_id
				left join dbo.v_Sex as SX on SX.Sex_id = PS.Sex_id
				left join dbo.v_PayType as PT on PT.PayType_id = EPS.PayType_id
				--left join dbo.v_SocStatus as SS on SS.SocStatus_id = PS.SocStatus_id
				outer apply (
					select top 1
						PrivilegeType_id,
						PrivilegeType_Code,
						PrivilegeType_Name
					from dbo.v_PersonPrivilege WITH (NOLOCK)
					where PrivilegeType_Code in ('81', '82', '83')
						and Person_id = PS.Person_id
					order by PersonPrivilege_begDate desc
				) as IT
				outer apply (
					select top 1
						PrivilegeType_id,
						PrivilegeType_Code,
						PrivilegeType_Name
					from dbo.v_PersonPrivilege WITH (NOLOCK)
					where PrivilegeType_Code in ('11', '20', '91', '81', '82', '83', '84')
						and Person_id = PS.Person_id
					order by PersonPrivilege_begDate desc
				) as IT2
				left join dbo.v_PersonCard as PC on PC.Person_id = PS.Person_id
					and PC.PersonCard_begDate is not null
					and PC.PersonCard_begDate <= EPS.EvnPS_insDT
					and (PC.PersonCard_endDate is null or PC.PersonCard_endDate > EPS.EvnPS_insDT)
					and PC.Lpu_id = EPS.Lpu_id
				left join dbo.v_LpuSection as PHLS on PHLS.LpuSection_id = EPS.LpuSection_did
				left join dbo.v_OrgHead as OH on OH.LpuUnit_id = PHLS.LpuUnit_id and OH.OrgHeadPost_id = 13
				left join dbo.v_MedPersonal as OHMP on OHMP.Person_id = OH.Person_id
				left join dbo.v_Lpu as PreHospLpu on PreHospLpu.Lpu_id = EPS.Lpu_did
				left join dbo.v_MedStaffFact as MSF on MSF.MedStaffFact_id = EPS.MedStaffFact_pid
				left join dbo.v_OrgMilitary as PHOM on PHOM.OrgMilitary_id = EPS.OrgMilitary_did
				left join dbo.v_Org as PHO on PHO.Org_id = EPS.Org_did
				left join dbo.v_PrehospArrive as PA on PA.PrehospArrive_id = EPS.PrehospArrive_id
				left join dbo.v_Diag as DiagH on DiagH.Diag_id = EPS.Diag_did
				left join dbo.v_Diag as DiagP on DiagP.Diag_id = EPS.Diag_pid
				left join dbo.v_PrehospToxic as PHTX on PHTX.PrehospToxic_id = EPS.PrehospToxic_id
				left join dbo.v_LpuSectionTransType as LSTT on LSTT.LpuSectionTransType_id = EPS.LpuSectionTransType_id
				left join dbo.v_PrehospType as PHT on PHT.PrehospType_id = EPS.PrehospType_id
				left join dbo.v_PrehospTrauma as PHTR on PHTR.PrehospTrauma_id = EPS.PrehospTrauma_id
				left join dbo.v_MedPersonal as MPFirst on EPS.MedPersonal_pid = MPFirst.MedPersonal_id
				left join dbo.v_LpuSection as LSFirst on LSFirst.LpuSection_id = ESFirst.LpuSection_id
				left join dbo.v_LpuSectionBedProfile as LSBPFirst on LSBPFirst.LpuSectionBedProfile_id = LSFirst.LpuSectionBedProfile_id
				left join dbo.v_LeaveType as LT on LT.LeaveType_id = EPS.LeaveType_id
				left join dbo.v_EvnLeave as EL on EL.EvnLeave_pid = ESLast.EvnSection_id
				left join dbo.v_EvnDie as EDie on EDie.EvnDie_pid = ESLast.EvnSection_id
				left join dbo.v_EvnOtherLpu as EOL on EOL.EvnOtherLpu_pid = ESLast.EvnSection_id
				left join dbo.v_EvnOtherStac as EOST on EOST.EvnOtherStac_pid = ESLast.EvnSection_id
				left join dbo.v_ResultDesease as RD on RD.ResultDesease_id = COALESCE(EL.ResultDesease_id, EOL.ResultDesease_id, EOST.ResultDesease_id, EDie.ResultDesease_id)
				left join dbo.v_PersonEncrypHIV as PEH on PEH.Person_id = PS.Person_id
				outer apply (
					select top 1
						 EvnStick_id
						,EvnStick_setDT
						,EvnStick_disDT
					from
						dbo.v_EvnStick with (nolock)
					where
						EvnStick_pid = EPS.EvnPS_id
					order by
						EvnStick_setDT
				) as EST
				outer apply (
					select top 1
						 dbo.Age2(t2.Person_Birthday, EPS.EvnPS_setDT) as Person_Age
						,t3.Sex_Name
						,t3.Sex_id
					from
						dbo.v_EvnStickCarePerson as t1 with (nolock)
						left join dbo.v_PersonState as t2 on t2.Person_id = t1.Person_id
						left join dbo.v_Sex as t3 on t3.Sex_id = t2.Sex_id
					where
						t1.Evn_id = EST.EvnStick_id
				) as ESTCP
				left join dbo.v_Diag as DG on DG.Diag_id = ESLast.Diag_id and ISNULL(ESLast.LeaveType_id, 0) != 5
				left join dbo.v_Diag as PAD on PAD.Diag_id = EDie.Diag_aid
				outer apply (
					select top 1 Diag_id
					from dbo.v_EvnDiagPS with (nolock)
					where EvnDiagPS_pid = ESLast.EvnSection_id
						and DiagSetClass_id = 2
				) as TDGA
				left join dbo.v_Diag as DGA on DGA.Diag_id = TDGA.Diag_id and ISNULL(ESLast.LeaveType_id, 0) != 5
				outer apply (
					select top 1 Diag_id
					from dbo.v_EvnDiagPS with (nolock)
					where EvnDiagPS_pid = ESLast.EvnSection_id
						and DiagSetClass_id = 3
				) as TDGS
				left join dbo.v_Diag as DGS on DGS.Diag_id = TDGS.Diag_id and ISNULL(ESLast.LeaveType_id, 0) != 5
				outer apply (
					select top 1 Diag_id
					from dbo.v_EvnDiagPS with (nolock)
					where EvnDiagPS_pid = EDie.EvnDie_id
						and DiagSetClass_id = 2
				) as TPADA
				left join dbo.v_Diag as PADA on PADA.Diag_id = TPADA.Diag_id
				outer apply (
					select top 1 Diag_id
					from dbo.v_EvnDiagPS with (nolock)
					where EvnDiagPS_pid = EDie.EvnDie_id
						and DiagSetClass_id = 3
				) as TPADS
				left join dbo.v_Diag as PADS on PADS.Diag_id = TPADS.Diag_id
				left join dbo.v_LpuUnitType as oLUT on oLUT.LpuUnitType_id = EOST.LpuUnitType_oid
				outer apply (
					select top 1 t3.YesNo_Name
					from dbo.v_EvnUsluga as t1 with (nolock)
						inner join dbo.v_UslugaComplex as t2 on t2.UslugaComplex_id = t1.UslugaComplex_id
						inner join dbo.v_YesNo as t3 on t3.YesNo_Code = 1
					where t1.EvnUsluga_rid = EPS.EvnPS_id
						and t2.UslugaComplex_Code = 'A12.06.011'
						and t1.EvnUsluga_SetDT is not null
				) as IsRW
				outer apply (
					select top 1 t3.YesNo_Name
					from dbo.v_EvnUsluga t1 with (nolock)
						inner join dbo.v_UslugaComplex t2 on t2.UslugaComplex_id = t1.UslugaComplex_id
						inner join dbo.v_YesNo as t3 on t3.YesNo_Code = 1
					where
						t1.EvnUsluga_rid = EPS.EvnPS_id
						and t2.UslugaComplex_Code = 'A09.05.228'
						and t1.EvnUsluga_setDT is not null
				) as IsAIDS
				OUTER APPLY(
					SELECT TOP 1 vper.SocStatus_id
					FROM v_Person_all vper with (nolock)
					WHERE vper.Person_id = EPS.Person_id AND vper.PersonEvn_id = EPS.PersonEvn_id
					ORDER BY vper.PersonEvn_updDT DESC
				) PALL
				left join dbo.v_SocStatus as SS on SS.SocStatus_id = PALL.SocStatus_id
			where
				EPS.EvnPS_id = :EvnPS_id
		";

		if( !isTFOMSUser() && empty($data['session']['medpersonal_id']) ) {
			$query.=' and EPS.Lpu_id = :Lpu_id';
		}

		$response = $this->queryResult($query, [
			'EvnPS_id' => $data['EvnPS_id'],
			'Lpu_id' => $data['Lpu_id'],
		]);

		if ( !is_array($response) || count($response) == 0 ) {
			return false;
		}

		//Отдельно получим сопутствующие диагнозы и осложнения
		$response_temp = [];

		$response_temp[0] = [
			'LeaveDiagSop_Name' => '',
			'LeaveDiagSop_Code' => '',
			'LeaveDiagAgg_Name' => '',
			'LeaveDiagAgg_Code' => '',
		];

		$response_diag_sop = $this->queryResult("
			select
				DGS.Diag_Code as LeaveDiagSop_Code,
				DGS.Diag_Name as LeaveDiagSop_Name
			from
				v_EvnDiagPS as EDPS with (nolock)
				inner join v_EvnSection as ESLast on ESLast.EvnSection_id = EDPS.EvnDiagPS_pid
				inner join v_Diag as DGS on DGS.Diag_id = EDPS.Diag_id
			where
				EDPS.DiagSetClass_id = 3
				and EDPS.EvnDiagPS_rid = :EvnPS_id
				and EDPS.Lpu_id = :Lpu_id
				and ESLast.EvnSection_Index = ESLast.EvnSection_Count - 1
		", [
			'EvnPS_id' => $data['EvnPS_id'],
			'Lpu_id' => $data['Lpu_id'],
		]);

		if ( is_array($response_diag_sop) ) {
			foreach ( $response_diag_sop as $row ) {
				$response_temp[0]['LeaveDiagSop_Name'] .= $row['LeaveDiagSop_Name'];
				$response_temp[0]['LeaveDiagSop_Code'] .= $row['LeaveDiagSop_Code'];
			}
		}

		$response_diag_osl = $this->queryResult("
			select
				DGA.Diag_Code as LeaveDiagAgg_Code,
				DGA.Diag_Name as LeaveDiagAgg_Name
			from
				v_EvnDiagPS as EDPS with (nolock)
				inner join v_EvnSection as ESLast on ESLast.EvnSection_id = EDPS.EvnDiagPS_pid
				inner join v_Diag as DGA on DGA.Diag_id = EDPS.Diag_id
			where
				EDPS.DiagSetClass_id = 2
				and EDPS.EvnDiagPS_rid = :EvnPS_id
				and EDPS.Lpu_id = :Lpu_id
				and ESLast.EvnSection_Index = ESLast.EvnSection_Count - 1
		", [
			'EvnPS_id' => $data['EvnPS_id'],
			'Lpu_id' => $data['Lpu_id'],
		]);

		if ( is_array($response_diag_osl) ) {
			foreach ( $response_diag_osl as $row ) {
				$response_temp[0]['LeaveDiagAgg_Name'] .= $row['LeaveDiagAgg_Name'];
				$response_temp[0]['LeaveDiagAgg_Code'] .= $row['LeaveDiagAgg_Code'];
			}
		}

		$response[0]['LeaveDiagSop_Name'] = $response_temp[0]['LeaveDiagSop_Name'];
		$response[0]['LeaveDiagSop_Code'] = $response_temp[0]['LeaveDiagSop_Code'];
		$response[0]['LeaveDiagAgg_Name'] = $response_temp[0]['LeaveDiagAgg_Name'];
		$response[0]['LeaveDiagAgg_Code'] = $response_temp[0]['LeaveDiagAgg_Code'];

		return $response;
	}

	/**
	 * @param array $data
	 * @param array $response
	 * @return array|string
	 */
	protected function _printEvnPS($data = [], $response = []) {
		$template = 'evn_ps_template_list_a4_vologda';

		switch ( $response[0]['PayType_Code'] ) {
			case 1:
				$response[0]['PayType_Code'] = 1; // ОМС-1
				break;

			case 2:
				$response[0]['PayType_Code'] = 2; // ДМС-4
				break;

			case 3:
				$response[0]['PayType_Code'] = 3; // Бюджет-2
				break;

			case 4:
				$response[0]['PayType_Code'] = 4; // Бюджет-2
				break;
			
			case 4:
				$response[0]['PayType_Code'] = 5; // Платные услуги-3
				break;

			default:
				$response[0]['PayType_Code'] = 6; // другое
				break;
		}
		
		$evn_section_data = [];
		$evn_usluga_oper_data = [];

		$response_temp = $this->getEvnSectionData($data);

		if ( is_array($response_temp) ) {

			foreach($response_temp as $j => $value) {

				$query = "
					select  LS.LpuSection_Code,
							CONCAT(LS.LpuSection_Name, ' [Реанимация]') as LpuSection_Name,
							ERP.EvnReanimatPeriod_pid,
							convert(varchar(10), ERP.EvnReanimatPeriod_setDT, 104) + ' ' + convert(varchar(5), ERP.EvnReanimatPeriod_setDT, 108) as EvnSection_setDT,
							convert(varchar(10), ERP.EvnReanimatPeriod_disDT, 104) + ' ' + convert(varchar(5), ERP.EvnReanimatPeriod_disDT, 108) as EvnSection_disDT,
							ES.Diag_id,
							RTRIM(ISNULL(D.Diag_Code, '')) as EvnSectionDiagOsn_Code,
							RTRIM(ISNULL(D.Diag_Name, '')) as EvnSectionDiagOsn_Name,
							'Основной' as EvnSectionDiagSetClassOsn_Name,
							EPS.EvnPS_id as EvnReanimatPeriod_rid,
							MP.MedPersonal_TabCode as MedPersonal_Code,
							RTRIM(ISNULL(Mes.Mes_Code, '')) as EvnSectionMesOsn_Code,
							MP.MedPersonal_Code as MedPersonal_Code,
							RTRIM(ISNULL(PT.PayType_Name, '')) as EvnSectionPayType_Name,
							LSBP.LpuSectionBedProfile_Code,
							LSBP.LpuSectionBedProfile_Name
					  from  dbo.v_EvnReanimatPeriod ERP with(nolock)
							inner join v_EvnSection ES with(nolock) on ES.EvnSection_id = ERP.EvnReanimatPeriod_pid
							inner join v_EvnPS EPS with(nolock) on EPS.EvnPS_id = ES.EvnSection_pid
							left join v_LpuSection LS with(nolock) on LS.LpuSection_id = ERP.LpuSection_id
							left join v_MedService MS with(nolock) on MS.MedService_id = ERP.MedService_id
							left join v_MesOld Mes with (nolock) on Mes.Mes_id = ES.Mes_id
							inner join LpuSectionProfile LSP with (nolock) on LSP.LpuSectionProfile_id = LS.LpuSectionProfile_id
							left join fed.LpuSectionBedProfileLink LSBPLink with(nolock) on  LSBPLink.LpuSectionBedProfileLink_id = ES.LpuSectionBedProfileLink_fedid
							left join dbo.v_LpuSectionBedProfile LSBP with(nolock) on LSBP.LpuSectionBedProfile_id = LSBPLink.LpuSectionBedProfile_id
							cross apply (
							select top 1
								 MedPersonal_TabCode
								,MedPersonal_Code
								,Person_Fio
							from v_MedPersonal with (nolock)
							where MedPersonal_id = ES.MedPersonal_id
							) MP
							left join dbo.Diag D with(nolock) on D.Diag_id = isnull(ES.Diag_id, EPS.Diag_pid)
							inner join v_PayType PT with (nolock) on PT.PayType_id = ES.PayType_id
					  where ERP.EvnReanimatPeriod_pid = :EvnSection_id
				";

				$result = $this -> db -> query($query, array('EvnSection_id' => $value['EvnSection_id']));

				if (is_object($result)) {
					$erp_data = $result -> result('array');
					array_splice($response_temp, $j + 1, 0, $erp_data);
				}
				else{
					return false;
				}

			}

			$evn_section_data = $response_temp;

			for ( $i = 0; $i < (count($evn_section_data) < 6 ? 6 : count($evn_section_data)); $i++ ) {
				if ( $i >= count($evn_section_data) ) {
					$evn_section_data[$i] = [
						'Index' => $i + 1,
						'LpuSection_Code' => '&nbsp;',
						'EvnSection_setDT' => '&nbsp;',
						'EvnSection_disDT' => '&nbsp;',
						'EvnSectionDiagOsn_Code' => '&nbsp;',
						'EvnSectionMesOsn_Code' => '&nbsp;',
						'EvnSection_UKL' => '&nbsp;',
						'EvnSectionPayType_Name' => '&nbsp;',
						'LpuSectionBedProfile_Code' => '&nbsp;',
						'MedPersonal_Code' => '&nbsp;'
					];
				}
				else {
					$evn_section_data[$i]['Index'] = $i + 1;

					if ( !empty($evn_section_data[$i]['PayType_Name']) ) {
						$evn_section_data[$i]['EvnSectionPayType_Name'] = $evn_section_data[$i]['PayType_Name'];
					}
				}
			}
		}

		$response_temp = $this->getEvnUslugaOperData($data);

		if ( is_array($response_temp) ) {
			for ( $i = 0; $i < count($response_temp); $i++ ) {
				$evn_usluga_oper_data[] = [
					'EvnUslugaOper_setDT' => $response_temp[$i]['EvnUslugaOper_setDT'],
					'EvnUslugaOperMedPersonal_Code' => $response_temp[$i]['MedPersonal_Code'],
					'EvnUslugaOperLpuSection_Code' => $response_temp[$i]['LpuSection_Code'],
					'EvnUslugaOper_Name' => $response_temp[$i]['Usluga_Name'],
					'EvnUslugaOper_Code' => $response_temp[$i]['Usluga_Code'],
					'EvnUslugaOperAnesthesiaClass_Name' => $response_temp[$i]['AnesthesiaClass_Name'],
					'EvnUslugaOper_IsEndoskop' => $response_temp[$i]['EvnUslugaOper_IsEndoskop'] == 1 ? 'X' : '&nbsp;',
					'EvnUslugaOper_IsLazer' => $response_temp[$i]['EvnUslugaOper_IsLazer'] == 1 ? 'X' : '&nbsp;',
					'EvnUslugaOper_IsKriogen' => $response_temp[$i]['EvnUslugaOper_IsKriogen'] == 1 ? 'X' : '&nbsp;',
					'EvnUslugaOper_IsRadGraf' => $response_temp[$i]['EvnUslugaOper_IsRadGraf'] == 1 ? 'X' : '&nbsp;',
					'EvnUslugaOperPayType_Name' => $response_temp[$i]['PayType_Name']
				];
			}

			// https://redmine.swan.perm.ru/issues/6484
			// savage: Добавляем пустые строки в таблицу с хирургическими операциями, если количество операций меньше двух
			for ( $j = $i; $j < 3; $j++ ) {
				$evn_usluga_oper_data[] = [
					'EvnUslugaOper_setDT' => '&nbsp;<br />&nbsp;',
					'EvnUslugaOperMedPersonal_Code' => '&nbsp;<br />&nbsp;',
					'EvnUslugaOperLpuSection_Code' => '&nbsp;<br />&nbsp;',
					'EvnUslugaOper_Name' => '&nbsp;<br />&nbsp;',
					'EvnUslugaOper_Code' => '&nbsp;<br />&nbsp;',
					'EvnUslugaOperAnesthesiaClass_Name' => '&nbsp;<br />&nbsp;',
					'EvnUslugaOper_IsEndoskop' => '&nbsp;',
					'EvnUslugaOper_IsLazer' => '&nbsp;',
					'EvnUslugaOper_IsKriogen' => '&nbsp;',
					'EvnUslugaOper_IsRadGraf' => '&nbsp;',
					'EvnUslugaOperPayType_Name' => '&nbsp;<br />&nbsp;'
				];
			}
		}

		if ( !empty($response[0]['PrivilegeType_Code']) ) {
			switch ( $response[0]['PrivilegeType_Code'] ) {
				case 10:
					$response[0]['PrivilegeType_Code'] = 1; // инвалид ВОВ
					break;

				case 11:
				case 20:
					$response[0]['PrivilegeType_Code'] = 2; // участник ВОВ
					break;

				case 12:
				case 30:
				case 40:
					$response[0]['PrivilegeType_Code'] = 3; // воин-интернационалист
					break;

				case 111:
				case 112:
					$response[0]['PrivilegeType_Code'] = 4; // лицо, подвергш. радиационному облуч.
					break;

				case 91:
				case 92:
				case 93:
				case 94:
				case 98:
				case 101:
				case 102:
					$response[0]['PrivilegeType_Code'] = 5; // в т.ч. в Чернобыле
					break;

				case 83:
					$response[0]['PrivilegeType_Code'] = 6; // инв. Iгр
					break;

				case 82:
					$response[0]['PrivilegeType_Code'] = 7; // инв. IIгр
					break;

				case 81:
					$response[0]['PrivilegeType_Code'] = 8; // инв. IIIгр
					break;

				case 84:
				//case 101:
					$response[0]['PrivilegeType_Code'] = 9; // ребенок-инвалид
					break;

				case 84:
					$response[0]['PrivilegeType_Code'] = 10; // инвалид с детства
					break;

				default:
					$response[0]['PrivilegeType_Code'] = 11; // прочие
					break;
			}
		}

		$LeaveType_Code = '';

		if ( in_array($response[0]['LeaveType_SysNick'], [ 'ksleave', 'dsleave' ]) ) {
			$LeaveType_Code = 1; // выписан
		}
		else if ( in_array($response[0]['LeaveType_SysNick'], [ 'ksstac' ]) ) {
			$LeaveType_Code = 2; // в т.ч. в дневной стационар
		}
		else if ( in_array($response[0]['LeaveType_SysNick'], [ 'dsstac' ]) ) {
			$LeaveType_Code = 3; // в круглосуточный стационар
		}
		else if ( in_array($response[0]['LeaveType_SysNick'], [ 'ksother', 'ksper', 'dsother', 'dsper' ]) ) {
			$LeaveType_Code = 4; // переведен в другой стационар
		}

		$print_data = [
			'EvnPSTemplateTitle' => 'Печать карты выбывшего из стационара'
			,'EvnPS_NumCard' => returnValidHTMLString($response[0]['EvnPS_NumCard'])
			,'PolisType_Name' => returnValidHTMLString($response[0]['PolisType_Name'])
			,'Polis_Num' => returnValidHTMLString($response[0]['Polis_Num'])
			,'Polis_Ser' => returnValidHTMLString($response[0]['Polis_Ser'])
			,'OMSSprTerr_Code' => returnValidHTMLString($response[0]['OMSSprTerr_Code'])
			,'OrgSmo_Name' => returnValidHTMLString($response[0]['OrgSmo_Name'])
			,'Person_Fio' => returnValidHTMLString($response[0]['Person_Fio'])
			,'Person_AgeYears' => returnValidHTMLString($response[0]['Person_AgeYears'])
			,'Person_AgeMonths' => ''
			,'Person_AgeDays' => ''
			,'Person_OKATO' => returnValidHTMLString($response[0]['Person_OKATO'])
			,'Sex_Name' => returnValidHTMLString($response[0]['Sex_Name'])
			,'Person_Birthday' => returnValidHTMLString($response[0]['Person_Birthday'])
			,'DocumentType_Name' => returnValidHTMLString($response[0]['DocumentType_Name'])
			,'Document_Ser' => returnValidHTMLString($response[0]['Document_Ser'])
			,'Document_Num' => returnValidHTMLString($response[0]['Document_Num'])
			,'KLAreaType_Name' => returnValidHTMLString($response[0]['KLAreaType_Name'])
			,'KLAreaType_id' => returnValidHTMLString($response[0]['KLAreaType_id'])
			,'Person_Phone' => returnValidHTMLString($response[0]['Person_Phone'])
			,'MedPersonalPriem_Code' => returnValidHTMLString($response[0]['MedPersonalPriem_Code'])
			,'MedPersonalPriem_FIO' => returnValidHTMLString($response[0]['MedPersonalPriem_FIO'])
			,'PAddress_Name' => returnValidHTMLString($response[0]['PAddress_Name'])
			,'UAddress_Name' => returnValidHTMLString($response[0]['UAddress_Name'])
			,'PayType_Code' => returnValidHTMLString($response[0]['PayType_Code'])
			,'PayType_Name' => returnValidHTMLString($response[0]['PayType_Name'])
			,'SocStatus_Code' => returnValidHTMLString($response[0]['SocStatus_Code'])
			,'SocStatus_Name' => returnValidHTMLString($response[0]['SocStatus_Name'])
			,'PrehospOrg_Name' => returnValidHTMLString($response[0]['PrehospOrg_Name'])
			,'PrehospArrive_Name' => returnValidHTMLString($response[0]['PrehospArrive_Name'])
			,'PersonCard_Code' => returnValidHTMLString($response[0]['PersonCard_Code'])
			,'PrivilegeType_Code' => returnValidHTMLString($response[0]['PrivilegeType_Code'])
			,'Lpu_Name' => returnValidHTMLString($response[0]['Lpu_Name'])
			,'PrehospDiag_Code' => returnValidHTMLString($response[0]['PrehospDiag_Code'])
			,'PrehospDiag_Name' => returnValidHTMLString($response[0]['PrehospDiag_Name'])
			,'AdmitDiag_Code' => returnValidHTMLString($response[0]['AdmitDiag_Code'])
			,'PrehospToxic_Code' => returnValidHTMLString($response[0]['PrehospToxic_Code'])
			,'LpuSectionTransType_Code' => returnValidHTMLString($response[0]['LpuSectionTransType_Code'])
			,'PrehospType_Code' => returnValidHTMLString($response[0]['PrehospType_Code'])
			,'PrehospType_Name' => returnValidHTMLString($response[0]['PrehospType_Name'])
			,'EvnPS_HospCountCode' => returnValidHTMLString($response[0]['EvnPS_HospCountCode'])
			,'EvnPS_HospCountName' => returnValidHTMLString($response[0]['EvnPS_HospCountName'])
			,'EvnPS_TimeDesease' => (returnValidHTMLString($response[0]['EvnPS_TimeDesease']))==''?'0':((returnValidHTMLString($response[0]['EvnPS_TimeDesease']))<=6?'1':(returnValidHTMLString($response[0]['EvnPS_TimeDesease'])>24?'3':2))
			,'PrehospTrauma_Code' => returnValidHTMLString($response[0]['PrehospTrauma_Code'])
			,'EvnPS_setDate' => returnValidHTMLString($response[0]['EvnPS_setDate'])
			,'EvnPS_setTime' => returnValidHTMLString($response[0]['EvnPS_setTime'])
			,'LpuSectionFirst_Code' => returnValidHTMLString($response[0]['LpuSectionFirst_Code'])
			,'LpuSectionFirst_Name' => returnValidHTMLString($response[0]['LpuSectionFirst_Name'])
			,'EvnSectionFirst_setDate' => returnValidHTMLString($response[0]['EvnSectionFirst_setDate'])
			,'EvnSectionFirst_setTime' => returnValidHTMLString($response[0]['EvnSectionFirst_setTime'])
			,'EvnPS_disDate' => returnValidHTMLString($response[0]['EvnPS_disDate'])
			,'EvnPS_disTime' => returnValidHTMLString($response[0]['EvnPS_disTime'])
			,'EvnPS_KoikoDni' => returnValidHTMLString($response[0]['EvnPS_KoikoDni'])
			,'LeaveType_Code' => $LeaveType_Code
			,'ResultDesease_aCode' => returnValidHTMLString($response[0]['ResultDesease_aCode'])
			,'EvnStick_setDate' => returnValidHTMLString($response[0]['EvnStick_setDate'])
			,'EvnStick_disDate' => returnValidHTMLString($response[0]['EvnStick_disDate'])
			,'PersonCare_Age' => returnValidHTMLString($response[0]['PersonCare_Age'])
			,'PersonCare_SexName' => returnValidHTMLString($response[0]['PersonCare_SexName'])
			,'EvnSectionData' => $evn_section_data
			,'EvnUslugaOperData' => $evn_usluga_oper_data
			,'LeaveDiag_Code' => returnValidHTMLString($response[0]['LeaveDiag_Code'])
			,'LeaveDiag_Name' => returnValidHTMLString($response[0]['LeaveDiag_Name'])
			,'LeaveDiagAgg_Code' => returnValidHTMLString($response[0]['LeaveDiagAgg_Code'])
			,'LeaveDiagAgg_Name' => returnValidHTMLString($response[0]['LeaveDiagAgg_Name'])
			,'LeaveDiagSop_Code' => returnValidHTMLString($response[0]['LeaveDiagSop_Code'])
			,'LeaveDiagSop_Name' => returnValidHTMLString($response[0]['LeaveDiagSop_Name'])
			,'AnatomDiag_Code' => returnValidHTMLString($response[0]['AnatomDiag_Code'])
			,'AnatomDiag_Name' => returnValidHTMLString($response[0]['AnatomDiag_Name'])
			,'AnatomDiagAgg_Code' => returnValidHTMLString($response[0]['AnatomDiagAgg_Code'])
			,'AnatomDiagAgg_Name' => returnValidHTMLString($response[0]['AnatomDiagAgg_Name'])
			,'AnatomDiagSop_Code' => returnValidHTMLString($response[0]['AnatomDiagSop_Code'])
			,'AnatomDiagSop_Name' => returnValidHTMLString($response[0]['AnatomDiagSop_Name'])
			,'EvnPS_IsDiagMismatch' => returnValidHTMLString($response[0]['EvnPS_IsDiagMismatch'])
			,'EvnPS_IsImperHosp' => returnValidHTMLString($response[0]['EvnPS_IsImperHosp'])
			,'EvnPS_IsShortVolume' => returnValidHTMLString($response[0]['EvnPS_IsShortVolume'])
			,'EvnPS_IsWrongCure' => returnValidHTMLString($response[0]['EvnPS_IsWrongCure'])
			,'EvnDirection_Num' => returnValidHTMLString($response[0]['EvnDirection_Num'])
			,'EvnDirection_setDate' => returnValidHTMLString($response[0]['EvnDirection_setDate'])
		];

		if (allowPersonEncrypHIV($data['session']) && !empty($response[0]['PersonEncrypHIV_Encryp'])) {
			$print_data['Person_Fio'] = returnValidHTMLString($response[0]['PersonEncrypHIV_Encryp']);

			$person_fields = [ 'PolisType_Name', 'Polis_Num', 'Polis_Ser', 'OMSSprTerr_Code', 'OrgSmo_Name',
				'Person_OKATO', 'Sex_Name', 'Person_Birthday', 'Person_AgeYears', 'Person_AgeMonths', 'Person_AgeDays',
				'DocumentType_Name', 'Document_Ser', 'Document_Num', 'KLAreaType_Name', 'KLAreaType_id', 'Person_Phone',
				'PAddress_Name', 'UAddress_Name', 'SocStatus_Code', 'InvalidType_Name', 'PersonCard_Code',
				'PrivilegeType_Code'
			];

			foreach($person_fields as $field) {
				$print_data[$field] = '';
			}
		}

		$html = $this->parser->parse($template, $print_data, !empty($data['returnString']));

		if ( !empty($data['returnString']) ) {
			return [ 'html' => $html ];
		}
		else {
			return $html;
		}
	}
}
