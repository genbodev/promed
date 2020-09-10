<?php defined('BASEPATH') or die ('No direct script access allowed');
class PersonIdentPackage_model extends swModel {
	/**
	 * construct
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * @param array $data
	 * @return array
	 */
	function loadPersonIdentPackagePosHistoryGrid($data) {
		$params = array(
			'Person_id' => $data['Person_id']
		);

		$query = "
			select
				-- select
				PIPP.PersonIdentPackagePos_id,
				PIPP.PersonIdentPackage_id,
				PIPP.Person_id,
				convert(varchar(10), PIPP.PersonIdentPackagePos_updDT, 104) as PersonIdentPackagePos_updDT,
				convert(varchar(10), PIPP.PersonIdentPackagePos_identDT, 104) as PersonIdentPackagePos_identDT,
				convert(varchar(10), PIPP.PersonIdentPackagePos_identDT2, 104) as PersonIdentPackagePos_identDT2,
				PIPT.PersonIdentPackageTool_id,
				PIPT.PersonIdentPackageTool_Name,
				E.Evn_id,
				E.Evn_pid,
				E.Evn_rid,
				E.EvnClass_Name,
				E.EvnClass_SysNick,
				PIS.PersonIdentState_id,
				PIS.PersonIdentState_Code,
				PIS.PersonIdentState_Name,
				null as Errors
				-- end select
			from
				-- from
				v_PersonIdentPackagePos PIPP with(nolock)
				left join v_PersonIdentState PIS with(nolock) on PIS.PersonIdentState_id = PIPP.PersonIdentState_id
				left join v_PersonIdentPackageTool PIPT with(nolock) on PIPT.PersonIdentPackageTool_id = PIPP.PersonIdentPackageTool_id
				left join v_Evn E with(nolock) on E.Evn_id = PIPP.Evn_id
				left join v_EvnPL EPL with(nolock) on EPL.EvnPL_Id = E.Evn_id
				left join v_EvnPS EPS with(nolock) on EPS.EvnPS_Id = E.Evn_id
				-- end from
			where
				-- where
				PIPP.Person_id = :Person_id
				and PIPP.PersonIdentPackagePos_identDT is not null
				-- end where
			order by
				-- order by
				PIPP.PersonIdentPackagePos_identDT
				-- end order by
		";

		$count = $this->getFirstResultFromQuery(getCountSQLPH($query), $params);
		$result = $this->queryResult(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
		if (!is_array($result) || $count === false) {
			return false;
		}

		$ids = array();
		$responseData = array();
		foreach($result as $item) {
			$key = $item['PersonIdentPackagePos_id'];
			$ids[] = $key;
			$responseData[$key] = $item;
		}

		if (count($ids) > 0) {
			$ids_str = implode(",", $ids);

			$query = "
				select
					PersonIdentPackagePos_id,
					isnull(PersonIdentPackagePosError_ErrCode+'. ','')+PersonIdentPackagePosError_ErrDescr as PersonIdentPackagePosError
				from v_PersonIdentPackagePosError with(nolock)
				where PersonIdentPackagePos_id in ({$ids_str})
			";
			$result = $this->queryResult($query);
			if (!is_array($result)) {
				return false;
			}

			$errorsByPos = array();
			foreach($result as $item) {
				$key = $item['PersonIdentPackagePos_id'];
				$errorsByPos[$key][] = $item['PersonIdentPackagePosError'];
			}

			foreach($errorsByPos as $key => $errors) {
				$responseData[$key]['Errors'] = implode("<br/>", $errors);
			}
		}

		$response = array(
			'totoalCount' => $count,
			'data' => array_values($responseData),
		);

		return $response;
	}

	/**
	 * Сохранение данных пакета для идентификации
	 */
	function savePersonIdentPackage($data) {
		$params = array(
			'PersonIdentPackage_id' => !empty($data['PersonIdentPackage_id'])?$data['PersonIdentPackage_id']:null,
			'PersonIdentPackage_Name' => $data['PersonIdentPackage_Name'],
			'PersonIdentPackage_begDate' => $data['PersonIdentPackage_begDate'],
			'PersonIdentPackage_IsResponseRetrieved' => $data['PersonIdentPackage_IsResponseRetrieved'],
			'PersonIdentPackage_resDate' => !empty($data['PersonIdentPackage_resDate'])?$data['PersonIdentPackage_resDate']:null,
			'pmUser_id' => $data['pmUser_id'],
		);
		if (empty($params['PersonIdentPackage_id'])) {
			$procedure = "p_PersonIdentPackage_ins";
		} else {
			$procedure = "p_PersonIdentPackage_upd";
		}
		$query = "
			declare
				@Error_Code int,
				@Error_Message varchar(4000),
				@Res bigint = :PersonIdentPackage_id
			exec {$procedure}
				@PersonIdentPackage_id = @Res output,
				@PersonIdentPackage_Name = :PersonIdentPackage_Name,
				@PersonIdentPackage_begDate = :PersonIdentPackage_begDate,
				@PersonIdentPackage_IsResponseRetrieved = :PersonIdentPackage_IsResponseRetrieved,
				@PersonIdentPackage_resDate = :PersonIdentPackage_resDate,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
			select @Res as PersonIdentPackage_id, @Error_Code as Error_Code, @Error_Message as Error_Msg
		";
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			return $this->createError('','Ошибка при сохранении пакета данных для идентификации');
		}
		return $response;
	}

	/**
	 * @param array $data
	 * @return array
	 */
	function addPersonIdentPackagePos($data) {
		$this->beginTransaction();

		$dt = date_create();
		$params = array(
			'PersonIdentPackagePos_id' => !empty($data['PersonIdentPackagePos_id'])?$data['PersonIdentPackagePos_id']:null,
			'PersonIdentPackage_id' => !empty($data['PersonIdentPackage_id'])?$data['PersonIdentPackage_id']:null,
			'Evn_id' => !empty($data['Evn_id'])?$data['Evn_id']:null,
			'CmpCallCard_id' => !empty($data['CmpCallCard_id'])?$data['CmpCallCard_id']:null,
			'Person_id' => $data['Person_id'],
			'Lpu_id' => !empty($data['Lpu_id'])?$data['Lpu_id']:null,
			'PersonIdentPackagePos_identDT' => !empty($data['PersonIdentPackagePos_identDT'])?$data['PersonIdentPackagePos_identDT']:$dt->format('Y-m-d'),
			'PersonIdentPackagePos_identDT2' => !empty($data['PersonIdentPackagePos_identDT2'])?$data['PersonIdentPackagePos_identDT2']:null,
			'PersonIdentState_id' => !empty($data['PersonIdentState_id'])?$data['PersonIdentState_id']:null,
			'PersonIdentPackageTool_id' => !empty($data['PersonIdentPackageTool_id'])?$data['PersonIdentPackageTool_id']:null,
			'pmUser_id' => $data['pmUser_id'],
		);

		$query = "
			declare @begDate date = :PersonIdentPackagePos_identDT
			declare @endDate date = :PersonIdentPackagePos_identDT2
			declare @bigDate date = dateadd(year, 100, dbo.tzGetDate())
			select top 1 count(*) as cnt
			from v_PersonIdentPackagePos PIPP with(nolock)
			where PIPP.PersonIdentPackage_id is null
			and PIPP.PersonIdentState_id is null
			and PIPP.Person_id = :Person_id
			and PIPP.PersonIdentPackagePos_identDT < isnull(@endDate, @bigDate)
			and isnull(PIPP.PersonIdentPackagePos_identDT2, @bigDate) > @begDate
		";
		//echo getDebugSQL($query, $params);exit;
		$count = $this->getFirstResultFromQuery($query, $params);
		if ($count === false) {
			$this->rollbackTransaction();
			return $this->createError('','Ошибка при проверке наличия записи на идентификацию в ТФОМС');
		}
		if ($count > 0) {
			$this->rollbackTransaction();
			return $this->createError(302,'Уже существует запись на идентификацию в ТФОМС');
		}

		$query = "
			declare
				@Error_Code int,
				@Error_Message varchar(4000),
				@Res bigint = :PersonIdentPackagePos_id
			exec p_PersonIdentPackagePos_ins
				@PersonIdentPackagePos_id = @Res output,
				@PersonIdentPackage_id = :PersonIdentPackage_id,
				@Evn_id = :Evn_id,
				@CmpCallCard_id = :CmpCallCard_id,
				@Person_id = :Person_id,
				@Lpu_id = :Lpu_id,
				@PersonIdentPackagePos_identDT = :PersonIdentPackagePos_identDT,
				@PersonIdentPackagePos_identDT2 = :PersonIdentPackagePos_identDT2,
				@PersonIdentState_id = :PersonIdentState_id,
				@PersonIdentPackageTool_id = :PersonIdentPackageTool_id,				
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
			select @Res as PersonIdentPackagePos_id, @Error_Code as Error_Code, @Error_Message as Error_Msg
		";
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			$this->rollbackTransaction();
			return $this->createError('','Ошибка при сохранении данных для идентификации');
		}
		if (!$this->isSuccessful($response)) {
			$this->rollbackTransaction();
			return $response;
		}

		$this->load->model('Person_model');
		$resp = $this->Person_model->updatePerson(array(
			'Person_id' => $data['Person_id'],
			'Person_IsInErz' => null,
			'pmUser_id' => $data['pmUser_id'],
		));
		if (!$this->isSuccessful($resp)) {
			$this->rollbackTransaction();
			return $resp;
		}

		$this->commitTransaction();

		return $response;
	}

	/**
	 * @param string $tmpTable
	 * @param array $data
	 */
	function insertPersonIdentPackagePosFromQuery($querySelector, $data, $isRegistry = false) {
		if ($isRegistry) {
			$this->db = $this->load->database('registry', true);
		}

		$additParams = array(
			'PersonIdentPackage_id' => !empty($data['tmpPersonIdentPackage_id'])?$data['tmpPersonIdentPackage_id']:null,
			'pmUser_id' => $data['pmUser_id'],
			'DT' => date_create()->format('Y-m-d H:i:s'),
		);

		$page_size = 1000;
		$count = $this->getFirstResultFromQuery(getCountSQLPH($querySelector), $data);

		for ($page = 0; $page < $count/$page_size; $page++) {
			$posList = $this->queryResult(getLimitSQLPH($querySelector, $page*$page_size, $page_size), $data);
			$this->insertPersonIdentPackagePosList($posList, $additParams, $isRegistry);
		}

		if ($isRegistry) {
			$this->db = $this->load->database('default', true);
		}
	}

	/**
	 * @param array $list
	 * @return array
	 * @throws Exception
	 */
	function insertPersonIdentPackagePosList($list, $additParams = array(), $isRegistry = false) {
		$insertValuesFn = function($fields, $params){
			return array_map(function($field) use($params) {
				return !empty($params[$field])?"'".str_replace("'", "''", $params[$field])."'":'null';
			}, $fields);
		};

		$configFields = array(
			'PersonIdentPackage_id' => 'bigint',
			'Evn_id' => 'bigint',
			'CmpCallCard_id' => 'bigint',
			'Person_id' => 'bigint',
			'pmUser_insID' => 'bigint',
			'pmUser_updID' => 'bigint',
			'PersonIdentPackagePos_insDT' => 'datetime',
			'PersonIdentPackagePos_updDT' => 'datetime',
			'PersonIdentPackagePos_identDT' => 'datetime',
			'PersonIdentPackagePos_identDT2' => 'datetime',
			'PersonIdentPackagePos_IsCmp' => 'bigint',
			'PersonIdentState_id' => 'bigint',
			'PersonIdentPackagePos_NumRec' => 'bigint',
			'PersonIdentPackageTool_id' => 'bigint',
		);

		$insertQuery = function($fields, $values){
			return "
				declare @Error_Code bigint = null
				declare @Error_Message varchar(4000) = ''
				set nocount on
				begin try
					insert into PersonIdentPackagePos
					({$fields})
					values
					{$values}
				end try
				begin catch
					set @Error_Code = error_number()
					set @Error_Message = error_message()
				end catch
				set nocount off
				select @Error_Code as Error_Code, @Error_Message as Error_Msg
			";
		};

		$convertParams = function($item) use($configFields, $additParams) {
			$tmp = array();
			$item = array_merge($item, $additParams);
			foreach ($configFields as $field => $type) {
				$tmp[$field] = !empty($item[$field])?$item[$field]:null;
			}
			if (!empty($item['pmUser_id'])) {
				$tmp['pmUser_insID'] = $item['pmUser_id'];
				$tmp['pmUser_updID'] = $item['pmUser_id'];
			}
			if (!empty($item['DT'])) {
				$tmp['PersonIdentPackagePos_insDT'] = $item['DT'];
				$tmp['PersonIdentPackagePos_updDT'] = $item['DT'];
			}
			return $tmp;
		};

		$execInsertQuery = function($insertArr) use($configFields, $insertQuery) {
			$fields = implode(",", array_keys($configFields));
			$values = implode(",", $insertArr);
			$resp = $this->queryResult($insertQuery($fields, $values));
			if (!is_array($resp)) {
				return $this->createError('Ошибка при заполенении временной таблицы данными из файла');
			}
			return $resp;
		};

		if ($isRegistry) {
			$this->db = $this->load->database('default', true);
		}

		$insertArr = array();
		foreach(array_map($convertParams, $list) as $item) {
			$insertArr[] = "(".implode(",", $insertValuesFn(array_keys($configFields), $item)).")";
			if (count($insertArr) == 100) {
				$resp = $execInsertQuery($insertArr);
				if (!$this->isSuccessful($resp)) {
					throw new Exception($resp[0]['Error_Msg']);
				}
				$insertArr = array();
			}
		}

		if (count($insertArr) > 0) {
			$resp = $execInsertQuery($insertArr);
			if (!$this->isSuccessful($resp)) {
				throw new Exception($resp[0]['Error_Msg']);
			}
		}

		if ($isRegistry) {
			$this->db = $this->load->database('registry', true);
		}

		return array(array('success' => true));
	}

	/**
	 * Сохранение ошибки идентификации записи из пакета
	 */
	function savePersonIdentPackagePosError($data) {
		$params = array(
			'PersonIdentPackagePosError_id' => !empty($data['PersonIdentPackagePosError_id'])?$data['PersonIdentPackagePosError_id']:null,
			'PersonIdentPackagePos_id' => $data['PersonIdentPackagePos_id'],
			'PersonIdentPackagePosErrorType_id' => !empty($data['PersonIdentPackagePosErrorType_id'])?$data['PersonIdentPackagePosErrorType_id']:null,
			'PersonIdentPackagePosError_ErrCode' => !empty($data['PersonIdentPackagePosError_ErrCode'])?$data['PersonIdentPackagePosError_ErrCode']:null,
			'PersonIdentPackagePosError_ErrDescr' => !empty($data['PersonIdentPackagePosError_ErrDescr'])?$data['PersonIdentPackagePosError_ErrDescr']:null,
			'pmUser_id' => $data['pmUser_id'],
		);
		if (empty($params['PersonIdentPackagePosError_id'])) {
			$procedure = 'p_PersonIdentPackagePosError_ins';
		} else {
			$procedure = 'p_PersonIdentPackagePosError_upd';
		}
		$query = "
			declare
				@Error_Message varchar(4000),
				@Error_Code bigint,
				@Res bigint = :PersonIdentPackagePosError_id;
			exec {$procedure}
				@PersonIdentPackagePosError_id = @Res output,
				@PersonIdentPackagePos_id = :PersonIdentPackagePos_id,
				@PersonIdentPackagePosErrorType_id = :PersonIdentPackagePosErrorType_id,
				@PersonIdentPackagePosError_ErrCode = :PersonIdentPackagePosError_ErrCode,
				@PersonIdentPackagePosError_ErrDescr = :PersonIdentPackagePosError_ErrDescr,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @Res as PersonIdentPackagePosError_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";
		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при сохранении импортируемой ошибки');
		}
		return $resp;
	}
};