<?php	defined('BASEPATH') or die ('No direct script access allowed');

class EmergencyTeam_model extends swModel {
	
	/**
	 * @desc Сохранение бригады СМП
	 * @param array $data Данные ProcessInputData
	 * @return array or false
	 */
	public function saveEmergencyTeam( $data ){
		
		if ( !array_key_exists( 'Lpu_id', $data ) || !$data['Lpu_id'] ) {
			return array( array( 'Err_Msg' => 'Не указан идентификатор ЛПУ') );
		}
		
		$sqlArr = array(
			'EmergencyTeam_Num'				=> $data['EmergencyTeam_Num'],
			'EmergencyTeam_CarNum'			=> $data['EmergencyTeam_CarNum'],
			'EmergencyTeam_CarBrand'		=> $data['EmergencyTeam_CarBrand'],
			'EmergencyTeam_CarModel'		=> $data['EmergencyTeam_CarModel'],
			'EmergencyTeam_PortRadioNum'	=> $data['EmergencyTeam_PortRadioNum'],
			'EmergencyTeam_GpsNum'			=> $data['EmergencyTeam_GpsNum'],
			'LpuBuilding_id'	=> $data['LpuBuilding_id'],
			'EmergencyTeam_BaseStationNum'	=> $data['EmergencyTeam_BaseStationNum'],
			'EmergencyTeamSpec_id'			=> $data['EmergencyTeamSpec_id'],
			'EmergencyTeam_HeadShift'		=> $data['EmergencyTeam_HeadShift'],
			'EmergencyTeam_Driver'			=> $data['EmergencyTeam_Driver'],
			'EmergencyTeam_Assistant1'		=> $data['EmergencyTeam_Assistant1'],
			'EmergencyTeam_Assistant2'		=> $data['EmergencyTeam_Assistant2'],
			'Lpu_id'						=> $data['Lpu_id'],
			'pmUser_id'						=> $data['pmUser_id'],
		);
		
		$additionalParamForcheckExistetETQuery ='';
		if ( !array_key_exists( 'EmergencyTeam_id', $data ) || !$data['EmergencyTeam_id'] ) {
			$procedure = 'p_EmergencyTeam_ins';
			$sqlArr['EmergencyTeam_id'] = null;
		} else {
			$procedure = 'p_EmergencyTeam_upd';
			$sqlArr['EmergencyTeam_id'] = $data['EmergencyTeam_id'];
			$additionalParamForcheckExistetETQuery = ' AND ET.EmergencyTeam_id != :EmergencyTeam_id';
		}

		$checkExistetETQuery = "
			SELECT
				ET.EmergencyTeam_id
			FROM 
				v_EmergencyTeam ET with (nolock)
			WHERE 
				ET.EmergencyTeam_Num = :EmergencyTeam_Num AND
				ET.Lpu_id = :Lpu_id
				{$additionalParamForcheckExistetETQuery}
			";
		$result = $this->db->query( $checkExistetETQuery, $sqlArr );
		if ( is_object( $result ) ) {
			$result = $result->result('array');

			if (sizeof($result)) {
				return array(array('success'=>false,'Error_Msg'=>'Бригада с таким номером уже существует в данном ЛПУ. Пожалуйста, отредактируйте или удалите бригаду с таким номером.'));
			}
		}
		
		
		$query = "
			DECLARE
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000)

			SET @Res = :EmergencyTeam_id;

			EXEC ".$procedure."
				@EmergencyTeam_id = @Res output,
				@EmergencyTeam_Num = :EmergencyTeam_Num,
				@EmergencyTeam_CarNum = :EmergencyTeam_CarNum,
				@EmergencyTeam_CarBrand = :EmergencyTeam_CarBrand,
				@EmergencyTeam_CarModel = :EmergencyTeam_CarModel,
				@EmergencyTeam_PortRadioNum = :EmergencyTeam_PortRadioNum,
				@EmergencyTeam_GpsNum = :EmergencyTeam_GpsNum,
				@LpuBuilding_id = :LpuBuilding_id,
				@EmergencyTeam_BaseStationNum = :EmergencyTeam_BaseStationNum,
				@EmergencyTeamSpec_id = :EmergencyTeamSpec_id,
				@EmergencyTeam_HeadShift = :EmergencyTeam_HeadShift,
				@EmergencyTeam_Driver = :EmergencyTeam_Driver,
				@EmergencyTeam_Assistant1 = :EmergencyTeam_Assistant1,
				@EmergencyTeam_Assistant2 = :EmergencyTeam_Assistant2,
				@Lpu_id = :Lpu_id,

				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			SELECT @Res as EmergencyTeam_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		
		$result = $this->db->query( $query, $sqlArr );
		if ( is_object( $result ) ) {
			
			$result = $result->result('array');
			
			$EmergencyTeam_id = $result[0]['EmergencyTeam_id'];
			$this->db->query("
				UPDATE
					EmergencyTeam
				SET
					EmergencyTeam_Deleted = 2,
					pmUser_delID = :pmUser_id,
					EmergencyTeam_delDT = dbo.tzGetDate()
				WHERE
					EmergencyTeam_id != :EmergencyTeam_id
					AND EmergencyTeam_Num = :EmergencyTeam_Num
					AND Lpu_id = :Lpu_id
			", array(
				'EmergencyTeam_id'				=> $EmergencyTeam_id,
				'EmergencyTeam_Num'				=> $data['EmergencyTeam_Num'],
				'Lpu_id'						=> $data['Lpu_id'],
				'pmUser_id'						=> $data['pmUser_id'],
			));
			
			return $result;
		}
		
		return false;
	}
	

	/**
	 * @desc Список бригад для комбобокса
	 * @param type $data
	 * @return boolean
	 */
	function getEmergencyTeamCombo($data) {
		$query = "
			SELECT
				ET.EmergencyTeam_id,
				ET.EmergencyTeam_Num,
				MP.Person_Fin
			FROM
				v_EmergencyTeam  ET with (nolock)
				LEFT JOIN v_MedPersonal as MP with(nolock) ON( MP.MedPersonal_id = ET.EmergencyTeam_HeadShift )
			";
		
		$result = $this->db->query( $query);
		
		if ( is_object( $result ) ) {
			return $result->result('array');
		}
		
		return false;
	}


	/**
	 * @desc Получение оперативной обстановки по бригадам СМП
	 * @param array $data
	 * @return array or false
	 */
	function loadEmergencyTeamOperEnv( $data ){

		if ( !array_key_exists( 'Lpu_id', $data ) || !$data['Lpu_id'] ) {
			return array( array( 'Err_Msg' => 'Не указан идентификатор ЛПУ') );
		}
		$filter = '1 = 1';

		$sqlArr = array();

		$joins = '';

		$data['closeHide'] = isset($data['closeHide']) ? $data['closeHide'] : 1;

		if ( (array_key_exists( 'closeHide', $data ) && ($data['closeHide'] == 1)) && (isset($data['CmpCallCard']) && $data['CmpCallCard'] > 0) ){

			$sqlArr = array(
				//	'Lpu_id' => $data['Lpu_id'],
				'CmpCallCard' => $data['CmpCallCard']
			);
			$joins = " LEFT JOIN v_CmpCallCard as CC ON(CC.CmpCallCard_id = :CmpCallCard) ";
			$filter .= " AND (ETD.EmergencyTeamDuty_DTStart <  CC.CmpCallCard_prmDT AND (ETD.EmergencyTeamDuty_DTFinish >  CC.CmpCallCard_prmDT))";
		}

		$filter .= " AND COALESCE(ET.EmergencyTeam_isTemplate,1)=1";

		// Вышел на смену
		if( (getRegionNick() == 'buryatiya') && !empty($data[ 'session' ]) && !empty($data[ 'session' ]["CurArmType"]) && ($data[ 'session' ]["CurArmType"] != 'smpheadduty') ){
			$filter .= " AND ETD.EmergencyTeamDuty_isComesToWork=2";
		}

		$filter .= " AND ET.Lpu_id = '".$data['Lpu_id']."'";


		if(isset($data["teamTime"])){
			//$filter .= " AND ((ETD.EmergencyTeamDuty_DTStart < :teamTime) AND (ETD.EmergencyTeamDuty_DTFinish > :teamTime))";
			$filter .= " AND etd.EmergencyTeamDuty_DTStart <= CONVERT(datetime, :teamTime)
					AND etd.EmergencyTeamDuty_DTFinish >= CONVERT(datetime, :teamTime)";

			$sqlArr["teamTime"] = DateTime::createFromFormat( 'd.m.Y H:i:s', $data["teamTime"] );
			//$sqlArr["teamTime"] = DateTime::createFromFormat( 'd.m.Y H:i:s', '01.09.2016 13:23:00' );
		}

		if(isset($data["LpuBuilding_id"])){
			$filter .= " AND ET.LpuBuilding_id = :LpuBuilding_id";
			$sqlArr["LpuBuilding_id"] = $data["LpuBuilding_id"];
		}

		$query = "
				SELECT DISTINCT
					ET.EmergencyTeam_id,
					ET.EmergencyTeamSpec_id,
					ET.EmergencyTeam_Num,
					ET.EmergencyTeam_CarNum,
					ET.EmergencyTeam_CarBrand,
					ET.EmergencyTeam_CarModel,
					ET.EmergencyTeam_PortRadioNum,
					ET.EmergencyTeam_GpsNum,
					ET.EmergencyTeam_BaseStationNum,
					ET.LpuBuilding_id,
					MP.MedPersonal_id,
					MSF.MedStaffFact_id,
					--ETSpec.EmergencyTeamSpec_Name+' '+convert(varchar, ETD.EmergencyTeamDuty_comesToWorkDT, 104) as EmergencyTeamSpec_Name,
					MP.Person_Fin+'. '+ETSpec.EmergencyTeamSpec_Name as EmergencyTeamSpec_Name,
					ETSpec.EmergencyTeamSpec_Code,
					case when isnull(ET.EmergencyTeam_isOnline,1)=2 then 'online'
						else 'offline' end as EmergencyTeam_isOnline,

					ETS.EmergencyTeamStatus_Name,

					MP.Person_Fin
					-- end select
				FROM
					v_EmergencyTeam as ET with (nolock)
					LEFT JOIN v_EmergencyTeamDuty ETD with(nolock) on ET.EmergencyTeam_id = ETD.EmergencyTeam_id
					LEFT JOIN v_LpuBuilding LB ON( LB.LpuBuilding_id=ET.LpuBuilding_id )
					--LEFT JOIN v_MedService MS with (nolock) on MS.LpuBuilding_id = LB.LpuBuilding_id
					LEFT JOIN v_EmergencyTeamSpec as ETSpec with (nolock) ON(ET.EmergencyTeamSpec_id = ETSpec.EmergencyTeamSpec_id)
					LEFT JOIN v_EmergencyTeamStatus AS ETS with (nolock) ON( ETS.EmergencyTeamStatus_id=ET.EmergencyTeamStatus_id )
					LEFT JOIN v_MedPersonal as MP with (nolock) ON( MP.MedPersonal_id=ET.EmergencyTeam_HeadShift )
					LEFT JOIN v_MedStaffFact MSF on (MSF.MedPersonal_id = ET.EmergencyTeam_HeadShift)
					".$joins."
					-- end from					
				WHERE
					-- where
					".$filter."
					-- end where
				ORDER BY
					-- order by
					EmergencyTeam_Num
					-- end order by
		";
		//var_dump(getDebugSQL($query, $sqlArr)); exit;

		$result = $this->db->query( $query, $sqlArr );

		if ( is_object( $result ) ) {



			return $result->result('array');
		}



		return false;
	}
	
	/**
	 * @param array $data
	 * @return array|false список бригад для карты закрытия вызова и поточного ввода (CmpCloseCard)
	 */
	public function loadEmergencyTeamCCC($data){
		if (empty($data['Lpu_id'])) {
			return array(array('Error_Msg' => 'Не указан идентификатор ЛПУ'));
		}
		$filter = '1 = 1';
		$EmergencyID = false;
		$sqlArr = array();

		$filter .= " AND COALESCE(ET.EmergencyTeam_isTemplate,1)=1";

		// Вышел на смену
		$filter .= " AND ETD.EmergencyTeamDuty_isComesToWork=2";

		$filter .= " AND ET.Lpu_id = '".$data['Lpu_id']."'";
		$filter .= " AND MP.MedPersonal_id is not null";

		if(isset($data["AcceptTime"])){
			if($data['CmpCallCard_id']){
				// узнаем есть ли назначенная бригада и включим ее в выборку #116341
				$EmergencyID = $this->getAppointedBrigadeForTheCall($data);
			}
			if($EmergencyID){
				$filter .= " 
					AND ( 
							(
							etd.EmergencyTeamDuty_factToWorkDT <= CONVERT(datetime, :AcceptTime)
							AND (etd.EmergencyTeamDuty_factEndWorkDT is null or etd.EmergencyTeamDuty_factEndWorkDT >= CONVERT(datetime, :AcceptTime))
							)
							OR ET.EmergencyTeam_id = ".$EmergencyID."
						)";
			}else{
				$filter .= " 
					AND etd.EmergencyTeamDuty_factToWorkDT <= CONVERT(datetime, :AcceptTime)
					AND (etd.EmergencyTeamDuty_factEndWorkDT is null or etd.EmergencyTeamDuty_factEndWorkDT >= CONVERT(datetime, :AcceptTime))";
			}

			$sqlArr["AcceptTime"] = DateTime::createFromFormat( 'd.m.Y H:i:s', $data["AcceptTime"] );
		}

		$query = "
				SELECT DISTINCT
					ET.EmergencyTeam_id,
					ET.EmergencyTeamSpec_id,
					ET.EmergencyTeam_Num,
					ET.EmergencyTeam_CarNum,
					ET.EmergencyTeam_CarBrand,
					ET.EmergencyTeam_CarModel,
					ET.EmergencyTeam_PortRadioNum,
					ET.EmergencyTeam_GpsNum,
					ET.EmergencyTeam_BaseStationNum,
					ET.LpuBuilding_id,
					MP.MedPersonal_id,
					MSF.MedStaffFact_id,
					--ETSpec.EmergencyTeamSpec_Name+' '+convert(varchar, ETD.EmergencyTeamDuty_comesToWorkDT, 104) as EmergencyTeamSpec_Name,
					MP.Person_Fin+'. '+ETSpec.EmergencyTeamSpec_Name as EmergencyTeamSpec_Name,
					ETSpec.EmergencyTeamSpec_Code,
					case when isnull(ET.EmergencyTeam_isOnline,1)=2 then 'online'
						else 'offline' end as EmergencyTeam_isOnline,

					ETS.EmergencyTeamStatus_Name,
					etd.EmergencyTeamDuty_factToWorkDT,
					etd.EmergencyTeamDuty_factEndWorkDT,

					MP.Person_Fin
					-- end select
				FROM
					v_EmergencyTeam as ET with (nolock)
					LEFT JOIN v_EmergencyTeamDuty ETD with(nolock) on ET.EmergencyTeam_id = ETD.EmergencyTeam_id
					LEFT JOIN v_LpuBuilding LB ON( LB.LpuBuilding_id=ET.LpuBuilding_id )
					--LEFT JOIN v_MedService MS with (nolock) on MS.LpuBuilding_id = LB.LpuBuilding_id
					LEFT JOIN v_EmergencyTeamSpec as ETSpec with (nolock) ON(ET.EmergencyTeamSpec_id = ETSpec.EmergencyTeamSpec_id)
					LEFT JOIN v_EmergencyTeamStatus AS ETS with (nolock) ON( ETS.EmergencyTeamStatus_id=ET.EmergencyTeamStatus_id )
					LEFT JOIN v_MedPersonal as MP with (nolock) ON( MP.MedPersonal_id=ET.EmergencyTeam_HeadShift )
					/*

					outer apply (
						select top 1 MedStaffFact_id
						from v_MedStaffFact with(nolock)
						where MedPersonal_id = MP.MedPersonal_id
							and WorkData_begDate <= dbo.tzGetDate()
							and (WorkData_endDate > dbo.tzGetDate() or WorkData_endDate  is null)
						order by PostOccupationType_id asc
					) MSF
					*/
					LEFT JOIN v_MedStaffFact MSF on (MSF.MedStaffFact_id = ET.EmergencyTeam_HeadShiftWorkPlace)
					-- end from
				WHERE
					-- where
					".$filter."
					-- end where
				ORDER BY
					-- order by
					EmergencyTeam_Num
					-- end order by
		";

		$result = $this->db->query( $query, $sqlArr );

		if ( is_object( $result ) ) {

			return $result->result('array');
		}

		return false;
	}

	/**
	 * @desc Сохраняет заданную дату и время начала и окончания смены
	 * @param array $data
	 * @return bool
	 */
	public function saveEmergencyTeamDutyTime( $data ){
		
		if ( !array_key_exists( 'EmergencyTeam_id', $data )
			|| !array_key_exists( 'EmergencyTeamDuty_DateStart', $data )
			|| !array_key_exists( 'EmergencyTeamDuty_DateFinish', $data )
		) {
			return array( array( 'Error_Msg' => 'Отсутствуют необходимые данные. Возможно вы не указали ни одной смены или не выбрали бригаду.' ) );
		}

		if ( !array_key_exists( 'EmergencyTeamDuty_id', $data ) || !$data['EmergencyTeamDuty_id'] ) {
			$procedure = 'p_EmergencyTeamDuty_ins';
		} else {
			$procedure = 'p_EmergencyTeamDuty_upd';
		}

		$return = false;
		
		foreach( $data['EmergencyTeamDuty_DateStart'] as $k => &$v ) {
			if ( trim( $data['EmergencyTeamDuty_DateStart'][ $k ] ) == '' || trim( $data['EmergencyTeamDuty_DateFinish'][ $k ] == '' ) ) {
				continue;
			}
			
			$date_start = preg_replace( '#[\s]?\(.*\)[\s]*$#', '', $data['EmergencyTeamDuty_DateStart'][ $k ] );
			$date_finish = preg_replace( '#[\s]?\(.*\)[\s]*$#', '', $data['EmergencyTeamDuty_DateFinish'][ $k ] );
			$date_start = DateTime::createFromFormat( '??? M d Y H:i:s ????????', $date_start );
			$date_finish = DateTime::createFromFormat( '??? M d Y H:i:s ????????', $date_finish );
			
			$query = "
				DECLARE
					/*@Res bigint,*/
					@ErrCode int,
					@ErrMessage varchar(4000)

				EXEC ".$procedure."
					@EmergencyTeamDuty_DTStart = :EmergencyTeamDuty_DTStart,
					@EmergencyTeamDuty_DTFinish = :EmergencyTeamDuty_DTFinish,
					@EmergencyTeam_id = :EmergencyTeam_id,

					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;

				SELECT @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			
			$sqlArr = array(
				'EmergencyTeamDuty_id'			=> !array_key_exists( 'EmergencyTeamDuty_id', $data ) || !$data['EmergencyTeamDuty_id'] ? 0 : $data['EmergencyTeamDuty_id'],
				'EmergencyTeamDuty_DTStart'		=> $date_start,
				'EmergencyTeamDuty_DTFinish'	=> $date_finish,
				'EmergencyTeam_id'				=> $data['EmergencyTeam_id'],
				'pmUser_id'						=> $data['pmUser_id'],
			);

			$result = $this->db->query( $query, $sqlArr );
			
			if ( is_object( $result ) ) {
				$return = $result->result('array');
			}
		}
		
		if ( !$return ) {
			return array( array( 'Error_Msg' => 'Вы не указали ни одной смены или данные были введены не корректно.' ) );
		}

		return $return;
	}
	

	/**
	 * @desc Получение оперативной обстановки по диспетчерам СМП
	 * @param array $data
	 * @return array or false
	 */
	function loadDispatchOperEnv( $data ){
		//var_dump($data);
		//exit();
		// Получаем список диспетчеров
		$query = "
			SELECT
				-- select
				pmUser_id,
				pmUser_name,
				
				Lpu_Name
				-- end select
			FROM
				-- from
				v_pmUserCache u with (nolock)
				INNER JOIN v_Lpu as l with (nolock) ON( l.Lpu_id=u.Lpu_id )
				-- end from
			WHERE
				-- where
				pmUser_groups LIKE '%{\"name\":\"SMPCallDispath\"}%'
				AND pmUser_groups LIKE '%{\"name\":\"SMPDispatchDirections\"}%'
				AND l.Lpu_id = :Lpu_id
				-- end where
		";
		
		$sqlArr = array(
			'Lpu_id' => $data['Lpu_id'],
		);
		$result = $this->db->query( $query, $sqlArr );
		
		if ( is_object( $result ) ) {
			return $result->result('array');
		}
		
		return false;
	}

	
	/**
	 * @desc Возвращает данные указанной бригады
	 * 
	 * @param array $data
	 * @return array|boolean
	 */
	function loadEmergencyTeam( $data ){
		
		if ( !array_key_exists( 'EmergencyTeam_id', $data ) || !$data['EmergencyTeam_id'] ) {
			return false;
		}
		
		if ( !array_key_exists( 'Lpu_id', $data ) || !$data['Lpu_id'] ) {
			return array( array( 'Err_Msg' => 'Не указан идентификатор ЛПУ') );
		}
		
		$query = "
			SELECT TOP 1
				ET.EmergencyTeam_id,
				ET.EmergencyTeam_Num,
				ET.EmergencyTeam_CarNum,
				ET.EmergencyTeam_CarBrand,
				ET.EmergencyTeam_CarModel,
				ET.EmergencyTeam_PortRadioNum,
				ET.EmergencyTeam_GpsNum,
				ET.EmergencyTeam_BaseStationNum,
				ET.LpuBuilding_id,
				ET.EmergencyTeamSpec_id,
				ET.EmergencyTeam_HeadShift,
				ET.EmergencyTeam_Driver,
				ET.EmergencyTeam_Assistant1,
				ET.EmergencyTeam_Assistant2,
				ET.EmergencyTeamStatus_id,
				ET.EmergencyTeam_IsOnline,
				ET.Lpu_id
			FROM
				v_EmergencyTeam ET with (nolock)
			WHERE
				ET.EmergencyTeam_id = :EmergencyTeam_id
				AND ET.Lpu_id = :Lpu_id
		";
		
		$sqlArr = array(
			'EmergencyTeam_id' => $data['EmergencyTeam_id'],
			'Lpu_id' => $data['Lpu_id'],
		);
		
		$result = $this->db->query( $query, $sqlArr );

		if ( is_object( $result ) ) {
			return $result->result('array');
		}

		return false;
	}
	
	
	/**
	 * @desc Удаляет бригаду СМП
	 * 
	 * @param array $data
	 * @return array|false
	 */
	function deleteEmergencyTeam( $data ) {
		
		if ( !array_key_exists( 'EmergencyTeam_id', $data ) || !$data['EmergencyTeam_id'] ) {
			return false;
		}
		
		if ( !array_key_exists( 'Lpu_id', $data ) || !$data['Lpu_id'] ) {
			return array( array( 'Err_Msg' => 'Не указан идентификатор ЛПУ') );
		}
		
		$query = "
			DECLARE
				@ErrCode int,
				@ErrMessage varchar(4000);

			EXEC p_EmergencyTeam_setdel
				@EmergencyTeam_id = :EmergencyTeam_id,
				@Lpu_id = :Lpu_id,

				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			SELECT @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		
		$result = $this->db->query( $query, array(
			'EmergencyTeam_id' => $data['EmergencyTeam_id'],
			'Lpu_id' => $data['Lpu_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		if ( is_object( $result ) ) {
			return $result->result('array');
		}
		
		return array(array('Error_Msg' => 'Во время удаления бригады СМП произошла ошибка в базе данных.'));
	}
	
	
	/**
	 * @desc Изменяет статус бригады СМП
	 * @param array $data
	 * @return array|false
	 */
	public function setEmergencyTeamStatus( $data ){
		if ( !array_key_exists( 'EmergencyTeam_id', $data ) || !$data['EmergencyTeam_id']
			|| !array_key_exists( 'EmergencyTeamStatus_id', $data ) || !$data['EmergencyTeamStatus_id']
		) {
			return false;
		}
		
		$query = "
			DECLARE
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			
			SET @Res = :EmergencyTeamStatus_id;

			EXEC p_EmergencyTeam_setStatus
				@EmergencyTeam_id = :EmergencyTeam_id,
				@EmergencyTeamStatus_id = @Res,

				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			SELECT @Res as EmergencyTeamStatus_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		
		$sqlArr = array(
			'EmergencyTeam_id'				=> $data['EmergencyTeam_id'],
			'EmergencyTeamStatus_id'		=> $data['EmergencyTeamStatus_id'],
			'pmUser_id'						=> $data['pmUser_id'],
		);
		
		$result = $this->db->query( $query, $sqlArr );
		
		if ( is_object( $result ) ) {
			return $result->result('array');
		}
		
		return false;
	}
	
	
	/**
	 * @desc Получение списка смен указанной бригады для графика нарядов
	 * 
	 * @param array $data
	 * @return array|false
	 */
	public function loadEmergencyTeamDutyTimeGrid( $data ){
		if ( !array_key_exists( 'EmergencyTeam_id', $data ) || !$data['EmergencyTeam_id'] ) {
			return false;
		}

		$sqlArr = array(
			'EmergencyTeam_id' => $data['EmergencyTeam_id'],
		);
		
		$filter = '';
		
		if ( !array_key_exists( 'dateStart', $data ) || empty( $data['dateStart'] )
			|| !array_key_exists( 'dateFinish', $data ) || empty( $data['dateFinish'] )	) {
			$data['dateStart'] = date('Y.m.d',time()-7*24*60*60).' 00:00:00';
			$data['dateFinish'] = date('Y.m.d').' 00:00:00';
		}
		
		$filter .= "
			AND CAST(etd.EmergencyTeamDuty_DTStart as date) >= CONVERT(datetime, :dateStart, 20)
			AND CAST(etd.EmergencyTeamDuty_DTStart as date) <= CONVERT(datetime, :dateFinish, 20)
		";
		$sqlArr['dateStart'] = $data['dateStart'];
		$sqlArr['dateFinish'] = $data['dateFinish'];
		
		$query = "
			SELECT
				etd.EmergencyTeamDuty_id,
				etd.EmergencyTeam_id,
				CONVERT( varchar, CAST( etd.EmergencyTeamDuty_DTStart as datetime ), 120 ) as EmergencyTeamDuty_DTStart,
                CONVERT( varchar, CAST( etd.EmergencyTeamDuty_DTFinish as datetime ), 120 ) as EmergencyTeamDuty_DTFinish,
				CASE
					WHEN etd.EmergencyTeamDuty_isComesToWork=2 THEN 'Да'
					WHEN etd.EmergencyTeamDuty_isComesToWork=1 THEN 'Нет'
					ELSE ''
				END as ComesToWork
			FROM
				v_EmergencyTeamDuty etd with (nolock)
			WHERE
				etd.EmergencyTeam_id = :EmergencyTeam_id
				".$filter."
		";
		
		$result = $this->db->query( $query, $sqlArr );
		
		if ( is_object( $result ) ) {
			$arr = $result->result('array');
			return array(
				'data' => $arr,
				'totalCount' => sizeof( $arr )
			);
		}
		
		return false;
	}
	
	/**
	 * @desc Отмечает выход на смену бригады СМП по врачу
	 * 
	 * @param array $data
	 * @return array|false
	 */
	function setEmergencyTeamWorkComingMedPersonal( $data ) {
		//var_dump($data['MedPersonal_id']);
		if ( !array_key_exists( 'MedPersonal_id', $data ) || !$data['MedPersonal_id'] ) {
			return array( array( 'Err_Msg' => 'Не указан врач') );
		} 		
		
		//INNER JOIN v_MedPersonal mp with (nolock) ON( mp.MedPersonal_id=et.EmergencyTeam_HeadShift )
		
		$query = "
        	SELECT
				etd.EmergencyTeamDuty_id,
				etd.EmergencyTeam_id,
				etd.EmergencyTeamDuty_DTStart,
                etd.EmergencyTeamDuty_DTFinish,
				etd.EmergencyTeamDuty_isComesToWork
			FROM
				v_EmergencyTeamDuty etd with (nolock)
				LEFT JOIN v_EmergencyTeam et with (nolock) ON( et.EmergencyTeam_id=etd.EmergencyTeam_id )
			WHERE
				et.EmergencyTeam_HeadShift = :EmergencyTeam_HeadShift
				and ISNULL(etd.EmergencyTeamDuty_isClose,1) != '2'
				--AND (etd.EmergencyTeamDuty_DTStart <= DATEADD(hour, 1, dbo.tzGetDate()) AND (etd.EmergencyTeamDuty_DTFinish >= dbo.tzGetDate()))
				AND etd.EmergencyTeamDuty_factToWorkDT > '1900-01-01'
				AND dbo.tzGetDate() BETWEEN etd.EmergencyTeamDuty_factToWorkDT AND 
				CASE
					WHEN  etd.EmergencyTeamDuty_factEndWorkDT is null OR etd.EmergencyTeamDuty_factEndWorkDT = '1900-01-01'
						THEN '2030-01-01'
					ELSE etd.EmergencyTeamDuty_factEndWorkDT
				END
    	";
		
		$result = $this->db->query( $query, array('EmergencyTeam_HeadShift' => $data['MedPersonal_id']) );
        
		
		if ( is_object( $result ) ) {
			$result = $result->result('array');
		}
		
		if (count($result) > 1) {
			$duty_cnt = 0;
			foreach ($result as $res) {
				if ($res['EmergencyTeamDuty_isComesToWork'] == '2') $duty_cnt++;
			}
			if ($duty_cnt == 1) 
				return array( array( 'success' => true, 'Code' => 3, 'Msg' => 'Вы на смене') );
			else 
				return array( array( 'success' => true, 'Code' => 1, 'Msg' => 'Есть доступные смены') );
		}
		
		if (count($result) == 0) {
			return array( array( 'success' => true, 'Code' => 2, 'Msg' => 'На ближайший час смен нет') );
		}
		
		if (count($result) == 1 && $result[0]['EmergencyTeamDuty_isComesToWork'] == '2') {
			return array( array( 'success' => true, 'Code' => 3, 'Msg' => 'Вы на смене') );
		}
		
		if (count($result) == 1 && $result[0]['EmergencyTeamDuty_isComesToWork'] != '2') {
			return array( array( 'success' => true, 'Code' => 4, 'Msg' => 'Нужно отметиться о выходе на смену') );
		}
		return false;
	}
	
	
	/**
	 * @desc Отмечает выход на смену бригады СМП по врачу
	 * 
	 * @param array $data
	 * @return array|false
	 */
	function loadEmergencyTeamByMedPersonal( $data ) {
		
		
		$sqlArr = array(
			'EmergencyTeam_HeadShift' => $data['session']['medpersonal_id']
		);
				
		if ( !array_key_exists( 'dateStart', $data ) || empty( $data['dateStart'] )
			|| !array_key_exists( 'dateFinish', $data ) || empty( $data['dateFinish'] )	) {
			$data['dateStart'] = date('Y.m.d',time()-7*24*60*60).' 00:00:00';
			$data['dateFinish'] = date('Y.m.d').' 00:00:00';
		}
		
		
		$sqlArr['dateStart'] = $data['dateStart'];
		$sqlArr['dateFinish'] = $data['dateFinish'];
		
		$query = "
        	SELECT
				etd.EmergencyTeamDuty_id,
				etd.EmergencyTeam_id,
				CONVERT( varchar, CAST( etd.EmergencyTeamDuty_DTStart as datetime ), 120 ) as EmergencyTeamDuty_DTStart,
				CONVERT( varchar, CAST( etd.EmergencyTeamDuty_DTFinish as datetime ), 120 ) as EmergencyTeamDuty_DTFinish,      
				CONVERT( varchar, CAST( etd.EmergencyTeamDuty_DTStart as datetime ), 104)+' '+CONVERT( varchar, CAST( etd.EmergencyTeamDuty_DTStart as datetime ), 108) as EmergencyTeamDuty_DTStartVis,
				CONVERT( varchar, CAST( etd.EmergencyTeamDuty_DTFinish as datetime ), 104)+' '+CONVERT( varchar, CAST( etd.EmergencyTeamDuty_DTFinish as datetime ), 108) as EmergencyTeamDuty_DTFinishVis,
				etd.EmergencyTeamDuty_isComesToWork,				
				et.EmergencyTeam_Num,
				et.EmergencyTeam_CarNum,
				et.EmergencyTeam_CarBrand,
				et.EmergencyTeam_CarModel,
				et.EmergencyTeam_PortRadioNum,
				et.EmergencyTeam_GpsNum,
				et.EmergencyTeam_BaseStationNum,
				et.LpuBuilding_id,
				et.EmergencyTeam_HeadShift,
				ETSpec.EmergencyTeamSpec_Name,
				ETSpec.EmergencyTeamSpec_Code,
				ETS.EmergencyTeamStatus_Name,				
				CASE WHEN ISNULL(etd.EmergencyTeamDuty_isComesToWork,1) = 1 THEN 'false' ELSE 'true' END AS EmergencyTeamDuty_isComesToWork,
				CASE WHEN ISNULL(etd.EmergencyTeamDuty_isClose,1) = 1 THEN 'false' ELSE 'true' END AS EmergencyTeamDuty_isClose
			FROM
				v_EmergencyTeam et with (nolock)
				LEFT JOIN v_EmergencyTeamDuty etd with (nolock) ON( et.EmergencyTeam_id=etd.EmergencyTeam_id )
				LEFT JOIN v_EmergencyTeamStatus AS ETS with (nolock) ON( ETS.EmergencyTeamStatus_id=et.EmergencyTeamStatus_id )
				LEFT JOIN v_EmergencyTeamSpec as ETSpec with (nolock) ON(et.EmergencyTeamSpec_id = ETSpec.EmergencyTeamSpec_id)
			WHERE
				et.EmergencyTeam_HeadShift = :EmergencyTeam_HeadShift
				--and ISNULL(etd.EmergencyTeamDuty_isClose,1) != '2'
				--AND etd.EmergencyTeamDuty_DTStart >= DATEADD(hour, -24, dbo.tzGetDate())
				AND CAST(etd.EmergencyTeamDuty_DTStart as date) >= CONVERT(datetime, :dateStart, 20)
				AND CAST(etd.EmergencyTeamDuty_DTStart as date) <= CONVERT(datetime, :dateFinish, 20)
				--AND (etd.EmergencyTeamDuty_DTFinish > dbo.tzGetDate()))
    	";
		
		
		
		$result = $this->db->query( $query, $sqlArr );
		
		if ( is_object( $result ) ) {
			$arr = $result->result('array');
			return array(
				'data' => $arr,
				'totalCount' => sizeof( $arr )
			);
		}
		
	}
	
	/**
	 * @desc Отмечает выход на смену бригады СМП
	 * 
	 * @param array $data
	 * @return array|false
	 */
	function setEmergencyTeamWorkComing( $data ) {
		
		if ( !array_key_exists( 'EmergencyTeamDuty_id', $data ) || !$data['EmergencyTeamDuty_id'] ) {
			return array( array( 'Error_Msg' => 'Не указана смена бригады.' ) );
		}

		if ( !array_key_exists( 'EmergencyTeam_id', $data ) || !$data['EmergencyTeam_id'] ) {
			return array( array( 'Error_Msg' => 'Не указана бригада.' ) );
		}

		if ( !array_key_exists( 'EmergencyTeamDuty_isComesToWork', $data ) || !$data['EmergencyTeamDuty_isComesToWork'] ) {
			return array( array( 'Error_Msg' => 'Не указан флаг выхода на смену бригады.' ) );
		}
		
		$query = "
			DECLARE
				@ErrCode int,
				@ErrMessage varchar(4000);

			EXEC p_EmergencyTeamDuty_setWorkComing
				@EmergencyTeamDuty_id = :EmergencyTeamDuty_id,
				@EmergencyTeam_id = :EmergencyTeam_id,
				@EmergencyTeamDuty_isComesToWork = :EmergencyTeamDuty_isComesToWork,

				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			SELECT @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		
		$result = $this->db->query( $query, array(
			'EmergencyTeamDuty_id' => $data['EmergencyTeamDuty_id'],
			'EmergencyTeam_id' => $data['EmergencyTeam_id'],
			'EmergencyTeamDuty_isComesToWork' => $data['EmergencyTeamDuty_isComesToWork'] == 2 ? 2 : 1,
			'pmUser_id' => $data['pmUser_id']
		));

		if ( is_object( $result ) ) {			
			return $result->result('array');
		}
		
		return array(array('Error_Msg' => 'Во время попытки отметить выход на смену для бригады СМП, произошла ошибка в базе данных.'));
	}

	
	/**
	 * @desc Возращает список для справочника списка бригад СМП
	 *
	 * @param array $data
	 * @return array|false
	 */
	function loadEmergencyTeamCombo( $data ) {
		
		$where = array();
		$sqlArr = array();
		
		// Выводим только бригады состоящих в ЛПУ пользователя
		if ( !array_key_exists( 'Lpu_id', $data ) || !$data['Lpu_id'] ) {
			return array( array( 'Err_Msg' => 'Не указан идентификатор ЛПУ') );
		}
		
		$where[] = "et.Lpu_id = :Lpu_id";
		if(!empty($data['begDate']) && !empty($data['endDate'])){
			$where[] = "(  (
							 (:begDate BETWEEN etd.EmergencyTeamDuty_factToWorkDT and etd.EmergencyTeamDuty_factEndWorkDT )
							 or (:begDate > etd.EmergencyTeamDuty_factToWorkDT and etd.EmergencyTeamDuty_factEndWorkDT is null)
							 or (etd.EmergencyTeamDuty_factToWorkDT IS NULL)
							 OR  ( etd.EmergencyTeamDuty_factToWorkDT BETWEEN :begDate and :endDate )
							)
							and
							(
							 (etd.EmergencyTeamDuty_factEndWorkDT IS NULL)
							 OR  ( :endDate BETWEEN etd.EmergencyTeamDuty_factToWorkDT and etd.EmergencyTeamDuty_factEndWorkDT )
							 or ( etd.EmergencyTeamDuty_factEndWorkDT BETWEEN :begDate and :endDate )
							 )
						)";

			$sqlArr['begDate'] = $data['begDate'] . ' 00:00:00';
			$sqlArr['endDate'] = $data['endDate'] . '  23:59:59';
		}
		$where[] = 'etd.EmergencyTeamDuty_isComesToWork = 2';
		if(!empty($data['LpuBuilding_id'])){
			$where[] = "ET.LpuBuilding_id = :LpuBuilding_id";
			$sqlArr["LpuBuilding_id"] = $data["LpuBuilding_id"];
		}
		
        $query = "
        	SELECT distinct
				et.EmergencyTeam_id,
				et.LpuBuilding_id,
				et.EmergencyTeam_Num as EmergencyTeam_Code,
				LTRIM( RTRIM( mp.Person_FIO ) ) as EmergencyTeam_Name
			FROM
				v_EmergencyTeam et with (nolock)
				LEFT JOIN v_EmergencyTeamDuty ETD with(nolock) on ET.EmergencyTeam_id = ETD.EmergencyTeam_id
				INNER JOIN v_MedPersonal mp with (nolock) ON( mp.MedPersonal_id=et.EmergencyTeam_HeadShift )
			".ImplodeWherePH( $where )."
			ORDER BY
				et.EmergencyTeam_Num
    	";

		$sqlArr['Lpu_id'] = $data['Lpu_id'];

		$result = $this->db->query( $query, $sqlArr );

        if ( is_object( $result ) ) {
            return $result->result('array');
        }
	}
	
	/**
	 * @desc я не знаю что за функция, но от меня требуют ее описание
	 * @param array $data
	 * @return array or false
	 */
	function getEmergencyTeamProposalLogic($data) {
		
		$filter = '(1=1)';
		
		if ( !array_key_exists( 'Lpu_id', $data ) || !$data['Lpu_id'] ) {
			return array( array( 'Err_Msg' => 'Не указан идентификатор ЛПУ') );
		} else {
			$filter .= ' and ETPL.Lpu_id = :Lpu_id';
		}
		
		if ( !empty($data['EmergencyTeamProposalLogic_id'])) {
			$filter .= ' and ETPL.EmergencyTeamProposalLogic_id = :EmergencyTeamProposalLogic_id';
		}
		
		$query = "
        	SELECT
				ETPL.EmergencyTeamProposalLogic_id,
				ETPL.CmpReason_id,
				ETPL.Sex_id,
				CR.CmpReason_Code,
				ISNULL(S.Sex_Name,'Все') as Sex_Name,
				ISNULL(CAST(ETPL.EmergencyTeamProposalLogic_AgeFrom as varchar(10)),'') as EmergencyTeamProposalLogic_AgeFrom,
				ISNULL(CAST(ETPL.EmergencyTeamProposalLogic_AgeTo as varchar(10)),'') as EmergencyTeamProposalLogic_AgeTo,
				Codes.Codes as EmergencyTeamProposalLogic_Sequence
			FROM
				v_EmergencyTeamProposalLogic ETPL with (nolock)
				LEFT JOIN v_Sex S with (nolock) ON( ETPL.Sex_id=S.Sex_id)
				INNER JOIN v_CmpReason CR with (nolock) ON( ETPL.CmpReason_id = CR.CmpReason_id)
				OUTER APPLY (
					SELECT DISTINCT
					(
						SELECT
							ETS2.EmergencyTeamSpec_Code + ' '
						FROM 
							v_EmergencyTeamProposalLogicRule ETPLR with (nolock)
							INNER JOIN v_EmergencyTeamSpec ETS2 with (nolock) on (ETPLR.EmergencyTeamSpec_id = ETS2.EmergencyTeamSpec_id)
						WHERE 
							ETPLR.EmergencyTeamProposalLogic_id = ETPL.EmergencyTeamProposalLogic_id
						ORDER BY 
							ETPLR.EmergencyTeamProposalLogicRule_SequenceNum ASC
						FOR XML PATH('')
					) AS Codes
				) as Codes
			WHERE
				{$filter}
    	";
		
		$result = $this->db->query( $query, $data );

        if ( is_object( $result ) ) {
			$arr = $result->result('array');
			return array(
				'data' => $arr,
				'totalCount' => sizeof( $arr )
			);
		}
	}
	
	 /**
	 * @desc я не знаю что за функция, но от меня требуют ее описание
	 * @param array $data
	 * @return array or false
	 */
	function getEmergencyTeamProposalLogicRuleSpecSequence($data) {
		
		$filter = '(1=1)';
		
		if ( !isset( $data['EmergencyTeamProposalLogic_id']) || $data['EmergencyTeamProposalLogic_id']==0 ) {
			$query = "
				SELECT
					0 as EmergencyTeamProposalLogicRule_id,
					ROW_NUMBER() OVER(ORDER BY ETS.EmergencyTeamSpec_id) as EmergencyTeamProposalLogicRule_SequenceNum,
					ETS.EmergencyTeamSpec_id,
					ETS.EmergencyTeamSpec_Code,
					ETS.EmergencyTeamSpec_Name
				FROM
					v_EmergencyTeamSpec ETS with (nolock)
				";
			
			
		} else {
			$filter .= ' and ETPLR.EmergencyTeamProposalLogic_id = :EmergencyTeamProposalLogic_id';
			
			$query = "
				SELECT
					ETPLR.EmergencyTeamProposalLogicRule_id,
					ETPLR.EmergencyTeamSpec_id,
					ETPLR.EmergencyTeamProposalLogicRule_SequenceNum,
					ETS.EmergencyTeamSpec_Code,
					ETS.EmergencyTeamSpec_Name

				FROM
					v_EmergencyTeamProposalLogicRule ETPLR with (nolock)
					INNER JOIN v_EmergencyTeamSpec ETS with (nolock) ON( ETS.EmergencyTeamSpec_id = ETPLR.EmergencyTeamSpec_id)
					
				WHERE
					{$filter}
			";
			
		}
					
		$result = $this->db->query( $query, $data );

        if ( is_object( $result ) ) {
			$arr = $result->result('array');
			return array(
				'data' => $arr,
				'totalCount' => sizeof( $arr )
			);
		}
	}
	
	/**
	 * @desc я не знаю что за функция, но от меня требуют ее описание
	 * @param array $data
	 * @return array or false
	 */
	function saveEmergencyTeamProposalLogicRule($data) {
		if ( !array_key_exists( 'EmergencyTeamProposalLogic_id', $data ) || !$data['EmergencyTeamProposalLogic_id'] ) {
			$data['EmergencyTeamProposalLogic_id'] = 0;
			$procedure = 'p_EmergencyTeamProposalLogic_ins';
		} else {
			$procedure = 'p_EmergencyTeamProposalLogic_upd';
		}

		if ( !array_key_exists( 'CmpReason_id', $data ) || !$data['CmpReason_id'] ) {
			return array( array( 'Error_Msg' => 'Не указан повод.' ) );
		}

		if (!isset($data['EmergencyTeamProposalLogic_AgeFrom'])&&(!isset($data['EmergencyTeamProposalLogic_AgeTo']))) {
			return array( array( 'Error_Msg' => 'Хотя бы одно из полей (Возраст С, Возраст ПО) должно быть заполнено.' ) );
		}
		
		if (isset($data['EmergencyTeamProposalLogic_AgeFrom'])&&isset($data['EmergencyTeamProposalLogic_AgeTo'])&&($data['EmergencyTeamProposalLogic_AgeFrom']>$data['EmergencyTeamProposalLogic_AgeTo'])) {
				return array( array( 'Error_Msg' => 'Значение поля "Возраст С" не может быть больше значения поля "Возраст ПО"' ) );
			}
		
		
		//Проверка непротиворечивости правила
		$consistencyQueryParams=array(
			'CmpReason_id'=>$data['CmpReason_id'],
			'Sex_id'=>(isset($data['Sex_id']))?$data['Sex_id']:null,
			'EmergencyTeamProposalLogic_AgeFrom'=>(isset($data['EmergencyTeamProposalLogic_AgeFrom']))?$data['EmergencyTeamProposalLogic_AgeFrom']:120,
			'EmergencyTeamProposalLogic_AgeTo'=>(isset($data['EmergencyTeamProposalLogic_AgeTo']))?$data['EmergencyTeamProposalLogic_AgeTo']:0,
			'Lpu_id'=>$data['Lpu_id']
		);

		$consistencyCheckQuery = "
			DECLARE 
				@Sex_id bigint,
				@EmergencyTeamProposalLogic_AgeFrom bigint,
				@EmergencyTeamProposalLogic_AgeTo bigint;

			set @Sex_id = :Sex_id;
			set	@EmergencyTeamProposalLogic_AgeFrom = :EmergencyTeamProposalLogic_AgeFrom;
			set	@EmergencyTeamProposalLogic_AgeTo = :EmergencyTeamProposalLogic_AgeTo;

			SELECT
				ETPL.EmergencyTeamProposalLogic_id
			FROM
				v_EmergencyTeamProposalLogic ETPL with (nolock)
			WHERE
				NOT (
					(ISNULL(@EmergencyTeamProposalLogic_AgeFrom,0) > ISNULL(ETPL.EmergencyTeamProposalLogic_AgeTo,120)) 
					OR 
					(ISNULL(@EmergencyTeamProposalLogic_AgeTo,120) < ISNULL(ETPL.EmergencyTeamProposalLogic_AgeFrom,0))
				)
				AND ETPL.CmpReason_id = :CmpReason_id
				AND ( (ISNULL(ETPL.Sex_id,0) = 0) OR (ISNULL(@Sex_id,0) = 0) OR (ISNULL(ETPL.Sex_id,0) = ISNULL(@Sex_id,0)))
				AND ETPL.Lpu_id = :Lpu_id
		";
		
		$resultConsistencyCheckQuery = $this->db->query( $consistencyCheckQuery,$consistencyQueryParams);
		
		if (is_object($resultConsistencyCheckQuery))
		{
			$resultConsistencyCheckQueryArray = $resultConsistencyCheckQuery->result('array');
			if (count($resultConsistencyCheckQueryArray)>0) {
				return array(array('Error_Msg' => 'Введенное правило противоречит одному из существующих правил с соответствующим поводом вызова.'));
			}
		}
		else
		{
			return array(array('Error_Msg' => 'Введенное правило противоречит одному из существующих правил с соответствующим поводом вызова.'));
		}
				
		
		$query = "
			DECLARE
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);			
				
			SET @Res = :EmergencyTeamProposalLogic_id;
			
			EXEC {$procedure}
				@EmergencyTeamProposalLogic_id = @Res output,
				@CmpReason_id = :CmpReason_id,
				@Sex_id = :Sex_id,
				@Lpu_id = :Lpu_id,
				@EmergencyTeamProposalLogic_AgeFrom = :EmergencyTeamProposalLogic_AgeFrom,
				@EmergencyTeamProposalLogic_AgeTo = :EmergencyTeamProposalLogic_AgeTo,

				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			SELECT @Res as EmergencyTeamProposalLogic_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		
		$result = $this->db->query( $query, array(
			'EmergencyTeamProposalLogic_id' => $data['EmergencyTeamProposalLogic_id'],
			'CmpReason_id' => $data['CmpReason_id'],
			'Sex_id' => isset($data['Sex_id'])?$data['Sex_id']:null,
			'Lpu_id' => $data['Lpu_id'],
			'EmergencyTeamProposalLogic_AgeFrom' => isset($data['EmergencyTeamProposalLogic_AgeFrom'])?$data['EmergencyTeamProposalLogic_AgeFrom']:null,
			'EmergencyTeamProposalLogic_AgeTo' => isset($data['EmergencyTeamProposalLogic_AgeTo'])?$data['EmergencyTeamProposalLogic_AgeTo']:null,
			
			'pmUser_id' => $data['pmUser_id']
		));

		if ( is_object( $result ) ) {
			return $result->result('array');
		}
		
		return array(array('Error_Msg' => 'Во время попытки отметить выход на смену для бригады СМП, произошла ошибка в базе данных.'));
	}
	
	/**
	 * @desc я не знаю что за функция, но от меня требуют ее описание
	 * @param array $data
	 * @return array or false
	 */
	function saveEmergencyTeamProposalLogicRuleSequence($data) {
		if ( !array_key_exists( 'EmergencyTeamProposalLogic_id', $data ) || !$data['EmergencyTeamProposalLogic_id'] ) {
			return array( array( 'Error_Msg' => 'Не указан идентификатор правила.' ) );
		}

		if ( !array_key_exists( 'EmergencyTeamSpec_id', $data ) || !$data['EmergencyTeamSpec_id'] ) {
			return array( array( 'Error_Msg' => 'Не указан идентификатор профиля бригады.' ) );
		}

		if ( !array_key_exists( 'EmergencyTeamProposalLogicRule_SequenceNum', $data ) ) {
			
			return array( array( 'Error_Msg' => 'Не указан порядок профиля' ) );
		}
				
		if ( isset($data['EmergencyTeamProposalLogicRule_id'])&&$data['EmergencyTeamProposalLogicRule_id']!=0) {
			$procedure = 'p_EmergencyTeamProposalLogicRule_upd';
		} else {
			$data['EmergencyTeamProposalLogicRule_id']=0;
			$procedure = 'p_EmergencyTeamProposalLogicRule_ins';
		}
		
		$query = "
			DECLARE
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);			
			SET @Res = :EmergencyTeamProposalLogicRule_id;
			
			EXEC {$procedure}
				@EmergencyTeamProposalLogicRule_id = @Res output,
				@EmergencyTeamProposalLogic_id = :EmergencyTeamProposalLogic_id,
				@EmergencyTeamSpec_id = :EmergencyTeamSpec_id,
				@EmergencyTeamProposalLogicRule_SequenceNum = :EmergencyTeamProposalLogicRule_SequenceNum,
				
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			SELECT @Res as EmergencyTeamProposalLogicRule_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
			
		$result = $this->db->query( $query, array(
			'EmergencyTeamProposalLogicRule_id' => $data['EmergencyTeamProposalLogicRule_id'],
			'EmergencyTeamProposalLogic_id' => $data['EmergencyTeamProposalLogic_id'],
			'EmergencyTeamSpec_id' =>$data['EmergencyTeamSpec_id'],
			'EmergencyTeamProposalLogicRule_SequenceNum' => $data['EmergencyTeamProposalLogicRule_SequenceNum'],
			
			'pmUser_id' => $data['pmUser_id']
		));
			
		if ( is_object( $result ) ) {
			return $result->result('array');
		}
		
		return array(array('Error_Msg' => 'Во время попытки отметить выход на смену для бригады СМП, произошла ошибка в базе данных.'));
	}
	
	
	/**
	 * @desc я не знаю что за функция, но от меня требуют ее описание
	 * @param array $data
	 * @return array or false
	 */
	function deleteEmergencyTeamProposalLogicRule($data) {
		if ( !array_key_exists( 'EmergencyTeamProposalLogic_id', $data ) || !$data['EmergencyTeamProposalLogic_id'] ) {
			return array( array( 'Error_Msg' => 'Не указан идентификатор правила.' ) );
		}		
		//Дабы ускорить процесс удаления используем цикл
		$queryDeletingSequence = "
				DECLARE
					@EmergencyTeamProposalLogic_id bigint,
					@EmergencyTeamProposalLogicRule_id bigint,
					@ErrCode int,
					@ErrMessage varchar(4000),
					@countBlock int;

				SET @countBlock=0;
				SET @EmergencyTeamProposalLogic_id = :EmergencyTeamProposalLogic_id;
				
				IF (ISNULL(@EmergencyTeamProposalLogic_id,0)!=0)
				BEGIN
					WHILE ((
						SELECT 
							COUNT(ETPLR.EmergencyTeamProposalLogicRule_id) 
						FROM
							v_EmergencyTeamProposalLogicRule ETPLR with (nolock)
						WHERE
							ETPLR.EmergencyTeamProposalLogic_id = @EmergencyTeamProposalLogic_id
						) > 0)
					BEGIN

					SET @countBlock = @countBlock+1;

					SELECT DISTINCT
						@EmergencyTeamProposalLogicRule_id = ETPLR.EmergencyTeamProposalLogicRule_id
					FROM
						v_EmergencyTeamProposalLogicRule ETPLR with (nolock)
					WHERE
						ETPLR.EmergencyTeamProposalLogic_id = @EmergencyTeamProposalLogic_id

					EXEC p_EmergencyTeamProposalLogicRule_del
						@EmergencyTeamProposalLogicRule_id = @EmergencyTeamProposalLogicRule_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
					 

					IF (((SELECT @countBlock) > 50)OR(@ErrMessage != ''))--На всякий случай от зацикливания
						BREAK
					ELSE
						CONTINUE
					END

					select @ErrCode as Error_Code, @ErrMessage as Error_Msg
				END
				ELSE
					select @ErrCode as Error_Code, 'Не задан идентификатор правила' as Error_Msg
			


			";
		
		$resultDeletingSequence = $this->db->query($queryDeletingSequence, array(
			'EmergencyTeamProposalLogic_id' => $data['EmergencyTeamProposalLogic_id']
			)
		);
		
		if ( is_object($resultDeletingSequence) ) {
			$resultDeletingSequenceArray = $resultDeletingSequence->result('array');
			if ($resultDeletingSequenceArray[0]['Error_Msg']!='') {
				return $resultDeletingSequenceArray;
			}
		} else {
			return false;
		}
		
		
		$query = "
			DECLARE
				@ErrCode int,
				@ErrMessage varchar(4000);
			EXEC p_EmergencyTeamProposalLogic_del
				@EmergencyTeamProposalLogic_id = :EmergencyTeamProposalLogic_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$result = $this->db->query($query, array(
			'EmergencyTeamProposalLogic_id' => $data['EmergencyTeamProposalLogic_id']
			)
		);
		
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 * @desc я не знаю что за функция, но от меня требуют ее описание
	 * @param array $data
	 * @return array or false
	 */
	function deleteEmergencyTeamProposalLogicRuleSequence($data) {
		if ( !array_key_exists( 'EmergencyTeamProposalLogic_id', $data ) || !$data['EmergencyTeamProposalLogic_id'] ) {
			return array( array( 'Error_Msg' => 'Не указан идентификатор правила.' ) );
		}
		
		$query = "
			SELECT
				ETPLR.EmergencyTeamProposalLogicRule_id
			FROM
				v_EmergencyTeamProposalLogicRule ETPLR with (nolock)
			WHERE
				ETPLR.EmergencyTeamProposalLogic_id = :EmergencyTeamProposalLogic_id
		";
	}

	/**
	 * Получение назанченной бригады на вызов
	 */
	function getAppointedBrigadeForTheCall($data) {
		if ( empty($data['CmpCallCard_id']) ) {
			return false;
		}

		$queryParams = array('CmpCallCard_id' => $data['CmpCallCard_id']);

		$query = "
			select top 1 EmergencyTeam_id
			from v_CmpCallCard with (nolock)
			where CmpCallCard_id = :CmpCallCard_id
		";

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$res = $result->result('array');
			if( isset($res[0]['EmergencyTeam_id']) ){
				return $res[0]['EmergencyTeam_id'];
			}			
		}

		return false;
	}
	
	/**
	 * Возвращает Вид должности старшего бригады
	 */
	public function getEmergencyTeamPostKind($data){
		if(empty($data['EmergencyTeam_id'])) return false;
		$query = "
			SELECT top 1
				et.EmergencyTeam_id,
				et.LpuBuilding_id,
				et.EmergencyTeam_Num as EmergencyTeam_Code,
				et.EmergencyTeam_HeadShift,
				LTRIM( RTRIM( mp.Person_FIO ) ) as EmergencyTeam_HeadShift_Name,
				PostKind.code as PostKindHeadShift_Code,
				PostKind.name as PostKindHeadShift_Name,
				et.EmergencyTeam_HeadShift2,
				et.EmergencyTeam_Assistant1,
				et.EmergencyTeam_Assistant2,
				et.EmergencyTeam_Driver
			FROM
				v_EmergencyTeam et with (nolock)
				INNER JOIN v_MedPersonal mp with (nolock) ON( mp.MedPersonal_id=et.EmergencyTeam_HeadShift)
				left join persis.v_Post Post with(nolock) on Post.id = MP.Dolgnost_id
				left join persis.v_PostKind PostKind with(nolock) on PostKind.id = Post.PostKind_id
			WHERE 
				et.EmergencyTeam_id = :EmergencyTeam_id
		";
		
		$result = $this->getFirstRowFromQuery($query, $data);
		if (is_array($result) && count($result) > 0 ) {
			return array($result);
		}else{
			return false;
		}
	}

}
