<?php
class Waybill_model4E extends swModel {
	
	/**
	 * @desc Сохранение путевого листа
	 * 
	 * @param array $data Данные ProcessInputData
	 * @return array or false
	 */
	public function saveWaybill( $data ){

		if ( !array_key_exists( 'Lpu_id', $data ) || !$data['Lpu_id'] ) {
			return array( array( 'Err_Msg' => 'Не указан идентификатор ЛПУ') );
		}
		
		// Открываем транзакцию
		//$this->db->trans_begin();
		
		$sqlArr = array(
			// Общие сведения
			'Waybill_id'				=> $data['Waybill_id'] > 0 ? $data['Waybill_id'] : null,
			'Lpu_id'					=> $data['Lpu_id'],
			'EmergencyTeam_id'			=> $data['EmergencyTeam_id'],
			'Waybill_Series'			=> $data['Waybill_Series'],
			'Waybill_Num'				=> $data['Waybill_Num'],
			'Waybill_Date'				=> $data['Waybill_Date'],
			'Waybill_GarageNum'			=> $data['Waybill_GarageNum'],
			'Waybill_EmployeeNum'		=> $data['Waybill_EmployeeNum'],
			'Waybill_IdentityNum'		=> $data['Waybill_IdentityNum'],
			'Waybill_Class'				=> $data['Waybill_Class'],
			'Waybill_LicenseCard'		=> $data['Waybill_LicenseCard'],
			'Waybill_RegNum'			=> $data['Waybill_RegNum'],
			'Waybill_RegSeries'			=> $data['Waybill_RegSeries'],
			'Waybill_RegNum2'			=> $data['Waybill_RegNum2'],
			'Waybill_Address'			=> $data['Waybill_Address'],
			'Waybill_TimeStart'			=> $data['Waybill_TimeStart'],
			'Waybill_TimeFinish'		=> $data['Waybill_TimeFinish'],
			'Waybill_Justification'		=> $data['Waybill_Justification'],
			'Waybill_OdometrBefore'		=> $data['Waybill_OdometrBefore'],
			'Waybill_OdometrAfter'		=> $data['Waybill_OdometrAfter'],
			'WaybillGas_id'				=> $data['WaybillGas_id'],
			'Waybill_RefillCardNum'		=> $data['Waybill_RefillCardNum'],
			'Waybill_FuelGet'			=> $data['Waybill_FuelGet'],
			'Waybill_FuelBefore'		=> $data['Waybill_FuelBefore'],
			'Waybill_FuelAfter'			=> $data['Waybill_FuelAfter'],
			'Waybill_FuelConsumption'	=> $data['Waybill_FuelConsumption'],
			'Waybill_FuelFact'			=> $data['Waybill_FuelFact'],
			'Waybill_FuelEconomy'		=> $data['Waybill_FuelEconomy'],
			'Waybill_FuelOverrun'		=> $data['Waybill_FuelOverrun'],
			'Waybill_PersonCnt'			=> $data['Waybill_PersonCnt'],
			'Waybill_Trip'				=> $data['Waybill_Trip'],
			'Waybill_PaymentOdometr'	=> !empty( $data['Waybill_PaymentOdometr'] ) ? str_replace(',', '.', $data['Waybill_PaymentOdometr'] ) : $data['Waybill_PaymentOdometr'],
			'Waybill_PaymentTime'		=> !empty( $data['Waybill_PaymentTime'] ) ? str_replace(',', '.', $data['Waybill_PaymentTime'] ) : $data['Waybill_PaymentTime'],
			'Waybill_PaymentTotal'		=> !empty( $data['Waybill_PaymentTotal'] ) ? str_replace(',', '.', $data['Waybill_PaymentTotal'] ) : $data['Waybill_PaymentTotal'],
			'Waybill_CalcMakePost'		=> $data['Waybill_CalcMakePost'],
			'Waybill_CalcMakeName'		=> $data['Waybill_CalcMakeName'],
			'pmUser_id'					=> $data['pmUser_id'],
		);
		
		if ( !array_key_exists( 'Waybill_id', $data ) || !$data['Waybill_id'] ) {
			$procedure = 'p_Waybill_ins';
		} else {
			$procedure = 'p_Waybill_upd';
		}
		
		$query = "
			DECLARE
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000)

			SET @Res = :Waybill_id;

			EXEC ".$procedure."
				@Waybill_id = @Res output,
				@Lpu_id = :Lpu_id,
				@EmergencyTeam_id = :EmergencyTeam_id,
				@Waybill_Series = :Waybill_Series,
				@Waybill_Num = :Waybill_Num,
				@Waybill_Date = :Waybill_Date,
				@Waybill_GarageNum = :Waybill_GarageNum,
				@Waybill_EmployeeNum = :Waybill_EmployeeNum,
				@Waybill_IdentityNum = :Waybill_IdentityNum,
				@Waybill_Class = :Waybill_Class,
				@Waybill_LicenseCard = :Waybill_LicenseCard,
				@Waybill_RegNum = :Waybill_RegNum,
				@Waybill_RegSeries = :Waybill_RegSeries,
				@Waybill_RegNum2 = :Waybill_RegNum2,
				@Waybill_Address = :Waybill_Address,
				@Waybill_TimeStart = :Waybill_TimeStart,
				@Waybill_TimeFinish = :Waybill_TimeFinish,
				@Waybill_Justification = :Waybill_Justification,
				@Waybill_OdometrBefore = :Waybill_OdometrBefore,
				@Waybill_OdometrAfter = :Waybill_OdometrAfter,
				@WaybillGas_id = :WaybillGas_id,
				@Waybill_RefillCardNum = :Waybill_RefillCardNum,
				@Waybill_FuelGet = :Waybill_FuelGet,
				@Waybill_FuelBefore = :Waybill_FuelBefore,
				@Waybill_FuelAfter = :Waybill_FuelAfter,
				@Waybill_FuelConsumption = :Waybill_FuelConsumption,
				@Waybill_FuelFact = :Waybill_FuelFact,
				@Waybill_FuelEconomy = :Waybill_FuelEconomy,
				@Waybill_FuelOverrun = :Waybill_FuelOverrun,
				@Waybill_PersonCnt = :Waybill_PersonCnt,
				@Waybill_Trip = :Waybill_Trip,
				@Waybill_PaymentOdometr = :Waybill_PaymentOdometr,
				@Waybill_PaymentTime = :Waybill_PaymentTime,
				@Waybill_PaymentTotal = :Waybill_PaymentTotal,
				@Waybill_CalcMakePost = :Waybill_CalcMakePost,
				@Waybill_CalcMakeName = :Waybill_CalcMakeName,

				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			SELECT @Res as Waybill_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		
		$wb_result = $this->db->query( $query, $sqlArr );
		if ( !is_object( $wb_result ) ) {
			//$this->db->trans_rollback();
			return false;
		}
		
		$waybill = $wb_result->result_array();
		$waybill_id = $waybill[0]['Waybill_id'];
		
		if ( array_key_exists( 'WaybillRoute', $data ) && !empty( $data['WaybillRoute'] ) && $data['WaybillRoute'] != '[]' ) {
			$waybillroute = json_decode( toUTF( $data['WaybillRoute'] ), true );
			foreach( $waybillroute as $k => $route ) {
				
				$waybillroute_id = $route['WaybillRoute_id'];
				if ( $waybillroute_id == NULL ) {
					$procedure = 'p_WaybillRoute_ins';
				} else {
					$procedure = 'p_WaybillRoute_upd';
				}
				
				$sqlArr = array(
					'WaybillRoute_id'			=> $waybillroute_id,
					'Waybill_id'				=> $waybill_id,
					'WaybillRoute_CustCode'		=> $route['WaybillRoute_CustCode'],
					'WaybillRoute_PointStart'	=> $route['WaybillRoute_PointStart'],
					'WaybillRoute_PointFinish'	=> $route['WaybillRoute_PointFinish'],
					'WaybillRoute_TimeStart'	=> preg_match( '#^[0-9]{1,2}:[0-9]{1,2}$#', $route['WaybillRoute_TimeStart'] ) ? $route['WaybillRoute_TimeStart'] : '00:00',
					'WaybillRoute_TimeFinish'	=> preg_match( '#^[0-9]{1,2}:[0-9]{1,2}$#', $route['WaybillRoute_TimeFinish'] ) ? $route['WaybillRoute_TimeFinish'] : '00:00',
					'WaybillRoute_Trip'			=> floatval( str_replace( ',', '.', $route['WaybillRoute_Trip'] ) ),
					'pmUser_id'					=> $data['pmUser_id'],
				);
		
				$query = "
					DECLARE
						@Res bigint,
						@ErrCode int,
						@ErrMessage varchar(4000)

					SET @Res = :WaybillRoute_id;

					EXEC ".$procedure."
						@WaybillRoute_id = @Res output,
						@Waybill_id = :Waybill_id,
						@WaybillRoute_CustCode = :WaybillRoute_CustCode,
						@WaybillRoute_PointStart = :WaybillRoute_PointStart,
						@WaybillRoute_PointFinish = :WaybillRoute_PointFinish,
						@WaybillRoute_TimeStart = :WaybillRoute_TimeStart,
						@WaybillRoute_TimeFinish = :WaybillRoute_TimeFinish,
						@WaybillRoute_Trip = :WaybillRoute_Trip,

						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;

					SELECT @Res as WaybillRoute_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";
				
				$wbr_result = $this->db->query( $query, $sqlArr );
				if ( !is_object( $wbr_result ) ) {
					//$this->db->trans_rollback();
					return false;
				}
			}
		}
		
		//$this->db->trans_commit();
		
		return $waybill;
	}
	
	
	/**
	 * @desc Возвращает список путевых листов
	 * 
	 * @param array $data
	 * @return array|false
	 */
	function loadWaybillGrid( $data ){
		
		// Выводим путевые листы только для бригад состоящих в ЛПУ пользователя
		if ( !array_key_exists( 'Lpu_id', $data ) || !$data['Lpu_id'] ) {
			return array( array( 'Err_Msg' => 'Не указан идентификатор ЛПУ') );
		}
		
		$filter = '';
		
		$filter .= "ET.Lpu_id = :Lpu_id ";
		
		if (  ( array_key_exists( 'dateStart', $data ) && $data['dateStart'] != NULL ) &&
			  ( array_key_exists( 'dateFinish', $data ) && $data['dateFinish']!= NULL  )  )
		{
			$date_start = DateTime::createFromFormat( 'd.m.Y', $data['dateStart']);
			$date_finish = DateTime::createFromFormat( 'd.m.Y', $data['dateFinish'] );
			
			$data['dateStart'] = $date_start->format('Y-m-d').' 00:00:00';
			$data['dateFinish'] = $date_finish->format('Y-m-d').' 00:00:00';

			$filter .= "
				AND CAST(w.Waybill_Date as date) >= :dateStart
				AND CAST(w.Waybill_Date as date) <= :dateFinish
			";
		}
		

		$query = "
			SELECT 
				-- select
				w.Waybill_id,
				w.Waybill_Num,
				CONVERT( varchar, w.Waybill_Date, 104 ) as Waybill_Date,
				
				LTRIM( RTRIM( mpDriver.Person_FIO ) ) as EmergencyTeam_Driver,
				et.EmergencyTeam_CarNum
				-- end select
			FROM
				-- from
				v_Waybill as w with (nolock)
				INNER JOIN v_EmergencyTeam as et with(nolock) ON( et.EmergencyTeam_id=w.EmergencyTeam_id )
				--INNER JOIN v_MedPersonal mpDriver with(nolock) ON( mpDriver.MedPersonal_id=et.EmergencyTeam_Driver )
				outer apply(
					select top 1
						mpp.Person_FIO
					from
						v_MedPersonal mpp with (nolock)						
					where
						mpp.MedPersonal_id = et.EmergencyTeam_Driver					
				) mpDriver
				-- end from
			WHERE
				".$filter."
			ORDER BY
				-- order by
				w.Waybill_Date
				-- end order by
		";
		
		$sqlArr = array(
			'Lpu_id' => $data['Lpu_id'],
			'dateStart'	 => $data['dateStart'],
			'dateFinish' => $data['dateFinish']
		);
		
		//var_dump(getDebugSQL($query, $sqlArr)); exit();
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
	 * @desc Возвращает данные путевого листа
	 * 
	 * @param array $data
	 * @return array|boolean
	 */
	function loadWaybill( $data ){
		
		if ( !array_key_exists( 'Waybill_id', $data ) || !$data['Waybill_id'] ) {
			return array( array( 'Err_Msg' => 'Не указан идентификатор путевого листа') );
		}
		
		$query = "
			SELECT TOP 1
				Waybill_id,
				EmergencyTeam_id,
				Waybill_Series,
				Waybill_Num,
				CONVERT( varchar, w.Waybill_Date, 104 ) as Waybill_Date,
				Waybill_GarageNum,
				Waybill_EmployeeNum,
				Waybill_IdentityNum,
				Waybill_Class,
				Waybill_LicenseCard,
				Waybill_RegNum,
				Waybill_RegSeries,
				Waybill_RegNum2,
				Waybill_Address,
				SUBSTRING( CONVERT( varchar, w.Waybill_TimeStart, 108 ), 1, 5 ) as Waybill_TimeStart,
				SUBSTRING( CONVERT( varchar, w.Waybill_TimeFinish, 108 ), 1, 5 ) as Waybill_TimeFinish,
				Waybill_Justification,
				Waybill_OdometrBefore,
				Waybill_OdometrAfter,
				WaybillGas_id,
				Waybill_RefillCardNum,
				Waybill_FuelGet,
				Waybill_FuelBefore,
				Waybill_FuelAfter,
				Waybill_FuelConsumption,
				Waybill_FuelFact,
				Waybill_FuelEconomy,
				Waybill_FuelOverrun,
				Waybill_PersonCnt,
				Waybill_Trip,
				Waybill_PaymentOdometr,
				Waybill_PaymentTime,
				Waybill_PaymentTotal,
				Waybill_CalcMakePost,
				Waybill_CalcMakeName
			FROM
				v_Waybill w with(nolock)
			WHERE
				w.Waybill_id = :Waybill_id
		";
		
		$sqlArr = array(
			'Waybill_id' => $data['Waybill_id'],
		);
		
		$result = $this->db->query( $query, $sqlArr );

		if ( is_object( $result ) ) {
			return $result->result('array');
		}

		return false;
	}

	
	/**
	 * @desc Возвращает данные маршрута путевого листа
	 * 
	 * @param array $data
	 * @return array|boolean
	 */
	function loadWaybillRoute( $data ){
		
		if ( !array_key_exists( 'Waybill_id', $data ) || !$data['Waybill_id'] ) {
			return array( array( 'Err_Msg' => 'Не указан идентификатор путевого листа') );
		}
		
		$query = "
			SELECT
				wr.WaybillRoute_id as WaybillRoute_id,
				wr.WaybillRoute_CustCode as WaybillRoute_CustCode,
				wr.WaybillRoute_PointStart as WaybillRoute_PointStart,
				wr.WaybillRoute_PointFinish as WaybillRoute_PointFinish,
				SUBSTRING( CONVERT( varchar, wr.WaybillRoute_TimeStart, 108 ), 1, 5 ) as WaybillRoute_TimeStart,
				SUBSTRING( CONVERT( varchar, wr.WaybillRoute_TimeFinish, 108 ), 1, 5 ) as WaybillRoute_TimeFinish,
				wr.WaybillRoute_Trip as WaybillRoute_Trip
			FROM
				v_WaybillRoute wr with(nolock)
			WHERE
				wr.Waybill_id = :Waybill_id
			ORDER BY
				wr.WaybillRoute_Trip,
				wr.WaybillRoute_id
		";
		
		$sqlArr = array(
			'Waybill_id' => $data['Waybill_id'],
		);
		
		$result = $this->db->query( $query, $sqlArr );

		if ( is_object( $result ) ) {
			return $result->result_array();
		}

		return false;
	}
	
	
	/**
	 * @desc Удаляет путевой лист
	 * 
	 * @param array $data
	 * @return array|false
	 */
	function deleteWaybill( $data ) {
		return array(array('Error_Msg' => 'Во время удаления путевого листа произошла ошибка в базе данных.'));
	}

	
	/**
	 * @desc Выводит даные путевого листа для печати
	 * @param array $data
	 * @return array|false
	 */
	function printWaybill( $data ) {
		
		
		if ( !array_key_exists( 'Waybill_id', $data ) || !$data['Waybill_id'] ) {
			return array( array( 'Err_Msg' => 'Не указан идентификатор путевого листа') );
		}
		
		$query = "
			SELECT TOP 1
				Waybill_id,
				Waybill_Series,
				Waybill_Num,
				CONVERT( varchar, w.Waybill_Date, 104 ) as Waybill_Date,
				Waybill_GarageNum,
				Waybill_EmployeeNum,
				Waybill_IdentityNum,
				Waybill_Class,
				Waybill_LicenseCard,
				Waybill_RegNum,
				Waybill_RegSeries,
				Waybill_RegNum2,
				Waybill_Address,
				SUBSTRING( CONVERT( varchar, w.Waybill_TimeStart, 108 ), 1, 5 ) as Waybill_TimeStart,
				SUBSTRING( CONVERT( varchar, w.Waybill_TimeFinish, 108 ), 1, 5 ) as Waybill_TimeFinish,
				Waybill_Justification,
				Waybill_OdometrBefore,
				Waybill_OdometrAfter,
				Waybill_RefillCardNum,
				Waybill_FuelGet,
				Waybill_FuelBefore,
				Waybill_FuelAfter,
				Waybill_FuelConsumption,
				Waybill_FuelFact,
				Waybill_FuelEconomy,
				Waybill_FuelOverrun,
				Waybill_PersonCnt,
				Waybill_Trip,
				Waybill_PaymentOdometr,
				Waybill_PaymentTime,
				Waybill_PaymentTotal,
				Waybill_CalcMakePost,
				Waybill_CalcMakeName,
				
				wg.WaybillGas_Code,
				wg.WaybillGas_Name,
				
				l.Lpu_Name,
				
				et.EmergencyTeam_CarNum,
				et.EmergencyTeam_CarBrand,
				et.EmergencyTeam_CarModel,
				
				MP.Person_Fin as HeadShift,
				MP2.Person_Fin as Driver
			FROM
				v_Waybill w with(nolock)
				LEFT JOIN v_EmergencyTeam et with(nolock) ON( et.EmergencyTeam_id=w.EmergencyTeam_id )
				LEFT JOIN v_WaybillGas wg with(nolock) ON( wg.WaybillGas_id=w.WaybillGas_id )
				LEFT JOIN v_Lpu l with(nolock) ON( l.Lpu_id=w.Lpu_id )
				
				LEFT JOIN v_MedPersonal as MP with(nolock) ON( MP.MedPersonal_id=ET.EmergencyTeam_HeadShift )
				LEFT JOIN v_MedPersonal as MP2 with(nolock) ON( MP.MedPersonal_id=ET.EmergencyTeam_Driver )
			WHERE
				w.Waybill_id = :Waybill_id
		";
		
		$sqlArr = array(
			'Waybill_id' => $data['Waybill_id'],
		);
		
		$result = $this->db->query( $query, $sqlArr );
		if ( !is_object( $result ) ) {
			return false;
		}
		
		$waybill = $result->result_array();
		if ( !sizeof( $waybill ) ) {
			return false;
		}
		$waybill = $waybill[0];
		
		
		//
		// Получаем маршурты путевого листа
		//
		
		$waybill['WaybillRoute'] = array();
		$result = $this->loadWaybillRoute( $data );
		if ( is_array( $result ) && sizeof( $result ) ) {
			$waybill['WaybillRoute'] = $result;
		}
		
		unset( $result );
		
		return $waybill;
	}
}