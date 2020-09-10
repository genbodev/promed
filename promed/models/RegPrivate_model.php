<?php

/**
 * Reg - модель для работы регистратуры частной клиники
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package
 * @access       public
 * @copyright    Copyright (c) 2020 Swan Ltd.
 * @author       brotherhood of swan developers
 */
class RegPrivate_model extends swModel {

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Загрузка входящих заявок
	 */
	function loadIncomeRequests($data){

		$filter = ''; $params = array();

		if (!empty($data['Lpu_id'])) {
			$params['Lpu_id'] = $data['Lpu_id'];
			$filter .= " and eqr.Lpu_id = :Lpu_id ";
		}

		if (!empty($data['MedService_id'])) {

		}

		$query = "
			select top 100
				eqr.EvnQueue_id,
				ed.EvnDirection_id,
				eqr.Person_id,
				CONVERT(varchar,eqr.EvnQueue_insDT,104) + ' ' + CONVERT(varchar(5),eqr.EvnQueue_insDT,108) as EvnQueue_insDT,
				DATEDIFF(mi, eqr.EvnQueue_insDT, dbo.tzGetDate()) as time_diff,
				esh.EvnStatus_id,
				esh.pmUser_insID as EvnStatus_pmUser_insID,
				es.EvnStatus_Name,
				null as RequestStatus_Name
			from v_EvnQueue_RecRequest eqr (nolock)
			inner join v_EvnDirection_all ed (nolock) on ed.EvnDirection_id = eqr.EvnDirection_id
			outer apply (
				select top 1
					esh.EvnStatus_id,
					esh.pmUser_insID
				from v_EvnStatusHistory esh (nolock)
				where esh.Evn_id = ed.EvnDirection_id
				order by esh.EvnStatusHistory_insDT desc
			) as esh
			left join v_EvnStatus es (nolock) on es.EvnStatus_id = esh.EvnStatus_id
			where (1=1)
				and eqr.RecMethodType_id in (1,2,3,14,15)					-- портал
				and ed.EvnStatus_id in (10,51)				-- новая, в работе
				and eqr.Person_id is not null
				and eqr.TimetableGraf_id is null
				{$filter}
			order by eqr.EvnQueue_insDT desc
		";

		$result = $this->queryResult($query, $params);
		foreach ($result as &$item) {
			$item['RequestStatus_Name'] = $this->transformStatusName($item);
		}
		return $result;
	}

	/**
	 * Переименование статусов
	 */
	function transformStatusName($data) {

		// lvl1 = EvnStatus,
		// lvl2 - EvnStatusCause
		$status_list = array(
			10 => 'Новая',
			13 => array(
				0 => 'Отклонено клиникой',
				18 => 'Клиент не пришел' // неявка пациента
			),
			17 => 'Подтверждена',
			15 => 'Обслужен', // клиент пришел,
			12 => 'Отклонено пациентом',
			51 => 'В обработке'
		);

		$resp = 'Не определен';

		if (!empty($data['EvnStatus_id'])) {
			if (isset($status_list[$data['EvnStatus_id']])) {
				$status = &$status_list[$data['EvnStatus_id']];
				if (is_array($status) && isset($data['EvnStatusCause_id'])) {
					if (isset($status[$data['EvnStatusCause_id']])) {
						$resp = $status[$data['EvnStatusCause_id']];
					} else {
						$resp = $status[0];
					}
				} else {
					$resp = $status;
				}
			}
		}

		return $resp;
	}

	/**
	 * Загрузка обработанных заявок
	 */
	function loadProcessedRequests($data){

		$filter = ''; $tthfilter = ''; $params = array();

		if (!empty($data['Lpu_id'])) {
			$params['Lpu_id'] = $data['Lpu_id'];
			$filter .= " and eqr.Lpu_id = :Lpu_id ";
		}

		if (!empty($data['date'])) {
			$params['date'] = $data['date'];
			$tthfilter .= " and cast(tth.TimetableGraf_begTime as date) = :date ";
			$filter .= " and tth.TimetableGraf_id is not null ";
		}

		$query = "
			select
				eqr.EvnQueue_id,
				ed.EvnDirection_id,
				eqr.Person_id,
				CONVERT(varchar,eqr.EvnQueue_insDT,104) + ' ' + CONVERT(varchar(5),eqr.EvnQueue_insDT,108) as EvnQueue_insDT,
				DATEDIFF(hour,dbo.tzGetDate(), eqr.EvnQueue_insDT),
				ed.EvnStatus_id,
				es.EvnStatus_Name,
				esc.EvnStatusCause_id,
				esc.EvnStatusCause_Name,
				null as RequestStatus_Name,
				eqr.QueueFailCause_id,
				tt.TimetableGraf_id,
				case when tt.TimetableGraf_begTime is null
					then tth.TimetableGraf_begTime
					else CONVERT(varchar,tt.TimetableGraf_begTime,104) + ' ' + CONVERT(varchar(5),tt.TimetableGraf_begTime,108)
				end as TimetableGraf_begTime,
				rtrim(ps.Person_Surname) as Person_Surname,
				rtrim(ps.Person_Firname) as Person_Firname,
				rtrim(ps.Person_Secname) as Person_Secname,
				rtrim(msf.Person_Surname) as MedPersonal_Surname,
				rtrim(msf.Person_Firname) as MedPersonal_Firname,
				rtrim(msf.Person_Secname) as MedPersonal_Secname,
			   	CONVERT(varchar,ps.Person_BirthDay,104) as Person_BirthDay,
			    dbo.Age2(ps.Person_BirthDay, dbo.tzGetDate()) as Person_Age,
			    lsp.ProfileSpec_Name,
				lsp.LpuSectionProfile_Code,
				lsp.LpuSectionProfile_id,
			    ps.Person_Phone,
			   	ed.EvnDirection_Descr,
			   	eqr.pmuser_insId
			from v_EvnQueue_RecRequest eqr (nolock)
			inner join v_EvnDirection_all ed (nolock) on ed.EvnDirection_id = eqr.EvnDirection_id
			left join v_EvnStatus es (nolock) on es.EvnStatus_id = ed.EvnStatus_id
			left join v_PersonState ps (nolock) on ps.Person_id = eqr.Person_id
			left join v_MedStaffFact msf (nolock) on msf.MedStaffFact_id = ed.MedStaffFact_id
			left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = msf.LpuSectionProfile_id
			left join v_TimetableGraf_lite tt (nolock) on tt.TimetableGraf_id = eqr.TimetableGraf_id
			outer apply (
				select top 1
					CONVERT(varchar,tth.TimetableGraf_begTime,104) + ' ' + CONVERT(varchar(5),tth.TimetableGraf_begTime,108) as TimetableGraf_begTime,
					tth.TimetableGraf_id
				from v_TimetableGrafHist tth (nolock)
				where tth.EvnDirection_id = eqr.EvnDirection_id
				{$tthfilter}
				order by tth.TimetableGrafHist_insDT desc
			) as tth
			outer apply (
				select top 1
					esh.EvnStatusCause_id,
					esc.EvnStatusCause_Name
				from v_EvnStatusHistory esh (nolock)
				left join v_EvnStatusCause esc (nolock) on esc.EvnStatusCause_id = esh.EvnStatusCause_id
				where esh.Evn_id = ed.EvnDirection_id
				order by esh.EvnStatusHistory_insDT desc
			) as esc
			where (1=1)
				and eqr.RecMethodType_id in (1,2,3,14,15)					-- портал
				and ed.EvnStatus_id in (17,15,12,13)				--
				and eqr.Person_id is not null
				{$filter}
			order by TimetableGraf_begTime, eqr.EvnStatus_id
		";

		$result = $this->queryResult($query, $params);
        if(!empty($result)) {
            $pmUsersArr = array_column($result, 'pmuser_insId');
            if(!empty($pmUsersArr)) {
                $rishConfig = $this->config->item('SwServiceRish');
                $this->load->library('SwServiceApi', $rishConfig, 'rish');
                $dbres = $this->rish->GET('Person/getEmail', [
                    'pmUsersList'=>implode(',',$pmUsersArr)
                ]);
                if(!empty($dbres['data'])) {
                    $pmUsersNewArr = array_column($dbres['data'], 'EMail', 'id');
                }
            }
            foreach ($result as &$item) {
                if(!empty($pmUsersNewArr)) {
                    $item['Person_Email'] = isset($pmUsersNewArr[$item['pmuser_insId']]) ? $pmUsersNewArr[$item['pmuser_insId']] : "";
                }
                $item['RequestStatus_Name'] = $this->transformStatusName($item);
            }
        }
		return $result;
	}

	/**
	 * Загрузка инфы по человеку
	 */
	function loadRequestData($data){
		$query = "
			select top 1
				eqr.EvnQueue_id,
				eqr.EvnDirection_id,
				eqr.QueueFailCause_id,
				ps.Person_id,
				CONVERT(varchar,eqr.EvnQueue_insDT,104) + ' ' + CONVERT(varchar(5),eqr.EvnQueue_insDT,108) as EvnQueue_insDT,
				DATEDIFF(hour,dbo.tzGetDate(), eqr.EvnQueue_insDT),
				rtrim(ps.Person_Surname) as Person_Surname,
				rtrim(ps.Person_Firname) as Person_Firname,
				rtrim(ps.Person_Secname) as Person_Secname,
				rtrim(msf.Person_Surname) as MedPersonal_Surname,
				rtrim(msf.Person_Firname) as MedPersonal_Firname,
				rtrim(msf.Person_Secname) as MedPersonal_Secname,
				CONVERT(varchar,ps.Person_BirthDay,104) as Person_BirthDay,
				dbo.Age2(ps.Person_BirthDay, dbo.tzGetDate()) as Person_Age,
				ps.PersonInfo_InternetPhone as Person_Phone,
			  	ed.EvnDirection_Descr,
			  	ed.MedStaffFact_id,
			  	u.email as Person_Email,
			  	lsp.ProfileSpec_Name,
			  	lsp.LpuSectionProfile_Code,
			  	lsp.LpuSectionProfile_id,
			  	tt.TimetableGraf_id,
			  	esh.EvnStatus_id,
			  	esh.pmUser_insID as EvnStatus_pmUser_insID,
				es.EvnStatus_Name,
				esh.EvnStatusCause_id,
				esh.EvnStatusCause_Name,
				null as RequestStatus_Name,
				CONVERT(varchar,tt.TimetableGraf_begTime,104) + ' ' + CONVERT(varchar(5),tt.TimetableGraf_begTime,108) as TimetableGraf_begTime
			from v_EvnQueue_RecRequest eqr (nolock)
			inner join v_EvnDirection_all ed (nolock) on ed.EvnDirection_id = eqr.EvnDirection_id
			outer apply (
				select top 1
					esh.EvnStatus_id,
					esh.pmUser_insID,
					esh.EvnStatusCause_id,
					esc.EvnStatusCause_Name
				from v_EvnStatusHistory esh (nolock)
				left join v_EvnStatusCause esc (nolock) on esc.EvnStatusCause_id = esh.EvnStatusCause_id
				where esh.Evn_id = ed.EvnDirection_id
				order by esh.EvnStatusHistory_insDT desc
			) as esh
			left join v_EvnStatus es (nolock) on es.EvnStatus_id = esh.EvnStatus_id
			left join v_PersonState ps (nolock) on ps.Person_id = eqr.Person_id
			left join v_MedStaffFact msf (nolock) on msf.MedStaffFact_id = ed.MedStaffFact_id
			left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = msf.LpuSectionProfile_id
			left join v_TimetableGraf_lite tt (nolock) on tt.TimetableGraf_id = eqr.TimetableGraf_id
			left join UserPortal.dbo.users u (nolock) on u.id = eqr.pmUser_insID
			where (1=1)
				and eqr.EvnQueue_id = :EvnQueue_id
		";

		$result = $this->getFirstRowFromQuery($query, array('EvnQueue_id' => $data['EvnQueue_id']));
        if(!empty($result)) {
            if(!empty($result['pmuser_insId'])) {
                $rishConfig = $this->config->item('SwServiceRish');
                $this->load->library('SwServiceApi', $rishConfig, 'rish');
                $dbres = $this->rish->GET('Person/getEmail', [
                    'pmUsersList'=>$result['pmuser_insId']
                ]);
                if(!empty($dbres['data'])) {
                    $pmUsersNewArr = array_column($dbres['data'], 'EMail', 'id');
                }
            }
            if(!empty($dbres['data'][0]['EMail'])) {
                $result['Person_Email'] = isset($pmUsersNewArr[$result['pmuser_insId']]) ? $pmUsersNewArr[$result['pmuser_insId']] : "";
            }
            if (!empty($result)) {
                $result['RequestStatus_Name'] = $this->transformStatusName($result);
            }
        }

		return $result;
	}

	/**
	 * Подтверждение заявки, создание бирки
	 */
	function saveRequest($data){

		$warnings = array();
		$this->load->model('InetPerson_model');

		if (!empty($data['overwriteTimetableGraf'])) {
			$warnings[] = 'overwriteTimetableGraf';
		}

		if (!empty($data['OverrideWarning'])) {
			$warnings[] = 'OverrideWarning';
		}

		$this->beginTransaction();

		if (!empty($data['Person_Phone'])) {

			//Обновляем информацию о телефоне в основной базе
			$savePhone = $this->InetPerson_model->personSetInternetPhone(array(
				'Person_id' => $data['Person_id'],
				'Person_Phone' => $data['Person_Phone'],
				'pmUser_id' => $data['pmUser_id']
			));

			if (!empty($savePhone['Error_Msg'])) {
				$this->rollbackTransaction();
				return array('Error_Msg' => $savePhone['Error_Msg']);
			}
		}

		//if (!empty($data['Person_Email'])) {
		//	unset($this->db);
		//	$this->load->database('UserPortal');
		//
		//	//Обновляем информацию о телефоне в основной базе
		//	$savePhone = $this->InetPerson_model->setPersonEmail(array(
		//		'email' => $data['Person_Email'],
		//		'account_id' => $data['account_id']
		//	));
		//
		//	if (!empty($savePhone['Error_Msg'])) {
		//		$this->rollbackTransaction();
		//		return array('Error_Msg' => $savePhone['Error_Msg']);
		//	}
		//
		//	unset($this->db);
		//	$this->load->database('default');
		//}

		// признак что нужно создать бирку и записать на нее
		$needMakeTimetable = true;

		// признак что нужно отменить текущую бирку
		$needCancelTimetable = false;

		// получаем инфу по бирке
		$ttinfo = $this->getTimetableInfo(array('TimetableGraf_id' => $data['TimetableGraf_id']));

		// признак что доктор поменялся
		$isDoctorChanged = false;

		if (!empty($ttinfo['MedStaffFact_id'])) {
			$isDoctorChanged = $data['MedStaffFact_id'] !== $ttinfo['MedStaffFact_id'];
		}

		if ($isDoctorChanged) {
			$needCancelTimetable = true;
		}

		// если указана бирка и время на ней отличается от времени которое выставили
		// то мы должны удалить переданную бирку и создать новую
		if (!empty($data['TimetableGraf_id']) && !$isDoctorChanged) {

			if (!empty($ttinfo['TimetableGraf_begTime'])) {

				if ($ttinfo['TimetableGraf_begTime'] instanceof DateTime) {

					if ($ttinfo['TimetableGraf_begTime']->format('H:i') != $data['TimetableGraf_begTime_time']) {
						$needCancelTimetable = true;
					}

					if ($ttinfo['TimetableGraf_begTime']->format('d.m.Y') != $data['TimetableGraf_begTime_date']) {
						$needCancelTimetable = true;
					}

					// если созданная ранее бирка, равна времени записи,
					// ничего не создаем и не записываем так как запись уже есть
					if (!$needCancelTimetable) {
						$needMakeTimetable = false;
					}

				} else {
					$this->rollbackTransaction();
					return array('Error_Msg' => 'Неверный формат времени существующей бирки');
				}
			}
		}
		
		if ($needCancelTimetable) {

			if (empty($data['overwriteTimetableGraf'])) {
				$this->rollbackTransaction();
				return array(
					'Warning_Msg' => 'Вы действительно хотите перезаписать пациента на другое время?',
					'Warning_Param' => 'overwriteTimetableGraf',
					'warnings' => $warnings
				);
			}

			$this->load->model('Timetable_model');
			$data['dontCancelDirection'] = true;
			$data['object'] = 'TimetableGraf';

			$clearResult = $this->Timetable_model->Clear($data);
			if (!empty($clearResult['Error_Msg'])) {
				$this->rollbackTransaction();
				return array('Error_Msg' => $clearResult['Error_Msg']);
			}

			$this->load->model('TimetableGraf_model');
			$deleteResult = $this->TimetableGraf_model->DeleteTTG($data);
			if (!empty($deleteResult['Error_Msg'])) {
				$this->rollbackTransaction();
				return array('Error_Msg' => $deleteResult['Error_Msg']);
			}

		}

		if ($needMakeTimetable) {

			$acceptedBegTime = $data['TimetableGraf_begTime_date'].' '.$data['TimetableGraf_begTime_time'];

			$data['acceptedBegTime'] = DateTime::createFromFormat('d.m.Y H:i', $acceptedBegTime);
			$data['acceptedBegTime'] = $data['acceptedBegTime']->format('Y-m-d H:i:s');

			// создание бирки врача на конкретное время и запись на неё
			$applyResult = $this->applyOnUnscheduledTimetable($data);

			if (!empty($applyResult['Error_Msg'])) {
				$this->rollbackTransaction();
				return array('Error_Msg' => $applyResult['Error_Msg']);
			}

			if (!empty($applyResult['Warning_Msg'])) {
				$this->rollbackTransaction();
				return array(
					'Warning_Msg' => $applyResult['Warning_Msg'],
					'Warning_Param' => 'OverrideWarning',
					'warnings' => $warnings
				);
			}

			if ($isDoctorChanged) {
				// меняем врача в направлении
				$updateDirection = $this->swUpdate('EvnDirection', array(
					'EvnDirection_id' => $ttinfo['EvnDirection_id'],
					'MedStaffFact_id' => $data['MedStaffFact_id']
				), false);

				if (!empty($updateDirection['Error_Msg'])) {
					$this->rollbackTransaction();
					return array('Error_Msg' => $updateDirection['Error_Msg']);
				}
			}

			$this->commitTransaction();

		} else {
			$this->commitTransaction();
			$applyResult = array('EvnDirection_id' => $data['EvnDirection_id']);
		}

		return array('success' => true, 'EnvDirection_id' => $applyResult['EvnDirection_id']);
	}

	// создание бирки врача на конкретное время
	// и запись на нее
	function applyOnUnscheduledTimetable($data) {

		$this->load->helper('Reg');

		$checkResult = $this->checkTimetableExist(array(
			'MedStaffFact_id' => $data['MedStaffFact_id'],
			'acceptedBegTime' => $data['acceptedBegTime']
		));

		if (!empty($checkResult['TimetableGraf_id'])) {

			if (!empty($checkResult['Person_id'])) {
				return array('Error_Msg' => 'Ошибка записи на бирку: данная бирка уже существует и на неё записан пациент');
			} else {
				$data['TimetableGraf_id'] = $checkResult['TimetableGraf_id'];
			}

		} else {

			$day_id = $this->getFirstResultFromQuery("select top 1 day_id from v_Day where cast(day_date as date) = cast(:day_date as date)",
				array('day_date' => $data['acceptedBegTime'])
			);

			$response = $this->addTTGUnscheduled(array(
				'TimetableGraf_Day' => $day_id,
				'MedStaffFact_id' => $data['MedStaffFact_id'],
				'TimetableGraf_begTime' => $data['acceptedBegTime'],
				'pmUser_id' => $data['pmUser_id']
			));

			if (!empty($response['TimetableGraf_id'])) {
				$data['TimetableGraf_id'] = $response['TimetableGraf_id'];
			} else {
				$err = !empty($response['Error_Msg']) ? ': '.$response['Error_Msg'] : '';
				return array('Error_Msg' => 'Ошибка создания бирки для врача'.$err);
			}
		}

		// записываем "как бы" из очереди
		$data['redirectEvnDirection'] = 600;
		$data['object'] = 'TimetableGraf';

		$data['PersonEvn_id'] = $this->getFirstResultFromQuery("
			select top 1 pe.PersonEvn_id
			from v_PersonEvn pe (nolock)
			where pe.Person_id = :Person_id
			order by pe.PersonEvn_id
		", array('Person_id' => $data['Person_id']));

		$this->load->model('Timetable_model', 'tt_model');
		$response = $this->tt_model->Apply($data);

		if (!empty($response['EvnDirection_id'])) {
			return $response;
		} else if (!empty($response['warning'])) {
			return array('Warning_Msg' => $response['warning']);
		} else {
			$err = !empty($response['Error_Msg']) ? ': '.$response['Error_Msg'] : '';
			return array('Error_Msg' => 'Ошибка записи на бирку по направлению заявки'.$err);
		}
	}

	/**
	 * Добавление незапланированного приема в платной поликлинике
	 */
	function addTTGUnscheduled($data) {

		$sql = "
			declare
				@Res bigint,
				@ErrCode bigint,
				@ErrMsg varchar(4000);
			set @Res = null;

			exec p_TimetableGraf_ins
				@TimetableGraf_id = @Res output,
				@MedStaffFact_id = :MedStaffFact_id,
				@TimetableGraf_Day = :TimetableGraf_Day,
				@TimetableGraf_begTime = :TimetableGraf_begTime,
				@TimetableGraf_Time = 0,
				@TimetableType_id = 1,
				@TimetableGraf_factTime = null,
				@pmUser_id = :pmUser_id
			select @Res as TimetableGraf_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;";

		$response = $this->getFirstRowFromQuery($sql, $data);

		if (!empty($response['TimetableGraf_id'])) {
			return array('TimetableGraf_id' => $response['TimetableGraf_id']);
		} else {
			$err = !empty($response['Error_Msg']) ? ': '.$response['Error_Msg'] : '';
			return array('Error_Msg' => 'Ошибка добавления бирки'.$err);
		}
	}

	/**
	 * Проверка что бирка не занята
	 */
	function checkTimetableExist($data) {
		$response = $this->getFirstRowFromQuery("
			select top 1
				tt.Person_id,
			  	tt.MedStaffFact_id,
				tt.TimetableGraf_begTime
			from v_TimetableGraf_lite tt (nolock)
			where (1=1)
				and DATEDIFF(minute, tt.TimetableGraf_begTime, :acceptedBegTime) = 0
				and tt.MedStaffFact_id = :MedStaffFact_id
		", $data);

		return $response;
	}

	/**
	 * Получение информации по бирке
	 */
	function getTimetableInfo($data) {
		$response = $this->getFirstRowFromQuery("
			select top 1
				tt.Person_id,
			  	tt.MedStaffFact_id,
				tt.TimetableGraf_begTime,
				tt.EvnDirection_id
			from v_TimetableGraf_lite tt (nolock)
			where (1=1)
				and tt.TimetableGraf_id = :TimetableGraf_id
		", $data);

		return $response;
	}

	/**
	 * Отклонение заявки
	 */
	function declineRequest($data){

		$this->beginTransaction();

		// переопределяем
		$data['DirFailType_id'] = null;
		$data['QueueFailCause_id'] = null;
		switch($data['EvnStatusCause_id']) {
			case 1:
				$data['DirFailType_id'] = 5;
				$data['QueueFailCause_id'] = 8;
				break;
			case 3:
				$data['DirFailType_id'] = 11;
				$data['QueueFailCause_id'] = 11;
				break;
			case 4:
				$data['DirFailType_id'] = 14;
				$data['QueueFailCause_id'] = 5;
				break;
			case 5:
				$data['DirFailType_id'] = 13;
				$data['QueueFailCause_id'] = 4;
				break;
			case 14:
				$data['DirFailType_id'] = 9;
				$data['QueueFailCause_id'] = 9;
				break;
			case 15:
				$data['DirFailType_id'] = 10;
				$data['QueueFailCause_id'] = 10;
				break;
			case 16:
				$data['DirFailType_id'] = 12;
				$data['QueueFailCause_id'] = null; // нет подходящего
				break;
			case 18:
				$data['DirFailType_id'] = 17;
				$data['QueueFailCause_id'] = 12;
				break;
		}

		$directionData = $this->getFirstRowFromQuery("
			select top 1
				ed.EvnDirection_id,
				ed.pmUser_insID,
				es.EvnStatus_SysNick,
				eqr.EvnQueue_id,
				tt.TimetableGraf_id
			from v_EvnDirection_all ed (nolock)
			left join v_EvnStatus es (nolock) on es.EvnStatus_id = ed.EvnStatus_id
			left join v_EvnQueue_RecRequest eqr (nolock) on eqr.EvnDirection_id = ed.EvnDirection_id
			outer apply(
				select top 1
					tt.TimetableGraf_id
				from v_TimetableGraf_lite tt (nolock)
				where (1=1)
					and tt.TimetableGraf_id = eqr.TimetableGraf_id
					and tt.Person_id = eqr.Person_id
			) as tt
			where ed.EvnDirection_id = :EvnDirection_id
		", $data);

		if (empty($directionData['EvnDirection_id'])) {
			$this->rollbackTransaction();
			return array('Error_Msg' => 'Ошибка получения данных по направлению');
		}

		if (in_array($directionData['EvnStatus_SysNick'], array('Declined', 'Canceled'))) {
			$this->rollbackTransaction();
			return array('Error_Msg' => 'Направление уже отменено');
		}

		if (empty($directionData['EvnQueue_id'])) {
			$this->rollbackTransaction();
			return array('Error_Msg' => 'Направление не связано с заявкой. Отменить заявку невозможно');
		}

		$this->load->model('EvnDirection_model');

		// если пациент уже записан на бирку нужно ее отменить
		if (!empty($directionData['TimetableGraf_id'])) {

			$this->load->model('Timetable_model');
			$data['dontCancelDirection'] = true;
			$data['object'] = 'TimetableGraf';
			$data['TimetableGraf_id'] = $directionData['TimetableGraf_id'];

			$clearResult = $this->Timetable_model->Clear($data);
			if (!empty($clearResult['Error_Msg'])) {
				$this->rollbackTransaction();
				return array('Error_Msg' => $clearResult['Error_Msg']);
			}

			$this->load->model('TimetableGraf_model');
			$deleteResult = $this->TimetableGraf_model->DeleteTTG($data);
			if (!empty($deleteResult['Error_Msg'])) {
				$this->rollbackTransaction();
				return array('Error_Msg' => $deleteResult['Error_Msg']);
			}
		}

		// в начале отменим направление
		$params =  array(
			'EvnDirection_id' => $data['EvnDirection_id'],
			'DirFailType_id' => $data['DirFailType_id'],
			'EvnComment_Comment' => $data['EvnStatusHistory_Cause'],
			'EvnStatusCause_id' => $data['EvnStatusCause_id'],
			'pmUser_id' => $data['pmUser_id'],
			'Lpu_cid' => !empty($data['session']['lpu_id']) ? $data['session']['lpu_id'] : null,
			'MedStaffFact_fid' => !empty($data['session']['CurMedStaffFact_id']) ? $data['session']['CurMedStaffFact_id'] : null,
		);

		$declineResult = $this->execCommonSP('p_EvnDirection_decline', $params, 'array_assoc');

		if (!empty($declineResult['Error_Msg'])) {
			$this->rollbackTransaction();
			return array('Error_Msg' => $declineResult['Error_Msg']);
		}

		// затем отменим заявку
		$this->load->model('Queue_model', 'Queue_model');

		$params = array(
			'EvnQueue_id' => $directionData['EvnQueue_id'],
			'QueueFailCause_id' => $data['QueueFailCause_id'],
			'EvnStatusCause_id' => !empty($data['EvnStatusCause_id']) ? $data['EvnStatusCause_id'] : null,
			'EvnComment_Comment' => !empty($data['EvnStatusHistory_Cause']) ? substr($data['EvnStatusHistory_Cause'], 0, 2048) : '',
			'cancelType' => !empty($data['cancelType']) ? $data['cancelType'] : 'cancel',
			'pmUser_id' => $data['pmUser_id']
		);

		$queueCancel = $this->execCommonSP("p_EvnQueue_cancel", $params, 'array_assoc');

		if (!empty($queueCancel['Error_Msg'])) {
			$this->rollbackTransaction();
			return array('Error_Msg' => $queueCancel['Error_Msg']);
		}


		$this->commitTransaction();
		return array('success' => true, 'EnvDirection_id' => $data['EvnDirection_id']);
	}

	/*
	 * пациент пришел\не пришел
	 */
	function setVisitApproveStatus($data) {

		try {

			$RecRequest_data = $this->getFirstRowFromQuery("
				select top 1
					eqr.EvnQueue_id,
					eqr.EvnDirection_id,
					eqr.QueueFailCause_id,
					ed.EvnStatus_id
				from v_EvnQueue_RecRequest eqr (nolock)
				inner join  v_EvnDirection_all ed (nolock) on ed.EvnDirection_id = eqr.EvnDirection_id
				where eqr.EvnQueue_id = :EvnQueue_id
			", array('EvnQueue_id' => $data['EvnQueue_id']));

			if (empty($RecRequest_data)) {
				throw new Exception('Не удалось получить данные заявки');
			}

			if ($data['isApprove']) {

				if ($RecRequest_data['EvnStatus_id'] == 15) {
					throw new Exception('Данная заявка уже в статусе - Пациент пришел');
				}

				// если заявка была в статусе - Пациент не пришел, надо убрать этот статус
				if ($RecRequest_data['QueueFailCause_id'] == 12) {
					$resetQueue = $this->swUpdate('EvnQueue', array(
						'EvnQueue_id' => $data['EvnQueue_id'],
						'QueueFailCause_id' => null
					), false);

					if (!empty($resetQueue['Error_Msg'])) {
						throw new Exception($resetQueue['Error_Msg']);
					}
				}

				// ставим направлению - обслужено, если пациент пришел
				$this->load->model('EvnDirectionAll_model');
				$this->EvnDirectionAll_model->setStatus(array(
					'Evn_id' => $RecRequest_data['EvnDirection_id'],
					'EvnStatusCause_id' => null,
					'EvnStatusHistory_Cause' => null,
					'EvnStatus_SysNick' => EvnDirectionAll_model::EVN_STATUS_DIRECTION_SERVICED,
					'EvnClass_id' => 27,
					'pmUser_id' => $data['pmUser_id']
				));

			} else {

				if ($RecRequest_data['QueueFailCause_id'] == 12) {
					throw new Exception('Данная заявка уже в статусе - Пациент не пришел');
				}

				// если заявка была в статусе - Пациент пришел, надо вернуть направлению статус записано
				if ($RecRequest_data['EvnStatus_id'] == 15) {
					$this->load->model('EvnDirectionAll_model');
					$this->EvnDirectionAll_model->setStatus(array(
						'Evn_id' => $RecRequest_data['EvnDirection_id'],
						'EvnStatusCause_id' => null,
						'EvnStatusHistory_Cause' => null,
						'EvnStatus_SysNick' => EvnDirectionAll_model::EVN_STATUS_DIRECTION_RECORDED,
						'EvnClass_id' => 27,
						'pmUser_id' => $data['pmUser_id']
					));
				}

				// ставим в заявку - неявка пациента, если пациент не пришел
				$result = $this->swUpdate('EvnQueue', array(
					'EvnQueue_id' => $data['EvnQueue_id'],
					// неявка пациента
					'QueueFailCause_id' => 12
				), false);

				if (!empty($result['Error_Msg'])) {
					throw new Exception($result['Error_Msg']);
				}
			}

			return array('success' => true, 'EvnQueue_id' => $data['EvnQueue_id']);

		} catch (Exception $e) {
			return array('Error_Msg' => $e->getMessage());
		}
	}

	/**
	 * Смена статуса заявки(блокировка, разблокировка)
	 */
	function setRequestStatus($data){

		try {

			if (!in_array($data['EvnStatus_SysNick'], array('InProc','Queued'))) {
				throw new Exception('Необходимо указать правильное системное имя статуса направления');
			}

			$RecRequest_data = $this->getFirstRowFromQuery("
				select top 1
					eqr.EvnQueue_id,
					eqr.EvnDirection_id,
					eqr.QueueFailCause_id,
					ed.EvnStatus_id,
					es.EvnStatus_Name,
					es.EvnStatus_SysNick
				from v_EvnQueue_RecRequest eqr (nolock)
				inner join v_EvnDirection_all ed (nolock) on ed.EvnDirection_id = eqr.EvnDirection_id
				left join v_EvnStatus es (nolock) on es.EvnStatus_id = es.EvnStatus_id
				where eqr.EvnQueue_id = :EvnQueue_id
			", array('EvnQueue_id' => $data['EvnQueue_id']));

			if (empty($RecRequest_data)) {
				throw new Exception('Не удалось получить данные заявки');
			}

			if ($data['EvnStatus_SysNick'] == $RecRequest_data['EvnStatus_SysNick']) {
				throw new Exception('Данная заявка уже в статусе - '.$data['EvnStatus_Name']);
			}

			if (empty($RecRequest_data['EvnDirection_id'])) {
				throw new Exception('Не удалось найти идентификатор направления по заявке');
			}

			$this->load->model('EvnDirectionAll_model');
			$this->EvnDirectionAll_model->setStatus(array(
				'Evn_id' => $RecRequest_data['EvnDirection_id'],
				'EvnStatusCause_id' => null,
				'EvnStatusHistory_Cause' => null,
				'EvnStatus_SysNick' => $data['EvnStatus_SysNick'],
				'EvnClass_id' => 27,
				'pmUser_id' => $data['pmUser_id']
			));

			return array('success' => true, 'EvnQueue_id' => $data['EvnQueue_id']);

		} catch (Exception $e){
			return array('Error_Msg' => $e->getMessage());
		}
	}
}

?>