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

class Storage_model extends swModel {
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
			$where .= " and StorageUnitType_Code LIKE :StorageUnitType_Code + '%'";
		}
		if (!empty($data['StorageUnitType_Name'])) {
			$where .= " and StorageUnitType_Name LIKE :StorageUnitType_Name + '%'";
		}
		if (!empty($data['StorageUnitType_Nick'])) {
			$where .= " and StorageUnitType_Nick LIKE :StorageUnitType_Nick + '%'";
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
				StorageUnitType_id,
				StorageUnitType_Code,
				StorageUnitType_Nick,
				StorageUnitType_Name,
				(convert(varchar(10), StorageUnitType_BegDate, 104) + ' - ' + 
					isnull(convert(varchar(10), StorageUnitType_EndDate, 104), '')) as dateRange
			-- end select
			from
			-- from
			 	StorageUnitType WITH (nolock)
			where
				{$where}
			-- end from
			order by
			-- order by
				StorageUnitType_id
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
			select top 1
				StorageUnitType_id,
				StorageUnitType_Code,
				StorageUnitType_Nick,
				StorageUnitType_Name,
				(convert(varchar(10), StorageUnitType_BegDate, 104) + ' - ' + 
					isnull(convert(varchar(10), StorageUnitType_EndDate, 104), '')) as dateRange
			from
				StorageUnitType WITH (nolock)
			where
				StorageUnitType_id = :StorageUnitType_id
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
					StorageUnitType_Name, 
					StorageUnitType_Nick
				FROM 
					StorageUnitType WITH (nolock)
				WHERE 
					(lower(StorageUnitType_Name) = lower(:StorageUnitType_Name) OR
					lower(StorageUnitType_Nick) = lower(:StorageUnitType_Nick)) AND
					StorageUnitType_id != ISNULL(:StorageUnitType_id, 0)
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
		declare
			@Res bigint,
			@ErrCode int,
			@ErrMessage varchar(4000);
		set @Res = :StorageUnitType_id;
		exec " . $procedure . "
			@StorageUnitType_id = @Res output,
			@StorageUnitType_Name = :StorageUnitType_Name,
			@StorageUnitType_Nick = :StorageUnitType_Nick,
			@StorageUnitType_Code = :StorageUnitType_Code,
			@StorageUnitType_BegDate = :StorageUnitType_BegDate,
			@StorageUnitType_EndDate = :StorageUnitType_EndDate,
			@pmUser_id = :pmUser_id,
			@Error_Code = @ErrCode output,
			@Error_Message = @ErrMessage output;
		select @Res as StorageUnitType_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
	 */
	function checkGoodsStorageIsUsed($storageUnitTypeId) {
		$queryParams = array(
			'StorageUnitType_id' => $storageUnitTypeId
		);
		$response = array(
			'cnt' => 0
		,'Error_Msg' => ''
		);

		$query = "
			SELECT
				SCHEMA_NAME(f.[schema_id]) as SchemaName,
				OBJECT_NAME(f.parent_object_id) AS TableName,
				COL_NAME(fc.parent_object_id, fc.parent_column_id) AS ColumnName
			FROM sys.foreign_keys AS f with(nolock)
				INNER JOIN sys.foreign_key_columns AS fc with(nolock) ON f.OBJECT_ID = fc.constraint_object_id
			WHERE  OBJECT_NAME (f.referenced_object_id) = 'StorageUnitType'
			order by
				TableName
		";

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
					$queryList[] = "(select top 1 '" . $table . "' as id from " . $schema . "." . $table . " with (nolock) where :StorageUnitType_id in (" . implode(', ', $fieldList) . ")" . ($table == 'StorageUnitType' ? " and StorageUnitType_id != :StorageUnitType_id " : "") . ")";
				}

				$fieldList = array();
				$schema = $array['SchemaName'];
				$table = $array['TableName'];
			}

			$fieldList[] = $array['ColumnName'];
		}

		if ( !empty($table) && count($fieldList) > 0 ) {
			$queryList[] = "(select top 1 '" . $table . "' as id from " . $table . " with (nolock) where :StorageUnitType_id in (" . implode(', ', $fieldList) . ")" . ($table == 'StorageUnitType' ? " and StorageUnitType_id != :StorageUnitType_id " : "") . ")";
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
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :StorageUnitType_id;
			exec p_StorageUnitType_del
				@StorageUnitType_id = @Res,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
			$with[] = "storage_tree (Storage_id, Storage_pid) as (
				select
					i_s.Storage_id,
					i_s.Storage_pid
				from
					v_Storage i_s with (nolock)
				where
					i_s.Storage_id = :Storage_pid 
				union all
				select
					i_s.Storage_id,
					i_s.Storage_pid
				from
					v_Storage i_s with (nolock)
					inner join storage_tree i_tr on i_s.Storage_pid = i_tr.Storage_id
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
					StorageStructLevel i_ssl with(nolock)
					inner join Storage i_s with(nolock) on i_s.Storage_id = i_ssl.Storage_id
					left join v_LpuUnit i_lu with(nolock) on i_lu.LpuUnit_id = i_ssl.LpuUnit_id					
					left join v_LpuSection i_ls with(nolock) on i_ls.LpuSection_id = i_ssl.LpuSection_id
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
						v_StorageStructLevel i_ssl with(nolock)
						inner join Storage i_s with(nolock) on i_s.Storage_id = i_ssl.Storage_id
						left join v_LpuUnit i_lu with(nolock) on i_lu.LpuUnit_id = i_ssl.LpuUnit_id
						left join v_LpuSection i_ls with(nolock) on i_ls.LpuSection_id = i_ssl.LpuSection_id
						{$join}
					where
						{$where}
				)
			";

			//при помощи рекурсивного запроса добавляем к базовой выборке потомков складов (вместе с информацией об уровнях к которым они прицеплены)
			$with[] = "
				rec_ssl_list (StorageStructLevel_id, Storage_id, Storage_pid) as (
					select
						i_bsl.StorageStructLevel_id,
						i_bsl.Storage_id,
						i_bsl.Storage_pid
					from
						base_ssl_list i_bsl with (nolock)
					union all
					select
						i_ssl.StorageStructLevel_id,
						i_s.Storage_id,
						i_s.Storage_pid
					from
						v_StorageStructLevel i_ssl with(nolock)
						inner join v_Storage i_s with (nolock) on i_s.Storage_id = i_ssl.Storage_id 						
						inner join rec_ssl_list i_rsl on i_s.Storage_pid = i_rsl.Storage_id and i_s.Storage_id != i_rsl.Storage_id
				)
			";

			//оформляем результат в формате базовой выборки, избавляясь при этом от дублей
			$sll_query = "
				ssl_list as (
					select distinct
						i_ssl.*
					from
						rec_ssl_list i_rsl
						left join v_StorageStructLevel i_ssl with(nolock) on i_ssl.StorageStructLevel_id = i_rsl.StorageStructLevel_id
						left join v_MedService i_ms with (nolock) on i_ms.MedService_id = i_ssl.MedService_id
						left join v_MedServiceType i_mst with (nolock) on i_mst.MedServiceType_id = i_ms.MedServiceType_id
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
			$with = "with ".join(', ', $with);
		} else {
			$with = "";
		}

		$query = "
			{$with}
			select
				S.Storage_id,
				SSL.StorageStructLevel_id,
				ST.StorageType_id,
				A.Address_id,
				S.Storage_Name,
				S.Storage_Code,
				P_S.Storage_Name as Storage_pName,
				isnull(MS.MedService_Nick, P_MS.MedService_Nick) as MedService_Nick,
				ST.StorageType_Name,
				A.Address_Address,
				coalesce(MS.MedService_Nick,LS.LpuSection_Name, LU.LpuUnit_Name, LB.LpuBuilding_Nick, l.Lpu_Nick, OrgStruct_Nick, l.Org_Nick) as StorageStructLevel_Name,
                                lf.Lpu_id Storage4Lpu_id,
				lf.Lpu_Nick Storage4Lpu_Nick,				
                                convert(varchar(10), S.Storage_begDate, 104) as Storage_begDate,
				convert(varchar(10), S.Storage_endDate, 104) as Storage_endDate
			from
			 	ssl_list ssl
			 	inner join Storage S with(nolock) on S.Storage_id = SSL.Storage_id
			 	left join Storage P_S with(nolock) on P_S.Storage_id = S.Storage_pid
				left join OrgFarmacyIndex i with(nolock) on i.Storage_id = s.Storage_id
			 	left join v_Lpu Lf with(nolock) on Lf.Lpu_id = i.Lpu_id
				left join Address A with(nolock) on A.Address_id = S.Address_id
				left join StorageType ST with(nolock) on ST.StorageType_id = S.StorageType_id
				left join v_OrgStruct OS with(nolock) on OS.OrgStruct_id = SSL.OrgStruct_id
				left join v_Lpu L with(nolock) on L.Lpu_id = SSL.Lpu_id
				left join v_LpuBuilding LB with(nolock) on LB.LpuBuilding_id = SSL.LpuBuilding_id
				left join v_LpuUnit LU with(nolock) on LU.LpuUnit_id = SSL.LpuUnit_id
				left join v_LpuSection LS with(nolock) on LS.LpuSection_id = SSL.LpuSection_id
				left join v_MedService MS with(nolock) on MS.MedService_id = SSL.MedService_id
				outer apply (
					select top 1
						i_ms.MedService_Nick
					from
						v_StorageStructLevel i_ssl with(nolock)
						left join v_MedService i_ms with(nolock) on i_ms.MedService_id = i_ssl.MedService_id
					where
						i_ssl.Storage_id = P_S.Storage_id and
						i_ssl.MedService_id is not null
				) P_MS
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
				StorageStructLevel_id,
				Storage_id,
				1 as RecordStatus_Code,
				SSL.Org_id,
				SSL.OrgStruct_id,
				SSL.Lpu_id,
				SSL.LpuBuilding_id,
				SSL.LpuUnit_id,
				SSL.LpuSection_id,
				SSL.MedService_id,
				coalesce(MS.MedService_Nick,LS.LpuSection_Name, LU.LpuUnit_Name, LB.LpuBuilding_Nick, L.Lpu_Nick, OS.OrgStruct_Nick, O.Org_Nick) as StorageStructLevel_Name,
				case
					when MS.MedService_id is not null then 'Служба'
					when LS.LpuSection_id is not null then 'Отделение'
					when LU.LpuUnit_id is not null then 'Группа отделений'
					when LB.LpuBuilding_id is not null then 'Подразделение'
					when OS.OrgStruct_id is not null then 'Структурный уровень организации'
					when L.Lpu_id is not null then 'МО'
					when O.Org_id is not null then 'Организация'
				end as StorageStructLevelType_Name
			from
				v_StorageStructLevel SSL with(nolock)
				left join v_Lpu L with(nolock) on L.Lpu_id = SSL.Lpu_id
				left join v_LpuBuilding LB with(nolock) on LB.LpuBuilding_id = SSL.LpuBuilding_id
				left join v_LpuUnit LU with(nolock) on LU.LpuUnit_id = SSL.LpuUnit_id
				left join v_LpuSection LS with(nolock) on LS.LpuSection_id = SSL.LpuSection_id
				left join v_MedService MS with(nolock) on MS.MedService_id = SSL.MedService_id
				left join v_Org O with(nolock) on O.Org_id = SSL.Org_id
				left join v_OrgStruct OS with(nolock) on OS.OrgStruct_id = SSL.OrgStruct_id
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
				m.Mol_id,
				m.Person_id,
				m.MedPersonal_id,
				m.MedStaffFact_id,
				1 as RecordStatus_Code,
				m.Mol_Code,
				CASE WHEN (ISNULL(m.Person_id,0)=0) 
					THEN rtrim(ltrim(isnull(mwps.Person_SurName + ' ', '') + isnull(mwps.Person_FirName + ' ', '') + isnull(mwps.Person_SecName,'')))
					ELSE rtrim(ltrim(isnull(ps.Person_SurName + ' ', '') + isnull(ps.Person_FirName + ' ', '') + isnull(ps.Person_SecName,'')))
				END as Person_FIO,
				convert(varchar(10), m.Mol_begDT, 104) as Mol_begDT,
				convert(varchar(10), m.Mol_endDT, 104) as Mol_endDT,
				isnull(l.Org_id, ssl.Org_id) as Org_id
			from
				v_Mol m with(nolock)
				left join v_PersonState ps with (nolock) on ps.Person_id = m.Person_id
				left join persis.MedWorker mw  with (nolock) on mw.id = m.MedPersonal_id
				left join v_PersonState mwps with (nolock) on mwps.Person_id = mw.Person_id
				left join v_Lpu_all l with(nolock) on l.Lpu_id = m.Lpu_id
				outer apply(
					select top 1
						isnull(t.Org_id, t1.Org_id) as Org_id
					from
						v_StorageStructLevel t with(nolock)
						left join v_Lpu_all t1 with(nolock) on t1.Lpu_id = t.Lpu_id
					where
						Storage_id = m.Storage_id
				) ssl
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
		/*if (!empty($data['Lpu_id']) && empty($data['Org_id'])) {
			$params = array('Lpu_id' => $data['Lpu_id']);
			$query = "
				select top 1 L.Org_id
				from v_Lpu L with(nolock)
				where L.Lpu_id = :Lpu_id
			";
			$data['Org_id'] = $this->getFirstResultFromQuery($query, $params);
			if (!$data['Org_id']) {
				$response = array('success' => false);
			}
		} else if (!empty($data['Org_id']) && empty($data['Lpu_id'])) {
			$params = array('Org_id' => $data['Org_id']);
			$query = "
				select top 1 L.Lpu_id
				from v_Lpu L with(nolock)
				where L.Org_id = :Org_id
			";
			$data['Lpu_id'] = $this->getFirstResultFromQuery($query, $params);
			if (!$data['Lpu_id']) {
				$data['Lpu_id'] = null;
			}
		}*/

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
			select top 1
				:StorageStructLevel_id as StorageStructLevel_id,
				:Storage_id as Storage_id,
				:RecordStatus_Code as RecordStatus_Code,
				:Lpu_id as Lpu_id,
				:LpuBuilding_id as LpuBuilding_id,
				:LpuUnit_id as LpuUnit_id,
				:LpuSection_id as LpuSection_id,
				:MedService_id as MedService_id,
				:Org_id as Org_id,
				:OrgStruct_id as OrgStruct_id,
				:StorageStructLevelType_Nick as StorageStructLevelType_Nick,
				:StorageStructLevelType_Name as StorageStructLevelType_Name,
				o.{$object_field} as StorageStructLevel_Name
			from v_{$object} o with(nolock)
			where o.{$object}_id = :object_value
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
			select top 1
				S.Storage_id,
				S.Storage_pid,
				S.Storage_Code,
				S.Storage_Name,
				S.Storage_Area,
				S.Storage_Vol,
				S.StorageRecWriteType_id,
				S.StorageType_id,
				S.Storage_IsPKU,
				S.TempConditionType_id,
				convert(varchar(10), S.Storage_begDate, 104) as Storage_begDate,
				convert(varchar(10), S.Storage_endDate, 104) as Storage_endDate,
				A.Address_id,
				A.Address_Zip,
				A.KLCountry_id,
				A.KLRgn_id,
				A.KLSubRgn_id,
				A.KLCity_id,
				A.KLCity_id,
				A.KLTown_id,
				A.KLStreet_id,
				A.Address_House,
				A.Address_Corpus,
				A.Address_Flat,
				A.Address_Address
			from
				v_Storage S with(nolock)
				left join v_Address A with(nolock) on A.Address_id = S.Address_id
			where
				S.Storage_id = :Storage_id
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
			declare
				@Storage_id bigint = :Storage_id,
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec {$procedure}
				@Storage_id = @Storage_id output,
				@Storage_Code = :Storage_Code,
				@Storage_Name = :Storage_Name,
				@Storage_Area = :Storage_Area,
				@Storage_Vol = :Storage_Vol,
				@StorageRecWriteType_id = :StorageRecWriteType_id,
				@StorageType_id = :StorageType_id,
				@Storage_IsPKU = :Storage_IsPKU,
				@TempConditionType_id = :TempConditionType_id,
				@Address_id = :Address_id,
				@Storage_begDate = :Storage_begDate,
				@Storage_endDate = :Storage_endDate,
				@Lpu_id = :Lpu_id,
				@Storage_pid = :Storage_pid,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
			select @Storage_id as Storage_id, @Error_Code as Error_Code, @Error_Message as Error_Msg
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
			$query = "
				declare
					@Address_id bigint = null,
					@Error_Code bigint,
					@Error_Message varchar(4000);
				exec p_Address_ins
					@Server_id = :Server_id,
					@Address_id = @Address_id output,
					@KLAreaType_id = Null, -- опреляется логикой в хранимке
					@KLCountry_id = :KLCountry_id,
					@KLRgn_id = :KLRgn_id,
					@KLSubRgn_id = :KLSubRgn_id,
					@KLCity_id = :KLCity_id,
					@KLTown_id = :KLTown_id,
					@KLStreet_id = :KLStreet_id,
					@Address_Zip = :Address_Zip,
					@Address_House = :Address_House,
					@Address_Corpus = :Address_Corpus,
					@Address_Flat = :Address_Flat,
					@Address_Address = :Address_Address,
					@pmUser_id = :pmUser_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output
				select @Address_id as Address_id, @Error_Code as Error_Code, @Error_Message as Error_Msg
			";
		} else { // обновляем адрес
			$query = "
				declare
					@Address_id bigint = :Address_id,
					@Error_Code bigint,
					@Error_Message varchar(4000);
				exec p_Address_upd
					@Server_id = :Server_id,
					@Address_id = @Address_id output,
					@KLAreaType_id = NULL, -- опреляется логикой в хранимке
					@KLCountry_id = :KLCountry_id,
					@KLRgn_id = :KLRgn_id,
					@KLSubRgn_id = :KLSubRgn_id,
					@KLCity_id = :KLCity_id,
					@KLTown_id = :KLTown_id,
					@KLStreet_id = :KLStreet_id,
					@Address_Zip = :Address_Zip,
					@Address_House = :Address_House,
					@Address_Corpus = :Address_Corpus,
					@Address_Flat = :Address_Flat,
					@Address_Address = :Address_Address,
					@pmUser_id = :pmUser_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output
				select @Address_id as Address_id, @Error_Code as Error_Code, @Error_Message as Error_Msg
			";
		}
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
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec p_Address_del
				@Address_id = :Address_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
			select @Error_Code as Error_Code, @Error_Message as Error_Msg
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
			declare
				@StorageStructLevel_id bigint = :StorageStructLevel_id,
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec {$procedure}
				@StorageStructLevel_id = @StorageStructLevel_id output,
				@Storage_id = :Storage_id,
				@Org_id = :Org_id,
				@OrgStruct_id = :OrgStruct_id,
				@Lpu_id = :Lpu_id,
				@LpuBuilding_id = :LpuBuilding_id,
				@LpuUnit_id = :LpuUnit_id,
				@LpuSection_id = :LpuSection_id,
				@MedService_id = :MedService_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
			select @StorageStructLevel_id as StorageStructLevel_id, @Error_Code as Error_Code, @Error_Message as Error_Msg
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
			declare
				@Mol_id bigint = :Mol_id,
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec {$procedure}
				@Mol_id = @Mol_id output,
				@Server_id = :Server_id,
				@Mol_Code = :Mol_Code,
				@Person_id = :Person_id,
				@MedPersonal_id =:MedPersonal_id,
				@MedStaffFact_id =:MedStaffFact_id,
				@Mol_begDT = :Mol_begDT,
				@Mol_endDT = :Mol_endDT,
				@Storage_id = :Storage_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
			select @Mol_id as Mol_id, @Error_Code as Error_Code, @Error_Message as Error_Msg
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
			'Org_id' => $data['Org_id'],
			'Lpu_id' => $data['Lpu_id'],
			'Storage_Code' => $data['Storage_Code']
		);

		$query = "
			select top 1 count(S.Storage_id) as Count
			from v_Storage S with(nolock)
			where S.Storage_Code = :Storage_Code
			and exists(select * from v_StorageStructLevel with(nolock) where Storage_id = S.Storage_id and (Org_id = :Org_id or Lpu_id = :Lpu_id))
			and S.Storage_id != :Storage_id
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

		$query = "
			declare
				@Storage_pid bigint;
				
			set @Storage_pid = (select Storage_pid from v_Storage with (nolock) where Storage_id = :Storage_id);	
		
			with ms_list as ( -- службы с типом АРМ товаровед, для текущего склада и его родителя
				select
					ms.MedService_id
				from
					v_Storage s with (nolock)
					left join v_StorageStructLevel ssl with (nolock) on ssl.Storage_id = s.Storage_id or ssl.Storage_id = s.Storage_pid
					left join v_MedService ms with (nolock) on ms.MedService_id = ssl.MedService_id
					left join v_MedServiceType mst with (nolock) on mst.MedServiceType_id = ms.MedServiceType_id
				where
					s.Storage_id = :Storage_id and
					mst.MedServiceType_SysNick = 'merch'
			)
			select
				isnull((
					select top 1
						ms.MedService_Nick
					from
						ms_list ml
						left join v_MedService ms with (nolock) on ms.MedService_id = ml.MedService_id
					order by
						ml.MedService_id
				), '') as MedService_Nick,
				isnull((
					select
						count(ml.MedService_id) as cnt
					from
						ms_list ml
				), '') as ms_cnt,
				count(ms.MedService_id) as other_ms_cnt
			from
				v_StorageStructLevel ssl with (nolock)
				left join v_Storage s with (nolock) on s.Storage_id = ssl.Storage_id
				left join v_StorageStructLevel ssl2 with (nolock) on ssl2.Storage_id = s.Storage_id or ssl2.Storage_id = s.Storage_pid
				left join v_MedService ms with (nolock) on ms.MedService_id = ssl2.MedService_id
				left join v_MedServiceType mst with (nolock) on mst.MedServiceType_id = ms.MedServiceType_id
			where
				ssl.Lpu_id = :Lpu_id and
				isnull(ssl.LpuBuilding_id, 0) = isnull(:LpuBuilding_id, 0) and
				isnull(ssl.LpuUnit_id, 0) = isnull(:LpuUnit_id, 0) and
				isnull(ssl.LpuSection_id, 0) = isnull(:LpuSection_id, 0) and
				ssl.Storage_id <> :Storage_id and -- исключаем из поиска текущий склад
				ssl.Storage_id <> isnull(@Storage_pid, 0) and -- исключаем из поиска родительский склад
				mst.MedServiceType_SysNick = 'merch' and
				ms.MedService_id not in (
					select
						MedService_id
					from
						ms_list
				)
		";
		$check_data = $this->getFirstRowFromQuery($query, $data);

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
					mst.MedServiceType_SysNick
				from	
					v_MedService ms with (nolock)
					left join v_MedServiceType mst with (nolock) on mst.MedServiceType_id = ms.MedServiceType_id
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
				count(ssl.StorageStructLevel_id) as cnt
			from
				v_Storage s with (nolock)
				left join v_StorageStructLevel ssl with (nolock) on ssl.Storage_id = s.Storage_id or ssl.Storage_id = s.Storage_pid
				left join v_MedService ms with (nolock) on ms.MedService_id = ssl.MedService_id
				left join v_MedServiceType mst with (nolock) on mst.MedServiceType_id = ms.MedServiceType_id
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
					(select Storage_Name from v_Storage with (nolock) where Storage_id = :Storage_id) as Storage_Name,
					(select Lpu_Name from v_Lpu with (nolock) where Lpu_id = :Lpu_id) as Lpu_Name,
					(select LpuBuilding_Name from v_LpuBuilding with (nolock) where LpuBuilding_id = :LpuBuilding_id) as LpuBuilding_Name,
					(select LpuUnit_Name from v_LpuUnit with (nolock) where LpuUnit_id = :LpuUnit_id) as LpuUnit_Name,
					(select LpuSection_Name from v_LpuSection with (nolock) where LpuSection_id = :LpuSection_id) as LpuSection_Name
			";
			$msg_data = $this->getFirstRowFromQuery($query, $data);
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
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec p_StorageStructLevel_del
				@StorageStructLevel_id = :StorageStructLevel_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
			select @Error_Code as Error_Code, @Error_Message as Error_Msg
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
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec p_Mol_del
				@Mol_id = :Mol_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
			select @Error_Code as Error_Code, @Error_Message as Error_Msg
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
				select SSL.StorageStructLevel_id
				from v_StorageStructLevel SSL with(nolock)
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
				select top 1 S.Address_id
				from v_Storage S with(nolock)
				where S.Storage_id = :Storage_id
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
				declare
					@Error_Code bigint,
					@Error_Message varchar(4000);
				exec p_Storage_del
					@Storage_id = :Storage_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output
				select @Error_Code as Error_Code, @Error_Message as Error_Msg
			";
			$resp = $this->getFirstRowFromQuery($query, $params);
			if (!$resp) {
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
				SMP.StorageMedPersonal_id,
				SMP.Storage_id,
				SMP.MedPersonal_id,
				SMP.Server_id,
				MP.Person_Fio as MedPersonal_Name,
				convert(varchar(10), SMP.StorageMedPersonal_begDT, 104) as StorageMedPersonal_begDT,
				convert(varchar(10), SMP.StorageMedPersonal_endDT, 104) as StorageMedPersonal_endDT
			from
				v_StorageMedPersonal SMP with(nolock)
				outer apply (
					select top 1 Person_Fio
					from v_MedPersonal with(nolock)
					where MedPersonal_id = SMP.MedPersonal_id
				) as MP
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
				SMP.StorageMedPersonal_id,
				SMP.Storage_id,
				SMP.MedPersonal_id,
				SSL.Lpu_id,
				convert(varchar(10), SMP.StorageMedPersonal_begDT, 104) as StorageMedPersonal_begDT,
				convert(varchar(10), SMP.StorageMedPersonal_endDT, 104) as StorageMedPersonal_endDT
			from
				v_StorageMedPersonal SMP with(nolock)
				outer apply(
					select top 1 Lpu_id
					from v_StorageStructLevel with(nolock)
					where Storage_id = SMP.Storage_id
				) as SSL
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
			declare
				@StorageMedPersonal_id bigint = :StorageMedPersonal_id,
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec {$procedure}
				@StorageMedPersonal_id = @StorageMedPersonal_id output,
				@Storage_id = :Storage_id,
				@MedPersonal_id = :MedPersonal_id,
				@StorageMedPersonal_begDT = :StorageMedPersonal_begDT,
				@StorageMedPersonal_endDT = :StorageMedPersonal_endDT,
				@Server_id = :Server_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
			select @StorageMedPersonal_id as StorageMedPersonal_id, @Error_Code as Error_Code, @Error_Message as Error_Msg
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
				$filter .= " and str(Storage.Storage_Code)+' '+Storage.Storage_Name like '%'+:query+'%'";
				$params['query'] = $data['query'];
			}
		}

		$query = "
			select
				SSL.StorageStructLevel_id,
				Storage.Storage_id,
				Storage.StorageType_id,
				Storage.Storage_Code,
				rtrim(Storage.Storage_Name) as Storage_Name,
				LS.LpuSection_id,
				rtrim(LS.LpuSection_Name) as LpuSection_Name,
				LB.LpuBuilding_id,
				rtrim(LB.LpuBuilding_Name) as LpuBuilding_Name
			from
				v_StorageStructLevel SSL with(nolock)
				left join v_Storage Storage with(nolock) on Storage.Storage_id = SSL.Storage_id
				left join v_LpuBuilding LB with(nolock) on LB.LpuBuilding_id = SSL.LpuBuilding_id
				left join v_LpuSection LS with(nolock) on LS.LpuSection_id = SSL.LpuSection_id
			where
				{$filter}
			order by Storage.StorageType_id, Storage_Name;
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
				END AS MedServiceHasSmpMainStorage
			FROM
				v_StorageStructLevel SSL with (nolock)
				INNER JOIN v_Lpu L with (nolock) on SSL.Lpu_id = L.Lpu_id
			WHERE 
				SSL.MedService_id = :MedService_id 
				AND L.LpuType_Code IN ({$lpu_code_list_str})
				AND ISNULL(SSL.LpuSection_id,0) = 0
				AND ISNULL(SSL.LpuUnit_id,0) = 0
				AND ISNULL(SSL.LpuBuilding_id,0) = 0
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
				END AS MedServiceHasSmpSubStorage
			FROM
				v_StorageStructLevel SSL with (nolock)
				INNER JOIN v_Lpu L with (nolock) on SSL.Lpu_id = L.Lpu_id
			WHERE 
				SSL.MedService_id = :MedService_id 
				AND L.LpuType_Code IN ({$lpu_code_list_str})
				AND (
					ISNULL(SSL.LpuSection_id,0) != 0
					OR ISNULL(SSL.LpuUnit_id,0) != 0
					OR ISNULL(SSL.LpuBuilding_id,0) != 0
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
			SELECT TOP 1
				C.Contragent_id
			FROM
				v_Contragent C with (nolock)
			WHERE 
				C.MedService_id = :MedService_id
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
				S.Storage_id,
				S.StorageType_id,
				S.Storage_Code,
				rtrim(S.Storage_Name) as Storage_Name
			FROM
				v_StorageStructLevel SSL with (nolock)
				LEFT JOIN v_Storage S with (nolock) on S.Storage_id = SSL.Storage_id
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
				S.Storage_id,
				S.StorageType_id,
				S.Storage_Code,
				rtrim(S.Storage_Name) as Storage_Name

			FROM
				v_StorageStructLevel SSL with (nolock)
				INNER JOIN v_Lpu L with (nolock) on SSL.Lpu_id = L.Lpu_id
				LEFT JOIN v_Storage S with (nolock) on S.Storage_id = SSL.Storage_id
			WHERE
				SSL.Lpu_id = :Lpu_id
				AND L.LpuType_Code IN ({$lpu_code_list_str})
				AND (
					ISNULL(SSL.LpuSection_id,0) != 0
					OR ISNULL(SSL.LpuUnit_id,0) != 0
					OR ISNULL(SSL.LpuBuilding_id,0) != 0
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
				M.MedPersonal_id,
				M.Mol_id,
				M.Mol_Code,
				MPh.Person_Fin as Person_Fio
			FROM
				v_EmergencyTeam ET with (nolock)
				LEFT JOIN v_Mol M with (nolock) on (M.MedPersonal_id = ET.EmergencyTeam_HeadShift OR M.MedPersonal_id = ET.EmergencyTeam_HeadShift2)
				LEFT JOIN v_MedPersonal MPh with(nolock) ON (M.MedPersonal_id=MPh.MedPersonal_id)
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
			Declare
                                @Org_id bigint = :Org_id;
                        SElect distinct i.Lpu_id, l.Lpu_Nick from OrgFarmacyIndex i with (nolock)
                                join OrgFarmacy farm with (nolock) on farm.OrgFarmacy_id = i.OrgFarmacy_id and farm.Org_id = @Org_id
                                join v_Lpu l with (nolock) on l.Lpu_id = i.Lpu_id
                                where isnull(OrgFarmacyIndex_deleted, 1) = 1
                                        --and OrgFarmacy_id = @OrgFarmacy_id
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
            select top 1
                ssl.Storage_id
            from
                v_StorageStructLevel ssl with (nolock)
            where
                ssl.MedService_id = :MedService_id
            order by
                ssl.StorageStructLevel_id;
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
					$where_2[] = "s.Storage_Name like :query";
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
					p.Storage_id,
					isnull(s.Storage_Name, '') as Storage_Name,
					isnull(ms.MedService_Name, '') as MedService_Name,
					(case
						when mst.MedServiceType_SysNick = 'merch' then 1
						else 0
					end) as MedService_IsMerch
				from
					(
						select
							ssl.Storage_id,
							ssl.MedService_id
						from
							v_StorageStructLevel ssl with (nolock)
						{$where_clause_1}
						group by
							ssl.Storage_id,
							ssl.MedService_id
					) p
					left join v_Storage s with (nolock) on s.Storage_id = p.Storage_id
					left join v_MedService ms with (nolock) on ms.MedService_id = p.MedService_id
					left join v_MedServiceType mst with (nolock) on mst.MedServiceType_id = ms.MedServiceType_id
				{$where_clause_2}
			";
			$result = $this->queryResult($query, $params);
		}

		return $result;
	}
}