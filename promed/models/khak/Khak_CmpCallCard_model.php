<?php	defined('BASEPATH') or die ('No direct script access allowed');

require(APPPATH.'models/CmpCallCard_model.php');

class Khak_CmpCallCard_model extends CmpCallCard_model {

	/**
	 * Возвращает данные для печати карты закрытия вызова 110у
	 *
	 * @params array $data
	 * @return array or false
	 */
	public function printCmpCloseCard110( $data ){
		$sql = "
			SELECT TOP 1
				CLC.CmpCallCard_id,
				CLC.CmpCloseCard_id,
				--CC.CmpCallCard_Numv as Day_num,
				--CC.CmpCallCard_Ngod as Year_num,
				CLC.Day_num,
				CLC.Year_num,
				--convert(varchar, CLC.CmpCloseCard_insDT, 104) as CardDate,
				convert(varchar, CC.CmpCallCard_insDT, 104) as CallCardDate,
				CLC.Feldsher_id,
				CASE WHEN ISNULL(CLC.LpuBuilding_id,0) > 0 THEN LB.LpuBuilding_Name ELSE CLC.StationNum END as StationNum,
				COALESCE(CLC.EmergencyTeamNum, EMT.EmergencyTeam_Num, null) as EmergencyTeamNum,
				convert(varchar(5), CLC.AcceptTime, 108) as AcceptTime,
				convert(varchar, CLC.AcceptTime, 104) as AcceptDate,
				convert(varchar(5), CLC.TransTime, 108) as TransTime,
				convert(varchar(5), CLC.GoTime, 108) as GoTime,
				convert(varchar(5), CLC.ArriveTime, 108) as ArriveTime,
				convert(varchar(5), CLC.TransportTime, 108) as TransportTime,
				convert(varchar(5), CLC.ToHospitalTime, 108) as ToHospitalTime,
				convert(varchar(5), CLC.EndTime, 108) as EndTime,
				convert(varchar(5), CLC.BackTime, 108) as BackTime,
				CLC.SummTime,
				COALESCE( CLC.Person_PolisSer, CC.Person_PolisSer, PS.Polis_Ser, null) as Person_PolisSer,
				COALESCE( CLC.Person_PolisNum, CC.Person_PolisNum, PS.Polis_Num, null) as Person_PolisNum,
				CLC.Area_id,
				KL_AR.KLArea_Name as Area,
				CLC.City_id,
				KL_CITY.KLArea_Name as City,
				CLC.Town_id,
				KL_TOWN.KLArea_Name as Town,
				CLC.Street_id,

				CASE WHEN ISNULL(CLC.Street_id,0) > 0 THEN KL_ST.KLStreet_Name ELSE ClC.CmpCloseCard_Street END as Street,

				case when SecondStreet.KLStreet_FullName is not null then
					case when socrSecondStreet.KLSocr_Nick is not null then UPPER(socrSecondStreet.KLSocr_Nick)+'. '+SecondStreet.KLStreet_Name else
					SecondStreet.KLStreet_FullName end
					else ''
				end as secondStreetName,

				CLC.House,
				Lpu.Lpu_name,
				Lpu.UAddress_Address,
				Lpu.Lpu_Phone,
				CLC.Korpus,
				CLC.Room,
				CLC.Office,
				CLC.Entrance,
				CLC.Level,
				CLC.CodeEntrance,

				CLC.Fam,
				CLC.Name,
				CLC.Middle,
				CLC.Age,
				COALESCE(CLC.Person_Snils,PS.Person_Snils) as Person_Snils,
				SX.Sex_name,
				RS.CmpReason_Name as Reason,
			
				CLC.Work,
				CLC.DocumentNum,
				CLC.Ktov,
				COALESCE(CCrT.CmpCallerType_Name,CLC.Ktov) as CmpCallerType_Name,
				CLC.Phone,

				CLC.FeldsherAccept,
				CLC.FeldsherTrans,

				--RTRIM(PMCA.PMUser_Name) as FeldsherAcceptName,
				RTRIM(MPA.Person_Fio) as FeldsherAcceptName,
				--RTRIM(PMCT.PMUser_Name) as FeldsherTransName,
				RTRIM(MPT.Person_Fio) as FeldsherTransName,

				CLC.CallType_id,
				CCT.CmpCallType_Name as CallType,
				CCT.CmpCallType_Code,
				COALESCE(CLC.isAlco,1) as isAlco,
				CLC.CmpCloseCard_IsSignList,
				CLC.Complaints,
				CLC.Anamnez,
				CASE WHEN ISNULL(CLC.isMenen,1) = 2 THEN 'Да' ELSE 'Нет' END as isMenen,
				CASE WHEN ISNULL(CLC.isNist,1) = 2 THEN 'Да' ELSE 'Нет' END as isNist,
				CASE WHEN ISNULL(CLC.isAnis,1) = 2 THEN 'Да' ELSE 'Нет' END as isAnis,
				CASE WHEN ISNULL(CLC.isLight,1) = 2 THEN 'Да' ELSE 'Нет' END as isLight,
				CASE WHEN ISNULL(CLC.isAcro,1) = 2 THEN 'Да' ELSE 'Нет' END as isAcro,
				CASE WHEN ISNULL(CLC.isMramor,1) = 2 THEN 'Да' ELSE 'Нет' END as isMramor,
				
				COALESCE(CLC.isHale,1) as isHale,
				COALESCE(CLC.isPerit,1) as isPerit,

				CASE WHEN ISNULL(CLC.isSogl,1) = 2 THEN 'Да' ELSE CASE WHEN ISNULL(CLC.isSogl,2) = 1 THEN 'Нет' ELSE '' END END as isSogl,
				CASE WHEN ISNULL(CLC.isOtkazMed,1) = 2 THEN 'Да' ELSE CASE WHEN ISNULL(CLC.isOtkazMed,2) = 1 THEN 'Нет' ELSE '' END END as isOtkazMed,
				CASE WHEN ISNULL(CLC.isOtkazHosp,1) = 2 THEN 'Да' ELSE CASE WHEN ISNULL(CLC.isOtkazHosp,2) = 1 THEN 'Нет' ELSE '' END END as isOtkazHosp,

				CLC.Urine,
				CLC.Shit,
				CLC.OtherSympt,
				CLC.CmpCloseCard_AddInfo,
				CLC.WorkAD,
				CLC.AD,
				CLC.Chss,
				CLC.Pulse,
				CLC.Temperature,
				CLC.Chd,
				CLC.Pulsks,
				CLC.Gluck,
				CLC.LocalStatus,
				CLC.Ekg1,
				convert(varchar(5), CLC.Ekg1Time, 108) as Ekg1Time,
				CLC.Ekg2,
				convert(varchar(5), CLC.Ekg2Time, 108) as Ekg2Time,
				CLC.Diag_id,
				CLC.Diag_uid,
				DIAG.Diag_FullName as Diag,
				DIAG.Diag_Code as CodeDiag,
				UDIAG.Diag_FullName as uDiag,
				UDIAG.Diag_Code as uCodeDiag,
				CLC.HelpPlace,
				CLC.HelpAuto,
				CLC.CmpCloseCard_ClinicalEff,
				CLC.EfAD,
				CLC.EfChss,
				CLC.EfPulse,
				CLC.EfTemperature,
				CLC.EfChd,
				CLC.EfPulsks,
				CLC.EfGluck,
				CLC.Kilo,
				CLC.DescText,
				CLC.CmpCloseCard_Epid,
				CLC.CmpCloseCard_Glaz,
				CLC.CmpCloseCard_GlazAfter,
				CLC.CmpCloseCard_m1,
				CLC.CmpCloseCard_e1,
				CLC.CmpCloseCard_v1,
				CLC.CmpCloseCard_m2,
				CLC.CmpCloseCard_e2,
				CLC.CmpCloseCard_v2,
				CLC.CmpCloseCard_Topic,
				CC.CmpTrauma_id
			FROM
				{$this->schema}.v_CmpCloseCard CLC with (nolock)
				LEFT JOIN v_Sex SX with (nolock) on SX.Sex_id = CLC.Sex_id
				--LEFT JOIN v_pmUserCache PMCA with (nolock) on PMCA.PMUser_id = CLC.FeldsherAccept
				LEFT JOIN v_MedPersonal MPA with (nolock) on MPA.MedPersonal_id = CLC.FeldsherAccept
				--LEFT JOIN v_pmUserCache PMCT with (nolock) on PMCT.PMUser_id = CLC.FeldsherTrans
				LEFT JOIN v_MedPersonal MPT with (nolock) on MPT.MedPersonal_id = CLC.FeldsherTrans
				LEFT JOIN v_PersonState PS (nolock) on PS.Person_id = CLC.Person_id
				LEFT JOIN v_CmpReason RS with (nolock) on RS.CmpReason_id = CLC.CallPovod_id
				LEFT JOIN KLStreet KL_ST with (nolock) on KL_ST.KLStreet_id = CLC.Street_id
				LEFT JOIN v_KLStreet SecondStreet (nolock) on SecondStreet.KLStreet_id = CLC.CmpCloseCard_UlicSecond
				LEFT JOIN v_KLSocr socrSecondStreet with (nolock) on SecondStreet.KLSocr_id = socrSecondStreet.KLSocr_id
				LEFT JOIN KLArea KL_AR with (nolock) on KL_AR.KLArea_id = CLC.Area_id
				LEFT JOIN KLArea KL_CITY with (nolock) on KL_CITY.KLArea_id = CLC.City_id
				LEFT JOIN KLArea KL_TOWN with (nolock) on KL_TOWN.KLArea_id = CLC.Town_id
				LEFT JOIN v_CmpCallType CCT with (nolock) on CCT.CmpCallType_id = CLC.CallType_id
				LEFT JOIN v_CmpCallerType CCrT with (nolock) on CCrT.CmpCallerType_id = CLC.CmpCallerType_id
				LEFT JOIN v_Diag DIAG (nolock) on DIAG.Diag_id = CLC.Diag_id
				LEFT JOIN v_Diag UDIAG (nolock) on UDIAG.Diag_id = CLC.Diag_uid
				LEFT JOIN v_CmpCallCard CC (nolock) on CC.CmpCallCard_id = CLC.CmpCallCard_id
				LEFT JOIN v_EmergencyTeam EMT with (nolock) on EMT.EmergencyTeam_id = CC.EmergencyTeam_id
				LEFT JOIN v_Lpu Lpu (nolock) on Lpu.Lpu_id = CC.Lpu_id
				LEFT JOIN v_LpuBuilding LB (nolock) on LB.LpuBuilding_id = CLC.LpuBuilding_id
			WHERE
				CLC.CmpCallCard_id = :CmpCallCard_id
		";

		$query = $this->db->query( $sql, array(
			'CmpCallCard_id' => $data[ 'CmpCallCard_id' ]
		) );
		if ( is_object( $query ) ) {
			return $query->result_array();
		}

		return false;
	}

}
