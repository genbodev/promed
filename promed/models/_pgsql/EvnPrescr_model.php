<?php
/**
 * Модель назначений Режима, Диеты, Наблюдения,
 * которые хранятся в таблице EvnPrescr
 *
 * Также содержит некоторые общие методы
 *
 * @property Queue_model $MPQueue_model
 * @property TimetableGraf_model $TimetableGraf_model
 * @property EvnPrescrTreat_model $EvnPrescrTreat_model
 * @property EvnPrescrProc_model $EvnPrescrProc_model
 */
class EvnPrescr_model extends swPgModel {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Проверка признака выполнения назначения
	 */
	function getEvnPrescrIsExec($data) {
		$params = array('EvnPrescr_id' => $data['EvnPrescr_id']);

		// @task https://redmine.swan.perm.ru/issues/75887
		$object = (!empty($data['EvnClass_SysNick']) && in_array($data['EvnClass_SysNick'], array('EvnPrescrMse', 'EvnPrescrVK')) ? $data['EvnClass_SysNick'] : 'EvnPrescr');

		$query = "
			select
				coalesce({$object}_IsExec,1) as \"EvnPrescr_IsExec\"
			from v_{$object}
			where {$object}_id = :EvnPrescr_id
			limit 1
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Сохранение признака выполнения назначения
	 * 
	 * @param array $data
	 * @return boolean
	 */
	function saveEvnPrescrIsExec($data)
	{
		$query = "
			select
				ep.EvnPrescr_id as \"EvnPrescr_id\",
				ep.EvnPrescr_IsExec as \"EvnPrescr_IsExec\"
			from
				v_EvnPrescr ep
				inner join v_EvnPrescrDirection epd on ep.EvnPrescr_id = epd.EvnPrescr_id
			where
				epd.EvnDirection_id = :EvnDirection_id
		";

		$resp_ep = $this->queryResult($query, $data);
		foreach($resp_ep as $ep) {
			if (!empty($data['EvnPrescr_IsExec']) && $data['EvnPrescr_IsExec'] == 2) {
				if(!empty($data['Evn_didDT'])){
					//сохранение кастомного времени и комментария
					$data['EvnPrescr_id'] = $ep['EvnPrescr_id'];
					$sqlEvnPrescr = "
						update EvnPrescr
						set
							EvnPrescr_IsExec = :EvnPrescr_IsExec,
							EvnPrescr_Descr = :EvnPrescrProc_Descr
						where Evn_id = :EvnPrescr_id
					";

					$this->db->query($sqlEvnPrescr, $data);

					$sqlEvn = "
						update Evn
						set
							Evn_didDT = :Evn_didDT,
							Evn_updDT = dbo.tzGetDate(),
							pmUser_updID = :pmUser_id
						where Evn_id = :EvnPrescr_id
					";
					$this->db->query($sqlEvn, $data);
				}else{
					// выполняем
					if ($ep['EvnPrescr_IsExec'] = 2) {
						$this->execEvnPrescr(array(
							'EvnPrescr_id' => $ep['EvnPrescr_id'],
							'pmUser_id' => $data['pmUser_id']
						));
					}
				}

			} else {
				// снимаем признак выполнения
				if ($ep['EvnPrescr_IsExec'] == 2) {
					$this->rollbackEvnPrescrExecution(array(
						'EvnPrescr_id' => $ep['EvnPrescr_id'],
						'pmUser_id' => $data['pmUser_id']
					));
				}
			}
		}

		return true;
	}

	/**
	 * Проверка на дублирование назначения-направления
	 */
	function checkDoubleUsluga($data)
	{
		$params = array(
			'EvnPrescr_pid' => $data['EvnPrescr_pid'],
			'MedService_id' => $data['MedService_id'],
			'UslugaComplex_id' => $data['UslugaComplex_id'],
		);
		if (!empty($data['checkRecordQueue']) && $data['checkRecordQueue']) {
			$this->load->model('Queue_model', 'Queue_model');
			$res = $this->Queue_model->checkRecordQueue($params);
			if($res === true){
				return array('checkQueue'=>1);
			}
		}
		switch ($data['PrescriptionType_id']) {
			case 6: // проверять ли совпадение графика (параметров курса)?
				$query = "
					select
						ED.EvnDirection_id as \"EvnDirection_id\"
					from v_EvnCourseProc EC
					inner join v_EvnPrescrProc EP on EC.EvnCourseProc_id = EP.EvnCourse_id
						and EP.UslugaComplex_id = :UslugaComplex_id
						and coalesce(EP.EvnPrescrProc_IsExec, 1) = 1
						and EP.PrescriptionStatusType_id != 3
					inner join v_EvnPrescrDirection epd on epd.EvnPrescr_id = EP.EvnPrescrProc_id
					inner join lateral(
						Select
							ED.EvnDirection_id from v_EvnDirection_all ED where epd.EvnDirection_id = ED.EvnDirection_id
						and ED.MedService_id = :MedService_id
						and ED.EvnDirection_failDT is null
						and coalesce(ED.EvnStatus_id, 16) not in (12,13,15)
						limit 1
					)  ED on true
					where EC.EvnCourseProc_pid = :EvnPrescr_pid
						and EC.UslugaComplex_id = :UslugaComplex_id
					limit 1
				";
				break;
			case 7:// проверять ли совпадение всего списка услуг в EvnPrescrOperUsluga?
				$query = "
					select
						ED.EvnDirection_id as \"EvnDirection_id\"
					from v_EvnPrescrOper EP
					inner join EvnPrescrOperUsluga UL on UL.EvnPrescrOper_id = EP.EvnPrescrOper_id
					inner join v_EvnPrescrDirection epd on epd.EvnPrescr_id = EP.EvnPrescrOper_id
					inner join lateral(
						Select
							ED.EvnDirection_id from v_EvnDirection_all ED where epd.EvnDirection_id = ED.EvnDirection_id
						and ED.MedService_id = :MedService_id
						and ED.EvnDirection_failDT is null
						and coalesce(ED.EvnStatus_id, 16) not in (12,13,15)
						limit 1
					)  ED on true
					where EP.EvnPrescrOper_pid = :EvnPrescr_pid 
						and UL.UslugaComplex_id = :UslugaComplex_id
						and coalesce(EP.EvnPrescrOper_IsExec, 1) = 1
						and EP.PrescriptionStatusType_id != 3
					limit 1
				";
				break;
			case 11:// проверять ли совпадение состава в EvnPrescrLabDiagUsluga?
				$query = "
					select ED.EvnDirection_id as \"EvnDirection_id\"
					from v_EvnPrescrLabDiag EP
					inner join v_EvnPrescrDirection epd on epd.EvnPrescr_id = EP.EvnPrescrLabDiag_id
					inner join lateral(
						Select ED.EvnDirection_id from v_EvnDirection_all ED where epd.EvnDirection_id = ED.EvnDirection_id
						and ED.MedService_id = :MedService_id
						and ED.EvnDirection_failDT is null
						and coalesce(ED.EvnStatus_id, 16) not in (12,13,15)
						limit 1
					) ED on true
					where EP.EvnPrescrLabDiag_pid = :EvnPrescr_pid 
						and EP.UslugaComplex_id = :UslugaComplex_id
						and coalesce(EP.EvnPrescrLabDiag_IsExec, 1) = 1
						and EP.PrescriptionStatusType_id != 3
					limit 1
				";
				break;
			case 12:// проверять ли совпадение всего списка услуг в EvnPrescrFuncDiagUsluga?
				$query = "
					select ED.EvnDirection_id as \"EvnDirection_id\"
					from v_EvnPrescrFuncDiag EP
					inner join EvnPrescrFuncDiagUsluga UL on UL.EvnPrescrFuncDiag_id = EP.EvnPrescrFuncDiag_id
					inner join v_EvnPrescrDirection epd on epd.EvnPrescr_id = EP.EvnPrescrFuncDiag_id
					inner join lateral(
						Select ED.EvnDirection_id from v_EvnDirection_all ED where epd.EvnDirection_id = ED.EvnDirection_id
						and ED.MedService_id = :MedService_id
						and ED.EvnDirection_failDT is null
						and coalesce(ED.EvnStatus_id, 16) not in (12,13,15)
						limit 1
					)  ED on true
					where EP.EvnPrescrFuncDiag_pid = :EvnPrescr_pid 
						and UL.UslugaComplex_id = :UslugaComplex_id
						and coalesce(EP.EvnPrescrFuncDiag_IsExec, 1) = 1
						and EP.PrescriptionStatusType_id != 3
					limit 1
				";
				break;
			case 13:
				$query = "
					select ED.EvnDirection_id as \"EvnDirection_id\"
					from v_EvnPrescrConsUsluga EP
					inner join v_EvnPrescrDirection epd on epd.EvnPrescr_id = EP.EvnPrescrConsUsluga_id
					inner join v_EvnDirection_all ED on epd.EvnDirection_id = ED.EvnDirection_id
						and ED.MedService_id = :MedService_id
						and ED.EvnDirection_failDT is null
						and coalesce(ED.EvnStatus_id, 16) not in (12,13,15)
					where EP.EvnPrescrConsUsluga_pid = :EvnPrescr_pid 
						and EP.UslugaComplex_id = :UslugaComplex_id
						and coalesce(EP.EvnPrescrConsUsluga_IsExec, 1) = 1
						and EP.PrescriptionStatusType_id != 3
					limit 1
				";
				break;
			case 14:
				$query = "
					select ED.EvnDirection_id as \"EvnDirection_id\"
					from v_EvnPrescrOperBlock EP
					inner join v_EvnPrescrDirection epd on epd.EvnPrescr_id = EP.EvnPrescrOperBlock_id
					inner join v_EvnDirection_all ED on epd.EvnDirection_id = ED.EvnDirection_id
						and ED.MedService_id = :MedService_id
						and ED.EvnDirection_failDT is null
						and coalesce(ED.EvnStatus_id, 16) not in (12,13,15)
					where EP.EvnPrescrOperBlock_pid = :EvnPrescr_pid 
						and EP.UslugaComplex_id = :UslugaComplex_id
						and coalesce(EP.EvnPrescrOperBlock_IsExec, 1) = 1
						and EP.PrescriptionStatusType_id != 3
					limit 1
				";
				break;
			default:
				throw new Exception('Для указанного типа назначений не определен запрос для проверки дублирования', 500);
		}
		//throw new Exception(getDebugSQL($query, $params);
		$response = array(
			'EvnDirection_id' => $this->getFirstResultFromQuery($query, $params),
		);
		return $response;
	}

	/**
	 * Получает список назначений из курса для отмены
	 */
	protected function loadEvnPrescrListForCancel($EvnCourse_id=null, $EvnPrescr_id=null, $object = null) {
		$params = array();
		if ($EvnCourse_id > 0) {
			$params['EvnCourse_id'] = $EvnCourse_id;
			$where_clause = 'EP.EvnCourse_id = :EvnCourse_id';
		} else if ($EvnPrescr_id > 0) {
			$params['EvnPrescr_id'] = $EvnPrescr_id;
			$where_clause = 'EP.EvnPrescr_id = :EvnPrescr_id';
		} else {
			throw new Exception('Ошибка при получении списка назначений из курса для отмены!', 500);
		}
		if ($object == 'EvnPrescrLabDiag') {
			$edJoinType = "left join";
		} else {
			$edJoinType = "inner join";
		}

		$query = "
			select
				EP.EvnPrescr_id as \"EvnPrescr_id\",
				EP.EvnCourse_id as \"EvnCourse_id\",
				EP.PrescriptionStatusType_id as \"PrescriptionStatusType_id\",
				ED.EvnDirection_id as \"EvnDirection_id\",
				ED.DirType_id as \"DirType_id\",
				ED.EvnStatus_id as \"EvnStatus_id\",
				TTMS.TimetableMedService_id as \"TimetableMedService_id\",
				EQ.EvnQueue_id as \"EvnQueue_id\",
				TTR.TimetableResource_id as \"TimetableResource_id\",
				coalesce(EP.EvnPrescr_IsExec, 1) as \"EvnPrescr_IsExec\"
			from
				v_EvnPrescr EP
				left join lateral(
					Select 
						epd.EvnDirection_id,
						ED.DirType_id,
						coalesce(ED.EvnStatus_id, 16) as EvnStatus_id
					from v_EvnPrescrDirection epd
					{$edJoinType} v_EvnDirection_all ED on epd.EvnDirection_id = ED.EvnDirection_id
						and ED.EvnDirection_failDT is null
						and coalesce(ED.EvnStatus_id, 16) not in (12,13)
					where epd.EvnPrescr_id = EP.EvnPrescr_id
					order by epd.EvnPrescrDirection_insDT desc
					limit 1
				) ED on true
				left join lateral(
					Select TimetableMedService_id, TimetableMedService_begTime from v_TimetableMedService_lite TTMS where TTMS.EvnDirection_id = ED.EvnDirection_id limit 1
				) TTMS on true
				left join lateral(
					Select EQ.EvnQueue_id from v_EvnQueue EQ where EQ.EvnDirection_id = ED.EvnDirection_id
					and EQ.EvnQueue_recDT is null
					and EQ.EvnQueue_failDT is null
				) EQ on true
				left join lateral(
					Select TimetableResource_id, TimetableResource_begTime from v_TimetableResource_lite TTR where TTR.EvnDirection_id = ED.EvnDirection_id limit 1
				) TTR on true
			where
				{$where_clause}
		";
		// echo getDebugSQL($query, $params); exit();
		$result = $this->queryResult($query, $params);
		if ( !is_array($result) ) {
			throw new Exception('Ошибка запроса к БД при получении списка назначений из курса для отмены!', 500);
		}

		if ($object == 'EvnPrescrLabDiag' && isset($result[0]) && !empty($result[0]['EvnDirection_id'])) {
            $EvnDirection = null;
		    if ($this->usePostgreLis) {
				$this->load->swapi('lis');
				$EvnDirection = $this->lis->GET('EvnDirection', array(
					'EvnDirection_id' => $result[0]['EvnDirection_id']
				), 'single');
				if (!$this->isSuccessful($EvnDirection)) {
					throw new Exception($EvnDirection['Error_Msg'], 500);
				}
			}
            if (empty($EvnDirection)) {
				$this->load->model('EvnDirection_model');
				$EvnDirection = $this->EvnDirection_model->getEvnDirectionData([
					'EvnDirection_id' => $result[0]['EvnDirection_id']
				]);
				if (!is_array($EvnDirection)) {
					throw new Exception('Ошибка при получении данных направления', 500);
				}
			}
			if (!empty($EvnDirection)) {
				$result[0]['DirType_id'] = $EvnDirection['DirType_id'];
				$result[0]['EvnStatus_id'] = $EvnDirection['EvnStatus_id'];
			}
		}

		return $result;
	}

	/**
	 * Отмена назначений из курса
	 */
	function cancelEvnCourse($data, $isOnlyCourse = false) {
		$response = array(array(
			'Error_Msg' => null,
			'Error_Code' => null,
		));
		$isAllowEvnCourseDel = true;
		$queryDel = "
			select
				:EvnCourse_id as \"EvnCourse_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_EvnCourse_del(
				EvnCourse_id := :EvnCourse_id,
				pmUser_id := :pmUser_id
			)
		";
		try {
			$tmp = $this->loadEvnPrescrListForCancel($data['EvnCourse_id']);
			$data['isCancelEvnCourse'] = 1;
			if ($isOnlyCourse && count($tmp)>0) {
				throw new Exception('Нельзя отменить курс, в курсе есть ещё назначения!', '500');
			}
			foreach ($tmp as $row) {
				if ($row['EvnPrescr_IsExec'] == 2) {
					$isAllowEvnCourseDel = false;
				} else {
					$data['EvnPrescr_id'] = $row['EvnPrescr_id'];
					$data['EvnDirection_id'] = $row['EvnDirection_id'];
					$data['EvnStatus_id'] = $row['EvnStatus_id'];
					$data['TimetableMedService_id'] = $row['TimetableMedService_id'];
					$data['EvnQueue_id'] = $row['EvnQueue_id'];
					$tmp2 = $this->cancelEvnPrescr($data, $row['PrescriptionStatusType_id'] != 2, true);
					if (isset($tmp2[0]['TimetableMedService_id'])) {
						throw new Exception($tmp2[0]['TimetableMedService_id'], 800);
					}
					if (isset($tmp2[0]['EvnQueue_id'])) {
						throw new Exception($tmp2[0]['EvnQueue_id'], 801);
					}
					if (isset($tmp2[0]['Error_Msg'])) {
						throw new Exception($tmp2[0]['Error_Msg'], $tmp2[0]['Error_Code']);
					}
				}
			}
			if ($isAllowEvnCourseDel) {
				$queryParams = array(
					'EvnCourse_id' => $data['EvnCourse_id'],
					'pmUser_id' => $data['pmUser_id']
				);
				$result = $this->db->query($queryDel, $queryParams);
				if ( !is_object($result) ) {
					throw new Exception('Ошибка запроса к БД при отмене курса!', 500);
				}
				$response = $result->result('array');
			} else {
				$response[0]['EvnCourse_id'] = $data['EvnCourse_id'];
			}
		} catch (Exception $e) {
			$response[0]['EvnCourse_id'] = $data['EvnCourse_id'];
			if (800 === $e->getCode()) {
				$response[0]['TimetableMedService_id'] = $e->getMessage();
				$response[0]['Error_Msg'] = 'Нужно сначала освободить запись!';
				$response[0]['Error_Code'] = $e->getCode();
			} else if (801 === $e->getCode()) {
				$response[0]['EvnQueue_id'] = $e->getMessage();
				$response[0]['Error_Msg'] = 'Нужно сначала отменить постановку в очередь!';
				$response[0]['Error_Code'] = $e->getCode();
			} else {
				$response[0]['Error_Msg'] = $e->getMessage();
				$response[0]['Error_Code'] = $e->getCode();
			}
		}
		return $response;
	}

	/**
	 * Устанавливает назначению статус "отменено"
	 * и сохраняет данные о том, какой врач отменил, из какого отделения,
	 * если назначение подписано, но не выполнено
	 * или удаляет назначение
	 */
	function cancelEvnPrescr($data, $isAllowDelete = false, $hasLoadEvnPrescrListForCancel = false) {
		$response = array(array(
			'Error_Msg' => null,
			'Error_Code' => null,
		));
		try {
			$object = 'EvnPrescr' . $this->defineEvnPrescrType($data['PrescriptionType_id']);

			$queryCancel = "
				select
					:EvnPrescr_id as \"{$object}_id\",
					:EvnPrescr_id as \"EvnPrescr_id\",
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from p_EvnPrescr_cancel(
					EvnPrescr_id := :EvnPrescr_id,
					MedPersonal_cid := :MedPersonal_cid,
					LpuSection_cid := :LpuSection_cid,
					MedStaffFact_cid := :MedStaffFact_cid,
					pmUser_id := :pmUser_id
				)
			";

			// Для случая удаления
			$queryDel = "
				select
					:EvnPrescr_id as \"{$object}_id\",
					:EvnPrescr_id as \"EvnPrescr_id\",
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from p_{$object}_del(
					{$object}_id := :EvnPrescr_id,
					pmUser_id := :pmUser_id
				)
			";

			if (empty($data['EvnPrescr_id'])) {
				throw new Exception('Не указано назначение!', 400);
			}
			if (empty($data['pmUser_id'])) {
				throw new Exception('Не указан пользователь!', 400);
			}

			if (!$hasLoadEvnPrescrListForCancel) {
				$tmp = $this->loadEvnPrescrListForCancel(null, $data['EvnPrescr_id'], $object);
				if (empty($tmp)) {
					throw new Exception('Назначение не найдено!', 400);
				}
				if ($tmp[0]['EvnPrescr_IsExec'] == 2) {
					throw new Exception('Назначение выполнено и не может быть удалено!', 400);
				}
				if ($tmp[0]['PrescriptionStatusType_id'] == 3) {
					throw new Exception('Назначение отменено и не может быть удалено!', 400);
				}
				if (!empty($tmp[0]['EvnDirection_id']) && !in_array($tmp[0]['EvnStatus_id'], array(10, 12, 13, 16, 17))) {
					throw new Exception('Назначение не может быть отменено. Отменить можно, если направление имеет статус "Записано на бирку" или "В очереди"!', 400);
				}
				$data['EvnCourse_id'] = $tmp[0]['EvnCourse_id'];
				$data['EvnDirection_id'] = $tmp[0]['EvnDirection_id'];
				$data['DirType_id'] = $tmp[0]['DirType_id'];
				$data['EvnStatus_id'] = $tmp[0]['EvnStatus_id'];
				$data['TimetableMedService_id'] = $tmp[0]['TimetableMedService_id'];
				$data['EvnQueue_id'] = $tmp[0]['EvnQueue_id'];
				$data['TimetableResource_id'] = $tmp[0]['TimetableResource_id'];
				$isAllowDelete = ($tmp[0]['PrescriptionStatusType_id'] != 2);
			}

			// Если назначение имеет направление, то нужно сначала отменить направление, но только в том случае если по направлению только 1 назначение.
			$needCancelDirection = true;
			if (!empty($data['EvnDirection_id'])) {
				$resp_ep = $this->queryResult("
					select
						count(ep.EvnPrescr_id) as \"cnt\"
					from
						v_EvnPrescrDirection epd
						inner join v_EvnPrescr ep on ep.EvnPrescr_id = epd.EvnPrescr_id
					where
						epd.EvnDirection_id = :EvnDirection_id
				", array(
					'EvnDirection_id' => $data['EvnDirection_id']
				));

				if (!empty($resp_ep[0]['cnt']) && $resp_ep[0]['cnt'] > 1) {
					$needCancelDirection = false;
				}
			}

			if ($needCancelDirection) {
				if (!empty($data['TimetableMedService_id'])) {
					throw new Exception($data['TimetableMedService_id'], 800);
				}
                if (!empty($data['TimetableResource_id'])) {
                    throw new Exception($data['TimetableResource_id'], 802);
                }
				if (!empty($data['EvnQueue_id']) || (!empty($data['EvnDirection_id']) && $data['EvnStatus_id'] == 10)) {
					$outData = array();
					if (!empty($data['EvnQueue_id'])) {
						$outData['EvnQueue_id'] = $data['EvnQueue_id'];
					} else {
						$outData['EvnDirection_id'] = $data['EvnDirection_id'];
						$outData['DirType_id'] = $data['DirType_id'];
						$outData['EvnStatus_id'] = $data['EvnStatus_id'];
					}
					throw new Exception(json_encode($outData), 801);
				}
				/*if (!empty($data['TimetableResource_id'])) {
					throw new Exception($data['TimetableResource_id'], 802);
				}*/
				if (!empty($data['EvnDirection_id']) && $data['EvnStatus_id'] != 12) {
					throw new Exception('Нужно сначала отменить направление!', 400);
				}
			} else {
				// надо убрать услугу назначения из заявки и из заказа
				$resp_uc = $this->queryResult("
					select
						epld.UslugaComplex_id as \"UslugaComplex_id\",
						epd.EvnDirection_id as \"EvnDirection_id\"
					from
						v_EvnPrescrLabDiag epld
						inner join v_EvnPrescrDirection epd on epld.EvnPrescrLabDiag_id = epd.EvnPrescr_id
					where
						epld.EvnPrescrLabDiag_id = :EvnPrescr_id
				", array(
					'EvnPrescr_id' => $data['EvnPrescr_id']
				));
				if (!empty($resp_uc[0]['EvnDirection_id']) && !empty($resp_uc[0]['UslugaComplex_id'])) {
					// из EvnDirectionUslugaComplex убираем назначенную услугу
					$this->load->model('EvnDirection_model');
					$this->EvnDirection_model->cancelUslugaComplex(array(
						'EvnDirection_id' => $resp_uc[0]['EvnDirection_id'],
						'UslugaComplex_id' => $resp_uc[0]['UslugaComplex_id'],
						'pmUser_id' => $data['pmUser_id']
					));

					// из EvnUslugaPar убираем назначенную услугу
					$this->load->model('EvnLabSample_model');
					$this->EvnLabSample_model->cancelResearch(array(
						'EvnDirection_id' => $resp_uc[0]['EvnDirection_id'],
						'UslugaComplex_id' => $resp_uc[0]['UslugaComplex_id'],
						'pmUser_id' => $data['pmUser_id']
					));
				}
			}

			if ($isAllowDelete) {
				$queryParams = array(
					'EvnPrescr_id' => $data['EvnPrescr_id'],
					'pmUser_id' => $data['pmUser_id']
				);
				$result = $this->db->query($queryDel, $queryParams);
				// echo getDebugSQL($queryDel, $queryParams); exit();
			} else {
				if (empty($data['MedStaffFact_cid']) && (empty($data['MedPersonal_cid']) || empty($data['LpuSection_cid']))) {
					throw new Exception('Не указан врач или отделение!', 400);
				}
				$queryParams = array(
					'EvnPrescr_id' => $data['EvnPrescr_id'],
					'MedPersonal_cid' => $data['MedPersonal_cid'],
					'LpuSection_cid' => $data['LpuSection_cid'],
					'MedStaffFact_cid' => $data['MedStaffFact_cid'],
					'pmUser_id' => $data['pmUser_id']
				);
				// echo getDebugSQL($queryCancel, $queryParams); exit();
				$result = $this->db->query($queryCancel, $queryParams);
			}
			if ( is_object($result) ) {
				$tmp = $result->result('array');
				if (isset($tmp[0]['Error_Msg'])) {
					throw new Exception($tmp[0]['Error_Msg'], 500);
				}
				$response[0][$object.'_id'] = $tmp[0][$object.'_id'];
				$response[0]['EvnPrescr_id'] = $tmp[0]['EvnPrescr_id'];
				switch ($object) {
					case 'EvnPrescrTreat':
						$this->load->model('EvnPrescrTreat_model');
						$this->EvnPrescrTreat_model->onAfterCancel($data);
						break;
					case 'EvnPrescrProc':
						$this->load->model('EvnPrescrProc_model');
						$this->EvnPrescrProc_model->onAfterCancel($data);
						break;
					case 'EvnPrescrFuncDiag':
						$this->load->model('EvnPrescrFuncDiag_model');
						$this->EvnPrescrFuncDiag_model->onAfterCancel($data);
						break;
					case 'EvnPrescrVaccination':
						$this->load->model('EvnPrescrVaccination_model');
						$this->EvnPrescrVaccination_model->onAfterCancel($data);
						break;
				}
			}
			else {
				throw new Exception('Ошибка запроса к БД!', 500);
			}
		} catch (Exception $e) {
			if (800 === $e->getCode()) {
				$response[0]['TimetableMedService_id'] = $e->getMessage();
				$response[0]['Error_Msg'] = 'Нужно сначала освободить запись!';
				$response[0]['Error_Code'] = $e->getCode();
			} else if (801 === $e->getCode()) {
				$outData = json_decode($e->getMessage(), true);
				$response[0] = array_merge($response[0], $outData);
				$response[0]['Error_Msg'] = 'Нужно сначала отменить постановку в очередь!';
				$response[0]['Error_Code'] = $e->getCode();
			} else if (802 === $e->getCode()) {
				$response[0]['TimetableResource_id'] = $e->getMessage();
				$response[0]['Error_Msg'] = 'Нужно сначала освободить запись!';
				$response[0]['Error_Code'] = $e->getCode();
			} else {
				$response[0]['Error_Msg'] = $e->getMessage();
				$response[0]['Error_Code'] = $e->getCode();
			}
		}
		return $response;
	}

	/**
	 * Метод сохранения связи направления с назначением
	 */
	function directEvnPrescr($data) {
		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_EvnPrescrDirection_ins(
				EvnPrescr_id := :EvnPrescr_id,
				EvnDirection_id := :EvnDirection_id,
				pmUser_id := :pmUser_id
			)
		";

		$queryParams = array(
			'EvnPrescr_id' => $data['EvnPrescr_id'],
			'EvnDirection_id' => $data['EvnDirection_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		//echo getDebugSQL($query, $queryParams);exit;
		$result = $this->queryResult($query, $queryParams);
		if (!is_array($result)) {
			return $this->createError('','Ошибка запроса при создании связи назначения с направлением');
		}

		return $result;
	}



	/**
	 * Удаление связи назначения и направления
	 */
	function deleteEvnPrescrDirection($data) {
		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_EvnPrescrDirection_del(
				EvnPrescrDirection_id := :EvnPrescrDirection_id,
				EvnDirection_id := :EvnDirection_id,
				pmUser_id := :pmUser_id
			)
		";
		$queryParams = array('EvnPrescrDirection_id' => $data['EvnPrescrDirection_id']);
		$result = $this->queryResult($query, $queryParams);
		if (!is_array($result)) {
			return $this->createError('','Ошибка при удалении связи назначения с направлением');
		}
		return $result;
	}

	/**
	 * Выполнение назначения
	 */
	function execEvnPrescr($data) {
		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_EvnPrescr_exec(
				EvnPrescr_id := :EvnPrescr_id,
				pmUser_id := :pmUser_id
			)
		";
		$queryParams = array(
			'EvnPrescr_id' => $data['EvnPrescr_id'],
			'pmUser_id' => $data['pmUser_id']
		);
		$result = $this->db->query($query, $queryParams);
		if ( is_object($result) ) {
			// при выполнении назначения в АРМ конс. приёма из очереди нужно принять человека из очереди
			$query = "
				select
					ED.EvnDirection_id as \"EvnDirection_id\"
				from v_EvnPrescrDirection epd
					  inner join v_EvnDirection_all ED on epd.EvnDirection_id = ED.EvnDirection_id
					  	and ED.EvnDirection_failDT is null
					  	and coalesce(ED.EvnStatus_id, 16) not in (12,13)
					  inner join v_MedService MS on MS.MedService_id = ED.MedService_id
					  inner join v_MedServiceType MST on MST.MedServiceType_id = MS.MedServiceType_id
					  	and MST.MedServiceType_SysNick = 'konsult'
					  inner join v_EvnQueue EQ on EQ.EvnDirection_id = ED.EvnDirection_id
					  	and EQ.EvnQueue_recDT is null
					  	and EQ.EvnQueue_failDT is null
				where epd.EvnPrescr_id = :EvnPrescr_id
				order by epd.EvnPrescrDirection_insDT desc
				limit 1
			";
			$EvnDirection_id = $this->getFirstResultFromQuery($query, $queryParams);
			if (!empty($EvnDirection_id)) {
				$this->load->model('TimetableMedService_model','TimetableMedService_model');
				// принимаем человека из очереди
				$this->TimetableMedService_model->acceptWithoutRecord(array(
					'EvnDirection_id' => $EvnDirection_id,
					'pmUser_id' => $data['pmUser_id']
				));
			}
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Выполнение назначения для МП
	 */
	function mSetEvnPrescrExec($data) {

		$saveResult = $this->getFirstRowFromQuery("
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_EvnPrescr_exec(
				EvnPrescr_id := :EvnPrescr_id,
				pmUser_id := :pmUser_id
			)
		", array(
			'EvnPrescr_id' => $data['EvnPrescr_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		if (empty($saveResult['Error_Msg'])) {

			// при выполнении назначения в АРМ конс. приёма из очереди нужно принять человека из очереди
			$EvnDirection_id = $this->getFirstResultFromQuery("
				select
					ED.EvnDirection_id as \"EvnDirection_id\"
				from v_EvnPrescrDirection epd
					  inner join v_EvnDirection_all ED on epd.EvnDirection_id = ED.EvnDirection_id
					  	and ED.EvnDirection_failDT is null
					  	and coalesce(ED.EvnStatus_id, 16) not in (12,13)
					  inner join v_MedService MS on MS.MedService_id = ED.MedService_id
					  inner join v_MedServiceType MST on MST.MedServiceType_id = MS.MedServiceType_id
					  	and MST.MedServiceType_SysNick = 'konsult'
					  inner join v_EvnQueue EQ on EQ.EvnDirection_id = ED.EvnDirection_id
					  	and EQ.EvnQueue_recDT is null
					  	and EQ.EvnQueue_failDT is null
				where epd.EvnPrescr_id = :EvnPrescr_id
				order by epd.EvnPrescrDirection_insDT desc
				limit 1
			", array('EvnPrescr_id' => $data['EvnPrescr_id']));

			if (!empty($EvnDirection_id)) {

				$this->load->model('TimetableMedService_model','TimetableMedService_model');
				// принимаем человека из очереди
				$this->TimetableMedService_model->acceptWithoutRecord(array(
					'EvnDirection_id' => $EvnDirection_id,
					'pmUser_id' => $data['pmUser_id']
				));
			}
		}

		return $saveResult;
	}

	/**
	 * Отмена выполнения назначения
	 */
	function rollbackEvnPrescrExecution($data) {
	    
	    
		$response = array(array(
			'Error_Code'=>null,
			'Error_Msg'=>null,
		));

		$this->load->helper("Options");
		$this->load->model("Options_model", "Options_model");
		$data['options'] = $this->Options_model->getOptionsAll($data);
		$is_merch_module = (!empty($data['options']['drugcontrol']['drugcontrol_module']) && $data['options']['drugcontrol']['drugcontrol_module'] == 2); //признак учета в АРМ Товароведа

		//проверяем возможность отмены
		$query = "
			select
				EP.PrescriptionType_id as \"PrescriptionType_id\",
				EP.EvnPrescr_IsExec as \"EvnPrescr_IsExec\",
				ED.EvnDirection_id as \"EvnDirection_id\",
				EU.EvnUsluga_id as \"EvnUsluga_id\",
				to_char(EU.EvnUsluga_setDate, 'yyyy-mm-dd') as \"EvnUsluga_setDate\",
				EDr.EvnDrug_id as \"EvnDrug_id\",
				DUS.DocumentUcStr_id as \"DocumentUcStr_id\"
			from
			    v_EvnPrescr EP
				left join lateral(
					select ED.EvnStatus_id, EPD.EvnDirection_id from v_EvnPrescrDirection EPD
					inner join v_EvnDirection_all ED on EPD.EvnDirection_id = ED.EvnDirection_id
						 and ED.EvnDirection_failDT is null and coalesce(ED.EvnStatus_id, 16) not in (12,13)
					where EPD.EvnPrescr_id = EP.EvnPrescr_id
					order by epd.EvnPrescrDirection_insDT desc
					limit 1
				) ED on true
				left join lateral(
					select EvnUsluga_id, EvnUsluga_setDate from v_EvnUsluga
					where EvnPrescr_id = EP.EvnPrescr_id
					limit 1
				) EU on true
				left join lateral(
					select EvnDrug_id from v_EvnDrug
					where EvnPrescr_id = EP.EvnPrescr_id
					limit 1
				) EDr on true
				left join lateral(
                    select
                        i_dus.DocumentUcStr_id
                    from
                        v_EvnDrug i_ed
                        left join v_DocumentUcStr i_dus on i_dus.EvnDrug_id = i_ed.EvnDrug_id
                        left join v_DocumentUc i_du on i_du.DocumentUc_id = i_dus.DocumentUc_id
                    where
                        i_ed.EvnPrescr_id = EP.EvnPrescr_id and
                        (
                            i_du.DrugDocumentStatus_id in (2, 12) or -- 2 - Исполнен, 12 - На исполнении
                            i_dus.DrugDocumentStatus_id = 2
                        )
                    limit 1
                ) DUS on true
			where
				EP.EvnPrescr_id = :EvnPrescr_id
		";
		$queryParams = array(
			'EvnPrescr_id' => $data['EvnPrescr_id'],
		);
		$result = $this->db->query($query, $queryParams);
		if ( !is_object($result) ) {
			$response[0]['Error_Code'] = '500';
			$response[0]['Error_Msg'] = 'Ошибка запроса данных назначения для отмены выполнения!';
			return $response;
		}
		$tmp = $result->result('array');
		if ( empty($tmp) ) {
			$response[0]['Error_Code'] = '404';
			$response[0]['Error_Msg'] = 'Данных назначения не найдено!';
			return $response;
		}

		if ( $tmp[0]['EvnPrescr_IsExec'] != 2 ) {
			$response[0]['Error_Code'] = '400';
			$response[0]['Error_Msg'] = 'Назначение не было выполнено!';
			return $response;
		}

		if ( !empty($tmp[0]['DocumentUcStr_id']) && $is_merch_module ) {
			$response[0]['Error_Code'] = '400';
			$response[0]['Error_Msg'] = 'Отмена выполнения  не возможна, т.к. медикаменты уже списаны со склада';
			return $response;
		}

        //дополнение outer apply по v_EvnUsluga
		if ($this->usePostgreLis && $tmp[0]['PrescriptionType_id'] == 11) {
			$this->load->swapi('lis');
			$usluga = $this->lis->GET('EvnUsluga/UslugaByPrescr', $queryParams, 'single');
			if (!$this->isSuccessful($usluga)) {
				//return $uslugas['Error_Msg'];
				$usluga['EvnUsluga_id'] = null;
				$usluga['EvnUsluga_setDate'] = null;
			}
		} else {
			$this->load->model('EvnUsluga_model');
			$usluga = $this->EvnUsluga_model->getUslugaByPrescr($queryParams);
			if (!is_array($usluga) || empty($usluga)) {
				$usluga['EvnUsluga_id'] = null;
				$usluga['EvnUsluga_setDate'] = null;
			} else {
				$usluga = $usluga[0];
			}
		}

		$tmp[0]['EvnUsluga_id'] = $usluga['EvnUsluga_id'];
		$tmp[0]['EvnUsluga_setDate'] = $usluga['EvnUsluga_setDate'];

		$allowUnExec = true;
		switch ($tmp[0]['PrescriptionType_id']) {
			case 5:
				if ( $tmp[0]['EvnDrug_id'] > 0 ) {
					$allowUnExec = false;
				}
				break;
			case 6:
			case 7:
			case 11:
			case 13:
				// А теперь можно
				/*if ( $tmp[0]['EvnUsluga_id'] > 0 || $tmp[0]['EvnDirection_id'] > 0 ) {
					$allowUnExec = false;
				}*/
				break;
			case 12:
				if ( /*$tmp[0]['EvnUsluga_id'] > 0 || $tmp[0]['EvnDirection_id'] > 0*/!empty($tmp[0]['EvnUsluga_setDate']) ) {
					$allowUnExec = false;
				}
				break;
		}

		if ( !$allowUnExec ) {
			$response[0]['Error_Code'] = '400';
			$response[0]['Error_Msg'] = 'Выполнение назначения не может быть отменено!';
			return $response;
		}
		if($tmp[0]['PrescriptionType_id']==10){
			$this->load->model('EvnPrescrObserv_model');
			$this->EvnPrescrObserv_model->ClearDay($data);
		}
		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_EvnPrescr_unexec(
				EvnPrescr_id := :EvnPrescr_id,
				pmUser_id := :pmUser_id
			)
		";
		$queryParams = array(
			'EvnPrescr_id' => $data['EvnPrescr_id'],
			'pmUser_id' => $data['pmUser_id']
		);
		$result = $this->db->query($query, $queryParams);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			$response[0]['Error_Code'] = '500';
			$response[0]['Error_Msg'] = 'Ошибка запроса к БД для отмены выполнения!';
			return $response;
		}
	}

	/**
	 * Отмена выполнения назначения для МП
	 */
	function mUndoEvnPrescrExec($data) {

		$this->load->helper("Options");
		$this->load->model("Options_model", "Options_model");
		$data['options'] = $this->Options_model->getOptionsAll($data);

		//признак учета в АРМ Товароведа
		$is_merch_module = (!empty($data['options']['drugcontrol']['drugcontrol_module']) && $data['options']['drugcontrol']['drugcontrol_module'] == 2);

		//проверяем возможность отмены
		$result = $this->getFirstRowFromQuery( "
			select
				EP.PrescriptionType_id as \"PrescriptionType_id\",
				EP.EvnPrescr_IsExec as \"EvnPrescr_IsExec\",
				ED.EvnDirection_id as \"EvnDirection_id\",
				EU.EvnUsluga_id as \"EvnUsluga_id\",
				to_char(EU.EvnUsluga_setDate, 'yyyy-mm-dd') as \"EvnUsluga_setDate\",
				EDr.EvnDrug_id as \"EvnDrug_id\",
				DUS.DocumentUcStr_id as \"DocumentUcStr_id\"
			from
			    v_EvnPrescr EP
				left join lateral(
					select ED.EvnStatus_id, EPD.EvnDirection_id from v_EvnPrescrDirection EPD
					inner join v_EvnDirection_all ED on EPD.EvnDirection_id = ED.EvnDirection_id
						 and ED.EvnDirection_failDT is null and coalesce(ED.EvnStatus_id, 16) not in (12,13)
					where EPD.EvnPrescr_id = EP.EvnPrescr_id
					order by epd.EvnPrescrDirection_insDT desc
					limit 1
				) ED on true
				left join lateral(
					select EvnUsluga_id, EvnUsluga_setDate from v_EvnUsluga
					where EvnPrescr_id = EP.EvnPrescr_id
					limit 1
				) EU on true
				left join lateral(
					select EvnDrug_id from v_EvnDrug
					where EvnPrescr_id = EP.EvnPrescr_id
					limit 1
				) EDr on true
				left join lateral(
                    select
                        i_dus.DocumentUcStr_id
                    from
                        v_EvnDrug i_ed
                        left join v_DocumentUcStr i_dus on i_dus.EvnDrug_id = i_ed.EvnDrug_id
                        left join v_DocumentUc i_du on i_du.DocumentUc_id = i_dus.DocumentUc_id
                    where
                        i_ed.EvnPrescr_id = EP.EvnPrescr_id and
                        (
                            i_du.DrugDocumentStatus_id in (2, 12) or -- 2 - Исполнен, 12 - На исполнении
                            i_dus.DrugDocumentStatus_id = 2
                        )
                    limit 1
                ) DUS on true
			where EP.EvnPrescr_id = :EvnPrescr_id
		", array(
			'EvnPrescr_id' => $data['EvnPrescr_id'],
		));

		if (empty($result) ) {
			throw new Exception('Назначение не найдено');
		} else if (!empty($result['EvnPrescr_IsExec']) && $result['EvnPrescr_IsExec'] != 2) {
			throw new Exception('Назначение не имеет признак "Выполнено"');
		} else if (!empty($result['DocumentUcStr_id']) && $is_merch_module) {
			throw new Exception('Отмена выполнения  не возможна, т.к. медикаменты уже списаны со склада');
		}

		$result['EvnUsluga_id'] = null;
		$result['EvnUsluga_setDate'] = null;

		//дополнение outer apply по v_EvnUsluga
		if ($this->usePostgreLis && !empty($result['PrescriptionType_id']) && $result['PrescriptionType_id'] == 11) {

			$this->load->swapi('lis');
			$usluga = $this->lis->GET('EvnUsluga/UslugaByPrescr', array(
				'EvnPrescr_id' => $data['EvnPrescr_id'],
			), 'single');

		} else {

			$this->load->model('EvnUsluga_model');
			$usluga = $this->EvnUsluga_model->getUslugaByPrescr(array(
				'EvnPrescr_id' => $data['EvnPrescr_id'],
			));

			if (!empty($usluga[0])) {
				$usluga = $usluga[0];
			}
		}

		if (!empty($usluga) && is_array($usluga)) {
			$result['EvnUsluga_id'] = $usluga['EvnUsluga_id'];
			$result['EvnUsluga_setDate'] = $usluga['EvnUsluga_setDate'];
		}

		$allowUnExec = true;
		if ($result['PrescriptionType_id'] == 5 && $result['EvnDrug_id']) {
			$allowUnExec = false;
		}
		if ($result['PrescriptionType_id'] == 12 && !empty($result['EvnUsluga_setDate'])) {
			$allowUnExec = false;
		}

		if (!$allowUnExec) {
			throw new Exception('Выполнение назначения не может быть отменено');
		}

		if ($result['PrescriptionType_id'] ==10 ){
			$this->load->model('EvnPrescrObserv_model');
			$this->EvnPrescrObserv_model->ClearDay($data);
		}

		$result = $this->getFirstRowFromQuery("
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_EvnPrescr_unexec(
				EvnPrescr_id := :EvnPrescr_id,
				pmUser_id := :pmUser_id
			)
		", array(
			'EvnPrescr_id' => $data['EvnPrescr_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		if (!empty($result['Error_Msg'])) {
			throw new Exception('Ошибка при отмене выполнения назначения');
		}
		return $result;
	}

	/**
	 * Получение данных направления, связанного с назначением
	 */
	function loadEvnPrescrEvnDirectionCombo($data) {
		$add_where_cause = '';
		$params = array();
		if (isset($data['EvnDirection_id'])) {
			$add_where_cause .= ' and epd.EvnDirection_id = :EvnDirection_id';
			$params['EvnDirection_id'] = $data['EvnDirection_id'];
		}

		if (isset($data['EvnPrescr_id'])) {
			$add_where_cause .= ' and epd.EvnPrescr_id = :EvnPrescr_id';
			$params['EvnPrescr_id'] = $data['EvnPrescr_id'];
		}

		// хотя бы один из EvnDirection_id или EvnPrescr_id должен быть
		if (empty($add_where_cause)) {
			return false;
		}

		// todo: Если будут ошибки с отображением назначений, то нужно будет добавить в with ED строку
		// inner join v_EvnPrescr ep on epd.EvnPrescr_id = ep.EvnPrescr_id и нижнее условие EP.PrescriptionStatusType_id != 3
		// пока исхожу из того что в системе нет нескольких направлений, созданных по одному назначению
		$query = "
				with ED as (
					Select 
						ED.EvnDirection_id,
						ED.EvnQueue_id,
						epd.EvnPrescr_id,
						ED.LpuSection_did,
						ED.LpuUnit_did,
						ED.Lpu_did,
						ED.MedService_id,
						ED.LpuSectionProfile_id,
						ED.DirType_id
					from v_EvnPrescrDirection epd
						inner join v_EvnDirection_all ED on epd.EvnDirection_id = ED.EvnDirection_id
							and ED.EvnDirection_failDT is null
							and coalesce(ED.EvnStatus_id, 16) not in (12,13)
					where 1=1 {$add_where_cause}
					order by epd.EvnPrescrDirection_id desc
					limit 1
			)
			select
				EP.EvnPrescr_id as \"EvnPrescr_id\",
				ED.EvnDirection_id as \"EvnDirection_id\",
				TTMS.TimetableMedService_id as \"TimetableMedService_id\",
				EQ.EvnQueue_id as \"EvnQueue_id\",
				ED.MedService_id as \"MedService_id\",
				coalesce(MS.MedService_Name,'')
					||' / '|| coalesce(LS.LpuSection_Name,'')
					||' / '|| coalesce(LU.LpuUnit_Name,'')
					||' / '|| coalesce(Lpu.Lpu_Nick,'')
				as \"RecTo\",
				case	
					when TTMS.TimetableMedService_id is not null
						then coalesce(to_char(TTMS.TimetableMedService_begTime, 'dd.mm.yyyy HH24:MI'),'')	
					when TTR.TimetableResource_id is not null
						then coalesce(to_char(TTR.TimetableResource_begTime, 'dd.mm.yyyy HH24:MI'),'')	
					when EQ.EvnQueue_id is not null
						then 'В очереди с '|| coalesce(to_char(EQ.EvnQueue_setDate, 'dd.mm.yyyy'),'')
						else ''
				end as \"RecDate\",
				case	
					when TTMS.TimetableMedService_id is not null
						then 'TimetableMedService'	
					when TTR.TimetableResource_id is not null
						then 'TimetableResource'	
					when EQ.EvnQueue_id is not null
						then 'EvnQueue'
						else ''
				end as \"timetable\",
				case
					when TTMS.TimetableMedService_id is not null
						then TTMS.TimetableMedService_id
					when TTR.TimetableResource_id is not null
						then TTR.TimetableResource_id
					when EQ.EvnQueue_id is not null
						then EQ.EvnQueue_id
						else null
				end as \"timetable_id\",
				EvnUslugaOrder.EvnUslugaPar_id as \"EvnUslugaOrder_id\",
				EvnUslugaOrder.UslugaComplex_id as \"UslugaComplex_id\",
				UCMS.UslugaComplexMedService_id as \"UslugaComplexMedService_id\",
				LU.LpuUnitType_SysNick as \"LpuUnitType_SysNick\",
				DT.DirType_Code as \"DirType_Code\",
				MST.MedServiceType_SysNick as \"MedServiceType_SysNick\"
			from
				v_EvnPrescr EP
				inner join ED on EP.EvnPrescr_id = ed.EvnPrescr_id
				-- службы и параклиника
				left join lateral(
					Select TimetableMedService_id, TimetableMedService_begTime from v_TimetableMedService_lite TTMS where TTMS.EvnDirection_id = ED.EvnDirection_id limit 1
				) TTMS on true
				left join lateral(
					Select TimetableResource_id, TimetableResource_begTime from v_TimetableResource_lite TTR where TTR.EvnDirection_id = ED.EvnDirection_id limit 1
				) TTR on true
				-- очередь
				left join lateral(
					Select EQ.EvnQueue_id, EQ.LpuUnit_did, EQ.LpuSectionProfile_did, Lpu_id, EQ.EvnQueue_setDate from v_EvnQueue EQ where EQ.EvnDirection_id = ED.EvnDirection_id
					and EQ.EvnQueue_recDT is null
					and EQ.EvnQueue_failDT is null
					limit 1
					-- это костыль для того, чтобы направления у которых есть связь по EQ.EvnQueue_id = ED.EvnQueue_id, но нет реальной записи в TimetableMedService можно было отменить
					-- не уверен что он здесь нужен, пока не включаем
					/*
					union
					Select EQ.EvnQueue_id, EQ.LpuUnit_did, EQ.LpuSectionProfile_did, Lpu_id, EQ.EvnQueue_setDate from v_EvnQueue EQ 
					where (EQ.EvnQueue_id = ED.EvnQueue_id)
					and (EQ.EvnQueue_recDT is null or TTMS.TimetableMedService_id is null)
					and EQ.EvnQueue_failDT is null
					limit 1
					*/
				) EQ on true
				-- сама служба (todo: надо ли оно)
				left join v_MedService MS on MS.MedService_id = ED.MedService_id
				left join v_MedServiceType MST on MST.MedServiceType_id = MS.MedServiceType_id
				-- отделение для полки и стаца и для очереди
				left join v_LpuSection LS on LS.LpuSection_id = coalesce(ED.LpuSection_did, MS.LpuSection_id)
				-- подразделение для очереди и служб
				left join v_LpuUnit LU on coalesce(ED.LpuUnit_did,EQ.LpuUnit_did,MS.LpuUnit_id) = LU.LpuUnit_id -- todo: в ED.LpuUnit_did пусто, чего не должно быть
				-- профиль для очереди
				left join v_LpuSectionProfile LSPD on coalesce(ED.LpuSectionProfile_id,EQ.LpuSectionProfile_did,LS.LpuSectionProfile_id) = LSPD.LpuSectionProfile_id -- todo: тут на примере оказалось что почему то ED.LpuSectionProfile_id != EQ.LpuSectionProfile_did, чего не должно быть
				-- тип направления
				left join v_DirType DT on ED.DirType_id = DT.DirType_id
				-- ЛПУ
				left join v_Lpu Lpu on Lpu.Lpu_id = coalesce(ED.Lpu_did, LS.Lpu_id, MS.Lpu_id, EQ.Lpu_id)
				-- назначение в лабораторию
				left join v_EvnPrescrLabDiag epld on epld.EvnPrescrLabDiag_id = ed.EvnPrescr_id
				-- заказанная услуга
				left join v_EvnUslugaPar EvnUslugaOrder on EvnUslugaOrder.EvnDirection_id = ED.EvnDirection_id and coalesce(epld.UslugaComplex_id, EvnUslugaOrder.UslugaComplex_id) = EvnUslugaOrder.UslugaComplex_id
				left join lateral(
					--нельзя однозначно определить на какую услугу службы был создан заказ,
					--когда на службе есть несколько услуг с таким же UslugaComplex_id
					select ucms.UslugaComplexMedService_id
					from v_UslugaComplexMedService ucms
					where ucms.UslugaComplex_id = EvnUslugaOrder.UslugaComplex_id
						and ucms.MedService_id = ED.MedService_id
						and ucms.UslugaComplexMedService_pid IS NULL -- только 0 уровня
					limit 1
				) UCMS on true
			where
				EP.PrescriptionStatusType_id != 3
				
		";
		/*,
		Выявлена проблема при сохранении назначения-направления на услугу службы
		В назначении сохраняются ссылки на UslugaComplex_id из UslugaComplexMedService
			(это упрощает реализацию, но не совсем правильно с логической точки зрения,
			т.к. логичнее было бы сохранять ссылки на UslugaComplex_2011id из этих UslugaComplex)
		В EvnDirection сохраняется ссылка на службу MedService_id и бирку расписания TimetableMedService_id
		Если человек поставлен в очередь, то в EvnQueue сохраняется атрибуты службы и ссылка на направление EvnDirection_id
		В заказе также сохраняются ссылки на UslugaComplex_id из UslugaComplexMedService и ссылка на направление EvnDirection_id
		В итоге нельзя однозначно определить на какую услугу службы был создан заказ,
		когда на службе есть несколько услуг с таким же UslugaComplex_id

		Было бы правильнее в заказе сохранять UslugaComplexMedService_id
		 */
		//echo getDebugSQL($query, $params); exit();
		$result = $this->db->query($query, $params);
		if ( !is_object($result) ) {
			return false;
		}
		$resp = $result->result('array');
		foreach ($resp as &$row) {
			$row['EvnDirection_Text'] = $row['RecTo'].' '.$row['RecDate'];
			unset($row['RecTo']);

			if ($row['MedServiceType_SysNick'] == 'lab') {
				// получаем заказанные тесты в услуге
				$resp_educ = $this->queryResult("
					select
						elruc.UslugaComplex_id as \"UslugaComplex_id\"
					from
						v_EvnLabRequestUslugaComplex elruc
						inner join v_EvnLabRequest elr on elr.EvnLabRequest_id = elruc.EvnLabRequest_id
					where
						elr.EvnDirection_id = :EvnDirection_id
				", array(
					'EvnDirection_id' => $row['EvnDirection_id']
					//,'UslugaComplex_id' => $row['UslugaComplex_id']
				));
				/* поменял запрос по задаче #162365
				"
				select
						educ.UslugaComplex_id
					from
						v_EvnDirectionUslugaComplex educ
						left join v_EvnDirectionUslugaComplex educp on educp.EvnDirectionUslugaComplex_id = educ.EvnDirectionUslugaComplex_pid
					where
						educ.EvnDirection_id = :EvnDirection_id
						and educp.UslugaComplex_id = :UslugaComplex_id
				"

				* */
			} else {
				// получаем заказанные тесты в услуге
				$resp_educ = $this->queryResult("
					select
						educ.UslugaComplex_id as \"UslugaComplex_id\"
					from
						v_EvnDirectionUslugaComplex educ
					where
						educ.EvnDirection_id = :EvnDirection_id
				", array(
					'EvnDirection_id' => $row['EvnDirection_id']
				));
			}

			$EvnUslugaOrder_UslugaChecked = array();
			foreach($resp_educ as $resp_educ_one) {
				$EvnUslugaOrder_UslugaChecked[] = $resp_educ_one['UslugaComplex_id'];
			}
			$row['EvnUslugaOrder_UslugaChecked'] = json_encode($EvnUslugaOrder_UslugaChecked);
		}
		return $resp;
	}

	/**
	 * Возвращает данные для журналa консультаций в АРМ врача стационара: направления, созданные на основе назначения с типом «Консультация», к данному врачу, в данное отделение, по профилю данного отделения
	 */
	function loadEvnPrescrConsJournal($data) {
		//устаревший метод
		return array();
	}

	/**
	 * Возвращает данные для шаблона print_evnprescrdiet
	 */
	function getEvnPrescrDietPrintData($data) {
		$query = "
				select
					to_char(Diet.EvnPrescrDiet_setDate, 'dd.mm.yyyy') as \"EvnPrescrDiet_setDate\",
					Diet.EvnPrescrDiet_Descr as \"EvnPrescrDiet_Descr\",
					PRT.PrescriptionDietType_Name as \"PrescriptionDietType_Name\"
				from v_EvnPrescr EP
					inner join v_EvnPrescrDiet Diet on Diet.EvnPrescrDiet_pid = EP.EvnPrescr_id
					inner join PrescriptionDietType PRT on PRT.PrescriptionDietType_id = Diet.PrescriptionDietType_id
				where 
					EP.EvnPrescr_pid = :Evn_pid and EP.PrescriptionType_id = 2 and Diet.PrescriptionStatusType_id != 3
				order by
					Diet.EvnPrescrDiet_setDate
		";
		//echo getDebugSQL($query, $data); exit();
		$result = $this->db->query($query, $data);
		$response = array();
		if ( is_object($result) )
		{
			$tmp = $result->result('array');
			$tmp2 = array();
			$cnt = count($tmp);
			foreach($tmp as $i => $row) {
				if($i == 0)
				{
					$tmp2['EvnPrescrDiet_DateRange'] = $row['EvnPrescrDiet_setDate'];
				}
				if($i == ($cnt-1))
				{
					$tmp2['EvnPrescrDiet_DateRange'] .= '-'.$row['EvnPrescrDiet_setDate'];
					$tmp2['EvnPrescrDiet_Descr'] = $row['EvnPrescrDiet_Descr'];
					$tmp2['PrescriptionDietType_Name'] = $row['PrescriptionDietType_Name'];
					$response[] = $tmp2;
				}
			}
		}
		return $response;
	}

	/**
	 * @param array $data
	 * @return array
	 */
	function getEvnPrescrPrintData($data) {
		$response = array(
			'data' => array(),
			'count' => 0
		);

		$duration = function($duration, $type) {
			switch($type) {
				case 'дн': $type = 'дней';break;
				case 'нед': $type = 'недель';break;
				case 'мес': $type = 'месяцев';break;
			}
			return $duration.' '.$type;
		};
		$countInDay = function($count) {
			if (in_array($count % 10, array(2, 3, 4))) {
				return $count.' раза в день';
			}
			return $count.' раз в день';
		};
		$itemConvert = function($item) {
			$dateTimeStr = $item['setDate'];
			if (!empty($item['timeTableDT'])) {
				$dateTimeStr = $item['timeTableDT'];
			} else if (!empty($item['queueDate'])) {
				$dateTimeStr = 'Поставлен в очередь '.$item['queueDate'];
			}
			return array(
				'item' => "{$dateTimeStr} / {$item['name']}"
			);
		};

		$query = "
			select distinct
				EPTD.EvnPrescrTreatDrug_id as \"EvnPrescrTreatDrug_id\",
				to_char(EP.EvnPrescrTreat_setDate, 'dd.mm.yyyy') as \"EvnPrescr_setDate\",
				EP.EvnPrescrTreat_Descr as \"Descr\",
				ECT.EvnCourseTreat_MaxCountDay as \"CountInDay\",
				ECT.EvnCourseTreat_Duration as \"CourseDuration\",
				coalesce(DTP.DurationType_Nick, '') as \"DurationTypeP_Nick\",
				EPTD.EvnPrescrTreatDrug_KolvoEd as \"EvnPrescrTreatDrug_KolvoEd\",
				coalesce(PIT.PrescriptionIntroType_Name, '') as \"PrescriptionIntroType_Name\",
				coalesce(dcm.DrugComplexMnn_RusName, Drug.Drug_Name, '') as \"Drug_Name\",
				GUS.GoodsUnit_Name as \"GoodsUnitS_Name\",
				ERG.EvnReceptGeneral_Ser as \"EvnReceptGeneral_Ser\",
				ERG.EvnReceptGeneral_Num as \"EvnReceptGeneral_Num\",
				to_char(ERG.EvnReceptGeneral_begDate, 'dd.mm.yyyy') as \"EvnReceptGeneral_begDate\",
				RT.ReceptType_Name as \"ReceptType_Name\",
				case
					when ERG.EvnReceptGeneral_id is not null then 'Рецепт за полную стоимость'
				end as \"ReceptClass\"
			from v_EvnPrescrTreat EP
				inner join v_EvnPrescrTreatDrug EPTD on EPTD.EvnPrescrTreat_id = EP.EvnPrescrTreat_id
				inner join v_EvnCourseTreat ECT on EP.EvnCourse_id = ECT.EvnCourseTreat_id
				inner join v_EvnCourseTreatDrug ECTD on ECT.EvnCourseTreat_id = ECTD.EvnCourseTreat_id
				left join PrescriptionIntroType PIT on ECT.PrescriptionIntroType_id = PIT.PrescriptionIntroType_id
				left join rls.v_Drug Drug on Drug.Drug_id = EPTD.Drug_id
				left join rls.v_DrugComplexMnn dcm on dcm.DrugComplexMnn_id = coalesce(EPTD.DrugComplexMnn_id,Drug.DrugComplexMnn_id)
				left join rls.CLSDRUGFORMS df on dcm.CLSDRUGFORMS_ID = df.CLSDRUGFORMS_ID
				left join DurationType DTP on ECT.DurationType_id = DTP.DurationType_id
				left join v_GoodsUnit GUS on GUS.GoodsUnit_id = ECTD.GoodsUnit_sid
				left join v_EvnReceptGeneralDrugLink ERGDL on ERGDL.EvnCourseTreatDrug_id = EPTD.EvnCourseTreatDrug_id
				left join v_EvnReceptGeneral ERG on ERG.EvnReceptGeneral_id = ERGDL.EvnReceptGeneral_id
				left join v_ReceptType RT on RT.ReceptType_id = ERG.ReceptType_id
			where
				EP.EvnPrescrTreat_pid = :Evn_pid  and EP.PrescriptionStatusType_id != 3
			order by
				\"EvnPrescr_setDate\",
				\"EvnPrescrTreatDrug_id\"
		";
		$treat = $this->queryResult($query, $data);
		if (count($treat) > 0) {
			$items = array();
			foreach($treat as $row) {
				$item = "{$row['EvnPrescr_setDate']} / {$row['Drug_Name']}";
				$str = "";
				if (!empty($row['PrescriptionIntroType_Name'])) {
					$str .= " {$row['PrescriptionIntroType_Name']}";
				}
				if (!empty($row['CourseDuration'])) {
					$str .= " в течение ".$duration($row['CourseDuration'], $row['DurationTypeP_Nick']);
				}
				if (!empty($row['CountInDay'])) {
					$str .= " ".$countInDay($row['CountInDay']);
				}
				if (!empty($row['EvnPrescrTreatDrug_KolvoEd'])) {
					$str .= " по {$row['EvnPrescrTreatDrug_KolvoEd']} {$row['GoodsUnitS_Name']} за прием";
				}
				if (!empty($str)) {
					$str = trim($str);
					$item .= '. '.mb_strtoupper(mb_substr($str, 0, 1)).mb_substr($str, 1);
				}
				$item .= '.';
				if (!empty($row['EvnReceptGeneral_Num'])) {
					$type = mb_strtolower($row['ReceptType_Name']);
					$item .= " {$row['ReceptClass']} {$type} № {$row['EvnReceptGeneral_Ser']} {$row['EvnReceptGeneral_Num']} от {$row['EvnReceptGeneral_begDate']}.";
				}
				if (!empty($row['Descr'])) {
					$item .= ' ' . $row['Descr'];
				}
				$items[] = array('item' => $item);
			}

			$response['data'][] = array(
				'title' => 'Лекарственные назначения',
				'items' => $items
			);
		}

		$query = "
			SELECT
				coalesce(UCMS.UslugaComplex_Name, UC.UslugaComplex_Name) as \"name\",
				to_char(EPLD.EvnPrescrLabDiag_insDT, 'dd.mm.yyyy') as \"setDate\",
				to_char(EQ.EvnQueue_setDate, 'dd.mm.yyyy') as \"queueDate\",
				to_char(TTMS.TimetableMedService_begTime, 'dd.mm.yyyy HH24:MI:SS') as \"timeTableDT\"
			FROM
				v_EvnPrescrLabDiag EPLD
				inner join v_UslugaComplex UC on UC.UslugaComplex_id = EPLD.UslugaComplex_id
				left join v_EvnPrescrDirection EPD on EPD.EvnPrescr_id = EPLD.EvnPrescrLabDiag_id
				left join v_EvnDirection_all ED on ED.EvnDirection_id = EPD.EvnDirection_id
				left join v_EvnQueue EQ on EQ.EvnDirection_id = ED.EvnDirection_id
				left join v_TimetableMedService_lite TTMS on TTMS.TimeTableMedService_id = ED.TimeTableMedService_id
				left join v_UslugaComplexMedService UCMS on UCMS.UslugaComplex_id = UC.UslugaComplex_id 
					and UCMS.MedService_id = coalesce(EPLD.MedService_id, ED.MedService_id)
				left join v_EvnUslugaPar EUP on EUP.EvnPrescr_id = EPLD.EvnPrescrLabDiag_id
			where
				EPLD.EvnPrescrLabDiag_pid = :Evn_pid
			ORDER BY
				EUP.EvnUslugaPar_setDate
		";
		$labDiag = $this->queryResult($query, $data);
		if (count($labDiag) > 0) {
			$response['data'][] = array(
				'title' => 'Лабораторная диагностика',
				'items' => array_map($itemConvert, $labDiag)
			);
		}

		$query = "
			select
				UC.UslugaComplex_Name as \"name\",
				to_char(EPFD.EvnPrescrFuncDiag_insDT, 'dd.mm.yyyy') as \"setDate\",
				to_char(EQ.EvnQueue_setDate, 'dd.mm.yyyy') as \"queueDate\",
				to_char(TTR.TimeTableResource_begTime, 'dd.mm.yyyy HH24:MI:SS') as \"timeTableDT\"
			from
				v_EvnPrescrFuncDiag EPFD
				inner join v_EvnPrescrFuncDiagUsluga EPFDU on EPFDU.EvnPrescrFuncDiag_id = EPFD.EvnPrescrFuncDiag_id
				inner join v_UslugaComplex UC on UC.UslugaComplex_id = EPFDU.UslugaComplex_id
				left join lateral(
					Select * from v_EvnPrescrDirection EPD where EPD.EvnPrescr_id = EPFD.EvnPrescrFuncDiag_id limit 1
				) EPD on true
				--left join v_EvnPrescrDirection EPD on EPD.EvnPrescr_id = EPFD.EvnPrescrFuncDiag_id
				left join v_EvnDirection_all ED on ED.EvnDirection_id = EPD.EvnDirection_id
				left join v_EvnQueue EQ on EQ.EvnDirection_id = ED.EvnDirection_id
				left join v_TimetableResource_lite TTR on TTR.TimeTableResource_id = ED.TimeTableResource_id
			where
				EPFD.EvnPrescrFuncDiag_pid = :Evn_pid 
			order by
				EPFD.EvnPrescrFuncDiag_setDT
		";
		$funcDiag = $this->queryResult($query, $data);
		if (count($funcDiag) > 0) {
			$response['data'][] = array(
				'title' => 'Инструментальная диагностика',
				'items' => array_map($itemConvert, $funcDiag)
			);
		}

		$query = "
			select
				UC.UslugaComplex_Name as \"name\", 
				to_char(EPCU.EvnPrescrConsUsluga_insDT, 'dd.mm.yyyy') as \"setDate\",
				to_char(EQ.EvnQueue_setDT, 'dd.mm.yyyy') as \"queueDate\",
				to_char(TTMS.TimetableMedService_begTime, 'dd.mm.yyyy HH24:MI:SS') as \"timeTableDT\"
			from
				v_EvnPrescrConsUsluga as EPCU
				inner join v_UslugaComplex UC on UC.UslugaComplex_id = EPCU.UslugaComplex_id
				left join v_EvnPrescrDirection EPD on EPD.EvnPrescr_id = EPCU.EvnPrescrConsUsluga_id
				left join v_EvnDirection_all ED on ED.EvnDirection_id = EPD.EvnDirection_id
				left join v_TimetableMedService_lite TTMS on TTMS.TimeTableMedService_id = ED.TimeTableMedService_id
				left join v_EvnQueue EQ on EQ.EvnQueue_id = ED.EvnQueue_id
			where
				EPCU.EvnPrescrConsUsluga_pid = :Evn_pid
			order by
				EPCU.EvnPrescrConsUsluga_setDT
		";
		$consUsluga = $this->queryResult($query, $data);
		if (count($consUsluga) > 0) {
			$response['data'][] = array(
				'title' => 'Консультационная услуга',
				'items' => array_map($itemConvert, $consUsluga)
			);
		}

		$query = "
			select
				UC.UslugaComplex_Name as \"name\",
				(
					to_char(EPP.EvnPrescrProc_setDT, 'dd.mm.yyyy HH24:MI')
				) as \"setDate\"
			from
				v_EvnPrescrProc EPP
				inner join v_UslugaComplex UC on UC.UslugaComplex_id = EPP.UslugaComplex_id
			where
				EPP.EvnPrescrProc_pid = :Evn_pid
			order by
				EPP.EvnPrescrProc_setDT
		";
		$proc = $this->queryResult($query, $data);
		if (count($proc) > 0) {
			$response['data'][] = array(
				'title' => 'Манипуляции и процедуры',
				'items' => array_map($itemConvert, $proc)
			);
		}

		$response['count'] = array_reduce($response['data'], function($count, $prescrGroup) {
			return $count + count($prescrGroup['items']);
		}, 0);

		return $response;
	}

	/**
	 * Возвращает данные для шаблона print_evnprescrregime
	 */
	function getEvnPrescrRegimePrintData($data) {
		$query = "
				select
					to_char(Regime.EvnPrescrRegime_setDate, 'dd.mm.yyyy') as \"EvnPrescrRegime_setDate\",
					Regime.EvnPrescrRegime_Descr as \"EvnPrescrRegime_Descr\",
					PRT.PrescriptionRegimeType_Name as \"PrescriptionRegimeType_Name\"
				from v_EvnPrescr EP
					inner join v_EvnPrescrRegime Regime on Regime.EvnPrescrRegime_pid = EP.EvnPrescr_id
					inner join PrescriptionRegimeType PRT on PRT.PrescriptionRegimeType_id = Regime.PrescriptionRegimeType_id
				where 
					EP.EvnPrescr_pid = :Evn_pid and EP.PrescriptionType_id = 1 and Regime.PrescriptionStatusType_id != 3
				order by
					Regime.EvnPrescrRegime_setDate
		";
		//echo getDebugSQL($query, $data); exit();
		$result = $this->db->query($query, $data);
		$response = array();
		if ( is_object($result) )
		{
			$tmp = $result->result('array');
			$tmp2 = array();
			$cnt = count($tmp);
			foreach($tmp as $i => $row) {
				if($i == 0)
				{
					$tmp2['EvnPrescrRegime_DateRange'] = $row['EvnPrescrRegime_setDate'];
				}
				if($i == ($cnt-1))
				{
					$tmp2['EvnPrescrRegime_DateRange'] .= '-'.$row['EvnPrescrRegime_setDate'];
					$tmp2['EvnPrescrRegime_Descr'] = $row['EvnPrescrRegime_Descr'];
					$tmp2['PrescriptionRegimeType_Name'] = $row['PrescriptionRegimeType_Name'];
					$response[] = $tmp2;
				}
			}
		}
		return $response;
	}

	/**
	 * проверкa возможности добавить назначение
	 */
	function checkEvnPrescrSaveAbility($data) {
		$response = array('allow' => true, 'Error_Msg' => '');

		if ( !in_array($data['PrescriptionType_id'], array(1,2,10)) || !empty($data['EvnPrescr_id']) ) {
			return $response;
		}

		$query = "
			select
				count(EP.EvnPrescr_id) as \"cnt\"
			from
				v_EvnPrescr EP
				left join v_EvnPrescrRegime Regime on EP.PrescriptionType_id = 1 and Regime.EvnPrescrRegime_pid = EP.EvnPrescr_id
				left join v_EvnPrescrDiet Diet on EP.PrescriptionType_id = 2 and Diet.EvnPrescrDiet_pid = EP.EvnPrescr_id
				left join v_EvnPrescrObserv Obs on EP.PrescriptionType_id = 10 and Obs.EvnPrescrObserv_pid = EP.EvnPrescr_id
			where
				EP.EvnPrescr_pid = :EvnPrescr_pid
				and EP.PrescriptionType_id = :PrescriptionType_id
				and coalesce(Regime.EvnPrescrRegime_id,Diet.EvnPrescrDiet_id,Obs.EvnPrescrObserv_id) is not null
				and EP.PrescriptionStatusType_id != 3
				and ( CAST(coalesce(Regime.EvnPrescrRegime_setDT,Diet.EvnPrescrDiet_setDT,Obs.EvnPrescrObserv_setDT) as date) between CAST(:beg_date as date) and CAST(:end_date as date)
					or CAST(coalesce(Regime.EvnPrescrRegime_disDT,Diet.EvnPrescrDiet_disDT,Obs.EvnPrescrObserv_disDT) as date) between CAST(:beg_date as date) and CAST(:end_date as date)
				)
		";

		$queryParams = array(
			'EvnPrescr_pid' => $data['EvnPrescr_pid']
		,'PrescriptionType_id' => $data['PrescriptionType_id']
		,'beg_date' => $data['EvnPrescr_setDate_Range'][0]
		,'end_date' => $data['EvnPrescr_setDate_Range'][1]
		);

		//echo getDebugSQL($query, $queryParams); exit();
		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			$response['allow'] = false;
			$response['Error_Msg'] = 'Ошибка при выполнении запроса к базе данных (проверка возможности добавить назначение)';
		}
		else {
			$responseTmp = $result->result('array');

			if ( !is_array($responseTmp) || count($responseTmp) == 0 ) {
				$response['allow'] = false;
				$response['Error_Msg'] = 'Ошибка при проверке возможности добавить назначение';
			}
			else if ( !empty($responseTmp[0]['cnt']) ) {
				$response['allow'] = false;
				$response['Error_Msg'] = 'Указанная продолжительность курса пересекается с продолжительностью курса назначения указанного типа, которое уже имеется в рамках выбранного случая посещения/движения';
			}
		}

		return $response;
	}

	/**
	 * Метод
	 */
	function getEvnPrescrObservPosData($data) {
		$query = "
			select
				EPOP.EvnPrescrObservPos_id as \"EvnPrescrObservPos_id\",
				coalesce(OPT.ObservParamType_Name, '') as \"ObservParamType_Name\"
			from v_EvnPrescrObservPos EPOP
				inner join ObservParamType OPT on OPT.ObservParamType_id = EPOP.ObservParamType_id
			where
				EPOP.EvnPrescr_id = :EvnPrescr_id
			order by
				OPT.ObservParamType_id
		";

		$queryParams = array(
			'EvnPrescr_id' => $data['EvnPrescr_id']
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array();
		}
	}

	/**
	 * Метод
	 */
	function loadEvnObservDataViewGrid($data) {
		$query = "
			select
				 EOD.EvnObservData_id as \"EvnObservData_id\",
				 OST.ObservParamType_id as \"ObservParamType_id\",
				 EO.ObservTimeType_id as \"ObservTimeType_id\",
				 to_char(EO.EvnObserv_setDate, 'dd.mm.yyyy') as \"EvnObserv_setDate\",
				 OST.ObservParamType_Name as \"ObservParamType_Name\",
				 OTT.ObservTimeType_Name as \"ObservTimeType_Name\",
				 EOD.EvnObservData_Value as \"EvnObservData_Value\"
			from
				v_EvnObservData EOD
				inner join v_EvnObserv EO on EO.EvnObserv_id = EOD.EvnObserv_id
				inner join v_EvnPrescrObserv EPO on EPO.EvnPrescrObserv_id = EO.EvnObserv_pid
				inner join v_EvnPrescr EP on EP.EvnPrescr_id = EPO.EvnPrescrObserv_pid
				inner join ObservParamType OST on OST.ObservParamType_id = EOD.ObservParamType_id
				inner join ObservTimeType OTT on OTT.ObservTimeType_id = EO.ObservTimeType_id
			where
				EP.EvnPrescr_pid = :EvnObserv_pid
		";

		$queryParams = array(
			'EvnObserv_pid' => $data['EvnObserv_pid']
		);
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Загрузка результатов наблюдений для графиков
	 */
	public function loadEvnObservGraphsData( $data ){
		if (empty($data['loadAll'])) {
			$filter_charts = 'and EOD.ObservParamType_id in (1,2,3,4)';
		} else {
			$filter_charts = '';
		}
		$sql = "
			SELECT
				EOD.EvnObservData_id as \"EvnObservData_id\",
				EOD.EvnObservData_Value as \"EvnObservData_Value\",
				EO.ObservTimeType_id as \"ObservTimeType_id\",
				to_char(EO.EvnObserv_setDate, 'dd.mm.yyyy') as \"EvnObserv_setDate\",
				OST.ObservParamType_id as \"ObservParamType_id\",
				OST.ObservParamType_Name as \"ObservParamType_Name\",
				OTT.ObservTimeType_Name as \"ObservTimeType_Name\"
			FROM
				v_EvnPrescr EP
				INNER JOIN v_EvnPrescrObserv EPO ON EPO.EvnPrescrObserv_pid = EP.EvnPrescr_id
				INNER JOIN v_EvnObserv EO ON EO.EvnObserv_pid = EPO.EvnPrescrObserv_id
				INNER JOIN v_EvnObservData EOD ON EOD.EvnObserv_id = EO.EvnObserv_id
				{$filter_charts}
				INNER JOIN ObservParamType OST ON OST.ObservParamType_id = EOD.ObservParamType_id
				INNER JOIN ObservTimeType OTT ON OTT.ObservTimeType_id = EO.ObservTimeType_id
			WHERE
				EP.EvnPrescr_pid = :EvnObserv_pid
				and EP.PrescriptionType_id = 10
				and length(EOD.EvnObservData_Value) > 0
			ORDER BY
				OST.ObservParamType_id,
				EO.EvnObserv_setDate
		";

		$query = $this->db->query($sql,array(
			'EvnObserv_pid' => $data['EvnObserv_pid']
		));

		if ( is_object( $query ) ) {
			return $query->result_array();
		} else {
			return false;
		}
	}

	/**
	 * Загрузка дополнительной информации темпераутрного листа
	 *
	 * @param array $data
	 * @return array
	 */
	public function loadEvnObservGraphsInfo( $data ){
		$fio = "NULLIF( coalesce( Pa.Person_SurName, '' ) || coalesce( ' ' || Pa.Person_FirName, '' ) || coalesce( ' ' || Pa.Person_SecName, '' ), '')";

		$join = "";
		$selectPersonData = "to_char(Pa.Person_Birthday, 'dd.mm.yyyy') as \"Person_Birthday\",
				{$fio} AS \"Person_Fio\",";
		if (allowPersonEncrypHIV($data['session'])) {
			$join .= " left join v_PersonEncrypHIV peh on peh.Person_id = EP.Person_id";
			$selectPersonData = "case when peh.PersonEncrypHIV_Encryp is null then to_char(Pa.Person_Birthday, 'dd.mm.yyyy') end as \"Person_Birthday\",
				case when peh.PersonEncrypHIV_Encryp is null then {$fio} else peh.PersonEncrypHIV_Encryp end as \"Person_Fio\",";
		}

		$sql = "
			SELECT
				{$selectPersonData}
				EPS.EvnPs_NumCard as \"EvnPs_NumCard\",
				EP.EvnPrescr_pid as \"EvnPrescr_pid\",
				LSW.LpuSectionWard_Name as \"LpuSectionWard_Name\",
				LS.LpuSectionProfile_Name as \"LpuSectionProfile_Name\"
			FROM
				v_EvnPrescr EP
				LEFT JOIN v_Person_all Pa ON( Pa.PersonEvn_id=EP.PersonEvn_id AND Pa.Server_id=EP.Server_id )
				LEFT JOIN v_EvnPS EPS ON( EPS.EvnPS_id=EP.EvnPrescr_rid )
				LEFT JOIN v_EvnSection ES ON( ES.EvnSection_id=EP.EvnPrescr_pid )
				LEFT JOIN v_LpuSection LS ON( coalesce( ES.LpuSection_id, EPS.LpuSection_pid )=LS.LpuSection_id )
				LEFT JOIN v_LpuSectionWard LSW ON( coalesce( ES.LpuSectionWard_id, EPS.LpuSectionWard_id )=LSW.LpuSectionWard_id )
				{$join}
			WHERE
				EP.EvnPrescr_pid=:EvnObserv_pid
		";

		$query = $this->db->query($sql,array(
			'EvnObserv_pid' => $data['EvnObserv_pid']
		));

		if ( is_object( $query ) ) {
			return $query->result_array();
		} else {
			return false;
		}
	}

	/**
	 * Утро
	 */

	const TIME_TYPE_MORNING = 1;

	/**
	 * День
	 */
	const TIME_TYPE_DAY = 2;

	/**
	 * Вечер
	 */
	const TIME_TYPE_EVENING = 3;


	/**
	 * Систолическое давление - верхняя граница артераильного давления
	 */
	const PARAM_TYPE_SYSTOLIC = 1;

	/**
	 * Диастолическое давление - нижняя граница артераильного давления
	 */
	const PARAM_TYPE_DIASTOLIC = 2;

	/**
	 * Пульс
	 */
	const PARAM_TYPE_PULSE = 3;

	/**
	 * Температура
	 */
	const PARAM_TYPE_TEMPERATURE = 4;

	/**
	 * Дыхание
	 */
	const PARAM_TYPE_BREATH = 5;

	/**
	 * Вес
	 */
	const PARAM_TYPE_WEIGHT = 6;

	/**
	 * Выпито жидкости
	 */
	const PARAM_TYPE_AQUA = 7;

	/**
	 * Суточное количество мочи
	 */
	const PARAM_TYPE_URINE = 8;

	/**
	 * Стул
	 */
	const PARAM_TYPE_FECES = 9;

	/**
	 * Ванная
	 */
	const PARAM_TYPE_BATH = 10;

	/**
	 * Смена белья
	 */
	const PARAM_TYPE_CLOTH = 11;

	/**
	 * Педикулёз
	 */
	const PARAM_TYPE_PEDICULOSIS = 12;

	/**
	 * Часотка
	 */
	const PARAM_TYPE_SCABIES = 13;

	/**
	 * Подготавливает полученные данные для печати температурного листа
	 *
	 * @param array $data
	 * @return array(
	 * 		'date_start' => <дата начала формирования графика в формате time>,
	 * 		'date_finish' => <дата окончания формирования графика в формате time>,
	 * 		'dates' => <список дат>,
	 * 		'blood_pressure' => array(
	 * 			<дата в формате time> => array(
	 * 				<ключ из таблицы ObserTimeType:1-утро|2-день|3-вечер> => array(
	 * 					'low' => <диастолическое давление>,
	 * 					'high' => <систолическое давление>
	 * 				)
	 * 			)
	 * 		),
	 * 		'pulse' => array(
	 * 			<дата в формате time> => array(
	 * 				<ключ из таблицы ObserTimeType:1-утро|2-день|3-вечер> => <значение пульса>
	 * 			)
	 * 		),
	 * 		'temperature' => array(
	 * 			<дата в формате time> => array(
	 * 				<ключ из таблицы ObserTimeType:1-утро|2-день|3-вечер> => <значение температуры>
	 * 			)
	 * 		)
	 * 	)
	 */
	public function preparePrintEvnObservGraphsData($data) {
		$new = array(
			'date_start' => null,
			'date_finish' => null,
			'dates' => array(),
			'time_dates' => array(),
			'blood_pressure' => array(),
			'pulse' => array(),
			'temperature' => array(),
		);
		if ( is_array( $data ) && sizeof( $data ) ) {
			$min_date = null;
			$max_date = null;
			foreach ($data as $k => $v) {

				// Получаем минимальную и максимальную даты
				$date = strtotime($v['EvnObserv_setDate']);
				if ($min_date > $date || $min_date === null) {
					$min_date = $date;
					$new['date_start'] = $min_date;
				}
				if ($max_date < $date || $max_date === null) {
					$max_date = $date;
					$new['date_finish'] = $max_date;
				}
				if (!in_array($date, $new['dates'])) {
					$new['dates'][] = $date;
				}
				// на клиенте некорректно переводится из time в date
				$new['time_dates'][$date] = $v['EvnObserv_setDate'];

				switch ($v['ObservParamType_id']) {
					case self::PARAM_TYPE_TEMPERATURE:
						$new['temperature'][$date][$v['ObservTimeType_id']] = $v['EvnObservData_Value'];
						break;

					case self::PARAM_TYPE_PULSE:
						$new['pulse'][$date][$v['ObservTimeType_id']] = $v['EvnObservData_Value'];
						break;

					case self::PARAM_TYPE_DIASTOLIC:
						$new['blood_pressure'][$date][$v['ObservTimeType_id']]['low'] = $v['EvnObservData_Value'];
						break;

					case self::PARAM_TYPE_SYSTOLIC:
						$new['blood_pressure'][$date][$v['ObservTimeType_id']]['high'] = $v['EvnObservData_Value'];
						break;

					case self::PARAM_TYPE_AQUA:
					case self::PARAM_TYPE_BATH:
					case self::PARAM_TYPE_BREATH:
					case self::PARAM_TYPE_FECES:
					case self::PARAM_TYPE_URINE:
					case self::PARAM_TYPE_WEIGHT:
					case self::PARAM_TYPE_CLOTH:
						$new['param_' . $v['ObservParamType_id']][$date] = $v['EvnObservData_Value'];
						break;
				}
			}

			// Отсортируем список дат
			sort($new['dates']);
		}

		return $new;
	}

	/**
	 * Метод
	 */
	function loadEvnPrescrObservPosList($data) {
		$query = "
			select
				 EvnPrescrObserv_id as \"EvnPrescrObserv_id\",
				 EvnPrescrObserv_setDT as \"EvnPrescrObserv_setDT\",
				 ObservParamType_id as \"ObservParamType_id\",
				 ObservTimeType_id  as \"ObservTimeType_id\",
				 EvnObserv_id as \"EvnObserv_id\",
				 EvnObservData_id as \"EvnObservData_id\",
				 EvnObservData_Value as \"EvnObservData_Value\",
				 isMain as \"isMain\"
			FROM(
				select
					 EPO.EvnPrescrObserv_id
					,to_char(EPO.EvnPrescrObserv_setDT, 'dd.mm.yyyy') as EvnPrescrObserv_setDT
					,OST.ObservParamType_id
					,EPO.ObservTimeType_id 
					,EO.EvnObserv_id
					,EOD.EvnObservData_id
					,EOD.EvnObservData_Value
					,2 as isMain
				from
					v_EvnPrescrObservPos EPOP
					inner join v_EvnPrescrObserv EPO on EPO.EvnPrescrObserv_pid = EPOP.EvnPrescr_id
					inner join ObservParamType OST on OST.ObservParamType_id = EPOP.ObservParamType_id
					left join v_EvnObserv EO on EO.EvnObserv_pid = EPO.EvnPrescrObserv_id	
						and cast(EO.EvnObserv_setDT as DATE) = cast(:EvnObserv_setDate as DATE)
					left join v_EvnObservData EOD on EOD.EvnObserv_id = EO.EvnObserv_id
						and EOD.ObservParamType_id = EPOP.ObservParamType_id
				where
					EPO.EvnPrescrObserv_pid = :EvnObserv_pid
					and cast(EPO.EvnPrescrObserv_setDT as DATE) = cast(:EvnObserv_setDate as DATE)
					union
				select
					 EPO.EvnPrescrObserv_id
					,to_char(EPO.EvnPrescrObserv_setDT, 'dd.mm.yyyy') as EvnPrescrObserv_setDT
					,EOD.ObservParamType_id
					,EPO.ObservTimeType_id 
					,EO.EvnObserv_id
					,EOD.EvnObservData_id
					,EOD.EvnObservData_Value
					,1 as isMain
				from
					v_EvnObservData EOD
					inner join v_EvnPrescrObserv EPO on EPO.EvnPrescrObserv_pid = :EvnObserv_pid
					inner join v_EvnObserv EO on EO.EvnObserv_pid = EPO.EvnPrescrObserv_id	
						and cast(EO.EvnObserv_setDT as DATE) = cast(:EvnObserv_setDate as DATE)
					and EOD.EvnObserv_id = EO.EvnObserv_id 
						
				where cast(EPO.EvnPrescrObserv_setDT as DATE) = cast(:EvnObserv_setDate as DATE) 
			)ept
		";

		$queryParams = array(
			'EvnObserv_pid' => $data['EvnObserv_pid'],
			//'ObservTimeType_id' => $data['ObservTimeType_id'],
			'EvnObserv_setDate' => $data['EvnObserv_setDate'],
		);
		//echo getDebugSQL($query, $queryParams); exit();
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$tmp_arr = $result->result('array');
			if(count($tmp_arr) > 0)
			{
				$response = array();
				$paramtype = array();
			}
			else
			{
				return $tmp_arr;
			}
			foreach($tmp_arr as $row) {
				$paramtype[$row['ObservTimeType_id']][$row['ObservParamType_id']]['EvnObservData_id'] = $row['EvnObservData_id'];
				$paramtype[$row['ObservTimeType_id']][$row['ObservParamType_id']]['value'] = $row['EvnObservData_Value'];
				$paramtype[$row['ObservTimeType_id']][$row['ObservParamType_id']]['isMain'] = $row['isMain'];
				$paramtype[$row['ObservTimeType_id']]['EvnPrescrObserv_id']=$row['EvnPrescrObserv_id'];
				$paramtype[$row['ObservTimeType_id']]['EvnObserv_id'] = $row['EvnObserv_id'];
			}

			$response[0]['EvnPrescrObserv_id'] = $tmp_arr[0]['EvnPrescrObserv_id'];
			$response[0]['EvnObserv_setDT'] = $tmp_arr[0]['EvnPrescrObserv_setDT'];
			//$response[0]['EvnPrescrObserv_setDT'] = $dateList[0];
			$response[0]['ObservParamType_id'] = $paramtype;
			//print_r($response);
			return $response;
		}
		else {
			return false;
		}
	}

	/**
	 * Метод
	 */
	function loadEvnPrescrObservEditForm($data) {
		$query = "
			select
				case when EPO.PrescriptionStatusType_id = 1 then 'edit' else 'view' end as \"accessType\",
				EPO.EvnPrescrObserv_Descr as \"EvnPrescrObserv_Descr\",
				EPO.EvnPrescrObserv_id as \"EvnPrescrObserv_id\",
				EPO.EvnPrescrObserv_pid as \"EvnPrescrObserv_pid\",
				to_char(EPO.EvnPrescrObserv_setDT, 'dd.mm.yyyy') as \"EvnPrescrObserv_setDate\",
				EPO.ObservTimeType_id as \"ObservTimeType_id\",
				EPO.PersonEvn_id as \"PersonEvn_id\",
				EPO.PrescriptionStatusType_id as \"PrescriptionStatusType_id\",
				EPO.Server_id as \"Server_id\"
			from
				v_EvnPrescrObserv EPO
				inner join v_EvnPrescr EP on EP.EvnPrescr_id = EPO.EvnPrescrObserv_pid
			where
				EPO.EvnPrescrObserv_id = :EvnPrescrObserv_id
			limit 1
		";

		$queryParams = array(
			'EvnPrescrObserv_id' => $data['EvnPrescrObserv_id']
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Метод, скорее всего, устаревший
	 */
	function loadEvnPrescrProcData($data) {
		$query = "
			select
				 coalesce(EPS.EvnPS_id, ES.EvnSection_id) as \"Evn_pid\",
				 LS.LpuSection_id as \"LpuSection_id\",
				 to_char(coalesce(EPS.EvnPS_setDate, ES.EvnSection_setDate), 'dd.mm.yyyy') as \"Evn_setDate\",
				 coalesce(LS.LpuSection_Name, '') as \"LpuSection_Name\",
				 coalesce(MP.Person_Fio, '') as \"MedPersonal_FIO\",
				 EP.Usluga_id as \"Usluga_id\"
			from
				v_EvnPrescrProc EPP
				inner join v_EvnPrescr EP on EP.EvnPrescr_id = EPP.EvnPrescrProc_pid
				left join v_EvnSection ES on ES.EvnSection_id = EP.EvnPrescr_pid
				left join v_EvnPS EPS on EPS.EvnPS_id = EP.EvnPrescr_pid
				inner join LpuSection LS on LS.LpuSection_id = coalesce(EPS.LpuSection_pid, ES.LpuSection_id)
				left join lateral(
					select Person_Fio
					from v_MedPersonal
					where MedPersonal_id = coalesce(EPS.MedPersonal_pid, ES.MedPersonal_id)
					limit 1
				) MP on true
			where
				EPP.EvnPrescrProc_id = :EvnPrescrProc_id
			limit 1
		";

		$queryParams = array(
			'EvnPrescrProc_id' => $data['EvnPrescrProc_id']
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *  Получение списка выполненных назначений
	 *  Используется: журнал медицинских мероприятий
	 * Метод, скорее всего, устаревший
	 */
	function loadEvnPrescrCompletedJournalGrid($data) {
		$filter = "(1 = 1)";
		$queryParams = array(
			'Lpu_id' => $data['Lpu_id'],
			'MedStaffFact_id' => (!empty($data['session']['CurMedStaffFact_id']) ? $data['session']['CurMedStaffFact_id'] : NULL)
		);

		$filter .= " and EP.Lpu_id = :Lpu_id";

		if ( isset($data['EvnPrescr_setDate_Range'][0]) ) {
			$filter .= " and cast(EPT.EvnPrescr_setDT as date) >= cast(:EvnPrescr_setDate_Range_0 as date)";
			$queryParams['EvnPrescr_setDate_Range_0'] = $data['EvnPrescr_setDate_Range'][0];
		}

		if ( isset($data['EvnPrescr_setDate_Range'][1]) ) {
			$filter .= " and cast(EPT.EvnPrescr_setDT as date) <= cast(:EvnPrescr_setDate_Range_1 as date)";
			$queryParams['EvnPrescr_setDate_Range_1'] = $data['EvnPrescr_setDate_Range'][1];
		}

		if ( !empty($data['Person_Birthday']) ) {
			$filter .= " and PS.Person_Birthday = :Person_Birthday";
			$queryParams['Person_Birthday'] = $data['Person_Birthday'];
		}

		if ( !empty($data['Person_Firname']) ) {
			$filter .= " and PS.Person_Firname ilike :Person_Firname";
			$queryParams['Person_Firname'] = $data['Person_Firname'] . '%';
		}

		if ( !empty($data['Person_Secname']) ) {
			$filter .= " and PS.Person_Secname ilike :Person_Secname";
			$queryParams['Person_Secname'] = $data['Person_Secname'] . '%';
		}

		if ( !empty($data['Person_Surname']) ) {
			$filter .= " and PS.Person_Surname ilike :Person_Surname";
			$queryParams['Person_Surname'] = $data['Person_Surname'] . '%';
		}

		if ( !empty($data['PrescriptionType_id']) ) {
			$filter .= " and EP.PrescriptionType_id = :PrescriptionType_id";
			$queryParams['PrescriptionType_id'] = $data['PrescriptionType_id'];
		}

		$filter .= " and EPT.PrescriptionStatusType_id = 2";
		$filter .= " and coalesce(EPT.EvnPrescr_IsExec, 1) = 2";

		// Использовать union
		$query = "
			select
				-- select
				 EPT.EvnPrescr_id as \"EvnPrescr_id\",
				 EPT.EvnPrescr_pid as \"EvnPrescr_pid\",
				 EP.Person_id as \"Person_id\",
				 EP.PersonEvn_id as \"PersonEvn_id\",
				 EP.Server_id as \"Server_id\",
				 EP.PrescriptionType_id as \"PrescriptionType_id\",
				 EPT.EvnPrescr_IsExec as \"EvnPrescr_IsExec\",
				 EPT.EvnPrescr_setDate as \"EvnPrescr_setDate\",
				 RTRIM(LTRIM(coalesce(PS.Person_Surname, '') || ' ' || coalesce(PS.Person_Firname, '') || ' ' || coalesce(PS.Person_Secname, ''))) as \"Person_FIO\",
				 to_char(PS.Person_Birthday, 'dd.mm.yyyy') as \"Person_Birthday\",
				 coalesce(PT.PrescriptionType_Code, 0) as \"PrescriptionType_Code\",
				 coalesce(PT.PrescriptionType_Name, '') as \"PrescriptionType_Name\",
				 coalesce(EPT.EvnPrescr_Descr, '') as \"EvnPrescr_Descr\",
				 coalesce(PRT.PrescriptionRegimeType_Name, '') as \"PrescriptionRegimeType_Name\",
				 coalesce(PDT.PrescriptionDietType_Name, '') as \"PrescriptionDietType_Name\",
				 coalesce(U.Usluga_Name, '') as \"Usluga_Name\",
				 '' as \"LpuSectionProfile_Name\",
				 coalesce(PIT.PrescriptionIntroType_Name, '') as \"PrescriptionIntroType_Name\",
				 coalesce(PTT.PrescriptionTreatType_Name, '') as \"PrescriptionTreatType_Name\",
				 coalesce(EPT.EvnPrescrTreat_CountInDay, 0) as \"EvnPrescrTreat_CountInDay\",
				 EPT.ObservTimeType_id as \"ObservTimeType_id\",
				 coalesce(OTT.ObservTimeType_Name, '') as \"ObservTimeType_Name\"
				-- end select
			from
				-- from
				(
					select
						 EPR.EvnPrescrRegime_pid as EvnPrescr_pid
						,EPR.EvnPrescrRegime_id as EvnPrescr_id
						,EPR.EvnPrescrRegime_IsExec as EvnPrescr_IsExec
						,EPR.PrescriptionStatusType_id as PrescriptionStatusType_id
						,EPR.EvnPrescrRegime_setDT as EvnPrescr_setDT
						,to_char(EPR.EvnPrescrRegime_setDT, 'dd.mm.yyyy') as EvnPrescr_setDate
						,to_char(EPR.EvnPrescrRegime_setDT, 'HH24:MI') as EvnPrescr_setTime
						,null as LpuSectionProfile_id
						,EPR.EvnPrescrRegime_Descr as EvnPrescr_Descr
						,EPR.PrescriptionRegimeType_id
						,null as PrescriptionDietType_id
						,null as PrescriptionTreatType_id
						,null as EvnPrescrTreat_CountInDay
						,null as ObservTimeType_id
					from v_EvnPrescrRegime EPR
					union
					select
						 EPC.EvnPrescrCons_pid as EvnPrescr_pid
						,EPC.EvnPrescrCons_id as EvnPrescr_id
						,EPC.EvnPrescrCons_IsExec as EvnPrescr_IsExec
						,EPC.PrescriptionStatusType_id as PrescriptionStatusType_id
						,EPC.EvnPrescrCons_setDT as EvnPrescr_setDT
						,to_char(EPC.EvnPrescrCons_setDT, 'dd.mm.yyyy') as EvnPrescr_setDate
						,to_char(EPC.EvnPrescrCons_setDT, 'HH24:MI') as EvnPrescr_setTime
						,EPC.LpuSectionProfile_id as LpuSectionProfile_id
						,EPC.EvnPrescrCons_Descr as EvnPrescr_Descr
						,null as PrescriptionRegimeType_id
						,null as PrescriptionDietType_id
						,null as PrescriptionTreatType_id
						,null as EvnPrescrTreat_CountInDay
						,null as ObservTimeType_id
					from v_EvnPrescrCons EPC
					union
					select
						 EPTR.EvnPrescrTreat_pid as EvnPrescr_pid
						,EPTR.EvnPrescrTreat_id as EvnPrescr_id
						,EPTR.EvnPrescrTreat_IsExec as EvnPrescr_IsExec
						,EPTR.PrescriptionStatusType_id as PrescriptionStatusType_id
						,EPTR.EvnPrescrTreat_setDT as EvnPrescr_setDT
						,to_char(EPTR.EvnPrescrTreat_setDT, 'dd.mm.yyyy') as EvnPrescr_setDate
						,to_char(EPTR.EvnPrescrTreat_setDT, 'HH24:MI') as EvnPrescr_setTime
						,null as LpuSectionProfile_id
						,EPTR.EvnPrescrTreat_Descr as EvnPrescr_Descr
						,null as PrescriptionRegimeType_id
						,null as PrescriptionDietType_id
						,EPTR.PrescriptionTreatType_id
						,EPTR.EvnPrescrTreat_CountInDay
						,null as ObservTimeType_id
					from v_EvnPrescrTreat EPTR
					union
					select
						 EPP.EvnPrescrProc_pid as EvnPrescr_pid
						,EPP.EvnPrescrProc_id as EvnPrescr_id
						,EPP.EvnPrescrProc_IsExec as EvnPrescr_IsExec
						,EPP.PrescriptionStatusType_id as PrescriptionStatusType_id
						,EPP.EvnPrescrProc_setDT as EvnPrescr_setDT
						,to_char(EPP.EvnPrescrProc_setDT, 'dd.mm.yyyy') as EvnPrescr_setDate
						,to_char(EPP.EvnPrescrProc_setDT, 'HH24:MI') as EvnPrescr_setTime
						,null as LpuSectionProfile_id
						,EPP.EvnPrescrProc_Descr as EvnPrescr_Descr
						,null as PrescriptionRegimeType_id
						,null as PrescriptionDietType_id
						,null as PrescriptionTreatType_id
						,null as EvnPrescrTreat_CountInDay
						,null as ObservTimeType_id
					from v_EvnPrescrProc EPP
					union
					select
						 EPO.EvnPrescrOper_pid as EvnPrescr_pid
						,EPO.EvnPrescrOper_id as EvnPrescr_id
						,EPO.EvnPrescrOper_IsExec as EvnPrescr_IsExec
						,EPO.PrescriptionStatusType_id as PrescriptionStatusType_id
						,EPO.EvnPrescrOper_setDT as EvnPrescr_setDT
						,to_char(EPO.EvnPrescrOper_setDT, 'dd.mm.yyyy') as EvnPrescr_setDate
						,to_char(EPO.EvnPrescrOper_setDT, 'HH24:MI') as EvnPrescr_setTime
						,null as LpuSectionProfile_id
						,EPO.EvnPrescrOper_Descr as EvnPrescr_Descr
						,null as PrescriptionRegimeType_id
						,null as PrescriptionDietType_id
						,null as PrescriptionTreatType_id
						,null as EvnPrescrTreat_CountInDay
						,null as ObservTimeType_id
					from v_EvnPrescrOper EPO
					union
					select
						 EPO.EvnPrescrObserv_pid as EvnPrescr_pid
						,EPO.EvnPrescrObserv_id as EvnPrescr_id
						,EPO.EvnPrescrObserv_IsExec as EvnPrescr_IsExec
						,EPO.PrescriptionStatusType_id as PrescriptionStatusType_id
						,EPO.EvnPrescrObserv_setDT as EvnPrescr_setDT
						,to_char(EPO.EvnPrescrObserv_setDT, 'dd.mm.yyyy') as EvnPrescr_setDate
						,to_char(EPO.EvnPrescrObserv_setDT, 'HH24:MI') as EvnPrescr_setTime
						,null as LpuSectionProfile_id
						,EPO.EvnPrescrObserv_Descr as EvnPrescr_Descr
						,null as PrescriptionRegimeType_id
						,null as PrescriptionDietType_id
						,null as PrescriptionTreatType_id
						,null as EvnPrescrTreat_CountInDay
						,EPO.ObservTimeType_id
					from v_EvnPrescrObserv EPO
				) EPT
				inner join v_EvnPrescr EP on EP.EvnPrescr_id = EPT.EvnPrescr_pid
				inner join PrescriptionType PT on PT.PrescriptionType_id = EP.PrescriptionType_id
				left join v_EvnPS EPS on EPS.EvnPS_id = EP.EvnPrescr_pid
				left join v_EvnSection ES on ES.EvnSection_id = EP.EvnPrescr_pid
				left join v_EvnVizitPL EVPL on EVPL.EvnVizitPL_id = EP.EvnPrescr_pid
				inner join v_PersonState PS on PS.Person_id = COALESCE(EPS.Person_id, ES.Person_id, EVPL.Person_id)
				left join PrescriptionRegimeType PRT on PRT.PrescriptionRegimeType_id = EPT.PrescriptionRegimeType_id
				left join PrescriptionDietType PDT on PDT.PrescriptionDietType_id::text = EPT.PrescriptionDietType_id
				left join Usluga U on U.Usluga_id = EP.Usluga_id
				left join LpuSectionProfile LSP on LSP.LpuSectionProfile_id = EPT.LpuSectionProfile_id
				left join PrescriptionIntroType PIT on PIT.PrescriptionIntroType_id = EP.PrescriptionIntroType_id
				left join PrescriptionTreatType PTT on PTT.PrescriptionTreatType_id = EPT.PrescriptionTreatType_id
				left join ObservTimeType OTT on OTT.ObservTimeType_id = EPT.ObservTimeType_id
				-- end from
			where
				-- where
				" . $filter . "
				-- end where
			order by
				-- order by
				EPT.EvnPrescr_setDT,
				PS.Person_Surname,
				PS.Person_Firname,
				PS.Person_Secname,
				PS.Person_Birthday
				-- end order by
		";

		// echo getDebugSQL($query, $queryParams); exit();

		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			return array();
		}

		$response = $result->result('array');

		foreach ( $response as $key => $evnPrescr ) {
			$response[$key]['PrescriptionType_Name'] = str_replace(' ', '<br />', $evnPrescr['PrescriptionType_Name']);

			$response[$key]['EvnPrescr_Name'] = '<div>';

			if ( !empty($evnPrescr['LpuSectionProfile_Name']) ) {
				$response[$key]['EvnPrescr_Name'] .= '<div><span style="font-weight: bold;">Профиль:</span> ' .  htmlspecialchars($evnPrescr['LpuSectionProfile_Name']) . '</div>';
			}

			if ( !empty($evnPrescr['Usluga_Name']) ) {
				$response[$key]['EvnPrescr_Name'] .= '<div><span style="font-weight: bold;">Услуга:</span> ' .  htmlspecialchars($evnPrescr['Usluga_Name']) . '</div>';
			}

			if ( !empty($evnPrescr['PrescriptionRegimeType_Name']) ) {
				$response[$key]['EvnPrescr_Name'] .= '<div><span style="font-weight: bold;">Тип режима:</span> ' .  htmlspecialchars($evnPrescr['PrescriptionRegimeType_Name']) . '</div>';
			}

			if ( !empty($evnPrescr['PrescriptionDietType_Name']) ) {
				$response[$key]['EvnPrescr_Name'] .= '<div><span style="font-weight: bold;">Тип диеты:</span> ' .  htmlspecialchars($evnPrescr['PrescriptionDietType_Name']) . '</div>';
			}

			if ( !empty($evnPrescr['PrescriptionIntroType_Name']) ) {
				$response[$key]['EvnPrescr_Name'] .= '<div><span style="font-weight: bold;">Метод введения:</span> ' .  htmlspecialchars($evnPrescr['PrescriptionIntroType_Name']) . '</div>';
			}

			if ( !empty($evnPrescr['PrescriptionTreatType_Name']) ) {
				$response[$key]['EvnPrescr_Name'] .= '<div><span style="font-weight: bold;">Вид назначения:</span> ' .  htmlspecialchars($evnPrescr['PrescriptionTreatType_Name']) . '</div>';

				if ( !empty($evnPrescr['EvnPrescrTreat_CountInDay']) ) {
					$response[$key]['EvnPrescr_Name'] .= '<div><span style="font-weight: bold;">Количество в день:</span> ' .  htmlspecialchars($evnPrescr['EvnPrescrTreat_CountInDay']) . '</div>';
				}
			}

			if ( !empty($evnPrescr['ObservTimeType_Name']) ) {
				$response[$key]['EvnPrescr_Name'] .= '<div><span style="font-weight: bold;">Время наблюдения:</span> ' .  htmlspecialchars($evnPrescr['ObservTimeType_Name']) . '</div>';
			}

			if ( !empty($evnPrescr['PrescriptionType_Code']) && $evnPrescr['PrescriptionType_Code'] == 5 ) {
				// Получаем список медикаментов
				$response[$key]['EvnPrescr_Name'] .= '<div style="font-weight: bold;">Медикаменты:</div>';

				$responseTmp = $this->getEvnPrescrTreatDrugData(array(
					'EvnPrescr_id' => $evnPrescr['EvnPrescr_pid']
				));

				foreach ( $responseTmp as $keyTmp => $evnPrescrTmp ) {
					$response[$key]['EvnPrescr_Name'] .= '<div>- ' . htmlspecialchars($evnPrescrTmp['DrugPrep_Name']) . '</div>';
				}

				$response[$key]['EvnPrescr_Name'] .= "</div>";
			}

			if ( !empty($evnPrescr['EvnPrescr_Descr']) ) {
				$response[$key]['EvnPrescr_Name'] .= '<div><span style="font-weight: bold;">Комментарий:</span> ' .  htmlspecialchars($evnPrescr['EvnPrescr_Descr']) . '</div>';
			}

			$response[$key]['EvnPrescr_Name'] .= '</div>';
		}

		return $response;
	}
	
	/**
	 * Сохранение причины невыполнения назначенной процедуры
	 */
	function saveEvnPrescrUnExecReason($data) {
		$result = $this->saveObject('EvnPrescr', array(
			'EvnPrescr_id' => $data['EvnPrescr_id'],
			'PrescrFailureType_id' => empty($data['PrescrFailureType_id']) ? null:$data['PrescrFailureType_id']
		));

		if (!empty($result['Error_Code'])) {
			return array('Error_Code' => $result['Error_Code'], 'Error_Msg' => $result['Error_Msg']);
		}

		return $result;
	}

	/**
	 *  Получение списка выполненных назначенных процедур
	 *  Используется: журнал выполненных процедур
	 * Метод, скорее всего, устаревший
	 */
	function loadEvnPrescrProcCmpJournalGrid($data) {
		$filter = "(1 = 1)";
		$queryParams = array(
			'Lpu_id' => $data['Lpu_id'],
			'MedStaffFact_id' => (!empty($data['session']['CurMedStaffFact_id']) ? $data['session']['CurMedStaffFact_id'] : NULL)
		);

		$filter .= " and EUC.Lpu_id = :Lpu_id";
		$filter .= " and EP.PrescriptionType_id = 6";
		// $filter .= " and MSF.LpuSection_id = coalesce(ES.LpuSection_id, EPS.LpuSection_pid)";

		if ( isset($data['EvnUslugaCommon_setDate_Range'][0]) ) {
			$filter .= " and cast(EUC.EvnUslugaCommon_setDT as date) >= cast(:EvnUslugaCommon_setDate_Range_0 as date)";
			$queryParams['EvnUslugaCommon_setDate_Range_0'] = $data['EvnUslugaCommon_setDate_Range'][0];
		}

		if ( isset($data['EvnUslugaCommon_setDate_Range'][1]) ) {
			$filter .= " and cast(EUC.EvnUslugaCommon_setDT as date) <= cast(:EvnUslugaCommon_setDate_Range_1 as date)";
			$queryParams['EvnUslugaCommon_setDate_Range_1'] = $data['EvnUslugaCommon_setDate_Range'][1];
		}

		if ( !empty($data['Person_Birthday']) ) {
			$filter .= " and PS.Person_Birthday = :Person_Birthday";
			$queryParams['Person_Birthday'] = $data['Person_Birthday'];
		}

		if ( !empty($data['Person_Firname']) ) {
			$filter .= " and PS.Person_Firname ilike :Person_Firname";
			$queryParams['Person_Firname'] = $data['Person_Firname'] . '%';
		}

		if ( !empty($data['Person_Secname']) ) {
			$filter .= " and PS.Person_Secname ilike :Person_Secname";
			$queryParams['Person_Secname'] = $data['Person_Secname'] . '%';
		}

		if ( !empty($data['Person_Surname']) ) {
			$filter .= " and PS.Person_Surname ilike :Person_Surname";
			$queryParams['Person_Surname'] = $data['Person_Surname'] . '%';
		}


		$query = "
			select
				-- select
				 EUC.EvnUslugaCommon_id as \"EvnUslugaCommon_id\",
				 EPP.EvnPrescrProc_id as \"EvnPrescrProc_id\",
				 coalesce(EUC.EvnUslugaCommon_IsSigned, 1) as \"EvnUslugaCommon_IsSigned\",
				 EUC.Person_id as \"Person_id\",
				 EUC.PersonEvn_id as \"PersonEvn_id\",
				 EUC.Server_id as \"Server_id\",
				 to_char(EUC.EvnUslugaCommon_setDT, 'dd.mm.yyyy') as \"EvnUslugaCommon_setDate\",
				 coalesce(EPS.EvnPS_NumCard, '') as \"EvnPS_NumCard\",
				 coalesce(LS.LpuSection_Name, '') as \"LpuSection_Name\",
				 coalesce(U.Usluga_Name, '') as \"Usluga_Name\",
				 RTRIM(LTRIM(coalesce(PS.Person_Surname, '') || ' ' || coalesce(PS.Person_Firname, '') || ' ' || coalesce(PS.Person_Secname, ''))) as \"Person_FIO\",
				 to_char(PS.Person_Birthday, 'dd.mm.yyyy') as \"Person_Birthday\",
				 to_char(EUC.EvnUslugaCommon_signDT, 'dd.mm.yyyy') as \"EvnUslugaCommon_signDate\"
				-- end select
			from
				-- from
				v_EvnUslugaCommon EUC
				inner join v_EvnPrescrProc EPP on EPP.EvnPrescrProc_id = EUC.EvnPrescr_id
				inner join v_EvnPrescr EP on EP.EvnPrescr_id = EPP.EvnPrescrProc_pid
				inner join v_EvnPS EPS on EPS.EvnPS_id = EUC.EvnUslugaCommon_rid
				inner join v_PersonState PS on PS.Person_id = EPS.Person_id
				inner join LpuSection LS on LS.LpuSection_id = EUC.LpuSection_uid
				inner join Usluga U on U.Usluga_id = EUC.Usluga_id
				left join v_EvnSection ES on ES.EvnSection_id = EP.EvnPrescr_pid
				left join lateral(
					select LpuSection_id
					from v_MedStaffFact
					where MedStaffFact_id = :MedStaffFact_id
					limit 1
				) MSF on true
				-- end from
			where
				-- where
				" . $filter . "
				-- end where
			order by
				-- order by
				EUC.EvnUslugaCommon_setDT,
				PS.Person_Surname,
				PS.Person_Firname,
				PS.Person_Secname,
				PS.Person_Birthday
				-- end order by
		";

		// echo getDebugSQL($query, $queryParams); exit();

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

    /**
     * Получение списка назначений для журнала назначений: МАРМ версия
     */
	function mLoadEvnPrescrJournalGrid($data) {

		$queryParams = array(
			'EvnPrescrDay_begDate' => $data['EvnPrescr_begDate'],
			'EvnPrescrDay_endDate' => $data['EvnPrescr_endDate'],
			'LpuSection_id' => $data['LpuSection_id'],
			'Lpu_id' => $data['Lpu_id']
		);

		$filter = ''; $union_filter = ''; $main_filter = '';

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
				PS.Sex_id as Sex_id,
				case when peh.PersonEncrypHIV_Encryp is null then PS.Person_Birthday end as Person_Birthday,
				case when peh.PersonEncrypHIV_Encryp is null then PS.Person_SurName else peh.PersonEncrypHIV_Encryp end as Person_Surname,
				case when peh.PersonEncrypHIV_Encryp is null then PS.Person_FirName end as Person_Firname,
				case when peh.PersonEncrypHIV_Encryp is null then PS.Person_SecName end as Person_Secname,";
		} else {
			$selectPersonData = "PS.Sex_id as Sex_id,
					PS.Person_Birthday as Person_Birthday,
					PS.Person_Surname as Person_Surname,
					PS.Person_Firname as Person_Firname,
					PS.Person_Secname as Person_Secname,";
		}

		if (!empty($data['Person_SurName'])) {
			if (allowPersonEncrypHIV($data['session']) && $isSearchByEncryp) {
				$filter .= " and peh.PersonEncrypHIV_Encryp ilike :Person_SurName";
			} else {
				$filter .= " and PS.Person_SurName ilike :Person_SurName || '%'";
			}
			$queryParams['Person_SurName'] = rtrim($data['Person_SurName']);
		}

		if ( !empty($data['Person_FirName']) ) {
			$filter .= " and PS.Person_FirName ilike :Person_FirName || '%'";
			$queryParams['Person_FirName'] = rtrim($data['Person_FirName']);
		}

		if ( !empty($data['Person_SecName']) ) {
			$filter .= " and PS.Person_SecName ilike :Person_SecName || '%'";
			$queryParams['Person_SecName'] = rtrim($data['Person_SecName']);
		}

		if ( !empty($data['MedPersonal_id']) ) {
			$filter .= " and PMUI.pmUser_Medpersonal_id = :MedPersonal_id";
			$queryParams['MedPersonal_id'] = $data['MedPersonal_id'];
		}

		if (!empty($data['EvnPrescr_IsExec']) ) {
			$filter .= " and coalesce(EP.EvnPrescr_IsExec,1) = 2";
		}

		if ( !empty($data['LpuSectionWard_id']) ) {
			$union_filter .= " and coalesce(EPS.LpuSectionWard_id, ES.LpuSectionWard_id) = :LpuSectionWard_id";
			$queryParams['LpuSectionWard_id'] = $data['LpuSectionWard_id'];
		}

		$prescrTypeFilter = ' and EP.PrescriptionType_id != 4 ';

		if (!empty($data['PrescriptionType_id'])) {
			$prescrTypeFilter = ' and EP.PrescriptionType_id = :PrescriptionType_id ';
			$queryParams['PrescriptionType_id'] = $data['PrescriptionType_id'];
		}

		// не показывать очередь
		if (empty($data['showEvnQueue'])) {
			$main_filter .= " and EQ.EvnQueue_id is null";
		}

		$join = "
			LEFT JOIN LATERAL  (
					Select null EvnPrescr_setTime,
					 null EvnPrescr_execTime,
					 null EvnPrescr_allTime
			 ) arr_time on true
		 ";

		//  время назначения лек.обеспечения (для Уфы)
		if ($data['session']['region']['nick'] == 'ufa') {
			$join = '
			LEFT JOIN LATERAL  (
				select
					case
						when EP.EvnPrescrDay_IsExec = 2
						then null
						else EvnPrescr_setTime
					end as EvnPrescr_setTime,
					case
						when EP.EvnPrescrDay_IsExec = 2
						then EvnPrescr_allTime
						else EvnPrescr_execTime
					end as EvnPrescr_execTime,
					EvnPrescr_allTime
				from r2.fn_getEvnCourseTreatTimeEntry_time (EP.EvnPrescr_id)
			) arr_time on true';
		}

		$query = "
			-- addit with
	 		with tmpEvnPrescrFirst AS(
                (
				-- движения
				select
					EP.PrescriptionType_id as PrescriptionType_id,
					EP.Person_id as Person_id,
					EP.pmUser_insID as pmUser_insID,
					EP.pmUser_updID as pmUser_updID,
					EP.EvnPrescr_id as EvnPrescr_id,
					EP.EvnPrescr_pid as EvnPrescr_pid,
					EP.EvnPrescr_rid as EvnPrescr_rid,
					coalesce(Regime.EvnPrescrRegime_didDT,Diet.EvnPrescrDiet_didDT,Obs.EvnPrescrObserv_didDT,EP.EvnPrescr_didDT) as EvnPrescr_didDT,
					EP.PersonEvn_id as PersonEvn_id,
					EP.Server_id as Server_id,
					EP.Lpu_id as Lpu_id,
					EP.PrescrFailureType_id as PrescrFailureType_id,
					PFT.PrescrFailureType_Name as PrescrFailureType_Name,
					EP.EvnPrescr_insDT as EvnPrescr_insDT,
					EP.EvnPrescr_IsCito as EvnPrescr_IsCito,
					--EP.PrescriptionStatusType_id as PrescriptionStatusType_id,
					EP.EvnPrescr_Descr as EvnPrescr_Descr,
					EP.EvnPrescr_setDT as EvnPrescr_setDT,
					EP.EvnPrescr_setTime as EvnPrescr_setTime,
					EP.EvnPrescr_IsExec as EvnPrescr_IsExec,
					coalesce(EPS.Diag_pid,ES.Diag_id) as Diag_id,
					ES.EvnSection_id as EvnSection_id,
					coalesce(EPS.LpuSectionWard_id, ES.LpuSectionWard_id) as LpuSectionWard_id,
					coalesce(Regime.EvnPrescrRegime_IsExec,Diet.EvnPrescrDiet_IsExec,Obs.EvnPrescrObserv_IsExec,EP.EvnPrescr_IsExec,1) as EvnPrescrDay_IsExec,
					coalesce(Regime.PrescriptionStatusType_id,Diet.PrescriptionStatusType_id,Obs.PrescriptionStatusType_id,EP.PrescriptionStatusType_id) as PrescriptionStatusType_id,
					coalesce(Regime.EvnPrescrRegime_Descr,Diet.EvnPrescrDiet_Descr,Obs.EvnPrescrObserv_Descr,EP.EvnPrescr_Descr) as EvnPrescrDay_Descr,
					coalesce(Regime.EvnPrescrRegime_setDT,Diet.EvnPrescrDiet_setDT,Obs.EvnPrescrObserv_setDT,EP.EvnPrescr_setDT) as EvnPrescrDay_setDT,
					coalesce(Regime.EvnPrescrRegime_setTime,Diet.EvnPrescrDiet_setTime,Obs.EvnPrescrObserv_setTime,EP.EvnPrescr_setTime) as EvnPrescrDay_setTime,
					coalesce(Regime.EvnPrescrRegime_id,Diet.EvnPrescrDiet_id,Obs.EvnPrescrObserv_id,EP.EvnPrescr_id) as EvnPrescrDay_id,
					Regime.PrescriptionRegimeType_id as PrescriptionRegimeType_id,
					Diet.PrescriptionDietType_id as PrescriptionDietType_id,
					Obs.ObservTimeType_id as ObservTimeType_id,
					Obs.pmUser_updID as userUPD
				from v_EvnPrescr EP
					left join v_PrescrFailureType PFT  on EP.PrescrFailureType_id = PFT.PrescrFailureType_id
					inner join v_EvnSection ES  on ES.EvnSection_id = EP.EvnPrescr_pid
					inner join Evn e  on e.Evn_id = ES.EvnSection_pid -- КВС для фильтрации по датам
					left join EvnPS EPS  on 1=0 -- квс не нужна, но может использоваться в фильтрах
					LEFT JOIN LATERAL (
						SELECT
							EvnPrescrObserv_id, PrescriptionStatusType_id,
							EvnPrescrObserv_Descr,
							EvnPrescrObserv_setDT,
							EvnPrescrObserv_didDT,
							EvnPrescrObserv_setTime,
							EvnPrescrObserv_IsExec,
							ObservTimeType_id,
							pmUser_updID
						from v_EvnPrescrObserv EPO
						WHERE
							EP.PrescriptionType_id = 10
							and EPO.Lpu_id = :Lpu_id
							and EPO.EvnPrescrObserv_pid = EP.EvnPrescr_id
							-- оказвыается может и больше 500 по одному назначению. Сделаем огрничения по дате, а то в выборку много ненужных записей попадает
							and cast(EPO.EvnPrescrObserv_setDT as date) >= cast(:EvnPrescrDay_begDate as date)
							and cast(EPO.EvnPrescrObserv_setDT as date) <= cast(:EvnPrescrDay_endDate as date)
                        limit 500  -- по одному назначению вряд ли будет больше 500 наблюдений
					) AS Obs on true
					LEFT JOIN LATERAL  (
						SELECT
							EvnPrescrDiet_id, PrescriptionStatusType_id,
							EvnPrescrDiet_Descr,
							EvnPrescrDiet_setDT,
							EvnPrescrDiet_didDT,
							EvnPrescrDiet_setTime,
							EvnPrescrDiet_IsExec,
							PrescriptionDietType_id
						from v_EvnPrescrDiet EPD
						WHERE EP.PrescriptionType_id = 2
						and EPD.Lpu_id = :Lpu_id
						and EPD.EvnPrescrDiet_pid = EP.EvnPrescr_id
                        limit 500
					) AS Diet on true
					LEFT JOIN LATERAL (
						SELECT
							EvnPrescrRegime_id, PrescriptionStatusType_id,
							EvnPrescrRegime_Descr,
							EvnPrescrRegime_setDT,
							EvnPrescrRegime_didDT,
							EvnPrescrRegime_setTime,
							EvnPrescrRegime_IsExec,
							PrescriptionRegimeType_id
						from v_EvnPrescrRegime WHERE EvnPrescrRegime_pid = EP.EvnPrescr_id
                        limit 10000
					) AS Regime on true
				where (1=1)
					{$prescrTypeFilter}
					and cast(E.Evn_setDT as date) <= cast(:EvnPrescrDay_endDate as date) and coalesce(cast(E.Evn_disDT as date), cast(:EvnPrescrDay_begDate as date)) >= cast(:EvnPrescrDay_begDate as date)
					and ES.LpuSection_id = :LpuSection_id
					and coalesce(ES.EvnSection_IsPriem, 1) = 1
					and EP.Lpu_id = :Lpu_id
					{$union_filter}
                )
				union all
                (
				-- КВС
				select
					EP.PrescriptionType_id as PrescriptionType_id,
					EP.Person_id as Person_id,
					EP.pmUser_insID as pmUser_insID,
					EP.pmUser_updID as pmUser_updID,
					EP.EvnPrescr_id as EvnPrescr_id,
					EP.EvnPrescr_pid as EvnPrescr_pid,
					EP.EvnPrescr_rid as EvnPrescr_rid,
					coalesce(Regime.EvnPrescrRegime_didDT,Diet.EvnPrescrDiet_didDT,Obs.EvnPrescrObserv_didDT,EP.EvnPrescr_didDT) as EvnPrescr_didDT,
					EP.PersonEvn_id as PersonEvn_id,
					EP.Server_id as Server_id,
					EP.Lpu_id as Lpu_id,
					EP.PrescrFailureType_id as PrescrFailureType_id,
					PFT.PrescrFailureType_Name as PrescrFailureType_Name,
					EP.EvnPrescr_insDT as EvnPrescr_insDT,
					EP.EvnPrescr_IsCito as EvnPrescr_IsCito,
					--EP.PrescriptionStatusType_id as PrescriptionStatusType_id,
					EP.EvnPrescr_Descr as EvnPrescr_Descr,
					EP.EvnPrescr_setDT as EvnPrescr_setDT,
					EP.EvnPrescr_setTime as EvnPrescr_setTime,
					EP.EvnPrescr_IsExec as EvnPrescr_IsExec,
					coalesce(EPS.Diag_pid,ES.Diag_id) as Diag_id,
					ES.Evn_id as EvnSection_id,
					coalesce(EPS.LpuSectionWard_id, ES.LpuSectionWard_id) as LpuSectionWard_id,
					coalesce(Regime.EvnPrescrRegime_IsExec,Diet.EvnPrescrDiet_IsExec,Obs.EvnPrescrObserv_IsExec,EP.EvnPrescr_IsExec,1) as EvnPrescrDay_IsExec,
					coalesce(Regime.PrescriptionStatusType_id,Diet.PrescriptionStatusType_id,Obs.PrescriptionStatusType_id,EP.PrescriptionStatusType_id) as PrescriptionStatusType_id,
					coalesce(Regime.EvnPrescrRegime_Descr,Diet.EvnPrescrDiet_Descr,Obs.EvnPrescrObserv_Descr,EP.EvnPrescr_Descr) as EvnPrescrDay_Descr,
					coalesce(Regime.EvnPrescrRegime_setDT,Diet.EvnPrescrDiet_setDT,Obs.EvnPrescrObserv_setDT,EP.EvnPrescr_setDT) as EvnPrescrDay_setDT,
					coalesce(Regime.EvnPrescrRegime_setTime,Diet.EvnPrescrDiet_setTime,Obs.EvnPrescrObserv_setTime,EP.EvnPrescr_setTime) as EvnPrescrDay_setTime,
					coalesce(Regime.EvnPrescrRegime_id,Diet.EvnPrescrDiet_id,Obs.EvnPrescrObserv_id,EP.EvnPrescr_id) as EvnPrescrDay_id,
					Regime.PrescriptionRegimeType_id as PrescriptionRegimeType_id,
					Diet.PrescriptionDietType_id as PrescriptionDietType_id,
					Obs.ObservTimeType_id as ObservTimeType_id,
					Obs.pmUser_updID as userUPD
				from v_EvnPrescr EP
					left join v_PrescrFailureType PFT  on EP.PrescrFailureType_id = PFT.PrescrFailureType_id
					inner join EvnPS EPS  on EPS.Evn_id = EP.EvnPrescr_pid
					inner join Evn e  on e.Evn_id = EPS.Evn_id -- КВС для фильтрации по датам
					left join EvnSection ES  on 1=0 -- движение не нужно, но может использоваться в фильтрах
					LEFT JOIN LATERAL (
						SELECT
							EvnPrescrObserv_id, PrescriptionStatusType_id,
							EvnPrescrObserv_Descr,
							EvnPrescrObserv_setDT,
							EvnPrescrObserv_didDT,
							EvnPrescrObserv_setTime,
							EvnPrescrObserv_IsExec,
							ObservTimeType_id,
							pmUser_updID
						from v_EvnPrescrObserv EPO
						WHERE EP.PrescriptionType_id = 10
							and EPO.Lpu_id = :Lpu_id
							and EPO.EvnPrescrObserv_pid = EP.EvnPrescr_id
							-- оказвыается может и больше 500 по одному назначению. Сделаем огрничения по дате.
							and cast(EPO.EvnPrescrObserv_setDT as date) >= cast(:EvnPrescrDay_begDate as date)
							and cast(EPO.EvnPrescrObserv_setDT as date) <= cast(:EvnPrescrDay_endDate as date)
                        limit 500  -- по одному назначению вряд ли будет больше 500 наблюдений
					) AS Obs on true
					LEFT JOIN LATERAL (
						SELECT
							EvnPrescrDiet_id, PrescriptionStatusType_id,
							EvnPrescrDiet_Descr,
							EvnPrescrDiet_setDT,
							EvnPrescrDiet_didDT,
							EvnPrescrDiet_setTime,
							EvnPrescrDiet_IsExec,
							PrescriptionDietType_id
						from v_EvnPrescrDiet EPD
						WHERE EP.PrescriptionType_id = 2
						and EPD.Lpu_id = :Lpu_id
						and EPD.EvnPrescrDiet_pid = EP.EvnPrescr_id
                        limit 500
					) AS Diet on true
					LEFT JOIN LATERAL (
						SELECT
							EvnPrescrRegime_id, PrescriptionStatusType_id,
							EvnPrescrRegime_Descr,
							EvnPrescrRegime_setDT,
							EvnPrescrRegime_didDT,
							EvnPrescrRegime_setTime,
							EvnPrescrRegime_IsExec,
							PrescriptionRegimeType_id
						from v_EvnPrescrRegime WHERE EvnPrescrRegime_pid = EP.EvnPrescr_id
                        limit 10000
					) AS Regime on true
				where (1=1)
					{$prescrTypeFilter}
					and cast(E.Evn_setDT as date) <= cast(:EvnPrescrDay_endDate as date) and coalesce(cast(E.Evn_disDT as date), cast(:EvnPrescrDay_begDate as date)) >= cast(:EvnPrescrDay_begDate as date)
					and EPS.LpuSection_pid = :LpuSection_id
					and EP.Lpu_id = :Lpu_id
					{$union_filter}
                )
				union all
                (
				-- назначения в приёмном (если они сохраняются на КВС)
				select
					EP.PrescriptionType_id as PrescriptionType_id,
					EP.Person_id as Person_id,
					EP.pmUser_insID as pmUser_insID,
					EP.pmUser_updID as pmUser_updID,
					EP.EvnPrescr_id as EvnPrescr_id,
					EP.EvnPrescr_pid as EvnPrescr_pid,
					EP.EvnPrescr_rid as EvnPrescr_rid,
					coalesce(Regime.EvnPrescrRegime_didDT,Diet.EvnPrescrDiet_didDT,Obs.EvnPrescrObserv_didDT,EP.EvnPrescr_didDT) as EvnPrescr_didDT,
					EP.PersonEvn_id as PersonEvn_id,
					EP.Server_id as Server_id,
					EP.Lpu_id as Lpu_id,
					EP.PrescrFailureType_id as PrescrFailureType_id,
					PFT.PrescrFailureType_Name as PrescrFailureType_Name,
					EP.EvnPrescr_insDT as EvnPrescr_insDT,
					EP.EvnPrescr_IsCito as EvnPrescr_IsCito,
					--EP.PrescriptionStatusType_id as PrescriptionStatusType_id,
					EP.EvnPrescr_Descr as EvnPrescr_Descr,
					EP.EvnPrescr_setDT as EvnPrescr_setDT,
					EP.EvnPrescr_setTime as EvnPrescr_setTime,
					EP.EvnPrescr_IsExec as EvnPrescr_IsExec,
					ES.Diag_id as Diag_id,
					ES.EvnSection_id as EvnSection_id,
					ES.LpuSectionWard_id as LpuSectionWard_id,
					coalesce(Regime.EvnPrescrRegime_IsExec,Diet.EvnPrescrDiet_IsExec,Obs.EvnPrescrObserv_IsExec,EP.EvnPrescr_IsExec,1) as EvnPrescrDay_IsExec,
					coalesce(Regime.PrescriptionStatusType_id,Diet.PrescriptionStatusType_id,Obs.PrescriptionStatusType_id,EP.PrescriptionStatusType_id) as PrescriptionStatusType_id,
					coalesce(Regime.EvnPrescrRegime_Descr,Diet.EvnPrescrDiet_Descr,Obs.EvnPrescrObserv_Descr,EP.EvnPrescr_Descr) as EvnPrescrDay_Descr,
					coalesce(Regime.EvnPrescrRegime_setDT,Diet.EvnPrescrDiet_setDT,Obs.EvnPrescrObserv_setDT,EP.EvnPrescr_setDT) as EvnPrescrDay_setDT,
					coalesce(Regime.EvnPrescrRegime_setTime,Diet.EvnPrescrDiet_setTime,Obs.EvnPrescrObserv_setTime,EP.EvnPrescr_setTime) as EvnPrescrDay_setTime,
					coalesce(Regime.EvnPrescrRegime_id,Diet.EvnPrescrDiet_id,Obs.EvnPrescrObserv_id,EP.EvnPrescr_id) as EvnPrescrDay_id,
					Regime.PrescriptionRegimeType_id as PrescriptionRegimeType_id,
					Diet.PrescriptionDietType_id as PrescriptionDietType_id,
					Obs.ObservTimeType_id as ObservTimeType_id,
					Obs.pmUser_updID as userUPD
				from v_EvnPrescr EP
					left join v_PrescrFailureType PFT  on EP.PrescrFailureType_id = PFT.PrescrFailureType_id
					inner join v_EvnSection ES  on ES.EvnSection_pid = EP.EvnPrescr_pid -- внутри КВС есть движение текущего отделения
					inner join Evn e  on e.Evn_id = ES.EvnSection_pid -- КВС для фильтрации по датам
					left join v_EvnPS eps  on 1=0 -- квс не нужна, но может использоваться в фильтрах
					LEFT JOIN LATERAL (
						SELECT
							EvnPrescrObserv_id, PrescriptionStatusType_id,
							EvnPrescrObserv_Descr,
							EvnPrescrObserv_setDT,
							EvnPrescrObserv_didDT,
							EvnPrescrObserv_setTime,
							EvnPrescrObserv_IsExec,
							ObservTimeType_id,
							pmUser_updID
						from v_EvnPrescrObserv EPO
						WHERE EP.PrescriptionType_id = 10
							and EPO.Lpu_id = :Lpu_id
							and EPO.EvnPrescrObserv_pid = EP.EvnPrescr_id
							-- оказвыается может и больше 500 по одному назначению. Сделаем огрничения по дате.
							and cast(EPO.EvnPrescrObserv_setDT as date) >= cast(:EvnPrescrDay_begDate as date)
							and cast(EPO.EvnPrescrObserv_setDT as date) <= cast(:EvnPrescrDay_endDate as date)
                        limit 500 -- по одному назначению вряд ли будет больше 500 наблюдений
					) AS Obs on true
					LEFT JOIN LATERAL (
						SELECT
							EvnPrescrDiet_id, PrescriptionStatusType_id,
							EvnPrescrDiet_Descr,
							EvnPrescrDiet_setDT,
							EvnPrescrDiet_didDT,
							EvnPrescrDiet_setTime,
							EvnPrescrDiet_IsExec,
							PrescriptionDietType_id
						from v_EvnPrescrDiet EPD
						WHERE EP.PrescriptionType_id = 2
						and EPD.Lpu_id = :Lpu_id
						and EPD.EvnPrescrDiet_pid = EP.EvnPrescr_id
                        limit 500
					) AS Diet on true
					LEFT JOIN LATERAL (
						SELECT
							EvnPrescrRegime_id, PrescriptionStatusType_id,
							EvnPrescrRegime_Descr,
							EvnPrescrRegime_setDT,
							EvnPrescrRegime_didDT,
							EvnPrescrRegime_setTime,
							EvnPrescrRegime_IsExec,
							PrescriptionRegimeType_id
						from v_EvnPrescrRegime WHERE EvnPrescrRegime_pid = EP.EvnPrescr_id
                        limit 10000
					) AS Regime on true
				where (1=1)
					{$prescrTypeFilter}
					and cast(E.Evn_setDT as date) <= cast(:EvnPrescrDay_endDate as date) and coalesce(cast(E.Evn_disDT as date), cast(:EvnPrescrDay_begDate as date)) >= cast(:EvnPrescrDay_begDate as date)
					and ES.LpuSection_id = :LpuSection_id
					and EP.Lpu_id = :Lpu_id
					{$union_filter}
                )
				union all
                (
				-- назначения в приёмном движении (если они сохраняются на приёмное движение)
				select
					EP.PrescriptionType_id as PrescriptionType_id,
					EP.Person_id as Person_id,
					EP.pmUser_insID as pmUser_insID,
					EP.pmUser_updID as pmUser_updID,
					EP.EvnPrescr_id as EvnPrescr_id,
					EP.EvnPrescr_pid as EvnPrescr_pid,
					EP.EvnPrescr_rid as EvnPrescr_rid,
					coalesce(Regime.EvnPrescrRegime_didDT,Diet.EvnPrescrDiet_didDT,Obs.EvnPrescrObserv_didDT,EP.EvnPrescr_didDT) as EvnPrescr_didDT,
					EP.PersonEvn_id as PersonEvn_id,
					EP.Server_id as Server_id,
					EP.Lpu_id as Lpu_id,
					EP.PrescrFailureType_id as PrescrFailureType_id,
					PFT.PrescrFailureType_Name as PrescrFailureType_Name,
					EP.EvnPrescr_insDT as EvnPrescr_insDT,
					EP.EvnPrescr_IsCito as EvnPrescr_IsCito,
					--EP.PrescriptionStatusType_id as PrescriptionStatusType_id,
					EP.EvnPrescr_Descr as EvnPrescr_Descr,
					EP.EvnPrescr_setDT as EvnPrescr_setDT,
					EP.EvnPrescr_setTime as EvnPrescr_setTime,
					EP.EvnPrescr_IsExec as EvnPrescr_IsExec,
					ES.Diag_id as Diag_id,
					ES.EvnSection_id as EvnSection_id,
					ES.LpuSectionWard_id as LpuSectionWard_id,
					coalesce(Regime.EvnPrescrRegime_IsExec,Diet.EvnPrescrDiet_IsExec,Obs.EvnPrescrObserv_IsExec,EP.EvnPrescr_IsExec,1) as EvnPrescrDay_IsExec,
					coalesce(Regime.PrescriptionStatusType_id,Diet.PrescriptionStatusType_id,Obs.PrescriptionStatusType_id,EP.PrescriptionStatusType_id) as PrescriptionStatusType_id,
					coalesce(Regime.EvnPrescrRegime_Descr,Diet.EvnPrescrDiet_Descr,Obs.EvnPrescrObserv_Descr,EP.EvnPrescr_Descr) as EvnPrescrDay_Descr,
					coalesce(Regime.EvnPrescrRegime_setDT,Diet.EvnPrescrDiet_setDT,Obs.EvnPrescrObserv_setDT,EP.EvnPrescr_setDT) as EvnPrescrDay_setDT,
					coalesce(Regime.EvnPrescrRegime_setTime,Diet.EvnPrescrDiet_setTime,Obs.EvnPrescrObserv_setTime,EP.EvnPrescr_setTime) as EvnPrescrDay_setTime,
					coalesce(Regime.EvnPrescrRegime_id,Diet.EvnPrescrDiet_id,Obs.EvnPrescrObserv_id,EP.EvnPrescr_id) as EvnPrescrDay_id,
					Regime.PrescriptionRegimeType_id as PrescriptionRegimeType_id,
					Diet.PrescriptionDietType_id as PrescriptionDietType_id,
					Obs.ObservTimeType_id as ObservTimeType_id,
					Obs.pmUser_updID as userUPD
				from v_EvnPrescr EP
					left join v_PrescrFailureType PFT  on EP.PrescrFailureType_id = PFT.PrescrFailureType_id
					inner join v_EvnSection ES2  on ES2.EvnSection_id = EP.EvnPrescr_pid
					inner join v_EvnSection ES  on ES.EvnSection_pid = ES2.EvnSection_pid -- внутри КВС есть движение текущего отделения
					inner join Evn e  on e.Evn_id = ES.EvnSection_pid -- КВС для фильтрации по датам
					left join v_EvnPS eps  on 1=0 -- квс не нужна, но может использоваться в фильтрах
					LEFT JOIN LATERAL (
						SELECT
							EvnPrescrObserv_id, PrescriptionStatusType_id,
							EvnPrescrObserv_Descr,
							EvnPrescrObserv_setDT,
							EvnPrescrObserv_didDT,
							EvnPrescrObserv_setTime,
							EvnPrescrObserv_IsExec,
							ObservTimeType_id,
							pmUser_updID
						from v_EvnPrescrObserv EPO
						WHERE EP.PrescriptionType_id = 10
							and EPO.Lpu_id = :Lpu_id
							and EPO.EvnPrescrObserv_pid = EP.EvnPrescr_id
							-- оказвыается может и больше 500 по одному назначению. Сделаем огрничения по дате.
							and cast(EPO.EvnPrescrObserv_setDT as date) >= cast(:EvnPrescrDay_begDate as date)
							and cast(EPO.EvnPrescrObserv_setDT as date) <= cast(:EvnPrescrDay_endDate as date)
                        limit 500 -- по одному назначению вряд ли будет больше 500 наблюдений
					) AS Obs on true
					LEFT JOIN LATERAL (
						SELECT
							EvnPrescrDiet_id, PrescriptionStatusType_id,
							EvnPrescrDiet_Descr,
							EvnPrescrDiet_setDT,
							EvnPrescrDiet_didDT,
							EvnPrescrDiet_setTime,
							EvnPrescrDiet_IsExec,
							PrescriptionDietType_id
						from v_EvnPrescrDiet EPD
						WHERE EP.PrescriptionType_id = 2
						and EPD.Lpu_id = :Lpu_id
						and EPD.EvnPrescrDiet_pid = EP.EvnPrescr_id
                        limit 500
					) AS Diet on true
					LEFT JOIN LATERAL (
						SELECT
							EvnPrescrRegime_id, PrescriptionStatusType_id,
							EvnPrescrRegime_Descr,
							EvnPrescrRegime_setDT,
							EvnPrescrRegime_didDT,
							EvnPrescrRegime_setTime,
							EvnPrescrRegime_IsExec,
							PrescriptionRegimeType_id
						from v_EvnPrescrRegime WHERE EvnPrescrRegime_pid = EP.EvnPrescr_id
                        limit 10000
					) AS Regime on true
				where (1=1)
					{$prescrTypeFilter}
					and cast(E.Evn_setDT as date) <= cast(:EvnPrescrDay_endDate as date) and coalesce(cast(E.Evn_disDT as date), cast(:EvnPrescrDay_begDate as date)) >= cast(:EvnPrescrDay_begDate as date)
					and ES.LpuSection_id = :LpuSection_id
					and ES2.EvnSection_IsPriem = 2
					and EP.Lpu_id = :Lpu_id
					{$union_filter}
			    )
            ), EvnPrescrAll as (
				select
					EP.PrescriptionType_id as PrescriptionType_id,
					EP.EvnPrescr_id as EvnPrescr_id,
					EP.EvnPrescr_pid as EvnPrescr_pid,
					EP.EvnPrescr_rid as EvnPrescr_rid,
					EP.EvnPrescr_didDT as EvnPrescr_didDT,
					EP.Person_id as Person_id,
					EP.PersonEvn_id as PersonEvn_id,
					EP.Server_id as Server_id,
					{$selectPersonData}
					EP.Lpu_id as Lpu_id,
					EP.PrescrFailureType_id as PrescrFailureType_id,
					EP.PrescrFailureType_Name as PrescrFailureType_Name,
					EP.Diag_id as Diag_id,
					EP.EvnSection_id as EvnSection_id,
					EP.LpuSectionWard_id as LpuSectionWard_id,
					EP.EvnPrescr_insDT as EvnPrescr_insDT,
					coalesce(PMUI.pmUser_Name, '') as pmUser_insName,
					coalesce(PMUU.pmUser_Name, '') as pmUser_execName,
					coalesce(EP.EvnPrescr_IsCito, 1) as EvnPrescr_IsCito,
					EP.EvnPrescrDay_IsExec as EvnPrescrDay_IsExec,
					EP.PrescriptionStatusType_id as PrescriptionStatusType_id,
					EP.EvnPrescrDay_Descr as EvnPrescrDay_Descr,
					EP.EvnPrescrDay_setDT as EvnPrescrDay_setDT,
					EP.EvnPrescrDay_setTime as EvnPrescrDay_setTime,
					EP.EvnPrescrDay_id as EvnPrescrDay_id,
					EP.PrescriptionRegimeType_id as PrescriptionRegimeType_id,
					EP.PrescriptionDietType_id as PrescriptionDietType_id,
					EP.ObservTimeType_id as ObservTimeType_id,
					EP.userUPD as userUPD
				from tmpEvnPrescrFirst EP
				inner join v_PersonState PS  on PS.Person_id = EP.Person_id
				left join v_pmUser PMUI  on PMUI.pmUser_id = EP.pmUser_insID
				left join v_pmUser PMUU  on PMUU.pmUser_id = EP.pmUser_updID
				left join v_PersonEncrypHIV peh  on peh.Person_id = PS.Person_id
				where 1 = 1
				{$filter}
			)
			-- end addit with

			select
				-- select
				EP.EvnPrescrDay_id as \"EvnPrescrDay_id\"
				,EP.EvnPrescr_id as \"EvnPrescr_id\"
				,EP.EvnPrescr_pid as \"EvnPrescr_pid\"
				,EP.EvnPrescr_rid as \"EvnPrescr_rid\"
				,EP.Diag_id as \"Diag_id\"
				,EP.Person_id as \"Person_id\"
				,EP.PersonEvn_id as \"PersonEvn_id\"
				,EP.Server_id as \"Server_id\"
				,EP.PrescrFailureType_id as \"PrescrFailureType_id\"
				,EP.PrescrFailureType_Name as \"PrescrFailureType_Name\"
				,EP.PrescriptionType_id as \"PrescriptionType_id\"
				,EP.EvnPrescr_IsCito as \"EvnPrescr_IsCito\"
				,EP.EvnPrescrDay_IsExec as \"EvnPrescr_IsExec\"
				,to_char(EP.EvnPrescrDay_setDT, 'dd.mm.yyyy') as \"EvnPrescr_setDate\"
				,case
					when TTMS.TimetableMedService_id is not null then
						to_char(TTMS.TimetableMedService_begTime, 'hh:mm:ss')
					when TTR.TimetableResource_id is not null then
						to_char(TTR.TimetableResource_begTime, 'hh:mm:ss')
					when EQ.EvnQueue_id is not null then
						'Очередь'
					when arr_time.EvnPrescr_setTime is not null then
							arr_time.EvnPrescr_setTime
					else 'б/з'
				end	as \"EvnPrescr_planTime\"
				,case when EP.EvnPrescrDay_IsExec = 2 then
					to_char(coalesce(EU.EvnUsluga_disDT, EP.EvnPrescr_didDT) , 'dd.mm.yyyy') || ' ' || to_char(coalesce(EU.EvnUsluga_disDT, EP.EvnPrescr_didDT), 'hh:mm:ss')
				else
					null
				end as \"EvnPrescr_execDate\"
				,COALESCE(arr_time.EvnPrescr_setTime,
					to_char(TTMS.TimetableMedService_begTime, 'hh:mm:ss'), to_char(EP.EvnPrescrDay_setTime,'hh:mm:ss')) as \"EvnPrescr_setTime\"
				, arr_time.EvnPrescr_execTime as \"EvnPrescr_execTime\"
				,RTRIM(LTRIM(coalesce(EP.Person_Surname, ''))) || ' ' || RTRIM(LTRIM(coalesce(EP.Person_Firname, ''))) || ' ' || RTRIM(LTRIM(coalesce(EP.Person_Secname, ''))) as \"Person_FIO\"
				,to_char(EP.Person_Birthday, 'dd.mm.yyyy') as \"Person_Birthday\"
				,dbo.Age2(EP.Person_Birthday, dbo.tzGetDate()) as \"Person_Age\"
				,coalesce(PT.PrescriptionType_Code, 0) as \"PrescriptionType_Code\"
				,coalesce(PT.PrescriptionType_Name, '') as \"PrescriptionType_Name\"
				,to_char(EP.EvnPrescr_insDT, 'dd.mm.yyyy') || ' ' || to_char(EP.EvnPrescr_insDT, 'hh:mm:ss') as \"EvnPrescr_insDT\"
				,YN.YesNo_Name as \"IsExec_Name\"
				,EP.EvnPrescrDay_Descr as \"EvnPrescr_Descr\"
				,'' as \"LpuSectionProfile_Name\"
				,EP.pmUser_insName as \"pmUser_insName\"
				,LSW.LpuSectionWard_id as \"LpuSectionWard_id\"
				,EP.Sex_id as \"Sex_id\"
				,EP.EvnSection_id as \"EvnSection_id\"
				,coalesce(LSW.LpuSectionWard_Name, 'Без палаты') as \"LpuSectionWard_Name\"
				,ED.EvnDirection_id as \"EvnDirection_id\"
				,to_char(ED.EvnDirection_setDate, 'dd.mm.yyyy') as \"EvnDirection_setDate\"
				--1
				,coalesce(PRT.PrescriptionRegimeType_Name, '') as \"PrescriptionRegimeType_Name\"
				--2
				,coalesce(PDT.PrescriptionDietType_Name, '') as \"PrescriptionDietType_Name\"
				--10
				,EP.ObservTimeType_id as \"ObservTimeType_id\"
				,coalesce(OTT.ObservTimeType_Name, '') as \"ObservTimeType_Name\"
				-- остальное, чего может быть несколько для одного назначения, надо будет получить отдельными запросами
				--5
				,0 as \"PrescriptionTreatType_Code\"
				,'' as \"PrescriptionTreatType_Name\"
				,coalesce(ECT.EvnCourseTreat_MaxCountDay, 0) as \"EvnPrescrTreat_CountInDay\"
				,coalesce(PIT.PrescriptionIntroType_Name, '') as \"PrescriptionIntroType_Name\"
				,coalesce(PFT.PerformanceType_Name, '') as \"PerformanceType_Name\"
				,Treat.EvnPrescrTreat_PrescrCount as \"PrescrCntDay\"
				--данные медикаментов нужно будет получить отдельно
				,null as \"EvnPrescrTreatDrug_id\"
				,null as \"EvnPrescrTreatDrug_KolvoEd\"
				,null as \"DrugForm_Name\"
				,null as \"EvnPrescrTreatDrug_Kolvo\"
				,null as \"Okei_NationSymbol\"
				,null as \"Drug_Name\"
				,null as \"DrugTorg_Name\"
				,0 as \"cntDrug\"
				,null as \"DoseDay\"
				,null as \"FactCntDay\"
				--5,6
				,coalesce(ECT.EvnCourseTreat_MaxCountDay,ECP.EvnCourseProc_MaxCountDay, 0) as \"CountInDay\"
				,coalesce(ECT.EvnCourseTreat_Duration,ECP.EvnCourseProc_Duration, 0) as \"CourseDuration\"
				,coalesce(ECT.EvnCourseTreat_ContReception,ECP.EvnCourseProc_ContReception, 0) as \"ContReception\"
				,coalesce(ECT.EvnCourseTreat_Interval,ECP.EvnCourseProc_Interval, 0) as \"Interval\"
				,coalesce(DTP.DurationType_Nick, '') as \"DurationTypeP_Nick\"
				,coalesce(DTN.DurationType_Nick, '') as \"DurationTypeN_Nick\"
				,coalesce(DTI.DurationType_Nick, '') as \"DurationTypeI_Nick\"
				--6,11,13 для 7,12 нужно будет получить отдельно
				,case when EU.EvnUsluga_id is null then 1 else 2 end as \"EvnPrescr_IsHasEvn\"
				,case
					when EP.PrescriptionType_id in (6,11,13) then 1
					else null
				end as \"TableUsluga_id\"
				,UC.UslugaComplex_Name as \"UslugaComplex_Name\"
				,UC.UslugaComplex_id as \"UslugaComplex_id\"
				,case
					when EP.PrescriptionType_id in (6,11,13) then 1
					else null
				end as \"cntUsluga\"
				,case when EP.EvnPrescrDay_IsExec = 2 then
					case
						when EP.PrescriptionType_id IN (6,7,11,12,13,14) then MP.Person_SurName || ' ' || coalesce(MP.Person_FirName, '') || ' ' || coalesce(MP.Person_SecName, '')
						when EP.PrescriptionType_id = 10 then ObsPMU.PMUser_Name
					  	else EP.pmUser_execName
				  	end
				else ''
				end as \"pmUser_execName\"
				-- end select
			from
				-- from
				EvnPrescrAll EP
				left join v_LpuSectionWard LSW  on LSW.LpuSectionWard_id = EP.LpuSectionWard_id
				LEFT JOIN LATERAL (
					Select
						ED.EvnDirection_id,
						ED.EvnStatus_id,
						ED.MedService_id,
						ED.EvnDirection_setDate
					from v_EvnPrescrDirection epd
					inner join v_EvnDirection_all ED  on epd.EvnDirection_id = ED.EvnDirection_id
						and ED.EvnDirection_failDT is null
						and coalesce(ED.EvnStatus_id, 16) not in (12,13)
					where epd.EvnPrescr_id = EP.EvnPrescr_id
					order by epd.EvnPrescrDirection_insDT desc
                    limit 1
				) ED on true
				LEFT JOIN LATERAL (
					Select  TimetableMedService_id, TimetableMedService_begTime from v_TimetableMedService_lite TTMS  where TTMS.EvnDirection_id = ED.EvnDirection_id limit 1
				) TTMS on true
				LEFT JOIN LATERAL (
					Select TimetableResource_id, TimetableResource_begTime from v_TimetableResource_lite TTR  where TTR.EvnDirection_id = ED.EvnDirection_id limit 1
				) TTR on true
				LEFT JOIN LATERAL (
					Select EQ.EvnQueue_id, EQ.EvnQueue_setDate from v_EvnQueue EQ  where EQ.EvnDirection_id = ED.EvnDirection_id and EQ.EvnQueue_failDT is null limit 1
				) EQ on true
				inner join PrescriptionType PT  on PT.PrescriptionType_id = EP.PrescriptionType_id
				left join YesNo YN  on YN.YesNo_id = EP.EvnPrescrDay_IsExec
				--1
				left join PrescriptionRegimeType PRT  on EP.PrescriptionType_id = 1 and PRT.PrescriptionRegimeType_id = EP.PrescriptionRegimeType_id
				--2
				left join PrescriptionDietType PDT  on EP.PrescriptionType_id = 2 and PDT.PrescriptionDietType_id = EP.PrescriptionDietType_id
				--5
				left join v_EvnPrescrTreat Treat  on Treat.EvnPrescrTreat_id = EP.EvnPrescr_id
				left join v_EvnCourseTreat ECT  on Treat.EvnCourse_id = ECT.EvnCourseTreat_id
				left join PrescriptionIntroType PIT  on ECT.PrescriptionIntroType_id = PIT.PrescriptionIntroType_id
				left join PerformanceType PFT  on  ECT.PerformanceType_id = PFT.PerformanceType_id
				--6
				left join v_EvnPrescrProc EPPR  on EP.PrescriptionType_id = 6 and EPPR.EvnPrescrProc_id = EP.EvnPrescr_id
				left join v_EvnCourseProc ECP  on EP.PrescriptionType_id = 6 and EPPR.EvnCourse_id = ECP.EvnCourseProc_id
				--5,6
				left join DurationType DTP  on coalesce(ECP.DurationType_id,ECT.DurationType_id) = DTP.DurationType_id
				left join DurationType DTN  on coalesce(ECP.DurationType_recid,ECT.DurationType_recid) = DTN.DurationType_id
				left join DurationType DTI  on coalesce(ECP.DurationType_intid,ECT.DurationType_intid) = DTI.DurationType_id
				--10
				left join ObservTimeType OTT  on EP.PrescriptionType_id = 10 and OTT.ObservTimeType_id = EP.ObservTimeType_id
				left join v_pmUserCache ObsPMU  on ObsPMU.PMUser_id = EP.userUPD
				--11
				left join v_EvnUsluga_all EUAll  on ED.EvnDirection_id = EuAll.EvnDirection_id
				left join v_MedPersonal MP  on MP.MedPersonal_id = EUAll.MedPersonal_id AND MP.Lpu_id = EUAll.Lpu_id
				--left join v_EvnPrescrLabDiag EPLD  on EP.PrescriptionType_id = 11 and EPLD.EvnPrescrLabDiag_id = EP.EvnPrescr_id
				LEFT JOIN LATERAL (
					select  * from v_EvnPrescrLabDiag EPLD  where EP.PrescriptionType_id = 11 and EvnPrescrLabDiag_id = EP.EvnPrescr_id limit 100
				) EPLD on true
				--13,6
				--left join v_EvnPrescrConsUsluga EPCU  on EP.PrescriptionType_id = 13 and EPCU.EvnPrescrConsUsluga_id = EP.EvnPrescr_id
				LEFT JOIN LATERAL (
					select * from v_EvnPrescrConsUsluga  where EP.PrescriptionType_id = 13 and EvnPrescrConsUsluga_id = EP.EvnPrescr_id limit 100
				) EPCU on true
				left join v_UslugaComplex UC  on UC.UslugaComplex_id = coalesce(EPPR.UslugaComplex_id,EPLD.UslugaComplex_id,EPCU.UslugaComplex_id)
				LEFT JOIN LATERAL (
					select  EvnUsluga_id, coalesce(EvnUsluga_disDT, EvnUsluga_setDT) as EvnUsluga_disDT from v_EvnUsluga
					where EP.EvnPrescrDay_IsExec = 2 and UC.UslugaComplex_id is not null and EvnPrescr_id = EP.EvnPrescr_id
                    limit 1
				) EU on true
				{$join}
				-- end from
			where
				-- where
				(1=1)
				{$main_filter}
				and cast(coalesce(TTMS.TimetableMedService_begTime,TTR.TimetableResource_begTime,EQ.EvnQueue_setDate,EP.EvnPrescrDay_setDT) as date) >= cast(:EvnPrescrDay_begDate as date)
				and cast(coalesce(TTMS.TimetableMedService_begTime,TTR.TimetableResource_begTime,EQ.EvnQueue_setDate,EP.EvnPrescrDay_setDT) as date) <= cast(:EvnPrescrDay_endDate as date)
				-- end where
			order by
				-- order by
				EP.EvnPrescrDay_setDT,
				EP.PrescriptionType_id,
				EP.EvnPrescr_id
				-- end order by
		";

		//echo '<pre>',print_r(getDebugSQL($query, $queryParams)),'</pre>'; die();

		$response = $this->getPagingResponse($query, $queryParams, $data['start'], $data['limit'], true);
		$response = $response['data'];

		// сделаем пост-обработку для 5,7,12
		$processed_prescr = array();
		$inlist = array(
			'drug_prescr' => array(),
			'usluga_prescr' => array()
		);

		foreach ($response as $key => $prescr) {
			if (in_array($prescr['PrescriptionType_id'], array(5,7,12))) {

				$processed_prescr[$prescr['EvnPrescr_id']] = $prescr;

				if ($prescr['PrescriptionType_id'] == 5) {
					$inlist['drug_prescr'][] = $prescr['EvnPrescr_id'];
				}

				if (in_array($prescr['PrescriptionType_id'], array(7,12))) {
					$inlist['usluga_prescr'][] = $prescr['EvnPrescr_id'];
				}

				unset($response[$key]);
			}
		}

		if (!empty($inlist['drug_prescr'])) {

			$inlist['drug_prescr'] = implode(',', $inlist['drug_prescr']);

			$drugs_prescr_data = $this->queryResult("
				select
					EPTD.EvnPrescrTreat_id as \"EvnPrescr_id\",
					coalesce(Drug.Drug_Name, dcm.DrugComplexMnn_RusName, '') as \"Drug_Name\",
					EPTD.EvnPrescrTreatDrug_DoseDay as \"DoseDay\",
					EPTD.EvnPrescrTreatDrug_FactCount as \"FactCntDay\",
					case when EDr.EvnDrug_id is null
						then 1
						else 2
					end as \"EvnPrescr_IsHasEvn\",
					Treat.EvnPrescrTreat_PrescrCount as \"PrescrCntDay\"
				from v_EvnPrescrTreatDrug EPTD
					left join v_EvnPrescrTreat Treat  on Treat.EvnPrescrTreat_id = EPTD.EvnPrescrTreat_id
					left join rls.v_Drug Drug  on Drug.Drug_id = EPTD.Drug_id
					left join rls.v_DrugComplexMnn dcm  on dcm.DrugComplexMnn_id = EPTD.DrugComplexMnn_id
					LEFT JOIN LATERAL (
						select EvnDrug_id
						from v_EvnDrug EvnDrug
							inner join Evn on Evn.Evn_id = EvnDrug.EvnDrug_id and Evn_deleted = 1
						where EvnDrug.EvnPrescr_id = EPTD.EvnPrescrTreat_id
                	    limit 1
					) EDr on true
				where EPTD.EvnPrescrTreat_id in ({$inlist['drug_prescr']})
			", array());

			$grouped_prescrs = array();

			// сгруппируем лекарственные назначения по ид назначения
			foreach ($drugs_prescr_data as $prescr) {
				$grouped_prescrs[$prescr['EvnPrescr_id']][] = $prescr;
			}

			// по сгруппированным назначениям добавим доп. данные в основной массив
			foreach ($grouped_prescrs as $key => $drug_list) {

				if (isset($processed_prescr[$key])) {

					$merge_data = array(
						'precr_count' => count($drug_list),
						'EvnPrescr_Name' => ''
					);

					// составное имя назначения если лекарств несколько
					$EvnPrescr_Name = array();

					foreach ($drug_list as $drug_data) {

						$name = $drug_data['Drug_Name'];

						if (!empty($drug_data['DoseDay'])) {
							$name .= ', дневная доза – ' . $drug_data['DoseDay'];
						}

						if (!empty($drug_data['PrescrCntDay']) && !empty($drug_data['FactCntDay'])) {
							$name .= ', '.$drug_data['FactCntDay'].'/'.$drug_data['PrescrCntDay'];
						}

						$name .= '.';
						$EvnPrescr_Name[] = $name;
					}

					if (!empty($EvnPrescr_Name)) {
						$merge_data['EvnPrescr_Name'] = implode("\x0A", $EvnPrescr_Name);
					}

					// мержим дополнительные данные
					$processed_prescr[$key] = array_merge($processed_prescr[$key], $merge_data);
				}
			}
		}

		if (!empty($inlist['usluga_prescr'])) {

			$inlist['usluga_prescr'] = implode(',', $inlist['usluga_prescr']);

			$usluga_prescr_data = $this->queryResult("
				select
					EP.EvnPrescr_id as \"EvnPrescr_id\"
					,UC.UslugaComplex_Name as \"UslugaComplex_Name\"
					,UC.UslugaComplex_id as \"UslugaComplex_id\"
					,case when EU.EvnUsluga_id is null then 1 else 2 end as \"EvnPrescr_IsHasEvn\"
				from v_EvnPrescr EP
				left join v_EvnPrescrOperUsluga EPOU  on EPOU.EvnPrescrOper_id = EP.EvnPrescr_id
				left join v_EvnPrescrFuncDiagUsluga EPFDU  on EPFDU.EvnPrescrFuncDiag_id = EP.EvnPrescr_id
				inner join v_UslugaComplex UC  on UC.UslugaComplex_id = coalesce(EPFDU.UslugaComplex_id,EPOU.UslugaComplex_id)
				LEFT JOIN LATERAL (
					select EvnUsluga_id from v_EvnUsluga
					where EP.EvnPrescr_IsExec = 2 and EvnPrescr_id = EP.EvnPrescr_id
                    limit 1
				) EU on true
				where EP.EvnPrescr_id in ({$inlist['usluga_prescr']})
			", array());

			$grouped_prescrs = array();

			// сгруппируем эти две группы назначения по ид назначения
			foreach ($usluga_prescr_data as $prescr) {
				$grouped_prescrs[$prescr['EvnPrescr_id']][] = $prescr;
			}

			// по сгруппированным назначениям добавим доп. данные в основной массив
			foreach ($grouped_prescrs as $key => $usluga_list) {
				if (isset($processed_prescr[$key])) {

					$merge_data = array(
						'precr_count' => count($usluga_list),
						'EvnPrescr_Name' => ''
					);

					// составное имя назначения если лекарств несколько
					$EvnPrescr_Name = array();

					foreach ($usluga_list as $usluga_data) {
						$EvnPrescr_Name[] = $usluga_data['UslugaComplex_Name'];
					}

					if (!empty($EvnPrescr_Name)) {
						$merge_data['EvnPrescr_Name'] = implode("\x0A", $EvnPrescr_Name);
					}

					// мержим дополнительные данные
					$processed_prescr[$key] = array_merge($processed_prescr[$key], $merge_data);
				}
			}
		}

		if (!empty($processed_prescr)) {
			$processed_prescr = array_values($processed_prescr);
		}

		// мержим в результирующий массив
		$response = array_merge($response, $processed_prescr);

		// добавим для вывода только нужные поля
		$allowed_fields = array(
			'EvnPrescr_id',
			'Person_id',
			'Person_FIO',
			'PrescriptionType_Code',
			'PrescriptionType_Name',
			'pmUser_insName',
			'EvnPrescr_Name',
			'EvnPrescr_IsExec',
			'Person_Birthday',
			'EvnSection_id',
			'Person_Age',
			'LpuSectionWard_Name',
			'EvnDirection_setDate',
			'EvnPrescr_planTime'
		);

		$allowed_fields = array_flip($allowed_fields);

		// пост-обработка для строки "Назначение"
		foreach ($response as &$prescr) {

			//Манипуляции и процедуры
			//Лабораторная диагностика
			//Консультационная услуга
			if (in_array($prescr['PrescriptionType_id'],array(6,11,13))) {
				$prescr['EvnPrescr_Name'] = $prescr['UslugaComplex_Name'];
			}

			if ($prescr['PrescriptionType_id'] = 1 && !empty($prescr['PrescriptionRegimeType_Name'])) {
				$prescr['EvnPrescr_Name'] = "Тип режима: ". $prescr['PrescriptionRegimeType_Name'];
			}

			if ($prescr['PrescriptionType_id'] = 2 && !empty($prescr['PrescriptionDietType_Name'])) {
				$prescr['EvnPrescr_Name'] = "Тип диеты: ". $prescr['PrescriptionDietType_Name'];
			}

			if ($prescr['PrescriptionType_id'] = 10 && !empty($prescr['ObservTimeType_Name'])) {
				$prescr['EvnPrescr_Name'] = "Время наблюдения: ". $prescr['ObservTimeType_Name'];
			}

			if ($prescr['EvnPrescr_IsCito'] == 2 ) {
				$prescr['EvnPrescr_Name'] .= "\x0A".'Cito!';
			}

			if (!empty($prescr['EvnPrescr_Descr'])) {
				$prescr['EvnPrescr_Name'] .= "\x0A".'Комментарий: '.$prescr['EvnPrescr_Descr'];
			}

			// отфильтруем поля
			foreach ($prescr as $fieldName => &$value) {
				if (!isset($allowed_fields[$fieldName])) {
					unset($prescr[$fieldName]);
				}
			}
		}

		return $response;
	}

	/**
	 * Получение списка назначений для журнала назначений
	 */
 function loadEvnPrescrJournalGrid($data) {
		$response = array();
		$response['data'] = array();
		$response['totalCount'] = 0;
		if(empty($data['LpuSection_id']) && !empty($data['MedService_id'])){
			$LpuSectionArray = $this->getServicedLpuSection($data['MedService_id']);
			if(isset($LpuSectionArray[0])){
				$LpuSectionList = '';
				foreach($LpuSectionArray as $LpuSectionKey => $LpuSectionData){
					$LpuSectionList .= ($LpuSectionKey == 0) ? $LpuSectionData['LpuSection_id'] : ','.$LpuSectionData['LpuSection_id'];
				}
				$LpuSectionFilter = 'and ES.LpuSection_id in ('.$LpuSectionList.')';
				$LpuSectionPFilter = 'and EPS.LpuSection_pid in ('.$LpuSectionList.')';
			}else{
				$LpuSectionFilter = '';
				$LpuSectionPFilter = '';
			}
		}else{
			$LpuSectionFilter = 'and ES.LpuSection_id = :LpuSection_id';
			$LpuSectionPFilter = 'and EPS.LpuSection_pid = :LpuSection_id';
		}
		if ( empty($data['EvnPrescr_setDate_Range'][0]) ) {
			return $response;
		}
		if ( empty($data['EvnPrescr_setDate_Range'][1]) ) {
			return $response;
		}
		$queryParams = array(
			'EvnPrescrDay_begDate' => $data['EvnPrescr_setDate_Range'][0],
			'EvnPrescrDay_endDate' => $data['EvnPrescr_setDate_Range'][1],
			'LpuSection_id' => $data['LpuSection_id'],
			'Lpu_id' => $data['Lpu_id']
		);
		$join = "";
		$filterPrescriptionType = 'EP.PrescriptionType_id != 4';
		$join .= " left join lateral(
				SELECT
					EvnPrescrDiet_id, PrescriptionStatusType_id,
					EvnPrescrDiet_Descr,
					EvnPrescrDiet_setDT,
					EvnPrescrDiet_didDT,
					EvnPrescrDiet_setTime,
					EvnPrescrDiet_IsExec,
					PrescriptionDietType_id
				from v_EvnPrescrDiet EPD
				WHERE EP.PrescriptionType_id = 2
				and EPD.Lpu_id = :Lpu_id
				and EPD.EvnPrescrDiet_pid = EP.EvnPrescr_id
				limit 500
			) Diet on true";

        $join .= " 
            left join lateral (
				SELECT
					EvnPrescrRegime_id, PrescriptionStatusType_id,
					EvnPrescrRegime_Descr,
					EvnPrescrRegime_setDT,
					EvnPrescrRegime_didDT,
					EvnPrescrRegime_setTime,
					EvnPrescrRegime_IsExec,
					PrescriptionRegimeType_id
				from v_EvnPrescrRegime WHERE EvnPrescrRegime_pid = EP.EvnPrescr_id
				limit 10000
			) AS Regime ON true";

		$filters = '';
		$epfilters = '';
		$dop_filters = '';
		$joinArrTime = '';

		if ( !empty($data['PrescriptionType_id']) ) {
			$filterPrescriptionType = 'EP.PrescriptionType_id = :PrescriptionType_id';
			$queryParams['PrescriptionType_id'] = $data['PrescriptionType_id'];
		}

		if ( !empty($data['EvnPrescr_IsExec']) ) {
			$filters .= " and coalesce(EP.EvnPrescr_IsExec,1) = :EvnPrescr_IsExec";
			$queryParams['EvnPrescr_IsExec'] = $data['EvnPrescr_IsExec'];
		}

		$isSearchByEncryp = false;
		$selectPersonData = "PS.Sex_id as Sex_id,
					PS.Person_Birthday as Person_Birthday,
					PS.Person_Surname as Person_Surname,
					PS.Person_Firname as Person_Firname,
					PS.Person_Secname as Person_Secname,";

		$EvnPrescrAllJoin = "";

		if (allowPersonEncrypHIV($data['session'])) {
			$isSearchByEncryp = isSearchByPersonEncrypHIV($data['Person_SurName']);
			$EvnPrescrAllJoin = "left join v_PersonEncrypHIV peh on peh.Person_id = PS.Person_id";
			$selectPersonData = "
				PS.Sex_id as Sex_id,
				case when peh.PersonEncrypHIV_Encryp is null then PS.Person_Birthday end as Person_Birthday,
				case when peh.PersonEncrypHIV_Encryp is null then PS.Person_SurName else peh.PersonEncrypHIV_Encryp end as Person_Surname,
				case when peh.PersonEncrypHIV_Encryp is null then PS.Person_FirName end as Person_Firname,
				case when peh.PersonEncrypHIV_Encryp is null then PS.Person_SecName end as Person_Secname,";
		}

		if ( !empty($data['Person_SurName']) ) {
			if (allowPersonEncrypHIV($data['session']) && $isSearchByEncryp) {
				$filters .= " and peh.PersonEncrypHIV_Encryp ilike :Person_SurName";
			} else {
				$filters .= " and PS.Person_SurName ilike :Person_SurName || '%'";
			}
			$queryParams['Person_SurName'] = rtrim($data['Person_SurName']);
		}
		if ( !empty($data['Person_FirName']) ) {
			$filters .= " and PS.Person_FirName ilike :Person_FirName || '%'";
			$queryParams['Person_FirName'] = rtrim($data['Person_FirName']);
		}
		if ( !empty($data['Person_SecName']) ) {
			$filters .= " and PS.Person_SecName ilike :Person_SecName || '%'";
			$queryParams['Person_SecName'] = rtrim($data['Person_SecName']);
		}
		if ( !empty($data['Person_BirthDay']) ) {
			$filters .= " and PS.Person_BirthDay = :Person_BirthDay";
			$queryParams['Person_BirthDay'] = $data['Person_BirthDay'];
		}
		if ( !empty($data['MedPersonal_id']) ) {
			$filters .= " and PMUI.pmUser_Medpersonal_id = :MedPersonal_id";
			$queryParams['MedPersonal_id'] = $data['MedPersonal_id'];
		}

		if ( !empty($data['PrescriptionIntroType_id']) ) {
			$filterPrescriptionType = 'EP.PrescriptionType_id = 5';
			$dop_filters .= " and ECT.PrescriptionIntroType_id = :PrescriptionIntroType_id";
			$queryParams['PrescriptionIntroType_id'] = $data['PrescriptionIntroType_id'];
		}

		if ( !empty($data['LpuSectionWard_id']) ) {
			$epfilters .= " and coalesce(EPS.LpuSectionWard_id, ES.LpuSectionWard_id) = :LpuSectionWard_id";
			$queryParams['LpuSectionWard_id'] = $data['LpuSectionWard_id'];
		}

		if ( !empty($data['LpuSectionProfile_id']) ) {
			$epfilters .= " and ES.LpuSectionProfile_id = :LpuSectionProfile_id";
			$queryParams['LpuSectionProfile_id'] = $data['LpuSectionProfile_id'];
		}

		if ( !empty($data['EvnQueueShow_id']) ) {
			// показывать очередь

		} else {
			// не показывать очередь
			$dop_filters .= " and EQ.EvnQueue_id is null";
		}

		if ( !empty($data['EvnPrescr_insDT'][0]) && !empty($data['EvnPrescr_insDT'][1])) {
			$epfilters .=
				" and cast(EP.EvnPrescr_insDT as date) >= cast(:EvnPrescr_ins_begDT as date)".
				" and cast(EP.EvnPrescr_insDT as date) <= cast(:EvnPrescr_ins_endDT as date)";
			$queryParams['EvnPrescr_ins_begDT'] = $data['EvnPrescr_insDT'][0];
			$queryParams['EvnPrescr_ins_endDT'] = $data['EvnPrescr_insDT'][1];
		}

		if (!empty($data['isClose'])) {
			if ($data['isClose'] == 2) {
				$epfilters .= " and ES.LeaveType_id is not null";
			} else if ($data['isClose'] == 1) {
				$epfilters .= " and ES.LeaveType_id is null";
			}
		}

		if ( $data['session']['region']['nick'] == 'ufa' )
			//  время назначения лек.обеспечения (для Уфы)
			$joinArrTime = '
					left join lateral(select  case  when EP.EvnPrescrDay_IsExec = 2 then null else EvnPrescr_setTime end  as EvnPrescr_setTime,
											case  when EP.EvnPrescrDay_IsExec = 2 then EvnPrescr_allTime else EvnPrescr_execTime end  as EvnPrescr_execTime,
											EvnPrescr_allTime from  r2.fn_getEvnCourseTreatTimeEntry_time (EP.EvnPrescr_id)) arr_time on true';
		else
			$joinArrTime = 'left join lateral(Select null as EvnPrescr_setTime, null as EvnPrescr_execTime, null as EvnPrescr_allTime) arr_time on true';

		$query = "
			--addit with
			with EvnPrescrFirst as(
				-- движения
				select
					EP.PrescriptionType_id,
					EP.Person_id,
					EP.pmUser_insID,
					EP.pmUser_updID,
					EP.EvnPrescr_id,
					EP.EvnPrescr_pid,
					EP.EvnPrescr_rid,
					coalesce(Regime.EvnPrescrRegime_didDT,Diet.EvnPrescrDiet_didDT,Obs.EvnPrescrObserv_didDT,EP.EvnPrescr_didDT) as EvnPrescr_didDT,
					EP.PersonEvn_id,
					EP.Server_id,
					EP.Lpu_id,
					EP.PrescrFailureType_id,
					PFT.PrescrFailureType_Name,
					EP.EvnPrescr_insDT,
					EP.EvnPrescr_IsCito,
					--EP.PrescriptionStatusType_id,
					EP.EvnPrescr_Descr,
					EP.EvnPrescr_setDT,
					EP.EvnPrescr_setTime,
					EP.EvnPrescr_IsExec,
					coalesce(EPS.Diag_pid,ES.Diag_id) as Diag_id,
					ES.EvnSection_id,
					coalesce(EPS.LpuSectionWard_id, ES.LpuSectionWard_id) as LpuSectionWard_id,
					coalesce(Regime.EvnPrescrRegime_IsExec,Diet.EvnPrescrDiet_IsExec,Obs.EvnPrescrObserv_IsExec,EP.EvnPrescr_IsExec,1) as EvnPrescrDay_IsExec,
					coalesce(Regime.PrescriptionStatusType_id,Diet.PrescriptionStatusType_id,Obs.PrescriptionStatusType_id,EP.PrescriptionStatusType_id) as PrescriptionStatusType_id,
					coalesce(Regime.EvnPrescrRegime_Descr,Diet.EvnPrescrDiet_Descr,Obs.EvnPrescrObserv_Descr,EP.EvnPrescr_Descr) as EvnPrescrDay_Descr,
					coalesce(Regime.EvnPrescrRegime_setDT,Diet.EvnPrescrDiet_setDT,Obs.EvnPrescrObserv_setDT,EP.EvnPrescr_setDT) as EvnPrescrDay_setDT,
					coalesce(Regime.EvnPrescrRegime_setTime,Diet.EvnPrescrDiet_setTime,Obs.EvnPrescrObserv_setTime,EP.EvnPrescr_setTime) as EvnPrescrDay_setTime,
					coalesce(Regime.EvnPrescrRegime_id,Diet.EvnPrescrDiet_id,Obs.EvnPrescrObserv_id,EP.EvnPrescr_id) as EvnPrescrDay_id,
					Regime.PrescriptionRegimeType_id,
					Diet.PrescriptionDietType_id,
					Obs.ObservTimeType_id,
					Obs.pmUser_updID as userUPD
				from v_EvnPrescr EP
					left join v_PrescrFailureType PFT on EP.PrescrFailureType_id = PFT.PrescrFailureType_id
					inner join v_EvnSection ES on ES.EvnSection_id = EP.EvnPrescr_pid
					inner join Evn e on e.Evn_id = ES.EvnSection_pid -- КВС для фильтрации по датам
					left join EvnPS EPS on 1=0 -- квс не нужна, но может использоваться в фильтрах
					left join lateral(
						SELECT -- по одному назначению вряд ли будет больше 500 наблюдений
							EvnPrescrObserv_id, PrescriptionStatusType_id,
							EvnPrescrObserv_Descr,
							EvnPrescrObserv_setDT,
							EvnPrescrObserv_didDT,
							EvnPrescrObserv_setTime,
							EvnPrescrObserv_IsExec,
							ObservTimeType_id,
							pmUser_updID
						from v_EvnPrescrObserv EPO
						WHERE
							EP.PrescriptionType_id = 10
							and EPO.Lpu_id = :Lpu_id
							and EPO.EvnPrescrObserv_pid = EP.EvnPrescr_id
							-- оказвыается может и больше 500 по одному назначению. Сделаем огрничения по дате, а то в выборку много ненужных записей попадает
							and cast(EPO.EvnPrescrObserv_setDT as date) >= cast(:EvnPrescrDay_begDate as date)
							and cast(EPO.EvnPrescrObserv_setDT as date) <= cast(:EvnPrescrDay_endDate as date)
						limit 500
					) AS Obs on true
					{$join}
				where {$filterPrescriptionType}
					and cast(E.Evn_setDT as date) <= cast(:EvnPrescrDay_endDate as date) and coalesce(cast(E.Evn_disDT as date), cast(:EvnPrescrDay_begDate as date)) >= cast(:EvnPrescrDay_begDate as date)
					{$LpuSectionFilter}
					and coalesce(ES.EvnSection_IsPriem, 1) = 1
					and EP.Lpu_id = :Lpu_id
					{$epfilters}

				union all

				-- КВС
				select
					EP.PrescriptionType_id,
					EP.Person_id,
					EP.pmUser_insID,
					EP.pmUser_updID,
					EP.EvnPrescr_id,
					EP.EvnPrescr_pid,
					EP.EvnPrescr_rid,
					coalesce(Regime.EvnPrescrRegime_didDT,Diet.EvnPrescrDiet_didDT,Obs.EvnPrescrObserv_didDT,EP.EvnPrescr_didDT) as EvnPrescr_didDT,
					EP.PersonEvn_id,
					EP.Server_id,
					EP.Lpu_id,
					EP.PrescrFailureType_id,
					PFT.PrescrFailureType_Name,
					EP.EvnPrescr_insDT,
					EP.EvnPrescr_IsCito,
					--EP.PrescriptionStatusType_id,
					EP.EvnPrescr_Descr,
					EP.EvnPrescr_setDT,
					EP.EvnPrescr_setTime,
					EP.EvnPrescr_IsExec,
					coalesce(EPS.Diag_pid,ES.Diag_id) as Diag_id,
					ES.Evn_id as EvnSection_id,
					coalesce(EPS.LpuSectionWard_id, ES.LpuSectionWard_id) as LpuSectionWard_id,
					coalesce(Regime.EvnPrescrRegime_IsExec,Diet.EvnPrescrDiet_IsExec,Obs.EvnPrescrObserv_IsExec,EP.EvnPrescr_IsExec,1) as EvnPrescrDay_IsExec,
					coalesce(Regime.PrescriptionStatusType_id,Diet.PrescriptionStatusType_id,Obs.PrescriptionStatusType_id,EP.PrescriptionStatusType_id) as PrescriptionStatusType_id,
					coalesce(Regime.EvnPrescrRegime_Descr,Diet.EvnPrescrDiet_Descr,Obs.EvnPrescrObserv_Descr,EP.EvnPrescr_Descr) as EvnPrescrDay_Descr,
					coalesce(Regime.EvnPrescrRegime_setDT,Diet.EvnPrescrDiet_setDT,Obs.EvnPrescrObserv_setDT,EP.EvnPrescr_setDT) as EvnPrescrDay_setDT,
					coalesce(Regime.EvnPrescrRegime_setTime,Diet.EvnPrescrDiet_setTime,Obs.EvnPrescrObserv_setTime,EP.EvnPrescr_setTime) as EvnPrescrDay_setTime,
					coalesce(Regime.EvnPrescrRegime_id,Diet.EvnPrescrDiet_id,Obs.EvnPrescrObserv_id,EP.EvnPrescr_id) as EvnPrescrDay_id,
					Regime.PrescriptionRegimeType_id,
					Diet.PrescriptionDietType_id,
					Obs.ObservTimeType_id,
					Obs.pmUser_updID as userUPD
				from v_EvnPrescr EP
					left join v_PrescrFailureType PFT on EP.PrescrFailureType_id = PFT.PrescrFailureType_id
					inner join EvnPS EPS on EPS.Evn_id = EP.EvnPrescr_pid
					inner join Evn e on e.Evn_id = EPS.Evn_id -- КВС для фильтрации по датам
					left join EvnSection ES on 1=0 -- движение не нужно, но может использоваться в фильтрах
					left join lateral(
						SELECT -- по одному назначению вряд ли будет больше 500 наблюдений
							EvnPrescrObserv_id, PrescriptionStatusType_id,
							EvnPrescrObserv_Descr,
							EvnPrescrObserv_setDT,
							EvnPrescrObserv_didDT,
							EvnPrescrObserv_setTime,
							EvnPrescrObserv_IsExec,
							ObservTimeType_id,
							pmUser_updID
						from v_EvnPrescrObserv EPO
						WHERE EP.PrescriptionType_id = 10
							and EPO.Lpu_id = :Lpu_id
							and EPO.EvnPrescrObserv_pid = EP.EvnPrescr_id
							-- оказвыается может и больше 500 по одному назначению. Сделаем огрничения по дате.
							and cast(EPO.EvnPrescrObserv_setDT as date) >= cast(:EvnPrescrDay_begDate as date)
							and cast(EPO.EvnPrescrObserv_setDT as date) <= cast(:EvnPrescrDay_endDate as date)
						limit 500
					) AS Obs on true
					{$join}
				where {$filterPrescriptionType}
					and cast(E.Evn_setDT as date) <= cast(:EvnPrescrDay_endDate as date) and coalesce(cast(E.Evn_disDT as date), cast(:EvnPrescrDay_begDate as date)) >= cast(:EvnPrescrDay_begDate as date)
					{$LpuSectionPFilter}
					and EP.Lpu_id = :Lpu_id
					{$epfilters}

				union all

				-- назначения в приёмном (если они сохраняются на КВС)
				select
					EP.PrescriptionType_id,
					EP.Person_id,
					EP.pmUser_insID,
					EP.pmUser_updID,
					EP.EvnPrescr_id,
					EP.EvnPrescr_pid,
					EP.EvnPrescr_rid,
					coalesce(Regime.EvnPrescrRegime_didDT,Diet.EvnPrescrDiet_didDT,Obs.EvnPrescrObserv_didDT,EP.EvnPrescr_didDT) as EvnPrescr_didDT,
					EP.PersonEvn_id,
					EP.Server_id,
					EP.Lpu_id,
					EP.PrescrFailureType_id,
					PFT.PrescrFailureType_Name,
					EP.EvnPrescr_insDT,
					EP.EvnPrescr_IsCito,
					--EP.PrescriptionStatusType_id,
					EP.EvnPrescr_Descr,
					EP.EvnPrescr_setDT,
					EP.EvnPrescr_setTime,
					EP.EvnPrescr_IsExec,
					ES.Diag_id,
					ES.EvnSection_id,
					ES.LpuSectionWard_id,
					coalesce(Regime.EvnPrescrRegime_IsExec,Diet.EvnPrescrDiet_IsExec,Obs.EvnPrescrObserv_IsExec,EP.EvnPrescr_IsExec,1) as EvnPrescrDay_IsExec,
					coalesce(Regime.PrescriptionStatusType_id,Diet.PrescriptionStatusType_id,Obs.PrescriptionStatusType_id,EP.PrescriptionStatusType_id) as PrescriptionStatusType_id,
					coalesce(Regime.EvnPrescrRegime_Descr,Diet.EvnPrescrDiet_Descr,Obs.EvnPrescrObserv_Descr,EP.EvnPrescr_Descr) as EvnPrescrDay_Descr,
					coalesce(Regime.EvnPrescrRegime_setDT,Diet.EvnPrescrDiet_setDT,Obs.EvnPrescrObserv_setDT,EP.EvnPrescr_setDT) as EvnPrescrDay_setDT,
					coalesce(Regime.EvnPrescrRegime_setTime,Diet.EvnPrescrDiet_setTime,Obs.EvnPrescrObserv_setTime,EP.EvnPrescr_setTime) as EvnPrescrDay_setTime,
					coalesce(Regime.EvnPrescrRegime_id,Diet.EvnPrescrDiet_id,Obs.EvnPrescrObserv_id,EP.EvnPrescr_id) as EvnPrescrDay_id,
					Regime.PrescriptionRegimeType_id,
					Diet.PrescriptionDietType_id,
					Obs.ObservTimeType_id,
					Obs.pmUser_updID as userUPD
				from v_EvnPrescr EP
					left join v_PrescrFailureType PFT on EP.PrescrFailureType_id = PFT.PrescrFailureType_id
					inner join v_EvnSection ES on ES.EvnSection_pid = EP.EvnPrescr_pid -- внутри КВС есть движение текущего отделения
					inner join Evn e on e.Evn_id = ES.EvnSection_pid -- КВС для фильтрации по датам
					left join v_EvnPS eps on 1=0 -- квс не нужна, но может использоваться в фильтрах
					left join lateral(
						SELECT -- по одному назначению вряд ли будет больше 500 наблюдений
							EvnPrescrObserv_id, PrescriptionStatusType_id,
							EvnPrescrObserv_Descr,
							EvnPrescrObserv_setDT,
							EvnPrescrObserv_didDT,
							EvnPrescrObserv_setTime,
							EvnPrescrObserv_IsExec,
							ObservTimeType_id,
							pmUser_updID
						from v_EvnPrescrObserv EPO
						WHERE EP.PrescriptionType_id = 10
							and EPO.Lpu_id = :Lpu_id
							and EPO.EvnPrescrObserv_pid = EP.EvnPrescr_id
							-- оказвыается может и больше 500 по одному назначению. Сделаем огрничения по дате.
							and cast(EPO.EvnPrescrObserv_setDT as date) >= cast(:EvnPrescrDay_begDate as date)
							and cast(EPO.EvnPrescrObserv_setDT as date) <= cast(:EvnPrescrDay_endDate as date)
						limit 500
					) AS Obs on true
					{$join}
				where {$filterPrescriptionType}
					and cast(E.Evn_setDT as date) <= cast(:EvnPrescrDay_endDate as date) and coalesce(cast(E.Evn_disDT as date), cast(:EvnPrescrDay_begDate as date)) >= cast(:EvnPrescrDay_begDate as date)
					{$LpuSectionFilter}
					and EP.Lpu_id = :Lpu_id
					{$epfilters}

				union all

				-- назначения в приёмном движении (если они сохраняются на приёмное движение)
				select
					EP.PrescriptionType_id,
					EP.Person_id,
					EP.pmUser_insID,
					EP.pmUser_updID,
					EP.EvnPrescr_id,
					EP.EvnPrescr_pid,
					EP.EvnPrescr_rid,
                    coalesce(Regime.EvnPrescrRegime_didDT,Diet.EvnPrescrDiet_didDT,Obs.EvnPrescrObserv_didDT,EP.EvnPrescr_didDT) as EvnPrescr_didDT,
					EP.PersonEvn_id,
					EP.Server_id,
					EP.Lpu_id,
					EP.PrescrFailureType_id,
					PFT.PrescrFailureType_Name,
					EP.EvnPrescr_insDT,
					EP.EvnPrescr_IsCito,
					--EP.PrescriptionStatusType_id,
					EP.EvnPrescr_Descr,
					EP.EvnPrescr_setDT,
					EP.EvnPrescr_setTime,
					EP.EvnPrescr_IsExec,
					ES.Diag_id,
					ES.EvnSection_id,
					ES.LpuSectionWard_id,
					coalesce(Regime.EvnPrescrRegime_IsExec,Diet.EvnPrescrDiet_IsExec,Obs.EvnPrescrObserv_IsExec,EP.EvnPrescr_IsExec,1) as EvnPrescrDay_IsExec,
					coalesce(Regime.PrescriptionStatusType_id,Diet.PrescriptionStatusType_id,Obs.PrescriptionStatusType_id,EP.PrescriptionStatusType_id) as PrescriptionStatusType_id,
					coalesce(Regime.EvnPrescrRegime_Descr,Diet.EvnPrescrDiet_Descr,Obs.EvnPrescrObserv_Descr,EP.EvnPrescr_Descr) as EvnPrescrDay_Descr,
					coalesce(Regime.EvnPrescrRegime_setDT,Diet.EvnPrescrDiet_setDT,Obs.EvnPrescrObserv_setDT,EP.EvnPrescr_setDT) as EvnPrescrDay_setDT,
					coalesce(Regime.EvnPrescrRegime_setTime,Diet.EvnPrescrDiet_setTime,Obs.EvnPrescrObserv_setTime,EP.EvnPrescr_setTime) as EvnPrescrDay_setTime,
					coalesce(Regime.EvnPrescrRegime_id,Diet.EvnPrescrDiet_id,Obs.EvnPrescrObserv_id,EP.EvnPrescr_id) as EvnPrescrDay_id,
					Regime.PrescriptionRegimeType_id,
					Diet.PrescriptionDietType_id,
					Obs.ObservTimeType_id,
					Obs.pmUser_updID as userUPD
				from v_EvnPrescr EP
					left join v_PrescrFailureType PFT on EP.PrescrFailureType_id = PFT.PrescrFailureType_id
					inner join v_EvnSection ES2 on ES2.EvnSection_id = EP.EvnPrescr_pid
					inner join v_EvnSection ES on ES.EvnSection_pid = ES2.EvnSection_pid -- внутри КВС есть движение текущего отделения
					inner join Evn e on e.Evn_id = ES.EvnSection_pid -- КВС для фильтрации по датам
					left join v_EvnPS eps on 1=0 -- квс не нужна, но может использоваться в фильтрах
					left join lateral(
						SELECT -- по одному назначению вряд ли будет больше 500 наблюдений
							EvnPrescrObserv_id, PrescriptionStatusType_id,
							EvnPrescrObserv_Descr,
							EvnPrescrObserv_setDT,
							EvnPrescrObserv_didDT,
							EvnPrescrObserv_setTime,
							EvnPrescrObserv_IsExec,
							ObservTimeType_id,
							pmUser_updID
						from v_EvnPrescrObserv EPO
						WHERE EP.PrescriptionType_id = 10
							and EPO.Lpu_id = :Lpu_id
							and EPO.EvnPrescrObserv_pid = EP.EvnPrescr_id
							-- оказвыается может и больше 500 по одному назначению. Сделаем огрничения по дате.
							and cast(EPO.EvnPrescrObserv_setDT as date) >= cast(:EvnPrescrDay_begDate as date)
							and cast(EPO.EvnPrescrObserv_setDT as date) <= cast(:EvnPrescrDay_endDate as date)
						limit 500
					) AS Obs on true
					{$join}
				where {$filterPrescriptionType}
					and cast(E.Evn_setDT as date) <= cast(:EvnPrescrDay_endDate as date) and coalesce(cast(E.Evn_disDT as date), cast(:EvnPrescrDay_begDate as date)) >= cast(:EvnPrescrDay_begDate as date)
					{$LpuSectionFilter}
					and ES2.EvnSection_IsPriem = 2
					and EP.Lpu_id = :Lpu_id
					{$epfilters}
			), EvnPrescrAll as (
				select
					EP.PrescriptionType_id,
					EP.EvnPrescr_id,
					EP.EvnPrescr_pid,
					EP.EvnPrescr_rid,
					EP.EvnPrescr_didDT,
					EP.Person_id,
					EP.PersonEvn_id,
					EP.Server_id,
					{$selectPersonData}
					EP.Lpu_id,
					EP.PrescrFailureType_id,
					EP.PrescrFailureType_Name,
					EP.Diag_id,
					EP.EvnSection_id,
					EP.LpuSectionWard_id,
					EP.EvnPrescr_insDT,
					coalesce(PMUI.pmUser_Name, '') as pmUser_insName,
					coalesce(PMUU.pmUser_Name, '') as pmUser_execName,
					coalesce(EP.EvnPrescr_IsCito, 1) as EvnPrescr_IsCito,
					EP.EvnPrescrDay_IsExec,
					EP.PrescriptionStatusType_id,
					EP.EvnPrescrDay_Descr,
					EP.EvnPrescrDay_setDT,
					EP.EvnPrescrDay_setTime,
					EP.EvnPrescrDay_id,
					EP.PrescriptionRegimeType_id,
					EP.PrescriptionDietType_id,
					EP.ObservTimeType_id,
					EP.userUPD
				from EvnPrescrFirst EP
				inner join v_PersonState PS on PS.Person_id = EP.Person_id
				left join v_pmUser PMUI on PMUI.pmUser_id = EP.pmUser_insID
				left join v_pmUser PMUU on PMUU.pmUser_id = EP.pmUser_updID
				{$EvnPrescrAllJoin}
				where 1 = 1
				{$filters}
			)
			-- end addit with

			select
				-- select
				EP.EvnPrescrDay_id as \"EvnPrescrDay_id\",
				EP.EvnPrescr_id as \"EvnPrescr_id\",
				EP.EvnPrescr_pid as \"EvnPrescr_pid\",
				EP.EvnPrescr_rid as \"EvnPrescr_rid\",
				EP.Diag_id as \"Diag_id\",
				EP.Person_id as \"Person_id\",
				EP.PersonEvn_id as \"PersonEvn_id\",
				EP.Server_id as \"Server_id\",
				EP.PrescrFailureType_id as \"PrescrFailureType_id\",
				EP.PrescrFailureType_Name as \"PrescrFailureType_Name\",
				EP.PrescriptionType_id as \"PrescriptionType_id\",
				EP.EvnPrescr_IsCito as \"EvnPrescr_IsCito\",
				EP.EvnPrescrDay_IsExec as \"EvnPrescr_IsExec\",
				to_char(EP.EvnPrescrDay_setDT, 'dd.mm.yyyy') as \"EvnPrescr_setDate\",
				case
					when TTMS.TimetableMedService_id is not null then
						to_char(TTMS.TimetableMedService_begTime, 'dd.mm.yyyy HH24:MI')
					when TTR.TimetableResource_id is not null then
						to_char(TTR.TimetableResource_begTime, 'dd.mm.yyyy HH24:MI')
					when EQ.EvnQueue_id is not null then
						null
					when arr_time.EvnPrescr_setTime is not null then
							to_char(EP.EvnPrescrDay_setDT, 'dd.mm.yyyy') || '  <br />(' || arr_time.EvnPrescr_setTime || ')'
					when arr_time.EvnPrescr_allTime is not null and EP.EvnPrescrDay_IsExec = 2 then
						to_char(EP.EvnPrescrDay_setDT, 'dd.mm.yyyy')
					else
						to_char(EP.EvnPrescrDay_setDT, 'dd.mm.yyyy HH24:MI')
				end	as \"EvnPrescr_planDate\",
				case when EP.EvnPrescrDay_IsExec = 2 then
					to_char(coalesce(EU.EvnUsluga_disDT, EP.EvnPrescr_didDT), 'dd.mm.yyyy HH24:MI')
				else
					null
				end as \"EvnPrescr_execDate\",
				COALESCE(arr_time.EvnPrescr_setTime,
					to_char(TTMS.TimetableMedService_begTime, 'HH24:MI'), to_char(EP.EvnPrescrDay_setTime, 'HH24:MI')) as \"EvnPrescr_setTime\",
				arr_time.EvnPrescr_execTime as \"EvnPrescr_execTime\",
				RTRIM(LTRIM(coalesce(EP.Person_Surname, ''))) || ' ' || RTRIM(LTRIM(coalesce(EP.Person_Firname, ''))) || ' ' || RTRIM(LTRIM(coalesce(EP.Person_Secname, ''))) as \"Person_FIO\",
				to_char(EP.Person_Birthday, 'dd.mm.yyyy') as \"Person_Birthday\",
				coalesce(PT.PrescriptionType_Code, 0) as \"PrescriptionType_Code\",
				coalesce(PT.PrescriptionType_Name, '') as \"PrescriptionType_Name\",
				to_char(EP.EvnPrescr_insDT, 'dd.mm.yyyy HH24:MI') as \"EvnPrescr_insDT\",
				YN.YesNo_Name as \"IsExec_Name\",
				EP.EvnPrescrDay_Descr as \"EvnPrescr_Descr\",
				'' as \"LpuSectionProfile_Name\",
				EP.pmUser_insName as \"pmUser_insName\",
				LSW.LpuSectionWard_id as \"LpuSectionWard_id\",
				EP.Sex_id as \"Sex_id\",
				EP.EvnSection_id as \"EvnSection_id\",
				coalesce(LSW.LpuSectionWard_Name, 'Без палаты') as \"LpuSectionWard_Name\",
				ED.EvnDirection_id as \"EvnDirection_id\",
				--1
				coalesce(PRT.PrescriptionRegimeType_Name, '')  as \"PrescriptionRegimeType_Name\",
				--2
				coalesce(PDT.PrescriptionDietType_Name, '') as \"PrescriptionDietType_Name\",
				--10
				EP.ObservTimeType_id as \"ObservTimeType_id\",
				coalesce(OTT.ObservTimeType_Name, '') as \"ObservTimeType_Name\",
				0 as \"PrescriptionTreatType_Code\",
				'' as \"PrescriptionTreatType_Name\",
				coalesce(ECT.EvnCourseTreat_MaxCountDay, 0) as \"EvnPrescrTreat_CountInDay\",
				coalesce(PIT.PrescriptionIntroType_Name, '') as \"PrescriptionIntroType_Name\",
				coalesce(PFT.PerformanceType_Name, '') as \"PerformanceType_Name\",
				Treat.EvnPrescrTreat_PrescrCount as \"PrescrCntDay\",
				null as \"EvnPrescrTreatDrug_id\",
				null as \"EvnPrescrTreatDrug_KolvoEd\",
				null as \"DrugForm_Name\",
				null as \"EvnPrescrTreatDrug_Kolvo\",
				null as \"Okei_NationSymbol\",
				null as \"Drug_Name\",
				null as \"DrugTorg_Name\",
				0 as \"cntDrug\",
				null as \"DoseDay\",
				null as \"FactCntDay\",
				coalesce(ECT.EvnCourseTreat_MaxCountDay::varchar,ECP.EvnCourseProc_MaxCountDay::varchar, '') as \"CountInDay\",
				coalesce(ECT.EvnCourseTreat_Duration::varchar,ECP.EvnCourseProc_Duration::varchar, '') as \"CourseDuration\",
				coalesce(ECT.EvnCourseTreat_ContReception::varchar,ECP.EvnCourseProc_ContReception::varchar, '') as \"ContReception\",
				coalesce(ECT.EvnCourseTreat_Interval::varchar,ECP.EvnCourseProc_Interval::varchar, '') as \"Interval\",
				coalesce(DTP.DurationType_Nick, '') as \"DurationTypeP_Nick\",
				coalesce(DTN.DurationType_Nick, '') as \"DurationTypeN_Nick\",
				coalesce(DTI.DurationType_Nick, '') as \"DurationTypeI_Nick\",
				case when EU.EvnUsluga_id is null then 1 else 2 end as \"EvnPrescr_IsHasEvn\",
				case
					when EP.PrescriptionType_id in (6,11,13) then 1
					else null
				end as \"TableUsluga_id\",
				UC.UslugaComplex_Name as \"UslugaComplex_Name\",
				UC.UslugaComplex_id as \"UslugaComplex_id\",
				case
					when EP.PrescriptionType_id in (6,11,13) then 1
					else null
				end as \"cntUsluga\",
				case when EP.EvnPrescrDay_IsExec = 2 then
					case
						when EP.PrescriptionType_id IN (6,7,11,12,13,14) then MP.Person_SurName || ' ' || coalesce(MP.Person_FirName, '') || ' ' || coalesce(MP.Person_SecName, '')
						when EP.PrescriptionType_id = 10 then ObsPMU.PMUser_Name
					  	else EP.pmUser_execName
				  	end
				else ''
				end as \"pmUser_execName\",
				CASE WHEN PQ.PersonQuarantine_id is not null THEN 'true' ELSE 'false' END as \"PersonQuarantine_IsOn\",
				to_char(PQ.PersonQuarantine_begDT, 'DD.MM.YYYY') as \"PersonQuarantine_begDT\"
				-- end select
			from
				-- from
				EvnPrescrAll EP
				left join v_LpuSectionWard LSW on LSW.LpuSectionWard_id = EP.LpuSectionWard_id
				left join lateral(
					Select ED.EvnDirection_id, ED.EvnStatus_id, ED.MedService_id
					from v_EvnPrescrDirection epd
					inner join v_EvnDirection_all ED on epd.EvnDirection_id = ED.EvnDirection_id
						and ED.EvnDirection_failDT is null
						and coalesce(ED.EvnStatus_id, 16) not in (12,13)
					where epd.EvnPrescr_id = EP.EvnPrescr_id
					order by epd.EvnPrescrDirection_insDT desc
					limit 1
				) ED on true
				left join lateral(
					Select TimetableMedService_id, TimetableMedService_begTime from v_TimetableMedService_lite TTMS where TTMS.EvnDirection_id = ED.EvnDirection_id limit 1
				) TTMS on true
				left join lateral(
					Select TimetableResource_id, TimetableResource_begTime from v_TimetableResource_lite TTR where TTR.EvnDirection_id = ED.EvnDirection_id limit 1
				) TTR on true
				left join lateral(
					Select EQ.EvnQueue_id, EQ.EvnQueue_setDate from v_EvnQueue EQ where EQ.EvnDirection_id = ED.EvnDirection_id and EQ.EvnQueue_failDT is null limit 1
				) EQ on true
				inner join PrescriptionType PT on PT.PrescriptionType_id = EP.PrescriptionType_id
				left join YesNo YN on YN.YesNo_id = EP.EvnPrescrDay_IsExec
				--1
				left join PrescriptionRegimeType PRT on EP.PrescriptionType_id = 1 and PRT.PrescriptionRegimeType_id = EP.PrescriptionRegimeType_id
				--2
				left join PrescriptionDietType PDT on EP.PrescriptionType_id = 2 and PDT.PrescriptionDietType_id = EP.PrescriptionDietType_id
				--5
				left join v_EvnPrescrTreat Treat on Treat.EvnPrescrTreat_id = EP.EvnPrescr_id
				left join v_EvnCourseTreat ECT on Treat.EvnCourse_id = ECT.EvnCourseTreat_id
				left join PrescriptionIntroType PIT on ECT.PrescriptionIntroType_id = PIT.PrescriptionIntroType_id
				left join PerformanceType PFT on  ECT.PerformanceType_id = PFT.PerformanceType_id
				--6
				left join v_EvnPrescrProc EPPR on EP.PrescriptionType_id = 6 and EPPR.EvnPrescrProc_id = EP.EvnPrescr_id
				left join v_EvnCourseProc ECP on EP.PrescriptionType_id = 6 and EPPR.EvnCourse_id = ECP.EvnCourseProc_id
				--5,6
				left join DurationType DTP on coalesce(ECP.DurationType_id,ECT.DurationType_id) = DTP.DurationType_id
				left join DurationType DTN on coalesce(ECP.DurationType_recid,ECT.DurationType_recid) = DTN.DurationType_id
				left join DurationType DTI on coalesce(ECP.DurationType_intid,ECT.DurationType_intid) = DTI.DurationType_id
				--10
				left join ObservTimeType OTT on EP.PrescriptionType_id = 10 and OTT.ObservTimeType_id = EP.ObservTimeType_id
				left join v_pmUserCache ObsPMU on ObsPMU.PMUser_id = EP.userUPD
				--11
				left join v_EvnUsluga_all EUAll on ED.EvnDirection_id = EuAll.EvnDirection_id
				left join v_MedPersonal MP on MP.MedPersonal_id = EUAll.MedPersonal_id AND MP.Lpu_id = EUAll.Lpu_id
				--left join v_EvnPrescrLabDiag EPLD on EP.PrescriptionType_id = 11 and EPLD.EvnPrescrLabDiag_id = EP.EvnPrescr_id
				left join lateral(
					select * from v_EvnPrescrLabDiag EPLD where EP.PrescriptionType_id = 11 and EvnPrescrLabDiag_id = EP.EvnPrescr_id limit 100
				) EPLD on true
				--13,6
				--left join v_EvnPrescrConsUsluga EPCU on EP.PrescriptionType_id = 13 and EPCU.EvnPrescrConsUsluga_id = EP.EvnPrescr_id
				left join lateral(
					select * from v_EvnPrescrConsUsluga where EP.PrescriptionType_id = 13 and EvnPrescrConsUsluga_id = EP.EvnPrescr_id limit 100
				) EPCU on true
				left join v_UslugaComplex UC on UC.UslugaComplex_id = coalesce(EPPR.UslugaComplex_id,EPLD.UslugaComplex_id,EPCU.UslugaComplex_id)
				left join lateral(
					select EvnUsluga_id, coalesce(EvnUsluga_disDT, EvnUsluga_setDT) as EvnUsluga_disDT from v_EvnUsluga
					where EP.EvnPrescrDay_IsExec = 2 and UC.UslugaComplex_id is not null and EvnPrescr_id = EP.EvnPrescr_id
					limit 1
				) EU on true
				left join lateral (
					select PQ.*
					from v_PersonQuarantine PQ
					where PQ.Person_id = EP.Person_id 
					and PQ.PersonQuarantine_endDT is null
					limit 1
				) PQ on true
				{$joinArrTime}
				-- end from
			where
				-- where
				(1=1)
				{$dop_filters}
				and cast(coalesce(TTMS.TimetableMedService_begTime,TTR.TimetableResource_begTime,EQ.EvnQueue_setDate,EP.EvnPrescrDay_setDT) as date) >= cast(:EvnPrescrDay_begDate as date)
				and cast(coalesce(TTMS.TimetableMedService_begTime,TTR.TimetableResource_begTime,EQ.EvnQueue_setDate,EP.EvnPrescrDay_setDT) as date) <= cast(:EvnPrescrDay_endDate as date)
				-- end where
			order by
				-- order by
				EP.EvnPrescrDay_setDT,
				EP.PrescriptionType_id,
				EP.EvnPrescr_id
				-- end order by";

		$response = $this->getPagingResponse($query, $queryParams, $data['start'], $data['limit'], true);
		$res_arr = $response['data'];
		$response['data'] = array();

		$tmp_arr = array();
		foreach ( $res_arr as $i => &$evnPrescr ) {
			$evnPrescr['PrescriptionType_Name'] = str_replace(' ', '<br />', $evnPrescr['PrescriptionType_Name']);
			switch(true)
			{
				case (in_array($evnPrescr['PrescriptionType_id'],array(6,11,13))):
					// получена вся информация для назначений
					//Манипуляции и процедуры
					//Лабораторная диагностика
					//Консультационная услуга
					$evnPrescr['EvnPrescr_Name'] = '<div>';
					$evnPrescr['EvnPrescr_Name'] .= '<span style="font-weight: bold;">'.$evnPrescr['UslugaComplex_Name'].'</span>';
					if ( $evnPrescr['EvnPrescr_IsCito'] == 2 )
						$evnPrescr['EvnPrescr_Name'] .=  '&nbsp;<span style="color: red">Cito!</span>';
					if ( !empty($evnPrescr['EvnPrescr_Descr']) )
					{
						$evnPrescr['EvnPrescr_Name'] .= '<div><span style="font-weight: bold;">Комментарий:</span> ' .  htmlspecialchars($evnPrescr['EvnPrescr_Descr']) . '</div>';
					}
					$evnPrescr['EvnPrescr_Name'] .= '</div>';
					$evnPrescr['UslugaId_List'] = $evnPrescr['UslugaComplex_id'];
					$response['data'][] = $evnPrescr;
					break;
				case (in_array($evnPrescr['PrescriptionType_id'],array(1,2,10))):
					// получена вся информация для назначений наблюдения, режима и диеты
					$evnPrescr['EvnPrescr_Name'] = '<div>';
					if ( !empty($evnPrescr['PrescriptionRegimeType_Name']) )
					{
						$evnPrescr['EvnPrescr_Name'] .= '<div><span style="font-weight: bold;">Тип режима:</span> ' .  htmlspecialchars($evnPrescr['PrescriptionRegimeType_Name']) . '</div>';
					}
					if ( !empty($evnPrescr['PrescriptionDietType_Name']) )
					{
						$evnPrescr['EvnPrescr_Name'] .= '<div><span style="font-weight: bold;">Тип диеты:</span> ' .  htmlspecialchars($evnPrescr['PrescriptionDietType_Name']) . '</div>';
					}
					if ( !empty($evnPrescr['ObservTimeType_Name']) )
					{
						$evnPrescr['EvnPrescr_Name'] .= '<div><span style="font-weight: bold;">Время наблюдения:</span> ' .  htmlspecialchars($evnPrescr['ObservTimeType_Name']) . '</div>';
					}
					if ( !empty($evnPrescr['EvnPrescr_Descr']) )
					{
						$evnPrescr['EvnPrescr_Name'] .= '<div><span style="font-weight: bold;">Комментарий:</span> ' .  htmlspecialchars($evnPrescr['EvnPrescr_Descr']) . '</div>';
					}
					$evnPrescr['EvnPrescr_Name'] .= '</div>';
					$evnPrescr['UslugaId_List'] = '';
					$response['data'][] = $evnPrescr;
					break;
				default:
					//нужно будет дополучить информацию о курсах, медикаментах, услугах
					if (empty($tmp_arr[$evnPrescr['PrescriptionType_id']])) {
						$tmp_arr[$evnPrescr['PrescriptionType_id']] = array();
					}
					$tmp_arr[$evnPrescr['PrescriptionType_id']][$i] = $evnPrescr['EvnPrescr_id'];
					break;
			}
		}

		foreach ( $tmp_arr as $prescriptionType => $EvnPrescrIdList ) {
			switch (true) {
				case ( 5 == $prescriptionType ):
					//нужно получить данные медикаментов для назначений из списка
					$query = "
						select
							EPTD.EvnPrescrTreat_id as \"EvnPrescr_id\",
							coalesce(Drug.Drug_Name, dcm.DrugComplexMnn_RusName, '') as \"Drug_Name\",
							EPTD.EvnPrescrTreatDrug_DoseDay as \"DoseDay\",
							EPTD.EvnPrescrTreatDrug_FactCount as \"FactCntDay\",
							case when EDr.EvnDrug_id is null then 1 else 2 end as \"EvnPrescr_IsHasEvn\"
						from v_EvnPrescrTreatDrug EPTD
							--left join rls.MASSUNITS ep_mu on EPTD.MASSUNITS_ID = ep_mu.MASSUNITS_ID
							--left join rls.CUBICUNITS ep_cu on EPTD.CUBICUNITS_id = ep_cu.CUBICUNITS_id
							left join rls.v_Drug Drug on Drug.Drug_id = EPTD.Drug_id
							left join rls.v_DrugComplexMnn dcm on dcm.DrugComplexMnn_id = EPTD.DrugComplexMnn_id
							--left join rls.CLSDRUGFORMS df on dcm.CLSDRUGFORMS_ID = df.CLSDRUGFORMS_ID
							left join lateral(
								select EvnDrug_id from v_EvnDrug EvnDrug
								inner join Evn on Evn.Evn_id = EvnDrug.EvnDrug_id
								where EvnDrug.EvnPrescr_id = EPTD.EvnPrescrTreat_id
								limit 1
							) EDr on true
						where EPTD.EvnPrescrTreat_id in (".implode(',',$EvnPrescrIdList).")
					";
					// echo getDebugSQL($query, array()); exit();
					$result = $this->db->query($query, array());
					if ( !is_object($result) ) {
						return $response;
					}
					$dopData = $result->result('array');
					foreach ($EvnPrescrIdList as $i => $EvnPrescr_id) {
						$ep = $res_arr[$i];
						$tmp2_arr = array();
						$ep['cntDrug'] = 0;
						foreach ($dopData as $row) {
							if ($EvnPrescr_id == $row['EvnPrescr_id']) {
								$ep['EvnPrescr_IsHasEvn'] = $row['EvnPrescr_IsHasEvn'];
								$ep['cntDrug']++;
								$str = $row['Drug_Name'];
								if (!empty($row['DoseDay'])) {
									$str .= ', дневная доза – '.$row['DoseDay'];
								}
								if (!empty($ep['PrescrCntDay'])) {
									$str .= ', '.(empty($row['FactCntDay'])?0:$row['FactCntDay']).'/'.$evnPrescr['PrescrCntDay'].'.';
								} else {
									$str .= '.';
								}
								$tmp2_arr[] = $str;
							}
						}
						$ep['EvnPrescr_Name'] = '<div>';
						$ep['EvnPrescr_Name'] .= implode('<br>',$tmp2_arr);
						if ( !empty($ep['EvnPrescr_Descr']) ) {
							$ep['EvnPrescr_Name'] .= '<div><span style="font-weight: bold;">Комментарий:</span> '. htmlspecialchars($ep['EvnPrescr_Descr']) .'</div>';
						}
						$ep['EvnPrescr_Name'] .= '</div>';
						$ep['UslugaId_List'] = '';
						$response['data'][] = $ep;
					}
					unset($dopData);
					break;
				case ( in_array($prescriptionType, array(7,12)) ):
					$query = "
						select
							EP.EvnPrescr_id as \"EvnPrescr_id\",
							UC.UslugaComplex_Name as \"UslugaComplex_Name\",
							UC.UslugaComplex_id as \"UslugaComplex_id\",
							case when EU.EvnUsluga_id is null then 1 else 2 end as \"EvnPrescr_IsHasEvn\"
						from v_EvnPrescr EP
						left join v_EvnPrescrOperUsluga EPOU on EPOU.EvnPrescrOper_id = EP.EvnPrescr_id
						left join v_EvnPrescrFuncDiagUsluga EPFDU on EPFDU.EvnPrescrFuncDiag_id = EP.EvnPrescr_id
						inner join v_UslugaComplex UC on UC.UslugaComplex_id = coalesce(EPFDU.UslugaComplex_id,EPOU.UslugaComplex_id)
						left join lateral(
							select EvnUsluga_id from v_EvnUsluga
							where EP.EvnPrescr_IsExec = 2 and EvnPrescr_id = EP.EvnPrescr_id
							limit 1
						) EU on true
						where EP.EvnPrescr_id in (".implode(',',$EvnPrescrIdList).")
					";
					// echo getDebugSQL($query, array()); exit();
					$result = $this->db->query($query, array());
					if ( !is_object($result) ) {
						return $response;
					}
					$dopData = $result->result('array');
					foreach ($EvnPrescrIdList as $i => $EvnPrescr_id) {
						$ep = $res_arr[$i];
						$tmp2_arr = array();
						$ep['UslugaId_List'] = array();
						$ep['cntUsluga'] = 0;
						foreach ($dopData as $row) {
							if ($EvnPrescr_id != $row['EvnPrescr_id']) {
								continue;
							}
							$ep['EvnPrescr_IsHasEvn'] = $row['EvnPrescr_IsHasEvn'];
							$ep['cntUsluga']++;
							$tmp2_arr[] = '<b>'.$row['UslugaComplex_Name'].'</b>';
							$ep['UslugaId_List'][] = $row['UslugaComplex_id'];
						}
						$ep['EvnPrescr_Name'] = '<div>';
						$ep['EvnPrescr_Name'] .= implode('<br />',$tmp2_arr);
						if ( $ep['EvnPrescr_IsCito'] == 2 )
							$ep['EvnPrescr_Name'] .=  '&nbsp;<span style="color: red">Cito!</span>';
						if ( !empty($ep['EvnPrescr_Descr']) )
						{
							$ep['EvnPrescr_Name'] .= '<div><span style="font-weight: bold;">Комментарий:</span> ' .  htmlspecialchars($ep['EvnPrescr_Descr']) . '</div>';
						}
						$ep['EvnPrescr_Name'] .= '</div>';
						$ep['UslugaId_List'] = implode(',',$ep['UslugaId_List']);
						$response['data'][] = $ep;
					}
					unset($dopData);
					break;
			}
		}

		// Хватит им количества уникальных персонов на одной (текущей) странице я думаю :)
		$persons = array();
		foreach ( $res_arr as $i => $evnPrescr ) {
			if (!in_array($evnPrescr['Person_id'], $persons)) {
				$persons[] = $evnPrescr['Person_id'];
			}
		}
		$response['countPerson'] = count($persons);

		unset($tmp_arr);
		unset($res_arr);

		return $response;
	}

	/**
	 * Создание случая наблюдения
	 */
	function saveEvnObserv($data) {
		$query = "
			select
				EvnObserv_id as \"EvnObserv_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_EvnObserv_ins(
				EvnObserv_pid := :EvnObserv_pid,
				Lpu_id := :Lpu_id,
				Server_id := :Server_id,
				PersonEvn_id := :PersonEvn_id,
				EvnObserv_setDT := :EvnObserv_setDT,
				ObservTimeType_id := :ObservTimeType_id,
				pmUser_id := :pmUser_id
			)
		";

		$queryParams = array(
			'EvnObserv_pid' => $data['EvnObserv_pid'],
            'Lpu_id' => $data['Lpu_id'],
            'Server_id' => $data['Server_id'],
            'PersonEvn_id' => $data['PersonEvn_id'],
            'EvnObserv_setDT' => DateTime::createFromFormat('m.d.Y',$data['EvnObserv_setDate'])->format('d.m.Y'),
            'ObservTimeType_id' => $data['ObservTimeType_id'],
            'pmUser_id' => $data['pmUser_id']
		);

		// echo getDebugSQL($query, $queryParams); //exit();

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 * Метод сохранения значения наблюдаемого параметра
	 */
	function saveEvnObservData($data) {
		$query = "
			select
				EvnObservData_id as \"EvnObservData_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_EvnObservData_".(empty($data['EvnObservData_id'])?'ins':'upd')."(
				EvnObservData_id := :EvnObservData_id,
				EvnObserv_id := :EvnObserv_id,
				ObservParamType_id := :ObservParamType_id,
				EvnObservData_Value := :EvnObservData_Value::varchar,
				pmUser_id := :pmUser_id
			)
		";

		$queryParams = array(
			'EvnObserv_id' => $data['EvnObserv_id'],
            'EvnObservData_id' => $data['EvnObservData_id'],
            'ObservParamType_id' => $data['ObservParamType_id'],
            'EvnObservData_Value' => (!empty($data['EvnObservData_Value']))?$data['EvnObservData_Value']:'',
            'pmUser_id' => $data['pmUser_id']
		);

		// echo getDebugSQL($query, $queryParams); exit();

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 * Метод сохранения назначений наблюдений
	 */
	function saveEvnPrescr($data) {
		$query = "
			select
				EvnPrescr_id as \"EvnPrescr_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_EvnPrescr_" . (!empty($data['EvnPrescr_id']) && $data['EvnPrescr_id'] > 0 ? "upd" : "ins") . "(
				EvnPrescr_id := :EvnPrescr_id,
				EvnPrescr_pid := :EvnPrescr_pid,
				Lpu_id := :Lpu_id,
				Server_id := :Server_id,
				PersonEvn_id := :PersonEvn_id,
				PrescriptionType_id := :PrescriptionType_id,
				PrescriptionIntroType_id := :PrescriptionIntroType_id,
				EvnPrescr_IsCito := :EvnPrescr_IsCito,
				pmUser_id := :pmUser_id
			)
		";

		$queryParams = array(
			'EvnPrescr_id' => (!empty($data['EvnPrescr_id']) && $data['EvnPrescr_id'] > 0 ? $data['EvnPrescr_id'] : NULL),
			'EvnPrescr_pid' => $data['EvnPrescr_pid'],
			'Lpu_id' => $data['Lpu_id'],
			'Server_id' => $data['Server_id'],
			'PersonEvn_id' => $data['PersonEvn_id'],
			'PrescriptionType_id' => $data['PrescriptionType_id'],
			'PrescriptionIntroType_id' => (!empty($data['PrescriptionIntroType_id']) ? $data['PrescriptionIntroType_id'] : NULL),
			'EvnPrescr_IsCito' => (!empty($data['EvnPrescr_IsCito']) ? $data['EvnPrescr_IsCito'] : 1),
			'pmUser_id' => $data['pmUser_id']
		);

		// echo getDebugSQL($query, $queryParams); exit();

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $prescriptiontype_id
	 * @return string
	 * @throws Exception
	 */
	function defineEvnPrescrType($prescriptiontype_id) {
		switch ( $prescriptiontype_id ) {
			case 1:	$evnPrescrType = 'Regime'; break;
			case 2: $evnPrescrType = 'Diet'; break;
			case 3: $evnPrescrType = 'Diag'; break;
			case 4: $evnPrescrType = 'Cons'; break;
			case 5: $evnPrescrType = 'Treat'; break;
			case 6: $evnPrescrType = 'Proc'; break;
			case 7: $evnPrescrType = 'Oper'; break;
			case 10: $evnPrescrType = 'Observ'; break;
			case 11: $evnPrescrType = 'LabDiag'; break;
			case 12: $evnPrescrType = 'FuncDiag'; break;
			case 13: $evnPrescrType = 'ConsUsluga'; break;
			case 14: $evnPrescrType = 'OperBlock'; break;
			case 15: $evnPrescrType = 'Vaccination'; break;
			default:
				throw new Exception('Неверный тип назначения');
				break;
		}
		return $evnPrescrType;
	}


	/**
	 * Метод подписания
	 */
	function signEvnPrescr($data) {
		try {
			$evnPrescrType = $this->defineEvnPrescrType($data['PrescriptionType_id']);
		} catch (Exception $e) {
			return array(array('Error_Msg' => $e->getMessage()));
		}

		$queryParams = array(
			'EvnPrescr_pid' => $data['EvnPrescr_pid']
		);

		$f_date = '';

		if ( !empty($data['EvnPrescr_id']) && in_array($data['PrescriptionType_id'], array(4,5,6,7,11,12,13)) )
		{
			$f_date .= ' and EP.Evn'. $evnPrescrType . '_id = :EvnPrescr_id';
			$queryParams['EvnPrescr_id'] = $data['EvnPrescr_id'];
		}

		if ( !empty($data['EvnPrescr_setDate']) && (!is_array($data['EvnPrescr_rangeDate'] || count($data['EvnPrescr_rangeDate']) == 0 || empty($data['EvnPrescr_rangeDate'][0])) /*|| !in_array($data['PrescriptionType_id'], array(1, 2, 5, 6, 10))*/) )
		{
			$f_date .= ' and cast(EP.EvnPrescr'. $evnPrescrType . '_setDT as date) = cast(:EvnPrescr_setDate as date)';
			$queryParams['EvnPrescr_setDate'] = $data['EvnPrescr_setDate'];
		}

		if (!empty($data['unsign'])) {
			$error_msg = '';

			$query = "
				update
					EvnPrescr" . $evnPrescrType . "
				set
					PrescriptionStatusType_id = 1
				from 
				    EvnPrescr" . $evnPrescrType . " EP,
				    v_Evn ve
				where 
                    EvnPrescr" . $evnPrescrType . ".evn_id = EP.evn_id AND
                    ve.Evn_id = EP.Evn_id AND 
                    ve.Evn_pid = :EvnPrescr_pid
					{$f_date}
			";

			$result = $this->db->query($query, $queryParams);

		} else {

			// Получаем список назначений указанного типа за день
			$query = "
				select
					EP.EvnPrescr" . $evnPrescrType . "_id as \"EvnPrescr_id\"
				from v_EvnPrescr" . $evnPrescrType . " EP
				where EP.EvnPrescr" . $evnPrescrType . "_pid = :EvnPrescr_pid
					{$f_date}
					and EP.PrescriptionStatusType_id = 1
			";

			//echo getDebugSQL($query, $queryParams); exit();

			$result = $this->db->query($query, $queryParams);

			if ( !is_object($result) ) {
				return false;
			}

			$response = $result->result('array');

			$error_msg = 'Ни одно назначение не подписано';
			foreach ( $response as $evnPrescrData ) {
				// Добавить еще врача, который подписал назначение
				$query = "
					select
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Msg\"
					from p_EvnPrescr_sign(
						EvnPrescr_id := :EvnPrescr_id,
						MedStaffFact_sid := :MedStaffFact_sid,
						pmUser_id := :pmUser_id
					)
				";

				$queryParams = array(
					'EvnPrescr_id' => $evnPrescrData['EvnPrescr_id'],
					'MedStaffFact_sid' => (!empty($data['session']['CurMedStaffFact_id']) ? $data['session']['CurMedStaffFact_id'] : NULL),
					'pmUser_id' => $data['pmUser_id']
				);

				$result = $this->db->query($query, $queryParams);

				if ( !is_object($result) ) {
					return false;
				}
				$res = $result->result('array');
				if(empty($res) || empty($res[0]) || !empty($res[0]['Error_Msg']))
				{
					return $res;
				}
				$error_msg = '';
			}
		}

		return array(array('Error_Msg'=>$error_msg));
	}

	/**
	 * Метод получения списка параметров наблюдения
	 */
	function getObservParamTypeList() {
		$query = "
			select
				 ObservParamType_id as \"ObservParamType_id\",
				 ObservParamType_Name as \"ObservParamType_Name\"
			from
				v_ObservParamType
			order by
				ObservParamType_id
		";

		$result = $this->db->query($query);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Метод
	 */
	function getEvnPrescrsList($data)
	{
		if(isset($data['PrescriptionStatusType_id']))
		{
			$operator = '=';
			$coalesce = 1;
		}
		else
		{
			//по умолчанию выводит не отмененные назначения
			$operator = '!=';
			$coalesce = 3;
			$data['PrescriptionStatusType_id'] = 3;
		}
		$res_fields = '';
		$old_fields = '';
		$add_join = '';
		$where_clause = 'WHERE (1=1)';
		if(isset($data['addFields']) && is_array($data['addFields']))
		{
			foreach($data['addFields'] as $field) {
				switch($field) {
					case 'EvnPrescr_date':
					case 'EvnPrescr_setDate':
						if($field == 'EvnPrescr_date') $res_fields .= ',cast(cast(EP.EvnPrescr_setDT as date) as varchar(10)) as "EvnPrescr_date" ';
						if($field == 'EvnPrescr_setDate') $res_fields .= ",to_char(varchar,EP.EvnPrescr_setDT, 'dd.mm.yyyy') as \"EvnPrescr_setDate\" ";
						$old_fields .= ',coalesce(EvnPrescrRegime_setDT,EvnPrescrDiet_setDT,EvnPrescrObserv_setDT,EvnPrescr_setDT) as "EvnPrescr_setDT"';
						$where_clause .= ' and EP.EvnPrescr_setDT is not null';
						$add_join .= 'left join v_EvnPrescrRegime Regime on E.PrescriptionType_id = 1 and Regime.EvnPrescrRegime_pid = E.EvnPrescr_id
					left join v_EvnPrescrDiet Diet on E.PrescriptionType_id = 2 and Diet.EvnPrescrDiet_pid = E.EvnPrescr_id
					left join v_EvnPrescrObserv Obs on E.PrescriptionType_id = 10 and Obs.EvnPrescrObserv_pid = E.EvnPrescr_id
';
						break;
				}
			}
		}
		$order = 'EP.PrescriptionType_id, EP.EvnPrescr_id';
		if(isset($data['orderFields']) && is_array($data['orderFields']))
		{
			$order_fields = array();
			foreach($data['orderFields'] as $field) {
				$order_fields[] = 'EP.'.$field;
			}
			if(count($order_fields) > 0)
				$order = implode(' ,',$order_fields . " as \"{$order_fields}\"");
		}
		$query = "
			select
				 EP.EvnPrescr_id as \"EvnPrescr_id\"
				,EP.PrescriptionType_id as \"PrescriptionType_id\"
				{$res_fields}
			from
			(
				select distinct
					E.EvnPrescr_id
					,E.PrescriptionType_id
					{$old_fields}
				from v_EvnPrescr E
					{$add_join}
				where
					E.EvnPrescr_pid = :EvnPrescr_pid 
					and E.PrescriptionType_id in (1,2,5,6,7,10,11,12,13)
					and coalesce(E.PrescriptionStatusType_id,{$coalesce}) {$operator} :PrescriptionStatusType_id
			) EP
			{$where_clause}
			order by
				{$order}
		";
		// echo getDebugSQL($query, $data); exit();
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 *	$df - массив, содержащий дополнительные поля, характерные для назначения конкретного типа
	 */
	function getEvnPrescription($data, $df)
	{
		$ptype = $data['evnPrescrType'];
		$select = '';
		$from = 'v_EvnPrescr' . $ptype . ' epp 
			left join v_EvnCourse ec on ec.EvnCourse_id=epp.EvnCourse_id
			';
		switch($ptype){
			case 'TreatDrug':
				$from = 'v_EvnPrescrTreatDrug epp 
				left join v_EvnPrescrTreat evct on evct.EvnPrescrTreat_id=epp.EvnPrescrTreat_id
			left join v_EvnCourseTreat ect on ect.EvnCourseTreat_id = evct.EvnCourse_id';
				$where = 'epp.EvnPrescrTreat_id = :EvnPrescr_id';
				break;
			case 'ObservPos':
				$from = 'v_EvnPrescr' . $ptype . ' epp ';
				$where = 'EvnPrescr_id = :EvnPrescr_id';
				break;
			case 'ProcUsluga':
				$from = 'v_EvnPrescrProc';
				$where = 'EvnPrescrProc_id = :EvnPrescr_id';
				break;
			case 'OperUsluga':
			case 'LabDiagUsluga':
			case 'FuncDiagUsluga':
				$from = 'v_EvnPrescr' . $ptype . ' epp ';
				$where = 'EvnPrescr'.$data['ept'].'_id = :EvnPrescr_id';
				break;
			//
			case 'Cons':
			case 'Treat':
				$from.='left join v_EvnCourseTreat ecp on ecp.EvnCourseTreat_id = epp.EvnCourse_id';
				$where = 'EvnPrescr' . $ptype . '_id = :EvnPrescr_id and coalesce(PrescriptionStatusType_id,1) != 3';
				if(isset($data['EvnPrescr_begDate'])){
					$select .= "date_part('day', EvnPrescr' . $ptype . '_setDate - cast(:EvnPrescr_begDate as date)) + 1 as \"EvnPrescr_DayNum\"";
				}
				break;
			case 'Proc':
				$from.='left join v_EvnCourseProc ecp on ecp.EvnCourseProc_id = epp.EvnCourse_id';
				$where = 'EvnPrescr' . $ptype . '_id = :EvnPrescr_id and coalesce(PrescriptionStatusType_id,1) != 3';
				if(isset($data['EvnPrescr_begDate'])){
					$select .= "date_part('day', EvnPrescr' . $ptype . '_setDate - cast(:EvnPrescr_begDate as date)) + 1 as \"EvnPrescr_DayNum\"";
				}
				break;
			case 'Oper':
			case 'LabDiag':
			case 'FuncDiag':
			case 'ConsUsluga':

				$where = 'EvnPrescr' . $ptype . '_id = :EvnPrescr_id and coalesce(PrescriptionStatusType_id,1) != 3';
				if(isset($data['EvnPrescr_begDate'])){
					$select .= "date_part('day', EvnPrescr' . $ptype . '_setDate - cast(:EvnPrescr_begDate as date)) + 1 as \"EvnPrescr_DayNum\"";
				}
				break;
			/*
			case 'Regime':
			case 'Diet':
			case 'Observ':
			case 'Diag':
			*/
			default:
				$where = 'EvnPrescr' . $ptype . '_pid = :EvnPrescr_id and coalesce(PrescriptionStatusType_id,1) != 3';
				if(isset($data['EvnPrescr_begDate'])){
					$select .= "date_part('day', EvnPrescr' . $ptype . '_setDate - cast(:EvnPrescr_begDate as date)) + 1 as \"EvnPrescr_DayNum\"";
				}
				break;
		}

		if($df && is_array($df) && count($df)>0){
			for($i=0; $i<count($df); $i++){
				$select .= (trim($select)=='') ? $df[$i] : ',' . $df[$i];
			}
		}

		$query = "
			select
				{$select}
			from
				{$from}
			where
				{$where}
		";
		//echo getDebugSQL($query, $data);
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Получение данных для формы добавления услуги при выполнении назначения с оказанием услуги
	 */
	function loadEvnUslugaData($data)
	{
		$query = "
			select
				v_Evn.EvnClass_SysNick as \"EvnClass_SysNick\",
				coalesce(ES.MedStaffFact_id,EV.MedStaffFact_id) as \"MedStaffFact_id\",
				LS.LpuSection_id as \"LpuSection_id\",
				LS.LpuSection_Name as \"LpuSection_Name\",
				to_char(v_Evn.Evn_setDate, 'dd.mm.yyyy') as \"Evn_setDate\",
				Evn_setTime as \"Evn_setTime\",
				(select Usluga_id from v_UslugaComplex where UslugaComplex_id = :UslugaComplex_id limit 1) as \"Usluga_id\",
				MP.Person_Fio as \"MedPersonal_FIO\",
				MP.MedPersonal_id as \"MedPersonal_id\"
			from
				v_Evn
				left join v_EvnSection ES on ES.EvnSection_id = v_Evn.Evn_id
				left join v_EvnVizit EV on EV.EvnVizit_id = v_Evn.Evn_id
				left join v_LpuSection LS on LS.LpuSection_id = coalesce(ES.LpuSection_id,EV.LpuSection_id)
				left join v_MedPersonal MP on MP.MedPersonal_id = coalesce(ES.MedPersonal_id,EV.MedPersonal_id)
					and MP.Lpu_id = v_Evn.Lpu_id
			where
				v_Evn.Evn_id = :Evn_id
			limit 1
		";
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Метод подписания
	 */
	function setCitoEvnPrescr($data) {
		$proc = 'isCito';
		$error_msg = '';
		if($data['EvnPrescr_IsCito']!=2)
			$proc = 'unCito';
		
		/*echo "<pre>";
		echo "Proc - " . $proc;
		echo "</pre>";
		die();*/
		
		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_EvnPrescr_{$proc}(
				EvnPrescr_id := :EvnPrescr_id,
				pmUser_id := :pmUser_id
			)
		";

		$queryParams = array(
			'EvnPrescr_id' => $data['EvnPrescr_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			return false;
		}
		$res = $result->result('array');
		if(empty($res) || empty($res[0]) || !empty($res[0]['Error_Msg']))
		{
			return $res;
		}

		return array(array('Error_Msg'=>$error_msg));

	}


    /**
     * Метод смены значения цели исследований
     */
    function setStudyTargetEvnPrescr($data)
    {
        $error_msg = '';
        $query = "
            select
                ErrCode as \"Error_Code\",
                ErrMessage as \"Error_Msg\"
            from p_EvnPrescr_upd
            (
                EvnPrescr_id := :EvnPrescr_id,
                pmUser_id := :pmUser_id,
                StudyTarget_id := :StudyTarget_id
            )
		";

        $queryParams = array(
            'EvnPrescr_id' => $data['EvnPrescr_id'],
            'pmUser_id' => $data['Person_id'],
            'StudyTarget_id' => $data['StudyTarget_id']
        );

        $result = $this->db->query($query, $queryParams);

        if ( !is_object($result) ) {
            return false;
        }
        $res = $result->result('array');
        if(empty($res) || empty($res[0]) || !empty($res[0]['Error_Msg']))
        {
            return $res;
        }

        return [['Error_Msg' => $error_msg]];

    }

	/**
	 * Устанавливает назначению статус "отменено"
	 * и сохраняет данные о том, какой врач отменил, из какого отделения,
	 * если назначение подписано, но не выполнено
	 * или удаляет назначение
	 */
	function deleteFromDirection($data)
	{
		$response = array(array(
			'Error_Msg' => null,
			'Error_Code' => null,
		));
		try {
			if (empty($data['EvnPrescr_id'])) {
				throw new Exception('Не указано назначение!', 400);
			}
			if (empty($data['pmUser_id'])) {
				throw new Exception('Не указан пользователь!', 400);
			}
			$tmp = $this->loadEvnPrescrListForCancel(null, $data['EvnPrescr_id']);
			if (empty($tmp)) {
				throw new Exception('Назначение не найдено!', 400);
			}
			if ($tmp[0]['EvnPrescr_IsExec'] == 2) {
				throw new Exception('Назначение выполнено и не может быть удалено!', 400);
			}
			if ($tmp[0]['PrescriptionStatusType_id'] == 3) {
				throw new Exception('Назначение отменено и не может быть удалено!', 400);
			}
			if (!empty($tmp[0]['EvnDirection_id']) && !in_array($tmp[0]['EvnStatus_id'], array(10, 16, 17))) {
				throw new Exception('Назначение не может быть отменено. Отменить можно, если направление имеет статус "Записано на бирку" или "В очереди"!', 400);
			}
			$data['EvnCourse_id'] = $tmp[0]['EvnCourse_id'];
			$data['EvnDirection_id'] = $tmp[0]['EvnDirection_id'];
			$data['EvnStatus_id'] = $tmp[0]['EvnStatus_id'];
			$data['TimetableMedService_id'] = $tmp[0]['TimetableMedService_id'];
			$data['EvnQueue_id'] = $tmp[0]['EvnQueue_id'];
			$data['TimetableResource_id'] = $tmp[0]['TimetableResource_id'];
			// Если назначение имеет направление, то нужно сначала отменить направление, но только в том случае если по направлению только 1 назначение.
			$needCancelDirection = true;
			if (!empty($data['EvnDirection_id'])) {
				$resp_ep = $this->queryResult("
					select
						count(ep.EvnPrescr_id) as \"cnt\"
					from
						v_EvnPrescrDirection epd
						inner join v_EvnPrescr ep on ep.EvnPrescr_id = epd.EvnPrescr_id
					where
						epd.EvnDirection_id = :EvnDirection_id
				", array(
					'EvnDirection_id' => $data['EvnDirection_id']
				));
				if (!empty($resp_ep[0]['cnt']) && $resp_ep[0]['cnt'] > 1) {
					$needCancelDirection = false;
				}
			}
			if ($needCancelDirection) {
				if (!empty($data['TimetableMedService_id'])) {
					throw new Exception($data['TimetableMedService_id'], 800);
				}
				if (!empty($data['EvnQueue_id'])) {
					throw new Exception($data['EvnQueue_id'], 801);
				}
				if (!empty($data['TimetableResource_id'])) {
					throw new Exception($data['TimetableResource_id'], 802);
				}
				if (!empty($data['EvnDirection_id'])) {
					throw new Exception('Нужно сначала отменить направление!', 400);
				}
			} else {
				// надо убрать услугу назначения из заявки и из заказа
				$resp_uc = $this->queryResult("
					select
						epld.UslugaComplex_id as \"UslugaComplex_id\",
						epd.EvnDirection_id as \"EvnDirection_id\"
					from
						v_EvnPrescrLabDiag epld
						inner join v_EvnPrescrDirection epd on epld.EvnPrescrLabDiag_id = epd.EvnPrescr_id
					where
						epld.EvnPrescrLabDiag_id = :EvnPrescr_id
				", array(
					'EvnPrescr_id' => $data['EvnPrescr_id']
				));
				if (!empty($resp_uc[0]['EvnDirection_id']) && !empty($resp_uc[0]['UslugaComplex_id'])) {
					// из EvnDirectionUslugaComplex убираем назначенную услугу
					$this->load->model('EvnDirection_model');
					$this->EvnDirection_model->cancelUslugaComplex(array(
						'EvnDirection_id' => $resp_uc[0]['EvnDirection_id'],
						'UslugaComplex_id' => $resp_uc[0]['UslugaComplex_id'],
						'pmUser_id' => $data['pmUser_id']
					));
					// из EvnUslugaPar убираем назначенную услугу
					$this->load->model('EvnLabSample_model');
					$this->EvnLabSample_model->cancelResearch(array(
						'EvnDirection_id' => $resp_uc[0]['EvnDirection_id'],
						'UslugaComplex_id' => $resp_uc[0]['UslugaComplex_id'],
						'pmUser_id' => $data['pmUser_id']
					));
					$data['EvnDirection_id'] = $resp_uc[0]['EvnDirection_id'];
				}
				if (!empty($data['EvnDirection_id']) && !empty($data['EvnPrescr_id'])) {
					$EvnPrescrDirection = $this->getFirstRowFromQuery("
						SELECT 
							EvnPrescrDirection_id as \"EvnPrescrDirection_id\" 
						FROM EvnPrescrDirection ed 
						WHERE 
							ed.EvnPrescr_id = :EvnPrescr_id 
							AND ed.EvnDirection_id = :EvnDirection_id
						limit 1
					", $data);
					if (empty($EvnPrescrDirection)) throw new Exception($data['EvnDirection_id'], 800);
					$resp = $this->deleteEvnPrescrDirection($EvnPrescrDirection);
					return $resp;
				}
			}
		} catch (Exception $e) {
			if (800 === $e->getCode()) {
				$response[0]['TimetableMedService_id'] = $e->getMessage();
				$response[0]['Error_Msg'] = 'Нужно сначала освободить запись!';
				$response[0]['Error_Code'] = $e->getCode();
			} else if (801 === $e->getCode()) {
				$response[0]['EvnQueue_id'] = $e->getMessage();
				$response[0]['Error_Msg'] = 'Нужно сначала отменить постановку в очередь!';
				$response[0]['Error_Code'] = $e->getCode();
			} else if (802 === $e->getCode()) {
				$response[0]['TimetableResource_id'] = $e->getMessage();
				$response[0]['Error_Msg'] = 'Нужно сначала освободить запись!';
				$response[0]['Error_Code'] = $e->getCode();
			} else {
				$response[0]['Error_Msg'] = $e->getMessage();
				$response[0]['Error_Code'] = $e->getCode();
			}
		}
		return $response;
	}

	/**
	 *  Получение списка отделений для фильтрации медикаментов на форме редактирования назначения
	 *  Входящие данные: <фильтры>
	 *  На выходе: JSON-строка
	 *  Используется: комбобокс "Отделение" на форме редактирования назначения
	 */
	function loadLpuSectionCombo($data) {
		$params = array();
		$where = array();

		if (!empty($data['LpuSection_id'])) {
			$where[] = "ls.LpuSection_id = :LpuSection_id";
			$params['LpuSection_id'] = $data['LpuSection_id'];
		} else {
			if (!empty($data['query'])) {
				$where[] = "ls.LpuSection_Name ilike :query";
				$params['query'] = "%".$data['query']."%";
			}
		}

		$params['UserLpuSection_id'] = $data['UserLpuSection_id'];

		$query = "
			select
				ls.LpuBuilding_id as \"LpuBuilding_id\"
			from
				v_LpuSection ls
			where
				ls.LpuSection_id = :UserLpuSection_id
		";
		$params['UserLpuBuilding_id'] = $this->getFirstResultFromQuery($query, $params);
		$where[] = "ls.LpuBuilding_id = :UserLpuBuilding_id";

		$where_clause = implode(" and ", $where);
		if (strlen($where_clause)) {
			$where_clause = "
				where
					{$where_clause}
			";
		}

		$query = "
			select
				p.LpuSection_id as \"LpuSection_id\",
				p.LpuSection_Name as \"LpuSection_Name\"
			from
				(
					select
						ls.LpuSection_id,
						ls.LpuSection_Name,
						(case
							when ls.LpuSection_id = :UserLpuSection_id then 1
							else 3
						end) as ord_val
					from
						v_LpuSection ls	
					{$where_clause}
					/*union all
					select
						-1 as LpuSection_id,
						'Аптека МО' as LpuSection_Name,
						2 as ord_val*/
				) p
			order by
				p.ord_val
			limit 250			
		";
		$result = $this->queryResult($query, $params);
		return $result;
	}

	/**
	 *  Получение списка склодов для фильтрации медикаментов на форме редактирования назначения
	 *  Входящие данные: <фильтры>
	 *  На выходе: JSON-строка
	 *  Используется: комбобокс "Склад" на форме редактирования назначения
	 */
	function loadStorageCombo($data) {
		$params = array();
		$where = array();

		if (!empty($data['Storage_id'])) {
			$where[] = "s.Storage_id = :Storage_id";
			$params['Storage_id'] = $data['Storage_id'];
		} else {
			if (!empty($data['query'])) {
				$where[] = "s.Storage_Name ilike :query";
				$params['query'] = "%".$data['query']."%";
			}
		}
		$params['UserLpuSection_id'] = $data['UserLpuSection_id'];

		$where_clause = implode(" and ", $where);
		if (strlen($where_clause)) {
			$where_clause = "
				and {$where_clause}
			";
		}

		$query = "
			select
				s.Storage_id as \"Storage_id\",
				s.Storage_Name as \"Storage_Name\"
			from
				v_StorageStructLevel ssl
				left join v_Storage s on s.Storage_id = ssl.Storage_id
				left join lateral(
					select
						(case
							when ssl.LpuSection_id = :UserLpuSection_id then 1
							else 2
						end) as val
				) ord on true
			where
				(
					ssl.LpuSection_id = :UserLpuSection_id or
					ssl.MedService_id in (
						select
							ms.MedService_id
						from 
							v_MedService ms
							left join v_MedServiceType mst on ms.MedServiceType_id = mst.MedServiceType_id
						where
							ms.LpuSection_id = :UserLpuSection_id and
							mst.MedServiceType_SysNick = 'merch'
					)
				)
				{$where_clause}	
			order by
				ord.val
			limit 250
					
		";
		$result = $this->queryResult($query, $params);
		return $result;
	}

	/**
	 * Получение списка клин. рекомендаций
	 */
	function loadEvnVizitWithPrescrList($data){
		$limit = '';
		$select = ",to_char(evpl.EvnVizitPL_setDate, 'dd.mm.yyyy') as \"objectSetDate\"
				,evpl.EvnVizitPL_setTime as \"objectSetTime\"
				,d.Diag_Name as \"Diag_Name\"
				,d.Diag_Code as \"Diag_Code\"
				,lsp.LpuSectionProfile_Name as \"LpuSectionProfile_Name\"
				";
		$queryParams = array(
			'Person_id' => $data['Person_id']
		);

		if(!empty($data['top']) && intval($data['top']) > 0){
			// Если необходимы только несколько записей, выдаем их количество
			$limit = 'limit '.$data['top'];
			// Если необходимо только 2 записи, значит это проверка на существование
			if( intval($data['top']) == 2)
				$select = '';
		}

		//countRouteList
		$DestRouteList =  $this->queryResult("
			SELECT
				evpl.EvnVizitPL_id as \"Evn_id\"
				{$select}
			from
				v_EvnVizitPL evpl
				left join v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = evpl.LpuSectionProfile_id
				left join v_diag d on d.Diag_id = evpl.Diag_id
				left join v_EvnPrescr EP on EP.EvnPrescr_pid = evpl.EvnVizitPL_id
                left join v_EvnPrescrProc EPPR on EP.PrescriptionType_id = 6 and EPPR.EvnPrescrProc_id = EP.EvnPrescr_id
				left join v_EvnCourseProc ECP on EP.PrescriptionType_id = 6 and EPPR.EvnCourse_id = ECP.EvnCourseProc_id
				left join lateral(
					select UslugaComplex_id from v_EvnPrescrOperUsluga where EP.PrescriptionType_id = 7 and EvnPrescrOper_id = EP.EvnPrescr_id limit 1
				) EPOU on true
				left join lateral(
					select UslugaComplex_id from v_EvnPrescrLabDiag where EP.PrescriptionType_id = 11 and EvnPrescrLabDiag_id = EP.EvnPrescr_id limit 1
				) EPLD on true
				left join lateral(
					select UslugaComplex_id from v_EvnPrescrLabDiagUsluga where EP.PrescriptionType_id = 11 and EvnPrescrLabDiag_id = EP.EvnPrescr_id limit 1
				) EPLDU on true
				left join lateral(
					select UslugaComplex_id from v_EvnPrescrFuncDiagUsluga where EP.PrescriptionType_id = 12 and EvnPrescrFuncDiag_id = EP.EvnPrescr_id limit 1
				) EPFDU on true
				left join lateral(
					select UslugaComplex_id from v_EvnPrescrConsUsluga where EP.PrescriptionType_id = 13 and EvnPrescrConsUsluga_id = EP.EvnPrescr_id limit 1
				) EPCU on true
				left join v_UslugaComplex UC on EP.PrescriptionType_id in (6,7,11,12,13) and UC.UslugaComplex_id = coalesce(EPPR.UslugaComplex_id,EPFDU.UslugaComplex_id,EPLDU.UslugaComplex_id,EPOU.UslugaComplex_id,EPCU.UslugaComplex_id)
				left join v_UslugaComplex PUC on EP.PrescriptionType_id = 11 and PUC.UslugaComplex_id = EPLD.UslugaComplex_id
				left join lateral(
					Select ED.EvnDirection_id
					from v_EvnPrescrDirection epd
					inner join v_EvnDirection_all ED on epd.EvnDirection_id = ED.EvnDirection_id
					where epd.EvnPrescr_id = EP.EvnPrescr_id
					and ED.DirType_id != 24
					AND coalesce(ED.DirType_id,1) not in (7, 18, 19, 20) and ED.EvnStatus_id not in (12, 13, 15)
					order by 
						case when coalesce(ED.EvnStatus_id, 16) in (12,13) then 2 else 1 end /* первым неотмененное/неотклоненное направление */
						,epd.EvnPrescrDirection_insDT desc
					limit 1
				) ED on true
			where
				 (1=1) and evpl.Person_id = :Person_id
				 and coalesce(UC.UslugaComplex_id,PUC.UslugaComplex_id) is not NULL
				 AND evpl.EvnVizitPL_setDate > (dbo.tzGetDate() - interval '2 month')
				 AND ED.EvnDirection_id IS NOT NULL
			
			GROUP BY 
				evpl.EvnVizitPL_id,
				evpl.EvnVizitPL_setDate,
				evpl.EvnVizitPL_setTime,
				lsp.LpuSectionProfile_Name,
				d.Diag_Name,
				d.Diag_Code
			ORDER by 
				evpl.EvnVizitPL_setDate DESC
			{$limit}
		", $queryParams);

		// Значит это проверка на наличие множества посещений
		if(!empty($data['top']) && intval($data['top']) == 2){
			$checkData = array(
				'countRouteList' => count($DestRouteList)
			);
			if(count($DestRouteList) == 1)
				$checkData['Evn_id'] = $DestRouteList[0]['Evn_id'];
			$DestRouteList = $checkData;
		}


		return $DestRouteList;
	}


	/**
	 * Применеие списка клин. рекомендаций
	 */
	function saveTreatmentStandardsForm($data){
		$err_arr = array();
		$save_data = json_decode($data['save_data'], true);
		$evn = $this->queryResult("
			select EvnClass_SysNick as \"EvnClass_SysNick\",
				PersonEvn_id as \"PersonEvn_id\",
				Server_id as \"Server_id\",
				Evn_setDT as \"Evn_setDT\"
			from v_Evn
			where Evn_id = :id
		", array(
			'id' => $data['Evn_pid'],
		));
		$evn = $evn[0];
		
		$Evn_setDT = DateTime::createFromFormat('Y-m-d H:i:s', $evn['Evn_setDT']);
		if (!empty($Evn_setDT) && $Evn_setDT instanceof DateTime) {
			$default_set_date = $Evn_setDT->format('Y-m-d');
			$parentEvnClass_SysNick = $evn['EvnClass_SysNick'];
		} else {
			$this->ReturnData(array('success' => false, 'Error_Msg' => 'Не удалось получить дату учетного документа'));
			return false;
		}

		if (!empty($save_data['proc']) && is_array($save_data['proc']) && count($save_data['proc']) > 0) {
			$tmp_data = $data;
			$err_arr['proc'] = array();
			$tmp_data['EvnCourseProc_pid'] = $data['Evn_pid'];
			$tmp_data['EvnCourseProc_id'] = NULL;
			$tmp_data['EvnCourseProc_setDate'] = $default_set_date;
			$tmp_data['MedPersonal_id'] = $data['session']['medpersonal_id'];
			$tmp_data['LpuSection_id'] = $data['session']['CurLpuSection_id'];
			$tmp_data['Morbus_id'] = NULL;
			$tmp_data['signature'] = NULL;
			$tmp_data['EvnPrescrProc_IsCito'] = NULL;
			$tmp_data['EvnPrescrProc_Descr'] = NULL;
			$tmp_data['MedService_id'] = NULL;
			$this->load->model('EvnPrescrProc_model', 'EvnPrescrProc_model');
			foreach ($save_data['proc'] as $id) {
				if (!empty($id) && is_numeric($id)) {
					$tmp_data['UslugaComplex_id'] = $id;
					$response = $this->EvnPrescrProc_model->doSaveEvnCourseProc($tmp_data); //$response[0]['EvnCourseProc_id']
					if (!empty($response[0]['Error_Msg'])) {
						return array(array('success' => false, 'Error_Msg' => 'Ошибка при сохранении назначения'));
					}
				}
			}
		}
		if (!empty($save_data['funcdiag']) && is_array($save_data['funcdiag']) && count($save_data['funcdiag']) > 0) {
			$tmp_data = $data;
			$err_arr['funcdiag'] = array();
			$tmp_data['EvnPrescrFuncDiag_pid'] = $data['Evn_pid'];
			$tmp_data['EvnPrescrFuncDiag_id'] = NULL;
			$tmp_data['EvnPrescrFuncDiag_setDate'] = $default_set_date;
			$tmp_data['signature'] = NULL;
			$tmp_data['EvnPrescrFuncDiag_IsCito'] = NULL;
			$tmp_data['EvnPrescrFuncDiag_Descr'] = NULL;
			$tmp_data['StudyTarget_id'] = NULL;
			$tmp_data['MedService_id'] = NULL;
			$this->load->model('EvnPrescrFuncDiag_model', 'EvnPrescrFuncDiag_model');
			foreach ($save_data['funcdiag'] as $id) {
				if (!empty($id) && is_numeric($id)) {
					$tmp_data['EvnPrescrFuncDiag_uslugaList'] = $id;
					$response = $this->EvnPrescrFuncDiag_model->doSave($tmp_data); //$response[0]['EvnPrescrFuncDiag_id']
					if (!empty($response[0]['Error_Msg'])) {
						return array(array('success' => false, 'Error_Msg' => 'Ошибка при сохранении курса'));
					}
				}
			}
		}

		if (!empty($save_data['consusluga']) && is_array($save_data['consusluga']) && count($save_data['consusluga']) > 0) {
			$tmp_data = $data;
			$err_arr['consusluga'] = array();
			$tmp_data['parentEvnClass_SysNick'] = $parentEvnClass_SysNick;
			$tmp_data['signature'] = NULL;
			$tmp_data['EvnPrescrConsUsluga_id'] = NULL;
			$tmp_data['EvnPrescrConsUsluga_pid'] = $data['Evn_pid'];
			$tmp_data['EvnPrescrConsUsluga_setDate'] = $default_set_date;
			$tmp_data['EvnPrescrFuncDiag_IsCito'] = NULL;
			$tmp_data['EvnPrescrFuncDiag_Descr'] = NULL;
			$tmp_data['EvnPrescrConsUsluga_Descr'] = '';
			$tmp_data['DopDispInfoConsent_id'] = null;

			$this->load->model('EvnPrescrConsUsluga_model', 'EvnPrescrConsUsluga_model');
			foreach ($save_data['consusluga'] as $id) {
				if (!empty($id) && is_numeric($id)) {
					$tmp_data['UslugaComplex_id'] = $id;
					$response = $this->EvnPrescrConsUsluga_model->doSave($tmp_data);
					if (!empty($response[0]['Error_Msg'])) {
						return array(array('success' => false, 'Error_Msg' => 'Ошибка при сохранении курса'));
					}
				}
			}
		}

		if (!empty($save_data['labdiag']) && is_array($save_data['labdiag']) && count($save_data['labdiag']) > 0) {
			$tmp_data = $data;
			$err_arr['labdiag'] = array();
			$tmp_data['EvnPrescrLabDiag_pid'] = $data['Evn_pid'];
			$tmp_data['EvnPrescrLabDiag_id'] = NULL;
			$tmp_data['EvnPrescrLabDiag_setDate'] = $default_set_date;
			$tmp_data['signature'] = NULL;
			$tmp_data['MedService_pzmid'] = NULL;
			$tmp_data['EvnPrescrLabDiag_IsCito'] = NULL;
			$tmp_data['EvnPrescrLabDiag_Descr'] = NULL;
			$tmp_data['StudyTarget_id'] = NULL;
			$tmp_data['MedService_id'] = NULL;
			$this->load->model('EvnPrescrLabDiag_model', 'EvnPrescrLabDiag_model');
			foreach ($save_data['labdiag'] as $id) {
				
				
				$tmp_data['UslugaComplex_id'] = $id;
				$tmp_data['EvnPrescrLabDiag_uslugaList'] = $id;
				$response = $this->EvnPrescrLabDiag_model->doSave($tmp_data); //$response[0]['EvnPrescrLabDiag_id']
				if (!empty($response[0]['Error_Msg'])) {
					return array(array('success' => false, 'Error_Msg' => 'Ошибка при сохранении лабораторной диагностики'));
				}
			}
		}

		if (!empty($save_data['drug']) && is_array($save_data['drug']) && count($save_data['drug']) > 0) {
			$this->load->model('EvnPrescrTreat_model', 'EvnPrescrTreat_model');
			$this->load->model('Dlo_EvnRecept_model', 'Dlo_EvnRecept_model');

			$params = array();
			$err_arr['drug'] = array();
			$params['Lpu_id'] = $data['Lpu_id'];
			$params['MedPersonal_id'] = $data['session']['medpersonal_id'];
			$params['LpuSection_id'] = $data['session']['CurLpuSection_id'];
			$params['pmUser_id'] = $data['pmUser_id'];
			foreach ($save_data['drug'] as $key => $obj) {
				$receptPanel = array();
				if(!empty($obj['ReceiptPanel'])){
					$receptPanel = $obj['ReceiptPanel'];
					unset($obj['ReceiptPanel']);
				}
				$drug = array_merge($obj, $params);
				if(!empty($drug['EvnCourseTreat_setDate'])) $drug['EvnCourseTreat_setDate'] = ConvertDateFormat($drug['EvnCourseTreat_setDate']);
				if(!empty($drug['EvnCourseTreat_disDate'])) $drug['EvnCourseTreat_disDate'] = ConvertDateFormat($drug['EvnCourseTreat_disDate']);
				$response = $this->EvnPrescrTreat_model->doSaveEvnCourseTreat($drug);
				if(is_array($response) && !empty($response[0]['EvnCourseTreat_id']) && !empty($response[0]['EvnCourseTreatDrug_id0_saved'])){
					if(count($receptPanel)>0){
						if (empty($receptPanel['EvnReceptGeneral_IsChronicDisease'])) $receptPanel['EvnReceptGeneral_IsChronicDisease'] = null;
						if (empty($receptPanel['EvnReceptGeneral_IsSpecNaz'])) $receptPanel['EvnReceptGeneral_IsSpecNaz'] = null;
						if (empty($receptPanel['ReceptUrgency_id'])) $receptPanel['ReceptUrgency_id'] = null;
						if (empty($receptPanel['EvnReceptGeneral_endDate'])) $receptPanel['EvnReceptGeneral_endDate'] = null;
						if (empty($receptPanel['EvnReceptGeneral_Validity'])) $receptPanel['EvnReceptGeneral_Validity'] = null;
						if (empty($receptPanel['EvnReceptGeneral_Period'])) $receptPanel['EvnReceptGeneral_Period'] = null;
						if (empty($receptPanel['ReceptValid_id'])) $receptPanel['ReceptValid_id'] = null;
						if (empty($receptPanel['EvnReceptGeneral_id'])) $receptPanel['EvnReceptGeneral_id'] = null;
						//$receptPanel['ReceptForm_id'] = 2;
						$recept = array_merge($receptPanel, $params);
						$recept['EvnCourseTreatDrug_id'] = $response[0]['EvnCourseTreatDrug_id0_saved'];
						$recept['EvnRecept_Ser'] = $receptPanel['EvnReceptGeneral_Ser'];
						$recept['EvnRecept_Num'] = $receptPanel['EvnReceptGeneral_Num'];
						$response = $this->Dlo_EvnRecept_model->saveEvnReceptGeneral($recept);
						//$response[0]['EvnReceptGeneral_id']; $response[0]['Error_Msg'];
						if(!empty($response[0]['Error_Msg'])) $err_arr['drug'][$key] = $response[0]['Error_Msg'];
						if(empty($response[0]['EvnReceptGeneral_id'])) $err_arr['drug'][$key] = 'ошибка при сохранении общего рецепта';
					}
				}else{
					if(!empty($response[0]['Error_Msg'])) {
						$err_arr['drug'][$key] = $response[0]['Error_Msg'];
					}else{
						$err_arr['drug'][$key] = 'Ошибка при назначение медикамента';
					}
					continue;
				}
			}
		}

		return array('success' => true, 'Error_Msg' => null, 'err_arr' => $err_arr);
	}

	/**
	 * Получение идентификаторов направлений, связанных с назначением
	 * @param array $data
	 * @return array
	 */
	function getEvnDirectionIds($data) {
		$params = array(
			'EvnPrescr_id' => $data['EvnPrescr_id'],
		);
		$query = "
			select
				EPD.EvnDirection_id as \"EvnDirection_id\"
			from
				v_EvnPrescrDirection EPD
			where
				EPD.EvnPrescr_id = :EvnPrescr_id
		";
		$EvnDirection_ids = $this->queryList($query, $params);
		if (!is_array($EvnDirection_ids)) {
			return $this->createError('','Ошибка при получении списка идентификаторов направлений');
		}
		return array(array(
			'success' => true,
			'EvnDirection_ids' => $EvnDirection_ids
		));
	}

	/**
	 *Получение дочернего назначения
	 */
	function getChildEvnPrescrId($data) {
		$query = "
			select
				e_child.Evn_id as \"Evn_id\"
			from
				v_EvnPrescr ep
				inner join v_Evn e on e.Evn_id = EvnPrescr_pid -- посещние/движение
				inner join v_Evn e_child on e_child.Evn_pid = e.Evn_pid -- посещения/движения той же КВС/ТАП
			where
				e_child.EvnClass_SysNick IN ('EvnSection', 'EvnVizitPL', 'EvnVizitPLStom')
				and EvnPrescr_id = :EvnPrescr_id
			limit 1
		";

		$id = $this->getFirstResultFromQuery($query, $data);

		return [
			'id' => $id
		];
	}

	/**
	 * Проверка наличия направления по назначению
	 */
	function checkEvnPrescr($data) {
		$resp_ed = $this->queryResult("
				select
					ed.EvnDirection_id as \"EvnDirection_id\"
				from
					v_EvnPrescrDirection epd
					inner join v_EvnDirection_all ed on ed.EvnDirection_id = epd.EvnDirection_id
				where
					epd.EvnPrescr_id = :EvnPrescr_id
					and coalesce(ed.EvnStatus_id, 16) not in (12, 13) -- не отменено/отклонено
				limit 1
			", $data);

		return $resp_ed;
	}

	/**
	 * Проверка наличия направления по назначению, создание связи, если её нет
	 */
	function checkAndDirectEvnPrescr($data) {
		$resp_ed = $this->checkEvnPrescr($data);
		if (!empty($resp_ed[0]['EvnDirection_id'])) {
			throw new Exception('По данному назначению уже создано направление', 500);
		}

		return $this->directEvnPrescr($data);
	}

	/**
	 * Получение даты назначения
	 */
	function getEvnPrescrInsDate($data)	{
		$res = $this->getFirstResultFromQuery("
			select
				to_char(ep.EvnPrescr_insDT, 'dd.mm.yyyy') as \"EvnPrescr_insDate\"
			from
				v_EvnPrescrDirection epd
				inner join v_EvnPrescr ep on ep.EvnPrescr_id = epd.EvnPrescr_id
			where
				epd.EvnDirection_id = :EvnDirection_id
			limit 1
		", $data);

		return [
			'EvnPrescr_insDate' => $res
		];
	}

	/**
	 * Определение назначения и случая к которому будет привязана услуга
	 */
	function defineUslugaParams($data) {
		$needRecalcKSGKPGKOEF = false;
		// определяем к чему будет привязана услуга, ищем назначение
		$filter = "";
		$queryParams = array(
			'EvnDirection_id' => $data['EvnDirection_id'],
			'UslugaComplex_id' => $data['UslugaComplex_id']
		);
		if (!empty($data['EvnPrescr_id'])) {
			$filter .= " and epd.EvnPrescr_id = :EvnPrescr_id";
			$queryParams['EvnPrescr_id'] = $data['EvnPrescr_id'];
		} else {
			$data['EvnPrescr_id'] = null;
		}
		$query = "
			select
				epd.EvnPrescr_id as \"EvnPrescr_id\",
				epld.EvnPrescrLabDiag_pid as \"EvnPrescrLabDiag_pid\",
				e.EvnClass_SysNick as \"EvnClass_SysNick\",
				case when epld.UslugaComplex_id = :UslugaComplex_id then 1 else 0 end as \"hasEvnPrescr\"
			from
				v_EvnPrescrDirection epd
				inner join v_EvnPrescrLabDiag epld on epd.EvnPrescr_id = epld.EvnPrescrLabDiag_id
				left join v_Evn e on e.Evn_id = epld.EvnPrescrLabDiag_pid
			where
				epd.EvnDirection_id = :EvnDirection_id
				{$filter}
			order by
				case when epld.UslugaComplex_id = :UslugaComplex_id then 1 else 0 end desc -- в первую очередь берём назначение по данной услуге, если нет, то любое назначение той же заявки.
			limit 1
		";
		$resp_ep = $this->queryResult($query, $queryParams);
		if (!empty($resp_ep[0]['EvnPrescr_id'])) {
			if ($resp_ep[0]['hasEvnPrescr'] == 1) {
				$data['EvnPrescr_id'] = $resp_ep[0]['EvnPrescr_id'];
			}
			// если въявную не указан родитель, то ищем к чему привязывать по назначению
			if (empty($data['EvnUslugaPar_pid']) && !empty($data['EvnUslugaPar_setDT'])) {
				if (getRegionNick() == 'perm') {
					$data['EvnUslugaPar_pid'] = $resp_ep[0]['EvnPrescrLabDiag_pid'];

					if ($resp_ep[0]['EvnClass_SysNick'] == 'EvnSection') {
						$needRecalcKSGKPGKOEF = true;
					}
				} else {
					$dt = ":EvnUslugaPar_setDT";
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
								inner join v_EvnPS eps on eps.EvnPS_id = es.EvnSection_pid and cast(coalesce(eps.EvnPS_disDT, (select dt from mv)) as {$checkDateType}) >= cast((select dt from mv) as {$checkDateType}) -- дата выписки больше
							";
							if ($resp_ep[0]['EvnClass_SysNick'] == 'EvnPS') {
								$joins = "
									inner join v_EvnPS eps on eps.EvnPS_id = ep.EvnPrescr_pid and cast(coalesce(eps.EvnPS_disDT, (select dt from mv)) as {$checkDateType}) >= cast((select dt from mv) as {$checkDateType}) -- дата выписки больше
								";
							}
							$query = "
								with mv as (
									{$dt} as dt
								)
								select
									coalesce(es_child.EvnSection_id, eps.EvnPS_id) as \"EvnUslugaPar_pid\",
									case when es_child.EvnSection_id is not null then 1 else 0 end as \"needRecalcKSGKPGKOEF\"
								from
									v_EvnPrescr ep
									{$joins}
									left join v_EvnSection es_child on es_child.LpuSection_id is not null and es_child.EvnSection_pid = eps.EvnPS_id and cast(es_child.EvnSection_setDT as {$checkDateType}) <= cast((select dt from mv) as {$checkDateType}) and (cast(es_child.EvnSection_disDT as {$checkDateType}) >= cast((select dt from mv) as {$checkDateType}) OR es_child.EvnSection_disDT IS NULL) -- актуальное движение той же КВС
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
								with mv as (
									{$dt} as dt
								)
								select
									ev.EvnVizitPL_id as \"EvnUslugaPar_pid\"
								from
									v_EvnPrescr ep
									inner join v_EvnVizitPL ev on ev.EvnVizitPL_id = ep.EvnPrescr_pid
									inner join v_EvnPL epl on epl.EvnPL_id = ev.EvnVizitPL_pid and cast(epl.EvnPL_setDT as {$checkDateType}) <= cast((select dt from mv) as {$checkDateType}) and (coalesce(epl.EvnPL_IsFinish, 1) = 1 OR cast(coalesce(epl.EvnPL_disDT, (select dt from mv)) as {$checkDateType}) >= cast((select dt from mv) as {$checkDateType})) -- дата конца случая больше
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
	 * Определение даты назначения по услуге
	 */
	function getSetDateByUslugaPar($data) {
		$res = $this->getFirstResultFromQuery("
			select
				to_char(ep.EvnPrescr_setDT, 'dd.mm.yyyy') as \"EvnPrescr_Date\"
			from
				v_EvnUslugaPar eup
				left join v_EvnPrescrDirection epd on epd.EvnDirection_id = eup.EvnDirection_id
				inner join v_EvnPrescr ep on ep.EvnPrescr_id = COALESCE(eup.EvnPrescr_id, epd.EvnPrescr_id)
			where
				eup.EvnUslugaPar_id = :EvnUslugaPar_id
		", $data
		);

		return [
			'EvnPrescr_Date' => $res
		];
	}

	/**
	 * Определение назначения по направлению
	 */
	function getPrescrByDirection($data) {
		$res = $this->getFirstResultFromQuery("
			select
				epd.EvnPrescr_id as \"EvnPrescr_id\"
			from
				v_EvnPrescrDirection epd
			where
				epd.EvnDirection_id = :EvnDirection_id
		", $data);

		return [
			'EvnPrescr_id' => $res
		];
	}

	/**
	 * @param array $data
	 * @return array|false
	 */
	function getPayTypeFromEvn($data) {
		$params = array(
			'EvnPrescr_id' => $data['EvnPrescr_id'],
		);
		$query = "
			select
				coalesce(ev.PayType_id, es.PayType_id) as \"PayType_id\"
			from
				v_EvnPrescr ep
				left join v_EvnVizit ev on ev.EvnVizit_id = ep.EvnPrescr_pid
				left join v_EvnSection es on es.EvnSection_id = ep.EvnPrescr_pid
			where
				ep.EvnPrescr_id = :EvnPrescr_id
			limit 1
		";
		return $this->queryResult($query, $params);
	}

	function getEvnData($data) {
		$evn = $this->getFirstRowFromQuery("
			select
				EvnClass_SysNick as \"EvnClass_SysNick\",
				PersonEvn_id as \"PersonEvn_id\",
				Server_id as \"Server_id\",
				to_char(Evn_setDT, 'dd.mm.yyyy') as \"Evn_setDT\"
			from v_Evn
			where Evn_id = :id
		", $data);

		if (isset($evn['Evn_setDT'])) {
			$evn['Evn_setDT'] = DateTime::createFromFormat('d.m.Y', $evn['Evn_setDT']);
		}

		return $evn;
	}
}
