<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * OperBlock_model - модель для работы с оперблоком
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      OperBlock
 * @access       public
 * @copyright    Copyright (c) 2020 Swan Ltd.
 * @author       Valery Bondarev
 * @version      06.01.2020
 */

class OperBlock_model extends swPgModel
{
	/**
	 *	Конструктор
	 */
    function __construct()
    {
        parent::__construct();
    }

	/**
	 * Сохранение участника операционной бригады
	 */
	function saveEvnUslugaOperBrig($data) {
		if (!empty($data['EvnUslugaOperBrig_id'])) {
			$proc = 'p_EvnUslugaOperBrig_upd';
		} else {
			$proc = 'p_EvnUslugaOperBrig_ins';
			$data['EvnUslugaOperBrig_id'] = null;
		}

		$query = "
			select
				EvnUslugaOperBrig_id as \"EvnUslugaOperBrig_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$proc}(
				EvnUslugaOperBrig_id := :EvnUslugaOperBrig_id,
				EvnUslugaOper_id := :EvnUslugaOper_id,
				MedPersonal_id := :MedPersonal_id,
				MedStaffFact_id := :MedStaffFact_id,
				SurgType_id := :SurgType_id,
				pmUser_id := :pmUser_id
			)
		";

		return $this->queryResult($query, $data);
	}

	/**
	 * Сохранение вида анестезии
	 */
	function saveEvnUslugaOperAnest($data) {
		if (!empty($data['EvnUslugaOperAnest_id'])) {
			$proc = 'p_EvnUslugaOperAnest_upd';
		} else {
			$proc = 'p_EvnUslugaOperAnest_ins';
			$data['EvnUslugaOperAnest_id'] = null;
		}

		$query = "
			select
				EvnUslugaOperAnest_id as \"EvnUslugaOperAnest_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$proc}(
				EvnUslugaOperAnest_id := :EvnUslugaOperAnest_id,
				EvnUslugaOper_id := :EvnUslugaOper_id,
				AnesthesiaClass_id := :AnesthesiaClass_id,
				pmUser_id := :pmUser_id
			)
		";

		return $this->queryResult($query, $data);
	}

	/**
	 * Сохранение планирования
	 */
	function saveEvnPrescrOperBlockPlanWindow($data) {
		$this->load->helper( 'Reg' );

		// 1. достаём EvnRequestOper_id, если нет создаём
		$query = "
			select
				ed.Lpu_id as \"Lpu_id\",
				ed.MedService_id as \"MedService_id\",
				ed.PersonEvn_id as \"PersonEvn_id\",
				ed.Server_id as \"Server_id\",
				ed.Person_id as \"Person_id\",
				ero.EvnRequestOper_id as \"EvnRequestOper_id\",
				ero.EvnRequestOper_isAnest as \"EvnRequestOper_isAnest\",
				ttr.TimetableResource_id as \"TimetableResource_id\",
				educ.EvnDirectionUslugaComplex_id as \"EvnDirectionUslugaComplex_id\",
				euo.EvnUslugaOper_id as \"EvnUslugaOper_id\",
				euo.EvnUslugaOper_setDT as \"EvnUslugaOper_setDT\",
				epd.EvnPrescr_id as \"EvnPrescr_id\",
				ed.EvnDirection_IsCito as \"EvnDirection_IsCito\"
			from
				v_EvnDirection_all ed
				left join v_EvnUslugaOper euo on euo.EvnDirection_id = ed.EvnDirection_id
				left join v_TimetableResource_lite ttr on ttr.EvnDirection_id = ed.EvnDirection_id
				left join v_EvnDirectionUslugaComplex educ on educ.EvnDirection_id = ed.EvnDirection_id
				left join v_EvnRequestOper ero on ed.EvnDirection_id = ero.EvnDirection_id
				left join v_EvnPrescrDirection epd on epd.EvnDirection_id = ed.EvnDirection_id
			where
				ed.EvnDirection_id = :EvnDirection_id
		";
		$resp = $this->queryResult($query, array(
			'EvnDirection_id' => $data['EvnDirection_id']
		));

		if (empty($resp[0])) {
			return array('Error_Msg' => 'Ошибка получения информации по направлению');
		}

		if (!empty($resp[0]['EvnUslugaOper_setDT'])) {
			return array('Error_Msg' => 'Нельзя перепланировать операцию, т.к. она уже выполнена');
		}

		if (!empty($resp[0]['EvnRequestOper_id'])) {
			$data['EvnRequestOper_id'] = $resp[0]['EvnRequestOper_id'];
			if($data['EvnRequestOper_isAnest'] != $resp[0]['EvnRequestOper_isAnest']){
				$resp_save = $this->saveEvnRequestOper(array(
					'EvnRequestOper_id' => $data['EvnRequestOper_id'],
					'EvnDirection_id' => $data['EvnDirection_id'],
					'EvnRequestOper_isAnest' => $data['EvnRequestOper_isAnest'],
					'Lpu_id' => $resp[0]['Lpu_id'],
					'MedService_id' => $resp[0]['MedService_id'],
					'PersonEvn_id' => $resp[0]['PersonEvn_id'],
					'Server_id' => $resp[0]['Server_id'],
					'pmUser_id' => $data['pmUser_id']
				));
			}
		} else {
			$resp_save = $this->saveEvnRequestOper(array(
				'EvnDirection_id' => $data['EvnDirection_id'],
				'EvnRequestOper_isAnest' => $data['EvnRequestOper_isAnest'],
				'Lpu_id' => $resp[0]['Lpu_id'],
				'MedService_id' => $resp[0]['MedService_id'],
				'PersonEvn_id' => $resp[0]['PersonEvn_id'],
				'Server_id' => $resp[0]['Server_id'],
				'pmUser_id' => $data['pmUser_id']
			));

			if (!empty($resp_save[0]['EvnRequestOper_id'])) {
				$data['EvnRequestOper_id'] = $resp_save[0]['EvnRequestOper_id'];
			}
		}

		if (empty($data['EvnRequestOper_id'])) {
			return array('Error_Msg' => 'Ошибка получения идентификатора заявки');
		}

		if (!empty($resp[0]['EvnUslugaOper_id'])) {
			$data['EvnUslugaOper_id'] = $resp[0]['EvnUslugaOper_id'];
			// обновляем лишь услугу, т.к. она могла измениться
			$this->db->query("
				update EvnUsluga
				set UslugaComplex_id = :UslugaComplex_id
				where Evn_id = :EvnUsluga_id
			", array(
				'EvnUsluga_id' => $resp[0]['EvnUslugaOper_id'],
				'UslugaComplex_id' => $data['UslugaComplex_id']
			));
		} else {
			$resp_save = $this->saveEvnUslugaOper(array(
				'EvnDirection_id' => $data['EvnDirection_id'],
				'Lpu_id' => $data['Lpu_id'],
				'EvnUslugaOper_setDT' => null,
				'EvnUslugaOper_disDT' => null,
				'OperType_id' => ($resp[0]['EvnDirection_IsCito'] == 2)?2:1,
				'UslugaComplex_id' => $data['UslugaComplex_id'],
				'EvnPrescr_id' => $resp[0]['EvnPrescr_id'],
				'PayType_id' => $this->getFirstResultFromQuery("
					select
						PayType_id as \"PayType_id\"
					from v_PayType
					where PayType_SysNick = :PayType_SysNick
					limit 1
				", [
					'PayType_SysNick' => getPayTypeSysNickOMS()
				]),
				'PersonEvn_id' => $resp[0]['PersonEvn_id'],
				'Server_id' => $resp[0]['Server_id'],
				'pmUser_id' => $data['pmUser_id']
			));

			if (!empty($resp_save[0]['EvnUslugaOper_id'])) {
				$data['EvnUslugaOper_id'] = $resp_save[0]['EvnUslugaOper_id'];
			}
		}

		if (empty($data['EvnUslugaOper_id'])) {
			return array('Error_Msg' => 'Ошибка получения идентификатора услуги');
		}

		// обновляем услугу в EvnDirectionUslugaComplex
		$this->load->model('EvnDirection_model');
		$resp_save = $this->EvnDirection_model->saveEvnDirectionUslugaComplex(array(
			'EvnDirectionUslugaComplex_id' => $resp[0]['EvnDirectionUslugaComplex_id'],
			'EvnDirection_id' => $data['EvnDirection_id'],
			'UslugaComplex_id' => $data['UslugaComplex_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		// сохраняем TimetableResource
		if (!empty($data['TimetableResource_begTime'])) {
			$data['TimetableResource_begTime'] = $data['TimetableResource_begDate'] . ' ' . $data['TimetableResource_begTime'];
		} else {
			$data['TimetableResource_begTime'] = $data['TimetableResource_begDate'];
		}

		$data['Timetable_Day'] = TimeToDay( strtotime($data['TimetableResource_begTime']) );

		if (!empty($data['TimetableResource_Time'])) {
			// надо привести к виду в минутах
			$time = mb_split(':', $data['TimetableResource_Time']);
			if (count($time) > 1) {
				$data['TimetableResource_Time'] = $time[0] * 60 + $time[1];
			} else {
				$data['TimetableResource_Time'] = $time[0];
			}
		}

		$resp_save = $this->saveTimetableResource(array(
			'TimetableResource_id' => $resp[0]['TimetableResource_id'],
			'TimetableResource_begTime' => $data['TimetableResource_begTime'],
			'TimetableResource_Time' => $data['TimetableResource_Time'],
			'EvnDirection_id' => $data['EvnDirection_id'],
			'Resource_id' => $data['Resource_id'],
			'Person_id' => $resp[0]['Person_id'],
			'Timetable_Day' => $data['Timetable_Day'],
			'pmUser_id' => $data['pmUser_id']
		));

		// 2. сохраняем операционную бригаду
		// 2.1. удаляем существующие
		$query = "
			select
				EvnUslugaOperBrig_id as \"EvnUslugaOperBrig_id\"
			from
				v_EvnUslugaOperBrig
			where
				EvnUslugaOper_id = :EvnUslugaOper_id
		";
		$resp_query = $this->queryResult($query, array(
			'EvnUslugaOper_id' => $data['EvnUslugaOper_id']
		));
		foreach($resp_query as $respone) {
			$query = "
				select
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from p_EvnUslugaOperBrig_del(
					EvnUslugaOperBrig_id := :EvnUslugaOperBrig_id
				)
			";
			$this->queryResult($query, array(
				'EvnUslugaOperBrig_id' => $respone['EvnUslugaOperBrig_id']
			));
		}

		foreach($data['BrigDataJson'] as $onebrig) {
			$resp_save = $this->saveEvnUslugaOperBrig(array(
				'MedPersonal_id' => $this->getFirstResultFromQuery("
					select MedPersonal_id as \"MedPersonal_id\"
					from v_MedStaffFact
					where MedStaffFact_id = :MedStaffFact_id
				", array('MedStaffFact_id' => $onebrig['MedStaffFact_id'])),
				'MedStaffFact_id' => $onebrig['MedStaffFact_id'],
				'SurgType_id' => $onebrig['SurgType_id'],
				'EvnUslugaOper_id' => $data['EvnUslugaOper_id'],
				'pmUser_id' => $data['pmUser_id']
			));
		}

		// 3. сохраняем виды анастезий
		// 3.1. удаляем существующие
		$query = "
			select
				EvnUslugaOperAnest_id as \"EvnUslugaOperAnest_id\"
			from
				v_EvnUslugaOperAnest
			where
				EvnUslugaOper_id = :EvnUslugaOper_id
		";
		$resp_query = $this->queryResult($query, array(
			'EvnUslugaOper_id' => $data['EvnUslugaOper_id']
		));
		foreach($resp_query as $respone) {
			$query = "
				select
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from p_EvnUslugaOperAnest_del(
					EvnUslugaOperAnest_id := :EvnUslugaOperAnest_id
				)
			";
			$this->queryResult($query, array(
				'EvnUslugaOperAnest_id' => $respone['EvnUslugaOperAnest_id']
			));
		}

		foreach($data['AnestDataJson'] as $oneanest) {
			$resp_save = $this->saveEvnUslugaOperAnest(array(
				'AnesthesiaClass_id' => $oneanest['AnesthesiaClass_id'],
				'EvnUslugaOper_id' => $data['EvnUslugaOper_id'],
				'pmUser_id' => $data['pmUser_id']
			));
		}

		return array('Error_Msg' => '');
	}

	/**
	 * Сохранение услуги
	 */
	function saveEvnUslugaOper($data) {
		if (!empty($data['EvnUslugaOper_id'])) {
			$proc = 'p_EvnUslugaOper_upd';
		} else {
			$proc = 'p_EvnUslugaOper_ins';
			$data['EvnUslugaOper_id'] = null;
		}

		$query = "
			select
				EvnUslugaOper_id as \"EvnUslugaOper_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$proc}(
				EvnUslugaOper_id := :EvnUslugaOper_id,
				EvnUslugaOper_pid := null,
				OperType_id := :OperType_id,
				TreatmentConditionsType_id := 2, -- стационарно
				EvnDirection_id := :EvnDirection_id,
				EvnUslugaOper_Kolvo := 1,
				Lpu_id := :Lpu_id,
				EvnPrescr_id := :EvnPrescr_id,
				EvnUslugaOper_setDT := :EvnUslugaOper_setDT,
				EvnUslugaOper_disDT := :EvnUslugaOper_disDT,
				UslugaComplex_id := :UslugaComplex_id,
				PayType_id := :PayType_id,
				PersonEvn_id := :PersonEvn_id,
				Server_id := :Server_id,
				pmUser_id := :pmUser_id
			)
		";

		return $this->queryResult($query, $data);
	}

	/**
	 * Сохранение заявки
	 */
	function saveEvnRequestOper($data) {
		if (!empty($data['EvnRequestOper_id'])) {
			$proc = 'p_EvnRequestOper_upd';
		} else {
			$proc = 'p_EvnRequestOper_ins';
			$data['EvnRequestOper_id'] = null;
		}

		$query = "
			select
				EvnRequestOper_id as \"EvnRequestOper_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$proc}(
				EvnRequestOper_id := :EvnRequestOper_id,
				EvnDirection_id := :EvnDirection_id,
				EvnRequestOper_isAnest := :EvnRequestOper_isAnest,
				Lpu_id := :Lpu_id,
				MedService_id := :MedService_id,
				PersonEvn_id := :PersonEvn_id,
				Server_id := :Server_id,
				pmUser_id := :pmUser_id
			)
		";

		return $this->queryResult($query, $data);
	}

	/**
	 * Правка конфликтов по столу
	 */
	function getIntersectedResources($data) {
		if (!empty($data['start']) && !empty($data['end']) ) {
			$begTime = $data['start'];
			$endTime = $data['end'];
		} else {
			if (!empty($data['TimetableResource_begTime'])) {
				$data['TimetableResource_begTime'] = $data['TimetableResource_begDate'] . ' ' . $data['TimetableResource_begTime'];
			} else {
				$data['TimetableResource_begTime'] = $data['TimetableResource_begDate'];
			}
			$data['Timetable_Day'] = strtotime($data['TimetableResource_begTime']);

			if (!empty($data['TimetableResource_Time'])) {
				// надо привести к виду в минутах
				$time = mb_split(':', $data['TimetableResource_Time']);
				if (count($time) > 1) {
					$data['TimetableResource_Time'] = $time[0] * 60 + $time[1];
				} else {
					$data['TimetableResource_Time'] = $time[0];
				}
			}
			if (empty($data['TimetableResource_Time'])) {
				$data['TimetableResource_Time'] = 30;
			}

			if ($data['TimetableResource_Time'] < 10) {
				$data['TimetableResource_Time'] = 10;
			}
			$begTime = $data['TimetableResource_begTime'];
			$endTime = date('Y-m-d H:i:s', strtotime($data['TimetableResource_begTime']) + $data['TimetableResource_Time'] * 60);
		}

		$queryParams = array(
			'Resource_id' => $data['Resource_id'],
			'TimetableResource_id' => $data['TimetableResource_id'],
			'begTime' => $begTime,
			'endTime' => $endTime
		);
		$filter_timetable = "";
		if ( !empty($data['TimetableResource_id']) ) {
			$queryParams['TimetableResource_id'] = $data['TimetableResource_id'];
			$filter_timetable .= " and ttr.TimetableResource_id <> :TimetableResource_id";
		} else if ( !empty($data['EvnDirection_id']) ) {
			$filter_timetable .= " and ttr.EvnDirection_id <> ".$data['EvnDirection_id'];
		}
		$filter_confl = "";
		$BrigData = $data['BrigDataJson'];
		if (!empty($BrigData)) {
			foreach($BrigData as $brig) {
				if (!empty($filter_confl)) {
					$filter_confl .= " or ";
				}
				$filter_confl .= "euob1.MedStaffFact_id = ".$brig['MedStaffFact_id'];
			}
			$filter_confl = "and ( {$filter_confl} )";
		}
		$query_brig = "
			SELECT
				ttr.TimetableResource_id as \"TimetableResource_id\",
				ttr.EvnDirection_id as \"EvnDirection_id\",
				ttr.Resource_id as \"Resource_id\",
				euob1.MedStaffFact_id as \"MedStaffFact_id\",
				r.Resource_name as \"Resource_name\",
				ttr.TimetableResource_begTime as \"TimetableResource_begTime\",
				ttr.TimetableResource_Time as \"TimetableResource_Time\",
				IsNull(msf.Person_SurName, '') as \"MedPersonal_SurName\",
				IsNull(msf.Person_FirName, '') as \"MedPersonal_FirName\",
				IsNull(msf.Person_SecName, '') as \"MedPersonal_SecName\",
				st.SurgType_Code as \"SurgType_Code\",
				st.SurgType_Name as \"SurgType_Name\"
			FROM
				v_TimetableResource_lite ttr
			INNER JOIN 
				v_Resource r on r.Resource_id = ttr.Resource_id
			INNER JOIN
				v_EvnDirection_all ed on ed.EvnDirection_id = ttr.EvnDirection_id
			INNER JOIN
				v_EvnUslugaOper euo on euo.EvnDirection_id = ed.EvnDirection_id
			left JOIN
				v_EvnUslugaOperBrig euob1 on euob1.EvnUslugaOper_id = euo.EvnUslugaOper_id
				{$filter_confl}
			left JOIN v_MedStaffFact msf on msf.MedStaffFact_id = euob1.MedStaffFact_id
			left  join v_SurgType st on st.SurgType_id = euob1.SurgType_id
			WHERE
				(( ttr.TimetableResource_begTime < :endTime
				and ttr.TimetableResource_begTime >= :begTime )
				or
				( DATEADD('MINUTE', ttr.TimetableResource_Time, ttr.TimetableResource_begTime) > :begTime
				and DATEADD('MINUTE', ttr.TimetableResource_Time, ttr.TimetableResource_begTime) <= :endTime ))
				{$filter_timetable}
		";
		// $_REQUEST['sql_debug'] = 1;
		$resp_confl = $this->queryResult($query_brig, $queryParams);

		return array(
			'data' => $resp_confl,
			'success' => true
		);
	}

	/**
	 * Правка конфликтов по столу
	 */
	function correctTimetableResourceConflicts($data) {
		// проверка конфликтов
		$begTime = $data['TimetableResource_begTime'];
		$endTime = date('Y-m-d H:i:s', strtotime($data['TimetableResource_begTime']) + $data['TimetableResource_Time'] * 60);
		$queryParams = array(
			'Resource_id' => $data['Resource_id'],
			'begTime' => $begTime,
			'endTime' => $endTime
		);
		$filter_confl = "";
		if (!empty($data['TimetableResource_id'])) {
			$queryParams['TimetableResource_id'] = $data['TimetableResource_id'];
			$filter_confl .= " and ttr.TimetableResource_id <> :TimetableResource_id";
		}
		$query = "
			select
				ttr.TimetableResource_id as \"TimetableResource_id\",
				to_char(ttr.TimetableResource_begTime, 'yyyy-mm-dd hh24:mi:ss') as \"TimetableResource_begTime\",
				coalesce(ttr.TimetableResource_Time, 30) as \"TimetableResource_Time\",
				ttr.TimetableResource_Day as \"Timetable_Day\",
				ES.EvnStatus_SysNick as \"EvnStatus_SysNick\"
			from
				v_TimetableResource_lite ttr
				inner join v_EvnDirection_all ED on ed.EvnDirection_id = ttr.EvnDirection_id
				left join v_EvnStatus ES on ES.EvnStatus_id = ED.EvnStatus_id
			where
				ttr.Resource_id = :Resource_id
				and (
					(ttr.TimetableResource_begTime >= :begTime and ttr.TimetableResource_begTime < :endTime)
					or
					(DATEADD('MINUTE', ttr.TimetableResource_Time, ttr.TimetableResource_begTime) > :begTime and DATEADD('MINUTE', coalesce(ttr.TimetableResource_Time, 30), ttr.TimetableResource_begTime) <= :endTime)
				)
				{$filter_confl}
		";
		// $_REQUEST['sql_debug'] = 1;
		$resp_confl = $this->queryResult($query, $queryParams);

		if (empty($data['wasConflicts'])) {
			$data['wasConflicts'] = false;
		}

		foreach($resp_confl as $one_confl) {
			if (!empty($one_confl['TimetableResource_id'])) {
				// был произведен передел метода по этой задаче https://jira.is-mis.ru/browse/PROMEDWEB-11101
				
				$data['TimetableResource_begTime'] = date('Y-m-d H:i:s', strtotime($one_confl['TimetableResource_begTime']) + $one_confl['TimetableResource_Time'] * 60);
				$data['Timetable_Day'] = TimeToDay(strtotime($data['TimetableResource_begTime']));
				$data['wasConflicts'] = true;
				if (!empty($data['TimetableResource_id'])) {
					$this->db->query("
						update
							TimetableResource
						set
							TimetableResource_begTime = :TimetableResource_begTime,
							TimetableResource_Day = :TimetableResource_Day
						where
							TimetableResource_id = :TimetableResource_id
					", array(
						'TimetableResource_begTime' => $data['TimetableResource_begTime'],
						'TimetableResource_Day' => $data['Timetable_Day'],
						'TimetableResource_id' => $data['TimetableResource_id']
					));
				}
				// т.к. данную операцию сместили, то надо её ещё раз проверить на конфликты
				return $this->correctTimetableResourceConflicts($data);
			}
		}

		return array(
			'wasConflicts' => $data['wasConflicts'],
			'TimetableResource_begTime' => $data['TimetableResource_begTime'],
			'Timetable_Day' => $data['Timetable_Day']
		);
	}

	/**
	 * Сохранение записи
	 */
	function saveTimetableResource($data) {
		if (!empty($data['TimetableResource_id'])) {
			$proc = 'p_TimetableResource_upd';
		} else {
			$proc = 'p_TimetableResource_ins';
			$data['TimetableResource_id'] = null;
		}

		$needUpdate = false; // надо ли обновить область АРМ (надо, если решались конфликты)

		if (empty($data['TimetableResource_Time'])) {
			$data['TimetableResource_Time'] = 30;
		}

		if ($data['TimetableResource_Time'] < 10) {
			$data['TimetableResource_Time'] = 10;
		}

		$resp = $this->correctTimetableResourceConflicts(array(
			'Resource_id' => $data['Resource_id'],
			'TimetableResource_id' => $data['TimetableResource_id'],
			'TimetableResource_begTime' => $data['TimetableResource_begTime'],
			'TimetableResource_Time' => $data['TimetableResource_Time'],
			'Timetable_Day' => $data['Timetable_Day']
		));
		if (!empty($resp['wasConflicts'])) {
			$needUpdate = true;
			$data['TimetableResource_begTime'] = $resp['TimetableResource_begTime'];
			$data['Timetable_Day'] = $resp['Timetable_Day'];
		}

		$query = "
			select
				TimetableResource_id as \"TimetableResource_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$proc}(
				TimetableResource_id := :TimetableResource_id,
				Person_id := :Person_id,
				Resource_id := :Resource_id,
				TimetableResource_begTime := :TimetableResource_begTime,
				EvnDirection_id := :EvnDirection_id,
				TimetableResource_Day := :Timetable_Day,
				TimetableResource_Time := :TimetableResource_Time,
				RecClass_id := 3,
				RecMethodType_id := 1,
				pmUser_id := :pmUser_id
			)
		";

		$resp = $this->queryResult($query, $data);

		// т.к. записали на бирку, то статус должен поменяться
		$this->setEvnDirectionStatus(array(
			'EvnDirection_id' => $data['EvnDirection_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		if (!empty($resp[0])) {
			$resp[0]['needUpdate'] = $needUpdate;
		}

		return $resp;
	}

	/**
	 * Удаление записи
	 */
	function destroyCalendarEvents($data) {
		$query = "
			select
				tr.TimetableResource_id as \"TimetableResource_id\",
				euo.EvnUslugaOper_setDT as \"EvnUslugaOper_setDT\",
				euo.EvnUslugaOper_id as \"EvnUslugaOper_id\"
			from
				v_TimetableResource_lite tr
				left join v_EvnUslugaOper euo on euo.EvnDirection_id = tr.EvnDirection_id
			where
				tr.EvnDirection_id = :EvnDirection_id
		";

		$resp = $this->queryResult($query, array(
			'EvnDirection_id' => $data['EvnDirection_id']
		));

		// Если операция выполнена - отменяем выполнение
		if (!empty($resp[0]['EvnUslugaOper_setDT'])) {
			return $this->cancelEvnUslugaOper(array_merge($data, $resp[0]));
		}

		$result = array('Error_Msg' => '');
		foreach($resp as $respone) {
			$query = "
				select
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from p_TimetableResource_del(
					TimetableResource_id := :TimetableResource_id,
					pmUser_id := :pmUser_id
				)
			";
			$result = $this->queryResult($query, array(
				'TimetableResource_id' => $respone['TimetableResource_id'],
				'pmUser_id' => $data['pmUser_id']
			));
		}

		// т.к. удалили бирку, то статус должен поменяться
		$this->setEvnDirectionStatus(array(
			'EvnDirection_id' => $data['EvnDirection_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		if (is_array($result)) {
			return $result;
		}

		return false;
	}
	
	/**
	 * Отмена выполения операции
	 */
	function cancelEvnUslugaOper($data) {

		$query = "
			select
				EvnClass_id as \"EvnClass_id\",
				EvnClass_Name as \"EvnClass_Name\",
				EvnUslugaOper_id as \"EvnUslugaOper_id\",
				EvnUslugaOper_setDate as \"EvnUslugaOper_setDate\",
				EvnUslugaOper_setTime as \"EvnUslugaOper_setTime\",
				EvnUslugaOper_didDate as \"EvnUslugaOper_didDate\",
				EvnUslugaOper_didTime as \"EvnUslugaOper_didTime\",
				EvnUslugaOper_disDate as \"EvnUslugaOper_disDate\",
				EvnUslugaOper_disTime as \"EvnUslugaOper_disTime\",
				EvnUslugaOper_pid as \"EvnUslugaOper_pid\",
				EvnUslugaOper_rid as \"EvnUslugaOper_rid\",
				Lpu_id as \"Lpu_id\",
				Server_id as \"Server_id\",
				PersonEvn_id as \"PersonEvn_id\",
				EvnUslugaOper_setDT as \"EvnUslugaOper_setDT\",
				EvnUslugaOper_disDT as \"EvnUslugaOper_disDT\",
				EvnUslugaOper_didDT as \"EvnUslugaOper_didDT\",
				EvnUslugaOper_insDT as \"EvnUslugaOper_insDT\",
				EvnUslugaOper_updDT as \"EvnUslugaOper_updDT\",
				EvnUslugaOper_Index as \"EvnUslugaOper_Index\",
				EvnUslugaOper_Count as \"EvnUslugaOper_Count\",
				pmUser_insID as \"pmUser_insID\",
				pmUser_updID as \"pmUser_updID\",
				Person_id as \"Person_id\",
				Morbus_id as \"Morbus_id\",
				EvnUslugaOper_IsSigned as \"EvnUslugaOper_IsSigned\",
				pmUser_signID as \"pmUser_signID\",
				EvnUslugaOper_signDT as \"EvnUslugaOper_signDT\",
				EvnUslugaOper_IsArchive as \"EvnUslugaOper_IsArchive\",
				EvnUslugaOper_Guid as \"EvnUslugaOper_Guid\",
				EvnUslugaOper_IndexMinusOne as \"EvnUslugaOper_IndexMinusOne\",
				EvnStatus_id as \"EvnStatus_id\",
				EvnUslugaOper_statusDate as \"EvnUslugaOper_statusDate\",
				EvnUslugaOper_IsTransit as \"EvnUslugaOper_IsTransit\",
				PayType_id as \"PayType_id\",
				Usluga_id as \"Usluga_id\",
				MedPersonal_id as \"MedPersonal_id\",
				UslugaPlace_id as \"UslugaPlace_id\",
				Lpu_uid as \"Lpu_uid\",
				LpuSection_uid as \"LpuSection_uid\",
				EvnUslugaOper_Kolvo as \"EvnUslugaOper_Kolvo\",
				Org_uid as \"Org_uid\",
				UslugaComplex_id as \"UslugaComplex_id\",
				EvnUslugaOper_isCito as \"EvnUslugaOper_isCito\",
				MedPersonal_sid as \"MedPersonal_sid\",
				EvnUslugaOper_Result as \"EvnUslugaOper_Result\",
				EvnDirection_id as \"EvnDirection_id\",
				UslugaComplexTariff_id as \"UslugaComplexTariff_id\",
				EvnUslugaOper_CoeffTariff as \"EvnUslugaOper_CoeffTariff\",
				MesOperType_id as \"MesOperType_id\",
				EvnUslugaOper_IsModern as \"EvnUslugaOper_IsModern\",
				EvnUslugaOper_Price as \"EvnUslugaOper_Price\",
				EvnUslugaOper_Summa as \"EvnUslugaOper_Summa\",
				EvnPrescr_id as \"EvnPrescr_id\",
				EvnPrescrTimeTable_id as \"EvnPrescrTimeTable_id\",
				EvnCourse_id as \"EvnCourse_id\",
				EvnUslugaOper_IsVizitCode as \"EvnUslugaOper_IsVizitCode\",
				EvnUslugaOper_IsInReg as \"EvnUslugaOper_IsInReg\",
				EvnUslugaOper_IsPaid as \"EvnUslugaOper_IsPaid\",
				MedStaffFact_id as \"MedStaffFact_id\",
				MedSpecOms_id as \"MedSpecOms_id\",
				LpuSectionProfile_id as \"LpuSectionProfile_id\",
				EvnUslugaOper_MedPersonalCode as \"EvnUslugaOper_MedPersonalCode\",
				EvnUslugaOper_IndexRep as \"EvnUslugaOper_IndexRep\",
				EvnUslugaOper_IndexRepInReg as \"EvnUslugaOper_IndexRepInReg\",
				DiagSetClass_id as \"DiagSetClass_id\",
				Diag_id as \"Diag_id\",
				LpuDispContract_id as \"LpuDispContract_id\",
				EvnUslugaOper_IsMinusUsluga as \"EvnUslugaOper_IsMinusUsluga\",
				Mes_id as \"Mes_id\",
				UslugaExecutionReason_id as \"UslugaExecutionReason_id\",
				UslugaExecutionType_id as \"UslugaExecutionType_id\",
				Registry_sid as \"Registry_sid\",
				EvnUslugaOper_IsAPP as \"EvnUslugaOper_IsAPP\",
				EvnUslugaOper_IsEndoskop as \"EvnUslugaOper_IsEndoskop\",
				EvnUslugaOper_IsLazer as \"EvnUslugaOper_IsLazer\",
				EvnUslugaOper_IsKriogen as \"EvnUslugaOper_IsKriogen\",
				AnesthesiaClass_id as \"AnesthesiaClass_id\",
				OperType_id as \"OperType_id\",
				OperDiff_id as \"OperDiff_id\",
				TreatmentConditionsType_id as \"TreatmentConditionsType_id\",
				EvnUslugaOper_IsVMT as \"EvnUslugaOper_IsVMT\",
				EvnUslugaOper_IsMicrSurg as \"EvnUslugaOper_IsMicrSurg\",
				EvnUslugaOper_IsOpenHeart as \"EvnUslugaOper_IsOpenHeart\",
				EvnUslugaOper_IsArtCirc as \"EvnUslugaOper_IsArtCirc\",
				EvnUslugaOper_BallonBegDT as \"EvnUslugaOper_BallonBegDT\",
				EvnUslugaOper_CKVEndDT as \"EvnUslugaOper_CKVEndDT\",
				EvnUslugaOper_IsOperationDeath as \"EvnUslugaOper_IsOperationDeath\",
				EvnUslugaOper_IsRadGraf as \"EvnUslugaOper_IsRadGraf\"
			from v_EvnUslugaOper
			where EvnUslugaOper_id = :EvnUslugaOper_id
		";
		$resp = $this->queryResult($query, array(
			'EvnUslugaOper_id' => $data['EvnUslugaOper_id']
		));

		$query = "
			select
				EvnUslugaOper_id as \"EvnUslugaOper_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_EvnUslugaOper_upd(
				EvnUslugaOper_id := :EvnUslugaOper_id,
				EvnUslugaOper_pid := :EvnUslugaOper_pid,
				EvnDirection_id := :EvnDirection_id,
				Lpu_id := :Lpu_id,
				Server_id := :Server_id,
				PersonEvn_id := :PersonEvn_id,
				EvnUslugaOper_setDT := :EvnUslugaOper_setDT,
				EvnUslugaOper_disDT := :EvnUslugaOper_disDT,
				PayType_id := :PayType_id,
				EvnUslugaOper_IsVMT := :EvnUslugaOper_IsVMT,
				EvnUslugaOper_IsMicrSurg := :EvnUslugaOper_IsMicrSurg,
				EvnUslugaOper_IsOpenHeart := :EvnUslugaOper_IsOpenHeart,
				EvnUslugaOper_IsArtCirc := :EvnUslugaOper_IsArtCirc,
				Usluga_id := :Usluga_id,
				UslugaComplex_id := :UslugaComplex_id,
				MedPersonal_id := :MedPersonal_id,
				MedStaffFact_id := :MedStaffFact_id,
				Morbus_id := :Morbus_id,
				UslugaPlace_id := :UslugaPlace_id,
				Lpu_uid := :Lpu_uid,
				LpuSection_uid := :LpuSection_uid,
				Org_uid := :Org_uid,
				EvnUslugaOper_Kolvo := :EvnUslugaOper_Kolvo,
				EvnUslugaOper_IsEndoskop := :EvnUslugaOper_IsEndoskop,
				EvnUslugaOper_IsLazer := :EvnUslugaOper_IsLazer,
				EvnUslugaOper_IsKriogen := :EvnUslugaOper_IsKriogen,
				EvnUslugaOper_IsRadGraf := :EvnUslugaOper_IsRadGraf,
				OperType_id := :OperType_id,
				OperDiff_id := :OperDiff_id,
				TreatmentConditionsType_id := :TreatmentConditionsType_id,
				EvnUslugaOper_CoeffTariff := :EvnUslugaOper_CoeffTariff,
				EvnUslugaOper_IsModern := :EvnUslugaOper_IsModern,
				MesOperType_id := :MesOperType_id,
				UslugaComplexTariff_id := :UslugaComplexTariff_id,
				DiagSetClass_id := :DiagSetClass_id,
				Diag_id := :Diag_id,
				EvnPrescrTimetable_id := null,
				EvnPrescr_id := :EvnPrescr_id,
				MedSpecOms_id := :MedSpecOms_id,
				LpuSectionProfile_id := :LpuSectionProfile_id,
				EvnUslugaOper_BallonBegDT := :EvnUslugaOper_BallonBegDT,
				EvnUslugaOper_CKVEndDT := :EvnUslugaOper_CKVEndDT,
				EvnUslugaOper_IsOperationDeath := :EvnUslugaOper_IsOperationDeath,
				pmUser_id := :pmUser_id
			)
		";

		$queryParams = array(
			'EvnUslugaOper_id' => $resp[0]['EvnUslugaOper_id'],
			'EvnUslugaOper_pid' => null,
			'EvnDirection_id' => $resp[0]['EvnDirection_id'],
			'EvnUslugaOper_IsVMT' => $resp[0]['EvnUslugaOper_IsVMT'],
			'EvnUslugaOper_IsMicrSurg' => $resp[0]['EvnUslugaOper_IsMicrSurg'],
			'EvnUslugaOper_IsOpenHeart' => $resp[0]['EvnUslugaOper_IsOpenHeart'],
			'EvnUslugaOper_IsArtCirc' => $resp[0]['EvnUslugaOper_IsArtCirc'],
			'Lpu_id' => $resp[0]['Lpu_id'],
			'Server_id' => $resp[0]['Server_id'],
			'PersonEvn_id' => $resp[0]['PersonEvn_id'],
			'EvnUslugaOper_setDT' => null,
			'EvnUslugaOper_disDT' => null,
			'PayType_id' => $resp[0]['PayType_id'],
			'Usluga_id' => (!empty($resp[0]['Usluga_id']) ? $resp[0]['Usluga_id'] : NULL),
			'UslugaComplex_id' => (!empty($resp[0]['UslugaComplex_id']) ? $resp[0]['UslugaComplex_id'] : NULL),
			'MedPersonal_id' => null,
			'MedStaffFact_id' => null,
			'UslugaPlace_id' => null,
			'Lpu_uid' => $resp[0]['Lpu_uid'],
			'LpuSection_uid' => null,
			'Org_uid' => $resp[0]['Org_uid'],
			'EvnUslugaOper_Kolvo' => $resp[0]['EvnUslugaOper_Kolvo'],
			'EvnUslugaOper_IsEndoskop' => null,
			'EvnUslugaOper_IsLazer' => null,
			'EvnUslugaOper_IsKriogen' => null,
			'EvnUslugaOper_IsRadGraf' => null,
			'OperType_id' => $resp[0]['OperType_id'],
			'Morbus_id' => $resp[0]['Morbus_id'],
			'OperDiff_id' => null,
			'TreatmentConditionsType_id' => $resp[0]['TreatmentConditionsType_id'],
			'EvnUslugaOper_CoeffTariff' => (!empty($resp[0]['EvnUslugaOper_CoeffTariff']) ? $resp[0]['EvnUslugaOper_CoeffTariff'] : NULL),
			'EvnUslugaOper_IsModern' => (!empty($resp[0]['EvnUslugaOper_IsModern']) ? $resp[0]['EvnUslugaOper_IsModern'] : NULL),
			'MesOperType_id' => (!empty($resp[0]['MesOperType_id']) ? $resp[0]['MesOperType_id'] : NULL),
			'UslugaComplexTariff_id' => (!empty($resp[0]['UslugaComplexTariff_id']) ? $resp[0]['UslugaComplexTariff_id'] : NULL),
			'DiagSetClass_id' => (!empty($resp[0]['DiagSetClass_id']) ? $resp[0]['DiagSetClass_id'] : NULL),
			'Diag_id' => (!empty($resp[0]['Diag_id']) ? $resp[0]['Diag_id'] : NULL),
			'EvnPrescr_id' => (!empty($resp[0]['EvnPrescr_id']) ? $resp[0]['EvnPrescr_id'] : NULL),
			'MedSpecOms_id' => (!empty($resp[0]['MedSpecOms_id']) ? $resp[0]['MedSpecOms_id'] : NULL),
			'LpuSectionProfile_id' => (!empty($resp[0]['LpuSectionProfile_id']) ? $resp[0]['LpuSectionProfile_id'] : NULL),
			'EvnUslugaOper_BallonBegDT' => null,
			'EvnUslugaOper_CKVEndDT' => null,
			'EvnUslugaOper_IsOperationDeath' => null,
			'pmUser_id' => $data['pmUser_id']
		);

		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			throw new Exception('Не удалось выполнить обновление оперативной услуги');
		}

		// Возвращаем статус направлению
		$this->setEvnDirectionStatus(array(
			'EvnDirection_id' => $resp[0]['EvnDirection_id'],
			'pmUser_id' => $data['pmUser_id']
		));
		
		// Снимаем признак выполнения назначения
		$this->load->model('EvnPrescr_model', 'EvnPrescr_model');
		$this->EvnPrescr_model->saveEvnPrescrIsExec(array(
			'pmUser_id' => $data['pmUser_id'],
			'EvnDirection_id' => $resp[0]['EvnDirection_id'],
			'EvnPrescr_IsExec' => 1
		));
		
		return $result->result('array');
	}

	/**
	 * Проставление правильного статуса направлению
	 */
	function setEvnDirectionStatus($data) {
		$this->load->model('EvnDirectionAll_model');
		$EvnStatus_SysNick = EvnDirectionAll_model::EVN_STATUS_DIRECTION_IN_QUEUE;

		if (!empty($data['EvnDirection_id'])) {
			// достаём данные услуги и бирки
			$resp = $this->queryResult("
				select
					ed.EvnDirection_id as \"EvnDirection_id\",
					es.EvnStatus_SysNick as \"EvnStatus_SysNick\",
					euo.EvnUslugaOper_id as \"EvnUslugaOper_id\",
					tr.TimetableResource_id as \"TimetableResource_id\"
				from
					v_EvnDirection_all ed
					left join v_EvnStatus es on es.EvnStatus_id = ed.EvnStatus_id
					left join lateral(
						select
							euo.EvnUslugaOper_id
						from
							v_EvnUslugaOper euo
						where
							euo.EvnDirection_id = ed.EvnDirection_id
							and euo.EvnUslugaOper_setDT is not null -- выполнена
						limit 1
					) euo on true
					left join lateral(
						select
							tr.TimetableResource_id
						from
							v_TimetableResource_lite tr
						where
							tr.EvnDirection_id = ed.EvnDirection_id
						limit 1
					) tr on true
				where
					EvnDirection_id = :EvnDirection_id
				limit 1
			", array(
				'EvnDirection_id' => $data['EvnDirection_id']
			));

			if (!empty($resp[0]['EvnDirection_id'])) {
				if (!empty($resp[0]['EvnUslugaOper_id'])) {
					// есть выполненная услуга
					$EvnStatus_SysNick = EvnDirectionAll_model::EVN_STATUS_DIRECTION_SERVICED;
				} else if (!empty($resp[0]['TimetableResource_id'])) {
					// записано на бирку
					$EvnStatus_SysNick = EvnDirectionAll_model::EVN_STATUS_DIRECTION_RECORDED;
				}

				if ($EvnStatus_SysNick != $resp[0]['EvnStatus_SysNick']) {
					$this->EvnDirectionAll_model->setStatus(array(
						'Evn_id' => $data['EvnDirection_id'],
						'EvnStatus_SysNick' => $EvnStatus_SysNick,
						'EvnClass_id' => $this->EvnDirectionAll_model->evnClassId,
						'pmUser_id' => $data['pmUser_id']
					));
				}
			}
		}
	}

	/**
	 * Сохранение записи
	 */
	function updateCalendarEvents($data) {
		$this->load->helper( 'Reg' );

		$data['TimetableResource_Time'] = round((strtotime($data['end']) - strtotime($data['start']))/60);

		// получаем необходимые данные по направлению
		$query = "
			select
				ed.Person_id as \"Person_id\",
				ttr.TimetableResource_id as \"TimetableResource_id\",
				euo.EvnUslugaOper_id as \"EvnUslugaOper_id\",
				euo.EvnUslugaOper_setDT as \"EvnUslugaOper_setDT\",
				educ.UslugaComplex_id as \"UslugaComplex_id\",
				ed.PersonEvn_id as \"PersonEvn_id\",
				ed.Server_id as \"Server_id\",
				epd.EvnPrescr_id as \"EvnPrescr_id\",
				ed.EvnDirection_IsCito as \"EvnDirection_IsCito\"
			from
				v_EvnDirection_all ed
				left join v_TimetableResource_lite ttr on ttr.EvnDirection_id = ed.EvnDirection_id
				left join v_EvnUslugaOper euo on euo.EvnDirection_id = ed.EvnDirection_id
				left join v_EvnDirectionUslugaComplex educ on educ.EvnDirection_id = ed.EvnDirection_id
				left join v_EvnPrescrDirection epd on epd.EvnDirection_id = ed.EvnDirection_id
			where
				ed.EvnDirection_id = :EvnDirection_id
		";
		$result = $this->queryResult($query, array(
			'EvnDirection_id' => $data['EvnDirection_id']
		));

		if (is_array($result) && !empty($result[0]['Person_id'])) {
			if (!empty($result[0]['EvnUslugaOper_setDT'])) {
				return array('Error_Msg' => 'Нельзя перепланировать операцию, т.к. она уже выполнена');
			}

			if (empty($result[0]['EvnUslugaOper_id'])) { // если услуга ещё не сохранена
				$resp_save = $this->saveEvnUslugaOper(array(
					'EvnDirection_id' => $data['EvnDirection_id'],
					'Lpu_id' => $data['Lpu_id'],
					'EvnUslugaOper_setDT' => null,
					'EvnUslugaOper_disDT' => null,
					'OperType_id' => ($result[0]['EvnDirection_IsCito'] == 2)?2:1,
					'UslugaComplex_id' => $result[0]['UslugaComplex_id'],
					'EvnPrescr_id' => $result[0]['EvnPrescr_id'],
					'PayType_id' => $this->getFirstResultFromQuery("select PayType_id from v_PayType where PayType_SysNick = :PayType_SysNick limit 1", array(
						'PayType_SysNick' => getPayTypeSysNickOMS()
					)),
					'PersonEvn_id' => $result[0]['PersonEvn_id'],
					'Server_id' => $result[0]['Server_id'],
					'pmUser_id' => $data['pmUser_id']
				));
			}

			// если услуга не может выполняться на ресурсе, выдаём ошибку
			if (!empty($result[0]['UslugaComplex_id'])) {
				$resp_ucr = $this->queryResult("
					select
						ucr.UslugaComplexResource_id as \"UslugaComplexResource_id\"
					from
						v_UslugaComplexResource ucr 
						inner join v_UslugaComplexMedService ucms on ucms.UslugaComplexMedService_id = ucr.UslugaComplexMedService_id
					where
						ucr.Resource_id = :Resource_id
						and ucms.UslugaComplex_id = :UslugaComplex_id
					limit 1
				", array(
					'UslugaComplex_id' => $result[0]['UslugaComplex_id'],
					'Resource_id' => $data['Resource_id']
				));

				if (empty($resp_ucr[0]['UslugaComplexResource_id'])) {
					return array('Error_Msg' => 'Услуга не может быть выполнена на данном ресурсе');
				}
			}

			$data['Timetable_Day'] = empty( $data['Timetable_Day'] ) ? TimeToDay( strtotime($data['start']) ) : $data['Timetable_Day'];

			$result = $this->saveTimetableResource(array(
				'TimetableResource_id' => $result[0]['TimetableResource_id'],
				'TimetableResource_begTime' => $data['start'],
				'TimetableResource_Time' => $data['TimetableResource_Time'],
				'EvnDirection_id' => $data['EvnDirection_id'],
				'Resource_id' => $data['Resource_id'],
				'Person_id' => $result[0]['Person_id'],
				'Timetable_Day' => $data['Timetable_Day'],
				'pmUser_id' => $data['pmUser_id']
			));

			if (is_array($result)) {
				return $result;
			}
		}

		return false;
	}

	/**
	 *  Получение главного списка АРМ
	 */
	function loadMainGrid($data) {
		$queryParams = array(
			'MedService_id' => $data['MedService_id'],
			'onDate' => $data['onDate']
		);

		$globalFilters = "1=1";
		$filters = "";
		if ($data['type'] == 'operplan') {
			// только планируемые
			$filters .= " and ES.EvnStatus_SysNick <> 'Serviced'";
			$planfilter = "and cast(ttr.TimetableResource_begTime as date) = :onDate -- запланированные на текущую дату";
		} else {
			// только выполненные
			$globalFilters .= "and cast(euo.EvnUslugaOper_setDT as date) = :onDate -- выполненные в текущую дату";
			$filters .= " and ES.EvnStatus_SysNick = 'Serviced'";
			$planfilter = "and cast(euo.EvnUslugaOper_setDT as date) = :onDate -- выполненные в текущую дату";
		}

		// отменённые/отклонённые не нужны
		$filters .= " and ES.EvnStatus_SysNick <> 'Canceled'";
		$filters .= " and ES.EvnStatus_SysNick <> 'Declined'";

		// если не заведующий, то только заявки в которых участвует
		if (!havingGroup('operblock_head')) {
			$globalFilters .= "
				and exists (
					select
						euob.EvnUslugaOperBrig_id
					from
						v_EvnUslugaOperBrig euob
					where
						euob.EvnUslugaOper_id = euo.EvnUslugaOper_id
						and euob.MedPersonal_id = :MedPersonal_id
					limit 1
				)
			";
			$queryParams['MedPersonal_id'] = $data['session']['medpersonal_id'];
		}

		$query = "
			-- фильтруем
			with EvnDirection as (
				select
					ED.EvnDirection_id,
					ED.EvnDirection_IsCito,
					ED.EvnDirection_desDT,
					ED.EvnDirection_setDate,
					ttr.TimetableResource_id,
					ttr.TimetableResource_begTime,
					1 as IsPlanned,
					ED.Diag_id,
					ED.EvnDirection_pid,
					ED.Person_id,
					ttr.Resource_id
				from
					v_EvnDirection_all ED
					inner join v_EvnStatus ES on ES.EvnStatus_id = coalesce(ED.EvnStatus_id, 1)
					left join v_TimetableResource_lite ttr on ttr.EvnDirection_id = ED.EvnDirection_id
				where
					ED.MedService_id = :MedService_id
					and ttr.TimetableResource_id is null
					and coalesce(ed.EvnDirection_desDT, CAST(:onDate as date)) <= :onDate -- в очереди, желаемая дата меньше выбранной
					{$filters}

				union all

				select
					ED.EvnDirection_id,
					ED.EvnDirection_IsCito,
					ED.EvnDirection_desDT,
					ED.EvnDirection_setDate,
					ttr.TimetableResource_id,
					ttr.TimetableResource_begTime,
					2 as IsPlanned,
					ED.Diag_id,
					ED.EvnDirection_pid,
					ED.Person_id,
					ttr.Resource_id
				from
					v_EvnDirection_all ED
					inner join v_EvnStatus ES on ES.EvnStatus_id = coalesce(ED.EvnStatus_id, 1)
					inner join v_TimetableResource_lite ttr on ttr.EvnDirection_id = ED.EvnDirection_id
					left join v_EvnUslugaOper euo on euo.EvnDirection_id = ED.EvnDirection_id
				where
					ED.MedService_id = :MedService_id
					{$planfilter}
					{$filters}
			)

			-- получаем данные
			select
				EvnDirection.EvnDirection_id as \"EvnDirection_id\",
				es.EvnSection_id as \"EvnSection_id\",
				es.EvnSection_pid as \"EvnSection_pid\",
				evpl.EvnVizitPL_id as \"EvnVizitPL_id\",
				evpl.EvnVizitPL_pid as \"EvnVizitPL_pid\",
				EvnDirection.TimetableResource_id as \"TimetableResource_id\",
				EvnDirection.IsPlanned as \"IsPlanned\",
				to_char(EvnDirection.TimetableResource_begTime, 'hh24:mi') as \"TimetableResource_begTime\",
				EvnDirection.EvnDirection_IsCito as \"EvnDirection_IsCito\",
				to_char(EvnDirection.TimetableResource_begTime, 'dd.mm.yyyy') as \"TimetableResource_begDate\",
				EvnDirection.Diag_id as \"Diag_id\",
				D.Diag_Code || ' ' || D.Diag_Name as \"Diag_Name\",
				COALESCE(ps.Person_SurName, '') || COALESCE(' '|| SUBSTRING(ps.Person_FirName,1,1) || '.','') || COALESCE(' ' || SUBSTRING(ps.Person_SecName,1,1) || '.','') as \"Person_Fio\",
				dbo.Age2(PS.Person_BirthDay, dbo.tzGetDate()) as \"Person_Age\",
				PS.Person_id as \"Person_id\",
				PS.Server_id as \"Server_id\",
				PS.PersonEvn_id as \"PersonEvn_id\",
				PS.Sex_id as \"Sex_id\",
				ls.LpuSection_Name as \"LpuSection_Name\",
				YNAnest.YesNo_Name as \"isAnest\", 
				to_char(EvnDirection.EvnDirection_setDate, 'dd.mm.yyyy') as \"EvnDirection_setDate\",
				educ.UslugaComplex_id as \"UslugaComplex_id\",
				uc.UslugaComplex_Name as \"UslugaComplex_Name\",
				msf.MedStaffFact_id as \"MedStaffFact_id\",
				msf.MedPersonal_id as \"MedPersonal_id\",
				msf.LpuSection_id as \"LpuSection_id\",
				COALESCE(msf.Person_SurName, '') || COALESCE(' ' || SUBSTRING(msf.Person_FirName,1,1) || '.','') || COALESCE(' ' || SUBSTRING(msf.Person_SecName,1,1) || '.','') as \"MedPersonal_Fio\",
				to_char(EvnDirection.EvnDirection_desDT, 'dd.mm.yyyy') as \"EvnDirection_desDT\",
				euo.EvnUslugaOper_id as \"EvnUslugaOper_id\",
				to_char(euo.EvnUslugaOper_setDT, 'dd.mm.yyyy') as \"EvnUslugaOper_setDT\",
				R.Resource_Name as \"Resource_Name\",
				EDO.EvnXml_id as \"EvnXml_id\",
				EX.EvnXml_id as \"UslugaEvnXml_id\",
				Brig.Person_Fin as \"EvnRequestOperBrig\",
				case when PAR.PersonAllergicReaction_id is null then 1 else 2 end as \"IsAllerg\"
			from
				EvnDirection
				inner join v_PersonState PS on PS.Person_id = EvnDirection.Person_id
				left join v_EvnUslugaOper euo on euo.EvnDirection_id = EvnDirection.EvnDirection_id
				left join EvnDirectionOper EDO on EDO.Evn_id = EvnDirection.EvnDirection_id
				left join v_EvnRequestOper ero on ero.EvnDirection_id = EvnDirection.EvnDirection_id
				left join v_YesNo YNAnest on YNAnest.YesNo_id = coalesce(ero.EvnRequestOper_IsAnest, 1)
				left join v_Resource r on EvnDirection.Resource_id = r.Resource_id
				left join v_EvnDirectionUslugaComplex educ on educ.EvnDirection_id = EvnDirection.EvnDirection_id
				left join v_UslugaComplex uc on uc.UslugaComplex_id = educ.UslugaComplex_id
				left join v_EvnSection es on es.EvnSection_id = EvnDirection.EvnDirection_pid
				left join v_EvnVizitPL evpl on evpl.EvnVizitPL_id = EvnDirection.EvnDirection_pid
				left join v_LpuSection ls on ls.LpuSection_id = coalesce(es.LpuSection_id, evpl.LpuSection_id)
				left join v_MedStaffFact msf on msf.MedStaffFact_id = coalesce(es.MedStaffFact_id, evpl.MedStaffFact_id)
				left join v_Diag D on D.Diag_id = EvnDirection.Diag_id
				-- хак, чтобы не было задваений при использовании двух типов протоколов одновременно #
				left join lateral (
					select
						*
					from
						v_EvnXml E
					where E.Evn_id = euo.EvnUslugaOper_id
					limit 1
				) as EX on true
				left join lateral (
					select
						PersonAllergicReaction_id
					from
						v_PersonAllergicReaction
					where
						Person_id = PS.Person_id
					limit 1
				) PAR on true
				left join lateral (
					select
						MP.Person_Fin
					from
						v_EvnUslugaOperBrig euob
						left join v_MedPersonal MP on MP.MedPersonal_id = euob.MedPersonal_id
					where
						euob.EvnUslugaOper_id = euo.EvnUslugaOper_id
						and euob.SurgType_id = 1 -- хирург
					limit 1
				) Brig on true
			where
				{$globalFilters}
		";

		$result = $this->db->query($query, $queryParams);
		if (is_object($result)) {
			return $result->result('array');
		}

		return false;
	}

	/**
	 *  Получение данных формы планирования
	 */
	function loadEvnPrescrOperBlockPlanWindow($data) {
		$query = "
			select
				ed.EvnDirection_id as \"EvnDirection_id\",
				ed.MedService_id as \"MedService_id\",
				COALESCE(PS.Person_SurName, '') || COALESCE(' ' || PS.Person_FirName, '') || COALESCE(' ' || PS.Person_SecName, '') as \"Person_Fio\",
				educ.UslugaComplex_id as \"UslugaComplex_id\",
				tr.Resource_id as \"Resource_id\",
				to_char(coalesce(tr.TimetableResource_begTime, dbo.tzGetDate()), 'dd.mm.yyyy') as \"TimetableResource_begDate\",
				to_char(tr.TimetableResource_begTime, 'hh24:mi') as \"TimetableResource_begTime\",
				tr.TimetableResource_Time as \"TimetableResource_Time\",
				euo.EvnUslugaOper_id as \"EvnUslugaOper_id\",
				case when (ero.EvnRequestOper_isAnest = '2') then 'true' else 'false' end as \"EvnRequestOper_isAnest\",
				tr.TimetableResource_id as \"TimetableResource_id\"
			from
				v_EvnDirection_all ed
				left join v_EvnUslugaOper euo on euo.EvnDirection_id = ed.EvnDirection_id
				left join v_EvnRequestOper ero on ed.EvnDirection_id = ero.EvnDirection_id
				left join v_PersonState ps on ps.Person_id = ed.Person_id
				left join v_EvnDirectionUslugaComplex educ on educ.EvnDirection_id = ed.EvnDirection_id
				left join v_TimetableResource_lite tr on tr.EvnDirection_id = ed.EvnDirection_id
			where
				ed.EvnDirection_id = :EvnDirection_id
		";

		$resp = $this->queryResult($query, array(
			'EvnDirection_id' => $data['EvnDirection_id']
		));

		if (!empty($resp[0])) {
			if (!empty($resp[0]['TimetableResource_Time'])) {
				// надо привести к виду hh:mm
				$time = $resp[0]['TimetableResource_Time'];
				$hours = str_pad(floor($time/60), 2, '0', STR_PAD_LEFT);
				$minutes = str_pad($time%60, 2, '0', STR_PAD_LEFT);
				$resp[0]['TimetableResource_Time'] = $hours . ':' .$minutes;
			}
			// гризим операционную бригаду
			$query = "
				select
					EvnUslugaOperBrig_id as \"EvnUslugaOperBrig_id\",
					MedStaffFact_id as \"MedStaffFact_id\",
					SurgType_id as \"SurgType_id\"
				from
					v_EvnUslugaOperBrig
				where
					EvnUslugaOper_id = :EvnUslugaOper_id
			";

			$resp_brig = $this->queryResult($query, array(
				'EvnUslugaOper_id' => $resp[0]['EvnUslugaOper_id']
			));

			$resp[0]['BrigData'] = array();
			foreach ($resp_brig as $respone) {
				$resp[0]['BrigData'][] = array(
					'MedStaffFact_id' => $respone['MedStaffFact_id'],
					'SurgType_id' => $respone['SurgType_id']
				);
			}

			// грузим виды анастезий
			$query = "
				select
					EvnUslugaOperAnest_id as \"EvnUslugaOperAnest_id\",
					AnesthesiaClass_id as \"AnesthesiaClass_id\"
				from
					v_EvnUslugaOperAnest
				where
					EvnUslugaOper_id = :EvnUslugaOper_id
			";

			$resp_anest = $this->queryResult($query, array(
				'EvnUslugaOper_id' => $resp[0]['EvnUslugaOper_id']
			));

			$resp[0]['AnestData'] = array();
			foreach ($resp_anest as $respone) {
				$resp[0]['AnestData'][] = array(
					'AnesthesiaClass_id' => $respone['AnesthesiaClass_id']
				);
			}
		}

		return $resp;
	}

	/**
	 * Получение календарей
	 */
	function getCalendars($data) {
		$query = "
			select
				Res.Resource_id as \"Resource_id\",
				Res.Resource_Name as \"Resource_Name\"
			from
				v_Resource Res
			where
				Res.MedService_id = :MedService_id
		";

		$resp = $this->queryResult($query, $data);
		$k = 0;
		foreach($resp as &$respone) {
			$k++;
			$respone['color'] = $k%6+1; // диазайнер задал всего 6 цветов

			// для каждого надо подтянуть список доступных услуг
			$resp_uc = $this->queryResult("
				select
					ucms.UslugaComplex_id as \"UslugaComplex_id\"
				from
					v_UslugaComplexResource ucr
					inner join v_UslugaComplexMedService ucms on ucms.UslugaComplexMedService_id = ucr.UslugaComplexMedService_id
				where
					ucr.Resource_id = :Resource_id
			", array(
				'Resource_id' => $respone['Resource_id']
			));

			$respone['UslugaComplex_ids'] = array(); // список доступных услуг ресурса
			foreach($resp_uc as $respone_uc) {
				$respone['UslugaComplex_ids'][] = $respone_uc['UslugaComplex_id'];
			}
		}

		return $resp;
	}

	/**
	 * Сохранение экстренной заявки
	 */
	function createUrgentRequest($data) {
		// записываем человека "к себе" в службу в очередь
		$this->load->model('EvnDirection_model');

		$data['From_MedStaffFact_id'] = null;
		// попробуем найти рабочее место врача
		$resp = $this->queryResult("
			select
				MedStaffFact_id as \"MedStaffFact_id\"
			from
				v_MedStaffFact
			where
				MedPersonal_id = :MedPersonal_id
				and LpuSection_id = :LpuSection_id
				and WorkData_begDate <= dbo.tzGetDate()
				and (WorkData_endDate >= dbo.tzGetDate() OR WorkData_endDate IS NULL)
		", array(
			'LpuSection_id' => $data['LpuSection_id'], // направившее отделение
			'MedPersonal_id' => $data['MedPersonal_id'] // направивший врач
		));

		if (!empty($resp[0]['MedStaffFact_id'])) {
			$data['From_MedStaffFact_id'] = $resp[0]['MedStaffFact_id'];
		}

		$data['EvnDirection_pid'] = null;
		$data['Diag_id'] = null;
		// получаем данные об открытом движении в данной МО
		$resp = $this->queryResult("
			select
				es.EvnSection_id as \"EvnSection_id\",
				es.Diag_id as \"Diag_id\"
			from
				v_EvnSection es
			where
				es.Lpu_id = :Lpu_id
				and es.Person_id = :Person_id
				and es.LeaveType_id is null
				and coalesce(es.EvnSection_IsPriem, 1) = 1
			order by
				EvnSection_setDT desc
			limit 1
		", array(
			'Person_id' => $data['Person_id'],
			'Lpu_id' => $data['Lpu_id']
		));
		if (!empty($resp[0]['EvnSection_id'])) {
			$data['EvnDirection_pid'] = $resp[0]['EvnSection_id'];
			$data['Diag_id'] = $resp[0]['Diag_id'];
		}

		return $this->EvnDirection_model->saveEvnDirection(array(
			'toQueue' => true, // в очередь
			'EvnDirection_id' => null, // новое
			'EvnDirection_pid' => $data['EvnDirection_pid'],
			'Diag_id' => $data['Diag_id'],
			'EvnDirection_Num' => '0', // сгенерится
			'EvnDirection_Descr' => null,
			'LpuSection_did' => $data['LpuSection_id'], // куда направили
			'LpuSection_id' => $data['LpuSection_id'], // направившее отделение
			'MedPersonal_id' => $data['MedPersonal_id'], // направивший врач
			'MedPersonal_zid' => null,
			'EvnDirection_IsCito' => 2,
			'EvnDirection_setDT' => date('Y-m-d H:i:s'),
			'EvnDirection_setDate' => date('Y-m-d H:i:s'),
			'Person_id' => $data['Person_id'],
			'PersonEvn_id' => $data['PersonEvn_id'],
			'Server_id' => $data['Server_id'],
			'MedService_id' => $data['MedService_id'],
			'DirType_id' => 20, // в опер.блок
			'LpuSectionProfile_id' => null,
			'Lpu_id'  => $data['Lpu_id'],
			'Lpu_did' => $data['Lpu_id'],
			'Lpu_sid' => $data['Lpu_id'],
			'From_MedStaffFact_id' => $data['From_MedStaffFact_id'],
			'pmUser_id' => $data['pmUser_id']
		));
	}

	/**
	 * Получение событий календарей
	 */
	function getCalendarEvents($data) {
		$queryParams = array(
			'MedService_id' => $data['MedService_id'],
			'onDate' => $data['onDate']
		);

		// если не заведующий, то только заявки в которых участвует
		$filters = "";
		if (!havingGroup('operblock_head')) {
			$filters .= "
				and exists (
					select
						euob.EvnUslugaOperBrig_id
					from
						v_EvnUslugaOperBrig euob
					where
						euob.EvnUslugaOper_id = euo.EvnUslugaOper_id
						and euob.MedPersonal_id = :MedPersonal_id
					limit 1
				)
			";
			$queryParams['MedPersonal_id'] = $data['session']['medpersonal_id'];
		}

		// отменённые/отклонённые не нужны
		$filters .= " and ES.EvnStatus_SysNick <> 'Canceled'";
		$filters .= " and ES.EvnStatus_SysNick <> 'Declined'";

		$query = "
			select
				ttr.TimetableResource_id as \"TimetableResource_id\",
				ttr.Resource_id as \"Resource_id\",
				to_char(ttr.TimetableResource_begTime, 'dd.mm.yyyy hh24:mi:ss') as \"TimetableResource_begTime\",
				ttr.TimetableResource_Time as \"TimetableResource_Time\",
				ttr.EvnDirection_id as \"EvnDirection_id\",
				ps.Person_SurName as \"Person_SurName\",
				ps.Person_FirName as \"Person_FirName\",
				ps.Person_SecName as \"Person_SecName\",
				ps.Sex_id as \"Sex_id\",
				uc.UslugaComplex_Name as \"UslugaComplex_Name\",
				dbo.Age2(PS.Person_BirthDay, dbo.tzGetDate()) as \"Person_Age\",
				euo.EvnUslugaOper_id as \"EvnUslugaOper_id\",
				to_char(euo.EvnUslugaOper_setDT, 'dd.mm.yyyy') as \"EvnUslugaOper_setDT\",
				euo.UslugaComplex_id as \"UslugaComplex_id\"
			from
				v_TimetableResource_lite ttr
				inner join v_Resource r on r.Resource_id = ttr.Resource_id
				inner join v_EvnDirection_all ed on ed.EvnDirection_id = ttr.EvnDirection_id
				inner join v_EvnStatus ES on ES.EvnStatus_id = ED.EvnStatus_id
				left join v_EvnUslugaOper euo on euo.EvnDirection_id = ed.EvnDirection_id
				left join v_EvnDirectionUslugaComplex educ on educ.EvnDirection_id = ed.EvnDirection_id
				left join v_UslugaComplex uc on uc.UslugaComplex_id = educ.UslugaComplex_id
				left join v_PersonState ps on ps.Person_id = ed.Person_id
			where
				r.MedService_id = :MedService_id
				and (
					cast(ttr.TimetableResource_begTime as date) = :onDate -- либо началась сегодня
					or cast(DATEADD('MINUTE', ttr.TimetableResource_Time, ttr.TimetableResource_begTime) as date) = :onDate -- либо закончилась сегодня
				)
				{$filters}
		";

		$resp = $this->queryResult($query, $queryParams);

		$out = array();
		foreach($resp as $respone) {
			$minutes = 30;
			if (!empty($respone['TimetableResource_Time'])) {
				$minutes = $respone['TimetableResource_Time'];
			}

			$TimetableResource_begTime = DateTime::createFromFormat('d.m.Y H:i:s', $respone['TimetableResource_begTime']);

			$startdate = $TimetableResource_begTime->format('Y-m-d H:i:s');
			$enddate = $TimetableResource_begTime->add(new DateInterval('PT'.$minutes.'M'))->format('Y-m-d H:i:s');


			$Person_Fio = mb_ucfirst($respone['Person_SurName']);
			if (!empty($respone['Person_FirName'])) {
				$Person_Fio .= ' '.mb_substr($respone['Person_FirName'], 0, 1).'.';
			}
			if (!empty($respone['Person_SecName'])) {
				$Person_Fio .= ' '.mb_substr($respone['Person_SecName'],0,1).'.';
			}

			if (!empty($respone['Person_Age'])) {
				$Person_Age = $respone['Person_Age'] . ru_word_case(' год', ' года', ' лет', $respone['Person_Age']);
			} else {
				$Person_Age = '';
			}
			$Person_Sex = '';
			switch($respone['Sex_id']) {
				case 1:
					$Person_Sex = 'М';
					break;
				case 2:
					$Person_Sex = 'Ж';
					break;
			}

			$operbrigdata = array();
			// получаем операционную бригаду
			$query = "
				select
					msf.MedStaffFact_id as \"MedStaffFact_id\",
					msf.MedPersonal_id as \"MedPersonal_id\",
					euob.EvnUslugaOperBrig_id as \"EvnUslugaOperBrig_id\",
					coalesce(msf.Person_SurName, '') as \"MedPersonal_SurName\",
					coalesce(msf.Person_FirName, '') as \"MedPersonal_FirName\",
					coalesce(msf.Person_SecName, '') as \"MedPersonal_SecName\",
					st.SurgType_Code as \"SurgType_Code\",
					st.SurgType_Name as \"SurgType_Name\",
					CASE WHEN CMF.hasConflict IS NOT NULL then 1 else 0 end as \"hasConflict\"
				from
					v_EvnUslugaOperBrig euob
					left join v_MedStaffFact msf on msf.MedStaffFact_id = euob.MedStaffFact_id
					left join v_SurgType st on st.SurgType_id = euob.SurgType_id
					left join lateral(
						SELECT
							ttr.TimetableResource_id as hasConflict
						FROM
							v_TimetableResource_lite ttr
						INNER JOIN 
							v_Resource r on r.Resource_id = ttr.Resource_id
						INNER JOIN
							v_EvnDirection_all ed on ed.EvnDirection_id = ttr.EvnDirection_id
						INNER JOIN
							v_EvnUslugaOper euo on euo.EvnDirection_id = ed.EvnDirection_id
						INNER JOIN
							v_EvnUslugaOperBrig euob1 on euob1.EvnUslugaOper_id = euo.EvnUslugaOper_id
								and euob1.MedPersonal_id = msf.MedPersonal_id
						WHERE
							r.MedService_id <> :MedService_id
							and ttr.TimetableResource_begTime < :endDate
							and DATEADD('MINUTE', ttr.TimetableResource_Time, ttr.TimetableResource_begTime) > :startDate
							and ED.EvnStatus_id not in (12, 13)
						LIMIT 1
					) CMF on true
				where
					euob.EvnUslugaOper_id = :EvnUslugaOper_id
			";

			$resp_brig = $this->queryResult($query, array(
				'EvnUslugaOper_id' => $respone['EvnUslugaOper_id'],
				'MedService_id' => $queryParams['MedService_id'],
				'startDate' => $startdate,
				'endDate' => $enddate
			));

			foreach($resp_brig as $brigone) {
				$brigone['MedPersonal_ShortFio'] = mb_convert_case($brigone['MedPersonal_SurName'], MB_CASE_TITLE);
				if (!empty($brigone['MedPersonal_FirName'])) {
					$brigone['MedPersonal_ShortFio'] .= ' '.mb_substr($brigone['MedPersonal_FirName'], 0,1).'.';
				}
				if (!empty($brigone['MedPersonal_SecName'])) {
					$brigone['MedPersonal_ShortFio'] .= ' '.mb_substr($brigone['MedPersonal_SecName'], 0,1).'.';
				}

				$operbrigdata[] = array(
					'MedStaffFact_id' => $brigone['MedStaffFact_id'],
					'MedPersonal_id' => $brigone['MedPersonal_id'],
					'MedPersonal_ShortFio' => $brigone['MedPersonal_ShortFio'],
					'SurgType_Code' => $brigone['SurgType_Code'],
					'SurgType_Name' => $brigone['SurgType_Name'],
					'hasConflict' => $brigone['hasConflict']
				);
			}

			$pretitle = '';
			if (!empty($respone['EvnUslugaOper_setDT'])) {
				$pretitle = "<div class='completed'></div> ";
			}

			$out[] = array(
				'EvnDirection_id' => $respone['EvnDirection_id'],
				'EvnUslugaOper_id' => $respone['EvnUslugaOper_id'],
				'EvnUslugaOper_setDT' => $respone['EvnUslugaOper_setDT'],
				'Resource_id' => $respone['Resource_id'],
				'UslugaComplex_id' => $respone['UslugaComplex_id'],
				'start' => $startdate,
				'end' => $enddate,
				'title' => $pretitle.$Person_Fio.' '.$Person_Age.' '.$Person_Sex,
				'usluga' => $respone['UslugaComplex_Name'],
				'operbrig' => $operbrigdata
			);
		}

		return array(
			'success' => true,
			'message' => 'Loaded data',
			'data' => $out
		);
	}
}
