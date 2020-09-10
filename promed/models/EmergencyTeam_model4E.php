<?php

/**
 * Class EmergencyTeam_Model4E
 * @property Wialon_Model wmodel
 */
class EmergencyTeam_model4E extends swModel {
	
	
	/**
	 * Сохранение бригады СМП
	 * @param array $data Данные ProcessInputData
	 * @return array or false
	 */
	public function saveEmergencyTeam( $data, $update_duty_time=true ){
		if ( !array_key_exists( 'Lpu_id', $data ) || !$data['Lpu_id'] ) {
			return array( array( 'Err_Msg' => 'Не указан идентификатор ЛПУ') );
		}

		$is_pg = $this->db->dbdriver == 'postgre' ? true : false;
		
		//Каждый шаблон должен обладать уникальным именем
		if( 
			(!empty($data['EmergencyTeam_isTemplate']) && $data['EmergencyTeam_isTemplate']==2) && 
				!empty($data['EmergencyTeam_TemplateName'])
		){
			
			$checkEmergencyTeamUniqueTemplateNameWhere = array();
			$checkEmergencyTeamUniqueTemplateNameWhere[] = 'EmergencyTeam_TemplateName = :EmergencyTeam_TemplateName';
			
			if($data['EmergencyTeam_id'])
				$checkEmergencyTeamUniqueTemplateNameWhere[] = 'EmergencyTeam_id != :EmergencyTeam_id';

			$checkEmergencyTeamUniqueTemplateNameWhere[] = $this->getNestedLpuBuildingsForRequests($data);
			
			$checkEmergencyTeamUniqueTemplateName = "
				select EmergencyTeam_id 
				from v_EmergencyTeam ET with (nolock) 
				".ImplodeWherePH( $checkEmergencyTeamUniqueTemplateNameWhere )."
			";
			
			$checkEmergencyTeamUniqueTemplateNameParams = array(
				'EmergencyTeam_TemplateName' => $data['EmergencyTeam_TemplateName'],
				'EmergencyTeam_id' => empty($data['EmergencyTeam_id'])? null : $data['EmergencyTeam_id']
			);
			
			//var_dump(getDebugSQL($checkEmergencyTeamUniqueTemplateName, $checkEmergencyTeamUniqueTemplateNameParams)); exit;
			
			$res_checkEmergencyTeamUniqueTemplateName = $this->queryResult($checkEmergencyTeamUniqueTemplateName, $checkEmergencyTeamUniqueTemplateNameParams);
			
			
			if (!empty($res_checkEmergencyTeamUniqueTemplateName[0]['EmergencyTeam_id'])) {
				return $this->createError(1, 'Имя шаблона должно быть уникальным');
			}
		}
		
		if(empty($data['LpuBuilding_id'])){
			$this->load->model('CmpCallCard_model4E', 'CmpCallCard_model4E');
			$lpuBuilding = $this->CmpCallCard_model4E->getLpuBuildingBySessionData($data);
			if (empty($lpuBuilding[0])){
				return $this->createError(null, 'Не определена подстанция');
			}
			else{
				$data['LpuBuilding_id'] = $lpuBuilding[0]["LpuBuilding_id"];
			}
		}
		
		//
		// 1. Сохраняем бригаду
		//
		$sqlArr = array(
			'EmergencyTeam_Num'				=> $data['EmergencyTeam_Num'],
			'EmergencyTeam_CarNum'			=> $data['EmergencyTeam_CarNum'],
			'EmergencyTeam_CarBrand'		=> $data['EmergencyTeam_CarBrand'],
			'EmergencyTeam_CarModel'		=> (!empty($data['EmergencyTeam_CarModel']))?$data['EmergencyTeam_CarModel']:null,
			'EmergencyTeam_PortRadioNum'	=> (!empty($data['EmergencyTeam_PortRadioNum']))?$data['EmergencyTeam_PortRadioNum']:null,
			'EmergencyTeam_GpsNum'			=> (!empty($data['EmergencyTeam_GpsNum']))?$data['EmergencyTeam_GpsNum']:null,
			'LpuBuilding_id'				=> $data['LpuBuilding_id'],
			'EmergencyTeamSpec_id'			=> (!empty($data['EmergencyTeamSpec_id']))?$data['EmergencyTeamSpec_id']:null,
			'EmergencyTeam_HeadShift'		=> (!empty($data['EmergencyTeam_HeadShift']))?$data['EmergencyTeam_HeadShift']:null,
			'EmergencyTeam_HeadShiftWorkPlace'		=> (!empty($data['EmergencyTeam_HeadShiftWorkPlace']))?$data['EmergencyTeam_HeadShiftWorkPlace']:null,
			'EmergencyTeam_HeadShift2'		=> (!empty($data['EmergencyTeam_HeadShift2']))?$data['EmergencyTeam_HeadShift2']:null,
			'EmergencyTeam_HeadShift2WorkPlace'		=> (!empty($data['EmergencyTeam_HeadShift2WorkPlace']))?$data['EmergencyTeam_HeadShift2WorkPlace']:null,
			'EmergencyTeam_Driver'			=> (!empty($data['EmergencyTeam_Driver']))?$data['EmergencyTeam_Driver']:null,
			'EmergencyTeam_DriverWorkPlace'			=> (!empty($data['EmergencyTeam_DriverWorkPlace']))?$data['EmergencyTeam_DriverWorkPlace']:null,
			'EmergencyTeam_Driver2'			=> (!empty($data['EmergencyTeam_Driver2']))?$data['EmergencyTeam_Driver2']:null,
			'EmergencyTeam_Assistant1'		=> (!empty($data['EmergencyTeam_Assistant1']))?$data['EmergencyTeam_Assistant1']:null,
			'EmergencyTeam_Assistant1WorkPlace'		=> (!empty($data['EmergencyTeam_Assistant1WorkPlace']))?$data['EmergencyTeam_Assistant1WorkPlace']:null,
			'EmergencyTeam_Assistant2'		=> (!empty($data['EmergencyTeam_Assistant2']))?$data['EmergencyTeam_Assistant2']:null,
			'Lpu_id'						=> $data['Lpu_id'],
			'CMPTabletPC_id'				=> (!empty($data['CMPTabletPC_id'])) ? $data['CMPTabletPC_id'] : null,
			'MedProductCard_id'				=> (!empty($data['MedProductCard_id']))?$data['MedProductCard_id']:null,		
			'pmUser_id'						=> $data['pmUser_id'],
			'EmergencyTeam_DutyTime'		=> round( $data['EmergencyTeam_DutyTime'] ),
			'EmergencyTeam_isTemplate'		=> (!empty($data['EmergencyTeam_isTemplate'])) ? $data['EmergencyTeam_isTemplate'] : null,			
			'EmergencyTeam_TemplateName'	=> (!empty($data['EmergencyTeam_TemplateName'])) ? $data['EmergencyTeam_TemplateName'] : null,
			'EmergencyTeam_Phone' => (!empty($data['EmergencyTeam_Phone'])) ? $data['EmergencyTeam_Phone'] : null
		);

		$sqlArr['EmergencyTeamStatus_id'] = isset( $data['EmergencyTeamStatus_id'] ) && $data['EmergencyTeamStatus_id'] ? $data['EmergencyTeamStatus_id'] : null;
		if (empty($sqlArr['EmergencyTeamStatus_id']) && !empty($data['EmergencyTeam_id'])) {
			if ( $is_pg ) {
				$queryStatus = "
					SELECT
						ET.\"EmergencyTeamStatus_id\"
					FROM
						dbo.\"EmergencyTeam\" ET
					WHERE
						ET.\"EmergencyTeam_id\" = :EmergencyTeam_id
				";
			} else {
				$queryStatus = "
					select EmergencyTeamStatus_id from EmergencyTeam with (nolock) where EmergencyTeam_id = :EmergencyTeam_id
				";
			}

			$resp_ts = $this->queryResult($queryStatus, array(
				'EmergencyTeam_id' => $data['EmergencyTeam_id']
			));

			if (!empty($resp_ts[0]['EmergencyTeamStatus_id'])) {
				$sqlArr['EmergencyTeamStatus_id'] = $resp_ts[0]['EmergencyTeamStatus_id'];
			}
		}

		if ( !array_key_exists( 'EmergencyTeam_id', $data ) || !$data['EmergencyTeam_id'] ) {
			$procedure = 'p_EmergencyTeam_ins';
			$sqlArr['EmergencyTeam_id'] = null;
		} else {
			$procedure = 'p_EmergencyTeam_upd';
			$sqlArr['EmergencyTeam_id'] = $data['EmergencyTeam_id'];
		}

		$sql = "
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
				@CMPTabletPC_id = :CMPTabletPC_id,
				@EmergencyTeam_Phone = :EmergencyTeam_Phone,
				@EmergencyTeamSpec_id = :EmergencyTeamSpec_id,
				@EmergencyTeam_HeadShift = :EmergencyTeam_HeadShift,
				@EmergencyTeam_HeadShiftWorkPlace = :EmergencyTeam_HeadShiftWorkPlace,
				@EmergencyTeam_HeadShift2 = :EmergencyTeam_HeadShift2,
				@EmergencyTeam_HeadShift2WorkPlace = :EmergencyTeam_HeadShift2WorkPlace,
				@EmergencyTeam_Driver = :EmergencyTeam_Driver,
				@EmergencyTeam_DriverWorkPlace = :EmergencyTeam_DriverWorkPlace,
				@EmergencyTeam_Driver2 = :EmergencyTeam_Driver2,
				@EmergencyTeam_Assistant1 = :EmergencyTeam_Assistant1,
				@EmergencyTeam_Assistant1WorkPlace = :EmergencyTeam_Assistant1WorkPlace,
				@EmergencyTeam_Assistant2 = :EmergencyTeam_Assistant2,
				@EmergencyTeam_isTemplate = :EmergencyTeam_isTemplate,
				@EmergencyTeam_TemplateName = :EmergencyTeam_TemplateName,
				@EmergencyTeam_DutyTime = :EmergencyTeam_DutyTime,
				@MedProductCard_id = :MedProductCard_id,
				@EmergencyTeamStatus_id = :EmergencyTeamStatus_id,

				@Lpu_id = :Lpu_id,

				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			SELECT @Res as EmergencyTeam_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		

		$this->beginTransaction();
		$result = $this->queryResult( $sql, $sqlArr );
		
		if (!$this->isSuccessful($result) || empty($result[ 0 ][ 'EmergencyTeam_id' ]))  {
			$this->rollbackTransaction();
			return $result;
		}
		
		
		//
		// 2. Сохраняем связь с геосервисом
		//
		
		if (!empty($data['GeoserviceTransport_id'])) {
			
			$save_geoservice_rel = $this->_saveEmergencyTeamGeoserviceTransportRel(array_merge($data,$result[0]));
			
			if (!$this->isSuccessful($save_geoservice_rel)) {
				$this->rollbackTransaction();
				return $save_geoservice_rel;
			}

		}else{

			if(getRegionNick() != 'ufa'){

				$this->load->model( 'Wialon_model', 'wmodel' );

				$this->wmodel->deleteMergeEmergencyTeam(array(
					'EmergencyTeam_id' => $data[ 'EmergencyTeam_id' ],
					'pmUser_id' => $data[ 'pmUser_id' ],
				));
			}
		}
		
		//
		// 3. Сохраняем смену
		//
		
		if ( !$update_duty_time || (empty( $data[ 'EmergencyTeamDuty_DTStart' ] ) || empty( $data[ 'EmergencyTeamDuty_DTFinish' ]) ) ) {
			$this->commitTransaction();
			return $result;
		}
		
		$exist_dt_query_result = $this->_getEmergencyTeamDutyIdByEmergencyTeamId($data);
		
		
		$save_duty_result = $this->editEmergencyTeamDutyTime( array(
			'EmergencyTeamDuty_id' => ( (empty( $exist_dt_query_result[ 0 ][ 'EmergencyTeamDuty_id' ] ) ) ? null : $exist_dt_query_result[ 0 ][ 'EmergencyTeamDuty_id' ]),
			'EmergencyTeamDuty_DateStart' => $data[ 'EmergencyTeamDuty_DTStart' ],
			'EmergencyTeamDuty_DateFinish' => $data[ 'EmergencyTeamDuty_DTFinish' ],
			'EmergencyTeam_id' => $result[ 0 ][ 'EmergencyTeam_id' ],
			'pmUser_id' => $data[ 'pmUser_id' ]
		) );
		
		if (!$this->isSuccessful($save_duty_result)) {
			$this->rollbackTransaction();
			return $save_duty_result;
		}
		
		if (!empty( $save_duty_result[ 0 ][ 'EmergencyTeamDuty_id' ])) {
			$result[ 0 ][ 'EmergencyTeamDuty_id' ] = $save_duty_result[ 0 ][ 'EmergencyTeamDuty_id' ];
		}
		
		
		
		$this->commitTransaction();
		return $result;
	}
	
	/**
	* Получаем список EmergencyTeam_TemplateName
	*/
	public function getEmergencyTeamTemplatesNames($data){
		
		$where[] = "ISNULL(ET.EmergencyTeam_isTemplate,1) = 2";
		$where[] = $this->getNestedLpuBuildingsForRequests($data);
			
		$query = "
			select distinct ET.EmergencyTeam_TemplateName
			from v_EmergencyTeam ET with (nolock) 
			".ImplodeWherePH( $where )."
			
		";
		$result = $this->db->query( $query );
		
		if ( is_object( $result ) ) {
			return $result->result('array');
		}
		
		return false;
		
	}
	
	/**
	 * Сохранения связи наряда и транспорта геосервиса
	 * @param type $data
	 * @return type
	 */
	protected function _saveEmergencyTeamGeoserviceTransportRel($data)
	{
		
		$region = getRegionNick();
		
		$result = array();
		
		if ( $region == 'ufa') {
			
			$this->load->model( 'TNC_model', 'tncmodel' );
				
			$result = $this->tncmodel->mergeEmergencyTeam(array(
				'EmergencyTeam_id' => $data[ 'EmergencyTeam_id' ],
				'TNCTransport_id' => $data[ 'GeoserviceTransport_id' ],
				'pmUser_id' => $data[ 'pmUser_id' ],
			));
			
		} else {

			$this->load->model( 'Wialon_model', 'wmodel' );
			
			$result = $this->wmodel->mergeEmergencyTeam(array(
				'EmergencyTeam_id' => $data[ 'EmergencyTeam_id' ],
				'WialonEmergencyTeamId' => $data[ 'GeoserviceTransport_id' ],
				'pmUser_id' => $data[ 'pmUser_id' ],
			));
			
		}
		
		return $result;
	}
	
	/**
	* Получение подчин. подстанций для запроса
	*/
	private function getNestedLpuBuildingsForRequests($data){
		// здесь мы получаем список доступных подстанций для работы из лдапа
		$user = pmAuthUser::find($_SESSION['login']);
		$settings = @unserialize($user->settings);

		if ( isset($settings['lpuBuildingsWorkAccess']) && is_array($settings['lpuBuildingsWorkAccess']) ) {
			$lpuBuildingsWorkAccess = $settings['lpuBuildingsWorkAccess'];
		}
			
		if ( !(empty( $lpuBuildingsWorkAccess)) ) {
			if($lpuBuildingsWorkAccess[0]=='')
				return $this->createError(null,'Не настроен список доступных для работы подстанций');
			// Отображаем только те вызовы, которые доступны подстанций для работы из лдапа (#85307)
			$lpuBuildingIdList = $lpuBuildingsWorkAccess;
		}
		else{
			$this->load->model('CmpCallCard_Model4E', 'CmpCallCard_Model4E');
			$smpUnitsNested = $this->CmpCallCard_Model4E->loadSmpUnitsNested($data,
				in_array($_SESSION['region']['nick'], array('ufa', 'krym', 'kz', 'perm', 'ekb', 'astra'))
			);

			if ( (empty( $smpUnitsNested)) ) {
				return $this->createError(null, 'Не определена подстанция');
			}

			$lpuBuildingIdList = $smpUnitsNested;
		}

		if (!empty($lpuBuildingIdList)) {
			return "ET.LpuBuilding_id in (" . implode(',', $lpuBuildingIdList) . ")";
		} else {
			return "1=0";
		}
	}
	
	/**
	 * Метод получения идентификатора смены по идентификатору бригады
	 * @param type $data
	 * @return type
	 */
	protected function _getEmergencyTeamDutyIdByEmergencyTeamId($data)
	{
		
		$rules = array(
			array( 'field'	=> 'EmergencyTeam_id', 'label' => 'Идентификатор подстанции', 'rules' => 'required', 'type' => 'int')
		);
		
		$queryParams = $this->_checkInputData($rules , $data, $err);
		if (!empty($err)) {
			return $err;
		}
		
		$is_pg = $this->db->dbdriver == 'postgre' ? true : false;
		
		if ( $is_pg ) {
			$queryExistDT = "
				SELECT
					ETD.\"EmergencyTeamDuty_id\"
				FROM
					dbo.\"v_EmergencyTeamDuty\" ETD
				WHERE
					ETD.\"EmergencyTeam_id\" = :EmergencyTeam_id
				LIMIT 1
			";
		} else {
			$queryExistDT = "
				SELECT TOP 1
					ETD.EmergencyTeamDuty_id
				FROM
					v_EmergencyTeamDuty ETD with (nolock)
				WHERE
					ETD.EmergencyTeam_id = :EmergencyTeam_id
			";
		}
		
		return $this->queryResult($queryExistDT,$queryParams);
	}
	
	/**
	 * @desc Сохраняет бригады
	 * @param array $data
	 * @return bool
	 */
	public function saveEmergencyTeams( $data ){

		if ( !array_key_exists( 'EmergencyTeams', $data ) ) {
			return array( array( 'Error_Msg' => 'Не удалось получить список бригад.' ) );
		}
		
		$result = array();

		foreach( $data['EmergencyTeams'] as $emergency_team ){

			//$docs = array( 'EmergencyTeam_HeadShift', 'EmergencyTeam_HeadShift2','EmergencyTeam_Assistant1','EmergencyTeam_Driver' );

			$res = $this->checkMedPersonalBusy($emergency_team);

			//при копировании наряда надо проверять активно ли рабочее место сотрудников бригады
			$isWorking = $this->checkActiveMedStaffFact($emergency_team);
			if(!$isWorking){
				return $this->createError(false, 'Рабочее место сотрудника на текущую дату неактивно');
			}

			if(!empty($res[0]) && !empty($res[0]["EmergencyTeam_id"])){

				$EmergencyTeamDuty_DTStart =  DateTime::createFromFormat( 'Y-m-d H:i:s', $res[0]["EmergencyTeamDuty_DTStart"] );
				$EmergencyTeamDuty_DTFinish =  DateTime::createFromFormat( 'Y-m-d H:i:s', $res[0]["EmergencyTeamDuty_DTFinish"] );

				$EmergencyTeamDuty_DTStart = $EmergencyTeamDuty_DTStart->format('d.m.Y H:i');
				$EmergencyTeamDuty_DTFinish = $EmergencyTeamDuty_DTFinish->format('d.m.Y H:i');

				$str_msg = 'Состав бригады имеет пересечение по составу с бригадой №'. $res[0]["EmergencyTeam_Num"]
					. '.</br>С датой работы c ' . $EmergencyTeamDuty_DTStart . ' по ' . $EmergencyTeamDuty_DTFinish
					. '.</br>Подстанция: '.$res[0]["LpuBuilding_Name"];
				return $this->createError(false, $str_msg);
			}
			if($emergency_team['MedProductCard_id']) {
				$res = $this->checkCarByDate($emergency_team);
				if (empty($res[0])) {
					$str_msg = 'Выбранный автомобиль закрыт на дату наряда. Сохранение невозможно';
					return $this->createError(false, $str_msg);
				}

				if (!empty($emergency_team['checkCarByDutyDate']) && $emergency_team['checkCarByDutyDate'] == 'true') {
					$res = $this->checkCarByDutyDate($emergency_team);
					if (!empty($res[0])) {
						$teamNums = array();

						foreach ($res as $emergencyTeamAuto) {
							$teamNums[] = ($emergencyTeamAuto['EmergencyTeam_Num']);
						};

						$str_msg = 'Автомобиль уже включен в наряд ' . implode(", ", $teamNums) . ', с которыми есть пересечение.';
						return $this->createError(1, $str_msg);
					}
				}
			}
			$save_emergency_team_result = $this->saveEmergencyTeam( array_merge( $data, $emergency_team ), false );
			if ( !isset( $save_emergency_team_result[ 0 ][ 'EmergencyTeam_id' ] ) ) {continue;}
			
			if ( $emergency_team['EmergencyTeamDuty_DTStart'] && $emergency_team['EmergencyTeamDuty_DTFinish'] ) {
				
				//Если идентификатор бригады был передан с параметрами с клиента, значит редактируем смену тоже
				$emergency_team[ 'EmergencyTeamDuty_id' ] = ($emergency_team[ 'EmergencyTeam_id' ]) ? $emergency_team[ 'EmergencyTeamDuty_id' ] : null ;

				$emergency_team['EmergencyTeam_id'] = $save_emergency_team_result[ 0 ][ 'EmergencyTeam_id' ];

				$saveEmergencyTeamDutyTimeResult = $this->saveEmergencyTeamDutyTime( array(
					'EmergencyTeamsDutyTimes' => json_encode( array( $emergency_team ) ),
					'pmUser_id' => $data[ 'pmUser_id' ]
				) );

			}
			
			$save_emergency_team_result[0]['EmergencyTeam_Num'] = $emergency_team['EmergencyTeam_Num'];
			$result[] = $save_emergency_team_result;
            
		}
		if(!empty($result[0])){

			$this->load->model('CmpCallCard_model4E', 'CmpCallCard_model4E');
			$operDpt = $this->CmpCallCard_model4E->getOperDepartament($data);
			$sql = "select top 1 *
					from v_SmpUnitParam with (nolock)
					where LpuBuilding_id = :LpuBuilding_pid
					order by SmpUnitParam_id desc";
			$operDptParams = $this->getFirstRowFromQuery( $sql, array('LpuBuilding_pid' => $operDpt["LpuBuilding_pid"]) );

			$result[0]['SmpUnitParam_IsAutoEmergDuty'] = $operDptParams['SmpUnitParam_IsAutoEmergDuty'];
			$result[0]['SmpUnitParam_IsAutoEmergDutyClose'] = $operDptParams['SmpUnitParam_IsAutoEmergDutyClose'];

		}
		return $result;
	}
	
	/**
	 * @desc Проверка на автомобиль - не просрочен ли он
	 * @param type $data
	 * @return boolean
	 */
	function checkCarByDate($data) {
		
		$filters = "MPC.MedProductCard_id = :MedProductCard_id";
		
		$filters .= " AND ((  (AD.AccountingData_setDate IS NULL) OR  ( (AD.AccountingData_setDate) <= :EmergencyTeamDuty_DTStart )  )
			AND (  (AD.AccountingData_endDate IS NULL) OR  ( (AD.AccountingData_endDate) >= :EmergencyTeamDuty_DTFinish  )  ))";
				
		$query = "
			SELECT
				MPC.MedProductCard_id,
                LB.LpuBuilding_id,
                LB.LpuBuilding_Name,
                MPC.MedProductCard_BoardNumber,
				AD.AccountingData_RegNumber,			
				MPCl.MedProductClass_Name,
				MPCl.MedProductClass_Model,
				MPT.MedProductType_Code,
                MPC.MedProductCard_Glonass as GeoserviceTransport_id,
				convert(varchar(10), AD.AccountingData_setDate, 120) as AccountingData_setDate,
                convert(varchar(10), AD.AccountingData_endDate, 120) as AccountingData_endDate
			FROM
				passport.v_MedProductCard MPC with (nolock)				
				left join passport.v_MedProductClass MPCl with (nolock) on MPCl.MedProductClass_id = MPC.MedProductClass_id
                left join passport.v_AccountingData AD with (nolock) on MPC.MedProductCard_id = AD.MedProductCard_id
                left join passport.v_MedProductType MPT with (nolock) on MPT.MedProductType_id = MPCl.MedProductType_id
				left join v_LpuBuilding LB with (nolock) on LB.LpuBuilding_id = MPC.LpuBuilding_id
			WHERE
            	".$filters."
		";

		$sqlArr = array(
			'MedProductCard_id' => $data['MedProductCard_id'],			
			'EmergencyTeamDuty_DTStart' => $data['EmergencyTeamDuty_DTStart'],			
			'EmergencyTeamDuty_DTFinish' => $data['EmergencyTeamDuty_DTFinish']
		);
		//var_dump(getDebugSQL($query, $sqlArr)); exit;
		$result = $this->db->query( $query,  $sqlArr);
		
		if ( is_object( $result ) ) {
			return $result->result('array');
		}
		
		return false;
	}

    /**
     * @desc Проверка на автомобиль - не задействован ли он на данное время
     * @param type $data
     * @return array
     */
    function checkCarByDutyDate($data) {

        $query = "
			 select
                ET.EmergencyTeam_id,
                EmergencyTeam_Num,
                CONVERT(varchar, ETD.EmergencyTeamDuty_DTStart, 120) AS EmergencyTeamDuty_DTStart,
                CONVERT(varchar, ETD.EmergencyTeamDuty_DTFinish, 120) AS EmergencyTeamDuty_DTFinish
                from v_EmergencyTeam ET with(nolock)
                LEFT JOIN v_EmergencyTeamDuty ETD with(nolock) on ET.EmergencyTeam_id = ETD.EmergencyTeam_id
                LEFT JOIN passport.v_MedProductCard MPC with(nolock) on ET.MedProductCard_id = MPC.MedProductCard_id
                WHERE MPC.MedProductCard_id = :MedProductCard_id
                and ISNULL(ET.EmergencyTeam_isTemplate,1) = 1
                AND (
                    ( CAST(ETD.EmergencyTeamDuty_DTStart as datetime) >= :EmergencyTeamDuty_DTStart AND CAST(ETD.EmergencyTeamDuty_DTStart as datetime) <= :EmergencyTeamDuty_DTStart )
                    OR (CAST(ETD.EmergencyTeamDuty_DTFinish as datetime) >= :EmergencyTeamDuty_DTStart AND CAST(ETD.EmergencyTeamDuty_DTFinish as datetime) <= :EmergencyTeamDuty_DTFinish)
                )

		";

        $sqlArr = array(
            'MedProductCard_id' => $data['MedProductCard_id'],
            'EmergencyTeamDuty_DTStart' => $data['EmergencyTeamDuty_DTStart'],
            'EmergencyTeamDuty_DTFinish' => $data['EmergencyTeamDuty_DTFinish']
        );
        //var_dump(getDebugSQL($query, $sqlArr)); exit;
        $result = $this->db->query( $query,  $sqlArr);

        if ( is_object( $result ) ) {
            return $result->result('array');
        }

        return false;
    }
	
		
	/**
	 * @desc Проверка не состоит ли врач в другой смене.
	 * @param type $data
	 * @return boolean
	 */
	function checkMedPersonalBusy($data) {
		
		$query = "
			SELECT
				ET.EmergencyTeam_id,
				ET.EmergencyTeam_Num,
				ETD.EmergencyTeamDuty_id,
				ET.LpuBuilding_id,
                LB.LpuBuilding_Name,
                CONVERT( varchar, ETD.EmergencyTeamDuty_DTStart, 120 ) as EmergencyTeamDuty_DTStart,
				CONVERT( varchar,ETD.EmergencyTeamDuty_DTFinish, 120 ) as EmergencyTeamDuty_DTFinish
			FROM
				v_EmergencyTeamDuty ETD with (nolock)
				LEFT JOIN v_EmergencyTeam as ET with(nolock) ON( ET.EmergencyTeam_id = ETD.EmergencyTeam_id )
				LEFT JOIN v_LpuBuilding LB on LB.LpuBuilding_id =  ET.LpuBuilding_id
			WHERE 
				(
					ET.EmergencyTeam_id != :EmergencyTeam_id AND
					(
						( :EmergencyTeam_Assistant1 in ( ET.EmergencyTeam_Assistant1, ET.EmergencyTeam_HeadShift, ET.EmergencyTeam_HeadShift2, ET.EmergencyTeam_Driver) )
						OR ( :EmergencyTeam_HeadShift in ( ET.EmergencyTeam_Assistant1, ET.EmergencyTeam_HeadShift, ET.EmergencyTeam_HeadShift2, ET.EmergencyTeam_Driver))
						OR ( :EmergencyTeam_HeadShift2 in ( ET.EmergencyTeam_Assistant1, ET.EmergencyTeam_HeadShift, ET.EmergencyTeam_HeadShift2, ET.EmergencyTeam_Driver))
						OR ( :EmergencyTeam_Driver in ( ET.EmergencyTeam_Assistant1, ET.EmergencyTeam_HeadShift, ET.EmergencyTeam_HeadShift2, ET.EmergencyTeam_Driver))
					)
				)			
				AND EmergencyTeamDuty_DTStart < :EmergencyTeamDuty_DTFinish
				AND :EmergencyTeamDuty_DTStart < EmergencyTeamDuty_DTFinish
			";

		$sqlArr = array(
			'EmergencyTeam_id' => ($data['EmergencyTeam_id']) ? $data['EmergencyTeam_id'] : '',
			'EmergencyTeam_Assistant1' => !empty($data['EmergencyTeam_Assistant1'])?$data['EmergencyTeam_Assistant1']:null,
			'EmergencyTeam_HeadShift' =>!empty($data['EmergencyTeam_HeadShift'])?$data['EmergencyTeam_HeadShift']:null,
			'EmergencyTeam_HeadShift2' => !empty($data['EmergencyTeam_HeadShift2'])?$data['EmergencyTeam_HeadShift2']:null,
			'EmergencyTeam_Driver' => !empty($data['EmergencyTeam_Driver'])?$data['EmergencyTeam_Driver']:null,
			'EmergencyTeamDuty_DTStart' => $data['EmergencyTeamDuty_DTStart'],			
			'EmergencyTeamDuty_DTFinish' => $data['EmergencyTeamDuty_DTFinish']
		);
		//var_dump(getDebugSQL($query,  $sqlArr)); exit;
		$result = $this->db->query( $query,  $sqlArr);
		
		if ( is_object( $result ) ) {
			return $result->result('array');
		}
		
		return false;
	}


	/**
	 * @desc Проверка на активное место работы сотрудника бригады
	 * @param type $data
	 * @return boolean
	 */
	function checkActiveMedStaffFact($data) {
		$dataArr = array(
			'EmergencyTeam_HeadShiftWorkPlace'		=> (!empty($data['EmergencyTeam_HeadShiftWorkPlace']))?$data['EmergencyTeam_HeadShiftWorkPlace']:null,
			'EmergencyTeam_HeadShift2WorkPlace'		=> (!empty($data['EmergencyTeam_HeadShift2WorkPlace']))?$data['EmergencyTeam_HeadShift2WorkPlace']:null,
			'EmergencyTeam_DriverWorkPlace'			=> (!empty($data['EmergencyTeam_DriverWorkPlace']))?$data['EmergencyTeam_DriverWorkPlace']:null,
			'EmergencyTeam_Assistant1WorkPlace'		=> (!empty($data['EmergencyTeam_Assistant1WorkPlace']))?$data['EmergencyTeam_Assistant1WorkPlace']:null,
		);

		foreach( $dataArr as $MedStaffFact_id ){
			if($MedStaffFact_id){
				$query = "
					select top 1
						MedStaffFact_id
					from v_MedStaffFact with(nolock)
					where MedStaffFact_id = {$MedStaffFact_id}
						and WorkData_begDate <= dbo.tzGetDate()
						and (WorkData_endDate > dbo.tzGetDate() or WorkData_endDate  is null)
				";


				$result = $this->db->query( $query );

				if ( is_object( $result ) ) {

					$result = $result->result('array');

					if(!count($result)){
						return false;
					}
				}
			}
		}

		return true;
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
	 * Метод получения полей и объектов БД для идентификаторов транспортных средств в сторонних сервисах
	 * @param array $data
	 * @return type
	 */
	protected function _defineGeoserviceTransportRelQueryParams($data = array()) {
		
		$region = getRegionNick();
		
		if (isset($_GET['dbg']) && $_GET['dbg'] == '1') {
			var_dump($region);
		}


		if ( $region == 'ufa' ) {
			$result = array(
				'GeoserviceTransport_id_field'=>"TNCTransport_id",
				// 'GeoserviceTransport_name_field'=>"TNCTransport_Name",
				'EmergencyTeam_id_field'=>"EmergencyTeam_id",
				'GeoserviceTransportRel_object'=>"v_EmergencyTeamTNCRel",
			);
		} else {
			$result = array(
				'GeoserviceTransport_id_field' => "WialonEmergencyTeamId",
				// 'GeoserviceTransport_name_field'=>"WialonEmergencyTeamId",
				'EmergencyTeam_id_field' => "EmergencyTeam_id",
				'GeoserviceTransportRel_object' => "v_EmergencyTeamWialonRel",
			);
		}
				
		foreach($result as $key=>$value) {
			$result["$key"] = $value;
		}
		
		return $result;
	}
	
	/**
	* @desc Получение дежурств наряда бригады СМП
	* @param array $data
	* @return array or false
	*/
	public function loadEmergencyTeamVigils( $data ){
		if ( !array_key_exists( 'EmergencyTeam_id', $data ) || !$data['EmergencyTeam_id'] ) {
			return array( array( 'Err_Msg' => 'Не указан идентификатор наряда') );
		}
		
		$sql = "
			SELECT DISTINCT
				CETD.CmpEmTeamDuty_id,
				CETD.EmergencyTeam_id,
				
				CONVERT( varchar, CETD.CmpEmTeamDuty_PlanBegDT, 120 ) as CmpEmTeamDuty_PlanBegDT,
				CONVERT( varchar, CETD.CmpEmTeamDuty_PlanEndDT, 120 ) as CmpEmTeamDuty_PlanEndDT,
				CONVERT( varchar, CETD.CmpEmTeamDuty_FactBegDT, 120 ) as CmpEmTeamDuty_FactBegDT,
				CONVERT( varchar, CETD.CmpEmTeamDuty_FactEndDT, 120 ) as CmpEmTeamDuty_FactEndDT,
				
				case when SRGN.KLSubRgn_FullName is not null then ''+SRGN.KLSubRgn_FullName else 'г.'+City.KLCity_Name end +
					case when Town.KLTown_FullName is not null then ', '+Town.KLTown_FullName else '' end +
					case when Street.KLStreet_FullName is not null then ', ул.'+Street.KLStreet_Name else '' end +
					case when CETD.CmpEmTeamDuty_House is not null then ', д.'+CETD.CmpEmTeamDuty_House else '' end +
					case when CETD.CmpEmTeamDuty_Flat is not null then ', кв.'+CETD.CmpEmTeamDuty_Flat else '' end +
					case when UAD.UnformalizedAddressDirectory_Name is not null then ', Место: '+UAD.UnformalizedAddressDirectory_Name else '' 
				end as address_AddressText,
				
				CETD.CmpEmTeamDuty_Description
				
				FROM v_CmpEmTeamDuty as CETD
					LEFT JOIN v_KLRgn RGN on RGN.KLRgn_id = CETD.KLRgn_id
					LEFT JOIN v_KLSubRgn SRGN on SRGN.KLSubRgn_id = CETD.KLSubRgn_id
					LEFT JOIN v_KLCity City on City.KLCity_id = CETD.KLCity_id
					LEFT JOIN v_KLTown Town on Town.KLTown_id = CETD.KLTown_id
					LEFT JOIN v_KLStreet Street on Street.KLStreet_id = CETD.KLStreet_id
					LEFT JOIN v_UnformalizedAddressDirectory UAD on UAD.UnformalizedAddressDirectory_id = CETD.UnformalizedAddressDirectory_id
				
				WHERE CETD.EmergencyTeam_id = :EmergencyTeam_id
			";
			
		$sqlArr = array(
			'EmergencyTeam_id' => $data['EmergencyTeam_id'],
		);
		
		$query = $this->db->query( $sql, $sqlArr );
		if ( is_object( $query ) ) {
			return $query->result_array();
		}
		
		return false;
	}
	
	/**
	* @desc Получение информации о дежурстве наряда бригады СМП
	* @param array $data
	* @return array or false
	*/
	public function loadSingleEmergencyTeamVigil( $data ){
		if ( !array_key_exists( 'EmergencyTeam_id', $data ) || !$data['EmergencyTeam_id'] ) {
			return array( array( 'Err_Msg' => 'Не указан идентификатор наряда') );
		}
		
		$sqlArr = array(
			'EmergencyTeam_id' => $data['EmergencyTeam_id'],
		);
		
		$where[] = "ET.EmergencyTeam_id = :EmergencyTeam_id";
		
		if(!empty($data["CmpEmTeamDuty_id"])){
			$where[] = "CmpEmTeamDuty_id = :CmpEmTeamDuty_id";
			$sqlArr["CmpEmTeamDuty_id"] = $data["CmpEmTeamDuty_id"];
		}
		else{
			//если дежурство на добавление, то возвращаем данные бригады
			$sql = "SELECT 
				ET.EmergencyTeam_id, 
				ET.EmergencyTeam_Num,
				CONVERT( varchar, ETD.EmergencyTeamDuty_DTStart, 120 ) as CmpEmTeamDuty_PlanBegDT,
				CONVERT( varchar,ETD.EmergencyTeamDuty_DTFinish, 120 ) as CmpEmTeamDuty_PlanEndDT,
				CONVERT( varchar, ETD.EmergencyTeamDuty_factToWorkDT, 120 ) as CmpEmTeamDuty_FactBegDT,
				CONVERT( varchar,ETD.EmergencyTeamDuty_factEndWorkDT, 120 ) as CmpEmTeamDuty_FactEndDT				
			FROM v_EmergencyTeam as ET 
				LEFT JOIN v_EmergencyTeamDuty ETD with(nolock) on ET.EmergencyTeam_id = ETD.EmergencyTeam_id
			".ImplodeWherePH( $where );
			
			$query = $this->db->query( $sql, $sqlArr );
			
			if ( is_object( $query ) ) {return $query->result_array();}
			return false;
		}
		
		$sql = "
			SELECT
				ET.EmergencyTeam_id,
				ET.EmergencyTeam_Num,
				CETD.CmpEmTeamDuty_id,				
				CONVERT( varchar, CETD.CmpEmTeamDuty_PlanBegDT, 120 ) as CmpEmTeamDuty_PlanBegDT,
				CONVERT( varchar, CETD.CmpEmTeamDuty_PlanEndDT, 120 ) as CmpEmTeamDuty_PlanEndDT,
				CONVERT( varchar, CETD.CmpEmTeamDuty_FactBegDT, 120 ) as CmpEmTeamDuty_FactBegDT,
				CONVERT( varchar, CETD.CmpEmTeamDuty_FactEndDT, 120 ) as CmpEmTeamDuty_FactEndDT,
				CETD.CmpEmTeamDuty_Description,
				CETD.KLRgn_id,
				CETD.KLSubRgn_id,
				CETD.KLCity_id,
				CETD.KLTown_id,
				CETD.KLStreet_id,
				CETD.CmpEmTeamDuty_House,
				CETD.CmpEmTeamDuty_Corpus,
				CETD.CmpEmTeamDuty_Flat,
				CETD.UnformalizedAddressDirectory_id,
				case when SRGN.KLSubRgn_FullName is not null then ''+SRGN.KLSubRgn_FullName else 'г.'+City.KLCity_Name end +
					case when Town.KLTown_FullName is not null then ', '+Town.KLTown_FullName else '' end +
					case when Street.KLStreet_FullName is not null then ', ул.'+Street.KLStreet_Name else '' end +
					case when CETD.CmpEmTeamDuty_House is not null then ', д.'+CETD.CmpEmTeamDuty_House else '' end +
					case when CETD.CmpEmTeamDuty_Flat is not null then ', кв.'+CETD.CmpEmTeamDuty_Flat else '' end +
					case when UAD.UnformalizedAddressDirectory_Name is not null then ', Место: '+UAD.UnformalizedAddressDirectory_Name else '' 
				end as address_AddressText
			FROM v_EmergencyTeam as ET 
            	LEFT JOIN v_CmpEmTeamDuty CETD on CETD.EmergencyTeam_id = ET.EmergencyTeam_id
				LEFT JOIN v_KLRgn RGN on RGN.KLRgn_id = CETD.KLRgn_id
				LEFT JOIN v_KLSubRgn SRGN on SRGN.KLSubRgn_id = CETD.KLSubRgn_id
				LEFT JOIN v_KLCity City on City.KLCity_id = CETD.KLCity_id
				LEFT JOIN v_KLTown Town on Town.KLTown_id = CETD.KLTown_id
				LEFT JOIN v_KLStreet Street on Street.KLStreet_id = CETD.KLStreet_id
				LEFT JOIN v_UnformalizedAddressDirectory UAD on UAD.UnformalizedAddressDirectory_id = CETD.UnformalizedAddressDirectory_id
			".ImplodeWherePH( $where )."
		";

		$query = $this->db->query( $sql, $sqlArr );
		if ( is_object( $query ) ) {
			return $query->result_array();
		}
		
		return false;
	}
	
	/**
	 * @desc Сохранение дежурства бригады
	 * @param array $data
	 * @return array or false
	 */
	public function saveEmergencyTeamVigil ($data){
		
		if(empty($data["CmpEmTeamDuty_id"])){
			$procedure = "p_CmpEmTeamDuty_ins";
			$data["CmpEmTeamDuty_id"] = null;
		}
		else{
			$procedure = "p_CmpEmTeamDuty_upd";
		}
		
		//предварительные проверки сохранения дежурства
		$presql = "
			SELECT
				ET.EmergencyTeam_id,
				ET.EmergencyTeam_Num,
				case when (ETD.EmergencyTeamDuty_DTStart > :CmpEmTeamDuty_PlanBegDT OR ETD.EmergencyTeamDuty_DTFinish < :CmpEmTeamDuty_PlanBegDT) then 1
					else case when (ETD.EmergencyTeamDuty_DTStart > :CmpEmTeamDuty_PlanEndDT OR ETD.EmergencyTeamDuty_DTFinish < :CmpEmTeamDuty_PlanEndDT) then 2
						else case when(ETD.EmergencyTeamDuty_factToWorkDT > :CmpEmTeamDuty_FactBegDT) then 3
							else case when(CETD.CmpEmTeamDuty_PlanBegDT <= :CmpEmTeamDuty_PlanBegDT AND CETD.CmpEmTeamDuty_PlanEndDT >= :CmpEmTeamDuty_PlanEndDT) then 4
								else 0
							end
						end
					end
				end as errCode
			FROM v_EmergencyTeam as ET 
			LEFT JOIN v_CmpEmTeamDuty CETD on CETD.EmergencyTeam_id = ET.EmergencyTeam_id				
			LEFT JOIN v_EmergencyTeamDuty ETD with(nolock) on ET.EmergencyTeam_id = ETD.EmergencyTeam_id
			WHERE
				ET.EmergencyTeam_id = :EmergencyTeam_id
				AND CETD.CmpEmTeamDuty_id != :CmpEmTeamDuty_id
				
		";

		$query = $this->db->query( $presql, $data );
		
		if ( is_object( $query ) ) {
			$preRes = $query->result_array();
			if(!empty($preRes[0]) && !empty($preRes[0]["errCode"])){
				
				$errMsg = 'Ошибка при проверке пересечений дат';
				
				switch($preRes[0]["errCode"]){
					case 1: {
						$errMsg = "Плановое время начала дежурства должно входить в плановый период работы наряда";
						break;
					}
					case 2: {
						$errMsg = "Плановое время окончания дежурства должно входить в плановый период работы наряда";
						break;
					}
					case 3: {
						$errMsg = "Фактическое время окончания дежурства должно входить в фактичекий период работы наряда";
						break;
					}
					case 4: {
						$errMsg = "Плановый период дежурства не должен пересекаться с плановым периодом другого дежурства";
						break;
					}
				}
				return array( array( 'success' => false, 'Error_Msg' => $errMsg ) );
			}
		}
		//конец проверки
		
		$exeptedFields = array("CmpEmTeamDuty_id");
		$genQuery = $this -> getParamsForSQLQuery($procedure, $data, $exeptedFields);
		$genQueryParams = $genQuery["paramsArray"];
		$genQuerySQL = $genQuery["sqlParams"];
		
		if(empty($data["CmpEmTeamDuty_id"])){			
			$genQueryParams["CmpEmTeamDuty_id"] = null;
		}
		
		$sql = "
			DECLARE
				@ErrCode int,
				@ErrMessage varchar(4000),
				@Res bigint

			SET @Res = :CmpEmTeamDuty_id;

			EXEC $procedure
				@CmpEmTeamDuty_id = @Res output,
				$genQuerySQL
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			SELECT @ErrCode as Error_Code, @ErrMessage as Error_Msg, @Res as CmpEmTeamDuty_id;
		";
		
		//var_dump(getDebugSQL($sql, $genQueryParams)); exit;
		
		$query = $this->db->query( $sql, $genQueryParams );
		
		if ( is_object( $query ) ) {
			return $query->result_array();
		}
		
		return false;
	}
	
	/**
	 * @desc Редактирование времени дежурства бригады
	 * @param array $data
	 * @return array or false
	 */
	public function editEmergencyTeamVigilTimes ($data){
		$presql = "
			SELECT
				*
			FROM v_CmpEmTeamDuty
			WHERE
				CmpEmTeamDuty_id = :CmpEmTeamDuty_id
				
		";

		$query = $this->db->query( $presql, $data );
		$result = $query->first_row('array');
		
		foreach($data as $key => $value){
			if(!empty($value)){
				$result[$key] = $value;
			}
		}
		
		$res = $this -> saveEmergencyTeamVigil($result);
		
		return $res;
	}
	
	/**
	 * @desc Удаление дежурства бригады
	 * @param array $data
	 * @return array or false
	 */
	public function deleteEmergencyTeamVigil ($data){
		
		$sql = "
			DECLARE
				@ErrCode int,
				@ErrMessage varchar(4000),
				@Res bigint

			EXEC p_CmpEmTeamDuty_del
				@CmpEmTeamDuty_id = :CmpEmTeamDuty_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			SELECT @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		
		//var_dump(getDebugSQL($sql, $genQueryParams)); exit;
		$params = array("CmpEmTeamDuty_id" => $data["CmpEmTeamDuty_id"]);
		$query = $this->db->query( $sql, $params );
		
		if ( is_object( $query ) ) {
			return $query->result_array();
		}
		
		return false;
	}
	
	/**
	* inputProcedure - процедура для инсерта
	* params - параметры для вставки
	* exceptedFields исключающие поля (поля не для сохранения)
	* isPostgresql - параметр для конвертации запроса в Postgresql формат	
	* возвращает список параметров(array/string(Postgresql)), значения параметров в sql (string)
	*/
	private function getParamsForSQLQuery( $inputProcedure, $params, $exceptedFields=null, $isPostgresql=false ){
		
		$paramsArray = array();
		$sqlParams = "";
		$paramsPosttgress = "";
		
		//автоматический сбор полей с процедуры
		$queryFields = $this->db->query("select 'Parameter_name' = name, 'Type' = type_name(user_type_id) from sys.parameters where object_id = object_id('".$inputProcedure."')");
		$allFields = $queryFields->result_array();

		//получаем список всех возможных полей
		foreach ($allFields as $fieldVal)
		{
			$field = ltrim($fieldVal["Parameter_name"], "@");
			
			//получение значений параметров
			if( isset($params[$field]) && !empty($params[$field]) ){
				//небольшая ремарка для полей boolean-овского типа
				if($params[$field] == 'true') $params[$field] = 2;
				if($params[$field] == 'false') $params[$field] = 1;
				//
				$paramsArray[$field] = $params[$field];
				//список полей и значений которые определены				
				if( empty($exceptedFields) || !(in_array($field, $exceptedFields)) ) {
					if($isPostgresql){
						$paramsPosttgress .= $params[$field].",\r\n";
						$sqlParams .= $p.",\r\n";							
					}
					else{	
						$sqlParams .= "@".$field." = :".$field.",\r\n";
					}
				}
			}
		}

		//список параметров, значения параметров
		return array(
			"paramsArray" => ($isPostgresql)?$paramsPosttgress:$paramsArray,
			"sqlParams" => $sqlParams
		);
	}
	
	
	/**
	 * @desc Получение оперативной обстановки по бригадам СМП
	 * @param array $data
	 * @return array or false
	 */
	public function loadEmergencyTeamOperEnv( $data ){
		if ( !array_key_exists( 'Lpu_id', $data ) || !$data['Lpu_id'] ) {
			return array( array( 'Err_Msg' => 'Не указан идентификатор ЛПУ') );
		}
		
		//Получаем наименование полей и объектов геосервиса в зависимости от региона (и, возможно, в будущем - МО)
		$GTR = $this->_defineGeoserviceTransportRelQueryParams($data);
		
		if ( $this->db->dbdriver == 'postgre' ) {
			$sql = "
				SELECT DISTINCT
					-- select
					ET.\"EmergencyTeam_id\",
					ET.\"EmergencyTeam_Num\",
					ET.\"EmergencyTeam_CarNum\",
					ET.\"EmergencyTeam_CarBrand\",
					ET.\"EmergencyTeam_CarModel\",
					ET.\"EmergencyTeam_PortRadioNum\",
					ET.\"EmergencyTeam_GpsNum\",
					ET.\"LpuBuilding_id\",
					ET.\"EmergencyTeamStatus_id\",
					ET.\"EmergencyTeamSpec_id\",
					ETSpec.\"EmergencyTeamSpec_Name\",
					ETSpec.\"EmergencyTeamSpec_Code\",

					CASE WHEN COALESCE(ET.\"EmergencyTeam_IsOnline\",1)=2 THEN 'online' ELSE 'offline' END as EmergencyTeam_isOnline,
					(case when ET.\"EmergencyTeam_HeadShift2\" is not null then 1 else 0 end)+
					(case when ET.\"EmergencyTeam_Assistant1\" is not null then 1 else 0 end)+
					(case when ET.\"EmergencyTeam_Assistant2\" is not null then 1 else 0 end) as medPersonCount,

					LB.\"LpuBuilding_Nick\" as EmergencyTeamBuildingName,
					ETS.\"EmergencyTeamStatus_Name\",
					
					--CONVERT( varchar, CAST( ETD.EmergencyTeamDuty_DTStart as datetime ), 120 ) as EmergencyTeamDuty_DTStart,
					TO_CHAR( ETD.\"EmergencyTeamDuty_DTStart\", 'YYYY-MM-DD HH24:MI:SS' ) as \"EmergencyTeamDuty_DTStart\",
					--CONVERT( varchar, CAST( ETD.EmergencyTeamDuty_DTFinish as datetime ), 120 ) as EmergencyTeamDuty_DTFinish,
					TO_CHAR( ETD.\"EmergencyTeamDuty_DTFinish\", 'YYYY-MM-DD HH24:MI:SS' ) as \"EmergencyTeamDuty_DTFinish\",

					ETD.\"EmergencyTeamDuty_id\",
					CASE
						WHEN ETS.\"EmergencyTeamStatus_Code\"=14 THEN 'red'
						WHEN ETS.\"EmergencyTeamStatus_Code\"=3 THEN 'red'
						WHEN ETS.\"EmergencyTeamStatus_Code\"=21 THEN 'blue'
						WHEN ETS.\"EmergencyTeamStatus_Code\"=13 THEN 'blue'
						WHEN ETS.\"EmergencyTeamStatus_Code\"=23 THEN 'green'
						WHEN ETS.\"EmergencyTeamStatus_Code\"=8 THEN 'green'
						ELSE 'black'
					END as EmergencyTeamStatus_Color,
					MP.\"Person_Fin\",
					GTR.{$GTR['GeoserviceTransport_id_field']} as \"GeoserviceTransport_id\"
						
					-- end select
				FROM
					-- from
					dbo.\"v_EmergencyTeam\" as ET
					-- INNER JOIN (
					--	SELECT DISTINCT
					--		\"EmergencyTeam_Num\",
					--		MAX(\"EmergencyTeam_id\") as \"EmergencyTeam_id\"
					--	FROM
					--		dbo.\"v_EmergencyTeam\"
					--	GROUP BY \"Lpu_id\", \"EmergencyTeam_Num\"
					-- ) as q1 ON( q1.\"EmergencyTeam_id\"=ET.\"EmergencyTeam_id\" )
					LEFT JOIN dbo.\"v_EmergencyTeamStatus\" as ETS ON( ETS.\"EmergencyTeamStatus_id\"=ET.\"EmergencyTeamStatus_id\" )
					LEFT JOIN dbo.\"v_MedPersonal\" as MP ON( MP.\"MedPersonal_id\"=ET.\"EmergencyTeam_HeadShift\" )
					LEFT JOIN dbo.\"v_EmergencyTeamSpec\" as ETSpec ON(ET.\"EmergencyTeamSpec_id\" = ETSpec.\"EmergencyTeamSpec_id\")
					LEFT JOIN dbo.{$GTR['GeoserviceTransportRel_object']} as GTR ON( GTR.{$GTR['EmergencyTeam_id_field']}=ET.\"EmergencyTeam_id\" )
					LEFT JOIN dbo.\"v_EmergencyTeamDuty\" as ETD ON( ETD.\"EmergencyTeam_id\"=ET.\"EmergencyTeam_id\" )
					LEFT JOIN dbo.\"v_LpuBuilding\" as LB ON( LB.\"LpuBuilding_id\"=ET.\"LpuBuilding_id\" )
					-- end from
				WHERE
					-- where
					ET.\"Lpu_id\"=?
					AND GTR.{$GTR['GeoserviceTransport_id_field']} > 0
					-- end where
				ORDER BY
					-- order by
					\"EmergencyTeam_Num\"
					-- end order by
			";
			$sqlArr = array(
				$data['Lpu_id'],
			);
		} else {
			$sql = "
				Declare @dt datetime = dbo.tzGetDate();
				SELECT DISTINCT
					-- select
					ET.EmergencyTeam_id,
					ET.EmergencyTeam_Num,
					ET.EmergencyTeam_CarNum,
					ET.EmergencyTeam_CarBrand,
					ET.EmergencyTeam_CarModel,
					ET.EmergencyTeam_PortRadioNum,
					ET.EmergencyTeam_GpsNum,
					ET.LpuBuilding_id,
					ET.EmergencyTeamStatus_id,
					ET.EmergencyTeamSpec_id,
					ETSpec.EmergencyTeamSpec_Name,
					ETSpec.EmergencyTeamSpec_Code,
					case when isnull(ET.EmergencyTeam_isOnline,1)=2 then 'online'
						else 'offline' end as EmergencyTeam_isOnline,
					(case when (ET.EmergencyTeam_HeadShift2 is not null and ET.EmergencyTeam_HeadShift2 != 0) then 1 else 0 end)+
					(case when (ET.EmergencyTeam_Assistant1 is not null and ET.EmergencyTeam_Assistant1 != 0) then 1 else 0 end)+
					(case when (ET.EmergencyTeam_Assistant2 is not null and ET.EmergencyTeam_Assistant2 != 0) then 1 else 0 end) as medPersonCount,
					LB.LpuBuilding_Nick as EmergencyTeamBuildingName,
					ETS.EmergencyTeamStatus_Name,
					CONVERT( varchar, CAST( ETSH.EmergencyTeamStatusHistory_insDT as datetime ), 120 ) as EmergencyTeamStatusHistory_insDT,
					CONVERT( varchar, CAST( etd.EmergencyTeamDuty_DTStart as datetime ), 120 ) as EmergencyTeamDuty_DTStart,
					CONVERT( varchar, CAST( etd.EmergencyTeamDuty_DTFinish as datetime ), 120 ) as EmergencyTeamDuty_DTFinish,
					ETD.EmergencyTeamDuty_id,
					CC.CmpCallCard_Numv,
					CC.CmpCallCard_Ngod,
					CC.CmpCallCard_id,
					CASE
						WHEN ETS.EmergencyTeamStatus_Code=14 THEN 'red'
						WHEN ETS.EmergencyTeamStatus_Code=3 THEN 'red'
						WHEN ETS.EmergencyTeamStatus_Code=21 THEN 'blue'
						WHEN ETS.EmergencyTeamStatus_Code=13 THEN 'blue'
						WHEN ETS.EmergencyTeamStatus_Code=23 THEN 'green'
						WHEN ETS.EmergencyTeamStatus_Code=8 THEN 'green'
						ELSE 'black'
					END as EmergencyTeamStatus_Color,
					MP.Person_Fin,
					GTR.{$GTR['GeoserviceTransport_id_field']} as GeoserviceTransport_id
					-- end select
				FROM
					-- from
					v_EmergencyTeam as ET with (nolock)
					-- INNER JOIN (
					--	SELECT DISTINCT EmergencyTeam_Num, MAX(EmergencyTeam_id) as EmergencyTeam_id FROM v_EmergencyTeam with (nolock) GROUP BY Lpu_id, EmergencyTeam_Num
					-- ) as q1 ON( q1.EmergencyTeam_id=ET.EmergencyTeam_id )
					LEFT JOIN v_EmergencyTeamStatus AS ETS with (nolock) ON( ETS.EmergencyTeamStatus_id=ET.EmergencyTeamStatus_id )
					LEFT JOIN v_MedPersonal as MP with(nolock) ON( MP.MedPersonal_id=ET.EmergencyTeam_HeadShift )
					LEFT JOIN v_EmergencyTeamSpec as ETSpec with(nolock) ON(ET.EmergencyTeamSpec_id = ETSpec.EmergencyTeamSpec_id)
					LEFT JOIN v_CmpCallCard as CC with (nolock) ON(				 
						CC.CmpCallCard_id = (SELECT TOP 1 C2.CmpCallCard_id FROM v_CmpCallCard as C2 with(nolock) WHERE C2.EmergencyTeam_id = ET.EmergencyTeam_id AND C2.CmpCallCardStatusType_id = 2 ORDER BY C2.CmpCallCard_updDT DESC)
					)
					LEFT JOIN {$GTR['GeoserviceTransportRel_object']} GTR with (nolock) ON( GTR.{$GTR['EmergencyTeam_id_field']}=ET.EmergencyTeam_id )
					LEFT JOIN v_EmergencyTeamDuty ETD with(nolock) on ET.EmergencyTeam_id = ETD.EmergencyTeam_id
					LEFT JOIN v_LpuBuilding LB with(nolock) on ET.LpuBuilding_id = LB.LpuBuilding_id
					OUTER APPLY(
						SELECT TOP 1
							ETSH.EmergencyTeamStatusHistory_insDT,
							ETSH.EmergencyTeamStatus_id
						FROM v_EmergencyTeamStatusHistory ETSH with(nolock)
						WHERE ETSH.EmergencyTeam_id = ET.EmergencyTeam_id
						ORDER BY
							EmergencyTeamStatusHistory_id DESC
					) as ETSH
					-- end from
				WHERE
					-- where
					(ET.EmergencyTeam_isTemplate = 1 or ET.EmergencyTeam_isTemplate IS NULL)
					AND (ETD.EmergencyTeamDuty_DTStart < @dt AND (ETD.EmergencyTeamDuty_DTFinish > @dt))
					AND ETD.EmergencyTeamDuty_isComesToWork=2
					AND	ET.Lpu_id = :Lpu_id
					--AND GTR.{$GTR['GeoserviceTransport_id_field']} > 0
					-- end where
				ORDER BY
					-- order by
					EmergencyTeam_Num
					-- end order by
			";
			$sqlArr = array(
				'Lpu_id' => $data['Lpu_id'],
			);
		}
		
		$query = $this->db->query( $sql, $sqlArr );
		if ( is_object( $query ) ) {
			return $query->result_array();
		}
		
		return false;
	}

	/**
	 * Возвращает данные по оперативной обстановке бригад СМП
	 * С возможность фильтрации по подстанции
	 *
	 * @param array $data
	 * @return array or false
	 */
	public function loadEmergencyTeamOperEnvForSmpUnit( $data ) {
		$is_pg = $this->db->dbdriver == 'postgre' ? true : false;

		$where = array();
		$params = array();

		$lpuBuildingsWorkAccess = null;

        $regionNick = getRegionNick();

		$CurArmType = (!empty($data['CurArmType']) ? $data['CurArmType'] : '');
		
		// здесь мы получаем список доступных подстанций для работы из лдапа
		$user = pmAuthUser::find($_SESSION['login']);
		$settings = @unserialize($user->settings);
		
		if ( isset($settings['lpuBuildingsWorkAccess']) && is_array($settings['lpuBuildingsWorkAccess']) ) {
			$lpuBuildingsWorkAccess = $settings['lpuBuildingsWorkAccess'];
		}
		//

		//Получаем наименование полей и объектов геосервиса в зависимости от региона (и, возможно, в будущем - МО)
		$GTR = $this->_defineGeoserviceTransportRelQueryParams($data);

		if ( !in_array($data["CurArmType"], array('dispcallnmp', 'dispdirnmp')) && !in_array(getRegionNick(), array('astra', 'khak')) ){
			$where[] = "ET.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data[ 'Lpu_id' ];
		}

		$WorkAccess = '';
		if ( !empty( $lpuBuildingsWorkAccess) ) {
			
			if($lpuBuildingsWorkAccess[0]=='') return array( array( 'success' => false, 'Error_Msg' => 'Не настроен список доступных для работы подстанций' ) );

			if(empty($data['LpuBuilding_id'])) $data['LpuBuilding_id'] = $lpuBuildingsWorkAccess[0];
		}
		else{
			return array( array( 'success' => false, 'Error_Msg' => 'Не настроен список доступных для работы подстанций' ) );
		}
		// Не забывать, что условия должны работать в двух базах
		// При необходимости разделять с помощью флага $is_pg
		/*elseif ( !empty( $data[ 'MedService_id' ] ) ) {
			$where[] = "MS.\"MedService_id\" = :MedService_id";
			$params['MedService_id'] = $data[ 'MedService_id' ];
		}*/
		//$where[] = "GTR.{$GTR['GeoserviceTransport_id_field']} >0";
		$where[] = "COALESCE(ET.EmergencyTeam_isTemplate,1) = 1";

		$this->load->model('CmpCallCard_model4E', 'CmpCallCard_model4E');
		$operDpt = $this->CmpCallCard_model4E->getOperDepartament($data);

		$this->load->model('LpuStructure_model', 'LpuStructure');
		$operDptParams = $this->LpuStructure->getLpuBuildingData(array("LpuBuilding_id"=>$operDpt["LpuBuilding_pid"]));

		$operDptParams = $operDptParams[0];
		/*
		$sql = "select top 1 *
					from v_SmpUnitParam with (nolock)
					where LpuBuilding_id = :LpuBuilding_pid
					order by SmpUnitParam_id desc";
		$operDptParams = $this->getFirstRowFromQuery( $sql, array('LpuBuilding_pid' => $operDpt["LpuBuilding_pid"]) );
		*/
		//здесь реализовано оповещение и автоматический выход на смену / снятие со смены бригады смп
        $autoStartDutyTeamsIds = null;
        $autoFinishDutyTeamsIds = null;

        $cur_date = new DateTime();
        $cur_date = $cur_date->format('Y-m-d H:i:s');

		if(!empty($operDptParams['SmpUnitParam_IsAutoEmergDuty']) && $operDptParams['SmpUnitParam_IsAutoEmergDuty'] == 'true') {
			//получаем список просроченных по плановому выходу на смену бригад
			//Добавим фильтр по выбранным подстанциям, чтоб не выводились чужие бригады
			$autoStartDutyTeams = $this->getAutoStartVigil($data, array_merge($where, array("LB.LpuBuilding_id in (" . implode(",", $lpuBuildingsWorkAccess) . ")")) , $params);
			if ($autoStartDutyTeams) {
				$autoStartDutyTeamsIds = array();
				foreach ($autoStartDutyTeams as &$value) {
					$value['ComesToWork'] = 2;
					$value['EmergencyTeamDuty_factToWorkDT'] = $cur_date;
					$autoStartDutyTeamsIds[] = $value["EmergencyTeam_id"];
				}

				$teamsAutoStartArray = array('pmUser_id' => $data['pmUser_id']);

				$teamsAutoStartArray['EmergencyTeamsDutyTimesAndComing'] = json_encode($autoStartDutyTeams);

				//проставляем время фактического выхода на смену и признак
				$this->setEmergencyTeamsWorkComingList($teamsAutoStartArray);

			}
		}

		if(!empty($operDptParams['SmpUnitParam_IsAutoEmergDutyClose']) && $operDptParams['SmpUnitParam_IsAutoEmergDutyClose'] == 'true') {

			//получаем список просроченных по плановому завершению смены бригад
			//Добавим фильтр по выбранным подстанциям, чтоб не закрывались чужие бригады
			$autoFinishDutyTeams = $this->getAutoFinishVigil($data, array_merge($where, array("LB.LpuBuilding_id in (" . implode(",", $lpuBuildingsWorkAccess) . ")")) , $params);

			if ($autoFinishDutyTeams) {
				$autoFinishDutyTeamsIds = array();

				foreach ($autoFinishDutyTeams as &$value) {
					$value['ComesToWork'] = 2;
					$value['closed'] = 2;
					$value['EmergencyTeamDuty_factEndWorkDT'] = $cur_date;
					$autoFinishDutyTeamsIds[] = $value["EmergencyTeam_id"];
				}

				$teamsAutoFinishArray = array('pmUser_id' => $data['pmUser_id']);

				$teamsAutoFinishArray['EmergencyTeamsDutyTimesAndComing'] = json_encode($autoFinishDutyTeams);

				if ($autoFinishDutyTeamsIds){
					//проставляем время фактического завершения смены и признак
					$this->setEmergencyTeamsWorkComingList($teamsAutoFinishArray);
				}

			}
		}

		//В зависимости от настроек загружаем вызовы
		if($operDptParams['SmpUnitParam_IsViewOther'] == 'true'){
			//всех подстанция опер отдела
			$params['LpuBuilding_pid'] = $operDpt["LpuBuilding_pid"];
			$where[] = "SUP.LpuBuilding_pid = :LpuBuilding_pid";
			//с флагом доступа к бригаде
			$WorkAccess = ",CASE WHEN (ISNULL(ETSpec.EmergencyTeamSpec_isTeamAvailable, 1) = 2 OR (LB.LpuBuilding_id in (" . implode(",", $lpuBuildingsWorkAccess) . "))) THEN 'true' ELSE 'false' END as WorkAccess";
		}else{
			//либо только выбранные пользователем
			//в этом месте запрос подвисает, тк подразделений на рабочем много, нужен доп. фильтр по мо
			$where[] = "LB.LpuBuilding_id in (" . implode(",", $lpuBuildingsWorkAccess) . ")";
			$WorkAccess = ",'true' as WorkAccess";
		}

		$lastCallOnTeamApply = "";
		$checkInAddress = "'' as lastCheckinAddress,";
		if ( in_array($regionNick, array('perm', 'kareliya')) && !in_array($CurArmType, array('dispcallnmp', 'dispnmp', 'dispdirnmp')) ) {
			//из за СМП Перми не будем тормозить другие регионы

			$checkInAddress = "
				CASE WHEN (ETS.EmergencyTeamStatus_Code IN(21) OR lastCallOnTeam.CmpCallCard_id IS NULL) THEN
					case when LBSRGNCity.KLSubRgn_Name is not null then LBSRGNCity.KLSocr_Nick+' '+LBSRGNCity.KLSubRgn_Name+', '
						else case when LBSRGNTown.KLSubRgn_Name is not null then LBSRGNTown.KLSocr_Nick+' '+LBSRGNTown.KLSubRgn_Name+', '
						else case when LBSRGN.KLSubRgn_Name is not null then LBSRGN.KLSocr_Nick+' '+LBSRGN.KLSubRgn_Name+', ' else '' end end end+
						case when LBCity.KLCity_Name is not null then 'г. '+LBCity.KLCity_Name else '' end+
						case when LBTown.KLTown_FullName is not null then
							case when LBCity.KLCity_Name is not null then ', ' else '' end
							 +isnull(LOWER(LBTown.KLSocr_Nick)+'. ','') + LBTown.KLTown_Name else ''
						end+

						case when LBStreet.KLStreet_FullName is not null then
							case when LBStreet.KLSocr_Nick is not null then ', '+LOWER(LBStreet.KLSocr_Nick)+'. '+LBStreet.KLStreet_Name else
							', '+LBStreet.KLStreet_FullName  end
						else ''
						end +

						case when LBAddress.Address_House is not null then ', д.'+LBAddress.Address_House else '' end +
						case when LBAddress.Address_Corpus is not null then ', к.'+LBAddress.Address_Corpus else '' end +
						case when LBAddress.Address_Flat is not null then ', кв.'+LBAddress.Address_Flat else '' end
				ELSE
					lastCallOnTeam.Adress_Name
				END as lastCheckinAddress,
			";

			$lastCallOnTeamApply = "
				outer apply (
					select top 1
						c.CmpCallCard_id
						,case when SRGNCity.KLSubRgn_Name is not null then SRGNCity.KLSocr_Nick+' '+SRGNCity.KLSubRgn_Name+', '
						else case when SRGNTown.KLSubRgn_Name is not null then SRGNTown.KLSocr_Nick+' '+SRGNTown.KLSubRgn_Name+', '
						else case when SRGN.KLSubRgn_Name is not null then SRGN.KLSocr_Nick+' '+SRGN.KLSubRgn_Name+', ' else '' end end end+
						case when City.KLCity_Name is not null then 'г. '+City.KLCity_Name else '' end+
						case when Town.KLTown_FullName is not null then
							case when City.KLCity_Name is not null then ', ' else '' end
							 +isnull(LOWER(Town.KLSocr_Nick)+'. ','') + Town.KLTown_Name else ''
						end+

						case when Street.KLStreet_FullName is not null then
							case when Street.KLSocr_Nick is not null then ', '+LOWER(Street.KLSocr_Nick)+'. '+Street.KLStreet_Name else
							', '+Street.KLStreet_FullName  end
						else case when c.CmpCallCard_Ulic is not null then ', '+c.CmpCallCard_Ulic else '' end
						end +

						case when SecondStreet.KLStreet_FullName is not null then
							case when SecondStreet.KLSocr_Nick is not null then ', '+LOWER(SecondStreet.KLSocr_Nick)+'. '+SecondStreet.KLStreet_Name else
							', '+SecondStreet.KLStreet_FullName end
							else ''
						end +

						case when c.CmpCallCard_Dom is not null then ', д.'+c.CmpCallCard_Dom else '' end +
						case when c.CmpCallCard_Korp is not null then ', к.'+c.CmpCallCard_Korp else '' end +
						case when c.CmpCallCard_Kvar is not null then ', кв.'+c.CmpCallCard_Kvar else '' end +
						case when c.CmpCallCard_Room is not null then ', ком. '+c.CmpCallCard_Room else '' end +
						case when UAD.UnformalizedAddressDirectory_Name is not null then ', Место: '+UAD.UnformalizedAddressDirectory_Name else '' end as Adress_Name
					from v_CmpCallCardTeamsAssignmentHistory cctah with (nolock)
					left join v_CmpCallCard c with (nolock) on cctah.CmpCallCard_id = c.CmpCallCard_id
					outer apply (
						select top 1 * from v_EmergencyTeamStatusHistory (nolock) WHERE EmergencyTeam_id = c.EmergencyTeam_id and CmpCallCard_id = c.CmpCallCard_id order by EmergencyTeamStatusHistory_id desc
						) as lastETSH
					left join v_EmergencyTeamStatus lastCallETS with (nolock) on lastETSH.EmergencyTeamStatus_id = lastCallETS.EmergencyTeamStatus_id
					left join v_KLRgn RGN with(nolock) on RGN.KLRgn_id = c.KLRgn_id
					left join v_KLRgn RGNCity with(nolock) on RGNCity.KLRgn_id = c.KLCity_id
					left join v_KLSubRgn SRGN with(nolock) on SRGN.KLSubRgn_id = c.KLSubRgn_id
					left join v_KLCity City with(nolock) on City.KLCity_id = c.KLCity_id
					left join v_KLTown Town with(nolock) on Town.KLTown_id = c.KLTown_id
					left join v_KLSubRgn SRGNTown with(nolock) on SRGNTown.KLSubRgn_id = c.KLTown_id
					left join v_KLSubRgn SRGNCity with(nolock) on SRGNCity.KLSubRgn_id = c.KLCity_id
					left join v_KLStreet Street with(nolock) on Street.KLStreet_id = c.KLStreet_id
					left join v_UnformalizedAddressDirectory UAD with(nolock) on UAD.UnformalizedAddressDirectory_id = c.UnformalizedAddressDirectory_id
					left join v_KLStreet SecondStreet (nolock) on SecondStreet.KLStreet_id = c.CmpCallCard_UlicSecond
					where c.EmergencyTeam_id = ET.EmergencyTeam_id and lastCallETS.EmergencyTeamStatus_Code != 36
					order by CmpCallCardTeamsAssignmentHistory_id desc
				) lastCallOnTeam
				LEFT JOIN v_Address LBAddress with(nolock) on LB.Address_id = LBAddress.Address_id
				left join v_KLRgn LBRGN with(nolock) on LBRGN.KLRgn_id = LBAddress.KLRgn_id
				left join v_KLRgn LBRGNCity with(nolock) on LBRGNCity.KLRgn_id = LBAddress.KLCity_id
				left join v_KLSubRgn LBSRGN with(nolock) on LBSRGN.KLSubRgn_id = LBAddress.KLSubRgn_id
				left join v_KLCity LBCity with(nolock) on LBCity.KLCity_id = LBAddress.KLCity_id
				left join v_KLTown LBTown with(nolock) on LBTown.KLTown_id = LBAddress.KLTown_id
				left join v_KLSubRgn LBSRGNTown with(nolock) on LBSRGNTown.KLSubRgn_id = LBAddress.KLTown_id
				left join v_KLSubRgn LBSRGNCity with(nolock) on LBSRGNCity.KLSubRgn_id = LBAddress.KLCity_id
				left join v_KLStreet LBStreet with(nolock) on LBStreet.KLStreet_id = LBAddress.KLStreet_id
			";
		}


		if(!empty($operDptParams['SmpUnitParam_IsSignalEnd']) && $operDptParams['SmpUnitParam_IsSignalEnd'] == 'true'){
			$soundSetting = ",2 as IsSignalEnd";
		}
		else{
			$soundSetting = ",'1' as IsSignalEnd";
		}

		$countCallsOnTeam = ",null as countcallsOnTeam";
		if($operDptParams['SmpUnitParam_IsShowCallCount'] == 'true'){
			$countCallsOnTeam = ',callsOnTeam.countCalls as countcallsOnTeam';
		}

		// Вышел на смену
		$where[] = "ETD.EmergencyTeamDuty_isComesToWork = 2";
		//Не закрыта
		//$where[] = "ETD.EmergencyTeamDuty_isClose = 1";

		//Временной интервал
		$where[] = "
			( ISNULL(ETD.EmergencyTeamDuty_factToWorkDT, @datetime) <= @datetime
				and ISNULL(ETD.EmergencyTeamDuty_factEndWorkDT, @datetime) >= @datetime )
		";

		$sql = "
			declare @datetime datetime = dbo.tzGetDate()
			SELECT DISTINCT
				-- select
				ET.EmergencyTeam_id,
				ET.EmergencyTeam_Num,
				ET.EmergencyTeam_CarNum,
				ET.EmergencyTeam_CarBrand,
				ET.EmergencyTeam_CarModel,
				ET.EmergencyTeam_PortRadioNum,
				ET.EmergencyTeam_GpsNum,
				ET.LpuBuilding_id,
				ET.EmergencyTeamStatus_id,
				ET.EmergencyTeamSpec_id,
				CASE
					WHEN  @datetime BETWEEN ETD.EmergencyTeamDuty_factToWorkDT AND ETD.EmergencyTeamDuty_DTFinish THEN 0 ELSE 1
				END as EmergencyTeamDuty_isNotFact,
				DATEDIFF(mi,ETES.EmergencyTeamStatusHistory_insDT,@datetime) as EmergencyTeamStatusHistory_insDT,
				CONVERT( varchar(20), ETD.EmergencyTeamDuty_DTStart, 120 ) as EmergencyTeamDuty_DTStart,
				CONVERT( varchar(20), ETD.EmergencyTeamDuty_DTFinish, 120 ) as EmergencyTeamDuty_DTFinish,
				CONVERT( varchar(20), ETD.EmergencyTeamDuty_factToWorkDT, 120 ) as EmergencyTeamDuty_factToWorkDT,
				CONVERT( varchar(20), ETD.EmergencyTeamDuty_factEndWorkDT, 120 ) as EmergencyTeamDuty_factEndWorkDT,
				ETD.EmergencyTeamDuty_id,
				-- ETD.EmergencyTeamStatusHistory_insDT,
				ETSpec.EmergencyTeamSpec_Name,
				ETSpec.EmergencyTeamSpec_Code,
				CC.CmpCallCard_id,
				CC.CmpCallCard_Numv,
				
				CC.CmpCallCard_Ngod,
				CASE WHEN ETS.EmergencyTeamStatus_Code in (3,53) THEN hL.Lpu_Nick ELSE '' END as HLpu_Nick,
				CASE WHEN COALESCE(ET.EmergencyTeam_isOnline,1)=2 THEN 'online' ELSE 'offline' END as EmergencyTeam_isOnline,
				--case when ET.CMPTabletPC_id is not null then 1 ELSE 0 END as EmergencyTeam_isTablet,

				( CASE WHEN COALESCE(ET.EmergencyTeam_HeadShift2,0)!=0 THEN 1 ELSE 0 END )
				+ ( CASE WHEN COALESCE(ET.EmergencyTeam_Assistant1,0)!=0 THEN 1 ELSE 0 END )
				+ ( CASE WHEN COALESCE(ET.EmergencyTeam_Assistant2,0)!=0 THEN 1 ELSE 0 END ) as medPersonCount,

				ETS.EmergencyTeamStatus_Name,
				ETS.EmergencyTeamStatus_Code,
				CASE
					WHEN ETS.EmergencyTeamStatus_Code IN(3,14) THEN 'red'
					WHEN ETS.EmergencyTeamStatus_Code IN(13,21) THEN 'blue'
					WHEN ETS.EmergencyTeamStatus_Code IN(8,23) THEN 'green'
					ELSE 'black'
				END as EmergencyTeamStatus_Color,
				CASE
					WHEN ETS.EmergencyTeamStatus_Code IN(13,4,5,20,21,47) THEN 'true' --(свободна, конец обслуживания, Возвращение на базу, свободна на подстанции, возвращение на подстанцию, Свободна на территории)
					ELSE 'false'
				END as EmergencyTeamStatus_FREE,
				{$checkInAddress}
				LB.LpuBuilding_Name,
				L.Lpu_Nick,
				UPPER(SUBSTRING(MP.Person_SurName,1,1)) +SUBSTRING( RTRIM(LOWER(MP.Person_SurName)), 2, LEN(RTRIM(LOWER(MP.Person_SurName))) )+' '+SUBSTRING(MP.Person_FirName,1,1) + case when MP.Person_SecName IS NULL then + '' else + ' '+SUBSTRING(MP.Person_SecName,1,1) END as Person_Fin,
				MP.MedPersonal_id,
				COALESCE(GTR.{$GTR['GeoserviceTransport_id_field']}, MPC.MedProductCard_Glonass) as GeoserviceTransport_id,
				--,MS.MedService_id
				SUP.LpuBuilding_pid,
				alertToStartVigil.CmpEmTeamDuty_id as alertToStartVigil,
				alertToEndVigil.CmpEmTeamDuty_id as alertToEndVigil
				" . $soundSetting . "
				" . $WorkAccess . "
				" . $countCallsOnTeam . "
				-- end select
			FROM
				-- from
				v_EmergencyTeam as ET with (nolock)
				LEFT JOIN v_EmergencyTeamStatus AS ETS with (nolock) ON( ETS.EmergencyTeamStatus_id=ET.EmergencyTeamStatus_id )
				--LEFT JOIN v_EmergencyTeamStatusHistory as ETSH with (nolock) ON( ETS.EmergencyTeamStatus_id=ETSH.EmergencyTeamStatus_id )
				LEFT JOIN v_EmergencyTeamDuty AS ETD with (nolock) ON( ETD.EmergencyTeam_id=ET.EmergencyTeam_id)
				LEFT JOIN v_MedPersonal as MP with(nolock) ON( MP.MedPersonal_id=ET.EmergencyTeam_HeadShift )
				LEFT JOIN v_EmergencyTeamSpec as ETSpec with(nolock) ON(ET.EmergencyTeamSpec_id = ETSpec.EmergencyTeamSpec_id)
				outer apply (
					select top 1
						c.CmpCallCard_id,
						c.CmpCallCard_Numv,
						c.CmpCallCard_Ngod,
						c.Lpu_hid,
						c.CmpCallCard_updDT
					from v_CmpCallCardTeamsAssignmentHistory cctah with (nolock)
					left join v_CmpCallCard c with (nolock) on cctah.CmpCallCard_id = c.CmpCallCard_id
					left join v_CmpCallCardStatus CCCS with (nolock) on CCCS.CmpCallCardStatus_id = c.CmpCallCardStatus_id
					where c.EmergencyTeam_id = ET.EmergencyTeam_id
						AND CCCS.CmpCallCardStatusType_id = 2
					order by CmpCallCardTeamsAssignmentHistory_id asc
				) CC
				outer apply (
					select count(1) as countCalls
					from v_CmpCallCard c with (nolock)
					where c.EmergencyTeam_id = ET.EmergencyTeam_id
						AND c.CmpCallCardStatusType_id = 2
				) callsOnTeam
				LEFT JOIN v_Lpu hL with (nolock) on (CC.Lpu_hid = hL.Lpu_id )
				LEFT JOIN passport.v_MedProductCard MPC with (nolock) on MPC.MedProductCard_id = ET.MedProductCard_id
				LEFT JOIN {$GTR['GeoserviceTransportRel_object']} as GTR with (nolock) ON( GTR.{$GTR['EmergencyTeam_id_field']}=ET.EmergencyTeam_id )
				LEFT JOIN v_LpuBuilding LB with (nolock) on LB.LpuBuilding_id = ET.LpuBuilding_id
				LEFT JOIN v_Lpu L with (nolock) on LB.Lpu_id = L.Lpu_id
				--LEFT JOIN v_MedService MS with (nolock) on MS.LpuBuilding_id = LB.LpuBuilding_id
				outer apply (
					select top 1 EmergencyTeamStatusHistory_insDT
					from v_EmergencyTeamStatusHistory with (nolock)
					where EmergencyTeam_id = ET.EmergencyTeam_id
					order by EmergencyTeamStatusHistory_insDT desc
				) ETES
				
				LEFT JOIN v_CmpEmTeamDuty as alertToStartVigil with (nolock) on (
					alertToStartVigil.EmergencyTeam_id = ET.EmergencyTeam_id
					AND (@datetime > alertToStartVigil.CmpEmTeamDuty_PlanBegDT)
					AND (@datetime < alertToStartVigil.CmpEmTeamDuty_PlanEndDT)
					AND ETS.EmergencyTeamStatus_Code IN(4,5,13,47)
				)
					
				LEFT JOIN v_CmpEmTeamDuty as alertToEndVigil with (nolock) on (
					alertToEndVigil.EmergencyTeam_id = ET.EmergencyTeam_id
					AND (@datetime > alertToEndVigil.CmpEmTeamDuty_PlanEndDT)
					AND ETS.EmergencyTeamStatus_Code IN(50)
				)
				outer apply (
					select top 1 *
					from v_SmpUnitParam with (nolock)
					where LpuBuilding_id = ET.LpuBuilding_id
					order by SmpUnitParam_id desc
				) SUP
				{$lastCallOnTeamApply}
				-- end from
			".ImplodeWherePH( $where )."
			ORDER BY
				-- order by
				EmergencyTeamStatus_FREE DESC,
				EmergencyTeam_Num
				-- end order by
		";

		//var_dump(getDebugSQL($sql, $params)); exit;
		if (isset($_GET['dbg']) && $_GET['dbg'] == '1')
			var_dump(getDebugSQL($sql, $params));

		$query = $this->db->query( $sql, $params );
		if ( is_object( $query ) ) {
			return $query->result_array();
		}
		return false;
	}

    /**
     * Возвращает список бригад которым пора на смену (началось плановое время)
	 *
	 * @param array $data
	 * @return array or false
     */
    private function getAutoStartVigil( $data, $where, $params){

        $where[] = "COALESCE(ETD.EmergencyTeamDuty_IsCancelledStart, 1) = 1";
        $where[] = "COALESCE(ETD.EmergencyTeamDuty_isClose, 1) = 1";
        $where[] = "COALESCE(ETD.EmergencyTeamDuty_isComesToWork, 1) = 1";
        $where[] = "ETD.EmergencyTeamDuty_DTStart <= @datetime";
        $where[] = "ISNULL(ETD.EmergencyTeamDuty_factToWorkDT, 1) = 1";
        //не будем выгребать все бригады, их слишком много
        $where[] = "DATEDIFF(hour, ETD.EmergencyTeamDuty_DTStart, dbo.tzGetDate())<24";

        $sql = "
            declare @datetime datetime = dbo.tzGetDate()
            SELECT
                ET.EmergencyTeam_id,
                ETD.EmergencyTeamDuty_id,
                ETD.EmergencyTeamDuty_isClose,
                CASE WHEN COALESCE(ETD.EmergencyTeamDuty_isClose,1) = 1 THEN '' ELSE 'true' END AS closed,
                ETD.EmergencyTeamDuty_isComesToWork,
				CONVERT(varchar, ETD.EmergencyTeamDuty_DTStart, 120) AS EmergencyTeamDuty_DTStart,
                CONVERT(varchar, ETD.EmergencyTeamDuty_DTFinish, 120) AS EmergencyTeamDuty_DTFinish,
                CONVERT( varchar, CAST( ETD.EmergencyTeamDuty_factToWorkDT as datetime ), 120 ) as EmergencyTeamDuty_factToWorkDT,
				CONVERT( varchar, CAST( ETD.EmergencyTeamDuty_factEndWorkDT as datetime ), 120 ) as EmergencyTeamDuty_factEndWorkDT,
				ETD.EmergencyTeamDuty_Comm,
				ETD.EmergencyTeamDuty_ChangeComm
			FROM
			    v_EmergencyTeam as ET with (nolock)
			    LEFT JOIN v_EmergencyTeamStatus AS ETS with (nolock) ON( ETS.EmergencyTeamStatus_id=ET.EmergencyTeamStatus_id )
				LEFT JOIN v_EmergencyTeamDuty AS ETD with (nolock) ON( ETD.EmergencyTeam_id=ET.EmergencyTeam_id)
				LEFT JOIN v_LpuBuilding LB with (nolock) on LB.LpuBuilding_id = ET.LpuBuilding_id
			".ImplodeWherePH( $where )."
				";

        $query = $this->db->query( $sql, $params );
        if ( is_object( $query ) ) {
            return $query->result_array();
        }
        return false;
    }
    /**
     * Возвращает список бригад которым пора со смены (закончилось плановое время)
     * @param $data
     * @param $where
     *
     */
    private function getAutoFinishVigil( $data, $where, $params){

        $where[] = "COALESCE(ETD.EmergencyTeamDuty_IsCancelledClose, 1) = 1";
        $where[] = "COALESCE(ETD.EmergencyTeamDuty_isClose, 1) = 1";
        $where[] = "COALESCE(ETD.EmergencyTeamDuty_isComesToWork, 1) = 2";
        $where[] = "ETD.EmergencyTeamDuty_DTFinish <= @datetime";
        $where[] = "ISNULL(ETD.EmergencyTeamDuty_factEndWorkDT, 1) = 1";
        //не будем выгребать все бригады, их слишком много
        $where[] = "DATEDIFF(hour, ETD.EmergencyTeamDuty_DTFinish, dbo.tzGetDate())<24";
        $where[] = "ETS.EmergencyTeamStatus_Code IN(13,19,21)";

        $sql = "
            declare @datetime datetime = dbo.tzGetDate()
            SELECT
                ET.EmergencyTeam_id,
                ETD.EmergencyTeamDuty_id,
                ETD.EmergencyTeamDuty_isClose,
                CASE WHEN COALESCE(ETD.EmergencyTeamDuty_isClose,1) = 1 THEN '' ELSE 'true' END AS closed,
                ETD.EmergencyTeamDuty_isComesToWork,
				CONVERT(varchar, ETD.EmergencyTeamDuty_DTStart, 120) AS EmergencyTeamDuty_DTStart,
                CONVERT(varchar, ETD.EmergencyTeamDuty_DTFinish, 120) AS EmergencyTeamDuty_DTFinish,
                CONVERT( varchar, CAST( ETD.EmergencyTeamDuty_factToWorkDT as datetime ), 120 ) as EmergencyTeamDuty_factToWorkDT,
				CONVERT( varchar, CAST( ETD.EmergencyTeamDuty_factEndWorkDT as datetime ), 120 ) as EmergencyTeamDuty_factEndWorkDT,
				ETD.EmergencyTeamDuty_Comm,
				ETD.EmergencyTeamDuty_ChangeComm
			FROM
			    v_EmergencyTeam as ET with (nolock)
			    LEFT JOIN v_EmergencyTeamStatus AS ETS with (nolock) ON( ETS.EmergencyTeamStatus_id=ET.EmergencyTeamStatus_id )
				LEFT JOIN v_EmergencyTeamDuty AS ETD with (nolock) ON( ETD.EmergencyTeam_id=ET.EmergencyTeam_id)
				LEFT JOIN v_LpuBuilding LB with (nolock) on LB.LpuBuilding_id = ET.LpuBuilding_id
			".ImplodeWherePH( $where )."
				";

        //var_dump(getDebugSQL($sql, $params )); exit;
        $query = $this->db->query( $sql, $params );
        if ( is_object( $query ) ) {
            return $query->result_array();
        }
        return false;
    }
	
	/**
	 * Возвращает данные по оперативной обстановке бригад СМП
	 * Для списка подчиненных подстанций СМП
	 *
	 * @param array $data
	 * @return array or false
	 */
	public function loadEmergencyTeamOperEnvForSmpUnitsNested( $data ) {
		$where = array();
		$params = array();

		$regionNick = getRegionNick();

		$CurArmType = (!empty($data['session']['CurArmType']) ? $data['session']['CurArmType'] : '');
		
		//Получаем наименование полей и объектов геосервиса в зависимости от региона (и, возможно, в будущем - МО)
		$GTR = $this->_defineGeoserviceTransportRelQueryParams($data);

		$where[] = "COALESCE(ET.EmergencyTeam_isTemplate,1)=1";

		// Вышел на смену
		$where[] = "ETD.EmergencyTeamDuty_isComesToWork = 2";
		$where[] = "ETD.EmergencyTeamDuty_isClose = 1";
		//$where[] = "dbo.tzGetDate() BETWEEN ETD.EmergencyTeamDuty_factToWorkDT AND ETD.EmergencyTeamDuty_DTFinish ";
		$where[] = "
			( ISNULL(ETD.EmergencyTeamDuty_factToWorkDT, @date) <= @date
				and ISNULL(ETD.EmergencyTeamDuty_factEndWorkDT, @date) >= @date )
		";
		
		// здесь мы получаем список доступных подстанций для работы
		$where[] = $this->getNestedLpuBuildingsForRequests($data);

		$lastCallOnTeamApply = "";
		$checkInAddress = "'' as lastCheckinAddress,";
		if ( in_array($regionNick, array('perm')) && !in_array($CurArmType, array('dispnmp', 'dispdirnmp')) ) {
			//из за СМП Перми не будем тормозить другие регионы

			$checkInAddress = "
				CASE WHEN (ETS.EmergencyTeamStatus_Code IN(21) OR lastCallOnTeam.CmpCallCard_id IS NULL) THEN
					case when LBSRGNCity.KLSubRgn_Name is not null then LBSRGNCity.KLSocr_Nick+' '+LBSRGNCity.KLSubRgn_Name+', '
						else case when LBSRGNTown.KLSubRgn_Name is not null then LBSRGNTown.KLSocr_Nick+' '+LBSRGNTown.KLSubRgn_Name+', '
						else case when LBSRGN.KLSubRgn_Name is not null then LBSRGN.KLSocr_Nick+' '+LBSRGN.KLSubRgn_Name+', ' else '' end end end+
						case when LBCity.KLCity_Name is not null then 'г. '+LBCity.KLCity_Name else '' end+
						case when LBTown.KLTown_FullName is not null then
							case when LBCity.KLCity_Name is not null then ', ' else '' end
							 +isnull(LOWER(LBTown.KLSocr_Nick)+'. ','') + LBTown.KLTown_Name else ''
						end+

						case when LBStreet.KLStreet_FullName is not null then
							case when LBStreet.KLSocr_Nick is not null then ', '+LOWER(LBStreet.KLSocr_Nick)+'. '+LBStreet.KLStreet_Name else
							', '+LBStreet.KLStreet_FullName  end
						else ''
						end +

						case when LBAddress.Address_House is not null then ', д.'+LBAddress.Address_House else '' end +
						case when LBAddress.Address_Corpus is not null then ', к.'+LBAddress.Address_Corpus else '' end +
						case when LBAddress.Address_Flat is not null then ', кв.'+LBAddress.Address_Flat else '' end
				ELSE
					lastCallOnTeam.Adress_Name
				END as lastCheckinAddress,
			";

			$lastCallOnTeamApply = "
				outer apply (
					select top 1
						c.CmpCallCard_id
						,case when SRGNCity.KLSubRgn_Name is not null then SRGNCity.KLSocr_Nick+' '+SRGNCity.KLSubRgn_Name+', '
						else case when SRGNTown.KLSubRgn_Name is not null then SRGNTown.KLSocr_Nick+' '+SRGNTown.KLSubRgn_Name+', '
						else case when SRGN.KLSubRgn_Name is not null then SRGN.KLSocr_Nick+' '+SRGN.KLSubRgn_Name+', ' else '' end end end+
						case when City.KLCity_Name is not null then 'г. '+City.KLCity_Name else '' end+
						case when Town.KLTown_FullName is not null then
							case when City.KLCity_Name is not null then ', ' else '' end
							 +isnull(LOWER(Town.KLSocr_Nick)+'. ','') + Town.KLTown_Name else ''
						end+

						case when Street.KLStreet_FullName is not null then
							case when Street.KLSocr_Nick is not null then ', '+LOWER(Street.KLSocr_Nick)+'. '+Street.KLStreet_Name else
							', '+Street.KLStreet_FullName  end
						else case when c.CmpCallCard_Ulic is not null then ', '+c.CmpCallCard_Ulic else '' end
						end +

						case when SecondStreet.KLStreet_FullName is not null then
							case when SecondStreet.KLSocr_Nick is not null then ', '+LOWER(SecondStreet.KLSocr_Nick)+'. '+SecondStreet.KLStreet_Name else
							', '+SecondStreet.KLStreet_FullName end
							else ''
						end +

						case when c.CmpCallCard_Dom is not null then ', д.'+c.CmpCallCard_Dom else '' end +
						case when c.CmpCallCard_Korp is not null then ', к.'+c.CmpCallCard_Korp else '' end +
						case when c.CmpCallCard_Kvar is not null then ', кв.'+c.CmpCallCard_Kvar else '' end +
						case when c.CmpCallCard_Room is not null then ', ком. '+c.CmpCallCard_Room else '' end +
						case when UAD.UnformalizedAddressDirectory_Name is not null then ', Место: '+UAD.UnformalizedAddressDirectory_Name else '' end as Adress_Name
					from v_CmpCallCardTeamsAssignmentHistory cctah with (nolock)
					left join v_CmpCallCard c with (nolock) on cctah.CmpCallCard_id = c.CmpCallCard_id
					outer apply (
						select top 1 * from v_EmergencyTeamStatusHistory (nolock) WHERE EmergencyTeam_id = c.EmergencyTeam_id and CmpCallCard_id = c.CmpCallCard_id order by EmergencyTeamStatusHistory_id desc
						) as lastETSH
					left join v_EmergencyTeamStatus lastCallETS with (nolock) on lastETSH.EmergencyTeamStatus_id = lastCallETS.EmergencyTeamStatus_id
					left join v_KLRgn RGN with(nolock) on RGN.KLRgn_id = c.KLRgn_id
					left join v_KLRgn RGNCity with(nolock) on RGNCity.KLRgn_id = c.KLCity_id
					left join v_KLSubRgn SRGN with(nolock) on SRGN.KLSubRgn_id = c.KLSubRgn_id
					left join v_KLCity City with(nolock) on City.KLCity_id = c.KLCity_id
					left join v_KLTown Town with(nolock) on Town.KLTown_id = c.KLTown_id
					left join v_KLSubRgn SRGNTown with(nolock) on SRGNTown.KLSubRgn_id = c.KLTown_id
					left join v_KLSubRgn SRGNCity with(nolock) on SRGNCity.KLSubRgn_id = c.KLCity_id
					left join v_KLStreet Street with(nolock) on Street.KLStreet_id = c.KLStreet_id
					left join v_UnformalizedAddressDirectory UAD with(nolock) on UAD.UnformalizedAddressDirectory_id = c.UnformalizedAddressDirectory_id
					left join v_KLStreet SecondStreet (nolock) on SecondStreet.KLStreet_id = c.CmpCallCard_UlicSecond
					where c.EmergencyTeam_id = ET.EmergencyTeam_id and lastCallETS.EmergencyTeamStatus_Code != 36
					order by CmpCallCardTeamsAssignmentHistory_id desc
				) lastCallOnTeam
				LEFT JOIN v_Address LBAddress with(nolock) on LB.Address_id = LBAddress.Address_id
				left join v_KLRgn LBRGN with(nolock) on LBRGN.KLRgn_id = LBAddress.KLRgn_id
				left join v_KLRgn LBRGNCity with(nolock) on LBRGNCity.KLRgn_id = LBAddress.KLCity_id
				left join v_KLSubRgn LBSRGN with(nolock) on LBSRGN.KLSubRgn_id = LBAddress.KLSubRgn_id
				left join v_KLCity LBCity with(nolock) on LBCity.KLCity_id = LBAddress.KLCity_id
				left join v_KLTown LBTown with(nolock) on LBTown.KLTown_id = LBAddress.KLTown_id
				left join v_KLSubRgn LBSRGNTown with(nolock) on LBSRGNTown.KLSubRgn_id = LBAddress.KLTown_id
				left join v_KLSubRgn LBSRGNCity with(nolock) on LBSRGNCity.KLSubRgn_id = LBAddress.KLCity_id
				left join v_KLStreet LBStreet with(nolock) on LBStreet.KLStreet_id = LBAddress.KLStreet_id
			";
		}

		$sql = "
		    DECLARE @date DATETIME = dbo.tzGetDate()
			SELECT DISTINCT
				-- select
				ET.EmergencyTeam_id,
				ET.EmergencyTeam_Num,
				ET.EmergencyTeam_CarNum,
				ET.EmergencyTeam_CarBrand,
				ET.EmergencyTeam_CarModel,
				ET.EmergencyTeam_PortRadioNum,
				ET.EmergencyTeam_GpsNum,
				ET.LpuBuilding_id,
				ET.EmergencyTeamStatus_id,
				ETS.EmergencyTeamStatus_Code,
				ET.EmergencyTeamSpec_id,
				
				CONVERT( varchar, CAST( ETD.EmergencyTeamDuty_DTStart as datetime ), 120 ) as EmergencyTeamDuty_DTStart,
				CONVERT( varchar, CAST( ETD.EmergencyTeamDuty_DTFinish as datetime ), 120 ) as EmergencyTeamDuty_DTFinish,
				ETD.EmergencyTeamDuty_id,
				DATEDIFF(minute, ETLastStatusDateTime.EmergencyTeamStatusHistory_insDT, @date) as lastChangedStatusTime,
				--время от конца смены планового до конца выхода смены фактической
				CASE WHEN (
					DATEDIFF(minute, @date, ETD.EmergencyTeamDuty_DTFinish ) < 0
					AND ETS.EmergencyTeamStatus_Code IN(1,2,3,17,48)
					)
					THEN 2
					ELSE 1
				END
				as isOverTime,
				CASE
					WHEN @date BETWEEN ETD.EmergencyTeamDuty_factToWorkDT AND ETD.EmergencyTeamDuty_DTFinish THEN 0 ELSE 1
				END as EmergencyTeamDuty_isNotFact,
				ETSpec.EmergencyTeamSpec_Name,
				ETSpec.EmergencyTeamSpec_Code,
				CC.CmpCallCard_id,
				CC.CmpCallCard_Numv,
				COALESCE(CC.Lpu_hid,lsL.Lpu_id) as Lpu_hid,
				COALESCE(lpuHid.Lpu_Nick,lsL.Lpu_Nick) as LpuHid_Nick,
				
				CC.CmpCallCard_Ngod,
				CASE WHEN COALESCE(ET.EmergencyTeam_isOnline,1)=2 THEN 'online' ELSE 'offline' END as EmergencyTeam_isOnline,

				( CASE WHEN COALESCE(ET.EmergencyTeam_HeadShift2,0)!=0 THEN 1 ELSE 0 END )
				+ ( CASE WHEN COALESCE(ET.EmergencyTeam_Assistant1,0)!=0 THEN 1 ELSE 0 END )
				+ ( CASE WHEN COALESCE(ET.EmergencyTeam_Assistant2,0)!=0 THEN 1 ELSE 0 END ) as medPersonCount,

				ETS.EmergencyTeamStatus_Name,
				CASE
					WHEN ETS.EmergencyTeamStatus_Code IN(3,14) THEN 'red'
					WHEN ETS.EmergencyTeamStatus_Code IN(5,13,21,47) THEN 'blue'
					WHEN ETS.EmergencyTeamStatus_Code IN(8,23) THEN 'green'
					ELSE 'black'
				END as EmergencyTeamStatus_Color,
				MP.Person_Fin,
				{$checkInAddress}
				case when LB.LpuBuilding_Nick is not null then LB.LpuBuilding_Nick else LB.LpuBuilding_Name end as EmergencyTeamBuildingName,
				COALESCE(GTR.{$GTR['GeoserviceTransport_id_field']}, MPC.MedProductCard_Glonass) as GeoserviceTransport_id

				--MS.MedService_id
				-- end select
			FROM
				-- from
				v_EmergencyTeam as ET with (nolock)
				LEFT JOIN v_EmergencyTeamStatus AS ETS with (nolock) ON( ETS.EmergencyTeamStatus_id=ET.EmergencyTeamStatus_id )
				--LEFT JOIN v_EmergencyTeamStatusHistory as ETSH with (nolock) ON( ETS.EmergencyTeamStatus_id=ETSH.EmergencyTeamStatus_id )
				LEFT JOIN v_EmergencyTeamDuty AS ETD with (nolock) ON( ETD.EmergencyTeam_id=ET.EmergencyTeam_id)
				LEFT JOIN v_MedPersonal as MP with(nolock) ON( MP.MedPersonal_id=ET.EmergencyTeam_HeadShift )
				LEFT JOIN v_EmergencyTeamSpec as ETSpec with(nolock) ON(ET.EmergencyTeamSpec_id = ETSpec.EmergencyTeamSpec_id)
				outer apply (
					SELECT TOP 1 C2.* 
					FROM v_CmpCallCard as C2 with(nolock)
					WHERE 
						C2.EmergencyTeam_id = ET.EmergencyTeam_id 
						AND C2.CmpCallCardStatusType_id = 2
					ORDER BY C2.CmpCallCard_updDT DESC
				) CC
				outer apply (
					SELECT TOP 1 *
					FROM v_EmergencyTeamStatusHistory as EmergTeamStatHistory with(nolock)
					WHERE 
						EmergTeamStatHistory.EmergencyTeam_id = ET.EmergencyTeam_id					
					ORDER BY EmergTeamStatHistory.EmergencyTeamStatusHistory_insDT DESC
				) as ETLastStatusDateTime
				LEFT JOIN {$GTR['GeoserviceTransportRel_object']} as GTR with (nolock) ON( GTR.{$GTR['EmergencyTeam_id_field']}=ET.EmergencyTeam_id )
				LEFT JOIN passport.v_MedProductCard MPC with (nolock) on MPC.MedProductCard_id = ET.MedProductCard_id
				LEFT JOIN v_LpuBuilding LB with (nolock) on LB.LpuBuilding_id = ET.LpuBuilding_id
				left join v_Lpu lpuHid with (nolock) on CC.Lpu_hid = lpuHid.Lpu_id
				left join v_Lpu lsL with (nolock) on lsL.Lpu_id = LB.Lpu_id
				{$lastCallOnTeamApply}
				--LEFT JOIN v_MedService MS with (nolock) on MS.LpuBuilding_id = LB.LpuBuilding_id
				-- end from
			".ImplodeWherePH( $where )."
			ORDER BY
				-- order by
				EmergencyTeam_Num
				-- end order by
		";
	
		//var_dump(getDebugSQL($sql, $params));
		if (isset($_GET['dbg']) && $_GET['dbg'] == '1') 
			var_dump(getDebugSQL($sql, $params));
		
		$query = $this->db->query( $sql, $params );
		if ( is_object( $query ) ) {
			return $query->result_array();
		}
		return false;
	}

	/**
	 * Список бригад для АРМ Интерактивной карты СМП
	 * @param $data
	 * @return bool
	 */
	public function loadEmergencyTeamOperEnvForInteractiveMap($data) {
		$where = array();
		$params = array();

		//Получаем наименование полей и объектов геосервиса в зависимости от региона (и, возможно, в будущем - МО)
		$GTR = $this->_defineGeoserviceTransportRelQueryParams($data);

		// Вышел на смену
		$where[] = "ETD.EmergencyTeamDuty_isComesToWork=2";
		//$where[] = "dbo.tzGetDate() BETWEEN ETD.EmergencyTeamDuty_factToWorkDT AND ETD.EmergencyTeamDuty_DTFinish ";
		$where[] = "
			( ISNULL(ETD.EmergencyTeamDuty_factToWorkDT, @date) <= @date
				and ISNULL(ETD.EmergencyTeamDuty_factEndWorkDT, @date) >= @date )
		";

		if (!empty($data['EmergencyTeamStatus_id'])) {
			$params['EmergencyTeamStatus_id'] = $data['EmergencyTeamStatus_id'];
			$where[] = 'ET.EmergencyTeamStatus_id = :EmergencyTeamStatus_id';

		}

		// здесь мы получаем список доступных подстанций для работы
		$where[] = $this->getNestedLpuBuildingsForRequests($data);

		$sql = "
		    DECLARE @date DATETIME = dbo.tzGetDate()
			SELECT DISTINCT
				-- select
				ET.EmergencyTeam_id,
				ET.EmergencyTeam_Num,
				
				--ET.LpuBuilding_id,
				ET.EmergencyTeamStatus_id,
				ETS.EmergencyTeamStatus_Code,
				ET.EmergencyTeamSpec_id,
				
				CONVERT( varchar, CAST( ETD.EmergencyTeamDuty_DTStart as datetime ), 120 ) as EmergencyTeamDuty_DTStart,
				CONVERT( varchar, CAST( ETD.EmergencyTeamDuty_DTFinish as datetime ), 120 ) as EmergencyTeamDuty_DTFinish,
				ETD.EmergencyTeamDuty_id,
				DATEDIFF(minute, ETLastStatusDateTime.EmergencyTeamStatusHistory_insDT, @date) as lastChangedStatusTime,
				--время от конца смены планового до конца выхода смены фактической
				CASE WHEN (
					DATEDIFF(minute, @date, ETD.EmergencyTeamDuty_DTFinish ) < 0
					AND ETS.EmergencyTeamStatus_Code IN(1,2,3,17,48)
					)
					THEN 2
					ELSE 1
				END
				as isOverTime, 
				ETSpec.EmergencyTeamSpec_Name,
				ETSpec.EmergencyTeamSpec_Code,
				CC.CmpCallCard_id,
				CC.CmpCallCard_Numv,
				COALESCE(CC.Lpu_hid,lsL.Lpu_id) as Lpu_hid,
				COALESCE(lpuHid.Lpu_Nick,lsL.Lpu_Nick) as LpuHid_Nick,
				lpuHid.PAddress_Address as LpuHid_PAddress,
				
				CC.CmpCallCard_Ngod,
				CASE WHEN COALESCE(ET.EmergencyTeam_isOnline,1)=2 THEN 'online' ELSE 'offline' END as EmergencyTeam_isOnline,

				( CASE WHEN COALESCE(ET.EmergencyTeam_HeadShift2,0)!=0 THEN 1 ELSE 0 END )
				+ ( CASE WHEN COALESCE(ET.EmergencyTeam_Assistant1,0)!=0 THEN 1 ELSE 0 END )
				+ ( CASE WHEN COALESCE(ET.EmergencyTeam_Assistant2,0)!=0 THEN 1 ELSE 0 END ) as medPersonCount,

				ETS.EmergencyTeamStatus_Name,
				CASE
					WHEN ETS.EmergencyTeamStatus_Code IN(3,14) THEN 'red'
					WHEN ETS.EmergencyTeamStatus_Code IN(5,13,21,47) THEN 'blue'
					WHEN ETS.EmergencyTeamStatus_Code IN(8,23) THEN 'green'
					ELSE 'black'
				END as EmergencyTeamStatus_Color,
				MP.Person_Fin,
				case when LB.LpuBuilding_Nick is not null then LB.LpuBuilding_Nick else LB.LpuBuilding_Name end as EmergencyTeamBuildingName,
				GTR.{$GTR['GeoserviceTransport_id_field']} as GeoserviceTransport_id

				--MS.MedService_id
				-- end select
			FROM
				-- from
				v_EmergencyTeam as ET with (nolock)
				LEFT JOIN v_EmergencyTeamStatus AS ETS with (nolock) ON( ETS.EmergencyTeamStatus_id=ET.EmergencyTeamStatus_id )
				--LEFT JOIN v_EmergencyTeamStatusHistory as ETSH with (nolock) ON( ETS.EmergencyTeamStatus_id=ETSH.EmergencyTeamStatus_id )
				LEFT JOIN v_EmergencyTeamDuty AS ETD with (nolock) ON( ETD.EmergencyTeam_id=ET.EmergencyTeam_id)
				LEFT JOIN v_MedPersonal as MP with(nolock) ON( MP.MedPersonal_id=ET.EmergencyTeam_HeadShift )
				LEFT JOIN v_EmergencyTeamSpec as ETSpec with(nolock) ON(ET.EmergencyTeamSpec_id = ETSpec.EmergencyTeamSpec_id)
				outer apply (
					SELECT TOP 1 C2.* 
					FROM v_CmpCallCard as C2 with(nolock)
					WHERE 
						C2.EmergencyTeam_id = ET.EmergencyTeam_id 
						AND C2.CmpCallCardStatusType_id = 2
					ORDER BY C2.CmpCallCard_updDT DESC
				) CC
				outer apply (
					SELECT TOP 1 *
					FROM v_EmergencyTeamStatusHistory as EmergTeamStatHistory with(nolock)
					WHERE 
						EmergTeamStatHistory.EmergencyTeam_id = ET.EmergencyTeam_id					
					ORDER BY EmergTeamStatHistory.EmergencyTeamStatusHistory_insDT DESC
				) as ETLastStatusDateTime
				LEFT JOIN {$GTR['GeoserviceTransportRel_object']} as GTR with (nolock) ON( GTR.{$GTR['EmergencyTeam_id_field']}=ET.EmergencyTeam_id )
				LEFT JOIN v_LpuBuilding LB with (nolock) on LB.LpuBuilding_id = ET.LpuBuilding_id
				left join v_Lpu lpuHid with (nolock) on CC.Lpu_hid = lpuHid.Lpu_id
				left join v_Lpu lsL with (nolock) on lsL.Lpu_id = LB.Lpu_id
				--LEFT JOIN v_MedService MS with (nolock) on MS.LpuBuilding_id = LB.LpuBuilding_id
				-- end from
			".ImplodeWherePH( $where )."
			ORDER BY
				-- order by
				EmergencyTeam_Num
				-- end order by
		";

		//var_dump(getDebugSQL($sql, $params));
		if (isset($_GET['dbg']) && $_GET['dbg'] == '1')
			print_r(getDebugSQL($sql, $params));

		$query = $this->db->query( $sql, $params );
		if ( is_object( $query ) ) {
			return $query->result_array();
		}
		return false;
	}
	/**
	 * Сохраняет время смен бригад
	 *
	 * @param array $data
	 * @return bool
	 */
	public function saveEmergencyTeamDutyTime( $data ) {
		if ( !array_key_exists( 'EmergencyTeamsDutyTimes', $data ) ) {
			return array( array( 'Error_Msg' => 'Отсутствуют необходимые данные.' ) );
		}

		$is_pg = $this->db->dbdriver == 'postgre' ? true : false;
		$return = array();
		$has_errors = false;
		$arr = json_decode( $data[ 'EmergencyTeamsDutyTimes' ],true );
		foreach( (array)$arr as $key => $value ){

			if(!empty( $value["EmergencyTeamDuty_id"] )){
				if($is_pg){
					$sqlETD = "
					SELECT TOP 1
							\"EmergencyTeamDuty_isComesToWork\",
							CONVERT( varchar, CAST( ETD.\"EmergencyTeamDuty_factToWorkDT\" as datetime ), 120 ) as \"EmergencyTeamDuty_factToWorkDT\",
							CONVERT( varchar, CAST( ETD.\"EmergencyTeamDuty_factEndWorkDT\" as datetime ), 120 ) as \"EmergencyTeamDuty_factEndWorkDT\",
							\"EmergencyTeamDuty_isClose\",
							CONVERT( varchar, CAST( ETD.\"EmergencyTeamDuty_comesToWorkDT\" as datetime ), 120 ) as \"EmergencyTeamDuty_comesToWorkDT\"
					FROM
							dbo.\"EmergencyTeamDuty\" etd
					WHERE
							\"EmergencyTeamDuty_id\" = :EmergencyTeamDuty_id
					";
				}else{
					$sqlETD = "
					SELECT TOP 1
							EmergencyTeamDuty_isComesToWork,
							EmergencyTeamDuty_Comm,
							CONVERT( varchar, CAST( ETD.EmergencyTeamDuty_factToWorkDT as datetime ), 120 ) as EmergencyTeamDuty_factToWorkDT,
							CONVERT( varchar, CAST( ETD.EmergencyTeamDuty_factEndWorkDT as datetime ), 120 ) as EmergencyTeamDuty_factEndWorkDT,
							EmergencyTeamDuty_isClose,
							CONVERT( varchar, CAST( ETD.EmergencyTeamDuty_comesToWorkDT as datetime ), 120 ) as EmergencyTeamDuty_comesToWorkDT
					FROM
							v_EmergencyTeamDuty ETD
					WHERE
							EmergencyTeamDuty_id = :EmergencyTeamDuty_id
					";
				}

				$query = $this->db->query( $sqlETD, array('EmergencyTeamDuty_id' => $value["EmergencyTeamDuty_id"]) );
				if ( is_object( $query ) ) {
					$resp = $query->result('array');
					$value = array_merge($value, $resp[0]);
				} else {
					$has_errors = true;
				}


			}

			$sqlArr = array(
				'EmergencyTeamDuty_DTStart' => $value["EmergencyTeamDuty_DTStart"] ,
				'EmergencyTeamDuty_DTFinish' => $value["EmergencyTeamDuty_DTFinish"] ,
				'EmergencyTeam_id' => $value["EmergencyTeam_id"],
				'EmergencyTeamDuty_ChangeComm' => !empty( $value["EmergencyTeamDuty_ChangeComm"] ) ? $value["EmergencyTeamDuty_ChangeComm"] : null,
				'EmergencyTeamDuty_Comm' => !empty( $value["EmergencyTeamDuty_Comm"] ) ? $value["EmergencyTeamDuty_Comm"] : null,
				'EmergencyTeamDuty_isComesToWork' => !empty( $value["EmergencyTeamDuty_isComesToWork"] ) ? $value["EmergencyTeamDuty_isComesToWork"] : null,
				'EmergencyTeamDuty_isClose' => !empty( $value["EmergencyTeamDuty_isClose"] ) ? $value["EmergencyTeamDuty_isClose"] : null,
				'EmergencyTeamDuty_comesToWorkDT' => !empty( $value["EmergencyTeamDuty_comesToWorkDT"] ) ? $value["EmergencyTeamDuty_comesToWorkDT"] : null,
				'EmergencyTeamDuty_factToWorkDT' => !empty( $value["EmergencyTeamDuty_factToWorkDT"] ) ? $value["EmergencyTeamDuty_factToWorkDT"] : null,
				'EmergencyTeamDuty_factEndWorkDT' => !empty( $value["EmergencyTeamDuty_factEndWorkDT"] ) ? $value["EmergencyTeamDuty_factEndWorkDT"] : null,
				'EmergencyTeam_Head1StartTime' => !empty( $value["EmergencyTeam_Head1StartTime"] ) ? $value["EmergencyTeam_Head1StartTime"] : null,
				'EmergencyTeam_Head1FinishTime' => !empty( $value["EmergencyTeam_Head1FinishTime"] ) ? $value["EmergencyTeam_Head1FinishTime"] : null,
				'EmergencyTeam_Head2StartTime' => !empty( $value["EmergencyTeam_Head2StartTime"] ) ? $value["EmergencyTeam_Head2StartTime"] : null,
				'EmergencyTeam_Head2FinishTime' => !empty( $value["EmergencyTeam_Head2FinishTime"] ) ? $value["EmergencyTeam_Head2FinishTime"] : null,
				'EmergencyTeam_Assistant1StartTime' => !empty( $value["EmergencyTeam_Assistant1StartTime"] ) ? $value["EmergencyTeam_Assistant1StartTime"] : null,
				'EmergencyTeam_Assistant1FinishTime' => !empty( $value["EmergencyTeam_Assistant1FinishTime"] ) ? $value["EmergencyTeam_Assistant1FinishTime"] : null,
				'EmergencyTeam_Assistant2StartTime' => !empty( $value["EmergencyTeam_Assistant2StartTime"] ) ? $value["EmergencyTeam_Assistant2StartTime"] : null,
				'EmergencyTeam_Assistant2FinishTime' => !empty( $value["EmergencyTeam_Assistant2FinishTime"] ) ? $value["EmergencyTeam_Assistant2FinishTime"] : null,
				'EmergencyTeam_Driver1StartTime' => !empty( $value["EmergencyTeam_Driver1StartTime"] ) ? $value["EmergencyTeam_Driver1StartTime"] : null,
				'EmergencyTeam_Driver1FinishTime' => !empty( $value["EmergencyTeam_Driver1FinishTime"] ) ? $value["EmergencyTeam_Driver1FinishTime"] : null,
				'EmergencyTeam_Driver2StartTime' => !empty( $value["EmergencyTeam_Driver2StartTime"] ) ? $value["EmergencyTeam_Driver2StartTime"] : null,
				'EmergencyTeam_Driver2FinishTime' => !empty( $value["EmergencyTeam_Driver2FinishTime"] ) ? $value["EmergencyTeam_Driver2FinishTime"] : null,
				'EmergencyTeamDuty_IsCancelledStart' => !empty( $value["EmergencyTeamDuty_IsCancelledStart"] ) ? $value["EmergencyTeamDuty_IsCancelledStart"] : null,
				'EmergencyTeamDuty_IsCancelledClose' => !empty( $value["EmergencyTeamDuty_IsCancelledClose"] ) ? $value["EmergencyTeamDuty_IsCancelledClose"] : null,
				'pmUser_id' => $data[ 'pmUser_id' ]
			);
			
			//if ($sqlArr['EmergencyTeamDuty_DTFinish'] !== false && $sqlArr['EmergencyTeamDuty_DTStart'] >= $sqlArr['EmergencyTeamDuty_DTFinish']) 
				//$sqlArr['EmergencyTeamDuty_DTFinish'] = $sqlArr['EmergencyTeamDuty_DTFinish']->modify("+1 day");
			
			if ( $is_pg ) {
				if ( !isset( $value["EmergencyTeamDuty_id"] ) || !$value["EmergencyTeamDuty_id"] ) {
					$sql = "
						INSERT INTO dbo.\"EmergencyTeamDuty\" (
							\"EmergencyTeamDuty_DTStart\",
							\"EmergencyTeamDuty_DTFinish\",
							\"EmergencyTeam_id\",
							\"EmergencyTeamDuty_ChangeComm\",
							\"EmergencyTeamDuty_isComesToWork\",
							\"EmergencyTeamDuty_comesToWorkDT\",
							\"EmergencyTeamDuty_factToWorkDT\",
							\"EmergencyTeam_Head1StartTime\",
							\"EmergencyTeam_Head1FinishTime\",
							\"EmergencyTeam_Head2StartTime\",
							\"EmergencyTeam_Head2FinishTime\",
							\"EmergencyTeam_Assistant1StartTime\",
							\"EmergencyTeam_Assistant1FinishTime\",
							\"EmergencyTeam_Assistant2StartTime\",
							\"EmergencyTeam_Assistant2FinishTime\",
							\"EmergencyTeam_Driver1FinishTime\",
							\"EmergencyTeam_Driver1StartTime\",
							\"EmergencyTeam_Driver2StartTime\",
							\"EmergencyTeam_Driver2FinishTime\",
							\"pmUser_insID\",
							\"pmUser_updID\",
							\"EmergencyTeamDuty_insDT\",
							\"EmergencyTeamDuty_updDT\",
							\"EmergencyTeamDuty_IsCancelledStart\",
							\"EmergencyTeamDuty_IsCancelledClose\"
						) VALUES (
							:EmergencyTeamDuty_DTStart,
							:EmergencyTeamDuty_DTFinish,
							:EmergencyTeam_id,
							:EmergencyTeamDuty_ChangeComm,
							:EmergencyTeamDuty_isComesToWork,
							-- Сохраняем время для timestamp. Если кто знает способ лучше - you are welcome
							-- 1900-01-01 это из-за того что, так MSSQL сохраняет строку 'ЧЧ:ММ'
							to_timestamp('1900-01-01 ' || :EmergencyTeamDuty_comesToWorkDT,'YYYY-MM-DD HH24:MI')::timestamp,
							to_timestamp('1900-01-01 ' || :EmergencyTeamDuty_factToWorkDT,'YYYY-MM-DD HH24:MI')::timestamp,
							to_timestamp('1900-01-01 ' || :EmergencyTeam_Head1StartTime,'YYYY-MM-DD HH24:MI')::timestamp,
							to_timestamp('1900-01-01 ' || :EmergencyTeam_Head1FinishTime,'YYYY-MM-DD HH24:MI')::timestamp,
							to_timestamp('1900-01-01 ' || :EmergencyTeam_Head2StartTime,'YYYY-MM-DD HH24:MI')::timestamp,
							to_timestamp('1900-01-01 ' || :EmergencyTeam_Head2FinishTime,'YYYY-MM-DD HH24:MI')::timestamp,
							to_timestamp('1900-01-01 ' || :EmergencyTeam_Assistant1StartTime,'YYYY-MM-DD HH24:MI')::timestamp,
							to_timestamp('1900-01-01 ' || :EmergencyTeam_Assistant1FinishTime,'YYYY-MM-DD HH24:MI')::timestamp,
							to_timestamp('1900-01-01 ' || :EmergencyTeam_Assistant2StartTime,'YYYY-MM-DD HH24:MI')::timestamp,
							to_timestamp('1900-01-01 ' || :EmergencyTeam_Assistant2FinishTime,'YYYY-MM-DD HH24:MI')::timestamp,
							to_timestamp('1900-01-01 ' || :EmergencyTeam_Driver1FinishTime,'YYYY-MM-DD HH24:MI')::timestamp,
							to_timestamp('1900-01-01 ' || :EmergencyTeam_Driver1StartTime,'YYYY-MM-DD HH24:MI')::timestamp,
							to_timestamp('1900-01-01 ' || :EmergencyTeam_Driver2StartTime,'YYYY-MM-DD HH24:MI')::timestamp,
							to_timestamp('1900-01-01 ' || :EmergencyTeam_Driver2FinishTime,'YYYY-MM-DD HH24:MI')::timestamp,
							:pmUser_id,
							:pmUser_id,
							NOW(),
							NOW()
						) RETURNING \"EmergencyTeamDuty_id\", null as \"Error_Code\", null as \"Error_Msg\";
					";
				} else {
					$sqlArr['EmergencyTeamDuty_id'] = (int)$value["EmergencyTeamDuty_id"];
					$sql = "
						UPDATE dbo.\"EmergencyTeamDuty\" SET
							\"EmergencyTeamDuty_DTStart\" = :EmergencyTeamDuty_DTStart,
							\"EmergencyTeamDuty_DTFinish\" = :EmergencyTeamDuty_DTFinish,
							\"EmergencyTeam_id\" = :EmergencyTeam_id,
							\"EmergencyTeamDuty_ChangeComm\" = :EmergencyTeamDuty_ChangeComm,
							\"EmergencyTeamDuty_isComesToWork\" = :EmergencyTeamDuty_isComesToWork,
							-- Сохраняем время для timestamp. Если кто знает способ лучше - you are welcome
							-- 1900-01-01 это из-за того что, так MSSQL сохраняет строку 'ЧЧ:ММ'
							\"EmergencyTeamDuty_comesToWorkDT\" = to_timestamp('1900-01-01 ' || :EmergencyTeamDuty_comesToWorkDT,'YYYY-MM-DD HH24:MI')::timestamp,
							\"EmergencyTeamDuty_factToWorkDT\" = to_timestamp('1900-01-01 ' || :EmergencyTeamDuty_factToWorkDT,'YYYY-MM-DD HH24:MI')::timestamp,
							\"EmergencyTeam_Head1StartTime\" = to_timestamp('1900-01-01 ' || :EmergencyTeam_Head1StartTime,'YYYY-MM-DD HH24:MI')::timestamp,
							\"EmergencyTeam_Head1FinishTime\" = to_timestamp('1900-01-01 ' || :EmergencyTeam_Head1FinishTime,'YYYY-MM-DD HH24:MI')::timestamp,
							\"EmergencyTeam_Head2StartTime\" = to_timestamp('1900-01-01 ' || :EmergencyTeam_Head2StartTime,'YYYY-MM-DD HH24:MI')::timestamp,
							\"EmergencyTeam_Head2FinishTime\" = to_timestamp('1900-01-01 ' || :EmergencyTeam_Head2FinishTime,'YYYY-MM-DD HH24:MI')::timestamp,
							\"EmergencyTeam_Assistant1StartTime\" = to_timestamp('1900-01-01 ' || :EmergencyTeam_Assistant1StartTime,'YYYY-MM-DD HH24:MI')::timestamp,
							\"EmergencyTeam_Assistant1FinishTime\" = to_timestamp('1900-01-01 ' || :EmergencyTeam_Assistant1FinishTime,'YYYY-MM-DD HH24:MI')::timestamp,
							\"EmergencyTeam_Assistant2StartTime\" = to_timestamp('1900-01-01 ' || :EmergencyTeam_Assistant2StartTime,'YYYY-MM-DD HH24:MI')::timestamp,
							\"EmergencyTeam_Assistant2FinishTime\" = to_timestamp('1900-01-01 ' || :EmergencyTeam_Assistant2FinishTime,'YYYY-MM-DD HH24:MI')::timestamp,
							\"EmergencyTeam_Driver1FinishTime\" = to_timestamp('1900-01-01 ' || :EmergencyTeam_Driver1FinishTime,'YYYY-MM-DD HH24:MI')::timestamp,
							\"EmergencyTeam_Driver1StartTime\" = to_timestamp('1900-01-01 ' || :EmergencyTeam_Driver1StartTime,'YYYY-MM-DD HH24:MI')::timestamp,
							\"EmergencyTeam_Driver2StartTime\" = to_timestamp('1900-01-01 ' || :EmergencyTeam_Driver2StartTime,'YYYY-MM-DD HH24:MI')::timestamp,
							\"EmergencyTeam_Driver2FinishTime\" = to_timestamp('1900-01-01 ' || :EmergencyTeam_Driver2FinishTime,'YYYY-MM-DD HH24:MI')::timestamp,
							\"pmUser_updID\" = :pmUser_id,
							\"EmergencyTeamDuty_IsCancelledStart\" = :EmergencyTeamDuty_IsCancelledStart,
							\"EmergencyTeamDuty_IsCancelledClose\" = :EmergencyTeamDuty_IsCancelledClose,
							\"EmergencyTeamDuty_updDT\" = NOW()
						WHERE
							\"EmergencyTeamDuty_id\" = :EmergencyTeamDuty_id
						RETURNING
							\"EmergencyTeamDuty_id\", null as \"Error_Code\", null as \"Error_Msg\";
					";
				}
			} else {
				// при копировании надо инсерт
				if ( !isset( $value["EmergencyTeamDuty_id"] ) || !$value["EmergencyTeamDuty_id"] ) {
					$procedure = 'p_EmergencyTeamDuty_ins';
					$sqlArr[ 'EmergencyTeamDuty_id' ] = null;
				} else {
					$procedure = 'p_EmergencyTeamDuty_upd';
					$sqlArr[ 'EmergencyTeamDuty_id' ] = $value["EmergencyTeamDuty_id"];
				}


				$sql = "
					DECLARE
						@ErrCode int,
						@ErrMessage varchar(4000),
						@Res bigint

					SET @Res = :EmergencyTeamDuty_id;

					EXEC ".$procedure."
						@EmergencyTeamDuty_id = @Res output,
						@EmergencyTeamDuty_DTStart = :EmergencyTeamDuty_DTStart,
						@EmergencyTeamDuty_DTFinish = :EmergencyTeamDuty_DTFinish,
						@EmergencyTeam_id = :EmergencyTeam_id,
						@EmergencyTeamDuty_Comm = :EmergencyTeamDuty_Comm,
						@EmergencyTeamDuty_ChangeComm = :EmergencyTeamDuty_ChangeComm,
						@EmergencyTeamDuty_isComesToWork = :EmergencyTeamDuty_isComesToWork,
						@EmergencyTeamDuty_isClose = :EmergencyTeamDuty_isClose,
						@EmergencyTeamDuty_comesToWorkDT = :EmergencyTeamDuty_comesToWorkDT,
						@EmergencyTeamDuty_factToWorkDT = :EmergencyTeamDuty_factToWorkDT,

						@EmergencyTeam_Head1StartTime = :EmergencyTeam_Head1StartTime,
						@EmergencyTeam_Head1FinishTime = :EmergencyTeam_Head1FinishTime,
						@EmergencyTeam_Head2StartTime = :EmergencyTeam_Head2StartTime,
						@EmergencyTeam_Head2FinishTime = :EmergencyTeam_Head2FinishTime,
						@EmergencyTeam_Assistant1StartTime = :EmergencyTeam_Assistant1StartTime,
						@EmergencyTeam_Assistant1FinishTime = :EmergencyTeam_Assistant1FinishTime,
						@EmergencyTeam_Assistant2StartTime = :EmergencyTeam_Assistant2StartTime,
						@EmergencyTeam_Assistant2FinishTime = :EmergencyTeam_Assistant2FinishTime,
						@EmergencyTeam_Driver1StartTime = :EmergencyTeam_Driver1StartTime,
						@EmergencyTeam_Driver1FinishTime = :EmergencyTeam_Driver1FinishTime,
						@EmergencyTeam_Driver2StartTime = :EmergencyTeam_Driver2StartTime,
						@EmergencyTeam_Driver2FinishTime = :EmergencyTeam_Driver2FinishTime,
						@EmergencyTeamDuty_IsCancelledStart = :EmergencyTeamDuty_IsCancelledStart,
						@EmergencyTeamDuty_IsCancelledClose = :EmergencyTeamDuty_IsCancelledClose,

						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;

					SELECT @ErrCode as Error_Code, @ErrMessage as Error_Msg, @Res as EmergencyTeamDuty_id, :EmergencyTeam_id as EmergencyTeam_id;
				";
			}
			
			$query = $this->db->query( $sql, $sqlArr );
			if ( is_object( $query ) ) {
				$return[] = $query->result( 'array' );
				// убрал смену статуса бригаде, т.к. при редактировании бригады которая на вызове сбрасывается её статус, возможно статус надо менять где то в другом месте?
				/*if(!empty($value['EmergencyTeamDuty_isComesToWork']) && $value['EmergencyTeamDuty_isComesToWork'] == 2) {
					if ( $EmergencyTeamStatus_id = $this->getEmergencyTeamStatusIdByCode( 13 ) ) {
						$this->setEmergencyTeamStatus( array_merge( $sqlArr, array(
							'EmergencyTeamStatus_id' => $EmergencyTeamStatus_id,
							'ARMType_id' => isset( $data[ 'ARMType' ] ) ? (int)$data[ 'ARMType' ] : null
						) ) );
					}
				}*/
			} else {
				$has_errors = true;
			}
		}
		return $return;
	}

	/**
	 * @desc Сохраняет заданную дату и время начала и окончания смены
	 * @param array $data
	 * @return bool
	 */
	public function editEmergencyTeamDutyTime( $data ){
		if ( !array_key_exists( 'EmergencyTeam_id', $data )
			|| !array_key_exists( 'EmergencyTeamDuty_DateStart', $data )
			|| !array_key_exists( 'EmergencyTeamDuty_DateFinish', $data )
		) {
			return array( array( 'Error_Msg' => 'Отсутствуют необходимые данные. Возможно вы не указали ни одной смены или не выбрали бригаду.' ) );
		}

		$sqlArr = array(
			'EmergencyTeamDuty_id'			=> array_key_exists( 'EmergencyTeamDuty_id', $data ) && $data['EmergencyTeamDuty_id'] ? $data['EmergencyTeamDuty_id'] : null,
			'EmergencyTeamDuty_DTStart'		=> $data['EmergencyTeamDuty_DateStart'],
			'EmergencyTeamDuty_DTFinish'	=> $data['EmergencyTeamDuty_DateFinish'],
			'EmergencyTeam_id'				=> $data['EmergencyTeam_id'],
			'pmUser_id'						=> $data['pmUser_id'],
		);

		$result = $this->saveEmergencyTeamDutyTime( array(
			'EmergencyTeamsDutyTimes' => json_encode( array( $sqlArr ) ),
			'pmUser_id' => $data[ 'pmUser_id' ]
		) );

		if ( sizeof( $result ) ) {
			return $result[0];
		}

		return false;
	}
	
	
	/**
	 * Удаляет заданную дату и время начала и окончания смены
	 *
	 * @param array $data
	 * @return bool
	 */
	public function deleteEmergencyTeamDutyTime( $data ){
		if ( !array_key_exists( 'EmergencyTeamDuty_id', $data ) ) {
			return array( array( 'Error_Msg' => 'Отсутствуют необходимые данные.' ) );
		}

		if ( $this->db->dbdriver == 'postgre' ) {
			$sql = "
				DELETE FROM dbo.\"EmergencyTeamDuty\" WHERE \"EmergencyTeamDuty_id\" = :EmergencyTeamDuty_id
			";
		} else {
			$sql = "
				DECLARE
					@ErrCode int,
					@ErrMessage varchar(4000)

				EXEC p_EmergencyTeamDuty_del
					@EmergencyTeamDuty_id = :EmergencyTeamDuty_id

				SELECT @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
		}
		
		$query = $this->db->query( $sql, array(
			'EmergencyTeamDuty_id' => $data['EmergencyTeamDuty_id']
		) );
		if ( is_object( $query ) ) {
			return $query->result_array();
		}

		return false;
	}
	
	/**
	 * Удаляет заданную дату и время начала и окончания у смен
	 *
	 * @param array $data
	 * @return bool
	 */
	public function deleteEmergencyTeamDutyTimeList( $data ){
		if ( !array_key_exists( 'EmergencyTeamDutyList', $data ) ) {
			return array( array( 'Error_Msg' => 'Отсутствуют необходимые данные.' ) );
		}
		
		$deleteEmergencyTeamDutyTime = $data;
		unset($deleteEmergencyTeamDutyTime['EmergencyTeamDutyList']);
		$result = array();
		foreach ($data['EmergencyTeamDutyList'] as $emergemcy_id) {
			$deleteEmergencyTeamDutyTime['EmergencyTeamDuty_id'] = $emergemcy_id;
			$n = $this->deleteEmergencyTeamDutyTime($deleteEmergencyTeamDutyTime);
			$result[$emergemcy_id] = ( isset($n[0]['Error_Msg']) || !$n[0]) ? false : true;
		}
		return $result;
	}

	/**
	 * @desc Получение оперативной обстановки по диспетчерам СМП
	 * @param array $data
	 * @return array or false
	 */
	function loadDispatchOperEnv( $data ){
		$query = "
			DECLARE
				@Online char(10) = 'false',
				@SMPCallDispath bigint = (select top 1 pmUserCacheGroup_id from dbo.pmUserCacheGroup where pmUserCacheGroup_SysNick = 'SMPCallDispath'),
				@SMPDispatchDirections bigint = (select top 1 pmUserCacheGroup_id from dbo.pmUserCacheGroup where pmUserCacheGroup_SysNick = 'SMPDispatchDirections');
				
			SELECT
				pmUser_id,
				pmUser_name,				
				Lpu_Name,
				@Online as online
			FROM
				v_pmUserCache u with(nolock)
				INNER JOIN v_Lpu as l with(nolock) ON( l.Lpu_id=u.Lpu_id )
			WHERE
				exists(Select top 1 pucgl.pmUserCacheGroupLink_id from v_pmUserCacheGroupLink pucgl with (nolock) where pucgl.pmUserCacheGroup_id = @SMPCallDispath and pucgl.pmUserCache_id = u.PMUser_id)
				and exists(Select top 1 pucgl.pmUserCacheGroupLink_id from v_pmUserCacheGroupLink pucgl with (nolock) where pucgl.pmUserCacheGroup_id = @SMPDispatchDirections and pucgl.pmUserCache_id = u.PMUser_id)
				AND l.Lpu_id = :Lpu_id
				AND u.pmUser_deleted != 2				
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
	 * @desc Возвращает укладку
	 * 
	 * @param array $data
	 * @return array|boolean
	 */
	function loadEmergencyTeamDrugsPack( $data ){
		
		if ( !array_key_exists( 'EmergencyTeam_id', $data ) || !$data['EmergencyTeam_id'] ) {
			return array( array( 'Err_Msg' => 'Отсутствует обязательный параметр: идентификатор бригады') );
		}

		$query = "
			select 				
				CASE WHEN (ISNULL(Drug.Drug_Fas,0) = 0) then RTRIM(convert(varchar,isnull(Drug.DrugTorg_Name,''))+' '+convert(varchar,isnull(Drug.DrugForm_Name,''))+' '+convert(varchar,isnull(Drug.Drug_Dose,'')))
					else RTRIM(convert(varchar,isnull(Drug.DrugTorg_Name,''))+', '+convert(varchar,isnull(Drug.DrugForm_Name,''))+', '+convert(varchar,isnull(Drug.Drug_Dose,''))+', №'+CONVERT(varchar,Drug.Drug_Fas))
				end as DrugTorg_Name,
				Drug.Drug_id as Drug_id,
				Drug.DrugPrepFas_id as DrugPrepFas_id,
				Drug.Drug_Nomen,
				RTRIM(ISNULL(Drug.Drug_Nomen, '')) as Drug_Name,
				RTRIM(ISNULL(Drug.DrugForm_Name, '')) as DrugForm_Name,
				Drug.Drug_Dose as Drug_Dose,
				Drug.Drug_Fas as Drug_Fas,
				Drug.Drug_PackName as Drug_PackName,
				Drug.Drug_Firm as Drug_Firm,
				Drug.Drug_Ean as Drug_Ean,
				Drug.Drug_RegNum as Drug_RegNum,
				dMnn.DrugComplexMnn_RusName as DrugMnn,
				EDP.EmergencyTeamDrugPack_Total
			from 				
				rls.v_Drug Drug with (nolock)
			left join rls.v_DrugComplexMnn dMnn (nolock) on dMnn.DrugComplexMnn_id=Drug.DrugComplexMnn_id 
			INNER JOIN EmergencyTeamDrugPack EDP with(nolock) ON (
				EDP.Drug_id = Drug.Drug_id 
				AND EDP.EmergencyTeam_id = :EmergencyTeam_id
			)				
		";
				
		$queryParams = array(
			'EmergencyTeam_id' => $data['EmergencyTeam_id']
		);
		
		
		$result = $this->db->query($query, $queryParams);
		$arr = $result->result('array');
		
		
		if (is_object($result)){
			$response = array();
			$response['data'] = $arr;
			$response['totalCount'] = count($arr);
			return $response;
		} else {
			return false;
		}
		return $response;
		
	}
	
	
	/**
	 * @desc Возвращает данные указанной бригады
	 * 
	 * @param array $data
	 * @return array|boolean
	 */
	function loadEmergencyTeam( $data ){
		
		if ( !array_key_exists( 'EmergencyTeam_id', $data ) || !$data['EmergencyTeam_id'] ) {
			return array( array( 'Err_Msg' => 'Отсутствует обязательный параметр: идентификатор бригады') );
		}
		
		$GTR = $this->_defineGeoserviceTransportRelQueryParams($data);

		$sql = "
			SELECT TOP 1
				ET.EmergencyTeam_id,
				ET.EmergencyTeam_Num,
				ET.EmergencyTeam_CarNum,
				ET.EmergencyTeam_CarBrand,
				ET.EmergencyTeam_CarModel,
				ET.EmergencyTeam_PortRadioNum,
				ET.EmergencyTeam_GpsNum,
				ET.LpuBuilding_id,
				ET.EmergencyTeamSpec_id,
				ET.EmergencyTeam_HeadShift,
				ET.EmergencyTeam_HeadShiftWorkPlace,
				ET.EmergencyTeam_HeadShift2,
				ET.EmergencyTeam_HeadShift2WorkPlace,
				ET.EmergencyTeam_Driver,
				ET.EmergencyTeam_DriverWorkPlace,
				ET.EmergencyTeam_Driver2,
				ET.EmergencyTeam_Assistant1,
				ET.EmergencyTeam_Assistant1WorkPlace,
				ET.EmergencyTeam_Assistant2,
				ET.CMPTabletPC_id,
				TPC.CMPTabletPC_SIM,
				ETD.EmergencyTeamDuty_id,
				ETD.EmergencyTeamDuty_ChangeComm,
				CONVERT( varchar, CAST( ETD.EmergencyTeamDuty_DTStart as datetime ), 120 ) as EmergencyTeamDuty_DTStart,
				CONVERT( varchar, CAST( ETD.EmergencyTeamDuty_DTFinish as datetime ), 120 ) as EmergencyTeamDuty_DTFinish,
				CONVERT( varchar, CAST( ETD.EmergencyTeamDuty_DTStart as datetime ), 104 ) as EmergencyTeamDuty_DT,

				CONVERT( varchar, CAST( ETD.EmergencyTeam_Head1StartTime as datetime ), 120 ) as EmergencyTeam_Head1StartTime,
				CONVERT( varchar, CAST( ETD.EmergencyTeam_Head1FinishTime as datetime ), 120 ) as EmergencyTeam_Head1FinishTime,
				CONVERT( varchar, CAST( ETD.EmergencyTeam_Head2StartTime as datetime ), 120 ) as EmergencyTeam_Head2StartTime,
				CONVERT( varchar, CAST( ETD.EmergencyTeam_Head2FinishTime as datetime ), 120 ) as EmergencyTeam_Head2FinishTime,
				CONVERT( varchar, CAST( ETD.EmergencyTeam_Assistant1StartTime as datetime ), 120 ) as EmergencyTeam_Assistant1StartTime,
				CONVERT( varchar, CAST( ETD.EmergencyTeam_Assistant1FinishTime as datetime ), 120 ) as EmergencyTeam_Assistant1FinishTime,
				CONVERT( varchar, CAST( ETD.EmergencyTeam_Assistant2StartTime as datetime ), 120 ) as EmergencyTeam_Assistant2StartTime,
				CONVERT( varchar, CAST( ETD.EmergencyTeam_Assistant2FinishTime as datetime ), 120 ) as EmergencyTeam_Assistant2FinishTime,
				CONVERT( varchar, CAST( ETD.EmergencyTeam_Driver1StartTime as datetime ), 120 ) as EmergencyTeam_Driver1StartTime,
				CONVERT( varchar, CAST( ETD.EmergencyTeam_Driver1FinishTime as datetime ), 120 ) as EmergencyTeam_Driver1FinishTime,
				CONVERT( varchar, CAST( ETD.EmergencyTeam_Driver2StartTime as datetime ), 120 ) as EmergencyTeam_Driver2StartTime,
				CONVERT( varchar, CAST( ETD.EmergencyTeam_Driver2FinishTime as datetime ), 120 ) as EmergencyTeam_Driver2FinishTime,
				ET.EmergencyTeam_DutyTime,
				ET.EmergencyTeamStatus_id,
				ETS.EmergencyTeamStatus_Code,
				ETS.EmergencyTeamStatus_Name,
				ET.EmergencyTeam_IsOnline,
				ET.EmergencyTeam_Phone,
				ET.Lpu_id,
				ET.EmergencyTeam_TemplateName,
				--GTR.{$GTR['GeoserviceTransport_id_field']} as GeoserviceTransport_id,
				MPC.MedProductCard_Glonass as GeoserviceTransport_id,
				CCC.CmpCallCard_Numv,
				CCC.CmpCallCard_Ngod,
				CCC.CmpCallCard_id,
				CCC.CmpReason_id,
				COALESCE(PS.Person_Surname, CCC.Person_SurName, '') + ' ' 
				+ COALESCE(PS.Person_Firname, case when rtrim(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end, '') + ' ' 
				+ COALESCE(PS.Person_Secname, case when rtrim(CCC.Person_SecName) = 'null'  then '' else CCC.Person_SecName end, '') as Person_FIO,
				ISNULL(CCC.Person_Age,0) as Person_Age,
				case when SRGN.KLSubRgn_FullName is not null then ''+SRGN.KLSubRgn_FullName else 'г.'+City.KLCity_Name end +
				case when Town.KLTown_FullName is not null then ', '+Town.KLTown_FullName else '' end +
				case when Street.KLStreet_FullName is not null then ', ул.'+Street.KLStreet_Name else '' end +
				case when CCC.CmpCallCard_Dom is not null then ', д.'+CCC.CmpCallCard_Dom else '' end +
				case when CCC.CmpCallCard_Kvar is not null then ', кв.'+CCC.CmpCallCard_Kvar else '' end +
				case when CCC.CmpCallCard_Room is not null then ', ком. '+CCC.CmpCallCard_Room else '' end +
				case when UAD.UnformalizedAddressDirectory_Name is not null then ', Место: '+UAD.UnformalizedAddressDirectory_Name else '' end as Adress_Name,
				CCC.CmpCallCard_Urgency as CmpCallCard_Urgency,
				MPC.MedProductCard_BoardNumber,
				MPC.MedProductCard_id,
				isnull(MPCl.MedProductClass_Model,'') as MedProductClass_Model,
				isnull(AD.AccountingData_RegNumber,'') as AccountingData_RegNumber
			FROM
				v_EmergencyTeam ET with(nolock)
				LEFT JOIN v_EmergencyTeamDuty ETD with(nolock) on ET.EmergencyTeam_id = ETD.EmergencyTeam_id
				LEFT JOIN v_EmergencyTeamStatus AS ETS with (nolock) ON( ETS.EmergencyTeamStatus_id=ET.EmergencyTeamStatus_id )
				LEFT JOIN {$GTR['GeoserviceTransportRel_object']} GTR with (nolock) ON( GTR.{$GTR['EmergencyTeam_id_field']}=ET.EmergencyTeam_id )
				LEFT JOIN v_CmpCallCard as CCC with (nolock) ON(
					CCC.CmpCallCard_id = (SELECT TOP 1 C2.CmpCallCard_id FROM v_CmpCallCard as C2 with(nolock) WHERE C2.EmergencyTeam_id = ET.EmergencyTeam_id ORDER BY C2.CmpCallCard_updDT DESC)
				)
				left join v_PersonState PS with (nolock) on PS.Person_id = CCC.Person_id
				left join v_CMPTabletPC TPC with (nolock) on TPC.CMPTabletPC_id = ET.CMPTabletPC_id
				left join v_KLRgn RGN on RGN.KLRgn_id = CCC.KLRgn_id
				left join v_KLSubRgn SRGN on SRGN.KLSubRgn_id = CCC.KLSubRgn_id
				left join v_KLCity City on City.KLCity_id = CCC.KLCity_id
				left join v_KLTown Town on Town.KLTown_id = CCC.KLTown_id
				left join v_KLStreet Street on Street.KLStreet_id = CCC.KLStreet_id
				left join v_UnformalizedAddressDirectory UAD on UAD.UnformalizedAddressDirectory_id = CCC.UnformalizedAddressDirectory_id
				LEFT JOIN passport.v_MedProductCard MPC with (nolock) on MPC.MedProductCard_id = ET.MedProductCard_id
				LEFT JOIN passport.v_MedProductClass MPCl with (nolock) on MPCl.MedProductClass_id = MPC.MedProductClass_id
				LEFT JOIN passport.v_AccountingData AD with (nolock) on MPC.MedProductCard_id = AD.MedProductCard_id
			WHERE
				ET.EmergencyTeam_id = :EmergencyTeam_id
		";
		
		
		$query = $this->db->query( $sql, array(
			'EmergencyTeam_id' => $data['EmergencyTeam_id']
		) );

		if ( is_object( $query ) ) {
			return $query->result_array();
		}

		return false;
	}
	
	
	/**
	 * Возвращает данные автомобилей, заведенных в паспорте мо
	 * 
	 * @param array $data
	 * @return array|boolean
	 */
	public function loadEmergencyTeamList( $data ) {
		if ( !array_key_exists( 'Lpu_id', $data ) || !$data[ 'Lpu_id' ] ) {
			return array( array( 'Err_Msg' => 'Не указан идентификатор ЛПУ' ) );
		}
		
		$lpuFilter = '';
		$selects = '';
		$joins = '';
		$filters = '(1=1)';
		// соберем ИД базовых подстанций выбранных пользователем при входе в АРМ и подставим в фильтр по ним
		$user = pmAuthUser::find($_SESSION['login']);
		$settings = unserialize($user->settings);
		$CurArmType = $data['session']['CurArmType'];

		if ( empty( $data["viewAllMO"]) ){
			if ( isset($settings['lpuBuildingsWorkAccess']) && is_array($settings['lpuBuildingsWorkAccess']) ) {
				$lpuBuildingsWorkAccess = $settings['lpuBuildingsWorkAccess'];
				if (!empty($lpuBuildingsWorkAccess)) {
					$lpuFilter = " AND LB.LpuBuilding_id in (" . implode(",", $lpuBuildingsWorkAccess) . ")";
				} else {
					$lpuFilter = " AND 1 = 0";
				}
			}
			if ( !empty($data[ 'LpuBuilding_id' ]) ) {

				if ( empty($data[ 'filterLpuBuilding' ]) ) {
					$filters .= ($lpuFilter) ? $lpuFilter : " AND LB.LpuBuilding_id = :LpuBuilding_id";
				}
				else{
					$filters .= " AND LB.LpuBuilding_id = :filterLpuBuilding";
				}

			}
		}

		if ( !in_array($CurArmType, array('dispdirnmp')) ) {
			$filters .= " and LB.Lpu_id = :Lpu_id";
		}

		if ( in_array($CurArmType, array('dispnmp', 'dispdirnmp')) ) {
			$filters .= " AND MPT.MedProductType_Code in (6) AND LB.LpuBuildingType_id = 28";
		}else {
			$filters .= " AND MPT.MedProductType_Code in (7,8,9,10)";
		}
		
		if(!empty($data[ 'display' ])){
			switch($data[ 'display' ]){
				case 'all': {
					break;
				}
				case 'opened': {					
					$filters .= " AND ((  (AD.AccountingData_endDate IS NULL) OR  ( (AD.AccountingData_setDate) < (dbo.tzGetDate() ) )  )
						AND (  (AD.AccountingData_endDate IS NULL) OR  ( (AD.AccountingData_endDate) > (dbo.tzGetDate() ) )  ))";
					break;
				}
				case 'closed': {
					$filters .= " AND NOT (    (  (AD.AccountingData_endDate IS NULL) OR  ( (AD.AccountingData_setDate) < (dbo.tzGetDate() ) )  ) 
						AND (  (AD.AccountingData_endDate IS NULL) OR  ( (AD.AccountingData_endDate) > (dbo.tzGetDate() ) )  )    )";
					break;
				}
			}
			
		}
		//$where[] = "(( AD.AccountingData_setDate) < (dbo.tzGetDate())) AND (( AD.AccountingData_endDate) > (dbo.tzGetDate()))";
		
		//Получаем наименование полей и объектов геосервиса в зависимости от региона (и, возможно, в будущем - МО)
		//$GTR = $this->_defineGeoserviceTransportRelQueryParams($data);

		//фильтр авто, открытых в выбранный промежуток времени
		if(!empty($data['dStart']) && !empty($data['dFinish'])){
			$filters .= " AND ((  (AD.AccountingData_endDate IS NULL) OR  ( (AD.AccountingData_setDate) <= (:dStart ) )  )
						AND (  (AD.AccountingData_endDate IS NULL) OR  ( (AD.AccountingData_endDate) >= (:dFinish ) )  ))";

		}

        $params = array(
            'Lpu_id' => $data[ 'Lpu_id' ],
            'LpuBuilding_id' => $data[ 'LpuBuilding_id' ],
            'filterLpuBuilding' => !empty($data['filterLpuBuilding']) ? $data['filterLpuBuilding']: null,
            'dStart' => !empty($data['dStart']) ? $data['dStart']: null,
            'dFinish' => !empty($data['dFinish']) ? $data['dFinish']: null
        );

        $apply = '';
        $select = '';
        if(!empty($data['dtStart']) && !empty($data['dtFinish'])) {
            $apply = '
            outer apply(
                select top 1
                	    ET.EmergencyTeam_id,
                	    EmergencyTeam_Num,
                	    CONVERT(varchar, ETD.EmergencyTeamDuty_DTStart, 120) AS EmergencyTeamDuty_DTStart,
                	    CONVERT(varchar, ETD.EmergencyTeamDuty_DTFinish, 120) AS EmergencyTeamDuty_DTFinish
                    from v_EmergencyTeam ET with(nolock)
                    LEFT JOIN v_EmergencyTeamDuty ETD with(nolock) on ET.EmergencyTeam_id = ETD.EmergencyTeam_id
                    where MPC.MedProductCard_id = ET.MedProductCard_id
                    and ISNULL(ET.EmergencyTeam_isTemplate,1) = 1
                    AND (
                        ( CAST(ETD.EmergencyTeamDuty_DTStart as datetime) >= :dtStart AND CAST(ETD.EmergencyTeamDuty_DTStart as datetime) <= :dtFinish )
                        OR (CAST(ETD.EmergencyTeamDuty_DTFinish as datetime) >= :dtStart AND CAST(ETD.EmergencyTeamDuty_DTFinish as datetime) <= :dtFinish)
                        )
                    )
                as team';

            $params['dtStart'] = $data['dtStart'];
            $params['dtFinish'] = $data['dtFinish'];
            $select = 'team.EmergencyTeam_id,
                	   team.EmergencyTeam_Num,
                	   team.EmergencyTeamDuty_DTStart,
                	   team.EmergencyTeamDuty_DTFinish,
                	   ';
        }

		$sql = "
			SELECT
				MPC.MedProductCard_id,
                LB.LpuBuilding_id,
                LB.LpuBuilding_Name,
                {$select}
                MPC.MedProductCard_BoardNumber,
				AD.AccountingData_RegNumber,			
				MPCl.MedProductClass_Name,
				MPCl.MedProductClass_Model,
				MPT.MedProductType_Code,
                MPC.MedProductCard_Glonass as GeoserviceTransport_id,
				convert(varchar(10), AD.AccountingData_setDate, 120) as AccountingData_setDate,
                convert(varchar(10), AD.AccountingData_endDate, 120) as AccountingData_endDate
			FROM
				passport.v_MedProductCard MPC with (nolock)				
				left join passport.v_MedProductClass MPCl with (nolock) on MPCl.MedProductClass_id = MPC.MedProductClass_id
                left join passport.v_AccountingData AD with (nolock) on MPC.MedProductCard_id = AD.MedProductCard_id
                left join passport.v_MedProductType MPT with (nolock) on MPT.MedProductType_id = MPCl.MedProductType_id
				left join v_LpuBuilding LB with (nolock) on LB.LpuBuilding_id = MPC.LpuBuilding_id
				{$apply}

			WHERE
            	".$filters."
		";
		


        //var_dump(getDebugSQL($sql, $params)); exit;

		$query = $this->db->query( $sql, $params );
		$result = $query->result_array();
		
		if ( is_object( $query ) && count($result) > 0) {
			return $result;
		}

		return false;
		
	}

	/**
	 * Возвращает данные всех бригад ЛПУ c сегодняшнего дня
	 * 
	 * @param array $data
	 * @return array|boolean
	 */
	public function loadEmergencyTeamShiftList( $data ) {
		
		$is_pg = $this->db->dbdriver == 'postgre' ? true : false;

		$queryParams = array();
		$CurArmType = $data['session']['CurArmType'];

		if ( !empty( $data[ 'session' ][ 'CurMedService_id' ] ) ) {
			if ( $is_pg ) {
				$lpuBuildingQuery = "
					SELECT
						COALESCE(MS.\"LpuBuilding_id\",0) as \"LpuBuilding_id\"
					FROM
						dbo.\"v_MedService\" MS
					WHERE
						MS.\"MedService_id\" = :MedService_id
				";
			} else {
				$lpuBuildingQuery = "
					SELECT
						ISNULL(MS.LpuBuilding_id,0) as LpuBuilding_id
					FROM
						v_MedService MS with (nolock)
					WHERE
						MS.MedService_id = :MedService_id
				";
			}
			$lpuBuildingResult = $this->db->query( $lpuBuildingQuery, array(
				'MedService_id' => $data[ 'session' ][ 'CurMedService_id' ]
			) );
			
			
			if ( is_object( $lpuBuildingResult ) ) {	
				
				$lpuFilter = "";
				// Усли нужно загрузить список нарядов тех подразделений СМП, которые были выбраны  пользователем при входе в АРМ
				if (isset($data['loadSelectSmp'])) {
					// возьмем ИД выбранных пользователем подразделений СМП
					$arrayIdSelectSmp = $this->loadIdSelectSmp();
					if (!empty($arrayIdSelectSmp)) {
						$lpuFilter .= " ET.LpuBuilding_id in (" . implode(",", $arrayIdSelectSmp) . ")";
					} else {
						$lpuFilter .= " 1 = 0";
					}
				}
				
				$lpuBuildingResult = $lpuBuildingResult->result( 'array' );
				if ( isset( $lpuBuildingResult[ 0 ] ) && (!empty( $lpuBuildingResult[ 0 ][ 'LpuBuilding_id' ] )) ) {
					$queryParams[ 'LpuBuilding_id' ] = $lpuBuildingResult[ 0 ][ 'LpuBuilding_id' ];
				} else {
					//return $lpuBuildingResult;
					return false;
				}
			} else {
				return false;
			}
		}
		
		//если указан интервал то выводим бригады в интервале
		//иначе только те, у которых активная смена
		if ( !empty( $data[ 'dateStart' ] ) && !empty( $data[ 'dateFinish' ] ) ) {
			$date_start = DateTime::createFromFormat( 'd.m.Y', $data[ 'dateStart' ] );
			$date_finish = DateTime::createFromFormat( 'd.m.Y', $data[ 'dateFinish' ] );

			$queryParams[ 'dateStart' ] = $date_start->format( 'Y-m-d' );
			$queryParams[ 'dateFinish' ] = $date_finish->format( 'Y-m-d' );

			if ( $is_pg ) {
				// @todo Проверить работу этого условия сравнения даты
				$filter = "	AND ETD.\"EmergencyTeamDuty_DTStart\"::date >= :dateStart::date AND ETD.\"EmergencyTeamDuty_DTStart\"::date <= :dateFinish::date ";
			} else {
				$filter = "	AND CAST(ETD.EmergencyTeamDuty_DTStart as date) >= :dateStart AND CAST(ETD.EmergencyTeamDuty_DTStart as date) <= :dateFinish";
			}
		} elseif ( !empty( $data[ 'is_actual' ] ) ) {
			
			if ( $is_pg ) {
				// В данный момент не предполагается использование запроса с этим условием на postgress
			} else {
				$filter = "AND ( ETD.EmergencyTeamDuty_DTFinish) > (dbo.tzGetDate())" ;
			}
			
		} else {
			if ( $is_pg ) {
				$filter = " AND ETD.\"EmergencyTeamDuty_DTStart\" < dbo.\"tzGetDate\"() AND ETD.\"EmergencyTeamDuty_DTFinish\" > dbo.\"tzGetDate\"()";
			} else {
				$filter = " AND (( ETD.EmergencyTeamDuty_DTStart) < (dbo.tzGetDate())) AND (( ETD.EmergencyTeamDuty_DTFinish) > (dbo.tzGetDate()))";
			}
		}
		
			$filter = " ISNULL(ET.EmergencyTeam_isTemplate,1) = 1 ".$filter;

		if($lpuFilter){
			$filter .= ' AND '.$lpuFilter;
		}else{
			$filter .= ' AND ET.LpuBuilding_id = :LpuBuilding_id';
		}

		if( !empty($data['EmergencyTeamSpec_id'])) {
			$filter .= ' AND ET.EmergencyTeamSpec_id = :EmergencyTeamSpec_id';
			$queryParams['EmergencyTeamSpec_id'] = $data['EmergencyTeamSpec_id'];
		}
		

		//если указано выводить бригады по фактичекой дате начала и конца смены
		if ( !empty( $data[ 'dateFactFinish' ] ) && !empty( $data[ 'dateFactStart' ] ) ) {
			/*
			 * по заданию #88109
			 * Бригада считается находящейся на смене, если текущая дата и время входят в фактический период работы бригады 
			 * (поля «Фактическое начало работы», «Фактическое окончание работы» формы «Отметка о выходе бригад СМП»). 
			 * Если Фактическое начало работы НЕ задано, то считается, что бригада НЕ вышла на смену. 
			 * Если Фактическое начало работы заполнено, а Фактическое окончание работы НЕ заполнено, то считается, что бригада находится на смене.
			 */
			$date_start = DateTime::createFromFormat( 'd.m.Y H:i:s', $data[ 'dateFactStart' ] );
			$date_finish = DateTime::createFromFormat( 'd.m.Y H:i:s', $data[ 'dateFactFinish' ] );
			$queryParams[ 'dateFactStart' ] = $date_start->format( 'Y-m-d H:i:s' );
			$queryParams[ 'dateFactFinish' ] = $date_finish->format( 'Y-m-d H:i:s' );
			// некоторое пустые занчения сохранены как '1900-01-01 00:00:00', значит пока будем считать их как пустые
			$queryParams[ 'is1900' ] = "1900-01-01 00:00:00";
			$filter .= "
				AND ETD.EmergencyTeamDuty_factToWorkDT < :dateFactStart
				AND ETD.EmergencyTeamDuty_factToWorkDT <> :is1900
				AND ( 
					ETD.EmergencyTeamDuty_factEndWorkDT is NULL
					OR  ETD.EmergencyTeamDuty_factEndWorkDT = :is1900
					OR (ETD.EmergencyTeamDuty_factEndWorkDT) > :dateFactFinish 
				)
			";
		}

		if(!empty($data['showCurrentTeamsByFact']) && $data['showCurrentTeamsByFact'] == 'true'){
			$queryParams[ 'is1900' ] = "1900-01-01 00:00:00";
			$filter .= "
				AND ( ETD.EmergencyTeamDuty_factToWorkDT IS NOT NULL AND ETD.EmergencyTeamDuty_factToWorkDT < dbo.tzGetDate() )              
				AND (
					ETD.EmergencyTeamDuty_factEndWorkDT is NULL
					OR  ETD.EmergencyTeamDuty_factEndWorkDT = :is1900
					OR (ETD.EmergencyTeamDuty_factEndWorkDT) > dbo.tzGetDate()
				)
			";
		}
		
		//Получаем наименование полей и объектов геосервиса в зависимости от региона (и, возможно, в будущем - МО)
		$GTR = $this->_defineGeoserviceTransportRelQueryParams($data);
		if ( $is_pg ) {
			$query = "
				SELECT
					ET.\"EmergencyTeam_id\",
					ET.\"EmergencyTeam_Num\",
					ET.\"EmergencyTeam_PortRadioNum\",
					ET.\"EmergencyTeam_GpsNum\",
					ET.\"EmergencyTeam_CarNum\",
					ET.\"EmergencyTeam_CarBrand\",
					ET.\"EmergencyTeam_CarModel\",					
					LB.\"LpuBuilding_Nick\",
					ET.\"LpuBuilding_id\",
					ET.\"Lpu_id\",
					ET.\"CMPTabletPC_id\",
					ET.\"EmergencyTeam_Phone\",

					ET.\"EmergencyTeam_HeadShift\",
					ET.\"EmergencyTeam_HeadShiftWorkPlace\",
					ET.\"EmergencyTeam_HeadShift2\",
					ET.\"EmergencyTeam_HeadShift2WorkPlace\",
					ET.\"EmergencyTeam_Assistant1\",
					ET.\"EmergencyTeam_Assistant1WorkPlace\",
					ET.\"EmergencyTeam_Assistant2\",
					ET.\"EmergencyTeam_Driver\",
					ET.\"EmergencyTeam_DriverWorkPlace\",
					ET.\"EmergencyTeam_Driver2\",
					ET.\"EmergencyTeam_DutyTime\",
					ET.\"EmergencyTeamStatus_id\",

					ETSpec.\"EmergencyTeamSpec_id\",
					ETSpec.\"EmergencyTeamSpec_Name\",
					ETSpec.\"EmergencyTeamSpec_Code\",

					MPh1.\"Person_Fin\" as \"EmergencyTeam_HeadShiftFIO\",
					MPh2.\"Person_Fin\" as \"EmergencyTeam_HeadShift2FIO\",
					MPd1.\"Person_Fin\" as \"EmergencyTeam_DriverFIO\",
					MPd2.\"Person_Fin\" as \"EmergencyTeam_Driver2FIO\",
					MPa1.\"Person_Fin\" as \"EmergencyTeam_Assistant1FIO\",
					MPa2.\"Person_Fin\" as \"EmergencyTeam_Assistant2FIO\",

					ETD.\"EmergencyTeamDuty_id\",			
					
					TO_CHAR( ETD.\"EmergencyTeamDuty_DTStart\", 'YYYY-MM-DD HH24:MI:SS' ) as \"EmergencyTeamDuty_DTStart\",
				
					TO_CHAR( ETD.\"EmergencyTeamDuty_DTFinish\", 'YYYY-MM-DD HH24:MI:SS' ) as \"EmergencyTeamDuty_DTFinish\",
					
					TO_CHAR( ETD.\"EmergencyTeamDuty_DTStart\", 'YYYY-MM-DD' ) as \"EmergencyTeamDuty_DStart\",					
					TO_CHAR( ETD.\"EmergencyTeamDuty_DTFinish\", 'YYYY-MM-DD' ) as \"EmergencyTeamDuty_DFinish\",					

					TO_CHAR( ETD.\"EmergencyTeam_Head1StartTime\", 'YYYY-MM-DD HH24:MI:SS' ) as \"EmergencyTeam_Head1StartTime\",
					TO_CHAR( ETD.\"EmergencyTeam_Head1FinishTime\", 'YYYY-MM-DD HH24:MI:SS' ) as \"EmergencyTeam_Head1FinishTime\",
					TO_CHAR( ETD.\"EmergencyTeam_Head2StartTime\", 'YYYY-MM-DD HH24:MI:SS' ) as \"EmergencyTeam_Head2StartTime\",
					TO_CHAR( ETD.\"EmergencyTeam_Head2FinishTime\", 'YYYY-MM-DD HH24:MI:SS' ) as \"EmergencyTeam_Head2FinishTime\",
					TO_CHAR( ETD.\"EmergencyTeam_Assistant1StartTime\", 'YYYY-MM-DD HH24:MI:SS' ) as \"EmergencyTeam_Assistant1StartTime\",
					TO_CHAR( ETD.\"EmergencyTeam_Assistant1FinishTime\", 'YYYY-MM-DD HH24:MI:SS' ) as \"EmergencyTeam_Assistant1FinishTime\",
					TO_CHAR( ETD.\"EmergencyTeam_Assistant2StartTime\", 'YYYY-MM-DD HH24:MI:SS' ) as \"EmergencyTeam_Assistant2StartTime\",
					TO_CHAR( ETD.\"EmergencyTeam_Assistant2FinishTime\", 'YYYY-MM-DD HH24:MI:SS' ) as \"EmergencyTeam_Assistant2FinishTime\",
					TO_CHAR( ETD.\"EmergencyTeam_Driver1StartTime\", 'YYYY-MM-DD HH24:MI:SS' ) as \"EmergencyTeam_Driver1StartTime\",
					TO_CHAR( ETD.\"EmergencyTeam_Driver1FinishTime\", 'YYYY-MM-DD HH24:MI:SS' ) as \"EmergencyTeam_Driver1FinishTime\",
					TO_CHAR( ETD.\"EmergencyTeam_Driver2StartTime\", 'YYYY-MM-DD HH24:MI:SS' ) as \"EmergencyTeam_Driver2StartTime\",
					TO_CHAR( ETD.\"EmergencyTeam_Driver2FinishTime\", 'YYYY-MM-DD HH24:MI:SS' ) as \"EmergencyTeam_Driver2FinishTime\",
					ETD.\"EmergencyTeamDuty_ChangeComm\",

					ETD.\"EmergencyTeamDuty_IsCancelledStart\",
					ETD.\"EmergencyTeamDuty_IsCancelledClose\",

					CASE WHEN COALESCE(ETD.\"EmergencyTeamDuty_isComesToWork\",1) = 1 THEN 'false' ELSE 'true' END AS \"locked\",
					CASE WHEN COALESCE(ETD.\"EmergencyTeamDuty_isClose\",1) = 1 THEN 'false' ELSE 'true' END AS \"closed\", 
					
					GTR.{$GTR['GeoserviceTransport_id_field']} as \"GeoserviceTransport_id\"
				FROM
					dbo.\"v_EmergencyTeam\" ET
					LEFT JOIN dbo.\"v_MedPersonal\" MPh1 ON( MPh1.\"MedPersonal_id\"=ET.\"EmergencyTeam_HeadShift\" )
					LEFT JOIN dbo.\"v_MedPersonal\" MPh2 ON( MPh2.\"MedPersonal_id\"=ET.\"EmergencyTeam_HeadShift2\" )
					LEFT JOIN dbo.\"v_MedPersonal\" MPd1 ON( MPd1.\"MedPersonal_id\"=ET.\"EmergencyTeam_Driver\" )
					LEFT JOIN dbo.\"v_MedPersonal\" MPd2 ON( MPd2.\"MedPersonal_id\"=ET.\"EmergencyTeam_Driver2\" )
					LEFT JOIN dbo.\"v_MedPersonal\" MPa1 ON( MPa1.\"MedPersonal_id\"=ET.\"EmergencyTeam_Assistant1\" )
					LEFT JOIN dbo.\"v_MedPersonal\" MPa2 ON( MPa2.\"MedPersonal_id\"=ET.\"EmergencyTeam_Assistant2\" )
					LEFT JOIN dbo.{$GTR['GeoserviceTransportRel_object']} as GTR ON( GTR.{$GTR['EmergencyTeam_id_field']}=ET.\"EmergencyTeam_id\" )
					LEFT JOIN dbo.\"v_EmergencyTeamSpec\" as ETSpec ON(ET.\"EmergencyTeamSpec_id\"=ETSpec.\"EmergencyTeamSpec_id\" )
					LEFT JOIN dbo.\"v_LpuBuilding\" LB ON ET.\"LpuBuilding_id\"=LB.\"LpuBuilding_id\"
					LEFT JOIN dbo.\"v_EmergencyTeamDuty\" ETD on ET.\"EmergencyTeam_id\"=ETD.\"EmergencyTeam_id\"
				WHERE
					ET.\"LpuBuilding_id\" = :LpuBuilding_id
					AND COALESCE(ET.\"EmergencyTeam_isTemplate\",1)=1
					".$filter."
			";
		} else {
			$query = "
				SELECT DISTINCT
					ET.EmergencyTeam_id,
					ET.EmergencyTeam_Num,
					ET.EmergencyTeam_PortRadioNum,
					ET.EmergencyTeam_GpsNum,
					ET.EmergencyTeam_CarNum,
					ET.EmergencyTeam_CarBrand,
					ET.EmergencyTeam_CarModel,
					LB.LpuBuilding_Nick,
					LB.LpuBuilding_Name,
					ET.LpuBuilding_id,
					ET.Lpu_id,
					ET.CMPTabletPC_id,
					ET.EmergencyTeam_Phone,

					ET.EmergencyTeam_HeadShift,
					ET.EmergencyTeam_HeadShiftWorkPlace,
					ET.EmergencyTeam_HeadShift2,
					ET.EmergencyTeam_HeadShift2WorkPlace,
					ET.EmergencyTeam_Assistant1,
					ET.EmergencyTeam_Assistant1WorkPlace,
					ET.EmergencyTeam_Assistant2,
					ET.EmergencyTeam_Driver,
					ET.EmergencyTeam_DriverWorkPlace,
					ET.EmergencyTeam_Driver2,
					ET.EmergencyTeam_DutyTime,
					ET.EmergencyTeamStatus_id,
					ET.MedProductCard_id,
					MPC.MedProductCard_BoardNumber,
					MPCl.MedProductClass_Model,
					AD.AccountingData_RegNumber,
					isnull(MPC.MedProductCard_BoardNumber,'') + ' ' + isnull(MPCl.MedProductClass_Model,'') + ' ' + isnull(AD.AccountingData_RegNumber,'') as MedProduct_Name,


					ETSpec.EmergencyTeamSpec_id,
					ETSpec.EmergencyTeamSpec_Name,
					ETSpec.EmergencyTeamSpec_Code,

					MPh1.Person_Fin as EmergencyTeam_HeadShiftFIO,
					MPh2.Person_Fin as EmergencyTeam_HeadShift2FIO,
					MPd1.Person_Fin as EmergencyTeam_DriverFIO,
					MPd2.Person_Fin as EmergencyTeam_Driver2FIO,
					MPa1.Person_Fin as EmergencyTeam_Assistant1FIO,
					MPa2.Person_Fin as EmergencyTeam_Assistant2FIO,

					ETD.EmergencyTeamDuty_id,
					CONVERT( varchar, CAST( ETD.EmergencyTeamDuty_DTStart as datetime ), 120 ) as EmergencyTeamDuty_DTStart,
					CONVERT( varchar, CAST( ETD.EmergencyTeamDuty_DTFinish as datetime ), 120 ) as EmergencyTeamDuty_DTFinish,
					CONVERT( varchar, CAST( ETD.EmergencyTeamDuty_DTStart as datetime ), 104 ) as EmergencyTeamDuty_DStart,
					CONVERT( varchar, CAST( ETD.EmergencyTeamDuty_DTFinish as datetime ), 104 ) as EmergencyTeamDuty_DFinish,

					CONVERT( varchar, CAST( ETD.EmergencyTeam_Head1StartTime as datetime ), 120 ) as EmergencyTeam_Head1StartTime,
					CONVERT( varchar, CAST( ETD.EmergencyTeam_Head1FinishTime as datetime ), 120 ) as EmergencyTeam_Head1FinishTime,
					CONVERT( varchar, CAST( ETD.EmergencyTeam_Head2StartTime as datetime ), 120 ) as EmergencyTeam_Head2StartTime,
					CONVERT( varchar, CAST( ETD.EmergencyTeam_Head2FinishTime as datetime ), 120 ) as EmergencyTeam_Head2FinishTime,
					CONVERT( varchar, CAST( ETD.EmergencyTeam_Assistant1StartTime as datetime ), 120 ) as EmergencyTeam_Assistant1StartTime,
					CONVERT( varchar, CAST( ETD.EmergencyTeam_Assistant1FinishTime as datetime ), 120 ) as EmergencyTeam_Assistant1FinishTime,
					CONVERT( varchar, CAST( ETD.EmergencyTeam_Assistant2StartTime as datetime ), 120 ) as EmergencyTeam_Assistant2StartTime,
					CONVERT( varchar, CAST( ETD.EmergencyTeam_Assistant2FinishTime as datetime ), 120 ) as EmergencyTeam_Assistant2FinishTime,
					CONVERT( varchar, CAST( ETD.EmergencyTeam_Driver1StartTime as datetime ), 120 ) as EmergencyTeam_Driver1StartTime,
					CONVERT( varchar, CAST( ETD.EmergencyTeam_Driver1FinishTime as datetime ), 120 ) as EmergencyTeam_Driver1FinishTime,
					CONVERT( varchar, CAST( ETD.EmergencyTeam_Driver2StartTime as datetime ), 120 ) as EmergencyTeam_Driver2StartTime,
					CONVERT( varchar, CAST( ETD.EmergencyTeam_Driver2FinishTime as datetime ), 120 ) as EmergencyTeam_Driver2FinishTime,
					ETD.EmergencyTeamDuty_ChangeComm,

					ETD.EmergencyTeamDuty_IsCancelledStart,
					ETD.EmergencyTeamDuty_IsCancelledClose,

					CONVERT( varchar, CAST( ETD.EmergencyTeamDuty_factToWorkDT as datetime ), 120 ) as EmergencyTeamDuty_factToWorkDT,
					CONVERT( varchar, CAST( ETD.EmergencyTeamDuty_factEndWorkDT as datetime ), 120 ) as EmergencyTeamDuty_factEndWorkDT,

					CASE WHEN ISNULL(ETD.EmergencyTeamDuty_isComesToWork,1) = 1 THEN 'false' ELSE 'true' END AS locked,
					CASE WHEN ISNULL(ETD.EmergencyTeamDuty_isClose,1) = 1 THEN 'false' ELSE 'true' END AS closed,					
					COALESCE(GTR.{$GTR['GeoserviceTransport_id_field']}, MPC.MedProductCard_Glonass) as GeoserviceTransport_id,
					L.Lpu_Nick,
					L.Lpu_id
				FROM
					v_EmergencyTeam ET with(nolock)
					LEFT JOIN v_MedPersonal MPh1 with(nolock) ON( MPh1.MedPersonal_id=ET.EmergencyTeam_HeadShift )
					LEFT JOIN v_MedPersonal MPh2 with(nolock) ON( MPh2.MedPersonal_id=ET.EmergencyTeam_HeadShift2 )
					LEFT JOIN v_MedPersonal MPd1 with(nolock) ON( MPd1.MedPersonal_id=ET.EmergencyTeam_Driver )
					LEFT JOIN v_MedPersonal MPd2 with(nolock) ON( MPd2.MedPersonal_id=ET.EmergencyTeam_Driver2 )
					LEFT JOIN v_MedPersonal MPa1 with(nolock) ON( MPa1.MedPersonal_id=ET.EmergencyTeam_Assistant1 )
					LEFT JOIN v_MedPersonal MPa2 with(nolock) ON( MPa2.MedPersonal_id=ET.EmergencyTeam_Assistant2 )
					LEFT JOIN {$GTR['GeoserviceTransportRel_object']} as GTR with (nolock) ON( GTR.{$GTR['EmergencyTeam_id_field']}=ET.EmergencyTeam_id )
					LEFT JOIN v_EmergencyTeamSpec as ETSpec with(nolock) ON(ET.EmergencyTeamSpec_id = ETSpec.EmergencyTeamSpec_id)
					LEFT JOIN v_LpuBuilding LB with(nolock) ON ET.LpuBuilding_id = LB.LpuBuilding_id
					LEFT JOIN v_EmergencyTeamDuty ETD with(nolock) on ET.EmergencyTeam_id = ETD.EmergencyTeam_id
					LEFT JOIN passport.v_MedProductCard MPC with (nolock) on MPC.MedProductCard_id = ET.MedProductCard_id
					LEFT JOIN passport.v_MedProductClass MPCl with (nolock) on MPCl.MedProductClass_id = MPC.MedProductClass_id
					LEFT JOIN passport.v_AccountingData AD with (nolock) on MPC.MedProductCard_id = AD.MedProductCard_id
					LEFT JOIN v_Lpu L with(nolock) on L.Lpu_id = ET.Lpu_id
				WHERE
					".$filter."
			";
		}
		
		//var_dump(getDebugSQL($query, $queryParams)); exit;

		$result = $this->db->query( $query, $queryParams );
		
		if ( count( $result->result( 'array' ) ) > 0 ) {
			return $result->result( 'array' );
		} else {
			return false;
		}
	}
	
	
	
	/**
	 * Возвращает данные шаблонов бригад
	 * 
	 * @param array $data
	 * @return array|boolean
	 */
	public function loadEmergencyTeamTemplateList( $data ) {
		
		$queryParams = array();
		$where = array();
		$lpuFilter = '';
		
		$GTR = $this->_defineGeoserviceTransportRelQueryParams($data);
		
		$this->load->model( 'CmpCallCard_model4E', 'cardModel' );
		
		$where[] = $this->getNestedLpuBuildingsForRequests($data);
		$where[] = "ISNULL(ET.EmergencyTeam_isTemplate,1) = 2";
	
		$query = "
			SELECT DISTINCT
				ET.EmergencyTeam_id,
				ET.EmergencyTeam_Num,
				ET.EmergencyTeam_PortRadioNum,
				ET.EmergencyTeam_GpsNum,
				ET.EmergencyTeam_CarNum,
				ET.EmergencyTeam_CarBrand,
				ET.EmergencyTeam_CarModel,
				LB.LpuBuilding_Nick,
				LB.LpuBuilding_Name,
				ET.LpuBuilding_id,
				ET.Lpu_id,
				ET.CMPTabletPC_id,

				ET.EmergencyTeam_HeadShift,
				ET.EmergencyTeam_HeadShiftWorkPlace,
				ET.EmergencyTeam_HeadShift2,
				ET.EmergencyTeam_HeadShift2WorkPlace,
				ET.EmergencyTeam_Assistant1,
				ET.EmergencyTeam_Assistant1WorkPlace,
				ET.EmergencyTeam_Assistant2,
				ET.EmergencyTeam_Driver,
				ET.EmergencyTeam_DriverWorkPlace,
				ET.EmergencyTeam_Driver2,
				ET.EmergencyTeam_DutyTime,
				ET.EmergencyTeamStatus_id,
				ET.MedProductCard_id,
				MPCl.MedProductClass_Name,

				ETSpec.EmergencyTeamSpec_id,
				ETSpec.EmergencyTeamSpec_Name,
				ETSpec.EmergencyTeamSpec_Code,
				
				ET.EmergencyTeam_TemplateName,
				CONVERT( varchar, CAST( ETD.EmergencyTeamDuty_DTStart as datetime ), 120 ) as EmergencyTeamDuty_DTStart,
				CONVERT( varchar, CAST( ETD.EmergencyTeamDuty_DTFinish as datetime ), 120 ) as EmergencyTeamDuty_DTFinish,

				MPh1.Person_Fin as EmergencyTeam_HeadShiftFIO,
				MPh2.Person_Fin as EmergencyTeam_HeadShift2FIO,
				MPd1.Person_Fin as EmergencyTeam_DriverFIO,
				MPd2.Person_Fin as EmergencyTeam_Driver2FIO,
				MPa1.Person_Fin as EmergencyTeam_Assistant1FIO,
				MPa2.Person_Fin as EmergencyTeam_Assistant2FIO,
				GTR.{$GTR['GeoserviceTransport_id_field']} as GeoserviceTransport_id,
				ETD.EmergencyTeamDuty_id
			FROM
				v_EmergencyTeam ET with(nolock)
				LEFT JOIN v_MedPersonal MPh1 with(nolock) ON( MPh1.MedPersonal_id=ET.EmergencyTeam_HeadShift )
				LEFT JOIN v_MedPersonal MPh2 with(nolock) ON( MPh2.MedPersonal_id=ET.EmergencyTeam_HeadShift2 )
				LEFT JOIN v_MedPersonal MPd1 with(nolock) ON( MPd1.MedPersonal_id=ET.EmergencyTeam_Driver )
				LEFT JOIN v_MedPersonal MPd2 with(nolock) ON( MPd2.MedPersonal_id=ET.EmergencyTeam_Driver2 )
				LEFT JOIN v_MedPersonal MPa1 with(nolock) ON( MPa1.MedPersonal_id=ET.EmergencyTeam_Assistant1 )
				LEFT JOIN v_MedPersonal MPa2 with(nolock) ON( MPa2.MedPersonal_id=ET.EmergencyTeam_Assistant2 )				
				LEFT JOIN v_EmergencyTeamSpec as ETSpec with(nolock) ON(ET.EmergencyTeamSpec_id = ETSpec.EmergencyTeamSpec_id)
				LEFT JOIN v_LpuBuilding LB with(nolock) ON ET.LpuBuilding_id = LB.LpuBuilding_id
				LEFT JOIN v_EmergencyTeamDuty ETD with(nolock) on ET.EmergencyTeam_id = ETD.EmergencyTeam_id
				LEFT JOIN passport.v_MedProductCard MPC with (nolock) on MPC.MedProductCard_id = ET.MedProductCard_id
				LEFT JOIN passport.v_MedProductClass MPCl with (nolock) on MPCl.MedProductClass_id = MPC.MedProductClass_id
				LEFT JOIN {$GTR['GeoserviceTransportRel_object']} GTR with (nolock) ON( GTR.{$GTR['EmergencyTeam_id_field']}=ET.EmergencyTeam_id )
				".ImplodeWherePH( $where )."
		";
		
		
		//var_dump(getDebugSQL($query, $queryParams)); exit;

		$result = $this->db->query( $query, $queryParams );
		
		if ( count( $result->result( 'array' ) ) > 0 ) {
			return $result->result( 'array' );
		} else {
			return false;
		}
	}
	
	
	/**
	 * Получение списка бригад с незавершёнными сменами
	 * @param type $data
	 * @return type
	 */
	public function loadUnfinishedEmergencyTeamList($data) {
		
		//Получаем идентификатор отделения
		
		$this->load->model('CmpCallCard_model4E', 'cccmodel');
		$LpuBuilding_result = $this->cccmodel->getLpuBuildingBySessionData($data);		
		if (!$this->isSuccessful( $LpuBuilding_result ) || !isset($LpuBuilding_result[0])) {
			return $LpuBuilding_result;
		}
		
		$data = array_merge($data, $LpuBuilding_result[0]);
		
		
		//Пока параметры пустые и загружаем все все все бригады с незавершёнными сменами
		$rules = array(
			array( 'field'	=> 'LpuBuilding_id', 'label' => 'Идентификатор подстанции', 'rules'	=> 'required', 'type' => 'int' ),
			array( 'field'	=> 'EmergencyTeam_id', 'label' => 'Идентификатор подстанции', 'rules'	=> '', 'type' => 'int', 'default'=>null )
			
		);
		
		$queryParams = $this->_checkInputData($rules , $data, $err);
		if (!empty($err)) {
			return $err;
		}
		$additionalWhereClause = '';
		if (!empty($queryParams['EmergencyTeam_id'])) {
			$additionalWhereClause = 'OR ET.EmergencyTeam_id = :EmergencyTeam_id';
		}
		
		$query = "
			SELECT DISTINCT
				ET.EmergencyTeam_id,
				ET.EmergencyTeam_Num,
				
				ET.EmergencyTeam_HeadShift,
				ETSpec.EmergencyTeamSpec_id,
				ETSpec.EmergencyTeamSpec_Code,

				MPh1.Person_Fin as EmergencyTeam_HeadShiftFIO,

				ETD.EmergencyTeamDuty_id,
				CONVERT( varchar, CAST( ETD.EmergencyTeamDuty_DTStart as datetime ), 120 ) as EmergencyTeamDuty_DTStart,
				CONVERT( varchar, CAST( ETD.EmergencyTeamDuty_DTFinish as datetime ), 120 ) as EmergencyTeamDuty_DTFinish,

				CASE WHEN ISNULL(ETD.EmergencyTeamDuty_isComesToWork,1) = 1 THEN 'false' ELSE 'true' END AS locked,
				CASE WHEN ISNULL(ETD.EmergencyTeamDuty_isClose,1) = 1 THEN 'false' ELSE 'true' END AS closed,
				
				ET.EmergencyTeam_Num + ' ' + ETSpec.EmergencyTeamSpec_Code + ' '+ ISNULL(MPh1.Person_Fin,'') as EmergencyTeam_Name

			FROM
				v_EmergencyTeam ET with(nolock)
				LEFT JOIN v_MedPersonal MPh1 with(nolock) ON( MPh1.MedPersonal_id=ET.EmergencyTeam_HeadShift )
				LEFT JOIN v_EmergencyTeamSpec as ETSpec with(nolock) ON(ET.EmergencyTeamSpec_id = ETSpec.EmergencyTeamSpec_id)
				LEFT JOIN v_EmergencyTeamDuty ETD with(nolock) on ET.EmergencyTeam_id = ETD.EmergencyTeam_id
			WHERE
				(
					ET.LpuBuilding_id = :LpuBuilding_id
					AND ISNULL(ET.EmergencyTeam_isTemplate,1) = 1
					AND ( ETD.EmergencyTeamDuty_DTFinish) > (dbo.tzGetDate())
				) $additionalWhereClause
			";
		
		return $this->queryResult($query , $queryParams);
	}

	/**
	 * @desc Загрузка возможных статусов бригады
	 * @param array $data
	 * @return array|false
	 */
	public function loadEmergencyTeamStatuses( $data ){
		$sqlArr = array();
		$where = array();

		$where[] = "(ETS.EmergencyTeamStatus_begDT is null or ETS.EmergencyTeamStatus_begDT <= dbo.tzGetDate())";
		$where[] = "(ETS.EmergencyTeamStatus_endDT is null or ETS.EmergencyTeamStatus_endDT >= dbo.tzGetDate())";

		if ( getRegionNick() != 'perm') {
			$query = "
				SELECT
					ETS.EmergencyTeamStatus_id,
					ETS.EmergencyTeamStatus_Code,
					ETS.EmergencyTeamStatus_Name,
					convert(varchar(20), ETS.EmergencyTeamStatus_begDT, 120) as EmergencyTeamStatus_begDT,
					convert(varchar(20), ETS.EmergencyTeamStatus_endDT, 120) as EmergencyTeamStatus_endDT
				FROM
					v_EmergencyTeamStatus as ETS with (nolock)
				".ImplodeWhere( $where )."
			";

			$result = $this->db->query( $query, $sqlArr );

			if ( is_object( $result ) ) {
				return $result->result('array');
			}

			return false;
		}

		if(!empty($data["EmergencyTeamStatus_pid"])) {
			$where[] = "ETSM.EmergencyTeamStatus_pid = :EmergencyTeamStatus_pid";
			$sqlArr["EmergencyTeamStatus_pid"] = $data["EmergencyTeamStatus_pid"];
		}

		$query = "
			SELECT
				ETS.EmergencyTeamStatus_id,
				ETS.EmergencyTeamStatus_Code,
				ETS.EmergencyTeamStatus_Name,
                ETSM.EmergencyTeamStatus_pid as ppp
			FROM
            	v_EmergencyTeamStatusModel as ETSM with (nolock)
                LEFT JOIN v_EmergencyTeamStatus as ETS with (nolock) on ETS.EmergencyTeamStatus_id = ETSM.EmergencyTeamStatus_id
			".ImplodeWhere( $where );

		$result = $this->db->query( $query, $sqlArr );
		
		if ( is_object( $result ) ) {
			return $result->result('array');
		}
		
		return false;
	}
	
	/**
	 * @desc Загрузка истории статусов бригады
	 * @param array $data
	 * @return array|false
	 */
	public function loadEmergencyTeamStatusesHistory( $data ){
		
		$sqlArr = array(
			'EmergencyTeam_id' => $data['EmergencyTeam_id']
		);
		
		$query = "
			SELECT
				ETSH.EmergencyTeamStatusHistory_id,
				ETSH.EmergencyTeamStatus_id,
				CONVERT( varchar, CAST( ETSH.EmergencyTeamStatusHistory_insDT as datetime ), 120 ) as EmergencyTeamStatusHistory_insDT,
				ETS.EmergencyTeamStatus_Code,
				ETS.EmergencyTeamStatus_Name,
				ССС.CmpCallCard_Ngod
			FROM
				v_EmergencyTeamStatusHistory as ETSH with (nolock)
				LEFT JOIN v_EmergencyTeamStatus ETS with(nolock) ON( ETS.EmergencyTeamStatus_id=ETSH.EmergencyTeamStatus_id )
				LEFT JOIN v_CmpCallCard ССС with(nolock) ON( ETSH.CmpCallCard_id=ССС.CmpCallCard_id )
			WHERE 
				ETSH.EmergencyTeam_id = :EmergencyTeam_id
			ORDER BY ETSH.EmergencyTeamStatusHistory_id
		";
		
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
		if ( $this->db->dbdriver == 'postgre' ) {
			$query = "
				UPDATE
					dbo.\"EmergencyTeam\"
				SET
					\"pmUser_delID\"=".((isset($data[ 'pmUser_id' ])) ? $data[ 'pmUser_id' ] : "DEFAULT").",
					\"EmergencyTeam_delDT\"=now(),
					\"EmergencyTeam_Deleted\"=2
				WHERE
					\"EmergencyTeam_id\" = ".((isset($data[ 'EmergencyTeam_id' ])) ? $data[ 'EmergencyTeam_id' ] : "DEFAULT")."
					AND \"Lpu_id\" = ".((isset($data[ 'Lpu_id' ])) ? $data[ 'Lpu_id' ] : "DEFAULT")."
				RETURNING
					null as \"Error_Code\", null as \"Error_Msg\"
			";
			$result = $this->db->query( $query );
		} else {		
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
		}
		if ( is_object( $result ) ) {
			return $result->result('array');
		}
		
		return array(array('Error_Msg' => 'Во время удаления бригады СМП произошла ошибка в базе данных.'));
	}
	
	/**
	 * Удаление списка бригад	 *
	 * @param $data
	 */
	public function deleteEmergencyTeamList($data) {
		if( !array_key_exists( 'EmergencyTeamsList', $data ) || !is_array($data['EmergencyTeamsList']) || !array_key_exists( 'Lpu_id', $data ) || !$data['Lpu_id']){
			return false;
		}
		$deleteEmergencyTeam = $data;
		unset($deleteEmergencyTeam['EmergencyTeamsList']);
		$result = array();
		foreach ($data['EmergencyTeamsList'] as $emergemcy_id) {
			$deleteEmergencyTeam['EmergencyTeam_id'] = $emergemcy_id;
			$n = $this->deleteEmergencyTeam($deleteEmergencyTeam);
			$result[$emergemcy_id] = ( isset($n[0]['Error_Msg']) || isset($n[0]['Err_Msg']) || !$n[0]) ? false : true;
		}
		return $result;
	}
	
	/**
	 * Возвращает идентификатор из справочника статусов бригад по его коду
	 *
	 * @param int $code Код статуса
	 * @return int в случае успеха или false
	 */
	public function getEmergencyTeamStatusIdByCode( $code ){
		$sql = "SELECT TOP 1 EmergencyTeamStatus_id FROM v_EmergencyTeamStatus with(nolock) WHERE EmergencyTeamStatus_Code=:EmergencyTeamStatus_Code";
		$query = $this->db->query( $sql, array(
			'EmergencyTeamStatus_Code' => $code
		) );
		if ( is_object( $query ) ) {
			$result = $query->first_row('array');
			return $result['EmergencyTeamStatus_id'];
		}

		return false;
	}

	/**
	 * Возвращает идентификатор из справочника статусов бригад по его коду
	 *
	 * @param int $code Код статуса
	 * @return int в случае успеха или false
	 */
	public function getEmergencyTeamStatusCodeById( $id ){
		$sql = "SELECT TOP 1 EmergencyTeamStatus_Code FROM v_EmergencyTeamStatus with(nolock) WHERE EmergencyTeamStatus_id=:EmergencyTeamStatus_id";
		$query = $this->db->query( $sql, array(
			'EmergencyTeamStatus_id' => $id
		) );
		if ( is_object( $query ) ) {
			$result = $query->first_row('array');
			return $result['EmergencyTeamStatus_Code'];
		}

		return false;
	}
	
	/**
	 * @desc Изменяет статус бригады СМП
	 * @param array $data
	 * @return array|false
	 */
	public function setEmergencyTeamStatus( $data ){
		if ( !array_key_exists( 'EmergencyTeam_id', $data ) || !$data['EmergencyTeam_id']
		) {
			return false;
		}

		if( (!array_key_exists( 'EmergencyTeamStatus_id', $data ) || !$data['EmergencyTeamStatus_id']) && isset($data['EmergencyTeamStatus_Code'])){

			$data['EmergencyTeamStatus_id'] =  $this->getEmergencyTeamStatusIdByCode( $data['EmergencyTeamStatus_Code'] );
			if(!isset($data['EmergencyTeamStatus_id'])) return false;
		}

		//проверка на текущий статус
		$sqlStat = "
			SELECT TOP 1
				ET.EmergencyTeamStatus_id, ET.EmergencyTeam_id
			FROM
				v_EmergencyTeam as ET with (nolock)
			WHERE
				ET.EmergencyTeam_id = :EmergencyTeam_id
		";

		$resultStat = $this->db->query( $sqlStat, $data );
		$resultStat = $resultStat->result('array');

		if (empty($resultStat[0])){
			return false;
		}
		$resultCall = $this -> getCallOnEmergencyTeam($data);
		//уходим если статус аналогичный и вызов идентичный
		if(!empty($resultStat[0]) && $resultStat[0]["EmergencyTeamStatus_id"]){
			$newCard_id = (!empty($data[ 'CmpCallCard_id' ])) ? $data[ 'CmpCallCard_id' ] : null;
			$curCard_id = !empty($resultCall[0]) && $resultCall[0]["CmpCallCard_id"] > 0 ? $resultCall[0]["CmpCallCard_id"] : null;
			if($resultStat[0]["EmergencyTeamStatus_id"] == $data['EmergencyTeamStatus_id'] && $newCard_id && $newCard_id == $curCard_id ){
				return false;
			}
		}

		$CmpCallCard_IsUpd = '';
		if(isset($data['CmpCallCard_IsUpd'])){
			$CmpCallCard_IsUpd = '@CmpCallCard_IsUpd = :CmpCallCard_IsUpd,';
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
				@ARMType_id = :ARMType_id,
				@CmpCallCard_id = :CmpCallCard_id,
				{$CmpCallCard_IsUpd}

				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			SELECT @Res as EmergencyTeamStatus_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		
		$sqlArr = array(
			'EmergencyTeam_id'				=> $data['EmergencyTeam_id'],
			'EmergencyTeamStatus_id'		=> $data['EmergencyTeamStatus_id'],
			'pmUser_id'						=> $data['pmUser_id'],
			'ARMType_id'					=> (!empty($data['ARMType_id'])) ? $data['ARMType_id'] : null,
			'CmpCallCard_id'				=> (!empty($data[ 'CmpCallCard_id' ])) ? $data[ 'CmpCallCard_id' ] : null,
			'CmpCallCard_IsUpd'				=> (isset($data[ 'CmpCallCard_IsUpd' ])) ? $data[ 'CmpCallCard_IsUpd' ] : null
		);

		$result = $this->db->query( $query, $sqlArr );

		if ( is_object( $result ) ) {
			$resp_save = $result->result('array');

			$data['EmergencyTeamStatus_Code'] =  $this->getEmergencyTeamStatusCodeById( $data['EmergencyTeamStatus_id'] );

			if (isset($data['CmpCallCard_id']) && $data['CmpCallCard_id'] > 0) {
				//установка временных параметров карты в зависимости от статуса бригады
				$this->setTimesCardFromEmergencyTeam(array(
					'EmergencyTeamStatus_id' => $data['EmergencyTeamStatus_id'],
					'CmpCallCard_id' => $data['CmpCallCard_id'],
					'pmUser_id' => $data['pmUser_id']
				));

				$this->load->model('CmpCallCard_model', 'CmpCallCard_model');

				$CmpCallCardEventType_id = $this->CmpCallCard_model->getCmpCallCardEventTypeIdByEmergencyTeamStatusId($data['EmergencyTeamStatus_id']);
				if($CmpCallCardEventType_id){
					$data["CmpCallCardEventType_id"] = $CmpCallCardEventType_id;
					$this->CmpCallCard_model->setCmpCallCardEvent($data);
				}

				// отправляем сообщение в ActiveMQ
				if (!empty($resp_save[0]['EmergencyTeamStatus_id']) && defined('STOMPMQ_MESSAGE_DESTINATION_EMERGENCY')) {
					$this->CmpCallCard_model->checkSendReactionToActiveMQ(array(
						'EmergencyTeamStatus_id' => $resp_save[0]['EmergencyTeamStatus_id'],
						'EmergencyTeam_id' => $data['EmergencyTeam_id'],
						'CmpCallCard_id' => $data['CmpCallCard_id']
					));
				}

				//Статусы "Выехал на вызов" и "Приезд на вызов" дублируем для всех попутных
				if (in_array($data['EmergencyTeamStatus_Code'], array(1, 2))) {
					$callssql = "
								SELECT
									C.*
								FROM v_CmpCallCard C (nolock)
								left join v_CmpCallType CT (nolock) on CT.CmpCallType_id = C.CmpCallType_id
								WHERE CmpCallCard_rid = :CmpCallCard_rid and CT.CmpCallType_Code = 4 and C.CmpCallCardStatusType_id = 2
								ORDER BY C.CmpCallCard_prmDT
								";
					$callsres = $this->db->query($callssql, array('CmpCallCard_rid' => $data['CmpCallCard_id']));

					$calls = $callsres->result('array');

					if (is_array($calls) && count($calls) > 0) {
						foreach ($calls as $call) {
							$prms = array(
								'EmergencyTeam_id' => $data['EmergencyTeam_id'],
								'EmergencyTeamStatus_id' => $data['EmergencyTeamStatus_id'],
								'CmpCallCard_id' => $call['CmpCallCard_id'],
								'pmUser_id' => $data['pmUser_id']
							);
							$this->setEmergencyTeamStatus($prms);

						}
					}
				}

			}

			if(!isset($data['EmergencyTeamStatus_id'])) return false;

			if($data['EmergencyTeamStatus_Code'] == 4){

				$resultStat = $this -> getCallOnEmergencyTeam($sqlArr);

				if($resultStat && $resultStat[0] ){
					$sqlArr['EmergencyTeamStatus_id'] = $resultStat[0]["EmergencyTeamStatus_id"];
					$sqlArr['EmergencyTeamStatus_Code'] = null;
					//чтобы не плодить одинаковые статусы
					if(($resultStat[0]["EmergencyTeamStatus_id"] != $data['EmergencyTeamStatus_id']) && $sqlArr['CmpCallCard_id'] != $resultStat[0]['CmpCallCard_id']){
						$this -> setEmergencyTeamStatus($sqlArr);
					}
				}
				else{
					//установка статуса на "свободно", если нет других вызовов
					$sqlArr['EmergencyTeamStatus_Code'] = 13;
					$sqlArr['EmergencyTeamStatus_id'] = null;
					$sqlArr['CmpCallCard_id'] = null;
					$this -> setEmergencyTeamStatus($sqlArr);
				}
			}

			return $resp_save;
		}
		
		return false;
	}

	/**
	 * @desc Возвращает последний статус текущего вызова на бригаде
	 * @param array $data
	 * @return array|false
	 */
	public function getCallOnEmergencyTeam($data){
		$sqlStat = "
			SELECT TOP 1
				ETSH.EmergencyTeamStatus_id,
				CCC.CmpCallCard_id
			FROM
				v_EmergencyTeamStatusHistory as ETSH with (nolock)
				LEFT JOIN v_EmergencyTeamStatus ETS with(nolock) ON( ETS.EmergencyTeamStatus_id=ETSH.EmergencyTeamStatus_id )
				outer apply (
					select top 1
						c.CmpCallCard_id,
						c.CmpCallCardStatusType_id
					from v_CmpCallCardTeamsAssignmentHistory cctah with (nolock)
					left join v_CmpCallCard c with (nolock) on cctah.CmpCallCard_id = c.CmpCallCard_id
					where cctah.EmergencyTeam_id = ETSH.EmergencyTeam_id
						AND c.CmpCallCardStatusType_id = 2
					order by CmpCallCardTeamsAssignmentHistory_id asc
				) CCC
			WHERE
				ETSH.EmergencyTeam_id = :EmergencyTeam_id
				AND CCC.CmpCallCard_id = ETSH.CmpCallCard_id
			ORDER BY ETSH.EmergencyTeamStatusHistory_insDT DESC
		";
		//echo(getDebugSql($sqlStat, $data)); die;
		$resultStat = $this->db->query( $sqlStat, $data );
		$resultStat = $resultStat->result('array');

		return $resultStat;
	}
	
	/**
	 * @desc Установка временных параметров карты в зависимости от статуса бригады вызова
	 * @param array $data
	 * @return array|false
	 */
	public function setTimesCardFromEmergencyTeam ($data){
		$query = "
			SELECT top 1
				CCC.CmpCallCard_id,
				
				CCC.CmpCallCard_Tper,
				CCC.CmpCallCard_Vyez,
				CCC.CmpCallCard_Przd,
				CCC.CmpCallCard_Tgsp,
				CCC.CmpCallCard_Tsta,
				CCC.CmpCallCard_Tisp,
				CCC.CmpCallCard_Tvzv,
				
				CCC.CmpCallCard_HospitalizedTime,
				CCC.CmpCallCard_IsPoli,
				ETSH.EmergencyTeamStatusHistory_insDT,
				ETS.EmergencyTeamStatus_Code
			FROM v_CmpCallCard CCC with (nolock)
			LEFT JOIN v_EmergencyTeam ET with (nolock) on CCC.EmergencyTeam_id = ET.EmergencyTeam_id
			LEFT JOIN v_EmergencyTeamStatus AS ETS with (nolock) ON( ETS.EmergencyTeamStatus_id=ET.EmergencyTeamStatus_id )
			LEFT JOIN v_EmergencyTeamStatusHistory ETSH with (nolock) ON( ETSH.EmergencyTeamStatus_id=ET.EmergencyTeamStatus_id 
			and  ETSH.EmergencyTeam_id = ET.EmergencyTeam_id )
			WHERE CCC.CmpCallCard_id = :CmpCallCard_id
			order by ETSH.EmergencyTeamStatusHistory_insDT DESC
		";
		
		$cardParams = $this->db->query( $query, array(
			'CmpCallCard_id' => $data[ 'CmpCallCard_id' ]
		) );
		
		if ( !is_object( $cardParams ) ) {
			return false;
		}

		$cardParams = $cardParams->result( 'array' );

		if(is_array($cardParams) && count($cardParams) > 0){
			$cardParams = $cardParams[0];
			
			switch ($cardParams["EmergencyTeamStatus_Code"]) {
				case 1:
					//выехал на вызов
					$cardParams['CmpCallCard_Vyez'] = (isset($cardParams['CmpCallCard_Vyez'])) ? $cardParams['CmpCallCard_Vyez'] : $cardParams['EmergencyTeamStatusHistory_insDT'];
				break;
				case 2:
					//приезд на вызов
					$cardParams['CmpCallCard_Przd'] = (isset($cardParams['CmpCallCard_Przd'])) ? $cardParams['CmpCallCard_Przd'] : $cardParams['EmergencyTeamStatusHistory_insDT'];
				break;
				case 3:
				case 53:
					//Госпитализация (перевозка)
					$cardParams['CmpCallCard_Tgsp'] = (isset($cardParams['CmpCallCard_Tgsp'])) ? $cardParams['CmpCallCard_Tgsp'] : $cardParams['EmergencyTeamStatusHistory_insDT'];
				break;
				case 17:
					//Прибытие в МО 
					$cardParams['CmpCallCard_HospitalizedTime'] = (isset($cardParams['CmpCallCard_HospitalizedTime'])) ? $cardParams['CmpCallCard_HospitalizedTime'] : $cardParams['EmergencyTeamStatusHistory_insDT'];
				break;
				case 13:
				case 4:
					//Конец обслуживания
					$cardParams['CmpCallCard_Tisp'] = (isset($cardParams['CmpCallCard_Tisp'])) ? $cardParams['CmpCallCard_Tisp'] : $cardParams['EmergencyTeamStatusHistory_insDT'];
				break;
				default: 
					return false;
				break;
			}
			$cardParams["pmUser_id"] = $data['pmUser_id'];
			
			$this->load->model( 'CmpCallCard_model4E', 'cardModel' );

			$result = $this->cardModel->saveCmpCallCardTimes( $cardParams );
			
			if ( is_object( $result ) ) {
				return $result->result('array');
			}
		}
		return false;
	}
	
	/**
	 * @desc Изменяет статус бригады СМП на предыдущий у вызова
	 * @param array $data
	 * @return array|false
	 */
	public function cancelEmergencyTeamFromCall( $data ){

		//получаем текущий статус бригады
		$sql = "
			DECLARE
				@ErrCode int,
				@ErrMessage varchar(4000);

				SELECT TOP 1
					ets.EmergencyTeamStatus_Code
				FROM v_EmergencyTeamStatusHistory etsh with(nolock)
					left join v_EmergencyTeamStatus ets on etsh.EmergencyTeamStatus_id = ets.EmergencyTeamStatus_id
				WHERE etsh.EmergencyTeam_id= :EmergencyTeam_id and etsh.CmpCallCard_id = :CmpCallCard_id
				ORDER BY
					etsh.EmergencyTeamStatusHistory_id DESC
			";

		$res = $this->db->query($sql, array('EmergencyTeam_id' => $data['EmergencyTeam_id'], 'CmpCallCard_id' => $data['CmpCallCard_id']));

		$EmergencyTeamStatus = $res->result('array');

		$this->load->model('CmpCallCard_model4E', 'cardModel');
		//если установлен статус и он "в ожидании принятия" либо "принял вызов"
		if (count($EmergencyTeamStatus) > 0 && ($EmergencyTeamStatus[0]['EmergencyTeamStatus_Code'] == 36 || $EmergencyTeamStatus[0]['EmergencyTeamStatus_Code'] == 48)) {

			$params['pmUser_id'] = $data['pmUser_id'];
			$params['CmpCallCard_id'] = $data['CmpCallCard_id'];
			$params['CmpCallCard_Tper'] = null;

			$this->swUpdate('CmpCallCard', $params);

			$presult = $this->cardModel->setEmergencyTeam(array(
				'EmergencyTeam_id' => 0,
				'CmpCallCard_id' => $data['CmpCallCard_id'],
				'pmUser_id' => $data['pmUser_id']
			));

		} else {
			$setTisp = "
				declare @datetime datetime = dbo.tzGetDate()

				update dbo.CmpCallCard with (ROWLOCK)
				set CmpCallCard_Tisp = @datetime,
					pmUser_updID = :pmUser_id,
					CmpCallCard_updDT = @datetime
					where CmpCallCard_id=:CmpCallCard_id
				";
			$this->db->query($setTisp, array(
				'CmpCallCard_id' => $data['CmpCallCard_id'],
				'pmUser_id' => $data['pmUser_id']
			));


			$this->cardModel->setStatusCmpCallCard(array(
				"CmpCallCard_id" => $data['CmpCallCard_id'],
				"CmpCallCardStatusType_Code" => 4,
				"pmUser_id" => $data["pmUser_id"]
			));

			$newcmpcallcard = $this->cardModel->copyCmpCallCard($data);
		}


		$params = array(
			'EmergencyTeam_id'=>$data['EmergencyTeam_id'],
			'EmergencyTeamStatus_Code'=>4, //Статус Конец обслуживания
			'ARMType_id'=>$data['ARMType_id'],
			'CmpCallCard_IsUpd' => 1,
			//'CmpCallCard_id' => $data['CmpCallCard_id'],
			'pmUser_id'=>$data['pmUser_id'],
		);

		$result = $this->setEmergencyTeamStatus($params);

		/*
		$params = array(
			'EmergencyTeam_id'=>$data['EmergencyTeam_id'],
			'EmergencyTeamStatus_Code'=>13, //Статус Свободна
			'ARMType_id'=>$data['ARMType_id'],
			'pmUser_id'=>$data['pmUser_id'],
		);
		$result = $this->setEmergencyTeamStatus($params);
		*/

		// отправляем сообщение в ActiveMQ
		if (!empty($data['EmergencyTeam_id']) && defined('STOMPMQ_MESSAGE_DESTINATION_EMERGENCY')) {
			$this->CmpCallCard_model->checkSendReactionToActiveMQ(array(
				'EmergencyTeam_id' => $data['EmergencyTeam_id'],
				'CmpCallCard_id' => $data['CmpCallCard_id'],
				'resetTeam' => true
			));
		}

		if ( is_array( $result ) ) {
			return $result;
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
				v_EmergencyTeamDuty etd with(nolock)
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
	 * @desc Получение списка смен бригад СМП указанной ЛПУ для графика нарядов
	 * 
	 * @param array $data
	 * @return array|false
	 */
	public function loadEmergencyTeamDutyTimeListGrid( $data ){

		$is_pg = $this->db->dbdriver == 'postgre' ? true : false;

		$sqlArr = array(
			'Lpu_id' => $data['Lpu_id']
		);
		if ( $is_pg ) {
			$where = array(
				"ET.\"Lpu_id\" = :Lpu_id"
			);
		} else {
			$where = array(
				"ET.Lpu_id = :Lpu_id"
			);
		}
		
		if (  ( array_key_exists( 'dateStart', $data ) && $data['dateStart'] != NULL ) &&
			  ( array_key_exists( 'dateFinish', $data ) && $data['dateFinish']!= NULL  )  )
		{

			$sqlArr['dateStart'] = new DateTime( $data[ 'dateStart' ]);
			$sqlArr['dateFinish'] = new DateTime( $data[ 'dateFinish' ].' 23:59:59');

			if ( $is_pg ) {
				$where[] = "etd.\"EmergencyTeamDuty_DTStart\"::date >= :dateStart::date";
				$where[] = "etd.\"EmergencyTeamDuty_DTStart\"::date <= :dateFinish::date";
			} else {
				$where[] = "CAST(etd.EmergencyTeamDuty_DTStart as date) >= :dateStart";
				$where[] = "CAST(etd.EmergencyTeamDuty_DTStart as date) <= :dateFinish";
			}
		}



		if ( !empty( $data[ 'session' ][ 'CurMedService_id' ] ) ) {
			if ( $is_pg ) {
				$lpuBuildingQuery = "
					SELECT
						COALESCE(MS.\"LpuBuilding_id\",0) as \"LpuBuilding_id\"
					FROM
						dbo.\"v_MedService\" MS
					WHERE
						MS.\"MedService_id\" = :MedService_id
				";
			} else {
				$lpuBuildingQuery = "
					SELECT
						ISNULL(MS.LpuBuilding_id,0) as LpuBuilding_id
					FROM
						v_MedService MS with (nolock)
					WHERE
						MS.MedService_id = :MedService_id
				";
			}
			$lpuBuildingResult = $this->db->query( $lpuBuildingQuery, array(
				'MedService_id' => $data[ 'session' ][ 'CurMedService_id' ]
			) );
			if ( is_object( $lpuBuildingResult ) ) {
				$lpuFilter = "";
				// Усли нужно загрузить список нарядов тех подразделений СМП, которые были выбраны  пользователем при входе в АРМ
				if (isset($data['loadSelectSmp'])) {
					// возьмем ИД выбранных пользователем подразделений СМП
					$arrayIdSelectSmp = $this->loadIdSelectSmp();
					if (!empty($arrayIdSelectSmp)) {
						$lpuFilter .= " ET.LpuBuilding_id in (" . implode(",", $arrayIdSelectSmp) . ")";
					} else {
						$lpuFilter .= " 1 = 0";
					}
				}
				
				$lpuBuildingResult = $lpuBuildingResult->result( 'array' );
				if($lpuFilter){
					$where[] = $lpuFilter;
				}elseif ( isset( $lpuBuildingResult[ 0 ] ) && (!empty( $lpuBuildingResult[ 0 ][ 'LpuBuilding_id' ] )) ) {
					if ( $is_pg ) {
						$where[] = "et.\"LpuBuilding_id\" = ".(int)$lpuBuildingResult[ 0 ][ 'LpuBuilding_id' ];
					} else {
						$where[] = "et.LpuBuilding_id = ".(int)$lpuBuildingResult[ 0 ][ 'LpuBuilding_id' ];
					}
					//$queryParams[ 'LpuBuilding_id' ] = $lpuBuildingResult[ 0 ][ 'LpuBuilding_id' ];
				} else {
					//return $lpuBuildingResult;
					return false;
				}
			} else {
				return false;
			}
		}


		if ( $is_pg ) {
			$sql = "
				SELECT DISTINCT
					etd.\"EmergencyTeamDuty_id\",					
					
					MPh1.\"Person_Fin\" as \"EmergencyTeam_HeadShiftFIO\",
					MPh2.\"Person_Fin\" as \"EmergencyTeam_HeadShift2FIO\",
					MPd1.\"Person_Fin\" as \"EmergencyTeam_DriverFIO\",
					MPd2.\"Person_Fin\" as \"EmergencyTeam_Driver2FIO\",
					MPa1.\"Person_Fin\" as \"EmergencyTeam_Assistant1FIO\",
					MPa2.\"Person_Fin\" as \"EmergencyTeam_Assistant2FIO\",
					
					TO_CHAR( etd.\"EmergencyTeam_Head1StartTime\", 'YYYY-MM-DD HH24:MI:SS' ) as \"EmergencyTeam_Head1StartTime\",
					TO_CHAR( etd.\"EmergencyTeam_Head1FinishTime\", 'YYYY-MM-DD HH24:MI:SS' ) as \"EmergencyTeam_Head1FinishTime\",
					TO_CHAR( etd.\"EmergencyTeam_Head2StartTime\", 'YYYY-MM-DD HH24:MI:SS' ) as \"EmergencyTeam_Head2StartTime\",
					TO_CHAR( etd.\"EmergencyTeam_Head2FinishTime\", 'YYYY-MM-DD HH24:MI:SS' ) as \"EmergencyTeam_Head2FinishTime\",
					TO_CHAR( etd.\"EmergencyTeam_Assistant1StartTime\", 'YYYY-MM-DD HH24:MI:SS' ) as \"EmergencyTeam_Assistant1StartTime\",
					TO_CHAR( etd.\"EmergencyTeam_Assistant1FinishTime\", 'YYYY-MM-DD HH24:MI:SS' ) as \"EmergencyTeam_Assistant1FinishTime\",
					TO_CHAR( etd.\"EmergencyTeam_Assistant2StartTime\", 'YYYY-MM-DD HH24:MI:SS' ) as \"EmergencyTeam_Assistant2StartTime\",
					TO_CHAR( etd.\"EmergencyTeam_Assistant2FinishTime\", 'YYYY-MM-DD HH24:MI:SS' ) as \"EmergencyTeam_Assistant2FinishTime\",
					TO_CHAR( etd.\"EmergencyTeam_Driver1StartTime\", 'YYYY-MM-DD HH24:MI:SS' ) as \"EmergencyTeam_Driver1StartTime\",
					TO_CHAR( etd.\"EmergencyTeam_Driver1FinishTime\", 'YYYY-MM-DD HH24:MI:SS' ) as \"EmergencyTeam_Driver1FinishTime\",
					TO_CHAR( etd.\"EmergencyTeam_Driver2StartTime\", 'YYYY-MM-DD HH24:MI:SS' ) as \"EmergencyTeam_Driver2StartTime\",
					TO_CHAR( etd.\"EmergencyTeam_Driver2FinishTime\", 'YYYY-MM-DD HH24:MI:SS' ) as \"EmergencyTeam_Driver2FinishTime\",
					
					et.\"EmergencyTeam_id\",
					lpub.\"LpuBuilding_Name\",
					et.\"EmergencyTeam_Num\",
					--CONVERT( varchar, CAST( etd.EmergencyTeamDuty_DTStart as datetime ), 120 ) as \"EmergencyTeamDuty_DTStart\",
					TO_CHAR( etd.\"EmergencyTeamDuty_DTStart\", 'YYYY-MM-DD HH24:MI:SS' ) as \"EmergencyTeamDuty_DTStart\",
					--CONVERT( varchar, CAST( etd.EmergencyTeamDuty_DTStart as datetime ), 104 ) as EmergencyTeamDuty_DStart,
					TO_CHAR( etd.\"EmergencyTeamDuty_DTStart\", 'YYYY-MM-DD' ) as \"EmergencyTeamDuty_DStart\",
					--CONVERT( varchar, CAST( etd.EmergencyTeamDuty_DTStart as datetime ), 108 ) as EmergencyTeamDuty_TStart,
					TO_CHAR( etd.\"EmergencyTeamDuty_DTStart\", 'HH24:MI:SS' ) as \"EmergencyTeamDuty_TStart\",
					--CONVERT( varchar, CAST( etd.EmergencyTeamDuty_factToWorkDT as datetime ), 120 ) as \"EmergencyTeamDuty_factToWorkDT\",
					TO_CHAR( etd.\"EmergencyTeamDuty_factToWorkDT\", 'YYYY-MM-DD HH24:MI:SS' ) as \"EmergencyTeamDuty_factToWorkDT\",
					--CONVERT( varchar, CAST( etd.EmergencyTeamDuty_DTFinish as datetime ), 120 ) as \"EmergencyTeamDuty_DTFinish\",
					TO_CHAR( etd.\"EmergencyTeamDuty_DTFinish\", 'YYYY-MM-DD HH24:MI:SS' ) as \"EmergencyTeamDuty_DTFinish\",
					--CONVERT( varchar, CAST( etd.EmergencyTeamDuty_DTFinish as datetime ), 104 ) as EmergencyTeamDuty_DFinish,
					TO_CHAR( etd.\"EmergencyTeamDuty_DTFinish\", 'YYYY-MM-DD' ) as \"EmergencyTeamDuty_DFinish\",
					--CONVERT( varchar, CAST( etd.EmergencyTeamDuty_DTFinish as datetime ), 108 ) as EmergencyTeamDuty_TFinish,	
					TO_CHAR( etd.\"EmergencyTeamDuty_DTFinish\", 'HH24:MI:SS' ) as \"EmergencyTeamDuty_TFinish\",
					CASE WHEN COALESCE(etd.\"EmergencyTeamDuty_isComesToWork\",1)=2 THEN 'true' ELSE 'false' END as \"ComesToWork\",
					CASE WHEN COALESCE(etd.\"EmergencyTeamDuty_isClose\",1)=2 THEN 'true' ELSE 'false' END as \"closed\",
					etd.\"EmergencyTeamDuty_Comm\",
					CASE WHEN COALESCE(etd.\"EmergencyTeamDuty_IsCancelledStart\",1)=2 THEN 'true' ELSE 'false' END as \"EmergencyTeamDuty_IsCancelledStart\",
					CASE WHEN COALESCE(etd.\"EmergencyTeamDuty_IsCancelledClose\",1)=2 THEN 'true' ELSE 'false' END as \"EmergencyTeamDuty_IsCancelledClose\",
					etd.\"EmergencyTeamDuty_ChangeComm\"
				FROM
					dbo.\"v_EmergencyTeamDuty\" etd
					LEFT JOIN dbo.\"v_EmergencyTeam\" et ON( et.\"EmergencyTeam_id\"=etd.\"EmergencyTeam_id\" )
					LEFT JOIN dbo.\"v_MedPersonal\" MPh1 ON( MPh1.\"MedPersonal_id\"=et.\"EmergencyTeam_HeadShift\" )
					LEFT JOIN dbo.\"v_MedPersonal\" MPh2 ON( MPh2.\"MedPersonal_id\"=et.\"EmergencyTeam_HeadShift2\" )
					LEFT JOIN dbo.\"v_MedPersonal\" MPd1 ON( MPd1.\"MedPersonal_id\"=et.\"EmergencyTeam_Driver\" )
					LEFT JOIN dbo.\"v_MedPersonal\" MPd2 ON( MPd2.\"MedPersonal_id\"=et.\"EmergencyTeam_Driver2\" )
					LEFT JOIN dbo.\"v_MedPersonal\" MPa1 ON( MPa1.\"MedPersonal_id\"=et.\"EmergencyTeam_Assistant1\" )
					LEFT JOIN dbo.\"v_MedPersonal\" MPa2 ON( MPa2.\"MedPersonal_id\"=et.\"EmergencyTeam_Assistant2\" )
					LEFT JOIN dbo.\"v_LpuBuilding\" lpub ON( lpub.\"LpuBuilding_id\"=et.\"LpuBuilding_id\" )
				".ImplodeWhere( $where )."
			";
		} else {
			$sql = "
				SELECT DISTINCT
					etd.EmergencyTeamDuty_id,
					
					MPh1.Person_Fin as EmergencyTeam_HeadShiftFIO,
					MPh2.Person_Fin as EmergencyTeam_HeadShift2FIO,
					MPd1.Person_Fin as EmergencyTeam_DriverFIO,
					MPd2.Person_Fin as EmergencyTeam_Driver2FIO,
					MPa1.Person_Fin as EmergencyTeam_Assistant1FIO,
					MPa2.Person_Fin as EmergencyTeam_Assistant2FIO,
					
					CONVERT( varchar, CAST( etd.EmergencyTeam_Head1StartTime as datetime ), 108 ) as EmergencyTeam_Head1StartTime,
					CONVERT( varchar, CAST( etd.EmergencyTeam_Head1FinishTime as datetime ), 108 ) as EmergencyTeam_Head1FinishTime,
					CONVERT( varchar, CAST( etd.EmergencyTeam_Head2StartTime as datetime ), 108 ) as EmergencyTeam_Head2StartTime,
					CONVERT( varchar, CAST( etd.EmergencyTeam_Head2FinishTime as datetime ), 108 ) as EmergencyTeam_Head2FinishTime,
					CONVERT( varchar, CAST( etd.EmergencyTeam_Assistant1StartTime as datetime ), 108 ) as EmergencyTeam_Assistant1StartTime,
					CONVERT( varchar, CAST( etd.EmergencyTeam_Assistant1FinishTime as datetime ), 108 ) as EmergencyTeam_Assistant1FinishTime,
					CONVERT( varchar, CAST( etd.EmergencyTeam_Assistant2StartTime as datetime ), 108 ) as EmergencyTeam_Assistant2StartTime,
					CONVERT( varchar, CAST( etd.EmergencyTeam_Assistant2FinishTime as datetime ), 108 ) as EmergencyTeam_Assistant2FinishTime,
					CONVERT( varchar, CAST( etd.EmergencyTeam_Driver1StartTime as datetime ), 108 ) as EmergencyTeam_Driver1StartTime,
					CONVERT( varchar, CAST( etd.EmergencyTeam_Driver1FinishTime as datetime ), 108 ) as EmergencyTeam_Driver1FinishTime,
					CONVERT( varchar, CAST( etd.EmergencyTeam_Driver2StartTime as datetime ), 108 ) as EmergencyTeam_Driver2StartTime,
					CONVERT( varchar, CAST( etd.EmergencyTeam_Driver2FinishTime as datetime ), 108 ) as EmergencyTeam_Driver2FinishTime,
					
					et.EmergencyTeam_id,
					lpub.LpuBuilding_Name,
					et.EmergencyTeam_Num,		
					etd.EmergencyTeamDuty_ChangeComm,
					CONVERT( varchar, CAST( etd.EmergencyTeamDuty_DTStart as datetime ), 120 ) as EmergencyTeamDuty_DTStart,
					CONVERT( varchar, CAST( etd.EmergencyTeamDuty_DTStart as datetime ), 104 ) as EmergencyTeamDuty_DStart,
					CONVERT( varchar, CAST( etd.EmergencyTeamDuty_DTStart as datetime ), 108 ) as EmergencyTeamDuty_TStart,
					CONVERT( varchar, CAST( etd.EmergencyTeamDuty_factToWorkDT as datetime ), 120 ) as EmergencyTeamDuty_factToWorkDT,
					CONVERT( varchar, CAST( etd.EmergencyTeamDuty_factEndWorkDT as datetime ), 120 ) as EmergencyTeamDuty_factEndWorkDT,
					CONVERT( varchar, CAST( etd.EmergencyTeamDuty_DTFinish as datetime ), 120 ) as EmergencyTeamDuty_DTFinish,
					CONVERT( varchar, CAST( etd.EmergencyTeamDuty_DTFinish as datetime ), 104 ) as EmergencyTeamDuty_DFinish,
					CONVERT( varchar, CAST( etd.EmergencyTeamDuty_DTFinish as datetime ), 108 ) as EmergencyTeamDuty_TFinish,	
					
					CONVERT( varchar, CAST( etd.EmergencyTeamDuty_DTStart as datetime ), 104)+' '+CONVERT( varchar, CAST( etd.EmergencyTeamDuty_DTStart as datetime ), 108) as EmergencyTeamDuty_DTStartVis,
					CONVERT( varchar, CAST( etd.EmergencyTeamDuty_DTFinish as datetime ), 104)+' '+CONVERT( varchar, CAST( etd.EmergencyTeamDuty_DTFinish as datetime ), 108) as EmergencyTeamDuty_DTFinishVis,
				
					CASE WHEN COALESCE(etd.EmergencyTeamDuty_isComesToWork,1)=2 THEN 'true' ELSE 'false' END as ComesToWork,
					CASE WHEN COALESCE(etd.EmergencyTeamDuty_isClose,1)=2 THEN 'true' ELSE 'false' END as closed,
					etd.EmergencyTeamDuty_Comm,
					CASE WHEN COALESCE(etd.EmergencyTeamDuty_IsCancelledStart,1)=2 THEN 'true' ELSE 'false' END as EmergencyTeamDuty_IsCancelledStart,
					CASE WHEN COALESCE(etd.EmergencyTeamDuty_IsCancelledClose,1)=2 THEN 'true' ELSE 'false' END as EmergencyTeamDuty_IsCancelledClose,
					ETS.EmergencyTeamStatus_Code,
					CASE WHEN ExistCmpCallCard.CmpCallCard_id IS NULL THEN 0 ELSE 1 END as CountCmpCallCards
				FROM
					v_EmergencyTeamDuty etd
					LEFT JOIN v_EmergencyTeam et with(nolock) ON( et.EmergencyTeam_id=etd.EmergencyTeam_id )
					LEFT JOIN v_EmergencyTeamStatus AS ETS with (nolock) ON( ETS.EmergencyTeamStatus_id=ET.EmergencyTeamStatus_id )
					LEFT JOIN v_MedPersonal MPh1  with(nolock) ON( MPh1.MedPersonal_id=et.EmergencyTeam_HeadShift )
					LEFT JOIN v_MedPersonal MPh2 with(nolock) ON( MPh2.MedPersonal_id=et.EmergencyTeam_HeadShift2 )
					LEFT JOIN v_MedPersonal MPd1 with(nolock) ON( MPd1.MedPersonal_id=et.EmergencyTeam_Driver )
					LEFT JOIN v_MedPersonal MPd2 with(nolock) ON( MPd2.MedPersonal_id=et.EmergencyTeam_Driver2 )
					LEFT JOIN v_MedPersonal MPa1 with(nolock) ON( MPa1.MedPersonal_id=et.EmergencyTeam_Assistant1 )
					LEFT JOIN v_MedPersonal MPa2 with(nolock) ON( MPa2.MedPersonal_id=et.EmergencyTeam_Assistant2 )
					LEFT JOIN v_LpuBuilding lpub with(nolock) ON( lpub.LpuBuilding_id=et.LpuBuilding_id )
					OUTER APPLY (
						Select top 1 CmpCallCard_id FROM v_CmpCallCard selCC with(nolock)
						WHERE selCC.EmergencyTeam_id = etd.EmergencyTeam_id
					) as ExistCmpCallCard
				".ImplodeWhere( $where )."
			";
		}
		
		//var_dump(getDebugSQL($sql, $sqlArr)); exit;
		
		$query = $this->db->query( $sql, $sqlArr );

		if ( is_object( $query ) ) {
			$result = $query->result_array();
			return array(
				'data' => $result,
				'totalCount' => sizeof( $result )
			);
		}
		
		return false;
	}	
	
	
	/**
	 * Отмечает выход на смену бригады СМП
	 * 
	 * @param array $data
	 * @return array|false
	 */
	public function setEmergencyTeamWorkComing( $data ) {

		if ( !array_key_exists( 'EmergencyTeamDuty_id', $data ) || !$data[ 'EmergencyTeamDuty_id' ] ) {
			return array( array( 'Error_Msg' => 'Не указана смена бригады.' ) );
		}

		if ( !array_key_exists( 'EmergencyTeam_id', $data ) || !$data[ 'EmergencyTeam_id' ] ) {
			return array( array( 'Error_Msg' => 'Не указана бригада.' ) );
		}

		if ( !array_key_exists( 'EmergencyTeamDuty_isComesToWork', $data ) || !$data[ 'EmergencyTeamDuty_isComesToWork' ] ) {
			return array( array( 'Error_Msg' => 'Не указан флаг выхода на смену бригады.' ) );
		}

		if ( $this->db->dbdriver == 'postgre' ) {
			$sql = "
				UPDATE dbo.\"EmergencyTeamDuty\" SET
					EmergencyTeamDuty_isComesToWork = :EmergencyTeamDuty_isComesToWork,
					EmergencyTeamDuty_comesToWorkDT = NOW(),
					pmUser_updID = :pmUser_id,
					EmergencyTeamDuty_updDT = NOW()
				WHERE
					EmergencyTeamDuty_id = :EmergencyTeamDuty_id
					AND EmergencyTeam_id = :EmergencyTeam_id
				RETURNING null as Error_Code, null as Error_Msg;
			";
		} else {
			$sql = "
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
		}

		$query = $this->db->query( $sql, array(
			'EmergencyTeamDuty_id' => $data[ 'EmergencyTeamDuty_id' ],
			'EmergencyTeam_id' => $data[ 'EmergencyTeam_id' ],
			'EmergencyTeamDuty_isComesToWork' => $data[ 'EmergencyTeamDuty_isComesToWork' ] == 2 ? 2 : 1,
			'pmUser_id' => $data[ 'pmUser_id' ]
		) );

		if ( is_object( $query ) ) {
			return $query->result_array();
		}

		return array( array( 'Error_Msg' => 'Во время попытки отметить выход на смену для бригады СМП, произошла ошибка в базе данных.' ) );
	}

	/**
	 * @desc Отмечает выход на смену бригады СМП
	 * 
	 * @param array $data
	 * @return array|false
	 */
	function setEmergencyTeamsWorkComingList( $data ) {
		$arr = json_decode($data['EmergencyTeamsDutyTimesAndComing']);
        //var_dump(($arr)); exit;
		$res_array = array();

		foreach ($arr as $key => $value) {
			$EmergencyTeam_id = $value->EmergencyTeam_id;
			$EmergencyTeamDuty_id = $value->EmergencyTeamDuty_id;
			$EmergencyTeamDuty_Comm = !empty($value->EmergencyTeamDuty_Comm)?$value->EmergencyTeamDuty_Comm:null;
			
			$Date_start = $value->EmergencyTeamDuty_DTStart;
			$Date_finish = $value->EmergencyTeamDuty_DTFinish;
			$FactDate_start = $value->EmergencyTeamDuty_factToWorkDT;
			$FactDate_end = $value->EmergencyTeamDuty_factEndWorkDT;

			/*
			if ($value->closed !== null) {
				$closed = ($value->closed) ? 2 : 1;				
				$sqlArr = array(
					'EmergencyTeam_id'	 => $EmergencyTeam_id,
					'EmergencyTeamDuty_id'	 => $EmergencyTeamDuty_id,					
					'EmergencyTeamDuty_isClose'	 => $closed,
					'pmUser_id' => $data['pmUser_id']
				);

				$query = "
					DECLARE
						@ErrCode int,
						@ErrMessage varchar(4000);

					EXEC p_EmergencyTeamDuty_setClose
						@EmergencyTeamDuty_id = :EmergencyTeamDuty_id,
						@EmergencyTeam_id = :EmergencyTeam_id,
						@EmergencyTeamDuty_isClose = :EmergencyTeamDuty_isClose,

						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;

					SELECT @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";
				//var_dump(getDebugSQL($query, $sqlArr)); exit;
				$result = $this->db->query( $query, $sqlArr );
			}	*/		
						
			$ComesToWork = null;			
			if ($value->ComesToWork != null)
				{$ComesToWork = ($value->ComesToWork) ? 2 : 1;}

			$sqlArr = array(
				'EmergencyTeam_id'	 => $EmergencyTeam_id,
				'EmergencyTeamDuty_id'	 => $EmergencyTeamDuty_id,				
				'EmergencyTeamDuty_DTStart' => DateTime::createFromFormat( 'Y-m-d H:i:s', $Date_start ),
				'EmergencyTeamDuty_DTFinish' => DateTime::createFromFormat( 'Y-m-d H:i:s', $Date_finish ),
				'EmergencyTeamDuty_factToWorkDT' => !empty($FactDate_start)?DateTime::createFromFormat( 'Y-m-d H:i:s', $FactDate_start ):null,
				'EmergencyTeamDuty_factEndWorkDT' => !empty($FactDate_end)?DateTime::createFromFormat( 'Y-m-d H:i:s', $FactDate_end ):null,
				'EmergencyTeamDuty_isClose'	 => ($value->closed) ? 2 : 1,
				'ComesToWork'	 => $ComesToWork,
				'pmUser_id' => $data['pmUser_id'],
				'EmergencyTeamDuty_Comm' => !empty($value->EmergencyTeamDuty_Comm) ? $value->EmergencyTeamDuty_Comm : null,
				'EmergencyTeamDuty_ChangeComm' => !empty($value->EmergencyTeamDuty_ChangeComm) ? $value->EmergencyTeamDuty_ChangeComm : null,
				'EmergencyTeamDuty_IsCancelledStart' => !empty($value->EmergencyTeamDuty_IsCancelledStart) ? 2 : null,
				'EmergencyTeamDuty_IsCancelledClose' => !empty($value->EmergencyTeamDuty_IsCancelledClose) ? 2 : null,
			);

			$query = "
				DECLARE
					@ErrCode int,
					@ErrMessage varchar(4000),
					@datetime datetime = dbo.tzGetDate(),				
					@res bigint = :EmergencyTeamDuty_id;

				EXEC p_EmergencyTeamDuty_upd
					@EmergencyTeamDuty_id = :EmergencyTeamDuty_id,
					@EmergencyTeam_id = :EmergencyTeam_id,
					@EmergencyTeamDuty_isComesToWork = :ComesToWork,
					@EmergencyTeamDuty_DTStart = :EmergencyTeamDuty_DTStart,
					@EmergencyTeamDuty_factToWorkDT =:EmergencyTeamDuty_factToWorkDT,
					@EmergencyTeamDuty_factEndWorkDT =:EmergencyTeamDuty_factEndWorkDT,
					@EmergencyTeamDuty_Comm = :EmergencyTeamDuty_Comm,
					@EmergencyTeamDuty_ChangeComm = :EmergencyTeamDuty_ChangeComm,
					@EmergencyTeamDuty_isClose =:EmergencyTeamDuty_isClose,
					@EmergencyTeamDuty_DTFinish =:EmergencyTeamDuty_DTFinish,
					@EmergencyTeamDuty_IsCancelledStart =:EmergencyTeamDuty_IsCancelledStart,
					@EmergencyTeamDuty_IsCancelledClose =:EmergencyTeamDuty_IsCancelledClose,
                    @EmergencyTeamDuty_comesToWorkDT = @datetime,

					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;

				SELECT @ErrCode as Error_Code, @ErrMessage as Error_Msg, @res as res;
			";

			$result = $this->db->query( $query, $sqlArr );
			if ( !is_object( $result ) ) {
				return array( array( 'Err_Msg' => 'Ошибка при сохранении') );
			}
			else{
				$object_res = $result->result('array');				
				array_push($res_array, $object_res[0]["res"]);

				// Если сделага отметка о выходе на смену, установим бригаде статус свободна
				//if ( $ComesToWork == 2 && ( $value->closed === null || !$value->closed ) ) {
				if ( $ComesToWork == 2 ) {
					if ( $EmergencyTeamStatus_id = $this->getEmergencyTeamStatusIdByCode( 13 ) ) {
						$this->setEmergencyTeamStatus( array_merge( $sqlArr, array(
							'EmergencyTeamStatus_id' => $EmergencyTeamStatus_id,
							'ARMType_id' => isset( $data[ 'ARMType' ] ) ? (int)$data[ 'ARMType' ] : null
						) ) );
					}
				}
			}
		}
		return $res_array;
	}
	
	/**
	 * @desc Отмечает закрытие смен бригад СМП
	 * 
	 * @param array $data
	 * @return array|false
	 */
	function setEmergencyTeamsCloseList( $data ) {
		
		$arr = json_decode($data['EmergencyTeamsClose']);
		$res_array = array();
		foreach ($arr as $key => $value) {
			$EmergencyTeam_id = $value->EmergencyTeam_id;
			$EmergencyTeamDuty_id = $value->EmergencyTeamDuty_id;
			
				
			$closed = ($value->closed) ? 2 : 1;
				
			$sqlArr = array(
				'EmergencyTeam_id'	 => $EmergencyTeam_id,
				'EmergencyTeamDuty_id'	 => $EmergencyTeamDuty_id,
				'EmergencyTeamDuty_isClose'	 => $closed,
				'pmUser_id' => $data['pmUser_id']
			);
			
			$query = "
				DECLARE
					@ErrCode int,
					@ErrMessage varchar(4000);

				EXEC p_EmergencyTeamDuty_setClose
					@EmergencyTeamDuty_id = :EmergencyTeamDuty_id,
					@EmergencyTeam_id = :EmergencyTeam_id,
					@EmergencyTeamDuty_isClose = :EmergencyTeamDuty_isClose,

					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;

				SELECT @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";

			$result = $this->db->query( $query, $sqlArr );
			if ( !is_object( $result ) ) {
				return array( array( 'Err_Msg' => 'Ошибка при сохранении') );
			}			
		}
		return  $result->result('array');
	}
	
	
	
	
	/**
	 * @desc Возращает список для справочника списка бригад СМП
	 *
	 * @param array $data
	 * @return array|false
	 */
	function loadEmergencyTeamCombo( $data ) {
		
		// Выводим только бригады состоящих в ЛПУ пользователя
		if ( !array_key_exists( 'Lpu_id', $data ) || !$data['Lpu_id'] ) {
			return array( array( 'Err_Msg' => 'Не указан идентификатор ЛПУ') );
		}
		
        $query = "
        	SELECT DISTINCT
				et.EmergencyTeam_id,
				et.EmergencyTeam_Num as EmergencyTeam_Code,
				LTRIM( RTRIM( mp.Person_FIO ) ) as EmergencyTeam_Name
			FROM
				v_EmergencyTeam et with(nolock)
				INNER JOIN v_MedPersonal mp with(nolock) ON( mp.MedPersonal_id=et.EmergencyTeam_HeadShift )
			WHERE
				et.Lpu_id = :Lpu_id
			ORDER BY
				et.EmergencyTeam_Num
    	";

		$sqlArr = array(
			'Lpu_id' => $data['Lpu_id'],
		);
		
		$result = $this->db->query( $query, $sqlArr );

        if ( is_object( $result ) ) {
            return $result->result('array');
        }
	}

	/**
	 * @desc Возращает список для справочника списка бригад СМП
	 *
	 * @param array $data
	 * @return array|false
	 */
	public function loadEmergencyTeamComboWithWialonID($data) {
		
		$rules = array(
			array('field'=>'Lpu_id','label'	=> 'Идентификатор ЛПУ','rules'	=> 'required','type'	=> 'id')
		);
		
		$queryParams = $this->_checkInputData($rules , $data, $error);
		if (!$queryParams || !empty($error)) {
			return $err;
		}
		
		$filter = "ET.Lpu_id = :Lpu_id";
		
		$queryParams = array();
		
		$queryParams['Lpu_id'] = $data['Lpu_id'];
		
		if ( !empty( $data[ 'dateStart' ] ) ) {
			$queryParams[ 'dateStart' ] = $data[ 'dateStart' ];
			$filter .= " AND CAST(ETD.EmergencyTeamDuty_DTStart as date) = :dateStart";
		}
		
		if ( !empty( $data[ 'workComing' ] ) && $data[ 'workComing' ] ) {
			$filter .= " AND ISNULL(ETD.EmergencyTeamDuty_isClose,1) = 1 AND ETD.EmergencyTeamDuty_isComesToWork=2";
		}

        $query = "
        	SELECT DISTINCT
			ETD.EmergencyTeamDuty_DTFinish,
				ET.EmergencyTeam_id,
				ET.EmergencyTeam_Num as EmergencyTeam_Code,
				LTRIM( RTRIM( MP.Person_FIO ) ) as EmergencyTeam_Name,
				ETW.WialonEmergencyTeamId as WialonID,
				ETD.EmergencyTeamDuty_isClose
			FROM
				v_EmergencyTeam et with(nolock)
				INNER JOIN v_MedPersonal MP with(nolock) ON( MP.MedPersonal_id=ET.EmergencyTeam_HeadShift )
				LEFT JOIN v_EmergencyTeamWialonRel ETW with(nolock) ON( ETW.EmergencyTeam_id=ET.EmergencyTeam_id )
				LEFT JOIN v_EmergencyTeamDuty ETD with(nolock) on ET.EmergencyTeam_id = ETD.EmergencyTeam_id
			WHERE
				{$filter}
			ORDER BY
				ET.EmergencyTeam_Num
    	";

		return $this->queryResult($query , $queryParams);
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
				v_EmergencyTeamProposalLogic ETPL with(nolock)
				LEFT JOIN v_Sex S with(nolock) ON( ETPL.Sex_id=S.Sex_id)
				INNER JOIN v_CmpReason CR with(nolock) ON( ETPL.CmpReason_id = CR.CmpReason_id)
				OUTER APPLY (
					SELECT DISTINCT
					(
						SELECT
							ETS2.EmergencyTeamSpec_Code + ' '
						FROM 
							v_EmergencyTeamProposalLogicRule ETPLR with(nolock)
							INNER JOIN v_EmergencyTeamSpec ETS2 with(nolock) on (ETPLR.EmergencyTeamSpec_id = ETS2.EmergencyTeamSpec_id)
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
					v_EmergencyTeamSpec ETS with(nolock)
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
					v_EmergencyTeamProposalLogicRule ETPLR with(nolock)
					INNER JOIN v_EmergencyTeamSpec ETS with(nolock) ON( ETS.EmergencyTeamSpec_id = ETPLR.EmergencyTeamSpec_id)
					
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
				v_EmergencyTeamProposalLogic ETPL with(nolock)
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
							v_EmergencyTeamProposalLogicRule ETPLR with(nolock)
						WHERE
							ETPLR.EmergencyTeamProposalLogic_id = @EmergencyTeamProposalLogic_id
						) > 0)
					BEGIN

					SET @countBlock = @countBlock+1;

					SELECT DISTINCT
						@EmergencyTeamProposalLogicRule_id = ETPLR.EmergencyTeamProposalLogicRule_id
					FROM
						v_EmergencyTeamProposalLogicRule ETPLR with(nolock)
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
				v_EmergencyTeamProposalLogicRule ETPLR with(nolock)
			WHERE
				ETPLR.EmergencyTeamProposalLogic_id = :EmergencyTeamProposalLogic_id
		";
	}
	/**
	 * Метод сохранения прихода медикамента на укладку наряда СМП
	 * @param type $data
	 * @return type
	 */
	public function saveEmergencyTeamDrugPackMove($data) {
		
		//
		// Получаем идентификатор движения
		//
		if (!empty($data['DocumentUcStr_id']) && empty($data['EmergencyTeamDrugPackMove_id'])) {
			//Если передан идентификатор строки учетного документа, значит строка пополнения склада (EmergencyTeamDrugPackMove_id) 
			//уже существует, необходимо получить её идентификатор и дальнейшие действия проводить с ней
			$ETDrugPackMove_data = $this->getEmergencyTeamDrugPackMoveIdByDocumentUcStr($data);
			if (!$this->isSuccessful( $ETDrugPackMove_data )) {
				return $ETDrugPackMove_data;
			}
			
			$data['EmergencyTeamDrugPackMove_id'] = (!empty($ETDrugPackMove_data[0]['EmergencyTeamDrugPackMove_id']))? $ETDrugPackMove_data[0]['EmergencyTeamDrugPackMove_id'] : null;
		}
		
		//
		// Получаем идентификатор укладки
		//
		if ( empty( $data[ 'EmergencyTeamDrugPack_id' ] ) ) {
			
			if ( !empty( $data[ 'EmergencyTeamDrugPackMove_id' ] ) ) {
				$record_result = $this->_getDrugPackByDrugPackMove( $data ) ;
			} elseif ( !empty( $data[ 'EmergencyTeam_id' ] ) && !empty( $data[ 'Drug_id' ] ) ) {
				$record_result = $this->getDrugPackByDrugAndEmergencyTeam( $data ) ;
			}
			
			if ( !empty( $record_result ) && !$this->isSuccessful( $record_result ) ) {
				return $record_result ;
			}

			//
			// Если укладки не существует, создаём её
			//
			if  (empty( $record_result[ 0 ][ 'EmergencyTeamDrugPack_id' ] ) ) {
				
				$newDrugPack_result = $this->saveEmergencyTeamDrugPack($data);
				
				if (!$this->isSuccessful( $newDrugPack_result )) {
					return $newDrugPack_result;
				}
				
				if (empty($newDrugPack_result[0]['EmergencyTeamDrugPack_id'])) {
					return $this->createError(NULL, 'Ошибка создания записи укладки');
				}
					
				$data['EmergencyTeamDrugPack_id'] = $newDrugPack_result[0]['EmergencyTeamDrugPack_id'];
				
			} else {
				$data[ 'EmergencyTeamDrugPack_id' ] =  $record_result[ 0 ][ 'EmergencyTeamDrugPack_id' ] ;
			}
			
		}
		
		
		$rules = array(
			array( 'field' => 'EmergencyTeamDrugPackMove_id' , 'label' => 'Идентификатор движения медикамента в укладке' , 'rules' => '' , 'type' => 'id' , 'default' => null ) ,
			array( 'field' => 'EmergencyTeamDrugPack_id' , 'label' => 'Идентификатор укладки' , 'rules' => 'required' , 'type' => 'id' ) ,
			array( 'field' => 'DocumentUcStr_id' , 'label' => 'Идентификатор строки документа' , 'rules' => '' , 'type' => 'id' ) ,
			array( 'field' => 'CmpCallCard_id' , 'label' => 'Идентификатор строки документа' , 'rules' => '' , 'type' => 'id' ) ,
			array( 'field' => 'EmergencyTeamDrugPackMove_Quantity' , 'label' => 'Количество доз' , 'rules' => 'required' , 'type' => 'float' ) ,
			array( 'field' => 'pmUser_id' , 'rules' => 'required' , 'label' => 'Идентификатор пользователя' , 'type' => 'id' ) ,
		);
		
		$queryParams = $this->_checkInputData($rules , $data, $err);
		if (!empty($err)) {
			return $err;
		}
		
		$this->db->trans_begin();
		
		//
		// Сохраняем приход медикаментов на укладку бригады
		//
		
		$procedure = (empty( $data[ 'EmergencyTeamDrugPackMove_id' ] )) ? 'p_EmergencyTeamDrugPackMove_ins' : 'p_EmergencyTeamDrugPackMove_upd' ;
		
		$query = "
			
			DECLARE
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);			
			SET @Res = :EmergencyTeamDrugPackMove_id;
			
			EXEC {$procedure}
				@EmergencyTeamDrugPackMove_id = @Res output,
				@DocumentUcStr_id = :DocumentUcStr_id,
				@CmpCallCard_id = :CmpCallCard_id,
				@EmergencyTeamDrugPackMove_Quantity = :EmergencyTeamDrugPackMove_Quantity,
				@EmergencyTeamDrugPack_id = :EmergencyTeamDrugPack_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			SELECT @Res as EmergencyTeamDrugPackMove_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		
		$ETDPM_result =  $this->queryResult($query , $queryParams);
		
		if ( !$this->isSuccessful( $ETDPM_result ) ) {
			$this->db->trans_rollback();
			return $ETDPM_result;
		}
		
		//
		// Получаем суммарное количество медикаментов в укладке
		//
		
		$count_result = $this->getDrugCountFromDrugPackMoveByDrugPackId($data);
		
		if (!$this->isSuccessful( $count_result )) {
			$this->db->trans_rollback();
			return $count_result;
		}
		
		if ( empty($count_result[0]['DrugCount']) || empty($count_result[0]['EmergencyTeam_id']) || empty($count_result[0]['Drug_id']) ) {
			$this->db->trans_rollback();
			return $this->createError(null,'Ошибка получения суммарного остатка медикамента в укладке');
		}
		
		$data['EmergencyTeamDrugPack_Total'] = $count_result[0]['DrugCount'];
		$data['EmergencyTeam_id'] = $count_result[0]['EmergencyTeam_id'];
		$data['Drug_id'] = $count_result[0]['Drug_id'];
		
		//
		// Обновляем остатки медикамента в укладке
		//
		
		$updateDrugPackResult = $this->saveEmergencyTeamDrugPack($data);
		
		if ( !$this->isSuccessful( $updateDrugPackResult ) ) {
			$this->db->trans_rollback();
			return $updateDrugPackResult;
		}
		$this->db->trans_commit();
		return $updateDrugPackResult;
		
	}
	
	/**
	 * Актуализация остатков медикамента в укладке СМП
	 * @param type $data
	 * @return type
	 */
	public function saveEmergencyTeamDrugPack($data) {
		
		if (empty($data['pmUser_id'])) {
			$session_params = getSessionParams();
			$data['pmUser_id'] = $session_params['pmUser_id'];
		}
		
		$rules = array(
			array( 'field' => 'EmergencyTeam_id', 'label' => 'Бригада', 'rules' => '', 'type' => 'id'),
			array( 'field' => 'Drug_id', 'label' => 'Медикамент', 'rules' => '', 'type' => 'id'),
			array( 'field' => 'EmergencyTeamDrugPack_Total' , 'label' => 'Количество доз' , 'rules' => '' , 'type' => 'float', 'default' => 0 ),
			array( 'field' => 'EmergencyTeamDrugPack_id', 'label' => 'Укладка', 'rules' => '', 'type' => 'id', 'default'=>null ),
			array( 'field' => 'pmUser_id' , 'rules' => 'required' , 'label' => 'Идентификатор пользователя' , 'type' => 'id' ),
		);
		
		$queryParams = $this->_checkInputData( $rules , $data , $err ) ;
		if ( !empty( $err ) ) {
			return $err ;
		}

		$procedure = (empty( $queryParams[ 'EmergencyTeamDrugPack_id' ] )) ? 'p_EmergencyTeamDrugPack_ins' : 'p_EmergencyTeamDrugPack_upd' ;


		$query = "
			DECLARE
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000)

			SET @Res = :EmergencyTeamDrugPack_id;

			EXEC " . $procedure . "
				@EmergencyTeamDrugPack_id = @Res output,
				@EmergencyTeam_id = :EmergencyTeam_id,
				@Drug_id = :Drug_id,
				@EmergencyTeamDrugPack_Total = :EmergencyTeamDrugPack_Total,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			SELECT @Res as EmergencyTeamDrugPack_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		return $this->queryResult( $query , $queryParams ) ;
		
	}
	
	/**
	 * Получение записи остатков по идентификатору бригады и медикамента
	 * @param type $data
	 * @return type
	 */
	protected function getDrugPackByDrugAndEmergencyTeam( $data ) {
		$rules = array(
			array( 'field' => 'EmergencyTeam_id' , 'label' => 'Бригада' , 'rules' => 'required' , 'type' => 'id' ) ,
			array( 'field' => 'Drug_id' , 'label' => 'Медикамент' , 'rules' => 'required' , 'type' => 'id' ) ,
		) ;

		$queryParams = $this->_checkInputData( $rules , $data , $err ) ;
		if ( !empty( $err ) ) {
			return $err ;
		}

		$query = "
			SELECT
				ETDP.EmergencyTeamDrugPack_id
			FROM
				v_EmergencyTeamDrugPack ETDP with (nolock)
			WHERE
				ETDP.EmergencyTeam_id = :EmergencyTeam_id
				AND ETDP.Drug_id = :Drug_id
		" ;

		return  $this->queryResult( $query , $queryParams ) ;
		
	}

	/**
	 * Получение суммарное количество медикамента по всем операциям списания и зачисления
	 * @param type $data
	 * @return type
	 */
	public function getDrugCountFromDrugPackMoveByDrugPackId( $data ) {

		$rules = array(
			array( 'field' => 'EmergencyTeamDrugPack_id', 'label' => 'Укладка', 'rules' => 'required', 'type' => 'id' ),
		) ;

		$queryParams = $this->_checkInputData( $rules , $data , $err ) ;
		if ( !empty( $err ) ) {
			return $err ;
		}

		$query = "
			SELECT
				ISNULL(SUM(ETDPM.EmergencyTeamDrugPackMove_Quantity),0) as DrugCount,
				ETDP.EmergencyTeam_id,
				ETDP.Drug_id
			FROM
				v_EmergencyTeamDrugPackMove ETDPM with (nolock)
				left join v_EmergencyTeamDrugPack ETDP with (nolock) on ETDP.EmergencyTeamDrugPack_id = ETDPM.EmergencyTeamDrugPack_id
			WHERE
				ETDPM.EmergencyTeamDrugPack_id = cast( :EmergencyTeamDrugPack_id as bigint )
			GROUP BY
				ETDP.EmergencyTeam_id,
				ETDP.Drug_id
		" ;
		
		return $this->queryResult( $query , $queryParams ) ;
	}

	/**
	 * Метод получения идентификатора движения медикамента для наряда СМП по идентификатору строки учетного документа списания
	 * @param type $data
	 * @return type
	 */
	protected function getEmergencyTeamDrugPackMoveIdByDocumentUcStr($data) {
		
		$rules = array(
			array( 'field'	=> 'DocumentUcStr_id', 'label' => 'Идентификатор строки документа', 'rules'	=> 'required', 'type' => 'id'),
		);
		
		$queryParams = $this->_checkInputData($rules , $data, $err);
		if (!empty($err)) {
			return $err;
		}
		
		$query = "
			SELECT TOP 1
				ETDPM.EmergencyTeamDrugPackMove_id,
				ETDPM.EmergencyTeamDrugPackMove_Quantity,
				ETDPM.EmergencyTeamDrugPack_id,
				ETDP.EmergencyTeam_id,
				ETDP.Drug_id
			FROM
				v_EmergencyTeamDrugPackMove ETDPM with (nolock)
				left join v_EmergencyTeamDrugPack ETDP with (nolock) on ETDP.EmergencyTeamDrugPack_id = ETDPM.EmergencyTeamDrugPack_id
			WHERE
				ETDPM.DocumentUcStr_id = :DocumentUcStr_id
			";
		
		return $this->queryResult($query , $queryParams);
		
	}
	/**
	 * Метод удаления движения медикамента на укладку наряда СМП
	 * @param type $data
	 * @return type
	 */
	public function deleteEmergencyTeamPackMoveByDocumentUcStr( $data ) {

		//
		// Поулчаем идентификатор движения медикамента по идетификатору строки документа списания
		//
		
		$EmergencyTeamDrugPackMoveId_result = $this->getEmergencyTeamDrugPackMoveIdByDocumentUcStr( $data ) ;
		
		if ( !$this->isSuccessful( $EmergencyTeamDrugPackMoveId_result ) ) {

			return $EmergencyTeamDrugPackMoveId_result ;
			
		} elseif ( empty( $EmergencyTeamDrugPackMoveId_result[ 0 ][ 'EmergencyTeamDrugPackMove_id' ] ) ) {

			// Если запись не найдена, значит уже была удалена

			return array( array( 'success' => true , 'Error_Msg' => '' ) ) ;
		}
		
		
		// Пополняем входные параметры данными об укладке: EmergencyTeamDrugPack_id, EmergencyTeam_id, Drug_id

		$data = array_merge( $data , $EmergencyTeamDrugPackMoveId_result[ 0 ] ) ;

		//
		// Удаляем запись о движении
		//
		return $this->deleteEmergencyTeamPackMove( $data ) ;

	}
	/**
	 * Метод получения идентификатора укладки по идентификатору движения укладки
	 * @param type $data
	 * @return type
	 */
	protected function _getDrugPackByDrugPackMove($data) {
		
		$rules = array(
			array( 'field'	=> 'EmergencyTeamDrugPackMove_id', 'label' => 'Идентификатор подстанции', 'rules' => 'required', 'type' => 'int' )
		);
		
		$queryParams = $this->_checkInputData($rules , $data, $err);
		if (!empty($err)) {
			return $err;
		}
		
		$get_drug_pack_id_query = "
			SELECT 
				ETDPM.EmergencyTeamDrugPack_id
			FROM
				v_EmergencyTeamDrugPackMove ETDPM with (nolock)
			WHERE
				ETDPM.EmergencyTeamDrugPackMove_id = :EmergencyTeamDrugPackMove_id
				";
		
		return $this->queryResult($get_drug_pack_id_query , $queryParams);
		
	}
	/**
	 * Метод удаления записи о приходе
	 * @param type $data
	 * @return type
	 */
	protected function deleteEmergencyTeamPackMove($data) {
		
		$rules = array(
			array( 'field'	=> 'EmergencyTeamDrugPackMove_id', 'label' => 'Идентификатор подстанции', 'rules' => 'required', 'type' => 'int' )
		);
		
		$queryParams = $this->_checkInputData($rules , $data, $err);
		if (!empty($err)) {
			return $err;
		}
		
		//
		//Получаем идентификатор укладки
		//
		
		$drug_pack_id_result = $this->_getDrugPackByDrugPackMove($queryParams);
		
		if (!$this->isSuccessful( $drug_pack_id_result )) {
			return $drug_pack_id_result;
		}
		
		if (empty($drug_pack_id_result[0]['EmergencyTeamDrugPack_id'])) {
			return $this->createError(false,'Не удалось получить укладку по записи.');
		}
		$data['EmergencyTeamDrugPack_id'] = $drug_pack_id_result[0]['EmergencyTeamDrugPack_id'];
		
		$this->db->trans_begin();
		
		//
		//Удаляем запись об изменении укладки
		//
		
		$delete_query = "
			DECLARE
				@ErrCode int,
				@ErrMessage varchar(4000)

			EXEC p_EmergencyTeamDrugPackMove_del
				@EmergencyTeamDrugPackMove_id = :EmergencyTeamDrugPackMove_id

			SELECT @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		
		$delete_result = $this->queryResult($delete_query , $queryParams);
		
		
		if ( !$this->isSuccessful( $delete_result ) ) {
			$this->db->trans_rollback() ;
			return $delete_result ;
		}

		//
		// Получаем суммарное количество медикаментов в укладке
		//
		
		$count_result = $this->getDrugCountFromDrugPackMoveByDrugPackId( $data ) ;

		if ( !$this->isSuccessful( $count_result ) ) {
			$this->db->trans_rollback() ;
			return $count_result ;
		}
		
		
		if ( empty($count_result[0]['DrugCount']) || empty($count_result[0]['EmergencyTeam_id']) || empty($count_result[0]['Drug_id']) ) {
			$this->db->trans_rollback();
			return $this->createError(null,'Ошибка получения суммарного остатка медикамента в укладке');
		}
		
		$data['EmergencyTeamDrugPack_Total'] = $count_result[0]['DrugCount'];
		$data['EmergencyTeam_id'] = $count_result[0]['EmergencyTeam_id'];
		$data['Drug_id'] = $count_result[0]['Drug_id'];

		//
		// Обновляем остатки медикамента в укладке
		//
		
		$updateDrugPackResult = $this->saveEmergencyTeamDrugPack( $data ) ;

		if ( !$this->isSuccessful( $updateDrugPackResult ) ) {
			$this->db->trans_rollback() ;
			return $updateDrugPackResult ;
		}
		$this->db->trans_commit() ;
		return $updateDrugPackResult ;
	}
	
	
	/**
	 * Возвращает данные по оперативной обстановке бригад СМП для арма ЦМК
	 * Для списка подчиненных подстанций СМП
	 *
	 * @param array $data
	 * @return array or false
	 */
	public function loadEmergencyTeamsARMCenterDisaster( $data ) {
		$where = array();
		$params = array();

		//Получаем наименование полей и объектов геосервиса в зависимости от региона (и, возможно, в будущем - МО)
		$GTR = $this->_defineGeoserviceTransportRelQueryParams($data);

		$where[] = "COALESCE(ET.EmergencyTeam_isTemplate,1)=1";

		// Вышел на смену
		$where[] = "ETD.EmergencyTeamDuty_isComesToWork=2";
		$where[] = "@getdate BETWEEN ETD.EmergencyTeamDuty_DTStart AND ETD.EmergencyTeamDuty_DTFinish";
		$where[] = "ETD.EmergencyTeamDuty_factToWorkDT is not null";
		$where[] = "LB.LpuBuildingType_id = 27";

		$user = pmAuthUser::find($_SESSION['login']);
		$settings = unserialize($user->settings);

		if( !empty ($data['Lpu_ids']) ) {
				$where[] = "L.Lpu_id in (".$data['Lpu_ids'].")";
		} else {
			$Lpu_ids = $this->getSelectedLpuId();
			if ( $Lpu_ids ) {
				$where[] = "L.Lpu_id in (".implode(',',$Lpu_ids).')';
			} else {
				return false;
			}
		}
		
		$sql = "
			declare @getdate datetime = dbo.tzGetdate();
			
			SELECT --DISTINCT
				-- select
				ET.EmergencyTeam_id,
				ET.EmergencyTeam_Num,
				ET.EmergencyTeam_CarNum,
				ET.EmergencyTeam_CarBrand,
				ET.EmergencyTeam_CarModel,
				ET.EmergencyTeam_PortRadioNum,
				ET.EmergencyTeam_GpsNum,
				ET.LpuBuilding_id,
				L.Lpu_Nick,
				ET.EmergencyTeamStatus_id,
				ETS.EmergencyTeamStatus_Code,
				ET.EmergencyTeamSpec_id,
				convert(varchar(19), (GETDATE() - LAstStatus.EmergencyTeamStatusHistory_insDT), 120) as statusTime,
				CONVERT( varchar, CAST( ETD.EmergencyTeamDuty_DTStart as datetime ), 120 ) as EmergencyTeamDuty_DTStart,
				CONVERT( varchar, CAST( ETD.EmergencyTeamDuty_DTFinish as datetime ), 120 ) as EmergencyTeamDuty_DTFinish,
				ETD.EmergencyTeamDuty_id,					
				
				ETSpec.EmergencyTeamSpec_Name,
				ETSpec.EmergencyTeamSpec_Code,
				CC.CmpCallCard_id,
				CC.CmpCallCard_Numv,
				
				CC.CmpCallCard_Ngod,
                CC.Person_Age,
                COALESCE(PS.Person_Surname, CC.Person_SurName, '') + ' ' + COALESCE(PS.Person_Firname, case when rtrim(CC.Person_FirName) = 'null' then '' else CC.Person_FirName end, '') + ' ' + COALESCE(PS.Person_Secname, case when rtrim(CC.Person_SecName) = 'null' then '' else CC.Person_SecName end, '') as Person_FIO,
                RTRIM(case when CR.CmpReason_id is not null then CR.CmpReason_Code+'. ' else '' end + ISNULL(CR.CmpReason_Name, '')) as CmpReason_Name,
				CASE WHEN COALESCE(ET.EmergencyTeam_isOnline,1)=2 THEN 'online' ELSE 'offline' END as EmergencyTeam_isOnline,
				COALESCE(GTR.{$GTR['GeoserviceTransport_id_field']}, MPC.MedProductCard_Glonass) as GeoserviceTransport_id,
				( CASE WHEN COALESCE(ET.EmergencyTeam_HeadShift2,0)!=0 THEN 1 ELSE 0 END )
				+ ( CASE WHEN COALESCE(ET.EmergencyTeam_Assistant1,0)!=0 THEN 1 ELSE 0 END )
				+ ( CASE WHEN COALESCE(ET.EmergencyTeam_Assistant2,0)!=0 THEN 1 ELSE 0 END ) as medPersonCount,

				L.Lpu_Nick+' / '+isnull(LB.LpuBuilding_Nick, LB.LpuBuilding_Name)+' / '+ET.EmergencyTeam_Num as EmergencyTeamNum,

				ETS.EmergencyTeamStatus_Name,
				CASE
					WHEN ETS.EmergencyTeamStatus_Code IN(13,21,36) THEN 'green'
					WHEN ETS.EmergencyTeamStatus_Code IN(8,9,23) THEN 'gray'
					ELSE 'black'
				END as EmergencyTeamStatus_Color,
				--MP.Person_Fin as HeadDocFio,
				case when LB.LpuBuilding_Nick is not null then LB.LpuBuilding_Nick else LB.LpuBuilding_Name end as EmergencyTeamBuildingName,

				--CountCmpCallCards,
				(case when (ET.EmergencyTeam_HeadShift is not null and ET.EmergencyTeam_HeadShift != 0) then 1 else 0 end)+
					(case when (ET.EmergencyTeam_HeadShift2 is not null and ET.EmergencyTeam_HeadShift2 != 0) then 1 else 0 end) as EmergencyTeam_HeadShiftCount,
                (case when (ET.EmergencyTeam_Assistant1 is not null and ET.EmergencyTeam_Assistant1 != 0) then 1 else 0 end)+
					(case when (ET.EmergencyTeam_Assistant2 is not null and ET.EmergencyTeam_Assistant2 != 0) then 1 else 0 end) as EmergencyTeam_AssistantCount,
				--MS.MedService_id
				case when City.KLCity_Name is not null then 'г. '+City.KLCity_Name else '' end +
				case when Town.KLTown_FullName is not null then
				case when City.KLCity_Name is not null then ', ' else '' end 
				+isnull(LOWER(Town.KLSocr_Nick)+'. ','') + Town.KLTown_Name else '' end +
				case when Street.KLStreet_FullName is not null then ', '+LOWER(Street.KLSocr_Nick)+'. '+Street.KLStreet_Name else '' end +
				case when CC.CmpCallCard_Dom is not null then ', д.'+CC.CmpCallCard_Dom else '' end +
				case when CC.CmpCallCard_Korp is not null then ', к.'+CC.CmpCallCard_Korp else '' end +
				case when CC.CmpCallCard_Kvar is not null then ', кв.'+CC.CmpCallCard_Kvar else '' end +
				case when CC.CmpCallCard_Room is not null then ', ком. '+CC.CmpCallCard_Room else '' end +
				case when UAD.UnformalizedAddressDirectory_Name is not null then ', Место: '+UAD.UnformalizedAddressDirectory_Name else '' end as Address_Name,
				CASE WHEN ( COALESCE(CC.Person_Age,0) = 0 AND COALESCE(CC.Person_BirthDay, PS.Person_BirthDay , 0) !=0 ) THEN
					CASE WHEN DATEDIFF(m,ISNULL(CC.Person_BirthDay, PS.Person_BirthDay), getdate() ) > 12 THEN
						convert(  varchar(20), DATEDIFF( yy,ISNULL(CC.Person_BirthDay, PS.Person_BirthDay), getdate() )  ) + ' лет'
					ELSE
						CASE WHEN DATEDIFF(d,ISNULL(CC.Person_BirthDay, PS.Person_BirthDay) ,dbo.tzGetDate() ) <=30 THEN
							convert(  varchar(20), DATEDIFF(d,ISNULL(CC.Person_BirthDay, PS.Person_BirthDay), getdate() ) ) + ' дн. '
						ELSE
							convert(  varchar(20), DATEDIFF( m,ISNULL(CC.Person_BirthDay, PS.Person_BirthDay), getdate() )  ) + ' мес.'
						END
					END
				 ELSE
				 	CASE WHEN COALESCE(CC.Person_Age,0) = 0 THEN ''
					ELSE convert(  varchar(20), CC.Person_Age ) + ' лет'
					END
				 END as personAgeText,
				CC.CmpCallCard_isExtra,
				case when EmergencyTeamStatus_Code = 3
					then lpuHid.Lpu_Nick
					else ''
				end as CCCLpu_Nick
				-- end select
			FROM
				-- from
				v_EmergencyTeam as ET with (nolock)
				INNER JOIN v_LpuBuilding LB with (nolock) on LB.LpuBuilding_id = ET.LpuBuilding_id
				INNER JOIN v_Lpu L with (nolock) on L.Lpu_id = LB.Lpu_id
				LEFT JOIN v_EmergencyTeamStatus AS ETS with (nolock) ON( ETS.EmergencyTeamStatus_id=ET.EmergencyTeamStatus_id )
				--LEFT JOIN v_EmergencyTeamStatusHistory as ETSH with (nolock) ON( ETS.EmergencyTeamStatus_id=ETSH.EmergencyTeamStatus_id )
				outer apply (
					SELECT TOP 1 *
					FROM v_EmergencyTeamStatusHistory with(nolock)
					WHERE 
						EmergencyTeam_id = ET.EmergencyTeam_id					
					ORDER BY
						EmergencyTeamStatusHistory_insDT DESC
				) as LastStatus
				LEFT JOIN v_EmergencyTeamDuty AS ETD with (nolock) ON( ETD.EmergencyTeam_id=ET.EmergencyTeam_id)
				--LEFT JOIN v_MedPersonal as MP with(nolock) ON( MP.MedPersonal_id=ET.EmergencyTeam_HeadShift and MP.Lpu_id = LB.Lpu_id )
				LEFT JOIN v_EmergencyTeamSpec as ETSpec with(nolock) ON(ET.EmergencyTeamSpec_id = ETSpec.EmergencyTeamSpec_id)
				outer apply (
					SELECT TOP 1 C2.* 
					FROM v_CmpCallCard as C2 with(nolock)
					WHERE 
						C2.EmergencyTeam_id = ET.EmergencyTeam_id 
						AND C2.CmpCallCardStatusType_id = 2
					ORDER BY C2.CmpCallCard_updDT DESC
				) CC
				left join v_Lpu CCLpu with(nolock) on CCLpu.Lpu_id = CC.Lpu_id
				left join v_Lpu lpuHid with(nolock) on lpuHid.Lpu_id = CC.Lpu_hid
                left join v_CmpReason CR with (nolock) on CR.CmpReason_id = CC.CmpReason_id
                left join v_PersonState PS with (nolock) on PS.Person_id = CC.Person_id
				LEFT JOIN v_UnformalizedAddressDirectory UAD with(nolock) on UAD.UnformalizedAddressDirectory_id = CC.UnformalizedAddressDirectory_id
				LEFT JOIN v_KLCity City with(nolock) on City.KLCity_id = CC.KLCity_id
				LEFT JOIN v_KLTown Town with(nolock) on Town.KLTown_id = CC.KLTown_id
				LEFT JOIN v_KLStreet Street with(nolock) on Street.KLStreet_id = CC.KLStreet_id
				LEFT JOIN passport.v_MedProductCard MPC with (nolock) on MPC.MedProductCard_id = ET.MedProductCard_id
				LEFT JOIN {$GTR['GeoserviceTransportRel_object']} as GTR with (nolock) ON( GTR.{$GTR['EmergencyTeam_id_field']}=ET.EmergencyTeam_id )
				--OUTER APPLY (Select COUNT(1) as CountCmpCallCards  FROM v_CmpCallCard selCC with(nolock) WHERE ET.LpuBuilding_id = selCC.LpuBuilding_id) as CountCmpCallCards
				-- end from
			".ImplodeWherePH( $where )."
			ORDER BY	
				-- order by
				EmergencyTeam_Num
				-- end order by
		";
	
		//var_dump(getDebugSQL($sql, $params)); exit;
		//echo getDebugSQL($sql, $params);exit;
		if (isset($_GET['dbg']) && $_GET['dbg'] == '1') 
			var_dump(getDebugSQL($sql, $params));
		
		$query = $this->db->query( $sql, $params );
		if ( is_object( $query ) ) {
			return $query->result_array();
		}
		return false;
	}
	
	
		/**
	 * Возвращает данные по оперативной обстановке бригад СМП для арма ЦМК
	 * Для списка подчиненных подстанций СМП
	 *
	 * @param array $data
	 * @return array or false
	 */
	public function loadCmpCallCardsARMCenterDisaster( $data ) {
		$where = array();
		$select = '';
		$join = '';

		$params = array();
		//Получаем наименование полей и объектов геосервиса в зависимости от региона (и, возможно, в будущем - МО)
		$GTR = $this->_defineGeoserviceTransportRelQueryParams($data);

		$lpuTable = 'LB';
		//вызовы нмп
		if( !empty($data['isNmp']) ) {
			$select = "
				nmpLpu.Lpu_Nick as NmpLpu_Nick,
				nmpLpu.Lpu_id as NmpLpu_id,
				rtrim(ToDT.PMUser_Name) + ' ' + convert(varchar(10), cast(ToDT.ToDT as datetime), 104) + ' ' + convert(varchar(5), cast(ToDT.ToDT as datetime), 108) as PPDUser_Name,
				CASE WHEN (CCCST.CmpCallCardStatusType_Code = 2) THEN
					CASE WHEN EPL.EvnPL_setDT is null
						THEN convert(varchar(20), cast(CCC.CmpCallCard_updDT as datetime), 120)
						ELSE convert(varchar(20), cast(EPL.EvnPL_setDT as datetime), 120)
					END
				END as CallAcceptanceDT,
			";
			//принятые и переданные. первичные и повторы и консультация старшего врача

			$join = '
				LEFT JOIN v_Lpu nmpLpu with(nolock) on nmpLpu.Lpu_id = CCC.Lpu_ppdid
				OUTER APPLY (select top 1 * from v_EvnPL where CmpCallCard_id = CCC.CmpCallCard_id) as EPL
				outer apply(
					select top 1
						CmpCallCardStatus_insDT as ToDT,
						PU.PMUser_Name
					from
						v_CmpCallCardStatus with(nolock)
						left join v_PmUser PU with(nolock) on PU.PMUser_id = pmUser_insID
					where
						CmpCallCardStatusType_id = 2 and CmpCallCard_id = CCC.CmpCallCard_id
					order by CmpCallCardStatus_insDT desc
				) as ToDT
			';
			$where[] = "CCT.CmpCallType_Code in ( 1, 2 ) ";
			$where[] = "CCCST.CmpCallCardStatusType_Code IN (1, 2, 10)";
			$where[] = "nmpLpu.Lpu_id is not null";
			$lpuTable = 'CCC';
		} else {
			//вызовы СМП только первичные вызовы
			$select = "
				lpuHid.Lpu_Nick as LpuHid_Nick,
				CCCD.Duplicate_Count,
			";
			$join = '
				left join v_Lpu lpuHid with(nolock) on lpuHid.Lpu_id = CCC.Lpu_hid
				outer apply(
					select
						COUNT(CCCDouble.CmpCallCard_id) as Duplicate_Count
					from
						v_CmpCallCard CCCDouble with (nolock)
					left join v_CmpCallCardStatusType CCCSTDouble with(nolock) on CCCSTDouble.CmpCallCardStatusType_id = CCCDouble.CmpCallCardStatusType_id
					where
						CCCDouble.CmpCallCard_rid = CCC.CmpCallCard_id
						and CCCSTDouble.CmpCallCardStatusType_Code = 9
						and COALESCE(CCCDouble.CmpCallCard_IsActiveCall, 1) != 2
				) CCCD
			';
			$where[] = "CCT.CmpCallType_Code in ( 1,4,9 ) ";
			$where[] = "CCCST.CmpCallCardStatusType_Code IN (1, 2, 7, 8, 10)";
		}

		if( !empty ($data['Lpu_ids']) ) {
			$where[] = $lpuTable.".Lpu_id in (".$data['Lpu_ids'].")";
		} else {
			$Lpu_ids = $this->getSelectedLpuId();
			if ( $Lpu_ids ) {
				$where[] = $lpuTable.".Lpu_id in (".implode(',',$Lpu_ids).')';
			} else {
				return false;
			}
		}

		//Скрываем вызовы принятые в ППД
		$where[] = "COALESCE (CCC.CmpCallCard_IsReceivedInPPD, 1) != 2";

		//Временно только открытые карты
		$where[] = "COALESCE (CCC.CmpCallCard_IsOpen, 1) = 2";

		if(!empty($data['begDate']) && !empty($data['endDate'])){
			$where[] = 'CCC.CmpCallCard_prmDT BETWEEN :begDate AND :endDate';
			$begDate = date_create($data["begDate"]);
			$endDate = date_create($data['endDate']);
			$params['begDate'] = $begDate->format('Y-m-d').' ' . ((!empty($data['begTime']) ? $data['begTime'] : ' 00:00'));
			$params['endDate'] = $endDate->format('Y-m-d').' ' . ((!empty($data['endTime']) ? $data['endTime'] : ' 23:59'));
		}else {
			$where[] = "DATEDIFF(hh, CCC.CmpCallCard_prmDT, @getdate) <= 24";
		}

		$user = pmAuthUser::find($_SESSION['login']);
		$settings = unserialize($user->settings);


		$sql = "
			-- variables
			declare @getdate datetime = dbo.tzGetDate();
			-- end variables

			SELECT DISTINCT
				$select
				CCC.CmpCallCard_id,
				convert(varchar(20), cast(CCC.CmpCallCard_prmDT as datetime), 113) as CmpCallCard_prmDT,
				RTRIM(case when CR.CmpReason_id is not null then (CR.CmpReason_Code+' ') else '' end + ISNULL(CR.CmpReason_Name, '')) as CmpReason_Name,
				CCC.Person_Age,
				CCCST.CmpCallCardStatusType_Code,
				CCCST.CmpCallCardStatusType_Name,
				convert(varchar(19), (GETDATE() - CCCS.CmpCallCardStatus_insDT), 120) as statusTime,
				convert(varchar(19), CCCS.CmpCallCardStatus_insDT, 120) as CmpCallCardStatus_insDT,
				ET.EmergencyTeam_id,
				(case when (ET.EmergencyTeam_Num is not null) then ET.EmergencyTeam_Num+' ' else '' end)+
				(case when (MP.Person_Fin is not null) then MP.Person_Fin else '' end) as EmergencyTeam_Name,
				ET.EmergencyTeamStatus_id,
				ETSpec.EmergencyTeamSpec_Code,
				ETS.EmergencyTeamStatus_Name,
				CCC.LpuBuilding_id,
				CCC.CmpCallCard_CallLng,
                CCC.CmpCallCard_CallLtd,
				case when LB.LpuBuilding_Nick is not null then LB.LpuBuilding_Nick else LB.LpuBuilding_Name end as LpuBuildingName,
				
				case when City.KLCity_Name is not null then 'г. '+City.KLCity_Name else '' end +
				case when Town.KLTown_FullName is not null then
				case when City.KLCity_Name is not null then ', ' else '' end 
				+isnull(LOWER(Town.KLSocr_Nick)+'. ','') + Town.KLTown_Name else '' end +
				case when Street.KLStreet_FullName is not null then ', '+LOWER(Street.KLSocr_Nick)+'. '+Street.KLStreet_Name else '' end +
				case when SecondStreet.KLStreet_FullName is not null then', '+LOWER(SecondStreet.KLSocr_Nick)+'. '+SecondStreet.KLStreet_Name else '' end +
				case when CCC.CmpCallCard_Dom is not null then ', д.'+CCC.CmpCallCard_Dom else '' end +
				case when CCC.CmpCallCard_Korp is not null then ', к.'+CCC.CmpCallCard_Korp else '' end +
				case when CCC.CmpCallCard_Kvar is not null then ', кв.'+CCC.CmpCallCard_Kvar else '' end +
				case when CCC.CmpCallCard_Room is not null then ', ком. '+CCC.CmpCallCard_Room else '' end +
				case when UAD.UnformalizedAddressDirectory_Name is not null then ', Место: '+UAD.UnformalizedAddressDirectory_Name else '' end as Adress_Name,

				COALESCE(PS.Person_Surname, CCC.Person_SurName, '') + ' ' 
				+ COALESCE(PS.Person_Firname, case when rtrim(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end, '') + ' ' 
				+ COALESCE(PS.Person_Secname, case when rtrim(CCC.Person_SecName) = 'null'  then '' else CCC.Person_SecName end, '') as Person_FIO,
				L.Lpu_id,
				L.Lpu_Nick,
				isnull(CCC.MedService_id,0) as MedService_id,
				isnull(MS.MedService_Nick,'-') as MedService_Nick,
				CASE WHEN COALESCE(CCC112.CmpCallCard112_id,CCC112rid.CmpCallCard112_id, 1) = 1 THEN 1 ELSE 2 END as is112,
				CCC.CmpCallCard_isExtra,
				CASE WHEN ( COALESCE(CCC.Person_Age,0) = 0 AND COALESCE(CCC.Person_BirthDay, PS.Person_BirthDay , 0) !=0 ) THEN
					CASE WHEN DATEDIFF(m,ISNULL(CCC.Person_BirthDay, PS.Person_BirthDay), @getdate ) > 12 THEN
						convert(  varchar(20), DATEDIFF( yy,ISNULL(CCC.Person_BirthDay, PS.Person_BirthDay), @getdate )  ) + ' лет'
					ELSE
						CASE WHEN DATEDIFF(d,ISNULL(CCC.Person_BirthDay, PS.Person_BirthDay) ,dbo.tzGetDate() ) <=30 THEN
							convert(  varchar(20), DATEDIFF(d,ISNULL(CCC.Person_BirthDay, PS.Person_BirthDay), @getdate ) ) + ' дн. '
						ELSE
							convert(  varchar(20), DATEDIFF( m,ISNULL(CCC.Person_BirthDay, PS.Person_BirthDay), @getdate )  ) + ' мес.'
						END
				   	END
				 ELSE
				 	CASE WHEN COALESCE(CCC.Person_Age,0) = 0 THEN ''
					ELSE convert(  varchar(20), CCC.Person_Age ) + ' лет'
					END
				 END as personAgeText,
				CCC.Diag_uid,
				D.Diag_Code + '.' + D.Diag_Name as Diag_Name,
				D.Diag_Code,
				CCC.Lpu_hid,
				CCC.CmpCallCard_Numv,
				CCC.CmpCallCard_Ngod,
				CCT.CmpCallType_Name
			FROM
				v_CmpCallCard CCC with(nolock)
				left join v_CmpCallCard CCCrid (nolock) on CCC.CmpCallCard_rid = CCCrid.CmpCallCard_id
				LEFT JOIN v_CmpCallCardStatusType CCCST with(nolock) on CCC.CmpCallCardStatusType_id = CCCST.CmpCallCardStatusType_id
				LEFT JOIN v_CmpReason CR with (nolock) on CR.CmpReason_id = CCC.CmpReason_id
				LEFT JOIN v_KLRgn RGN with(nolock) on RGN.KLRgn_id = CCC.KLRgn_id
				LEFT JOIN v_KLSubRgn SRGN with(nolock) on SRGN.KLSubRgn_id = CCC.KLSubRgn_id
				LEFT JOIN v_KLCity City with(nolock) on City.KLCity_id = CCC.KLCity_id
				LEFT JOIN v_KLTown Town with(nolock) on Town.KLTown_id = CCC.KLTown_id
				LEFT JOIN v_KLStreet Street with(nolock) on Street.KLStreet_id = CCC.KLStreet_id
				LEFT JOIN v_UnformalizedAddressDirectory UAD with(nolock) on UAD.UnformalizedAddressDirectory_id = CCC.UnformalizedAddressDirectory_id
				LEFT JOIN v_KLStreet SecondStreet (nolock) on SecondStreet.KLStreet_id = CCC.CmpCallCard_UlicSecond
				LEFT JOIN v_PersonState PS with (nolock) on PS.Person_id = CCC.Person_id
				LEFT JOIN v_EmergencyTeam ET with(nolock) on CCC.EmergencyTeam_id = ET.EmergencyTeam_id
				LEFT JOIN v_MedPersonal as MP with(nolock) ON( MP.MedPersonal_id=ET.EmergencyTeam_HeadShift )
				LEFT JOIN v_EmergencyTeamStatus AS ETS with (nolock) ON( ETS.EmergencyTeamStatus_id=ET.EmergencyTeamStatus_id )
				LEFT JOIN v_EmergencyTeamSpec as ETSpec with(nolock) ON(ET.EmergencyTeamSpec_id = ETSpec.EmergencyTeamSpec_id)
				LEFT JOIN v_LpuBuilding LB with (nolock) on LB.LpuBuilding_id = CCC.LpuBuilding_id
				left join v_CmpCallType CCT with (nolock) on CCT.CmpCallType_id = CCC.CmpCallType_id
				LEFT JOIN v_Lpu L with(nolock) on L.Lpu_id = CCC.Lpu_id
				LEFT JOIN v_MedService MS with(nolock) on MS.MedService_id = CCC.MedService_id
				left join v_CmpCallCard112 CCC112 (nolock) on CCC.CmpCallCard_id = CCC112.CmpCallCard_id
				left join v_CmpCallCard112 CCC112rid (nolock) on CCCrid.CmpCallCard_id = CCC112rid.CmpCallCard_id
				left join v_Diag D with(nolock) on D.Diag_id = CCC.Diag_gid
				OUTER APPLY (SELECT top 1 * FROM v_CmpCallCardStatus with(nolock) where CmpCallCard_id = CCC.CmpCallCard_id order by CmpCallCardStatus_insDT desc) as CCCS
				$join
			".ImplodeWherePH( $where )."
		";


	
		//var_dump(getDebugSQL($sql, $params)); exit;
		if (isset($_GET['dbg']) && $_GET['dbg'] == '1') 
			var_dump(getDebugSQL($sql, $params));
		
		$query = $this->db->query( $sql, $params );
		if ( is_object( $query ) ) {
			return $query->result_array();
		}
		return false;
	}
	
	
	/**
	 * Возвращает данные для раздела "Наряды"
	 */
	public function loadOutfitsARMCenterDisaster ( $data ) {

		$queryParams = array();

		if ( !empty( $data[ 'dateStart' ] ) && !empty( $data[ 'dateFinish' ] ) ) {
			$date_start = DateTime::createFromFormat( 'd.m.Y', $data[ 'dateStart' ] );
			$date_finish = DateTime::createFromFormat( 'd.m.Y', $data[ 'dateFinish' ] );

			$queryParams[ 'dateStart' ] = $date_start->format( 'Y-m-d' );
			$queryParams[ 'dateFinish' ] = $date_finish->format( 'Y-m-d' );

			
			$filter = "	AND CAST(ETD.EmergencyTeamDuty_DTStart as date) >= :dateStart AND CAST(ETD.EmergencyTeamDuty_DTStart as date) <= :dateFinish";
			
		} else {
			$filter = " AND (( ETD.EmergencyTeamDuty_DTStart) < (@getdate)) AND (( ETD.EmergencyTeamDuty_DTFinish) > (@getdate))";
		}

		if(!empty($data['Lpu_ids'])) {
			$filter .= " AND ET.Lpu_id in (".$data['Lpu_ids'].")";
		} else {
			$Lpu_ids = $this->getSelectedLpuId();
			if ( $Lpu_ids ) {
				$filter .= " AND ET.Lpu_id in (".implode(',',$Lpu_ids).')';
			} else {
				return false;
			}
		}

		if( !empty( $data[ 'dateFactStart' ] ) ) {
			$begDate = date_create($data["dateFactStart"]);
			$queryParams['dateFactStart']  = $begDate->format('Y-m-d').' '.((!empty($data['timeFactStart']) ? $data['timeFactStart'] : '00:00'));
			$filter .= ' AND ETD.EmergencyTeamDuty_factToWorkDT >= :dateFactStart';
			$queryParams[ 'is1900' ] = "1900-01-01 00:00:00";

			if( !empty( $data [ 'dateFactFinish' ])) {
				$endDate = date_create($data["dateFactFinish"]);
				$queryParams['dateFactFinish'] = $endDate->format('Y-m-d').' ' . ((!empty($data['timeFactFinish']) ? $data['timeFactFinish'] : ' 23:59'));
				$filter .= ' AND ETD.EmergencyTeamDuty_factEndWorkDT <= :dateFactFinish';
			}
		}

		$query = "
			declare @getdate datetime = dbo.tzGetdate();

			SELECT
				ET.EmergencyTeam_id,
				ET.EmergencyTeam_Num,
				ET.EmergencyTeam_GpsNum,
				ET.EmergencyTeam_CarNum,
				LB.LpuBuilding_Nick,
				LB.LpuBuilding_Name,
				ET.LpuBuilding_id,
				ET.Lpu_id,
				ET.CMPTabletPC_id,
				ET.EmergencyTeam_Phone,
				ET.EmergencyTeamSpec_id,
				isnull(MPC.MedProductCard_BoardNumber,'') + ' ' + isnull(MPCl.MedProductClass_Model,'') + ' ' + isnull(AD.AccountingData_RegNumber,'') as MedProduct_Name,
				ETD.EmergencyTeamDuty_id,
				CONVERT( varchar, CAST( ETD.EmergencyTeamDuty_DTStart as datetime ), 120 ) as EmergencyTeamDuty_DTStart,
				CONVERT( varchar, CAST( ETD.EmergencyTeamDuty_DTFinish as datetime ), 120 ) as EmergencyTeamDuty_DTFinish,
				CONVERT( varchar, CAST( ETD.EmergencyTeamDuty_factToWorkDT as datetime ), 120 ) as EmergencyTeamDuty_factToWorkDT,
				CONVERT( varchar, CAST( ETD.EmergencyTeamDuty_factEndWorkDT as datetime ), 120 ) as EmergencyTeamDuty_factEndWorkDT,
				L.Lpu_Nick,
				L.Lpu_id
			FROM
				v_EmergencyTeam ET with(nolock)
				LEFT JOIN v_LpuBuilding LB with(nolock) ON ET.LpuBuilding_id = LB.LpuBuilding_id
				LEFT JOIN v_EmergencyTeamDuty ETD with(nolock) on ET.EmergencyTeam_id = ETD.EmergencyTeam_id
				LEFT JOIN passport.v_MedProductCard MPC with (nolock) on MPC.MedProductCard_id = ET.MedProductCard_id
				LEFT JOIN passport.v_MedProductClass MPCl with (nolock) on MPCl.MedProductClass_id = MPC.MedProductClass_id
				LEFT JOIN passport.v_AccountingData AD with (nolock) on MPC.MedProductCard_id = AD.MedProductCard_id
				LEFT JOIN v_Lpu L with(nolock) on L.Lpu_id = ET.Lpu_id
			WHERE
				(1=1)
				".$filter."
		";
		

		$result = $this->db->query( $query, $queryParams );
		
		if ( count( $result->result( 'array' ) ) > 0 ) {
			return $result->result( 'array' );
		} else {
			return false;
		}
	}

	/**
	* Возвращает количество кол-во врачей, бригад, вызовов СМП для арма ЦМК
	* Для списка подчиненных подстанций СМП
	*
	* @param array $data
	* @return array or false
	*/
	public function getCountsTeamsCallsAndDocsARMCenterDisaster( $data ) {
		$where = array();
		$params = array();

		$where[] = "LB.LpuBuildingType_id = 27";
		$user = pmAuthUser::find($_SESSION['login']);
		$settings = unserialize($user->settings);

		if( !empty ($data['Lpu_ids']) ) {
			$where[] = "L.Lpu_id in (".$data['Lpu_ids'].")";
		} else {
			$Lpu_ids = $this->getSelectedLpuId();
			if ( $Lpu_ids ) {
				$where[] = "L.Lpu_id in (".implode(',',$Lpu_ids).')';
			} else {
				return false;
			}
		}

		$sql = "
			with
			LpuBuildingList as (
				select
					LB.LpuBuilding_id,
					L.Lpu_Nick+' / '+isnull(LB.LpuBuilding_Nick, LB.LpuBuilding_Name) as LpuBuilding_Name
				from 
					v_LpuBuilding LB with(nolock)
					inner join v_Lpu L with(nolock) on L.Lpu_id = LB.Lpu_id
				".ImplodeWherePH( $where )."
			),
			CountEmergencyTeams as (
				Select 
					selEt.LpuBuilding_id,
					COUNT(selET.EmergencyTeam_id) as CountEmergencyTeams,
					sum(case when selETS.EmergencyTeamStatus_Code IN(13,21,36) then 1 else 0 end) as TeamsStatusFree_Count,
					sum(case when selETS.EmergencyTeamStatus_Code IN(8,9,23) then 1 else 0 end) as TeamsStatusUnaccepted_Count,
					sum(case when selETS.EmergencyTeamStatus_Code IN(8,9,23,13,21,36) then 0 else 1 end) as TeamsStatusDuty_Count,
					sum( (case when (selET.EmergencyTeam_HeadShift is not null and selET.EmergencyTeam_HeadShift != 0) then 1 else 0 end)+
						(case when (selET.EmergencyTeam_HeadShift2 is not null and selET.EmergencyTeam_HeadShift2 != 0) then 1 else 0 end) ) as Team_HeadShiftCount,
					sum( (case when (selET.EmergencyTeam_Assistant1 is not null and selET.EmergencyTeam_Assistant1 != 0) then 1 else 0 end)+
						(case when (selET.EmergencyTeam_Assistant2 is not null and selET.EmergencyTeam_Assistant2 != 0) then 1 else 0 end) ) as Team_AssistantCount
				FROM 
					v_EmergencyTeam selET with(nolock)
					LEFT JOIN v_EmergencyTeamDuty selETD with (nolock) ON (selETD.EmergencyTeam_id = selET.EmergencyTeam_id) 
					LEFT JOIN v_EmergencyTeamStatus selETS with (nolock) ON( selETS.EmergencyTeamStatus_id=selET.EmergencyTeamStatus_id )
				WHERE 
					selET.LpuBuilding_id in (select LpuBuilding_id from LpuBuildingList) and
					COALESCE (selET.EmergencyTeam_isTemplate, 1) = 1 and
					selETD.EmergencyTeamDuty_isComesToWork = 2 and
					dbo.tzGetDate() BETWEEN selETD.EmergencyTeamDuty_factToWorkDT AND
					selETD.EmergencyTeamDuty_DTFinish
				group by
					selEt.LpuBuilding_id
			),
			CountCmpCallCards as (
				SELECT
					selCC.LpuBuilding_id,
					COUNT(CmpCallCard_id) as CountCmpCallCards,
					sum(case when COALESCE(selCC.EmergencyTeam_id,0)!=0 then 1 else 0 end) as CallsAccepted,
					sum(case when COALESCE(selCC.EmergencyTeam_id,0)=0 then 1 else 0 end) as CallsNoAccepted
				FROM 
					v_CmpCallCard selCC with(nolock)
					LEFT JOIN v_CmpCallCardStatusType CCCS on selCC.CmpCallCardStatusType_id = CCCS.CmpCallCardStatusType_id
				WHERE 
					selCC.LpuBuilding_id in (select LpuBuilding_id from LpuBuildingList)
					AND COALESCE (selCC.CmpCallCard_IsReceivedInPPD, 1) != 2
					AND COALESCE (selCC.CmpCallCard_IsOpen, 1) = 2
					AND selCC.CmpCallType_id = 2
					AND CCCS.CmpCallCardStatusType_Code IN (1, 2, 7, 8, 10)
				group by
					selCC.LpuBuilding_id
			)
			SELECT 
				LB.LpuBuilding_id,
				LB.LpuBuilding_Name,
				isnull(CountEmergencyTeams, 0) as CountEmergencyTeams,
				isnull(TeamsStatusFree_Count, 0) as TeamsStatusFree_Count,
				isnull(TeamsStatusUnaccepted_Count, 0) as TeamsStatusUnaccepted_Count,
				isnull(TeamsStatusDuty_Count, 0) as TeamsStatusDuty_Count,
				isnull(Team_HeadShiftCount, 0) as Team_HeadShiftCount,
				isnull(Team_AssistantCount, 0) as Team_AssistantCount,
				isnull(CountCmpCallCards, 0) as CountCmpCallCards,
				isnull(CallsAccepted, 0) as CallsAccepted,
				isnull(CallsNoAccepted, 0) as CallsNoAccepted
			FROM 
				LpuBuildingList LB with(nolock)
				left join CountEmergencyTeams cet on cet.LpuBuilding_id = LB.LpuBuilding_id
				left join CountCmpCallCards cccc on cccc.LpuBuilding_id = LB.LpuBuilding_id
		";
	
		//var_dump(getDebugSQL($sql, $params)); exit;
		//echo getDebugSQL($sql, $params);exit;
		
		if (isset($_GET['dbg']) && $_GET['dbg'] == '1') 
			var_dump(getDebugSQL($sql, $params));
		
		$query = $this->db->query( $sql, $params );
		if ( is_object( $query ) ) {
			return $query->result_array();
		}
		return false;
	}

	/**
	 * Получение параметров для проигрывания трека виалон
	 * @param $data
	 * @return array
	 */
	public function getCmpCallCardTrackPlayParams( $data ) {
	    $rules = array(
	        array('field' => 'CmpCallCard_id', 'label' => 'Идентификатор вызова', 'rules' => 'required', 'type' => 'id'),
	        array('field' => 'EmergencyTeam_id', 'label' => 'Идентификатор бригады', 'rules' => 'required', 'type' => 'id'),
	    );
		$startTime=false; $endTime=false;
	    $queryParams = $this->_checkInputData( $rules , $data , $err, FALSE ) ;
	    if ( !empty( $err ) ) {
	        return $err ;
	    }
		
		return array(
			array(
				//'startTime' => $this->_getCmpCallCardPassToEmergencyTeamTimestamp($queryParams['CmpCallCard_id']),
				//'endTime' => $this->_getCmpCallCardEndTimestamp($queryParams['CmpCallCard_id'], $queryParams['EmergencyTeam_id']),	
				'startTime' => $this->_getCmpCallCardStartTime($queryParams),
				'endTime' => $this->_getCmpCallCardEndTime($queryParams),
				'wialonId' => $this->_getEmergencyTeamGeoserviveTransportId($queryParams['EmergencyTeam_id'])
			)
		);
	}
	
	/**
	 * Получение времени назначения статуса карты вызова "принято"
	 * @param null $CmpCallCard_id
	 * @param null $EmergencyTeam_id
	 */
	protected function _getCmpCallCardStartTime($param) {
		if( !isset($param['CmpCallCard_id']) || !isset($param['EmergencyTeam_id']) ) {
			return false;
		}
		$sql = "
			SELECT TOP 1
				convert(varchar, CCCS.CmpCallCardStatus_insDT, 120) as EventDT
	        FROM
	        	v_CmpCallCardStatus CCCS
	        	left join v_CmpCallCardStatusType CCCST on CCCST.CmpCallCardStatusType_id = CCCS.CmpCallCardStatusType_id
				left join v_CmpCallCard CCC on CCC.CmpCallCard_id = CCCS.CmpCallCard_id
	        WHERE
	        	CCCS.CmpCallCard_id = :CmpCallCard_id 
				AND CCCST.CmpCallCardStatusType_code = 2
				AND CCC.EmergencyTeam_id = :EmergencyTeam_id
	      	ORDER BY
	      		CCCS.CmpCallCardStatus_insDT DESC";
		$queryParams[ 'CmpCallCard_id' ] = $param['CmpCallCard_id'];
		$queryParams[ 'EmergencyTeam_id' ] = $param['EmergencyTeam_id'];
		
		$result = $this->queryList( $sql , $queryParams );
		return ( $result ) ? $result[0] : false;
	}
	
	/**
	 * Получение времени завершения вызова
	 * @param null $CmpCallCard_id
	 * @param null $EmergencyTeam_id
	 */
	protected function _getCmpCallCardEndTime($param) {
		if( !isset($param['CmpCallCard_id']) || !isset($param['EmergencyTeam_id']) ) {
			return false;
		}
		
		$sql = "
			SELECT TOP 1
				convert(varchar, CmpCallCard_Tisp, 120) as EventDT
	        FROM
	        	v_CmpCallCard
	        WHERE
				CmpCallCard_Tisp is not null
	        	and CmpCallCard_id = :CmpCallCard_id";
		$queryParams[ 'CmpCallCard_id' ] = $param['CmpCallCard_id'];
		$queryParams[ 'EmergencyTeam_id' ] = $param['EmergencyTeam_id'];
		
		$result = $this->queryList( $sql , $queryParams );
		
		if( !$result ){
			$result = $this->queryList( "SELECT convert(varchar, dbo.tzGetDate(), 120) as EventDT" );
		}		
		return ( $result ) ? $result[0] : false;
	}	
	
	/**
	 * Получает идентификатор автомобиля в Виалон по идентификатору бригады
	 * @param null $EmergencyTeam_id
	 * @return bool
	 */
	protected function _getEmergencyTeamGeoserviveTransportId($EmergencyTeam_id = null) {
		$GTR = $this->_defineGeoserviceTransportRelQueryParams();
		$query_params = array(
			'EmergencyTeam_id' => $EmergencyTeam_id
		);

		$query = "
			SELECT TOP 1
				COALESCE(GTR.{$GTR['GeoserviceTransport_id_field']}, MPC.MedProductCard_Glonass) as GeoserviceTransport_id
			FROM v_EmergencyTeam ET with (nolock)
			LEFT JOIN {$GTR['GeoserviceTransportRel_object']}  as GTR with (nolock) ON( GTR.{$GTR['EmergencyTeam_id_field']}=ET.EmergencyTeam_id )
			LEFT JOIN passport.v_MedProductCard MPC with (nolock) on MPC.MedProductCard_id = ET.MedProductCard_id
			WHERE ET.EmergencyTeam_id=:EmergencyTeam_id 
		 ";

		$GeoserviceTransport_id = $this->queryList( $query , $query_params );

		return ($GeoserviceTransport_id == false) ? false : $GeoserviceTransport_id[0];
	}
	
	/**
	 * получение идентификатор автомобиля в Виалон из защищенного метода  _getEmergencyTeamGeoserviveTransportId
	 */
	public function getEmergencyTeamGeoserviveTransportId($param) {
		if( empty($param) ) return false;
		return $this->_getEmergencyTeamGeoserviveTransportId($param);
	}

	/**
	 * Получает время передачи вызова на бригаду
	 * @param null $CmpCallCard_id
	 * @return bool
	 */
	protected function _getCmpCallCardPassToEmergencyTeamTimestamp($CmpCallCard_id = null ) {
	    $query_params = array(
	        'CmpCallCard_id' => $CmpCallCard_id,
	        'CmpCallCardStatusType_code' => 2 // Передан
	    );

	    $query = "
	        SELECT TOP 1
	        	DATEDIFF(SECOND,{d '1970-01-01'}, CCCS.CmpCallCardStatus_insDT) as CmpCallCardStatus_insDTStamp
	        FROM
	        	v_CmpCallCardStatus CCCS
	        	left join v_CmpCallCardStatusType CCCST on CCCST.CmpCallCardStatusType_id = CCCS.CmpCallCardStatusType_id
	        WHERE
	        	CCCS.CmpCallCard_id = :CmpCallCard_id AND
	        	CCCST.CmpCallCardStatusType_code = :CmpCallCardStatusType_code 
	      	ORDER BY
	      		CCCS.CmpCallCardStatus_insDT DESC
	    ";

		$timestamp = $this->queryList( $query , $query_params );

		return ($timestamp == false) ? false : $timestamp[0];

	}

	/**
	 * Получает время окончания вызова бригадой
	 * @param null $CmpCallCard_id
	 * @param null $EmergencyTeam_id
	 * @return int
	 */
	protected function _getCmpCallCardEndTimestamp($CmpCallCard_id = null, $EmergencyTeam_id = null ) {
		$query_params = array(
			'CmpCallCard_id' => $CmpCallCard_id,
			'EmergencyTeam_id' => $EmergencyTeam_id,
			'EmergencyTeamStatus_code' => 4 // Конец обслуживания
		);

		$query = "
	        SELECT TOP 1
	        	DATEDIFF(SECOND,{d '1970-01-01'}, ETSH.EmergencyTeamStatusHistory_insDT) as EmergencyTeamStatusHistory_insDTStamp
	        FROM
	        	v_EmergencyTeamStatusHistory ETSH
	        	LEFT JOIN v_EmergencyTeamStatus ETS on ETS.EmergencyTeamStatus_id = ETSH.EmergencyTeamStatus_id
	        WHERE
	        	ETSH.CmpCallCard_id = :CmpCallCard_id AND
	        	ETSH.EmergencyTeam_id = :EmergencyTeam_id AND 
	        	ETS.EmergencyTeamStatus_code = :EmergencyTeamStatus_code
	      	ORDER BY
	      		ETSH.EmergencyTeamStatusHistory_insDT DESC
	    ";


		$timestamp = $this->queryList( $query , $query_params );

		return ($timestamp == false) ? date_timestamp_get(date_create()) : $timestamp[0];

	}
	
	/**
	* Возвращает массив ID тех подразделений СМП, которые были выбраны  пользователем при входе в АРМ
	*/
	public function loadIdSelectSmp() {
		$user = pmAuthUser::find($_SESSION['login']);
		$settings = unserialize($user->settings);

		if ( isset($settings['lpuBuildingsWorkAccess']) && is_array($settings['lpuBuildingsWorkAccess']) && !empty($settings['lpuBuildingsWorkAccess'][0]) ) {
			return $settings['lpuBuildingsWorkAccess'];
		}else{
			return false;
		}
	}


	/**
	 * Проверка превышения времени обеда бригады
	 * @param $data
	 * @return array
	 */

	function checkLunchTimeOut($data) {
		if(!$data['EmergencyTeam_id']){
			return false;
		}

		$sql = "
		SELECT top 1
				CASE WHEN
				DATEDIFF(minute,ETSH.EmergencyTeamStatusHistory_insDT, dbo.tzGetDate()) > COALESCE(PSUT.LunchTimeET, SUT.LunchTimeET)
				THEN
					2
				ELSE
					1
				END as LunchTimeOut
			FROM
				v_LpuBuilding LB with (nolock)
				left join EmergencyTeam ET on ET.LpuBuilding_id = LB.LpuBuilding_id
				left join EmergencyTeamStatusHistory ETSH on ETSH.EmergencyTeamStatusHistory_id = ET.EmergencyTeamStatusHistory_id
				outer apply (
					select top 1 *
					from v_SmpUnitTimes with (nolock)
					where LpuBuilding_id = LB.LpuBuilding_id
				) SUT
				outer apply (
					select top 1 *
					from v_SmpUnitParam with (nolock)
					where LpuBuilding_id = LB.LpuBuilding_id
					order by SmpUnitParam_id desc
				) SUP
				outer apply (
					select top 1 *
					from v_SmpUnitTimes with (nolock)
					where LpuBuilding_id = SUP.LpuBuilding_pid
				) PSUT
			WHERE
				ET.EmergencyTeam_id = :EmergencyTeam_id
		";

		$query = $this->db->query( $sql, array('EmergencyTeam_id' => $data['EmergencyTeam_id']) );
		return $query->result_array();
	}

	/**
	 * Номер телефона по умолчанию для наряда
	 */
	public function getDefaultPhoneNumber($data){

		$this->load->model('CmpCallCard_model4E', 'CmpCallCard_model4E');
		$operDpt = $this->CmpCallCard_model4E->getOperDepartament($data);

		if(!empty($operDpt["LpuBuilding_pid"])){

			$this->load->model('LpuStructure_model', 'LpuStructure');
			$LpuBuildingData = $this->LpuStructure->getLpuBuildingData(array("LpuBuilding_id"=>$operDpt["LpuBuilding_pid"]));

			if(!empty($LpuBuildingData[0]['LpuBuildingSmsType_id'])){
				if($LpuBuildingData[0]['LpuBuildingSmsType_id'] == 1){
					$this->load->model('LpuPassport_model', 'LpuPassport');
					$MedProductCardData = $this->LpuPassport->loadMedProductCardData(array('MedProductCard_id' => $data['MedProductCard_id']));
					if(count($MedProductCardData) > 0){
						return array($MedProductCardData[0]['MedProductCard_Phone']);
					}
				}else{
					$query = "
						select
							MSF.MedStaffFactCache_PhoneNum
						from
							v_MedStaffFactCache MSF with (nolock)
						where
							MSF.MedStaffFact_id = :MedStaffFact_id
					";
					$item = $this->getFirstRowFromQuery($query, array('MedStaffFact_id' => $data['EmergencyTeam_HeadShift']));
					if(is_array($item) && count($item) > 0){
						return array($item['MedStaffFactCache_PhoneNum']);
					}
				}
			}
		}
		return false;
	}

	/**
	* Возвращает массив ID МО выбранных в АРМ
	*/
	public function getSelectedLpuId() {
		$user = pmAuthUser::find($_SESSION['login']);
		$settings = unserialize($user->settings);

		if ( isset($settings['lpuWorkAccess']) && is_array($settings['lpuWorkAccess']) && $settings['lpuWorkAccess'][0] != "") {
			return $settings['lpuWorkAccess'];
		}else{
			return false;
		}
	}


	/**
	 * Информация о бригаде в АРМе ЦМК
	 */
	function getEmergencyTeam($data) {
		$params['EmergencyTeam_id'] = $data['EmergencyTeam_id'];
		$sql = "
			SELECT TOP 1
				MPH1.Person_Fio as EmergencyTeam_HeadShift,
				MPH2.Person_Fio as EmergencyTeam_HeadShift2,
				MPD.Person_Fio as EmergencyTeam_Driver,
				MPA.Person_Fio as EmergencyTeam_Assistant1,
				ETSpec.EmergencyTeamSpec_Code,
				ET.EmergencyTeam_Num,
				ETS.EmergencyTeamStatus_Name,
				count.countCateredCmpCallCards,
				CC.CmpCallCard_Numv,
				CC.CmpCallCard_Ngod,
				lpuHid.Lpu_Nick as LpuHid_Nick
			FROM
				v_EmergencyTeam ET with(nolock)
				LEFT JOIN v_LpuBuilding LB on LB.LpuBuilding_id =  ET.LpuBuilding_id
				LEFT JOIN v_EmergencyTeamStatus AS ETS with (nolock) ON ETS.EmergencyTeamStatus_id=ET.EmergencyTeamStatus_id 
				LEFT JOIN v_EmergencyTeamSpec as ETSpec with(nolock) ON ET.EmergencyTeamSpec_id = ETSpec.EmergencyTeamSpec_id
				LEFT JOIN v_MedPersonal MPH1 with(nolock) on MPH1.MedPersonal_id = ET.EmergencyTeam_HeadShift  and MPH1.Lpu_id = LB.Lpu_id
				LEFT JOIN v_MedPersonal MPH2 with(nolock) on MPH2.MedPersonal_id = ET.EmergencyTeam_HeadShift2 and MPH2.Lpu_id = LB.Lpu_id
				LEFT JOIN v_MedPersonal MPD with(nolock) on MPD.MedPersonal_id = ET.EmergencyTeam_Driver and MPD.Lpu_id = LB.Lpu_id
				LEFT JOIN v_MedPersonal MPA with(nolock) on MPA.MedPersonal_id = ET.EmergencyTeam_Assistant1 and MPA.Lpu_id = LB.Lpu_id
				outer apply(
					SELECT COUNT(*) as countCateredCmpCallCards
					FROM v_CmpCallCard ccc with(nolock)
					WHERE ccc.EmergencyTeam_id = :EmergencyTeam_id
					and CmpCallCardStatusType_id IN (4,6,7,8,18)
				) count
				outer apply (
					SELECT TOP 1 C2.* 
					FROM v_CmpCallCard as C2 with(nolock)
					WHERE 
						C2.EmergencyTeam_id = ET.EmergencyTeam_id 
						AND C2.CmpCallCardStatusType_id = 2
					ORDER BY C2.CmpCallCard_updDT DESC
				) CC
				left join v_Lpu lpuHid with(nolock) on lpuHid.Lpu_id = CC.Lpu_hid
			WHERE
				ET.EmergencyTeam_id = :EmergencyTeam_id
		";

		$query = $this->db->query( $sql, $params );
		return $query->result_array();
	}
	
	/**
	 * Проверяем закрыта ли бригада
	 */	
	function checkOpenEmergencyTeam($EmergencyTeam_id){
		$params['EmergencyTeam_id'] = $EmergencyTeam_id;
		$sql = "select 1 from v_EmergencyTeamDuty where EmergencyTeam_id = :EmergencyTeam_id and EmergencyTeamDuty_isClose=2";
		$query = $this->db->query( $sql, $params );
		
		$resultarr = $query->result_array();
		if (is_array($resultarr) && (count($resultarr) > 0)){
			return $this->createError(false, 'Редактирование невозможно. В отметке о выходе бригад установлено значение закрыто.');			
		}
		return true;				
		
	}	
}
