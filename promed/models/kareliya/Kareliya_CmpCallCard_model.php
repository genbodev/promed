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

class Kareliya_CmpCallCard_model extends CmpCallCard_model {
	
	
	
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
				COALESCE(PS.Polis_Ser, CCC.Person_PolisSer) as Polis_Ser,
				COALESCE(PS.Polis_Num, CCC.Person_PolisNum) as Polis_Num,
				PS.Person_EdNum as Polis_EdNum,
				CCC.Person_id,
				CClC.Ktov,
				CClC.CmpCallerType_id,

				CCC.KLRgn_id
				,CClC.Area_id as Area_id
				,CClC.City_id as City_id
				,CClC.Town_id as Town_id
				,CClC.Street_id  as Street_id
				,CClC.CmpCloseCard_Street as CmpCloseCard_Street
				,CClC.Room	
				,CClC.Korpus	
				
				,CClC.EmergencyTeamNum as EmergencyTeamNum
				,CCLC.EmergencyTeam_id as EmergencyTeam_id
				,CClC.EmergencyTeamSpec_id as EmergencyTeamSpec_id
				,CClC.LpuSection_id as LpuSection_id
				,CClC.MedPersonal_id as MedPersonal_id
				,CClC.MedStaffFact_id as MedStaffFact_id
				,CClC.StationNum as StationNum
				,CClC.LpuBuilding_id
				,CClC.Feldsher_id
				,CClC.FeldsherAccept
				,CClC.FeldsherTrans

				,CClC.CmpCloseCard_IsNMP

				,CClC.PayType_id
		
				,CClC.isSogl
				,CClC.isOtkazMed
				,CClC.isOtkazHosp				
		
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
				--,CASE WHEN ISNULL(CCC.CmpCallCard_IsNMP,1) = 1 THEN NULL ELSE 1 END as CmpCallCard_IsNMP
				,CASE WHEN ISNULL(CCC.CmpCallCard_IsReceivedInPPD,1) = 1 THEN NULL ELSE 1 END as CmpCallCard_IsReceivedInPPD
				,CClC.CallPovod_id				
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
				,CClC.FeldsherAccept as FeldsherAcceptName
				,CClC.FeldsherTrans as FeldsherTransName
				
			from
				v_CmpCloseCard CClC with (nolock)
				left join v_CmpCallCard CCC (nolock) on CCC.CmpCallCard_id = CClC.CmpCallCard_id
				left join v_PersonState PS (nolock) on PS.Person_id = CCC.Person_id
				LEFT JOIN v_Lpu L with (nolock) on L.Lpu_id = CClC.Lpu_id	
				LEFT JOIN v_EmergencyTeam EMT with (nolock) on EMT.EmergencyTeam_id = CCC.EmergencyTeam_id	
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
				,ClC.CmpCloseCard_Street as Street2

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
				
				,CLC.FeldsherAccept as FeldsherAcceptName
				,CLC.FeldsherTrans as FeldsherTransName
				
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
				LEFT JOIN v_CmpCallerType CCrT with (nolock) on CCrT.CmpCallerType_id = CLC.CmpCallerType_id
				left join v_Diag DIAG (nolock) on DIAG.Diag_id = CLC.Diag_id
				left join v_CmpCallCard CC (nolock) on CC.CmpCallCard_id = CLC.CmpCallCard_id
				left join v_Lpu Lpu (nolock) on Lpu.Lpu_id = CC.Lpu_id
				LEFT JOIN v_LpuBuilding LB (nolock) on LB.LpuBuilding_id = CLC.LpuBuilding_id
			
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
	 * Назначаем новый Person_id при идентификации
	 * @todo При необходимости вынести метод в базовый класс
	 *
	 * @return null on empty data or query result
	 */
	protected function CmpCallCardSetPerson( $CmpCallCard_id, $Person_id, $pmUser_id ){
		if ( empty( $Person_id ) || empty( $CmpCallCard_id ) ) {
			return;
		}

		$sql = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :CmpCallCard_id;
			exec p_CmpCallCard_setPerson
				@CmpCallCard_id = @Res,
				@Person_id = :Person_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as CmpCallCard_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		return $this->db->query( $sql, array(
			'CmpCallCard_id' => $CmpCallCard_id,
			'Person_id' => $Person_id,
			'pmUser_id' => $pmUser_id
		), false );
	}

	/**
	 * Возвращает CmpCloseCard_id по указанному CmpCallCard_id
	 *
	 * @param type $CmpCallCard_id Идентификатор талона вызова
	 *
	 * @return CmpCloseCard_id or false
	 */
	protected function getCmpCloseCardIdByCmpCallCardId( $CmpCallCard_id ){
		$sql = "
			SELECT TOP 1
				CLC.CmpCloseCard_id
			FROM
				v_CmpCloseCard CLC with (nolock)
			WHERE
				CLC.CmpCallCard_id = :CmpCallCard_id
		";
		$query = $this->db->query( $sql, array(
			'CmpCallCard_id' => $CmpCallCard_id
		) );
		
		if ( is_object( $query ) ) {
			$result = $query->row_array();
			return isset( $result['CmpCloseCard_id'] ) ? $result['CmpCloseCard_id'] : null;
		}
		
		return false;
	}

	/**
	 * Проверка совпадения количества параметров в процедуре. Для отладки.
	 *
	 * @param string $procedure Procedure scheme.name
	 * @param array $data
	 *
	 * @return output unexist fields
	 */
	protected function checkParamsNumberInProcedureWithExistedArray( $procedure, $data ){
		$sql = "SELECT 'Parameter_name' = name FROM sys.parameters WHERE object_id = object_id('".$procedure."')";
		$query = $this->db->query( $sql );
		$result = $query->result_array();

		if ( sizeof( $result ) != sizeof( $data ) ) {
			echo 'Переданных параметром меньше на: '.( sizeof( $result ) - sizeof( $data ) )."\n";
		}

		// Не используем array_flip т.к может быть передан объект DateTime
		$fields = array();
		foreach( $data as $k => $v ) {
			$fields[] = $k;
		}

		foreach( $result as $k2 => $v2 ) {
			if ( $key = array_search( substr( $v2['Parameter_name'], 1 ), $fields ) ) {
				unset( $fields[ $key ] );
			}
		}

		echo "Лишние поля:\n".implode("\n",$fields);
		exit;
	}

	/**
	 * Хелпер записывает список параметров в текстовом виде для запроса
	 *
	 * @param array $params <key> => <value>
	 * @return string
	 */
	protected function prepareParamsForProcedureSql( $params ){
		$txt = '';
		foreach( $params as $k => $v ){
			$txt .= "@".$k." = :".$k.",\r\n";
		}
		return $txt;
	}

	/**
	 * @var array Список комбобоксов формы закрытия вызова
	 */
	protected $cmpclosecard110_combos = array(
			'Condition_id', 'Behavior_id', 'Cons_id', 'Pupil_id', 'Kozha_id', 'Hypostas_id',
			'Crop_id', 'Hale_id', 'Rattle_id', 'Shortwind_id', 'Heart_id', 'Noise_id', 'Pulse_id', 'Lang_id',
			'Gaste_id', 'Liver_id', 'Complicat_id', 'ComplicatEf_id', 'Patient_id', 'AgeType_id',
			'TransToAuto_id',
			'ResultUfa_id',
			'PersonRegistry_id', 'PersonSocial_id', 'CallTeamPlace_id',
			'Delay_id', 'TeamComplect_id', 'CallPlace_id', 'AccidentReason_id', 'Trauma_id', 'Result_id'
		);

	/**
	 * Сохранение связанных комбобоксов с картой закрытия вызова
	 *
	 * @param array $data Массив данных пришедших от пользователя
	 * @param int $CmpCloseCard_id Актуальный ID карты закрытия вызова
	 * @param int $new_CmpCloseCard_id Новый ID карты закрытия вызова (для версионности, не актуальный)
	 * 
	 * @return boolean
	 */
	 /*
	protected function saveCmpCloseCard110Combos( $data, $CmpCloseCard_id, $new_CmpCloseCard_id, $action ){
		// Собираем значения комбобоксов
		$combo_fields = array();
		foreach( $this->cmpclosecard110_combos as $name ) {
			if ( !isset( $data[ $name ] ) ) {
				continue;
			}
			// Если это чекбокс, собираем отмеченные значения
			if ( is_array( $data[ $name ] ) ) {
				foreach( $data[ $name ] as $field ) {
					$combo_fields[] = $this->getComboIdByCode($field);
				}
			}
			// Радиокнопка
			elseif( (int)$data[ $name ] == $data[ $name ] && (int)$data[ $name ] > 0 ) {
				$combo_fields[] = $this->getComboIdByCode((int)$data[ $name ]);
			}
		}
		
		// Добавление?
		if ( $action == 'add' ) {
			// Запишем полученные значения комбобоксов и радио
			$sql = "
				declare
					@Res bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);
				set @Res = 0;
				exec p_CmpCloseCardRel_ins
					@CmpCloseCardRel_id = @Res output,
					@CmpCloseCard_id = :CmpCloseCard_id,
					@CmpCloseCardCombo_id = :CmpCloseCardCombo_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @Res as CmpCloseCard_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";

			foreach( $combo_fields as $CmpCloseCardCombo_id ){
				$this->db->query( $sql, array(
					'CmpCloseCardCombo_id' => $CmpCloseCardCombo_id,
					'CmpCloseCard_id' => $CmpCloseCard_id,
					'pmUser_id' => $data[ 'pmUser_id' ],
				) );
			}

			// Запишем значения полей ввода
			if ( is_array( $data[ 'ComboValue' ] ) ) {
				$sql = "
					declare
						@Res bigint,
						@ErrCode int,
						@ErrMessage varchar(4000);
					set @Res = 0;
					exec p_CmpCloseCardRel_ins
						@CmpCloseCardRel_id = @Res output,
						@CmpCloseCard_id = :CmpCloseCard_id,
						@CmpCloseCardCombo_id = :CmpCloseCardCombo_id,
						@Localize = :Localize,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
					select @Res as CmpCloseCard_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";

				foreach( $data[ 'ComboValue' ] as $CmpCloseCardCombo_id => $Localize ){
					if ( empty( $Localize ) ) {
						continue;
					}
					$this->db->query( $sql, array(
						'CmpCloseCardCombo_id' => $this->getComboIdByCode($CmpCloseCardCombo_id),
						'Localize' => $Localize,
						'CmpCloseCard_id' => $CmpCloseCard_id,
						'pmUser_id' => $data[ 'pmUser_id' ],
					) );
				}
			}

		} elseif ( $action == 'edit' ) {
			// Т.к. при редактировании текущие значения карты закрытия вызова
			// сохраняются в новую запись, необходимо обновить идентификаторы
			// в связи у комбобоксов
			$sql = "
				declare
					@pmUser_id bigint,
					@Error_Code int,
					@Error_Message varchar(4000);
				exec p_CmpCloseCardRel_updVersion
					@CmpCloseCard_oldId = :CmpCloseCard_oldId,
					@CmpCloseCard_newId = :CmpCloseCard_newId,
					@pmUser_id = :pmUser_id
			";
			$this->db->query( $sql, array(
				'CmpCloseCard_oldId' => $CmpCloseCard_id,
				'CmpCloseCard_newId' => $new_CmpCloseCard_id,
				'pmUser_id' => $data['pmUser_id']
			) );
			
			// Записываем новые значения в исходную запись
			$sql = "
				declare
					@Res bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);
				set @Res = 0;
				exec p_CmpCloseCardRel_ins
					@CmpCloseCardRel_id = @Res output,
					@CmpCloseCard_id = :CmpCloseCard_id,
					@CmpCloseCardCombo_id = :CmpCloseCardCombo_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @Res as CmpCloseCard_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";

			foreach( $combo_fields as $CmpCloseCardCombo_id ){
				$this->db->query( $sql, array(
					'CmpCloseCardCombo_id' => $CmpCloseCardCombo_id,
					'CmpCloseCard_id' => $CmpCloseCard_id,
					'pmUser_id' => $data[ 'pmUser_id' ],
				) );
			}

			// Запишем значения полей ввода
			if ( is_array( $data[ 'ComboValue' ] ) ) {
				$sql = "
					declare
						@Res bigint,
						@ErrCode int,
						@ErrMessage varchar(4000);
					set @Res = 0;
					exec p_CmpCloseCardRel_ins
						@CmpCloseCardRel_id = @Res output,
						@CmpCloseCard_id = :CmpCloseCard_id,
						@CmpCloseCardCombo_id = :CmpCloseCardCombo_id,
						@Localize = :Localize,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
					select @Res as CmpCloseCard_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";

				foreach( $data[ 'ComboValue' ] as $CmpCloseCardCombo_id => $Localize ){
					if ( empty( $Localize ) ) {
						continue;
					}
					$this->db->query( $sql, array(
						'CmpCloseCardCombo_id' => $CmpCloseCardCombo_id,
						'Localize' => $Localize,
						'CmpCloseCard_id' => $CmpCloseCard_id,
						'pmUser_id' => $data[ 'pmUser_id' ],
					) );
				}
			}
		}

		// @todo Вывод false если не удалось сохранить какой-то комбобокс
		return true;
	}
	*/
	/**
	 * Сохранение формы 110у с набором полей для Карелии
	 * 
	 * @param array $data
	 * @return boolean 
	 */
	 /*
	public function saveCmpCloseCard110( $data ) {
		
		$action = null;
		$oldresult = null;
		$NewCmpCloseCard_id = null;
		$relProcedure = 'p_CmpCloseCardRel_ins';
		
		$rules = array(
			array( 'field' => 'Kilo' , 'label' => 'Километраж' , 'type' => 'float', 'maxValue' => '1000' ),
		);

		$queryParams = $this->_checkInputData( $rules , $data , $err , true ) ;
		if ( !empty( $err ) )
			return $err ;
		
		
		if ( !empty( $data['CmpCloseCard_id'] ) ) {
			$action = 'edit';

			// Сперва нужно отредактировать запись в талоне. Назначаем новый Person_id при идентификации
			// @todo Зачем этот функционал? Надо ли его вызывать внутри блока где CmpCloseCard_id получается через CmpCallCard_id?
			$this->CmpCallCardSetPerson( $data[ 'CmpCallCard_id' ], $data[ 'Person_id' ], $data[ 'pmUser_id' ] );
		} elseif ( ( $CmpCloseCard_id = $this->getCmpCloseCardIdByCmpCallCardId( $data[ 'CmpCallCard_id' ] ) ) ) {
			$action = 'edit';
			$data['CmpCloseCard_id'] = $CmpCloseCard_id;
		} else {
			$action = 'add';
		}
		unset( $CmpCloseCard_id );
		
		$queryParams = array(
			'CmpCallCard_id' => $data['CmpCallCard_id'],
			
			'Day_num' => $data['Day_num'],
			'Year_num' => $data['Year_num'],
			'Feldsher_id' => (isset($data['Feldsher_id'])) ? $data['Feldsher_id'] : null,
			'StationNum' => (isset( $data[ 'StationNum' ])) ? $data[ 'StationNum' ] : null,
			'LpuBuilding_id' => $data['LpuBuilding_id'],
			'EmergencyTeamNum' => $data['EmergencyTeamNum'],
			'EmergencyTeam_id' => ($data['EmergencyTeam_id'] != '') ? $data['EmergencyTeam_id'] : null,
			'EmergencyTeamSpec_id' => $data['EmergencyTeamSpec_id'],
			'LpuSection_id' => $data['LpuSection_id'],
			'MedPersonal_id' => $data['MedPersonal_id'],
			'MedStaffFact_id' => $data['MedStaffFact_id'],
			
			'AcceptTime' => ($data['AcceptTime'] != '') ? $data['AcceptTime'] : null,
			'TransTime' => ($data['TransTime'] != '') ? $data['TransTime'] : null,
			'GoTime' => ($data['GoTime'] != '') ? $data['GoTime'] : null,
			'ArriveTime' => ($data['ArriveTime'] != '') ? $data['ArriveTime'] : null,
			'TransportTime' => ($data['TransportTime'] != '') ? $data['TransportTime'] : null,
			'ToHospitalTime' => ($data['ToHospitalTime'] != '') ? $data['ToHospitalTime'] : null,
			'EndTime' => ($data['EndTime'] != '') ? $data['EndTime'] : null,
			'BackTime' => ($data['BackTime'] != '') ? $data['BackTime'] : null,
			'SummTime' => $data['SummTime'],
			
			'Area_id' => (int)$data['Area_id'] ? (int)$data['Area_id'] : null,
			'City_id' =>  (int)$data['City_id'] ? (int)$data['City_id'] : null,
			'Town_id' => (int)$data['Town_id'] ? (int)$data['Town_id'] : null,
			'Street_id' => (isset( $data[ 'Street_id' ]) && !isset( $data[ 'CmpCloseCard_Street' ] ) && $data[ 'Street_id' ] > 0 ) ? $data[ 'Street_id' ] : null,
			'CmpCloseCard_Street' => (!empty( $data['CmpCloseCard_Street'] )) ? $data[ 'CmpCloseCard_Street' ] : null,
			'House' => $data['House'],
			'Korpus' => isset( $data['Korpus'] ) ? $data['Korpus'] : '',
			'Office' => $data['Office'],
			'Room' => isset( $data['Room'] ) ? $data['Room'] : '',
			'Entrance' => !empty( $data['Entrance'] ) ? $data['Entrance'] : null,
			'Level' => !empty( $data['Level'] ) ? $data['Level'] : null,
			'CodeEntrance' => $data['CodeEntrance'],
			
			'Fam' => $data['Fam'],
			'Name' => $data['Name'],
			'Middle' => $data['Middle'],
			'Age' => $data['Age'],
			'Sex_id' => $data['Sex_id'],
			'Work' => $data['Work'],
			'DocumentNum' => $data['DocumentNum'],
			
			'Ktov' => (!empty( $data[ 'Ktov' ] ) ? $data[ 'Ktov' ] : null),
			'CmpCallerType_id' => (!empty( $data[ 'CmpCallerType_id' ] ) ? $data[ 'CmpCallerType_id' ] : null),
			'Phone' => $data['Phone'],
			
			'FeldsherAccept' => $data[ 'FeldsherAccept' ],
			'FeldsherTrans' => $data[ 'FeldsherTrans' ],
			
			'CallType_id' => $data['CallType_id'],
			'CallPovod_id' => $data['CallPovod_id'],
			
			'isAlco' => (($data['isAlco'] > 0)?$data['isAlco']:null),
			
			'Complaints' => $data['Complaints'],
			
			'Anamnez' => $data['Anamnez'],
			
			'isMenen' => $data['isMenen'],
			'isNist' => $data['isNist'],
			'isAnis' => $data['isAnis'],
			'isLight' => $data['isLight'],
			'isAcro' => $data['isAcro'],
			'isMramor' => $data['isMramor'],
			'isHale' => $data['isHale'],
			'isPerit' => $data['isPerit'],
			
			'Urine' => $data['Urine'],
			'Shit' => $data['Shit'],
			'OtherSympt' => $data['OtherSympt'],
			'WorkAD' => $data['WorkAD'],
			'AD' => $data['AD'],
			'Chss' => $data['Chss'],
			'Pulse' => $data['Pulse'],
			'Temperature' => $data['Temperature'],
			'Chd' => $data['Chd'],
			'Pulsks' => $data['Pulsks'],
			'Gluck' => $data['Gluck'],
			'LocalStatus' => $data['LocalStatus'],
			'Ekg1' => $data['Ekg1'],
			'Ekg1Time' => ($data['Ekg1Time'] != '') ? $data['Ekg1Time'] : null,
			'Ekg2' => $data['Ekg2'],
			'Ekg2Time' => ($data['Ekg2Time'] != '') ? $data['Ekg2Time'] : null,
			
			'Diag_id' => !empty( $data['Diag_id'] ) ? $data['Diag_id'] : null,
			
			'HelpPlace' => $data['HelpPlace'],
			'HelpAuto' => $data['HelpAuto'],
			
			'EfAD' => $data['EfAD'],
			'EfChss' => $data['EfChss'],
			'EfPulse' => $data['EfPulse'],
			'EfTemperature' => $data['EfTemperature'],
			'EfChd' => $data['EfChd'],
			'EfPulsks' => $data['EfPulsks'],
			'EfGluck' => $data['EfGluck'],
			
			'Kilo' => $data['Kilo'],
			'DescText' => $data['DescText'],
			
			'PayType_id' => !empty( $data[ 'PayType_id' ] ) ? $data[ 'PayType_id' ] : null,

			'isSogl' => isset( $data[ 'isSogl' ] ) ? $data[ 'isSogl' ] : null,
			'isOtkazMed' => isset( $data[ 'isOtkazMed' ] ) ? $data[ 'isOtkazMed' ] : null,
			'isOtkazHosp' => isset( $data[ 'isOtkazHosp' ] ) ? $data[ 'isOtkazHosp' ] : null,

			'CmpCloseCard_IsNMP' => ( (isset($data[ 'CmpCloseCard_IsNMP' ]) && $data[ 'CmpCloseCard_IsNMP' ] == 'on'  ) ? 2 : 1),

			'pmUser_id' => $data['pmUser_id']
		);

		$UnicNums = ';';
		
		if ( $action == 'edit' ) {
			// Версионность: создаем новую запись на основе исходной и обновлям исходную новыми данными
			// 1. Выбираем исходную запись
			$sql = "SELECT CLC.* FROM v_CmpCloseCard CLC with (nolock) WHERE CLC.CmpCloseCard_id=:CmpCloseCard_id";
			$query2 = $this->db->query( $sql, array( 'CmpCloseCard_id' => $data['CmpCloseCard_id'] ) );
			if ( !is_object( $query2 ) ) {
				return false;
			}
			$source_params = $query2->row_array();
			$oldresult = $source_params;
			$source_params[ 'pmUser_id' ] = $source_params[ 'pmUser_insID' ];

			unset(
				$source_params['pmUser_insID'],
				$source_params['pmUser_updID'],
				$source_params['CmpCloseCard_insDT'],
				$source_params['CmpCloseCard_updDT'],

				$source_params['CmpCloseCard_id'],

				$source_params['CmpCloseCard_IsPaid'], // ? Почему этого поля нет при вставке не понятно
				$source_params['CmpCloseCard_IsInReg'] // #88691
			);

			// Проверка совпадения количества параметров в процедуре
			//$this->checkParamsNumberInProcedureWithExistedArray( 'p_CmpCloseCard_ins', $source_params );
		
		

			// 2. Создаем новую запись на основе исходной
			$sql = "
				declare
					@Res bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);

				set @Res = 0;

				exec p_CmpCloseCard_ins
					@CmpCloseCard_id = @Res output,
					".$this->prepareParamsForProcedureSql( $source_params )."
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;

				if ( @ErrMessage is null )
					exec p_CmpCloseCard_del
						@CmpCloseCard_id = @Res,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;

				select @Res as CmpCloseCard_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			$query3 = $this->db->query( $sql, $source_params );

			if ( !is_object( $query3 ) ) {
				return false;
			}
			$result = $query3->row_array();
			$NewCmpCloseCard_id = $result['CmpCloseCard_id'];

			// Непонятно почему два этих кейса вынесены только для редактирования
			if (isset($data['CmpCallCard_IsReceivedInPPD']) && $data['CmpCallCard_IsReceivedInPPD'] == 'on') {
				$data1['CmpCallCard_id'] = $data['CmpCallCard_id'];
				$data1['CmpCallCard_IsReceivedInPPD'] = 2;
				$data1['CmpCallCardStatusType_id'] = 6;
				$data1['pmUser_id'] = $data['pmUser_id'];
				$this->setStatusCmpCallCard($data1);
			}
			if ( empty( $data['Person_id'] ) ) {
				$data1['CmpCallCard_id'] = $data['CmpCallCard_id'];
				$data1['Person_id'] = null;
				$data1['pmUser_id'] = $data['pmUser_id'];
				$this->setPerson( $data1 );
			}
			
			// 3. Обновляем исходную запись новыми данными
			$newParams = $queryParams;
			$newParams[ 'CmpCloseCard_id' ] = $data[ 'CmpCloseCard_id' ];

			$sql = "
				declare
					@Res bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);

				set @Res = :CmpCloseCard_id;

				exec p_CmpCloseCard_upd
					".$this->prepareParamsForProcedureSql( $newParams )."
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @Res as CmpCloseCard_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			$query4 = $this->db->query( $sql, $newParams );
			if ( !is_object( $query4 ) ) {
				return false;
			}
			$result_main = $query4->result('array');

			// 4 - устанавливаем значение старого id в перезаписанной записи
			// Почему это нельзя обновить на шаге 3 ?
			$this->db->query("
				exec p_CmpCloseCard_setFirstVersion
					@CmpCloseCard_id = :CmpCloseCard_id,
					@CmpCloseCard_firstVersion = :CmpCloseCard_firstVersion,
					@pmUser_id = :pmUser_id
			", array(
				'CmpCloseCard_id' => $data[ 'CmpCloseCard_id' ],
				'CmpCloseCard_firstVersion' => $NewCmpCloseCard_id,
				'pmUser_id' => $data['pmUser_id'],
			) );			
		} else {
			$sql = "
				declare
					@Res bigint,
					@ErrCode int,
					@ErrMessage varchar(4000)
					".$UnicNums."
				set @Res = 0;
				SET NOCOUNT ON;
				exec p_CmpCloseCard_ins
					@CmpCloseCard_id = @Res output,
					".$this->prepareParamsForProcedureSql( $queryParams )."
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @Res as CmpCloseCard_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			$query = $this->db->query( $sql, $queryParams );
			$result_main = $query->result('array');
		}
		

		if ( !empty( $data[ 'AcceptTime' ] ) ) {			
			$update_CmpCallCard_prmDT_result = $this->swUpdate( 'CmpCallCard', array(
				'CmpCallCard_id' => $data[ 'CmpCallCard_id' ],
				'CmpCallCard_prmDT' => $data[ 'AcceptTime' ],
				'pmUser_id' => $data[ 'pmUser_id' ],
				//'CmpCallCard_IsReceivedInPPD' =>  array_key_exists( 'CmpCallCard_IsReceivedInPPD', $data ) ? 1 : null
				'CmpCallCard_IsReceivedInPPD' =>  (isset($data['CmpCallCard_IsReceivedInPPD']) && $data['CmpCallCard_IsReceivedInPPD'] == 'on')?2:1
				//'CmpCallCard_IsNMP' => (isset($data['CmpCallCard_IsNMP']) && $data['CmpCallCard_IsNMP'] == 'on')?2:1
			) );
			
			if ( !$this->isSuccessful( $update_CmpCallCard_prmDT_result ) ) {
				return $update_CmpCallCard_prmDT_result;
			}
		}

		//унес сохранение комбос в функцию
		$res = $this->saveCmpCloseCardComboValues($data, $action, $oldresult, $result_main, $NewCmpCloseCard_id, $UnicNums, $relProcedure);
		
		return $res;
	}
	*/
	
		
}
