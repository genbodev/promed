<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Buryatiya_CmpCallCardModel - модель для работы с картами вызова СМП. Версия для Карелии
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package            Common
 * @access            public
 * @copyright        Copyright (c) 2013 Swan Ltd.
 * @author            Popkov Sergey
 * @version            Buryatiya
 */

require_once(APPPATH . 'models/_pgsql/CmpCallCard_model.php');

class Buryatiya_CmpCallCard_model extends CmpCallCard_model
{
	/**
	 * default desc
	 */

	/*function loadCmpCloseCardViewForm($data)
	{
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
			select
				CClC.CmpCallCard_id as \"CmpCallCard_id\",
				CClC.CmpCloseCard_id as \"CmpCloseCard_id\",
				CCC.CmpReason_id as \"CmpReason_id\",
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
				CClC.PayType_id as \"PayType_id\",
				CCC.KLRgn_id as \"KLRgn_id\",
				CClC.Area_id as Area_id as \"Area_id as Area_id\",
				CClC.City_id as City_id as \"City_id as City_id\",
				CClC.Town_id as Town_id as \"Town_id as Town_id\",
				CClC.Street_id as Street_id as \"Street_id as Street_id\",
				CClC.Room as \"Room\",
				CClC.Korpus as \"Korpus\",
				CClC.EmergencyTeamNum as EmergencyTeamNum as \"EmergencyTeamNum as EmergencyTeamNum\",
				CCLC.EmergencyTeam_id as EmergencyTeam_id as \"EmergencyTeam_id as EmergencyTeam_id\",
				CClC.EmergencyTeamSpec_id as EmergencyTeamSpec_id as \"EmergencyTeamSpec_id as EmergencyTeamSpec_id\",
				CClC.LpuSection_id as LpuSection_id as \"LpuSection_id as LpuSection_id\",
				CClC.MedPersonal_id as MedPersonal_id as \"MedPersonal_id as MedPersonal_id\",
				CClC.MedPersonal_id as MedStaffFact_id as \"MedPersonal_id as MedStaffFact_id\",
				CClC.StationNum as StationNum as \"StationNum as StationNum\",
				CClC.LpuBuilding_id as \"LpuBuilding_id\",
				CClC.pmUser_insID as Feldsher_id as \"pmUser_insID as Feldsher_id\",
				CCLC.FeldsherAccept as \"FeldsherAccept\",
				CClC.FeldsherTrans as \"FeldsherTrans\",
				CClC.isSogl as \"isSogl\",
				CClC.isOtkazMed as \"isOtkazMed\",
				CClC.isOtkazHosp as \"isOtkazHosp\",
				to_char(CClC.AcceptTime, 'dd.mm.yyyy hh24:mi') as \"AcceptTime\",
				to_char(CClC.TransTime, 'dd.mm.yyyy hh24:mi') as \"TransTime\",
				to_char(CClC.GoTime, 'dd.mm.yyyy hh24:mi') as \"GoTime\",
				to_char(CClC.ArriveTime, 'dd.mm.yyyy hh24:mi') as \"ArriveTime\",
				to_char(CClC.TransportTime, 'dd.mm.yyyy hh24:mi') as \"TransportTime\",
				to_char(CClC.ToHospitalTime, 'dd.mm.yyyy hh24:mi') as \"ToHospitalTime\",
				to_char(CClC.EndTime, 'dd.mm.yyyy hh24:mi') as \"EndTime\",
				to_char(CClC.BackTime, 'dd.mm.yyyy hh24:mi') as \"BackTime\",
				CClC.SummTime as \"SummTime\",
				CClC.Work as \"Work\",
				CClC.DocumentNum as \"DocumentNum\",
				CClC.CallType_id as \"CallType_id\",
				CASE WHEN coalesce(CCC.CmpCallCard_IsReceivedInPPD,1) = 1 THEN NULL ELSE 1 END as \"CmpCallCard_IsReceivedInPPD\",
				CClC.CallPovod_id as \"CallPovod_id\"				,
				CASE WHEN coalesce(CClC.isAlco,0) = 0 THEN NULL ELSE CClC.isAlco END as \"isAlco\",
				CClC.Complaints as \"Complaints\",
				CClC.Anamnez as \"Anamnez\",
				CASE WHEN coalesce(CClC.isMenen,0) = 0 THEN NULL ELSE CClC.isMenen END as \"isMenen\",
				CASE WHEN coalesce(CClC.isAnis,0) = 0 THEN NULL ELSE CClC.isAnis END as \"isAnis\",
				CASE WHEN coalesce(CClC.isNist,0) = 0 THEN NULL ELSE CClC.isNist END as \"isNist\",
				CASE WHEN coalesce(CClC.isLight,0) = 0 THEN NULL ELSE CClC.isLight END as \"isLight\",
				CASE WHEN coalesce(CClC.isAcro,0) = 0 THEN NULL ELSE CClC.isAcro END as \"isAcro\",
				CASE WHEN coalesce(CClC.isMramor,0) = 0 THEN NULL ELSE CClC.isMramor END as \"isMramor\",
				CASE WHEN coalesce(CClC.isHale,0) = 0 THEN NULL ELSE CClC.isHale END as \"isHale\",
				CASE WHEN coalesce(CClC.isPerit,0) = 0 THEN NULL ELSE CClC.isPerit END as \"isPerit\",
				CClC.Urine as \"Urine\",
				CClC.Shit as \"Shit\",
				CClC.OtherSympt as \"OtherSympt\",
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
				CClC.EfAD as \"EfAD\",
				CASE WHEN coalesce(CClC.EfChss,0) = 0 THEN NULL ELSE CClC.EfChss END as \"EfChss\",
				CASE WHEN coalesce(CClC.EfPulse,0) = 0 THEN NULL ELSE CClC.EfPulse END as \"EfPulse\",
				CClC.EfTemperature as \"EfTemperature\",
				CASE WHEN coalesce(CClC.EfChd,0) = 0 THEN NULL ELSE CClC.EfChd END as \"EfChd\",
				CClC.EfPulsks as \"EfPulsks\",
				CClC.EfGluck as \"EfGluck\",
				CClC.Kilo as \"Kilo\",
				CClC.Lpu_id as \"Lpu_id\",
				CClC.HelpPlace as \"HelpPlace\",
				CClC.HelpAuto as \"HelpAuto\",
				CClC.DescText as \"DescText\",
				UCA.PMUser_Name as \"FeldsherAcceptName\",
				UCT.PMUser_Name as \"FeldsherTransName\"
			from
				v_CmpCloseCard CClC
				left join v_CmpCallCard CCC on CCC.CmpCallCard_id = CClC.CmpCallCard_id
				left join v_PersonState PS on PS.Person_id = CCC.Person_id
				LEFT JOIN v_Lpu L on L.Lpu_id = CClC.Lpu_id	
				LEFT JOIN v_EmergencyTeam EMT on EMT.EmergencyTeam_id = CCC.EmergencyTeam_id	
				LEFT JOIN v_pmUserCache UCA on UCA.PMUser_id = CClC.pmUser_insID
				LEFT JOIN v_pmUserCache UCT on UCT.PMUser_id = CClC.FeldsherTrans
				left join lateral(
					select
						CCCS.CmpCallCardStatus_insDT as TransTime,
						CCCS.pmUser_insID as FeldsherTransPmUser_id
					from v_CmpCallCardStatus CCCS
					where CCCS.CmpCallCard_id = CClC.CmpCallCard_id
						and CCCS.CmpCallCardStatusType_id = 2
					order by CCCS.pmUser_insID desc
					limit 1
				) as CCCStatusData on true
		   where
			   {$filter}
			limit 1
	   ";

		//LEFT JOIN v_pmUser P on P.PMUser_id = CCC.pmUser_updID

		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}*/


	/**
	 * @desc Сохранение формы 110у с набором полей для Карелии
	 * @param array $data
	 * @return boolean
	 */
	
	/*function saveCmpCloseCard110($data) {		
		$action = null;
		$oldresult = null;
		$NewCmpCloseCard_id = null;

		$rules = array(
			array( 'field' => 'Kilo' , 'label' => 'Километраж' , 'type' => 'float', 'maxValue' => '1000' ),
		);

		$queryParams = $this->_checkInputData( $rules , $data , $err , true ) ;
		if ( !empty( $err ) )
			return $err ;

		if ( isset( $data[ 'CmpCloseCard_id' ] ) && $data[ 'CmpCloseCard_id' ] ) {
			$action = 'edit';

			$procedure = 'p_CmpCloseCard_upd';
			$relProcedure = 'p_CmpCloseCardRel_ins';
		} else {
			$query = "
				SELECT
					CLC.CmpCloseCard_id as \"CmpCloseCard_id\"
				FROM
					{$this->schema}.v_CmpCloseCard CLC
				WHERE
					CLC.CmpCallCard_id = :CmpCallCard_id
			";
			$result = $this->db->query( $query, array(
				'CmpCallCard_id' => $data[ 'CmpCallCard_id' ]
			) );
			$retrun = $result->result( 'array' );

			if ( sizeof( $retrun ) ) {
				$data[ 'CmpCloseCard_id' ] = $retrun[ 0 ][ 'CmpCloseCard_id' ];
				$action = 'edit';
				$procedure = 'p_CmpCloseCard_upd';
				$relProcedure = 'p_CmpCloseCardRel_ins';
			} else {
				$action = 'add';
				$procedure = 'p_CmpCloseCard_ins';
				$relProcedure = 'p_CmpCloseCardRel_ins';
			}
		}

		$UnicNums = ';';
		if ( isset( $data[ 'CmpCloseCard_prmTime' ] ) ) {
			$data[ 'CmpCloseCard_prmDate' ] .= ' '.$data[ 'CmpCloseCard_prmTime' ].':00';
		}
		//Добавил проверки на новые поля, которых нет в CmpCallCard
		if ( !isset( $data[ 'Korpus' ] ) ) {
			$data[ 'Korpus' ] = '';
		}
		if ( !isset( $data[ 'Room' ] ) ) {
			$data[ 'Room' ] = '';
		}

		//Приводим данные в полях с типом datetime к виду, в котором их принимает БД
		$timeFiledsNames = array(
			'AcceptTime',
			'TransTime',
			'GoTime',
			'ArriveTime',
			'TransportTime',
			'CmpCloseCard_TranspEndDT',
			'ToHospitalTime',
			'BackTime',
			'EndTime'
		);

		foreach( $timeFiledsNames as $key => $timeFieldName ){
			if ( !empty( $data[ $timeFieldName ] ) ) {
				if ( isset( $data[ $timeFieldName ] ) && $data[ $timeFieldName ] != '' )
					$data[ $timeFieldName ] = substr( $data[ $timeFieldName ], 3, 3 ).substr( $data[ $timeFieldName ], 0, 3 ).substr( $data[ $timeFieldName ], 6, 10 );
			}
		}

		if ( $data[ 'Entrance' ] == 0 || $data[ 'Entrance' ] == '' )
			$data[ 'Entrance' ] = null;
		if ( $data[ 'Level' ] == 0 || $data[ 'Level' ] == '' )
			$data[ 'Level' ] = null;
		$queryParams = array(
			'CmpCallCard_id' => $data[ 'CmpCallCard_id' ],
			'Day_num' => $data[ 'Day_num' ],
			'Year_num' => $data[ 'Year_num' ],
			'Feldsher_id' => !empty($data[ 'Feldsher_id' ]) ? $data[ 'Feldsher_id' ] : null,
			'StationNum' =>(!empty( $data['StationNum'] )) ? $data[ 'StationNum' ] : null,
			'LpuBuilding_id' => (!empty( $data['LpuBuilding_id'] ) ? $data['LpuBuilding_id'] : null),
			'EmergencyTeamNum' => $data[ 'EmergencyTeamNum' ],
			'EmergencyTeam_id' => !empty($data['EmergencyTeam_id']) ? $data['EmergencyTeam_id'] : null,
			'EmergencyTeamSpec_id' => !empty($data['EmergencyTeamSpec_id']) ? $data['EmergencyTeamSpec_id'] : null,

			'PayType_id' => !empty($data['PayType_id']) ? $data['PayType_id'] : null,

			'AcceptTime' => (isset( $data[ 'AcceptTime' ])) ? $data[ 'AcceptTime' ] : null,
			'TransTime' => (isset( $data[ 'TransTime' ])) ? $data[ 'TransTime' ] : null,
			'GoTime' => (isset( $data[ 'GoTime' ])) ? $data[ 'GoTime' ] : null,
			'ArriveTime' => (isset( $data[ 'ArriveTime' ])) ? $data[ 'ArriveTime' ] : null,
			'TransportTime' => (isset( $data[ 'TransportTime' ])) ? $data[ 'TransportTime' ] : null,
			'ToHospitalTime' => (isset( $data[ 'ToHospitalTime' ])) ? $data[ 'ToHospitalTime' ] : null,
			'EndTime' => (isset( $data[ 'EndTime' ])) ? $data[ 'EndTime' ] : null,
			'BackTime' => (isset( $data[ 'BackTime' ])) ? $data[ 'BackTime' ] : null,
			'SummTime' => (isset( $data[ 'SummTime' ])) ? $data[ 'SummTime' ] : null,
			'CmpCloseCard_TranspEndDT' => (isset( $data[ 'CmpCloseCard_TranspEndDT' ])) ? $data[ 'CmpCloseCard_TranspEndDT' ] : null,
			'Area_id' => (int)$data[ 'Area_id' ] ? (int)$data[ 'Area_id' ] : null,
			'City_id' => (int)$data[ 'City_id' ] ? (int)$data[ 'City_id' ] : null,
			'Town_id' => (int)$data[ 'Town_id' ] ? (int)$data[ 'Town_id' ] : null,
			//'Street_id' => (!empty( $data['Street_id'] )) ? (int)$data[ 'Street_id' ] : null,
			'Street_id' => (isset( $data[ 'Street_id' ]) && !isset( $data[ 'CmpCloseCard_Street' ] ) && $data[ 'Street_id' ] > 0 ) ? $data[ 'Street_id' ] : null,
			'CmpCloseCard_Street' => (!empty( $data['CmpCloseCard_Street'] )) ? $data[ 'CmpCloseCard_Street' ] : null,
			'House' => $data[ 'House' ],
			'Korpus' => $data[ 'Korpus' ],
			'Office' => $data[ 'Office' ],
			'Room' => $data[ 'Room' ],
			'Entrance' => $data[ 'Entrance' ],
			'Level' => $data[ 'Level' ],
			'CodeEntrance' => $data[ 'CodeEntrance' ],
			'MedStaffFact_id' => (!empty( $data['MedStaffFactDoc_id'] ) ? $data['MedStaffFactDoc_id'] : null),
			'CmpCloseCard_IsHeartNoise' => (!empty( $data['CmpCloseCard_IsHeartNoise'] ) ? $data['CmpCloseCard_IsHeartNoise'] : null),
			'CmpCloseCard_IsIntestinal' => (!empty( $data['CmpCloseCard_IsIntestinal'] ) ? $data['CmpCloseCard_IsIntestinal'] : null),
			'CmpCloseCard_IsDiuresis' => (!empty( $data['CmpCloseCard_IsDiuresis'] ) ? $data['CmpCloseCard_IsDiuresis'] : null),
			'CmpCloseCard_IsVomit' => (!empty( $data['CmpCloseCard_IsVomit'] ) ? $data['CmpCloseCard_IsVomit'] : null),
			'CmpCloseCard_IsDefecation' => (!empty( $data['CmpCloseCard_IsDefecation'] ) ? $data['CmpCloseCard_IsDefecation'] : null),
			'CmpCloseCard_IsTrauma' => (!empty( $data['CmpCloseCard_IsTrauma'] ) ? $data['CmpCloseCard_IsTrauma'] : null),
			'CmpCloseCard_BegTreatDT' => (!empty( $data['CmpCloseCard_BegTreatDT'] ) ? $data['CmpCloseCard_BegTreatDT'] : null),
			'CmpCloseCard_EndTreatDT' => (!empty( $data['CmpCloseCard_EndTreatDT'] ) ? $data['CmpCloseCard_EndTreatDT'] : null),
			'CmpCloseCard_HelpDT' => (!empty( $data['CmpCloseCard_HelpDT'] ) ? $data['CmpCloseCard_HelpDT'] : null),
			'CmpCloseCard_Sat' => (!empty( $data['CmpCloseCard_Sat'] ) ? $data['CmpCloseCard_Sat'] : null),
			'CmpCloseCard_AfterSat' => (!empty( $data['CmpCloseCard_AfterSat'] ) ? $data['CmpCloseCard_AfterSat'] : null),
			'CmpCloseCard_Rhythm' => (!empty( $data['CmpCloseCard_Rhythm'] ) ? $data['CmpCloseCard_Rhythm'] : null),
			'CmpCloseCard_AfterRhythm' => (!empty( $data['CmpCloseCard_AfterRhythm'] ) ? $data['CmpCloseCard_AfterRhythm'] : null),
			'CmpLethalType_id' => (!empty( $data['CmpLethalType_id'] ) ? $data['CmpLethalType_id'] : null),
			'CmpCloseCard_LethalDT' => (!empty( $data['CmpCloseCard_LethalDT'] ) ? $data['CmpCloseCard_LethalDT'] : null),
			'Person_id' => (!empty( $data['Person_id'] ) ? $data['Person_id'] : null),
			'Fam' => $data[ 'Fam' ],
			'Name' => $data[ 'Name' ],
			'Middle' => $data[ 'Middle' ],
			'Age' => $data[ 'Age' ],
			'Sex_id' => $data[ 'Sex_id' ],
			'Work' => $data[ 'Work' ],
			'DocumentNum' => $data[ 'DocumentNum' ],
			'Ktov' => (!empty( $data[ 'Ktov' ] ) ? $data[ 'Ktov' ] : null),
			'CmpCallerType_id' => (!empty( $data['CmpCallerType_id'] ) ? $data['CmpCallerType_id'] : null),
			'Phone' => $data[ 'Phone' ],
			'SocStatus_id' => (!empty( $data[ 'SocStatus_id' ] ) ? $data[ 'SocStatus_id' ] : null),
			'CallPovodNew_id' => (!empty( $data[ 'CallPovodNew_id' ] ) ? $data[ 'CallPovodNew_id' ] : null),
			'FeldsherAccept' => $data[ 'FeldsherAccept' ],
			'FeldsherTrans' => $data[ 'FeldsherTrans' ],
			'CallType_id' => $data[ 'CallType_id' ],
			'CallPovod_id' => $data[ 'CallPovod_id' ],
			'isAlco' => (($data[ 'isAlco' ] > 0) ? $data[ 'isAlco' ] : null),
			'Complaints' => $data[ 'Complaints' ],
			'Anamnez' => $data[ 'Anamnez' ],
			'isMenen' => $data[ 'isMenen' ],
			'isNist' => $data[ 'isNist' ],
			'isAnis' => (isset( $data[ 'isAnis' ] ) && $data[ 'isAnis' ] != '') ? $data[ 'isAnis' ] : null,

			'isLight' => $data[ 'isLight' ],
			'isAcro' => (isset( $data[ 'isAcro' ] ) && $data[ 'isAcro' ] != '') ? $data[ 'isAcro' ] : null,
			'isMramor' => (isset( $data[ 'isMramor' ] ) && $data[ 'isMramor' ] != '') ? $data[ 'isMramor' ] : null,
			'isSogl' => (isset( $data[ 'isSogl' ] ) && $data[ 'isSogl' ] != '') ? $data[ 'isSogl' ] : null,
			'isOtkazMed' => (isset( $data[ 'isOtkazMed' ] ) && $data[ 'isOtkazMed' ] != '') ? $data[ 'isOtkazMed' ] : null,
			'isOtkazHosp' => (isset( $data[ 'isOtkazHosp' ] ) && $data[ 'isOtkazHosp' ] != '') ? $data[ 'isOtkazHosp' ] : null,
			'isOtkazSign' => (isset( $data[ 'isOtkazSign' ] ) && $data[ 'isOtkazSign' ] != '') ? $data[ 'isOtkazSign' ] : null,
			'OtkazSignWhy' => (isset( $data[ 'OtkazSignWhy' ] ) && $data[ 'OtkazSignWhy' ] != '') ? $data[ 'OtkazSignWhy' ] : null,
			'CmpCloseCard_IsExtra' => (isset( $data[ 'CmpCloseCard_IsExtra' ] ) && $data[ 'CmpCloseCard_IsExtra' ] != '') ? $data[ 'CmpCloseCard_IsExtra' ] : null,
			'CmpCloseCard_IsProfile' => (isset( $data[ 'CmpCloseCard_IsProfile' ] ) && $data[ 'CmpCloseCard_IsProfile' ] != '') ? $data[ 'CmpCloseCard_IsProfile' ] : null,
			'isHale' => $data[ 'isHale' ],
			'isPerit' => $data[ 'isPerit' ],
			'Urine' => (isset( $data[ 'Urine' ] ) && $data[ 'Urine' ] != '') ? $data[ 'Urine' ] : null,
			'Shit' => (isset( $data[ 'Shit' ] ) && $data[ 'Shit' ] != '') ? $data[ 'Shit' ] : null,
			'OtherSympt' => $data[ 'OtherSympt' ],
			'WorkAD' => $data[ 'WorkAD' ],
			'AD' => $data[ 'AD' ],
			'Chss' => $data[ 'Chss' ],
			'Pulse' => (isset( $data[ 'Pulse' ] ) && $data[ 'Pulse' ] != '') ? $data[ 'Pulse' ] : null,
			'Temperature' => $data[ 'Temperature' ],
			'Chd' => $data[ 'Chd' ],
			'Pulsks' => (isset( $data[ 'Pulsks' ] ) && $data[ 'Pulsks' ] != '') ? $data[ 'Pulsks' ] : null,
			'Gluck' => $data[ 'Gluck' ],
			'LocalStatus' => (isset( $data[ 'LocalStatus' ] ) && $data[ 'LocalStatus' ] != '') ? $data[ 'LocalStatus' ] : null,
			'Ekg1' => $data[ 'Ekg1' ],
			'Ekg1Time' => (isset( $data[ 'Ekg1Time' ] ) && $data[ 'Ekg1Time' ] != '') ? $data[ 'Ekg1Time' ] : null,
			'Ekg2' => $data[ 'Ekg2' ],
			'Ekg2Time' => (isset( $data[ 'Ekg2Time' ] ) && $data[ 'Ekg2Time' ] != '') ? $data[ 'Ekg2Time' ] : null,
			'Diag_id' => (isset( $data[ 'Diag_id' ] ) && $data[ 'Diag_id' ] != '') ? $data[ 'Diag_id' ] : null,
			'Diag_uid' => (isset( $data[ 'Diag_uid' ] ) && $data[ 'Diag_uid' ] != '') ? $data[ 'Diag_uid' ] : null,
			'Diag_sid' => (isset( $data[ 'Diag_sid' ] ) && $data[ 'Diag_sid' ] != '') ? $data[ 'Diag_sid' ] : null,
			'HelpPlace' => (isset( $data[ 'HelpPlace' ] ) && $data[ 'HelpPlace' ] != '') ? $data[ 'HelpPlace' ] : null,
			'HelpAuto' => $data[ 'HelpAuto' ],
			'EfAD' => $data[ 'EfAD' ],
			'EfChss' => $data[ 'EfChss' ],
			'EfPulse' => (isset( $data[ 'EfPulse' ] ) && $data[ 'EfPulse' ] != '') ? $data[ 'EfPulse' ] : null,
			'EfTemperature' => $data[ 'EfTemperature' ],
			'EfChd' => $data[ 'EfChd' ],
			'EfPulsks' => (isset( $data[ 'EfPulsks' ] ) && $data[ 'EfPulsks' ] != '') ? $data[ 'EfPulsks' ] : null,
			'EfGluck' => $data[ 'EfGluck' ],
			'Kilo' => (isset( $data[ 'Kilo' ] ) && $data[ 'Kilo' ] != '') ? $data[ 'Kilo' ] : null,
			'DescText' => (isset( $data[ 'DescText' ] ) && $data[ 'DescText' ] != '') ? $data[ 'DescText' ] : null,
			'pmUser_id' => $data[ 'pmUser_id' ],
			'CmpCloseCard_IsNMP' =>  (isset($data[ 'CmpCloseCard_IsNMP' ]) ? 2 : 1),
			'CmpCloseCard_Epid' => (isset($data[ 'CmpCloseCard_Epid' ]) ? $data[ 'CmpCloseCard_Epid' ] : null),
			'CmpCloseCard_Glaz' => (isset($data[ 'CmpCloseCard_Glaz' ]) ? $data[ 'CmpCloseCard_Glaz' ] : null),
			'CmpCloseCard_GlazAfter' => (isset($data[ 'CmpCloseCard_GlazAfter' ]) ? $data[ 'CmpCloseCard_GlazAfter' ] : null),
			'CmpCloseCard_m1' => (isset($data[ 'CmpCloseCard_m1' ]) ? $data[ 'CmpCloseCard_m1' ] : null),
			'CmpCloseCard_e1' => (isset($data[ 'CmpCloseCard_e1' ]) ? $data[ 'CmpCloseCard_e1' ] : null),
			'CmpCloseCard_v1' => (isset($data[ 'CmpCloseCard_v1' ]) ? $data[ 'CmpCloseCard_v1' ] : null),
			'CmpCloseCard_m2' => (isset($data[ 'CmpCloseCard_m2' ]) ? $data[ 'CmpCloseCard_m2' ] : null),
			'CmpCloseCard_e2' => (isset($data[ 'CmpCloseCard_e2' ]) ? $data[ 'CmpCloseCard_e2' ] : null),
			'CmpCloseCard_v2' => (isset($data[ 'CmpCloseCard_v2' ]) ? $data[ 'CmpCloseCard_v2' ] : null),
			'CmpCloseCard_Topic' => (isset($data[ 'CmpCloseCard_Topic' ]) ? $data[ 'CmpCloseCard_Topic' ] : null)
		);

		$txt = [];
		foreach( $queryParams as $q => $p ){
			$txt[] = $q." := :".$q;
		}
		$txt = implode(",\r\n", $txt);

		$query = "
			select
				CmpCloseCard_id as \"CmpCloseCard_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from ".$procedure."(
				CmpCloseCard_id := :CmpCloseCard_id,
				" . $txt . "
			);
		";

		if ( $action == 'edit' ) {
			$NewCmpCloseCard_id = null;
			//Если админ смп , то делаем копию исходной записи, а измененную копию сохраняем на место старой
			//1 - выбираем старую запись
			$squery = "
				SELECT
					CmpCallCard_id as \"CmpCallCard_id\",
					CmpCallCard_Numv as \"CmpCallCard_Numv\",
					CmpCallCard_Ngod as \"CmpCallCard_Ngod\",
					CmpCallCard_Prty as \"CmpCallCard_Prty\",
					CmpCallCard_Sect as \"CmpCallCard_Sect\",
					CmpArea_id as \"CmpArea_id\",
					CmpCallCard_City as \"CmpCallCard_City\",
					CmpCallCard_Ulic as \"CmpCallCard_Ulic\",
					CmpCallCard_Dom as \"CmpCallCard_Dom\",
					CmpCallCard_Kvar as \"CmpCallCard_Kvar\",
					CmpCallCard_Podz as \"CmpCallCard_Podz\",
					CmpCallCard_Etaj as \"CmpCallCard_Etaj\",
					CmpCallCard_Kodp as \"CmpCallCard_Kodp\",
					CmpCallCard_Telf as \"CmpCallCard_Telf\",
					CmpPlace_id as \"CmpPlace_id\",
					CmpCallCard_Comm as \"CmpCallCard_Comm\",
					CmpReason_id as \"CmpReason_id\",
					Person_id as \"Person_id\",
					Person_SurName as \"Person_SurName\",
					Person_FirName as \"Person_FirName\",
					Person_SecName as \"Person_SecName\",
					Person_Age as \"Person_Age\",
					Person_BirthDay as \"Person_BirthDay\",
					Person_PolisSer as \"Person_PolisSer\",
					Person_PolisNum as \"Person_PolisNum\",
					Sex_id as \"Sex_id\",
					CmpCallCard_Ktov as \"CmpCallCard_Ktov\",
					CmpCallType_id as \"CmpCallType_id\",
					CmpProfile_cid as \"CmpProfile_cid\",
					CmpCallCard_Smpt as \"CmpCallCard_Smpt\",
					CmpCallCard_Stan as \"CmpCallCard_Stan\",
					CmpCallCard_prmDT as \"CmpCallCard_prmDT\",
					CmpCallCard_Line as \"CmpCallCard_Line\",
					CmpResult_id as \"CmpResult_id\",
					CmpArea_gid as \"CmpArea_gid\",
					CmpLpu_id as \"CmpLpu_id\",
					CmpDiag_oid as \"CmpDiag_oid\",
					CmpDiag_aid as \"CmpDiag_aid\",
					CmpTrauma_id as \"CmpTrauma_id\",
					CmpCallCard_IsAlco as \"CmpCallCard_IsAlco\",
					Diag_uid as \"Diag_uid\",
					CmpCallCard_Numb as \"CmpCallCard_Numb\",
					CmpCallCard_Smpb as \"CmpCallCard_Smpb\",
					CmpCallCard_Stbr as \"CmpCallCard_Stbr\",
					CmpCallCard_Stbb as \"CmpCallCard_Stbb\",
					CmpProfile_bid as \"CmpProfile_bid\",
					CmpCallCard_Ncar as \"CmpCallCard_Ncar\",
					CmpCallCard_RCod as \"CmpCallCard_RCod\",
					CmpCallCard_TabN as \"CmpCallCard_TabN\",
					CmpCallCard_Dokt as \"CmpCallCard_Dokt\",
					CmpCallCard_Tab2 as \"CmpCallCard_Tab2\",
					CmpCallCard_Tab3 as \"CmpCallCard_Tab3\",
					CmpCallCard_Tab4 as \"CmpCallCard_Tab4\",
					Diag_sid as \"Diag_sid\",
					CmpTalon_id as \"CmpTalon_id\",
					CmpCallCard_Expo as \"CmpCallCard_Expo\",
					CmpCallCard_Smpp as \"CmpCallCard_Smpp\",
					CmpCallCard_Vr51 as \"CmpCallCard_Vr51\",
					CmpCallCard_D201 as \"CmpCallCard_D201\",
					CmpCallCard_Dsp1 as \"CmpCallCard_Dsp1\",
					CmpCallCard_Dsp2 as \"CmpCallCard_Dsp2\",
					CmpCallCard_Dspp as \"CmpCallCard_Dspp\",
					CmpCallCard_Dsp3 as \"CmpCallCard_Dsp3\",
					CmpCallCard_Kakp as \"CmpCallCard_Kakp\",
					CmpCallCard_Tper as \"CmpCallCard_Tper\",
					CmpCallCard_Vyez as \"CmpCallCard_Vyez\",
					CmpCallCard_Przd as \"CmpCallCard_Przd\",
					CmpCallCard_Tgsp as \"CmpCallCard_Tgsp\",
					CmpCallCard_Tsta as \"CmpCallCard_Tsta\",
					CmpCallCard_Tisp as \"CmpCallCard_Tisp\",
					CmpCallCard_Tvzv as \"CmpCallCard_Tvzv\",
					CmpCallCard_Kilo as \"CmpCallCard_Kilo\",
					CmpCallCard_Dlit as \"CmpCallCard_Dlit\",
					CmpCallCard_Prdl as \"CmpCallCard_Prdl\",
					CmpArea_pid as \"CmpArea_pid\",
					CmpCallCard_PCity as \"CmpCallCard_PCity\",
					CmpCallCard_PUlic as \"CmpCallCard_PUlic\",
					CmpCallCard_PDom as \"CmpCallCard_PDom\",
					CmpCallCard_PKvar as \"CmpCallCard_PKvar\",
					CmpLpu_aid as \"CmpLpu_aid\",
					CmpCallCard_IsPoli as \"CmpCallCard_IsPoli\",
					cmpCallCard_Medc as \"cmpCallCard_Medc\",
					CmpCallCard_Izv1 as \"CmpCallCard_Izv1\",
					CmpCallCard_Tiz1 as \"CmpCallCard_Tiz1\",
					CmpCallCard_Inf1 as \"CmpCallCard_Inf1\",
					CmpCallCard_Inf2 as \"CmpCallCard_Inf2\",
					CmpCallCard_Inf3 as \"CmpCallCard_Inf3\",
					CmpCallCard_Inf4 as \"CmpCallCard_Inf4\",
					CmpCallCard_Inf5 as \"CmpCallCard_Inf5\",
					CmpCallCard_Inf6 as \"CmpCallCard_Inf6\",
					pmUser_insID as \"pmUser_insID\",
					pmUser_updID as \"pmUser_updID\",
					CmpCallCard_insDT as \"CmpCallCard_insDT\",
					CmpCallCard_updDT as \"CmpCallCard_updDT\",
					KLRgn_id as \"KLRgn_id\",
					KLSubRgn_id as \"KLSubRgn_id\",
					KLCity_id as \"KLCity_id\",
					KLTown_id as \"KLTown_id\",
					KLStreet_id as \"KLStreet_id\",
					Lpu_ppdid as \"Lpu_ppdid\",
					CmpCallCard_IsEmergency as \"CmpCallCard_IsEmergency\",
					CmpCallCard_IsOpen as \"CmpCallCard_IsOpen\",
					CmpCallCardStatusType_id as \"CmpCallCardStatusType_id\",
					CmpCallCardStatus_Comment as \"CmpCallCardStatus_Comment\",
					CmpCallCard_IsReceivedInPPD as \"CmpCallCard_IsReceivedInPPD\",
					CmpPPDResult_id as \"CmpPPDResult_id\",
					EmergencyTeam_id as \"EmergencyTeam_id\",
					CmpCallCard_IsInReg as \"CmpCallCard_IsInReg\",
					Lpu_id as \"Lpu_id\",
					CmpCallCard_IsMedPersonalIdent as \"CmpCallCard_IsMedPersonalIdent\",
					MedPersonal_id as \"MedPersonal_id\",
					ResultDeseaseType_id as \"ResultDeseaseType_id\",
					CmpCallCard_firstVersion as \"CmpCallCard_firstVersion\",
					UnformalizedAddressDirectory_id as \"UnformalizedAddressDirectory_id\",
					CmpCallCard_IsPaid as \"CmpCallCard_IsPaid\",
					CmpCallCard_Korp as \"CmpCallCard_Korp\",
					CmpCallCard_Room as \"CmpCallCard_Room\",
					CmpCallCard_DiffTime as \"CmpCallCard_DiffTime\",
					UslugaComplex_id as \"UslugaComplex_id\",
					LpuBuilding_id as \"LpuBuilding_id\",
					CmpCallerType_id as \"CmpCallerType_id\",
					CmpCallPlaceType_id as \"CmpCallPlaceType_id\",
					CmpCallCard_rid as \"CmpCallCard_rid\",
					CmpCallCard_Urgency as \"CmpCallCard_Urgency\",
					CmpCallCard_BoostTime as \"CmpCallCard_BoostTime\",
					CmpSecondReason_id as \"CmpSecondReason_id\",
					CmpDiseaseAndAccidentType_id as \"CmpDiseaseAndAccidentType_id\",
					CmpCallReasonType_id as \"CmpCallReasonType_id\",
					CmpReasonNew_id as \"CmpReasonNew_id\",
					CmpCallCard_EmergencyTeamDiscardReason as \"CmpCallCard_EmergencyTeamDiscardReason\",
					CmpCallCard_IndexRep as \"CmpCallCard_IndexRep\",
					CmpCallCard_IndexRepInReg as \"CmpCallCard_IndexRepInReg\",
					CmpCallCard_IsArchive as \"CmpCallCard_IsArchive\",
					MedStaffFact_id as \"MedStaffFact_id\",
					RankinScale_id as \"RankinScale_id\",
					RankinScale_sid as \"RankinScale_sid\",
					LeaveType_id as \"LeaveType_id\",
					CmpCallCard_isShortEditVersion as \"CmpCallCard_isShortEditVersion\",
					LpuSection_id as \"LpuSection_id\",
					CmpCallCard_Recomendations as \"CmpCallCard_Recomendations\",
					CmpCallCard_Condition as \"CmpCallCard_Condition\",
					Lpu_cid as \"Lpu_cid\",
					CmpCallCard_Tend as \"CmpCallCard_Tend\",
					CmpCallCard_CallLtd as \"CmpCallCard_CallLtd\",
					CmpCallCard_CallLng as \"CmpCallCard_CallLng\",
					CmpCallCard_IsNMP as \"CmpCallCard_IsNMP\",
					CmpRejectionReason_id as \"CmpRejectionReason_id\",
					CmpCallCard_HospitalizedTime as \"CmpCallCard_HospitalizedTime\",
					CmpCallCard_saveDT as \"CmpCallCard_saveDT\",
					CmpCallCard_PlanDT as \"CmpCallCard_PlanDT\",
					CmpCallCard_FactDT as \"CmpCallCard_FactDT\",
					CmpCallCardInputType_id as \"CmpCallCardInputType_id\",
					CmpCallCard_IsExtra as \"CmpCallCard_IsExtra\",
					CmpCallCardStatus_id as \"CmpCallCardStatus_id\",
					CmpCallCard_GUID as \"CmpCallCard_GUID\",
					CmpCallCard_rGUID as \"CmpCallCard_rGUID\",
					CmpCallCard_firstVersionGUID as \"CmpCallCard_firstVersionGUID\",
					CmpCallCardStatus_GUID as \"CmpCallCardStatus_GUID\",
					EmergencyTeam_GUID as \"EmergencyTeam_GUID\",
					CmpCallCard_storDT as \"CmpCallCard_storDT\",
					CmpCallCard_defCom as \"CmpCallCard_defCom\",
					MedService_id as \"MedService_id\",
					CmpCallCard_PolisEdNum as \"CmpCallCard_PolisEdNum\",
					CmpCallCard_IsDeterior as \"CmpCallCard_IsDeterior\",
					Diag_sopid as \"Diag_sopid\",
					CmpLeaveType_id as \"CmpLeaveType_id\",
					CmpLeaveTask_id as \"CmpLeaveTask_id\",
					CmpMedicalCareKind_id as \"CmpMedicalCareKind_id\",
					CmpTransportType_id as \"CmpTransportType_id\",
					CmpResultDeseaseType_id as \"CmpResultDeseaseType_id\",
					CmpCallCardResult_id as \"CmpCallCardResult_id\",
					Person_IsUnknown as \"Person_IsUnknown\",
					CmpCallCard_IsPassSSMP as \"CmpCallCard_IsPassSSMP\",
					Lpu_smpid as \"Lpu_smpid\",
					Lpu_hid as \"Lpu_hid\",
					UnformalizedAddressDirectory_wid as \"UnformalizedAddressDirectory_wid\",
					PayType_id as \"PayType_id\",
					CmpCallCard_UlicSecond as \"CmpCallCard_UlicSecond\",
					CmpCallCard_sid as \"CmpCallCard_sid\",
					CmpCallCard_IsActiveCall as \"CmpCallCard_IsActiveCall\",
					CmpCallCard_isControlCall as \"CmpCallCard_isControlCall\",
					CmpCallCard_isTimeExceeded as \"CmpCallCard_isTimeExceeded\",
					CmpCallCard_NumvPr as \"CmpCallCard_NumvPr\",
					CmpCallCard_NgodPr as \"CmpCallCard_NgodPr\",
					CmpCallSignType_id as \"CmpCallSignType_id\",
					Lpu_CodeSMO as \"Lpu_CodeSMO\",
					Registry_sid as \"Registry_sid\",
					Diag_gid as \"Diag_gid\",
					MedicalCareBudgType_id as \"MedicalCareBudgType_id\",
					CmpCommonState_id as \"CmpCommonState_id\",
					CmpCallKind_id as \"CmpCallKind_id\",
					CmpCallCard_isViewCancelCall as \"CmpCallCard_isViewCancelCall\"
				FROM {$this->schema}.v_CmpCloseCard CLC
				WHERE CLC.CmpCloseCard_id = ".$data[ 'CmpCloseCard_id' ]."
			";

			$result = $this->db->query( $squery, $data );

			if ( !is_object( $result ) ) {
				return false;
			}
			$oldresult = $result->result( 'array' );
			$oldresult = $oldresult[ 0 ];

			
			//2 - сохраняем страую запись в новую

			$squeryParams = array(
				'CmpCallCard_id' => $oldresult[ 'CmpCallCard_id' ],
				'Day_num' => $oldresult[ 'Day_num' ],
				'Year_num' => $oldresult[ 'Year_num' ],
				'Feldsher_id' => $oldresult[ 'Feldsher_id' ],
				'StationNum' => $oldresult[ 'StationNum' ],
				'LpuBuilding_id' => $oldresult[ 'LpuBuilding_id' ],
				'EmergencyTeamNum' => $oldresult[ 'EmergencyTeamNum' ],
				'EmergencyTeam_id' => $oldresult['EmergencyTeam_id'],

				'PayType_id' => $oldresult['PayType_id'],

				'AcceptTime' => ($oldresult[ 'AcceptTime' ] != '') ? $oldresult[ 'AcceptTime' ] : null,
				'TransTime' => ($oldresult[ 'TransTime' ] != '') ? $oldresult[ 'TransTime' ] : null,
				'GoTime' => ($oldresult[ 'GoTime' ] != '') ? $oldresult[ 'GoTime' ] : null,
				'ArriveTime' => ($oldresult[ 'ArriveTime' ] != '') ? $oldresult[ 'ArriveTime' ] : null,
				'TransportTime' => ($oldresult[ 'TransportTime' ] != '') ? $oldresult[ 'TransportTime' ] : null,
				'ToHospitalTime' => ($oldresult[ 'ToHospitalTime' ] != '') ? $oldresult[ 'ToHospitalTime' ] : null,
				'CmpCloseCard_TranspEndDT' => ($oldresult[ 'CmpCloseCard_TranspEndDT' ] != '') ? $oldresult[ 'CmpCloseCard_TranspEndDT' ] : null,
				'CmpCloseCard_IsExtra' => ($oldresult[ 'CmpCloseCard_IsExtra' ] != '') ? $oldresult[ 'CmpCloseCard_IsExtra' ] : null,
				'CmpCloseCard_IsProfile' => ($oldresult[ 'CmpCloseCard_IsProfile' ] != '') ? $oldresult[ 'CmpCloseCard_IsProfile' ] : null,
				'CallPovodNew_id' => ($oldresult[ 'CallPovodNew_id' ] != '') ? $oldresult[ 'CallPovodNew_id' ] : null,
				'EndTime' => ($oldresult[ 'EndTime' ] != '') ? $oldresult[ 'EndTime' ] : null,
				'BackTime' => ($oldresult[ 'BackTime' ] != '') ? $oldresult[ 'BackTime' ] : null,
				'SummTime' => $oldresult[ 'SummTime' ],
				'Area_id' => (int)$oldresult[ 'Area_id' ] ? (int)$oldresult[ 'Area_id' ] : null,
				'City_id' => (int)$oldresult[ 'City_id' ] ? (int)$oldresult[ 'City_id' ] : null,
				'Town_id' => (int)$oldresult[ 'Town_id' ] ? (int)$oldresult[ 'Town_id' ] : null,
				'Street_id' => (!empty($oldresult['Street_id'])) ? (int)$oldresult[ 'Street_id' ] : null,
				'CmpCloseCard_Street' => (!empty($oldresult['CmpCloseCard_Street'])) ? $oldresult[ 'CmpCloseCard_Street' ] : null,
				'House' => $oldresult[ 'House' ],
				'Office' => $oldresult[ 'Office' ],
				'Entrance' => $oldresult[ 'Entrance' ],
				'Level' => $oldresult[ 'Level' ],
				'CodeEntrance' => $oldresult[ 'CodeEntrance' ],

				'MedStaffFact_id' => (!empty($oldresult['MedStaffFact_id'])) ? $oldresult['MedStaffFact_id'] : null,
				'CmpCloseCard_IsHeartNoise' => (!empty($oldresult['CmpCloseCard_IsHeartNoise'])) ? $oldresult['CmpCloseCard_IsHeartNoise'] : null,
				'CmpCloseCard_IsIntestinal' => (!empty($oldresult['CmpCloseCard_IsIntestinal'])) ? $oldresult['CmpCloseCard_IsIntestinal'] : null,
				'CmpCloseCard_IsVomit' => (!empty($oldresult['CmpCloseCard_IsVomit'])) ? $oldresult['CmpCloseCard_IsVomit'] : null,
				'EmergencyTeamSpec_id' => (!empty($oldresult['EmergencyTeamSpec_id'])) ? $oldresult['EmergencyTeamSpec_id'] : null,
				'CmpCloseCard_IsDiuresis' => (!empty($oldresult['CmpCloseCard_IsDiuresis'])) ? $oldresult['CmpCloseCard_IsDiuresis'] : null,
				'CmpCloseCard_IsDefecation' => (!empty($oldresult['CmpCloseCard_IsDefecation'])) ? $oldresult['CmpCloseCard_IsDefecation'] : null,
				'CmpCloseCard_IsTrauma' => (!empty($oldresult['CmpCloseCard_IsTrauma'])) ? $oldresult['CmpCloseCard_IsTrauma'] : null,
				'CmpCloseCard_BegTreatDT' => (!empty($oldresult['CmpCloseCard_BegTreatDT'])) ? $oldresult['CmpCloseCard_BegTreatDT'] : null,
				'CmpCloseCard_EndTreatDT' => (!empty($oldresult['CmpCloseCard_EndTreatDT'])) ? $oldresult['CmpCloseCard_EndTreatDT'] : null,
				'CmpCloseCard_HelpDT' => (!empty($oldresult['CmpCloseCard_HelpDT'])) ? $oldresult['CmpCloseCard_HelpDT'] : null,
				'CmpCloseCard_Sat' => (!empty($oldresult['CmpCloseCard_Sat'])) ? $oldresult['CmpCloseCard_Sat'] : null,
				'CmpCloseCard_AfterSat' => (!empty($oldresult['CmpCloseCard_AfterSat'])) ? $oldresult['CmpCloseCard_AfterSat'] : null,
				'CmpCloseCard_Rhythm' => (!empty($oldresult['CmpCloseCard_Rhythm'])) ? $oldresult['CmpCloseCard_Rhythm'] : null,
				'CmpCloseCard_AfterRhythm' => (!empty($oldresult['CmpCloseCard_AfterRhythm'])) ? $oldresult['CmpCloseCard_AfterRhythm'] : null,
				'CmpLethalType_id' => (!empty($oldresult['CmpLethalType_id'])) ? $oldresult['CmpLethalType_id'] : null,
				'CmpCloseCard_LethalDT' => (!empty($oldresult['CmpCloseCard_LethalDT'])) ? $oldresult['CmpCloseCard_LethalDT'] : null,
				'Person_id' => (!empty($oldresult['Person_id'])) ? $oldresult['Person_id'] : null,
				'Fam' => $oldresult[ 'Fam' ],
				'Name' => $oldresult[ 'Name' ],
				'Middle' => $oldresult[ 'Middle' ],
				'Age' => $oldresult[ 'Age' ],
				'Sex_id' => $oldresult[ 'Sex_id' ],
				'Work' => $oldresult[ 'Work' ],
				'DocumentNum' => $oldresult[ 'DocumentNum' ],
				'Ktov' => $oldresult[ 'Ktov' ],
				'CmpCallerType_id' => $oldresult[ 'CmpCallerType_id' ],
				'Phone' => $oldresult[ 'Phone' ],
				'SocStatus_id' => $oldresult[ 'SocStatus_id' ],
				'FeldsherAccept' => $oldresult[ 'FeldsherAccept' ],
				'FeldsherTrans' => $oldresult[ 'FeldsherTrans' ],
				'CallType_id' => $oldresult[ 'CallType_id' ],
				'CallPovod_id' => $oldresult[ 'CallPovod_id' ],
				'isAlco' => $oldresult[ 'isAlco' ],
				'Complaints' => $oldresult[ 'Complaints' ],
				'Anamnez' => $oldresult[ 'Anamnez' ],
				'Korpus' => $oldresult[ 'Korpus' ],
				'Room' => $oldresult[ 'Room' ],
				'isMenen' => $oldresult[ 'isMenen' ],
				'isNist' => $oldresult[ 'isNist' ],
				'isAnis' => $oldresult[ 'isAnis' ],
				'isLight' => $oldresult[ 'isLight' ],
				'isAcro' => $oldresult[ 'isAcro' ],
				'isMramor' => $oldresult[ 'isMramor' ],
				'isHale' => $oldresult[ 'isHale' ],
				'isPerit' => $oldresult[ 'isPerit' ],
				'Urine' => $oldresult[ 'Urine' ],
				'Shit' => $oldresult[ 'Shit' ],
				'OtherSympt' => $oldresult[ 'OtherSympt' ],
				'WorkAD' => $oldresult[ 'WorkAD' ],
				'AD' => $oldresult[ 'AD' ],
				'Chss' => $oldresult[ 'Chss' ],
				'Pulse' => $oldresult[ 'Pulse' ],
				'Temperature' => $oldresult[ 'Temperature' ],
				'Chd' => $oldresult[ 'Chd' ],
				'Pulsks' => $oldresult[ 'Pulsks' ],
				'Gluck' => $oldresult[ 'Gluck' ],
				'LocalStatus' => $oldresult[ 'LocalStatus' ],
				'Ekg1' => $oldresult[ 'Ekg1' ],
				'Ekg1Time' => ($oldresult[ 'Ekg1Time' ] != '') ? $oldresult[ 'Ekg1Time' ] : null,
				'Ekg2' => $oldresult[ 'Ekg2' ],
				'Ekg2Time' => ($oldresult[ 'Ekg2Time' ] != '') ? $oldresult[ 'Ekg2Time' ] : null,
				'Diag_id' => $oldresult[ 'Diag_id' ],
				'Diag_uid' => $oldresult[ 'Diag_uid' ],
				'Diag_sid' => $oldresult[ 'Diag_sid' ],
				'HelpPlace' => $oldresult[ 'HelpPlace' ],
				'HelpAuto' => $oldresult[ 'HelpAuto' ],
				'EfAD' => $oldresult[ 'EfAD' ],
				'EfChss' => $oldresult[ 'EfChss' ],
				'EfPulse' => $oldresult[ 'EfPulse' ],
				'EfTemperature' => $oldresult[ 'EfTemperature' ],
				'EfChd' => $oldresult[ 'EfChd' ],
				'EfPulsks' => $oldresult[ 'EfPulsks' ],
				'EfGluck' => $oldresult[ 'EfGluck' ],
				'Kilo' => $oldresult[ 'Kilo' ],
				'DescText' => $oldresult[ 'DescText' ],
				'pmUser_id' => $oldresult[ 'pmUser_insID' ],
				'CmpCloseCard_IsNMP' => $oldresult[ 'CmpCloseCard_IsNMP' ],
				'CmpCloseCard_firstVersion' => $oldresult[ 'CmpCloseCard_firstVersion' ],
				'CmpCloseCard_Epid' => $oldresult[ 'CmpCloseCard_Epid' ],
				'CmpCloseCard_Glaz' => $oldresult[ 'CmpCloseCard_Glaz' ],
				'CmpCloseCard_GlazAfter' => $oldresult[ 'CmpCloseCard_GlazAfter' ],
				'CmpCloseCard_m1' => $oldresult[ 'CmpCloseCard_m1' ],
				'CmpCloseCard_e1' => $oldresult[ 'CmpCloseCard_e1' ],
				'CmpCloseCard_v1' => $oldresult[ 'CmpCloseCard_v1' ],
				'CmpCloseCard_m2' => $oldresult[ 'CmpCloseCard_m2' ],
				'CmpCloseCard_e2' => $oldresult[ 'CmpCloseCard_e2' ],
				'CmpCloseCard_v2' => $oldresult[ 'CmpCloseCard_v2' ],
				'CmpCloseCard_Topic' => $oldresult[ 'CmpCloseCard_Topic' ]
			);

			$txt = [];
			foreach( $squeryParams as $q => $p ){
				$txt[] = $q." := :".$q;
			}
			$txt = implode(",\r\n", $txt);
			
			$squery = "
				select
					CmpCloseCard_id as \"CmpCloseCard_id\",
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from p_CmpCloseCard_ins(
					".$txt."
				)
			";

			$result = $this->db->query( $squery, $squeryParams );

			if ( !is_object( $result ) ) {
				return false;
			}

			$result = $result->result( 'array' );
			$result = $result[ 0 ];

			$NewCmpCloseCard_id = $result[ 'CmpCloseCard_id' ];

			// 3 - заменяем старую запись текущими изменениями

			$newParams = $queryParams;
			$newParams[ 'CmpCloseCard_id' ] = $oldresult[ 'CmpCloseCard_id' ];

			if ( (!isset( $newParams[ 'CmpCloseCard_id' ] )) || ($newParams[ 'CmpCloseCard_id' ] == null ) ) {
				$newParams[ 'CmpCallCard_id' ] = $oldresult[ 'CmpCallCard_id' ];
			}

			$txt = [];
			foreach( $newParams as $q => $p ){
				$txt[] = $q." := :".$q;
			}
			$txt = implode(",\r\n", $txt);

			$squery = "
				select
					CmpCloseCard_id as \"CmpCloseCard_id\",
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from p_CmpCloseCard_upd(
					".$txt."
				)
			";
			//var_dump(getDebugSQL($squery, $newParams)); exit;
			$result = $this->db->query( $squery, $newParams );
			$resArray = $result->result( 'array' );

			// 4 - устанавливаем значение старого id в перезаписанной записи
			$squery = "
				select *
				from p_CmpCloseCard_setFirstVersion(
					CmpCloseCard_id := ".$oldresult[ 'CmpCloseCard_id' ].",
					CmpCloseCard_firstVersion := ".$NewCmpCloseCard_id.",
					pmUser_id := ".$data[ 'pmUser_id' ]."
				)
			";

			$this->db->query( $squery );
		} else { // add
			$result = $this->db->query( $query, $queryParams );
			$resArray = $result->result( 'array' );
		}

		// Связь документа списания медикаментов на пациента и талона закрытия вызова
		if ( isset( $data['DocumentUc_id'] ) && $data['DocumentUc_id'] ) {
			$this->saveCmpCloseCardDocumentUcRel( array_merge( $data, array( 'CmpCloseCard_id' => $resArray[ 0 ][ 'CmpCloseCard_id' ] ) ) );
		}

		if ( isset( $data['CmpEquipment'] ) && $data['CmpEquipment'] ) {
			// Использованное оборудование
			$this->saveCmpCloseCardEquipmentRel( array_merge( $data, array( 'CmpCloseCard_id' => $resArray[ 0 ][ 'CmpCloseCard_id' ] ) ) );
		}

		if (!empty($data['AcceptTime']) && !empty($data['CmpCallCard_id'])) {

			$AcceptDate = DateTime::createFromFormat('d.m.Y H:i', $data['AcceptTime']);
			$aDate = $AcceptDate->format('Y-m-d H:i');

			$pars = array(
				'CmpCallCard_id'=>$data['CmpCallCard_id'],
				//'CmpCallCard_prmDT'=>$data['AcceptTime'],
				'CmpCallCard_prmDT'=>$aDate,
				'pmUser_id'=>$data['pmUser_id']
			);

			$update_CmpCallCard_prmDT_result = $this->swUpdate('CmpCallCard', $pars, false);

			if (!$this->isSuccessful( $update_CmpCallCard_prmDT_result )) {
				return $update_CmpCallCard_prmDT_result;
			}

		}

		//сохранение person_id в CmpCallCard
		if (isset($data['Person_id']) && $data['Person_id'] != '')
		{
			$personQuery = "
				select *
				from {$this->schema}.p_CmpCallCard_setPerson(
					CmpCallCard_id := " . $data['CmpCallCard_id'] . ",
					Person_id := " . $data['Person_id'] . ",
					pmUser_id := " . $data['pmUser_id'] . "
				)
			";

			$persRes = $this->db->query($personQuery);
		}

		//унес сохранение комбос в функцию
		$res = $this->saveCmpCloseCardComboValues($data, $action, $oldresult, $resArray, $NewCmpCloseCard_id, $UnicNums, $relProcedure);

		return $res;
	}*/
}
