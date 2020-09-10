<?php	defined('BASEPATH') or die ('No direct script access allowed');

require_once(APPPATH.'models/_pgsql/EvnPS_model.php');

class Astra_EvnPS_model extends EvnPS_model {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * @return array
	 */
	protected function _getSaveInputRules()
	{
		$all = parent::_getSaveInputRules();

		$all['EvnPS_isMseDirected'] = array(
			'field' => 'EvnPS_isMseDirected',
			'label' => 'Пациент направлен на МСЭ',
			'rules' => '',
			'type' => 'swcheckbox'
		);

		return $all;
	}

	/**
	 * Возвращает список всех используемых ключей атрибутов объекта
	 * @return array
	 */
	static function defAttributes()
	{
		$arr = parent::defAttributes();

		$arr['evnps_ismsedirected'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnPS_isMseDirected',
			'label' => 'Пациент направлен на МСЭ',
			'save' => '',
			'type' => 'swcheckbox'
		);

		return $arr;
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

		$query = "
			select
				lp.Lpu_id as \"Lpu_id\",
				lp.Lpu_f003mcod as \"fcode\"
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

		$query = "
			select
				coalesce((
				Select
					xmlagg( xmlelement(name NPR,
					xmlelement (name N_NPR,coalesce(npr.EvnNPR_NPRID, npr_eps.EvnNPR_NPRID, ext_npr.EvnDirectionExt_NPRID, 0)), --Int
					xmlelement (name N_NPR_LPU,cast(DATE_PART('year',ed.EvnDirection_setDT) as varchar) ||cast(lp.Lpu_f003mcod as varchar)||cast(ed.EvnDirection_Num as varchar)), --T(16) Номер направления (MCOD+NP)
					xmlelement (name D_NPR,cast(ed.EvnDirection_setDT as date)),
					xmlelement (name D_NPR,case when dt.DirType_Code = 5 then 2 else coalesce(dt.DirType_Code,1) end) ,-- Int - Форма оказания медицинской помощи
					xmlelement (name NCODE_MO,lp.Lpu_f003mcod), -- T(6)-- numeric(6, 0) - МО, направившая на госпитализацию (реестровый номер, F003)
					xmlelement (name NLPU_1,lb.LpuBuilding_Code), -- T(8)-- numeric(4, 0) - Код подразделения медицинской организации, направившей на госпитализацию
					xmlelement (name DCODE_MO,coalesce(lp1.Lpu_f003mcod, '0')), -- T(6)-- numeric(6, 0) - МО, куда направлен пациент (реестровый номер, F003)
					xmlelement (name DLPU_1,lb1.LpuBuilding_Code ), -- T(8) Код подразделения медицинской организации, куда выписано направление
					xmlelement (name VPOLIS,pt.PolisType_CodeF008 ),-- numeric(1, 0) , -- Тип документа, подтверждающего факт страхования по ОМС (F008)
					xmlelement (name SPOLIS,case when pt.PolisType_CodeF008 in (1,2) then po.Polis_Ser else Null end ), -- Varchar(10) - Серия документа, подтверждающего факт страхования по ОМС
					xmlelement (name NPOLIS,case when pt.PolisType_CodeF008=3 then polisInfo.edNum else po.Polis_Num end ), -- Numeric(20) - Номер документа, подтверждающего факт страхования по ОМС
					xmlelement (name SMO,smo.Orgsmo_f002smocod ), -- numeric(6, 0) - СМО (реестровый номер, F002)
					xmlelement (name SMO_OGRN,org.Org_OGRN), --Т(15) ОГРН СМО
					xmlelement (name SMO_OK,org.Org_OKATO) , --Т(5) ОКАТО территории страхования
					xmlelement (name SMO_NAM,case when org.Org_OGRN is null and org.Org_OKATO is null then coalesce(org.Org_Name, smo.OrgSMO_Name) else Null end), --Т(100) Наименование СМО
					xmlelement (name FAM,pe.Person_SurName), --varchar(30) – Фамилия
					xmlelement (name IM,pe.Person_FirName ), --varchar(30) – Имя
					xmlelement (name OT,pe.Person_SecName), --varchar(30) – Отчество
					xmlelement (name W,case when pe.Sex_id=3 then 1 else pe.Sex_id end ), --numeric(1) – пол (1 - муж, 2 - жен)
					xmlelement (name DR,cast(pe.Person_BirthDay as date) ), --date – дата рождения
					xmlelement (name CT,case when len(coalesce(pe.PersonPhone_Phone, '')) = 0 then 'телефон не указан' else pe.PersonPhone_Phone end), --Varchar(100) - Контактная информация
					xmlelement (name DS1,coalesce(di.Diag_Code, '') ), --Char(4) - Код диагноза по МКБ
					xmlelement (name USL_OK,case
					when lut1.LpuUnitType_SysNick = 'stac' then 1
					when lut1.LpuUnitType_SysNick in ('dstac','hstac','pstac') then 2
					end),
					xmlelement (name DET,case when dbo.Age2(pe.Person_BirthDay, ed.EvnDirection_setDT) < 18 then 1 else 0 end),
					xmlelement (name PROFIL,lsp.LpuSectionProfile_Code), --numeric(4, 0) - Профиль койки
					xmlelement (name PODR,ls1.LpuSection_Code ), --numeric(4, 0) - Код отделения (профиль)
					xmlelement (name IDDOKT,substring(mp.Person_Snils,1,3)||'-'||substring(mp.Person_Snils,4,3)||'-'||substring(mp.Person_Snils,7,3)||' '||substring(mp.Person_Snils,10,2)), --Varchar(16) - Снилс медицинского работника, направившего больного
					xmlelement (name DATE_1, cast(coalesce(tt.TimetableStac_setDate, ed.EvnDirection_desDT) as date)))) -- date - Плановая дата госпитализации
				from dbo.v_EvnDirection ed
					left join dbo.v_EvnQueue eq on eq.EvnDirection_id = ed.EvnDirection_id
					left join dbo.v_EvnPS eps on eps.EvnDirection_id = ed.EvnDirection_id
					left join v_EvnDirectionExt ext_npr on ext_npr.EvnDirection_id = ed.EvnDirection_id
					left join r30.v_EvnNPR npr on npr.Evn_id = ed.EvnDirection_id
					left join r30.v_EvnNPR npr_eps on npr_eps.Evn_id = eps.EvnPS_id
					left join dbo.v_DirType dt on dt.DirType_id = ed.DirType_id
					left join dbo.v_PrehospType prt on ed.PrehospType_did=prt.PrehospType_id
					inner join v_lpu lp on lp.Lpu_id = coalesce(ed.Lpu_sid, ed.Lpu_id)
					left join dbo.v_LpuSection ls on ls.LpuSection_id = ed.LpuSection_id
					left join dbo.v_LpuUnit lu on ls.LpuUnit_id=lu.LpuUnit_id
					left join dbo.v_LpuBuilding lb on lb.LpuBuilding_id = lu.LpuBuilding_id
					inner join dbo.v_MedPersonal mp on ed.MedPersonal_id=mp.MedPersonal_id
						and mp.Lpu_id = lp.Lpu_id
					left join dbo.v_TimetableStac_lite tt on tt.EvnDirection_id = ed.EvnDirection_id
					inner join dbo.v_Diag di on di.Diag_id = ed.Diag_id
					inner join v_lpu lp1 on lp1.Lpu_id = ed.Lpu_did
					left join dbo.v_LpuSection ls1 on ls1.LpuSection_id = ed.LpuSection_did
					left join dbo.v_LpuUnit lu1 on ls1.LpuUnit_id=lu1.LpuUnit_id
					left join dbo.v_LpuUnitType lut1 on lut1.LpuUnitType_id=coalesce(ed.LpuUnitType_id,lu1.LpuUnitType_id)
					left join dbo.v_LpuBuilding lb1 on lb1.LpuBuilding_id = lu1.LpuBuilding_id
					left join v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = coalesce(ls1.LpuSectionProfile_id,ed.LpuSectionProfile_id)
					left join dbo.v_LpuSectionBedProfile lsb on lsb.LpuSectionBedProfile_id = ls1.LpuSectionBedProfile_id
					--пациент
					inner join dbo.v_Person_all pe on pe.PersonEvn_id = ed.PersonEvn_id
						and pe.Server_id = ed.Server_id
					--представитель пациента
					left join dbo.v_PersonDeputy pd on pd.Person_id = pe.Person_id
					left join lateral (
						select
							pe.Person_id,
							pe.Polis_id,
							pe.Person_EdNum
						from dbo.v_Person_all pe
						where pe.Person_id = pd.Person_pid
							and pe.PersonEvn_begDT <= ed.EvnDirection_insDT
						order by
							pe.PersonEvn_insDT desc
						limit 1
					) d_pe on true
					--идентификатор полиса пациента или его представителя
					left join lateral(
						select
							case when dbo.Age2(pe.Person_BirthDay, ed.EvnDirection_setDT) < 18 and pe.Polis_id is null and d_pe.Person_id is not null
								then d_pe.Polis_id else pe.Polis_id end as Polis_id,
							case when dbo.Age2(pe.Person_BirthDay, ed.EvnDirection_setDT) < 18 and pe.Polis_id is null and d_pe.Person_id is not null
								then d_pe.Person_EdNum else pe.Person_EdNum end as edNum
					) polisInfo on true
					--данные полиса
					left join dbo.v_Polis po on po.Polis_id = polisInfo.Polis_id
					left join dbo.PolisType pt on pt.PolisType_id = po.PolisType_id and coalesce(pt.PolisType_CodeF008,0)<>0
					left join dbo.v_OrgSMO smo on smo.OrgSMO_id = po.OrgSmo_id
					left join dbo.v_Org org on org.Org_id = smo.Org_id
				where (1=1)
					and ed.DirType_id in (1,5)
					and ed.PayType_id = (select PayType_id from v_PayType where PayType_SysNick = 'oms' limit 1)
					and (:Lpu_id is null or coalesce(ed.Lpu_sid, ed.Lpu_id)=:Lpu_id)
					and ed.EvnDirection_setDT>=dateadd('day', 2, cast(to_char(:Date,'dd.mm.yyyy')||' '||:startTime as timestamp)) and ed.EvnDirection_setDT<=dateadd('day', -1, cast(to_char(:Date,'dd.mm.yyyy')||' '||:finalTime as timestamp))
				),'') as N1, --GetHospPlan
				coalesce ((
				select
					xmlagg( xmlelement(name NPR,
					xmlelement (name N_NPR,coalesce(npr.EvnNPR_NPRID, npr_eps.EvnNPR_NPRID, ext_npr.EvnDirectionExt_NPRID, 0)), --Int
					xmlelement (name D_NPR,cast(coalesce(ed.EvnDirection_setDT, eps.EvnDirection_setDT) as date) ),
					xmlelement (name FOR_POM,case when prt.PrehospType_Code in (2,3) then 2 else coalesce(prt.PrehospType_Code,1) end ), - Int - Форма оказания медицинской помощи
					xmlelement (name DCODE_MO,lp.Lpu_f003mcod), -- T(6)-- numeric(6, 0) - МО госпитализации (реестровый номер, F003)
					xmlelement (name DLPU_1,lb.LpuBuilding_Code), -- T(8) Код подразделения МО госпитализации
					xmlelement (name NCODE_MO,lp1.Lpu_f003mcod), -- T(6)-- numeric(6, 0) - Код подразделения МО, создавшей направление (реестровый номер, F003)
					xmlelement (name NLPU_1,lb1.LpuBuilding_Code), -- T(8)-- numeric(4, 0) - Код подразделения МО, создавшей направление
					xmlelement (name DATE_1,cast(eps.EvnPS_setDate as date)), --Дата фактической госпитализации
					xmlelement (name TIME_1,to_char(eps.EvnPS_setTime, 'hh24:mi')), --Время фактической госпитализации
					xmlelement (name VPOLIS,pt.PolisType_CodeF008),-- numeric(1, 0) , -- Тип документа, подтверждающего факт страхования по ОМС (F008)
					xmlelement (name SPOLIS,case when pt.PolisType_CodeF008 in (1,2) then po.Polis_Ser else Null end ), -- Varchar(10) - Серия документа, подтверждающего факт страхования по ОМС
					xmlelement (name NPOLIS,case when pt.PolisType_CodeF008=3 then polisInfo.edNum else po.Polis_Num end ), -- Numeric(20) - Номер документа, подтверждающего факт страхования по ОМС
					xmlelement (name FAM,pe.Person_SurName), --varchar(30) – Фамилия
					xmlelement (name IM,pe.Person_FirName), --varchar(30) – Имя
					xmlelement (name OT,pe.Person_SecName), --varchar(30) – Отчество
					xmlelement (name W,case when pe.Sex_id=3 then 1 else pe.Sex_id end), --numeric(1) – пол (1 - муж, 2 - жен)
					xmlelement (name DR,cast(pe.Person_BirthDay as date)), --date – дата рождения
					xmlelement (name USL_OK,case
					when lu.LpuUnitType_SysNick = 'stac' then 1
					when lu.LpuUnitType_SysNick in ('dstac','hstac','pstac') then 2
					end),
					xmlelement (name DET,case when dbo.Age2(pe.Person_BirthDay, eps.EvnPS_setDT) < 18 then 1 else 0 end),
					xmlelement (name PROFIL,lsp.LpuSectionProfile_Code), --numeric(4, 0) - Профиль койки
					xmlelement (name PODR,ls.LpuSection_Code), --numeric(4, 0) - Код отделения (профиль)
					xmlelement (name NHISTORY,eps.EvnPS_NumCard), --Номер карты стационарного больного
					xmlelement (name DS1,coalesce(di.Diag_Code, dirdi.Diag_Code, '') ) --Char(4) - Диагноз приемного отделения
					))
				from dbo.v_EvnPS eps
					left join dbo.v_EvnDirection ed on ed.EvnDirection_id = eps.EvnDirection_id
					left join v_EvnDirectionExt ext_npr on ext_npr.EvnDirection_id = eps.EvnDirection_id
					left join r30.v_EvnNPR npr on npr.Evn_id = eps.EvnDirection_id
					left join r30.v_EvnNPR npr_eps on npr_eps.Evn_id = eps.EvnPS_id
					left join dbo.v_PrehospType prt on eps.PrehospType_id=prt.PrehospType_id
					inner join v_lpu lp on lp.Lpu_id = eps.Lpu_id
					inner join dbo.v_LpuSection ls on ls.LpuSection_id = eps.LpuSection_id
					inner join dbo.v_LpuUnit lu on ls.LpuUnit_id=lu.LpuUnit_id
					inner join dbo.v_LpuBuilding lb on lb.LpuBuilding_id = lu.LpuBuilding_id
					left join dbo.v_LpuSectionBedProfile lsb on lsb.LpuSectionBedProfile_id = ls.LpuSectionBedProfile_id
					left join v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
					left join dbo.v_LpuSection ls1 on ls1.LpuSection_id = coalesce(ed.LpuSection_id, eps.LpuSection_did)
					left join dbo.v_LpuUnit lu1 on ls1.LpuUnit_id=lu1.LpuUnit_id
					left join dbo.v_LpuBuilding lb1 on lb1.LpuBuilding_id = lu1.LpuBuilding_id
					inner join dbo.v_lpu lp1 on lp1.Lpu_id = coalesce(ed.Lpu_sid, ed.Lpu_id, eps.Lpu_did, ls1.Lpu_id)
					left join dbo.Diag di on di.Diag_id = coalesce(eps.Diag_pid,eps.Diag_id)
					left join dbo.Diag dirdi on dirdi.Diag_id = ed.Diag_id
					--пациент
					inner join dbo.v_Person_all pe on pe.PersonEvn_id = eps.PersonEvn_id
						and pe.Server_id = eps.Server_id
					--представитель пациента
					left join dbo.v_PersonDeputy pd on pd.Person_id = pe.Person_id
					left join lateral (
						select
							pe.Person_id,
							pe.Polis_id,
							pe.Person_EdNum
						from dbo.v_Person_all pe
						where pe.Person_id = pd.Person_pid
							and pe.PersonEvn_begDT <= eps.EvnPS_insDT
						order by
							pe.PersonEvn_insDT desc
						limit 1
					) d_pe on true
					--идентификатор полиса пациента или его представителя
					left join lateral(
					select
						case when dbo.Age2(pe.Person_BirthDay, eps.EvnPS_setDT) < 18 and pe.Polis_id is null and d_pe.Person_id is not null
							then d_pe.Polis_id else pe.Polis_id end as Polis_id,
						case when dbo.Age2(pe.Person_BirthDay, eps.EvnPS_setDT) < 18 and pe.Polis_id is null and d_pe.Person_id is not null
							then d_pe.Person_EdNum else pe.Person_EdNum end as edNum
					) polisInfo on true
					--данные полиса
					left join dbo.v_Polis po on po.Polis_id = polisInfo.Polis_id
					left join dbo.PolisType pt on pt.PolisType_id = po.PolisType_id
				where (1=1)
					and (:Lpu_id is null or eps.Lpu_id=:Lpu_id)
					and eps.PayType_id = (select PayType_id from v_PayType where PayType_SysNick = 'oms' limit 1)
					and eps.EvnPS_setDT>=dateadd('day', 2, cast(to_char(:Date,'dd.mm.yyyy')||' '||:startTime as timestamp))
					and eps.EvnPS_setDT<=dateadd('day', -1, cast(to_char(:Date,'dd.mm.yyyy')||' '||:finalTime as timestamp))
					and eps.PrehospWaifRefuseCause_id is null and prt.PrehospType_Code=1
					and (ed.EvnDirection_id is not null or eps.EvnDirection_setDT is not null)
				), '') as N2, --GetHospEmerg
				coalesce((
				select
					xmlagg( xmlelement(name NPR,
					xmlelement (name N_NPR,coalesce(npr.EvnNPR_NPRID, npr_eps.EvnNPR_NPRID, 0)), --Int
					xmlelement (name DCODE_MO,lp.Lpu_f003mcod ), - T(6)-- numeric(6, 0) - МО госпитализации (реестровый номер, F003)
					xmlelement (name DLPU_1,lb.LpuBuilding_Code), -- T(8) Код подразделения МО госпитализации
					xmlelement (name DATE_1,cast(eps.EvnPS_setDate as date)), --Дата фактической госпитализации
					xmlelement (name TIME_1,to_char(eps.EvnPS_setTime, 'hh24:mi')), --Время фактической госпитализации
					xmlelement (name VPOLIS,pt.PolisType_CodeF008),-- numeric(1, 0) , -- Тип документа, подтверждающего факт страхования по ОМС (F008)
					xmlelement (name SPOLIS,case when pt.PolisType_CodeF008 in (1,2) then po.Polis_Ser else Null end ), -- Varchar(10) - Серия документа, подтверждающего факт страхования по ОМС
					xmlelement (name NPOLIS,case when pt.PolisType_CodeF008=3 then polisInfo.edNum else po.Polis_Num end), -- Numeric(20) - Номер документа, подтверждающего факт страхования по ОМС
					xmlelement (name SMO,smo.Orgsmo_f002smocod), -- numeric(6, 0) - СМО (реестровый номер, F002)
					xmlelement (name SMO_OGRN,org.Org_OGRN ), --Т(15) ОГРН СМО
					xmlelement (name SMO_OK,org.Org_OKATO), --Т(5) ОКАТО территории страхования
					xmlelement (name SMO_NAM,case when org.Org_OGRN is null and smo.Orgsmo_f002smocod is null then coalesce(org.Org_Name, smo.OrgSMO_Name) else Null end), --Т(100) Наименование СМО
					xmlelement (name FAM,pe.Person_SurName), --varchar(30) – Фамилия
					xmlelement (name IM,pe.Person_FirName), --varchar(30) – Имя
					xmlelement (name OT,pe.Person_SecName), --varchar(30) – Отчество
					xmlelement (name W,case when pe.Sex_id=3 then 1 else pe.Sex_id end), --numeric(1) – пол (1 - муж, 2 - жен)
					xmlelement (name DR,cast(pe.Person_BirthDay as date)), --date – дата рождения
					xmlelement (name USL_OK,case
					when lu.LpuUnitType_SysNick = 'stac' then 1
					when lu.LpuUnitType_SysNick in ('dstac','hstac','pstac') then 2
					end),
					xmlelement (name DET,case when dbo.Age2(pe.Person_BirthDay, eps.EvnPS_setDT) < 18 then 1 else 0 end),
					xmlelement (name PROFIL,lsp.LpuSectionProfile_Code), --numeric(4, 0) - Профиль койки
					xmlelement (name PODR, ls.LpuSection_Code), --numeric(4, 0) - Код отделения (профиль)
					xmlelement (name NHISTORY,eps.EvnPS_NumCard), --Номер карты стационарного больного
					xmlelement (name DS1,coalesce(di.Diag_Code, dirdi.Diag_Code, '')) --Char(4) - Диагноз приемного отделения
					))
				from dbo.v_EvnPS eps
					left join dbo.v_EvnDirection ed on ed.EvnDirection_id = eps.EvnDirection_id
					--left join v_EvnDirectionExt ext_npr on eps.EvnPS_id=npr.Evn_id
					left join r30.v_EvnNPR npr on npr.Evn_id = eps.EvnDirection_id
					left join r30.v_EvnNPR npr_eps on npr_eps.Evn_id = eps.EvnPS_id
					left join dbo.v_PrehospType prt on eps.PrehospType_id=prt.PrehospType_id
					inner join v_lpu lp on lp.Lpu_id = eps.Lpu_id
					inner join dbo.v_LpuSection ls on ls.LpuSection_id = eps.LpuSection_id
					inner join dbo.v_LpuUnit lu on ls.LpuUnit_id=lu.LpuUnit_id
					inner join dbo.v_LpuBuilding lb on lb.LpuBuilding_id = lu.LpuBuilding_id
					left join dbo.v_LpuSectionBedProfile lsb on lsb.LpuSectionBedProfile_id = ls.LpuSectionBedProfile_id
					left join v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
					left join dbo.Diag di on di.Diag_id = coalesce(eps.Diag_pid,eps.Diag_id)
					left join dbo.Diag dirdi on dirdi.Diag_id = ed.Diag_id
					--пациент
					inner join dbo.v_Person_all pe on pe.PersonEvn_id = eps.PersonEvn_id
						and pe.Server_id = eps.Server_id
					--представитель пациента
					left join dbo.v_PersonDeputy pd on pd.Person_id = pe.Person_id
					left join lateral (
						select
							pe.Person_id,
							pe.Polis_id,
							pe.Person_EdNum
						from dbo.v_Person_all pe
						where pe.Person_id = pd.Person_pid
							and pe.PersonEvn_begDT <= eps.EvnPS_insDT
						order by pe.PersonEvn_insDT desc
						limit 1
					) d_pe on true
					--идентификатор полиса пациента или его представителя
					left join lateral(
					select
						case when dbo.Age2(pe.Person_BirthDay, eps.EvnPS_setDT) < 18 and pe.Polis_id is null and d_pe.Person_id is not null
							then d_pe.Polis_id else pe.Polis_id end as Polis_id,
						case when dbo.Age2(pe.Person_BirthDay, eps.EvnPS_setDT) < 18 and pe.Polis_id is null and d_pe.Person_id is not null
							then d_pe.Person_EdNum else pe.Person_EdNum end as edNum
					) polisInfo on true
					--данные полиса
					left join dbo.v_Polis po on po.Polis_id = polisInfo.Polis_id
					left join dbo.PolisType pt on pt.PolisType_id = po.PolisType_id and coalesce(pt.PolisType_CodeF008,0)<>0
					left join dbo.v_OrgSMO smo on smo.OrgSMO_id = po.OrgSmo_id
					left join dbo.v_Org org on org.Org_id = smo.Org_id
				where (1=1)
					and (:Lpu_id is null or eps.Lpu_id=:Lpu_id)
					and eps.PayType_id = (select PayType_id from v_PayType where PayType_SysNick = 'oms' limit 1)
					and eps.EvnPS_setDT>=dateadd('day', 2, cast(to_char(:Date,'dd.mm.yyyy')||' '||:startTime as timestamp)) and eps.EvnPS_setDT<=dateadd('day', -1, cast(to_char(:Date,'dd.mm.yyyy')||' '||:finalTime as timestamp))
					and eps.PrehospWaifRefuseCause_id is null and prt.PrehospType_Code in (2,3)
				), '') as N3, -GetCancelReferToHosp
				coalesce ((
				select
					xmlagg( xmlelement(name NPR,
					xmlelement (name N_NPR,t.N_NPR),
					xmlelement (name D_NPR ,t.D_NPR),
					xmlelement (name ISTNPR,t.ISTNPR),
					xmlelement (name SMOLPU,t.SMOLPU),
					xmlelement (name LPU_1,t.LPU_1),
					xmlelement (name PRNPR,t.PRNPR)))
				from (
					select
						coalesce(npr.EvnNPR_NPRID, npr_eps.EvnNPR_NPRID, ext_npr.EvnDirectionExt_NPRID, 0) as N_NPR, --Int
						cast(ed.EvnDirection_setDT as date) as D_NPR, --Дата направления = дата создания направления
						case when puc.Lpu_id=ed.Lpu_id then 2 when puc.Lpu_id=ed.Lpu_did then 3 end as ISTNPR, --Источник аннулирования
						smolp.Lpu_f003mcod as SMOLPU,- numeric(6, 0) МО, отменившая направление (реестровый номер, F003)
						null as LPU_1, -- numeric(4, 0) Код подразделения МО, источника аннулирования
						case
						when dr.DirFailType_Code=5 then 3
						when dr.DirFailType_Code=13 then 4
						else 5
						end as PRNPR-- int Причина отмены направления
					from dbo.v_EvnDirection ed
						left join v_EvnPS eps on eps.EvnDirection_id = ed.EvnDirection_id
						left join v_EvnDirectionExt ext_npr on ext_npr.EvnDirection_id = ed.EvnDirection_id
						left join r30.v_EvnNPR npr on npr.Evn_id = ed.EvnDirection_id
						left join r30.v_EvnNPR npr_eps on npr_eps.Evn_id = eps.EvnPS_id
						inner join dbo.DirFailType dr on dr.DirFailType_id = ed.DirFailType_id
						inner join dbo.pmUserCache puc on ed.pmUser_failID=puc.PMUser_id
						inner join v_lpu smolp on smolp.Lpu_id = puc.Lpu_id
						inner join v_Lpu lp on lp.Lpu_id = ed.Lpu_id
					where
						ed.DirType_id in (1,5)
						and ed.PayType_id = (select PayType_id from v_PayType where PayType_SysNick = 'oms' limit 1)
						and (:Lpu_id is null or ed.Lpu_id=:Lpu_id)
						and ed.EvnDirection_failDT>=dateadd('day', 2, cast(to_char(:Date,'dd.mm.yyyy')||' '||:startTime as timestamp)) and ed.EvnDirection_failDT<=dateadd('day', -1, cast(to_char(:Date,'dd.mm.yyyy')||' '||:finalTime as timestamp))
					union all
					select
						coalesce(npr.EvnNPR_NPRID, npr_eps.EvnNPR_NPRID, ext_npr.EvnDirectionExt_NPRID, 0) as N_NPR, --Int
						cast(ed.EvnDirection_setDT as date) as D_NPR, --Дата направления = дата создания направления
						case when puc.Lpu_id=ed.Lpu_id then 2 when puc.Lpu_id=ed.Lpu_did then 3 end as ISTNPR, --Источник аннулирования
						smolp.Lpu_f003mcod as SMOLPU,- numeric(6, 0) МО, отменившая направление (реестровый номер, F003)
						null as LPU_1, -- numeric(4, 0) Код подразделения МО, источника аннулирования
						case
						when esc.EvnStatusCause_Code = 1 then 3
						when esc.EvnStatusCause_Code = 5 then 4
						when esc.EvnStatusCause_Code in (6,7,8,12,13,14,15) then 2
						when esc.EvnStatusCause_Code in (9,10,11,18) then 1
						else 5
						end as PRNPR-- int Причина отмены направления
					from dbo.v_EvnDirection ed
						left join v_EvnPS eps on eps.EvnDirection_id = ed.EvnDirection_id
						left join v_EvnDirectionExt ext_npr on ext_npr.EvnDirection_id = ed.EvnDirection_id
						inner join lateral (
						select
						EvnStatusCause_id
						from v_EvnStatusHistory
						where Evn_id = ed.EvnDirection_id
						and EvnStatus_id = ed.EvnStatus_id
						order by EvnStatusHistory_id desc
						limit 1
						) esh on true
						inner join v_EvnStatusCause esc on esc.EvnStatusCause_id = esh.EvnStatusCause_id
						left join r30.v_EvnNPR npr on npr.Evn_id = ed.EvnDirection_id
						left join r30.v_EvnNPR npr_eps on npr_eps.Evn_id = eps.EvnPS_id
						inner join dbo.pmUserCache puc on ed.pmUser_failID=puc.PMUser_id
						inner join v_lpu smolp on smolp.Lpu_id = puc.Lpu_id
						inner join v_Lpu lp on lp.Lpu_id = ed.Lpu_id
					where
						ed.DirType_id in (1,5)
						and ed.PayType_id = (select PayType_id from v_PayType where PayType_SysNick = 'oms' limit 1)
						and ed.DirFailType_id is null
						and ed.EvnStatus_id in (12,13)
						and (:Lpu_id is null or ed.Lpu_id=:Lpu_id)
						and ed.EvnDirection_statusDate>=dateadd('day', 2, cast(to_char(:Date,'dd.mm.yyyy')||' '||:startTime as timestamp)) and ed.EvnDirection_statusDate<=dateadd('day', -1, cast(to_char(:Date,'dd.mm.yyyy')||' '||:finalTime as timestamp))
					union all
					select
						coalesce(npr.EvnNPR_NPRID, npr_eps.EvnNPR_NPRID, ext_npr.EvnDirectionExt_NPRID, 0) as N_NPR, --Int
						cast(coalesce(ed.EvnDirection_setDT,eps.EvnDirection_setDT) as date) as D_NPR, --Дата направления = дата создания направления
						case when smolp.Lpu_id=eps.Lpu_id then 2 when smolp.Lpu_id=ed.Lpu_did then 3 end as ISTNPR, --Источник аннулирования
						smolp.Lpu_f003mcod as SMOLPU,- numeric(6, 0) МО, отменившая направление (реестровый номер, F003)
						null as LPU_1, -- numeric(4, 0) Код подразделения МО, источника аннулирования
						case
						when pwrc.PrehospWaifRefuseCause_Code = 11 then 1
						when pwrc.PrehospWaifRefuseCause_Code = 10 then 4
						when pwrc.PrehospWaifRefuseCause_Code in (2,8) then 3
						else 2
						end as PRNPR-- int Причина отмены направления
					from dbo.v_EvnPS eps
						left join dbo.v_EvnDirection ed on ed.EvnDirection_id = eps.EvnDirection_id
						left join v_EvnDirectionExt ext_npr on ext_npr.EvnDirection_id = ed.EvnDirection_id
						left join r30.v_EvnNPR npr on npr.Evn_id = ed.EvnDirection_id
						left join r30.v_EvnNPR npr_eps on npr_eps.Evn_id = eps.EvnPS_id
						left join dbo.v_LpuSection ls1 on ls1.LpuSection_id = ed.LpuSection_did
						left join dbo.v_LpuUnit lu on ls1.LpuUnit_id=lu.LpuUnit_id
						inner join dbo.v_PrehospWaifRefuseCause pwrc on pwrc.PrehospWaifRefuseCause_id = eps.PrehospWaifRefuseCause_id
						inner join v_LpuSection ls on ls.LpuSection_id = eps.LpuSection_pid
						inner join v_lpu smolp on smolp.Lpu_id = ls.Lpu_id
						inner join v_lpu lp on lp.Lpu_id = ls.Lpu_id
					where
						(:Lpu_id is null or eps.Lpu_id=:Lpu_id)
						and eps.PayType_id = (select PayType_id from v_PayType where PayType_SysNick = 'oms' limit 1)
						and coalesce(ed.EvnDirection_setDT, eps.EvnDirection_setDT) is not null
						and eps.EvnPS_OutcomeDT>=dateadd('day', 2, cast(to_char(:Date,'dd.mm.yyyy')||' '||:startTime as timestamp)) and eps.EvnPS_OutcomeDT<=dateadd('day', -1, cast(to_char(:Date,'dd.mm.yyyy')||' '||:finalTime as timestamp))
				) as t
				), '') as N4, --GetExitHosp
				coalesce ((
				select
					xmlagg( xmlelement(name NPR,
					xmlelement (name N_NPR,COALESCE), --Int
					xmlelement (name D_NPR,cast(coalesce(ed.EvnDirection_setDT, eps.EvnDirection_setDT, eps.EvnPS_setDT) as date)), --Дата направления = дата создания направления
					xmlelement (name FOR_POM,case when prt.PrehospType_Code in (2,3) then 2 else coalesce(prt.PrehospType_Code,1) end ), - Int - Форма оказания медицинской помощи
					xmlelement (name LPU,lp.Lpu_f003mcod), -- T(6)-- numeric(6, 0) - МО госпитализации (реестровый номер, F003)
					xmlelement (name LPU_1,lb.LpuBuilding_Code), -- T(8) Код подразделения МО госпитализации
					xmlelement (name DATE_1,cast(eps.EvnPS_setDate as date)), --Дата госпитализации
					xmlelement (name DATE_2,cast (eps.EvnPS_disDT as date)), -- datetime - Дата выбытия
					xmlelement (name W,case when pe.Sex_id=3 then 1 else pe.Sex_id end), --numeric(1) – пол (1 - муж, 2 - жен)
					xmlelement (name DR,cast(pe.Person_BirthDay as date)), --date – дата рождения
					xmlelement (name PROFIL,lsp.LpuSectionProfile_Code), --numeric(4, 0) - Профиль койки
					xmlelement (name PODR,ls.LpuSection_Code ), --numeric(4, 0) - Код отделения (профиль)
					xmlelement (name NHISTORY,eps.EvnPS_NumCard) --Номер карты стационарного больного
					))
				from dbo.v_EvnPS eps
					left join dbo.v_EvnDirection ed on ed.EvnDirection_id = eps.EvnDirection_id
					left join v_EvnDirectionExt ext_npr on ext_npr.EvnDirection_id = ed.EvnDirection_id
					left join r30.v_EvnNPR npr on npr.Evn_id = ed.EvnDirection_id
					left join r30.v_EvnNPR npr_eps on npr_eps.Evn_id = eps.EvnPS_id
					left join dbo.v_PrehospType prt on coalesce(ed.PrehospType_did,eps.PrehospType_id)=prt.PrehospType_id
					inner join v_lpu lp on lp.Lpu_id = eps.Lpu_id
					inner join dbo.v_LpuSection ls on ls.LpuSection_id = eps.LpuSection_id
					inner join dbo.v_LpuUnit lu on ls.LpuUnit_id=lu.LpuUnit_id
					inner join dbo.v_LpuBuilding lb on lb.LpuBuilding_id = lu.LpuBuilding_id
					left join dbo.v_LpuSectionBedProfile lsb on lsb.LpuSectionBedProfile_id = ls.LpuSectionBedProfile_id
					left join v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
					inner join dbo.v_Person_all pe on pe.PersonEvn_id = eps.PersonEvn_id and pe.Server_id = eps.Server_id
					inner join dbo.v_LeaveType lt on lt.LeaveType_id = eps.LeaveType_id
				where (1=1)
					and (:Lpu_id is null or eps.Lpu_id=:Lpu_id)
					and eps.PayType_id = (select PayType_id from v_PayType where PayType_SysNick = 'oms' limit 1)
					and eps.EvnPS_disDT>=dateadd('day', -2, cast(to_char(:Date,'dd.mm.yyyy')||' '||:startTime as timestamp)) and eps.EvnPS_disDT<=dateadd('day', -1, cast(to_char(:Date,'dd.mm.yyyy')||' '||:finalTime as timestamp))
				), '') as N5, --GetCouchInfo
				coalesce ((
				select
					xmlagg( xmlelement(name NPR,
					xmlelement(name data,data),
					xmlelement(name LPU,LPU),
					xmlelement(name LPU_1,LPU_1),
					xmlelement(name USL_OK,USL_OK),
					xmlelement(name DET,DET),
					xmlelement(name PROFIL,PROFIL),
					xmlelement(name COUNTP,COUNTP),
					xmlelement(name POSTP,POSTP),
					xmlelement(name VIBP,VIBP),
					xmlelement(name PLANP,PLANP),
					xmlelement(name FREEK,FREEK),
					xmlelement(name FREEM,FREEM),
					xmlelement(name FREEW,FREEW),
					xmlelement(name FREED,FREED),
					xmlelement(name OBSMO,
					xmlelement(name SMO,SMO),
					xmlelement(name SMOSL,SMOSL ),
					xmlelement(name SMOKD,SMOKD ))
					))
				from rpt30.Han_hosp_enable(:Lpu_id,null,:Date) t
				), '') as N6;
		";

		$hosp_data_xml_arr = array();
		foreach($lpu_arr as $lpu) {
			$params['Lpu_id'] = $lpu['Lpu_id'];
			//echo getDebugSQL($query, $params);exit;
			$hosp_data_xml_arr[$lpu['fcode']] = $this->getFirstRowFromQuery($query, $params);

			if ($hosp_data_xml_arr[$lpu['fcode']] == false) {
				return false;
			}
		}

		return $hosp_data_xml_arr;
	}

	/**
	 * Формирование масива ошибок для выгрузки
	 */
	function checkXmlDataOnErrors($method, $xml_string) {
		$rules = array(
			'N1' => array(
				'DATE_1' => array('allowEmpty' => false),
			),
		);

		if (!isset($rules[$method])) {
			return array();
		}

		$xml = new SimpleXMLElement($xml_string);
		$errors = array();
		$index = 0;

		foreach($xml->NPR as $npr) {
			$index++;
			$msg = "Выгрузка {$method}. Запись №{$index}, {$npr->FAM} {$npr->IM} {$npr->OT}: ";

			foreach($rules[$method] as $field => $params) {
				if (array_key_exists('allowEmpty', $params) && $params['allowEmpty'] == false && empty($npr->$field)) {
					$errors[] = $msg."Отсутсвует значение {$field}";
				}
			}
		}

		return $errors;
	}


	/**
	 * Сохраняет идентификатор от ТФОМС
	 * @param array $data
	 * @return array
	 */
	function saveEvnNPR($data) {
		$params = array(
			'EvnNPR_id' => null,
			'Evn_id' => $data['Evn_id'],
			'EvnClass_id' => $data['EvnClass_id'],
			'NPRID' => $data['NPRID'],
			'pmUser_id' => $data['pmUser_id']
		);

		if (empty($data['EvnNPR_id'])) {
			$procedure = 'r30.p_EvnNPR_ins';
		} else {
			$params['EvnNPR_id'] = $data['EvnNPR_id'];
			$procedure = 'r30.p_EvnNPR_upd';
		}

		$query = "
			select
				EvnNPR_id as \"EvnNPR_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from " . $procedure . "(
				EvnNPR_id := :EvnNPR_id,
				Evn_id := :Evn_id,
				EvnClass_id := :EvnClass_id,
				EvnNPR_NPRID := :NPRID,
				pmUser_id := :pmUser_id
			)
		";

		$result = $this->queryResult($query, $params);
		if (!is_array($result)) {
			return $this->createError('','Ошибка при сохранении идентификаторва от ТФОМС');
		}
		return $result;
	}

	/**
	 * Сохранение данных из xml во временную таблицу
	 * @param DOMDocument $HospData Данные xml-файла
	 * @param array $stat Массив для сбора стат. данных импорта
	 * @return string Наименование временной таблицы
	 * @throws Exception
	 */
	function saveHospDataInTmpTable($HospData, &$stat = array('all' => 0)) {
		$tmpTableName = "#tmp" . time();

		$importFields = array(
			'NPRID' => 'bigint',
			'DATE_1' => 'date',
			'D_NPR' => 'date',
			'FOR_POM' => 'int',
			'DCODE_MO' => 'varchar(6)',
			'NCODE_MO' => 'varchar(6)',
			'LPU' => 'varchar(6)',
			'FAM' => 'varchar(40)',
			'IM' => 'varchar(40)',
			'OT'  => 'varchar(40)',
			'VPOLIS' => 'varchar(1)',
			'DS1' => 'varchar(10)',
			'PROFIL' => 'int',
			'CT' => 'varchar(50)',
			'W' => 'int',
			'SPOLIS' => 'varchar(10)',
			'NPOLIS' => 'varchar(20)',
			'PODR' => 'int',
			'DR' => 'date',
			'IDDOKT' => 'varchar(16)',
			'NHISTORY' => 'varchar(50)',
		);

		$tableFieldFn = function($field, $type){return "$field $type";};
		$tableFieldsStr = implode(",\n", array_map($tableFieldFn, array_keys($importFields), $importFields));
		$query = "
			DROP TABLE {$tmpTableName} IF EXISTS;
	
			create temp table {$tmpTableName} (
				{$tableFieldsStr},
				Person_id bigint,
				Evn_id bigint,
				EvnClass_id bigint,
				EvnClass_SysNick varchar(50),
				isExternal int,
				doubleID bigint
			)
			returning null as \"Error_Msg\", 0 as \"Error_Code\";
		";
		$resp = $this->queryResult($query);
		if (!is_array($resp)) {
			throw new Exception('Ошибка при создании временной таблицы для импорта данных');
		}
		if (!$this->isSuccessful($resp)) {
			throw new Exception($resp[0]['Error_Msg']);
		}

		$insert_arr = array();
		$insertValuesFn = function($fields, $params){
			return array_map(function($field) use($params) {
				return !empty($params[$field])?"'".str_replace("'", "''", $params[$field])."'":'null';
			}, $fields);
		};

		$insertQuery = function($tmpTableName, $fields, $values){
			return "
				insert into {$tmpTableName}
					({$fields})
				values
					{$values}
			";
		};

		$dom_npr = $HospData->getElementsByTagName('NPR');
		foreach ($dom_npr as $dom_onenpr) {
			$stat['all']++;

			$params = array();
			$params['NPRID'] = 0;
			$params['DATE_1'] = null;
			$params['D_NPR'] = null;
			$params['FOR_POM'] = 1;
			$params['DCODE_MO'] = '';
			$params['NCODE_MO'] = '';
			$params['LPU'] = '';
			$params['FAM'] = '';
			$params['IM'] = '';
			$params['OT'] = '';
			$params['VPOLIS'] = '';
			$params['DS1'] = '';
			$params['PROFIL'] = '';
			$params['CT'] = '';
			$params['W'] = '';
			$params['SPOLIS'] = '';
			$params['NPOLIS'] = '';
			$params['PODR'] = '';
			$params['DR'] = '';
			$params['IDDOKT'] = '';
			$params['NHISTORY'] = '';

			/*$dom_nnprlpu = $dom_onenpr->getElementsByTagName('N_NPR_LPU');
			foreach($dom_nnprlpu as $dom_onennprlpu) {
				$params['DIRNUM'] = substr($dom_onennprlpu->nodeValue, 10);
			}*/

			$dom_date1 = $dom_onenpr->getElementsByTagName('DATE_1');
			foreach($dom_date1 as $dom_onedate1) {
				$params['DATE_1'] = $dom_onedate1->nodeValue;
			}

			$dom_dnpr = $dom_onenpr->getElementsByTagName('D_NPR');
			foreach($dom_dnpr as $dom_onednpr) {
				$params['D_NPR'] = $dom_onednpr->nodeValue;
			}

			$dom_dnpr = $dom_onenpr->getElementsByTagName('DNPR');
			foreach($dom_dnpr as $dom_onednpr) {
				$params['D_NPR'] = $dom_onednpr->nodeValue;
			}

			$dom_forpom = $dom_onenpr->getElementsByTagName('FOR_POM');
			foreach($dom_forpom as $dom_oneforpom) {
				$params['FOR_POM'] = $dom_oneforpom->nodeValue;
			}

			$dom_dcodemo = $dom_onenpr->getElementsByTagName('DCODE_MO');
			foreach($dom_dcodemo as $dom_onedcodemo) {
				$params['DCODE_MO'] = $dom_onedcodemo->nodeValue;
			}

			$dom_ncodemo = $dom_onenpr->getElementsByTagName('NCODE_MO');
			foreach($dom_ncodemo as $dom_onencodemo) {
				$params['NCODE_MO'] = $dom_onencodemo->nodeValue;
			}

			$dom_ncodemo = $dom_onenpr->getElementsByTagName('LPU');
			foreach($dom_ncodemo as $dom_onencodemo) {
				$params['LPU'] = $dom_onencodemo->nodeValue;
			}

			$dom_fam = $dom_onenpr->getElementsByTagName('FAM');
			foreach($dom_fam as $dom_onefam) {
				$params['FAM'] = toAnsi($dom_onefam->nodeValue);
			}

			$dom_im = $dom_onenpr->getElementsByTagName('IM');
			foreach($dom_im as $dom_oneim) {
				$params['IM'] = toAnsi($dom_oneim->nodeValue);
			}

			$dom_ot = $dom_onenpr->getElementsByTagName('OT');
			foreach($dom_ot as $dom_oneot) {
				$params['OT'] = toAnsi($dom_oneot->nodeValue);
			}

			$dom_obj = $dom_onenpr->getElementsByTagName('VPOLIS');
			foreach($dom_obj as $dom_oneobj) {
				$params['VPOLIS'] = $dom_oneobj->nodeValue;
			}

			$dom_obj = $dom_onenpr->getElementsByTagName('DS1');
			foreach($dom_obj as $dom_oneobj) {
				$params['DS1'] = $dom_oneobj->nodeValue;
			}

			$dom_obj = $dom_onenpr->getElementsByTagName('PROFIL');
			foreach($dom_obj as $dom_oneobj) {
				$params['PROFIL'] = $dom_oneobj->nodeValue;
			}

			$dom_obj = $dom_onenpr->getElementsByTagName('CT');
			foreach($dom_obj as $dom_oneobj) {
				$params['CT'] = $dom_oneobj->nodeValue;
			}

			$dom_obj = $dom_onenpr->getElementsByTagName('W');
			foreach($dom_obj as $dom_oneobj) {
				$params['W'] = $dom_oneobj->nodeValue;
			}

			$dom_obj = $dom_onenpr->getElementsByTagName('SPOLIS');
			foreach($dom_obj as $dom_oneobj) {
				$params['SPOLIS'] = $dom_oneobj->nodeValue;
			}

			$dom_obj = $dom_onenpr->getElementsByTagName('NPOLIS');
			foreach($dom_obj as $dom_oneobj) {
				$params['NPOLIS'] = $dom_oneobj->nodeValue;
			}

			$dom_obj = $dom_onenpr->getElementsByTagName('PODR');
			foreach($dom_obj as $dom_oneobj) {
				$params['PODR'] = $dom_oneobj->nodeValue;
			}

			$dom_obj = $dom_onenpr->getElementsByTagName('NHISTORY');
			foreach($dom_obj as $dom_oneobj) {
				$params['NHISTORY'] = $dom_oneobj->nodeValue;
			}

			$dom_dr = $dom_onenpr->getElementsByTagName('DR');
			foreach($dom_dr as $dom_onedr) {
				$params['DR'] = $dom_onedr->nodeValue;
			}

			$dom_dr = $dom_onenpr->getElementsByTagName('IDDOKT');
			foreach($dom_dr as $dom_onedr) {
				$params['IDDOKT'] = $dom_onedr->nodeValue;
			}

			$dom_nnpr = $dom_onenpr->getElementsByTagName('N_NPR');
			foreach($dom_nnpr as $dom_onennpr) {
				$params['NPRID'] = $dom_onennpr->nodeValue;
			}

			if (empty($params['NPRID'])) {
				continue;
			}

			$insert_arr[] = "(".implode(",", $insertValuesFn(array_keys($importFields), $params)).")";
			if (count($insert_arr) == 100) {
				$fields = implode(",", array_keys($importFields));
				$values = implode(",", $insert_arr);
				$resp = $this->queryResult($insertQuery($tmpTableName, $fields, $values));
				if (!is_array($resp)) {
					throw new Exception('Ошибка при заполенении временной таблицы данными из файла');
				}
				if (!$this->isSuccessful($resp)) {
					throw new Exception($resp[0]['Error_Msg']);
				}
				$insert_arr = array();
			}
		}

		if (count($insert_arr) > 0) {
			$fields = implode(",", array_keys($importFields));
			$values = implode(",", $insert_arr);
			$resp = $this->queryResult($insertQuery($tmpTableName, $fields, $values));
			if (!is_array($resp)) {
				throw new Exception('Ошибка при заполенении временной таблицы данными из файла');
			}
			if (!$this->isSuccessful($resp)) {
				throw new Exception($resp[0]['Error_Msg']);
			}
		}

		return $tmpTableName;
	}

	/**
	 * @param string $tmpTableName
	 * @param string $method
	 * @param array $stat
	 * @param array $errors
	 * @throws Exception
	 */
	function identHospData($tmpTableName, $method, &$stat = array('ident' => 0), &$errors = array()) {
		$object = "";
		$join = "";
		$peJoin = "";
		$where = "(1=1)";
		$params = array('identExternal' => 0);
		$doubleFields = array();

		switch($method) {
			case 'N1':
				$object = 'EvnDirection';
				$join .= " left join Lpu nl on nl.Lpu_f003mcod = pl.NCODE_MO";
				$join .= " left join Lpu dl on dl.Lpu_f003mcod = pl.DCODE_MO";
				$where .= " and pe.Person_SurName = pl.FAM";
				$where .= " and coalesce(pe.Person_FirName, '') = coalesce(pl.IM, '')";
				$where .= " and coalesce(pe.Person_SecName, '') = coalesce(pl.OT, '')";
				$where .= " and pe.Person_BirthDay = pl.DR";
				$where .= " and e.EvnDirection_setDate = pl.D_NPR";
				$where .= " and coalesce(e.Lpu_sid, e.Lpu_id, 0) = coalesce(nl.Lpu_id, 0)";
				$where .= " and coalesce(e.Lpu_did, 0) = coalesce(dl.Lpu_Id, 0)";
				$where .= " and e.DirType_id in (1,5)";
				$params['identExternal'] = 1;
				$doubleFields = array('D_NPR','NCODE_MO','DCODE_MO','FAM','IM','OT','DR');
				break;
			case 'N2':
				$object = 'EvnPS';
				$join .= " left join Lpu nl on nl.Lpu_f003mcod = pl.NCODE_MO";
				$join .= " left join Lpu dl on dl.Lpu_f003mcod = pl.DCODE_MO";
				$peJoin .= " left join v_EvnDirection ed on ed.EvnDirection_id = e.EvnDirection_id";
				$peJoin .= " left join v_LpuSection ls on ls.LpuSection_id = e.LpuSection_did";
				$where .= " and e.EvnPS_setDate = pl.DATE_1";
				$where .= " and coalesce(ed.EvnDirection_setDate, e.EvnDirection_setDT, cast('1900-01-01' as date)) = coalesce(pl.D_NPR, cast('1900-01-01' as date))";
				$where .= " and coalesce(ed.Lpu_sid, e.Lpu_id, 0) = coalesce(dl.Lpu_id, e.Lpu_id, 0)";
				$where .= " and coalesce(ed.Lpu_did, e.Lpu_did, ls.Lpu_id, e.Lpu_id, 0) = coalesce(nl.Lpu_id, 0)";
				$where .= " and pe.Person_SurName = pl.FAM";
				$where .= " and coalesce(pe.Person_FirName, '') = coalesce(pl.IM, '')";
				$where .= " and coalesce(pe.Person_SecName, '') = coalesce(pl.OT, '')";
				$where .= " and pe.Person_BirthDay = pl.DR";
				$doubleFields = array('D_NPR','NCODE_MO','DCODE_MO','FAM','IM','OT','DR');
				break;
			case 'N3':
				$object = 'EvnPS';
				$join .= " left join Lpu dl on dl.Lpu_f003mcod = pl.DCODE_MO";
				$peJoin .= " left join v_EvnDirection ed on ed.EvnDirection_id = e.EvnDirection_id";
				$peJoin .= " left join v_LpuSection ls on ls.LpuSection_id = e.LpuSection_did";
				$where .= " and e.EvnPS_setDate = pl.DATE_1";
				$where .= " and coalesce(ed.EvnDirection_setDate, e.EvnDirection_setDT, cast('1900-01-01' as date)) = coalesce(pl.D_NPR, ed.EvnDirection_setDate, e.EvnDirection_setDT, cast('1900-01-01' as date))";
				$where .= " and coalesce(ed.Lpu_did, e.Lpu_id, 0) = coalesce(dl.Lpu_id, ed.Lpu_did, e.Lpu_id, 0)";
				$where .= " and pe.Person_SurName = pl.FAM";
				$where .= " and coalesce(pe.Person_FirName, '') = coalesce(pl.IM, '')";
				$where .= " and coalesce(pe.Person_SecName, '') = coalesce(pl.OT, '')";
				$where .= " and pe.Person_BirthDay = pl.DR";
				$doubleFields = array('D_NPR','DCODE_MO','FAM','IM','OT','DR','DATE_1');
				break;
			case 'N5':
				$object = 'EvnPS';
				$where .= " and pe.Person_SurName ilike pl.FAM";
				$where .= " and coalesce(pe.Person_FirName, '') ilike coalesce(pl.IM, '')";
				$where .= " and coalesce(pe.Person_SecName, '') ilike coalesce(pl.OT, '')";
				$where .= " and e.EvnPS_setDate = pl.DATE_1";
				$where .= " and pe.Person_BirthDay = pl.DR";
				$doubleFields = array('FAM','IM','OT','DR','DATE_1');
				break;
			default:
				return;
		}
		$doubleFieldsStr = implode(",", $doubleFields);
		$doubleFieldsOnFn = function($field){
			switch(true) {
				case in_array($field, array('FAM','IM','OT')):
					return "coalesce(dl.$field,'') = coalesce(pl.$field,'')";
				case in_array($field, array('D_NPR','DATE_1','DR')):
					return "coalesce(dl.$field, dbo.tzgetdate()) = coalesce(pl.$field, dbo.tzgetdate())";
				case in_array($field, array('NCODE_MO','DCODE_MO')):
					return "coalesce(dl.$field,0) = coalesce(pl.$field,0)";
				default:
					return '1=1';
			}
		};
		$doubleFieldsOnStr = implode("\nand ", array_map($doubleFieldsOnFn, $doubleFields));

		$query = "
			with double_list as (
				select
					max(NPRID) as NPRID,
					{$doubleFieldsStr}
				from {$tmpTableName}
				group by {$doubleFieldsStr}
				having count(NPRID) > 1
			)
			update {$tmpTableName}
			set doubleID = dl.NPRID
			from {$tmpTableName} pl
				inner join double_list dl on {$doubleFieldsOnStr};
		";
		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			throw new Exception('Ошибка при идентификации по данным от ТФОМС и СМО');
		}
		if ($params['identExternal'] == 1) {
			$query = "
				update {$tmpTableName}
				set Person_id = ede.Person_id,
					isExternal = 1
				from {$tmpTableName} pl
				inner join lateral (
					select
						*
					from v_EvnDirectionExt ede
					where ede.EvnDirectionExt_NPRID = pl.NPRID
						and ede.EvnDirectionExt_setDT = pl.D_NPR
						and ede.Person_SurName = pl.FAM
						and ede.Person_FirName = pl.IM
						and ede.Person_SecName = pl.OT
					order by EvnDirectionExt_insDT desc
					limit 1
				) ede on true;
			
				update {$tmpTableName}
				set Person_id = pe.Person_id,
					Evn_id = pe.Evn_id,
					EvnClass_id = pe.EvnClass_id,
					EvnClass_SysNick = pe.EvnClass_SysNick
				from
					{$tmpTableName} pl
					{$join}
					inner join lateral(
						select
							pe.Person_id,
							e.{$object}_id as Evn_id,
							ec.EvnClass_id,
							ec.EvnClass_SysNick
						from
							v_Person_all pe
							inner join v_{$object} e on e.PersonEvn_id = pe.PersonEvn_id
								and e.Server_id = pe.Server_id
							inner join v_EvnClass ec on ec.EvnClass_id = e.EvnClass_id
							{$peJoin}
						where {$where}
						limit 1
					) pe on true
				where 
					pl.doubleID is null 
					and pl.Evn_id is null;
					
				update {$tmpTableName}
				set Person_id = pe.Person_id,
					Evn_id = pe.Evn_id,
					EvnClass_id = pe.EvnClass_id,
					EvnClass_SysNick = pe.EvnClass_SysNick
				from
					{$tmpTableName} pl
					{$join}
					inner join lateral(
						select
							pe.Person_id,
							e.{$object}_id as Evn_id,
							ec.EvnClass_id,
							ec.EvnClass_SysNick
						from
							v_PersonState pe
							inner join v_{$object} e on e.PersonEvn_id = pe.PersonEvn_id
								and e.Server_id = pe.Server_id
							inner join v_EvnClass ec on ec.EvnClass_id = e.EvnClass_id
							{$peJoin}
						where {$where}
						limit 1
					) pe on true
				where
				 	pl.doubleID is null
					and pl.Evn_id is null;
					
				update {$tmpTableName}
				set Person_id = p.Person_id,
					isExternal = 1
				from
					{$tmpTableName} pl
					left join lateral(
						select dbo.GetPersonByFioPolis(pl.FAM, pl.IM, pl.OT, pl.DR, pl.SPOLIS, pl.NPOLIS) as Person_id
					) p on true
				where
					pl.doubleID is null
					and pl.Evn_id is null
					and p.Person_id is not null;
			";
			$resp = $this->queryResult($query, $params);
			if (!is_array($resp)) {
				throw new Exception('Ошибка при идентификации по данным от ТФОМС и СМО');
			}
		}
		$query = "
			select
				count(*) as \"Ident_Count\"
			from {$tmpTableName}
			where Person_id is not null
			limit 1
		";
		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			throw new Exception('Ошибка при идентификации по данным от ТФОМС и СМО');
		}
		$stat['ident'] = $resp[0]['Ident_Count'];

		$doublesList = $this->queryResult("
			select
				NPRID as \"NPRID\",
				doubleID as \"doubleID\"
			from {$tmpTableName}
			where doubleID is not null
		");
		if ($doublesList === false) {
			throw new Exception('Ошибка при получении списка неидентифицированных записей');
		}
		$groupDoubles = array();
		foreach($doublesList as $doubles) {
			$groupDoubles[$doubles['doubleID']][] = $doubles['NPRID'];
		}
		foreach($groupDoubles as $group) {
			$groupStr = implode(", ", $group);
			$errors[] = "В файле дублируются записи с идентификаторами ТФОМС {$groupStr}";
		}

		$nonIdentList = $this->queryList("
			select
				NPRID as \"NPRID\"
			from {$tmpTableName}
			where Person_id is null
				and doubleID is null
		");
		if ($nonIdentList === false) {
			throw new Exception('Ошибка при получении списка неидентифицированных записей');
		}
		foreach($nonIdentList as $NPRID) {
			$errors[] = "В базе данных не найден объект с идентификатором ТФОМС {$NPRID}";
		}
	}

	/**
	 * @param string $tmpTableName
	 * @param int $NPRID
	 * @return array
	 */
	function deleteTmpHospData($tmpTableName, $NPRID) {
		$params = array('NPRID' => $NPRID);
		$query = "
			delete {$tmpTableName} where NPRID = :NPRID;
		";
		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			$this->createError('','Ошибка при удалении записи из временной таблицы');
		}
		return $resp;
	}

	/**
	 * @param string $tmpTableName
	 * @param array $session
	 * @param array $stat
	 * @param array $errors
	 * @throws Exception
	 */
	function saveEvnDirectionExt($tmpTableName, $session, &$stat = array('ext' => 0, 'upd' => 0), &$errors = array()) {
		$query = "
			select
				-- select
				ede.EvnDirectionExt_id as \"EvnDirectionExt_id\",
				pl.NPRID as \"EvnDirectionExt_NPRID\",
				case 
					when pl.EvnClass_SysNick = 'EvnDirection' then pl.Evn_id
					when pl.EvnClass_SysNick = 'EvnPS' then eps.EvnDirection_id
				end as \"EvnDirection_id\",
				to_char(pl.D_NPR, 'yyyy-mm-dd') as \"EvnDirectionExt_setDT\",
				case when pl.FOR_POM = 1 then 2 else 1 end as \"PrehospType_id\",
				dl.Lpu_id as \"Lpu_id\",		--МО куда направлен
				dl.Lpu_Name as \"Lpu_Name\",	--МО куда направлен
				pl.NCODE_MO as \"Lpu_f003mcod\",	--МО создавшая направление
				pl.Person_id as \"Person_id\",
				pl.FAM as \"Person_SurName\",
				pl.IM as \"Person_FirName\",
				pl.OT as \"Person_SecName\",
				to_char(pl.DR, 'yyyy-mm-dd') as \"Person_BirthDay\",
				pl.CT as \"Person_Phone\",
				pl.W as \"Sex_id\",
				case 
					when pl.VPOLIS = 2 then 3
					when pl.VPOLIS = 3 then 4
					else 1
				end as \"PolisType_id\",
				pl.SPOLIS as \"Polis_Ser\",
				pl.NPOLIS as \"Polis_Num\",
				d.Diag_id as \"Diag_id\",
				lsp.LpuSectionProfile_id as \"LpuSectionProfile_id\",
				pl.PODR as \"LpuSection_Code\",
				pl.IDDOKT as \"MedPersonal_SNILS\",
				to_char(pl.DATE_1, 'yyyy-mm-dd') as \"EvnDirectionExt_planDate\"
				-- end select
			from 
				-- from
				{$tmpTableName} pl
				--inner join v_Lpu nl on nl.Lpu_f003mcod = pl.NCODE_MO
				left join v_Lpu dl on dl.Lpu_f003mcod = pl.DCODE_MO
				left join v_Diag d on d.Diag_Code = pl.DS1 and DiagLevel_id = 4
				left join v_LpuSectionProfile lsp on lsp.LpuSectionProfile_Code = cast(pl.PROFIL as varchar)
				left join v_EvnPS eps on eps.EvnPS_id = pl.Evn_id
				left join lateral(
					select
						*
					from v_EvnDirectionExt ede
					where ede.Person_id = pl.Person_id
						and ede.EvnDirectionExt_NPRID = pl.NPRID
						and ede.EvnDirectionExt_setDT = pl.D_NPR
					order by ede.EvnDirectionExt_insDT desc
					limit 1
				) ede on true
				-- end from
			where
				-- where
				coalesce(pl.isExternal, 0) = 1
				-- end where
			order by
				-- order by
				pl.Person_id
				-- end order by
		";

		$start = 0;
		$limit = 200;
		$count = $stat['ext'] = $this->getFirstResultFromQuery(getCountSQLPH($query));
		if ($count === false) {
			throw new Exception('Ошибка при формировании данных для обновления внешних направлений');
		}

		$this->load->model('EvnDirectionExt_model');

		while($start < $count) {
			$resp = $this->queryResult(getLimitSQLPH($query, $start, $limit));
			if (!is_array($resp)) {
				throw new Exception('Ошибка при формировании данных для обновления внешних направлений');
			}

			foreach($resp as $item) {
				try {
					$item['pmUser_id'] = $session['pmuser_id'];
					$item['session'] = $session;

					$resp = $this->EvnDirectionExt_model->saveEvnDirectionExt($item);
					if (!$this->isSuccessful($resp)) {
						throw new Exception($resp[0]['Error_Msg']);
					}
					if (!empty($resp[0]['EvnDirection_id'])) {
						$stat['upd']++;
					}
				} catch (Exception $e) {
					$errors[] = "Ошибка при обновлении данных внешнего направления NPRID={$item['EvnDirectionExt_NPRID']}: ".$e->getMessage();
				}
			}

			$start += $limit;
		}
	}

	/**
	 * @param string $tmpTableName
	 * @param array $session
	 * @param array $stat
	 * @param array $errors
	 * @throws Exception
	 */
	function saveEvnDirectionInt($tmpTableName, $session, &$stat = array('int' => 0, 'upd' => 0), &$errors = array()) {
		$query = "
			select
				-- select
				pl.Evn_id as \"Evn_id\",
				pl.EvnClass_id as \"EvnClass_id\",
				pl.NPRID as \"NPRID\",
				npr.EvnNPR_id as \"EvnNPR_id\"
				-- end select
			from
				-- from
				{$tmpTableName} pl
				left join r30.v_EvnNPR npr on npr.Evn_id = pl.Evn_id
				-- end from
			where
				-- where
				pl.Evn_id is not null
				and coalesce(pl.isExternal, 0) = 0
				and pl.NPRID in (select max(NPRID) from {$tmpTableName} group by Evn_id)
				and (npr.EvnNPR_id is null or npr.EvnNPR_NPRID <> pl.NPRID)
				-- end where
			order by
				-- order by
				pl.Person_id
				-- end order by
		";

		$start = 0;
		$limit = 200;
		$count = $stat['int'] = $this->getFirstResultFromQuery(getCountSQLPH($query));
		if ($count === false) {
			throw new Exception('Ошибка при формировании данных для обновления внутренних направлений');
		}

		while($start < $count) {
			$resp = $this->queryResult(getLimitSQLPH($query, $start, $limit));
			if (!is_array($resp)) {
				throw new Exception('Ошибка при формировании данных для обновления внутренних направлений');
			}

			foreach($resp as $item) {
				try {
					$item['pmUser_id'] = $session['pmuser_id'];

					$resp = $this->saveEvnNPR($item);
					if (!$this->isSuccessful($resp)) {
						throw new Exception($resp[0]['Error_Msg']);
					}
					$stat['upd']++;
				} catch (Exception $e) {
					$errors[] = "Ошибка при обновлении данных внутреннего направления NPRID={$item['EvnDirectionExt_NPRID']}: ".$e->getMessage();
				}
			}

			$start += $limit;
		}
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function getEvnPSFields($data) {
		$query = "
			select
				 coalesce(EPS.EvnPS_NumCard, '') as \"EvnPS_NumCard\"
				,RTRIM(coalesce(Lpu.Lpu_Name, '')) as \"Lpu_Name\"
				,RTRIM(coalesce(PLST.PolisType_Name, '')) as \"PolisType_Name\"
				,CASE WHEN PLST.PolisType_Code = 4 then '' ELSE RTRIM(coalesce(PLS.Polis_Ser, '')) END as \"Polis_Ser\"
				,CASE WHEN PLST.PolisType_Code = 4 then coalesce(RTRIM(PS.Person_EdNum), '') ELSE RTRIM(coalesce(PLS.Polis_Num, '')) END as \"Polis_Num\"
				,RTRIM(coalesce(OST.OMSSprTerr_Code, '')) as \"OMSSprTerr_Code\"
				,RTRIM(coalesce(OrgSmo.OrgSMO_Name, '')) as \"OrgSmo_Name\"
				,RTRIM(coalesce(OS.Org_OKATO, '')) as \"OrgSmo_OKATO\"
				,PS.Person_id as \"Person_id\"
				,RTRIM(RTRIM(coalesce(PS.Person_Surname, ''))
					|| ' ' || RTRIM(coalesce(PS.Person_Firname, ''))
					|| ' ' || RTRIM(coalesce(PS.Person_Secname, '')))
				as \"Person_Fio\"
				,RTRIM(coalesce(SX.Sex_Name, '')) as \"Sex_Name\"
				,RTRIM(coalesce(SX.Sex_Code, '')) as \"Sex_Code\"
				,to_char(PS.Person_Birthday, 'dd.mm.yyyy') as \"Person_Birthday\"
				,dbo.Age2(PS.Person_Birthday, EPS.EvnPS_setDate) as \"Person_Age\"
				,RTRIM(coalesce(D.Document_Num, '')) as \"Document_Num\"
				,RTRIM(coalesce(D.Document_Ser, '')) as \"Document_Ser\"
				,RTRIM(coalesce(DT.DocumentType_Name, '')) as \"DocumentType_Name\"
				,RTRIM(coalesce(KLAT.KLAreaType_Name, '')) as \"KLAreaType_Name\"
				,RTRIM(coalesce(KLAT.KLAreaType_id, '')) as \"KLAreaType_id\"
				,RTRIM(coalesce(PS.Person_Phone, '')) as \"Person_Phone\"
				,RTRIM(coalesce(PAddr.Address_Address, '')) as \"PAddress_Name\"
				,RTRIM(coalesce(UAddr.Address_Address, '')) as \"UAddress_Name\"
				,RTRIM(coalesce(MSF.Person_Fio,'')) as \"FIO_Priem\"
				,RTRIM(coalesce(PT.PayType_Name, '')) as \"PayType_Name\"
				,RTRIM(coalesce(PT.PayType_Code, '')) as \"PayType_Code\"
				,RTRIM(coalesce(SS.SocStatus_Name, '')) as \"SocStatus_Name\"
				,RTRIM(coalesce(SS.SocStatus_Code, '')) as \"SocStatus_Code\"
				,RTRIM(coalesce(SS.SocStatus_SysNick, '')) as \"SocStatus_SysNick\"
				,IT.PrivilegeType_id as \"PrivilegeType_id\"
				,RTRIM(coalesce(IT.PrivilegeType_Name, '')) as \"PrivilegeType_Name\"
				,coalesce(IT2.PrivilegeType_Code, '') as \"PrivilegeType_Code\"
				,RTRIM(coalesce(PersPriv.PrivilegeType_Name, '')) as \"PersPriv\"
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
				,RTRIM(coalesce(PA.PrehospArrive_Name, '')) as \"PrehospArrive_Name\"
				,RTRIM(coalesce(DiagH.Diag_Name, '')) as \"PrehospDiag_Name\"
				,RTRIM(coalesce(DiagP.Diag_Name, '')) as \"AdmitDiag_Name\"
				,RTRIM(coalesce(PHTX.PrehospToxic_Name, '')) as \"PrehospToxic_Name\"
				,RTRIM(coalesce(PHTX.PrehospToxic_Code, '')) as \"PrehospToxic_Code\"
				,RTRIM(coalesce(LSTT.LpuSectionTransType_Name, '')) as \"LpuSectionTransType_Name\"
				,RTRIM(coalesce(LSTT.LpuSectionTransType_Code, '')) as \"LpuSectionTransType_Code\"
				,RTRIM(coalesce(PHT.PrehospType_Name, '')) as \"PrehospType_Name\"
				,RTRIM(coalesce(PHT.PrehospType_Code, '')) as \"PrehospType_Code\"
				,case when PHT.PrehospType_Code in (2,3) then 3 when PHT.PrehospType_Code = 1 then 4 end as \"PregospType_sCode\"
				,case when coalesce(EPS.EvnPS_HospCount, 1) = 1 then 'первично' else 'повторно' end as \"EvnPS_HospCount\"
				,case when coalesce(EPS.EvnPS_HospCount, 1) = 1 then 1 else 2 end as \"IsFirst\"
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
				,EPS.EvnPS_TimeDesease as \"EvnPS_TimeDesease\"
				,EPS.EvnDirection_Num as \"EvnDirection_Num\"
				,EPS.EvnPS_CodeConv as \"EvnPS_CodeConv\"
				,EPS.EvnPS_NumConv as \"EvnPS_NumConv\"
				,to_char(EPS.EvnDirection_SetDT, 'dd.mm.yyyy') as \"EvnDirection_SetDT\"
				,RTRIM(PC.PersonCard_Code) as \"PersonCard_Code\"
				,RTRIM(coalesce(PHTR.PrehospTrauma_Name, '')) as \"PrehospTrauma_Name\"
				,RTRIM(coalesce(PHTR.PrehospTrauma_Code, '')) as \"PrehospTrauma_Code\"
				,to_char(EPS.EvnPS_setDate, 'dd.mm.yyyy') as \"EvnPS_setDate\"
				,to_char(EPS.EvnPS_setTime, 'hh24:mi:ss') as \"EvnPS_setTime\"
				,RTRIM(coalesce(LSFirst.LpuSection_Name, '')) as \"LpuSectionFirst_Name\"
				,RTRIM(coalesce(LSBPFirst.LpuSectionBedProfile_Name, '')) as \"LpuSectionBedProfile_Name\"
				,RTRIM(coalesce(MPFirst.MedPersonal_TabCode, '')) as \"MedPersonal_TabCode\"
				,RTRIM(coalesce(MPFirst.MedPersonal_Code, '')) as \"MPFirst_Code\"
				,RTRIM(coalesce(MPFirst.Person_Fio, '')) as \"MedPerson_FIO\"
				,RTRIM(coalesce(OHMP.Person_Fio,'')) as \"OrgHead_FIO\"
				,RTRIM(coalesce(OHMP.MedPersonal_TabCode,'')) as \"OrgHead_Code\"
				,to_char(ESFirst.EvnSection_setDT, 'dd.mm.yyyy') as \"EvnSectionFirst_setDate\"
				,ESFirst.EvnSection_setTime as \"EvnSectionFirst_setTime\"
				,to_char(EPS.EvnPS_disDate, 'dd.mm.yyyy') as \"EvnPS_disDate\"
				,to_char(EPS.EvnPS_disTime, 'hh24:mi:ss') as \"EvnPS_disTime\"
				,LUTLast.LpuUnitType_SysNick as \"LpuUnitType_SysNick\"
				,case when LUTLast.LpuUnitType_SysNick = 'stac'
				  then
                    datediff('day', EPS.EvnPS_setDate, EPS.EvnPS_disDate) || abs(sign(datediff('day', EPS.EvnPS_setDate, EPS.EvnPS_disDate)) - interval '1 day') -- круглосуточные
				  else
				    (datediff('day', EPS.EvnPS_setDate, EPS.EvnPS_disDate) + interval '1 day') -- дневные
				end as \"EvnPS_KoikoDni\"
				,RTRIM(coalesce(LT.LeaveType_Name, '')) as \"LeaveType_Name\"
				,LT.LeaveType_Code as \"LeaveType_sCode\"
				,RTRIM(coalesce(RD.ResultDesease_Name, '')) as \"ResultDesease_Name\"
				,RD.ResultDesease_Code as \"ResultDesease_Code\"
				,RD.ResultDesease_Code as \"ResultDesease_sCode\"
				,case
					when LT.LeaveType_SysNick ilike 'die' then 6
					when RD.ResultDesease_SysNick in('zdorvosst','zdorchast','zdornar') then 1
					when RD.ResultDesease_SysNick in('rem','uluc','stabil', 'kompens', 'hron') then 2
					when RD.ResultDesease_SysNick in('noeffect') then 3
					when RD.ResultDesease_SysNick in('yatr','novzab','progress') then 4
					when RD.ResultDesease_SysNick ilike 'zdor' then 5
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
					select
						PrivilegeType_id,
						PrivilegeType_Code,
						PrivilegeType_Name
					from v_PersonPrivilege
					where PrivilegeType_Code in ('81', '82', '83') and Person_id = PS.Person_id
					order by PersonPrivilege_begDate desc
					limit 1
				) IT on true
				left join lateral (
					select
						PrivilegeType_id,
						PrivilegeType_Code,
						PrivilegeType_Name
					from v_PersonPrivilege
					where PrivilegeType_Code in ('11', '20', '91', '81', '82', '83', '84') and Person_id = PS.Person_id
					order by PersonPrivilege_begDate desc
					limit 1
				) IT2 on true
				left join lateral (
					select
						PP.PrivilegeType_id,
						PP.PrivilegeType_Code,
						PP.PrivilegeType_Name
					from v_PersonPrivilege PP
					where PP.Person_id = PS.Person_id
						and (PP.PersonPrivilege_endDate is null or PP.PersonPrivilege_endDate <= EPS.EvnPS_disDate)
					order by PP.PersonPrivilege_begDate desc
					limit 1
				) PersPriv on true
				left join lateral(
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
				left join v_Diag DG on DG.Diag_id = ESLast.Diag_id and coalesce(ESLast.LeaveType_id, 0) != 5
				left join v_Diag PAD on PAD.Diag_id = ED.Diag_aid
				left join lateral (
					select
						Diag_id
					from v_EvnDiagPS
					where EvnDiagPS_pid = ESLast.EvnSection_id
						and DiagSetClass_id = 2
					limit 1
				) TDGA on true
				left join v_Diag DGA on DGA.Diag_id = TDGA.Diag_id and coalesce(ESLast.LeaveType_id, 0) != 5
				left join lateral (
					select
						Diag_id
					from v_EvnDiagPS
					where EvnDiagPS_pid = ESLast.EvnSection_id
						and DiagSetClass_id = 3
					limit 1
				) TDGS on true
				left join v_Diag DGS on DGS.Diag_id = TDGS.Diag_id and coalesce(ESLast.LeaveType_id, 0) != 5
				left join lateral (
					select
						Diag_id
					from v_EvnDiagPS
					where EvnDiagPS_pid = ED.EvnDie_id
						and DiagSetClass_id = 2
					limit 1
				) TPADA on true
				left join v_Diag PADA on PADA.Diag_id = TPADA.Diag_id
				left join lateral (
					select
						Diag_id
					from v_EvnDiagPS
					where EvnDiagPS_pid = ED.EvnDie_id
						and DiagSetClass_id = 3
					limit 1
				) TPADS on true
				left join v_Diag PADS on PADS.Diag_id = TPADS.Diag_id
				left join v_LpuUnitType oLUT on oLUT.LpuUnitType_id = EOST.LpuUnitType_oid
				left join lateral (
					select
						YN.YesNo_Name
					from v_EvnUsluga EU
						inner join v_UslugaComplex UC on UC.UslugaComplex_id = EU.UslugaComplex_id
						inner join v_YesNo YN on YN.YesNo_Code = 1
					where EU.EvnUsluga_rid = EPS.EvnPS_id
						and UC.UslugaComplex_Code ilike 'A12.06.011'
						and EU.EvnUsluga_SetDT is not null
					limit 1
				) as IsRW on true
				left join lateral (
					select
						YN.YesNo_Name
					from v_EvnUsluga EU
						inner join v_UslugaComplex UC on UC.UslugaComplex_id = EU.UslugaComplex_id
						inner join v_YesNo YN on YN.YesNo_Code = 1
					where
						EU.EvnUsluga_rid = EPS.EvnPS_id and UC.UslugaComplex_Code ilike 'A09.05.228'
						and EU.EvnUsluga_SetDT is not null
					limit 1
				) as IsAIDS on true
			where
				EPS.EvnPS_id = :EvnPS_id
			limit 1
				
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
	 * @param array $data
	 * @param array $response
	 * @return array|string
	 */
	protected function _printEvnPS($data = [], $response = []) {
		$invalid_type_name = '';
		$template = 'evn_ps_template_list_a4_astra';

		// Приводим код оплаты в соответствие с формой
		// https://redmine.swan.perm.ru/issues/39608
		switch ( $response[0]['PayType_Code'] ) {
			case 1:
			case 7:
			case 8:
				$response[0]['PayType_Code'] = 1;
				break;

			case 2:
				$response[0]['PayType_Code'] = 4;
				break;

			case 3:
			case 4:
			case 9:
				$response[0]['PayType_Code'] = 2;
				break;

			case 5:
				$response[0]['PayType_Code'] = 3;
				break;

			case 6:
				$response[0]['PayType_Code'] = 5;
				break;
		}

		$evn_section_data = array();
		$evn_usluga_oper_data = array();

		$response_temp = $this->getEvnSectionData($data);

		if ( is_array($response_temp) ) {
			$evn_section_data = $response_temp;

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
					'EvnUslugaOperPayType_Name' => $response_temp[$i]['PayType_Name']
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
					'EvnUslugaOperPayType_Name' => '&nbsp;<br />&nbsp;'
				);
			}
		}

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
		//LpuUnitType_SysNick
		$LeaveType_aCode = $response[0]['LeaveType_Code'];
		if($response[0]['LeaveType_Code'] == 2 || $response[0]['LeaveType_Code'] == 5)
			$LeaveType_aCode = 4;
		else if ($response[0]['LeaveType_Code'] == 4)
		{
			if($response[0]['LpuUnitType_SysNick'] == 'stac')
				$LeaveType_aCode = 3;
			else
				$LeaveType_aCode = 2;
		}
		if($response[0]['ResultDesease_aCode']==6) {
			$LeaveType_aCode = 0;
		}
		$print_data = array(
			'EvnPSTemplateTitle' => 'Печать карты выбывшего из стационара'
			,'EvnPS_NumCard' => returnValidHTMLString($response[0]['EvnPS_NumCard'])
			,'PolisType_Name' => returnValidHTMLString($response[0]['PolisType_Name'])
			,'Polis_Num' => returnValidHTMLString($response[0]['Polis_Num'])
			,'Polis_Ser' => returnValidHTMLString($response[0]['Polis_Ser'])
			,'OMSSprTerr_Code' => returnValidHTMLString($response[0]['OMSSprTerr_Code'])
			,'OrgSmo_Name' => returnValidHTMLString($response[0]['OrgSmo_Name'])
			,'Person_Fio' => returnValidHTMLString($response[0]['Person_Fio'])
			,'Person_OKATO' => returnValidHTMLString($response[0]['Person_OKATO'])
			,'Sex_Code' => returnValidHTMLString($response[0]['Sex_Code'])
			,'Person_Birthday' => returnValidHTMLString($response[0]['Person_Birthday'])
			,'Person_Age' => returnValidHTMLString($response[0]['Person_Age'])
			,'DocumentType_Name' => returnValidHTMLString($response[0]['DocumentType_Name'])
			,'Document_Ser' => returnValidHTMLString($response[0]['Document_Ser'])
			,'Document_Num' => returnValidHTMLString($response[0]['Document_Num'])
			,'KLAreaType_Name' => returnValidHTMLString($response[0]['KLAreaType_Name'])
			,'KLAreaType_id' => returnValidHTMLString($response[0]['KLAreaType_id'])
			,'Person_Phone' => returnValidHTMLString($response[0]['Person_Phone'])
			,'FIO_Priem' => returnValidHTMLString($response[0]['FIO_Priem'])
			,'PAddress_Name' => returnValidHTMLString($response[0]['PAddress_Name'])
			,'UAddress_Name' => returnValidHTMLString($response[0]['UAddress_Name'])
			,'PayType_Code' => returnValidHTMLString($response[0]['PayType_Code'])
			,'SocStatus_Code' => returnValidHTMLString($response[0]['SocStatus_Code'])
			,'InvalidType_Name' => returnValidHTMLString($invalid_type_name)
			,'PrehospOrg_Name' => returnValidHTMLString($response[0]['PrehospOrg_Name'])
			,'PrehospArrive_Name' => returnValidHTMLString($response[0]['PrehospArrive_Name'])
			,'PersonCard_Code' => returnValidHTMLString($response[0]['PersonCard_Code'])
			,'PrivilegeType_Code' => returnValidHTMLString($response[0]['PrivilegeType_Code'])
			,'Lpu_Name' => returnValidHTMLString($response[0]['Lpu_Name'])
			,'PrehospDiag_Name' => returnValidHTMLString($response[0]['PrehospDiag_Name'])
			,'AdmitDiag_Name' => returnValidHTMLString($response[0]['AdmitDiag_Name'])
			,'PrehospToxic_Code' => returnValidHTMLString($response[0]['PrehospToxic_Code'])
			,'LpuSectionTransType_Code' => returnValidHTMLString($response[0]['LpuSectionTransType_Code'])
			,'PrehospType_Code' => returnValidHTMLString($response[0]['PrehospType_Code'])
			,'IsFirst' => returnValidHTMLString($response[0]['IsFirst'])
			,'EvnPS_HospCount' => returnValidHTMLString($response[0]['EvnPS_HospCount'])
			,'EvnPS_TimeDesease' => (returnValidHTMLString($response[0]['EvnPS_TimeDesease']))==''?'0':((returnValidHTMLString($response[0]['EvnPS_TimeDesease']))<=6?'1':(returnValidHTMLString($response[0]['EvnPS_TimeDesease'])>24?'3':2))
			,'PrehospTrauma_Code' => returnValidHTMLString($response[0]['PrehospTrauma_Code'])
			,'EvnPS_setDate' => returnValidHTMLString($response[0]['EvnPS_setDate'])
			,'EvnPS_setTime' => returnValidHTMLString($response[0]['EvnPS_setTime'])
			,'LpuSectionFirst_Name' => returnValidHTMLString($response[0]['LpuSectionFirst_Name'])
			,'EvnSectionFirst_setDate' => returnValidHTMLString($response[0]['EvnSectionFirst_setDate'])
			,'EvnSectionFirst_setTime' => returnValidHTMLString($response[0]['EvnSectionFirst_setTime'])
			,'EvnPS_disDate' => returnValidHTMLString($response[0]['EvnPS_disDate'])
			,'EvnPS_disTime' => returnValidHTMLString($response[0]['EvnPS_disTime'])
			,'EvnPS_KoikoDni' => returnValidHTMLString($response[0]['EvnPS_KoikoDni'])
			,'LeaveType_aCode' => $LeaveType_aCode
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
		);

		if (allowPersonEncrypHIV($data['session']) && !empty($response[0]['PersonEncrypHIV_Encryp'])) {
			$print_data['Person_Fio'] = returnValidHTMLString($response[0]['PersonEncrypHIV_Encryp']);

			$person_fields = array('PolisType_Name', 'Polis_Num', 'Polis_Ser', 'OMSSprTerr_Code', 'OrgSmo_Name',
				'Person_OKATO', 'Sex_Code', 'Person_Birthday', 'Person_Age', 'DocumentType_Name', 'Document_Ser', 'Document_Num',
				'KLAreaType_Name', 'KLAreaType_id', 'Person_Phone', 'PAddress_Name', 'UAddress_Name', 'SocStatus_Code',
				'InvalidType_Name', 'PersonCard_Code', 'PrivilegeType_Code'
			);

			foreach($person_fields as $field) {
				$print_data[$field] = '';
			}
		}

		$html = $this->parser->parse($template, $print_data, !empty($data['returnString']));
		if (!empty($data['returnString'])) {
			return array('html' => $html);
		} else {
			return $html;
		}
	}
}