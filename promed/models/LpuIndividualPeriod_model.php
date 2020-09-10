<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * LpuIndividualPeriod - модель для работы с настройками индивидуальных периодов записи
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2019 Swan Ltd.
 * @author			Timofeev
 * @version			30052019
 */

class LpuIndividualPeriod_model extends swModel {


	/**
	 * Получение списка МО, имеющих доступ к индивидуальной настройке периодов записи
	 */
	function getLpuIndividualPeriodList() {
		$query = "
			select 
				LIP.LpuIndividualPeriod_id,
				LIP.Lpu_id,
				L.Lpu_Nick
			from
				v_LpuIndividualPeriod LIP with (nolock)
				inner join v_Lpu L with (nolock) on L.Lpu_id = LIP.Lpu_id
			order by
				L.Lpu_Nick
		";
		$response['data'] = $this->queryResult($query);

		return $response;
	}

	/**
	 * Добавление МО в список МО, имеющих доступ к индивидуальной настройке периодов записи
	 */
	function saveLpuIndividualPeriod($data) {

		//Проверяем наличие добавляемой МО в списке
		$query = "
			select top 1 LpuIndividualPeriod_id
			from v_LpuIndividualPeriod
			where Lpu_id = :Lpu_id
		";
		$queryParams = array(
			'Lpu_id' => $data['Lpu_id']
		);
		$checkExist = $this->getFirstRowFromQuery($query, $data);
		if(!empty($checkExist)) {
			throw new Exception('Данной МО уже предоставлен доступ к настройке');
		}

		$query = "
			declare
				@LpuIndividualPeriod_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec p_LpuIndividualPeriod_ins
				@LpuIndividualPeriod_id = @LpuIndividualPeriod_id output,
				@Lpu_id = :Lpu_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @LpuIndividualPeriod_id as LpuIndividualPeriod_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$queryParams = array(
			'Lpu_id' => $data['Lpu_id'],
			'pmUser_id' => $data['pmUser_id']
		);
		return $this->queryResult($query, $queryParams);
	}

	/**
	 * Удаление МО из списка МО, имеющих доступ к индивидуальной настройке периодов записи
	 */
	function deleteLpuIndividualPeriod($data) {
		$query = "
			declare
				@LpuIndividualPeriod_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec p_LpuIndividualPeriod_del
				@LpuIndividualPeriod_id = :LpuIndividualPeriod_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @LpuIndividualPeriod_id as LpuIndividualPeriod_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$queryParams = array(
			'LpuIndividualPeriod_id' => $data['LpuIndividualPeriod_id'],
			'pmUser_id' =>$data['pmUser_id']
		);

		return $this->queryResult($query, $queryParams);
	}

	/**
	 * Получение списка индивидуальных периодов записи для текущей МО(для таблицы)
	 */
	function getIndividualPeriodList($data) {
		$query = "
			SELECT
				IP.IndividualPeriod_id,
				IP.IndividualPeriodType_id,
				IPT.IndividualPeriodType_Name,
				IP.MedStaffFact_id,
				IP.LpuSection_id,
				IP.MedService_id,
				IP.IndividualPeriod_value,
				case
					when IP.IndividualPeriodType_id = 1 then MSF.Person_FIO + ' (' + COALESCE(LSMS.LpuSection_FullName, 'отделение не указано') + ')'
					when IP.IndividualPeriodType_id = 2 then LSIP.LpuSection_FullName + ' (' + LU.LpuUnit_Name + ')' 
					when IP.IndividualPeriodType_id = 3 then MS.MedService_Name + MS.MedService_Section
				end as IndividualPeriodObject_Name

			from 
				v_IndividualPeriod IP with (nolock)
				inner join v_IndividualPeriodType IPT with (nolock) on IPT.IndividualPeriodType_id = IP.IndividualPeriodType_id
				left join v_MedStaffFact MSF with (nolock) on MSF.MedStaffFact_id = IP.MedStaffFact_id
				left join v_LpuSection LSMS with (nolock) on LSMS.LpuSection_id = MSF.LpuSection_id
				left join v_LpuSection LSIP with (nolock) on LSIP.LpuSection_id = IP.LpuSection_id
				left join v_LpuUnit LU with (nolock) on LU.LpuUnit_id = LSIP.LpuUnit_id
				outer apply(
					select top 1
						MSs.MedService_Name,
						case
							when 
								LSs.LpuSection_Name is not null 
								or LUs.LpuUnit_Name is not null 
								or LUs.LpuBuilding_Name is not null
							then
								' (' + isnull(LUs.LpuBuilding_Name, '') + ' ' + isnull(LUs.LpuUnit_Name, '') + ' ' + isnull(LSs.LpuSection_Name, '') + ')'
							else ''
						end as MedService_Section
					from
						v_MedService MSs with (nolock)
						left join v_LpuSection LSs with (nolock) on LSs.LpuSection_id = MSs.LpuSection_id
						left join v_LpuUnit LUs with (nolock) on LUs.LpuUnit_id = MSs.LpuUnit_id
					where
						MSs.MedService_id = IP.MedService_id

				) as MS
			where
				IP.Lpu_id = :Lpu_id
		";	
		$queryParams = array(
			'Lpu_id' => $data['session']['lpu_id']
		);
		$response = $this->queryResult($query, $queryParams);
		return array('data' => $response);
	}

	/**
	 * Получение индвидуального периода записи объектов
	 */
	function getObjectIndividualPeriod($data, $object_type) {
		$queryParams = array(
			'Lpu_id' => (empty($data['Lpu_id'])) ? $data['session']['lpu_id'] : $data['Lpu_id']
		);
		
		switch($object_type) {
			case 'MedStaffFact':
				$queryParams['IndividualPeriodType_id'] = 1;
				break;
			case 'LpuSection':
				$queryParams['IndividualPeriodType_id'] = 2;
				break;
			case 'MedService':
				$queryParams['IndividualPeriodType_id'] = 3;
				break;
		}
		

		$query = "
			SELECT
				case
					when IP.IndividualPeriodType_id = 1 then IP.MedStaffFact_id
					when IP.IndividualPeriodType_id = 2 then IP.LpuSection_id
					when IP.IndividualPeriodType_id = 3 then IP.MedService_id
				end as IndividualPeriodObject_id,
				IP.IndividualPeriod_value
			from
				v_IndividualPeriod IP with (nolock)
				inner join v_LpuIndividualPeriod LIP with (nolock) on LIP.Lpu_id = IP.Lpu_id --не отображаем записи без связи с разрешёнными МО
			where
				IP.Lpu_id = :Lpu_id
				and IP.IndividualPeriodType_id = :IndividualPeriodType_id
		";
		$objects = $this->queryResult($query, $queryParams);
		$result = array();
		if(!empty($objects)) {
			foreach($objects as $object) {
				$result[$object['IndividualPeriodObject_id']] = $object['IndividualPeriod_value'];
			}
		}
		
		return $result;
	}

	/**
	 * Загрузка формы редактирования индивидуального периода
	 */
	function loadIndividualPeriodEditForm($data) {
		$query = "
			select
				IndividualPeriod_id,
				IndividualPeriodType_id,
				MedStaffFact_id,
				MedService_id,
				LpuSection_id,
				IndividualPeriod_value
			from
				v_IndividualPeriod IP with (nolock)
			where
				IP.IndividualPeriod_id = :IndividualPeriod_id
		";
		$queryParams = array(
			'IndividualPeriod_id' => $data['IndividualPeriod_id']
		);

		return $this->queryResult($query, $queryParams);
	}

	/**
	 * Сохранение индивидуального периода записи для текущей МО
	 */
	function saveIndividualPeriod($data) {

		$query = "
				select top 1
					IndividualPeriod_id,
					IndividualPeriodType_id
				from
					v_IndividualPeriod with (nolock)
				where
					IndividualPeriod_id != COALESCE(:IndividualPeriod_id, '-1')
					and (
						(IndividualPeriodType_id = 1 and MedStaffFact_id = :MedStaffFact_id)
						or (IndividualPeriodType_id = 2 and LpuSection_id = :LpuSection_id)
						or (IndividualPeriodType_id = 3 and MedService_id = :MedService_id)
					)
			";
		$response = $this->getFirstRowFromQuery($query, $data);
		if(!empty($response)) {
			switch ($response['IndividualPeriodType_id']) {
				case '1':
					$Alert_Msg = "Для данного врача уже установлен период записи регистратором. Изменить период?";
					break;
				case '2':
					$Alert_Msg = "Для отделения стационара уже установлен период записи регистратором. Изменить период?";
					break;
				case '3':
					$Alert_Msg = "Для отделения стационара уже установлен период записи регистратором. Изменить период? ";
					break;
			}
			
			return array('Error_Msg' => 'YesNo', 'Alert_Msg' => $Alert_Msg, 'Error_Code' => 101, 'IndividualPeriod_id' => $response['IndividualPeriod_id'], 'success' => false);
		}
		
		$proc = !empty($data['IndividualPeriod_id']) ? 'upd' : 'ins';
		$query = "
			declare
				@LpuIndividualPeriod_id bigint = :IndividualPeriod_id,
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec p_IndividualPeriod_{$proc}
				@IndividualPeriod_id = @LpuIndividualPeriod_id output,
				@Lpu_id = :Lpu_id,
				@IndividualPeriodType_id = :IndividualPeriodType_id,
				@MedStaffFact_id = :MedStaffFact_id,
				@MedService_id = :MedService_id,
				@LpuSection_id = :LpuSection_id,
				@IndividualPeriod_value = :IndividualPeriod_value,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @LpuIndividualPeriod_id as LpuIndividualPeriod_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'IndividualPeriod_id' => !empty($data['IndividualPeriod_id']) ? $data['IndividualPeriod_id'] : null,
			'Lpu_id' => $data['session']['lpu_id'],
			'IndividualPeriodType_id' => $data['IndividualPeriodType_id'],
			'MedStaffFact_id' => $data['MedStaffFact_id'],
			'MedService_id' => $data['MedService_id'],
			'LpuSection_id' => $data['LpuSection_id'],
			'IndividualPeriod_value' => $data['IndividualPeriod_value'],
			'pmUser_id' => $data['pmUser_id']
		);

		return $this->queryResult($query, $queryParams);
	}

	/**
	 * Удаление индивидуального периода
	 */
	function deleteIndividualPeriod($data) {
		$query = "
			declare
				@IndividualPeriod_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec p_IndividualPeriod_del
				@IndividualPeriod_id = :IndividualPeriod_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @IndividualPeriod_id as IndividualPeriod_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$queryParams = array(
			'IndividualPeriod_id' => $data['IndividualPeriod_id'],
			'pmUser_id' =>$data['pmUser_id']
		);

		return $this->queryResult($query, $queryParams);
	}
}