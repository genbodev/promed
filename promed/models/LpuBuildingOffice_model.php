<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * LpuBuildingOffice_model - модель для работы со справочником кабинетов
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 */

class LpuBuildingOffice_model extends swModel {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Удаление кабинета
	 */
	public function delete($data) {
		$result = array();

		$LpuBuildingOfficeMedStaffLink_id = $this->getFirstResultFromQuery("
			select top 1 LpuBuildingOfficeMedStaffLink_id
			from v_LpuBuildingOfficeMedStaffLink with (nolock)
			where LpuBuildingOffice_id = :LpuBuildingOffice_id
		", $data);

		if ( !empty($LpuBuildingOfficeMedStaffLink_id) ) {
			$result['success'] = false;
			$result['Error_Msg'] = 'Удаление кабинета невозможно, т.к. есть связанные места работы';
			return array($result);
		}

		$query = "
			declare
				@Error_Code int,
				@Error_Msg varchar(4000);

			exec p_LpuBuildingOffice_del
				@LpuBuildingOffice_id = :LpuBuildingOffice_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Msg output;

			select @Error_Code as Error_Code, @Error_Msg as Error_Msg;
		";
		$resp = $this->queryResult($query, $data);

		if ( !empty($resp['Error_Msg']) ) {
			$result['success'] = false;
			$result['Error_Msg'] = $response['Error_Msg'];
			return array($result);
		}

		$result['success'] = true;

		return array($result);
	}

	/**
	 * Возвращает список кабинетов
	 */
	public function loadList($data) {
		$filterList = array('(1 = 1)');
		$queryParams = array();

		if ( !isSuperAdmin() ) {
			$data['Lpu_id'] = $data['session']['lpu_id'];
		}

		if ( !empty($data['Lpu_id'])) {
			$filterList[] = "lbo.Lpu_id = :Lpu_id";
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}

		$query = "
			select
				-- select
				 lbo.LpuBuildingOffice_id
				,lbo.Lpu_id
				,lbo.LpuBuilding_id
				,l.Lpu_Nick
				,lb.LpuBuilding_Name
				,lbo.LpuBuildingOffice_Number
				,lbo.LpuBuildingOffice_Name
				,lbo.LpuBuildingOffice_Comment
				,convert(varchar(10), lbo.LpuBuildingOffice_begDate, 104) as LpuBuildingOffice_begDate
				,convert(varchar(10), lbo.LpuBuildingOffice_endDate, 104) as LpuBuildingOffice_endDate
				-- end select
			from
				-- from
				v_LpuBuildingOffice lbo with(nolock)
				inner join v_Lpu l with (nolock) on l.Lpu_id = lbo.Lpu_id
				inner join v_LpuBuilding lb with (nolock) on lb.LpuBuilding_id = lbo.LpuBuilding_id
				-- end from
			where
				-- where
				" . implode(' and ', $filterList) . "
				-- end where
			order by
				-- order by
				lbo.LpuBuildingOffice_Number
				-- end order by
		";

		$response = $this->getPagingResponse($query, $queryParams, $data['start'], $data['limit'], true);

		return $response;
	}

	/**
	 * Возвращает список групп кабинетов (для комбо)
	 */
	public function loadLpuBuildingOfficeCombo($data) {

		$filter = "";

		if (empty($data['Lpu_id']) && !empty($data['session']['lpu_id'])) {
			$params['Lpu_id'] = $data['session']['lpu_id'];
		} else if (!empty($data['Lpu_id'])) {
			$params['Lpu_id'] = $data['Lpu_id'];
		}

		if (!empty($data['LpuBuilding_id'])) {
			$params['LpuBuilding_id'] = $data['LpuBuilding_id'];
			$filter = "
				and lbo.LpuBuilding_id = :LpuBuilding_id
			";
		}

		$prefix = !empty($data['showOfficeNumPrefix']) ? 'каб. ' : '';

		return $this->queryResult("
			select
				lbo.LpuBuildingOffice_id,
				lbo.LpuBuilding_id,
				'{$prefix}' + lbo.LpuBuildingOffice_Number as LpuBuildingOffice_Number,
				lbo.LpuBuildingOffice_Comment,
				lbo.LpuBuildingOffice_Name,
				convert(varchar(10), lbo.LpuBuildingOffice_begDate, 104) as LpuBuildingOffice_begDate,
				convert(varchar(10), lbo.LpuBuildingOffice_endDate, 104) as LpuBuildingOffice_endDate
			from
				v_LpuBuildingOffice lbo (nolock)
			where (1=1)
				and lbo.Lpu_id = :Lpu_id
				{$filter}
		", $params);
	}

	/**
	 * Возвращает данные кабинета
	 */
	public function load($data) {
		$query = "
			select top 1
				 LpuBuildingOffice_id
				,Lpu_id
				,LpuBuilding_id
				,LpuBuildingOffice_Number
				,LpuBuildingOffice_Name
				,LpuBuildingOffice_Comment
				,convert(varchar(10), LpuBuildingOffice_begDate, 104) as LpuBuildingOffice_begDate
				,convert(varchar(10), LpuBuildingOffice_endDate, 104) as LpuBuildingOffice_endDate
			from
				v_LpuBuildingOffice with (nolock)
			where
				LpuBuildingOffice_id = :LpuBuildingOffice_id
		";

		return $this->queryResult($query, $data);
	}

	/**
	 * Сохраняет кабинет
	 */
	public function save($data) {
		try {
			$action = (empty($data['LpuBuildingOffice_id']) ? 'ins' : 'upd');

			if ( !isSuperAdmin() ) {
				$data['Lpu_id'] = $data['session']['lpu_id'];
			}

			$isRoomOrginalValueChanged = false;

			if (!empty($data['LpuBuildingOffice_id']) && !empty($data['LpuBuildingOffice_Number'])) {

				// Сохраняем старое значение кабинета
				$room_originalValue = $this->getFirstResultFromQuery("
					select top 1 LpuBuildingOffice_Number
					from v_LpuBuildingOffice with (nolock)
					where LpuBuildingOffice_id = :LpuBuildingOffice_id
				", $data, true);

				if ($data['LpuBuildingOffice_Number'] != $room_originalValue) {
					$isRoomOrginalValueChanged = true;
				}
			}

			// Проверяем уникальность номера кабинета
			$checkLpuBuildingOfficeNumber = $this->getFirstResultFromQuery("
				select top 1 LpuBuildingOffice_id
				from v_LpuBuildingOffice with (nolock)
				where LpuBuildingOffice_Number = :LpuBuildingOffice_Number
					and Lpu_id = :Lpu_id
					and LpuBuilding_id = :LpuBuilding_id
					and LpuBuildingOffice_id != ISNULL(:LpuBuildingOffice_id, 0)
					and (:LpuBuildingOffice_endDate is null or LpuBuildingOffice_begDate <= :LpuBuildingOffice_endDate)
					and (LpuBuildingOffice_endDate is null or :LpuBuildingOffice_begDate <= LpuBuildingOffice_endDate)
			", $data, true);

			if ( $checkLpuBuildingOfficeNumber === false ) {
				throw new Exception('Ошибка при выполнении запроса к БД (' . __LINE__ . ')');
			}

			if ( !empty($checkLpuBuildingOfficeNumber) ) {
				throw new Exception('Кабинет с таким номером уже существует.');
			}

			if ( $action == 'upd' ) {
				// Проверяем даты действия кабинета
				$queryList = array("
					select top 1 LpuBuildingOfficeMedStaffLink_id
					from v_LpuBuildingOfficeMedStaffLink with (nolock)
					where LpuBuildingOffice_id = :LpuBuildingOffice_id
						and LpuBuildingOfficeMedStaffLink_begDate < :LpuBuildingOffice_begDate
				");

				if ( !empty($data['LpuBuildingOffice_endDate']) ) {
					$queryList[] = "
						select top 1 LpuBuildingOfficeMedStaffLink_id
						from v_LpuBuildingOfficeMedStaffLink with (nolock)
						where LpuBuildingOffice_id = :LpuBuildingOffice_id
							and (LpuBuildingOfficeMedStaffLink_endDate is null or LpuBuildingOfficeMedStaffLink_endDate > :LpuBuildingOffice_endDate)
					";
				}

				$checkLpuBuildingOfficeDates = $this->getFirstResultFromQuery(implode(" union all ", $queryList), $data, true);

				if ( $checkLpuBuildingOfficeDates === false ) {
					throw new Exception('Ошибка при выполнении запроса к БД (' . __LINE__ . ')');
				}

				if ( !empty($checkLpuBuildingOfficeDates) ) {
					throw new Exception('Для кабинета указаны связки с местами работы, действие которых выходит за пределы периода действия кабинета.');
				}

				// При сохранении формы, если для кабинета существует связь со службой, то производится проверка:
				// - Служба принадлежит подразделению кабинета
				// - Период действия кабинета пересекает период действия службы (то есть служба является действующей в определенном периоде действия кабинета (пересечением считается 1 день)  
				// Если условия не выполняются, то сообщения об ошибке
				// «Для выбранного кабинета не может быть указана служба <Наименование службы>, так как у них разные периоды действия и/или подразделения. ОК»
				// При нажатии «ОК» диалоговое окно закрывается, форма «Кабинет» остается открытой.
				$resp = $this->getFirstResultFromQuery("
					select top 1 t3.MedService_Name
					from v_LpuBuildingOfficeVizitTime t1 with (nolock)
						inner join v_LpuBuildingOfficeMedStaffLink t2 on t2.LpuBuildingOfficeMedStaffLink_id = t1.LpuBuildingOfficeMedStaffLink_id
						inner join v_MedService t3 on t3.MedService_id = t2.MedService_id
					where t2.LpuBuildingOffice_id = :LpuBuildingOffice_id
						and t3.LpuBuilding_id != :LpuBuilding_id

					union all

					select top 1 t3.MedService_Name
					from v_LpuBuildingOfficeVizitTime t1 with (nolock)
						inner join v_LpuBuildingOfficeMedStaffLink t2 on t2.LpuBuildingOfficeMedStaffLink_id = t1.LpuBuildingOfficeMedStaffLink_id
						inner join v_MedService t3 on t3.MedService_id = t2.MedService_id
					where t2.LpuBuildingOffice_id = :LpuBuildingOffice_id
						and not (
							(:LpuBuildingOffice_endDate is null or t3.MedService_begDT <= :LpuBuildingOffice_endDate)
							and (t3.MedService_endDT is null or :LpuBuildingOffice_begDate <= t3.MedService_endDT)
						)
				", $data, true);

				if ( $resp === false ) {
					throw new Exception('Ошибка при выполнении запроса к БД (' . __LINE__ . ')');
				}

				if ( !empty($resp) ) {
					throw new Exception('Для выбранного кабинета не может быть указана служба <b>' . $resp . '</b>, так как у них разные периоды действия и/или подразделения. (' . __LINE__ . ')');
				}

				$LpuBuildingOfficeMedStaffLink_id = $this->getFirstResultFromQuery("
					select top 1 LpuBuildingOfficeMedStaffLink_id
					from v_LpuBuildingOfficeMedStaffLink with (nolock)
					where LpuBuildingOffice_id = :LpuBuildingOffice_id
				", $data);

				if ( !empty($LpuBuildingOfficeMedStaffLink_id) ) {
					$data['LpuBuilding_id'] = $this->getFirstResultFromQuery("select top 1 LpuBuilding_id from v_LpuBuildingOffice with (nolock) where LpuBuildingOffice_id = :LpuBuildingOffice_id", $data);
				}
			}

			$query = "
				declare
					@LpuBuildingOffice_id bigint = :LpuBuildingOffice_id,
					@Error_Code bigint,
					@Error_Message varchar(4000);

				exec p_LpuBuildingOffice_{$action}
					@LpuBuildingOffice_id = @LpuBuildingOffice_id output,
					@Lpu_id = :Lpu_id,
					@LpuBuilding_id = :LpuBuilding_id,
					@LpuBuildingOffice_Number = :LpuBuildingOffice_Number,
					@LpuBuildingOffice_Name = :LpuBuildingOffice_Name,
					@LpuBuildingOffice_Comment = :LpuBuildingOffice_Comment,
					@LpuBuildingOffice_begDate = :LpuBuildingOffice_begDate,
					@LpuBuildingOffice_endDate = :LpuBuildingOffice_endDate,
					@pmUser_id = :pmUser_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output;

				select @LpuBuildingOffice_id as LpuBuildingOffice_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
			";

			$response = $this->queryResult($query, $data);

			if ( !empty($response[0]['Error_Msg']) ) {
				throw new Exception($response[0]['Error_Msg']);
			}

			if ($isRoomOrginalValueChanged) {

				// берем данные по врачам связанным с этим кабинетом
				$broadcast_data = $this->dbmodel->queryResult("
					select
						ls.LpuBuilding_id,
						ls.LpuSection_id,
						lbomsfl.LpuBuildingOfficeMedStaffLink_id
					from v_LpuBuildingOffice lbo (nolock)
					inner join v_LpuBuildingOfficeMedStaffLink lbomsfl (nolock) on lbomsfl.LpuBuildingOffice_id = lbo.LpuBuildingOffice_id
					inner join v_MedStaffFact msf (nolock) on msf.MedStaffFact_id = lbomsfl.MedStaffFact_id
					inner join v_LpuSection ls (nolock) on ls.LpuSection_id = msf.LpuSection_id
					where lbo.LpuBuildingOffice_id = :LpuBuildingOffice_id
				", array('LpuBuildingOffice_id' => $data['LpuBuildingOffice_id']));

				if (!empty($broadcast_data)) {

					$this->load->model('LpuBuildingOfficeMedStaffLink_model', 'LpuBuildingOfficeMedStaffLink_model');

					foreach ($broadcast_data as $msfLink) {

						// нагребаем параметры для НОДА
						$nodeParams = array(
							'message' => 'ScoreboardTimetableChangeRoom',
							'LpuBuilding_id' => $msfLink['LpuBuilding_id'], // комната для броадкастинга сообщения
							'LpuSection_id' => $msfLink['LpuSection_id'], // комната для броадкастинга сообщения
							'LpuBuildingOffice_Number' => $data['LpuBuildingOffice_Number'], // кабинет
							'LpuBuildingOfficeMedStaffLink_id' => $msfLink['LpuBuildingOfficeMedStaffLink_id'] // связка врач-кабинет
						);

						// отправляем сообщение через нод всем ТВ (в отделении или подразделении)
						$this->LpuBuildingOfficeMedStaffLink_model->broadcastNodeMessage($nodeParams);
					}
				}
			}

		}
		catch ( Exception $e ) {
			$response = array(array('Error_Msg' => $e->getMessage()));
		}

		return $response;
	}

	/**
	 * Сохраняет связь кабинета и инфомата
	 */
	function saveLpuBuildingOfficeInfomat($data) {

		$params = array(
			'LpuBuildingOffice_id' => $data['LpuBuildingOffice_id'],
			'ElectronicInfomat_id' => $data['ElectronicInfomat_id'],
			'LpuBuildingOfficeInfomat_begDT' => $data['LpuBuildingOfficeAssign_begDate'],
			'LpuBuildingOfficeInfomat_endDT' => !empty($data['LpuBuildingOfficeAssign_endDate']) ? $data['LpuBuildingOfficeAssign_endDate'] : null,
			'LpuBuildingOfficeInfomat_id' => $data['LpuBuildingOfficeInfomat_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		// считаем количество поводов обращений на данном инфомате
		// + количество поводов в добавляемом кабинете
		$linkedEQ = $this->queryResult("
			select eqlist.ElectronicQueueInfo_id from (
				select 
					lboi.LpuBuildingOffice_id,
					lboml.MedStaffFact_id,
					lboml.MedService_id,
					isnull(eqiMedService.ElectronicQueueInfo_id, es.ElectronicQueueInfo_id) as ElectronicQueueInfo_id
				from v_LpuBuildingOfficeInfomat lboi (nolock)
				left join v_LpuBuildingOffice lbo (nolock) on lbo.LpuBuildingOffice_id = lboi.LpuBuildingOffice_id
				left join v_LpuBuildingOfficeMedStaffLink lboml (nolock) on lboml.LpuBuildingOffice_id = lbo.LpuBuildingOffice_id
				left join v_ElectronicQueueInfo eqiMedService (nolock) on eqiMedService.MedService_id = lboml.MedService_id
				outer apply (
					select top 1
						es.ElectronicQueueInfo_id
					from v_ElectronicService es (nolock)
					left join v_MedServiceElectronicQueue mseq (nolock) on  mseq.ElectronicService_id = es.ElectronicService_id
					where mseq.MedStaffFact_id = lboml.MedStaffFact_id
					order by mseq.MedServiceElectronicQueue_id desc
				) es
				where (1=1)
					and lboi.ElectronicInfomat_id = :ElectronicInfomat_id
					and lboi.LpuBuildingOfficeInfomat_begDT < dbo.tzGetDate()
					and isnull(lboi.LpuBuildingOfficeInfomat_endDT, '2050-01-01 00:00:00') > dbo.tzGetDate()
					
				union all
				
				select
					lbo.LpuBuildingOffice_id,
					lboml.MedStaffFact_id,
					lboml.MedService_id,
					isnull(eqiMedService.ElectronicQueueInfo_id, es.ElectronicQueueInfo_id) as ElectronicQueueInfo_id
				from v_LpuBuildingOffice lbo (nolock)
				left join v_LpuBuildingOfficeMedStaffLink lboml (nolock) on lboml.LpuBuildingOffice_id = lbo.LpuBuildingOffice_id
				left join v_ElectronicQueueInfo eqiMedService (nolock) on eqiMedService.MedService_id = lboml.MedService_id
				outer apply (
					select top 1
						es.ElectronicQueueInfo_id
					from v_ElectronicService es (nolock)
					left join v_MedServiceElectronicQueue mseq (nolock) on  mseq.ElectronicService_id = es.ElectronicService_id
					where mseq.MedStaffFact_id = lboml.MedStaffFact_id
					order by mseq.MedServiceElectronicQueue_id desc
				) es
				where (1=1) and lbo.LpuBuildingOffice_id = :LpuBuildingOffice_id
			) as eqlist
			where eqlist.ElectronicQueueInfo_id is not null
				
		 ", array(
		 		'ElectronicInfomat_id' => $data['ElectronicInfomat_id'],
				'LpuBuildingOffice_id' => $data['LpuBuildingOffice_id']
			)
		);

		if (!empty($linkedEQ)) {

			$linkedEQ_list = implode(',',array_unique(array_column($linkedEQ, 'ElectronicQueueInfo_id')));

			$reasons_counter = $this->getFirstResultFromQuery("
				select count(distinct ElectronicTreatment_pid) as cnt
				from v_ElectronicTreatment et (nolock)
				where et.ElectronicTreatment_id in
				 (
					select distinct etl.ElectronicTreatment_id
					from v_ElectronicTreatmentLink etl (nolock)
					where (1=1) 
						and etl.ElectronicQueueInfo_id in ({$linkedEQ_list})
				)
			", array());

			if ($reasons_counter > 8) {
				return array('Error_Msg' => 'Добавление этого кабинета приведет к превышению допустимого количества групп поводов обращений, отображаемых на Инфомате.');
			}
		}

		$crossFilter = "";
		$crossParams = array(
			'LpuBuildingOffice_id' => $data['LpuBuildingOffice_id'],
			'LpuBuildingOfficeInfomat_begDT' => $data['LpuBuildingOfficeAssign_begDate'],
			'LpuBuildingOfficeInfomat_endDT' => !empty($data['LpuBuildingOfficeAssign_endDate']) ? $data['LpuBuildingOfficeAssign_endDate'] : '2050-01-01'
		);

		// исключим проверку по самому себе
		if (!empty($data['LpuBuildingOfficeInfomat_id'])) {
			$crossFilter .= ' and LpuBuildingOfficeInfomat_id != :LpuBuildingOfficeInfomat_id ';
			$crossParams['LpuBuildingOfficeInfomat_id'] = $data['LpuBuildingOfficeInfomat_id'];
		}

		$crossData = $this->getFirstRowFromQuery("
			select * from v_LpuBuildingOfficeInfomat (nolock)
			where LpuBuildingOffice_id = :LpuBuildingOffice_id
			and (
				:LpuBuildingOfficeInfomat_begDT BETWEEN LpuBuildingOfficeInfomat_begDT AND isnull(LpuBuildingOfficeInfomat_endDT, '2050-01-01')
				or :LpuBuildingOfficeInfomat_endDT BETWEEN LpuBuildingOfficeInfomat_begDT AND isnull(LpuBuildingOfficeInfomat_endDT, '2050-01-01')
			)
			{$crossFilter}
			",$crossParams
		);

		if (!empty($crossData['LpuBuildingOffice_id'])) {
			return array('Error_Msg' => 'Данный кабинет уже связан с этим инфоматом.');
		}

		$action = !empty($data['LpuBuildingOfficeInfomat_id']) ? "upd" : "ins";

		$query = "
			declare
				@LpuBuildingOfficeInfomat_id bigint = :LpuBuildingOfficeInfomat_id,
				@Error_Code bigint,
				@Error_Msg varchar(4000);

			exec p_LpuBuildingOfficeInfomat_{$action}
				@LpuBuildingOfficeInfomat_id = @LpuBuildingOfficeInfomat_id output,
				@LpuBuildingOffice_id = :LpuBuildingOffice_id,
				@ElectronicInfomat_id = :ElectronicInfomat_id,
				@LpuBuildingOfficeInfomat_begDT = :LpuBuildingOfficeInfomat_begDT,
				@LpuBuildingOfficeInfomat_endDT = :LpuBuildingOfficeInfomat_endDT,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Msg output;

			select @LpuBuildingOfficeInfomat_id as LpuBuildingOfficeInfomat_id, @Error_Code as Error_Code, @Error_Msg as Error_Msg;
		";

		$resp = $this->getFirstRowFromQuery($query, $params);
		return array($resp);
	}

	/**
	 * Сохраняет связь кабинета и табло
	 */
	function saveLpuBuildingOfficeScoreboard($data) {

		$params = array(
			'LpuBuildingOffice_id' => $data['LpuBuildingOffice_id'],
			'ElectronicScoreboard_id' => $data['ElectronicScoreboard_id'],
			'LpuBuildingOfficeScoreboard_begDT' => $data['LpuBuildingOfficeAssign_begDate'],
			'LpuBuildingOfficeScoreboard_endDT' => !empty($data['LpuBuildingOfficeAssign_endDate']) ? $data['LpuBuildingOfficeAssign_endDate'] : null,
			'LpuBuildingOfficeScoreboard_id' => $data['LpuBuildingOfficeScoreboard_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		$linkedRooms = $this->getFirstResultFromQuery("
			select count(LpuBuildingOfficeScoreboard_id) as cnt
			from v_LpuBuildingOfficeScoreboard (nolock) where ElectronicScoreboard_id = :ElectronicScoreboard_id
		 ", array('ElectronicScoreboard_id' => $data['ElectronicScoreboard_id'])
		);

		if ($linkedRooms > 12) {
			return array('Error_Msg' => 'Рекомендуемое количество кабинетов на одно электронное табло не более 12.');
		}

		$crossFilter = "";
		$crossParams =  array(
			'LpuBuildingOffice_id' => $data['LpuBuildingOffice_id'],
			'LpuBuildingOfficeScoreboard_begDT' => $data['LpuBuildingOfficeAssign_begDate'],
			'LpuBuildingOfficeScoreboard_endDT' => !empty($data['LpuBuildingOfficeAssign_endDate']) ? $data['LpuBuildingOfficeAssign_endDate'] : '2050-01-01'
		);

		// исключим проверку по самому себе
		if (!empty($data['LpuBuildingOfficeScoreboard_id'])) {
			$crossFilter .= ' and LpuBuildingOfficeScoreboard_id != :LpuBuildingOfficeScoreboard_id ';
			$crossParams['LpuBuildingOfficeScoreboard_id'] = $data['LpuBuildingOfficeScoreboard_id'];
		}

		$crossData = $this->getFirstRowFromQuery("
			select * from v_LpuBuildingOfficeScoreboard (nolock)
			where LpuBuildingOffice_id = :LpuBuildingOffice_id
			and (
				:LpuBuildingOfficeScoreboard_begDT BETWEEN LpuBuildingOfficeScoreboard_begDT AND isnull(LpuBuildingOfficeScoreboard_endDT, '2050-01-01')
				or :LpuBuildingOfficeScoreboard_endDT BETWEEN LpuBuildingOfficeScoreboard_begDT AND isnull(LpuBuildingOfficeScoreboard_endDT, '2050-01-01')
			)
			{$crossFilter}
			",$crossParams
		);

		if (!empty($crossData['LpuBuildingOffice_id'])) {
			return array('Error_Msg' => 'Данный кабинет уже связан с этим табло.');
		}

		$action = !empty($data['LpuBuildingOfficeScoreboard_id']) ? "upd" : "ins";

		$query = "
			declare
				@LpuBuildingOfficeScoreboard_id bigint = :LpuBuildingOfficeScoreboard_id,
				@Error_Code int,
				@Error_Msg varchar(4000);

			exec p_LpuBuildingOfficeScoreboard_{$action}
				@LpuBuildingOfficeScoreboard_id = @LpuBuildingOfficeScoreboard_id output,
				@LpuBuildingOffice_id = :LpuBuildingOffice_id,
				@ElectronicScoreboard_id = :ElectronicScoreboard_id,
				@LpuBuildingOfficeScoreboard_begDT = :LpuBuildingOfficeScoreboard_begDT,
				@LpuBuildingOfficeScoreboard_endDT = :LpuBuildingOfficeScoreboard_endDT,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Msg output;

			select @LpuBuildingOfficeScoreboard_id as LpuBuildingOfficeScoreboard_id, @Error_Code as Error_Code, @Error_Msg as Error_Msg;
		";

		$resp = $this->getFirstRowFromQuery($query, $params);
		return array($resp);
	}

	/**
	 * Загружает грид связи табло и кабинета
	 */
	function loadLpuBuildingOfficeScoreboard($data) {

		$query = "
			select
				lbos.LpuBuildingOfficeScoreboard_id,
				lbos.LpuBuildingOffice_id,
				lbos.ElectronicScoreboard_id,
				convert(varchar(10), lbos.LpuBuildingOfficeScoreboard_begDT, 104) as LpuBuildingOfficeScoreboard_begDT,
				convert(varchar(10), lbos.LpuBuildingOfficeScoreboard_endDT, 104) as LpuBuildingOfficeScoreboard_endDT,
				lbo.LpuBuildingOffice_Number,
				lbo.LpuBuildingOffice_Name,
				lbo.LpuBuildingOffice_Comment
			from v_LpuBuildingOfficeScoreboard lbos (nolock)
			left join v_LpuBuildingOffice lbo (nolock) on lbo.LpuBuildingOffice_id = lbos.LpuBuildingOffice_id
			left join v_ElectronicScoreboard eboard (nolock) on eboard.ElectronicScoreboard_id = lbos.ElectronicScoreboard_id
			where (1=1)
				and lbos.ElectronicScoreboard_id = :ElectronicScoreboard_id
		";

		return $this->queryResult($query, $data);
	}

	/**
	 * Загружает грид связи табло и кабинета
	 */
	function loadLpuBuildingOfficeInfomat($data) {

		$query = "
			select
				lboi.LpuBuildingOfficeInfomat_id,
				lboi.LpuBuildingOffice_id,
				lboi.ElectronicInfomat_id,
				convert(varchar(10), lboi.LpuBuildingOfficeInfomat_begDT, 104) as LpuBuildingOfficeInfomat_begDT,
				convert(varchar(10), lboi.LpuBuildingOfficeInfomat_endDT, 104) as LpuBuildingOfficeInfomat_endDT,
				lbo.LpuBuildingOffice_Number,
				lbo.LpuBuildingOffice_Name,
				lbo.LpuBuildingOffice_Comment
			from v_LpuBuildingOfficeInfomat lboi (nolock)
			left join v_LpuBuildingOffice lbo (nolock) on lbo.LpuBuildingOffice_id = lboi.LpuBuildingOffice_id
			left join v_ElectronicInfomat ei (nolock) on ei.ElectronicInfomat_id = lboi.ElectronicInfomat_id
			where (1=1)
				and lboi.ElectronicInfomat_id = :ElectronicInfomat_id
		";

		return $this->queryResult($query, $data);
	}

	/**
	 * Загружает данные формы назначения кабинета
	 */
	function loadLpuBuildingOfficeAssignData($data) {

		$result = array();

		if ($data['object'] === 'scoreboard') {
			$result = $this->queryResult(
				"
					select top 1
						LpuBuildingOfficeScoreboard_id,
						LpuBuildingOffice_id,
						ElectronicScoreboard_id,
						convert(varchar(10), LpuBuildingOfficeScoreboard_begDT, 104) as LpuBuildingOfficeAssign_begDate,
						convert(varchar(10), LpuBuildingOfficeScoreboard_endDT, 104) as LpuBuildingOfficeAssign_endDate
					from v_LpuBuildingOfficeScoreboard lbos (nolock)
					where LpuBuildingOfficeScoreboard_id = :LpuBuildingOfficeScoreboard_id
				", array('LpuBuildingOfficeScoreboard_id' => $data['assign_id'])
			);
		}

		if ($data['object'] === 'infomat') {
			$result = $this->queryResult(
				"
					select top 1
						LpuBuildingOfficeInfomat_id,
						LpuBuildingOffice_id,
						ElectronicInfomat_id,
						convert(varchar(10), LpuBuildingOfficeInfomat_begDT, 104) as LpuBuildingOfficeAssign_begDate,
						convert(varchar(10), LpuBuildingOfficeInfomat_endDT, 104) as LpuBuildingOfficeAssign_endDate
					from v_LpuBuildingOfficeInfomat lbos (nolock)
					where LpuBuildingOfficeInfomat_id = :LpuBuildingOfficeInfomat_id
				", array('LpuBuildingOfficeInfomat_id' => $data['assign_id'])
			);
		}

		return $result;
	}

	/**
	 * Удаляет данные формы назначения кабинета
	 */
	function deleteLpuBuildingOfficeScoreboard($data) {

		$query = "
			declare
				@Error_Code int,
				@Error_Msg varchar(4000);

			exec p_LpuBuildingOfficeScoreboard_del
				@LpuBuildingOfficeScoreboard_id = :LpuBuildingOfficeScoreboard_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Msg output;

			select @Error_Code as Error_Code, @Error_Msg as Error_Msg;
		";

		$resp = $this->queryResult($query, $data);
		if (!empty($resp['Error_Msg'])) {
			$result['success'] = false;
			$result['Error_Msg'] = $resp['Error_Msg'];
			return array($result);
		}

		$result['success'] = true;
		return array($result);
	}

	/**
	 * Удаляет данные формы назначения кабинета
	 */
	function deleteLpuBuildingOfficeInfomat($data) {

		$query = "
			declare
				@Error_Code int,
				@Error_Msg varchar(4000);

			exec p_LpuBuildingOfficeInfomat_del
				@LpuBuildingOfficeInfomat_id = :LpuBuildingOfficeInfomat_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Msg output;

			select @Error_Code as Error_Code, @Error_Msg as Error_Msg;
		";

		$resp = $this->queryResult($query, $data);
		if (!empty($resp['Error_Msg'])) {
			$result['success'] = false;
			$result['Error_Msg'] = $resp['Error_Msg'];
			return array($result);
		}

		$result['success'] = true;
		return array($result);
	}
}