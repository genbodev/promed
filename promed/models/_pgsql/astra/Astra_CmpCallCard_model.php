<?php	defined('BASEPATH') or die ('No direct script access allowed');

require(APPPATH.'models/_pgsql/CmpCallCard_model.php');

class Astra_CmpCallCard_model extends CmpCallCard_model {
	/**
	 * Удаление карты вызова
	 * 
	 * @param array $data
	 * @return bool
	 */
	function deleteCmpCallCard($data = array(), $ignoreRegistryCheck = false, $delCallCard = true) {
		if ( !array_key_exists( 'CmpCallCard_id', $data ) || !$data['CmpCallCard_id'] ) {
			return [['Error_Msg' => 'Не указан идентификатор карты вызова.']];
		}

		$checkLock = $this->checkLockCmpCallCard($data);
		if ($checkLock!= false && is_array($checkLock) && isset($checkLock[0]) && isset($checkLock[0]['CmpCallCard_id'])) {
			return [['Error_Msg' => 'Невозможно удалить. Карта вызова редактируется']];
		}
		
		$sql = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_CmpCallCard_del(
				CmpCallCard_id := :CmpCallCard_id,
				pmUser_id := :pmUser_id
			)
		";
		
		$sqlArr = array(
			'CmpCallCard_id' => $data['CmpCallCard_id'],
			'pmUser_id' => $data['pmUser_id']
		);
		
		$result = $this->db->query( $sql, $sqlArr );
		if ( is_object( $result ) ) {
			return $result->result('array');
		} else {
			return array( array( 'Error_Msg' => 'Во время удаления карты вызова произошла ошибка. При повторении ошибки обратитесь к администратору.' ) );
		}
	}

	/**
	 * Возвращает данные для печати карты закрытия вызова 110у
	 *
	 * @params array $data
	 * @return array or false
	 */
	public function printCmpCloseCard110( $data ){
		$sql = "
			SELECT
				CLC.CmpCallCard_id as \"CmpCallCard_id\",
				CLC.CmpCloseCard_id as \"CmpCloseCard_id\",
				CLC.Day_num as \"Day_num\",
				CLC.Year_num as \"Year_num\",
				to_char(CC.CmpCallCard_insDT, 'dd.mm.yyyy') as \"CallCardDate\",
				CLC.Feldsher_id as \"Feldsher_id\",
				CASE WHEN coalesce(CLC.LpuBuilding_id,0) > 0
					THEN LB.LpuBuilding_Name
					ELSE CLC.StationNum
				END as \"StationNum\",
				COALESCE(CLC.EmergencyTeamNum, EMT.EmergencyTeam_Num, null) as \"EmergencyTeamNum\",
				to_char(CLC.AcceptTime, 'HH24:MI') as \"AcceptTime\",
				to_char(CLC.AcceptTime, 'dd.mm.yyyy') as \"AcceptDate\",
				to_char(CLC.TransTime, 'HH24:MI') as \"TransTime\",
				to_char(CLC.GoTime, 'HH24:MI') as \"GoTime\",
				to_char(CLC.ArriveTime, 'HH24:MI') as \"ArriveTime\",
				to_char(CLC.TransportTime, 'HH24:MI') as \"TransportTime\",
				to_char(CLC.ToHospitalTime, 'HH24:MI') as \"ToHospitalTime\",
				to_char(CLC.EndTime, 'HH24:MI') as \"EndTime\",
				to_char(CLC.BackTime, 'HH24:MI') as \"BackTime\",
				CLC.SummTime as \"SummTime\",
				COALESCE( CLC.Person_PolisSer, CC.Person_PolisSer, PS.Polis_Ser, null) as \"Person_PolisSer\",
				COALESCE( CLC.Person_PolisNum, CC.Person_PolisNum, PS.Polis_Num, null) as \"Person_PolisNum\",
				CLC.Area_id as \"Area_id\",
				KL_AR.KLArea_Name as \"Area\",
				CLC.City_id as \"City_id\",
				KL_CITY.KLArea_Name as \"City\",
				CLC.Town_id as \"Town_id\",
				KL_TOWN.KLArea_Name as \"Town\",
				CLC.Street_id as \"Street_id\",
				CASE WHEN coalesce(CLC.Street_id,0) > 0
					THEN KL_ST.KLStreet_Name
					ELSE ClC.CmpCloseCard_Street
				END as \"Street\",
				case when SecondStreet.KLStreet_FullName is not null then
					case when socrSecondStreet.KLSocr_Nick is not null then UPPER(socrSecondStreet.KLSocr_Nick) || '. ' || SecondStreet.KLStreet_Name else
					SecondStreet.KLStreet_FullName end
					else ''
				end as \"secondStreetName\",
				CLC.House as \"House\",
				Lpu.Lpu_name as \"Lpu_name\",
				Lpu.UAddress_Address as \"UAddress_Address\",
				Lpu.Lpu_Phone as \"Lpu_Phone\",
				CLC.Korpus as \"Korpus\",
				CLC.Room as \"Room\",
				CLC.Office as \"Office\",
				CLC.Entrance as \"Entrance\",
				CLC.Level as \"Level\",
				CLC.CodeEntrance as \"CodeEntrance\",
			    CLC.CallPovodNew_id as \"CallPovodNew_id\",
				CLC.CmpCallPlaceType_id as \"CmpCallPlaceType_id\",
				CLC.Fam as \"Fam\",
				CLC.Name as \"Name\",
				CLC.Middle as \"Middle\",
				CLC.Age as \"Age\",
				COALESCE(CLC.Person_Snils,PS.Person_Snils) as \"Person_Snils\",
				SX.Sex_name as \"Sex_name,
				RS.CmpReason_Name as \"\"Reason\",
				CLC.Work as \"Work\",
				CLC.DocumentNum as \"DocumentNum\",
				CLC.Ktov as \"Ktov\",
				COALESCE(CCrT.CmpCallerType_Name,CLC.Ktov) as \"CmpCallerType_Name\",
				CLC.Phone as \"Phone\",
				CLC.FeldsherAccept as \"FeldsherAccept\",
				CLC.FeldsherTrans as \"FeldsherTrans\",
				RTRIM(MPA.Person_Fio) as \"FeldsherAcceptName\",
				RTRIM(MPT.Person_Fio) as \"FeldsherTransName\",
			    MPA.MedPersonal_TabCode as \"FeldsherAccept_TabCode \",
				CLC.CallType_id as \"CallType_id\",
				CCT.CmpCallType_Name as \"CallType\",
				CCT.CmpCallType_Code as \"CmpCallType_Code\",
				COALESCE(CLC.isAlco, 1) as \"isAlco\",
				CLC.CmpCloseCard_IsSignList as \"CmpCloseCard_IsSignList\",
				CLC.Complaints as \"Complaints\",
				CLC.Anamnez as \"Anamnez\",
				CASE WHEN coalesce(CLC.isMenen,1) = 2 THEN 'Да' ELSE 'Нет' END as \"isMenen\",
				CASE WHEN coalesce(CLC.isNist,1) = 2 THEN 'Да' ELSE 'Нет' END as \"isNist\",
				CASE WHEN coalesce(CLC.isAnis,1) = 2 THEN 'Да' ELSE 'Нет' END as \"isAnis\",
				CASE WHEN coalesce(CLC.isLight,1) = 2 THEN 'Да' ELSE 'Нет' END as \"isLight\",
				CASE WHEN coalesce(CLC.isAcro,1) = 2 THEN 'Да' ELSE 'Нет' END as \"isAcro\",
				CASE WHEN coalesce(CLC.isMramor,1) = 2 THEN 'Да' ELSE 'Нет' END as \"isMramor\",
				COALESCE(CLC.isHale,1) as \"isHale\",
				COALESCE(CLC.isPerit,1) as \"isPerit\",
				CASE WHEN coalesce(CLC.isSogl,1) = 2 THEN 'Да' ELSE CASE WHEN coalesce(CLC.isSogl,2) = 1 THEN 'Нет' ELSE '' END END as \"isSogl\",
				CASE WHEN coalesce(CLC.isOtkazMed,1) = 2 THEN 'Да' ELSE CASE WHEN coalesce(CLC.isOtkazMed,2) = 1 THEN 'Нет' ELSE '' END END as \"isOtkazMed\",
				CASE WHEN coalesce(CLC.isOtkazHosp,1) = 2 THEN 'Да' ELSE CASE WHEN coalesce(CLC.isOtkazHosp,2) = 1 THEN 'Нет' ELSE '' END END as \"isOtkazHosp\",
				CLC.Urine as \"Urine\",
				CLC.Shit as \"Shit\",
				CLC.OtherSympt as \"OtherSympt\",
				CLC.CmpCloseCard_AddInfo as \"CmpCloseCard_AddInfo\",
				CLC.WorkAD as \"WorkAD\",
				CLC.AD as \"AD\",
				CLC.Chss as \"Chss\",
				CLC.Pulse as \"Pulse\",
				CLC.Temperature as \"Temperature\",
				CLC.Chd as \"Chd\",
				CLC.Pulsks as \"Pulsks\",
				CLC.Gluck as \"Gluck\",
				CLC.LocalStatus as \"LocalStatus\",
				CLC.Ekg1 as \"Ekg1\",
				to_char(CLC.Ekg1Time, 'HH24:MI') as \"Ekg1Time\",
				CLC.Ekg2 as \"Ekg2\",
				to_char(CLC.Ekg2Time, 'HH24:MI') as \"Ekg2Time\",
				CLC.Diag_id as \"Diag_id\",
				CLC.Diag_uid as \"Diag_uid\",
				DIAG.Diag_FullName as \"Diag\",
				DIAG.Diag_Code as \"CodeDiag\",
				UDIAG.Diag_FullName as \"uDiag\",
				UDIAG.Diag_Code as \"uCodeDiag\",
				CLC.HelpPlace as \"HelpPlace\",
				CLC.HelpAuto as \"HelpAuto\",
				CLC.CmpCloseCard_ClinicalEff as \"CmpCloseCard_ClinicalEff\",
				CLC.EfAD as \"EfAD\",
				CLC.EfChss as \"EfChss\",
				CLC.EfPulse as \"EfPulse\",
				CLC.EfTemperature as \"EfTemperature\",
				CLC.EfChd as \"EfChd\",
				CLC.EfPulsks as \"EfPulsks\",
				CLC.EfGluck as \"EfGluck\",
				CLC.Kilo as \"Kilo\",
				CLC.DescText as \"DescText\",
				CLC.CmpCloseCard_Epid as \"CmpCloseCard_Epid\",
				CLC.CmpCloseCard_Glaz as \"CmpCloseCard_Glaz\",
				CLC.CmpCloseCard_GlazAfter as \"CmpCloseCard_GlazAfter\",
				CLC.CmpCloseCard_m1 as \"CmpCloseCard_m1\",
				CLC.CmpCloseCard_e1 as \"CmpCloseCard_e1\",
				CLC.CmpCloseCard_v1 as \"CmpCloseCard_v1\",
				CLC.CmpCloseCard_m2 as \"CmpCloseCard_m2\",
				CLC.CmpCloseCard_e2 as \"CmpCloseCard_e2\",
				CLC.CmpCloseCard_v2 as \"CmpCloseCard_v2\",
				CLC.CmpCloseCard_Topic as \"CmpCloseCard_Topic\",
				CC.CmpTrauma_id as \"CmpTrauma_id\"
			FROM
				{$this->schema}.v_CmpCloseCard CLC
				LEFT JOIN v_Sex SX on SX.Sex_id = CLC.Sex_id
				LEFT JOIN v_MedPersonal MPA on MPA.MedPersonal_id = CLC.FeldsherAccept
				LEFT JOIN v_MedPersonal MPT on MPT.MedPersonal_id = CLC.FeldsherTrans
				LEFT JOIN v_PersonState PS on PS.Person_id = CLC.Person_id
				LEFT JOIN v_CmpReason RS on RS.CmpReason_id = CLC.CallPovod_id
				LEFT JOIN KLStreet KL_ST on KL_ST.KLStreet_id = CLC.Street_id
				LEFT JOIN v_KLStreet SecondStreet on SecondStreet.KLStreet_id = CLC.CmpCloseCard_UlicSecond
				LEFT JOIN v_KLSocr socrSecondStreet on SecondStreet.KLSocr_id = socrSecondStreet.KLSocr_id
				LEFT JOIN KLArea KL_AR on KL_AR.KLArea_id = CLC.Area_id
				LEFT JOIN KLArea KL_CITY on KL_CITY.KLArea_id = CLC.City_id
				LEFT JOIN KLArea KL_TOWN on KL_TOWN.KLArea_id = CLC.Town_id
				LEFT JOIN v_CmpCallType CCT on CCT.CmpCallType_id = CLC.CallType_id
				LEFT JOIN v_CmpCallerType CCrT on CCrT.CmpCallerType_id = CLC.CmpCallerType_id
				LEFT JOIN v_Diag DIAG on DIAG.Diag_id = CLC.Diag_id
				LEFT JOIN v_Diag UDIAG on UDIAG.Diag_id = CLC.Diag_uid
				LEFT JOIN v_CmpCallCard CC on CC.CmpCallCard_id = CLC.CmpCallCard_id
				LEFT JOIN v_EmergencyTeam EMT on EMT.EmergencyTeam_id = CC.EmergencyTeam_id
				LEFT JOIN v_Lpu Lpu on Lpu.Lpu_id = CC.Lpu_id
				LEFT JOIN v_LpuBuilding LB on LB.LpuBuilding_id = CLC.LpuBuilding_id
			WHERE
				CLC.CmpCallCard_id = :CmpCallCard_id
			limit 1
		";

		$query = $this->db->query( $sql, array(
			'CmpCallCard_id' => $data[ 'CmpCallCard_id' ]
		) );
		if ( is_object( $query ) ) {
			return $query->result_array();
		}

		return false;
	}

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
