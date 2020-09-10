<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* MedService_model - модель служб
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009-2011 Swan Ltd.
*/

class MedService_model extends swModel {
	var $scheme = "dbo";

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}
	/**
	 * @comment
	 */
	function checkTTR($data)
	{
		$params = array('Resource_id'=>$data['Resource_id']);
		$sql = "
			Select
				count(*) as record_count
			from v_TimetableResource_lite ttr (nolock)
			where Resource_id= :Resource_id and person_id is not null
		";
		$result = $this->db->query($sql, $params);
		if (is_object($result))
		{
			$rc = $result->result('array');
			if (count($rc)>0 && is_array($rc[0])) {
				return $rc[0]['record_count'];
			}
		}
		return null;
	}
	/**
	 * @comment
	 */
	function deleteResource($data){
		if($this->checkTTR($data)>0){
			return array( array( 'Error_Msg' => 'На данный ресурс уже есть записанные люди. Удаление невозможно' ) );
		}

		$objectList = $this->queryResult("
			select
				SCHEMA_NAME(f.[schema_id]) as [schema_name],
				OBJECT_NAME(f.parent_object_id) as [object_name],
				COL_NAME(fc.parent_object_id, fc.parent_column_id) as [column_name]
			from
				sys.foreign_keys as f with(nolock)
				inner join sys.foreign_key_columns as fc with(nolock) ON f.OBJECT_ID = fc.constraint_object_id
			where OBJECT_NAME(f.referenced_object_id) = 'Resource'
		");

		if ( $objectList === false ) {
			return array(array('Error_Msg' => 'Ошибка при получении списка объектов, связанных с ресурсом'));
		}

		$deniedForDelObjects = array();
		$nonImportantObjects = array();

		foreach ( $objectList as $object ) {
			if ( in_array($object['object_name'], array('EvnDirection', 'TimeTableResource', 'TimeTableResourceHist', 'Annotation')) ) {
				$deniedForDelObjects[] = $object;
			}
			else {
				$nonImportantObjects[] = $object;
			}
		}

		$deniedForDelObjectsQueryArray = array();

		foreach ( $deniedForDelObjects as $object ) {
			$deniedForDelObjectsQueryArray[] = "
				select top 1 {$object['object_name']}_id as id
				from {$object['schema_name']}.{$object['object_name']} with (nolock)
				where {$object['column_name']} = :Resource_id
			";
		}

		$checkResult = $this->queryResult(implode(' union all ', $deniedForDelObjectsQueryArray), array('Resource_id' => $data['Resource_id']));

		if ( $checkResult === false ) {
			return array(array('Error_Msg' => 'Ошибка при проверке наличия ссылок на ресурс в других объектах'));
		}
		else if ( is_array($checkResult) && count($checkResult) > 0 ) {
			return array(array('Error_Msg' => 'Удаление невозможно, т.к. в базе данных существуют объекты, ссылающиеся на удаляемую запись'));
		}

		$nonImportantObjectsQueryArray = array();

		foreach ( $nonImportantObjects as $object ) {
			$nonImportantObjectsQueryArray[] = "
				declare cur1 cursor read_only for
				select {$object['object_name']}_id
				from {$object['schema_name']}.{$object['object_name']} with (nolock)
				where {$object['column_name']} = :Resource_id

				open cur1

				fetch next from cur1 into @id

				while ( @@FETCH_STATUS = 0 )
				begin
					exec {$object['schema_name']}.p_{$object['object_name']}_del
						@{$object['object_name']}_id = @id,
						@Error_Code = @Error_Code output,
						@Error_Message = @Error_Message output;
					fetch next from cur1 into @id
				end

				close cur1
				deallocate cur1
			";
		}

		$sql = "
			declare
				@id bigint,
				@Error_Code int,
				@Error_Message varchar(4000);

			" . implode(' ', $nonImportantObjectsQueryArray) . "

			exec p_Resource_del
				@Resource_id = :Resource_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";
		//echo getDebugSQL($sql,$data);exit();
		$result = $this->db->query($sql, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	/**
	 *
	 * @param type $data
	 * @return type
	 */
	function saveResourceLink($data){
		// Сначала определяем - может просто редактируем связь если запись уже есть а связь не снимается
		$procedure = "p_UslugaComplexResource_ins";
		if($data['isActive'] && (!empty($data['UslugaComplexResource_id']))){
			$procedure = "p_UslugaComplexResource_upd";
			/*
			// Получаем данные по связи - проверяем есть ли реально связь
			$UslugaComplexResource_id = $this->getFirstResultFromQuery("
				select top 1 UslugaComplexResource_id
				from v_UslugaComplexResource with (nolock)
				where UslugaComplexResource_id = :UslugaComplexResource_id
			", array('UslugaComplexResource_id' => $data['UslugaComplexResource_id']));
			if (!empty($UslugaComplexResource_id)) {
				// Апдейтим данные
				$procedure = "p_UslugaComplexResource_upd";
			} else {
				$data['UslugaComplexResource_id'] = null;
			}
			*/
		}
		if($data['isActive']){
			$query = "
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000),
				@UslugaComplexResource_id bigint = :UslugaComplexResource_id;

			exec {$procedure}
				@UslugaComplexResource_id = @UslugaComplexResource_id output,
				@Resource_id = :Resource_id,
				@UslugaComplexMedService_id = :UslugaComplexMedService_id,
				@UslugaComplexResource_Time = :UslugaComplexResource_Time,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
				select @UslugaComplexResource_id as UslugaComplexResource_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
			";
		} elseif (!empty($data['UslugaComplexResource_id'])) {
			// удаляем связь если снята активность
			$query = "
				declare
					@Error_Code bigint,
					@Error_Message varchar(4000);
				exec p_UslugaComplexResource_del
					@UslugaComplexResource_id = :UslugaComplexResource_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output;
					select @Error_Code as Error_Code, @Error_Message as Error_Msg;
			";
		} else {
			// Не делаем никаких операций
			return false;
		}
		//echo getDebugSQL($query, $data);exit;
		$result = $this->db->query($query, $data);
		if(is_object($result)){
			$response = $result->result('array');
			return $response;
		}
		return false;
	}

	/**
	 *
	 * @param type $data
	 * @return type
	 */
	/*function loadUslugaComplexResourceGrid($data){

		$query = "
			SELECT
				-- select
				UCR.UslugaComplexResource_id as UslugaComplexResource_id,
				UCMS.UslugaComplexMedService_id,
				UC.UslugaComplex_Name,
				:Resource_id as Resource_id,
				case when isnull(UCR.UslugaComplexResource_id,'')!='' then 'true' else 'false' end as isActive
				-- end select
			FROM
				-- from
				v_UslugaComplexMedService UCMS with (NOLOCK)
				left join v_UslugaComplex UC with (NOLOCK) on UCMS.UslugaComplex_id = UC.UslugaComplex_id
				outer apply(select top 1 UslugaComplexResource_id from v_UslugaComplexResource UCR with (NOLOCK)
				where UCR.UslugaComplexMedService_id = UCMS.UslugaComplexMedService_id
				and Resource_id = :Resource_id) UCR
				-- end from
			where
				-- where
				UCMS.MedService_id = :MedService_id
				-- end where
		";
		//echo getDebugSQL($query, $data);
		$result = $this->db->query($query, $data);
		if (is_object($result))
		{
			return $result->result('array');
		}
		return false;
	}*/


	/**
	 * Получение связи ресурсов с услугами
	 * Может быть использована в друх режимах: по определенному ресурсу (возвращает услуги), по определенной услуге (возвращает ресурсы)
	 * @param type $data
	 * @return type
	 */
	function loadUslugaComplexResourceGrid($data){
		if ($data['object']=='Resource') { // Если передана услуга, то возвращаем ресурсы по услуге
			$table = "v_Resource";
			$fields = "
				t.Resource_Name,
				:UslugaComplexMedService_id as UslugaComplexMedService_id,
				t.Resource_id as Resource_id,";
			$link = "UCR.Resource_id = t.Resource_id and UCR.UslugaComplexMedService_id = :UslugaComplexMedService_id";
			$join = "";
		} elseif ($data['object']=='UslugaComplexMedService') { // Если передан ресурс, то возвращаем услуги по ресурсу
			$table = "v_UslugaComplexMedService";
			$fields = "UC.UslugaComplex_Name,
				t.UslugaComplexMedService_id as UslugaComplexMedService_id,
				convert(varchar, t.UslugaComplexMedService_begDT, 126) as UslugaComplexMedService_begDT,
				convert(varchar, t.UslugaComplexMedService_endDT, 126) as UslugaComplexMedService_endDT,
				:Resource_id as Resource_id,";
			$key = "t.UslugaComplexMedService_id";
			$link = "UCR.UslugaComplexMedService_id = t.UslugaComplexMedService_id and UCR.Resource_id = :Resource_id";
			$join = "left join v_UslugaComplex UC with (NOLOCK) on t.UslugaComplex_id = UC.UslugaComplex_id";
		} else { // Ничего не возвращаем, по сути это ошибка
			return false;
		}


		$query = "
			SELECT
				-- select
				UCR.UslugaComplexResource_id as UslugaComplexResource_id,
				{$fields}
				case when UCR.UslugaComplexResource_id is not null then 1 else 0 end as isActive,
				/* пока нет полей
				convert(varchar(10),UCMS.UslugaComplexResource_begDT,104) as UslugaComplexResource_begDT,
				convert(varchar(10),UCMS.UslugaComplexResource_endDT,104) as UslugaComplexResource_endDT,
				*/
				UCR.UslugaComplexResource_Time
				-- end select
			FROM
				-- from
				{$table} as t with (NOLOCK)
				{$join}
				-- связь
				outer apply(
					select top 1
						UslugaComplexResource_id,
						UslugaComplexMedService_id,
						Resource_id,
						UslugaComplexResource_Time
					from v_UslugaComplexResource UCR with (NOLOCK)
					where {$link}
				) UCR
				-- end from
			where
				-- where
				t.MedService_id = :MedService_id
				-- end where
		";
		//echo getDebugSQL($query, $data);
		$result = $this->db->query($query, $data);
		if (is_object($result))
		{
			return $result->result('array');
		}
		return false;
	}


	/**
	 *
	 * @param type $data
	 * @return type
	 */
	function loadMedProductCardResourceGrid($data){

		$query = "
			SELECT
				-- select
				MPCR.MedProductCardResource_id,
				1 as RecordStatus_Code,
				MPC.MedProductCard_id,
				MPCl.MedProductClass_Name,
				convert(varchar(10),MPCR.MedProductCardResource_begDT,104) as MedProductCardResource_begDT,
				convert(varchar(10),MPCR.MedProductCardResource_endDT,104) as MedProductCardResource_endDT
				-- end select
			FROM
				-- from
				passport.v_MedProductCardResource MPCR with (nolock)
				left join passport.v_MedProductCard MPC with (nolock) on MPC.MedProductCard_id = MPCR.MedProductCard_id
				left join passport.v_MedProductClass MPCl with (nolock) on MPCl.MedProductClass_id = MPC.MedProductClass_id
				-- end from
			where
				-- where
				MPCR.Resource_id = :Resource_id
				-- end where
		";
		//echo getDebugSQL($query, $data);
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			return $result->result('array');
		}
		return false;
	}

	/**
	 * Сохранение таблицы связи ресурса с медицинским изделием
	 */
	function saveMedProductCardResourceGrid($data) {

		$MedProductCardResourceData = json_decode($data['MedProductCardResourceData'], true);
		foreach($MedProductCardResourceData as $MedProductCardResource) {
			$MedProductCardResource['Resource_id'] = $data['Resource_id'];
			$MedProductCardResource['pmUser_id'] = $data['pmUser_id'];
			if ( $data['ResourceType_id'] != 3 ) {
				$MedProductCardResource['RecordStatus_Code'] = 3;
			}
			switch($MedProductCardResource['RecordStatus_Code']) {
				case 1:
					$resp = true;
					break;
				case 0:
				case 2:
					$resp = $this->saveMedProductCardResource($MedProductCardResource);
					break;
				case 3:
					$resp = $this->deleteMedProductCardResource($MedProductCardResource);
					break;
			}
			if (!empty($resp[0]['Error_Msg']) || !empty($resp[0]['Alert_Msg'])) {
				$this->rollbackTransaction();
				throw new Exception('Ошибка при ' . ($MedProductCardResource['RecordStatus_Code'] == 3 ? 'удалении' : 'сохранении') . ' медицинского изделия');
			}
		}
	}

	/**
	 * Сохранение связей ресурсов и услуг
	 */
	function saveUSRData($data) {
		$ucrData = json_decode($data['ucrData'], true);
		if (!$ucrData) return false;
		foreach($ucrData as $ucrLink) {
			// передавать сохранять
			if (empty($ucrLink['Resource_id']) && isset($data['Resource_id']))
				$ucrLink['Resource_id'] = $data['Resource_id'];
			if (empty($ucrLink['UslugaComplexMedService_id']) && isset($data['UslugaComplexMedService_id']))
				$ucrLink['UslugaComplexMedService_id'] = $data['UslugaComplexMedService_id'];
			$ucrLink['pmUser_id'] = $data['pmUser_id'];
			if ($ucrLink['UslugaComplexResource_Time']==='') {
				$ucrLink['UslugaComplexResource_Time']=null;
			}
			$response = $this->saveResourceLink($ucrLink);
			if (!empty($response[0]['Error_Msg'])) {
				$this->rollbackTransaction();
				throw new Exception('При сохранении связей ресурса и услуги произошла ошибка: '.$response[0]['Error_Msg']);
			}
		}
	}

	/**
	 * Проверка дублирования связи ресурса с медицинским изделием
	 */
	function checkMedProductCardResource($data) {

		$params = array(
			'Resource_id' => $data['Resource_id'],
			'MedProductCardResource_id' => $data['MedProductCardResource_id'],
			'MedProductCardResource_begDT' => $data['MedProductCardResource_begDT'],
			'MedProductCardResource_endDT' => empty($data['MedProductCardResource_endDT']) ? NULL : $data['MedProductCardResource_endDT']
		);

		$query = "
			select
				count(*) as cnt
			from
				passport.v_MedProductCardResource MPCR with (nolock)
			where
				Resource_id = :Resource_id and
				MedProductCardResource_id != ISNULL(:MedProductCardResource_id, 0) and
				(
					(MedProductCardResource_begDT <= :MedProductCardResource_begDT AND
					(MedProductCardResource_endDT > :MedProductCardResource_endDT OR MedProductCardResource_endDT IS NULL))
				OR
					(:MedProductCardResource_begDT BETWEEN MedProductCardResource_begDT AND MedProductCardResource_endDT)
				OR
					(MedProductCardResource_begDT > :MedProductCardResource_begDT AND :MedProductCardResource_endDT is null)
				)
		";

		//echo getDebugSQL($query, $data);
		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			$check = $result->result('array');
		} else {
			return false;
		}

		if (is_array($check) && count($check) > 0 && !empty($check[0]['cnt']) ){
			if ($check[0]['cnt'] > 0) {
				$this->rollbackTransaction();
				throw new Exception('В один период времени Ресурс может быть связан только с одним медицинским изделием');
			}
		} else if ($check === false) {
			$this->rollbackTransaction();
			throw new Exception('Не удалось проверить пересечение мед. издений');
		}

		return false;
	}

	/**
	 * Сохранение связи ресурса с медицинским изделием
	 */
	function saveMedProductCardResource($data) {

		$this->checkMedProductCardResource($data);

		$params = array(
			'MedProductCardResource_id' => $data['MedProductCardResource_id'],
			'MedProductCard_id' => $data['MedProductCard_id'],
			'Resource_id' => $data['Resource_id'],
			'MedProductCardResource_begDT' => $data['MedProductCardResource_begDT'],
			'MedProductCardResource_endDT' => empty($data['MedProductCardResource_endDT']) ? NULL : $data['MedProductCardResource_endDT'],
			'pmUser_id' => $data['pmUser_id']
		);

		if (!empty($params['MedProductCardResource_id']) && $params['MedProductCardResource_id'] > 0) {
			$procedure = 'passport.p_MedProductCardResource_upd';
		} else {
			$params['MedProductCardResource_id'] = null;
			$procedure = 'passport.p_MedProductCardResource_ins';
		}

		$query = "
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000),
				@MedProductCardResource_id bigint = :MedProductCardResource_id;

			exec {$procedure}
				@MedProductCardResource_id = @MedProductCardResource_id output,
				@MedProductCard_id = :MedProductCard_id,
				@Resource_id = :Resource_id,
				@MedProductCardResource_begDT = :MedProductCardResource_begDT,
				@MedProductCardResource_endDT = :MedProductCardResource_endDT,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @MedProductCardResource_id as MedProductCardResource_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		//echo getDebugSQL($query, $data);exit;
		$result = $this->db->query($query, $params);
		if(is_object($result)){
			$response = $result->result('array');
			return $response;
		}
		return false;

	}

	/**
	 * Удаление связи ресурса с медицинским изделием
	 */
	function deleteMedProductCardResource($data) {
		$params = array('MedProductCardResource_id' => $data['MedProductCardResource_id']);

		$query = "
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec passport.p_MedProductCardResource_del
				@MedProductCardResource_id = :MedProductCardResource_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
			select @Error_Code as Error_Code, @Error_Message as Error_Msg
		";
		$response = $this->getFirstRowFromQuery($query, $params);
		if (!$response) {
			$response = array('Error_Msg' => 'Ошибка при удалении связи ресурса с медицинским изделием');
		}

		return array($response);
	}

	/**
	 * Проверка наличия у службы связанных служб
	 */
	function checkMedServiceHasLinked($data) {
		$filter = "";
		if (!empty($data['MedServiceLinkType_Code'])) {
			$filter .= " and mslt.MedServiceLinkType_Code = :MedServiceLinkType_Code";
		}

		$query = "
			select
				count(*) as cnt,
				'' as Error_Msg
			from
				v_MedServiceLink msl (nolock)
				inner join v_MedServiceLinkType mslt (nolock) on msl.MedServiceLinkType_id = mslt.MedServiceLinkType_id
			where
				msl.MedService_lid = :MedService_id
				{$filter}
		";

		$result = $this->db->query($query, $data);
		if (is_object($result))
		{
			$resp = $result->result('array');
			if (!empty($resp[0])) {
				return $resp[0];
			}
		}
		return false;
	}

	/**
	 *  Читает список аппаратов
	 */
	function loadApparatusList($data) {

		$query = "
			SELECT
				MS.MedService_id
				,MS.MedService_Name
				,convert(varchar(10),MS.MedService_begDT,104) as MedService_begDT
				,convert(varchar(10),MS.MedService_endDT,104) as MedService_endDT
			FROM
				v_MedService MS with (NOLOCK)
			where
				MS.MedService_pid = :MedService_pid
			order by
				MS.MedService_Name
		";

		// echo getDebugSql($query, $data); die();

		$result = $this->db->query($query, $data);
		if (is_object($result))
		{
			return $result->result('array');
		}
		return false;
	}

	/**
	 *  Читает для комбобокса MedService
	 */
	function loadMedServiceList($data)
	{
		$params = array();
		$fieldsList = array();
		$filterList = array();

		if (isset($data['isMse']) && $data['isMse'] == 1 ) {
			$filterList[] = ' exists (select Lpu_id from LpuMseLink with (nolock) where Lpu_id = :Lpu_id and Lpu_bid = MS.Lpu_id and MedService_id = MS.MedService_id) ';
			$filterList[] = ' MS.MedServiceType_id = 2 ';
		}

		elseif (isset($data['Lpu_isAll']) && (!$data['Lpu_isAll']) ) {
			$params['Lpu_id'] = ( empty($data['Lpu_id']) ) ? $data['session']['lpu_id'] : $data['Lpu_id'];

			if ( array_key_exists('linkedLpuIdList', $data['session']) && empty($data['Lpu_id']) ) {
				$filterList[] = 'MS.Lpu_id in (' . implode(',', $data['session']['linkedLpuIdList']) . ')';
				$fieldsList[] = 'case when MS.Lpu_id = :Lpu_id then 1 else 2 end as sortID';
			}
			else {
				$filterList[] = 'MS.Lpu_id = :Lpu_id';
			}
		}

		if (isset($data['isHtm']) && $data['isHtm'] == 1 ) {
			$filterList[] = ' MS.MedServiceType_id = 39 ';
		}
		$this->load->library('swCache');
		$data['mode'] = (isset($data['mode']) && $data['mode']=='all')?'all':'';

		if ( $data['mode'] == 'all' && $params['Lpu_id'] > 0 ) {
			if ($resCache = $this->swcache->get("MedServiceList_".$data['Lpu_id'])) {
				return $resCache;
			}
		}

		if( !empty($data['Contragent_id']) ) {
			$this->getContragentData($data);
		}

		if (!empty($data['LpuBuilding_id']))
		{
			$filterList[] = 'MS.LpuBuilding_id = :LpuBuilding_id';
			$params['LpuBuilding_id'] = $data['LpuBuilding_id'];
		}
		if (!empty($data['LpuUnitType_id']))
		{
			$filterList[] = 'MS.LpuUnitType_id = :LpuUnitType_id';
			$params['LpuUnitType_id'] = $data['LpuUnitType_id'];
		}
		if (!empty($data['LpuUnit_id']))
		{
			$filterList[] = 'MS.LpuUnit_id = :LpuUnit_id';
			$params['LpuUnit_id'] = $data['LpuUnit_id'];
		}

		if (!empty($data['LpuSection_id']))
		{
			$filterList[] = 'MS.LpuSection_id = :LpuSection_id';
			$params['LpuSection_id'] = $data['LpuSection_id'];
		}

		if (isset($data['MedServiceTypeIsLabOrFenceStation']) && $data['MedServiceTypeIsLabOrFenceStation'] == 1 ) {//пункты лабораторий или ограда (забор).
			$filterList[] = 'MS.MedServiceType_id IN (6, 7, 71)';
		}

		if ( !empty($data['MedServiceType_id']) ) {
			$filterList[] = 'MS.MedServiceType_id  = :MedServiceType_id';
			$params['MedServiceType_id'] = $data['MedServiceType_id'];
		}

		if ( !empty($data['MedServiceType_SysNick']) ) {
			$filterList[] = 'MST.MedServiceType_SysNick  = :MedServiceType_SysNick';
			$params['MedServiceType_SysNick'] = $data['MedServiceType_SysNick'];
		}

		if ( !empty($data['MedService_id']) ) {
			if (!empty($data['ARMType']) && $data['ARMType'] == 'reglab') {
				// все подчинённые лаборатории
				$filterList[] = "MS.MedService_id IN (select MSL.MedService_lid from v_MedServiceLink MSL with (nolock) where msl.MedService_id = :MedService_id)";
			} else if (!empty($data['ARMType']) && $data['ARMType'] == 'pzm') {
				// все подчинённые лаборатории
				$filterList[] = "MS.MedService_id IN (select MSL.MedService_lid from v_MedServiceLink MSL with (nolock) where msl.MedService_id = :MedService_id)";
			} else {
				$filterList[] = 'MS.MedService_id = :MedService_id';
			}

			$params['MedService_id'] = $data['MedService_id'];
		}

		if ( !empty($data['UslugaComplex_prescid']) ) {
			// фильтрация по доступным услугам (по услуге из назначения)
			$filterList[] = 'exists (
				select top 1
					uc.UslugaComplex_id
				from
					v_UslugaComplex uc with (NOLOCK)
					inner join v_UslugaComplexMedService ucms with (NOLOCK) on ucms.UslugaComplex_id = uc.UslugaComplex_id
					outer apply (
						Select
							un.UslugaComplex_id,
							un.UslugaComplex_2004id,
							un.UslugaComplex_2011id,
							un.UslugaComplex_TFOMSid,
							un.UslugaComplex_llprofid,
							un.UslugaComplex_slprofid
						from v_UslugaComplex un with (NOLOCK)
						where un.UslugaComplex_id = :UslugaComplex_prescid
					) as ul
				where
					(ucms.MedService_id = MS.MedService_id) and (uc.UslugaComplexLevel_id in (7,8) or uc.UslugaComplex_pid is null)
					 and (
						uc.UslugaComplex_id = ul.UslugaComplex_id or
						uc.UslugaComplex_2004id = ul.UslugaComplex_2004id or
						uc.UslugaComplex_2011id = ul.UslugaComplex_2011id or
						uc.UslugaComplex_TFOMSid = ul.UslugaComplex_TFOMSid or
						uc.UslugaComplex_llprofid = ul.UslugaComplex_llprofid or
						uc.UslugaComplex_slprofid = ul.UslugaComplex_slprofid
					)
			)';
			$params['UslugaComplex_prescid'] = $data['UslugaComplex_prescid'];
		}

		if ( !empty($data['MedService_pid']) ) {
			$filterList[] = 'exists (select MedServiceLink_id from v_MedServiceLink with (nolock) where MedService_id = :MedService_pid and MedService_lid = MS.MedService_id)';
			$params['MedService_pid'] = $data['MedService_pid'];
		}

		if (!empty($data['is_Act']))
		{
			// Актуальные службы
			$filterList[] = 'MS.MedService_begDT <= dbo.tzGetDate() and (MS.MedService_endDT >= dbo.tzGetDate() or MS.MedService_endDT is null)';
		}
		
		if ( !empty($data['MedService_IsCytologic']) ) {
			//признак Цитологическое исследование
			$filterList[] = 'MS.MedService_IsCytologic  = 2';
		}

		$query = "
			SELECT
				MS.MedService_id
				,MS.MedService_Nick
				,MS.MedService_Name
				,MS.MedServiceType_id
				,MS.Org_id
				,MS.Lpu_id
				,MS.LpuBuilding_id
				,MS.LpuUnitType_id
				,MS.LpuUnit_id
				,MS.LpuSection_id
				,l.Lpu_Nick as Lpu_Name
				,convert(varchar(10),MS.MedService_begDT,104) as MedService_begDT
				,convert(varchar(10),MS.MedService_endDT,104) as MedService_endDT
				,mst.MedServiceType_SysNick
				,lut.LpuUnitType_SysNick
				,ls.LpuSectionProfile_id
				,ls.LpuSection_Name
				,ms.MedService_IsExternal
				,ms.MedService_IsShowDiag
				,MS.MedService_IsCytologic
				,ms.MedService_IsFileIntegration
				,case
					when MS.MedService_endDT is null or MS.MedService_endDT > dbo.tzGetDate() then 0 else 1
				end MedService_IsClosed
				,RecordQueue_id
				" . (count($fieldsList) > 0 ? ',' . implode(',', $fieldsList) : '' ) . "
			FROM
				v_MedService MS with (NOLOCK)
				inner join v_Lpu l with (NOLOCK) on l.Lpu_id = MS.Lpu_id
				left join v_MedServiceType mst with (nolock) on ms.MedServiceType_id = mst.MedServiceType_id
				left join v_LpuUnit lu with (nolock) on ms.LpuUnit_id = lu.LpuUnit_id
				left join v_LpuUnitType lut with (nolock) on isnull(ms.LpuUnitType_id,lu.LpuUnitType_id) = lut.LpuUnitType_id
				left join v_LpuSection ls with (nolock) on ms.LpuSection_id = ls.LpuSection_id
			" . (count($filterList) > 0 ? 'where ' . implode(' and ', $filterList) : '') . "
			order by
				MS.MedService_Name
		";

		//echo getDebugSql($query, $data); die();

		$result = $this->db->query($query, $data);
		if (is_object($result))
		{
			$response = $result->result('array');
			if ( $data['mode'] == 'all' && $data['Lpu_id'] > 0 ) {
				$this->swcache->set("MedServiceList_".$data['Lpu_id'], $response);
			}
			return $response;
		}
		return false;
	}

	/**
	 *	Определяет параметры контрагента
	 *	Ничего не возвращает, так как этого не требуется (принимает параметры по ссылке и просто дополняет массив)
	 */
	function getContragentData(&$data) {
		$query = "
			select top 1
				Lpu_id
				,Org_id
				,LpuSection_id
			from
				v_Contragent with(nolock)
			where
				Contragent_id = :Contragent_id
		";
		$result = $this->db->query($query, array(
			'Contragent_id' => $data['Contragent_id']
		));
		if ( is_object($result) ) {
			$result = $result->result('array');
			if( isset($result[0]) ) {
				foreach( $result[0] as $k=>$row ) {
					if( !empty($row) ) {
						$data[$k] = $row;
					}
				}
			}
		}
	}
	/**
	 * Возвращает состав услуги для настройки проб и биоматериала
	 * 08.07.2013 Сделал использование состава услуги (UslugaComplexComposition)
	 * 08.07.2013 По схеме работы с ЛИС даже простая услуга включает в себя состав (саму себя)
	 * 05.09.2013 Сделал использование состава из UslugaComplexMedService по UslugaComplexMedService_pid (refs #23929)
	 * todo: Этот момент в дальшейшем стоит уточнить
	 */
	function loadUslugaComplexMedServiceGridChild($data){
		// предварительно проверяем является ли запрашиваемая услуга простой (критерий проверки: услуга не содержит в себе других услуг)
		// и если является, то отображаем ее
		$query = "
			SELECT
				count(*) as records_count
			FROM
				v_UslugaComplexMedService s with(nolock)
				inner join v_UslugaComplexMedService ucm with(nolock) on s.UslugaComplexMedService_id = ucm.UslugaComplexMedService_pid
				inner JOIN v_UslugaComplex u with(nolock) on u.UslugaComplex_id = ucm.UslugaComplex_id
			WHERE
				s.MedService_id = :MedService_id
				AND s.UslugaComplex_id = :UslugaComplex_pid
		";

		$records_count = 0;
		try {
			$r = $this->db->query($query, $data);
			if (is_object($r)) {
				$records = $r->result('array');
				if (count($records)>0) {
					$records_count = $records[0]['records_count'];
				}
			}
		} catch (Exception $e) {
			// ничего не произошло :)
			$result = array(
				0 => array(
					'Error_Code' => null,
					'Error_Msg' => 'Ошибка при проверке услуги: '.str_replace(chr(13),' ', str_replace(chr(10),'<br> ', $e->getCode().' '.$e->getMessage()))
				)
			);
			return $result;
		}
		$join = "";
		$select = "";
		$filter = "";
		if ($records_count==0) { // если услуга простая, то ее и выводим
            $join .= "
			INNER join v_UslugaComplexMedService ucm with(nolock) on s.UslugaComplexMedService_id = ucm.UslugaComplexMedService_pid ";
			$join .= "
			INNER JOIN v_UslugaComplex u with(nolock) on u.UslugaComplex_id = s.UslugaComplex_id ";
			$join .= "
			LEFT JOIN dbo.v_RefSample r with(nolock) on r.RefSample_id = s.RefSample_id ";
			$select .= "s.UslugaComplexMedService_id as \"UslugaComplexMedService_id\",";
			$select .= "coalesce(LpuUslName.UslugaComplex_Name, s.UslugaComplex_Name, u.UslugaComplex_Name) as \"UslugaComplex_Name\",";
			$filter .= " and s.UslugaComplexMedService_pid is null";
		} else { // иначе выводим состав комплексной услуги
			$join .= "
			INNER join v_UslugaComplexMedService ucm with(nolock) on s.UslugaComplexMedService_id = ucm.UslugaComplexMedService_pid ";
			$join .= "
			INNER JOIN v_UslugaComplex u with(nolock) on u.UslugaComplex_id = ucm.UslugaComplex_id ";
			$join .= "
			LEFT JOIN dbo.v_RefSample r with(nolock) on r.RefSample_id = ucm.RefSample_id ";
			$join .= "
				cross apply(
					select top 1
						at_child.AnalyzerTest_id
					from
						lis.v_AnalyzerTest at_child with(nolock)
						inner join lis.v_AnalyzerTest at with(nolock) on at.AnalyzerTest_id = at_child.AnalyzerTest_pid
						inner join lis.v_Analyzer a with(nolock) on a.Analyzer_id = at.Analyzer_id
					where
						at_child.UslugaComplexMedService_id = ucm.UslugaComplexMedService_id
						and at.UslugaComplexMedService_id = ucm.UslugaComplexMedService_pid
						and coalesce(at_child.AnalyzerTest_IsNotActive, 1) = 1
						and coalesce(at.AnalyzerTest_IsNotActive, 1) = 1
						and coalesce(a.Analyzer_IsNotActive, 1) = 1
						and (at_child.AnalyzerTest_endDT >= dbo.tzGetDate() or at_child.AnalyzerTest_endDT is null)
				) ATEST -- фильтрация услуг по активности тестов связанных с ними
			";
			$select .= "ucm.UslugaComplexMedService_id as \"UslugaComplexMedService_id\",";
			$select .= "coalesce(LpuUslName.UslugaComplex_Name, ucm.UslugaComplex_Name, u.UslugaComplex_Name, s.UslugaComplex_Name) as \"UslugaComplex_Name\",";
		}

		$join .= "
			outer apply(
				select top 1
					UCm.UslugaComplex_Name
				from
					v_UslugaComplex UCm with(nolock)
					left join v_UslugaCategory UCat with(nolock) on UCm.UslugaCategory_id = UCat.UslugaCategory_id
				where
					UCm.UslugaComplex_2011id = u.UslugaComplex_id
					and Lpu_id = :Lpu_id
					and UCat.UslugaCategory_SysNick = 'lpu'
			) LpuUslName";

		$query = "
		SELECT
			{$select}
			r.RefSample_Name as \"RefSample_Name\",
			r.RefSample_id as \"RefSample_id\",
			u.UslugaComplex_Code as \"UslugaComplex_Code\",
			u.UslugaComplex_id as \"UslugaComplex_id\",
			m.RefMaterial_Name as \"RefMaterial_Name\",
			m.RefMaterial_id as \"RefMaterial_id\",
			u.UslugaComplex_pid as \"UslugaComplex_pid\",
			CT.ContainerType_id as \"ContainerType_id\",
			CT.ContainerType_Name as \"ContainerType_Name\",
			case when ucm.UslugaComplexMedService_IsSeparateSample = 2 then 'true' else 'false' end as \"UslugaComplexMedService_IsSeparateSample\"
		FROM
			v_UslugaComplexMedService s with(nolock)
			{$join}
			LEFT JOIN dbo.v_RefMaterial m with(nolock) on r.RefMaterial_id = m.RefMaterial_id
			LEFT JOIN dbo.ContainerType CT with(nolock) on r.ContainerType_id = CT.ContainerType_id
			--left join v_UslugaComplex UC2011 with(nolock) with (NOLOCK) on u.UslugaComplex_2011id = UC2011.UslugaComplex_id
		WHERE
			s.MedService_id = :MedService_id and
			s.UslugaComplex_id = :UslugaComplex_pid
			{$filter}
		";
		//echo getDebugSql($query, $data);die();
		$result = $this->db->query($query, $data);
		if (is_object($result))
		{
			return $result->result('array');
		}
		return false;
    }

    /**
	 *  Читает для грида UslugaComplexMedService
	 */
	function loadUslugaComplexMedServiceGrid($data)
	{
		$filters = 'UCMS.MedService_id = :MedService_id';
		$from = 'v_UslugaComplexMedService UCMS with (NOLOCK)
				left join v_UslugaComplex UC with (NOLOCK) on UCMS.UslugaComplex_id = UC.UslugaComplex_id
				cross apply(
					select top 1
						at.AnalyzerTest_id
					from
						lis.v_AnalyzerTest at (nolock)
						inner join lis.v_Analyzer a (nolock) on a.Analyzer_id = at.Analyzer_id
					where
						at.UslugaComplexMedService_id = UCMS.UslugaComplexMedService_id
						and ISNULL(at.AnalyzerTest_IsNotActive, 1) = 1
						and ISNULL(a.Analyzer_IsNotActive, 1) = 1
						and (at.AnalyzerTest_endDT >= dbo.tzGetDate() or at.AnalyzerTest_endDT is null)
				) ATEST -- фильтрация услуг по активности тестов связанных с ними
		';
		if (!empty($data['UslugaComplexMedService_id']) && !empty($data['UslugaComplex_pid']))
		{
			//return array();
			//$filters .= ' and UCMS.UslugaComplexMedService_id = :UslugaComplexMedService_id';
			$filters = 'UC.UslugaComplex_pid = :UslugaComplex_pid';
			$from = 'v_UslugaComplex UC with (NOLOCK)
					left join v_UslugaComplexMedService UCMS with (NOLOCK) on UCMS.UslugaComplexMedService_id = :UslugaComplexMedService_id';
		}

		$filters .= " AND UCMS.UslugaComplexMedService_pid IS NULL"; // услуги только верхнего уровня

		if ($data['Urgency_id']==1) {
			$filters .= ' and UCMS.UslugaComplexMedService_begDT is not null and (UCMS.UslugaComplexMedService_endDT is null or UCMS.UslugaComplexMedService_endDT > dbo.tzGetDate())';
		}
		elseif ($data['Urgency_id']==2) {
			$filters .= ' and UCMS.UslugaComplexMedService_begDT is not null and UCMS.UslugaComplexMedService_endDT < dbo.tzGetDate()';
		}

		$query = "
			SELECT
				UCMS.UslugaComplexMedService_id,
				UCMS.MedService_id,
				UC.UslugaComplex_id,
				--IsNull(UC2011.UslugaComplex_Code, ''+UC.UslugaComplex_Code+'') as UslugaComplex_Code,
				UC.UslugaComplex_Code,
				ISNULL(UCMS.UslugaComplex_Name, UC.UslugaComplex_Name) as UslugaComplex_Name,
				case when (UCMS.UslugaComplexMedService_endDT<=getdate()) then 2 else 1 end as closed,
				convert(varchar(10),UCMS.UslugaComplexMedService_begDT,104) as UslugaComplexMedService_begDT,
				convert(varchar(10),UCMS.UslugaComplexMedService_endDT,104) as UslugaComplexMedService_endDT,
				case when UCMS.UslugaComplexMedService_IsSeparateSample = 2 then 'true' else 'false' end as UslugaComplexMedService_IsSeparateSample,
				r.RefSample_Name,
				r.RefSample_id,
                m.RefMaterial_Name,
                m.RefMaterial_id,
                CT.ContainerType_id,
                CT.ContainerType_Name
			FROM
				{$from}
				LEFT JOIN v_RefSample r with(nolock) on r.RefSample_id = UCMS.RefSample_id 
				LEFT JOIN v_RefMaterial m with(nolock) on r.RefMaterial_id = m.RefMaterial_id
				LEFT JOIN ContainerType CT with(nolock) on r.ContainerType_id = CT.ContainerType_id
				--left join v_UslugaComplex UC2011 with (NOLOCK) on UC.UslugaComplex_2011id = UC2011.UslugaComplex_id
			where
				{$filters}
			order by
				UC.UslugaComplex_Name
		";
		//echo getDebugSql($query, $data);exit;
		$result = $this->db->query($query, $data);
		if (is_object($result))
		{
			return $result->result('array');
		}
		return false;
	}

	/**
	 * createMedServiceRefSample
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function createMedServiceRefSample($data){
        $RefSample_id = $this->createRefSample($data);
        if ($this->isSuccessful($RefSample_id) ) {
            $Usluga_ids = json_decode($data['Usluga_ids']);
            foreach ($Usluga_ids as $UslugaComplexMedService_id) {
                if (!$this->bindUslugaComplexMedServiceToRefSample($data, $UslugaComplexMedService_id, $RefSample_id)){
                    throw new Exception("Ошибка при попытке объединить услуги в пробу (UslugaComplexMedService_id: $UslugaComplexMedService_id, RefSample_id: $RefSample_id)");
                }
            }
			return $RefSample_id;
        } else {
        	throw new Exception($RefSample_id['Error_Msg'], 500);
		}
    }

    /**
     * @param $UslugaComplexMedService_id
     * @param $RefSample_id
     * @param $pmUser_id
     * @return bool
     */
    function bindUslugaComplexMedServiceToRefSample($data, $UslugaComplexMedService_id, $RefSample_id){
		$this->load->model('UslugaComplexMedService_model', 'UslugaComplexMedService_model');
		$resp = $this->UslugaComplexMedService_model->doSaveUslugaComplexMedService(array(
			'scenario' => self::SCENARIO_DO_SAVE,
			'UslugaComplexMedService_id' => $UslugaComplexMedService_id,
			'UslugaComplexMedService_IsSeparateSample' => ($data['UslugaComplexMedService_IsSeparateSample']=='on'? 2: 1),
			'RefSample_id' => $RefSample_id['RefSample_id'],
			'session' => $data['session'],
			'pmUser_id' => $data['pmUser_id']
		));

		if (!empty($resp['UslugaComplexMedService_id'])) {
			return true;
		} else {
			return false;
		}
    }

    /**
     * @param $RefSample_Name
     * @param $RefMaterial_id
     * @param $pmUser_id
	 * @return array
     */
    function createRefSample($data){
		$query = "
            declare
                @RefSample_id bigint,
                @Error_Code int,
                @Error_Message varchar(4000);
            EXEC dbo.p_RefSample_ins
                @RefSample_id = @RefSample_id output,
                @RefMaterial_id = :RefMaterial_id,
                @ContainerType_id = :ContainerType_id,
                @RefSample_Name = :RefSample_Name,
                @pmUser_id = :pmUser_id,
                @Error_Code = @Error_Code,
                @Error_Message = @Error_Message;
            SELECT @RefSample_id as RefSample_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
        ";
        $result = $this->queryResult($query, array(
            'RefSample_Name' => $data["RefSample_Name"],
            'RefMaterial_id' => $data["RefMaterial_id"],
            'ContainerType_id' => $data["ContainerType_id"],
            'pmUser_id' => $data["pmUser_id"]
        ));
        return $result[0];
    }

	/**
	 *
	 * @param type $data
	 * @return type
	 */
	function getResourceData($data){
		$query = "
			SELECT
				Res.Resource_id,
				Res.Resource_Name,
				Res.ResourceType_id,
				Res.MedService_id,
				10 as Resource_Time,
				convert(varchar(10),Res.Resource_begDT,120) as Resource_begDT,
				convert(varchar(10),Res.Resource_endDT,120) as Resource_endDT
			FROM
				v_Resource Res with (NOLOCK)
			where
				Res.Resource_id = :Resource_id
		";

		$result = $this->db->query($query, $data);
		if (is_object($result))
		{
			$result=$result->result('array');
			return $result;
		}
		return false;
	}

	/**
	 * Проверка наличия связи службы с ЭО
	 */
	function checkEQMedServiceLink($data) {
		$query = "
			SELECT top 1 *
			FROM
				MedServiceElectronicQueue
			WHERE
				MedService_id = :MedService_id
		";
		$queryParams = array(
			'MedService_id' => $data['session']['CurMedService_id']
		);
		$response = $this->getFirstResultFromQuery($query, $queryParams);
		return !empty($response);
	}

	/**
	 *
	 * @param type $data
	 * @return type
	 */
	function getResourceLastTime($data){
		$query = "
			select top 1
convert(varchar(10),TimetableResource_begTime,120) as TimetableResource_begTime
from v_TimetableResource_lite s with(nolock) where Resource_id = :Resource_id
order by s.TimetableResource_begTime desc
		";

		$result = $this->db->query($query, $data);
		if (is_object($result))
		{
			$result=$result->result('array');
			if(count($result)>0){
				return $result['TimetableResource_begTime'];
			}else{
				return Date('Y-m-d');
			}
		}
		return false;

	}
	/**
	 *
	 * @param type $data
	 * @return type
	 */
	/*function saveMedServiceResource($data){

		if(!isset($data['Resource_id'])){
			return false;
		}
		$res = $this->getResourceData($data);
		$resource_data = $res[0];
		$resource_data['pmUser_id'] = $data['pmUser_id'];
		$response = array();
		switch(true){
			case isset($data['Resource_Time']):
				$resource_data['Resource_Time'] = $data['Resource_Time'];
				break;
			case(isset($data['isActive'])):
				if($data['isActive']){

				}else{
					$resource_data['Resource_endDT'] = $this->getResourceLastTime($data);
				}
				break;
			default:break;
		}
		$response = $this->saveResource($resource_data);
		return $response;
	}
	*/
	/**
	 * comment
	 */
	function saveResource($data){
		$this->beginTransaction();
		$proc = 'p_Resource_ins';
		if(isset($data['Resource_id'])){
			$proc = 'p_Resource_upd';
		}
		$query = "
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000),
				@Resource_id bigint = :Resource_id;

			exec {$proc}
				@Resource_id = @Resource_id output,
				@Resource_Name = :Resource_Name,
				@ResourceType_id = :ResourceType_id,
				@MedService_id = :MedService_id,
				@Resource_begDT = :Resource_begDT,
				@Resource_endDT = :Resource_endDT,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
				select @Resource_id as Resource_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		//echo getDebugSQL($query, $data);exit;
		$result = $this->db->query($query, $data);
		if(is_object($result)){
			$response = $result->result('array');
			$data['Resource_id'] = $response[0]['Resource_id'];
			// сохранение связей с медизделиями
			//if ($data['ResourceType_id']==3) { // Если аппарат, для остальных нет смысла сохранять upd: зато есть смысл их удалить
				$this->saveMedProductCardResourceGrid($data);
			//}
			// сохраняем связи с услугами
			$this->saveUSRData($data);

			$this->commitTransaction();
			return $response;
		}
		return false;
	}
	/**
	 *
	 * @param type $data
	 * @return type
	 */
	/*function loadMedServiceResourceGrid($data) {
		$query = "
			declare @curTime date = dbo.tzGetDate()
			SELECT
				Res.Resource_id,
				case when Res.Resource_begDT is not null and ISNULL(Res.Resource_endDT,'2030-01-01')>@curTime then 'true'
				else 'false' end as isActive,
				Res.Resource_Name,
				10 as Resource_Time,
				convert(varchar(10),Res.Resource_begDT,104) as Resource_begDT,
				convert(varchar(10),Res.Resource_endDT,104) as Resource_endDT
			FROM
				v_Resource Res with (NOLOCK)
				left join v_UslugaComplexResource UCR with(nolock) on Res.Resource_id=UCR.Resource_id
			where
				Res.MedService_id=:MedService_id

		";

		$result = $this->db->query($query, $data);
		if (is_object($result))
		{
			return $result->result('array');
		}
		return false;
	}*/
	/**
	*  Читает для грида MedServiceMedPersonal
	*/
	function loadMedServiceMedPersonalGrid($data)
	{
		$query = "
			SELECT
				MSMP.MedServiceMedPersonal_id
				,MSMP.MedService_id
				,MSMP.MedPersonal_id
				,MSMP.Server_id
				,MP.Person_Fio as MedPersonal_Name
				,case when msmp.MedServiceMedPersonal_IsTransfer = 2 then 'true' else 'false' end as MedServiceMedPersonal_IsTransfer
				,convert(varchar(10),MSMP.MedServiceMedPersonal_begDT,104) as MedServiceMedPersonal_begDT
				,convert(varchar(10),MSMP.MedServiceMedPersonal_endDT,104) as MedServiceMedPersonal_endDT
			FROM
				v_MedServiceMedPersonal MSMP with (NOLOCK)
				left join v_MedPersonal MP with (NOLOCK) on MSMP.MedPersonal_id = MP.MedPersonal_id
			where
				MSMP.MedService_id = :MedService_id
			order by
				MedPersonal_Name
		";

		$result = $this->db->query($query, $data);
		if (is_object($result))
		{
			return $result->result('array');
		}
		return false;
	}

	/**
	 * проверяет есть ли служба указанного типа в ЛПУ
	 */
	function checkMedServiceExistInLpu($data) {
		if(empty($data['MedServiceType_id'])) { $data['MedServiceType_id'] = NULL; }
		if(empty($data['Lpu_id'])) { $data['Lpu_id'] = NULL; }

		$query = "
			SELECT
				TOP 1 MS.MedService_id
			FROM
				v_MedService MS with (NOLOCK)
				left join v_MedServiceType MST with (NOLOCK) on MS.MedServiceType_id = MST.MedServiceType_id
			where
				MS.Lpu_id = :Lpu_id and
				MST.MedServiceType_id = :MedServiceType_id
			order by
				MedService_Name
		";

		$result = $this->db->query($query, $data);
		if (is_object($result))
		{
			$response = $result->result('array');
			if (count($response) > 0) {
				return true;
			}
		}
		return false;
	}

	/**
	 *  Читает для грида MedService
	 */
	function loadGrid($data)
	{
		$params = array(
			'Lpu_id' => $data['Lpu_id']
		);
		$level='lpu';
		$filters = '';
		if (!empty($data['LpuBuilding_id']))
		{
			$level='lpubuilding';
			$filters .= ' and MS.LpuBuilding_id = :LpuBuilding_id';
			$params['LpuBuilding_id'] = $data['LpuBuilding_id'];
		}
		if (!empty($data['LpuUnitType_id']))
		{
			$level='lpuunittype';
			$filters .= ' and MS.LpuUnitType_id = :LpuUnitType_id';
			$params['LpuUnitType_id'] = $data['LpuUnitType_id'];
		}
		if (!empty($data['LpuUnit_id']))
		{
			$level='lpuunit';
			$filters .= ' and MS.LpuUnit_id = :LpuUnit_id';
			$params['LpuUnit_id'] = $data['LpuUnit_id'];
		}
		if (!empty($data['LpuSection_id']))
		{
			$level='lpusection';
			$filters .= ' and MS.LpuSection_id = :LpuSection_id';
			$params['LpuSection_id'] = $data['LpuSection_id'];
		}
		if (empty($data['is_All']))
		{
			// только Службы Выбранного уровня
			switch($level){
				case 'lpu';
					$filters = ' and MS.LpuBuilding_id is null and ms.LpuUnitType_id is null and MS.LpuUnit_id is null and MS.LpuSection_id is null';
					break;
				case 'lpubuilding';
					$filters = ' and MS.LpuBuilding_id = :LpuBuilding_id and ms.LpuUnitType_id is null and MS.LpuUnit_id is null and MS.LpuSection_id is null';
					break;
				case 'lpuunittype';
					$filters = ' and MS.LpuBuilding_id = :LpuBuilding_id and MS.LpuUnitType_id = :LpuUnitType_id and MS.LpuUnit_id is null and MS.LpuSection_id is null';
					break;
				case 'lpuunit';
					$filters = ' and MS.LpuUnit_id = :LpuUnit_id and MS.LpuSection_id is null';
					break;
				case 'lpusection';
					$filters = ' and MS.LpuSection_id = :LpuSection_id';
					break;
			}
		}

		/*if (!empty($data['is_Act']) && $data['is_Act'] == 1)
		{
			// Актуальные службы
			$filters .= ' and MS.MedService_begDT <= dbo.tzGetDate() and (MS.MedService_endDT >= dbo.tzGetDate() or MS.MedService_endDT is null)';
		}*/

		if (!empty($data['isClose']) && $data['isClose'] == 1) {
			$filters .= " and (MS.MedService_endDT is null or ms.MedService_endDT > dbo.tzGetDate())";
		} elseif (!empty($data['isClose']) && $data['isClose'] == 2) {
			$filters .= " and MS.MedService_endDT <= dbo.tzGetDate()";
		}

		$query = "
			SELECT
				MS.MedService_id
				--,MS.MedService_Name + isnull(' ('+MedService_Nick+')','') as MedService_Name
				,MS.MedService_Name
				,MS.MedService_Nick
				,MST.MedServiceType_Name
				,MST.MedServiceType_SysNick
				,MS.Lpu_id
				,MS.LpuBuilding_id
				,MS.LpuUnitType_id
				,MS.LpuUnit_id
				,MS.LpuSection_id
				,convert(varchar(10),MS.MedService_begDT,104) as MedService_begDT
				,convert(varchar(10),MS.MedService_endDT,104) as MedService_endDT
			FROM
				v_MedService MS with (NOLOCK)
				left join v_MedServiceType MST with (NOLOCK) on MS.MedServiceType_id = MST.MedServiceType_id
			where
				MS.Lpu_id = :Lpu_id
				{$filters}
			order by
				MedService_Name
		";
		/*
		echo getDebugSql($query, $params);
		exit;
		*/
		$result = $this->db->query($query, $params);
		if (is_object($result))
		{
			return $result->result('array');
		}
		return false;
	}

	/**
	 *  Читает одну строку для формы редактирования MedService
	 */
	public function loadEditForm( $data ) {
		$query = "
			Select top 1
				ms.MedService_id,
				ms.MedService_Name,
				ms.MedService_Nick,
				ms.MedService_Code,
				ms.MedServiceType_id,
				convert(varchar(10),ms.MedService_begDT,104) as MedService_begDT,
				convert(varchar(10),ms.MedService_endDT,104) as MedService_endDT,
				ms.LpuBuilding_id,
				lb.LpuBuildingType_id,
				ms.LpuSection_id,
				ms.LpuUnitType_id,
				ms.LpuUnit_id,
				ms.Lpu_id,
				ms.Org_id,
				ms.OrgStruct_id,
				ms.MedService_WialonLogin,
				ms.MedService_WialonPasswd,
				ms.MedService_WialonNick,
				ms.MedService_WialonURL,
				ms.MedService_WialonAuthURL,
				ms.MedService_WialonToken,
				ms.MedService_WialonPort,
				case when ms.MedService_IsAutoQueryRes = 2 then 1 else 0 end as MedService_IsAutoQueryRes,
				ms.MedService_FreqQuery,
				ms.ApiServiceType_id,
				ms.RecordQueue_id,
				ms.MseOffice_id,
				ms.LpuSectionAge_id,
				case when ms.MedService_IsThisLPU = 2 then 1 else 0 end as MedService_IsThisLPU,
				case when ms.MedService_IsExternal = 2 then 1 else 0 end as MedService_IsExternal,
				case when ms.MedService_IsShowDiag = 2 then 1 else 0 end as MedService_IsShowDiag,
				case when ms.MedService_IsQualityTestApprove = 2 then 1 else 0 end as MedService_IsQualityTestApprove,
				case when ms.MedService_IsFileIntegration = 2 then 1 else 0 end as MedService_IsFileIntegration,
				case when ms.MedService_IsLocalCMP = 2 then 1 else 0 end as MedService_IsLocalCMP,
				case when ms.MedService_IsSendMbu = 2 then 1 else 0 end as MedService_IsSendMbu,
				ms.MedService_LocalCMPPath,
				a.Address_id,
				a.Address_Zip,
				a.KLCountry_id,
				a.KLRGN_id,
				a.KLSubRGN_id,
				a.KLCity_id,
				a.KLTown_id,
				a.KLStreet_id,
				a.Address_House,
				a.Address_Corpus,
				a.Address_Flat,
				a.Address_Address,
				a.Address_Address as Address_AddressText,
				lp.LpuEquipmentPacs_id,
				lp.PACS_name
			from
				v_MedService ms with (NOLOCK)
				left join v_Address a with(nolock) on a.Address_id = ms.Address_id
				left join v_LpuBuilding lb with(nolock) on lb.LpuBuilding_id = ms.LpuBuilding_id
				left join v_LpuEquipmentPacs lp with(nolock) on lp.LpuEquipmentPacs_id = ms.LpuEquipmentPacs_id
			where
				ms.MedService_id = :MedService_id
		";
		$result = $this->db->query( $query, $data );
		if ( is_object( $result ) ) {
			return $result->result( 'array' );
		} else {
			return false;
		}
	}

	/**
	 *  Читает одну строку для формы редактирования аппарата
	 */
	function loadApparatusEditForm($data)
	{
		$query = "
			Select top 1
				ms.MedService_id,
				ms.MedService_pid,
				ms.MedService_Name,
				ms.MedService_Nick,
				ms.MedServiceType_id,
				convert(varchar(10),ms.MedService_begDT,104) as MedService_begDT,
				convert(varchar(10),ms.MedService_endDT,104) as MedService_endDT
			from
				v_MedService ms with (NOLOCK)
			where
				ms.MedService_id = :MedService_id
		";
		$result = $this->db->query($query, $data);
		if ( is_object($result) )
		{
			return $result->result('array');
		}
		else
		{
			return false;
		}
	}

	/**
	 * Сохранение адреса службы
	 */
	function saveAddress($data) {
		$params = array(
			'Address_id' => !empty($data['Address_id'])?$data['Address_id']:null,
			'Address_Zip' => !empty($data['Address_Zip'])?$data['Address_Zip']:null,
			'KLCountry_id' => !empty($data['KLCountry_id'])?$data['KLCountry_id']:null,
			'KLRGN_id' => !empty($data['KLRGN_id'])?$data['KLRGN_id']:null,
			'KLSubRGN_id' => !empty($data['KLSubRGN_id'])?$data['KLSubRGN_id']:null,
			'KLCity_id' => !empty($data['KLCity_id'])?$data['KLCity_id']:null,
			'KLTown_id' => !empty($data['KLTown_id'])?$data['KLTown_id']:null,
			'KLStreet_id' => !empty($data['KLStreet_id'])?$data['KLStreet_id']:null,
			'Address_House' => !empty($data['Address_House'])?$data['Address_House']:null,
			'Address_Corpus' => !empty($data['Address_Corpus'])?$data['Address_Corpus']:null,
			'Address_Flat' => !empty($data['Address_Flat'])?$data['Address_Flat']:null,
			'Address_Address' => !empty($data['Address_Address'])?$data['Address_Address']:null,
			'Server_id' => $data['Server_id'],
			'pmUser_id' => $data['pmUser_id'],
		);
		if (empty($params['Address_id'])) {
			$procedure = 'p_Address_ins';
		} else {
			$procedure = 'p_Address_upd';
		}
		$query = "
			declare
				@Error_Message varchar(4000),
				@Error_Code bigint,
				@Address_id bigint = :Address_id;
			exec {$procedure}
				@Address_id = @Address_id output,
				@Address_Zip = :Address_Zip,
				@KLCountry_id = :KLCountry_id,
				@KLRGN_id = :KLRGN_id,
				@KLSubRGN_id = :KLSubRGN_id,
				@KLCity_id = :KLCity_id,
				@KLTown_id = :KLTown_id,
				@KLStreet_id = :KLStreet_id,
				@Address_House = :Address_House,
				@Address_Corpus = :Address_Corpus,
				@Address_Flat = :Address_Flat,
				@Address_Address = :Address_Address,
				@Server_id = :Server_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @Address_id as Address_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";
		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при сохранении адреса');
		}
		return $resp;
	}

	/**
	 *  Записывает одну строку MedService
	 */
	public function saveRecord( $data ) {
		if ( $data[ 'MedService_id' ] > 0 ) {
			$proc = 'p_MedService_upd';
			$data[ 'copyFromLpuSection' ] = 0;
		} else {
			$proc = 'p_MedService_ins';
			$data[ 'MedService_id' ] = null;
		}

		$MedServiceType = $this->getFirstRowFromQuery("SELECT * FROM v_MedServiceType with(nolock) WHERE MedServiceType_id = :MedServiceType_id", $data);
		$MedServiceType_Code = $MedServiceType['MedServiceType_Code'];
		$MedServiceType_SysNick = $MedServiceType['MedServiceType_SysNick'];

		// для внешней службы эти поля можно использовать
		if ( empty($data['MedService_IsExternal']) && $MedServiceType_Code != 3 ) {
			$data['MedService_WialonNick'] = null;
			$data['MedService_WialonURL'] = null;
			$data['MedService_WialonPort'] = null;
		}
		// Разрешаем сохранять данные авторизации в Виалоне только для СМП и для внешней службы
		if ( empty($data['MedService_IsExternal']) && !in_array($MedServiceType_Code, array(18,19,53))) {
			$data['MedService_WialonLogin'] = null;
			$data['MedService_WialonPasswd'] = null;
		}
		if ( !empty( $data[ 'Lpu_id' ] ) ) {
			$MedServiceLevelType_id = 4;

			if ( !empty( $data[ 'LpuSection_id' ] ) ) {
				$MedServiceLevelType_id = 1;
			} else if ( !empty( $data[ 'LpuUnit_id' ] ) ) {
				$MedServiceLevelType_id = 2;
			} else if ( !empty( $data[ 'LpuBuilding_id' ] ) ) {
				$MedServiceLevelType_id = 3;
			}

			if ( !empty( $MedServiceLevelType_id ) ) {
				$medServiceLevelTypeArray = $this->getAllowedMedServiceLevelTypeArray( $data[ 'MedServiceType_id' ] );

				if ( $medServiceLevelTypeArray === false ) {
					return array( array( 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных (получение списка доступных уровней для указанного типа службы)' ) );
				} else if ( !in_array( $MedServiceLevelType_id, $medServiceLevelTypeArray ) ) {
					return array( array( 'Error_Msg' => 'Указан недопустимый тип службы для выбранного уровня МО' ) );
				}
			}
		}

		// При сохранении службы типа «Оперблок» параметр службы «Запись в очередь» принудительно устанавливается в значение «Разрешить»
		if ( $data['MedServiceType_id'] == 57 ) {
			$data['RecordQueue_id'] = 2;
		}

		/*
		  // при вставке службы предварительно надо проверить, чтобы одинаковых кодов службы не было (для служб лабораторного типа)
		  if (empty($data['MedService_id']) && $MedServiceType_SysNick == 'lab') {
		  $ccodes = $this->checkMedServiceCode($data);
		  if ($ccodes>0) { // если уже есть служба с таким кодом, надо сгенерить новый код
		  $mscodes = $this->getMedServiceCode();
		  if (count($mscodes)>0) {
		  $data['MedService_Code'] = $mscodes[0]['MedService_Code']; // новый код
		  }
		  }
		  } */

		$address_id = null;
		if (isset($data['Address_Address'])) {
			$resp = $this->saveAddress($data);
			if (!$this->isSuccessful($resp)) {
				return $resp;
			}
			$address_id = $resp[0]['Address_id'];
		}

		$params = array(
			'MedService_id' => $data[ 'MedService_id' ],
			'MedService_Name' => $data[ 'MedService_Name' ],
			'MedService_Nick' => $data[ 'MedService_Nick' ],
			'MedService_Code' => $data[ 'MedService_Code' ],
			'MedServiceType_id' => $data[ 'MedServiceType_id' ],
			'MedService_begDT' => $data[ 'MedService_begDT' ],
			'MedService_endDT' => $data[ 'MedService_endDT' ],
			'LpuBuilding_id' => $data[ 'LpuBuilding_id' ],
			'LpuUnitType_id' => $data[ 'LpuUnitType_id' ],
			'LpuEquipmentPacs_id' => $data[ 'LpuEquipmentPacs_id' ], //#146135
			'LpuSection_id' => $data[ 'LpuSection_id' ],
			'LpuUnit_id' => $data[ 'LpuUnit_id' ],
			'Lpu_id' => $data[ 'Lpu_id' ],
			'Org_id' => $data[ 'Org_id' ],
			'RecordQueue_id' =>$data['RecordQueue_id'],
			'OrgStruct_id' => $data[ 'OrgStruct_id' ],
			'Server_id' => $data[ 'Server_id' ],
			'pmUser_id' => $data[ 'pmUser_id' ],
			'MedService_WialonLogin' => $data['MedService_WialonLogin'],
			'MedService_WialonPasswd' => $data['MedService_WialonPasswd'],
			'MedService_WialonNick' => $data['MedService_WialonNick'],
			'MedService_WialonURL' => $data['MedService_WialonURL'],
			'MedService_WialonAuthURL' => $data['MedService_WialonAuthURL'],
			'MedService_WialonToken' => $data['MedService_WialonToken'],
			'MedService_WialonPort' => $data['MedService_WialonPort'],
			'MedService_IsAutoQueryRes' => $data['MedService_IsAutoQueryRes'],
			'MedService_FreqQuery' => $data['MedService_FreqQuery'],
			'ApiServiceType_id' => $data['ApiServiceType_id'],
			'MedService_LocalCMPPath' => $data['MedService_LocalCMPPath'],
			'MedService_IsExternal' => 1,
			'MedService_IsShowDiag' => 1,
			'MedService_IsQualityTestApprove' => 1,
			'MedService_IsFileIntegration' => 1,
			'MedService_IsThisLPU'=>1,
			'MedService_IsLocalCMP'=>1,
			'Address_id' => $address_id,
			'MseOffice_id' => $data['MseOffice_id'],
			'LpuSectionAge_id' => $data['LpuSectionAge_id'],
			'MedService_IsSendMbu'=>1
		);
		$params['MedService_IsAutoQueryRes'] = ($data['MedService_IsAutoQueryRes']) ? 2 : 1;
		$params['MedService_FreqQuery'] = ($data['MedService_IsAutoQueryRes']) ? $params['MedService_FreqQuery'] : 2;
		if( $data['MedService_IsThisLPU']){
			$params['MedService_IsThisLPU'] = 2;
		}
		if ($data['MedService_IsShowDiag']) {
			$params['MedService_IsShowDiag'] = 2;
		}
		if ($data['MedService_IsQualityTestApprove']) {
			$params['MedService_IsQualityTestApprove'] = 2;
		}
		if ($data['MedService_IsExternal']) {
			$params['MedService_IsExternal'] = 2;
		}
		if ($data['MedService_IsFileIntegration']){
			$params['MedService_IsFileIntegration'] = 2;
		}
		if ($data['MedService_IsLocalCMP']) {
			$params['MedService_IsLocalCMP'] = 2;
		}
		if ($data['MedService_IsSendMbu']) {
			$params['MedService_IsSendMbu'] = 2;
		}

		$query = '
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000),
				@MedService_id bigint = :MedService_id;

			exec '.$proc.'
				@MedService_id = @MedService_id output,
				@MedService_Name = :MedService_Name,
				@MedService_IsThisLPU = :MedService_IsThisLPU,
				@RecordQueue_id = :RecordQueue_id,
				@MedService_Nick = :MedService_Nick,
				@MedService_Code = :MedService_Code,
				@MedServiceType_id = :MedServiceType_id,
				@MedService_begDT = :MedService_begDT,
				@MedService_endDT = :MedService_endDT,
				@LpuBuilding_id = :LpuBuilding_id,
				@LpuUnitType_id = :LpuUnitType_id,
				@LpuSection_id = :LpuSection_id,
				@LpuUnit_id = :LpuUnit_id,
				@Lpu_id = :Lpu_id,
				@Org_id = :Org_id,
				@OrgStruct_id = :OrgStruct_id,
				@MedService_WialonLogin = :MedService_WialonLogin,
				@MedService_WialonPasswd = :MedService_WialonPasswd,
				@MedService_WialonNick = :MedService_WialonNick,
				@MedService_WialonURL = :MedService_WialonURL,
				@MedService_WialonAuthURL = :MedService_WialonAuthURL,
				@MedService_WialonToken = :MedService_WialonToken,
				@MedService_WialonPort = :MedService_WialonPort,
				@MedService_IsAutoQueryRes = :MedService_IsAutoQueryRes,
				@MedService_FreqQuery = :MedService_FreqQuery,
				@ApiServiceType_id = :ApiServiceType_id,
				@MedService_IsExternal = :MedService_IsExternal,
				@MedService_IsShowDiag = :MedService_IsShowDiag,
				@MedService_IsQualityTestApprove = :MedService_IsQualityTestApprove,
				@MedService_IsFileIntegration = :MedService_IsFileIntegration,
				@MedService_IsLocalCMP = :MedService_IsLocalCMP,
				@MedService_LocalCMPPath = :MedService_LocalCMPPath,
				@Address_id = :Address_id,
				@MseOffice_id = :MseOffice_id,
				@LpuSectionAge_id = :LpuSectionAge_id,
				@LpuEquipmentPacs_id = :LpuEquipmentPacs_id,
				@MedService_IsSendMbu = :MedService_IsSendMbu,
				@pmUser_id = :pmUser_id,
				@Server_id = :Server_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
				select @MedService_id as MedService_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		';

		//echo getDebugSQL($query, $params);exit;
		$result = $this->db->query( $query, $params );

		if ( is_object( $result ) ) {
			$response = $result->result( 'array' );
			if ( !empty( $response[ 0 ][ 'MedService_id' ] ) && $MedServiceType_SysNick == 'lab' ) {

				// Если при добавлении для лаборатории такой код службы уже сгенерирован
				if ( empty( $data[ 'MedService_id' ] ) ) { // только при добавлении
					$ccodes = 2;
					while( $ccodes > 1 ){ // проверяем пока код не станет нормальным
						$ccodes = $this->checkMedServiceCode( $data );
						if ( $ccodes > 1 ) {
							// если уже есть служба с таким кодом, то надо проапдейтить наш код службы на вновь сгенерированный
							$result_update = $this->incMedServiceCode( array( 'MedService_id' => $response[ 0 ][ 'MedService_id' ] ) );
							if ( !$result_update ) {
								// Запишем ошибку в лог
								log_message( 'error', 'Error update medservice code: MedService_id = '.$response[ 0 ][ 'MedService_id' ].' params: '.var_export( $params, true ) );
								$ccodes = 1; // если количество служб с таким кодом две, но апдейт вернул ошибку, то нужно остановить это насилие
							}
						}
					}
				}

				$postData = [
					'Analyzer_Name' => 'Ручные методики',
					'Analyzer_Code' => '000',
					'AnalyzerModel_id' => null,
					'MedService_id' => $response[0]['MedService_id'],
					'Analyzer_begDT' => $data['MedService_begDT'],
					'Analyzer_endDT' => null,
					'Analyzer_LisClientId' => null,
					'Analyzer_LisCompany' => null,
					'Analyzer_LisLab' => null,
					'Analyzer_LisMachine' => null,
					'Analyzer_LisLogin' => null,
					'Analyzer_LisPassword' => null,
					'Analyzer_LisNote' => null,
					'Analyzer_IsNotActive' => false,
					'Analyzer_IsAutoOk' => null,
					'Analyzer_IsAutoGood' => null,
					'Analyzer_2wayComm' => null,
					'Analyzer_IsUseAutoReg' => null,
					'Analyzer_IsManualTechnic' => 2,
					'pmUser_id' => 1 // системный
				];
				
				$this->load->model('Analyzer_model');
				$res = $this->Analyzer_model->save($postData);
				
				if (!$this->isSuccessful($res)) {
					return $res;
				}
			}

			// Удаляем данные из кэша
			$this->load->library('swCache');
			$this->swcache->clear("MedServiceList_".$data['Lpu_id']);

			//BOB - 25.01.2017
			//ЕСЛИ ИМЕЮТСЯ ПРИКРЕПЛЁННЫЕ ОБСЛУЖИВАЕМЫЕ ОТДЕЛЕНИЯ
			if ( !empty($data['MedServiceSectionData']) ) {
				$MedServiceSectionData = json_decode($data['MedServiceSectionData'], true);
				if ( is_array($MedServiceSectionData) ) {
					foreach($MedServiceSectionData as $MedServiceSection) {
						$MedServiceSection['pmUser_id'] = $data['pmUser_id'];
						$MedServiceSection['MedService_id'] = $response[ 0 ][ 'MedService_id' ];

						if ( empty($MedServiceSection['LpuSection_id']) ) {
							if ( !empty($MedServiceSection['MedServiceSection_id']) && $MedServiceSection['MedServiceSection_id'] > 0 ) {
								$MedServiceSection['RecordStatus_Code'] = 3;
							}
							else {
								continue;
							}
						}


						switch ( $MedServiceSection['RecordStatus_Code'] ) {
							case 0:
							case 2:
								//    echo '<pre>' . print_r($MedServiceSection, 1) . '</pre>'; //BOB - 25.01.2017
								$queryResponse = $this->saveMedServiceSection($MedServiceSection);
								break;

							case 3:
								$queryResponse = $this->deleteMedServiceSection($MedServiceSection);
								break;
						}

						if ( isset($queryResponse) && !is_array($queryResponse) ) {
							$this->rollbackTransaction();
							return array(array('Error_Msg' => 'Ошибка при ' . ($MedServiceSection['RecordStatus_Code'] == 3 ? 'удалении' : 'сохранении') . ' обслуживаемого отделения'));
						}
						else if ( !empty($queryResponse[0]['Error_Msg']) ) {
							$this->rollbackTransaction();
							return $queryResponse;
						}
					}
				}

			}
			//BOB - 25.01.2017




			if ( empty( $response[ 0 ][ 'Error_Msg' ] ) && !empty( $response[ 0 ][ 'MedService_id' ] ) && !empty( $data[ 'copyFromLpuSection' ] ) && !empty( $data[ 'LpuSection_id' ] ) ) {
				$data[ 'MedService_id' ] = $response[ 0 ][ 'MedService_id' ];
				$response[ 0 ][ 'Alert_Msg' ] = $this->copyDataFromLpuSection( $data, $MedServiceType_SysNick );
			}
			return $response;
		} else {
			return false;
		}
	}

    //BOB - 25.01.2017
	/**
	 * Сохранение информации об обслуживаемом отделении
	 */
	function saveMedServiceSection($data) {
		$params = $data;

		if ($params['MedServiceSection_id'] > 0) {
			$procedure = 'p_MedServiceSection_upd';
		} else {
			$params['MedServiceSection_id'] = null;
			$procedure = 'p_MedServiceSection_ins';
		}

		$query = "
			declare
				@MedServiceSection_id bigint = :MedServiceSection_id,
				@Error_Code int = 0,
				@Error_Message varchar(4000) = '';
			exec {$procedure}
				@MedServiceSection_id = @MedServiceSection_id output,
				@MedService_id = :MedService_id,
				@LpuSection_id = :LpuSection_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @MedServiceSection_id as MedServiceSection_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		return false;
	}

	/**
	 * Удаление информации об обслуживаемом отделении
	 */
	function deleteMedServiceSection($data) {
		$params = array('MedServiceSection_id' => $data['MedServiceSection_id']);

		$query = "
			declare
				@Error_Code int = 0,
				@Error_Message varchar(4000) = '';
			exec p_MedServiceSection_del
				@MedServiceSection_id = :MedServiceSection_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		return false;
	}
    //BOB - 25.01.2017




	/**
	 *  Сохраняет аппарат
	 */
	public function saveApparatus($data) {
		if ($data[ 'MedService_id' ] > 0) {
			$proc = 'p_MedService_upd';
			$data[ 'copyFromLpuSection' ] = 0;
		} else {
			$proc = 'p_MedService_ins';
			$data[ 'MedService_id' ] = null;
		}

		$params = array
			(
			'MedService_id' => $data[ 'MedService_id' ],
			'MedService_pid' => $data[ 'MedService_pid' ],
			'MedService_Name' => $data[ 'MedService_Name' ],
			'MedService_Nick' => $data[ 'MedService_Nick' ],
			'MedService_begDT' => $data[ 'MedService_begDT' ],
			'MedService_endDT' => $data[ 'MedService_endDT' ],
			'Server_id' => $data[ 'Server_id' ],
			'pmUser_id' => $data[ 'pmUser_id' ]
		);
		$query = "
			declare
				@Error_Code bigint,
				@MedServiceType_id bigint,
				@Error_Message varchar(4000),
				@MedService_id bigint = :MedService_id;

			set @MedServiceType_id = (select top 1 MedServiceType_id from v_MedServiceType (nolock) where MedServiceType_SysNick = 'app');

			exec {$proc}
				@MedService_id = @MedService_id output,
				@MedService_pid = :MedService_pid,
				@MedService_Name = :MedService_Name,
				@MedService_Nick = :MedService_Nick,
				@MedServiceType_id = @MedServiceType_id,
				@MedService_begDT = :MedService_begDT,
				@MedService_endDT = :MedService_endDT,
				@LpuBuilding_id = null,
				@LpuUnitType_id = null,
				@LpuSection_id = null,
				@LpuUnit_id = null,
				@Lpu_id = null,
				@Org_id = null,
				@OrgStruct_id = null,
				@pmUser_id = :pmUser_id,
				@Server_id = :Server_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
				select @MedService_id as MedService_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		//echo getDebugSQL($query, $params);exit;
		$result = $this->db->query($query, $params);

		if (is_object($result)) {

			// Удаляем данные из кэша
			$this->load->library('swCache');
			$this->swcache->clear("MedServiceList_".$data['Lpu_id']);

			$response = $result->result('array');
			if (empty($response[ 0 ][ 'Error_Msg' ]) && !empty($response[ 0 ][ 'MedService_id' ]) && !empty($data[ 'copyFromLpuSection' ]) && !empty($data[ 'LpuSection_id' ])) {
				$data[ 'MedService_id' ] = $response[ 0 ][ 'MedService_id' ];
				$response[ 0 ][ 'Alert_Msg' ] = $this->copyDataFromLpuSection($data, 'app');
			}
			return $response;
		} else {
			return false;
		}
	}

	/**
	 * Копирование списков услуг и сотрудников из данных отделения
	 */
	function copyDataFromLpuSection($data, $MedServiceType_SysNick)
	{
		$error = null;
		// Копирование услуг
		// для лабораторной не надо, т.к. услуги заводятся на анализатор.
		// для remoteconsultcenter не надо.
		if (false == in_array($MedServiceType_SysNick, array('lab','remoteconsultcenter'))) {
			$query = '
				select
					ucp.UslugaComplex_id,
					cast(ucp.UslugaComplexPlace_begDT as date) as UslugaComplex_begDT,
					cast(ucp.UslugaComplexPlace_endDT as date) as UslugaComplex_endDT
				from
					v_UslugaComplexPlace ucp with (NOLOCK)
				where
					(ucp.Lpu_id = :Lpu_id or ucp.Lpu_id is null)
					and ucp.LpuSection_id = :LpuSection_id
					and ucp.UslugaComplexPlace_begDT is not null -- сделал чтобы копировались только услуги с датой начала
			';
			$result = $this->db->query($query, $data);
			if ( is_object($result) )
			{
				$response = $result->result('array');
				foreach($response as $row) {
					$row['UslugaComplexMedService_id'] = 0;
					$row['MedService_id'] = $data['MedService_id'];
					$row['pmUser_id'] = $data['pmUser_id'];
					$row['session'] = $data['session'];
					$res = $this->saveUslugaComplexMedService($row);
					if(empty($res))
					{
						$error = 'Ошибка запроса БД при копировании услуг отделения';
						break;
					}
					if(!empty($res[0]['Error_Msg']))
					{
						$error = $res[0]['Error_Msg'];
						break;
					}
				}
			}
			if(!empty($error))
			{
				return $error;
			}
		}
		// Копирование сотрудников
		$query = '
			select
				msf.MedPersonal_id,
				msf.MedStaffFact_id,
				cast(msf.WorkData_begDate as date) as MedServiceMedPersonal_begDT,
				cast(msf.WorkData_endDate as date) as MedServiceMedPersonal_endDT
			from
				v_MedStaffFact msf with (NOLOCK)
				LEFT JOIN v_LpuUnit lu with(nolock) on lu.LpuUnit_id=msf.LpuUnit_id
			where
				msf.Lpu_id = :Lpu_id
				and msf.LpuSection_id = :LpuSection_id
		';
		$result = $this->db->query($query, $data);
		if ( is_object($result) )
		{
			$response = $result->result('array');
			foreach($response as $row) {
				$row['MedServiceMedPersonal_id'] = 0;
				$row['MedService_id'] = $data['MedService_id'];
				$row['Server_id'] = $data['Server_id'];
				$row['pmUser_id'] = $data['pmUser_id'];
				$res = $this->saveMedServiceMedPersonalRecord($row);
				if(empty($res))
				{
					$error = 'Ошибка запроса БД при копировании сотрудников отделения';
					break;
				}
				if(!empty($res[0]['Error_Msg']))
				{
					$error = $res[0]['Error_Msg'];
					break;
				}
			}
		}
		return $error;
	}

	/**
	 *  Записывает одну строку UslugaComplexMedService
	 */
	function saveUslugaComplexMedService($data)
	{
		$this->load->model('UslugaComplexMedService_model');
		$resp = $this->UslugaComplexMedService_model->doSave(array(
			'scenario' => self::SCENARIO_DO_SAVE,
			'UslugaComplexMedService_id' => $data['UslugaComplexMedService_id'],
			'MedService_id' => $data['MedService_id'],
			'UslugaComplex_id' => $data['UslugaComplex_id'],
			'UslugaComplexMedService_begDT' => $data['UslugaComplex_begDT'],
			'UslugaComplexMedService_endDT' => $data['UslugaComplex_endDT'],
			'session' => $data['session'],
			'pmUser_id' => $data['pmUser_id']
		));

		if (!empty($resp['UslugaComplexMedService_id'])) {
			$resp['UslugaComplex_id'] = $data['UslugaComplex_id'];
		}

		return $resp;
	}

	/**
	 *  Метод удаления одной записи UslugaComplexMedService
	 */
	function deleteUslugaComplexMedService($data)
	{
		$query = '
			declare
				@Error_Code int,
				@Error_Message varchar(4000);

			exec p_UslugaComplexMedService_del
				@UslugaComplexMedService_id = :id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
				select @Error_Code as Error_Code, @Error_Message as Error_Msg;
		';

		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *  Читает одну строку для формы редактирования MedServiceMedPersonal
	 */
	function loadMedServiceMedPersonalEditForm($data)
	{
		$query = '
			Select top 1
				msmp.MedServiceMedPersonal_id,
				msmp.MedService_id,
				msmp.MedPersonal_id,
				case when msmp.MedServiceMedPersonal_isNotApproveRights = 2 then 1 else 0 end as MedServiceMedPersonal_isNotApproveRights,
				case when msmp.MedServiceMedPersonal_isNotWithoutRegRights = 2 then 1 else 0 end as MedServiceMedPersonal_isNotWithoutRegRights,
				convert(varchar(10),msmp.MedServiceMedPersonal_begDT,104) as MedServiceMedPersonal_begDT,
				convert(varchar(10),msmp.MedServiceMedPersonal_endDT,104) as MedServiceMedPersonal_endDT,
				mp.Lpu_id,
				case when msmp.MedServiceMedPersonal_IsTransfer = 2 then 1 else 0 end as MedServiceMedPersonal_IsTransfer,
				msmp.MedStaffFact_id
			from
				v_MedServiceMedPersonal msmp with (NOLOCK)
				left join v_MedService ms with (NOLOCK) on ms.MedService_id = msmp.MedService_id
				left join v_MedPersonal mp with (NOLOCK) on msmp.MedPersonal_id = mp.MedPersonal_id and mp.Lpu_id = ms.Lpu_id
			where
				msmp.MedServiceMedPersonal_id = :MedServiceMedPersonal_id
		';
		$result = $this->db->query($query, $data);
		if ( is_object($result) )
		{
			return $result->result('array');
		}
		else
		{
			return false;
		}
	}

	/**
	 *  Записывает одну строку MedServiceMedPersonal
	 */
	function saveMedServiceMedPersonalRecord($data)
	{
		if ($data['MedServiceMedPersonal_id'] > 0)
		{
			$proc = 'p_MedServiceMedPersonal_upd';
		}
		else
		{
			$proc = 'p_MedServiceMedPersonal_ins';
			$data['MedServiceMedPersonal_id'] = null;
		}

		$params = array
		(
			'MedServiceMedPersonal_id' => $data['MedServiceMedPersonal_id'],
			'MedService_id' => $data['MedService_id'],
			'MedPersonal_id' => $data['MedPersonal_id'],
			'MedServiceMedPersonal_isNotApproveRights' => 1,
			'MedServiceMedPersonal_isNotWithoutRegRights' => 1,
			'MedServiceMedPersonal_begDT' => $data['MedServiceMedPersonal_begDT'],
			'MedServiceMedPersonal_endDT' => $data['MedServiceMedPersonal_endDT'],
			'MedStaffFact_id' => $data['MedStaffFact_id'],
			'MedServiceMedPersonal_IsTransfer' => 1,
			'Server_id'=>$data['Server_id'],
			'pmUser_id' => $data['pmUser_id']
		);
		if (isset($data['MedServiceMedPersonal_isNotApproveRights']) && $data['MedServiceMedPersonal_isNotApproveRights']) {
			$params['MedServiceMedPersonal_isNotApproveRights'] = 2;
		}
		if (isset($data['MedServiceMedPersonal_isNotWithoutRegRights']) && $data['MedServiceMedPersonal_isNotWithoutRegRights']) {
			$params['MedServiceMedPersonal_isNotWithoutRegRights'] = 2;
		}
		if (isset($data['MedServiceMedPersonal_IsTransfer']) && $data['MedServiceMedPersonal_IsTransfer']) {
			$params['MedServiceMedPersonal_IsTransfer'] = 2;
		}
		$query = '
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000),
				@MedServiceMedPersonal_id bigint = :MedServiceMedPersonal_id;

			exec ' .$proc. '
				@MedServiceMedPersonal_id = @MedServiceMedPersonal_id output,
				@MedService_id = :MedService_id,
				@MedPersonal_id = :MedPersonal_id,
				@MedServiceMedPersonal_isNotApproveRights = :MedServiceMedPersonal_isNotApproveRights,
				@MedServiceMedPersonal_isNotWithoutRegRights = :MedServiceMedPersonal_isNotWithoutRegRights,
				@MedServiceMedPersonal_begDT = :MedServiceMedPersonal_begDT,
				@MedServiceMedPersonal_endDT = :MedServiceMedPersonal_endDT,
				@MedStaffFact_id = :MedStaffFact_id,
				@MedServiceMedPersonal_IsTransfer = :MedServiceMedPersonal_IsTransfer,
				@pmUser_id = :pmUser_id,
				@Server_id = :Server_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
				select @MedServiceMedPersonal_id as MedServiceMedPersonal_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		';

		//echo getDebugSQL($query, $params);exit;
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}

	}

	/**
	 *  Выбираем службы, доступные данному врачу (MedPersonal_id)
	 */
	function defineMedServiceListOnMedPersonal($data)
	{
		$query = "
			select
				MSMP.MedService_id,
				MP.Person_Fio as MedPersonal_FIO
			from
				v_MedServiceMedPersonal MSMP with(nolock)
				left join v_MedService MS with(nolock) on MS.MedService_id = MSMP.MedService_id
				left join v_MedPersonal MP with(nolock) on MP.MedPersonal_id = MSMP.MedPersonal_id
			where
				MSMP.MedPersonal_id = :MedPersonal_id
				and MS.Lpu_id = :Lpu_id
		";

		$result = $this->db->query($query, $data);

		$res = array();
		$res['medservices'] = array();
		$res['success'] = true;
		if ( is_object($result) ) {
			$response = $result->result('array');
			if(count($response) > 0) {
				$res['MedPersonal_FIO'] = toUTF($response[0]['MedPersonal_FIO']);
				foreach($response as $r) {
					$res['medservices'][] = $r['MedService_id'];
				}
			}
			return $res;
		}
		else {
			return false;
		}
	}

	/**
	*   проверка дублирования врача на службе
	*/
	function checkDoubleMedPersonal($data)
	{
		$filter = '';
		if (!empty($data['MedServiceMedPersonal_id']))
		{
			$filter .= ' and MSMP.MedServiceMedPersonal_id <> :MedServiceMedPersonal_id';
		}
		if(!empty($data['MedServiceMedPersonal_begDT'])){
			if(empty($data['MedServiceMedPersonal_endDT'])) $data['MedServiceMedPersonal_endDT'] = '2050-01-01';
			$filter .= " and isnull(convert(varchar(10), MSMP.MedServiceMedPersonal_endDT,120), '2050-01-01') >= :MedServiceMedPersonal_begDT";
			$filter .= ' and convert(varchar(10), MSMP.MedServiceMedPersonal_begDT,120) <= :MedServiceMedPersonal_endDT';
		}

		$query = "
			select
				MSMP.MedService_id
			from
				v_MedServiceMedPersonal MSMP with(nolock)
				left join v_MedService MS with(nolock) on MS.MedService_id = MSMP.MedService_id
			where
				MSMP.MedPersonal_id = :MedPersonal_id
				and MSMP.MedService_id = :MedService_id
				{$filter}
				and MS.Lpu_id = :Lpu_id
		";

		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * loadMedServiceMedPersonalList
	 * @param $data
	 * @return bool
	 */
	function loadMedServiceMedPersonalList($data)
	{
		$where = '';
		$join = '';
		if(!empty($data['MedService_id'])) {
			$where .= ' and MSMP.MedService_id = :MedService_id';
		}

		if(!empty($data['MedServiceType_id'])) {
			$where .= ' and MS.MedServiceType_id = :MedServiceType_id';
		}

		if(!empty($data['MedServiceType_SysNick'])) {
			$join = ' left join v_MedServiceType MST with(nolock) on MST.MedServiceType_id = MS.MedServiceType_id';
			$where .= ' and MST.MedServiceType_SysNick = :MedServiceType_SysNick';
		}

		$query = "
			select distinct
				MSMP.MedServiceMedPersonal_id,
				MSMP.MedService_id,
				MSMP.MedPersonal_id,
				MS.MedServiceType_id,
				MP.Person_Fio as MedPersonal_Fio
			from
				v_MedServiceMedPersonal MSMP with(nolock)
				left join v_MedService MS with(nolock) on MS.MedService_id = MSMP.MedService_id
				left join v_MedPersonal MP with(nolock) on MP.MedPersonal_id = MSMP.MedPersonal_id
				{$join}
			where
				MP.Lpu_id = :Lpu_id
				{$where}
		";

		//echo getDebugSQL($query, $data); exit;
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * получение обслужевающей организации с указанной службой по Lpu_id
	 * @param $data
	 * @return mixed
	 */
	function getServeOrgWithMedService($data) {
		$query = "
			select top 1
				LPU.Lpu_id
			from
				v_Lpu LPU with(nolock)
				inner join v_MedService MS with(nolock) on MS.Lpu_id = LPU.Lpu_id
			where
				LPU.Lpu_id = :Lpu_id and MS.MedServiceType_id = :MedServiceType_id
		";
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			$response = $result->result('array');
			if (count($response) > 0) {
				return $data['Lpu_id'];
			}
		}

		// получение обслуживающей организации
		$query = "
			select top 1
				LOS.Lpu_id
			from
				v_LpuOrgServed LOS with(nolock)
				inner join v_Lpu LPUO with (nolock) on LPUO.Org_id = LOS.Org_id
			where
				LPUO.Lpu_id = :Lpu_id
		";
		//echo getDebugSQL($query, $data);exit;
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			$response = $result->result('array');
			if (count($response) > 0) {
				return $response[0]['Lpu_id'];
			}
		}

		return $data['Lpu_id'];
	}

	/**
	 * @param $data
	 * @return array|bool|null
	 */
	function getLpusWithMedService($data) {
		if (isset($data['comAction']))
		{$action = $data['comAction'];}
		else
		{$action = "AllAddress";}

		switch ($action)
		{
			//по месту вызова
			case "CallAddress":
				mb_regex_encoding();
				if (isset($data['CmpCallCard_Dom']))
				{
					$currNumHouse = $data['CmpCallCard_Dom'];
				}

				$filter = "(1=1) ";

				if ($data['MedServiceType_id'] != '18') {

					if ((isset($data['KLStreet_id'])) && ($data['KLStreet_id']!='')){
						$filter .= " and LRS.KLStreet_id = ".$data['KLStreet_id'];
					}

					if ((isset($data['KLSubRgn_id'])) && ($data['KLSubRgn_id']!='')){
						$filter .= " and LRS.KLSubRgn_id = ".$data['KLSubRgn_id'];
					}

					if ((isset($data['KLCity_id'])) && ($data['KLCity_id']!='')){
						$filter .= " and LRS.KLCity_id = ".$data['KLCity_id'];
					}

					if ((isset($data['KLTown_id'])) && ($data['KLTown_id']!='')){
						$filter .= " and LRS.KLTown_id = ".$data['KLTown_id'];
					}

					if ((isset($data['MedServiceType_id'])) && ($data['MedServiceType_id']!='')){
						$filter .= " and MS.MedServiceType_id = ".$data['MedServiceType_id'];
					}

					if (isset($data['Person_Age'])) {

						if ( ($data['Person_Age'] >= 1) && ($data['Person_Age'] < 18) ) {
							$filter .= " and lr.LpuRegionType_id = 2";
						}

						if ( $data['Person_Age'] >= 18) {
							$filter .= " and lr.LpuRegionType_id = 1";
						}

						if ( $data['Person_Age'] == 0)
						{
							$result = array(
							0 => array(
									'Error_Code' => null,
									'Error_Msg' => 'Дети до года обслуживаются в СМП'
									)
							);
							return $result;
							break;
						}
					}

					$query = "
					SELECT DISTINCT
						LPU.Lpu_id,
						LPU.Lpu_Name,
						LPU.Lpu_Nick,
						LRS.LpuRegionStreet_HouseSet
					FROM LpuRegionStreet LRS
						left join v_LpuRegion lr with(nolock) on lr.LpuRegion_id = LRS.LpuRegion_id
						left join v_Lpu LPU with(nolock) on (lr.Lpu_id = LPU.Lpu_id
						OR LPU.Lpu_id IN (SELECT DISTINCT LDC.Lpu_id FROM v_LpuDispContract LDC
							WHERE LDC.Lpu_oid = lr.Lpu_id
							and (LDC.LpuSectionProfile_id = 77
							or LDC.LpuSectionProfile_id = 76)
							)
						)
						left join v_MedService MS with(nolock) on MS.Lpu_id = LPU.Lpu_id WHERE {$filter}
					";
				} else {
					if ((isset($data['KLStreet_id'])) && ($data['KLStreet_id']!='')){
						$filter .= " and HC.KLStreet_id = ".$data['KLStreet_id'];
					}

					if (isset($data['Person_Age'])) {
						if ( $data['Person_Age'] == 0)
						{
							$result = array(
							0 => array(
									'Error_Code' => null,
									'Error_Msg' => 'Дети до года обслуживаются в СМП'
									)
							);
							return $result;
							break;
						}
					}

					$query = "
					SELECT DISTINCT
						LPU.Lpu_id,
						LPU.Lpu_Name,
						LPU.Lpu_Nick,
						H.KLHouse_Name as LpuRegionStreet_HouseSet
					FROM KLHouseCoords HC with(nolock)
						inner join MedServiceKLHouseCoordsRel Rel with(nolock) on (Rel.KLHouseCoords_id = HC.KLHouseCoords_id)
						left join MedService MS with(nolock) on (MS.MedService_id = Rel.MedService_id)
						left join KLHouse H with(nolock) on (H.KLHouse_id = HC.KLHouse_id)
						left join v_Lpu LPU with(nolock) on (
							MS.Lpu_id = LPU.Lpu_id
							OR LPU.Lpu_id IN (
								SELECT DISTINCT LDC.Lpu_id FROM v_LpuDispContract LDC with(nolock)
								WHERE LDC.Lpu_oid = MS.Lpu_id
								and (
									LDC.LpuSectionProfile_id = 77
									or LDC.LpuSectionProfile_id = 76
								)
							)
						)
					WHERE {$filter}
					";
				}

				$result = $this->db->query($query);
				$res = $result->result('array');



				//обработка номеров домов
				$address_result = array();
				if ( is_object($result) ) {
					//var_dump(count($res));
					if (count($res) == 0 )
					{return false; break;}
					//если несколько адресов

					$emptyFieldSMP = array(
						'Lpu_id' => '',
						'Lpu_Name' => ' ',
						'Lpu_Nick' => ' '
					);

					if (count($res) > 0 )
					{
						//есть номер дома
						if ( (isset($currNumHouse)) && ($currNumHouse != '') )
						{
							foreach( $res as $row ) {
								$houseNums = mb_split(",", $row["LpuRegionStreet_HouseSet"]);
								//есть ли дом через запятую
								foreach ($houseNums as $nh) {
									if ((string)$currNumHouse == $nh && !in_array($row, $address_result)) {
										$address_result[] = $row;
									}
								}
								//есть ли дом в интервале
								if (strstr($row["LpuRegionStreet_HouseSet"], "-")) {
									foreach( $houseNums as $str )
									{
										if (strstr($str, "-"))
										{
											$nstr = mb_split("-", $str);
											//проверка на букву (проверяем 2 индекс интервала и вводимое значение, не совпадают - не наш случай)
											$odd = $even = false;
											if (mb_substr($nstr[0], 0, 1) == "Ч") $even = true;
											if (mb_substr($nstr[0], 0, 1) == "Н") $odd = true;
											$nstr[0] = str_replace(array('Н','н','Ч','ч','('),"",$nstr[0]);
											$nstr[1] = str_replace(')',"",$nstr[1]);
											settype($nstr[0], 'integer');
											settype($nstr[1], 'integer');

											if ( (strlen((int)$currNumHouse) == strlen($currNumHouse)) && ($nstr[0] <= $currNumHouse) && ($nstr[1] >= $currNumHouse) )
											{

												if ($odd == true && $currNumHouse%2!=0 && !in_array($row, $address_result)) {
													$address_result[] = $row;
												}
												if ($even == true && $currNumHouse%2==0 && !in_array($row, $address_result))  {
													$address_result[] = $row;
												}
												continue;
											}

										}
									}
								}
							}
							//если есть адреса с домом - возвращаем его
							if (isset ($address_result))
							{
								//$address_result[] = $emptyFieldSMP;
								//var_dump($address_result);
								return $address_result;
								break;
							}
						}
						//нет номера дома
						else
						{
							//проверяем на дубликаты, если вдруг ввели одну улицу с разнми домами в участках
							$filterRes = null;
							$oldLpuId = null;
							foreach( $res as $l=>$row )
							{
								if ( $row['Lpu_id'] != $oldLpuId )
								{
									$filterRes[] = $row;
								}
								$oldLpuId = $row['Lpu_id'];
							}
							//$res[] = $emptyFieldSMP;
							//	var_dump($res);
							return $filterRes;

							break;
						}
					}
				}
				else{
					return false;
				}
			break;
			//если обычная выборка
			case "AllAddress":

				$filter = "1=1";

				if ( !empty( $data[ 'MedServiceType_id' ] )) {
					$filter .= " and MS.MedServiceType_id = :MedServiceType_id";
				}

				if(isset($data['Lpu_id']) && !empty($data['Lpu_id'])) {
					$filter .= " and LPU.Lpu_id = :Lpu_id";
				}
				//только открытые лпу
				$filter .= " and ((@cur_dt BETWEEN LPU.Lpu_begDate AND LPU.Lpu_endDate) OR ISNULL(LPU.Lpu_endDate, 1) = 1 )";
				//только открытые службы
				$filter .= " and ((@cur_dt BETWEEN MS.MedService_begDT AND MS.MedService_endDT) OR ISNULL(MS.MedService_endDT, 1) = 1 )";

				$query = "
					declare @cur_dt datetime = dbo.tzGetDate();
					select DISTINCT
						LPU.Lpu_id
						,LPU.Lpu_Name
						,LPU.Lpu_Nick
					from
						v_Lpu LPU with(nolock)
						inner join v_MedService MS with(nolock) on MS.Lpu_id = LPU.Lpu_id
					where
					{$filter}
				";

				$result = $this->db->query($query, $data);

				if ( is_object($result) ) {
					return $result->result('array');
				}

				break;
		}

		return false;

	}

	/**
	 * Читает список для sw.Promed.SwMedServiceCombo
	 * @param $filter
	 * @return bool
	 */
	function loadList($filter) {
		$where = array();
		$p = array();
		$order_clause = null;
		if (isset($filter['MedService_id']) && $filter['MedService_id']) {
			$where[] = 'ms.MedService_id = :MedService_id';
			$p['MedService_id'] = $filter['MedService_id'];
		}
		if (isset($filter['Server_id']) && $filter['Server_id']) {
			$where[] = 'ms.Server_id = :Server_id';
			$p['Server_id'] = $filter['Server_id'];
		}
		if (isset($filter['MedService_Name']) && $filter['MedService_Name']) {
			$where[] = 'ms.MedService_Name = :MedService_Name';
			$p['MedService_Name'] = $filter['MedService_Name'];
		}
		if (isset($filter['MedService_Nick']) && $filter['MedService_Nick']) {
			$where[] = 'ms.MedService_Nick = :MedService_Nick';
			$p['MedService_Nick'] = $filter['MedService_Nick'];
		}
		if (isset($filter['MedServiceType_id']) && $filter['MedServiceType_id']) {
			$where[] = 'ms.MedServiceType_id = :MedServiceType_id';
			$p['MedServiceType_id'] = $filter['MedServiceType_id'];
		}
		if (!empty($filter['MedServiceType_SysNick'])) {
			$where[] = 'mst.MedServiceType_SysNick = :MedServiceType_SysNick';
			$p['MedServiceType_SysNick'] = $filter['MedServiceType_SysNick'];
		}
		if (isset($filter['Lpu_id']) && $filter['Lpu_id']) {
			$where[] = 'ms.Lpu_id = :Lpu_id';
			$p['Lpu_id'] = $filter['Lpu_id'];
		}
		if (isset($filter['LpuBuilding_id']) && $filter['LpuBuilding_id']) {
			$where[] = 'ms.LpuBuilding_id = :LpuBuilding_id';
			$p['LpuBuilding_id'] = $filter['LpuBuilding_id'];
		}
		if (isset($filter['LpuUnitType_id']) && $filter['LpuUnitType_id']) {
			$where[] = 'ms.LpuUnitType_id = :LpuUnitType_id';
			$p['LpuUnitType_id'] = $filter['LpuUnitType_id'];
		}
		if (isset($filter['LpuUnit_id']) && $filter['LpuUnit_id']) {
			$where[] = 'ms.LpuUnit_id = :LpuUnit_id';
			$p['LpuUnit_id'] = $filter['LpuUnit_id'];
		}
		if (isset($filter['LpuSection_id']) && $filter['LpuSection_id']) {
			$where[] = 'ms.LpuSection_id = :LpuSection_id';
			$p['LpuSection_id'] = $filter['LpuSection_id'];
		}
		if (isset($filter['MedService_begDT']) && $filter['MedService_begDT']) {
			$where[] = 'ms.MedService_begDT = :MedService_begDT';
			$p['MedService_begDT'] = $filter['MedService_begDT'];
		}
		if (isset($filter['MedService_endDT']) && $filter['MedService_endDT']) {
			$where[] = 'ms.MedService_endDT = :MedService_endDT';
			$p['MedService_endDT'] = $filter['MedService_endDT'];
		}
		if (isset($filter['Org_id']) && $filter['Org_id']) {
			$where[] = 'ms.Org_id = :Org_id';
			$p['Org_id'] = $filter['Org_id'];
		}
		if (isset($filter['OrgStruct_id']) && $filter['OrgStruct_id']) {
			$where[] = 'ms.OrgStruct_id = :OrgStruct_id';
			$p['OrgStruct_id'] = $filter['OrgStruct_id'];
		}
		$selectLpuSectionProfile_id_list = ',null as LpuSectionProfile_id_List';
		if (!empty($filter['isDirection'])
			&& !empty($filter['Lpu_id'])
			&& !empty($filter['setDate'])
			&& 'remoteconsultcenter' == $filter['MedServiceType_SysNick']
		) {
			/*
			 * список служб фильтруется по профилю и списку "обслуживаемые организации" (если он есть)
			 */
			//$p['Lpu_uid'] = $filter['session']['lpu_id'];//фильтр по обсл. МО
			if (!empty($filter['LpuSectionProfile_id'])) {
				$p['LpuSectionProfile_id'] = $filter['LpuSectionProfile_id'];
				$where[] = 'exists(
					select top 1 LpuSectionProfileMedService_id
					from v_LpuSectionProfileMedService p with (NOLOCK)
					where MedService_id = ms.MedService_id
						and LpuSectionProfile_id = :LpuSectionProfile_id
				)';
			}
			$p['setDate'] = $filter['setDate'];
			$where[] = 'convert(varchar(10),ms.MedService_begDT,120) <= :setDate';
			$where[] = '(ms.MedService_endDT is null OR convert(varchar(10),ms.MedService_endDT,120) >= :setDate)';
			$selectLpuSectionProfile_id_list = ",STUFF(
				(
				SELECT ','+cast(lspms.LpuSectionProfile_id as varchar)
				FROM v_LpuSectionProfileMedService lspms WITH (nolock)
				inner join v_LpuSectionProfile lsp WITH (nolock) on lsp.LpuSectionProfile_id = lspms.LpuSectionProfile_id
						and (lsp.LpuSectionProfile_begDT is null OR convert(varchar(10),lsp.LpuSectionProfile_begDT,120) <= :setDate)
						and (lsp.LpuSectionProfile_endDT is null OR convert(varchar(10),lsp.LpuSectionProfile_endDT,120) >= :setDate)
				WHERE lspms.MedService_id = ms.MedService_id
				FOR XML PATH ('')
				), 1, 1, ''
			) as LpuSectionProfile_id_List";
		}
		// выбираем только связанные службы
		// Пока реализована связь только лаборатории с пунктами забора
		$join = '';
		if ($filter['MedService_lid']>0) { // Если есть связь с лабораторией
			if ($filter['MedServiceType_SysNick']=='pzm') { // Если тип служб = пункт забора
				$join = 'inner join v_MedServiceLink msl with(nolock) on ms.MedService_id= msl.MedService_id and msl.MedService_lid = :MedService_lid';
			} else { // Если тип служб = лаборатория, других связей пока нет
				$join = 'inner join v_MedServiceLink msl with(nolock) on ms.MedService_id= msl.MedService_lid and msl.MedService_id = :MedService_lid';
			}
			$p['MedService_lid'] = $filter['MedService_lid'];
		}

		//  Показать только несвязанные
		if ( $filter['NotLinkedMedService_id'] > 0 ) {
			if ($filter['MedServiceType_SysNick']=='pzm') { // Если тип служб = пункт забора
				$where[] = 'ms.MedService_id not in (select MedService_id from v_MedServiceLink with(nolock) where MedService_lid = :NotLinkedMedService_id)';
			} else { // Если тип служб = лаборатория
				$where[] = 'ms.MedService_id not in (select MedService_lid as MedService_id from v_MedServiceLink with(nolock) where MedService_id = :NotLinkedMedService_id)';
			}
			$p['NotLinkedMedService_id'] = $filter['NotLinkedMedService_id'];
		}

		if ( !empty($filter['filterByCurrentMedPersonal']) ) {
			$where[] = 'MSMP.MedPersonal_id = :medPersonal_id';
			$p['medPersonal_id'] = $filter['session']['medpersonal_id'];
		}

		if ( !empty($filter['isClose']) ) {
			// 1 - действующие на дату приема вызова службы НМП
			// 2 - закрытые на дату приема вызова службы НМП
			if ($filter['isClose'] == 1) {
				$where[] = " (ms.MedService_endDT is null or ms.MedService_endDT > dbo.tzGetDate())";
			} elseif ($filter['isClose'] == 2) {
				$where[] = " ms.MedService_endDT <= dbo.tzGetDate()";
			}
		}

		$selectLpuSectionLpuSectionProfileList = ',null as LpuSectionLpuSectionProfileList';
		if ( !empty($filter['isLpuSectionLpuSectionProfileList']) ){
			//профиля отделений из списка действующих: основной профиль и дополнительные профиля
			$selectLpuSectionLpuSectionProfileList = "
				,STUFF(
					(select
						',' + cast(LpuSectionProfile_id as varchar)
					FROM
						v_LpuSectionLpuSectionProfile with (nolock)
					WHERE
						LpuSection_id = LpuSection_id_ref.LpuSection_id
					FOR XML PATH ('')
					), 1, 1, ''
				) + ',' + cast(LpuSection_id_ref.LpuSectionProfile_id as varchar) as LpuSectionLpuSectionProfileList
				";
		}

		if ($filter['MedServiceType_SysNick'] == 'pzm') {
			$p['OurLpu_id'] = $filter['session']['lpu_id'];
			$where[] = 'not exists (select * from v_MedService vms with (nolock) where vms.MedService_id = ms.MedService_id and vms.MedService_IsThisLPU = 2 and vms.Lpu_id != :OurLpu_id)';
		}

		$where_clause = implode(' AND ', $where);
		if (strlen($where_clause)) {
			$where_clause = 'WHERE '.$where_clause;
		}

		$q = "
		SELECT distinct top 1000
			ms.MedService_id,
			ms.Server_id,
			ms.MedService_Name,
			ms.MedService_Nick,
			ms.MedServiceType_id,
			ms.Lpu_id,
			ms.LpuBuilding_id,
			ms.LpuUnitType_id,
			ms.LpuUnit_id,
			ms.LpuSection_id,
			ms.MedService_begDT,
			ms.MedService_endDT,
			ms.Org_id,
			ms.OrgStruct_id,
			mst.MedServiceType_Name MedServiceType_id_Name,
			Lpu_id_ref.Lpu_Nick Lpu_id_Nick,
			LpuBuilding_id_ref.LpuBuilding_Name LpuBuilding_id_Name,
			ISNULL(LpuBuilding_id_ref_Address_ref.Address_Address,'(нет адреса)') Address_Address,
			LpuUnitType_id_ref.LpuUnitType_Name LpuUnitType_id_Name,
			LpuUnit_id_ref.LpuUnit_Name LpuUnit_id_Name,
			LpuUnit_id_ref.LpuBuilding_Name,
			LpuSection_id_ref.LpuSection_Name LpuSection_id_Name,
			Org_id_ref.Org_Name Org_id_Name
			{$selectLpuSectionProfile_id_list}
			{$selectLpuSectionLpuSectionProfileList}
		FROM
			dbo.v_MedService ms WITH ( NOLOCK )
			LEFT JOIN dbo.v_MedServiceType mst WITH ( NOLOCK ) ON mst.MedServiceType_id = ms.MedServiceType_id
			LEFT JOIN dbo.v_Lpu Lpu_id_ref WITH ( NOLOCK ) ON Lpu_id_ref.Lpu_id = ms.Lpu_id
			LEFT JOIN dbo.v_LpuBuilding LpuBuilding_id_ref WITH ( NOLOCK ) ON LpuBuilding_id_ref.LpuBuilding_id = ms.LpuBuilding_id
    		LEFT JOIN dbo.v_Address LpuBuilding_id_ref_Address_ref WITH ( NOLOCK ) ON LpuBuilding_id_ref_Address_ref.Address_id = LpuBuilding_id_ref.Address_id
			LEFT JOIN dbo.v_LpuUnitType LpuUnitType_id_ref WITH ( NOLOCK ) ON LpuUnitType_id_ref.LpuUnitType_id = ms.LpuUnitType_id
			LEFT JOIN dbo.v_LpuUnit LpuUnit_id_ref WITH ( NOLOCK ) ON LpuUnit_id_ref.LpuUnit_id = ms.LpuUnit_id
			LEFT JOIN dbo.v_LpuSection LpuSection_id_ref WITH ( NOLOCK ) ON LpuSection_id_ref.LpuSection_id = ms.LpuSection_id
			LEFT JOIN dbo.v_Org Org_id_ref WITH ( NOLOCK ) ON Org_id_ref.Org_id = ms.Org_id
			left join v_MedServiceMedPersonal as MSMP with(nolock) on MSMP.MedService_id = ms.MedService_id
			{$join}
			{$where_clause}
		";

		//для работы distinct и order by
		if ( $filter['order'] == 'lpu' && !empty($filter['session']['lpu_id']) ) {
			$p['OurLpu_id'] = $filter['session']['lpu_id'];
			$q = "
				with myvars as (
					{$q}
				)

				select * from myvars
				ORDER BY (case when myvars.Lpu_id = :OurLpu_id then 1 else 0 end) DESC, Lpu_id_Nick ASC
			";
		}

		//echo getDebugSQL($q, $p);
		$result = $this->db->query($q, $p);
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 * Получение данных по службе для регистратуры
	 */
	function getMedServiceInfoForReg($data)
	{
		$params = array(
			'MedService_id' => $data['MedService_id']
		);
		$sql = "
			select TOP 1
				ms.Lpu_id,
				l.Lpu_Nick,
				ms.MedService_id,
				ms.MedService_Name,
				l.Org_id,
				lu.LpuUnit_id,
				lu.LpuUnit_Name
			from
				v_MedService ms (nolock)
				left join v_Lpu l (nolock) on l.Lpu_id = ms.Lpu_id
				left join v_LpuUnit lu (nolock) on lu.LpuUnit_id = ms.LpuUnit_id
			where
				ms.MedService_id = :MedService_id
		";
		$result = $this->db->query($sql, $params);
		if (is_object($result))
		{
			$res = $result->result('array');
			return $res[0];
		}
		else
		{
			return false;
		}
	}

	/**
	 * Получение данных по услуге для регистратуры
	 */
	function getUslugaComplexInfoForReg($data)
	{
		$params = array(
			'UslugaComplexMedService_id' => $data['UslugaComplexMedService_id']
		);
		$sql = "
			select TOP 1
				ms.Lpu_id,
				l.Lpu_Nick,
				ms.MedService_id,
				ms.MedService_Name,
				l.Org_id,
				lu.LpuUnit_id,
				lu.LpuUnit_Name,
				ucms.UslugaComplexMedService_id,
				uc.UslugaComplex_id,
				uc.UslugaComplex_Name
			from v_UslugaComplexMedService ucms with (nolock)
			left join v_MedService ms with (nolock) on ucms.MedService_id = ms.MedService_id
			left join v_Lpu l with (nolock) on l.Lpu_id = ms.Lpu_id
			left join v_LpuUnit lu (nolock) on lu.LpuUnit_id = ms.LpuUnit_id
			left join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = ucms.UslugaComplex_id
			where ucms.UslugaComplexMedService_id = :UslugaComplexMedService_id
		";
		$result = $this->db->query($sql, $params);
		if (is_object($result))
		{
			$res = $result->result('array');
			return $res[0];
		}
		else
		{
			return false;
		}
	}

	/**
	 *
	 * @param type $data
	 * @return type
	 */
	function getResourceInfoForReg($data)
	{
		$params = array(
			'Resource_id' => $data['Resource_id']
		);
		$sql = "
			select TOP 1
				ms.Lpu_id,
				l.Lpu_Nick,
				res.Resource_id,
				res.Resource_Name,
				ms.MedService_id,
				ms.MedService_Name,
				l.Org_id,
				lu.LpuUnit_id,
				lu.LpuUnit_Name
			from v_Resource res with (nolock)
			left join v_MedService ms with (nolock) on res.MedService_id = ms.MedService_id
			left join v_Lpu l with (nolock) on l.Lpu_id = ms.Lpu_id
			left join v_LpuUnit lu (nolock) on lu.LpuUnit_id = ms.LpuUnit_id
			where res.Resource_id = :Resource_id
		";
		$result = $this->db->query($sql, $params);
		if (is_object($result))
		{
			$res = $result->result('array');
			return $res[0];
		}
		else
		{
			return false;
		}
	}

	/**
	 * @param $data
	 * @return array|bool
	 * @description Читает для формы направления на службы
	 */
	function loadUslugaComplexMedServiceList($data)
	{
		$filter = '';
		$add_join = '';
		$params = array(
			'LpuSection_id' => $data['LpuSection_id'],
			'Lpu_id'=>$data['Lpu_id']
		);
		$uc_join = 'inner join';

		if ((!isset($data['LpuSection_id']) || (isset($data['LpuSection_id']) && $data['LpuSection_id'] == 0)) &&  isset($data['session']['CurARM']['ARMType']) && ($data['session']['CurARM']['ARMType'] == 'reanimation'))//BOB - 02.07.2019
		{
			$params['MedStaffFact_id'] = $data['session']['CurMedStaffFact_id'];
			//echo '<pre>' . 'BOB_loadUslugaComplexMedServiceList-$params_1= '.print_r($params, 1) . '</pre>';  //BOB - 02.07.2019
			$sql = "
				Select
					user_ls.Lpu_id,
					user_ls.LpuBuilding_id,
					user_ls.LpuUnit_id,
					user_ls.LpuSection_id
				from MedStaffFactCache user_ls with (nolock)
				where user_ls.MedStaffFact_id = :MedStaffFact_id
			";
			$result = $this->db->query($sql, $params);
			if (is_object($result)) {
				$rc = $result->result('array');
				if (count($rc)>0 && is_array($rc[0])) {
					$params['LpuSection_id'] = $rc[0]['LpuSection_id'];
				}
			}
		}
		//BOB - 02.07.2019
		//echo '<pre>' . 'BOB_loadUslugaComplexMedServiceList-$params_2= '.print_r($params, 1) . '</pre>';  //BOB - 02.07.2019
		switch (true) {
			case (!empty($data['EvnPrescr_id'])):
				// только службы, оказывающие услугу из назначения либо услугу, связанную с ней по эталонным полям
				$params['EvnPrescr_id'] = $data['EvnPrescr_id'];
				switch ($data['PrescriptionType_Code']) {
					case '6':
						$add_join = 'inner join v_EvnUslugaCommon EP with (nolock) on EP.EvnPrescr_id = :EvnPrescr_id
				inner join v_UslugaComplex EPUC with (nolock) on EPUC.UslugaComplex_id = EP.UslugaComplex_id ';
						break;
					case '7':
						$add_join = 'inner join v_EvnPrescrOperUsluga EP with (nolock) on EP.EvnPrescrOper_id = :EvnPrescr_id
				inner join v_UslugaComplex EPUC with (nolock) on EPUC.UslugaComplex_id = EP.UslugaComplex_id ';
						break;
					case '11':
						$add_join = 'inner join v_EvnPrescrLabDiag EP with (nolock) on EP.EvnPrescrLabDiag_id = :EvnPrescr_id
				inner join v_UslugaComplex EPUC with (nolock) on EPUC.UslugaComplex_id = EP.UslugaComplex_id ';
						break;
					case '12':
						$add_join = 'inner join v_EvnPrescrFuncDiagUsluga EP with (nolock) on EP.EvnPrescrFuncDiag_id = :EvnPrescr_id
				inner join v_UslugaComplex EPUC with (nolock) on EPUC.UslugaComplex_id = EP.UslugaComplex_id ';
						break;
					case '13':
						$add_join = 'inner join v_EvnPrescrConsUsluga EP with (nolock) on EP.EvnPrescrConsUsluga_id = :EvnPrescr_id
				inner join v_UslugaComplex EPUC with (nolock) on EPUC.UslugaComplex_id = EP.UslugaComplex_id ';
						break;
					default:
						return false;
				}
				$filter .= " and (
					UC.UslugaComplex_id = EPUC.UslugaComplex_id
					OR UC.UslugaComplex_2004id = EPUC.UslugaComplex_2004id
					OR UC.UslugaComplex_2011id = EPUC.UslugaComplex_2011id
					OR UC.UslugaComplex_slprofid = EPUC.UslugaComplex_slprofid
				) ";
				break;
			// Фильтры по типу направления
			// в зависимости от типа направления доступны разные службы
			case (9 == $data['DirType_id']):
				// на ВК и МСЭ
				$uc_join = 'left join';
				//$filter .= " and ms.LpuUnit_id is null and mst.MedServiceType_SysNick in ('vk', 'mse')  ";
				if(isset($data['MedService_Caption'])){
					$filter .= " and ms.MedService_Name like :MedService_Caption ";
					$params['MedService_Caption']="%".$data['MedService_Caption']."%";
				}
				$filter .= " /*and ms.LpuUnit_id is null*/ and mst.MedServiceType_SysNick like 'vk' ";
				break;
			case (17 == $data['DirType_id']):
				// направление в ЦУК
				$uc_join = 'left join';
				if (isset($data['MedService_Caption'])){
					$filter .= " and ms.MedService_Name like :MedService_Caption ";
					$params['MedService_Caption']="%".$data['MedService_Caption']."%";
				}
				$filter .= " and mst.MedServiceType_id = 36 ";
				break;
			default:
				$filter .= " and mst.MedServiceType_SysNick not in ('patb', 'okadr', 'mstat', 'dpoint', 'merch', 'regpol', 'sprst', 'slneotl', 'smp', 'minzdravdlo')  ";
				break;
		}

		if (!empty($data['onlyMyLpu'])) {
			$filter .= " and ms.Lpu_id = :Lpu_id";
		}

		$sql = "
			-- variables
			declare @cur_dt datetime = dbo.tzGetDate();
			-- end variables

			select
				-- select
				case when MedService_IsThisLPU=2 and ms.Lpu_id!=:Lpu_id then 0 else 1 end as selfOnly,
				ms.MedService_id,
				case when ttucms1.TimetableMedService_begTime is null then null else ucms.UslugaComplexMedService_id end as UslugaComplexMedService_id,
				uc.UslugaComplex_id,
				ms.Lpu_id,
				ms.LpuBuilding_id,
				lu.LpuUnitType_id,
				ms.LpuUnit_id,
				ms.LpuSection_id,
				ls.LpuSectionProfile_id,
				ms.MedServiceType_id,
				lu.LpuUnitType_SysNick,
				l.Lpu_Nick,
				ms.MedService_Name,
				ms.MedService_Nick,
				mst.MedServiceType_SysNick,
				ISNULL(ucms.UslugaComplex_Name, uc.UslugaComplex_Name) as UslugaComplex_Name,
				ls.LpuSectionProfile_Name,
				lu.LpuBuilding_Name,
				lu.LpuUnit_Name,
				ls.LpuSection_Name,
				lua.Address_Address as LpuUnit_Address,
				CONVERT(varchar(10), ISNULL(ttms1.TimetableMedService_begTime, ttms2.TimetableMedService_begTime), 104) as FirstFreeDate,
				CONVERT(varchar(5), ISNULL(ttms1.TimetableMedService_begTime, ttms2.TimetableMedService_begTime), 108) as FirstFreeTime
				,user_l.Lpu_id as user_Lpu_id
				,user_lu.LpuBuilding_id as user_LpuBuilding_id
				,user_ls.LpuUnit_id as user_LpuUnit_id
				,user_ls.LpuSection_id as user_LpuSection_id
				-- end select
			from
				-- from
				v_MedService ms with (nolock)
				{$uc_join} v_UslugaComplexMedService ucms with (nolock) on ucms.MedService_id = ms.MedService_id
				{$uc_join} v_UslugaComplex uc with (nolock) on uc.UslugaComplex_id = ucms.UslugaComplex_id
				left join v_LpuUnit lu with (nolock) on lu.LpuUnit_id = ms.LpuUnit_id
				inner join v_Lpu l with (nolock) on ms.Lpu_id = l.Lpu_id
				left join v_LpuSection ls with (nolock) on ms.LpuSection_id = ls.LpuSection_id
				left join v_MedServiceType mst with (nolock) on ms.MedServiceType_id = mst.MedServiceType_id
				left join v_Lpu user_l with (nolock) on user_l.Lpu_id = :Lpu_id
				left join v_LpuSection user_ls with (nolock) on user_ls.LpuSection_id = :LpuSection_id
				left join v_LpuUnit user_lu with (nolock) on user_lu.LpuUnit_id = user_ls.LpuUnit_id
				left join v_Address lua with (nolock) on lua.Address_id = isnull(lu.Address_id,l.UAddress_id)
				outer apply (
					select top 1 TimetableMedService_begTime from v_TimetableMedService_lite with (nolock)
					where UslugaComplexMedService_id = ucms.UslugaComplexMedService_id
						and TimetableMedService_begTime >= @cur_dt
				) ttucms1
				outer apply (
					select top 1 TimetableMedService_begTime
					from v_TimetableMedService_lite with (nolock)
					where Person_id is null
						and MedService_id = ms.MedService_id
						and (ucms.UslugaComplexMedService_id is null or ttucms1.TimetableMedService_begTime is null)
						and TimetableMedService_begTime >= @cur_dt
				) ttms1
				outer apply (
					select top 1
						TimetableMedService_id,
						TimetableMedService_begTime
					from v_TimetableMedService_lite with (nolock)
					where Person_id is null
						and UslugaComplexMedService_id = ucms.UslugaComplexMedService_id
						and TimetableMedService_begTime >= @cur_dt
				) ttms2
				{$add_join}
				-- end from
			WHERE
				-- where
				(
					ucms.UslugaComplexMedService_endDT is null
					OR cast(ucms.UslugaComplexMedService_endDT as date) > cast(@cur_dt as date)
					OR ttms2.TimetableMedService_id is not null
				)
				and l.Lpu_endDate is null
				and (ms.MedService_endDT is null OR cast(ms.MedService_endDT as date) > cast(@cur_dt as date))
				and not exists( select top 1 1 from v_medservice mslo with(nolock) where mslo.MedService_id = ms.MedService_id and mslo.MedService_IsThisLPU=2 and mslo.lpu_id!=:Lpu_id)
				--and uc.UslugaComplex_pid is null
				{$filter}
				-- end where
			ORDER BY
				-- order by
				case when ms.Lpu_id=user_l.Lpu_id then '' else l.Lpu_Nick end,
				case when ms.LpuBuilding_id=user_lu.LpuBuilding_id then '' else lu.LpuBuilding_Name end,
				case when ms.LpuUnit_id=user_ls.LpuUnit_id then '' else lu.LpuUnit_Name end,
				case when ms.LpuSection_id=user_ls.LpuSection_id then '' else ls.LpuSection_Name end,
				uc.UslugaComplex_Name
				-- end order by
		";
		/*
				left join v_UslugaComplex uc with (nolock) on uc.UslugaComplex_id = ucms.UslugaComplex_id
				left join v_UslugaComplexAttribute uca with (nolock) on uca.UslugaComplex_id = uc.UslugaComplex_id
				left join v_UslugaComplexAttributeType ucat with (nolock) on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
		 */

		// echo getDebugSQL(getLimitSQLPH($sql, $data['start'], $data['limit']), $params); die;
		$result = $this->db->query(getLimitSQLPH($sql, $data['start'], $data['limit']), $params);
		$result_count = $this->db->query(getCountSQLPH($sql), $params);

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
			if (17 == $data['DirType_id']) {
				$tmp = array();
				foreach ($response['data'] as $row) {
					$tmp[] = $row['MedService_id'];
				}
				if (count($tmp) > 0) {
					$tmp = implode(',', $tmp);
					$sql = "
						declare @setDate datetime = dbo.tzGetDate();
						select lspms.MedService_id, lsp.LpuSectionProfile_id, lsp.LpuSectionProfile_Name
						from v_LpuSectionProfileMedService lspms with (nolock)
						inner join v_LpuSectionProfile lsp with (nolock) on lsp.LpuSectionProfile_id = lspms.LpuSectionProfile_id
							AND (lsp.LpuSectionProfile_begDT IS NULL OR lsp.LpuSectionProfile_begDT <= @setDate)
							AND (lsp.LpuSectionProfile_endDT IS NULL OR lsp.LpuSectionProfile_endDT >= @setDate)
						where lspms.MedService_id in ({$tmp})
						order by lsp.LpuSectionProfile_Name
					";
					$result = $this->db->query($sql);
					if (is_object($result)) {
						$tmp = $result->result('array');
						$msLsp = array();
						foreach ($tmp as $row) {
							$id = $row['MedService_id'];
							if (empty($msLsp[$id])) {
								$msLsp[$id] = array('nameList' => array(), 'idList' => array());
							}
							$msLsp[$id]['nameList'][] = $row['LpuSectionProfile_Name'];
							$msLsp[$id]['idList'][] = $row['LpuSectionProfile_id'];
						}
						if (count($msLsp) > 0) {
							foreach ($response['data'] as $index => $row) {
								$id = $row['MedService_id'];
								if (isset($msLsp[$id])) {
									$response['data'][$index]['UslugaComplex_Name'] = implode(', ', $msLsp[$id]['nameList']);
									$response['data'][$index]['FirstFreeDate'] = implode(',', $msLsp[$id]['idList']);
								}
							}
						}
					}
				}
			}
			$response['totalCount'] = $count;
			return $response;
		}
		else
		{
			return false;
		}

	}

	/**
	 * Загрузка списка в фильтр "Службы" в форме добавления назначений услуг
	 */
	function loadFilterCombo($data)
	{
		$this->load->helper('Reg');
		$params = array(
			'MedServiceType_id' => $data['MedServiceType_id']
		);
		$MedServiceType_ids = array();
		if (isset($data['PrescriptionType_Code'])) {
			switch ($data['PrescriptionType_Code']) {
				case '6':// Манипуляции и процедуры
					$MedServiceType_ids[] = 13; // Процедурный кабинет
					break;
				case '7': // Оперативное лечение
					$MedServiceType_ids[] = 57; // Опер. блок
					break;
				case '11': // Лабораторная диагностика
					$MedServiceType_ids[] = 6; // Лаборатория
					$MedServiceType_ids[] = 7; // Пункт забора биоматериала
					$MedServiceType_ids[] = 71; // Бактериология
					break;
				case '12': // Инструментальная диагностика
					$MedServiceType_ids[] = 8; // Диагностика
					break;
				case '13': // Консультационная услуга
					$MedServiceType_ids[] = 29; // Консультативный прием
					break;
				case '14': // Операционный блок
					$MedServiceType_ids[] = 57; // Операционный блок
					break;
			}
		}
		$allRowsWhere = array(
			'MedServiceType_id' => 'ms.MedServiceType_id = :MedServiceType_id'
		);
		if (count($MedServiceType_ids) > 0) {
			$MedServiceType_ids_str = implode(',', $MedServiceType_ids);
			$allRowsWhere['MedServiceType_id'] = "ms.MedServiceType_id in ({$MedServiceType_ids_str})";
		}

		if (!empty($data['filterByLpu_id'])) {
			$params['filterByLpu_id'] = $data['filterByLpu_id'];
			$allRowsWhere['filterByLpu'] = 'ms.Lpu_id = :filterByLpu_id';
			if ($data['filterByLpu_id'] != $data['Lpu_id']) {
				$allRowsWhere['IsThisLPU'] = '(ms.MedService_IsThisLPU != 2 or ISNULL(ms.MedService_IsThisLPU,0) = 0)';
			}
		} else if (!empty($data['filterByLpu_str'])) {
			$params['filterByLpu_str'] = '%'.$data['filterByLpu_str'].'%';
			// Фильтруем места оказания по Lpu_Nick или Lpu_Name ?
			$allRowsWhere['filterByLpu'] = 'lpu.Lpu_Nick like :filterByLpu_str';
		}

		if (!empty($data['query'])) {
			$params['query'] = '%'.$data['query'].'%';
			// Фильтруем по MedService_Nick или MedService_Name ?
			$allRowsWhere['query'] = 'ms.MedService_Nick like :query';
		}

		$allRowsWhere['work'] = 'cast(ms.MedService_begDT as date) <= @cur_date
		and (ms.MedService_endDT is null OR cast(ms.MedService_endDT as date) > @cur_date)';

		if (!empty($data['filterByUslugaComplex_id'])) {
			$params['UslugaComplex_id'] = $data['filterByUslugaComplex_id'];
			$allRowsWhere['UslugaComplex_id'] = "exists (
				select top 1 uc.UslugaComplex_id
				from v_UslugaComplexMedService ucms with (nolock)
				inner join v_UslugaComplex uc with (nolock) on uc.UslugaComplex_id = ucms.UslugaComplex_id
				where ucms.MedService_id = ms.MedService_id
				and ucms.UslugaComplexMedService_pid IS NULL
				and ucms.UslugaComplex_id = :UslugaComplex_id
				and cast(ucms.UslugaComplexMedService_begDT as date) <= @cur_date
				and (ucms.UslugaComplexMedService_endDT is null OR cast(ucms.UslugaComplexMedService_endDT as date) > @cur_date)
			)";
		}

		if (!empty($data['isOnlyPolka'])) {
			// будем показывать только службы поликлинических отделений, в т.ч. стоматологических
			$allRowsWhere['isOnlyPolka'] = "exists (
				select top 1 lut.LpuUnitType_id
				from v_LpuUnitType lut with (nolock)
				where lut.LpuUnitType_id = ms.LpuUnitType_id and lut.LpuUnitType_SysNick in ('polka', 'ccenter', 'traumcenter', 'fap')
			)";
		}

		$allRowsWhere = implode("\n AND ", $allRowsWhere);
		$sql = "
			declare @cur_dt datetime = dbo.tzGETDATE();
			declare @cur_date date = cast(@cur_dt as date);

			select top 100
				lpu.Lpu_id,
				lpu.Lpu_Nick,
				ms.MedService_id,
				ms.MedServiceType_id,
				ms.MedService_Name,
				ms.MedService_Nick
			from
				v_MedService ms with (nolock)
				inner join v_Lpu lpu with (nolock) on lpu.Lpu_id = ms.Lpu_id
			where {$allRowsWhere}
			ORDER BY
				ms.MedService_Name
		";

		//echo getDebugSQL($sql, $params); die;
		$result = $this->db->query($sql, $params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Загрузка данных выбора службы по известной услуге в комбо "место оказания"
	 * и в фильтр по службе
	 */
	function getMedServiceSelectCombo($data)
	{
		$this->load->helper('Reg');
		$params = array(
			'LpuSection_id' => $data['userLpuSection_id'],
			'userLpu_id' => $data['Lpu_id'],
			'MedServiceType_id' => 0,
			'medservice_record_day_count' => GetMedServiceDayCount()
		);
		if (empty($params['medservice_record_day_count'])) {
			$params['medservice_record_day_count'] = 365; // не ограничено
		}

		$isLab = (11==$data['PrescriptionType_Code']);
		$isFunc = (12==$data['PrescriptionType_Code']);
		switch ($data['PrescriptionType_Code']) {
			case '6':// Манипуляции и процедуры
				$params['MedServiceType_id'] = 13; // Процедурный кабинет
				break;
			case '7': // Оперативное лечение
				$params['MedServiceType_id'] = 5; // Другое
				break;
			case '11': // Лабораторная диагностика
				$params['MedServiceType_id'] = 6; // Лаборатория
				break;
			case '12': // Инструментальная диагностика
				$params['MedServiceType_id'] = 8; // Диагностика
				break;
			case '13': // Консультационная услуга
				$params['MedServiceType_id'] = 29; // Консультативный прием
				break;
		}
		$MedServiceTypeWithResources = "'".implode("','", array('func'))."'";
		$allRowsSelect = array(
			'UslugaComplexMedService_id'=>'ucms.UslugaComplexMedService_id',
			'UslugaComplex_2011id'=>'uc.UslugaComplex_2011id',
			'UslugaComplex_id'=>'uc.UslugaComplex_id',
			'UslugaComplex_Code'=>'uc.UslugaComplex_Code',
			'UslugaComplex_Name'=>'ISNULL(ucms.UslugaComplex_Name, uc.UslugaComplex_Name) as UslugaComplex_Name',
			'MedService_Name'=>'ms.MedService_Name',
			'MedService_Nick'=>'ms.MedService_Nick',
			'MedServiceType_id'=>'ms.MedServiceType_id',
			'Lpu_id'=>'ms.Lpu_id',
			'LpuBuilding_id'=>'ms.LpuBuilding_id',
			'LpuSection_id'=>'ms.LpuSection_id',
			'LpuUnit_id'=>'ms.LpuUnit_id',
			//override for lab
			'MedService_id'=>'ms.MedService_id',
			'isComposite'=>'0 as isComposite',
			'lab_MedService_id'=>'null as lab_MedService_id',
			'pzm_MedService_id'=>'null as pzm_MedService_id',
			'pzm_UslugaComplexMedService_id'=>'null as pzm_UslugaComplexMedService_id',
			'pzm_Lpu_id'=>'null as pzm_Lpu_id',
			'pzm_LpuUnit_id'=>'null as pzm_LpuUnit_id',
			'pzm_LpuSection_id'=>'null as pzm_LpuSection_id',
			'pzm_MedServiceType_id'=>'null as pzm_MedServiceType_id',
			'pzm_MedServiceType_SysNick'=>'null as pzm_MedServiceType_SysNick',
			'pzm_MedService_Name'=>'null as pzm_MedService_Name',
			'pzm_MedService_Nick'=>'null as pzm_MedService_Nick',
			'withResource'=>"case when mst.MedServiceType_SysNick in ({$MedServiceTypeWithResources}) then 1 else 0 end as withResource",
		);

		// $allRowsFrom - массив с выражениями FROM запроса, формирующего таблицу DD:
		$allRowsFrom = array(
			// Информация о службах (ms) (через WHERE этот перечень фильтруется, остаются только лаборатории):
			'ms'=>'v_MedService ms with (nolock)',

			// К информации о службах (ms) прицепляем информацию об их типе (mst), оставляем в выборке лишь те службы,
			// для которых известен тип:
			'mst'=>'inner join v_MedServiceType mst with (nolock) on mst.MedServiceType_id = ms.MedServiceType_id',

			// К информации о службах (ms) прицепляем информацию об их связях с услугами (ucms), оставляем в выборке
			// лишь службы, связанные с услугами:
			'ucms'=>'inner join v_UslugaComplexMedService ucms with (nolock) on ms.MedService_id = ucms.MedService_id',

			// К связям служб с услугами (ucms) прицепляем информацию об услугах (uc), оставляем в выборке лишь те связи
			// (и, соответственно службы), для которых есть информация об услугах:
			'uc'=>'inner join v_UslugaComplex uc with (nolock) on uc.UslugaComplex_id = ucms.UslugaComplex_id',
		);

		// $allRowsWhere - массив с условиями WHERE запроса, формирующего таблицу DD:
		$allRowsWhere = array(
			'MedServiceType_id' => 'ms.MedServiceType_id = :MedServiceType_id'
		);

		if (!empty($data['filterByUslugaComplex_id'])) {
			$params['UslugaComplex_id'] = $data['filterByUslugaComplex_id'];
			$allRowsWhere['filterByUslugaComplex_id'] = 'uc.UslugaComplex_id = :UslugaComplex_id';
		}
		if ( !empty($this->options['prescription']['enable_grouping_by_gost2011']) || 2 == $this->options['prescription']['service_name_show_type']) {
			// При группировке услуг по связке со справочником ГОСТ 2011 и при отображении наименований по ГОСТ 2011
			// При отсутствии связки услуги со справочником ГОСТ 2011 данную услугу не отображать.
			$allRowsFrom['uc11']='inner join v_UslugaComplex uc11 with (nolock) on uc.UslugaComplex_2011id = uc11.UslugaComplex_id';
		}
		if ( 2 == $this->options['prescription']['service_name_show_type']) {
			// при отображении наименований по ГОСТ 2011
			$allRowsSelect['UslugaComplex_Code']='uc11.UslugaComplex_Code';// вместо uc.UslugaComplex_Code
			$allRowsSelect['UslugaComplex_Code']='uc11.UslugaComplex_Name';// вместо ISNULL(ucms.UslugaComplex_Name, uc.UslugaComplex_Name) as UslugaComplex_Name
		}

		if ($isLab) {
			$fromHasAnalyzerTestTpl = "cross apply (
					select top 1 Analyzer.Analyzer_id
					from lis.v_AnalyzerTest AnalyzerTest with (nolock)
					inner join lis.v_Analyzer Analyzer (nolock) on Analyzer.Analyzer_id = AnalyzerTest.Analyzer_id
					where AnalyzerTest.UslugaComplexMedService_id = {ucms_id}
					and ISNULL(AnalyzerTest.AnalyzerTest_IsNotActive, 1) = 1 and ISNULL(Analyzer.Analyzer_IsNotActive, 1) = 1
				) ATT";
			$allRowsFrom['fromHasAnalyzerTestTpl'] = strtr($fromHasAnalyzerTestTpl,array('{ucms_id}'=>'ucms.UslugaComplexMedService_id'));

			// #5597
			// Дополняем инф-цию о службах (ms) инф-цией о связанных с ней пунктах забора (pzm), причем только из
			// интересующей МО.
			// Во вложенном SELECT из всех служб и их связей с другими службами (v_MedService в пересечении с
			// v_MedServiceLink через inner join) оставляем только те, что удовлетворяют условиям:
			//  1. Служба относится к интересующей МО (Lpu_id) (если МО известно).
			//  2. Служба является пунктом забора (MedServiceType_id).
			//  3. Связь имеет тип "Пункт забора - лаборатория" (MedServiceLinkType_id).
			//  4. Служба действующая (MedService_endDT).
			//  5. Запись на службу не запрещена (RecordQueue_id).
			$allRowsFrom['pzm'] = '
			LEFT JOIN (
					SELECT
						vms.Lpu_id,
						vms.LpuUnit_id,
						vms.LpuSection_id,
						vms.MedService_id,
						vms.MedService_Name,
						vms.MedService_Nick,
						vms.MedServiceType_id,
						vmsl.MedService_lid
					FROM
						v_MedService vms
						INNER JOIN v_MedServiceLink vmsl ON vmsl.MedService_id = vms.MedService_id 
					WHERE';

			if (!empty($data['filterByLpu_id']))
				$allRowsFrom['pzm'] = $allRowsFrom['pzm'] . '
					vms.Lpu_id = :filterByLpu_id
						AND ';

			$allRowsFrom['pzm'] = $allRowsFrom['pzm'] . '
						vms.MedServiceType_id = 7
						AND vmsl.MedServiceLinkType_id = 1
						AND (vms.MedService_endDT IS NULL OR CAST(vms.MedService_endDT AS DATE) > @cur_date)
						AND vms.RecordQueue_id != 1 -- запись не запрещена
			) pzm ON 
				pzm.MedService_lid = ms.MedService_id';

			// Дополняем информацию о пунктах забора (pzm) информацией об их связях с услугами (ucpzm), причем лишь
			// теми, с которыми связаны и лаборатория (ucms) (если пункт забора не связан с такими услугами,
			// соответствующие столбцы итоговой выборки остаются пустыми):
			$allRowsFrom['ucpzm'] = 'left join v_UslugaComplexMedService ucpzm with (nolock) on ucpzm.MedService_id = pzm.MedService_id
			and ucms.UslugaComplex_id = ucpzm.UslugaComplex_id';

			$MedServiceDisplayField = "isnull(DD.pzm_MedService_Nick, DD.MedService_Nick)";

			$allRowsSelect['isComposite'] = 'case when exists(
			select ucms2.UslugaComplexMedService_id
			from v_UslugaComplexMedService ucms2 with (nolock)
				'.strtr($fromHasAnalyzerTestTpl,array('{ucms_id}'=>'ucms2.UslugaComplexMedService_id')).'
			where
				ucms2.UslugaComplexMedService_pid = ucms.UslugaComplexMedService_id
		) then 1 else 0 end as isComposite';
			$allRowsSelect['lab_MedService_id'] = 'ms.MedService_id as lab_MedService_id';
			$allRowsSelect['pzm_MedService_id'] = 'pzm.MedService_id as pzm_MedService_id';
			$allRowsSelect['pzm_UslugaComplexMedService_id'] = 'ucpzm.UslugaComplexMedService_id as pzm_UslugaComplexMedService_id';
			$allRowsSelect['pzm_Lpu_id'] = 'pzm.Lpu_id as pzm_Lpu_id';
			$allRowsSelect['pzm_LpuUnit_id'] = 'pzm.LpuUnit_id as pzm_LpuUnit_id';
			$allRowsSelect['pzm_LpuSection_id'] = 'pzm.LpuSection_id as pzm_LpuSection_id';
			$allRowsSelect['pzm_MedServiceType_id'] = 'pzm.MedServiceType_id as pzm_MedServiceType_id';
			$allRowsSelect['pzm_MedServiceType_SysNick'] = "'pzm' as pzm_MedServiceType_SysNick";
			$allRowsSelect['pzm_MedService_Name'] = 'pzm.MedService_Name as pzm_MedService_Name';
			$allRowsSelect['pzm_MedService_Nick'] = 'pzm.MedService_Nick as pzm_MedService_Nick';
			$AllRowsLpuField = "isnull(ms.Lpu_id,pzm.Lpu_id)";
		} else {
			$MedServiceDisplayField = "DD.MedService_Nick";
			$AllRowsLpuField = "ms.Lpu_id";
		}
		if($isFunc){
			$allRowsSelect['Resource_id'] = 'res.Resource_id';
			$allRowsSelect['Resource_Name'] = 'res.Resource_Name';
			$allRowsFrom['ucres'] = 'inner join v_UslugaComplexResource ucres with (nolock) on ucres.UslugaComplexMedService_id = ucms.UslugaComplexMedService_id';
			$allRowsFrom['res'] = 'LEFT join v_Resource res with (nolock) on res.Resource_id = ucres.Resource_id and (res.Resource_endDT is null OR cast(res.Resource_endDT as date) > @cur_date)';
		}
		if (!empty($data['filterByLpu_id'])) {
			$params['filterByLpu_id'] = $data['filterByLpu_id'];
			$allRowsWhere['filterByLpu'] = $AllRowsLpuField. ' = :filterByLpu_id';
		} else if (!empty($data['filterByLpu_str'])) {
			$params['filterByLpu_str'] = '%'.$data['filterByLpu_str'].'%';
			// Фильтруем места оказания по Lpu_Nick или Lpu_Name ?
			$allRowsWhere['filterByLpu'] = 'exists (
				select top 1 l.Lpu_id
				from v_Lpu l with (nolock)
				where l.Lpu_id = ' . $AllRowsLpuField . ' and l.Lpu_Nick like :filterByLpu_str
			)';
		}

		//общие фильтры
		//только 0 уровня
		$allRowsWhere['UslugaComplexMedService_pid'] = 'ucms.UslugaComplexMedService_pid IS NULL';
		$resExists = 'exists(
			select t2.Resource_id
			from v_UslugaComplexResource t1 with (nolock)
			inner join v_Resource t2 with (nolock) on t1.Resource_id = t2.Resource_id
			where t1.UslugaComplexMedService_id = ucms.UslugaComplexMedService_id
			and ISNULL(cast(t2.Resource_begDT as date), @cur_date) <= @cur_date
			and ISNULL(cast(t2.Resource_endDT as date), @cur_date) >= @cur_date
		)';
		if($isFunc)
			$resExists = 'res.Resource_id is not null';
		$allRowsWhere['work'] = 'cast(ms.MedService_begDT as date) <= @cur_date
		and (ms.MedService_endDT is null OR cast(ms.MedService_endDT as date) > @cur_date)
		and ms.RecordQueue_id != 1 -- запись не запрещена
		and cast(ucms.UslugaComplexMedService_begDT as date) <= @cur_date
		and (ucms.UslugaComplexMedService_endDT is null OR cast(ucms.UslugaComplexMedService_endDT as date) > @cur_date)
		and (mst.MedServiceType_SysNick not in (\'func\') OR '.$resExists.')';

		if (!empty($data['isOnlyPolka'])) {
			// будем показывать только службы поликлинических отделений, в т.ч. стоматологических
			$allRowsWhere['isOnlyPolka'] = "exists (
	select top 1 lut.LpuUnitType_id
	from v_LpuUnitType lut with (nolock)
	where lut.LpuUnitType_id = ms.LpuUnitType_id and lut.LpuUnitType_SysNick in ('polka', 'ccenter', 'traumcenter', 'fap')
)";
		}

		$sortDir = ('DESC'==$data['dir'])?'DESC':'ASC';
		switch ($data['sort']) {
			case 'UslugaComplex_FullName':
				$orderBy = "UslugaComplex_Code {$sortDir}";
				break;
			case 'Lpu_Nick':
				$orderBy = "lpu.Lpu_Nick {$sortDir}";
				break;
			case 'LpuBuilding_Name':
				$orderBy = "lu.LpuBuilding_Name {$sortDir}";
				break;
			case 'LpuUnit_Name':
				$orderBy = "lu.LpuUnit_Name {$sortDir}";
				break;
			case 'LpuSection_Name':
				$orderBy = "ls.LpuSection_Name {$sortDir}";
				break;
			case 'LpuUnit_Address':
				$orderBy = "lua.Address_Address {$sortDir}";
				break;
			case 'timetable':
				$orderBy = "ttmsx.TimetableMedService_begTime {$sortDir}";
				break;
			case 'MedService_Nick':
				$orderBy = "{$MedServiceDisplayField} {$sortDir}";
				break;
			default:
				/**
				 * "наверх" должна попадать служба по принципу
				 * наибольшей близости и наиболее ранним доступным временем записи
				 */
				$orderBy = "isnull(ttmsx.TimetableMedService_begTime,'2999-12-31'),
				case when DD.Lpu_id=user_ls.Lpu_id then 1 else 2 end,
				case when DD.LpuBuilding_id=user_lu.LpuBuilding_id then 1 else 2 end,
				case when DD.LpuUnit_id=user_ls.LpuUnit_id then 1 else 2 end,
				case when DD.LpuSection_id=user_ls.LpuSection_id then 1 else 2 end";
				break;
		}

		$allRowsFields = array();
		foreach ($allRowsSelect as $alias => $select) {
			$allRowsFields[]=$alias;
		}
		$allRowsFields = implode("\n,", $allRowsFields);
		$allRowsSelect = implode("\n,", $allRowsSelect);
		$allRowsFrom = implode("\n", $allRowsFrom);
		$allRowsWhere = implode("\n AND ", $allRowsWhere);

		$this->load->library('sql/UslugaComplexSelectListSqlBuilder');
		$data['userLpu_id'] = $data['Lpu_id'];

		$addFields = "
			,ttmsx.TimetableMedService_id as TimetableMedService_id
			,ttmsx.MedService_id as ttms_MedService_id
			,ttmsx.UslugaComplexMedService_id as ttms_UslugaComplexMedService_id
			,ttmsx.TimetableResource_id
			,ttmsx.Resource_id
			,ttmsx.Resource_Name
			,ttmsx.Resource_id as ttr_Resource_id
		";
		$key = "cast(DD.UslugaComplexMedService_id as varchar) + ISNULL('_' + cast(DD.MedService_id as varchar), '') + ISNULL('_' + cast(DD.pzm_MedService_id as varchar), '') as UslugaComplexMedService_key,";
		if($isFunc){
			$addFields = "
				,ttmsx.TimetableMedService_id as TimetableMedService_id
				,DD.MedService_id as ttms_MedService_id
				,DD.UslugaComplexMedService_id as ttms_UslugaComplexMedService_id
				,ttmsx.TimetableResource_id
				,DD.Resource_id
				,DD.Resource_Name
				,DD.Resource_id as ttr_Resource_id
			";
			$key = "cast(DD.UslugaComplexMedService_id as varchar) + ISNULL('_' + cast(DD.MedService_id as varchar), '') + ISNULL('_' + cast(DD.Resource_id as varchar), '') as UslugaComplexMedService_key,";
		}


		if(!empty($data['formMode']))
			UslugaComplexSelectListSqlBuilder::$formMode = $data['formMode'];
		UslugaComplexSelectListSqlBuilder::$withResource = ($data['PrescriptionType_Code'] == 12);
		UslugaComplexSelectListSqlBuilder::$isLab = $isLab;
		UslugaComplexSelectListSqlBuilder::$isFunc = $isFunc;
		$from = UslugaComplexSelectListSqlBuilder::getTimetableQueryFrom();

		$sql = "
			-- addit with
			declare @cur_dt datetime = GETDATE();
			declare @upper_dt datetime = DATEADD(day, cast(:medservice_record_day_count as int), @cur_dt);
			declare @cur_date date = cast(@cur_dt as date);

			WITH DD ({$allRowsFields})
			AS
			(
				SELECT DISTINCT {$allRowsSelect}
				FROM  {$allRowsFrom}
				WHERE  {$allRowsWhere}
			)

			-- end addit with
			select
				-- select
				{$key}
				DD.MedService_id,
				DD.UslugaComplexMedService_id,
				lpu.Lpu_id,
				lpu.Lpu_Nick,
				lu.LpuBuilding_id,
				lu.LpuBuilding_Name,
				lu.LpuUnitType_id,
				lu.LpuUnitType_SysNick,
				lu.LpuUnit_id,
				lu.LpuUnit_Name,
				lua.Address_Address as LpuUnit_Address,
				ls.LpuSection_id,
				ls.LpuSection_Name,
				ls.LpuSectionProfile_id,
				DD.MedService_Name,
				DD.MedService_Nick,
				mst.MedServiceType_id,
				mst.MedServiceType_SysNick,
				DD.UslugaComplex_2011id,
				DD.UslugaComplex_id,
				DD.UslugaComplex_Code,
				DD.UslugaComplex_Name
				,CONVERT(varchar(10), ttmsx.TimetableMedService_begTime, 104) +' '
				+CONVERT(varchar(5), ttmsx.TimetableMedService_begTime, 108) as TimetableMedService_begTime
				,DD.isComposite
				,DD.lab_MedService_id
				,DD.pzm_MedService_id
				,DD.pzm_UslugaComplexMedService_id
				,DD.pzm_Lpu_id
				,DD.pzm_MedServiceType_id
				,DD.pzm_MedServiceType_SysNick
				,DD.pzm_MedService_Name
				,DD.pzm_MedService_Nick
				,CONVERT(varchar(10), ttmsx.TimetableResource_begTime, 104) +' '+CONVERT(varchar(5), ttmsx.TimetableResource_begTime, 108) as TimetableResource_begTime
				{$addFields}
				-- end select
			from
				-- from
				DD
				inner join v_LpuSection user_ls with (nolock) on user_ls.LpuSection_id = :LpuSection_id
				inner join v_LpuUnit user_lu with (nolock) on user_lu.LpuUnit_id = user_ls.LpuUnit_id
				inner join v_MedServiceType mst with (nolock) on mst.MedServiceType_id = DD.MedServiceType_id
				inner join v_Lpu lpu with (nolock) on lpu.Lpu_id = DD.Lpu_id
				inner join v_LpuUnit lu with (nolock) on lu.LpuUnit_id = DD.LpuUnit_id
				inner join v_LpuSection ls with (nolock) on ls.LpuSection_id = DD.LpuSection_id
				left join v_Address lua with (nolock) on lua.Address_id = isnull(lu.Address_id, lpu.UAddress_id)
				{$from}
				-- end from
			ORDER BY
				-- order by
				{$orderBy}
				-- end order by
		";

		//echo getDebugSQL(getLimitSQLPH($sql, $data['start'], $data['limit']), $params); die;
		$result = $this->db->query(getLimitSQLPH($sql, $data['start'], $data['limit']), $params);
		if (is_object($result))
		{
			$response = array();
			$response['data'] = $result->result('array');
			$response['totalCount'] = 0;
			return $response;
		}
		else
		{
			return false;
		}
	}


	/**
	 * Загрузка данных в грид формы выбора службы по известной услуге
	 */
	function getMedServiceSelectList($data)
	{
		$this->load->helper('Reg');
		$params = array(
			'LpuSection_id' => $data['userLpuSection_id'],
			'medservice_record_day_count' => GetMedServiceDayCount()
		);
		if (empty($params['medservice_record_day_count'])) {
			$params['medservice_record_day_count'] = 365; // не ограничено
		}

		$isLab = (11==$data['PrescriptionType_Code']);
		$MedServiceTypeWithResources = "'".implode("','", array('func'))."'";
		$allRowsSelect = array(
			'UslugaComplexMedService_id'=>'ucms.UslugaComplexMedService_id',
			'UslugaComplex_id'=>'uc.UslugaComplex_id',
			'UslugaComplex_Code'=>'uc.UslugaComplex_Code',
			'UslugaComplex_Name'=>'ISNULL(ucms.UslugaComplex_Name, uc.UslugaComplex_Name) as UslugaComplex_Name',
			'MedService_Name'=>'ms.MedService_Name',
			'MedService_Nick'=>'ms.MedService_Nick',
			'MedServiceType_id'=>'ms.MedServiceType_id',
			'Lpu_id'=>'ms.Lpu_id',
			'LpuBuilding_id'=>'ms.LpuBuilding_id',
			'LpuSection_id'=>'ms.LpuSection_id',
			'LpuUnit_id'=>'ms.LpuUnit_id',
			//override for lab
			'MedService_id'=>'ms.MedService_id',
			'isComposite'=>'0 as isComposite',
			'lab_MedService_id'=>'null as lab_MedService_id',
			'pzm_MedService_id'=>'null as pzm_MedService_id',
			'pzm_UslugaComplexMedService_id'=>'null as pzm_UslugaComplexMedService_id',
			'pzm_Lpu_id'=>'null as pzm_Lpu_id',
			'pzm_LpuUnit_id'=>'null as pzm_LpuUnit_id',
			'pzm_LpuSection_id'=>'null as pzm_LpuSection_id',
			'pzm_MedServiceType_id'=>'null as pzm_MedServiceType_id',
			'pzm_MedServiceType_SysNick'=>'null as pzm_MedServiceType_SysNick',
			'pzm_MedService_Name'=>'null as pzm_MedService_Name',
			'pzm_MedService_Nick'=>'null as pzm_MedService_Nick',
			'withResource'=>"case when mst.MedServiceType_SysNick in ({$MedServiceTypeWithResources}) then 1 else 0 end as withResource",
		);
		$allRowsFrom = array(
			'ms'=>'v_MedService ms with (nolock)',
			'mst'=>'inner join v_MedServiceType mst with (nolock) on mst.MedServiceType_id = ms.MedServiceType_id',
			'ucms'=>'inner join v_UslugaComplexMedService ucms with (nolock) on ms.MedService_id = ucms.MedService_id',
			// услуга лаборатории или службы другого типа
			'uc'=>'inner join v_UslugaComplex uc with (nolock) on uc.UslugaComplex_id = ucms.UslugaComplex_id',
		);
		if (2 == $this->options['prescription']['service_name_show_type']) {
			//Отображение наименований услуг из Справочник ГОСТ-2011
			// в v_UslugaComplexMedService.UslugaComplex_id может быть услуга не из ГОСТ-2011 и не связанная с ГОСТ-2011
			$allRowsFrom['UC2011'] = 'inner join v_UslugaComplex UC2011 with (nolock) on UC2011.UslugaComplex_id = UC.UslugaComplex_2011id';
			$allRowsSelect['UslugaComplex_Code'] = 'UC2011.UslugaComplex_Code';
			$allRowsSelect['UslugaComplex_Name'] = 'UC2011.UslugaComplex_Name';
		}
		/*
		if ($this->options['prescription']['enable_show_service_nick']) {
			//Отображать состав лабораторной услуги (тесты) при помощи кратких наименований услуг, при наличии
			if (2 == $this->options['prescription']['service_name_show_type']) {
				$allRowsSelect['UslugaComplex_Name'] = "coalesce(UC2011.UslugaComplex_Nick, UC2011.UslugaComplex_Name, '') as UslugaComplex_Name";
			} else {
				$allRowsSelect['UslugaComplex_Name'] = "coalesce(UC.UslugaComplex_Nick, ucms.UslugaComplex_Name, UC.UslugaComplex_Name) as UslugaComplex_Name";
			}
		}
		*/
		$allRowsWhere = array();
		$all_ttms_filters = array(
			'free'=>'ttms.Person_id is null',
			'lowerLimitTime'=>'ttms.TimetableMedService_begTime >= @cur_dt',
			'upperLimitTime'=>'ttms.TimetableMedService_begTime < @upper_dt',
		);
		$all_ttr_filters = array(
			'free'=>'ttr.Person_id is null',
			'lowerLimitTime'=>'ttr.TimetableResource_begTime >= @cur_dt',
			'upperLimitTime'=>'ttr.TimetableResource_begTime < @upper_dt',
		);
		$all_ttms_join = array();
		$all_ttms_join['ttms'] = 'v_TimetableMedService_lite ttms with (nolock)';
		$all_ttr_join = array();
		$all_ttr_join['ttr'] = 'v_TimetableResource_lite ttr with (nolock)';
		$all_ttr_join['res'] = 'inner join v_Resource r with (nolock) on r.Resource_id = ttr.Resource_id';
		$all_ttr_join['ucr'] = 'inner join v_UslugaComplexResource ucr with (nolock) on ucr.Resource_id = r.Resource_id';
		if ($isLab) {
			$allRowsWhere['MedServiceType_id'] = 'ms.MedServiceType_id = 6';
			$fromHasAnalyzerTestTpl = "cross apply(
					select top 1 Analyzer.Analyzer_id
					from lis.v_AnalyzerTest AnalyzerTest with (nolock)
					inner join lis.v_Analyzer Analyzer (nolock) on Analyzer.Analyzer_id = AnalyzerTest.Analyzer_id
					where AnalyzerTest.UslugaComplexMedService_id = {ucms_id}
					and ISNULL(AnalyzerTest.AnalyzerTest_IsNotActive, 1) = 1 and ISNULL(Analyzer.Analyzer_IsNotActive, 1) = 1
				) ATT";
			$allRowsFrom['fromHasAnalyzerTestTpl'] = strtr($fromHasAnalyzerTestTpl,array('{ucms_id}'=>'ucms.UslugaComplexMedService_id'));

			$allRowsFrom['msl'] = 'left join v_MedServiceLink msl with (nolock) on msl.MedService_lid = ms.MedService_id
			and msl.MedServiceLinkType_id = 1';
			$allRowsFrom['pzm'] = 'left join v_MedService pzm with (nolock) on pzm.MedServiceType_id = 7
			and msl.MedService_id = pzm.MedService_id
			and (pzm.MedService_endDT is null OR cast(pzm.MedService_endDT as date) > @cur_date)';
			$allRowsFrom['ucpzm'] = 'left join v_UslugaComplexMedService ucpzm with (nolock) on ucpzm.MedService_id = pzm.MedService_id
			and ucms.UslugaComplex_id = ucpzm.UslugaComplex_id';

			$MedServiceDisplayField = "isnull(AllRows.pzm_MedService_Nick, AllRows.MedService_Nick)";
			$LpuKeyField = "isnull(AllRows.pzm_Lpu_id, AllRows.Lpu_id)";

			$LpuUnitKeyField = "isnull(AllRows.pzm_LpuUnit_id, AllRows.LpuUnit_id)";
			$LpuSectionKeyField = "isnull(AllRows.pzm_LpuSection_id, AllRows.LpuSection_id)";

			$allRowsSelect['isComposite'] = 'case when exists(
			select ucms2.UslugaComplexMedService_id
			from v_UslugaComplexMedService ucms2 with (nolock)
				'.strtr($fromHasAnalyzerTestTpl,array('{ucms_id}'=>'ucms2.UslugaComplexMedService_id')).'
			where
				ucms2.UslugaComplexMedService_pid = ucms.UslugaComplexMedService_id
		) then 1 else 0 end as isComposite';
			$allRowsSelect['MedService_id'] = 'isnull(pzm.MedService_id, ms.MedService_id) as MedService_id';
			$allRowsSelect['MedServiceLink_id'] = 'msl.MedServiceLink_id';
			$allRowsSelect['lab_MedService_id'] = 'ms.MedService_id as lab_MedService_id';
			$allRowsSelect['pzm_MedService_id'] = 'pzm.MedService_id as pzm_MedService_id';
			$allRowsSelect['pzm_UslugaComplexMedService_id'] = 'ucpzm.UslugaComplexMedService_id as pzm_UslugaComplexMedService_id';
			$allRowsSelect['pzm_Lpu_id'] = 'pzm.Lpu_id as pzm_Lpu_id';
			$allRowsSelect['pzm_LpuUnit_id'] = 'pzm.LpuUnit_id as pzm_LpuUnit_id';
			$allRowsSelect['pzm_LpuSection_id'] = 'pzm.LpuSection_id as pzm_LpuSection_id';
			$allRowsSelect['pzm_MedServiceType_id'] = 'pzm.MedServiceType_id as pzm_MedServiceType_id';
			$allRowsSelect['pzm_MedServiceType_SysNick'] = "'pzm' as pzm_MedServiceType_SysNick";
			$allRowsSelect['pzm_MedService_Name'] = 'pzm.MedService_Name as pzm_MedService_Name';
			$allRowsSelect['pzm_MedService_Nick'] = 'pzm.MedService_Nick as pzm_MedService_Nick';
			$AllRowsLpuField = "isnull(pzm.Lpu_id,ms.Lpu_id)";
		} else {
			$MedServiceDisplayField = "AllRows.MedService_Nick";
			$AllRowsLpuField = "ms.Lpu_id";
			$LpuKeyField = "AllRows.Lpu_id";
			$LpuUnitKeyField = "AllRows.LpuUnit_id";
			$LpuSectionKeyField = "AllRows.LpuSection_id";
		}

		if (!empty($data['filterByLpu_id'])) {
			$params['filterByLpu_id'] = $data['filterByLpu_id'];
			$allRowsWhere['filterByLpu'] = $AllRowsLpuField. ' = :filterByLpu_id';
		} else if (!empty($data['filterByLpu_str'])) {
			$params['filterByLpu_str'] = '%'.$data['filterByLpu_str'].'%';
			// Фильтруем места оказания по Lpu_Nick или Lpu_Name ?
			$allRowsWhere['filterByLpu'] = 'exists (
				select top 1 l.Lpu_id
				from v_Lpu l with (nolock)
				where l.Lpu_id = ' . $AllRowsLpuField . ' and l.Lpu_Nick like :filterByLpu_str
			)';
		}

		//общие фильтры
		//только 0 уровня
		$allRowsWhere['UslugaComplexMedService_pid'] = 'ucms.UslugaComplexMedService_pid IS NULL';
		$allRowsWhere['work'] = 'cast(ms.MedService_begDT as date) <= @cur_date
		and (ms.MedService_endDT is null OR cast(ms.MedService_endDT as date) > @cur_date)
		and cast(ucms.UslugaComplexMedService_begDT as date) <= @cur_date
		and (ucms.UslugaComplexMedService_endDT is null OR cast(ucms.UslugaComplexMedService_endDT as date) > @cur_date)';
		$allRowsWhere['filterIsThisLPU'] ='not exists( select top 1 1 from v_medservice mslo with(nolock) where mslo.MedService_id = ms.MedService_id and mslo.MedService_IsThisLPU=2 and mslo.lpu_id!=:Lpu_id)';
		$params['Lpu_id'] = $data['Lpu_id'];
		if (!empty($data['filterByUslugaComplex_id'])) {
			$params['UslugaComplex_id'] = $data['filterByUslugaComplex_id'];
			$allRowsWhere['UslugaComplex_id'] = 'uc.UslugaComplex_id = :UslugaComplex_id';
		}

		if (!empty($data['isOnlyPolka'])) {
			// будем показывать только службы поликлинических отделений, в т.ч. стоматологических
			$allRowsWhere['isOnlyPolka'] = "exists (
	select top 1 lut.LpuUnitType_id
	from v_LpuUnitType lut with (nolock)
	where lut.LpuUnitType_id = ms.LpuUnitType_id and lut.LpuUnitType_SysNick in ('polka', 'ccenter', 'traumcenter', 'fap')
)";
		}

		$sortDir = ('DESC'==$data['dir'])?'DESC':'ASC';
		switch ($data['sort']) {
			case 'UslugaComplex_FullName':
				$orderBy = "UslugaComplex_Code {$sortDir}";
				break;
			case 'Lpu_Nick':
				$orderBy = "lpu.Lpu_Nick {$sortDir}";
				break;
			case 'LpuBuilding_Name':
				$orderBy = "lu.LpuBuilding_Name {$sortDir}";
				break;
			case 'LpuUnit_Name':
				$orderBy = "lu.LpuUnit_Name {$sortDir}";
				break;
			case 'LpuSection_Name':
				$orderBy = "ls.LpuSection_Name {$sortDir}";
				break;
			case 'LpuUnit_Address':
				$orderBy = "lua.Address_Address {$sortDir}";
				break;
			case 'timetable':
				$orderBy = "ttms.TimetableMedService_begTime {$sortDir}";
				break;
			case 'MedService_Nick':
				$orderBy = "{$MedServiceDisplayField} {$sortDir}";
				break;
			default:
				/**
				 * "наверх" должна попадать служба по принципу
				 * наибольшей близости и наиболее ранним доступным временем записи
				 */
				$orderBy = "
					isnull(ttms.TimetableMedService_begTime,'2999-12-31'),
					case when AllRows.Lpu_id=user_ls.Lpu_id then 1 else 2 end,
					case when AllRows.LpuBuilding_id=user_lu.LpuBuilding_id then 1 else 2 end,
					case when AllRows.LpuUnit_id=user_ls.LpuUnit_id then 1 else 2 end,
					case when AllRows.LpuSection_id=user_ls.LpuSection_id then 1 else 2 end,
					AllRows.MedService_id,
					AllRows.UslugaComplexMedService_id
					" . (!empty($allRowsSelect['MedServiceLink_id']) ? ",AllRows.MedServiceLink_id" : "") . "
				";
				break;
		}

		// ИМХО правильнее было бы отсекать в WHERE типы бирок, на которые нельзя записывать, но писатели ТЗ умнее, поэтому делаю по ТЗ
		$typeOrder = 'case
			/* При записи в свое МО: Все типы бирок кроме «резервных» */
			when AllRows.Lpu_id=user_ls.Lpu_id AND ttms.TimeTableType_id not in (2) then 0
			/* При записи в чужое МО: Обычная, По направлению */
			when AllRows.Lpu_id<>user_ls.Lpu_id AND ttms.TimeTableType_id in (1,5) then 0
			else 1
		end, ttms.TimetableMedService_begTime';
		$typeOrderRes = 'case
			/* При записи в свое МО: Все типы бирок кроме «резервных» */
			when AllRows.Lpu_id=user_ls.Lpu_id AND ttr.TimeTableType_id not in (2) then 0
			/* При записи в чужое МО: Обычная, По направлению */
			when AllRows.Lpu_id<>user_ls.Lpu_id AND ttr.TimeTableType_id in (1,5) then 0
			else 1
		end, ttr.TimetableResource_begTime';

		$allRowsFields = array();
		foreach ($allRowsSelect as $alias => $select) {
			$allRowsFields[]=$alias;
		}
		$allRowsFieldsStr = implode("\n,", $allRowsFields);
		$allRowsSelectStr = implode("\n,", $allRowsSelect);
		$allRowsFromStr = implode("\n", $allRowsFrom);
		$allRowsWhereStr = implode("\n AND ", $allRowsWhere);
		$all_ttms_filters = implode("\n AND ", $all_ttms_filters);
		$all_ttr_filters = implode("\n AND ", $all_ttr_filters);
		$all_ttms_join = implode("\n", $all_ttms_join);
		$all_ttr_join = implode("\n", $all_ttr_join);
		$sql = "
			-- variables
			declare @cur_dt datetime = GETDATE();
			declare @upper_dt datetime = DATEADD(day, cast(:medservice_record_day_count as int), @cur_dt);
			declare @cur_date date = cast(@cur_dt as date);

			SET NOCOUNT ON;
			IF OBJECT_ID(N'tempdb..#AllRows', N'U') IS NOT NULL
				DROP TABLE #AllRows;

			SELECT {$allRowsSelectStr}
			Into #AllRows
			FROM  {$allRowsFromStr}
			WHERE  {$allRowsWhereStr};
			-- end variables

			select
				-- select
				cast(AllRows.MedService_id as varchar)+CAST(AllRows.UslugaComplexMedService_id as varchar)" . (!empty($allRowsSelect['MedServiceLink_id']) ? "+CAST(isnull(AllRows.MedServiceLink_id,0) as varchar)" : "") . " as UslugaComplexMedService_key,
				AllRows.MedService_id,
				AllRows.UslugaComplexMedService_id,
				lpu.Lpu_id,
				lpu.Lpu_Nick,
				lu.LpuBuilding_id,
				lu.LpuBuilding_Name,
				lu.LpuUnitType_id,
				lu.LpuUnitType_SysNick,
				lu.LpuUnit_id,
				lu.LpuUnit_Name,
				lua.Address_Address as LpuUnit_Address,
				ls.LpuSection_id,
				ls.LpuSection_Name,
				ls.LpuSectionProfile_id,
				AllRows.MedService_Name,
				AllRows.MedService_Nick,
				mst.MedServiceType_id,
				mst.MedServiceType_SysNick,
				AllRows.UslugaComplex_id,
				AllRows.UslugaComplex_Code,
				AllRows.UslugaComplex_Name
				,ttms.TimetableMedService_id as TimetableMedService_id
				,ttms.MedService_id as ttms_MedService_id
				,CONVERT(varchar(10), ttms.TimetableMedService_begTime, 104) +' '
				+CONVERT(varchar(5), ttms.TimetableMedService_begTime, 108) as TimetableMedService_begTime
				,AllRows.isComposite
				,AllRows.lab_MedService_id
				,AllRows.pzm_MedService_id
				,AllRows.pzm_UslugaComplexMedService_id
				,AllRows.pzm_Lpu_id
				,AllRows.pzm_MedServiceType_id
				,AllRows.pzm_MedServiceType_SysNick
				,AllRows.pzm_MedService_Name
				,AllRows.pzm_MedService_Nick
				,CONVERT(varchar(10), ttms.TimetableResource_begTime, 104) +' '+CONVERT(varchar(5), ttms.TimetableResource_begTime, 108) as TimetableResource_begTime
				,ttms.TimetableResource_id
				,ttms.Resource_id
				,ttms.Resource_Name
				,ttms.Resource_id as ttr_Resource_id
				,AllRows.withResource
				-- end select
			from
				-- from
				#AllRows AllRows
				inner join v_LpuSection user_ls with (nolock) on user_ls.LpuSection_id = :LpuSection_id
				inner join v_LpuUnit user_lu with (nolock) on user_lu.LpuUnit_id = user_ls.LpuUnit_id
				inner join v_MedServiceType mst with (nolock) on mst.MedServiceType_id = AllRows.MedServiceType_id
				inner join v_Lpu lpu with (nolock) on lpu.Lpu_id = {$LpuKeyField}
				inner join v_LpuUnit lu with (nolock) on lu.LpuUnit_id = {$LpuUnitKeyField}
				inner join v_LpuSection ls with (nolock) on ls.LpuSection_id = {$LpuSectionKeyField}
				left join v_Address lua with (nolock) on lua.Address_id = isnull(lu.Address_id, lpu.UAddress_id)
				outer apply (
					SELECT top 1 * FROM (
						SELECT top 1
							null as TimetableMedService_id,
							ucr.UslugaComplexMedService_id,
							r.MedService_id,
							null as TimetableMedService_begTime,
							ttr.TimetableResource_id,
							ttr.TimetableResource_begTime,
							r.Resource_id,
							r.Resource_Name
						FROM
							{$all_ttr_join}
						WHERE
							{$all_ttr_filters}
							and AllRows.pzm_MedService_id is null
							and ucr.UslugaComplexMedService_id = AllRows.UslugaComplexMedService_id
						ORDER BY {$typeOrderRes}

						union

						SELECT top 1
							ttms.TimetableMedService_id,
							ttms.UslugaComplexMedService_id,
							ttms.MedService_id,
							ttms.TimetableMedService_begTime,
							null as TimetableResource_id,
							null as TimetableResource_begTime,
							null as Resource_id,
							null as Resource_Name
						FROM
							{$all_ttms_join}
						WHERE
							{$all_ttms_filters}
							and AllRows.pzm_MedService_id is null
							and ttms.MedService_id = AllRows.MedService_id
							and ttms.UslugaComplexMedService_id is null
						ORDER BY {$typeOrder}

						union

						SELECT top 1
							ttms.TimetableMedService_id,
							ttms.UslugaComplexMedService_id,
							ttms.MedService_id,
							ttms.TimetableMedService_begTime,
							null as TimetableResource_id,
							null as TimetableResource_begTime,
							null as Resource_id,
							null as Resource_Name
						FROM
							{$all_ttms_join}
						WHERE
							{$all_ttms_filters}
							and AllRows.pzm_MedService_id is null
							and ttms.MedService_id = AllRows.MedService_id
							and ttms.UslugaComplexMedService_id = AllRows.UslugaComplexMedService_id
						ORDER BY {$typeOrder}

						union

						SELECT top 1
							ttms.TimetableMedService_id,
							ttms.UslugaComplexMedService_id,
							ttms.MedService_id,
							ttms.TimetableMedService_begTime,
							null as TimetableResource_id,
							null as TimetableResource_begTime,
							null as Resource_id,
							null as Resource_Name
						FROM
							{$all_ttms_join}
						WHERE
							{$all_ttms_filters}
							and AllRows.pzm_MedService_id is not null
							and ttms.MedService_id = AllRows.pzm_MedService_id
							and ttms.UslugaComplexMedService_id is null
						ORDER BY {$typeOrder}

						union

						SELECT top 1
							ttms.TimetableMedService_id,
							ttms.UslugaComplexMedService_id,
							ttms.MedService_id,
							ttms.TimetableMedService_begTime,
							null as TimetableResource_id,
							null as TimetableResource_begTime,
							null as Resource_id,
							null as Resource_Name
						FROM
							{$all_ttms_join}
						WHERE
							{$all_ttms_filters}
							and AllRows.pzm_MedService_id is not null
							and ttms.MedService_id = AllRows.pzm_MedService_id
							and ttms.UslugaComplexMedService_id = AllRows.pzm_UslugaComplexMedService_id
						ORDER BY {$typeOrder}
					) ttms2
					ORDER BY case when ttms2.UslugaComplexMedService_id is not null then 0 else 1 end
				) ttms
				-- end from
			ORDER BY
				-- order by
				{$orderBy}
				-- end order by

			SET NOCOUNT OFF;
		";

		//echo getDebugSQL(getLimitSQLPH($sql, $data['start'], $data['limit']), $params); die;
		$result = $this->db->query(getLimitSQLPH($sql, $data['start'], $data['limit']), $params);

		if (is_object($result))
		{
			$response = array();
			$response['data'] = $result->result('array');
			// Если количество записей запроса равно limit, то, скорее всего еще есть страницы и каунт надо посчитать
			if (count($response['data'])==$data['limit'])
			{
				// todo: Здесь с каунтом надо подумать, слишком долго он считается
				// считаем каунт запроса по БД
				$result_count = $this->db->query(getCountSQLPH($sql), $params);
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
			} else { // Иначе считаем каунт по реальному количеству + start
				$count = $data['start'] + count($response['data']);
			}
			$response['totalCount'] = $count;
			return $response;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Загрузка списка услуг для апи
	 */
	function getUslugaComplexSelectListForApi($data) {

		$userLpu = $this->getFirstRowFromQuery("
			SELECT
                user_ls.Lpu_id as userLpu_id,
                user_lu.LpuBuilding_id as userLpuBuilding_id,
                user_ls.LpuUnit_id as userLpuUnit_id
			FROM v_LpuSection user_ls with (nolock)
			inner join v_LpuUnit user_lu with (nolock) on user_lu.LpuUnit_id = user_ls.LpuUnit_id
			WHERE user_ls.LpuSection_id = :LpuSection_id
		", $data);

		$data = array_merge($data, $userLpu);

		$data['MedServiceTypeWithResources'] = array('func');
		$this->load->library('sql/UslugaComplexSelectListSqlBuilder');

		if (!UslugaComplexSelectListSqlBuilder::setData($data)) {
			return array('Error_Msg' => UslugaComplexSelectListSqlBuilder::$error);
		}

		$sql = UslugaComplexSelectListSqlBuilder::getSqlForApi($data);
		$params = UslugaComplexSelectListSqlBuilder::getSqlParams();

		//echo '<pre>',print_r( getDebugSQL(getLimitSQLPH($sql, $data['start'], $data['limit']), $params)),'</pre>'; die();
		$result = $this->db->query(getLimitSQLPH($sql, $data['start'], $data['limit']), $params, true);
		$response['data'] = $result->result('array');

		return $response;
	}

	/**
	 * Загрузка данных в грид услуг левой части формы добавления назначений услуг
	 */
	function getUslugaComplexSelectList($data)
	{
		// обработка фильтров грида
		if (!empty($data['filter'])) {
			foreach($data['filter'] as $oneFilter) {
				if ($oneFilter['property'] == 'UslugaComplex_Name' && !empty($oneFilter['value'])) {
					// поиск по коду или наименованию услуги
					$data['filterByUslugaComplex_str'] = str_replace(array('%', '_'), '', $oneFilter['value']);
				}
				if ($oneFilter['property'] == 'UslugaComplex_id' && !empty($oneFilter['value'])) {
					// поиск по коду или наименованию услуги
					$data['filterByUslugaComplex_id'] = floatval($oneFilter['value']);
				}
			}
		}
		$params = array(
			'LpuSection_id' => $data['userLpuSection_id'],
		);
		$sql = "
			Select
                user_ls.Lpu_id,
                user_lu.LpuBuilding_id,
                user_ls.LpuUnit_id
			from v_LpuSection user_ls with (nolock)
			inner join v_LpuUnit user_lu with (nolock) on user_lu.LpuUnit_id = user_ls.LpuUnit_id
			where user_ls.LpuSection_id = :LpuSection_id
		";
		$result = $this->db->query($sql, $params);
		if (is_object($result))
		{
			$rc = $result->result('array');
			if (count($rc)>0 && is_array($rc[0])) {
				$data['userLpu_id'] = $rc[0]['Lpu_id'];
				$data['userLpuBuilding_id'] = $rc[0]['LpuBuilding_id'];
				$data['userLpuUnit_id'] = $rc[0]['LpuUnit_id'];
			}
			else if (isset($data['session']['CurARM']['ARMType']) && ($data['session']['CurARM']['ARMType'] == 'reanimation'))//BOB - 02.07.2019
			{
				$params = array(
					'MedStaffFact_id' => $data['session']['CurMedStaffFact_id'],
				);
				$sql = "
					Select
						user_ls.Lpu_id,
						user_ls.LpuBuilding_id,
						user_ls.LpuUnit_id,
						user_ls.LpuSection_id
					from MedStaffFactCache user_ls with (nolock)
					where user_ls.MedStaffFact_id = :MedStaffFact_id
				";
				$result = $this->db->query($sql, $params);
				if (is_object($result)) {
					$rc = $result->result('array');
					if (count($rc)>0 && is_array($rc[0])) {
						$data['userLpuSection_id'] = $rc[0]['LpuSection_id'];
						$data['userLpu_id'] = $rc[0]['Lpu_id'];
						$data['userLpuBuilding_id'] = $rc[0]['LpuBuilding_id'];
						$data['userLpuUnit_id'] = $rc[0]['LpuUnit_id'];
					}
				}
			}
			//BOB - 02.07.2019
		}
		$data['MedServiceTypeWithResources'] = array('func');

		if (!empty($data['filterByMedService_id'])) {
			$data['MedServiceType_SysNick'] = $this->getFirstResultFromQuery("
				select top 1 MST.MedServiceType_SysNick
				from v_MedService MS with(nolock)
				left join v_MedServiceType MST with(nolock) on MST.MedServiceType_id = MS.MedServiceType_id
				where MS.MedService_id = :MedService_id
			", array('MedService_id' => $data['filterByMedService_id']));
		}
		if(!empty($data['formMode']) && $data['formMode']=='ExtJS6') {
			$data['composition_type'] = 'composition_tests';
		}
		$this->load->library('sql/UslugaComplexSelectListSqlBuilder');
		if (!UslugaComplexSelectListSqlBuilder::setData($data)) {
			//log_message('debug', 'BOB_$data = '.print_r(UslugaComplexSelectListSqlBuilder::$error, 1));//BOB - 02.07.2019
			//echo '<pre>'.'UslugaComplexSelectListSqlBuilder::$error=' . print_r(UslugaComplexSelectListSqlBuilder::$error, 1) . '</pre>';//BOB - 02.07.2019
			//UslugaComplexSelectListSqlBuilder::$error
			return false;
		}
		$sql = UslugaComplexSelectListSqlBuilder::getSql($data);
		$params = UslugaComplexSelectListSqlBuilder::getSqlParams();

		if ($data['filterByLpu_id'] != $data['Lpu_id']) {
			$sql = str_replace('--IsThisLPU', 'and (isnull(mss.MedService_IsThisLPU, 1) != 2)', $sql);
		}

		// http://redmine.swan.perm.ru/issues/84104
		// для реализации отложенной подгрузки услуг для службы - сначала грузим по одной услуге для группы, т.к. грид работает по услугам
		// при указанной MedService_id загрузка пойдет обычным образом и подгрузит данные по группе на форму
		if(!empty($data['groupByMedService']) && empty($data['MedService_id']))
		{
			$this->load->library('swCache', array('use'=>'mongo'));
			$cacheQueryKey = md5(getDebugSql($sql, $params) . ', start: ' . $data['start'] . ', limit: ' . $data['limit']);
			$cacheObject = '_getUslugaComplexSelectList' . '_' . $cacheQueryKey;
			// Читаем из кэша
			if ($resCache = $this->swcache->getMulti($cacheObject)) {
				return $resCache;
			} else {
				$result = $this->db->query($sql, $params, true);
				if (is_object($result)) {
					$response = array();
					$respData = $result->result('array');
					$uniqRecs = array();
					$uniqGroup = array();
					foreach ($respData as $key => $value) {
						if (!in_array($value['Group_id'], $uniqGroup)) {
							// если у нас стоит фильтрация по lpu_id ($data['filterByLpu_id']), то показываем только те
							// пункты забора, которые принадлежат этой МО https://jira.is-mis.ru/browse/PROMEDWEB-5261
							// но также было замечено, что у пунктов забора присутсвует поле pzm_Lpu_id,
							// а у лабораторий - нет
							$add = true;
							if (!empty($data['filterByLpu_id']) 
								&& isset($value['pzm_Lpu_id']) 
								&& !(
									$value['pzm_Lpu_id'] == $data['filterByLpu_id']
									|| (
										empty($value['pzm_Lpu_id']) 
										&& stripos('lab', $value['MedServiceType_SysNick']) !== false
									)
								)
							) {
								$add = false;
							}
							
							if ($add) {
								array_push($uniqGroup, $value['Group_id']);
								array_push($uniqRecs, $value);	
							}
						}
					}
					unset($respData);
					$response['data'] = $uniqRecs;
					$count = count($response['data']);
					$response['totalCount'] = $count;

					if ($count > $data['limit']) {
						$response['data'] = array();
						$bound = (($data['start'] + $data['limit']) > $count) ? $count : ($data['start'] + $data['limit']);
						for ($i = $data['start']; $i < $bound; $i++) {
							array_push($response['data'], $uniqRecs[$i]);
						}
					}

					// кэшируем на 10 минут
					$this->swcache->setMulti($cacheObject, $response, array('ttl' => 600));

					return $response;
				} else {
					return false;
				}
			}
		}

		//echo getDebugSQL(getLimitSQLPH($sql, $data['start'], $data['limit']), $params); die;
		//echo '<pre>',print_r( getDebugSQL(getLimitSQLPH($sql, $data['start'], $data['limit']), $params)),'</pre>'; die();
		$result = $this->db->query(getLimitSQLPH($sql, $data['start'], $data['limit']), $params, true);

		if (is_object($result))
		{
			$response = array();
			$response['data'] = $result->result('array');
			if (!empty($data['formMode']) && $data['formMode'] == 'ExtJS6') {
				// в новой форме кол-во записей не нужно
			} else {
				// Если количество записей запроса равно limit, то, скорее всего еще есть страницы и каунт надо посчитать
				if (count($response['data']) == $data['limit']) {
					// считаем каунт запроса по БД
					$result_count = $this->db->query(getCountSQLPH($sql), $params, true);
					//echo getDebugSQL(getCountSQLPH($sql), $params); die;
					if (is_object($result_count)) {
						$cnt_arr = $result_count->result('array');
						$count = $cnt_arr[0]['cnt'];
						unset($cnt_arr);
					} else {
						$count = 0;
					}
				} else { // Иначе считаем каунт по реальному количеству + start
					$count = $data['start'] + count($response['data']);
				}
				$response['totalCount'] = $count;
			}

			if (!empty($response['data']) && !empty($data['Evn_id'])) {
				if (empty($data['allowedUslugaComplexAttributeList'])) {
					return array('Error_Msg' => 'Не указаны атрибуты услуги');
				}
				$allowedUslugaComplexAttributeList = json_decode($data['allowedUslugaComplexAttributeList'], true);
				if (!is_array($allowedUslugaComplexAttributeList) || count($allowedUslugaComplexAttributeList) != 1) {
					return array('Error_Msg' => 'Неправильный формат атрибутов услуги');
				}

				$ep_join = null;
				$tt_join = null;
				$tt_fields = null;
				if (in_array('lab', $allowedUslugaComplexAttributeList)) {
					$ep_join = 'inner join v_EvnPrescrLabDiag EPU with (nolock) on EPU.EvnPrescrLabDiag_id = ep.EvnPrescr_id';
					$tt_join = 'left join v_TimetableMedService_lite ttms (nolock) on ttms.EvnDirection_id = epd.EvnDirection_id
								outer apply(
									select top 1
										eup.EvnUslugaPar_id
									from
										v_EvnUslugaPar eup (nolock)
										left join v_EvnPrescrLabDiag epr with (nolock) on epr.EvnPrescrLabDiag_id = eup.EvnPrescr_id
									where
										eup.EvnDirection_id = epd.EvnDirection_id
								) as eu';
					$tt_fields = "
						CONVERT(varchar(10), ttms.TimetableMedService_begTime, 104) + ' ' +CONVERT(varchar(5), ttms.TimetableMedService_begTime, 108) as TimetableMedService_begTime,
						eu.EvnUslugaPar_id,
						ttms.TimetableMedService_id,
						null as TimetableResource_begTime,
					";
				} else if (in_array('func', $allowedUslugaComplexAttributeList)) {
					$ep_join = 'inner join v_EvnPrescrFuncDiagUsluga EPU with (nolock) on EPU.EvnPrescrFuncDiag_id = ep.EvnPrescr_id';
					$tt_join = 'left join v_TimetableResource_lite ttr (nolock) on ttr.EvnDirection_id = epd.EvnDirection_id';
					$tt_fields = "
						null as TimetableMedService_begTime,
						CONVERT(varchar(10), ttr.TimetableResource_begTime, 104) + ' ' +CONVERT(varchar(5), ttr.TimetableResource_begTime, 108) as TimetableResource_begTime,
					";
				} else if (in_array('consult', $allowedUslugaComplexAttributeList)) {
					$ep_join = 'inner join v_EvnPrescrConsUsluga EPU with (nolock) on EPU.EvnPrescrConsUsluga_id = ep.EvnPrescr_id';
					$tt_join = 'left join v_TimetableMedService_lite ttms (nolock) on ttms.EvnDirection_id = epd.EvnDirection_id';
					$tt_fields = "
						CONVERT(varchar(10), ttms.TimetableMedService_begTime, 104) + ' ' +CONVERT(varchar(5), ttms.TimetableMedService_begTime, 108) as TimetableMedService_begTime,
						null as TimetableResource_begTime,
					";
				}

				if (!empty($ep_join)) {
					// у случая могли быть уже назначения, надо их получить и связать с услугами
					$resp_saved = $this->queryResult("
						select
							ep.EvnPrescr_id,
							EP.EvnPrescr_pid,
							EP.EvnPrescr_CountComposit as compositionCntChecked,
							case WHEN ISNULL(ep.EvnPrescr_IsCito,1) = 2 then 1 else null end as UslugaComplex_IsCito,
							epd.EvnDirection_id,
							uc.UslugaComplex_id,
						    {$tt_fields}
							ms.MedService_Nick,
							ms.MedService_Name,
							l.Lpu_Nick,
							lu.LpuUnit_Name,
							lua.Address_Address as LpuUnit_Address,
							ms.MedService_id,
							ms.Lpu_id,
							ep.MedService_id as MedService_prescrid,
							msp.Lpu_id as Lpu_prescrid,
							null as UslugaComplex_IsFavorite,
						    st.StudyTarget_id,
							st.StudyTarget_Name,
							null as EvnStatus_SysNick
						from
							v_EvnPrescr ep (nolock)
							{$ep_join}
							inner join v_UslugaComplex UC with (nolock) on UC.UslugaComplex_id = EPU.UslugaComplex_id
							left join v_EvnPrescrDirection epd (nolock) on epd.EvnPrescr_id = ep.EvnPrescr_id
							left join v_EvnDirection_all ed (nolock) on ed.EvnDirection_id = epd.EvnDirection_id
							{$tt_join}
							left join v_MedService ms with (nolock) on ms.MedService_id = ed.MedService_id
							left join v_MedService msp with (nolock) on msp.MedService_id = ep.MedService_id
							left join v_Lpu l with (nolock) on ms.Lpu_id = l.Lpu_id
							left join v_LpuUnit lu with (nolock) on lu.LpuUnit_id = ms.LpuUnit_id
							left join v_Address lua with (nolock) on lua.Address_id = isnull(lu.Address_id,l.UAddress_id)
							left join v_StudyTarget st with (nolock) on st.StudyTarget_id = ep.StudyTarget_id
						where
							ep.EvnPrescr_pid = :Evn_id
							and ed.Lpu_cid is NULL
							and epd.EvnDirection_id is not NULL
					", array(
						'Evn_id' => $data['Evn_id']
					));

					if($this->usePostgreLis
						&& isset($data['allowedUslugaComplexAttributeList'])
						&& $data['allowedUslugaComplexAttributeList'] == "[\"lab\"]"
						&& isset($data['groupByMedService'])
						&& $data['groupByMedService'] == 0) {
						$this->load->model('Template_model', 'Template_model');

						$getPrescrParams =  array(
							'object' => 'EvnPrescrLabDiag',
							'object_id' => 'EvnPrescrLabDiag_id',
							'object_value' => 5,
							'parent_object' => 'EvnPrescr',
							'parent_object_id' => 'EvnPrescr_pid',
							'parent_object_value' => $data['Evn_id'],//При записи Evn_id присваивается в EvnPrescr_pid
							'param_name' => 'section',
							'param_value' => 'EvnPrescrPolka',
							'EvnDirection_pid' => $data['Evn_id'],
							'param_del_value' => 'EvnVizitPL'
						);

						$data = array_merge($data, $getPrescrParams);
						$lisResponse = $this->Template_model->getEvnPrescrData($data);
						$lisData = array();
						foreach ($lisResponse as $direct) {
							if(isset($direct['EvnStatus_SysNick']))
								$lisData[$direct['EvnPrescr_id']] = $direct;
						}
					}

					$savedArray = array();
					foreach ($resp_saved as $key => $value) {
						$keyUsl = $value['UslugaComplex_id'];
						if (!empty($data['filterByUslugaComplex_id'])) {
							$keyUsl .= '_' . $value['Lpu_prescrid']; // учитываем МО
						}
						if (!empty($data['MedService_id'])) {
							$keyUsl .= '_' . $value['MedService_prescrid']; // учитываем службу
						}

						if(isset($lisData)) {
							if(isset($lisData[$value['EvnPrescr_id']])){
								$value['EvnStatus_SysNick'] = $lisData[$value['EvnPrescr_id']]['EvnStatus_SysNick'];
							} else if(!isset($value['TimetableMedService_begTime'])){
								unset($value['EvnPrescr_id']);
								unset($value['EvnPrescr_pid']);
								unset($value['EvnDirection_id']);
								unset($value['EvnUslugaPar_id']);
								unset($value['EvnStatus_SysNick']);
							}
						}

						if (empty($value['EvnDirection_id'])) {
							// если нет данных по направлению то и эти поля менять не надо
							unset($value['EvnDirection_id']);
							unset($value['TimetableMedService_begTime']);
							unset($value['TimetableResource_begTime']);
							unset($value['MedService_Nick']);
							unset($value['MedService_Name']);
							unset($value['Lpu_Nick']);
							unset($value['LpuUnit_Name']);
							unset($value['LpuUnit_Address']);
							unset($value['MedService_id']);
							unset($value['Lpu_id']);
						}

						unset($value['MedService_prescrid']);
						unset($value['Lpu_prescrid']);

						$savedArray[$keyUsl] = $value;
					}

					foreach ($response['data'] as $key => $value) {
						$keyUsl = $value['UslugaComplex_id'];
						if (!empty($data['filterByUslugaComplex_id'])) {
							$keyUsl .= '_' . $value['Lpu_id'];
						}
						if (!empty($data['MedService_id'])) {
							if (!empty($value['pzm_MedService_id'])) {
								$keyUsl .= '_' . $value['pzm_MedService_id'];
							} else {
								$keyUsl .= '_' . $value['MedService_id'];
							}
						}
						if (isset($savedArray[$keyUsl])) {
							$response['data'][$key] = array_merge($response['data'][$key], $savedArray[$keyUsl]);
							$response['data'][$key]['UslugaComplexMedService_HasPrescr'] = 1;
						}
					}
				}
			}

			return $response;
		}
		else
		{
			return false;
		}
	}

	/**
	 *  Загружает список услуг служб для назначения лабораторной диагностики
	 */
	function getUslugaComplexMedServiceList($data)
	{
		$filter = 'ms.Lpu_id = :Lpu_id
		and ms.MedServiceType_id = 6
		and ucms.UslugaComplexMedService_pid IS NULL -- только 0 уровня
		and (ms.MedService_endDT is null OR cast(ms.MedService_endDT as date) > cast(GETDATE() as date))';
		$add_join = '';
		$params = array(
			'Lpu_id' => $data['Lpu_id'],
			'LpuSection_id' => $data['LpuSection_id'],
		);

		if (!empty($data['uslugaCategoryList'])) {
			$uslugaCategoryList = json_decode($data['uslugaCategoryList'], true);
			if ( is_array($uslugaCategoryList) && count($uslugaCategoryList) > 0 ) {
				$filter .= " and cat.UslugaCategory_SysNick in ('" . implode("', '", $uslugaCategoryList) . "')";
			}
		}

		if ( !empty($data['allowedUslugaComplexAttributeList']) ) {
			$allowedUslugaComplexAttributeList = json_decode($data['allowedUslugaComplexAttributeList'], true);

			if ( is_array($allowedUslugaComplexAttributeList) && count($allowedUslugaComplexAttributeList) > 0 ) {
				$filter .= " and exists (
					select t1.UslugaComplexAttribute_id
					from v_UslugaComplexAttribute t1 with (nolock)
						inner join v_UslugaComplexAttributeType t2 with (nolock) on t2.UslugaComplexAttributeType_id = t1.UslugaComplexAttributeType_id
					where t1.UslugaComplex_id = uc.UslugaComplex_id
						and t2.UslugaComplexAttributeType_SysNick in ('" . implode("', '", $allowedUslugaComplexAttributeList) . "')
				)";
			}
		}

		$sql = "
			select
				-- select
				ms.MedService_id,
				ucms.UslugaComplexMedService_id,
				uc.UslugaComplex_id,
				ms.Lpu_id,
				ms.LpuBuilding_id,
				lu.LpuUnitType_id,
				ms.LpuUnit_id,
				ms.LpuSection_id,
				ls.LpuSectionProfile_id,
				ms.MedServiceType_id,
				lu.LpuUnitType_SysNick,
				ms.MedService_Name,
				ms.MedService_Nick,
				mst.MedServiceType_SysNick,
				uc.UslugaComplex_Name
				,case when exists(
					select top 1 ucms2.UslugaComplexMedService_id
					from v_UslugaComplexMedService ucms2 with (nolock)
						inner join lis.v_AnalyzerTest at2 (nolock) on at2.UslugaComplexMedService_id = ucms2.UslugaComplexMedService_id
						inner join lis.v_Analyzer a2 (nolock) on a2.Analyzer_id = at2.Analyzer_id
					where
						ucms2.UslugaComplexMedService_pid = ucms.UslugaComplexMedService_id
						and ISNULL(at2.AnalyzerTest_IsNotActive, 1) = 1 and ISNULL(a2.Analyzer_IsNotActive, 1) = 1
				) then 1 else 0 end as isComposite
				,pzm.MedService_id as pzm_MedService_id
				,pzm.MedService_Name as pzm_MedService_Name
				,pzm.MedService_Nick as pzm_MedService_Nick
				,pzm.MedServiceType_id as pzm_MedServiceType_id
				,pzm.MedServiceType_SysNick as pzm_MedServiceType_SysNick
				,ttms.MedService_id as ttms_MedService_id
				-- end select
			from
				-- from
				v_MedService ms with (nolock)
				inner join v_LpuSection user_ls with (nolock) on user_ls.LpuSection_id = :LpuSection_id
				inner join v_LpuUnit user_lu with (nolock) on user_lu.LpuUnit_id = user_ls.LpuUnit_id
				inner join v_UslugaComplexMedService ucms with (nolock) on ucms.MedService_id = ms.MedService_id
				inner join lis.v_AnalyzerTest at (nolock) on at.UslugaComplexMedService_id = ucms.UslugaComplexMedService_id
				inner join lis.v_Analyzer a (nolock) on a.Analyzer_id = at.Analyzer_id
				inner join v_UslugaComplex uc with (nolock) on uc.UslugaComplex_id = ucms.UslugaComplex_id
				inner join v_UslugaCategory cat with (nolock) on cat.UslugaCategory_id = uc.UslugaCategory_id
				inner join v_LpuUnit lu with (nolock) on lu.LpuUnit_id = ms.LpuUnit_id
				-- inner джойном исключаем криво заведенные службы
				inner join v_LpuSection ls with (nolock) on ms.LpuSection_id = ls.LpuSection_id
				inner join v_MedServiceType mst with (nolock) on ms.MedServiceType_id = mst.MedServiceType_id
				outer apply (
					select top 1
						pzm.MedService_id,
						pzm.MedService_Name,
						pzm.MedService_Nick,
						pzm.MedServiceType_id,
						mstpzm.MedServiceType_SysNick
					from v_MedServiceLink msl with (nolock)
					inner join v_MedService pzm with (nolock) on msl.MedService_id = pzm.MedService_id
					inner join v_MedServiceType mstpzm with (nolock) on pzm.MedServiceType_id = mstpzm.MedServiceType_id
					where msl.MedService_lid = ms.MedService_id
					and pzm.Lpu_id = ms.Lpu_id
					and msl.MedServiceLinkType_id = 1
					and mstpzm.MedServiceType_SysNick like 'pzm'
					and (pzm.MedService_endDT is null OR cast(pzm.MedService_endDT as date) > cast(GETDATE() as date))
				) pzm
				outer apply (
					select top 1
						MedService_id
					from v_TimetableMedService_lite with (nolock)
					where Person_id is null
					and (MedService_id = pzm.MedService_id OR UslugaComplexMedService_id = ucms.UslugaComplexMedService_id OR MedService_id = ucms.MedService_id)
					and TimetableMedService_begTime >= GETDATE()
				) ttms
				{$add_join}
				-- end from
			WHERE
				-- where
				{$filter}
				and ISNULL(at.AnalyzerTest_IsNotActive, 1) = 1 and ISNULL(a.Analyzer_IsNotActive, 1) = 1
				-- end where
			ORDER BY
				-- order by
				case when ms.LpuBuilding_id=user_lu.LpuBuilding_id then null else isnull(lu.LpuBuilding_Name,'-') end,
				uc.UslugaComplex_Name
				-- end order by
		";

		//echo getDebugSQL($sql, $params);
		$result = $this->db->query($sql, $params);
		if (is_object($result))
		{
			return $result->result('array');
		}
		else
		{
			return false;
		}
	}

	/**
	 *  Загружает состав услуг в меню
	 */
	function loadCompositionMenu($data)
	{
		if (empty($data['UslugaComplexMedService_pid']) && !empty($data['MedService_pid']) && !empty($data['UslugaComplex_pid']) ) {
			$sql = "
				SELECT TOP 1 UslugaComplexMedService_id
				FROM v_UslugaComplexMedService UCMS
				WHERE UCMS.UslugaComplex_id = :UslugaComplex_id AND UCMS.MedService_id = :MedService_id
			";
			$res = $this->getFirstRowFromQuery($sql, array(
				'UslugaComplex_id' => $data['UslugaComplex_pid'],
				'MedService_id' => $data['MedService_pid'],
			));
			$data['UslugaComplexMedService_pid'] = $res?$res['UslugaComplexMedService_id']:false;
		}
		if (!empty($data['UslugaComplexMedService_pid'])) {
			$params = array(
				'UslugaComplexMedService_pid' => $data['UslugaComplexMedService_pid'],
				'Lpu_id' => $data['Lpu_id']
			);
			$add_join = "";
			$select_code = 'UC.UslugaComplex_Code';
			$select_name = 'coalesce(ATEST.AnalyzerTest_SysNick, UCMS.UslugaComplex_Name, UC.UslugaComplex_Name) as UslugaComplex_Name';
			if (2 == $this->options['prescription']['service_name_show_type']) {
				//Отображение наименований услуг из Справочник ГОСТ-2011
				// в v_UslugaComplexMedService.UslugaComplex_id может быть услуга не из ГОСТ-2011
				$add_join = 'inner join v_UslugaComplex UC2011 with (nolock) on UC2011.UslugaComplex_id = UC.UslugaComplex_2011id';
				$select_code = 'UC2011.UslugaComplex_Code';
				$select_name = 'UC2011.UslugaComplex_Name';
			}
			if ($this->options['prescription']['enable_show_service_nick']) {
				//Отображать состав лабораторной услуги (тесты) при помощи кратких наименований услуг, при наличии
				if (2 == $this->options['prescription']['service_name_show_type']) {
					//из Справочник ГОСТ-2011
					$select_name = "coalesce(UC2011.UslugaComplex_Nick, UC2011.UslugaComplex_Name, '') as UslugaComplex_Name";
				} else {
					$select_name = 'coalesce(ATEST.AnalyzerTest_SysNick, UCMS.UslugaComplex_Name, UC.UslugaComplex_Nick, UC.UslugaComplex_Name) as UslugaComplex_Name';
				}
			}
			$sql = "
				select
					UC.UslugaComplex_id,
					{$select_code},
					{$select_name}
				from v_UslugaComplexMedService UCMS with (nolock)
				inner join v_UslugaComplex UC with (nolock) on UCMS.UslugaComplex_id = UC.UslugaComplex_id
				{$add_join}
				cross apply(
					select top 1
						at_child.AnalyzerTest_SortCode,
						at_child.AnalyzerTest_id,
						ISNULL(at_child.AnalyzerTest_SysNick, uc.UslugaComplex_Name) as AnalyzerTest_SysNick
					from
						lis.v_AnalyzerTest at_child (nolock)
						inner join lis.v_AnalyzerTest at (nolock) on at.AnalyzerTest_id = at_child.AnalyzerTest_pid
						inner join lis.v_Analyzer a (nolock) on a.Analyzer_id = at.Analyzer_id
						left join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = at_child.UslugaComplex_id
					where
						at_child.UslugaComplexMedService_id = ucms.UslugaComplexMedService_id
						and at.UslugaComplexMedService_id = ucms.UslugaComplexMedService_pid
						and ISNULL(at_child.AnalyzerTest_IsNotActive, 1) = 1
						and ISNULL(at.AnalyzerTest_IsNotActive, 1) = 1
						and ISNULL(a.Analyzer_IsNotActive, 1) = 1
						and (at_child.AnalyzerTest_endDT >= dbo.tzGetDate() or at_child.AnalyzerTest_endDT is null)
						and (uc.UslugaComplex_endDT >= dbo.tzGetDate() or uc.UslugaComplex_endDT is null)
				) ATEST -- фильтрация услуг по активности тестов связанных с ними
				where UCMS.UslugaComplexMedService_pid = :UslugaComplexMedService_pid
				order by ISNULL(ATEST.AnalyzerTest_SortCode, 999999999)
			";
		} else if (!empty($data['UslugaComplex_pid'])) {
			$params = array('UslugaComplex_pid' => $data['UslugaComplex_pid']);
			$add_join = "";
			$select_code = 'UC.UslugaComplex_Code';
			$select_name = 'UC.UslugaComplex_Name';
			if (2 == $this->options['prescription']['service_name_show_type']) {
				//Отображение наименований услуг из Справочник ГОСТ-2011
				$add_join = 'inner join v_UslugaComplex UC2011 with (nolock) on UC2011.UslugaComplex_id = UC.UslugaComplex_2011id';
				$select_code = 'UC2011.UslugaComplex_Code';
				$select_name = 'UC2011.UslugaComplex_Name';
			}
			if ($this->options['prescription']['enable_show_service_nick']) {
				//Отображать состав лабораторной услуги (тесты) при помощи кратких наименований услуг, при наличии
				if (2 == $this->options['prescription']['service_name_show_type']) {
					//из Справочник ГОСТ-2011
					$select_name = "coalesce(UC2011.UslugaComplex_Nick, UC2011.UslugaComplex_Name, '') as UslugaComplex_Name";
				} else {
					$select_name = "coalesce(UC.UslugaComplex_Nick, UC.UslugaComplex_Name, '') as UslugaComplex_Name";
				}
			}
			$sql = "
			select
				UC.UslugaComplex_id,
				{$select_code},
				{$select_name}
			from v_UslugaComplexComposition UCC with (nolock)
			inner join v_UslugaComplex UC with (nolock) on UCC.UslugaComplex_id = UC.UslugaComplex_id
			{$add_join}
			where UCC.UslugaComplex_pid = :UslugaComplex_pid
			";
		} else {
			return false;
		}
		//echo getDebugSQL($sql, $params);die;
		$result = $this->db->query($sql, $params);
		if (is_object($result))
		{
			$resUslugaList = $result->result('array');
			// Для того, чтобы выделить выбранные пункты меню
			if(!empty($data['isExt6']) && !empty($data['EvnPrescr_id'])){
				$resUslugaList = $this->addCheckedItemsUslugaList($data,$resUslugaList);
			}
			// Для того, чтобы собрать список тестов/проб услуги в необходимом формате
			if(!empty($data['forUslugaList'])){
				$UslugaList = array();
				foreach($resUslugaList as $usl)
					$UslugaList[] = $usl['UslugaComplex_id'];
				$resUslugaList = $UslugaList;
			}
			return $resUslugaList;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Функция добавляется отметки о выделении пунктов меню услуги
	 * @param $data
	 * @param $arrUsl - полный состав родительской услуги
	 */
	function addCheckedItemsUslugaList($data, $arrUsl){
		$params = array(
			'EvnPrescrLabDiag_id' => $data['EvnPrescr_id'],
		);
		$checkedUslArr = array();
		$sql = "
			select
				UCC.UslugaComplex_id as UslugaComplex_sid
			from
				v_EvnPrescrLabDiag EP with (nolock)
				-- состав услуги, если услуга комплексная
				left join v_EvnPrescrLabDiagUsluga UCC with (nolock) on UCC.EvnPrescrLabDiag_id = EP.EvnPrescrLabDiag_id
			WHERE
				EP.EvnPrescrLabDiag_id = :EvnPrescrLabDiag_id
		";

		//echo getDebugSQL($sql, $params);
		$result = $this->db->query($sql, $params);
		if (is_object($result))
		{
			$res = $result->result('array');
			foreach($res as $usl){
				$checkedUslArr[] = $usl['UslugaComplex_sid'];
			}
			foreach($arrUsl as $key => $usl){
				$arrUsl[$key]['checkedUsl'] = in_array($usl['UslugaComplex_id'],$checkedUslArr);
			}
		}
		return $arrUsl;
	}
	/**
	 * Получение списка служб
	 */
	function loadMedServiceGrid($data)
	{
		$filter = "";
		$queryParams = array();

		if (!empty($data['MedService_id'])) {
			$filter .= " and MedService_id = :MedService_id";
			$queryParams['MedService_id'] = $data['MedService_id'];
		} else if (!empty($data['MedService_sid'])) {
			$filter .= " and MedService_id IN (select MSL.MedService_lid from v_MedServiceLink MSL with (nolock) where msl.MedService_id = :MedService_sid)";
			$queryParams['MedService_sid'] = $data['MedService_sid'];
		}

		$query = "
			select
				MedService_id,
				MedService_Name
			from
				v_MedService (nolock)
			where
				1 = 1
				{$filter}
		";

		return $this->queryResult($query, $queryParams);
	}

	/**
	 * Загружает состав услуг в дерево
	 */
	function loadCompositionTree($data)
	{
		$queryParams = array();
		$selectIsMes = 'null as UslugaComplex_isMes';
		$joinMes = '';
		$groupByMes = '';
		if (!empty($data['Mes_id']) && (isset($data['UslugaComplex_id']) || isset($data['UslugaComplexMedService_id']))) {
			$queryParams['Mes_id'] = $data['Mes_id'];
			$selectIsMes = 'case when mu.Mes_id is null then 1 else 2 end as UslugaComplex_isMes';
			$groupByMes = 'mu.Mes_id,';
			$joinMes = 'outer apply (
				select top 1 mu.Mes_id
				from v_MesUsluga mu with (nolock)
				where mu.UslugaComplex_id = UC.UslugaComplex_2011id
					and mu.Mes_id = :Mes_id
					and exists (
						select top 1 UslugaComplexTariff_id
						from v_UslugaComplexTariff with (nolock)
						where Lpu_id is null
							and UslugaComplex_id = UC.UslugaComplex_id
							and UslugaComplexTariff_UED = mu.MesUsluga_UslugaCount
					)
			) mu';
		}
		$is_for_prescription = (empty($joinMes) && empty($groupByMes));
		if (!empty($data['UslugaComplexMedService_id'])) {
			$queryParams['UslugaComplexMedService_id'] = $data['UslugaComplexMedService_id'];
			$select_code = 'UC.UslugaComplex_Code';
			$select_name = 'UC.UslugaComplex_Name';
			if ($is_for_prescription) {
				if (2 == $this->options['prescription']['service_name_show_type']) {
					//Отображение наименований услуг из Справочник ГОСТ-2011
					// в v_UslugaComplexMedService.UslugaComplex_id может быть услуга не из ГОСТ-2011
					$joinMes = 'inner join v_UslugaComplex UC2011 with (nolock) on UC2011.UslugaComplex_id = UC.UslugaComplex_2011id';
					$select_code = 'UC2011.UslugaComplex_Code';
					$select_name = 'UC2011.UslugaComplex_Name';
				}
				if ($this->options['prescription']['enable_show_service_nick']) {
					//Отображать состав лабораторной услуги (тесты) при помощи кратких наименований услуг, при наличии
					if (2 == $this->options['prescription']['service_name_show_type']) {
						//из Справочник ГОСТ-2011
						$select_name = "coalesce(UC2011.UslugaComplex_Nick, UC2011.UslugaComplex_Name, '')";
					} else {
						$select_name = 'coalesce(UC.UslugaComplex_Nick, UCMS.UslugaComplex_Name, UC.UslugaComplex_Name)';
					}
				}
			}
			$sql = "
			select
				COUNT(Childrens.UslugaComplexMedService_id) as ChildrensCount,
				'UslugaComplexMedService' as object,
				{$selectIsMes},
				UCMS.UslugaComplexMedService_id,
				UCMS.UslugaComplexMedService_pid as UslugaComplex_pid,
				UC.UslugaComplex_id,
				{$select_code},
				{$select_name} as UslugaComplex_Name
			from v_UslugaComplexMedService UCMS with (nolock)
			inner join v_UslugaComplex UC with (nolock) on UCMS.UslugaComplex_id = UC.UslugaComplex_id
			cross apply(
				select top 1
					at_child.AnalyzerTest_SortCode,
					at_child.AnalyzerTest_id
				from
					lis.v_AnalyzerTest at_child (nolock)
					inner join lis.v_AnalyzerTest at (nolock) on at.AnalyzerTest_id = at_child.AnalyzerTest_pid
					inner join lis.v_Analyzer a (nolock) on a.Analyzer_id = at.Analyzer_id
				where
					at_child.UslugaComplexMedService_id = ucms.UslugaComplexMedService_id
					and at.UslugaComplexMedService_id = ucms.UslugaComplexMedService_pid
					and ISNULL(at_child.AnalyzerTest_IsNotActive, 1) = 1
					and ISNULL(at.AnalyzerTest_IsNotActive, 1) = 1
					and ISNULL(a.Analyzer_IsNotActive, 1) = 1
					and (at_child.AnalyzerTest_endDT >= dbo.tzGetDate() or at_child.AnalyzerTest_endDT is null)
			) ATEST -- фильтрация услуг по активности тестов связанных с ними
			left join v_UslugaComplexMedService Childrens with (nolock)
				on Childrens.UslugaComplexMedService_pid = UCMS.UslugaComplexMedService_id
			{$joinMes}
			where UCMS.UslugaComplexMedService_pid = :UslugaComplexMedService_id
			group by {$groupByMes}
				UCMS.UslugaComplexMedService_id,
				UCMS.UslugaComplexMedService_pid,
				UC.UslugaComplex_id,
				{$select_code},
				{$select_name},
				ATEST.AnalyzerTest_SortCode
			order by ISNULL(ATEST.AnalyzerTest_SortCode, 999999999)
			";
		} else if (!empty($data['UslugaComplex_id'])) {
			$queryParams['UslugaComplex_id'] = $data['UslugaComplex_id'];
			$select_code = 'UC.UslugaComplex_Code';
			$select_name = 'UC.UslugaComplex_Name';
			if ($is_for_prescription) {
				if (2 == $this->options['prescription']['service_name_show_type']) {
					//Отображение наименований услуг из Справочник ГОСТ-2011
					// в v_UslugaComplexMedService.UslugaComplex_id может быть услуга не из ГОСТ-2011
					$joinMes = 'inner join v_UslugaComplex UC2011 with (nolock) on UC2011.UslugaComplex_id = UC.UslugaComplex_2011id';
					$select_code = 'UC2011.UslugaComplex_Code';
					$select_name = 'UC2011.UslugaComplex_Name';
				}
				if ($this->options['prescription']['enable_show_service_nick']) {
					//Отображать состав лабораторной услуги (тесты) при помощи кратких наименований услуг, при наличии
					if (2 == $this->options['prescription']['service_name_show_type']) {
						//из Справочник ГОСТ-2011
						$select_name = "coalesce(UC2011.UslugaComplex_Nick, UC2011.UslugaComplex_Name, '')";
					} else {
						$select_name = 'coalesce(UC.UslugaComplex_Nick, UCMS.UslugaComplex_Name, UC.UslugaComplex_Name)';
					}
				}
			}
			$sql = "
			select
				COUNT(Childrens.UslugaComplex_id) as ChildrensCount,
				'UslugaComplex' as object,
				{$selectIsMes},
				null as UslugaComplexMedService_id,
				UCC.UslugaComplex_pid,
				UC.UslugaComplex_id,
				{$select_code},
				{$select_name} as UslugaComplex_Name
			from v_UslugaComplexComposition UCC with (nolock)
			inner join v_UslugaComplex UC with (nolock) on UCC.UslugaComplex_id = UC.UslugaComplex_id
			left join v_UslugaComplexComposition Childrens with (nolock)
				on Childrens.UslugaComplex_pid = UC.UslugaComplex_id
			{$joinMes}
			where UCC.UslugaComplex_pid = :UslugaComplex_id
			group by {$groupByMes}
				UCC.UslugaComplex_pid,
				UC.UslugaComplex_id,
				{$select_code},
				{$select_name}
			order by {$select_code}
			";
		} else if (!empty($data['Mes_id']) && !empty($data['EvnUsluga_pid'])) {
			$queryParams['Mes_id'] = $data['Mes_id'];
			$queryParams['EvnUsluga_pid'] = $data['EvnUsluga_pid'];
			$sql = "
			select
				COUNT(Childrens.UslugaComplex_id) as ChildrensCount,
				'UslugaComplex' as object,
				2 as UslugaComplex_isMes,
				null as UslugaComplexMedService_id,
				mu.Mes_id as UslugaComplex_pid,
				UC.UslugaComplex_id,
				UC.UslugaComplex_Code,
				UC.UslugaComplex_Name
			from v_MesUsluga mu with (nolock)
			inner join v_UslugaComplex UC with (nolock) on UC.UslugaComplex_2011id = mu.UslugaComplex_id
			left join v_UslugaComplexComposition Childrens with (nolock)
				on Childrens.UslugaComplex_pid = UC.UslugaComplex_id
			where mu.Mes_id = :Mes_id and exists (
					select top 1 UslugaComplexTariff_id
					from v_UslugaComplexTariff with (nolock)
					where Lpu_id is null
						and UslugaComplex_id = UC.UslugaComplex_id
						and UslugaComplexTariff_UED = mu.MesUsluga_UslugaCount
				) and not exists (
					select top 1 v_EvnUsluga.EvnUsluga_id
					from v_EvnUsluga with (nolock)
					where v_EvnUsluga.EvnUsluga_pid = :EvnUsluga_pid
						and v_EvnUsluga.UslugaComplex_id = UC.UslugaComplex_id
				)
			group by
				mu.Mes_id,
				UC.UslugaComplex_id,
				UC.UslugaComplex_Code,
				UC.UslugaComplex_Name
			order by uc.UslugaComplex_Code
			";
		} else {
			return false;
		}
		//echo getDebugSQL($sql, $params);
		$result = $this->db->query($sql, $queryParams);
		if (is_object($result))
		{
			return $result->result('array');
		}
		else
		{
			return false;
		}
	}

	/**
	 *  Список лабораторий для комбобокса на форме статистики расхода реактивов
	 */
	function loadMedServiceListStat($data)
	{
		$queryParams = array();
		$queryParams['Lpu_id'] = $data['Lpu_id'];
		$queryParams['pmUser_id'] = $data['pmUser_id'];
		$where       = '';

		/*
		if ( strlen($data['query']) > 0 ) {
			$queryParams['query'] = "%" . $data['query'] . "%";
			//$where .= " AND (ms.MedService_Code + ' ' + ms.MedService_Nick) like replace(ltrim(rtrim(:query)),' ', '%') + '%'";
			$where .= " AND ( ms.MedService_Nick) like replace(ltrim(rtrim(:query)),' ', '%') + '%'";
		}
		*/

		$q = "
			SELECT ms.* from v_pmUserCache pm with (nolock)
			join dbo.MedServiceMedPersonal mp on mp.MedPersonal_id = pm.MedPersonal_id
			join dbo.v_MedService ms  with (nolock) on ms.MedService_id = mp.MedService_id
			where  ms.MedServiceType_id = 6

			AND ms.Lpu_id = :Lpu_id
			AND pm.PMUser_id = :pmUser_id
			AND MedService_begDT <= GetDate()
			AND isnull(MedService_endDT, '3000-01-01') >= Getdate()
		";

		/*
		$q = "
			SELECT
				ms.MedService_id,
				ms.MedService_Code,
				ms.MedService_Nick AS MedService_Name
				--ms.MedService_Code + ' ' + ms.MedService_Nick AS MedService_Name
			FROM v_MedService ms
			WHERE ms.MedServiceType_id = 6
				".$where."
		";
		*/
		$result = $this->db->query($q, $queryParams);
		if (is_object($result))
		{
			return $result->result('array');
		}
		else
		{
			return false;
		}
	}

	/**
	 *  Генерирует код службы (для лабораторий)
	 */
	function getMedServiceCode($data = array())
	{
		$params = array();
		$sql = "
			Select
				IsNull(max(MedService_Code),0)+1 as MedService_Code
			from MedService ms (nolock)
			Where MedServiceType_id = 6
		";
		$result = $this->db->query($sql, $params);
		if (is_object($result))
		{
			return $result->result('array');
		}
		else
		{
			return false;
		}
	}

	/**
	 *  Возвращает количество служб лабораторного типа с указанным кодом
	 */
	function checkMedServiceCode($data)
	{
		$params = array('MedService_Code'=>$data['MedService_Code']);
		$sql = "
			Select
				count(*) as record_count
			from MedService ms (nolock)
			where MedServiceType_id = 6 and MedService_Code = :MedService_Code
		";
		$result = $this->db->query($sql, $params);
		if (is_object($result))
		{
			$rc = $result->result('array');
			if (count($rc)>0 && is_array($rc[0])) {
				return $rc[0]['record_count'];
			}
		}
		return null;
	}

	/**
	 *  Меняет код для указанной службы на максимальный
	 */
	function incMedServiceCode($data)
	{
		$params = array('MedService_id'=>$data['MedService_id']);
		$sql = "
			Update MedService with (rowlock)
			set MedService_Code = (
				Select
					max(MedService_Code)+1 as MedService_Code
				from MedService ms (nolock)
				Where MedServiceType_id = 6
			)
			where MedService_id = :MedService_id
		";
		$result = $this->db->query($sql, $params);
		if (is_object($result))
		{
			return true;
		} else {
			return false;
		}
	}

	/**
	 *  Получение списка доступных уровней структуры МО для указанного типа службы
	 */
	function getAllowedMedServiceLevelTypeArray($MedServiceType_id = null) {
		$response = array();

		$query = "
			select MedServiceLevelType_id
			from v_MedServiceLevel with (nolock)
			where MedServiceType_id = :MedServiceType_id
		";
		$queryParams = array('MedServiceType_id' => $MedServiceType_id);
		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			return false;
		}

		$resultArray = $result->result('array');

		if ( is_array($resultArray) && count($resultArray) > 0 ) {
			foreach ( $resultArray as $array ) {
				$response[] = $array['MedServiceLevelType_id'];
			}
		}

		return $response;
	}

	/**
	 * Получение данных по службе
	 */
	function getMedServiceInfo( $medservice_id )
	{
		$params = array(
			$medservice_id
		);
		$sql = "
			select top 1
				MS.MedService_id,
				MS.MedService_Name,
				MS.Lpu_id,
				MS.LpuUnit_id,
				MS.LpuSection_id
			from
				v_MedService MS with (NOLOCK)
			where
				MS.MedService_id = ?
		";
		//echo getDebugSQL($sql, $params);
		$result = $this->db->query($sql, $params);
		if (is_object($result))
		{
			$result = $result->result('array');
			return (count($result) == 1 ? $result[0] : false);
		}
		else
		{
			return false;
		}
	}

	/**
	 * Получение списка служб, в которых есть склады
	 * @return boolean
	 */

	public function loadMedServiceListWithStorage($data) {

		$rules = array(
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id'),
			array('field'=>  'pmUser_id','rules' =>'required', 'label'=>'Идентификатор пользователя', 'type' => 'id'),

		);

		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;

		$query = "
			SELECT DISTINCT
				ms.MedService_id,
				ms.Server_id,
				ms.MedService_Name,
				ms.MedService_Nick,
				ms.MedServiceType_id,
				ms.Lpu_id,
				ms.LpuBuilding_id,
				ms.LpuUnitType_id,
				ms.LpuUnit_id,
				ms.LpuSection_id,
				ms.MedService_begDT,
				ms.MedService_endDT,
				ms.Org_id,
				ms.OrgStruct_id,
				mst.MedServiceType_Name MedServiceType_id_Name,
				Lpu_id_ref.Lpu_Nick Lpu_id_Nick,
				LpuBuilding_id_ref.LpuBuilding_Name LpuBuilding_id_Name,
				ISNULL(LpuBuilding_id_ref_Address_ref.Address_Address,'(нет адреса)') Address_Address,
				LpuUnitType_id_ref.LpuUnitType_Name LpuUnitType_id_Name,
				LpuUnit_id_ref.LpuUnit_Name LpuUnit_id_Name,
				LpuSection_id_ref.LpuSection_Name LpuSection_id_Name,
				Org_id_ref.Org_Name Org_id_Name
			FROM
				StorageStructLevel SSL with(nolock)
				LEFT JOIN dbo.v_MedService ms WITH ( NOLOCK )ON SSL.MedService_id = ms.MedService_id
				LEFT JOIN dbo.v_MedServiceType mst WITH ( NOLOCK ) ON mst.MedServiceType_id = ms.MedServiceType_id
				LEFT JOIN dbo.v_Lpu Lpu_id_ref WITH ( NOLOCK ) ON Lpu_id_ref.Lpu_id = ms.Lpu_id
				LEFT JOIN dbo.v_LpuBuilding LpuBuilding_id_ref WITH ( NOLOCK ) ON LpuBuilding_id_ref.LpuBuilding_id = ms.LpuBuilding_id
				LEFT JOIN dbo.v_Address LpuBuilding_id_ref_Address_ref WITH ( NOLOCK ) ON LpuBuilding_id_ref_Address_ref.Address_id = LpuBuilding_id_ref.Address_id
				LEFT JOIN dbo.v_LpuUnitType LpuUnitType_id_ref WITH ( NOLOCK ) ON LpuUnitType_id_ref.LpuUnitType_id = ms.LpuUnitType_id
				LEFT JOIN dbo.v_LpuUnit LpuUnit_id_ref WITH ( NOLOCK ) ON LpuUnit_id_ref.LpuUnit_id = ms.LpuUnit_id
				LEFT JOIN dbo.v_LpuSection LpuSection_id_ref WITH ( NOLOCK ) ON LpuSection_id_ref.LpuSection_id = ms.LpuSection_id
				LEFT JOIN dbo.v_Org Org_id_ref WITH ( NOLOCK ) ON Org_id_ref.Org_id = ms.Org_id
			WHERE
				SSL.Lpu_id = :Lpu_id
			";

		return $this->queryResult($query , $queryParams);


	}

	/**
	 * Получение данных по службе
	 */
	function getArmLevelByMedService($data)
	{
		if(!empty($data['MedService_id'])){
			$params = array('MedService_id'=>$data['MedService_id']);
		}
		$sql = "
			select top 1
				MS.Lpu_id,
				MS.LpuBuilding_id,
				LB.LpuBuilding_Name,
				MS.LpuUnit_id,
				MS.LpuSection_id,
				LS.LpuSection_Name,
				st.Storage_id,
				LB.LpuBuildingType_id,
				lb2.LpuBuildingType_id as LpuBuildingTypeByLpuSection,
				lb3.LpuBuildingType_id as LpuBuildingTypeByLpuUnit,
				st.LpuBuildingType_id as LpuBuildingTypeByStorage
			from
				v_MedService MS with (NOLOCK)
				left join v_LpuBuilding LB with (NOLOCK) on LB.LpuBuilding_id = MS.LpuBuilding_id
				left join v_LpuSection LS with (NOLOCK) on LS.LpuSection_id = MS.LpuSection_id
				left join v_LpuBuilding lb2 with (NOLOCK) on lb2.LpuBuilding_id = LS.LpuBuilding_id
				left join v_LpuUnit lu with (NOLOCK) on lu.LpuUnit_id = LS.LpuUnit_id
				left join v_LpuBuilding lb3 with (NOLOCK) on lb3.LpuBuilding_id = lu.LpuBuilding_id
				outer apply (
					select top 1
						ssl.Storage_id,
						lb4.LpuBuildingType_id
					from v_StorageStructLevel ssl with (nolock)
					left join v_LpuBuilding lb4 with (NOLOCK) on lb4.LpuBuilding_id = ssl.LpuBuilding_id
					where ssl.MedService_id = MS.MedService_id
				) st
			where
				MS.MedService_id = :MedService_id
		";
		//echo getDebugSQL($sql, $params);
		$result = $this->db->query($sql, $params);
		if (is_object($result))
		{
			$result = $result->result('array');
			return $result;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Получить права врача на данной службе (право одобрять пробы)
	 */
	function getApproveRights($data) {
		$query = "
			select top 1
				MedServiceMedPersonal_id as \"MedServiceMedPersonal_id\",
				MedService_id as \"MedService_id\",
				MedPersonal_id as \"MedPersonal_id\",
				case
					when msmp.MedServiceMedPersonal_isNotWithoutRegRights = 2
						then 1
						else 0
				end as \"MedServiceMedPersonal_isNotWithoutRegRights\",
				case
					when msmp.MedServiceMedPersonal_isNotApproveRights = 2
						then 1
						else 0
				end as \"MedServiceMedPersonal_isNotApproveRights\"
			from
				v_MedServiceMedPersonal msmp with(nolock)
			where
				msmp.MedService_id = :MedService_id
				and msmp.MedPersonal_id = :MedPersonal_id
		";
		//echo getDebugSQL($sql, $params);
		$result = $this->db->query($query, $data);
		if (is_object($result))
		{
			$result = $result->result('array');
			//c postgres почему-то даже при явном cast'е возвращаются varchar, а не int'ы
			if (isset($result[0])) {
				foreach ($result[0] as $key => $value) {
					$result[0][$key] = (int) $value;
				}
			}
			return [$result];
		}
		else
		{
			return false;
		}
	}

    //BOB - 25.01.2017
	/**
	 * Возвращает список обслуживаемых отделений
	 */
	function loadMedServiceSectionGrid($data) {

		$params = array('MedService_id' => $data['MedService_id']);

		$query = "
			select
				MSS.MedServiceSection_id,
				1 as RecordStatus_Code,
				MSS.MedService_id,
				MSS.LpuSection_id,
				dLS.LpuSection_FullName+', '+dLUT.LpuUnitType_Name as LpuSection_Name,
				cast(dLB.LpuBuilding_Code as varchar)+'. '+dLB.LpuBuilding_Name as LpuBuilding_Name
			from v_MedServiceSection MSS with(nolock)
				left join v_LpuSection dLS with(nolock) on dLS.LpuSection_id = MSS.LpuSection_id
				left join v_LpuUnit dLU with(nolock) on dLU.LpuUnit_id = dLS.LpuUnit_id
				left join v_LpuUnitType dLUT with(nolock) on dLUT.LpuUnitType_id = dLU.LpuUnitType_id
				left join v_LpuBuilding dLB with(nolock) on dLB.LpuBuilding_id = dLU.LpuBuilding_id
			where MSS.MedService_id = :MedService_id
		";

		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		return false;
	}



	/**
	 * Формирование строки грида обслуживаемых отделений, прикреплённых к службе
	 */
	function getRowMedServiceSection($data) {

		//ПРОВЕРКА тго, , что отделение уже прикреплено к другому отделению реанимации
		$params0 = array(
			'MedService_id' => $data['MedService_id'],
			'LpuSection_id' => $data['LpuSection_id']
		);

		$query0 = "
				select
					MS.MedService_Name as MedService_Name,
					(select CAST(MST.MedServiceType_Code as varchar(20)) + ' ' + MST.MedServiceType_Name  from dbo.MedServiceType MST where MST.MedServiceType_id = MS.MedServiceType_id) MedServiceType_Name,
					case
					when  MS.LpuSection_id is not null then
						(select 'отделением - ' + CAST(LS.LpuSection_Code as varchar(20)) + ' ' + Ls.LpuSection_Name   from dbo.LpuSection LS where LS.LpuSection_id = MS.LpuSection_id)
					when  MS.LpuSection_id is null and MS.LpuUnit_id is not null then
						(select 'группой отделений - ' + LU.LpuUnit_Name   from dbo.LpuUnit LU where LU.LpuUnit_id = MS.LpuUnit_id)
					when  MS.LpuUnit_id is null and  MS.LpuUnitType_id is not null then
						(select 'верхней группой отделений - ' + LUT.LpuUnitType_Name
			from v_LpuUnitType LUT with (nolock)
								 cross apply (
									select top 1 v_LpuUnit.LpuUnit_endDate
									  from v_LpuUnit with (nolock)
									 where v_LpuUnit.LpuBuilding_id = MS.LpuBuilding_id
									   and v_LpuUnit.LpuUnitType_id = LUT.LpuUnitType_id
									 order by v_LpuUnit.LpuUnit_endDate) LU
									)
					when  MS.LpuUnitType_id is null and MS.LpuBuilding_id is not null then
						(select 'подразделением - ' +  LB.LpuBuilding_Name   from dbo.LpuBuilding LB where LB.LpuBuilding_id = MS.LpuBuilding_id)
					when  MS.LpuBuilding_id is null and MS.Lpu_id is not null then
						(select 'ЛПУ - ' + O.Org_Nick
						   from dbo.Lpu L inner join dbo.Org O on L.Org_id = O.Org_id
						  where L.Lpu_id = MS.Lpu_id)
					end as ParentName
				from dbo.v_MedServiceSection MSS
					inner join dbo.v_MedService MS on MS.MedService_id = MSS.MedService_id
				where MSS.LpuSection_id = :LpuSection_id
				  and MSS.MedService_id <> :MedService_id
				";

		$response0 = $this->getFirstRowFromQuery($query0, $params0);
        //    echo '<pre>' . print_r($response0, 1) . '</pre>'; //BOB - 25.01.2017

        if ($response0) {
			$response0 = array('success' => false, 'data' => $response0);
            return $response0;
        }





		//ИЗВЛЕЧЕНИЕ ДАННЫХ ДЛЯ ЗАПИСИ В ГРИДЕ ПРИКРЕПЛЁННЫХ ОТДЕЛЕНИЙ
		$params = array(
			'MedServiceSection_id' => $data['MedServiceSection_id'],
			'MedService_id' => $data['MedService_id'],
			'LpuSection_id' => $data['LpuSection_id'],
			'RecordStatus_Code' => $data['RecordStatus_Code']
		);
		//echo '<pre>' . print_r($params, 1) . '</pre>'; //BOB - 25.01.2017
		$query = "
			select top 1
				:MedServiceSection_id as MedServiceSection_id,
				:MedService_id as MedService_id,
				:RecordStatus_Code as RecordStatus_Code,
				dLS.LpuSection_id as LpuSection_id,
				dLS.LpuSection_FullName+', '+dLUT.LpuUnitType_Name as LpuSection_Name,
				cast(dLB.LpuBuilding_Code as varchar)+'. '+dLB.LpuBuilding_Name as LpuBuilding_Name
			from v_LpuSection dLS with(nolock)
				left join v_LpuUnit dLU with(nolock) on dLU.LpuUnit_id = dLS.LpuUnit_id
				left join v_LpuUnitType dLUT on dLUT.LpuUnitType_id = dLU.LpuUnitType_id
				left join v_LpuBuilding dLB with(nolock) on dLB.LpuBuilding_id = dLU.LpuBuilding_id
			where dLS.LpuSection_id = :LpuSection_id
		";

		$response = $this->getFirstRowFromQuery($query, $params);
		if ($response) {
			$response = array('success' => true, 'data' => $response);
		} else {
			$response = array('success' => false, 'data' => false);
		}

		return $response;
	}
    //BOB - 25.01.2017

	/**
	 * Загружает первую свободную бирку
	 * @return bool
	 */
	function getTimetableNoLimit($data)
	{
		// 1. тянем бирку
		$upper_dt = 365;
		if(empty($data['pzm_MedService_id']))
			$data['pzm_MedService_id'] = null;
		if(empty($data['Resource_id']))
			$data['Resource_id'] = null;

		if($data['PrescriptionType_Code'] == 12 && !empty($data['Resource_id'])){
			$query = "
				DECLARE @cur_dt datetime = GETDATE();
				declare @upper_dt datetime = DATEADD(day, 365, @cur_dt);

				SELECT top 1
					NULL AS TimetableMedService_id,
					NULL AS TimetableMedService_begTime,
					CONVERT(varchar(10), ttr.TimetableResource_begTime, 104) +' '+CONVERT(varchar(5), ttr.TimetableResource_begTime, 108) as TimetableResource_begTime,
					ttr.TimetableResource_id,
					ttr.Person_id,
					r.Resource_id,
					r.Resource_id as ttr_Resource_id,
					r.Resource_Name,
					r.MedService_id as ttms_MedService_id
				from
					v_TimetableResource_lite ttr with (nolock)
					inner join v_Resource r with (nolock) on r.Resource_id = ttr.Resource_id
				where
					ttr.Person_id is NULL
					AND ttr.Resource_id = :Resource_id
					AND ttr.TimetableResource_begTime >= @cur_dt
					AND ttr.TimetableResource_begTime < @upper_dt
				ORDER BY
					ttr.TimetableResource_begTime
			";
		} else {
			$this->load->library('sql/UslugaComplexSelectListSqlBuilder');
			UslugaComplexSelectListSqlBuilder::$withResource = (!empty($data['PrescriptionType_Code']) && $data['PrescriptionType_Code'] == 12);
			if(!empty($data['formMode']))
				UslugaComplexSelectListSqlBuilder::$formMode = $data['formMode'];
			UslugaComplexSelectListSqlBuilder::$isLab = (!empty($data['PrescriptionType_Code']) && $data['PrescriptionType_Code'] == 11);
			$from = UslugaComplexSelectListSqlBuilder::getTimetableQueryFrom();
			$query = "
				declare @cur_dt datetime = GETDATE();
				declare @upper_dt datetime = DATEADD(day, {$upper_dt}, @cur_dt);
				declare @cur_date date = cast(@cur_dt as date);

				select top 1
					CONVERT(varchar(10), ttmsx.TimetableMedService_begTime, 104) +' '+CONVERT(varchar(5), ttmsx.TimetableMedService_begTime, 108) as TimetableMedService_begTime,
					ttmsx.TimetableMedService_id,
					ttmsx.MedService_id as ttms_MedService_id,
					CONVERT(varchar(10), ttmsx.TimetableResource_begTime, 104) +' '+CONVERT(varchar(5), ttmsx.TimetableResource_begTime, 108) as TimetableResource_begTime,
					ttmsx.TimetableResource_id,
					ttmsx.Resource_id,
					ttmsx.Resource_Name,
					ttmsx.Resource_id as ttr_Resource_id
				from
					v_UslugaComplexMedService ucms (nolock)
					outer apply (
						select top 1
							ucms.UslugaComplexMedService_id,
							ms.Lpu_id,
							ms.MedService_id,
							:pzm_MedService_id as pzm_MedService_id,
							null as pzm_UslugaComplexMedService_id
						from
							v_MedService ms (nolock)
						where
							ms.MedService_id = ucms.MedService_id
					) DD
					{$from}
				where
					ucms.UslugaComplexMedService_id = :UslugaComplexMedService_id
			";
		}


		$resp = $this->queryResult($query, array(
			'UslugaComplexMedService_id' => $data['UslugaComplexMedService_id'],
			'userLpu_id' => $data['Lpu_id'],
			'pzm_MedService_id' => $data['pzm_MedService_id'],
			'Resource_id' => $data['Resource_id']
		));

		if (empty($resp[0])) {
			return false;
		}

		$resp[0]['Error_Msg'] = '';

		return $resp[0];
	}

	/**
	 * Загружает следующую свободную бирку
	 * @return bool
	 */
	function getTimetableNext($data)
	{
		$response = array(
			'Error_Msg' => '',
			'TimetableMedService_id' => null,
			'TimetableMedService_begTime' => null,
			'TimetableResource_id' => null,
			'TimetableResource_begTime' => null
		);

		if (!empty($data['TimetableMedService_id'])) {
			$resp = $this->queryResult("
				select top 1
					ttms2.TimetableMedService_id,
					CONVERT(varchar(10), ttms2.TimetableMedService_begTime, 104) +' '+CONVERT(varchar(5), ttms2.TimetableMedService_begTime, 108) as TimetableMedService_begTime
				from
					v_TimetableMedService_lite ttms (nolock)
					inner join v_TimetableMedService_lite ttms2 (nolock) on
						ISNULL(ttms2.MedService_id, 0) = ISNULL(ttms.MedService_id, 0)
						and ISNULL(ttms2.UslugaComplexMedService_id, 0) = ISNULL(ttms.UslugaComplexMedService_id, 0)
						and ttms2.TimetableMedService_begTime >= ttms.TimetableMedService_begTime
						and ttms2.Person_id is null
				where
					ttms.TimetableMedService_id = :TimetableMedService_id
				order by
					ttms2.TimetableMedService_begTime asc
			", array(
				'TimetableMedService_id' => $data['TimetableMedService_id']
			));

			if (!empty($resp[0]['TimetableMedService_id'])) {
				$response['TimetableMedService_id'] = $resp[0]['TimetableMedService_id'];
				$response['TimetableMedService_begTime'] = $resp[0]['TimetableMedService_begTime'];
			}
		}

		if (!empty($data['TimetableResource_id'])) {
			$resp = $this->queryResult("
				select top 1
					ttr2.TimetableResource_id,
					CONVERT(varchar(10), ttr2.TimetableResource_begTime, 104) +' '+CONVERT(varchar(5), ttr2.TimetableResource_begTime, 108) as TimetableResource_begTime
				from
					v_TimetableResource_lite ttr (nolock)
					inner join v_TimetableResource_lite ttr2 (nolock) on
						ISNULL(ttr2.Resource_id, 0) = ISNULL(ttr.Resource_id, 0)
						and ttr2.TimetableResource_begTime >= ttr.TimetableResource_begTime
						and ttr2.Person_id is null
				where
					ttr.TimetableResource_id = :TimetableResource_id
				order by
					ttr2.TimetableResource_begTime asc
			", array(
				'TimetableResource_id' => $data['TimetableResource_id']
			));

			if (!empty($resp[0]['TimetableResource_id'])) {
				$response['TimetableResource_id'] = $resp[0]['TimetableResource_id'];
				$response['TimetableResource_begTime'] = $resp[0]['TimetableResource_begTime'];
			}
		}

		return $response;
	}
	/**
	 * Загружает первую свободную бирку
	 * @return bool
	 */
	function getTimetableNoLimitWithMedService($data)
	{
		// 1. тянем бирку
		$upper_dt = 365;

		$this->load->library('sql/UslugaComplexSelectListSqlBuilder');
		UslugaComplexSelectListSqlBuilder::$withResource = (!empty($data['PrescriptionType_Code']) && $data['PrescriptionType_Code'] == 12);
		if(!empty($data['formMode']))
			UslugaComplexSelectListSqlBuilder::$formMode = $data['formMode'];
		UslugaComplexSelectListSqlBuilder::$isLab = (!empty($data['PrescriptionType_Code']) && $data['PrescriptionType_Code'] == 11);
		UslugaComplexSelectListSqlBuilder::$isFunc = (!empty($data['PrescriptionType_Code']) && $data['PrescriptionType_Code'] == 12);
		$from = UslugaComplexSelectListSqlBuilder::getTimetableQueryFrom();

		$MedServiceType_SysNick = $this->getFirstResultFromQuery("select mst.MedServiceType_SysNick from v_MedService ms (nolock) inner join v_MedServiceType mst (nolock) on mst.MedServiceType_id = ms.MedServiceType_id where ms.MedService_id = :MedService_id", array(
			'MedService_id' => $data['MedService_id']
		));
		$filterMS = 'and ucms.MedService_id = :MedService_id';
		$main_from = 'v_UslugaComplexMedService ucms (nolock)';
		$pzm_MedService_id = 'null';
		$lpuSection = '
				mslab.Lpu_id,
				mslab.LpuUnit_id,
				mslab.LpuSection_id,
				lslab.LpuSectionProfile_id';
		$lpuSectionJoin = '
				left join v_MedService mslab (NOLOCK) ON mslab.MedService_id = DD.MedService_id
				left join v_LpuSection lslab (NOLOCK) ON lslab.LpuSection_id = mslab.LpuSection_id';
		if(!empty($MedServiceType_SysNick) && $MedServiceType_SysNick == 'pzm'){
			$main_from = ' v_MedServiceLink msl (nolock)
				inner join v_UslugaComplexMedService ucms (nolock) on ucms.MedService_id = msl.MedService_lid';
			$filterMS = ' and msl.MedService_id = :MedService_id';
			$pzm_MedService_id = ':MedService_id';
			$lpuSection = '
				COALESCE(mspz.Lpu_id, mslab.Lpu_id, null) AS Lpu_id,
				COALESCE(mspz.LpuUnit_id, mslab.LpuUnit_id, null) AS LpuUnit_id,
				COALESCE(mspz.LpuSection_id, mslab.LpuSection_id, null) AS LpuSection_id,
				COALESCE(lspz.LpuSectionProfile_id, lslab.LpuSectionProfile_id, null) AS LpuSectionProfile_id';
			$lpuSectionJoin = '
				left join v_MedService mslab (NOLOCK) ON mslab.MedService_id = DD.MedService_id
				left join v_MedService mspz (NOLOCK) ON mspz.MedService_id = :MedService_id
				left join v_LpuSection lslab (NOLOCK) ON lslab.LpuSection_id = mslab.LpuSection_id
				left join v_LpuSection lspz (NOLOCK) ON lspz.LpuSection_id = mspz.LpuSection_id';
		}



		$query = "
			declare @cur_dt datetime = GETDATE();
			declare @upper_dt datetime = DATEADD(day, {$upper_dt}, @cur_dt);
			declare @cur_date date = cast(@cur_dt as date);

			select top 1
				ucms.MedService_id,
				ucms.UslugaComplexMedService_id,
				CONVERT(varchar(10), ttmsx.TimetableMedService_begTime, 104) +' '+CONVERT(varchar(5), ttmsx.TimetableMedService_begTime, 108) as TimetableMedService_begTime,
				ttmsx.TimetableMedService_id,
				ttmsx.MedService_id as ttms_MedService_id,
				CONVERT(varchar(10), ttmsx.TimetableResource_begTime, 104) +' '+CONVERT(varchar(5), ttmsx.TimetableResource_begTime, 108) as TimetableResource_begTime,
				ttmsx.TimetableResource_id,
				ttmsx.Resource_id,
				ttmsx.Resource_Name,
				ttmsx.Resource_id as ttr_Resource_id,
				{$lpuSection}
			from
				{$main_from}
				outer apply (
					select top 1
						ucms.UslugaComplexMedService_id,
						ms.Lpu_id,
						ms.LpuUnit_id,
						ms.LpuSection_id,
						ms.MedService_id,
						{$pzm_MedService_id} as pzm_MedService_id,
						null as pzm_UslugaComplexMedService_id
					from
						v_MedService ms (nolock)
					where
						ms.MedService_id = ucms.MedService_id
				) DD
				{$lpuSectionJoin}
				{$from}
			where
				ucms.UslugaComplex_id = :UslugaComplex_id
				{$filterMS}
			ORDER BY
				ttmsx.TimetableMedService_id DESC,
				ttmsx.TimetableResource_id DESC
		";
		$resp = $this->queryResult($query, array(
			'MedService_id' => $data['MedService_id'],
			'UslugaComplex_id' => $data['UslugaComplex_id'],
			'userLpu_id' => $data['Lpu_id']
		));
		// Если не нашлось бирок или нет записи
		if (empty($resp[0]) ||
			(!empty($resp[0]) && empty($resp[0]['TimetableMedService_id']) && empty($resp[0]['TimetableResource_id']))) {
			$msData = $this->getFirstRowFromQuery("
				select
						ms.Lpu_id,
						ms.LpuUnit_id,
						ms.LpuSection_id,
						ls.LpuSectionProfile_id
					from
						v_MedService ms (nolock)
						LEFT JOIN v_LpuSection ls (NOLOCK) ON ls.LpuSection_id = ms.LpuSection_id
						where
				ms.MedService_id = :MedService_id
			", array('MedService_id' => $data['MedService_id']));
			if (empty($msData))
				return false;
			else {
				foreach ($msData as $attr){
					if (empty($attr)) {
						return false;
						break;
					}
				}

				$resp[0] = $msData;
			}
		}
		$resp[0]['Error_Msg'] = '';

		return $resp[0];
	}

	/**
	 *  Загружает список услуг служб для назначения лабораторной диагностики
	 */
	function loadLastPrescrList($data)
	{
		$top = 20;
		$select = '';

		$join = 'inner join v_UslugaComplex uc with (NOLOCK) on uc.UslugaComplex_id = EP.UslugaComplex_id ';
		$ms = 'EP.MedService_id';
		$join_prescr_code = 'left join v_PrescriptionType pt with (NOLOCK) on pt.PrescriptionType_id = EP.PrescriptionType_id ';
		// Состав фильтров

		$params = array(
			'Lpu_id' => $data['Lpu_id'],
			'MedPersonal_id' => $data['MedPersonal_id'],
			'PrescriptionType_Code' => $data['PrescriptionType_Code']
		);
		$filter = array(
			'MedPersonal_id' => 'PMUC.MedPersonal_id = :MedPersonal_id',
			'MedService_id' => 'ms.MedService_id IS NOT NULL',
			'Lpu_id' => 'EP.Lpu_id = :Lpu_id',
			'PrescriptionStatusType_id' => 'EP.PrescriptionStatusType_id != 3',
			'PrescriptionType_Code' => 'pt.PrescriptionType_Code = :PrescriptionType_Code'
		);


		if (!empty($data['top']) && intval($data['top']) > 0)
			$top = $data['top'];



		switch($data['PrescriptionType_Code']){
			case '6':
				unset($filter['PrescriptionStatusType_id']);
				unset($filter['PrescriptionType_Code']);
				$join_prescr_code = '';
				$tabl = 'EvnCourseProc';
				$join .= 'inner join v_UslugaComplexMedService ucms with (nolock) on ucms.UslugaComplex_id = uc.UslugaComplex_id AND ucms.UslugaComplexMedService_pid IS NULL AND cast(ucms.UslugaComplexMedService_begDT as date) <= @cur_date AND ISNULL(cast(ucms.UslugaComplexMedService_endDT as date), @cur_date) >= @cur_date';
				$ms = ' ucms.MedService_id AND ms.MedServiceType_id = 13 AND ms.Lpu_id = :Lpu_id AND ms.LpuSection_id is not null AND cast(ms.MedService_begDT as date) <= @cur_date AND ISNULL(cast(ms.MedService_endDT as date), @cur_date) >= @cur_date';
				break;
			case '7':
				$tabl = 'EvnPrescrOperBlock';
				break;
			case '11':
				$tabl = 'EvnPrescrLabDiag';
				$select .= ',
					max(ucms.UslugaComplexMedService_id) as UslugaComplexMedService_id';
				$join .= ' left join v_UslugaComplexMedService ucms with(nolock) on ucms.UslugaComplexMedService_id = EP.UslugaComplexMedService_id ';
				$filter['UslugaComplexMedService_id'] = 'ucms.UslugaComplexMedService_id is not null';
				break;
			case '12':
				$tabl = 'EvnPrescrFuncDiag';
				$join =	'inner join EvnPrescrFuncDiagUsluga EPFDU with (nolock) on EPFDU.EvnPrescrFuncDiag_id = EP.EvnPrescrFuncDiag_id
						inner join v_UslugaComplex uc with (NOLOCK) on uc.UslugaComplex_id = EPFDU.UslugaComplex_id ';
				break;
			case '13':
				$tabl = 'EvnPrescrConsUsluga';
				break;
			default:
				return false;
		}

		$sql = "
			declare @cur_dt datetime = GETDATE();
			declare @cur_date date = cast(@cur_dt as date);

			select top {$top}
				-- select
				uc.UslugaComplex_id,
				uc.UslugaComplex_Name,
				uc.UslugaComplex_Code,
				ms.MedService_id,
				ms.MedService_Name,
				EP.Lpu_id,
				null as UslugaComplex_IsFavorite {$select}
				-- end select
			from
				-- from
				v_{$tabl} EP with (nolock)
				left join v_pmUserCache PMUC (nolock) on PMUC.PMUser_id = EP.pmUser_insID and PMUC.Lpu_id = :Lpu_id
				{$join}
				{$join_prescr_code}
				inner join v_MedService ms with (nolock) on ms.MedService_id = {$ms}
				-- end from
			WHERE
				-- where
				".implode(' and ', $filter)."
				-- end where
			group by
				-- group by
				ms.MedService_id,
				ms.MedService_Name,
				uc.UslugaComplex_id,
				uc.UslugaComplex_Name,
				uc.UslugaComplex_Code,
				EP.Lpu_id
				-- end order by
			order by
				-- order by
				max(EP.{$tabl}_setDate) DESC,
				max(EP.{$tabl}_setTime) DESC
				-- end order by
		";

		//echo getDebugSQL($sql, $params);
		$result = $this->db->query($sql, $params);
		if (is_object($result))
		{
			return $result->result('array');
		}
		else
		{
			return false;
		}
	}

	/**
	 * Возвращает список назначенных для записи услуг со службами/ресурсами/лабораториями/пунктами забора с первой из доступных бирок
	 */
	function loadEvnPrescrUslugaList($data)
	{

		//$params = array('Evn_id' => 730023881215345);
		//$params = array('Evn_id' => 730023881270175);
		$params = array(
			'LpuSection_id' => $data['userLpuSection_id'],
		);
		$sql = "
			Select
                user_ls.Lpu_id,
                user_lu.LpuBuilding_id,
                user_ls.LpuUnit_id
			from v_LpuSection user_ls with (nolock)
			inner join v_LpuUnit user_lu with (nolock) on user_lu.LpuUnit_id = user_ls.LpuUnit_id
			where user_ls.LpuSection_id = :LpuSection_id
		";
		$result = $this->db->query($sql, $params);
		if (is_object($result))
		{
			$rc = $result->result('array');
			if (count($rc)>0 && is_array($rc[0])) {
				$data['userLpu_id'] = $rc[0]['Lpu_id'];
				$data['userLpuBuilding_id'] = $rc[0]['LpuBuilding_id'];
				$data['userLpuUnit_id'] = $rc[0]['LpuUnit_id'];
			}
		}

		$params = array(
			'Evn_id' => $data['Evn_id'],
			'userLpu_id' => $data['userLpu_id']
		);

		$query = "
			DECLARE @cur_dt DATETIME = dbo.tzGetDate();
			DECLARE @cur_date DATE = CAST(@cur_dt AS DATE);

			WITH ALL_USL as (
				SELECT
					EP.EvnPrescr_id
					,EP.EvnPrescr_pid
					,EP.EvnPrescr_rid
					,convert(varchar,EP.EvnPrescr_setDT,104) as EvnPrescr_setDate
					,null as EvnPrescr_setTime
					,isnull(EP.EvnPrescr_IsExec,1) as EvnPrescr_IsExec
					-- Если в качестве даты-времени выполнения брать EU.EvnUsluga_setDT, то дата может не отобразиться, если при выполнении не была создана услуга или услуга не связана с назначением
					-- Поэтому решил использовать EP.EvnPrescr_updDT, т.к. после выполнения эта дата не меняется
					,case when 2 = EP.EvnPrescr_IsExec
						THEN convert(varchar,EP.EvnPrescr_updDT,104)+' '+convert(varchar,EP.EvnPrescr_updDT,108) else null
					end as EvnPrescr_execDT
					,EP.PrescriptionStatusType_id
					,EP.PrescriptionType_id
					,EP.PrescriptionType_id as PrescriptionType_Code
					,isnull(EP.EvnPrescr_IsCito,1) as EvnPrescr_IsCito
					,isnull(EP.EvnPrescr_Descr,'') as EvnPrescr_Descr
					,EP.EvnPrescr_pid as timetable_pid
					,EP.EvnPrescr_CountComposit
					,EP.StudyTarget_id
				from v_EvnPrescr EP with (nolock)

				where
					EP.EvnPrescr_pid  = :Evn_id
					and EP.PrescriptionType_id IN (6,11,12,13)
					and EP.PrescriptionStatusType_id != 3
			)

			SELECT
				CASE when EvnVizitPL.Lpu_id = :userLpu_id
						AND isnull(EvnVizitPL.EvnVizitPL_IsSigned,1) = 1
						AND LR.EvnStatus_id = 1
						AND ISNULL(ALL_USL.EvnPrescr_IsExec, 1) = 1
						then 'edit' else 'view' end as accessType

				,case
					WHEN ALL_USL.PrescriptionType_id = 6 then 'EvnPrescrProc'
					when ALL_USL.PrescriptionType_id = 11 then 'EvnPrescrLabDiag'
					when ALL_USL.PrescriptionType_id = 12 then 'EvnPrescrFuncDiag'
					when ALL_USL.PrescriptionType_id = 13 then 'EvnPrescrConsUsluga'
					else ''
				end as object
				,uc.UslugaComplex_id
				,uc.UslugaComplex_Name
				,uc.UslugaComplex_Code
				,UCM.MedService_id
				,UCM.UslugaComplexMedService_id
				,UCM.MedService_Name
				,UCM.MedService_Nick
				,UCM.Lpu_id
				,UCM.Lpu_Nick
				,ALL_USL.*
				,case when ED.EvnDirection_id is null OR ISNULL(ED.EvnStatus_id, 16) in (12,13) then 1 else 2 end as EvnPrescr_IsDir
				,ED.EvnStatus_id as EvnStatus_id
				,convert(varchar(10), coalesce(ED.EvnDirection_statusDate, ED.EvnDirection_failDT), 104) as EvnDirection_statusDate
				,ED.DirFailType_id
				,ED.EvnDirection_id
				,case when ED.EvnDirection_Num is null then '' else cast(ED.EvnDirection_Num as varchar) end as EvnDirection_Num
				,EvnStatus.EvnStatus_SysNick
				,CONVERT(VARCHAR(10), ttr.TimeTableResource_begTime, 104) + ' ' + CONVERT(VARCHAR(5), ttr.TimeTableResource_begTime, 108) AS ED_TimetableResource_begTime
				,CONVERT(VARCHAR(10), ttm.TimetableMedService_begTime, 104) + ' ' + CONVERT(VARCHAR(5), ttm.TimetableMedService_begTime, 108) AS ED_TimetableMedService_begTime
				,ED.TimetableMedService_id as ED_TimetableMedService_id
				,ED.TimetableResource_id as ED_TimetableResource_id
			FROM ALL_USL
				left join EvnPrescrLabDiag EPLD with (nolock) on EPLD.EvnPrescrLabDiag_id = ALL_USL.EvnPrescr_id AND ALL_USL.PrescriptionType_id = 11
				left join EvnPrescrFuncDiagUsluga EPFDU with (nolock) on EPFDU.EvnPrescrFuncDiag_id = ALL_USL.EvnPrescr_id AND ALL_USL.PrescriptionType_id = 12
				left join EvnPrescrConsUsluga EPCU with (nolock) on EPCU.EvnPrescrConsUsluga_id = ALL_USL.EvnPrescr_id AND ALL_USL.PrescriptionType_id = 13
				left join EvnPrescrProc EPPU with (nolock) on EPPU.EvnPrescrProc_id = ALL_USL.EvnPrescr_id AND ALL_USL.PrescriptionType_id = 6
				outer apply (
					Select top 1
						ED.EvnDirection_id
						,ED.EvnDirection_Num
						,ED.MedService_id
						,ED.LpuSectionProfile_id
						,ED.DirType_id
						,ED.EvnStatus_id
						,ED.EvnDirection_statusDate
						,ED.DirFailType_id
						,ED.EvnDirection_failDT
						,ED.MedPersonal_id
						,ED.TimeTableMedService_id
						,ED.TimeTableResource_id
					from v_EvnPrescrDirection epd with (nolock)
					inner join v_EvnDirection_all ED with (nolock) on epd.EvnDirection_id = ED.EvnDirection_id
					where epd.EvnPrescr_id = ALL_USL.EvnPrescr_id
					order by
						case when ISNULL(ED.EvnStatus_id, 16) in (12,13) then 2 else 1 end /* первым неотмененное/неотклоненное направление */
						,epd.EvnPrescrDirection_insDT desc
				) ED
				LEFT JOIN v_TimeTableResource_lite ttr WITH (NOLOCK) ON ED.EvnDirection_id IS NOT NULL AND ttr.TimeTableResource_id = ED.TimeTableResource_id
				LEFT JOIN v_TimeTableMedService_lite ttm WITH (NOLOCK) ON ED.EvnDirection_id IS NOT NULL AND ttm.TimeTableMedService_id = ED.TimeTableMedService_id
				outer apply(
					select top 1 ESH.EvnStatus_id, ESH.EvnStatusCause_id, ESH.pmUser_insID, ESH.EvnStatusHistory_Cause
					from EvnStatusHistory ESH with(nolock)
					where ESH.Evn_id = ED.EvnDirection_id
						and ESH.EvnStatus_id = ED.EvnStatus_id
					order by ESH.EvnStatusHistory_begDate desc
				) ESH
				left join EvnStatus with(nolock) on EvnStatus.EvnStatus_id = ESH.EvnStatus_id
				left join v_EvnVizitPL EvnVizitPL with (nolock) on EvnVizitPL.EvnVizitPL_id = ALL_USL.EvnPrescr_pid
				left join v_EvnLabRequest LR with (nolock) on LR.EvnDirection_id = ED.EvnDirection_id
				inner join v_UslugaComplex uc with (NOLOCK) on uc.UslugaComplex_id = COALESCE(EPLD.UslugaComplex_id,EPFDU.UslugaComplex_id,EPCU.UslugaComplex_id,EPPU.UslugaComplex_id)
				OUTER APPLY
				(
					SELECT TOP 1
							ucms.MedService_id,
							ucms.UslugaComplexMedService_id,
							ms.MedService_Name,
							ms.MedService_Nick,
							Lpu.Lpu_id,
							Lpu.Lpu_Nick
					FROM v_UslugaComplexMedService ucms WITH (NOLOCK)
						LEFT JOIN v_MedService ms WITH ( NOLOCK )ON ucms.MedService_id = ms.MedService_id
						LEFT JOIN v_Lpu Lpu WITH ( NOLOCK ) ON Lpu.Lpu_id = ms.Lpu_id
					WHERE ucms.UslugaComplex_id = uc.UslugaComplex_id
							AND ucms.UslugaComplexMedService_pid IS NULL
							AND CAST(ucms.UslugaComplexMedService_begDT AS DATE) <= @cur_date
							AND
							(
								ucms.UslugaComplexMedService_endDT IS NULL
								OR CAST(ucms.UslugaComplexMedService_endDT AS DATE) > @cur_date
							)
				) UCM

		";

		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			$UslugaList = $result->result('array');
		} else return false;

		$FuncUslList = $LabUslList = $OtherUslList = array();

		foreach ($UslugaList as $key => $usl) {
			if(!empty($usl['EvnDirection_id']))
				continue;

			switch ($usl['object']) {
				case 'EvnPrescrProc':
				case 'EvnPrescrConsUsluga':
					$OtherUslList[] = $usl['UslugaComplex_id'];
					break;
				case 'EvnPrescrLabDiag':
					$LabUslList[] = $usl['UslugaComplex_id'];
					break;
				case 'EvnPrescrFuncDiag':
					$FuncUslList[] = $usl['UslugaComplex_id'];
					break;
				default;
			}
		}

		$resourceList = $this->getResourceListByFirstTT($data, $FuncUslList);
		$LabAndPZList = $this->getLabAndPZListByFirstTT($data, $LabUslList);
		$OtherMedServiceList = $this->getMedServiceListByFirstTT($data, $OtherUslList);

		$MedServiceList = array();
		$UslugaListWithMS = array();
		if (is_array($resourceList) && !empty($resourceList))
			foreach ($resourceList as $res)
				$MedServiceList[$res['UslugaComplex_id']] = $res;

		if (is_array($LabAndPZList) && !empty($LabAndPZList))
			foreach ($LabAndPZList as $lab)
				$MedServiceList[$lab['UslugaComplex_id']] = $lab;

		if (is_array($OtherMedServiceList) && !empty($OtherMedServiceList))
			foreach ($OtherMedServiceList as $ms)
				$MedServiceList[$ms['UslugaComplex_id']] = $ms;

		foreach ($UslugaList as $key => $usl) {
			if(!empty($MedServiceList[$usl['UslugaComplex_id']]))
				$UslugaListWithMS[$key] = array_merge($usl, $MedServiceList[$usl['UslugaComplex_id']]);
			else
				$UslugaListWithMS[$key] = $usl;
		}


		return $UslugaListWithMS;


	}

	/**
	 * Возвращает список ресурсов с первой из доступных биркой по услугам функциональной диагностики
	 */
	function getResourceListByFirstTT($data,$arrUslList) {

		//$params = array('Evn_id' => 730023881215345);
		if(empty($arrUslList))
			return false;
		//$arrUslList = array(27536, 200762);
		$query = "
			-- ресурсы, с первой из доступных бирок на услугу функциональной диагностики
			BEGIN

				SET NOCOUNT ON;
				IF OBJECT_ID(N'tempdb..#AllResource', N'U') IS NOT NULL
					DROP TABLE #AllResource;

				DECLARE @cur_dt DATETIME = GETDATE();
				DECLARE @upper_dt DATETIME = DATEADD(DAY, 14, @cur_dt);
				DECLARE @cur_date DATE = CAST(@cur_dt AS DATE);

				WITH allUslugaResource
				AS (
				SELECT distinct
						ucms.UslugaComplex_id,
						res.Resource_id,
						res.Resource_Name,
						ms.MedService_id,
						ms.MedService_Name,
						ms.MedService_Nick,
						ms.LpuUnit_id,
						ms.LpuSection_id,
						ls.LpuSection_Name,
						ls.LpuSectionProfile_id,
						Lpu.Lpu_id,
						Lpu.Lpu_Nick,
						case when ms.Lpu_id = :userLpu_id then 1 else 2 end as s1,
						case when ms.LpuBuilding_id = :userLpuBuilding_id then 1 else 2 end as s2,
						case when ms.LpuUnit_id = :userLpuUnit_id then 1 else 2 end as s3,
						case when ms.LpuSection_id = :userLpuSection_id then 1 else 2 end as s4
					FROM v_UslugaComplexMedService ucms WITH (NOLOCK)
						LEFT JOIN v_UslugaComplexResource ucres WITH (NOLOCK)
							ON ucres.UslugaComplexMedService_id = ucms.UslugaComplexMedService_id
						LEFT JOIN v_Resource res WITH (NOLOCK)
							ON res.Resource_id = ucres.Resource_id
							AND
							(
								ISNULL(CAST(res.Resource_begDT AS DATE), @cur_date) <= @cur_date
								AND ISNULL(CAST(res.Resource_endDT AS DATE), @cur_date) >= @cur_date
							)
						INNER JOIN v_MedService ms WITH ( NOLOCK )ON ucms.MedService_id = ms.MedService_id
							AND
							(
								CAST(ms.MedService_begDT AS DATE) <= @cur_date
								AND ISNULL(CAST(ms.MedService_endDT AS DATE), @cur_date) >= @cur_date
							)
						LEFT JOIN v_Lpu Lpu WITH ( NOLOCK ) ON Lpu.Lpu_id = ms.Lpu_id
						left join v_LpuSection ls with (nolock) on ls.LpuSection_id = ms.LpuSection_id
					WHERE ucms.UslugaComplex_id IN (" . implode(',', $arrUslList) . ")
						AND res.Resource_id IS NOT NULL
						AND ucms.UslugaComplexMedService_pid IS NULL
						AND CAST(ucms.UslugaComplexMedService_begDT AS DATE) <= @cur_date
						AND ISNULL(CAST(ucms.UslugaComplexMedService_endDT AS DATE), @cur_date) >= @cur_date
						)
				SELECT *
				INTO #AllResource
				FROM allUslugaResource
					OUTER APPLY
				(
					SELECT TOP 1
						   CONVERT(VARCHAR(10), ttr.TimeTableResource_begTime, 104) + ' '
						   + CONVERT(VARCHAR(5), ttr.TimeTableResource_begTime, 108) AS TimetableResource_begTime,
						   ttr.TimetableResource_id
					FROM v_TimeTableResource_lite ttr WITH (NOLOCK)
					WHERE ttr.Person_id IS NULL
						  AND ttr.Resource_id = allUslugaResource.Resource_id
						  AND ttr.TimeTableResource_begTime >= @cur_dt
						  AND ttr.TimeTableResource_begTime < @upper_dt
						  AND ttr.TimetableType_id <> 2
					ORDER BY CASE
								 WHEN ttr.TimeTableResource_begTime IS NOT NULL THEN
									 0
								 ELSE
									 1
							 END
				) timetable
				--WHERE timetable.TimetableResource_id IS NOT NULL;

				SELECT tt.*
				FROM
				(SELECT DISTINCT UslugaComplex_id FROM #AllResource) allt
					OUTER APPLY
				(
					SELECT TOP 1
						UslugaComplex_id,
						Resource_id,
						Resource_Name,
						MedService_id,
						MedService_Name,
						MedService_Nick,
						LpuUnit_id,
						LpuSection_id,
						LpuSection_Name,
						LpuSectionProfile_id,
						Lpu_id,
						Lpu_Nick,
						TimetableResource_begTime,
						TimetableResource_id
					FROM #AllResource temp
					WHERE temp.UslugaComplex_id = allt.UslugaComplex_id
					ORDER BY [s1],
							CASE
								 WHEN temp.TimeTableResource_begTime IS NOT NULL THEN
									 0
								 ELSE
									 1
							END,
							[s2], [s3], [s4]
				) tt;
			END;
		";

		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		return false;
	}

	/**
	 * Возвращает список пунктов забора и лабораторий с первой из доступных биркой по услугам лабораторной диагностики
	 */
	function getLabAndPZListByFirstTT($data,$arrUslList) {
		if(empty($arrUslList))
			return false;
		//$params = array('Evn_id' => 730023881215345);
		//$arrUslList = array(206893);
		$query = "
			-- лаборатории и пункты забора, с первой из доступных бирок на услугу лабораторной диагностики
			BEGIN

				SET NOCOUNT ON;
				IF OBJECT_ID(N'tempdb..#AllTTLabAndPzm', N'U') IS NOT NULL
					DROP TABLE #AllTTLabAndPzm;

				DECLARE @cur_dt DATETIME = GETDATE();
				DECLARE @upper_dt DATETIME = DATEADD(DAY, 14, @cur_dt);
				DECLARE @cur_date DATE = CAST(@cur_dt AS DATE);

				WITH AllLabAndPzm
				AS (SELECT DISTINCT
						   ISNULL(uc.UslugaComplex_id, uc11.UslugaComplex_id) AS UslugaComplex_id,
						   mss.MedService_id,
						   pzm.MedService_id AS pzm_MedService_id,
						   ucms.UslugaComplexMedService_id,
						   mss.MedService_Name,
						   mss.MedService_Nick,
						   mss.Lpu_id,
						   pzm.Lpu_id AS pzm_Lpu_id,
						   l.Lpu_Nick,
						   pzm.MedService_Name AS pzm_MedService_Name,
						   pzm.MedService_Nick AS pzm_MedService_Nick,
						   ucpzm.UslugaComplexMedService_id AS pzm_UslugaComplexMedService_id
					FROM v_UslugaComplex uc WITH (NOLOCK)
						INNER JOIN v_UslugaComplexMedService ucms WITH (NOLOCK)
							ON ucms.UslugaComplex_id = uc.UslugaComplex_id
							   AND ucms.UslugaComplexMedService_pid IS NULL
							   AND CAST(ucms.UslugaComplexMedService_begDT AS DATE) <= @cur_date
							   AND ISNULL(CAST(ucms.UslugaComplexMedService_endDT AS DATE), @cur_date) >= @cur_date
						OUTER APPLY
					(
						SELECT TOP 1
							   *
						FROM v_UslugaComplex uc11 WITH (NOLOCK)
						WHERE uc.UslugaComplex_2011id = uc11.UslugaComplex_id
					) uc11
						INNER JOIN v_MedService mss WITH (NOLOCK)
							ON mss.MedService_id = ucms.MedService_id
							   AND mss.MedServiceType_id = 6
							   AND mss.LpuSection_id IS NOT NULL
							   AND CAST(mss.MedService_begDT AS DATE) <= @cur_date
							   AND ISNULL(CAST(mss.MedService_endDT AS DATE), @cur_date) >= @cur_date
						LEFT JOIN v_MedServiceLink msl (NOLOCK)
							ON msl.MedService_lid = mss.MedService_id
							   AND msl.MedServiceLinkType_id = 1
						LEFT JOIN v_MedService pzm WITH (NOLOCK)
							ON pzm.MedServiceType_id = 7
							   AND msl.MedService_id = pzm.MedService_id
							   AND
							   (
								   pzm.MedService_endDT IS NULL
								   OR CAST(pzm.MedService_endDT AS DATE) > @cur_date
							   )
						OUTER APPLY (
							SELECT TOP 1 UslugaComplexMedService_id
							FROM v_UslugaComplexMedService ucpzm WITH (NOLOCK)
							LEFT JOIN v_UslugaComplex ucpzm11 WITH (NOLOCK) on ucpzm11.UslugaComplex_id = ucpzm.UslugaComplex_id
							WHERE
							 ucpzm.MedService_id = pzm.MedService_id
							 AND (
									ucpzm.UslugaComplex_id = uc.UslugaComplex_id
									OR ucpzm.UslugaComplex_id = uc.UslugaComplex_2011id
							   )

							   --AND (
								--	ucpzm.UslugaComplex_id = uc.UslugaComplex_id
								--	OR ucpzm.UslugaComplex_id = ucpzm11.UslugaComplex_2011id
							   --)
						) AS ucpzm
						LEFT JOIN v_Lpu l WITH (NOLOCK)
							ON mss.Lpu_id = l.Lpu_id
						LEFT JOIN v_Lpu pzml WITH (NOLOCK)
							ON pzm.Lpu_id = pzml.Lpu_id
						CROSS APPLY
					(
						SELECT TOP 1
							   Analyzer.Analyzer_id
						FROM lis.v_AnalyzerTest AnalyzerTest WITH (NOLOCK)
							INNER JOIN lis.v_Analyzer Analyzer (NOLOCK)
								ON Analyzer.Analyzer_id = AnalyzerTest.Analyzer_id
						WHERE AnalyzerTest.UslugaComplexMedService_id = ucms.UslugaComplexMedService_id
							  AND ISNULL(AnalyzerTest.AnalyzerTest_IsNotActive, 1) = 1
							  AND ISNULL(Analyzer.Analyzer_IsNotActive, 1) = 1
					) ATT
					WHERE uc.UslugaComplex_id IN (" . implode(',', $arrUslList) . ")
						  AND uc.UslugaComplex_begDT <= @cur_date
						  AND ISNULL(uc.UslugaComplex_endDT, @cur_date) >= @cur_date
						  AND NOT EXISTS
					(
						SELECT TOP 1
							   t1.UslugaComplexAttribute_id
						FROM v_UslugaComplexAttribute t1 WITH (NOLOCK)
							INNER JOIN v_UslugaComplexAttributeType t2 WITH (NOLOCK)
								ON t2.UslugaComplexAttributeType_id = t1.UslugaComplexAttributeType_id
						WHERE t1.UslugaComplex_id = ISNULL(uc11.UslugaComplex_id, uc.UslugaComplex_id)
							  AND t2.UslugaComplexAttributeType_SysNick IN ( 'noprescr' )
					)
						  AND NOT EXISTS
					(
						SELECT TOP 1
							   mslo.MedService_id
						FROM v_MedService mslo WITH (NOLOCK)
						WHERE mslo.MedService_id = mss.MedService_id
							  AND mslo.MedService_IsThisLPU = 2
							  AND mslo.Lpu_id != :userLpu_id
					)
						  AND EXISTS
					(
						SELECT TOP 1
							   t1.UslugaComplexAttribute_id
						FROM v_UslugaComplexAttribute t1 WITH (NOLOCK)
							INNER JOIN v_UslugaComplexAttributeType t2 WITH (NOLOCK)
								ON t2.UslugaComplexAttributeType_id = t1.UslugaComplexAttributeType_id
						WHERE t1.UslugaComplex_id = ISNULL(uc11.UslugaComplex_id, uc.UslugaComplex_id)
							  AND t2.UslugaComplexAttributeType_SysNick IN ( 'lab' )
					)
						  AND 1 = (CASE
									   WHEN mss.Lpu_id = :userLpu_id THEN
										   1
									   WHEN pzm.Lpu_id = :userLpu_id THEN
										   1
									   ELSE
										   0
								   END
								  ))

				-- Выше - достали все лаборатории и пункты забора с возможностью выполнить услугу
				SELECT ttmsx.*,
					   lp.*
				INTO #AllTTLabAndPzm
				FROM AllLabAndPzm lp
					OUTER APPLY
				(
					SELECT TOP 1
					 		ttms2.TimetableMedService_id,
						   CONVERT(VARCHAR(10), ttms2.TimetableMedService_begTime, 104) + ' '
						   + CONVERT(VARCHAR(5), ttms2.TimetableMedService_begTime, 108) AS TimetableMedService_begTime,
						   -- TimetableMedService_begTime,
						   ttms2.MedService_id AS ttms_MedService_id,
						   ttms2.is_pzm,
						   ttms2.by_Usl, -- так как поиск идет как по службе, как и по пз, так и по биркам на услуге
						   ttms2.UslugaComplexMedService_id AS tt_UslugaComplexMedService_id -- так как поиск идет как по id службы, так и по id пз
					FROM
					(
						SELECT TOP 1
							   ttms.TimetableMedService_id,
							   ttms.MedService_id,
							   ttms.TimetableMedService_begTime,
							   ttms.UslugaComplexMedService_id,
							   0 AS is_pzm,
							   0 AS by_Usl
						FROM v_TimeTableMedService_lite ttms WITH (NOLOCK)
						WHERE ttms.Person_id IS NULL
							  AND ttms.TimetableMedService_begTime >= @cur_dt
							  AND ttms.TimetableMedService_begTime < @upper_dt
							  AND ttms.MedService_id = lp.MedService_id
							  AND ttms.UslugaComplexMedService_id IS NULL
						ORDER BY CASE
									 /* При записи в свое МО: Все типы бирок кроме «резервных» */
									 WHEN lp.Lpu_id = :userLpu_id
										  AND ttms.TimetableType_id NOT IN ( 2 ) THEN
										 0
									 /* При записи в чужое МО: Обычная, По направлению */
									 WHEN lp.Lpu_id <> :userLpu_id
										  AND ttms.TimetableType_id IN ( 1, 5 ) THEN
										 0
									 ELSE
										 1
								 END,
								 ttms.TimetableMedService_begTime
						UNION
						SELECT TOP 1
							   ttms.TimetableMedService_id,
							   lp.MedService_id AS MedService_id,
							   ttms.TimetableMedService_begTime,
							   ttms.UslugaComplexMedService_id,
							   0 AS is_pzm,
							   1 AS by_Usl
						FROM v_TimeTableMedService_lite ttms WITH (NOLOCK)
						WHERE ttms.Person_id IS NULL
							  AND ttms.TimetableMedService_begTime >= @cur_dt
							  AND ttms.TimetableMedService_begTime < @upper_dt
							  AND ttms.UslugaComplexMedService_id = lp.UslugaComplexMedService_id
						ORDER BY CASE
									 /* При записи в свое МО: Все типы бирок кроме «резервных» */
									 WHEN lp.Lpu_id = :userLpu_id
										  AND ttms.TimeTableType_id NOT IN ( 2 ) THEN
										 0
									 /* При записи в чужое МО: Обычная, По направлению */
									 WHEN lp.Lpu_id <> :userLpu_id
										  AND ttms.TimeTableType_id IN ( 1, 5 ) THEN
										 0
									 ELSE
										 1
								 END,
								 ttms.TimetableMedService_begTime
						UNION
						SELECT TOP 1
							   ttms.TimetableMedService_id,
							   ttms.MedService_id,
							   ttms.TimetableMedService_begTime,
							   ttms.UslugaComplexMedService_id,
							   1 AS is_pzm,
							   0 AS by_Usl
						FROM v_TimeTableMedService_lite ttms WITH (NOLOCK)
						WHERE ttms.Person_id IS NULL
							  AND ttms.TimetableMedService_begTime >= @cur_dt
							  AND ttms.TimetableMedService_begTime < @upper_dt
							  AND ttms.MedService_id = lp.pzm_MedService_id
							  AND ttms.UslugaComplexMedService_id IS NULL
						ORDER BY CASE
									 /* При записи в свое МО: Все типы бирок кроме «резервных» */
									 WHEN lp.Lpu_id = :userLpu_id
										  AND ttms.TimeTableType_id NOT IN ( 2 ) THEN
										 0
									 /* При записи в чужое МО: Обычная, По направлению */
									 WHEN lp.Lpu_id <> :userLpu_id
										  AND ttms.TimeTableType_id IN ( 1, 5 ) THEN
										 0
									 ELSE
										 1
								 END,
								 ttms.TimetableMedService_begTime
						UNION
						SELECT TOP 1
							   ttms.TimetableMedService_id,
							   lp.pzm_MedService_id as MedService_id,
							   ttms.TimetableMedService_begTime,
							   ttms.UslugaComplexMedService_id,
							   1 AS is_pzm,
							   1 AS by_Usl
						FROM v_TimeTableMedService_lite ttms WITH (NOLOCK)
						WHERE ttms.Person_id IS NULL
							  AND ttms.TimetableMedService_begTime >= @cur_dt
							  AND ttms.TimetableMedService_begTime < @upper_dt
							  AND ttms.UslugaComplexMedService_id = lp.pzm_UslugaComplexMedService_id
						ORDER BY CASE
									 /* При записи в свое МО: Все типы бирок кроме «резервных» */
									 WHEN lp.Lpu_id = :userLpu_id
										  AND ttms.TimetableType_id NOT IN ( 2 ) THEN
										 0
									 /* При записи в чужое МО: Обычная, По направлению */
									 WHEN lp.Lpu_id <> :userLpu_id
										  AND ttms.TimetableType_id IN ( 1, 5 ) THEN
										 0
									 ELSE
										 1
								 END,
								 ttms.TimetableMedService_begTime
					) ttms2

					ORDER BY ttms2.by_Usl DESC, -- в первую очередь выбирать бирки из Услуги по пункту забора
							 ttms2.is_pzm DESC, -- в первую очередь выбирать бирки из пункта забора биоматериала
							 CASE
								 WHEN ttms2.UslugaComplexMedService_id IS NOT NULL THEN
									 0
								 ELSE
									 1
							 END
				) ttmsx;

				SELECT
					tt.*,
					ms.LpuUnit_id,
					ms.LpuSection_id,
					ls.LpuSection_Name,
					ls.LpuSectionProfile_id
				FROM
				(SELECT DISTINCT UslugaComplex_id FROM #AllTTLabAndPzm) allt
					OUTER APPLY
				(
					SELECT TOP 1
						   *
					FROM #AllTTLabAndPzm temp
					WHERE temp.UslugaComplex_id = allt.UslugaComplex_id
					ORDER BY CASE
								 WHEN temp.TimetableMedService_begTime IS NOT NULL THEN
									 0
								 ELSE
									 1
							 END,
							 temp.TimetableMedService_begTime
				) tt
				LEFT JOIN v_MedService ms WITH ( NOLOCK ) ON ms.MedService_id = COALESCE(tt.ttms_MedService_id,tt.MedService_id,null)
				left join v_LpuSection ls with (nolock) on ls.LpuSection_id = ms.LpuSection_id;

			END;
		";

		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		return false;
	}

	/**
	 * Возвращает список пунктов забора и лабораторий с первой из доступных биркой по услугам лабораторной диагностики
	 */
	function getMedServiceListByFirstTT($data,$arrUslList) {
		if(empty($arrUslList))
			return false;
		//$params = array('Evn_id' => 730023881215345);
		//$arrUslList = array(202616, 206570);
		$query = "
			-- службы, с первой из доступных бирок на типы услуг - процедуры и консультации
			BEGIN

				SET NOCOUNT ON;
				IF OBJECT_ID(N'tempdb..#AllTTMS', N'U') IS NOT NULL
					DROP TABLE #AllTTMS;

				DECLARE @cur_dt DATETIME = GETDATE();
				DECLARE @upper_dt DATETIME = DATEADD(DAY, 14, @cur_dt);
				DECLARE @cur_date DATE = CAST(@cur_dt AS DATE);

				WITH AllMedSevice
				AS (SELECT DISTINCT
						   ISNULL(uc.UslugaComplex_id, uc11.UslugaComplex_id) AS UslugaComplex_id,
						   mss.MedService_id,
						   ucms.UslugaComplexMedService_id,
						   mss.MedService_Name,
						   mss.MedService_Nick,
						   mss.Lpu_id,
						   l.Lpu_Nick
					FROM v_UslugaComplex uc WITH (NOLOCK)
						INNER JOIN v_UslugaComplexMedService ucms WITH (NOLOCK)
							ON ucms.UslugaComplex_id = uc.UslugaComplex_id
							   AND ucms.UslugaComplexMedService_pid IS NULL
							   AND CAST(ucms.UslugaComplexMedService_begDT AS DATE) <= @cur_date
							   AND ISNULL(CAST(ucms.UslugaComplexMedService_endDT AS DATE), @cur_date) >= @cur_date
						OUTER APPLY
					(
						SELECT TOP 1
							   *
						FROM v_UslugaComplex uc11 WITH (NOLOCK)
						WHERE uc.UslugaComplex_2011id = uc11.UslugaComplex_id
					) uc11
						INNER JOIN v_MedService mss WITH (NOLOCK)
							ON mss.MedService_id = ucms.MedService_id
							   AND mss.MedServiceType_id IN ( 13, 29 )
							   AND mss.Lpu_id = :userLpu_id
							   AND 1 = (CASE
											WHEN mss.MedServiceType_id = 29 THEN
												CASE
													WHEN EXISTS
														 (
															 SELECT TOP 1
																	lut.LpuUnitType_id
															 FROM v_LpuUnitType lut WITH (NOLOCK)
															 WHERE lut.LpuUnitType_id = mss.LpuUnitType_id
																   AND lut.LpuUnitType_SysNick IN ( 'polka', 'ccenter',
																									'traumcenter', 'fap'
																								  )
														 ) THEN
														1
													ELSE
														0
												END
											ELSE
												1
										END
									   )
							   AND mss.LpuSection_id IS NOT NULL
							   AND CAST(mss.MedService_begDT AS DATE) <= @cur_date
							   AND ISNULL(CAST(mss.MedService_endDT AS DATE), @cur_date) >= @cur_date
						LEFT JOIN v_Lpu l WITH (NOLOCK)
							ON mss.Lpu_id = l.Lpu_id
					WHERE uc.UslugaComplex_id IN (" . implode(',', $arrUslList) . ")
						  AND uc.UslugaComplex_begDT <= @cur_date
						  AND ISNULL(uc.UslugaComplex_endDT, @cur_date) >= @cur_date
						  AND NOT EXISTS
					(
						SELECT TOP 1
							   t1.UslugaComplexAttribute_id
						FROM v_UslugaComplexAttribute t1 WITH (NOLOCK)
							INNER JOIN v_UslugaComplexAttributeType t2 WITH (NOLOCK)
								ON t2.UslugaComplexAttributeType_id = t1.UslugaComplexAttributeType_id
						WHERE t1.UslugaComplex_id = ISNULL(uc11.UslugaComplex_id, uc.UslugaComplex_id)
							  AND t2.UslugaComplexAttributeType_SysNick IN ( 'noprescr' )
					)
						  AND NOT EXISTS
					(
						SELECT TOP 1
							   mslo.MedService_id
						FROM v_MedService mslo WITH (NOLOCK)
						WHERE mslo.MedService_id = mss.MedService_id
							  AND mslo.MedService_IsThisLPU = 2
							  AND mslo.Lpu_id != :userLpu_id
					)
						  AND 1 = (CASE
									   WHEN mss.Lpu_id = :userLpu_id THEN
										   1
									   ELSE
										   0
								   END
								  ))
				SELECT ttmsx.*,
					   lp.*
				INTO #AllTTMS
				FROM AllMedSevice lp
					OUTER APPLY
				(
					SELECT TOP 1
						   TimetableMedService_id,
						   CONVERT(VARCHAR(10), TimetableMedService_begTime, 104) + ' '
						   + CONVERT(VARCHAR(5), TimetableMedService_begTime, 108) AS TimetableMedService_begTime,
						   -- TimetableMedService_begTime,
						   MedService_id AS ttms_MedService_id,
						   by_Usl
					--,UslugaComplexMedService_id
					FROM
					(
						SELECT TOP 1
							   ttms.TimetableMedService_id,
							   ttms.MedService_id,
							   ttms.TimetableMedService_begTime,
							   ttms.UslugaComplexMedService_id,
							   0 as by_Usl
						FROM v_TimeTableMedService_lite ttms WITH (NOLOCK)
						WHERE ttms.Person_id IS NULL
							  AND ttms.TimetableMedService_begTime >= @cur_dt
							  AND ttms.TimetableMedService_begTime < @upper_dt
							  AND ttms.MedService_id = lp.MedService_id
							  AND ttms.UslugaComplexMedService_id IS NULL
						ORDER BY CASE
									 -- При записи в свое МО: Все типы бирок кроме «резервных»
									 WHEN lp.Lpu_id = :userLpu_id
										  AND ttms.TimetableType_id NOT IN ( 2 ) THEN
										 0
									 -- При записи в чужое МО: Обычная, По направлению
									 WHEN lp.Lpu_id <> :userLpu_id
										  AND ttms.TimetableType_id IN ( 1, 5 ) THEN
										 0
									 ELSE
										 1
								 END,
								 ttms.TimetableMedService_begTime
						UNION
						SELECT TOP 1
							   ttms.TimetableMedService_id,
							   lp.MedService_id as MedService_id,
							   ttms.TimetableMedService_begTime,
							   ttms.UslugaComplexMedService_id,
							   1 as by_Usl
						FROM v_TimeTableMedService_lite ttms WITH (NOLOCK)
						WHERE ttms.Person_id IS NULL
							  AND ttms.TimetableMedService_begTime >= @cur_dt
							  AND ttms.TimetableMedService_begTime < @upper_dt
							  AND ttms.UslugaComplexMedService_id = lp.UslugaComplexMedService_id
						ORDER BY CASE
									 -- При записи в свое МО: Все типы бирок кроме «резервных»
									 WHEN lp.Lpu_id = :userLpu_id
										  AND ttms.TimeTableType_id NOT IN ( 2 ) THEN
										 0
									 -- При записи в чужое МО: Обычная, По направлению
									 WHEN lp.Lpu_id <> :userLpu_id
										  AND ttms.TimeTableType_id IN ( 1, 5 ) THEN
										 0
									 ELSE
										 1
								 END,
								 ttms.TimetableMedService_begTime
					) ttms2
					ORDER BY CASE
								 WHEN ttms2.UslugaComplexMedService_id IS NOT NULL THEN
									 0
								 ELSE
									 1
							 END
				) ttmsx;

				SELECT
					tt.*,
					ls.LpuSection_Name,
					ls.LpuSectionProfile_id
				FROM
				(SELECT DISTINCT UslugaComplex_id FROM #AllTTMS) allt
					OUTER APPLY
				(
					SELECT TOP 1
						   *
					FROM #AllTTMS temp
					WHERE temp.UslugaComplex_id = allt.UslugaComplex_id
					ORDER BY CASE
								 WHEN temp.TimetableMedService_begTime IS NOT NULL THEN
									 0
								 ELSE
									 1
							 END,
							 temp.TimetableMedService_begTime
				) tt
				LEFT JOIN v_MedService ms WITH ( NOLOCK ) ON ms.MedService_id = COALESCE(tt.ttms_MedService_id,tt.MedService_id,null)
				left join v_LpuSection ls with (nolock) on ls.LpuSection_id = ms.LpuSection_id;
			END;
		";

		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		return false;
	}

	/**
	 * Список кодов ЕАВИИАС
	 */
	function loadMseOfficeList() {
		return $this->queryResult("
			select
				mo.MseOffice_id,
				mo.MseOffice_Code,
				mo.MseOffice_Name
			from v_MseOffice mo (nolock)
			inner join v_MseHeadOffice mho (nolock) on mho.MseHeadOffice_id = mo.MseHeadOffice_id
			where mho.Region_id = dbo.GetRegion()
		");
	}

	/**
	 * Получение связи услуги службы и пункта забора
	 */
	function getPzmUslugaComplexMedService($data)
	{
		$result = [
			'data' => null,
			'success' => false
		];

		$resp = $this->queryResult("
			select top 1
				UslugaComplexMedService_id
			from v_UslugaComplexMedService with (nolock)
			where
				MedService_id = :MedService_id
				and UslugaComplex_id = :UslugaComplex_id
		", $data);

		if ($resp) {
			$result['data'] = $resp;
			$result['success'] = true;
		}
		return $result;
	}
	/**
	 * Проверка Содержит ли служба одну из услуг A05.10.002, A05.10.006, A05.10.004 (ЭКГ)
	 */
	function checkMedServiceUsluga($data) {
		$query = "
			select top 1 MS.MedService_id
			from v_MedService MS (nolock)
				left join v_UslugaComplexMedService UCM (nolock) on UCM.MedService_id = MS.MedService_id
				left join v_UslugaComplex UC (nolock) on UC.UslugaComplex_id = UCM.UslugaComplex_id
			where
				(1=1)
				and (UC.UslugaComplex_Code like '%A05.10.002%' or UC.UslugaComplex_Code like '%A05.10.006%' or UC.UslugaComplex_Code like '%A05.10.004%')
				and MS.MedService_id = :MedService_id
		";
		$queryParams = array(
			'MedService_id' => $data['MedService_id']
		);
		$response = $this->getFirstResultFromQuery($query, $queryParams);
		return !empty($response);
	}

	/**
	 * Проверка, является ли данная служба внешней
	 * @return bool
	*/
	function checkIsExternal($data)
	{
		return $this->getFirstResultFromQuery("
			select
				case when MedService_IsExternal = 2
					then 1
					else null
				end as MedService_IsExternal,
				Lpu_id
			from v_MedService with (nolock)
			where MedService_id = :MedService_id
		", $data, true);
	}
}
