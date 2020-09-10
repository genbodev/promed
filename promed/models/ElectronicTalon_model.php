<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      ElectronicTalon
 * @access       public
 * @copyright    Copyright (c) 2017 Swan Ltd.
 */

class ElectronicTalon_model extends swModel {

	/**
	 * Конструктор
	 */
	function __construct() { parent::__construct(); }

	/**
	 * Получить статус эл. талона по айди статуса
	 */
	function getElectronicTalonStatusById($data) {

		$params['ElectronicTalonStatus_id'] = $data['ElectronicTalonStatus_id'];

		$query = "
			select top 1
				ElectronicTalonStatus_id,
				ElectronicTalonStatus_Code,
				ElectronicTalonStatus_Name
			from
				v_ElectronicTalonStatus with (nolock)
			where
				ElectronicTalonStatus_id = :ElectronicTalonStatus_id
		";

		$resp = $this->queryResult($query, $params);
		return $resp;
	}

	/**
	 * Получить код ПО (как бы альтернатива КАБИНЕТА)
	 */
	function getElectronicServiceCodeById($data) {

		$params['ElectronicService_id'] = $data['ElectronicService_id'];

		$query = "
			select top 1
				ElectronicService_id,
				ElectronicService_Code
			from v_ElectronicService with (nolock)
			where ElectronicService_id = :ElectronicService_id
		";

		$resp = $this->queryResult($query, $params);
		return (!empty($resp[0]) && !empty($resp[0]['ElectronicService_Code']) ? $resp[0]['ElectronicService_Code'] : null);
	}

	/**
	 * Получение инф. по талону
	 */
	function getElectronicTalonById($data) {

		$params['ElectronicTalon_id'] = $data['ElectronicTalon_id'];

		$query = "
			select
				et.ElectronicTalon_id,
				et.ElectronicQueueInfo_id,
			  	et.ElectronicTalon_Num,
				et.ElectronicTalonStatus_id,
				et.ElectronicTalon_OrderNum,
				et.ElectronicService_id,
				es.ElectronicService_Num,
				es.ElectronicService_Name,
				es.ElectronicService_Code,
				et.EvnDirection_id,
				et.EvnDirection_uid,
				et.ElectronicTreatment_id,
				et.Person_id,
				et.pmUser_insID,
				et.pmUser_updID,
				ets.ElectronicTalonStatus_id,
				ets.ElectronicTalonStatus_Name,
				ets.ElectronicTalonStatus_Code,
				mseq.MedStaffFact_id
			from
				v_ElectronicTalon et with (nolock)
				left join v_ElectronicTalonStatus ets with (nolock) on ets.ElectronicTalonStatus_id = et.ElectronicTalonStatus_id
				left join v_ElectronicService es with (nolock) on es.ElectronicService_id = et.ElectronicService_id
				left join v_MedServiceElectronicQueue mseq (nolock) on mseq.ElectronicService_id = et.ElectronicService_id
			where
				ElectronicTalon_id = :ElectronicTalon_id
		";

		$resp = $this->queryResult($query, $params);

		return $resp;
	}

	/**
	 * Установка статуса электронного талона при неявке пациента (в авто режиме)
	 * (усовершенствованный вариант)
	 */
	function doCancelTalonCheck($data) {

		$params = array(
			'ElectronicTalon_id' => $data['ElectronicTalon_id']
		);

		if (!empty($data['cancelCallCount'])) { // возратим 2 * cancelCallCount  строк

			$rowCount = $data['cancelCallCount'] * 2;

			$query = "
				select sum(ElectronicTalonStatus_id) statesSum
				from (
				 select top {$rowCount}
						ElectronicTalonStatus_id
					from
						v_ElectronicTalonHist
					where
						ElectronicTalon_id = :ElectronicTalon_id
					order by
						ElectronicTalonHist_id desc
				) as s
            ";

			$response = $this->getFirstRowFromQuery($query, $params);

			if (!empty($response)) {

				$equalSum = 3*(int)$data['cancelCallCount'];
				$querySum = (int)$response['statesSum'];

				// если $querySum == $equalSum отменяем талон
				if ($equalSum == $querySum) return true;
				else return false;
			} else return false;
		} else return false;
	}

	/**
	 * отправка пуш уведомления
	 */
	function sendCallPushNotification($data, $changedData) {

		$pushNotificationEnabled = false; // пока заготовка для настройки
		if (!empty($data['disablePush'])) $pushNotificationEnabled = false;

		// отправляем пуш, только если нажато "Вызвать" в АРМ врача
		if ($pushNotificationEnabled && $data['ElectronicTalonStatus_id'] == 2) {

			$personData = $this->queryResult("
					select top 1
						Person_id,
						rtrim(p.Person_Surname) as Person_Surname,
						left(p.Person_Firname, 1) as Person_FirnameLetter,
						left(p.Person_Secname, 1) as Person_SecnameLetter,
						dbo.Age2(p.Person_BirthDay, dbo.tzGetdate()) as Person_Age
					from
						v_PersonState p with (nolock)
					where p.Person_id = :Person_id
				", array('Person_id' => $data['Person_id'])
			);

			// по возможности определяем пользователя портала
			$pmUser_did = $this->dbmodel->getFirstResultFromQuery("
					select top 1
						 ttg.pmUser_updID as pmUser_did
					from v_TimetableGraf_lite ttg (nolock)
					inner join v_ElectronicTalon et (nolock) on et.EvnDirection_id = ttg.EvnDirection_id
					where (1=1)
						--and ttg.TimetableGrafAction_id = 2
						and ttg.Person_id = :Person_id
					 	and et.ElectronicTalon_id = :ElectronicTalon_id
					order by ttg.TimetableGraf_id desc
				", array(
					'ElectronicTalon_id' => $data['ElectronicTalon_id'],
					'Person_id' => $data['Person_id']
				)
			);

			if (!empty($personData[0]['Person_id'])) {
				$Person_FullNameDots =
					$personData[0]['Person_Surname']
					.(!empty($personData[0]['Person_FirnameLetter']) ? ' '.$personData[0]['Person_FirnameLetter'].'.' : '')
					.(!empty($personData[0]['Person_SecnameLetter']) ? $personData[0]['Person_SecnameLetter'].'.' : '');
			}

			// если место работы указано, вычисляем кабинет
			if (!empty($data['MedStaffFact_id'])) {

				$this->load->model('TimetableGraf_model', 'ttg_model');
				$room = $this->ttg_model->getDoctorRoom(array(
					'MedStaffFact_id' => $data['MedStaffFact_id']
				));
			}

			// если кабинет вычислить по врачу не удалось, записываем в кабинет код ПО
			if (empty($room)) { $room = $this->getElectronicServiceCodeById(
				array('ElectronicService_id' => $changedData['ElectronicService_id']));
			}

			$this->load->helper('Notify');
			$notifyResult = sendPushNotification(
				array(
					'Person_id' => $data['Person_id'], // персона которая заходит
					'Person_Age' => (!empty($personData[0]) && !empty($personData[0]['Person_Age']) ? $personData[0]['Person_Age'] : null),
					'pmUser_did' => (!empty($pmUser_did) ? $pmUser_did : null), // тот кто записал на бирку
					'message' => 'Пациент '
						.(!empty($Person_FullNameDots) ? $Person_FullNameDots : '' )
						.' приглашается в кабинет'
						.(!empty($room) ? ' №'.$room : ''),
					'PushNoticeType_id' => 4,
					'action' => 'call'
				)
			);
		}
	}

	/**
	 * Установка статуса электронного талона
	 */
	function setElectronicTalonStatus($data) {

		// если указан параметр "Число отмены вызовов(вызов-ожидание) для отмены талона"
		if (!empty($data['cancelCallCount'])) {

			//проверяем можно ли отменить талон
			$canCancel = $this->doCancelTalonCheck($data);

			// выставляем статус отмены
			if ($canCancel) $data['ElectronicTalonStatus_id'] = 5;
		}

		$electronicTalonStatus = $this->getElectronicTalonStatusById($data);

		if (empty($electronicTalonStatus[0]['ElectronicTalonStatus_Code'])) {
			return array('Error_Msg' => 'Ошибка получения кода статуса талона');
		} else $electronicTalonStatus = $electronicTalonStatus[0];

		// начитываем всё из ElectronicTalon
		$oldData = $this->getElectronicTalonById($data);

		if (empty($oldData[0]['ElectronicTalon_id'])) {
			return array('Error_Msg' => 'Указанный идентификатор талона не существует');
		} else {
			$oldData = $oldData[0];
			$changedData = $oldData;
		}


		$this->load->model('ElectronicQueue_model');

		if (!empty($data['ElectronicQueueInfo_id'])) {
			$changedData['ElectronicQueueInfo_id'] = $data['ElectronicQueueInfo_id'];
		}

		$is_linear_eq = $this->ElectronicQueue_model->getElectronicQueueType(
			array('ElectronicQueueInfo_id' => $changedData['ElectronicQueueInfo_id'])
		);

		//если очредь нелинейная
		if (!$is_linear_eq) {

			// определим мультисервисность если не установлено
			$data['isMultiserviceElectronicQueue'] = true;

			// если статус установлен в "ожидает", нужно так же сбросить ПО
			if ($data['ElectronicTalonStatus_id'] == 1
				&& array_key_exists('ElectronicService_id', $data)
				// для ПО чей порядковый номер 1, не сбрасываем ПО
				&& (!empty($oldData['ElectronicService_Num']) && $oldData['ElectronicService_Num'] != 1)
			)  unset($data['ElectronicService_id']);

			// если статус изменен и ПО уже занят
			// то нужно отказать в этом действии для нового ПО
			if (!empty($oldData['ElectronicService_id'])
				&& !empty($data['ElectronicService_id'])
				&& $data['ElectronicService_id'] != $oldData['ElectronicService_id']
			)  {
				return array('Error_Msg' => 'Данный талон уже обслуживается другим специалистом');
			}
		}

		if (
			$oldData['ElectronicTalonStatus_id'] != $data['ElectronicTalonStatus_id']
			|| (
				array_key_exists('ElectronicService_id', $data)
				&& $oldData['ElectronicService_id'] != $data['ElectronicService_id']
			)
		) {
			// меняем статус
			$changedData['ElectronicTalonStatus_id'] = $data['ElectronicTalonStatus_id'];

			if (array_key_exists('ElectronicService_id', $data) && !empty($data['ElectronicService_id'])) {
				$changedData['ElectronicService_id'] = $data['ElectronicService_id'];
			} else {

				if (!$is_linear_eq) {
					$changedData['ElectronicService_id'] = null; // сбрасываем ПО для нелинейной работы ЭО
				}
			}

			if (!empty($data['pmUser_id'])) $changedData['pmUser_id'] = $data['pmUser_id'];
			else $changedData['pmUser_id'] = 1; // т.к. метод без авторизации может работать

			if (!empty($data['clearRedirectLink'])) {
				$changedData['EvnDirection_uid'] = null;
			}

			$this->beginTransaction();

			// обновляем талон ЭО
			$updateTalon = $this->updateElectronicTalon($changedData);

			if (!empty($updateTalon[0]['Error_Msg'])) {
				$this->rollbackTransaction();
				return $updateTalon[0];
			}

			// сохраняем изменения в историю ElectronicTalonHist
			if (empty($data['alreadyInTalonHistory']))
				$updateTalonHistory = $this->updateElectronicTalonHistory($changedData);

			if (!empty($updateTalonHistory[0]['Error_Msg'])) {
				$this->rollbackTransaction();
				return $updateTalonHistory[0];
			}

			$this->commitTransaction();
			$this->sendCallPushNotification($data, $changedData);

			// шлем изменения по талону в текущий пункт и следующий пункт если есть
			if (!empty($changedData['ElectronicService_id'])) {

				// нагребаем параметры для НОДА
				if (!empty($changedData['ElectronicQueueInfo_id'])) {
					$electronictreatment_id = $this->getFirstResultFromQuery("
						select top 1 ElectronicTreatment_id
						from v_ElectronicTreatmentLink etr (nolock)
						where ElectronicQueueInfo_id = :ElectronicQueueInfo_id
						order by ElectronicTreatment_id desc
					", array('ElectronicQueueInfo_id' => $changedData['ElectronicQueueInfo_id']));

					$medservice_id = $this->getFirstResultFromQuery("
						select top 1 MedService_id
						from v_ElectronicQueueInfo eqi (nolock)
						where ElectronicQueueInfo_id = :ElectronicQueueInfo_id
					", array('ElectronicQueueInfo_id' => $changedData['ElectronicQueueInfo_id']));
				}

				if (!empty($changedData['ElectronicService_id'])) {
					$sourceElectronicQueueInfo_id = $this->getFirstResultFromQuery("
						select top 1 ElectronicQueueInfo_id
						from v_ElectronicService es (nolock)
						where ElectronicService_id = :ElectronicService_id
					", array('ElectronicService_id' => $changedData['ElectronicService_id']));
				}

				$nodeParams = array(
					'ElectronicQueueInfo_id' => $changedData['ElectronicQueueInfo_id'],
					'ElectronicTalon_id' => $changedData['ElectronicTalon_id'],
					'ElectronicTreatment_id' => !empty($electronictreatment_id) ? $electronictreatment_id : null,
					'MedService_id' => !empty($medservice_id) ? $medservice_id : null,
					'sourceElectronicQueueInfo_id' => !empty($sourceElectronicQueueInfo_id) ? $sourceElectronicQueueInfo_id : null,
					'ElectronicTalon_Num' => $this->convertTicketNum($changedData['ElectronicTalon_Num']),
					'prevElectronicService_id' => $oldData['ElectronicService_id'],
					'nextElectronicService_id' => $changedData['ElectronicService_id'],
					'ElectronicService_id' => $changedData['ElectronicService_id'],
					'ElectronicService_Name' => $changedData['ElectronicService_Name'],
					'ElectronicService_Code' => $changedData['ElectronicService_Code'],
					'ElectronicTalonStatus_id' => $changedData['ElectronicTalonStatus_id'],
					'ElectronicTalonStatus_Name' => $electronicTalonStatus['ElectronicTalonStatus_Name'],
					'message' => 'electronicTalonStatusHasChanged'
				);

				if (!empty($changedData['MedStaffFact_id'])) {

					// получаем кабинет
					$this->load->model('TimetableGraf_model');

					$room = $this->TimetableGraf_model->getDoctorRoom(array('MedStaffFact_id' => $changedData['MedStaffFact_id']));
					if (!empty($room)) $nodeParams['MedStaffFact_Room'] = $room;

					// и ФИО
					$full_name = $this->getFirstRowFromQuery("
						select top 1
							case
								when msf.Person_SurName is not null then msf.Person_SurName
								else ''
							end as Doctor_SurName,
							case
								when msf.Person_FirName is not null then msf.Person_FirName
								else ''
							end as Doctor_FirName,
							case
								when msf.Person_SecName is not null then msf.Person_SecName
								else ''
							end as Doctor_SecName
						from v_MedStaffFact msf (nolock)
						where MedStaffFact_id = :MedStaffFact_id
					", array('MedStaffFact_id' => $changedData['MedStaffFact_id']));

					$nodeParams['Doctor_Fin'] = mb_ucfirst(mb_strtolower($full_name['Doctor_SurName']))
						.' '. (!empty($full_name['Doctor_FirName']) ? mb_substr($full_name['Doctor_FirName'], 0, 1).'.' : '')
						. (!empty($full_name['Doctor_SecName']) ? mb_substr($full_name['Doctor_SecName'], 0, 1).'.' : '');
				}

				$nodeResponse = $this->sendElectronicQueueNodeMessage($nodeParams);
			}


			// делаем переадресацию для ЭО где много ПО
			if (!empty($data['isMultiserviceElectronicQueue'])) {

				// нагребаем параметры для НОДА
				$nodeParams = array(
					'ElectronicTalon_id' => $changedData['ElectronicTalon_id'],
					'ElectronicTalon_Num' => $changedData['ElectronicTalon_Num'],
					'fromElectronicService_id' => $oldData['ElectronicService_id'],
					'ElectronicTalonStatus_id' =>  $changedData['ElectronicTalonStatus_id'],
					'ElectronicTalonStatus_Name' => $electronicTalonStatus['ElectronicTalonStatus_Name']
				);

				if (array_key_exists('ElectronicService_id', $data)) {

					if ($is_linear_eq) {
						// только если ПО изменился
						if ($oldData['ElectronicService_id'] != $changedData['ElectronicService_id']) {

							$nodeParams['ElectronicService_id'] = $changedData['ElectronicService_id'];
							$nodeParams['message'] = 'electronicTalonRedirected';
							$nodeResponse = $this->sendElectronicQueueNodeMessage($nodeParams);
						}

					} else {
						$nodeParams['ElectronicQueueInfo_id'] = $changedData['ElectronicQueueInfo_id'];
						$nodeParams['message'] = 'electronicTalonIsBusy';
						$nodeResponse = $this->sendElectronicQueueNodeMessage($nodeParams);
					}

				} else {

					if (!$is_linear_eq) {

						// только если ПО изменился
						if ($oldData['ElectronicService_id'] != $changedData['ElectronicService_id']
						&& $changedData['ElectronicService_id'] == NULL) {

							$nodeParams['ElectronicService_id'] = 0;
							$nodeParams['message'] = 'electronicTalonRedirected';
							$nodeResponse = $this->sendElectronicQueueNodeMessage($nodeParams);
						}

						// если ПО не пришел значит отправляем талон в общую кучу
						$nodeParams['ElectronicQueueInfo_id'] = $changedData['ElectronicQueueInfo_id'];
						$nodeParams['message'] = 'electronicTalonIsFreeForCall';
						$nodeResponse = $this->sendElectronicQueueNodeMessage($nodeParams);
					}
				}
			}
		}

		return array('Error_Msg' => '');
	}

	/**
	 * Добавляем нули к номеру талона
	 */
	function convertTicketNum($num) {

		$talon_num = trim($num);
		$maxTalonDigits = 4;

		if ($talon_num && (mb_strlen($talon_num) < $maxTalonDigits)) {

			for ($i=0; $i<$maxTalonDigits-mb_strlen($talon_num); $i++) {
				$talon_num = '0'.$talon_num;
			}

			$num = $talon_num;
		}

		return $num;
	}

	/**
	 * Обновление талона ЭО
	 */
	function updateElectronicTalon($data) {

		if (empty($data['EvnDirection_uid'])) $data['EvnDirection_uid'] = null;

		$query = "
					declare
						@ElectronicTalon_id bigint = :ElectronicTalon_id,
						@ErrCode int,
						@ErrMessage varchar(4000);
					exec p_ElectronicTalon_upd
						@ElectronicTalon_id = @ElectronicTalon_id output,
						@ElectronicQueueInfo_id = :ElectronicQueueInfo_id,
						@ElectronicTalon_Num = :ElectronicTalon_Num,
						@ElectronicTalonStatus_id = :ElectronicTalonStatus_id,
						@ElectronicTalon_OrderNum = :ElectronicTalon_OrderNum,
						@ElectronicService_id = :ElectronicService_id,
						@EvnDirection_id = :EvnDirection_id,
						@EvnDirection_uid = :EvnDirection_uid,
						@ElectronicTreatment_id = :ElectronicTreatment_id,
						@Person_id = :Person_id,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
					select @ElectronicTalon_id as ElectronicTalon_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";

		// пересохраняем талон с новыми данными
		//echo '<pre>',print_r(getDebugSQL($query, $data)),'</pre>'; die();
		$response = $this->queryResult($query, $data);
		return $response;
	}

	/**
	 * Добавляем запись в историю талона
	 */
	function updateElectronicTalonHistory($data) {

		$query = "
					declare
						@ElectronicTalonHist_id bigint,
						@ErrCode int,
						@ErrMessage varchar(4000);
					exec p_ElectronicTalonHist_ins
						@ElectronicTalonHist_id = @ElectronicTalonHist_id output,
						@ElectronicTalon_id = :ElectronicTalon_id,
						@ElectronicTalonStatus_id = :ElectronicTalonStatus_id,
						@ElectronicService_id = :ElectronicService_id,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
					select @ElectronicTalonHist_id as ElectronicTalonHist_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";

		// сохраняем изменения в историю ElectronicTalonHist
		$response = $this->queryResult($query, $data);
		return $response;
	}

	/**
	 * Удаляем перенаправления талона ЭО
	 */
	function deleteElectronicTalonRedirect($data) {

		// удаляем запись перенаправления
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_ElectronicTalonRedirect_del
				@ElectronicTalonRedirect_id = :ElectronicTalonRedirect_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$response = $this->queryResult($query, $data);

		// перенаправляем талон в предыдущий пункт обслуживания
		$this->redirectElectronicTalon($data);
	}

	/**
	 * Отмена электронного талона по направлению
	 * @param array $data
	 * @return array
	 */
	function cancelElectronicTalonByEvnDirection($data) {
		// Если текущий статус Талона ЭО не равен "Отменен" или "Обслужен",
		// то Талону ЭО присваивается статус «Отменен».
		$resp_et = $this->queryResult("
			select
				et.ElectronicTalon_id
			from
				v_ElectronicTalon et with(nolock)
			where
				et.EvnDirection_id = :EvnDirection_id
				and et.ElectronicTalonStatus_id not in (4,5)
		", array(
			'EvnDirection_id' => $data['EvnDirection_id']
		));
		if (!is_array($resp_et)) {
			return $this->createError('','Ошибка при получении списка талонов для отмены');
		}

		foreach ($resp_et as $one_et) {
			$this->setElectronicTalonStatus(array(
				'ElectronicTalon_id' => $one_et['ElectronicTalon_id'],
				'ElectronicTalonStatus_id' => 5, // Изменяется текущий статус на Отменен
				'pmUser_id' => $data['pmUser_id']
			));
		}

		// смотрим есть ли доп. напаравления у талона ЭО
		$electronicTalonRedirect = $this->getFirstRowFromQuery("
			select top 1
				et.ElectronicTalon_id,
				etr.ElectronicTalonRedirect_id,
				etr.ElectronicService_uid
			from
				v_ElectronicTalon et with (nolock)
			inner join v_ElectronicTalonRedirect etr with (nolock) on etr.EvnDirection_uid = et.EvnDirection_uid
			where
				et.EvnDirection_uid = :EvnDirection_id
		", array(
			'EvnDirection_id' => $data['EvnDirection_id']
		), true);
		if ($electronicTalonRedirect === false) {
			return $this->createError('','Ошибка при получении данных перенаправления');
		}

		if ($electronicTalonRedirect) {
			// если есть связанное перенаправление удаляем его
			// и перенаправляем талон обратно где он был до этого
			$res = $this->deleteElectronicTalonRedirect(array(
				'ElectronicTalon_id' => $electronicTalonRedirect['ElectronicTalon_id'],
				'EvnDirection_uid' => $data['EvnDirection_id'],
				'ElectronicTalonRedirect_id' => $electronicTalonRedirect['ElectronicTalonRedirect_id'],
				'ElectronicService_id' => $electronicTalonRedirect['ElectronicService_uid'],
				'pmUser_id' => $data['pmUser_id']
			));
		}

		return array(array(
			'success' => true
		));
	}

	/**
	 * Создать направление в регистратуру, поместить в очередь EvnQueue,
	 * получить код брони, получить номер талона
	 */

	function ApplyElectronicQueue($data) {

		$this->load->model('EvnDirection_model', 'edmodel');

		if ( $data['EvnDirection_setDT'] instanceof DateTime ) {
			$data['year'] = $data['EvnDirection_setDT']->format('Y');
		}
		else {
			$data['year'] = substr($data['EvnDirection_setDT'], 0, 4);
		}

		// генерим номер EvnDirection_Num
		$edNumReq = $this->edmodel->getEvnDirectionNumber(array('Lpu_id' => $data['Lpu_id'], 'year' => $data['year']));

		if (is_array($edNumReq) && isset($edNumReq[0]['EvnDirection_Num'])) {
			$data['EvnDirection_Num'] = $edNumReq[0]['EvnDirection_Num'];
		}

		if (empty($data['EvnDirection_IsGenTalonCode'])) $data['EvnDirection_IsGenTalonCode'] = NULL;

		if (empty($data['MedService_did']) && !empty($data['MedService_id']))
			$data['MedService_did'] = $data['MedService_id'];

		//echo var_dump('5'); die();

		$sql = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_EvnDirection_insToQueue
				@EvnDirection_id = @Res output,
				@Lpu_id = :Lpu_id,
				@Lpu_did = :Lpu_did,
				@MedService_did = :MedService_did,
				@Server_id = :Server_id,
				@PersonEvn_id = :PersonEvn_id,
				@pmUser_id = :pmUser_id,
				@EvnDirection_Num = :EvnDirection_Num,
				@EvnDirection_IsAuto = :EvnDirection_IsAuto,
				@EvnDirection_setDT = :EvnDirection_setDT,
				@DirType_id = :DirType_id,
				@EvnDirection_IsGenTalonCode = :EvnDirection_IsGenTalonCode,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output
			select @Res as EvnDirection_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;";

		//echo getDebugSQL($sql, $data);; die();
		$result = $this->queryResult($sql, $data);

		//echo var_dump($result); die();
		return $result;
	}

	/**
	 * Создаем бирку и записываем на нее, результат в виде направления возращаем
	 */
	function applyOnUnscheduledTimetable($data) {

		$this->load->model('Timetable_model', 'tt_model');
		$result = array('EvnDirection_id' => NULL);

		$this->load->helper('Reg');
		$data['Day'] = TimeToDay(time()) ;
		$data['TimetableObject_id'] = 1;


		$data['Unscheduled'] = true; // признак того что создаем бирку незапланированную
		$data['ignoreCanRecord'] = true; // признак игнора записи на бирку
		$data['OverrideWarning'] = true; // предупреждения не показываем
		$data['isElectronicQueueRedirect'] = true; // для того чтобы не генерился код брони

		// для направления
		$personEvn = $this->dbmodel->getFirstRowFromQuery("
			select top 1 PersonEvn_id, Server_id
			from v_PersonState with (nolock)
			where Person_id = :Person_id",
			$data
		);

		$data['PersonEvn_id'] = (!empty($personEvn['PersonEvn_id']) ? $personEvn['PersonEvn_id'] : NULL);
		$data['Server_id'] = (!empty($personEvn['Server_id']) ? $personEvn['Server_id'] : NULL);
		$data['EvnDirection_IsAuto'] = 2; // направление автоматическое

		$currentDate = $this->getFirstResultFromQuery('select dbo.tzGetDate() as date');
		$data['EvnDirection_setDate'] = $currentDate->format('Y-m-d');
		$data["EvnDirection_setDT"] = $data["EvnDirection_setDate"];

		$data['EvnDirection_Num'] = "0"; // хз почему генерится номер по такому условию...
		$data['LpuSection_did'] = $data['LpuSection_id'];
		$data['Lpu_did'] = $data['Lpu_id'];
		$data['EvnDirection_Descr'] = "";
		$data['MedPersonal_zid'] = $data['MedPersonal_id'];
		$data['Diag_id'] = NULL;

		switch ($data['object']) {

			case "TimetableGraf":

				$this->load->model('TimetableGraf_model');
				$response = $this->TimetableGraf_model->addTTGUnscheduled($data);

				if (!empty($response['TimetableGraf_id'])) {
					$data['TimetableGraf_id'] = $response['TimetableGraf_id'];
				} else {
					return array('Error_Msg' => 'Ошибка создания дополнительной бирки', 'success' => false);
				}

				$data['DirType_id'] = 3; // на консультацию
				$response = $this->tt_model->Apply($data);

				if (!empty($response['EvnDirection_id'])) {
					$result['EvnDirection_id'] = $response['EvnDirection_id'];
				} else {
					return array('Error_Msg' => 'Ошибка создания направления и записи на бирку', 'success' => false);
				}

				break;

			case "TimetableMedService":

				$this->load->model('TimetableMedService_model');
				$response = $this->TimetableMedService_model->addTTMSDop($data);

				if (!empty($response['TimetableMedService_id'])) {
					$data['TimetableMedService_id'] = $response['TimetableMedService_id'];
				} else {
					return array('Error_Msg' => 'Ошибка создания дополнительной бирки', 'success' => false);
				}

				$data['DirType_id'] = 10; // на исследование
				$response = $this->tt_model->Apply($data);

				if (!empty($response['EvnDirection_id'])) {
					$result['EvnDirection_id'] = $response['EvnDirection_id'];
				} else {

					$err_msg = '';

					if (!empty($response[0]['Error_Msg'])) {
						$err_msg = $response[0]['Error_Msg'];
					} else if (!empty($response['Error_Msg'])) {
						$err_msg = $response['Error_Msg'];
					}

					return array('Error_Msg' => 'Ошибка создания направления и записи на бирку'.$err_msg, 'success' => false);
				}

				break;

			case "TimetableResource":
				$data['DirType_id'] = 10; // на исследование
				break;

			case "NotTimetable":

				$data['DirType_id'] = 24; // Регистратура
				$data['EvnDirection_IsGenTalonCode'] = 1; // не генерим код брони

				$response = $this->ApplyElectronicQueue($data);

				if (!empty($response) && !empty($response[0]['EvnDirection_id'])) {
					$result['EvnDirection_id'] = $response[0]['EvnDirection_id'];
				} else {
					return array('Error_Msg' => 'Ошибка создания направления в регистратуру', 'success' => false);
				}

				break;
		}

		return $result;
	}

	/**
	 * Запись на бирку и создание направления для нашего объекта
	 */
	function makeEvnDirection($data) {

		// необходимо записать на бирку
		if (!empty($data['MedStaffFact_id'])) {

			$data['object'] = "TimetableGraf";

		} else if (!empty($data['MedServiceType_SysNick']) && $data['MedServiceType_SysNick'] == 'regpol') {

			$data['object'] = "NotTimetable";

		} else if (!empty($data['MedService_id'])) {

			$ms_type = $this->getFirstResultFromQuery("
				select top 1
					mst.MedServiceType_SysNick
				from v_MedService ms (nolock)
				inner join v_MedServiceType mst (nolock) on mst.MedServiceType_id = ms.MedServiceType_id
				where ms.MedService_id = :MedService_id
			", array('MedService_id' => $data['MedService_id']));

			switch ($ms_type) {

				case "func":
					$data['object'] = "TimetableResource";
					break;
				case "lab":
					$data['object'] = "TimetableMedService";
					break;
				case "pzm":
					$data['object'] = "TimetableMedService";
					break;
			}
		} else {
			return array('Error_Msg' => 'Не определен тип объекта перенаправления');
		}

		$result = $this->applyOnUnscheduledTimetable($data);
		if (!empty($result['EvnDirection_id'])) {
			return array('EvnDirection_id' => $result['EvnDirection_id']);
		} else {
			$err_msg = !empty($result['Error_Msg']) ? ': '.$result['Error_Msg'] : '';
			return array('Error_Msg' => 'Не удалось создать направление'.$err_msg);
		}
	}

	/**
	 * Перенаправляем талон ЭО
	 */
	function redirectElectronicTalon($data) {

		//date_default_timezone_set("Asia/Yekaterinburg");
		$electronicTalon = $this->getElectronicTalonById($data);
		if (!empty($data['redirectBack'])) {

			$data['redirectBack'] = ($data['redirectBack'] === 'true' || $data['redirectBack'] == 1);
		}

		if (empty($electronicTalon[0]['ElectronicTalon_id'])) {
			return array('Error_Msg' => 'Указанный талон ЭО не существует', 'success' => false);
		} else $electronicTalon = $electronicTalon[0];

		$data['Person_id'] = $electronicTalon['Person_id'];

		// где сейчас талон
		$fromElectronicService_id = $electronicTalon['ElectronicService_id'];

		$params = array(
			'ElectronicTalon_id' => $data['ElectronicTalon_id'],
			'fromElectronicService_id' => $fromElectronicService_id,
			'toElectronicService_id' => $data['ElectronicService_id'],
			'pmUser_id' => $data['pmUser_id'],
			'EvnDirection_uid' => null
		);

		if (empty($data['EvnDirection_id']) && empty($data['redirectBack'])) {
			$resp_ed = $this->makeEvnDirection($data);
			if (!empty($resp_ed['EvnDirection_id'])) {
				$data['EvnDirection_id'] = $resp_ed['EvnDirection_id'];
			} else {
				return $resp_ed; // возвращаем ошибку
			}
		} else {
			// либо запись на бирку произошла через мастер записи
			// либо переадресация происходит по тому же направлению
		}

		// смотрим направление, если он есть, он будет  сохранен как EvnDirection_uid
		if (!empty($data['EvnDirection_id']) && empty($data['redirectBack'])) {
			// направление которое пришло из записи на бирку
			$params['EvnDirection_uid'] = $data['EvnDirection_id'];
		} else if (!empty($data['redirectBack'])) {

			// возвращаем
			$primaryElectronicService = $this->getPrimaryElectronicService($data);
			if (empty($primaryElectronicService[0]['fromElectronicService_id'])) {
				return array('Error_Msg' => 'Невозможно определить первоначальный пункт обслуживания, возврат талона не возможен');
			} else {

				// если это возврат не в первоначальный пункт обслуживания
				// то пытаемся найти доп. направление этого пункта обслуживания
				$primaryElectronicService_id = $primaryElectronicService[0]['fromElectronicService_id'];
				if ($primaryElectronicService_id != $params['toElectronicService_id']) {

					// возвращаем доп. направление этого пункта обслуживания
					$electronicServiceEvnDirection = $this->getRedirectedElectronicServiceEvnDirection($data);
					if (empty($electronicServiceEvnDirection[0]['EvnDirection_uid'])) {

						// возможно что мы сюда еще не перенаправляли, поэтому создаим талон и перенаправим туда
						$resp_ed = $this->makeEvnDirection($data);
						if (!empty($resp_ed['EvnDirection_id'])) {
							$data['EvnDirection_id'] = $resp_ed['EvnDirection_id'];
						} else {
							return $resp_ed; // возвращаем ошибку
						}
						if (!empty($data['EvnDirection_id'])) {
							$params['EvnDirection_uid'] = $data['EvnDirection_id'];
						}
						//return array('Error_Msg' => 'Невозможно определить доп. направление пункта обслуживания');
					} else {
						// возвращаем на это доп. направление
						$params['EvnDirection_uid'] = $electronicServiceEvnDirection[0]['EvnDirection_uid'];
					}

				} else {
					// если это возврат в первоначальный пункт обслуживания
					// то обнуляем доп. направление 'EvnDirection_uid' => null
				}
			}

		} else {
			return array('Error_Msg' => 'Не указано направление для перенаправления');
		}
		// добавляем запись в кладезь перенаправлений
		$query = "
			declare
				@ElectronicTalonRedirect_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_ElectronicTalonRedirect_ins
				@ElectronicTalonRedirect_id = @ElectronicTalonRedirect_id output,
				@ElectronicTalon_id = :ElectronicTalon_id,
				@ElectronicService_id = :toElectronicService_id,
				@ElectronicService_uid = :fromElectronicService_id,
				@EvnDirection_uid = :EvnDirection_uid,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ElectronicTalonRedirect_id as ElectronicTalonRedirect_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$response = $this->queryResult($query, $params);

		// добавляем запись в историю талона, меняем статус для предыдущего пункта
		$electronicTalon['pmUser_id'] = $data['pmUser_id'];
		$electronicTalon['ElectronicTalonStatus_id'] = 4; // Обслужен
		$this->updateElectronicTalonHistory($electronicTalon);

		// обновляем талон, указав новый пункт и статус
		$electronicTalon['ElectronicTalonStatus_id'] = 1; // Ожидает
		$electronicTalon['ElectronicService_id'] = $data['ElectronicService_id']; // новый пункт
		$electronicTalon['EvnDirection_uid'] = $params['EvnDirection_uid']; // новое направление

		// если изменяется ЭО, так же меняет идентификатор ЭО в талоне
		if (!empty($data['ElectronicQueueInfo_id'])) {
			$electronicTalon['ElectronicQueueInfo_id'] = $data['ElectronicQueueInfo_id'];
		}

		$this->updateElectronicTalon($electronicTalon);

		// добавляем запись в историю талона, что он переведен в другой пункт со статусом "Ожидает"
		// todo: в табличку так же добавить поле ElectronicQueueInfo_id
		$this->updateElectronicTalonHistory($electronicTalon);

		$electronicTalonStatus = $this->getElectronicTalonStatusById($electronicTalon);

		if (empty($electronicTalonStatus[0])) {
			return array('Error_Msg' => 'Невозможно определить статус талона');
		} else {
			$electronicTalonStatus = $electronicTalonStatus[0];
		}

		// нагребаем параметры для НОДА
		$nodeParams = array(
			'message' => 'electronicTalonRedirected',
			'ElectronicTalon_id' => $electronicTalon['ElectronicTalon_id'],
			'ElectronicTalon_Num' => $electronicTalon['ElectronicTalon_Num'],
			'ElectronicService_id' => $electronicTalon['ElectronicService_id'],
			'ElectronicQueueInfo_id' => $electronicTalon['ElectronicQueueInfo_id'],
			'ElectronicTalonStatus_id' => $electronicTalon['ElectronicTalonStatus_id'],
			'ElectronicTalonStatus_Name' => $electronicTalonStatus['ElectronicTalonStatus_Name'],
		);

		// отправляем сообщение пункту обслуживания, кому переадресован талон
		$this->sendElectronicQueueNodeMessage($nodeParams);

		$electronicServiceInfo = $this->queryResult("
                select top 1
                	ElectronicService_id,
                    ElectronicService_Name
                from
                    v_ElectronicService with (nolock)
                where
                    ElectronicService_id = :toElectronicService_id
            ", array('toElectronicService_id' => $data['ElectronicService_id'])
		);

		if (!empty($response[0]['ElectronicTalonRedirect_id'])
			&& !empty($electronicServiceInfo[0]['ElectronicService_Name'])
		) {
			$response[0]['ElectronicService_Name'] = $electronicServiceInfo[0]['ElectronicService_Name'];
		}

		return $response;
	}

	/**
	 * Отправка информационного сообщения в АРМы, через нод
	 */
	function sendElectronicQueueNodeMessage($data) {

		// инициализируем настройки соединения
		$config = null;
		if (defined('NODEJS_PORTAL_PROXY_HOSTNAME') && defined('NODEJS_PORTAL_PROXY_HTTPPORT')) {
			// берём хост и порт из конфига, если есть
			$config = array(
				'host' => NODEJS_PORTAL_PROXY_HOSTNAME,
				'port' => NODEJS_PORTAL_PROXY_HTTPPORT
			);


			$this->load->helper('NodeJS');
			$response = NodePostRequest($data, $config);

			if (!empty($response[0]['Error_Msg'])) {
				return $response[0];
			}
		}
	}



	/**
	 * Подгрузка комбо с пунктами обслуживания по текущему подразделению ЭО или ЛПУ (для перенаправления)
	 */
	public function loadLpuBuildingElectronicServices($data) {

		$filter = ""; $join = ""; $apply = ""; $select = "";
		$params = array('Lpu_id' => $data['Lpu_id']);

		if (!empty($data['CurrentElectronicService_id'])) {
			$params['CurrentElectronicService_id'] = $data['CurrentElectronicService_id'];
			$filter .= ' and es.ElectronicService_id != :CurrentElectronicService_id ';
		}

		if (!empty($data['ElectronicTalon_id'])) {
			$params['ElectronicTalon_id'] = $data['ElectronicTalon_id'];
			$apply .= "
				outer apply(
					select
						case when isnull(etr.ElectronicTalonRedirect_id, 0) > 0 then 1 else 0 end as wasRedirectedTo
					from v_ElectronicTalonRedirect etr (nolock)
					inner join v_ElectronicTalon et (nolock) on et.ElectronicTalon_id = etr.ElectronicTalon_id
					where
						etr.ElectronicTalon_id = :ElectronicTalon_id
						and etr.ElectronicService_uid = es.ElectronicService_id
						and cast(et.ElectronicTalon_insDT as date) = @curDate
				) etroa
			";

			$select .= "
				, etroa.wasRedirectedTo
			";
		}

		if (empty($data['noLoad'])) {

			$apply .= "
				outer apply(
					select count(et.ElectronicTalon_id) as cnt
					from v_ElectronicTalon et (nolock)
					where
						et.ElectronicTalonStatus_id in (1,2,3)
						and et.ElectronicService_id = es.ElectronicService_id
						and CONVERT(varchar,et.ElectronicTalon_insDT, 104) = CONVERT(varchar,dbo.tzGetDate(), 104)
				) load
			";

			$select .= " , load.cnt as ElectronicService_Load ";
		}

		if (!empty($data['LpuBuilding_id'])) {

			$params['LpuBuilding_id'] = $data['LpuBuilding_id'];

			$filter .= "
				and (
					eqi.LpuBuilding_id = :LpuBuilding_id
					or eqi.MedService_id in (
						select
							ms2.MedService_id
						from v_MedService ms2 (nolock)
						where ms2.LpuBuilding_id = :LpuBuilding_id
					)
					or eqi.MedService_id in (
						select
							ms3.MedService_id
						from v_MedService ms3 (nolock)
						where
							ms3.Lpu_id = :Lpu_id
							and ms3.LpuBuilding_id IS NULL
					)
				)
			";
		}

		$query = "

	        declare @curDate date = dbo.tzGetdate();

			select distinct
				es.ElectronicService_id,
				eqi.ElectronicQueueInfo_id,
				eqi.ElectronicQueueInfo_Name,
				isnull(lb.LpuBuilding_id, lbms.LpuBuilding_id) as LpuBuilding_id,
				isnull(lb.LpuBuilding_Name, lbms.LpuBuilding_Name) as LpuBuilding_Name,
				es.ElectronicService_Code,
				es.ElectronicService_Name,
				mseq.MedStaffFact_id,
				mseq.UslugaComplexMedService_id,
				mst.MedServiceType_SysNick,
				isnull(ucms.MedService_id, eqi.MedService_id) as MedService_id
				{$select}
			from
				v_ElectronicQueueInfo eqi with (nolock)
				inner join v_ElectronicService es with (nolock) on es.ElectronicQueueInfo_id = eqi.ElectronicQueueInfo_id
				left join v_MedServiceElectronicQueue mseq (nolock) on mseq.ElectronicService_id = es.ElectronicService_id
				left join v_UslugaComplexMedService ucms (nolock) on ucms.UslugaComplexMedService_id = mseq.UslugaComplexMedService_id
				left join v_MedService ms (nolock) on (ms.MedService_id = ucms.MedService_id or ms.MedService_id = eqi.MedService_id)
				left join v_MedServiceType mst (nolock) on mst.MedServiceType_id = ms.MedServiceType_id
				left join v_LpuBuilding lb (nolock) on lb.LpuBuilding_id = eqi.LpuBuilding_id
				left join v_LpuBuilding lbms (nolock) on lbms.LpuBuilding_id = ms.LpuBuilding_id
				{$apply}
			where (1=1)
				{$filter}
				and eqi.ElectronicQueueInfo_IsOff = 1
				and eqi.Lpu_id = :Lpu_id

			order by isnull(lb.LpuBuilding_id, lbms.LpuBuilding_id) desc, MedService_id asc
		";

		$resp = $this->queryResult($query, $params);
		$output = array();

		if (!empty($resp)) {

			$lastGroupName =  $resp[0]['LpuBuilding_Name'];
			$output[0]['GroupName'] = $lastGroupName;
			$output[0]['ElectronicService_id'] = 0;

			foreach ($resp as $key => $svc) {
				$svc['GroupName'] = null;
				if ($svc['LpuBuilding_Name'] !== $lastGroupName) {
					$lastGroupName = $svc['LpuBuilding_Name'];

					$item = array('GroupName' =>(empty($lastGroupName)) ? 'Подразделение не указано' : $lastGroupName, 'ElectronicService_id'=> 0);
					$output[] = $item;
				}
				$output[] = $svc;
			}
		}

		//echo getDebugSQL($query, $params); die();
		return $output;
	}


	/**
	 * Подгрузка комбо с пунктами обслуживания для редиректа талона
	 */
	public function loadRedirectedTalonServices($data) {

		$params['ElectronicTalon_id'] = array(
			'ElectronicTalon_id' => $data['ElectronicTalon_id'],
			'currentElectronicService_id' => $data['currentElectronicService_id']
		);

		$query = "
			select
				es.ElectronicService_id,
				es.ElectronicService_Code,
				es.ElectronicService_Name
			from
				v_ElectronicTalonRedirect etr with (nolock)
				inner join v_ElectronicService es with (nolock) on es.ElectronicService_id = etr.ElectronicService_id
				inner join v_EvnDirection_all ed with (nolock) on ed.EvnDirection_id = etr.EvnDirection_uid
			where (1=1)
				and etr.ElectronicTalon_id = :ElectronicTalon_id
				and etr.ElectronicService_id != :currentElectronicService_id -- не показываем ПО текущий
				and eda.EvnStatus_id != 12 -- отмененные назначения не показываем
			order by
				etr.ElectronicTalonRedirect_id desc
		";

		$resp = $this->queryResult($query, $params);

		$primaryElectronicService = $this->getPrimaryElectronicService($data);
		if (!empty($primaryElectronicService[0]['ElectronicService_id'])) {
			if (!empty($resp[0]['ElectronicService_id'])) {
				$resp = array_merge($resp,$primaryElectronicService);
			} else {
				$resp = $primaryElectronicService;
			}
		}

		return $resp;
	}

	/**
	 * Получаем самый первый пункт обcлуживания при перенаправлении
	 */
	function getPrimaryElectronicService($data) {

		$params['ElectronicTalon_id'] = $data['ElectronicTalon_id'];

		$query = "
			select top 1
				etr.ElectronicService_uid as fromElectronicService_id,
				etr.ElectronicService_uid as ElectronicService_id,
				es.ElectronicService_Code,
				es.ElectronicService_Name
			from
				v_ElectronicTalonRedirect etr with (nolock)
			inner join v_ElectronicService es with (nolock) on es.ElectronicService_id = etr.ElectronicService_uid
			outer apply (
				select
					min(etra.ElectronicTalonRedirect_id) as ElectronicTalonRedirect_id
				from
					v_ElectronicTalonRedirect etra with (nolock)
				inner join v_EvnDirection_all eda with (nolock) on eda.EvnDirection_id = etra.EvnDirection_uid
				where (1=1)
					and etra.ElectronicTalon_id = :ElectronicTalon_id
					and eda.EvnStatus_id != 12 -- отмененные назначения не показываем
			) as min
			where
				etr.ElectronicTalonRedirect_id = min.ElectronicTalonRedirect_id
		";

		//$query = "
		//	select top 1
		//		eth.ElectronicService_id as fromElectronicService_id,
		//		eth.ElectronicService_id as ElectronicService_id,
		//		es.ElectronicService_Code,
		//		es.ElectronicService_Name
		//	from
		//		v_ElectronicTalonHist eth with (nolock)
		//	inner join v_ElectronicService es with (nolock) on es.ElectronicService_id = eth.ElectronicService_id
		//	where eth.ElectronicTalon_id = :ElectronicTalon_id
		//	order by eth.ElectronicTalonHist_id asc
		//";

		$response = $this->queryResult($query, $params);
		return $response;
	}

	/**
	 * Получаем доп. направление пункта обслуживания куда перенаправили талон ЭО
	 */
	function getRedirectedElectronicServiceEvnDirection($data) {

		$params['ElectronicTalon_id'] = $data['ElectronicTalon_id'];
		$params['ElectronicService_id'] = $data['ElectronicService_id'];

		$query = "
			select top 1
				etr.EvnDirection_uid
			from
				v_ElectronicTalonRedirect etr with (nolock)
			outer apply (
				select
					min(etra.ElectronicTalonRedirect_id) as ElectronicTalonRedirect_id
				from
					v_ElectronicTalonRedirect etra with (nolock)
				inner join v_EvnDirection_all eda with (nolock) on eda.EvnDirection_id = etra.EvnDirection_uid
				where (1=1)
					and etra.ElectronicTalon_id = :ElectronicTalon_id
					and etra.ElectronicService_id = :ElectronicService_id
					and eda.EvnStatus_id != 12 -- отмененные назначения не показываем
			) as min
			where
				etr.ElectronicTalonRedirect_id = min.ElectronicTalonRedirect_id
		";

		$response = $this->queryResult($query, $params);
		return $response;
	}

	/**
	 * История талона электронной очереди
	 */
	function getElectronicTalonHistory($data) {

		$params['ElectronicTalon_id'] = $data['ElectronicTalon_id'];

		$query = "
			select
				eth.ElectronicTalon_id,
				eth.ElectronicTalonStatus_id,
				eth.ElectronicService_id,
				eth.pmUser_insID,
				convert(varchar(10), eth.ElectronicTalonHist_insDT, 104) + ' ' + convert(varchar(8), eth.ElectronicTalonHist_insDT, 108) as ElectronicTalonHist_insDT,
				et.ElectronicTalon_Num,
				ets.ElectronicTalonStatus_Name,
				eqi.ElectronicQueueInfo_id,
				case 
					when eth.pmUser_insID = 1 then 'Система'
					when eth.pmUser_insID = 999901 then 'Инфомат'
					when eth.pmUser_insID > 1000000 and eth.pmUser_insID < 5000000 then 'Пользователь портала'
			 		else pmuc.PMUser_Name 
				end as PMUser_Name
			from v_ElectronicTalonHist eth (nolock)
			left join v_ElectronicTalon et (nolock) on et.ElectronicTalon_id = eth.ElectronicTalon_id
			left join v_ElectronicTalonStatus ets (nolock) on ets.ElectronicTalonStatus_id = eth.ElectronicTalonStatus_id
			left join v_ElectronicService es (nolock) on es.ElectronicService_id = eth.ElectronicService_id
			left join v_ElectronicQueueInfo eqi (nolock) on eqi.ElectronicQueueInfo_id = es.ElectronicQueueInfo_id
			left join v_pmUserCache pmuc (nolock) on pmuc.PMUser_id = eth.pmUser_insID
			where (1=1)
				and eth.ElectronicTalon_id = :ElectronicTalon_id
		";

		$response = $this->queryResult($query, $params);
		return $response;
	}

	/**
	 * @param array $data
	 */
	function sendElectronicTalonMessage($data) {
		$notification = $this->getFirstRowFromQuery("
			select top 1 PersonInfo_InternetPhone, PersonInfo_Email
			from v_PersonInfo with(nolock) where Person_id = :Person_id
		", $data);
		if (is_array($notification)) {
			$this->load->helper('Notify');

			if (!empty($notification['PersonInfo_Email'])) {
				sendNotifyEmail(array(
					'EMail' => $notification['PersonInfo_Email'],
					'title' => 'Код бронирования',
					'body' => "Ваш код бронирования: {$data['EvnDirection_TalonCode']} для регистрации в электронной очереди."
				));
			}
			if (!empty($notification['PersonInfo_InternetPhone'])) {
				sendNotifySMS(array(
					'UserNotify_Phone' => $notification['PersonInfo_InternetPhone'],
					'text' => "Ваш код бронирования: {$data['EvnDirection_TalonCode']} для регистрации в электронной очереди.",
					'User_id' => $data['pmUser_id']
				));
			}
		}
	}

	function getGridElectronicQueueData($data) {

		if (empty($data['DirectionList']) || !is_array($data['DirectionList'])) {
			return array();
		}

		$filter = ""; $params = array();

		if (!empty($data['ElectronicTalon_Num'])) {
			$filter .= " and et.ElectronicTalon_Num like '%' + :ElectronicTalon_Num + '%' ";
			$params['ElectronicTalon_Num'] = $data['ElectronicTalon_Num'];
		}

		if (empty($data['ElectronicTalonPseudoStatus_id'])) {
			$filter .= " and et.ElectronicTalonStatus_id in (1,2,3) ";
		}

		$list = implode(',',$data['DirectionList']);

		$query = "				
			select
				case when et.EvnDirection_uid is not null
					then et.EvnDirection_uid
					else et.EvnDirection_id
				end as \"EvnDirection_id\",
				et.ElectronicTalon_Num as \"ElectronicTalon_Num\",
				ets.ElectronicTalonStatus_Name as \"ElectronicTalonStatus_Name\",
				et.ElectronicService_id as \"ElectronicService_id\",
				et.ElectronicTalonStatus_id as \"ElectronicTalonStatus_id\",
				et.ElectronicTalon_id as \"ElectronicTalon_id\",
				et.EvnDirection_uid as \"EvnDirection_uid\",
				etr.ElectronicService_id as \"toElectronicService_id\",
				etr.ElectronicService_uid as \"fromElectronicService_id\",
				et.ElectronicTreatment_id as \"ElectronicTreatment_id\",
				etre.ElectronicTreatment_Name as \"ElectronicTreatment_Name\",
				DATEDIFF(ss, et.ElectronicTalon_insDT, getdate()) as \"ElectronicTalon_TimeHasPassed\",
				coalesce(ttg.TimetableGraf_begTime, ttms.TimetableMedService_begTime, ttr.TimetableResource_begTime) as Timetable_begTime
			from v_ElectronicTalon et (nolock)
				left join v_ElectronicTalonStatus ets (nolock) on ets.ElectronicTalonStatus_id = et.ElectronicTalonStatus_id
				left join v_ElectronicTreatment etre (nolock) on etre.ElectronicTreatment_id = et.ElectronicTreatment_id
				left join v_EvnDirection_all ed (nolock) on ed.EvnDirection_id = et.EvnDirection_id
				left join v_TimetableGraf ttg (nolock) on ttg.EvnDirection_id = ed.EvnDirection_id
				left join v_TimetableMedService ttms (nolock) on ttms.EvnDirection_id = ed.EvnDirection_id
				left join v_TimetableResource ttr (nolock) on ttr.EvnDirection_id = ed.EvnDirection_id
			outer apply(
				select top 1 * from v_ElectronicTalonRedirect etr
				where etr.EvnDirection_uid = et.EvnDirection_uid
			) etr
			where (1=1)
				and (et.EvnDirection_id in ({$list}) or et.EvnDirection_uid in ({$list}))
				{$filter}
		";

		$response = $this->queryResult($query, $params);
		return $response;
	}
}
