<?php	defined('BASEPATH') or die ('No direct script access allowed');

require_once(APPPATH.'models/EvnPS_model.php');

class Ekb_EvnPS_model extends EvnPS_model {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function getEvnPSNumber($data) {
		$query = "
			declare @EvnPS_NumCard bigint;
			exec xp_GenpmID 
				@ObjectName = 'EvnPS', 
				@Lpu_id = :Lpu_id, 
				@ObjectID = @EvnPS_NumCard output, 
				@ObjectValue = :ObjectValue;
			select @EvnPS_NumCard as EvnPS_NumCard;
		";
		$result = $this->db->query($query, array(
			 'Lpu_id' => $data['Lpu_id']
			,'ObjectValue' => (!empty($data['year']) ? $data['year'] : date('Y'))
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
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
			lp.Lpu_id,
			lp.Lpu_f003mcod as fcode,
			lp.Lpu_RegNomN2 as rcode
			from v_Lpu lp with(nolock)
			where
			lp.Lpu_f003mcod is not null
			and lp.Lpu_RegNomN2 is not null
			and lp.Lpu_f003mcod <> '0'
			and (:Lpu_id is null or lp.Lpu_id = :Lpu_id)
		";
		$res = $this->db->query($query, $params);
		if (is_object($res)) {
			$lpu_arr = $res->result('array');
		} else {
			return false;
		}

		/*$query = "
			declare @Lpu_id bigint = :Lpu_id

			declare @date datetime = :Date
			declare @startTime datetime = dateadd(day, -2, cast(@date+' '+:startTime as datetime))
			declare @finalTime datetime = dateadd(day, -1, cast(@date+' '+:finalTime as datetime))

			declare @S1 VARCHAR (MAX)=''
			declare @S2 VARCHAR (MAX)=''
			declare @S3 VARCHAR (MAX)=''

			--GetReferToHosp направление на госпитализацию
			SET @S1 = isnull((
			Select
			0+ROW_NUMBER() over (order by ed.EvnDirection_id) as N_ZAP, --Int
			cast(year(EvnDirection_setDT) as varchar)+case when LEN(ISNULL(cast(lp.Lpu_RegNomN2 as varchar),'0'))<4 then REPLICATE('0', 4-LEN(ISNULL(cast(lp.Lpu_RegNomN2 as varchar),'0')))+ISNULL(cast(lp.Lpu_RegNomN2 as varchar),'0') else ISNULL(cast(lp.Lpu_RegNomN2 as varchar),'0') end+right(cast(EvnDirection_Num as varchar), 6) as NUMBTREND,	--T(16) Номер направления (MCOD+NP)
			convert(varchar(10), EvnDirection_setDT, 120) as DATETREND,
			case when dt.DirType_Code = 5 then 2 else 1 end as EXTR, -- Int - Форма оказания медицинской помощи
			case
				when lr.LpuRegion_id is null then right('0000'+ls.Lpusection_Code,4) + '0000'
				when len(isnull(lr.LpuRegion_Name,'')) >= len(ls.Lpusection_Code + '0000') then lr.LpuRegion_Name
				else right('0000'+ls.Lpusection_Code,4)+right('0'+lr.LpuRegion_Name,2)+'00'
			end as NPR_PODR, -- T(8)-- numeric(4, 0) - Код подразделения медицинской организации, направившей на госпитализацию
			lp.Lpu_f003mcod as MO_TREND, -- T(6)-- numeric(6, 0) - МО, направившая на госпитализацию	(реестровый номер, F003)
			lp1.Lpu_f003mcod as MO_IN, -- T(6)-- numeric(6, 0) - МО, куда направлен пациент	(реестровый номер, F003)
			right('0000'+ls1.LpuSection_Code,4)+'0000' as PODR, -- T(8) Код подразделения медицинской организации, куда выписано направление
			lsb.LpuSectionBedProfile_Code as PROFBERTH, --numeric(4, 0) - Профиль койки
			pt.PolisType_CodeF008 as VPOLIS,-- numeric(1, 0) , -- Тип документа, подтверждающего факт страхования по ОМС (F008)
			case when pt.PolisType_CodeF008 in (1) then po.Polis_Ser else Null end as SPOLIS, -- Varchar(10) - Серия документа, подтверждающего факт страхования по ОМС
			case
				when pt.PolisType_CodeF008=1 then
					case when len(po.Polis_Num)<7 then right(replicate('0',7)+po.Polis_Num,7) else po.Polis_Num end
				when pt.PolisType_CodeF008=2 then
					case when len(po.Polis_Num)<9 then right(replicate('0',9)+po.Polis_Num,9) else po.Polis_Num end
				else
					case when len(pe.Person_EdNum)<16 then right(replicate('0',16)+pe.Person_EdNum,16) else pe.Person_EdNum end
			end as NPOLIS, -- Numeric(20) - Номер документа, подтверждающего факт страхования по ОМС
			pe.Person_SurName as FAM, --varchar(30) – Фамилия
			pe.Person_FirName as IM, --varchar(30) – Имя
			pe.Person_SecName as OT, --varchar(30) – Отчество
			case when pe.Sex_id=3 then 1 else pe.Sex_id end as W, --numeric(1) – пол (1 - муж, 2 - жен)
			convert(varchar(10), pe.Person_BirthDay, 120) as DR, --date – дата рождения
			isnull(pe.PersonPhone_Phone, 'не указан') as PHONE, --Varchar(100) - Контактная информация
			coalesce(pa.Address_Address, ua.Address_Address, '') as ADRRESSG, --Адрес проживания
			case when right(di.Diag_Code,1) = '.'
				then SUBSTRING(di.Diag_Code,1,LEN(di.Diag_Code)-1)
				else di.Diag_Code
			end as MKB, --Char(4) - Код диагноза по МКБ
			dlo.CodeDLO as DOCTOR, --Код врача, выписавшего больного на диспансеризацию
			case when eq.EvnQueue_id is not null
				then convert(varchar(10), dateadd(day, 30, ed.EvnDirection_setDT), 120)
				else convert(varchar(10), tt.TimetableStac_setDate, 120)
			end as DATEPLAN, -- date - Плановая дата госпитализации
			convert(varchar(10), tt.TimetableStac_setDate, 120) as DATEAGREE, --Согласованная дата госпитализации
			'0' as SURGERY
			from dbo.v_EvnDirection ed (nolock)
			left join dbo.v_EvnQueue eq (nolock) on eq.EvnDirection_id = ed.EvnDirection_id
			left join dbo.v_DirType dt (nolock) on dt.DirType_id = ed.DirType_id
			--left join dbo.v_PrehospType prt (nolock) on ed.PrehospType_did=prt.PrehospType_id
			inner join v_lpu lp (nolock) on lp.Lpu_id = ed.Lpu_id
			inner join dbo.v_LpuSection ls (nolock) on ls.LpuSection_id = ed.LpuSection_id
			--inner join dbo.v_LpuUnit lu (nolock) on ls.LpuUnit_id=lu.LpuUnit_id
			--inner join dbo.v_LpuBuilding lb (nolock) on lb.LpuBuilding_id = lu.LpuBuilding_id
			inner join dbo.v_MedPersonal mp (nolock) on ed.MedPersonal_id=mp.MedPersonal_id and mp.Lpu_id = ed.Lpu_id
			left join persis.KodDLO dlo (nolock) on mp.MedPersonal_id=dlo.MedWorker_id
			left join dbo.v_TimetableStac_lite tt (nolock) on tt.EvnDirection_id = ed.EvnDirection_id
			inner join dbo.v_Diag di (nolock) on di.Diag_id = ed.Diag_id
			inner join v_lpu lp1 (nolock) on lp1.Lpu_id = ed.Lpu_did
			left join dbo.v_LpuSection ls1 (nolock) on ls1.LpuSection_id = ed.LpuSection_did
			--inner join dbo.v_LpuUnit lu1 (nolock) on ls1.LpuUnit_id=lu1.LpuUnit_id and lu1.LpuUnitType_SysNick='stac'
			--inner join dbo.v_LpuBuilding lb1 (nolock) on lb1.LpuBuilding_id = lu1.LpuBuilding_id
			--inner join dbo.LpuSectionProfile lspf (nolock) on ls1.LpuSectionProfile_id=lspf.LpuSectionProfile_id
			left join dbo.v_LpuSectionBedProfile lsb (nolock) on lsb.LpuSectionBedProfile_id = ls1.LpuSectionBedProfile_id
			inner join dbo.v_Person_all pe (nolock) on pe.PersonEvn_id = ed.PersonEvn_id and pe.Server_id = ed.Server_id
			left join dbo.v_Polis po (nolock) on po.Polis_id = pe.Polis_id
			left join dbo.PolisType pt (nolock) on pt.PolisType_id = po.PolisType_id and isnull(pt.PolisType_CodeF008,0)<>0
			--left join dbo.v_OrgSMO smo (nolock) on smo.OrgSMO_id = po.OrgSmo_id
			--left join dbo.v_Org org (nolock) on org.Org_id = smo.Org_id
			left join dbo.Address pa (nolock) on pe.PAddress_id=pa.Address_id
			left join dbo.Address ua (nolock) on pe.UAddress_id=ua.Address_id
			left join dbo.v_LpuUnit lu (nolock) on lu.LpuUnit_id = ls1.LpuUnit_id
			outer apply (
				select top 1 t.PersonCard_id, t.LpuRegion_id
				from v_PersonCard_all t with(nolock)
				where t.Person_id = ed.Person_id
					and t.PersonCard_begDate is not null
					and t.PersonCard_begDate <= ed.EvnDirection_insDT
					and (t.PersonCard_endDate is null or t.PersonCard_endDate > ED.EvnDirection_insDT)
					and t.Lpu_id = ls.Lpu_id and t.LpuAttachType_id = 1
				order by t.PersonCard_begDate desc
			) PC
			left join dbo.v_LpuRegion lr with(nolock) on lr.LpuRegion_id = PC.LpuRegion_id
			left join r66.v_LpuSectionLink lsl with(nolock) on lsl.LpuSection_id = ls1.LpuSection_id
			left join fed.v_MedicalCareKind mck (nolock) on mck.MedicalCareKind_id = lsl.MedicalCareKind_id
			where (1=1)
			and ed.DirType_id in (1,5)
			and (@Lpu_id is null or ed.Lpu_id=@Lpu_id)
			--and ed.EvnDirection_setDT>=@startTime and ed.EvnDirection_setDT<=@finalTime
			and cast(ed.EvnDirection_setDT as date) = dateadd(day, -1, @date)
			and (ed.LpuSection_did is null
				or
				(lu.LpuUnitType_SysNick = 'stac'
				and ls1.LpuSection_Code <> 660267
				and mck.MedicalCareKind_Code = 31
				and exists(
					select *
					from v_LpuSectionFinans lsf with(nolock)
					inner join v_PayType PayType with(nolock) on PayType.PayType_id = lsf.PayType_id
					where lsf.LpuSection_id = ls1.LpuSection_id
					and lsf.LpuSectionFinans_begDate <= ed.EvnDirection_setDT
					and (lsf.LpuSectionFinans_endDate is null or lsf.LpuSectionFinans_endDate > ed.EvnDirection_setDT)
					and PayType.PayType_SysNick = 'oms'
				))
			)
			for xml path('ZAP')
			),'')

			--GetHospPlan Плановая госпитализация
			SET @S2= isnull
			((
			select
			0+ROW_NUMBER() over (order by eps.EvnPS_id) as N_ZAP, --Int
			case when ed.EvnDirection_id is null then eps.EvnDirection_Num
			else cast(year(ed.EvnDirection_setDT) as varchar)+case
			when LEN(ISNULL(cast(lp.Lpu_RegNomN2 as varchar),'0'))<4
			then REPLICATE('0', 4-LEN(ISNULL(cast(lp.Lpu_RegNomN2 as varchar),'0')))+ISNULL(cast(lp.Lpu_RegNomN2 as varchar),'0')
			else ISNULL(cast(lp.Lpu_RegNomN2 as varchar),'0') end+right(cast(ed.EvnDirection_Num as varchar),6) end as NUMBTREND,	--T(16) Номер направления (MCOD+NP)
			convert(varchar(10), isnull(ed.EvnDirection_setDT,eps.EvnDirection_setDT), 120) as DATETREND, --дата направления
			prt.PrehospType_Code as EXTR, -- Int - Форма оказания медицинской помощи
			lp.Lpu_f003mcod as MO_IN, -- T(6)-- numeric(6, 0) - МО госпитализации	(реестровый номер, F003)
			right('0000'+ls.LpuSection_Code,4)+'0000' as PODR, --Код подразделения МО госпитализации
			replace(convert(varchar(19), eps.EvnPS_setDt, 120), ' ', 'T') as DFACT, --Дата и время фактической госпитализации
			convert(varchar(10), DATEADD(DD,10,eps.EvnPS_setDt), 120) as DLEAVE,
			pt.PolisType_CodeF008 as VPOLIS,-- numeric(1, 0) , -- Тип документа, подтверждающего факт страхования по ОМС (F008)
			case when pt.PolisType_CodeF008 in (1) then po.Polis_Ser else Null end as SPOLIS, -- Varchar(10) - Серия документа, подтверждающего факт страхования по ОМС
			case
				when pt.PolisType_CodeF008=1 then
					case when len(po.Polis_Num)<7 then right(replicate('0',7)+po.Polis_Num,7) else po.Polis_Num end
				when pt.PolisType_CodeF008=2 then
					case when len(po.Polis_Num)<9 then right(replicate('0',9)+po.Polis_Num,9) else po.Polis_Num end
				else
					case when len(pe.Person_EdNum)<16 then right(replicate('0',16)+pe.Person_EdNum,16) else pe.Person_EdNum end
			end as NPOLIS, -- Numeric(20) - Номер документа, подтверждающего факт страхования по ОМС
			pe.Person_SurName as FAM, --varchar(30) – Фамилия
			pe.Person_FirName as IM, --varchar(30) – Имя
			pe.Person_SecName as OT, --varchar(30) – Отчество
			case when pe.Sex_id=3 then 1 else pe.Sex_id end as W, --numeric(1) – пол (1 - муж, 2 - жен)
			convert(varchar(10), pe.Person_BirthDay, 120) as DR, --date – дата рождения
			case when right(di.Diag_Code,1) = '.'
				then SUBSTRING(di.Diag_Code,1,LEN(di.Diag_Code)-1)
				else di.Diag_Code
			end as MKB, --Char(4) - Код диагноза по МКБ
			eps.EvnPS_NumCard as NHISTORY --Номер карты стационарного больного
			from dbo.v_EvnPS eps (nolock)
			left join dbo.v_EvnDirection ed (nolock) on ed.EvnDirection_id = eps.EvnDirection_id
			left join dbo.v_PrehospType prt (nolock) on eps.PrehospType_id=prt.PrehospType_id
			inner join v_lpu lp (nolock) on lp.Lpu_id = eps.Lpu_id
			inner join dbo.v_LpuSection ls (nolock) on ls.LpuSection_id = eps.LpuSection_id
			--inner join dbo.v_LpuBuilding lb (nolock) on lb.LpuBuilding_id = lu.LpuBuilding_id
			--left join dbo.v_LpuSectionBedProfile lsb (nolock) on lsb.LpuSectionBedProfile_id = ls.LpuSectionBedProfile_id
			--left join v_lpu lp1 (nolock) on lp1.Lpu_id = ed.Lpu_id
			--left join dbo.v_LpuSection ls1 (nolock) on ls1.LpuSection_id = ed.LpuSection_id
			--left join dbo.v_LpuUnit lu1 (nolock) on ls1.LpuUnit_id=lu1.LpuUnit_id
			--left join dbo.v_LpuBuilding lb1 (nolock) on lb1.LpuBuilding_id = lu1.LpuBuilding_id
			left join dbo.Diag di (nolock) on di.Diag_id = eps.Diag_pid
			inner join dbo.v_Person_all pe (nolock) on pe.PersonEvn_id = eps.PersonEvn_id and pe.Server_id = eps.Server_id
			left join dbo.v_Polis po (nolock) on po.Polis_id = pe.Polis_id
			left join dbo.PolisType pt (nolock) on pt.PolisType_id = po.PolisType_id
			inner join dbo.v_LpuUnit lu (nolock) on lu.LpuUnit_id = ls.LpuUnit_id
			left join r66.v_LpuSectionLink lsl with(nolock) on lsl.LpuSection_id = ls.LpuSection_id
			left join fed.v_MedicalCareKind mck (nolock) on mck.MedicalCareKind_id = lsl.MedicalCareKind_id
			where (1=1)
			and (@Lpu_id is null or eps.Lpu_id=@Lpu_id)
			and eps.EvnPS_setDT>=@startTime and eps.EvnPS_setDT<=@finalTime
			and eps.PrehospWaifRefuseCause_id is null --and prt.PrehospType_Code=1
			and lu.LpuUnitType_SysNick = 'stac'
			and ls.LpuSection_Code <> 660267
			and mck.MedicalCareKind_Code = 31
			and exists(
				select *
				from v_LpuSectionFinans lsf with(nolock)
				inner join v_PayType PayType with(nolock) on PayType.PayType_id = lsf.PayType_id
				where lsf.LpuSection_id = ls.LpuSection_id
				and lsf.LpuSectionFinans_begDate <= eps.EvnPS_setDT
				and (lsf.LpuSectionFinans_endDate is null or lsf.LpuSectionFinans_endDate > eps.EvnPS_setDT)
				and PayType.PayType_SysNick = 'oms'
			)
			and
			(prt.PrehospType_SysNick = 'extreme' or ed.EvnDirection_id is not null
			or (SUBSTRING(eps.EvnDirection_Num,1,4) = cast(year(eps.EvnDirection_setDT) as varchar)
			and SUBSTRING(eps.EvnDirection_Num,5,4) = right('0000'+isnull(lp.Lpu_RegNomN2,'0'),4)
			and len(SUBSTRING(eps.EvnDirection_Num,9,LEN(eps.EvnDirection_Num))) > 0))
			for xml path('ZAP')
			), '')

			--GetCancelReferToHosp информация об аннулировании направления
			SET @S3= isnull
			((
			select
			0+ROW_NUMBER() over (order by ed.EvnDirection_id) as N_ZAP, --Int -- bigint , Идентификатор направления
			cast(year(ed.EvnDirection_setDT) as varchar)+case
			when LEN(ISNULL(cast(lp.Lpu_RegNomN2 as varchar),'0'))<4
			then REPLICATE('0', 4-LEN(ISNULL(cast(lp.Lpu_RegNomN2 as varchar),'0')))+ISNULL(cast(lp.Lpu_RegNomN2 as varchar),'0')
			else ISNULL(cast(lp.Lpu_RegNomN2 as varchar),'0') end+right(cast(ed.EvnDirection_Num as varchar),6) as NUMBTREND,	--T(16) Номер направления (MCOD+NP)
			convert(varchar(10), ed.EvnDirection_setDT, 120) as DATETREND, --Дата направления
			lp.Lpu_f003mcod as MOTREND, -- T(6)-- numeric(6, 0) - МО, направившая на госпитализацию	(реестровый номер, F003)
			case when dr.DirFailType_Code=5 then 1 when dr.DirFailType_Code=13 then 4 when dr.DirFailType_Code in (1,2,4,6,11,9,10) then 2 else 5 end as	REASON-- int  Причина отмены направления
			from dbo.v_EvnDirection ed (nolock)
			--left join r30.EvnNPR npr (nolock) on ed.EvnDirection_id=npr.Evn_id
			inner join dbo.DirFailType dr (nolock) on dr.DirFailType_id = ed.DirFailType_id
			--inner join dbo.pmUserCache puc (nolock) on ed.pmUser_failID=puc.PMUser_id
			--inner join v_lpu smolp (nolock) on smolp.Lpu_id = puc.Lpu_id
			inner join v_Lpu lp (nolock) on lp.Lpu_id = ed.Lpu_id
			left join v_LpuSection ls1 with(nolock) on ls1.LpuSection_id = ed.LpuSection_did
			left join dbo.v_LpuUnit lu (nolock) on lu.LpuUnit_id = ls1.LpuUnit_id
			left join r66.v_LpuSectionLink lsl with(nolock) on lsl.LpuSection_id = ls1.LpuSection_id
			left join fed.v_MedicalCareKind mck (nolock) on mck.MedicalCareKind_id = lsl.MedicalCareKind_id
			where(1=1)
			and ed.DirType_id in (1,5)
			and (@Lpu_id is null or ed.Lpu_id=@Lpu_id)
			--and ed.EvnDirection_failDT>=@startTime and ed.EvnDirection_failDT<=@finalTime
			and cast(ed.EvnDirection_failDT as date) = dateadd(day, -1, @date)
			and (ed.LpuSection_did is null
				or
				(lu.LpuUnitType_SysNick = 'stac'
				and ls1.LpuSection_Code <> 660267
				and mck.MedicalCareKind_Code = 31
				and exists(
					select *
					from v_LpuSectionFinans lsf with(nolock)
					inner join v_PayType PayType with(nolock) on PayType.PayType_id = lsf.PayType_id
					where lsf.LpuSection_id = ls1.LpuSection_id
					and lsf.LpuSectionFinans_begDate <= ed.EvnDirection_setDT
					and (lsf.LpuSectionFinans_endDate is null or lsf.LpuSectionFinans_endDate > ed.EvnDirection_setDT)
					and PayType.PayType_SysNick = 'oms'
				))
			)
			for xml path('ZAP')
			), '')

			SELECT
			@S1 as m1,	--GetReferToHosp
			@S2 as m2,	--GetHospPlan
			@S3 as m3	--GetCancelReferToHosp,
		";*/

		$query1 = "
			declare @Lpu_id bigint = :Lpu_id

			declare @date datetime = :Date
			declare @startTime datetime = dateadd(day, -2, cast(@date+' '+:startTime as datetime))
			declare @finalTime datetime = dateadd(day, -1, cast(@date+' '+:finalTime as datetime))

			--GetReferToHosp направление на госпитализацию
			Select
			0+ROW_NUMBER() over (order by ed.EvnDirection_id) as N_ZAP, --Int
			cast(year(EvnDirection_setDT) as varchar)
				+ right(REPLICATE('0', 4) + ISNULL(lp.Lpu_RegNomN2, ''), 4)
				+ right(REPLICATE('0', 6) + cast(EvnDirection_Num as varchar), 6)
			as NUMBTREND,	--T(16) Номер направления (MCOD+NP)
			convert(varchar(10), EvnDirection_setDT, 120) as DATETREND,
			case
				when lu1.LpuUnitType_SysNick = 'stac' then '1'
				else '2'
			end as TYPECARD, -- numeric(1, 0) -- тип подразделения ЛПУ у группы отделений
			case when dt.DirType_Code = 5 then 2 else 1 end as EXTR, -- Int - Форма оказания медицинской помощи
			left(ls.LpuSection_Code + '00000', 5) as NPR_PODR, -- T(8)-- numeric(4, 0) - Код подразделения медицинской организации, направившей на госпитализацию
			lp.Lpu_f003mcod as MO_TREND, -- T(6)-- numeric(6, 0) - МО, направившая на госпитализацию	(реестровый номер, F003)
			lp1.Lpu_f003mcod as MO_IN, -- T(6)-- numeric(6, 0) - МО, куда направлен пациент	(реестровый номер, F003)
			case
				when lu1.LpuUnitType_SysNick != 'stac' then left(ls1.LpuSection_Code + '00000', 5)
				else null
			end as PODR, -- T(8) Код подразделения медицинской организации, куда выписано направление
			case
				when lu1.LpuUnitType_SysNick = 'stac' then CEILING(lsp.LpuSectionProfile_Code) 
				else null
			end as PROFBERTH, --numeric(4, 0) - Профиль койки
			case 
				when lu1.LpuUnitType_SysNick != 'stac' then msc.MedSpecClass_Code
				else null
			end as MEDSPEC, --numeric(5, 0) - Код специальности врача, создавшего направление
			pt.PolisType_CodeF008 as VPOLIS,-- numeric(1, 0) , -- Тип документа, подтверждающего факт страхования по ОМС (F008)
			case when pt.PolisType_CodeF008 in (1) then po.Polis_Ser else Null end as SPOLIS, -- Varchar(10) - Серия документа, подтверждающего факт страхования по ОМС
			case
				when pt.PolisType_CodeF008=1 then
					case when len(po.Polis_Num)<7 then right(replicate('0',7)+po.Polis_Num,7) else po.Polis_Num end
				when pt.PolisType_CodeF008=2 then
					case when len(po.Polis_Num)<9 then right(replicate('0',9)+po.Polis_Num,9) else po.Polis_Num end
				else
					case when len(pe.Person_EdNum)<16 then right(replicate('0',16)+pe.Person_EdNum,16) else pe.Person_EdNum end
			end as NPOLIS, -- Numeric(20) - Номер документа, подтверждающего факт страхования по ОМС
			pe.Person_SurName as FAM, --varchar(30) – Фамилия
			pe.Person_FirName as IM, --varchar(30) – Имя
			pe.Person_SecName as OT, --varchar(30) – Отчество
			case when pe.Sex_id=3 then 1 else pe.Sex_id end as W, --numeric(1) – пол (1 - муж, 2 - жен)
			convert(varchar(10), pe.Person_BirthDay, 120) as DR, --date – дата рождения
			isnull(pe.PersonPhone_Phone, 'не указан') as PHONE, --Varchar(100) - Контактная информация
			coalesce(pa.Address_Address, ua.Address_Address, '') as ADRRESSG, --Адрес проживания
			case when right(di.Diag_Code,1) = '.'
				then SUBSTRING(di.Diag_Code,1,LEN(di.Diag_Code)-1)
				else di.Diag_Code
			end as MKB, --Char(4) - Код диагноза по МКБ
			dlo.CodeDLO as DOCTOR, --Код врача, выписавшего больного на диспансеризацию
			case when eq.EvnQueue_id is not null
				then convert(varchar(10), dateadd(day, 29, ed.EvnDirection_setDT), 120)
				else convert(varchar(10), tt.TimetableStac_setDate, 120)
			end as DATEPLAN, -- date - Плановая дата госпитализации
			convert(varchar(10), tt.TimetableStac_setDate, 120) as DATEAGREE, --Согласованная дата госпитализации
			case when ed.EvnDirection_IsNeedOper = 2 then '1' else '0' end as SURGERY
			from dbo.v_EvnDirection ed (nolock)
			left join dbo.v_EvnQueue eq (nolock) on eq.EvnDirection_id = ed.EvnDirection_id
			left join dbo.v_DirType dt (nolock) on dt.DirType_id = ed.DirType_id
			--left join dbo.v_PrehospType prt (nolock) on ed.PrehospType_did=prt.PrehospType_id
			inner join v_lpu lp (nolock) on lp.Lpu_id = ed.Lpu_id
			inner join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = ed.LpuSectionProfile_id
			left join dbo.v_MedStaffFact msf (nolock) on msf.MedStaffFact_id = ed.MedStaffFact_id
			inner join dbo.v_LpuSection ls (nolock) on ls.LpuSection_id = isnull(ed.LpuSection_id, msf.LpuSection_id)
			inner join dbo.v_LpuUnit lu1 (nolock) on ls.LpuUnit_id=lu1.LpuUnit_id
			--inner join dbo.v_LpuBuilding lb (nolock) on lb.LpuBuilding_id = lu.LpuBuilding_id
			inner join dbo.v_MedPersonal mp (nolock) on ed.MedPersonal_id=mp.MedPersonal_id and mp.Lpu_id = ed.Lpu_id
			left join dbo.v_MedSpecOms mso (nolock) on mso.MedSpecOms_id = msf.MedSpecOms_id
			left join fed.v_MedSpecClass msc (nolock) on msc.MedSpecClass_id = mso.MedSpecClass_id
			left join persis.KodDLO dlo (nolock) on mp.MedPersonal_id=dlo.MedWorker_id
			left join dbo.v_TimetableStac_lite tt (nolock) on tt.EvnDirection_id = ed.EvnDirection_id
			inner join dbo.v_Diag di (nolock) on di.Diag_id = ed.Diag_id
			inner join v_lpu lp1 (nolock) on lp1.Lpu_id = ed.Lpu_did
			left join dbo.v_LpuSection ls1 (nolock) on ls1.LpuSection_id = ed.LpuSection_did
			--inner join dbo.v_LpuUnit lu1 (nolock) on ls1.LpuUnit_id=lu1.LpuUnit_id and lu1.LpuUnitType_SysNick='stac'
			--inner join dbo.v_LpuBuilding lb1 (nolock) on lb1.LpuBuilding_id = lu1.LpuBuilding_id
			--inner join dbo.LpuSectionProfile lspf (nolock) on ls1.LpuSectionProfile_id=lspf.LpuSectionProfile_id
			left join dbo.v_LpuSectionBedProfile lsb (nolock) on lsb.LpuSectionBedProfile_id = ls1.LpuSectionBedProfile_id
			inner join dbo.v_Person_all pe (nolock) on pe.PersonEvn_id = ed.PersonEvn_id and pe.Server_id = ed.Server_id
			outer apply (
				select top 1 fLSBP.*
				from
					v_LpuSectionBedState LSBS with (nolock)
					inner join fed.v_LpuSectionBedProfileLink LSBPL with (nolock) on LSBPL.LpuSectionBedProfileLink_id = LSBS.LpuSectionBedProfileLink_fedid
					inner join fed.v_LpuSectionBedProfile fLSBP with (nolock) on fLSBP.LpuSectionBedProfile_id = LSBPL.LpuSectionBedProfile_fedid
					inner join dbo.v_LpuSectionBedProfile LSBP with (nolock) on LSBP.LpuSectionBedProfile_id = LSBS.LpuSectionBedProfile_id 
				where 
					LSBS.LpuSection_id = ls1.LpuSection_id
					and (
						dbo.Age(pe.Person_BirthDay, ed.EvnDirection_setDT) < 18
						or LSBP.LpuSectionBedProfile_IsChild = 1 
					)
					and (
						( pe.Sex_id in (1, 3) and LSBS.LpuSectionBedState_MaleFact > 0 )
						or ( pe.Sex_id = 2 and LSBS.LpuSectionBedState_FemaleFact > 0 )
					)
			) fBP
			left join dbo.v_Polis po (nolock) on po.Polis_id = pe.Polis_id
			left join dbo.PolisType pt (nolock) on pt.PolisType_id = po.PolisType_id and isnull(pt.PolisType_CodeF008,0)<>0
			--left join dbo.v_OrgSMO smo (nolock) on smo.OrgSMO_id = po.OrgSmo_id
			--left join dbo.v_Org org (nolock) on org.Org_id = smo.Org_id
			left join dbo.Address pa (nolock) on pe.PAddress_id=pa.Address_id
			left join dbo.Address ua (nolock) on pe.UAddress_id=ua.Address_id
			left join dbo.v_LpuUnit lu (nolock) on lu.LpuUnit_id = ls1.LpuUnit_id
			outer apply (
				select top 1 t.PersonCard_id, t.LpuRegion_id
				from v_PersonCard_all t with(nolock)
				where t.Person_id = ed.Person_id
					and t.PersonCard_begDate is not null
					and t.PersonCard_begDate <= ed.EvnDirection_insDT
					and (t.PersonCard_endDate is null or t.PersonCard_endDate > ED.EvnDirection_insDT)
					and t.Lpu_id = ls.Lpu_id and t.LpuAttachType_id = 1
				order by t.PersonCard_begDate desc
			) PC
			left join dbo.v_LpuRegion lr with(nolock) on lr.LpuRegion_id = PC.LpuRegion_id
			left join r66.v_LpuSectionLink lsl with(nolock) on lsl.LpuSection_id = ls1.LpuSection_id
			left join fed.v_MedicalCareKind mck (nolock) on mck.MedicalCareKind_id = lsl.MedicalCareKind_id
			left join dbo.DirFailType dft (nolock) on dft.DirFailType_id = ed.DirFailType_id
			where (1=1)
			and ed.DirType_id in (1,5)
			and (@Lpu_id is null or ed.Lpu_id=@Lpu_id)
			--and ed.EvnDirection_setDT>=@startTime and ed.EvnDirection_setDT<=@finalTime
			and cast(ed.EvnDirection_setDT as date) = dateadd(day, -1, @date)
			and (ed.LpuSection_did is null
				or
				(ls1.LpuSection_Code <> 660267
				and mck.MedicalCareKind_Code in (3,31,32)
				and exists(
					select *
					from v_LpuSectionFinans lsf with(nolock)
					inner join v_PayType PayType with(nolock) on PayType.PayType_id = lsf.PayType_id
					where lsf.LpuSection_id = ls1.LpuSection_id
					and lsf.LpuSectionFinans_begDate <= ed.EvnDirection_setDT
					and (lsf.LpuSectionFinans_endDate is null or lsf.LpuSectionFinans_endDate > ed.EvnDirection_setDT)
					and PayType.PayType_SysNick = 'oms'
				))
			)
			and ISNULL(dft.DirFailType_Code, 0) != 14  -- Неверный ввод
		";

		$query2 = "
			declare @Lpu_id bigint = :Lpu_id

			declare @date datetime = :Date
			declare @startTime datetime = dateadd(day, -2, cast(@date+' '+:startTime as datetime))
			declare @finalTime datetime = dateadd(day, -1, cast(@date+' '+:finalTime as datetime))

			--GetHospPlan Плановая госпитализация
			select
			0+ROW_NUMBER() over (order by eps.EvnPS_id) as N_ZAP, --Int
			case
				when ed.EvnDirection_id is null 
				then 
					cast(year(eps.EvnDirection_setDT) as varchar)
						+ right(REPLICATE('0', 4) + ISNULL(lp.Lpu_RegNomN2, ''), 4)
						+ right(REPLICATE('0', 6) + cast(eps.EvnDirection_Num as varchar), 6)
				else
					cast(year(ed.EvnDirection_setDT) as varchar)
						+ right(REPLICATE('0', 4) + ISNULL(lp.Lpu_RegNomN2, ''), 4)
						+ right(REPLICATE('0', 6) + cast(ed.EvnDirection_Num as varchar), 6)
			end as NUMBTREND,	--T(16) Номер направления (MCOD+NP)
			convert(varchar(10), isnull(ed.EvnDirection_setDT,eps.EvnDirection_setDT), 120) as DATETREND, --дата направления
			lp.Lpu_f003mcod as MO_TREND, --Код МО, выдавшей направление
			dlo.CodeDLO as DOCTOR, --Код медработника, выдавшего направление
			case
				when lu.LpuUnitType_SysNick = 'stac' then '1'
				else '2'
			end as TYPECARD, -- numeric(1, 0) -- тип подразделения ЛПУ у группы отделений
			case
				when lu.LpuUnitType_SysNick = 'stac' then '1'
				else '2'
			end as TYPEHELP, -- Условие оказания
			case
				when lu.LpuUnitType_SysNick = 'stac' then lsp.LpuSectionProfile_Code
				else null
			end as PROFBERTH, -- Профиль
			prt.PrehospType_Code as EXTR, -- Int - Форма оказания медицинской помощи
			lp.Lpu_f003mcod as MO_IN, -- T(6)-- numeric(6, 0) - МО госпитализации	(реестровый номер, F003)
			left(ls.LpuSection_Code + '0000', 5) as PODR, --Код подразделения МО госпитализации
			replace(convert(varchar(19), eps.EvnPS_setDt, 120), ' ', 'T') as DFACT, --Дата и время фактической госпитализации
			case
				when prt.PrehospType_Code = 1 then convert(varchar(10), DATEADD(DD,10,eps.EvnPS_setDt), 120)
				else null
			end as DLEAVE,
			pt.PolisType_CodeF008 as VPOLIS,-- numeric(1, 0) , -- Тип документа, подтверждающего факт страхования по ОМС (F008)
			case when pt.PolisType_CodeF008 in (1) then po.Polis_Ser else Null end as SPOLIS, -- Varchar(10) - Серия документа, подтверждающего факт страхования по ОМС
			case
				when pt.PolisType_CodeF008=1 then
					case when len(po.Polis_Num)<7 then right(replicate('0',7)+po.Polis_Num,7) else po.Polis_Num end
				when pt.PolisType_CodeF008=2 then
					case when len(po.Polis_Num)<9 then right(replicate('0',9)+po.Polis_Num,9) else po.Polis_Num end
				else
					case when len(pe.Person_EdNum)<16 then right(replicate('0',16)+pe.Person_EdNum,16) else pe.Person_EdNum end
			end as NPOLIS, -- Numeric(20) - Номер документа, подтверждающего факт страхования по ОМС
			pe.Person_SurName as FAM, --varchar(30) – Фамилия
			pe.Person_FirName as IM, --varchar(30) – Имя
			pe.Person_SecName as OT, --varchar(30) – Отчество
			case when pe.Sex_id=3 then 1 else pe.Sex_id end as W, --numeric(1) – пол (1 - муж, 2 - жен)
			convert(varchar(10), pe.Person_BirthDay, 120) as DR, --date – дата рождения
			case when right(di.Diag_Code,1) = '.'
				then SUBSTRING(di.Diag_Code,1,LEN(di.Diag_Code)-1)
				else di.Diag_Code
			end as MKB, --Char(4) - Код диагноза по МКБ
			eps.EvnPS_NumCard as NHISTORY --Номер карты стационарного больного
			from dbo.v_EvnPS eps (nolock)
			left join dbo.v_EvnDirection ed (nolock) on ed.EvnDirection_id = eps.EvnDirection_id
			left join dbo.v_PrehospType prt (nolock) on eps.PrehospType_id=prt.PrehospType_id
			inner join v_lpu lp (nolock) on lp.Lpu_id = eps.Lpu_id
			inner join dbo.v_LpuSection ls (nolock) on ls.LpuSection_id = eps.LpuSection_id
			left join persis.KodDLO dlo (nolock) on ed.MedPersonal_id = dlo.MedWorker_id
			outer apply (
				select top 1 *
				from
					v_EvnSection (nolock)
				where
					EvnSection_pid = eps.EvnPS_id
				order by
					EvnSection_setDate
			) as es
			left join dbo.v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = es.LpuSectionProfile_id
			--inner join dbo.v_LpuBuilding lb (nolock) on lb.LpuBuilding_id = lu.LpuBuilding_id
			--left join dbo.v_LpuSectionBedProfile lsb (nolock) on lsb.LpuSectionBedProfile_id = ls.LpuSectionBedProfile_id
			--left join v_lpu lp1 (nolock) on lp1.Lpu_id = ed.Lpu_id
			--left join dbo.v_LpuSection ls1 (nolock) on ls1.LpuSection_id = ed.LpuSection_id
			--left join dbo.v_LpuUnit lu1 (nolock) on ls1.LpuUnit_id=lu1.LpuUnit_id
			--left join dbo.v_LpuBuilding lb1 (nolock) on lb1.LpuBuilding_id = lu1.LpuBuilding_id
			left join dbo.Diag di (nolock) on di.Diag_id = isnull(eps.Diag_pid, eps.Diag_id)
			inner join dbo.v_Person_all pe (nolock) on pe.PersonEvn_id = eps.PersonEvn_id and pe.Server_id = eps.Server_id
			left join dbo.v_Polis po (nolock) on po.Polis_id = pe.Polis_id
			left join dbo.PolisType pt (nolock) on pt.PolisType_id = po.PolisType_id
			inner join dbo.v_LpuUnit lu (nolock) on lu.LpuUnit_id = ls.LpuUnit_id
			left join r66.v_LpuSectionLink lsl with(nolock) on lsl.LpuSection_id = ls.LpuSection_id
			left join fed.v_MedicalCareKind mck (nolock) on mck.MedicalCareKind_id = lsl.MedicalCareKind_id
			where (1=1)
			and (@Lpu_id is null or eps.Lpu_id=@Lpu_id)
			and eps.EvnPS_setDT>=@startTime and eps.EvnPS_setDT<=@finalTime
			and eps.PrehospWaifRefuseCause_id is null --and prt.PrehospType_Code=1
			--and lu.LpuUnitType_SysNick = 'stac'
			and ls.LpuSection_Code <> 660267
			and mck.MedicalCareKind_Code in (3,31,32)
			and exists(
				select *
				from v_LpuSectionFinans lsf with(nolock)
				inner join v_PayType PayType with(nolock) on PayType.PayType_id = lsf.PayType_id
				where lsf.LpuSection_id = ls.LpuSection_id
				and lsf.LpuSectionFinans_begDate <= eps.EvnPS_setDT
				and (lsf.LpuSectionFinans_endDate is null or lsf.LpuSectionFinans_endDate > eps.EvnPS_setDT)
				and PayType.PayType_SysNick = 'oms'
			)
			and
			(prt.PrehospType_SysNick = 'extreme' or ed.EvnDirection_id is not null
			or (SUBSTRING(eps.EvnDirection_Num,1,4) = cast(year(eps.EvnDirection_setDT) as varchar)
			and SUBSTRING(eps.EvnDirection_Num,5,4) = right('0000'+isnull(lp.Lpu_RegNomN2,'0'),4)
			and len(SUBSTRING(eps.EvnDirection_Num,9,LEN(eps.EvnDirection_Num))) > 0))
		";

		$query3 = "
			declare @Lpu_id bigint = :Lpu_id

			declare @date datetime = :Date
			declare @startTime datetime = dateadd(day, -2, cast(@date+' '+:startTime as datetime))
			declare @finalTime datetime = dateadd(day, -1, cast(@date+' '+:finalTime as datetime))

			--GetCancelReferToHosp информация об аннулировании направления
			select
			0+ROW_NUMBER() over (order by ed.EvnDirection_id) as N_ZAP, --Int -- bigint , Идентификатор направления
			cast(year(ed.EvnDirection_setDT) as varchar)+case
			when LEN(ISNULL(cast(lp.Lpu_RegNomN2 as varchar),'0'))<4
			then REPLICATE('0', 4-LEN(ISNULL(cast(lp.Lpu_RegNomN2 as varchar),'0')))+ISNULL(cast(lp.Lpu_RegNomN2 as varchar),'0')
			else ISNULL(cast(lp.Lpu_RegNomN2 as varchar),'0') end
			+ right(REPLICATE('0', 6) + cast(ed.EvnDirection_Num as varchar), 6) as NUMBTREND,	--T(16) Номер направления (MCOD+NP)
			convert(varchar(10), ed.EvnDirection_setDT, 120) as DATETREND, --Дата направления
			lp.Lpu_f003mcod as MO_TREND, -- T(6)-- numeric(6, 0) - МО, направившая на госпитализацию	(реестровый номер, F003)
			dlo.CodeDLO as DOCTOR, -- numeric(6, 0) - Код медработника, выдавшего направление
			case
				when lu1.LpuUnitType_SysNick = 'stac' then '1'
				else '2'
			end as TYPECARD, -- numeric(1, 0) -- тип подразделения ЛПУ у группы отделений
			lp1.Lpu_f003mcod as MO_IN, -- numeric(6, 0) -- Код МО, куда направлен 
			case when dr.DirFailType_Code=5 then 1 when dr.DirFailType_Code=13 then 4 when dr.DirFailType_Code in (1,2,4,6,11,9,10) then 2 else 5 end as	REASON,-- int  Причина отмены направления

			pt.PolisType_CodeF008 as VPOLIS,-- numeric(1, 0) , -- Тип документа, подтверждающего факт страхования по ОМС (F008)
			case when pt.PolisType_CodeF008 in (1) then po.Polis_Ser else Null end as SPOLIS, -- Varchar(10) - Серия документа, подтверждающего факт страхования по ОМС
			case
				when pt.PolisType_CodeF008=1 then
					case when len(po.Polis_Num)<7 then right(replicate('0',7)+po.Polis_Num,7) else po.Polis_Num end
				when pt.PolisType_CodeF008=2 then
					case when len(po.Polis_Num)<9 then right(replicate('0',9)+po.Polis_Num,9) else po.Polis_Num end
				else
					case when len(pe.Person_EdNum)<16 then right(replicate('0',16)+pe.Person_EdNum,16) else pe.Person_EdNum end
			end as NPOLIS, -- Numeric(20) - Номер документа, подтверждающего факт страхования по ОМС
			pe.Person_SurName as FAM, --varchar(30) – Фамилия
			pe.Person_FirName as IM, --varchar(30) – Имя
			pe.Person_SecName as OT, --varchar(30) – Отчество
			case when pe.Sex_id=3 then 1 else pe.Sex_id end as W, --numeric(1) – пол (1 - муж, 2 - жен)
			convert(varchar(10), pe.Person_BirthDay, 120) as DR --date – дата рождения
			from dbo.v_EvnDirection ed (nolock)
			--left join r30.EvnNPR npr (nolock) on ed.EvnDirection_id=npr.Evn_id
			inner join dbo.DirFailType dr (nolock) on dr.DirFailType_id = ed.DirFailType_id
			--inner join dbo.pmUserCache puc (nolock) on ed.pmUser_failID=puc.PMUser_id
			--inner join v_lpu smolp (nolock) on smolp.Lpu_id = puc.Lpu_id
			inner join v_Lpu lp (nolock) on lp.Lpu_id = ed.Lpu_id
			left join v_Lpu lp1 (nolock) on lp1.Lpu_id = ed.Lpu_did
			left join persis.KodDLO dlo (nolock) on ed.MedPersonal_id = dlo.MedWorker_id
			inner join dbo.v_Person_all pe (nolock) on pe.PersonEvn_id = ed.PersonEvn_id and pe.Server_id = ed.Server_id
			left join dbo.v_Polis po (nolock) on po.Polis_id = pe.Polis_id
			left join dbo.PolisType pt (nolock) on pt.PolisType_id = po.PolisType_id and isnull(pt.PolisType_CodeF008,0)<>0
			left join dbo.v_MedStaffFact msf (nolock) on msf.MedStaffFact_id = ed.MedStaffFact_id
			left join v_LpuSection ls (nolock) on ls.LpuSection_id = isnull(ed.LpuSection_id, msf.LpuSection_id)
			left join v_LpuSection ls1 with(nolock) on ls1.LpuSection_id = ed.LpuSection_did
			left join dbo.v_LpuUnit lu (nolock) on lu.LpuUnit_id = ls1.LpuUnit_id
			left join dbo.v_LpuUnit lu1 (nolock) on lu1.LpuUnit_id = ls.LpuUnit_id
			left join r66.v_LpuSectionLink lsl with(nolock) on lsl.LpuSection_id = ls1.LpuSection_id
			left join fed.v_MedicalCareKind mck (nolock) on mck.MedicalCareKind_id = lsl.MedicalCareKind_id
			where(1=1)
			and ed.DirType_id in (1,5)
			and (@Lpu_id is null or ed.Lpu_id=@Lpu_id)
			--and ed.EvnDirection_failDT>=@startTime and ed.EvnDirection_failDT<=@finalTime
			and cast(ed.EvnDirection_failDT as date) = dateadd(day, -1, @date)
			and (ed.LpuSection_did is null
				or
				(
				ls1.LpuSection_Code <> 660267
				and mck.MedicalCareKind_Code in (3,31,32)
				and exists(
					select *
					from v_LpuSectionFinans lsf with(nolock)
					inner join v_PayType PayType with(nolock) on PayType.PayType_id = lsf.PayType_id
					where lsf.LpuSection_id = ls1.LpuSection_id
					and lsf.LpuSectionFinans_begDate <= ed.EvnDirection_setDT
					and (lsf.LpuSectionFinans_endDate is null or lsf.LpuSectionFinans_endDate > ed.EvnDirection_setDT)
					and PayType.PayType_SysNick = 'oms'
				))
			)
			and dr.DirFailType_Code != 14  -- Неверный ввод
		";

		$query_couch_info = "
			select
				t.*
			from
				rpt66.Han_hosp_enable(:Lpu_id,:Date) t
		";

		$hosp_data_xml_arr = array();
		$lpu_rcode_arr = array();
		foreach($lpu_arr as $lpu) {
			$params['Lpu_id'] = $lpu['Lpu_id'];

			//echo getDebugSQL($query, $params);exit;
			/*$hosp_data_xml_arr[$lpu['fcode']] = $this->getFirstRowFromQuery($query, $params);
			if ($hosp_data_xml_arr[$lpu['fcode']] == false) {
				return false;
			}*/

// print_r(getDebugSQL($query1, $params));

			$result = $this->db->query($query1, $params);
			if (is_object($result)) {
				$resp = $result->result('array');
				foreach ($resp as &$respone) {
					if (!empty($respone['PHONE'])) {
						$respone['PHONE'] = preg_replace('/[^0-9]/', '', $respone['PHONE']);
					}
				}
				$hosp_data_xml_arr[$lpu['fcode']]["m1"] = $this->parser->parse('export_xml/hosp_data_for_tfoms_ekb_m1', array('ZAP' => $resp), true);
			}
			$result = $this->db->query($query2, $params);
			if (is_object($result)) {
				$resp = $result->result('array');
				$hosp_data_xml_arr[$lpu['fcode']]["m2"] = $this->parser->parse('export_xml/hosp_data_for_tfoms_ekb_m2', array('ZAP' => $resp), true);
			}
			$result = $this->db->query($query3, $params);
			if (is_object($result)) {
				$resp = $result->result('array');
				$hosp_data_xml_arr[$lpu['fcode']]["m3"] = $this->parser->parse('export_xml/hosp_data_for_tfoms_ekb_m3', array('ZAP' => $resp), true);
			}

			// var_dump($hosp_data_xml_arr[$lpu['fcode']]);

			//echo getDebugSQL($query_couch_info, $params);exit;
			$result = $this->db->query($query_couch_info, array(
				'Lpu_id' => $params['Lpu_id'],
				'Date' => Date('Y-m-d', strtotime($params['Date']))
			));
			if (is_object($result)) {
				$resp = $result->result('array');

				$hosp_data_xml_arr[$lpu['fcode']]['m4'] = $this->parser->parse('export_xml/hosp_data_for_tfoms_ekb_m4', array('ZAP' => $resp), true);
			}

			$lpu_rcode_arr[$lpu['fcode']] = $lpu['rcode'];
		}

		return array(
			'hosp_data_xml_arr' => $hosp_data_xml_arr,
			'lpu_rcode_arr' => $lpu_rcode_arr
		);
	}

}