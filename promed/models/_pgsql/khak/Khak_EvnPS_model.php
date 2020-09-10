<?php defined('BASEPATH') or die ('No direct script access allowed');

require_once(APPPATH . 'models/_pgsql/EvnPS_model.php');

class Khak_EvnPS_model extends EvnPS_model
{
	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Выгрузка данных для ТФОМС и СМО
	 */
	function exportHospDataForTfomsToXml($data)
	{
		$params = array(
			'Date' => $data['Date'],
			'Lpu_id' => $data['Lpu_id'],
			'startTime' => '20:00',
			'finalTime' => '19:59',
		);

		if ($data['ARMType'] == 'superadmin' || $data['ARMType'] == 'tfoms') {
			$params['Lpu_id'] = null;
		}

		$query = "
			select
				lp.Lpu_id as \"Lpu_id\",
				lp.Lpu_f003mcod as \"fcode\",
				case when exists(
					select * from v_LpuUnit t
					where t.Lpu_id = lp.Lpu_id and t.LpuUnitType_SysNick like 'polka'
				) then 1 else 0 end as \"hasPolka\",
				case when exists(
					select * from v_LpuUnit t
					where t.Lpu_id = lp.Lpu_id 
						and t.LpuUnitType_SysNick like 'stac'
						and cast(:Date as date) BETWEEN LpuUnit_begDate AND COALESCE(LpuUnit_endDate, :Date)
				) then 1 else 0 end as \"hasStac\"
			from v_Lpu lp
			where
			lp.Lpu_f003mcod is not null
			and lp.Lpu_f003mcod <> '0'
			and (:Lpu_id is null or lp.Lpu_id = :Lpu_id)
		";
		//echo getDebugSQL($query, $params);die();
		//Логгирование для решения задачи 174066
		$this->load->library('textlog', array('file' => '174066_' . time() . '.log'));
		$this->textlog->add(getDebugSQL($query, $params));

		$res = $this->db->query($query, $params);

		//Логгирование для решения задачи 174066
		$this->textlog->add($res);

		if (is_object($res)) {
			$lpu_arr = $res->result('array');
		} else {
			return false;
		}

		$query = "
			SELECT
				Select
						COALESCE(cast(lp.Lpu_f003mcod as varchar)
							|| cast(EXTRACT(YEAR FROM EvnDirection_setDT) as varchar)
							|| RIGHT(cast(EvnDirection_Num as varchar), 6), '') as NOM_NAP,		--T(16) Номер направления (MCOD+NP)
						to_char(EvnDirection_setDT, 'yyyy-mm-dd') as DTA_NAP,
						case when dt.DirType_Code = 5 then 2 else COALESCE(dt.DirType_Code, 1) end as FRM_MP, -- Int - Форма оказания медицинской помощи
						COALESCE(lp.Lpu_f003mcod, '') as MCOD_NAP, -- T(6)-- numeric(6, 0) - МО, направившая на госпитализацию	(реестровый номер, F003)
						COALESCE(lp1.Lpu_f003mcod, '') as MCOD_STC, -- T(6)-- numeric(6, 0) - МО, куда направлен пациент	(реестровый номер, F003)
						pt.PolisType_CodeF008 as VPOLIS,-- numeric(1, 0) , -- Тип документа, подтверждающего факт страхования по ОМС (F008)
						COALESCE(case when pt.PolisType_CodeF008 in (1, 2) then po.Polis_Ser else null end, '') as SPOLIS, -- Varchar(10) - Серия документа, подтверждающего факт страхования по ОМС
						case when pt.PolisType_CodeF008 = 3 then pe.Person_EdNum else po.Polis_Num end as NPOLIS, -- Numeric(20) - Номер документа, подтверждающего факт страхования по ОМС
						COALESCE(smo.Orgsmo_f002smocod, '') as SMO_CODE, -- numeric(6, 0) - СМО (реестровый номер, F002)
						COALESCE(org.Org_OKATO, '') as ST_OKATO, --Т(5) ОКАТО территории страхования
						COALESCE(pe.Person_SurName, '') as FAM, --varchar(30) – Фамилия
						COALESCE(pe.Person_FirName, '') as IM, --varchar(30) – Имя
						COALESCE(pe.Person_SecName, '') as OT, --varchar(30) – Отчество
						case when pe.Sex_id = 3 then 1 else pe.Sex_id end as W, --numeric(1) – пол (1 - муж, 2 - жен)
						cast(pe.Person_BirthDay as date) as DR, --date – дата рождения
						case when ps.Person_Phone is not null and ps.Person_Phone <> '' then ps.Person_Phone else 'не указан' end as TLF, --Varchar(100) - Контактная информация
						COALESCE(di.Diag_Code, '') as DS, --Char(4) - Код диагноза по МКБ
						0 as KOD_PFK, --numeric(4, 0) - Профиль койки
						COALESCE(lspf.LpuSectionProfile_Code, '') as KOD_PFO, --numeric(4, 0) - Код отделения (профиль)
						case
							when LENGTH(mp.Person_Snils) = 14 then REPLACE(mp.Person_Snils, ' ', '-')
							when LENGTH(mp.Person_Snils) = 11 then LEFT(mp.Person_Snils, 3) || '-' || SUBSTRING(mp.Person_Snils, 4, 3) || '-' || SUBSTRING(mp.Person_Snils, 7, 3) || '-' || RIGHT(mp.Person_Snils, 2)
							else ''
						end as KOD_DCT, --Varchar(14) - Снилс медицинского работника, направившего больного
						case
							when tt.TimetableStac_setDate is not null then COALESCE(cast(to_char(tt.TimetableStac_setDate, 'yyyy-mm-dd') as date), '')
							when ed.DirType_id = 5 then '' -- На госпитализацию экстренную
							when ed.EvnQueue_id is not null then 'в очередь'
							else NULL
						end as DTA_PLN -- Плановая дата госпитализации
					from dbo.v_EvnDirection ed
						left join dbo.v_DirType dt on dt.DirType_id = ed.DirType_id
						inner join v_Lpu lp on lp.Lpu_id = ed.Lpu_id
						left join lateral (
							select Person_Snils
							from v_MedPersonal
							where MedPersonal_id = ed.MedPersonal_id
							limit 1
						) mp on true
						left join dbo.v_TimetableStac_lite tt on tt.EvnDirection_id = ed.EvnDirection_id
						inner join dbo.v_Diag di on di.Diag_id = ed.Diag_id
						inner join v_lpu lp1 on lp1.Lpu_id = ed.Lpu_did
						left join v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = ed.LpuSectionProfile_id
						left join fed.v_LpuSectionProfile lspf on lspf.LpuSectionProfile_id = lsp.LpuSectionProfile_fedid
						left join v_PersonState ps on ps.Person_id = ed.Person_id
						left join PayType PTYPE on PTYPE.PayType_id = ED.PayType_id
						inner join dbo.v_Person_all pe on pe.PersonEvn_id = ed.PersonEvn_id and pe.Server_id = ed.Server_id
						inner join dbo.v_Polis po on po.Polis_id = pe.Polis_id
						inner join dbo.PolisType pt on pt.PolisType_id = po.PolisType_id
							and pt.PolisType_CodeF008 is not null
						left join dbo.v_OrgSMO smo on smo.OrgSMO_id = po.OrgSmo_id
						left join dbo.v_Org org on org.Org_id = smo.Org_id
					where (1 = 1)
						and ed.DirType_id in (1, 5)
						and (:Lpu_id is null or ed.Lpu_id = :Lpu_id)
						--and ed.EvnDirection_setDT> = @startTime
						--and ed.EvnDirection_setDT <= @finalTime
						and PTYPE.PayType_Code != 2			-- исключаем направления с типом оплаты ДМС
				), '') as \"N1\",	--GetReferToHosp
				COALESCE((
					select
						COALESCE(cast(case when ed.EvnDirection_id is not null then lp1.Lpu_f003mcod else ld.Lpu_f003mcod end as varchar)
							|| cast(EXTRACT(YEAR FROM case when ed.EvnDirection_id is not null then ed.EvnDirection_setDT else eps.EvnDirection_setDT end) as varchar)
							|| RIGHT(cast(case when ed.EvnDirection_id is not null then ed.EvnDirection_Num else eps.EvnDirection_Num end as varchar), 6)
						, '') as NOM_NAP, --T(16) Номер направления (MCOD+NP), --Int
						COALESCE(to_char(case when ed.EvnDirection_id is not null then ed.EvnDirection_setDT else eps.EvnDirection_setDT end, 'yyyy-mm-dd'), '') as DTA_NAP,
						case when prt.PrehospType_Code in (2, 3) then 2 else COALESCE(prt.PrehospType_Code, 1) end as FRM_MP, -- Int - Форма оказания медицинской помощи
						COALESCE(lp.Lpu_f003mcod, '') as MCOD_STC, -- T(6)-- numeric(6, 0) - МО госпитализации	(реестровый номер, F003)
						COALESCE(case when ed.EvnDirection_id is not null then lp1.Lpu_f003mcod else ld.Lpu_f003mcod end, '') as MCOD_NAP, -- T(6)-- numeric(6, 0) - Код подразделения МО, создавшей направление	(реестровый номер, F003)
						to_char(es.EvnSection_setDT, 'yyyy-mm-dd') as DTA_FKT, --Дата фактической госпитализации
						replace(to_char(es.EvnSection_setDT, 'hh24:mi'), ':', '-') as TIM_FKT, --Время фактической госпитализации
						pt.PolisType_CodeF008 as VPOLIS,-- numeric(1, 0) , -- Тип документа, подтверждающего факт страхования по ОМС (F008)
						COALESCE(case when pt.PolisType_CodeF008 in (1, 2) then po.Polis_Ser else Null end, '') as SPOLIS, -- Varchar(10) - Серия документа, подтверждающего факт страхования по ОМС
						case when pt.PolisType_CodeF008 = 3 then pe.Person_EdNum else po.Polis_Num end as NPOLIS, -- Numeric(20) - Номер документа, подтверждающего факт страхования по ОМС
						COALESCE(pe.Person_SurName, '') as FAM, --varchar(30) – Фамилия
						COALESCE(pe.Person_FirName, '') as IM, --varchar(30) – Имя
						COALESCE(pe.Person_SecName, '') as OT, --varchar(30) – Отчество
						case when pe.Sex_id = 3 then 1 else pe.Sex_id end as W, --numeric(1) – пол (1 - муж, 2 - жен)
						cast(pe.Person_BirthDay as date) as DR, --date – дата рождения
						COALESCE(lsb.LpuSectionBedProfile_Code, '0') as KOD_PFK, --numeric(4, 0) - Профиль койки
						COALESCE(lspf.LpuSectionProfile_Code, '0') as KOD_PFO, --numeric(4, 0) - Код отделения (профиль)
						COALESCE(eps.EvnPS_NumCard, '') as NHISTORY, --Номер карты стационарного больного
						COALESCE(di.Diag_Code, '') as DS --Char(4) - Диагноз приемного отделения
					from v_EvnSection es
						inner join dbo.v_EvnPS eps on eps.EvnPS_id = es.EvnSection_pid
						left join dbo.v_EvnDirection ed on ed.EvnDirection_id = eps.EvnDirection_id
						left join dbo.v_PrehospType prt on eps.PrehospType_id = prt.PrehospType_id
						inner join v_Lpu lp  on lp.Lpu_id = eps.Lpu_id
						inner join dbo.v_LpuSection ls  on ls.LpuSection_id = es.LpuSection_id
						left join dbo.v_LpuSectionBedProfile lsb on lsb.LpuSectionBedProfile_id = es.LpuSectionBedProfile_id
						left join v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
						left join fed.v_LpuSectionProfile lspf  on lspf.LpuSectionProfile_id = lsp.LpuSectionProfile_fedid
						left join v_Lpu_all ld  on ld.Org_id = eps.Org_did
						left join v_Lpu lp1 on lp1.Lpu_id = ed.Lpu_id
						left join dbo.Diag di on di.Diag_id = eps.Diag_pid
						inner join dbo.v_Person_all pe on pe.PersonEvn_id = es.PersonEvn_id
							and pe.Server_id = es.Server_id
						inner join dbo.v_Polis po on po.Polis_id = pe.Polis_id
						inner join dbo.PolisType pt on pt.PolisType_id = po.PolisType_id
							and pt.PolisType_CodeF008 is not null
					where es.EvnSection_Index = 0
						and (:Lpu_id is null or eps.Lpu_id = :Lpu_id)
						--and es.EvnSection_setDT >= @startTime
						--and es.EvnSection_setDT <= @finalTime
						and eps.PrehospWaifRefuseCause_id is null
						and prt.PrehospType_Code = 1
				), '') as \"N2\",	--GetHospPlan
				COALESCE ((
					select
						COALESCE(lp.Lpu_f003mcod, '') as MCOD_STC, -- T(6)-- numeric(6, 0) - МО госпитализации	(реестровый номер, F003)
						to_char(es.EvnSection_setDT, 'yyyy-mm-dd') as DTA_FKT, --Дата фактической госпитализации
						replace(to_char(es.EvnSection_setDT, 'hh24:mi'), ':', '-') as TIM_FKT, --Время фактической госпитализации
						pt.PolisType_CodeF008 as VPOLIS,-- numeric(1, 0) , -- Тип документа, подтверждающего факт страхования по ОМС (F008)
						COALESCE(case when pt.PolisType_CodeF008 in (1, 2) then po.Polis_Ser else null end, '') as SPOLIS, -- Varchar(10) - Серия документа, подтверждающего факт страхования по ОМС
						case when pt.PolisType_CodeF008 = 3 then pe.Person_EdNum else po.Polis_Num end as NPOLIS, -- Numeric(20) - Номер документа, подтверждающего факт страхования по ОМС
						COALESCE(smo.Orgsmo_f002smocod, '') as SMO_CODE, -- numeric(6, 0) - СМО (реестровый номер, F002)
						COALESCE(org.Org_OKATO, '') as ST_OKATO, --Т(5) ОКАТО территории страхования
						COALESCE(pe.Person_SurName, '') as FAM, --varchar(30) – Фамилия
						COALESCE(pe.Person_FirName, '') as IM, --varchar(30) – Имя
						COALESCE(pe.Person_SecName, '') as OT, --varchar(30) – Отчество
						case when pe.Sex_id = 3 then 1 else pe.Sex_id end as W, --numeric(1) – пол (1 - муж, 2 - жен)
						cast(pe.Person_BirthDay as date) as DR, --date – дата рождения
						COALESCE(lsb.LpuSectionBedProfile_Code, '0') as KOD_PFK, --numeric(4, 0) - Профиль койки
						COALESCE(lspf.LpuSectionProfile_Code, '') as KOD_PFO, --numeric(4, 0) - Код отделения (профиль)
						COALESCE(eps.EvnPS_NumCard, '') as NHISTORY, --Номер карты стационарного больного
						COALESCE(di.Diag_Code, '') as DS --Char(4) - Диагноз приемного отделения
					from v_EvnSection es
						inner join dbo.v_EvnPS eps on eps.EvnPS_id = es.EvnSection_pid
						left join dbo.v_EvnDirection ed on ed.EvnDirection_id = eps.EvnDirection_id
						left join dbo.v_PrehospType prt on eps.PrehospType_id = prt.PrehospType_id
						inner join v_Lpu lp on lp.Lpu_id = eps.Lpu_id
						inner join dbo.v_LpuSection ls on ls.LpuSection_id = es.LpuSection_id
						left join dbo.v_LpuSectionBedProfile lsb on lsb.LpuSectionBedProfile_id = es.LpuSectionBedProfile_id
						left join v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
						left join fed.v_LpuSectionProfile lspf on lspf.LpuSectionProfile_id = lsp.LpuSectionProfile_fedid
						left join dbo.Diag di  on di.Diag_id = eps.Diag_pid
						inner join dbo.v_Person_all pe on pe.PersonEvn_id = es.PersonEvn_id
							and pe.Server_id = es.Server_id
						inner join dbo.v_Polis po on po.Polis_id = pe.Polis_id
						inner join dbo.PolisType pt on pt.PolisType_id = po.PolisType_id
							and pt.PolisType_CodeF008 is not null
						left join dbo.v_OrgSMO smo on smo.OrgSMO_id = po.OrgSmo_id
						left join dbo.v_Org org on org.Org_id = smo.Org_id
					where es.EvnSection_Index = 0
						and (:Lpu_id is null or eps.Lpu_id = :Lpu_id)
						--and es.EvnSection_setDT >= @startTime
						--and es.EvnSection_setDT <= @finalTime
						and eps.PrehospWaifRefuseCause_id is null
						and prt.PrehospType_Code in (2, 3)
				), '') as \"N3\",	--GetHospEmerg,
				COALESCE((
					select
						t.NOM_NAP,
						t.DTA_NAP,
						t.IST_ANL,
						t.ACOD,
						t.PR_ANL
					from (
						select
							COALESCE(cast(lp.Lpu_f003mcod as varchar)
								|| cast(EXTRACT(YEAR FROM ed.EvnDirection_setDT) as varchar)
								|| RIGHT(cast(ed.EvnDirection_Num as varchar), 6)
							, '') as NOM_NAP,	--T(16) Номер направления (MCOD+NP)
							to_char(ed.EvnDirection_setDT, 'yyyy-mm-dd') as DTA_NAP, --Дата направления	= дата создания направления
							COALESCE(case
								when puc.Lpu_id = ed.Lpu_did then '2'
								when puc.Lpu_id = ed.Lpu_id then '3'
							end, '') as IST_ANL, -- Источник аннулирования
							COALESCE(smolp.Lpu_f003mcod, '') as ACOD, -- numeric(6, 0) МО, отменившая направление (реестровый номер, F003)
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
						from dbo.v_EvnDirection ed
							left join lateral (
								select EvnStatusCause_id
								from v_EvnStatusHistory 
								where Evn_id = ed.EvnDirection_id
									and EvnStatus_id = ed.EvnStatus_id
								limit 1
							) esh on true
							left join lateral (
								select LUT.LpuUnitType_SysNick as groupType
								from 
									v_MedStaffFact MSF
									left join v_LpuSection LS on LS.LpuSection_id = MSF.LpuSection_id
									left join LpuUnit LU on LU.LpuUnit_id = LS.LpuUnit_id
									left join LpuUnitType LUT on LUT.LpuUnitType_id = LU.LpuUnitType_id
								where MSF.MedStaffFact_id = ed.MedStaffFact_fid
							) lUnit on true
							inner join dbo.EvnStatusCause esc on esc.EvnStatusCause_id = esh.EvnStatusCause_id
							inner join dbo.pmUserCache puc on ed.pmUser_failID = puc.PMUser_id
							inner join v_Lpu smolp on smolp.Lpu_id = puc.Lpu_id
							inner join v_Lpu lp on lp.Lpu_id = ed.Lpu_id
							inner join dbo.v_Person_all pe on pe.PersonEvn_id = ed.PersonEvn_id and pe.Server_id = ed.Server_id
							inner join dbo.v_Polis po on po.Polis_id = pe.Polis_id
							inner join dbo.PolisType pt on pt.PolisType_id = po.PolisType_id and pt.PolisType_CodeF008 is not null -- без полисов ДМС
						where ed.DirType_id in (1, 5)
							and ed.EvnStatus_id in (12, 13)
							and (:Lpu_id is null or smolp.Lpu_id = :Lpu_id)
							--and ed.EvnDirection_statusDate >= @startTime
							--and ed.EvnDirection_statusDate <= @finalTime
							
						union all

						select
							cast(lp.Lpu_f003mcod as varchar)
								|| cast(EXTRACT(YEAR FROM ed.EvnDirection_setDT) as varchar)
								|| RIGHT(cast(ed.EvnDirection_Num as varchar), 6)
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
						from dbo.v_EvnPS eps
							inner join dbo.v_EvnDirection ed on ed.EvnDirection_id = eps.EvnDirection_id
							left join lateral (
								select LUT.LpuUnitType_SysNick as groupType
								from 
									v_MedStaffFact MSF
									left join v_LpuSection  LS on LS.LpuSection_id = MSF.LpuSection_id
									left join LpuUnit LU on LU.LpuUnit_id = LS.LpuUnit_id
									left join LpuUnitType LUT on LUT.LpuUnitType_id = LU.LpuUnitType_id
								where MSF.MedStaffFact_id = ed.MedStaffFact_fid
							) lUnit on true
							inner join dbo.v_PrehospWaifRefuseCause pwrc on pwrc.PrehospWaifRefuseCause_id = eps.PrehospWaifRefuseCause_id
							inner join v_LpuSection ls on ls.LpuSection_id = eps.LpuSection_pid
							inner join v_Lpu smolp on smolp.Lpu_id = ls.Lpu_id
							inner join v_Lpu lp on lp.Lpu_id = ls.Lpu_id
							inner join dbo.v_Person_all pe on pe.PersonEvn_id = ed.PersonEvn_id and pe.Server_id = ed.Server_id
							inner join dbo.v_Polis po on po.Polis_id = pe.Polis_id
							inner join dbo.PolisType pt on pt.PolisType_id = po.PolisType_id and pt.PolisType_CodeF008 is not null -- без полисов ДМС
						where ed.DirType_id in (1, 5)
							and ed.EvnStatus_id in (12, 13)
							and (:Lpu_id is null or eps.Lpu_id = :Lpu_id)
							--and eps.EvnPS_OutcomeDT >= @startTime
							--and eps.EvnPS_OutcomeDT <= @finalTime
					) as t
				), '') as \"N4\",	--GetCancelReferToHosp,
				COALESCE((
					select
						COALESCE(cast(case when ed.EvnDirection_id is not null then lp1.Lpu_f003mcod else ld.Lpu_f003mcod end as varchar)
							|| cast(EXTRACT(YEAR FROM case when ed.EvnDirection_id is not null then ed.EvnDirection_setDT else eps.EvnDirection_setDT end) as varchar)
							|| RIGHT(cast(case when ed.EvnDirection_id is not null then ed.EvnDirection_Num else eps.EvnDirection_Num end as varchar), 6)
						, '') as NOM_NAP,	--T(16) Номер направления (MCOD+NP), --Int
						COALESCE(to_char(case when ed.EvnDirection_id is not null then ed.EvnDirection_setDT else eps.EvnDirection_setDT end, 'yyyy-mm-dd'), '') as DTA_NAP,
						case
							when prt.PrehospType_Code in (2, 3) then 3
							else COALESCE(prt.PrehospType_Code, 1)
						end as FRM_MP, -- Int - Форма оказания медицинской помощи
						COALESCE(lp.Lpu_f003mcod, '') as MCOD_STC, -- T(6)-- numeric(6, 0) - МО госпитализации	(реестровый номер, F003)
						to_char(es.EvnSection_setDT, 'yyyy-mm-dd') as DTA_FKT, -- значение поля «Дата поступления» из первого движения
						to_char(eps.EvnPS_disDT, 'yyyy-mm-dd') as DTA_END, -- datetime - Дата выбытия
						COALESCE(pe.Person_SurName, '') as FAM, --varchar(30) – Фамилия
						COALESCE(pe.Person_FirName, '') as IM, --varchar(30) – Имя
						COALESCE(pe.Person_SecName, '') as OT, --varchar(30) – Отчество
						case
							when pe.Sex_id = 3 then 1
							else pe.Sex_id
						end as W, --numeric(1) – пол (1 - муж, 2 - жен)
						cast(pe.Person_BirthDay as date) as DR, --date – дата рождения
						COALESCE(lsb.LpuSectionBedProfile_Code, '0') as KOD_PFK, --numeric(4, 0) - Профиль койки
						COALESCE(lspf.LpuSectionProfile_Code, '') as KOD_PFO, --numeric(4, 0) - Код отделения (профиль)
						COALESCE(eps.EvnPS_NumCard, '') as NHISTORY --Номер карты стационарного больного
					from
						dbo.v_EvnPS eps
						inner join v_EvnSection es on es.EvnSection_pid = eps.EvnPS_id
							and es.EvnSection_Index = 0
						left join dbo.v_EvnDirection ed on ed.EvnDirection_id = eps.EvnDirection_id
						left join dbo.v_PrehospType prt on prt.PrehospType_id = case when ed.EvnDirection_id is not null then ed.PrehospType_did else eps.PrehospType_id end
						inner join v_Lpu lp on lp.Lpu_id = eps.Lpu_id
						inner join dbo.v_LpuSection ls  on ls.LpuSection_id = eps.LpuSection_id
						left join dbo.v_LpuSectionBedProfile lsb on lsb.LpuSectionBedProfile_id = es.LpuSectionBedProfile_id
						left join v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
						left join fed.v_LpuSectionProfile lspf on lspf.LpuSectionProfile_id = lsp.LpuSectionProfile_fedid
						left join v_Lpu_all ld  on ld.Org_id = eps.Org_did
						left join v_Lpu lp1 on lp1.Lpu_id = ed.Lpu_id
						inner join dbo.v_Person_all pe on pe.PersonEvn_id = eps.PersonEvn_id
							and pe.Server_id = eps.Server_id
						inner join dbo.v_LeaveType lt on lt.LeaveType_id = eps.LeaveType_id
						inner join dbo.v_Polis po on po.Polis_id = pe.Polis_id
						inner join dbo.PolisType pt on pt.PolisType_id = po.PolisType_id
							and pt.PolisType_CodeF008 is not null
					where (:Lpu_id is null or eps.Lpu_id = :Lpu_id)
						--and eps.EvnPS_disDT >= @startTime
						--and eps.EvnPS_disDT <= @finalTime
						and lt.LeaveType_Code IN (1, 4)
				), '') as \"N5\",	--GetExitHosp,
				COALESCE((
					select t.*
					from rpt19.Han_hosp_enable(:Lpu_id, null, :Date) t
				), '') as \"N6\"	--GetCouchInfo
		";

		$hosp_data_xml_arr = array();
		foreach ($lpu_arr as $lpu) {
			$params['Lpu_id'] = $lpu['Lpu_id'];
			$params['hasStac'] = $lpu['hasStac'];
			$params['hasPolka'] = $lpu['hasPolka'];
			//echo getDebugSQL($query, $params);die;
			//Логгирование для решения задачи 174066
			$this->textlog->add(getDebugSQL($query, $params));

			$hosp_data_xml_arr[$lpu['fcode']] = $this->getFirstRowFromQuery($query, $params);

			//Логгирование для решения задачи 174066
			$this->textlog->add($hosp_data_xml_arr[$lpu['fcode']]);
		}
		//echo json_encode($hosp_data_xml_arr);die;
		//Логгирование для решения задачи 174066
		$this->textlog->add($hosp_data_xml_arr);

		return $hosp_data_xml_arr;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function getEvnPSFields($data)
	{
		$query = "
			select
				 COALESCE(EPS.EvnPS_NumCard, '') as \"EvnPS_NumCard\"
				,RTRIM(COALESCE(Lpu.Lpu_Name, '')) as \"Lpu_Name\"
				,RTRIM(COALESCE(PLST.PolisType_Name, '')) as \"PolisType_Name\"
				,CASE WHEN PLST.PolisType_Code = 4 then '' ELSE RTRIM(COALESCE(PLS.Polis_Ser, '')) END as \"Polis_Ser\"
				,CASE WHEN PLST.PolisType_Code = 4 then COALESCE(RTRIM(PS.Person_EdNum), '') ELSE RTRIM(COALESCE(PLS.Polis_Num, '')) END AS \"Polis_Num\"
				--,RTRIM(COALESCE(PLS.Polis_Num, '')) as \"Polis_Num\"
				--,RTRIM(COALESCE(PLS.Polis_Ser, '')) as \"Polis_Ser\"
				,RTRIM(COALESCE(OST.OMSSprTerr_Code, '')) as \"OMSSprTerr_Code\"
				,RTRIM(COALESCE(OrgSmo.OrgSMO_Name, '')) as \"OrgSmo_Name\"
				,RTRIM(COALESCE(OS.Org_OKATO, '')) as \"OrgSmo_OKATO\"
				,PS.Person_id as \"Person_id\"
				,RTRIM(RTRIM(COALESCE(PS.Person_Surname, '')) || ' ' || RTRIM(COALESCE(PS.Person_Firname, '')) || ' ' || RTRIM(COALESCE(PS.Person_Secname, ''))) as \"Person_Fio\"
				,RTRIM(COALESCE(SX.Sex_Name, '')) as \"Sex_Name\"
				,RTRIM(COALESCE(SX.Sex_Code, '')) as \"Sex_Code\"
				,to_char(PS.Person_Birthday, 'dd.mm.yyyy') as \"Person_Birthday\"
				,dbo.Age2(PS.Person_Birthday, EPS.EvnPS_setDate) as \"Person_Age\"
				,RTRIM(COALESCE(D.Document_Num, '')) as \"Document_Num\"
				,RTRIM(COALESCE(D.Document_Ser, '')) as \"Document_Ser\"
				,RTRIM(COALESCE(DT.DocumentType_Name, '')) as \"DocumentType_Name\"
				,RTRIM(COALESCE(KLAT.KLAreaType_Name, '')) as \"KLAreaType_Name\"
				,RTRIM(COALESCE(KLAT.KLAreaType_id, '')) as \"KLAreaType_id\"
				,RTRIM(COALESCE(PS.Person_Phone, '')) as \"Person_Phone\"
				,RTRIM(COALESCE(PAddr.Address_Address, '')) as \"PAddress_Name\"
				,RTRIM(COALESCE(UAddr.Address_Address, '')) as \"UAddress_Name\"
				,RTRIM(COALESCE(MSF.Person_Fio,'')) as \"FIO_Priem\"
				,RTRIM(COALESCE(PT.PayType_Name, '')) as \"PayType_Name\"
				,RTRIM(COALESCE(PT.PayType_Code, '')) as \"PayType_Code\"
				,RTRIM(COALESCE(SS.SocStatus_Name, '')) as \"SocStatus_Name\"
				,RTRIM(COALESCE(SS.SocStatus_Code, '')) as \"SocStatus_Code\"
				,RTRIM(COALESCE(SS.SocStatus_SysNick, '')) as \"SocStatus_SysNick\"
				,IT.PrivilegeType_id as \"PrivilegeType_id\"
				,/*RTRIM(COALESCE(IT.PrivilegeType_Code, '')) + ' ' + */RTRIM(COALESCE(IT.PrivilegeType_Name, '')) as \"PrivilegeType_Name\"
				,COALESCE(IT2.PrivilegeType_Code, '') as \"PrivilegeType_Code\"
				,RTRIM(COALESCE(PersPriv.PrivilegeType_Name, '')) as \"PersPriv\"
				,CASE
					WHEN street.KLStreet_id is not null and street.KLAdr_Ocatd is not null THEN street.KLAdr_Ocatd
					WHEN town.KLArea_id is not null and town.KLAdr_Ocatd is not null THEN town.KLAdr_Ocatd
					WHEN city.KLArea_id is not null and city.KLAdr_Ocatd is not null THEN city.KLAdr_Ocatd
					WHEN srgn.KLArea_id is not null and srgn.KLAdr_Ocatd is not null THEN srgn.KLAdr_Ocatd
					WHEN rgn.KLArea_id is not null and rgn.KLAdr_Ocatd is not null THEN rgn.KLAdr_Ocatd
					WHEN country.KLArea_id is not null and country.KLAdr_Ocatd is not null THEN country.KLAdr_Ocatd
					ELSE ''
				END as \"Person_OKATO\"
				,RTRIM(COALESCE(PHLS.LpuSection_Name, PreHospLpu.Lpu_Name, PHOM.OrgMilitary_Name, PHO.Org_Name,PD.PrehospDirect_Name, '')) as \"PrehospOrg_Name\"
				,RTRIM(COALESCE(PA.PrehospArrive_Name, '')) as \"PrehospArrive_Name\"
				,RTRIM(COALESCE(DiagH.Diag_Name, '')) as \"PrehospDiag_Name\"
				,RTRIM(COALESCE(DiagP.Diag_Name, '')) as \"AdmitDiag_Name\"
				,RTRIM(COALESCE(PHTX.PrehospToxic_Name, '')) as \"PrehospToxic_Name\"
				,RTRIM(COALESCE(PHTX.PrehospToxic_Code, '')) as \"PrehospToxic_Code\"
				,RTRIM(COALESCE(LSTT.LpuSectionTransType_Name, '')) as \"LpuSectionTransType_Name\"
				,RTRIM(COALESCE(LSTT.LpuSectionTransType_Code, '')) as \"LpuSectionTransType_Code\"
				,RTRIM(COALESCE(PHT.PrehospType_Name, '')) as \"PrehospType_Name\"
				,RTRIM(COALESCE(PHT.PrehospType_Code, '')) as \"PrehospType_Code\"
				,case when PHT.PrehospType_Code in (2,3) then 3 when PHT.PrehospType_Code = 1 then 4 end as \"PregospType_sCode\"
				,case when COALESCE(EPS.EvnPS_HospCount, 1) = 1 then 'первично' else 'повторно' end as \"EvnPS_HospCount\"
				,case when COALESCE(EPS.EvnPS_HospCount, 1) = 1 then 1 else 2 end as \"IsFirst\"
				,case when EPS.Okei_id = '100'
				  then
				    case
					when (EPS.EvnPS_TimeDesease <= 6) then 1
					when (EPS.EvnPS_TimeDesease > 24) then 3
					when EPS.EvnPS_TimeDesease  is not null then 2
				 	else null
				  end
				  else 3
				end as \"EvnPS_TimeDeseaseType\"
				/*,case
					when (EPS.EvnPS_TimeDesease <= 6) then 1
					when (EPS.EvnPS_TimeDesease > 24) then 3
					when EPS.EvnPS_TimeDesease  is not null then 2
				 	else null
				 end as EvnPS_TimeDeseaseType*/
				,EPS.EvnPS_TimeDesease as \"EvnPS_TimeDesease\"
				,EPS.EvnDirection_Num as \"EvnDirection_Num\"
				,EPS.EvnPS_CodeConv as \"EvnPS_CodeConv\"
				,EPS.EvnPS_NumConv as \"EvnPS_NumConv\"
				,to_char(EPS.EvnDirection_SetDT, 'dd.mm.yyyy') as \"EvnDirection_SetDT\"
				,RTRIM(PC.PersonCard_Code) as \"PersonCard_Code\"
				,RTRIM(COALESCE(PHTR.PrehospTrauma_Name, '')) as \"PrehospTrauma_Name\"
				,RTRIM(COALESCE(PHTR.PrehospTrauma_Code, '')) as \"PrehospTrauma_Code\"
				,to_char(EPS.EvnPS_setDate, 'dd.mm.yyyy') as \"EvnPS_setDate\"
				,EPS.EvnPS_setTime as \"EvnPS_setTime\"
				,RTRIM(COALESCE(LSFirst.LpuSection_Name, '')) as \"LpuSectionFirst_Name\"
				,RTRIM(COALESCE(LSBPFirst.LpuSectionBedProfile_Name, '')) as \"LpuSectionBedProfile_Name\"
				,RTRIM(COALESCE(MPFirst.MedPersonal_TabCode, '')) as \"MedPersonal_TabCode\"
				,RTRIM(COALESCE(MPFirst.MedPersonal_Code, '')) as \"MPFirst_Code\"
				,RTRIM(COALESCE(MPFirst.Person_Fio, '')) as \"MedPerson_FIO\"
				,RTRIM(COALESCE(OHMP.Person_Fio,'')) as \"OrgHead_FIO\"
				,RTRIM(COALESCE(OHMP.MedPersonal_TabCode,'')) as \"OrgHead_Code\"
				,to_char(ESFirst.EvnSection_setDT, 'dd.mm.yyyy') as \"EvnSectionFirst_setDate\"
				,ESFirst.EvnSection_setTime as \"EvnSectionFirst_setTime\"
				,to_char(EPS.EvnPS_disDate, 'dd.mm.yyyy') as \"EvnPS_disDate\"
				,EPS.EvnPS_disTime as \"EvnPS_disTime\"
				,LUTLast.LpuUnitType_SysNick as \"LpuUnitType_SysNick\"
				,case when LUTLast.LpuUnitType_SysNick = 'stac'
				  then
				    date_part('day', EPS.EvnPS_disDate - EPS.EvnPS_setDate) + abs(sign(date_part('day', EPS.EvnPS_disDate - EPS.EvnPS_setDate)) - 1)
				  else
				    (date_part('day', EPS.EvnPS_disDate - EPS.EvnPS_setDate) + 1)
				end as \"EvnPS_KoikoDni\"
				,RTRIM(COALESCE(LT.LeaveType_Name, '')) as \"LeaveType_Name\"
				,LT.LeaveType_Code as \"LeaveType_Code\"
				,LT.LeaveType_Code as \"LeaveType_sCode\"
				,RTRIM(COALESCE(RD.ResultDesease_Name, '')) as \"ResultDesease_Name\"
				,RD.ResultDesease_Code as \"ResultDesease_Code\"
				,RD.ResultDesease_Code as \"ResultDesease_sCode\"
				,case
					when LT.LeaveType_SysNick like 'die' then 6
					when RD.ResultDesease_SysNick in('zdorvosst','zdorchast','zdornar') then 1
					when RD.ResultDesease_SysNick in('rem','uluc','stabil', 'kompens', 'hron') then 2
					when RD.ResultDesease_SysNick in('noeffect') then 3
					when RD.ResultDesease_SysNick in('yatr','novzab','progress') then 4
					when RD.ResultDesease_SysNick like 'zdor' then 5
					else RD.ResultDesease_Code
				end as \"ResultDesease_aCode\"
				,to_char(EST.EvnStick_setDT, 'dd.mm.yyyy') as \"EvnStick_setDate\"
				,to_char(EST.EvnStick_disDT, 'dd.mm.yyyy') as \"EvnStick_disDate\"
				,ESTCP.Person_Age as \"PersonCare_Age\"
				,ESTCP.Sex_Name as \"PersonCare_SexName\"
				,ESTCP.Sex_id as \"PersonCare_SexId\"
				,DG.Diag_Code as \"LeaveDiag_Code\"
				,DG.Diag_Name as \"LeaveDiag_Name\"
				,DGA.Diag_Code as \"LeaveDiagAgg_Code\"
				,DGA.Diag_Name as \"LeaveDiagAgg_Name\"
				,DGS.Diag_Code as \"LeaveDiagSop_Code\"
				,DGS.Diag_Name as \"LeaveDiagSop_Name\"
				,PAD.Diag_Code as \"AnatomDiag_Code\"
				,PAD.Diag_Name as \"AnatomDiag_Name\"
				,PADA.Diag_Code as \"AnatomDiagAgg_Code\"
				,PADA.Diag_Name as \"AnatomDiagAgg_Name\"
				,PADS.Diag_Code as \"AnatomDiagSop_Code\"
				,PADS.Diag_Name as \"AnatomDiagSop_Name\"
				,case when EPS.EvnPS_IsDiagMismatch = 2 then 'Несовпадение диагноза; ' else null end as \"EvnPS_IsDiagMismatch\"
				,case when EPS.EvnPS_IsImperHosp = 2 then 'Несвоевременность госпитализации; ' else null end as \"EvnPS_IsImperHosp\"
				,case when EPS.EvnPS_IsShortVolume = 2 then 'Недост. объем клинико-диаг. обследования; ' else null end as \"EvnPS_IsShortVolume\"
				,case when EPS.EvnPS_IsWrongCure = 2 then 'Неправильная тактика лечения; ' else null end as \"EvnPS_IsWrongCure\"
				,EPS.EvnPS_IsDiagMismatch as \"EvnPS_IsDiagMismatch1\"
				,EPS.EvnPS_IsImperHosp as \"EvnPS_IsImperHosp1\"
				,EPS.EvnPS_IsShortVolume as \"EvnPS_IsShortVolume1\"
				,EPS.EvnPS_IsWrongCure as \"EvnPS_IsWrongCure1\"
				,BSS.BirthSpecStac_OutcomPeriod as \"BirthSpecStac_OutcomPeriod\"
				,BSS.BirthSpecStac_CountPregnancy as \"BirthSpecStac_CountPregnancy\"
				,LC.LeaveCause_Code as \"LeaveCause_Code\"
				,EPS.PrehospWaifRefuseCause_id as \"PrehospWaifRefuseCause_id\"
				,IsRW.YesNo_Name as \"IsRW\"
				,IsAIDS.YesNo_Name as \"IsAIDS\"
				,PEH.PersonEncrypHIV_Encryp as \"PersonEncrypHIV_Encryp\"
			from v_EvnPS EPS
				inner join v_Lpu Lpu on Lpu.Lpu_id = EPS.Lpu_id
				inner join v_PersonState PS on PS.Person_id = EPS.Person_id
				left join v_EvnSection ESLast on ESLast.EvnSection_pid = EPS.EvnPS_id
					and ESLast.EvnSection_Index = ESLast.EvnSection_Count - 1
				left join v_EvnSection ESFirst on ESFirst.EvnSection_pid = EPS.EvnPS_id
					and ESFirst.EvnSection_Index = 0

				left join v_LpuSection LSLast on LSLast.LpuSection_id = ESLast.LpuSection_id
				left join LpuUnit LULast on LULast.LpuUnit_id = LSLast.LpuUnit_id
				left join LpuUnitType LUTLast on LUTLast.LpuUnitType_id = LULast.LpuUnitType_id
				left join v_PrehospDirect PD on EPS.PrehospDirect_id=PD.PrehospDirect_id
				left join v_EvnLeave ELeave on ELeave.EvnLeave_pid = ESLast.EvnSection_id
				left join LeaveCause LC on LC.LeaveCause_id = ELeave.LeaveCause_id
				left join v_Polis PLS on PLS.Polis_id = PS.Polis_id
				left join v_OmsSprTerr OST on OST.OmsSprTerr_id = PLS.OmsSprTerr_id
				left join v_PolisType PLST on PLST.PolisType_id = PLS.PolisType_id
				left join v_OrgSmo OrgSmo on OrgSmo.OrgSmo_id = PLS.OrgSmo_id
				left join v_Org OS on OS.Org_id = OrgSmo.Org_id
				left join v_Address UAddr on UAddr.Address_id = PS.UAddress_id
				left join KLArea country on country.KLArea_id = UAddr.KLCountry_id
				left join KLArea rgn on rgn.KLArea_id = UAddr.KLRgn_id
				left join KLArea srgn on srgn.KLArea_id = UAddr.KLSubRgn_id
				left join KLArea city on city.KLArea_id = UAddr.KLCity_id
				left join KLArea town on town.KLArea_id = UAddr.KLSubRgn_id
				left join KLStreet street on street.KLStreet_id = UAddr.KLStreet_id
				left join v_Address PAddr on PAddr.Address_id = PS.PAddress_id
				left join v_KLAreaType KLAT on KLAT.KLAreaType_id = PAddr.KLAreaType_id
				left join v_Document D on D.Document_id = PS.Document_id
				left join v_DocumentType DT on DT.DocumentType_id = D.DocumentType_id
				left join v_Sex SX on SX.Sex_id = PS.Sex_id
				left join v_PayType PT on PT.PayType_id = EPS.PayType_id
				left join v_SocStatus SS on SS.SocStatus_id = PS.SocStatus_id
				left join lateral (
					select PrivilegeType_id,
								PrivilegeType_Code,
								PrivilegeType_Name
					from v_PersonPrivilege
					where PrivilegeType_Code in ('81', '82', '83') and Person_id = PS.Person_id
					order by PersonPrivilege_begDate desc
					limit 1
				) IT on true
				left join lateral (
					select PrivilegeType_id,
								PrivilegeType_Code,
								PrivilegeType_Name
					from v_PersonPrivilege
					where PrivilegeType_Code in ('11', '20', '91', '81', '82', '83', '84') and Person_id = PS.Person_id
					order by PersonPrivilege_begDate desc
					limit 1
				) IT2 on true
				left join lateral (
								select PP.PrivilegeType_id,
								PP.PrivilegeType_Code,
								PP.PrivilegeType_Name
					from v_PersonPrivilege PP
					where PP.Person_id = PS.Person_id
					--and PP.PersonPrivilege_begDate >= EPS.EvnPS_setDate
					and (PP.PersonPrivilege_endDate is null or PP.PersonPrivilege_endDate <= EPS.EvnPS_disDate)
					order by PP.PersonPrivilege_begDate desc
					limit 1
				) PersPriv on true
				left join lateral (
							select 	BSS.BirthSpecStac_OutcomPeriod,
									BSS.BirthSpecStac_CountPregnancy
							from v_BirthSpecStac BSS
							left join v_EvnSection ES on ES.EvnSection_id = BSS.EvnSection_id
							where ES.EvnSection_pid = EPS.EvnPS_id
				) BSS on true
				left join v_PersonCard PC on PC.Person_id = PS.Person_id
					and PC.PersonCard_begDate is not null
					and PC.PersonCard_begDate <= EPS.EvnPS_insDT
					and (PC.PersonCard_endDate is null or PC.PersonCard_endDate > EPS.EvnPS_insDT)
					and PC.Lpu_id = EPS.Lpu_id
				left join v_LpuSection PHLS on PHLS.LpuSection_id = EPS.LpuSection_did
				left join v_OrgHead OH on OH.LpuUnit_id = PHLS.LpuUnit_id and OH.OrgHeadPost_id=13
				left join v_MedPersonal OHMP on OHMP.Person_id = OH.Person_id
				left join v_Lpu PreHospLpu on PreHospLpu.Lpu_id = EPS.Lpu_did
				left join v_MedStaffFact MSF on MSF.MedStaffFact_id=EPS.MedStaffFact_pid
				left join v_OrgMilitary PHOM on PHOM.OrgMilitary_id = EPS.OrgMilitary_did
				left join v_Org PHO on PHO.Org_id = EPS.Org_did
				left join v_PrehospArrive PA on PA.PrehospArrive_id = EPS.PrehospArrive_id
				left join v_Diag DiagH on DiagH.Diag_id = EPS.Diag_did
				left join v_Diag DiagP on DiagP.Diag_id = EPS.Diag_pid
				left join v_PrehospToxic PHTX on PHTX.PrehospToxic_id = EPS.PrehospToxic_id
				left join v_LpuSectionTransType LSTT on LSTT.LpuSectionTransType_id = EPS.LpuSectionTransType_id
				left join v_PrehospType PHT on PHT.PrehospType_id = EPS.PrehospType_id
				left join v_PrehospTrauma PHTR on PHTR.PrehospTrauma_id = EPS.PrehospTrauma_id
				left join v_MedPersonal MPFirst on EPS.MedPersonal_pid = MPFirst.MedPersonal_id
				left join v_LpuSection LSFirst on LSFirst.LpuSection_id = ESFirst.LpuSection_id
				left join v_LpuSectionBedProfile LSBPFirst on LSBPFirst.LpuSectionBedProfile_id = LSFirst.LpuSectionBedProfile_id
				left join v_LeaveType LT on LT.LeaveType_id = EPS.LeaveType_id
				left join v_EvnLeave EL on EL.EvnLeave_pid = ESLast.EvnSection_id
				left join v_EvnDie ED on ED.EvnDie_pid = ESLast.EvnSection_id
				left join v_EvnOtherLpu EOL on EOL.EvnOtherLpu_pid = ESLast.EvnSection_id
				left join v_EvnOtherStac EOST on EOST.EvnOtherStac_pid = ESLast.EvnSection_id
				left join v_ResultDesease RD on RD.ResultDesease_id = COALESCE(EL.ResultDesease_id, EOL.ResultDesease_id, EOST.ResultDesease_id, ED.ResultDesease_id)
				left join v_PersonEncrypHIV PEH on PEH.Person_id = PS.Person_id
				left join lateral (
					select
						 EvnStick_id
						,EvnStick_setDT
						,EvnStick_disDT
					from
						v_EvnStick
					where
						EvnStick_pid = EPS.EvnPS_id
					order by
						EvnStick_setDT
					limit 1
				) EST on true
				left join lateral (
					select
						 dbo.Age2(t2.Person_Birthday, EPS.EvnPS_setDT) as Person_Age
						,t3.Sex_Name
						,t3.Sex_id
					from
						v_EvnStickCarePerson t1
						left join v_PersonState t2 on t2.Person_id = t1.Person_id
						left join v_Sex t3 on t3.Sex_id = t2.Sex_id
					where
						t1.Evn_id = EST.EvnStick_id
					limit 1
				) ESTCP on true
				left join v_Diag DG on DG.Diag_id = ESLast.Diag_id and COALESCE(ESLast.LeaveType_id, 0) != 5
				left join v_Diag PAD on PAD.Diag_id = ED.Diag_aid
				left join lateral (
					select Diag_id
					from v_EvnDiagPS
					where EvnDiagPS_pid = ESLast.EvnSection_id
						and DiagSetClass_id = 2
					limit 1
				) TDGA on true
				left join v_Diag DGA on DGA.Diag_id = TDGA.Diag_id and COALESCE(ESLast.LeaveType_id, 0) != 5
				left join lateral (
					select Diag_id
					from v_EvnDiagPS
					where EvnDiagPS_pid = ESLast.EvnSection_id
						and DiagSetClass_id = 3
					limit 1
				) TDGS on true
				left join v_Diag DGS on DGS.Diag_id = TDGS.Diag_id and COALESCE(ESLast.LeaveType_id, 0) != 5
				left join lateral (
					select Diag_id
					from v_EvnDiagPS
					where EvnDiagPS_pid = ED.EvnDie_id
						and DiagSetClass_id = 2
					limit 1
				) TPADA on true
				left join v_Diag PADA on PADA.Diag_id = TPADA.Diag_id
				left join lateral (
					select Diag_id
					from v_EvnDiagPS
					where EvnDiagPS_pid = ED.EvnDie_id
						and DiagSetClass_id = 3
					limit 1
				) TPADS on true
				left join v_Diag PADS on PADS.Diag_id = TPADS.Diag_id
				left join v_LpuUnitType oLUT on oLUT.LpuUnitType_id = EOST.LpuUnitType_oid
				left join lateral (
					select YN.YesNo_Name
					from v_EvnUsluga EU
					inner join v_UslugaComplex UC on UC.UslugaComplex_id = EU.UslugaComplex_id
					inner join v_YesNo YN on YN.YesNo_Code = 1
					where EU.EvnUsluga_rid = EPS.EvnPS_id and UC.UslugaComplex_Code like 'A12.06.011'
					and EU.EvnUsluga_SetDT is not null
					limit 1
				) as IsRW on true
				left join lateral (
					select YN.YesNo_Name
					from v_EvnUsluga EU
					inner join v_UslugaComplex UC on UC.UslugaComplex_id = EU.UslugaComplex_id
					inner join v_YesNo YN on YN.YesNo_Code = 1
					where
						EU.EvnUsluga_rid = EPS.EvnPS_id and UC.UslugaComplex_Code like 'A09.05.228'
						and EU.EvnUsluga_SetDT is not null
					limit 1
				) as IsAIDS on true
			where
				EPS.EvnPS_id = :EvnPS_id
			limit 1
				
		";
		if (!isTFOMSUser() && empty($data['session']['medpersonal_id'])) {
			$query .= ' and EPS.Lpu_id = :Lpu_id';
		}
		//echo "<pre>".getDebugSQL($query, array('EvnPS_id' => $data['EvnPS_id'], 'Lpu_id' => $data['Lpu_id']))."</pre>"; exit();
		$result = $this->db->query($query, array(
			'EvnPS_id' => $data['EvnPS_id'],
			'Lpu_id' => $data['Lpu_id']
		));
		//Отдельно получим сопутствующие диагнозы и осложнения
		$query_diag_sop = "
			select
				DGS.Diag_Code as \"LeaveDiagSop_Code\",
				DGS.Diag_Name as \"LeaveDiagSop_Name\"
			from v_EvnPS EPS
			left join v_EvnSection ESLast on ESLast.EvnSection_pid = EPS.EvnPS_id
					and ESLast.EvnSection_Index = ESLast.EvnSection_Count - 1
			inner join v_EvnDiagPS EDPS on EDPS.EvnDiagPS_pid = ESLast.EvnSection_id and EDPS.DiagSetClass_id = 3
			left join v_Diag DGS on DGS.Diag_id = EDPS.Diag_id
			where
				EPS.EvnPS_id = :EvnPS_id
				and EPS.Lpu_id = :Lpu_id
		";
		$query_diag_osl = "
		select
				DGA.Diag_Code as \"LeaveDiagAgg_Code\",
				DGA.Diag_Name as \"LeaveDiagAgg_Name\"
			from v_EvnPS EPS
			left join v_EvnSection ESLast on ESLast.EvnSection_pid = EPS.EvnPS_id
					and ESLast.EvnSection_Index = ESLast.EvnSection_Count - 1
			inner join v_EvnDiagPS EDPS on EDPS.EvnDiagPS_pid = ESLast.EvnSection_id and EDPS.DiagSetClass_id = 2
			left join v_Diag DGA on DGA.Diag_id = EDPS.Diag_id
			where
				EPS.EvnPS_id = :EvnPS_id
				and EPS.Lpu_id = :Lpu_id
		";
		$result_temp = array();
		$result_diag_sop = $this->db->query($query_diag_sop, array(
			'EvnPS_id' => $data['EvnPS_id'],
			'Lpu_id' => $data['Lpu_id']
		));
		if (is_object($result_diag_sop)) {
			$result_temp[0]['LeaveDiagSop_Name'] = '';
			$result_temp[0]['LeaveDiagSop_Code'] = '';
			$result_diag_sop = $result_diag_sop->result('array');
			for ($i = 0; $i < count($result_diag_sop); $i++) {
				$result_temp[0]['LeaveDiagSop_Name'] = $result_temp[0]['LeaveDiagSop_Name'] . $result_diag_sop[$i]['LeaveDiagSop_Name'];
				$result_temp[0]['LeaveDiagSop_Code'] = $result_temp[0]['LeaveDiagSop_Code'] . $result_diag_sop[$i]['LeaveDiagSop_Code'];
			}
		}
		$result_diag_osl = $this->db->query($query_diag_osl, array(
			'EvnPS_id' => $data['EvnPS_id'],
			'Lpu_id' => $data['Lpu_id']
		));
		if (is_object($result_diag_osl)) {
			$result_temp[0]['LeaveDiagAgg_Name'] = '';
			$result_temp[0]['LeaveDiagAgg_Code'] = '';
			$result_diag_osl = $result_diag_osl->result('array');
			for ($i = 0; $i < count($result_diag_osl); $i++) {
				$result_temp[0]['LeaveDiagAgg_Name'] = $result_temp[0]['LeaveDiagAgg_Name'] . $result_diag_osl[$i]['LeaveDiagAgg_Name'];
				$result_temp[0]['LeaveDiagAgg_Code'] = $result_temp[0]['LeaveDiagAgg_Code'] . $result_diag_osl[$i]['LeaveDiagAgg_Code'];
			}
		}
		//Отдельно получим категории льготности (если несколько, то нужно выводить все) (https://redmine.swan.perm.ru/issues/23968 #25)
		$query_priv = "
			select 	PT.PrivilegeType_id as \"PrivilegeType_id\",
					PT.PrivilegeType_Code as \"PrivilegeType_Code\",
					PT.PrivilegeType_Name as \"PrivilegeType_Name\"
			from v_EvnPS EPS
			left join v_PersonEvn PE on PE.PersonEvn_id = EPS.PersonEvn_id
			left join v_PersonPrivilege PP on PP.Person_id = PE.Person_id
			inner join v_PrivilegeType PT on PT.PrivilegeType_id = PP.PrivilegeType_id
			where (PP.PersonPrivilege_endDate is null or PP.PersonPrivilege_endDate >= EPS.EvnPS_setDate)
			and (PP.PersonPrivilege_begDate <= EPS.EvnPS_disDate or EPS.EvnPS_disDate is null)
			and EPS.EvnPS_id = :EvnPS_id
			and EPS.Lpu_id = :Lpu_id
			order by PP.PersonPrivilege_begDate
		";
		$res_priv = $this->db->query($query_priv, array('EvnPS_id' => $data['EvnPS_id'],
			'Lpu_id' => $data['Lpu_id']));
		if (is_object($result)) {
			$result = $result->result('array');
			$result[0]['Privileges'] = '';
			if (is_object($res_priv) && count($res_priv) > 0) {
				$res_priv = $res_priv->result('array');
				for ($i = 0; $i < count($res_priv); $i++) {
					$result[0]['Privileges'] = ' ' . $result[0]['Privileges'] . $res_priv[$i]['PrivilegeType_Name'] . '; ';
				}
			}
			$result[0]['LeaveDiagSop_Name'] = $result_temp[0]['LeaveDiagSop_Name'];
			$result[0]['LeaveDiagSop_Code'] = $result_temp[0]['LeaveDiagSop_Code'];
			$result[0]['LeaveDiagAgg_Name'] = $result_temp[0]['LeaveDiagAgg_Name'];
			$result[0]['LeaveDiagAgg_Code'] = $result_temp[0]['LeaveDiagAgg_Code'];
			return $result;
			//return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @param $response
	 * @return string
	 */
	protected function _printEvnPS($data, $response)
	{
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

		if (is_array($response_temp)) {
			$evn_section_data = array();
			$i = 0;
			foreach ($response_temp as $item) {
				$evn_section_data[$i] = $item;
				if (!empty($narrow_bed_arr[$item['EvnSection_id']])) {
					foreach ($narrow_bed_arr[$item['EvnSection_id']] as $bed) {
						$i++;
						$evn_section_data[$i] = $bed;
					}
				}
				$i++;
			}

			for ($i = 0; $i < (count($evn_section_data) < 2 ? 2 : count($evn_section_data)); $i++) {
				if ($i >= count($evn_section_data)) {
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
					if (!empty($evn_section_data[$i]['PayType_Name'])) {
						$evn_section_data[$i]['EvnSectionPayType_Name'] = $evn_section_data[$i]['PayType_Name'];
					}
				}
			}
		}

		$response_temp = $this->getEvnUslugaOperData($data);

		if (is_array($response_temp)) {
			for ($i = 0; $i < count($response_temp); $i++) {
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
			for ($j = $i; $j < 3; $j++) {
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
			foreach ($response_temp as $item) {
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
		, 'EvnPS_NumCard' => returnValidHTMLString($response[0]['EvnPS_NumCard'])
		, 'PolisType_Name' => returnValidHTMLString($response[0]['PolisType_Name'])
		, 'Polis_Num' => returnValidHTMLString($response[0]['Polis_Num'])
		, 'Polis_Ser' => returnValidHTMLString($response[0]['Polis_Ser'])
		, 'OMSSprTerr_Code' => returnValidHTMLString($response[0]['OMSSprTerr_Code'])
		, 'OrgSmo_Name' => returnValidHTMLString($response[0]['OrgSmo_Name'])
		, 'OrgSmo_OKATO' => returnValidHTMLString($response[0]['OrgSmo_OKATO'])
		, 'Person_Fio' => returnValidHTMLString($response[0]['Person_Fio'])
		, 'Person_OKATO' => returnValidHTMLString($response[0]['Person_OKATO'])
		, 'Sex_Name' => returnValidHTMLString($response[0]['Sex_Name'])
		, 'Person_Birthday' => returnValidHTMLString($response[0]['Person_Birthday'])
		, 'Person_Age' => returnValidHTMLString($response[0]['Person_Age'])
		, 'DocumentType_Name' => returnValidHTMLString($response[0]['DocumentType_Name'])
		, 'Document_Ser' => returnValidHTMLString($response[0]['Document_Ser'])
		, 'Document_Num' => returnValidHTMLString($response[0]['Document_Num'])
		, 'KLAreaType_Name' => returnValidHTMLString($response[0]['KLAreaType_Name'])
		, 'KLAreaType_id' => returnValidHTMLString($response[0]['KLAreaType_id'])
		, 'Person_Phone' => returnValidHTMLString($response[0]['Person_Phone'])
		, 'PAddress_Name' => returnValidHTMLString($response[0]['PAddress_Name'])
		, 'UAddress_Name' => returnValidHTMLString($response[0]['UAddress_Name'])
		, 'PayType_Name' => returnValidHTMLString($response[0]['PayType_Name'])
		, 'SocStatus_Name' => returnValidHTMLString($response[0]['SocStatus_Name'])
		, 'InvalidType_Name' => returnValidHTMLString($invalid_type_name)
		, 'PrehospOrg_Name' => returnValidHTMLString($response[0]['PrehospOrg_Name'])
		, 'EvnDirection_Num' => returnValidHTMLString($response[0]['EvnDirection_Num'])
		, 'EvnDirection_SetDT' => returnValidHTMLString($response[0]['EvnDirection_SetDT'])
		, 'PrehospArrive_Name' => returnValidHTMLString($response[0]['PrehospArrive_Name'])
		, 'EvnPS_CodeConv' => returnValidHTMLString($response[0]['EvnPS_CodeConv'])
		, 'EvnPS_NumConv' => returnValidHTMLString($response[0]['EvnPS_NumConv'])
		, 'PersonCard_Code' => returnValidHTMLString($response[0]['PersonCard_Code'])
		, 'Lpu_Name' => returnValidHTMLString($response[0]['Lpu_Name'])
		, 'PrehospDiag_Name' => returnValidHTMLString($response[0]['PrehospDiag_Name'])
		, 'AdmitDiag_Name' => returnValidHTMLString($response[0]['AdmitDiag_Name'])
		, 'PrehospToxic_Name' => returnValidHTMLString($response[0]['PrehospToxic_Name'])
		, 'LpuSectionTransType_Name' => returnValidHTMLString($response[0]['LpuSectionTransType_Name'])
		, 'PrehospType_Name' => returnValidHTMLString($response[0]['PrehospType_Name'])
		, 'PregospType_sCode' => returnValidHTMLString($response[0]['PregospType_sCode'])
		, 'IsFirst' => returnValidHTMLString($response[0]['IsFirst'])
		, 'EvnPS_HospCount' => returnValidHTMLString($response[0]['EvnPS_HospCount'])
		, 'EvnPS_TimeDesease' => returnValidHTMLString($response[0]['EvnPS_TimeDesease'])
		, 'EvnPS_TimeDeseaseType' => returnValidHTMLString($response[0]['EvnPS_TimeDeseaseType'])
		, 'PrehospTrauma_Name' => returnValidHTMLString($response[0]['PrehospTrauma_Name'])
		, 'EvnPS_setDate' => returnValidHTMLString($response[0]['EvnPS_setDate'])
		, 'EvnPS_setTime' => returnValidHTMLString($response[0]['EvnPS_setTime'])
		, 'LpuSectionFirst_Name' => returnValidHTMLString($response[0]['LpuSectionFirst_Name'])
		, 'EvnSectionFirst_setDate' => returnValidHTMLString($response[0]['EvnSectionFirst_setDate'])
		, 'EvnSectionFirst_setTime' => returnValidHTMLString($response[0]['EvnSectionFirst_setTime'])
		, 'EvnPS_disDate' => returnValidHTMLString($response[0]['EvnPS_disDate'])
		, 'EvnPS_disTime' => returnValidHTMLString($response[0]['EvnPS_disTime'])
		, 'EvnPS_KoikoDni' => returnValidHTMLString($response[0]['EvnPS_KoikoDni'])
		, 'LeaveType_Name' => returnValidHTMLString($response[0]['LeaveType_Name'])
		, 'LeaveType_sCode' => returnValidHTMLString($response[0]['LeaveType_sCode'])
		, 'ResultDesease_Name' => returnValidHTMLString($response[0]['ResultDesease_Name'])
		, 'ResultDesease_sCode' => returnValidHTMLString($response[0]['ResultDesease_sCode'])
		, 'EvnStick_setDate' => returnValidHTMLString($response[0]['EvnStick_setDate'])
		, 'EvnStick_disDate' => returnValidHTMLString($response[0]['EvnStick_disDate'])
		, 'PersonCare_Age' => returnValidHTMLString($response[0]['PersonCare_Age'])
		, 'PersonCare_SexName' => returnValidHTMLString($response[0]['PersonCare_SexName'])
		, 'EvnSectionData' => $evn_section_data
		, 'EvnUslugaOperData' => $evn_usluga_oper_data
		, 'PrivilegeType' => $privilege_type
		, 'LeaveDiag_Code' => returnValidHTMLString($response[0]['LeaveDiag_Code'])
		, 'LeaveDiag_Name' => returnValidHTMLString($response[0]['LeaveDiag_Name'])
		, 'LeaveDiagAgg_Code' => returnValidHTMLString($response[0]['LeaveDiagAgg_Code'])
		, 'LeaveDiagAgg_Name' => returnValidHTMLString($response[0]['LeaveDiagAgg_Name'])
		, 'LeaveDiagSop_Code' => returnValidHTMLString($response[0]['LeaveDiagSop_Code'])
		, 'LeaveDiagSop_Name' => returnValidHTMLString($response[0]['LeaveDiagSop_Name'])
		, 'AnatomDiag_Code' => returnValidHTMLString($response[0]['AnatomDiag_Code'])
		, 'AnatomDiag_Name' => returnValidHTMLString($response[0]['AnatomDiag_Name'])
		, 'AnatomDiagAgg_Code' => returnValidHTMLString($response[0]['AnatomDiagAgg_Code'])
		, 'AnatomDiagAgg_Name' => returnValidHTMLString($response[0]['AnatomDiagAgg_Name'])
		, 'AnatomDiagSop_Code' => returnValidHTMLString($response[0]['AnatomDiagSop_Code'])
		, 'AnatomDiagSop_Name' => returnValidHTMLString($response[0]['AnatomDiagSop_Name'])
		, 'EvnPS_IsDiagMismatch' => returnValidHTMLString($response[0]['EvnPS_IsDiagMismatch'])
		, 'EvnPS_IsImperHosp' => returnValidHTMLString($response[0]['EvnPS_IsImperHosp'])
		, 'EvnPS_IsShortVolume' => returnValidHTMLString($response[0]['EvnPS_IsShortVolume'])
		, 'EvnPS_IsWrongCure' => returnValidHTMLString($response[0]['EvnPS_IsWrongCure'])
		, 'MedPersonal_TabCode' => returnValidHTMLString($response[0]['MedPersonal_TabCode'])
		, 'IsRW' => returnValidHTMLString($response[0]['IsRW'])
		, 'IsAIDS' => returnValidHTMLString($response[0]['IsAIDS'])
		);

		$html = $this->parser->parse($template, $print_data, !empty($data['returnString']));
		if (!empty($data['returnString'])) {
			return array('html' => $html);
		} else {
			return $html;
		}
	}
}