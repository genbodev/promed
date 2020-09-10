<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * ServiceEFIS_model - модель для работы с сервисом ЕФИС СК-ФАРМАЦИЯ
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      ServiceEFIS
 * @access       public
 * @copyright    Copyright (c) 2017 Swan Ltd.
 * @author       Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version      16.02.2017
 *
 * @property swServiceEFIS $service
 * @property swParser $parser
 *
 */

class ServiceEFIS_model extends swModel {
	public $scheme = 'r101';

	private $_syncObjectList = array();

	private $saveObjectQueries = array();
	private $saveObjectVariables = array();

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
		$this->load->library('parser');
		$this->load->library('textlog', array('file' => 'ServiceEFIS_'.date('Y-m-d').'.log'));
		$this->load->model('ObjectSynchronLog_model');
		$this->ObjectSynchronLog_model->setServiceSysNick('EfisKZ');
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
	 * Получение синхронизованных объектов
	 */
	function getSyncObject($name, $id = null, $field = 'Object_id') {
		if (!in_array($field, array('Object_id','Object_sid'))) {
			return null;
		}

		$nick = $field;
		if (in_array($field, array('Object_sid'))) {
			$nick = 'Object_Value';
		}
		if (empty($id)) {
			$nick = 'Object_Log';
		}

		// ищем в памяти
		if (isset($this->_syncObjectList[$name]) && isset($this->_syncObjectList[$name][$nick])) {
			if (isset($this->_syncObjectList[$name][$nick][$id])) {
				return $this->_syncObjectList[$name][$nick][$id];
			} else {
				return $this->_syncObjectList[$name][$nick];
			}
		}

		// ищем в бд
		$ObjectSynchronLogData = $this->ObjectSynchronLog_model->getObjectSynchronLog($name, $id, $field);
		if (!empty($ObjectSynchronLogData)) {
			if (empty($ObjectSynchronLogData['Object_id'])) {
				$this->_syncObjectList[$name]['Object_Log'] = &$ObjectSynchronLogData;
			} else {
				$key = $ObjectSynchronLogData['Object_id'];
				$this->_syncObjectList[$name]['Object_id'][$key] = &$ObjectSynchronLogData;

				$key = $ObjectSynchronLogData['Object_Value'];
				$this->_syncObjectList[$name]['Object_Value'][$key] = &$ObjectSynchronLogData;
			}

			return $ObjectSynchronLogData;
		}

		return null;
	}

	/**
	 * Сохранение синхронизованных объектов
	 */
	function saveSyncObject($name, $id = null, $value = null, $ins = false) {
		// сохраняем в БД
		$resp = $this->ObjectSynchronLog_model->saveObjectSynchronLog($name, $id, $value, $ins);

		// сохраняем в памяти
		$ObjectSynchronLogData = array(
			'ObjectSynchronLog_id' => $resp[0]['ObjectSynchronLog_id'],
			'Object_setDT' => $resp[0]['Object_setDT'],
			'Object_Name' => $name,
			'Object_id' => $id,
			'Object_Value' => $value,
		);

		if (empty($id) && empty($value)) {
			$this->_syncObjectList[$name]['Object_Log'] = &$ObjectSynchronLogData;
		}
		if (!empty($id)) {
			$this->_syncObjectList[$name]['Object_id'][$id] = &$ObjectSynchronLogData;
		}
		if (!empty($value)) {
			$this->_syncObjectList[$name]['Object_Value'][$value] = &$ObjectSynchronLogData;
		}
		return $ObjectSynchronLogData;
	}

	/**
	 * Выполнение запросов к сервису ЕФИС и обработка ошибок, которые возвращает сервис
	 */
	function exec($method, $type = 'get', $data = null) {
		$this->load->library('swServiceEFIS', $this->config->item('EFIS'), 'service');
		$this->textlog->add("exec method: $method, type: $type, data: ".print_r($data,true));
		$result = $this->service->data($method, $type, $data/*, array(), true*/);
		$this->textlog->add("result: ".print_r($result,true));
		if (is_object($result) && !empty($result->Message)) {
			$result = array(
				'success' => false,
				'errorMsg' => 'Ошибка в работе сервиса ЕФИС СК-Фармация: '.$result->Message
			);
		}
		if (is_object($result) && !empty($result->ExceptionMessage)) {
			$result = array(
				'success' => false,
				'errorMsg' => 'Ошибка в работе сервиса ЕФИС СК-Фармация: '.$result->ExceptionMessage
			);
		}
		return $result;
	}

	/**
	 * Выполнение запросов
	 */
	function execSaveObjectQueries() {
		$success = array(array('Error_Code' => null, 'Error_Msg' => null));

		if (count($this->saveObjectQueries) == 0) {
			return $success;
		}

		$tpl = "
			{variables}{variable}
			{/variables}
			declare @Error_Code bigint
			declare @Error_Message varchar(4000)
			
			set nocount on
			begin try
			{queries}
			if @Error_Message is null
			begin
				{query}
			end
			{/queries}
			end try
			begin catch
				set @Error_Code = error_number()
				set @Error_Message = error_message()
			end catch
			set nocount off
			select @Error_Code as Error_Code, @Error_Message as Error_Msg
		";

		$query_pages = array_chunk($this->saveObjectQueries, 50);
		$this->saveObjectQueries = array();
		$queries_formation = function($q){return array('query' => $q);};

		$declare_variables = function($v){return "declare $v";};
		$declare_formation = function($v)use($declare_variables){return array('variable' => $declare_variables($v));};
		$variables = array_map($declare_formation, $this->saveObjectVariables);

		foreach($query_pages as $queries) {
			$main_query = $this->parser->parse_string($tpl, array(
				'queries' => array_map($queries_formation, $queries),
				'variables' => $variables
			), true);

			//echo getDebugSQL($main_query, array());exit;
			$resp = $this->queryResult($main_query);
			if (!$this->isSuccessful($resp)) {
				return $resp;
			}
		}

		return $success;
	}

	/**
	 * Добавление переменных для запросов
	 */
	function addSaveObjectVariables($variables) {
		if (!is_array($variables)) $variables = array($variables);
		$this->saveObjectVariables = array_unique(array_merge($this->saveObjectVariables, $variables));
	}

	/**
	 * Добавление запроса по шаблону
	 */
	function addSaveObjectQueryWithTpl($tplQuery, $tplParams = array(), $batchSize = null) {
		$this->saveObjectQueries[] = $this->parser->parse_string($tplQuery, $tplParams, true);
		if (!empty($batchSize) && count($this->saveObjectQueries) >= $batchSize) {
			$this->execSaveObjectQueries();
		}
	}

	/**
	 * Добавлнение запроса для сохранение данных объекта
	 *
	 * @param array $objectData
	 * @param string $objectName
	 * @return array|false
	 */
	function addSaveObjectQuery($objectData, $objectName, $keyField = null) {
		$scheme = $this->scheme;
		$tmp = explode(".", $objectName);
		if (count($tmp) == 2) {
			$scheme = $tmp[0];
			$objectName = $tmp[1];
		}

		$idField = $objectName.'_id';
		if (empty($keyField)) {
			$keyField = $objectName.'_guid';
		}

		$tpl = "		
			select top 1 @id = {idField} from {scheme}.{objectName} where {keyField} = '{keyValue}'
			if @id is null
				insert into {scheme}.{objectName}
				({insert_fields})
				values
				({insert_values})
			else
				update {scheme}.{objectName} with(rowlock)
				set {update_params}
				where {idField} = @id
		";

		$prepare_fields = function($v) {return "[$v]";};
		$prepare_values = function($v){return empty($v)?"null":"'" . preg_replace("/'/u", "''", $v) . "'";};
		$prepare_update_params = function($f, $v) {return "$f=$v";};

		$fields = array_map($prepare_fields, array_keys($objectData));
		$values = array_map($prepare_values, array_values($objectData));
		$update_params = array_map($prepare_update_params, $fields, $values);

		$params = array(
			'idField' => $idField,
			'scheme' => $scheme,
			'objectName' => $objectName,
			'keyField' => $keyField,
			'keyValue' => $objectData[$keyField],
			'insert_fields' => implode(",", $fields),
			'insert_values' => implode(",", $values),
			'update_params' => implode(",", $update_params)
		);

		$this->addSaveObjectVariables("@id bigint");

		$this->addSaveObjectQueryWithTpl($tpl, $params, 500);

		return array(array('Error_Code' => null, 'Error_Msg' => null));
	}

	/**
	 * Добавление запроса для сохранения полученных данных типа медикаментов
	 */
	function addSaveDrugTypeQuery($DrugType) {
		if (is_object($DrugType)) {
			$DrugType = objectToArray($DrugType);
		}
		$params = array(
			'KM_DrugType_guid' => $DrugType['id'],
			'MnnName' => $DrugType['mnnName'],
			'DateCreate' => date_create($DrugType['dateCreate'])->format('Y-m-d H:i:s'),
			'DrugForm' => $DrugType['drugForm'],
			'Status' => $DrugType['status']
		);
		return $this->addSaveObjectQuery($params, 'KM_DrugType');
	}

	/**
	 * Получение списка типов медикаментов из сервиса
	 */
	function importDrugTypeList($data) {
		$list = $this->exec('/goods_types/');
		if (is_array($list) && !empty($list['errorMsg'])) {
			return $this->createError('', $list['errorMsg']);
		}
		if (empty($list)) {
			return array(array('success' => true));
		}
		if (!empty($_REQUEST['getDebug'])) {
			echo '<pre>';print_r($list);exit;
		}

		foreach($list as $item) {
			$resp = $this->addSaveDrugTypeQuery($item);
			if (!$this->isSuccessful($resp)) {
				return $resp;
			}
		}
		$resp = $this->execSaveObjectQueries();
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при сохранении типов медикаментов');
		}
		if (!$this->isSuccessful($resp)) {
			return $resp;
		}

		return array(array('success' => true));
	}

	/**
	 * Добавление запроса для сохранения полученных данных производителей
	 */
	function addSaveDrugManufacturerQuery($DrugManufacturer) {
		if (is_object($DrugManufacturer)) {
			$DrugManufacturer = objectToArray($DrugManufacturer);
		}
		$params = array(
			'KM_DrugManufacturer_guid' => $DrugManufacturer['id'],
			'KM_DrugManufacturer_Name' => $DrugManufacturer['name'],
			'KM_DrugManufacturer_Address' => $DrugManufacturer['address'],
			'Country' => $DrugManufacturer['country'],
			'CountryCode' => $DrugManufacturer['countryCode'],
			'Description' => (!empty($DrugManufacturer['description']) ? $DrugManufacturer['description'] : null),
			'Is_local' => $DrugManufacturer['local'],
			'Author' => $DrugManufacturer['author'],
			'DateCreate' => date_create($DrugManufacturer['dateCreate'])->format('Y-m-d H:i:s'),
			'Status' => $DrugManufacturer['status']
		);
		return $this->addSaveObjectQuery($params, 'KM_DrugManufacturer');
	}

	/**
	 * Получение списка производителей из сервиса
	 */
	function importDrugManufacturerList($data) {
		$list = $this->exec('/producers/');
		if (is_array($list) && !empty($list['errorMsg'])) {
			return $this->createError('', $list['errorMsg']);
		}
		if (empty($list)) {
			return array(array('success' => true));
		}
		if (!empty($_REQUEST['getDebug'])) {
			echo '<pre>';print_r($list);exit;
		}

		foreach($list as $item) {
			$resp = $this->addSaveDrugManufacturerQuery($item);
			if (!$this->isSuccessful($resp)) {
				return $resp;
			}
		}
		$resp = $this->execSaveObjectQueries();
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при сохранении производителей');
		}
		if (!$this->isSuccessful($resp)) {
			return $resp;
		}

		return array(array('success' => true));
	}

	/**
	 * Добавление запроса для сохранения полученных данных медикаментов
	 */
	function addSaveDrugQuery($Drug) {
		if (is_object($Drug)) {
			$Drug = objectToArray($Drug);
		}
		$params = array(
			'KM_Drug_guid' => $Drug['id'],
			'MnnName' => $Drug['mnnName'],
			'PharmName' => $Drug['pharmName'],
			'DrugForm' => $Drug['drugForm'],
			'GoodsTypeId' => $Drug['goodsTypeId'],
			'AvgVolId' => $Drug['avgVolId'],
			'Author' => $Drug['author'],
			'DateCreate' => date_create($Drug['dateCreate'])->format('Y-m-d H:i:s'),
			'DateUpdate' => date_create($Drug['dateUpdate'])->format('Y-m-d H:i:s'),
			'Status' => $Drug['status']
		);
		return $this->addSaveObjectQuery($params, 'KM_Drug');
	}

	/**
	 * Получение списка медикаметнов из сервиса
	 */
	function importDrugList($data) {
		$lastDateUpdate = $this->getFirstResultFromQuery("
			select top 1 
			convert(varchar(10), max(KMD.DateUpdate), 104) as DateUpdate
			from {$this->scheme}.KM_Drug KMD with(nolock)
		", array(), true);
		if ($lastDateUpdate === false) {
			return $this->createError('','Ошибка при получении даты последнего обновления списка медикаментов');
		}

		if (empty($lastDateUpdate)) {
			$list = $this->exec('/goods/');
		} else {
			$list = $this->exec('/goods/greaterthan/dateUpdate/'.$lastDateUpdate);
		}
		if (is_array($list) && !empty($list['errorMsg'])) {
			return $this->createError('', $list['errorMsg']);
		}
		if (empty($list)) {
			return array(array('success' => true));
		}
		if (!empty($_REQUEST['getDebug'])) {
			echo '<pre>';print_r($list);exit;
		}

		foreach($list as $item) {
			$resp = $this->addSaveDrugQuery($item);
			if (!$this->isSuccessful($resp)) {
				return $resp;
			}
		}
		$resp = $this->execSaveObjectQueries();
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при сохранении медикаментов');
		}
		if (!$this->isSuccessful($resp)) {
			return $resp;
		}

		return array(array('success' => true));
	}

	/**
	 * Добавление запроса для сохранения полученных прайсов
	 */
	function addSaveCommercialOfferQuery($CommercialOffer, $Org_id) {
		if (is_object($CommercialOffer)) {
			$CommercialOffer = objectToArray($CommercialOffer);
		}
		$params = array(
			'KM_CommercialOffer_guid' => $CommercialOffer['id'],
			'Name' => $CommercialOffer['name'],
			'Org_id' => $Org_id,
			'CommercialOffer_Comment' => (!empty($CommercialOffer['description']) ? $CommercialOffer['description'] : null),
			'CommercialOffer_begDT' => date_create($CommercialOffer['registrationDate'])->format('Y-m-d H:i:s'),
			'CommercialOffer_endDT' => null,
			'Status' => $CommercialOffer['status']
		);
		return $this->addSaveObjectQuery($params, 'KM_CommercialOffer');
	}

	/**
	 * Добавление запросов для создания коммерческого предложения по полученным прайсам
	 */
	function addSyncCommercialOfferQuery($data) {
		$list = $this->queryResult("
			select
				CO.CommercialOffer_id,
				CO.Org_did,
				KMCO.KM_CommercialOffer_guid as CommercialOffer_guid,
				KMCO.Name as CommercialOffer_Name,
				KMCO.Org_id,
				KMCO.CommercialOffer_Comment,
				convert(varchar(19), KMCO.CommercialOffer_begDT, 120) as CommercialOffer_begDT,
				convert(varchar(19), KMCO.CommercialOffer_endDT, 120) as CommercialOffer_endDT,
				KMCO.Status as CommercialOffer_Status
			from
				{$this->scheme}.KM_CommercialOffer KMCO with(nolock)
				left join dbo.CommercialOffer CO with(nolock) on CO.CommercialOffer_guid = KMCO.KM_CommercialOffer_guid
		");
		if (!is_array($list)) {
			return $this->createError('','Ошибка при получении данных для обновлнения коммерческих предложений');
		}

		$tpl = "
			set @id = {CommercialOffer_id}
			exec p_CommercialOffer_{action}
				@CommercialOffer_id = @id output,
				@CommercialOffer_guid = {CommercialOffer_guid},
				@CommercialOffer_Name = {CommercialOffer_Name},
				@CommercialOffer_Comment = {CommercialOffer_Comment},
				@CommercialOffer_begDT = {CommercialOffer_begDT},
				@CommercialOffer_endDT = {CommercialOffer_endDT},
				@CommercialOffer_Status = {CommercialOffer_Status},
				@Org_id = {Org_id},
				@Org_did = {Org_did},
				@pmUser_id = {pmUser_id},
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
		";

		$prepare_params = function($v){return empty($v)?"null":"'" . preg_replace("/'/u", "''", $v) . "'";};

		foreach($list as $item) {
			$params = array(
				'action' => empty($item['CommercialOffer_id'])?'ins':'upd',
				'pmUser_id' => $data['pmUser_id']
			);
			$params = array_merge($params, array_map($prepare_params, $item));

			$this->addSaveObjectQueryWithTpl($tpl, $params);
		}

		return array(array('success' => true));
	}

	/**
	 * Получение активных прайсов из сервиса
	 */
	function importCommercialOfferList($data) {
		$Org_id = $this->getFirstResultFromQuery("
			select top 1 Org_id from v_Org with(nolock) where Org_Nick like 'СК Фармация'
		", array(), true);
		if ($Org_id === false) {
			return $this->createError('','Ошибка при поиске организации СК Фармация');
		}
		if (empty($Org_id)) {
			return $this->createError('','Поставщик не найден. Необходимо добаваить организацию СК Фармация');
		}

		$list = $this->exec('/prices/equals/status/1');
		if (is_array($list) && !empty($list['errorMsg'])) {
			return $this->createError('', $list['errorMsg']);
		}
		if (empty($list)) {
			return array(array('success' => true));
		}
		if (!empty($_REQUEST['getDebug'])) {
			echo '<pre>';print_r($list);exit;
		}

		$activeCommercialOffer = null;
		foreach($list as $item) {
			$item = objectToArray($item);
			if ($item['status'] == 1) {
				$activeCommercialOffer = $item;
			}
			$resp = $this->addSaveCommercialOfferQuery($item, $Org_id);
			if (!$this->isSuccessful($resp)) {
				return $resp;
			}
		}

		if (!empty($activeCommercialOffer)) {
			$this->addSaveObjectQueryWithTpl("
				update {scheme}.KM_CommercialOffer with(rowlock)
				set [Status] = 0, [CommercialOffer_endDT] = '{endDT}'
				where [Status] = 1 and [KM_CommercialOffer_guid] <> '{activeGuid}'
			", array(
				'scheme' => $this->scheme,
				'activeGuid' => $activeCommercialOffer['id'],
				'endDT' => date_create($activeCommercialOffer['registrationDate'])->format('Y-m-d H:i:s'),
			));
		}

		$resp = $this->execSaveObjectQueries();
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при сохранении прайсов');
		}
		if (!$this->isSuccessful($resp)) {
			return $resp;
		}

		$resp = $this->addSyncCommercialOfferQuery($data);
		if (!$this->isSuccessful($resp)) {
			return $resp;
		}

		$resp = $this->execSaveObjectQueries();
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при сохранении прайсов');
		}
		if (!$this->isSuccessful($resp)) {
			return $resp;
		}

		return array(array('success' => true));
	}

	/**
	 * Добавление запроса для сохранения полученных медикаментов прайса
	 */
	function addSaveCommecialOfferDrugQuery($item, $CommercialOffer_guid = null) {
		if (is_object($item)) {
			$item = objectToArray($item);
		}
		if (empty($CommercialOffer_guid)) {
			$CommercialOffer_guid = $this->getFirstResultFromQuery("
				select top 1 KM_CommercialOffer_guid
				from {$this->scheme}.KM_CommercialOffer with(nolock) 
				where Name like :priceId
			", $item, true);
			if ($CommercialOffer_guid === false) {
				return $this->createError('','Ошибка при получении идентификатора прайса');
			}
		}

		$params = array(
			'Price_detail' => $item['priceDetail'],
			'Prev_price_detail' => null,
			'KM_CommercialOffer_guid' => $CommercialOffer_guid,
			'Drug_id' => null,
			'DrugPrepFas_id' => null,
			'KM_CommercialOfferDrug_Price' => $item['cost'],
			'Unit_Name' => $item['unitName'],
			'Goods_guid' => !empty($item['goodsId'])?$item['goodsId']:null,
			'Mnn_name' => $item['mnnName'],
			'Pharm_name' => $item['pharmName'],
			'Drug_form' => $item['drugForm'],
			'Package1' => $item['package1'],
			'Expire' => $item['expire'],
			'Prod_name' => $item['prodName'],
			'Prod_country' => $item['prodCountry'],
			'RegCertName' => !empty($item['regCertName'])?$item['regCertName']:null
		);

		$query = "
			select top 1
				D.Drug_id,
				D.DrugPrepFas_id
			from rls.v_REGCERT R with(nolock)
			inner join rls.v_PREP P with(nolock) on P.REGCERTID = R.REGCERT_ID
				and R.REGNUM = :RegCertName
			inner join rls.v_Drug D with(nolock) on D.DrugPrep_id = p.Prep_id
				and D.Drug_Fas = :Package1
		";
		$Drug = $this->getFirstRowFromQuery($query, $params, true);
		if ($Drug === false) {
			return $this->createError('','Ошибка при получении данных о медикаменте');
		}
		if (is_array($Drug)) {
			$params = array_merge($params, $Drug);
		}

		return $this->addSaveObjectQuery($params, 'KM_CommercialOfferDrug', 'Price_detail');
	}

	/**
	 * Добавление запросов для создания медикментов в коммерческом предложении по полученным медикаментам прайса
	 */
	function addSyncCommercialOfferDrugQuery($data) {
		$list = $this->queryResult("
			select
				COD.CommercialOfferDrug_id,
				CO.CommercialOffer_id,
				CO.CommercialOffer_guid,
				KMCOD.Drug_id,
				KMCOD.DrugPrepFas_id,
				KMCOD.KM_CommercialOfferDrug_Price as CommercialOfferDrug_Price,
				KMCOD.Unit_Name as CommercialOfferDrug_UnitName,
				KMCOD.Price_detail as CommercialOfferDrug_PriceDetail,
				KMCOD.Prev_price_detail as CommercialOfferDrug_PrevPriceDetail,
				KMCOD.Goods_guid,
				KMCOD.Mnn_name as CommercialOfferDrug_MnnName,
				KMCOD.Pharm_name as CommercialOfferDrug_PharmName,
				KMCOD.Drug_form as CommercialOfferDrug_Form,
				KMCOD.Package1 as CommercialOfferDrug_Package,
				KMCOD.Expire as CommercialOfferDrug_Expire,
				KMCOD.Prod_name as CommercialOfferDrug_ProdName,
				KMCOD.Prod_country as CommercialOfferDrug_ProdCountry,
				KMCOD.RegCertName as CommercialOfferDrug_RegCertName
			from 
				{$this->scheme}.KM_CommercialOfferDrug KMCOD with(nolock)
				inner join v_CommercialOffer CO with(nolock) on CO.CommercialOffer_guid = KMCOD.KM_CommercialOffer_guid
				left join v_CommercialOfferDrug COD with(nolock) on COD.CommercialOfferDrug_PriceDetail = KMCOD.Price_detail
			where not exists(
				select * from v_CommercialOfferDrug with(nolock)
				where CommercialOffer_guid = KMCOD.KM_CommercialOffer_guid
				and (Drug_id = KMCOD.Drug_id or CommercialOfferDrug_PriceDetail = KMCOD.Price_detail) 
			)
		");
		if (!is_array($list)) {
			return $this->createError('','Ошибка при получении данных для обновлнения медикаментов в коммерческом предложении');
		}

		$this->addSaveObjectVariables('@id bigint');

		//Если медикамента с указанным идентификатором/кодом СКП ранее не было в ком.предложении, то он добавляется в ком.предложение
		$tpl = "
			set @id = {CommercialOfferDrug_id}
			exec p_CommercialOfferDrug_{action}
				@CommercialOfferDrug_id = @id output,
				@CommercialOffer_id = {CommercialOffer_id},
				@CommercialOffer_guid = {CommercialOffer_guid},
				@Drug_id = {Drug_id},
				@DrugPrepFas_id = {DrugPrepFas_id},
				@CommercialOfferDrug_Price = {CommercialOfferDrug_Price},
				@CommercialOfferDrug_UnitName = {CommercialOfferDrug_UnitName},
				@CommercialOfferDrug_PriceDetail = {CommercialOfferDrug_PriceDetail},
				@CommercialOfferDrug_PrevPriceDetail = {CommercialOfferDrug_PrevPriceDetail},
				@Goods_guid = {Goods_guid},
				@CommercialOfferDrug_MnnName = {CommercialOfferDrug_MnnName},
				@CommercialOfferDrug_PharmName = {CommercialOfferDrug_PharmName},
				@CommercialOfferDrug_Form = {CommercialOfferDrug_Form},
				@CommercialOfferDrug_Package = {CommercialOfferDrug_Package},
				@CommercialOfferDrug_Expire = {CommercialOfferDrug_Expire},
				@CommercialOfferDrug_ProdName = {CommercialOfferDrug_ProdName},
				@CommercialOfferDrug_ProdCountry = {CommercialOfferDrug_ProdCountry},
				@CommercialOfferDrug_RegCertName = {CommercialOfferDrug_RegCertName},
				@pmUser_id = {pmUser_id},
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
		";

		$prepare_params = function($v){return empty($v)?"null":"'" . preg_replace("/'/u", "''", $v) . "'";};

		foreach($list as $item) {
			$params = array(
				'action' => empty($item['CommercialOfferDrug_id'])?'ins':'upd',
				'pmUser_id' => $data['pmUser_id']
			);
			$params = array_merge($params, array_map($prepare_params, $item));

			$this->addSaveObjectQueryWithTpl($tpl, $params);
		}

		return array(array('success' => true));
	}

	/**
	 * Получение списка медикаментов по текущему активному прайсу
	 */
	function importCommercialOfferDrugList($data) {
		$ActiveCommercialOffer = $this->getFirstRowFromQuery("
			select top 1 KM_CommercialOffer_guid as guid, Name 
			from {$this->scheme}.KM_CommercialOffer with(nolock) 
			where Status = 1
		", array(), true);
		if ($ActiveCommercialOffer === false) {
			return $this->createError('Ошибка при получении активного прайса');
		}
		if (empty($ActiveCommercialOffer)) {
			return $this->createError('Не найден активный прайс');
		}

		$priceId = rawurlencode($ActiveCommercialOffer['Name']);
		$list = $this->exec("/price_details/equals/priceId/{$priceId}");
		if (is_array($list) && !empty($list['errorMsg'])) {
			return $this->createError('', $list['errorMsg']);
		}
		if (empty($list)) {
			return array(array('success' => true));
		}
		if (!empty($_REQUEST['getDebug'])) {
			echo '<pre>';print_r($list);exit;
		}

		foreach($list as $item) {
			$resp = $this->addSaveCommecialOfferDrugQuery($item, $ActiveCommercialOffer['guid']);
			if (!$this->isSuccessful($resp)) {
				return $resp;
			}
		}

		$resp = $this->execSaveObjectQueries();
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при сохранении медикаментов действующего прайса');
		}
		if (!$this->isSuccessful($resp)) {
			return $resp;
		}

		$resp = $this->addSyncCommercialOfferDrugQuery($data);
		if (!$this->isSuccessful($resp)) {
			return $resp;
		}

		$resp = $this->execSaveObjetcQueries();
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при сохранении медикаментов действующего прайса');
		}
		if (!$this->isSuccessful($resp)) {
			return $resp;
		}

		return array(array('success' => true));
	}

	/**
	 * Добавление запросов для сохранение полученных серий медикаментов
	 */
	function addSavePrepSeriesQuery($item) {
		if (is_object($item)) {
			$item = objectToArray($item);
		}

		$query = "
			select top 1 P.Prep_id
			from v_CommercialOfferDrug COD with(nolock)
			left join rls.v_REGCERT R with(nolock) on R.REGNUM = COD.CommercialOfferDrug_RegCertName
			left join rls.v_PREP P with(nolock) on P.REGCERTID = R.REGCERT_ID
			where COD.Goods_guid = :goodsId
		";
		$Prep_id = $this->getFirstResultFromQuery($query, $item, true);
		if ($Prep_id === false) {
			return $this->createError('','Ошибка при получении препарата для серии');
		}
		if (empty($Prep_id)) {
			return $this->createError(4,'Не найден препарат для серии');
		}

		$params = array(
			'Prep_id' => $Prep_id,
			'PrepSeries_Ser' => $item['seriesNumber'],
			'PrepSeries_GodnDate' => date_create($item['seriesDate'])->format('Y-m-d H:i:s')
		);

		return $this->addSaveObjectQuery($params, 'rls.PrepSeries', 'Prep_id');
	}

	/**
	 * Получение списка серий медикаментов
	 */
	function importPrepSeriesList($data) {
		$lastSync = $this->getSyncObject('PrepSeriesList');	//Для получения даты последнего импорта

		if (empty($lastSync)) {
			$list = $this->exec("/inventory/");
		} else {
			$list = $this->exec("/inventory/greaterthan/dateCreate/".$lastSync['Object_setDT']->format('d.m.Y'));
		}
		if (is_array($list) && !empty($list['errorMsg'])) {
			return $this->createError('', $list['errorMsg']);
		}
		if (empty($list)) {
			return array(array('success' => true));
		}
		if (!empty($_REQUEST['getDebug'])) {
			echo '<pre>';print_r($list);exit;
		}

		foreach($list as $item) {
			$resp = $this->addSavePrepSeriesQuery($item);
			if (!$this->isSuccessful($resp)) {
				if ($resp[0]['Error_Code'] == 4) {
					continue;
				} else {
					return $resp;
				}
			}
		}

		$resp = $this->execSaveObjectQueries();
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при сохранении медикаментов действующего прайса');
		}
		if (!$this->isSuccessful($resp)) {
			return $resp;
		}
		$this->saveSyncObject('PrepSeriesList');	//Обновление даты последнего импорта

		return array(array('success' => true));
	}

	/**
	 * Разрыв соединения c клиентом после запуска импорта
	 */
	function sendImportResponse() {
		ignore_user_abort(true);
		ob_start();
		echo json_encode(array("success" => "true"));

		$size = ob_get_length();

		header("Content-Length: $size");
		header("Content-Encoding: none");
		header("Connection: close");

		ob_end_flush();
		ob_flush();
		flush();

		if (session_id()) session_write_close();
	}

	/**
	 * Запуск импорта данных из ЕФИС
	 */
	function runImport($data) {
		set_time_limit(0);
		ini_set("max_execution_time", "0");

		$this->load->helper('ServiceListLog');

		$pmUser_id = !empty($data['pmUser_id'])?$data['pmUser_id']:1;

		$log = new ServiceListLog(9, $pmUser_id);

		$resp = $log->start();
		if (!$this->isSuccessful($resp)) {
			return $resp;
		}

		if (empty($_REQUEST['getDebug'])) {
			$this->sendImportResponse();
		}

		$this->load->helper('ShutdownErrorHandler');
		registerShutdownErrorHandler(array($this, 'shutdownErrorHandler'), function($error) use($log) {
			$log->add(false, array("Импорт данных из сервиса ЕФИС СК-Фармация завершён с ошибкой:", $error));
			$log->finish(false);
		});

		try {
			set_error_handler(array($this, 'exceptionErrorHandler'));

			$objects = array();
			$objectsDescr = array();
			$allObjectsDescr = array(
				'DrugType' => 'Типы медикаментов',
				'DrugManufacturer' => 'Производители',
				'Drug' => 'Медикаменты',
				'PrepSeries' => 'Серии медикаментов',
				'CommercialOffer' => 'Прайс',
				'CommercialOfferDrug' => 'Медикаменты прайса',
			);

			if (is_string($data['objects'])) {
				$objects = explode("|", $data['objects']);
			}
			if (in_array('all', $objects)) {
				$objectsDescr = $allObjectsDescr;
			} else {
				foreach($objects as $object) {
					if (isset($allObjectsDescr[$object])) {
						$objectsDescr[$object] = $allObjectsDescr[$object];
					}
				}
			}
			if (count($objectsDescr) == 0) {
				throw new Exception('Отсутсвуют объекты для импорта');
			}

			$log->add(true, "Запускается импорт данных из сервиса ЕФИС СК-Фармация: ".implode(", ", array_values($objectsDescr)));

			foreach($objectsDescr as $object => $descr) {
				$log->add(true, "Импорт данных \"$descr\"");

				$method = "import{$object}List";
				$params = array('pmUser_id' => $data['pmUser_id']);
				$resp = $this->$method($params);

				if (!$this->isSuccessful($resp)) {
					$log->add(false, array("Ошибка импорта \"$descr\":", $resp[0]['Error_Msg']));
				}
			}

			$log->add(true, "Импорт данных из сервиса ЕФИС СК-Фармация завершён успешно");
			$log->finish(true);
		} catch(Exception $e) {
			restore_exception_handler();

			$code = $e->getCode();
			$error = $e->getMessage();

			$log->add(false, array("Импорт данных из сервиса ЕФИС СК-Фармация завершён с ошибкой:", $error));
			$log->finish(false);

			$response = $this->createError($code, $error);
			$response[0]['ServiceListLog_id'] = $log->getId();

			return $response;
		}

		return array(array('success' => true, 'ServiceListLog_id' => $log->getId()));
	}

	/**
	*	Запуск импорта договоров / доп соглашений L17-0599-009-1
	*/
	function importWhsDocumentUc($data)
	{
		//Если импорт дополнительного соглашения, то сначала найдем договор.
		$WhsDocumentUc_id = null;
		if($data['import_Type'] == 2)
		{
			$Doc_Exists = false;
			$params_get_WhsDocumentUc = array(
				'WhsDocumentUc_Num' => $data['WhsDocumentUc_Num']
			);

			$query_get_WhsDocumentUc = "
				select top 1 WhsDocumentUc_id
				from v_WhsDocumentUc (nolock)
				where WhsDocumentUc_Num = :WhsDocumentUc_Num
			";
			$result_get_WhsDocumentUc = $this->db->query($query_get_WhsDocumentUc,$params_get_WhsDocumentUc);
			if(is_object($result_get_WhsDocumentUc))
			{
				$result_get_WhsDocumentUc = $result_get_WhsDocumentUc->result('array');
				if(is_array($result_get_WhsDocumentUc) && count($result_get_WhsDocumentUc)>0)
				{
					$Doc_Exists = true;
					$WhsDocumentUc_id = $result_get_WhsDocumentUc[0]['WhsDocumentUc_id'];
				}
			}
			if(!$Doc_Exists ){
				$result = array(
					'success' => false,
					'errorMsg' => 'Отсутсвует договор с таким номером. Добавление дополнительного соглашения невозможно.'
				);
				return $result;
			}
			//Если договор есть, то проверим, нет ли уже в базе доп соглашения
			$query_check_Additional = "
				select WhsDocumentSupply_id
				from v_WhsDocumentSupply (nolock)
				where WhsDocumentUc_Num = :WhsDocumentUc_Num
				and WhsDocumentUc_pid = :WhsDocumentUc_pid
			";
			$result_check_Additional = $this->db->query($query_check_Additional, array(
				'WhsDocumentUc_Num' => $data['suppl_agreement'],
				'WhsDocumentUc_pid'	=> $WhsDocumentUc_id
			));
			if(is_object($result_check_Additional)) {
				$result_check_Additional = $result_check_Additional->result('array');
				if(is_array($result_check_Additional) && count($result_check_Additional) > 0)
				{
					$result = array(
						'success' => false,
						'errorMsg' => 'Дополнительное соглашение с указанным номером уже сущетвует для данного договора.'
					);
					return $result;
				}
			}
		}

		$res_whsuc = $this->exec('/contracts/number/'.$data['WhsDocumentUc_Num'].'/'.(($data['import_Type']==1)?$data['WhsDocumentUc_Num']:$data['suppl_agreement']), $type = 'get', $d = null);
		foreach($res_whsuc as $res_whsuc_item){
			$vIntegrContracts = objectToArray($res_whsuc_item);
			//Получим Org_aid:
			$Org_sid = -1;
			$params_get_Orgaid = array(
				'Org_OGRN'	=> $vIntegrContracts['contSuppBin']
			);
			$query_get_Orgaid = "
				select top 1 Org_id as Org_sid
				from v_Org (nolock)
				where Org_OGRN = :Org_OGRN
			";
			$result_get_Orgaid = $this->db->query($query_get_Orgaid, $params_get_Orgaid);
			if(is_object($result_get_Orgaid)){
				$result_get_Orgaid = $result_get_Orgaid->result('array');
				if(is_array($result_get_Orgaid) && count($result_get_Orgaid) > 0)
					$Org_sid = $result_get_Orgaid[0]['Org_sid'];
				else
				{
					$result = array(
						'success' => false,
						'errorMsg' => 'Отсутсвует организация с ОГРН = '.$vIntegrContracts['contSuppBin']
					);
					return $result;
				}
			}
			else
			{
				$result = array(
					'success' => false,
					'errorMsg' => 'Отсутсвует организация с ОГРН = '.$vIntegrContracts['contSuppBin']
				);
				return $result;
			}

			//Проверим наличие коммерческого приложения в БД с CommercialOffer_Name = priceId
			$CommercialOffer_id = -1;
			$query_getCommercialOffer = "
				select CommercialOffer_id
				from v_CommercialOffer
				where CommercialOffer_Name = :CommercialOffer_Name
			";
			$result_getCommercialOffer = $this->db->query($query_getCommercialOffer, array('CommercialOffer_Name' => $vIntegrContracts['priceId']));
			if(is_object($result_getCommercialOffer)){
				$result_getCommercialOffer = $result_getCommercialOffer->result('array');
				if(is_array($result_getCommercialOffer) && count($result_getCommercialOffer) > 0)
					$CommercialOffer_id = $result_getCommercialOffer[0]['CommercialOffer_id'];
			}
			if($CommercialOffer_id == -1)
			{
				$result = array(
					'success' => false,
					'errorMsg' => 'Отсутсвует ссылка на прайс-лист "'.$vIntegrContracts['priceId'].'"'.'. Сохранение невозможно'
				);
				return $result;
			}

			//Проверим, есть ли FinanceSource с FinanceSource_Name = budgetProgName. Если нет - то добавим
			$query_check_FinanceSource = "
				select FinanceSource_id
				from v_FinanceSource (nolock)
				where FinanceSource_Name = :FinanceSource_Name
			";
			$FinanceSource_id = -1;
			$result_check_FinanceSource = $this->db->query($query_check_FinanceSource, array('FinanceSource_Name' => $vIntegrContracts['budgetProgName']));
			if(is_object($result_check_FinanceSource))
			{
				$result_check_FinanceSource = $result_check_FinanceSource->result('array');
				if(is_array($result_check_FinanceSource) && count($result_check_FinanceSource) > 0)
					$FinanceSource_id = $result_check_FinanceSource[0]['FinanceSource_id'];
			}
			if($FinanceSource_id == -1)
			{
				$params_add_FinanceSource = array(
					'FinanceSource_Name'	=> $vIntegrContracts['budgetProgName'],
					'pmUser_id'				=> $data['pmUser_id']
				);
				$query_add_FinanceSource = "
					declare
						@getDT datetime = dbo.tzGetDate(),
						@ErrCode int,
						@ErrMessage varchar(4000),
						@Res bigint;
					set @Res = null;
					exec dbo.p_FinanceSource_ins
						@FinanceSource_id = @Res output,
						@FinanceSource_Name = :FinanceSource_Name,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
					select @Res as FinanceSource_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";
				$result_add_FinanceSource = $this->db->query($query_add_FinanceSource, $params_add_FinanceSource);
				if(is_object($result_add_FinanceSource))
				{
					$result_add_FinanceSource = $result_add_FinanceSource->result('array');
					$FinanceSource_id = $result_add_FinanceSource[0]['FinanceSource_id'];
				}
			}




			//Добавляем данные в WhsDocumentUc. Используем сразу p_WhsDocumentSupply_ins, т.к. в ней есть и создание WhsDocumentUc
			$additionDate	= new DateTime($vIntegrContracts['additionDate']);
			$contractDate	= new DateTime($vIntegrContracts['contractDate']);
			$dateUpdate 	= new DateTime($vIntegrContracts['dateUpdate']);

			$params_add_WhsDocumentSupply = array(
				'WhsDocumentUc_pid' 		=> $WhsDocumentUc_id,
				'WhsDocumentUc_Num'			=> ($data['import_Type'] == 1)?$vIntegrContracts['contractNumber']:$vIntegrContracts['additionNumber'],//$vIntegrContracts['contractNumber'],
				'WhsDocumentUc_Name'		=> ($data['import_Type'] == 1)?$vIntegrContracts['contractNumber']:$vIntegrContracts['additionNumber'],
				'WhsDocumentType_id'		=> ($data['import_Type'] == 1)?3:14,
				'WhsDocumentUc_Date'		=> ($data['import_Type'] == 1)?$contractDate->format('Y-m-d'):$additionDate->format('Y-m-d'), //todo или additionDate
				'WhsDocumentSupplyType_id'	=> 2,
				'WhsDocumentStatusType_id'	=> 2,
				'DrugFinance_id'			=> null,//32,//Республиканский //null,
				'Org_aid'					=> $data['session']['org_id'],
				'Org_sid'					=> $Org_sid,
				'Org_cid'					=> $data['session']['org_id'],
				'Org_pid'					=> $data['session']['org_id'],
				'Org_rid'					=> $data['session']['org_id'],
				'FinanceSource_id'			=> $FinanceSource_id,
				'WhsDocumentCostItemType'	=> 15,
				'WhsDocumentPurchType_id'	=> 11,
				'pmUser_id'					=> $data['pmUser_id']
			);
			$query_add_WhsDocumentSupply = "
				declare
					@getDT datetime = dbo.tzGetDate(),
					@ErrCode int,
					@ErrMessage varchar(4000),
					@Res bigint;
				set @Res = null;
				exec dbo.p_WhsDocumentSupply_ins
					@WhsDocumentSupply_id = @Res output,
					@WhsDocumentUc_id = :WhsDocumentUc_pid,
					@WhsDocumentUc_Num = :WhsDocumentUc_Num,
					@WhsDocumentUc_Name = :WhsDocumentUc_Name,
					@WhsDocumentType_id = :WhsDocumentType_id,
					@WhsDocumentUc_Date = :WhsDocumentUc_Date,
					@WhsDocumentSupplyType_id = :WhsDocumentSupplyType_id,
					@WhsDocumentStatusType_id = :WhsDocumentStatusType_id,
					@DrugFinance_id = :DrugFinance_id,
					@WhsDocumentCostItemType = :WhsDocumentCostItemType,
					@Org_aid = :Org_aid,
					@Org_sid = :Org_sid,
					@Org_cid = :Org_cid,
					@Org_pid = :Org_pid,
					@Org_rid = :Org_rid,
					@FinanceSource_id = :FinanceSource_id,
					@WhsDocumentPurchType_id = :WhsDocumentPurchType_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @Res as WhsDocumentSupply_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			$result_add_WhsDocumentSupply = $this->db->query($query_add_WhsDocumentSupply, $params_add_WhsDocumentSupply);
			$result_add_WhsDocumentSupply = $result_add_WhsDocumentSupply->result('array');
			$query_set_InsertDT = 'update WhsDocumentUc with (rowlock) set WhsDocumentUc_ImportDT = dbo.tzGetDate() where WhsDocumentUc_id = :WhsDocumentUc_id';
			$this->db->query($query_set_InsertDT,array('WhsDocumentUc_id' => $result_add_WhsDocumentSupply[0]['WhsDocumentSupply_id']));
			//$CommercialOffer_id
			$query_update_WhsDocumentSupply = "
				update WhsDocumentSupply with (rowlock) 
				set 
					CommercialOffer_id = :CommercialOffer_id, 
					WhsDocumentSupply_PriceWithoutNds = :WhsDocumentSupply_PriceWithoutNds 
				where WhsDocumentSupply_id = :WhsDocumentSupply_id
			";
			$this->db->query($query_update_WhsDocumentSupply, array(
				'WhsDocumentSupply_id'				=> $result_add_WhsDocumentSupply[0]['WhsDocumentSupply_id'],
				'WhsDocumentSupply_PriceWithoutNds'	=> $vIntegrContracts['costWithoutNds'],
				'CommercialOffer_id'				=> $CommercialOffer_id
			));
			$WhsDocumentSupply_id = $result_add_WhsDocumentSupply[0]['WhsDocumentSupply_id'];
			//Загружаем графики отгрузки контракта
			$res_whsdet = $this->exec('/contract_details/number/'.$data['WhsDocumentUc_Num'].'/'.(($data['import_Type']==1)?$data['WhsDocumentUc_Num']:$data['suppl_agreement']), $type = 'get', $d = null);
			$ContractDetails = array();
			$execDate = '2000-01-01';
			foreach($res_whsdet as $res_whsdet_item)
			{
				$vIntegrContractsDetails = objectToArray($res_whsdet_item);
				$vIntegrContractsDetails_date = new DateTime($vIntegrContractsDetails['startDate']);
				$vIntegrContractsDetails_enddate = new DateTime($vIntegrContractsDetails['endDate']);
				$endDate = $vIntegrContractsDetails_enddate->format('Y-m-d');
				$vIntegrContractsDetails['startDate'] = $vIntegrContractsDetails_date->format('Y-m-d');
				$vIntegrContractsDetails['endDate'] = $endDate;
				//$contract_details_index = array_search($vIntegrContractsDetails['priceDetailId'],$ContractDetails);
				$contract_details_index = array_search($vIntegrContractsDetails['priceDetailId'], array_column($ContractDetails, 'priceDetailId'));
				//if(!$contract_details_index || $contract_details_index < 0)
				if($contract_details_index === false)
				{
					$ContractDetails[] = $vIntegrContractsDetails;
				}
				else
				{
					if($vIntegrContractsDetails['startDate'] >= $ContractDetails[$contract_details_index]['startDate'])
						$ContractDetails[$contract_details_index] = $vIntegrContractsDetails['startDate'];
				}
				if($endDate > $execDate)
					$execDate = $endDate;
			}
			for($c=0; $c<count($ContractDetails); $c++)
			{
				$vIntegrContractsDetails = objectToArray($res_whsdet_item);
				//if($vIntegrContractsDetails['additionNumber'] != $vIntegrContractsDetails['contractNumber']) 
				//?? непонятно, зачем делать проверку на additionNumber != contractNumber и искать доп соглашение, если мы выше уже сохранили и получили нужный WhsDocumentSupply_id
				
				$CommercialOfferDrug_id = null;
				$Drug_id = null;
				$DrugComplexMnn_id = null;
				$CommercialOfferDrug_Package = 1;
				$GoodsUnit_id = 0;
				$params_get_CommercialOfferDrug = array(
					'CommercialOfferDrug_PriceDetail' => $ContractDetails[$c]['priceDetailId']
				);
				$query_get_CommercialOfferDrug = "
					select top 1 
						COD.CommercialOfferDrug_id, 
						COD.Drug_id, 
						D.DrugComplexMnn_id, 
						ISNULL(COD.CommercialOfferDrug_Package,1) as CommercialOfferDrug_Package, 
						ISNULL(GU.GoodsUnit_id,0) as GoodsUnit_id
					from v_CommercialOfferDrug COD (nolock)
					left join rls.v_Drug D (nolock) on D.Drug_id = COD.Drug_id
					left join dbo.GoodsUnit GU (nolock) on GU.GoodsUnit_Name = COD.CommercialOfferDrug_UnitName
					where COD.CommercialOfferDrug_PriceDetail = :CommercialOfferDrug_PriceDetail
				";
				$result_get_CommercialOfferDrug = $this->db->query($query_get_CommercialOfferDrug,$params_get_CommercialOfferDrug);
				if(is_object($result_get_CommercialOfferDrug)){
					$result_get_CommercialOfferDrug = $result_get_CommercialOfferDrug->result('array');
					if(is_array($result_get_CommercialOfferDrug) && count($result_get_CommercialOfferDrug) > 0)
					{
						$CommercialOfferDrug_id = $result_get_CommercialOfferDrug[0]['CommercialOfferDrug_id'];
						$Drug_id = $result_get_CommercialOfferDrug[0]['Drug_id'];
						$DrugComplexMnn_id = $result_get_CommercialOfferDrug[0]['DrugComplexMnn_id'];
						$CommercialOfferDrug_Package = $result_get_CommercialOfferDrug[0]['CommercialOfferDrug_Package'];
						$GoodsUnit_id = $result_get_CommercialOfferDrug[0]['GoodsUnit_id'];

						$params_add_WhsDocumentSupplySpec = array(
							'WhsDocumentSupply_id' => $WhsDocumentSupply_id,
							'CommercialOfferDrug_id'					=> $CommercialOfferDrug_id,
							'Drug_id'									=> $Drug_id,
							'DrugComplexMnn_id'							=> $DrugComplexMnn_id,
							'Okei_id'									=> '120',
							'WhsDocumentSupplySpec_KolvoUnit'			=> $ContractDetails[$c]['count'] / $CommercialOfferDrug_Package,
							'GoodsUnit_id'								=> $GoodsUnit_id,
							'WhsDocumentSupplySpec_GoodsUnitQty'		=> $ContractDetails[$c]['count'],
							'WhsDocumentSupplySpec_Price'				=> $ContractDetails[$c]['cost'],
							'WhsDocumentSupplySpec_PriceNDS'			=> $ContractDetails[$c]['cost'],
							'WhsDocumentSupplySpec_NDS'					=> 0,
							'WhsDocumentSupplySpec_SumNDS'				=> $ContractDetails[$c]['cost'] * $ContractDetails[$c]['count'],
							'FIRMNAMES_id'								=> null,
							'DRUGPACK_id'								=> null,
							'WhsDocumentSupplySpec_KolvoForm'			=> null,
							'WhsDocumentSupplySpec_Count'				=> null,
							'WhsDocumentSupplySpec_ShelfLifePersent'	=> 0,
							'WhsDocumentProcurementRequestSpec_id'		=> null,
							'Drug_did'									=> null,
							'pmUser_id'									=> $data['pmUser_id']
						);
						$query_add_WhsDocumentSupplySpec = "
							declare
								@Res bigint,
								@ErrCode int,
								@ErrMessage varchar(4000);
							set @Res = null;
							exec dbo.p_WhsDocumentSupplySpec_ins
							@WhsDocumentSupplySpec_id = @Res output,
							@WhsDocumentSupply_id 	= :WhsDocumentSupply_id,
							@CommercialOfferDrug_id = :CommercialOfferDrug_id,
							@Drug_id = :Drug_id,
							@DrugComplexMnn_id = :DrugComplexMnn_id,
							@Okei_id = :Okei_id,
							@WhsDocumentSupplySpec_KolvoUnit = :WhsDocumentSupplySpec_KolvoUnit,
							@GoodsUnit_id = :GoodsUnit_id,
							@WhsDocumentSupplySpec_GoodsUnitQty = :WhsDocumentSupplySpec_GoodsUnitQty,
							@WhsDocumentSupplySpec_Price = :WhsDocumentSupplySpec_Price,
							@WhsDocumentSupplySpec_PriceNDS = :WhsDocumentSupplySpec_PriceNDS,
							@WhsDocumentSupplySpec_NDS = :WhsDocumentSupplySpec_NDS,
							@WhsDocumentSupplySpec_SumNDS = :WhsDocumentSupplySpec_SumNDS,
							@FIRMNAMES_id = :FIRMNAMES_id,
							@DRUGPACK_id = :DRUGPACK_id,
							@WhsDocumentSupplySpec_KolvoForm = :WhsDocumentSupplySpec_KolvoForm,
							@WhsDocumentSupplySpec_Count = :WhsDocumentSupplySpec_Count,
							@WhsDocumentSupplySpec_ShelfLifePersent = :WhsDocumentSupplySpec_ShelfLifePersent,
							@WhsDocumentProcurementRequestSpec_id = :WhsDocumentProcurementRequestSpec_id,
							@Drug_did = :Drug_did,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output;

							select @Res as WhsDocumentSupplySpec_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
						";
						//echo getDebugSQL($query_add_WhsDocumentSupplySpec, $params_add_WhsDocumentSupplySpec);die;
						$result_add_WhsDocumentSupplySpec = $this->db->query($query_add_WhsDocumentSupplySpec, $params_add_WhsDocumentSupplySpec);
						if(is_object($result_add_WhsDocumentSupplySpec))
						{
							$result_add_WhsDocumentSupplySpec = $result_add_WhsDocumentSupplySpec->result('array');
							if(is_array($result_add_WhsDocumentSupplySpec) && count($result_add_WhsDocumentSupplySpec) > 0)
							{
								$WhsDocumentSupplySpec_id = $result_add_WhsDocumentSupplySpec[0]['WhsDocumentSupplySpec_id'];
								$params_add_WhsDocumentDelivery = array(
									'WhsDocumentSupply_id'		=> $WhsDocumentSupply_id,
									'WhsDocumentSupplySpec_id'	=> $WhsDocumentSupplySpec_id,
									'WhsDocumentDelivery_Kolvo' => $ContractDetails[$c]['count'] / $result_get_CommercialOfferDrug[0]['CommercialOfferDrug_Package'],
									'Okei_id'					=> 120,
									'WhsDocumentDelivery_setDT'	=> $ContractDetails[$c]['endDate'],
									'pmUser_id'					=> $data['pmUser_id']
								);
								$query_add_WhsDocumentDelivery = "
									declare 
										@Res bigint,
										@ErrCode int,
										@ErrMessage varchar(4000);
									set @Res = null;
									exec dbo.p_WhsDocumentDelivery_ins
									@WhsDocumentDelivery_id = @Res output,
									@WhsDocumentSupply_id = :WhsDocumentSupply_id,
									@WhsDocumentSupplySpec_id = :WhsDocumentSupplySpec_id,
									@WhsDocumentDelivery_Kolvo = :WhsDocumentDelivery_Kolvo,
									@Okei_id = :Okei_id,
									@WhsDocumentDelivery_setDT = :WhsDocumentDelivery_setDT,
									@pmUser_id = :pmUser_id,
									@Error_Code = @ErrCode output,
									@Error_Message = @ErrMessage output;

									select @Res as WhsDocumentDelivery_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
								";
								$result_add_WhsDocumentDelivery = $this->db->query($query_add_WhsDocumentDelivery, $params_add_WhsDocumentDelivery);
							}
						}

					}
				}

			}

			$query_update_WhsDocumentSupply = "
				update WhsDocumentSupply with (rowlock) 
				set 
					WhsDocumentSupply_ExecDate = :WhsDocumentSupply_ExecDate 
				where WhsDocumentSupply_id = :WhsDocumentSupply_id
			";
			$this->db->query($query_update_WhsDocumentSupply, array(
				'WhsDocumentSupply_id'			=> $WhsDocumentSupply_id,
				'WhsDocumentSupply_ExecDate'	=> $execDate
			));
		}
		return array(array('success' => true, 'WhsDocumentUc_id' => $result_add_WhsDocumentSupply[0]['WhsDocumentSupply_id']));
	}

	/**
	*	Запуск импорта накладной
	*/
	function importDocumentUc($data)
	{
		//Получим склад-получатель
		$Storage_tid = null;
		if(isset($data['MedService_id']) && $data['MedService_id'] > 0)
		{
			$params_get_Storage = array(
				'MedService_id'	=> $data['MedService_id']
			);

			$query_get_Storage = "
				select top 1
					SSL.Storage_id
				from v_StorageStructLevel SSL (nolock)
				where SSL.MedService_id = :MedService_id
			";
			$result_get_Storage = $this->db->query($query_get_Storage,$params_get_Storage);
			if(is_object($result_get_Storage)){
				$result_get_Storage = $result_get_Storage->result('array');
				if(is_array($result_get_Storage) && count($result_get_Storage)>0)
					$Storage_tid = $result_get_Storage[0]['Storage_id'];
			}
		}

		$query = '/shipment_invoices/equals/docNumber/'.$data['Document_Number'];
		$res = $this->exec($query,$type = 'get', $d = null);

		foreach($res as $result) {
			$vIntegrInvoices = objectToArray($result);

			// Проверим, а есть ли у нас договор, по которому проходит данная накладная? contractNumber
			$Contragent_sid = null;
			$DrugFinance_id = null;
			$WhsDocumentCostItemType_id = null;
			$WhsDocumentUc_id = null;
			$params_check_WhsDocumentUc = array(
				'WhsDocumentUc_Num'	=> $vIntegrInvoices['contractNumber']
			);
			$query_check_WhsDocumentUc = "
				select top 1 
					WDU.WhsDocumentUc_id,
					--ISNULL(CS.Contragent_id,0) as Contragent_sid,
					CS.Contragent_id as Contragent_sid,
					WDS.DrugFinance_id as DrugFinance_id,
					WDS.WhsDocumentCostItemType_id,
					WDS.WhsDocumentSupply_id
				from v_WhsDocumentUc WDU (nolock)
				inner join v_WhsDocumentSupply WDS (nolock) on WDS.WhsDocumentUc_id = WDU.WhsDocumentUc_id
				inner join v_Org O (nolock) on O.Org_id = WDS.Org_sid
				outer apply(
					select top 1 C1.Contragent_id
					from v_Contragent C1 (nolock)
					where C1.Org_id = WDS.Org_sid
				) as CS
				where WDU.WhsDocumentUc_Num = :WhsDocumentUc_Num
				and O.Org_Nick like '%Фармация%'
			";
			$result_check_WhsDocumentUc = $this->db->query($query_check_WhsDocumentUc, $params_check_WhsDocumentUc);
			if(is_object($result_check_WhsDocumentUc))
			{
				$result_check_WhsDocumentUc = $result_check_WhsDocumentUc->result('array');
				if(count($result_check_WhsDocumentUc) > 0)
				{
					$WhsDocumentUc_id = $result_check_WhsDocumentUc[0]['WhsDocumentUc_id'];
					$Contragent_sid = $result_check_WhsDocumentUc[0]['Contragent_sid'];
					$DrugFinance_id = $result_check_WhsDocumentUc[0]['DrugFinance_id'];
					$WhsDocumentCostItemType_id = $result_check_WhsDocumentUc[0]['WhsDocumentCostItemType_id'];

					//Найдем контрагента
					$Contragent_id = null;
					$Mol_tid = 0;
					if(isset($data['session']['lpu_id']) && $data['session']['lpu_id'] > 0)
					{
						//Ищем контрагента с типом "МО"
						$Contragent_Type = 'mo';
					}
					else
					{
						//Ищем контрагента с типом "Аптека"
						$Contragent_Type = 'apt';
					}
					$params_get_Contragent = array(
						'Org_id'			=> $data['session']['org_id'],
						'ContragentType_SysNick'	=> $Contragent_Type
					);
					$query_get_Contragent = "
						select top 1 
							C.Contragent_id,
							ISNULL(M.Mol_id, 0) as Mol_id
						from v_ContragentOrg CO (nolock)
						inner join v_Contragent C (nolock) on C.Contragent_id = CO.Contragent_id
						inner join v_ContragentType CT (nolock) on CT.ContragentType_id = C.ContragentType_id
						outer apply(
							select Mol_id
							from v_Mol (nolock)
							where Contragent_id = C.Contragent_id
						) as M
						where CO.Org_id = :Org_id
						and CT.ContragentType_SysNick = :ContragentType_SysNick
					";
					$result_get_Contragent = $this->db->query($query_get_Contragent, $params_get_Contragent);
					if(is_object($result_get_Contragent)){
						$result_get_Contragent = $result_get_Contragent->result('array');
						if(is_array($result_get_Contragent) && count($result_get_Contragent) > 0)
							{
								$Contragent_id = $result_get_Contragent[0]['Contragent_id'];
								$Mol_tid = $result_get_Contragent[0]['Mol_id'];
							}
					}
					$path = EXPORTPATH_ROOT."efis_import/";
					if (!file_exists($path)) {
						mkdir( $path );
					}
					$link = $path.'importDocNak-'.date("Ymd").'.txt';
					file_put_contents($link,''.PHP_EOL,FILE_APPEND);
					file_put_contents($link,''.PHP_EOL,FILE_APPEND);
					file_put_contents($link,''.PHP_EOL,FILE_APPEND);
					file_put_contents($link,'----------------------------------------------------------'.PHP_EOL,FILE_APPEND);
					file_put_contents($link,date("Y-m-d H:i:s").'Протокол импорта:'.PHP_EOL,FILE_APPEND);
					//Добавляем даные в DocumentUc
					$docDate = new DateTime($vIntegrInvoices['docDate']);
					$docInvoiceDate = new DateTime($vIntegrInvoices['docInvoiceDate']);
					$params_add_DocumentUc = array(
						'DocumentUc_Num'				=> $vIntegrInvoices['docNumber'],
						'DocumentUc_setDate'			=> $docDate->format('Y-m-d'),
						'DocumentUc_didDate'			=> date('Y-m-d'),
						'DocumentUc_DogNum'				=> $vIntegrInvoices['contractNumber'],
						'DocumentUc_DogDate'			=> null,
						'DocumentUc_Sum'				=> null,
						'DocumentUc_SumR'				=> $vIntegrInvoices['docSumm'],
						'DocumentUc_SumNds'				=> null,
						'DocumentUc_SumNdsR'			=> null,
						'Lpu_id'						=> (isset($data['session']['lpu_id']) && $data['session']['lpu_id'] > 0)?$data['session']['lpu_id']:null,
						'Contragent_id'					=> $Contragent_id,
						'Contragent_sid'				=> $Contragent_sid,
						'Mol_sid'						=> null,
						'Contragent_tid'				=> $Contragent_id,
						'Mol_tid'						=> $Mol_tid,
						'DrugFinance_id'				=> $DrugFinance_id,
						'DrugDocumentType_id'			=> 6,
						'DrugDocumentStatus_id'			=> 1,
						'Org_id'						=> $data['session']['org_id'],
						'Storage_sid'					=> null,
						'SubAccountType_sid'			=> null,
						'Storage_tid'					=> $Storage_tid,
						'SubAccountType_tid'			=> 1,
						'WhsDocumentCostItemType_id'	=> $WhsDocumentCostItemType_id,
						'WhsDocumentUc_id'				=> $WhsDocumentUc_id,
						'DrugDocumentClass_id'			=> null,
						'DocumentUc_planDT'				=> null,
						'DocumentUc_begDT'				=> null,
						'DocumentUc_endDT'				=> null,
						'DocumentUc_InvoiceNum'			=> $vIntegrInvoices['docInvoiceSuffix'],
						'DocumentUc_InvoiceDate'		=> $docInvoiceDate->format('Y-m-d'),
						'EmergencyTeam_id'				=> null,
						'DocumentUc_IsImport'			=> 2,
						'pmUser_id'						=> $data['pmUser_id']
					);
					$query_add_DocumentUc = "
						declare
							@Res bigint,
							@ErrCode bigint,
							@ErrMsg varchar(4000);
						set @Res = NULL;

						exec p_DocumentUc_ins
							@DocumentUc_id 					= @Res output,
							@DocumentUc_Num 				= :DocumentUc_Num,
							@DocumentUc_setDate 			= :DocumentUc_setDate,
							@DocumentUc_didDate 			= :DocumentUc_didDate,
							@DocumentUc_DogNum 				= :DocumentUc_DogNum,
							@DocumentUc_DogDate 			= :DocumentUc_DogDate,
							@DocumentUc_Sum 				= :DocumentUc_Sum,
							@DocumentUc_SumR 				= :DocumentUc_SumR,
							@DocumentUc_SumNds 				= :DocumentUc_SumNds,
							@DocumentUc_SumNdsR 			= :DocumentUc_SumNdsR,
							@Lpu_id 						= :Lpu_id,
							@Contragent_id 					= :Contragent_id,
							@Contragent_sid 				= :Contragent_sid,
							@Mol_sid 						= :Mol_sid,
							@Contragent_tid 				= :Contragent_tid,
							@Mol_tid 						= :Mol_tid,
							@DrugFinance_id 				= :DrugFinance_id,
							@DrugDocumentType_id 			= :DrugDocumentType_id,
							@DrugDocumentStatus_id 			= :DrugDocumentStatus_id,
							@Org_id 						= :Org_id,
							@Storage_sid 					= :Storage_sid,
							@SubAccountType_sid 			= :SubAccountType_sid,
							@Storage_tid 					= :Storage_tid,
							@SubAccountType_tid 			= :SubAccountType_tid,
							@WhsDocumentCostItemType_id 	= :WhsDocumentCostItemType_id,
							@WhsDocumentUc_id 				= :WhsDocumentUc_id,
							@DrugDocumentClass_id 			= :DrugDocumentClass_id,
							@DocumentUc_planDT 				= :DocumentUc_planDT,
							@DocumentUc_begDT 				= :DocumentUc_begDT,
							@DocumentUc_endDT 				= :DocumentUc_endDT,
							@DocumentUc_InvoiceNum 			= :DocumentUc_InvoiceNum,
							@DocumentUc_InvoiceDate 		= :DocumentUc_InvoiceDate,
							@EmergencyTeam_id 				= :EmergencyTeam_id,
							@DocumentUc_IsImport 			= :DocumentUc_IsImport,
							@pmUser_id 						= :pmUser_id,
							@Error_Code 					= @ErrCode output,
							@Error_Message 					= @ErrMsg output;
						select @Res as DocumentUc_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
					";
					//echo getDebugSQL($query_add_DocumentUc, $params_add_DocumentUc);die;
					$result_add_DocumentUc = $this->db->query($query_add_DocumentUc, $params_add_DocumentUc);

					if(is_object($result_add_DocumentUc))
					{
						$result_add_DocumentUc = $result_add_DocumentUc->result('array');
						if(is_array($result_add_DocumentUc) && count($result_add_DocumentUc) > 0)
						{
							file_put_contents($link,date("Y-m-d H:i:s").'Накладная №'.$data['Document_Number'].' добавлена'.PHP_EOL,FILE_APPEND);
							$DocumentUc_id = $result_add_DocumentUc[0]['DocumentUc_id'];

							//получаем медикаменты накладной
							$query_det = '/shipment_invoice_details/equals/docNumber/'.$data['Document_Number'];
							$res_det = $this->exec($query_det,$type = 'get', $d = null);
							foreach($res_det as $result)
							{
								$vIntegrInvoiceDetails = objectToArray($result);
								$priceDetailId = $vIntegrInvoiceDetails['priceDetailId'];
								$regCertName = $vIntegrInvoiceDetails['regCertName'];
								$params_get_CommercialOfferDrug = array(
									'priceDetailId'	=> $priceDetailId,
									'regCertName'	=> $regCertName
								);
								$query_get_CommercialOfferDrug = "
									select top 1 
										ISNULL(COD.Drug_id,-1) as Drug_id,
										ISNULL(P.Prep_id,-1) as Prep_id,
										ISNULL(GU.GoodsUnit_id,-1) as GoodsUnit_id,
										COD.CommercialOfferDrug_UnitName,
										ISNULL(COD.CommercialOfferDrug_Package,1) as CommercialOfferDrug_Package,
										CONVERT(varchar(10),CO.CommercialOffer_begDT,120) as CommercialOffer_begDT,
										COD.CommercialOfferDrug_Price
									from v_CommercialOfferDrug COD (nolock)
									left join v_GoodsUnit GU (nolock) on GU.GoodsUnit_Name = COD.CommercialOfferDrug_UnitName
									left join v_CommercialOffer CO (nolock) on CO.CommercialOffer_id = COD.CommercialOffer_id
									left join rls.v_Drug D (nolock) on D.Drug_id = COD.Drug_id
									left join rls.v_Prep P (nolock) on P.Prep_id = D.DrugPrep_id
									where COD.CommercialOfferDrug_PriceDetail = :priceDetailId
									and COD.CommercialOfferDrug_RegCertName = :regCertName
								";
								//echo getDebugSQL($query_get_CommercialOfferDrug, $params_get_CommercialOfferDrug);die;
								$result_get_CommercialOfferDrug = $this->db->query($query_get_CommercialOfferDrug, $params_get_CommercialOfferDrug);
								if(is_object($result_get_CommercialOfferDrug))
								{
									$result_get_CommercialOfferDrug = $result_get_CommercialOfferDrug->result('array');
									if(is_array($result_get_CommercialOfferDrug) && count($result_get_CommercialOfferDrug) > 0)
									{
										$Drug_id = $result_get_CommercialOfferDrug[0]['Drug_id'];
										if(isset($Drug_id) && $Drug_id > -1)
										{
											$GoodsUnit_id = -1;
											if($result_get_CommercialOfferDrug[0]['GoodsUnit_id'] < 0) //Если единицы измерениея нет в нашем справочнике, то создадим ее
											{
												$params_add_GoodsUnit = array(
													'GoodsUnit_Name'	=> $result_get_CommercialOfferDrug[0]['CommercialOfferDrug_UnitName'],
													'pmUser_id'			=> $data['pmUser_id']
												);
												$query_add_GoodsUnit = "
													declare
														@Res bigint,
														@ErrCode bigint,
														@ErrMsg varchar(4000);
													set @Res = NULL;

													exec p_GoodsUnit_ins
														@GoodsUnit_id 		= @Res output,
														@GoodsUnit_Name 	= :GoodsUnit_Name,
														@GoodsUnit_Nick		= :GoodsUnit_Name,
														@pmUser_id 			= :pmUser_id,
														@Error_Code 		= @ErrCode output,
														@Error_Message 		= @ErrMsg output;
													select @Res as GoodsUnit_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
												";
												$result_add_GoodsUnit = $this->db->query($query_add_GoodsUnit,$params_add_GoodsUnit);
												if(is_object($result_add_GoodsUnit))
												{
													$result_add_GoodsUnit = $result_add_GoodsUnit->result('array');
													if(is_array($result_add_GoodsUnit) && count($result_add_GoodsUnit) > 0)
													{
														$GoodsUnit_id = $result_add_GoodsUnit[0]['GoodsUnit_id'];
													}
													else
													{
														//ДОБАВЛЕНИЕ В ЛОГ СООБЩЕНИЯ О НЕВОЗМОЖНОСТИ ДОБАВЛЕНИЯ ЕДИНИЦЫ ИЗМЕРЕНИЯ
														file_put_contents($link,date("Y-m-d H:i:s").' Ошибка добавления единицы измерения "'.$result_get_CommercialOfferDrug[0]['CommercialOfferDrug_UnitName'].'"'.PHP_EOL,FILE_APPEND);
													}
												}
												else
												{
													//ДОБАВЛЕНИЕ В ЛОГ СООБЩЕНИЯ О НЕВОЗМОЖНОСТИ ДОБАВЛЕНИЯ ЕДИНИЦЫ ИЗМЕРЕНИЯ
													file_put_contents($link,date("Y-m-d H:i:s").' Ошибка добавления единицы измерения "'.$result_get_CommercialOfferDrug[0]['CommercialOfferDrug_UnitName'].'"'.PHP_EOL,FILE_APPEND);
												}
											}
											else
												$GoodsUnit_id = $result_get_CommercialOfferDrug[0]['GoodsUnit_id'];

											if($GoodsUnit_id != -1)
											{
												//Получили единицу измерения. Двигаемся дальше
												$DrugNds_id = null;
												$DocumentUcStr_EdCount = $vIntegrInvoiceDetails['count'];
												$DocumentUcStr_Count = $vIntegrInvoiceDetails['count'] / $result_get_CommercialOfferDrug[0]['CommercialOfferDrug_Package'];
												$Okei_id = 120;
												$DocumentUcStr_Price = $vIntegrInvoiceDetails['summ'] / $DocumentUcStr_Count;
												$DocumentUcStr_PriceR = $DocumentUcStr_Price;
												$DocumentUcStr_Sum = $vIntegrInvoiceDetails['summ'];
												$DocumentUcStr_SumR = $DocumentUcStr_Sum;
												$DocumentUcStr_SumNds = null;
												$DocumentUcStr_SumNdsR = null;
												$DocumentUcStr_Ser = $vIntegrInvoiceDetails['seriesNumber'];
												$DocumentUcStr_CertNum = $vIntegrInvoiceDetails['certificateNumber'];
												$DocumentUcStr_CertDate = null;
												$certificateExpireDate = new DateTime($vIntegrInvoiceDetails['certificateExpireDate']);
												$DocumentUcStr_CertGodnDate = $certificateExpireDate->format('Y-m-d');
												$DocumentUcStr_CertOrg = null;
												$DocumentUcStr_RegDate = $result_get_CommercialOfferDrug[0]['CommercialOffer_begDT'];
												$DocumentUcStr_RegPrice = $result_get_CommercialOfferDrug[0]['CommercialOfferDrug_Price'];
												$seriesDate = new DateTime($vIntegrInvoiceDetails['seriesDate']);
												$DocumentUcStr_godnDate = $seriesDate->format('Y-m-d');
												$DocumentUcStr_NTU = $priceDetailId;

												//Найдем данные в PrepSeries по Drug_id, seriesNumber и seriesDate. Если пусто - то добавим.
												$PrepSeries_id = -1;
												$params_get_PrepSeries = array(
													'Drug_id'				=> $result_get_CommercialOfferDrug[0]['Drug_id'],
													'PrepSeries_Ser'		=> $DocumentUcStr_Ser,
													'PrepSeries_GodnDate'	=> $DocumentUcStr_godnDate
												);
												$query_get_PrepSeries = "
													select top 1 PS.PrepSeries_id
													from rls.v_PrepSeries PS (nolock)
													where PS.Drug_id = :Drug_id
													and PrepSeries_Ser = :PrepSeries_Ser
													and PrepSeries_GodnDate = :PrepSeries_GodnDate

												";
												$result_get_PrepSeries = $this->db->query($query_get_PrepSeries,$params_get_PrepSeries);
												if(is_object($result_get_PrepSeries))
												{
													$result_get_PrepSeries = $result_get_PrepSeries->result('array');
													if(is_array($result_get_PrepSeries) && count($result_get_PrepSeries) > 0)
														$PrepSeries_id = $result_get_PrepSeries[0]['PrepSeries_id'];
												}

												if($PrepSeries_id == -1 && $result_get_CommercialOfferDrug[0]['Prep_id'] > -1)
												{
													$PrepSeries_id = null;
													$params_add_PrepSeries = array(
														'Prep_id'				=> $result_get_CommercialOfferDrug[0]['Prep_id'],
														'Drug_id'				=> $result_get_CommercialOfferDrug[0]['Drug_id'],
														'PrepSeries_Ser'		=> $DocumentUcStr_Ser,
														'PrepSeries_GodnDate'	=> $DocumentUcStr_godnDate,
														'pmUser_id'				=> $data['pmUser_id']
													);
													$query_add_PrepSeries = "
														declare
															@Res bigint,
															@ErrCode bigint,
															@ErrMsg varchar(4000);
														set @Res = NULL;

														exec rls.p_PrepSeries_ins
															@PrepSeries_id 			= @Res output,
															@Prep_id 				= :Prep_id,
															@Drug_id 				= :Drug_id,
															@PrepSeries_Ser			= :PrepSeries_Ser,
															@PrepSeries_GodnDate	= :PrepSeries_GodnDate,
															@pmUser_id 				= :pmUser_id,
															@Error_Code 			= @ErrCode output,
															@Error_Message 			= @ErrMsg output;

														select @Res as PrepSeries_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
													";
													$result_add_PrepSeries = $this->db->query($query_add_PrepSeries,$params_add_PrepSeries);
													if(is_object($result_add_PrepSeries))
													{
														$result_add_PrepSeries = $result_add_PrepSeries->result('array');
														if(count($result_add_PrepSeries) > 0)
															$PrepSeries_id = $result_add_PrepSeries[0]['PrepSeries_id'];
													}
												}

												if($PrepSeries_id == -1)
												{
													$PrepSeries_id = null;
												}
												else
												{
													//Добавили серию, двигаемся дальше.
													$DocumentUcStr_IsNDS = 1;

													//Добавляем данные в DrugShipment
													$DrugShipment_id = -1;
													/*$params_get_DrugShipment = array(
														'WhsDocumentSupply_id' => $result_check_WhsDocumentUc[0]['WhsDocumentSupply_id']
													);
													$query_get_DrugShipment = "
														select DrugShipment_id
														from v_DrugShipment
														where WhsDocumentSupply_id = :WhsDocumentSupply_id
													";
													$result_get_DrugShipment = $this->db->query($query_get_DrugShipment,$params_get_DrugShipment);
													if(is_object($result_get_DrugShipment))
													{
														$result_get_DrugShipment = $result_get_DrugShipment->result('array');
														if(is_array($result_get_DrugShipment) && count($result_get_DrugShipment) > 0)
															$DrugShipment_id = $result_get_DrugShipment[0]['DrugShipment_id'];
													}*/
													if($DrugShipment_id == -1)
													{
														$params_add_DrugShipment = array(
															'WhsDocumentSupply_id'	=> $result_check_WhsDocumentUc[0]['WhsDocumentSupply_id'],
															'AccountType_id'		=> 1,
															'pmUser_id'				=> $data['pmUser_id']
														);
														$query_add_DrugShipment = "
															declare
																@Res bigint,
																@ErrCode bigint,
																@ErrMsg varchar(4000),
																@name varchar(30);
															set @name = isnull((
																select max(cast(DrugShipment_Name as bigint))+1
																from v_DrugShipment with(nolock)
																where ISNUMERIC(DrugShipment_Name)=1 and DrugShipment_Name not like '%.%' and DrugShipment_Name not like '%,%'
															), 1);
															set @Res = NULL;

															exec p_DrugShipment_ins
																@DrugShipment_id 		= @Res output,
																@DrugShipment_Name 		= @name,
																@WhsDocumentSupply_id	= :WhsDocumentSupply_id,
																@AccountType_id			= :AccountType_id,
																@pmUser_id 				= :pmUser_id,
																@Error_Code 			= @ErrCode output,
																@Error_Message 			= @ErrMsg output;

															select @Res as DrugShipment_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
														";
														//echo getDebugSQL($query_add_DrugShipment,$params_add_DrugShipment);die;
														$result_add_DrugShipment = $this->db->query($query_add_DrugShipment,$params_add_DrugShipment);
														if(is_object($result_add_DrugShipment))
														{
															$result_add_DrugShipment = $result_add_DrugShipment->result('array');
															if(is_array($result_add_DrugShipment) && count($result_add_DrugShipment) > 0)
																$DrugShipment_id = $result_add_DrugShipment[0]['DrugShipment_id'];
														}
													}
													if($DrugShipment_id == -1)
													{
														file_put_contents($link,date("Y-m-d H:i:s").' Ошибка добавления записи о партии медикамента'.PHP_EOL,FILE_APPEND);
													}
													else
													{
														//Добавляем данные в DocumentUcStr
														$DocumentUcStr_id = -1;
														$params_add_DocumentUcStr = array(
															'DocumentUc_id'					=> $DocumentUc_id,
															'Drug_id'						=> $Drug_id,
															'DrugNds_id'					=> $DrugNds_id,
															'DocumentUcStr_EdCount'			=> $DocumentUcStr_EdCount,
															'GoodsUnit_id'					=> $GoodsUnit_id,
															'DocumentUcStr_Count'			=> $DocumentUcStr_Count,
															'Okei_id'						=> $Okei_id,
															'DocumentUcStr_Price'			=> $DocumentUcStr_Price,
															'DocumentUcStr_PriceR'			=> $DocumentUcStr_PriceR,
															'DocumentUcStr_Sum'				=> $DocumentUcStr_Sum,
															'DocumentUcStr_SumR'			=> $DocumentUcStr_SumR,
															'DocumentUcStr_SumNds'			=> $DocumentUcStr_SumNds,
															'DocumentUcStr_SumNdsR'			=> $DocumentUcStr_SumNdsR,
															'DocumentUcStr_Ser'				=> $DocumentUcStr_Ser,
															'DocumentUcStr_CertNum'			=> $DocumentUcStr_CertNum,
															'DocumentUcStr_CertDate'		=> $DocumentUcStr_CertDate,
															'DocumentUcStr_CertGodnDate'	=> $DocumentUcStr_CertGodnDate,
															'DocumentUcStr_CertOrg'			=> $DocumentUcStr_CertOrg,
															'DocumentUcStr_RegDate'			=> $DocumentUcStr_RegDate,
															'DocumentUcStr_RegPrice'		=> $DocumentUcStr_RegPrice,
															'DocumentUcStr_godnDate'		=> $DocumentUcStr_godnDate,
															'DocumentUcStr_NTU'				=> $DocumentUcStr_NTU,
															'PrepSeries_id'					=> $PrepSeries_id,
															'DocumentUcStr_IsNDS'			=> $DocumentUcStr_IsNDS,
															'pmUser_id'						=> $data['pmUser_id']
														);
														$query_add_DocumentUcStr = "
															declare
																@Res bigint,
																@ErrCode bigint,
																@ErrMsg varchar(4000);
															set @Res = NULL;

															exec p_DocumentUcStr_ins
																@DocumentUcStr_id 			= @Res output,
																@DocumentUc_id 				= :DocumentUc_id,
																@Drug_id					= :Drug_id,
																@DrugNds_id					= :DrugNds_id,
																@DocumentUcStr_EdCount		= :DocumentUcStr_EdCount,
																@GoodsUnit_id				= :GoodsUnit_id,
																@DocumentUcStr_Count		= :DocumentUcStr_Count,
																@Okei_id					= :Okei_id,
																@DocumentUcStr_Price		= :DocumentUcStr_Price,
																@DocumentUcStr_PriceR		= :DocumentUcStr_PriceR,
																@DocumentUcStr_Sum			= :DocumentUcStr_Sum,
																@DocumentUcStr_SumR			= :DocumentUcStr_SumR,
																@DocumentUcStr_SumNds		= :DocumentUcStr_SumNds,
																@DocumentUcStr_SumNdsR		= :DocumentUcStr_SumNdsR,
																@DocumentUcStr_Ser			= :DocumentUcStr_Ser,
																@DocumentUcStr_CertNum		= :DocumentUcStr_CertNum,
																@DocumentUcStr_CertDate 	= :DocumentUcStr_CertDate,
																@DocumentUcStr_CertGodnDate	= :DocumentUcStr_CertGodnDate,
																@DocumentUcStr_CertOrg		= :DocumentUcStr_CertOrg,
																@DocumentUcStr_RegDate		= :DocumentUcStr_RegDate,
																@DocumentUcStr_RegPrice		= :DocumentUcStr_RegPrice,
																@DocumentUcStr_godnDate		= :DocumentUcStr_godnDate,
																@DocumentUcStr_NTU			= :DocumentUcStr_NTU,
																@PrepSeries_id				= :PrepSeries_id,
																@DocumentUcStr_IsNDS		= :DocumentUcStr_IsNDS,
																@pmUser_id 					= :pmUser_id,
																@Error_Code 				= @ErrCode output,
																@Error_Message 				= @ErrMsg output;

															select @Res as DocumentUcStr_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
														";
														//echo getDebugSQL($query_add_DocumentUcStr,$params_add_DocumentUcStr);die;
														$result_add_DocumentUcStr = $this->db->query($query_add_DocumentUcStr,$params_add_DocumentUcStr);
														if(is_object($result_add_DocumentUcStr))
														{
															$result_add_DocumentUcStr = $result_add_DocumentUcStr->result('array');
															if(is_array($result_add_DocumentUcStr) && count($result_add_DocumentUcStr) > 0)
																$DocumentUcStr_id = $result_add_DocumentUcStr[0]['DocumentUcStr_id'];
														}
														if($DocumentUcStr_id == -1)
														{
															file_put_contents($link,date("Y-m-d H:i:s").'Ошибка добавления медикамента с кодом СКП "'.$priceDetailId.'" и РУ "'.$regCertName.'" в документ учета'.PHP_EOL,FILE_APPEND);
														}
														else
														{
															//Добавляем данные в DrugShipmentLink
															$params_add_DrugShipmentLink = array(
																'DocumentUcStr_id'	=> $DocumentUcStr_id,
																'DrugShipment_id'	=> $DrugShipment_id,
																'pmUser_id'			=> $data['pmUser_id']
															);
															$query_add_DrugShipmentLink = "
																declare
																	@Res bigint,
																	@ErrCode bigint,
																	@ErrMsg varchar(4000);
																set @Res = NULL;

																exec p_DrugShipmentLink_ins
																	@DrugShipmentLink_id 		= @Res output,
																	@DocumentUcStr_id 			= :DocumentUcStr_id,
																	@DrugShipment_id 			= :DrugShipment_id,
																	@pmUser_id 					= :pmUser_id,
																	@Error_Code 				= @ErrCode output,
																	@Error_Message 				= @ErrMsg output;

																select @Res as DrugShipmentLink_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
															";
															$result_add_DrugShipmentLink = $this->db->query($query_add_DrugShipmentLink,$params_add_DrugShipmentLink);
															file_put_contents($link,date("Y-m-d H:i:s").' Импортирован медикамент с кодом СКП "'.$priceDetailId.'" и РУ "'.$regCertName.'".'.PHP_EOL,FILE_APPEND);
														}
													}
												}
											}

										}
										else
										{
											file_put_contents($link,date("Y-m-d H:i:s").' Для медикамента с кодом СКП "'.$priceDetailId.'" и РУ "'.$regCertName.'" не найдена ссылка на справочник медикаментов РЛС Казмед'.PHP_EOL,FILE_APPEND);
										}

									}
									else
									{
										//ДОБАВЛЕНИЕ В ЛОГ СООБЩЕНИЯ О НЕНАЙДЕННОМ МЕДИКАМЕНТЕ
										file_put_contents($link,date("Y-m-d H:i:s").' По коду СКП "'.$priceDetailId.'" не найден медикамент с РУ "'.$regCertName.'" в действующем прайсе ТОО СК "Фармация"'.PHP_EOL,FILE_APPEND);
									}
								}
								else
									{
										//ДОБАВЛЕНИЕ В ЛОГ СООБЩЕНИЯ О НЕНАЙДЕННОМ МЕДИКАМЕНТЕ
										file_put_contents($link,date("Y-m-d H:i:s").' По коду СКП "'.$priceDetailId.'" не найден медикамент с РУ "'.$regCertName.'" в действующем прайсе ТОО СК "Фармация"'.PHP_EOL,FILE_APPEND);
									}

							}
						}
						else
						{
							$result = array(
								'success' => false,
								'errorMsg' => 'Ошибка добавления документа в базу.'
							);
							return $result;
						}
					}
					else
					{
						$result = array(
							'success' => false,
							'errorMsg' => 'Ошибка добавления документа в базу.'
						);
						return $result;
					}
				}
				else
				{
					$result = array(
						'success' => false,
						'errorMsg' => 'Контракт с № '.$data['Document_Number'].' не найден. Сохранение накладной не возможно.'
					);
					return $result;
				}
			}
			else
			{
				$result = array(
					'success' => false,
					'errorMsg' => 'Контракт с № '.$data['Document_Number'].' не найден. Сохранение накладной не возможно.'
				);
				return $result;
			}
		}
		file_put_contents($link,date("Y-m-d H:i:s").' Импорт завершен'.PHP_EOL,FILE_APPEND);
		$result = array(
			'success' => true,
			'errorMsg' => '',
			'info'	=> '<br />Скачать <a target="_blank" title="Скачать протокол импорта" href="'.$link.'">Скачать</a>'
		);
		return $result;
	}

	/**
	 * Тест
	 */
	function test() {
		set_time_limit(0);
		ini_set("max_execution_time", "0");

		echo '<pre>';

		/*$resp = $this->exec('/prices/2/4');
		print_r($resp);*/

		/*echo '<pre>';

		$resp = $this->runImport(array(
			'objects' => 'all',
			'pmUser_id' => '1573676522'
		));

		print_r($resp);*/

		$ActiveCommercialOffer = $this->getFirstRowFromQuery("
			select top 1 KM_CommercialOffer_guid as guid, Name 
			from {$this->scheme}.KM_CommercialOffer with(nolock) 
			where Status = 1
		", array(), true);
		if ($ActiveCommercialOffer === false) {
			return $this->createError('Ошибка при получении активного прайса');
		}
		if (empty($ActiveCommercialOffer)) {
			return $this->createError('Не найден активный прайс');
		}

		$list = $this->exec("/price_details/like/priceId/{$ActiveCommercialOffer['Name']}%");

		foreach($list as $item1) {
			$item = objectToArray($item1);
			print_r($item1);
			$params = array(
				'Price_detail' => $item['priceDetail'],
				'Prev_price_detail' => null,
				//'KM_CommercialOffer_guid' => $CommercialOffer_guid,
				'Drug_id' => null,
				'DrugPrepFas_id' => null,
				'KM_CommercialOfferDrug_Price' => $item['cost'],
				'Unit_Name' => $item['unitName'],
				'Goods_guid' => $item['goodsId'],
				'Mnn_name' => $item['mnnName'],
				'Pharm_name' => $item['pharmName'],
				'Drug_form' => $item['drugForm'],
				'Package1' => $item['package1'],
				'Expire' => $item['expire'],
				'Prod_name' => $item['prodName'],
				'Prod_country' => $item['prodCountry'],
				'RegCertName' => $item['regCertName'],
			);
			print_r($params);
		}
	}
}