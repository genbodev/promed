<?php
/**
 * Polka_PersonCard_model - модель, для работы с таблицей Personcard
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Stanislav Bykov (savage@swan.perm.ru)
 * @version      11.12.2013
 */
require_once(APPPATH.'models/Polka_PersonCard_model.php');

class Kz_Polka_PersonCard_model extends Polka_PersonCard_model {
	/**
	 *	Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 *	Получение списка заявлений о выборе МО
	 */
	function loadPersonCardAttachGrid($data) {
		$filters = "1=1";
		$queryParams = array();

		if( !empty($data['Person_SurName']) ) {
			$filters .= " and PS.Person_SurName like :Person_SurName + '%'";
			$queryParams['Person_SurName'] = rtrim($data['Person_SurName']);
		}

		if( !empty($data['Person_FirName']) ) {
			$filters .= " and PS.Person_FirName like :Person_FirName + '%'";
			$queryParams['Person_FirName'] = rtrim($data['Person_FirName']);
		}

		if( !empty($data['Person_SecName']) ) {
			$filters .= " and PS.Person_SecName like :Person_SecName + '%'";
			$queryParams['Person_SecName'] = rtrim($data['Person_SecName']);
		}

		if (!empty($data['Person_BirthDay_Range'][0]) && !empty($data['Person_BirthDay_Range'][1])) {
			$filters .= " and PS.Person_BirthDay between :Person_BirthDay_beg and :Person_BirthDay_end";
			$queryParams['Person_BirthDay_beg'] = $data['Person_BirthDay_Range'][0];
			$queryParams['Person_BirthDay_end'] = $data['Person_BirthDay_Range'][1];
		}

		if (!empty($data['PersonCardAttach_setDate_Range'][0]) && !empty($data['PersonCardAttach_setDate_Range'][1])) {
			$filters .= " and PCA.PersonCardAttach_setDate between :PersonCardAttach_setDate_beg and :PersonCardAttach_setDate_end";
			$queryParams['PersonCardAttach_setDate_beg'] = $data['PersonCardAttach_setDate_Range'][0];
			$queryParams['PersonCardAttach_setDate_end'] = $data['PersonCardAttach_setDate_Range'][1];
		}

		if( !empty($data['Lpu_aid']) ) {
			$filters .= " and PCA.Lpu_aid = :Lpu_aid";
			$queryParams['Lpu_aid'] = $data['Lpu_aid'];
		}

		if( !empty($data['PersonCardAttachStatusType_id']) ) {
			$filters .= " and PCAST.PersonCardAttachStatusType_id = :PersonCardAttachStatusType_id";
			$queryParams['PersonCardAttachStatusType_id'] = $data['PersonCardAttachStatusType_id'];
		}

		if( !empty($data['GetAttachment_Number']) ) {
			$filters .= " and GA.GetAttachment_Number = :GetAttachment_Number";
			$queryParams['GetAttachment_Number'] = $data['GetAttachment_Number'];
		}

		if( !empty($data['GetAttachmentCase_id']) ) {
			$filters .= " and GAC.GetAttachmentCase_id = :GetAttachmentCase_id";
			$queryParams['GetAttachmentCase_id'] = $data['GetAttachmentCase_id'];
		}

		$query = "
			select
				-- select
				PCA.PersonCardAttach_id,
				convert(varchar(10), PCA.PersonCardAttach_setDate, 104) as PersonCardAttach_setDate,
				PS.Person_SurName+' '+PS.Person_FirName+rtrim(' '+isnull(PS.Person_SecName,'')) as Person_Fio,
				GAC.GetAttachmentCase_Name,
				GA.GetAttachment_Number,
				La.Lpu_Nick as Lpu_aNick,
				PCAST.PersonCardAttachStatusType_id,
				PCAST.PersonCardAttachStatusType_Code,
				PCAST.PersonCardAttachStatusType_Name
				-- end select
			from
				-- from
				v_PersonCardAttach PCA with(nolock)
				cross apply(
					select top 1 Object_sid
					from ObjectSynchronLog with(nolock)
					where ObjectSynchronLogService_id = 2 and Object_Name = 'PersonCardAttach'
						and Object_id = PCA.PersonCardAttach_id
					order by Object_setDT desc
				) OSL_Attach
				inner join r101.v_GetAttachment GA with(nolock) on GA.GetAttachment_id = OSL_Attach.Object_sid
				inner join Person P with(nolock) on P.BDZ_id = GA.Person_id
				inner join v_PersonState PS with(nolock) on PS.Person_id = P.Person_id
				left join r101.v_GetAttachmentCase GAC with(nolock) on GAC.GetAttachmentCase_id = GA.GetAttachmentCase_id
				left join v_Lpu La with(nolock) on La.Lpu_id = PCA.Lpu_aid
				outer apply (
					select top 1 PersonCardAttachStatusType_id
					from v_PersonCardAttachStatus with(nolock)
					where PersonCardAttach_id = PCA.PersonCardAttach_id
					order by PersonCardAttachStatus_setDate desc, PersonCardAttachStatus_id desc
				) PCAS
				left join v_PersonCardAttachStatusType PCAST with(nolock) on PCAST.PersonCardAttachStatusType_id = PCAS.PersonCardAttachStatusType_id
				-- end from
			where
				-- where
				{$filters}
				-- end where
			order by
				-- order by
				PCA.PersonCardAttach_setDate,
				PCA.PersonCardAttach_id
				-- end order by
		";

		//echo getDebugSQL($query, $queryParams);exit;

		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $queryParams);
		$result_count = $this->db->query(getCountSQLPH($query), $queryParams);

		if (is_object($result_count))
		{
			$cnt_arr = $result_count->result('array');
			$count = $cnt_arr[0]['cnt'];
			unset($cnt_arr);
		}
		else
		{
			$count = 0;
		}
		if (is_object($result))
		{
			$response = array();
			$response['data'] = $result->result('array');
			$response['totalCount'] = $count;
			return $response;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Сохранение заявления на прикрепление
	 */
	function savePersonCardAttachRPN($data) {
		$this->load->model('ServiceRPN_model');

		if (empty($data['PersonCardAttach_id'])) {
			//Проверки при добавлении
			if (isset($data['ignorePersonCardExists']) && !$data['ignorePersonCardExists']) {
				$resp = $this->queryResult("
					select top 1
						PC.PersonCard_id,
						PC.Person_SurName+' '+PC.Person_FirName+isnull(' '+PC.Person_SecName,'') as Person_Fio,
						L.Lpu_Nick
					from
						v_PersonCard_all PC with(nolock)
						left join v_Lpu L with(nolock) on L.Lpu_id = PC.Lpu_id
					where
						PC.Person_id = :Person_id
						and PC.Lpu_id = :Lpu_id
						and PC.LpuAttachType_id = 1
						and PC.PersonCard_endDate is null
					order by
						PersonCard_begDate desc
				", array(
					'Person_id' => $data['Person_id'],
					'Lpu_id' => $data['Lpu_aid']
				));
				if (!is_array($resp)) {
					return $this->createError('','Ошибка при получении действующего прикрепления');
				}
				if (isset($resp[0])) {
					$this->_setAlertMsg("{$resp[0]['Person_Fio']} уже прикреплен к {$resp[0]['Lpu_Nick']}, продолжить оформление заявления?");
					return $this->createError('101','YesNo');
				}
			}

			$resp = $this->queryResult("
				select top 1
					PS.Person_SurName+' '+PS.Person_FirName+isnull(' '+PS.Person_SecName, '') as Person_Fio
				from
					v_PersonCardAttach PCA with(nolock)
					cross apply(
						select top 1 Object_sid
						from ObjectSynchronLog with(nolock)
						where ObjectSynchronLogService_id = 2 and Object_Name = 'PersonCardAttach' and Object_id = PCA.PersonCardAttach_id
						order by Object_setDT desc
					) OSL_Attach
					inner join r101.v_GetAttachment GA with(nolock) on GA.GetAttachment_id = OSL_Attach.Object_sid
					inner join Person P with(nolock) on P.BDZ_id = GA.Person_id
					inner join v_PersonState PS with(nolock) on PS.Person_id = P.Person_id
				where
					P.Person_id = :Person_id
					and PCA.Lpu_aid = :Lpu_aid
					and PCA.PersonCardAttach_setDate = :PersonCardAttach_setDate
			", array(
				'Person_id' => $data['Person_id'],
				'Lpu_aid' => $data['Lpu_aid'],
				'PersonCardAttach_setDate' => $data['PersonCardAttach_setDate'],
			));
			if (!is_array($resp)) {
				return $this->createError('','Ошибка при проверке существования заявления на текущий день');
			}
			if (isset($resp[0])) {
				return $this->createError('',"Для пациента {$resp[0]['Person_Fio']} уже добавлено заявление о прикреплении. Проверьте статус заявления");
			}
		} else {
			//Проверки при изменении
			$status = $this->getPersonCardAttachStatus($data);
			if (!is_array($status)) {
				return $this->createError('','Ошибка при получении текущего статуса заявления');
			}
			if ($status['PersonCardAttachStatusType_Code'] != 1) {
				return $this->createError('','Изменение данных доступно только для проекта заявления');
			}
		}

		$psData = $this->getPersonData(array(
			'Person_id' => $data['Person_id'],
			'LpuAttachType_id' => 1
		));
		if (!is_array($psData) || count($psData) == 0) {
			return $this->createError('','Ошибка при получении данных человека');
		}

		$Address_id = coalesce($psData[0]['PAddress_id'], $psData[0]['UAddress_id']);
		if (empty($Address_id)) {
			return $this->createError('','Не указан адрес проживания или регистрации');
		}

		$this->beginTransaction();

		$params = array(
			'PersonCardAttach_id' => !empty($data['PersonCardAttach_id'])?$data['PersonCardAttach_id']:null,
			'PersonCardAttach_setDate' => $data['PersonCardAttach_setDate'],
			'Lpu_id' => !empty($psData[0]['Lpu_id'])?$psData[0]['Lpu_id']:null,
			'Lpu_aid' => $data['Lpu_aid'],
			'Address_id' => $Address_id,
			'Polis_id' => !empty($psData[0]['Polis_id'])?$psData[0]['Polis_id']:null,
			'PersonCardAttach_IsSMS' => 1,
			'PersonCardAttach_SMS' => null,
			'PersonCardAttach_IsEmail' => 1,
			'PersonCardAttach_Email' => null,
			'PersonCardAttach_IsHimself' => null,
			'pmUser_id' => $data['pmUser_id']
		);

		if (empty($data['PersonCardAttach_id'])) {
			$procedure = 'p_PersonCardAttach_ins';
		} else {
			$procedure = 'p_PersonCardAttach_upd';
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :PersonCardAttach_id;
			exec {$procedure}
				@PersonCardAttach_id = @Res output,
				@PersonCardAttach_setDate = :PersonCardAttach_setDate,
				@Lpu_id = :Lpu_id,
				@Lpu_aid = :Lpu_aid,
				@Address_id = :Address_id,
				@Polis_id = :Polis_id,
				@PersonCardAttach_IsSMS = :PersonCardAttach_IsSMS,
				@PersonCardAttach_SMS = :PersonCardAttach_SMS,
				@PersonCardAttach_IsEmail = :PersonCardAttach_IsEmail,
				@PersonCardAttach_Email = :PersonCardAttach_Email,
				@PersonCardAttach_IsHimself = :PersonCardAttach_IsHimself,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as PersonCardAttach_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			$this->rollbackTransaction();
			return $this->createError('','Ошибка при сохранении заявления на прикрепление к участку');
		}
		if (!$this->isSuccessful($response)) {
			$this->rollbackTransaction();
			return $response;
		}

		//При добавлении заявления сохраняется статус "Проект заявления"
		if (empty($data['PersonCardAttach_id'])) {
			$resp = $this->savePersonCardAttachStatus(array(
				'PersonCardAttachStatus_id' => null,
				'PersonCardAttach_id' => $response[0]['PersonCardAttach_id'],
				'PersonCardAttachStatusType_id' => 1,
				'PersonCardAttachStatus_setDate' => $data['PersonCardAttach_setDate'],
				'pmUser_id' => $data['pmUser_id']
			));
			if (!$this->isSuccessful($resp)) {
				$this->rollbackTransaction();
				return $resp;
			}
		}

		//Сохраняются данные заявления для отправки в РПН
		$resp = $this->ServiceRPN_model->saveAttachmentRequest(array(
			'PersonCardAttach_id' => $response[0]['PersonCardAttach_id'],
			'Lpu_id' => $data['Lpu_id'],
			'Person_id' => $data['Person_id'],
			'LpuRegion_id' => $data['LpuRegion_id'],
			'LpuRegionType_id' => $data['LpuRegionType_id'],
			'Address_id' => $Address_id,
			'GetAttachment_begDate' => $data['PersonCardAttach_setDate'],
			'GetAttachment_endDate' => null,
			'GetAttachmentCase_id' => $data['GetAttachmentCase_id'],
			'GetAttachment_IsCareHome' => $data['GetAttachment_IsCareHome'],
			'pmUser_id' => $data['pmUser_id']
		));
		if (!$this->isSuccessful($resp)) {
			$this->rollbackTransaction();
			return $resp;
		}

		$this->commitTransaction();

		return $response;
	}

	/**
	 * Удаление заявления на прикрепление
	 */
	function deletePersonCardAttachRPN($data) {
		$status = $this->getPersonCardAttachStatus($data);
		if (!is_array($status)) {
			return $this->createError('','Ошибка при получении текущего статуса заявления');
		}
		if ($status['PersonCardAttachStatusType_Code'] != 1) {
			return $this->createError('','Можно удалить только проект заявления');
		}

		$this->beginTransaction();

		$response = $this->deletePersonCardAttach($data);
		if (!$this->isSuccessful($response)) {
			$this->rollbackTransaction();
			return $response;
		}

		$this->load->model('ServiceRPN_model');
		$resp = $this->ServiceRPN_model->deleteAttachmentRequest($data);
		if (!$this->isSuccessful($resp)) {
			$this->rollbackTransaction();
			return $resp;
		}

		$this->commitTransaction();

		return $response;
	}

	/**
	 * Получение данных для редактирования заявления на прикрепление
	 */
	function loadPersonCardAttachForm($data) {
		$params = array('PersonCardAttach_id' => $data['PersonCardAttach_id']);

		$query = "
			select top 1
				PCA.PersonCardAttach_id,
				PCA.Lpu_aid,
				convert(varchar(10), PCA.PersonCardAttach_setDate, 104) as PersonCardAttach_setDate,
				LR.LpuRegion_id,
				LRT.LpuRegionType_id,
				P.Person_id,
				GA.GetAttachmentCase_id,
				GA.GetAttachment_IsCareHome,
				GA.GetAttachment_Number
			from
				v_PersonCardAttach PCA with(nolock)
				cross apply(
					select top 1 Object_sid
					from ObjectSynchronLog with(nolock)
					where ObjectSynchronLogService_id = 2 and Object_Name = 'PersonCardAttach'
						and Object_id = PCA.PersonCardAttach_id
					order by Object_setDT desc
				) OSL_Attach
				inner join r101.v_GetAttachment GA with(nolock) on GA.GetAttachment_id = OSL_Attach.Object_sid
				cross apply(
					select top 1 Object_id
					from ObjectSynchronLog with(nolock)
					where ObjectSynchronLogService_id = 2 and Object_Name = 'LpuRegion'
						and Object_sid = GA.GetTerrService_id
					order by Object_setDT desc
				) OSL_LpuRegion
				inner join v_LpuRegion LR with(nolock) on LR.LpuRegion_id = OSL_LpuRegion.Object_id
				inner join v_LpuRegionType LRT with(nolock) on LRT.Region_id = 101 and LRT.LpuRegionType_Code = GA.GetTerrServiceProfile_id
				inner join Person P with(nolock) on P.BDZ_id = GA.Person_id
			where
				PCA.PersonCardAttach_id = :PersonCardAttach_id
		";

		$result = $this->queryResult($query, $params);

		if (isset($result[0]) && !empty($result[0]['PersonCardAttach_id'])) {
			$files = $this->getFilesOnPersonCardAttach(array(
				'PersonCardAttach_id' => $result[0]['PersonCardAttach_id'],
				'PersonCard_id' => null,
			));
			if (!$files) {
				$this->createError('Ошибка при получении списка прикрепленных файлов');
			}
			$result[0]['files'] = $files;
		}

		return $result;
	}

	/**
	 * Отправка заявления в сервис РПН
	 */
	function sendPersonCardAttachToRPN($data) {
		/**
		 * Создание исключений по ошибкам
		 */
		function exceptionErrorHandler($errno, $errstr, $errfile, $errline) {
			switch ($errno) {
				case E_NOTICE:
				case E_USER_NOTICE:
					$errors = "Notice";
					break;
				case E_WARNING:
				case E_USER_WARNING:
					$errors = "Warning";
					break;
				case E_ERROR:
				case E_USER_ERROR:
					$errors = "Fatal Error";
					break;
				default:
					$errors = "Unknown Error";
					break;
			}

			$msg = sprintf("%s:  %s in %s on line %d", $errors, $errstr, $errfile, $errline);
			throw new ErrorException($msg, 0, $errno, $errfile, $errline);
		}

		set_time_limit(0);

		$status = $this->getPersonCardAttachStatus($data);
		if (!is_array($status)) {
			return $this->createError('','Ошибка при получении текущего статуса заявления');
		}
		if (!in_array($status['PersonCardAttachStatusType_Code'], array(1,4))) {
			return $this->createError('','Отправка заявления в РПН доступно только для статусов "Проект заявления","Отправлено в РПН.Ошибка при отправке"');
		}

		//Если статус "4.Отправлено в РПН.Ошибка при отправке.", то проверяем статус в РПН
		if ($status['PersonCardAttachStatusType_Code'] == 4) {
			$resp = $this->getPersonCardAttachStatusFromRPN($data);
			if (!is_array($resp) || !isset($resp[0])) {
				return $this->createError('','Ошибка при получении статуса заявки из РПН');
			}
			if (!empty($resp[0]['Error_Msg']) && $resp[0]['Error_Code'] != 404) {
				return $resp;
			}
			if (empty($resp[0]['Error_Msg'])) {
				$status = $resp[0];
			}
			if ($status['PersonCardAttachStatusType_Code'] != 4) {
				return array(array('success' => true));
			}
		}

		$path = realpath(dirname( __FILE__ ).'/../../../');
		$query = "
			select
				pmMediaData_FileName as filename,
				'{$path}/' + pmMediaData_FilePath as url
			from v_pmMediaData with(nolock)
			where pmMediaData_ObjectName = 'PersonCardAttach' and pmMediaData_ObjectID = :Object_id
		";
		$params = array('Object_id' => $data['PersonCardAttach_id']);
		$files = $this->queryResult($query, $params);
		if (!is_array($files)) {
			return $this->createError('','Ошибка при получении прикрепленных документов');
		}
		if (count($files) == 0) {
			return $this->createError('','Отсутвуют прикрепленные документы');
		}
		$data['files'] = $files;

		$sendResp = array(array('success' => false));
		try {
			set_error_handler('exceptionErrorHandler');

			$resp = $this->savePersonCardAttachStatus(array(
				'PersonCardAttach_id' => $data['PersonCardAttach_id'],
				'PersonCardAttachStatusType_id' => 3,	//3.Отправлено в РПН
				'pmUser_id' => $data['pmUser_id']
			));
			if (!$this->isSuccessful($resp)) {
				//return $this->createError('','Ошибка при сохранении статуса "Отправлено в РПН"');
				throw new Exception('Ошибка при сохранении статуса "Отправлено в РПН"');
			}

			$this->load->model('ServiceRPN_model');
			$sendResp = $this->ServiceRPN_model->sendAttachmentRequestToRpn($data);
			if (!$this->isSuccessful($sendResp)) {
				if (isset($sendResp[0]) && !empty($sendResp[0]['Error_Msg'])) {
					throw new Exception($sendResp[0]['Error_Msg']);
				} else {
					throw new Exception('Ошибка при отправке заявления в РПН');
				}
			}

			$resp = $this->savePersonCardAttachStatus(array(
				'PersonCardAttach_id' => $data['PersonCardAttach_id'],
				'PersonCardAttachStatusType_id' => 5,	//5.Принято к рассмотрению
				'pmUser_id' => $data['pmUser_id']
			));
			if (!$this->isSuccessful($resp)) {
				throw new Exception('Ошибка при сохранении статуса "Принято к рассмотрению"');
			}
			restore_error_handler();
		} catch(Exception $e) {
			restore_error_handler();
			$this->savePersonCardAttachStatus(array(
				'PersonCardAttach_id' => $data['PersonCardAttach_id'],
				'PersonCardAttachStatusType_id' => 4,	//4.Отправлено в РПН.Ошибка при отправке.
				'pmUser_id' => $data['pmUser_id']
			));
			return $this->createError('',$e->getMessage());
		}

		return $sendResp;
	}

	/**
	 * Создание прикрепления по заявлению
	 */
	function createPersonCardByAttach($data) {
		$params = array('PersonCardAttach_id' => $data['PersonCardAttach_id']);

		$count = $this->getFirstResultFromQuery("
			select top 1 count(PersonCard_id) as cnt
			from v_PersonCard_all with(nolock)
			where PersonCardAttach_id = :PersonCardAttach_id
		", $params);
		if ($count === false) {
			return $this->createError('','Ошибка при проверке существования прикрепления');
		}
		if ($count > 0) {
			return $this->createError('','По заявлению уже было создано прикрепление');
		}

		$query = "
			select top 1
				P.Person_id,
				PCA.Lpu_aid,
				PCA.PersonCardAttach_id,
				isnull(PAC.PersonAmbulatCard_Num, 1) as PersonAmbulatCard_Num,
				--convert(varchar(10), PCA.PersonCardAttach_setDate, 120) as PersonCardAttach_setDate,
				convert(varchar(10), GA.GetAttachment_begDate, 120) as PersonCardAttach_setDate,
				LR.LpuRegion_id,
				LR.LpuRegionType_id
			from
				v_PersonCardAttach PCA with(nolock)
				cross apply(
					select top 1 Object_sid
					from ObjectSynchronLog with(nolock)
					where ObjectSynchronLogService_id = 2 and Object_Name = 'PersonCardAttach' and Object_id = PCA.PersonCardAttach_id
					order by Object_setDT desc
				) OSL_Attach
				inner join r101.v_GetAttachment GA with(nolock) on GA.GetAttachment_id = OSL_Attach.Object_sid
				cross apply(
					select top 1 Object_id
					from ObjectSynchronLog with(nolock)
					where ObjectSynchronLogService_id = 2 and Object_Name = 'LpuRegion'
						and Object_sid = GA.GetTerrService_id
					order by Object_setDT desc
				) OSL_LpuRegion
				inner join v_LpuRegion LR with(nolock) on LR.LpuRegion_id = OSL_LpuRegion.Object_id
				inner join Person P with(nolock) on P.BDZ_id = GA.Person_id
				outer apply(
					select top 1 max(cast(PersonAmbulatCard_Num as bigint))+1 as PersonAmbulatCard_Num
					from v_PersonAmbulatCard with(nolock)
					where ISNUMERIC(PersonAmbulatCard_Num) = 1 and Lpu_id = PCA.Lpu_id
				) PAC
			where PCA.PersonCardAttach_id = :PersonCardAttach_id
		";
		$attach = $this->getFirstRowFromQuery($query, $params);
		if ($attach === false) {
			return $this->createError('','Ошибка при получении данных для прикрепления');
		}

		$this->load->model('PersonAmbulatCard_model');
		$PersonAmbulatCard = $this->PersonAmbulatCard_model->checkPersonAmbulatCard(array(
			'Person_id' => $attach['Person_id'],
			'Lpu_id' => $attach['Lpu_aid'],
			'pmUser_id' => $data['pmUser_id'],
			'Server_id'=> $data['Server_id'],
			'PersonAmbulatCard_Num' => $attach['PersonAmbulatCard_Num'],
			'getCount' => false
		));
		if (!$this->isSuccessful($PersonAmbulatCard)) {
			return $PersonAmbulatCard;
		}

		$this->isAllowTransaction = false;
		$response = $this->PersonCard_model->savePersonCard(array(
			'action' => 'add',
			'PersonCard_id' => null,
			'PersonCard_Code' => $PersonAmbulatCard[0]['PersonCard_Code'],
			'PersonAmbulatCard_id' => $PersonAmbulatCard[0]['PersonAmbulatCard_id'],
			'PersonAmbulatCard_Num' => $attach['PersonAmbulatCard_Num'],
			'Person_id' => $attach['Person_id'],
			'Lpu_id' => $attach['Lpu_aid'],
			'PersonCard_begDate' => $attach['PersonCardAttach_setDate'],
			'PersonCard_endDate' => null,
			'PersonCardAttach_id' => $attach['PersonCardAttach_id'],
			'LpuAttachType_id' => 1,
			'CardCloseCause_id' => null,
			'LpuRegion_id' => $attach['LpuRegion_id'],
			'LpuRegionType_id' => $attach['LpuRegionType_id'],
			'LpuRegion_Fapid' => null,
			'noTransferService' => true,
			'Server_id' => $data['Server_id'],
			'pmUser_id' => $data['pmUser_id'],
		));
		$this->isAllowTransaction = true;

		return $response;
	}

	/**
	 * Получение статуса заявления из сервиса РПН
	 */
	function getPersonCardAttachStatusFromRPN($data) {
		$params = array(
			'PersonCardAttach_id' => $data['PersonCardAttach_id'],
			'Server_id' => $data['Server_id'],
			'pmUser_id' => $data['pmUser_id']
		);
		$this->load->model('ServiceRPN_model');

		$status = $this->getPersonCardAttachStatus($params);
		if (!is_array($status)) {
			return $this->createError('','Ошибка при получении текущего статуса заявления');
		}

		$this->beginTransaction();

		$response = $this->ServiceRPN_model->getAttachmentRequestStatus($params);
		if (!$this->isSuccessful($response)) {
			$this->rollbackTransaction();
			return $response;
		}
		if (empty($response[0]['GetAttachmentStatus_Code'])) {
			$this->rollbackTransaction();
			return $this->createError('','Не получен код статуса заявки на прикрепление');
		}

		switch((int)$response[0]['GetAttachmentStatus_Code']) {
			case 200:
				if ($status['PersonCardAttachStatusType_id'] != 5) {
					$resp = $this->savePersonCardAttachStatus(array(
						'PersonCardAttach_id' => $params['PersonCardAttach_id'],
						'PersonCardAttachStatusType_id' => 5,	//5.Принято к рассмотрению
						'pmUser_id' => $params['pmUser_id']
					));
					if (!$this->isSuccessful($resp)) {
						$this->rollbackTransaction();
						return $resp;
					}
				}
				break;
			case 300:
				if ($status['PersonCardAttachStatusType_id'] != 2) {
					$resp = $this->savePersonCardAttachStatus(array(
						'PersonCardAttach_id' => $params['PersonCardAttach_id'],
						'PersonCardAttachStatusType_id' => 2,	//2.Основное прикрепление
						'pmUser_id' => $params['pmUser_id']
					));
					if (!$this->isSuccessful($resp)) {
						$this->rollbackTransaction();
						return $resp;
					}
				}

				$resp = $this->createPersonCardByAttach($params);
				if (!$this->isSuccessful($resp)) {
					$this->rollbackTransaction();
					if (!is_array($resp)) {
						return $this->createError('','Ошибка при создании прикрепления');
					} else {
						return $resp;
					}
				}
				break;
			case 1200:
			case 1300:
				if ($status['PersonCardAttachStatusType_id'] != 6) {
					$resp = $this->savePersonCardAttachStatus(array(
						'PersonCardAttach_id' => $params['PersonCardAttach_id'],
						'PersonCardAttachStatusType_id' => 6,	//6.В прикреплении отказано
						'pmUser_id' => $params['pmUser_id']
					));
					if (!$this->isSuccessful($resp)) {
						$this->rollbackTransaction();
						return $resp;
					}
				}
				break;
		}

		$status = $this->getPersonCardAttachStatus($params);
		if (!is_array($status)) {
			$this->rollbackTransaction();
			return $this->createError('','Ошибка при получении текущего статуса заявления');
		}
		$response[0] = array_merge($response[0], $status);

		$this->commitTransaction();

		return $response;
	}
}