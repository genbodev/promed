<?php	defined('BASEPATH') or die ('No direct script access allowed');

require_once(APPPATH.'models/EvnPS_model.php');

class Khak_EvnPS_model extends EvnPS_model {
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
		$params = [
			'Date' => $data['Date'],
			'Lpu_id' => $data['Lpu_id'],
			'startTime' => '20:00',
			'finalTime' => '19:59',
		];

		if ($data['ARMType'] == 'superadmin' || $data['ARMType'] == 'tfoms') {
			$params['Lpu_id'] = null;
		}

		$query = "
			select
				lp.Lpu_id,
				lp.Lpu_f003mcod as fcode,
				case when exists(
					select * from v_LpuUnit t with(nolock)
					where t.Lpu_id = lp.Lpu_id and t.LpuUnitType_SysNick like 'polka'
				) then 1 else 0 end as hasPolka,
				case when exists(
					select * from v_LpuUnit t with(nolock)
					where t.Lpu_id = lp.Lpu_id 
						and t.LpuUnitType_SysNick like 'stac'
						and :Date BETWEEN LpuUnit_begDate AND isnull(LpuUnit_endDate, :Date)
				) then 1 else 0 end as hasStac
			from
				v_Lpu lp with(nolock)
			where
				lp.Lpu_f003mcod is not null
				and lp.Lpu_f003mcod <> '0'
				and (:Lpu_id is null or lp.Lpu_id = :Lpu_id)
		";
		//echo getDebugSQL($query, $params);die();
		//Логгирование для решения задачи 174066
		$this->load->library('textlog', ['file'=>'174066_' . time() . '.log'] );
		$this->textlog->add( getDebugSQL( $query, $params ) );

		$lpu_arr = $this->queryResult($query, $params);

		if ($lpu_arr === false) {
			return false;
		}

		$query = "
			declare @Lpu_id bigint = :Lpu_id;

			declare @date datetime = :Date;
			declare @startTime datetime = dateadd(day, -2, cast(@date+' '+:startTime as datetime));
			declare @finalTime datetime = dateadd(day, -1, cast(@date+' '+:finalTime as datetime));
			declare @hasPolka int = :hasPolka;
			declare @hasStac int = :hasStac;

			declare @S1 VARCHAR (MAX)='';
			declare @S2 VARCHAR (MAX)='';
			declare @S3 VARCHAR (MAX)='';
			declare @S4 VARCHAR (MAX)='';
			declare @S5 VARCHAR (MAX)='';
			declare @S6 VARCHAR (MAX)='';

			--GetReferToHosp
			if ( @hasPolka = 1 )
				SET @S1 = isnull((
					Select
						ISNULL(cast(lp.Lpu_f003mcod as varchar)
							+ cast(year(EvnDirection_setDT) as varchar)
							+ RIGHT(cast(EvnDirection_Num as varchar), 6), '') as NOM_NAP,		--T(16) Номер направления (MCOD+NP)
						convert(varchar(10), EvnDirection_setDT, 120) as DTA_NAP,
						case when dt.DirType_Code = 5 then 2 else isnull(dt.DirType_Code, 1) end as FRM_MP, -- Int - Форма оказания медицинской помощи
						ISNULL(lp.Lpu_f003mcod, '') as MCOD_NAP, -- T(6)-- numeric(6, 0) - МО, направившая на госпитализацию	(реестровый номер, F003)
						ISNULL(lp1.Lpu_f003mcod, '') as MCOD_STC, -- T(6)-- numeric(6, 0) - МО, куда направлен пациент	(реестровый номер, F003)
						pt.PolisType_CodeF008 as VPOLIS,-- numeric(1, 0) , -- Тип документа, подтверждающего факт страхования по ОМС (F008)
						ISNULL(case when pt.PolisType_CodeF008 in (1, 2) then po.Polis_Ser else null end, '') as SPOLIS, -- Varchar(10) - Серия документа, подтверждающего факт страхования по ОМС
						case when pt.PolisType_CodeF008 = 3 then pe.Person_EdNum else po.Polis_Num end as NPOLIS, -- Numeric(20) - Номер документа, подтверждающего факт страхования по ОМС
						ISNULL(smo.Orgsmo_f002smocod, '') as SMO_CODE, -- numeric(6, 0) - СМО (реестровый номер, F002)
						ISNULL(org.Org_OKATO, '') as ST_OKATO, --Т(5) ОКАТО территории страхования
						ISNULL(pe.Person_SurName, '') as FAM, --varchar(30) – Фамилия
						ISNULL(pe.Person_FirName, '') as IM, --varchar(30) – Имя
						ISNULL(pe.Person_SecName, '') as OT, --varchar(30) – Отчество
						case when pe.Sex_id = 3 then 1 else pe.Sex_id end as W, --numeric(1) – пол (1 - муж, 2 - жен)
						cast(pe.Person_BirthDay as date) as DR, --date – дата рождения
						case when ps.Person_Phone is not null and ps.Person_Phone <> '' then ps.Person_Phone else 'не указан' end as TLF, --Varchar(100) - Контактная информация
						ISNULL(di.Diag_Code, '') as DS, --Char(4) - Код диагноза по МКБ
						0 as KOD_PFK, --numeric(4, 0) - Профиль койки
						ISNULL(lspf.LpuSectionProfile_Code, '') as KOD_PFO, --numeric(4, 0) - Код отделения (профиль)
						case
							when LEN(mp.Person_Snils) = 14 then REPLACE(mp.Person_Snils, ' ', '-')
							when LEN(mp.Person_Snils) = 11 then LEFT(mp.Person_Snils, 3) + '-' + SUBSTRING(mp.Person_Snils, 4, 3) + '-' + SUBSTRING(mp.Person_Snils, 7, 3) + '-' + RIGHT(mp.Person_Snils, 2)
							else ''
						end as KOD_DCT, --Varchar(14) - Снилс медицинского работника, направившего больного
						--ISNULL(convert(varchar(10), tt.TimetableStac_setDate, 120), '') as DTA_PLN -- date - Плановая дата госпитализации
						case
							when tt.TimetableStac_setDate is not null then ISNULL(convert(varchar(10), tt.TimetableStac_setDate, 120), '')
							when ed.DirType_id = 5 then '' -- На госпитализацию экстренную
							when ed.EvnQueue_id is not null then 'в очередь'
							else NULL
						end as DTA_PLN -- Плановая дата госпитализации
					from dbo.v_EvnDirection ed (nolock)
						left join dbo.v_DirType dt (nolock) on dt.DirType_id = ed.DirType_id
						inner join v_Lpu lp (nolock) on lp.Lpu_id = ed.Lpu_id
						outer apply (
							select top 1 Person_Snils
							from v_MedPersonal with (nolock)
							where MedPersonal_id = ed.MedPersonal_id
						) mp
						left join dbo.v_TimetableStac_lite tt (nolock) on tt.EvnDirection_id = ed.EvnDirection_id
						inner join dbo.v_Diag di (nolock) on di.Diag_id = ed.Diag_id
						inner join v_lpu lp1 (nolock) on lp1.Lpu_id = ed.Lpu_did
						left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = ed.LpuSectionProfile_id
						left join fed.v_LpuSectionProfile lspf (nolock) on lspf.LpuSectionProfile_id = lsp.LpuSectionProfile_fedid
						left join v_PersonState ps (nolock) on ps.Person_id = ed.Person_id
						left join PayType PTYPE (nolock) on PTYPE.PayType_id = ED.PayType_id
						inner join dbo.v_Person_all pe (nolock) on pe.PersonEvn_id = ed.PersonEvn_id and pe.Server_id = ed.Server_id
						inner join dbo.v_Polis po (nolock) on po.Polis_id = pe.Polis_id
						inner join dbo.PolisType pt (nolock) on pt.PolisType_id = po.PolisType_id
							and pt.PolisType_CodeF008 is not null
						left join dbo.v_OrgSMO smo (nolock) on smo.OrgSMO_id = po.OrgSmo_id
						left join dbo.v_Org org (nolock) on org.Org_id = smo.Org_id
					where (1 = 1)
						and ed.DirType_id in (1, 5)
						and (@Lpu_id is null or ed.Lpu_id = @Lpu_id)
						--and ed.EvnDirection_setDT> = @startTime
						--and ed.EvnDirection_setDT <= @finalTime
						and PTYPE.PayType_Code != 2			-- исключаем направления с типом оплаты ДМС
					for xml path('ZAP')
				), '')

			--GetHospPlan
			if ( @hasStac = 1 )
				SET @S2 = isnull((
					select
						ISNULL(cast(case when ed.EvnDirection_id is not null then lp1.Lpu_f003mcod else ld.Lpu_f003mcod end as varchar)
							+ cast(year(case when ed.EvnDirection_id is not null then ed.EvnDirection_setDT else eps.EvnDirection_setDT end) as varchar)
							+ RIGHT(cast(case when ed.EvnDirection_id is not null then ed.EvnDirection_Num else eps.EvnDirection_Num end as varchar), 6)
						, '') as NOM_NAP, --T(16) Номер направления (MCOD+NP), --Int
						ISNULL(convert(varchar(10), case when ed.EvnDirection_id is not null then ed.EvnDirection_setDT else eps.EvnDirection_setDT end, 120), '') as DTA_NAP,
						case when prt.PrehospType_Code in (2, 3) then 2 else isnull(prt.PrehospType_Code, 1) end as FRM_MP, -- Int - Форма оказания медицинской помощи
						ISNULL(lp.Lpu_f003mcod, '') as MCOD_STC, -- T(6)-- numeric(6, 0) - МО госпитализации	(реестровый номер, F003)
						ISNULL(case when ed.EvnDirection_id is not null then lp1.Lpu_f003mcod else ld.Lpu_f003mcod end, '') as MCOD_NAP, -- T(6)-- numeric(6, 0) - Код подразделения МО, создавшей направление	(реестровый номер, F003)
						convert(varchar(10), es.EvnSection_setDT, 120) as DTA_FKT, --Дата фактической госпитализации
						replace(convert(varchar(5), es.EvnSection_setDT, 108), ':', '-') as TIM_FKT, --Время фактической госпитализации
						pt.PolisType_CodeF008 as VPOLIS,-- numeric(1, 0) , -- Тип документа, подтверждающего факт страхования по ОМС (F008)
						ISNULL(case when pt.PolisType_CodeF008 in (1, 2) then po.Polis_Ser else Null end, '') as SPOLIS, -- Varchar(10) - Серия документа, подтверждающего факт страхования по ОМС
						case when pt.PolisType_CodeF008 = 3 then pe.Person_EdNum else po.Polis_Num end as NPOLIS, -- Numeric(20) - Номер документа, подтверждающего факт страхования по ОМС
						ISNULL(pe.Person_SurName, '') as FAM, --varchar(30) – Фамилия
						ISNULL(pe.Person_FirName, '') as IM, --varchar(30) – Имя
						ISNULL(pe.Person_SecName, '') as OT, --varchar(30) – Отчество
						case when pe.Sex_id = 3 then 1 else pe.Sex_id end as W, --numeric(1) – пол (1 - муж, 2 - жен)
						cast(pe.Person_BirthDay as date) as DR, --date – дата рождения
						isnull(lsb.LpuSectionBedProfile_Code, '0') as KOD_PFK, --numeric(4, 0) - Профиль койки
						ISNULL(lspf.LpuSectionProfile_Code, '0') as KOD_PFO, --numeric(4, 0) - Код отделения (профиль)
						ISNULL(eps.EvnPS_NumCard, '') as NHISTORY, --Номер карты стационарного больного
						ISNULL(di.Diag_Code, '') as DS --Char(4) - Диагноз приемного отделения
					from v_EvnSection es with (nolock)
						inner join dbo.v_EvnPS eps (nolock) on eps.EvnPS_id = es.EvnSection_pid
						left join dbo.v_EvnDirection ed (nolock) on ed.EvnDirection_id = eps.EvnDirection_id
						left join dbo.v_PrehospType prt (nolock) on eps.PrehospType_id = prt.PrehospType_id
						inner join v_Lpu lp (nolock) on lp.Lpu_id = eps.Lpu_id
						inner join dbo.v_LpuSection ls (nolock) on ls.LpuSection_id = es.LpuSection_id
						left join dbo.v_LpuSectionBedProfile lsb (nolock) on lsb.LpuSectionBedProfile_id = es.LpuSectionBedProfile_id
						left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
						left join fed.v_LpuSectionProfile lspf (nolock) on lspf.LpuSectionProfile_id = lsp.LpuSectionProfile_fedid
						left join v_Lpu_all ld with (nolock) on ld.Org_id = eps.Org_did
						left join v_Lpu lp1 (nolock) on lp1.Lpu_id = ed.Lpu_id
						left join dbo.Diag di (nolock) on di.Diag_id = eps.Diag_pid
						inner join dbo.v_Person_all pe (nolock) on pe.PersonEvn_id = es.PersonEvn_id
							and pe.Server_id = es.Server_id
						inner join dbo.v_Polis po (nolock) on po.Polis_id = pe.Polis_id
						inner join dbo.PolisType pt (nolock) on pt.PolisType_id = po.PolisType_id
							and pt.PolisType_CodeF008 is not null
					where es.EvnSection_Index = 0
						and (@Lpu_id is null or eps.Lpu_id = @Lpu_id)
						--and es.EvnSection_setDT >= @startTime
						--and es.EvnSection_setDT <= @finalTime
						and eps.PrehospWaifRefuseCause_id is null
						and prt.PrehospType_Code = 1
					for xml path('ZAP')
				), '')

			--GetHospEmerg
			if ( @hasStac = 1 )
				SET @S3 = isnull ((
					select
						ISNULL(lp.Lpu_f003mcod, '') as MCOD_STC, -- T(6)-- numeric(6, 0) - МО госпитализации	(реестровый номер, F003)
						convert(varchar(10), es.EvnSection_setDT, 120) as DTA_FKT, --Дата фактической госпитализации
						replace(convert(varchar(5), es.EvnSection_setDT, 108), ':', '-') as TIM_FKT, --Время фактической госпитализации
						pt.PolisType_CodeF008 as VPOLIS,-- numeric(1, 0) , -- Тип документа, подтверждающего факт страхования по ОМС (F008)
						ISNULL(case when pt.PolisType_CodeF008 in (1, 2) then po.Polis_Ser else null end, '') as SPOLIS, -- Varchar(10) - Серия документа, подтверждающего факт страхования по ОМС
						case when pt.PolisType_CodeF008 = 3 then pe.Person_EdNum else po.Polis_Num end as NPOLIS, -- Numeric(20) - Номер документа, подтверждающего факт страхования по ОМС
						ISNULL(smo.Orgsmo_f002smocod, '') as SMO_CODE, -- numeric(6, 0) - СМО (реестровый номер, F002)
						ISNULL(org.Org_OKATO, '') as ST_OKATO, --Т(5) ОКАТО территории страхования
						ISNULL(pe.Person_SurName, '') as FAM, --varchar(30) – Фамилия
						ISNULL(pe.Person_FirName, '') as IM, --varchar(30) – Имя
						ISNULL(pe.Person_SecName, '') as OT, --varchar(30) – Отчество
						case when pe.Sex_id = 3 then 1 else pe.Sex_id end as W, --numeric(1) – пол (1 - муж, 2 - жен)
						cast(pe.Person_BirthDay as date) as DR, --date – дата рождения
						ISNULL(lsb.LpuSectionBedProfile_Code, '0') as KOD_PFK, --numeric(4, 0) - Профиль койки
						ISNULL(lspf.LpuSectionProfile_Code, '') as KOD_PFO, --numeric(4, 0) - Код отделения (профиль)
						ISNULL(eps.EvnPS_NumCard, '') as NHISTORY, --Номер карты стационарного больного
						ISNULL(di.Diag_Code, '') as DS --Char(4) - Диагноз приемного отделения
					from v_EvnSection es with (nolock)
						inner join dbo.v_EvnPS eps (nolock) on eps.EvnPS_id = es.EvnSection_pid
						left join dbo.v_EvnDirection ed (nolock) on ed.EvnDirection_id = eps.EvnDirection_id
						left join dbo.v_PrehospType prt (nolock) on eps.PrehospType_id = prt.PrehospType_id
						inner join v_Lpu lp (nolock) on lp.Lpu_id = eps.Lpu_id
						inner join dbo.v_LpuSection ls (nolock) on ls.LpuSection_id = es.LpuSection_id
						left join dbo.v_LpuSectionBedProfile lsb (nolock) on lsb.LpuSectionBedProfile_id = es.LpuSectionBedProfile_id
						left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
						left join fed.v_LpuSectionProfile lspf (nolock) on lspf.LpuSectionProfile_id = lsp.LpuSectionProfile_fedid
						left join dbo.Diag di (nolock) on di.Diag_id = eps.Diag_pid
						inner join dbo.v_Person_all pe (nolock) on pe.PersonEvn_id = es.PersonEvn_id
							and pe.Server_id = es.Server_id
						inner join dbo.v_Polis po (nolock) on po.Polis_id = pe.Polis_id
						inner join dbo.PolisType pt (nolock) on pt.PolisType_id = po.PolisType_id
							and pt.PolisType_CodeF008 is not null
						left join dbo.v_OrgSMO smo (nolock) on smo.OrgSMO_id = po.OrgSmo_id
						left join dbo.v_Org org (nolock) on org.Org_id = smo.Org_id
					where es.EvnSection_Index = 0
						and (@Lpu_id is null or eps.Lpu_id = @Lpu_id)
						--and es.EvnSection_setDT >= @startTime
						--and es.EvnSection_setDT <= @finalTime
						and eps.PrehospWaifRefuseCause_id is null
						and prt.PrehospType_Code in (2, 3)
					for xml path('ZAP')
				), '')

			--GetCancelReferToHosp
			if ( @hasPolka = 1 or @hasStac = 1 )
				SET @S4 = isnull((
					select
						t.NOM_NAP,
						t.DTA_NAP,
						t.IST_ANL,
						t.ACOD,
						t.PR_ANL
					from (
						select
							ISNULL(cast(lp.Lpu_f003mcod as varchar)
								+ cast(year(ed.EvnDirection_setDT) as varchar)
								+ RIGHT(cast(ed.EvnDirection_Num as varchar), 6)
							, '') as NOM_NAP,	--T(16) Номер направления (MCOD+NP)
							convert(varchar(10), ed.EvnDirection_setDT, 120) as DTA_NAP, --Дата направления	= дата создания направления
							ISNULL(case
								when puc.Lpu_id = ed.Lpu_did then '2'
								when puc.Lpu_id = ed.Lpu_id then '3'
							end, '') as IST_ANL, -- Источник аннулирования
							ISNULL(smolp.Lpu_f003mcod, '') as ACOD, -- numeric(6, 0) МО, отменившая направление (реестровый номер, F003)
							case
								when ed.Lpu_did = ed.Lpu_cid then
								case
									when lUnit.groupType = 'stac' then 2
									else 3
								end
								else case 
									when esc.EvnStatusCause_Code = 1 then 3
									when esc.EvnStatusCause_Code = 5 then 4
									when esc.EvnStatusCause_Code = 18 then 1
									when esc.EvnStatusCause_Code = 22 then 2
									else 5
								end
							end	as PR_ANL -- int  Причина отмены направления
						from dbo.v_EvnDirection ed (nolock)
							outer apply (
								select top 1 EvnStatusCause_id
								from v_EvnStatusHistory with (nolock)
								where Evn_id = ed.EvnDirection_id
									and EvnStatus_id = ed.EvnStatus_id
							) esh
							outer apply (
								select LUT.LpuUnitType_SysNick as groupType
								from 
									v_MedStaffFact MSF (nolock)
									left join v_LpuSection LS (nolock) on LS.LpuSection_id = MSF.LpuSection_id
									left join LpuUnit LU (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
									left join LpuUnitType LUT (nolock) on LUT.LpuUnitType_id = LU.LpuUnitType_id
								where MSF.MedStaffFact_id = ed.MedStaffFact_fid
							) lUnit
							inner join dbo.EvnStatusCause esc (nolock) on esc.EvnStatusCause_id = esh.EvnStatusCause_id
							inner join dbo.pmUserCache puc (nolock) on ed.pmUser_failID = puc.PMUser_id
							inner join v_Lpu smolp (nolock) on smolp.Lpu_id = puc.Lpu_id
							inner join v_Lpu lp (nolock) on lp.Lpu_id = ed.Lpu_id
							inner join dbo.v_Person_all pe (nolock) on pe.PersonEvn_id = ed.PersonEvn_id and pe.Server_id = ed.Server_id
							inner join dbo.v_Polis po (nolock) on po.Polis_id = pe.Polis_id
							inner join dbo.PolisType pt (nolock) on pt.PolisType_id = po.PolisType_id and pt.PolisType_CodeF008 is not null -- без полисов ДМС
						where ed.DirType_id in (1, 5)
							and ed.EvnStatus_id in (12, 13)
							and (@Lpu_id is null or smolp.Lpu_id = @Lpu_id)
							--and ed.EvnDirection_statusDate >= @startTime
							--and ed.EvnDirection_statusDate <= @finalTime
							
						union all

						select
							cast(lp.Lpu_f003mcod as varchar)
								+ cast(year(ed.EvnDirection_setDT) as varchar)
								+ RIGHT(cast(ed.EvnDirection_Num as varchar), 6)
							as NOM_NAP, -- bigint , Идентификатор направления
							cast(ed.EvnDirection_setDT as date) as DTA_NAP, --Дата направления	= дата создания направления
							2 as IST_ANL, --Источник аннулирования
							smolp.Lpu_f003mcod	as	ACOD,-- numeric(6, 0) МО, отменившая направление (реестровый номер, F003)
							case
								when ed.Lpu_did = ed.Lpu_cid then
								case
									when lUnit.groupType = 'stac' then 2
									else 3
								end
								else case
									when pwrc.PrehospWaifRefuseCause_Code = 11 then 1
									when pwrc.PrehospWaifRefuseCause_Code = 9 then 2
									when pwrc.PrehospWaifRefuseCause_Code = 2 then 3
									when pwrc.PrehospWaifRefuseCause_Code = 10 then 4
									else 5
								end
							end as PR_ANL -- int  Причина отмены направления
						from dbo.v_EvnPS eps (nolock)
							inner join dbo.v_EvnDirection ed (nolock) on ed.EvnDirection_id = eps.EvnDirection_id
							outer apply (
								select LUT.LpuUnitType_SysNick as groupType
								from 
									v_MedStaffFact MSF
									left join v_LpuSection  LS (nolock) on LS.LpuSection_id = MSF.LpuSection_id
									left join LpuUnit LU (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
									left join LpuUnitType LUT (nolock) on LUT.LpuUnitType_id = LU.LpuUnitType_id
								where MSF.MedStaffFact_id = ed.MedStaffFact_fid
							) lUnit
							inner join dbo.v_PrehospWaifRefuseCause pwrc with (nolock) on pwrc.PrehospWaifRefuseCause_id = eps.PrehospWaifRefuseCause_id
							inner join v_LpuSection ls (nolock) on ls.LpuSection_id = eps.LpuSection_pid
							inner join v_Lpu smolp (nolock) on smolp.Lpu_id = ls.Lpu_id
							inner join v_Lpu lp (nolock) on lp.Lpu_id = ls.Lpu_id
							inner join dbo.v_Person_all pe (nolock) on pe.PersonEvn_id = ed.PersonEvn_id and pe.Server_id = ed.Server_id
							inner join dbo.v_Polis po (nolock) on po.Polis_id = pe.Polis_id
							inner join dbo.PolisType pt (nolock) on pt.PolisType_id = po.PolisType_id and pt.PolisType_CodeF008 is not null -- без полисов ДМС
						where ed.DirType_id in (1, 5)
							and ed.EvnStatus_id in (12, 13)
							and (@Lpu_id is null or eps.Lpu_id = @Lpu_id)
							--and eps.EvnPS_OutcomeDT >= @startTime
							--and eps.EvnPS_OutcomeDT <= @finalTime
					) as t
					for xml path('ZAP')
				), '')

			--GetExitHosp
			if ( @hasStac = 1 )
				SET @S5 = isnull((
					select
						ISNULL(cast(case when ed.EvnDirection_id is not null then lp1.Lpu_f003mcod else ld.Lpu_f003mcod end as varchar)
							+ cast(year(case when ed.EvnDirection_id is not null then ed.EvnDirection_setDT else eps.EvnDirection_setDT end) as varchar)
							+ RIGHT(cast(case when ed.EvnDirection_id is not null then ed.EvnDirection_Num else eps.EvnDirection_Num end as varchar), 6)
						, '') as NOM_NAP,	--T(16) Номер направления (MCOD+NP), --Int
						ISNULL(convert(varchar(10), case when ed.EvnDirection_id is not null then ed.EvnDirection_setDT else eps.EvnDirection_setDT end, 120), '') as DTA_NAP,
						case
							when prt.PrehospType_Code in (2, 3) then 3
							else isnull(prt.PrehospType_Code, 1)
						end as FRM_MP, -- Int - Форма оказания медицинской помощи
						ISNULL(lp.Lpu_f003mcod, '') as MCOD_STC, -- T(6)-- numeric(6, 0) - МО госпитализации	(реестровый номер, F003)
						convert(varchar(10), es.EvnSection_setDT, 120) as DTA_FKT, -- значение поля «Дата поступления» из первого движения
						convert(varchar(10), eps.EvnPS_disDT, 120) as DTA_END, -- datetime - Дата выбытия
						ISNULL(pe.Person_SurName, '') as FAM, --varchar(30) – Фамилия
						ISNULL(pe.Person_FirName, '') as IM, --varchar(30) – Имя
						ISNULL(pe.Person_SecName, '') as OT, --varchar(30) – Отчество
						case
							when pe.Sex_id = 3 then 1
							else pe.Sex_id
						end as W, --numeric(1) – пол (1 - муж, 2 - жен)
						cast(pe.Person_BirthDay as date) as DR, --date – дата рождения
						isnull(lsb.LpuSectionBedProfile_Code, '0') as KOD_PFK, --numeric(4, 0) - Профиль койки
						ISNULL(lspf.LpuSectionProfile_Code, '') as KOD_PFO, --numeric(4, 0) - Код отделения (профиль)
						ISNULL(eps.EvnPS_NumCard, '') as NHISTORY --Номер карты стационарного больного
					from
						dbo.v_EvnPS eps (nolock)
						inner join v_EvnSection es with (nolock) on es.EvnSection_pid = eps.EvnPS_id
							and es.EvnSection_Index = 0
						left join dbo.v_EvnDirection ed (nolock) on ed.EvnDirection_id = eps.EvnDirection_id
						left join dbo.v_PrehospType prt (nolock) on prt.PrehospType_id = case when ed.EvnDirection_id is not null then ed.PrehospType_did else eps.PrehospType_id end
						inner join v_Lpu lp (nolock) on lp.Lpu_id = eps.Lpu_id
						inner join dbo.v_LpuSection ls (nolock) on ls.LpuSection_id = eps.LpuSection_id
						left join dbo.v_LpuSectionBedProfile lsb (nolock) on lsb.LpuSectionBedProfile_id = es.LpuSectionBedProfile_id
						left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
						left join fed.v_LpuSectionProfile lspf (nolock) on lspf.LpuSectionProfile_id = lsp.LpuSectionProfile_fedid
						left join v_Lpu_all ld with (nolock) on ld.Org_id = eps.Org_did
						left join v_Lpu lp1 (nolock) on lp1.Lpu_id = ed.Lpu_id
						inner join dbo.v_Person_all pe (nolock) on pe.PersonEvn_id = eps.PersonEvn_id
							and pe.Server_id = eps.Server_id
						inner join dbo.v_LeaveType lt (nolock) on lt.LeaveType_id = eps.LeaveType_id
						inner join dbo.v_Polis po (nolock) on po.Polis_id = pe.Polis_id
						inner join dbo.PolisType pt (nolock) on pt.PolisType_id = po.PolisType_id
							and pt.PolisType_CodeF008 is not null
					where (@Lpu_id is null or eps.Lpu_id = @Lpu_id)
						--and eps.EvnPS_disDT >= @startTime
						--and eps.EvnPS_disDT <= @finalTime
						and lt.LeaveType_Code IN (1, 4)
					for xml path('ZAP')
				), '')

			--GetCouchInfo
			if @hasStac = 1
				SET @S6 = isnull((
					select t.*
					from rpt19.Han_hosp_enable(@Lpu_id, null, @date) t
					for xml path('ZAP')
				), '')

			SELECT
				@S1 as N1,	--GetReferToHosp
				@S2 as N2,	--GetHospPlan
				@S3 as N3,	--GetHospEmerg,
				@S4 as N4,	--GetCancelReferToHosp,
				@S5 as N5,	--GetExitHosp,
				@S6 as N6	--GetCouchInfo
		";

		$hosp_data_xml_arr = [];

		foreach ($lpu_arr as $lpu) {
			$params['Lpu_id'] = $lpu['Lpu_id'];
			$params['hasStac'] = $lpu['hasStac'];
			$params['hasPolka'] = $lpu['hasPolka'];
			//echo getDebugSQL($query, $params);die;
			//Логгирование для решения задачи 174066
			$this->textlog->add(getDebugSQL($query, $params));

			$hosp_data_xml_arr[$lpu['fcode']] = $this->getFirstRowFromQuery($query, $params);

			//Логгирование для решения задачи 174066
			//$this->textlog->add($hosp_data_xml_arr[$lpu['fcode']]);
		}
		//echo json_encode($hosp_data_xml_arr);die;
		//Логгирование для решения задачи 174066
		//$this->textlog->add( $hosp_data_xml_arr );

		return $hosp_data_xml_arr;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function getEvnPSFields($data) {
		$query = "
			select top 1
				 ISNULL(EPS.EvnPS_NumCard, '') as EvnPS_NumCard
				,RTRIM(ISNULL(Lpu.Lpu_Name, '')) as Lpu_Name
				,RTRIM(ISNULL(PLST.PolisType_Name, '')) as PolisType_Name
				,CASE WHEN PLST.PolisType_Code = 4 then '' ELSE RTRIM(ISNULL(PLS.Polis_Ser, '')) END as Polis_Ser
				,CASE WHEN PLST.PolisType_Code = 4 then isnull(RTRIM(PS.Person_EdNum), '') ELSE RTRIM(ISNULL(PLS.Polis_Num, '')) END AS Polis_Num
				--,RTRIM(ISNULL(PLS.Polis_Num, '')) as Polis_Num
				--,RTRIM(ISNULL(PLS.Polis_Ser, '')) as Polis_Ser
				,RTRIM(ISNULL(OST.OMSSprTerr_Code, '')) as OMSSprTerr_Code
				,RTRIM(ISNULL(OrgSmo.OrgSMO_Name, '')) as OrgSmo_Name
				,RTRIM(ISNULL(OS.Org_OKATO, '')) as OrgSmo_OKATO
				,PS.Person_id
				,RTRIM(RTRIM(ISNULL(PS.Person_Surname, '')) + ' ' + RTRIM(ISNULL(PS.Person_Firname, '')) + ' ' + RTRIM(ISNULL(PS.Person_Secname, ''))) as Person_Fio
				,RTRIM(ISNULL(SX.Sex_Name, '')) as Sex_Name
				,RTRIM(ISNULL(SX.Sex_Code, '')) as Sex_Code
				,convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday
				,dbo.Age2(PS.Person_Birthday, EPS.EvnPS_setDate) as Person_Age
				,RTRIM(ISNULL(D.Document_Num, '')) as Document_Num
				,RTRIM(ISNULL(D.Document_Ser, '')) as Document_Ser
				,RTRIM(ISNULL(DT.DocumentType_Name, '')) as DocumentType_Name
				,RTRIM(ISNULL(KLAT.KLAreaType_Name, '')) as KLAreaType_Name
				,RTRIM(ISNULL(KLAT.KLAreaType_id, '')) as KLAreaType_id
				,RTRIM(ISNULL(PS.Person_Phone, '')) as Person_Phone
				,RTRIM(ISNULL(PAddr.Address_Address, '')) as PAddress_Name
				,RTRIM(ISNULL(UAddr.Address_Address, '')) as UAddress_Name
				,RTRIM(ISNULL(MSF.Person_Fio,'')) as FIO_Priem
				,RTRIM(ISNULL(PT.PayType_Name, '')) as PayType_Name
				,RTRIM(ISNULL(PT.PayType_Code, '')) as PayType_Code
				,RTRIM(ISNULL(SS.SocStatus_Name, '')) as SocStatus_Name
				,RTRIM(ISNULL(SS.SocStatus_Code, '')) as SocStatus_Code
				,RTRIM(ISNULL(SS.SocStatus_SysNick, '')) as SocStatus_SysNick
				,IT.PrivilegeType_id
				,/*RTRIM(ISNULL(IT.PrivilegeType_Code, '')) + ' ' + */RTRIM(ISNULL(IT.PrivilegeType_Name, '')) as PrivilegeType_Name
				,ISNULL(IT2.PrivilegeType_Code, '') as PrivilegeType_Code
				,RTRIM(ISNULL(PersPriv.PrivilegeType_Name, '')) as PersPriv
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
				,RTRIM(ISNULL(DiagH.Diag_Name, '')) as PrehospDiag_Name
				,RTRIM(ISNULL(DiagP.Diag_Name, '')) as AdmitDiag_Name
				,RTRIM(ISNULL(PHTX.PrehospToxic_Name, '')) as PrehospToxic_Name
				,RTRIM(ISNULL(PHTX.PrehospToxic_Code, '')) as PrehospToxic_Code
				,RTRIM(ISNULL(LSTT.LpuSectionTransType_Name, '')) as LpuSectionTransType_Name
				,RTRIM(ISNULL(LSTT.LpuSectionTransType_Code, '')) as LpuSectionTransType_Code
				,RTRIM(ISNULL(PHT.PrehospType_Name, '')) as PrehospType_Name
				,RTRIM(ISNULL(PHT.PrehospType_Code, '')) as PrehospType_Code
				,case when PHT.PrehospType_Code in (2,3) then 3 when PHT.PrehospType_Code = 1 then 4 end as PregospType_sCode
				,case when ISNULL(EPS.EvnPS_HospCount, 1) = 1 then 'первично' else 'повторно' end as EvnPS_HospCount
				,case when ISNULL(EPS.EvnPS_HospCount, 1) = 1 then 1 else 2 end as IsFirst
				,case when EPS.Okei_id = '100'
					then
						case
							when (EPS.EvnPS_TimeDesease <= 6) then 1
							when (EPS.EvnPS_TimeDesease > 24) then 3
							when EPS.EvnPS_TimeDesease  is not null then 2
							else null
						end
					else 3
				end as EvnPS_TimeDeseaseType
				,EPS.EvnPS_TimeDesease
				,EPS.EvnDirection_Num
				,EPS.EvnPS_CodeConv
				,EPS.EvnPS_NumConv
				,convert(varchar(10), EPS.EvnDirection_SetDT, 104) as EvnDirection_SetDT
				,RTRIM(PC.PersonCard_Code) as PersonCard_Code
				,RTRIM(ISNULL(PHTR.PrehospTrauma_Name, '')) as PrehospTrauma_Name
				,RTRIM(ISNULL(PHTR.PrehospTrauma_Code, '')) as PrehospTrauma_Code
				,convert(varchar(10), EPS.EvnPS_setDate, 104) as EvnPS_setDate
				,EPS.EvnPS_setTime
				,RTRIM(ISNULL(LSFirst.LpuSection_Name, '')) as LpuSectionFirst_Name
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
				,LT.LeaveType_Code as LeaveType_sCode
				,RTRIM(ISNULL(RD.ResultDesease_Name, '')) as ResultDesease_Name
				,RD.ResultDesease_Code
				,RD.ResultDesease_Code as ResultDesease_sCode
				,case
					when LT.LeaveType_SysNick like 'die' then 6
					when RD.ResultDesease_SysNick in('zdorvosst','zdorchast','zdornar') then 1
					when RD.ResultDesease_SysNick in('rem','uluc','stabil', 'kompens', 'hron') then 2
					when RD.ResultDesease_SysNick in('noeffect') then 3
					when RD.ResultDesease_SysNick in('yatr','novzab','progress') then 4
					when RD.ResultDesease_SysNick like 'zdor' then 5
					else RD.ResultDesease_Code
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
				,case when EPS.EvnPS_IsDiagMismatch = 2 then 'Несовпадение диагноза; ' else null end as EvnPS_IsDiagMismatch
				,case when EPS.EvnPS_IsImperHosp = 2 then 'Несвоевременность госпитализации; ' else null end as EvnPS_IsImperHosp
				,case when EPS.EvnPS_IsShortVolume = 2 then 'Недост. объем клинико-диаг. обследования; ' else null end as EvnPS_IsShortVolume
				,case when EPS.EvnPS_IsWrongCure = 2 then 'Неправильная тактика лечения; ' else null end as EvnPS_IsWrongCure
				,EPS.EvnPS_IsDiagMismatch as EvnPS_IsDiagMismatch1
				,EPS.EvnPS_IsImperHosp as EvnPS_IsImperHosp1
				,EPS.EvnPS_IsShortVolume as EvnPS_IsShortVolume1
				,EPS.EvnPS_IsWrongCure as EvnPS_IsWrongCure1
				,BSS.BirthSpecStac_OutcomPeriod
				,BSS.BirthSpecStac_CountPregnancy
				,LC.LeaveCause_Code
				,EPS.PrehospWaifRefuseCause_id
				,IsRW.YesNo_Name as IsRW
				,IsAIDS.YesNo_Name as IsAIDS
				,PEH.PersonEncrypHIV_Encryp
			from v_EvnPS EPS with (nolock)
				inner join v_Lpu Lpu with(nolock) on Lpu.Lpu_id = EPS.Lpu_id
				inner join v_PersonState PS with (nolock) on PS.Person_id = EPS.Person_id
				left join v_EvnSection ESLast with (nolock) on ESLast.EvnSection_pid = EPS.EvnPS_id
					and ESLast.EvnSection_Index = ESLast.EvnSection_Count - 1
				left join v_EvnSection ESFirst with (nolock) on ESFirst.EvnSection_pid = EPS.EvnPS_id
					and ESFirst.EvnSection_Index = 0

				left join v_LpuSection LSLast with (nolock) on LSLast.LpuSection_id = ESLast.LpuSection_id
				left join LpuUnit LULast with (nolock) on LULast.LpuUnit_id = LSLast.LpuUnit_id
				left join LpuUnitType LUTLast with (nolock) on LUTLast.LpuUnitType_id = LULast.LpuUnitType_id
				left join v_PrehospDirect PD with(nolock) on EPS.PrehospDirect_id=PD.PrehospDirect_id
				left join v_EvnLeave ELeave with (nolock) on ELeave.EvnLeave_pid = ESLast.EvnSection_id
				left join LeaveCause LC with (nolock) on LC.LeaveCause_id = ELeave.LeaveCause_id
				left join v_Polis PLS with (nolock) on PLS.Polis_id = PS.Polis_id
				left join v_OmsSprTerr OST with (nolock) on OST.OmsSprTerr_id = PLS.OmsSprTerr_id
				left join v_PolisType PLST with (nolock) on PLST.PolisType_id = PLS.PolisType_id
				left join v_OrgSmo OrgSmo with (nolock) on OrgSmo.OrgSmo_id = PLS.OrgSmo_id
				left join v_Org OS with (nolock) on OS.Org_id = OrgSmo.Org_id
				left join v_Address UAddr with (nolock) on UAddr.Address_id = PS.UAddress_id
				left join KLArea country with (nolock) on country.KLArea_id = UAddr.KLCountry_id
				left join KLArea rgn with (nolock) on rgn.KLArea_id = UAddr.KLRgn_id
				left join KLArea srgn with (nolock) on srgn.KLArea_id = UAddr.KLSubRgn_id
				left join KLArea city with (nolock) on city.KLArea_id = UAddr.KLCity_id
				left join KLArea town with (nolock) on town.KLArea_id = UAddr.KLSubRgn_id
				left join KLStreet street with (nolock) on street.KLStreet_id = UAddr.KLStreet_id
				left join v_Address PAddr with (nolock) on PAddr.Address_id = PS.PAddress_id
				left join v_KLAreaType KLAT with (nolock) on KLAT.KLAreaType_id = PAddr.KLAreaType_id
				left join v_Document D with (nolock) on D.Document_id = PS.Document_id
				left join v_DocumentType DT with (nolock) on DT.DocumentType_id = D.DocumentType_id
				left join v_Sex SX with (nolock) on SX.Sex_id = PS.Sex_id
				left join v_PayType PT with (nolock) on PT.PayType_id = EPS.PayType_id
				left join v_SocStatus SS with (nolock) on SS.SocStatus_id = PS.SocStatus_id
				outer apply (
					select top 1
						PrivilegeType_id,
						PrivilegeType_Code,
						PrivilegeType_Name
					from v_PersonPrivilege WITH (NOLOCK)
					where PrivilegeType_Code in ('81', '82', '83') and Person_id = PS.Person_id
					order by PersonPrivilege_begDate desc
				) IT
				outer apply (
					select top 1
						PrivilegeType_id,
						PrivilegeType_Code,
						PrivilegeType_Name
					from v_PersonPrivilege WITH (NOLOCK)
					where PrivilegeType_Code in ('11', '20', '91', '81', '82', '83', '84') and Person_id = PS.Person_id
					order by PersonPrivilege_begDate desc
				) IT2
				outer apply (
					select top 1
						PP.PrivilegeType_id,
						PP.PrivilegeType_Code,
						PP.PrivilegeType_Name
					from v_PersonPrivilege PP WITH (NOLOCK)
					where PP.Person_id = PS.Person_id
						--and PP.PersonPrivilege_begDate >= EPS.EvnPS_setDate
						and (PP.PersonPrivilege_endDate is null or PP.PersonPrivilege_endDate <= EPS.EvnPS_disDate)
					order by PP.PersonPrivilege_begDate desc
				) PersPriv
				outer apply(
					select top 1
						BSS.BirthSpecStac_OutcomPeriod,
						BSS.BirthSpecStac_CountPregnancy
					from v_BirthSpecStac BSS with(nolock)
						left join v_EvnSection ES with(nolock) on ES.EvnSection_id = BSS.EvnSection_id
					where ES.EvnSection_pid = EPS.EvnPS_id
				) BSS
				left join v_PersonCard PC with(nolock) on PC.Person_id = PS.Person_id
					and PC.PersonCard_begDate is not null
					and PC.PersonCard_begDate <= EPS.EvnPS_insDT
					and (PC.PersonCard_endDate is null or PC.PersonCard_endDate > EPS.EvnPS_insDT)
					and PC.Lpu_id = EPS.Lpu_id
				left join v_LpuSection PHLS with (nolock) on PHLS.LpuSection_id = EPS.LpuSection_did
				left join v_OrgHead OH with (nolock) on OH.LpuUnit_id = PHLS.LpuUnit_id and OH.OrgHeadPost_id=13
				left join v_MedPersonal OHMP with(nolock) on OHMP.Person_id = OH.Person_id
				left join v_Lpu PreHospLpu with (nolock) on PreHospLpu.Lpu_id = EPS.Lpu_did
				left join v_MedStaffFact MSF with(nolock) on MSF.MedStaffFact_id=EPS.MedStaffFact_pid
				left join v_OrgMilitary PHOM with (nolock) on PHOM.OrgMilitary_id = EPS.OrgMilitary_did
				left join v_Org PHO with (nolock) on PHO.Org_id = EPS.Org_did
				left join v_PrehospArrive PA with (nolock) on PA.PrehospArrive_id = EPS.PrehospArrive_id
				left join v_Diag DiagH with (nolock) on DiagH.Diag_id = EPS.Diag_did
				left join v_Diag DiagP with (nolock) on DiagP.Diag_id = EPS.Diag_pid
				left join v_PrehospToxic PHTX with (nolock) on PHTX.PrehospToxic_id = EPS.PrehospToxic_id
				left join v_LpuSectionTransType LSTT with (nolock) on LSTT.LpuSectionTransType_id = EPS.LpuSectionTransType_id
				left join v_PrehospType PHT with (nolock) on PHT.PrehospType_id = EPS.PrehospType_id
				left join v_PrehospTrauma PHTR with (nolock) on PHTR.PrehospTrauma_id = EPS.PrehospTrauma_id
				left join v_MedPersonal MPFirst with (nolock) on EPS.MedPersonal_pid = MPFirst.MedPersonal_id
				left join v_LpuSection LSFirst with (nolock) on LSFirst.LpuSection_id = ESFirst.LpuSection_id
				left join v_LpuSectionBedProfile LSBPFirst with (nolock) on LSBPFirst.LpuSectionBedProfile_id = LSFirst.LpuSectionBedProfile_id
				left join v_LeaveType LT with (nolock) on LT.LeaveType_id = EPS.LeaveType_id
				left join v_EvnLeave EL with (nolock) on EL.EvnLeave_pid = ESLast.EvnSection_id
				left join v_EvnDie ED with (nolock) on ED.EvnDie_pid = ESLast.EvnSection_id
				left join v_EvnOtherLpu EOL with (nolock) on EOL.EvnOtherLpu_pid = ESLast.EvnSection_id
				left join v_EvnOtherStac EOST with (nolock) on EOST.EvnOtherStac_pid = ESLast.EvnSection_id
				left join v_ResultDesease RD with (nolock) on RD.ResultDesease_id = COALESCE(EL.ResultDesease_id, EOL.ResultDesease_id, EOST.ResultDesease_id, ED.ResultDesease_id)
				left join v_PersonEncrypHIV PEH with(nolock) on PEH.Person_id = PS.Person_id
				outer apply (
					select top 1
						 EvnStick_id
						,EvnStick_setDT
						,EvnStick_disDT
					from
						v_EvnStick with (nolock)
					where
						EvnStick_pid = EPS.EvnPS_id
					order by
						EvnStick_setDT
				) EST
				outer apply (
					select top 1
						 dbo.Age2(t2.Person_Birthday, EPS.EvnPS_setDT) as Person_Age
						,t3.Sex_Name
						,t3.Sex_id
					from
						v_EvnStickCarePerson t1 with (nolock)
						left join v_PersonState t2 with (nolock) on t2.Person_id = t1.Person_id
						left join v_Sex t3 with (nolock) on t3.Sex_id = t2.Sex_id
					where
						t1.Evn_id = EST.EvnStick_id
				) ESTCP
				left join v_Diag DG with (nolock) on DG.Diag_id = ESLast.Diag_id and ISNULL(ESLast.LeaveType_id, 0) != 5
				left join v_Diag PAD with (nolock) on PAD.Diag_id = ED.Diag_aid
				outer apply (
					select top 1 Diag_id
					from v_EvnDiagPS with (nolock)
					where EvnDiagPS_pid = ESLast.EvnSection_id
						and DiagSetClass_id = 2
				) TDGA
				left join v_Diag DGA with (nolock) on DGA.Diag_id = TDGA.Diag_id and ISNULL(ESLast.LeaveType_id, 0) != 5
				outer apply (
					select top 1 Diag_id
					from v_EvnDiagPS with (nolock)
					where EvnDiagPS_pid = ESLast.EvnSection_id
						and DiagSetClass_id = 3
				) TDGS
				left join v_Diag DGS with (nolock) on DGS.Diag_id = TDGS.Diag_id and ISNULL(ESLast.LeaveType_id, 0) != 5
				outer apply (
					select top 1 Diag_id
					from v_EvnDiagPS with (nolock)
					where EvnDiagPS_pid = ED.EvnDie_id
						and DiagSetClass_id = 2
				) TPADA
				left join v_Diag PADA with (nolock) on PADA.Diag_id = TPADA.Diag_id
				outer apply (
					select top 1 Diag_id
					from v_EvnDiagPS with (nolock)
					where EvnDiagPS_pid = ED.EvnDie_id
						and DiagSetClass_id = 3
				) TPADS
				left join v_Diag PADS with (nolock) on PADS.Diag_id = TPADS.Diag_id
				left join v_LpuUnitType oLUT with(nolock) on oLUT.LpuUnitType_id = EOST.LpuUnitType_oid
				outer apply (
					select top 1 YN.YesNo_Name
					from v_EvnUsluga EU with(nolock)
					inner join v_UslugaComplex UC with(nolock) on UC.UslugaComplex_id = EU.UslugaComplex_id
					inner join v_YesNo YN with(nolock) on YN.YesNo_Code = 1
					where EU.EvnUsluga_rid = EPS.EvnPS_id and UC.UslugaComplex_Code like 'A12.06.011'
					and EU.EvnUsluga_SetDT is not null
				) as IsRW
				outer apply (
					select top 1 YN.YesNo_Name
					from v_EvnUsluga EU with(nolock)
					inner join v_UslugaComplex UC with(nolock) on UC.UslugaComplex_id = EU.UslugaComplex_id
					inner join v_YesNo YN with(nolock) on YN.YesNo_Code = 1
					where
						EU.EvnUsluga_rid = EPS.EvnPS_id and UC.UslugaComplex_Code like 'A09.05.228'
						and EU.EvnUsluga_SetDT is not null
				) as IsAIDS
			where
				EPS.EvnPS_id = :EvnPS_id
		";
		if(!isTFOMSUser() && empty($data['session']['medpersonal_id'])){
			$query.=' and EPS.Lpu_id = :Lpu_id';
		}
		//echo "<pre>".getDebugSQL($query, array('EvnPS_id' => $data['EvnPS_id'], 'Lpu_id' => $data['Lpu_id']))."</pre>"; exit();
		$result = $this->db->query($query, array(
			'EvnPS_id' => $data['EvnPS_id'],
			'Lpu_id' => $data['Lpu_id']
		));
		//Отдельно получим сопутствующие диагнозы и осложнения
		$query_diag_sop = "
			select
				DGS.Diag_Code as LeaveDiagSop_Code,
				DGS.Diag_Name as LeaveDiagSop_Name
			from v_EvnPS EPS with(nolock)
			left join v_EvnSection ESLast with (nolock) on ESLast.EvnSection_pid = EPS.EvnPS_id
					and ESLast.EvnSection_Index = ESLast.EvnSection_Count - 1
			inner join v_EvnDiagPS EDPS with(nolock) on EDPS.EvnDiagPS_pid = ESLast.EvnSection_id and EDPS.DiagSetClass_id = 3
			left join v_Diag DGS with (nolock) on DGS.Diag_id = EDPS.Diag_id
			where
				EPS.EvnPS_id = :EvnPS_id
				and EPS.Lpu_id = :Lpu_id
		";
		$query_diag_osl = "
		select
				DGA.Diag_Code as LeaveDiagAgg_Code,
				DGA.Diag_Name as LeaveDiagAgg_Name
			from v_EvnPS EPS with(nolock)
			left join v_EvnSection ESLast with (nolock) on ESLast.EvnSection_pid = EPS.EvnPS_id
					and ESLast.EvnSection_Index = ESLast.EvnSection_Count - 1
			inner join v_EvnDiagPS EDPS with(nolock) on EDPS.EvnDiagPS_pid = ESLast.EvnSection_id and EDPS.DiagSetClass_id = 2
			left join v_Diag DGA with (nolock) on DGA.Diag_id = EDPS.Diag_id
			where
				EPS.EvnPS_id = :EvnPS_id
				and EPS.Lpu_id = :Lpu_id
		";
		$result_temp = array();
		$result_diag_sop = $this->db->query($query_diag_sop, array(
			'EvnPS_id' => $data['EvnPS_id'],
			'Lpu_id' => $data['Lpu_id']
		));
		if(is_object($result_diag_sop)){
			$result_temp[0]['LeaveDiagSop_Name'] = '';
			$result_temp[0]['LeaveDiagSop_Code'] = '';
			$result_diag_sop = $result_diag_sop->result('array');
			for($i=0;$i<count($result_diag_sop);$i++){
				$result_temp[0]['LeaveDiagSop_Name'] = $result_temp[0]['LeaveDiagSop_Name'] . $result_diag_sop[$i]['LeaveDiagSop_Name'];
				$result_temp[0]['LeaveDiagSop_Code'] = $result_temp[0]['LeaveDiagSop_Code'] . $result_diag_sop[$i]['LeaveDiagSop_Code'];
			}
		}
		$result_diag_osl = $this->db->query($query_diag_osl, array(
			'EvnPS_id' => $data['EvnPS_id'],
			'Lpu_id' => $data['Lpu_id']
		));
		if(is_object($result_diag_osl)){
			$result_temp[0]['LeaveDiagAgg_Name'] = '';
			$result_temp[0]['LeaveDiagAgg_Code'] = '';
			$result_diag_osl = $result_diag_osl->result('array');
			for($i=0;$i<count($result_diag_osl);$i++){
				$result_temp[0]['LeaveDiagAgg_Name'] = $result_temp[0]['LeaveDiagAgg_Name'] . $result_diag_osl[$i]['LeaveDiagAgg_Name'];
				$result_temp[0]['LeaveDiagAgg_Code'] = $result_temp[0]['LeaveDiagAgg_Code'] . $result_diag_osl[$i]['LeaveDiagAgg_Code'];
			}
		}
		//Отдельно получим категории льготности (если несколько, то нужно выводить все) (https://redmine.swan.perm.ru/issues/23968 #25)
		$query_priv = "
			select 	PT.PrivilegeType_id,
					PT.PrivilegeType_Code,
					PT.PrivilegeType_Name
			from v_EvnPS EPS with (nolock)
			left join v_PersonEvn PE with (nolock) on PE.PersonEvn_id = EPS.PersonEvn_id
			left join v_PersonPrivilege PP with (nolock) on PP.Person_id = PE.Person_id
			inner join v_PrivilegeType PT with (nolock) on PT.PrivilegeType_id = PP.PrivilegeType_id
			where (PP.PersonPrivilege_endDate is null or PP.PersonPrivilege_endDate >= EPS.EvnPS_setDate)
			and (PP.PersonPrivilege_begDate <= EPS.EvnPS_disDate or EPS.EvnPS_disDate is null)
			and EPS.EvnPS_id = :EvnPS_id
			and EPS.Lpu_id = :Lpu_id
			order by PP.PersonPrivilege_begDate
		";
		$res_priv = $this->db->query($query_priv, array('EvnPS_id' => $data['EvnPS_id'],
			'Lpu_id' => $data['Lpu_id']));
		if ( is_object($result) ) {
			$result = $result->result('array');
			$result[0]['Privileges'] = '';
			if(is_object($res_priv)){
				$res_priv = $res_priv->result('array');
				for($i=0;$i<count($res_priv);$i++){
					$result[0]['Privileges'] = ' ' . $result[0]['Privileges'] . $res_priv[$i]['PrivilegeType_Name'] . '; ';
				}
			}
			$result[0]['LeaveDiagSop_Name'] = $result_temp[0]['LeaveDiagSop_Name'];
			$result[0]['LeaveDiagSop_Code'] = $result_temp[0]['LeaveDiagSop_Code'];
			$result[0]['LeaveDiagAgg_Name'] = $result_temp[0]['LeaveDiagAgg_Name'];
			$result[0]['LeaveDiagAgg_Code'] = $result_temp[0]['LeaveDiagAgg_Code'];
			return $result;
			//return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @param $response
	 * @return string
	 */
	protected function _printEvnPS($data, $response) {
		$invalid_type_name = '';
		$template = 'evn_ps_template_list_a4_hakasiya';

		$evn_section_data = array();
		$evn_usluga_oper_data = array();

		//$response_temp = $this->getEvnSectionNarrowBedData($data);
		$narrow_bed_arr = array();
		/*if (is_array($response_temp)) {
			foreach($response_temp as &$item) {
				$item['LpuSection_Name'] = '&nbsp;';
				$item['MedPersonal_Code'] = '&nbsp;';
				$item['EvnSectionDiagOsn_Code'] = '&nbsp;';
				$item['EvnSectionMesOsn_Code'] = '&nbsp;';
				$item['EvnSectionPayType_Name'] = '&nbsp;';

				$key = $item['EvnSection_id'];
				$narrow_bed_arr[$key][] = $item;
			}
		}*/

		$response_temp = $this->getEvnSectionData($data);

		if ( is_array($response_temp) ) {
			$evn_section_data = array();
			$i = 0;
			foreach($response_temp as $item) {
				$evn_section_data[$i] = $item;
				if(!empty($narrow_bed_arr[$item['EvnSection_id']])){
					foreach($narrow_bed_arr[$item['EvnSection_id']] as $bed) {
						$i++;
						$evn_section_data[$i] = $bed;
					}
				}
				$i++;
			}

			for ( $i = 0; $i < (count($evn_section_data) < 2 ? 2 : count($evn_section_data)); $i++ ) {
				if ( $i >= count($evn_section_data) ) {
					$evn_section_data[$i] = array(
						'Index' => $i + 1,
						'LpuSection_Name' => '&nbsp;',
						'EvnSection_setDT' => '&nbsp;',
						'EvnSection_disDT' => '&nbsp;',
						'EvnSectionDiagOsn_Code' => '&nbsp;',
						'EvnSectionMesOsn_Code' => '&nbsp;',
						'EvnSection_UKL' => '&nbsp;',
						'EvnSectionPayType_Name' => '&nbsp;',
						'LpuSectionBedProfile_Name' => '&nbsp;',
						'MedPersonal_Code' => '&nbsp;'
					);
				} else {
					$evn_section_data[$i]['Index'] = $i + 1;
					if(!empty($evn_section_data[$i]['PayType_Name'])) { $evn_section_data[$i]['EvnSectionPayType_Name'] = $evn_section_data[$i]['PayType_Name']; }
				}
			}
		}

		$response_temp = $this->getEvnUslugaOperData($data);

		if ( is_array($response_temp) ) {
			for ( $i = 0; $i < count($response_temp); $i++ ) {
				$evn_usluga_oper_data[] = array(
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
					'EvnUslugaOperPayType_Name' => $response_temp[$i]['PayType_Name'],
					'AggType_Name' => !empty($response_temp[$i]['AggType_Name']) ? $response_temp[$i]['AggType_Name'] : '&nbsp;',
					'AggType_Code' => !empty($response_temp[$i]['AggType_Code']) ? $response_temp[$i]['AggType_Code'] : '&nbsp;',
				);
			}

			// https://redmine.swan.perm.ru/issues/6484
			// savage: Добавляем пустые строки в таблицу с хирургическими операциями, если количество операций меньше двух
			for ( $j = $i; $j < 3; $j++ ) {
				$evn_usluga_oper_data[] = array(
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
					'EvnUslugaOperPayType_Name' => '&nbsp;<br />&nbsp;',
					'AggType_Name' => '&nbsp;',
					'AggType_Code' => '&nbsp;',
				);
			}
		}

		$response_temp = $this->getPersonPrivilegeDataHakas(array(
			'Person_id' => $response[0]['Person_id'],
			'PersonPrivilege_begDate' => ConvertDateFormat($response[0]['EvnPS_setDate']),
			'PersonPrivilege_endDate' => ConvertDateFormat($response[0]['EvnPS_disDate']),
		));

		$privilege_type = array();
		if (is_array($response_temp)) {
			foreach($response_temp as $item) {
				$privilege_type[] = $item['PrivilegeType_Code'];
			}
		}

		/*switch ( $response[0]['PrivilegeType_id'] ) {
			case 81:
				$invalid_type_name = "3-я группа";
			break;

			case 82:
				$invalid_type_name = "2-я группа";
			break;

			case 83:
				$invalid_type_name = "1-я группа";
			break;
		}*/

		$print_data = array(
			'EvnPSTemplateTitle' => 'Печать карты выбывшего из стационара'
			,'EvnPS_NumCard' => returnValidHTMLString($response[0]['EvnPS_NumCard'])
			,'PolisType_Name' => returnValidHTMLString($response[0]['PolisType_Name'])
			,'Polis_Num' => returnValidHTMLString($response[0]['Polis_Num'])
			,'Polis_Ser' => returnValidHTMLString($response[0]['Polis_Ser'])
			,'OMSSprTerr_Code' => returnValidHTMLString($response[0]['OMSSprTerr_Code'])
			,'OrgSmo_Name' => returnValidHTMLString($response[0]['OrgSmo_Name'])
			,'OrgSmo_OKATO' => returnValidHTMLString($response[0]['OrgSmo_OKATO'])
			,'Person_Fio' => returnValidHTMLString($response[0]['Person_Fio'])
			,'Person_OKATO' => returnValidHTMLString($response[0]['Person_OKATO'])
			,'Sex_Name' => returnValidHTMLString($response[0]['Sex_Name'])
			,'Person_Birthday' => returnValidHTMLString($response[0]['Person_Birthday'])
			,'Person_Age' => returnValidHTMLString($response[0]['Person_Age'])
			,'DocumentType_Name' => returnValidHTMLString($response[0]['DocumentType_Name'])
			,'Document_Ser' => returnValidHTMLString($response[0]['Document_Ser'])
			,'Document_Num' => returnValidHTMLString($response[0]['Document_Num'])
			,'KLAreaType_Name' => returnValidHTMLString($response[0]['KLAreaType_Name'])
			,'KLAreaType_id' => returnValidHTMLString($response[0]['KLAreaType_id'])
			,'Person_Phone' => returnValidHTMLString($response[0]['Person_Phone'])
			,'PAddress_Name' => returnValidHTMLString($response[0]['PAddress_Name'])
			,'UAddress_Name' => returnValidHTMLString($response[0]['UAddress_Name'])
			,'PayType_Name' => returnValidHTMLString($response[0]['PayType_Name'])
			,'SocStatus_Name' => returnValidHTMLString($response[0]['SocStatus_Name'])
			,'InvalidType_Name' => returnValidHTMLString($invalid_type_name)
			,'PrehospOrg_Name' => returnValidHTMLString($response[0]['PrehospOrg_Name'])
			,'EvnDirection_Num' => returnValidHTMLString($response[0]['EvnDirection_Num'])
			,'EvnDirection_SetDT' => returnValidHTMLString($response[0]['EvnDirection_SetDT'])
			,'PrehospArrive_Name' => returnValidHTMLString($response[0]['PrehospArrive_Name'])
			,'EvnPS_CodeConv' => returnValidHTMLString($response[0]['EvnPS_CodeConv'])
			,'EvnPS_NumConv' => returnValidHTMLString($response[0]['EvnPS_NumConv'])
			,'PersonCard_Code' => returnValidHTMLString($response[0]['PersonCard_Code'])
			,'Lpu_Name' => returnValidHTMLString($response[0]['Lpu_Name'])
			,'PrehospDiag_Name' => returnValidHTMLString($response[0]['PrehospDiag_Name'])
			,'AdmitDiag_Name' => returnValidHTMLString($response[0]['AdmitDiag_Name'])
			,'PrehospToxic_Name' => returnValidHTMLString($response[0]['PrehospToxic_Name'])
			,'LpuSectionTransType_Name' => returnValidHTMLString($response[0]['LpuSectionTransType_Name'])
			,'PrehospType_Name' => returnValidHTMLString($response[0]['PrehospType_Name'])
			,'PregospType_sCode' => returnValidHTMLString($response[0]['PregospType_sCode'])
			,'IsFirst' => returnValidHTMLString($response[0]['IsFirst'])
			,'EvnPS_HospCount' => returnValidHTMLString($response[0]['EvnPS_HospCount'])
			,'EvnPS_TimeDesease' => returnValidHTMLString($response[0]['EvnPS_TimeDesease'])
			,'EvnPS_TimeDeseaseType' => returnValidHTMLString($response[0]['EvnPS_TimeDeseaseType'])
			,'PrehospTrauma_Name' => returnValidHTMLString($response[0]['PrehospTrauma_Name'])
			,'EvnPS_setDate' => returnValidHTMLString($response[0]['EvnPS_setDate'])
			,'EvnPS_setTime' => returnValidHTMLString($response[0]['EvnPS_setTime'])
			,'LpuSectionFirst_Name' => returnValidHTMLString($response[0]['LpuSectionFirst_Name'])
			,'EvnSectionFirst_setDate' => returnValidHTMLString($response[0]['EvnSectionFirst_setDate'])
			,'EvnSectionFirst_setTime' => returnValidHTMLString($response[0]['EvnSectionFirst_setTime'])
			,'EvnPS_disDate' => returnValidHTMLString($response[0]['EvnPS_disDate'])
			,'EvnPS_disTime' => returnValidHTMLString($response[0]['EvnPS_disTime'])
			,'EvnPS_KoikoDni' => returnValidHTMLString($response[0]['EvnPS_KoikoDni'])
			,'LeaveType_Name' => returnValidHTMLString($response[0]['LeaveType_Name'])
			,'LeaveType_sCode' => returnValidHTMLString($response[0]['LeaveType_sCode'])
			,'ResultDesease_Name' => returnValidHTMLString($response[0]['ResultDesease_Name'])
			,'ResultDesease_sCode' => returnValidHTMLString($response[0]['ResultDesease_sCode'])
			,'EvnStick_setDate' => returnValidHTMLString($response[0]['EvnStick_setDate'])
			,'EvnStick_disDate' => returnValidHTMLString($response[0]['EvnStick_disDate'])
			,'PersonCare_Age' => returnValidHTMLString($response[0]['PersonCare_Age'])
			,'PersonCare_SexName' => returnValidHTMLString($response[0]['PersonCare_SexName'])
			,'EvnSectionData' => $evn_section_data
			,'EvnUslugaOperData' => $evn_usluga_oper_data
			,'PrivilegeType' => $privilege_type
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
			,'MedPersonal_TabCode' => returnValidHTMLString($response[0]['MedPersonal_TabCode'])
			,'IsRW' => returnValidHTMLString($response[0]['IsRW'])
			,'IsAIDS' => returnValidHTMLString($response[0]['IsAIDS'])
		);

		$html = $this->parser->parse($template, $print_data, !empty($data['returnString']));
		if (!empty($data['returnString'])) {
			return array('html' => $html);
		} else {
			return $html;
		}
	}
}