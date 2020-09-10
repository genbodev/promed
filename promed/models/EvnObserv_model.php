<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnObserv_model - модель для работы с наблюдениями за пациентами
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2016 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			28.09.2016
 */

class EvnObserv_model extends swModel {
	protected $_dictionaries = array();

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Получение данных из справочников
	 */
	function getDictionary($name) {
		if (!isset($this->_dictionaries[$name])) {
			$query = "select * from v_{$name} with(nolock)";
			$resp = $this->queryResult($query, $name);
			$dictionary = array();
			foreach($resp as $item) {
				$dictionary[$item[$name.'_id']] = $item;
			}
			$this->_dictionaries[$name] = $dictionary;
		}
		return $this->_dictionaries[$name];
	}

	/**
	 * Получение списка наблюдений
	 */
	function loadEvnObservGrid($data) {
		$filters = array();
		$params = array();

		$filters[] = "E.Person_id = :Person_id";
		$params['Person_id'] = $data['Person_id'];

		if (!empty($data['PersonNewBorn_id'])) {
			$filters[] = "EON.PersonNewBorn_id = :PersonNewBorn_id";
			$params['PersonNewBorn_id'] = $data['PersonNewBorn_id'];
		}

		$filters_str = implode("\nand ", $filters);
		$query = "
			select
				EO.EvnObserv_id,
				E.Evn_pid as EvnObserv_pid,
				EON.PersonNewBorn_id,
				convert(varchar(10), E.Evn_setDate, 104) as EvnObserv_setDate,
				OTT.ObservTimeType_Name as ObservTimeType_Name
			from
				EvnObserv EO with(nolock)
				inner join v_Evn E with(nolock) on E.Evn_id = EO.Evn_id
				left join EvnObservNewborn EON with(nolock) on EON.EvnObservNewBorn_id = EO.EvnObserv_id
				left join v_ObservTimeType OTT with(nolock) on OTT.ObservTimeType_id = EO.ObservTimeType_id
			where
				{$filters_str}
		";
		//echo getDebugSQL($query, $params);exit;
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			return false;
		}

		$EvnObserv_ids = array();
		foreach($response as $item) {
			$EvnObserv_ids[] = $item['EvnObserv_id'];
		}

		if (count($EvnObserv_ids) > 0) {
			$ids_str = implode(",", $EvnObserv_ids);
			$query = "
				select
					EOD.EvnObservData_Value,
					EOD.ObservParamType_id,
					EOD.EvnObserv_id
				from 
					v_EvnObservData EOD with(nolock)
				where
					EOD.EvnObserv_id in ({$ids_str})
			";
			$resp = $this->queryResult($query);
			if (!is_array($resp)) {
				return false;
			}

			$EvnObservValuesList = array();
			foreach($resp as $EvnObservData) {
				$evn_id = $EvnObservData['EvnObserv_id'];
				$param_id = $EvnObservData['ObservParamType_id'];
				$value = $EvnObservData['EvnObservData_Value'];

				$EvnObservValuesList[$evn_id][$param_id] = $value;
			}

			foreach($response as &$item) {
				if (!isset($EvnObservValuesList[$item['EvnObserv_id']])) continue;
				$values = $EvnObservValuesList[$item['EvnObserv_id']];

				if (!empty($values[1]) && !empty($values[2])) {
					$item['art_davlenie'] = $values[1].'/'.$values[2];
				}
				if (!empty($values[3])) {
					$item['puls'] = $values[3];
				}
				if (!empty($values[4])) {
					$item['temperatura'] = $values[4];
				}
				if (!empty($values[5])) {
					$item['chastota_dyihaniya'] = $values[5];
				}
				if (!empty($values[6])) {
					$item['ves'] = $values[6];
				}
				if (!empty($values[7])) {
					$item['vyipito_jidkosti'] = $values[7];
				}
				if (!empty($values[8])) {
					$item['kol-vo_mochi'] = $values[8];
				}
				if (!empty($values[9])) {
					$dict = $this->getDictionary('YesNo');
					$item['stul'] = $dict[$values[9]]['YesNo_Name'];
				}
				if (!empty($values[10])) {
					$dict = $this->getDictionary('YesNo');
					$item['vanna'] = $dict[$values[10]]['YesNo_Name'];
				}
				if (!empty($values[11])) {
					$dict = $this->getDictionary('YesNo');
					$item['vanna'] = $dict[$values[11]]['YesNo_Name'];
				}
				if (!empty($values[12])) {
					$dict = $this->getDictionary('ObservPesultType');
					$item['reaktsiya_zrachka'] = $dict[$values[12]]['ObservPesultType_Name'];
				}
				if (!empty($values[13])) {
					$dict = $this->getDictionary('ObservPesultType');
					$item['reaktsiya_na_osmotr'] = $dict[$values[13]]['ObservPesultType_Name'];
				}
			}
		}

		return $response;
	}

	/**
	 * Получение данных наблюдения
	 */
	function loadEvnObservData($data) {
		$params = array('EvnObserv_id' => $data['EvnObserv_id']);
		$query = "
			select
				EOD.EvnObservData_id,
				EOD.EvnObserv_id,
				EOD.EvnObservData_Value,
				EOD.ObservParamType_id
			from 
				v_EvnObservData EOD with(nolock)
			where
				EOD.EvnObserv_id = :EvnObserv_id
		";
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			return false;
		}
		return $response;
	}

	/**
	 * Получение данных наблюдения для редактирования
	 */
	function loadEvnObservForm($data) {
		$params = array('EvnObserv_id' => $data['EvnObserv_id']);
		$query = "
			select top 1
				EO.EvnObserv_id,
				E.Evn_pid as EvnObserv_pid,
				E.Person_id,
				E.PersonEvn_id,
				E.Server_id,
				convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
				convert(varchar(10), E.Evn_setDate, 104) as EvnObserv_setDate,
				EO.ObservTimeType_id
			from
				EvnObserv EO with(nolock)
				inner join v_Evn E with(nolock) on E.Evn_id = EO.EvnObserv_id
				left join EvnObservNewBorn EON with(nolock) on EON.EvnObservNewBorn_id = EO.EvnObserv_id
				left join v_Person_all PS with(nolock) on PS.PersonEvn_id = E.PersonEvn_id and PS.Server_id = E.Server_id
			where
				EO.EvnObserv_id = :EvnObserv_id
		";
		$response = $this->queryResult($query, $params);
		if (!is_array($response) || count($response) == 0) {
			return false;
		}

		$resp = $this->loadEvnObservData($data);
		if (!is_array($resp)) {
			return false;
		}
		$tmp = array();
		foreach($resp as $item) {
			$type = $item['ObservParamType_id'];
			$value = $item['EvnObservData_Value'];

			$response[0]['val_'.$type] = $value;
		}

		return $response;
	}

	/**
	 * Сохранение наблюдения
	 */
	function saveEvnObserv($data) {
		$this->beginTransaction();

		$params = array(
			'EvnObserv_id' => !empty($data['EvnObserv_id'])?$data['EvnObserv_id']:null,
			'EvnObserv_pid' => !empty($data['EvnObserv_pid'])?$data['EvnObserv_pid']:null,
			'EvnObserv_setDT' => $data['EvnObserv_setDate'],
			'PersonEvn_id' => $data['PersonEvn_id'],
			'Server_id' => $data['Server_id'],
			'Lpu_id' => $data['Lpu_id'],
			'ObservTimeType_id' => $data['ObservTimeType_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		$fields = '';
		$object = 'EvnObserv';
		if (!empty($data['PersonNewBorn_id'])) {

			if ('ufa' == $this->regionNick) {
				//создание новой специфики со значениями равной первой
				$query = "	
					declare
						@PersonNewborn_pid bigint = NULL,
						@PersonNewborn_did bigint = NULL,
						@PersonNewborn_id bigint = NULL,
						@Res bigint = NULL,
						@ErrCode int,
						@ErrMessage varchar(4000);
					
					select @PersonNewborn_id=:PersonNewBorn_id;
		
					--поиск идентификатора дочерней специфики
					select @PersonNewborn_did=PersonNewborn_id from v_PersonNewborn with(nolock) where PersonNewborn_pid=@PersonNewborn_id
		
					if (@PersonNewborn_did is not null)
					begin
						exec p_PersonNewborn_Copy
							@PersonNewBorn_id_new = @Res output,
							@PersonNewBorn_id = @PersonNewborn_id,
							@PersonNewBorn_pid = NULL,
							@EvnPS_id = NULL,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output;
									
					end else
					begin
						set @Res = @PersonNewborn_id;
					end
								
					select @Res as PersonNewBorn_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";

				$queryParams['PersonNewBorn_id'] = $data['PersonNewBorn_id'];
				
				//echo getDebugSQL($query, $queryParams);exit();
				$res = $this->queryResult($query, $queryParams);
				$data['PersonNewBorn_id'] = $res[0]['PersonNewBorn_id'];
			}			
			
			$object = 'EvnObservNewBorn';
			$params['PersonNewBorn_id'] = $data['PersonNewBorn_id'];
			$fields .= '@PersonNewBorn_id = :PersonNewBorn_id,';
		}

		if (empty($params[$object.'_id'])) {
			$procedure = "p_{$object}_ins";
		} else {
			$procedure = "p_{$object}_upd";
		}

		$query = "
			declare
				@Error_Message varchar(4000),
				@Error_Code bigint,
				@Res bigint = :EvnObserv_id;
			exec {$procedure}
				@{$object}_id = @Res output,
				@{$object}_pid = :EvnObserv_pid,
				@{$object}_setDT = :EvnObserv_setDT,
				@PersonEvn_id = :PersonEvn_id,
				@Server_id = :Server_id,
				@Lpu_id = :Lpu_id,
				@ObservTimeType_id = :ObservTimeType_id,
				{$fields}
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
			select @Res as EvnObserv_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			$this->rollbackTransaction();
			return $this->createError('','Ошибка при сохранении наблюдения');
		}
		if (!$this->isSuccessful($response)) {
			$this->rollbackTransaction();
			return $response;
		}
		$data['EvnObserv_id'] = $response[0]['EvnObserv_id'];


		$EvnObservDataList = json_decode($data['EvnObservDataList'], true);

		$params = array('EvnObserv_id' => $data['EvnObserv_id']);
		$query = "
			select
				EvnObservData_id,
				EvnObservData_Value,
				ObservParamType_id
			from v_EvnObservData EOD with(nolock)
			where EOD.EvnObserv_id = :EvnObserv_id
		";
		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при получении данных наблюдения');
		}
		$savedDataList = array();
		foreach($resp as $item) {
			$savedDataList[$item['ObservParamType_id']] = $item;
		}

		foreach($EvnObservDataList as $EvnObservData) {
			$type = $EvnObservData['ObservParamType_id'];
			$value = $EvnObservData['EvnObservData_Value'];
			$savedData = isset($savedDataList[$type])?$savedDataList[$type]:null;

			$EvnObservData['EvnObservData_id'] = $savedData?$savedData['EvnObservData_id']:null;
			$EvnObservData['EvnObserv_id'] = $data['EvnObserv_id'];
			$EvnObservData['pmUser_id']	= $data['pmUser_id'];

			$resp = null;
			$needSave = (!empty($value) && (!$savedData || $savedData['EvnObservData_Value'] != $value));
			$needDelete = (empty($value) && $savedData);

			if ($needSave) {
				$resp = $this->saveEvnObservData($EvnObservData);
			} else if ($needDelete) {
				$resp = $this->deleteEvnObservData($EvnObservData);
			}
			if ($resp && !$this->isSuccessful($resp)) {
				$this->rollbackTransaction();
				return $resp;
			}
		}

		$this->commitTransaction();
		return $response;
	}

	/**
	 * Сохранение данных наблюдения
	 */
	function saveEvnObservData($data) {
		$params = array(
			'EvnObservData_id' => !empty($data['EvnObservData_id'])?$data['EvnObservData_id']:null,
			'EvnObservData_Value' => !empty($data['EvnObservData_Value'])?$data['EvnObservData_Value']:null,
			'ObservParamType_id' => $data['ObservParamType_id'],
			'EvnObserv_id' => $data['EvnObserv_id'],
			'pmUser_id' => $data['pmUser_id'],
		);
		if (empty($params['EvnObservData_id'])) {
			$procedure = 'p_EvnObservData_ins';
		} else {
			$procedure = 'p_EvnObservData_upd';
		}
		$query = "
			declare
				@Error_Message varchar(4000),
				@Error_Code bigint,
				@Res bigint = :EvnObservData_id;
			exec {$procedure}
				@EvnObservData_id = @Res output,
				@EvnObservData_Value = :EvnObservData_Value,
				@ObservParamType_id = :ObservParamType_id,
				@EvnObserv_id = :EvnObserv_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
			select @Res as EvnObservData_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";
		//echo getDebugSQL($query, $params);
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			return $this->createError('','Ошибка при сохранении данных наблюдения');
		}
		return $response;
	}

	/**
	 * Удаление данных наблюдения
	 */
	function deleteEvnObservData($data) {
		$params = array('EvnObservData_id' => $data['EvnObservData_id']);
		$query = "
			declare
				@Error_Message varchar(4000),
				@Error_Code bigint;
			exec p_EvnObservData_del
				@EvnObservData_id = :EvnObservData_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
			select @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			return $this->createError('','Ошибка при удалении данных наблюдения');
		}
		return $response;
	}

	/**
	 * Удаление наблюдения
	 */
	function deleteEvnObserv($data) {
		$params = array(
			'EvnObserv_id' => $data['EvnObserv_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		$EvnObservDataList = $this->loadEvnObservData($params);
		if (!is_array($EvnObservDataList)) {
			return $this->createError('Ошика при получении данных наблюдения');
		}
		foreach($EvnObservDataList as $EvnObservData) {
			$resp = $this->deleteEvnObservData($EvnObservData);
			if (!$this->isSuccessful($resp)) {
				return $resp;
			}
		}

		$query = "
			declare
				@Error_Message varchar(4000),
				@Error_Code bigint;
			exec p_EvnObserv_del
				@EvnObserv_id = :EvnObserv_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
			select @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			return $this->createError('','Ошибка при удалении наблюдения');
		}
		return $response;
	}
}