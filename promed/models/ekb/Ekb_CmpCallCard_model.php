<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Kareliya_CmpCallCardModel - модель для работы с картами вызова СМП. Версия для Карелии
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package			Common
* @access			public
* @copyright		Copyright (c) 2013 Swan Ltd.
* @author			Popkov Sergey
* @version			kareliya
*/

require_once(APPPATH.'models/CmpCallCard_model.php');

class Ekb_CmpCallCard_model extends CmpCallCard_model {
		
	/**
	 * default desc 
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
				,CLC.Feldsher_id
				,CASE WHEN ISNULL(CLC.LpuBuilding_id,0) > 0 THEN LB.LpuBuilding_Name ELSE CLC.StationNum END as StationNum
				,CLC.EmergencyTeamNum
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

				,RTRIM(MPA.Person_Fio) as FeldsherAcceptName
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
				
				,CASE WHEN ISNULL(CLC.isSogl,1) = 2 THEN 'Да' ELSE 'Нет' END as isSogl
				,CASE WHEN ISNULL(CLC.isOtkazMed,1) = 2 THEN 'Да' ELSE 'Нет' END as isOtkazMed
				,CASE WHEN ISNULL(CLC.isOtkazHosp,1) = 2 THEN 'Да' ELSE 'Нет' END as isOtkazHosp
				
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
				,UDIAG.Diag_FullName as uDiag
				,UDIAG.Diag_Code as uCodeDiag
				,CLC.HelpPlace
				,CLC.HelpAuto
				,CLC.EfAD
				,CLC.EfChss
				,CLC.EfPulse
				,CLC.EfTemperature
				,CLC.EfChd
				,CLC.EfPulsks
				,CLC.EfGluck
				,CLC.Kilo
				,CLC.DescText
				-- ,CLC.pmUser_id
			from
				v_CmpCloseCard CLC with (nolock)
				LEFT JOIN Sex SX with (nolock) on SX.Sex_id = CLC.Sex_id
				LEFT JOIN v_CmpReason RS with (nolock) on RS.CmpReason_id = CLC.CallPovod_id
				LEFT JOIN KLStreet KL_ST with (nolock) on KL_ST.KLStreet_id = CLC.Street_id
				LEFT JOIN KLArea KL_AR with (nolock) on KL_AR.KLArea_id = CLC.Area_id
				LEFT JOIN KLArea KL_CITY with (nolock) on KL_CITY.KLArea_id = CLC.City_id
				LEFT JOIN KLArea KL_TOWN with (nolock) on KL_TOWN.KLArea_id = CLC.Town_id
				LEFT JOIN v_CmpCallType CCT with (nolock) on CCT.CmpCallType_id = CLC.CallType_id
				LEFT JOIN v_CmpCallerType CCrT with (nolock) on CCrT.CmpCallerType_id=CLC.CmpCallerType_id
				left join v_Diag DIAG (nolock) on DIAG.Diag_id = CLC.Diag_id
				LEFT JOIN v_Diag UDIAG (nolock) on UDIAG.Diag_id = CLC.Diag_uid
				left join v_CmpCallCard CC (nolock) on CC.CmpCallCard_id = CLC.CmpCallCard_id
				left join v_Lpu Lpu (nolock) on Lpu.Lpu_id = CC.Lpu_id
				LEFT JOIN v_LpuBuilding LB (nolock) on LB.LpuBuilding_id = CLC.LpuBuilding_id
				LEFT JOIN v_MedPersonal MPA with (nolock) on MPA.MedPersonal_id = CLC.FeldsherAccept
				LEFT JOIN v_MedPersonal MPT with (nolock) on MPT.MedPersonal_id = CLC.FeldsherTrans
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
		
}
