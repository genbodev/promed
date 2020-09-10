<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * ServiceRPN_model - модель для работы с оперблоком
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      ServiceRPN
 * @access       public
 * @copyright    Copyright (c) 2015 Swan Ltd.
 * @author       Markoff Andrew
 * @version      07.2015
 *
 * @property swServiceKZ $swservicesurkz
 * @property ObjectSynchronLog_model $ObjectSynchronLog_model
 * @property Utils_model $Utils_model
 * @property Polka_PersonCard_model $PersonCard_model
 *
 */

require_once(APPPATH.'models/ServiceSUR_model.php');

class Kz_ServiceSUR_model extends ServiceSUR_model
{
	public $scheme = 'r101';

	protected $_syncObjectList = array();

	/**
	 *	Конструктор
	 */	
	function __construct() {
		set_time_limit(0);
		parent::__construct();
		$this->load->model('ObjectSynchronLog_model');
		$this->ObjectSynchronLog_model->setServiceSysNick('SurKZ');
		$this->load->library('textlog', array('file' => 'ServiceSUR_'.date('Y-m-d').'.log'));
	}

	/**
	 * Json с ошибкой
	 */ 
	function err($msg) {
		return array(array(
			'Error_Msg' => $msg,
			'Cancel_Error_Handle'=>true,
			'success' => false
		));
	}

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

	/**
	 * Обработка Fatal Error
	 */
	function shutdownErrorHandler($func) {
		$error = error_get_last();

		if (!empty($error)) {
			switch ($error['type']) {
				case E_NOTICE:
				case E_USER_NOTICE:
					$type = "Notice";
					break;
				case E_WARNING:
				case E_USER_WARNING:
					$type = "Warning";
					break;
				case E_ERROR:
				case E_USER_ERROR:
					$type = "Fatal Error";
					break;
				default:
					$type = "Unknown Error";
					break;
			}

			$msg = sprintf("%s:  %s in %s on line %d", $type, $error['message'], $error['file'], $error['line']);

			//$func($msg);
			call_user_func($func, $msg);

			exit($error['type']);
		}
	}

	/**
	 * Выполнение запросов к сервису СУР и обработка ошибок, которые возвращает сервис
	 */
	function exec($method, $type = 'get', $data = null) {
		$config = $this->config->item('SUR');
		if (empty($config)) {
			return  array(
				'success' => false,
				'errorMsg' => 'Не найден конфиг для соединения с сервисом СУР'
			);
		}
		$this->load->library('swServiceKZ', $config, 'swservicerpnkz');

		$tryLimit = !empty($config['try_limit'])?$config['try_limit']:20;
		$tryCount = 0;
		$retry = false;

		do {
			if ($retry) {
				sleep(2);
			}
			$tryCount++;
			$this->textlog->add("exec method: $method, type: $type, try: $tryCount, data: ".print_r($data,true));
			$result = $this->swservicerpnkz->data($method, $type, $data);
			$this->textlog->add("result: ".print_r($result,true));
			$retry = (is_array($result) && isset($result['errorCode']) && in_array($result['errorCode'],  array(0, 500)));
		} while($retry && $tryCount < $tryLimit);

		if (is_object($result) && !empty($result->Message)) {
			$result = array(
				'success' => false,
				'errorMsg' => 'Ошибка в работе сервиса СУР: '.$result->Message
			);
		}
		if (is_object($result) && !empty($result->ExceptionMessage)) {
			$result = array(
				'success' => false,
				'errorMsg' => 'Ошибка в работе сервиса СУР: '.$result->ExceptionMessage
			);
		}
		return $result;
	}

	/**
	 * Получение синхронизованных объектов
	 */
	function getSyncObject($table, $id, $field = 'Object_id') {
		if (empty($id) || !in_array($field, array('Object_id','Object_sid'))) {
			return null;
		}

		$nick = $field;
		if (in_array($field, array('Object_sid'))) {
			$nick = 'Object_Value';
		}

		// ищем в памяти
		if (isset($this->_syncObjectList[$table]) && isset($this->_syncObjectList[$table][$nick]) && isset($this->_syncObjectList[$table][$nick][$id])) {
			return $this->_syncObjectList[$table][$nick][$id];
		}

		// ищем в бд
		$ObjectSynchronLogData = $this->ObjectSynchronLog_model->getObjectSynchronLog($table, $id, $field);
		if (!empty($ObjectSynchronLogData)) {
			$key = $ObjectSynchronLogData['Object_id'];
			$this->_syncObjectList[$table]['Object_id'][$key] = &$ObjectSynchronLogData;

			$key = $ObjectSynchronLogData['Object_Value'];
			$this->_syncObjectList[$table]['Object_Value'][$key] = &$ObjectSynchronLogData;

			return $ObjectSynchronLogData;
		}

		return null;
	}

	/**
	 * Сохранение синхронизованных объектов
	 */
	function saveSyncObject($table, $id, $value, $ins = false) {
		// сохраняем в БД
		$resp = $this->ObjectSynchronLog_model->saveObjectSynchronLog($table, $id, $value, $ins);

		// сохраняем в памяти
		$ObjectSynchronLogData = array(
			'ObjectSynchronLog_id' => $resp[0]['ObjectSynchronLog_id'],
			'Object_Name' => $table,
			'Object_id' => $id,
			'Object_Value' => $value,
		);

		if (!empty($id)) {
			$this->_syncObjectList[$table]['Object_id'][$id] = &$ObjectSynchronLogData;
		}
		$this->_syncObjectList[$table]['Object_Value'][$value] = &$ObjectSynchronLogData;
		return $ObjectSynchronLogData;
	}

	/**
	 * Сохранение данных объектов
	 *
	 * @param string $objectName
	 * @param array $objectData
	 * @return array
	 */
	function saveObject($objectData, $objectName, $idField = null, $allowEmptyId = false) {
		if (empty($idField)) {
			$idField = 'ID';
		}
		if (empty($objectData[$idField]) && !$allowEmptyId) {
			return $this->createError('','Отсутвует идентификатор объекта');
		}
		if (empty($objectData['pmUser_id'])) {
			return $this->createError('','Отсутвует идентификатор пользователя');
		}

		$savedData = array();
		$fields = array_map(function($field) {
			return mb_strtolower($field);
		}, array_keys($objectData));
		if (!empty($objectData[$objectName.'_id'])) {
			$resp = $this->queryResult("
				select top 1 *
				from {$this->scheme}.v_{$objectName}
				where $idField = :{$idField}
			", array(
				$idField => $objectData[$idField]
			));
			if (isset($resp[0])) {
				foreach($resp[0] as $field => $value) {
					if (!in_array(mb_strtolower($field), $fields)) {
						$savedData[$field] = $value;
			}
		}
			}
		}
		$objectData = array_merge($savedData, $objectData);
		unset($objectData[$objectName.'_id']);

		$ignoreFields = array('pmUser_insID','pmUser_updID',$objectName.'_insDT',$objectName.'_updDT');

		$queryParams = array();
		$execPartParams = array();
		foreach($objectData as $field => $value) {
			if (in_array($field, $ignoreFields)) {
				continue;
			}
			$execPartParams[] = "@{$field} = :{$field}";
			$value = trim($value);
			if ($value instanceof DateTime) {
				$value = $value->format('Y-m-d H:i:s');
			}
			if ($value == '0001-01-01T00:00:00') {
				$value = null;
			}
			$queryParams[$field] = !empty($value)?$value:null;
		}
		$execPartParamsStr = implode(",\n", $execPartParams);
		
		$addFilter = '';
		
		if ($objectName == 'GetPersonal') {
			$addFilter .= ' and PostID = :PostID ';
		}

		$query = "
			declare
				@{$idField} bigint,
				@{$objectName}_id bigint,
				@Error_Code bigint,
				@Error_Message varchar(4000);
			set @{$objectName}_id = (
				select top 1 {$objectName}_id
				from {$this->scheme}.{$objectName} with(nolock)
				where {$idField} = :{$idField}
					{$addFilter}
			)
			if @{$objectName}_id is null
			exec {$this->scheme}.p_{$objectName}_ins
				@{$objectName}_id = @{$objectName}_id output,
				{$execPartParamsStr},
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
			else
			exec {$this->scheme}.p_{$objectName}_upd
				@{$objectName}_id = @{$objectName}_id output,
				{$execPartParamsStr},
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
			select @{$objectName}_id as {$objectName}_id, @Error_Code as Error_Code, @Error_Message as Error_Msg
		";
		//echo getDebugSQL($query, $queryParams);exit;
		return $this->queryResult($query, $queryParams);
	}

	/**
	 * Сохранение связи идентификатора МО из Промеда и СУР
	 */
	function saveLpuLink($Lpu_id, $LpuSUR_id) {
		$lpulink = $this->getSyncObject('Lpu', $Lpu_id);

		if (!empty($LpuSUR_id) || !empty($lpulink)) {
			$this->saveSyncObject('Lpu', $Lpu_id, $LpuSUR_id);
		}

		//Если $LpuSUR_id пустой, то нужно найти запись в r101.GetMO по Lpu_id и очистить Lpu_id
		if (empty($LpuSUR_id)) {
			$LpuSUR_id = $this->getFirstResultFromQuery("
				select top 1 ID from {$this->scheme}.GetMO with(nolock) where Lpu_id = :Lpu_id
			", array('Lpu_id' => $Lpu_id), true);
			if (!empty($LpuSUR_id)) {
				$Lpu_id = null;
			}
		}

		if (!empty($LpuSUR_id)) {
			$params = array(
				'key_field' => 'ID',
				'ID' => $LpuSUR_id,
				'Lpu_id' => $Lpu_id
			);
			$this->swUpdate($this->scheme.'.GetMO', $params, false);
		}
	}

	/**
	 * Сохранение полученных даных об МО
	 */
	function saveGetMO($MO, $adrUnit, $pmUser_id) {
		$params = array(
			'ID' => $MO->id,
			'BIN' => $MO->bIN,
			'DataSource' => $MO->dataSource,
			'FullAddress' => $MO->fullAddress,
			'fullNameKZ' => $MO->fullNameKZ,
			'fullNameRU' => $MO->fullNameRU,
			'MedCode' => $MO->medCode,
			'RNN' => $MO->rNN,
			'pmUser_id' => $pmUser_id
		);
		$params['GetMO_id'] = $this->getFirstResultFromQuery("
			select top 1 GetMO_id from {$this->scheme}.GetMO with(nolock) where ID = :ID
		", $params, true);
		$resp = $this->saveObject($params, 'GetMO');
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при сохранении МО');
		}
		if (!$this->isSuccessful($resp)) {
			return $resp;
		}
		$this->saveSyncObject('GetMO', $resp[0]['GetMO_id'], $params['ID']);
		$this->saveSyncObject('MOAdrUnitLink', $params['ID'], $adrUnit);
		return $resp;
	}

	/**
	 * Получение списка МО
	 */
	function importGetMOList($data) {
		$params = json_encode(array(
			'stateId' => $data['adrUnit'],
			'recStart' => !empty($data['recStart'])?$data['recStart']:0,
			'recCount' => !empty($data['recCount'])?$data['recCount']:200,
		));
		$result = $this->exec('/SurServices/GetMOListByOblCode', 'post', $params);
		if (is_array($result) && !empty($result['errorMsg'])) {
			return $this->createError('',$result['errorMsg']);
		}
		if (empty($result) || (is_object($result) && empty($result->MOList))) {
			$MOList = array();
		} else {
			$MOList = $result->MOList;
		}
		if (isset($_REQUEST['Debug']) && $_REQUEST['Debug'] == 1) {
			echo '<pre>';print_r($MOList);exit;
		}

		foreach($MOList as $MO) {
			if ($MO->id == 0) continue;
			$resp = $this->saveGetMO($MO, $data['adrUnit'], $data['pmUser_id']);
			if (!empty($resp[0]['Error_Msg'])) {
				return $this->createError('',$resp[0]['Error_Msg']);
			}
		}

		return array(array('success' => true));
	}

	/**
	 * Сохранение полученных данных об функицональном подразделении в МО
	 */
	function saveGetFP($FP, $MOID, $pmUser_id) {
		$params = array(
			'FPID' => $FP->fPID,
			'MOID' => $MOID,
			'CodeId' => $FP->codeId,
			'CodeKz' => $FP->codeKz,
			'CodeRu' => $FP->codeRu,
			'IsVisible' => $FP->isVisible,
			'NameKZ' => $FP->nameKZ,
			'NameRU' => $FP->nameRU,
			'NomenclatureID' => $FP->nomenclatureID,
			'NomenclatureKZ' => $FP->nomenclatureKZ,
			'NomenclatureRU' => $FP->nomenclatureRU,
			'ParentID' => $FP->parentID,
			'TypeId' => $FP->typeId,
			'TypeKz' => $FP->typeKz,
			'TypeRu' => $FP->typeRu,
			'pmUser_id' => $pmUser_id
		);
		$resp = $this->saveObject($params, 'GetFP', 'FPID');
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при сохранении подразделения');
		}
		if (!$this->isSuccessful($resp)) {
			return $resp;
		}
		$this->saveSyncObject('GetFP', $resp[0]['GetFP_id'], $params['FPID']);
		$this->saveSyncObject('FPMOLink', $params['FPID'], $MOID);
		return $resp;
	}

	/**
	 * Получение списка функциональных подразделений в МО
	 */
	function importGetFPList($data) {
		$MOId = $data['MOId'];

		$FPList = $this->exec("/SurServices/GetMOFPList/{$MOId}");
		if (is_array($FPList) && !empty($FPList['errorMsg'])) {
			return $this->createError('',$FPList['errorMsg']);
		}
		if (isset($_REQUEST['Debug']) && $_REQUEST['Debug'] == 1) {
			echo '<pre>';print_r($FPList);exit;
		}

		foreach($FPList as $FP) {
			if ($FP->fPID == 0) continue;
			$resp = $this->saveGetFP($FP, $MOId, $data['pmUser_id']);
			if (!empty($resp[0]['Error_Msg'])) {
				return $this->createError('',$resp[0]['Error_Msg']);
			}
		}

		return array(array('success' => true));
	}

	/**
	 * Сохранение полученных данных о палате
	 */
	function saveGetRoom($Room, $MOID, $pmUser_id) {
		$params = array(
			'ID' => $Room->id,
			'FPID' => $Room->fpId,
			'Area' => $Room->area,
			'Child' => $Room->child,
			'Name' => $Room->name,
			'NameSetRoom' => $Room->nameSetRoom,
			'NameSetRoomKz' => $Room->nameSetRoomKz,
			'NameSetRoomRu' => $Room->nameSetRoomRu,
			'Number' => $Room->number,
			'SetRoom' => $Room->setRoom,
			'SetRoomKz' => $Room->setRoomKz,
			'SetRoomRu' => $Room->setRoomRu,
			'Sex' => $Room->sex,
			'SexKz' => $Room->sexKz,
			'SexRu' => $Room->sexRu,
			'SpecName' => $Room->specName,
			'SpecNameKz' => $Room->specNameKz,
			'SpecNameRu' => $Room->specNameRu,
			'pmUser_id' => $pmUser_id
		);
		$resp = $this->saveObject($params, 'GetRoom');
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при сохранении палаты');
		}
		if (!$this->isSuccessful($resp)) {
			return $resp;
		}
		$this->saveSyncObject('GetRoom', $resp[0]['GetRoom_id'], $params['ID']);
		if (!empty($MOID)) {
			$this->saveSyncObject('FPMOLink', $params['FPID'], $MOID);
			$this->saveSyncObject('RoomMOLink', $params['ID'], $MOID);
		}
		return $resp;
	}

	/**
	 * Получение списка палат в МО
	 */
	function importGetRoomList($data) {
		if (empty($data['idMo']) && empty($data['idFp'])) {
			echo 'Должен быть передан идентификатор МО и/или идентификатор подразделения';
			return $this->createError('','Должен быть передан идентификатор МО и/или идентификатор подразделения');
		}
		$params = json_encode(array(
			'idMo' => !empty($data['idMo'])?$data['idMo']:null,
			'idFp' => !empty($data['idFp'])?$data['idFp']:null,
		));

		$RoomList = $this->exec("/SurServices/GetMoFpRoomList", 'post', $params);
		if (is_array($RoomList) && !empty($RoomList['errorMsg'])) {
			return $this->createError('',$RoomList['errorMsg']);
		}
		if (isset($_REQUEST['Debug']) && $_REQUEST['Debug'] == 1) {
			echo '<pre>';print_r($RoomList);exit;
		}

		$MOID = !empty($data['idMo'])?$data['idMo']:null;

		foreach($RoomList as $Room) {
			if ($Room->id == 0) continue;
			$resp = $this->saveGetRoom($Room, $MOID, $data['pmUser_id']);
			if (!empty($resp[0]['Error_Msg'])) {
				return $this->createError('',$resp[0]['Error_Msg']);
			}
		}

		return array(array('success' => true));
	}

	/**
	 * Сохранение полученных данных о койке
	 */
	function saveGetBed($Bed, $MOID, $pmUser_id) {
		$params = array(
			'ID' => $Bed->id,
			'Name' => $Bed->name,
			'RoomID' => $Bed->roomId,
			'BedProfile' => $Bed->bedProfile,
			'BedProfileKz' => $Bed->bedProfileKz,
			'BedProfileRu' => $Bed->bedProfileRu,
			'BedType' => $Bed->bedType,
			'LastAction' => $Bed->lastAction,
			'LastActionDateBeg' => $Bed->lastActionDateBeg,
			'LastActionKz' => $Bed->lastActionKz,
			'LastActionRu' => $Bed->lastActionRu,
			'LastProfileDateBeg' => $Bed->lastProfileDateBeg,
			'LastStacTypeDateBeg' => $Bed->lastStacTypeDateBeg,
			'LastTypSrcFinDateBeg' => $Bed->lastTypSrcFinDateBeg,
			'StacDayKind' => $Bed->stacDayKind,
			'StacDayKindKz' => $Bed->stacDayKindKz,
			'StacDayKindRu' => $Bed->stacDayKindRu,
			'StacType' => $Bed->stacType,
			'StacTypeKz' => $Bed->stacTypeKz,
			'StacTypeRu' => $Bed->stacTypeRu,
			'Temporary' => $Bed->temporary,
			'TypeSrcFin' => $Bed->typeSrcFin,
			'TypeSrcFinKz' => $Bed->typeSrcFinKz,
			'TypeSrcFinRu' => $Bed->typeSrcFinRu,
			'pmUser_id' => $pmUser_id
		);
		$resp = $this->saveObject($params, 'GetBed');
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при сохранении койки');
		}
		if (!$this->isSuccessful($resp)) {
			return $resp;
		}
		$this->saveSyncObject('GetBed', $resp[0]['GetBed_id'], $params['ID']);
		if (!empty($MOID)) {
			$this->saveSyncObject('RoomMOLink', $params['RoomID'], $MOID);
			$this->saveSyncObject('BedMOLink', $params['ID'], $MOID);
		}
		return $resp;
	}

	/**
	 * Получение списка коек в МО
	 */
	function importGetBedList($data) {
		$params = json_encode(array(
			'idMo' => !empty($data['idMo'])?$data['idMo']:null,
			'idFp' => !empty($data['idFp'])?$data['idFp']:null,
			'idRoom' => !empty($data['idRoom'])?$data['idRoom']:null,
		));

		$BedList = $this->exec("/SurServices/GetMoBedList", 'post', $params);
		if (is_array($BedList) && !empty($BedList['errorMsg'])) {
			return $this->createError('',$BedList['errorMsg']);
		}
		if (isset($_REQUEST['Debug']) && $_REQUEST['Debug'] == 1) {
			echo '<pre>';print_r($BedList);exit;
		}

		$MOID = !empty($data['idMo'])?$data['idMo']:null;

		foreach($BedList as $Bed) {
			if ($Bed->id == 0) continue;
			$resp = $this->saveGetBed($Bed, $MOID, $data['pmUser_id']);
			if (!empty($resp[0]['Error_Msg'])) {
				return $this->createError('',$resp[0]['Error_Msg']);
			}
		}

		return array(array('success' => true));
	}

	/**
	 * Сохранение полученных данных о записи истории койки
	 */
	function saveGetBedHistory($BedHistory, $pmUser_id) {
		$params = array(
			'ID' => $BedHistory->id,
			'BegDate' => $BedHistory->begDate,
			'Comment' => $BedHistory->comment,
			'EndDate' => $BedHistory->endDate,
			'BedID' => $BedHistory->bedId,
			'BedAction' => $BedHistory->bedAction,
			'BedActionBase' => $BedHistory->bedActionBase,
			'BedActionKz' => $BedHistory->bedActionKz,
			'BedActionRu' => $BedHistory->bedActionRu,
			'pmUser_id' => $pmUser_id
		);
		$resp = $this->saveObject($params, 'GetBedHistory');
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при сохранении записи истории койки');
		}
		if (!$this->isSuccessful($resp)) {
			return $resp;
		}
		$this->saveSyncObject('GetBedHistory', $resp[0]['GetBedHistory_id'], $params['ID']);
		return $resp;
	}

	/**
	 * Получение истории состояний коек в МО
	 */
	function importGetBedHistoryList($data) {
		if (empty($data['idFp']) && empty($data['idBed'])) {
			return $this->createError('','Должен быть передан идентификатор подразделения и/или идентификатор койки');
		}
		$params = json_encode(array(
			'idMo' => $data['idMo'],
			'idFp' => !empty($data['idFp'])?$data['idFp']:null,
			'idBed' => !empty($data['idBed'])?$data['idBed']:null,
		));

		$BedHistoryList = $this->exec("/SurServices/GetMoBedHistoryList", 'post', $params);
		if (is_array($BedHistoryList) && !empty($BedHistoryList['errorMsg'])) {
			return $this->createError('',$BedHistoryList['errorMsg']);
		}
		if (isset($_REQUEST['Debug']) && $_REQUEST['Debug'] == 1) {
			echo '<pre>';print_r($BedHistoryList);exit;
		}

		foreach($BedHistoryList as $BedHistory) {
			if ($BedHistory->id == 0) continue;
			$resp = $this->saveGetBedHistory($BedHistory, $data['pmUser_id']);
			if (!empty($resp[0]['Error_Msg'])) {
				return $this->createError('',$resp[0]['Error_Msg']);
			}
		}

		return array(array('success' => true));
	}

	/**
	 * Сохранение полученных данных о враче
	 */
	function saveGetPersonal($Personal, $pmUser_id) {
		$params = array(
			'PersonalID' => $Personal->personalID,
			'PersonalTypeID' => $Personal->personalTypeID,
			'PersonalTypeKZ' => $Personal->personalTypeKZ,
			'PersonalTypeRU' => $Personal->personalTypeRU,
			'Id' => $Personal->id,
			'LastName' => $Personal->lastName,
			'FirstName' => $Personal->firstName,
			'SecondName' => $Personal->secondName,
			'IIN' => $Personal->iIN,
			'ListPost' => $Personal->listPost,
			'Comment' => mb_substr($Personal->comment,0,20),//Ограничение 20 символов. Если больше, обрезаем.
			'PersonId' => $Personal->personId,
			'FPID' => $Personal->fpId,
			'fpName' => $Personal->fpName,
			'fpNameKZ' => $Personal->fpNameKZ,
			'MOID' => $Personal->moId,
			'PostCategoryId' => $Personal->postCategoryId,
			'PostCategoryKz' => $Personal->postCategoryKz,
			'PostCategoryRu' => $Personal->postCategoryRu,
			'PostCount' => $Personal->postCount,
			'PostFuncID' => $Personal->postFuncID,
			'PostFuncKZ' => $Personal->postFuncKZ,
			'PostFuncRU' => $Personal->postFuncRU,
			'PostID' => $Personal->postID,
			'PostTypeID' => $Personal->postTypeID,
			'PostTypeKZ' => $Personal->postTypeKZ,
			'PostTypeRU' => $Personal->postTypeRU,
			'SpecialityID' => $Personal->specialityID,
			'SpecialityKZ' => $Personal->specialityKZ,
			'SpecialityRU' => $Personal->specialityRU,
			'StatusPost' => $Personal->statusPost,
			'statusPostKz' => $Personal->statusPostKz,
			'statusPostRu' => $Personal->statusPostRu,
			'TypSrcFinId' => $Personal->typSrcFinId,
			'TypSrcFinKz' => $Personal->typSrcFinKz,
			'TypSrcFinRu' => $Personal->typSrcFinRu,
			'TypeEmployeeId' => $Personal->typeEmployeeId,
			'TypeEmployeeKz' => $Personal->typeEmployeeKz,
			'TypeEmployeeRu' => $Personal->typeEmployeeRu,
			'pmUser_id' => $pmUser_id
		);
		$resp = $this->saveObject($params, 'GetPersonal', 'PersonalID');
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при сохранении сотрудника подразделения');
		}
		if (!$this->isSuccessful($resp)) {
			return $resp;
		}
		$this->saveSyncObject('GetPersonal', $resp[0]['GetPersonal_id'], $params['PersonalID']);
		return $resp;
	}

	/**
	 * Получение списка сотрудников функциональных подразделении в МО
	 */
	function importGetPersonalList($data) {
		$params = json_encode(array(
			'id' => $data['id'],
			'fp' => !empty($data['fp'])?$data['fp']:null,
			'active' => !empty($data['active'])?$data['active']:null,
		));

		$PersonalList = $this->exec("/SurServices/GetMOFPPersonalList", 'post', $params);
		if (is_array($PersonalList) && !empty($PersonalList['errorMsg'])) {
			return $this->createError('',$PersonalList['errorMsg']);
		}
		if (isset($_REQUEST['Debug']) && $_REQUEST['Debug'] == 1) {
			echo '<pre>';print_r($PersonalList);exit;
		}

		foreach($PersonalList as $Personal) {
			if ($Personal->personalID == 0) continue;
			$resp = $this->saveGetPersonal($Personal, $data['pmUser_id']);
			if (!empty($resp[0]['Error_Msg'])) {
				return $this->createError('',$resp[0]['Error_Msg']);
			}
		}

		return array(array('success' => true));
	}

	/**
	 * Сохранение полученных данных о записи истории врача
	 */
	function saveGetPersonalHistory($PersonalHistory, $pmUser_id) {
		$params = array(
			'ID' => $PersonalHistory->id,
			'PersonalID' => $PersonalHistory->personalId,
			'PostId' => $PersonalHistory->postId,
			'RpnId' => $PersonalHistory->rpnID,
			'BeginDate' => $PersonalHistory->beginDate,
			'Comment' => $PersonalHistory->comment,
			'EndDate' => $PersonalHistory->endDate,
			'FPID' => $PersonalHistory->fpId,
			'MOID' => $PersonalHistory->moId,
			'OrderNum' => $PersonalHistory->orderNum,
			'RsnWork' => $PersonalHistory->rsnWork,
			'RsnWorkKz' => $PersonalHistory->rsnWorkKz,
			'RsnWorkParent' => $PersonalHistory->rsnWorkParent,
			'RsnWorkRu' => $PersonalHistory->rsnWorkRu,
			'RsnWorkTermination' => $PersonalHistory->rsnWorkTermination,
			'RsnWorkTerminationKz' => $PersonalHistory->rsnWorkTerminationKz,
			'RsnWorkTerminationRu' => $PersonalHistory->rsnWorkTerminationRu,
			'pmUser_id' => $pmUser_id
		);
		$resp = $this->saveObject($params, 'GetPersonalHistory');
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при сохранении записи в истории сотрудника');
		}
		if (!$this->isSuccessful($resp)) {
			return $resp;
		}
		$this->saveSyncObject('GetPersonalHistory', $resp[0]['GetPersonalHistory_id'], $params['ID']);
		return $resp;
	}

	/**
	 * Получение истории должностей сотрудников в МО
	 */
	function importGetPersonalHistoryList($data) {
		$personalIdList = array();
		if (!empty($data['personalId'])) {
			$personalIdList[] = $data['personalId'];
		} else {
			$resp = $this->queryResult("
				select PersonalID
				from {$this->scheme}.v_GetPersonal GP with(nolock)
				where GP.MOID = :MOID
			", array('MOID' => $data['moId']));
			foreach($resp as $item) {
				$personalIdList[] = $item['PersonalID'];
			}
		}

		if (count($personalIdList) > 0) {
			$params = json_encode(array(
				'moId' => $data['moId'],
				'personalId' => $personalIdList,
			), JSON_BIGINT_AS_STRING);
			//print_r($params);exit;
			$PersonalHistoryList = $this->exec("/SurServices/GetMoPersonalPostHistoryList", 'post', $params);
			if (is_array($PersonalHistoryList) && !empty($PersonalHistoryList['errorMsg'])) {
				return $this->createError('',$PersonalHistoryList['errorMsg']);
			}
			if (isset($_REQUEST['Debug']) && $_REQUEST['Debug'] == 1) {
				echo '<pre>';print_r($PersonalHistoryList);exit;
			}

			foreach($PersonalHistoryList as $PersonalHistory) {
				if ($PersonalHistory->id == 0) continue;
				$resp = $this->saveGetPersonalHistory($PersonalHistory, $data['pmUser_id']);
				if (!empty($resp[0]['Error_Msg'])) {
					return $this->createError('',$resp[0]['Error_Msg']);
				}
			}
		}

		return array(array('success' => true));
	}

	/**
	 * Получение информации об МО
	 */
	function getMOInfo($data) {
		$query = "
			select top 1
				ID,
				BIN,
				RNN,
				MedCode,
				FullAddress,
				FullNameKZ,
				FullNameRU
			from {$this->scheme}.GetMO with(nolock)
			where Lpu_id = :Lpu_oid
		";
		$params = array('Lpu_oid' => $data['Lpu_oid']);
		$response = $this->queryResult($query, $params);

		return $response;
	}

	/**
	 * Получение элементов структуры подразделений
	 */
	function getFPNodeList($data) {
		$lpulink = $this->getSyncObject('Lpu', $data['Lpu_oid']);

		/*if (empty($data['ParentID'])) {
			$resp = $this->importGetFPList(array(
				'MOId' => $lpulink['Object_Value'],
				'pmUser_id' => $data['pmUser_id']
			));
			if (!$this->isSuccessful($resp)) {
				return $resp;
			}
		}*/

		$params = array('MOID' => $lpulink['Object_Value']);
		$filters = "MOID = :MOID";

		if (!empty($data['ParentID'])) {
			$params['ParentID'] = $data['ParentID'];
			$filters .= " and ParentID = :ParentID";
		} else {
			$filters .= " and ParentID is null";
		}

		$query = "
			select
				FPID,
				ParentID,
				MOID,
				CodeKz,
				CodeRu,
				NameKZ,
				NameRU,
				NomenclatureKZ,
				NomenclatureRU,
				TypeKZ,
				TypeRU,
				(select count(FPID) from r101.v_GetFP with(nolock) where ParentID = FP.FPID) as leafcount
			from
				{$this->scheme}.v_GetFP FP with(nolock)
			where
				{$filters}
			order by NameRU
		";
		//echo getDebugSQL($query, $params);exit;
		return $this->queryResult($query, $params);
	}

	/**
	 * Получние списка палат/кабинетов
	 */
	function loadRoomGrid($data) {
		$params = array('FPID' => $data['FPID']);
		$filter = "";

		/*$lpulink = $this->getSyncObject('Lpu', $data['Lpu_oid']);
		$resp = $this->importGetRoomList(array(
			'idMo' => $lpulink['Object_Value'],
			'idFp' => $data['FPID'],
			'pmUser_id' => $data['pmUser_id']
		));
		if (!$this->isSuccessful($resp)) {
			return $resp;
		}*/
		
		if (!empty($data['setDate'])) {
			$params['setDate'] = $data['setDate'];
			$filter .= " and cast(GetRoom_updDT as date) = :setDate ";
		}

		$query = "
			select
				ID,
				Number,
				Name,
				SpecNameRu,
				NameSetRoomRu,
				SetRoomRu,
				SexRu,
				case when Child = 1 then 2 else 1 end as Child,
				Area
			from {$this->scheme}.v_GetRoom with(nolock)
			where FPID = :FPID
			{$filter}
		";
		//echo getDebugSQL($query, $params);exit;
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение списка коек
	 */
	function loadBedGrid($data) {
		$params = array('idRoom' => $data['idRoom']);
		$filter = "";

		/*$resp = $this->importGetBedList(array(
			'idRoom' => $data['idRoom'],
			'pmUser_id' => $data['pmUser_id']
		));
		if (!$this->isSuccessful($resp)) {
			return $resp;
		}*/
		
		if (!empty($data['setDate'])) {
			$params['setDate'] = $data['setDate'];
			$filter .= " and cast(GetBed_updDT as date) = :setDate ";
		}

		$query = "
			select
				ID,
				Name,
				BedProfileRu,
				BedType,
				convert(varchar(10), LastProfileDateBeg, 104) as LastProfileDateBeg,
				LastActionRu,
				convert(varchar(10), LastActionDateBeg, 104) as LastActionDateBeg,
				case when Temporary = 1 then 2 else 1 end as Temporary,
				StacTypeRu,
				StacDayKindRu,
				convert(varchar(10), LastStacTypeDateBeg, 104) as LastStacTypeDateBeg,
				TypeSrcFinRu,
				convert(varchar(10), LastTypSrcFinDateBeg, 104) as LastTypSrcFinDateBeg
			from {$this->scheme}.v_GetBed with(nolock)
			where roomId = :idRoom
		";
		//echo getDebugSQL($query, $params);exit;
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение списка сотрудников
	 */
	function loadPersonalGrid($data) {
		$params = array('fp' => $data['fp']);
		$filter = "";

		/*$lpulink = $this->getSyncObject('Lpu', $data['Lpu_oid']);
		$resp = $this->importGetPersonalList(array(
			'fp' => $data['fp'],
			'id' => $lpulink['Object_Value'],
			'pmUser_id' => $data['pmUser_id']
		));
		if (!$this->isSuccessful($resp)) {
			return $resp;
		}*/
		
		if (!empty($data['setDate'])) {
			$params['setDate'] = $data['setDate'];
			$filter .= " and cast(GetPersonal_updDT as date) = :setDate ";
		}

		$query = "
			select
				PersonalID,
				PersonId,
				LastName+' '+FirstName+isnull(' '+SecondName,'') as FIO,
				IIN,
				PersonalTypeRU,
				PostCategoryRu,
				PostFuncRU,
				PostCount,
				PostTypeRU,
				SpecialityRU,
				StatusPostRu,
				TypSrcFinRu,
				TypeEmployeeRu,
				Comment
			from {$this->scheme}.v_GetPersonal with(nolock)
			where FPID = :fp
		";
		//echo getDebugSQL($query, $params);exit;
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение истории должностей сотрудника
	 */
	function loadPersonalHistoryGrid($data) {
		$params = array('personalId' => $data['personalId']);
		$filter = "";

		/*$lpulink = $this->getSyncObject('Lpu', $data['Lpu_oid']);
		$resp = $this->importGetPersonalHistoryList(array(
			'personalId' => $data['personalId'],
			'moId' => $lpulink['Object_Value'],
			'pmUser_id' => $data['pmUser_id']
		));
		if (!$this->isSuccessful($resp)) {
			return $resp;
		}*/
		
		if (!empty($data['setDate'])) {
			$params['setDate'] = $data['setDate'];
			$filter .= " and cast(GetPersonalHistory_updDT as date) = :setDate ";
		}

		$query = "
			select
				ID,
				convert(varchar(10), BeginDate, 104) as BeginDate,
				convert(varchar(10), EndDate, 104) as EndDate,
				OrderNum,
				rsnWorkRu,
				rsnWorkTerminationRu,
				Comment
			from {$this->scheme}.v_GetPersonalHistory with(nolock)
			where PersonalID = :personalId
		";
		//echo getDebugSQL($query, $params);exit;
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение истории действий над койкой
	 */
	function loadBedHistoryGrid($data) {
		$params = array('idBed' => $data['idBed']);

		/*$lpulink = $this->getSyncObject('Lpu', $data['Lpu_oid']);
		$resp = $this->importGetBedHistoryList(array(
			'idBed' => $data['idBed'],
			'idMo' => $lpulink['Object_Value'],
			'pmUser_id' => $data['pmUser_id']
		));
		if (!$this->isSuccessful($resp)) {
			return $resp;
		}*/

		$query = "
			select
				ID,
				BedActionBase,
				bedActionRu,
				convert(varchar(10), BegDate, 104) as BegDate,
				convert(varchar(10), EndDate, 104) as EndDate,
				Comment
			from {$this->scheme}.v_GetBedHistory with(nolock)
			where BedID = :idBed
		";
		//echo getDebugSQL($query, $params);exit;
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение списка МО
	 */
	function loadMOList($data) {
		$params = array();
		$query = "
			select
				ID,
				BIN,
				FullNameRU
			from {$this->scheme}.v_GetMO with(nolock)
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение списка МО
	 */
	function loadMOListForSettings($data) {
		$params = array();
		$filters = array('1=1');

		if (!empty($data['FullNameRU'])) {
			$filters[] = "FullNameRU like '%'+:FullNameRU+'%'";
			$params['FullNameRU'] = $data['FullNameRU'];
		}
		if (!empty($data['MedCode'])) {
			$filters[] = "MedCode like :MedCode+'%'";
			$params['MedCode'] = $data['MedCode'];
		}

		$filters_str = implode("\n\t\t\tand ", $filters);

		$query = "
			select
				GetMO_id,
				FullNameRU,
				MedCode,
				isnull(GetMO_ex, 0) as GetMO_ex_original,
				isnull(GetMO_ex, 0) as GetMO_ex
			from
				{$this->scheme}.v_GetMO with(nolock)
			where
				{$filters_str}
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Получение списка МО промеда, связанных с МО из СУР
	 */
	function loadPromedLpuList($data) {
		$params = array();
		$query = "
			select
				L.Lpu_id,
				L.Lpu_IsOblast,
				RTRIM(L.Lpu_Name) as Lpu_Name,
				RTRIM(L.Lpu_Nick) as Lpu_Nick,
				L.Lpu_Ouz,
				L.Lpu_RegNomC,
				L.Lpu_RegNomC2,
				L.Lpu_RegNomN2,
				convert(varchar(10), L.Lpu_DloBegDate, 104) as Lpu_DloBegDate,
				convert(varchar(10), L.Lpu_DloEndDate, 104) as Lpu_DloEndDate,
				convert(varchar(10), L.Lpu_BegDate, 104) as Lpu_BegDate,
				convert(varchar(10), L.Lpu_EndDate, 104) as Lpu_EndDate,
				isnull(LL.LpuLevel_Code, 0) as LpuLevel_Code,
				ISNULL(O.Org_IsAccess, 1) as Lpu_IsAccess
			from
				v_Lpu L with(nolock)
				inner join {$this->scheme}.GetMO MO with(nolock) on MO.Lpu_id = L.Lpu_id
				inner join v_Org O with (nolock) on O.Org_id = L.Org_id
				left join LpuLevel LL with (nolock) on LL.LpuLevel_id = L.LpuLevel_id
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение списка сотрудников
	 */
	function loadPersonalWorkGrid($data) {
		$params = array();
		$filters = array();

		$filters[] = "MO.Lpu_id = :Lpu_id";
		$params['Lpu_id'] = $data['Lpu_id'];

		if (!empty($data['LastName'])) {
			$filters[] = "PW.LastName like :LastName+'%'";
			$params['LastName'] = $data['LastName'];
		}
		if (!empty($data['FirstName'])) {
			$filters[] = "PW.FirstName like :FirstName+'%'";
			$params['FirstName'] = $data['FirstName'];
		}
		if (!empty($data['SecondName'])) {
			$filters[] = "PW.SecondName like :SecondName+'%'";
			$params['SecondName'] = $data['SecondName'];
		}
		if (!empty($data['IIN'])) {
			$filters[] = "PW.IIN = :IIN";
			$params['IIN'] = $data['IIN'];
		}
		if (!empty($data['PostFuncRU'])) {
			$filters[] = "PW.PostFuncRU like :PostFuncRU+'%'";
			$params['PostFuncRU'] = $data['PostFuncRU'];
		}
		if (!empty($data['OrderNum'])) {
			$filters[] = "PW.OrderNum like :OrderNum";
			$params['OrderNum'] = $data['OrderNum'];
		}
		if (!empty($data['BeginDate'])) {
			$filters[] = "PW.BeginDate = :BeginDate";
			$params['BeginDate'] = $data['BeginDate'];
		}
		if (!empty($data['EndDate'])) {
			$filters[] = "PW.EndDate = :EndDate";
			$params['EndDate'] = $data['EndDate'];
		}

		$filters_str = implode(" and ", $filters);

		$query = "
			select
				PW.ID,
				PW.PersonalID,
				isnull(PW.LastName,'')+isnull(' '+PW.FirstName,'')+isnull(' '+PW.SecondName,'') as PersonFIO,
				PW.IIN,
				PW.PersonalTypeRU,
				PW.PostCategoryRu,
				PW.PostFuncRU,
				PW.PostCount,
				PW.PostTypeRU,
				PW.SpecialityRU,
				PW.StatusPostRu,
				PW.TypSrcFinRu,
				convert(varchar(10), PW.BeginDate, 104) as BeginDate,
				convert(varchar(10), PW.EndDate, 104) as EndDate,
				PW.OrderNum
			from
				{$this->scheme}.v_GetPersonalWork PW with(nolock)
				inner join {$this->scheme}.GetMO MO with(nolock) on MO.ID = PW.MOID
			where
				{$filters_str}
			order by
				PW.BeginDate,
				PersonFIO
		";

		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			return false;
		}

		return array(
			'data' => $resp,
			'totalCount' => count($resp)
		);
	}

	/**
	 * Получение данных о рабочем месте сотрудника
	 */
	function loadPersonalWork($data) {
		$params = array('ID' => $data['ID']);
		$query = "
			select top 1
				PW.ID as PersonalHistoryID,
				isnull(PW.LastName,'')+isnull(' '+PW.FirstName,'')+isnull(' '+PW.SecondName,'') as PersonFIO,
				PW.fpName,
				PW.PersonalTypeRU,
				PW.PostTypeRU,
				PW.PostFuncRU,
				PW.StatusPostRu,
				PW.SpecialityRU,
				PW.PostCount,
				convert(varchar(10), PW.BeginDate, 104) as BeginDate,
				convert(varchar(10), PW.EndDate, 104) as EndDate,
				PW.OrderNum,
				PW.RsnWorkRu,
				PW.RsnWorkTerminationRu
			from {$this->scheme}.v_GetPersonalWork PW with(nolock)
			where PW.ID = :ID
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Сохранение связи рабочего места из Промед и СУР
	 */
	function savePersonalHistoryWP($data) {
		$params = array(
			'ID' => $data['ID'],
			'WorkPlace_id' => $data['MedStaffFact_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		$query = "
			select
				GHWP.WorkPlace_id,
				MSF.Person_Fio,
				PM.PostMed_Name,
				LS.LpuSection_Code,
				LS.LpuSection_Name
			from
				{$this->scheme}.v_GetPersonalHistoryWP GHWP with(nolock)
				inner join v_MedStaffFact MSF with(nolock) on MSF.MedStaffFact_id = GHWP.WorkPlace_id
				left join v_LpuSection LS with(nolock) on LS.LpuSection_id = MSF.LpuSection_id
				left join v_PostMed PM with(nolock) on PM.PostMed_id = MSF.Post_id
			where
				GHWP.ID = :ID
		";
		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при получении существующих связей мест работы');
		}
		if (count($resp) > 0 && empty($data['ignoreExistsLinkCheck'])) {
			$wp_list = array();
			$alredy_exists = false;

			foreach($resp as $item) {
				if ($item['WorkPlace_id'] == $params['WorkPlace_id']) {
					$alredy_exists = true;
					break;
				}
				$wp_list[] = "<li>{$item['PostMed_Name']} {$item['Person_Fio']}, {$item['LpuSection_Code']}. {$item['LpuSection_Name']}</li>";
			}

			if (!$alredy_exists) {
				$msg = "Место работы СУР уже связано со следующими местами работы:";
				$msg .= "<ul style='list-style: inside'>\n".implode("\n", $wp_list)."\n</ul>";
				$msg .= "Продолжить сохранение?";

				$this->_saveResponse['Alert_Msg'] = $msg;
				return $this->createError(121,'YesNo');
			}
		}

		$query = "
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000),
				@GetPersonalHistory_id bigint,
				@GetPersonalHistoryWP_id bigint;
			set @GetPersonalHistory_id = (
				select top 1 GetPersonalHistory_id
				from {$this->scheme}.GetPersonalHistory with(nolock)
				where ID = :ID
			)
			set @GetPersonalHistoryWP_id = (
				select top 1 GetPersonalHistoryWP_id
				from {$this->scheme}.GetPersonalHistoryWP with(nolock)
				where WorkPlace_id = :WorkPlace_id
			)
			if @GetPersonalHistoryWP_id is null
				exec {$this->scheme}.p_GetPersonalHistoryWP_ins
				@GetPersonalHistoryWP_id = @GetPersonalHistoryWP_id output,
				@GetPersonalHistory_id = @GetPersonalHistory_id,
				@ID = :ID,
				@WorkPlace_id = :WorkPlace_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
			else
				exec {$this->scheme}.p_GetPersonalHistoryWP_upd
				@GetPersonalHistoryWP_id = @GetPersonalHistoryWP_id output,
				@GetPersonalHistory_id = @GetPersonalHistory_id,
				@ID = :ID,
				@WorkPlace_id = :WorkPlace_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
			select @GetPersonalHistoryWP_id as GetPersonalHistoryWP_id, @Error_Code as Error_Code, @Error_Message as Error_Msg
		";

		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при сохранении связи рабочего места с идентификатором из СУР');
		}
		return $resp;
	}

	/**
	 * Удаление связи рабочего места из Промед и СУР
	 */
	function deletePersonalHistoryWP($data) {
		$params = array('WorkPlace_id' => $data['MedStaffFact_id']);

		$query = "
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000),
				@GetPersonalHistoryWP_id bigint;
			set @GetPersonalHistoryWP_id = (
				select top 1 GetPersonalHistoryWP_id
				from {$this->scheme}.GetPersonalHistoryWP with(nolock)
				where WorkPlace_id = :WorkPlace_id
			)
			exec {$this->scheme}.p_GetPersonalHistoryWP_del
				@GetPersonalHistoryWP_id = @GetPersonalHistoryWP_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
			select @Error_Code as Error_Code, @Error_Message as Error_Msg
		";

		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при удалении связи рабочего места с идентификатором из СУР');
		}
		return $resp;
	}

	/**
	 * @param array $data
	 * @return array|false
	 */
	function saveMOSettings($data) {
		$object = $this->scheme.'.GetMO';
		$saveData = $data['saveData'];

		if (!is_array($saveData)) {
			return $this->createError('','Не передан массив данных для сохранения');
		}

		$this->beginTransaction();

		foreach($saveData as $item) {
			$item['pmUser_id'] = $data['pmUser_id'];
			$resp = $this->swUpdate($object, $item);
			if (!$this->isSuccessful($resp)) {
				$this->rollbackTransaction();
				return $resp;
			}
		}

		$this->commitTransaction();

		return array(array('success' => true));
	}

	/**
	 * Разрыв соединения c клиентом после запуска импорта
	 */
	function sendImportResponse() {
		ignore_user_abort(true);
		$response = array("success" => "true");

		if (function_exists('fastcgi_finish_request')) {
			echo json_encode($response);
			if (session_id()) session_write_close();
			fastcgi_finish_request();
		} else {
			ob_start();
			echo json_encode($response);

			$size = ob_get_length();

			header("Content-Length: $size");
			header("Content-Encoding: none");
			header("Connection: close");

			ob_end_flush();
			ob_flush();
			flush();

			if (session_id()) session_write_close();
		}
	}

	/**
	 * @param string|array $error
	 * @throws Exception
	 */
	function throwError($error) {
		throw new Exception(is_array($error)?implode("<br/>", $error):$error);
	}

	/**
	 * Запуск импорта данных из СУР
	 */
	function runImport($data) {
		set_time_limit(0);
		ini_set("max_execution_time", "0");
		ini_set("max_input_time", "0");
		ini_set("default_socket_timeout", "999");

		$this->load->helper('ServiceListLog');

		$pmUser_id = !empty($data['pmUser_id'])?$data['pmUser_id']:1;

		$log = new ServiceListLog(5, $pmUser_id);

		$resp = $log->start();
		if (!$this->isSuccessful($resp)) {
			return $resp;
		}

		if (empty($_REQUEST['getDebug'])) {
			$this->sendImportResponse();
		}

		$this->load->helper('ShutdownErrorHandler');
		registerShutdownErrorHandler(array($this, 'shutdownErrorHandler'), function($error) use($log) {
			$log->add(false, array("Импорт данных из сервиса СУР завершён с ошибкой:", $error));
			$log->finish(false);
		});

		try{
			set_error_handler(array($this, 'exceptionErrorHandler'));

			$objects = array();
			$allObjects = array(
				'MO',
				'FP',
				'Room',
				'Bed',
				'BedHistory',
				'Personal',
				'PersonalHistory'
			);

			if (is_string($data['objects'])) {
				$data['objects'] = explode("|", $data['objects']);
			}
			if (in_array('all', $data['objects'])) {
				$objects = $allObjects;
			} else {
				$objects = array_intersect($allObjects, $data['objects']);
			}
			if (count($objects) == 0) {
				throw new Exception('Отсутсвуют объекты для импорта');
			}

			$regions = array();
			if (!empty($data['regions'])) {
				$regions = explode("|", $data['regions']);
			}
			if (empty($regions)) {
				$regions = array(2,4,5,16,17,18);
			}

			$log->add(true, "Запускается импорт данных из сервиса СУР: ".implode(", ", $objects));

			$config = $this->config->item('SUR');
			if (empty($config)) {
				throw new Exception('Не найден конфиг для соединения с сервисом СУР');
			}

			if (in_array('MO', $objects)) {
				//importGetMOList. Импорт выполняется по номерам регионов
				foreach ($regions as $regionNum) {
					$log->add(true, "Импорт МО региона №{$regionNum}");

					$resp = $this->importGetMOList(array(
						'adrUnit' => $regionNum,
						'recStart' => 0,
						'recCount' => 500,
						'pmUser_id' => $pmUser_id
					));
					if (!$this->isSuccessful($resp)) {
						$this->throwError(array(
							"Ошибка при импорте МО региона №{$regionNum}:",
							$resp[0]['Error_Msg']
						));
					}
				}
			}

			$MOList = array();	//На случай если не нужно выполнять импорты кроме МО
			if (count(array_diff($objects, array('MO'))) > 0) {
				$regions_str = implode(",", $regions);
				//Дальше импорт идет по каждой МО из СУР. Получаем список идентификаторов МО из СУР.
				$MOList = $this->queryResult("
					select MO.ID from {$this->scheme}.GetMO MO with(nolock)
					inner join v_ObjectSynchronLog OSL with(nolock) on OSL.ObjectSynchronLogService_id = :Service_id
						and OSL.Object_Name = 'MOAdrUnitLink' and OSL.Object_id = MO.ID
					where OSL.Object_sid in ({$regions_str})		--Ограничение по регионам
					and isnull(MO.GetMO_ex, 0) = 0
				", array('Service_id' => $this->ObjectSynchronLog_model->serviceId));
				if (!is_array($MOList)) {
					throw new Exception('Ошибка при запросе списка МО из БД');
				}
			}
			
			// Проверяем наличие файла и данные в файле sur_mo.txt и получаем из файла данные 
			$out_dir = IMPORTPATH_ROOT;
			if (!is_dir($out_dir)) {
				mkdir($out_dir, 0777, true);
			}
			//$out_dir = sys_get_temp_dir();
			$sur_mo_file = $out_dir . '/sur_upload_stopped_moid.txt';
			if (file_exists($sur_mo_file)) {
				$mosid = file_get_contents($sur_mo_file);
			} else {
				$mosid = 0;
			}
			$is_idsearch = false;
			if ((int)$mosid>0) {
				$is_idsearch = true;
			}

			$hasErrors = false;

			foreach($MOList as $MO) {
				try {
				if ($is_idsearch) { 
					if ($mosid==$MO['ID']) { 
						// если нашли id, то просто начинаем работать дальше по этому идентификатору
						$is_idsearch = false;
						$log->add(true, "Нашли идентификатор МО={$mosid}");
					} else {
						continue; // пропускаем эту итерацию, ищем нужный идентификатор id
					}
				}
					$log->add(true, "Импорт данных по МО={$MO['ID']}");
				// записываем ID МО в файл, чтобы вернуться к нему, если что 
				file_put_contents($sur_mo_file, $MO['ID']);

				if (in_array('FP', $objects)) {
					$resp = $this->importGetFPList(array(
						'MOId' => $MO['ID'],
						'pmUser_id' => $pmUser_id
					));
					if (!$this->isSuccessful($resp)) {
						$this->throwError(array(
							"Ошибка импорта структ. подразд. по МО={$MO['ID']}:",
							$resp[0]['Error_Msg']
						));
					}
				}

				if (in_array('Room', $objects)) {
					$resp = $this->importGetRoomList(array(
						'idMo' => $MO['ID'],
						'pmUser_id' => $pmUser_id
					));
					if (!$this->isSuccessful($resp)) {
						$this->throwError(array(
							"Ошибка импорта палат/кабинетов по МО={$MO['ID']}:",
							$resp[0]['Error_Msg']
						));
					}
				}

				if (in_array('Bed', $objects) || in_array('BedHistory', $objects)) {
					//Получение списка структурных подразделений в МО (костыль ибо импорт FP работает не для всех МО)
					$query = "
						select distinct Object_id as ID
						from v_ObjectSynchronLog OSL with(nolock)
						where OSL.ObjectSynchronLogService_id = :Service_id
						and OSL.Object_Name like 'FPMOLink' and OSL.Object_sid = :MOID
					";
					$queryParams = array(
						'Service_id' => $this->ObjectSynchronLog_model->serviceId,
						'MOID' => $MO['ID']
					);
					$FPList = $this->queryResult($query, $queryParams);
					if (!is_array($FPList)) {
						$FPList = array();
						$this->throwError("Ошибка при получении структ. подразд. из БД промеда по МО={$MO['ID']}");
					}

					foreach($FPList as $FP) {
						$params = array(
							'idMo' => $MO['ID'],
							'idFp' => $FP['ID'],
							'pmUser_id' => $pmUser_id
						);

						if (in_array('Bed', $objects)) {
							$resp = $this->importGetBedList($params);
							if (!$this->isSuccessful($resp)) {
								$this->throwError(array(
									"Ошибка импорта коек по подразделению={$FP['ID']}:",
									$resp[0]['Error_Msg']
								));
							}
						}

						if (in_array('BedHistory', $objects)) {
							$resp = $this->importGetBedHistoryList($params);
							if (!$this->isSuccessful($resp)) {
								$this->throwError(array(
									"Ошибка импорта истории состояния коек по подразделению={$FP['ID']}:",
									$resp[0]['Error_Msg']
								));
							}
						}
					}
				}

				if (in_array('Personal', $objects)) {
					$resp = $this->importGetPersonalList(array(
						'id' => $MO['ID'],
						'pmUser_id' => $data['pmUser_id']
					));
					if (!$this->isSuccessful($resp)) {
						$this->throwError(array(
							"Ошибка импорта врачей по МО={$MO['ID']}:",
							$resp[0]['Error_Msg']
						));
					}
				}

				if (in_array('PersonalHistory', $objects)) {
					$resp = $this->importGetPersonalHistoryList(array(
						'moId' => $MO['ID'],
						'pmUser_id' => $data['pmUser_id']
					));
					if (!$this->isSuccessful($resp)) {
						$this->throwError(array(
							"Ошибка импорта истории должностей врачей по МО={$MO['ID']}:",
							$resp[0]['Error_Msg']
						));
					}
				}
				} catch(Exception $e) {
					$error = $e->getMessage();
					if (strpos($error, 'Произошла ошибка.')) {
						$hasErrors = true;
						$log->add(false, array("Не удалось выполнить загрузку данных МО={$MO['ID']}.", $error));
						continue;
			}
				}
			}
			// Импорт из СУР прошел успешно, поэтому обнуляем файл с ID МО
			file_put_contents($sur_mo_file, '');

			$log->add(true, "Импорт данных из сервиса СУР завершён успешно");
			$log->finish(!$hasErrors);
		} catch(Exception $e) {
			restore_exception_handler();

			$code = $e->getCode();
			$error = $e->getMessage();

			$log->add(false, array("Импорт данных из сервиса СУР завершён с ошибкой:", $error));
			$log->finish(false);

			return $this->createError($code, $error);
		}

		return array(array('success' => true));
	}

	/**
	 * Функция для тестирования СУР
	 */
	function test() {
		$adrUnitList = array(
			'1' => 'АКМОЛИНСКАЯ область',
			'2' => 'АКТЮБИНСКАЯ область',
			'3' => 'АЛМАТИНСКАЯ область',
			'4' => 'Атырауская область',			//*
			'5' => 'ЗАПАДНО-КАЗАХСТАНСКАЯ область',	//*
			'6' => 'АКМОЛИНСКАЯ область / ЖАМБЫЛСКАЯ область',    //???
			'7' => 'КАРАГАНДИНСКАЯ область / КЫЗЫЛОРДИНСКАЯ область',	//???
			'8' => 'КОСТАНАЙСКАЯ область',
			'9' => 'КЫЗЫЛОРДИНСКАЯ область',
			'10' => 'МАНГИСТАУСКАЯ область',		//*
			//'11' => 'ЮЖНО-КАЗАХСТАНСКАЯ область',
			'12' => 'ПАВЛОДАРСКАЯ область',
			'13' => 'СЕВЕРО-КАЗАХСТАНСКАЯ область',
			'13' => 'СЕВЕРО-КАЗАХСТАНСКАЯ область',
			'14' => 'ВОСТОЧНО-КАЗАХСТАНСКАЯ область',
			'15' => 'Алматы / АЛМАТИНСКАЯ область',
			'16' => 'АСТАНА',						//*
			'17' => 'ЮЖНО-КАЗАХСТАНСКАЯ область',
		);

	}
}
?>