<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnFuncRequest_model - модель для работы с заявками на исследование
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Polka
 * @access			public
 * @copyright		Copyright (c) 2009 Swan Ltd.
 * @version			апрель.2012
 *
 * @property EvnDirection_model $EvnDirection_model
 * @property TimetableMedService_model $TimetableMedService_model
 * @property TimetableResource_model $TimetableResource_model
 * @property Evn_model $Evn_model
 * @property EvnPrescr_model $EvnPrescr_model
 * @property EvnPrescrProc_model $EvnPrescrProc_model
 */
class EvnFuncRequestProc_model extends swModel {
	/**
	 * constructor
	 */
	function __construct() {
		parent::__construct();
	}
	/**
	 * 
	 * @param type $usluga
	 * @param type $data
	 * @return string
	 */
	function getEvnFuncRequestUslugaList($data) {
		if ( !is_array($data) || (empty($data['EvnDirection_id']) && empty($data['EvnUslugaPar_id'])) ) {
			return json_encode(array());
		}

		$queryList = array();

		if ( !empty($data['EvnDirection_id']) ) {
			$queryList[] = "
				select
					U.UslugaComplex_Name,
					EUP.EvnUslugaPar_id,
					EUP.Person_id,
					convert(varchar(10), EUP.EvnUslugaPar_setDate, 104) as EvnUslugaPar_setDate
				from 
					v_EvnUslugaPar EUP with (nolock)
					left join v_UslugaComplex U with (nolock) on EUP.UslugaComplex_id = U.UslugaComplex_id
				where 
					EUP.EvnDirection_id = :EvnDirection_id
				order by EUP.EvnUslugaPar_id desc
			";
		} else if ( !empty($data['EvnUslugaPar_id']) ) {
			$queryList[] = "
				select top 1
					U.UslugaComplex_Name,
					EUP.EvnUslugaPar_id,
					EUP.Person_id,
					convert(varchar(10), EUP.EvnUslugaPar_setDate, 104) as EvnUslugaPar_setDate
				from 
					v_EvnUslugaPar EUP with (nolock)
					left join v_UslugaComplex U with (nolock) on EUP.UslugaComplex_id = U.UslugaComplex_id
				where 
					EUP.EvnUslugaPar_id = :EvnUslugaPar_id
			";
		} else {
			return json_encode(array());
		}

		$query = implode(' union ', $queryList);
		
		$resp = $this->queryResult($query, $data);
		if (is_array($resp)) {
			$response = json_encode($resp);

			if (!empty($response)) {
				// обновляем в бд
				$query = "
					update
						EvnFuncRequest with (rowlock)
					set
						EvnFuncRequest_UslugaCache = :EvnFuncRequest_UslugaCache
					where
						EvnFuncRequest_id = :EvnFuncRequest_id
				";
				// echo getDebugSql($query, $data);
				$this->db->query($query, array(
					'EvnFuncRequest_id' => $data['EvnFuncRequest_id'],
					'EvnFuncRequest_UslugaCache' => $response
				));
			}

			return $response;
		}

		return json_encode(array());
	}
	
	/**
	 * Проверка возможности удаления
	 */
	protected function canBeDeleted($data)
	{
		//удалять можно если новая, т.е. нет проб взятых или исследованных
		$samplesInWork = $this->getFirstResultFromQuery('
			SELECT COUNT(*) FROM
				v_EvnUslugaPar (nolock) eup
				inner join v_EvnFuncRequest efr with (nolock) on efr.EvnFuncRequest_pid = eup.EvnDirection_id
			WHERE
				efr.EvnFuncRequest_id = :EvnFuncRequest_id AND
				EvnUslugaPar_SetDT IS NOT NULL
			',
			array('EvnFuncRequest_id' => $data['EvnFuncRequest_id'])
		);
		
		$result = (0==$samplesInWork);
		return $result;
	}

	/**
	 * Отмена выполнения услуги
	 */
	function cancelEvnUslugaPar($data) {
		$query = "
			select
				eup.EvnUslugaPar_id,
				efr.EvnFuncRequest_id,
				epd.EvnPrescr_id,
				eup.EvnDirection_id
			from
				v_EvnUslugaPar eup (nolock)
				left join v_EvnFuncRequest efr (nolock) on efr.EvnFuncRequest_pid = eup.EvnDirection_id
				outer apply (
					select top 1 EvnPrescr_id
					from v_EvnPrescrDirection (nolock)
					where EvnDirection_id = eup.EvnDirection_id
				) epd
			where
				EvnUslugaPar_id = :EvnUslugaPar_id
		";
		$resp = $this->queryResult($query, array(
			'EvnUslugaPar_id' => $data['EvnUslugaPar_id']
		));

		if (is_array($resp) && !empty($resp[0]['EvnUslugaPar_id'])) {
			$this->beginTransaction();
			// отменяем выполнение
			$query = "
				update
					Evn with (rowlock)
				set
					Evn_setDT = null
				where
					Evn_id = :EvnUslugaPar_id;

				update
					EvnUsluga with (rowlock)
				set
					MedPersonal_id = null,
					UslugaPlace_id = null,
					MedStaffFact_id = null,
					LpuSection_uid = null
				where
					EvnUsluga_id = :EvnUslugaPar_id;
			";
			$this->db->query($query, array(
				'EvnUslugaPar_id' => $resp[0]['EvnUslugaPar_id']
			));
			// рекэшируем
			$this->ReCacheFuncRequestStatus(array(
				'EvnFuncRequest_id' => $resp[0]['EvnFuncRequest_id'],
				'EvnDirection_id' => $resp[0]['EvnDirection_id'],
				'EvnUslugaPar_id' => $resp[0]['EvnUslugaPar_id'],
				'pmUser_id' => $data['pmUser_id']
			));

			// Отменяем выполнение назначения
			if (!empty($resp[0]['EvnPrescr_id'])) {
				$this->load->model('EvnPrescr_model', 'EvnPrescr_model');
				$tmp = $this->EvnPrescr_model->rollbackEvnPrescrExecution(array(
					'EvnPrescr_id' => $resp[0]['EvnPrescr_id'],
					'pmUser_id' => $data['pmUser_id'],
				));
				if (!empty($tmp[0]['Error_Msg'])) {
					$this->rollbackTransaction();
					return array('Error_Msg' => $tmp[0]['Error_Msg']);
				}
			}
			$this->commitTransaction();
		}

		return array('Error_Msg' => '');
	}
	
	/**
	 * Отмена направления
	 */
	function cancelDirection($data)
	{
		$this->beginTransaction();
		$directionData = array();
		
		// 1. получение данных направления
		$query = "
			select top 1
				d.pmUser_insID,
				d.TimetableMedService_id,
				efr.EvnFuncRequest_id,
				d.EvnDirection_Num,
				ms.MedService_Name,
				(select top 1 DirFailType_Name from v_DirFailType (nolock) where DirFailType_id = :DirFailType_id) as DirFailType_Name,
				ISNULL(PS.Person_SurName, '') + ' ' + ISNULL(PS.Person_FirName, '') + ' ' + ISNULL(PS.Person_SecName, '') as Person_Fio,
				l.Lpu_Nick,
				ls.LpuSection_Name
			from
				v_EvnDirection_all d (nolock)
				left join v_EvnFuncRequest efr (nolock) on efr.EvnFuncRequest_pid = d.EvnDirection_id
				left join v_PersonState ps (nolock) on ps.Person_id = d.Person_id
				left join v_MedService ms (nolock) on ms.MedService_id = d.MedService_id
				left join v_Lpu l (nolock) on l.Lpu_id = ms.Lpu_id
				left join v_LpuSection ls (nolock) on ls.LpuSection_id = ms.LpuSection_id
			where
				d.EvnDirection_id = :EvnDirection_id
		";
		
		$result = $this->db->query($query, $data);
		
		if ( is_object($result) ) {
			$directionData = $result->result('array');
		}
		
		if (count($directionData) == 0) {
			$this->rollbackTransaction();
			return array(array('Error_Msg' => 'Ошибка получения данных по направлению'));
		}
		
		// 2. удаляем заявку
		if (!empty($directionData[0]['EvnFuncRequest_id'])) {
			if ($this->canBeDeleted(array(
				'EvnFuncRequest_id' => $directionData[0]['EvnFuncRequest_id'],
				'pmUser_id' => $data['pmUser_id']
			))) {
				// удаляем все услуги по заявке
				$this->delEvnUslugaParByEvnFuncRequest(array(
					'EvnFuncRequest_id' => $directionData[0]['EvnFuncRequest_id'],
					'pmUser_id' => $data['pmUser_id']
				));
				// удаляем заявку
				$params = array(
					'EvnFuncRequest_id' => $directionData[0]['EvnFuncRequest_id'],
					'pmUser_id' => $data['pmUser_id']
				);
				$result = $this->deleteEvnFuncRequest($params);
				if (!empty($result['Error_Msg'])) {
					$this->rollbackTransaction();
					return $result;
				}
			} else {
				$this->rollbackTransaction();
				return array(
					0 => array(
						'Error_Msg' => 'Нельзя удалить данную заявку, т.к. она обработана'
					)
				);
			}
		}
		if (!empty($data['EvnDirection_id'])) {
			$sql = "select ed.TimetableMedService_id as TimetableMedService_id,
				ed.EvnQueue_id as EvnQueue_id
					from v_EvnDirection_all ed with (nolock)
					where ed.EvnDirection_id = ?";
			$res = $this->db->query($sql, array($data['EvnDirection_id']));
			if (is_object($res)) {
				$tmp = $res->result('array');
			}
			if(count($tmp)>0){
				$data['TimetableMedService_id'] = $tmp[0]['TimetableMedService_id'];
				$data['EvnQueue_id'] = $tmp[0]['EvnQueue_id'];
			}
			switch (true) {
				case (!empty($data['TimetableMedService_id'])):
					$this->load->model('TimetableGraf_model', 'TimetableGraf_model');
					$data['object'] = 'TimetableMedService';
					$tmp = $this->TimetableGraf_model->Clear($data);
					if ( !$tmp['success'] ) {
						throw new Exception($tmp['Error_Msg'], 500);
					}
					break;
				case (!empty($data['EvnQueue_id'])):
					$this->load->model('Queue_model', 'MPQueue_model');
					$tmp = $this->MPQueue_model->deleteQueueRecord($data);
					if ( !$tmp ) {
						throw new Exception('Ошибка при удалении из очереди', 500);
					}
					if(isset($tmp[0]['Error_Msg'])) {
						throw new Exception($tmp[0]['Error_Msg'], 500);
					}
					break;
				default:
					// throw new Exception('Направление не может быть отменено!', 500);
					// для заявки не обязательно есть запись на бирку или в очередь, отменяться направление всё равно должно
					break;
			}
		}
		// 3. отмена направления
		$query = "
			declare
				@datetime datetime = dbo.tzGetDate(),
				@ErrCode int,
				@Err_Msg varchar(400),
				@EvnComment_id bigint;

			set nocount on;

			begin try
				begin tran

				update EvnDirection
				set
					DirFailType_id = :DirFailType_id,
					EvnDirection_failDT = dbo.tzGetDate(),
					pmUser_failID = :pmUser_id,
					TimetableGraf_id = null,
					TimetableStac_id = null,
					TimetableMedService_id = null,
					TimetablePar_id = null
				where EvnDirection_id = :EvnDirection_id

				set @EvnComment_id = (select top 1 EvnComment_id from v_EvnComment with (nolock) where Evn_id = :EvnDirection_id);

				if ( @EvnComment_id is not null )
					begin
						update dbo.EvnComment
						set EvnComment_Comment = :EvnComment_Comment,
							pmUser_updID = :pmUser_id,
							EvnComment_updDT = @datetime
						where EvnComment_id = @EvnComment_id
					end
				else
					begin
						insert into dbo.EvnComment with (ROWLOCK) (Evn_id, EvnComment_Comment, pmUser_insID, pmUser_updID, EvnComment_insDT, EvnComment_updDT)
						values (:EvnDirection_id, :EvnComment_Comment, :pmUser_id, :pmUser_id, @datetime, @datetime)
					end

				if ( :TimetableMedService_id is not null )
					begin
						exec p_TimetableMedService_cancel
							@TimetableMedService_id = :TimetableMedService_id,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @Err_Msg output;
					end
					
				exec p_EvnDirection_del
					@EvnDirection_id = :EvnDirection_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @Err_Msg output;
				select @ErrCode as Error_Code, @Err_Msg as Error_Msg;

				commit tran
			end try
			
			begin catch
				set @Err_Msg = error_message();
				rollback tran
			end catch

			set nocount off;

			select @Err_Msg as Error_Msg;
		";
		
		if ( strlen($data['EvnComment_Comment']) > 2048 ) {
			$data['EvnComment_Comment'] = substr($data['EvnComment_Comment'], 0, 2048);
		}
		
		$params = array(
			'pmUser_id' => $data['pmUser_id'],
			'TimetableMedService_id' => $directionData[0]['TimetableMedService_id'],
			'DirFailType_id' => $data['DirFailType_id'],
			'EvnComment_Comment' => $data['EvnComment_Comment'],
			'EvnDirection_id' => $data['EvnDirection_id']
		);

		$result = $this->db->query($query, $params);
		
		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (empty($resp[0]['Error_Msg'])) {
				$noticeData = array(
					'autotype' => 1
					,'User_rid' => $directionData[0]['pmUser_insID']
					,'pmUser_id' => $data['pmUser_id']
					,'type' => 1
					,'title' => 'Отмена направления'
					,'text' => 'Направление №' .$directionData[0]['EvnDirection_Num']. ' (' .$directionData[0]['Person_Fio']. ') в лабораторию ' .$directionData[0]['MedService_Name']. ' ('.$directionData[0]['Lpu_Nick'].', '.$directionData[0]['LpuSection_Name'].') отменено по причине '. $directionData[0]['DirFailType_Name'] . '. ' . $data['EvnComment_Comment']
				);
				$this->load->model('Messages_model', 'Messages_model');
				$noticeResponse = $this->Messages_model->autoMessage($noticeData);
			}

			$this->commitTransaction();
			return $resp;
		}

		$this->rollbackTransaction();
		return false;
	}

	/**
	 * Удаление услуг по заявке
	 */
	function delEvnUslugaParByEvnFuncRequest($data) {
		// получаем услуги
		$query = "
			select
				EUP.EvnUslugaPar_id
			from
				v_EvnUslugaPar EUP with (nolock)
				inner join v_EvnFuncRequest EFR with (nolock) on EFR.EvnFuncRequest_pid = EUP.EvnDirection_id
			where
				EFR.EvnFuncRequest_id = :EvnFuncRequest_id

			union all

			select
				EUP.EvnUslugaPar_id
			from
				v_EvnUslugaPar EUP with (nolock)
				inner join v_EvnFuncRequest EFR with (nolock) on EFR.EvnFuncRequest_pid = EUP.EvnUslugaPar_id
			where
				EFR.EvnFuncRequest_id = :EvnFuncRequest_id
		";

		$result = $this->db->query($query, array(
			'EvnFuncRequest_id' => $data['EvnFuncRequest_id']
		));

		if (is_object($result)) {
			$resp = $result->result('array');
			foreach($resp as $respone) {
				// удаляем
				$query = "
					declare
						@ErrCode int,
						@ErrMessage varchar(4000);
					exec p_EvnUslugaPar_del
						@EvnUslugaPar_id = :EvnUslugaPar_id,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;

					select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";
				$this->db->query($query, array(
					'EvnUslugaPar_id' => $respone['EvnUslugaPar_id'],
					'pmUser_id' => $data['pmUser_id']
				));
			}
		}

		return true;
	}

	/**
	 * Удаление заявки
	 */
	function deleteEvnFuncRequest($data) {
		$query = "
			declare
				@Error_Code bigint, @Error_Message varchar(4000);
			exec p_EvnFuncRequest_del
			   @EvnFuncRequest_id = :EvnFuncRequest_id,
			   @pmUser_id = :pmUser_id,
			   @Error_Code      = @Error_Code      output, -- int
			   @Error_Message   = @Error_Message   output  -- varchar(4000)
		   select @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";
		$resp = $this->db->query($query, $data);
		if (is_object($resp)) {
			return $resp->result('array');
		}

		return array('Error_Msg' => 'Ошибка удаления заявки');
	}
	
	/**
	 * @return array
	 * @throws Exception
	 */
	function delete($data)
	{
		if ($this->canBeDeleted($data)) {
			// удаляем все услуги по заявке
			$this->delEvnUslugaParByEvnFuncRequest(array(
				'EvnFuncRequest_id' => $data['EvnFuncRequest_id'],
				'pmUser_id' => $data['pmUser_id']
			));

			return $this->deleteEvnFuncRequest($data);
		} else {
			$result = array(
				0 => array(
					'Error_Code' => null,
					'Error_Msg' => 'Нельзя удалить данную заявку, т.к. она обработана',
					'Failure' => 2
				)
			);
		}

		return false;
	}
	
	
	/**
	 * @desc Получение списка услуг с прикрепленными исследованиями в формате Dicom
	 * @param array $data
	 * @return boolean
	 */
	function getEvnFuncRequestWithAssociatedResearches($data) {
		if ( !array_key_exists( 'Person_id', $data ) || !$data['Person_id'] ) {
			return array( array( 'Error_Msg' => 'Не указан идентификатор пациента.' ) );
		}
		
		$query = '
			SELECT
				EUP.EvnUslugaPar_id,
				convert(varchar(10),ED.EvnDirection_setDT,104) as EvnDirection_setDT
				,Usluga.UslugaComplex_Name
				,EUPAR.EvnUslugaParAssociatedResearches_id
			FROM
				v_EvnUslugaPar EUP with (nolock)
				left join v_EvnDirection_all ED with (nolock) on ED.EvnDirection_id = EUP.EvnDirection_id
				outer apply (
					select top 1
						EUPARO.EvnUslugaParAssociatedResearches_id
					from
						v_EvnUslugaParAssociatedResearches as EUPARO with (nolock)
					where
						EUPARO.EvnUslugaPar_id =  EUP.EvnUslugaPar_id
				) as EUPAR
				outer apply (
					select top 1
						U.UslugaComplex_Name,
						EUPO.EvnUslugaPar_id
					from
						v_EvnUslugaPar EUPO with (nolock)
						left join v_UslugaComplex U with (nolock) on EUPO.UslugaComplex_id = U.UslugaComplex_id
					where 
						EUPO.EvnUslugaPar_id = EUP.EvnUslugaPar_id
				) as Usluga
			where
				EUP.Person_id = :Person_id AND
				ISNULL(EUPAR.EvnUslugaParAssociatedResearches_id,0) != 0
			';
		
		$res = $this->db->query($query, array('Person_id' => $data['Person_id']));
	
		if ( is_object($res) ) {			
			return $res->result('array');
		} else {
			return false;
		}

	}

	/**
	 * Кэширование статуса заявки
	 */
	function ReCacheFuncRequestStatus($data) {
		if (!empty($data['EvnDirection_id'])) {
			$data['EvnFuncRequest_id'] = $this->getFirstResultFromQuery('SELECT top 1 EvnFuncRequest_id FROM v_EvnFuncRequest (nolock) WHERE EvnFuncRequest_pid = :EvnDirection_id',array('EvnDirection_id' => $data['EvnDirection_id']));

			// рекэшируем
			$data['EvnFuncRequest_UslugaCache'] = $this->getEvnFuncRequestUslugaList($data);
		} else if (!empty($data['EvnUslugaPar_id'])) {
			$data['EvnFuncRequest_id'] = $this->getFirstResultFromQuery('SELECT top 1 EvnFuncRequest_id FROM v_EvnFuncRequest (nolock) WHERE EvnFuncRequest_pid = :EvnUslugaPar_id',array('EvnUslugaPar_id' => $data['EvnUslugaPar_id']));

			// рекэшируем
			$data['EvnFuncRequest_UslugaCache'] = $this->getEvnFuncRequestUslugaList($data);
		}

		if (empty($data['EvnFuncRequest_id'])) {
			return false;
		}

		$query = "
			select top 1
				es.EvnStatus_SysNick,
				ed.EvnDirection_id,
				TTMS.TimeTableMedService_id,
				eupar.EvnUslugaPar_id
			from
				v_EvnFuncRequest efr (nolock)
				inner join v_EvnDirection_all ed (nolock) on ed.EvnDirection_id = efr.EvnFuncRequest_pid
				left join v_TimetableMedService_lite TTMS (nolock) on ED.EvnDirection_id = TTMS.EvnDirection_id
				outer apply(
					select top 1
						eup.EvnUslugaPar_id
					from
						v_EvnUslugaPar eup (nolock)
					where
						eup.EvnDirection_id = ed.EvnDirection_id
						and eup.EvnUslugaPar_SetDT is not null
				) EUPAR
				left join v_EvnStatus es (nolock) on es.EvnStatus_id = efr.EvnStatus_id
			where
				efr.EvnFuncRequest_id = :EvnFuncRequest_id
		";
		
		$result = $this->db->query($query, array(
			'EvnFuncRequest_id' => $data['EvnFuncRequest_id']
		));

		$EvnStatus_SysNick = 'FuncNew';
		
		if (is_object($result)) {
			$resp = $result->result('array');
			if (!empty($resp[0])) {
				if (!empty($resp[0]['EvnUslugaPar_id'])) {
					$EvnStatus_SysNick = 'FuncDonerec';
				}

				if ($EvnStatus_SysNick == 'FuncDonerec') {
					// если приём осуществлён, то принимаем из очереди
					if (empty($resp[0]['TimeTableMedService_id']) && !empty($resp[0]['EvnDirection_id'])) {
						$this->load->model('TimetableMedService_model','TimetableMedService_model');
						// принимаем человека из очереди
						$this->TimetableMedService_model->acceptWithoutRecord(array(
							'EvnDirection_id' => $resp[0]['EvnDirection_id'],
							'pmUser_id' => $data['pmUser_id']
						));
					}
				}

				if ($EvnStatus_SysNick != $resp[0]['EvnStatus_SysNick']) {
					$this->load->model('Evn_model', 'Evn_model');
					$this->Evn_model->updateEvnStatus(array(
						'Evn_id' => $data['EvnFuncRequest_id'],
						'EvnStatus_SysNick' => $EvnStatus_SysNick,
						'EvnClass_SysNick' => 'EvnFuncRequest',
						'pmUser_id' => $data['pmUser_id']
					));
					if(!empty($resp[0]['EvnDirection_id'])) {
						$EDEvnStatus_SysNick = 'Serviced';
						if ($EvnStatus_SysNick == 'FuncNew') {
							if (!empty($resp[0]['TimeTableMedService_id'])) {
								$EDEvnStatus_SysNick = 'DirZap';
							} else {
								$EDEvnStatus_SysNick = 'Queued';
							}
						}
						$this->Evn_model->updateEvnStatus(array(
							'Evn_id' => $resp[0]['EvnDirection_id'],
							'EvnStatus_SysNick' => $EDEvnStatus_SysNick,
							'EvnClass_SysNick' => 'EvnDirection',
							'pmUser_id' => $data['pmUser_id']
						));
					}
				}
			}
		}
		
		return false;
	}

	/**
	 * Проверка наличия расписания на услугу
	 * @return bool
	 */
	function checkUslugaComplexMedServiceTimeTable($data) {
		$response = array(
			'Error_Msg' => '',
			'UslugaComplexMedService_id' => null
		);

		$this->load->helper('Reg');
		$data['begDay_id'] = TimeToDay(strtotime($data['begDate']));
		$data['endDay_id'] = TimeToDay(strtotime($data['endDate']));

		$query = "
			select top 1
				ucms.UslugaComplexMedService_id
			from
				v_UslugaComplexMedService ucms (nolock)
				inner join v_EvnUslugaPar eup (nolock) on eup.UslugaComplex_id = ucms.UslugaComplex_id
				inner join v_TimetableMedService_lite ttms (nolock) on ttms.UslugaComplexMedService_id = ucms.UslugaComplexMedService_id
				inner join v_MedService MS with (nolock) ON MS.MedService_id = UCMS.MedService_id
			where
				UCMS.MedService_id = :MedService_id and (TTMS.TimetableMedService_Day is not null and TTMS.TimetableMedService_Day between :begDay_id and :endDay_id) and EUP.EvnDirection_id = :EvnDirection_id
		";

		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			$resp = $result->result('array');
			if (!empty($resp[0]['UslugaComplexMedService_id'])) {
				$response['UslugaComplexMedService_id'] = $resp[0]['UslugaComplexMedService_id'];
			}
		}

		return $response;
	}

	/**
	 *
	 * @param type $data
	 * @return type 
	 */
	function loadEvnFuncRequestList($data) {
		$commonFilters = array();
		$EFR_EQ_Filters = array();
		$EFR_TTMS_Filters = array();
		$EFRFilters = array();
		$queryParams = array();
		$TTMSFilters = array();
		
		$TTMSFilters[] = "(ISNULL(TTMS.MedService_id, UCMS.MedService_id) = :MedService_id)"; // Направление в эту определенную службу

		$queryParams['Lpu_id'] = $data['Lpu_id'];
		$queryParams['MedService_id'] = $data['MedService_id'];
		
		if ( !empty($data['Search_SurName']) ) {
			$commonFilters[] = "PS.Person_SurName LIKE (:Search_SurName+'%')";
			$queryParams['Search_SurName'] = rtrim($data['Search_SurName']);
		}
		
		if ( !empty($data['Search_FirName']) ) {
			$commonFilters[] = "PS.Person_FirName LIKE (:Search_FirName+'%')";
			$queryParams['Search_FirName'] = rtrim($data['Search_FirName']);
		}
		
		if ( !empty($data['Search_SecName']) ) {
			$commonFilters[] = "PS.Person_SecName LIKE (:Search_SecName+'%')";
			$queryParams['Search_SecName'] = rtrim($data['Search_SecName']);
		}
		
		if ( !empty($data['Search_BirthDay']) ) {
			$commonFilters[] = "PS.Person_BirthDay = :Search_BirthDay";
			$queryParams['Search_BirthDay'] = $data['Search_BirthDay'];
		}

		if ( !empty($data['EvnDirection_Num']) ) {
			$commonFilters[] = "ED.EvnDirection_Num = :EvnDirection_Num";
			$queryParams['EvnDirection_Num'] = $data['EvnDirection_Num'];
		}

		// Фильтр по услугам
		if ( !empty($data['UslugaComplex_id']) ) {
			$commonFilters[] = "exists (
				select top 1
					t1.UslugaComplex_id
				from 
					EvnUsluga t1 with (nolock)
					inner join Evn (nolock) on t1.EvnUsluga_id = Evn.Evn_id and Evn.Evn_deleted = 1 and EvnClass_id = 47
				where 
					t1.EvnDirection_id = ED.EvnDirection_id
					and t1.UslugaComplex_id = :UslugaComplex_id
			)";
			$queryParams['UslugaComplex_id'] = $data['UslugaComplex_id'];
		}
		
		if ( !empty($data['EvnDirection_IsCito']) ) {
			$commonFilters[] = "ISNULL(ED.EvnDirection_IsCito, 1) = :EvnDirection_IsCito";
			$queryParams['EvnDirection_IsCito'] = $data['EvnDirection_IsCito'];
		}

		if ( !empty($data['begDate']) && !empty($data['endDate']) ) {
			//записанные отображаются в тот день, на который они записаны #11499
			//те, кто в очереди отображаются в тот день, когда они направлены
			$TTMSFilters[] = "(
				(TTMS.TimetableMedService_Day is not null and TTMS.TimetableMedService_Day between :begDay_id and :endDay_id)
			)";
			$EFR_EQ_Filters[] = "(
				(ES.EvnStatus_SysNick <> 'FuncDonerec')
				OR
				(ES.EvnStatus_SysNick = 'FuncDonerec' and CAST(efr.EvnFuncRequest_statusDate as date) between :begDate and :endDate)
			)";
			$EFR_EQ_Filters[] = "(
				TTMS.TimetableMedService_begTime is null
			)";
			$EFR_TTMS_Filters[] = "(
				(TTMS.TimetableMedService_Day between :begDay_id and :endDay_id)
			)";
			$EFRFilters[] = "(CAST(isnull(ed.EvnDirection_setDT, EFR.EvnFuncRequest_setDT) as date) between :begDate and :endDate)";
			$this->load->helper('Reg');
			$queryParams['begDay_id'] = TimeToDay(strtotime($data['begDate']));
			$queryParams['endDay_id'] = TimeToDay(strtotime($data['endDate']));
			$queryParams['begDate'] = $data['begDate'];
			$queryParams['endDate'] = $data['endDate'];
		}
		
		$queryParams['MedService_lid'] = (!empty($data['session']['CurMedService_id']))?$data['session']['CurMedService_id']:null;
		$queryParams['MedServiceLinkType_Code'] = '3';
		
		
		$EFRFilters = array_merge($EFRFilters, $commonFilters);
		$EFR_EQ_Filters = array_merge($EFR_EQ_Filters, $commonFilters);
		$EFR_TTMS_Filters = array_merge($EFR_TTMS_Filters, $commonFilters);

		$querys = array();

		// 1. запрос по биркам (все пустые без направлений)
		$querys[0] = "
			select
				TTMS.TimetableMedService_id,
				null as EvnDirection_id,
				null as EvnQueue_id,
                null as Person_Phone,
				null as EvnFuncRequest_id,
				convert(varchar(10),TTMS.TimetableMedService_begTime,104) as group_name,
				case when TTMS.TimetableMedService_begTime is not null
					then convert(varchar,cast(TTMS.TimetableMedService_begTime as datetime),104)
					else null
				end as TimetableMedService_begDate,
				isnull(convert(varchar(5), TTMS.TimetableMedService_begTime, 108),'б/з') as TimetableMedService_begTime,
				convert(varchar(10),TTMS.TimetableMedService_begTime,104) as TimetableMedService_Type,
				'false' as EvnDirection_IsCito,
				null as EvnDirection_setDT,
				null as EvnDirection_Num,	
				null as Person_id,
				null as EvnUslugaPar_id,
				MSL.MedService_id as RCC_MedService_id,
				null as RemoteConsultCenterResearch_id,
				null as RemoteConsultCenterResearch_status,
				null as Person_FIO,
				null as Person_BirthDay,
				'false' as FuncRequestState,
				'' as Operator,
				case when UCMS.UslugaComplexMedService_id IS NULL then 'Общее' else UC.UslugaComplex_Name end as TimetableMedServiceType,
				'' as EvnFuncRequest_UslugaCache,
				'' as Lpu_Name,
				'' as LpuSection_Name,
				'' as EvnCostPrint_PrintStatus,
				null as PersonQuarantine_begDT,
				'' as PersonQuarantine_IsOn
			FROM v_TimetableMedService_lite TTMS with (nolock)
				LEFT JOIN v_UslugaComplexMedService UCMS (nolock) on UCMS.UslugaComplexMedService_id = TTMS.UslugaComplexMedService_id
				left join v_UslugaComplex UC (nolock) on UC.UslugaComplex_id = UCMS.UslugaComplex_id
				LEFT JOIN v_MedService MS with (nolock) ON MS.MedService_id = ISNULL(TTMS.MedService_id, UCMS.MedService_id)
				LEFT JOIN v_MedServiceLink MSL with (nolock) on MSL.MedService_lid = :MedService_lid AND MSL.MedServiceLinkType_id = (select top 1 MSLT.MedServiceLinkType_id from v_MedServiceLinkType MSLT (nolock) where MSLT.MedServiceLinkType_Code = :MedServiceLinkType_Code)
				--LEFT JOIN v_pmUserCache PUC with(nolock) on PUC.PMUser_id = TTMS.pmUser_insID
			WHERE 
				" . implode(' and ', $TTMSFilters) . "
				and TTMS.EvnDirection_id is null
				
		";

		// 2a. запрос по заявкам (все из EvnFuncRequest) не записанные (из очереди и остальные)
		$querys[0] .= "
			union all
			select
				ttms.TimetableMedService_id,
				ED.EvnDirection_id,
				ED.EvnQueue_id as EvnQueue_id,
				[dbo].[getPersonPhones](ed.Person_id, '<br />') as Person_Phone,
				EFR.EvnFuncRequest_id,
				null as group_name,
				case
					when ES.EvnStatus_SysNick = 'FuncDonerec' then convert(varchar,cast(efr.EvnFuncRequest_statusDate as datetime),104)
					else null
				end as TimetableMedService_begDate,
				'б/з' as TimetableMedService_begTime,
				null as TimetableMedService_Type,
				case when 2 = ISNULL(ED.EvnDirection_IsCito, 1) then 'true' else 'false' end as EvnDirection_IsCito,
				convert(varchar(10), ED.EvnDirection_setDT, 104) as EvnDirection_setDT,
				ED.EvnDirection_Num,	
				ED.Person_id,
				EvnUslugaPar.EvnUslugaPar_id,
				MSL.MedService_id as RCC_MedService_id,
				RCCR.RemoteConsultCenterResearch_id,
				RCCR.RemoteConsultCenterResearch_status,
				(PS.Person_SurName + ' ' + IsNull(PS.Person_FirName, '') + ' ' + IsNull(PS.Person_SecName,'')) as Person_FIO,
				convert(varchar(10), PS.Person_BirthDay, 104) as Person_BirthDay,
				case when ES.EvnStatus_SysNick = 'FuncDonerec' and EPP.EvnPrescrProc_didDT is not null then 'true' else 'false' end as FuncRequestState,
				case
					when PUC.PMUser_surName is null then ''
					else PUC.PMUser_surName+' '+isnull(left(PUC.PMUser_firName,1),'')+' '+isnull(left(PUC.PMUser_secName,1),'')
				end as Operator,
				'Общее' as TimetableMedServiceType,
				efr.EvnFuncRequest_UslugaCache,
				LpuFrom.Lpu_Nick as Lpu_Name,
				LpuSectionFrom.LpuSection_Name as LpuSection_Name,
				--'' as EvnCostPrint_PrintStatus,
				case when NoPrintCount.NPC > 0 then 'true' else 'false' end as EvnCostPrint_PrintStatus,
				convert(varchar(10), PQ.PersonQuarantine_begDT, 104) as PersonQuarantine_begDT,
				CASE WHEN PQ.PersonQuarantine_id is not null THEN 'true' ELSE 'false' END as PersonQuarantine_IsOn
			FROM v_EvnFuncRequest efr (nolock)
				inner join v_EvnDirection_all ed (nolock) on ed.EvnDirection_id = efr.EvnFuncRequest_pid and ed.EvnDirection_failDT is null
				inner join v_EvnQueue eq (nolock) on eq.EvnDirection_id = ed.EvnDirection_id and eq.EvnQueue_failDT is null and eq.EvnQueue_recDT is null
				left join v_TimetableMedService_lite ttms (nolock) on ttms.EvnDirection_id = ed.EvnDirection_id
				left join v_Lpu LpuFrom with (nolock) on LpuFrom.Lpu_id = ED.Lpu_sid
				left join v_LpuSection LpuSectionFrom with (nolock) on LpuSectionFrom.LpuSection_id = ED.LpuSection_id
				left join v_PersonState PS (nolock) on PS.Person_id = ED.Person_id
				outer apply (
					select top 1 
						PQ.PersonQuarantine_id,
						PQ.PersonQuarantine_begDT
					from v_PersonQuarantine PQ with(nolock)
					where PQ.Person_id = PS.Person_id 
					and PQ.PersonQuarantine_endDT is null
				) PQ
				--left join v_EvnCostPrint ECP (nolock) on ECP.Evn_id = efr.EvnFuncRequest_id
				outer apply (
					select top 1 ECP.EvnCostPrint_id as NPC
					from v_EvnUslugaPar EUP with(nolock)
					left join v_EvnCostPrint ECP on ECP.Evn_id = EUP.EvnUslugaPar_id
					where EUP.EvnDirection_id = efr.EvnFuncRequest_pid
					and ISNULL(ECP.EvnCostPrint_IsNoPrint,0) <> 2
				) NoPrintCount
				left join v_EvnStatus ES (nolock) on ES.EvnStatus_id = EFR.EvnStatus_id
				LEFT JOIN v_EvnPrescrDirection EPD with (nolock) on EPD.EvnDirection_id = ED.EvnDirection_id
				LEFT JOIN v_EvnPrescrProc EPP with (nolock) on EPP.EvnPrescrProc_id = EPD.EvnPrescr_id
				LEFT JOIN v_EvnPrescrFuncDiag EPFD with (nolock) on EPFD.EvnPrescrFuncDiag_id = EPD.EvnPrescr_id
				outer apply(
					select top 1 EvnUslugaPar_id from v_EvnUslugaPar with (nolock) where EvnDirection_id = ED.EvnDirection_id
				) EvnUslugaPar
				LEFT JOIN v_RemoteConsultCenterResearch RCCR with (nolock) on RCCR.EvnUslugaPar_id = EvnUslugaPar.EvnUslugaPar_id
				LEFT JOIN v_MedServiceLink MSL with (nolock) on MSL.MedService_lid = :MedService_lid AND MSL.MedServiceLinkType_id = (select top 1 MSLT.MedServiceLinkType_id from v_MedServiceLinkType MSLT (nolock) where MSLT.MedServiceLinkType_Code = :MedServiceLinkType_Code)
				LEFT JOIN v_pmUserCache PUC with(nolock) on PUC.PMUser_id = ed.pmUser_insID
			WHERE 
				efr.MedService_id = :MedService_id
				" . (count($EFR_EQ_Filters) > 0 ? "and " . implode(' and ', $EFR_EQ_Filters) : "") . "
		";

		// 2b. запрос по заявкам (все из EvnFuncRequest) только записанные
		$querys[0] .= "
			union all
			select
				ttms.TimetableMedService_id as TimetableMedService_id,
				ED.EvnDirection_id,
				ED.EvnQueue_id as EvnQueue_id,
				[dbo].[getPersonPhones](ed.Person_id, '<br />') as Person_Phone,
				EFR.EvnFuncRequest_id,
				convert(varchar(10),TTMS.TimetableMedService_begTime,104) as group_name,
				case
					when TTMS.TimetableMedService_begTime is not null then convert(varchar,cast(TTMS.TimetableMedService_begTime as datetime),104)
					when ES.EvnStatus_SysNick = 'FuncDonerec' then convert(varchar,cast(efr.EvnFuncRequest_statusDate as datetime),104)
					else null
				end as TimetableMedService_begDate,
				isnull(convert(varchar(5), TTMS.TimetableMedService_begTime, 108),'б/з') as TimetableMedService_begTime,
				convert(varchar(10),TTMS.TimetableMedService_begTime,104) as TimetableMedService_Type,
				case when 2 = ISNULL(ED.EvnDirection_IsCito, 1) or isnull(EPP.EvnPrescrProc_IsCito, 1) = 2 then 'true' else 'false' end as EvnDirection_IsCito,
				convert(varchar(10), ED.EvnDirection_setDT, 104) as EvnDirection_setDT,
				ED.EvnDirection_Num,
				ED.Person_id,
				EvnUslugaPar.EvnUslugaPar_id,
				MSL.MedService_id as RCC_MedService_id,
				RCCR.RemoteConsultCenterResearch_id,
				RCCR.RemoteConsultCenterResearch_status,
				(PS.Person_SurName + ' ' + IsNull(PS.Person_FirName, '') + ' ' + IsNull(PS.Person_SecName,'')) as Person_FIO,
				convert(varchar(10), PS.Person_BirthDay, 104) as Person_BirthDay,
				case when ES.EvnStatus_SysNick = 'FuncDonerec' then 'true' else 'false' end as FuncRequestState,
				case
					when PUC.PMUser_surName is null then ''
					else PUC.PMUser_surName+' '+isnull(left(PUC.PMUser_firName,1),'')+' '+isnull(left(PUC.PMUser_secName,1),'')
				end as Operator,
				case when UCMS.UslugaComplexMedService_id IS NULL then 'Общее' else UC.UslugaComplex_Name end as TimetableMedServiceType,
				efr.EvnFuncRequest_UslugaCache,
				LpuFrom.Lpu_Nick as Lpu_Name,
				LpuSectionFrom.LpuSection_Name as LpuSection_Name,
				--'' as EvnCostPrint_PrintStatus,
				case when NoPrintCount.NPC > 0 then 'true' else 'false' end as EvnCostPrint_PrintStatus,
				convert(varchar(10), PQ.PersonQuarantine_begDT, 104) as PersonQuarantine_begDT,
				CASE WHEN PQ.PersonQuarantine_id is not null THEN 'true' ELSE 'false' END as PersonQuarantine_IsOn
			FROM v_EvnFuncRequest efr (nolock)
				inner join v_EvnDirection_all ed (nolock) on ed.EvnDirection_id = efr.EvnFuncRequest_pid and ed.EvnDirection_failDT is null
				inner join v_TimetableMedService_lite ttms (nolock) on ttms.EvnDirection_id = ed.EvnDirection_id and ttms.TimetableMedService_begTime is not null
				left join v_Lpu LpuFrom with (nolock) on LpuFrom.Lpu_id = ED.Lpu_sid
				left join v_LpuSection LpuSectionFrom with (nolock) on LpuSectionFrom.LpuSection_id = ED.LpuSection_id
				left join v_PersonState PS (nolock) on PS.Person_id = ED.Person_id
				outer apply (
					select top 1 
						PQ.PersonQuarantine_id,
						PQ.PersonQuarantine_begDT
					from v_PersonQuarantine PQ with(nolock)
					where PQ.Person_id = PS.Person_id 
					and PQ.PersonQuarantine_endDT is null
				) PQ
				--left join v_EvnCostPrint ECP (nolock) on ECP.Evn_id = efr.EvnFuncRequest_id
				outer apply (
					select top 1 ECP.EvnCostPrint_id as NPC
					from v_EvnUslugaPar EUP with(nolock)
					left join v_EvnCostPrint ECP on ECP.Evn_id = EUP.EvnUslugaPar_id
					where EUP.EvnDirection_id = efr.EvnFuncRequest_pid
					and ISNULL(ECP.EvnCostPrint_IsNoPrint,0) <> 2
				) NoPrintCount
				LEFT JOIN v_UslugaComplexMedService UCMS (nolock) on UCMS.UslugaComplexMedService_id = TTMS.UslugaComplexMedService_id
				left join v_UslugaComplex UC (nolock) on UC.UslugaComplex_id = UCMS.UslugaComplex_id
				left join v_EvnStatus ES (nolock) on ES.EvnStatus_id = EFR.EvnStatus_id
				LEFT JOIN v_EvnPrescrDirection EPD with (nolock) on EPD.EvnDirection_id = ED.EvnDirection_id
				left join v_EvnPrescrProc EPP (nolock) on EPP.EvnPrescrProc_id = EPD.EvnPrescr_id
				LEFT JOIN v_EvnPrescrFuncDiag EPFD with (nolock) on EPFD.EvnPrescrFuncDiag_id = EPD.EvnPrescr_id
				outer apply(
					select top 1 EvnUslugaPar_id from v_EvnUslugaPar with (nolock) where EvnDirection_id = ED.EvnDirection_id
				) EvnUslugaPar
				LEFT JOIN v_RemoteConsultCenterResearch RCCR with (nolock) on RCCR.EvnUslugaPar_id = EvnUslugaPar.EvnUslugaPar_id
				LEFT JOIN v_MedServiceLink MSL with (nolock) on MSL.MedService_lid = :MedService_lid AND MSL.MedServiceLinkType_id = (select top 1 MSLT.MedServiceLinkType_id from v_MedServiceLinkType MSLT (nolock) where MSLT.MedServiceLinkType_Code = :MedServiceLinkType_Code)
				LEFT JOIN v_pmUserCache PUC with(nolock) on PUC.PMUser_id = ed.pmUser_insID
			WHERE
				efr.MedService_id = :MedService_id
				" . (count($EFR_TTMS_Filters) > 0 ? "and " . implode(' and ', $EFR_TTMS_Filters) : "") . "
		";

		// 3. без записи (без EvnDirection, связь с услугой по EvnFuncRequest_pid
		$querys[0] .= "
			union all
			-- без направления (без EvnDirection, связь с услугой по EvnFuncRequest_pid) по #23048.
			select
				NULL as TimetableMedService_id,
				ED.EvnDirection_id,
				null as EvnQueue_id,
				[dbo].[getPersonPhones](EFR.Person_id, '<br />') as Person_Phone,
				EFR.EvnFuncRequest_id,
				convert(varchar(10),ED.EvnDirection_setDT,104) as group_name,
				case when EvnUslugaPar.EvnUslugaPar_setDT is not null
					then convert(varchar,cast(EvnUslugaPar.EvnUslugaPar_setDT as datetime),104)
					else null
				end as TimetableMedService_begDate,
				'б/н' as TimetableMedService_begTime,
				null as TimetableMedService_Type,
				case when 2 = ISNULL(ED.EvnDirection_IsCito, 1) then 'true' else 'false' end as EvnDirection_IsCito,
				convert(varchar(10), ED.EvnDirection_setDT, 104) as EvnDirection_setDT,
				ED.EvnDirection_Num,
				EFR.Person_id,
				EvnUslugaPar.EvnUslugaPar_id,
				MSL.MedService_id as RCC_MedService_id,
				RCCR.RemoteConsultCenterResearch_id,
				RCCR.RemoteConsultCenterResearch_status,
				(PS.Person_SurName + ' ' + IsNull(PS.Person_FirName, '') + ' ' + IsNull(PS.Person_SecName,'')) as Person_FIO,
				convert(varchar(10), PS.Person_BirthDay, 104) as Person_BirthDay,
				case when EvnUslugaPar.EvnUslugaPar_setDT is not null then 'true' else 'false' end as FuncRequestState,
				case
					when UC.PMUser_surName is null then ''
					else UC.PMUser_surName+' '+isnull(left(UC.PMUser_firName,1),'')+' '+isnull(left(UC.PMUser_secName,1),'')
				end as Operator,
				'' as TimetableMedServiceType,
				efr.EvnFuncRequest_UslugaCache,
				'' as Lpu_Name,
				'' as LpuSection_Name,
				'' as EvnCostPrint_PrintStatus,
				convert(varchar(10), PQ.PersonQuarantine_begDT, 104) as PersonQuarantine_begDT,
				CASE WHEN PQ.PersonQuarantine_id is not null THEN 'true' ELSE 'false' END as PersonQuarantine_IsOn
			FROM v_EvnFuncRequest EFR  with (nolock)
				LEFT JOIN v_EvnStatus ES (nolock) on ES.EvnStatus_id = EFR.EvnStatus_id
				inner join v_EvnUslugaPar EvnUslugaPar with (nolock) on EvnUslugaPar.EvnUslugaPar_id = EFR.EvnFuncRequest_pid
				LEFT JOIN v_EvnDirection_all ED with (nolock) ON (1=0)
				LEFT JOIN v_PersonState PS with (nolock) on PS.Person_id = EFR.Person_id
				outer apply (
					select top 1 
						PQ.PersonQuarantine_id,
						PQ.PersonQuarantine_begDT
					from v_PersonQuarantine PQ with(nolock)
					where PQ.Person_id = PS.Person_id 
					and PQ.PersonQuarantine_endDT is null
				) PQ
				LEFT JOIN v_RemoteConsultCenterResearch RCCR with (nolock) on RCCR.EvnUslugaPar_id = EvnUslugaPar.EvnUslugaPar_id
				LEFT JOIN v_MedServiceLink MSL with (nolock) on MSL.MedService_lid = :MedService_lid AND MSL.MedServiceLinkType_id = (select top 1 MSLT.MedServiceLinkType_id from v_MedServiceLinkType MSLT (nolock) where MSLT.MedServiceLinkType_Code = :MedServiceLinkType_Code)
				LEFT JOIN v_pmUserCache UC with(nolock) on UC.PMUser_id = EFR.pmUser_insID
			WHERE
				isnull(ED.Lpu_did, EFR.Lpu_id) = :Lpu_id
				and isnull(ED.MedService_id, EFR.MedService_id) = :MedService_id
				and ED.TimetableMedService_id is null
				" . (count($EFRFilters) > 0 ? "and " . implode(' and ', $EFRFilters) : "") . "
		order by TimetableMedService_begTime, TimetableMedServiceType
";
		
		$response = array();

		foreach($querys as $query) {
			//echo getDebugSQL($query, $queryParams);
			$res = $this->db->query($query, $queryParams);
			
			if ( is_object($res) ) {
				$resp = $res->result('array');
				foreach($resp as $respone) {
					// на случай если ещё не кэшировалось
					$needRecache = true;
					if (!empty($respone['EvnFuncRequest_UslugaCache'])) {
						$EvnFuncRequest_UslugaCache = json_decode($respone['EvnFuncRequest_UslugaCache'], true);
						if (is_array($EvnFuncRequest_UslugaCache)) {
							if (!empty($EvnFuncRequest_UslugaCache[0]) && is_array($EvnFuncRequest_UslugaCache[0]) && array_key_exists('EvnUslugaPar_setDate', $EvnFuncRequest_UslugaCache[0])) {
								$needRecache = false;
							}
						}
					}
					if ($needRecache) {
						$respone['EvnFuncRequest_UslugaCache'] = $this->getEvnFuncRequestUslugaList($respone);
					}
					$response[] = $respone;
				}
			}
		}

		return $response;
	}

	/**
	 * Получение списка заявок ФД: МАРМ версия
	 */
	function mLoadEvnFuncRequestList($data) {

		$this->load->helper('Reg');
		$filter = ''; $person_filter='';

		$queryParams = array(
			'Lpu_id' => $data['Lpu_id'],
			'MedService_id' => $data['MedService_id'],
			'MedService_lid' => !empty($data['session']['CurMedService_id']) ? $data['session']['CurMedService_id'] : null,
			'MedServiceLinkType_Code' => '3',
			'begDate' => $data['FuncRequest_begDate'],
			'endDate' => $data['FuncRequest_endDate'],
			'begDay_id' =>  TimeToDay(strtotime($data['FuncRequest_begDate'])),
			'endDay_id' =>  TimeToDay(strtotime($data['FuncRequest_endDate']))
		);

		$data['Person_SurName'] = null;
		$isSearchByEncryp = false;

		if (!empty($data['Person_FIO'])) {

			$fullName = explode(' ',trim($data['Person_FIO']));

			if (!empty($fullName[0])) {
				$data['Person_SurName'] = $fullName[0];
			}

			if (!empty($fullName[1])) {
				$data['Person_FirName'] = $fullName[1];
			}

			if (!empty($fullName[2])) {
				$data['Person_SecName'] = $fullName[2];
			}
		}

		if (allowPersonEncrypHIV($data['session'])) {
			$isSearchByEncryp = isSearchByPersonEncrypHIV($data['Person_SurName']);
			$selectPersonData = "
				PS.Sex_id,
				case when peh.PersonEncrypHIV_Encryp is null then PS.Person_Birthday end as Person_Birthday,
				null as Person_Age,
				case when peh.PersonEncrypHIV_Encryp is null then PS.Person_SurName else peh.PersonEncrypHIV_Encryp end as Person_Surname,
				case when peh.PersonEncrypHIV_Encryp is null then PS.Person_FirName end as Person_Firname,
				case when peh.PersonEncrypHIV_Encryp is null then PS.Person_SecName end as Person_Secname,";
		} else {
			$selectPersonData = "PS.Sex_id,
					convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
					dbo.Age2(PS.Person_Birthday, dbo.tzGetDate()) as Person_Age,
					PS.Person_Surname,
					PS.Person_Firname,
					PS.Person_Secname,";
		}

		if (!empty($data['Person_SurName'])) {
			if (allowPersonEncrypHIV($data['session']) && $isSearchByEncryp) {
				$person_filter .= " and peh.PersonEncrypHIV_Encryp like :Person_SurName";
			} else {
				$person_filter .= " and PS.Person_SurName like :Person_SurName + '%'";
			}
			$queryParams['Person_SurName'] = rtrim($data['Person_SurName']);
		}

		if (!empty($data['Person_FirName'])) {
			$person_filter .= " and PS.Person_FirName like :Person_FirName + '%'";
			$queryParams['Person_FirName'] = rtrim($data['Person_FirName']);
		}

		if (!empty($data['Person_SecName'])) {
			$person_filter .= " and PS.Person_SecName like :Person_SecName + '%'";
			$queryParams['Person_SecName'] = rtrim($data['Person_SecName']);
		}

		if (!empty($data['EvnDirection_IsCito'])) {
			$filter .= " and ISNULL(ED.EvnDirection_IsCito, 1) = 2";
		}

		// Фильтр по услугам
		if (!empty($data['UslugaComplex_id'])) {
			$filter .= "
				and exists (
					select top 1
						t1.UslugaComplex_id
					from 
						EvnUsluga t1 with (nolock)
						inner join Evn (nolock) on t1.EvnUsluga_id = Evn.Evn_id and Evn.Evn_deleted = 1 and EvnClass_id = 47
					where 
						t1.EvnDirection_id = ED.EvnDirection_id
						and t1.UslugaComplex_id = :UslugaComplex_id
					)
				";
			$queryParams['UslugaComplex_id'] = $data['UslugaComplex_id'];
		}

		// 2a. запрос по заявкам (все из EvnFuncRequest) не записанные (из очереди и остальные)
		$query = "
			select
				ttms.TimetableMedService_id,
				ED.EvnDirection_id,
				ED.EvnQueue_id as EvnQueue_id,
				EFR.EvnFuncRequest_id,
				null as group_name,
				case
					when ES.EvnStatus_SysNick = 'FuncDonerec' then convert(varchar,cast(efr.EvnFuncRequest_statusDate as datetime),104)
					else null
				end as TimetableMedService_begDate,
				'б/з' as TimetableMedService_begTime,
				null as TimetableMedService_Type,
				case when 2 = ISNULL(ED.EvnDirection_IsCito, 1) then 'true' else 'false' end as EvnDirection_IsCito,
				convert(varchar(10), ED.EvnDirection_setDT, 104) as EvnDirection_setDT,
				ED.EvnDirection_Num,	
				ED.Person_id,
				EvnUslugaPar.EvnUslugaPar_id,
				MSL.MedService_id as RCC_MedService_id,
				RCCR.RemoteConsultCenterResearch_id,
				RCCR.RemoteConsultCenterResearch_status,
				{$selectPersonData}
				convert(varchar(10), PS.Person_BirthDay, 104) as Person_BirthDay,
				case when ES.EvnStatus_SysNick = 'FuncDonerec' and EPP.EvnPrescrProc_didDT is not null then 2 else 1 end as EvnFuncRequest_IsExec,
				case
					when PUC.PMUser_surName is null then ''
					else PUC.PMUser_surName+' '+isnull(left(PUC.PMUser_firName,1),'')+' '+isnull(left(PUC.PMUser_secName,1),'')
				end as Operator,
				'Общее' as TimetableMedServiceType,
				efr.EvnFuncRequest_UslugaCache,
				LpuFrom.Lpu_Nick as Lpu_Name,
				LpuSectionFrom.LpuSection_Name as LpuSection_Name,
				--'' as EvnCostPrint_PrintStatus,
				case when NoPrintCount.NPC > 0 then 'true' else 'false' end as EvnCostPrint_PrintStatus
			FROM v_EvnFuncRequest efr (nolock)
				inner join v_EvnDirection_all ed (nolock) on ed.EvnDirection_id = efr.EvnFuncRequest_pid and ed.EvnDirection_failDT is null
				inner join v_EvnQueue eq (nolock) on eq.EvnDirection_id = ed.EvnDirection_id and eq.EvnQueue_failDT is null and eq.EvnQueue_recDT is null
				left join v_TimetableMedService_lite ttms (nolock) on ttms.EvnDirection_id = ed.EvnDirection_id
				left join v_Lpu LpuFrom with (nolock) on LpuFrom.Lpu_id = ED.Lpu_sid
				left join v_LpuSection LpuSectionFrom with (nolock) on LpuSectionFrom.LpuSection_id = ED.LpuSection_id
				left join v_PersonState PS (nolock) on PS.Person_id = ED.Person_id
				left join v_PersonEncrypHIV peh with(nolock) on peh.Person_id = PS.Person_id
				outer apply (
					select top 1 
						ECP.EvnCostPrint_id as NPC
					from v_EvnUslugaPar EUP with(nolock)
					left join v_EvnCostPrint ECP on ECP.Evn_id = EUP.EvnUslugaPar_id
					where EUP.EvnDirection_id = efr.EvnFuncRequest_pid
					and ISNULL(ECP.EvnCostPrint_IsNoPrint,0) <> 2
				) NoPrintCount
				left join v_EvnStatus ES (nolock) on ES.EvnStatus_id = EFR.EvnStatus_id
				LEFT JOIN v_EvnPrescrDirection EPD with (nolock) on EPD.EvnDirection_id = ED.EvnDirection_id
				LEFT JOIN v_EvnPrescrProc EPP with (nolock) on EPP.EvnPrescrProc_id = EPD.EvnPrescr_id
				LEFT JOIN v_EvnPrescrFuncDiag EPFD with (nolock) on EPFD.EvnPrescrFuncDiag_id = EPD.EvnPrescr_id
				outer apply(
					select top 1 
						EvnUslugaPar_id
						from v_EvnUslugaPar with (nolock) 
						where EvnDirection_id = ED.EvnDirection_id
				) EvnUslugaPar
				LEFT JOIN v_RemoteConsultCenterResearch RCCR with (nolock) on RCCR.EvnUslugaPar_id = EvnUslugaPar.EvnUslugaPar_id
				LEFT JOIN v_MedServiceLink MSL with (nolock) on MSL.MedService_lid = :MedService_lid AND MSL.MedServiceLinkType_id = (select top 1 MSLT.MedServiceLinkType_id from v_MedServiceLinkType MSLT (nolock) where MSLT.MedServiceLinkType_Code = :MedServiceLinkType_Code)
				LEFT JOIN v_pmUserCache PUC with(nolock) on PUC.PMUser_id = ed.pmUser_insID
			WHERE 
				efr.MedService_id = :MedService_id
				--бывший EFR_EQ_Filters
				and TTMS.TimetableMedService_begTime is null
				and (
					ES.EvnStatus_SysNick <> 'FuncDonerec'
					OR (
						ES.EvnStatus_SysNick = 'FuncDonerec' 
						and CAST(efr.EvnFuncRequest_statusDate as date) between :begDate and :endDate
					)
				)
				{$filter}
				{$person_filter}
				--
		";

		// 2b. запрос по заявкам (все из EvnFuncRequest) только записанные
		$query .= "
		
			union all
			
			select
				ttms.TimetableMedService_id as TimetableMedService_id,
				ED.EvnDirection_id,
				ED.EvnQueue_id as EvnQueue_id,
				EFR.EvnFuncRequest_id,
				convert(varchar(10),TTMS.TimetableMedService_begTime,104) as group_name,
				case
					when TTMS.TimetableMedService_begTime is not null then convert(varchar,cast(TTMS.TimetableMedService_begTime as datetime),104)
					when ES.EvnStatus_SysNick = 'FuncDonerec' then convert(varchar,cast(efr.EvnFuncRequest_statusDate as datetime),104)
					else null
				end as TimetableMedService_begDate,
				isnull(convert(varchar(5), TTMS.TimetableMedService_begTime, 108),'б/з') as TimetableMedService_begTime,
				convert(varchar(10),TTMS.TimetableMedService_begTime,104) as TimetableMedService_Type,
				case when 2 = ISNULL(ED.EvnDirection_IsCito, 1) then 'true' else 'false' end as EvnDirection_IsCito,
				convert(varchar(10), ED.EvnDirection_setDT, 104) as EvnDirection_setDT,
				ED.EvnDirection_Num,
				ED.Person_id,
				EvnUslugaPar.EvnUslugaPar_id,
				MSL.MedService_id as RCC_MedService_id,
				RCCR.RemoteConsultCenterResearch_id,
				RCCR.RemoteConsultCenterResearch_status,
				{$selectPersonData}
				convert(varchar(10), PS.Person_BirthDay, 104) as Person_BirthDay,
				case when ES.EvnStatus_SysNick = 'FuncDonerec' then 2 else 1 end as EvnFuncRequest_IsExec,
				case
					when PUC.PMUser_surName is null then ''
					else PUC.PMUser_surName+' '+isnull(left(PUC.PMUser_firName,1),'')+' '+isnull(left(PUC.PMUser_secName,1),'')
				end as Operator,
				case when UCMS.UslugaComplexMedService_id IS NULL then 'Общее' else UC.UslugaComplex_Name end as TimetableMedServiceType,
				efr.EvnFuncRequest_UslugaCache,
				LpuFrom.Lpu_Nick as Lpu_Name,
				LpuSectionFrom.LpuSection_Name as LpuSection_Name,
				--'' as EvnCostPrint_PrintStatus,
				case when NoPrintCount.NPC > 0 then 'true' else 'false' end as EvnCostPrint_PrintStatus
			FROM v_EvnFuncRequest efr (nolock)
				inner join v_EvnDirection_all ed (nolock) on ed.EvnDirection_id = efr.EvnFuncRequest_pid and ed.EvnDirection_failDT is null
				inner join v_TimetableMedService_lite ttms (nolock) on ttms.EvnDirection_id = ed.EvnDirection_id and ttms.TimetableMedService_begTime is not null
				left join v_Lpu LpuFrom with (nolock) on LpuFrom.Lpu_id = ED.Lpu_sid
				left join v_LpuSection LpuSectionFrom with (nolock) on LpuSectionFrom.LpuSection_id = ED.LpuSection_id
				left join v_PersonState PS (nolock) on PS.Person_id = ED.Person_id
				left join v_PersonEncrypHIV peh with(nolock) on peh.Person_id = PS.Person_id
				--left join v_EvnCostPrint ECP (nolock) on ECP.Evn_id = efr.EvnFuncRequest_id
				outer apply (
					select top 1 ECP.EvnCostPrint_id as NPC
					from v_EvnUslugaPar EUP with(nolock)
					left join v_EvnCostPrint ECP on ECP.Evn_id = EUP.EvnUslugaPar_id
					where EUP.EvnDirection_id = efr.EvnFuncRequest_pid
					and ISNULL(ECP.EvnCostPrint_IsNoPrint,0) <> 2
				) NoPrintCount
				LEFT JOIN v_UslugaComplexMedService UCMS (nolock) on UCMS.UslugaComplexMedService_id = TTMS.UslugaComplexMedService_id
				left join v_UslugaComplex UC (nolock) on UC.UslugaComplex_id = UCMS.UslugaComplex_id
				left join v_EvnStatus ES (nolock) on ES.EvnStatus_id = EFR.EvnStatus_id
				LEFT JOIN v_EvnPrescrDirection EPD with (nolock) on EPD.EvnDirection_id = ED.EvnDirection_id
				LEFT JOIN v_EvnPrescrFuncDiag EPFD with (nolock) on EPFD.EvnPrescrFuncDiag_id = EPD.EvnPrescr_id
				outer apply(
					select top 1 EvnUslugaPar_id from v_EvnUslugaPar with (nolock) where EvnDirection_id = ED.EvnDirection_id
				) EvnUslugaPar
				LEFT JOIN v_RemoteConsultCenterResearch RCCR with (nolock) on RCCR.EvnUslugaPar_id = EvnUslugaPar.EvnUslugaPar_id
				LEFT JOIN v_MedServiceLink MSL with (nolock) on MSL.MedService_lid = :MedService_lid AND MSL.MedServiceLinkType_id = (select top 1 MSLT.MedServiceLinkType_id from v_MedServiceLinkType MSLT (nolock) where MSLT.MedServiceLinkType_Code = :MedServiceLinkType_Code)
				LEFT JOIN v_pmUserCache PUC with(nolock) on PUC.PMUser_id = ed.pmUser_insID
			WHERE
				efr.MedService_id = :MedService_id
				--бывший EFR_TTMS_Filters
				and TTMS.TimetableMedService_Day between :begDay_id and :endDay_id
				{$filter}
				{$person_filter}
				--
		";

		// 3. без записи (без EvnDirection, связь с услугой по EvnFuncRequest_pid
		$query .= "
		
			union all
			
			-- без направления (без EvnDirection, связь с услугой по EvnFuncRequest_pid) по #23048.
			select
				NULL as TimetableMedService_id,
				ED.EvnDirection_id,
				null as EvnQueue_id,
				EFR.EvnFuncRequest_id,
				convert(varchar(10),ED.EvnDirection_setDT,104) as group_name,
				case when EvnUslugaPar.EvnUslugaPar_setDT is not null
					then convert(varchar,cast(EvnUslugaPar.EvnUslugaPar_setDT as datetime),104)
					else null
				end as TimetableMedService_begDate,
				'б/н' as TimetableMedService_begTime,
				null as TimetableMedService_Type,
				case when 2 = ISNULL(ED.EvnDirection_IsCito, 1) then 'true' else 'false' end as EvnDirection_IsCito,
				convert(varchar(10), ED.EvnDirection_setDT, 104) as EvnDirection_setDT,
				ED.EvnDirection_Num,
				EFR.Person_id,
				EvnUslugaPar.EvnUslugaPar_id,
				MSL.MedService_id as RCC_MedService_id,
				RCCR.RemoteConsultCenterResearch_id,
				RCCR.RemoteConsultCenterResearch_status,
				{$selectPersonData}
				convert(varchar(10), PS.Person_BirthDay, 104) as Person_BirthDay,
				case when EvnUslugaPar.EvnUslugaPar_setDT is not null then 2 else 1 end as EvnFuncRequest_IsExec,
				case
					when UC.PMUser_surName is null then ''
					else UC.PMUser_surName+' '+isnull(left(UC.PMUser_firName,1),'')+' '+isnull(left(UC.PMUser_secName,1),'')
				end as Operator,
				'' as TimetableMedServiceType,
				efr.EvnFuncRequest_UslugaCache,
				'' as Lpu_Name,
				'' as LpuSection_Name,
				'' as EvnCostPrint_PrintStatus
			FROM v_EvnFuncRequest EFR  with (nolock)
				LEFT JOIN v_EvnStatus ES (nolock) on ES.EvnStatus_id = EFR.EvnStatus_id
				inner join v_EvnUslugaPar EvnUslugaPar with (nolock) on EvnUslugaPar.EvnUslugaPar_id = EFR.EvnFuncRequest_pid
				LEFT JOIN v_EvnDirection_all ED with (nolock) ON (1=0)
				LEFT JOIN v_PersonState PS with (nolock) on PS.Person_id = EFR.Person_id
				left join v_PersonEncrypHIV peh with(nolock) on peh.Person_id = PS.Person_id
				LEFT JOIN v_RemoteConsultCenterResearch RCCR with (nolock) on RCCR.EvnUslugaPar_id = EvnUslugaPar.EvnUslugaPar_id
				LEFT JOIN v_MedServiceLink MSL with (nolock) on MSL.MedService_lid = :MedService_lid AND MSL.MedServiceLinkType_id = (select top 1 MSLT.MedServiceLinkType_id from v_MedServiceLinkType MSLT (nolock) where MSLT.MedServiceLinkType_Code = :MedServiceLinkType_Code)
				LEFT JOIN v_pmUserCache UC with(nolock) on UC.PMUser_id = EFR.pmUser_insID
			WHERE
				isnull(ED.Lpu_did, EFR.Lpu_id) = :Lpu_id
				and isnull(ED.MedService_id, EFR.MedService_id) = :MedService_id
				and ED.TimetableMedService_id is null
				--бывший EFRFilters
				and CAST(isnull(ed.EvnDirection_setDT, EFR.EvnFuncRequest_setDT) as date) between :begDate and :endDate
				{$filter}
				{$person_filter}
				--
			order by TimetableMedService_begTime, TimetableMedServiceType
		";

		$resp = $this->queryResult($query, $queryParams);
		$grouped_resp = array();

		// добавим для вывода только нужные поля
		$allowed_fields = array(
			'EvnFuncRequest_id',
			'TimetableMedService_id',
			'Person_id',
			'Person_FIO',
			'group_name',
			'EvnDirection_IsCito',
			'EvnDirection_Num',
			'EvnDirection_id',
			'TimetableMedService_begTime',
			'TimetableMedService_begDate',
			'TimetableMedService_Type',
			'EvnFuncRequest_IsExec',
			'Person_Birthday',
			'Person_Age',
			'UslugaComplex_Name'
		);

		$allowed_fields = array_flip($allowed_fields);

		foreach($resp as &$item) {

			// cформируем Person_FIO
			$item['Person_FIO'] = trim($item['Person_Surname'].' '.$item['Person_Firname'].' '.$item['Person_Secname']);

			// на случай если ещё не кэшировалось
			$needRecache = true;

			if (!empty($item['EvnFuncRequest_UslugaCache'])) {
				$EvnFuncRequest_UslugaCache = json_decode($item['EvnFuncRequest_UslugaCache'], true);

				if (
					!empty($EvnFuncRequest_UslugaCache[0])
					&& is_array($EvnFuncRequest_UslugaCache[0])
				) {
					$item['UslugaComplex_Name'] = $EvnFuncRequest_UslugaCache[0]['UslugaComplex_Name'];

					if (array_key_exists('EvnUslugaPar_setDate', $EvnFuncRequest_UslugaCache[0])) {
						$needRecache = false;
					}
				}
			}

			if ($needRecache) {
				$item['EvnFuncRequest_UslugaCache'] = $this->getEvnFuncRequestUslugaList($item);
			}

			if (empty($item['TimetableMedService_Type'])) $item['TimetableMedService_Type'] = 'Общее';

			//группируем
			if (!empty($item['TimetableMedService_begDate'])) {
				$group_name = $item['TimetableMedService_begDate'];
			} else {
				$group_name = 'Очередь';
			}

			if (!isset($grouped_resp[$group_name])) {
				$grouped_resp[$group_name] = array(
					'group_name' => $group_name,
					'list' => array()
				);
			}

			// отфильтруем поля
			foreach ($item as $fieldName => &$value) {
				if (!isset($allowed_fields[$fieldName])) {
					unset($item[$fieldName]);
				}
			}

			$grouped_resp[$group_name]['list'][] = $item;
		}

		if (!empty($grouped_resp && is_array($grouped_resp))) {
			$grouped_resp = array_values($grouped_resp);
		}

		return $grouped_resp;
	}


	/**
	 *
	 * @param type $data
	 * @return type
	 */
	function loadEvnFuncRequestListDoneStatus($data) {
		$commonFilters = array();
		$EFR_EQ_Filters = array();
		$EFR_TTMS_Filters = array();
		$EFRFilters = array();
		$queryParams = array();
		$TTMSFilters = array();

		$TTMSFilters[] = "(ISNULL(TTMS.MedService_id, UCMS.MedService_id) = :MedService_id)"; // Направление в эту определенную службу

		$queryParams['Lpu_id'] = $data['Lpu_id'];
		$queryParams['MedService_id'] = $data['MedService_id'];

		if ( !empty($data['Search_SurName']) ) {
			$commonFilters[] = "PS.Person_SurName LIKE (:Search_SurName+'%')";
			$queryParams['Search_SurName'] = rtrim($data['Search_SurName']);
		}

		if ( !empty($data['Search_FirName']) ) {
			$commonFilters[] = "PS.Person_FirName LIKE (:Search_FirName+'%')";
			$queryParams['Search_FirName'] = rtrim($data['Search_FirName']);
		}

		if ( !empty($data['Search_SecName']) ) {
			$commonFilters[] = "PS.Person_SecName LIKE (:Search_SecName+'%')";
			$queryParams['Search_SecName'] = rtrim($data['Search_SecName']);
		}

		if ( !empty($data['Search_BirthDay']) ) {
			$commonFilters[] = "PS.Person_BirthDay = :Search_BirthDay";
			$queryParams['Search_BirthDay'] = $data['Search_BirthDay'];
		}

		if ( !empty($data['EvnDirection_Num']) ) {
			$commonFilters[] = "ED.EvnDirection_Num = :EvnDirection_Num";
			$queryParams['EvnDirection_Num'] = $data['EvnDirection_Num'];
		}

		// Фильтр по услугам
		if ( !empty($data['UslugaComplex_id']) ) {
			$commonFilters[] = "exists (
				select top 1
					t1.UslugaComplex_id
				from
					EvnUsluga t1 with (nolock)
					inner join Evn (nolock) on t1.EvnUsluga_id = Evn.Evn_id and Evn.Evn_deleted = 1 and EvnClass_id = 47
				where
					t1.EvnDirection_id = ED.EvnDirection_id
					and t1.UslugaComplex_id = :UslugaComplex_id
			)";
			$queryParams['UslugaComplex_id'] = $data['UslugaComplex_id'];
		}

		if ( !empty($data['EvnDirection_IsCito']) ) {
			$commonFilters[] = "ISNULL(ED.EvnDirection_IsCito, 1) = :EvnDirection_IsCito";
			$queryParams['EvnDirection_IsCito'] = $data['EvnDirection_IsCito'];
		}

		if ( !empty($data['begDate']) && !empty($data['endDate']) ) {
			//записанные отображаются в тот день, на который они записаны #11499
			//те, кто в очереди отображаются в тот день, когда они направлены
			$TTMSFilters[] = "(
				(TTMS.TimetableMedService_Day is not null and TTMS.TimetableMedService_Day between :begDay_id and :endDay_id)
			)";
			$EFR_EQ_Filters[] = "(
				(ES.EvnStatus_SysNick = 'FuncDonerec' and CAST(efr.EvnFuncRequest_statusDate as date) between :begDate and :endDate)
			)";
			$EFR_EQ_Filters[] = "(
				TTMS.TimetableMedService_begTime is null
			)";
			$EFR_TTMS_Filters[] = "(
				(TTMS.TimetableMedService_Day between :begDay_id and :endDay_id)
			)";
			$EFRFilters[] = "(CAST(isnull(ed.EvnDirection_setDT, EFR.EvnFuncRequest_setDT) as date) between :begDate and :endDate)";
			$this->load->helper('Reg');
			$queryParams['begDay_id'] = TimeToDay(strtotime($data['begDate']));
			$queryParams['endDay_id'] = TimeToDay(strtotime($data['endDate']));
			$queryParams['begDate'] = $data['begDate'];
			$queryParams['endDate'] = $data['endDate'];
		}

		$queryParams['MedService_lid'] = (!empty($data['session']['CurMedService_id']))?$data['session']['CurMedService_id']:null;
		$queryParams['MedServiceLinkType_Code'] = '3';


		$EFRFilters = array_merge($EFRFilters, $commonFilters);
		$EFR_EQ_Filters = array_merge($EFR_EQ_Filters, $commonFilters);
		$EFR_TTMS_Filters = array_merge($EFR_TTMS_Filters, $commonFilters);

		$querys = array();

		$query = "
			select
				ttms.TimetableMedService_id as TimetableMedService_id,
				ED.EvnDirection_id,
				ED.EvnQueue_id as EvnQueue_id,
				[dbo].[getPersonPhones](ed.Person_id, '<br />') as Person_Phone,
				EFR.EvnFuncRequest_id,
				convert(varchar(10),TTMS.TimetableMedService_begTime,104) as group_name,
				case
					when TTMS.TimetableMedService_begTime is not null then convert(varchar,cast(TTMS.TimetableMedService_begTime as datetime),104)
					when ES.EvnStatus_SysNick = 'FuncDonerec' then convert(varchar,cast(efr.EvnFuncRequest_statusDate as datetime),104)
					else null
				end as TimetableMedService_begDate,
				isnull(convert(varchar(5), TTMS.TimetableMedService_begTime, 108),'б/з') as TimetableMedService_begTime,
				convert(varchar(10),TTMS.TimetableMedService_begTime,104) as TimetableMedService_Type,
				case when 2 = ISNULL(ED.EvnDirection_IsCito, 1) then 'true' else 'false' end as EvnDirection_IsCito,
				convert(varchar(10), ED.EvnDirection_setDT, 104) as EvnDirection_setDT,
				ED.EvnDirection_Num,
				ED.Person_id,
				EvnUslugaPar.EvnUslugaPar_id,
				MSL.MedService_id as RCC_MedService_id,
				RCCR.RemoteConsultCenterResearch_id,
				RCCR.RemoteConsultCenterResearch_status,
				(PS.Person_SurName + ' ' + IsNull(PS.Person_FirName, '') + ' ' + IsNull(PS.Person_SecName,'')) as Person_FIO,
				convert(varchar(10), PS.Person_BirthDay, 104) as Person_BirthDay,
				case when ES.EvnStatus_SysNick = 'FuncDonerec' then 'true' else 'false' end as FuncRequestState,
				case
					when PUC.PMUser_surName is null then ''
					else PUC.PMUser_surName+' '+isnull(left(PUC.PMUser_firName,1),'')+' '+isnull(left(PUC.PMUser_secName,1),'')
				end as Operator,
				case when UCMS.UslugaComplexMedService_id IS NULL then 'Общее' else UC.UslugaComplex_Name end as TimetableMedServiceType,
				efr.EvnFuncRequest_UslugaCache,
				LpuFrom.Lpu_Nick as Lpu_Name,
				LpuSectionFrom.LpuSection_Name as LpuSection_Name,
				MP.Person_Fio as MedPerson_Fio,
				case when NoPrintCount.NPC > 0 then 'true' else 'false' end as EvnCostPrint_PrintStatus
			FROM v_EvnFuncRequest efr (nolock)
				inner join v_EvnDirection_all ed (nolock) on ed.EvnDirection_id = efr.EvnFuncRequest_pid and ed.EvnDirection_failDT is null
				inner join v_TimetableMedService_lite ttms (nolock) on ttms.EvnDirection_id = ed.EvnDirection_id and ttms.TimetableMedService_begTime is not null
				left join v_Lpu LpuFrom with (nolock) on LpuFrom.Lpu_id = ED.Lpu_sid
				left join v_LpuSection LpuSectionFrom with (nolock) on LpuSectionFrom.LpuSection_id = ED.LpuSection_id
				left join v_PersonState PS (nolock) on PS.Person_id = ED.Person_id
				outer apply (
					select top 1 ECP.EvnCostPrint_id as NPC
					from v_EvnUslugaPar EUP with(nolock)
					left join v_EvnCostPrint ECP on ECP.Evn_id = EUP.EvnUslugaPar_id
					where EUP.EvnDirection_id = efr.EvnFuncRequest_pid
					and ISNULL(ECP.EvnCostPrint_IsNoPrint,0) <> 2
				) NoPrintCount
				LEFT JOIN v_UslugaComplexMedService UCMS (nolock) on UCMS.UslugaComplexMedService_id = TTMS.UslugaComplexMedService_id
				left join v_UslugaComplex UC (nolock) on UC.UslugaComplex_id = UCMS.UslugaComplex_id
				left join v_EvnStatus ES (nolock) on ES.EvnStatus_id = EFR.EvnStatus_id
				LEFT JOIN v_EvnPrescrDirection EPD with (nolock) on EPD.EvnDirection_id = ED.EvnDirection_id
				LEFT JOIN v_EvnPrescrFuncDiag EPFD with (nolock) on EPFD.EvnPrescrFuncDiag_id = EPD.EvnPrescr_id
				outer apply(
					select top 1 EvnUslugaPar_id from v_EvnUslugaPar with (nolock) where EvnDirection_id = ED.EvnDirection_id
				) EvnUslugaPar
				LEFT JOIN v_RemoteConsultCenterResearch RCCR with (nolock) on RCCR.EvnUslugaPar_id = EvnUslugaPar.EvnUslugaPar_id
				LEFT JOIN v_MedServiceLink MSL with (nolock) on MSL.MedService_lid = :MedService_lid AND MSL.MedServiceLinkType_id = (select top 1 MSLT.MedServiceLinkType_id from v_MedServiceLinkType MSLT (nolock) where MSLT.MedServiceLinkType_Code = :MedServiceLinkType_Code)
				LEFT JOIN v_pmUserCache PUC with(nolock) on PUC.PMUser_id = ed.pmUser_insID
				LEFT JOIN v_MedPersonal MP with (nolock) on ED.MedPersonal_id = MP.MedPersonal_id
			WHERE
				efr.MedService_id = :MedService_id
				and ES.EvnStatus_SysNick = 'FuncDonerec'
				" . (count($EFR_TTMS_Filters) > 0 ? "and " . implode(' and ', $EFR_TTMS_Filters) : "") . "
			union all
			select
				ttms.TimetableMedService_id,
				ED.EvnDirection_id,
				ED.EvnQueue_id as EvnQueue_id,
				[dbo].[getPersonPhones](ed.Person_id, '<br />') as Person_Phone,
				EFR.EvnFuncRequest_id,
				null as group_name,
				case
					when ES.EvnStatus_SysNick = 'FuncDonerec' then convert(varchar,cast(efr.EvnFuncRequest_statusDate as datetime),104)
					else null
				end as TimetableMedService_begDate,
				'б/з' as TimetableMedService_begTime,
				null as TimetableMedService_Type,
				case when 2 = ISNULL(ED.EvnDirection_IsCito, 1) then 'true' else 'false' end as EvnDirection_IsCito,
				convert(varchar(10), ED.EvnDirection_setDT, 104) as EvnDirection_setDT,
				ED.EvnDirection_Num,
				ED.Person_id,
				EvnUslugaPar.EvnUslugaPar_id,
				MSL.MedService_id as RCC_MedService_id,
				RCCR.RemoteConsultCenterResearch_id,
				RCCR.RemoteConsultCenterResearch_status,
				(PS.Person_SurName + ' ' + IsNull(PS.Person_FirName, '') + ' ' + IsNull(PS.Person_SecName,'')) as Person_FIO,
				convert(varchar(10), PS.Person_BirthDay, 104) as Person_BirthDay,
				case when ES.EvnStatus_SysNick = 'FuncDonerec' then 'true' else 'false' end as FuncRequestState,
				case
					when PUC.PMUser_surName is null then ''
					else PUC.PMUser_surName+' '+isnull(left(PUC.PMUser_firName,1),'')+' '+isnull(left(PUC.PMUser_secName,1),'')
				end as Operator,
				'Общее' as TimetableMedServiceType,
				efr.EvnFuncRequest_UslugaCache,
				LpuFrom.Lpu_Nick as Lpu_Name,
				LpuSectionFrom.LpuSection_Name as LpuSection_Name,
				MP.Person_Fio as MedPerson_Fio,
				case when NoPrintCount.NPC > 0 then 'true' else 'false' end as EvnCostPrint_PrintStatus
			FROM v_EvnFuncRequest efr (nolock)
				inner join v_EvnDirection_all ed (nolock) on ed.EvnDirection_id = efr.EvnFuncRequest_pid and ed.EvnDirection_failDT is null
				inner join v_EvnQueue eq (nolock) on eq.EvnDirection_id = ed.EvnDirection_id and eq.EvnQueue_failDT is null and eq.EvnQueue_recDT is null
				left join v_TimetableMedService_lite ttms (nolock) on ttms.EvnDirection_id = ed.EvnDirection_id
				left join v_Lpu LpuFrom with (nolock) on LpuFrom.Lpu_id = ED.Lpu_sid
				left join v_LpuSection LpuSectionFrom with (nolock) on LpuSectionFrom.LpuSection_id = ED.LpuSection_id
				left join v_PersonState PS (nolock) on PS.Person_id = ED.Person_id
				--left join v_EvnCostPrint ECP (nolock) on ECP.Evn_id = efr.EvnFuncRequest_id
				outer apply (
					select top 1 ECP.EvnCostPrint_id as NPC
					from v_EvnUslugaPar EUP with(nolock)
					left join v_EvnCostPrint ECP on ECP.Evn_id = EUP.EvnUslugaPar_id
					where EUP.EvnDirection_id = efr.EvnFuncRequest_pid
					and ISNULL(ECP.EvnCostPrint_IsNoPrint,0) <> 2
				) NoPrintCount
				left join v_EvnStatus ES (nolock) on ES.EvnStatus_id = EFR.EvnStatus_id
				LEFT JOIN v_EvnPrescrDirection EPD with (nolock) on EPD.EvnDirection_id = ED.EvnDirection_id
				LEFT JOIN v_EvnPrescrFuncDiag EPFD with (nolock) on EPFD.EvnPrescrFuncDiag_id = EPD.EvnPrescr_id
				outer apply(
					select top 1 EvnUslugaPar_id from v_EvnUslugaPar with (nolock) where EvnDirection_id = ED.EvnDirection_id
				) EvnUslugaPar
				LEFT JOIN v_RemoteConsultCenterResearch RCCR with (nolock) on RCCR.EvnUslugaPar_id = EvnUslugaPar.EvnUslugaPar_id
				LEFT JOIN v_MedServiceLink MSL with (nolock) on MSL.MedService_lid = :MedService_lid AND MSL.MedServiceLinkType_id = (select top 1 MSLT.MedServiceLinkType_id from v_MedServiceLinkType MSLT (nolock) where MSLT.MedServiceLinkType_Code = :MedServiceLinkType_Code)
				LEFT JOIN v_pmUserCache PUC with(nolock) on PUC.PMUser_id = ed.pmUser_insID
				LEFT JOIN v_MedPersonal MP with (nolock) on ED.MedPersonal_id = MP.MedPersonal_id
			WHERE
				efr.MedService_id = :MedService_id
				" . (count($EFR_EQ_Filters) > 0 ? "and " . implode(' and ', $EFR_EQ_Filters) : "") . "
		";

		$response = array();

		//echo getDebugSQL($query, $queryParams);exit;
		$res = $this->db->query($query, $queryParams);

		if ( is_object($res) ) {
			$resp = $res->result('array');
			foreach($resp as $respone) {
				// на случай если ещё не кэшировалось
				$needRecache = true;
				if (!empty($respone['EvnFuncRequest_UslugaCache'])) {
					$EvnFuncRequest_UslugaCache = json_decode($respone['EvnFuncRequest_UslugaCache'], true);
					if (is_array($EvnFuncRequest_UslugaCache)) {
						if (!empty($EvnFuncRequest_UslugaCache[0]) && is_array($EvnFuncRequest_UslugaCache[0]) && array_key_exists('EvnUslugaPar_setDate', $EvnFuncRequest_UslugaCache[0])) {
							$needRecache = false;
						}
					}
				}
				if ($needRecache) {
					$respone['EvnFuncRequest_UslugaCache'] = $this->getEvnFuncRequestUslugaList($respone);
				}
				$response[] = $respone;
			}
		}

		return $response;
	}
	
	/**
	 * @param type $data
	 * @return boolean
	 */
	function loadEvnFuncRequestViewList($data) {
		$commonFilters = array();
		$EFRWithDirFilters = array();
		$EFRFilters = array();
		$queryParams = array();
		$TTMSFilters = array();
		
		$TTMSFilters[] = "(ISNULL(TTMS.MedService_id, UCMS.MedService_id) = :MedService_id)"; // Направление в эту определенную службу

		$queryParams['Lpu_id'] = $data['Lpu_id'];
		$queryParams['MedService_id'] = $data['MedService_id'];
		
		if ( !empty($data['Search_SurName']) ) {
			$commonFilters[] = "PS.Person_SurName LIKE (:Search_SurName+'%')";
			$queryParams['Search_SurName'] = rtrim($data['Search_SurName']);
		}
		
		if ( !empty($data['Search_FirName']) ) {
			$commonFilters[] = "PS.Person_FirName LIKE (:Search_FirName+'%')";
			$queryParams['Search_FirName'] = rtrim($data['Search_FirName']);
		}
		
		if ( !empty($data['Search_SecName']) ) {
			$commonFilters[] = "PS.Person_SecName LIKE (:Search_SecName+'%')";
			$queryParams['Search_SecName'] = rtrim($data['Search_SecName']);
		}
		
		if ( !empty($data['Search_BirthDay']) ) {
			$commonFilters[] = "PS.Person_BirthDay = :Search_BirthDay";
			$queryParams['Search_BirthDay'] = $data['Search_BirthDay'];
		}

		if ( !empty($data['EvnDirection_Num']) ) {
			$commonFilters[] = "ED.EvnDirection_Num = :EvnDirection_Num";
			$queryParams['EvnDirection_Num'] = $data['EvnDirection_Num'];
		}
		//$commonFilters[] = "UCMS.UslugaComplexMedService_id is not null";
		// Фильтр по услугам
		if ( !empty($data['UslugaComplex_id']) ) {
			$commonFilters[] = "exists (
				select top 1
					t1.UslugaComplex_id
				from 
					EvnUsluga t1 with (nolock)
					inner join Evn (nolock) on t1.EvnUsluga_id = Evn.Evn_id and Evn.Evn_deleted = 1 and EvnClass_id = 47
				where 
					t1.EvnDirection_id = ED.EvnDirection_id
					and t1.UslugaComplex_id = :UslugaComplex_id
			)";
			$queryParams['UslugaComplex_id'] = $data['UslugaComplex_id'];
		}
		
		if ( !empty($data['EvnDirection_IsCito']) ) {
			$commonFilters[] = "ISNULL(ED.EvnDirection_IsCito, 1) = :EvnDirection_IsCito";
			$queryParams['EvnDirection_IsCito'] = $data['EvnDirection_IsCito'];
		}

		if ( !empty($data['begDate']) && !empty($data['endDate']) ) {
			//записанные отображаются в тот день, на который они записаны #11499
			//те, кто в очереди отображаются в тот день, когда они направлены
			$TTMSFilters[] = "(
				(TTMS.TimetableMedService_Day is not null and TTMS.TimetableMedService_Day between :begDay_id and :endDay_id)
			)";
			$EFRWithDirFilters[] = "(
				(TTMS.TimetableMedService_begTime is null and ES.EvnStatus_SysNick <> 'FuncDonerec')
				OR
				(TTMS.TimetableMedService_Day is null and ES.EvnStatus_SysNick = 'FuncDonerec' and CAST(efr.EvnFuncRequest_statusDate as date) between :begDate and :endDate)
				OR
				(TTMS.TimetableMedService_Day between :begDay_id and :endDay_id)
			)";
			$EFRFilters[] = "(CAST(isnull(ed.EvnDirection_setDT, EFR.EvnFuncRequest_setDT) as date) between :begDate and :endDate)";
			$this->load->helper('Reg');
			$queryParams['begDay_id'] = TimeToDay(strtotime($data['begDate']));
			$queryParams['endDay_id'] = TimeToDay(strtotime($data['endDate']));
			$queryParams['begDate'] = $data['begDate'];
			$queryParams['endDate'] = $data['endDate'];
		}
		
		$queryParams['MedService_lid'] = (!empty($data['session']['CurMedService_id']))?$data['session']['CurMedService_id']:null;
		$queryParams['MedServiceLinkType_Code'] = '3';

		// $TTMSFilters = array_merge($TTMSFilters, $commonFilters);
		$EFRFilters = array_merge($EFRFilters, $commonFilters);
		$EFRWithDirFilters = array_merge($EFRWithDirFilters, $commonFilters);

		$querys = array();

		// 1. запрос по биркам (все пустые без направлений)
		$querys[0] = "
			select
				TTMS.TimetableMedService_id,
				null as EvnDirection_id,
				null as EvnQueue_id,
                null as Person_Phone,
				null as EvnFuncRequest_id,
				convert(varchar(10),TTMS.TimetableMedService_begTime,104) as group_name,
				case when TTMS.TimetableMedService_begTime is not null
					then convert(varchar,cast(TTMS.TimetableMedService_begTime as datetime),104)
					else null
				end as TimetableMedService_begDate,
				isnull(convert(varchar(5), TTMS.TimetableMedService_begTime, 108),'б/з') as TimetableMedService_begTime,
				convert(varchar(10),TTMS.TimetableMedService_begTime,104) as TimetableMedService_Type,
				'false' as EvnDirection_IsCito,
				null as EvnDirection_setDT,
				null as EvnDirection_Num,	
				null as Person_id,
				null as EvnUslugaPar_id,
				MSL.MedService_id as RCC_MedService_id,
				null as RemoteConsultCenterResearch_id,
				null as RemoteConsultCenterResearch_status,
				null as Person_FIO,
				null as Person_BirthDay,
				'false' as FuncRequestState,
				'' as Operator,
				case when UCMS.UslugaComplexMedService_id IS NULL then 'Общее' else UC.UslugaComplex_Name end as TimetableMedServiceType,
				'' as EvnFuncRequest_UslugaCache,
				'' as Lpu_Name,
				'' as LpuSection_Name,
				'' as EvnCostPrint_PrintStatus
			FROM v_TimetableMedService_lite TTMS with (nolock)
				LEFT JOIN v_UslugaComplexMedService UCMS (nolock) on UCMS.UslugaComplexMedService_id = TTMS.UslugaComplexMedService_id
				left join v_UslugaComplex UC (nolock) on UC.UslugaComplex_id = UCMS.UslugaComplex_id
				LEFT JOIN v_MedService MS with (nolock) ON MS.MedService_id = ISNULL(TTMS.MedService_id, UCMS.MedService_id)
				LEFT JOIN v_MedServiceLink MSL with (nolock) on MSL.MedService_lid = :MedService_lid AND MSL.MedServiceLinkType_id = (select top 1 MSLT.MedServiceLinkType_id from v_MedServiceLinkType MSLT (nolock) where MSLT.MedServiceLinkType_Code = :MedServiceLinkType_Code)
				--LEFT JOIN v_pmUserCache PUC with(nolock) on PUC.PMUser_id = TTMS.pmUser_insID
			WHERE 
				" . implode(' and ', $TTMSFilters) . "
				and TTMS.EvnDirection_id is null
				
		";

		// 2. запрос по заявкам (все из EvnFuncRequest)
		$querys[0] .= "
			union all
			select
				ttms.TimetableMedService_id as TimetableMedService_id,
				ED.EvnDirection_id,
				ED.EvnQueue_id as EvnQueue_id,
				[dbo].[getPersonPhones](ed.Person_id, '<br />') as Person_Phone,
				EFR.EvnFuncRequest_id,
				convert(varchar(10),TTMS.TimetableMedService_begTime,104) as group_name,
				case
					when TTMS.TimetableMedService_begTime is not null then convert(varchar,cast(TTMS.TimetableMedService_begTime as datetime),104)
					when ES.EvnStatus_SysNick = 'FuncDonerec' then convert(varchar,cast(efr.EvnFuncRequest_statusDate as datetime),104)
					else null
				end as TimetableMedService_begDate,
				isnull(convert(varchar(5), TTMS.TimetableMedService_begTime, 108),'б/з') as TimetableMedService_begTime,
				convert(varchar(10),TTMS.TimetableMedService_begTime,104) as TimetableMedService_Type,
				case when 2 = ISNULL(ED.EvnDirection_IsCito, 1) then 'true' else 'false' end as EvnDirection_IsCito,
				convert(varchar(10), ED.EvnDirection_setDT, 104) as EvnDirection_setDT,
				ED.EvnDirection_Num,	
				ED.Person_id,
				EvnUslugaPar.EvnUslugaPar_id,
				MSL.MedService_id as RCC_MedService_id,
				RCCR.RemoteConsultCenterResearch_id,
				RCCR.RemoteConsultCenterResearch_status,
				(PS.Person_SurName + ' ' + IsNull(PS.Person_FirName, '') + ' ' + IsNull(PS.Person_SecName,'')) as Person_FIO,
				convert(varchar(10), PS.Person_BirthDay, 104) as Person_BirthDay,
				case when ES.EvnStatus_SysNick = 'FuncDonerec' then 'true' else 'false' end as FuncRequestState,
				case
					when PUC.PMUser_surName is null then ''
					else PUC.PMUser_surName+' '+isnull(left(PUC.PMUser_firName,1),'')+' '+isnull(left(PUC.PMUser_secName,1),'')
				end as Operator,
				case when UCMS.UslugaComplexMedService_id IS NULL then 'Общее' else UC.UslugaComplex_Name end as TimetableMedServiceType,
				efr.EvnFuncRequest_UslugaCache,
				LpuFrom.Lpu_Nick as Lpu_Name,
				LpuSectionFrom.LpuSection_Name as LpuSection_Name,
				--'' as EvnCostPrint_PrintStatus,
				case when NoPrintCount.NPC > 0 then 'true' else 'false' end as EvnCostPrint_PrintStatus
			FROM v_EvnFuncRequest efr (nolock)
				inner join v_EvnDirection_all ed (nolock) on ed.EvnDirection_id = efr.EvnFuncRequest_pid and (ed.TimeTableMedService_id is not null or ed.EvnQueue_id is not null) -- только записанные или из очереди
				left join v_Lpu LpuFrom with (nolock) on LpuFrom.Lpu_id = ED.Lpu_id
				left join v_LpuSection LpuSectionFrom with (nolock) on LpuSectionFrom.LpuSection_id = ED.LpuSection_id
				left join v_PersonState PS (nolock) on PS.Person_id = ED.Person_id
				left join v_TimetableMedService_lite ttms (nolock) on ttms.EvnDirection_id = ed.EvnDirection_id
				--left join v_EvnCostPrint ECP (nolock) on ECP.Evn_id = efr.EvnFuncRequest_id
				outer apply (
					select count(ECP.EvnCostPrint_id) as NPC
					from v_EvnUslugaPar EUP with(nolock)
					left join v_EvnCostPrint ECP on ECP.Evn_id = EUP.EvnUslugaPar_id
					where EUP.EvnDirection_id = efr.EvnFuncRequest_pid
					and ISNULL(ECP.EvnCostPrint_IsNoPrint,0) <> 2
				) NoPrintCount
				/*outer apply (
					Select top 1
						TimetableMedService_id,
						UslugaComplexMedService_id,
						TimetableMedService_Day,
						TimetableMedService_begTime,
						TimetableMedService_factTime
					from
						v_TimetableMedService_lite (nolock)
					where EvnDirection_id = ed.EvnDirection_id
				) ttms*/
				LEFT JOIN v_UslugaComplexMedService UCMS (nolock) on UCMS.UslugaComplexMedService_id = TTMS.UslugaComplexMedService_id
				left join v_UslugaComplex UC (nolock) on UC.UslugaComplex_id = UCMS.UslugaComplex_id
				left join v_EvnQueue eq (nolock) on eq.EvnQueue_id = ed.EvnQueue_id
				left join v_EvnStatus ES (nolock) on ES.EvnStatus_id = EFR.EvnStatus_id
				LEFT JOIN v_EvnPrescrDirection EPD with (nolock) on EPD.EvnDirection_id = ED.EvnDirection_id
				LEFT JOIN v_EvnPrescrFuncDiag EPFD with (nolock) on EPFD.EvnPrescrFuncDiag_id = EPD.EvnPrescr_id
				outer apply(
					select top 1 EvnUslugaPar_id from v_EvnUslugaPar with (nolock) where EvnDirection_id = ED.EvnDirection_id
				) EvnUslugaPar
				LEFT JOIN v_RemoteConsultCenterResearch RCCR with (nolock) on RCCR.EvnUslugaPar_id = EvnUslugaPar.EvnUslugaPar_id
				LEFT JOIN v_MedServiceLink MSL with (nolock) on MSL.MedService_lid = :MedService_lid AND MSL.MedServiceLinkType_id = (select top 1 MSLT.MedServiceLinkType_id from v_MedServiceLinkType MSLT (nolock) where MSLT.MedServiceLinkType_Code = :MedServiceLinkType_Code)
				LEFT JOIN v_pmUserCache PUC with(nolock) on PUC.PMUser_id = EFR.pmUser_insID
			WHERE 
				efr.MedService_id = :MedService_id and eq.EvnQueue_recDT is null
				" . (count($EFRWithDirFilters) > 0 ? "and " . implode(' and ', $EFRWithDirFilters) : "") . "
		";
		
		// 3. без записи (без EvnDirection, связь с услугой по EvnFuncRequest_pid
		$querys[0] .= "
			union all
			-- без направления (без EvnDirection, связь с услугой по EvnFuncRequest_pid) по #23048.
			select
				NULL as TimetableMedService_id,
				ED.EvnDirection_id,
				null as EvnQueue_id,
				[dbo].[getPersonPhones](EFR.Person_id, '<br />') as Person_Phone,
				EFR.EvnFuncRequest_id,
				convert(varchar(10),ED.EvnDirection_setDT,104) as group_name,
				case when EvnUslugaPar.EvnUslugaPar_setDT is not null
					then convert(varchar,cast(EvnUslugaPar.EvnUslugaPar_setDT as datetime),104)
					else null
				end as TimetableMedService_begDate,
				'б/н' as TimetableMedService_begTime,
				null as TimetableMedService_Type,
				case when 2 = ISNULL(ED.EvnDirection_IsCito, 1) then 'true' else 'false' end as EvnDirection_IsCito,
				convert(varchar(10), ED.EvnDirection_setDT, 104) as EvnDirection_setDT,
				ED.EvnDirection_Num,
				EFR.Person_id,
				EvnUslugaPar.EvnUslugaPar_id,
				MSL.MedService_id as RCC_MedService_id,
				RCCR.RemoteConsultCenterResearch_id,
				RCCR.RemoteConsultCenterResearch_status,
				(PS.Person_SurName + ' ' + IsNull(PS.Person_FirName, '') + ' ' + IsNull(PS.Person_SecName,'')) as Person_FIO,
				convert(varchar(10), PS.Person_BirthDay, 104) as Person_BirthDay,
				case when EvnUslugaPar.EvnUslugaPar_setDT is not null then 'true' else 'false' end as FuncRequestState,
				case
					when UC.PMUser_surName is null then ''
					else UC.PMUser_surName+' '+isnull(left(UC.PMUser_firName,1),'')+' '+isnull(left(UC.PMUser_secName,1),'')
				end as Operator,
				'' as TimetableMedServiceType,
				efr.EvnFuncRequest_UslugaCache,
				'' as Lpu_Name,
				'' as LpuSection_Name,
				'' as EvnCostPrint_PrintStatus
			FROM v_EvnFuncRequest EFR  with (nolock)
				LEFT JOIN v_EvnStatus ES (nolock) on ES.EvnStatus_id = EFR.EvnStatus_id
				inner join v_EvnUslugaPar EvnUslugaPar with (nolock) on EvnUslugaPar.EvnUslugaPar_id = EFR.EvnFuncRequest_pid
				LEFT JOIN v_EvnDirection_all ED with (nolock) ON (1=0)
				LEFT JOIN v_PersonState PS with (nolock) on PS.Person_id = EFR.Person_id
				LEFT JOIN v_RemoteConsultCenterResearch RCCR with (nolock) on RCCR.EvnUslugaPar_id = EvnUslugaPar.EvnUslugaPar_id
				LEFT JOIN v_MedServiceLink MSL with (nolock) on MSL.MedService_lid = :MedService_lid AND MSL.MedServiceLinkType_id = (select top 1 MSLT.MedServiceLinkType_id from v_MedServiceLinkType MSLT (nolock) where MSLT.MedServiceLinkType_Code = :MedServiceLinkType_Code)
				LEFT JOIN v_pmUserCache UC with(nolock) on UC.PMUser_id = EFR.pmUser_insID
			WHERE
				isnull(ED.Lpu_did, EFR.Lpu_id) = :Lpu_id
				and isnull(ED.MedService_id, EFR.MedService_id) = :MedService_id
				and ED.TimetableMedService_id is null
				" . (count($EFRFilters) > 0 ? "and " . implode(' and ', $EFRFilters) : "") . "
		order by TimetableMedService_begTime
";
		
		$response = array();

		foreach($querys as $query) {
			$res = $this->db->query($query, $queryParams);
			if ( is_object($res) ) {
				$resp = $res->result('array');
				foreach($resp as $respone) {
					// на случай если ещё не кэшировалось
					if (empty($respone['EvnFuncRequest_UslugaCache'])) {
						$respone['EvnFuncRequest_UslugaCache'] = $this->getEvnFuncRequestUslugaList($respone, $data);
					}
					$response[] = $respone;
				}
			}
			
			
		}

		return $response;
	}

	/**
	 * Возвращает данные заявки на исследование
	 *
	 * @param array $data
	 * @return array|boolean
	 */
	function getEvnProcRequest($data) {
		$query = "
			select
				EFR.EvnFuncRequest_id,
				ED.EvnDirection_id,
				TTMS.TimetableMedService_id,
				ED.Server_id,
				ED.Person_id,
				ED.PersonEvn_id,
				convert(varchar(10), ED.EvnDirection_setDT, 104) as EvnDirection_setDT,
				convert(varchar(5), EPP.EvnPrescrProc_didDT, 108) as EvnPrescrProc_didDT,
				ED.EvnDirection_Num,
				IsNull(ED.PrehospDirect_id, EUP.PrehospDirect_id) as PrehospDirect_id,
				IsNull(ED.Lpu_sid, EUP.Lpu_did) AS Lpu_sid,
				IsNull(ED.LpuSection_id, EUP.LpuSection_did) as LpuSection_id,
				IsNull(ED.MedPersonal_id, EUP.MedPersonal_did) as MedPersonal_id,
				EFR.EvnFuncRequest_Ward as EvnLabRequest_Ward,
				ED.Org_sid,
				case when 2 = ISNULL(ED.EvnDirection_IsCito, 1) or isnull(EPP.EvnPrescrProc_IsCito, 1) = 2 then 'true' else 'false' end as EvnDirection_IsCito,
				IsNull(EFR.PayType_id, EUP.PayType_id) as PayType_id,
				EUP.UslugaComplex_id,
				EUP.EvnUslugaPar_id,
				CASE WHEN EUP.EvnUslugaPar_setDate IS NULL THEN 0 ELSE 1 END as disabled,
				EPP.EvnCourseProc_id,
				EPP.EvnPrescrProc_CountInDay,
				EPP.EvnPrescrProc_CourseDuration,
				EPP.EvnPrescrProc_ContReception,
				EPP.EvnPrescrProc_Interval,
				EPP.DurationType_id,
				EPP.DurationType_nid,
				EPP.DurationType_sid,
				EPP.EvnPrescrProc_id,
				EPP.EvnPrescrProc_Descr,
				EPP.PrescriptionStatusType_id,
				case when 2 = ISNULL(EPP.EvnPrescrProc_IsExec, 1) then 'true' else 'false' end as EvnPrescr_IsExec,
				ES.EvnStatus_SysNick
			FROM v_EvnDirection_all ED with (nolock)
				LEFT JOIN v_EvnFuncRequest EFR with (nolock) ON ED.EvnDirection_id = EFR.EvnFuncRequest_pid
				outer apply(
					select top 1 UslugaComplex_id, EvnUslugaPar_id, PayType_id, MedPersonal_did, LpuSection_did, Lpu_did, PrehospDirect_id, EvnUslugaPar_setDate from v_EvnUslugaPar EUPouter with (nolock) where ED.EvnDirection_id = EUPouter.EvnDirection_id
				) EUP
				outer apply(
					select top 1
						EvnPrescrProc_id,
						EC.EvnCourse_id as EvnCourseProc_id,
						EC.EvnCourse_MaxCountDay as EvnPrescrProc_CountInDay,
						EC.EvnCourse_Duration as EvnPrescrProc_CourseDuration, 
						EC.EvnCourse_ContReception as EvnPrescrProc_ContReception,
						ec.EvnCourse_Interval as EvnPrescrProc_Interval,
						ec.DurationType_id,
						ec.DurationType_intid as DurationType_sid,
						ec.DurationType_recid as DurationType_nid,
						EPPouter.EvnPrescrProc_IsCito,
						EPPouter.EvnPrescrProc_Descr,
						EPPouter.EvnPrescrProc_didDT,
						EPPouter.PrescriptionStatusType_id,
						EPPouter.EvnPrescrProc_IsExec
					from v_EvnPrescrProc EPPouter with (nolock)
					inner join v_EvnCourse EC with (nolock) on EPPouter.EvnCourse_id = EC.EvnCourse_id
					left join EvnPrescrDirection EPD with (nolock) on EPD.EvnPrescr_id = EPPouter.EvnPrescrProc_id
					where EPD.EvnDirection_id = ED.EvnDirection_id
				) EPP
				LEFT JOIN v_TimetableMedService_lite TTMS with (nolock) ON ED.EvnDirection_id = TTMS.EvnDirection_id
				LEFT JOIN v_EvnStatus ES (nolock) on ES.EvnStatus_id = EFR.EvnStatus_id
			WHERE
				(EvnFuncRequest_id = :EvnFuncRequest_id or :EvnFuncRequest_id is null)
				and (ED.EvnDirection_id = :EvnDirection_id)
		";

		$res = $this->db->query($query, [
			'EvnFuncRequest_id' => $data['EvnFuncRequest_id'],
			'EvnDirection_id' => $data['EvnDirection_id'
		]]);

		if (is_object($res) ) {
			return $res->result('array');
		}

		return false;
	}
	
	/**
	 * Возвращает данные заявки на исследование: МАРМ версия
	 */
	function mGetEvnProcRequest($data) {

		$result = $this->getFirstRowFromQuery("
			select top 1
				EFR.EvnFuncRequest_id,
				ED.EvnDirection_id,
				ED.EvnDirection_Num,
				ED.Person_id,
								
				convert(varchar(10), ED.EvnDirection_setDT, 104) as EvnDirection_setDT,
				IsNull(ED.PrehospDirect_id, EUP.PrehospDirect_id) as PrehospDirect_id,
				pd.PrehospDirect_Name,
				IsNull(ED.Lpu_id, EUP.Lpu_did) as Lpu_sid,
				ED.Org_sid,
				lpu.Lpu_Nick,
				IsNull(ED.LpuSection_id, EUP.LpuSection_did) as LpuSection_id,
				ls.LpuSection_Name,
				EFR.EvnFuncRequest_Ward as EvnLabRequest_Ward,
				IsNull(ED.MedPersonal_id, EUP.MedPersonal_did) as MedPersonal_id,
				mp.Person_Fio,
				case when 2 = ISNULL(ED.EvnDirection_IsCito, 1) then 1 else 2 end as EvnDirection_IsCito,
				IsNull(EFR.PayType_id, EUP.PayType_id) as PayType_id,
				
				EUP.EvnUslugaPar_id,
				EUP.UslugaComplex_id,
				EPP.EvnCourseProc_id,
				EPP.EvnPrescrProc_CountInDay,
				EPP.EvnPrescrProc_CourseDuration,
				EPP.EvnPrescrProc_ContReception,
				EPP.EvnPrescrProc_Interval,
				
				EPP.DurationType_id,
				EPP.DurationType_nid,
				EPP.DurationType_sid,
				
				EPP.EvnPrescrProc_id,
				EPP.DurationType_id,
				EPP.PrescriptionStatusType_id,
				convert(varchar(5), EPP.EvnPrescrProc_didDT, 108) as EvnPrescrProc_didDT,
				case when 2 = ISNULL(EPP.EvnPrescrProc_IsExec, 1) then 1 else 2 end as EvnPrescr_IsExec,
				EPP.EvnPrescrProc_Descr	
			FROM v_EvnDirection_all ED with (nolock)
				LEFT JOIN v_EvnFuncRequest EFR with (nolock) ON ED.EvnDirection_id = EFR.EvnFuncRequest_pid
				outer apply(
					select top 1
						 UslugaComplex_id, 
						 EvnUslugaPar_id, 
						 PayType_id, 
						 MedPersonal_did,
						 LpuSection_did, 
						 Lpu_did, 
						 PrehospDirect_id, 
						 EvnUslugaPar_setDate 
					 from v_EvnUslugaPar EUPouter with (nolock) 
					 where ED.EvnDirection_id = EUPouter.EvnDirection_id
				) EUP
				left join v_Lpu lpu (nolock) ON lpu.Lpu_id = IsNull(ED.Lpu_id, EUP.Lpu_did)
				left join v_LpuSection ls (nolock) ON ls.LpuSection_id = IsNull(ED.LpuSection_id, EUP.LpuSection_did)
				left join v_PrehospDirect pd (nolock) on pd.PrehospDirect_id = IsNull(ED.PrehospDirect_id, EUP.PrehospDirect_id)
				left join v_MedPersonal mp (nolock) on mp.MedPersonal_id = IsNull(ED.MedPersonal_id, EUP.MedPersonal_did)
				outer apply(
					select top 1
						EvnPrescrProc_id,
						EC.EvnCourse_id as EvnCourseProc_id,
						EC.EvnCourse_MaxCountDay as EvnPrescrProc_CountInDay,
						EC.EvnCourse_Duration as EvnPrescrProc_CourseDuration, 
						EC.EvnCourse_ContReception as EvnPrescrProc_ContReception,
						ec.EvnCourse_Interval as EvnPrescrProc_Interval,
						ec.DurationType_id,
						ec.DurationType_intid as DurationType_sid,
						ec.DurationType_recid as DurationType_nid,
						EPPouter.EvnPrescrProc_Descr,
						EPPouter.EvnPrescrProc_didDT,
						EPPouter.PrescriptionStatusType_id,
						EPPouter.EvnPrescrProc_IsExec
					from v_EvnPrescrProc EPPouter with (nolock)
					inner join v_EvnCourse EC with (nolock) on EPPouter.EvnCourse_id = EC.EvnCourse_id
					left join EvnPrescrDirection EPD with (nolock) on EPD.EvnPrescr_id = EPPouter.EvnPrescrProc_id
					where EPD.EvnDirection_id = ED.EvnDirection_id
				) EPP
				LEFT JOIN v_TimetableMedService_lite TTMS with (nolock) ON ED.EvnDirection_id = TTMS.EvnDirection_id
				LEFT JOIN v_EvnStatus ES (nolock) on ES.EvnStatus_id = EFR.EvnStatus_id
			WHERE
				(EvnFuncRequest_id = :EvnFuncRequest_id or :EvnFuncRequest_id is null)
				and (ED.EvnDirection_id = :EvnDirection_id)
		", array(
			'EvnFuncRequest_id' => $data['EvnFuncRequest_id'],
			'EvnDirection_id' => $data['EvnDirection_id']
		));

		return $result;
	}

	/**
	 * 
	 * @param type $data
	 * @return boolean
	 */
	function getEvnFuncRequest($data) {
		$query = "
			select
				EFR.EvnFuncRequest_id,
				ED.EvnDirection_id,
				TTMS.TimetableMedService_id,
				ED.Server_id,
				ED.Person_id,
				ED.PersonEvn_id,
				convert(varchar(10), ED.EvnDirection_setDT, 104) as EvnDirection_setDT,
				ED.EvnDirection_Num,
				IsNull(ED.PrehospDirect_id, EUP.PrehospDirect_id) as PrehospDirect_id,
				IsNull(ED.Lpu_sid, EUP.Lpu_did) AS Lpu_sid,
				IsNull(ED.LpuSection_id, EUP.LpuSection_did) as LpuSection_id,
				IsNull(ED.MedPersonal_id, EUP.MedPersonal_did) as MedPersonal_id,
				coalesce(ed.Org_sid, Lpu.Org_id) AS Org_sid,
				case when 2 = ISNULL(ED.EvnDirection_IsCito, 1) then 'true' else 'false' end as EvnDirection_IsCito,
				IsNull(EFR.PayType_id, EUP.PayType_id) as PayType_id,
				EUP.UslugaComplex_id,
				case when 2 = ISNULL(ECP.EvnCostPrint_IsNoPrint, 1) then 'true' else 'false' end as rejectionFlag,
				convert(varchar(10), ECP.EvnCostPrint_setDT, 104) as issueDate,
				EUP.EvnUslugaPar_id,
				CASE WHEN EvnUslugaPar_setDate IS NULL THEN 0 ELSE 1 END as disabled,
				(SELECT TOP 1 msf.MedStaffFact_id FROM v_MedStaffFact msf (nolock) WHERE ed.Post_id = msf.Post_id AND ed.MedPersonal_id = msf.MedPersonal_id and ed.LpuSection_id = msf.LpuSection_id) as MedStaffFact_id,
				EFR.Diag_id,
				edPidClass.EvnClass_SysNick as parentEvnClass_SysNick
			FROM v_EvnDirection_all ED with (nolock)
				LEFT JOIN v_EvnFuncRequest EFR with (nolock) ON ED.EvnDirection_id = EFR.EvnFuncRequest_pid
				LEFT JOIN v_EvnPrescrDirection EPD with (nolock) on EPD.EvnDirection_id = ED.EvnDirection_id
				LEFT JOIN v_EvnPrescrFuncDiag EPFD with (nolock) on EPFD.EvnPrescrFuncDiag_id = EPD.EvnPrescr_id
				LEFT JOIN v_EvnUslugaPar EUP with (nolock) on ED.EvnDirection_id = EUP.EvnDirection_id
				left join v_EvnCostPrint ECP with (nolock) on ECP.Evn_id = EUP.EvnUslugaPar_id
				left join v_Lpu Lpu (nolock) on Lpu.Lpu_id = coalesce(ed.Lpu_sid, EUP.Lpu_did)
				LEFT JOIN v_TimetableMedService_lite TTMS with (nolock) ON ED.EvnDirection_id = TTMS.EvnDirection_id
				left join v_Evn edPidClass with (nolock) on edPidClass.Evn_id = ED.EvnDirection_pid
			WHERE (EvnFuncRequest_id = :EvnFuncRequest_id or :EvnFuncRequest_id is null) and (ED.EvnDirection_id = :EvnDirection_id)
		";
		//echo getDebugSql($query, array('EvnFuncRequest_id' => $data['EvnFuncRequest_id'], 'EvnDirection_id' => $data['EvnDirection_id']));
		$db_resp = $this->db->query($query, array('EvnFuncRequest_id' => $data['EvnFuncRequest_id'], 'EvnDirection_id' => $data['EvnDirection_id']));
	
		if (is_object($db_resp)) {

			$result = $db_resp->result('array');

			if (count($result) > 0) {

				$response = array();
				$uslugaList = array();

			} else { return $result; }

			foreach($result as $key => $uslugaComplex) {

				if (!empty($uslugaComplex['UslugaComplex_id'])) {

					$uslugaList[$key]['UslugaComplex_id'] = $uslugaComplex['UslugaComplex_id'];
					$uslugaList[$key]['Evn_id'] = $uslugaComplex['EvnUslugaPar_id'];
					$uslugaList[$key]['rejectionFlag'] = $uslugaComplex['rejectionFlag'];
					$uslugaList[$key]['issueDate'] = $uslugaComplex['issueDate'];
					$uslugaList[$key]['disabled'] = $uslugaComplex['disabled'];

					// добавляем зубья связанные с услугой
					if ($uslugaComplex['parentEvnClass_SysNick'] == 'EvnVizitPLStom') {
						$this->load->model('EvnUsluga_model', 'eumodel');

						// возмьем данные по существующим зубам для события_услуги
						$resp = $this->eumodel->getToothNumEvnUsluga(
							array('EvnUsluga_id' => $uslugaComplex['EvnUslugaPar_id'])
						);

						if (!empty($resp[0]) && empty($response[0]['Error_Msg'])) {

							$uslugaList[$key]['ToothNums'] = '';
							foreach($resp as $toothNumber) {
								$uslugaList[$key]['ToothNums'] .= $toothNumber['ToothNumEvnUsluga_ToothNum'].',';
			}
							$uslugaList[$key]['ToothNums'] = rtrim($uslugaList[$key]['ToothNums'], ',');
			}
				}
			}
			}

			$response[0] = $result[0];
			$response[0]['EvnFuncRequest_uslugaList'] = $uslugaList;

			// убираем за ненадобностью, т.к. вся инфа в $uslugaList
			if (isset($response[0]['ToothNums'])) unset($response[0]['ToothNums']);
			if (isset($response[0]['EvnUsluga_id'])) unset($response[0]['EvnUsluga_id']);

			return $response;

		} else { return false; }
		}
	/**
	 * Создание/обновление заявки с направлением
	 * @param array $data
	 * @return boolean|array
	 */
	function saveEvnFuncRequest($data) {

		// если создаём заявку без бирки (приём без записи),
		// то создаём доп. бирку и заявку кидаем на неё.

		if (
			empty($data['EvnDirection_id'])
			&& empty($data['EvnFuncRequest_id'])
			&& empty($data['TimetableResource_id'])
			&& !empty($data['Resource_id'])
		) {
			$this->load->helper('Reg');
			$this->load->model('TimetableResource_model');

			$ttrdata = $this->TimetableResource_model->addTTRDop(
				array(
					'MedService_id' => $data['MedService_id'],
					'Resource_id' => $data['Resource_id'],
					'TimetableExtend_Descr' => null,
					'withoutRecord' => true,
					'ignoreTTRExist' => true,
					'pmUser_id' => $data['pmUser_id']
				)
			);

			if (!empty($ttrdata['TimetableResource_id'])) {
				$data['TimetableResource_id'] = $ttrdata['TimetableResource_id'];
			}
		}

		// направление надо сохранить, если направления ещё нет
		// или если не автоматическое и своя МО.

		$hasAccessSaveEvnDirection = false;

			if (!empty($data['EvnDirection_id'])) {

			$query = "
				select
					EvnDirection_IsAuto as EvnDirection_IsAuto,
					PrehospDirect_id
				from
					v_EvnDirection_all (nolock)
				where
					EvnDirection_id = :EvnDirection_id
			";

			$resp = $this->queryResult($query, array('EvnDirection_id' => $data['EvnDirection_id']));

			if (!empty($resp[0])
				&& $resp[0]['EvnDirection_IsAuto'] != 2
				&& $resp[0]['PrehospDirect_id'] == 1
			) { $hasAccessSaveEvnDirection = true; }
				}

		if (empty($data['EvnDirection_id']) || $hasAccessSaveEvnDirection) {
			$EvnDirectionData = $this->saveEvnDirection($data);
		} else {
			$EvnDirectionData = array('EvnDirection_id' => $data['EvnDirection_id']);
			}

		if ($EvnDirectionData) {

			$data['EvnDirection_id'] = $EvnDirectionData['EvnDirection_id'];

			// для 1 EvnDirection_id всегда должна быть 1 заявка EvnFuncRequest_id
			if (!empty($data['EvnDirection_id'])) {
				$data['EvnFuncRequest_id'] = $this->getFirstResultFromQuery('
					SELECT top 1
						EvnFuncRequest_id
				 	FROM v_EvnFuncRequest (nolock)
				 	WHERE EvnFuncRequest_pid = :EvnDirection_id
				 	',array('EvnDirection_id' => $data['EvnDirection_id'])
				);

				if (empty($data['EvnFuncRequest_id'])) {$data['EvnFuncRequest_id'] = null; }
			}

			$data['EvnStatus_id'] = null;
			$data['EvnFuncRequest_statusDate'] = null;

			if (!empty($data['EvnDirection_id'])) {

				$res = $this->db->query("
					select
						EvnStatus_id,
						convert(varchar, EvnFuncRequest_statusDate, 120) as EvnFuncRequest_statusDate
					from
						v_EvnFuncRequest (nolock)
					where
						EvnFuncRequest_pid = :EvnDirection_id
				", array('EvnDirection_id' => $data['EvnDirection_id'])
				);

				if (is_object($res)) {

					$resp = $res->result('array');

					if (!empty($resp[0])) {

						// статусы не должны сбрасываться
						$data['EvnStatus_id'] = $resp[0]['EvnStatus_id'];
						$data['EvnFuncRequest_statusDate'] = $resp[0]['EvnFuncRequest_statusDate'];
					}
				}
			}

			$action = (!empty($data['EvnFuncRequest_id']) ? 'upd' : 'ins' );

			if (empty($data['EvnFuncRequest_id'])
				&& empty($data['Diag_id'])
				&& !empty($data['EvnDirection_id'])
			) {
				$Diag_id = $this->getFirstResultFromQuery("
					SELECT top 1
						Diag_id
					FROM v_EvnDirection_all with(nolock)
					where Direction_id = :Direction_id
					", array('EvnDirection_id' => $data['EvnDirection_id'])
				);

				$data['Diag_id'] = (!empty($Diag_id) ? $Diag_id : null);
				}

			$sql = "
				declare
					@Evn_IsSigned bigint;

				set @Evn_IsSigned = (
					select top 1
						case 
							when Evn_IsSigned IS NOT NULL then 1
							else null
						end as Evn_IsSigned
					from
						v_Evn (nolock)
					where
						Evn_id = :EvnFuncRequest_id
				);
				
				DECLARE
					@EvnFuncRequest_id bigint = :EvnFuncRequest_id,
					@Error_Code bigint,
					@Error_Message varchar(4000);
				EXEC dbo.p_EvnFuncRequest_{$action}
					@EvnFuncRequest_id = @EvnFuncRequest_id output, 
					@EvnFuncRequest_pid = :EvnFuncRequest_pid,
					@EvnFuncRequest_setDT =  :EvnFuncRequest_setDT,
					@EvnFuncRequest_Ward =  :EvnFuncRequest_Ward,
					@Lpu_id = :Lpu_id,
					@MedService_id = :MedService_id,
					@Server_id = :Server_id, 
					@PersonEvn_id = :PersonEvn_id, 
					@PayType_id = :PayType_id,
					@Diag_id = :Diag_id,
					@EvnStatus_id = :EvnStatus_id,
					@EvnFuncRequest_statusDate = :EvnFuncRequest_statusDate,
					@EvnFuncRequest_IsSigned = @Evn_IsSigned,
					@StudyTarget_id = :StudyTarget_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output;
				SELECT
					@EvnFuncRequest_id as EvnFuncRequest_id,
					@Error_Code as Error_Code,
					@Error_Message as Error_Msg;
				";
		 
			$params = array(
				'EvnFuncRequest_id' => $data['EvnFuncRequest_id'],
				'EvnFuncRequest_pid' => $data['EvnDirection_id'],
				'EvnFuncRequest_setDT' => $data['EvnDirection_setDT'],
				'EvnFuncRequest_Ward' => $data['EvnFuncRequest_Ward'],
				'Lpu_id'  => $data['Lpu_id'],
				'MedService_id'  => $data['MedService_id'],
				'Server_id' => $data['Server_id'],
				'PersonEvn_id' => $data['PersonEvn_id'],
				'PayType_id' => $data['PayType_id'],
				'Diag_id' => $data['Diag_id'],
				'EvnStatus_id' => $data['EvnStatus_id'],
				'EvnFuncRequest_statusDate' => $data['EvnFuncRequest_statusDate'],
				'StudyTarget_id' => $data['StudyTarget_id'],
				'pmUser_id' => $data['pmUser_id']
			);

			//echo getDebugSQL($sql, $params);die;
			$res = $this->db->query($sql, $params);
			
			if (is_object($res)) {

				$result = $res->result('array');
				if (!empty($result[0]['EvnFuncRequest_id'])) {

					$data['EvnFuncRequest_id'] = $result[0]['EvnFuncRequest_id'];

					// обновляем вид оплаты в направлении, если оно с признаком "к себе"
					$query = "
						update
							EvnDirection with (rowlock)
						set
							PayType_id = :PayType_id
						where
							EvnDirection_id = :EvnDirection_id
							and EvnDirection_IsReceive = 2
					";

					$this->db->query($query, array(
						'PayType_id' => $data['PayType_id'],
						'EvnDirection_id' => $data['EvnDirection_id']
					));

					// обновляем вид оплаты в услугах
					$query = "
						update
							EvnUsluga with (rowlock)
						set
							PayType_id = :PayType_id
						where
							EvnDirection_id = :EvnDirection_id;
					";

					$this->db->query($query, array(
						'PayType_id' => $data['PayType_id'],
						'EvnDirection_id' => $data['EvnDirection_id']
					));

					//$trans_result = $res->result('array');
					//$trans_good = true;

					$this->load->model('TimetableMedService_model', 'TimetableMedService_model');

					// записываем
					if (!empty($data['TimetableResource_id'])) {
						$data['Evn_id'] = $result[0]['EvnFuncRequest_id'];
						$data['object'] = 'TimetableResource';
						$this->TimetableMedService_model->Apply($data);
					}
				}



			} else { return false; }

			// рефакторинг февраль 2018, по задаче с зубами
			$this->beginTransaction();

			if (!empty($data['uslugaData'])) $data['uslugaData'] = json_decode(toUTF($data['uslugaData']), true);
			foreach($data['uslugaData'] as $usluga) {

				$usluga = (object)($usluga);

				// если это завершенная услуга
				if ($usluga->completed) {

					$region_nick = (!empty($data['session']['region']['nick'])
						? $data['session']['region']['nick']
						: null
					);

					if (!empty($region_nick) && in_array($region_nick, array('kareliya','ekb'))) {

						$query = "
							select
								ECP.EvnCostPrint_id,
								ECP.Person_id,
								ECP.EvnCostPrint_Number,
								ECP.EvnCostPrint_Cost
						from v_EvnFuncRequest EFR (nolock)
						left join v_EvnUslugaPar EUP (nolock) on EUP.EvnDirection_id = EFR.EvnFuncRequest_pid
						left join v_EvnCostPrint ECP (nolock) on ECP.Evn_id = EvnUslugaPar_id
							where
								EFR.EvnFuncRequest_id = :EvnFuncRequest_id
						and ECP.Evn_id = :Evn_id
					";

						$db_query = $this->db->query($query,
							array(
						'EvnFuncRequest_id' => $data['EvnFuncRequest_id'],
								'Evn_id' => $usluga->Evn_id
							)
						);

						if (is_object($db_query)) {

							$res = $db_query->result('array');
							if (!empty($res) && !empty($res[0])) {

							$params = array(
									'EvnCostPrint_id'		=> $res[0]['EvnCostPrint_id'],
									'EvnCostPrint_Number'	=> $res[0]['EvnCostPrint_Number'],
									'CostPrint_Cost'		=> $res[0]['EvnCostPrint_Cost'],
									'Person_id'				=> $res[0]['Person_id'],
									'Evn_id'				=> $usluga->Evn_id,
									'CostPrint_setDT'		=> ConvertDateFormat(trim($usluga->issueDate)),
									'CostPrint_IsNoPrint'	=> ($usluga->rejectionFlag) ? 2 : 1,
								'pmUser_id'				=> $data['pmUser_id']
							);

							$query = "
								declare
									@Res bigint,
									@ErrCode int,
									@ErrMessage varchar(4000)

								set @Res = :EvnCostPrint_id;

								exec p_EvnCostPrint_upd
									@EvnCostPrint_id = @Res output,
									@Evn_id = :Evn_id,
									@Person_id = :Person_id,
									@EvnCostPrint_Number = :EvnCostPrint_Number,
									@EvnCostPrint_setDT = :CostPrint_setDT,
									@EvnCostPrint_IsNoPrint = :CostPrint_IsNoPrint,
									@EvnCostPrint_Cost = :CostPrint_Cost,
									@pmUser_id = :pmUser_id,
									@Error_Code = @ErrCode output,
									@Error_Message = @ErrMessage output;

								select @Res as EvnCostPrint_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
							";
							//echo getDebugSQL($query, $params);die;
							$this->db->query($query, $params);
						}
					}
				}
				
				} else {

					$res = null;
					if (!empty($usluga->Evn_id)) {
						// если эта услуга не завершена, удаляем событие услуги, отвязываем зубы
						$res = $this->delEvnFuncRequestUsluga(
							array(
								'EvnUslugaPar_id' => $usluga->Evn_id,
								'pmUser_id' => $data['pmUser_id'],
								'parentEvnClass_SysNick' => (!empty($data['parentEvnClass_SysNick']) ? $data['parentEvnClass_SysNick'] : null)
							)
						);
				}
						
					if (!empty($usluga->UslugaComplex_id)) {

						if (empty($res)) {
							$data = array_merge(
								array(
									'EvnRequest_id' => $data['EvnFuncRequest_id'],
									'UslugaComplex_id' => $usluga->UslugaComplex_id
								), $data
							);

							// создаем событие услуги заново, привязываем зубы (если есть)
							$res = $this->saveEvnFuncRequestUsluga($data);

							if (!empty($res) && !empty($res[0]) && empty($res[0]['Error_Msg'])) {
								if (!empty($data['parentEvnClass_SysNick']) && $data['parentEvnClass_SysNick'] == 'EvnVizitPLStom') {

									if (!empty($usluga->toothData) && !empty($res[0]['EvnUslugaPar_id'])) {

										$this->load->model('EvnUsluga_model', 'eumodel');
										foreach ($usluga->toothData as $toothNumber) {

											$toothUslugaAddResult = $this->eumodel->insToothNumEvnUsluga(array(
												'EvnUsluga_id' => $res[0]['EvnUslugaPar_id'],
												'pmUser_id' => $data['pmUser_id'],
												'ToothNumEvnUsluga_ToothNum' => $toothNumber
											));
						}
						}
					}

							} else { $result[0]['Error_Msg'] = $res[0]['Error_Msg']; break; }
						} else { $result[0]['Error_Msg'] = 'Ошибка удаления события услуги'; break; }
					}
					}
				}

			if (empty($result[0]['Error_Msg'])) {

				// рекэш списка услуг по заявке
				$this->ReCacheFuncRequestUslugaCache(array(
					'MedService_id' => $data['MedService_id'],
					'EvnFuncRequest_id' => $data['EvnFuncRequest_id'],
					'EvnDirection_id' => $data['EvnDirection_id'],
					'pmUser_id' => $data['pmUser_id']
				));
			
				$this->commitTransaction();

			} else { $this->rollbackTransaction(); }

			$result[0]['EvnDirection_id'] = $data['EvnDirection_id'];
			return $result;

		} else { return false; }
		}

	/**
	 * Рекэш списка услуг заявки
	 */
	function ReCacheFuncRequestUslugaCache($data) {
		$data['EvnFuncRequest_UslugaCache'] = $this->getEvnFuncRequestUslugaList($data);
	}

	/**
	 * Создание заявки без направления
	 * @param array $data
	 * @return boolean|array
	 */
	function addEvnFuncRequest($data) {
		if (empty($data['PayType_id'])) {
			$PayType_SysNick = 'oms';

			switch ( $data['session']['region']['nick'] ) {
				case 'by': $PayType_SysNick = 'besus'; break;
				case 'kz': $PayType_SysNick = 'Resp'; break;
			}

			$data['PayType_id'] = $this->getFirstResultFromQuery("
				select PayType_id from v_PayType with (nolock) where PayType_SysNick = '{$PayType_SysNick}'
			");
			if ($data['PayType_id'] === false) {
				return false;
			}
		}
		// сначала сохраняем услугу
		$data['EvnDirection_id'] = null;
		$res = $this->saveEvnFuncRequestUsluga($data);
		if(empty($res))
		{
			return false;
		}
		else if(!empty($res[0]) && !empty($res[0]['Error_Msg']))
		{
			return $res;
		}
		$EvnFuncRequest_pid = $res[0]['EvnUslugaPar_id'];

		$sql = "
			DECLARE
				@EvnFuncRequest_id bigint = :EvnFuncRequest_id,
				@Error_Code bigint,
				@Error_Message varchar(4000);
			EXEC dbo.p_EvnFuncRequest_ins
				@EvnFuncRequest_id = @EvnFuncRequest_id output,
				@EvnFuncRequest_pid = :EvnFuncRequest_pid,
				@EvnFuncRequest_setDT =  :EvnFuncRequest_setDT,
				@MedService_id = :MedService_id,
				@Lpu_id = :Lpu_id,
				@Server_id = :Server_id,
				@PersonEvn_id = :PersonEvn_id,
				@PayType_id = :PayType_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			SELECT
				@EvnFuncRequest_id as EvnFuncRequest_id,
				@Error_Code as Error_Code,
				@Error_Message as Error_Msg;
			";

		$params = array(
			'EvnFuncRequest_id' => null,
			'EvnFuncRequest_pid' => $EvnFuncRequest_pid,
			'EvnFuncRequest_setDT' => date('Y-m-d'),
			'MedService_id'  => $data['MedService_id'],
			'Lpu_id'  => $data['Lpu_id'],
			'Server_id' => $data['Server_id'],
			'PersonEvn_id' => $data['PersonEvn_id'],
			'PayType_id' => $data['PayType_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		$res = $this->db->query($sql, $params);
		if (is_object($res)) {
			$result = $res->result('array');
			if (!empty($result[0]['EvnFuncRequest_id'])) {
				$result[0]['EvnUslugaPar_id'] = $EvnFuncRequest_pid;

				// рекэш списка услуг по заявке
				$this->ReCacheFuncRequestUslugaCache(array(
					'MedService_id' => $data['MedService_id'],
					'EvnFuncRequest_id' => $result[0]['EvnFuncRequest_id'],
					'EvnUslugaPar_id' => $EvnFuncRequest_pid,
					'pmUser_id' => $data['pmUser_id']
				));

				return $result;
			}
		}

		return false;
	}

	/**
	 * Создание/обновление заявки на исследование
	 *
	 * @param array $input_data
	 * @return array|boolean
	 * @throws Exception
	 */
	function saveEvnProcRequest($input_data) {
		if (empty($input_data['UslugaComplex_id'])) {
			throw new Exception('Не указана услуга', 500);
		}

		$direction_data = $this->saveEvnDirection($input_data);

		if (!$direction_data) {
			throw new Exception('Ошибка сохранения направления', 500);
		}
		$EvnDirection_id = $direction_data['EvnDirection_id'];

		$data = array_merge($direction_data, $input_data);
		$data['EvnDirection_id'] = $EvnDirection_id;

		// Для одного EvnDirection_id всегда должна быть одна заявка EvnFuncRequest_id
		$data['EvnFuncRequest_id'] = $this->getFirstResultFromQuery('
				SELECT top 1 EvnFuncRequest_id FROM v_EvnFuncRequest (nolock) WHERE EvnFuncRequest_pid = :EvnDirection_id
			', ['EvnDirection_id' => $EvnDirection_id]
		);

		$result = $this->getFirstRowFromQuery("
			select top 1
				EvnStatus_id,
				convert(varchar, EvnFuncRequest_statusDate, 120) as EvnFuncRequest_statusDate
			from
				v_EvnFuncRequest (nolock)
			where
				EvnFuncRequest_pid = :EvnDirection_id
			", ['EvnDirection_id' => $EvnDirection_id]
		);

		$data['EvnStatus_id'] = null;
		$data['EvnFuncRequest_statusDate'] = null;
		if ($result) {
			// Статусы не должны сбрасываться
			$data['EvnStatus_id'] = $result['EvnStatus_id'];
			$data['EvnFuncRequest_statusDate'] = $result['EvnFuncRequest_statusDate'];
		}

		$procedure = !empty($data['EvnFuncRequest_id']) ? 'p_EvnFuncRequest_upd' : 'p_EvnFuncRequest_ins';
		$sql = "
			DECLARE
				@EvnFuncRequest_id bigint = :EvnFuncRequest_id,
				@Error_Code bigint,
				@Error_Message varchar(4000);
			EXEC dbo.{$procedure}
				@EvnFuncRequest_id = @EvnFuncRequest_id output, 
				@EvnFuncRequest_pid = :EvnFuncRequest_pid,
				@EvnFuncRequest_setDT =  :EvnFuncRequest_setDT,
				@MedService_id = :MedService_id,
				@Lpu_id = :Lpu_id,
				@Server_id = :Server_id, 
				@PersonEvn_id = :PersonEvn_id, 
				@PayType_id = :PayType_id,
				@EvnStatus_id = :EvnStatus_id,
				@EvnFuncRequest_statusDate = :EvnFuncRequest_statusDate,
				@EvnFuncRequest_Ward = :EvnFuncRequest_Ward,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			SELECT
				@EvnFuncRequest_id as EvnFuncRequest_id,
				@Error_Code as Error_Code,
				@Error_Message as Error_Msg;
			";

		$procedure_params = [
			'EvnFuncRequest_id' => !empty($data['EvnFuncRequest_id']) ? $data['EvnFuncRequest_id'] : null,
			'EvnFuncRequest_pid' => $EvnDirection_id,
			'EvnFuncRequest_setDT' => $data['EvnDirection_setDT'],
			'Lpu_id'  => $data['Lpu_id'],
			'Server_id' => $data['Server_id'],
			'MedService_id' => !empty($data['EvnFuncRequest_id']) ? $data['MedService_id'] : null,
			'PersonEvn_id' => $data['PersonEvn_id'],
			'PayType_id' => $data['PayType_id'],
			'EvnStatus_id' => $data['EvnStatus_id'],
			'EvnFuncRequest_statusDate' => $data['EvnFuncRequest_statusDate'],
			'EvnFuncRequest_Ward' => $data['EvnLabRequest_Ward'],
			'pmUser_id' => $data['pmUser_id']
		];

		$trans_result = $this->getFirstRowFromQuery($sql, $procedure_params);

		if (!$trans_result) {
			throw new Exception('Ошибка при сохранении заявки', 500);
		}
		if (!empty($trans_result['Error_Msg'])) {
			throw new Exception($trans_result['Error_Msg'], $trans_result['Error_Code']);
		}

		$EvnFuncRequest_id = $data['EvnFuncRequest_id'] = $trans_result['EvnFuncRequest_id'];

		if (!empty($data['TimetableResource_id'])) {
			$data['Evn_id'] = $EvnFuncRequest_id;
			$data['object'] = 'TimetableResource';
			$this->load->model('TimetableMedService_model');
			$this->TimetableMedService_model->Apply($data);
		}

		// Очищаем все услуги и сохраняем только одну.
		$this->_clearEvnFuncRequestUsluga(['EvnDirection_id' => $EvnDirection_id, 'pmUser_id' => $data['pmUser_id']], [], true);
		$uslugaSaved = $this->saveEvnFuncRequestUsluga(
			array_merge(['UslugaComplex_id' => $data['UslugaComplex_id']], $data)
		);
		if (!$uslugaSaved) {
			throw new Exception('Ошибка при сохранении услуги', 500);
		}
		if (!empty($uslugaSaved) && !empty($uslugaSaved['Error_Msg'])) {
			throw new Exception($uslugaSaved['Error_Msg'], $uslugaSaved['Error_Code']);
		}

		$EvnCourseProc_id = $this->_saveEvnCourseProc($data);
		$data['EvnCourseProc_id'] = $EvnCourseProc_id;

		$EvnPrescrProc_id = $this->_saveEnvPrescrProc($data);

		// Только одно направление для одного назначения
		$this->load->model('EvnPrescr_model');
		$evn_direction_res = $this->EvnPrescr_model->checkEvnPrescr(['EvnPrescr_id' => $EvnPrescrProc_id]);
		if (empty($evn_direction_res[0]['EvnDirection_id'])) {
			$this->EvnPrescr_model->directEvnPrescr([
				'EvnPrescr_id' => $EvnPrescrProc_id,
				'EvnDirection_id' => $EvnDirection_id,
				'pmUser_id' => $data['pmUser_id']
			]);
		}

		// Сохраняем выполнение по свяанному назначению
		$params = [
			'pmUser_id' => $data['pmUser_id'],
			'EvnDirection_id' => $EvnDirection_id,
			'EvnPrescrProc_Descr' => !empty($data['EvnPrescrProc_Descr']) ? $data['EvnPrescrProc_Descr'] : null,
		];
		if (isset($data['EvnPrescr_IsExec'])) {
			$params['EvnPrescr_IsExec'] = $data['EvnPrescr_IsExec'];
			$params ['Evn_didDT'] = !empty($data['EvnPrescrProc_didDT']) ? $data['EvnDirection_setDT'] . ' ' . $data['EvnPrescrProc_didDT'] : null;
		} else {
			$params['EvnPrescr_IsExec'] = 1;
		}
		$this->EvnPrescr_model->saveEvnPrescrIsExec($params);

		// Рекэш списка услуг по заявке
		$this->ReCacheFuncRequestUslugaCache([
			'MedService_id' => $data['MedService_id'],
			'EvnFuncRequest_id' => $EvnFuncRequest_id,
			'EvnDirection_id' => $EvnDirection_id,
			'pmUser_id' => $data['pmUser_id']
		]);

		$this->load->model('Evn_model');
		$this->Evn_model->updateEvnStatus([
			'Evn_id' => $EvnFuncRequest_id,
			'EvnStatus_SysNick' => 'FuncDonerec',
			'EvnClass_SysNick' => 'EvnFuncRequest',
			'pmUser_id' => $data['pmUser_id']
		]);
		$this->Evn_model->updateEvnStatus([
			'Evn_id' => $EvnDirection_id,
			'EvnStatus_SysNick' => 'Serviced',
			'EvnClass_SysNick' => 'EvnDirection',
			'pmUser_id' => $data['pmUser_id']
		]);

		return $trans_result;
	}

	/**
	 * Создание/обновление заявки на исследование
	 */
	function mSaveEvnProcRequest($input_data) {

		$this->load->model('Common_model');
		$personEvnData = $this->Common_model->loadPersonDataForApi(array('Person_id' => $input_data['Person_id']));

		if (!empty($personEvnData[0])) $personEvnData = $personEvnData[0];
		if (empty($personEvnData) || !empty($personEvnData) && empty($personEvnData['PersonEvn_id'])) {
			$this->response(array(
					'success' => false,
					'error_code' => 6,
					'Error_Msg' => (!empty($personEvnData['Error_Msg'])
						? $personEvnData['Error_Msg']
						: 'Ошибка при получении периодики пациента')
				)
			);
		}

		$input_data['PersonEvn_id'] = $personEvnData['PersonEvn_id'];
		$input_data['Server_id'] = $personEvnData['Server_id'];

		$input_data['MedPersonal_id'] = $input_data['session']['medpersonal_id'];

		$direction_data = $this->saveEvnDirection($input_data);
		$EvnDirection_id = $direction_data['EvnDirection_id'];

		if (!$EvnDirection_id) {
			throw new Exception('Ошибка сохранения направления', 500);
		}

		$data = array_merge($direction_data, $input_data);
		$data['EvnDirection_id'] = $EvnDirection_id;

		// Для одного EvnDirection_id всегда должна быть одна заявка EvnFuncRequest_id
		$data['EvnFuncRequest_id'] = $this->getFirstResultFromQuery('
				SELECT top 1 EvnFuncRequest_id FROM v_EvnFuncRequest (nolock) WHERE EvnFuncRequest_pid = :EvnDirection_id
			', ['EvnDirection_id' => $EvnDirection_id]
		);

		$result = $this->getFirstRowFromQuery("
			select top 1
				EvnStatus_id,
				convert(varchar, EvnFuncRequest_statusDate, 120) as EvnFuncRequest_statusDate
			from
				v_EvnFuncRequest (nolock)
			where
				EvnFuncRequest_pid = :EvnDirection_id
			", ['EvnDirection_id' => $EvnDirection_id]
		);

			if (is_object($result)) {
				$resp = $result->result('array');
				if (!empty($resp[0])) {
					// статусы не должны сбрасываться
					$data['EvnStatus_id'] = $resp[0]['EvnStatus_id'];
					$data['EvnFuncRequest_statusDate'] = $resp[0]['EvnFuncRequest_statusDate'];
				}
			}

		$procedure = !empty($data['EvnFuncRequest_id']) ? 'p_EvnFuncRequest_upd' : 'p_EvnFuncRequest_ins';
		$sql = "
			DECLARE
				@EvnFuncRequest_id bigint = :EvnFuncRequest_id,
				@Error_Code bigint,
				@Error_Message varchar(4000);
			EXEC dbo.{$procedure}
				@EvnFuncRequest_id = @EvnFuncRequest_id output, 
				@EvnFuncRequest_pid = :EvnFuncRequest_pid,
				@EvnFuncRequest_setDT =  :EvnFuncRequest_setDT,
				@MedService_id = :MedService_id,
				@Lpu_id = :Lpu_id,
				@Server_id = :Server_id, 
				@PersonEvn_id = :PersonEvn_id, 
				@PayType_id = :PayType_id,
				@EvnStatus_id = :EvnStatus_id,
				@EvnFuncRequest_statusDate = :EvnFuncRequest_statusDate,
				@EvnFuncRequest_Ward = :EvnFuncRequest_Ward,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			SELECT
				@EvnFuncRequest_id as EvnFuncRequest_id,
				@Error_Code as Error_Code,
				@Error_Message as Error_Msg;
			";

		$procedure_params = [
			'EvnFuncRequest_id' => !empty($data['EvnFuncRequest_id']) ? $data['EvnFuncRequest_id'] : null,
			'EvnFuncRequest_pid' => $EvnDirection_id,
			'EvnFuncRequest_setDT' => $data['EvnDirection_setDT'],
			'Lpu_id'  => $data['Lpu_id'],
			'Server_id' => $data['Server_id'],
			'MedService_id' => !empty($data['EvnFuncRequest_id']) ? $data['MedService_id'] : null,
			'PersonEvn_id' => $data['PersonEvn_id'],
			'PayType_id' => $data['PayType_id'],
			'EvnStatus_id' => $data['EvnStatus_id'],
			'EvnFuncRequest_statusDate' => $data['EvnFuncRequest_statusDate'],
			'EvnFuncRequest_Ward' => $data['EvnLabRequest_Ward'],
			'pmUser_id' => $data['pmUser_id']
		];

		$trans_result = $this->getFirstRowFromQuery($sql, $procedure_params);

		if (!$trans_result) {
			throw new Exception('Ошибка при сохранении заявки', 500);
		}
		if (!empty($trans_result['Error_Msg'])) {
			throw new Exception($trans_result['Error_Msg'], $trans_result['Error_Code']);
		}

		$EvnFuncRequest_id = $data['EvnFuncRequest_id'] = $trans_result['EvnFuncRequest_id'];

		if (!empty($data['TimetableResource_id'])) {
			$data['Evn_id'] = $EvnFuncRequest_id;
			$data['object'] = 'TimetableResource';
			$this->load->model('TimetableMedService_model');
			$this->TimetableMedService_model->Apply($data);
		}

		// Очищаем все услуги и сохраняем только одну.
		$this->_clearEvnFuncRequestUsluga(['EvnDirection_id' => $EvnDirection_id, 'pmUser_id' => $data['pmUser_id']], [], true);
		$uslugaSaved = $this->saveEvnFuncRequestUsluga(
			array_merge(['UslugaComplex_id' => $data['UslugaComplex_id']], $data)
		);
		if (!$uslugaSaved) {
			throw new Exception('Ошибка при сохранении услуги', 500);
		}
		if (!empty($uslugaSaved) && !empty($uslugaSaved['Error_Msg'])) {
			throw new Exception($uslugaSaved['Error_Msg'], $uslugaSaved['Error_Code']);
		}

		$EvnCourseProc_id = $this->_saveEvnCourseProc($data);
		$data['EvnCourseProc_id'] = $EvnCourseProc_id;

		$EvnPrescrProc_id = $this->_saveEnvPrescrProc($data);

		// Только одно направление для одного назначения
		$this->load->model('EvnPrescr_model');
		$evn_direction_res = $this->EvnPrescr_model->checkEvnPrescr(['EvnPrescr_id' => $EvnPrescrProc_id]);
		if (empty($evn_direction_res[0]['EvnDirection_id'])) {
			$this->EvnPrescr_model->directEvnPrescr([
				'EvnPrescr_id' => $EvnPrescrProc_id,
				'EvnDirection_id' => $EvnDirection_id,
				'pmUser_id' => $data['pmUser_id']
			]);
		}

		// Сохраняем выполнение по свяанному назначению
		$params = [
			'pmUser_id' => $data['pmUser_id'],
			'EvnDirection_id' => $EvnDirection_id,
			'EvnPrescrProc_Descr' => !empty($data['EvnPrescrProc_Descr']) ? $data['EvnPrescrProc_Descr'] : null,
		];
		if (isset($data['EvnPrescr_IsExec'])) {
			$params['EvnPrescr_IsExec'] = $data['EvnPrescr_IsExec'];
			$params ['Evn_didDT'] = !empty($data['EvnPrescrProc_didDT']) ? $data['EvnDirection_setDT'] . ' ' . $data['EvnPrescrProc_didDT'] : null;
		} else {
			$params['EvnPrescr_IsExec'] = 1;
		}
		$this->EvnPrescr_model->saveEvnPrescrIsExec($params);

		// Рекэш списка услуг по заявке
		$this->ReCacheFuncRequestUslugaCache([
			'MedService_id' => $data['MedService_id'],
			'EvnFuncRequest_id' => $EvnFuncRequest_id,
			'EvnDirection_id' => $EvnDirection_id,
			'pmUser_id' => $data['pmUser_id']
		]);

		$this->load->model('Evn_model');
		$this->Evn_model->updateEvnStatus([
			'Evn_id' => $EvnFuncRequest_id,
			'EvnStatus_SysNick' => 'FuncDonerec',
			'EvnClass_SysNick' => 'EvnFuncRequest',
			'pmUser_id' => $data['pmUser_id']
		]);
		$this->Evn_model->updateEvnStatus([
			'Evn_id' => $EvnDirection_id,
			'EvnStatus_SysNick' => 'Serviced',
			'EvnClass_SysNick' => 'EvnDirection',
			'pmUser_id' => $data['pmUser_id']
		]);

		return $trans_result;
	}


	/**
	 * 
	 * @param array $data
	 * @return array|boolean
	 */
    private function saveEvnDirection($data) {
		$this->load->model('EvnDirection_model', 'EvnDirection_model');

		// если создаём заявку из АРМ, значит направление к себе.
		$data['EvnDirection_IsReceive'] = 2;
		if (!empty($data['EvnDirection_id'])) {
			// признак "К себе" оставляем как был.
			$resp = $this->queryResult("
				select top 1
					EvnDirection_id,
					EvnDirection_pid,
					EvnDirection_IsReceive,
					DirType_id,
					Diag_id
				from
					v_EvnDirection_all (nolock)
				where
					EvnDirection_id = :EvnDirection_id
			", array(
				'EvnDirection_id' => $data['EvnDirection_id']
			));

			if (!empty($resp[0]['EvnDirection_id'])) {
				$data['EvnDirection_IsReceive'] = $resp[0]['EvnDirection_IsReceive'];
				$data['EvnDirection_pid'] = $resp[0]['EvnDirection_pid'];
				$data['Diag_id'] = $resp[0]['Diag_id'];
				$data['DirType_id'] = $resp[0]['DirType_id'];
			}
		}

		$params = array(
			'EvnDirection_id' => $data['EvnDirection_id'],
			'EvnDirection_pid' => !empty($data['EvnDirection_pid'])?$data['EvnDirection_pid']:null,
			'toQueue' => empty($data['TimetableResource_id'])?true:null,
			'Server_id' => $data['Server_id'],
			'PersonEvn_id' => $data['PersonEvn_id'],
			'EvnDirection_Num' => $data['EvnDirection_Num'],
			'TimetableResource_id' => !empty($data['TimetableResource_id'])?$data['TimetableResource_id']:null,
			'PrehospDirect_id' => $data['PrehospDirect_id'],
			'EvnDirection_setDT' => $data['EvnDirection_setDT'],
			'MedService_id' => $data['MedService_id'],
			'Resource_id' => !empty($data['Resource_id'])?$data['Resource_id']:null,
			'EvnDirection_IsCito' => $data['EvnDirection_IsCito'],
			'Lpu_id'  => $data['Lpu_id'],//ЛПУ, создавшее направление
			'Lpu_did' => $data['Lpu_id'],//ЛПУ, куда был направлен пациент
			'DirType_id' => !empty($data['DirType_id'])?$data['DirType_id']:10,//тип направления: "На исследование"
			'EvnDirection_IsAuto' => 2,//Это системное направление, т.к. электронное направление может создать только врач
			'EvnDirection_IsReceive' => $data['EvnDirection_IsReceive'],
			'Diag_id' => $data['Diag_id'],
			'LpuSection_id' => null,//Направившее отделение
			'MedPersonal_id' => !empty($data['MedPersonal_id'])?$data['MedPersonal_id']:null,//Направивший врач
			'From_MedStaffFact_id' => null,//Направивший врач
			'Lpu_sid' => null,//Направившее ЛПУ
			'Org_sid' => null,//Направившая организация
			'pmUser_id' => $data['pmUser_id']
		);

		if (!empty($data['EvnDirection_id'])) {
			// ссылку на очередь и на бирку берём существующие.
			$sql = "
				select
					ed.TimetableResource_id,
					ed.TimetableResource_id,
					ed.EvnQueue_id
				from
					v_EvnDirection_all ed (nolock)
				where
					ed.EvnDirection_id = :EvnDirection_id
			";
			$res = $this->db->query($sql, $data);
			if (is_object($res)) {
				$tmp = $res->result('array');
			}
			if (count($tmp)>0) {
				$params['TimetableResource_id'] = $tmp[0]['TimetableResource_id'];
				$params['TimetableResource_id'] = $tmp[0]['TimetableResource_id'];
				$params['EvnQueue_id'] = $tmp[0]['EvnQueue_id'];
				$params['onlySaveDirection'] = true;
			}
		}

		// Кем направлен:
		if($this->getRegionNick() == 'kz') {
			switch ((int)$data['PrehospDirect_id']) {
				case 8: // 1 Отделение ЛПУ
				case 15: // 1 Отделение ЛПУ
					$params['Lpu_sid'] = $data['Lpu_id']; //Направившее ЛПУ
					$params['LpuSection_id'] = $data['LpuSection_id'];
					$params['From_MedStaffFact_id'] = $data['MedStaffFact_id'];
					$params['Org_sid'] = $data['Org_sid'];
					break;
				case 9: // 2 Другое ЛПУ
				case 11: // 2 Другое ЛПУ
				case 16: // 2 Другое ЛПУ
					$params['Lpu_sid'] = $data['Lpu_sid'];//Направившее ЛПУ
					$params['LpuSection_id'] = $data['LpuSection_id'];
					$params['From_MedStaffFact_id'] = $data['MedStaffFact_id'];
					$params['Org_sid'] = $data['Org_sid'];
					break;
				case 10: // 3 Другая организация
				case 12: // 4 Военкомат
				case 13: // 5 Скорая помощь
				case 14: // 6 Администрация
					$params['Org_sid'] = $data['Org_sid'];
					break;
				default:
					$params['Org_sid'] = $data['Org_sid'];
					break;
			}
		} else {
			switch ((int)$data['PrehospDirect_id']) {
				case 1: // 1 Отделение ЛПУ
					$params['Lpu_sid'] = $data['Lpu_id']; //Направившее ЛПУ
					$params['LpuSection_id'] = $data['LpuSection_id'];
					$params['From_MedStaffFact_id'] = $data['MedStaffFact_id'];
					break;
				case 2: // 2 Другое ЛПУ
					$params['Lpu_sid'] = $data['Lpu_sid'];//Направившее ЛПУ
					$params['LpuSection_id'] = $data['LpuSection_id'];
					$params['From_MedStaffFact_id'] = $data['MedStaffFact_id'];
					break;
				case 3: // 3 Другая организация
				case 4: // 4 Военкомат
				case 5: // 5 Скорая помощь
				case 6: // 6 Администрация
					$params['Org_sid'] = $data['Org_sid'];
					break;
				case 7: // 7 Пункт помощи на дому
					break;
			}
		}

		$tmp = array('LpuSectionProfile_id', 'Diag_id', 'EvnDirection_Descr', 'LpuSection_did', 'MedPersonal_zid', 'EvnUsluga_id', 'EvnQueue_id', 'EvnDirection_pid', 'MedPersonal_id');
		foreach ($tmp as $k) {
			if (!array_key_exists($k,$params)) {
				$params[$k] = null;
			}
		}

		// Диагноз оставляем тот, что был в направлении, только если это не первое создание заявки.
		if (!empty($data['EvnFuncRequest_id']) && !empty($params['EvnDirection_id'])) {
			$query = "
				select
					Diag_id
				from
					v_EvnDirection_all (nolock)
				where
					EvnDirection_id = :EvnDirection_id
			";
			$res = $this->db->query($query, $params);

			if ( is_object($res) ) {
				$resp = $res->result('array');
				if (count($resp) > 0) {
					$params['Diag_id'] = $resp[0]['Diag_id'];
				}
			}
		}

		$resultDir = $this->EvnDirection_model->saveEvnDirection($params);
		if (is_array($resultDir)) {
			if (!empty($resultDir[0]['EvnDirection_id'])) {
				$params['EvnDirection_id']=$resultDir[0]['EvnDirection_id'];
				return $params;
			}
		}

		return false;
    }

	/**
	 * Очищает список услуг
	 *
	 * @param array $data
	 * @param array $evnuslugalist
	 * @param boolean $clearall
	 * @return boolean
	 */
	private function _clearEvnFuncRequestUsluga($data, $evnuslugalist, $clearall) {
		$result = $this->queryResult("select EvnUslugaPar_id from v_EvnUslugaPar with (nolock) where EvnDirection_id = :EvnDirection_id",
			['EvnDirection_id' => $data['EvnDirection_id']]
		);

		if (is_array($result)) {
			if (empty($result)) {
				return array(array('Error_Msg' => null));
			}
			foreach ($result as $row) {
				$row['pmUser_id'] = $data['pmUser_id'];
				$query = "
						declare
							@ErrCode int,
							@ErrMessage varchar(4000);
						exec p_EvnUslugaPar_del
							@EvnUslugaPar_id = :EvnUslugaPar_id,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output;

						select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
					";
				if ($clearall || in_array($row['EvnUslugaPar_id'], $evnuslugalist)) {
					$result = $this->db->query($query, $row);
				}
			}
			return array(array('Error_Msg' => null));
		}

		return false;
	}

	/**
	 * 
	 * @param array $data
	 * @return array|boolean
	 */
	function saveEvnFuncRequestUsluga($data) {
		$query = "
			declare
				@EvnUslugaPar_id bigint,
				@EvnPrescr_id bigint,
				@EvnUslugaPar_pid bigint,
				@ErrCode int,
				@ErrMessage varchar(4000),
				@dt datetime = null;--dbo.tzGetDate();
			set @EvnUslugaPar_id = :EvnUslugaPar_id;
			set @EvnPrescr_id = (select top 1 EvnPrescr_id from v_EvnPrescrDirection (nolock) where EvnDirection_id = :EvnDirection_id);
			set @EvnUslugaPar_pid = (
				select top 1
					e_child.Evn_id
				from
					v_EvnPrescr ep (nolock)
					inner join v_Evn e (nolock) on e.Evn_id = EvnPrescr_pid -- посещние/движение
					inner join v_Evn e_child (nolock) on e_child.Evn_pid = e.Evn_pid -- посещения/движения той же КВС/ТАП
				where
					EvnPrescr_id = @EvnPrescr_id and e_child.Evn_setDT <= @dt and (e_child.Evn_disDT >= @dt OR e_child.Evn_disDT IS NULL) -- актуальное
			);
			exec p_EvnUslugaPar_ins
				@EvnUslugaPar_id = @EvnUslugaPar_id output,
				@EvnUslugaPar_pid = @EvnUslugaPar_pid,
                @Lpu_id = :Lpu_id,
				@Server_id = :Server_id, 
				@PersonEvn_id = :PersonEvn_id, 
				@UslugaPlace_id = :UslugaPlace_id,
				@EvnUslugaPar_Kolvo = 1,
				@PayType_id = :PayType_id,
				@UslugaComplex_id = :UslugaComplex_id,
				@pmUser_id = :pmUser_id,
				@EvnDirection_id = :EvnDirection_id,
				@EvnPrescrTimetable_id = null,
				@EvnPrescr_id = @EvnPrescr_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @EvnUslugaPar_id as EvnUslugaPar_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'EvnUslugaPar_id' => NULL,
			'Lpu_id' => $data['Lpu_id'],
			'Server_id' => $data['Server_id'],
			'UslugaPlace_id' => 1,
			'PersonEvn_id' => $data['PersonEvn_id'],
			'PayType_id' => $data['PayType_id'],
			'EvnDirection_id' => $data['EvnDirection_id'],
			'UslugaComplex_id' => $data['UslugaComplex_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		return $this->getFirstRowFromQuery($query, $queryParams);
	}
	/**
	 * 
	 * @param type $data
	 * @return boolean
	 */
	function loadEvnUslugaEditForm($data) {
		$query = "
			select
				EUP.EvnUslugaPar_id,
				ED.EvnDirection_id,
				EUP.Lpu_id,
				EUP.PayType_id,
				EUP.Org_uid,
				convert(varchar(10), EUP.EvnUslugaPar_setDT, 104) as EvnUslugaPar_setDate,
				EUP.EvnUslugaPar_setTime,
				EUP.LpuSection_uid,
				EUP.MedStaffFact_id,
				EUP.MedPersonal_id as MedPersonal_uid,
				EUP.MedPersonal_sid,
				EUP.Server_id,
				EUP.Person_id,
				EUP.PersonEvn_id,
				EUP.UslugaComplex_id,
				EUP.EvnUslugaPar_Regime,
				EUP.EvnUslugaPar_Comment
			FROM v_EvnUslugaPar EUP with (nolock)
				LEFT JOIN v_EvnDirection_all ED with (nolock) ON ED.EvnDirection_id = EUP.EvnDirection_id
			WHERE EvnUslugaPar_id = :EvnUslugaPar_id
		";

		$res = $this->db->query($query, array('EvnUslugaPar_id' => $data['EvnUslugaPar_id']));

		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}	
		
	}
	
	/**
	 * Сохранение результата выполнения услуги
	 *
	 * @param array $data
	 * @return boolean
	 */
	public function saveEvnUslugaEditForm( $data ){
		if ( !empty( $data[ 'AssociatedResearches' ] ) ) {
			// Из Pacs сведения могут прийти в любой кодировке, поэтому приводим к UTF
			$AssociatedResearchesArray = json_decode( toUTF( $data[ 'AssociatedResearches' ] ), true );
			if ( json_last_error() !== 0 ) {
				return array( array( 'success' => false, 'Error_Msg' => 'Проблемы с кодировкой при прикреплении исследований. Обратитесь к разработчикам' ) );
			}
		}

		if ( !empty($data['EvnUslugaPar_setTime']) ) {
			$data['EvnUslugaPar_setDate'] .= ' ' . $data['EvnUslugaPar_setTime'] . ':00.000';
		}
		
		if (empty($data['EvnUslugaPar_Regime'])) {
			$data['EvnUslugaPar_Regime'] = 1;
		}

		if (!empty($data['EvnUslugaPar_setDate'])) {
			// проверяем что рабочее место врача на дату выполнения услуги открыто.
			$MedStaffFact_id = $this->getFirstResultFromQuery("
				select top 1
					MedStaffFact_id
				from
					v_MedStaffFact (nolock)
				where
					MedStaffFact_id = :MedStaffFact_id
					and WorkData_begDate <= :EvnUslugaPar_setDT
					and (WorkData_endDate >= :EvnUslugaPar_setDT OR WorkData_endDate IS NULL)
			", array(
				'MedStaffFact_id' => $data['MedStaffFact_id'],
				'EvnUslugaPar_setDT' => $data['EvnUslugaPar_setDate']
			));
			if (empty($MedStaffFact_id)) {
				return array('Error_Msg' => 'Период работы врача не соответствует дате выполнения услуги');
			}
		}
		
		if ( $data[ 'EvnUslugaPar_Regime' ] == 1 ) {
			if ( !isset( $AssociatedResearchesArray ) || !is_array( $AssociatedResearchesArray ) || !sizeof( $AssociatedResearchesArray ) ) {
				return array( array( 'success' => false, 'Error_Msg' => 'Необходимо прикрепить хотя бы одно исследование. Для продолжения сохранения, без прикрепления исследований, выберите аналоговый режим и заполните протокол.' ) );
			}
		}

		if (!empty($data['EvnUslugaPar_setDate'])) {
			// проверяем что рабочее место врача на дату выполнения услуги открыто.
			$MedStaffFact_id = $this->getFirstResultFromQuery("
				select top 1
					MedStaffFact_id
				from
					v_MedStaffFact (nolock)
				where
					MedStaffFact_id = :MedStaffFact_id
					and WorkData_begDate <= :EvnUslugaPar_setDT
					and (WorkData_endDate >= :EvnUslugaPar_setDT OR WorkData_endDate IS NULL)
			", array(
				'MedStaffFact_id' => $data['MedStaffFact_id'],
				'EvnUslugaPar_setDT' => $data['EvnUslugaPar_setDate']
			));
			if (empty($MedStaffFact_id)) {
				return array('Error_Msg' => 'Период работы врача не соответствует дате выполнения услуги');
			}
		}
		
		$query = "
			declare
				@EvnUslugaPar_id bigint,
				@EvnPrescr_id bigint,
				@EvnUslugaPar_pid bigint,
				@ErrCode int,
				@ErrMessage varchar(4000),
				@dt datetime = :EvnUslugaPar_setDT;
			set @EvnPrescr_id = (select top 1 EvnPrescr_id from v_EvnPrescrDirection (nolock) where EvnDirection_id = :EvnDirection_id);
			set @EvnUslugaPar_pid = (
				select top 1
					case
						when
							ec.EvnClass_SysNick = 'EvnSection'
						then
							isnull(e_child.Evn_id, e.Evn_pid)
						else
							null
					end
				from
					v_EvnPrescr ep (nolock)
					inner join v_Evn e (nolock) on e.Evn_id = EvnPrescr_pid -- посещние/движение
					inner join v_EvnClass ec (nolock) on ec.EvnClass_id = e.EvnClass_id
					left join v_Evn e_child (nolock) on e_child.Evn_pid = e.Evn_pid  and e_child.Evn_setDT <= @dt and (e_child.Evn_disDT >= @dt OR e_child.Evn_disDT IS NULL) -- актуальное посещение/движение той же КВС/ТАП
				where
					ep.EvnPrescr_id = @EvnPrescr_id
			);
			set @EvnUslugaPar_id = :EvnUslugaPar_id;
			exec p_EvnUslugaPar_upd
				@EvnUslugaPar_id = @EvnUslugaPar_id output,
				@EvnUslugaPar_pid = @EvnUslugaPar_pid,
                @Lpu_id = :Lpu_id,
				@Server_id = :Server_id, 
				@PersonEvn_id = :PersonEvn_id, 
				@EvnUslugaPar_setDT = :EvnUslugaPar_setDT,
				@UslugaComplex_id = :UslugaComplex_id,
				@UslugaPlace_id = :UslugaPlace_id,
				@EvnUslugaPar_Kolvo = 1,
				@PayType_id = :PayType_id,
				@Org_uid = :Org_uid,
				@LpuSection_uid = :LpuSection_uid,
				@MedStaffFact_id = :MedStaffFact_id,
				@MedPersonal_id = :MedPersonal_uid,
				@MedPersonal_sid = :MedPersonal_sid,
				@EvnUslugaPar_Regime = :EvnUslugaPar_Regime,
				@EvnUslugaPar_Comment = :EvnUslugaPar_Comment,
				@pmUser_id = :pmUser_id,
				@EvnDirection_id = :EvnDirection_id,
				@EvnPrescrTimetable_id = null,
				@EvnPrescr_id = @EvnPrescr_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @EvnUslugaPar_id as EvnUslugaPar_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'EvnUslugaPar_id' => $data['EvnUslugaPar_id'],
			'Lpu_id'  => $data['Lpu_id'],
            'Server_id' => $data['Server_id'],
            'PersonEvn_id' => $data['PersonEvn_id'],
			'EvnUslugaPar_setDT' => $data['EvnUslugaPar_setDate'],
			'UslugaComplex_id' => $data['UslugaComplex_id'],
            'UslugaPlace_id' => 1,
			'PayType_id' => $data['PayType_id'],
			'EvnDirection_id' => $data['EvnDirection_id'],
			'Org_uid' => $data['Org_uid'],
			'LpuSection_uid' => $data['LpuSection_uid'],
			'MedStaffFact_id' => $data['MedStaffFact_id'],
			'MedPersonal_uid' => $data['MedPersonal_uid'],
			'MedPersonal_sid' => $data['MedPersonal_sid'],
			'EvnUslugaPar_Regime' => $data['EvnUslugaPar_Regime'],
			'EvnUslugaPar_Comment' => $data['EvnUslugaPar_Comment'],
			'pmUser_id' => $data['pmUser_id']
		);
		
		$result = $this->db->query($query, $queryParams);
		
		if ( is_object($result) ) {
			// кэшируем статус заявки
			$this->ReCacheFuncRequestStatus(array(
				'EvnDirection_id' => $data['EvnDirection_id'],
				'EvnUslugaPar_id' => $data['EvnUslugaPar_id'],
				'pmUser_id' => $data['pmUser_id']
			));
			
			// сохраняем выполнение по свяанному назначению
			if (!empty($data['EvnDirection_id'])) {
				$this->load->model('EvnPrescr_model', 'EvnPrescr_model');
				$this->EvnPrescr_model->saveEvnPrescrIsExec(array(
					'pmUser_id' => $data['pmUser_id'],
					'EvnDirection_id' => $data['EvnDirection_id'],
					'EvnPrescr_IsExec' => 2
				));
			}
			
			$saveEvnUslugaParResult = $result->result('array');
			
			if ( isset( $AssociatedResearchesArray ) ) {

				$this->load->model( 'Dicom_model', 'dcm_model' );

				// https://redmine.swan.perm.ru/issues/61002
				// Удаляем исследования если были переданы данные от клиента
				// через $data['AssociatedResearches'] в любом случае, чтобы
				// была возможность сохранять услугу без прикепленного исследования
				// Если такой массив не был передан, считаем что форма сохраняется
				// без возможности повлиять на исследования, поэтому эта часть внутри условия
				$removeResult = $this->dcm_model->removeAssociatedResearches( array(
					'EvnUslugaPar_id' => $data[ 'EvnUslugaPar_id' ]
				) );
				
				if ( is_array( $AssociatedResearchesArray ) && sizeof( $AssociatedResearchesArray ) > 0 ) {
					if ( !empty( $removeResult ) && ($removeResult[ 0 ][ 'Error_Msg' ] != '') ) {
						return $removeResult;
					} else {
						$count = 1;
						foreach( $AssociatedResearchesArray as $AssociatedResearch ){
							//Временный костыль
							if ( $count == 1 ) {
								$temporaryAssociateResult = $this->dcm_model->addStudyToEvnUslugaPar( array(
									'EvnUslugaPar_id' => $saveEvnUslugaParResult[ 0 ][ 'EvnUslugaPar_id' ],
									'study_uid' => $AssociatedResearch[ 'study_uid' ],
									'pmUser_id' => $data[ 'pmUser_id' ]
								) );
								if ( !empty( $temporaryAssociateResult ) && ($temporaryAssociateResult[ 0 ][ 'Error_Msg' ] != '') ) {
									return $temporaryAssociateResult;
								}
							}

							$associateResult = $this->dcm_model->AssociateResearcheWithEvnUslugaPar( array(
								'study_uid' => $AssociatedResearch[ 'study_uid' ],
								'study_date' => $AssociatedResearch[ 'study_date' ],
								'study_time' => ((array_key_exists( 'study_time', $AssociatedResearch )) ? $AssociatedResearch[ 'study_time' ] : ''),
								'patient_name' => $AssociatedResearch[ 'patient_name' ],
								'LpuEquipmentPacs_id' => $AssociatedResearch[ 'LpuEquipmentPacs_id' ],
								'EvnUslugaPar_id' => $saveEvnUslugaParResult[ 0 ][ 'EvnUslugaPar_id' ],
								'pmUser_id' => $data[ 'pmUser_id' ]
							) );
							if ( !empty( $associateResult ) && ($associateResult[ 0 ][ 'Error_Msg' ] != '') ) {
								return $associateResult;
							}
							$count++;
						}
					}
				}
			}

			return $saveEvnUslugaParResult;
		}
		else {
			return false;
		}	

	}
	
	/**
	 * Отправка исследования в центр удаленной консультации
	 */
	function sendUslugaParToRCC($data) {
		
		$queryParams = array();
		
		if (!$data||empty($data['EvnUslugaPar_id'])) {
			return array(array('success'=>false,'Error_Msg'=>'Отсутствует Идентификатор заявки на функциональную диагностику'));
		} else {
			$queryParams['EvnUslugaPar_id'] = $data['EvnUslugaPar_id'];
		}
		
		if (!$data||(!isset($data['session']['CurMedService_id'])&&empty($data['MedService_lid']))) {
			return array(array('success'=>false,'Error_Msg'=>'Отсутствует Идентификатор службы ФД'));
		} else {
			$queryParams['MedService_lid'] = (isset($data['session']['CurMedService_id']))?$data['session']['CurMedService_id']:$data['MedService_lid'];
		}
		
		if (!$data||empty($data['pmUser_id'])) {
			return array(array('success'=>false,'Error_Msg'=>'Отсутствует идентификатор пользователя'));
		} else {
			$queryParams['pmUser_id'] = $data['pmUser_id'];
		}
		
		$queryParams['RemoteConsultCenterResearch_status'] = (!$data||empty($data['RemoteConsultCenterResearch_status']))?null:$data['RemoteConsultCenterResearch_status'];
		$queryParams['RemoteConsultCenterResearch_id'] = (!$data||empty($data['RemoteConsultCenterResearch_id']))?null:$data['RemoteConsultCenterResearch_id'];
		$procedure = (!$data||empty($data['RemoteConsultCenterResearch_id']))?'p_RemoteConsultCenterResearch_ins':'p_RemoteConsultCenterResearch_ins';
		
		$query = "
				declare
					@Res bigint,
					@ErrCode int,
					@ErrMessage varchar(4000)
				set @Res = :RemoteConsultCenterResearch_id;
				exec {$procedure}
					@RemoteConsultCenterResearch_id = @Res output,
					@EvnUslugaPar_id = :EvnUslugaPar_id,
					@MedService_lid = :MedService_lid,
					@RemoteConsultCenterResearch_status = :RemoteConsultCenterResearch_status,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;

				select @Res as UnformalizedAddressDirectory_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
		
		$res = $this->db->query($query, $queryParams);

		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}	
		
		return true;
	}
	
	/**
	 * 
	 * Загрузка грида в АРМ Центра удалённой конслуьтации
	 */
	function loadRemoteConsultCenterResearchList($data) {
		
		$filter = '(1 = 1)';
		$queryParams = array();
		if ( !empty($data['begDate']) ) {
			$filter .= " and cast(RCCR.RemoteConsultCenterResearch_insDT as date) >= :begDate";
			$queryParams['begDate'] = $data['begDate'];
		} else {
			return $this->createError('', 'Не указана дата начала периода');
		}

		if ( !empty($data['endDate']) ) {
			$filter .= " and cast(RCCR.RemoteConsultCenterResearch_insDT as date) <= :endDate";
			$queryParams['endDate'] = $data['endDate'];
		} else {
			return $this->createError('', 'Не указана дата конца периода');
		}
		
		if (!$data||(!isset($data['session']['CurMedService_id'])&&empty($data['MedService_id']))) {
			return $this->createError('', 'Отсутствует Идентификатор службы ЦУК');
		} else {
			$queryParams['MedService_id'] = (isset($data['session']['CurMedService_id']))?$data['session']['CurMedService_id']:$data['MedService_id'];
		}
		
		
		$query = "
			SELECT
				RCCR.RemoteConsultCenterResearch_id,
				RCCR.RemoteConsultCenterResearch_status,
				ED.EvnDirection_Num,
				convert(varchar(10), ED.EvnDirection_setDT, 104) as EvnDirection_setDT,
				(PS.Person_SurName + ' ' + ISNULL(PS.Person_FirName, '') + ' ' + ISNULL(PS.Person_SecName,'')) as Person_FIO,
				U.UslugaComplex_Name	
			FROM
				v_RemoteConsultCenterResearch RCCR with (nolock)
				LEFT JOIN v_EvnUslugaPar EUP with (nolock) on EUP.EvnUslugaPar_id = RCCR.EvnUslugaPar_id
				LEFT JOIN v_EvnDirection_all ED with (nolock) on EUP.EvnDirection_id = ED.EvnDirection_id
				LEFT JOIN v_PersonState PS with (nolock) on PS.Person_id = ED.Person_id
				LEFT JOIN v_UslugaComplex U with (nolock) on EUP.UslugaComplex_id = U.UslugaComplex_id
				LEFT JOIN v_MedServiceLink MSL with (nolock) on MSL.MedService_lid = RCCR.MedService_lid
				
			WHERE
				{$filter}
				and MSL.MedService_id = :MedService_id
			";
				
		$res = $this->db->query($query, $queryParams);

		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}	

		return true;

	}

	/**
	 * Сохраняет курс
	 *
	 * @param array $data
	 * @return int
	 * @throws Exception
	 */
	private function _saveEvnCourseProc($data) {
		$procedure = !empty($data['EvnCourseProc_id']) ? 'p_EvnCourseProc_upd' : 'p_EvnCourseProc_ins';

		$result = $this->getFirstRowFromQuery("
			declare
				@Res bigint,
				@pmUser_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @Res = :EvnCourseProc_id;

			exec dbo.{$procedure}
				@EvnCourseProc_id = @Res output,
				@EvnCourseProc_pid = :EvnCourseProc_pid,
				@Lpu_id = :Lpu_id,
				@Server_id = :Server_id,
				@PersonEvn_id = :PersonEvn_id,
				@EvnCourseProc_setDT = :EvnCourseProc_setDT,
				@MedPersonal_id = :MedPersonal_id,
				@LpuSection_id = :LpuSection_id,
				@CourseType_id = :CourseType_id,
				@EvnCourseProc_MinCountDay = :EvnCourseProc_CountInDay,
				@EvnCourseProc_MaxCountDay = :EvnCourseProc_CountInDay,
				@EvnCourseProc_ContReception = :EvnCourseProc_ContReception,
				@DurationType_recid = :DurationType_recid,
				@EvnCourseProc_Interval = :EvnCourseProc_Interval,
				@DurationType_intid = :DurationType_intid,
				@EvnCourseProc_Duration = :EvnCourseProc_Duration,
				@DurationType_id = :DurationType_id,
				@ResultDesease_id = :ResultDesease_id,
				@UslugaComplex_id = :UslugaComplex_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as EvnCourseProc_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			", [
			'EvnCourseProc_id' => empty($data['EvnCourseProc_id']) ? null : $data['EvnCourseProc_id'],
			'EvnCourseProc_pid' => $data['EvnFuncRequest_id'],
			'Lpu_id' => empty($data['Lpu_sid']) ? $_SESSION['lpu_id'] : $data['Lpu_sid'],
			'Server_id' => $data['Server_id'],
			'PersonEvn_id' => $data['PersonEvn_id'],
			'EvnCourseProc_setDT' => empty($data['EvnDirection_setDT']) ? null : $data['EvnDirection_setDT'],
			'MedPersonal_id' => empty($data['MedPersonal_id']) ? null : $data['MedPersonal_id'],
			'LpuSection_id' => null,
			'CourseType_id' => 2,
			'EvnCourseProc_CountInDay' => empty($data['EvnPrescrProc_CountInDay']) ? 1 : $data['EvnPrescrProc_CountInDay'],
			'EvnCourseProc_ContReception' => empty($data['EvnPrescrProc_ContReception']) ? 1 : $data['EvnPrescrProc_ContReception'],
			'DurationType_recid' => empty($data['DurationType_nid']) ? 1 : $data['DurationType_nid'],
			'EvnCourseProc_Interval' => empty($data['EvnPrescrProc_Interval']) ? 0 : $data['EvnPrescrProc_Interval'],
			'DurationType_intid' => empty($data['DurationType_sid']) ? 1 : $data['DurationType_sid'],
			'EvnCourseProc_Duration' => empty($data['EvnPrescrProc_CourseDuration']) ? 1 : $data['EvnPrescrProc_CourseDuration'],
			'DurationType_id' => empty($data['DurationType_id']) ? 1 : $data['DurationType_id'],
			'ResultDesease_id' => empty($data['ResultDesease_id']) ? null : $data['ResultDesease_id'],
			'UslugaComplex_id' => $data['UslugaComplex_id'],
			'pmUser_id' => $data['pmUser_id'],
		]);

		if ($result) {
			if (!empty($result['Error_Msg'])) {
				throw new Exception($result['Error_Msg'], $result['Error_Code']);
			}
			return $result['EvnCourseProc_id'];
		} else {
			throw new Exception('Ошибка при сохранении курса', 500);
		}
	}

	/**
	 * Сохраняет назначение
	 *
	 * @param array $data
	 * @return int
	 * @throws Exception
	 */
	private function _saveEnvPrescrProc($data) {
		$resp_ep = $this->queryResult("
			select
				ep.EvnPrescr_id,
				ep.EvnPrescr_pid
			from
				v_EvnPrescr ep (nolock)
				inner join v_EvnPrescrDirection epd (nolock) on ep.EvnPrescr_id = epd.EvnPrescr_id
			where
				epd.EvnDirection_id = :EvnDirection_id
			", $data
		);

		$procedure = !empty($resp_ep[0]['EvnPrescr_id']) ? 'p_EvnPrescrProc_upd' : 'p_EvnPrescrProc_ins';

		$result = $this->getFirstRowFromQuery("
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @Res = :EvnPrescrProc_id;

			exec dbo.{$procedure}
				@EvnPrescrProc_id = @Res output,
				@EvnPrescrProc_pid = :EvnPrescrProc_pid,
				@EvnCourse_id = :EvnCourse_id,
				@PrescriptionType_id = :PrescriptionType_id,
				@PrescriptionStatusType_id = :PrescriptionStatusType_id,
				@Lpu_id = :Lpu_id,
				@Server_id = :Server_id,
				@PersonEvn_id = :PersonEvn_id,
				@UslugaComplex_id = :UslugaComplex_id,
				@EvnPrescrProc_setDT = :EvnPrescrProc_setDT,
				@EvnPrescrProc_IsCito = :EvnPrescrProc_IsCito,
				@EvnPrescrProc_Descr = :EvnPrescrProc_Descr,
				@pmUser_id = :pmUser_id,
				@EvnPrescrProc_IsExec = :EvnPrescrProc_IsExec,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as EvnPrescrProc_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			", [
			'EvnPrescrProc_id' => !empty($resp_ep[0]['EvnPrescr_id']) ? $resp_ep[0]['EvnPrescr_id'] : null,
			'EvnPrescrProc_pid' => !empty($resp_ep[0]['EvnPrescr_pid']) ? $resp_ep[0]['EvnPrescr_pid'] : $data['EvnFuncRequest_id'],
			'EvnCourse_id' => $data['EvnCourseProc_id'],
			'PrescriptionType_id' => 6,
			'PrescriptionStatusType_id' => !empty($data['PrescriptionStatusType_id'] ? $data['PrescriptionStatusType_id'] : 1),
			'Lpu_id' => empty($data['Lpu_sid']) ? $_SESSION['lpu_id'] : $data['Lpu_sid'],
			'Server_id' => $data['Server_id'],
			'PersonEvn_id' => $data['PersonEvn_id'],
			'UslugaComplex_id' => $data['UslugaComplex_id'],
			'EvnPrescrProc_setDT' => $data['EvnDirection_setDT'],
			'EvnPrescrProc_IsCito' => $data['EvnDirection_IsCito'],
			'EvnPrescrProc_Descr' => $data['EvnPrescrProc_Descr'],
			'pmUser_id' => $data['pmUser_id'],
			'EvnPrescrProc_IsExec' => $data['EvnPrescr_IsExec'],
			'EvnPrescr_IsCito' => !empty($data['EvnDirection_IsCito'] ? $data['EvnDirection_IsCito'] : 1),
			'EvnPrescr_Descr' => !empty($data['EvnPrescrProc_Descr'] ? $data['EvnPrescrProc_Descr'] : null),
		]);

		if ($result) {
			if (!empty($result['Error_Msg'])) {
				throw new Exception($result['Error_Msg'], $result['Error_Code']);
			}
			return $result['EvnPrescrProc_id'];
		} else {
			throw new Exception('Ошибка при сохранении назначения', 500);
		}
	}

}
