<?php defined('BASEPATH') or die ('No direct script access allowed');

require_once(APPPATH.'models/_pgsql/EvnPrescr_model.php');

class Lis_EvnPrescr_model extends EvnPrescr_model {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->load->swapi('common');
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
				// Если инфы в постгрелис нет, то вдруг пришло с клиента
				if(empty($data['EvnDirection_id']) && empty($data['EvnStatus_id']) 
					&& empty($data['UslugaComplex_id']) && empty($data['EvnPrescr_IsExec'])){
					// ну тут уж наши полномочия всё
					throw new Exception('Назначение не найдено!', 400);
				}
			} else {
				$data['EvnCourse_id'] = $tmp[0]['EvnCourse_id'];
				$data['EvnDirection_id'] = $tmp[0]['EvnDirection_id'];
				$data['EvnStatus_id'] = $tmp[0]['EvnStatus_id'];
				$data['TimetableMedService_id'] = $tmp[0]['TimetableMedService_id'];
			}
			if ($data['EvnPrescr_IsExec'] == 2) {
				throw new Exception('Назначение выполнено и не может быть удалено!', 400);
			}
			if (!empty($data['PrescriptionStatusType_id']) && $data['PrescriptionStatusType_id'] == 3) {
				throw new Exception('Назначение отменено и не может быть удалено!', 400);
			}
			if (!empty($data['EvnDirection_id']) && !in_array($data['EvnStatus_id'], array(10, 16, 17))) {
				throw new Exception('Назначение не может быть отменено. Отменить можно, если направление имеет статус "Записано на бирку" или "В очереди"!', 400);
			}
			
			// Если назначение имеет направление, то нужно сначала отменить направление, но только в том случае если по направлению только 1 назначение.
			$needCancelDirection = true;
			if (!empty($data['EvnDirection_id'])) {
				$resp_ep = $this->queryResult("
					select
						count(ep.EvnPrescr_id) as \"cnt\"
					from
						EvnPrescrDirection epd
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
			if ($needCancelDirection && (!empty($data['couple']) && !$data['couple'])) {
				if (!empty($data['TimetableMedService_id'])) {
					throw new Exception($data['TimetableMedService_id'], 800);
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
						inner join EvnPrescrDirection epd on epld.EvnPrescrLabDiag_id = epd.EvnPrescr_id
					where
						epld.EvnPrescrLabDiag_id = :EvnPrescr_id
				", array(
					'EvnPrescr_id' => $data['EvnPrescr_id']
				));
				// из EvnUslugaPar убираем назначенную услугу
				if (!empty($resp_uc[0]['EvnDirection_id']) && !empty($resp_uc[0]['UslugaComplex_id'])) {
					//Если в постгрелис есть данные, удаляем по ним
					$this->load->model('EvnLabSample_model');
					$this->EvnLabSample_model->cancelResearch(array(
						'EvnDirection_id' => $resp_uc[0]['EvnDirection_id'],
						'UslugaComplex_id' => $resp_uc[0]['UslugaComplex_id'],
						'pmUser_id' => $data['pmUser_id']
					));
					$data['EvnDirection_id'] = $resp_uc[0]['EvnDirection_id'];
				} elseif(!empty($data['EvnDirection_id']) && !empty($data['UslugaComplex_id'])) {
					// Если в постгрелис нет данных, удаляем что пришло с клиента
					$this->load->model('EvnLabSample_model');
					$this->EvnLabSample_model->cancelResearch(array(
						'EvnDirection_id' => $data['EvnDirection_id'],
						'UslugaComplex_id' => $data['UslugaComplex_id'],
						'pmUser_id' => $data['pmUser_id']
					));
				}
			}
		} catch (Exception $e) {
			if (800 === $e->getCode()) {
				$response[0]['TimetableMedService_id'] = $e->getMessage();
				$response[0]['Error_Msg'] = 'Нужно сначала освободить запись!';
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
				coalesce(EP.EvnPrescr_IsExec, 1) as \"EvnPrescr_IsExec\"
			from
				v_EvnPrescr EP
				left join lateral(
					Select 
						epd.EvnDirection_id,
						ED.DirType_id,
						coalesce(ED.EvnStatus_id, 16) as EvnStatus_id
					from EvnPrescrDirection epd
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
			$EvnDirection = $this->common->GET('EvnDirection', array(
				'EvnDirection_id' => $result[0]['EvnDirection_id']
			), 'single');
			if (!$this->isSuccessful($EvnDirection)) {
				throw new Exception($EvnDirection['Error_Msg'], 500);
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
		$queryParams = array(
			'EvnPrescrDirection_id' => $data['EvnPrescrDirection_id'],
			'EvnDirection_id' => $data['EvnDirection_id'],
			'pmUser_id' => $data['pmUser_id'],
		);
		$result = $this->queryResult($query, $queryParams);
		if (!is_array($result)) {
			return $this->createError('','Ошибка при удалении связи назначения с направлением');
		}
		return $result;
	}
}
