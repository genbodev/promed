<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Ufa_CmpCallCardModel - модель для работы с картами вызова СМП. Версия для Уфы
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package            Common
 * @access            public
 * @copyright        Copyright (c) 2020 Swan Ltd.
 * @author            Valery Bondarev
 * @version            ufa
 */

require_once(APPPATH . 'models/_pgsql/CmpCallCard_model.php');

class Ufa_CmpCallCard_model extends CmpCallCard_model
{


	/**
	 * Поточный ввод талонов вызова
	 */
	/*
	public function saveCmpStreamCard( $data ) {
		if ( $data[ 'AcceptDT' ] != '' )
			$data[ 'AcceptTime' ] = $data[ 'AcceptDT' ];
		if ( $data[ 'TransDT' ] != '' )
			$data[ 'TransTime' ] = $data[ 'TransDT' ];
		if ( $data[ 'GoDT' ] != '' )
			$data[ 'GoTime' ] = $data[ 'GoDT' ];
		if ( $data[ 'ArriveDT' ] != '' )
			$data[ 'ArriveTime' ] = $data[ 'ArriveDT' ];
		if ( $data[ 'TransportDT' ] != '' )
			$data[ 'TransportTime' ] = $data[ 'TransportDT' ];
		if ( $data[ 'EndDT' ] != '' )
			$data[ 'EndTime' ] = $data[ 'EndDT' ];
		if ( $data[ 'BackDT' ] != '' )
			$data[ 'BackTime' ] = $data[ 'BackDT' ];

		$UnicNums = '';
		$statuschange = true;
		$CmpCallCard_Numv = '';

		$UnicNums = ",
			@UnicCmpCallCard_Numv bigint,
			@UnicCmpCallCard_Ngod bigint,
			@SQLstring nvarchar(500),
			@ParamDefinition nvarchar(500);

			SET @SQLString =
				N'SELECT @UnicCmpCallCard_NumvOUT = MAX(CASE WHEN CAST( CCC.CmpCallCard_prmDT as date) = ''".date( 'Y-m-d' )."''
				THEN ISNULL(CmpCallCard_Numv,0) ELSE 0 END)+1,
				@UnicCmpCallCard_NgodOUT = MAX(CASE WHEN YEAR( CCC.CmpCallCard_prmDT ) = ".date( 'Y' )."
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
		($data[ 'Fam' ] == 'НЕИЗВЕСТЕН' &&
			$data[ 'Name' ] == 'НЕИЗВЕСТЕН' &&
			$data[ 'Middle' ] == 'НЕИЗВЕСТЕН') || $data['Person_id'] == 0
		) {
			$socstatus_Ids = array("ufa" => 2, "buryatiya" => 10000083, "kareliya" => 51, "khak" => 32,
				"astra" => 10000053, "kaluga" => 231, "penza" => 224, "perm" => 2, "pskov" => 25,
				"saratov" => 10000035, "ekb" => 10000072, "msk" => 60, "krym" => 262, "kz" => 91, "by" => 201);

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

				,@PersonBirthDay_BirthDay = :PersonBirthDay_BirthDay
				,@PersonSex_id = :Sex_id
				,@PersonSocStatus_id = :socstatus_Ids

				,@pmUser_id = :pmUser_id
				,@Error_Code = @ErrCode output
				,@Error_Message = @ErrMessage output;

				select @Pers_id as Pid, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			$queryParams = array(
				'pmUser_id' => $data['pmUser_id'],
				'Server_id' => $data['Server_id'],
				'Sex_id' => $data['Sex_id'],
				'socstatus_Ids' => $socstatus_Ids[getRegionNick()],
				'PersonBirthDay_BirthDay' => '01.01.' . (date("Y") - (isset($data['Person_Age']) ? $data['Person_Age'] : $data['Age']))
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

			exec p_CmpCallCard_ins
				@CmpCallCard_id = @Res output,
				@CmpCallCard_rid = :CmpCallCard_rid,
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
				@CmpCallCard_Ktov = :CmpCallCard_Ktov,
				@CmpCallerType_id = :CmpCallerType_id,
				@CmpCallType_id = :CmpCallType_id,

				@CmpCallCard_IsReceivedInPPD = :CmpCallCard_IsReceivedInPPD,
				@CmpCallCard_IsNMP = :CmpCallCard_IsNMP,

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

				@MedStaffFact_id = :MedStaffFact_id,

				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as CmpCallCard_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$aDate = explode( " ", $data[ 'AcceptTime' ] );
		$aTime = $aDate[ 1 ];
		$aDate = explode( ".", $aDate[ 0 ] );
		$aDate = $aDate[ 2 ].'-'.$aDate[ 1 ].'-'.$aDate[ 0 ].' '.$aTime;

		$queryParams = array(
			'Lpu_id_forUnicNumRequest' => $data[ 'Lpu_id' ],
			'CmpCallCard_id' => $data[ 'CmpCallCard_id' ],
			'CmpCallCard_rid' => (!empty( $data[ 'CmpCallCard_rid' ] ) ? $data[ 'CmpCallCard_rid' ] : NULL),
			'CmpCallCard_Numv' => $data[ 'Day_num' ],
			'CmpCallCard_Ngod' => $data[ 'Year_num' ],
			'CmpCallCard_City' => $data[ 'City_id' ],
			'CmpCallCard_Ulic' => $data[ 'Street_id' ],
			'CmpCallCard_Dom' => $data[ 'House' ],
			'CmpCallCard_Korp' => $data[ 'Korpus' ],
			'CmpCallCard_Room' => (!empty( $data[ 'Room' ] ) ? $data[ 'Room' ] : NULL),
			'CmpCallCard_Kvar' => $data[ 'Office' ],
			'CmpCallCard_Podz' => $data[ 'Entrance' ],
			'CmpCallCard_Etaj' => $data[ 'Level' ],
			'CmpCallCard_Kodp' => $data[ 'CodeEntrance' ],
			'CmpCallCard_Telf' => $data[ 'Phone' ],
			'CmpCallPlaceType_id' => (!empty( $data[ 'CmpCallPlaceType_id' ] ) ? $data[ 'CmpCallPlaceType_id' ] : NULL),
			'CmpCallCard_Comm' => $data[ 'CmpCallCard_Comm' ],
			'CmpReason_id' => $data[ 'CallPovod_id' ],
			'Person_Surname' => $data[ 'Fam' ],
			'Person_Firname' => $data[ 'Name' ],
			'Person_Secname' => $data[ 'Middle' ],
			'Person_Age' => $data[ 'Age' ],
			'Person_id' => (!empty( $data[ 'Person_id' ] ) && is_numeric( $data[ 'Person_id' ] ) ? $data[ 'Person_id' ] : null),
			'Person_PolisSer' => $data[ 'PolisSerial' ],
			'Person_PolisNum' => $data[ 'PolisNum' ],
			'Sex_id' => (!empty( $data[ 'Sex_id' ] ) ? $data[ 'Sex_id' ] : null),
			'CmpCallCard_Ktov' => (!empty( $data[ 'Ktov' ] ) ? $data[ 'Ktov' ] : null),
			'CmpCallerType_id' => (!empty( $data[ 'CmpCallerType_id' ] ) ? $data[ 'CmpCallerType_id' ] : null),
			'CmpCallType_id' => $data[ 'CallType_id' ],
			'KLRgn_id' => (isset( $data[ 'KLRgn_id' ] ) && $data[ 'KLRgn_id' ] > 0) ? $data[ 'KLRgn_id' ] : ((isset( $data[ 'KLAreaStat_idEdit' ] ) && $data[ 'KLAreaStat_idEdit' ] > 0) ? $data[ 'KLAreaStat_idEdit' ] : null),
			'KLSubRgn_id' => (isset( $data[ 'Area_id' ] ) && $data[ 'Area_id' ] > 0) ? $data[ 'Area_id' ] : null,
			'KLCity_id' => (isset( $data[ 'City_id' ] ) && $data[ 'City_id' ] > 0) ? $data[ 'City_id' ] : null,
			'KLTown_id' => (isset( $data[ 'Town_id' ] ) && $data[ 'Town_id' ] > 0) ? $data[ 'Town_id' ] : null,
			'KLStreet_id' => (isset( $data[ 'Street_id' ] ) && $data[ 'Street_id' ] > 0) ? $data[ 'Street_id' ] : null,
			'Lpu_id' => (isset( $data[ 'Lpu_id' ] ) && $data[ 'Lpu_id' ] > 0) ? $data[ 'Lpu_id' ] : null,
			'Lpu_ppdid' => (isset( $data[ 'Lpu_ppdid' ] ) && $data[ 'Lpu_ppdid' ] > 0) ? $data[ 'Lpu_ppdid' ] : null,
			'CmpCallCard_IsReceivedInPPD' => (array_key_exists( 'CmpCallCard_IsReceivedInPPD', $data ) && $data[ 'CmpCallCard_IsReceivedInPPD' ] == 'on') ? '2' : '1',
			'CmpCallCard_IsNMP' => (array_key_exists( 'CmpCallCard_IsNMP', $data ) && $data[ 'CmpCallCard_IsNMP' ] == 'on') ? '2' : '1',
			'CmpCallCard_IsOpen' => '2',
			'CmpCallCardStatusType_id' => '6',
			'LpuBuilding_id' => (!empty( $data[ 'LpuBuilding_id' ] )) ? $data[ 'LpuBuilding_id' ] : null,
			'CmpCallCard_prmDT' => $aDate,
			'MedStaffFact_id' => (!empty($data['MedStaffFact_id']))?$data['MedStaffFact_id']:((!empty($data['MedStaffFact_uid']))?$data['MedStaffFact_uid']:null),
			'pmUser_id' => $data[ 'pmUser_id' ]
		);

		$this->beginTransaction();
		//var_dump(getDebugSql($query, $queryParams)); exit;
		$result = $this->db->query( $query, $queryParams );
		if ( is_object( $result ) ) {
			$result = $result->result( 'array' );
			if ( $result[ 0 ][ 'CmpCallCard_id' ] > 0 ) {
				$result110 = $this->saveCmpCloseCard110( array_merge( $data , array( 'CmpCallCard_id' => $result[ 0 ][ 'CmpCallCard_id' ] ) ) ) ;
				if (!$this->isSuccessful( $result110 )) {
					$this->rollbackTransaction();
				}
				$this->commitTransaction();
				return array( array_merge( $result[ 0 ], $result110[ 0 ] ) ) ;
			}
		}
		$this->rollbackTransaction();

		return false;
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
			   CClC.Age
			   ,CCC.Person_id
			   ,CClC.Ktov
			   ,CClC.CmpCallerType_id
			   ,CCC.KLRgn_id
			   ,CCC.KLSubRgn_id as Area_id
			   ,CCC.KLCity_id as City_id
			   ,CCC.KLTown_id as Town_id
			   ,CCC.KLStreet_id  as Street_id
			   ,CClC.Room	
			   ,CClC.Korpus as Korpus
			   
			   ,CClC.EmergencyTeamNum as EmergencyTeamNum
			   ,CCLC.EmergencyTeam_id as EmergencyTeam_id
			   ,CClC.StationNum as StationNum
			   ,CClC.LpuBuilding_id
			   --,CClC.pmUser_insID as Feldsher_id
			   ,CClC.Feldsher_id as Feldsher_id
			   --,CClC.pmUser_insID as FeldsherAccept
			   --,CCLC.FeldsherAccept
			   --,CClC.FeldsherTrans
			   
	   
			   ,convert(varchar(10), CClC.AcceptTime, 104)+' '+convert(varchar(5), cast(CClC.AcceptTime as datetime), 108) as AcceptTime
			   ,convert(varchar(10), CClC.TransTime, 104)+' '+convert(varchar(5), cast(CClC.TransTime as datetime), 108) as TransTime
			   ,convert(varchar(10), CClC.GoTime, 104)+' '+convert(varchar(5), cast(CClC.GoTime as datetime), 108) as GoTime
			   
			   ,convert(varchar(10), CClC.ArriveTime, 104)+' '+convert(varchar(5), cast(CClC.ArriveTime as datetime), 108) as ArriveTime
			   ,convert(varchar(10), CClC.TransportTime, 104)+' '+convert(varchar(5), cast(CClC.TransportTime as datetime), 108) as TransportTime
			   --,convert(varchar(10), CClC.ToHospitalTime, 104)+' '+convert(varchar(5), cast(CClC.ToHospitalTime as datetime), 108) as ToHospitalTime
			   ,convert(varchar(10), CClC.EndTime, 104)+' '+convert(varchar(5), cast(CClC.EndTime as datetime), 108) as EndTime
			   ,convert(varchar(10), CClC.BackTime, 104)+' '+convert(varchar(5), cast(CClC.BackTime as datetime), 108) as BackTime
	   
			   ,CClC.SummTime
			   ,CClC.Work
			   ,CClC.DocumentNum
			   --,CClC.SocStatus_id
			   ,CClC.CallType_id
			   ,CCC.CmpReason_id as CallPovod_id
			   --,CASE WHEN ISNULL(CClC.isAlco,0) = 0 THEN NULL ELSE CClC.isAlco END as isAlco
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
			   ,CASE WHEN ISNULL(CClC.EfChss,0) = 0 THEN NULL ELSE CClC.EfChss END as EfChss
			   ,CASE WHEN ISNULL(CClC.EfPulse,0) = 0 THEN NULL ELSE CClC.EfPulse END as EfPulse
			   ,CClC.EfTemperature
			   ,CASE WHEN ISNULL(CClC.EfChd,0) = 0 THEN NULL ELSE CClC.EfChd END as EfChd
			   ,CClC.EfPulsks
			   ,CClC.EfGluck
			   ,CClC.Kilo
			   ,CClC.Lpu_id
			   ,CClC.HelpPlace
			   ,CClC.HelpAuto
			   ,CClC.DescText

			   ,CClC.isSogl
			   ,CClC.isOtkazMed
			   ,CClC.isOtkazHosp

			   ,CClC.CallPovodNew_id
			   ,CClC.isKupir
			   ,CClC.isVac
			   ,convert(varchar(10), CClC.Mensis_DT, 104)+' '+convert(varchar(5), cast(CClC.Mensis_DT as datetime), 108) as Mensis_DT
			   ,convert(varchar(10), CClC.Bad_DT, 104)+' '+convert(varchar(5), cast(CClC.Bad_DT as datetime), 108) as Bad_DT
			   ,CClC.Alerg
			   ,CClC.Epid
			   ,CClC.Perk
			   ,CClC.Zev

			   --,UCA.PMUser_Name as FeldsherAcceptName
			   --,UCT.PMUser_Name as FeldsherTransName
			   
		   from
			   {$this->schema}.v_CmpCloseCard CClC with (nolock)
			   left join v_CmpCallCard CCC (nolock) on CCC.CmpCallCard_id = CClC.CmpCallCard_id
			   left join v_PersonState PS (nolock) on PS.Person_id = CCC.Person_id
			   LEFT JOIN v_Lpu L with (nolock) on L.Lpu_id = CClC.Lpu_id	
			   LEFT JOIN v_EmergencyTeam EMT with (nolock) on EMT.EmergencyTeam_id = CCC.EmergencyTeam_id	
			   LEFT JOIN v_pmUserCache UCA with (nolock) on UCA.PMUser_id = CClC.pmUser_insID
			   LEFT JOIN v_pmUserCache UCT with (nolock) on UCT.PMUser_id = CClC.pmUser_insID
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
	 * default desc
	 */
	function printCmpCloseCard110($data)
	{
		$query = "
			select
				CLC.CmpCallCard_id as \"CmpCallCard_id\"
				,CLC.CmpCloseCard_id as \"CmpCloseCard_id\"
				--,CC.CmpCallCard_Numv as Day_num
				--,CC.CmpCallCard_Ngod as Year_num
				,CLC.Day_num as \"Day_num\"
				,CLC.Year_num as \"Year_num\"
				--,convert(varchar, CLC.CmpCloseCard_insDT, 104) as CardDate				
				,cto_char(CC.CmpCallCard_insDT, 'dd.mm.yyyy') as \"CallCardDate\"			
				,CLC.Feldsher_id as \"Feldsher_id\"
				--,CASE WHEN coalesce(CLC.LpuBuilding_id,0) > 0 THEN LB.LpuBuilding_Name ELSE CLC.StationNum END as StationNum
				,CLC.StationNum as \"StationNum\"
				,CLC.EmergencyTeamNum as \"EmergencyTeamNum\"
				,to_char(CLC.AcceptTime, 'hh24:mi') as \"AcceptTime\"
				,to_char(CLC.AcceptTime, 'dd.mm.yyyy') as \"AcceptDate\"
				,to_char(CLC.TransTime, 'hh24:mi') as \"TransTime\"
				,to_char(CLC.GoTime, 'hh24:mi') as \"GoTime\"
				,to_char(CLC.ArriveTime, 'hh24:mi') as \"ArriveTime\"
				,to_char(CLC.TransportTime, 'hh24:mi') as \"TransportTime\"
				--,to_char(CLC.ToHospitalTime, 'hh24:mi') as ToHospitalTime
				,to_char(CLC.EndTime, 'hh24:mi') as \"EndTime\"
				,to_char(CLC.BackTime, 'hh24:mi') as \"BackTime\"
				,CLC.SummTime as \"SummTime\"
				
				,CLC.Area_id as \"Area_id\"
				,KL_AR.KLArea_Name as \"Area\"
				,CC.KLCity_id as \"KLCity_id\"
				,KL_CITY.KLArea_Name as \"City\"
				,CC.KLTown_id as \"KLTown_id\"
				,KL_SOCR.KLSocr_Nick as \"Socr\"
				,KL_TOWN.KLArea_Name as \"Town\"
				,CC.KLStreet_id as \"KLStreet_id\"
				,KL_ST.KLStreet_Name as \"Street\"
				,case when SecondStreet.KLStreet_FullName is not null then
					case when socrSecondStreet.KLSocr_Nick is not null then UPPER(socrSecondStreet.KLSocr_Nick)||'. '||SecondStreet.KLStreet_Name else
					SecondStreet.KLStreet_FullName end
					else ''
				end as \"secondStreetName\"

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
				--,RS.CmpReason_Name as Reason
				
				--,SS.SocStatus_Name as C_PersonSocial_id
				
				,CLC.Work as \"Work\"
				,CLC.DocumentNum as \"DocumentNum\"
				,CLC.Ktov as \"Ktov\",
				COALESCE(CCrT.CmpCallerType_Name,CLC.Ktov) as \"CmpCallerType_Name\",
				CLC.Phone as \"Phone\"
				
				,CLC.FeldsherAccept as \"FeldsherAccept\"
				,CLC.FeldsherTrans as \"FeldsherTrans\"
				
				,MPA.Person_Fio as \"FeldsherAcceptName\"
				,MPT.Person_Fio as \"FeldsherTransName\"
				
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
				-- ,CLC.pmUser_id
			from
				{$this->schema}.v_CmpCloseCard CLC
				LEFT JOIN Sex SX on SX.Sex_id = CLC.Sex_id
				--LEFT join v_pmUserCache PMCA on PMCA.PMUser_id = CLC.FeldsherAccept
				LEFT JOIN v_MedPersonal MPA on MPA.MedPersonal_id = CLC.FeldsherAccept				
				--LEFT join v_pmUserCache PMCT on PMCT.PMUser_id = CLC.FeldsherTrans
				LEFT join v_MedPersonal MPT on MPT.MedPersonal_id = CLC.FeldsherTrans
				--LEFT JOIN CmpReason RS on RS.CmpReason_id = CLC.CallPovod_id
				LEFT JOIN KLStreet KL_ST on KL_ST.KLStreet_id = CC.KLStreet_id
				LEFT JOIN v_KLStreet SecondStreet on SecondStreet.KLStreet_id = CLC.CmpCloseCard_UlicSecond
				LEFT JOIN v_KLSocr socrSecondStreet on SecondStreet.KLSocr_id = socrSecondStreet.KLSocr_id
				LEFT JOIN KLArea KL_AR on KL_AR.KLArea_id = CC.KLArea_id
				LEFT JOIN KLArea KL_CITY on KL_CITY.KLArea_id = CC.KLCity_id
				LEFT JOIN KLArea KL_TOWN on KL_TOWN.KLArea_id = CC.KLTown_id
				LEFT JOIN KLSocr KL_SOCR on KL_TOWN.KLSocr_id = KL_SOCR.KLSocr_id
				LEFT JOIN v_CmpCallType CCT on CCT.CmpCallType_id = CLC.CallType_id
				LEFT JOIN v_CmpCallerType CCrT on CCrT.CmpCallerType_id = CLC.CmpCallerType_id
				left join v_Diag DIAG on DIAG.Diag_id = CLC.Diag_id
				left join v_CmpCallCard CC on CC.CmpCallCard_id = CLC.CmpCallCard_id
				left join v_Lpu Lpu on Lpu.Lpu_id = CC.Lpu_id
				--LEFT JOIN v_LpuBuilding LB on LB.LpuBuilding_id = CLC.LpuBuilding_id
				left join v_SocStatus SS on SS.SocStatus_id = CLC.SocStatus_id
			
			where
				CLC.CmpCallCard_id = :CmpCallCard_id
			limit 1
		";

		$result = $this->db->query($query, array(
			'CmpCallCard_id' => $data['CmpCallCard_id']
		));


		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * default шапка для печати
	 */
	function printCmpCallCardHeader($data)
	{
		$query = "
			select
				CCC.CmpCallCard_id as \"CmpCallCard_id\"				
				,COALESCE(CCLC.Day_num, CCC.CmpCallCard_Numv) as \"Day_num\"
				,to_char(CCC.CmpCallCard_prmDT, 'dd.mm.yyyy') as \"CallCardDate\"			
				,LB.LpuBuilding_Name as \"LpuBuilding_Name\"
				,EMT.EmergencyTeam_Num as \"EmergencyTeam_Num\"

				,to_char(CCC.CmpCallCard_prmDT, 'hh24:mi') as \"AcceptTime\"
				,to_char(CCC.CmpCallCard_Tper, 'hh24:mi') as \"TransTime\"
				,case when CCC.CmpCallCard_Tgsp is not null then to_char(CCC.CmpCallCard_Tgsp, 'hh24:mi') else COALESCE(startTransportir.timeIns, '') end as \"startTransportir\"
				,case when CCC.CmpCallCard_HospitalizedTime is not null then to_char(CCC.CmpCallCard_HospitalizedTime, 'hh24:mi') else COALESCE(arrivalMO.timeIns, '') end as \"arrivalMO\"
				,case when CCC.CmpCallCard_Tisp is not null then to_char(CCC.CmpCallCard_Tisp, 'hh24:mi') else COALESCE(endService.timeIns, '') end as \"endService\"
				,case 
					when (CCC.CmpCallCard_Tisp is not null AND CCC.CmpCallCard_prmDT is not null)
					then to_char(ABS(DATEDIFF('minute', CCC.CmpCallCard_Tisp, CCC.CmpCallCard_prmDT)), 'hh24:mi:ss')
					else to_char(CCLC.SummTime, 'hh24:mi')
				end as \"SummTime\"
				
				,tochar(COALESCE(CCC.CmpCallCard_Tgsp, CCC.CmpCallCard_Tisp, CCLC.TransportTime), 'hh24:mi') as \"TransportTime\"
				,tochar(COALESCE(CCC.CmpCallCard_Vyez, CCLC.GoTime), 'hh24:mi') as \"GoTime\"
				,tochar(COALESCE(CCC.CmpCallCard_Przd, CCLC.ArriveTime), 'hh24:mi') as \"ArriveTime\"
				,tochar(COALESCE(CCC.CmpCallCard_Tvzv, CCLC.BackTime), 'hh24:mi') as \"BackTime\"
				,tochar(COALESCE(CmpCloseCard_CallBackTime, CCLC.EndTime), 'hh24:mi') as \"EndTime\"
				
				,COALESCE(SRGN.KLSubRgn_Name, '') as \"SubRegion\"
				,COALESCE(KL_AR.KLArea_Name, '') as \"Area\"
				,CCC.KLCity_id as \"KLCity_id\"
				,COALESCE(KL_CITY.KLArea_Name, '') as \"City\"
				,CCC.KLTown_id as \"KLTown_id\"
				,COALESCE(KL_TOWN.KLArea_Name, '') as \"Town\"
				,CCC.KLStreet_id as \"KLStreet_id\"
				,COALESCE(KL_ST.KLStreet_Name, CCC.CmpCallCard_Ulic, '') as \"Street\"


				,case when SecondCCLCStreet.KLStreet_FullName is not null then
					case when SecondCCLCStreet.KLStreet_FullName is not null then
						case when socrSecondCCLCStreet.KLSocr_Nick is not null then ', '||UPPER(socrSecondCCLCStreet.KLSocr_Nick)||'. '||SecondCCLCStreet.KLStreet_Name else
						', '||SecondCCLCStreet.KLStreet_FullName end
						else ''
					end
				else
					case when SecondStreet.KLStreet_FullName is not null then
					case when socrSecondStreet.KLSocr_Nick is not null then ', '||UPPER(socrSecondStreet.KLSocr_Nick)||'. '||SecondStreet.KLStreet_Name else
					', '||SecondStreet.KLStreet_FullName end
					else ''
					end
				end as \"secondStreetName\"
				,case when UAD.UnformalizedAddressDirectory_Name is not null then ' Место: '||UAD.UnformalizedAddressDirectory_Name else '' end as \"Adress_Object\"
				,COALESCE(KL_SOST.KLSocr_Nick, '') as \"SocrSt\"
				,COALESCE(KL_SOTW.KLSocr_Nick, '') as \"SocrTw\"

				,COALESCE(CCC.CmpCallCard_Dom, '') as \"House\"
				,Lpu.Lpu_Nick as \"Lpu_name\"
				,Lpu.UAddress_Address as \"UAddress_Address\"
				,Lpu.Lpu_Phone as \"Lpu_Phone\"

				,COALESCE(CCC.CmpCallCard_Korp, '') as \"Korpus\"
				,COALESCE(CCC.CmpCallCard_Kvar, '') as \"Office\"
				,CASE WHEN COALESCE(CCC.CmpCallCard_Podz,0) = 0 THEN '' ELSE CCC.CmpCallCard_Podz END as \"Entrance\"
				,CASE WHEN COALESCE(CCC.CmpCallCard_Etaj,0) = 0 THEN '' ELSE CCC.CmpCallCard_Etaj END as \"Level\"
				,CCC.CmpCallCard_Kodp as \"CodeEntrance\"

				,CCC.Person_id as \"Person_id\"
				,CCC.Person_SurName as \"Fam\"
				,CCC.Person_FirName as \"Name\"
				,CCC.Person_SecName as \"Middle\"
				,CCC.Person_Age as \"Age\"
				,pls.Polis_Ser as \"Polis_Ser\"
				,pls.Polis_Num as \"Polis_Num\"
				,pls.Polis_id as \"Polis_id\"
				,case when
					DATEDIFF('year',coalesce(PS.Person_BirthDay, CCC.Person_BirthDay, '01.01.2000'), dbo.tzGetDate())>1
				then
					'лет'
				else
					case when
						DATEDIFF('month',coalesce(PS.Person_BirthDay, CCC.Person_BirthDay), dbo.tzGetDate())>1
					then
						'мес'
					else
						'дн'
					end
				end as \"AgeTypeValue\"
				,case when
					DATEDIFF('year', coalesce(PS.Person_BirthDay, CCC.Person_BirthDay, '01.01.2000'), dbo.tzGetDate())>1
				then
					case when
						COALESCE(PS.Person_BirthDay, CCC.Person_BirthDay,0)=0
					then
						''
					else
						DATEDIFF('year', coalesce(PS.Person_BirthDay, CCC.Person_BirthDay), dbo.tzGetDate())
					end
				else
					case when
						DATEDIFF('year', coalesce(PS.Person_BirthDay, CCC.Person_BirthDay), dbo.tzGetDate())>1
					then
						DATEDIFF('month', coalesce(PS.Person_BirthDay, CCC.Person_BirthDay), dbo.tzGetDate())
					else
						DATEDIFF('day', coalesce(PS.Person_BirthDay, CCC.Person_BirthDay), dbo.tzGetDate())
					end
				end as \"AgePS\"
				,CASE
					WHEN pls.Polis_endDate is not null and pls.Polis_endDate <= cast(to_char(dbo.tzGetDate(), 'yyyymmdd') as timestamp) THEN 'orange'
					ELSE CASE
						WHEN p.PersonCloseCause_id = 2 and p.Person_closeDT is not null THEN 'red'
						ELSE CASE
							WHEN p.Server_pid = 0 THEN 'true'
							ELSE 'false'
						END
					END
				END as \"Person_IsBDZ\"

				,SX.Sex_name as \"Sex_name\"
				,RS.CmpReason_Name as \"Reason\"

				--,SS.SocStatus_Name as C_PersonSocial_id
		
				-- ,CCC.CmpCallCard_Ktov
				--,CCC.CmpCallCard_Telf as Phone
				,'' as \"Phone\"
				,CCC.CmpCallType_id as \"CmpCallType_id\"
				,CCC.CmpCallCard_Comm as \"CmpCallCard_Comm\"
				,CCT.CmpCallType_Code as \"CmpCallType_Code\"
				,coalesce(CCRT.CmpCallerType_Name,CCC.CmpCallCard_Ktov) as \"CmpCallerType_Name\"
				,CCT.CmpCallType_Name as \"CallType\"
				,CCC.CmpCallCard_IsExtra as \"CmpCallCard_IsExtra\"
			
			from
				v_CmpCallCard CCC
				LEFT JOIN Sex SX on SX.Sex_id = CCC.Sex_id
				left join v_PersonState PS on PS.Person_id = CCC.Person_id
				LEFT JOIN v_CmpReason RS on RS.CmpReason_id = CCC.CmpReason_id
				LEFT JOIN KLStreet KL_ST on KL_ST.KLStreet_id = CCC.KLStreet_id

				LEFT JOIN v_KLStreet SecondStreet on SecondStreet.KLStreet_id = CCC.CmpCallCard_UlicSecond
				LEFT JOIN v_KLSocr socrSecondStreet on SecondStreet.KLSocr_id = socrSecondStreet.KLSocr_id
				LEFT JOIN v_UnformalizedAddressDirectory UAD on UAD.UnformalizedAddressDirectory_id = CCC.UnformalizedAddressDirectory_id
				LEFT JOIN KLSocr KL_SOST on KL_SOST.KLSocr_id = KL_ST.KLSocr_id
				LEFT JOIN KLArea KL_AR on KL_AR.KLArea_id = CCC.KLSubRgn_id
				LEFT JOIN KLArea KL_CITY on KL_CITY.KLArea_id = CCC.KLCity_id
				LEFT JOIN KLArea KL_TOWN on KL_TOWN.KLArea_id = CCC.KLTown_id
				LEFT JOIN KLSocr KL_SOTW on KL_SOTW.KLSocr_id = KL_TOWN.KLSocr_id
				LEFT JOIN v_CmpCallType CCT on CCT.CmpCallType_id = CCC.CmpCallType_id
				LEFT JOIN v_CmpCallerType CCRT on CCRT.CmpCallerType_id = CCC.CmpCallerType_id
				left join v_Lpu Lpu on Lpu.Lpu_id = CCC.Lpu_id
				LEFT JOIN v_EmergencyTeam EMT on EMT.EmergencyTeam_id = CCC.EmergencyTeam_id
				LEFT JOIN v_LpuBuilding LB on LB.LpuBuilding_id = CCC.LpuBuilding_id
				-- left join v_SocStatus SS on SS.SocStatus_id = CLC.SocStatus_id
				left join v_PersonState_all p on p.Person_id = CCC.Person_id
				left join v_Polis pls on pls.Polis_id = p.Polis_id
				left join v_KLSubRgn SRGN on SRGN.KLSubRgn_id = CCC.KLSubRgn_id
				LEFT JOIN v_CmpCloseCard CCLC on CCLC.CmpCallCard_id = CCC.CmpCallCard_id

				LEFT JOIN v_KLStreet SecondCCLCStreet on SecondCCLCStreet.KLStreet_id = CCLC.CmpCloseCard_UlicSecond
				LEFT JOIN v_KLSocr socrSecondCCLCStreet on SecondCCLCStreet.KLSocr_id = socrSecondCCLCStreet.KLSocr_id

				left join lateral (
					select to_char(ETSH.EmergencyTeamStatusHistory_insDT, 'hh24:mi') as timeIns
					from 
						v_EmergencyTeamStatusHistory ETSH
						LEFT JOIN v_EmergencyTeamStatus ETS on ETS.EmergencyTeamStatus_id = ETSH.EmergencyTeamStatus_id
					where ETSH.EmergencyTeam_id = CCC.EmergencyTeam_id and ETSH.CmpCallCard_id = CCC.CmpCallCard_id and ETS.EmergencyTeamStatus_Code = 3
					ORDER BY ETSH.EmergencyTeamStatusHistory_insDT ASC
					LIMIT 1
				) startTransportir on true --Начало транспортировки
				left join lateral (
					select to_char(ETSH.EmergencyTeamStatusHistory_insDT, 'hh24:mi') as timeIns
					from 
						v_EmergencyTeamStatusHistory ETSH
						LEFT JOIN v_EmergencyTeamStatus ETS on ETS.EmergencyTeamStatus_id = ETSH.EmergencyTeamStatus_id
					where ETSH.EmergencyTeam_id = CCC.EmergencyTeam_id and ETSH.CmpCallCard_id = CCC.CmpCallCard_id and ETS.EmergencyTeamStatus_Code = 41
					ORDER BY ETSH.EmergencyTeamStatusHistory_insDT DESC
					LIMIT 1
				) arrivalMO on true --Прибытия в МО
				left join lateral (
					select to_char(ETSH.EmergencyTeamStatusHistory_insDT, 'hh24:mi') as timeIns
					from 
						v_EmergencyTeamStatusHistory ETSH
						LEFT JOIN v_EmergencyTeamStatus ETS on ETS.EmergencyTeamStatus_id = ETSH.EmergencyTeamStatus_id
					where ETSH.EmergencyTeam_id = CCC.EmergencyTeam_id and ETSH.CmpCallCard_id = CCC.CmpCallCard_id and ETS.EmergencyTeamStatus_Code = 4
					ORDER BY ETSH.EmergencyTeamStatusHistory_insDT DESC
					LIMIT 1
				) endService on true --Окончание вызова
			where
				CCC.CmpCallCard_id = :CmpCallCard_id
			limit 1
		";

		$result = $this->db->query($query, array(
			'CmpCallCard_id' => $data['CmpCallCard_id']
		));


		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Удаление карты вызова
	 *
	 * @param array $data
	 * @return bool
	 */
	function deleteCmpCallCard($data = array(), $ignoreRegistryCheck = false, $delCallCard = true)
	{

		if (!array_key_exists('CmpCallCard_id', $data) || !$data['CmpCallCard_id']) {
			return array(array('Error_Msg' => 'Не указан идентификатор карты вызова.'));
		}

		$checkLock = $this->checkLockCmpCallCard($data);
		if ($checkLock != false && is_array($checkLock) && isset($checkLock[0]) && isset($checkLock[0]['CmpCallCard_id'])) {
			return array(array('Error_Msg' => 'Невозможно удалить. Карта вызова редактируется'));
		}

		if ($ignoreRegistryCheck === false) {
			// Проверка наличия карты вызова в реестре
			$data['CmpCloseCard_id'] = $this->getFirstResultFromQuery("
                select CmpCloseCard_id as \"CmpCloseCard_id\"
                from {$this->schema}.v_CmpCloseCard
                where CmpCallCard_id = :CmpCallCard_id
                limit 1;
            ", array(
				'CmpCallCard_id' => $data['CmpCallCard_id']
			));

			$this->load->model('RegistryUfa_model', 'RegistryUfa_model');
			$registryData = $this->RegistryUfa_model->checkEvnAccessInRegistry($data);

			if (is_array($registryData)) {
				if (isset($registryData['Error_Msg'])) {
					$registryData['Error_Msg'] = str_replace('Удаление записи невозможно', ' Удалите Карту вызова из реестра и повторите действие', $registryData['Error_Msg']);
				}
				return $registryData;
			}
		}

		$sql = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Message\"
			from p_CmpCallCard_del (
				CmpCallCard_id := :CmpCallCard_id,
				pmUser_id := :pmUser_id
			);
		";

		$sqlArr = array(
			'CmpCallCard_id' => $data['CmpCallCard_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		$result = $this->db->query($sql, $sqlArr);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return array(array('Error_Msg' => 'Во время удаления карты вызова произошла ошибка. При повторении ошибки обратитесь к администратору.'));
		}
	}

	/**
	 * для ЭМК
	 * @param type $data
	 * @return array
	 */
	function printCmpCloseCardEMK($data)
	{
		$query = "
			select
				CLC.CmpCallCard_id as \"CmpCallCard_id\"
				,CLC.CmpCloseCard_id as \"CmpCloseCard_id\"
				--,convert(varchar, CLC.CmpCloseCard_insDT, 104) as CardDate				
				,to_char(CC.CmpCallCard_insDT, 'dd.mm.yyyy') as \"CallCardDate\"				
				,CLC.Day_num as \"Day_num\"
				,CLC.Year_num as \"Year_num\"
				--,CLC.Feldsher_id
				--,CLC.StationNum
				--,CLC.EmergencyTeamNum
				,to_char(CLC.AcceptTime, 'dd.mm.yyyy hh24:mi') as \"AcceptDateTime\"
				,SX.Sex_name as \"Sex_name\"
				,CLC.SummTime as \"SummTime\"
				,CLC.Fam as \"Fam\"
				,CLC.Name as \"Name\"
				,CLC.Middle as \"Middle\"
				--,CLC.Age
				,DIAG.Diag_FullName as \"Diag\"				
				--,MPA.Person_Fio as FeldsherAcceptName
				--,CLC.Feldsher_id as FeldsherAcceptName
				,rtrim(coalesce(UCA.PMUser_surName,'')) ||' '||rtrim(coalesce(UCA.PMUser_firName,'')) ||' '|| rtrim(COALESCE(UCA.PMUser_secName,'')) as \"FeldsherAcceptName\"
				--,UCT.PMUser_Name as FeldsherTransName
				,to_char(ccp.CmpCallCardCostPrint_setDT, 'dd.mm.yyyy') as \"CmpCallCardCostPrint_setDT\"
				,ccp.CmpCallCardCostPrint_IsNoPrint as \"CmpCallCardCostPrint_IsNoPrint\"
				,STR(ccp.CmpCallCardCostPrint_Cost, 19, 2) as \"CostPrint\"
				,COALESCE(msfC.Person_Fio, msfE.Person_Fio) as \"EmergencyTeam_HeadShift_Name\"
			from
				{$this->schema}.v_CmpCloseCard CLC
				LEFT JOIN v_CmpCallCard CC on CC.CmpCallCard_id = CLC.CmpCallCard_id
				left join v_CmpCallCardCostPrint ccp on ccp.CmpCallCard_id = cc.CmpCallCard_id
				LEFT JOIN Sex SX on SX.Sex_id = CLC.Sex_id
				left join v_Diag DIAG on DIAG.Diag_id = CLC.Diag_id
				left join v_EmergencyTeam ET on CC.EmergencyTeam_id = ET.EmergencyTeam_id
				LEFT JOIN v_MedStaffFact msfE on msfE.MedPersonal_id = ET.EmergencyTeam_HeadShift
				LEFT JOIN v_MedStaffFact msfC on msfC.MedStaffFact_id = CLC.MedStaffFact_id
				LEFT JOIN v_pmUserCache UCA on UCA.PMUser_id = CLC.pmUser_insID
				--LEFT JOIN v_MedPersonal MPA on MPA.MedPersonal_id = CLC.FeldsherAccept
				--LEFT JOIN v_pmUserCache UCT on UCT.PMUser_id = CLC.FeldsherTrans
			where
				CLC.CmpCloseCard_id = :CmpCloseCard_id
			limit 1
		";


		$result = $this->db->query($query, array(
			'CmpCloseCard_id' => $data['CmpCloseCard_id']
		));


		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
}
