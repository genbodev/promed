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

class LpuBuildingOffice_model extends swPgModel {

    protected $dateFormatTo104 = 'DD.MM.YY';

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
			select 
				LpuBuildingOfficeMedStaffLink_id as \"LpuBuildingOfficeMedStaffLink_id\"
			from 
				v_LpuBuildingOfficeMedStaffLink
			where 
				LpuBuildingOffice_id = :LpuBuildingOffice_id
			limit 1
		", $data);

		if ( !empty($LpuBuildingOfficeMedStaffLink_id) ) {
			$result['success'] = false;
			$result['Error_Msg'] = 'Удаление кабинета невозможно, т.к. есть связанные места работы';
			return array($result);
		}

		$query = "
		select 
		    Error_Code as \"Error_Code\", 
		    Error_Message as \"Error_Msg\"
		from p_LpuBuildingOffice_del (
			LpuBuildingOffice_id := :LpuBuildingOffice_id
		)";
	
		$resp = $this->queryResult($query, $data);

		if ( !empty($resp['Error_Msg']) ) {
			$result['success'] = false;
			$result['Error_Msg'] = $resp['Error_Msg'];
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
				 lbo.LpuBuildingOffice_id as \"LpuBuildingOffice_id\",
				 lbo.Lpu_id as \"Lpu_id\",
				 lbo.LpuBuilding_id as \"LpuBuilding_id\",
				 l.Lpu_Nick as \"Lpu_Nick\",
				 lb.LpuBuilding_Name as \"LpuBuilding_Name\",
				 lbo.LpuBuildingOffice_Number as \"LpuBuildingOffice_Number\",
				 lbo.LpuBuildingOffice_Name as \"LpuBuildingOffice_Name\",
				 lbo.LpuBuildingOffice_Comment as \"LpuBuildingOffice_Comment\",
				 to_char(lbo.LpuBuildingOffice_begDate, '{$this->dateFormatTo104}') as \"LpuBuildingOffice_begDate\",
				 to_char(lbo.LpuBuildingOffice_endDate, '{$this->dateFormatTo104}') as \"LpuBuildingOffice_endDate\"
				-- end select
			from
				-- from
				v_LpuBuildingOffice lbo
				inner join v_Lpu l on l.Lpu_id = lbo.Lpu_id
				inner join v_LpuBuilding lb on lb.LpuBuilding_id = lbo.LpuBuilding_id
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
				lbo.LpuBuildingOffice_id as \"LpuBuildingOffice_id\",
				lbo.LpuBuilding_id as \"LpuBuilding_id\",
				'{$prefix}' || lbo.LpuBuildingOffice_Number as \"LpuBuildingOffice_Number\",
				lbo.LpuBuildingOffice_Comment,
				lbo.LpuBuildingOffice_Name as \"LpuBuildingOffice_Name\",
				to_char(lbo.LpuBuildingOffice_begDate, '{$this->dateFormatTo104}') as \"LpuBuildingOffice_begDate\",
				to_char(lbo.LpuBuildingOffice_endDate, '{$this->dateFormatTo104}') as \"LpuBuildingOffice_endDate\"
			from
				v_LpuBuildingOffice lbo
			where
				lbo.Lpu_id = :Lpu_id
				{$filter}
		", $data);
	}

	/**
	 * Возвращает данные кабинета
	 */
	public function load($data) {
		$query = "
			select
			    --select
				 LpuBuildingOffice_id as \"LpuBuildingOffice_id\",
				 Lpu_id as \"Lpu_id\",
				 LpuBuilding_id as \"LpuBuilding_id\",
				 LpuBuildingOffice_Number as \"LpuBuildingOffice_Number\",
				 LpuBuildingOffice_Name as \"LpuBuildingOffice_Name\",
				 LpuBuildingOffice_Comment as \"LpuBuildingOffice_Comment\",
				 to_char(LpuBuildingOffice_begDate, '{$this->dateFormatTo104}') as \"LpuBuildingOffice_begDate\",
				 to_char(LpuBuildingOffice_endDate, '{$this->dateFormatTo104}') as \"LpuBuildingOffice_endDate\"
				 --select
			from
				v_LpuBuildingOffice
			where
				LpuBuildingOffice_id = :LpuBuildingOffice_id
			limit 1
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
					select 
					  LpuBuildingOffice_Number as \"LpuBuildingOffice_Number\"
					from 
					  v_LpuBuildingOffice
					where 
					  LpuBuildingOffice_id = :LpuBuildingOffice_id
					limit 1
				", $data, true);

				if ($data['LpuBuildingOffice_Number'] != $room_originalValue) {
					$isRoomOrginalValueChanged = true;
				}
			}

			// Проверяем уникальность номера кабинета
			$checkLpuBuildingOfficeNumber = $this->getFirstResultFromQuery("
				select 
                  LpuBuildingOffice_id as \"LpuBuildingOffice_id\"
				from 
				  v_LpuBuildingOffice
				where 
                  LpuBuildingOffice_Number = :LpuBuildingOffice_Number
                and 
                  Lpu_id = :Lpu_id::bigint
                and
                  LpuBuilding_id = :LpuBuilding_id::bigint
                and 
                  LpuBuildingOffice_id != coalesce(:LpuBuildingOffice_id::bigint, 0)
                and 
                  (:LpuBuildingOffice_endDate is null or LpuBuildingOffice_begDate <= :LpuBuildingOffice_endDate)
                and 
                  (LpuBuildingOffice_endDate is null or :LpuBuildingOffice_begDate <= LpuBuildingOffice_endDate)
				limit 1
			", $data, true);

			if ( $checkLpuBuildingOfficeNumber === false ) {
				throw new Exception('Ошибка при выполнении запроса к БД (' . __LINE__ . ')');
			}

			if ( !empty($checkLpuBuildingOfficeNumber) ) {
				throw new Exception('Кабинет с таким номером уже существует.');
			}

			if ( $action == 'upd' ) {
				// Проверяем даты действия кабинета
				$queryList = ["
				(
					select 
					  LpuBuildingOfficeMedStaffLink_id as \"LpuBuildingOfficeMedStaffLink_id\"
					from 
					  v_LpuBuildingOfficeMedStaffLink
					where 
					  LpuBuildingOffice_id = :LpuBuildingOffice_id
					and 
					  LpuBuildingOfficeMedStaffLink_begDate < :LpuBuildingOffice_begDate
					limit 1
                )
				"];

				if ( !empty($data['LpuBuildingOffice_endDate']) ) {
					$queryList[] = "
					(
						select 
						  LpuBuildingOfficeMedStaffLink_id as \"LpuBuildingOfficeMedStaffLink_id\"
						from 
						  v_LpuBuildingOfficeMedStaffLink
						where 
						  LpuBuildingOffice_id = :LpuBuildingOffice_id
						and 
						  (LpuBuildingOfficeMedStaffLink_endDate is null or LpuBuildingOfficeMedStaffLink_endDate > :LpuBuildingOffice_endDate)
						limit 1
						)
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
					select t.*
					from (
						(
							select 
								t3.MedService_Name as \"MedService_Name\"
							from 
								v_LpuBuildingOfficeVizitTime t1
								inner join v_LpuBuildingOfficeMedStaffLink t2 on t2.LpuBuildingOfficeMedStaffLink_id = t1.LpuBuildingOfficeMedStaffLink_id
								inner join v_MedService t3 on t3.MedService_id = t2.MedService_id
							where t2.LpuBuildingOffice_id = :LpuBuildingOffice_id
								and t3.LpuBuilding_id != :LpuBuilding_id
							limit 1
						)
						union all
						(
							select
								t3.MedService_Name as \"MedService_Name\"
							from 
								v_LpuBuildingOfficeVizitTime t1
								inner join v_LpuBuildingOfficeMedStaffLink t2 on t2.LpuBuildingOfficeMedStaffLink_id = t1.LpuBuildingOfficeMedStaffLink_id
								inner join v_MedService t3 on t3.MedService_id = t2.MedService_id
							where t2.LpuBuildingOffice_id = :LpuBuildingOffice_id
								and not (
									(:LpuBuildingOffice_endDate is null or t3.MedService_begDT <= :LpuBuildingOffice_endDate)
									and (t3.MedService_endDT is null or :LpuBuildingOffice_begDate <= t3.MedService_endDT)
								)
							limit 1
						)
					) t
				", $data, true);

				if ( $resp === false ) {
					throw new Exception('Ошибка при выполнении запроса к БД (' . __LINE__ . ')');
				}

				if ( !empty($resp) ) {
					throw new Exception('Для выбранного кабинета не может быть указана служба <b>' . $resp . '</b>, так как у них разные периоды действия и/или подразделения. (' . __LINE__ . ')');
				}

				$LpuBuildingOfficeMedStaffLink_id = $this->getFirstResultFromQuery("
					select 
						LpuBuildingOfficeMedStaffLink_id as \"LpuBuildingOfficeMedStaffLink_id\"
					from 
						v_LpuBuildingOfficeMedStaffLink
					where 
						LpuBuildingOffice_id = :LpuBuildingOffice_id
					limit 1
				", $data);

				if ( !empty($LpuBuildingOfficeMedStaffLink_id) ) {
					$data['LpuBuilding_id'] = $this->getFirstResultFromQuery("
                        select 
                        	LpuBuilding_id as \"LpuBuilding_id\" 
                        from 
                        	v_LpuBuildingOffice 
                        where 
                        	LpuBuildingOffice_id = :LpuBuildingOffice_id 
                        limit 1
                    ", $data);
				}
			}

			$query = "
                select 
                    LpuBuildingOffice_id as \"LpuBuildingOffice_id\",
                    Error_Code as \"Error_Code\",
                    Error_Message as \"Error_Msg\"
                from p_LpuBuildingOffice_{$action} (
                	LpuBuildingOffice_id := :LpuBuildingOffice_id,
                	Lpu_id := :Lpu_id,
                	LpuBuilding_id := :LpuBuilding_id,
                	LpuBuildingOffice_Number := :LpuBuildingOffice_Number,
                	LpuBuildingOffice_Name := :LpuBuildingOffice_Name,
                	LpuBuildingOffice_Comment := :LpuBuildingOffice_Comment,
                	LpuBuildingOffice_begDate := :LpuBuildingOffice_begDate,
                	LpuBuildingOffice_endDate := :LpuBuildingOffice_endDate,
                	pmUser_id := :pmUser_id
                )
			";

			$response = $this->queryResult($query, $data);

			if ( !empty($response[0]['Error_Msg']) ) {
				throw new Exception($response[0]['Error_Msg']);
			}

			if ($isRoomOrginalValueChanged) {

				// берем данные по врачам связанным с этим кабинетом
				$broadcast_data = $this->dbmodel->queryResult("
					select
						ls.LpuBuilding_id as \"LpuBuilding_id\",
						ls.LpuSection_id as \"LpuSection_id\",
						lbomsfl.LpuBuildingOfficeMedStaffLink_id as \"LpuBuildingOfficeMedStaffLink_id\"
					from 
					--from
                        v_LpuBuildingOffice lbo
                        inner join v_LpuBuildingOfficeMedStaffLink lbomsfl on lbomsfl.LpuBuildingOffice_id = lbo.LpuBuildingOffice_id
                        inner join v_MedStaffFact msf on msf.MedStaffFact_id = lbomsfl.MedStaffFact_id
                        inner join v_LpuSection ls on ls.LpuSection_id = msf.LpuSection_id
					--from end
					where 
					    lbo.LpuBuildingOffice_id = :LpuBuildingOffice_id
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
			select
				eqlist.ElectronicQueueInfo_id as \"ElectronicQueueInfo_id\"
			from (
				select 
					lboi.LpuBuildingOffice_id,
					lboml.MedStaffFact_id,
					lboml.MedService_id,
					COALESCE(eqiMedService.ElectronicQueueInfo_id, es.ElectronicQueueInfo_id) as ElectronicQueueInfo_id
                from v_LpuBuildingOfficeInfomat lboi
                	left join v_LpuBuildingOffice lbo on lbo.LpuBuildingOffice_id = lboi.LpuBuildingOffice_id
                	left join v_LpuBuildingOfficeMedStaffLink lboml on lboml.LpuBuildingOffice_id = lbo.LpuBuildingOffice_id
                	left join v_ElectronicQueueInfo eqiMedService on eqiMedService.MedService_id = lboml.MedService_id
                	LEFT JOIN LATERAL(
                		select
                			es.ElectronicQueueInfo_id
                		from v_ElectronicService es
                			left join v_MedServiceElectronicQueue mseq on mseq.ElectronicService_id = es.ElectronicService_id
                		where mseq.MedStaffFact_id = lboml.MedStaffFact_id
                		order by mseq.MedServiceElectronicQueue_id desc
                		limit 1
                	) es ON true
                where (1 = 1)
                	and lboi.ElectronicInfomat_id =:ElectronicInfomat_id 
                    and lboi.LpuBuildingOfficeInfomat_begDT < dbo.tzGetDate()
                    and COALESCE(lboi.LpuBuildingOfficeInfomat_endDT, '2050-01-01 00:00:00') > dbo.tzGetDate()
                union all
                select
                	lbo.LpuBuildingOffice_id,
                	lboml.MedStaffFact_id,
                	lboml.MedService_id,
                	COALESCE(eqiMedService.ElectronicQueueInfo_id, es.ElectronicQueueInfo_id) as ElectronicQueueInfo_id
                from v_LpuBuildingOffice lbo
                	left join v_LpuBuildingOfficeMedStaffLink lboml on lboml.LpuBuildingOffice_id = lbo.LpuBuildingOffice_id
                	left join v_ElectronicQueueInfo eqiMedService on eqiMedService.MedService_id = lboml.MedService_id
                	LEFT JOIN LATERAL(
                		select es.ElectronicQueueInfo_id
                		from v_ElectronicService es
                		     left join v_MedServiceElectronicQueue mseq on mseq.ElectronicService_id = es.ElectronicService_id
                		where mseq.MedStaffFact_id = lboml.MedStaffFact_id
                		order by mseq.MedServiceElectronicQueue_id desc
                		limit 1
                	) es ON true
                where (1 = 1) and
                	lbo.LpuBuildingOffice_id =:LpuBuildingOffice_id
            ) as eqlist
            where eqlist.ElectronicQueueInfo_id is not null
		", [
		 		'ElectronicInfomat_id' => $data['ElectronicInfomat_id'],
				'LpuBuildingOffice_id' => $data['LpuBuildingOffice_id']
		]);

		if (!empty($linkedEQ)) {

			$linkedEQ_list = implode(',',array_unique(array_column($linkedEQ, 'ElectronicQueueInfo_id')));

			$reasons_counter = $this->getFirstResultFromQuery("
			select count(distinct ElectronicTreatment_pid) as \"cnt\"
			from v_ElectronicTreatment et
			where et.ElectronicTreatment_id in (
				select distinct
					etl.ElectronicTreatment_id
				from v_ElectronicTreatmentLink etl
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
			select
				LpuBuildingOfficeInfomat_id as \"LpuBuildingOfficeInfomat_id\",
    			LpuBuildingOffice_id as \"LpuBuildingOffice_id\",
    			ElectronicInfomat_id as \"ElectronicInfomat_id\",
    			LpuBuildingOfficeInfomat_begDT as \"LpuBuildingOfficeInfomat_begDT\",
    			LpuBuildingOfficeInfomat_endDT as \"LpuBuildingOfficeInfomat_endDT\",
    			pmUser_insID as \"pmUser_insID\",
    			pmUser_updID as \"pmUser_updID\",
    			LpuBuildingOfficeInfomat_insDT as \"LpuBuildingOfficeInfomat_insDT\",
    			LpuBuildingOfficeInfomat_updDT as \"LpuBuildingOfficeInfomat_updDT\"
			from v_LpuBuildingOfficeInfomat
			where LpuBuildingOffice_id = :LpuBuildingOffice_id
			and (
				:LpuBuildingOfficeInfomat_begDT BETWEEN LpuBuildingOfficeInfomat_begDT AND coalesce(LpuBuildingOfficeInfomat_endDT, '2050-01-01')
				or :LpuBuildingOfficeInfomat_endDT BETWEEN LpuBuildingOfficeInfomat_begDT AND coalesce(LpuBuildingOfficeInfomat_endDT, '2050-01-01')
			)
			{$crossFilter}
			",$crossParams
		);

		if (!empty($crossData['LpuBuildingOffice_id'])) {
			return array('Error_Msg' => 'Данный кабинет уже связан с этим инфоматом.');
		}

		$action = !empty($data['LpuBuildingOfficeInfomat_id']) ? "upd" : "ins";

		$query = "
			select
				LpuBuildingOfficeInfomat_id as \"LpuBuildingOfficeInfomat_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_LpuBuildingOfficeInfomat_{$action}(
				LpuBuildingOfficeInfomat_id := :LpuBuildingOfficeInfomat_id,
				LpuBuildingOffice_id := :LpuBuildingOffice_id,
				ElectronicInfomat_id := :ElectronicInfomat_id,
				LpuBuildingOfficeInfomat_begDT := :LpuBuildingOfficeInfomat_begDT,
				LpuBuildingOfficeInfomat_endDT := :LpuBuildingOfficeInfomat_endDT,
				pmUser_id := :pmUser_id
			);
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
			  select count(LpuBuildingOfficeScoreboard_id) as \"cnt\"
              from v_LpuBuildingOfficeScoreboard
              where ElectronicScoreboard_id =:ElectronicScoreboard_id
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
			select
				LpuBuildingOfficeScoreboard_id as \"LpuBuildingOfficeScoreboard_id\",
				LpuBuildingOffice_id as \"LpuBuildingOffice_id\",
				ElectronicScoreboard_id as \"ElectronicScoreboard_id\",
				LpuBuildingOfficeScoreboard_begDT as \"LpuBuildingOfficeScoreboard_begDT\",
				LpuBuildingOfficeScoreboard_endDT as \"LpuBuildingOfficeScoreboard_endDT\",
				pmUser_insID as \"pmUser_insID\",
				pmUser_updID as \"pmUser_updID\",
				LpuBuildingOfficeScoreboard_insDT as \"LpuBuildingOfficeScoreboard_insDT\",
				LpuBuildingOfficeScoreboard_updDT as \"LpuBuildingOfficeScoreboard_updDT\"
        	from v_LpuBuildingOfficeScoreboard
        	where LpuBuildingOffice_id =:LpuBuildingOffice_id
        		and (
        		:LpuBuildingOfficeScoreboard_begDT BETWEEN LpuBuildingOfficeScoreboard_begDT
        			AND coalesce(LpuBuildingOfficeScoreboard_endDT, '2050-01-01')
        		or :LpuBuildingOfficeScoreboard_endDT BETWEEN LpuBuildingOfficeScoreboard_begDT
        		AND colaesce(LpuBuildingOfficeScoreboard_endDT, '2050-01-01')
        		)
				{$crossFilter}
		", $crossParams);

		if (!empty($crossData['LpuBuildingOffice_id'])) {
			return array('Error_Msg' => 'Данный кабинет уже связан с этим табло.');
		}

		$action = !empty($data['LpuBuildingOfficeScoreboard_id']) ? "upd" : "ins";

		$query = "
			select
				LpuBuildingOfficeScoreboard_id as \"LpuBuildingOfficeScoreboard_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_LpuBuildingOfficeScoreboard_{$action}(
				LpuBuildingOfficeScoreboard_id := :LpuBuildingOfficeScoreboard_id,
				LpuBuildingOffice_id := :LpuBuildingOffice_id,
				ElectronicScoreboard_id := :ElectronicScoreboard_id,
				LpuBuildingOfficeScoreboard_begDT := :LpuBuildingOfficeScoreboard_begDT,
				LpuBuildingOfficeScoreboard_endDT := :LpuBuildingOfficeScoreboard_endDT,
				pmUser_id := :pmUser_id
			);
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
				lbos.LpuBuildingOfficeScoreboard_id as \"LpuBuildingOfficeScoreboard_id\",
				lbos.LpuBuildingOffice_id as \"LpuBuildingOffice_id\",
				lbos.ElectronicScoreboard_id as \"ElectronicScoreboard_id\",
				to_char(lbos.LpuBuildingOfficeScoreboard_begDT, 'dd.mm.yyyy') as \"LpuBuildingOfficeScoreboard_begDT\",
				to_char(lbos.LpuBuildingOfficeScoreboard_endDT, 'dd.mm.yyyy') as \"LpuBuildingOfficeScoreboard_endDT\",
				lbo.LpuBuildingOffice_Number as \"LpuBuildingOffice_Number\",
				lbo.LpuBuildingOffice_Name as \"LpuBuildingOffice_Name\",
				lbo.LpuBuildingOffice_Comment as \"LpuBuildingOffice_Comment\"
			from v_LpuBuildingOfficeScoreboard lbos
				left join v_LpuBuildingOffice lbo on lbo.LpuBuildingOffice_id = lbos.LpuBuildingOffice_id
				left join v_ElectronicScoreboard eboard on eboard.ElectronicScoreboard_id = lbos.ElectronicScoreboard_id
			where (1 = 1)
				and lbos.ElectronicScoreboard_id =:ElectronicScoreboard_id
		";

		return $this->queryResult($query, $data);
	}

	/**
	 * Загружает грид связи табло и кабинета
	 */
	function loadLpuBuildingOfficeInfomat($data) {

		$query = "
			select
				lboi.LpuBuildingOfficeInfomat_id as \"LpuBuildingOfficeInfomat_id\",
				lboi.LpuBuildingOffice_id as \"LpuBuildingOffice_id\",
				lboi.ElectronicInfomat_id as \"ElectronicInfomat_id\",
				to_char (lboi.LpuBuildingOfficeInfomat_begDT, 'dd.mm.yyyy') as \"LpuBuildingOfficeInfomat_begDT\",
				to_char (lboi.LpuBuildingOfficeInfomat_endDT, 'dd.mm.yyyy') as \"LpuBuildingOfficeInfomat_endDT\",
				lbo.LpuBuildingOffice_Number as \"LpuBuildingOffice_Number\",
				lbo.LpuBuildingOffice_Name as \"LpuBuildingOffice_Name\",
				lbo.LpuBuildingOffice_Comment as \"LpuBuildingOffice_Comment\"
			from v_LpuBuildingOfficeInfomat lboi 
			left join v_LpuBuildingOffice lbo on lbo.LpuBuildingOffice_id = lboi.LpuBuildingOffice_id
			left join v_ElectronicInfomat ei on ei.ElectronicInfomat_id = lboi.ElectronicInfomat_id
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
			$result = $this->queryResult("
				select
					LpuBuildingOfficeScoreboard_id as \"LpuBuildingOfficeScoreboard_id\",
					LpuBuildingOffice_id as \"LpuBuildingOffice_id\",
					ElectronicScoreboard_id as \"ElectronicScoreboard_id\",
					to_char(LpuBuildingOfficeScoreboard_begDT, 'dd.mm.yyyy') as \"LpuBuildingOfficeAssign_begDate\",
					to_char(LpuBuildingOfficeScoreboard_endDT, 'dd.mm.yyyy') as \"LpuBuildingOfficeAssign_endDate\"
				from v_LpuBuildingOfficeScoreboard lbos
				where LpuBuildingOfficeScoreboard_id =:LpuBuildingOfficeScoreboard_id
				limit 1
			", [
				'LpuBuildingOfficeScoreboard_id' => $data['assign_id']
			]);
		}

		if ($data['object'] === 'infomat') {
			$result = $this->queryResult("
				select
					LpuBuildingOfficeInfomat_id as \"LpuBuildingOfficeInfomat_id\",
					LpuBuildingOffice_id as \"LpuBuildingOffice_id\",
					ElectronicInfomat_id as \"ElectronicInfomat_id\",
					to_char(LpuBuildingOfficeInfomat_begDT, 'dd.mm.yyyy') as \"LpuBuildingOfficeAssign_begDate\",
					to_char(LpuBuildingOfficeInfomat_endDT, 'dd.mm.yyyy') as \"LpuBuildingOfficeAssign_endDate\"
            	from v_LpuBuildingOfficeInfomat lbos
            	where LpuBuildingOfficeInfomat_id =:LpuBuildingOfficeInfomat_id
            	limit 1
			", [
				'LpuBuildingOfficeInfomat_id' => $data['assign_id']
			]);
		}

		return $result;
	}

	/**
	 * Удаляет данные формы назначения кабинета
	 */
	function deleteLpuBuildingOfficeScoreboard($data) {

		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_LpuBuildingOfficeScoreboard_del(
				LpuBuildingOfficeScoreboard_id := :LpuBuildingOfficeScoreboard_id
			);
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
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_LpuBuildingOfficeInfomat_del(
				LpuBuildingOfficeInfomat_id := :LpuBuildingOfficeInfomat_id
			);
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