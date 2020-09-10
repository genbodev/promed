<?php defined('BASEPATH') or die ('No direct script access allowed');

/**
 * PromedWeb
 *
 * Модель для работы с SOAP-запросами
 *
 * @package                Common
 * @copyright            Copyright (c) 2009 Swan Ltd.
 * @link                http://swan.perm.ru/PromedWeb
 */
class HL7SoapService_model extends swModel
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Обработка полученного HL7 сообщения
	 */
	function handleHL7Message($data, $message)
	{
		$response = '';

		$HL7Message = new HL7_Message($message);
		$eventType = $HL7Message->getEventType();
		switch ($eventType) {
			case 'S01':
				// Метод в HL7 : Регистрация записи на прием
				// Метод в HL7 : Включение в лист ожидания
				$HL7RecordMessage = new HL7_RecordMessage($message);

				$MedStaffFact_id = $HL7RecordMessage->getMedStaffFactId();
				$resp_msf = $this->queryResult("
						select top 1
							MedStaffFact_id,
							MedPersonal_id,
							LpuSection_id,
							LpuSectionProfile_id,
							Person_id,
							Person_SurName,
							Person_FirName,
							Person_SecName,
							Lpu_id
						from
							v_MedStaffFact (nolock)
						where
							MedStaffFact_id = :MedStaffFact_id
					", array(
					'MedStaffFact_id' => $MedStaffFact_id
				));

				if (empty($resp_msf[0]['MedStaffFact_id'])) {
					throw new Exception("Неверно указано рабочее место врача");
				}

				// 1. Смотрим в сегменте APR в последнем поле значение Service_Id. Если = 01, то вызываем метод 2
				if ($HL7RecordMessage->getServiceId() == '01') {
					// "2. Вызываем метод получения свободных дат приема GET api/TimeTableGraf/TimeTableGrafFreeDate с входящими параметрами:
					// ● MedStaffFact_id (N, O) – Место работы врача, берем из поля Наш_Resource_ID
					// ● TimeTableGraf_beg (D, O)  - Дата начала диапазона, берем из поля Дата_Время_С^Дата_Время_ПО
					// ● TimeTableGraf_end (D, O)  - Дата окончания диапазона, берем из поля Дата_Время_С^Дата_Время_ПО
					// В ответе получаем даты, де есть свободные бирки TimeTableGraf_begTime.
					$TimetableGraf_id = null;
					$TimetableGraf_beg = '';
					$TimetableGraf_end = '';

					$prefStart = $HL7RecordMessage->getPrefStart();
					$prefEnd = $HL7RecordMessage->getPrefEnd();
					$prefMon = $HL7RecordMessage->getPrefMon();
					$prefTue = $HL7RecordMessage->getPrefTue();
					$prefWed = $HL7RecordMessage->getPrefWed();
					$prefThu = $HL7RecordMessage->getPrefThu();
					$prefFri = $HL7RecordMessage->getPrefFri();
					$prefSat = $HL7RecordMessage->getPrefSat();
					$prefSun = $HL7RecordMessage->getPrefSun();

					$this->load->model('TimetableGraf_model');
					$resp_td = $this->TimetableGraf_model->getTimeTableGrafFreeDate(array(
						'MedStaffFact_id' => $MedStaffFact_id,
						'TimeTableGraf_beg' => $HL7RecordMessage->getTimeTableGrafBeg(),
						'TimeTableGraf_end' => $HL7RecordMessage->getTimeTableGrafEnd()
					));
					foreach($resp_td as $one_td) {
						if (
							(!empty($prefMon) && $prefMon != 'OK' && date('N', strtotime($one_td['TimeTableGraf_begTime'])) == 1) ||
							(!empty($prefTue) && $prefTue != 'OK' && date('N', strtotime($one_td['TimeTableGraf_begTime'])) == 2) ||
							(!empty($prefWed) && $prefWed != 'OK' && date('N', strtotime($one_td['TimeTableGraf_begTime'])) == 3) ||
							(!empty($prefThu) && $prefThu != 'OK' && date('N', strtotime($one_td['TimeTableGraf_begTime'])) == 4) ||
							(!empty($prefFri) && $prefFri != 'OK' && date('N', strtotime($one_td['TimeTableGraf_begTime'])) == 5) ||
							(!empty($prefSat) && $prefSat != 'OK' && date('N', strtotime($one_td['TimeTableGraf_begTime'])) == 6) ||
							(!empty($prefSun) && $prefSun != 'OK' && date('N', strtotime($one_td['TimeTableGraf_begTime'])) == 7)
						) {
							continue;
						}

						// 3. Вызываем метод получения свободного времени приема GET api/TimeTableGraf/TimeTableGrafFreeTime с входящими параметрами:
						// ● MedStaffFact_id (N, O) – Место работы врача, берем из поля Наш_Resource_ID
						// ● TimeTableGraf_begTime (D, O) – свободная дата приема
						// В ответе получаем массив свободных бирок к врачу:
						// ● TimeTableGraf_id (N, Н) – идентификатор свободной бирки.
						// ● TimeTableGraf_begTime (D, O) –Время начала приема
						// ● TimeTableGraf_Time (N,O) – Длительность приема"
						$resp_tt = $this->TimetableGraf_model->getTimeTableGrafFreeTime(array(
							'MedStaffFact_id' => $MedStaffFact_id,
							'TimeTableGraf_begTime' => $one_td['TimeTableGraf_begTime']
						));

						// "4. Ищем пересечения полученного в сообщении критерия выбора времени и свободных бирок к врачу полученных по методу GET api/TimeTableGraf/TimeTableGrafFreeTime.
						// Если пересечение не пусто, то берем первую по началу времени приема бирку из пересечения."
						foreach($resp_tt as $one_tt) {;
							if (
								(empty($prefStart) || date('Hi', strtotime($one_tt['TimeTableGraf_begTime'])) >= $prefStart) &&
								(empty($prefEnd) || date('Hi', strtotime($one_tt['TimeTableGraf_begTime'])) <= $prefEnd)
							) {
								$TimetableGraf_id = $one_tt['TimeTableGraf_id'];
								$TimetableGraf_beg = date('YmdHis', strtotime($one_tt['TimeTableGraf_begTime']));
								$TimetableGraf_end = date('YmdHis', (strtotime($one_tt['TimeTableGraf_begTime']) + $one_tt['TimeTableGraf_Time'] * 60));
								break;
							}
						}

						if (!empty($TimetableGraf_id)) {
							break;
						}
					}

					// "5. Вызывается метод получения списка пациентов по ключевым параметрам GET api/PersonList:
					// • PersonSurName_SurName (S[50], О) – Фамилия;
					// • PersonFirName_FirName (S[50], Н) – Имя;
					// • PersonSecName_SecName (S[50], Н) – Отчество;
					// • PersonBirthDay_BirthDay (D, О) – Дата рождения;
					// • PersonSnils_Snils (N[11],Н) – СНИЛС.
					// В ответе получаем Person_id. Если найдено несколько записей, то возвращаем ошибку.
					$this->load->model('Person_model');
					$resp_ps = $this->Person_model->loadPersonListForAPI(array(
						'PersonSurName_SurName' => $HL7RecordMessage->getPersonSurName(),
						'PersonFirName_FirName' => $HL7RecordMessage->getPersonFirName(),
						'PersonSecName_SecName' => $HL7RecordMessage->getPersonSecName(),
						'PersonBirthDay_BirthDay' => $HL7RecordMessage->getPersonBirthDay(),
						'PersonSnils_Snils' => $HL7RecordMessage->getPersonSnils()
					));

					if (count($resp_ps['data']) > 1) {
						// Если найдено несколько записей, то возвращаем ошибку.
						throw new Exception("Найдено несколько пациентов, регистрация записи на приём не возможна");
					} else if (count($resp_ps['data']) == 1) {
						$Person_id = $resp_ps['data'][0]['Person_id'];
					} else {
						// 6. Если записей не найдено, то вызываем методы:
						// 1.  создания человека POST api/Person с входящими параметрами:
						// • PersonSurName_SurName (S[50], О) – Фамилия;
						// • PersonFirName_FirName (S[50], Н) – Имя;
						// • PersonSecName_SecName (S[50], Н) – Отчество;
						// • PersonBirthDay_BirthDay (D, О) – Дата рождения;
						// • Person_Sex_id (N, О) – Пол (значение справочника dbo.sex)
						// • PersonPhone_Phone (S, Н) – Телефон;
						// • PersonSnils_Snils (N[11],Н) – СНИЛС;
						// В ответе получаем Person_id
						$resp_saveps = $this->Person_model->savePersonEditWindow(array(
							'Person_SurName' => $HL7RecordMessage->getPersonSurName(),
							'Person_FirName' => $HL7RecordMessage->getPersonFirName(),
							'Person_SecName' => $HL7RecordMessage->getPersonSecName(),
							'Person_BirthDay' => $HL7RecordMessage->getPersonBirthDay(),
							'PersonSex_id' => $HL7RecordMessage->getPersonSex(),
							'PersonPhone_Phone' => $HL7RecordMessage->getPersonPhone(),
							'Person_SNILS' => $HL7RecordMessage->getPersonSnils(),
							'Person_id' => null,
							'SocStatus_id' => null,
							'mode' => 'add',
							'oldValues' => '',
							'Server_id' => $data['Server_id'],
							'pmUser_id' => $data['pmUser_id'],
							'session' => $data['session']
						));
						if (!empty($resp_saveps[0]['Person_id'])) {
							$Person_id = $resp_saveps[0]['Person_id'];
						}

						// 2. создания документа POST api/Document с входящими параметрами:
						// • Person_id (N, Н) – идентификатор пациента
						// • DocumentType_id (N, О) – Тип документа (значение справочника dbo.DocumentType) = 13;
						// • Document_Ser (S, Н) – Серия документа (первые 4 цифры поля ПАСПОРТ);
						// • Document_Num (S, О) – Номер документа (последние 6 цифр поля ПАСПОРТ);
						//   В ответе получаем Person_id и Document_id"
						$Document_Ser = $HL7RecordMessage->getPersonDocumentSer();
						$Document_Num = $HL7RecordMessage->getPersonDocumentNum();
						if (!empty($Person_id) && !empty($Document_Ser) && !empty($Document_Num)) {
							$PersonInfo = $this->getFirstRowFromQuery("
								select top 1 Server_id, PersonEvn_id from v_PersonState PS with(nolock) where PS.Person_id = :Person_id
							", array(
								'Person_id' => $Person_id
							));
							$resp = $this->queryResult("
								declare @ErrCode int
								declare @ErrMsg varchar(400)
					
								exec p_PersonDocument_ins
									@Server_id 			= :Server_id,
									@Person_id 			= :Person_id,
									@PersonDocument_insDT = :PersonDocument_insDT,
									@DocumentType_id 	= :DocumentType_id,
									@OrgDep_id 			= :OrgDep_id,
									@Document_Ser 		= :Document_Ser,
									@Document_Num 		= :Document_Num,
									@Document_begDate 	= :Document_begDate,
									@pmUser_id 			= :pmUser_id,
									@Error_Code 		= @ErrCode output,
									@Error_Message 		= @ErrMsg output
					
								select @ErrMsg as Error_Msg
							", array(
								'Server_id' => $PersonInfo['Server_id'],
								'Person_id' => $Person_id,
								'PersonDocument_insDT' => date('Y-m-d'),
								'DocumentType_id' => 13, // паспорт
								'OrgDep_id' => null,
								'Document_Ser' => $Document_Ser,
								'Document_Num' => $Document_Num,
								'Document_begDate' => null,
								'pmUser_id' => $data['pmUser_id']
							));
							if (isset($resp[0]) && !empty($resp[0]['Error_Msg'])) {
								throw new Exception($resp[0]['Error_Msg']);
							}
						}
					}

					if (empty($Person_id)) {
						throw new Exception("Не удалось создать пациента");
					}

					if (!empty($TimetableGraf_id)) {
						$result = 'RESERVED';
						// "7. Вызываем метод записи пациента на прием POST api/TimeTableGraf/TimeTableGrafWrite с входящими параметрами
						// ● Person_id (N, О) – Идентификатор пациента;
						// ● TimeTableGraf_id (N, O)  - идентификатор свободной бирки, полученной по пункту 4.
						// ● EvnQueue_id (N, H) – идентификатор постановки в очередь, берем Resource_ID^ID_очереди в сегменте APR
						$this->load->helper('Reg');
						$resp_rec = $this->TimetableGraf_model->writeTimetableGraf(array(
							'object' => 'TimetableGraf',
							'Person_id' => $Person_id,
							'TimetableGraf_id' => $TimetableGraf_id,
							'EvnQueue_id' => null,
							'pmUser_id' => $data['pmUser_id'],
							'session' =>  $data['session']
						));

						$RecordId = $Person_id . '^' . $TimetableGraf_id;
					} else {
						$result = 'WAITLIST';
						// 8. Если по пункту 4 нет бирки, то:
						// 1). Вызываем метод получения места работы по идентификатору GET api/MedStaffFactByid для получения значения поля LpuSection_id
						// 2). По идентификатору LpuSection_id отделения получаем LpuSectionProfile_id
						// 3). Вызываем метод добавления записи в лист ожидания POST api/EvnQueue/EvnQueue с   входящими параметрами:
						// ● Person_id (N, О) – Идентификатор пациента;
						// ● LpuSectionProfile_id (N, O) – Идентификатор профиля отделения МО
						// ● LpuSection_id (N, O) – Идентификатор отделения
						// Получаем идентификатор постановки в очередь EvnQueue_id"
						$this->load->model('EvnQueue_model');
						$resp_rec = $this->EvnQueue_model->saveEvnQueue(array(
							'Person_id' => $Person_id,
							'LpuSectionProfile_id' => $resp_msf[0]['LpuSectionProfile_id'],
							'LpuSection_id' => $resp_msf[0]['LpuSection_id'],
							'Lpu_id' => $data['Lpu_id'],
							'pmUser_id' => $data['pmUser_id']
						));

						$RecordId = $Person_id . '*' . $resp_rec['EvnQueue_id'];
					}

					// "В подтверждении приёма такого сообщения поле ""Уникальный_ID_сообщения"" заполняется значением, построенным по следующему правилу:
					$uniqueId = 'UREG' . date('YmdHis') . '*';

					// Формируем ответ
					$response = 'MSH|^~\&|' . $HL7RecordMessage->getReceivingApplication() . '|' . $HL7RecordMessage->getReceivingFacility() . '|' . $HL7RecordMessage->getSendingApplication() . '|' . $HL7RecordMessage->getSendingFacility() . '|' . date('YmdHis') . '||SQR^' . $eventType . '^SQR_' . $eventType . '|' . $uniqueId . '|P|2.5||||||UNICODE UTF-8' . PHP_EOL;
					// Если в процессе приема и исполнения сообщения возникает ошибка, то передаем значение AR, если успешно, то АА
					$response .= 'MSA|AA|' . $uniqueId . PHP_EOL;
					// "В поле SCH-2 передаем значение:
					// - Person_id^TimeTableGraf_id, если получили успешный ответ по rest методу 7
					// - EvnQueue_id, если получили успешный ответ по rest методу 8
					// В поле  SCH-25 передаем значение:
					// - RESERVED, если получили успешный ответ по rest методу 7
					// - WAITLIST, если получили успешный ответ по rest методу 8"
					$response .= 'SCH|0000000000001|'.$RecordId.'|||'.$MedStaffFact_id.'||||||||||||||||||||' . $result . PHP_EOL;
					// Передаем TimeTableGraf_beg|TimeTableGraf_beg+TimeTableGraf_Time, если получили успешный ответ по rest методу 6
					$response .= 'TQ1|0||||||' . $TimetableGraf_beg . '|' . $TimetableGraf_end . PHP_EOL;
					$response .= $HL7RecordMessage->getSegment('PID')->getAsString() . PHP_EOL;
					$response .= 'RGS|0' . PHP_EOL;
					$response .= 'AIS|0||' . $HL7RecordMessage->getUslugaId() . PHP_EOL;
					$response .= 'AIP|0||' . $resp_msf[0]['Person_id'] . '^' . $resp_msf[0]['Person_SurName'] . '^' . $resp_msf[0]['Person_FirName'] . '^' . $resp_msf[0]['Person_SecName'];
				} else if ($HL7RecordMessage->getServiceId() == '02') {
					// если = 02, то переходим к вызову врача на дом

					// "2. Вызывается метод получения списка пациентов по ключевым параметрам GET api/PersonList:
					// • PersonSurName_SurName (S[50], О) – Фамилия;
					// • PersonFirName_FirName (S[50], Н) – Имя;
					// • PersonSecName_SecName (S[50], Н) – Отчество;
					// • PersonBirthDay_BirthDay (D, О) – Дата рождения;
					// • PersonSnils_Snils (N[11],Н) – СНИЛС.
					// В ответе получаем Person_id. Если найдено несколько записей, то возвращаем ошибку.
					$this->load->model('Person_model');
					$resp_ps = $this->Person_model->loadPersonListForAPI(array(
						'PersonSurName_SurName' => $HL7RecordMessage->getPersonSurName(),
						'PersonFirName_FirName' => $HL7RecordMessage->getPersonFirName(),
						'PersonSecName_SecName' => $HL7RecordMessage->getPersonSecName(),
						'PersonBirthDay_BirthDay' => $HL7RecordMessage->getPersonBirthDay(),
						'PersonSnils_Snils' => $HL7RecordMessage->getPersonSnils()
					));

					if (count($resp_ps['data']) > 1) {
						// Если найдено несколько записей, то возвращаем ошибку.
						throw new Exception("Найдено несколько пациентов, регистрация вызова врача на дом не возможна");
					} else if (count($resp_ps['data']) == 1) {
						$Person_id = $resp_ps['data'][0]['Person_id'];
					} else {
						// 3. Если записей не найдено, то вызываем методы:
						// 1.  создания человека POST api/Person с входящими параметрами:
						// • PersonSurName_SurName (S[50], О) – Фамилия;
						// • PersonFirName_FirName (S[50], Н) – Имя;
						// • PersonSecName_SecName (S[50], Н) – Отчество;
						// • PersonBirthDay_BirthDay (D, О) – Дата рождения;
						// • Person_Sex_id (N, О) – Пол (значение справочника dbo.sex)
						// • PersonPhone_Phone (S, Н) – Телефон;
						// • PersonSnils_Snils (N[11],Н) – СНИЛС;
						// В ответе получаем Person_id
						$resp_saveps = $this->Person_model->savePersonEditWindow(array(
							'Person_SurName' => $HL7RecordMessage->getPersonSurName(),
							'Person_FirName' => $HL7RecordMessage->getPersonFirName(),
							'Person_SecName' => $HL7RecordMessage->getPersonSecName(),
							'Person_BirthDay' => $HL7RecordMessage->getPersonBirthDay(),
							'PersonSex_id' => $HL7RecordMessage->getPersonSex(),
							'PersonPhone_Phone' => $HL7RecordMessage->getPersonPhone(),
							'Person_SNILS' => $HL7RecordMessage->getPersonSnils(),
							'Person_id' => null,
							'SocStatus_id' => null,
							'mode' => 'add',
							'oldValues' => '',
							'Server_id' => $data['Server_id'],
							'pmUser_id' => $data['pmUser_id'],
							'session' => $data['session']
						));
						if (!empty($resp_saveps[0]['Person_id'])) {
							$Person_id = $resp_saveps[0]['Person_id'];
						}

						// 2. создания документа POST api/Document с входящими параметрами:
						// • Person_id (N, Н) – идентификатор пациента
						// • DocumentType_id (N, О) – Тип документа (значение справочника dbo.DocumentType) = 13;
						// • Document_Ser (S, Н) – Серия документа (первые 4 цифры поля ПАСПОРТ);
						// • Document_Num (S, О) – Номер документа (последние 6 цифр поля ПАСПОРТ);
						//   В ответе получаем Person_id и Document_id"
					}

					if (empty($Person_id)) {
						throw new Exception("Не удалось создать пациента");
					}

					// "4. Вызываем метод добавления вызова врача на дом POST api/HomeVisit/HomeVisit с входящими параметрами
					// ● Person_id (N, О) – Идентификатор пациента;
					// ● CallProfType_id (N, O) – Идентификатор профиля вызова. Значение берем из сегмента APR. (1, если ID_услуги = 001; 2, если ID_услуги = 002).
					// ● AdressHomeVisit_id (N, O)  - Идентификатор адреса вызова. Добавляем адрес по справочнику  КЛАДРа, значение берем из сегмента PID. Страна по умолчанию = РОССИЯ.
					// ● HomeVisitCallType_id (N, O) – Идентификатор типа вызова. Значение = 2.
					// ● HomeVisit_setDT (DT, O)  - Дата и время вызова. Значение берем из последнего поля сегмента ARQ
					// ● HomeVisit_Num (T, O) - Номер вызова. Проставляем автонумератором.
					// ● MedStaffFact_id (N, O) – Место работы врача. Значение берем из сегмента ARQ (Наш_Resource_ID).
					// ● HomeVisit_Phone (T, O) – Телефон обратной связи. Значение берем из последнего поля сегмента PID
					// ● HomeVisitWhoCall_id (N, O) – Идентификатор вызвавшего врача. Значение = 4.
					// ● HomeVisit_Symptoms (T, O) – Симптомы. Значение = ""Не определены"".
					// ● HomeVisitStatus_id (N, O) – Идентификатор статуса вызова. Значение = 6.
					// В ответе получаем HomeVisit_id"

					$this->load->model('HomeVisit_model');

					// номер из нумератора
					$HomeVisit_Num = $this->HomeVisit_model->getHomeVisitNum(array(
						'Lpu_id' => $resp_msf[0]['Lpu_id'],
						'onDate' => date('Y-m-d', strtotime($HL7RecordMessage->getTimeTableGrafBeg())),
						'Numerator_id' => null,
						'pmUser_id' => $data['pmUser_id']
					));
					if (!empty($HomeVisit_Num['Error_Msg'])) {
						throw new Exception('Ошибка получения номера вызова врача на дом: '.$HomeVisit_Num['Error_Msg']);
					} else if (!isset($HomeVisit_Num['Numerator_Num'])) {
						throw new Exception('Ошибка получения номера вызова врача на дом');
					}

					$HomeVisit_setDT = date('YmdHis', strtotime($HL7RecordMessage->getHomeVisitSetDT()));

					$resp_hv = $this->HomeVisit_model->addHomeVisit(array(
						'Lpu_id' => $resp_msf[0]['Lpu_id'],
						'MedPersonal_id' => $resp_msf[0]['MedPersonal_id'],
						'Person_id' => $Person_id,
						'CallProfType_id' => $HL7RecordMessage->getUslugaId() == '002'?2:1,
						'Address_Address' => $HL7RecordMessage->getPersonAddress(),
						'HomeVisitCallType_id' => 2,
						'HomeVisit_setDT' => $HL7RecordMessage->getHomeVisitSetDT(),
						'HomeVisit_Num' => $HomeVisit_Num['Numerator_Num'],
						'MedStaffFact_id' => $HL7RecordMessage->getMedStaffFactId(),
						'HomeVisit_Phone' => $HL7RecordMessage->getPersonPhone(),
						'HomeVisitWhoCall_id' => 4,
						'HomeVisit_Symptoms' => 'Не определены',
						'HomeVisitStatus_id' => 6,
						'pmUser_id' => $data['pmUser_id']
					));

					if (!empty($resp_hv[0]['Error_Msg'])) {
					} else if (empty($resp_hv[0]['HomeVisit_id'])) {
						throw new Exception('Ошибка создания вызова врача на дом');
					}

					$RecordId = $resp_hv[0]['HomeVisit_id'];

					// "В подтверждении приёма такого сообщения поле ""Уникальный_ID_сообщения"" заполняется значением, построенным по следующему правилу:
					$uniqueId = 'UREG' . date('YmdHis') . '*';

					// Формируем ответ
					$response = 'MSH|^~\&|' . $HL7RecordMessage->getReceivingApplication() . '|' . $HL7RecordMessage->getReceivingFacility() . '|' . $HL7RecordMessage->getSendingApplication() . '|' . $HL7RecordMessage->getSendingFacility() . '|' . date('YmdHis') . '||SQR^' . $eventType . '^SQR_' . $eventType . '|' . $uniqueId . '|P|2.5||||||UNICODE UTF-8' . PHP_EOL;
					// Если в процессе приема и исполнения сообщения возникает ошибка, то передаем значение AR, если успешно, то АА
					$response .= 'MSA|AA|' . $uniqueId . PHP_EOL;
					// "В поле SCH-2 передаем значение:
					// - Person_id^TimeTableGraf_id, если получили успешный ответ по rest методу 7
					// - EvnQueue_id, если получили успешный ответ по rest методу 8
					// В поле  SCH-25 передаем значение:
					// - RESERVED, если получили успешный ответ по rest методу 7
					// - WAITLIST, если получили успешный ответ по rest методу 8"
					$response .= 'SCH|0000000000001|'.$RecordId.'|||'.$MedStaffFact_id.'||||||||||||||||||||STARTED' . PHP_EOL;
					// Передаем TimeTableGraf_beg|TimeTableGraf_beg+TimeTableGraf_Time, если получили успешный ответ по rest методу 6
					$response .= 'TQ1|0||||||' . $HomeVisit_setDT . '|' . PHP_EOL;
					$response .= $HL7RecordMessage->getSegment('PID')->getAsString() . PHP_EOL;
					$response .= 'RGS|0' . PHP_EOL;
					$response .= 'AIS|0||' . $HL7RecordMessage->getUslugaId() . PHP_EOL;
					$response .= 'AIP|0||' . $resp_msf[0]['Person_id'] . '^' . $resp_msf[0]['Person_SurName'] . '^' . $resp_msf[0]['Person_FirName'] . '^' . $resp_msf[0]['Person_SecName'];
				}

				break;
			case 'S04': // Метод в HL7 : Отмена записи на прием
			case 'S06': // Метод в HL7 : Отказ в записи на прием
				$HL7CancelMessage = new HL7_CancelMessage($message);

				$slotId = $HL7CancelMessage->getSlotId();
				if (mb_strpos($slotId, '^') !== false) {
					$Person_id = $HL7CancelMessage->getPersonId();

					// 1. Если передан Person_id^TimeTableGraf_id
					// то вызываем метод изменения статуса записи на прием PUT api/TimeTableGraf/TimeTableGrafStatus с входящими параметрами:
					// ● Person_id (N, O) - Идентификатор пациента, берем из сегмента FRQ поля (EI) 00861 значение до символа ^.
					// ● TimeTableGraf_id (N, O) – Идентификатор бирки, берем из сегмента FRQ поля (EI) 00861 значение после символа ^.
					// ● EvnStatus_id (N,O) – Идентификатор статуса направления, значение = 6 (Отмена)."
					$this->load->model('TimetableGraf_model');
					$resp = $this->TimetableGraf_model->setTimeTableGrafStatus(array(
						'Person_id' => $HL7CancelMessage->getPersonId(),
						'TimeTableGraf_id' => $HL7CancelMessage->getTimeTableGrafId(),
						'EvnStatus_id' => 12, // отменено
						'pmUser_id' => $data['pmUser_id']
					));
					if (!empty($resp['Error_Msg'])) {
						throw new Exception($resp['Error_Msg']);
					}
				} else if (mb_strpos($slotId, '*') !== false) {
					// 2. Если передан Person_id*EvnQueue_id, то вызываем метод изменения статуса записи в листе ожидания с входящими параметрами:
					// ● EvnQueue_id (N, O) – идентификатор постановки в очередь.
					// ● QueueFailCause_id (N, O) – Идентификатор причины изменения порядка в очереди. Значение = 11 (Ошибочное направление).
					// ● EvnStatus_id (N,O) – Идентификатор статуса направления, значение = 6 (Отмена).
					$slotParts = explode('*', $slotId);
					$Person_id = $slotParts[0];
					$this->load->model('EvnQueue_model');
					$resp = $this->EvnQueue_model->setQueueFailCause(array(
						'EvnQueue_id' => $slotParts[1],
						'QueueFailCause_id' => 11,
						'EvnStatus_id' => 12, // отменено
						'pmUser_id' => $data['pmUser_id']
					));
					if (!empty($resp['Error_Msg'])) {
						throw new Exception($resp['Error_Msg']);
					}
				} else {
					$this->load->model('HomeVisit_model');
					// нужно получить данные с HomeVisit
					$resp_hv = $this->HomeVisit_model->getHomeVisitForAPI(array(
						'HomeVisit_id' => $slotId
					));

					if (empty($resp_hv[0])) {
						throw new Exception("Не удалось получить данные вызова врача на дом");
					}

					if ($resp_hv[0]['HomeVisitStatus_id'] == 5 || $resp_hv[0]['HomeVisitStatus_id'] == 2) {
						throw new Exception("Вызова врача на дом уже отменён");
					}

					$Person_id = $resp_hv[0]['Person_id'];

					// 2. Если передан HomeVisit_id, то вызываем метод изменения статуса вызова врача на дом PUT api/HomeVisitStatus/HomeVisitStatus с входящими параметрами:
					// ● HomeVisitStatus_id (N, O) – Идентификатор статуса вызова. Значение = 5 (Отменен)
					// ● HomeVisit_id (N, O) - Идентификатор вызова на дом.
					$resp = $this->HomeVisit_model->setHomeVisitStatus(array(
						'HomeVisit_id' => $slotId,
						'HomeVisitStatus_id' => ($eventType == 'S04')?5:2,
						'pmUser_id' => $data['pmUser_id']
					));
				}

				$result = "CANCELLED";
				if ($eventType == 'S06') {
					$result = "DELETED";
				}

				// "В подтверждении приёма такого сообщения поле ""Уникальный_ID_сообщения"" заполняется значением, построенным по следующему правилу:
				$uniqueId = 'UREG' . date('YmdHis') . '*';

				// Формируем ответ
				$response = 'MSH|^~\&|' . $HL7CancelMessage->getReceivingApplication() . '|' . $HL7CancelMessage->getReceivingFacility() . '|' . $HL7CancelMessage->getSendingApplication() . '|' . $HL7CancelMessage->getSendingFacility() . '|' . date('YmdHis') . '||SRR^' . $eventType . '^SRR_' . $eventType . '|' . $uniqueId . '|P|2.5||||||UNICODE UTF-8' . PHP_EOL;
				$response .= 'MSA|AA|' . $uniqueId . PHP_EOL;
				// "2. В поле SCH-2 передаем Person_id^TimeTableGraf_id.
				// В поле  SCH-25 передаем значение:
				// - CANCELED, если получили успешный ответ по rest методу 1
				// - ошибку, в остальных случаях."

				$UslugaId = '001';

				if (mb_strpos($slotId, '^') !== false) {
					// "5. Вызываем метод получения атрибутов бирки по идентификатору GET api/TimeTableGraf/TimeTableGrafById - получаем MedStaffFact_id
					$resp_tt = $this->TimetableGraf_model->getTimeTableGrafById(array(
						'TimeTableGraf_id' => $HL7CancelMessage->getTimeTableGrafId()
					));
					$TimetableGraf_Time = "";
					$TimetableGraf_beg = "";
					$TimetableGraf_end = "";
					$MedStaffFact_id = null;
					if (!empty($resp_tt[0])) {
						$TimetableGraf_Time = $resp_tt[0]['TimetableGraf_Time'];
						$TimetableGraf_beg = date('YmdHis', strtotime($resp_tt[0]['TimeTableGraf_begTime']));
						$TimetableGraf_end = date('YmdHis', (strtotime($resp_tt[0]['TimeTableGraf_begTime']) + $TimetableGraf_Time * 60));
						$MedStaffFact_id = $resp_tt[0]['MedStaffFact_id'];
					}

					$response .= 'SCH|' . $HL7CancelMessage->getMisSlotId() . '|' . $HL7CancelMessage->getSlotId() . '|||' . $MedStaffFact_id . '||||||||||||||||||||' . $result . PHP_EOL;
					// 3. Передаем TimeTableGraf_beg|TimeTableGraf_beg+TimeTableGraf_Time
					$response .= 'TQ1|0||||||' . $TimetableGraf_beg . '|' . $TimetableGraf_end . PHP_EOL;
				} else if (mb_strpos($slotId, '*') !== false) {
					$MedStaffFact_id = null;
					$response .= 'SCH|' . $HL7CancelMessage->getMisSlotId() . '|' . $HL7CancelMessage->getSlotId() . '|||||||||||||||||||||||' . $result . PHP_EOL;
					$response .= 'TQ1|0|||||||' . PHP_EOL;
				} else {
					if ($resp_hv[0]['CallProfType_id'] == 2) {
						$UslugaId = '002';
					}

					$MedStaffFact_id = $resp_hv[0]['MedStaffFact_id'];

					$response .= 'SCH|' . $HL7CancelMessage->getMisSlotId() . '|' . $HL7CancelMessage->getSlotId() . '|||' . $MedStaffFact_id . '||||||||||||||||||||' . $result . PHP_EOL;
					$response .= 'TQ1|0||||||' . (date('YmdHis', strtotime($resp_hv[0]['HomeVisit_setDT']))) . '|' . PHP_EOL;
				}

				// Получаем данные по пациенту для сегмента PID
				$resp_ps = $this->queryResult("
					select
						ps.Person_id,
						ps.Person_Snils,
						ps.Polis_Ser,
						ps.Polis_Num,
						ps.Document_Ser,
						ps.Document_Num,
						d.DocumentType_id,
						dt.DocumentType_Name,
						ps.Person_SurName,
						ps.Person_FirName,
						ps.Person_SecName,
						convert(varchar(10), ps.Person_BirthDay, 120) as Person_BirthDay,
						ps.Sex_id,
						a.Address_Zip,
						a.KLRGN_Name,
						a.KLSubRGN_Name,
						a.KLCity_Name,
						a.KLTown_Name,
						a.KLStreet_Name,
						a.Address_House,
						a.Address_Corpus,
						a.Address_Flat,
						ISNULL(ps.Person_Phone, '') as Person_Phone
					from
						v_PersonState ps (nolock)
						left join v_Document d (nolock) on d.Document_id = ps.Document_id
						left join v_DocumentType dt (nolock) on dt.DocumentType_id = d.DocumentType_id
						left join v_Address_all a (nolock) on a.Address_id = ps.PAddress_id
					where
						ps.Person_id = :Person_id
				", array(
					'Person_id' => $Person_id
				));
				if (!empty($resp_ps[0]['Person_id'])) {
					$documentInfo = "";
					if (!empty($resp_ps[0]['Person_Snils'])) {
						if (!empty($documentInfo)) {
							$documentInfo .= '~';
						}
						$documentInfo .= $resp_ps[0]['Person_Snils'].'^СНИЛС';
					}
					if (!empty($resp_ps[0]['Polis_Num'])) {
						if (!empty($documentInfo)) {
							$documentInfo .= '~';
						}
						if (!empty($resp_ps[0]['Polis_Ser'])) {
							$documentInfo .= $resp_ps[0]['Polis_Ser'];
						}
						$documentInfo .= $resp_ps[0]['Polis_Num'].'^ОМС';
					}
					if (!empty($resp_ps[0]['Document_Num'])) {
						$documentType = 'ПАСПОРТ';
						if ($resp_ps[0]['DocumentType_id'] != 13) { // Если значение <>13 (паспорт РФ), то передаем в сегмент PID вместо слова ПАСПОРТ значение DocumentType_Name,
							$documentType = $resp_ps[0]['DocumentType_Name'];
						}
						if (!empty($documentInfo)) {
							$documentInfo .= '~';
						}
						if (!empty($resp_ps[0]['Document_Ser'])) {
							$documentInfo .= $resp_ps[0]['Document_Ser'];
						}
						$documentInfo .= $resp_ps[0]['Document_Num'].'^'.$documentType;
					}
					$PersonSex = '';
					if ($resp_ps[0]['Sex_id'] == 1) {
						$PersonSex = 'M';
					} else if ($resp_ps[0]['Sex_id'] == 2) {
						$PersonSex = 'F';
					}
					$PersonAddress = '';
					if (!empty($resp_ps[0]['Address_Zip'])) {
						if (!empty($PersonAddress)) {
							$PersonAddress .= '^';
						}
						$PersonAddress .= $resp_ps[0]['Address_Zip'];
					}
					if (!empty($resp_ps[0]['KLRGN_Name'])) {
						if (!empty($PersonAddress)) {
							$PersonAddress .= '^';
						}
						$PersonAddress .= $resp_ps[0]['KLRGN_Name'];
					}
					if (!empty($resp_ps[0]['KLSubRGN_Name'])) {
						if (!empty($PersonAddress)) {
							$PersonAddress .= '^';
						}
						$PersonAddress .= $resp_ps[0]['KLSubRGN_Name'];
					}
					if (!empty($resp_ps[0]['KLCity_Name'])) {
						if (!empty($PersonAddress)) {
							$PersonAddress .= '^';
						}
						$PersonAddress .= $resp_ps[0]['KLCity_Name'];
					}
					if (!empty($resp_ps[0]['KLTown_Name'])) {
						if (!empty($PersonAddress)) {
							$PersonAddress .= '^';
						}
						$PersonAddress .= $resp_ps[0]['KLTown_Name'];
					}
					if (!empty($resp_ps[0]['KLStreet_Name'])) {
						if (!empty($PersonAddress)) {
							$PersonAddress .= '^';
						}
						$PersonAddress .= $resp_ps[0]['KLStreet_Name'];
					}
					if (!empty($resp_ps[0]['Address_House'])) {
						if (!empty($PersonAddress)) {
							$PersonAddress .= '^';
						}
						$PersonAddress .= $resp_ps[0]['Address_House'];
					}
					if (!empty($resp_ps[0]['Address_Corpus'])) {
						if (!empty($PersonAddress)) {
							$PersonAddress .= '^';
						}
						$PersonAddress .= $resp_ps[0]['Address_Corpus'];
					}
					if (!empty($resp_ps[0]['Address_Flat'])) {
						if (!empty($PersonAddress)) {
							$PersonAddress .= '^';
						}
						$PersonAddress .= $resp_ps[0]['Address_Flat'];
					}
					$response .= 'PID|0||' . $documentInfo . '||' . $resp_ps[0]['Person_SurName'] . '^' . $resp_ps[0]['Person_FirName'] . '^' . $resp_ps[0]['Person_SecName'] . '||' . date('Ymd', strtotime($resp_ps[0]['Person_BirthDay'])) . '|' . $PersonSex . '|||' . $PersonAddress . '||' . $resp_ps[0]['Person_Phone'] . PHP_EOL;
				} else {
					$response .= 'PID|0||||||||||||' . PHP_EOL;
				}
				$response .= 'RGS|0' . PHP_EOL;
				$response .= 'AIS|0||' . $UslugaId . PHP_EOL;
				$MedPerson_id = '';
				$MedPersonal_SurName = '';
				$MedPersonal_FirName = '';
				$MedPersonal_SecName = '';
				if (!empty($MedStaffFact_id)) {
					// 6. Вызываем метод получения места работы по идентификатору GET api/MedStaffFactByid - получаем MedPersonal_id
					$this->load->model('MedStaffFact_model');
					$resp_msf = $this->MedStaffFact_model->loadMedStaffFactById(array(
						'MedStaffFact_id' => $MedStaffFact_id
					));
					if (!empty($resp_msf[0]['MedPersonal_id'])) {
						// 7. Вызываем метод получения сотрудника по идентификатору GET api/MedWorkerById - получаем Person_id
						$this->load->model('MedWorker_model');
						$resp_mw = $this->MedWorker_model->getMedWorkerById(array(
							'MedWorker_id' => $resp_msf[0]['MedPersonal_id']
						));

						if (!empty($resp_mw[0]['Person_id'])) {
							$MedPerson_id = $resp_mw[0]['Person_id'];
							// 8. Вызываем метод получения GET api/Person по Person_id. Получаем в результате и передаем в сегмент AIP:
							// • PersonSurName_SurName (S[50], O) – Фамилия;
							// • PersonFirName_FirName (S[50], O) – Имя;
							// • PersonSecName_SecName (S[50], H) – Отчество;"
							$this->load->model('Person_model');
							$resp_mwps = $this->Person_model->loadPersonListForAPI(array(
								'Person_id' => $resp_mw[0]['Person_id']
							));
							if (!empty($resp_mwps['data'][0]['Person_id'])) {
								$MedPersonal_SurName = $resp_mwps['data'][0]['PersonSurName_SurName'];
								$MedPersonal_FirName = $resp_mwps['data'][0]['PersonFirName_FirName'];
								$MedPersonal_SecName = $resp_mwps['data'][0]['PersonSecName_SecName'];
							}
						}
					}

				}
				$response .= 'AIP|0||' . $MedPerson_id . '^' . $MedPersonal_SurName . '^' . $MedPersonal_FirName . '^' . $MedPersonal_SecName;

				break;
			case 'S25': // Метод в HL7 : Получение расписания врача
				$HL7GetTimetableMessage = new HL7_GetTimetableMessage($message);
				// 1. Вызываем метод получения свободных дат приема GET api/TimeTableGraf/TimeTableGrafFreeDate с входящими параметрами:
				// ● MedStaffFact_id (N, O) – Место работы врача, берем из сегмента сообщения QRD - 8
				// ● TimeTableGraf_beg (D, O)  - Дата начала диапазона, берем из сегмента QRF - 9
				// ● TimeTableGraf_end (D, O)  - Дата окончания диапазона, берем из сегмента QRF - 9
				// В ответе получаем даты, де есть свободные бирки TimeTableGraf_begTime.
				$this->load->model('TimetableGraf_model');
				$resp = $this->TimetableGraf_model->getTimeTableGrafFreeDate(array(
					'MedStaffFact_id' => $HL7GetTimetableMessage->getMedStaffFactId(),
					'TimeTableGraf_beg' => date('Y-m-d', strtotime($HL7GetTimetableMessage->getTimeTableGrafBeg())),
					'TimeTableGraf_end' => date('Y-m-d', strtotime($HL7GetTimetableMessage->getTimeTableGrafEnd()))
				));
				$resp_tt = array();
				if (!empty($resp[0])) {
					// 2. Вызываем метод получения свободного времени приема GET api/TimeTableGraf/TimeTableGrafFreeTime с входящими параметрами:
					// ● MedStaffFact_id (N, O) – Место работы врача, берем из сегмента сообщения AIP
					// ● TimeTableGraf_begTime (D, O) – свободная дата приема
					// В ответе получаем массив свободных бирок к врачу:
					// ● TimeTableGraf_id (N, Н) – идентификатор свободной бирки.
					// ● TimeTableGraf_begTime (D, O) –Время начала приема
					// ● TimeTableGraf_Time (N,O) – Длительность приема
					foreach($resp as $one_resp) {
						$resp_tt_temp = $this->TimetableGraf_model->getTimeTableGrafFreeTime(array(
							'MedStaffFact_id' => $HL7GetTimetableMessage->getMedStaffFactId(),
							'TimeTableGraf_begTime' => $one_resp['TimeTableGraf_begTime']
						));

						foreach($resp_tt_temp as $one_tt_temp) {
							if (
								strtotime($one_tt_temp['TimeTableGraf_begTime']) >= strtotime($HL7GetTimetableMessage->getTimeTableGrafBeg()) &&
								strtotime($one_tt_temp['TimeTableGraf_begTime']) <= strtotime($HL7GetTimetableMessage->getTimeTableGrafEnd())
							) {
								$resp_tt[] = $one_tt_temp;
							}
						}
					}
				}

				$result = "NF";
				if (!empty($resp_tt[0]['TimeTableGraf_id'])) {
					$result = "OK";
				}

				// "В подтверждении приёма такого сообщения поле ""Уникальный_ID_сообщения"" заполняется значением, построенным по следующему правилу:
				$uniqueId = 'UREG' . date('YmdHis') . '*';

				// Формируем ответ
				$response = 'MSH|^~\&|' . $HL7GetTimetableMessage->getReceivingApplication() . '|' . $HL7GetTimetableMessage->getReceivingFacility() . '|' . $HL7GetTimetableMessage->getSendingApplication() . '|' . $HL7GetTimetableMessage->getSendingFacility() . '|' . date('YmdHis') . '||SQR^' . $eventType . '^SQR_' . $eventType . '|' . $uniqueId . '|P|2.5||||||UNICODE UTF-8' . PHP_EOL;
				$response .= 'QAK|' . $HL7GetTimetableMessage->getQueryIdent() . '|' . $result . PHP_EOL;
				foreach($resp_tt as $one_tt) {
					$response .= 'SCH|' . $one_tt['TimeTableGraf_id'] . '|||||||||||||OPEN' . PHP_EOL;
					$response .= 'TQ1|0|' . $one_tt['TimeTableGraf_Time'] . '|||||' . date('YmdHis', strtotime($one_tt['TimeTableGraf_begTime'])) . '|' . date('YmdHis', (strtotime($one_tt['TimeTableGraf_begTime']) + $one_tt['TimeTableGraf_Time'] * 60)) . PHP_EOL;
				}
				$response .= $HL7GetTimetableMessage->getSegment('RGS')->getAsString() . PHP_EOL;
				$response .= $HL7GetTimetableMessage->getSegment('AIS')->getAsString() . PHP_EOL;
				$response .= $HL7GetTimetableMessage->getSegment('AIP')->getAsString();

				break;
			case 'M01': // Метод в HL7 : Получение списка врачей
				$HL7GetMedStaffMessage = new HL7_GetMedStaffMessage($message);
				// "1. Вызываем метод получения списка специальностей в МО GET api/MedSpecOms/MedSpecOmsByMO с входящим параметром:
				//  ● Lpu_id (N, O)  - Идентификатор МО - значение берем из последнего поля сегмента QRD
				$this->load->model('MedStaffFact_model');
				$resp_ms = $this->MedStaffFact_model->getMedSpecOmsByMo(array(
					'Lpu_id' => $HL7GetMedStaffMessage->getLpuId()
				));
				$resp_msf = array();
				foreach($resp_ms as $one_ms) {
					// 2. По всем полученным специальностям запускаем метод GET api/MedStaffFact/MedStaffFactByMO с входящими параметрами:
					// ● MedSpecOms_id (N, O) - Идентификатор специальности
					// ● Lpu_id  (N, O)  - Идентификатор МО
					// В результатет получаем MedStaffFact_id, Person_id и ФИО"
					$resp_msf_temp = $this->MedStaffFact_model->getMedStaffFactByMo(array(
						'Lpu_id' => $HL7GetMedStaffMessage->getLpuId(),
						'MedSpecOms_id' => $one_ms['MedSpecOms_id']
					));

					foreach($resp_msf_temp as $one_msf_temp) {
						$one_msf_temp['MedSpecOms_id'] = $one_ms['MedSpecOms_id'];
						$resp_msf[] = $one_msf_temp;
					}
				}

				// "В подтверждении приёма такого сообщения поле ""Уникальный_ID_сообщения"" заполняется значением, построенным по следующему правилу:
				$uniqueId = 'UREG' . date('YmdHis') . '*';

				// Формируем ответ
				$response = 'MSH|^~\&|' . $HL7GetMedStaffMessage->getReceivingApplication() . '|' . $HL7GetMedStaffMessage->getReceivingFacility() . '|' . $HL7GetMedStaffMessage->getSendingApplication() . '|' . $HL7GetMedStaffMessage->getSendingFacility() . '|' . date('YmdHis') . '||MFR^' . $eventType . '^MFR_' . $eventType . '|' . $uniqueId . '|P|2.5||||||UNICODE UTF-8' . PHP_EOL;
				$response .= 'MSA|AA|' . $uniqueId . PHP_EOL;
				$response .= 'MFI|0001^HL7|||||AL';
				foreach($resp_msf as $one_msf) {
					$response .= PHP_EOL . 'MFE|' . $one_msf['MedStaffFact_id'];
					$response .= PHP_EOL . 'STF||' . $one_msf['Person_id'] . '|' . $one_msf['PersonSurName_SurName'] . '^' . $one_msf['PersonFirName_FirName'] . '^' . $one_msf['PersonSecName_SecName'] . '|' . $one_msf['MedSpecOms_id'] . '^';
				}

				break;
		}

		return new SendResponse(
			new ERGate_MessageData(
				$response
			)
		);
	}
}
// END MedPersonal_model class
