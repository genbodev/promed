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

require_once(APPPATH.'models/_pgsql/CmpCallCard_model.php');

class Ekb_CmpCallCard_model extends CmpCallCard_model {
		
	/**
	 * default desc 
	 */
	function printCmpCloseCard110($data) {
		$query = "
			select
				CLC.CmpCallCard_id as \"CmpCallCard_id\"
				,CLC.CmpCloseCard_id as \"CmpCloseCard_id\"
				--,CC.CmpCallCard_Numv as \"Day_num\"
				--,CC.CmpCallCard_Ngod as \"Year_num\"
				,CLC.Day_num as \"Day_num\"
				,CLC.Year_num as \"Year_num\"
				--,to_char(CLC.CmpCloseCard_insDT, 'dd.mm.yyyy') as \"CardDate\"				
				,to_char(CC.CmpCallCard_insDT, 'dd.mm.yyyy') as \"CallCardDate\"
				,CLC.Feldsher_id as \"Feldsher_id\"
				,CASE WHEN COALESCE(CLC.LpuBuilding_id,0) > 0 THEN LB.LpuBuilding_Name ELSE CLC.StationNum END as \"StationNum\"
				,CLC.EmergencyTeamNum as \"EmergencyTeamNum\"
				,to_char(CLC.AcceptTime, 'hh24:mi') as \"AcceptTime\"
				,to_char(CLC.AcceptTime, 'dd.mm.yyyy') as \"AcceptDate\"
				,to_char(CLC.TransTime, 'hh24:mi') as \"TransTime\"
				,to_char(CLC.GoTime, 'hh24:mi') as \"GoTime\"
				,to_char(CLC.ArriveTime, 'hh24:mi') as \"ArriveTime\"
				,to_char(CLC.TransportTime, 'hh24:mi') as \"TransportTime\"
				,to_char(CLC.ToHospitalTime, 'hh24:mi') as \"ToHospitalTime\"
				,to_char(CLC.EndTime, 'hh24:mi') as \"EndTime\"
				,to_char(CLC.BackTime, 'hh24:mi') as \"BackTime\"
				,CLC.SummTime as \"SummTime\"
				
				,CLC.Area_id as \"Area_id\"
				,KL_AR.KLArea_Name as \"Area\"
				,CLC.City_id as \"City_id\"
				,KL_CITY.KLArea_Name as \"City\"
				,CLC.Town_id as \"Town_id\"
				,KL_TOWN.KLArea_Name as \"Town\"
				,CLC.Street_id as \"Street_id\"
				,KL_ST.KLStreet_Name as \"Street\"

				,CLC.House as \"House\"
				,Lpu.Lpu_name as \"Lpu_name\"
				,Lpu.UAddress_Address as \"UAddress_Address\"
				,Lpu.Lpu_Phone as \"Lpu_Phone\"
				,CLC.Korpus as \"Korpus\"
				,CLC.Room as \"Room\"
				,CLC.Office as \"Office\"
				,CLC.Entrance as \"Entrance\"
				,CLC.Level as \"Level\"
				,CLC.CodeEntrance as \"CodeEntrance\"
				
				,CLC.Fam as \"Fam\"
				,CLC.Name as \"Name\"
				,CLC.Middle as \"Middle\"
				,CLC.Age as \"Age\"
				,SX.Sex_name as \"Sex_name\"
				,RS.CmpReason_Name as \"Reason\"
				
				,CLC.Work as \"Work\"
				,CLC.DocumentNum as \"DocumentNum\"
				,CLC.Ktov as \"Ktov\"
				,COALESCE(CCrT.CmpCallerType_Name,CLC.Ktov) as \"CmpCallerType_Name\"
				,CLC.Phone as \"Phone\"
				
				,CLC.FeldsherAccept as \"FeldsherAccept\"
				,CLC.FeldsherTrans as \"FeldsherTrans\"

				,RTRIM(MPA.Person_Fio) as \"FeldsherAcceptName\"
				,RTRIM(MPT.Person_Fio) as \"FeldsherTransName\"
				
				,CLC.CallType_id as \"CallType_id\"
				,CCT.CmpCallType_Name as \"CallType\"
				
				,CASE WHEN COALESCE(CLC.isAlco,1) = 2 THEN 'Да' ELSE 'Нет' END as \"isAlco\"
				,CLC.Complaints as \"Complaints\"
				,CLC.Anamnez as \"Anamnez\"
				,CASE WHEN COALESCE(CLC.isMenen,1) = 2 THEN 'Да' ELSE 'Нет' END as \"isMenen\"
				,CASE WHEN COALESCE(CLC.isNist,1) = 2 THEN 'Да' ELSE 'Нет' END as \"isNist\"
				,CASE WHEN COALESCE(CLC.isAnis,1) = 2 THEN 'Да' ELSE 'Нет' END as \"isAnis\"
				,CASE WHEN COALESCE(CLC.isLight,1) = 2 THEN 'Да' ELSE 'Нет' END as \"isLight\"
				,CASE WHEN COALESCE(CLC.isAcro,1) = 2 THEN 'Да' ELSE 'Нет' END as \"isAcro\"
				,CASE WHEN COALESCE(CLC.isMramor,1) = 2 THEN 'Да' ELSE 'Нет' END as \"isMramor\"
				,CASE WHEN COALESCE(CLC.isHale,1) = 2 THEN 'Да' ELSE 'Нет' END as \"isHale\"
				,CASE WHEN COALESCE(CLC.isPerit,1) = 2 THEN 'Да' ELSE 'Нет' END as \"isPerit\"
				
				,CASE WHEN COALESCE(CLC.isSogl,1) = 2 THEN 'Да' ELSE 'Нет' END as \"isSogl\"
				,CASE WHEN COALESCE(CLC.isOtkazMed,1) = 2 THEN 'Да' ELSE 'Нет' END as \"isOtkazMed\"
				,CASE WHEN COALESCE(CLC.isOtkazHosp,1) = 2 THEN 'Да' ELSE 'Нет' END as \"isOtkazHosp\"
				
				,CLC.Urine as \"Urine\"
				,CLC.Shit as \"Shit\"
				,CLC.OtherSympt as \"OtherSympt\"
				,CLC.WorkAD as \"WorkAD\"
				,CLC.AD as \"AD\"
				,CLC.Chss as \"Chss\"
				,CLC.Pulse as \"Pulse\"
				,CLC.Temperature as \"Temperature\"
				,CLC.Chd as \"Chd\"
				,CLC.Pulsks as \"Pulsks\"
				,CLC.Gluck as \"Gluck\"
				,CLC.LocalStatus as \"LocalStatus\"
				,CLC.Ekg1 as \"Ekg1\"
				,to_char(CLC.Ekg1Time, 'hh24:mi') as \"Ekg1Time\"
				,CLC.Ekg2 as \"Ekg2\"
				,to_char(CLC.Ekg2Time, 'hh24:mi') as \"Ekg2Time\"
				,CLC.Diag_id as \"Diag_id\"
				,DIAG.Diag_FullName as \"Diag\"
				,DIAG.Diag_Code as \"CodeDiag\"				
				,UDIAG.Diag_FullName as \"uDiag\"
				,UDIAG.Diag_Code as \"uCodeDiag\"
				,CLC.HelpPlace as \"HelpPlace\"
				,CLC.HelpAuto as \"HelpAuto\"
				,CLC.EfAD as \"EfAD\"
				,CLC.EfChss as \"EfChss\"
				,CLC.EfPulse as \"EfPulse\"
				,CLC.EfTemperature as \"EfTemperature\"
				,CLC.EfChd as \"EfChd\"
				,CLC.EfPulsks as \"EfPulsks\"
				,CLC.EfGluck as \"EfGluck\"
				,CLC.Kilo as \"Kilo\"
				,CLC.DescText as \"DescText\"
				-- ,CLC.pmUser_id as \"pmUser_id\"
			from
				v_CmpCloseCard CLC
				LEFT JOIN Sex SX on SX.Sex_id = CLC.Sex_id
				LEFT JOIN v_CmpReason RS on RS.CmpReason_id = CLC.CallPovod_id
				LEFT JOIN KLStreet KL_ST on KL_ST.KLStreet_id = CLC.Street_id
				LEFT JOIN KLArea KL_AR on KL_AR.KLArea_id = CLC.Area_id
				LEFT JOIN KLArea KL_CITY on KL_CITY.KLArea_id = CLC.City_id
				LEFT JOIN KLArea KL_TOWN on KL_TOWN.KLArea_id = CLC.Town_id
				LEFT JOIN v_CmpCallType CCT on CCT.CmpCallType_id = CLC.CallType_id
				LEFT JOIN v_CmpCallerType CCrT on CCrT.CmpCallerType_id=CLC.CmpCallerType_id
				left join v_Diag DIAG on DIAG.Diag_id = CLC.Diag_id
				LEFT JOIN v_Diag UDIAG UDIAG.Diag_id = CLC.Diag_uid
				left join v_CmpCallCard CC on CC.CmpCallCard_id = CLC.CmpCallCard_id
				left join v_Lpu Lpu on Lpu.Lpu_id = CC.Lpu_id
				LEFT JOIN v_LpuBuilding LB on LB.LpuBuilding_id = CLC.LpuBuilding_id
				LEFT JOIN v_MedPersonal MPA on MPA.MedPersonal_id = CLC.FeldsherAccept
				LEFT JOIN v_MedPersonal MPT on MPT.MedPersonal_id = CLC.FeldsherTrans
			where
				CLC.CmpCallCard_id = :CmpCallCard_id
			limit 1
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
