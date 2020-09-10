<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * ElectronicTreatment_model - модель для работы со справочником поводов обращений
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 */

class ElectronicTreatment_model extends swModel {
	/**
	 * Конструктор объекта
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Удаление повода
	 */
	public function delete($data) {
		$result = array();

		$this->beginTransaction();

		$electronicTreatmentData = $this->getFirstRowFromQuery("
			select top 1
				ET.ElectronicTreatment_pid,
				ET.ElectronicTreatmentLevel_id,
				ETChild.ElectronicTreatment_id as ElectronicTreatment_Count
			from
				v_ElectronicTreatment ET with (nolock)
				outer apply (
					select top 1 ElectronicTreatment_id
					from v_ElectronicTreatment with (nolock)
					where ElectronicTreatment_pid = ET.ElectronicTreatment_id
				) ETChild
			where
				ET.ElectronicTreatment_id = :ElectronicTreatment_id
		", $data);

		if ( is_array($electronicTreatmentData) && count($electronicTreatmentData) > 0 ) {
			// Удаляется группа поводов и есть связанные поводы обращений
			if ( !empty($electronicTreatmentData['ElectronicTreatment_Count']) ) {
				$result['success'] = false;
				$result['Error_Msg'] = 'Удаление группы поводов обращений невозможно, т.к. есть связанные поводы обращений';
				$this->rollbackTransaction();
				return array($result);
			}

			// Повод
			if ( !empty($electronicTreatmentData['ElectronicTreatment_pid']) ) {
				// Чистим связанные очереди
				$query = "
					select
						ElectronicTreatmentLink_id
					from
						v_ElectronicTreatmentLink with (nolock)
					where
						ElectronicTreatment_id = :ElectronicTreatment_id
				";
				$resp = $this->queryResult($query, $data);

				if ( is_array($resp) && count($resp) > 0 ) {
					foreach ( $resp as $queueLink ) {
						$response = $this->deleteElectronicTreatmentLink(array(
							'ElectronicTreatmentLink_id' => $queueLink['ElectronicTreatmentLink_id']
						));

						if ( !empty($response['Error_Msg']) ) {
							$result['success'] = false;
							$result['Error_Msg'] = $response['Error_Msg'];
							$this->rollbackTransaction();
							return array($result);
						}
					}
				}
			}
		}
		
		$query = "
			declare
				@Error_Code int,
				@Error_Msg varchar(4000);

			exec p_ElectronicTreatment_del
				@ElectronicTreatment_id = :ElectronicTreatment_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Msg output;

			select @Error_Code as Error_Code, @Error_Msg as Error_Msg;
		";
		$resp = $this->queryResult($query, $data);

		if ( !empty($resp['Error_Msg']) ) {
			$result['success'] = false;
			$result['Error_Msg'] = $response['Error_Msg'];
			$this->rollbackTransaction();
			return array($result);
		}

		$result['success'] = true;

		$this->commitTransaction();

		return array($result);
	}

	/**
	 * Возвращает количество групп поводов обращения связанных с очередьми
	 */
	public function getCountTreatmentGroup($data) {

		$ElectronicQueueInfoIds = implode(', ', $data['ElectronicQueueInfoIds']);

		$query = "
			select count(distinct ET.ElectronicTreatment_pid) as ElectronicTreatment_Count
			from 
				v_ElectronicTreatmentLink ETL with(nolock)
				inner join v_ElectronicTreatment ET with(nolock) on ET.ElectronicTreatment_id = ETL.ElectronicTreatment_id
			where
				ETL.ElectronicQueueInfo_id in ({$ElectronicQueueInfoIds})
		";
		return $this->queryResult($query, $data);
	}

	/**
	 * Возвращает список поводов обращений
	 */
	public function loadList($data) {
		$fieldsList = array();
		$filterList = array('(1 = 1)');
		$joinList = array();
		$queryParams = array();

		if ( !isSuperAdmin() ) {
			$data['Lpu_id'] = $data['session']['lpu_id'];
		}

		if ( !empty($data['ElectronicTreatment_pid'])) {
			$fieldsList[] = "et.ElectronicTreatment_pid";
			$fieldsList[] = "substring(CAST(eqiCodes.ElectronicQueueInfo_Codes AS nvarchar(max)), 1, len(CAST(eqiCodes.ElectronicQueueInfo_Codes AS nvarchar(max))) - 1) as ElectronicQueues";
			$filterList[] = "et.ElectronicTreatment_pid = :ElectronicTreatment_pid";
			$filterList[] = "et.ElectronicTreatmentLevel_id = 2";
			$joinList[] = "
				outer apply (
					select (select
					ISNULL(CAST(eqi.ElectronicQueueInfo_Code as VARCHAR(10)),'') + ',' as 'data()'
					from v_ElectronicQueueInfo eqi (nolock)
					inner join v_ElectronicTreatmentLink etl (nolock) on etl.ElectronicQueueInfo_id = eqi.ElectronicQueueInfo_id
					where etl.ElectronicTreatment_id = et.ElectronicTreatment_id
					for xml path(''), TYPE) as ElectronicQueueInfo_Codes
				) eqiCodes
			";
			$queryParams['ElectronicTreatment_pid'] = $data['ElectronicTreatment_pid'];
		}
		else {
			$fieldsList[] = "LB.LpuBuilding_id";
			$fieldsList[] = "LB.LpuBuilding_Name";
			$joinList[] = "left join LpuBuilding LB with(nolock) on LB.LpuBuilding_id = et.LpuBuilding_id";
			$filterList[] = "et.ElectronicTreatmentLevel_id = 1";
		}

		if ( !empty($data['Lpu_id'])) {
			$filterList[] = "et.Lpu_id = :Lpu_id";
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}

		$query = "
			select
				-- select
				et.ElectronicTreatment_id
				,et.ElectronicTreatmentLevel_id
				,et.Lpu_id
				,et.ElectronicTreatment_Code
				,et.ElectronicTreatment_Name
				,et.ElectronicTreatment_Descr
				,convert(varchar(10), et.ElectronicTreatment_begDate, 104) as ElectronicTreatment_begDate
				,convert(varchar(10), et.ElectronicTreatment_endDate, 104) as ElectronicTreatment_endDate
				,et.ElectronicTreatment_isConfirmPage
				,et.ElectronicTreatment_isFIOShown
				,l.Lpu_Nick
				" . (count($fieldsList) > 0 ? ',' . implode(',', $fieldsList) : '') . "
				-- end select
			from
				-- from
				v_ElectronicTreatment et with(nolock)
				inner join v_Lpu l with (nolock) on l.Lpu_id = et.Lpu_id
				" . (count($joinList) > 0 ? implode(' ', $joinList) : '') . "
				-- end from
			where
				-- where
				" . implode(' and ', $filterList) . "
				-- end where
			order by
				-- order by
				et.ElectronicTreatment_begDate desc
				-- end order by
		";

		$response = $this->getPagingResponse($query, $queryParams, $data['start'], $data['limit'], true);

		return $response;
	}

	/**
	 * Возвращает список связанных с поводом инфоматов
	 */
	public function loadElectronicInfomatTreatmentLink($data) {

		$params = array(
			'ElectronicTreatment_id' => $data['ElectronicTreatment_id']
		);

		$query = "
			select
				-- select
				eitl.ElectronicInfomatTreatmentLink_id
				,eitl.ElectronicInfomat_id
				,eitl.ElectronicTreatment_id
				,ei.LpuBuilding_id
				,ei.ElectronicInfomat_Name
				,lb.LpuBuilding_Name
				,a.Address_Address as LpuBuilding_Address
				-- end select
			from
				-- from
				v_ElectronicInfomatTreatmentLink eitl with(nolock)
				inner join v_ElectronicInfomat ei with(nolock) on ei.ElectronicInfomat_id = eitl.ElectronicInfomat_id
				left join v_LpuBuilding lb with(nolock) on lb.LpuBuilding_id = ei.LpuBuilding_id
				left join v_Address a with(nolock) on a.Address_id = lb.Address_id
				-- end from
			where
				-- where
				eitl.ElectronicTreatment_id = :ElectronicTreatment_id
				-- end where
			order by
				-- order by
				eitl.ElectronicInfomatTreatmentLink_id desc
				-- end order by
		";

		$response = $this->getPagingResponse($query, $params, $data['start'], $data['limit'], true);
		return $response;
	}

	/**
	 * Возвращает список групп поводов обращений (для комбо)
	 */
	public function loadElectronicTreatmentGroupCombo($data) {
		if ( !isSuperAdmin() ) {
			$data['Lpu_id'] = $data['session']['lpu_id'];
		}

		return $this->queryResult("
			select
				et.ElectronicTreatment_id
				,et.ElectronicTreatment_Code
				,et.ElectronicTreatment_Name
			from
				v_ElectronicTreatment et with(nolock)
			where
				et.Lpu_id = :Lpu_id
				and et.ElectronicTreatmentLevel_id = 1
		", $data);
	}

	/**
	 * Возвращает повод обращения
	 */
	public function load($data) {
		$query = "
			select top 1
				ElectronicTreatment_id
				,ElectronicTreatment_pid
				,ElectronicTreatmentLevel_id
				,Lpu_id
				,ElectronicTreatment_Code
				,ElectronicTreatment_Name
				,ElectronicTreatment_Descr
				,convert(varchar(10), ElectronicTreatment_begDate, 104) as ElectronicTreatment_begDate
				,convert(varchar(10), ElectronicTreatment_endDate, 104) as ElectronicTreatment_endDate
				,ElectronicTreatment_isConfirmPage
				,ElectronicTreatment_isFIOShown
			from
				v_ElectronicTreatment with (nolock)
			where
				ElectronicTreatment_id = :ElectronicTreatment_id
		";

		return $this->queryResult($query, $data);
	}

	/**
	 * Возвращает список очередей для повода обращения
	 */
	public function loadElectronicTreatmentQueues($data) {
		$query = "
			select
				etl.ElectronicTreatmentLink_id
				,etl.ElectronicTreatment_id
				,eqi.ElectronicQueueInfo_id
				,eqi.ElectronicQueueInfo_Code
				,eqi.ElectronicQueueInfo_Name
				,ms.MedService_Name
				,lb.LpuBuilding_Name
				,ls.LpuSection_Name
				,convert(varchar(10), eqi.ElectronicQueueInfo_begDate, 104) as ElectronicQueueInfo_begDate
				,convert(varchar(10), eqi.ElectronicQueueInfo_endDate, 104) as ElectronicQueueInfo_endDate
			from
				v_ElectronicTreatmentLink etl with(nolock)
				left join v_ElectronicQueueInfo eqi with (nolock) on eqi.ElectronicQueueInfo_id = etl.ElectronicQueueInfo_id
				left join v_MedService ms with (nolock) on ms.MedService_id = eqi.MedService_id
				left join v_LpuBuilding lb with (nolock) on lb.LpuBuilding_id = eqi.LpuBuilding_id
				left join v_LpuSection ls with (nolock) on ls.LpuSection_id = eqi.LpuSection_id
			where
				ElectronicTreatment_id = :ElectronicTreatment_id
		";

		return $this->queryResult($query, $data);
	}

	/**
	 * Сохраняет повод обращения
	 */
	public function save($data) {
		try {
			// начнем транзакцию
			$this->beginTransaction();

			$action = (empty($data['ElectronicTreatment_id']) ? 'ins' : 'upd');

			if ( !isSuperAdmin() ) {
				$data['Lpu_id'] = $data['session']['lpu_id'];
			}

			// проверим дату
			if ( !empty($data['ElectronicTreatment_begDate']) && !empty($data['ElectronicTreatment_endDate']) && $data['ElectronicTreatment_begDate'] > $data['ElectronicTreatment_endDate'] ) {
				throw new Exception('Дата начала не может быть больше даты окончания');
			}

			if ( !empty($data['ElectronicTreatment_id']) ) {
				$Lpu_id = $this->getFirstResultFromQuery("
					select top 1 Lpu_id
					from v_ElectronicTreatment with (nolock)
					where ElectronicTreatment_id = :ElectronicTreatment_id
				", $data);

				if ( empty($Lpu_id) || $Lpu_id === false ) {
					throw new Exception('Ошибка при получении идентификатора МО');
				}

				if ( $Lpu_id != $data['Lpu_id'] ) {
					if ( !isSuperAdmin() ) {
						throw new Exception('Вы не можете редактировать записи других МО');
					}

					// Если редактируем группу поводов обращений и меняется Lpu_id, то проевряем наличие связанных очередей
					if ( empty($data['ElectronicTreatment_pid']) && $action == 'upd'  ) {
						$checkLinkedElectronicQueue = $this->getFirstResultFromQuery("
							select top 1 eqi.ElectronicQueueInfo_id
							from v_ElectronicTreatmentLink etl with (nolock)
								inner join ElectronicQueueInfo eqi with (nolock) on eqi.ElectronicQueueInfo_id = etl.ElectronicQueueInfo_id
								inner join ElectronicTreatment et with (nolock) on et.ElectronicTreatment_id = etl.ElectronicTreatment_id
							where et.ElectronicTreatment_pid = :ElectronicTreatment_id
						", $data, true);

						if ( $checkLinkedElectronicQueue === false ) {
							throw new Exception('Ошибка при выполнении запроса к БД (' . __LINE__ . ')');
						}

						if ( !empty($checkLinkedElectronicQueue) ) {
							throw new Exception('Изменение МО невозможно, т.к. имеются связанные очереди из другой МО');
						}
					}
				}
			}

			// Проверяем уникальность кода
			$checkElectronicTreatmentCode = $this->getFirstResultFromQuery("
				select top 1 ElectronicTreatment_id
				from v_ElectronicTreatment with (nolock)
				where ElectronicTreatment_Code = :ElectronicTreatment_Code
					and Lpu_id = :Lpu_id
					and ElectronicTreatmentLevel_id = :ElectronicTreatmentLevel_id
					and ElectronicTreatment_id != ISNULL(:ElectronicTreatment_id, 0)
					and ElectronicTreatment_begDate < ISNULL(cast(:ElectronicTreatment_endDate as datetime), ElectronicTreatment_begDate + 1)
					and cast(:ElectronicTreatment_begDate as datetime) < ISNULL(ElectronicTreatment_endDate, cast(:ElectronicTreatment_begDate as datetime) + 1)
			", $data, true);

			if ( $checkElectronicTreatmentCode === false ) {
				throw new Exception('Ошибка при выполнении запроса к БД (' . __LINE__ . ')');
			}

			if ( !empty($checkElectronicTreatmentCode) ) {
				throw new Exception('Код ' . ($data['ElectronicTreatmentLevel_id'] == 1 ? 'повода' : 'группы поводов') . ' обращения должен быть уникальным в рамках МО, действующих в определенный период времени.');
			}

			$filter = '';
			if( !empty($data['LpuBuilding_id']) ) {
				$filter .= " and LpuBuilding_id = :LpuBuilding_id"; 
			} else {
				$filter .= " and LpuBuilding_id is null";
			}
			
			// Проверяем количество (не больше 8 групп)
			$checkElectronicTreatmentCount = $this->getFirstResultFromQuery("
				select count(ElectronicTreatment_id) as cnt
				from v_ElectronicTreatment with (nolock)
				where Lpu_id = :Lpu_id
					{$filter}
					and ElectronicTreatmentLevel_id = :ElectronicTreatmentLevel_id
					and ElectronicTreatment_id != ISNULL(:ElectronicTreatment_id, 0)
					and ISNULL(ElectronicTreatment_pid, 0) = ISNULL(:ElectronicTreatment_pid, 0)
					and ElectronicTreatment_begDate <= ISNULL(cast(:ElectronicTreatment_endDate as datetime), ElectronicTreatment_begDate + 1)
					and cast(:ElectronicTreatment_begDate as datetime) <= ISNULL(ElectronicTreatment_endDate, cast(:ElectronicTreatment_begDate as datetime) + 1)
			", $data, true);

			if ( $checkElectronicTreatmentCount === false ) {
				throw new Exception('Ошибка при выполнении запроса к БД (' . __LINE__ . ')');
			}

			if ( !empty($checkElectronicTreatmentCount) ) {
				if ( $data['ElectronicTreatmentLevel_id'] == 1 ) {

					if ( $checkElectronicTreatmentCount >= 8 ) {
						if ( !empty($data['LpuBuilding_id']) ) {
							throw new Exception('В одном подразделение может быть создано не более 8 групп поводов обращения.');
						} else {
							throw new Exception('В одной медицинской организации может быть создано не более 8 групп поводов обращения без привязки к подразделению.');
						}
					}
				}
				else if( $checkElectronicTreatmentCount >= 6 ){
					throw new Exception('Для одной группы поводов обращения может быть создано не более 6 поводов обращения, действующих в определенный период времени.');
				}
			}

			$query = "
				declare
					@ElectronicTreatment_id bigint = :ElectronicTreatment_id,
					@Error_Code bigint,
					@Error_Message varchar(4000);

				exec p_ElectronicTreatment_{$action}
					@ElectronicTreatment_id = @ElectronicTreatment_id output,
					@ElectronicTreatment_pid = :ElectronicTreatment_pid,
					@ElectronicTreatmentLevel_id = :ElectronicTreatmentLevel_id,
					@Lpu_id = :Lpu_id,
					@LpuBuilding_id = :LpuBuilding_id,
					@ElectronicTreatment_Code = :ElectronicTreatment_Code,
					@ElectronicTreatment_Name = :ElectronicTreatment_Name,
					@ElectronicTreatment_Descr = :ElectronicTreatment_Descr,
					@ElectronicTreatment_begDate = :ElectronicTreatment_begDate,
					@ElectronicTreatment_endDate = :ElectronicTreatment_endDate,
					@ElectronicTreatment_isConfirmPage = :ElectronicTreatment_isConfirmPage,
					@ElectronicTreatment_isFIOShown = :ElectronicTreatment_isFIOShown,
					@pmUser_id = :pmUser_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output;

				select @ElectronicTreatment_id as ElectronicTreatment_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
			";

			$response = $this->queryResult($query, $data);

			if ( !empty($response[0]['Error_Msg']) ) {
				throw new Exception($response[0]['Error_Msg']);
			}

			$data['ElectronicTreatment_id'] = $response[0]['ElectronicTreatment_id'];

			if ( empty($data['ElectronicTreatment_pid']) && $action == 'upd'  ) {
				// Если редактируем группу поводов обращений, то нужно менять Lpu_id у входящих в нее поводов обращений
				$query = "
					declare
						@Error_Code bigint = 0,
						@Error_Message varchar(4000) = '';

					set nocount on;

					begin try
						update ElectronicTreatment with (rowlock)
						set Lpu_id = :Lpu_id
						where ElectronicTreatment_pid = :ElectronicTreatment_id
					end try

					begin catch
						set @Error_Code = error_number()
						set @Error_Message = error_message()
					end catch

					set nocount off;

					select @Error_Code as Error_Code, @Error_Message as Error_Msg
				";

				$updateLpuRes = $this->getFirstRowFromQuery($query, $data);

				if ( $updateLpuRes === false || !is_array($updateLpuRes) || count($updateLpuRes) == 0 ) {
					throw new Exception('Ошибка при выполнении запроса к БД (' . __LINE__ . ')');
				}

				if ( !empty($updateLpuRes['Error_Msg']) ) {
					throw new Exception($updateLpuRes['Error_Msg']);
				}
			}

			// Обработка queueData
			if ( !empty($data['ElectronicTreatment_pid']) && !empty($data['queueData']) ) {
				// Сформируем доп. параметры для передачи
				$linkedQueueParams = array(
					'ElectronicTreatment_id' => $data['ElectronicTreatment_id'],
					'jsonData' => $data['queueData'],
					'pmUser_id' => $data['pmUser_id'],
					'Server_id' => $data['Server_id']
				);

				$saveLinkedQueueRes = $this->updateElectronicTreatmentLink($linkedQueueParams);

				if ( !empty($saveLinkedQueueRes[0]['Error_Msg'])) {
					throw new Exception($saveLinkedQueueRes[0]['Error_Msg']);
				}
			}

			$this->commitTransaction() ;
		}
		catch ( Exception $e ) {
			$this->rollbackTransaction() ;
			$response = array(array('Error_Msg' => $e->getMessage()));
		}

		return $response;
	}

	/**
	 * Сохранение инфомата для повода
	 */
	public function addElectronicInfomatTreatmentLink($data) {

		$query = "
				declare
					@ElectronicInfomatTreatmentLink_id bigint = :ElectronicInfomatTreatmentLink_id,
					@Error_Code bigint,
					@Error_Message varchar(4000);

				exec p_ElectronicInfomatTreatmentLink_ins
					@ElectronicInfomatTreatmentLink_id = @ElectronicInfomatTreatmentLink_id output,
					@ElectronicInfomat_id = :ElectronicInfomat_id,
					@ElectronicTreatment_id = :ElectronicTreatment_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output;

				select @ElectronicInfomatTreatmentLink_id as ElectronicInfomatTreatmentLink_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
			";

		$response = $this->queryResult($query, $data);
		return $response;
	}

	/**
	 * Сохраняет связь повод-очередь для всех записей
	 */
	private function updateElectronicTreatmentLink($data) {
		$error = array();
		$result = array();

		if ( !empty($data['jsonData']) && $data['ElectronicTreatment_id'] > 0 ) {
			ConvertFromWin1251ToUTF8($data['jsonData']);
			$records = (array) json_decode($data['jsonData']);

			// сохраняем\удаляем все записи из связанного грида по очереди
			foreach ( $records as $record ) {
				if ( count($error) == 0 ) {
					switch ( $record->state ) {
						case 'add':
						case 'edit':
							$response = $this->saveObject('ElectronicTreatmentLink', array(
								'ElectronicTreatmentLink_id' => $record->state == 'add' ? null : $record->ElectronicTreatmentLink_id,
								'ElectronicTreatment_id' => $data['ElectronicTreatment_id'],
								'ElectronicQueueInfo_id' => $record->ElectronicQueueInfo_id,
								'pmUser_id' => $data['pmUser_id']
							));
							break;

						case 'delete':
							$response = $this->deleteElectronicTreatmentLink(array(
								'ElectronicTreatmentLink_id' => $record->ElectronicTreatmentLink_id
							));
							break;
					}

					if ( !empty($response['Error_Msg']) ) {
						$error[] = $response['Error_Msg'];
					}
				}

				if ( count($error) > 0 ) {
					break;
				}
			}
		}

		if ( count($error) > 0 ) {
			$result['success'] = false;
			$result['Error_Msg'] = $error[0];
		}
		else {
			$result['success'] = true;
		}

		return array($result);
	}

	/**
	 * Удаление связи повод-очередь
	 */
	public function deleteElectronicTreatmentLink($data) {
		$query = "
			declare
				@Error_Code int,
				@Error_Message varchar(4000);

			execute dbo.p_ElectronicTreatmentLink_del
				@ElectronicTreatmentLink_id = :ElectronicTreatmentLink_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;

			select @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		return $this->getFirstRowFromQuery($query, $data);
	}

	/**
	 * Подгрузка комбо
	 */
	public function loadElectronicQueueInfoCombo($data) {
		if ( !isSuperAdmin() ) {
			$data['Lpu_id'] = $data['session']['lpu_id'];
		}

		$filter = '';
		$params['Lpu_id'] = $data['Lpu_id'];

		if (!empty($data['LpuBuilding_id'])) {
			$params['LpuBuilding_id'] = $data['LpuBuilding_id'];
			$filter .= " and (eqi.LpuBuilding_id = :LpuBuilding_id or eqi.LpuBuilding_id is null) ";
		}

		$query = "
			select
				eqi.ElectronicQueueInfo_id
				,eqi.ElectronicQueueInfo_Code
				,eqi.ElectronicQueueInfo_Name
				,ms.MedService_Name
				,lb.LpuBuilding_id
				,lb.LpuBuilding_Name
				,ls.LpuSection_Name
				,convert(varchar(10), eqi.ElectronicQueueInfo_begDate, 104) as ElectronicQueueInfo_begDate
				,convert(varchar(10), eqi.ElectronicQueueInfo_endDate, 104) as ElectronicQueueInfo_endDate
			from
				v_ElectronicQueueInfo eqi with (nolock)
				left join v_MedService ms with (nolock) on ms.MedService_id = eqi.MedService_id
				left join v_LpuBuilding lb with (nolock) on lb.LpuBuilding_id = eqi.LpuBuilding_id
				left join v_LpuSection ls with (nolock) on ls.LpuSection_id = eqi.LpuSection_id
			where
				eqi.Lpu_id = :Lpu_id
				{$filter}
			order by
				eqi.ElectronicQueueInfo_begDate desc
		";

		$resp = $this->queryResult($query, $params);

		return $resp;
	}
}