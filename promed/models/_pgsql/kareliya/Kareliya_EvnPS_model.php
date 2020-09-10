<?php
require_once(APPPATH . 'models/_pgsql/EvnPS_model.php');

class Kareliya_EvnPS_model extends EvnPS_model
{
	/**
	 * @param $data
	 * @return bool
	 */
	function getEvnPSNumber($data)
	{
		$query = "
			select
				ObjectID as \"EvnPS_NumCard\"
			from xp_GenpmID (
				ObjectName := 'EvnPS',
				Lpu_id := :Lpu_id,
				ObjectValue := :ObjectValue
			);
		";
		$result = $this->db->query($query, [
			'Lpu_id' => $data['Lpu_id'],
			'ObjectValue' => (!empty($data['year']) ? $data['year'] : date('Y'))
		]);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Выгрузка данных для ТФОМС и СМО
	 */
	function exportHospDataForTfomsToXml($data) {
		$params = [
			'Date' => $data['Date'],
			'Lpu_id' => $data['Lpu_id'],
			'startTime' => '20:00',
			'finalTime' => '19:59',
		];

		if ($data['ARMType'] == 'superadmin') {
			$params['Lpu_id'] = null;
		}

		$query = "
			select
				lp.Lpu_id as \"Lpu_id\",
				lp.Lpu_f003mcod as \"fcode\",
				case when exists(
					select *
					from v_LpuUnit t
					where t.Lpu_id = lp.Lpu_id
						and t.LpuUnitType_SysNick = 'polka'
				)
					then 1
					else 0
				end as \"hasPolka\",
				case when exists(
					select *
					from v_LpuUnit t
					where t.Lpu_id = lp.Lpu_id
						and t.LpuUnitType_SysNick IN ('stac', 'dstac', 'pstac', 'hstac')
				)
					then 1
					else 0
				end as \"hasStac\"
			from v_Lpu lp 
			where lp.Lpu_f003mcod is not null
				and lp.Lpu_f003mcod <> '0'
				and (:Lpu_id is null or lp.Lpu_id = :Lpu_id)
		";
		$res = $this->db->query($query, $params);
		if (is_object($res)) {
			$lpu_arr = $res->result('array');
		} else {
			return false;
		}

		$T3_AdditionalFields = "";

		if ( strtotime($data['Date']) >= strtotime('01.01.2018') ) {
			$T3_AdditionalFields .= "
				case
					-- есть электронное направление - берем данные с электронного направления
					when ed.EvnDirection_id is not null then
						ed_ld.Lpu_f003mcod
						|| cast(EXTRACT(YEAR FROM ed.EvnDirection_setDT) as varchar(4))
						|| right('000000' || ed.EvnDirection_Num, 6)
					-- нет электронного направления и направлен отделением МО - данные с КВС, код МО - своя МО
					-- нет электронного направления и направлен другой МО - данные с КВС, код МО - направившая МО из КВС
					when eps.PrehospDirect_id in (1, 2) and LENGTH(RTRIM(COALESCE(eps.EvnDirection_Num, ''))) > 0 and eps.EvnDirection_setDT is not null then
						right('000000' || COALESCE(case when eps.PrehospDirect_id = 1 then lp.Lpu_f003mcod else eps_ld.Lpu_f003mcod end, ''), 6)
						|| cast(EXTRACT(YEAR FROM eps.EvnDirection_setDT) as varchar(4))
						|| right('000000' || eps.Evndirection_Num, 6)
				end as NOM_NAP,
				cast(
					case
						when ed.EvnDirection_id is not null then ed.EvnDirection_setDT
						when eps.PrehospDirect_id in (1, 2) and LENGTH(RTRIM(COALESCE(eps.EvnDirection_Num, ''))) > 0 and eps.EvnDirection_setDT is not null then eps.EvnDirection_setDT
					end
				as date) as DTA_NAP,
			";
		}

		$query = "
			SELECT
			COALESCE((
			Select
				cast(lp.Lpu_f003mcod as varchar)||cast(EXTRACT(YEAR FROM EvnDirection_setDT) as varchar)
				||COALESCE(repeat('0',6-length(cast(EvnDirection_Num as varchar))),'')
				||cast(EvnDirection_Num as varchar) as NOM_NAP,		--T(16) Номер направления (MCOD+NP)
				cast(EvnDirection_setDT as date) as DTA_NAP,
				case when prt.PrehospType_Code in (2,3) or ed.DirType_id = 5 then 3 else COALESCE(prt.PrehospType_Code,1) end as FRM_MP, -- Int - Форма оказания медицинской помощи
				lp.Lpu_f003mcod as MCOD_NAP, -- T(6)-- numeric(6, 0) - МО, направившая на госпитализацию	(реестровый номер, F003)
				SUBSTRING(ls.LpuSection_Code,1,2) as MPODR_NAP, -- numeric(2, 0) - Указываются первые два символа из Кода отделения врача, направившего на госпитализацию.
				lp1.Lpu_f003mcod as MCOD_STC, -- T(6)-- numeric(6, 0) - МО, куда направлен пациент	(реестровый номер, F003)
				SUBSTRING(ls1.LpuSection_Code,1,2) as MPODR_STC, -- numeric(2, 0) - Указываются первые два символа из Кода отделения, куда направлен пациент.
				case
					when lu1.LpuUnitType_id = 1 then 1 -- круглосуточный
					when lu1.LpuUnitType_id = 6 then 21 -- дневной стационар при стационаре
					when lu1.LpuUnitType_id = 7 then 23 -- стационар на дому
					when lu1.LpuUnitType_id = 9 then 22 -- дневной стационар при поликлинике
				end as USL_OK,
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
				COALESCE(pe.PersonPhone_Phone, '-') as TLF, --Varchar(100) - Контактная информация
				di.Diag_Code as DS, --Char(4) - Код диагноза по МКБ
				case when SUBSTRING(ls1.LpuSection_Code,3,2)='' then 0 else SUBSTRING(ls1.LpuSection_Code,3,2) end as KOD_PFO, --numeric(4, 0) - Код отделения (профиль)
				coalesce(ls1.LpuSectionProfile_Code, lsp.LpuSectionProfile_Code) as KOD_PFK, -- Профиль койки
				case when length(mps.Person_Snils) = 11 then SUBSTRING(mps.Person_Snils,1,3)||'-'||SUBSTRING(mps.Person_Snils,4,3)||'-'||SUBSTRING(mps.Person_Snils,7,3)||'-'||SUBSTRING(mps.Person_Snils,10,2) else '' end as KOD_DCT,
				case
					when tt.TimeTableStac_setDate is not null then cast(tt.TimeTableStac_setDate as date)
					else cast(EvnDirection_setDT + 30 as date)
				end as DTA_PLN -- date - Плановая дата госпитализации
			from dbo.v_EvnDirection ed
				left join dbo.v_PrehospType prt on ed.PrehospType_did = prt.PrehospType_id
				inner join v_lpu lp on lp.Lpu_id = ed.Lpu_id
				left join dbo.v_LpuSection ls on ls.LpuSection_id = ed.LpuSection_id
				inner join dbo.v_MedPersonal mp on ed.MedPersonal_id = mp.MedPersonal_id and mp.Lpu_id = ed.Lpu_id
				inner join dbo.v_PersonState mps on mps.Person_id = mp.Person_id
				left join dbo.v_TimeTableStac_lite tt on tt.EvnDirection_id = ed.EvnDirection_id
				inner join dbo.v_Diag di on di.Diag_id = ed.Diag_id
				inner join v_lpu lp1 on lp1.Lpu_id = ed.Lpu_did
				inner join dbo.v_LpuSection ls1 on ls1.LpuSection_id = ed.LpuSection_did
				left join dbo.v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = ed.LpuSectionProfile_id
				inner join dbo.v_LpuUnit lu1 on ls1.LpuUnit_id = lu1.LpuUnit_id
				inner join dbo.v_LpuBuilding lb1 on lb1.LpuBuilding_id = lu1.LpuBuilding_id
				left join dbo.v_LpuSectionBedProfile lsb on lsb.LpuSectionBedProfile_id = ls1.LpuSectionBedProfile_id
				inner join dbo.v_Person_all pe on pe.PersonEvn_id = ed.PersonEvn_id and pe.Server_id = ed.Server_id
				left join dbo.v_Polis po on po.Polis_id = pe.Polis_id
				left join dbo.PolisType pt on pt.PolisType_id = po.PolisType_id and COALESCE(pt.PolisType_CodeF008, 0) <> 0
				left join dbo.v_OrgSMO smo on smo.OrgSMO_id = po.OrgSmo_id
				left join dbo.v_Org org on org.Org_id = smo.Org_id
			where (1=1)
				and ed.DirType_id in (1,5)
				and COALESCE(ed.PayType_id, (select PayType_id from v_PayType where PayType_SysNick = 'oms' limit 1)) = (select PayType_id from v_PayType where PayType_SysNick = 'oms' limit 1)
				and (:Lpu_id is null or ed.Lpu_id=:Lpu_id)
				and ed.EvnDirection_setDT>=dateadd('day', -2, cast(:Date||' '||:startTime as datetime)) and ed.EvnDirection_setDT<=dateadd('day', -1, cast(:Date||' '||finalTime as datetime))
			),'') as \"T1\",	--GetReferToHosp
			COALESCE((
			select
			cast(lp1.Lpu_f003mcod as varchar)
			||cast(EXTRACT(YEAR FROM COALESCE(ed.EvnDirection_setDT,eps.EvnDirection_setDT)) as varchar)
			||COALESCE(repeat('0',6-length(cast(COALESCE(ed.EvnDirection_Num,eps.EvnDirection_Num) as varchar))),'')
			||cast(COALESCE(ed.EvnDirection_Num,eps.EvnDirection_Num) as varchar) as NOM_NAP, --Int
			cast(COALESCE(ed.EvnDirection_setDT,eps.EvnDirection_setDT) as date) as DTA_NAP,
			case when eps.MedicalCareFormType_id = 1 then 3 when eps.MedicalCareFormType_id = 2 then 2 else 1 end as FRM_MP, -- Int - Форма оказания медицинской помощи
			lp.Lpu_f003mcod as MCOD_STC, -- T(6)-- numeric(6, 0) - МО госпитализации	(реестровый номер, F003)
			SUBSTRING(ls.LpuSection_Code,1,2) as MPODR_STC, -- numeric(2, 0) - Указываются первые два символа из Кода отделения, куда направлен пациент.
			lp1.Lpu_f003mcod as MCOD_NAP, -- T(6)-- numeric(6, 0) - Код подразделения МО, создавшей направление	(реестровый номер, F003)
			SUBSTRING(ls1.LpuSection_Code,1,2) as MPODR_NAP, -- numeric(2, 0) - Указываются первые два символа из Кода отделения врача, направившего на госпитализацию.
			cast(eps.EvnPS_setDate as date) as DTA_FKT, --Дата фактической госпитализации
			case when length(date_part('hour', eps.EvnPS_setTime)) = 1 then '0'||date_part('hour', eps.EvnPS_setTime) else date_part('hour', eps.EvnPS_setTime) end||'-'
				||case when length(date_part('minute', eps.EvnPS_setTime)) = 1 then '0'||date_part('minute', eps.EvnPS_setTime) else date_part('minute', eps.EvnPS_setTime) end
			as TIM_FKT, --Время фактической госпитализации
			pt.PolisType_CodeF008 as VPOLIS,-- numeric(1, 0) , -- Тип документа, подтверждающего факт страхования по ОМС (F008)
			case when pt.PolisType_CodeF008 in (1,2) then case when length(po.Polis_Ser) > 0 then po.Polis_Ser else null end else Null end as SPOLIS, -- Varchar(10) - Серия документа, подтверждающего факт страхования по ОМС
			case when pt.PolisType_CodeF008=3 then pe.Person_EdNum else po.Polis_Num end as NPOLIS, -- Numeric(20) - Номер документа, подтверждающего факт страхования по ОМС
			case
				when smo.Orgsmo_f002smocod is null then '00000'
				when po.Polis_endDate is not null and po.Polis_endDate < eps.EvnPS_setDate then '00000'
				else smo.Orgsmo_f002smocod
			end as SMO_CODE, -- numeric(6, 0) - СМО (реестровый номер, F002)
			pe.Person_SurName as FAM, --varchar(30) – Фамилия
			pe.Person_FirName as IM, --varchar(30) – Имя
			pe.Person_SecName as OT, --varchar(30) – Отчество
			case when pe.Sex_id=3 then 1 else pe.Sex_id end as W, --numeric(1) – пол (1 - муж, 2 - жен)
			cast(pe.Person_BirthDay as date) as DR, --date – дата рождения
			case
				when lu.LpuUnitType_id = 1 then 1 -- круглосуточный
				when lu.LpuUnitType_id = 6 then 21 -- дневной стационар при стационаре
			 	when lu.LpuUnitType_id = 7 then 23 -- стационар на дому
				when lu.LpuUnitType_id = 9 then 22 -- дневной стационар при поликлинике
			end as USL_OK,
			case when SUBSTRING(coalesce(EvnSec.LpuSection_Code,ls.LpuSection_Code,'0'),3,2)='' then 0 else SUBSTRING(coalesce(EvnSec.LpuSection_Code,ls.LpuSection_Code,'0'),3,2) end as KOD_PFO, --numeric(4, 0) - Код отделения (профиль)
			coalesce(EvnSec.LpuSectionProfile_Code,ls.LpuSectionProfile_Code,'0') as KOD_PFK, -- Профиль койки
			eps.EvnPS_NumCard as NHISTORY, --Номер карты стационарного больного
			COALESCE(di.Diag_Code, EvnSec.Diag_Code) as DS --Char(4) - Диагноз приемного отделения
			from dbo.v_EvnPS eps
			left join dbo.v_EvnDirection ed on ed.EvnDirection_id = eps.EvnDirection_id
			left join dbo.v_PrehospType prt on eps.PrehospType_id=prt.PrehospType_id
			left join dbo.v_PrehospDirect pd on eps.PrehospDirect_id=pd.PrehospDirect_id
			inner join v_lpu lp on lp.Lpu_id = eps.Lpu_id
			inner join dbo.v_LpuSection ls on ls.LpuSection_id = eps.LpuSection_id
			inner join dbo.v_LpuUnit lu on ls.LpuUnit_id=lu.LpuUnit_id
			inner join dbo.v_LpuBuilding lb on lb.LpuBuilding_id = lu.LpuBuilding_id
			left join dbo.v_LpuSectionBedProfile lsb on lsb.LpuSectionBedProfile_id = ls.LpuSectionBedProfile_id
			inner join v_lpu_all lp1 on lp1.Org_id = eps.Org_did or lp1.Lpu_id = case
				when ed.EvnDirection_id is not null then ed.Lpu_id
				when pd.PrehospDirect_SysNick like 'lpusection' then eps.Lpu_id
				when pd.PrehospDirect_SysNick like 'lpu' then eps.Lpu_did
			end
			left join dbo.v_LpuSection ls1 on ls1.LpuSection_id = COALESCE(ed.LpuSection_id, eps.LpuSection_did)
			left join dbo.v_LpuUnit lu1 on ls1.LpuUnit_id=lu1.LpuUnit_id
			left join dbo.v_LpuBuilding lb1 on lb1.LpuBuilding_id = lu1.LpuBuilding_id
			left join dbo.Diag di on di.Diag_id = eps.Diag_pid
			inner join dbo.v_Person_all pe on pe.PersonEvn_id = eps.PersonEvn_id and pe.Server_id = eps.Server_id
			left join dbo.v_Polis po on po.Polis_id = pe.Polis_id
			left join dbo.PolisType pt on pt.PolisType_id = po.PolisType_id
			left join dbo.v_OrgSMO smo on smo.OrgSMO_id = po.OrgSmo_id
			left join lateral(
				select lses.LpuSection_Code, lses.LpuSectionProfile_Code, d.Diag_Code
				from v_EvnSection es
				inner join v_Lpusection lses on lses.Lpusection_id = es.lpusection_id
				left join v_Diag d on d.Diag_id = es.Diag_id
				where es.Evnsection_pid = eps.EvnPS_id and COALESCE(EvnSection_isPriem,1)!=2
				order by EvnSection_setDate desc
				limit 1
			)EvnSec on true
			where (1=1)
			and (:Lpu_id is null or eps.Lpu_id=:Lpu_id)
			and eps.EvnPS_setDT>=dateadd('day', -2, cast(:Date||' '||:startTime as datetime))
			and eps.EvnPS_setDT<=dateadd('day', -1, cast(:Date||' '||finalTime as datetime))
			and eps.PrehospWaifRefuseCause_id is null
			and prt.PrehospType_Code = 1
			and eps.PayType_id = (select PayType_id from v_PayType where PayType_SysNick = 'oms' limit 1) and :hasStac = 1
			), '') as \"T2\",	--GetHospPlan
			COALESCE
			((
			select
			{$T3_AdditionalFields}
			lp.Lpu_f003mcod as MCOD_STC, -- T(6)-- numeric(6, 0) - МО госпитализации	(реестровый номер, F003)
			SUBSTRING(ls.LpuSection_Code,1,2) as MPODR_STC, -- numeric(2, 0) - Указываются первые два символа из Кода отделения, куда направлен пациент.
			cast(eps.EvnPS_setDate as date) as DTA_FKT, --Дата фактической госпитализации
			case when length(date_part('hour', eps.EvnPS_setTime)) = 1 then '0'||date_part('hour', eps.EvnPS_setTime) else date_part('hour', eps.EvnPS_setTime) end||'-'
				||case when length(date_part('minute', eps.EvnPS_setTime)) = 1 then '0'||date_part('minute', eps.EvnPS_setTime) else date_part('minute', eps.EvnPS_setTime) end
			as TIM_FKT, --Время фактической госпитализации
			pt.PolisType_CodeF008 as VPOLIS,-- numeric(1, 0) , -- Тип документа, подтверждающего факт страхования по ОМС (F008)
			case when pt.PolisType_CodeF008 in (1,2) then case when length(po.Polis_Ser) > 0 then po.Polis_Ser else null end else Null end as SPOLIS, -- Varchar(10) - Серия документа, подтверждающего факт страхования по ОМС
			case when pt.PolisType_CodeF008=3 then pe.Person_EdNum else po.Polis_Num end as NPOLIS, -- Numeric(20) - Номер документа, подтверждающего факт страхования по ОМС
			COALESCE(smo.Orgsmo_f002smocod,'00000') as SMO_CODE, -- numeric(6, 0) - СМО (реестровый номер, F002)
			org.Org_OKATO as ST_OKATO, --Т(5) ОКАТО территории страхования
			pe.Person_SurName as FAM, --varchar(30) – Фамилия
			pe.Person_FirName as IM, --varchar(30) – Имя
			pe.Person_SecName as OT, --varchar(30) – Отчество
			case when pe.Sex_id=3 then 1 else pe.Sex_id end as W, --numeric(1) – пол (1 - муж, 2 - жен)
			cast(pe.Person_BirthDay as date) as DR, --date – дата рождения
			case
				when lu.LpuUnitType_id = 1 then 1 -- круглосуточный
				when lu.LpuUnitType_id = 6 then 21 -- дневной стационар при стационаре
			 	when lu.LpuUnitType_id = 7 then 23 -- стационар на дому
				when lu.LpuUnitType_id = 9 then 22 -- дневной стационар при поликлинике
			end as USL_OK,
			case when SUBSTRING(ls.LpuSection_Code,3,2)='' then 0 else SUBSTRING(ls.LpuSection_Code,3,2) end as KOD_PFO, --numeric(4, 0) - Код отделения (профиль)
			ls.LpuSectionProfile_Code as KOD_PFK, -- Профиль койки
			eps.EvnPS_NumCard as NHISTORY, --Номер карты стационарного больного
			COALESCE(di.Diag_Code, EvnSec.Diag_Code) as DS --Char(4) - Диагноз приемного отделения
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
			left join dbo.v_Lpu ed_ld on ed_ld.Lpu_id = ed.Lpu_id -- направившая МО из электронного напрваления
			left join dbo.v_Lpu_all eps_ld on eps_ld.Org_id = eps.Org_did -- направившая МО из КВС
			left join lateral(
				select d.Diag_Code
				from v_EvnSection es
				left join v_Diag d on d.Diag_id = es.Diag_id
				where es.Evnsection_pid = eps.EvnPS_id and COALESCE(EvnSection_isPriem,1)!=2
				order by EvnSection_setDate desc
				limit 1
			) EvnSec on true
			where (1=1) 
			and (:Lpu_id is null or eps.Lpu_id=:Lpu_id)
			and eps.EvnPS_setDT>=dateadd('day', -2, cast(:Date||' '||:startTime as datetime)) and eps.EvnPS_setDT<=dateadd('day', -1, cast(:Date||' '||finalTime as datetime))
			and eps.PrehospWaifRefuseCause_id is null and prt.PrehospType_Code in (2,3)
			and eps.PayType_id = (select PayType_id from v_PayType where PayType_SysNick = 'oms' limit 1) and :hasStac = 1
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
			cast(lp1.Lpu_f003mcod as varchar)
			||cast(year(ed.EvnDirection_setDT) as varchar)
			||COALESCE(repeat('0',6-length(cast(ed.EvnDirection_Num as varchar))),'')
			||cast(ed.EvnDirection_Num as varchar)	as	NOM_NAP, -- bigint , Идентификатор направления
			cast(ed.EvnDirection_setDT as date) as DTA_NAP, --Дата направления	= дата создания направления
			case when puc.Lpu_id=ed.Lpu_id then 2 when puc.Lpu_id=ed.Lpu_did then 3 end as IST_ANL, --Источник аннулирования
			lp.Lpu_f003mcod	as	ACOD,-- numeric(6, 0) МО, отменившая направление (реестровый номер, F003)
			'' as MPODR_ANL,-- numeric(2, 0) Указывается первые два символа из Кода отделения, отменившей направление. Однозначно не определить, поэтому не заполняем
			case
				when ED.EvnStatus_id in (12,13) and ECS.EvnStatusCause_id = 18 then 1
				when ED.EvnStatus_id in (12,13) and ECS.EvnStatusCause_id = 22 then 2
				when ED.EvnStatus_id in (12,13) and ECS.EvnStatusCause_id = 1 then 3
				when ED.EvnStatus_id in (12,13) and ECS.EvnStatusCause_id = 24 then 4
				when ED.EvnStatus_id in (12,13) and ECS.EvnStatusCause_id = 5 then 5
				when ED.EvnStatus_id in (12,13) then 6
			end	as	PR_ANL-- int  Причина отмены направления
			from dbo.v_EvnDirection ed
			left join dbo.DirFailType dr on dr.DirFailType_id = ed.DirFailType_id
			inner join dbo.pmUserCache puc on ed.pmUser_failID=puc.PMUser_id
			inner join v_lpu lp on lp.Lpu_id = puc.Lpu_id
			inner join v_lpu lp1 on lp1.Lpu_id = ed.Lpu_id
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
			and ed.EvnDirection_failDT>=dateadd('day', -2, cast(:Date||' '||:startTime as datetime)) and ed.EvnDirection_failDT<=dateadd('day', -1, cast(:Date||' '||finalTime as datetime))
			and COALESCE(ed.PayType_id, (select PayType_id from v_PayType where PayType_SysNick = 'oms' limit 1)) = (select PayType_id from v_PayType where PayType_SysNick = 'oms' limit 1)
			union
			select
			cast(lp1.Lpu_f003mcod as varchar)
			||cast(EXTRACT(YEAR FROM ed.EvnDirection_setDT) as varchar)
			||COALESCE(repeat('0',6-length(cast(ed.EvnDirection_Num as varchar))),'')
			||cast(ed.EvnDirection_Num as varchar)		as	NOM_NAP, -- bigint , Идентификатор направления
			cast(ed.EvnDirection_setDT as date) as DTA_NAP, --Дата направления	= дата создания направления
			case when lp.Lpu_id=eps.Lpu_id then 2 when lp.Lpu_id=ed.Lpu_did then 3 end as IST_ANL, --Источник аннулирования
			lp.Lpu_f003mcod	as	ACOD,-- numeric(6, 0) МО, отменившая направление (реестровый номер, F003)
			SUBSTRING(ls.LpuSection_Code,1,2) as MPODR_ANL,-- numeric(2, 0) Указывается первые два символа из Кода отделения, отменившей направление
			case
				when ED.EvnStatus_id in (12,13) and ECS.EvnStatusCause_id = 18 then 1
				when ED.EvnStatus_id in (12,13) and ECS.EvnStatusCause_id = 22 then 2
				when ED.EvnStatus_id in (12,13) and ECS.EvnStatusCause_id = 1 then 3
				when ED.EvnStatus_id in (12,13) and ECS.EvnStatusCause_id = 24 then 4
				when ED.EvnStatus_id in (12,13) and ECS.EvnStatusCause_id = 5 then 5
				when ED.EvnStatus_id in (12,13) then 6
			end	as	PR_ANL
			from dbo.v_EvnPS eps
			left join dbo.v_EvnDirection ed on ed.EvnDirection_id = eps.EvnDirection_id
			inner join dbo.v_LpuSection ls1 on ls1.LpuSection_id = eps.LpuSection_id
			inner join dbo.v_LpuUnit lu on ls1.LpuUnit_id=lu.LpuUnit_id
			inner join dbo.v_PrehospWaifRefuseCause pwrc on pwrc.PrehospWaifRefuseCause_id = eps.PrehospWaifRefuseCause_id
			inner join v_LpuSection ls on ls.LpuSection_id = eps.LpuSection_pid
			left join dbo.v_PrehospDirect pd on eps.PrehospDirect_id=pd.PrehospDirect_id
			inner join v_lpu lp on lp.Lpu_id = ls.Lpu_id
			inner join v_lpu_all lp1 on lp1.Org_id = eps.Org_did or lp1.Lpu_id = case
				when ed.EvnDirection_id is not null then ed.Lpu_id
				when pd.PrehospDirect_SysNick like 'lpusection' then eps.Lpu_id
				when pd.PrehospDirect_SysNick like 'lpu' then eps.Lpu_did
			end
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
			where(1=1) and (:hasPolka = 1 or :hasStac = 1)
			and ed.DirType_id in (1,5)
			and (:Lpu_id is null or ed.Lpu_id=:Lpu_id)
			and eps.EvnPS_OutcomeDT>=dateadd('day', -2, cast(:Date||' '||:startTime as datetime)) and eps.EvnPS_OutcomeDT<=dateadd('day', -1, cast(:Date||' '||finalTime as datetime))
			and eps.PayType_id = (select PayType_id from v_PayType where PayType_SysNick = 'oms' limit 1)
			) as t
			), '') as \"T4\",	--GetCancelReferToHosp,
			COALESCE((
			select
			cast(lp1.Lpu_f003mcod as varchar)
			||cast(EXTRACT(YEAR FROM COALESCE(ed.EvnDirection_setDT, eps.EvnDirection_setDT)) as varchar)
			||COALESCE(repeat('0',6-length(cast(COALESCE(ed.EvnDirection_Num, eps.EvnDirection_Num) as varchar))),'')
			||cast(COALESCE(ed.EvnDirection_Num, eps.EvnDirection_Num) as varchar)		as	NOM_NAP, -- bigint , Идентификатор направления
			cast(COALESCE(ed.EvnDirection_setDT, eps.EvnDirection_setDT) as date) as DTA_NAP, --Дата направления	= дата создания направления
			case when eps.MedicalCareFormType_id = 1 then 3 when eps.MedicalCareFormType_id = 2 then 2 else 1 end as FRM_MP, -- Int - Форма оказания медицинской помощи
			lp.Lpu_f003mcod as MCOD_STC, -- T(6)-- numeric(6, 0) - МО госпитализации	(реестровый номер, F003)
			SUBSTRING(ls.LpuSection_Code,1,2) as MPODR_STC, -- numeric(2, 0) - Указываются первые два символа из Кода отделения, куда направлен пациент.
			cast(es.EvnSection_setDate as date) as DTA_FKT, --Дата госпитализации
			cast (es.EvnSection_disDT as date)	as	DTA_END, -- datetime - Дата выбытия
			case
				when smo.Orgsmo_f002smocod is null then '00000'
				when po.Polis_endDate is not null and po.Polis_endDate < elb.EvnLeaveBase_setDate then '00000'
				else smo.Orgsmo_f002smocod
			end as SMO_CODE, -- numeric(6, 0) - СМО (реестровый номер, F002)
			pe.Person_SurName as FAM, --varchar(30) – Фамилия
			pe.Person_FirName as IM, --varchar(30) – Имя
			pe.Person_SecName as OT, --varchar(30) – Отчество
			case when pe.Sex_id=3 then 1 else pe.Sex_id end as W, --numeric(1) – пол (1 - муж, 2 - жен)
			cast(pe.Person_BirthDay as date) as DR, --date – дата рождения
			pt.PolisType_CodeF008 as VPOLIS,-- numeric(1, 0) -- Тип документа, подтверждающего факт страхования по ОМС (F008)
			case when pt.PolisType_CodeF008 = 1 and length(po.Polis_Ser) > 0 then po.Polis_Ser else null end as SPOLIS, -- Varchar(10) - Серия документа, подтверждающего факт страхования по ОМС
			case when pt.PolisType_CodeF008 = 3 then pe.Person_EdNum else po.Polis_Num end as NPOLIS, -- Numeric(20) - Номер документа, подтверждающего факт страхования по ОМС
			case
				when lu.LpuUnitType_id = 1 then 1 -- круглосуточный
				when lu.LpuUnitType_id = 6 then 21 -- дневной стационар при стационаре
			 	when lu.LpuUnitType_id = 7 then 23 -- стационар на дому
				when lu.LpuUnitType_id = 9 then 22 -- дневной стационар при поликлинике
			end as USL_OK,
			case when SUBSTRING(ls.LpuSection_Code,3,2)='' then 0 else SUBSTRING(ls.LpuSection_Code,3,2) end as KOD_PFO, --numeric(4, 0) - Код отделения (профиль)
			ls.LpuSectionProfile_Code as KOD_PFK, -- Профиль койки
			eps.EvnPS_NumCard as NHISTORY --Номер карты стационарного больного
			from dbo.v_EvnSection es
			inner join dbo.v_EvnPS eps on eps.EvnPS_id = es.EvnSection_pid
			inner join dbo.v_EvnLeaveBase elb on elb.EvnLeaveBase_pid = es.EvnSection_id
			left join dbo.v_EvnDirection ed on ed.EvnDirection_id = eps.EvnDirection_id
			left join dbo.v_PrehospType prt on eps.PrehospType_id=prt.PrehospType_id
			left join dbo.v_PrehospDirect pd on eps.PrehospDirect_id=pd.PrehospDirect_id
			inner join v_lpu lp on lp.Lpu_id = es.Lpu_id
			inner join dbo.v_LpuSection ls on ls.LpuSection_id = es.LpuSection_id
			inner join dbo.v_LpuUnit lu on ls.LpuUnit_id=lu.LpuUnit_id
			inner join dbo.v_LpuBuilding lb on lb.LpuBuilding_id = lu.LpuBuilding_id
			left join dbo.v_LpuSectionBedProfile lsb on lsb.LpuSectionBedProfile_id = ls.LpuSectionBedProfile_id
			inner join v_lpu_all lp1 on lp1.Org_id = eps.Org_did or lp1.Lpu_id = case
				when ed.EvnDirection_id is not null then ed.Lpu_id
				when pd.PrehospDirect_SysNick like 'lpusection' then eps.Lpu_id
				when pd.PrehospDirect_SysNick like 'lpu' then eps.Lpu_did
			end
			inner join dbo.v_Person_all pe on pe.PersonEvn_id = elb.PersonEvn_id and pe.Server_id = elb.Server_id
			left join dbo.v_Polis po on po.Polis_id = pe.Polis_id
			left join dbo.PolisType pt on pt.PolisType_id = po.PolisType_id
			left join dbo.v_OrgSMO smo on smo.OrgSMO_id = po.OrgSmo_id
			inner join dbo.v_LeaveType lt on lt.LeaveType_id = es.LeaveType_id
			where (1=1)
				and (:Lpu_id is null or es.Lpu_id=:Lpu_id)
				and es.EvnSection_disDT>=dateadd('day', -2, cast(:Date||' '||:startTime as datetime)) and es.EvnSection_disDT<=dateadd('day', -1, cast(:Date||' '||finalTime as datetime))
				and lt.LeaveType_Code <> 5
				and es.PayType_id = (select PayType_id from v_PayType where PayType_SysNick = 'oms' limit 1) and :hasStac = 1
			), '') as \"T5\",	--GetExitHosp,
			COALESCE((
			select
				t.*
			from rpt10.Han_hosp_enable(:Lpu_id,null,:Date) t where :hasStac = 1
			), '') as \"T6\"	--GetCouchInfo
		";

		$hosp_data_xml_arr = array();
		foreach($lpu_arr as $lpu) {
			$params['Lpu_id'] = $lpu['Lpu_id'];
			$params['hasStac'] = $lpu['hasStac'];
			$params['hasPolka'] = $lpu['hasPolka'];
			//echo getDebugSQL($query, $params);exit;
			$hosp_data_xml_arr[$lpu['fcode']] = $this->getFirstRowFromQuery($query, $params);
		}

		return $hosp_data_xml_arr;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function getEvnPSFields($data) {
		$query = "
			select
				 COALESCE(EPS.EvnPS_NumCard, '') as \"EvnPS_NumCard\"
				,RTRIM(COALESCE(Lpu.Lpu_Name, '')) as \"Lpu_Name\"
				,RTRIM(COALESCE(PLST.PolisType_Name, '')) as \"PolisType_Name\"
				,CASE WHEN PLST.PolisType_Code = 4 then '' ELSE RTRIM(COALESCE(PLS.Polis_Ser, '')) END as \"Polis_Ser\"
				,CASE WHEN PLST.PolisType_Code = 4 then COALESCE(RTRIM(PS.Person_EdNum), '') ELSE RTRIM(COALESCE(PLS.Polis_Num, '')) END AS \"Polis_Num\"
				--,RTRIM(COALESCE(PLS.Polis_Num, '')) as Polis_Num
				--,RTRIM(COALESCE(PLS.Polis_Ser, '')) as Polis_Ser
				,RTRIM(COALESCE(OST.OMSSprTerr_Code, '')) as \"OMSSprTerr_Code\"
				,RTRIM(COALESCE(OrgSmo.OrgSMO_Name, '')) as \"OrgSmo_Name\"
				,RTRIM(COALESCE(OS.Org_OKATO, '')) as \"OrgSmo_OKATO\"
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
				,RTRIM(COALESCE(PT.PayType_Name, '')) as \"PayType_Name\"
				,RTRIM(COALESCE(PT.PayType_Code, '')) as \"PayType_Code\"
				,RTRIM(COALESCE(SS.SocStatus_Name, '')) as \"SocStatus_Name\"
				,RTRIM(COALESCE(SS.SocStatus_Code, '')) as \"SocStatus_Code\"
				,RTRIM(COALESCE(SS.SocStatus_SysNick, '')) as \"SocStatus_SysNick\"
				,IT.PrivilegeType_id as \"PrivilegeType_id\"
				,/*RTRIM(ISNULL(IT.PrivilegeType_Code, '')) + ' ' + */RTRIM(ISNULL(IT.PrivilegeType_Name, '')) as PrivilegeType_Name
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
				,RTRIM(COALESCE(PHLS.LpuSection_Name, PreHospLpu.Lpu_Name, PHOM.OrgMilitary_Name, PHO.Org_Name, '')) as \"PrehospOrg_Name\"
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
				,case when COALESCE(EPS.EvnPS_HospCount, 1) = 1 then 1 else 2 end as \"sFirst\"
				,case when EPS.Okei_id = '100'
				  then
				    case when (EPS.EvnPS_TimeDesease <= 6) then 1 else (case when (EPS.EvnPS_TimeDesease > 24) then 3 else 2 end) end
				  else 3
				end as \"EvnPS_TimeDeseaseType\"
				--,case when (EPS.EvnPS_TimeDesease <= 6) then 1 else (case when (EPS.EvnPS_TimeDesease > 24) then 3 else 2 end) end as EvnPS_TimeDeseaseType
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
				,case when LUTLast.LpuUnitType_SysNick = 'stac'
				  then
				    date_part('day', cast(EPS.EvnPS_disDate as date) - cast(EPS.EvnPS_setDate as date))
                     + abs(sign(date_part('day', cast(EPS.EvnPS_disDate as date) - cast(EPS.EvnPS_setDate as date) )) - 1) -- круглосуточные
				  else
				    (date_part('day', cast(EPS.EvnPS_disDate as date) - cast(EPS.EvnPS_setDate as date)) + 1) -- дневные
				end as \"EvnPS_KoikoDni\"
				,RTRIM(COALESCE(LT.LeaveType_Name, '')) as \"LeaveType_Name\"
				,LT.LeaveType_Code as \"LeaveType_Code\"
				,case
					when LT.LeaveType_SysNick like 'leave' then 1
					when LT.LeaveType_SysNick like 'stac' and oLUT.LpuUnitType_SysNick in('pstac','dstac') then 2
					when LT.LeaveType_SysNick like 'stac' and oLUT.LpuUnitType_SysNick like 'stac' then 3
					when LT.LeaveType_SysNick like 'stac' and oLUT.LpuUnitType_SysNick not in('stac','pstac','dstac') then 4
					else LT.LeaveType_Code
				end as \"LeaveType_sCode\"
				,RTRIM(COALESCE(RD.ResultDesease_Name, '')) as \"ResultDesease_Name\"
				,RD.ResultDesease_Code as \"ResultDesease_Code\"
				,case
					when LT.LeaveType_SysNick like 'die' then 6
					when RD.ResultDesease_SysNick in('zdorvosst','zdorchast','zdornar') then 1
					when RD.ResultDesease_SysNick in('rem','uluc','stabil') then 2
					when RD.ResultDesease_SysNick in('noeffect') then 3
					when RD.ResultDesease_SysNick in('yatr','novzab','progress') then 4
					when RD.ResultDesease_SysNick like 'zdor' then 5
					else RD.ResultDesease_Code
				end as \"ResultDesease_sCode\"
				,to_char(ESWR.EvnStickWorkRelease_begDT, 'dd.mm.yyyy') as \"EvnStick_setDate\"
				,to_char(ESWR.EvnStickWorkRelease_endDT, 'dd.mm.yyyy') as \"EvnStick_disDate\"
				,EST.StickLeaveType_id as \"StickLeaveType_id\"
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
						,StickLeaveType_id
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
				left join lateral (
					select
						 min(EvnStickWorkRelease_begDT) as EvnStickWorkRelease_begDT
						,max(EvnStickWorkRelease_endDT) as EvnStickWorkRelease_endDT
					from
						v_EvnStickWorkRelease t1
					where
						t1.EvnStickBase_id = EST.EvnStick_id
				) ESWR on true
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
			where
				EPS.EvnPS_id = :EvnPS_id
			limit 1
		";
		$checkQuery = "
			select * 
			from v_EvnPS EPS
			where EPS.EvnPS_id = :EvnPS_id and EPS.Lpu_id = :Lpu_id
				";
		$check_result = $this->db->query($checkQuery, array(
			'EvnPS_id' => $data['EvnPS_id'],
			'Lpu_id' => $data['Lpu_id']
		));
		if(is_object($check_result)&&(count($check_result->result('array'))>0)){
			$check = true;
		} else {
			$check = false;
		}
		if(!isTFOMSUser() && $check){
			$query.=' and EPS.Lpu_id = :Lpu_id';
		}
		// echo getDebugSQL($query, array('EvnPS_id' => $data['EvnPS_id'], 'Lpu_id' => $data['Lpu_id'])); exit();
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
					-- and ESLast.EvnSection_Index = ESLast.EvnSection_Count - 1
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
					-- and ESLast.EvnSection_Index = ESLast.EvnSection_Count - 1
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
		if(is_object($result_diag_sop)){
			$result_temp[0]['LeaveDiagSop_Name'] = '';
			$result_temp[0]['LeaveDiagSop_Code'] = '';
			$result_diag_sop = $result_diag_sop->result('array');
			for($i=0;$i<count($result_diag_sop);$i++){
				$result_temp[0]['LeaveDiagSop_Name'] = $result_temp[0]['LeaveDiagSop_Name'] . $result_diag_sop[$i]['LeaveDiagSop_Name'] . ' <br /> ';
				$result_temp[0]['LeaveDiagSop_Code'] = $result_temp[0]['LeaveDiagSop_Code'] . $result_diag_sop[$i]['LeaveDiagSop_Code'] . ' <br /> ';
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
				$result_temp[0]['LeaveDiagAgg_Name'] = $result_temp[0]['LeaveDiagAgg_Name'] . $result_diag_osl[$i]['LeaveDiagAgg_Name'] . ' <br /> ';
				$result_temp[0]['LeaveDiagAgg_Code'] = $result_temp[0]['LeaveDiagAgg_Code'] . $result_diag_osl[$i]['LeaveDiagAgg_Code'] . ' <br /> ';
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
		if ( is_object($result) ) {
			$result = $result->result('array');
			$result[0]['Privileges'] = '';
			if(is_object($res_priv) && count($res_priv) > 0){
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
	protected function _printEvnPS($data,$response){
		$invalid_type_name = '';
		$template = 'evn_ps_template_list_a4_karelya';

		$evn_section_data = array();
		$evn_usluga_oper_data = array();
		$evn_usluga_oper_med_data = array();

		$allowPriem = !empty($response[0]['PrehospWaifRefuseCause_id']);

		$response_temp = $this->getEvnSectionData($data, $allowPriem);
		$EvnSection_IsAdultEscort = 1;

		// https://redmine.swan.perm.ru/issues/40196
		if ( $allowPriem === false && count($response_temp) > 0 ) {
			$response[0]['LpuSectionFirst_Name'] = $response_temp[0]['LpuSection_Name'];
			$response[0]['EvnSectionFirst_setDate'] = mb_substr($response_temp[0]['EvnSection_setDT'], 0, 10);
			$response[0]['EvnSectionFirst_setTime'] = mb_substr($response_temp[0]['EvnSection_setDT'], -5);
			$response[0]['LpuSectionBedProfile_Name'] = $response_temp[0]['LpuSectionBedProfile_Name'];
		}

		if ( is_array($response_temp) ) {
			$evn_section_data = $response_temp;

			for ( $i = 0; $i < (count($evn_section_data) < 2 ? 2 : count($evn_section_data)); $i++ ) {
				if((isset($evn_section_data[$i])) && ($evn_section_data[$i]['EvnSection_IsAdultEscort'] == 2)) { //http://redmine.swan.perm.ru/issues/29601
					$EvnSection_IsAdultEscort = 2;
				}

				if ( $i >= count($evn_section_data) ) {
					$evn_section_data[$i] = array(
						'Index' => $i + 1,
						'LpuSection_CodeName' => '&nbsp;',
						'EvnSection_setDT' => '&nbsp;',
						'EvnSection_disDT' => '&nbsp;',
						'EvnSectionDiagOsn_Code' => '&nbsp;',
						'EvnSectionMesOsn_Code' => '&nbsp;',
						'EvnSection_UKL' => '&nbsp;',
						'EvnSectionPayType_Name' => '&nbsp;',
						'LpuSectionBedProfile_Name' => '&nbsp;',
						'MedPersonal_FIO'  => '&nbsp;',
						'MPCode' => '&nbsp;'
					);
				} else {
					$evn_section_data[$i]['Index'] = $i + 1;
					if(!empty($evn_section_data[$i]['PayType_Name'])) { $evn_section_data[$i]['EvnSectionPayType_Name'] = $evn_section_data[$i]['PayType_Name']; }
				}
			}
			//В рамках задачи 29601 берем врача из последнего посещения и печатаем его как лечащего врача

			$MP_Last_FIO = (count($response_temp)>0)?$response_temp[count($response_temp)-1]['MedPersonal_FIO']:'';
			$MP_Last_Code = (count($response_temp)>0)?$response_temp[count($response_temp)-1]['MPCode']:'';
		}

		$response_temp = $this->getEvnUslugaOperData($data);

		if ( is_array($response_temp) ) {
			for ( $i = 0; $i < count($response_temp); $i++ ) {
				$evn_usluga_oper_data[] = array(
					'Number' => ($i+1),
					'EvnUslugaOper_setDT' => $response_temp[$i]['EvnUslugaOper_setDT'],
					'Oper_dur' => '&nbsp;<br />&nbsp;',
					'EvnUslugaOperMedPersonal_Code' => $response_temp[$i]['MedPersonal_Code'],
					'EvnUslugaOperLpuSection_Code' => $response_temp[$i]['LpuSection_Code'],
					'EvnUslugaOper_Name' => $response_temp[$i]['Usluga_Name'],
					'EvnUslugaOper_Code' => $response_temp[$i]['Usluga_Code'],
					'AggType_Name_1' => $response_temp[$i]['AggType_Name_1'],
					'AggType_Name_2' => $response_temp[$i]['AggType_Name_2'],
					'EvnUslugaOperAnesthesiaClass_Name' => $response_temp[$i]['AnesthesiaClass_Name'],
					'EvnUslugaOper_IsEndoskop' => $response_temp[$i]['EvnUslugaOper_IsEndoskop'] == 1 ? 'X' : '&nbsp;',
					'EvnUslugaOper_IsLazer' => $response_temp[$i]['EvnUslugaOper_IsLazer'] == 1 ? 'X' : '&nbsp;',
					'EvnUslugaOper_IsKriogen' => $response_temp[$i]['EvnUslugaOper_IsKriogen'] == 1 ? 'X' : '&nbsp;',
					'EvnUslugaOper_IsRadGraf' => $response_temp[$i]['EvnUslugaOper_IsRadGraf'] == 1 ? 'X' : '&nbsp;',
					'EvnUslugaOper_IsMicrSurg' => $response_temp[$i]['EvnUslugaOper_IsMicrSurg'] == 1 ? 'X' : '&nbsp;',
					'EvnUslugaOperPayType_Name' => $response_temp[$i]['PayType_Name']
				);
			}

			// https://redmine.swan.perm.ru/issues/6484
			// savage: Добавляем пустые строки в таблицу с хирургическими операциями, если количество операций меньше двух
			for ( $j = $i; $j < 3; $j++ ) {
				$evn_usluga_oper_data[] = array(
					'Number' => '&nbsp;<br />&nbsp;',
					'EvnUslugaOper_setDT' => '&nbsp;<br />&nbsp;',
					'Oper_dur' => '&nbsp;<br />&nbsp;',
					'EvnUslugaOperMedPersonal_Code' => '&nbsp;<br />&nbsp;',
					'EvnUslugaOperLpuSection_Code' => '&nbsp;<br />&nbsp;',
					'EvnUslugaOper_Name' => '&nbsp;<br />&nbsp;',
					'EvnUslugaOper_Code' => '&nbsp;<br />&nbsp;',
					'AggType_Name_1' => '&nbsp;<br />&nbsp;',
					'AggType_Name_2' => '&nbsp;<br />&nbsp;',
					'EvnUslugaOperAnesthesiaClass_Name' => '&nbsp;<br />&nbsp;',
					'EvnUslugaOper_IsEndoskop' => '&nbsp;',
					'EvnUslugaOper_IsLazer' => '&nbsp;',
					'EvnUslugaOper_IsKriogen' => '&nbsp;',
					'EvnUslugaOper_IsRadGraf' => '&nbsp;',
					'EvnUslugaOper_IsMicrSurg' => '&nbsp;',
					'EvnUslugaOperPayType_Name' => '&nbsp;<br />&nbsp;'
				);
			}
		}

		$response_temp = $this->getEvnUslugaOperMedDataKarelya($data);
		if ( is_array($response_temp) ) {
			for ( $i = 0; $i < count($response_temp); $i++ ) {
				$evn_usluga_oper_med_data[] = array(
					'Number' => ($i+1),
					'EvnUslugaOper_setDT' => $response_temp[$i]['EvnUslugaOper_setDT'],
					'OperSurgeon_Name' => $response_temp[$i]['OperSurgeon_Name'],
					'OperSurgeon_Code' => $response_temp[$i]['OperSurgeon_Code'],
					'OperAnesthetist_Name' => $response_temp[$i]['OperAnesthetist_Name'],
					'OperAnesthetist_Code' => $response_temp[$i]['OperAnesthetist_Code'],
					'Oper1Assistant_Name' => $response_temp[$i]['Oper1Assistant_Name'],
					'Oper1Assistant_Code' => $response_temp[$i]['Oper1Assistant_Code'],
					'Oper2Assistant_Name' => $response_temp[$i]['Oper2Assistant_Name'],
					'Oper2Assistant_Code' => $response_temp[$i]['Oper2Assistant_Code']
				);
			}

			// https://redmine.swan.perm.ru/issues/6484
			// savage: Добавляем пустые строки в таблицу с хирургическими операциями, если количество операций меньше двух
			for ( $j = $i; $j < 3; $j++ ) {
				$evn_usluga_oper_med_data[] = array(
					'Number' => '&nbsp;<br />&nbsp;',
					'EvnUslugaOper_setDT' => '&nbsp;<br />&nbsp;',
					'OperSurgeon_Name' => '&nbsp;<br />&nbsp;',
					'OperSurgeon_Code' => '&nbsp;<br />&nbsp;',
					'OperAnesthetist_Name' => '&nbsp;<br />&nbsp;',
					'OperAnesthetist_Code' => '&nbsp;<br />&nbsp;',
					'Oper1Assistant_Name' => '&nbsp;<br />&nbsp;',
					'Oper1Assistant_Code' => '&nbsp;<br />&nbsp;',
					'Oper2Assistant_Name' => '&nbsp;<br />&nbsp;',
					'Oper2Assistant_Code' => '&nbsp;<br />&nbsp;'
				);
			}
		}
		if(isset($response[0]['PrivilegeType_id'])){
			switch ( $response[0]['PrivilegeType_id'] ) {
				case 81:
					$invalid_type_name = "3-я группа";
					break;

				case 82:
					$invalid_type_name = "2-я группа";
					break;

				case 83:
					$invalid_type_name = "1-я группа";
					break;
			}
		}
		$count_preg = (isset($response[0]['BirthSpecStac_CountPregnancy'])) ? returnValidHTMLString($response[0]['BirthSpecStac_CountPregnancy']) : ''; //Беременность
		$print_data = array(
			'EvnPSTemplateTitle' => 'Печать карты выбывшего из стационара'
		,'EvnPS_NumCard' => (isset($response[0]['EvnPS_NumCard'])) ? returnValidHTMLString($response[0]['EvnPS_NumCard']) : ''
		,'PolisType_Name' => (isset($response[0]['PolisType_Name'])) ? returnValidHTMLString($response[0]['PolisType_Name']) : ''
		,'Polis_Num' => (isset($response[0]['Polis_Num'])) ? returnValidHTMLString($response[0]['Polis_Num']) : ''
		,'Polis_Ser' => (isset($response[0]['Polis_Ser'])) ? returnValidHTMLString($response[0]['Polis_Ser']) : ''
		,'OMSSprTerr_Code' => (isset($response[0]['OMSSprTerr_Code'])) ? returnValidHTMLString($response[0]['OMSSprTerr_Code']) : ''
		,'OrgSmo_Name' => (isset($response[0]['OrgSmo_Name'])) ? returnValidHTMLString($response[0]['OrgSmo_Name']) : ''
		,'Person_Fio' => (isset($response[0]['Person_Fio'])) ? returnValidHTMLString($response[0]['Person_Fio']) : ''
		,'Person_OKATO' => (isset($response[0]['Person_OKATO'])) ? returnValidHTMLString($response[0]['Person_OKATO']) : ''
		,'Sex_Name' => (isset($response[0]['Sex_Name'])) ? returnValidHTMLString($response[0]['Sex_Name']) : ''
		,'Person_Birthday' => (isset($response[0]['Person_Birthday'])) ? returnValidHTMLString($response[0]['Person_Birthday']) : ''
		,'Person_Age' => (isset($response[0]['Person_Age'])) ? returnValidHTMLString($response[0]['Person_Age']) : ''
		,'DocumentType_Name' => (isset($response[0]['DocumentType_Name'])) ? returnValidHTMLString($response[0]['DocumentType_Name']) : ''
		,'Document_Ser' => (isset($response[0]['Document_Ser'])) ? returnValidHTMLString($response[0]['Document_Ser']) : ''
		,'Document_Num' => (isset($response[0]['Document_Num'])) ? returnValidHTMLString($response[0]['Document_Num']) : ''
		,'KLAreaType_Name' => (isset($response[0]['KLAreaType_Name'])) ? returnValidHTMLString($response[0]['KLAreaType_Name']) : ''
		,'KLAreaType_id' => (isset($response[0]['KLAreaType_id'])) ? returnValidHTMLString($response[0]['KLAreaType_id']) : ''
		,'Person_Phone' => (isset($response[0]['Person_Phone'])) ? returnValidHTMLString($response[0]['Person_Phone']) : ''
		,'PAddress_Name' => (isset($response[0]['PAddress_Name'])) ? returnValidHTMLString($response[0]['PAddress_Name']) : ''
		,'UAddress_Name' => (isset($response[0]['UAddress_Name'])) ? returnValidHTMLString($response[0]['UAddress_Name']) : ''
		,'PayType_Name' => (isset($response[0]['PayType_Name'])) ? returnValidHTMLString($response[0]['PayType_Name']) : ''
		,'SocStatus_Name' => (isset($response[0]['SocStatus_Name'])) ? returnValidHTMLString($response[0]['SocStatus_Name']) : ''
		,'PrivilegeType_Name' => (isset($response[0]['Privileges'])) ? returnValidHTMLString($response[0]['Privileges']) : ''
		,'InvalidType_Name' => returnValidHTMLString($invalid_type_name)
		,'PrehospOrg_Name' => (isset($response[0]['PrehospOrg_Name'])) ? returnValidHTMLString($response[0]['PrehospOrg_Name']) : ''
		,'EvnPS_CodeConv' => (isset($response[0]['EvnPS_CodeConv'])) ? returnValidHTMLString($response[0]['EvnPS_CodeConv']) : ''
		,'EvnPS_NumConv' => (isset($response[0]['EvnPS_NumConv'])) ? returnValidHTMLString($response[0]['EvnPS_NumConv']) : ''
		,'EvnDirection_Num' => (isset($response[0]['EvnDirection_Num'])) ? returnValidHTMLString($response[0]['EvnDirection_Num']) : ''
		,'EvnDirection_SetDT' => (isset($response[0]['EvnDirection_SetDT'])) ? returnValidHTMLString($response[0]['EvnDirection_SetDT']) : ''
		,'IsRecruit' => (isset($response[0]['SocStatus_SysNick'])) ? ((returnValidHTMLString($response[0]['SocStatus_SysNick'])=='priz')?1:2) : ''
		,'PrehospArrive_Name' => (isset($response[0]['PrehospArrive_Name'])) ? returnValidHTMLString($response[0]['PrehospArrive_Name']) : ''
		,'PersonCard_Code' => (isset($response[0]['PersonCard_Code'])) ? returnValidHTMLString($response[0]['PersonCard_Code']) : ''
		,'Lpu_Name' => (isset($response[0]['Lpu_Name'])) ? returnValidHTMLString($response[0]['Lpu_Name']) : ''
		,'PrehospDiag_Name' => (isset($response[0]['PrehospDiag_Name'])) ? returnValidHTMLString($response[0]['PrehospDiag_Name']) : ''
		,'AdmitDiag_Name' => (isset($response[0]['AdmitDiag_Name'])) ? returnValidHTMLString($response[0]['AdmitDiag_Name']) : ''
		,'PrehospToxic_Name' => (isset($response[0]['PrehospToxic_Name'])) ? returnValidHTMLString($response[0]['PrehospToxic_Name']) : ''
		,'LpuSectionTransType_Name' => (isset($response[0]['LpuSectionTransType_Name'])) ? returnValidHTMLString($response[0]['LpuSectionTransType_Name']) : ''
		,'PrehospType_Name' => (isset($response[0]['PrehospType_Name'])) ? returnValidHTMLString($response[0]['PrehospType_Name']) : ''
		,'EvnPS_HospCount' => (isset($response[0]['EvnPS_HospCount'])) ? returnValidHTMLString($response[0]['EvnPS_HospCount']) : ''
		,'IsFirst' => (isset($response[0]['IsFirst'])) ? returnValidHTMLString($response[0]['IsFirst']) : ''
		,'PrehospType_Code' => (isset($response[0]['PrehospType_Code'])) ? returnValidHTMLString($response[0]['PrehospType_Code']) : ''
		,'EvnPS_TimeDesease' => (isset($response[0]['EvnPS_TimeDesease'])) ? returnValidHTMLString($response[0]['EvnPS_TimeDesease']) : ''
		,'EvnPS_TimeDeseaseType' => (isset($response[0]['EvnPS_TimeDeseaseType'])) ? returnValidHTMLString($response[0]['EvnPS_TimeDeseaseType']) : ''
		,'PrehospTrauma_Name' => (isset($response[0]['PrehospTrauma_Name'])) ? returnValidHTMLString($response[0]['PrehospTrauma_Name']) : ''
		,'EvnPS_setDate' => (isset($response[0]['EvnPS_setDate'])) ? returnValidHTMLString($response[0]['EvnPS_setDate']) : ''
		,'EvnPS_setTime' => (isset($response[0]['EvnPS_setTime'])) ? returnValidHTMLString($response[0]['EvnPS_setTime']) : ''
		,'LpuSectionFirst_Name' => (isset($response[0]['LpuSectionFirst_Name'])) ? returnValidHTMLString($response[0]['LpuSectionFirst_Name']) : ''
		,'MedPersonal_TabCode' => (isset($response[0]['MedPersonal_TabCode'])) ? returnValidHTMLString($response[0]['MedPersonal_TabCode']) : ''
		,'MPFirst_Code' => (isset($response[0]['MPFirst_Code'])) ? returnValidHTMLString($response[0]['MPFirst_Code']) : ''
		,'LpuSectionBedProfile_Name_Beg' => (isset($response[0]['LpuSectionBedProfile_Name'])) ? returnValidHTMLString($response[0]['LpuSectionBedProfile_Name']) : ''
		,'MedPerson_FIO' => (isset($response[0]['MedPerson_FIO'])) ? returnValidHTMLString($response[0]['MedPerson_FIO']) : ''
		,'OrgHead_FIO' => (isset($response[0]['OrgHead_FIO'])) ? returnValidHTMLString($response[0]['OrgHead_FIO']) : ''
		,'OrgHead_Code' => (isset($response[0]['OrgHead_Code'])) ? returnValidHTMLString($response[0]['OrgHead_Code']) : ''
		,'EvnSectionFirst_setDate' => (isset($response[0]['EvnSectionFirst_setDate'])) ? returnValidHTMLString($response[0]['EvnSectionFirst_setDate']) : ''
		,'EvnSectionFirst_setTime' => (isset($response[0]['EvnSectionFirst_setTime'])) ? returnValidHTMLString($response[0]['EvnSectionFirst_setTime']) : ''
		,'EvnPS_disDate' => (isset($response[0]['EvnPS_disDate'])) ? returnValidHTMLString($response[0]['EvnPS_disDate']) : ''
		,'EvnPS_disTime' => (isset($response[0]['EvnPS_disTime'])) ? returnValidHTMLString($response[0]['EvnPS_disTime']) : ''
		,'EvnPS_KoikoDni' => (isset($response[0]['EvnPS_KoikoDni'])) ? ((returnValidHTMLString($response[0]['EvnPS_KoikoDni'])==0)?1:(returnValidHTMLString($response[0]['EvnPS_KoikoDni']))) : ''
		,'LeaveType_Name' => (isset($response[0]['LeaveType_Name'])) ? returnValidHTMLString($response[0]['LeaveType_Name']) : ''
		,'LeaveType_Code' => (isset($response[0]['LeaveType_Code'])) ? returnValidHTMLString($response[0]['LeaveType_Code']) : ''
		,'LeaveCause_Code' => (isset($response[0]['LeaveCause_Code'])) ? returnValidHTMLString($response[0]['LeaveCause_Code']) : ''
		,'ResultDesease_Name' => (isset($response[0]['ResultDesease_Name'])) ? returnValidHTMLString($response[0]['ResultDesease_Name']) : ''
		,'ResultDesease_Code' => (isset($response[0]['ResultDesease_Code'])) ? returnValidHTMLString($response[0]['ResultDesease_Code']) : ''
		,'EvnStick_setDate' => (isset($response[0]['EvnStick_setDate'])) ? returnValidHTMLString($response[0]['EvnStick_setDate']) : ''
		,'EvnStick_disDate' => (isset($response[0]['StickLeaveType_id'])&&!empty($response[0]['StickLeaveType_id'])) ? returnValidHTMLString($response[0]['EvnStick_disDate']) : ''
		,'PersonCare_Age' => (isset($response[0]['PersonCare_Age'])) ? returnValidHTMLString($response[0]['PersonCare_Age']) : ''
		,'PersonCare_SexName' => (isset($response[0]['PersonCare_SexName'])) ? returnValidHTMLString($response[0]['PersonCare_SexName']) : ''
		,'PersonCare_SexId' => (isset($response[0]['PersonCare_SexId'])) ? returnValidHTMLString($response[0]['PersonCare_SexId']) : ''
		,'EvnSectionData' => $evn_section_data
		,'EvnUslugaOperData' => $evn_usluga_oper_data
		,'EvnUslugaOperMedData' => $evn_usluga_oper_med_data
		,'LeaveDiag_Code' => (isset($response[0]['LeaveDiag_Code'])) ? returnValidHTMLString($response[0]['LeaveDiag_Code']) : ''
		,'LeaveDiag_Name' => (isset($response[0]['LeaveDiag_Name'])) ? returnValidHTMLString($response[0]['LeaveDiag_Name']) : ''
		,'LeaveDiagAgg_Code' => (isset($response[0]['LeaveDiagAgg_Code'])) ? $response[0]['LeaveDiagAgg_Code'] : ''//returnValidHTMLString($response[0]['LeaveDiagAgg_Code'])
		,'LeaveDiagAgg_Name' => (isset($response[0]['LeaveDiagAgg_Name'])) ? $response[0]['LeaveDiagAgg_Name'] : ''//returnValidHTMLString($response[0]['LeaveDiagAgg_Name'])
		,'LeaveDiagSop_Code' => (isset($response[0]['LeaveDiagSop_Code'])) ? $response[0]['LeaveDiagSop_Code'] : ''//returnValidHTMLString($response[0]['LeaveDiagSop_Code'])
		,'LeaveDiagSop_Name' => (isset($response[0]['LeaveDiagSop_Name'])) ? $response[0]['LeaveDiagSop_Name'] : ''//returnValidHTMLString($response[0]['LeaveDiagSop_Name'])
		,'AnatomDiag_Code' => (isset($response[0]['AnatomDiag_Code'])) ? returnValidHTMLString($response[0]['AnatomDiag_Code']) : ''
		,'AnatomDiag_Name' => (isset($response[0]['AnatomDiag_Name'])) ? returnValidHTMLString($response[0]['AnatomDiag_Name']) : ''
		,'AnatomDiagAgg_Code' => (isset($response[0]['AnatomDiagAgg_Code'])) ? returnValidHTMLString($response[0]['AnatomDiagAgg_Code']) : ''
		,'AnatomDiagAgg_Name' => (isset($response[0]['AnatomDiagAgg_Name'])) ? returnValidHTMLString($response[0]['AnatomDiagAgg_Name']) : ''
		,'AnatomDiagSop_Code' => (isset($response[0]['AnatomDiagSop_Code'])) ? returnValidHTMLString($response[0]['AnatomDiagSop_Code']) : ''
		,'AnatomDiagSop_Name' => (isset($response[0]['AnatomDiagSop_Name'])) ? returnValidHTMLString($response[0]['AnatomDiagSop_Name']) : ''
		,'EvnPS_IsDiagMismatch' => (isset($response[0]['EvnPS_IsDiagMismatch1'])) ? returnValidHTMLString($response[0]['EvnPS_IsDiagMismatch1']) : ''
		,'EvnPS_IsImperHosp' => (isset($response[0]['EvnPS_IsImperHosp1'])) ? returnValidHTMLString($response[0]['EvnPS_IsImperHosp1']) : ''
		,'EvnPS_IsShortVolume' => (isset($response[0]['EvnPS_IsShortVolume1'])) ? returnValidHTMLString($response[0]['EvnPS_IsShortVolume1']) : ''
		,'EvnPS_IsWrongCure' => (isset($response[0]['EvnPS_IsWrongCure1'])) ? returnValidHTMLString($response[0]['EvnPS_IsWrongCure1']) : ''
		,'BirthSpecStac_OutcomPeriod' => (isset($response[0]['BirthSpecStac_OutcomPeriod'])) ? returnValidHTMLString($response[0]['BirthSpecStac_OutcomPeriod']) : ''
		,'BirthSpecStac_CountPregnancy' => isset($response[0]['BirthSpecStac_CountPregnancy'])?($count_preg==1?1:2):0
		,'MP_Last_FIO' => returnValidHTMLString($MP_Last_FIO)
		,'MP_Last_Code' => returnValidHTMLString($MP_Last_Code)
		,'EvnSection_IsAdultEscort' => $EvnSection_IsAdultEscort
		);

		$html = $this->parser->parse($template, $print_data, !empty($data['returnString']));
		if (!empty($data['returnString'])) {
			return array('html' => $html);
		} else {
			return $html;
		}
	}
}
