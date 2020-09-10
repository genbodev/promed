<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Krym_CmpCallCardModel - модель для работы с картами вызова СМП. Версия для Крыма
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package			Common
* @access			public
* @copyright		Copyright (c) 2013 Swan Ltd.
* @author			Sobenin Alexander
* @version			krym
*/

require_once(APPPATH.'models/CmpCallCard_model.php');

class Krym_CmpCallCard_model extends CmpCallCard_model {

	/**
	 * default шапка для печати
	 */
	function printCmpCallCardHeader($data) {
		$query = "
			select top 1
				CCC.CmpCallCard_id
				,CCC.CmpCallCard_Numv as Day_num
				,CCC.CmpCallCard_Ngod as Year_num
				,convert(varchar, CCC.CmpCallCard_insDT, 104) as CallCardDate
				,LB.LpuBuilding_Name
				,EMT.EmergencyTeam_Num

				,CASE WHEN CCC.CmpCallCard_Dsp1 IS NOT NULL THEN CCC.CmpCallCard_Dsp1 ELSE '' END as Dsp_prm
				,CASE WHEN CCC.CmpCallCard_Dsp2 IS NOT NULL THEN CCC.CmpCallCard_Dsp2 ELSE '' END as Dsp_per

				,convert(varchar(5), CCC.CmpCallCard_prmDT, 108) as AcceptTime
				,'' as TransportTime
				,'' as GoTime
				,'' as ArriveTime
				,'' as EndTime
				,'' as BackTime
				,convert(varchar(5), CCC.CmpCallCard_Tisp, 108) as IspTime
				,CASE WHEN ((CCC.CmpCallCard_prmDT IS NOT NULL) AND (CCC.CmpCallCard_Tisp IS NOT NULL)) THEN DATEDIFF(mm,CCC.CmpCallCard_prmDT,CCC.CmpCallCard_Tisp) ELSE '' END as Dlit

				,convert(varchar(5), CCC.CmpCallCard_Tper, 108) as PerTime
				,CASE WHEN MP.Person_Fin IS NOT NULL THEN MP.Person_Fin ELSE CCC.CmpCallCard_Dokt END as Doc


				,DATEDIFF(mm,CCC.CmpCallCard_prmDT,CCC.CmpCallCard_Tisp)


				,KL_AR.KLArea_Name as Area
				,CCC.KLCity_id
				,KL_CITY.KLCity_Name as City
				,CCC.KLTown_id
				,KL_TOWN.KLTown_Name as Town
				,CCC.KLStreet_id
				,KL_ST.KLStreet_Name as Street

				,CCC.CmpCallCard_Dom as House
				,Lpu.Lpu_Nick as Lpu_name
				,Lpu.UAddress_Address
				,Lpu.Lpu_Phone

				,case when KL_CITY.KLCity_Name is not null then 'г. '+KL_CITY.KLCity_Name else '' end +
				case when KL_TOWN.KLTown_FullName is not null then
					case when (KL_CITY.KLCity_Name is not null) then ', '+LOWER(KL_TOWN.KLSocr_Nick)+'. '+KL_TOWN.KLTown_Name else LOWER(KL_TOWN.KLSocr_Nick)+'. '+KL_TOWN.KLTown_Name end
				else '' end +
				case when KL_ST.KLStreet_Name is not null then ',<br> '+LOWER(socrStreet.KLSocr_Nick)+'. '+KL_ST.KLStreet_Name  
				else case when CCC.CmpCallCard_Ulic is not null then ', '+LOWER(CCC.CmpCallCard_Ulic)+'. ' else '' end
					end +

				case when SecondStreet.KLStreet_FullName is not null then
					case when socrSecondStreet.KLSocr_Nick is not null then ', '+LOWER(socrSecondStreet.KLSocr_Nick)+'. '+SecondStreet.KLStreet_Name else
					', '+SecondStreet.KLStreet_FullName end
					else ''
				end +

				case when CCC.CmpCallCard_Dom is not null then ', д.'+CCC.CmpCallCard_Dom else '' end +
				case when CCC.CmpCallCard_Korp is not null then ', к.'+CCC.CmpCallCard_Korp else '' end +
				case when CCC.CmpCallCard_Kvar is not null then ', кв.'+CCC.CmpCallCard_Kvar else '' end +
				case when CCC.CmpCallCard_Kodp is not null then '</br>Код подъезда: '+CCC.CmpCallCard_Kodp else '' end +
				case when CCC.CmpCallCard_Comm is not null then '</br>'+CCC.CmpCallCard_Comm else '' end as Address_Address


				,CCC.CmpCallCard_Korp as Korpus
				,CCC.CmpCallCard_Kvar as Office
				,CASE WHEN ISNULL(CCC.CmpCallCard_Podz,0) = 0 THEN '' ELSE CCC.CmpCallCard_Podz END as Entrance
				,CASE WHEN ISNULL(CCC.CmpCallCard_Etaj,0) = 0 THEN '' ELSE CCC.CmpCallCard_Etaj END as Level
				,CCC.CmpCallCard_Kodp as CodeEntrance

				,case when CCC.Person_SurName is not null then CCC.Person_SurName + '<br>' else '' end +
				case when CCC.Person_FirName is not null then CCC.Person_FirName + '<br>' else '' end +
				case when CCC.Person_SecName is not null then CCC.Person_SecName + '<br>' else '' end as FIO
				,CCC.Person_Age as Age
				,case when
					DATEDIFF(yy,ISNULL(PS.Person_BirthDay, ISNULL(CCC.Person_BirthDay,'01.01.2000')),dbo.tzGetDate())>1
				then
					'лет'
				else
					case when
						DATEDIFF(mm,ISNULL(PS.Person_BirthDay, CCC.Person_BirthDay),dbo.tzGetDate())>1
					then
						'мес'
					else
						'дн'
					end
				end as AgeTypeValue
				,case when
					DATEDIFF(yy,ISNULL(PS.Person_BirthDay, ISNULL(CCC.Person_BirthDay,'01.01.2000')),dbo.tzGetDate())>1
				then
					case when
						ISNULL(PS.Person_BirthDay, ISNULL(CCC.Person_BirthDay,0))=0
					then
						''
					else
						DATEDIFF(yy,ISNULL(PS.Person_BirthDay, CCC.Person_BirthDay),dbo.tzGetDate())
					end
				else
					case when
						DATEDIFF(mm,ISNULL(PS.Person_BirthDay, CCC.Person_BirthDay),dbo.tzGetDate())>1
					then
						DATEDIFF(mm,ISNULL(PS.Person_BirthDay, CCC.Person_BirthDay),dbo.tzGetDate())
					else
						DATEDIFF(dd,ISNULL(PS.Person_BirthDay, CCC.Person_BirthDay),dbo.tzGetDate())
					end
				end as AgePS

				,SX.Sex_name
				,RS.CmpReason_Name as Reason

				--,SS.SocStatus_Name as C_PersonSocial_id

				-- ,CCC.CmpCallCard_Ktov
				,CCC.CmpCallCard_Telf as Phone

				,CCC.CmpCallType_id
				,CCC.CmpCallCard_Comm
				,CCT.CmpCallType_Code
				, CASE WHEN CCRT.CmpCallerType_Name IS NOT NULL THEN CCRT.CmpCallerType_Name ELSE CCC.CmpCallCard_Ktov END as CmpCallerType_Name
				-- ,CCT.CmpCallType_Name as CallType
				,CCC.CmpCallCard_IsReceivedInPPD

			from
				v_CmpCallCard CCC with (nolock)
				LEFT JOIN Sex SX with (nolock) on SX.Sex_id = CCC.Sex_id
				left join v_PersonState PS (nolock) on PS.Person_id = CCC.Person_id
				LEFT JOIN v_CmpReason RS with (nolock) on RS.CmpReason_id = CCC.CmpReason_id
				LEFT JOIN KLStreet KL_ST with (nolock) on KL_ST.KLStreet_id = CCC.KLStreet_id
				LEFT JOIN KLArea KL_AR with (nolock) on KL_AR.KLArea_id = CCC.KLSubRgn_id
				left join v_KLSocr socrStreet with (nolock) on KL_ST.KLSocr_id = socrStreet.KLSocr_id
				left join v_KLStreet SecondStreet (nolock) on SecondStreet.KLStreet_id = CCC.CmpCallCard_UlicSecond
				left join v_KLSocr socrSecondStreet with (nolock) on SecondStreet.KLSocr_id = socrSecondStreet.KLSocr_id
				LEFT JOIN v_KLCity KL_CITY with (nolock) on KL_CITY.KLCity_id = CCC.KLCity_id
				LEFT JOIN v_KLTown KL_TOWN with (nolock) on KL_TOWN.KLTown_id = CCC.KLTown_id
				LEFT JOIN v_CmpCallType CCT with (nolock) on CCT.CmpCallType_id = CCC.CmpCallType_id
				LEFT JOIN v_CmpCallerType CCRT with (nolock) on CCRT.CmpCallerType_id = CCC.CmpCallerType_id
				left join v_Lpu Lpu (nolock) on Lpu.Lpu_id = CCC.Lpu_id
				LEFT JOIN v_EmergencyTeam EMT with (nolock) on EMT.EmergencyTeam_id = CCC.EmergencyTeam_id
				LEFT JOIN v_MedPersonal MP with (nolock) on MP.MedPersonal_id = EMT.EmergencyTeam_HeadShift
				LEFT JOIN v_LpuBuilding LB (nolock) on LB.LpuBuilding_id = CCC.LpuBuilding_id
				-- left join v_SocStatus SS (nolock) on SS.SocStatus_id = CLC.SocStatus_id
			where
				CCC.CmpCallCard_id = :CmpCallCard_id
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
	 * Печать всей формы
	 */
	function printCmpCallCard($data) {
		if ( !empty( $data[ 'CmpCallCard_id' ] ) ) {
			$where[] = "CCC.CmpCallCard_id = :CmpCallCard_id";
			$params[ 'CmpCallCard_id' ] = $data[ 'CmpCallCard_id' ];
		} elseif ( !empty( $data[ 'CmpCloseCard_id' ] ) ) {
			$where[] = "CClC.CmpCloseCard_id = :CmpCloseCard_id";
			$params[ 'CmpCloseCard_id' ] = $data[ 'CmpCloseCard_id' ];
		}

		$sql = "
			SELECT TOP 1
				CClC.CmpCallCard_id,
				--CClC.CmpCallCard_IsAlco as isAlco,
				CClC.isSogl,
				CClC.CmpCloseCard_id,
				CCC.CmpReason_id,
				CClC.PayType_id,
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
				CCC.Person_id,
				CClC.Ktov,
				CClC.CmpCallerType_id,
				CClC.CmpCloseCard_IsExtra,
				CClC.CmpCloseCard_ClinicalEff,
				CCC.KLRgn_id,
				CCC.KLSubRgn_id as Area_id,
				CCC.KLCity_id as City_id,
				CCC.KLTown_id as Town_id,
				CCC.CmpCallPlaceType_id,
				CCC.KLStreet_id  as Street_id,
				CClC.Room,
				CClC.Korpus as Korpus,

				CClC.EmergencyTeamNum as EmergencyTeamNum,
				CCLC.EmergencyTeam_id as EmergencyTeam_id,
				CClC.StationNum as StationNum,
				CClC.LpuBuilding_id,
				LB.LpuBuilding_Code,
				CClC.pmUser_insID as Feldsher_id,
				--CClC.pmUser_insID as FeldsherAccept,
				CCLC.FeldsherAccept,
				CClC.FeldsherTrans,
				
				convert(varchar(5), cast(CClC.CmpCloseCard_BegTreatDT as datetime), 108) as CmpCloseCard_BegTreatDT,
				convert(varchar(5), cast(CClC.CmpCloseCard_EndTreatDT as datetime), 108) as CmpCloseCard_EndTreatDT,

				convert(varchar(10), CClC.AcceptTime, 104)+' '+convert(varchar(5), cast(CClC.AcceptTime as datetime), 108) as AcceptTime,
				convert(varchar(10), CClC.TransTime, 104)+' '+convert(varchar(5), cast(CClC.TransTime as datetime), 108) as TransTime,
				convert(varchar(10), CClC.GoTime, 104)+' '+convert(varchar(5), cast(CClC.GoTime as datetime), 108) as GoTime,

				convert(varchar(10), CClC.ArriveTime, 104)+' '+convert(varchar(5), cast(CClC.ArriveTime as datetime), 108) as ArriveTime,
				convert(varchar(10), CClC.TransportTime, 104)+' '+convert(varchar(5), cast(CClC.TransportTime as datetime), 108) as TransportTime,
				convert(varchar(10), CClC.ToHospitalTime, 104)+' '+convert(varchar(5), cast(CClC.ToHospitalTime as datetime), 108) as ToHospitalTime,
				convert(varchar(10), CClC.EndTime, 104)+' '+convert(varchar(5), cast(CClC.EndTime as datetime), 108) as EndTime,
				convert(varchar(10), CClC.BackTime, 104)+' '+convert(varchar(5), cast(CClC.BackTime as datetime), 108) as BackTime,
				convert(varchar(10), CClC.CmpCloseCard_TranspEndDT, 104)+' '+convert(varchar(5), cast(CClC.CmpCloseCard_TranspEndDT as datetime), 108) as TranspEndDT,

				CClC.SummTime,
				CClC.Work,
				CClC.DocumentNum,
				CClC.CallType_id,
				CClC.CallPovod_id,
				CASE WHEN ISNULL(CClC.isAlco,0) = 0 THEN NULL ELSE CClC.isAlco END as isAlco,
				CClC.Complaints,
				CClC.Anamnez,
				CASE WHEN ISNULL(CClC.isMenen,0) = 0 THEN NULL ELSE CClC.isMenen END as isMenen,
				CASE WHEN ISNULL(CClC.isAnis,0) = 0 THEN NULL ELSE CClC.isAnis END as isAnis,
				CASE WHEN ISNULL(CClC.isNist,0) = 0 THEN NULL ELSE CClC.isNist END as isNist,
				CASE WHEN ISNULL(CClC.isLight,0) = 0 THEN NULL ELSE CClC.isLight END as isLight,
				CASE WHEN ISNULL(CClC.isAcro,0) = 0 THEN NULL ELSE CClC.isAcro END as isAcro,
				CASE WHEN ISNULL(CClC.isMramor,0) = 0 THEN NULL ELSE CClC.isMramor END as isMramor,
				CASE WHEN ISNULL(CClC.isHale,0) = 0 THEN NULL ELSE CClC.isHale END as isHale,
				CASE WHEN ISNULL(CClC.isPerit,0) = 0 THEN NULL ELSE CClC.isPerit END as isPerit,
				CClC.Urine,
				CClC.Shit,
				CClC.OtherSympt,
				CClC.CmpCloseCard_AddInfo,
				CClC.WorkAD,
				CClC.AD,
				CASE WHEN COALESCE(CClC.Pulse,0)=0 THEN NULL ELSE CClC.Pulse END as Pulse,
				CASE WHEN COALESCE(CClC.Chss,0)=0 THEN NULL ELSE CClC.Chss END as Chss,
				CASE WHEN COALESCE(CClC.Chd,0)=0 THEN NULL ELSE CClC.Chd END as Chd,
				CClC.Temperature,
				CClC.Pulsks,
				CClC.Gluck,
				CClC.LocalStatus,
				convert(varchar(5), cast(CClC.Ekg1Time as datetime), 108) as Ekg1Time,
				CClC.Ekg1,
				convert(varchar(5), cast(CClC.Ekg2Time as datetime), 108) as Ekg2Time,
				CClC.Ekg2,
				CClC.Diag_id,
				CClC.Diag_sid,
				CClC.EfAD,
				CASE WHEN ISNULL(CClC.EfChss,0) = 0 THEN NULL ELSE CClC.EfChss END as EfChss,
				CASE WHEN ISNULL(CClC.EfPulse,0) = 0 THEN NULL ELSE CClC.EfPulse END as EfPulse,
				CClC.EfTemperature,
				CASE WHEN ISNULL(CClC.EfChd,0) = 0 THEN NULL ELSE CClC.EfChd END as EfChd,
				CClC.EfPulsks,
				CClC.EfGluck,
				CClC.Kilo,
				CClC.Lpu_id,
				CClC.HelpPlace,
				CClC.HelpAuto,
				CClC.DescText,
				CClC.CmpCloseCard_Epid,
				CClC.CmpCloseCard_Glaz,
				CClC.CmpCloseCard_GlazAfter,
				CClC.CmpCloseCard_m1,
				CClC.CmpCloseCard_e1,
				CClC.CmpCloseCard_v1,
				CClC.CmpCloseCard_m2,
				CClC.CmpCloseCard_e2,
				CClC.CmpCloseCard_v2,
				CClC.CmpCloseCard_Topic,
				CClC.CmpCloseCard_IsProfile,
				CClC.CmpCloseCard_IsVomit as IsVomit,
				CClC.CmpCloseCard_IsDiuresis as IsDiuresis,
				CClC.CmpCloseCard_IsDefecation as IsDefecation,
				CClC.CmpCloseCard_IsTrauma as IsTrauma,
				CClC.CmpCloseCard_Sat as Sat,
				CClC.CmpCloseCard_AfterSat as AfterSat,
				convert(varchar(5), cast(CClC.CmpCloseCard_HelpDT as datetime), 108) as HelpDT,
				CClC.HelpAuto,
				CClC.CmpCloseCard_Rhythm,
				CClC.CmpCloseCard_AfterRhythm,
				CClC.DescText,
				UCA.PMUser_Name as FeldsherAcceptName,
				UCT.PMUser_Name as FeldsherTransName


				,convert(varchar, CCC.CmpCallCard_insDT, 104) as CallCardDate
				,LB.LpuBuilding_Name
				,EMT.EmergencyTeam_Num

				,CASE WHEN CCC.CmpCallCard_Dsp1 IS NOT NULL THEN CCC.CmpCallCard_Dsp1 ELSE '' END as Dsp_prm
				,CASE WHEN CCC.CmpCallCard_Dsp2 IS NOT NULL THEN CCC.CmpCallCard_Dsp2 ELSE '' END as Dsp_per

				,convert(varchar(5), CCC.CmpCallCard_Tper, 108) as PerTime
				--,CASE WHEN MP.Person_Fio IS NOT NULL THEN MP.Person_Fio ELSE CCC.MedStaffFact_id END as Doc
				,msf.Person_FIO as Doc
				,MSFCID.Person_FIO as DocCid
				,DATEDIFF(mm,CCC.CmpCallCard_prmDT,CCC.CmpCallCard_Tisp)
				,KL_AR.KLArea_Name as Area
				,CCC.KLCity_id
				,KL_CITY.KLCity_Name as City
				,CCC.KLTown_id
				,KL_TOWN.KLTown_Name as Town
				,CCC.KLStreet_id

				,COALESCE(KL_ST.KLStreet_Name, LOWER(CClC.CmpCloseCard_Street),CCC.CmpCallCard_Ulic, '') as Street

				,case when SecondStreet.KLStreet_FullName is not null then
					case when socrSecondStreet.KLSocr_Nick is not null then UPPER(socrSecondStreet.KLSocr_Nick)+'. '+SecondStreet.KLStreet_Name else
					SecondStreet.KLStreet_FullName end
					else ''
				end as secondStreetName

				,CCC.CmpCallCard_IsReceivedInPPD
				,CCC.CmpCallCard_Dom as House
				,Lpu.Lpu_Nick as Lpu_name
				,Lpu.UAddress_Address
				,Lpu.Lpu_Phone

				,case when KL_CITY.KLCity_Name is not null then 'г. '+KL_CITY.KLCity_Name else '' end +
				case when KL_TOWN.KLTown_FullName is not null then
					case when (KL_CITY.KLCity_Name is not null) then ', '+LOWER(KL_TOWN.KLSocr_Nick)+'. '+KL_TOWN.KLTown_Name else LOWER(KL_TOWN.KLSocr_Nick)+'. '+KL_TOWN.KLTown_Name end
				else '' end +
				case when KL_ST.KLStreet_Name is not null then ',<br> '+LOWER(socrStreet.KLSocr_Nick)+'. '+KL_ST.KLStreet_Name  else '' end +
				case when CCC.CmpCallCard_Dom is not null then ', д.'+CCC.CmpCallCard_Dom else '' end +
				case when CCC.CmpCallCard_Korp is not null then ', к.'+CCC.CmpCallCard_Korp else '' end +
				case when CCC.CmpCallCard_Kvar is not null then ', кв.'+CCC.CmpCallCard_Kvar else '' end +
				case when CCC.CmpCallCard_Kodp is not null then '</br>Код подъезда: '+CCC.CmpCallCard_Kodp else '' end +
				case when CCC.CmpCallCard_Comm is not null then '</br>'+CCC.CmpCallCard_Comm else '' end as Address_Address


				,CCC.CmpCallCard_Korp as Korpus
				,CCC.CmpCallCard_Kvar as Office
				,CASE WHEN ISNULL(CCC.CmpCallCard_Podz,0) = 0 THEN '' ELSE CCC.CmpCallCard_Podz END as Entrance
				,CASE WHEN ISNULL(CCC.CmpCallCard_Etaj,0) = 0 THEN '' ELSE CCC.CmpCallCard_Etaj END as Level
				,CCC.CmpCallCard_Kodp as CodeEntrance

				,case when CCC.Person_SurName is not null then CCC.Person_SurName + '<br>' else '' end +
				case when CCC.Person_FirName is not null then CCC.Person_FirName + '<br>' else '' end +
				case when CCC.Person_SecName is not null then CCC.Person_SecName + '<br>' else '' end as FIO
				,CCC.Person_Age as Age
				,convert(varchar(10), PS.Person_BirthDay, 104) as BirthDay
				,case when
					DATEDIFF(yy,ISNULL(PS.Person_BirthDay, ISNULL(CCC.Person_BirthDay,'01.01.2000')),dbo.tzGetDate())>1
				then
					'лет'
				else
					case when
						DATEDIFF(mm,ISNULL(PS.Person_BirthDay, CCC.Person_BirthDay),dbo.tzGetDate())>1
					then
						'мес'
					else
						'дн'
					end
				end as AgeTypeValue
				,case when
					DATEDIFF(yy,ISNULL(PS.Person_BirthDay, ISNULL(CCC.Person_BirthDay,'01.01.2000')),dbo.tzGetDate())>1
				then
					case when
						ISNULL(PS.Person_BirthDay, ISNULL(CCC.Person_BirthDay,0))=0
					then
						''
					else
						DATEDIFF(yy,ISNULL(PS.Person_BirthDay, CCC.Person_BirthDay),dbo.tzGetDate())
					end
				else
					case when
						DATEDIFF(mm,ISNULL(PS.Person_BirthDay, CCC.Person_BirthDay),dbo.tzGetDate())>1
					then
						DATEDIFF(mm,ISNULL(PS.Person_BirthDay, CCC.Person_BirthDay),dbo.tzGetDate())
					else
						DATEDIFF(dd,ISNULL(PS.Person_BirthDay, CCC.Person_BirthDay),dbo.tzGetDate())
					end
				end as AgePS
				,CClC.Age

				,SX.Sex_name
				,RS.CmpReason_Name as Reason
				,RS.CmpReason_Code as ReasonCode

				--,SS.SocStatus_Name as C_PersonSocial_id

				-- ,CCC.CmpCallCard_Ktov
				,CCC.CmpCallCard_Telf as Phone

				,CCC.CmpCallType_id
				,CCC.CmpCallCard_Comm
				,CCT.CmpCallType_Code
				, CASE WHEN CCRT.CmpCallerType_Name IS NOT NULL THEN CCRT.CmpCallerType_Name ELSE CCC.CmpCallCard_Ktov END as CmpCallerType_Name
				-- ,CCT.CmpCallType_Name as CallType
				,CCC.CmpCallCard_IsReceivedInPPD
				,DIAG.Diag_FullName as main_Diag_Name
				,DIAG.Diag_Code as main_Diag_Code
				,UDIAG.Diag_FullName as s_Diag_Name
				,UDIAG.Diag_Code as s_Diag_Code
			FROM
				{$this->schema}.v_CmpCloseCard CClC with (nolock)
				LEFT JOIN v_CmpCallCard CCC (nolock) on CCC.CmpCallCard_id = CClC.CmpCallCard_id
				LEFT JOIN v_Diag DIAG (nolock) on DIAG.Diag_id = CClC.Diag_id
				LEFT JOIN v_Diag UDIAG (nolock) on UDIAG.Diag_id = CClC.Diag_sid
				LEFT JOIN v_PersonState PS (nolock) on PS.Person_id = CCC.Person_id
				LEFT JOIN v_Lpu L with (nolock) on L.Lpu_id = CClC.Lpu_id
				LEFT JOIN v_EmergencyTeam EMT with (nolock) on EMT.EmergencyTeam_id = CCC.EmergencyTeam_id
				LEFT JOIN v_pmUserCache UCA with (nolock) on UCA.PMUser_id = CClC.pmUser_insID
				LEFT JOIN v_pmUserCache UCT with (nolock) on UCT.PMUser_id = CClC.FeldsherTrans

				LEFT JOIN v_KLStreet SecondStreet (nolock) on SecondStreet.KLStreet_id = CClC.CmpCloseCard_UlicSecond
				LEFT JOIN v_KLSocr socrSecondStreet with (nolock) on SecondStreet.KLSocr_id = socrSecondStreet.KLSocr_id

				LEFT JOIN Sex SX with (nolock) on SX.Sex_id = CCC.Sex_id

				LEFT JOIN v_CmpReason RS with (nolock) on RS.CmpReason_id = CCC.CmpReason_id
				LEFT JOIN KLStreet KL_ST with (nolock) on KL_ST.KLStreet_id = CClC.Street_id
				LEFT JOIN KLArea KL_AR with (nolock) on KL_AR.KLArea_id = CClC.Area_id
				left join v_KLSocr socrStreet with (nolock) on KL_ST.KLSocr_id = socrStreet.KLSocr_id
				LEFT JOIN v_KLCity KL_CITY with (nolock) on KL_CITY.KLCity_id = CClC.City_id
				LEFT JOIN v_KLTown KL_TOWN with (nolock) on KL_TOWN.KLTown_id = CClC.Town_id
				LEFT JOIN v_CmpCallType CCT with (nolock) on CCT.CmpCallType_id = CCC.CmpCallType_id
				LEFT JOIN v_CmpCallerType CCRT with (nolock) on CCRT.CmpCallerType_id = CCC.CmpCallerType_id
				left join v_Lpu Lpu (nolock) on Lpu.Lpu_id = CCC.Lpu_id
                LEFT JOIN v_MedStaffFact msf on msf.MedPersonal_id =  COALESCE(EMT.EmergencyTeam_HeadShift, CClC.MedStaffFact_id, null)
				--LEFT JOIN v_MedStaffFact MSF with (nolock) on MSF.MedStaffFact_id = CClC.MedStaffFact_id
				LEFT JOIN v_MedStaffFact MSFCID with (nolock) on MSFCID.MedStaffFact_id = CClC.MedStaffFact_cid
				LEFT JOIN v_MedPersonal MP with (nolock) on MP.MedPersonal_id = msf.MedStaffFact_id
				LEFT JOIN v_LpuBuilding LB (nolock) on LB.LpuBuilding_id = CCC.LpuBuilding_id
				-- left join v_SocStatus SS (nolock) on SS.SocStatus_id = CLC.SocStatus_id

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
			".ImplodeWherePH( $where )."
		";

		$query = $this->db->query( $sql, $params );
		if ( is_object( $query ) ) {
			$response = $query->result_array();
		}
		else
			return false;

		$sql = "
			declare @curdate datetime = dbo.tzGetDate();

			SELECT TOP 1
				vper.Person_SurName,
				vper.Person_SecName,
				vper.Person_FirName,
				-- если человек имеет федеральную льготу, то устанавливаем Server_pid = 1
				-- убрать, когда будет сделан адекватный механизм сбора атрибутов человека
				--CASE WHEN ( (fedl.Person_id is not null) ) THEN 1 ELSE -1 END as Server_pid,
				-- федеральный льготник
				case
					when PersonPrivilegeFed.Person_id is not null then 1
					else 0
				end as Person_IsFedLgot,
				vper.Server_pid,
				vper.Person_IsInErz,
				vper.PersonIdentState_id,
				vper.Person_id,
				convert(varchar,cast(vper.Person_BirthDay as datetime),104) as Person_BirthDay,
				vper.Sex_id as PersonSex_id,
				case
					when len(vper.Person_Snils) = 11 then left(vper.Person_Snils, 3) + '-' + substring(vper.Person_Snils, 4, 3) + '-' +
						substring(vper.Person_Snils, 7, 3) + '-' + right(vper.Person_Snils, 2)
					else vper.Person_Snils
				end as Person_SNILS,
				vper.SocStatus_id,
				vper.FamilyStatus_id,
				vper.PersonFamilyStatus_IsMarried,
				vper.Person_edNum as Federal_Num,
				vper.UAddress_id,
				uaddr.PersonSprTerrDop_id as UPersonSprTerrDop_id,
				uaddr.Address_Zip as UAddress_Zip,
				uaddr.KLCountry_id as UKLCountry_id,
				uaddr.KLRGN_id as UKLRGN_id,
				uaddr.KLSubRGN_id as UKLSubRGN_id,
				uaddr.KLCity_id as UKLCity_id,
				uaddr.KLTown_id as UKLTown_id,
				uaddr.KLStreet_id as UKLStreet_id,
				uaddr.Address_House as UAddress_House,
				uaddr.Address_Corpus as UAddress_Corpus,
				uaddr.Address_Flat as UAddress_Flat,
				uaddrsp.AddressSpecObject_id as UAddressSpecObject_id,
				uaddrsp.AddressSpecObject_Name as UAddressSpecObject_Value,
				uaddr.Address_Address as UAddress_AddressText,
				uaddr.Address_Address as UAddress_Address,
				--convert(varchar,cast(uaddr.Address_begDate as datetime),104) as UAddress_begDate,

				baddr.PersonSprTerrDop_id as BPersonSprTerrDop_id,
				baddr.Address_id,
				baddr.KLCountry_id as BKLCountry_id,
				baddr.KLRGN_id as BKLRGN_id,
				baddr.KLSubRGN_id as BKLSubRGN_id,
				baddr.KLCity_id as BKLCity_id,
				baddr.KLTown_id as BKLTown_id,
				baddr.KLStreet_id as BKLStreet_id,
				baddr.Address_House as BAddress_House,
				baddr.Address_Corpus as BAddress_Corpus,
				baddr.Address_Flat as BAddress_Flat,
				baddrsp.AddressSpecObject_id as BAddressSpecObject_id,
				baddrsp.AddressSpecObject_Name as BAddressSpecObject_Value,
				baddr.Address_Zip as BAddress_Zip,
				baddr.Address_Address as BAddress_AddressText,
				baddr.Address_Address as BAddress_Address,
				pcc.PolisCloseCause_Code as polisCloseCause,
				vper.PAddress_id,
				paddr.PersonSprTerrDop_id as PPersonSprTerrDop_id,
				paddr.Address_Zip as PAddress_Zip,
				paddr.KLCountry_id as PKLCountry_id,
				paddr.KLRGN_id as PKLRGN_id,
				paddr.KLSubRGN_id as PKLSubRGN_id,
				paddr.KLCity_id as PKLCity_id,
				paddr.KLTown_id as PKLTown_id,
				paddr.KLStreet_id as PKLStreet_id,
				paddr.Address_House as PAddress_House,
				paddr.Address_Corpus as PAddress_Corpus,
				paddr.Address_Flat as PAddress_Flat,
				paddrsp.AddressSpecObject_id as PAddressSpecObject_id,
				paddrsp.AddressSpecObject_Name as PAddressSpecObject_Value,
				paddr.Address_Address as PAddress_AddressText,
				paddr.Address_Address as PAddress_Address,
				--convert(varchar,cast(paddr.Address_begDate as datetime),104) as PAddress_begDate,

				pi.Nationality_id as PersonNationality_id,
				pol.OmsSprTerr_id as OMSSprTerr_id,
				pol.PolisType_id,
				pol.Polis_Ser,
				pol.PolisFormType_id,
				case when pol.PolisType_id = 4 then '' else pol.Polis_Num end as Polis_Num,
				pol.OrgSmo_id as OrgSMO_id,
				convert(varchar,cast(pol.Polis_begDate as datetime),104) as Polis_begDate,
				convert(varchar,cast(pol.Polis_endDate as datetime),104) as Polis_endDate,
				doc.DocumentType_id,
				doc.Document_id,
				doc.Document_Ser,
				doc.Document_Num,
				doc.OrgDep_id as OrgDep_id,
				ns.KLCountry_id,
				klc.KLCountry_Name,
				smo.Org_Name as SMO_Name,
				case when ns.NationalityStatus_IsTwoNation = 2 then 1 else 0 end as NationalityStatus_IsTwoNation,
				pjob.Org_id,
				pjob.OrgUnion_id,
				pjob.Post_id,
				convert(varchar,cast(doc.Document_begDate as datetime),104) as Document_begDate,
				PDEP.DeputyKind_id,
				PDEP.Person_pid as DeputyPerson_id,
				case when PDEPSTATE.Person_id is not null THEN PDEPSTATE.Person_SurName + ' ' + PDEPSTATE.Person_FirName + ' ' + isnull(PDEPSTATE.Person_SecName, '') ELSE '' END as DeputyPerson_Fio,
				ResidPlace_id,
				PersonChild_IsManyChild,
				PersonChild_IsBad,
				PersonChild_IsYoungMother,
				PersonChild_IsIncomplete,
				PersonChild_IsInvalid,
				PersonChild_IsTutor,
				PersonChild_IsMigrant,
				HealthKind_id,
				ph.PersonHeight_IsAbnorm,
				ph.HeightAbnormType_id,
				pw.WeightAbnormType_id,
				pw.PersonWeight_IsAbnorm,
				PCh.PersonSprTerrDop_id,
				FeedingType_id,
				InvalidKind_id,
				convert(varchar,cast(PersonChild_invDate as datetime),104) as PersonChild_invDate,
				HealthAbnorm_id,
				HealthAbnormVital_id,

				convert(varchar,cast(vper.Person_deadDT as datetime),104) as Person_deadDT,
				convert(varchar,cast(vper.Person_closeDT as datetime),104) as Person_closeDT,
				rtrim(vper.Person_Phone) as PersonPhone_Phone,
				rtrim(pi.PersonInfo_InternetPhone) as PersonInfo_InternetPhone,
				rtrim(vper.Person_Inn) as PersonInn_Inn,
				rtrim(vper.Person_SocCardNum) as PersonSocCardNum_SocCardNum,
				rtrim(Ref.PersonRefuse_IsRefuse) as PersonRefuse_IsRefuse,
				rtrim(pce.PersonCarExist_IsCar) as PersonCarExist_IsCar,
				rtrim(pche.PersonChildExist_IsChild) as PersonChildExist_IsChild,
				ph.PersonHeight_Height as PersonHeight_Height,
				ISNULL(pw.Okei_id, 37) as Okei_id,
				pw.PersonWeight_Weight as PersonWeight_Weight,
				-- признак того, что человек БДЗшный и у него закончился полис и можно дать ввести иногородний
				CASE WHEN
					vper.Server_pid = 0
					and pol.Polis_endDate is not null
					and pol.Polis_endDate < @curdate
				THEN
					1
				ELSE
					0
				END as Polis_CanAdded,
				pi.Ethnos_id,
				mop.OnkoOccupationClass_id as OnkoOccupationClass_id,
				per.BDZ_id,
				per.BDZ_Guid,
				pol.Polis_Guid,
				IsUnknown.YesNo_Code as Person_IsUnknown,
				IsAnonym.YesNo_Code as Person_IsAnonym
			from v_PersonState vper with (nolock)
			left join v_Person per with (nolock) on per.Person_id=vper.Person_id
			left join v_Address uaddr with (nolock) on vper.UAddress_id = uaddr.Address_id
			left join v_AddressSpecObject uaddrsp with (nolock) on uaddr.AddressSpecObject_id = uaddrsp.AddressSpecObject_id
			left join v_Address paddr with (nolock) on vper.PAddress_id = paddr.Address_id
			left join v_AddressSpecObject paddrsp with (nolock) on paddr.AddressSpecObject_id = paddrsp.AddressSpecObject_id
			-- Адрес рождения
			left join PersonBirthPlace pbp with (nolock) on vper.Person_id = pbp.Person_id
			left join v_Address baddr with (nolock) on pbp.Address_id = baddr.Address_id
			left join v_AddressSpecObject baddrsp with (nolock) on baddr.AddressSpecObject_id = baddrsp.AddressSpecObject_id
			-- end. Адрес рождения

			left join Polis pol with (nolock) on pol.Polis_id=vper.Polis_id
			left join v_OrgSMO orgsmo with (nolock) on orgsmo.OrgSMO_id=pol.OrgSMO_id
			left join v_Org smo with (nolock) on orgsmo.Org_id=smo.Org_id
			left join v_PolisCloseCause pcc (nolock) on pol.PolisCloseCause_id = pcc.PolisCloseCause_id
			left join Document doc with (nolock) on doc.Document_id=vper.Document_id
			left join NationalityStatus ns with (nolock) on ns.NationalityStatus_id = vper.NationalityStatus_id
			left join v_KLCountry klc with (nolock) on ns.KLCountry_id=klc.KLCountry_id
			left join PersonInfo pi with (nolock) on pi.Person_id = vper.Person_id
			left join Job pjob with (nolock) on vper.Job_id = pjob.Job_id
			left join PersonDeputy PDEP with (nolock) on PDEP.Person_id = vper.Person_id
			left join v_PersonState PDEPSTATE with (nolock) on PDEPSTATE.Person_id = PDEP.Person_pid
			left join PersonChild PCh with (nolock) on PCh.Person_id = vper.Person_id
			left join v_YesNo IsUnknown with(nolock) on IsUnknown.YesNo_id = isnull(per.Person_IsUnknown,1)
			left join v_YesNo IsAnonym with(nolock) on IsAnonym.YesNo_id = isnull(per.Person_IsAnonym,1)
			-- федеральный льготник
			-- полис, который был в истории периодик
			-- outer apply (select top 1 pls1.Person_id, pls1.BDZ_id, pls1.Polis_endDate, pls1.PolisCloseCause_id from v_PersonPolis pls1 with (nolock) where pls1.Person_id = vper.Person_id and pls1.BDZ_id is not null order by Polis_begDate desc) as bdz_pol
			outer apply (
				select top 1
					pp.Person_id
				from
					v_PersonPrivilege pp with (nolock)
					inner join v_PrivilegeType pt (nolock) on pt.PrivilegeType_id = pp.PrivilegeType_id
				where
					pt.ReceptFinance_id = 1
					and pp.PersonPrivilege_begDate <= @curdate
					and (pp.PersonPrivilege_endDate is null or pp.PersonPrivilege_endDate >= cast(@curdate as date))
					and pp.Person_id = vper.Person_id
			) PersonPrivilegeFed
			outer apply (
				select top 1
					OnkoOccupationClass_id
				from
					v_MorbusOnkoPerson with (nolock)
				where
					Person_id = :Person_id
				order by
					MorbusOnkoPerson_insDT desc
			) as mop
			outer apply (
				select top 1
					PersonRefuse_IsRefuse
				from
					v_PersonRefuse with (nolock)
				where
					Person_id = :Person_id
					and PersonRefuse_Year = year(@curdate)
				order by
					PersonRefuse_insDT desc
			) as Ref
			outer apply (
				select top 1
					PersonCarExist_IsCar
				from
					PersonCarExist with (nolock)
				where
					Person_id = :Person_id
				order by
					PersonCarExist_setDT desc
			) as pce
			outer apply (
				select top 1
					PersonChildExist_IsChild
				from
					PersonChildExist with (nolock)
				where
					Person_id = :Person_id
				order by
					PersonChildExist_setDT desc
			) as pche
			outer apply (
				select top 1
					PersonHeight_Height,
					PersonHeight_IsAbnorm,
					HeightAbnormType_id
				from
					PersonHeight with (nolock)
				where
					Person_id = :Person_id
				order by
					PersonHeight_setDT desc
			) as ph
			outer apply (
				select top 1
					PersonWeight_Weight,
					WeightAbnormType_id,
					PersonWeight_IsAbnorm,
					Okei_id
				from
					PersonWeight with (nolock)
				where
					Person_id = :Person_id
				order by
					PersonWeight_setDT desc
			) as pw
			where vper.Person_id= :Person_id
		";



		$res = $this->db->query(
			$sql, array(
				'Person_id' => $response[0]['Person_id']
			)
		);
		$arPerson = $res->result('array');
		if(is_array($arPerson) && count($arPerson) > 0)
			$response = array_merge($response[0],$arPerson[0]);

		if (isset($response["OrgDep_id"])) {
			$sql = "
		SELECT O.Org_Name
		FROM OrgDep as OD
			INNER JOIN Org  as O ON O.Org_id = OD.Org_id
 		WHERE OD.OrgDep_id = :OrgDep_id";
			$res = $this->db->query(
				$sql, array(
					'OrgDep_id' => $response["OrgDep_id"]
				)
			);
			$arOrgDep = $res->result('array');
			if (is_array($arOrgDep) && count($arOrgDep) > 0)
				$response = array_merge($response, $arOrgDep[0]);
		}


		//		if(isset($response["Diag_id"])) {
		//			$sql = "
		//		SELECT D.Diag_Code as main_Diag_Code,D.Diag_Name as main_Diag_Name,SD.Diag_Code as s_Diag_Code,SD.Diag_Name as s_Diag_Name
		//		FROM Diag as D
		//		LEFT JOIN Diag as SD on SD.Diag_id = :Diag_sid
		// 		WHERE D.Diag_id = :Diag_id";
		//			$res = $this->db->query(
		//				$sql, array(
		//					'Diag_id' => $response["Diag_id"],
		//					'Diag_sid' => $response["Diag_sid"]
		//				)
		//			);
		//			$arDiag = $res->result('array');
		//			if (is_array($arDiag) && count($arDiag) > 0)
		//				$response = array_merge($response, $arDiag[0]);
		//		}

		if(isset($response["EmergencyTeam_id"])) {

			$sql = "
		SELECT ETS.EmergencyTeamSpec_Name
		FROM EmergencyTeam AS ET
			INNER JOIN EmergencyTeamSpec AS ETS  with (nolock) ON ETS.EmergencyTeamSpec_id = ET.EmergencyTeamSpec_id
 		WHERE EmergencyTeam_id = :EmergencyTeam_id";
			$res = $this->db->query(
				$sql, array(
					'EmergencyTeam_id' => $response["EmergencyTeam_id"]
				)
			);
			$arEmergencyTeam = $res->result('array');
			if (is_array($arEmergencyTeam) && count($arEmergencyTeam) > 0)
				$response = array_merge($response, $arEmergencyTeam[0]);
		}

		$query = "
			SELECT
				--CCC.CmpCloseCard_id,
				CCCM.CmpCloseCardCombo_Code as CmpCloseCardCombo_id,
				CCCR.Localize
			FROM
				{$this->schema}.v_CmpCloseCard CCC with (nolock)
				LEFT JOIN {$this->schema}.v_CmpCloseCardRel CCCR with (nolock) on CCCR.CmpCloseCard_id = CCC.CmpCloseCard_id
				LEFT JOIN v_CmpCloseCardCombo CCCM with (nolock) on CCCM.CmpCloseCardCombo_id = CCCR.CmpCloseCardCombo_id
			WHERE
				CCC.CmpCallCard_id = :CmpCallCard_id
		";
		$result = $this->db->query($query, $data);
		$arComboboxes = $result->result('array');
		$response["Comboboxes"] = $arComboboxes;


		$arrMedPersonal = array_filter($response["Comboboxes"],function($innerArray){
			//Оставляем только врача,фельдшера,фельдшера м\с и водителя для выбора их имен
			return in_array($innerArray["CmpCloseCardCombo_id"],array(674,675,676,677,714));
		});

		foreach($arrMedPersonal as $elem){
			$sql = "

			select
				MP.Person_Fio as MedPersonal_Fio
			from
				v_MedPersonal MP with(nolock)
			where
				MP.MedPersonal_id = :MedPersonal_id
				";

			$res = $this->db->query(
				$sql, array(
					'MedPersonal_id' => $elem["Localize"]
				)
			);
			$MedPerson = $res->result('array');
			if (is_array($MedPerson) && count($MedPerson) > 0)
				$response["MPFio" . $elem["CmpCloseCardCombo_id"]] = $MedPerson[0]["MedPersonal_Fio"];
		}

		$arrOrgs = array_filter($response["Comboboxes"],function($innerArray){
			//Оставляем только морг,травм пункт и больницу для выбора их наименований
			return in_array($innerArray["CmpCloseCardCombo_id"],array(687,688,689));
		});

		foreach($arrOrgs as $elem) {
			$query = "
			SELECT
				RTRIM(o.Org_Nick) as Org_Nick
			FROM
				v_Org o with (nolock)
			WHERE Org_id = :Org_id
		";

			$res = $this->db->query(
				$query, array(
					'Org_id' => $elem["Localize"]
				)
			);
			$Org = $res->result('array');
			if (is_array($Org) && count($Org) > 0)
				$response["OrgNick" . $elem["CmpCloseCardCombo_id"]] = $Org[0]["Org_Nick"];
		}
		return $response;

	}

	/**
	 * Проверка оплаты диагноза по ОМС
	 */
	function checkDiagFinance($data){

		$query = "
		declare @history_date datetime = dbo.tzGetDate();
			select top 1
				IsOms.YesNo_Code as DiagFinance_IsOms,
				IsAlien.YesNo_Code as DiagFinance_IsAlien,
				df.Sex_id as Diag_Sex,
				a.PersonAgeGroup_Code,
				p.OmsSprTerr_Code,
				p.Sex_id,
				dbo.Age2(p.Person_BirthDay, @history_date) as Age,
				pt.PayType_SysNick
			from
				v_DiagFinance df with (nolock)
				left join PersonAgeGroup a with (nolock) on a.PersonAgeGroup_id = df.PersonAgeGroup_id
				left join YesNo IsAlien with (nolock) on IsAlien.YesNo_id = df.DiagFinance_IsAlien
				left join YesNo IsOms with (nolock) on IsOms.YesNo_id = df.DiagFinance_IsOms
				outer apply (
					select top 1
						ost.OmsSprTerr_Code,
						ps.Sex_id,
						ps.Person_BirthDay
					from
						[v_PersonState] [ps] with (nolock)
						left join [v_Polis] pls with (nolock) on [pls].[Polis_id] = [ps].[Polis_id]
						left join [v_OmsSprTerr] ost with (nolock) on [ost].[OmsSprTerr_id] = [pls].[OmsSprTerr_id]
					where ps.Person_id = :Person_id
				) p
				left join v_PayType pt with (nolock) on pt.PayType_id = :PayType_id
			where
				df.Diag_id = :Diag_id
		";
		$queryParams = array(
			'Diag_id' => $data['Diag_id'],
			'PayType_id' => $data['PayType_id'],
			'Person_id' => $data['Person_id']
		);
		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			return array('Error_Msg' => "Ошибка при выполнении запроса к базе данных");
		}

		return $result->result('array');
	}

	/**
	 * Удаление карты вызова
	 *
	 * @param array $data
	 * @param bool $ignoreRegistryCheck
	 * @return bool
	 */
	function deleteCmpCallCard($data = array(), $ignoreRegistryCheck = false, $delCallCard = true) {
		$result = array();
		$error = array();
		$doc_array = array();
		// CmpCloseCard - карта вызова
		// CmpCallCard - талон вызова
		$isCloseCard = false;

		if (!array_key_exists('CmpCallCard_id', $data) || !$data['CmpCallCard_id']) {
			$error[] = 'Не указан идентификатор карты вызова.';
		}

		if (count($error) == 0) {
			$checkLock = $this->checkLockCmpCallCard($data);
			if (isset($checkLock[0]['CmpCallCard_id'])) {
				$error[] = 'Карта вызова редактируется и не может быть удалена.';
			}
		}
		//признак источника
		$callCardInputTypeCode = $this->getCallCardInputTypeCode($data['CmpCallCard_id']);

		if (count($error) == 0 && $ignoreRegistryCheck === false) {
			$checkRegistryParam = $data;
			$query = "
                select
                    CmpCloseCard_id
                from
                    v_CmpCloseCard with (nolock)
                where
                    CmpCallCard_id = :CmpCallCard_id;
            ";
			$cclc_array = $this->queryResult($query, array(
				'CmpCallCard_id' => $data['CmpCallCard_id']
			));
			if(is_array($cclc_array) && count($cclc_array) > 0){
				$isCloseCard = (int)$cclc_array[0]['CmpCloseCard_id'];
				$checkRegistryParam['CmpCloseCard_id'] = $cclc_array[0]['CmpCloseCard_id'];
				$checkRegistryParam['CmpCallCard_id'] = null;

				// Проверку наличия карты вызова в реестре
				$this->load->model('Registry_model', 'Reg_model');
				$registryData = $this->Reg_model->checkEvnAccessInRegistry($checkRegistryParam);
				if ( is_array($registryData) ) {
					if(isset($registryData['Error_Msg'])){
						$registryData['Error_Msg'] = str_replace('Удаление записи невозможно', ' Удалите Карту вызова из реестра и повторите действие', $registryData['Error_Msg']);
					}
					return $registryData;
				}
				unset($checkRegistryParam);
			}

		}

		//удаление информации о использовании медикаментов
		if (count($error) == 0) {
			$query = "
                select
                    CmpCallCardDrug_id,
                    DocumentUcStr_id
                from
                    v_CmpCallCardDrug with (nolock)
                where
                    CmpCallCard_id = :CmpCallCard_id;
            ";
			$cccd_array = $this->queryResult($query, array(
				'CmpCallCard_id' => $data['CmpCallCard_id']
			));
			if (is_array($cccd_array)) {
				foreach($cccd_array as $cccd_data) {
					if (!empty($cccd_data['DocumentUc_id']) && !in_array($cccd_data['DocumentUc_id'], $doc_array)) { //сбор идентификаторов документов
						$doc_array[] = $cccd_data['DocumentUc_id'];
					}
					$response = $this->deleteCmpCallCardDrug(array(
						'CmpCallCardDrug_id' => $cccd_data['CmpCallCardDrug_id'],
						'DocumentUcStr_id' => $cccd_data['DocumentUcStr_id'],
						'pmUser_id' => $data['pmUser_id']
					));
					if (!empty($response['Error_Msg'])) {
						$error[] = $response['Error_Msg'];
						break;
					}
				}
			}
		}

		//удаление пустых документов учета
		if (count($error) == 0 && count($doc_array) > 0) {
			$response = $this->deleteEmptyDocumentUc($doc_array);
			if (!empty($response['Error_Msg'])) {
				$error[] = $response['Error_Msg'];
			}
		}

		if (count($error) == 0) {
			$query = "
                declare
                    @ErrCode int,
                    @ErrMessage varchar(4000);

                exec p_CmpCallCard_del
                    @CmpCallCard_id = :CmpCallCard_id,
                    @pmUser_id = :pmUser_id,
                    @Error_Code = @ErrCode output,
                    @Error_Message = @ErrMessage output;

                select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
            ";
			$response = $this->getFirstRowFromQuery($query, array(
				'CmpCallCard_id' => $data['CmpCallCard_id'],
				'pmUser_id' => $data['pmUser_id']
			));
			if ($response === false) {
				$error[] = "Во время удаления талона вызова произошла ошибка. При повторении ошибки обратитесь к администратору.";
			} else {
				if (!empty($response['Error_Msg'])) {
					$error[] = $response['Error_Msg'];
				}
			}
		}

		if ( $isCloseCard && !in_array($callCardInputTypeCode, array(1,2)) && count($error) == 0) {
			$query = "
                declare
                    @ErrCode int,
                    @ErrMessage varchar(4000);

                exec p_CmpCloseCard_del
                    @CmpCloseCard_id = :CmpCloseCard_id,
                    @pmUser_id = :pmUser_id,
                    @Error_Code = @ErrCode output,
                    @Error_Message = @ErrMessage output;

                select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
            ";
			$response = $this->getFirstRowFromQuery($query, array(
				'CmpCloseCard_id' => $isCloseCard,
				'pmUser_id' => $data['pmUser_id']
			));
			if ($response === false){
				$error[] = "Во время удаления карты вызова произошла ошибка. При повторении ошибки обратитесь к администратору.";
			} elseif ( !empty($response['Error_Msg']) ){
				$error[] = $response['Error_Msg'];
			}
		}

		if (count($error) > 0) {
			$response = $this->createError(null, $error[0]);
		}

		return $response;
	}
}
