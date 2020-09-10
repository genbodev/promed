<?php
require_once(APPPATH . 'models/_pgsql/EvnPS_model.php');

class Krym_EvnPS_model extends EvnPS_model
{
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
		if ($data['ARMType'] == 'superadmin') {
			$params['Lpu_id'] = null;
		}

		$query = "
			select
			lp.Lpu_id as \"Lpu_id\",
			lp.Lpu_f003mcod as \"fcode\",
			case when exists(
				select * from v_LpuUnit t 
				where t.Lpu_id = lp.Lpu_id and t.LpuUnitType_SysNick = 'polka'
			) then 1 else 0 end as \"hasPolka\",
			case when exists(
				select * from v_LpuUnit t
				where t.Lpu_id = lp.Lpu_id and t.LpuUnitType_SysNick IN ('stac', 'dstac', 'pstac', 'hstac')
			) then 1 else 0 end as \"hasStac\"
			from v_Lpu lp
			where
			lp.Lpu_f003mcod is not null
			and lp.Lpu_f003mcod <> '0'
			and (:Lpu_id is null or lp.Lpu_id = :Lpu_id)
		";
		$res = $this->db->query($query, $params);
		if (is_object($res)) {
			$lpu_arr = $res->result('array');
		} else {
			return false;
		}

		$query = "
			SELECT
			COALESCE((
			Select
				cast(lp.Lpu_f003mcod as varchar)||cast(EXTRACT(YEAR FROM EvnDirection_setDT) as varchar)||cast(EvnDirection_Num as varchar) as NOM_NAP,		--T(16) Номер направления (MCOD+NP)
				cast(EvnDirection_setDT as date) as DTA_NAP,
				case when prt.PrehospType_Code in (2,3) or ed.DirType_id = 5 then 3 else COALESCE(prt.PrehospType_Code,1) end as FRM_MP, -- Int - Форма оказания медицинской помощи
				lp.Lpu_f003mcod as MCOD_NAP, -- T(6)-- numeric(6, 0) - МО, направившая на госпитализацию	(реестровый номер, F003)
				SUBSTRING(ls.LpuSection_Code,1,2) as MPODR_NAP, -- numeric(2, 0) - Указываются первые два символа из Кода отделения врача, направившего на госпитализацию.
				lp1.Lpu_f003mcod as MCOD_STC, -- T(6)-- numeric(6, 0) - МО, куда направлен пациент	(реестровый номер, F003)
				SUBSTRING(ls1.LpuSection_Code,1,2) as MPODR_STC, -- numeric(2, 0) - Указываются первые два символа из Кода отделения, куда направлен пациент.
				pt.PolisType_CodeF008 as VPOLIS,-- numeric(1, 0) , -- Тип документа, подтверждающего факт страхования по ОМС (F008)
				case when pt.PolisType_CodeF008 in (1,2) then case when length(po.Polis_Ser) > 0 then po.Polis_Ser else null end else Null end as SPOLIS, -- Varchar(10) - Серия документа, подтверждающего факт страхования по ОМС
				case when pt.PolisType_CodeF008=3 then pe.Person_EdNum else po.Polis_Num end as NPOLIS, -- Numeric(20) - Номер документа, подтверждающего факт страхования по ОМС
				COALESCE(smo.Orgsmo_f002smocod,'00000') as SMO_CODE, -- numeric(6, 0) - СМО (реестровый номер, F002)
				org.Org_OKATO as ST_OKATO,--код ОКАТО, определять по значению поля «Территория»
				pe.Person_SurName as FAM, --varchar(30) – Фамилия
				pe.Person_FirName as IM, --varchar(30) – Имя
				pe.Person_SecName as OT, --varchar(30) – Отчество
				case when pe.Sex_id=3 then 1 else pe.Sex_id end as W, --numeric(1) – пол (1 - муж, 2 - жен)
				cast(pe.Person_BirthDay as date) as DR, --date – дата рождения
				COALESCE(pe.PersonPhone_Phone, 'не указан') as TLF, --Varchar(100) - Контактная информация
				di.Diag_Code as DS, --Char(4) - Код диагноза по МКБ
				case when SUBSTRING(ls1.LpuSection_Code,3,2)='' then 0 else SUBSTRING(ls1.LpuSection_Code,3,2) end as KOD_PFO, --numeric(4, 0) - Код отделения (профиль)
				ls1.LpuSectionProfile_Code as KOD_PFK, -- Профиль койки
				case when LENGTH(mps.Person_Snils) = 11 then SUBSTRING(mps.Person_Snils,1,3)||'-'||SUBSTRING(mps.Person_Snils,4,3)||'-'||SUBSTRING(mps.Person_Snils,7,3)||'-'||SUBSTRING(mps.Person_Snils,10,2) else '' end as KOD_DCT,
				case
					when tt.TimeTableStac_setDate is not null then cast(tt.TimeTableStac_setDate as date)
					else cast(EvnDirection_setDT + 30 as date)
				end as DTA_PLN -- date - Плановая дата госпитализации
			from dbo.v_EvnDirection ed
				left join dbo.v_PrehospType prt on ed.PrehospType_did=prt.PrehospType_id
				inner join v_lpu lp on lp.Lpu_id = ed.Lpu_id
				inner join dbo.v_LpuSection ls on ls.LpuSection_id = ed.LpuSection_id
				inner join dbo.v_LpuUnit lu on ls.LpuUnit_id=lu.LpuUnit_id
				inner join dbo.v_LpuBuilding lb on lb.LpuBuilding_id = lu.LpuBuilding_id
				inner join dbo.v_MedPersonal mp on ed.MedPersonal_id=mp.MedPersonal_id and mp.Lpu_id = ed.Lpu_id
				inner join dbo.v_PersonState mps on mps.Person_id = mp.Person_id
				left join dbo.v_TimeTableStac_lite tt on tt.EvnDirection_id = ed.EvnDirection_id
				inner join dbo.v_Diag di on di.Diag_id = ed.Diag_id
				inner join v_lpu lp1 on lp1.Lpu_id = ed.Lpu_did
				inner join dbo.v_LpuSection ls1 on ls1.LpuSection_id = ed.LpuSection_did
				inner join dbo.v_LpuUnit lu1 on ls1.LpuUnit_id=lu1.LpuUnit_id
				inner join dbo.v_LpuBuilding lb1 on lb1.LpuBuilding_id = lu1.LpuBuilding_id
				left join dbo.v_LpuSectionBedProfile lsb on lsb.LpuSectionBedProfile_id = ls1.LpuSectionBedProfile_id
				inner join dbo.v_Person_all pe on pe.PersonEvn_id = ed.PersonEvn_id and pe.Server_id = ed.Server_id
				left join dbo.v_Polis po on po.Polis_id = pe.Polis_id
				left join dbo.PolisType pt on pt.PolisType_id = po.PolisType_id and COALESCE(pt.PolisType_CodeF008,0)<>0
				left join dbo.v_OrgSMO smo on smo.OrgSMO_id = po.OrgSmo_id
				left join dbo.v_Org org on org.Org_id = smo.Org_id
			where (1=1)
				and ed.DirType_id in (1,5)
				and (:Lpu_id is null or ed.Lpu_id=:Lpu_id)
				and ed.EvnDirection_setDT>=dateadd('day', -2, cast(:Date||' '||:startTime as datetime)) and ed.EvnDirection_setDT<=dateadd('day', -1, cast(:Date||' '||:finalTime as datetime))
				and :hasPolka = 1
			),'') as \"T1\",	--GetReferToHosp
			COALESCE((
			select
				cast(lp.Lpu_f003mcod as varchar)||cast(year(ed.EvnDirection_setDT) as varchar)||cast(ed.EvnDirection_Num as varchar) as NOM_NAP, --Int
				cast(ed.EvnDirection_setDT as date) as DTA_NAP,
				case when eps.MedicalCareFormType_id = 1 then 3 when eps.MedicalCareFormType_id = 2 then 2 else 1 end as FRM_MP, -- Int - Форма оказания медицинской помощи
				lp.Lpu_f003mcod as MCOD_STC, -- T(6)-- numeric(6, 0) - МО госпитализации	(реестровый номер, F003)
				SUBSTRING(ls.LpuSection_Code,1,2) as MPODR_STC, -- numeric(2, 0) - Указываются первые два символа из Кода отделения, куда направлен пациент.
				lp1.Lpu_f003mcod as MCOD_NAP, -- T(6)-- numeric(6, 0) - Код подразделения МО, создавшей направление	(реестровый номер, F003)
				SUBSTRING(ls1.LpuSection_Code,1,2) as MPODR_NAP, -- numeric(2, 0) - Указываются первые два символа из Кода отделения врача, направившего на госпитализацию.
				cast(eps.EvnPS_setDate as date) as DTA_FKT, --Дата фактической госпитализации
				case when LENGTH(to_char(eps.EvnPS_setTime, 'hh24')) = 1 then '0'||to_char(eps.EvnPS_setTime, 'hh24') else to_char(eps.EvnPS_setTime, 'hh24') end||'-'
					||case when LENGTH(to_char(eps.EvnPS_setTime, 'mi')) = 1 then '0'||to_char(eps.EvnPS_setTime, 'mi') else to_char(eps.EvnPS_setTime, 'mi') end
				as TIM_FKT, --Время фактической госпитализации
				pt.PolisType_CodeF008 as VPOLIS,-- numeric(1, 0) , -- Тип документа, подтверждающего факт страхования по ОМС (F008)
				case when pt.PolisType_CodeF008 in (1,2) then case when LENGTH(po.Polis_Ser) > 0 then po.Polis_Ser else null end else Null end as SPOLIS, -- Varchar(10) - Серия документа, подтверждающего факт страхования по ОМС
				case when pt.PolisType_CodeF008=3 then pe.Person_EdNum else po.Polis_Num end as NPOLIS, -- Numeric(20) - Номер документа, подтверждающего факт страхования по ОМС
				pe.Person_SurName as FAM, --varchar(30) – Фамилия
				pe.Person_FirName as IM, --varchar(30) – Имя
				pe.Person_SecName as OT, --varchar(30) – Отчество
				case when pe.Sex_id=3 then 1 else pe.Sex_id end as W, --numeric(1) – пол (1 - муж, 2 - жен)
				cast(pe.Person_BirthDay as date) as DR, --date – дата рождения
				case when SUBSTRING(coalesce(EvnSec.LpuSection_Code,ls.LpuSection_Code,'0'),3,2)='' then 0 else SUBSTRING(coalesce(EvnSec.LpuSection_Code,ls.LpuSection_Code,'0'),3,2) end as KOD_PFO, --numeric(4, 0) - Код отделения (профиль)
				coalesce(EvnSec.LpuSectionProfile_Code,ls.LpuSectionProfile_Code,'0') as KOD_PFK, -- Профиль койки
				eps.EvnPS_NumCard as NHISTORY, --Номер карты стационарного больного
				di.Diag_Code as DS --Char(4) - Диагноз приемного отделения
			from dbo.v_EvnPS eps
				left join dbo.v_EvnDirection ed on ed.EvnDirection_id = eps.EvnDirection_id
				left join dbo.v_PrehospType prt on eps.PrehospType_id=prt.PrehospType_id
				inner join v_lpu lp on lp.Lpu_id = eps.Lpu_id
				inner join dbo.v_LpuSection ls on ls.LpuSection_id = eps.LpuSection_id
				inner join dbo.v_LpuUnit lu on ls.LpuUnit_id=lu.LpuUnit_id
				inner join dbo.v_LpuBuilding lb on lb.LpuBuilding_id = lu.LpuBuilding_id
				left join dbo.v_LpuSectionBedProfile lsb on lsb.LpuSectionBedProfile_id = ls.LpuSectionBedProfile_id
				inner join v_lpu lp1 on lp1.Lpu_id = ed.Lpu_id
				inner join dbo.v_LpuSection ls1 on ls1.LpuSection_id = ed.LpuSection_id
				inner join dbo.v_LpuUnit lu1 on ls1.LpuUnit_id=lu1.LpuUnit_id
				inner join dbo.v_LpuBuilding lb1 on lb1.LpuBuilding_id = lu1.LpuBuilding_id
				left join dbo.Diag di on di.Diag_id = eps.Diag_pid
				inner join dbo.v_Person_all pe on pe.PersonEvn_id = eps.PersonEvn_id and pe.Server_id = eps.Server_id
				left join dbo.v_Polis po on po.Polis_id = pe.Polis_id
				left join dbo.PolisType pt on pt.PolisType_id = po.PolisType_id
				left join lateral (
					select lses.LpuSection_Code, lses.LpuSectionProfile_Code 
					from v_EvnSection es
					inner join v_Lpusection lses on lses.Lpusection_id = es.lpusection_id
					where es.Evnsection_pid = eps.EvnPS_id and COALESCE(EvnSection_isPriem,1)!=2
					order by EvnSection_setDate desc
					limit 1
				)EvnSec on true
			where (1=1)
				and (:Lpu_id is null or eps.Lpu_id=:Lpu_id)
				and eps.EvnPS_setDT>=dateadd('day', -2, cast(:Date||' '||:startTime as datetime)) and eps.EvnPS_setDT<=dateadd('day', -1, cast(:Date||' '||:finalTime as datetime))
				and eps.PrehospWaifRefuseCause_id is null
				and :hasStac = 1
			), '') as \"T2\",	--GetHospPlan
			COALESCE
			((
			select
				lp.Lpu_f003mcod as MCOD_STC, -- T(6)-- numeric(6, 0) - МО госпитализации	(реестровый номер, F003)
				SUBSTRING(ls.LpuSection_Code,1,2) as MPODR_STC, -- numeric(2, 0) - Указываются первые два символа из Кода отделения, куда направлен пациент.
				cast(eps.EvnPS_setDate as date) as DTA_FKT, --Дата фактической госпитализации
				case when LENGTH(to_char(eps.EvnPS_setTime, 'hh24')) = 1 then '0'||to_char(eps.EvnPS_setTime,'hh24') else to_char(eps.EvnPS_setTime, 'hh24') end||'-'
					||case when LENGTH(to_char(eps.EvnPS_setTime, 'mi')) = 1 then '0'||to_char(eps.EvnPS_setTime, 'mi') else to_char(eps.EvnPS_setTime,'mi') end
				as TIM_FKT, --Время фактической госпитализации
				pt.PolisType_CodeF008 as VPOLIS,-- numeric(1, 0) , -- Тип документа, подтверждающего факт страхования по ОМС (F008)
				case when pt.PolisType_CodeF008 in (1,2) then case when LENGTH(po.Polis_Ser) > 0 then po.Polis_Ser else null end else Null end as SPOLIS, -- Varchar(10) - Серия документа, подтверждающего факт страхования по ОМС
				case when pt.PolisType_CodeF008=3 then pe.Person_EdNum else po.Polis_Num end as NPOLIS, -- Numeric(20) - Номер документа, подтверждающего факт страхования по ОМС
				COALESCE(smo.Orgsmo_f002smocod,'00000') as SMO_CODE, -- numeric(6, 0) - СМО (реестровый номер, F002)
				org.Org_OKATO as ST_OKATO, --Т(5) ОКАТО территории страхования
				pe.Person_SurName as FAM, --varchar(30) – Фамилия
				pe.Person_FirName as IM, --varchar(30) – Имя
				pe.Person_SecName as OT, --varchar(30) – Отчество
				case when pe.Sex_id=3 then 1 else pe.Sex_id end as W, --numeric(1) – пол (1 - муж, 2 - жен)
				cast(pe.Person_BirthDay as date) as DR, --date – дата рождения
				case when SUBSTRING(ls.LpuSection_Code,3,2)='' then 0 else SUBSTRING(ls.LpuSection_Code,3,2) end as KOD_PFO, --numeric(4, 0) - Код отделения (профиль)
				ls.LpuSectionProfile_Code as KOD_PFK, -- Профиль койки
				eps.EvnPS_NumCard as NHISTORY, --Номер карты стационарного больного
				di.Diag_Code as DS --Char(4) - Диагноз приемного отделения
			from dbo.v_EvnPS eps
				left join dbo.v_EvnDirection ed on ed.EvnDirection_id = eps.EvnDirection_id
				left join dbo.v_PrehospType prt on eps.PrehospType_id=prt.PrehospType_id
				inner join v_lpu lp on lp.Lpu_id = eps.Lpu_id
				inner join dbo.v_LpuSection ls on ls.LpuSection_id = eps.LpuSection_id
				inner join dbo.v_LpuUnit lu on ls.LpuUnit_id=lu.LpuUnit_id
				inner join dbo.v_LpuBuilding lb on lb.LpuBuilding_id = lu.LpuBuilding_id
				left join dbo.v_LpuSectionBedProfile lsb on lsb.LpuSectionBedProfile_id = ls.LpuSectionBedProfile_id
				left join dbo.Diag di on di.Diag_id = eps.Diag_pid
				inner join dbo.v_Person_all pe on pe.PersonEvn_id = eps.PersonEvn_id and pe.Server_id = eps.Server_id
				left join dbo.v_Polis po on po.Polis_id = pe.Polis_id
				left join dbo.PolisType pt on pt.PolisType_id = po.PolisType_id
				left join dbo.v_OrgSMO smo on smo.OrgSMO_id = po.OrgSmo_id
				left join dbo.v_Org org on org.Org_id = smo.Org_id
			where (1=1)
				and (:Lpu_id is null or eps.Lpu_id=:Lpu_id)
				and eps.EvnPS_setDT>=dateadd('day', -2, cast(:Date||' '||:startTime as datetime)) and eps.EvnPS_setDT<=dateadd('day', -1, cast(:Date||' '||:finalTime as datetime))
				and eps.PrehospWaifRefuseCause_id is null and prt.PrehospType_Code in (2,3)
				and :hasStac = 1
			), '') as \"T3\",	--GetHospEmerg,
			COALESCE((
			select
			t.NOM_NAP,
			t.DTA_NAP,
			t.IST_ANL,
			t.ACOD,
			t.MPODR_ANL,
			t.PR_ANL
			from (
			select
			cast(lp.Lpu_f003mcod as varchar)||cast(EXTRACT(YEAR FROM ed.EvnDirection_setDT) as varchar)||cast(ed.EvnDirection_Num as varchar)	as	NOM_NAP, -- bigint , Идентификатор направления
			cast(ed.EvnDirection_setDT as date) as DTA_NAP, --Дата направления	= дата создания направления
			case when puc.Lpu_id=ed.Lpu_id then 2 when puc.Lpu_id=ed.Lpu_did then 3 end as IST_ANL, --Источник аннулирования
			lp.Lpu_f003mcod	as	ACOD,-- numeric(6, 0) МО, отменившая направление (реестровый номер, F003)
			'' as MPODR_ANL,-- numeric(2, 0) Указывается первые два символа из Кода отделения, отменившей направление. Однозначно не определить, поэтому не заполняем
			case
				when ED.EvnStatus_id in (12,13) and ECS.EvnStatusCause_id = 18 then 1
				when ED.EvnStatus_id in (12,13) and ECS.EvnStatusCause_id = 22 then 2
				when ED.EvnStatus_id in (12,13) and ECS.EvnStatusCause_id = 1 then 3
				when ED.EvnStatus_id in (12,13) and ECS.EvnStatusCause_id = 24 then 4
				when ED.EvnStatus_id in (12,13) and ECS.EvnStatusCause_id = 25 then 5
				when ED.EvnStatus_id in (12,13) and ECS.EvnStatusCause_id = 26 then 6
				when ED.EvnStatus_id in (12,13) and ECS.EvnStatusCause_id = 27 then 7
				when ED.EvnStatus_id in (12,13) and ECS.EvnStatusCause_id = 5 then 8
				when ED.EvnStatus_id in (12,13) then 9
			end	as	PR_ANL-- int  Причина отмены направления
			from dbo.v_EvnDirection ed
			inner join dbo.DirFailType dr on dr.DirFailType_id = ed.DirFailType_id
			inner join dbo.pmUserCache puc on ed.pmUser_failID=puc.PMUser_id
			inner join v_lpu lp on lp.Lpu_id = puc.Lpu_id
			left join lateral (
				select
					eps.PrehospWaifRefuseCause_id
				from
					v_EvnPS eps
				where
					eps.EvnDirection_id = ed.EvnDirection_id
				limit 1
			) eps on true
			left join lateral (
				select
					EvnStatusCause_id
				from
					v_EvnStatusHistory
				where
					Evn_id = ed.EvnDirection_id
				order by
					EvnStatusHistory_insDT desc
				limit 1
			) ecs on true
			where(1=1)
			and ed.DirType_id in (1,5)
			and (:Lpu_id is null or ed.Lpu_id=:Lpu_id)
			and ed.EvnDirection_failDT>=dateadd('day', -2, cast(:Date||' '||:startTime as datetime)) and ed.EvnDirection_failDT<=dateadd('day', -1, cast(:Date||' '||:finalTime as datetime))
			union
			select
			cast(lp.Lpu_f003mcod as varchar)||cast(EXTRACT(YEAR FROM ed.EvnDirection_setDT) as varchar)||cast(ed.EvnDirection_Num as varchar)		as	NOM_NAP, -- bigint , Идентификатор направления
			cast(ed.EvnDirection_setDT as date) as DTA_NAP, --Дата направления	= дата создания направления
			case when lp.Lpu_id=eps.Lpu_id then 2 when lp.Lpu_id=ed.Lpu_did then 3 end as IST_ANL, --Источник аннулирования
			lp.Lpu_f003mcod	as	ACOD,-- numeric(6, 0) МО, отменившая направление (реестровый номер, F003)
			SUBSTRING(ls.LpuSection_Code,1,2) as MPODR_ANL,-- numeric(2, 0) Указывается первые два символа из Кода отделения, отменившей направление
			case
				when ED.EvnStatus_id in (12,13) and ECS.EvnStatusCause_id = 18 then 1
				when ED.EvnStatus_id in (12,13) and ECS.EvnStatusCause_id = 22 then 2
				when ED.EvnStatus_id in (12,13) and ECS.EvnStatusCause_id = 1 then 3
				when ED.EvnStatus_id in (12,13) and ECS.EvnStatusCause_id = 24 then 4
				when ED.EvnStatus_id in (12,13) and ECS.EvnStatusCause_id = 25 then 5
				when ED.EvnStatus_id in (12,13) and ECS.EvnStatusCause_id = 26 then 6
				when ED.EvnStatus_id in (12,13) and ECS.EvnStatusCause_id = 27 then 7
				when ED.EvnStatus_id in (12,13) and ECS.EvnStatusCause_id = 5 then 8
				when ED.EvnStatus_id in (12,13) then 9
			end	as	PR_ANL
			from dbo.v_EvnPS eps
			left join dbo.v_EvnDirection ed on ed.EvnDirection_id = eps.EvnDirection_id
			inner join dbo.v_LpuSection ls1 on ls1.LpuSection_id = eps.LpuSection_id
			inner join dbo.v_LpuUnit lu on ls1.LpuUnit_id=lu.LpuUnit_id
			inner join dbo.v_PrehospWaifRefuseCause pwrc on pwrc.PrehospWaifRefuseCause_id = eps.PrehospWaifRefuseCause_id
			inner join v_LpuSection ls on ls.LpuSection_id = eps.LpuSection_pid
			inner join v_lpu lp on lp.Lpu_id = ls.Lpu_id
			left join lateral (
				select
					EvnStatusCause_id
				from
					v_EvnStatusHistory
				where
					Evn_id = ed.EvnDirection_id
				order by
					EvnStatusHistory_insDT desc
				limit 1
			) ecs on true
			where(1=1)
			and ed.DirType_id in (1,5)
			and (:Lpu_id is null or ed.Lpu_id=:Lpu_id)
			and eps.EvnPS_OutcomeDT>=dateadd('day', -2, cast(:Date||' '||:startTime as datetime)) and eps.EvnPS_OutcomeDT<=dateadd('day', -1, cast(:Date||' '||:finalTime as datetime))
			) as t
			), '') as \"T4\",	--GetCancelReferToHosp,
			COALESCE((
			select
				cast(lp.Lpu_f003mcod as varchar)||cast(EXTRACT(YEAR FROM ed.EvnDirection_setDT) as varchar)||cast(ed.EvnDirection_Num as varchar)		as	NOM_NAP, -- bigint , Идентификатор направления
				cast(ed.EvnDirection_setDT as date) as DTA_NAP, --Дата направления	= дата создания направления
				case when eps.MedicalCareFormType_id = 1 then 3 when eps.MedicalCareFormType_id = 2 then 2 else 1 end as FRM_MP, -- Int - Форма оказания медицинской помощи
				lp.Lpu_f003mcod as MCOD_STC, -- T(6)-- numeric(6, 0) - МО госпитализации	(реестровый номер, F003)
				SUBSTRING(ls.LpuSection_Code,1,2) as MPODR_STC, -- numeric(2, 0) - Указываются первые два символа из Кода отделения, куда направлен пациент.
				cast(es.EvnSection_setDate as date) as DTA_FKT, --Дата госпитализации
				cast (es.EvnSection_disDT as date)	as	DTA_END, -- datetime - Дата выбытия
				pe.Person_SurName as FAM, --varchar(30) – Фамилия
				pe.Person_FirName as IM, --varchar(30) – Имя
				pe.Person_SecName as OT, --varchar(30) – Отчество
				case when pe.Sex_id=3 then 1 else pe.Sex_id end as W, --numeric(1) – пол (1 - муж, 2 - жен)
				cast(pe.Person_BirthDay as date) as DR, --date – дата рождения
				case when SUBSTRING(ls.LpuSection_Code,3,2)='' then 0 else SUBSTRING(ls.LpuSection_Code,3,2) end as KOD_PFO, --numeric(4, 0) - Код отделения (профиль)
				ls.LpuSectionProfile_Code as KOD_PFK, -- Профиль койки
				eps.EvnPS_NumCard as NHISTORY --Номер карты стационарного больного
			from dbo.v_EvnSection es
				inner join dbo.v_EvnPS eps on eps.EvnPS_id = es.EvnSection_pid
				left join dbo.v_EvnDirection ed on ed.EvnDirection_id = eps.EvnDirection_id
				left join dbo.v_PrehospType prt on eps.PrehospType_id=prt.PrehospType_id
				inner join v_lpu lp on lp.Lpu_id = es.Lpu_id
				inner join dbo.v_LpuSection ls on ls.LpuSection_id = es.LpuSection_id
				inner join dbo.v_LpuUnit lu on ls.LpuUnit_id=lu.LpuUnit_id
				inner join dbo.v_LpuBuilding lb on lb.LpuBuilding_id = lu.LpuBuilding_id
				left join dbo.v_LpuSectionBedProfile lsb  on lsb.LpuSectionBedProfile_id = ls.LpuSectionBedProfile_id
				inner join dbo.v_Person_all pe on pe.PersonEvn_id = es.PersonEvn_id and pe.Server_id = es.Server_id
				inner join dbo.v_LeaveType lt  on lt.LeaveType_id = es.LeaveType_id
			where (1=1)
			and (:Lpu_id is null or es.Lpu_id=:Lpu_id)
			and es.EvnSection_disDT>=dateadd('day', -2, cast(:Date||' '||:startTime as datetime)) and es.EvnSection_disDT<=dateadd('day', -1, cast(:Date||' '||:finalTime as datetime))
			and lt.LeaveType_Code <> 5
			and :hasStac = 1
			), '') as \"T5\",	--GetExitHosp,
			COALESCE((
			select
				t.*
			from rpt10.Han_hosp_enable(:Lpu_id,null,:date) t
			), '') as \"T6\"	--GetCouchInfo
		";

		$hosp_data_xml_arr = array();
		foreach ($lpu_arr as $lpu) {
			$params['Lpu_id'] = $lpu['Lpu_id'];
			$params['hasStac'] = $lpu['hasStac'];
			$params['hasPolka'] = $lpu['hasPolka'];
			//echo getDebugSQL($query, $params);exit;
			$hosp_data_xml_arr[$lpu['fcode']] = $this->getFirstRowFromQuery($query, $params);
		}

		return $hosp_data_xml_arr;
	}
}
