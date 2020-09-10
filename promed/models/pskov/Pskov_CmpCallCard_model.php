<?php	defined('BASEPATH') or die ('No direct script access allowed');

require(APPPATH.'models/CmpCallCard_model.php');

class Pskov_CmpCallCard_model extends CmpCallCard_model {
	/**
	 * Печать карты
	 */
	function printCmpCloseCard110($data) {
		$query = "
			select top 1
				CLC.CmpCallCard_id
				,CLC.CmpCloseCard_id
				--,CC.CmpCallCard_Numv as Day_num
				--,CC.CmpCallCard_Ngod as Year_num
				,CLC.Day_num
				,CLC.Year_num
				--,convert(varchar, CLC.CmpCloseCard_insDT, 104) as CardDate				
				,convert(varchar, CC.CmpCallCard_insDT, 104) as CallCardDate				
				,RTRIM(MPA.Person_Fio) as Feldsher_id
				,CASE WHEN ISNULL(CLC.LpuBuilding_id,0) > 0 THEN LB.LpuBuilding_Name ELSE CLC.StationNum END as StationNum
				--,CLC.EmergencyTeamNum
				,ET.EmergencyTeam_Num as EmergencyTeamNum
				
				,convert(varchar(5), CLC.AcceptTime, 108) as AcceptTime
				,convert(varchar, CLC.AcceptTime, 104) as AcceptDate
				,convert(varchar(5), CLC.TransTime, 108) as TransTime
				,convert(varchar(5), CLC.GoTime, 108) as GoTime
				,convert(varchar(5), CLC.ArriveTime, 108) as ArriveTime
				,convert(varchar(5), CLC.TransportTime, 108) as TransportTime
				,convert(varchar(5), CLC.ToHospitalTime, 108) as ToHospitalTime
				,convert(varchar(5), CLC.EndTime, 108) as EndTime
				,convert(varchar(5), CLC.BackTime, 108) as BackTime
				,CLC.SummTime
				
				,CLC.Area_id
				,KL_AR.KLArea_Name as Area
				,CLC.City_id
				,KL_CITY.KLArea_Name as City
				,CLC.Town_id
				,KL_TOWN.KLArea_Name as Town
				,CLC.Street_id
				,KL_ST.KLStreet_Name as Street

				,CLC.House	
				,Lpu.Lpu_name
				,Lpu.UAddress_Address
				,Lpu.Lpu_Phone
				,CLC.Korpus
				,CLC.Room
				,CLC.Office
				,CLC.Entrance
				,CLC.Level
				,CLC.CodeEntrance
				
				,CLC.Fam
				,CLC.Name
				,CLC.Middle
				,CLC.Age
				,SX.Sex_name
				,RS.CmpReason_Name as Reason
				
				,CLC.Work
				,CLC.DocumentNum
				,CLC.Ktov,
				COALESCE(CCrT.CmpCallerType_Name,CLC.Ktov) as CmpCallerType_Name,
				CLC.Phone
				
				,CLC.FeldsherAccept
				,CLC.FeldsherTrans
								
				--,RTRIM(PMCA.PMUser_Name) as FeldsherAcceptName
				,RTRIM(MPA.Person_Fio) as FeldsherAcceptName
				--,RTRIM(PMCT.PMUser_Name) as FeldsherTransName
				,RTRIM(MPT.Person_Fio) as FeldsherTransName
				
				,CLC.CallType_id
				,CCT.CmpCallType_Name as CallType
				
				,CASE WHEN ISNULL(CLC.isAlco,1) = 2 THEN 'Да' ELSE 'Нет' END as isAlco
				,CLC.Complaints
				,CLC.Anamnez
				,CASE WHEN ISNULL(CLC.isMenen,1) = 2 THEN 'Да' ELSE 'Нет' END as isMenen
				,CASE WHEN ISNULL(CLC.isNist,1) = 2 THEN 'Да' ELSE 'Нет' END as isNist
				,CASE WHEN ISNULL(CLC.isAnis,1) = 2 THEN 'Да' ELSE 'Нет' END as isAnis
				,CASE WHEN ISNULL(CLC.isLight,1) = 2 THEN 'Да' ELSE 'Нет' END as isLight
				,CASE WHEN ISNULL(CLC.isAcro,1) = 2 THEN 'Да' ELSE 'Нет' END as isAcro
				,CASE WHEN ISNULL(CLC.isMramor,1) = 2 THEN 'Да' ELSE 'Нет' END as isMramor
				,CASE WHEN ISNULL(CLC.isHale,1) = 2 THEN 'Да' ELSE 'Нет' END as isHale
				,CASE WHEN ISNULL(CLC.isPerit,1) = 2 THEN 'Да' ELSE 'Нет' END as isPerit
				,CLC.Urine
				,CLC.Shit
				,CLC.OtherSympt
				,CLC.WorkAD
				,CLC.AD
				,CLC.Chss
				,CLC.Pulse
				,CLC.Temperature
				,CLC.Chd
				,CLC.Pulsks
				,CLC.Gluck
				,CLC.LocalStatus
				,CLC.Ekg1
				,convert(varchar(5), CLC.Ekg1Time, 108) as Ekg1Time				
				,CLC.Ekg2
				,convert(varchar(5), CLC.Ekg2Time, 108) as Ekg2Time
				,CLC.Diag_id
				,DIAG.Diag_FullName as Diag
				,DIAG.Diag_Code as CodeDiag				
				,CLC.HelpPlace
				,CLC.HelpAuto
				,CLC.EfAD
				,CASE WHEN ISNULL(CLC.EfChss,0) = 0 THEN NULL ELSE CLC.EfChss END as EfChss
				,CASE WHEN ISNULL(CLC.EfPulse,0) = 0 THEN NULL ELSE CLC.EfPulse END as EfPulse
				,CLC.EfTemperature
				,CASE WHEN ISNULL(CLC.EfChd,0) = 0 THEN NULL ELSE CLC.EfChd END as EfChd
				,CLC.EfPulsks
				,CLC.EfGluck
				,CLC.Kilo
				,CLC.DescText
				,CLC.MessageNum
				,CASE WHEN ISNULL(CLC.isSogl,1) = 2 THEN 'Да' ELSE 'Нет' END as isSogl
				,CASE WHEN ISNULL(CLC.isOtkazMed,1) = 2 THEN 'Да' ELSE 'Нет' END as isOtkazMed
				,CASE WHEN ISNULL(CLC.isOtkazHosp,1) = 2 THEN 'Да' ELSE 'Нет' END as isOtkazHosp
				,CASE WHEN ISNULL(CLC.isOtkazSign,1) = 2 THEN 'Да' ELSE 'Нет' END as isOtkazSign				
				,CLC.OtkazSignWhy				
				,convert(varchar, CLC.DisStart, 104) as DisStart
				,CLC.AcceptFio
				,convert(varchar(5), CLC.CmpCloseCardWhere_DT, 108) as CmpCloseCardWhere_DT
				,Cause.CmpCloseCardCause_name
				,WhereReported.CmpCloseCardWhereReported_name
				-- ,CLC.pmUser_id
			from
				v_CmpCloseCard CLC with (nolock)

				LEFT JOIN Sex SX with (nolock) on SX.Sex_id = CLC.Sex_id

				LEFT JOIN v_MedPersonal as MPA with (nolock) ON( MPA.MedPersonal_id = CLC.FeldsherAccept)
				LEFT JOIN v_MedPersonal as MPT with (nolock) ON( MPT.MedPersonal_id = CLC.FeldsherTrans)

				LEFT join v_pmUserCache PMCA with (nolock) on PMCA.PMUser_id = CLC.FeldsherAccept
				LEFT join v_pmUserCache PMCT with (nolock) on PMCT.PMUser_id = CLC.FeldsherTrans
				
				LEFT JOIN v_EmergencyTeam ET with (nolock) on ET.EmergencyTeam_id = CLC.EmergencyTeam_id

				LEFT JOIN KLStreet KL_ST with (nolock) on KL_ST.KLStreet_id = CLC.Street_id
				LEFT JOIN KLArea KL_AR with (nolock) on KL_AR.KLArea_id = CLC.Area_id
				LEFT JOIN KLArea KL_CITY with (nolock) on KL_CITY.KLArea_id = CLC.City_id
				LEFT JOIN KLArea KL_TOWN with (nolock) on KL_TOWN.KLArea_id = CLC.Town_id
				LEFT JOIN v_CmpCallType CCT with (nolock) on CCT.CmpCallType_id = CLC.CallType_id
				LEFT JOIN v_CmpCallerType CCrT with (nolock) on CCrT.CmpCallerType_id = CLC.CmpCallerType_id
				left join v_Diag DIAG (nolock) on DIAG.Diag_id = CLC.Diag_id
				left join v_CmpCallCard CC (nolock) on CC.CmpCallCard_id = CLC.CmpCallCard_id
				LEFT JOIN v_CmpReason RS with (nolock) on RS.CmpReason_id = CC.CmpReason_id
				left join v_Lpu Lpu (nolock) on Lpu.Lpu_id = CC.Lpu_id
				LEFT JOIN v_LpuBuilding LB (nolock) on LB.LpuBuilding_id = CLC.LpuBuilding_id
				left join v_CmpCloseCardCause Cause (nolock) on Cause.CmpCloseCardCause_id = CLC.CmpCloseCardCause_id
				left join v_CmpCloseCardWhereReported WhereReported (nolock) on WhereReported.CmpCloseCardWhereReported_id = CLC.CmpCloseCardWhereReported_id
			
			where
				CLC.CmpCallCard_id = :CmpCallCard_id
		";
		
		$result = $this->db->query($query, array(
			'CmpCallCard_id' => $data['CmpCallCard_id']		
		));
		

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 * default desc 
	 */
    /*
	function loadCmpCloseCardEditForm($data) {
		
		$queryParams = array();
		$filter = '(1 = 1)';
		$filter .=" and CCC.CmpCallCard_id = :CmpCallCard_id";
		
		$query = "
			select top 1
				'' as accessType,
				CCC.CmpCallCard_id,
				CCC.CmpCallCard_Numv as Day_num,
				CCC.CmpCallCard_Ngod as Year_num,
				RTRIM(PMC.PMUser_Name) as FeldsherAcceptName,				
				RTRIM(PMC.PMUser_Name) as Feldsher_id,
				--CCC.pmUser_updID as FeldsherAcceptName,
				CCC.CmpCallCard_IsAlco as isAlco,
				CCC.CmpCallType_id as CallType_id,
				CCC.CmpReason_id,
				CCC.CmpReasonNew_id as CallPovodNew_id,
				CCC.Sex_id,
				CCC.KLSubRgn_id as Area_id,
				CCC.KLCity_id as City_id,
				CCC.KLTown_id as Town_id,
				CCC.KLStreet_id as Street_id,
				CCC.CmpCallCard_Dom as House,
				CCC.CmpCallCard_Korp as Korpus,
				CCC.CmpCallCard_Room as Room,
				CCC.CmpCallCard_Kvar as Office,
				CCC.CmpCallCard_Podz as Entrance,
				CCC.CmpCallCard_Etaj as Level,
				CCC.CmpCallCard_Kodp as CodeEntrance,
				CCC.CmpCallCard_Telf as Phone,
				CCC.CmpCallPlaceType_id,
				CCC.CmpCallCard_Comm as DescText,
				
				CCC.Person_id,
		
				case when PS.Document_Ser is not null then PS.Document_Ser end + ' ' +
				case when PS.Document_Num is not null then PS.Document_Num end						
				as DocumentNum,
				

				org1.Org_Name as Work,
				dbfss.SocStatus_SysNick as SocStatusNick,
				
				case
					when CCC.Person_Age > 0 then 219
					when DATEDIFF(DAY, CCC.Person_BirthDay, CCC.CmpCallCard_insDT) > 365 then 219
					when DATEDIFF(DAY, CCC.Person_BirthDay, CCC.CmpCallCard_insDT) > 31 then 220					
					when DATEDIFF(DAY, CCC.Person_BirthDay, CCC.CmpCallCard_insDT) > 0 then 221
					else 219
				end as AgeType_id2,
				
				case
					when CCC.Person_Age > 0 then CCC.Person_Age
					when DATEDIFF(DAY, CCC.Person_BirthDay, CCC.CmpCallCard_insDT) > 365 then DATEDIFF(DAY, CCC.Person_BirthDay, CCC.CmpCallCard_insDT)/365
					when DATEDIFF(DAY, CCC.Person_BirthDay, CCC.CmpCallCard_insDT) > 31 then DATEDIFF(DAY, CCC.Person_BirthDay, CCC.CmpCallCard_insDT)/31					
					when DATEDIFF(DAY, CCC.Person_BirthDay, CCC.CmpCallCard_insDT) > 0 then DATEDIFF(DAY, CCC.Person_BirthDay, CCC.CmpCallCard_insDT)					
					else null
				end as Age,
				


				CCC.CmpReason_id as CallPovod_id,
				
				--cast(CCC.CmpCallCard_prmDT as varchar) as AcceptTime,
				convert(varchar(10), CCC.CmpCallCard_prmDT, 104)+' '+convert(varchar(5), CCC.CmpCallCard_prmDT, 108) as AcceptTime,
				convert(varchar(10), CCCStatusData.TransTime, 104)+' '+convert(varchar(5),CCCStatusData.TransTime,108) as TransTime,

				ISNULL(PS.Person_Surname, case when RTRIM(CCC.Person_SurName) = 'null' then '' else CCC.Person_SurName end) as Fam,
				ISNULL(PS.Person_Firname, case when RTRIM(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end) as Name,
				ISNULL(PS.Person_Secname, case when RTRIM(CCC.Person_SecName) = 'null' then '' else CCC.Person_SecName end) as Middle,
			
				CCC.CmpCallCard_Ktov as Ktov,
				CCC.CmpCallerType_id,
				CCC.Lpu_id
				,CCC.Lpu_ppdid
				,ISNULL(L.Lpu_Nick,'') as CmpLpu_Name				
				,CCC.KLRgn_id
				,CCC.KLSubRgn_id
				,CCC.KLCity_id
				,CCC.KLTown_id
				,CCC.KLStreet_id			
				
				,EMT.EmergencyTeam_Num as EmergencyTeamNum
				,L.Lpu_Nick as StationNum
				--,CCC.pmUser_insID as FeldsherAccept
				--,CCC.pmUser_insID as FeldsherAcceptPskov
				,PMCins.MedPersonal_id as FeldsherAccept
				,PMCins.MedPersonal_id as FeldsherAcceptPskov
				,CCCStatusData.FeldsherTransPmUser_id as FeldsherTrans
				,CCCStatusData.FeldsherTransPmUser_id as FeldsherTransPskov
				,CCC.EmergencyTeam_id

				,CONVERT( varchar, CCC.CmpCallCard_TEnd, 104 ) + ' ' + SUBSTRING( CONVERT( varchar, CCC.CmpCallCard_TEnd, 108 ), 0, 6 ) as EndTime
			from
				v_CmpCallCard CCC with (nolock)
				left join v_PersonState PS (nolock) on PS.Person_id = CCC.Person_id
				left join v_Job as job1 with (nolock) ON PS.Job_id=job1.Job_id
				left join v_Org as org1 with (nolock) ON job1.Org_id=org1.Org_id
				left join SocStatus as dbfss with (nolock) on dbfss.SocStatus_id = PS.SocStatus_id
				LEFT JOIN v_Lpu L with (nolock) on L.Lpu_id = CCC.Lpu_id
				LEFT join v_pmUserCache PMC with (nolock) on PMC.PMUser_id = CCC.pmUser_updID
				LEFT join v_pmUserCache PMCins with (nolock) on PMCins.PMUser_id = CCC.pmUser_insID		
				LEFT JOIN v_EmergencyTeam EMT with (nolock) on EMT.EmergencyTeam_id = CCC.EmergencyTeam_id	
				outer apply (
					select top 1
						CCCS.CmpCallCardStatus_insDT as TransTime,
						CCCS.pmUser_insID as FeldsherTransPmUser_id
					from
						v_CmpCallCardStatus CCCS with (nolock) 
					where
						CCCS.CmpCallCard_id = CCC.CmpCallCard_id
						and CCCS.CmpCallCardStatusType_id = 2
					order by
						CCCS.pmUser_insID desc
				) as CCCStatusData
			where
			{$filter}				
		";
		
		//LEFT JOIN v_pmUser P with (nolock) on P.PMUser_id = CCC.pmUser_updID
			
		$result = $this->db->query($query, array(
			'CmpCallCard_id' => $data['CmpCallCard_id']		
		));

		if ( is_object($result) ) {
			//var_dump($result->result('array')); exit;
			return $result->result('array');
		}
		else {
			return false;
		}
	}
    */
	/**
	 * default desc 
	 */
    /*
	function loadCmpCloseCardViewForm($data) {
	
		$filter = "FALSE";
		$queryParams = array();
		
		if (!empty($data['CmpCallCard_id'])) {
			$filter = "CCC.CmpCallCard_id = :CmpCallCard_id";
			$queryParams['CmpCallCard_id'] = $data['CmpCallCard_id'];
		} elseif (!empty($data['CmpCloseCard_id'])) {
			$filter = "CClC.CmpCloseCard_id = :CmpCloseCard_id";
			$queryParams['CmpCloseCard_id'] = $data['CmpCloseCard_id'];
		}
		
		$query = "
			select top 1
				CClC.CmpCallCard_id,
				--CClC.CmpCallCard_IsAlco as isAlco,
				CClC.CmpCloseCard_id,
				CCC.CmpReason_id,
				CCC.CmpSecondReason_id,
				CClC.CallPovod_id,
				CClC.Year_num,				
				CClC.Day_num,				
				CClC.Sex_id,				
				CClC.Area_id,
				CClC.City_id,
				CClC.Town_id,
				CClC.Street_id,
				CClC.House,
				CClC.Office,
				CClC.Entrance,
				CClC.Level,
				CClC.CodeEntrance,
				CClC.Phone,				
				CClC.DescText,
				CClC.Fam,
                CClC.Name,
                CClC.Middle,
				CClC.Age,
				CClC.Ktov,
				CClC.CmpCallerType_id
				,CCC.Person_id
				,CCC.KLRgn_id
				,CCC.KLSubRgn_id as Area_id
				,CCC.KLCity_id as City_id
				,CCC.KLTown_id as Town_id
				,CCC.KLStreet_id  as Street_id
				,CCC.CmpCallPlaceType_id
				,CClC.Room	
				,CClC.Korpus
				
				,CClC.EmergencyTeamNum as EmergencyTeamNum
				,CCLC.EmergencyTeam_id as EmergencyTeam_id
				,CClC.StationNum as StationNum
				,CClC.LpuBuilding_id as LpuBuilding_id 
				,CClC.pmUser_insID as Feldsher_id
				
				,CCLC.FeldsherAccept
				--,UCA.MedPersonal_id as FeldsherAccept
				--,UCA.MedPersonal_id as FeldsherAcceptPskov
				,CClC.FeldsherTrans
				,CClC.FeldsherTrans as FeldsherTransPskov
		
				,CClC.PayType_id				
		
				,convert(varchar(10), CClC.AcceptTime, 104)+' '+convert(varchar(5), cast(CClC.AcceptTime as datetime), 108) as AcceptTime
				,convert(varchar(10), CClC.TransTime, 104)+' '+convert(varchar(5), cast(CClC.TransTime as datetime), 108) as TransTime
				,convert(varchar(10), CClC.GoTime, 104)+' '+convert(varchar(5), cast(CClC.GoTime as datetime), 108) as GoTime
				
				,convert(varchar(10), CClC.ArriveTime, 104)+' '+convert(varchar(5), cast(CClC.ArriveTime as datetime), 108) as ArriveTime
				,convert(varchar(10), CClC.TransportTime, 104)+' '+convert(varchar(5), cast(CClC.TransportTime as datetime), 108) as TransportTime
				,convert(varchar(10), CClC.ToHospitalTime, 104)+' '+convert(varchar(5), cast(CClC.ToHospitalTime as datetime), 108) as ToHospitalTime
				,convert(varchar(10), CClC.EndTime, 104)+' '+convert(varchar(5), cast(CClC.EndTime as datetime), 108) as EndTime
				,convert(varchar(10), CClC.BackTime, 104)+' '+convert(varchar(5), cast(CClC.BackTime as datetime), 108) as BackTime
		
				,CClC.SummTime
				,CClC.Work
				,CClC.DocumentNum
				,CClC.CallType_id
				,CClC.CallPovodNew_id				
				,CASE WHEN ISNULL(CClC.isAlco,0) = 0 THEN NULL ELSE CClC.isAlco END as isAlco								
				,CClC.Complaints
				,CClC.Anamnez
				,CASE WHEN ISNULL(CClC.isMenen,0) = 0 THEN NULL ELSE CClC.isMenen END as isMenen
				,CASE WHEN ISNULL(CClC.isAnis,0) = 0 THEN NULL ELSE CClC.isAnis END as isAnis
				,CASE WHEN ISNULL(CClC.isNist,0) = 0 THEN NULL ELSE CClC.isNist END as isNist
				,CASE WHEN ISNULL(CClC.isLight,0) = 0 THEN NULL ELSE CClC.isLight END as isLight
				,CASE WHEN ISNULL(CClC.isAcro,0) = 0 THEN NULL ELSE CClC.isAcro END as isAcro
				,CASE WHEN ISNULL(CClC.isMramor,0) = 0 THEN NULL ELSE CClC.isMramor END as isMramor
				,CASE WHEN ISNULL(CClC.isHale,0) = 0 THEN NULL ELSE CClC.isHale END as isHale
				,CASE WHEN ISNULL(CClC.isPerit,0) = 0 THEN NULL ELSE CClC.isPerit END as isPerit		
				,CClC.Urine
				,CClC.Shit
				,CClC.OtherSympt
				,CClC.WorkAD
				,CClC.AD,
				CASE WHEN COALESCE(CClC.Pulse,0)=0 THEN NULL ELSE CClC.Pulse END as Pulse,
				CASE WHEN COALESCE(CClC.Chss,0)=0 THEN NULL ELSE CClC.Chss END as Chss,
				CASE WHEN COALESCE(CClC.Chd,0)=0 THEN NULL ELSE CClC.Chd END as Chd,
				CClC.Temperature
				,CClC.Pulsks
				,CClC.Gluck
				,CClC.LocalStatus
				,convert(varchar(5), cast(CClC.Ekg1Time as datetime), 108) as Ekg1Time
				,CClC.Ekg1
				,convert(varchar(5), cast(CClC.Ekg2Time as datetime), 108) as Ekg2Time
				,CClC.Ekg2
				,CClC.Diag_id
				,CClC.EfAD
				,CASE WHEN COALESCE(CClC.EfPulse,0)=0 THEN NULL ELSE CClC.EfPulse END as EfPulse
				,CASE WHEN COALESCE(CClC.EfChss,0)=0 THEN NULL ELSE CClC.EfChss END as EfChss
				,CClC.EfTemperature
				,CASE WHEN COALESCE(CClC.EfChd,0)=0 THEN NULL ELSE CClC.EfChd END as EfChd
				,CClC.EfPulsks
				,CClC.EfGluck
				,CClC.Kilo
				,CClC.Lpu_id
				,CClC.HelpPlace
				,CClC.HelpAuto
				,CClC.DescText
		
				,CClC.CmpCloseCardCause_id
				,CClC.CmpCloseCardWhereReported_id
				,CClC.MessageNum
		
				,CClC.MedPersonal_id
				,CClC.MedStaffFact_id
				,CClC.isSogl
				,CClC.isOtkazMed
				,CClC.isOtkazHosp
				,CClC.isOtkazSign
		
				,CClC.OtkazSignWhy
				,convert(varchar(10), CClC.DisStart, 104) as DisStart
				,CClC.AcceptFio
				,convert(varchar(5), cast(CClC.CmpCloseCardWhere_DT as datetime), 108) as CmpCloseCardWhere_DT				
		
				,UCA.PMUser_Name as pmUser_insName
				,UCT.PMUser_Name as FeldsherTransName
			from
				v_CmpCloseCard CClC with (nolock)
				left join v_CmpCallCard CCC (nolock) on CCC.CmpCallCard_id = CClC.CmpCallCard_id
				left join v_PersonState PS (nolock) on PS.Person_id = CCC.Person_id
				LEFT JOIN v_Lpu L with (nolock) on L.Lpu_id = CClC.Lpu_id	
				--LEFT JOIN v_EmergencyTeam EMT with (nolock) on EMT.EmergencyTeam_id = CClC.EmergencyTeam_id	
				LEFT JOIN v_pmUserCache UCA with (nolock) on UCA.PMUser_id = CClC.pmUser_insID
				LEFT JOIN v_pmUserCache UCT with (nolock) on UCT.PMUser_id = CClC.FeldsherTrans
				outer apply (
					select top 1
						CCCS.CmpCallCardStatus_insDT as TransTime,
						CCCS.pmUser_insID as FeldsherTransPmUser_id
					from
						v_CmpCallCardStatus CCCS with (nolock) 
					where
						CCCS.CmpCallCard_id = CClC.CmpCallCard_id
						and CCCS.CmpCallCardStatusType_id = 2
					order by
						CCCS.pmUser_insID desc
				) as CCCStatusData
			where
				{$filter}
		";
		
		//LEFT JOIN v_pmUser P with (nolock) on P.PMUser_id = CCC.pmUser_updID
			
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {			
			return $result->result('array');
		}
		else {
			return false;
		}
	}
    */
	
	/**
	 * Поточный ввод
	 */
    /*
	function saveCmpStreamCard($data) {
		$newdata = $data;		
		$UnicNums ='';
		$statuschange = true;		
		$CmpCallCard_Numv ='';
		
		$procedure = 'p_CmpCallCard_ins';
		$UnicNums = ", 
		@UnicCmpCallCard_Numv bigint,
		@UnicCmpCallCard_Ngod bigint,
		@SQLstring nvarchar(500),
		@ParamDefinition nvarchar(500);

		SET @SQLString = 
			N'SELECT @UnicCmpCallCard_NumvOUT = MAX(CASE WHEN CAST( CCC.CmpCallCard_prmDT as date) = ''".date('Y-m-d')."'' 
			THEN ISNULL(CmpCallCard_Numv,0) ELSE 0 END)+1,
			@UnicCmpCallCard_NgodOUT = MAX(CASE WHEN YEAR( CCC.CmpCallCard_prmDT ) = ".date('Y')." 
			THEN ISNULL(CmpCallCard_Ngod,0) ELSE 0 END)+1
			FROM v_CmpCallCard CCC with (nolock)
			WHERE CCC.Lpu_id = @Lpu_id_forUnicNumRequest ';

		SET @ParamDefinition = N'@UnicCmpCallCard_NumvOUT bigint OUTPUT, 
		@UnicCmpCallCard_NgodOUT bigint OUTPUT, @Lpu_id_forUnicNumRequest bigint';

		exec sp_executesql 
		@SQLString, 
		@ParamDefinition,
		@Lpu_id_forUnicNumRequest = :Lpu_id_forUnicNumRequest,
		@UnicCmpCallCard_NumvOUT = @UnicCmpCallCard_Numv OUTPUT, 
		@UnicCmpCallCard_NgodOUT = @UnicCmpCallCard_Ngod OUTPUT
		";
		
		$CmpCallCard_Numv = ':CmpCallCard_Numv';
		$CmpCallCard_Ngod = ':CmpCallCard_Ngod';


		// Если у нас неизвестный пациент всё равно вставляем в person
		if(
			$data[ 'Fam' ] == 'Неизвестен' &&
			$data[ 'Name' ] == 'Неизвестен' &&
			$data[ 'Middle' ] == 'Неизвестен'
		) {
			$query="
				declare
					@Pers_id bigint = NULL,
					@ErrCode int,
					@ErrMessage varchar(4000);

				exec p_PersonAll_ins
					@Person_id = @Pers_id OUTPUT
					,@Server_id = :Server_id

				,@Person_IsInErz = NULL
				,@PersonSurName_SurName = :PersonSurName_SurName
				,@PersonFirName_FirName = :PersonFirName_FirName
				,@PersonSecName_SecName = :PersonSecName_SecName
				,@Person_IsUnknown = 2
				,@pmUser_id = :pmUser_id
				,@Error_Code = @ErrCode output
				,@Error_Message = @ErrMessage output;

				select @Pers_id as Pid, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			$queryParams = array(
				'pmUser_id' => $data['pmUser_id'],
				'Server_id' => $data['Server_id']
				//,'Person_id' => $data['Person_id']
			);
			$queryParams['PersonSurName_SurName'] = $data[ 'Fam' ];
			$queryParams['PersonFirName_FirName'] = $data[ 'Name' ];
			$queryParams['PersonSecName_SecName'] = $data[ 'Middle' ];


			$res = $this->db->query($query, $queryParams);
			if (!is_object($res)) {
				return array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных');
			}
			$rows = $res->result('array');

			if (!is_array($rows) || count($rows) == 0) {
				return array('success' => false, 'Error_Msg' => 'Ошибки сохранения человека');
			} else if (!empty($rows[0]['Error_Msg'])) {
				return array('success' => false, 'Error_Msg' => $rows[0]['Error_Msg']);
			}
			$data['Person_id'] = $rows[0]['Pid'];		
		}
		
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000)

				".$UnicNums."
			
			set @Res = :CmpCallCard_id;
			
			exec " . $procedure . "
				@CmpCallCard_id = @Res output,
				@CmpCallCard_Numv = ".$CmpCallCard_Numv.",
				@CmpCallCard_Ngod = ".$CmpCallCard_Ngod.",
				
				@CmpCallCard_City = :CmpCallCard_City,
				@CmpCallCard_Ulic = :CmpCallCard_Ulic,
				@CmpCallCard_Dom = :CmpCallCard_Dom,
				@CmpCallCard_Korp = :CmpCallCard_Korp,
				@CmpCallCard_Room = :CmpCallCard_Room,
				@CmpCallCard_Kvar = :CmpCallCard_Kvar,
				@CmpCallCard_Podz = :CmpCallCard_Podz,
				@CmpCallCard_Etaj = :CmpCallCard_Etaj,
				@CmpCallCard_Kodp = :CmpCallCard_Kodp,
				@CmpCallCard_Telf = :CmpCallCard_Telf,
				@CmpCallPlaceType_id = :CmpCallPlaceType_id,
			
				@CmpCallCard_Comm = :CmpCallCard_Comm,
				@CmpReason_id = :CmpReason_id,
			
				@Person_SurName = :Person_Surname,
				@Person_FirName = :Person_Firname,
				@Person_SecName = :Person_Secname,
				@Person_Age = :Person_Age,
				@Person_id = :Person_id,
				
				@Person_PolisNum = :Person_PolisNum,
				@Person_PolisSer = :Person_PolisSer,
				@Sex_id = :Sex_id,
				@Diag_uid = :Diag_uid,
				@CmpCallCard_Ktov = :CmpCallCard_Ktov,
				@CmpCallerType_id = :CmpCallerType_id,
				@CmpCallType_id = :CmpCallType_id,
			
				@CmpCallCard_IsReceivedInPPD = :CmpCallCard_IsReceivedInPPD,
			
				@KLRgn_id = :KLRgn_id,
				@KLSubRgn_id = :KLSubRgn_id,
				@KLCity_id = :KLCity_id,
				@KLTown_id = :KLTown_id,
				@KLStreet_id = :KLStreet_id,				
				
				@CmpCallCard_prmDT = :CmpCallCard_prmDT,
				@CmpCallCard_IsOpen = :CmpCallCard_IsOpen,
				@CmpCallCardStatusType_id = :CmpCallCardStatusType_id,
				
				@Lpu_ppdid = :Lpu_ppdid,
				@Lpu_id = :Lpu_id,
				
				@LpuBuilding_id = :LpuBuilding_id,

				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as CmpCallCard_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		if (isset($data['AcceptDT'])) {
			$aDate = explode(" ",$data['AcceptDT']);
		} else {
			$aDate = explode(" ",$data['AcceptTime']);
		}
		$aTime = $aDate[1];
		$aDate = explode(".",$aDate[0]);
		$aDate = $aDate[2].'-'.$aDate[1].'-'.$aDate[0].' '.$aTime;	

		$queryParams = array(
			'Lpu_id_forUnicNumRequest' => $data['Lpu_id'],
			'CmpCallCard_id' => $data['CmpCallCard_id'],
			'CmpCallCard_Numv' => $data['Day_num'],
			'CmpCallCard_Ngod' => $data['Year_num'],
			'CmpCallCard_City' => $data['City_id'],
			'CmpCallCard_Ulic' => $data['Street_id'],
			'CmpCallCard_Dom' => $data['House'],
			'CmpCallCard_Korp' => $data['Korpus'],
			'CmpCallCard_Room' => $data['Room'],			
			'CmpCallCard_Kvar' => $data['Office'],
			'CmpCallCard_Podz' => $data['Entrance'],
			'CmpCallCard_Etaj' => $data['Level'],
			'CmpCallCard_Kodp' => $data['CodeEntrance'],
			'CmpCallCard_Telf' => $data['Phone'],
			'CmpCallCard_Comm' => $data['CmpCallCard_Comm'],
			'CmpCallPlaceType_id' => $data[ 'CmpCallPlaceType_id' ],
			'CmpReason_id' => null,
			'Person_Surname' => $data['Fam'],
			'Person_Firname' => $data['Name'],
			'Person_Secname' => $data['Middle'],
			'Person_Age' => $data['Age'],
			'Person_id' => (!empty( $data[ 'Person_id' ] ) && is_numeric( $data[ 'Person_id' ] ) ? $data[ 'Person_id' ] : null),
			'Person_PolisSer' => $data['PolisSerial'],
			'Person_PolisNum' => $data['PolisNum'],
			'Sex_id' => $data['Sex_id'],
			'Diag_uid' => (isset($data['Diag_uid']) && $data['Diag_uid'] > 0) ? (int)$data['Diag_uid'] : null,
			'CmpCallCard_Ktov' => (!empty( $data[ 'Ktov' ] ) ? $data[ 'Ktov' ] : null),
			'CmpCallerType_id' => (!empty( $data[ 'CmpCallerType_id' ] ) ? $data[ 'CmpCallerType_id' ] : null),
			'CmpCallType_id' => $data['CallType_id'],			
			'KLRgn_id' => (isset($data['KLRgn_id']) && $data['KLRgn_id'] > 0)?$data['KLRgn_id']:((isset($data['KLAreaStat_idEdit']) && $data['KLAreaStat_idEdit'] > 0)?$data['KLAreaStat_idEdit']:null),
			'KLSubRgn_id' => (isset($data['Area_id']) && $data['Area_id'] > 0)?$data['Area_id']:null,
			'KLCity_id' => (isset($data['City_id']) && $data['City_id'] > 0)?$data['City_id']:null,
			'KLTown_id' => (isset($data['Town_id']) && $data['Town_id'] > 0)?$data['Town_id']:null,
			'KLStreet_id' => (isset($data['Street_id']) && $data['Street_id'] > 0)?$data['Street_id']:null,
			'Lpu_id' => (isset($data['Lpu_id']) && $data['Lpu_id'] > 0)?$data['Lpu_id']:null,
			'Lpu_ppdid' => (isset($data['Lpu_ppdid']) && $data['Lpu_ppdid'] > 0)?$data['Lpu_ppdid']:null,			
			'CmpCallCard_IsReceivedInPPD' => '1',
			'CmpCallCard_IsOpen' =>  '2',
			'CmpCallCardStatusType_id' => '6',
			'LpuBuilding_id' => (!empty($data['LpuBuilding_id']))?$data['LpuBuilding_id']:null,
			'CmpCallCard_prmDT' => $aDate,
			'pmUser_id' => $data['pmUser_id']
		);
		
		$result = $this->db->query($query, $queryParams);
		if ( is_object( $result ) ) {
			$result = $result->result( 'array' );
			if ( $result[ 0 ][ 'CmpCallCard_id' ] > 0 ) {
				//return $this->saveCmpCloseCard110( array_merge( $data, array( 'CmpCallCard_id' => $result[ 0 ][ 'CmpCallCard_id' ] ) ) );
				$result110 = $this->saveCmpCloseCard110( array_merge( $data , array( 'CmpCallCard_id' => $result[ 0 ][ 'CmpCallCard_id' ] ) ) ) ;
				return array( 0 => array_merge( $result110[ 0 ] , $result[ 0 ] ) ) ;
			}
		}

		return false;
	}
	*/

	/**
	 * default desc 
	 */
    /*
	function saveCmpCallCloseCard($data) {
		$procedure = 'p_CmpCallCard_setCardUpd';
		$UnicNums = ';';
		if ( isset($data['CmpCallCard_prmTime']) ) {
			$data['CmpCallCard_prmDate'] .= ' ' . $data['CmpCallCard_prmTime'] . ':00.000';
		}
		
		//var_dump($data['CmpArea_pid']); exit;
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000)

				".$UnicNums."

			set @Res = :CmpCallCard_id;

			exec " . $procedure . "
				@CmpCallCard_id = @Res output,
				@CmpCallCard_Numv = :CmpCallCard_Numv,
				@CmpCallCard_Ngod = :CmpCallCard_Ngod,
				@CmpCallCard_Prty = :CmpCallCard_Prty,
				@CmpCallCard_Sect = :CmpCallCard_Sect,
				@CmpArea_id = :CmpArea_id,
				@CmpCallCard_City = :CmpCallCard_City,
				@CmpCallCard_Ulic = :CmpCallCard_Ulic,
				@CmpCallCard_Dom = :CmpCallCard_Dom,
				@CmpCallCard_Kvar = :CmpCallCard_Kvar,
				@CmpCallCard_Podz = :CmpCallCard_Podz,
				@CmpCallCard_Etaj = :CmpCallCard_Etaj,
				@CmpCallCard_Kodp = :CmpCallCard_Kodp,
				@CmpCallCard_Telf = :CmpCallCard_Telf,
				@CmpPlace_id = :CmpPlace_id,
				@CmpCallCard_Comm = :CmpCallCard_Comm,				
				@CmpReason_id = :CmpReason_id,
				@Person_id = :Person_id,
				@Person_SurName = :Person_Surname,
				@Person_FirName = :Person_Firname,
				@Person_SecName = :Person_Secname,
				@Person_Age = :Person_Age,
				@Person_BirthDay = :Person_Birthday,
				@Person_PolisNum = :Person_PolisNum,
				@Person_PolisSer = :Person_PolisSer,
				@Sex_id = :Sex_id,
				@CmpCallCard_Ktov = :CmpCallCard_Ktov,
				@CmpCallerType_id = :CmpCallerType_id,
				@CmpCallType_id = :CmpCallType_id,
				@CmpProfile_cid = :CmpProfile_cid,
				@CmpCallCard_Smpt = :CmpCallCard_Smpt,
				@CmpCallCard_Stan = :CmpCallCard_Stan,
				@CmpCallCard_prmDT = :CmpCallCard_prmDT,
				@CmpCallCard_Line = :CmpCallCard_Line,
				@CmpResult_id = :CmpResult_id,
				@ResultDeseaseType_id = :ResultDeseaseType_id,
				@CmpArea_gid = :CmpArea_gid,
				@CmpLpu_id = :CmpLpu_id,
				@CmpDiag_oid = :CmpDiag_oid,
				@CmpDiag_aid = :CmpDiag_aid,
				@CmpTrauma_id = :CmpTrauma_id,
				@CmpCallCard_IsAlco = :CmpCallCard_IsAlco,
				@Diag_uid = :Diag_uid,
				@CmpCallCard_Numb = :CmpCallCard_Numb,
				@CmpCallCard_Smpb = :CmpCallCard_Smpb,
				@CmpCallCard_Stbr = :CmpCallCard_Stbr,
				@CmpCallCard_Stbb = :CmpCallCard_Stbb,
				@CmpProfile_bid = :CmpProfile_bid,
				@CmpCallCard_Ncar = :CmpCallCard_Ncar,
				@CmpCallCard_RCod = :CmpCallCard_RCod,
				@CmpCallCard_TabN = :CmpCallCard_TabN,
				@CmpCallCard_Dokt = :CmpCallCard_Dokt,
				@CmpCallCard_Tab2 = :CmpCallCard_Tab2,
				@CmpCallCard_Tab3 = :CmpCallCard_Tab3,
				@CmpCallCard_Tab4 = :CmpCallCard_Tab4,
				@Diag_sid = :Diag_sid,
				@CmpTalon_id = :CmpTalon_id,
				@CmpCallCard_Expo = :CmpCallCard_Expo,
				@CmpCallCard_Smpp = :CmpCallCard_Smpp,
				@CmpCallCard_Vr51 = :CmpCallCard_Vr51,
				@CmpCallCard_D201 = :CmpCallCard_D201,
				@CmpCallCard_Dsp1 = :CmpCallCard_Dsp1,
				@CmpCallCard_Dsp2 = :CmpCallCard_Dsp2,
				@CmpCallCard_Dspp = :CmpCallCard_Dspp,
				@CmpCallCard_Dsp3 = :CmpCallCard_Dsp3,
				@CmpCallCard_Kakp = :CmpCallCard_Kakp,
				@CmpCallCard_Tper = :CmpCallCard_Tper,
				@CmpCallCard_Vyez = :CmpCallCard_Vyez,
				@CmpCallCard_Przd = :CmpCallCard_Przd,
				@CmpCallCard_Tgsp = :CmpCallCard_Tgsp,
				@CmpCallCard_Tsta = :CmpCallCard_Tsta,
				@CmpCallCard_Tisp = :CmpCallCard_Tisp,
				@CmpCallCard_Tvzv = :CmpCallCard_Tvzv,
				@CmpCallCard_Kilo = :CmpCallCard_Kilo,
				@CmpCallCard_Dlit = :CmpCallCard_Dlit,
				@CmpCallCard_Prdl = :CmpCallCard_Prdl,
				@CmpArea_pid = :CmpArea_pid,
				@CmpCallCard_PCity = :CmpCallCard_PCity,
				@CmpCallCard_PUlic = :CmpCallCard_PUlic,
				@CmpCallCard_PDom = :CmpCallCard_PDom,
				@CmpCallCard_PKvar = :CmpCallCard_PKvar,
				@CmpCallCard_Izv1 = :CmpCallCard_Izv1,
				@CmpCallCard_Tiz1 = :CmpCallCard_Tiz1,
				@CmpCallCard_IsReceivedInPPD = :CmpCallCard_IsReceivedInPPD,
				
				@CmpCallCard_Inf1 = :CmpCallCard_Inf1,
				@CmpCallCard_Inf2 = :CmpCallCard_Inf2,
				@CmpCallCard_Inf3 = :CmpCallCard_Inf3,
				@CmpCallCard_Inf4 = :CmpCallCard_Inf4,
				@CmpCallCard_Inf5 = :CmpCallCard_Inf5,
				@CmpCallCard_Inf6 = :CmpCallCard_Inf6,
				
				@KLRgn_id = :KLRgn_id,
				@KLSubRgn_id = :KLSubRgn_id,
				@KLCity_id = :KLCity_id,
				@KLTown_id = :KLTown_id,
				@KLStreet_id = :KLStreet_id,
				
				@Lpu_id = :Lpu_id,
				@Lpu_ppdid = :Lpu_ppdid,
				@CmpCallCard_IsOpen = :CmpCallCard_IsOpen,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as CmpCallCard_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		
		$queryParams = array(
			'CmpCallCard_id' => $data['CmpCallCard_id'],
			'CmpCallCard_Numv' => $data['CmpCallCard_Numv'],
			'CmpCallCard_Ngod' => $data['CmpCallCard_Ngod'],
			'CmpCallCard_Prty' => $data['CmpCallCard_Prty'],
			'CmpCallCard_Sect' => $data['CmpCallCard_Sect'],
			'CmpArea_id' => $data['CmpArea_id'],
			'CmpCallCard_City' => $data['CmpCallCard_City'],
			'CmpCallCard_Ulic' => $data['CmpCallCard_Ulic'],
			'CmpCallCard_Dom' => $data['CmpCallCard_Dom'],
			'CmpCallCard_Kvar' => $data['CmpCallCard_Kvar'],
			'CmpCallCard_Podz' => $data['CmpCallCard_Podz'],
			'CmpCallCard_Etaj' => $data['CmpCallCard_Etaj'],
			'CmpCallCard_Kodp' => $data['CmpCallCard_Kodp'],
			'CmpCallCard_Telf' => $data['CmpCallCard_Telf'],
			'CmpPlace_id' => $data['CmpPlace_id'],
			'CmpCallCard_Comm' => $data['CmpCallCard_Comm'],
			'CmpReason_id' => $data['CmpReason_id'],
			'Person_id' => $data['Person_id'],
			'Person_Surname' => $data['Person_Surname'],
			'Person_Firname' => $data['Person_Firname'],
			'Person_Secname' => $data['Person_Secname'],
			'Person_Age' => $data['Person_Age'],
			'Person_Birthday' => $data['Person_Birthday'],
			'Person_PolisSer' => $data['Polis_Ser'],
			'Person_PolisNum' => $data['Polis_Num'],
			'Sex_id' => $data['Sex_id'],
			'CmpCallCard_Ktov' => (!empty( $data[ 'CmpCallCard_Ktov' ] ) ? $data[ 'CmpCallCard_Ktov' ] : null),
			'CmpCallerType_id' => (!empty( $data[ 'CmpCallerType_id' ] ) ? $data[ 'CmpCallerType_id' ] : null),
			'CmpCallType_id' => $data['CmpCallType_id'],
			'CmpProfile_cid' => $data['CmpProfile_cid'],
			'CmpCallCard_Smpt' => $data['CmpCallCard_Smpt'],
			'CmpCallCard_Stan' => $data['CmpCallCard_Stan'],
			'CmpCallCard_prmDT' => $data['CmpCallCard_prmDate'],
			'CmpCallCard_Line' => $data['CmpCallCard_Line'],
			'CmpResult_id' => $data['CmpResult_id'],
			'ResultDeseaseType_id' => $data['ResultDeseaseType_id'],
			'CmpArea_gid' => $data['CmpArea_gid'],
			'CmpLpu_id' => $data['CmpLpu_id'],
			'CmpDiag_oid' => $data['CmpDiag_oid'],
			'CmpDiag_aid' => $data['CmpDiag_aid'],
			'CmpTrauma_id' => $data['CmpTrauma_id'],
			'CmpCallCard_IsAlco' => $data['CmpCallCard_IsAlco'],
			'Diag_uid' => $data['Diag_uid'],
			'CmpCallCard_Numb' => $data['CmpCallCard_Numb'],
			'CmpCallCard_Smpb' => $data['CmpCallCard_Smpb'],
			'CmpCallCard_Stbr' => $data['CmpCallCard_Stbr'],
			'CmpCallCard_Stbb' => $data['CmpCallCard_Stbb'],
			'CmpProfile_bid' => $data['CmpProfile_bid'],
			'CmpCallCard_Ncar' => $data['CmpCallCard_Ncar'],
			'CmpCallCard_RCod' => $data['CmpCallCard_RCod'],
			'CmpCallCard_TabN' => $data['CmpCallCard_TabN'],
			'CmpCallCard_Dokt' => $data['CmpCallCard_Dokt'],
			'CmpCallCard_Tab2' => $data['CmpCallCard_Tab2'],
			'CmpCallCard_Tab3' => $data['CmpCallCard_Tab3'],
			'CmpCallCard_Tab4' => $data['CmpCallCard_Tab4'],
			'Diag_sid' => $data['Diag_sid'],
			'CmpTalon_id' => $data['CmpTalon_id'],
			'CmpCallCard_Expo' => $data['CmpCallCard_Expo'],
			'CmpCallCard_Smpp' => $data['CmpCallCard_Smpp'],
			'CmpCallCard_Vr51' => $data['CmpCallCard_Vr51'],
			'CmpCallCard_D201' => $data['CmpCallCard_D201'],
			'CmpCallCard_Dsp1' => $data['CmpCallCard_Dsp1'],
			'CmpCallCard_Dsp2' => $data['CmpCallCard_Dsp2'],
			'CmpCallCard_Dspp' => $data['CmpCallCard_Dspp'],
			'CmpCallCard_Dsp3' => $data['CmpCallCard_Dsp3'],
			'CmpCallCard_Kakp' => $data['CmpCallCard_Kakp'],
			'CmpCallCard_Tper' => $data['CmpCallCard_Tper'],
			'CmpCallCard_Vyez' => $data['CmpCallCard_Vyez'],
			'CmpCallCard_Przd' => $data['CmpCallCard_Przd'],
			'CmpCallCard_Tgsp' => $data['CmpCallCard_Tgsp'],
			'CmpCallCard_Tsta' => $data['CmpCallCard_Tsta'],
			'CmpCallCard_Tisp' => $data['CmpCallCard_Tisp'],
			'CmpCallCard_Tvzv' => $data['CmpCallCard_Tvzv'],
			'CmpCallCard_Kilo' => $data['CmpCallCard_Kilo'],
			'CmpCallCard_Dlit' => $data['CmpCallCard_Dlit'],
			'CmpCallCard_Prdl' => $data['CmpCallCard_Prdl'],
			'CmpArea_pid' => $data['CmpArea_pid'],
			'CmpCallCard_PCity' => $data['CmpCallCard_PCity'],
			'CmpCallCard_PUlic' => $data['CmpCallCard_PUlic'],
			'CmpCallCard_PDom' => $data['CmpCallCard_PDom'],
			'CmpCallCard_PKvar' => $data['CmpCallCard_PKvar'],
			// 'CmpLpu_aid' => $data['CmpLpu_aid'],
			// 'CmpCallCard_Medc' => $data['CmpCallCard_Medc'],
			'CmpCallCard_Izv1' => $data['CmpCallCard_Izv1'],
			'CmpCallCard_Tiz1' => $data['CmpCallCard_Tiz1'],
			'CmpCallCard_Inf1' => $data['CmpCallCard_Inf1'],
			'CmpCallCard_Inf2' => $data['CmpCallCard_Inf2'],
			'CmpCallCard_Inf3' => $data['CmpCallCard_Inf3'],
			'CmpCallCard_Inf4' => $data['CmpCallCard_Inf4'],
			'CmpCallCard_Inf5' => $data['CmpCallCard_Inf5'],
			'CmpCallCard_Inf6' => $data['CmpCallCard_Inf6'],
			'KLRgn_id' => $data['KLRgn_id'],
			'KLSubRgn_id' => $data['KLSubRgn_id'],
			'KLCity_id' => $data['KLCity_id'],
			'KLTown_id' => $data['KLTown_id'],
			'KLStreet_id' => $data['KLStreet_id'],
			'Lpu_id' => $data['Lpu_id'],
			'Lpu_ppdid' => $data['Lpu_ppdid'],
			'CmpCallCard_IsOpen' => $data['CmpCallCard_IsOpen'],
			'CmpCallCard_IsReceivedInPPD' =>  array_key_exists( 'CmpCallCard_IsReceivedInPPD', $data ) ? $data['CmpCallCard_IsReceivedInPPD'] : 1,
			'pmUser_id' => $data['pmUser_id']
		);
		
		//var_dump($queryParams['CmpCallCard_TabN']); exit;
		
		$result = $this->db->query($query, $queryParams);
		$resultforstatus = array();
		$resultforstatus = $result->result('array');
		if ( is_object($result) ) {
			if (($data['ARMType']=='smpreg')||($data['ARMType']=='smpdispatchdirect')) {
				$resultforstatus = array();
				$resultforstatus = $result->result('array');
				$data['CmpCallCard_id'] = $resultforstatus[0]['CmpCallCard_id'];
				$data['CmpCallCardStatusType_id'] = 1;
				$data['CmpCallCardStatus_Comment'] = '';
				$this->setStatusCmpCallCard($data);
			}
			return $result->result('array');
		}
		else {
			return false;
		}
	}
    */
	/**
	 * default desc 
	 */
    /*
	function saveCmpCallCard($data, $cccConfig = null) {
		$procedure = '';
		$UnicNums ='';
		$statuschange = true;
		
		$checkLock = $this->checkLockCmpCallCard($data);
		if ($checkLock!= false && is_array($checkLock) && isset($checkLock[0]) && isset($checkLock[0]['CmpCallCard_id'])) {
			return array( array( 'Error_Msg' => 'Невозможно сохранить. Карта вызова редактируется другим пользователем' ) );
		}
		
		$CmpCallCard_Numv ='';		
		if ( (!isset($data['CmpCallCard_id'])) || ($data['CmpCallCard_id'] <= 0) ) {		
			$procedure = 'p_CmpCallCard_ins';
				$UnicNums = ", 
				@UnicCmpCallCard_Numv bigint,
				@UnicCmpCallCard_Ngod bigint,
				@SQLstring nvarchar(500),
				@ParamDefinition nvarchar(500);

				SET @SQLString = 
					N'SELECT @UnicCmpCallCard_NumvOUT = MAX(CASE WHEN CAST( CCC.CmpCallCard_prmDT as date) = ''".date('Y-m-d')."'' 
					THEN ISNULL(CmpCallCard_Numv,0) ELSE 0 END)+1,
					@UnicCmpCallCard_NgodOUT = MAX(CASE WHEN YEAR( CCC.CmpCallCard_prmDT ) = ".date('Y')." 
					THEN ISNULL(CmpCallCard_Ngod,0) ELSE 0 END)+1
					FROM v_CmpCallCard CCC with (nolock)
					WHERE CCC.Lpu_id = @Lpu_id_forUnicNumRequest ';

				SET @ParamDefinition = N'@UnicCmpCallCard_NumvOUT bigint OUTPUT,
				@UnicCmpCallCard_NgodOUT bigint OUTPUT, @Lpu_id_forUnicNumRequest bigint';

				exec sp_executesql
				@SQLString,
				@ParamDefinition,
				@Lpu_id_forUnicNumRequest = :Lpu_id_forUnicNumRequest,
				@UnicCmpCallCard_NumvOUT = @UnicCmpCallCard_Numv OUTPUT, 
				@UnicCmpCallCard_NgodOUT = @UnicCmpCallCard_Ngod OUTPUT
				";
			if ($data['ARMType']=='smpadmin') {
				$CmpCallCard_Numv = ':CmpCallCard_Numv';
				$CmpCallCard_Ngod = ':CmpCallCard_Ngod';
			} else {
				$CmpCallCard_Numv = '@UnicCmpCallCard_Numv';
				$CmpCallCard_Ngod = '@UnicCmpCallCard_Ngod';				
			}
		} else {
			
			//Если админ смп , то делаем копию исходной записи, а измененную копию сохраняем на место старой
			
			//1 - выбираем старую запись
			
			if ( $data['ARMType']=='smpadmin' )	{
				$query = "
					SELECT * 
					FROM v_CmpCallCard CCC with (nolock) 
					WHERE CCC.CmpCallCard_id = " . $data['CmpCallCard_id'] . ";
				";
				$result = $this->db->query($query, $data);

				if ( !is_object($result) ) {
					return false;
				}
				$result = $result->result('array');
				$result = $result[0];

				//2 - сохраняем страую запись в новую

				$procedure = 'p_CmpCallCard_ins';
				$UnicNums = ", 
					@UnicCmpCallCard_Numv bigint,
					@UnicCmpCallCard_Ngod bigint,
					@SQLstring nvarchar(500),
					@ParamDefinition nvarchar(500);

					SET @SQLString = 
						N'SELECT @UnicCmpCallCard_NumvOUT = MAX(CASE WHEN CAST( CCC.CmpCallCard_prmDT as date) = ''".date('Y-m-d')."'' 
						THEN ISNULL(CmpCallCard_Numv,0) ELSE 0 END)+1,
						@UnicCmpCallCard_NgodOUT = MAX(CASE WHEN YEAR( CCC.CmpCallCard_prmDT ) = ".date('Y')." 
						THEN ISNULL(CmpCallCard_Ngod,0) ELSE 0 END)+1
						FROM v_CmpCallCard CCC with (nolock)
						WHERE CCC.Lpu_id = @Lpu_id_forUnicNumRequest ';

					SET @ParamDefinition = N'@UnicCmpCallCard_NumvOUT bigint OUTPUT, 
					@UnicCmpCallCard_NgodOUT bigint OUTPUT, @Lpu_id_forUnicNumRequest bigint';

					exec sp_executesql 
					@SQLString, 
					@ParamDefinition,
					@Lpu_id_forUnicNumRequest = :Lpu_id_forUnicNumRequest,
					@UnicCmpCallCard_NumvOUT = @UnicCmpCallCard_Numv OUTPUT, 
					@UnicCmpCallCard_NgodOUT = @UnicCmpCallCard_Ngod OUTPUT
				";
				$CmpCallCard_Numv = '@UnicCmpCallCard_Numv';
				$CmpCallCard_Ngod = '@UnicCmpCallCard_Ngod';		

				$query = "
					declare
						@Res bigint,
						@ErrCode int,
						@ErrMessage varchar(4000)
						".$UnicNums."
						exec " . $procedure . "
						@CmpCallCard_id = @Res output,
						@CmpCallCard_Numv = ".$CmpCallCard_Numv.",
						@CmpCallCard_Ngod = ".$CmpCallCard_Ngod.",
						@CmpCallCard_Prty = :CmpCallCard_Prty,
						@CmpCallCard_Sect = :CmpCallCard_Sect,
						@CmpArea_id = :CmpArea_id,
						@CmpCallCard_City = :CmpCallCard_City,
						@CmpCallCard_Ulic = :CmpCallCard_Ulic,
						@CmpCallCard_Dom = :CmpCallCard_Dom,
						@CmpCallCard_Korp = :CmpCallCard_Korp,
						@CmpCallCard_Room = :CmpCallCard_Room,
						@CmpCallCard_Kvar = :CmpCallCard_Kvar,
						@CmpCallCard_Podz = :CmpCallCard_Podz,
						@CmpCallCard_Etaj = :CmpCallCard_Etaj,
						@CmpCallCard_Kodp = :CmpCallCard_Kodp,
						@CmpCallCard_Telf = :CmpCallCard_Telf,
						@CmpCallPlaceType_id = :CmpCallPlaceType_id,
						@CmpPlace_id = :CmpPlace_id,
						@CmpCallCard_Comm = :CmpCallCard_Comm,
						@CmpReason_id = :CmpReason_id,
						@CmpSecondReason_id = :CmpSecondReason_id,
						@CmpReasonNew_id = :CmpReasonNew_id,
						@Person_id = :Person_id,
						@Person_SurName = :Person_Surname,
						@Person_FirName = :Person_Firname,
						@Person_SecName = :Person_Secname,
						@Person_Age = :Person_Age,
						@Person_BirthDay = :Person_Birthday,
						@Person_PolisNum = :Person_PolisNum,
						@Person_PolisSer = :Person_PolisSer,
						@Sex_id = :Sex_id,
						@CmpCallCard_Ktov = :CmpCallCard_Ktov,
						@CmpCallerType_id = :CmpCallerType_id,
						@CmpCallType_id = :CmpCallType_id,
						@CmpProfile_cid = :CmpProfile_cid,
						@CmpCallCard_Smpt = :CmpCallCard_Smpt,
						@CmpCallCard_Stan = :CmpCallCard_Stan,
						@CmpCallCard_prmDT = :CmpCallCard_prmDT,
						@CmpCallCard_Line = :CmpCallCard_Line,
						@CmpResult_id = :CmpResult_id,
						@ResultDeseaseType_id = :ResultDeseaseType_id,
						@CmpArea_gid = :CmpArea_gid,
						@CmpLpu_id = :CmpLpu_id,
						@CmpDiag_oid = :CmpDiag_oid,
						@CmpDiag_aid = :CmpDiag_aid,
						@CmpTrauma_id = :CmpTrauma_id,
						@CmpCallCard_IsAlco = :CmpCallCard_IsAlco,
						@Diag_uid = :Diag_uid,
						@CmpCallCard_Numb = :CmpCallCard_Numb,
						@CmpCallCard_Smpb = :CmpCallCard_Smpb,
						@CmpCallCard_Stbr = :CmpCallCard_Stbr,
						@CmpCallCard_Stbb = :CmpCallCard_Stbb,
						@CmpProfile_bid = :CmpProfile_bid,
						@CmpCallCard_Ncar = :CmpCallCard_Ncar,
						@CmpCallCard_RCod = :CmpCallCard_RCod,
						@CmpCallCard_TabN = :CmpCallCard_TabN,
						@CmpCallCard_Dokt = :CmpCallCard_Dokt,
						@MedPersonal_id = :MedPersonal_id,
						@CmpCallCard_IsMedPersonalIdent = :CmpCallCard_IsMedPersonalIdent,
						@CmpCallCard_Tab2 = :CmpCallCard_Tab2,
						@CmpCallCard_Tab3 = :CmpCallCard_Tab3,
						@CmpCallCard_Tab4 = :CmpCallCard_Tab4,
						@Diag_sid = :Diag_sid,
						@CmpTalon_id = :CmpTalon_id,
						@CmpCallCard_Expo = :CmpCallCard_Expo,
						@CmpCallCard_Smpp = :CmpCallCard_Smpp,
						@CmpCallCard_Vr51 = :CmpCallCard_Vr51,
						@CmpCallCard_D201 = :CmpCallCard_D201,
						@CmpCallCard_Dsp1 = :CmpCallCard_Dsp1,
						@CmpCallCard_Dsp2 = :CmpCallCard_Dsp2,
						@CmpCallCard_Dspp = :CmpCallCard_Dspp,
						@CmpCallCard_Dsp3 = :CmpCallCard_Dsp3,
						@CmpCallCard_Kakp = :CmpCallCard_Kakp,
						@CmpCallCard_Tper = :CmpCallCard_Tper,
						@CmpCallCard_Vyez = :CmpCallCard_Vyez,
						@CmpCallCard_Przd = :CmpCallCard_Przd,
						@CmpCallCard_Tgsp = :CmpCallCard_Tgsp,
						@CmpCallCard_Tsta = :CmpCallCard_Tsta,
						@CmpCallCard_Tisp = :CmpCallCard_Tisp,
						@CmpCallCard_Tvzv = :CmpCallCard_Tvzv,
						@CmpCallCard_Kilo = :CmpCallCard_Kilo,
						@CmpCallCard_Dlit = :CmpCallCard_Dlit,
						@CmpCallCard_Prdl = :CmpCallCard_Prdl,
						@CmpArea_pid = :CmpArea_pid,
						@CmpCallCard_PCity = :CmpCallCard_PCity,
						@CmpCallCard_PUlic = :CmpCallCard_PUlic,
						@CmpCallCard_PDom = :CmpCallCard_PDom,
						@CmpCallCard_PKvar = :CmpCallCard_PKvar,
						@CmpCallCard_Izv1 = :CmpCallCard_Izv1,
						@CmpCallCard_Tiz1 = :CmpCallCard_Tiz1,
						@CmpCallCard_IsReceivedInPPD = :CmpCallCard_IsReceivedInPPD,
						@CmpCallCard_Inf1 = :CmpCallCard_Inf1,
						@CmpCallCard_Inf2 = :CmpCallCard_Inf2,
						@CmpCallCard_Inf3 = :CmpCallCard_Inf3,
						@CmpCallCard_Inf4 = :CmpCallCard_Inf4,
						@CmpCallCard_Inf5 = :CmpCallCard_Inf5,
						@CmpCallCard_Inf6 = :CmpCallCard_Inf6,
						@CmpCallCard_firstVersion = :CmpCallCard_firstVersion,
						@KLRgn_id = :KLRgn_id,
						@KLSubRgn_id = :KLSubRgn_id,
						@KLCity_id = :KLCity_id,
						@KLTown_id = :KLTown_id,
						@KLStreet_id = :KLStreet_id,		
						@UnformalizedAddressDirectory_id = :UnformalizedAddressDirectory_id,
						@UslugaComplex_id = :UslugaComplex_id,

						@Lpu_ppdid = :Lpu_ppdid,
						@Lpu_id = :Lpu_id,
						@CmpCallCard_IsOpen = :CmpCallCard_IsOpen,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;

					if ( @ErrMessage is null )
						exec p_CmpCallCard_del
							@CmpCallCard_id = @Res,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output;

					select @Res as CmpCallCard_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";
				
				$queryParams = array(
					'Lpu_id_forUnicNumRequest' => $result['Lpu_id'],
					'CmpCallCard_id' => $result['CmpCallCard_id'],
					'CmpCallCard_Numv' => $result['CmpCallCard_Numv'],
					'CmpCallCard_Ngod' => $result['CmpCallCard_Ngod'],
					'CmpCallCard_Prty' => $result['CmpCallCard_Prty'],
					'CmpCallCard_Sect' => $result['CmpCallCard_Sect'],
					'CmpArea_id' => $result['CmpArea_id'],
					'CmpCallCard_City' => $result['CmpCallCard_City'],
					'CmpCallCard_Ulic' => $result['CmpCallCard_Ulic'],
					'CmpCallCard_Dom' => $result['CmpCallCard_Dom'],
					'CmpCallCard_Korp' => $result['CmpCallCard_Korp'],
					'CmpCallCard_Room' => $result['CmpCallCard_Room'],
					'CmpCallCard_Kvar' => $result['CmpCallCard_Kvar'],
					'CmpCallCard_Podz' => $result['CmpCallCard_Podz'],
					'CmpCallCard_Etaj' => $result['CmpCallCard_Etaj'],
					'CmpCallCard_Kodp' => $result['CmpCallCard_Kodp'],
					'CmpCallCard_Telf' => $result['CmpCallCard_Telf'],
					'CmpCallPlaceType_id' => $result['CmpCallPlaceType_id'],
					'CmpPlace_id' => $result['CmpPlace_id'],
					'CmpCallCard_Comm' => $result['CmpCallCard_Comm'],
					'CmpReason_id' => $result['CmpReason_id'],
					'CmpSecondReason_id' => (!empty( $result[ 'CmpSecondReason_id' ] ) ? $result[ 'CmpSecondReason_id' ] : null),
					'CmpReasonNew_id' => $result['CmpReasonNew_id'],
					'Person_id' => $result['Person_id'],
					'Person_Surname' => $result['Person_SurName'],
					'Person_Firname' => $result['Person_FirName'],
					'Person_Secname' => $result['Person_SecName'],
					'Person_Age' => $result['Person_Age'],
					'Person_Birthday' => $result['Person_BirthDay'],
					'Person_PolisSer' => $result['Person_PolisSer'],
					'Person_PolisNum' => $result['Person_PolisNum'],
					'Sex_id' => $result['Sex_id'],
					'CmpCallCard_Ktov' => (!empty( $result[ 'CmpCallCard_Ktov' ] ) ? $result[ 'CmpCallCard_Ktov' ] : null),
					'CmpCallerType_id' => (!empty( $result[ 'CmpCallerType_id' ] ) ? $result[ 'CmpCallerType_id' ] : null),
					'CmpCallType_id' => $result['CmpCallType_id'],
					'CmpProfile_cid' => $result['CmpProfile_cid'],
					'CmpCallCard_Smpt' => $result['CmpCallCard_Smpt'],
					'CmpCallCard_Stan' => $result['CmpCallCard_Stan'],
					'CmpCallCard_prmDT' => $result['CmpCallCard_prmDT'],
					'CmpCallCard_Line' => $result['CmpCallCard_Line'],
					'CmpResult_id' => $result['CmpResult_id'],
					'ResultDeseaseType_id' => $result['ResultDeseaseType_id'],
					'CmpArea_gid' => $result['CmpArea_gid'],
					'CmpLpu_id' => $result['CmpLpu_id'],
					'CmpDiag_oid' => $result['CmpDiag_oid'],
					'CmpDiag_aid' => $result['CmpDiag_aid'],
					'CmpTrauma_id' => $result['CmpTrauma_id'],
					'CmpCallCard_IsAlco' => $result['CmpCallCard_IsAlco'],
					'Diag_uid' => $result['Diag_uid'],
					'CmpCallCard_Numb' => $result['CmpCallCard_Numb'],
					'CmpCallCard_Smpb' => $result['CmpCallCard_Smpb'],
					'CmpCallCard_Stbr' => $result['CmpCallCard_Stbr'],
					'CmpCallCard_Stbb' => $result['CmpCallCard_Stbb'],
					'CmpProfile_bid' => $result['CmpProfile_bid'],
					'CmpCallCard_Ncar' => $result['CmpCallCard_Ncar'],
					'CmpCallCard_RCod' => $result['CmpCallCard_RCod'],
					'CmpCallCard_TabN' => $result['CmpCallCard_TabN'],
					'CmpCallCard_Dokt' => $result['CmpCallCard_Dokt'],
					'MedPersonal_id' => $result['MedPersonal_id'],
					'CmpCallCard_IsMedPersonalIdent' => $result['CmpCallCard_IsMedPersonalIdent'],
					'CmpCallCard_Tab2' => $result['CmpCallCard_Tab2'],
					'CmpCallCard_Tab3' => $result['CmpCallCard_Tab3'],
					'CmpCallCard_Tab4' => $result['CmpCallCard_Tab4'],
					'Diag_sid' => $result['Diag_sid'],
					'CmpTalon_id' => $result['CmpTalon_id'],
					'CmpCallCard_Expo' => $result['CmpCallCard_Expo'],
					'CmpCallCard_Smpp' => $result['CmpCallCard_Smpp'],
					'CmpCallCard_Vr51' => $result['CmpCallCard_Vr51'],
					'CmpCallCard_D201' => $result['CmpCallCard_D201'],
					'CmpCallCard_Dsp1' => $result['CmpCallCard_Dsp1'],
					'CmpCallCard_Dsp2' => $result['CmpCallCard_Dsp2'],
					'CmpCallCard_Dspp' => $result['CmpCallCard_Dspp'],
					'CmpCallCard_Dsp3' => $result['CmpCallCard_Dsp3'],
					'CmpCallCard_Kakp' => $result['CmpCallCard_Kakp'],
					'CmpCallCard_Tper' => $result['CmpCallCard_Tper'],
					'CmpCallCard_Vyez' => $result['CmpCallCard_Vyez'],
					'CmpCallCard_Przd' => $result['CmpCallCard_Przd'],
					'CmpCallCard_Tgsp' => $result['CmpCallCard_Tgsp'],
					'CmpCallCard_Tsta' => $result['CmpCallCard_Tsta'],
					'CmpCallCard_Tisp' => $result['CmpCallCard_Tisp'],
					'CmpCallCard_Tvzv' => $result['CmpCallCard_Tvzv'],
					'CmpCallCard_Kilo' => $result['CmpCallCard_Kilo'],
					'CmpCallCard_Dlit' => $result['CmpCallCard_Dlit'],
					'CmpCallCard_Prdl' => $result['CmpCallCard_Prdl'],
					'CmpArea_pid' => $result['CmpArea_pid'],
					'CmpCallCard_PCity' => $result['CmpCallCard_PCity'],
					'CmpCallCard_PUlic' => $result['CmpCallCard_PUlic'],
					'CmpCallCard_PDom' => $result['CmpCallCard_PDom'],
					'CmpCallCard_PKvar' => $result['CmpCallCard_PKvar'],
					// 'CmpLpu_aid' => $result['CmpLpu_aid'],
					// 'CmpCallCard_Medc' => $result['CmpCallCard_Medc'],
					'CmpCallCard_Izv1' => $result['CmpCallCard_Izv1'],
					'CmpCallCard_Tiz1' => $result['CmpCallCard_Tiz1'],
					'CmpCallCard_Inf1' => $result['CmpCallCard_Inf1'],
					'CmpCallCard_Inf2' => $result['CmpCallCard_Inf2'],
					'CmpCallCard_Inf3' => $result['CmpCallCard_Inf3'],
					'CmpCallCard_Inf4' => $result['CmpCallCard_Inf4'],
					'CmpCallCard_Inf5' => $result['CmpCallCard_Inf5'],
					'CmpCallCard_Inf6' => $result['CmpCallCard_Inf6'],
					'CmpCallCard_firstVersion' => $result['CmpCallCard_firstVersion'],
					'KLRgn_id' => $result['KLRgn_id'],
					'KLSubRgn_id' => $result['KLSubRgn_id'],
					'KLCity_id' => $result['KLCity_id'],
					'KLTown_id' => $result['KLTown_id'],
					'KLStreet_id' => $result['KLStreet_id'],
					'UnformalizedAddressDirectory_id' => $result['UnformalizedAddressDirectory_id'],
					'UslugaComplex_id' => $result['UslugaComplex_id'],
					'Lpu_id' => $result['Lpu_id'],
					'Lpu_ppdid' => $result['Lpu_ppdid'],
					'CmpCallCard_IsOpen' => $result['CmpCallCard_IsOpen'],
					'CmpCallCard_IsReceivedInPPD' =>  array_key_exists( 'CmpCallCard_IsReceivedInPPD', $result ) ? $result['CmpCallCard_IsReceivedInPPD'] : 1,
					'pmUser_id' => $result['pmUser_insID']
				);

				$result = $this->db->query($query, $queryParams);
				$newfield= array();
				$newfield = $result->result('array');
			}

			//3 - заменяем старую запись текущими изменениями

			if  ( ($data['ARMType']!='smpadmin') || (($data['ARMType']=='smpadmin') && (isset($data['CmpCallCard_id'])) ) )//else
			{
				$procedure = 'p_CmpCallCard_setCardUpd';
				$statuschange = false;
				$UnicNums = ';';
				$CmpCallCard_Numv = ':CmpCallCard_Numv';
				$CmpCallCard_Ngod = ':CmpCallCard_Ngod';
			}				
		}

		if ( isset($data['CmpCallCard_prmTime']) ) {
			$data['CmpCallCard_prmDate'] .= ' ' . $data['CmpCallCard_prmTime'] . ':00.000';
		}
		
		if (isset($data['CmpCallCard_id'])){
			$query = "
				SELECT * 
				FROM v_CmpCallCard CCC with (nolock) 
				WHERE CCC.CmpCallCard_id = " . $data['CmpCallCard_id'] . ";
			";
			$result = $this->db->query($query, $data);

			if ( !is_object($result) ) {
				return false;
			}
			$result = $result->result('array');
			$result = $result[0];
		}
		
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000)

				".$UnicNums."

			
			set @Res = :CmpCallCard_id;
			
			exec " . $procedure . "
				@CmpCallCard_id = @Res output,
				@CmpCallCard_Numv = ".$CmpCallCard_Numv.",
				@CmpCallCard_Ngod = ".$CmpCallCard_Ngod.",
				@CmpCallCard_Prty = :CmpCallCard_Prty,
				@CmpCallCard_Sect = :CmpCallCard_Sect,
				@CmpArea_id = :CmpArea_id,
				@CmpCallCard_City = :CmpCallCard_City,
				@CmpCallCard_Ulic = :CmpCallCard_Ulic,
				@CmpCallCard_Dom = :CmpCallCard_Dom,
				@CmpCallCard_Korp = :CmpCallCard_Korp,
				@CmpCallCard_Room = :CmpCallCard_Room,
				@CmpCallCard_Kvar = :CmpCallCard_Kvar,
				@CmpCallCard_Podz = :CmpCallCard_Podz,
				@CmpCallCard_Etaj = :CmpCallCard_Etaj,
				@CmpCallCard_Kodp = :CmpCallCard_Kodp,
				@CmpCallCard_Telf = :CmpCallCard_Telf,
				@CmpCallPlaceType_id = :CmpCallPlaceType_id,
				@CmpPlace_id = :CmpPlace_id,
				@CmpCallCard_Comm = :CmpCallCard_Comm,
				@CmpReason_id = :CmpReason_id,
				@CmpSecondReason_id = :CmpSecondReason_id,
				@CmpReasonNew_id = :CmpReasonNew_id,
				@Person_id = :Person_id,
				@Person_SurName = :Person_Surname,
				@Person_FirName = :Person_Firname,
				@Person_SecName = :Person_Secname,
				@Person_Age = :Person_Age,
				@Person_BirthDay = :Person_Birthday,
				@Person_PolisNum = :Person_PolisNum,
				@Person_PolisSer = :Person_PolisSer,
				@Sex_id = :Sex_id,
				@CmpCallCard_Ktov = :CmpCallCard_Ktov,
				@CmpCallerType_id = :CmpCallerType_id,
				@CmpCallType_id = :CmpCallType_id,
				@CmpProfile_cid = :CmpProfile_cid,
				@CmpCallCard_Smpt = :CmpCallCard_Smpt,
				@CmpCallCard_Stan = :CmpCallCard_Stan,
				@CmpCallCard_prmDT = :CmpCallCard_prmDT,
				@CmpCallCard_Line = :CmpCallCard_Line,
				@CmpResult_id = :CmpResult_id,
				@ResultDeseaseType_id = :ResultDeseaseType_id,
				@CmpArea_gid = :CmpArea_gid,
				@CmpLpu_id = :CmpLpu_id,
				@CmpDiag_oid = :CmpDiag_oid,
				@CmpDiag_aid = :CmpDiag_aid,
				@CmpTrauma_id = :CmpTrauma_id,
				@CmpCallCard_IsAlco = :CmpCallCard_IsAlco,
				@Diag_uid = :Diag_uid,
				@CmpCallCard_Numb = :CmpCallCard_Numb,
				@CmpCallCard_Smpb = :CmpCallCard_Smpb,
				@CmpCallCard_Stbr = :CmpCallCard_Stbr,
				@CmpCallCard_Stbb = :CmpCallCard_Stbb,
				@CmpProfile_bid = :CmpProfile_bid,
				@CmpCallCard_Ncar = :CmpCallCard_Ncar,
				@CmpCallCard_RCod = :CmpCallCard_RCod,
				@CmpCallCard_TabN = :CmpCallCard_TabN,
				@CmpCallCard_Dokt = :CmpCallCard_Dokt,
				@MedPersonal_id = :MedPersonal_id,
				@MedStaffFact_id = :MedStaffFact_id,
				@CmpCallCard_IsMedPersonalIdent = :CmpCallCard_IsMedPersonalIdent,
				@CmpCallCard_Tab2 = :CmpCallCard_Tab2,
				@CmpCallCard_Tab3 = :CmpCallCard_Tab3,
				@CmpCallCard_Tab4 = :CmpCallCard_Tab4,
				@Diag_sid = :Diag_sid,
				@CmpTalon_id = :CmpTalon_id,
				@CmpCallCard_Expo = :CmpCallCard_Expo,
				@CmpCallCard_Smpp = :CmpCallCard_Smpp,
				@CmpCallCard_Vr51 = :CmpCallCard_Vr51,
				@CmpCallCard_D201 = :CmpCallCard_D201,
				@CmpCallCard_Dsp1 = :CmpCallCard_Dsp1,
				@CmpCallCard_Dsp2 = :CmpCallCard_Dsp2,
				@CmpCallCard_Dspp = :CmpCallCard_Dspp,
				@CmpCallCard_Dsp3 = :CmpCallCard_Dsp3,
				@CmpCallCard_Kakp = :CmpCallCard_Kakp,
				@CmpCallCard_Tper = :CmpCallCard_Tper,
				@CmpCallCard_Vyez = :CmpCallCard_Vyez,
				@CmpCallCard_Przd = :CmpCallCard_Przd,
				@CmpCallCard_Tgsp = :CmpCallCard_Tgsp,
				@CmpCallCard_Tsta = :CmpCallCard_Tsta,
				@CmpCallCard_Tisp = :CmpCallCard_Tisp,
				@CmpCallCard_Tvzv = :CmpCallCard_Tvzv,
				@CmpCallCard_Kilo = :CmpCallCard_Kilo,
				@CmpCallCard_Dlit = :CmpCallCard_Dlit,
				@CmpCallCard_Prdl = :CmpCallCard_Prdl,
				@CmpArea_pid = :CmpArea_pid,
				@CmpCallCard_PCity = :CmpCallCard_PCity,
				@CmpCallCard_PUlic = :CmpCallCard_PUlic,
				@CmpCallCard_PDom = :CmpCallCard_PDom,
				@CmpCallCard_PKvar = :CmpCallCard_PKvar,
				@CmpCallCard_Izv1 = :CmpCallCard_Izv1,
				@CmpCallCard_Tiz1 = :CmpCallCard_Tiz1,
				@CmpCallCard_IsReceivedInPPD = :CmpCallCard_IsReceivedInPPD,
				@CmpCallCard_Inf1 = :CmpCallCard_Inf1,
				@CmpCallCard_Inf2 = :CmpCallCard_Inf2,
				@CmpCallCard_Inf3 = :CmpCallCard_Inf3,
				@CmpCallCard_Inf4 = :CmpCallCard_Inf4,
				@CmpCallCard_Inf5 = :CmpCallCard_Inf5,
				@CmpCallCard_Inf6 = :CmpCallCard_Inf6,				
				@KLRgn_id = :KLRgn_id,
				@KLSubRgn_id = :KLSubRgn_id,
				@KLCity_id = :KLCity_id,
				@KLTown_id = :KLTown_id,
				@KLStreet_id = :KLStreet_id,				
				@UnformalizedAddressDirectory_id = :UnformalizedAddressDirectory_id,
				@UslugaComplex_id = :UslugaComplex_id,

				@Lpu_ppdid = :Lpu_ppdid,
				@Lpu_id = :Lpu_id,
				@CmpCallCard_IsOpen = :CmpCallCard_IsOpen,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as CmpCallCard_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		
		if (isset($result) && isset($data['CmpCallCard_Tper']) && $data['CmpCallCard_Tper'] != '') 
			$result['CmpCallCard_Tper'] = $result['CmpCallCard_Tper']->format('Y-m-d').' '.$data['CmpCallCard_Tper'].':00';
		if (isset($result) && isset($data['CmpCallCard_Vyez']) && $data['CmpCallCard_Vyez'] != '') 
			$result['CmpCallCard_Vyez'] = $result['CmpCallCard_Vyez']->format('Y-m-d').' '.$data['CmpCallCard_Vyez'].':00';
		if (isset($result) && isset($data['CmpCallCard_Przd']) && $data['CmpCallCard_Przd'] != '') 
			$result['CmpCallCard_Przd'] = $result['CmpCallCard_Przd']->format('Y-m-d').' '.$data['CmpCallCard_Przd'].':00';
		if (isset($result) && isset($data['CmpCallCard_Tgsp']) && $data['CmpCallCard_Tgsp'] != '') 
			$result['CmpCallCard_Tgsp'] = $result['CmpCallCard_Tgsp']->format('Y-m-d').' '.$data['CmpCallCard_Tgsp'].':00';
		if (isset($result) && isset($data['CmpCallCard_Tsta']) && $data['CmpCallCard_Tsta'] != '') 
			$result['CmpCallCard_Tsta'] = $result['CmpCallCard_Tsta']->format('Y-m-d').' '.$data['CmpCallCard_Tsta'].':00';
		if (isset($result) && isset($data['CmpCallCard_Tisp']) && $data['CmpCallCard_Tisp'] != '') 
			$result['CmpCallCard_Tisp'] = $result['CmpCallCard_Tisp']->format('Y-m-d').' '.$data['CmpCallCard_Tisp'].':00';
		if (isset($result) && isset($data['CmpCallCard_Tvzv']) && $data['CmpCallCard_Tvzv'] != '') 
			$result['CmpCallCard_Tvzv'] = $result['CmpCallCard_Tvzv']->format('Y-m-d').' '.$data['CmpCallCard_Tvzv'].':00';
		if (isset($result) && isset($data['CmpCallPlaceType_id']) && $data['CmpCallPlaceType_id'] != '') 
			$result['CmpCallPlaceType_id'] = $data['CmpCallPlaceType_id'];
		
		$queryParams = array(
			'Lpu_id_forUnicNumRequest' => $data['Lpu_id'],
			'CmpCallCard_id' => $data['CmpCallCard_id'],
			'CmpCallCard_Numv' => $data['CmpCallCard_Numv'],
			'CmpCallCard_Ngod' => $data['CmpCallCard_Ngod'],
			'CmpCallCard_Prty' => $data['CmpCallCard_Prty'],
			'CmpCallCard_Sect' => $data['CmpCallCard_Sect'],
			'CmpArea_id' => $data['CmpArea_id'],
			'CmpCallCard_City' => $data['CmpCallCard_City'],
			'CmpCallCard_Ulic' => $data['CmpCallCard_Ulic'],
			'CmpCallCard_Dom' => $data['CmpCallCard_Dom'],
			'CmpCallCard_Korp' => $data['CmpCallCard_Korp'],
			'CmpCallCard_Room' => $data['CmpCallCard_Room'],
			'CmpCallCard_Dom' => $data['CmpCallCard_Dom'],
			'CmpCallCard_Kvar' => $data['CmpCallCard_Kvar'],
			'CmpCallCard_Podz' => $data['CmpCallCard_Podz'],
			'CmpCallCard_Etaj' => $data['CmpCallCard_Etaj'],
			'CmpCallCard_Kodp' => $data['CmpCallCard_Kodp'],
			'CmpCallCard_Telf' => $data['CmpCallCard_Telf'],
			'CmpCallPlaceType_id' => $data['CmpCallPlaceType_id'],
			'CmpPlace_id' => $data['CmpPlace_id'],
			'CmpCallCard_Comm' => $data['CmpCallCard_Comm'],
			'CmpReason_id' => $data['CmpReason_id'],
			'CmpSecondReason_id' => $data['CmpSecondReason_id'],
			'CmpReasonNew_id' => $data['CmpReasonNew_id'],
			'Person_id' => $data['Person_id'],
			'Person_Surname' => $data['Person_Surname'],
			'Person_Firname' => $data['Person_Firname'],
			'Person_Secname' => $data['Person_Secname'],
			'Person_Age' => $data['Person_Age'],
			'Person_Birthday' => $data['Person_Birthday'],
			'Person_PolisSer' => $data['Polis_Ser'],
			'Person_PolisNum' => $data['Polis_Num'],
			'Sex_id' => $data['Sex_id'],
			'CmpCallCard_Ktov' => (!empty( $data[ 'CmpCallCard_Ktov' ] ) ? $data[ 'CmpCallCard_Ktov' ] : null),
			'CmpCallerType_id' => (!empty( $data[ 'CmpCallerType_id' ] ) ? $data[ 'CmpCallerType_id' ] : null),
			'CmpCallType_id' => $data['CmpCallType_id'],
			'CmpProfile_cid' => $data['CmpProfile_cid'],
			'CmpCallCard_Smpt' => $data['CmpCallCard_Smpt'],
			'CmpCallCard_Stan' => $data['CmpCallCard_Stan'],
			'CmpCallCard_prmDT' => $data['CmpCallCard_prmDate'],
			'CmpCallCard_Line' => $data['CmpCallCard_Line'],
			'CmpResult_id' => $data['CmpResult_id'],
			'ResultDeseaseType_id' => $data['ResultDeseaseType_id'],
			'CmpArea_gid' => $data['CmpArea_gid'],
			'CmpLpu_id' => $data['CmpLpu_id'],
			'CmpDiag_oid' => $data['CmpDiag_oid'],
			'CmpDiag_aid' => $data['CmpDiag_aid'],
			'CmpTrauma_id' => $data['CmpTrauma_id'],
			'CmpCallCard_IsAlco' => $data['CmpCallCard_IsAlco'],
			'Diag_uid' => $data['Diag_uid'],
			'CmpCallCard_Numb' => $data['CmpCallCard_Numb'],
			'CmpCallCard_Smpb' => $data['CmpCallCard_Smpb'],
			'CmpCallCard_Stbr' => $data['CmpCallCard_Stbr'],
			'CmpCallCard_Stbb' => $data['CmpCallCard_Stbb'],
			'CmpProfile_bid' => $data['CmpProfile_bid'],
			'CmpCallCard_Ncar' => $data['CmpCallCard_Ncar'],
			'CmpCallCard_RCod' => $data['CmpCallCard_RCod'],
			'CmpCallCard_TabN' => $data['CmpCallCard_TabN'],
			'CmpCallCard_Dokt' => $data['CmpCallCard_Dokt'],
			'MedPersonal_id' => $data['MedPersonal_id'],
			'MedStaffFact_id' => $data['MedStaffFact_id'],
			'CmpCallCard_IsMedPersonalIdent' => $data['CmpCallCard_IsMedPersonalIdent'],
			'CmpCallCard_Tab2' => $data['CmpCallCard_Tab2'],
			'CmpCallCard_Tab3' => $data['CmpCallCard_Tab3'],
			'CmpCallCard_Tab4' => $data['CmpCallCard_Tab4'],
			'Diag_sid' => $data['Diag_sid'],
			'CmpTalon_id' => $data['CmpTalon_id'],
			'CmpCallCard_Expo' => $data['CmpCallCard_Expo'],
			'CmpCallCard_Smpp' => $data['CmpCallCard_Smpp'],
			'CmpCallCard_Vr51' => $data['CmpCallCard_Vr51'],
			'CmpCallCard_D201' => $data['CmpCallCard_D201'],
			'CmpCallCard_Dsp1' => $data['CmpCallCard_Dsp1'],
			'CmpCallCard_Dsp2' => $data['CmpCallCard_Dsp2'],
			'CmpCallCard_Dspp' => $data['CmpCallCard_Dspp'],
			'CmpCallCard_Dsp3' => $data['CmpCallCard_Dsp3'],
			'CmpCallCard_Kakp' => $data['CmpCallCard_Kakp'],		
			
			'CmpCallCard_Tper' => (isset($result))?$result['CmpCallCard_Tper']:null,
			'CmpCallCard_Vyez' => (isset($result))?$result['CmpCallCard_Vyez']:null,
			'CmpCallCard_Przd' => (isset($result))?$result['CmpCallCard_Przd']:null,
			'CmpCallCard_Tgsp' => (isset($result))?$result['CmpCallCard_Tgsp']:null,
			'CmpCallCard_Tsta' => (isset($result))?$result['CmpCallCard_Tsta']:null,
			'CmpCallCard_Tisp' => (isset($result))?$result['CmpCallCard_Tisp']:null,
			'CmpCallCard_Tvzv' => (isset($result))?$result['CmpCallCard_Tvzv']:null,
			'CmpCallCard_Kilo' => $data['CmpCallCard_Kilo'],
			'CmpCallCard_Dlit' => $data['CmpCallCard_Dlit'],
			'CmpCallCard_Prdl' => $data['CmpCallCard_Prdl'],
			'CmpArea_pid' => $data['CmpArea_pid'],
			'CmpCallCard_PCity' => $data['CmpCallCard_PCity'],
			'CmpCallCard_PUlic' => $data['CmpCallCard_PUlic'],
			'CmpCallCard_PDom' => $data['CmpCallCard_PDom'],
			'CmpCallCard_PKvar' => $data['CmpCallCard_PKvar'],
			// 'CmpLpu_aid' => $data['CmpLpu_aid'],
			// 'CmpCallCard_Medc' => $data['CmpCallCard_Medc'],
			'CmpCallCard_Izv1' => $data['CmpCallCard_Izv1'],
			'CmpCallCard_Tiz1' => $data['CmpCallCard_Tiz1'],
			'CmpCallCard_Inf1' => $data['CmpCallCard_Inf1'],
			'CmpCallCard_Inf2' => $data['CmpCallCard_Inf2'],
			'CmpCallCard_Inf3' => $data['CmpCallCard_Inf3'],
			'CmpCallCard_Inf4' => $data['CmpCallCard_Inf4'],
			'CmpCallCard_Inf5' => $data['CmpCallCard_Inf5'],
			'CmpCallCard_Inf6' => $data['CmpCallCard_Inf6'],
			'KLRgn_id' => $data['KLRgn_id'],
			'KLSubRgn_id' => $data['KLSubRgn_id'],
			'KLCity_id' => $data['KLCity_id'],
			'KLTown_id' => $data['KLTown_id'],
			'KLStreet_id' => $data['KLStreet_id'],
			'UnformalizedAddressDirectory_id' => $data['UnformalizedAddressDirectory_id'],
			'UslugaComplex_id' => $data['UslugaComplex_id'],
			'Lpu_id' => $data['Lpu_id'],
			'Lpu_ppdid' => $data['Lpu_ppdid'],
			'CmpCallCard_IsOpen' => $data['CmpCallCard_IsOpen'],
			'CmpCallCard_IsReceivedInPPD' =>  array_key_exists( 'CmpCallCard_IsReceivedInPPD', $data ) ? $data['CmpCallCard_IsReceivedInPPD'] : 1,
			'pmUser_id' => $data['pmUser_id']
		);
		
		//var_dump($queryParams); exit;

		$result = $this->db->query($query, $queryParams);
		$resultforstatus = array();
		$resultforstatus = $result->result('array');
		if ( is_object($result) ) {			
			if ((($data['ARMType']=='smpreg')||($data['ARMType']=='smpdispatchdirect')) && $statuschange) {				
				$resultforstatus = array();
				$resultforstatus = $result->result('array');
				$data['CmpCallCard_id'] = $resultforstatus[0]['CmpCallCard_id'];
				$data['CmpCallCardStatusType_id'] = 1;
				$data['CmpCallCardStatus_Comment'] = '';
				$this->setStatusCmpCallCard($data);
			}
			
			//4 - апдейтим версию записи

			if (($data['ARMType']=='smpadmin') && (isset($data['CmpCallCard_id'])) )		
			{
				$query = "
						exec p_CmpCallCard_setFirstVersion 
						@CmpCallCard_id = " . $data['CmpCallCard_id'] . ",
						@CmpCallCard_firstVersion = " . $newfield[0]['CmpCallCard_id'] . ",
						@pmUser_id = " . $data['pmUser_id'] . ";							
					";
				$res = $this->db->query($query, $data);
			}
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	*/
	/**
	 *
	 * загрузка карты смп
	 *
	 * @param type $data
	 * @return boolean 
	 */
    /*
	function loadCmpCallCardEditForm($data) {
		$query = "
			select top 1
				'' as accessType,
				CCC.CmpCallCard_id,
				ISNULL(CCC.Person_id, 0) as Person_id,
				CCC.CmpArea_gid,
				CCC.CmpArea_id,
				CCC.CmpArea_pid,
				CCC.CmpCallCard_IsAlco,
				CCC.CmpCallCard_IsPoli,
				CCC.CmpCallType_id,
				CCC.CmpDiag_aid,
				CCC.CmpDiag_oid,
				CCC.CmpLpu_aid,
				CCC.CmpLpu_id,
				CL.Lpu_id as Lpu_oid,
				CCC.CmpPlace_id,
				CCC.CmpProfile_bid,
				CCC.CmpProfile_cid,
				CCC.CmpReason_id,
				CCC.CmpSecondReason_id,
				CCC.CmpReasonNew_id,
				CCC.CmpResult_id,
				CCC.ResultDeseaseType_id,
				CCC.CmpTalon_id,
				CCC.CmpTrauma_id,
				CCC.Diag_sid,
				CCC.Diag_uid,
				CCC.Sex_id as Sex_id,
				PS.Sex_id as SexIdent_id,
				CCC.CmpCallCard_Numv,
				CCC.CmpCallCard_Ngod,
				CCC.CmpCallCard_Prty,
				CCC.CmpCallCard_Sect,
				CCC.CmpCallCard_City,
				CCC.CmpCallCard_Ulic,
				CCC.CmpCallCard_Dom,
				CCC.CmpCallCard_Korp,
				CCC.CmpCallCard_Room,
				CCC.CmpCallCard_Kvar,
				CCC.CmpCallCard_Podz,
				CCC.CmpCallCard_Etaj,
				CCC.CmpCallCard_Kodp,
				CCC.CmpCallCard_Telf,
				CCC.CmpCallPlaceType_id,
				CCC.CmpCallCard_Comm,
				RTRIM(LTRIM(case when RTRIM(CCC.Person_SurName) = 'null' then '' else CCC.Person_SurName end)) as Person_Surname,
				RTRIM(LTRIM(case when RTRIM(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end)) as Person_Firname,
				RTRIM(LTRIM(case when RTRIM(CCC.Person_SecName) = 'null' then '' else CCC.Person_SecName end)) as Person_Secname,
				RTRIM(LTRIM(ISNULL(PS.Person_Surname, ''))) as PersonIdent_Surname,
				RTRIM(LTRIM(ISNULL(PS.Person_Firname, ''))) as PersonIdent_Firname,
				RTRIM(LTRIM(ISNULL(PS.Person_Secname, ''))) as PersonIdent_Secname,
				convert(varchar(10), CCC.Person_BirthDay, 104) as Person_Birthday,
				CCC.Person_Age as Person_Age,
				ISNULL(dbo.Age2(PS.Person_Birthday, CCC.CmpCallCard_prmDT), '') as PersonIdent_Age,
				CCC.Person_PolisSer as Polis_Ser,
				CCC.Person_PolisNum as Polis_Num,
				PS.Person_EdNum as Polis_EdNum,
				PS.Polis_Num as PolisIdent_Num,
				CCC.CmpCallCard_Ktov,
				CCC.CmpCallerType_id,
				CCC.CmpCallCard_Smpt,
				CCC.CmpCallCard_Stan,
				convert(varchar(10), CCC.CmpCallCard_prmDT, 104) as CmpCallCard_prmDate,
				convert(varchar(5), CCC.CmpCallCard_prmDT, 108) as CmpCallCard_prmTime,
				CCC.CmpCallCard_Line,
				CCC.CmpCallCard_Numb,
				CCC.CmpCallCard_Smpb,
				CCC.CmpCallCard_Stbr,
				CCC.CmpCallCard_Stbb,
				CCC.CmpCallCard_Ncar,
				CCC.CmpCallCard_RCod,
				CCC.CmpCallCard_TabN,
				CCC.CmpCallCard_Dokt,
				CCC.MedPersonal_id,
				isnull(CCC.MedStaffFact_id, msf1.MedStaffFact_id) as MedStaffFact_id,
				ISNULL(CCC.CmpCallCard_IsMedPersonalIdent,1) as CmpCallCard_IsMedPersonalIdent,
				CCC.CmpCallCard_Tab2,
				CCC.CmpCallCard_Tab3,
				CCC.CmpCallCard_Tab4,
				CCC.CmpCallCard_Expo,
				CCC.CmpCallCard_Smpp,
				CCC.CmpCallCard_Vr51,
				CCC.CmpCallCard_D201,
				CCC.CmpCallCard_Dsp1,
				CCC.CmpCallCard_Dsp2,
				CCC.CmpCallCard_Dsp3,
				CCC.CmpCallCard_Dspp,
				CCC.CmpCallCard_Kakp,
				convert(varchar(5), CCC.CmpCallCard_Tper, 108) as CmpCallCard_Tper,
				convert(varchar(5), CCC.CmpCallCard_Vyez, 108) as CmpCallCard_Vyez,
				convert(varchar(5), CCC.CmpCallCard_Przd, 108) as CmpCallCard_Przd,
				convert(varchar(5), CCC.CmpCallCard_Tgsp, 108) as CmpCallCard_Tgsp,
				convert(varchar(5), CCC.CmpCallCard_Tsta, 108) as CmpCallCard_Tsta,
				convert(varchar(5), CCC.CmpCallCard_Tisp, 108) as CmpCallCard_Tisp,
				convert(varchar(5), CCC.CmpCallCard_Tvzv, 108) as CmpCallCard_Tvzv,
				CCC.CmpCallCard_Kilo,
				CCC.CmpCallCard_Dlit,
				CCC.CmpCallCard_Prdl,
				CCC.CmpCallCard_PCity,
				CCC.CmpCallCard_PUlic,
				CCC.CmpCallCard_PDom,
				CCC.CmpCallCard_PKvar,
				CCC.cmpCallCard_Medc,
				CCC.CmpCallCard_Izv1,
				convert(varchar(5), CCC.CmpCallCard_Tiz1, 108) as CmpCallCard_Tiz1,
				CCC.CmpCallCard_Inf1,
				CCC.CmpCallCard_Inf2,
				CCC.CmpCallCard_Inf3,
				CCC.CmpCallCard_Inf4,
				CCC.CmpCallCard_Inf5,
				CCC.CmpCallCard_Inf6
				,UslugaComplex_id
				,CCC.Lpu_id
				,CCC.Lpu_ppdid
				,ISNULL(L.Lpu_Nick,'') as CmpLpu_Name
				,CASE WHEN ISNULL(OC.OftenCallers_id,0) = 0 THEN 1 ELSE 2 END as Person_isOftenCaller
				
				,CCC.UnformalizedAddressDirectory_id
				,case when isnull(CCC.KLStreet_id,0) = 0 then
					case when isnull(CCC.UnformalizedAddressDirectory_id,0) = 0 then NULL
					else 'UA.'+CAST(CCC.UnformalizedAddressDirectory_id as varchar(8)) end
				else 'ST.'+CAST(CCC.KLStreet_id as varchar(8)) end as StreetAndUnformalizedAddressDirectory_id
				
				,CCC.KLRgn_id
				,CCC.KLSubRgn_id
				,CCC.KLCity_id
				,CCC.KLTown_id
				,CCC.KLStreet_id
			from
				v_CmpCallCard CCC with (nolock)
				left join CmpLpu CL with (nolock) on CL.CmpLpu_id = CCC.CmpLpu_id
				-- left join v_PersonState PS (nolock) on PS.Person_id = CCC.Person_id
				outer apply(
					select top 1
						 pa.Person_id
						,ISNULL(pa.Person_SurName, '') as Person_Surname
						,ISNULL(pa.Person_FirName, '') as Person_Firname
						,ISNULL(pa.Person_SecName, '') as Person_Secname
						,pa.Person_BirthDay as Person_Birthday
						,ISNULL(pa.Sex_id, 0) as Sex_id
						,pa.Person_EdNum
						,ISNULL(p.Polis_Num, '') as Polis_Num
					from
						v_Person_all pa with (nolock)
						left join v_Polis p (nolock) on p.Polis_id = pa.Polis_id
					where
						Person_id = CCC.Person_id
						and PersonEvn_insDT <= CCC.CmpCallCard_prmDT
					order by
						PersonEvn_insDT desc
				) PS
				outer apply (
					select top 1 MedStaffFact_id
					from v_MedStaffFact with(nolock)
					where MedPersonal_id = CCC.MedPersonal_id
					and Lpu_id = CCC.CmpLpu_id
					order by PostOccupationType_id asc
				) msf1
				LEFT JOIN v_Lpu L with (nolock) on L.Lpu_id = CCC.CmpLpu_id
				LEFT JOIN v_OftenCallers OC with (nolock) on OC.Person_id = CCC.Person_id
			where
				CCC.CmpCallCard_id = :CmpCallCard_id
		";

		
		$result = $this->db->query($query, array(
			'CmpCallCard_id' => $data['CmpCallCard_id'],
			'Lpu_ppdid' => $data['Lpu_id']
		));
		
		if ( is_object($result) ) {			
			//var_dump($result->result('array')); exit;
			return $result->result('array');
		}
		else {
			return false;
		}
	}
    */
	/**
	 * Человеческая дата
	 */
	function peopleDate($str) {	
		$s = explode(' ',$str);
		$m=array(
			'Jan'=>'01',
			'Feb'=>'02',
			'Mar'=>'03',
			'Apr'=>'04',
			'May'=>'05',
			'Jun'=>'06',
			'Jul'=>'07',
			'Aug'=>'08',
			'Sep'=>'09',
			'Oct'=>'10',
			'Nov'=>'11',
			'Dec'=>'12'
		);
		return $s[2].'.'.$m[$s[1]].'.'.$s[3].' '.$s[4];		
	}	
	
}
