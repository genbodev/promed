<?php defined('BASEPATH') or die ('No direct script access allowed');
require_once(APPPATH.'models/PersonNewBorn_model.php');

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
			$where .= " and (ISNULL(PS.Person_Surname, '') + ' ' + ISNULL(PS.Person_Firname, '') + ' ' + ISNULL(PS.Person_Secname, '')) like :Person_FIO";
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
					$where .= " and mother_evnps.EvnPS_disDate is null and close_evnps_any.EvnPS_setDate is null and last_close_evnps.EvnPS_id is null and last_open_evnps.EvnPS_id is null and (ps.Person_deadDT is null and isnull(PntDeathSvid.PntDeathSvid_id,'0')='0')";
					break;
				case 2:
					$where .= " and ".
						"  ((mother_evnps.EvnPS_disDate is not null or close_evnps_any.EvnPS_setDate is not null) and last_open_evnps.EvnPS_id is null and (ps.Person_deadDT is null and isnull(PntDeathSvid.PntDeathSvid_id,'0')='0')".
						"	or last_open_evnps.EvnPS_id is not null and last_close_evnps.EvnPS_id is null and (ps.Person_deadDT is null and isnull(PntDeathSvid.PntDeathSvid_id,'0')='0')".
						"	or (ps.Person_deadDT is not null or isnull(PntDeathSvid.PntDeathSvid_id,'0')!='0')".
						"	or (last_open_evnps.EvnPS_id is not null and last_close_evnps.EvnPS_id is not null))";
					break;
				case 3:
					$where .= " and (mother_evnps.EvnPS_disDate is not null or close_evnps_any.EvnPS_setDate is not null) and last_open_evnps.EvnPS_id is null and (ps.Person_deadDT is null and isnull(PntDeathSvid.PntDeathSvid_id,'0')='0')";
					break;
				case 4:
					$where .= " and last_open_evnps.EvnPS_id is not null and last_close_evnps.EvnPS_id is null and (ps.Person_deadDT is null and isnull(PntDeathSvid.PntDeathSvid_id,'0')='0')";
					break;
				case 5:
					$where .= " and (ps.Person_deadDT is not null or COALESCE(PntDeathSvid.PntDeathSvid_id,'0')!='0')";
					$where .= " and (ps.Person_deadDT is not null or isnull(PntDeathSvid.PntDeathSvid_id,'0')!='0')";
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
					$where .= " and dateadd(DAY, -29, @getDT) < ps.Person_BirthDay";
					if(isset($data['PersonNewBorn_IsHighRisk'])&&$data['PersonNewBorn_IsHighRisk']==1){
						//$where .= " and pch.PersonNewBorn_IsHighRisk = 2";
						$where .= " and not [dbo].[GetHighRisk](pch.PersonNewBorn_id, ps.Person_id) = ''";
					}
				break;
				case 'OneAgePanel':
					$where .= " and dateadd(YEAR, -1, @getDT) < ps.Person_BirthDay";
					if(isset($data['PersonNewBorn_IsHighRisk'])&&$data['PersonNewBorn_IsHighRisk']==1){
						//$where .= " and pch.PersonNewBorn_IsHighRisk = 2";
						$where .= " and not [dbo].[GetHighRisk](pch.PersonNewBorn_id, ps.Person_id) = ''";
					}
				break;
				case 'AllAgePanel':
					$where .= " and dateadd(YEAR, -1, @getDT) >= ps.Person_BirthDay and dateadd(YEAR, -18, @getDT) < ps.Person_BirthDay";
					$panelyear=18;
				break;
				case 'MonitorCenterPanel':
					$where .= " and 1=1";
				break;
			}

		if ( !empty($data['Person_SurName']) ) {
			$where .= " and ISNULL(PS.Person_Surname, '') like :Person_Surname+'%'";
			$params['Person_Surname'] = $data['Person_SurName'];
		}

		if ( !empty($data['Person_SecName']) ) {
			$where .= " and ISNULL(PS.Person_SecName, '') like :Person_SecName+'%'";
			$params['Person_SecName'] = $data['Person_SecName'];
		}

		if ( !empty($data['Person_FirName']) ) {
			$where .= " and ISNULL(PS.Person_FirName, '') like :Person_FirName+'%'";
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
				and (case when convert(varchar,DATEDIFF(day, dateadd(MONTH, DATEDIFF(MONTH, ps.Person_BirthDay, @getDT), ps.Person_BirthDay), @getDT)) >= 0 then
					convert(varchar,DATEDIFF(MONTH, ps.Person_BirthDay, @getDT))
				else
					convert(varchar,DATEDIFF(MONTH, ps.Person_BirthDay, @getDT)-1)
				end)=:NumberList_idd
			";
			$params['NumberList_idd'] = $data['NumberList_idd']-1;
		}

		if ( !empty($data['NumberList_id']) && $data['NumberList_id'] != 400) {
			$where .= "
				and (case when convert(varchar,DATEDIFF(day, dateadd(MONTH, DATEDIFF(MONTH, ps.Person_BirthDay, @getDT), ps.Person_BirthDay), @getDT)) >= 0 then
					convert(varchar,DATEDIFF(MONTH, ps.Person_BirthDay, @getDT))
				else
					convert(varchar,DATEDIFF(MONTH, ps.Person_BirthDay, @getDT)-1)
				end)=:NumberList_id
			";
			$params['NumberList_id'] = $data['NumberList_id']-1;
		}

		if ( !empty($data['NumberList_aid']) && $data['NumberList_aid'] != 400) {
			$where .= "
				and (case when convert(varchar,DATEDIFF(day, dateadd(YEAR, DATEDIFF(YEAR, ps.Person_BirthDay, @getDT), ps.Person_BirthDay), @getDT)) >= 0 then
					convert(varchar,DATEDIFF(YEAR, ps.Person_BirthDay, @getDT))
				else
					convert(varchar,DATEDIFF(YEAR, ps.Person_BirthDay, @getDT)-1)
				end)=:NumberList_aid
			";
			$params['NumberList_aid'] = $data['NumberList_aid']-1;
		}

		//ограничение записей для оператора
		$userGroups = array();
		$descOnlyOper = 'declare @getDT datetime = dbo.tzGetDate();';
		$fromOnlyOper = '';
		$whereOnlyOper = '';
		if (!empty($_SESSION['groups']) && is_string($_SESSION['groups'])) {
			$userGroups = explode('|', $_SESSION['groups']);
		}
		if (count(array_intersect(array('OperBirth'), $userGroups)) > 0 &&
			count(array_intersect(array('OperRegBirth'), $userGroups)) <= 0){
			$fromOnlyOper = '
				outer apply (
						SELECT TOP 1
							hv0.lpu_id
						FROM dbo.v_HomeVisit hv0 with (nolock)
						WHERE hv0.Person_id=EPS.Person_id and hv0.HomeVisitStatus_id in (1,3,6)
						ORDER BY hv0.HomeVisit_setDT desc
				) hv1
			';
			$whereOnlyOper = ' and (PC.Lpu_id=@UserLpu_id or mLP.Lpu_id=@UserLpu_id or last_open_evnps.Lpu_id=@UserLpu_id or hv1.lpu_id=@UserLpu_id) ';
			$descOnlyOper = '
				declare @getDT date = dbo.tzGetDate(),
					@UserLpu_id varchar(2) =:UserLpu_id;
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
				if ( !empty($data['Lpu_lid']) ) {
					$where .= " and vizit.Lpu_id=:Lpu_lid";
					$params['Lpu_lid'] = $data['Lpu_lid'];
				}
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
											(case when pch.ChildTermType_id = 2 then 3 else '' end)
										else '' end
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
						ps.Person_id,
						case when pch.PersonNewBorn_IsHighRisk = 2 then 1 else 0 end as isHighRisk,
						ps.Person_SurName +' '+LEFT(isnull(ps.Person_FirName,''),1)+' '+LEFT(isnull(ps.Person_SecName,''),1) as Person_FIO,
						convert(varchar, ps.Person_BirthDay,104) AS Person_BirthDay ,
						ISNULL(W.PersonWeight_Weight, pch.PersonNewBorn_Weight) as PersonNewBorn_Weight,
						BirthSvid.BirthSvid_id,
						BirthSvid.BirthSvid_Num as BirthSvid,
						PntDeathSvid.PntDeathSvid_id,
						PntDeathSvid.PntDeathSvid_Num as DeathSvid,
						ps.Server_id AS Server_id,
						pch.Person_id AS Person_cid,
						mPS.Person_id as Person_mid,
						mLP.Lpu_Nick as LpuBirth,
						--lp.Lpu_Nick as LpuHosp,
						last_open_evnps.Lpu_Nick as LpuHosp,
						pch.PersonNewborn_id,
						cast(agp.NewbornApgarRate_Values as int) as NewbornApgarRate_Values,
						case
							when ps.Person_deadDT is not null or isnull(PntDeathSvid.PntDeathSvid_id,'0')!='0' then 'Умер'
							when ((mother_evnps.EvnPS_disDate is not null or close_evnps_any.EvnPS_setDate is not null) and last_open_evnps.EvnPS_id is null) then 'Выписан'
							when (last_open_evnps.EvnPS_id is not null and last_close_evnps.EvnPS_id is not null) then 'Переведен в другую МО'
							when (last_open_evnps.EvnPS_id is not null and last_close_evnps.EvnPS_id is null and (ps.Person_deadDT is null and isnull(PntDeathSvid.PntDeathSvid_id,'0')='0')) then 'В стационаре'
							else ''
						end as State,
						case when isnull(pch.PersonNewBorn_IsNeonatal,1)=2 then 'true' else '' end as PersonNewBorn_IsNeonatal,
						case when isnull(pch.PersonNewborn_IsAidsMother,1)=2 then 'true' else '' end as PersonNewborn_IsAidsMother,
						case when isnull(pch.PersonNewborn_IsRejection,1)=2 then 'true' else '' end as PersonNewborn_IsRejection,
						Deputy.Deputy_FIO,
						Deputy.Deputy_Addres,
						Deputy.Deputy_Phone,
						DP.DegreeOfPrematurity_Name,
						first_evnps.Diag,
						YN1.YesNo_Name as PersonNewborn_IsBCG,
						YN2.YesNo_Name as PersonNewBorn_IsHepatit,
						[dbo].[GetHighRisk](pch.PersonNewBorn_id, ps.Person_id) as listHighRisk
						-- end select
					from
						-- from
						v_PersonState ps with(nolock)
						outer apply (
							select top 1 pch0.*
							from v_PersonNewBorn pch0
							where pch0.Person_id = ps.Person_id
							order by pch0.PersonNewBorn_insDT asc
						)pch
						left join v_BirthSpecStac BSS with(nolock) on pch.birthspecstac_id = BSS.BirthSpecStac_id
						outer apply(
							select  top 1
								ISNULL(psd.Person_SurName,'') + ' ' + ISNULL(psd.Person_FirName,'') + ' ' + ISNULL(psd.Person_SecName,'') as Deputy_FIO,
								ISNULL(Adr.Address_Address,'') as Deputy_Addres,
								ISNULL(psd.Person_Phone,'') as Deputy_Phone
							from v_PersonDeputy PD with(nolock)
							inner join v_PersonState psd with(nolock) on psd.Person_id = PD.Person_pid and PD.Person_id = ps.Person_id
							left join v_Address Adr with(nolock) on Adr.Address_id = psd.PAddress_id
						)Deputy
						outer apply(
							select
								top 1 1 as Trauma
							from
								v_PersonBirthTrauma PC with (nolock)
							where
								PC.PersonNewBorn_id = pch.PersonNewBorn_id
						)tr
						outer apply (
							select top 1 a.NewbornApgarRate_Values
							from v_NewbornApgarRate a
							where a.PersonNewborn_id = pch.PersonNewborn_id and a.NewbornApgarRate_Values is not null
							order by NewbornApgarRate_Time desc, NewbornApgarRate_insDT desc
						)agp
						left join v_EvnSection mES with(nolock) on mES.EvnSection_id = BSS.EvnSection_id
						left join v_PersonRegister mPR with(nolock) on mPR.PersonRegister_id = BSS.PersonRegister_id
						left join v_PersonState mPS with(nolock) on mPS.Person_id = isnull(mPR.Person_id, mES.Person_id)
						left join v_Lpu_all mLP with(nolock) on mLP.Lpu_id = isnull(BSS.Lpu_id, mES.Lpu_id)
						left join v_EvnPS eps with(nolock) on eps.EvnPS_id = pch.EvnPS_id
						left join v_Lpu_all lp with(nolock) on lp.lpu_id=eps.Lpu_id
						outer apply(SELECT TOP 1 pds.PntDeathSvid_id,pds.PntDeathSvid_Num FROM dbo.v_PntDeathSvid pds WITH (NOLOCK) WHERE pds.Person_cid = pch.Person_id and (pds.PntDeathSvid_isBad is null or pds.PntDeathSvid_isBad=1) order by pds.PntDeathSvid_insDT desc) as PntDeathSvid
						outer apply(SELECT TOP 1 BirthSvid_id,BirthSvid_Num FROM dbo.v_BirthSvid bs WITH (NOLOCK) WHERE bs.Person_cid = pch.Person_id) AS BirthSvid
						outer apply(select top 1 case when pw.Okei_id=37 then pw.PersonWeight_Weight*1000 else pw.PersonWeight_Weight end as PersonWeight_Weight,pw.PersonWeight_id from v_personWeight pw with(nolock) where pw.person_id =pch.Person_id and pw.WeightMeasureType_id = 1) W
						outer apply (
							select
							top 1 EPS.EvnPS_setDate, /*EPL.EvnPl_setDT,*/HV.HomeVisit_setDT, patr.Lpu_Name, EPS.Lpu_id
							from v_EvnPS EPS with (nolock)
								left join v_HomeVisit hv with (nolock) on hv.Person_id=EPS.Person_id
								left join v_Lpu patr (nolock) on patr.Lpu_id = EPS.Lpu_id
								--left join v_EvnPL EPL with (nolock) on EPL.Person_id=PS.Person_id
								--left join v_Lpu patr (nolock) on patr.Lpu_id = EPL.Lpu_id
									and EPS.EvnPS_setDate <= hv.HomeVisit_setDT and hv.HomeVisit_setDT <= dateadd(DAY, 3, EPS.EvnPS_setDate)
							where
								EPS.EvnPS_disDate is not null and EPS.Person_id=PS.Person_id and dateadd(DAY, 7, EPS.EvnPS_setDate) >=getdate()
						)close_evnps
						outer apply (
							select
								top 1 EPS.EvnPS_setDate
							from v_EvnPS EPS with (nolock)
							where
								EPS.EvnPS_disDate is not null and EPS.Person_id=PS.Person_id
						)close_evnps_any
						left join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
						left join v_Lpu_all LpuAttach with (nolock) on LpuAttach.Lpu_id = PC.Lpu_id
						outer apply (
							select
							top 1 EPS.EvnPS_setDate, gospit.Lpu_Nick, EPS.Lpu_id, EPS.EvnPS_disDT, EPS.EvnPS_id
							from v_EvnPS EPS with (nolock)
								left join v_Lpu gospit (nolock) on gospit.Lpu_id = EPS.Lpu_id
							where
								EPS.Person_id=PS.Person_id and EPS.EvnPS_disDate is null
							order by EPS.EvnPS_setDate desc
						)last_open_evnps
						outer apply (
							select
								top 1 EPS.EvnPS_setDate, gospit.Lpu_Nick, EPS.Lpu_id, EPS.EvnPS_disDT, EPS.EvnPS_id
							from v_EvnPS EPS with (nolock)
								inner join v_EvnSection EPSLastES with (nolock) on EPSLastES.EvnSection_pid = EPS.EvnPS_id and EPSLastES.EvnSection_Index = EPSLastES.EvnSection_Count-1
								left join v_Lpu gospit (nolock) on gospit.Lpu_id = EPS.Lpu_id
								inner join v_LeaveType lt (nolock) on lt.LeaveType_id = EPSLastES.LeaveType_id and lt.LeaveType_Code=2
							where
								EPS.Person_id=PS.Person_id and EPS.EvnPS_disDate is not null
							order by EPS.EvnPS_setDate desc
						)last_close_evnps
						outer apply (
							select
								top 1 HV.HomeVisit_setDT, patr.Lpu_Name, hv.Lpu_id
							from v_HomeVisit hv with (nolock)
								left join v_Lpu patr (nolock) on patr.Lpu_id = hv.Lpu_id
							where hv.Person_id=PS.person_id and not hv.HomeVisitStatus_id in (2,5)
							order by HV.HomeVisit_setDT desc
						)vizit
						outer apply (
							select
							top 1 D.Diag_Code, (D.Diag_Code + '.' + D.Diag_Name) as Diag
							from v_EvnPS EPS with (nolock)
								inner join v_Diag D with(nolock) on D.Diag_id = EPS.Diag_id
							where
								EPS.Person_id=PS.Person_id and dateadd(DAY, 7, ps.Person_BirthDay) >=EPS.EvnPS_setDate
						)first_evnps
						left join v_DegreeOfPrematurity DP with(nolock) on DP.DegreeOfPrematurity_id = (case
										when COALESCE(W.PersonWeight_Weight, pch.PersonNewBorn_Weight) < 1000 then 1
										when COALESCE(W.PersonWeight_Weight, pch.PersonNewBorn_Weight) < 1500 then 2
										when COALESCE(W.PersonWeight_Weight, pch.PersonNewBorn_Weight) = 1500 then
											(case when pch.ChildTermType_id = 2 then 3 else '' end)
										else '' end
									)
						left join v_YesNo YN1 with (nolock) on YN1.YesNo_id = pch.PersonNewborn_IsBCG
						left join v_YesNo YN2 with (nolock) on YN2.YesNo_id = pch.PersonNewborn_IsHepatit
						outer apply (
							select
								top 1 EPS.EvnPS_disDate
							from v_EvnPS EPS with (nolock)
								inner join v_EvnSection ESM (nolock)
									on ESM.EvnSection_pid = EPS.EvnPS_id
								inner join v_BirthSpecStac BSSM (nolock)
									on BSSM.EvnSection_id = ESM.EvnSection_id
								inner join v_PersonNewBorn PNBM (nolock)
									on (PNBM.BirthSpecStac_id = BSSM.BirthSpecStac_id /*or PNBM.EvnPS_id = EPS.EvnPS_id*/)
							where
								PNBM.Person_id=PS.person_id and EPS.EvnPS_disDate is not null
							order by EPS.EvnPS_setDate desc
						)mother_evnps
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
					$where .= " and vizit.Lpu_id=:Lpu_pid";
					$params['Lpu_pid'] = $data['Lpu_pid'];
				}

				if (!empty($data['DirType_id'])){
					$where .= " and dirtype.Person_id is not null";
					$params['DirType_id'] = $data['DirType_id'];
					$from= "
						outer apply (
							SELECT
								TOP 1 ED.Person_id
							FROM
								v_EvnDirection_all ED with (nolock)
								inner join Evn Evn0 with (NOLOCK) on Evn0.Evn_id = ED.EvnDirection_id and Evn0.Evn_deleted = 1
							WHERE
								ED.DirType_id != 24 and isnull(ED.DirType_id,1) not in (7, 18, 19, 20)
								and isnull(ED.EvnStatus_id,11) in (11,17,14,10,33) and ED.DirType_id = :DirType_id
								and ED.Person_id = PS.Person_id and Evn0.Evn_setDT is not null
						) dirtype
					";
				}

				//диспансерное наблюдение
				if(isset($data['DispensaryObservation'])&&$data['DispensaryObservation']==1){
					$from .= "
						outer apply (
							SELECT
								top 1 dg1.Diag_Code+' '+dg1.diag_name as diag
							FROM
								v_PersonDisp PD with (nolock)
								left join v_Diag dg1 with (nolock) on PD.Diag_id = dg1.Diag_id
								WHERE
								PD.Person_id = ps.Person_id and
								coalesce(dg1.Diag_Code, '') not in('J30.0','C30.1') and
								coalesce(dg1.Diag_Code, '') not between 'A50.0' and 'A64' and
								PD.PersonDisp_endDate is null
							ORDER BY
								PD.PersonDisp_begDate desc
						)PersonDisp
					";
					$where .= " and PersonDisp.diag is not null ";
				}

				$query = "
					-- variables
					{$descOnlyOper}
					-- end variables

					select
						-- select
						pch.PersonNewBorn_id as PersonNewBorn_id,
						ps.Person_id,
						case when pch.PersonNewBorn_IsHighRisk = 2 then 1 else 0 end as isHighRisk,
						pch.Person_id AS Person_cid,
						ps.Server_id AS Server_id,
						TeenInspection.EvnPLDispTeenInspection_id as TeenInspection_id,
						ps.Person_SurName +' '+LEFT(COALESCE(ps.Person_FirName,''),1)+' '+LEFT(COALESCE(ps.Person_SecName,''),1) as Person_FIO,
						convert(varchar, ps.Person_BirthDay,104) AS Person_BirthDay ,
						LpuAttach.Lpu_Nick as LpuAttach_Nick,
						lp.Lpu_Nick as LpuHosp,
						lpulech.Lpu_Nick as LpuLech_Nick,
						TeenInspection.EvnPLDispTeenInspection_disDate as TeenInspection_disDate,
						[dbo].[GetPersonDisp](ps.Person_id) as PersonDisp,
						[dbo].[GetEvnDirections](ps.Person_id, {$panelyear}) as EvnDirectList,
						[dbo].[GetFirstDiagPL](ps.Person_id, {$panelyear}) as FirstDiag,
						[dbo].[GetHighRisk](pch.PersonNewBorn_id, ps.Person_id) as listHighRisk
						/*
						ps.Person_id,
						case when pch.PersonNewBorn_IsHighRisk = 2 then 1 else 0 end as isHighRisk,
						ps.Person_SurName +' '+LEFT(isnull(ps.Person_FirName,''),1)+' '+LEFT(isnull(ps.Person_SecName,''),1) as Person_FIO,
						convert(varchar, ps.Person_BirthDay,104) AS Person_BirthDay ,
						ISNULL(W.PersonWeight_Weight, pch.PersonNewBorn_Weight) as PersonNewBorn_Weight,
						BirthSvid.BirthSvid_id,
						BirthSvid.BirthSvid_Num as BirthSvid,
						PntDeathSvid.PntDeathSvid_id,
						PntDeathSvid.PntDeathSvid_Num as DeathSvid,
						ps.Server_id AS Server_id,
						pch.Person_id AS Person_cid,
						mPS.Person_id as Person_mid,
						mLP.Lpu_Nick as LpuBirth,
						lp.Lpu_Nick as LpuHosp,
						LpuAttach.Lpu_Nick as LpuAttach_Nick,
						lpulech.Lpu_Nick as LpuLech_Nick,
						[dbo].[GetPersonDisp](ps.Person_id) as PersonDisp,
						[dbo].[GetFirstDiagPL](ps.Person_id, {$panelyear}) as FirstDiag,
						TeenInspection.EvnPLDispTeenInspection_disDate as TeenInspection_disDate,
						TeenInspection.EvnPLDispTeenInspection_id as TeenInspection_id,
						[dbo].[GetEvnDirections](ps.Person_id, {$panelyear}) as EvnDirectList,
						[dbo].[GetHighRisk](pch.PersonNewBorn_id, ps.Person_id) as listHighRisk*/
						-- end select
					from
						-- from
						v_PersonState ps with(nolock)
						outer apply(
							select
								top 1 *
							from
								v_PersonNewBorn pch0 with (nolock)
							where
								pch0.Person_id = ps.Person_id
							order by PersonNewBorn_insDT desc
						)pch
						left join v_BirthSpecStac BSS with(nolock) on pch.birthspecstac_id = BSS.BirthSpecStac_id
						outer apply(
							select  top 1
								ISNULL(psd.Person_SurName,'') + ' ' + ISNULL(psd.Person_FirName,'') + ' ' + ISNULL(psd.Person_SecName,'') as Deputy_FIO,
								ISNULL(Adr.Address_Address,'') as Deputy_Addres,
								ISNULL(psd.Person_Phone,'') as Deputy_Phone
							from v_PersonDeputy PD with(nolock)
							inner join v_PersonState psd with(nolock) on psd.Person_id = PD.Person_pid and PD.Person_id = ps.Person_id
							left join v_Address Adr with(nolock) on Adr.Address_id = psd.PAddress_id
						)Deputy
						outer apply(
							select
								top 1 1 as Trauma
							from
								v_PersonBirthTrauma PC with (nolock)
							where (1 = 1)
								and PC.PersonNewBorn_id = pch.PersonNewBorn_id
						)tr
						outer apply (
							select top 1 a.NewbornApgarRate_Values 
							from v_NewbornApgarRate a 
							where a.PersonNewborn_id = pch.PersonNewborn_id
							order by NewbornApgarRate_Time desc, NewbornApgarRate_insDT desc
						)agp
						left join v_EvnSection mES with(nolock) on mES.EvnSection_id = BSS.EvnSection_id
						left join v_PersonRegister mPR with(nolock) on mPR.PersonRegister_id = BSS.PersonRegister_id
						left join v_PersonState mPS with(nolock) on mPS.Person_id = isnull(mPR.Person_id, mES.Person_id)
						left join v_Lpu_all mLP with(nolock) on mLP.Lpu_id = isnull(BSS.Lpu_id, mES.Lpu_id)
						left join v_EvnPS eps with(nolock) on eps.EvnPS_id = pch.EvnPS_id
						outer apply(SELECT TOP 1 pds.PntDeathSvid_id,pds.PntDeathSvid_Num FROM dbo.v_PntDeathSvid pds WITH (NOLOCK) WHERE pds.Person_cid = pch.Person_id  and (pds.PntDeathSvid_isBad is null or pds.PntDeathSvid_isBad=1) order by pds.PntDeathSvid_insDT desc) as PntDeathSvid
						outer apply(SELECT TOP 1 BirthSvid_id,BirthSvid_Num FROM dbo.v_BirthSvid bs WITH (NOLOCK) WHERE bs.Person_cid = pch.Person_id) AS BirthSvid
						outer apply(select top 1 case when pw.Okei_id=37 then pw.PersonWeight_Weight*1000 else pw.PersonWeight_Weight end as PersonWeight_Weight,pw.PersonWeight_id from v_personWeight pw with(nolock) where pw.person_id =pch.Person_id and pw.WeightMeasureType_id = 1) W
						left join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
						left join v_Lpu_all LpuAttach with (nolock) on LpuAttach.Lpu_id = PC.Lpu_id
						outer apply (
							select
								top 1 lpulech0.lpu_Nick
							from
								v_EvnPL epl0 with(nolock)
								inner join v_Lpu_all lpulech0 with(nolock) on lpulech0.lpu_id=epl0.Lpu_id
							where epl0.Person_id=PS.Person_id and epl0.EvnPL_IsFinish=1
							order by epl0.EvnPL_setDT desc
						)lpulech
						outer apply (
							select
							top 1 EPS.EvnPS_setDate, /*EPL.EvnPl_setDT,*/HV.HomeVisit_setDT, patr.Lpu_Name, EPS.Lpu_id
							from v_EvnPS EPS with (nolock)
								left join v_HomeVisit hv with (nolock) on hv.Person_id=EPS.Person_id
								left join v_Lpu patr (nolock) on patr.Lpu_id = EPS.Lpu_id
								--left join v_EvnPL EPL with (nolock) on EPL.Person_id=PS.Person_id
								--left join v_Lpu patr (nolock) on patr.Lpu_id = EPL.Lpu_id
									and EPS.EvnPS_setDate <= hv.HomeVisit_setDT and hv.HomeVisit_setDT <= dateadd(DAY, 3, EPS.EvnPS_setDate)
							where
								EPS.EvnPS_disDate is not null and EPS.Person_id=PS.Person_id and dateadd(DAY, 7, EPS.EvnPS_setDate) >=@getDT
						)close_evnps
						outer apply (
							SELECT TOP 1
								EPLDTI.EvnPLDispTeenInspection_id as EvnPLDispTeenInspection_id,
								convert(varchar(10), EPLDTI.EvnPLDispTeenInspection_disDate, 104) as EvnPLDispTeenInspection_disDate
							FROM
								v_EvnPLDispTeenInspection EPLDTI with (nolock)
							WHERE
								--EPLDTI.EvnPLDispTeenInspection_setDate = cast('2020-01-24' as datetime)
								--and EPLDTI.EvnPLDispTeenInspection_setDate >= cast('2020-01-01' as datetime)
								--and EPLDTI.EvnPLDispTeenInspection_setDate <= cast('2020-12-31' as datetime)
								isnull(EPLDTI.DispClass_id, 6) in ( '10' ,'12')
								and EPLDTI.Person_id=PS.Person_id
							ORDER BY
								EPLDTI.EvnPLDispTeenInspection_setDate
							DESC
						)TeenInspection
						outer apply (
							select
								top 1 EPS.EvnPS_setDate, EPS.EvnPS_id, EPS.Diag_pid, EPS.lpu_id
							from v_EvnPS EPS with (nolock)
							where
								EPS.EvnPS_disDate is null and EPS.Person_id=PS.Person_id
						)open_evnps
						left join v_Lpu_all lp with(nolock) on lp.lpu_id=open_evnps.Lpu_id
						outer apply (
							select
								top 1 HV.HomeVisit_setDT, patr.Lpu_Name, hv.Lpu_id
							from v_HomeVisit hv with (nolock)
								left join v_Lpu patr (nolock) on patr.Lpu_id = hv.Lpu_id
							where hv.Person_id=PS.person_id and not hv.HomeVisitStatus_id in (2,5)
							order by HV.HomeVisit_setDT desc
						)vizit
						outer apply (
							select
							top 1 EPS.EvnPS_setDate, gospit.Lpu_Nick, EPS.Lpu_id, EPS.EvnPS_disDT, EPS.EvnPS_id
							from v_EvnPS EPS with (nolock)
								left join v_Lpu gospit (nolock) on gospit.Lpu_id = EPS.Lpu_id
							where
								EPS.Person_id=PS.Person_id and EPS.EvnPS_disDate is null
							order by EPS.EvnPS_setDate desc
						)last_open_evnps
						outer apply (
							select
								top 1 EPS.EvnPS_setDate, gospit.Lpu_Nick, EPS.Lpu_id, EPS.EvnPS_disDT, EPS.EvnPS_id
							from v_EvnPS EPS with (nolock)
								inner join v_EvnSection EPSLastES with (nolock) on EPSLastES.EvnSection_pid = EPS.EvnPS_id and EPSLastES.EvnSection_Index = EPSLastES.EvnSection_Count-1
								left join v_Lpu gospit (nolock) on gospit.Lpu_id = EPS.Lpu_id
								inner join v_LeaveType lt (nolock) on lt.LeaveType_id = EPSLastES.LeaveType_id and lt.LeaveType_Code=2
							where
								EPS.Person_id=PS.Person_id and EPS.EvnPS_disDate is not null
							order by EPS.EvnPS_setDate desc
						)last_close_evnps
						outer apply (
							select
								top 1 EPS.EvnPS_disDate
							from v_EvnPS EPS with (nolock)
								left join v_EvnSection ESM (nolock)
									on ESM.EvnSection_pid = EPS.EvnPS_id
								left join v_BirthSpecStac BSSM (nolock)
									on BSSM.EvnSection_id = ESM.EvnSection_id
								left join v_PersonNewBorn PNBM (nolock)
									on (PNBM.BirthSpecStac_id = BSSM.BirthSpecStac_id or PNBM.EvnPS_id = EPS.EvnPS_id)
							where
								PNBM.Person_id=PS.person_id and EPS.EvnPS_disDate is not null
							order by EPS.EvnPS_setDate desc
						)mother_evnps
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
						ps.Person_id,
						EvnDirection.EvnDirection_id,
						case when pch.PersonNewBorn_IsHighRisk = 2 then 1 else 0 end as isHighRisk,
						ps.Server_id AS Server_id,
						pch.Person_id AS Person_cid,
						mPS.Person_id as Person_mid,
						cmpcallcard.CmpCallCard_id,
						open_evnps.Diag_pid,
						convert(varchar, open_evnps.EvnPS_setDate,104) as EvnPS_setDate,
						ps.Person_SurName,
						ps.Person_FirName,
						ps.Person_SecName,
						convert(varchar, ps.Person_BirthDay,104) AS Person_BirthDay,
						(case when convert(varchar,DATEDIFF(day, dateadd(MONTH, DATEDIFF(MONTH, ps.Person_BirthDay, @getDT), ps.Person_BirthDay), @getDT)) > 0 then
									convert(varchar,DATEDIFF(MONTH, ps.Person_BirthDay, @getDT))+' мес. '+
									convert(varchar,DATEDIFF(day, dateadd(MONTH, DATEDIFF(MONTH, ps.Person_BirthDay, @getDT), ps.Person_BirthDay), @getDT)) + ' дн.'
							when convert(varchar,DATEDIFF(day, dateadd(MONTH, DATEDIFF(MONTH, ps.Person_BirthDay, @getDT), ps.Person_BirthDay), @getDT)) = 0 then
									convert(varchar,DATEDIFF(MONTH, ps.Person_BirthDay, @getDT))+' мес. '
							else
									convert(varchar,DATEDIFF(MONTH, ps.Person_BirthDay, @getDT)-1)+' мес. '+
									convert(varchar,DATEDIFF(day, dateadd(MONTH, DATEDIFF(MONTH, ps.Person_BirthDay, @getDT)-1, ps.Person_BirthDay), @getDT)) + ' дн.'
						end)
						AS age,
						LpuAttach.Lpu_Nick as LpuAttach_Nick,
						lp.Lpu_Nick as LpuHosp,
						DATEDIFF(day, open_evnps.EvnPS_setDate, @getDT) as DayHosp,
						(CASE WHEN EXISTS
								(SELECT
									TOP 1 esN.Person_id
								FROM
									v_EvnSectionNarrowBed AS esN WITH (nolock)
									INNER JOIN v_LpuSection lsNB WITH (nolock) ON lsNB.LpuSection_id = esN.LpuSection_id
									INNER JOIN v_LpuSectionProfile lsProfNB WITH (nolock) ON lsProfNB.LpuSectionProfile_id = lsNB.LpuSectionProfile_id
								WHERE
									(EvnSectionNarrowBed_pid = es.EvnSection_id) AND lsProfNB.LpuSectionProfile_Code LIKE '[1-3]035')
									OR lsprof.LpuSectionProfile_Code LIKE '[1-3]035' OR Reanimat.EvnReanimatPeriod_setDate is not null
							THEN '!'
							ELSE ''
						END) AS PersReanim,
						(case when Diag.Diag_Code is not null then (Diag.Diag_Code + ' ' + Diag.Diag_Name)
							when pDiag.Diag_Code is not null then ('!' + pDiag.Diag_Code + ' ' + pDiag.Diag_Name)
							else '' end) EvnSectionDiag,
						open_evnpl.EvnPL_id,
						open_evnpl.EvnPL_NumCard as EvnPL_NumCard,
						DATEDIFF(day, open_evnpl.EvnPL_setDate, @getDT) as DayPL,
						(case when plDiag.Diag_Code is not null then (plDiag.Diag_Code + ' ' + plDiag.Diag_Name) else '' end) as plDiag,
						cmpcallcard.CmpCallCard_Numv,
						cmpcallcard.CmpCallCard_prmDT,
						cmpcallcard.CmpReasonNew_Name,
						(case when (close_evnps.EvnPS_setDate is not null and close_evnps.EvnPl_setDT is null and close_evnps.LeaveType_Code = 1) then '!' else '' end) as LpuPatr_Nick,
						--EvnDirection.EvnDirection_Num as EvnDirection
						[dbo].[GetEvnDirections](ps.Person_id, 3) as EvnDirectList,
						[dbo].[GetHighRisk](pch.PersonNewBorn_id, ps.Person_id) as listHighRisk
						-- end select
					FROM
						-- from
						v_PersonState ps with(nolock)
						outer apply(
							select
								top 1 *
							from
								v_PersonNewBorn pch0 with (nolock)
							where
								pch0.Person_id = ps.Person_id
							order by PersonNewBorn_insDT desc
						)pch
						left join v_BirthSpecStac BSS with(nolock) on pch.birthspecstac_id = BSS.BirthSpecStac_id
						left join v_EvnSection mES with(nolock) on mES.EvnSection_id = BSS.EvnSection_id
						left join v_Lpu_all mLP with(nolock) on mLP.Lpu_id = isnull(BSS.Lpu_id, mES.Lpu_id)
						left join v_PersonRegister mPR with(nolock) on mPR.PersonRegister_id = BSS.PersonRegister_id
						left join v_PersonState mPS with(nolock) on mPS.Person_id = isnull(mPR.Person_id, mES.Person_id)
						left join v_EvnPS eps with(nolock) on eps.EvnPS_id = pch.EvnPS_id
						left join v_PersonCard PC with (nolock) on PC.Person_id = PS.Person_id and PC.LpuAttachType_id = 1
						left join v_Lpu_all LpuAttach with (nolock) on LpuAttach.Lpu_id = PC.Lpu_id
						outer apply (
							select
								top 1 EPS.EvnPS_setDate, EPS.EvnPS_id, EPS.Diag_pid, EPS.lpu_id
							from v_EvnPS EPS with (nolock)
							where
								EPS.EvnPS_disDate is null and EPS.Person_id=PS.Person_id
						)open_evnps
						left join v_Lpu_all lp with(nolock) on lp.lpu_id=open_evnps.Lpu_id
						outer apply (
							select
							top 1 EPS.EvnPS_setDate, EPL.EvnPl_setDT,/*HV.HomeVisit_setDT,*/ patr.Lpu_Name, LT.LeaveType_Code
							from v_EvnPS EPS with (nolock)
								--left join v_HomeVisit hv with (nolock) on hv.Person_id=EPS.Person_id
								--left join v_Lpu patr (nolock) on patr.Lpu_id = EPS.Lpu_id
								left join v_EvnPL EPL with (nolock) on EPL.Person_id=PS.Person_id and EPS.EvnPS_setDate <= EPL.EvnPl_setDT
								left join v_Lpu patr (nolock) on patr.Lpu_id = EPL.Lpu_id
								left join v_LeaveType LT (nolock) on LT.LeaveType_id = EPS.LeaveType_id
									--and EPS.EvnPS_setDate <= hv.HomeVisit_setDT and hv.HomeVisit_setDT <= dateadd(DAY, 3, EPS.EvnPS_setDate)
							where
								EPS.EvnPS_disDate is not null and EPS.Person_id=PS.Person_id
								and dateadd(DAY, 7, EPS.EvnPS_disDate) >=@getDT
								and @getDT >= dateadd(DAY, 3, EPS.EvnPS_disDate)
						)close_evnps
						outer apply (
							select
								top 1 EPL.EvnPL_setDate, EPL.EvnPL_id, EPL.EvnPL_NumCard, EPL.Diag_id
							from v_EvnPL EPL with (nolock)
							where
								dateadd(DAY, 7, EPL.EvnPL_setDate) <= @getDT and EPL.EvnPL_IsFinish=1 and EPL.Person_id=PS.Person_id
						)open_evnpl
						outer apply (
							select
								top 1 convert(varchar, cmpclose.AcceptTime,104) as CmpCallCard_prmDT, (case when EPS.EvnPS_setDate is null then EPL.EvnPL_setDate else EPS.EvnPS_setDate end ) as SetDate, cmp.CmpCallCard_Numv, reason.CmpReason_Name as CmpReasonNew_Name,
								cmp.CmpCallCard_id
							from dbo.v_CmpCallCard cmp with (nolock)
								inner join dbo.v_CmpCloseCard cmpclose with (nolock) on cmpclose.CmpCallCard_id=cmp.CmpCallCard_id
								--left join v_EvnPS EPS with (nolock) on EPS.Person_id=cmp.Person_id and cmp.CmpCallCard_prmDT <= EPS.EvnPS_setDate+EPS.EvnPS_setTime and EPS.EvnPS_setDate+EPS.EvnPS_setTime <= dateadd(DAY, 7, cmp.CmpCallCard_prmDT)
								--left join v_EvnPL EPL with (nolock) on EPL.Person_id=cmp.Person_id and cmp.CmpCallCard_prmDT <= EPL.EvnPL_setDate+EPL.EvnPL_setTime and EPL.EvnPL_setDate+EPL.EvnPL_setTime <= dateadd(DAY, 7, cmp.CmpCallCard_prmDT)
								left join v_EvnPS EPS with (nolock) on EPS.Person_id=cmp.Person_id and cmpclose.AcceptTime <= EPS.EvnPS_setDate+EPS.EvnPS_setTime and EPS.EvnPS_setDate+EPS.EvnPS_setTime <= dateadd(DAY, 7, cmpclose.AcceptTime)
								left join v_EvnPL EPL with (nolock) on EPL.Person_id=cmp.Person_id and cmpclose.AcceptTime <= EPL.EvnPL_setDate+EPL.EvnPL_setTime and EPL.EvnPL_setDate+EPL.EvnPL_setTime <= dateadd(DAY, 7, cmpclose.AcceptTime)
								left join v_CmpReason reason with (nolock) on reason.CmpReason_id=cmp.CmpReason_id
							where
								cmp.Person_id=PS.Person_id
								and @getDT <= dateadd(DAY, 7, cmpclose.AcceptTime)
								and @getDT >= dateadd(DAY, 1, cmpclose.AcceptTime)
							order by cmpclose.AcceptTime desc
						)cmpcallcard
						outer apply (
							select
								top 1 ed1.EvnDirection_Num, ed1.EvnDirection_id, ed1.DirType_id
							from v_EvnDirection ed1 with(nolock)
								inner join v_DirType dt1 with(nolock) on dt1.dirtype_id=ed1.dirtype_id
							where ed1.Person_id=PS.Person_id and ed1.EvnClass_id=27 and ed1.EvnDirection_didDate is null and ed1.EvnDirection_disDate is null
							and dt1.dirtype_code in (1,4,5,6)
							and ED1.EvnStatus_id in (11,17,14,10,33)
						)EvnDirection
						outer apply (
							SELECT
								top 1 ED.EvnDirection_id, ED.EvnDirection_Num
							FROM
								v_EvnDirection_all ED with (nolock)
								inner join v_DirType DT with (nolock) on DT.DirType_id=ED.DirType_id
							WHERE
								ED.DirType_id != 24 and isnull(ED.DirType_id,1) not in (7, 18, 19, 20)
								and ED.EvnStatus_id in (11,17,14,10,33) and DT.DirType_Code in (1,4,5,6)
								and dateadd(YEAR, 1, ps.Person_BirthDay) > ED.EvnDirection_setDT
								and ED.Person_id=PS.Person_id
							ORDER BY
								ED.EvnDirection_setDT desc
						)EvnDirectionFiltr
						outer apply (
							select
								top 1 EvnReanimatPeriod_setDate
							from v_EvnReanimatPeriod with(nolock)
							where Person_id=PS.Person_id and EvnReanimatPeriod_disDate is null
						)Reanimat
						left join v_EvnSection AS es WITH (nolock) ON es.EvnSection_pid = open_evnps.EvnPS_id
						left join v_LpuSection AS ls WITH (nolock) ON ls.LpuSection_id = es.LpuSection_id AND ls.Lpu_id = es.Lpu_id
						left join v_LpuSectionProfile lsprof WITH (nolock) ON lsprof.LpuSectionProfile_id = ls.LpuSectionProfile_id
						left join v_EvnSection EPSLastES with (nolock) on EPSLastES.EvnSection_pid = open_evnps.EvnPS_id and EPSLastES.EvnSection_Index = EPSLastES.EvnSection_Count-1
						left join v_Diag Diag with (nolock) on Diag.Diag_id = EPSLastES.Diag_id
						left join v_Diag pDiag with (nolock) on pDiag.Diag_id = open_evnps.Diag_pid
						left join v_Diag plDiag with (nolock) on plDiag.Diag_id = open_evnpl.Diag_id
						{$fromOnlyOper}
						-- end from
					WHERE
						-- where
						(1=1)
						" . $where . "
						{$whereOnlyOper}
						and dateadd(YEAR, -1, @getDT) < ps.Person_BirthDay
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
			declare @PersonNewBorn_id bigint = :PersonNewBorn_id;
			select top 1
				PC.PersonNewBorn_id,
				PC.ChildTermType_id,
				PC.PersonNewBorn_IsAidsMother,
				ApgarRate.NewbornApgarRate_Values,
				BirthTraumaRod.status as statusrod,
				BirthTraumaPorok.status as statusporok,
				BirthTraumaPorag.status as statusporag,
				LastScreenGipotrofia.Value as screengipotrofia
			from
				v_PersonNewBorn PC with (nolock)
				left join v_PersonState ps with(nolock) on PC.person_id=ps.person_id
				left join v_BirthSpecStac BSS with(nolock) on BSS.BirthSpecStac_id = PC.BirthSpecStac_id
				outer apply (
					select top 1 Screen.*
					from v_PregnancyScreen Screen with(nolock)
					where Screen.PersonRegister_id = BSS.PersonRegister_id
					order by Screen.PregnancyScreen_setDT desc
				) LastScreen
				outer apply(
					select top 1 PQ.PregnancyQuestion_ValuesStr as Value
					from v_PregnancyQuestion PQ with(nolock)
					inner join v_QuestionType QT with(nolock) on QT.QuestionType_id = PQ.QuestionType_id
					inner join v_GenConditFetus GF with(nolock) on GF.GenConditFetus_id = PQ.PregnancyQuestion_ValuesStr and GF.GenConditFetus_Code in (4,5)
					where PQ.PregnancyScreen_id = LastScreen.PregnancyScreen_id
					and QT.QuestionType_Code in (422)
				) LastScreenGipotrofia
				outer apply(
					select
						top 1 cast(AR.NewbornApgarRate_Values as int) as NewbornApgarRate_Values
					from
						v_NewbornApgarRate AR with (nolock)
					where (1 = 1)
						and AR.PersonNewBorn_id = PC.PersonNewBorn_id and AR.NewbornApgarRate_Time=1
					order by AR.NewbornApgarRate_Time asc
				) ApgarRate
				outer apply(
					select
						1 as status
					from
						v_PersonBirthTrauma PBT with (nolock)
						left join v_Diag D with(nolock) on D.Diag_id = PBT.Diag_id
					where (1 = 1)
						and PBT.PersonNewBorn_id = PC.PersonNewBorn_id
						and PBT.BirthTraumaType_id = '1'
				union all
					select
						1 as status
					from v_EvnDiag ED with(nolock)
					inner join v_PersonNewBorn PNB with(nolock) on pnb.EvnPS_id = ED.EvnDiag_rid
					left join v_Diag D with(nolock) on D.Diag_id = ED.Diag_id
					where PNB.PersonNewBorn_id = PC.PersonNewBorn_id
					and d.Diag_Code >='P10' and d.Diag_Code<='P15' and ED.EvnClass_id=33
				) BirthTraumaRod
				outer apply(
					select
						1 as status
					from
						v_PersonBirthTrauma PBT with (nolock)
						left join v_Diag D with(nolock) on D.Diag_id = PBT.Diag_id
					where (1 = 1)
						and PBT.PersonNewBorn_id = PC.PersonNewBorn_id
						and PBT.BirthTraumaType_id = '2'
				union all
					select
						1 as status
					from v_EvnDiag ED with(nolock)
					inner join v_PersonNewBorn PNB with(nolock) on pnb.EvnPS_id = ED.EvnDiag_rid
					left join v_Diag D with(nolock) on D.Diag_id = ED.Diag_id
					where PNB.PersonNewBorn_id = PC.PersonNewBorn_id
					and d.Diag_Code >='P15' and d.Diag_Code<='P99' and ED.EvnClass_id=33
				) BirthTraumaPorok
				outer apply(
					select
						1 as status
					from
						v_PersonBirthTrauma PBT with (nolock)
						left join v_Diag D with(nolock) on D.Diag_id = PBT.Diag_id
					where (1 = 1)
						and PBT.PersonNewBorn_id = PC.PersonNewBorn_id
						and PBT.BirthTraumaType_id = '3'
				union all
					select
						1 as status
					from v_EvnDiag ED with(nolock)
					inner join v_PersonNewBorn PNB with(nolock) on pnb.EvnPS_id = ED.EvnDiag_rid
					left join v_Diag D with(nolock) on D.Diag_id = ED.Diag_id
					where PNB.PersonNewBorn_id = PC.PersonNewBorn_id
					and d.Diag_Code like 'Q%' and ED.EvnClass_id=33
				) BirthTraumaPorag
			where
				PC.PersonNewBorn_id = @PersonNewBorn_id
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
			declare @Error_Code bigint = null
			declare @Error_Message varchar(4000) = ''
			set nocount on
			begin try
				update PersonNewBorn with(rowlock)
				set PersonNewBorn_IsHighRisk = :PersonNewBorn_IsHighRisk
				where PersonNewBorn_id = :PersonNewBorn_id
			end try
			begin catch
				set @Error_Code = error_number()
				set @Error_Message = error_message()
			end catch
			set nocount off
			select @Error_Code as Error_Code, @Error_Message as Error_Msg
		";
		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при обновлении флага высого риска');
		}
		return $resp;
	}

	/**
	 *
	 * @param type $data
	 * @return type
	 */
	function loadPersonNewBornData($data) {

		if (empty($data['EvnPS_id'])){
			$query = "
					select top 1
						PC.PersonNewBorn_id,
						PC.ChildTermType_id,
						PC.FeedingType_id,
						PC.NewBornWardType_id,
						ISNULL(PC.PersonNewBorn_BCGNum, '') as PersonNewBorn_BCGNum,
						ISNULL(PC.PersonNewBorn_BCGSer, '') as PersonNewBorn_BCGSer,
						convert(varchar(10),PC.PersonNewBorn_BCGDate,104) as PersonNewBorn_BCGDate,
						ISNULL(PC.PersonNewBorn_HepatitNum, '') as PersonNewBorn_HepatitNum,
						ISNULL(PC.PersonNewBorn_HepatitSer, '') as PersonNewBorn_HepatitSer,
						convert(varchar(10),PC.PersonNewBorn_HepatitDate,104) as PersonNewBorn_HepatitDate,
						PC.PersonNewBorn_IsAidsMother,
						PC.PersonNewBorn_IsHepatit,
						PC.PersonNewBorn_IsBCG,
						IsBreath.YesNo_Code as PersonNewBorn_IsBreath,
						IsHeart.YesNo_Code as PersonNewBorn_IsHeart,
						IsPulsation.YesNo_Code as PersonNewBorn_IsPulsation,
						IsMuscle.YesNo_Code as PersonNewBorn_IsMuscle,
						PS.PersonEvn_id,
						PC.Person_id,
						PC.Server_id,
						PC.EvnPS_id,
						PC.EvnSection_mid,
						PC.PersonNewBorn_IsBleeding,
						PC.PersonNewBorn_IsNeonatal,
						PC.PersonNewBorn_IsAudio,
						PersonNewBorn_CountChild,
						ChildPositionType_id,
						PersonNewBorn_IsRejection,
						PC.PersonNewBorn_Head,
						PC.PersonNewBorn_Height as PersonNewBorn_Height, PC.PersonNewBorn_Weight as PersonNewBorn_Weight,
						PC.PersonNewBorn_Breast,
						convert(varchar(10),PS.Person_BirthDay,104) as Person_BirthDay,
						PC.PersonNewborn_BloodBili,
						PC.PersonNewborn_BloodHemoglo,
						PC.PersonNewborn_BloodEryth,
						PC.PersonNewborn_BloodHemato,
						PC.PersonNewBorn_pid,
						PC.RefuseType_pid,
						PC.RefuseType_bid,
						PC.RefuseType_gid,
						PC.RefuseType_aid
					from
						v_PersonNewBorn PC with (nolock)
						left join v_PersonState ps with(nolock) on PC.person_id=ps.person_id
						outer apply(select top 1 ph.PersonHeight_Height,ph.PersonHeight_id from v_personHeight ph with(nolock) where ph.person_id =PC.Person_id and ph.HeightMeasureType_id = 1) H
						outer apply(select top 1 case when pw.Okei_id=37 then pw.PersonWeight_Weight*1000 else pw.PersonWeight_Weight end as PersonWeight_Weight,pw.PersonWeight_id from v_personWeight pw with(nolock) where pw.person_id =PC.Person_id and pw.WeightMeasureType_id = 1) W
						left join v_YesNo IsBreath with(nolock) on IsBreath.YesNo_id = PC.PersonNewBorn_IsBreath
						left join v_YesNo IsHeart with(nolock) on IsHeart.YesNo_id = PC.PersonNewBorn_IsHeart
						left join v_YesNo IsPulsation with(nolock) on IsPulsation.YesNo_id = PC.PersonNewBorn_IsPulsation
						left join v_YesNo IsMuscle with(nolock) on IsMuscle.YesNo_id = PC.PersonNewBorn_IsMuscle
					where (1 = 1)
						and PC.Person_id = :Person_id and PC.EvnPS_id is null
						order by PC.PersonNewborn_insDT desc
				";

			$result = $this->db->query($query, array(
				'Person_id' => $data['Person_id']
			));

			$res = $result->result('array');
			return $res;
		}else {

			$query_ = "
			select top 1
				PC.PersonNewBorn_id,
				PC.ChildTermType_id,
				PC.FeedingType_id,
				PC.NewBornWardType_id,
				ISNULL(PC.PersonNewBorn_BCGNum, '') as PersonNewBorn_BCGNum,
				ISNULL(PC.PersonNewBorn_BCGSer, '') as PersonNewBorn_BCGSer,
				convert(varchar(10),PC.PersonNewBorn_BCGDate,104) as PersonNewBorn_BCGDate,
				ISNULL(PC.PersonNewBorn_HepatitNum, '') as PersonNewBorn_HepatitNum,
				ISNULL(PC.PersonNewBorn_HepatitSer, '') as PersonNewBorn_HepatitSer,
				convert(varchar(10),PC.PersonNewBorn_HepatitDate,104) as PersonNewBorn_HepatitDate,
				PC.PersonNewBorn_IsAidsMother,
				PC.PersonNewBorn_IsHepatit,
				PC.PersonNewBorn_IsBCG,
				IsBreath.YesNo_Code as PersonNewBorn_IsBreath,
				IsHeart.YesNo_Code as PersonNewBorn_IsHeart,
				IsPulsation.YesNo_Code as PersonNewBorn_IsPulsation,
				IsMuscle.YesNo_Code as PersonNewBorn_IsMuscle,
				PS.PersonEvn_id,
				PC.Person_id,
				PC.Server_id,
				PC.EvnPS_id,
				PC.EvnSection_mid,
				PC.PersonNewBorn_IsBleeding,
				PC.PersonNewBorn_IsNeonatal,
				PC.PersonNewBorn_IsAudio,
				PC.PersonNewBorn_CountChild,
				PC.ChildPositionType_id,
				PC.PersonNewBorn_IsRejection,
				PC.PersonNewBorn_Head,
				PC.PersonNewBorn_Height as PersonNewBorn_Height, PC.PersonNewBorn_Weight as PersonNewBorn_Weight,
				PC.PersonNewBorn_Breast,
				convert(varchar(10),PS.Person_BirthDay,104) as Person_BirthDay,
				PC.PersonNewborn_BloodBili,
				PC.PersonNewborn_BloodHemoglo,
				PC.PersonNewborn_BloodEryth,
				PC.PersonNewborn_BloodHemato,
				PC.RefuseType_pid,
				PC.RefuseType_bid,
				PC.RefuseType_gid,
				PC.RefuseType_aid,

				(case when pnlast.ChildTermType_id is null then 2 else 1 end) as ChildTermType_id_IsEdit,
				(case when pnlast.FeedingType_id is null then 2 else 1 end) as FeedingType_id_IsEdit,
				(case when pnlast.NewBornWardType_id is null then 2 else 1 end) as NewBornWardType_id_IsEdit,
				(case when pnlast.PersonNewBorn_BCGNum is null then 2 else 1 end) as PersonNewBorn_BCGNum_IsEdit,
				(case when pnlast.PersonNewBorn_BCGSer is null then 2 else 1 end) as PersonNewBorn_BCGSer_IsEdit,
				(case when pnlast.PersonNewBorn_BCGDate is null then 2 else 1 end) as PersonNewBorn_BCGDate_IsEdit,
				(case when pnlast.PersonNewBorn_HepatitNum is null then 2 else 1 end) as PersonNewBorn_HepatitNum_IsEdit,
				(case when pnlast.PersonNewBorn_HepatitSer is null then 2 else 1 end) as PersonNewBorn_HepatitSer_IsEdit,
				(case when pnlast.PersonNewBorn_HepatitDate is null then 2 else 1 end) as PersonNewBorn_HepatitDate_IsEdit,
				(case when pnlast.PersonNewBorn_IsAidsMother is null then 2 else 1 end) as PersonNewBorn_IsAidsMother_IsEdit,
				(case when pnlast.PersonNewBorn_IsHepatit is null then 2 else 1 end) as PersonNewBorn_IsHepatit_IsEdit,
				(case when pnlast.PersonNewBorn_IsBCG is null then 2 else 1 end) as PersonNewBorn_IsBCG_IsEdit,
				(case when pnlast.PersonNewBorn_IsBreath is null then 2 else 1 end) as PersonNewBorn_IsBreath_IsEdit,
				(case when pnlast.PersonNewBorn_IsHeart is null then 2 else 1 end) as PersonNewBorn_IsHeart_IsEdit,
				(case when pnlast.PersonNewBorn_IsPulsation is null then 2 else 1 end) as PersonNewBorn_IsPulsation_IsEdit,
				(case when pnlast.PersonNewBorn_IsMuscle is null then 2 else 1 end) as PersonNewBorn_IsMuscle_IsEdit,
				(case when pnlast.PersonNewBorn_IsBleeding is null then 2 else 1 end) as PersonNewBorn_IsBleeding_IsEdit,
				(case when pnlast.PersonNewBorn_IsNeonatal is null then 2 else 1 end) as PersonNewBorn_IsNeonatal_IsEdit,
				(case when pnlast.PersonNewBorn_IsAudio is null then 2 else 1 end) as PersonNewBorn_IsAudio_IsEdit,
				(case when pnlast.PersonNewBorn_CountChild is null then 2 else 1 end) as PersonNewBorn_CountChild_IsEdit,
				(case when pnlast.ChildPositionType_id is null then 2 else 1 end) as ChildPositionType_id_IsEdit,
				(case when pnlast.PersonNewBorn_IsRejection is null then 2 else 1 end) as PersonNewBorn_IsRejection_IsEdit,
				(case when pnlast.PersonNewBorn_Head is null then 2 else 1 end) as PersonNewBorn_Head_IsEdit,
				(case when pnlast.PersonNewBorn_Height is null then 2 else 1 end) as PersonNewBorn_Height_IsEdit,
				(case when pnlast.PersonNewBorn_Weight is null then 2 else 1 end) as PersonNewBorn_Weight_IsEdit,
				(case when pnlast.PersonNewBorn_Breast is null then 2 else 1 end) as PersonNewBorn_Breast_IsEdit,
				(case when pnlast.PersonNewborn_BloodBili is null then 2 else 1 end) as PersonNewborn_BloodBili_IsEdit,
				(case when pnlast.PersonNewborn_BloodHemoglo is null then 2 else 1 end) as PersonNewborn_BloodHemoglo_IsEdit,
				(case when pnlast.PersonNewborn_BloodEryth is null then 2 else 1 end) as PersonNewborn_BloodEryth_IsEdit,
				(case when pnlast.PersonNewborn_BloodHemato is null then 2 else 1 end) as PersonNewborn_BloodHemato_IsEdit,

				(case when pnlast.RefuseType_pid is null then 2 else 1 end) as RefuseType_pid_IsEdit,
				(case when pnlast.RefuseType_bid is null then 2 else 1 end) as RefuseType_bid_IsEdit,
				(case when pnlast.RefuseType_gid is null then 2 else 1 end) as RefuseType_gid_IsEdit,
				(case when pnlast.RefuseType_aid is null then 2 else 1 end) as RefuseType_aid_IsEdit,

				pnlast.PersonNewborn_Id as PersonNewborn_Id_Last
			from
				v_PersonNewBorn PC with (nolock)
				left join v_PersonState ps with(nolock) on PC.person_id=ps.person_id
				outer apply(select top 1 ph.PersonHeight_Height,ph.PersonHeight_id from v_personHeight ph with(nolock) where ph.person_id =PC.Person_id and ph.HeightMeasureType_id = 1) H
				outer apply(select top 1 case when pw.Okei_id=37 then pw.PersonWeight_Weight*1000 else pw.PersonWeight_Weight end as PersonWeight_Weight,pw.PersonWeight_id from v_personWeight pw with(nolock) where pw.person_id =PC.Person_id and pw.WeightMeasureType_id = 1) W
				left join v_YesNo IsBreath with(nolock) on IsBreath.YesNo_id = PC.PersonNewBorn_IsBreath
				left join v_YesNo IsHeart with(nolock) on IsHeart.YesNo_id = PC.PersonNewBorn_IsHeart
				left join v_YesNo IsPulsation with(nolock) on IsPulsation.YesNo_id = PC.PersonNewBorn_IsPulsation
				left join v_YesNo IsMuscle with(nolock) on IsMuscle.YesNo_id = PC.PersonNewBorn_IsMuscle
				outer apply(select top 1
					pnl.PersonNewborn_Id,
					pnl.ChildTermType_id,
					pnl.FeedingType_id,
					pnl.NewBornWardType_id,
					pnl.PersonNewBorn_BCGNum as PersonNewBorn_BCGNum,
					pnl.PersonNewBorn_BCGSer as PersonNewBorn_BCGSer,
					convert(varchar(10),pnl.PersonNewBorn_BCGDate,104) as PersonNewBorn_BCGDate,
					pnl.PersonNewBorn_HepatitNum as PersonNewBorn_HepatitNum,
					pnl.PersonNewBorn_HepatitSer as PersonNewBorn_HepatitSer,
					convert(varchar(10),pnl.PersonNewBorn_HepatitDate,104) as PersonNewBorn_HepatitDate,
					pnl.PersonNewBorn_IsAidsMother,
					pnl.PersonNewBorn_IsHepatit,
					pnl.PersonNewBorn_IsBCG,
					pnl.PersonNewBorn_IsBreath,
					pnl.PersonNewBorn_IsHeart,
					pnl.PersonNewBorn_IsPulsation,
					pnl.PersonNewBorn_IsMuscle,
					pnl.PersonNewBorn_IsBleeding,
					pnl.PersonNewBorn_IsNeonatal,
					pnl.PersonNewBorn_IsAudio,
					pnl.PersonNewBorn_CountChild,
					pnl.ChildPositionType_id,
					pnl.PersonNewBorn_IsRejection,
					pnl.PersonNewBorn_Head,
					pnl.PersonNewBorn_Height as PersonNewBorn_Height, pnl.PersonNewBorn_Weight as PersonNewBorn_Weight,
					pnl.PersonNewBorn_Breast,
					pnl.PersonNewborn_BloodBili,
					pnl.PersonNewborn_BloodHemoglo,
					pnl.PersonNewborn_BloodEryth,
					pnl.PersonNewborn_BloodHemato,
					PC.RefuseType_pid,
					PC.RefuseType_bid,
					PC.RefuseType_gid,
					PC.RefuseType_aid
				from PersonNewBorn pnl with(nolock) where pnl.person_id =PC.Person_id and pnl.PersonNewborn_id=PC.PersonNewborn_pid order by pnl.PersonNewborn_insDT desc) pnlast
			where (1 = 1)
				and PC.Person_id = :Person_id
				and PC.EvnPS_id=:EvnPS_id
		";

			$result = $this->db->query($query_, array(
				'Person_id' => $data['Person_id'],
				'EvnPS_id' => $data['EvnPS_id']
			));

			if (is_object($result)) {

				$res = $result->result('array');

				if ('ufa' == $this->regionNick && count($res) == 0) {
					$query = "
					select top 1
						PC.PersonNewBorn_id,
						PC.ChildTermType_id,
						PC.FeedingType_id,
						PC.NewBornWardType_id,
						ISNULL(PC.PersonNewBorn_BCGNum, '') as PersonNewBorn_BCGNum,
						ISNULL(PC.PersonNewBorn_BCGSer, '') as PersonNewBorn_BCGSer,
						convert(varchar(10),PC.PersonNewBorn_BCGDate,104) as PersonNewBorn_BCGDate,
						ISNULL(PC.PersonNewBorn_HepatitNum, '') as PersonNewBorn_HepatitNum,
						ISNULL(PC.PersonNewBorn_HepatitSer, '') as PersonNewBorn_HepatitSer,
						convert(varchar(10),PC.PersonNewBorn_HepatitDate,104) as PersonNewBorn_HepatitDate,
						PC.PersonNewBorn_IsAidsMother,
						PC.PersonNewBorn_IsHepatit,
						PC.PersonNewBorn_IsBCG,
						IsBreath.YesNo_Code as PersonNewBorn_IsBreath,
						IsHeart.YesNo_Code as PersonNewBorn_IsHeart,
						IsPulsation.YesNo_Code as PersonNewBorn_IsPulsation,
						IsMuscle.YesNo_Code as PersonNewBorn_IsMuscle,
						PS.PersonEvn_id,
						PC.Person_id,
						PC.Server_id,
						PC.EvnPS_id,
						PC.EvnSection_mid,
						PC.PersonNewBorn_IsBleeding,
						PC.PersonNewBorn_IsNeonatal,
						PC.PersonNewBorn_IsAudio,
						PersonNewBorn_CountChild,
						ChildPositionType_id,
						PersonNewBorn_IsRejection,
						PC.PersonNewBorn_Head,
						PC.PersonNewBorn_Height as PersonNewBorn_Height, PC.PersonNewBorn_Weight as PersonNewBorn_Weight,
						PC.PersonNewBorn_Breast,
						convert(varchar(10),PS.Person_BirthDay,104) as Person_BirthDay,
						PC.PersonNewborn_BloodBili,
						PC.PersonNewborn_BloodHemoglo,
						PC.PersonNewborn_BloodEryth,
						PC.PersonNewborn_BloodHemato,
						PC.PersonNewBorn_pid,
						PC.RefuseType_pid,
						PC.RefuseType_bid,
						PC.RefuseType_gid,
						PC.RefuseType_aid
					from
						v_PersonNewBorn PC with (nolock)
						left join v_PersonState ps with(nolock) on PC.person_id=ps.person_id
						outer apply(select top 1 ph.PersonHeight_Height,ph.PersonHeight_id from v_personHeight ph with(nolock) where ph.person_id =PC.Person_id and ph.HeightMeasureType_id = 1) H
						outer apply(select top 1 case when pw.Okei_id=37 then pw.PersonWeight_Weight*1000 else pw.PersonWeight_Weight end as PersonWeight_Weight,pw.PersonWeight_id from v_personWeight pw with(nolock) where pw.person_id =PC.Person_id and pw.WeightMeasureType_id = 1) W
						left join v_YesNo IsBreath with(nolock) on IsBreath.YesNo_id = PC.PersonNewBorn_IsBreath
						left join v_YesNo IsHeart with(nolock) on IsHeart.YesNo_id = PC.PersonNewBorn_IsHeart
						left join v_YesNo IsPulsation with(nolock) on IsPulsation.YesNo_id = PC.PersonNewBorn_IsPulsation
						left join v_YesNo IsMuscle with(nolock) on IsMuscle.YesNo_id = PC.PersonNewBorn_IsMuscle
					where (1 = 1)
						and PC.Person_id = :Person_id
						order by PC.PersonNewborn_insDT desc
				";

					$result = $this->db->query($query, array(
						'Person_id' => $data['Person_id']
					));

					$res = $result->result('array');
					if (count($res) > 0) {

						//создание новой специфики со значениями равной первой

						$query = "
						declare
							@Res bigint,
							@ErrCode int,
							@ErrMessage varchar(4000);
			
						exec p_PersonNewborn_Copy
							@PersonNewBorn_id_new = @Res output,
							@PersonNewBorn_id = :PersonNewBorn_id,
							@PersonNewBorn_pid = :PersonNewBorn_pid,
							@EvnPS_id = :EvnPS_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output;
			
						select @Res as PersonNewBorn_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
					";

						$queryParams['PersonNewBorn_id'] = $res[0]['PersonNewBorn_id'];
						$queryParams['PersonNewBorn_pid'] = $res[0]['PersonNewBorn_id'];
						$queryParams['EvnPS_id'] = $data['EvnPS_id'];
						//$queryParams['EvnSection_id'] = $data['EvnSection_id'];

						//echo getDebugSQL($query, $queryParams);exit();
						$res = $this->queryResult($query, $queryParams);
						if (!is_array($res)) {
							return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение сведений о специфике)'));
						}
						if ($this->isSuccessful($res)) {
							//$res[0]['PersonNewBorn_id'] = '1';

							$query = "
					select top 1
						PC.PersonNewBorn_id,
						PC.ChildTermType_id,
						PC.FeedingType_id,
						PC.NewBornWardType_id,
						ISNULL(PC.PersonNewBorn_BCGNum, '') as PersonNewBorn_BCGNum,
						ISNULL(PC.PersonNewBorn_BCGSer, '') as PersonNewBorn_BCGSer,
						convert(varchar(10),PC.PersonNewBorn_BCGDate,104) as PersonNewBorn_BCGDate,
						ISNULL(PC.PersonNewBorn_HepatitNum, '') as PersonNewBorn_HepatitNum,
						ISNULL(PC.PersonNewBorn_HepatitSer, '') as PersonNewBorn_HepatitSer,
						convert(varchar(10),PC.PersonNewBorn_HepatitDate,104) as PersonNewBorn_HepatitDate,
						PC.PersonNewBorn_IsAidsMother,
						PC.PersonNewBorn_IsHepatit,
						PC.PersonNewBorn_IsBCG,
						IsBreath.YesNo_Code as PersonNewBorn_IsBreath,
						IsHeart.YesNo_Code as PersonNewBorn_IsHeart,
						IsPulsation.YesNo_Code as PersonNewBorn_IsPulsation,
						IsMuscle.YesNo_Code as PersonNewBorn_IsMuscle,
						PS.PersonEvn_id,
						PC.Person_id,
						PC.Server_id,
						PC.EvnPS_id,
						PC.EvnSection_mid,
						PC.PersonNewBorn_IsBleeding,
						PC.PersonNewBorn_IsNeonatal,
						PC.PersonNewBorn_IsAudio,
						PersonNewBorn_CountChild,
						ChildPositionType_id,
						PersonNewBorn_IsRejection,
						PC.PersonNewBorn_Head,
						PC.PersonNewBorn_Height as PersonNewBorn_Height, PC.PersonNewBorn_Weight as PersonNewBorn_Weight,
						PC.PersonNewBorn_Breast,
						convert(varchar(10),PS.Person_BirthDay,104) as Person_BirthDay,
						PC.PersonNewborn_BloodBili,
						PC.PersonNewborn_BloodHemoglo,
						PC.PersonNewborn_BloodEryth,
						PC.PersonNewborn_BloodHemato
					from
						v_PersonNewBorn PC with (nolock)
						left join v_PersonState ps with(nolock) on PC.person_id=ps.person_id
						outer apply(select top 1 ph.PersonHeight_Height,ph.PersonHeight_id from v_personHeight ph with(nolock) where ph.person_id =PC.Person_id and ph.HeightMeasureType_id = 1) H
						outer apply(select top 1 case when pw.Okei_id=37 then pw.PersonWeight_Weight*1000 else pw.PersonWeight_Weight end as PersonWeight_Weight,pw.PersonWeight_id from v_personWeight pw with(nolock) where pw.person_id =PC.Person_id and pw.WeightMeasureType_id = 1) W
						left join v_YesNo IsBreath with(nolock) on IsBreath.YesNo_id = PC.PersonNewBorn_IsBreath
						left join v_YesNo IsHeart with(nolock) on IsHeart.YesNo_id = PC.PersonNewBorn_IsHeart
						left join v_YesNo IsPulsation with(nolock) on IsPulsation.YesNo_id = PC.PersonNewBorn_IsPulsation
						left join v_YesNo IsMuscle with(nolock) on IsMuscle.YesNo_id = PC.PersonNewBorn_IsMuscle
					where (1 = 1)
						and PC.PersonNewBorn_id = :PersonNewBorn_id
						order by PC.PersonNewborn_insDT asc
				";
							/*
													$result = $this->db->query($query_, array(
														'PersonNewBorn_id' => $res[0]['PersonNewBorn_id']
													));
							*/
							$result = $this->db->query($query_, array(
								'Person_id' => $data['Person_id'],
								'EvnPS_id' => $data['EvnPS_id']
							));

							$res = $result->result('array');

							//return $res;
						}

					}

				}
				return $res;
			} else {
				return false;
			}
		}
	}

	/**
	 *
	 * @param array $data
	 * @return array
	 */
	function savePersonNewBorn($data) {
		$procedure = "p_PersonNewBorn_ins";
		//var_dump($data['ApgarData']);die;
		$queryParams = array(
			'Server_id' => $data['Server_id'],
			'BirthSpecStac_id' => (!empty($data['BirthSpecStac_id']) ? $data['BirthSpecStac_id'] : NULL),
			'PersonNewBorn_id' => (!empty($data['PersonNewBorn_id']) ? $data['PersonNewBorn_id'] : NULL),
			'EvnPS_id' => (!empty($data['EvnPS_id']) ? $data['EvnPS_id'] : NULL),
			'EvnSection_mid'=>(!empty($data['EvnSection_mid']) ? $data['EvnSection_mid'] : NULL),
			'PersonNewBorn_IsHighRisk'=>(!empty($data['PersonNewBorn_IsHighRisk']) ? $data['PersonNewBorn_IsHighRisk'] : NULL),
			'pmUser_id' => $data['pmUser_id'],
			'Person_id' => $data['Person_id']
		);

		$whereEvnPS = '';
		if ('ufa' == $this->regionNick) {
			if (!empty($data['EvnPS_id'])) {
				$whereEvnPS = ' and PNB.EvnPS_id=:EvnPS_id ';
			}
		}

		$sql = "
			select top 1
				PNB.PersonNewBorn_id,
				PNB.BirthSpecStac_id,
				PNB.EvnPS_id,
				PNB.EvnSection_mid,
				ES.EvnSection_pid as EvnPS_mid,
				PNB.PersonNewBorn_IsHighRisk
			from v_PersonNewBorn PNB with(nolock)
			left join v_EvnSection ES with(nolock) on ES.EvnSection_id = PNB.EvnSection_mid
			where PNB.Person_id = :Person_id
			{$whereEvnPS}
		";
		$res =  $this->db->query($sql, array('Person_id'=>$data['Person_id'], 'EvnPS_id'=>$data['EvnPS_id']));
		$res = $res->result('array');
		if(count($res)>0){
			if (!empty($res[0]['EvnPS_id']) && !empty($res[0]['EvnPS_mid']) && $res[0]['EvnPS_id'] == $res[0]['EvnPS_mid']) {
				return $this->createError('','Совпадают КВС матери и КВС ребенка');
			}

			$queryParams['PersonNewBorn_id'] = (!empty($res[0]['PersonNewBorn_id'])&&$queryParams['PersonNewBorn_id']==null)?$res[0]['PersonNewBorn_id']:$queryParams['PersonNewBorn_id'];
			$data['PersonNewBorn_id'] =$queryParams['PersonNewBorn_id'];
			$queryParams['BirthSpecStac_id'] = (!empty($res[0]['BirthSpecStac_id'])&&$queryParams['BirthSpecStac_id']==null)?$res[0]['BirthSpecStac_id']:$queryParams['BirthSpecStac_id'];
			$queryParams['EvnPS_id'] = (!empty($res[0]['EvnPS_id'])&&$queryParams['EvnPS_id']==null)?$res[0]['EvnPS_id']:$queryParams['EvnPS_id'];
			$queryParams['EvnSection_mid'] = (!empty($res[0]['EvnSection_mid'])&&$queryParams['EvnSection_mid']==null)?$res[0]['EvnSection_mid']:$queryParams['EvnSection_mid'];
			$queryParams['PersonNewBorn_IsHighRisk'] = (!empty($res[0]['PersonNewBorn_IsHighRisk'])&&empty($queryParams['PersonNewBorn_IsHighRisk']))?$res[0]['PersonNewBorn_IsHighRisk']:$queryParams['PersonNewBorn_IsHighRisk'];
		}

		$personInfo = $this->getFirstRowFromQuery("
			select top 1
				convert(varchar(10), PS.Person_BirthDay, 120) as Person_BirthDay
			from v_PersonState PS with(nolock)
			where PS.Person_id = :Person_id
		", array('Person_id'=>$data['Person_id']));
		if (!is_array($personInfo)) {
			return $this->createError('','Ошибка при получении данных о ребёнке');
		}

		if ('ufa' == $this->regionNick && $data['PersonNewBorn_id'] == 1) {
			$data['PersonNewBorn_id'] = '';
			$queryParams['PersonNewBorn_id'] = '';
		}

		if ( !empty($data['PersonNewBorn_id'])) {
			$procedure = "p_PersonNewBorn_upd";
		}
		$queryParams['NewBornWardType_id'] = (!empty($data['NewBornWardType_id']) ? $data['NewBornWardType_id'] : NULL);//
		$queryParams['FeedingType_id'] =(!empty($data['FeedingType_id']) ? $data['FeedingType_id'] : NULL);//
		$queryParams['ChildTermType_id'] = (!empty($data['ChildTermType_id']) ? $data['ChildTermType_id'] : NULL);//
		$queryParams['PersonNewBorn_IsAidsMother'] = (!empty($data['PersonNewBorn_IsAidsMother']) ? $data['PersonNewBorn_IsAidsMother'] : NULL);//
		$queryParams['PersonNewBorn_IsAudio'] = (!empty($data['PersonNewBorn_IsAudio']) ? $data['PersonNewBorn_IsAudio'] : NULL);
		$queryParams['PersonNewBorn_IsBleeding'] = (!empty($data['PersonNewBorn_IsBleeding']) ? $data['PersonNewBorn_IsBleeding'] : NULL);
		$queryParams['PersonNewBorn_IsNeonatal'] = (!empty($data['PersonNewBorn_IsNeonatal']) ? $data['PersonNewBorn_IsNeonatal'] : NULL);
		$queryParams['PersonNewBorn_IsBCG'] = (!empty($data['PersonNewBorn_IsBCG']) ? $data['PersonNewBorn_IsBCG'] : NULL);//
		$queryParams['PersonNewBorn_BCGSer'] = (!empty($data['PersonNewBorn_BCGSer']) ? $data['PersonNewBorn_BCGSer'] : NULL);//
		$queryParams['PersonNewBorn_BCGNum'] = (!empty($data['PersonNewBorn_BCGNum']) ? $data['PersonNewBorn_BCGNum'] : NULL);//
		$queryParams['PersonNewBorn_BCGDate'] = (!empty($data['PersonNewBorn_BCGDate']) ? $data['PersonNewBorn_BCGDate'] : NULL);//
		$queryParams['PersonNewBorn_IsHepatit'] = (!empty($data['PersonNewBorn_IsHepatit']) ? $data['PersonNewBorn_IsHepatit'] : NULL);
		$queryParams['PersonNewBorn_HepatitSer'] = (!empty($data['PersonNewBorn_HepatitSer']) ? $data['PersonNewBorn_HepatitSer'] : NULL);
		$queryParams['PersonNewBorn_HepatitNum'] = (!empty($data['PersonNewBorn_HepatitNum']) ? $data['PersonNewBorn_HepatitNum'] : NULL);
		$queryParams['PersonNewBorn_HepatitDate'] = (!empty($data['PersonNewBorn_HepatitDate']) ? $data['PersonNewBorn_HepatitDate'] : NULL);
		$queryParams['PersonNewBorn_CountChild'] = (!empty($data['PersonNewBorn_CountChild']) ? $data['PersonNewBorn_CountChild'] : NULL);
		$queryParams['ChildPositionType_id'] = (!empty($data['ChildPositionType_id']) ? $data['ChildPositionType_id'] : NULL);
		$queryParams['PersonNewBorn_IsRejection'] = (!empty($data['PersonNewBorn_IsRejection']) ? $data['PersonNewBorn_IsRejection'] : NULL);
		$queryParams['PersonNewBorn_Height'] = (!empty($data['PersonNewBorn_Height']) ? $data['PersonNewBorn_Height'] : NULL);
		$queryParams['PersonNewBorn_Weight'] = (!empty($data['PersonNewBorn_Weight']) ? $data['PersonNewBorn_Weight'] : NULL);
		$queryParams['PersonNewBorn_Head'] = (!empty($data['PersonNewBorn_Head']) ? $data['PersonNewBorn_Head'] : NULL);
		$queryParams['PersonNewBorn_Breast'] = (!empty($data['PersonNewBorn_Breast']) ? $data['PersonNewBorn_Breast'] : NULL);
		$queryParams['PersonNewBorn_IsBreath'] = (!empty($data['PersonNewBorn_IsBreath']) ? $data['PersonNewBorn_IsBreath'] : NULL);
		$queryParams['PersonNewBorn_IsHeart'] = (!empty($data['PersonNewBorn_IsHeart']) ? $data['PersonNewBorn_IsHeart'] : NULL);
		$queryParams['PersonNewBorn_IsPulsation'] = (!empty($data['PersonNewBorn_IsPulsation']) ? $data['PersonNewBorn_IsPulsation'] : NULL);
		$queryParams['PersonNewBorn_IsMuscle'] = (!empty($data['PersonNewBorn_IsMuscle']) ? $data['PersonNewBorn_IsMuscle'] : NULL);
		$queryParams['PersonNewborn_BloodBili'] = (!empty($data['PersonNewborn_BloodBili']) ? $data['PersonNewborn_BloodBili'] : NULL);
		$queryParams['PersonNewborn_BloodHemoglo'] = (!empty($data['PersonNewborn_BloodHemoglo']) ? $data['PersonNewborn_BloodHemoglo'] : NULL);
		$queryParams['PersonNewborn_BloodEryth'] = (!empty($data['PersonNewborn_BloodEryth']) ? $data['PersonNewborn_BloodEryth'] : NULL);
		$queryParams['PersonNewborn_BloodHemato'] = (!empty($data['PersonNewborn_BloodHemato']) ? $data['PersonNewborn_BloodHemato'] : NULL);

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @Res = :PersonNewBorn_id;

			exec " . $procedure . "
				@Server_id = :Server_id,
				@PersonNewBorn_id = @Res output,
				@Person_id = :Person_id,
				@FeedingType_id = :FeedingType_id,
				@ChildTermType_id = :ChildTermType_id,
				@EvnSection_mid = :EvnSection_mid,
				@PersonNewBorn_Weight=:PersonNewBorn_Weight,
				@PersonNewBorn_Height=:PersonNewBorn_Height,
				@PersonNewBorn_Head=:PersonNewBorn_Head,
				@PersonNewBorn_Breast=:PersonNewBorn_Breast,
				@PersonNewBorn_IsAidsMother = :PersonNewBorn_IsAidsMother,
				@PersonNewBorn_IsAudio = :PersonNewBorn_IsAudio,
				@PersonNewBorn_IsBleeding = :PersonNewBorn_IsBleeding,
				@PersonNewBorn_IsNeonatal=:PersonNewBorn_IsNeonatal,
				@PersonNewBorn_IsBCG = :PersonNewBorn_IsBCG,
				@PersonNewBorn_BCGSer = :PersonNewBorn_BCGSer,
				@PersonNewBorn_BCGNum = :PersonNewBorn_BCGNum,
				@PersonNewBorn_BCGDate = :PersonNewBorn_BCGDate,
				@PersonNewBorn_IsHepatit = :PersonNewBorn_IsHepatit,
				@PersonNewBorn_HepatitSer = :PersonNewBorn_HepatitSer,
				@PersonNewBorn_HepatitNum = :PersonNewBorn_HepatitNum,
				@PersonNewBorn_HepatitDate = :PersonNewBorn_HepatitDate,
				@PersonNewBorn_CountChild = :PersonNewBorn_CountChild,
				@PersonNewBorn_IsBreath = :PersonNewBorn_IsBreath,
				@PersonNewBorn_IsHeart = :PersonNewBorn_IsHeart,
				@PersonNewBorn_IsPulsation = :PersonNewBorn_IsPulsation,
				@PersonNewBorn_IsMuscle = :PersonNewBorn_IsMuscle,
				@PersonNewBorn_IsHighRisk = :PersonNewBorn_IsHighRisk,
				@PersonNewborn_BloodBili = :PersonNewborn_BloodBili,
				@PersonNewborn_BloodHemoglo = :PersonNewborn_BloodHemoglo,
				@PersonNewborn_BloodEryth = :PersonNewborn_BloodEryth,
				@PersonNewborn_BloodHemato = :PersonNewborn_BloodHemato,
				@ChildPositionType_id = :ChildPositionType_id,
				@PersonNewBorn_IsRejection=:PersonNewBorn_IsRejection,
				@BirthSpecStac_id = :BirthSpecStac_id,
				@EvnPS_id = :EvnPS_id,
				@NewBornWardType_id = :NewBornWardType_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as PersonNewBorn_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		//echo getDebugSQL($query, $queryParams);exit();
		$res = $this->queryResult($query, $queryParams);
		if (!is_array($res)) {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение сведений о новорожденном)'));
		}
		if (!$this->isSuccessful($res)) {
			return $res;
		}

		//создание новой специфики с наследованием значений заполненных полей от предыдущей 
		$IsInheritPNB = ($data['PersonNewBorn_id'] != null && $res[0]['PersonNewBorn_id'] != null && $data['PersonNewBorn_id'] != $res[0]['PersonNewBorn_id']);
		
		$this->updatePersonChild($data);

		if(!empty($data['PersonNewBorn_Height'])){
			$this->load->model('PersonHeight_model');
			$data['HeightMeasureType_id']=1;
			$data['PersonHeight_setDate']=$personInfo['Person_BirthDay'];
			$data['Okei_id']=2;
			$data['PersonHeight_Height'] = $data['PersonNewBorn_Height'];
			$this->PersonHeight_model->savePersonHeight($data);
		}
		if(!empty($data['PersonNewBorn_Weight'])){
			$this->load->model('PersonWeight_model');
			$data['WeightMeasureType_id']=1;
			$data['PersonWeight_setDate']=$personInfo['Person_BirthDay'];;
			$data['Okei_id']=36;
			$data['PersonWeight_Weight']=$data['PersonNewBorn_Weight'];
			$this->PersonWeight_model->savePersonWeight($data);
		}
		if ($IsInheritPNB) {
			$i = 0;
			foreach ($data['PersonBirthTraumaData'] as $item) {
				if ($item['PersonBirthTrauma_id'] != '') {
					$data['PersonBirthTraumaData'][$i]['PersonNewBorn_id'] = $res[0]['PersonNewBorn_id'];
				}
				if (isset($item['RecordStatus_Code']) && $item['RecordStatus_Code'] == '1') {
					$data['PersonBirthTraumaData'][$i]['RecordStatus_Code'] = 0;
				}
				$i = $i + 1;
			}
		}
		if (!empty($data['PersonBirthTraumaData']) && is_array($data['PersonBirthTraumaData'])) {
			$savePersonBirthTraumaResult = $this->savePersonBirthTrauma(array(
				"PersonNewBorn_id"=>$res[0]['PersonNewBorn_id'],
				"Server_id"=>$data['Server_id'],
				'pmUser_id'=>$data['pmUser_id'],
				"PersonBirthTraumaData"=>$data['PersonBirthTraumaData'],
				'IsInheritPNB'=>$IsInheritPNB
			));
		}
		if (!empty($data['ApgarData']) && is_array($data['ApgarData'])) {

			if ($IsInheritPNB) {
				$i=0;
				foreach ($data['ApgarData'] as $item) {
					if (isset($item['RecordStatus_Code']) && $item['RecordStatus_Code'] == '1') {
						$data['ApgarData'][$i]['RecordStatus_Code'] = 0;
						$data['ApgarData'][$i]['NewbornApgarRate_id'] = '';
					}
					$i=$i+1;
				}
			}
			
			$SaveApgarResult = $this->saveApgarRate(array(
				"PersonNewBorn_id"=>$res[0]['PersonNewBorn_id'],
				"Server_id"=>$data['Server_id'],
				'pmUser_id'=>$data['pmUser_id'],
				"ApgarData"=>$data['ApgarData']
			));
		}
		$this->updatePersonNewbornIsHighRisk(array(
			'PersonNewBorn_id' => $res[0]['PersonNewBorn_id'],
		));

		return $res;
	}
	/**
	 *
	 * @param type $data
	 * @return type
	 */
	function savePersonBirthTrauma($data) {
		$IsClear = false;
		foreach($data['PersonBirthTraumaData'] as $item){
			if(empty($item['Diag_id'])){
				continue;
			}
			if (!$IsClear && $data['IsInheritPNB']){
				$query = "
					delete from PersonBirthTrauma with (ROWLOCK) where PersonNewBorn_id=:PersonNewBorn_id
				";
				$result = $this->db->query($query, $data);
				$IsClear=true;
			}
			switch($item['RecordStatus_Code']){
				case 0:
				case 2:
					$procedure = "p_PersonBirthTrauma_ins";

					$queryParams = array(
						'BirthTraumaType_id' => $item['BirthTraumaType_id'],
						'PersonNewBorn_id' =>$data['PersonNewBorn_id'],
						'PersonBirthTrauma_setDate'=>date('Y-m-d', strtotime($item['PersonBirthTrauma_setDate'])),
						'PersonBirthTrauma_id' => NULL,
						'PersonBirthTrauma_Comment' => (!empty($item['PersonBirthTrauma_Comment']) ? $item['PersonBirthTrauma_Comment'] : NULL),
						'pmUser_id' => $data['pmUser_id'],
						'Server_id'=>$data['Server_id'],
						'Diag_id' => $item['Diag_id']
					);
					if($item['RecordStatus_Code']==2){
						$queryParams['PersonBirthTrauma_id']=$item['PersonBirthTrauma_id'];
						$procedure = "p_PersonBirthTrauma_upd";
					}
					$query = "
						declare
							@Res bigint,
							@ErrCode int,
							@ErrMessage varchar(4000);

						set @Res = :PersonBirthTrauma_id;

						exec " . $procedure . "
							@PersonNewBorn_id = :PersonNewBorn_id,
							@PersonBirthTrauma_id = @Res output,
							@PersonBirthTrauma_setDate = :PersonBirthTrauma_setDate,
							@BirthTraumaType_id = :BirthTraumaType_id,
							@Diag_id = :Diag_id,
							@PersonBirthTrauma_Comment = :PersonBirthTrauma_Comment,
							@Server_id = :Server_id,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output;

						select @Res as PersonBirthTrauma_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
					";
					/*echo getDebugSQL($query, $queryParams);exit();*/
					$result = $this->db->query($query, $queryParams);
					break;

				case 3:
					$queryParams = array('PersonBirthTrauma_id'=>$item['PersonBirthTrauma_id']);
					$query = "
						declare
							@ErrCode int,
							@ErrMessage varchar(4000);
						exec p_PersonBirthTrauma_del
							@PersonBirthTrauma_id = :PersonBirthTrauma_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output;

						select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
					";
					/*echo getDebugSQL($query, $queryParams);exit();*/
					$result = $this->db->query($query, $queryParams);
					break;
			}
		}

	}	
}