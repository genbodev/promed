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
 * @property EvnDirection_model EvnDirection_model
 * @property Kz_UslugaMedType_model $UslugaMedType_model
 *
 *
*/
class EvnFuncRequest_model extends swPgModel
{
	/**
	 * constructor
	 */
	function __construct()
	{
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
					U.UslugaComplex_Name as \"UslugaComplex_Name\",
					EUP.EvnUslugaPar_id as \"EvnUslugaPar_id\",
					EUP.Person_id as \"Person_id\",
					to_char(EUP.EvnUslugaPar_setDate, 'dd.mm.yyyy') as \"EvnUslugaPar_setDate\",
					ex.EvnXml_IsSigned as \"EvnXml_IsSigned\"
				from 
					v_EvnUslugaPar EUP
					left join v_UslugaComplex U on EUP.UslugaComplex_id = U.UslugaComplex_id
					left join v_EvnXml ex on ex.Evn_id = eup.EvnUslugaPar_id
				where 
					EUP.EvnDirection_id = :EvnDirection_id
				order by EUP.EvnUslugaPar_id desc
			";
		} else if ( !empty($data['EvnUslugaPar_id']) ) {
			$queryList[] = "
				select
					U.UslugaComplex_Name as \"UslugaComplex_Name\",
					EUP.EvnUslugaPar_id as \"EvnUslugaPar_id\",
					EUP.Person_id as \"Person_id\",
					to_char(EUP.EvnUslugaPar_setDate, 'dd.mm.yyyy') as \"EvnUslugaPar_setDate\",
					ex.EvnXml_IsSigned as \"EvnXml_IsSigned\"
				from 
					v_EvnUslugaPar EUP
					left join v_UslugaComplex U on EUP.UslugaComplex_id = U.UslugaComplex_id
					left join v_EvnXml ex on ex.Evn_id = eup.EvnUslugaPar_id
				where 
					EUP.EvnUslugaPar_id = :EvnUslugaPar_id
				limit 1
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
						EvnFuncRequest
					set
						EvnFuncRequest_UslugaCache = :EvnFuncRequest_UslugaCache
					where
						Evn_id = :EvnFuncRequest_id
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
				v_EvnUslugaPar eup
				inner join v_EvnFuncRequest efr on efr.EvnFuncRequest_pid = eup.EvnDirection_id
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
				eup.EvnUslugaPar_id as \"EvnUslugaPar_id\",
				efr.EvnFuncRequest_id as \"EvnFuncRequest_id\",
				epd.EvnPrescr_id as \"EvnPrescr_id\",
				eup.EvnDirection_id as \"EvnDirection_id\",
				ttr.TimetableResource_IsDop as \"TimetableResource_IsDop\",
				eup.EvnUslugaPar_pid as \"EvnUslugaPar_pid\",
				e.EvnClass_SysNick as \"EvnClass_SysNick\"
			from
				v_EvnUslugaPar eup
				left join v_EvnFuncRequest efr on efr.EvnFuncRequest_pid = eup.EvnDirection_id
				left join v_TimetableResource_lite ttr on ttr.EvnDirection_id = eup.EvnDirection_id
				left join v_Evn e on e.Evn_id = eup.EvnUslugaPar_pid
				left join lateral(
					select EvnPrescr_id
					from v_EvnPrescrDirection
					where EvnDirection_id = eup.EvnDirection_id
				) epd on true
			where
				EvnUslugaPar_id = :EvnUslugaPar_id
			limit 1
		";
		$resp = $this->queryResult($query, array(
			'EvnUslugaPar_id' => $data['EvnUslugaPar_id']
		));

		if (is_array($resp) && !empty($resp[0]['EvnUslugaPar_id'])) {
			$this->beginTransaction();
			// отменяем выполнение
			$query = "
				update
					Evn
				set
					Evn_setDT = null
				where
					Evn_id = :EvnUslugaPar_id;

				update
					EvnUsluga
				set
					MedPersonal_id = null,
					UslugaPlace_id = null,
					MedStaffFact_id = null,
					LpuSection_uid = null
				where
					Evn_id = :EvnUslugaPar_id;
			";
			$this->db->query($query, array(
				'EvnUslugaPar_id' => $resp[0]['EvnUslugaPar_id']
			));
			if ($resp[0]['TimetableResource_IsDop'] == 1) {
				$this->load->model('EvnDirectionAll_model', 'edm');
				$this->edm->returnToQueue(array(
					'EvnDirection_id' => $resp[0]['EvnDirection_id'],
					'session' => $data['session'],
					'pmUser_id' => $data['pmUser_id']
				));
			}
			// рекэшируем
			$this->ReCacheFuncRequestStatus(array(
				'EvnFuncRequest_id' => $resp[0]['EvnFuncRequest_id'],
				'EvnDirection_id' => $resp[0]['EvnDirection_id'],
				'EvnUslugaPar_id' => $resp[0]['EvnUslugaPar_id'],
				'pmUser_id' => $data['pmUser_id']
			));

			$this->commitTransaction();

			if (in_array(getRegionNick(), array('perm', 'kaluga', 'ufa', 'astra')) && !empty($resp[0]['EvnUslugaPar_pid']) && $resp[0]['EvnClass_SysNick'] == 'EvnSection') {
				$this->load->model('EvnSection_model');
				$this->EvnSection_model->recalcKSGKPGKOEF($resp[0]['EvnUslugaPar_pid'], $data['session']);
			}
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
			select
				d.pmUser_insID as \"pmUser_insID\",
				d.TimetableResource_id as \"TimetableResource_id\",
				d.EvnQueue_id as \"EvnQueue_id\",
				ttr.TimetableResource_IsDop as \"TimetableResource_IsDop\",
				efr.EvnFuncRequest_id as \"EvnFuncRequest_id\",
				d.EvnDirection_Num as \"EvnDirection_Num\",
				ms.MedService_Name as \"MedService_Name\",
				(select DirFailType_Name from v_DirFailType where DirFailType_id = :DirFailType_id) as \"DirFailType_Name\",
				coalesce(PS.Person_SurName, '') || ' ' || coalesce(PS.Person_FirName, '') || ' ' || coalesce(PS.Person_SecName, '') as \"Person_Fio\",
				l.Lpu_Nick as \"Lpu_Nick\",
				ls.LpuSection_Name as \"LpuSection_Name\"
			from
				v_EvnDirection_all d
				left join v_TimetableResource_lite ttr on ttr.TimetableResource_id = d.TimetableResource_id
				left join v_EvnFuncRequest efr on efr.EvnFuncRequest_pid = d.EvnDirection_id
				left join v_PersonState ps on ps.Person_id = d.Person_id
				left join v_MedService ms on ms.MedService_id = d.MedService_id
				left join v_Lpu l on l.Lpu_id = ms.Lpu_id
				left join v_LpuSection ls on ls.LpuSection_id = ms.LpuSection_id
			where
				d.EvnDirection_id = :EvnDirection_id
			limit 1
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

		$TimetableResource_id_for_del = null;
		switch (true) {
			case (!empty($directionData[0]['TimetableResource_id'])):
				$data['TimetableResource_id'] = $directionData[0]['TimetableResource_id'];
				if ($directionData[0]['TimetableResource_IsDop'] == 1) {
					$TimetableResource_id_for_del = $data['TimetableResource_id'];
				} else {
					$this->load->model('TimetableGraf_model', 'TimetableGraf_model');
					$data['object'] = 'TimetableResource';
					$data['cancelType'] = 'decline';
					$tmp = $this->TimetableGraf_model->Clear($data);
					if (!$tmp['success']) {
						throw new Exception($tmp['Error_Msg'], 500);
					}
				}
				break;
			case (!empty($directionData[0]['EvnQueue_id'])):
				$data['EvnQueue_id'] = $directionData[0]['EvnQueue_id'];
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
				break;
		}

		$params = array(
			'pmUser_id' => $data['pmUser_id'],
			'TimetableResource_id' => $directionData[0]['TimetableResource_id'],
			'DirFailType_id' => $data['DirFailType_id'],
			'EvnComment_Comment' => $data['EvnComment_Comment'],
			'EvnDirection_id' => $data['EvnDirection_id']
		);

		// 3. отмена направления
		$this->queryResult("
			update EvnDirection
			set
				DirFailType_id = :DirFailType_id,
				EvnDirection_failDT = dbo.tzGetDate(),
				pmUser_failID = :pmUser_id,
				TimetableGraf_id = null,
				TimetableStac_id = null,
				TimetableResource_id = null,
				TimetablePar_id = null
			where Evn_id = :EvnDirection_id
		", $params);

		$res = $this->getFirstResultFromQuery("
			select EvnComment_id as \"EvnComment_id\"
			from v_EvnComment
			where Evn_id = :EvnDirection_id
			limit 1
		", $params);

		if ($res) {
			$params['EvnComment_id'] = $res;
			$this->queryResult("
				update dbo.EvnComment
				set EvnComment_Comment = :EvnComment_Comment,
					pmUser_updID = :pmUser_id,
					EvnComment_updDT = dbo.tzGetDate()
				where Evn_id =:EvnComment_id
			", $params);
		} else {
			$this->queryResult("
				insert into dbo.EvnComment
					(Evn_id, EvnComment_Comment, pmUser_insID, pmUser_updID, EvnComment_insDT, EvnComment_updDT)
					values (:EvnDirection_id, :EvnComment_Comment, :pmUser_id, :pmUser_id, dbo.tzGetDate(), dbo.tzGetDate())
			", $params);
		}

		if ($params['TimetableResource_id']) {
			$res = $this->queryResult("
				select
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from p_TimetableResource_cancel(
					TimetableResource_id := :TimetableResource_id,
					pmUser_id := :pmUser_id
				)
			", $params);

			if (!$this->isSuccessful($res)) {
				$this->rollbackTransaction();
				return $res;
			}
		}

		$res = $this->queryResult("
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from dbo.p_EvnDirection_del(
				EvnDirection_id := :EvnDirection_id,
				pmUser_id := :pmUser_id
			)
		", $params);

		if (!$this->isSuccessful($res)) {
			$this->rollbackTransaction();
			return false;
		} else {
			if (strlen($data['EvnComment_Comment']) > 2048) {
				$data['EvnComment_Comment'] = substr($data['EvnComment_Comment'], 0, 2048);
			}

			if (!empty($TimetableResource_id_for_del)) {
				$tmp = $this->execCommonSP('p_TimetableResource_del', array(
					'pmUser_id' => $data['pmUser_id'],
					'TimetableResource_id' => $TimetableResource_id_for_del
				), 'array_assoc');
				if (empty($tmp)) {
					throw new Exception('Ошибка запроса к БД', 500);
				}
				if (isset($tmp['Error_Msg'])) {
					throw new Exception($tmp['Error_Msg'], 500);
				}
			}

			$noticeData = array(
				'autotype' => 1
			, 'User_rid' => $directionData[0]['pmUser_insID']
			, 'pmUser_id' => $data['pmUser_id']
			, 'type' => 1
			, 'title' => 'Отмена направления'
			, 'text' => 'Направление №' . $directionData[0]['EvnDirection_Num'] . ' (' . $directionData[0]['Person_Fio'] . ') в лабораторию ' . $directionData[0]['MedService_Name'] . ' (' . $directionData[0]['Lpu_Nick'] . ', ' . $directionData[0]['LpuSection_Name'] . ') отменено по причине ' . $directionData[0]['DirFailType_Name'] . '. ' . $data['EvnComment_Comment']
			);
			$this->load->model('Messages_model', 'Messages_model');
			$noticeResponse = $this->Messages_model->autoMessage($noticeData);

			$this->commitTransaction();
			return $res;
		}
	}

	/**
	 * Удаление услуг по заявке
	 */
	function delEvnUslugaParByEvnFuncRequest($data) {
		// получаем услуги
		$query = "
			select
				EUP.EvnUslugaPar_id as \"EvnUslugaPar_id\"
			from
				v_EvnUslugaPar EUP
				inner join v_EvnFuncRequest EFR on EFR.EvnFuncRequest_pid = EUP.EvnDirection_id
			where
				EFR.EvnFuncRequest_id = :EvnFuncRequest_id

			union all

			select
				EUP.EvnUslugaPar_id as \"EvnUslugaPar_id\"
			from
				v_EvnUslugaPar EUP
				inner join v_EvnFuncRequest EFR on EFR.EvnFuncRequest_pid = EUP.EvnUslugaPar_id
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
					select
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Msg\"
					from  dbo.p_EvnUslugaPar_del(
						EvnUslugaPar_id := :EvnUslugaPar_id,
						pmUser_id := :pmUser_id
					)
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
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from  dbo.p_EvnFuncRequest_del(
				EvnFuncRequest_id := :EvnFuncRequest_id,
				pmUser_id := :pmUser_id
			)
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
	 * @param type $data
	 * @return boolean
	 */
	function getEvnFuncRequestWithAssociatedResearches($data) {
		if ( !array_key_exists( 'Person_id', $data ) || !$data['Person_id'] ) {
			return array( array( 'Error_Msg' => 'Не указан идентификатор пациента.' ) );
		}
		
		$query = "
			SELECT
				EUP.EvnUslugaPar_id as \"EvnUslugaPar_id\",
				to_char(ED.EvnDirection_setDT, 'dd.mm.yyyy') as \"EvnDirection_setDT\",
				Usluga.UslugaComplex_Name as \"UslugaComplex_Name\",
				EUPAR.EvnUslugaParAssociatedResearches_id as \"EvnUslugaParAssociatedResearches_id\"
			FROM
				v_EvnUslugaPar EUP
				left join v_EvnDirection_all ED on ED.EvnDirection_id = EUP.EvnDirection_id
				left join lateral(
					select
						EUPARO.EvnUslugaParAssociatedResearches_id
					from
						v_EvnUslugaParAssociatedResearches as EUPARO
					where
						EUPARO.EvnUslugaPar_id =  EUP.EvnUslugaPar_id
					limit 1
				) as EUPAR on true
				left join lateral(
					select
						U.UslugaComplex_Name,
						EUPO.EvnUslugaPar_id
					from
						v_EvnUslugaPar EUPO
						left join v_UslugaComplex U on EUPO.UslugaComplex_id = U.UslugaComplex_id
					where 
						EUPO.EvnUslugaPar_id = EUP.EvnUslugaPar_id
					limit 1
				) as Usluga on true
			where
				EUP.Person_id = :Person_id AND
				coalesce(EUPAR.EvnUslugaParAssociatedResearches_id,0) != 0
			";
		
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
			$data['EvnFuncRequest_id'] = $this->getFirstResultFromQuery('SELECT EvnFuncRequest_id as "EvnFuncRequest_id" FROM v_EvnFuncRequest WHERE EvnFuncRequest_pid = :EvnDirection_id limit 1',array('EvnDirection_id' => $data['EvnDirection_id']));

			// рекэшируем
			$data['EvnFuncRequest_UslugaCache'] = $this->getEvnFuncRequestUslugaList($data);
		} else if (!empty($data['EvnUslugaPar_id'])) {
			$data['EvnFuncRequest_id'] = $this->getFirstResultFromQuery('SELECT EvnFuncRequest_id as "EvnFuncRequest_id" FROM v_EvnFuncRequest WHERE EvnFuncRequest_pid = :EvnUslugaPar_id limit 1',array('EvnUslugaPar_id' => $data['EvnUslugaPar_id']));

			// рекэшируем
			$data['EvnFuncRequest_UslugaCache'] = $this->getEvnFuncRequestUslugaList($data);
		}

		if (empty($data['EvnFuncRequest_id'])) {
			return false;
		}

		$query = "
			select
				es.EvnStatus_SysNick as \"EvnStatus_SysNick\",
				ed.EvnDirection_id as \"EvnDirection_id\",
				TTR.TimetableResource_id as \"TimetableResource_id\",
				eupar.EvnUslugaPar_id as \"EvnUslugaPar_id\",
				ep.EvnPrescr_id as \"EvnPrescr_id\",
				ep.EvnPrescr_IsExec as \"EvnPrescr_IsExec\"
			from
				v_EvnFuncRequest efr
				inner join v_EvnDirection_all ed on ed.EvnDirection_id = efr.EvnFuncRequest_pid
				left join v_EvnPrescrDirection epd on epd.EvnDirection_id = ed.EvnDirection_id
				left join v_EvnPrescr ep on ep.EvnPrescr_id = epd.EvnPrescr_id
				left join v_TimetableResource_lite TTR on ED.EvnDirection_id = TTR.EvnDirection_id
				left join lateral(
					select
						eup.EvnUslugaPar_id
					from
						v_EvnUslugaPar eup
					where
						eup.EvnDirection_id = ed.EvnDirection_id
						and eup.EvnUslugaPar_SetDT is not null
				limit 1
				) EUPAR on true
				left join v_EvnStatus es on es.EvnStatus_id = efr.EvnStatus_id
			where
				efr.EvnFuncRequest_id = :EvnFuncRequest_id
			limit 1
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
					if (empty($resp[0]['TimetableResource_id']) && !empty($resp[0]['EvnDirection_id'])) {
						$this->load->model('TimetableMedService_model','TimetableMedService_model');
						// принимаем человека из очереди
						$this->TimetableMedService_model->acceptWithoutRecord(array(
							'EvnDirection_id' => $resp[0]['EvnDirection_id'],
							'pmUser_id' => $data['pmUser_id']
						));
					}
				}

				if (!empty($resp[0]['EvnPrescr_id'])) {
					if ($EvnStatus_SysNick == 'FuncDonerec') {
						if ($resp[0]['EvnPrescr_IsExec'] != 2) {
							// сохраняем выполнение по свяанному назначению
							if (!empty($data['EvnDirection_id'])) {
								$this->load->model('EvnPrescr_model', 'EvnPrescr_model');
								$this->EvnPrescr_model->saveEvnPrescrIsExec(array(
									'pmUser_id' => $data['pmUser_id'],
									'EvnDirection_id' => $data['EvnDirection_id'],
									'EvnPrescr_IsExec' => 2
								));
							}
						}
					} else {
						if ($resp[0]['EvnPrescr_IsExec'] == 2) {
							// Отменяем выполнение назначения
							if (!empty($resp[0]['EvnPrescr_id'])) {
								$this->load->model('EvnPrescr_model', 'EvnPrescr_model');
								$tmp = $this->EvnPrescr_model->rollbackEvnPrescrExecution(array(
									'EvnPrescr_id' => $resp[0]['EvnPrescr_id'],
									'pmUser_id' => $data['pmUser_id'],
								));
								if (!empty($tmp[0]['Error_Msg']) && !in_array($tmp[0]['Error_Code'], array('400'))) {
									$this->rollbackTransaction();
									return array('Error_Msg' => $tmp[0]['Error_Msg']);
								}
							}
						}
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
							if (!empty($resp[0]['TimetableResource_id'])) {
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
			select
				ucms.UslugaComplexMedService_id as \"UslugaComplexMedService_id\"
			from
				v_UslugaComplexMedService ucms
				inner join v_EvnUslugaPar eup on eup.UslugaComplex_id = ucms.UslugaComplex_id
				inner join v_TimetableResource_lite TTR on TTR.UslugaComplexMedService_id = ucms.UslugaComplexMedService_id
				inner join v_MedService MS ON MS.MedService_id = UCMS.MedService_id
			where
				UCMS.MedService_id = :MedService_id and (TTR.TimetableResource_Day is not null and TTR.TimetableResource_Day between :begDay_id and :endDay_id) and EUP.EvnDirection_id = :EvnDirection_id
			limit 1
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
	 * Получение услуг по заявке
	 * @return bool
	 */
	function getEvnFuncRequestUslugaComplex($data) {
		$query = "
			select
				EUP.UslugaComplex_id as \"UslugaComplex_id\"
			from
				v_EvnUslugaPar EUP
				inner join v_EvnFuncRequest EFR on EFR.EvnFuncRequest_pid = EUP.EvnDirection_id
			where
				EFR.EvnFuncRequest_id = :EvnFuncRequest_id
		";

		return $this->queryResult($query, array(
			'EvnFuncRequest_id' => $data['EvnFuncRequest_id']
		));
	}

	/**
	 *
	 * @param type $data
	 * @return type 
	 */
	function loadEvnFuncRequestList($data) {
		$personFilters = array();
		$commonFilters = array();
		$EFR_EQ_Filters = array();
		$EFR_TTR_Filters = array();
		$queryParams = array();
		$TTRFilters = array();
		$filterByUslugaResultDT = '';
		$TTRFilters[] = "R.MedService_id = :MedService_id"; // Направление в эту определенную службу

		$queryParams['Lpu_id'] = $data['Lpu_id'];
		$queryParams['MedService_id'] = $data['MedService_id'];

		if ( !empty($data['Search_SurName']) ) {
			if (allowPersonEncrypHIV($data['session']) && isSearchByPersonEncrypHIV($data['Search_SurName'])) {
				$personFilters[] = "PEH.PersonEncrypHIV_Encryp ilike :Search_SurName";
			} else {
				$personFilters[] = "PS.Person_SurName ilike (:Search_SurName||'%')";
			}
			$queryParams['Search_SurName'] = rtrim($data['Search_SurName']);
		}
		
		if ( !empty($data['Search_FirName']) ) {
			$personFilters[] = "PS.Person_FirName ilike (:Search_FirName||'%')";
			$queryParams['Search_FirName'] = rtrim($data['Search_FirName']);
		}
		
		if ( !empty($data['Search_SecName']) ) {
			$personFilters[] = "PS.Person_SecName ilike (:Search_SecName||'%')";
			$queryParams['Search_SecName'] = rtrim($data['Search_SecName']);
		}
		
		if ( !empty($data['Search_BirthDay']) ) {
			$personFilters[] = "PS.Person_BirthDay = :Search_BirthDay";
			$queryParams['Search_BirthDay'] = $data['Search_BirthDay'];
		}

		if( !empty($data['Person_id'])) {
			$personFilters[] = "PS.Person_id = :Person_id";
			$queryParams['Person_id'] = $data['Person_id'];
		}

		if( !empty($data['Search_PersonInn']) || $data['Search_PersonInn'] == '0') {
			$personFilters[] = "PS.Person_Inn LIKE ':Search_PersonInn%'";
			$queryParams['Search_PersonInn'] = $data['Search_PersonInn'];
		}

		if( !empty($data['Search_LpuId'])) {
			$personFilters[] = "LpuFrom.Lpu_id = :Search_LpuId";
			$queryParams['Search_LpuId'] = $data['Search_LpuId'];
		}

		if ( !empty($data['EvnDirection_Num']) ) {
			$commonFilters[] = "ED.EvnDirection_Num = :EvnDirection_Num";
			$queryParams['EvnDirection_Num'] = $data['EvnDirection_Num'];
		}

		// Фильтр по услугам
		if ( !empty($data['UslugaComplex_id']) ) {
			$commonFilters[] = "exists (
				select
					t1.UslugaComplex_id
				from 
					EvnUsluga t1
					inner join Evn on t1.EvnUsluga_id = Evn.Evn_id and Evn.Evn_deleted = 1 and EvnClass_id = 47
				where 
					t1.EvnDirection_id = ED.EvnDirection_id
					and t1.UslugaComplex_id = :UslugaComplex_id
				limit 1
			)";
			$queryParams['UslugaComplex_id'] = $data['UslugaComplex_id'];
		}
		
		if ( !empty($data['EvnDirection_IsCito']) ) {
			$commonFilters[] = "coalesce(ED.EvnDirection_IsCito, 1) = :EvnDirection_IsCito";
			$queryParams['EvnDirection_IsCito'] = $data['EvnDirection_IsCito'];
		}

		if ( !empty($data['begDate']) && !empty($data['endDate']) ) {
			$endDate = new DateTime($data['endDate']);
			$begDate = new DateTime($data['begDate']);

			if ($begDate->diff($endDate)->days >= 31){
				return false;
			}
			//записанные отображаются в тот день, на который они записаны #11499
			//те, кто в очереди отображаются в тот день, когда они направлены
			$TTRFilters[] = "(
				(TTR.TimetableResource_Day is not null and TTR.TimetableResource_Day between :begDay_id and :endDay_id)
			)";
			$EFR_EQ_Filters[] = "(
				EFR.EvnStatus_id = (select EvnStatus_id from v_EvnStatus where EvnStatus_SysNick = 'FuncNew' limit 1)
				OR
				(ES.EvnStatus_SysNick IN ('FuncDonerec', 'FuncDone') and CAST(efr.EvnFuncRequest_statusDate as date) between :begDate and :endDate)
			)";
			$EFR_EQ_Filters[] = "(
				TTR.TimetableResource_id is null
			)";
			$EFR_TTR_Filters[] = "(
				(TTR.TimetableResource_Day between :begDay_id and :endDay_id)
			)";
			// только для Казахстана #148791 скрываем заявки с результатами, внесенные задним числом,
			// по-другому: дата исследования не входит  в промежуток на фильтрах
			/*if (getRegionNick() == 'kz') {
				$filterByUslugaResultDT = ' AND (
				EvnUslugaPar.EvnUslugaPar_id IS NULL 
				OR (
					EvnUslugaPar.EvnUslugaPar_id IS NOT NULL 
					and (CAST(EvnUslugaPar.EvnUslugaPar_setDate as date) between :begDate and :endDate)
					)
				) ';
			}*/
			$this->load->helper('Reg');
			$queryParams['begDay_id'] = TimeToDay(strtotime($data['begDate']));
			$queryParams['endDay_id'] = TimeToDay(strtotime($data['endDate']));
			$queryParams['begDate'] = $data['begDate'];
			$queryParams['endDate'] = $data['endDate'];
		}
		
		$queryParams['MedService_lid'] = (!empty($data['session']['CurMedService_id']))?$data['session']['CurMedService_id']:null;
		$queryParams['MedServiceLinkType_Code'] = '3';

		$selectPersonData = "dbo.getPersonPhones(ed.Person_id, '<br />') as \"Person_Phone\",
				rtrim(PS.Person_SurName) as \"Person_Surname\",
				rtrim(PS.Person_FirName) as \"Person_Firname\",
				rtrim(PS.Person_SecName) as \"Person_Secname\",
				(PS.Person_SurName || ' ' || coalesce(PS.Person_FirName, '') || ' ' || coalesce(PS.Person_SecName,'')) as \"Person_FIO\",
				to_char(PS.Person_BirthDay, 'dd.mm.yyyy') as \"Person_BirthDay\",";
		$joinPersonEncrypHIV = "";
		if (allowPersonEncrypHIV($data['session'])) {
			$joinPersonEncrypHIV = "left join v_PersonEncrypHIV PEH on PEH.Person_id = PS.Person_id";
			$selectPersonData = "case when PEH.PersonEncrypHIV_Encryp is null then dbo.getPersonPhones(ed.Person_id, '<br />') else null end as \"Person_Phone\",
				case when PEH.PersonEncrypHIV_Encryp is null then (PS.Person_SurName || ' ' || coalesce(PS.Person_FirName, '') || ' ' || coalesce(PS.Person_SecName,'')) else PEH.PersonEncrypHIV_Encryp end as \"Person_FIO\",
				case when PEH.PersonEncrypHIV_Encryp is null then to_char(PS.Person_BirthDay, 'dd.mm.yyyy') else null end as \"Person_BirthDay\",";
		}

		$EFR_TTR_Filters = array_merge($EFR_TTR_Filters, $commonFilters);
		$commonFilters = array_merge($commonFilters ,$personFilters);
		$EFR_EQ_Filters = array_merge($EFR_EQ_Filters, $commonFilters);
		
		// пока используется только на Казахстане, для других регионов заглушка, чтобы не сломались запросы
		$isBDZ = 'null as "Person_IsBDZ",';
		if (getRegionNick() == 'kz') {
			$isBDZ = "case
				when pers.Person_IsInFOMS = 1 then 'orange'
				when pers.Person_IsInFOMS = 2 then 'true'
				else 'false'
			end as \"Person_IsBDZ\",";
		}

		$querys = array();

		if(empty($commonFilters) || (isset($data['wnd_id']) && !empty($data['wnd_id']) && ($data['wnd_id'] != 'swWorkPlaceFuncDiagWindow'))) {
			// 1. запрос по биркам (все пустые без направлений)
			$querys[] = "
				select
					TTR.TimetableResource_id as \"TimetableResource_id\",
					null as \"EvnDirection_id\",
					null as \"parentEvnClass_SysNick\",
					null as \"EvnQueue_id\",
					null as \"EvnFuncRequest_id\",
					null as \"Person_id\",
					null as \"Person_Phone\",
	                null as \"Person_FIO\",
					null as \"Person_BirthDay\",
					to_char(TTR.TimetableResource_begTime, 'dd.mm.yyyy') as \"group_name\",
					case when TTR.TimetableResource_begTime is not null
						then to_char(cast(TTR.TimetableResource_begTime as timestamp), 'dd.mm.yyyy')
						else null
					end as \"TimetableResource_begDate\",
					null as \"TimetableResource_Date\",
					r.Resource_id as \"Resource_id\",
					r.Resource_Name as \"Resource_Name\",
					coalesce(to_char(TTR.TimetableResource_begTime, 'HH24:MI:SS'),'б/з') as \"TimetableResource_begTime\",
					to_char(TTR.TimetableResource_begTime, 'dd.mm.yyyy') as \"TimetableResource_Type\",
					'false' as \"EvnDirection_IsCito\",
					null as \"EvnDirection_setDT\",
					null as \"EvnDirection_Num\",
					null as \"EvnUslugaPar_id\",
					MSL.MedService_id as \"RCC_MedService_id\",
					null as \"RemoteConsultCenterResearch_id\",
					null as \"RemoteConsultCenterResearch_status\",
					'false' as \"FuncRequestState\",
					'' as \"Operator\",
					'' as \"EvnFuncRequest_UslugaCache\",
					'' as \"Lpu_Name\",
					'' as \"LpuSection_Name\",
					'' as \"EvnCostPrint_PrintStatus\",
					null as \"ElectronicTalon_Num\",
					null as \"ElectronicTalonStatus_Name\",
					null as \"ElectronicService_id\",
					null as \"ElectronicTalonStatus_id\",
					null as \"ElectronicTalon_id\",
					null as \"EvnDirection_uid\",
					null as \"PersonQuarantine_IsOn\",
					null as \"Person_IsBDZ\",
					null as \"toElectronicService_id\",
					null as \"fromElectronicService_id\"
				FROM v_TimetableResource_lite TTR
					inner join v_Resource r on r.Resource_id = ttr.Resource_id
					LEFT JOIN v_MedService MS ON MS.MedService_id = r.MedService_id
					LEFT JOIN v_MedServiceLink MSL on MSL.MedService_lid = :MedService_lid AND MSL.MedServiceLinkType_id = (select MSLT.MedServiceLinkType_id from v_MedServiceLinkType MSLT where MSLT.MedServiceLinkType_Code = :MedServiceLinkType_Code limit 1)
					--LEFT JOIN v_pmUserCache PUC on PUC.PMUser_id = TTR.pmUser_insID
				WHERE 
					" . implode(' and ', $TTRFilters) . "
					and TTR.EvnDirection_id is null
					and r.Resource_Name is not null
			";
		}

		// 2a. запрос по заявкам (все из EvnFuncRequest) не записанные (из очереди и остальные)
		$querys[] = "
			select
				TTR.TimetableResource_id as \"TimetableResource_id\",
				ED.EvnDirection_id as \"EvnDirection_id\",
				EPC.EvnClass_SysNick as \"parentEvnClass_SysNick\",
				ED.EvnQueue_id as \"EvnQueue_id\",
				EFR.EvnFuncRequest_id as \"EvnFuncRequest_id\",
				PS.Person_id as \"Person_id\",
				{$selectPersonData}
				null as \"group_name\",
				case
					when ES.EvnStatus_SysNick = 'FuncDonerec' then to_char(cast(efr.EvnFuncRequest_statusDate as timestamp), 'dd.mm.yyyy')
					else null
				end as \"TimetableResource_begDate\",
				case
					when TimetableResource_begTime is not null then to_char(TimetableResource_begTime, 'dd.mm.yyyy')
					else to_char(TimetableResource_insDT, 'dd.mm.yyyy')
				end as \"TimetableResource_Date\",
				r.Resource_id as \"Resource_id\",
				r.Resource_Name as \"Resource_Name\",
				'б/з' as \"TimetableResource_begTime\",
				null as \"TimetableResource_Type\",
				case when 2 = COALESCE(ed.EvnDirection_IsCito,epfd.EvnPrescrFuncDiag_IsCito, 1) then 'true' else 'false' end as \"EvnDirection_IsCito\",
				to_char(ED.EvnDirection_setDT, 'dd.mm.yyyy') as \"EvnDirection_setDT\",
				ED.EvnDirection_Num as \"EvnDirection_Num\",
				EvnUslugaPar.EvnUslugaPar_id as \"EvnUslugaPar_id\",
				MSL.MedService_id as \"RCC_MedService_id\",
				RCCR.RemoteConsultCenterResearch_id as \"RemoteConsultCenterResearch_id\",
				RCCR.RemoteConsultCenterResearch_status as \"RemoteConsultCenterResearch_status\",
				case when ES.EvnStatus_SysNick = 'FuncDonerec' then 'true' else 'false' end as \"FuncRequestState\",
				case
					when PUC.PMUser_surName is null then ''
					else PUC.PMUser_surName||' '||coalesce(left(PUC.PMUser_firName,1),'')||' '||coalesce(left(PUC.PMUser_secName,1),'')
				end as \"Operator\",
				efr.EvnFuncRequest_UslugaCache as \"EvnFuncRequest_UslugaCache\",
				LpuFrom.Lpu_Nick as \"Lpu_Name\",
				LpuSectionFrom.LpuSection_Name as \"LpuSection_Name\",
				--'' as \"EvnCostPrint_PrintStatus\",
				case when NoPrintCount.NPC > 0 then 'true' else 'false' end as \"EvnCostPrint_PrintStatus\",
				et.ElectronicTalon_Num as \"ElectronicTalon_Num\",
				ets.ElectronicTalonStatus_Name as \"ElectronicTalonStatus_Name\",
				et.ElectronicService_id as \"ElectronicService_id\",
				et.ElectronicTalonStatus_id as \"ElectronicTalonStatus_id\",
				et.ElectronicTalon_id as \"ElectronicTalon_id\",
				et.EvnDirection_uid as \"EvnDirection_uid\",
				case when exists(
					select *
					from v_PersonQuarantine PQ
					where PQ.Person_id = PS.Person_id 
					and PQ.PersonQuarantine_endDT is null
				) then 2 else 1 end as \"PersonQuarantine_IsOn\",
				{$isBDZ}
				etr.ElectronicService_id as \"toElectronicService_id\",
				etr.ElectronicService_uid as \"fromElectronicService_id\",
				wls.WorkListStatus_Name as \"WorkListStatus_Name\"
			FROM v_EvnFuncRequest efr
				inner join v_EvnDirection_all ed on ed.EvnDirection_id = efr.EvnFuncRequest_pid and coalesce(ed.EvnStatus_id, 16) not in (12, 13)
				left join v_EvnPrescrDirection epd on ed.EvnDirection_id = epd.evnDirection_id
				left join v_EvnPrescrFuncDiag epfd on epfd.EvnPrescrFuncDiag_id = epd.EvnPrescr_id
				--inner join v_EvnQueue eq on eq.EvnDirection_id = ed.EvnDirection_id and eq.EvnQueue_failDT is null and eq.EvnQueue_recDT is null
				left join v_TimetableResource_lite TTR on TTR.EvnDirection_id = ed.EvnDirection_id
				left join v_Resource R on R.Resource_id = TTR.Resource_id
				left join v_Lpu LpuFrom on LpuFrom.Lpu_id = ED.Lpu_sid
				left join v_LpuSection LpuSectionFrom on LpuSectionFrom.LpuSection_id = ED.LpuSection_id
				left join v_PersonState PS on PS.Person_id = ED.Person_id
				left join v_Person pers on pers.Person_id = ED.Person_id
				{$joinPersonEncrypHIV}
				--left join v_EvnCostPrint ECP on ECP.Evn_id = efr.EvnFuncRequest_id
				left join lateral(
					select ECP.EvnCostPrint_id as NPC
					from v_EvnUslugaPar EUP
					left join v_EvnCostPrint ECP on ECP.Evn_id = EUP.EvnUslugaPar_id
					where EUP.EvnDirection_id = efr.EvnFuncRequest_pid
					and coalesce(ECP.EvnCostPrint_IsNoPrint,0) <> 2
					limit 1
				) NoPrintCount on true
				left join v_EvnStatus ES on ES.EvnStatus_id = EFR.EvnStatus_id
				left join lateral(
					select EvnUslugaPar_id from v_EvnUslugaPar where EvnDirection_id = ED.EvnDirection_id limit 1
				) EvnUslugaPar on true
				LEFT JOIN v_RemoteConsultCenterResearch RCCR on RCCR.EvnUslugaPar_id = EvnUslugaPar.EvnUslugaPar_id
				LEFT JOIN v_MedServiceLink MSL on MSL.MedService_lid = :MedService_lid AND MSL.MedServiceLinkType_id = (select MSLT.MedServiceLinkType_id from v_MedServiceLinkType MSLT where MSLT.MedServiceLinkType_Code = :MedServiceLinkType_Code limit 1)
				LEFT JOIN v_pmUserCache PUC on PUC.PMUser_id = ed.pmUser_insID
				left join v_ElectronicTalonRedirect etr on etr.EvnDirection_uid = TTR.EvnDirection_id
				left join v_ElectronicTalon et on (et.EvnDirection_uid = TTR.EvnDirection_id or  et.EvnDirection_id = TTR.EvnDirection_id)
				left join v_ElectronicTalonStatus ets on ets.ElectronicTalonStatus_id = et.ElectronicTalonStatus_id
				left join v_Evn EPC on EPC.Evn_id = ED.EvnDirection_pid
				left join v_WorkListQueue wlq on EvnUslugaPar.EvnUslugaPar_id = wlq.EvnUslugaPar_id
				left join v_WorkListStatus wls on wlq.WorkListStatus_id = wls.WorkListStatus_id
				--left join v_ElectronicTalonRedirect etr on etr.EvnDirection_uid = et.EvnDirection_uid
			WHERE 
				efr.MedService_id = :MedService_id
				and exists(Select 1 from v_EvnQueue eq where eq.EvnDirection_id = ed.EvnDirection_id and eq.EvnQueue_failDT is null and eq.EvnQueue_recDT is null limit 1)
				" . (count($EFR_EQ_Filters) > 0 ? "and " . implode(' and ', $EFR_EQ_Filters) : "") . "
				and r.Resource_Name is not null
		";

		// 2b. запрос по заявкам (все из EvnFuncRequest) только записанные
		$querys[] = "
			WITH table1 as (
				SELECT
					EvnFuncRequest_id,
					Evn.Evn_pid as EvnFuncRequest_pid,
					EvnFuncRequest_UslugaCache,
					ed2.EvnDirection_id,
					ed2.EvnDirection_pid,
					ed.EvnQueue_id,
					Evn.Person_id,
					case 
						when EvnPrescrFuncDiag_IsCito = 2 then 2
						when ed2.EvnDirection_IsCito = 2 then 2 else 1
					end as	EvnPrescrFuncDiag_IsCito,
					ed.EvnDirection_setDT,
					ed.EvnDirection_Num,
					ed.Lpu_sid,
					ed.LpuSection_id,
					Evn.EvnStatus_id,
					ed.pmUser_insID,
					Evn.Evn_statusDate as EvnFuncRequest_statusDate,
					ttr.Resource_id,
					ttr.TimetableResource_id,
					TimetableResource_begTime,
					Resource_Name,
					case
						when TimetableResource_begTime is not null then to_char(TimetableResource_begTime, 'dd.mm.yyyy')
						else to_char(TimetableResource_insDT, 'dd.mm.yyyy')
					end as TimetableResource_Date
				from 
					v_TimeTableResource_lite ttr
						left join v_Resource r on ttr.Resource_id = r.Resource_id
						left join v_MedService ms on r.MedService_id = ms.MedService_id
						left join Evn on ttr.EvnDirection_id = Evn.Evn_pid and Evn.Evn_deleted = 1
						left join v_EvnDirection_all ed on ed.EvnDirection_id = ttr.EvnDirection_id and coalesce(ed.EvnStatus_id, 16) not in (12,13)
						left join v_EvnDirection_all ed2 on ed2.EvnDirection_id = ttr.EvnDirection_id
						left join v_EvnPrescrDirection epd on ed.EvnDirection_id = epd.evnDirection_id
						left join v_EvnPrescrFuncDiag epfd on epfd.EvnPrescrFuncDiag_id = epd.EvnPrescr_id
						left join v_EvnFuncRequest efr on efr.EvnFuncRequest_id = Evn.Evn_id
				WHERE
					ms.MedService_id = :MedService_id
					and EVN.person_id is not null
					" . (count($EFR_TTR_Filters) > 0 ? "and " . implode(' and ', $EFR_TTR_Filters) : "") . "
			),
            table2 as (
            SELECT * FROM v_PersonState PS WHERE PS.Person_id IN (SELECT Person_id FROM table1)
            ),
            table3 as (
            SELECT * FROM v_Evn EPC WHERE EPC.Evn_id IN (SELECT EvnDirection_pid FROM table1)
            )
			
			select
				efr.TimetableResource_id as \"TimetableResource_id\",
				EFR.EvnDirection_id as \"EvnDirection_id\",
				wls.WorkListStatus_Name as \"WorkListStatus_Name\",
				EPC.EvnClass_SysNick as \"parentEvnClass_SysNick\",
				EFR.EvnQueue_id as \"EvnQueue_id\",
				EFR.EvnFuncRequest_id as \"EvnFuncRequest_id\",
				PS.Person_id as \"Person_id\",
				".(str_replace('ed.','efr.', $selectPersonData))."
				to_char(efr.TimetableResource_begTime, 'dd.mm.yyyy') as \"group_name\",
				case
					when efr.TimetableResource_begTime is not null then to_char(cast(efr.TimetableResource_begTime as timestamp), 'dd.mm.yyyy')
					when EFR.EvnStatus_id = (select EvnStatus_id from v_EvnStatus where EvnStatus_SysNick = 'FuncDonerec' limit 1) then to_char(cast(efr.EvnFuncRequest_statusDate as timestamp), 'dd.mm.yyyy')
					else null
				end as \"TimetableResource_begDate\",
				efr.TimetableResource_Date as \"TimetableResource_Date\",
				efr.Resource_id as \"Resource_id\",
				efr.Resource_Name as \"Resource_Name\",
				coalesce(to_char(efr.TimetableResource_begTime, 'HH24:MI:SS'),'б/з') as \"TimetableResource_begTime\",
				to_char(efr.TimetableResource_begTime, 'dd.mm.yyyy') as \"TimetableResource_Type\",
				case when 2 = coalesce(EFR.EvnPrescrFuncDiag_IsCito, 1) then 'true' else 'false' end as \"EvnDirection_IsCito\",
				to_char(EFR.EvnDirection_setDT, 'dd.mm.yyyy') as \"EvnDirection_setDT\",
				EFR.EvnDirection_Num as \"EvnDirection_Num\",
				EvnUslugaPar.EvnUslugaPar_id as \"EvnUslugaPar_id\",
				to_char(EvnUslugaPar.EvnUslugaPar_setDate, 'dd.mm.yyyy') as \"EvnUslugaPar_setDate\",
				MSL.MedService_id as \"RCC_MedService_id\",
				RCCR.RemoteConsultCenterResearch_id as \"RemoteConsultCenterResearch_id\",
				RCCR.RemoteConsultCenterResearch_status as \"RemoteConsultCenterResearch_status\",
				case when EFR.EvnStatus_id = (select EvnStatus_id from v_EvnStatus where EvnStatus_SysNick = 'FuncDonerec' limit 1) then 'true' else 'false' end as \"FuncRequestState\",
				case
					when PUC.PMUser_surName is null then ''
					else PUC.PMUser_surName||' '||coalesce(left(PUC.PMUser_firName,1),'')||' '||coalesce(left(PUC.PMUser_secName,1),'')
				end as \"Operator\",
				efr.EvnFuncRequest_UslugaCache as \"EvnFuncRequest_UslugaCache\",
				LpuFrom.Lpu_Nick as \"Lpu_Name\",
				LpuSectionFrom.LpuSection_Name as \"LpuSection_Name\",
				--'' as \"EvnCostPrint_PrintStatus\",
				case when NoPrintCount.NPC > 0 then 'true' else 'false' end as \"EvnCostPrint_PrintStatus\",
				et.ElectronicTalon_Num as \"ElectronicTalon_Num\",
				ets.ElectronicTalonStatus_Name as \"ElectronicTalonStatus_Name\",
				et.ElectronicService_id as \"ElectronicService_id\",
				et.ElectronicTalonStatus_id as \"ElectronicTalonStatus_id\",
				et.ElectronicTalon_id as \"ElectronicTalon_id\",
				et.EvnDirection_uid as \"EvnDirection_uid\",
				case when exists(
					select *
					from v_PersonQuarantine PQ
					where PQ.Person_id = PS.Person_id 
					and PQ.PersonQuarantine_endDT is null
				) then 2 else 1 end as \"PersonQuarantine_IsOn\",
				{$isBDZ}
				etr.ElectronicService_id as \"toElectronicService_id\",
				etr.ElectronicService_uid as \"fromElectronicService_id\"
			FROM table1 efr
				left join v_Lpu LpuFrom on LpuFrom.Lpu_id = efr.Lpu_sid
				left join v_LpuSection LpuSectionFrom on LpuSectionFrom.LpuSection_id = efr.LpuSection_id
				inner join table2 PS on PS.Person_id = efr.Person_id
				left join v_Person pers on pers.Person_id = efr.Person_id
				--left join v_EvnStatus ES on ES.EvnStatus_id = EFR.EvnStatus_id
				left join lateral(
					SELECT EvnUslugaPar_id, EvnUslugaPar_setDate from v_EvnUslugaPar where EvnDirection_id = efr.EvnDirection_id limit 1
				) EvnUslugaPar on true

				left JOIN v_RemoteConsultCenterResearch RCCR on RCCR.EvnUslugaPar_id = EvnUslugaPar.EvnUslugaPar_id
				left join v_WorkListQueue wlq on EvnUslugaPar.EvnUslugaPar_id = wlq.EvnUslugaPar_id
				left join v_WorkListStatus wls on wlq.WorkListStatus_id = wls.WorkListStatus_id
				LEFT JOIN v_MedServiceLink MSL on MSL.MedService_lid = :MedService_lid AND MSL.MedServiceLinkType_id = (select MSLT.MedServiceLinkType_id from v_MedServiceLinkType MSLT where MSLT.MedServiceLinkType_Code = :MedServiceLinkType_Code limit 1)
				left JOIN v_pmUserCache PUC on PUC.PMUser_id = efr.pmUser_insID
				left join lateral(
					select ECP.EvnCostPrint_id as NPC
					from v_EvnUslugaPar EUP
					left join v_EvnCostPrint ECP ON ECP.Evn_id = EUP.EvnUslugaPar_id
					where EUP.EvnDirection_id = efr.EvnFuncRequest_pid
					and coalesce(ECP.EvnCostPrint_IsNoPrint,0) <> 2
					limit 1
				) NoPrintCount on true
				left join v_ElectronicTalonRedirect etr on etr.EvnDirection_uid = efr.EvnDirection_id				
				--left join v_ElectronicTalon et on (coalesce(et.EvnDirection_uid, et.EvnDirection_id) = efr.EvnDirection_id)
				left join lateral (
					Select * from  v_ElectronicTalon et where (et.EvnDirection_uid = efr.EvnDirection_id or et.EvnDirection_id = efr.EvnDirection_id) limit 1
				) et on true
				left join v_ElectronicTalonStatus ets on ets.ElectronicTalonStatus_id = et.ElectronicTalonStatus_id
				left join table3 EPC on EPC.Evn_id = efr.EvnDirection_pid
				{$joinPersonEncrypHIV}
			WHERE
				(1 = 1)
				{$filterByUslugaResultDT}
				" . (count($personFilters) > 0 ? "and " . implode(' and ', $personFilters) : "") . "	
		";
		
		$response = array();


		foreach($querys as $key=>$query) {

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
		}
		if(empty($data['start']) && empty($data['limit'])){
				$data['start'] = 0;
				$data['limit'] = 20;
		}
		$subresp = $this->loadEvnFuncQueueRequestList($data);
		$response = array_merge($response,$subresp);
		return $response;
	}

	/**
	 *
	 * Получение списка заявок ФД: МАРМ версия
	 */
	function mLoadEvnFuncRequestList($data) {

		$this->load->helper('Reg');
		$person_filter = ''; $filter = '';

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
				case when peh.PersonEncrypHIV_Encryp is null then PS.Person_SurName else peh.PersonEncrypHIV_Encryp end as Person_Surname,
				case when peh.PersonEncrypHIV_Encryp is null then PS.Person_FirName end as Person_Firname,
				case when peh.PersonEncrypHIV_Encryp is null then PS.Person_SecName end as Person_Secname,";
			$selectPersonDataWithQoutes = "
				PS.Sex_id,
				case when peh.PersonEncrypHIV_Encryp is null then PS.Person_Birthday end as \"Person_Birthday\",
				case when peh.PersonEncrypHIV_Encryp is null then PS.Person_SurName else peh.PersonEncrypHIV_Encryp end as \"Person_Surname\",
				case when peh.PersonEncrypHIV_Encryp is null then PS.Person_FirName end as \"Person_Firname\",
				case when peh.PersonEncrypHIV_Encryp is null then PS.Person_SecName end as \"Person_Secname\",";
		} else {
			$selectPersonData = "
				PS.Sex_id,
				PS.Person_Birthday,
				PS.Person_Surname,
				PS.Person_Firname,
				PS.Person_Secname,";
			$selectPersonDataWithQoutes = "
				PS.Sex_id as \"Sex_id\",
				PS.Person_Birthday as \"Person_Birthday\",
				PS.Person_Surname as \"Person_Surname\",
				PS.Person_Firname as \"Person_Firname\",
				PS.Person_Secname as \"Person_Secname\",";
		}

		if (!empty($data['Person_SurName'])) {
			if (allowPersonEncrypHIV($data['session']) && $isSearchByEncryp) {
				$person_filter .= " and peh.PersonEncrypHIV_Encryp ilike :Person_SurName";
			} else {
				$person_filter .= " and PS.Person_SurName ilike :Person_SurName || '%'";
			}
			$queryParams['Person_SurName'] = rtrim($data['Person_SurName']);
		}

		if (!empty($data['Person_FirName'])) {
			$person_filter .= " and PS.Person_FirName ilike :Person_FirName || '%'";
			$queryParams['Person_FirName'] = rtrim($data['Person_FirName']);
		}

		if (!empty($data['Person_SecName'])) {
			$person_filter .= " and PS.Person_SecName ilike :Person_SecName || '%'";
			$queryParams['Person_SecName'] = rtrim($data['Person_SecName']);
		}

		if ( !empty($data['EvnDirection_IsCito']) ) {
			$filter .= " and coalesce(ED.EvnDirection_IsCito, 1) = 2";
		}

		// Фильтр по услугам
		if (!empty($data['UslugaComplex_id'])) {
			$filter .= " 
				and exists (
					select
						t1.UslugaComplex_id
					from 
						EvnUsluga t1
						inner join Evn on t1.EvnUsluga_id = Evn.Evn_id
							and Evn.Evn_deleted = 1 and EvnClass_id = 47
					where 
						t1.EvnDirection_id = ED.EvnDirection_id
						and t1.UslugaComplex_id = :UslugaComplex_id
					limit 1
				)";
			$queryParams['UslugaComplex_id'] = $data['UslugaComplex_id'];
		}

		// 2a. запрос по заявкам (все из EvnFuncRequest) не записанные (из очереди и остальные)
		$queuedQuery = "
			with table1 as (
				SELECT
					NULL as TimetableResource_id,
					ED.EvnDirection_id,
					EPC.EvnClass_SysNick as parentEvnClass_SysNick,
					null as EvnQueue_id,
					EFR.evn_id as EvnFuncRequest_id,
					to_char(ED.EvnDirection_setDT, 'dd.mm.yyyy') as group_name,
					case when 2 = coalesce(ED.EvnDirection_IsCito, 1)
						then 'true'
						else 'false'
					end as EvnDirection_IsCito,
					to_char(ED.EvnDirection_setDT, 'dd.mm.yyyy') as EvnDirection_setDT,
					ED.EvnDirection_Num,
					efr.EvnFuncRequest_UslugaCache,
					Evn.EvnStatus_id,
					Evn.Person_id,
					Evn.pmUser_insID
				FROM
					EvnFuncRequest EFR 
					inner join Evn on Evn.Evn_id = EFR.Evn_id and coalesce(Evn.Evn_deleted, 1) = 1
					inner JOIN v_EvnDirection_all ED ON ed.EvnDirection_id = Evn.Evn_pid
						and coalesce(ed.EvnStatus_id, 16) not in (12, 13)
					left join v_Evn EPC on EPC.Evn_id = ED.EvnDirection_pid
				WHERE
					Evn.Lpu_id = :Lpu_id
					and efr.MedService_id = :MedService_id
					and ED.TimetableResource_id is null -- без бирки
					and ED.EvnQueue_id is null -- без очереди
					-- бывший EFRFilter
					and CAST(Evn.Evn_setDT as date) between :begDate and :endDate
					{$filter}
				limit 100
			), table2 as (
				SELECT
					ED.EvnDirection_id,
					EPC.EvnClass_SysNick as parentEvnClass_SysNick,
					ED.EvnQueue_id as EvnQueue_id,
					EFR.Evn_id as EvnFuncRequest_id,
					case when 2 = coalesce(ED.EvnDirection_IsCito, 1)
						then 'true'
						else 'false'
					end as EvnDirection_IsCito,
					to_char(ED.EvnDirection_setDT, 'dd.mm.yyyy') as EvnDirection_setDT,
					ED.EvnDirection_Num,
					efr.EvnFuncRequest_UslugaCache,
					ed.Lpu_sid,
					ed.LpuSection_id,
					ed.Person_id,
					evn.Evn_pid as EvnFuncRequest_pid,
					ed.pmUser_insID,
					es.EvnStatus_SysNick,
					Evn.Evn_statusDate as EvnFuncRequest_statusDate
				FROM
					EvnFuncRequest efr
					inner join Evn on Evn.Evn_id = EFR.Evn_id
						and coalesce(Evn.Evn_deleted, 1) = 1
					inner join v_EvnDirection_all ed on ed.EvnDirection_id = Evn.Evn_pid
						and coalesce(ed.EvnStatus_id, 16) not in (12, 13)
					left join v_EvnStatus ES on ES.EvnStatus_id = Evn.EvnStatus_id
					left join v_Evn EPC on EPC.Evn_id = ED.EvnDirection_pid
				WHERE 
					efr.MedService_id = :MedService_id
					-- бывший EFR_EQ_Filters
					and (
						Evn.EvnStatus_id = (
							select
								EvnStatus_id 
							from v_EvnStatus
							where EvnStatus_SysNick = 'FuncNew'
							limit 1
						) OR (
							ES.EvnStatus_SysNick IN ('FuncDonerec', 'FuncDone') 
							and CAST(Evn.Evn_statusDate as date) between :begDate and :endDate
						)
					)
				limit 100
			)
			
			select
				TimetableResource_id as \"TimetableResource_id\",
				EvnDirection_id as \"EvnDirection_id\",
				parentEvnClass_SysNick as \"parentEvnClass_SysNick\",
				EvnQueue_id as \"EvnQueue_id\",
				EvnFuncRequest_id as \"EvnFuncRequest_id\",
				Person_id as \"Person_id\",
				Sex_id as \"Sex_id\",
				Person_Birthday as \"Person_Birthday\",
				Person_Surname as \"Person_Surname\",
				Person_Firname as \"Person_Firname\",
				Person_Secname as \"Person_Secname\",
				group_name as \"group_name\",
				TimetableResource_begDate as \"TimetableResource_begDate\",
				Resource_id as \"Resource_id\",
				Resource_Name as \"Resource_Name\",
				TimetableResource_begTime as \"TimetableResource_begTime\",
				TimetableResource_Type as \"TimetableResource_Type\",
				EvnDirection_IsCito as \"EvnDirection_IsCito\",
				EvnDirection_setDT as \"EvnDirection_setDT\",
				EvnDirection_Num as \"EvnDirection_Num\",
				EvnUslugaPar_id as \"EvnUslugaPar_id\",
				RCC_MedService_id as \"RCC_MedService_id\",
				RemoteConsultCenterResearch_id as \"RemoteConsultCenterResearch_id\",
				RemoteConsultCenterResearch_status as \"RemoteConsultCenterResearch_status\",
				FuncRequestState as \"FuncRequestState\",
				Operator as \"Operator\",
				EvnFuncRequest_UslugaCache as \"EvnFuncRequest_UslugaCache\",
				Lpu_Name as \"Lpu_Name\",
				LpuSection_Name as \"LpuSection_Name\",
				EvnCostPrint_PrintStatus as \"EvnCostPrint_PrintStatus\"
			from (
				select
					TTR.TimetableResource_id,
					table2.EvnDirection_id,
					table2.parentEvnClass_SysNick,
					table2.EvnQueue_id,
					table2.EvnFuncRequest_id,
					PS.Person_id,
					{$selectPersonData}
					null as group_name,
					case when table2.EvnStatus_SysNick = 'FuncDonerec' 
						then to_char(table2.EvnFuncRequest_statusDate, 'dd.mm.yyyy')
						else null
					end as TimetableResource_begDate,
					r.Resource_id,
					r.Resource_Name,
					'б/з' as TimetableResource_begTime,
					null as TimetableResource_Type,
					table2.EvnDirection_IsCito,
					table2.EvnDirection_setDT,
					table2.EvnDirection_Num,
					EvnUslugaPar.EvnUslugaPar_id,
					MSL.MedService_id as RCC_MedService_id,
					RCCR.RemoteConsultCenterResearch_id,
					RCCR.RemoteConsultCenterResearch_status,
					case when table2.EvnStatus_SysNick = 'FuncDonerec'
						then 'true'
						else 'false'
					end as FuncRequestState,
					case when PUC.PMUser_surName is null
						then ''
						else PUC.PMUser_surName
							|| ' ' || coalesce(left(PUC.PMUser_firName, 1), '')
							|| ' ' || coalesce(left(PUC.PMUser_secName, 1), '')
					end as Operator,
					table2.EvnFuncRequest_UslugaCache,
					LpuFrom.Lpu_Nick as Lpu_Name,
					LpuSectionFrom.LpuSection_Name as LpuSection_Name,
					case when NoPrintCount.NPC > 0
						then 'true'
						else 'false'
					end as EvnCostPrint_PrintStatus
				FROM
					table2 table2
					left join v_TimetableResource_lite TTR on TTR.EvnDirection_id = table2.EvnDirection_id
					left join v_Resource R on R.Resource_id = TTR.Resource_id
					left join v_Lpu LpuFrom on LpuFrom.Lpu_id = table2.Lpu_sid
					left join v_LpuSection LpuSectionFrom on LpuSectionFrom.LpuSection_id = table2.LpuSection_id
					left join v_PersonState PS on PS.Person_id = table2.Person_id
					left join v_PersonEncrypHIV peh on peh.Person_id = PS.Person_id
					left join lateral(
						select
							ECP.EvnCostPrint_id as NPC
						from v_EvnUslugaPar EUP
							left join v_EvnCostPrint ECP on ECP.Evn_id = EUP.EvnUslugaPar_id
						where EUP.EvnDirection_id = table2.EvnFuncRequest_pid
							and coalesce(ECP.EvnCostPrint_IsNoPrint,0) <> 2
						limit 1
					) NoPrintCount on true
					left join lateral(
						select
							EvnUslugaPar_id
						from v_EvnUslugaPar
						where EvnDirection_id = table2.EvnDirection_id
						limit 1
					) EvnUslugaPar on true
					LEFT JOIN v_RemoteConsultCenterResearch RCCR on RCCR.EvnUslugaPar_id = EvnUslugaPar.EvnUslugaPar_id
					LEFT JOIN v_MedServiceLink MSL on MSL.MedService_lid = :MedService_lid
						AND MSL.MedServiceLinkType_id = (
							select
								MSLT.MedServiceLinkType_id
							from v_MedServiceLinkType MSLT
							where MSLT.MedServiceLinkType_Code = :MedServiceLinkType_Code
							limit 1
						)
					LEFT JOIN v_pmUserCache PUC on PUC.PMUser_id = table2.pmUser_insID
					inner join lateral (
						SELECT
							1 AS Eq
						from v_EvnQueue eq
						where eq.EvnDirection_id = table2.EvnDirection_id
							and eq.EvnQueue_failDT is null
							and eq.EvnQueue_recDT is NULL
						limit 1
					) EQ on true
				WHERE 
					r.Resource_Name is null
					and TTR.TimetableResource_id is null
					{$filter}
					{$person_filter}
					
				union all
				
				-- без направления (без EvnDirection, связь с услугой была по EvnFuncRequest_pid, но скриптом создали направления без объекта очереди и без объекта бирки...)
				select
					NULL as TimetableResource_id,
					table1.EvnDirection_id,
					table1.parentEvnClass_SysNick,
					null as EvnQueue_id,
					table1.EvnFuncRequest_id,
					PS.Person_id,
					{$selectPersonData}
					table1.group_name,
					case when EvnUslugaPar.EvnUslugaPar_setDT is not null
						then to_char(EvnUslugaPar.EvnUslugaPar_setDT, 'dd.mm.yyyy')
						else null
					end as TimetableResource_begDate,
					null as Resource_id,
					'' as Resource_Name,
					'б/н' as TimetableResource_begTime,
					null as TimetableResource_Type,
					table1.EvnDirection_IsCito,
					table1.EvnDirection_setDT,
					table1.EvnDirection_Num,
					EvnUslugaPar.EvnUslugaPar_id,
					MSL.MedService_id as RCC_MedService_id,
					RCCR.RemoteConsultCenterResearch_id,
					RCCR.RemoteConsultCenterResearch_status,
					case when EvnUslugaPar.EvnUslugaPar_setDT is not null
						then 'true'
						else 'false'
					end as FuncRequestState,
					case
						when UC.PMUser_surName is null then ''
						else UC.PMUser_surName
							|| ' ' || coalesce(left(UC.PMUser_firName, 1), '')
							|| ' ' || coalesce(left(UC.PMUser_secName, 1), '')
					end as Operator,
					table1.EvnFuncRequest_UslugaCache,
					'' as Lpu_Name,
					'' as LpuSection_Name,
					'' as EvnCostPrint_PrintStatus
				FROM
					table1 table1
					left join lateral(
						Select
							EvnUslugaPar_id,
							EvnUslugaPar_setDT
						from v_EvnUslugaPar EvnUslugaPar
						where EvnUslugaPar.EvnDirection_id = table1.EvnDirection_i
						limit 1
					) EvnUslugaPar on true
					LEFT JOIN v_EvnStatus ES on ES.EvnStatus_id = table1.EvnStatus_id
					LEFT JOIN v_PersonState PS on PS.Person_id = table1.Person_id
					left join v_PersonEncrypHIV peh on peh.Person_id = PS.Person_id
					LEFT JOIN v_RemoteConsultCenterResearch RCCR on RCCR.EvnUslugaPar_id = EvnUslugaPar.EvnUslugaPar_id
					LEFT JOIN v_MedServiceLink MSL on MSL.MedService_lid = :MedService_lid
						AND MSL.MedServiceLinkType_id = (
							select
								MSLT.MedServiceLinkType_id
							from v_MedServiceLinkType MSLT
							where MSLT.MedServiceLinkType_Code = :MedServiceLinkType_Code
							limit 1
						)
					LEFT JOIN v_pmUserCache UC on UC.PMUser_id = table1.pmUser_insID
				WHERE
					1 = 1
					and EvnUslugaPar.EvnUslugaPar_id is not null
					{$filter}
					{$person_filter}
				) paging
			where (1=1)
			order by
				paging.EvnDirection_setDT,
				paging.EvnDirection_id
		";

		$queuedResult = $this->queryResult($queuedQuery, $queryParams);

		// 2b. запрос по заявкам (все из EvnFuncRequest) только записанные
		$requestsQuery = "
			WITH table1 as (
				SELECT
					EvnFuncRequest_id,
					Evn.Evn_pid as EvnFuncRequest_pid,
					EvnFuncRequest_UslugaCache,
					ed2.EvnDirection_id,
					ed2.EvnDirection_pid,
					ed.EvnQueue_id,
					Evn.Person_id,
					case 
						when EvnPrescrFuncDiag_IsCito = 2 then 2
						when ed2.EvnDirection_IsCito = 2 then 2 else 1
					end as	EvnPrescrFuncDiag_IsCito,
					ed.EvnDirection_setDT,
					ed.EvnDirection_Num,
					ed.Lpu_sid,
					ed.LpuSection_id,
					Evn.EvnStatus_id,
					ed.pmUser_insID,
					Evn.Evn_statusDate as EvnFuncRequest_statusDate,
					ttr.Resource_id,
					ttr.TimetableResource_id,
					TimetableResource_begTime,
					Resource_Name,
					case when TimetableResource_begTime is not null
						then to_char(TimetableResource_begTime, 'dd.mm.yyyy')
						else to_char(TimetableResource_insDT, 'dd.mm.yyyy')
					end as TimetableResource_Date
				from v_TimeTableResource_lite ttr
				left join v_Resource r on ttr.Resource_id = r.Resource_id
				left join v_MedService ms on r.MedService_id = ms.MedService_id
				left join Evn on ttr.EvnDirection_id = Evn.Evn_pid
					and Evn.Evn_deleted = 1
				left join v_EvnDirection_all ed on ed.EvnDirection_id = ttr.EvnDirection_id
					and coalesce(ed.EvnStatus_id, 16) not in (12,13)
				left join v_EvnDirection_all ed2 on ed2.EvnDirection_id = ttr.EvnDirection_id
				left join v_EvnPrescrDirection epd on ed.EvnDirection_id = epd.evnDirection_id
				left join v_EvnPrescrFuncDiag epfd on epfd.EvnPrescrFuncDiag_id = epd.EvnPrescr_id
				left join EvnFuncRequest efr on efr.EvnFuncRequest_id = Evn.Evn_id
				WHERE
					ms.MedService_id = :MedService_id
					and EVN.person_id is not null
					-- бывший EFR_TTR_Filters
					and (TTR.TimetableResource_Day between :begDay_id and :endDay_id)
					{$filter} 
					--
			)
			
			select
				efr.TimetableResource_id as TimetableResource_id,
				EFR.EvnDirection_id as EvnDirection_id,
				EPC.EvnClass_SysNick as parentEvnClass_SysNick,
				EFR.EvnQueue_id as EvnQueue_id,
				EFR.EvnFuncRequest_id as EvnFuncRequest_id,
				PS.Person_id as Person_id,
				{$selectPersonDataWithQoutes}
				to_char(efr.TimetableResource_begTime, 'dd.mm.yyyy') as group_name,
				case when efr.TimetableResource_begTime is not null
					then to_char(efr.TimetableResource_begTime, 'dd.mm.yyyy')
				when EFR.EvnStatus_id = (
					select
						EvnStatus_id 
					from v_EvnStatus 
					where EvnStatus_SysNick = 'FuncDonerec'
					limit 1
				)
					then to_char(efr.EvnFuncRequest_statusDate, 'dd.mm.yyyy')
					else null
				end as TimetableResource_begDate,
				efr.TimetableResource_Date,
				efr.Resource_id,
				efr.Resource_Name,
				coalesce(to_char(efr.TimetableResource_begTime, 'hh24:mi'), 'б/з') as TimetableResource_begTime,
				to_char(efr.TimetableResource_begTime, 'dd.mm.yyyy') as TimetableResource_Type,
				case when 2 = coalesce(EFR.EvnPrescrFuncDiag_IsCito, 1)
					then 'true'
					else 'false'
				end as EvnDirection_IsCito,
				to_char(EFR.EvnDirection_setDT, 'dd.mm.yyyy') as EvnDirection_setDT,
				EFR.EvnDirection_Num,
				EvnUslugaPar.EvnUslugaPar_id,
				to_char(EvnUslugaPar.EvnUslugaPar_setDate, 'dd.mm.yyyy') as EvnUslugaPar_setDate,
				MSL.MedService_id as RCC_MedService_id,
				RCCR.RemoteConsultCenterResearch_id,
				RCCR.RemoteConsultCenterResearch_status,
				case when EFR.EvnStatus_id = (
					select
						EvnStatus_id 
					from v_EvnStatus 
					where EvnStatus_SysNick = 'FuncDonerec'
					limit 1
				)
					then 'true'
					else 'false'
				end as FuncRequestState,
				case when PUC.PMUser_surName is null
					then ''
					else PUC.PMUser_surName
						|| ' ' || coalesce(left(PUC.PMUser_firName, 1), '')
						|| ' ' || coalesce(left(PUC.PMUser_secName, 1), '')
				end as Operator,
				efr.EvnFuncRequest_UslugaCache,
				LpuFrom.Lpu_Nick as Lpu_Name,
				LpuSectionFrom.LpuSection_Name as LpuSection_Name,
				case when NoPrintCount.NPC > 0
					then 'true'
					else 'false'
				end as EvnCostPrint_PrintStatus
			FROM table1 efr
				left join v_Lpu LpuFrom on LpuFrom.Lpu_id = efr.Lpu_sid
				left join v_LpuSection LpuSectionFrom on LpuSectionFrom.LpuSection_id = efr.LpuSection_id
				inner join v_PersonState PS on PS.Person_id = efr.Person_id
				left join v_Person pers on pers.Person_id = efr.Person_id
				left join v_PersonEncrypHIV peh on peh.Person_id = PS.Person_id
				left join lateral(
					SELECT
						EvnUslugaPar_id,
						EvnUslugaPar_setDate
					from v_EvnUslugaPar
					where EvnDirection_id = efr.EvnDirection_id
					limit 1
				) EvnUslugaPar on true
				left JOIN v_RemoteConsultCenterResearch RCCR on RCCR.EvnUslugaPar_id = EvnUslugaPar.EvnUslugaPar_id
				LEFT JOIN v_MedServiceLink MSL on MSL.MedService_lid = :MedService_lid
					AND MSL.MedServiceLinkType_id = (
						select
							MSLT.MedServiceLinkType_id
						from v_MedServiceLinkType MSLT
						where MSLT.MedServiceLinkType_Code = :MedServiceLinkType_Code
						limit 1
					)
				left JOIN v_pmUserCache PUC on PUC.PMUser_id = efr.pmUser_insID
				left join lateral(
					select
						ECP.EvnCostPrint_id as NPC
					from v_EvnUslugaPar EUP
						left join v_EvnCostPrint ECP ON ECP.Evn_id = EUP.EvnUslugaPar_id
					where EUP.EvnDirection_id = efr.EvnFuncRequest_pid
						and coalesce(ECP.EvnCostPrint_IsNoPrint,0) <> 2
					limit 1
				) NoPrintCount on true				
				left join v_Evn EPC on EPC.Evn_id = efr.EvnDirection_pid
			WHERE (1=1)
				{$person_filter}
		";

		$requestsResult = $this->queryResult($requestsQuery, $queryParams);
		$response = array_merge($queuedResult,$requestsResult);
		return $response;
	}

	/**
	 * Запрос заявок в очереди
	 */
	function loadEvnFuncQueueRequestList($data) {
		$commonFilters = array();
		$EFR_EQ_Filters = array();
		$EFRFilters = array();
		$queryParams = array();
		$TTRFilters = array();

		$TTRFilters[] = "R.MedService_id = :MedService_id"; // Направление в эту определенную службу

		$queryParams['Lpu_id'] = $data['Lpu_id'];
		$queryParams['MedService_id'] = $data['MedService_id'];

		if ( !empty($data['Search_LpuId']) ) {
			$commonFilters[] = "Lpu_sid = :Search_LpuId";
			$queryParams['Search_LpuId'] = $data['Search_LpuId'];
		}

		if ( !empty($data['EvnDirection_Num']) ) {
			$commonFilters[] = "ED.EvnDirection_Num = :EvnDirection_Num";
			$queryParams['EvnDirection_Num'] = $data['EvnDirection_Num'];
		}

		// Фильтр по услугам
		if ( !empty($data['UslugaComplex_id']) ) {
			$commonFilters[] = "exists (
				select
					t1.UslugaComplex_id
				from 
					EvnUsluga t1
					inner join Evn on t1.EvnUsluga_id = Evn.Evn_id and Evn.Evn_deleted = 1 and EvnClass_id = 47
				where 
					t1.EvnDirection_id = ED.EvnDirection_id
					and t1.UslugaComplex_id = :UslugaComplex_id
				limit 1
			)";
			$queryParams['UslugaComplex_id'] = $data['UslugaComplex_id'];
		}
		
		if ( !empty($data['EvnDirection_IsCito']) ) {
			$commonFilters[] = "coalesce(ED.EvnDirection_IsCito, 1) = :EvnDirection_IsCito";
			$queryParams['EvnDirection_IsCito'] = $data['EvnDirection_IsCito'];
		}

		if ( !empty($data['begDate']) && !empty($data['endDate']) ) {
			//записанные отображаются в тот день, на который они записаны #11499
			//те, кто в очереди отображаются в тот день, когда они направлены
			$TTRFilters[] = "(
				(TTR.TimetableResource_Day is not null and TTR.TimetableResource_Day between :begDay_id and :endDay_id)
			)";
			$EFR_EQ_Filters[] = "(
				efr.EvnStatus_id = (select EvnStatus_id from v_EvnStatus where EvnStatus_SysNick = 'FuncNew' limit 1)
				OR
				(ES.EvnStatus_SysNick IN ('FuncDonerec', 'FuncDone') and CAST(efr.evnfuncrequest_statusdate as date) between :begDate and :endDate)
			)";
			$EFRFilters[] = "(CAST(efr.evnfuncrequest_setdt as date) between :begDate and :endDate)";
			$this->load->helper('Reg');
			$queryParams['begDay_id'] = TimeToDay(strtotime($data['begDate']));
			$queryParams['endDay_id'] = TimeToDay(strtotime($data['endDate']));
			$queryParams['begDate'] = $data['begDate'];
			$queryParams['endDate'] = $data['endDate'];
		}
		
		$queryParams['MedService_lid'] = (!empty($data['session']['CurMedService_id']))?$data['session']['CurMedService_id']:null;
		$queryParams['MedServiceLinkType_Code'] = '3';

		$selectPersonData1 = "dbo.getPersonPhones(table2.Person_id, '<br />') as Person_Phone,
				(PS.Person_SurName || ' ' || coalesce(PS.Person_FirName, '') || ' ' || coalesce(PS.Person_SecName,'')) as Person_FIO,
				to_char(PS.Person_BirthDay, 'dd.mm.yyyy') as Person_BirthDay,";
		$selectPersonData2 = "dbo.getPersonPhones(table1.Person_id, '<br />') as Person_Phone,
				(PS.Person_SurName || ' ' || coalesce(PS.Person_FirName, '') || ' ' || coalesce(PS.Person_SecName,'')) as Person_FIO,
				to_char(PS.Person_BirthDay, 'dd.mm.yyyy') as Person_BirthDay,";
		$joinPersonEncrypHIV = "";
		if (allowPersonEncrypHIV($data['session'])) {
			$joinPersonEncrypHIV = "left join v_PersonEncrypHIV PEH on PEH.Person_id = PS.Person_id";
			$selectPersonData1 = "case when PEH.PersonEncrypHIV_Encryp is null then dbo.getPersonPhones(table2.Person_id, '<br />') else null end as Person_Phone,
				case when PEH.PersonEncrypHIV_Encryp is null then (PS.Person_SurName || ' ' || coalesce(PS.Person_FirName, '') || ' ' || coalesce(PS.Person_SecName,'')) else PEH.PersonEncrypHIV_Encryp end as Person_FIO,
				case when PEH.PersonEncrypHIV_Encryp is null then to_char(PS.Person_BirthDay, 'dd.mm.yyyy') else null end as Person_BirthDay,";
			$selectPersonData2 = "case when PEH.PersonEncrypHIV_Encryp is null then dbo.getPersonPhones(table1.Person_id, '<br />') else null end as Person_Phone,
				case when PEH.PersonEncrypHIV_Encryp is null then (PS.Person_SurName || ' ' || coalesce(PS.Person_FirName, '') || ' ' || coalesce(PS.Person_SecName,'')) else PEH.PersonEncrypHIV_Encryp end as Person_FIO,
				case when PEH.PersonEncrypHIV_Encryp is null then to_char(PS.Person_BirthDay, 'dd.mm.yyyy') else null end as Person_BirthDay,";
		}
		
		$EFRFilters = array_merge($EFRFilters, $commonFilters);
		$EFR_EQ_Filters = array_merge($EFR_EQ_Filters, $commonFilters);

		$commonFilters = array();
		if ( !empty($data['Search_SurName']) ) {
			if (allowPersonEncrypHIV($data['session']) && isSearchByPersonEncrypHIV($data['Search_SurName'])) {
				$commonFilters[] = "PEH.PersonEncrypHIV_Encryp ilike :Search_SurName";
			} else {
				$commonFilters[] = "PS.Person_SurName ilike (:Search_SurName||'%')";
			}
			$queryParams['Search_SurName'] = rtrim($data['Search_SurName']);
		}

		if ( !empty($data['Search_FirName']) ) {
			$commonFilters[] = "PS.Person_FirName ilike (:Search_FirName||'%')";
			$queryParams['Search_FirName'] = rtrim($data['Search_FirName']);
		}

		if ( !empty($data['Search_SecName']) ) {
			$commonFilters[] = "PS.Person_SecName ilike (:Search_SecName||'%')";
			$queryParams['Search_SecName'] = rtrim($data['Search_SecName']);
		}

		if ( !empty($data['Search_BirthDay']) ) {
			$commonFilters[] = "PS.Person_BirthDay = :Search_BirthDay";
			$queryParams['Search_BirthDay'] = $data['Search_BirthDay'];
		}

		if( !empty($data['Person_id'])) {
			$commonFilters[] = "PS.Person_id = :Person_id";
			$queryParams['Person_id'] = $data['Person_id'];
		}

		if( !empty($data['Search_PersonInn']) || $data['Search_PersonInn'] == '0') {
			$personFilters[] = "PS.Person_Inn LIKE ':Search_PersonInn%'";
			$queryParams['Search_PersonInn'] = $data['Search_PersonInn'];
		}

		// 2a. запрос по заявкам (все из EvnFuncRequest) не записанные (из очереди и остальные)
		$query = "
			-- addit with
			with table1 as (
				SELECT
					NULL as TimetableResource_id,
					ED.EvnDirection_id,
					EPC.EvnClass_SysNick as parentEvnClass_SysNick,
					null as EvnQueue_id,
					EFR.EvnFuncRequest_id,
					dbo.getPersonPhones(ed.Person_id, '<br />') as Person_Phone,
					to_char(ED.EvnDirection_setDT, 'dd.mm.yyyy') as group_name,
					case when 2 = coalesce(ED.EvnDirection_IsCito, 1) then 'true' else 'false' end as EvnDirection_IsCito,
					to_char(ED.EvnDirection_setDT, 'dd.mm.yyyy') as EvnDirection_setDT,
					ED.EvnDirection_Num,
					efr.EvnFuncRequest_UslugaCache,
                    efr.EvnStatus_id,
                    efr.Person_id,
                    efr.pmUser_insID
				FROM
					v_EvnFuncRequest EFR 
					inner JOIN v_EvnDirection_all ED ON ed.EvnDirection_id = EFR.EvnFuncRequest_pid and coalesce(ed.EvnStatus_id, 16) not in (12, 13)
					left join v_Evn EPC on EPC.Evn_id = ED.EvnDirection_pid
				WHERE
					efr.Lpu_id = :Lpu_id
					and efr.MedService_id = :MedService_id
					and ED.TimetableResource_id is null -- без бирки
					and ED.EvnQueue_id is null -- без очереди
					" . (count($EFRFilters) > 0 ? "and " . implode(' and ', $EFRFilters) : "") . "
			), table2 as (
				SELECT
					ED.EvnDirection_id,
					EPC.EvnClass_SysNick as parentEvnClass_SysNick,
					ED.EvnQueue_id as EvnQueue_id,
					EFR.EvnFuncRequest_id,
					dbo.getPersonPhones(ed.Person_id, '<br />') as Person_Phone,
					case when 2 = coalesce(ED.EvnDirection_IsCito, 1) then 'true' else 'false' end as EvnDirection_IsCito,
					to_char(ED.EvnDirection_setDT, 'dd.mm.yyyy') as EvnDirection_setDT,
					ED.EvnDirection_Num,
					efr.EvnFuncRequest_UslugaCache,
					ed.Lpu_sid,
					ed.LpuSection_id,
					ed.Person_id,
					efr.EvnFuncRequest_pid as EvnFuncRequest_pid,
					ed.pmUser_insID,
					es.EvnStatus_SysNick,
					efr.EvnFuncRequest_statusDate as EvnFuncRequest_statusDate
				FROM
					v_EvnFuncRequest efr
					inner join v_EvnDirection_all ed on ed.EvnDirection_id = EFR.EvnFuncRequest_pid and coalesce(ed.EvnStatus_id, 16) not in (12, 13)
					left join v_EvnStatus ES on ES.EvnStatus_id = efr.EvnStatus_id
					left join v_Evn EPC on EPC.Evn_id = ED.EvnDirection_pid
				WHERE 
					efr.MedService_id = :MedService_id
					" . (count($EFR_EQ_Filters) > 0 ? "and " . implode(' and ', $EFR_EQ_Filters) : "") . "
			)
			-- end addit with
			
			select
				-- select
				TimetableResource_id as \"TimetableResource_id\",
				EvnDirection_id as \"EvnDirection_id\",
				parentEvnClass_SysNick as \"parentEvnClass_SysNick\",
				EvnQueue_id as \"EvnQueue_id\",
				EvnFuncRequest_id as \"EvnFuncRequest_id\",
				Person_id as \"Person_id\",
				PersonQuarantine_IsOn as \"PersonQuarantine_IsOn\",
				Person_FIO as \"Person_FIO\",
				Person_BirthDay as \"Person_BirthDay\",
				group_name as \"group_name\",
				TimetableResource_begDate as \"TimetableResource_begDate\",
				Resource_id as \"Resource_id\",
				Resource_Name as \"Resource_Name\",
				TimetableResource_begTime as \"TimetableResource_begTime\",
				TimetableResource_Type as \"TimetableResource_Type\",
				EvnDirection_IsCito as \"EvnDirection_IsCito\",
				EvnDirection_setDT as \"EvnDirection_setDT\",
				EvnDirection_Num as \"EvnDirection_Num\",
				EvnUslugaPar_id as \"EvnUslugaPar_id\",
				RCC_MedService_id as \"RCC_MedService_id\",
				RemoteConsultCenterResearch_id as \"RemoteConsultCenterResearch_id\",
				RemoteConsultCenterResearch_status as \"RemoteConsultCenterResearch_status\",
				FuncRequestState as \"FuncRequestState\",
				Operator as \"Operator\",
				EvnFuncRequest_UslugaCache as \"EvnFuncRequest_UslugaCache\",
				Lpu_Name as \"Lpu_Name\",
				LpuSection_Name as \"LpuSection_Name\",
				EvnCostPrint_PrintStatus as \"EvnCostPrint_PrintStatus\"
				-- end select
			from
				-- from
				(
				select
					TTR.TimetableResource_id,
					table2.EvnDirection_id,
					table2.parentEvnClass_SysNick,
					table2.EvnQueue_id,
					table2.EvnFuncRequest_id,
					PS.Person_id,
					case when exists(
						select * 
						from v_PersonQuarantine PQ
						where PQ.Person_id = PS.Person_id
						and PQ.PersonQuarantine_endDT is null
					) then 'true' else 'false' end as PersonQuarantine_IsOn,
					{$selectPersonData1}
					null as group_name,
					case
						when table2.EvnStatus_SysNick = 'FuncDonerec' then to_char(cast(table2.EvnFuncRequest_statusDate as timestamp), 'dd.mm.yyyy')
						else null
					end as TimetableResource_begDate,
					r.Resource_id,
					r.Resource_Name,
					'б/з' as TimetableResource_begTime,
					null as TimetableResource_Type,
					table2.EvnDirection_IsCito,
					table2.EvnDirection_setDT,
					table2.EvnDirection_Num,
					EvnUslugaPar.EvnUslugaPar_id,
					wls.WorkListStatus_Name,
					MSL.MedService_id as RCC_MedService_id,
					RCCR.RemoteConsultCenterResearch_id,
					RCCR.RemoteConsultCenterResearch_status,
					case when table2.EvnStatus_SysNick = 'FuncDonerec' then 'true' else 'false' end as FuncRequestState,
					case
						when PUC.PMUser_surName is null then ''
						else PUC.PMUser_surName||' '||coalesce(left(PUC.PMUser_firName,1),'')||' '||coalesce(left(PUC.PMUser_secName,1),'')
					end as Operator,
					table2.EvnFuncRequest_UslugaCache,
					LpuFrom.Lpu_Nick as Lpu_Name,
					LpuSectionFrom.LpuSection_Name as LpuSection_Name,
					--'' as EvnCostPrint_PrintStatus,
					case when NoPrintCount.NPC > 0 then 'true' else 'false' end as EvnCostPrint_PrintStatus
				FROM
					table2 table2
					left join v_TimetableResource_lite TTR on TTR.EvnDirection_id = table2.EvnDirection_id
					left join v_Resource R on R.Resource_id = TTR.Resource_id
					left join v_Lpu LpuFrom on LpuFrom.Lpu_id = table2.Lpu_sid
					left join v_LpuSection LpuSectionFrom on LpuSectionFrom.LpuSection_id = table2.LpuSection_id
					left join v_PersonState PS on PS.Person_id = table2.Person_id
					{$joinPersonEncrypHIV}
					left join lateral(
						select ECP.EvnCostPrint_id as NPC
						from v_EvnUslugaPar EUP
						left join v_EvnCostPrint ECP on ECP.Evn_id = EUP.EvnUslugaPar_id
						where EUP.EvnDirection_id = table2.EvnFuncRequest_pid
						and coalesce(ECP.EvnCostPrint_IsNoPrint,0) <> 2
						limit 1
					) NoPrintCount on true
					left join lateral(
						select EvnUslugaPar_id from v_EvnUslugaPar where EvnDirection_id = table2.EvnDirection_id limit 1
					) EvnUslugaPar on true
					LEFT JOIN v_RemoteConsultCenterResearch RCCR on RCCR.EvnUslugaPar_id = EvnUslugaPar.EvnUslugaPar_id
					left join v_WorkListQueue wlq on EvnUslugaPar.EvnUslugaPar_id = wlq.EvnUslugaPar_id
					left join v_WorkListStatus wls on wlq.WorkListStatus_id = wls.WorkListStatus_id
					LEFT JOIN v_MedServiceLink MSL on MSL.MedService_lid = :MedService_lid 
                    AND MSL.MedServiceLinkType_id = (select MSLT.MedServiceLinkType_id from v_MedServiceLinkType MSLT where MSLT.MedServiceLinkType_Code = :MedServiceLinkType_Code limit 1)
					LEFT JOIN v_pmUserCache PUC on PUC.PMUser_id = table2.pmUser_insID
					inner join lateral(SELECT 1 AS Eq from v_EvnQueue eq where eq.EvnDirection_id = table2.EvnDirection_id and eq.EvnQueue_failDT is null and eq.EvnQueue_recDT is NULL limit 1) AS EQ on true
				WHERE 
					r.Resource_Name is null
					and TTR.TimetableResource_id is null
					" . (count($commonFilters) > 0 ? "and " . implode(' and ', $commonFilters) : "") . "
	
				union all
				
				-- без направления (без EvnDirection, связь с услугой была по EvnFuncRequest_pid, но скриптом создали направления без объекта очереди и без объекта бирки...)
				select
					NULL as TimetableResource_id,
					table1.EvnDirection_id,
					table1.parentEvnClass_SysNick,
					null as EvnQueue_id,
					table1.EvnFuncRequest_id,
					PS.Person_id,
					case when exists(
						select * 
						from v_PersonQuarantine PQ
						where PQ.Person_id = PS.Person_id
						and PQ.PersonQuarantine_endDT is null
					) then 'true' else 'false' end as PersonQuarantine_IsOn,
					{$selectPersonData2}
					table1.group_name,
					case when EvnUslugaPar.EvnUslugaPar_setDT is not null
						then to_char(cast(EvnUslugaPar.EvnUslugaPar_setDT as timestamp), 'dd.mm.yyyy')
						else null
					end as TimetableResource_begDate,
					null as Resource_id,
					'' as Resource_Name,
					'б/н' as TimetableResource_begTime,
					null as TimetableResource_Type,
					table1.EvnDirection_IsCito,
					table1.EvnDirection_setDT,
					table1.EvnDirection_Num,
					EvnUslugaPar.EvnUslugaPar_id,
					wls.WorkListStatus_Name,
					MSL.MedService_id as RCC_MedService_id,
					RCCR.RemoteConsultCenterResearch_id,
					RCCR.RemoteConsultCenterResearch_status,
					case when EvnUslugaPar.EvnUslugaPar_setDT is not null then 'true' else 'false' end as FuncRequestState,
					case
						when UC.PMUser_surName is null then ''
						else UC.PMUser_surName||' '||coalesce(left(UC.PMUser_firName,1),'')||' '||coalesce(left(UC.PMUser_secName,1),'')
					end as Operator,
					table1.EvnFuncRequest_UslugaCache,
					'' as Lpu_Name,
					'' as LpuSection_Name,
					'' as EvnCostPrint_PrintStatus
				FROM
					table1 table1
					left join lateral(
						Select EvnUslugaPar_id, EvnUslugaPar_setDT from v_EvnUslugaPar EvnUslugaPar where EvnUslugaPar.EvnDirection_id = table1.EvnDirection_id limit 1
					) EvnUslugaPar on true
					LEFT JOIN v_EvnStatus ES on ES.EvnStatus_id = table1.EvnStatus_id
					LEFT JOIN v_PersonState PS on PS.Person_id = table1.Person_id
					{$joinPersonEncrypHIV}
					LEFT JOIN v_RemoteConsultCenterResearch RCCR on RCCR.EvnUslugaPar_id = EvnUslugaPar.EvnUslugaPar_id
					left join v_WorkListQueue wlq on EvnUslugaPar.EvnUslugaPar_id = wlq.EvnUslugaPar_id
					left join v_WorkListStatus wls on wlq.WorkListStatus_id = wls.WorkListStatus_id
					LEFT JOIN v_MedServiceLink MSL on MSL.MedService_lid = :MedService_lid 
                    AND MSL.MedServiceLinkType_id = (select MSLT.MedServiceLinkType_id from v_MedServiceLinkType MSLT where MSLT.MedServiceLinkType_Code = :MedServiceLinkType_Code limit 1)
					LEFT JOIN v_pmUserCache UC on UC.PMUser_id = table1.pmUser_insID
				WHERE
					1 = 1
					and EvnUslugaPar.EvnUslugaPar_id is not null
					" . (count($commonFilters) > 0 ? "and " . implode(' and ', $commonFilters) : "") . "
				) paging
				-- end from
			where
				-- where
				(1=1)
				-- end where
			order by
				-- order by
				paging.EvnDirection_setDT,
				paging.EvnDirection_id
				-- end order by
		";
		
		$response = array();

		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $queryParams);
		if (is_object($result))
		{
			$resp = $result->result('array');

			$count = count($resp);
			if (intval($data['start']) != 0 || $count >= intval($data['limit'])) { // считаем каунт только если записей не меньше, чем лимит или не первая страничка грузится
				$result_count = $this->db->query(getCountSQLPH($query), $queryParams);

				if (is_object($result_count)) {
					$cnt_arr = $result_count->result('array');
					$count = $cnt_arr[0]['cnt'];
					unset($cnt_arr);
				} else {
					$count = 0;
				}
			}

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
				$respone['total'] = $count;
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
		$TTRFilters = array();
		
		$TTRFilters[] = "(coalesce(res.MedService_id, UCMS.MedService_id) = :MedService_id)"; // Направление в эту определенную службу

		$queryParams['Lpu_id'] = $data['Lpu_id'];
		$queryParams['MedService_id'] = $data['MedService_id'];
		
		
		//$commonFilters[] = "UCMS.UslugaComplexMedService_id is not null";
		// Фильтр по услугам
		

		if ( !empty($data['begDate']) && !empty($data['endDate']) ) {
			//записанные отображаются в тот день, на который они записаны #11499
			//те, кто в очереди отображаются в тот день, когда они направлены
			$TTRFilters[] = "(
				(TTR.TimetableResource_Day is not null and TTR.TimetableResource_Day between :begDay_id and :endDay_id)
			)";
			$EFRWithDirFilters[] = "(
				(TTR.TimetableResource_Day is null and ES.EvnStatus_SysNick <> 'FuncDonerec')
				OR
				(TTR.TimetableResource_Day is null and ES.EvnStatus_SysNick = 'FuncDonerec' and CAST(efr.EvnFuncRequest_statusDate as date) between :begDate and :endDate)
				OR
				(TTR.TimetableResource_Day between :begDay_id and :endDay_id)
			)";
			$EFRFilters[] = "(CAST(coalesce(ed.EvnDirection_setDT, EFR.EvnFuncRequest_setDT) as date) between :begDate and :endDate)";
			$this->load->helper('Reg');
			$queryParams['begDay_id'] = TimeToDay(strtotime($data['begDate']));
			$queryParams['endDay_id'] = TimeToDay(strtotime($data['endDate']));
			$queryParams['begDate'] = $data['begDate'];
			$queryParams['endDate'] = $data['endDate'];
		}
		
		$queryParams['MedService_lid'] = (!empty($data['session']['CurMedService_id']))?$data['session']['CurMedService_id']:null;
		$queryParams['MedServiceLinkType_Code'] = '3';
		
		
		
		// $TTRFilters = array_merge($TTRFilters, $commonFilters);
		$EFRFilters = array_merge($EFRFilters, $commonFilters);
		$EFRWithDirFilters = array_merge($EFRWithDirFilters, $commonFilters);

		$querys = array();

		// 1. запрос по биркам (все пустые без направлений)
		$querys[0] = "
			select
				TTR.TimetableResource_id as \"TimetableResource_id\",
				null as \"EvnDirection_id\",
				null as \"EvnQueue_id\",
                null as \"Person_Phone\",
				null as \"EvnFuncRequest_id\",
				to_char(TTR.TimetableResource_begTime, 'dd.mm.yyyy') as \"group_name\",
				case when TTR.TimetableResource_begTime is not null
					then to_char(cast(TTR.TimetableResource_begTime as timestamp), 'dd.mm.yyyy')
					else null
				end as \"TimetableResource_begDate\",
				coalesce(to_char(TTR.TimetableResource_begTime, 'HH24:MI:SS'),'б/з') as \"TimetableResource_begTime\",
				to_char(TTR.TimetableResource_begTime, 'dd.mm.yyyy') as \"TimetableResource_Type\",
				'false' as \"EvnDirection_IsCito\",
				null as \"EvnDirection_setDT\",
				null as \"EvnDirection_Num\",	
				null as \"Person_id\",
				null as \"PersonEvn_id\",
				null as \"EvnPrescr_id\",
				null as \"Server_id\",
				null as \"EvnUslugaPar_id\",
				TTR.TimetableResource_Time as \"TimetableResource_Time\",
				UCMS.UslugaComplex_id as \"UslugaComplex_id\",
				MSL.MedService_id as \"RCC_MedService_id\",
				null as \"RemoteConsultCenterResearch_id\",
				null as \"RemoteConsultCenterResearch_status\",
				'' as \"Person_FIO\",
				null as \"Person_BirthDay\",
				'false' as \"FuncRequestState\",
				'' as \"Operator\",
				coalesce(UCMS.UslugaComplexMedService_id,1) as \"UslugaComplexMedService_id\",
				res.Resource_Name as \"TimetableResourceType\",
				'' as \"EvnFuncRequest_UslugaCache\",
				'' as \"Lpu_Name\",
				'' as \"LpuSection_Name\"
			FROM v_TimetableResource_lite TTR
				left join v_Resource res on res.Resource_id= TTR.Resource_id
			left join v_UslugaComplexResource ucr on ucr.Resource_id= TTR.Resource_id
			LEFT JOIN v_UslugaComplexMedService UCMS on UCMS.UslugaComplexMedService_id = ucr.UslugaComplexMedService_id
				left join v_UslugaComplex UC on UC.UslugaComplex_id = UCMS.UslugaComplex_id
				LEFT JOIN v_MedService MS ON MS.MedService_id = coalesce(res.MedService_id, UCMS.MedService_id)
				LEFT JOIN v_MedServiceLink MSL on MSL.MedService_lid = :MedService_lid AND MSL.MedServiceLinkType_id = (select MSLT.MedServiceLinkType_id from v_MedServiceLinkType MSLT where MSLT.MedServiceLinkType_Code = :MedServiceLinkType_Code limit 1)
				--LEFT JOIN v_pmUserCache PUC on PUC.PMUser_id = TTR.pmUser_insID
			WHERE 
				" . implode(' and ', $TTRFilters) . "
				and TTR.EvnDirection_id is null  and UCMS.UslugaComplexMedService_id is not null
				
		";

		// 2. запрос по заявкам (все из EvnFuncRequest)
		$querys[0] .= "
			union all
			select
				TTR.TimetableResource_id as \"TimetableResource_id\",
				ED.EvnDirection_id as \"EvnDirection_id\",
				ED.EvnQueue_id as \"EvnQueue_id\",
				dbo.getPersonPhones(ed.Person_id, '<br />') as \"Person_Phone\",
				EFR.EvnFuncRequest_id as \"EvnFuncRequest_id\",
				to_char(TTR.TimetableResource_begTime, 'dd.mm.yyyy') as \"group_name\",
				case when TTR.TimetableResource_begTime is not null
					then to_char(cast(TTR.TimetableResource_begTime as timestamp), 'dd.mm.yyyy')
				end as \"TimetableResource_begDate\",
				coalesce(to_char(TTR.TimetableResource_begTime, 'HH24:MI:SS'),'б/з') as \"TimetableResource_begTime\",
				to_char(TTR.TimetableResource_begTime, 'dd.mm.yyyy') as \"TimetableResource_Type\",
				case when 2 = COALESCE(ed.EvnDirection_IsCito,epfd.EvnPrescrFuncDiag_IsCito, 1) then 'true' else 'false' end as \"EvnDirection_IsCito\",
				to_char(ED.EvnDirection_setDT, 'dd.mm.yyyy') as \"EvnDirection_setDT\",
				ED.EvnDirection_Num as \"EvnDirection_Num\",	
				ED.Person_id as \"Person_id\",
				ED.PersonEvn_id as \"PersonEvn_id\",
				ED.Server_id as \"Server_id\",
				EPD.EvnPrescr_id as \"EvnPrescr_id\",
				EvnUslugaPar.EvnUslugaPar_id as \"EvnUslugaPar_id\",
				TTR.TimetableResource_Time as \"TimetableResource_Time\",
				UCMS.UslugaComplex_id as \"UslugaComplex_id\",
				MSL.MedService_id as \"RCC_MedService_id\",
				RCCR.RemoteConsultCenterResearch_id as \"RemoteConsultCenterResearch_id\",
				RCCR.RemoteConsultCenterResearch_status as \"RemoteConsultCenterResearch_status\",
				coalesce((PS.Person_SurName || ' ' || coalesce(PS.Person_FirName, '') || ' ' || coalesce(PS.Person_SecName,'')),'') as \"Person_FIO\",
				to_char(PS.Person_BirthDay, 'dd.mm.yyyy') as \"Person_BirthDay\",
				case when ES.EvnStatus_SysNick = 'FuncDonerec' then 'true' else 'false' end as \"FuncRequestState\",
				case
					when PUC.PMUser_surName is null then ''
					else PUC.PMUser_surName||' '||coalesce(left(PUC.PMUser_firName,1),'')||' '||coalesce(left(PUC.PMUser_secName,1),'')
				end as \"Operator\",
				coalesce(UCMS.UslugaComplexMedService_id,1) as \"UslugaComplexMedService_id\",
				res.Resource_Name as \"TimetableResourceType\",
				efr.EvnFuncRequest_UslugaCache as \"EvnFuncRequest_UslugaCache\",
				LpuFrom.Lpu_Nick as \"Lpu_Name\",
				LpuSectionFrom.LpuSection_Name as \"LpuSection_Name\"
			FROM v_EvnFuncRequest efr
				inner join v_EvnDirection_all ed on ed.EvnDirection_id = efr.EvnFuncRequest_pid and (ed.TimetableResource_id is not null or ed.EvnQueue_id is not null) -- только записанные или из очереди
				left join v_EvnPrescrDirection epd on ed.EvnDirection_id = epd.evnDirection_id
				left join v_EvnPrescrFuncDiag epfd on epfd.EvnPrescrFuncDiag_id = epd.EvnPrescr_id
				left join v_Lpu LpuFrom on LpuFrom.Lpu_id = ED.Lpu_id
				left join v_LpuSection LpuSectionFrom on LpuSectionFrom.LpuSection_id = ED.LpuSection_id
				left join v_PersonState PS on PS.Person_id = ED.Person_id
				left join v_TimetableResource_lite TTR on TTR.EvnDirection_id = ed.EvnDirection_id
				left join v_Resource res on res.Resource_id= TTR.Resource_id
			left join v_UslugaComplexResource ucr on ucr.Resource_id= TTR.Resource_id
			LEFT JOIN v_UslugaComplexMedService UCMS on UCMS.UslugaComplexMedService_id = ucr.UslugaComplexMedService_id
				left join v_UslugaComplex UC on UC.UslugaComplex_id = UCMS.UslugaComplex_id
				left join v_EvnQueue eq on eq.EvnQueue_id = ed.EvnQueue_id
				left join v_EvnStatus ES on ES.EvnStatus_id = EFR.EvnStatus_id
				left join lateral(
					select EvnUslugaPar_id from v_EvnUslugaPar where EvnDirection_id = ED.EvnDirection_id limit 1
				) EvnUslugaPar on true
				LEFT JOIN v_RemoteConsultCenterResearch RCCR on RCCR.EvnUslugaPar_id = EvnUslugaPar.EvnUslugaPar_id
				LEFT JOIN v_MedServiceLink MSL on MSL.MedService_lid = :MedService_lid AND MSL.MedServiceLinkType_id = (select MSLT.MedServiceLinkType_id from v_MedServiceLinkType MSLT where MSLT.MedServiceLinkType_Code = :MedServiceLinkType_Code limit 1)
				LEFT JOIN v_pmUserCache PUC on PUC.PMUser_id = ed.pmUser_insID
			WHERE 
				efr.MedService_id = :MedService_id and eq.EvnQueue_recDT is null and UCMS.UslugaComplexMedService_id is not null
				" . (count($EFRWithDirFilters) > 0 ? "and " . implode(' and ', $EFRWithDirFilters) : "") . "
					order by UslugaComplexMedService_id, TimetableResource_begTime
		";
		
		
		
		$response = array();
		
		$arr=array();
		$response['data']=array();
		foreach($querys as $query) {
			//echo getDebugSQL($query, $queryParams);
			$res = $this->db->query($query, $queryParams);
			if ( is_object($res) ) {
				$resp = $res->result('array');
				foreach($resp as $respone) {
					if(!in_array($respone['TimetableResourceType'], $arr)){
						$arr[(string)$respone['UslugaComplexMedService_id']]=$respone['TimetableResourceType'];

					}
					if (empty($respone['EvnFuncRequest_UslugaCache'])) {
						$respone['EvnFuncRequest_UslugaCache'] = $this->getEvnFuncRequestUslugaList($respone);
					}

					$response['data'][] = $respone;
				}
			}
		}
		if(count($arr)>0){
			$response['tpl']=$arr;
		}
		//print_r($response);
		return $response;
	}
	/**
	 * 
	 * @param type $data
	 * @return boolean
	 */
	function getEvnProcRequest($data) {
		$query = "
			select
				EFR.EvnFuncRequest_id as \"EvnFuncRequest_id\",
				ED.EvnDirection_id as \"EvnDirection_id\",
				TTR.TimetableResource_id as \"TimetableResource_id\",
				ED.Server_id as \"Server_id\",
				ED.Person_id as \"Person_id\",
				ED.PersonEvn_id as \"PersonEvn_id\",
				to_char(ED.EvnDirection_setDT, 'dd.mm.yyyy') as \"EvnDirection_setDT\",
				ED.EvnDirection_Num as \"EvnDirection_Num\",
				coalesce(ED.PrehospDirect_id, EUP.PrehospDirect_id) as \"PrehospDirect_id\",
				coalesce(ED.Lpu_sid, EUP.Lpu_did) AS \"Lpu_sid\",
				coalesce(ED.LpuSection_id, EUP.LpuSection_did) as \"LpuSection_id\",
				coalesce(ED.MedPersonal_id, EUP.MedPersonal_did) as \"MedPersonal_id\",
				ED.Org_sid as \"Org_sid\",
				case when 2 = coalesce(ED.EvnDirection_IsCito, 1) then 'true' else 'false' end as \"EvnDirection_IsCito\",
				coalesce(EFR.PayType_id, EUP.PayType_id) as \"PayType_id\",
				EUP.UslugaComplex_id as \"UslugaComplex_id\",
				EUP.EvnUslugaPar_id as \"EvnUslugaPar_id\",
				CASE WHEN EUP.EvnUslugaPar_setDate IS NULL THEN 0 ELSE 1 END as \"disabled\",
				EPP.EvnPrescrProc_CountInDay as \"EvnPrescrProc_CountInDay\",
				EPP.EvnPrescrProc_CourseDuration as \"EvnPrescrProc_CourseDuration\",
				EPP.EvnPrescrProc_ContReception as \"EvnPrescrProc_ContReception\",
				EPP.EvnPrescrProc_Interval as \"EvnPrescrProc_Interval\",
				EPP.DurationType_id as \"DurationType_id\",
				EPP.DurationType_nid as \"DurationType_nid\",
				EPP.DurationType_sid as \"DurationType_sid\",
				EPP.EvnPrescrProc_id as \"EvnPrescrProc_id\"
			FROM v_EvnDirection_all ED
				LEFT JOIN v_EvnFuncRequest EFR ON ED.EvnDirection_id = EFR.EvnFuncRequest_pid
				left join lateral(
					select UslugaComplex_id, EvnUslugaPar_id, PayType_id, MedPersonal_did, LpuSection_did, Lpu_did, PrehospDirect_id, EvnUslugaPar_setDate from v_EvnUslugaPar EUPouter where ED.EvnDirection_id = EUPouter.EvnDirection_id limit 1
				) EUP on true
				left join lateral(
					select EvnPrescrProc_id,
						EC.EvnCourse_MaxCountDay as EvnPrescrProc_CountInDay,
						EC.EvnCourse_Duration as EvnPrescrProc_CourseDuration, 
						EC.EvnCourse_ContReception as EvnPrescrProc_ContReception,
						ec.EvnCourse_Interval as EvnPrescrProc_Interval,
						ec.DurationType_id,
						ec.DurationType_intid as DurationType_sid,
						ec.DurationType_recid as DurationType_nid

					from v_EvnPrescrProc EPPouter
					inner join v_EvnCourse EC on EPPouter.EvnCourse_id = EC.EvnCourse_id
					left join EvnPrescrDirection EPD on EPD.EvnPrescr_id = EPPouter.EvnPrescrProc_id
					where EPD.EvnDirection_id = ED.EvnDirection_id
					limit 1
				) EPP on true
				LEFT JOIN v_TimetableResource_lite TTR ON ED.EvnDirection_id = TTR.EvnDirection_id
			WHERE (EvnFuncRequest_id = :EvnFuncRequest_id or :EvnFuncRequest_id is null) and (ED.EvnDirection_id = :EvnDirection_id)
		";
		//echo getDebugSql($query, array('EvnFuncRequest_id' => $data['EvnFuncRequest_id'], 'EvnDirection_id' => $data['EvnDirection_id']));
		$res = $this->db->query($query, array('EvnFuncRequest_id' => $data['EvnFuncRequest_id'], 'EvnDirection_id' => $data['EvnDirection_id']));
	
		if ( is_object($res) ) {			
			return $res->result('array');
		} else {
			return false;
		}
	}
	/**
	 * 
	 * @param type $data
	 * @return boolean
	 */
	function getEvnFuncRequest($data) {

		$query = "
			select
				ED.PayType_id as \"EDPayType_id\",
				EFR.EvnFuncRequest_id as \"EvnFuncRequest_id\",
				EPD.EvnPrescr_id as \"EvnPrescr_id\",
				epfd.EvnPrescrFuncDiag_id as \"EvnPrescrFuncDiag_id\",
				ED.EvnDirection_id as \"EvnDirection_id\",
				TTR.TimetableResource_id as \"TimetableResource_id\",
				ED.Server_id as \"Server_id\",
				ED.Person_id as \"Person_id\",
				ED.PersonEvn_id as \"PersonEvn_id\",
				to_char(ED.EvnDirection_setDT, 'dd.mm.yyyy') as \"EvnDirection_setDT\",
				ED.EvnDirection_Num as \"EvnDirection_Num\",
				coalesce(ED.PrehospDirect_id, EUP.PrehospDirect_id) as \"PrehospDirect_id\",
				coalesce(ED.Lpu_sid, EUP.Lpu_did) AS \"Lpu_sid\",
				coalesce(ED.LpuSection_id, EUP.LpuSection_did) as \"LpuSection_id\",
				coalesce(ED.MedPersonal_id, EUP.MedPersonal_did) as \"MedPersonal_id\",
				ed.MedPersonal_Code as \"MedPersonal_Code\",
				coalesce(ed.Org_sid, Lpu.Org_id) AS \"Org_sid\",
				case when 2 = COALESCE(ED.EvnDirection_IsCito,epfd.EvnPrescrFuncDiag_IsCito, 1) then 'true' else 'false' end as \"EvnDirection_IsCito\",
				coalesce(epfd.EvnPrescrFuncDiag_Descr || '\n', '') || coalesce(ed.EvnDirection_Descr, '') as \"EvnDirection_Descr\",
				coalesce(EFR.PayType_id, EUP.PayType_id) as \"PayType_id\",
				EUP.UslugaComplex_id as \"UslugaComplex_id\",
				EUP.FSIDI_id as \"FSIDI_id\",
				case when 2 = coalesce(ECP.EvnCostPrint_IsNoPrint, 1) then 'true' else 'false' end as \"rejectionFlag\",
				to_char(ECP.EvnCostPrint_setDT, 'dd.mm.yyyy') as \"issueDate\",
				EUP.EvnUslugaPar_id as \"EvnUslugaPar_id\",
				CASE WHEN EvnUslugaPar_setDate IS NULL THEN 0 ELSE 1 END as \"disabled\",
				(SELECT msf.MedStaffFact_id FROM v_MedStaffFact msf WHERE ed.Post_id = msf.Post_id AND ed.MedPersonal_id = msf.MedPersonal_id and ed.LpuSection_id = msf.LpuSection_id limit 1) as \"MedStaffFact_id\",
				EFR.Diag_id as \"Diag_id\",
				coalesce(TTR.Resource_id, ED.Resource_id) as \"Resource_id\",
				EFR.EvnFuncRequest_Ward as \"EvnFuncRequest_Ward\",
				coalesce(ED.EvnDirection_IsReceive, 1) as \"EvnDirection_IsReceive\",
				ED.EvnDirection_IsAuto as \"EvnDirection_IsAuto\",
				coalesce(EFR.StudyTarget_id,ED.StudyTarget_id) as \"StudyTarget_id\",
				edPidClass.EvnClass_SysNick as \"parentEvnClass_SysNick\"
			FROM v_EvnDirection_all ED
				LEFT JOIN v_EvnFuncRequest EFR ON ED.EvnDirection_id = EFR.EvnFuncRequest_pid
				LEFT JOIN v_EvnPrescrDirection EPD on EPD.EvnDirection_id = ED.EvnDirection_id
				left join v_EvnPrescrFuncDiag epfd on epfd.EvnPrescrFuncDiag_id = epd.EvnPrescr_id
				LEFT JOIN v_EvnUslugaPar EUP on ED.EvnDirection_id = EUP.EvnDirection_id
				left join v_EvnCostPrint ECP on ECP.Evn_id = EUP.EvnUslugaPar_id
				left join v_Lpu Lpu on Lpu.Lpu_id = coalesce(ed.Lpu_sid, EUP.Lpu_did)
				LEFT JOIN v_TimetableResource_lite TTR ON ED.EvnDirection_id = TTR.EvnDirection_id
				left join v_Evn edPidClass on edPidClass.Evn_id = ED.EvnDirection_pid
			WHERE (EvnFuncRequest_id = :EvnFuncRequest_id or :EvnFuncRequest_id is null) and (ED.EvnDirection_id = :EvnDirection_id)
		";

		//getDebugSql($query, array('EvnFuncRequest_id' => $data['EvnFuncRequest_id'], 'EvnDirection_id' => $data['EvnDirection_id']));
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
					$uslugaList[$key]['disabled'] = intval($uslugaComplex['disabled']);
					$uslugaList[$key]['FSIDI_id'] = $uslugaComplex['FSIDI_id'];

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
			$response[0]['disabled'] = intval($response[0]['disabled']);

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
		
		$EvnDirection_IsAuto = null;

		if (!empty($data['EvnDirection_id'])) {

			$query = "
				select
					EvnDirection_IsAuto as \"EvnDirection_IsAuto\",
					PrehospDirect_id as \"PrehospDirect_id\"
				from
					v_EvnDirection_all
				where
					EvnDirection_id = :EvnDirection_id
			";

			$resp = $this->queryResult($query, array('EvnDirection_id' => $data['EvnDirection_id']));
			
			$EvnDirection_IsAuto = (empty($resp[0]['EvnDirection_IsAuto']))?null:$resp[0]['EvnDirection_IsAuto'];

			if (!empty($resp[0])
				&& $resp[0]['EvnDirection_IsAuto'] != 2
				&& $resp[0]['PrehospDirect_id'] == 1
			) { $hasAccessSaveEvnDirection = true; }
		}
        $this->beginTransaction();

		if (empty($data['EvnDirection_id']) || $hasAccessSaveEvnDirection) {
			$EvnDirectionData = $this->saveEvnDirection($data);
			
			if ( $EvnDirectionData ) {
				$EvnDirection_IsAuto = $EvnDirectionData['EvnDirection_IsAuto'];
			}
		} else {
			$EvnDirectionData = array('EvnDirection_id' => $data['EvnDirection_id']);
		}

		if ($EvnDirectionData) {
			
			$data['EvnDirection_id'] = $EvnDirectionData['EvnDirection_id'];

			// для 1 EvnDirection_id всегда должна быть 1 заявка EvnFuncRequest_id
			if (!empty($data['EvnDirection_id'])) {
				$data['EvnFuncRequest_id'] = $this->getFirstResultFromQuery('
					SELECT
						EvnFuncRequest_id as "EvnFuncRequest_id"
				 	FROM v_EvnFuncRequest
				 	WHERE EvnFuncRequest_pid = :EvnDirection_id
					limit 1
				 	',array('EvnDirection_id' => $data['EvnDirection_id'])
				);

				if (empty($data['EvnFuncRequest_id'])) {$data['EvnFuncRequest_id'] = null; }
			}

			$data['EvnStatus_id'] = null;
			$data['EvnFuncRequest_statusDate'] = null;

			if (!empty($data['EvnDirection_id'])) {

				$res = $this->db->query("
					select
						EvnStatus_id as \"EvnStatus_id\",
						to_char(EvnFuncRequest_statusDate, 'yyyy-mm-dd') as \"EvnFuncRequest_statusDate\"
					from
						v_EvnFuncRequest
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

					if (getRegionNick() == 'kz' && $data['OuterKzDirection'] && empty($data['EvnStatus_id'])) {
						$data['EvnStatus_id'] = 7;
						$data['EvnFuncRequest_statusDate'] = date('Y-m-d H:i:s.v');
					}
				}
			}

			$action = (!empty($data['EvnFuncRequest_id']) ? 'upd' : 'ins' );

			if (empty($data['EvnFuncRequest_id'])
				&& empty($data['Diag_id'])
				&& !empty($data['EvnDirection_id'])
			) {
				$Diag_id = $this->getFirstResultFromQuery("
					SELECT
						Diag_id as \"Diag_id\"
					FROM v_EvnDirection_all
					where Direction_id = :Direction_id
					limit 1
					", array('EvnDirection_id' => $data['EvnDirection_id'])
				);

				$data['Diag_id'] = (!empty($Diag_id) ? $Diag_id : null);
			}

			$sql = "
				with mv as (
					select
						case 
							when Evn_IsSigned IS NOT NULL then 1
							else null
						end as Evn_IsSigned
					from
						v_Evn
					where
						Evn_id = :EvnFuncRequest_id
					limit 1
				)
				
				select
					EvnFuncRequest_id as \"EvnFuncRequest_id\",
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from dbo.p_EvnFuncRequest_{$action}(
					EvnFuncRequest_id := :EvnFuncRequest_id, 
					EvnFuncRequest_pid := :EvnFuncRequest_pid,
					EvnFuncRequest_setDT :=  :EvnFuncRequest_setDT,
					EvnFuncRequest_Ward :=  :EvnFuncRequest_Ward,
					Lpu_id := :Lpu_id,
					MedService_id := :MedService_id,
					Server_id := :Server_id, 
					PersonEvn_id := :PersonEvn_id, 
					PayType_id := :PayType_id,
					Diag_id := :Diag_id,
					EvnStatus_id := :EvnStatus_id,
					EvnFuncRequest_statusDate := :EvnFuncRequest_statusDate,
					EvnFuncRequest_IsSigned := (select Evn_IsSigned from mv),
					StudyTarget_id := :StudyTarget_id,
					pmUser_id := :pmUser_id
				)
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

					if ( $EvnDirection_IsAuto == 2 ) {
						$updParams = [];
						switch ((int)$data['PrehospDirect_id']) {
							case 1: // 1 Отделение ЛПУ
							case 15: // Казахстан
							case 8: // 8 ПСМП Казахстан
								$updParams['Lpu_sid'] = $data['Lpu_id']; //Направившее ЛПУ
								$updParams['LpuSection_id'] = $data['LpuSection_id'];
								$updParams['MedStaffFact_id'] = $data['MedStaffFact_id'];
								//$updParams['Org_sid'] = $data['Lpu_id'];
								$updParams['Org_sid'] = $this->getFirstResultFromQuery("
									SELECT
										Org_id
									FROM
										v_Lpu
									WHERE
										Lpu_id = :Lpu_id
									LIMIT 1	
								",array(
									'Lpu_id' => $data['Lpu_id']
								));
								if ( !empty($data['MedStaffFact_id']) ) {
									$tmp = $this->db->query(
										"select Post_id as \"Post_id\",MedPersonal_id as \"MedPersonal_id\" from v_MedStaffFact where MedStaffFact_id = :MedStaffFact_id limit 1",
										[ 'MedStaffFact_id' => $data['MedStaffFact_id'] ]
									)->result('array');
									if ( !empty($tmp[0]) ) {
										$updParams['Post_id'] = $tmp[0]['Post_id'];
										$updParams['MedPersonal_id'] = $tmp[0]['MedPersonal_id'];
									}
								}
								break;
							case 2: // 2 Другое ЛПУ
							case 16: // Казахстан
							case 9: // 9 Казахстан КДП
								// по  $this->EvnDirection_Org_sid получаем Lpu_id
								$Lpu_id = $this->getFirstResultFromQuery("
									SELECT
										Lpu_id
									FROM
										v_Lpu
									WHERE
										Org_id = :Org_sid
									LIMIT 1	
								",array(
									'Org_sid' => $data['Org_sid']
								));
								$updParams['Lpu_sid'] = empty($Lpu_id)?$data['Lpu_id']:$Lpu_id;//Направившее ЛПУ
								$updParams['LpuSection_id'] = $data['LpuSection_id'];
								$updParams['MedStaffFact_id'] = $data['MedStaffFact_id'];
								$updParams['Org_sid'] = $data['Org_sid'];
								if ( !empty($data['MedStaffFact_id']) ) {
									$tmp = $this->db->query(
										"select Post_id as \"Post_id\",MedPersonal_id as \"MedPersonal_id\" from v_MedStaffFact where MedStaffFact_id = :MedStaffFact_id limit 1",
										[ 'MedStaffFact_id' => $data['MedStaffFact_id'] ]
									)->result('array');
									if ( !empty($tmp[0]) ) {
										$updParams['Post_id'] = $tmp[0]['Post_id'];
										$updParams['MedPersonal_id'] = $tmp[0]['MedPersonal_id'];
									}
								}
								break;
							case 3: // 3 Другая организация
							case 4: // 4 Военкомат
							case 5: // 5 Скорая помощь
							case 6: // 6 Администрация
							case 10: // Казахстан 3 Скорая помощь
							case 11: // Казахстан 4 Другой стационар
							case 12: // Казахстан 5 Военкомат
							case 13: // Казахстан 6 Роддом
							case 14: // Казахстан
							//case 15: // Казахстан
							//case 16: // Казахстан
								$updParams['Org_sid'] = $data['Org_sid'];
								$updParams['LpuSection_id'] = null;
								$updParams['MedStaffFact_id'] = null;
								break;
							case 7: // 7 Пункт помощи на дому
								break;
						}

						$updParams['PrehospDirect_id'] = $data['PrehospDirect_id'];
						$updParams['EvnDirection_IsCito'] = ($data['EvnDirection_IsCito']=='on')?2:1;
						$updParams['EvnDirection_Descr'] = (empty($data['EvnDirection_Descr']))?null:$data['EvnDirection_Descr'];

						$set = '';

						foreach ( $updParams as $key=>$value ) {
							$set = $set . "{$key} = :{$key},";
						}

						$set = rtrim( $set, ',' );

						$query = "
							update
								EvnDirection
							set
								{$set}
							where
								Evn_id = :EvnDirection_id
						";

						$updParams['EvnDirection_id'] = $data['EvnDirection_id'];

						//echo getDebugSQL($query, $updParams);
						$this->db->query($query, $updParams);
					}

					// обновляем вид оплаты в направлении, если оно с признаком "к себе"
					$query = "
						update
							EvnDirection
						set
							PayType_id = :PayType_id
						where
							Evn_id = :EvnDirection_id
							and EvnDirection_IsReceive = 2
					";

					$this->db->query($query, array(
						'PayType_id' => $data['PayType_id'],
						'EvnDirection_id' => $data['EvnDirection_id']
					));

					// обновляем вид оплаты в услугах
					$query = "
						update
							EvnUsluga
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

					$this->load->model('TimetableMedService_model', 'TTR_model');

					// записываем
					if (!empty($data['TimetableResource_id'])) {
						$data['Evn_id'] = $result[0]['EvnFuncRequest_id'];
						$data['object'] = 'TimetableResource';
						$this->TTR_model->Apply($data);
					}
				}



			} else { return false; }

			// рефакторинг февраль 2018, по задаче с зубами

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
								ECP.EvnCostPrint_id as \"EvnCostPrint_id\",
								ECP.Person_id as \"Person_id\",
								ECP.EvnCostPrint_Number as \"EvnCostPrint_Number\",
								ECP.EvnCostPrint_Cost as \"EvnCostPrint_Cost\"
							from v_EvnFuncRequest EFR
							left join v_EvnUslugaPar EUP on EUP.EvnDirection_id = EFR.EvnFuncRequest_pid
							left join v_EvnCostPrint ECP on ECP.Evn_id = EvnUslugaPar_id
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
								select
									EvnCostPrint_id as \"EvnCostPrint_id\",
									Error_Code as \"Error_Code\",
									Error_Message as \"Error_Msg\"
								from dbo.p_EvnCostPrint_upd(
									EvnCostPrint_id := :EvnCostPrint_id,
									Evn_id := :Evn_id,
									Person_id := :Person_id,
									EvnCostPrint_Number := :EvnCostPrint_Number,
									EvnCostPrint_setDT := :CostPrint_setDT,
									EvnCostPrint_IsNoPrint := :CostPrint_IsNoPrint,
									EvnCostPrint_Cost := :CostPrint_Cost,
									pmUser_id := :pmUser_id
								)
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

							$data['UslugaComplex_id'] = $usluga->UslugaComplex_id;
							$data['EvnRequest_id'] = $data['EvnFuncRequest_id'];
                            $data['FSIDI_id'] = $usluga->FSIDI_id;

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
	 * 
	 * @param type $data
	 * @return boolean
	 */
	function saveEvnProcRequest($data) {
		$this->load->helper('Reg');
		$EvnDirection = $this->saveEvnDirection($data);
		$EvnDirection_id = $EvnDirection['EvnDirection_id'];
		if ($EvnDirection_id) {
			$data = array_merge($EvnDirection,$data);
			$data['EvnDirection_id'] = $EvnDirection_id;
			
			if (!empty($data['EvnFuncRequest_id'])) {
				$insupd = 'upd';
			} else {
				$insupd = 'ins';
			}
			
			$data['EvnStatus_id'] = null;
			$data['EvnFuncRequest_statusDate'] = null;
			$result = $this->db->query("
				select
					EvnStatus_id as \"EvnStatus_id\",
					to_char(EvnFuncRequest_statusDate, 'yyyy-mm-dd') as \"EvnFuncRequest_statusDate\"
				from
					v_EvnFuncRequest
				where
					EvnFuncRequest_pid = :EvnDirection_id
			", array(
				'EvnDirection_id' => $data['EvnDirection_id']
			));

			if (is_object($result)) {
				$resp = $result->result('array');
				if (!empty($resp[0])) {
					// статусы не должны сбрасываться
					$data['EvnStatus_id'] = $resp[0]['EvnStatus_id'];
					$data['EvnFuncRequest_statusDate'] = $resp[0]['EvnFuncRequest_statusDate'];
				}
			}
			
			$sql = "
				SELECT
					EvnFuncRequest_id as \"EvnFuncRequest_id\",
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from dbo.p_EvnFuncRequest_{$insupd}(
					EvnFuncRequest_id := :EvnFuncRequest_id, 
					EvnFuncRequest_pid := :EvnFuncRequest_pid,
					EvnFuncRequest_setDT :=  :EvnFuncRequest_setDT,
					MedService_id := :MedService_id,
					Lpu_id := :Lpu_id, 
					Server_id := :Server_id, 
					PersonEvn_id := :PersonEvn_id, 
					PayType_id := :PayType_id,
					EvnStatus_id := :EvnStatus_id,
					EvnFuncRequest_statusDate := :EvnFuncRequest_statusDate,
					pmUser_id := :pmUser_id
				)
				";
		 
			$params = array(
				'EvnFuncRequest_id' => $data['EvnFuncRequest_id'],
				'EvnFuncRequest_pid' => $EvnDirection_id,
				'EvnFuncRequest_setDT' => $data['EvnDirection_setDT'],
				'Lpu_id'  => $data['Lpu_id'],
				'Server_id' => $data['Server_id'],
				'MedService_id' => (!empty($data['EvnFuncRequest_id']))?$data['MedService_id']:NULL,
				'PersonEvn_id' => $data['PersonEvn_id'],
				'PayType_id' => $data['PayType_id'],
				'EvnStatus_id' => $data['EvnStatus_id'],
				'EvnFuncRequest_statusDate' => $data['EvnFuncRequest_statusDate'],	
				'pmUser_id' => $data['pmUser_id']			
			);

			$res = $this->db->query($sql, $params);
			
			if (is_object($res)) {
				$trans_result = $res->result('array');
				$trans_good = true;
				
				if (!empty($data['TimetableResource_id'])) {
					$data['Evn_id'] = $trans_result[0]['EvnFuncRequest_id'];
					$data['object'] = 'TimetableResource';
					$this->load->model('TimetableMedService_model', 'TTR_model');
					$TTR = $this->TTR_model->Apply($data);
				}
			} 
			else 
			{
				$trans_good = false;
			}

			$uslugalist = array();
			
			if (!empty($data['UslugaComplex_id'])) {
				// очищаем все услуги и сохраняем только одну.
				$this->clearEvnFuncRequestUsluga(array('EvnDirection_id' => $EvnDirection_id, 'pmUser_id' => $data['pmUser_id']), array(), true);
				$uslugalist[] = $data['UslugaComplex_id'];
			}
			
			// сохраняем выполнение по свяанному назначению
			if (!empty($EvnDirection_id)) {
				$this->load->model('EvnPrescr_model', 'EvnPrescr_model');
				$this->EvnPrescr_model->saveEvnPrescrIsExec(array(
					'pmUser_id' => $data['pmUser_id'],
					'EvnDirection_id' => $EvnDirection_id,
					'EvnPrescr_IsExec' => (count($uslugalist)>0)?2:1
				));
			}
			
			if($trans_good === true && !empty($uslugalist))
			{
				foreach($uslugalist as $d)
				{
					$res = $this->saveEvnFuncRequestUsluga(
							array_merge (array('UslugaComplex_id' => $d), $data)
						);
					if(empty($res))
					{
						$trans_result[0]['Error_Msg'] = 'Ошибка запроса при сохранении услуги';
						$trans_good = false;
						break;
					}
					if(!empty($res) && !empty($res[0]) && !empty($res[0]['Error_Msg']))
					{
						$trans_result[0]['Error_Msg'] = $res[0]['Error_Msg'];
						$trans_good = false;
						break;
					}
				}
				
				// рекэш списка услуг по заявке
				$this->ReCacheFuncRequestUslugaCache(array(
					'MedService_id' => $data['MedService_id'],
					'EvnFuncRequest_id' => $trans_result[0]['EvnFuncRequest_id'],
					'EvnDirection_id' => $EvnDirection_id,
					'pmUser_id' => $data['pmUser_id']
				));
				
				$this->load->model('Evn_model', 'Evn_model');
				$this->Evn_model->updateEvnStatus(array(
					'Evn_id' => $trans_result[0]['EvnFuncRequest_id'],
					'EvnStatus_SysNick' => 'FuncDonerec',
					'EvnClass_SysNick' => 'EvnFuncRequest',
					'pmUser_id' => $data['pmUser_id']
				));
				$this->Evn_model->updateEvnStatus(array(
					'Evn_id' => $EvnDirection_id,
					'EvnStatus_SysNick' => 'Serviced',
					'EvnClass_SysNick' => 'EvnDirection',
					'pmUser_id' => $data['pmUser_id']
				));
			}
			
			return $trans_result;
			
		} else {
			return false;
		}
	}
	/**
	 * 
	 * @param type $data
	 * @return boolean
	 */
    private function saveEvnDirection($data)
    {
		$this->load->model('EvnDirection_model', 'EvnDirection_model');

		// если создаём заявку из АРМ, значит направление к себе.
		$data['EvnDirection_IsReceive'] = 2;
		if (!empty($data['EvnDirection_id'])) {
			// признак "К себе" оставляем как был.
			$resp = $this->queryResult("
				select
					EvnDirection_id as \"EvnDirection_id\",
					EvnDirection_IsReceive as \"EvnDirection_IsReceive\",
					EvnDirection_Num as \"EvnDirection_Num\",
					Diag_id as \"Diag_id\"
				from
					v_EvnDirection_all
				where
					EvnDirection_id = :EvnDirection_id
				limit 1
			", array(
				'EvnDirection_id' => $data['EvnDirection_id']
			));

			if (!empty($resp[0]['EvnDirection_id'])) {
				$data['EvnDirection_IsReceive'] = $resp[0]['EvnDirection_IsReceive'];
				$data['Diag_id'] = $resp[0]['Diag_id'];
				$data['EvnDirection_Num'] = empty($data['EvnDirection_Num']) ? $resp[0]['EvnDirection_Num'] : $data['EvnDirection_Num'];
			}
		}

		$params = array(
            'EvnDirection_id' => $data['EvnDirection_id'],
			'toQueue' => empty($data['TimetableResource_id'])?true:null,
            'Server_id' => $data['Server_id'],
            'PersonEvn_id' => $data['PersonEvn_id'],
            'EvnDirection_Num' => $data['EvnDirection_Num'],
			'TimetableResource_id' => !empty($data['TimetableResource_id'])?$data['TimetableResource_id']:null,
            'PrehospDirect_id' => $data['PrehospDirect_id'],
            'EvnDirection_setDT' => $data['EvnDirection_setDT'],
            'MedService_id' => $data['MedService_id'],
            'Resource_id' => !empty($data['Resource_id'])?$data['Resource_id']:null,
            'EvnDirection_IsCito' => ($data['EvnDirection_IsCito']=='on') ? '2' : '1',
            'EvnDirection_Descr' => !empty($data['EvnDirection_Descr'])?$data['EvnDirection_Descr']:null,
            'Lpu_id'  => $data['Lpu_id'],//ЛПУ, создавшее направление
            'Lpu_did' => $data['Lpu_id'],//ЛПУ, куда был направлен пациент
			'DirType_id' => 10,//тип направления: "На исследование"
			'EvnDirection_IsAuto' => 2,//Это системное направление, т.к. электронное направление может создать только врач
			'EvnDirection_IsReceive' => $data['EvnDirection_IsReceive'],
			'Diag_id' => $data['Diag_id'],
            'LpuSection_id' => null,//Направившее отделение
            'MedPersonal_id' => null,//Направивший врач
			'MedPersonal_Code' => isset($data['MedPersonal_Code'])?$data['MedPersonal_Code']:null,//Код врача
			'From_MedStaffFact_id' => null,//Направивший врач
            'Lpu_sid' => null,//Направившее ЛПУ
            'Org_sid' => null,//Направившая организация
            'pmUser_id' => $data['pmUser_id']
		);

		if (!empty($data['EvnDirection_id'])) {
			// ссылку на очередь и на бирку берём существующие.
			$sql = "
				select
					ed.TimetableResource_id as \"TimetableResource_id\",
					ed.TimetableResource_id as \"TimetableResource_id\",
					ed.EvnQueue_id as \"EvnQueue_id\"
				from
					v_EvnDirection_all ed
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
        switch ((int)$data['PrehospDirect_id']) {
            case 1: // 1 Отделение ЛПУ
                $params['Lpu_sid'] = $data['Lpu_id']; //Направившее ЛПУ
                $params['LpuSection_id'] = $data['LpuSection_id'];
                $params['From_MedStaffFact_id'] = $data['MedStaffFact_id'];
                break;
            case 2: // 2 Другое ЛПУ
				$Lpu_id = $this->getFirstResultFromQuery('SELECT Lpu_id as "Lpu_id" FROM v_Lpu WHERE Org_id = :Org_sid limit 1',array('Org_sid' => $data['Org_sid']));
				$params['Lpu_sid'] = $Lpu_id;//Направившее ЛПУ
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
		/*
		echo getDebugSql($sql, $params);
		exit;
		*/
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
					Diag_id as \"Diag_id\"
				from
					v_EvnDirection_all
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
			} else {
				return false;
			}
		} else {
			return false;
		}
    }

	/**
	 * @param array $data
	 * @return boolean
	 */
	private function delEvnFuncRequestUsluga($data) {

		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from  dbo.p_EvnUslugaPar_del(
				EvnUslugaPar_id := :EvnUslugaPar_id,
				pmUser_id := :pmUser_id
			)
		";

		$this->db->query($query, $data);

		if (!empty($data['parentEvnClass_SysNick'])
			&& $data['parentEvnClass_SysNick'] == 'EvnVizitPLStom'
		) {
			$this->load->model('EvnPrescrFuncDiag_model', 'epfdmodel');
			$this->epfdmodel->delEvnUslugaToothNum(array(
				'EvnUsluga_id' => $data['EvnUslugaPar_id']
			));
		}
	}

	/**
	 *
	 * @param array $data
	 * @param array $evnuslugalist
	 * @param boolean $clearall
	 * @return boolean
	 */
	private function clearEvnFuncRequestUsluga($data, $evnuslugalist, $clearall) {

		$result = $this->db->query("
			select
				EvnUslugaPar_id as \"EvnUslugaPar_id\"
			from v_EvnUslugaPar
			where EvnDirection_id = :EvnDirection_id
		",
			array('EvnDirection_id' => $data['EvnDirection_id'])
		);
		
		if (is_object($result)) {

			$response = $result->result('array');
			if (is_array($response)) {

				if (empty($response)) { return array(array('Error_Msg'=>null)); }

				foreach($response as $evnUslugaPar) {

					$evnUslugaPar['pmUser_id'] = $data['pmUser_id'];

					$query = "
						select
							Error_Code as \"Error_Code\",
							Error_Message as \"Error_Msg\"
						from  dbo.p_EvnUslugaPar_del(
							EvnUslugaPar_id := :EvnUslugaPar_id,
							pmUser_id := :pmUser_id
						)
					";

					if ($clearall || in_array($evnUslugaPar['EvnUslugaPar_id'], $evnuslugalist)) {
						$result = $this->db->query($query, $evnUslugaPar);

						if (!empty($data['parentEvnClass_SysNick'])
							&& $data['parentEvnClass_SysNick'] == 'EvnVizitPLStom'
						) {
							$this->load->model('EvnPrescrFuncDiag_model', 'epfdmodel');
							$this->epfdmodel->delEvnUslugaToothNum(array(
								'EvnUsluga_id' => $evnUslugaPar['EvnUslugaPar_id']
							));
						}
					}
				}

				return array(array('Error_Msg'=>null));

			} else { return false; }
		} else { return false; }
	}
	/**
	 * 
	 * @param array $data
	 * @return boolean
	 */
	function saveEvnFuncRequestUsluga($data) {

		$query = "
			with mv as (select EvnPrescr_id from v_EvnPrescrDirection where EvnDirection_id = :EvnDirection_id limit 1)
			
			select
				EvnUslugaPar_id as \"EvnUslugaPar_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from dbo.p_EvnUslugaPar_ins(
				EvnUslugaPar_id := :EvnUslugaPar_id,
				EvnUslugaPar_pid := null,
                Lpu_id := :Lpu_id,
				Server_id := :Server_id, 
				PersonEvn_id := :PersonEvn_id, 
				UslugaPlace_id := :UslugaPlace_id,
				EvnUslugaPar_Kolvo := 1,
				PayType_id := :PayType_id,
				Diag_id := :Diag_id,
				UslugaComplex_id := :UslugaComplex_id,
				pmUser_id := :pmUser_id,
				EvnDirection_id := :EvnDirection_id,
				EvnRequest_id := :EvnRequest_id,
				EvnPrescrTimetable_id := null,
				EvnPrescr_id := (select evnprescr_id from mv),
				PrehospDirect_id := :PrehospDirect_id,
				FSIDI_id := :FSIDI_id,
			)
		";

		$queryParams = array(
			'EvnUslugaPar_id' => NULL,
			'Lpu_id'  => $data['Lpu_id'],
            'Server_id' => $data['Server_id'],
            'UslugaPlace_id' => 1,
            'PersonEvn_id' => $data['PersonEvn_id'],
			'PayType_id' => $data['PayType_id'],
			'Diag_id' => $data['Diag_id'] ?? null,
			'EvnDirection_id' => $data['EvnDirection_id'],
			'EvnRequest_id' => !empty($data['EvnRequest_id'])?$data['EvnRequest_id']:null,
			'UslugaComplex_id' => $data['UslugaComplex_id'],
			'PrehospDirect_id' => $data['PrehospDirect_id'],
			'pmUser_id' => $data['pmUser_id'],
            'FSIDI_id' => !empty($data['FSIDI_id']) ?? null
		);

		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) { return $result->result('array'); }
		else { return false; }
	}
	/**
	 * 
	 * @param type $data
	 * @return boolean
	 */
	function loadEvnUslugaEditForm($data) {
		$needAttributesPanel = "";
		$join = "";
		if (getRegionNick() == 'perm') {
			$needAttributesPanel = "case when es.EvnSection_id is not null and uc.UslugaComplex_Code in ('A04.20.001', 'A04.20.001.001') then 1 else 0 end as \"needAttributesPanel\",";
			$join .= " left join v_UslugaComplex uc on uc.UslugaComplex_id = eup.UslugaComplex_id";
			$join .= " left join lateral(
				select
					es.EvnSection_id
				from
					v_EvnPrescrDirection epd
					inner join v_EvnPrescr ep on ep.EvnPrescr_id = epd.EvnPrescr_id
					inner join v_EvnSection es on es.EvnSection_id = ep.EvnPrescr_pid
					inner join v_LpuSection ls on ls.LpuSection_id = es.LpuSection_id
					inner join v_LpuUnit lu on lu.LpuUnit_id = ls.LpuUnit_id
				where
					epd.EvnDirection_id = eup.EvnDirection_id
					and lu.LpuUnitType_SysNick in ('dstac','pstac','hstac')
					and exists (
						select
							eu.EvnUsluga_id
						from
							v_EvnSection es2
							inner join v_EvnUsluga eu on eu.EvnUsluga_pid = es2.EvnSection_id
							inner join v_UslugaComplex uc2 on uc2.UslugaComplex_id = eu.UslugaComplex_id
						where
							uc2.UslugaComplex_Code in ('A11.20.017.002', 'A11.20.017.003', 'A11.20.030.001', 'A11.20.017')
							and es2.EvnSection_pid = es.EvnSection_pid
						limit 1
					)
				limit 1
				) es on true
			";
		}

		if (getRegionNick() === 'kz') {
            $needAttributesPanel .= "
				UMTL.UslugaMedType_id as \"UslugaMedType_id\",
			";
            $join .= "
				left join r101.v_UslugaMedTypeLink UMTL ON UMTL.Evn_id=EUP.EvnUslugaPar_id
			";
        }

		$query = "
			select
				{$needAttributesPanel}
				EUP.EvnUslugaPar_Kolvo as \"EvnUsluga_Kolvo\",
				EUP.EvnUslugaPar_id as \"EvnUslugaPar_id\",
				ED.EvnDirection_id as \"EvnDirection_id\",
				EUP.EvnRequest_id as \"EvnRequest_id\",
				EUP.MedProductCard_id as \"MedProductCard_id\",
				EUP.PrehospDirect_id as \"PrehospDirect_id\",
				EUP.Lpu_id as \"Lpu_id\",
				EUP.PayType_id as \"PayType_id\",
				EUP.Org_uid as \"Org_uid\",
				to_char(EUP.EvnUslugaPar_setDT, 'dd.mm.yyyy') as \"EvnUslugaPar_setDate\",
				to_char(EUP.EvnUslugaPar_setTime, 'hh24:mi') as \"EvnUslugaPar_setTime\",
				EUP.LpuSection_uid as \"LpuSection_uid\",
				EUP.MedStaffFact_id as \"MedStaffFact_id\",
				EUP.MedPersonal_id as \"MedPersonal_uid\",
				EUP.MedPersonal_sid as \"MedPersonal_sid\",
				EUP.Server_id as \"Server_id\",
				EUP.Person_id as \"Person_id\",
				EUP.PersonEvn_id as \"PersonEvn_id\",
				EUP.UslugaComplex_id as \"UslugaComplex_id\",
				EUP.FSIDI_id as \"FSIDI_id\",
				EUP.Diag_id as \"Diag_id\",
				EUP.TumorStage_id as \"TumorStage_id\",
				EUP.DeseaseType_id as \"DeseaseType_id\",
				EUP.Mes_id as \"Mes_id\",
				EUP.EvnUslugaPar_Regime as \"EvnUslugaPar_Regime\",
				EUP.EvnUslugaPar_Comment as \"EvnUslugaPar_Comment\",
				EUP.EvnUslugaPar_IsPaid as \"EvnUslugaPar_IsPaid\",
				EUP.EvnUslugaPar_IndexRep as \"EvnUslugaPar_IndexRep\",
				EUP.EvnUslugaPar_IndexRepInReg as \"EvnUslugaPar_IndexRepInReg\",
				EUP.MedicalCareFormType_id as \"EUP_MedicalCareFormType_id\",
				ED.Diag_id as \"DirectionDiag_id\",
				EUP.EvnUslugaPar_NumUsluga as \"EvnUslugaPar_NumUsluga\",
				EUP.EvnUslugaPar_UslugaNum as \"EvnUslugaPar_UslugaNum\",
				EUP.StudyResult_id as \"StudyResult_id\",
				EDlink.EvnDirection_id as \"link_EvnDirection_id\",
				EDlink.EvnDirection_Num as \"link_EvnDirection_Num\",
				to_char(EDlink.EvnDirection_setDate, 'dd.mm.yyyy') as \"link_EvnDirection_setDate\",
				ESlink.EvnStatus_id as \"link_EvnStatus_id\",
				ESlink.EvnStatus_Name as \"link_EvnStatus_Name\",
				tth.ToothNums as \"ToothNums\"
			FROM v_EvnUslugaPar EUP
				LEFT JOIN v_EvnDirection_all ED ON ED.EvnDirection_id = EUP.EvnDirection_id				
				LEFT JOIN v_EvnDirection_all EDlink on EDlink.EvnDirection_pid = EUP.EvnUslugaPar_id
				LEFT JOIN v_EvnStatus ESlink on ESlink.EvnStatus_id = EDlink.EvnStatus_id
				{$join}
				left join lateral(
					select
						string_agg(coalesce(cast(tneu.ToothNumEvnUsluga_ToothNum as varchar),''), ',') as ToothNums
					from v_ToothNumEvnUsluga tneu
					where tneu.EvnUsluga_id = EUP.EvnUslugaPar_id
				) tth on true
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
	 * Определение назначения и случая к которому будет привязана услуга
	 */
	function defineUslugaParams($data) {
		$needRecalcKSGKPGKOEF = false;
		// определяем к чему будет привязана услуга, ищем назначение
		$data['EvnPrescr_id'] = null;
		$query = "
			select
				epd.EvnPrescr_id as \"EvnPrescr_id\",
				ep.EvnPrescr_pid as \"EvnPrescr_pid\",
				e.EvnClass_SysNick as \"EvnClass_SysNick\"
			from
				v_EvnPrescrDirection epd
				inner join v_EvnPrescr ep on epd.EvnPrescr_id = ep.EvnPrescr_id
				left join v_Evn e on e.Evn_id = ep.EvnPrescr_pid
			where
				epd.EvnDirection_id = :EvnDirection_id
			limit 1
		";
		$resp_ep = $this->queryResult($query, array(
			'EvnDirection_id' => $data['EvnDirection_id']
		));
		if (!empty($resp_ep[0]['EvnPrescr_id'])) {
			$data['EvnPrescr_id'] = $resp_ep[0]['EvnPrescr_id'];
			// если въявную не указан родитель, то ищем к чему привязывать по назначению
			if (empty($data['EvnUslugaPar_pid']) && !empty($data['EvnUslugaPar_setDT'])) {
				if (getRegionNick() == 'perm') {
					$data['EvnUslugaPar_pid'] = $resp_ep[0]['EvnPrescr_pid'];

					if ($resp_ep[0]['EvnClass_SysNick'] == 'EvnSection') {
						$needRecalcKSGKPGKOEF = true;
					}
				} else {
					$dt = "cast(:EvnUslugaPar_setDT as timestamp)";
					if ($data['EvnUslugaPar_setDT'] == 'curdate') {
						$dt = "dbo.tzGetDate()";
					}
					$checkDateType = "timestamp";
					if (getRegionNick() == "astra") $checkDateType = "date";

					switch ($resp_ep[0]['EvnClass_SysNick']) {
						case 'EvnPS': // из приёмного
						case 'EvnSection':
							// Услуги, независимо от того, из какого движения (включая приемное) в пределах КВС они назначены, присоединяются к тому движению в рамках КВС назначения, в интервал которого попадает coalesce(дата забора для лабораторной, дата выполнения для всех остальных).
							// Услуги, назначенные в КВС/движении, у которых coalesce(дата забора для лабораторной, дата выполнения для всех остальных) позже выписки из стационара, не попадают в случай, а отображаются сами по себе.
							$joins = "
								inner join v_EvnSection es on es.EvnSection_id = ep.EvnPrescr_pid
								inner join v_EvnPS eps on eps.EvnPS_id = es.EvnSection_pid and cast(coalesce(eps.EvnPS_disDT, {$dt}) as {$checkDateType}) >= cast({$dt} as {$checkDateType}) -- дата выписки больше
							";
							if ($resp_ep[0]['EvnClass_SysNick'] == 'EvnPS') {
								$joins = "
									inner join v_EvnPS eps on eps.EvnPS_id = ep.EvnPrescr_pid and cast(coalesce(eps.EvnPS_disDT, {$dt}) as {$checkDateType}) >= cast({$dt} as {$checkDateType}) -- дата выписки больше
								";
							}
							$query = "
								select
									coalesce(es_child.EvnSection_id, eps.EvnPS_id) as \"EvnUslugaPar_pid\",
									case when es_child.EvnSection_id is not null then 1 else 0 end as \"needRecalcKSGKPGKOEF\"
								from
									v_EvnPrescr ep
									{$joins}
									left join v_EvnSection es_child on es_child.LpuSection_id is not null and es_child.EvnSection_pid = eps.EvnPS_id and cast(es_child.EvnSection_setDT as {$checkDateType}) <= cast({$dt} as {$checkDateType}) and (cast(es_child.EvnSection_disDT as {$checkDateType}) >= cast({$dt} as {$checkDateType}) OR es_child.EvnSection_disDT IS NULL) -- актуальное движение той же КВС
								where
									ep.EvnPrescr_id = :EvnPrescr_id
								limit 1
							";
							$resp_eup = $this->queryResult($query, array(
								'EvnPrescr_id' => $resp_ep[0]['EvnPrescr_id'],
								'EvnUslugaPar_setDT' => $data['EvnUslugaPar_setDT']
							));
							if (!empty($resp_eup[0]['EvnUslugaPar_pid'])) {
								$data['EvnUslugaPar_pid'] = $resp_eup[0]['EvnUslugaPar_pid'];
							}
							if (!empty($resp_eup[0]['needRecalcKSGKPGKOEF'])) {
								$needRecalcKSGKPGKOEF = true;
							}
							break;
						case 'EvnVizitPL':
						case 'EvnVizitPLStom':
							// если (coalesce(дата забора для лабораторной, дата выполнения для всех остальных) то услуга отображается в случае АПЛ в том посещении, из которого была назначена.
							// иначе - услуга связана со случаем только через назначение и не входит в случай лечения.
							$query = "
								select
									ev.EvnVizitPL_id as \"EvnUslugaPar_pid\"
								from
									v_EvnPrescr ep
									inner join v_EvnVizitPL ev on ev.EvnVizitPL_id = ep.EvnPrescr_pid
									inner join v_EvnPL epl on epl.EvnPL_id = ev.EvnVizitPL_pid and cast(epl.EvnPL_setDT as {$checkDateType}) <= cast({$dt} as {$checkDateType}) and (coalesce(epl.EvnPL_IsFinish, 1) = 1 OR cast(coalesce(epl.EvnPL_disDT, {$dt}) as {$checkDateType}) >= cast({$dt} as {$checkDateType})) -- дата конца случая больше
								where
									ep.EvnPrescr_id = :EvnPrescr_id
								limit 1
							";
							$resp_eup = $this->queryResult($query, array(
								'EvnPrescr_id' => $resp_ep[0]['EvnPrescr_id'],
								'EvnUslugaPar_setDT' => $data['EvnUslugaPar_setDT']
							));
							if (!empty($resp_eup[0]['EvnUslugaPar_pid'])) {
								$data['EvnUslugaPar_pid'] = $resp_eup[0]['EvnUslugaPar_pid'];
							}
							break;
					}
				}
			}
		}

		return array(
			'EvnPrescr_id' => $data['EvnPrescr_id'],
			'EvnUslugaPar_pid' => $data['EvnUslugaPar_pid'],
			'needRecalcKSGKPGKOEF' => $needRecalcKSGKPGKOEF
		);
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
		
		if ( false && $data[ 'EvnUslugaPar_Regime' ] == 1 ) { // убрали жёсткий контроль по #121894
			if ( !isset( $AssociatedResearchesArray ) || !is_array( $AssociatedResearchesArray ) || !sizeof( $AssociatedResearchesArray ) ) {
				return array( array( 'success' => false, 'Error_Msg' => 'Необходимо прикрепить хотя бы одно исследование. Для продолжения сохранения, без прикрепления исследований, выберите аналоговый режим и заполните протокол.' ) );
			}
		}
		
		if ( empty($data['MedPersonal_uid']) && empty($data['MedPersonal_sid']) ) {
			return array( array( 'success' => false, 'Error_Msg' => 'Поле Врач или Средний мед.персонал должно быть заполнено' ) );			
		}

		if (!empty($data['EvnUslugaPar_setDate']) && !empty($data['MedStaffFact_id'])) {
			// проверяем что рабочее место врача на дату выполнения услуги открыто.
			$MedStaffFact_id = $this->getFirstResultFromQuery("
				select
					MedStaffFact_id as \"MedStaffFact_id\"
				from
					v_MedStaffFact
				where
					MedStaffFact_id = :MedStaffFact_id
					and WorkData_begDate <= :EvnUslugaPar_setDT
					and (WorkData_endDate >= :EvnUslugaPar_setDT OR WorkData_endDate IS NULL)
				limit 1
			", array(
				'MedStaffFact_id' => $data['MedStaffFact_id'],
				'EvnUslugaPar_setDT' => $data['EvnUslugaPar_setDate']
			));
			if (empty($MedStaffFact_id)) {
				return array('Error_Msg' => 'Период работы врача не соответствует дате выполнения услуги');
			}
		}

		// если направление не записано на бирку (стоит в очереди), надо создать доп. бирку и направление записать.
		$query = "
			select
				ed.EvnDirection_id as \"EvnDirection_id\",
				ed.TimetableResource_id as \"TimetableResource_id\",
				ed.MedService_id as \"MedService_id\",
				ed.EvnQueue_id as \"EvnQueue_id\",
				ed.Resource_id as \"Resource_id\",
				ttr.TimetableResource_IsDop as \"TimetableResource_IsDop\"
			from
				v_EvnDirection_all ed
				left join v_EvnQueue eq on eq.EvnQueue_id = ed.EvnQueue_id
				left join v_TimetableResource_lite ttr on ttr.TimetableResource_id = ed.TimetableResource_id
			where
				ed.EvnDirection_id = :EvnDirection_id
			limit 1
		";
		$resp = $this->queryResult($query, array(
			'EvnDirection_id' => $data['EvnDirection_id']
		));
		if (!empty($resp[0]['EvnDirection_id']) && empty($resp[0]['TimetableResource_id']) && !empty($resp[0]['EvnQueue_id'])) {
			// если ресурс не задан, то его надо определить на основе услуги заявки
			if (empty($resp[0]['Resource_id'])) {
				$this->load->model('Resource_model');
				$resources = $this->Resource_model->loadResourceList(array(
					'UslugaComplex_ids' => array($data['UslugaComplex_id']),
					'MedService_id' => $resp[0]['MedService_id']
				));
				if (!empty($resources[0]['Resource_id'])) {
					$resp[0]['Resource_id'] = $resources[0]['Resource_id'];
				}
			}

			if (empty($resp[0]['Resource_id'])) {
				return array('Error_Msg' => 'Не удалось определить ресурс на котором возможно выполнение услуги.');
			}
			$this->load->helper('Reg');
			$this->load->model('TimetableResource_model');
			$ttrdata = $this->TimetableResource_model->addTTRDop(array(
				'MedService_id' => $resp[0]['MedService_id'],
				'Resource_id' => $resp[0]['Resource_id'],
				'TimetableExtend_Descr' => null,
				'withoutRecord' => true,
				'ignoreTTRExist' => true,
				'pmUser_id' => $data['pmUser_id']
			));

			if (!empty($ttrdata['TimetableResource_id'])) {
				// записываем направление на бирку
				$this->load->model('EvnDirection_model');
				$apply = $this->EvnDirection_model->applyEvnDirectionFromQueue(array(
					'EvnQueue_id' => $resp[0]['EvnQueue_id'],
					'EvnDirection_id' => $resp[0]['EvnDirection_id'],
					'TimetableResource_id' => $ttrdata['TimetableResource_id'],
					'pmUser_id' => $data['pmUser_id']
				));
			}
		}

		$TimetableResource_id = false;
		// #148791 Если меняется результат услуги дополнительной бирки на Казахстане, ей нужно поменять день создания (приема без записи)
		if(!empty($resp) && !empty($resp[0]) && !empty($resp[0]['TimetableResource_id']) && !empty($resp[0]['TimetableResource_IsDop'])){
			$TimetableResource_id = $resp[0]['TimetableResource_id'];
		}
		if(!empty($TimetableResource_id) && $this->getRegionNick() == 'kz'){
			// проапдейтить день бирки
			$this->load->helper('Reg');
			$EvnUslugaParDay = TimeToDay(strtotime($data['EvnUslugaPar_setDate']));
			$this->db->query("
						update
							TimetableResource
						set
							TimeTableResource_Day = :TimeTableResource_Day,
							pmUser_updID = :pmUser_id,
							TimeTableResource_updDT = GETDATE()
						where
							TimetableResource_id = :TimetableResource_id
					", array(
				'TimetableResource_id' => $TimetableResource_id,
				'TimeTableResource_Day' => $EvnUslugaParDay,
				'pmUser_id' => $data['pmUser_id']
			));
		}
		if (getRegionNick() == 'penza' && !empty($data['EvnUslugaPar_UslugaNum']) && !empty($resp[0]['MedService_id'])) {
			$query = "
				select 
					count(*) as \"cnt\"
				from 
					v_EvnUslugaPar EUP
					inner join v_EvnDirection_all ED on ED.EvnDirection_id = EUP.EvnDirection_id
				where
				 	EUP.EvnUslugaPar_id <> :EvnUslugaPar_id
				 	and ED.MedService_id = :MedService_id
				 	and date_part('year', EUP.EvnUslugaPar_setDT) = date_part('year', CAST(:EvnUslugaPar_setDate as date))
				 	and EUP.EvnUslugaPar_UslugaNum = :EvnUslugaPar_UslugaNum
				limit 1
			";
			$params = array(
				'EvnUslugaPar_id' => $data['EvnUslugaPar_id'],
				'MedService_id' => $resp[0]['MedService_id'],
				'EvnUslugaPar_setDate' => $data['EvnUslugaPar_setDate'],
				'EvnUslugaPar_UslugaNum' => $data['EvnUslugaPar_UslugaNum'],
			);
			//echo getDebugSQL($query, $params);exit;
			$count = $this->getFirstResultFromQuery($query, $params);
			if ($count === false) {
				return array('Error_Msg' => 'Ошибка при проверке номера услуги');
			}
			if ($count > 0) {
				return array('Error_Code' => 301, 'Error_Msg' => "В текущем году уже существует услуга с номером из журнала выполненных услуг {$data['EvnUslugaPar_UslugaNum']}. Измените значение в поле «№ услуги из журнала выполненных услуг»");
			}
		}

		if (!empty($data['MedStaffFact_id'])) {
			// проверяем что рабочее место врача соответсвует врачу, т.к. как то умудряются сохранять некорректные данные
			$MedStaffFact_id = $this->getFirstResultFromQuery("
				select
					MedStaffFact_id as \"MedStaffFact_id\"
				from
					v_MedStaffFact
				where
					MedStaffFact_id = :MedStaffFact_id
					and LpuSection_id = :LpuSection_id
					and MedPersonal_id = :MedPersonal_id
				limit 1
			", array(
				'MedStaffFact_id' => $data['MedStaffFact_id'],
				'LpuSection_id' => $data['LpuSection_uid'],
				'MedPersonal_id' => $data['MedPersonal_uid']
			));

			if (empty($MedStaffFact_id)) {
				throw new Exception('Место работы врача не соответствует выбранному отделению или врачу');
			}
		}

		$uslugaParams = $this->defineUslugaParams(array(
			'EvnDirection_id' => $data['EvnDirection_id'],
			'EvnUslugaPar_pid' => null,
			'EvnUslugaPar_setDT' => $data['EvnUslugaPar_setDate']
		));

		$data['EvnPrescr_id'] = $uslugaParams['EvnPrescr_id'];
		$data['EvnUslugaPar_pid'] = $uslugaParams['EvnUslugaPar_pid'];
		
		$query = "
			select
				EvnUslugaPar_id as \"EvnUslugaPar_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from dbo.p_EvnUslugaPar_upd(
				EvnUslugaPar_id := :EvnUslugaPar_id,
				EvnUslugaPar_pid := :EvnUslugaPar_pid,
                PrehospDirect_id := :PrehospDirect_id,
                Lpu_id := :Lpu_id,
				Server_id := :Server_id, 
				PersonEvn_id := :PersonEvn_id, 
				EvnUslugaPar_setDT := :EvnUslugaPar_setDT,
				UslugaComplex_id := :UslugaComplex_id,
				FSIDI_id := :FSIDI_id,
				Diag_id := :Diag_id,
				TumorStage_id := :TumorStage_id,
				DeseaseType_id := :DeseaseType_id,
				Mes_id := :Mes_id,
				UslugaPlace_id := :UslugaPlace_id,
				EvnUslugaPar_Kolvo := :EvnUsluga_Kolvo,
				PayType_id := :PayType_id,
				Org_uid := :Org_uid,
				LpuSection_uid := :LpuSection_uid,
				MedStaffFact_id := :MedStaffFact_id,
				MedPersonal_id := :MedPersonal_uid,
				MedPersonal_sid := :MedPersonal_sid,
				EvnUslugaPar_Regime := :EvnUslugaPar_Regime,
				EvnUslugaPar_Comment := :EvnUslugaPar_Comment,
				EvnUslugaPar_IndexRep := :EvnUslugaPar_IndexRep,
				EvnUslugaPar_IndexRepInReg := :EvnUslugaPar_IndexRepInReg,
				pmUser_id := :pmUser_id,
				EvnDirection_id := :EvnDirection_id,
				EvnRequest_id := :EvnRequest_id,
				EvnPrescrTimetable_id := null,
				EvnPrescr_id := :EvnPrescr_id,
				MedProductCard_id := :MedProductCard_id,
				EvnUslugaPar_NumUsluga := :EvnUslugaPar_NumUsluga,
				EvnUslugaPar_UslugaNum := :EvnUslugaPar_UslugaNum,
				MedicalCareFormType_id := :MedicalCareFormType_id,
				StudyResult_id := :StudyResult_id
			)
		";

		$queryParams = array(
			'EvnUslugaPar_id' => $data['EvnUslugaPar_id'],
			'PrehospDirect_id' => $data['PrehospDirect_id'],
			'Lpu_id'  => $data['Lpu_id'],
            'Server_id' => $data['Server_id'],
            'PersonEvn_id' => $data['PersonEvn_id'],
			'EvnUslugaPar_setDT' => $data['EvnUslugaPar_setDate'],
			'UslugaComplex_id' => $data['UslugaComplex_id'],
			'FSIDI_id' => $data['FSIDI_id'] ?? null,
			'Diag_id' => (!empty($data['Diag_id']))?$data['Diag_id']: null,
			'TumorStage_id'	=> (!empty($data['TumorStage_id']))?$data['TumorStage_id']: null,
			'DeseaseType_id' => (!empty($data['DeseaseType_id']))?$data['DeseaseType_id']: null,
			'Mes_id' => (!empty($data['Mes_id']))?$data['Mes_id']: null,
            'UslugaPlace_id' => 1,
			'PayType_id' => $data['PayType_id'],
			'EvnUslugaPar_pid' => $data['EvnUslugaPar_pid'],
			'EvnPrescr_id' => $data['EvnPrescr_id'],
			'MedProductCard_id' => $data['MedProductCard_id'],
			'EvnDirection_id' => $data['EvnDirection_id'],
			'EvnRequest_id' => $data['EvnRequest_id'],
			'Org_uid' => $data['Org_uid'],
			'LpuSection_uid' => $data['LpuSection_uid'],
			'MedStaffFact_id' => $data['MedStaffFact_id'],
			'MedPersonal_uid' => $data['MedPersonal_uid'],
			'MedPersonal_sid' => $data['MedPersonal_sid'],
			'EvnUslugaPar_Regime' => $data['EvnUslugaPar_Regime'],
			'EvnUslugaPar_Comment' => $data['EvnUslugaPar_Comment'],
			'EvnUslugaPar_IndexRep' =>  (!empty($data['EvnUslugaPar_IndexRep']))?$data['EvnUslugaPar_IndexRep']: 0,
			'EvnUslugaPar_IndexRepInReg' =>  (!empty($data['EvnUslugaPar_IndexRepInReg']))?$data['EvnUslugaPar_IndexRepInReg']: 1,
			'EvnUslugaPar_NumUsluga' => $data['EvnUslugaPar_NumUsluga'],
			'EvnUsluga_Kolvo' => $data['EvnUsluga_Kolvo'],
			'EvnUslugaPar_UslugaNum' => !empty($data['EvnUslugaPar_UslugaNum'])?$data['EvnUslugaPar_UslugaNum']:null,
			'MedicalCareFormType_id' => null,
			'StudyResult_id' => $data['StudyResult_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		if ($this->getRegionNick() == 'penza') {
			$queryParams['MedicalCareFormType_id'] = isset($data['EUP_MedicalCareFormType_id']) ? $data['EUP_MedicalCareFormType_id'] : null;
		}

		$this->beginTransaction();
		$result = $this->db->query($query, $queryParams);
		
		if ( is_object($result) ) {
			// кэшируем статус заявки
			$this->ReCacheFuncRequestStatus(array(
				'EvnDirection_id' => $data['EvnDirection_id'],
				'EvnUslugaPar_id' => $data['EvnUslugaPar_id'],
				'pmUser_id' => $data['pmUser_id']
			));
			
			$saveEvnUslugaParResult = $result->result('array');

			if (!empty($saveEvnUslugaParResult[0]['EvnUslugaPar_id'])) {
				if (!empty($data['AttributeSignValueData'])) {
					$this->load->model('Attribute_model');
					$AttributeSignValueData = json_decode($data['AttributeSignValueData'], true);

					if (is_array($AttributeSignValueData)) {
						foreach ($AttributeSignValueData as $AttributeSignValue) {
							$AttributeSignValue['pmUser_id'] = $data['pmUser_id'];
							$AttributeSignValue['AttributeSign_TableName'] = 'EvnUslugaCommon'; // атрибуты создали для EvnUslugaCommon, хотя на деле теже сохраняются и для параклинических услуг.
							$AttributeSignValue['AttributeSignValue_TablePKey'] = $saveEvnUslugaParResult[0]['EvnUslugaPar_id'];
							$AttributeSignValue['AttributeSignValue_begDate'] = !empty($AttributeSignValue['AttributeSignValue_begDate']) ? ConvertDateFormat($AttributeSignValue['AttributeSignValue_begDate']) : null;
							$AttributeSignValue['AttributeSignValue_endDate'] = !empty($AttributeSignValue['AttributeSignValue_endDate']) ? ConvertDateFormat($AttributeSignValue['AttributeSignValue_endDate']) : null;

							$this->Attribute_model->isAllowTransaction = false;
							switch ($AttributeSignValue['RecordStatus_Code']) {
								case 0:
								case 2:
									$queryResponse = $this->Attribute_model->saveAttributeSignValue($AttributeSignValue);
									break;

								case 3:
									$queryResponse = $this->Attribute_model->deleteAttributeSignValue($AttributeSignValue);
									break;
							}
							$this->Attribute_model->isAllowTransaction = true;

							if (isset($queryResponse) && !is_array($queryResponse)) {
								$this->rollbackTransaction();
								return array(array('Error_Msg' => 'Ошибка при ' . ($AttributeSignValue['RecordStatus_Code'] == 3 ? 'удалении' : 'сохранении') . ' обслуживаемого отделения'));
							} else if (!empty($queryResponse[0]['Error_Msg'])) {
								$this->rollbackTransaction();
								return $queryResponse;
							}
						}
					}
				}
			}

			$this->commitTransaction();

            if (!empty($saveEvnUslugaParResult[0]['EvnUslugaPar_id'])) {
                $this->saveUslugaMedTypeLink(
                    $saveEvnUslugaParResult[0]['EvnUslugaPar_id'],
                    isset($data['UslugaMedType_id']) ? $data['UslugaMedType_id'] : null
                );
            }

			if (in_array(getRegionNick(), array('perm', 'kaluga', 'ufa', 'astra')) && !empty($uslugaParams['needRecalcKSGKPGKOEF'])) {
				$this->load->model('EvnSection_model');
				$this->EvnSection_model->recalcKSGKPGKOEF($uslugaParams['EvnUslugaPar_pid'], $data['session']);
			}
			
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
								'pmUser_id' => $data[ 'pmUser_id' ],
								'Lpu_id' => $data[ 'Lpu_id' ]
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
				select
					RemoteConsultCenterResearch_id as \"UnformalizedAddressDirectory_id\",
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from {$procedure}(
					RemoteConsultCenterResearch_id := :RemoteConsultCenterResearch_id,
					EvnUslugaPar_id := :EvnUslugaPar_id,
					MedService_lid := :MedService_lid,
					RemoteConsultCenterResearch_status := :RemoteConsultCenterResearch_status,
					pmUser_id := :pmUser_id
				)
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
				RCCR.RemoteConsultCenterResearch_id as \"RemoteConsultCenterResearch_id\",
				RCCR.RemoteConsultCenterResearch_status as \"RemoteConsultCenterResearch_status\",
				ED.EvnDirection_Num as \"EvnDirection_Num\",
				to_char(ED.EvnDirection_setDT, 'dd.mm.yyyy') as \"EvnDirection_setDT\",
				(PS.Person_SurName || ' ' || coalesce(PS.Person_FirName, '') || ' ' || coalesce(PS.Person_SecName,'')) as \"Person_FIO\",
				U.UslugaComplex_Name as \"UslugaComplex_Name\"	
			FROM
				v_RemoteConsultCenterResearch RCCR
				LEFT JOIN v_EvnUslugaPar EUP on EUP.EvnUslugaPar_id = RCCR.EvnUslugaPar_id
				LEFT JOIN v_EvnDirection_all ED on EUP.EvnDirection_id = ED.EvnDirection_id
				LEFT JOIN v_PersonState PS on PS.Person_id = ED.Person_id
				LEFT JOIN v_UslugaComplex U on EUP.UslugaComplex_id = U.UslugaComplex_id
				LEFT JOIN v_MedServiceLink MSL on MSL.MedService_lid = RCCR.MedService_lid
				
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
     * @param int $Evn_id
     * @param int $UslugaMedType_id
     * @throws Exception
     */
    protected function saveUslugaMedTypeLink($Evn_id, $UslugaMedType_id)
    {
        if (getRegionNick() === 'kz') {
            $this->load->model('UslugaMedType_model');

            $result = $this->UslugaMedType_model->saveUslugaMedTypeLink([
                'UslugaMedType_id' => $UslugaMedType_id,
                'Evn_id' => $Evn_id,
                'pmUser_id' => $this->promedUserId
            ]);

            if (!$this->isSuccessful($result)) {
                throw new Exception($result[0]['Error_Msg'], $result[0]['Error_Code']);
            }
        }
    }
}
