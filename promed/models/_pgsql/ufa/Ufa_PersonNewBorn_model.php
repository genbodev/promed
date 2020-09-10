<?php defined('BASEPATH') or die ('No direct script access allowed');
require_once(APPPATH.'models/_pgsql/PersonNewBorn_model.php');

class Ufa_PersonNewBorn_model extends PersonNewBorn_model {
	/**
	 * @construct
	 */
	function __construct() {
		parent::__construct();
	}

    /**
     *
     * @param type $data
     * @return type
     */
    function loadMonitorBirthSpecGrid($data){
        $params =array();
        $where = '';
		$from = '';
        if ( !empty($data['Person_FIO']) ) {
            $where .= " and (COALESCE(PS.Person_Surname, '') || ' ' || COALESCE(PS.Person_Firname, '') || ' ' || COALESCE(PS.Person_Secname, '')) ilike :Person_FIO";
            $params['Person_FIO'] = $data['Person_FIO'] . '%';
        }
		if(isset($data['PersonNewBorn_IsNeonatal'])){
			if ($data['PersonNewBorn_IsNeonatal'] == 2){
				$where .= " and pch.PersonNewBorn_IsNeonatal =:PersonNewBorn_IsNeonatal";
				$params['PersonNewBorn_IsNeonatal'] = $data['PersonNewBorn_IsNeonatal'];
			}else{
				$where .= " and (pch.PersonNewBorn_IsNeonatal is null or pch.PersonNewBorn_IsNeonatal=1)";
			}
		}
		if ( isset($data['Period_DateRange'][0]) )
		{
			$where .= " and cast(ps.Person_BirthDay as date) >= cast(:Beg_Date as date)";
			$params['Beg_Date'] = $data['Period_DateRange'][0];
		}

		if ( isset($data['Period_DateRange'][1]) )
		{
			$where .= " and cast(ps.Person_BirthDay as date) <= cast(:End_Date as date)";
			$params['End_Date'] = $data['Period_DateRange'][1];
		}
		if ( isset($data['State_id']) )
		{
			switch($data['State_id']){
				case 1:
					$where .= " and mother_evnps.EvnPS_disDate is null and close_evnps_any.EvnPS_setDate is null and last_close_evnps.EvnPS_id is null and last_open_evnps.EvnPS_id is null and (ps.Person_deadDT is null and COALESCE(PntDeathSvid.PntDeathSvid_id,'0')='0')";
					break;
				case 2:
					$where .= " and ".
						"  ((mother_evnps.EvnPS_disDate is not null or close_evnps_any.EvnPS_setDate is not null) and last_open_evnps.EvnPS_id is null and (ps.Person_deadDT is null and COALESCE(PntDeathSvid.PntDeathSvid_id,'0')='0')".
						"	or last_open_evnps.EvnPS_id is not null and last_close_evnps.EvnPS_id is null and (ps.Person_deadDT is null and COALESCE(PntDeathSvid.PntDeathSvid_id,'0')='0')".
						"	or (ps.Person_deadDT is not null or COALESCE(PntDeathSvid.PntDeathSvid_id,'0')!='0')".
						"	or (last_open_evnps.EvnPS_id is not null and last_close_evnps.EvnPS_id is not null))";
					break;
				case 3:
					$where .= " and (mother_evnps.EvnPS_disDate is not null or close_evnps_any.EvnPS_setDate is not null) and last_open_evnps.EvnPS_id is null and (ps.Person_deadDT is null and COALESCE(PntDeathSvid.PntDeathSvid_id,'0')='0')";
					break;
				case 4:
					$where .= " and last_open_evnps.EvnPS_id is not null and last_close_evnps.EvnPS_id is null and (ps.Person_deadDT is null and COALESCE(PntDeathSvid.PntDeathSvid_id,'0')='0')";
					break;
				case 5:
					$where .= " and (ps.Person_deadDT is not null or COALESCE(PntDeathSvid.PntDeathSvid_id,'0')!='0')";
					break;
				case 6:
					$where .= " and last_open_evnps.EvnPS_id is not null and last_close_evnps.EvnPS_id is not null";
					break;
				case 7:
					$where .= " and last_open_evnps.EvnPS_id is not null";
					break;
				case 8:
					$where .= " and lpulech.Lpu_Nick is not null";
					break;
			}
			$params['State_id'] = $data['State_id'];
		}

		if ( !empty($data['Lpu_bid']) ) {
			$where .= " and mLP.Lpu_id=:Lpu_bid";
			$params['Lpu_bid'] = $data['Lpu_bid'];
		}
		if ( !empty($data['Lpu_tid']) ) {
			$where .= " and PC.Lpu_id=:Lpu_tid";
			$params['Lpu_tid'] = $data['Lpu_tid'];
		}
		$panelyear = 1;
		if (!empty($data['Type']))
			switch($data['Type']){
				case 'NewBornPanel':
					$where .= " and now() - interval '29 day' < ps.Person_BirthDay";
					if(isset($data['PersonNewBorn_IsHighRisk'])&&$data['PersonNewBorn_IsHighRisk']==1){
						//$where .= " and pch.PersonNewBorn_IsHighRisk = 2";
						$where .= " and not gethighrisk(pch.PersonNewBorn_id, ps.Person_id) = ''";
					}
					break;
				case 'OneAgePanel':
					$where .= " and now() - interval '1 year' < ps.Person_BirthDay";
					if(isset($data['PersonNewBorn_IsHighRisk'])&&$data['PersonNewBorn_IsHighRisk']==1){
						//$where .= " and pch.PersonNewBorn_IsHighRisk = 2";
						$where .= " and not gethighrisk(pch.PersonNewBorn_id, ps.Person_id) = ''";
					}
					break;
				case 'AllAgePanel':
					$where .= " and now() - interval '1 year' >= ps.Person_BirthDay and now() - interval '18 year' < ps.Person_BirthDay";
					$panelyear=18;
					break;
				case 'MonitorCenterPanel':
					$where .= " and 1=1";
					break;
			}

		if ( !empty($data['Person_SurName']) ) {
			$where .= " and COALESCE(PS.Person_Surname, '') ilike :Person_Surname||'%'";
			$params['Person_Surname'] = $data['Person_SurName'];
		}

		if ( !empty($data['Person_SecName']) ) {
			$where .= " and COALESCE(PS.Person_SecName, '') ilike :Person_SecName||'%'";
			$params['Person_SecName'] = $data['Person_SecName'];
		}

		if ( !empty($data['Person_FirName']) ) {
			$where .= " and COALESCE(PS.Person_FirName, '') ilike :Person_FirName||'%'";
			$params['Person_FirName'] = $data['Person_FirName'];
		}

		if ( isset($data['Period_DDateRange'][0]) )
		{
			$where .= " and cast(ps.Person_BirthDay as date) >= cast(:Beg_Date as date)";
			$params['Beg_Date'] = $data['Period_DDateRange'][0];
		}

		if ( isset($data['Period_DDateRange'][1]) )
		{
			$where .= " and cast(ps.Person_BirthDay as date) <= cast(:End_Date as date)";
			$params['End_Date'] = $data['Period_DDateRange'][1];
		}

		if ( !empty($data['Lpu_hhid']) ) {
			$where .= " and open_evnps.Lpu_id=:Lpu_hhid";
			$params['Lpu_hhid'] = $data['Lpu_hhid'];
		}
		if ( !empty($data['Lpu_ttid']) ) {
			$where .= " and PC.Lpu_id=:Lpu_ttid";
			$params['Lpu_ttid'] = $data['Lpu_ttid'];
		}

		if ( !empty($data['NumberList_idd']) && $data['NumberList_idd'] != 400 ) {
			$where .= "
				and CAST(EXTRACT(MONTH from age(getdate(), ps.Person_BirthDay)) as int)=:NumberList_idd
			";
			$params['NumberList_idd'] = $data['NumberList_idd']-1;
		}

		if ( !empty($data['NumberList_id']) && $data['NumberList_id'] != 400) {
			$where .= "
				and CAST(EXTRACT(MONTH from age(getdate(), ps.Person_BirthDay)) as int)=:NumberList_id
			";
			$params['NumberList_id'] = $data['NumberList_id']-1;
		}

		if ( !empty($data['NumberList_aid']) && $data['NumberList_aid'] != 400) {
			$where .= "
				and CAST(EXTRACT(YEAR from age(getdate(), ps.Person_BirthDay)) as int)=:NumberList_aid
			";
			$params['NumberList_aid'] = $data['NumberList_aid']-1;
		}

		//ограничение записей для оператора
		$userGroups = array();
		$descOnlyOper = '';
		$fromOnlyOper = '';
		$whereOnlyOper = '';
		if (!empty($_SESSION['groups']) && is_string($_SESSION['groups'])) {
			$userGroups = explode('|', $_SESSION['groups']);
		}
		if (count(array_intersect(array('OperBirth'), $userGroups)) > 0 &&
			count(array_intersect(array('OperRegBirth'), $userGroups)) <= 0){
			/*
			$fromOnlyOper = '
			left join lateral(
				SELECT
					hv0.lpu_id
				FROM v_HomeVisit hv0
					 left join v_EvnPS eps on eps.EvnPS_id = pch.EvnPS_id
				WHERE hv0.Person_id=EPS.Person_id and hv0.HomeVisitStatus_id in (1,3,6)
				ORDER BY hv0.HomeVisit_setDT desc
				LIMIT 1
			)as hv1 on true
			';*/
			$whereOnlyOper = ' and (PC.Lpu_id=(select id from usertable) or mLP.Lpu_id=(select id from usertable) or last_open_evnps.Lpu_id=(select id from usertable) /*or hv1.lpu_id=(select id from usertable)*/or vizit.lpu_id=(select id from usertable)) ';
			$descOnlyOper = '
				with usertable as (select cast(:UserLpu_id as int) as id)
			';
			$params['UserLpu_id'] = $data['session']['lpu_id'];
		}


		//во вкладке новорожденные, отображаем только детей на которых заполнена специфика
		if (!empty($data['Type'])){
			if ($data['Type'] == 'NewBornPanel'){
				if ( !empty($data['Lpu_hid']) ) {
					$where .= " and last_open_evnps.Lpu_id=:Lpu_hid";
					$params['Lpu_hid'] = $data['Lpu_hid'];
				}
				if ( !empty($data['Lpu_pid']) ) {
					$where .= " and vizit.Lpu_id=:Lpu_pid";
					$params['Lpu_pid'] = $data['Lpu_pid'];
				}
				if ( !empty($data['Lpu_lid']) ) {
					$where .= " and vizit.Lpu_id=:Lpu_lid";
					$params['Lpu_lid'] = $data['Lpu_lid'];
				}/*
				if ( !empty($data['PersonNewBorn_IsBCG']) ) {
					$where .= " and pch.PersonNewborn_IsBCG=:PersonNewBorn_IsBCG";
					$params['PersonNewBorn_IsBCG'] = $data['PersonNewBorn_IsBCG'];
				}else{
					$where .= " and pch.PersonNewborn_IsBCG is null";
				}
				if ( !empty($data['PersonNewBorn_IsHepatit']) ) {
					$where .= " and pch.PersonNewborn_IsHepatit=:PersonNewBorn_IsHepatit";
					$params['PersonNewBorn_IsHepatit'] = $data['PersonNewBorn_IsHepatit'];
				}else{
					$where .= " and pch.PersonNewborn_IsHepatit is null";
				}*/
				if ( !empty($data['PersonNewBorn_IsBCG']) ) {
					if ($data['PersonNewBorn_IsBCG']== 1)
						$where .= " and (pch.PersonNewborn_IsBCG=:PersonNewBorn_IsBCG or pch.PersonNewborn_IsBCG is null)";
					else
						$where .= " and pch.PersonNewborn_IsBCG=:PersonNewBorn_IsBCG";
					$params['PersonNewBorn_IsBCG'] = $data['PersonNewBorn_IsBCG'];
				}
				if ( !empty($data['PersonNewBorn_IsHepatit']) ) {
					if ($data['PersonNewBorn_IsHepatit'] == 1)
						$where .= " and (pch.PersonNewborn_IsHepatit=:PersonNewBorn_IsHepatit or pch.PersonNewborn_IsHepatit is null)";
					else
						$where .= " and pch.PersonNewborn_IsHepatit=:PersonNewBorn_IsHepatit";
					$params['PersonNewBorn_IsHepatit'] = $data['PersonNewBorn_IsHepatit'];
				}
				if ( !empty($data['DegreeOfPrematurity_id']) ) {
					$where .= " and (case
										when COALESCE(W.PersonWeight_Weight, pch.PersonNewBorn_Weight) < 1000 then 1
										when COALESCE(W.PersonWeight_Weight, pch.PersonNewBorn_Weight) < 1500 then 2
										when COALESCE(W.PersonWeight_Weight, pch.PersonNewBorn_Weight) = 1500 then
											(case when pch.ChildTermType_id = 2 then 3 else 10 end)
										else 10 end
									)=:DegreeOfPrematurity_id";
					$params['DegreeOfPrematurity_id'] = $data['DegreeOfPrematurity_id'];
				}
				if (!empty($data['Diag_Code_From'])) {
					$where .= " and first_evnps.Diag_Code >= :Diag_Code_From";
					$params['Diag_Code_From'] = $data['Diag_Code_From'];
				}
				if (!empty($data['Diag_Code_To'])) {
					$where .= " and first_evnps.Diag_Code <= :Diag_Code_To";
					$params['Diag_Code_To'] = $data['Diag_Code_To'];
				}
				$query = "
					-- variables
					{$descOnlyOper}
					-- end variables

					select
						-- select
						ps.Person_id as \"Person_id\",
						case when pch.PersonNewBorn_IsHighRisk = 2 then 1 else 0 end as \"isHighRisk\",
						ps.Person_SurName ||' '||LEFT(COALESCE(ps.Person_FirName,''),1)||' '||LEFT(COALESCE(ps.Person_SecName,''),1) as \"Person_FIO\",
						to_char(ps.Person_BirthDay, 'dd.mm.yyyy') as \"Person_BirthDay\",
						COALESCE(W.PersonWeight_Weight, pch.PersonNewBorn_Weight) as \"PersonNewBorn_Weight\",
						BirthSvid.BirthSvid_id as \"BirthSvid_id\",
						BirthSvid.BirthSvid_Num as \"BirthSvid\",
						PntDeathSvid.PntDeathSvid_id as \"PntDeathSvid_id\",
						PntDeathSvid.PntDeathSvid_Num as \"DeathSvid\",
						ps.Server_id AS \"Server_id\",
						pch.Person_id AS \"Person_cid\",
						mPS.Person_id as \"Person_mid\",
						mLP.Lpu_Nick as \"LpuBirth\",
						--lp.Lpu_Nick as \"LpuHosp\",
						last_open_evnps.Lpu_Nick as \"LpuHosp\",
						pch.PersonNewborn_id as \"PersonNewborn_id\",
						cast(agp.NewbornApgarRate_Values as int) as \"NewbornApgarRate_Values\",
						case
							when ps.Person_deadDT is not null or COALESCE(PntDeathSvid.PntDeathSvid_id,'0')!='0' then 'Умер'
							when ((mother_evnps.EvnPS_disDate is not null or close_evnps_any.EvnPS_setDate is not null) and last_open_evnps.EvnPS_id is null) then 'Выписан'
							when (last_open_evnps.EvnPS_id is not null and last_close_evnps.EvnPS_id is not null) then 'Переведен в другую МО'
							when (last_open_evnps.EvnPS_id is not null and last_close_evnps.EvnPS_id is null and (ps.Person_deadDT is null and COALESCE(PntDeathSvid.PntDeathSvid_id,'0')='0')) then 'В стационаре'
							else ''
						end as \"State\",
						case when COALESCE(pch.PersonNewBorn_IsNeonatal,1)=2 then 'true' else '' end as \"PersonNewBorn_IsNeonatal\",
						case when COALESCE(pch.PersonNewborn_IsAidsMother,1)=2 then 'true' else '' end as \"PersonNewborn_IsAidsMother\",
						case when COALESCE(pch.PersonNewborn_IsRejection,1)=2 then 'true' else '' end as \"PersonNewborn_IsRejection\",
						Deputy.Deputy_FIO as \"Deputy_FIO\",
						Deputy.Deputy_Addres as \"Deputy_Addres\",
						Deputy.Deputy_Phone as \"Deputy_Phone\",
						DP.DegreeOfPrematurity_Name as \"DegreeOfPrematurity_Name\",
						first_evnps.Diag as \"Diag\",
						YN1.YesNo_Name as \"PersonNewborn_IsBCG\",
						YN2.YesNo_Name as \"PersonNewBorn_IsHepatit\",
						gethighrisk(pch.PersonNewBorn_id, ps.Person_id) as \"listHighRisk\"
						-- end select
					from
						-- from
						v_PersonState ps
						left join lateral(
							select
								pch0.*
							from
								v_PersonNewBorn pch0
							where
								pch0.Person_id = ps.Person_id
							order by pch0.PersonNewBorn_insDT asc
							limit 1
						)as pch on true
						left join v_BirthSpecStac BSS on pch.birthspecstac_id = BSS.BirthSpecStac_id
						left join lateral(
							select
								COALESCE(psd.Person_SurName,'') || ' ' || COALESCE(psd.Person_FirName,'') || ' ' || COALESCE(psd.Person_SecName,'') as Deputy_FIO,
								COALESCE(Adr.Address_Address,'') as Deputy_Addres,
								COALESCE(psd.Person_Phone,'') as Deputy_Phone
							from v_PersonDeputy PD
							inner join v_PersonState psd on psd.Person_id = PD.Person_pid and PD.Person_id = ps.Person_id
							left join v_Address Adr on Adr.Address_id = psd.PAddress_id
							LIMIT 1
						)as Deputy on true
						left join lateral(
							select
								1 as Trauma
							from
								v_PersonBirthTrauma PC
							where
								PC.PersonNewBorn_id = pch.PersonNewBorn_id
							limit 1
						)as tr on true
						left join lateral(
							select a.NewbornApgarRate_Values
							from v_NewbornApgarRate a
							where a.PersonNewborn_id = pch.PersonNewborn_id and a.NewbornApgarRate_Values is not null
							order by NewbornApgarRate_Time desc, NewbornApgarRate_insDT desc
							limit 1
						)as agp on true
						left join v_EvnSection mES on mES.EvnSection_id = BSS.EvnSection_id
						left join v_PersonRegister mPR on mPR.PersonRegister_id = BSS.PersonRegister_id
						left join v_PersonState mPS on mPS.Person_id = COALESCE(mPR.Person_id, mES.Person_id)
						left join v_Lpu_all mLP on mLP.Lpu_id = COALESCE(BSS.Lpu_id, mES.Lpu_id)
						left join v_EvnPS eps on eps.EvnPS_id = pch.EvnPS_id
						left join v_Lpu_all lp on lp.lpu_id=eps.Lpu_id
						left join lateral(SELECT pds.PntDeathSvid_id,pds.PntDeathSvid_Num FROM v_PntDeathSvid pds WHERE pds.Person_cid = pch.Person_id and (pds.PntDeathSvid_isBad is null or pds.PntDeathSvid_isBad=1) order by pds.PntDeathSvid_insDT desc limit 1) as PntDeathSvid on true
						left join lateral(SELECT BirthSvid_id,BirthSvid_Num FROM v_BirthSvid bs WHERE bs.Person_cid = pch.Person_id limit 1) AS BirthSvid on true
						left join lateral(select case when pw.Okei_id=37 then pw.PersonWeight_Weight*1000 else pw.PersonWeight_Weight end as PersonWeight_Weight,pw.PersonWeight_id from v_personWeight pw where pw.person_id =pch.Person_id and pw.WeightMeasureType_id = 1 limit 1)as W on true
						left join lateral(
							select
								EPS.EvnPS_setDate, /*EPL.EvnPl_setDT,*/HV.HomeVisit_setDT, patr.Lpu_Name, EPS.Lpu_id
							from v_EvnPS EPS
								left join v_HomeVisit hv on hv.Person_id=EPS.Person_id
								left join v_Lpu patr on patr.Lpu_id = EPS.Lpu_id
								--left join v_EvnPL EPL on EPL.Person_id=PS.Person_id
								--left join v_Lpu patr on patr.Lpu_id = EPL.Lpu_id
									and EPS.EvnPS_setDate <= hv.HomeVisit_setDT and hv.HomeVisit_setDT <= EPS.EvnPS_setDate + interval '3 day'
							where
								EPS.EvnPS_disDate is not null and EPS.Person_id=PS.Person_id and EPS.EvnPS_setDate + interval '7 day' >=now()
							limit 1
						)as close_evnps  on true
						left join lateral(
							select
								EPS.EvnPS_setDate
							from v_EvnPS EPS
							where
								EPS.EvnPS_disDate is not null and EPS.Person_id=PS.Person_id
							limit 1
						)as close_evnps_any  on true
						left join v_PersonCard PC on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
						left join v_Lpu_all LpuAttach on LpuAttach.Lpu_id = PC.Lpu_id
						left join lateral(
							select
								EPS.EvnPS_setDate, gospit.Lpu_Nick, EPS.Lpu_id, EPS.EvnPS_disDT, EPS.EvnPS_id
							from v_EvnPS EPS
								left join v_Lpu gospit on gospit.Lpu_id = EPS.Lpu_id
							where
								EPS.Person_id=PS.Person_id and EPS.EvnPS_disDate is null
							order by EPS.EvnPS_setDate desc
							limit 1
						)as last_open_evnps on true
						left join lateral(
							select
								EPS.EvnPS_setDate, gospit.Lpu_Nick, EPS.Lpu_id, EPS.EvnPS_disDT, EPS.EvnPS_id
							from v_EvnPS EPS
								inner join v_EvnSection EPSLastES on EPSLastES.EvnSection_pid = EPS.EvnPS_id and EPSLastES.EvnSection_Index = EPSLastES.EvnSection_Count-1
								left join v_Lpu gospit on gospit.Lpu_id = EPS.Lpu_id
								inner join v_LeaveType lt on lt.LeaveType_id = EPSLastES.LeaveType_id and lt.LeaveType_Code=2
							where
								EPS.Person_id=PS.Person_id and EPS.EvnPS_disDate is not null
							order by EPS.EvnPS_setDate desc
							limit 1
						)as last_close_evnps on true
						left join lateral(
							select
								HV.HomeVisit_setDT, patr.Lpu_Name, hv.Lpu_id
							from v_HomeVisit hv
								left join v_Lpu patr on patr.Lpu_id = hv.Lpu_id
							where hv.Person_id=PS.person_id and not hv.HomeVisitStatus_id in (2,5)
							order by HV.HomeVisit_setDT desc
							limit 1
						)as vizit on true
						left join lateral(
							select
								D.Diag_Code, (D.Diag_Code || '.' || D.Diag_Name) as Diag
							from v_EvnPS EPS
								inner join v_Diag D on D.Diag_id = EPS.Diag_id
							where
								EPS.Person_id=PS.Person_id and ps.Person_BirthDay + interval '7 day' >=EPS.EvnPS_setDate
							limit 1
						)as first_evnps on true
						left join v_degreeofprematurity DP on DP.degreeofprematurity_id = (case
										when COALESCE(W.PersonWeight_Weight, pch.PersonNewBorn_Weight) < 1000 then 1
										when COALESCE(W.PersonWeight_Weight, pch.PersonNewBorn_Weight) < 1500 then 2
										when COALESCE(W.PersonWeight_Weight, pch.PersonNewBorn_Weight) = 1500 then
											(case when pch.ChildTermType_id = 2 then 3 else 10 end)
										else 10 end
									)
						left join v_YesNo YN1 on YN1.YesNo_id = pch.PersonNewborn_IsBCG
						left join v_YesNo YN2 on YN2.YesNo_id = pch.PersonNewborn_IsHepatit
						left join lateral(
							select
								EPS.EvnPS_disDate
							from v_EvnPS EPS
								inner join v_EvnSection ESM on ESM.EvnSection_pid = EPS.EvnPS_id
								inner join v_BirthSpecStac BSSM on BSSM.EvnSection_id = ESM.EvnSection_id
								inner join v_PersonNewBorn PNBM on (PNBM.BirthSpecStac_id = BSSM.BirthSpecStac_id /*or PNBM.EvnPS_id = EPS.EvnPS_id*/)
							where
								PNBM.Person_id=PS.person_id and EPS.EvnPS_disDate is not null
							order by EPS.EvnPS_setDate desc
							limit 1
						)as mother_evnps on true
						{$from}
						{$fromOnlyOper}
						-- end from
					where
						-- where
						(1=1)
						" . $where . "
						{$whereOnlyOper}
						-- end where
					order by
						-- order by
						ps.Person_Surname,
						ps.Person_Firname,
						ps.Person_Secname,
						ps.Person_Birthday
						-- end order by
				";
			}else if ($data['Type'] == 'OneAgePanel' || $data['Type'] == 'AllAgePanel'){

				if ( !empty($data['Lpu_hid']) ) {
					$where .= " and open_evnps.Lpu_id=:Lpu_hid";
					$params['Lpu_hid'] = $data['Lpu_hid'];
				}
				if ( !empty($data['Lpu_pid']) ) {
					/*
					$from.= "
						left join lateral(
							select
								HV.HomeVisit_setDT, patr.Lpu_Name, hv.Lpu_id
							from v_HomeVisit hv
								left join v_Lpu patr on patr.Lpu_id = hv.Lpu_id
							where hv.Person_id=PS.person_id and not hv.HomeVisitStatus_id in (2,5)
							order by HV.HomeVisit_setDT desc
							limit 1
						)as vizit on true
					";*/
					$where .= " and vizit.Lpu_id=:Lpu_pid";
					$params['Lpu_pid'] = $data['Lpu_pid'];
				}

				if (!empty($data['DirType_id'])){
					$where .= " and dirtype.Person_id is not null";
					$params['DirType_id'] = $data['DirType_id'];
					$from.= "
						left join lateral(
							SELECT
								ED.Person_id
							FROM
								v_EvnDirection_all ED
								inner join Evn Evn0 on Evn0.Evn_id = ED.EvnDirection_id and Evn0.Evn_deleted = 1
							WHERE
								ED.DirType_id != 24 and COALESCE(ED.DirType_id,1) not in (7, 18, 19, 20)
								and COALESCE(ED.EvnStatus_id,11) in (11,17,14,10,33) and ED.DirType_id = :DirType_id
								and ED.Person_id = PS.Person_id and Evn0.Evn_setDT is not null
							LIMIT 1
						)as dirtype on true
					";
				}

				//диспансерное наблюдение
				if(isset($data['DispensaryObservation'])&&$data['DispensaryObservation']==1){
					$from .= "
						left join lateral(
							SELECT
								dg1.Diag_Code||' '||dg1.diag_name as diag
							FROM
								v_PersonDisp PD
								left join v_Diag dg1 on PD.Diag_id = dg1.Diag_id
								WHERE
								PD.Person_id = ps.Person_id and
								coalesce(dg1.Diag_Code, '') not in('J30.0','C30.1') and
								coalesce(dg1.Diag_Code, '') not between 'A50.0' and 'A64' and
								PD.PersonDisp_endDate is null
							ORDER BY
								PD.PersonDisp_begDate desc
							LIMIT 1
						)as PersonDisp on true
					";
					$where .= " and PersonDisp.diag is not null ";
				}

				$query = "
					-- variables
					{$descOnlyOper}
					-- end variables

					select
						-- select
						pch.PersonNewBorn_id as \"PersonNewBorn_id\",
						ps.Person_id as \"Person_id\",
						case when pch.PersonNewBorn_IsHighRisk = 2 then 1 else 0 end as \"isHighRisk\",
						pch.Person_id AS \"Person_cid\",
						ps.Server_id AS \"Server_id\",
						TeenInspection.EvnPLDispTeenInspection_id as \"TeenInspection_id\",
						ps.Person_SurName ||' '||LEFT(COALESCE(ps.Person_FirName,''),1)||' '||LEFT(COALESCE(ps.Person_SecName,''),1) as \"Person_FIO\",
						to_char(ps.Person_BirthDay, 'dd.mm.yyyy') as \"Person_BirthDay\",
						LpuAttach.Lpu_Nick as \"LpuAttach_Nick\",
						lp.Lpu_Nick as \"LpuHosp\",
						lpulech.Lpu_Nick as \"LpuLech_Nick\",
						TeenInspection.EvnPLDispTeenInspection_disDate as \"TeenInspection_disDate\",
						getpersondisp(ps.Person_id) as \"PersonDisp\",
						getevndirections(ps.Person_id, {$panelyear}) as \"EvnDirectList\",
						getfirstdiagpl(ps.Person_id, {$panelyear}) as \"FirstDiag\",
						gethighrisk(pch.PersonNewBorn_id, ps.Person_id) as \"listHighRisk\"
						-- end select
					from
						-- from
						v_PersonState ps
						left join v_PersonNewBorn pch on ps.Person_id = pch.Person_id
						left join v_BirthSpecStac BSS on pch.birthspecstac_id = BSS.BirthSpecStac_id
						/*left join lateral(
							select
								COALESCE(psd.Person_SurName,'') || ' ' || COALESCE(psd.Person_FirName,'') || ' ' || COALESCE(psd.Person_SecName,'') as Deputy_FIO,
								COALESCE(Adr.Address_Address,'') as Deputy_Addres,
								COALESCE(psd.Person_Phone,'') as Deputy_Phone
							from v_PersonDeputy PD
							inner join v_PersonState psd on psd.Person_id = PD.Person_pid and PD.Person_id = ps.Person_id
							left join v_Address Adr on Adr.Address_id = psd.PAddress_id
							limit 1
						)as Deputy on true*/
						left join lateral(
							select
								1 as Trauma
							from
								v_PersonBirthTrauma PC
							where (1 = 1)
								and PC.PersonNewBorn_id = pch.PersonNewBorn_id
							limit 1
						)as tr on true
						/*left join lateral(
							select a.NewbornApgarRate_Values 
							from v_NewbornApgarRate a 
							where a.PersonNewborn_id = pch.PersonNewborn_id
							order by NewbornApgarRate_Time desc, NewbornApgarRate_insDT desc
							limit 1
						)as agp on true*/
						left join v_EvnSection mES on mES.EvnSection_id = BSS.EvnSection_id
						left join v_PersonRegister mPR on mPR.PersonRegister_id = BSS.PersonRegister_id
						/*left join v_PersonState mPS on mPS.Person_id = COALESCE(mPR.Person_id, mES.Person_id)*/
						left join v_Lpu_all mLP on mLP.Lpu_id = COALESCE(BSS.Lpu_id, mES.Lpu_id)
						/*left join v_EvnPS eps on eps.EvnPS_id = pch.EvnPS_id*/
						left join lateral(SELECT pds.PntDeathSvid_id,pds.PntDeathSvid_Num FROM v_PntDeathSvid pds WHERE pds.Person_cid = pch.Person_id and (pds.PntDeathSvid_isBad is null or pds.PntDeathSvid_isBad=1) order by pds.PntDeathSvid_insDT desc limit 1) as PntDeathSvid on true
						/*left join lateral(SELECT BirthSvid_id,BirthSvid_Num FROM v_BirthSvid bs WHERE bs.Person_cid = pch.Person_id limit 1) AS BirthSvid on true*/
						left join lateral(select case when pw.Okei_id=37 then pw.PersonWeight_Weight*1000 else pw.PersonWeight_Weight end as PersonWeight_Weight,pw.PersonWeight_id from v_personWeight pw where pw.person_id =pch.Person_id and pw.WeightMeasureType_id = 1 limit 1)as W on true
						left join v_PersonCard PC on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
						left join v_Lpu_all LpuAttach on LpuAttach.Lpu_id = PC.Lpu_id
						left join lateral(
							select
								epl0.Lpu_id
							from
								v_EvnPL epl0
							where epl0.Person_id=PS.Person_id and epl0.EvnPL_IsFinish=1
							order by epl0.EvnPL_setDT desc
							limit 1
						)as lpulech0 on true
						left join v_Lpu_all lpulech on lpulech.Lpu_id = lpulech0.Lpu_id
						/*left join lateral(
							select
								EPS.EvnPS_setDate, HV.HomeVisit_setDT, patr.Lpu_Name, EPS.Lpu_id
							from v_EvnPS EPS
								left join v_HomeVisit hv on hv.Person_id=EPS.Person_id
								left join v_Lpu patr on patr.Lpu_id = EPS.Lpu_id
								--left join v_EvnPL EPL on EPL.Person_id=PS.Person_id
								--left join v_Lpu patr on patr.Lpu_id = EPL.Lpu_id
									and EPS.EvnPS_setDate <= hv.HomeVisit_setDT and hv.HomeVisit_setDT <= EPS.EvnPS_setDate + interval '3 day'
							where
								EPS.EvnPS_disDate is not null and EPS.Person_id=PS.Person_id and EPS.EvnPS_setDate + interval '7 day' >=now()
							limit 1
						)as close_evnps on true*/
						left join lateral(
							SELECT
								EPLDTI.EvnPLDispTeenInspection_id as EvnPLDispTeenInspection_id,
								to_char(EPLDTI.EvnPLDispTeenInspection_disDate, 'dd.mm.yyyy') as EvnPLDispTeenInspection_disDate
							FROM
								v_EvnPLDispTeenInspection EPLDTI
							WHERE
								/*EPLDTI.EvnPLDispTeenInspection_setDate = cast('2020-01-24' as datetime)
								and EPLDTI.EvnPLDispTeenInspection_setDate >= cast('2020-01-01' as datetime)
								and EPLDTI.EvnPLDispTeenInspection_setDate <= cast('2020-12-31' as datetime)*/
								COALESCE(EPLDTI.DispClass_id, 6) in ( '10' ,'12')
								and EPLDTI.Person_id=PS.Person_id
							ORDER BY
								EPLDTI.EvnPLDispTeenInspection_setDate
							DESC
							LIMIT 1
						)as TeenInspection on true
						left join lateral(
							select
								EPS.EvnPS_setDate, EPS.EvnPS_id, EPS.Diag_pid, EPS.lpu_id
							from v_EvnPS EPS
							where
								EPS.EvnPS_disDate is null and EPS.Person_id=PS.Person_id
							limit 1
						)as open_evnps on true
						left join v_Lpu_all lp on lp.lpu_id=open_evnps.Lpu_id
						/*left join lateral(
							select
								HV.HomeVisit_setDT, patr.Lpu_Name, hv.Lpu_id
							from v_HomeVisit hv
								left join v_Lpu patr on patr.Lpu_id = hv.Lpu_id
							where hv.Person_id=PS.person_id and not hv.HomeVisitStatus_id in (2,5)
							order by HV.HomeVisit_setDT desc
							limit 1
						)as vizit on true*/
						left join lateral(
							select
								EPS.EvnPS_setDate/*, gospit.Lpu_Nick*/, EPS.Lpu_id, EPS.EvnPS_disDT, EPS.EvnPS_id
							from v_EvnPS EPS
								/*left join v_Lpu gospit on gospit.Lpu_id = EPS.Lpu_id*/
							where
								EPS.Person_id=PS.Person_id and EPS.EvnPS_disDate is null
							order by EPS.EvnPS_setDate desc
							limit 1
						)as last_open_evnps on true
						left join lateral(
							select
								EPS.EvnPS_setDate, gospit.Lpu_Nick, EPS.Lpu_id, EPS.EvnPS_disDT, EPS.EvnPS_id
							from v_EvnPS EPS
								inner join v_EvnSection EPSLastES on EPSLastES.EvnSection_pid = EPS.EvnPS_id and EPSLastES.EvnSection_Index = EPSLastES.EvnSection_Count-1
								left join v_Lpu gospit on gospit.Lpu_id = EPS.Lpu_id
								inner join v_LeaveType lt on lt.LeaveType_id = EPSLastES.LeaveType_id and lt.LeaveType_Code=2
							where
								EPS.Person_id=PS.Person_id and EPS.EvnPS_disDate is not null
							order by EPS.EvnPS_setDate desc
							limit 1
						)as last_close_evnps on true
						left join lateral(
							select
								HV.HomeVisit_setDT, patr.Lpu_Name, hv.Lpu_id
							from v_HomeVisit hv
								left join v_Lpu patr on patr.Lpu_id = hv.Lpu_id
							where hv.Person_id=PS.person_id and not hv.HomeVisitStatus_id in (2,5)
							order by HV.HomeVisit_setDT desc
							limit 1
						)as vizit on true
						{$from}
						{$fromOnlyOper}
						-- end from
					where
						-- where
						(1=1)
						" . $where . "
						{$whereOnlyOper}
						-- end where
					order by
						-- order by
						ps.Person_Surname,
						ps.Person_Firname,
						ps.Person_Secname,
						ps.Person_Birthday
						-- end order by
				";
			}else{

				if ( !empty($data['Lpu_hid']) ) {
					$where .= " and open_evnps.Lpu_id=:Lpu_hid";
					$params['Lpu_hid'] = $data['Lpu_hid'];
				}
				if ( !empty($data['Lpu_pid']) ) {
					$where .= " and close_evnps.Lpu_id=:Lpu_pid";
					$params['Lpu_pid'] = $data['Lpu_pid'];
				}

				//направлен на госпитализацию
				if(isset($data['Hospitalization'])&&$data['Hospitalization']==1){
					$where .= " and EvnDirectionFiltr.EvnDirection_Num is not null ";
				}

				$query = "
					-- variables
					{$descOnlyOper}
					-- end variables

					SELECT
						-- select
						ps.Person_id as \"Person_id\",
						EvnDirection.EvnDirection_id as \"EvnDirection_id\",
						case when pch.PersonNewBorn_IsHighRisk = 2 then 1 else 0 end as \"isHighRisk\",
						ps.Server_id AS \"Server_id\",
						pch.Person_id AS \"Person_cid\",
						mPS.Person_id as \"Person_mid\",
						cmpcallcard.CmpCallCard_id as \"CmpCallCard_id\",
						open_evnps.Diag_pid as \"Diag_pid\",
						to_char(open_evnps.EvnPS_setDate, 'dd.mm.yyyy') as \"EvnPS_setDate\",
						ps.Person_SurName as \"Person_SurName\",
						ps.Person_FirName as \"Person_FirName\",
						ps.Person_SecName as \"Person_SecName\",
						to_char(ps.Person_BirthDay, 'dd.mm.yyyy') as \"Person_BirthDay\",
						(case when EXTRACT(MONTH from age(getdate(), ps.Person_BirthDay)) > 0 then EXTRACT(MONTH from age(getdate(), ps.Person_BirthDay))||' мес. ' else '' end)||
						(case when EXTRACT(DAY from age(getdate(), ps.Person_BirthDay)) > 0 then EXTRACT(DAY from age(getdate(), ps.Person_BirthDay))||' дн. ' else '' end)
						AS \"age\",
						LpuAttach.Lpu_Nick as \"LpuAttach_Nick\",
						lp.Lpu_Nick as \"LpuHosp\",
						extract(day from (now() - open_evnps.EvnPS_setDate)) as \"DayHosp\",
						(CASE WHEN EXISTS
								(SELECT
									esN.Person_id
								FROM
									v_EvnSectionNarrowBed AS esN
									INNER JOIN v_LpuSection lsNB ON lsNB.LpuSection_id = esN.LpuSection_id
									INNER JOIN v_LpuSectionProfile lsProfNB ON lsProfNB.LpuSectionProfile_id = lsNB.LpuSectionProfile_id
								WHERE
									(EvnSectionNarrowBed_pid = es.EvnSection_id) AND lsProfNB.LpuSectionProfile_Code ilike '[1-3]035')
									OR lsprof.LpuSectionProfile_Code ilike '[1-3]035' OR Reanimat.EvnReanimatPeriod_setDate is not null
							THEN '!'
							ELSE ''
						END) AS \"PersReanim\",
						(case when Diag.Diag_Code is not null then (Diag.Diag_Code || ' ' || Diag.Diag_Name)
							when pDiag.Diag_Code is not null then ('!' || pDiag.Diag_Code || ' ' || pDiag.Diag_Name)
							else '' end) \"EvnSectionDiag\",
						open_evnpl.EvnPL_id as \"EvnPL_id\",
						open_evnpl.EvnPL_NumCard as \"EvnPL_NumCard\",
						extract(day from (now() - open_evnpl.EvnPL_setDate)) as \"DayPL\",
						(case when plDiag.Diag_Code is not null then (plDiag.Diag_Code || ' ' || plDiag.Diag_Name) else '' end) as \"plDiag\",
						cmpcallcard.CmpCallCard_Numv as \"CmpCallCard_Numv\",
						cmpcallcard.CmpCallCard_prmDT as \"CmpCallCard_prmDT\",
						cmpcallcard.CmpReasonNew_Name as \"CmpReasonNew_Name\",
						(case when (close_evnps.EvnPS_setDate is not null and close_evnps.EvnPl_setDT is null and close_evnps.LeaveType_Code = 1) then '!' else '' end) as \"LpuPatr_Nick\",
						--EvnDirection.EvnDirection_Num as EvnDirection
						getevndirections(ps.Person_id, 3) as \"EvnDirectList\",
						gethighrisk(pch.PersonNewBorn_id, ps.Person_id) as \"listHighRisk\"
						-- end select
					FROM
						-- from
						v_PersonState ps
						left join v_PersonNewBorn pch on ps.Person_id = pch.Person_id
						left join v_BirthSpecStac BSS on pch.birthspecstac_id = BSS.BirthSpecStac_id
						left join v_EvnSection mES on mES.EvnSection_id = BSS.EvnSection_id
						left join v_Lpu_all mLP on mLP.Lpu_id = COALESCE(BSS.Lpu_id, mES.Lpu_id)
						left join v_PersonRegister mPR on mPR.PersonRegister_id = BSS.PersonRegister_id
						left join v_PersonState mPS on mPS.Person_id = COALESCE(mPR.Person_id, mES.Person_id)
						left join v_EvnPS eps on eps.EvnPS_id = pch.EvnPS_id
						left join v_PersonCard PC on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
						left join v_Lpu_all LpuAttach on LpuAttach.Lpu_id = PC.Lpu_id
						left join lateral(
							select
								EPS.EvnPS_setDate, EPS.EvnPS_id, EPS.Diag_pid, EPS.lpu_id
							from v_EvnPS EPS
							where
								EPS.EvnPS_disDate is null and EPS.Person_id=PS.Person_id
							limit 1
						)as open_evnps on true
						left join v_Lpu_all lp on lp.lpu_id=open_evnps.Lpu_id
						left join lateral(
							select
								EPS.EvnPS_setDate, EPL.EvnPl_setDT,/*HV.HomeVisit_setDT,*/ patr.Lpu_Name, LT.LeaveType_Code
							from v_EvnPS EPS
								--left join v_HomeVisit hv on hv.Person_id=EPS.Person_id
								--left join v_Lpu patr on patr.Lpu_id = EPS.Lpu_id
								left join v_EvnPL EPL on EPL.Person_id=PS.Person_id and EPS.EvnPS_setDate <= EPL.EvnPl_setDT
								left join v_Lpu patr on patr.Lpu_id = EPL.Lpu_id
								left join v_LeaveType LT on LT.LeaveType_id = EPS.LeaveType_id
									--and EPS.EvnPS_setDate <= hv.HomeVisit_setDT and hv.HomeVisit_setDT <= EPS.EvnPS_setDate + interval '3 day'
							where
								EPS.EvnPS_disDate is not null and EPS.Person_id=PS.Person_id
								and EPS.EvnPS_disDate + interval '7 day' >= now()
								and now() >= EPS.EvnPS_disDate + interval '3 day'
							limit 1
						)as close_evnps on true
						left join lateral(
							select
								EPL.EvnPL_setDate, EPL.EvnPL_id, EPL.EvnPL_NumCard, EPL.Diag_id
							from v_EvnPL EPL
							where
								EPL.EvnPL_setDate + interval '7 day' <= now() and EPL.EvnPL_IsFinish=1 and EPL.Person_id=PS.Person_id
							limit 1
						)as open_evnpl on true
						left join lateral(
							select
								to_char(cmpclose.AcceptTime, 'dd.mm.yyyy') as CmpCallCard_prmDT, 
								(case when EPS.EvnPS_setDate is null then EPL.EvnPL_setDate else EPS.EvnPS_setDate end ) as SetDate, cmp.CmpCallCard_Numv, reason.CmpReason_Name as CmpReasonNew_Name,
								cmp.CmpCallCard_id
							from v_CmpCallCard cmp
								inner join v_CmpCloseCard cmpclose on cmpclose.CmpCallCard_id=cmp.CmpCallCard_id
								--left join v_EvnPS EPS on EPS.Person_id=cmp.Person_id and cmp.CmpCallCard_prmDT <= EPS.EvnPS_setDate+EPS.EvnPS_setTime and EPS.EvnPS_setDate+EPS.EvnPS_setTime <= cmp.CmpCallCard_prmDT + interval '7 day'
								--left join v_EvnPL EPL on EPL.Person_id=cmp.Person_id and cmp.CmpCallCard_prmDT <= EPL.EvnPL_setDate+EPL.EvnPL_setTime and EPL.EvnPL_setDate+EPL.EvnPL_setTime <= cmp.CmpCallCard_prmDT + interval '7 day'
								left join v_EvnPS EPS on EPS.Person_id=cmp.Person_id and cmpclose.AcceptTime <= EPS.EvnPS_setDate+EPS.EvnPS_setTime and EPS.EvnPS_setDate+EPS.EvnPS_setTime <= cmpclose.AcceptTime + interval '7 day'
								left join v_EvnPL EPL on EPL.Person_id=cmp.Person_id and cmpclose.AcceptTime <= EPL.EvnPL_setDate+EPL.EvnPL_setTime and EPL.EvnPL_setDate+EPL.EvnPL_setTime <= cmpclose.AcceptTime + interval '7 day'
								left join v_CmpReason reason on reason.CmpReason_id=cmp.CmpReason_id
							where
								cmp.Person_id=PS.Person_id
								and now() <= cmpclose.AcceptTime + interval '7 day'
								and now() >= cmpclose.AcceptTime + interval '1 day'
							order by cmpclose.AcceptTime desc
							limit 1
						)as cmpcallcard on true
						left join lateral(
							select
								ed1.EvnDirection_Num, ed1.EvnDirection_id, ed1.DirType_id
							from v_EvnDirection ed1
								inner join v_DirType dt1 on dt1.dirtype_id=ed1.dirtype_id
							where ed1.Person_id=PS.Person_id and ed1.EvnClass_id=27 and ed1.EvnDirection_didDate is null and ed1.EvnDirection_disDate is null
							and dt1.dirtype_code in (1,4,5,6)
							and ED1.EvnStatus_id in (11,17,14,10,33)
							limit 1
						)as EvnDirection on true
						left join lateral(
							SELECT
								ED.EvnDirection_id, ED.EvnDirection_Num
							FROM
								v_EvnDirection_all ED
								inner join v_DirType DT on DT.DirType_id=ED.DirType_id
							WHERE
								ED.DirType_id != 24 and COALESCE(ED.DirType_id,1) not in (7, 18, 19, 20)
								and ED.EvnStatus_id in (11,17,14,10,33) and DT.DirType_Code in (1,4,5,6)
								and ps.Person_BirthDay + interval '1 year' > ED.EvnDirection_setDT
								and ED.Person_id=PS.Person_id
							ORDER BY
								ED.EvnDirection_setDT desc
							LIMIT 1
						)as EvnDirectionFiltr on true
						left join lateral(
							select
								EvnReanimatPeriod_setDate
							from v_EvnReanimatPeriod
							where Person_id=PS.Person_id and EvnReanimatPeriod_disDate is null
							limit 1
						)as Reanimat on true
						left join v_EvnSection AS es ON es.EvnSection_pid = open_evnps.EvnPS_id
						left join v_LpuSection AS ls ON ls.LpuSection_id = es.LpuSection_id AND ls.Lpu_id = es.Lpu_id
						left join v_LpuSectionProfile lsprof ON lsprof.LpuSectionProfile_id = ls.LpuSectionProfile_id
						left join v_EvnSection EPSLastES on EPSLastES.EvnSection_pid = open_evnps.EvnPS_id and EPSLastES.EvnSection_Index = EPSLastES.EvnSection_Count-1
						left join v_Diag Diag on Diag.Diag_id = EPSLastES.Diag_id
						left join v_Diag pDiag on pDiag.Diag_id = open_evnps.Diag_pid
						left join v_Diag plDiag on plDiag.Diag_id = open_evnpl.Diag_id
						{$fromOnlyOper}
						-- end from
					WHERE
						-- where
						(1=1)
						" . $where . "
						{$whereOnlyOper}
						and now() - interval '1 year' < ps.Person_BirthDay
						and (open_evnps.EvnPS_setDate is not null
							or (close_evnps.EvnPS_setDate is not null and close_evnps.EvnPl_setDT is null and close_evnps.LeaveType_Code = 1)
							or open_evnpl.EvnPL_setDate is not null
							or (cmpcallcard.CmpCallCard_prmDT is not null and cmpcallcard.SetDate is null)
							or EvnDirection.EvnDirection_Num is not null
						)
						-- end where
					ORDER BY
						-- order by
						ps.Person_Surname,
						ps.Person_Firname,
						ps.Person_Secname,
						ps.Person_Birthday
						-- end order by
				";
			}
		}
		//echo getDebugSQL(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
		//echo getDebugSQL($query, $params);exit();
        $result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
        $result_count = $this->db->query(getCountSQLPH($query), $params);

        if ( is_object($result_count) ) {
            $cnt_arr = $result_count->result('array');
            $count = $cnt_arr[0]['cnt'];
            unset($cnt_arr);
        }
        else {
            $count = 0;
        }

        if ( is_object($result) ) {
            $response = array();
            $response['data'] = $result->result('array');
            $response['totalCount'] = $count;
            return $response;
        }
        else {
            return false;
        }
    }

	/**
	 * Обновление флага высокого риска у новорожденного
	 */
	function updatePersonNewbornIsHighRisk($data) {
		$params = array('PersonNewBorn_id' => $data['PersonNewBorn_id']);

		$query = "
			select
    			PC.PersonNewBorn_id,
    			PC.ChildTermType_id,
    			PC.PersonNewBorn_IsAidsMother,
				ApgarRate.NewbornApgarRate_Values,
				BirthTraumaRod.status as statusrod,
				BirthTraumaPorok.status as statusporok,
				BirthTraumaPorag.status as statusporag,
				LastScreenGipotrofia.Value as screengipotrofia
			from
				v_PersonNewBorn PC
				left join v_BirthSpecStac BSS on BSS.BirthSpecStac_id = PC.BirthSpecStac_id
				left join lateral(
					select Screen.PregnancyScreen_id
					from v_PregnancyScreen Screen
					where Screen.PersonRegister_id = BSS.PersonRegister_id
					order by Screen.PregnancyScreen_setDT desc
					limit 1
				) LastScreen on true
                left join lateral(
					select PQ.PregnancyQuestion_ValuesStr as Value
					from v_PregnancyQuestion PQ
					inner join v_QuestionType QT on QT.QuestionType_id = PQ.QuestionType_id
					inner join v_GenConditFetus GF on GF.GenConditFetus_id = PQ.PregnancyQuestion_ValuesStr and GF.GenConditFetus_Code in (4,5)
					where PQ.PregnancyScreen_id = LastScreen.PregnancyScreen_id
					and QT.QuestionType_Code in (422)
                    limit 1
                ) LastScreenGipotrofia on true
                left join lateral(
					select
						AR.NewbornApgarRate_Values as NewbornApgarRate_Values
					from
						v_NewbornApgarRate AR
					where AR.PersonNewBorn_id = PC.PersonNewBorn_id and AR.NewbornApgarRate_Time=1
					order by AR.NewbornApgarRate_Time asc
					limit 1
				) ApgarRate on true
                left join lateral(
					select
						1 as status
					from
						v_PersonBirthTrauma PBT
						left join v_Diag D on D.Diag_id = PBT.Diag_id
					where (1 = 1)
						and PBT.PersonNewBorn_id = PC.PersonNewBorn_id
						and PBT.BirthTraumaType_id = '1'
				union all
					select
						1 as status
					from v_EvnDiag ED
					inner join v_PersonNewBorn PNB on pnb.EvnPS_id = ED.EvnDiag_rid
					left join v_Diag D on D.Diag_id = ED.Diag_id
					where PNB.PersonNewBorn_id = PC.PersonNewBorn_id
					and d.Diag_Code >='P10' and d.Diag_Code<='P15' and ED.EvnClass_id=33
				)as BirthTraumaRod on true
                left join lateral(
					select
						1 as status
					from
						v_PersonBirthTrauma PBT
						left join v_Diag D on D.Diag_id = PBT.Diag_id
					where (1 = 1)
						and PBT.PersonNewBorn_id = PC.PersonNewBorn_id
						and PBT.BirthTraumaType_id = '2'
				union all
					select
						1 as status
					from v_EvnDiag ED
					inner join v_PersonNewBorn PNB on pnb.EvnPS_id = ED.EvnDiag_rid
					left join v_Diag D on D.Diag_id = ED.Diag_id
					where PNB.PersonNewBorn_id = PC.PersonNewBorn_id
					and d.Diag_Code >='P15' and d.Diag_Code<='P99' and ED.EvnClass_id=33
                )as BirthTraumaPorok on true
                left join lateral(
					select
						1 as status
					from
						v_PersonBirthTrauma PBT
						left join v_Diag D on D.Diag_id = PBT.Diag_id
					where (1 = 1)
						and PBT.PersonNewBorn_id = PC.PersonNewBorn_id
						and PBT.BirthTraumaType_id = '3'
				union all
					select
						1 as status
					from v_EvnDiag ED
					inner join v_PersonNewBorn PNB on PNB.EvnPS_id = ED.EvnDiag_rid
					left join v_Diag D on D.Diag_id = ED.Diag_id
					where PNB.PersonNewBorn_id = PC.PersonNewBorn_id
					and d.Diag_Code ilike 'Q%' and ED.EvnClass_id=33
				)as BirthTraumaPorag on true
			where
                PC.PersonNewBorn_id = :PersonNewBorn_id
		";
		$info = $this->getFirstRowFromQuery($query, $params);
		if (!is_array($info)) {
			return $this->createError('','Ошибка при получении информации о новорожденном');
		}

		$IsHighRisk = false;
		if (!empty($info['ChildTermType_id']) && $info['ChildTermType_id'] == 2) {
			$IsHighRisk = true;
		}
		if (!empty($info['NewbornApgarRate_Values']) && $info['NewbornApgarRate_Values'] < 7) {
			$IsHighRisk = true;
		}
		if (!empty($info['statusrod']) && $info['statusrod'] == 1) {
			$IsHighRisk = true;
		}
		if (!empty($info['statusporok']) && $info['statusporok'] == 1) {
			$IsHighRisk = true;
		}
		if (!empty($info['statusporag']) && $info['statusporag'] == 1) {
			$IsHighRisk = true;
		}
		if (!empty($info['screengipotrofia']) && ($info['screengipotrofia'] == 4 || $info['screengipotrofia'] == 5)) {
			$IsHighRisk = true;
		}
		if (!empty($info['PersonNewBorn_IsAidsMother']) && $info['PersonNewBorn_IsAidsMother'] == 2) {
			$IsHighRisk = true;
		}

		$params = array(
			'PersonNewBorn_id' => $data['PersonNewBorn_id'],
			'PersonNewBorn_IsHighRisk' => $IsHighRisk ? 2 : 1,
		);
		$query = "
			update PersonNewBorn
			set PersonNewBorn_IsHighRisk = :PersonNewBorn_IsHighRisk
			where PersonNewBorn_id = :PersonNewBorn_id
		";
		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при обновлении флага высого риска');
		}
		return $resp;
	}
}