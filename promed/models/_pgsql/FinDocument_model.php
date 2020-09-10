<?php defined('BASEPATH') or die ('No direct script access allowed');

class FinDocument_model extends swPgModel {
	var $schema = "dbo"; //региональная схема

	/**
	 * Конструктор
	 */
	function __construct(){
		parent::__construct();

		//установка региональной схемы
		$config = get_config();
		$this->schema = $config['regions'][getRegionNumber()]['schema'];
	}

	/**
	 * Загрузка
	 */
	function load($data) {
		$query = "
			select
				fd.FinDocument_id as \"FinDocument_id\",
				fd.Registry_id as \"Registry_id\",
				fd.FinDocumentType_id as \"FinDocumentType_id\",
				fd.FinDocument_Number as \"FinDocument_Number\",
				fd.FinDocument_Date as \"FinDocument_Date\",
				fd.FinDocument_Sum as \"FinDocument_Sum\",
				fd.Org_id as \"Org_id\",
				fd.Org_mid as \"Org_mid\",
				fd.UslugaComplex_id as \"UslugaComplex_id\",
                rlfd.RegistryLLO_id as \"RegistryLLO_id\"
			from
				{$this->schema}.v_FinDocument fd
				left join lateral(
				    select
				        i_rlfd.RegistryLLO_id
				    from
				        {$this->schema}.v_RegistryLLOFinDocument i_rlfd
				    where
				        i_rlfd.FinDocument_id = fd.FinDocument_id
				    order by
				        i_rlfd.RegistryLLOFinDocument_id
				    limit 1
				) rlfd on true
			where
				fd.FinDocument_id = :FinDocument_id
		";
		$result = $this->db->query($query, array('FinDocument_id' => $data['FinDocument_id']));
		if (is_object($result)) {
			$result = $result->result('array');
			if (isset($result[0])) {
				return $result;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка
	 */
	function loadList($filter) {
		$where = array();
		$p = array();

		if (isset($filter['FinDocument_id']) && $filter['FinDocument_id']) {
			$where[] = 'v_FinDocument.FinDocument_id = :FinDocument_id';
			$p['FinDocument_id'] = $filter['FinDocument_id'];
		}
		if (isset($filter['RegistryLLO_id']) && $filter['RegistryLLO_id']) {
			$where[] = 'rlfd.RegistryLLO_id = :RegistryLLO_id';
			$p['RegistryLLO_id'] = $filter['RegistryLLO_id'];
		}
		if (isset($filter['FinDocumentType_id']) && $filter['FinDocumentType_id']) {
			$where[] = 'fd.FinDocumentType_id = :FinDocumentType_id';
			$p['FinDocumentType_id'] = $filter['FinDocumentType_id'];
		}

		$where_clause = implode(' and ', $where);
		if (strlen($where_clause)) {
			$where_clause = 'where '.$where_clause;
		}

		$query = "
			select
				fd.FinDocument_id as \"FinDocument_id\",
				fd.Registry_id as \"Registry_id\",
				fd.FinDocumentType_id as \"FinDocumentType_id\",
				fd.FinDocument_Number as \"FinDocument_Number\",
				to_char(fd.FinDocument_Date, 'dd.mm.yyyy') as \"FinDocument_Date\",
				fd.FinDocument_Sum as \"FinDocument_Sum\",
				fdt.FinDocumentType_Name as \"FinDocumentType_Name\"
			from
			    {$this->schema}.v_RegistryLLOFinDocument rlfd
                left join {$this->schema}.v_FinDocument fd on fd.FinDocument_id = rlfd.FinDocument_id
				left join dbo.v_FinDocumentType fdt on fdt.FinDocumentType_id = fd.FinDocumentType_id
			$where_clause
		";
		$result = $this->db->query($query, $filter);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Удаление
	 */
	function delete($data) {
		$error = array(); //для сбора ошибок
		$doc_arr = array();
		$result = array();

		if (empty($data['FinDocument_id']) && !empty($data['id'])) {
			$data['FinDocument_id'] = $data['id'];
		}

		//получаем информацию о документе
		$query = "
            select
                rlfd.RegistryLLO_id as \"RegistryLLO_id\",
                fdt.FinDocumentType_Code as \"FinDocumentType_Code\",
                rlfd.RegistryLLOFinDocument_id as \"RegistryLLOFinDocument_id\"
            from
                {$this->schema}.v_FinDocument fd
                left join {$this->schema}.v_RegistryLLOFinDocument rlfd on rlfd.FinDocument_id = fd.FinDocument_id
                left join v_FinDocumentType fdt on fdt.FinDocumentType_id = fd.FinDocumentType_id
            where
                rlfd.FinDocument_id = :FinDocument_id
        ";
		$doc_data = $this->getFirstRowFromQuery($query, array(
			'FinDocument_id' => $data['FinDocument_id']
		));
		if (!is_array($doc_data) || count($doc_data) == 0) {
			$error[] = "Не удалось получить данные документа.";
		}

		if (count($error) == 0 && $doc_data['FinDocumentType_Code'] == 1) { //1 - счёт
			//получение списка платежных документов
			$query = "
                select
                    rlfd.FinDocument_id as \"FinDocument_id\",
                    rlfd.RegistryLLOFinDocument_id as \"RegistryLLOFinDocument_id\"
                from
                    {$this->schema}.v_FinDocument fd
                    left join {$this->schema}.v_RegistryLLOFinDocument rlfd on rlfd.FinDocument_id = fd.FinDocument_id
                    left join v_FinDocumentType fdt on fdt.FinDocumentType_id = fd.FinDocumentType_id
                where
                    rlfd.RegistryLLO_id = :RegistryLLO_id and
                    fdt.FinDocumentType_Code = 2 -- 2 - платёжный документ
            ";
			$res = $this->db->query($query, array(
				'RegistryLLO_id' => $doc_data['RegistryLLO_id']
			));
			if (is_object($res)) {
				$doc_arr = $res->result('array');
			}

			if (count($doc_arr) > 0) {
				$error[] = "Счет оплачен, его удаление не возможно.";
			}
		}

		$this->beginTransaction();

		if (count($error) == 0) {
			$response = $this->deleteObject($this->schema.'.RegistryLLOFinDocument', array(
				'RegistryLLOFinDocument_id' => $doc_data['RegistryLLOFinDocument_id']
			));
			if (!empty($response['Error_Msg'])) {
				$error[] = $response['Error_Msg'];
			}
		}

		if (count($error) == 0) {
			$response = $this->deleteObject($this->schema.'.FinDocument', array(
				'FinDocument_id' => $data['FinDocument_id']
			));
			if (!empty($response['Error_Msg'])) {
				$error[] = $response['Error_Msg'];
			}
		}

		if (count($error) > 0 && $doc_data['FinDocumentType_Code'] == 1) { //1 - счёт
			$response = $this->setRegistryLLOAutoStatus(array(
				'RegistryLLO_id' => $doc_data['RegistryLLO_id'],
				'pmUser_id' => $data['pmUser_id']
			));
			$result = array_merge($result, $response);
		}

		if (count($error) > 0) {
			$this->rollbackTransaction();
			$result['Error_Msg'] = $error[0];
			return $result;
		} else {
			$this->commitTransaction();
			$result['Error_Code'] = null;
			$result['Error_Msg'] = null;
			return $result;
		}
	}

	/**
	 * Сохранение спецификации из JSON
	 */
	function saveFinDocumentSpecFromJSON($data) {
		if (!empty($data['json_str']) && $data['RegistryLLO_id'] > 0) {
			ConvertFromWin1251ToUTF8($data['json_str']);
			$dt = (array) json_decode($data['json_str']);

			foreach($dt as $record) {
				ConvertFromUTF8ToWin1251($record->FinDocument_Number);
				switch($record->state) {
					case 'add':
					case 'edit':
						$response = $this->saveObject($this->schema.'.FinDocument', array(
							'FinDocument_id' => $record->state == 'add' ? 0 : $record->FinDocument_id,
							'RegistryLLO_id' => $data['RegistryLLO_id'],
							'FinDocumentType_id' => 2, //Платёжное поручение
							'FinDocument_Number' => $record->FinDocument_Number,
							'FinDocument_Date' => join('-', array_reverse(explode('.', $record->FinDocument_Date))),
							'FinDocument_Sum' => $record->FinDocument_Sum,
							'pmUser_id' => $data['pmUser_id']
						));
						if ($record->state == 'add' && !empty($response['FinDocument_id'])) { //сохранение связи между реестром и счетом
							$response = $this->saveObject($this->schema.'.RegistryLLOFinDocument', array(
								'FinDocument_id' => $response['FinDocument_id'],
								'RegistryLLO_id' => $data['RegistryLLO_id'],
								'pmUser_id' => $data['pmUser_id']
							));
						}
						break;
					case 'delete':
						$response = $this->delete(array(
							'FinDocument_id' => $record->FinDocument_id,
							'pmUser_id' => $data['pmUser_id']
						));
						break;
				}
			}
		}
	}

	/**
	 * Контроль статуса реестра на оплату, в зависимости от сохраненных платежных поручений
	 */
	function setRegistryLLOAutoStatus($data) {
		$params = [
			'RegistryLLO_id' => !empty($data['RegistryLLO_id']) ? $data['RegistryLLO_id'] : null,
			'FinDocument_id' => !empty($data['FinDocument_id']) ? $data['FinDocument_id'] : null
		];
		//получение суммы счета, идентификатора и статуса реестра

		if(empty($params['RegistryLLO_id'])) {
			$res = $this->getFirstResultFromQuery("
				select
					RegistryLLO_id as \"RegistryLLO_id\"
				from {$this->schema}.v_RegistryLLOFinDocument rlfd
				where FinDocument_id = :FinDocument_id
				limit 1
			", $params);

			$params['RegistryLLO_id'] = $res;
		}

		$query = "
            with mv1 as (
            	select
                    rs.RegistryStatus_Code
                from
                    {$this->schema}.v_RegistryLLO rllo
                    left join v_RegistryStatus rs on rs.RegistryStatus_id = rllo.RegistryStatus_id
                where
                    rllo.RegistryLLO_id = :RegistryLLO_id
                limit 1
            ), mv2 as (
            	select
                    fd.FinDocument_Sum
                from
                    {$this->schema}.v_RegistryLLOFinDocument rlfd
                    left join {$this->schema}.v_FinDocument fd on fd.FinDocument_id = rlfd.FinDocument_id
                    left join v_FinDocumentType fdt on fdt.FinDocumentType_id = fd.FinDocumentType_id
                where
                    rlfd.RegistryLLO_id = :RegistryLLO_id and
                    fdt.FinDocumentType_Code = 1 --счет
                order by
                    fd.FinDocument_id
                limit 1
            )

            select
                :RegistryLLO_id as \"RegistryLLO_id\",
                (select RegistryStatus_Code from mv1) as \"RegistryStatus_Code\",
                (select FinDocument_Sum from mv2) as \"FinDocument_Sum\"
        ";
		$registry_data = $this->getFirstRowFromQuery($query, $params);

		$new_status_code = null;
		if ($registry_data['RegistryLLO_id'] > 0 ) {
			if ($registry_data['FinDocument_Sum'] > 0) { //если счет создан и его сумма больше нуля, то возможен переход к статусам: "К оплате" или "Оплачен"
				if ($registry_data['RegistryStatus_Code'] == 3) { //если статус реестра 3 - "В работе"
					$new_status_code = 2; //2 - "К оплате"
				}
				if ($registry_data['RegistryStatus_Code'] == 2 || $registry_data['RegistryStatus_Code'] == 4) { //если статус реестра 2 - "К оплате" или 4 - "Оплачен"
					//получение суммы платежных поручений
					$query = "
						select
							sum(fd.FinDocument_Sum) as \"FinDocument_Sum\"
						from
							{$this->schema}.v_RegistryLLOFinDocument rlfd
							left join {$this->schema}.v_FinDocument fd on fd.FinDocument_id = rlfd.FinDocument_id
							left join v_FinDocumentType fdt on fdt.FinDocumentType_id = fd.FinDocumentType_id
						where
							rlfd.RegistryLLO_id = :RegistryLLO_id and
							fdt.FinDocumentType_Code = 2 --платежное поручение
		
					";
					$docs_sum = $this->getFirstResultFromQuery($query, array(
						'RegistryLLO_id' => $registry_data['RegistryLLO_id']
					));

					if ($docs_sum >= $registry_data['FinDocument_Sum'] && $registry_data['RegistryStatus_Code'] == 2) { //Cтатус реестра: 2 - "К оплате"
						$new_status_code = 4;
					} else if ($docs_sum < $registry_data['FinDocument_Sum'] && $registry_data['RegistryStatus_Code'] == 4) { //Cтатус реестра: 4 - "Оплачен"
						$new_status_code = 2;
					}
				}
			} else { //если счет удален или его сумма ровна нулю, реестр должен получить статус "В работе"
				if ($registry_data['RegistryStatus_Code'] != 3) { //если статус реестра не соответствует значению 3 - "В работе"
					$new_status_code = 3; //3 - "В работе"
				}
			}
		}



		if ($new_status_code > 0) {
			//смена статуса реестра при необходимости
			$response = $this->saveObject($this->schema.'.RegistryLLO', array(
				'RegistryLLO_id' => $registry_data['RegistryLLO_id'],
				'RegistryStatus_id' => $this->getObjectIdByCode('RegistryStatus', $new_status_code),
				'pmUser_id' => $data['pmUser_id']
			));
			return array('RegistryStatus_isChanged' => true, 'RegistryStatus_Code' => $new_status_code);
		} else {
			return array('RegistryStatus_isChanged' => false, 'RegistryStatus_Code' => $registry_data['RegistryStatus_Code']);
		}
	}

	/**
	 * Получение идентификатора счета по идентификатору реестра
	 */
	function getIdByRegistryLLO($registryllo_id) {
		$query = "
            select
                rlfd.FinDocument_id as \"FinDocument_id\"
            from
                {$this->schema}.v_RegistryLLOFinDocument rlfd
                left join {$this->schema}.v_FinDocument fd on fd.FinDocument_id = rlfd.FinDocument_id
                left join v_FinDocumentType fdt on fdt.FinDocumentType_id = fd.FinDocumentType_id
            where
                rlfd.RegistryLLO_id = :RegistryLLO_id and
                fdt.FinDocumentType_Code = 1 -- счет
            order by
                rlfd.FinDocument_id
            limit 1
        ";
		$id = $this->getFirstResultFromQuery($query, array(
			'RegistryLLO_id' => $registryllo_id
		));
		return !empty($id) && $id > 0 ? $id : null;
	}

	/**
	 * Загрузка списка организаций для комбобокса
	 */
	function loadOrgMidCombo($data) {
		$params = array();
		$filters = array();
		$where = null;

		$filters[] = "(o.Org_id = (select MinzdravOrg_id from mv) or ot.OrgType_Code in (5, 16))"; //5 - Региональный склад ДЛО; 16 - Поставщик.

		if (!empty($data['query'])) {
			$filters[] = "o.Org_Nick ilike :Org_Nick";
			$params['Org_Nick'] = '%'.$data['query'].'%';
		}

		if (count($filters) > 0) {
			$where = "
				where
				    ".join(" and ", $filters)."
			";
		}

		$query = "
            with mv as (
            	select
            		dbo.GetMinzdravDloOrgId() as MinzdravOrg_id
            )

			select
				o.Org_id as \"Org_id\",
				rtrim(o.Org_Nick) as \"Org_Nick\",
				rtrim(o.Org_Name) as \"Org_Name\"
			from
				v_Org o
				left join v_OrgType ot on ot.OrgType_id = o.OrgType_id
			limit 1
			{$where}
		";

		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			$result = $result->result('array');
			return $result;
		} else {
			return false;
		}
	}

	/**
	 *	Получение списка параметров хранимой процедуры
	 */
	function getStoredProcedureParamsList($sp, $schema) {
		$query = "
			SELECT 
                name as \"name\" 
            FROM (
            	SELECT 
            	       unnest(proargnames) as name
            	FROM pg_proc p
            	     LEFT OUTER JOIN pg_description ds ON ds.objoid = p.oid
            	     INNER JOIN pg_namespace n ON p.pronamespace = n.oid
            	WHERE p.proname = :name AND
            	      n.nspname = :schema
            	) t
            WHERE t.name not in ('error_code', 'error_message', 'isreloadcount')

		";

		$queryParams = array(
			'name' => strtolower($sp),
			'schema' => strtolower($schema)
		);

		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			return false;
		}

		$outputData = array();
		$response = $result->result('array');

		foreach ( $response as $row ) {
			$outputData[] = str_replace('@', '', $row['name']);
		}

		return $outputData;
	}

	/**
	 * Сохранение произвольного обьекта (без повреждения предыдущих данных).
	 */
	function saveObject($object_name, $data) {
		$schema = "dbo";

		//при необходимости выделяем схему из имени обьекта
		$name_arr = explode('.', $object_name);
		if (count($name_arr) > 1) {
			$schema = $name_arr[0];
			$object_name = $name_arr[1];
		}

		$key_field = !empty($data['key_field']) ? $data['key_field'] : "{$object_name}_id";

		if (!isset($data[$key_field])) {
			$data[$key_field] = null;
		}

		$action = $data[$key_field] > 0 ? "upd" : "ins";
		$proc_name = "p_{$object_name}_{$action}";
		$params_list = $this->getStoredProcedureParamsList($proc_name, $schema);
		$save_data = array();
		$query_part = "";

		//получаем существующие данные если апдейт
		if ($action == "upd") {
			$query = "
				select
					*
				from
					{$schema}.{$object_name}
				where
					{$key_field} = :id;
			";
			$result = $this->getFirstRowFromQuery($query, array(
				'id' => $data[$key_field]
			));
			if (is_array($result)) {
				foreach($result as $key => $value) {
					if (in_array($key, $params_list)) {
						$save_data[$key] = $value;
					}
				}
			}
		}

		foreach($data as $key => $value) {
			if (in_array($key, $params_list)) {
				$save_data[$key] = $value;
			}
		}

		foreach($save_data as $key => $value) {
			if (in_array($key, $params_list) && $key != $key_field) {
				//перобразуем даты в строки
				if (is_object($save_data[$key]) && get_class($save_data[$key]) == 'DateTime') {
					$save_data[$key] = $save_data[$key]->format('Y-m-d H:i:s');
				}
				$query_part .= "{$key} := :{$key}, ";
			}
		}

		$save_data['pmUser_id'] = isset($data['pmUser_id']) ? $data['pmUser_id'] : null;

		$query = "
			select
				{$key_field} as \"{$key_field}\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$schema}.{$proc_name}(
				{$key_field} := :{$key_field},
				{$query_part}
			)
		";

		if (isset($data['debug_query'])) {
			print getDebugSQL($query, $save_data);
		}
		$result = $this->getFirstRowFromQuery($query, $save_data);
		if ($result && is_array($result)) {
			if($result[$key_field] > 0) {
				$result['success'] = true;
			}
			return $result;
		} else {
			return array('Error_Msg' => 'При сохранении произошла ошибка');
		}
	}

	/**
	 * Удаление произвольного обьекта.
	 */
	function deleteObject($object_name, $data) {
		$schema = "dbo";

		//при необходимости выделяем схему из имени обьекта
		$name_arr = explode('.', $object_name);
		if (count($name_arr) > 1) {
			$schema = $name_arr[0];
			$object_name = $name_arr[1];
		}

		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$schema}.p_{$object_name}_del(
				{$object_name}_id := :{$object_name}_id
			)
		";

		$result = $this->getFirstRowFromQuery($query, $data);
		if ($result && is_array($result)) {
			if(empty($result['Error_Msg'])) {
				$result['success'] = true;
			}
			return $result;
		} else {
			return array('Error_Msg' => 'При удалении произошла ошибка');
		}
	}

	/**
	 * Получение идентификатора типа документа по коду
	 */
	function getObjectIdByCode($object_name, $code) {
		$schema = "dbo";

		//при необходимости выделяем схему из имени обьекта
		$name_arr = explode('.', $object_name);
		if (count($name_arr) > 1) {
			$schema = $name_arr[0];
			$object_name = $name_arr[1];
		}

		$query = "
			select
				{$object_name}_id as \"{$object_name}_id\"
			from
				{$schema}.{$object_name}
			where
				{$object_name}_Code = :code
			limit 1
		";
		$result = $this->getFirstResultFromQuery($query, array(
			'code' => $code
		));

		return $result && $result > 0 ? $result : false;
	}

	/**
	 * Получение следующего номера произвольного обьекта.
	 */
	function getObjectNextNum($object_name, $num_field) {
		$query = "
			select
				coalesce(max(cast({$num_field} as integer)), 0) + 1 as \"num\"
			from
				{$object_name}
			where
				length({$num_field}) <= 6 and
				coalesce((
					Select Case When strpos({$num_field}, '.') > 0 Then 0 Else 1 End
					Where IsNumeric({$num_field} + 'e0') = 1
				), 0) = 1
		";
		$num = $this->getFirstResultFromQuery($query);
		return !empty($num) && $num > 0 ? $num : 0;
	}
}
