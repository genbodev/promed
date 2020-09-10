<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Storage - модель для работы со складами
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			02.07.2014
 */

class Storage_model extends swPgModel {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Получение списка наименований мест хранения товара для грида
	 */
	function loadGoodsStorageGrid($data) {
		$params = array();
		$where = "(1 = 1)";

		if (!empty($data['StorageUnitType_Code'])) {
			$where .= " and StorageUnitType_Code ilike :StorageUnitType_Code || '%'";
		}
		if (!empty($data['StorageUnitType_Name'])) {
			$where .= " and StorageUnitType_Name ilike :StorageUnitType_Name || '%'";
		}
		if (!empty($data['StorageUnitType_Nick'])) {
			$where .= " and StorageUnitType_Nick ilike :StorageUnitType_Nick || '%'";
		}
		if (!empty($data['dateRange'][0])) {
			$data['StorageUnitType_BegDate'] = $data['dateRange'][0];
			$where .= " and StorageUnitType_BegDate >= :StorageUnitType_BegDate";
		}
		if (!empty($data['dateRange'][1])) {
			$data['StorageUnitType_EndDate'] = $data['dateRange'][1];
			$where .= " and StorageUnitType_EndDate <= :StorageUnitType_EndDate";
		}
		unset($data['dateRange']);

		$query = "
			select
			-- select
				StorageUnitType_id as \"StorageUnitType_id\",
				StorageUnitType_Code as \"StorageUnitType_Code\",
				StorageUnitType_Nick as \"StorageUnitType_Nick\",
				StorageUnitType_Name as \"StorageUnitType_Name\",
				(to_char(StorageUnitType_BegDate, 'dd.mm.yyyy') || ' - ' || 
					coalesce(to_char(StorageUnitType_EndDate, 'dd.mm.yyyy'), '')) as \"dateRange\"
			-- end select
			from
			-- from
			 	StorageUnitType
			where
				{$where}
			-- end from
			order by
			-- order by
				\"StorageUnitType_id\"
			-- end order by
		";

		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $data);
		$result_count = $this->db->query(getCountSQLPH($query), $data);
		if (is_object($result_count)) {
			$cnt_arr = $result_count->result('array');
			$count = $cnt_arr[0]['cnt'];
			unset($cnt_arr);
		} else {
			$count = 0;
		}

		if (is_object($result)) {
			$response = array();
			$response['data'] = $result->result('array');
			$response['totalCount'] = $count;
			return $response;
		} else {
			return false;
		}
	}

	/**
	 * Получение данных для формы редактирования наименований мест хранения товара
	 */
	function loadGoodsStorage($data) {
		$params = array('StorageUnitType_id' => $data['StorageUnitType_id']);

		$query = "
			select
				StorageUnitType_id as \"StorageUnitType_id\",
				StorageUnitType_Code as \"StorageUnitType_Code\",
				StorageUnitType_Nick as \"StorageUnitType_Nick\",
				StorageUnitType_Name as \"StorageUnitType_Name\",
				(to_char(StorageUnitType_BegDate, 'dd.mm.yyyy') || ' - ' || 
					coalesce(to_char(StorageUnitType_EndDate, 'dd.mm.yyyy'), '')) as \"dateRange\"
			from
				StorageUnitType
			where
				StorageUnitType_id = :StorageUnitType_id
			limit 1
		";

		$result = $this->db->query($query, $params);

		if (!is_object($result)) {
			return false;
		}

		return $result->result('array');
	}

	/**
	 * Проверка на уникальность наименований мест хранения товара
	 */
	function checkGoodsStorage($data) {
		$query = "
				SELECT
					StorageUnitType_Name as \"StorageUnitType_Name\", 
					StorageUnitType_Nick as \"StorageUnitType_Nick\"
				FROM 
					StorageUnitType
				WHERE 
					(lower(StorageUnitType_Name) = lower(:StorageUnitType_Name::varchar) OR
					lower(StorageUnitType_Nick) = lower(:StorageUnitType_Nick::varchar)) AND
					StorageUnitType_id != coalesce(:StorageUnitType_id::bigint, 0)
			";
		$params = array(
			'StorageUnitType_Name' => $data['StorageUnitType_Name'],
			'StorageUnitType_Nick' => $data['StorageUnitType_Nick'],
			'StorageUnitType_id' => $data['StorageUnitType_id']
		);
		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Сохранение наименований мест хранения товара
	 */
	function saveGoodsStorage($data) {
		$response = array('success' => false);

		if ((!isset($data['StorageUnitType_id'])) || ($data['StorageUnitType_id'] <= 0)) {
			$procedure = 'p_StorageUnitType_ins';
		} else {
			$procedure = 'p_StorageUnitType_upd';
		}
		$query = "
		select
			StorageUnitType_id as \"StorageUnitType_id\",
			Error_Code as \"Error_Code\",
			Error_Message as \"Error_Msg\"
		from " . $procedure . "(
			StorageUnitType_id := :StorageUnitType_id,
			StorageUnitType_Name := :StorageUnitType_Name,
			StorageUnitType_Nick := :StorageUnitType_Nick,
			StorageUnitType_Code := :StorageUnitType_Code,
			StorageUnitType_BegDate := :StorageUnitType_BegDate,
			StorageUnitType_EndDate := :StorageUnitType_EndDate,
			pmUser_id := :pmUser_id
		)
		";
		$params = array(
			'StorageUnitType_id' => $data['StorageUnitType_id'],
			'StorageUnitType_Name' => $data['StorageUnitType_Name'],
			'StorageUnitType_Nick' => $data['StorageUnitType_Nick'],
			'StorageUnitType_Code' => $data['StorageUnitType_Code'],
			'StorageUnitType_BegDate' => $data['dateRange'][0],
			'StorageUnitType_EndDate' => $data['dateRange'][1],
			'pmUser_id' => pmAuthUser::find($data['session']['login'])->pmuser_id
		);

		$result = $this->db->query($query, $params);

		if ( !is_object($result) ) {
			return $response;
		}
		$arr = $result->result('array');
		if ( !is_array($arr) ) {
			return $response;
		}

		return array('success' => true, 'StorageUnitType_id' => $arr[0]['StorageUnitType_id']);
	}

	/**
	 * Проверка наличия ссылок на наименования мест хранения товара в других таблицах
	 *
	 */
	function checkGoodsStorageIsUsed($storageUnitTypeId) {
		$queryParams = array(
			'StorageUnitType_id' => $storageUnitTypeId
		);
		$response = array(
			'cnt' => 0
		,'Error_Msg' => ''
		);
		/*  Было:
		$query = "
			SELECT
				SCHEMA_NAME(f.schema_id) as SchemaName,
				OBJECT_NAME(f.parent_object_id) AS TableName,
				COL_NAME(fc.parent_object_id, fc.parent_column_id) AS ColumnName
			FROM sys.foreign_keys AS f
				INNER JOIN sys.foreign_key_columns AS fc ON f.OBJECT_ID = fc.constraint_object_id
			WHERE  OBJECT_NAME (f.referenced_object_id) = 'StorageUnitType'
			order by
				TableName
		";*/

		//todo разобраться, какой вариант вернее
		//Вариант для postgre, предложенный Рязановым Александром
		$query = "
			select
				parent_schema as \"SchemaName\",
				parent_table AS \"TableName\",
				parent_column AS \"ColumnName\"
			from dbo.getforeignkey (null,'StorageUnitType')
		";

		/*Другой вариант
		$query = "
			SELECT
				f.table_schema as \"SchemaName\",
				f.table_name AS \"TableName\",
				fc.column_name AS \"ColumnName\"
			FROM information_schema.table_constraints AS f
				INNER JOIN information_schema.key_column_usage AS fc ON f.constraint_name = fc.constraint_name
			WHERE  (f.table_name) = 'StorageUnitType'
			order by
				f.table_name
		";*/

		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			$response['Error_Msg'] = 'Ошибка при выполнении запроса к базе данных (получение списка связанных таблиц)';
			return $response;
		}

		$queryResponse = $result->result('array');

		if (empty($queryResponse)) {
			return $response;
		}

		if ( !is_array($queryResponse) ) {
			$response['Error_Msg'] = 'Ошибка при получении списка связанных таблиц';
			return $response;
		}

		$fieldList = array();
		$queryList = array();
		$schema = '';
		$table = '';

		foreach ( $queryResponse as $array ) {
			if ( $table != $array['TableName'] ) {
				if ( !empty($schema) && !empty($table) && count($fieldList) > 0 ) {
					$queryList[] = "(select '" . $table . "' as \"id\" from " . $schema . "." . $table . " where :StorageUnitType_id in (" . implode(', ', $fieldList) . ")" . ($table == 'StorageUnitType' ? " and StorageUnitType_id != :StorageUnitType_id " : "") . " limit 1)";
				}

				$fieldList = array();
				$schema = $array['SchemaName'];
				$table = $array['TableName'];
			}

			$fieldList[] = $array['ColumnName'];
		}

		if ( !empty($table) && count($fieldList) > 0 ) {
			$queryList[] = "(select '" . $table . "' as \"id\" from " . $table . " where :StorageUnitType_id in (" . implode(', ', $fieldList) . ")" . ($table == 'StorageUnitType' ? " and StorageUnitType_id != :StorageUnitType_id " : "") . " limit 1)";
		}

		$query = implode(' union ', $queryList);

		$result = $this->db->query($query, $queryParams);
		if ( !is_object($result) ) {
			$response['Error_Msg'] = 'Ошибка при выполнении запроса к базе данных (проверка ссылок на услугу в таблицах базы данных)';
			return $response;
		}

		$queryResponse = $result->result('array');

		if ( !is_array($queryResponse) ) {
			$response['Error_Msg'] = 'Ошибка при проверке ссылок на услугу в таблицах базы данных';
		}
		else if ( count($queryResponse) > 0 ) {
			$response['cnt'] = count($queryResponse);
			$response['Error_Msg'] = 'Операция невозможна, т.к. услуга уже была использована ранее';
		}

		return $response;
	}

	/**
	 * Удаление наименования мест хранения товара
	 */
	function deleteGoodsStorage($data) {
		$params = array(
			'StorageUnitType_id'=>$data['id']
		);
		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_StorageUnitType_del(
				StorageUnitType_id := :StorageUnitType_id
			)
		";

		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение списка складов для грида
	 */
	function loadStorageGrid($data) {
		$with = array();
		$params = array();
		$join = "";
		$where = "(1 = 1)";

		if (!empty($data['Lpu_id'])) {
			$where .= " and i_ssl.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		}
		if (!empty($data['LpuBuilding_id'])) {
			$where .= " and i_ssl.LpuBuilding_id = :LpuBuilding_id";
			$params['LpuBuilding_id'] = $data['LpuBuilding_id'];
		}
		if (!empty($data['LpuUnit_id'])) {
			$where .= " and i_ssl.LpuUnit_id = :LpuUnit_id";
			$params['LpuUnit_id'] = $data['LpuUnit_id'];
		}
		if (!empty($data['LpuSection_id'])) {
			$where .= " and (i_ssl.LpuSection_id = :LpuSection_id or i_ls.LpuSection_pid = :LpuSection_id)";
			$params['LpuSection_id'] = $data['LpuSection_id'];
		}
		if (!empty($data['Org_id'])) {
			$where .= " and i_ssl.Org_id = :Org_id";
			$params['Org_id'] = $data['Org_id'];
		}
		if (!empty($data['OrgStruct_id'])) {
			$where .= " and i_ssl.OrgStruct_id = :OrgStruct_id";
			$params['OrgStruct_id'] = $data['OrgStruct_id'];
		}
		if (!empty($data['MedService_id'])) {
			$where .= " and i_ssl.MedService_id = :MedService_id";
			$params['MedService_id'] = $data['MedService_id'];
		}

		if (!empty($data['isClose']) && $data['isClose'] == 1) {
			$where .= " and (i_s.Storage_endDate is null or i_s.Storage_endDate > dbo.tzGetDate())";
		} elseif (!empty($data['isClose']) && $data['isClose'] == 2) {
			$where .= " and i_s.Storage_endDate <= dbo.tzGetDate()";
		}

		if (!empty($data['LpuUnitType_id'])) {
			$where .= " and i_lu.LpuUnitType_id = :LpuUnitType_id";
			$params['LpuUnitType_id'] = $data['LpuUnitType_id'];
		}

		if (!empty($data['Storage_pid'])) {
			$with[] = " storage_tree as (
				(select
					i_s.Storage_id,
					i_s.Storage_pid
				from
					v_Storage i_s
				where
					i_s.Storage_id = :Storage_pid)
				union all
				(select
					i_s.Storage_id,
					i_s.Storage_pid
				from
					v_Storage i_s
					inner join storage_tree i_tr on i_s.Storage_pid = i_tr.Storage_id)
			)";
			$join .= " left join storage_tree i_str on i_str.Storage_id = i_s.Storage_id ";
			$where .= " and i_str.Storage_id is not null";
			$params['Storage_pid'] = $data['Storage_pid'];
		}

		//базовая выборка
		$sll_query = "
			ssl_list as (
				select
					i_ssl.*
				from		
					StorageStructLevel i_ssl
					inner join Storage i_s on i_s.Storage_id = i_ssl.Storage_id
					left join v_LpuUnit i_lu on i_lu.LpuUnit_id = i_ssl.LpuUnit_id					
					left join v_LpuSection i_ls on i_ls.LpuSection_id = i_ssl.LpuSection_id
					{$join}
				where
					{$where}
			)
		";

		if (!empty($data['LpuBuilding_id'])) { //если передано подразделение, значит склады загружаются с нижних уровней струтуры МО (ниже уровня МО), тогда надо использовать иной базовый запрос
			//используем упрощенную базовую выборку
			$with[] = "
				base_ssl_list as (
					select
						i_ssl.StorageStructLevel_id,
						i_s.Storage_id,
						i_s.Storage_pid,
						i_ssl.MedService_id
					from		
						v_StorageStructLevel i_ssl
						inner join Storage i_s on i_s.Storage_id = i_ssl.Storage_id
						left join v_LpuUnit i_lu on i_lu.LpuUnit_id = i_ssl.LpuUnit_id
						left join v_LpuSection i_ls on i_ls.LpuSection_id = i_ssl.LpuSection_id
						{$join}
					where
						{$where}
				)
			";

			//при помощи рекурсивного запроса добавляем к базовой выборке потомков складов (вместе с информацией об уровнях к которым они прицеплены)
			$with[] = "
				rec_ssl_list as (
					(select
						i_bsl.StorageStructLevel_id,
						i_bsl.Storage_id,
						i_bsl.Storage_pid
					from
						base_ssl_list i_bsl)
					union all
					(select
						i_ssl.StorageStructLevel_id,
						i_s.Storage_id,
						i_s.Storage_pid
					from
						v_StorageStructLevel i_ssl
						inner join v_Storage i_s on i_s.Storage_id = i_ssl.Storage_id 						
						inner join rec_ssl_list i_rsl on i_s.Storage_pid = i_rsl.Storage_id
							and i_s.Storage_id != i_rsl.Storage_id)
				)
			";

			//оформляем результат в формате базовой выборки, избавляясь при этом от дублей
			$sll_query = "
				ssl_list as (
					select distinct
						i_ssl.*
					from
						rec_ssl_list i_rsl
						left join v_StorageStructLevel i_ssl on i_ssl.StorageStructLevel_id = i_rsl.StorageStructLevel_id
						left join v_MedService i_ms on i_ms.MedService_id = i_ssl.MedService_id
						left join v_MedServiceType i_mst on i_mst.MedServiceType_id = i_ms.MedServiceType_id
					where
						i_ssl.MedService_id is null or -- нет привязанной службы
						i_mst.MedServiceType_SysNick <> 'merch' or -- служба не является службой АРМ Товаровед
						i_ssl.MedService_id in ( -- служба есть в базовой выборке
							select
								MedService_id
							from
								base_ssl_list
						)
				)
			";
		}

		$with[] = $sll_query;

		if (count($with) > 0) {
			$with = "with recursive ".join(', ', $with);
		} else {
			$with = "";
		}

		$query = "
			{$with}
			select
				S.Storage_id as \"Storage_id\",
				SSL.StorageStructLevel_id as \"StorageStructLevel_id\",
				ST.StorageType_id as \"StorageType_id\",
				A.Address_id as \"Address_id\",
				S.Storage_Name as \"Storage_Name\",
				S.Storage_Code as \"Storage_Code\",
				P_S.Storage_Name as \"Storage_pName\",
				coalesce(MS.MedService_Nick, P_MS.MedService_Nick) as \"MedService_Nick\",
				ST.StorageType_Name as \"StorageType_Name\",
				A.Address_Address as \"Address_Address\",
				coalesce(MS.MedService_Nick,LS.LpuSection_Name, LU.LpuUnit_Name, LB.LpuBuilding_Nick, l.Lpu_Nick, OrgStruct_Nick, l.Org_Nick) as \"StorageStructLevel_Name\",
                lf.Lpu_id as \"Storage4Lpu_id\",
				lf.Lpu_Nick as \"Storage4Lpu_Nick\",				
                to_char(S.Storage_begDate, 'dd.mm.yyyy') as \"Storage_begDate\",
				to_char(S.Storage_endDate, 'dd.mm.yyyy') as \"Storage_endDate\"
			from
			 	ssl_list ssl
			 	inner join Storage S on S.Storage_id = SSL.Storage_id
			 	left join Storage P_S on P_S.Storage_id = S.Storage_pid
				left join OrgFarmacyIndex i on i.Storage_id = s.Storage_id
			 	left join v_Lpu Lf on Lf.Lpu_id = i.Lpu_id
				left join Address A on A.Address_id = S.Address_id
				left join StorageType ST on ST.StorageType_id = S.StorageType_id
				left join v_OrgStruct OS on OS.OrgStruct_id = SSL.OrgStruct_id
				left join v_Lpu L on L.Lpu_id = SSL.Lpu_id
				left join v_LpuBuilding LB on LB.LpuBuilding_id = SSL.LpuBuilding_id
				left join v_LpuUnit LU on LU.LpuUnit_id = SSL.LpuUnit_id
				left join v_LpuSection LS on LS.LpuSection_id = SSL.LpuSection_id
				left join v_MedService MS on MS.MedService_id = SSL.MedService_id
				left join lateral(
					select
						i_ms.MedService_Nick
					from
						v_StorageStructLevel i_ssl
						left join v_MedService i_ms on i_ms.MedService_id = i_ssl.MedService_id
					where
						i_ssl.Storage_id = P_S.Storage_id and
						i_ssl.MedService_id is not null
					limit 1
				) P_MS on true
			order by
				S.Storage_Code 
		";

		//echo getDebugSQL($query, $params);exit;
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		$response = $result->result('array');

		return array('data'=>$response);
	}

	/**
	 * Получение списка структурных уровней склада для грида
	 */
	function loadStorageStructLevelGrid($data) {
		$params = array('Storage_id' => $data['Storage_id']);

		$query = "
			select
				StorageStructLevel_id as \"StorageStructLevel_id\",
				Storage_id as \"Storage_id\",
				1 as \"RecordStatus_Code\",
				SSL.Org_id as \"Org_id\",
				SSL.OrgStruct_id as \"OrgStruct_id\",
				SSL.Lpu_id as \"Lpu_id\",
				SSL.LpuBuilding_id as \"LpuBuilding_id\",
				SSL.LpuUnit_id as \"LpuUnit_id\",
				SSL.LpuSection_id as \"LpuSection_id\",
				SSL.MedService_id as \"MedService_id\",
				coalesce(MS.MedService_Nick,LS.LpuSection_Name, LU.LpuUnit_Name, LB.LpuBuilding_Nick, L.Lpu_Nick, OS.OrgStruct_Nick, O.Org_Nick) as \"StorageStructLevel_Name\",
				case
					when MS.MedService_id is not null then 'Служба'
					when LS.LpuSection_id is not null then 'Отделение'
					when LU.LpuUnit_id is not null then 'Группа отделений'
					when LB.LpuBuilding_id is not null then 'Подразделение'
					when OS.OrgStruct_id is not null then 'Структурный уровень организации'
					when L.Lpu_id is not null then 'МО'
					when O.Org_id is not null then 'Организация'
				end as \"StorageStructLevelType_Name\"
			from
				v_StorageStructLevel SSL
				left join v_Lpu L on L.Lpu_id = SSL.Lpu_id
				left join v_LpuBuilding LB on LB.LpuBuilding_id = SSL.LpuBuilding_id
				left join v_LpuUnit LU on LU.LpuUnit_id = SSL.LpuUnit_id
				left join v_LpuSection LS on LS.LpuSection_id = SSL.LpuSection_id
				left join v_MedService MS on MS.MedService_id = SSL.MedService_id
				left join v_Org O on O.Org_id = SSL.Org_id
				left join v_OrgStruct OS on OS.OrgStruct_id = SSL.OrgStruct_id
			where
				SSL.Storage_id = :Storage_id
		";
		//echo getDebugSQL($query, $params);exit;
		$result = $this->db->query($query, $params);

		if (!is_object($result)) {
			return false;
		}

		return $result->result('array');
	}

	/**
	 * Получение списка МОЛ для грида
	 */
	function loadMolGrid($data) {
		$params = array('Storage_id' => $data['Storage_id']);

		$query = "
			select
				m.Mol_id as \"Mol_id\",
				m.Person_id as \"Person_id\",
				m.MedPersonal_id as \"MedPersonal_id\",
				m.MedStaffFact_id as \"MedStaffFact_id\",
				1 as \"RecordStatus_Code\",
				m.Mol_Code as \"Mol_Code\",
				CASE WHEN (coalesce(m.Person_id,0)=0) 
					THEN rtrim(ltrim(coalesce(mwps.Person_SurName || ' ', '') || coalesce(mwps.Person_FirName || ' ', '') || coalesce(mwps.Person_SecName,'')))
					ELSE rtrim(ltrim(coalesce(ps.Person_SurName || ' ', '') || coalesce(ps.Person_FirName || ' ', '') || coalesce(ps.Person_SecName,'')))
				END as \"Person_FIO\",
				to_char(m.Mol_begDT, 'dd.mm.yyyy') as \"Mol_begDT\",
				to_char(m.Mol_endDT, 'dd.mm.yyyy') as \"Mol_endDT\",
				coalesce(l.Org_id, ssl.Org_id) as \"Org_id\"
			from
				v_Mol m
				left join v_PersonState ps on ps.Person_id = m.Person_id
				left join persis.MedWorker mw  on mw.id = m.MedPersonal_id
				left join v_PersonState mwps on mwps.Person_id = mw.Person_id
				left join v_Lpu_all l on l.Lpu_id = m.Lpu_id
				left join lateral(
					select
						coalesce(t.Org_id, t1.Org_id) as Org_id
					from
						v_StorageStructLevel t
						left join v_Lpu_all t1 on t1.Lpu_id = t.Lpu_id
					where
						Storage_id = m.Storage_id
					limit 1
				) ssl on true
			where
				m.Storage_id = :Storage_id
		";
		//echo getDebugSQL($query, $params);exit;
		$result = $this->db->query($query, $params);

		if (!is_object($result)) {
			return false;
		}

		return $result->result('array');
	}

	/**
	 * Получение данных для строки в гриде структурных уровней склада
	 */
	function getRowStorageStructLevel($data) {
		$type = $this->getStorageStructLevelType($data);

		$object = $type['object_nick'];
		$object_value = $data[$type['object_nick'].'_id'];
		$object_field = $type['object_field'];
		$params = array(
			'StorageStructLevel_id' => $data['StorageStructLevel_id'],
			'Storage_id' => $data['Storage_id'],
			'RecordStatus_Code' => $data['RecordStatus_Code'],
			'Lpu_id' => $data['Lpu_id'],
			'LpuBuilding_id' => $data['LpuBuilding_id'],
			'LpuUnit_id' => $data['LpuUnit_id'],
			'LpuSection_id' => $data['LpuSection_id'],
			'MedService_id' => $data['MedService_id'],
			'Org_id' => $data['Org_id'],
			'OrgStruct_id' => $data['OrgStruct_id'],
			'StorageStructLevelType_Nick' => $type['object_nick'],
			'StorageStructLevelType_Name' => $type['object_name'],
			'object_value' => $object_value
		);

		$query = "
			select
				:StorageStructLevel_id as \"StorageStructLevel_id\",
				:Storage_id as \"Storage_id\",
				:RecordStatus_Code as \"RecordStatus_Code\",
				:Lpu_id as \"Lpu_id\",
				:LpuBuilding_id as \"LpuBuilding_id\",
				:LpuUnit_id as \"LpuUnit_id\",
				:LpuSection_id as \"LpuSection_id\",
				:MedService_id as \"MedService_id\",
				:Org_id as \"Org_id\",
				:OrgStruct_id as \"OrgStruct_id\",
				:StorageStructLevelType_Nick as \"StorageStructLevelType_Nick\",
				:StorageStructLevelType_Name as \"StorageStructLevelType_Name\",
				o.{$object_field} as \"StorageStructLevel_Name\"
			from v_{$object} o
			where o.{$object}_id = :object_value
			limit 1
		";
		$response = $this->getFirstRowFromQuery($query, $params);
		if ($response) {
			$response = array('success' => true, 'data' => $response);
		} else {
			$response = array('success' => false);
		}

		return $response;
	}

	/**
	 * Получение информации о структурном уровне
	 */
	function getStorageStructLevelType($data) {
		$object_nick = '';
		$object_name = '';
		$object_field = '';

		if (!empty($data['MedService_id'])) {
			$object_nick = 'MedService';
			$object_name = 'Служба';
			$object_field = 'MedService_Nick';
		} else
			if (!empty($data['LpuSection_id'])) {
				$object_nick = 'LpuSection';
				$object_name = 'Отделение';
				$object_field = 'LpuSection_Name';
			} else
				if (!empty($data['LpuUnit_id'])) {
					$object_nick = 'LpuUnit';
					$object_name = 'Группа отделений';
					$object_field = 'LpuUnit_Name';
				} else
					if (!empty($data['LpuBuilding_id'])) {
						$object_nick = 'LpuBuilding';
						$object_name = 'Подразделение';
						$object_field = 'LpuBuilding_Nick';
					} else
						if (!empty($data['OrgStruct_id'])) {
							$object_nick = 'OrgStruct';
							$object_name = 'Структурный уровень организации';
							$object_field = 'OrgStruct_Nick';
						} else
							if (!empty($data['Lpu_id'])) {
								$object_nick = 'Lpu';
								$object_name = 'МО';
								$object_field = 'Lpu_Nick';
							} else
								if (!empty($data['Org_id'])) {
									$object_nick = 'Org';
									$object_name = 'Организация';
									$object_field = 'Org_Nick';
								}

		return array(
			'object_nick' => $object_nick,
			'object_name' => $object_name,
			'object_field' => $object_field
		);
	}

	/**
	 * Получение данных для формы редатирования склада
	 */
	function loadStorageForm($data) {
		$params = array('Storage_id' => $data['Storage_id']);

		$query = "
			select
				S.Storage_id as \"Storage_id\",
				S.Storage_pid as \"Storage_pid\",
				S.Storage_Code as \"Storage_Code\",
				S.Storage_Name as \"Storage_Name\",
				S.Storage_Area as \"Storage_Area\",
				S.Storage_Vol as \"Storage_Vol\",
				S.StorageRecWriteType_id as \"StorageRecWriteType_id\",
				S.StorageType_id as \"StorageType_id\",
				S.Storage_IsPKU as \"Storage_IsPKU\",
				S.TempConditionType_id as \"TempConditionType_id\",
				to_char(S.Storage_begDate, 'dd.mm.yyyy') as \"Storage_begDate\",
				to_char(S.Storage_endDate, 'dd.mm.yyyy') as \"Storage_endDate\",
				A.Address_id as \"Address_id\",
				A.Address_Zip as \"Address_Zip\",
				A.KLCountry_id as \"KLCountry_id\",
				A.KLRgn_id as \"KLRgn_id\",
				A.KLSubRgn_id as \"KLSubRgn_id\",
				A.KLCity_id as \"KLCity_id\",
				A.KLCity_id as \"KLCity_id\",
				A.KLTown_id as \"KLTown_id\",
				A.KLStreet_id as \"KLStreet_id\",
				A.Address_House as \"Address_House\",
				A.Address_Corpus as \"Address_Corpus\",
				A.Address_Flat as \"Address_Flat\",
				A.Address_Address as \"Address_Address\"
			from
				v_Storage S
				left join v_Address A on A.Address_id = S.Address_id
			where
				S.Storage_id = :Storage_id
			limit 1
		";

		$result = $this->db->query($query, $params);

		if (!is_object($result)) {
			return false;
		}

		return $result->result('array');
	}

	/**
	 * Сохранение склада
	 */
	function saveStorage($data) {
		$params = array(
			'Storage_id' => $data['Storage_id'],
			'Storage_Code' => $data['Storage_Code'],
			'Storage_Name' => $data['Storage_Name'],
			'Storage_Area' => $data['Storage_Area'],
			'Storage_Vol' => $data['Storage_Vol'],
			'StorageRecWriteType_id' => $data['StorageRecWriteType_id'],
			'StorageType_id' => $data['StorageType_id'],
			'Storage_IsPKU' => $data['Storage_IsPKU'],
			'TempConditionType_id' => $data['TempConditionType_id'],
			'Address_id' => $data['Address_id'],
			'Storage_begDate' => $data['Storage_begDate'],
			'Storage_endDate' => $data['Storage_endDate'],
			'pmUser_id' => $data['pmUser_id'],
			'Lpu_id' => $data['Lpu_id'],
			'Storage_pid' => !empty($data['Storage_pid']) ? $data['Storage_pid'] : null
		);

		$procedure = 'p_Storage_ins';
		if (!empty($params['Storage_id'])) {
			$procedure = 'p_Storage_upd';
		}

		$query = "
			select
				Storage_id as \"Storage_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure}(
				Storage_id := :Storage_id,
				Storage_Code := :Storage_Code,
				Storage_Name := :Storage_Name,
				Storage_Area := :Storage_Area,
				Storage_Vol := :Storage_Vol,
				StorageRecWriteType_id := :StorageRecWriteType_id,
				StorageType_id := :StorageType_id,
				Storage_IsPKU := :Storage_IsPKU,
				TempConditionType_id := :TempConditionType_id,
				Address_id := :Address_id,
				Storage_begDate := :Storage_begDate,
				Storage_endDate := :Storage_endDate,
				Storage_pid := :Storage_pid,
				pmUser_id := :pmUser_id
			)
		";

		$response = $this->getFirstRowFromQuery($query, $params);

		return $response;
	}

	/**
	 * Сохранение адреса склада
	 */
	function saveStorageAddress($data) {
		$response = false;
		$params = array(
			'Address_id' => $data['Address_id'],
			'Server_id' => $data['Server_id'],
			'KLCountry_id' => $data['KLCountry_id'],
			'KLRgn_id' => $data['KLRgn_id'],
			'KLSubRgn_id' => $data['KLSubRgn_id'],
			'KLCity_id' => $data['KLCity_id'],
			'KLTown_id' => $data['KLTown_id'],
			'KLStreet_id' => $data['KLStreet_id'],
			'Address_Zip' => $data['Address_Zip'],
			'Address_House' => $data['Address_House'],
			'Address_Corpus' => $data['Address_Corpus'],
			'Address_Flat' => $data['Address_Flat'],
			'Address_Address' => $data['Address_Address'],
			'pmUser_id' => $data['pmUser_id']
		);

		if (empty($data['Address_id'])) {
			$proc = 'ins';
		} else { // обновляем адрес
			$proc = 'upd';
		}
		$query = "
				select
					Address_id as \"Address_id\",
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from p_Address_{$proc}(
					Server_id := :Server_id,
					Address_id := :Address_id,
					KLAreaType_id := NULL, -- опреляется логикой в хранимке
					KLCountry_id := :KLCountry_id,
					KLRgn_id := :KLRgn_id,
					KLSubRgn_id := :KLSubRgn_id,
					KLCity_id := :KLCity_id,
					KLTown_id := :KLTown_id,
					KLStreet_id := :KLStreet_id,
					Address_Zip := :Address_Zip,
					Address_House := :Address_House,
					Address_Corpus := :Address_Corpus,
					Address_Flat := :Address_Flat,
					Address_Address := :Address_Address,
					pmUser_id := :pmUser_id
				)
			";
		//echo getDebugSQL($query, $params);exit;
		$response = $this->getFirstRowFromQuery($query, $params);

		return $response;
	}

	/**
	 * Удаление адреса склада
	 */
	function deleteStorageAddress($data) {
		$params = array('Address_id' => $data['Address_id']);
		$response = array('Error_Msg' => '');

		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_Address_del(
				Address_id := :Address_id
			)
		";
		$resp = $this->getFirstRowFromQuery($query, $params);
		if (!$resp) {
			$response['Error_Msg'] = 'Ошибка при удалении адреса склада';
		} else if(!empty($resp['Error_Msg'])) {
			$respone['Error_Msg'] = $resp['Error_Msg'];
		}

		return $response;
	}

	/**
	 * Изменение данных о структурных уровнях склада
	 */
	function saveStorageStructLevelData($data) {
		$list = json_decode($data['StorageStructLevelData'], true);

		foreach($list as $item) {
			if (empty($item['StorageStructLevel_id'])) {
				continue;
			}
			$resp = true;

			switch($item['RecordStatus_Code']) {
				case 0:
				case 2:
					$params = $item;
					$params['Storage_id'] = $data['Storage_id'];
					$params['Storage_Code'] = $data['Storage_Code'];
					$params['pmUser_id'] = $data['pmUser_id'];

					if (!$this->checkStorageDouble($params)) {
						return array('Error_Msg' => 'Уже сущестует склад с номером "'.$params['Storage_Code'].'"');
					}

					//проверки для уровней структуры МО
					if (!empty($params['Lpu_id'])) {
						//проверка уникальности связи уровня со службой АРМ товароведа
						$check_data = $this->checkMerchWpLinkDouble($params);
						if (!$check_data['check_result']) {
							return array('Error_Msg' => $check_data['error_msg']);
						}

						//проверка наличия связи склада со службой АРМ товароведа
						$check_data = $this->checkParentStorageExists($params);
						if (!$check_data['check_result']) {
							return array('Error_Msg' => $check_data['error_msg']);
						}
					}

					$resp = $this->saveStorageStructLevel($params);
					break;

				case 3:
					$params['StorageStructLevel_id'] = $item['StorageStructLevel_id'];
					$resp = $this->deleteStorageStructLevel($params);
					break;
			}

			if (!$resp) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Изменение данных о МОЛ
	 */
	function saveMolData($data) {
		$list = json_decode($data['MolData'], true);

		foreach($list as $item) {
			if (empty($item['Mol_id'])) {
				continue;
			}

			$resp = true;

			switch($item['RecordStatus_Code']) {
				case 0:
				case 2:
					$params = $item;
					$params['Storage_id'] = $data['Storage_id'];
					$params['Server_id'] = $data['Server_id'];
					$params['pmUser_id'] = $data['pmUser_id'];
					$resp = $this->saveMol($params);
					break;

				case 3:
					$params['Mol_id'] = $item['Mol_id'];
					$resp = $this->deleteMol($params);
					break;
			}

			if (!$resp) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Сохранение структурного уровня склада
	 */
	function saveStorageStructLevel($data) {
		$params = array(
			'StorageStructLevel_id' => null,
			'Storage_id' => $data['Storage_id'],
			'Org_id' => empty($data['Org_id']) ? null : $data['Org_id'],
			'OrgStruct_id' => empty($data['OrgStruct_id']) ? null : $data['OrgStruct_id'],
			'Lpu_id' => empty($data['Lpu_id']) ? null : $data['Lpu_id'],
			'LpuBuilding_id' => empty($data['LpuBuilding_id']) ? null : $data['LpuBuilding_id'],
			'LpuUnit_id' => empty($data['LpuUnit_id']) ? null : $data['LpuUnit_id'],
			'LpuSection_id' => empty($data['LpuSection_id']) ? null : $data['LpuSection_id'],
			'MedService_id' => empty($data['MedService_id']) ? null : $data['MedService_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		$procedure = 'p_StorageStructLevel_ins';
		if (!empty($data['StorageStructLevel_id']) && $data['StorageStructLevel_id'] > 0) {
			$params['StorageStructLevel_id'] = $data['StorageStructLevel_id'];
			$procedure = 'p_StorageStructLevel_upd';
		}

		$query = "
			select
				StorageStructLevel_id as \"StorageStructLevel_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure}(
				StorageStructLevel_id := :StorageStructLevel_id,
				Storage_id := :Storage_id,
				Org_id := :Org_id,
				OrgStruct_id := :OrgStruct_id,
				Lpu_id := :Lpu_id,
				LpuBuilding_id := :LpuBuilding_id,
				LpuUnit_id := :LpuUnit_id,
				LpuSection_id := :LpuSection_id,
				MedService_id := :MedService_id,
				pmUser_id := :pmUser_id
			)
		";
		//echo getDebugSQL($query, $params);exit;
		$response = $this->getFirstRowFromQuery($query, $params);

		return $response;
	}

	/**
	 * Сохранение МОЛ
	 */
	function saveMol($data) {
		$params = array(
			'Mol_id' => null,
			'Server_id' => $data['Server_id'],
			'Mol_Code' => $data['Mol_Code'],
			'Person_id' => empty( $data[ 'Person_id' ] ) ? null : $data[ 'Person_id' ] ,
			'MedPersonal_id' => empty( $data[ 'MedPersonal_id' ] ) ? null : $data[ 'MedPersonal_id' ] ,
			'MedStaffFact_id' => empty( $data[ 'MedStaffFact_id' ] ) ? null : $data[ 'MedStaffFact_id' ] ,
			'Mol_begDT' => isset($data['Mol_begDT']) && !empty($data['Mol_begDT']) ? $this->formatDate($data['Mol_begDT']) : null,
			'Mol_endDT' => isset($data['Mol_endDT']) && !empty($data['Mol_endDT']) ? $this->formatDate($data['Mol_endDT']) : null,
			'Storage_id' => $data['Storage_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		$procedure = 'p_Mol_ins';
		if (!empty($data['Mol_id']) && $data['Mol_id'] > 0) {
			$params['Mol_id'] = $data['Mol_id'];
			$procedure = 'p_Mol_upd';
		}

		$query = "
			select
				Mol_id as \"Mol_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure}(
				Mol_id := :Mol_id,
				Server_id := :Server_id,
				Mol_Code := :Mol_Code,
				Person_id := :Person_id,
				MedPersonal_id :=:MedPersonal_id,
				MedStaffFact_id :=:MedStaffFact_id,
				Mol_begDT := :Mol_begDT,
				Mol_endDT := :Mol_endDT,
				Storage_id := :Storage_id,
				pmUser_id := :pmUser_id
			)
		";
		//echo getDebugSQL($query, $params);exit;
		$response = $this->getFirstRowFromQuery($query, $params);

		return $response;
	}

	/**
	 * Проверка номера склада на дубли в рамках МО или организации
	 */
	function checkStorageDouble($data) {
		$params = array(
			'Storage_id' => $data['Storage_id'],
			'Org_id' => !empty($data['Org_id'])?$data['Org_id']:0,
			'Lpu_id' => !empty($data['Lpu_id'])?$data['Lpu_id']:0,
			'Storage_Code' => $data['Storage_Code']
		);

		$query = "
			select
				count(S.Storage_id) as \"Count\"
			from v_Storage S
			where S.Storage_Code = :Storage_Code
			and exists(select * from v_StorageStructLevel where Storage_id = S.Storage_id and (Org_id = :Org_id or Lpu_id = :Lpu_id))
			and S.Storage_id != :Storage_id
			limit 1
		";
		$count = $this->getFirstResultFromQuery($query, $params);

		if ($count > 0) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Проверка уникальности связи уровня со службой АРМ товароведа
	 */
	function checkMerchWpLinkDouble($data) {
		$check_result = false;
		$error_msg = null;

		$params = array(
			'Lpu_id' => $data['Lpu_id'],
			'LpuBuilding_id' => !empty($data['LpuBuilding_id'])?$data['LpuBuilding_id']:null,
			'LpuUnit_id' => !empty($data['LpuUnit_id'])?$data['LpuUnit_id']:null,
			'LpuSection_id' => !empty($data['LpuSection_id'])?$data['LpuSection_id']:null,
			'Storage_id' => $data['Storage_id'],
		);

		$query = "
			with ms_list as ( -- службы с типом АРМ товаровед, для текущего склада и его родителя
				select
					ms.MedService_id
				from
					v_Storage s
					left join v_StorageStructLevel ssl on ssl.Storage_id = s.Storage_id or ssl.Storage_id = s.Storage_pid
					left join v_MedService ms on ms.MedService_id = ssl.MedService_id
					left join v_MedServiceType mst on mst.MedServiceType_id = ms.MedServiceType_id
				where
					s.Storage_id = :Storage_id and
					mst.MedServiceType_SysNick = 'merch'
			)
			select
				coalesce((
					select
						ms.MedService_Nick
					from
						ms_list ml
						left join v_MedService ms on ms.MedService_id = ml.MedService_id
					order by
						ml.MedService_id
					limit 1
				), '') as \"MedService_Nick\",
				coalesce((
					select
						count(ml.MedService_id) as cnt
					from
						ms_list ml
				), 0) as \"ms_cnt\",
				count(ms.MedService_id) as \"other_ms_cnt\"
			from
				v_StorageStructLevel ssl
				left join v_Storage s on s.Storage_id = ssl.Storage_id
				left join v_StorageStructLevel ssl2 on ssl2.Storage_id = s.Storage_id or ssl2.Storage_id = s.Storage_pid
				left join v_MedService ms on ms.MedService_id = ssl2.MedService_id
				left join v_MedServiceType mst on mst.MedServiceType_id = ms.MedServiceType_id
			where
				ssl.Lpu_id = :Lpu_id and
				coalesce(ssl.LpuBuilding_id, 0) = coalesce(:LpuBuilding_id, 0) and
				coalesce(ssl.LpuUnit_id, 0) = coalesce(:LpuUnit_id, 0) and
				coalesce(ssl.LpuSection_id, 0) = coalesce(:LpuSection_id, 0) and
				ssl.Storage_id <> :Storage_id and -- исключаем из поиска текущий склад
				ssl.Storage_id <> coalesce((select Storage_pid from v_Storage where Storage_id = :Storage_id), 0) and -- исключаем из поиска родительский склад
				mst.MedServiceType_SysNick = 'merch' and
				ms.MedService_id not in (
					select
						MedService_id
					from
						ms_list
				)
		";
		$check_data = $this->getFirstRowFromQuery($query, $params);

		if (is_array($check_data) && count($check_data) > 0) {
			if ($check_data['ms_cnt'] > 0 && $check_data['other_ms_cnt'] > 0) {
				$check_result = false;
				$error_msg = "Указанный уровень структуры МО уже связан с АРМ «Товаровед» {$check_data['MedService_Nick']}. Один структурный уровень МО может быть связан лишь с одним АРМ «Товаровед» через склад АРМа, или его дочерний склад». Измените данные о связи уровня структуры МО и склада.";
			} else {
				$check_result = true;
			}
		} else {
			$error_msg = "При выполнении проверки произошла ошибка";
		}

		return array('check_result' => $check_result, 'error_msg' => $error_msg);
	}

	/**
	 * Проверка наличия связи склада со службой АРМ товароведа
	 */
	function checkParentStorageExists($data) {
		$check_result = false;
		$error_msg = null;

		//получение типа текущей службы
		$current_ms_nick = null;
		if (!empty($data['MedService_id'])) {
			$query = "
				select
					mst.MedServiceType_SysNick as \"MedServiceType_SysNick\"
				from	
					v_MedService ms
					left join v_MedServiceType mst on mst.MedServiceType_id = ms.MedServiceType_id
				where
					ms.MedService_id = :MedService_id
			";
			$current_ms_nick = $this->getFirstResultFromQuery($query, array(
				'MedService_id' => $data['MedService_id']
			));
		}
		//получение данных склада
		$query = "
			select
				count(ssl.StorageStructLevel_id) as \"cnt\"
			from
				v_Storage s
				left join v_StorageStructLevel ssl on ssl.Storage_id = s.Storage_id or ssl.Storage_id = s.Storage_pid
				left join v_MedService ms on ms.MedService_id = ssl.MedService_id
				left join v_MedServiceType mst on mst.MedServiceType_id = ms.MedServiceType_id
 			where
				s.Storage_id = :Storage_id and
				mst.MedServiceType_SysNick = 'merch'
		";
		$count = $this->getFirstResultFromQuery($query, array(
			'Storage_id' => $data['Storage_id']
		));

		if ($current_ms_nick != "merch" && $count < 1) {
			$check_result = false;

			//получаем данные для вывода сообщения об ошибке
			$query = "
				select
					(select Storage_Name from v_Storage where Storage_id = :Storage_id) as \"Storage_Name\",
					(select Lpu_Name from v_Lpu where Lpu_id = :Lpu_id) as \"Lpu_Name\",
					(select LpuBuilding_Name from v_LpuBuilding where LpuBuilding_id = :LpuBuilding_id) as \"LpuBuilding_Name\",
					(select LpuUnit_Name from v_LpuUnit where LpuUnit_id = :LpuUnit_id) as \"LpuUnit_Name\",
					(select LpuSection_Name from v_LpuSection where LpuSection_id = :LpuSection_id) as \"LpuSection_Name\"
			";
			$params = array(
				'Storage_id' => !empty($data['Storage_id'])?$data['Storage_id']:0,
				'Lpu_id' => !empty($data['Lpu_id'])?$data['Lpu_id']:0,
				'LpuBuilding_id' => !empty($data['LpuBuilding_id'])?$data['LpuBuilding_id']:0,
				'LpuUnit_id' => !empty($data['LpuUnit_id'])?$data['LpuUnit_id']:0,
				'LpuSection_id' => !empty($data['LpuSection_id'])?$data['LpuSection_id']:0,
			);
			$msg_data = $this->getFirstRowFromQuery($query, $params);
			if (is_array($msg_data) && count($msg_data) > 0)  {
				$msg_data['StorageStructLevel_Name'] = "";
				if (!empty($msg_data['LpuSection_Name'])) {
					$msg_data['StorageStructLevel_Name'] = $msg_data['LpuSection_Name'];
				} else if (!empty($msg_data['LpuUnit_Name'])) {
					$msg_data['StorageStructLevel_Name'] = $msg_data['LpuUnit_Name'];
				} else if (!empty($msg_data['LpuBuilding_id'])) {
					$msg_data['StorageStructLevel_Name'] = $msg_data['LpuBuilding_id'];
				} else if (!empty($msg_data['Lpu_Name'])) {
					$msg_data['StorageStructLevel_Name'] = $msg_data['Lpu_Name'];
				}

				$error_msg = "Не заполнены данные о складе, которому подчиняется склад {$msg_data['Storage_Name']} связанный с {$msg_data['StorageStructLevel_Name']}.  Для того, чтобы выполнение операций по движению медикаментов на этом складе было возможно, укажите данные о складе, которому подчиняется этот склад";
			} else {
				$error_msg = "При получении данных о структурном уровне произошла ошибка";
			}
		} else {
			$check_result = true;
		}

		return array('check_result' => $check_result, 'error_msg' => $error_msg);
	}

	/**
	 * Удаление структурного уровня склада
	 */
	function deleteStorageStructLevel($data) {
		$params = array('StorageStructLevel_id' => $data['StorageStructLevel_id']);
		$response = array('Error_Msg' => '');

		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_StorageStructLevel_del(
				StorageStructLevel_id := :StorageStructLevel_id
			)
		";

		$resp = $this->getFirstRowFromQuery($query, $params);
		if (!$resp) {
			$response['Error_Msg'] = 'Ошибка при удалении структурного уровня склада';
		} else if(!empty($resp['Error_Msg'])) {
			$respone['Error_Msg'] = $resp['Error_Msg'];
		}

		return $response;
	}

	/**
	 * Удаление МОЛ
	 */
	function deleteMol($data) {
		$params = array('Mol_id' => $data['Mol_id']);
		$response = array('Error_Msg' => '');

		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_Mol_del(
				Mol_id := :Mol_id
			)
		";

		$resp = $this->getFirstRowFromQuery($query, $params);
		if (!$resp) {
			$response['Error_Msg'] = 'Ошибка при удалении структурного уровня склада';
		} else if(!empty($resp['Error_Msg'])) {
			$respone['Error_Msg'] = $resp['Error_Msg'];
		}

		return $response;
	}

	/**
	 * Удаление склада
	 */
	function deleteStorage($data) {
		$params = array('Storage_id' => $data['Storage_id']);
		$response = array('Error_Msg' => '');

		$this->beginTransaction();

		try {
			$query = "
				select SSL.StorageStructLevel_id as \"StorageStructLevel_id\"
				from v_StorageStructLevel SSL
				where SSL.Storage_id = :Storage_id
			";
			$result = $this->db->query($query, $params);

			if (!is_object($result)) {
				throw new Exception('Ошибка при получении списка структурных уровней склада для удаления');
			}
			$storage_struct_level = $result->result('array');

			foreach($storage_struct_level as $item) {
				$resp = $this->deleteStorageStructLevel(array(
					'StorageStructLevel_id' => $item['StorageStructLevel_id']
				));
				if (!empty($resp['Error_Msg'])) {
					throw new Exception($resp['Error_Msg']);
				}
			}
			
			$query = "
				select M.Mol_id as \"Mol_id\"
				from v_Mol M
				where M.Storage_id = :Storage_id
			";
			$result = $this->db->query($query, $params);

			if (!is_object($result)) {
				throw new Exception('Ошибка при получении списка МОЛ склада для удаления');
			}
			$storage_struct_level = $result->result('array');

			foreach($storage_struct_level as $item) {
				$resp = $this->deleteMol(array(
					'Mol_id' => $item['Mol_id']
				));
				if (!empty($resp['Error_Msg'])) {
					throw new Exception($resp['Error_Msg']);
				}
			}
			
			$query = "
				select S.Address_id as \"Address_id\"
				from v_Storage S
				where S.Storage_id = :Storage_id
				limit 1
			";
			$data['Address_id'] = $this->getFirstResultFromQuery($query, $params);
			if ($data['Address_id'] === false) {
				throw new Exception('Ошибка при получении адреса склада для удаления');
			}

			$params = array(
				'StorageStructLevel_id' => null,
				'Storage_id' => $data['Storage_id'],
				'Org_id' => empty($data['Org_id']) ? null : $data['Org_id'],
				'OrgStruct_id' => empty($data['OrgStruct_id']) ? null : $data['OrgStruct_id'],
				'Lpu_id' => empty($data['Lpu_id']) ? null : $data['Lpu_id'],
				'LpuBuilding_id' => empty($data['LpuBuilding_id']) ? null : $data['OrgStruct_id'],
				'LpuUnit_id' => empty($data['LpuUnit_id']) ? null : $data['LpuUnit_id'],
				'LpuSection_id' => empty($data['LpuSection_id']) ? null : $data['LpuSection_id'],
				'MedService_id' => empty($data['MedService_id']) ? null : $data['MedService_id'],
				'pmUser_id' => $data['pmUser_id']
			);

			$query = "
				select
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from p_Storage_del(
					Storage_id := :Storage_id
				)
			";
			$resp = $this->getFirstRowFromQuery($query, $params);
			if (!$resp) {
				throw new Exception('Ошибка при удалении склада');
			} else if (!empty($resp['Error_Code']) && $resp['Error_Code'] == 23503) {
				throw new Exception('Ошибка при удалении склада');
			} else if(!empty($resp['Error_Msg'])) {
				throw new Exception($resp['Error_Msg']);
			}

			if (!empty($data['Address_id'])) {
				$resp = $this->deleteStorageAddress($data);
				if (!empty($resp['Error_Msg'])) {
					throw new Exception($resp['Error_Msg']);
				}
			}
		} catch (Exception $e) {
			$this->rollbackTransaction();
			$response['Error_Msg'] = $e->getMessage();
			return $response;
		}

		//$this->rollbackTransaction();
		$this->commitTransaction();

		return $response;
	}

	/**
	 * Получение списка сотрудников склада
	 */
	function loadStorageMedPersonalGrid($data) {
		$params = array('Storage_id' => $data['Storage_id']);

		$query = "
			select
				SMP.StorageMedPersonal_id as \"StorageMedPersonal_id\",
				SMP.Storage_id as \"Storage_id\",
				SMP.MedPersonal_id as \"MedPersonal_id\",
				SMP.Server_id as \"Server_id\",
				MP.Person_Fio as \"MedPersonal_Name\",
				to_char(SMP.StorageMedPersonal_begDT, 'dd.mm.yyyy') as \"StorageMedPersonal_begDT\",
				to_char(SMP.StorageMedPersonal_endDT, 'dd.mm.yyyy') as \"StorageMedPersonal_endDT\"
			from
				v_StorageMedPersonal SMP
				left join lateral(
					select Person_Fio
					from v_MedPersonal
					where MedPersonal_id = SMP.MedPersonal_id
					limit 1
				) as MP on true
			where
				SMP.Storage_id = :Storage_id
		";

		//echo getDebugSQL($query, $params);exit;
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		$response = $result->result('array');

		return array('data'=>$response);
	}

	/**
	 * Получение данных для формы редактирования сотрудника склада
	 */
	function loadStorageMedPersonalForm($data) {
		$params = array('StorageMedPersonal_id' => $data['StorageMedPersonal_id']);

		$query = "
			select
				SMP.StorageMedPersonal_id as \"StorageMedPersonal_id\",
				SMP.Storage_id as \"Storage_id\",
				SMP.MedPersonal_id as \"MedPersonal_id\",
				SSL.Lpu_id as \"Lpu_id\",
				to_char(SMP.StorageMedPersonal_begDT, 'dd.mm.yyyy') as \"StorageMedPersonal_begDT\",
				to_char(SMP.StorageMedPersonal_endDT, 'dd.mm.yyyy') as \"StorageMedPersonal_endDT\"
			from
				v_StorageMedPersonal SMP
				left join lateral(
					select Lpu_id
					from v_StorageStructLevel
					where Storage_id = SMP.Storage_id
					limit 1
				) as SSL on true
			where
				SMP.StorageMedPersonal_id = :StorageMedPersonal_id
		";

		//echo getDebugSQL($query, $params);exit;
		$result = $this->db->query($query, $params);

		if (!is_object($result)) {
			return false;
		}

		return $result->result('array');
	}

	/**
	 * Сохранение сотрудника склада
	 */
	function saveStorageMedPersonal($data) {
		$params = $data;

		$procedure = 'p_StorageMedPersonal_ins';
		if (!empty($data['StorageMedPersonal_id']) && $data['StorageMedPersonal_id'] > 0) {
			$params['StorageMedPersonal_id'] = $data['StorageMedPersonal_id'];
			$procedure = 'p_StorageMedPersonal_upd';
		}

		$query = "
			select
				StorageMedPersonal_id as \"StorageMedPersonal_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure}(
				StorageMedPersonal_id := :StorageMedPersonal_id,
				Storage_id := :Storage_id,
				MedPersonal_id := :MedPersonal_id,
				StorageMedPersonal_begDT := :StorageMedPersonal_begDT,
				StorageMedPersonal_endDT := :StorageMedPersonal_endDT,
				Server_id := :Server_id,
				pmUser_id := :pmUser_id
			)
		";
		//echo getDebugSQL($query, $params); exit;
		$response = $this->getFirstRowFromQuery($query, $params);

		return $response;
	}

	/**
	 * Вспомогательная функция преобразования формата даты
	 * Получает строку c датой в формате d.m.Y, возвращает строку с датой в формате Y-m-d
	 */
	function formatDate($date) {
		$d_str = null;
		if (!empty($date)) {
			$date = preg_replace('/\//', '.', $date);
			$d_arr = explode('.', $date);
			if (is_array($d_arr)) {
				$d_arr = array_reverse($d_arr);
			}
			if (count($d_arr) == 3) {
				$d_str = join('-', $d_arr);
			}
		}
		return $d_str;
	}

	/**
	 * Получение списка складов на структурном уровне
	 */
	function loadStorageStructLevelList($data) {
		$params = array();
		$filter = "(1=1)";

		if(!empty($data['Storage_id']) && $data['Storage_id'] > 0) {
			$filter .= " and Storage.Storage_id = :Storage_id";
			$params['Storage_id'] = $data['Storage_id'];
		} else {
			if(!empty($data['StorageType_id'])) {
				$filter .= " and Storage.StorageType_id = :StorageType_id";
				$params['StorageType_id'] = $data['StorageType_id'];
			}
			if(!empty($data['StorageTypeCode_List'])) {
				$filter .= " and StorageType.StorageType_Code in ({$data['StorageTypeCode_List']})";
			}
			if (!empty($data['Org_id']) && $data['Org_id'] > 0) {
				$filter .= " and SSL.Org_id = :Org_id";
				$params['Org_id'] = $data['Org_id'];
			}
			if (!empty($data['Lpu_aid']) && $data['Lpu_aid'] > 0) {
				$filter .= " and SSL.Lpu_id = :Lpu_id";
				$params['Lpu_id'] = $data['Lpu_aid'];
			}
			if (!empty($data['MedService_id']) && $data['MedService_id'] > 0) {
				$filter .= " and SSL.MedService_id = :MedService_id";
				$params['MedService_id'] = $data['MedService_id'];
			}
			if (!empty($data['query'])) {
				$filter .= " and cast(Storage.Storage_Code as varchar)||' '||Storage.Storage_Name ilike '%'||:query||'%'";
				$params['query'] = $data['query'];
			}
		}

		$query = "
			select
				SSL.StorageStructLevel_id as \"StorageStructLevel_id\",
				Storage.Storage_id as \"Storage_id\",
				Storage.StorageType_id as \"StorageType_id\",
				Storage.Storage_Code as \"Storage_Code\",
				rtrim(Storage.Storage_Name) as \"Storage_Name\",
				LS.LpuSection_id as \"LpuSection_id\",
				rtrim(LS.LpuSection_Name) as \"LpuSection_Name\",
				LB.LpuBuilding_id as \"LpuBuilding_id\",
				rtrim(LB.LpuBuilding_Name) as \"LpuBuilding_Name\"
			from
				v_StorageStructLevel SSL
					left join v_Storage Storage on Storage.Storage_id = SSL.Storage_id
					left join v_LpuBuilding LB on LB.LpuBuilding_id = SSL.LpuBuilding_id
					left join v_LpuSection LS on LS.LpuSection_id = SSL.LpuSection_id
			where
				{$filter}
			order by Storage.StorageType_id, \"Storage_Name\";
		";
		//echo getDebugSQL($query, $params);exit;
		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Функция определения принадлежности рабочего места товароведа к центральному складу СМП
	 */
	public function checkIfMerchandiserIsInSmp( $data ) {

		//Список типов ЛПУ, которые участвуют в выборке
		$lpu_code_list = array( 79 ) ;

		//Получаем текущую службу из сессии, если не передана с параметрами
		$data[ 'MedService_id' ] = (empty( $data[ 'MedService_id' ] )) ? ((empty( $data[ 'session' ][ 'CurMedService_id' ] )) ? null : $data[ 'session' ][ 'CurMedService_id' ]) : $data[ 'MedService_id' ] ;

		$rules = array( array( 'field' => 'MedService_id' , 'rules' => 'required' , 'label' => 'Идентификатор службы' , 'type' => 'id' ) ) ;
		$queryParams = $this->_checkInputData( $rules , $data , $err , false ) ;
		if ( !$queryParams || !empty( $err ) )
			return $err ;

		$lpu_code_list_str = implode( ',' , $lpu_code_list ) ;

		$is_main_storage_query = "
			SELECT
				CASE WHEN COUNT(SSL.StorageStructLevel_id) = 0 
					THEN 0
					ELSE 1
				END AS \"MedServiceHasSmpMainStorage\"
			FROM
				v_StorageStructLevel SSL
				INNER JOIN v_Lpu L on SSL.Lpu_id = L.Lpu_id
			WHERE 
				SSL.MedService_id = :MedService_id 
				AND L.LpuType_Code IN ({$lpu_code_list_str})
				AND coalesce(SSL.LpuSection_id,0) = 0
				AND coalesce(SSL.LpuUnit_id,0) = 0
				AND coalesce(SSL.LpuBuilding_id,0) = 0
			" ;

		$is_main_storage_result = $this->queryResult( $is_main_storage_query , $queryParams ) ;

		if ( !$this->isSuccessful( $is_main_storage_result ) ) {
			return $is_main_storage_result ;
		}

		$is_sub_storage_query = "
			SELECT
				CASE WHEN COUNT(SSL.StorageStructLevel_id) = 0 
					THEN 0
					ELSE 1
				END AS \"MedServiceHasSmpSubStorage\"
			FROM
				v_StorageStructLevel SSL
				INNER JOIN v_Lpu L on SSL.Lpu_id = L.Lpu_id
			WHERE 
				SSL.MedService_id = :MedService_id 
				AND L.LpuType_Code IN ({$lpu_code_list_str})
				AND (
					coalesce(SSL.LpuSection_id,0) != 0
					OR coalesce(SSL.LpuUnit_id,0) != 0
					OR coalesce(SSL.LpuBuilding_id,0) != 0
					)
			" ;

		$is_sub_storage_result = $this->queryResult( $is_sub_storage_query , $queryParams ) ;

		if ( !$this->isSuccessful( $is_sub_storage_result ) ) {
			return $is_sub_storage_result ;
		}

		return array( array_merge( $is_main_storage_result[ 0 ] , $is_sub_storage_result[ 0 ] ) ) ;
	}

	/**
	 * Функция получения главного склада СМП у текущей службы, если таковой вообще существует
	 * @return boolean
	 */
	public function getCurrentMedServiceContragentId($data) {
		//Получаем текущую службу из сессии, если не передана с параметрами
		$data[ 'MedService_id' ] = (empty( $data[ 'MedService_id' ] )) ? ((empty( $data[ 'session' ][ 'CurMedService_id' ] )) ? null : $data[ 'session' ][ 'CurMedService_id' ]) : $data[ 'MedService_id' ] ;

		$rules = array( array( 'field' => 'MedService_id' , 'rules' => 'required' , 'label' => 'Идентификатор службы' , 'type' => 'id' ) ) ;
		$queryParams = $this->_checkInputData( $rules , $data , $err, false ) ;
		if ( !$queryParams || !empty( $err ) ) return $err ;

		$query = "
			SELECT
				C.Contragent_id as \"Contragent_id\"
			FROM
				v_Contragent C
			WHERE 
				C.MedService_id = :MedService_id
			limit 1
			";

		return $this->queryResult($query , $queryParams);
	}


	/**
	 * Формирование списка складов, подчиненных службе главного склада СМП
	 */
	public function loadSmpMainStorageList($data) {

		//Получаем текущую службу из сессии, если не передана с параметрами
		$data[ 'MedService_id' ] = (empty( $data[ 'MedService_id' ] )) ? ((empty( $data[ 'session' ][ 'CurMedService_id' ] )) ? null : $data[ 'session' ][ 'CurMedService_id' ]) : $data[ 'MedService_id' ] ;

		$rules = array( array( 'field' => 'MedService_id' , 'rules' => 'required' , 'label' => 'Идентификатор службы' , 'type' => 'id' ) ) ;
		$queryParams = $this->_checkInputData( $rules , $data , $err, false ) ;
		if ( !$queryParams || !empty( $err ) ) return $err ;

		$query = " 
			SELECT DISTINCT
				S.Storage_id as \"Storage_id\",
				S.StorageType_id as \"StorageType_id\",
				S.Storage_Code as \"Storage_Code\",
				rtrim(S.Storage_Name) as \"Storage_Name\"
			FROM
				v_StorageStructLevel SSL
				LEFT JOIN v_Storage S on S.Storage_id = SSL.Storage_id
			WHERE
				SSL.MedService_id = :MedService_id
		";

		return $this->queryResult($query , $queryParams);
	}
	/**
	 * Формирование списка складов, подчиненных главному складу СМП
	 */
	public function loadSmpSubStorageList($data) {

		//Список типов ЛПУ, которые участвуют в выборке
		$lpu_code_list = array( 79 ) ;

		//Получаем текущую службу из сессии, если не передана с параметрами
		$data[ 'Lpu_id' ] = (empty( $data[ 'session' ][ 'lpu_id' ] )) ? null : $data[ 'session' ][ 'lpu_id' ];

		$rules = array( array( 'field' => 'Lpu_id' , 'rules' => 'required' , 'label' => 'Идентификатор службы' , 'type' => 'id' ) ) ;
		$queryParams = $this->_checkInputData( $rules , $data , $err, false ) ;
		if ( !$queryParams || !empty( $err ) ) return $err ;

		$lpu_code_list_str = implode(',',$lpu_code_list);

		$query = " 
			SELECT DISTINCT
				S.Storage_id as \"Storage_id\",
				S.StorageType_id as \"StorageType_id\",
				S.Storage_Code as \"Storage_Code\",
				rtrim(S.Storage_Name) as \"Storage_Name\"
			FROM
				v_StorageStructLevel SSL
				INNER JOIN v_Lpu L on SSL.Lpu_id = L.Lpu_id
				LEFT JOIN v_Storage S on S.Storage_id = SSL.Storage_id
			WHERE
				SSL.Lpu_id = :Lpu_id
				AND L.LpuType_Code IN ({$lpu_code_list_str})
				AND (
					coalesce(SSL.LpuSection_id,0) != 0
					OR coalesce(SSL.LpuUnit_id,0) != 0
					OR coalesce(SSL.LpuBuilding_id,0) != 0
				)
		";

		return $this->queryResult($query , $queryParams);
	}

	/**
	 * Получение списка МОЛов бригады СМП
	 * @return boolean
	 */
	public function getMolByEmergencyTeam($data) {

		$rules = array(
			array('field' => 'EmergencyTeam_id', 'label' => 'Идентификатор бригады', 'rules' => 'required', 'type' => 'id'),
		) ;
		$queryParams = $this->_checkInputData( $rules , $data , $err, false ) ;
		if ( !$queryParams || !empty( $err ) ) return $err ;

		$query = "
			SELECT DISTINCT
				M.MedPersonal_id as \"MedPersonal_id\",
				M.Mol_id as \"Mol_id\",
				M.Mol_Code as \"Mol_Code\",
				MPh.Person_Fin as \"Person_Fio\"
			FROM
				v_EmergencyTeam ET
				LEFT JOIN v_Mol M on (M.MedPersonal_id = ET.EmergencyTeam_HeadShift OR M.MedPersonal_id = ET.EmergencyTeam_HeadShift2)
				LEFT JOIN v_MedPersonal MPh ON (M.MedPersonal_id=MPh.MedPersonal_id)
			WHERE
				ET.EmergencyTeam_id = :EmergencyTeam_id
		";

		return $this->queryResult($query , $queryParams);


	}

	/**
	 * Получение списка МО для прикрепления к складам аптек
	 */
	public function GetLpu4FarmStorage($data) {

		$queryParams = array();

		log_message('debug', 'GetLpu4FarmStorage: Org_id='.$data['Org_id']);

		$query = "
			Select distinct i.Lpu_id as \"Lpu_id\", l.Lpu_Nick as \"Lpu_Nick\"
            from OrgFarmacyIndex i
                join OrgFarmacy farm on farm.OrgFarmacy_id = i.OrgFarmacy_id and farm.Org_id = :Org_id
                join v_Lpu l on l.Lpu_id = i.Lpu_id
            where coalesce(OrgFarmacyIndex_deleted, 1) = 1
            order by l.Lpu_Nick 
		";



		$queryParams['Org_id'] = $data['Org_id'];

		return  $this->queryResult($query , $queryParams);
	}

	/**
	 * Получение идентификатора склада по идентификатору службы
	 */
	function getStorageByMedServiceId($data) {
		$query = "
            select
                ssl.Storage_id as \"Storage_id\"
            from
                v_StorageStructLevel ssl
            where
                ssl.MedService_id = :MedService_id
            order by
                ssl.StorageStructLevel_id
			limit 1
        ";
		$result = $this->getFirstRowFromQuery($query, array(
			'MedService_id' => $data['MedService_id']
		));

		if (!empty($result['Storage_id'])) {
			$result['success'] = true;
		}

		return $result;
	}

	/**
	 * Получение списка складов по идентификатору МО или идентификатору организации (используется для комбобокса на форме редактирования складов)
	 */
	function getStorageListByOrgLpu($data) {
		$result = array();
		$params = array();

		if (!empty($data['Org_id']) || !empty($data['Lpu_id'])) {
			$where_1 = array();
			$where_2 = array();
			$where_clause_1 = "";
			$where_clause_2 = "";

			$where_1[] = "ssl.MedService_id is not null";

			if (!empty($data['Org_id'])) {
				$where_1[] = "ssl.Org_id = :Org_id";
				$params['Org_id'] = $data['Org_id'];
			}

			if (!empty($data['Lpu_id'])) {
				$where_1[] = "ssl.Lpu_id = :Lpu_id";
				$params['Lpu_id'] = $data['Lpu_id'];
			}

			if (!empty($data['Storage_id'])) {
				$where_1[] = "ssl.Storage_id = :Storage_id";
				$params['Storage_id'] = $data['Storage_id'];
			} else {
				if (!empty($data['query'])) {
					$where_2[] = "s.Storage_Name ilike :query";
					$params['query'] = '%'.$data['query'].'%';
				}
			}

			if (count($where_1) > 0) {
				$where_clause_1 = "where ".join(" and ", $where_1);
			}

			if (count($where_2) > 0) {
				$where_clause_2 = "where ".join(" and ", $where_2);
			}

			$query = "
				select
					p.Storage_id as \"Storage_id\",
					coalesce(s.Storage_Name, '') as \"Storage_Name\",
					coalesce(ms.MedService_Name, '') as \"MedService_Name\",
					(case
						when mst.MedServiceType_SysNick = 'merch' then 1
						else 0
					end) as \"MedService_IsMerch\"
				from
					(
						select
							ssl.Storage_id,
							ssl.MedService_id
						from
							v_StorageStructLevel ssl
						{$where_clause_1}
						group by
							ssl.Storage_id,
							ssl.MedService_id
					) p
					left join v_Storage s on s.Storage_id = p.Storage_id
					left join v_MedService ms on ms.MedService_id = p.MedService_id
					left join v_MedServiceType mst on mst.MedServiceType_id = ms.MedServiceType_id
				{$where_clause_2}
			";
			$result = $this->queryResult($query, $params);
		}

		return $result;
	}
}
