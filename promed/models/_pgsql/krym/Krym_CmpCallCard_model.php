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
 * @copyright		Copyright (c) 2020 Swan Ltd.
 * @author			Valery Bondarev
 * @version			krym
 */

require_once(APPPATH.'models/_pgsql/CmpCallCard_model.php');

class Krym_CmpCallCard_model extends CmpCallCard_model {

	/**
	 * default шапка для печати
	 */
	function printCmpCallCardHeader($data) {
		$query = "
			select
				CCC.CmpCallCard_id as \"CmpCallCard_id\"
				,CCC.CmpCallCard_Numv as \"Day_num\"
				,CCC.CmpCallCard_Ngod as \"Year_num\"
				,to_char(CCC.CmpCallCard_insDT, 'dd.mm.yyyy') as \"CallCardDate\"
				,LB.LpuBuilding_Name as \"LpuBuilding_Name\"
				,EMT.EmergencyTeam_Num as \"EmergencyTeam_Num\"

				,CASE WHEN CCC.CmpCallCard_Dsp1 IS NOT NULL THEN CCC.CmpCallCard_Dsp1 ELSE '' END as \"Dsp_prm\"
				,CASE WHEN CCC.CmpCallCard_Dsp2 IS NOT NULL THEN CCC.CmpCallCard_Dsp2 ELSE '' END as \"Dsp_per\"

				,to_char(CCC.CmpCallCard_prmDT, 'hh24:mi') as \"AcceptTime\"
				,'' as \"TransportTime\"
				,'' as \"GoTime\"
				,'' as \"ArriveTime\"
				,'' as \"EndTime\"
				,'' as \"BackTime\"
				,to_char(CCC.CmpCallCard_Tisp, 'hh24:mi') as \"IspTime\"
				,CASE WHEN ((CCC.CmpCallCard_prmDT IS NOT NULL) AND (CCC.CmpCallCard_Tisp IS NOT NULL)) THEN DATEDIFF('mm',CCC.CmpCallCard_prmDT,CCC.CmpCallCard_Tisp) ELSE '' END as \"Dlit\"

				,to_char(CCC.CmpCallCard_Tper, 'hh24:mi') as \"PerTime\"
				,CASE WHEN MP.Person_Fin IS NOT NULL THEN MP.Person_Fin ELSE CCC.CmpCallCard_Dokt END as \"Doc\"


				,DATEDIFF('mm',CCC.CmpCallCard_prmDT,CCC.CmpCallCard_Tisp)


				,KL_AR.KLArea_Name as \"Area\"
				,CCC.KLCity_id as \"KLCity_id\"
				,KL_CITY.KLCity_Name as \"City\"
				,CCC.KLTown_id as \"KLTown_id\"
				,KL_TOWN.KLTown_Name as \"Town\"
				,CCC.KLStreet_id as \"KLStreet_id\"
				,KL_ST.KLStreet_Name as \"Street\"

				,CCC.CmpCallCard_Dom as \"House\"
				,Lpu.Lpu_Nick as \"Lpu_name\"
				,Lpu.UAddress_Address as \"UAddress_Address\"
				,Lpu.Lpu_Phone as \"Lpu_Phone\"

				,case when KL_CITY.KLCity_Name is not null then 'г. '||KL_CITY.KLCity_Name else '' end ||
				case when KL_TOWN.KLTown_FullName is not null then
					case when (KL_CITY.KLCity_Name is not null) then ', '||LOWER(KL_TOWN.KLSocr_Nick)||'. '||KL_TOWN.KLTown_Name else LOWER(KL_TOWN.KLSocr_Nick)||'. '||KL_TOWN.KLTown_Name end
				else '' end ||
				case when KL_ST.KLStreet_Name is not null then ',<br> '||LOWER(socrStreet.KLSocr_Nick)||'. '||KL_ST.KLStreet_Name  
				else case when CCC.CmpCallCard_Ulic is not null then ', '||LOWER(CCC.CmpCallCard_Ulic)||'. ' else '' end
					end ||

				case when SecondStreet.KLStreet_FullName is not null then
					case when socrSecondStreet.KLSocr_Nick is not null then ', '||LOWER(socrSecondStreet.KLSocr_Nick)||'. '||SecondStreet.KLStreet_Name else
					', '||SecondStreet.KLStreet_FullName end
					else ''
				end ||

				case when CCC.CmpCallCard_Dom is not null then ', д.'||CCC.CmpCallCard_Dom else '' end ||
				case when CCC.CmpCallCard_Korp is not null then ', к.'||CCC.CmpCallCard_Korp else '' end ||
				case when CCC.CmpCallCard_Kvar is not null then ', кв.'||CCC.CmpCallCard_Kvar else '' end ||
				case when CCC.CmpCallCard_Kodp is not null then '</br>Код подъезда: '||CCC.CmpCallCard_Kodp else '' end ||
				case when CCC.CmpCallCard_Comm is not null then '</br>'||CCC.CmpCallCard_Comm else '' end as \"Address_Address\"


				,CCC.CmpCallCard_Korp as \"Korpus\"
				,CCC.CmpCallCard_Kvar as \"Office\"
				,CASE WHEN COALESCE(CCC.CmpCallCard_Podz,0) = 0 THEN '' ELSE CCC.CmpCallCard_Podz END as \"Entrance\"
				,CASE WHEN COALESCE(CCC.CmpCallCard_Etaj,0) = 0 THEN '' ELSE CCC.CmpCallCard_Etaj END as \"Level\"
				,CCC.CmpCallCard_Kodp as \"CodeEntrance\"

				,case when CCC.Person_SurName is not null then CCC.Person_SurName + '<br>' else '' end +
				case when CCC.Person_FirName is not null then CCC.Person_FirName + '<br>' else '' end +
				case when CCC.Person_SecName is not null then CCC.Person_SecName + '<br>' else '' end as \"FIO\"
				,CCC.Person_Age as \"Age\"
				,case when
					DATEDIFF('yy',coalesce(PS.Person_BirthDay, CCC.Person_BirthDay,'01.01.2000'::date),dbo.tzGetDate())>1
				then
					'лет'
				else
					case when
						DATEDIFF('mm',coalesce(PS.Person_BirthDay, CCC.Person_BirthDay),dbo.tzGetDate())>1
					then
						'мес'
					else
						'дн'
					end
				end as \"AgeTypeValue\"
				,case when
					DATEDIFF('yy',coalesce(PS.Person_BirthDay, CCC.Person_BirthDay,'01.01.2000'::date),dbo.tzGetDate())>1
				then
					case when
						COALESCE(PS.Person_BirthDay, COALESCE(CCC.Person_BirthDay,0))=0
					then
						''
					else
						DATEDIFF('yy',coalesce(PS.Person_BirthDay, CCC.Person_BirthDay),dbo.tzGetDate())
					end
				else
					case when
						DATEDIFF('mm',coalesce(PS.Person_BirthDay, CCC.Person_BirthDay),dbo.tzGetDate())>1
					then
						DATEDIFF('mm',coalesce(PS.Person_BirthDay, CCC.Person_BirthDay),dbo.tzGetDate())
					else
						DATEDIFF('dd',coalesce(PS.Person_BirthDay, CCC.Person_BirthDay),dbo.tzGetDate())
					end
				end as \"AgePS\"

				,SX.Sex_name as \"Sex_name\"
				,RS.CmpReason_Name as \"Reason\"

				--,SS.SocStatus_Name as C_PersonSocial_id

				-- ,CCC.CmpCallCard_Ktov
				,CCC.CmpCallCard_Telf as \"Phone\"

				,CCC.CmpCallType_id as \"CmpCallType_id\"
				,CCC.CmpCallCard_Comm as \"CmpCallCard_Comm\"
				,CCT.CmpCallType_Code as \"CmpCallType_Code\"
				, CASE WHEN CCRT.CmpCallerType_Name IS NOT NULL THEN CCRT.CmpCallerType_Name ELSE CCC.CmpCallCard_Ktov END as \"CmpCallerType_Name\"
				-- ,CCT.CmpCallType_Name as CallType
				,CCC.CmpCallCard_IsReceivedInPPD as \"CmpCallCard_IsReceivedInPPD\"

			from
				v_CmpCallCard CCC 
				LEFT JOIN Sex SX  on SX.Sex_id = CCC.Sex_id
				left join v_PersonState PS  on PS.Person_id = CCC.Person_id
				LEFT JOIN v_CmpReason RS  on RS.CmpReason_id = CCC.CmpReason_id
				LEFT JOIN KLStreet KL_ST  on KL_ST.KLStreet_id = CCC.KLStreet_id
				LEFT JOIN KLArea KL_AR  on KL_AR.KLArea_id = CCC.KLSubRgn_id
				left join v_KLSocr socrStreet  on KL_ST.KLSocr_id = socrStreet.KLSocr_id
				left join v_KLStreet SecondStreet  on SecondStreet.KLStreet_id = CCC.CmpCallCard_UlicSecond
				left join v_KLSocr socrSecondStreet  on SecondStreet.KLSocr_id = socrSecondStreet.KLSocr_id
				LEFT JOIN v_KLCity KL_CITY  on KL_CITY.KLCity_id = CCC.KLCity_id
				LEFT JOIN v_KLTown KL_TOWN  on KL_TOWN.KLTown_id = CCC.KLTown_id
				LEFT JOIN v_CmpCallType CCT  on CCT.CmpCallType_id = CCC.CmpCallType_id
				LEFT JOIN v_CmpCallerType CCRT  on CCRT.CmpCallerType_id = CCC.CmpCallerType_id
				left join v_Lpu Lpu  on Lpu.Lpu_id = CCC.Lpu_id
				LEFT JOIN v_EmergencyTeam EMT  on EMT.EmergencyTeam_id = CCC.EmergencyTeam_id
				LEFT JOIN v_MedPersonal MP  on MP.MedPersonal_id = EMT.EmergencyTeam_HeadShift
				LEFT JOIN v_LpuBuilding LB  on LB.LpuBuilding_id = CCC.LpuBuilding_id
				-- left join v_SocStatus SS  on SS.SocStatus_id = CLC.SocStatus_id
			where
				CCC.CmpCallCard_id = :CmpCallCard_id
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
			SELECT
				CClC.CmpCallCard_id as \"CmpCallCard_id\",
				--CClC.CmpCallCard_IsAlco as isAlco,
				CClC.isSogl as \"isSogl\",
				CClC.CmpCloseCard_id as \"CmpCloseCard_id\",
				CCC.CmpReason_id as \"CmpReason_id\",
				CClC.PayType_id as \"PayType_id\",
				CClC.Year_num as \"Year_num\",
				CClC.Day_num as \"Day_num\",
				CClC.Sex_id as \"Sex_id\",
				CClC.Area_id as \"Area_id\",
				CClC.City_id as \"City_id\",
				CClC.Town_id as \"Town_id\",
				CClC.Street_id as \"Street_id\",
				CClC.House as \"House\",
				CClC.Office as \"Office\",
				CClC.Entrance as \"Entrance\",
				CClC.Level as \"Level\",
				CClC.CodeEntrance as \"CodeEntrance\",
				CClC.Phone as \"Phone\",
				CClC.DescText as \"DescText\",
				CClC.Fam as \"Fam\",
                CClC.Name as \"Name\",
                CClC.Middle as \"Middle\",
				CClC.Age as \"Age\",
				CCC.Person_id as \"Person_id\",
				CClC.Ktov as \"Ktov\",
				CClC.CmpCallerType_id as \"CmpCallerType_id\",
				CClC.CmpCloseCard_IsExtra as \"CmpCloseCard_IsExtra\",
				CClC.CmpCloseCard_ClinicalEff as \"CmpCloseCard_ClinicalEff\",
				CCC.KLRgn_id as \"KLRgn_id\",
				CCC.KLSubRgn_id as \"Area_id\",
				CCC.KLCity_id as \"City_id\",
				CCC.KLTown_id as \"Town_id\",
				CCC.CmpCallPlaceType_id as \"CmpCallPlaceType_id\",
				CCC.KLStreet_id  as \"Street_id\",
				CClC.Room as \"Room\",
				CClC.Korpus as \"Korpus\",

				CClC.EmergencyTeamNum as \"EmergencyTeamNum\",
				CCLC.EmergencyTeam_id as \"EmergencyTeam_id\",
				CClC.StationNum as \"StationNum\",
				CClC.LpuBuilding_id as \"LpuBuilding_id\",
				LB.LpuBuilding_Code as \"LpuBuilding_Code\",
				CClC.pmUser_insID as \"Feldsher_id\",
				--CClC.pmUser_insID as \"FeldsherAccept\",
				CCLC.FeldsherAccept as \"FeldsherAccept\",
				CClC.FeldsherTrans as \"FeldsherTrans\",
				
				to_char(cast(CClC.CmpCloseCard_BegTreatDT as timestamp), 'hh24:mi') as \"CmpCloseCard_BegTreatDT\",
				to_char(cast(CClC.CmpCloseCard_EndTreatDT as timestamp), 'hh24:mi') as \"CmpCloseCard_EndTreatDT\",

				to_char(CClC.AcceptTime, 'dd.mm.yyyy')||' '||to_char(cast(CClC.AcceptTime as timestamp), 'hh24:mi') as \"AcceptTime\",
				to_char(CClC.TransTime, 'dd.mm.yyyy')||' '||to_char(cast(CClC.TransTime as timestamp), 'hh24:mi') as \"TransTime\",
				to_char(CClC.GoTime, 'dd.mm.yyyy')||' '||to_char(cast(CClC.GoTime as timestamp), 'hh24:mi') as \"GoTime\",

				to_char(CClC.ArriveTime, 'dd.mm.yyyy')||' '||to_char(cast(CClC.ArriveTime as timestamp), 'hh24:mi') as \"ArriveTime\",
				to_char(CClC.TransportTime, 'dd.mm.yyyy')||' '||to_char(cast(CClC.TransportTime as timestamp), 'hh24:mi') as \"TransportTime\",
				to_char(CClC.ToHospitalTime, 'dd.mm.yyyy')||' '||to_char(cast(CClC.ToHospitalTime as timestamp), 'hh24:mi') as \"ToHospitalTime\",
				to_char(CClC.EndTime, 'dd.mm.yyyy')||' '||to_char(cast(CClC.EndTime as timestamp), 'hh24:mi') as \"EndTime\",
				to_char(CClC.BackTime, 'dd.mm.yyyy')||' '||to_char(cast(CClC.BackTime as timestamp), 'hh24:mi') as \"BackTime\",
				to_char(CClC.CmpCloseCard_TranspEndDT, 'dd.mm.yyyy')||' '||to_char(cast(CClC.CmpCloseCard_TranspEndDT as timestamp), 'hh24:mi') as \"TranspEndDT\",

				CClC.SummTime as \"SummTime\",
				CClC.Work as \"Work\",
				CClC.DocumentNum as \"DocumentNum\",
				CClC.CallType_id as \"CallType_id\",
				CClC.CallPovod_id as \"CallPovod_id\",
				CASE WHEN COALESCE(CClC.isAlco,0) = 0 THEN NULL ELSE CClC.isAlco END as \"isAlco\",
				CClC.Complaints as \"Complaints\",
				CClC.Anamnez as \"Anamnez\",
				CASE WHEN COALESCE(CClC.isMenen,0) = 0 THEN NULL ELSE CClC.isMenen END as \"isMenen\",
				CASE WHEN COALESCE(CClC.isAnis,0) = 0 THEN NULL ELSE CClC.isAnis END as \"isAnis\",
				CASE WHEN COALESCE(CClC.isNist,0) = 0 THEN NULL ELSE CClC.isNist END as \"isNist\",
				CASE WHEN COALESCE(CClC.isLight,0) = 0 THEN NULL ELSE CClC.isLight END as \"isLight\",
				CASE WHEN COALESCE(CClC.isAcro,0) = 0 THEN NULL ELSE CClC.isAcro END as \"isAcro\",
				CASE WHEN COALESCE(CClC.isMramor,0) = 0 THEN NULL ELSE CClC.isMramor END as \"isMramor\",
				CASE WHEN COALESCE(CClC.isHale,0) = 0 THEN NULL ELSE CClC.isHale END as \"isHale\",
				CASE WHEN COALESCE(CClC.isPerit,0) = 0 THEN NULL ELSE CClC.isPerit END as \"isPerit\",
				CClC.Urine as \"Urine\",
				CClC.Shit as \"Shit\",
				CClC.OtherSympt as \"OtherSympt\",
				CClC.CmpCloseCard_AddInfo as \"CmpCloseCard_AddInfo\",
				CClC.WorkAD as \"WorkAD\",
				CClC.AD as \"AD\",
				CASE WHEN COALESCE(CClC.Pulse,0)=0 THEN NULL ELSE CClC.Pulse END as \"Pulse\",
				CASE WHEN COALESCE(CClC.Chss,0)=0 THEN NULL ELSE CClC.Chss END as \"Chss\",
				CASE WHEN COALESCE(CClC.Chd,0)=0 THEN NULL ELSE CClC.Chd END as \"Chd\",
				CClC.Temperature as \"Temperature\",
				CClC.Pulsks as \"Pulsks\",
				CClC.Gluck as \"Gluck\",
				CClC.LocalStatus as \"LocalStatus\",
				to_char(cast(CClC.Ekg1Time as timestamp), 'hh24:mi') as \"Ekg1Time\",
				CClC.Ekg1 as \"Ekg1\",
				to_char(cast(CClC.Ekg2Time as timestamp), 'hh24:mi') as \"Ekg2Time\",
				CClC.Ekg2 as \"Ekg2\",
				CClC.Diag_id as \"Diag_id\",
				CClC.Diag_sid as \"Diag_sid\",
				CClC.EfAD as \"EfAD\",
				CASE WHEN COALESCE(CClC.EfChss,0) = 0 THEN NULL ELSE CClC.EfChss END as \"EfChss\",
				CASE WHEN COALESCE(CClC.EfPulse,0) = 0 THEN NULL ELSE CClC.EfPulse END as \"EfPulse\",
				CClC.EfTemperature as \"EfTemperature\",
				CASE WHEN COALESCE(CClC.EfChd,0) = 0 THEN NULL ELSE CClC.EfChd END as \"EfChd\",
				CClC.EfPulsks as \"EfPulsks\",
				CClC.EfGluck as \"EfGluck\",
				CClC.Kilo as \"Kilo\",
				CClC.Lpu_id as \"Lpu_id\",
				CClC.HelpPlace as \"HelpPlace\",
				CClC.HelpAuto as \"HelpAuto\",
				CClC.DescText as \"DescText\",
				CClC.CmpCloseCard_Epid as \"CmpCloseCard_Epid\",
				CClC.CmpCloseCard_Glaz as \"CmpCloseCard_Glaz\",
				CClC.CmpCloseCard_GlazAfter as \"CmpCloseCard_GlazAfter\",
				CClC.CmpCloseCard_m1 as \"CmpCloseCard_m1\",
				CClC.CmpCloseCard_e1 as \"CmpCloseCard_e1\",
				CClC.CmpCloseCard_v1 as \"CmpCloseCard_v1\",
				CClC.CmpCloseCard_m2 as \"CmpCloseCard_m2\",
				CClC.CmpCloseCard_e2 as \"CmpCloseCard_e2\",
				CClC.CmpCloseCard_v2 as \"CmpCloseCard_v2\",
				CClC.CmpCloseCard_Topic as \"CmpCloseCard_Topic\",
				CClC.CmpCloseCard_IsProfile as \"CmpCloseCard_IsProfile\",
				CClC.CmpCloseCard_IsVomit as \"IsVomit\",
				CClC.CmpCloseCard_IsDiuresis as \"IsDiuresis\",
				CClC.CmpCloseCard_IsDefecation as \"IsDefecation\",
				CClC.CmpCloseCard_IsTrauma as \"IsTrauma\",
				CClC.CmpCloseCard_Sat as \"Sat\",
				CClC.CmpCloseCard_AfterSat as \"AfterSat\",
				to_char(cast(CClC.CmpCloseCard_HelpDT as timestamp), 'hh24:mi') as \"HelpDT\",
				CClC.HelpAuto as \"HelpAuto\",
				CClC.CmpCloseCard_Rhythm as \"CmpCloseCard_Rhythm\",
				CClC.CmpCloseCard_AfterRhythm as \"CmpCloseCard_AfterRhythm\",
				CClC.DescText as \"DescText\",
				UCA.PMUser_Name as \"FeldsherAcceptName\",
				UCT.PMUser_Name as \"FeldsherTransName\"


				,to_char(CCC.CmpCallCard_insDT, 'dd.mm.yyyy') as \"CallCardDate\"
				,LB.LpuBuilding_Name as \"LpuBuilding_Name\"
				,EMT.EmergencyTeam_Num as \"EmergencyTeam_Num\"

				,CASE WHEN CCC.CmpCallCard_Dsp1 IS NOT NULL THEN CCC.CmpCallCard_Dsp1 ELSE '' END as \"Dsp_prm\"
				,CASE WHEN CCC.CmpCallCard_Dsp2 IS NOT NULL THEN CCC.CmpCallCard_Dsp2 ELSE '' END as \"Dsp_per\"

				,convert(varchar(5), CCC.CmpCallCard_Tper, 108) as \"PerTime\"
				--,CASE WHEN MP.Person_Fio IS NOT NULL THEN MP.Person_Fio ELSE CCC.MedStaffFact_id END as \"Doc\"
				,msf.Person_FIO as \"Doc\"
				,MSFCID.Person_FIO as \"DocCid\"
				,DATEDIFF('mm',CCC.CmpCallCard_prmDT,CCC.CmpCallCard_Tisp)
				,KL_AR.KLArea_Name as \"Area\"
				,CCC.KLCity_id as \"KLCity_id\"
				,KL_CITY.KLCity_Name as \"City\"
				,CCC.KLTown_id as \"KLTown_id\"
				,KL_TOWN.KLTown_Name as \"Town\"
				,CCC.KLStreet_id as \"KLStreet_id\"

				,COALESCE(KL_ST.KLStreet_Name, LOWER(CClC.CmpCloseCard_Street),CCC.CmpCallCard_Ulic, '') as \"Street\"

				,case when SecondStreet.KLStreet_FullName is not null then
					case when socrSecondStreet.KLSocr_Nick is not null then UPPER(socrSecondStreet.KLSocr_Nick)||'. '||SecondStreet.KLStreet_Name else
					SecondStreet.KLStreet_FullName end
					else ''
				end as \"secondStreetName\"

				,CCC.CmpCallCard_IsReceivedInPPD as \"CmpCallCard_IsReceivedInPPD\"
				,CCC.CmpCallCard_Dom as \"House\"
				,Lpu.Lpu_Nick as \"Lpu_name\"
				,Lpu.UAddress_Address as \"UAddress_Address\"
				,Lpu.Lpu_Phone as \"Lpu_Phone\"

				,case when KL_CITY.KLCity_Name is not null then 'г. '||KL_CITY.KLCity_Name else '' end ||
				case when KL_TOWN.KLTown_FullName is not null then
					case when (KL_CITY.KLCity_Name is not null) then ', '||LOWER(KL_TOWN.KLSocr_Nick)||'. '||KL_TOWN.KLTown_Name else LOWER(KL_TOWN.KLSocr_Nick)||'. '||KL_TOWN.KLTown_Name end
				else '' end ||
				case when KL_ST.KLStreet_Name is not null then ',<br> '||LOWER(socrStreet.KLSocr_Nick)||'. '||KL_ST.KLStreet_Name  else '' end ||
				case when CCC.CmpCallCard_Dom is not null then ', д.'||CCC.CmpCallCard_Dom else '' end ||
				case when CCC.CmpCallCard_Korp is not null then ', к.'||CCC.CmpCallCard_Korp else '' end ||
				case when CCC.CmpCallCard_Kvar is not null then ', кв.'||CCC.CmpCallCard_Kvar else '' end ||
				case when CCC.CmpCallCard_Kodp is not null then '</br>Код подъезда: '||CCC.CmpCallCard_Kodp else '' end ||
				case when CCC.CmpCallCard_Comm is not null then '</br>'||CCC.CmpCallCard_Comm else '' end as \"Address_Address\"


				,CCC.CmpCallCard_Korp as \"Korpus\"
				,CCC.CmpCallCard_Kvar as \"Office\"
				,CASE WHEN COALESCE(CCC.CmpCallCard_Podz,0) = 0 THEN '' ELSE CCC.CmpCallCard_Podz END as \"Entrance\"
				,CASE WHEN COALESCE(CCC.CmpCallCard_Etaj,0) = 0 THEN '' ELSE CCC.CmpCallCard_Etaj END as \"Level\"
				,CCC.CmpCallCard_Kodp as \"CodeEntrance\"

				,case when CCC.Person_SurName is not null then CCC.Person_SurName || '<br>' else '' end ||
				case when CCC.Person_FirName is not null then CCC.Person_FirName || '<br>' else '' end ||
				case when CCC.Person_SecName is not null then CCC.Person_SecName || '<br>' else '' end as \"FIO\"
				,CCC.Person_Age as \"Age\"
				,to_char(PS.Person_BirthDay, 'dd.mm.yyyy') as \"BirthDay\"
				,case when
					DATEDIFF('yy',coalesce(PS.Person_BirthDay, CCC.Person_BirthDay,'01.01.2000'::date),dbo.tzGetDate())>1
				then
					'лет'
				else
					case when
						DATEDIFF('mm',coalesce(PS.Person_BirthDay, CCC.Person_BirthDay),dbo.tzGetDate())>1
					then
						'мес'
					else
						'дн'
					end
				end as \"AgeTypeValue\"
				,case when
					DATEDIFF('yy',coalesce(PS.Person_BirthDay, CCC.Person_BirthDay,'01.01.2000'::date),dbo.tzGetDate())>1
				then
					case when
						COALESCE(PS.Person_BirthDay, CCC.Person_BirthDay,0)=0
					then
						''
					else
						DATEDIFF('yy',coalesce(PS.Person_BirthDay, CCC.Person_BirthDay),dbo.tzGetDate())
					end
				else
					case when
						DATEDIFF('mm',coalesce(PS.Person_BirthDay, CCC.Person_BirthDay),dbo.tzGetDate())>1
					then
						DATEDIFF('mm',coalesce(PS.Person_BirthDay, CCC.Person_BirthDay),dbo.tzGetDate())
					else
						DATEDIFF('dd',coalesce(PS.Person_BirthDay, CCC.Person_BirthDay),dbo.tzGetDate())
					end
				end as \"AgePS\"
				,CClC.Age as \"Age\"

				,SX.Sex_name as \"Sex_name\"
				,RS.CmpReason_Name as \"Reason\"
				,RS.CmpReason_Code as \"ReasonCode\"

				--,SS.SocStatus_Name as \"C_PersonSocial_id\"

				-- ,CCC.CmpCallCard_Ktov
				,CCC.CmpCallCard_Telf as \"Phone\"

				,CCC.CmpCallType_id as \"CmpCallType_id\"
				,CCC.CmpCallCard_Comm as \"CmpCallCard_Comm\"
				,CCT.CmpCallType_Code as \"CmpCallType_Code\"
				, CASE WHEN CCRT.CmpCallerType_Name IS NOT NULL THEN CCRT.CmpCallerType_Name ELSE CCC.CmpCallCard_Ktov END as \"CmpCallerType_Name\"
				-- ,CCT.CmpCallType_Name as \"CallType\"
				,CCC.CmpCallCard_IsReceivedInPPD as \"CmpCallCard_IsReceivedInPPD\"
				,DIAG.Diag_FullName as \"main_Diag_Name\"
				,DIAG.Diag_Code as \"main_Diag_Code\"
				,UDIAG.Diag_FullName as \"s_Diag_Name\"
				,UDIAG.Diag_Code as \"s_Diag_Code\"
			FROM
				{$this->schema}.v_CmpCloseCard CClC 
				LEFT JOIN v_CmpCallCard CCC  on CCC.CmpCallCard_id = CClC.CmpCallCard_id
				LEFT JOIN v_Diag DIAG  on DIAG.Diag_id = CClC.Diag_id
				LEFT JOIN v_Diag UDIAG  on UDIAG.Diag_id = CClC.Diag_sid
				LEFT JOIN v_PersonState PS  on PS.Person_id = CCC.Person_id
				LEFT JOIN v_Lpu L  on L.Lpu_id = CClC.Lpu_id
				LEFT JOIN v_EmergencyTeam EMT  on EMT.EmergencyTeam_id = CCC.EmergencyTeam_id
				LEFT JOIN v_pmUserCache UCA  on UCA.PMUser_id = CClC.pmUser_insID
				LEFT JOIN v_pmUserCache UCT  on UCT.PMUser_id = CClC.FeldsherTrans

				LEFT JOIN v_KLStreet SecondStreet  on SecondStreet.KLStreet_id = CClC.CmpCloseCard_UlicSecond
				LEFT JOIN v_KLSocr socrSecondStreet  on SecondStreet.KLSocr_id = socrSecondStreet.KLSocr_id

				LEFT JOIN Sex SX  on SX.Sex_id = CCC.Sex_id

				LEFT JOIN v_CmpReason RS  on RS.CmpReason_id = CCC.CmpReason_id
				LEFT JOIN KLStreet KL_ST  on KL_ST.KLStreet_id = CClC.Street_id
				LEFT JOIN KLArea KL_AR  on KL_AR.KLArea_id = CClC.Area_id
				left join v_KLSocr socrStreet  on KL_ST.KLSocr_id = socrStreet.KLSocr_id
				LEFT JOIN v_KLCity KL_CITY  on KL_CITY.KLCity_id = CClC.City_id
				LEFT JOIN v_KLTown KL_TOWN  on KL_TOWN.KLTown_id = CClC.Town_id
				LEFT JOIN v_CmpCallType CCT  on CCT.CmpCallType_id = CCC.CmpCallType_id
				LEFT JOIN v_CmpCallerType CCRT  on CCRT.CmpCallerType_id = CCC.CmpCallerType_id
				left join v_Lpu Lpu  on Lpu.Lpu_id = CCC.Lpu_id
                LEFT JOIN v_MedStaffFact msf on msf.MedPersonal_id =  COALESCE(EMT.EmergencyTeam_HeadShift, CClC.MedStaffFact_id, null)
				--LEFT JOIN v_MedStaffFact MSF  on MSF.MedStaffFact_id = CClC.MedStaffFact_id
				LEFT JOIN v_MedStaffFact MSFCID  on MSFCID.MedStaffFact_id = CClC.MedStaffFact_cid
				LEFT JOIN v_MedPersonal MP  on MP.MedPersonal_id = msf.MedStaffFact_id
				LEFT JOIN v_LpuBuilding LB  on LB.LpuBuilding_id = CCC.LpuBuilding_id
				-- left join v_SocStatus SS  on SS.SocStatus_id = CLC.SocStatus_id

				left join lateral (
					select
						CCCS.CmpCallCardStatus_insDT as TransTime,
						CCCS.pmUser_insID as FeldsherTransPmUser_id
					from
						v_CmpCallCardStatus CCCS
					where
						CCCS.CmpCallCard_id = CClC.CmpCallCard_id
						and CCCS.CmpCallCardStatusType_id = 2
					order by
						CCCS.pmUser_insID desc
					limit 1
				) as CCCStatusData on true
			".ImplodeWherePH( $where )."
			limit 1
		";

		$query = $this->db->query( $sql, $params );
		if ( is_object( $query ) ) {
			$response = $query->result_array();
		}
		else
			return false;

		$sql = "

			SELECT
				vper.Person_SurName as \"Person_SurName\",
				vper.Person_SecName as \"Person_SecName\",
				vper.Person_FirName as \"Person_FirName\",
				-- если человек имеет федеральную льготу, то устанавливаем Server_pid = 1
				-- убрать, когда будет сделан адекватный механизм сбора атрибутов человека
				--CASE WHEN ( (fedl.Person_id is not null) ) THEN 1 ELSE -1 END as Server_pid,
				-- федеральный льготник
				case
					when PersonPrivilegeFed.Person_id is not null then 1
					else 0
				end as \"Person_IsFedLgot\",
				vper.Server_pid as \"Server_pid\",
				vper.Person_IsInErz as \"Person_IsInErz\",
				vper.PersonIdentState_id as \"PersonIdentState_id\",
				vper.Person_id as \"Person_id\",
				to_char(cast(vper.Person_BirthDay as timestamp),'dd.mm.yyyy') as \"Person_BirthDay\",
				vper.Sex_id as \"PersonSex_id\",
				case
					when length(vper.Person_Snils) = 11 then left(vper.Person_Snils, 3) || '-' || substring(vper.Person_Snils, 4, 3) || '-' ||
						substring(vper.Person_Snils, 7, 3) || '-' || right(vper.Person_Snils, 2)
					else vper.Person_Snils
				end as \"Person_SNILS\",
				vper.SocStatus_id as \"SocStatus_id\",
				vper.FamilyStatus_id as \"FamilyStatus_id\",
				vper.PersonFamilyStatus_IsMarried as \"PersonFamilyStatus_IsMarried\",
				vper.Person_edNum as \"Federal_Num\",
				vper.UAddress_id as \"UAddress_id\",
				uaddr.PersonSprTerrDop_id as \"UPersonSprTerrDop_id\",
				uaddr.Address_Zip as \"UAddress_Zip\",
				uaddr.KLCountry_id as \"UKLCountry_id\",
				uaddr.KLRGN_id as \"UKLRGN_id\",
				uaddr.KLSubRGN_id as \"UKLSubRGN_id\",
				uaddr.KLCity_id as \"UKLCity_id\",
				uaddr.KLTown_id as \"UKLTown_id\",
				uaddr.KLStreet_id as \"UKLStreet_id\",
				uaddr.Address_House as \"UAddress_House\",
				uaddr.Address_Corpus as \"UAddress_Corpus\",
				uaddr.Address_Flat as \"UAddress_Flat\",
				uaddrsp.AddressSpecObject_id as \"UAddressSpecObject_id\",
				uaddrsp.AddressSpecObject_Name as \"UAddressSpecObject_Value\",
				uaddr.Address_Address as \"UAddress_AddressText\",
				uaddr.Address_Address as \"UAddress_Address\",
				--convert(varchar,cast(uaddr.Address_begDate as timestamp),104) as \"UAddress_begDate\",

				baddr.PersonSprTerrDop_id as \"BPersonSprTerrDop_id\",
				baddr.Address_id as \"Address_id\",
				baddr.KLCountry_id as \"BKLCountry_id\",
				baddr.KLRGN_id as \"BKLRGN_id\",
				baddr.KLSubRGN_id as \"BKLSubRGN_id\",
				baddr.KLCity_id as \"BKLCity_id\",
				baddr.KLTown_id as \"BKLTown_id\",
				baddr.KLStreet_id as \"BKLStreet_id\",
				baddr.Address_House as \"BAddress_House\",
				baddr.Address_Corpus as \"BAddress_Corpus\",
				baddr.Address_Flat as \"BAddress_Flat\",
				baddrsp.AddressSpecObject_id as \"BAddressSpecObject_id\",
				baddrsp.AddressSpecObject_Name as \"BAddressSpecObject_Value\",
				baddr.Address_Zip as \"BAddress_Zip\",
				baddr.Address_Address as \"BAddress_AddressText\",
				baddr.Address_Address as \"BAddress_Address\",
				pcc.PolisCloseCause_Code as \"polisCloseCause\",
				vper.PAddress_id as \"PAddress_id\",
				paddr.PersonSprTerrDop_id as \"PPersonSprTerrDop_id\",
				paddr.Address_Zip as \"PAddress_Zip\",
				paddr.KLCountry_id as \"PKLCountry_id\",
				paddr.KLRGN_id as \"PKLRGN_id\",
				paddr.KLSubRGN_id as \"PKLSubRGN_id\",
				paddr.KLCity_id as \"PKLCity_id\",
				paddr.KLTown_id as \"PKLTown_id\",
				paddr.KLStreet_id as \"PKLStreet_id\",
				paddr.Address_House as \"PAddress_House\",
				paddr.Address_Corpus as \"PAddress_Corpus\",
				paddr.Address_Flat as \"PAddress_Flat\",
				paddrsp.AddressSpecObject_id as \"PAddressSpecObject_id\",
				paddrsp.AddressSpecObject_Name as \"PAddressSpecObject_Value\",
				paddr.Address_Address as \"PAddress_AddressText\",
				paddr.Address_Address as \"PAddress_Address\",
				--convert(varchar,cast(paddr.Address_begDate as timestamp),104) as \"PAddress_begDate\",

				pi.Nationality_id as \"PersonNationality_id\",
				pol.OmsSprTerr_id as \"OMSSprTerr_id\",
				pol.PolisType_id as \"PolisType_id\",
				pol.Polis_Ser as \"Polis_Ser\",
				pol.PolisFormType_id as \"PolisFormType_id\",
				case when pol.PolisType_id = 4 then '' else pol.Polis_Num end as \"Polis_Num\",
				pol.OrgSmo_id as \"OrgSMO_id\",
				to_char(cast(pol.Polis_begDate as timestamp),'dd.mm.yyyy') as \"Polis_begDate\",
				to_char(cast(pol.Polis_endDate as timestamp),'dd.mm.yyyy') as \"Polis_endDate\",
				doc.DocumentType_id as \"DocumentType_id\",
				doc.Document_id as \"Document_id\",
				doc.Document_Ser as \"Document_Ser\",
				doc.Document_Num as \"Document_Num\",
				doc.OrgDep_id as \"OrgDep_id\",
				ns.KLCountry_id as \"KLCountry_id\",
				klc.KLCountry_Name as \"KLCountry_Name\",
				smo.Org_Name as \"SMO_Name\",
				case when ns.NationalityStatus_IsTwoNation = 2 then 1 else 0 end as \"NationalityStatus_IsTwoNation\",
				pjob.Org_id as \"Org_id\",
				pjob.OrgUnion_id as \"OrgUnion_id\",
				pjob.Post_id as \"Post_id\",
				to_char(cast(doc.Document_begDate as timestamp),'dd.mm.yyyy') as \"Document_begDate\",
				PDEP.DeputyKind_id as \"DeputyKind_id\",
				PDEP.Person_pid as \"DeputyPerson_id\",
				case when PDEPSTATE.Person_id is not null THEN PDEPSTATE.Person_SurName || ' ' || PDEPSTATE.Person_FirName || ' ' || COALESCE(PDEPSTATE.Person_SecName, '') ELSE '' END as \"DeputyPerson_Fio\",
				ResidPlace_id as \"ResidPlace_id\",
				PersonChild_IsManyChild as \"PersonChild_IsManyChild\",
				PersonChild_IsBad as \"PersonChild_IsBad\",
				PersonChild_IsYoungMother as \"PersonChild_IsYoungMother\",
				PersonChild_IsIncomplete as \"PersonChild_IsIncomplete\",
				PersonChild_IsInvalid as \"PersonChild_IsInvalid\",
				PersonChild_IsTutor as \"PersonChild_IsTutor\",
				PersonChild_IsMigrant as \"PersonChild_IsMigrant\",
				HealthKind_id as \"HealthKind_id\",
				ph.PersonHeight_IsAbnorm as \"PersonHeight_IsAbnorm\",
				ph.HeightAbnormType_id as \"HeightAbnormType_id\",
				pw.WeightAbnormType_id as \"WeightAbnormType_id\",
				pw.PersonWeight_IsAbnorm as \"PersonWeight_IsAbnorm\",
				PCh.PersonSprTerrDop_id as \"PersonSprTerrDop_id\",
				FeedingType_id as \"FeedingType_id\",
				InvalidKind_id as \"InvalidKind_id\",
				to_char(cast(PersonChild_invDate as timestamp),'dd.mm.yyyy') as \"PersonChild_invDate\",
				HealthAbnorm_id as \"HealthAbnorm_id\",
				HealthAbnormVital_id as \"HealthAbnormVital_id\",

				to_char(cast(vper.Person_deadDT as timestamp),'dd.mm.yyyy') as \"Person_deadDT\",
				to_char(cast(vper.Person_closeDT as timestamp),'dd.mm.yyyy') as \"Person_closeDT\",
				rtrim(vper.Person_Phone) as \"PersonPhone_Phone\",
				rtrim(pi.PersonInfo_InternetPhone) as \"PersonInfo_InternetPhone\",
				rtrim(vper.Person_Inn) as \"PersonInn_Inn\",
				rtrim(vper.Person_SocCardNum) as \"PersonSocCardNum_SocCardNum\",
				rtrim(Ref.PersonRefuse_IsRefuse) as \"PersonRefuse_IsRefuse\",
				rtrim(pce.PersonCarExist_IsCar) as \"PersonCarExist_IsCar\",
				rtrim(pche.PersonChildExist_IsChild) as \"PersonChildExist_IsChild\",
				ph.PersonHeight_Height as \"PersonHeight_Height\",
				COALESCE(pw.Okei_id, 37) as \"Okei_id\",
				pw.PersonWeight_Weight as \"PersonWeight_Weight\",
				-- признак того, что человек БДЗшный и у него закончился полис и можно дать ввести иногородний
				CASE WHEN
					vper.Server_pid = 0
					and pol.Polis_endDate is not null
					and pol.Polis_endDate < dbo.tzGetDate()
				THEN
					1
				ELSE
					0
				END as \"Polis_CanAdded\",
				pi.Ethnos_id as \"Ethnos_id\",
				mop.OnkoOccupationClass_id as \"OnkoOccupationClass_id\",
				per.BDZ_id as \"BDZ_id\",
				per.BDZ_Guid as \"BDZ_Guid\",
				pol.Polis_Guid as \"Polis_Guid\",
				IsUnknown.YesNo_Code as \"Person_IsUnknown\",
				IsAnonym.YesNo_Code as \"Person_IsAnonym\"
			from v_PersonState vper
			left join v_Person per on per.Person_id=vper.Person_id
			left join v_Address uaddr on vper.UAddress_id = uaddr.Address_id
			left join v_AddressSpecObject uaddrsp on uaddr.AddressSpecObject_id = uaddrsp.AddressSpecObject_id
			left join v_Address paddr on vper.PAddress_id = paddr.Address_id
			left join v_AddressSpecObject paddrsp on paddr.AddressSpecObject_id = paddrsp.AddressSpecObject_id
			-- Адрес рождения
			left join PersonBirthPlace pbp on vper.Person_id = pbp.Person_id
			left join v_Address baddr on pbp.Address_id = baddr.Address_id
			left join v_AddressSpecObject baddrsp on baddr.AddressSpecObject_id = baddrsp.AddressSpecObject_id
			-- end. Адрес рождения

			left join Polis pol on pol.Polis_id=vper.Polis_id
			left join v_OrgSMO orgsmo on orgsmo.OrgSMO_id=pol.OrgSMO_id
			left join v_Org smo on orgsmo.Org_id=smo.Org_id
			left join v_PolisCloseCause pcc on pol.PolisCloseCause_id = pcc.PolisCloseCause_id
			left join Document doc on doc.Document_id=vper.Document_id
			left join NationalityStatus ns on ns.NationalityStatus_id = vper.NationalityStatus_id
			left join v_KLCountry klc on ns.KLCountry_id=klc.KLCountry_id
			left join PersonInfo pi on pi.Person_id = vper.Person_id
			left join Job pjob on vper.Job_id = pjob.Job_id
			left join PersonDeputy PDEP on PDEP.Person_id = vper.Person_id
			left join v_PersonState PDEPSTATE on PDEPSTATE.Person_id = PDEP.Person_pid
			left join PersonChild PCh on PCh.Person_id = vper.Person_id
			left join v_YesNo IsUnknown on IsUnknown.YesNo_id = COALESCE(per.Person_IsUnknown,1)
			left join v_YesNo IsAnonym on IsAnonym.YesNo_id = COALESCE(per.Person_IsAnonym,1)
			-- федеральный льготник
			-- полис, который был в истории периодик
			-- outer apply (select top 1 pls1.Person_id, pls1.BDZ_id, pls1.Polis_endDate, pls1.PolisCloseCause_id from v_PersonPolis pls1 where pls1.Person_id = vper.Person_id and pls1.BDZ_id is not null order by Polis_begDate desc) as bdz_pol
			left join lateral (
				select
					pp.Person_id
				from
					v_PersonPrivilege pp
					inner join v_PrivilegeType pt on pt.PrivilegeType_id = pp.PrivilegeType_id
				where
					pt.ReceptFinance_id = 1
					and pp.PersonPrivilege_begDate <= dbo.tzGetDate()
					and (pp.PersonPrivilege_endDate is null or pp.PersonPrivilege_endDate >= cast(dbo.tzGetDate() as date))
					and pp.Person_id = vper.Person_id
				limit 1
			) PersonPrivilegeFed on true
			left join lateral (
				select
					OnkoOccupationClass_id
				from
					v_MorbusOnkoPerson
				where
					Person_id = :Person_id
				order by
					MorbusOnkoPerson_insDT desc
				limit 1
			) as mop on true
			left join lateral (
				select 
					PersonRefuse_IsRefuse
				from
					v_PersonRefuse
				where
					Person_id = :Person_id
					and PersonRefuse_Year = EXTRACT(YEAR FROM dbo.tzGetDate())
				order by
					PersonRefuse_insDT desc
				limit 1
			) as Ref on true
			left join lateral (
				select 
					PersonCarExist_IsCar
				from
					PersonCarExist 
				where
					Person_id = :Person_id
				order by
					PersonCarExist_setDT desc
				limit 1
			) as pce on true
			left join lateral (
				select
					PersonChildExist_IsChild
				from
					PersonChildExist
				where
					Person_id = :Person_id
				order by
					PersonChildExist_setDT desc
				limit 1
			) as pche on true
			left join lateral (
				select
					PersonHeight_Height,
					PersonHeight_IsAbnorm,
					HeightAbnormType_id
				from
					PersonHeight
				where
					Person_id = :Person_id
				order by
					PersonHeight_setDT desc
				limit 1
			) as ph on true
			left join lateral (
				select
					PersonWeight_Weight,
					WeightAbnormType_id,
					PersonWeight_IsAbnorm,
					Okei_id
				from
					PersonWeight
				where
					Person_id = :Person_id
				order by
					PersonWeight_setDT desc
				limit 1
			) as pw on true
			where vper.Person_id= :Person_id
			limit 1
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
		SELECT O.Org_Name as \"Org_Name\"
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
		SELECT ETS.EmergencyTeamSpec_Name as \"EmergencyTeamSpec_Name\"
		FROM EmergencyTeam AS ET
			INNER JOIN EmergencyTeamSpec AS ETS ON ETS.EmergencyTeamSpec_id = ET.EmergencyTeamSpec_id
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
				CCCM.CmpCloseCardCombo_Code as \"CmpCloseCardCombo_id\",
				CCCR.Localize as \"Localize\"
			FROM
				{$this->schema}.v_CmpCloseCard CCC
				LEFT JOIN {$this->schema}.v_CmpCloseCardRel CCCR on CCCR.CmpCloseCard_id = CCC.CmpCloseCard_id
				LEFT JOIN v_CmpCloseCardCombo CCCM on CCCM.CmpCloseCardCombo_id = CCCR.CmpCloseCardCombo_id
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
				MP.Person_Fio as \"MedPersonal_Fio\"
			from
				v_MedPersonal MP 
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
				RTRIM(o.Org_Nick) as \"Org_Nick\"
			FROM
				v_Org o
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
			select
				IsOms.YesNo_Code as \"DiagFinance_IsOms\",
				IsAlien.YesNo_Code as \"DiagFinance_IsAlien\",
				df.Sex_id as \"Diag_Sex\",
				a.PersonAgeGroup_Code as \"PersonAgeGroup_Code\",
				p.OmsSprTerr_Code as \"OmsSprTerr_Code\",
				p.Sex_id as \"Sex_id\",
				dbo.Age2(p.Person_BirthDay, dbo.tzGetDate()) as \"Age\",
				pt.PayType_SysNick as \"PayType_SysNick\"
			from
				v_DiagFinance df
				left join PersonAgeGroup a on a.PersonAgeGroup_id = df.PersonAgeGroup_id
				left join YesNo IsAlien on IsAlien.YesNo_id = df.DiagFinance_IsAlien
				left join YesNo IsOms on IsOms.YesNo_id = df.DiagFinance_IsOms
				left join lateral (
					select 
						ost.OmsSprTerr_Code,
						ps.Sex_id,
						ps.Person_BirthDay
					from
						v_PersonState ps
						left join v_Polis pls on pls.Polis_id = ps.Polis_id
						left join v_OmsSprTerr ost on ost.OmsSprTerr_id = pls.OmsSprTerr_id
					where ps.Person_id = :Person_id
					limit 1
				) p on true
				left join v_PayType pt on pt.PayType_id = :PayType_id
			where
				df.Diag_id = :Diag_id
			limit 1
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
                    CmpCloseCard_id as \"CmpCloseCard_id\"
                from
                    v_CmpCloseCard
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
                    CmpCallCardDrug_id as \"CmpCallCardDrug_id\",
                    DocumentUcStr_id as \"DocumentUcStr_id\"
                from
                    v_CmpCallCardDrug
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
				select
                    Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
                from p_CmpCallCard_del (
                    CmpCallCard_id := :CmpCallCard_id,
                    pmUser_id := :pmUser_id
                );
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
                select
                    Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\" 
				from p_CmpCloseCard_del (
                    CmpCloseCard_id := :CmpCloseCard_id,
                    pmUser_id := :pmUser_id
                );
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
