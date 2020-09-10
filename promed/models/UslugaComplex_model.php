<?php
/**
 * @property Usluga_model Usluga_model
 * @property CureStandartUslugaComplexLink_model $CureStandartUslugaComplexLink_model
 */
class UslugaComplex_model extends swModel {
	/**
	 *	Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

    /**
     * Определение правил для входящих параметров
     * @param string $scenario
     * @return array
     */
    public function getInputRules($scenario) {
        $rules = array();
        switch ($scenario) {
            case 'loadForSelect':
                $rules = array(
                    array('field' => 'UslugaComplex_id', 'label' => 'Пакет', 'rules' => '', 'type' => 'id'),
                    array('field' => 'Mes_id', 'label' => 'МЭС', 'rules' => '', 'type' => 'id'),
                    // чтобы отфильтровать услуги, которые были оказаны
					array('field' => 'EvnUsluga_rid', 'label' => 'Событие (ТАП)', 'rules' => 'trim', 'type' => 'id'),
					array('field' => 'EvnUsluga_pid', 'label' => 'Событие (посещение)', 'rules' => 'required', 'type' => 'id'),
					array('field' => 'EvnDiagPLStom_id', 'label' => 'Заболевание', 'rules' => '', 'type' => 'id'),
                    // Параметры для выбора тарифа
                    array('field' => 'LpuSection_id', 'label' => 'Отделение', 'rules' => 'required', 'type' => 'id'),
                    array('field' => 'PayType_id', 'label' => 'Вид оплаты', 'rules' => 'required', 'type' => 'id'),
                    array('field' => 'Person_id', 'label' => 'Пациент', 'rules' => 'required', 'type' => 'id'),
                    array('field' => 'UslugaComplexTariff_Date', 'label' => 'Дата события', 'rules' => 'required', 'type' => 'date'),
					array('field' => 'UEDAboveZero', 'label' => 'УЕТ больше 0', 'rules' => '', 'type' => 'int'),
					array('field' => 'doNotIncludeEvnUslugaDid', 'label' => 'С учетом заведенных услуг в рамках случая', 'rules' => '', 'type' => 'int'),
                );
                break;
        }
        return $rules;
    }
	
	/**
	 * Добавление связи между комплексными услугами
	 */
	function linkUslugaComplex($data) {
		$uslugaLinkedData = $this->getUslugaComplexData($data['UslugaComplex_id']); // Данные связываемой услуги
		$uslugaParentData = $this->getUslugaComplexData($data['UslugaComplex_pid']); // Данные основной услуги

		$uslugaComplexId = 0;
		$uslugaComplexPid = 0;

		if ( empty($uslugaLinkedData['UslugaCategory_SysNick']) ) {
			return array(array('Error_Msg' => 'Ошибка при определнии категории связываемой услуги'));
		}
		else if ( empty($uslugaParentData['UslugaCategory_SysNick']) ) {
			return array(array('Error_Msg' => 'Ошибка при определнии категории услуги'));
		}
		else if ( in_array($uslugaLinkedData['UslugaCategory_SysNick'], array('promed', 'lpu')) && in_array($uslugaParentData['UslugaCategory_SysNick'], array('promed', 'lpu')) ) {
			return array(array('Error_Msg' => 'Неверные категории связанных услуг'));
		}

		if ( $uslugaLinkedData['UslugaCategory_SysNick'] == $uslugaParentData['UslugaCategory_SysNick'] ) {
			return array(array('Error_Msg' => 'Недопустимо связывание услуг одной категории'));
		}
			
		// Основная - эталонная
		if ( in_array($uslugaParentData['UslugaCategory_SysNick'], array('gost2004', 'gost2011', 'syslabprofile')) ) {
			// Обновляем поле у связываемой услуги, которое соответствует категории основной услуги
			$field = $this->getUslugaComplexFieldNameByCategory($uslugaParentData['UslugaCategory_SysNick']);
			$uslugaComplexId = $data['UslugaComplex_id'];
			$uslugaComplexPid = $data['UslugaComplex_pid'];
		}
		// Основная - услуга Промед, тфомс или услуга ЛПУ; связываемая - гост и пр.
		else {
			// Обновляем поле у основной услуги, которое соответствует категории связываемой услуги
			$field = $this->getUslugaComplexFieldNameByCategory($uslugaLinkedData['UslugaCategory_SysNick']);
			$uslugaComplexId = $data['UslugaComplex_pid'];
			$uslugaComplexPid = $data['UslugaComplex_id'];
		}

		if ( empty($field) ) {
			return array(array('Error_Msg' => '(Строка ' . __LINE__ . ') Ошибка при определении наименования поля'));
		}

		$query = "
			update UslugaComplex with (rowlock)
			set " . $field . " = :UslugaComplex_pid
			where UslugaComplex_id = :UslugaComplex_id
		";

		$queryParams['UslugaComplex_id'] = $uslugaComplexId;
		$queryParams['UslugaComplex_pid'] = $uslugaComplexPid;

		$result = $this->db->query($query, $queryParams);

		if ( !$result ) {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (связывание комплексных услуг)'));
		}

		return array(array('Error_Msg' => ''));
	}


	/**
	 * Удаление связи между комплексными услугами
	 */
	function deleteLinkedUslugaComplex($data) {
		$uslugaLinkedData = $this->getUslugaComplexData($data['UslugaComplex_id']); // Данные связываемой услуги
		$uslugaParentData = $this->getUslugaComplexData($data['UslugaComplex_pid']); // Данные основной услуги
		$uslugaComplexId = 0;

		if ( empty($uslugaLinkedData['UslugaCategory_SysNick']) ) {
			return array(array('Error_Msg' => 'Ошибка при определнии категории связываемой услуги'));
		}
		else if ( empty($uslugaParentData['UslugaCategory_SysNick']) ) {
			return array(array('Error_Msg' => 'Ошибка при определнии категории услуги'));
		}
		else if ( in_array($uslugaLinkedData['UslugaCategory_SysNick'], array('promed', 'lpu')) && in_array($uslugaParentData['UslugaCategory_SysNick'], array('promed', 'lpu')) ) {
			return array(array('Error_Msg' => 'Неверные категории связанных услуг'));
		}

		// Основная - эталонная
		if ( in_array($uslugaParentData['UslugaCategory_SysNick'], array('gost2004', 'gost2011', 'syslabprofile')) ) {
			// Чистим поле у удаляемой услуги, которое соответствует категории основной услуги
			$field = $this->getUslugaComplexFieldNameByCategory($uslugaParentData['UslugaCategory_SysNick']);
			$uslugaComplexId = $data['UslugaComplex_id'];
		}
		// Основная - услуга Промед или услуга ЛПУ; удаляемая - эталонная
		else {
			// Чистим поле у основной услуги, которое соответствует категории удаляемой услуги
			$field = $this->getUslugaComplexFieldNameByCategory($uslugaLinkedData['UslugaCategory_SysNick']);
			if (empty($field)) {
				return array(array('Error_Msg' => 'Нельзя связать услуги данных категорий'));
			}
			$uslugaComplexId = $data['UslugaComplex_pid'];
		}
			
		$query = "
			update UslugaComplex with (rowlock)
			set " . $field . " = null
			where UslugaComplex_id = :UslugaComplex_id
		";

		$queryParams['UslugaComplex_id'] = $uslugaComplexId;

		$result = $this->db->query($query, $queryParams);

		if ( !$result ) {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление связанной услуги)'));
		}

		return array(array('Error_Msg' => ''));
	}


	/**
	 * Загрузка комбо комплексных услуг
	 */
	function loadUslugaComplexCombo($data) {
		$filters = array();
		$joinList = array();
		$lpuFilters = array();
		$queryParams = array();

		$filters[] = "ISNULL(uc.UslugaComplexLevel_id,2) in (2,3,7,8,10)";
		
		// Загружаем конкретную запись
		if ( !empty($data['UslugaComplex_id']) ) {
			$filters[] = "uc.UslugaComplex_id = :UslugaComplex_id";
			$queryParams['UslugaComplex_id'] = $data['UslugaComplex_id'];
		}
		else {
			// Место выполнения услуги - отделение
			if ( !empty($data['LpuSection_id']) ) {
				$joinList[] = "
					outer apply (
						select
							 ls.Lpu_id
							,lu.LpuBuilding_id
							,lu.LpuUnit_id
							,ls.LpuSection_id
						from v_LpuSection ls with (nolock)
							inner join LpuUnit lu with (nolock) on lu.LpuUnit_id = ls.LpuUnit_id
						where ls.LpuSection_id = :LpuSection_id
					) place
				";
				$queryParams['LpuSection_id'] = $data['LpuSection_id'];
			}
			// Место выполнения услуги - служба
			else if ( !empty($data['MedService_id']) ) {
				$joinList[] = "
					outer apply (
						select
							 ms.Lpu_id
							,ms.LpuBuilding_id
							,ms.LpuUnit_id
							,ms.LpuSection_id
							,ms.MedService_begDT
							,ms.MedService_endDT
						from v_MedService ms with (nolock)
						where ms.MedService_id = :MedService_id
					) place
				";
				$queryParams['MedService_id'] = $data['MedService_id'];
				// Закомментировал, ибо https://redmine.swan.perm.ru/issues/26919
				// $filters[] = "cast(uc.UslugaComplex_begDT as date) <= cast(place.MedService_begDT as date)";
				// Как вариант, можно было бы сделать так:
				// $filters[] = "uc.UslugaComplex_begDT <= dbo.tzGetDate()";
				$filters[] = "(uc.UslugaComplex_endDT is null or place.MedService_endDT is null or cast(uc.UslugaComplex_endDT as date) > cast(place.MedService_endDT as date))";
			}
			// Иначе тянем все услуги ЛПУ
			else {
				$joinList[] = "left join v_Lpu place with (nolock) on place.Lpu_id = :Lpu_id";
				$queryParams['Lpu_id'] = (!empty($data['Lpu_uid']) ? $data['Lpu_uid'] : $data['Lpu_id']);
			}

			// Строка поиска
			if ( !empty($data['query']) ) {
				$queryParams['query'] = '%'. $data['query'] . '%';
				$filters[] = "(cast(uc.UslugaComplex_Code as varchar) + ' ' + rtrim(isnull(uc.UslugaComplex_Name, ''))) like :query";
			}

			// Категория услуги
			if ( !empty($data['UslugaCategory_id']) ) {
				$filters[] = "ucat.UslugaCategory_id = :UslugaCategory_id";
				$queryParams['UslugaCategory_id'] = $data['UslugaCategory_id'];
			}
			else if ( !empty($data['uslugaCategoryList']) ) {
				$uslugaCategoryList = json_decode($data['uslugaCategoryList'], true);

				if ( is_array($uslugaCategoryList) && count($uslugaCategoryList) > 0 ) {
					$filters[] = "ucat.UslugaCategory_SysNick in ('" . implode("', '", $uslugaCategoryList) . "')";
				}
				else {
					$filters[] = "ucat.UslugaCategory_SysNick in ('tfoms', 'promed', 'gost2011', 'lpu')";
				}
			}

			$filters[] = "(ucat.UslugaCategory_SysNick not in ('lpu', 'lpulabprofile') or uc.Lpu_id = place.Lpu_id)";

			// Дата актуальности услуги
			if ( !empty($data['UslugaComplex_Date']) ) {
				$filters[] = "cast(uc.UslugaComplex_begDT as date) <= cast(:UslugaComplex_Date as date)";
				$filters[] = "(uc.UslugaComplex_endDT is null or cast(uc.UslugaComplex_endDT as date) > cast(:UslugaComplex_Date as date))";
				$queryParams['UslugaComplex_Date'] = $data['UslugaComplex_Date'];
			}

			if ( !empty($data['UslugaComplex_begDT']) ) {
				$filters[] = "uc.UslugaComplex_begDT >= cast(:UslugaComplex_begDT as datetime)";
				$filters[] = "(uc.UslugaComplex_endDT is null or uc.UslugaComplex_endDT >= cast(:UslugaComplex_begDT as datetime))";
				$queryParams['UslugaComplex_begDT'] = $data['UslugaComplex_begDT'];
			}

			if ( !empty($data['UslugaComplex_endDT']) ) {
				$filters[] = "uc.UslugaComplex_begDT <= cast(:UslugaComplex_endDT as datetime)";
				$queryParams['UslugaComplex_endDT'] = $data['UslugaComplex_endDT'];
			}

			// Идентификатор родительской услуги
			if ( !empty($data['UslugaComplex_pid']) ) {
				$filters[] = "uc.UslugaComplex_pid = :UslugaComplex_pid";
				$queryParams['UslugaComplex_pid'] = $data['UslugaComplex_pid'];
			}
			
			// Идентификатор родительской услуги для фильтра по ЛПУ
			if ( !empty($data['UslugaComplex_ForLpuFilter_pid']) ) {
				$filters[] = "(uc.Lpu_id = (select top 1 Lpu_id from v_UslugaComplex (nolock) where UslugaComplex_id = :UslugaComplex_ForLpuFilter_pid) or ucat.UslugaCategory_SysNick not in ('lpu','lpulabprofile'))";
				$queryParams['UslugaComplex_ForLpuFilter_pid'] = $data['UslugaComplex_ForLpuFilter_pid'];
			}
		}

		$query = "
			select top 500
				 uc.UslugaComplex_id
				,ucat.UslugaCategory_id
				,ucat.UslugaCategory_Name
				,uc.UslugaComplex_pid
				,convert(varchar(10), uc.UslugaComplex_begDT, 104) as UslugaComplex_begDT
				,convert(varchar(10), uc.UslugaComplex_endDT, 104) as UslugaComplex_endDT
				,uc.UslugaComplex_Code
				,rtrim(isnull(uc.UslugaComplex_Name, '')) as UslugaComplex_Name
				,uc.UslugaComplex_UET
				,UCCCount.count as CompositionCount
				,l.Lpu_Nick
			from
				v_UslugaComplex uc with (nolock)
				left join v_Lpu l with (nolock) on l.Lpu_id = uc.Lpu_id
				left join v_UslugaCategory ucat with (nolock) on ucat.UslugaCategory_id = uc.UslugaCategory_id
				outer apply(
					select count(UslugaComplexComposition_id) as count from v_UslugaComplexComposition ucc with (nolock) where ucc.UslugaComplex_pid = uc.UslugaComplex_id
				) UCCCount
				" . implode(' ', $joinList) . "
			where
				" . implode(' and ', $filters) . "
			order by
				uc.UslugaComplex_Code
		";
		//echo getDebugSql($query, $queryParams); exit();
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Загрузка комбо комплексных услуг ГОСТ
	 */
	function loadUslugaComplexGost($data) {
		$filters = array();
		$joinList = array();
		$lpuFilters = array();
		$queryParams = array();

		//$filters[] = "ISNULL(uc.UslugaComplexLevel_id,2) in (2,3,7,8,10)";
		$filters[] = "uc.UslugaCategory_id = 4";

		$joinList[] = "left join v_Lpu place with (nolock) on place.Lpu_id = :Lpu_id";
		$queryParams['Lpu_id'] = (!empty($data['Lpu_uid']) ? $data['Lpu_uid'] : $data['Lpu_id']);
			//}

			// Строка поиска
		if ( !empty($data['query']) ) {
			$queryParams['query'] = '%'. $data['query'] . '%';
			$filters[] = "(cast(uc.UslugaComplex_Code as varchar) + ' ' + rtrim(isnull(uc.UslugaComplex_Name, ''))) like :query";
		}

		// Загружаем конкретную запись
		if ( !empty($data['UslugaComplex_id']) ) {
			$filters[] = "uc.UslugaComplex_id = :UslugaComplex_id";
			$queryParams['UslugaComplex_id'] = $data['UslugaComplex_id'];
		}

		$query = "
			select top 500
				 uc.UslugaComplex_id
				,ucat.UslugaCategory_id
				,ucat.UslugaCategory_Name
				,uc.UslugaComplex_pid
				,convert(varchar(10), uc.UslugaComplex_begDT, 104) as UslugaComplex_begDT
				,convert(varchar(10), uc.UslugaComplex_endDT, 104) as UslugaComplex_endDT
				,uc.UslugaComplex_Code
				,rtrim(isnull(uc.UslugaComplex_Name, '')) as UslugaComplex_Name
				,uc.UslugaComplex_UET
				,UCCCount.count as CompositionCount
				,l.Lpu_Nick
			from
				v_UslugaComplex uc with (nolock)
				left join v_Lpu l with (nolock) on l.Lpu_id = uc.Lpu_id
				left join v_UslugaCategory ucat with (nolock) on ucat.UslugaCategory_id = uc.UslugaCategory_id
				outer apply(
					select count(UslugaComplexComposition_id) as count from v_UslugaComplexComposition ucc with (nolock) where ucc.UslugaComplex_pid = uc.UslugaComplex_id
				) UCCCount
				" . implode(' ', $joinList) . "
			where
				" . implode(' and ', $filters) . "
			order by
				uc.UslugaComplex_Code
		";

		 //echo getDebugSql($query, $queryParams); exit();
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Проверка наличия ссылок на услугу в других таблицах, кроме UslugaComplexAttribute, UslugaComplexPlace, UslugaComplexTariff
	 */
	function checkUslugaComplexIsUsed($uslugaComplexId, $tablesToPass = array()) {
		$queryParams = array(
			'UslugaComplex_id' => $uslugaComplexId
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
			WHERE  OBJECT_NAME (f.referenced_object_id) = 'UslugaComplex'
				and OBJECT_NAME (f.parent_object_id) not in ('UslugaComplexComposition', 'UslugaComplexAttribute', 'UslugaComplexPlace', 'UslugaComplexTariff', 'UslugaComplexInfo')
				" . (is_array($tablesToPass) && count($tablesToPass) > 0 ? "and OBJECT_NAME (f.parent_object_id) not in ('" . implode("', '", $tablesToPass) . "')" : "") . "
			order by
				TableName
		";

		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			$response['Error_Msg'] = 'Ошибка при выполнении запроса к базе данных (получение списка связанных таблиц)';
			return $response;
		}

		$queryResponse = $result->result('array');

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
					$queryList[] = "(select top 1 '" . $table . "' as id from " . $schema . "." . $table . " with (nolock) where :UslugaComplex_id in (" . implode(', ', $fieldList) . ")" . ($table == 'UslugaComplex' ? " and UslugaComplex_id != :UslugaComplex_id " : "") . ")";
				}

				$fieldList = array();
				$schema = $array['SchemaName'];
				$table = $array['TableName'];
			}

			$fieldList[] = $array['ColumnName'];
		}

		if ( !empty($table) && count($fieldList) > 0 ) {
			$queryList[] = "(select top 1 '" . $table . "' as id from " . $table . " with (nolock) where :UslugaComplex_id in (" . implode(', ', $fieldList) . ")" . ($table == 'UslugaComplex' ? " and UslugaComplex_id != :UslugaComplex_id " : "") . ")";
		}

		$query = implode(' union ', $queryList);

		// echo getDebugSQL($query, $queryParams); exit();

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
	 * Проверка наличия ссылок на услугу в службе в других таблицах
	 */
	function checkUslugaComplexMedServiceIsUsed($UslugaComplexMedService_id, $tablesToPass = array()) {
		$queryParams = array(
			'UslugaComplexMedService_id' => $UslugaComplexMedService_id
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
			WHERE  OBJECT_NAME (f.referenced_object_id) = 'UslugaComplexMedService'
				" . (is_array($tablesToPass) && count($tablesToPass) > 0 ? "and OBJECT_NAME (f.parent_object_id) not in ('" . implode("', '", $tablesToPass) . "')" : "") . "
			order by
				TableName
		";
		//echo getDebugSql($query, $queryParams);
		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			$response['Error_Msg'] = 'Ошибка при выполнении запроса к базе данных (получение списка связанных таблиц)';
			return $response;
		}

		$queryResponse = $result->result('array');

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
					$queryList[] = "(select top 1 '" . $table . "' as id from " . $schema . "." . $table . " (nolock) where :UslugaComplexMedService_id in (" . implode(', ', $fieldList) . ")" . ($table == 'UslugaComplexMedService' ? " and UslugaComplexMedService_id != :UslugaComplexMedService_id " : "") . ")";
				}

				$fieldList = array();
				$schema = $array['SchemaName'];
				$table = $array['TableName'];
			}

			$fieldList[] = $array['ColumnName'];
		}

		if ( !empty($table) && count($fieldList) > 0 ) {
			$queryList[] = "(select top 1 '" . $table . "' as id from " . $table . " (nolock) where :UslugaComplexMedService_id in (" . implode(', ', $fieldList) . ")" . ($table == 'UslugaComplexMedService' ? " and UslugaComplexMedService_id != :UslugaComplexMedService_id " : "") . ")";
		}

		$query = implode(' union all ', $queryList);

		// echo getDebugSQL($query, $queryParams); exit();

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
			$response['Error_Msg'] = 'Операция невозможна, т.к. на услугу есть ссылки';
		}

		return $response;
	}


	/**
	 *	Удаление услуги
	 */
	function deleteUslugaComplex($data) {
		$queryParams = array(
			'UslugaComplex_id' => $data['UslugaComplex_id']
		);

		// Сначала проверка на возможность удаления эталонной услуги
		$query = "
			select top 1 ucat.UslugaCategory_SysNick
			from v_UslugaComplex uc with (nolock)
				inner join v_UslugaCategory ucat with (nolock) on ucat.UslugaCategory_id = uc.UslugaCategory_id
			where uc.UslugaComplex_id = :UslugaComplex_id
		";
		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (получение категории услуги)'));
		}

		$response = $result->result('array');

		if ( !is_array($response) || count($response) == 0 || empty($response[0]['UslugaCategory_SysNick']) ) {
			return array(array('Error_Msg' => 'Ошибка при получении категории услуги'));
		}
		else if 
			((in_array($response[0]['UslugaCategory_SysNick'], array('gost2004', 'tfoms', 'gost2011', 'lpulabprofile'))) || 
			 (!isSuperadmin() && in_array($response[0]['UslugaCategory_SysNick'], array('syslabprofile')))
			) {
			return array(array('Error_Msg' => 'Удаление эталонных услуг запрещено'));
		}

		// Проверка наличия ссылок на услугу в других таблицах
		$checkResult = $this->checkUslugaComplexIsUsed($data['UslugaComplex_id']);

		if ( !empty($checkResult['Error_Msg']) ) {
			return array(array('Error_Msg' => $checkResult['Error_Msg']));
		}

		// Проверка вхождения услуги в состав комплексной услуги
		$query = "
			select count(UslugaComplexComposition_id) as cnt
			from v_UslugaComplexComposition with (nolock)
			where UslugaComplex_id = :UslugaComplex_id
		";
		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (проверка вхождения услуги в состав комплексной услуги)'));
		}

		$response = $result->result('array');

		if ( !is_array($response) || count($response) == 0 ) {
			return array(array('Error_Msg' => 'Ошибка при проверке вхождения услуги в состав комплексной услуги'));
		}
		else if ( !empty($response[0]['cnt']) ) {
			return array(array('Error_Msg' => 'Удаление услуги невозможно, т.к. она входит в состав комплексной услуги'));
		}

		// Удаление услуги
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec p_UslugaComplex_del
				@UslugaComplex_id = :UslugaComplex_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 *	Удаление атрибута услуги
	 */
	function deleteUslugaComplexAttribute($data) {
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec p_UslugaComplexAttribute_del
				@UslugaComplexAttribute_id = :UslugaComplexAttribute_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'UslugaComplexAttribute_id' => $data['UslugaComplexAttribute_id']
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *	Удаление профиля услуги
	 */
	function deleteUslugaComplexProfile($data) {
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec p_UslugaComplexProfile_del
				@UslugaComplexProfile_id = :UslugaComplexProfile_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'UslugaComplexProfile_id' => $data['UslugaComplexProfile_id']
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *	Удаление из состава
	 */
	function deleteUslugaComplexComposition($data) {
		$queryParams = array(
			'UslugaComplexComposition_id' => $data['UslugaComplexComposition_id']
		);

		// Получаем сведения об услугах
		$query = "
			select top 1
				 uc.UslugaComplex_id
				,uc.UslugaComplexLevel_id
				,ucnt.cnt as UslugaComplexComposition_Count
				,ucat.UslugaCategory_SysNick
			from v_UslugaComplexComposition ucc with (nolock)
				inner join v_UslugaComplex uc with (nolock) on uc.UslugaComplex_id = ucc.UslugaComplex_pid
				inner join v_UslugaCategory ucat with (nolock) on ucat.UslugaCategory_id = uc.UslugaCategory_id
				outer apply (
					select count(UslugaComplexComposition_id) as cnt
					from v_UslugaComplexComposition with (nolock)
					where UslugaComplex_pid = uc.UslugaComplex_id
						and UslugaComplexComposition_id = ucc.UslugaComplexComposition_id
				) ucnt
			where
				ucc.UslugaComplexComposition_id = :UslugaComplexComposition_id
		";

		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			return false;
		}

		$queryResponse = $result->result('array');

		if ( !is_array($queryResponse) || count($queryResponse) == 0 ) {
			return false;
		}

		// Проверка наличия ссылок на родительскую услугу в других таблицах
		$checkResult = $this->checkUslugaComplexIsUsed($queryResponse[0]['UslugaComplex_id'], array('UslugaComplexComposition'));

		if ( $checkResult['cnt'] > 0 ) {
			return array(array('Error_Msg' => 'Удаление услуги из состава невозможно, т.к. родительская услуга уже была использована ранее'));
		}
		else if ( !empty($checkResult['Error_Msg']) ) {
			return array(array('Error_Msg' => $checkResult['Error_Msg']));
		}

		// Проверяем необходимость пометить родительскую услугу как простую
		if ( !in_array($queryResponse[0]['UslugaCategory_SysNick'], array('gost2004', 'gost2011')) && $queryResponse[0]['UslugaComplexLevel_id'] == 2 && $queryResponse[0]['UslugaComplexComposition_Count'] == 0 ) {
			$queryResponse = $this->setUslugaComplexLevel(array(
				 'pmUser_id' => $data['pmUser_id']
				,'UslugaComplex_id' => $queryResponse[0]['UslugaComplex_id']
				,'UslugaComplexLevel_id' => 3
			));

			if ( !is_array($queryResponse) || count($queryResponse) == 0 ) {
				return array(array('Error_Msg' => 'Ошибка при обновлении уровня услуги'));
			}
			else if ( !empty($queryResponse[0]['Error_Msg']) ) {
				return $queryResponse;
			}
		}

		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec p_UslugaComplexComposition_del
				@UslugaComplexComposition_id = :UslugaComplexComposition_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *	Проверка возможности редактирования/удаление места оказания услуги
	 */
	function checkUslugaComplexPlaceCanSave($data) {
		$query = "
			select top 1 
				UslugaComplexPlace_id 
			from v_UslugaComplexPlace ucp with (nolock)
				inner join v_UslugaComplex uc with (nolock) on uc.UslugaComplex_id = ucp.UslugaComplex_id
			where UslugaComplexPlace_id = :UslugaComplexPlace_id and (uc.Lpu_id = :Lpu_id or ucp.Lpu_id = :Lpu_id)
		";
		
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) > 0) {
				return true;
			}
		}

		return false;
	}
	
	/**
	 *	Проверка есть ли место оказания услуги
	 */
	function checkUslugaComplexPlaceExist($data) {
		$query = "
			select top 1 
				UslugaComplexPlace_id 
			from v_UslugaComplexPlace ucp with (nolock)
			where 
			ucp.UslugaComplex_id = :UslugaComplex_id
			and (ucp.UslugaComplexPlace_id <> :UslugaComplexPlace_id OR :UslugaComplexPlace_id IS NULL)
			and ISNULL(ucp.Lpu_id,0) = ISNULL(:Lpu_id, 0)
			and ISNULL(ucp.LpuBuilding_id,0) = ISNULL(:LpuBuilding_id, 0)
			and ISNULL(ucp.LpuUnit_id,0) = ISNULL(:LpuUnit_id, 0)
			and ISNULL(ucp.LpuSection_id,0) = ISNULL(:LpuSection_id, 0)
			and ucp.UslugaComplexPlace_begDT <= :UslugaComplexPlace_begDate
			and (ucp.UslugaComplexPlace_endDT >= :UslugaComplexPlace_begDate or ucp.UslugaComplexPlace_endDT IS NULL)
		";
		// echo getDebugSql($query, $data); die();
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) > 0) {
				return true;
			}
		}

		return false;
	}
	
	/**
	 *	Если админом ЦОД добавлен тариф с LPu_id null, то его нельзя перекрыть, пока он не закрыт
	 */
	function checkUslugaComplexTariffHasOMSBySuperAdmin($data) {
		$query = "
			select top 1 
				UslugaComplexTariff_id 
			from
				v_UslugaComplexTariff with (nolock)
			where
				(UslugaComplexTariff_id <> :UslugaComplexTariff_id OR :UslugaComplexTariff_id IS NULL)
				and UslugaComplex_id = :UslugaComplex_id
				and (
					(UslugaComplexTariff_begDate <= :UslugaComplexTariff_begDate and (UslugaComplexTariff_endDate >= :UslugaComplexTariff_begDate or UslugaComplexTariff_endDate IS NULL))
					or 
					(:UslugaComplexTariff_begDate <= UslugaComplexTariff_begDate and (:UslugaComplexTariff_endDate >= UslugaComplexTariff_begDate or :UslugaComplexTariff_endDate IS NULL))
				)
				and Lpu_id IS NULL
				and PayType_id = :PayType_id
		";

		$queryParams = array(
			'UslugaComplexTariff_id' => (!empty($data['UslugaComplexTariff_id']) ? $data['UslugaComplexTariff_id'] : NULL),
			'UslugaComplex_id' => $data['UslugaComplex_id'],
			'UslugaComplexTariff_begDate' => $data['UslugaComplexTariff_begDate'],
			'UslugaComplexTariff_endDate' => $data['UslugaComplexTariff_endDate'],
			'PayType_id' => $data['PayType_id']
		);

		// echo getDebugSQL($query, $queryParams); exit();

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) > 0) {
				return true;
			}
		}

		return false;
	}
	
	/**
	 *	Проверка на существование такого же тарифа как и сохраняемый
	 */
	function checkUslugaComplexTariffHasDuplicate($data) {
		$query = "
			select top 1 
				UslugaComplexTariff_id 
			from
				v_UslugaComplexTariff with (nolock)
			where
				(UslugaComplexTariff_id <> :UslugaComplexTariff_id OR :UslugaComplexTariff_id IS NULL)
				and ISNULL(UslugaComplexTariff_Code,'') = ISNULL(:UslugaComplexTariff_Code,'')
				and ISNULL(UslugaComplexTariff_Name,'') = ISNULL(:UslugaComplexTariff_Name,'')
				and UslugaComplex_id = :UslugaComplex_id
				and (
					(UslugaComplexTariff_begDate <= :UslugaComplexTariff_begDate and (UslugaComplexTariff_endDate >= :UslugaComplexTariff_begDate or UslugaComplexTariff_endDate IS NULL))
					or 
					(:UslugaComplexTariff_begDate <= UslugaComplexTariff_begDate and (:UslugaComplexTariff_endDate >= UslugaComplexTariff_begDate or :UslugaComplexTariff_endDate IS NULL))
				)
				and ISNULL(Lpu_id,0) = ISNULL(:Lpu_id,0)
				and ISNULL(LpuBuilding_id,0) = ISNULL(:LpuBuilding_id,0)
				and ISNULL(LpuUnit_id,0) = ISNULL(:LpuUnit_id,0)
				and ISNULL(LpuSection_id,0) = ISNULL(:LpuSection_id,0)
				and ISNULL(MedService_id,0) = ISNULL(:MedService_id,0)
				and UslugaComplexTariffType_id = :UslugaComplexTariffType_id
				and PayType_id = :PayType_id
				and ISNULL(LpuLevel_id,0) = ISNULL(:LpuLevel_id,0)
				and ISNULL(LpuSectionProfile_id,0) = ISNULL(:LpuSectionProfile_id,0)
				and ISNULL(LpuUnitType_id,0) = ISNULL(:LpuUnitType_id,0)
				and ISNULL(MesAgeGroup_id,0) = ISNULL(:MesAgeGroup_id,0)
				and ISNULL(Sex_id,0) = ISNULL(:Sex_id,0)
		";

		$queryParams = array(
			'UslugaComplexTariff_id' => (!empty($data['UslugaComplexTariff_id']) ? $data['UslugaComplexTariff_id'] : NULL),
			'UslugaComplex_id' => $data['UslugaComplex_id'],
			'UslugaComplexTariff_begDate' => $data['UslugaComplexTariff_begDate'],
			'UslugaComplexTariff_endDate' => $data['UslugaComplexTariff_endDate'],
			'UslugaComplexTariff_Code' => $data['UslugaComplexTariff_Code'],
			'UslugaComplexTariff_Name' => $data['UslugaComplexTariff_Name'],
			'Lpu_id' => (!empty($data['Lpu_id']) ? $data['Lpu_id'] : NULL),
			'LpuBuilding_id' => (!empty($data['LpuBuilding_id']) ? $data['LpuBuilding_id'] : NULL),
			'LpuUnit_id' => (!empty($data['LpuUnit_id']) ? $data['LpuUnit_id'] : NULL),
			'LpuSection_id' => (!empty($data['LpuSection_id']) ? $data['LpuSection_id'] : NULL),
			'MedService_id' => (!empty($data['MedService_id']) ? $data['MedService_id'] : NULL),
			'LpuLevel_id' => (!empty($data['LpuLevel_id']) ? $data['LpuLevel_id'] : NULL),
			'LpuSectionProfile_id' => (!empty($data['LpuSectionProfile_id']) ? $data['LpuSectionProfile_id'] : NULL),
			'LpuUnitType_id' => (!empty($data['LpuUnitType_id']) ? $data['LpuUnitType_id'] : NULL),
			'MesAgeGroup_id' => (!empty($data['MesAgeGroup_id']) ? $data['MesAgeGroup_id'] : NULL),
			'Sex_id' => (!empty($data['Sex_id']) ? $data['Sex_id'] : NULL),
			'UslugaComplexTariffType_id' => $data['UslugaComplexTariffType_id'],
			'PayType_id' => $data['PayType_id']
		);

		// echo getDebugSQL($query, $queryParams); exit();

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) > 0) {
				return true;
			}
		}

		return false;
	}
	
	/**
	 *	Проверка использования тарифа в случаях оказания услуг
	 */
	function checkUslugaComplexTariffUsedInEvnUsluga($data) {
		$filter = "";
		
		/*if (!empty($data['UslugaComplexTariff_endDate'])) {
			$filter .= " and eu.EvnUsluga_setDate > :UslugaComplexTariff_endDate";
		}*/

		$query = "
			select top 1 
				UslugaComplexTariff_id
			from
				v_EvnUsluga eu with (nolock)
				inner join Evn Evn with (nolock) on eu.EvnUsluga_id = Evn.Evn_id
			where
				eu.UslugaComplexTariff_id = :UslugaComplexTariff_id
				and (Evn.Evn_deleted = 1 or Evn.Evn_deleted is null)

			union all

			select top 1
				UslugaComplexTariff_id
			from
				CmpCallCardUsluga with (nolock)
			where
				UslugaComplexTariff_id = :UslugaComplexTariff_id

		";
		
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) > 0) {
				return true;
			}
		}

		return false;
	}
	
	/**
	 *	Проверка возможности редактирования/удаление тарифа услуги
	 */
	function checkUslugaComplexTariffCanSave($data) {
		$query = "
			select top 1 
				UslugaComplexTariff_id 
			from v_UslugaComplexTariff uct with (nolock)
				inner join v_UslugaComplex uc with (nolock) on uc.UslugaComplex_id = uct.UslugaComplex_id
			where UslugaComplexTariff_id = :UslugaComplexTariff_id and (uc.Lpu_id = :Lpu_id or uct.Lpu_id = :Lpu_id)
		";
		
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) > 0) {
				return true;
			}
		}

		return false;
	}

	/**
	 *	Удаление места оказания услуги
	 */
	function deleteUslugaComplexPlace($data) {
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec p_UslugaComplexPlace_del
				@UslugaComplexPlace_id = :UslugaComplexPlace_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'UslugaComplexPlace_id' => $data['UslugaComplexPlace_id']
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 *	Удаление тарифа
	 */
	function deleteUslugaComplexTariff($data) {
		$queryParams = array(
			'UslugaComplexTariff_id' => $data['UslugaComplexTariff_id']
		);

		//Зануляем ссылки на тариф в удалённых услугах
		$query = "
			update
				EvnUsluga with (rowlock)
			set
				UslugaComplexTariff_id = null
			where
				EvnUsluga_id in (
					select
						eu.EvnUsluga_id
					from
						EvnUsluga eu
						inner join Evn e with (nolock) on e.Evn_id = eu.EvnUsluga_id
					where
						eu.UslugaComplexTariff_id = :UslugaComplexTariff_id and e.Evn_deleted = 2
				)
		";

		$result = $this->db->query($query, $queryParams);

		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec p_UslugaComplexTariff_del
				@UslugaComplexTariff_id = :UslugaComplexTariff_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (!empty($resp[0]['Error_Msg'])) {
				return array('Error_Msg' => 'Тариф услуги используется, удаление не возможно');
			}

			return $resp;
		}
		else {
			return false;
		}
	}

	/**
	 * Получение сисника по id категории услуги
	 */
	function getUslugaCategorySysNickById($UslugaCategory_id) {
		$query = "
			select top 1 
				UslugaCategory_SysNick
			from v_UslugaCategory uc with (nolock)
			where UslugaCategory_id = :UslugaCategory_id
		";
		
		$result = $this->db->query($query, array('UslugaCategory_id' => $UslugaCategory_id));

		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) > 0) {
				return $resp[0]['UslugaCategory_SysNick'];
			}
		}

		return '';
	}
	
	/**
	 * Получение названия поля в таблице по сиснику категории услуги
	 */
	function getUslugaComplexFieldNameByCategory($UslugaCategory_SysNick) {
		$result = '';

		switch ( $UslugaCategory_SysNick ) {
			case 'gost2004':
				$result = 'UslugaComplex_2004id';
			break;

			case 'tfoms':
				$result = 'UslugaComplex_TFOMSid';
			break;

			case 'classmedus':
			case 'gost2011':
				$result = 'UslugaComplex_2011id';
			break;

			case 'syslabprofile':
				$result = 'UslugaComplex_slprofid';
			break;

			case 'lpulabprofile':
				$result = 'UslugaComplex_llprofid';
			break;
		}
		
		return $result;
	}
	
	/**
	*  Сохраняет услугу на службу
	*/
	function saveUslugaComplexMedService($data) {
		$this->load->model('UslugaComplexMedService_model');
		$data['scenario'] = self::SCENARIO_DO_SAVE;
		$savedata = $this->UslugaComplexMedService_model->doSave($data);

		// только если добавляем, получаем состав и его тоже запихиваем в UslugaComplexMedService
		if (empty($data['UslugaComplexMedService_id']) && !empty($savedata['UslugaComplexMedService_id'])) {
			$query = "
				select
					ucc.UslugaComplex_id
				from
					v_UslugaComplexComposition ucc with (nolock)
				where
					ucc.UslugaComplex_pid = :UslugaComplex_id
			";
			
			$resultContent = $this->db->query($query, $data);
			
			if ( is_object($resultContent) ) {
				$resp = $resultContent->result('array');
				foreach ($resp as $respone) {
					$newdata = array();
					$newdata['UslugaComplexMedService_id'] = null;
					$newdata['UslugaComplexMedService_pid'] = $savedata['UslugaComplexMedService_id'];
					$newdata['UslugaComplexMedService_begDT'] = $data['UslugaComplexMedService_begDT'];
					$newdata['UslugaComplexMedService_endDT'] = $data['UslugaComplexMedService_endDT'];
					$newdata['UslugaComplexMedService_IsPortalRec'] = $data['UslugaComplexMedService_IsPortalRec'];
					$newdata['UslugaComplexMedService_IsPay'] = $data['UslugaComplexMedService_IsPay'];
					$newdata['UslugaComplexMedService_IsElectronicQueue'] = $data['UslugaComplexMedService_IsElectronicQueue'];
					$newdata['MedService_id'] = $data['MedService_id'];
					$newdata['UslugaComplex_id'] = $respone['UslugaComplex_id'];
					$newdata['pmUser_id'] = $data['pmUser_id'];

					$newdata['scenario'] = self::SCENARIO_DO_SAVE;
					$this->UslugaComplexMedService_model->doSave($newdata);
				}
			}
		}
		// сохраняем связи с ресурсами
		if (!empty($savedata['UslugaComplexMedService_id'])) {
			// для передачи в saveUSRData
			$data['UslugaComplexMedService_id'] = $savedata['UslugaComplexMedService_id'];
		}
		$this->load->model('MedService_model', 'MedService_model');
		$this->MedService_model->saveUSRData($data);

		return $savedata;
	}
	
	/**
	*  Удаление услуги на службе
	*/
	function deleteUslugaComplexMedService($data)
	{
		// Получаем состав входящих услуг и проверяем возможность удаления каждой услуги
		$query = '
			Select UslugaComplexMedService_id from UslugaComplexMedService (nolock) where UslugaComplexMedService_pid = :UslugaComplexMedService_id
		';
		$composition = array();
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			$composition = $result->result('array');
			if (is_array($composition) && count($composition)>0) {
				foreach ($composition as $item) {
					$checkResult = $this->checkUslugaComplexMedServiceIsUsed($item['UslugaComplexMedService_id'], array());
					if ( !empty($checkResult['Error_Msg']) ) {
						return array(array('Error_Msg' => $checkResult['Error_Msg']));
					}
				}
				// если мы пришли сюда, значит можем удалить состав, но предварительно надо проверить сможем ли мы удалить саму услугу
			}
		}
		
		// Проверка наличия ссылок на услугу в других таблицах
		$checkResult = $this->checkUslugaComplexMedServiceIsUsed($data['UslugaComplexMedService_id'], array('UslugaComplexMedService')); // поскольку состав мы удалим, то на UslugaComplexMedService не проверяем
		
		if ( !empty($checkResult['Error_Msg']) ) {
			return array(array('Error_Msg' => $checkResult['Error_Msg']));
		}
		
		$query = '
			declare
				@Error_Code int,
				@Error_Message varchar(4000);
				
			exec p_UslugaComplexMedService_del
				@UslugaComplexMedService_id = :UslugaComplexMedService_id,  
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
				select @Error_Code as Error_Code, @Error_Message as Error_Msg;
		';
		
		if (is_array($composition) && count($composition)>0) { // Если у данной услуги есть состав
			foreach ($composition as $item) { // то удаляем состав
				$result = $this->db->query($query, $item);
				if ( !is_object($result) ) {
					return false;
				}
			}
		}	
		// удаляем саму услугу
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	*	Получение грида услуг на службе
	*/
	function loadUslugaComplexMedServiceGrid($data)
	{
		$filters = "";
		
		if (!empty($data['UslugaComplexMedService_pid']) && $data['UslugaComplexMedService_pid'] > 0) {
			$filters .= " AND UCMS.UslugaComplexMedService_pid = :UslugaComplexMedService_pid";
		} else {
			$filters .= " AND UCMS.UslugaComplexMedService_pid IS NULL";
		}
		
		if (!empty($data['UslugaComplex_CodeName'])) {
			$filters .= " AND ISNULL(uc.UslugaComplex_Code,'') + ' ' + ISNULL(uc.UslugaComplex_Name,'') LIKE '%' + :UslugaComplex_CodeName + '%'";
			$queryParams['UslugaComplex_CodeName'] = $data['UslugaComplex_CodeName'];
		}
		
		$query = "
			SELECT
				-- select
				UCMS.UslugaComplexMedService_id
				,UCMS.UslugaComplexMedService_pid
				,UCMS.MedService_id
				,UC.UslugaComplex_id
				,UC.UslugaComplex_Name
				,ucat.UslugaCategory_Name
				,ucat.UslugaCategory_SysNick
				,uc.UslugaComplex_Code
				,convert(varchar(10),UCMS.UslugaComplexMedService_begDT,104) as UslugaComplexMedService_begDT
				,convert(varchar(10),UCMS.UslugaComplexMedService_endDT,104) as UslugaComplexMedService_endDT
				,ucms.UslugaComplexMedService_Time
				,UCCCount.count as CompositionCount
				,case when ucatr.UslugaComplexAttribute_id is not null then 1 else 0 end as IsLabUsluga,
				UCMS.UslugaComplexMedService_IsPortalRec,
				UCMS.UslugaComplexMedService_IsPay,
				UCMS.UslugaComplexMedService_IsElectronicQueue
				-- end select
			FROM 
				-- from
				v_UslugaComplexMedService UCMS with (NOLOCK)
				left join v_UslugaComplex UC with (NOLOCK) on UCMS.UslugaComplex_id = UC.UslugaComplex_id
				left join v_UslugaCategory ucat with (NOLOCK) on UC.UslugaCategory_id = ucat.UslugaCategory_id
				outer apply (
					select top 1 UslugaComplexAttribute_id
					from v_UslugaComplexAttribute with (NOLOCK)
					where UslugaComplex_id = UC.UslugaComplex_id
						and UslugaComplexAttributeType_id = 8
				) ucatr
				outer apply(
					select count(ucms_child.UslugaComplexMedService_id) as count from v_UslugaComplexMedService ucms_child with (nolock) where ucms_child.UslugaComplexMedService_pid = ucms.UslugaComplexMedService_id
				) UCCCount
				-- end from
			where
				-- where
				UCMS.MedService_id = :MedService_id
				{$filters}
				-- end where
			order by
				-- order by
				UC.UslugaComplex_Code
				-- end order by
		";
		
		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $data);
		$result_count = $this->db->query(getCountSQLPH($query), $data);

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
	*  Сохраняет связанную услугу
	*/
	function saveUslugaComplexLinked($data) {
		$this->beginTransaction();
		
		$newLinkedUslugas = array(); // массив связанных услуг
		
		$queryParams = array(
			'UslugaComplex_id' => $data['UslugaComplex_id']
			,'UslugaComplex_pid' => $data['UslugaComplex_pid']
		);
		
		$newLinkedUslugas[] = $data['UslugaComplex_id'];
		
		$uslugaLinkedData = $this->getUslugaComplexData($data['UslugaComplex_id']); // Данные связываемой услуги
		$uslugaParentData = $this->getUslugaComplexData($data['UslugaComplex_pid']); // Данные основной услуги
		
		if ( empty($uslugaLinkedData['UslugaCategory_SysNick']) ) {
			$this->rollbackTransaction();
			return array(array('Error_Msg' => 'Ошибка при определнии категории связываемой услуги'));
		}
		else if ( empty($uslugaParentData['UslugaCategory_SysNick']) ) {
			$this->rollbackTransaction();
			return array(array('Error_Msg' => 'Ошибка при определнии категории услуги'));
		}
		
		$LinkedUslugaCategory_SysNick = $uslugaLinkedData['UslugaCategory_SysNick'];
		$ParentUslugaCategory_SysNick = $uslugaParentData['UslugaCategory_SysNick'];
		$ParentUslugaComplexLevel_id = $uslugaParentData['UslugaComplexLevel_id'];
		
		// не суперадмин может связывать только услуги лпу
		if (!isSuperAdmin() && $ParentUslugaCategory_SysNick != 'lpu') {
			$this->rollbackTransaction();
			return array(array('Error_Msg' => 'Вы не можете связать указанные услуги'));
		}
		
		$field = $this->getUslugaComplexFieldNameByCategory($LinkedUslugaCategory_SysNick);
		
		if ($data['rewriteExistent'] == 1 && $LinkedUslugaCategory_SysNick == 'lpu') {
			// надо удалить связь с предыдущей услугой лпу (до редактирования)
			if (!empty($data['oldUslugaComplex_id'])) {
				$removeData = array();
				$removeData['UslugaComplex_pid'] = $data['UslugaComplex_pid'];
				$removeData['UslugaComplex_id'] = $data['oldUslugaComplex_id'];
				$this->deleteLinkedUslugaComplex($removeData);
			}
		}
		
		if (($data['rewriteExistent'] != 1) && (in_array($LinkedUslugaCategory_SysNick,$data['deniedCategoryList']) || ($field == '' && $LinkedUslugaCategory_SysNick != 'lpu'))) {
			$this->rollbackTransaction();
			return array(array('success' => false, 'Error_Msg' => 'Нельзя связать услугу данной категории'));
		}
		
		// Основная - эталонная
		if ( in_array($uslugaParentData['UslugaCategory_SysNick'], array('gost2004', 'gost2011', 'syslabprofile')) ) {
			// Обновляем поле у связываемой услуги, которое соответствует категории основной услуги
			$field = $this->getUslugaComplexFieldNameByCategory($uslugaParentData['UslugaCategory_SysNick']);
			$uslugaComplexId = $data['UslugaComplex_id'];
			$uslugaComplexPid = $data['UslugaComplex_pid'];
		}
		// Основная - услуга Промед, тфомс или услуга ЛПУ; связываемая - гост и пр.
		else {
			// Обновляем поле у основной услуги, которое соответствует категории связываемой услуги
			$field = $this->getUslugaComplexFieldNameByCategory($uslugaLinkedData['UslugaCategory_SysNick']);
			if (empty($field)) {
				return array(array('Error_Msg' => 'Нельзя связать услуги данных категорий'));
			}
			$uslugaComplexId = $data['UslugaComplex_pid'];
			$uslugaComplexPid = $data['UslugaComplex_id'];
		}
			
		$query = "
			update UslugaComplex with (rowlock)
			set " . $field . " = :UslugaComplex_pid
			where UslugaComplex_id = :UslugaComplex_id
		";

		$queryParams['UslugaComplex_id'] = $uslugaComplexId;
		$queryParams['UslugaComplex_pid'] = $uslugaComplexPid;

		$result = $this->db->query($query, $queryParams);

		if ($data['CopyContent'] == 1) {
			// скопировать состав услуги.
			$query = "
				select
					ucc.UslugaComplex_id
				from
					v_UslugaComplexComposition ucc with (nolock)
				where
					ucc.UslugaComplex_pid = :UslugaComplex_id and
					not exists (select UslugaComplexComposition_id from v_UslugaComplexComposition with (nolock) Where UslugaComplex_id = ucc.UslugaComplex_id and UslugaComplex_pid = :UslugaComplex_pid)
			";
			
			$result = $this->db->query($query, $queryParams);
			$count = 0;
			if ( is_object($result) ) {
				$resp = $result->result('array');
				foreach ($resp as $respone) {
					$count++;
					$uslugaComplexComposition = array();
					$uslugaComplexComposition['UslugaComplex_id'] = $respone['UslugaComplex_id'];
					$uslugaComplexComposition['UslugaComplex_pid'] = $data['UslugaComplex_pid'];
					$uslugaComplexComposition['pmUser_id'] = $data['pmUser_id'];
					$this->saveUslugaComplexComposition($uslugaComplexComposition);
				}
				
				// Если были добавлены услуги в состав и до этого услуга значилась как простая, то меняем уровень услуги на комплексную
				if ( ($count > 0 && $ParentUslugaComplexLevel_id == 3)) {
					$queryResponse = $this->setUslugaComplexLevel(array(
						 'pmUser_id' => $data['pmUser_id']
						,'UslugaComplex_id' => $data['UslugaComplex_pid']
						,'UslugaComplexLevel_id' => 2
					));

					if ( !is_array($queryResponse) || count($queryResponse) == 0 ) {
						$this->rollbackTransaction();
						return array(array('success' => false, 'Error_Msg' => 'Ошибка при обновлении уровня услуги'));
					}
					else if ( !empty($queryResponse[0]['Error_Msg']) ) {
						$this->rollbackTransaction();
						return array(array('success' => false, 'Error_Msg' => $queryResponse[0]['Error_Msg']));
					}
				}
			}
		}
		
		if ($data['CopyAllLinked'] == 1) {
			$query = "
				select 
					UslugaComplex_2004id, 
					UslugaComplex_2011id,
					UslugaComplex_slprofid
				from 
					v_UslugaComplex with (nolock)
				where
					UslugaComplex_id = :UslugaComplex_id
				";
				
			$result = $this->db->query($query, $queryParams);
		
			$addToQuery = array();
			$deniedCategoryList = $data['deniedCategoryList'];
			$deniedCategoryList[] = $LinkedUslugaCategory_SysNick;
			
			if ( is_object($result) ) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					if (!in_array('gost2004',$deniedCategoryList) && !empty($resp[0]['UslugaComplex_2004id'])) {	
						$newLinkedUslugas[] = $resp[0]['UslugaComplex_2004id'];
						$addToQuery[] = "UslugaComplex_2004id = :UslugaComplex_2004id";
						$queryParams['UslugaComplex_2004id'] = $resp[0]['UslugaComplex_2004id'];
					}
					if (!in_array('gost2011',$deniedCategoryList) && !empty($resp[0]['UslugaComplex_2011id'])) {	
						$newLinkedUslugas[] = $resp[0]['UslugaComplex_2011id'];
						$addToQuery[] = "UslugaComplex_2011id = :UslugaComplex_2011id";
						$queryParams['UslugaComplex_2011id'] = $resp[0]['UslugaComplex_2011id'];
					}
					if (!in_array('syslabprofile',$deniedCategoryList) && !empty($resp[0]['UslugaComplex_slprofid'])) {	
						$newLinkedUslugas[] = $resp[0]['UslugaComplex_slprofid'];
						$addToQuery[] = "UslugaComplex_slprofid = :UslugaComplex_slprofid";
						$queryParams['UslugaComplex_slprofid'] = $resp[0]['UslugaComplex_slprofid'];
					}
					
					$addToQuery = implode(',',$addToQuery);
					
					if (!empty($addToQuery)) {
						$query = "
							update 
								UslugaComplex with (rowlock)
							set 
								{$addToQuery}
							where
								UslugaComplex_id = :UslugaComplex_pid
						";
						
						$result = $this->db->query($query, $queryParams);
					}
				}
			}
		}
		
		if ($data['CopyAttributes'] == 1) {
			$addToQuery = "";
			if (!empty($newLinkedUslugas)) {
				$newLinkedUslugas = implode(',',$newLinkedUslugas);
				$addToQuery = ",".$newLinkedUslugas;
			}
			
			$query = "select 
				UslugaComplexAttributeType_id,
				UslugaComplexAttribute_Float,
				UslugaComplexAttribute_Text,
				UslugaComplexAttribute_DBTableID,
				UslugaComplexAttribute_Int,
				UslugaComplexAttribute_Value,
				convert(varchar(10), UslugaComplexAttribute_begDate, 120) as UslugaComplexAttribute_begDate,
				convert(varchar(10), UslugaComplexAttribute_endDate, 120) as UslugaComplexAttribute_endDate
			from
				v_UslugaComplexAttribute uca with (nolock)
			where 
				UslugaComplex_id in (:UslugaComplex_id{$addToQuery}) and 
				not exists (select UslugaComplexAttribute_id from v_UslugaComplexAttribute with (nolock) Where UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id and UslugaComplex_id = :UslugaComplex_pid)";
			
			$result = $this->db->query($query, $queryParams);
			
			$uslugaComplexAttribute = array(
				 'pmUser_id' => $data['pmUser_id']
				,'UslugaComplex_id' => $data['UslugaComplex_pid']
			);
			
			if ( is_object($result) ) {
				$resp = $result->result('array');
				foreach ($resp as $respone) {
					$uslugaComplexAttribute['UslugaComplexAttributeType_id'] = $respone['UslugaComplexAttributeType_id'];
					$uslugaComplexAttribute['UslugaComplexAttribute_Float'] = $respone['UslugaComplexAttribute_Float'];
					$uslugaComplexAttribute['UslugaComplexAttribute_Int'] = $respone['UslugaComplexAttribute_Int'];
					$uslugaComplexAttribute['UslugaComplexAttribute_Text'] = $respone['UslugaComplexAttribute_Text'];
					$uslugaComplexAttribute['UslugaComplexAttribute_DBTableID'] = $respone['UslugaComplexAttribute_DBTableID'];
					$uslugaComplexAttribute['UslugaComplexAttribute_begDate'] = $respone['UslugaComplexAttribute_begDate'];
					$uslugaComplexAttribute['UslugaComplexAttribute_endDate'] = $respone['UslugaComplexAttribute_endDate'];

					$queryResponse = $this->saveUslugaComplexAttribute($uslugaComplexAttribute);
					
					if ( !is_array($queryResponse) ) {
						$this->rollbackTransaction();
						return array(array('success' => false, 'Error_Msg' => 'Ошибка при копировании атрибутов услуг'));
					}
				}
			}
		}
		
		$this->commitTransaction();
		
		return array(array('success' => true, 'Error_Msg' => ''));
	}
		
	/**
	*  Проверяет можно ли связать услугу, возвращает список запрещенных для связывания категорий или false если что то пошло не так.
	*/
	function checkUslugaComplexCanBeLinked($data) {
		$queryParams = array(
			'UslugaComplex_id' => $data['UslugaComplex_id']
			,'UslugaComplex_pid' => $data['UslugaComplex_pid']
		);
		
		$query = "
			select top 1
				CASE WHEN uc.UslugaComplex_2004id IS NOT NULL THEN 'gost2004' ELSE '' END AS UslugaCategory_2SysNick
				,CASE WHEN uc.UslugaComplex_2011id IS NOT NULL THEN 'gost2011' ELSE '' END AS UslugaCategory_3SysNick
				,CASE WHEN uc.UslugaComplex_slprofid IS NOT NULL THEN 'syslabprofile' ELSE '' END AS UslugaCategory_4SysNick
				,ucat.UslugaCategory_SysNick
			from v_UslugaComplex uc with (nolock)
			left join v_UslugaCategory ucat with (nolock) on ucat.UslugaCategory_id = uc.UslugaCategory_id
			where UslugaComplex_id = :UslugaComplex_pid
		";
		
		$result = $this->db->query($query, $queryParams);

		$deniedCategoryList = array();
		
		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) > 0) {
				$deniedCategoryList[] = $resp[0]['UslugaCategory_2SysNick'];
				$deniedCategoryList[] = $resp[0]['UslugaCategory_3SysNick'];
				$deniedCategoryList[] = $resp[0]['UslugaCategory_4SysNick'];
				$deniedCategoryList[] = $resp[0]['UslugaCategory_SysNick'];
				
				return $deniedCategoryList;
			}
		}

		return false;
	}
	
	/**
	*  Проверяет есть ли услуга в составе услуги
	*/
	function checkUslugaComplexHasComposition($data) {
		$queryParams = array(
			'UslugaComplex_id' => $data['UslugaComplex_id']
			,'UslugaComplex_pid' => $data['UslugaComplex_pid']
		);
		
		$query = "
			select top 1 
				ucc.UslugaComplexComposition_id
			from
				v_UslugaComplexComposition ucc with (nolock)
			where 
				UslugaComplex_id = :UslugaComplex_id and
				UslugaComplex_pid = :UslugaComplex_pid
		";
		
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) > 0) {
				return true;
			}
		}

		return false;
	}
	
	/**
	*  Проверяет есть ли услуга в составе услуги в службе
	*/
	function checkUslugaComplexInMedService($data) {
		$filters = "";
		
		$queryParams = array(
			'MedService_id' => $data['MedService_id']
			,'UslugaComplex_id' => $data['UslugaComplex_id']
			,'UslugaComplexMedService_id' => $data['UslugaComplexMedService_id']
		);
		
		if (empty($data['UslugaComplexMedService_pid']) || $data['UslugaComplexMedService_pid'] == 0) {
			$filters .= " and UslugaComplexMedService_pid IS NULL";
		} else {
			$filters .= " and UslugaComplexMedService_pid = :UslugaComplexMedService_pid";
			$queryParams['UslugaComplexMedService_pid'] = $data['UslugaComplexMedService_pid'];
		}
		
		$query = "
			select top 1 
				ucms.UslugaComplexMedService_id
			from
				v_UslugaComplexMedService ucms with (nolock)
			where 
				UslugaComplex_id = :UslugaComplex_id and
				MedService_id = :MedService_id
				{$filters}
		";
		
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) > 0) {
				return true;
			}
		}

		return false;
	}

	private $_parentUslugaCategory_id = null;
	private $_commonUslugaCategory_id = null;
	private $_parentUslugaComplexLevel_id = null;

	/**
	 * Состав комплексной услуги и пакета услуг
	 * может быть только из услуг одной категории
	 * @param $UslugaComplex_id
	 * @param $UslugaComplex_pid
	 * @return bool
	 * @throws Exception
	 */
	private function checkUslugaCategory($UslugaComplex_id, $UslugaComplex_pid) {
		if (
			!isset($this->_commonUslugaCategory_id) ||
			!isset($this->_parentUslugaCategory_id) ||
			!isset($this->_parentUslugaComplexLevel_id)
		) {
			$query = "
				select top 1
					isnull(p.UslugaCategory_id, 0) as parentUslugaCategory_id,
					isnull(p.UslugaComplexLevel_id, 0) as parentUslugaComplexLevel_id,
					isnull(uc.UslugaCategory_id, -1) as commonUslugaCategory_id
				from v_UslugaComplex p with (nolock)
					left join v_UslugaComplexComposition ucc with (nolock) on ucc.UslugaComplex_pid = p.UslugaComplex_id
					left join v_UslugaComplex uc with (nolock) on uc.UslugaComplex_id = ucc.UslugaComplex_id
				where p.UslugaComplex_id = :UslugaComplex_id
			";
			$result = $this->db->query($query, array('UslugaComplex_id' => $UslugaComplex_pid));
			if ( !is_object($result) ) {
				throw new Exception('Ошибка получения общей категории услуг из состава ');
			}
			$resp = $result->result('array');
			if (count($resp) > 0) {
				$this->_parentUslugaCategory_id = $resp[0]['parentUslugaCategory_id'];
				$this->_parentUslugaComplexLevel_id = $resp[0]['parentUslugaComplexLevel_id'];
				$this->_commonUslugaCategory_id = $resp[0]['commonUslugaCategory_id'];
			} else {
				throw new Exception('Ошибка получения общей услуги');
			}
		}
		/*
		if ($this->_parentUslugaComplexLevel_id == 9) {
			// Состав пакета услуг может быть только из услуг той же категории - "Услуги ЛПУ"
			$this->_commonUslugaCategory_id = $this->_parentUslugaCategory_id;
		}
		*/
		if ($this->_commonUslugaCategory_id > 0) {
			$query = "
				select UslugaCategory_id
				from v_UslugaComplex with (nolock)
				where UslugaComplex_id = :UslugaComplex_id
			";
			$result = $this->db->query($query, array('UslugaComplex_id' => $UslugaComplex_id));
			if ( !is_object($result) ) {
				throw new Exception('Ошибка получения категории услуги');
			}
			$resp = $result->result('array');
			if (count($resp) > 0) {
				if ($this->_commonUslugaCategory_id != $resp[0]['UslugaCategory_id']) {
					throw new Exception('Состав комплексной услуги может быть только из услуг одной категории');
				}
			} else {
				throw new Exception('Ошибка получения услуги');
			}
		} else {
			// состава ещё нет, можно добавлять услугу любой категории?
		}
	}

	/**
	 *  Сохраняет услугу в составе комплексной услуги
	 */
	function saveUslugaComplexComposition($data) {
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @Res = :UslugaComplexComposition_id;

			exec p_UslugaComplexComposition_" . (!empty($data['UslugaComplexComposition_id']) && $data['UslugaComplexComposition_id'] > 0 ? "upd" : "ins") . "
				@UslugaComplexComposition_id = @Res output,
				@UslugaComplex_id = :UslugaComplex_id,
				@UslugaComplex_pid = :UslugaComplex_pid,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as UslugaComplexComposition_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$queryParams = array(
			'UslugaComplexComposition_id' => (!empty($data['UslugaComplexComposition_id']) && $data['UslugaComplexComposition_id'] > 0 ? $data['UslugaComplexComposition_id'] : NULL),
			'UslugaComplex_id' => $data['UslugaComplex_id'],
			'UslugaComplex_pid' => $data['UslugaComplex_pid'],
			'pmUser_id' => $data['pmUser_id'],
		);
		try {
			if ($data['UslugaComplex_id'] == $data['UslugaComplex_pid']) {
				throw new Exception('Нельзя добавлять услугу в состав самой себя');
			}
			// Состав комплексной услуги и пакета услуг может быть только из услуг одной категории
			$this->checkUslugaCategory($data['UslugaComplex_id'], $data['UslugaComplex_pid']);
			$result = $this->db->query($query, $queryParams);
			if ( is_object($result) ) {
				return $result->result('array');
			} else {
				throw new Exception('Ошибка запроса к БД (сохранение услугу в составе комплексной услуги)');
			}
		} catch (Exception $e) {
			return array(array('Error_Msg' => $e->getMessage()));
		}
	}

	/**
	 * Получение сисника по id услуги
	 */
	function getUslugaCategorySysNickByUslugaComplexId($UslugaComplex_id) {
		$query = "
			select top 1 
				ucat.UslugaCategory_SysNick
			from v_UslugaCategory ucat with (nolock)
				inner join v_UslugaComplex uc with (nolock) on uc.UslugaCategory_id = ucat.UslugaCategory_id
			where uc.UslugaComplex_id = :UslugaComplex_id
		";
		
		$result = $this->db->query($query, array('UslugaComplex_id' => $UslugaComplex_id));

		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) > 0) {
				return $resp[0]['UslugaCategory_SysNick'];
			}
		}

		return '';
	}

	/**
	*  Получение списка связанных услуг
	*/
	function loadLinkedUslugaGrid($data) {
		$deniedCategoryList = array();
		$filterList = array();

		if ( !empty($data['deniedCategoryList']) ) {
			$deniedCategoryList = json_decode($data['deniedCategoryList'], true);
		}
		
		// получаем категорию услуги
		$UslugaCategory_SysNick = $this->getUslugaCategorySysNickByUslugaComplexId($data['UslugaComplex_id']);
		
		$filter = "(1=1)";
		
		if (in_array($UslugaCategory_SysNick, array('gost2004','gost2011','syslabprofile'))) {
			// Для услуг гост4, гост 11, системные профили исследований отображать в списке связанных те услуги, которые ссылаются на них по соответствующим полям (c) Березовский Сергей
			$filter .= " and (:UslugaComplex_id in (uc.UslugaComplex_2004id, uc.UslugaComplex_2011id, uc.UslugaComplex_slprofid))";
		} else {
			// для остальных - показывать те услуги из категорий гост4, 11, системные профили исследований, на которые ссылается выбранная услуг
			$filter .= " and (uc.UslugaComplex_id in (
				select UslugaComplex_2004id as id from v_UslugaComplex with (nolock) where UslugaComplex_id = :UslugaComplex_id and UslugaComplex_2004id != :UslugaComplex_id
				union
				select UslugaComplex_2011id as id from v_UslugaComplex with (nolock) where UslugaComplex_id = :UslugaComplex_id and UslugaComplex_2011id != :UslugaComplex_id
				union
				select UslugaComplex_slprofid as id from v_UslugaComplex with (nolock) where UslugaComplex_id = :UslugaComplex_id and UslugaComplex_slprofid != :UslugaComplex_id
			))";
		}
		
		$query = "
			select
				 uc.UslugaComplex_id
				,1 as RecordStatus_Code
				,ucat.UslugaCategory_id
				,case when ucat.UslugaCategory_SysNick = 'lpu' then ucat.UslugaCategory_Name + ' - ' + l.Lpu_Nick else ucat.UslugaCategory_Name end as UslugaCategory_Name
				,ucat.UslugaCategory_SysNick
				,uc.UslugaComplex_Code
				,uc.UslugaComplex_Name
			from v_UslugaComplex uc with (nolock)
				inner join v_UslugaCategory ucat with (nolock) on ucat.UslugaCategory_id = uc.UslugaCategory_id
				left join v_Lpu l with (nolock) on l.Lpu_id = uc.Lpu_id
			where {$filter}
				". ( !isSuperadmin() ? " and (ucat.UslugaCategory_SysNick != 'lpu' OR l.Lpu_id = :Lpu_id)" : "" );

		if ( count($deniedCategoryList) > 0 ) {
			$query .= "
				and uc.UslugaCategory_id not in (" . implode(', ', $deniedCategoryList) . ")
			";
		}

		if ( $data['noLPU'] != 1 ) {
			$query .= "
				union all
				
				select
					 uc.UslugaComplex_id
					,1 as RecordStatus_Code
					,ucat.UslugaCategory_id
					,case when ucat.UslugaCategory_SysNick = 'lpu' then ucat.UslugaCategory_Name + ' - ' + l.Lpu_Nick else ucat.UslugaCategory_Name end as UslugaCategory_Name
					,ucat.UslugaCategory_SysNick
					,uc.UslugaComplex_Code
					,uc.UslugaComplex_Name
				from v_UslugaComplex uc with (nolock)
					inner join v_UslugaCategory ucat with (nolock) on ucat.UslugaCategory_id = uc.UslugaCategory_id
					left join v_Lpu l with (nolock) on l.Lpu_id = uc.Lpu_id
				where 
					(:UslugaComplex_id in (uc.UslugaComplex_2004id, uc.UslugaComplex_2011id, uc.UslugaComplex_slprofid, uc.UslugaComplex_llprofid)
					and uc.UslugaComplex_id != :UslugaComplex_id
					and ucat.UslugaCategory_SysNick = 'lpu')
					". ( !isSuperadmin() ? " and (ucat.UslugaCategory_SysNick != 'lpu' OR l.Lpu_id = :Lpu_id)" : "" );
		}
		
		$query .= "order by UslugaComplex_Code";

		$queryParams = array(
			'UslugaComplex_id' => $data['UslugaComplex_id'],
			'Lpu_id' => $data['Lpu_id']
		);

		// echo getDebugSQL($query, $queryParams); exit();

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	*  Получение списка атрибутов услуги
	*/
	function loadUslugaComplexAttributeGrid($data) {
		$uslugaComplexList = array();

		if ( !empty($data['uslugaComplexList']) ) {
			$uslugaComplexList = json_decode($data['uslugaComplexList'], true);
		}

		if ( !empty($data['EvnUslugaPar_ids']) ) {
			$query = " select UslugaComplex_id from v_EvnUslugaPar where EvnUslugaPar_id in ({$data['EvnUslugaPar_ids']})";
			$result =  $this->queryResult($query);
			if(!is_array($result)) throw new Exception('Ошибка при выполнении запроса');
			foreach ($result as $obj) {
				$uslugaComplexList[] = $obj['UslugaComplex_id'];
			}
		}

		$query = "
			select
				 uca.UslugaComplexAttribute_id
				,uca.UslugaComplex_id
				,1 as RecordStatus_Code
				,uca.UslugaComplexAttributeType_id
			    ,ucat.UslugaComplexAttributeType_Code
				,ucat.UslugaComplexAttributeType_SysNick
				,uca.UslugaComplexAttribute_Float
				,uca.UslugaComplexAttribute_Int
				,uca.UslugaComplexAttribute_Text
				,uca.UslugaComplexAttribute_DBTableID
				,avt.AttributeValueType_Name
				,uca.UslugaComplexAttribute_Value
				,ucat.UslugaComplexAttributeType_Name
				,convert(varchar(10), uca.UslugaComplexAttribute_begDate, 104) as UslugaComplexAttribute_begDate
				,convert(varchar(10), uca.UslugaComplexAttribute_endDate, 104) as UslugaComplexAttribute_endDate
				,ltrim(rtrim(pu.pmUser_Name)) as pmUser_Name
			from v_UslugaComplexAttribute uca with (nolock)
				inner join v_UslugaComplexAttributeType ucat with (nolock) on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
				inner join v_AttributeValueType avt with (nolock) on avt.AttributeValueType_id = ucat.AttributeValueType_id
				left join pmUserCache pu with (nolock) on pu.pmUser_id = uca.pmUser_updID
			where
				" . (count($uslugaComplexList) > 0 ? "uca.UslugaComplex_id in (" . implode(', ', $uslugaComplexList) . ")" : "uca.UslugaComplex_id = :UslugaComplex_id") . "
		";

		$queryParams = array(
			'UslugaComplex_id' => $data['UslugaComplex_id']
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	*  Получение списка профилей услуги
	*/
	function loadUslugaComplexProfileGrid($data) {
		$uslugaComplexList = array();

		if ( !empty($data['uslugaComplexList']) ) {
			$uslugaComplexList = json_decode($data['uslugaComplexList'], true);
		}

		$query = "
			select
				 ucp.UslugaComplexProfile_id
				,1 as RecordStatus_Code
				,ucp.LpuSectionProfile_id
				,convert(varchar(10), ucp.UslugaComplexProfile_begDate, 104) as UslugaComplexProfile_begDate
				,convert(varchar(10), ucp.UslugaComplexProfile_endDate, 104) as UslugaComplexProfile_endDate
				,lsp.LpuSectionProfile_Name
				,ltrim(rtrim(pu.pmUser_Name)) as pmUser_Name
			from v_UslugaComplexProfile ucp with (nolock)
				inner join v_LpuSectionProfile lsp with (nolock) on lsp.LpuSectionProfile_id = ucp.LpuSectionProfile_id
				left join pmUserCache pu with (nolock) on pu.pmUser_id = ucp.pmUser_updID
			where
				" . (count($uslugaComplexList) > 0 ? "ucp.UslugaComplex_id in (" . implode(', ', $uslugaComplexList) . ")" : "ucp.UslugaComplex_id = :UslugaComplex_id") . "
				and ucp.DispClass_id is null
		";

		$queryParams = array(
			'UslugaComplex_id' => $data['UslugaComplex_id']
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	*  Получение списка мест выполнения услуги
	*/
	function loadUslugaComplexPlaceGrid($data) {
		$query = "
			select
				 ucp.UslugaComplexPlace_id
				,1 as RecordStatus_Code
				,ucp.Lpu_id
				,ucp.LpuBuilding_id
				,ucp.LpuUnit_id
				,ucp.LpuSection_id
				,l.Lpu_Nick as Lpu_Name
				,lb.LpuBuilding_Name
				,lu.LpuUnit_Name
				,ls.LpuSection_Name
				,convert(varchar(10), ucp.UslugaComplexPlace_begDT, 104) as UslugaComplexPlace_begDate
				,convert(varchar(10), ucp.UslugaComplexPlace_endDT, 104) as UslugaComplexPlace_endDate
				,pu.pmUser_Name
			from v_UslugaComplexPlace ucp with (nolock)
				left join v_Lpu l with (nolock) on l.Lpu_id = ucp.Lpu_id
				left join v_LpuBuilding lb with (nolock) on lb.LpuBuilding_id = ucp.LpuBuilding_id
				left join v_LpuUnit lu with (nolock) on lu.LpuUnit_id = ucp.LpuUnit_id
				left join v_LpuSection ls with (nolock) on ls.LpuSection_id = ucp.LpuSection_id
				left join pmUserCache pu with (nolock) on pu.pmUser_id = ucp.pmUser_updID
			where
				ucp.UslugaComplex_id = :UslugaComplex_id
				" . (( !isSuperadmin() && $data['LpuEditFlag'] == 1) ? " and ucp.Lpu_id = :Lpu_id" : "") . "
		";

		$queryParams = array(
			'UslugaComplex_id' => $data['UslugaComplex_id'],
			'Lpu_id' => $data['Lpu_id']
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *	Загрузка грида тарифов
	 */
	function loadUslugaComplexTariffOnPlaceGrid($data) {
		$filter = "";

		if (!empty($data['isClose']) && $data['isClose'] == 1) {
			$filter .= " and (IsNull(uct.UslugaComplexTariff_endDate, @curdate+1) > @curdate)";
		} elseif (!empty($data['isClose']) && $data['isClose'] == 2) {
			$filter .= " and uct.UslugaComplexTariff_endDate <= @curdate";
		}

		if (!empty($data['UslugaComplex_id'])) {
			$filter .= " and uct.UslugaComplex_id = :UslugaComplex_id";
		}

		$outer = "";
		if (!empty($data['UslugaComplexPlace_id'])) {
			$outer = "
				outer apply(
					select top 1
						UslugaComplex_id, Lpu_id, LpuBuilding_id, LpuUnit_id, LpuSection_id
					from
						UslugaComplexPlace (nolock)
					where
						UslugaComplexPlace_id = :UslugaComplexPlace_id
				) ucp
			";

			$filter .= "
			 	and uct.UslugaComplex_id = ucp.UslugaComplex_id
			 	and (uct.Lpu_id = ucp.lpu_id or uct.Lpu_id is null)
				-- в зависимости от уровня структуры
				and (
					uct.LpuBuilding_id = ucp.LpuBuilding_id
					or uct.LpuBuilding_id = :LpuBuilding_id
					or uct.LpuBuilding_id is null
					or :LpuBuilding_id is null
				)

				and (
					uct.LpuUnit_id =ucp.LpuUnit_id
					or uct.LpuUnit_id is null
					or uct.LpuUnit_id = :LpuUnit_id
					or :LpuUnit_id is null
				)

				and (
					uct.LpuSection_id = ucp.LpuSection_id
					or uct.LpuSection_id is null
					or uct.LpuSection_id = :LpuSection_id
					or :LpuSection_id is null
				)
			";
		} else {
			if (!empty($data['Lpu_id'])) {
				$filter .= " and uct.Lpu_id = :Lpu_id";
			} else {
				$filter .= " and (uct.Lpu_id is null or uct.Lpu_id = :Lpu_id)";
				$data['Lpu_id'] = $data['session']['lpu_id'];
			}
		}

		$query = "
			-- variables
			declare @curdate datetime = dbo.tzGetDate();
			-- end variables
			
			select
				-- select
				 uct.UslugaComplexTariff_id
				,1 as RecordStatus_Code
				,uc.UslugaComplex_Code
				,uc.UslugaComplex_Name
				,uct.Lpu_id
				,uct.LpuBuilding_id
				,uct.UslugaComplex_id
				,uct.LpuUnit_id
				,uct.LpuSection_id
				,uct.MedService_id
				,uct.UslugaComplexTariffType_id
				,uct.PayType_id
				,ISNULL(l.Lpu_Nick, '')+ISNULL(', ' + lb.LpuBuilding_Name, '')+ISNULL(', ' + lu.LpuUnit_Name, '')+ISNULL(', ' + ls.LpuSection_Name, '') as Lpu_Name
				,uctt.UslugaComplexTariffType_Name
				,pt.PayType_Name
				,uct.UslugaComplexTariff_Tariff
				,convert(varchar(10), uct.UslugaComplexTariff_begDate, 104) as UslugaComplexTariff_begDate
				,convert(varchar(10), uct.UslugaComplexTariff_endDate, 104) as UslugaComplexTariff_endDate
				,null as EvnUsluga_setDate
				,rtrim(ltrim(pu.pmUser_Name)) as pmUser_Name
				,uct.LpuLevel_id
				,uct.LpuSectionProfile_id
				,uct.LpuUnitType_id
				,uct.MesAgeGroup_id
				,uct.Sex_id
				,uct.VizitClass_id
				,uct.UslugaComplexTariff_UED
				,uct.UslugaComplexTariff_UEM
				,uct.UslugaComplexTariff_Name
				,uct.UslugaComplexTariff_Code
				,s.Sex_Name
				,vc.VizitClass_Name
				,mag.MesAgeGroup_Name
				,lut.LpuUnitType_Name
				,lsp.LpuSectionProfile_Name
				,ll.LpuLevel_Name
				-- end select
			from 
				-- from
				v_UslugaComplexTariff uct with (nolock)
				inner join v_UslugaComplexTariffType uctt with (nolock) on uctt.UslugaComplexTariffType_id = uct.UslugaComplexTariffType_id
				inner join v_PayType pt with (nolock) on pt.PayType_id = uct.PayType_id
				left join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = uct.UslugaComplex_id
				left join v_Lpu l with (nolock) on l.Lpu_id = uct.Lpu_id
				left join v_LpuBuilding lb with (nolock) on lb.LpuBuilding_id = uct.LpuBuilding_id
				left join v_LpuUnit lu with (nolock) on lu.LpuUnit_id = uct.LpuUnit_id
				left join v_LpuSection ls with (nolock) on ls.LpuSection_id = uct.LpuSection_id
				left join v_Sex s with (nolock) on s.Sex_id = uct.Sex_id
				left join v_VizitClass vc with (nolock) on vc.VizitClass_id = uct.VizitClass_id
				left join v_MesAgeGroup mag with (nolock) on mag.MesAgeGroup_id = uct.MesAgeGroup_id
				left join v_LpuUnitType lut with (nolock) on lut.LpuUnitType_id = uct.LpuUnitType_id
				left join v_LpuSectionProfile lsp with (nolock) on lsp.LpuSectionProfile_id = uct.LpuSectionProfile_id
				left join v_LpuLevel ll with (nolock) on ll.LpuLevel_id = uct.LpuLevel_id
				left join pmUserCache pu with (nolock) on pu.pmUser_id = uct.pmUser_updID
				/*outer apply (
					select max(EvnUsluga_setDT) as EvnUsluga_setDT
					from v_EvnUsluga with (nolock)
					where UslugaComplexTariff_id = uct.UslugaComplexTariff_id
				) eu*/
				{$outer}
				-- end from
			where
				-- where
				1=1
				{$filter}
				-- end where
			order by
				-- order by
				uct.UslugaComplexTariff_id
				-- end order by
		";
		
		
		// echo getDebugSql($query, $data); die();
		
		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $data);
		$result_count = $this->db->query(getCountSQLPH($query), $data);

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
	 *	Получение максимальной даты последней услуги по тарифу для редактирования на форме
	 */
	function getUslugaComplexTariffMaxDate($data) {
		if (empty($data['UslugaComplexTariff_id'])) {
			return null;
		}
		$filter = "";
		if (!empty($data['Lpu_id'])) {
			$filter .= " and (Lpu_id = :Lpu_id)";
		}
		$query = "
			select convert(varchar(10), max(EvnUsluga_setDT), 104) as EvnUsluga_setDate
			from v_EvnUsluga with (nolock)
			where UslugaComplexTariff_id = :UslugaComplexTariff_id {$filter}
		";
		
		// echo getDebugSql($query, $data); die();
		
		$result = $this->db->query($query, $data);
		
		if (is_object($result)) {
			$arr = $result->result('array');
			
			if (count($arr)>0) {
				return $arr[0];
			}
		}
		return null;
	}
    /**
     * Получение списка услуг для выбора из состава пакета или по МЭС для формы оказания услуг
     */
    function loadForSelect($data)
    {
        $queryParams = array();
        $selectIsMes = 'null as UslugaComplex_IsByMes';
        $joinMes = '';
		$datefilter3 = "";

		if (getRegionNick() == 'perm' && !empty($data['EvnUsluga_pid'])) { // тариф на дату последнего посещения в ТАП.
			$filter_check = "";
			$checkParams = array(
				'EvnUsluga_pid' => $data['EvnUsluga_pid']
			);
			if (!empty($data['UslugaComplexTariff_Date'])) {
				$filter_check .= " and ev2.EvnVizit_setDate >= :UslugaComplexTariff_Date";
				$checkParams['UslugaComplexTariff_Date'] = $data['UslugaComplexTariff_Date'];
			}
			$resp = $this->queryResult("
				select top 1
					convert(varchar(10), ev2.EvnVizit_setDate, 120) as EvnVizit_setDate
				from
					v_EvnVizit ev (nolock)
					inner join v_EvnVizit ev2 (nolock) on ev2.EvnVizit_pid = ev.EvnVizit_pid and ev2.EvnVizit_id <> ev.EvnVizit_id
				where
					ev.EvnVizit_id = :EvnUsluga_pid
					{$filter_check}
				order by
					ev2.EvnVizit_setDate desc		
			", $checkParams);

			if (!empty($resp[0]['EvnVizit_setDate'])) {
				$data['UslugaComplexTariff_Date'] = $resp[0]['EvnVizit_setDate'];
			}
		}

        if (!empty($data['Mes_id']) && isset($data['UslugaComplex_id'])) {
            $queryParams['Mes_id'] = $data['Mes_id'];
            $selectIsMes = 'case when mu.Mes_id is null then 1 else 2 end as UslugaComplex_IsByMes';
			$datefilter1 = "";
			$datefilter2 = "";

			if (!empty($data['UslugaComplexTariff_Date'])) {
				$queryParams['UslugaComplexTariff_Date'] = $data['UslugaComplexTariff_Date'];
				$datefilter1 = "
					and UslugaComplexTariff_begDate <= :UslugaComplexTariff_Date
					and (UslugaComplexTariff_endDate >= :UslugaComplexTariff_Date or UslugaComplexTariff_endDate is null)
				";
				$datefilter2 = "
					and ISNULL(mu.MesUsluga_begDT, :UslugaComplexTariff_Date) <= :UslugaComplexTariff_Date
					and ISNULL(mu.MesUsluga_endDT, :UslugaComplexTariff_Date) >= :UslugaComplexTariff_Date
				";
				$datefilter3 = "
					and ISNULL(UC.UslugaComplex_begDT, :UslugaComplexTariff_Date) <= :UslugaComplexTariff_Date
					and ISNULL(UC.UslugaComplex_endDT, :UslugaComplexTariff_Date) >= :UslugaComplexTariff_Date
				";
			}
            $joinMes = "outer apply (
                select top 1 mu.Mes_id
                from v_MesUsluga mu with (nolock)
                where mu.UslugaComplex_id = UC.UslugaComplex_2011id
                    and mu.Mes_id = :Mes_id
                    and exists (
                        select top 1 UslugaComplexTariff_id
                        from v_UslugaComplexTariff with (nolock)
                        where Lpu_id is null
                            and UslugaComplex_id = UC.UslugaComplex_id
                            --and UslugaComplexTariff_UED = mu.MesUsluga_UslugaCount
                            {$datefilter1}
                    )
					{$datefilter2}
            ) mu";
        }
        $evnFilter = '';

		if ( !empty($data['doNotIncludeEvnUslugaDid']) ) {
			if (!empty($data['EvnUsluga_rid'])) {
				$queryParams['EvnUsluga_rid'] = $data['EvnUsluga_rid'];
				$evnFilter = ' and not exists (
					select top 1 eu.EvnUsluga_id
					from EvnUsluga eu with (nolock)
					inner join Evn (nolock) on Evn.Evn_id = eu.EvnUsluga_id and Evn.Evn_deleted = 1
					where eu.EvnUsluga_rid = :EvnUsluga_rid
						and eu.UslugaComplex_id = UC.UslugaComplex_id
				)';
			} else if (!empty($data['EvnUsluga_pid'])) {
				$edpjoin = "";
				$edpfilter = "";
				if (!empty($data['EvnDiagPLStom_id'])) {
					$queryParams['EvnDiagPLStom_id'] = $data['EvnDiagPLStom_id'];
					$edpjoin = " inner join EvnUslugaStom eus (nolock) on eus.EvnUslugaStom_id = eu.EvnUsluga_id";
					$edpfilter = " and eus.EvnDiagPLStom_id = :EvnDiagPLStom_id";
				}
				else {
					$edpfilter = " and eu.EvnUsluga_pid = :EvnUsluga_pid";
				}
				$queryParams['EvnUsluga_pid'] = $data['EvnUsluga_pid'];
				$evnFilter = " and not exists (
					select top 1 eu.EvnUsluga_id
					from EvnUsluga eu with (nolock)
					inner join Evn (nolock) on Evn.Evn_id = eu.EvnUsluga_id and Evn.Evn_deleted = 1
					{$edpjoin}
					where
						eu.UslugaComplex_id = UC.UslugaComplex_id
						{$edpfilter}
				)";
			}
		}

        if (!empty($data['UslugaComplex_id'])) {
            $queryParams['UslugaComplex_id'] = $data['UslugaComplex_id'];
            $sql = "
            select
                UC.UslugaComplex_id,
                UC.UslugaComplex_Code,
                UC.UslugaComplex_Name,
                {$selectIsMes},
                1 as EvnUsluga_Kolvo,
                0 as UslugaComplexTariff_Count,
                null as UslugaComplexTariff_id,
                0 as UslugaComplexTariff_Tariff,
                0 as UslugaComplexTariff_UED,
                0 as UslugaComplexTariff_UEM
            from v_UslugaComplexComposition UCC with (nolock)
            inner join v_UslugaComplex UC with (nolock) on UCC.UslugaComplex_id = UC.UslugaComplex_id
            {$joinMes}
            where UCC.UslugaComplex_pid = :UslugaComplex_id
            {$evnFilter}
            {$datefilter3}
            order by UC.UslugaComplex_Name
            ";
        } else if (!empty($data['Mes_id']) && !empty($data['EvnUsluga_pid'])) {
            $queryParams['Mes_id'] = $data['Mes_id'];
            $queryParams['EvnUsluga_pid'] = $data['EvnUsluga_pid'];
			$datefilter1 = "";
			$datefilter2 = "";
			$datefilter3 = "";
			if (!empty($data['UslugaComplexTariff_Date'])) {
				$queryParams['UslugaComplexTariff_Date'] = $data['UslugaComplexTariff_Date'];
				$datefilter1 = "
					and UslugaComplexTariff_begDate <= :UslugaComplexTariff_Date
					and (UslugaComplexTariff_endDate >= :UslugaComplexTariff_Date or UslugaComplexTariff_endDate is null)
				";
				$datefilter2 = "
					and ISNULL(mu.MesUsluga_begDT, :UslugaComplexTariff_Date) <= :UslugaComplexTariff_Date
					and ISNULL(mu.MesUsluga_endDT, :UslugaComplexTariff_Date) >= :UslugaComplexTariff_Date
				";
				$datefilter3 = "
					and ISNULL(UC.UslugaComplex_begDT, :UslugaComplexTariff_Date) <= :UslugaComplexTariff_Date
					and ISNULL(UC.UslugaComplex_endDT, :UslugaComplexTariff_Date) >= :UslugaComplexTariff_Date
				";
			}

			$joinuc = "inner join v_UslugaComplex UC with (nolock) on UC.UslugaComplex_2011id = mu.UslugaComplex_id";
			if (!empty($data['EvnDiagPLStom_id'])) {
				// для КСГ надо напрямую джойнить
				$joinuc = "inner join v_UslugaComplex UC with (nolock) on UC.UslugaComplex_id = mu.UslugaComplex_id";
			}

            $sql = "
            select
                UC.UslugaComplex_id,
                UC.UslugaComplex_Code,
                UC.UslugaComplex_Name,
                2 as UslugaComplex_IsByMes,
                1 as EvnUsluga_Kolvo,
                0 as UslugaComplexTariff_Count,
                null as UslugaComplexTariff_id,
                0 as UslugaComplexTariff_Tariff,
                0 as UslugaComplexTariff_UED,
                0 as UslugaComplexTariff_UEM,
				case when mu.MesUsluga_IsNeedUsluga = 2 then 'X' else '' end as MesUsluga_IsNeedUsluga
            from v_MesUsluga mu with (nolock)
            inner join v_MesOld m with (nolock) on m.Mes_id = mu.Mes_id
            {$joinuc}
            where mu.Mes_id = :Mes_id
                and (m.MesType_id = 7 or exists (  -- данная фильтрация по тарифу не нужна для КСГ
                    select top 1 UslugaComplexTariff_id
                    from v_UslugaComplexTariff with (nolock)
                    where Lpu_id is null
                        and UslugaComplex_id = UC.UslugaComplex_id
                        and UslugaComplexTariff_UED = mu.MesUsluga_UslugaCount
                        {$datefilter1}
                ))
                {$evnFilter}
                {$datefilter2}
                {$datefilter3}
            order by uc.UslugaComplex_Name
            ";
        } else {
            return false;
        }
        //echo getDebugSQL($sql, $params);
        $result = $this->db->query($sql, $queryParams);
        if (!is_object($result)) {
            return false;
        }
        $tmp = $result->result('array');
        if (empty($tmp)) {
            return array();
        }
        if ( empty($data['LpuSection_id']) || empty($data['PayType_id']) 
        || empty($data['Person_id']) || empty($data['UslugaComplexTariff_Date'])
        ) {
            return $tmp;
        }
        // считаем тарифы
        $usluga_complex_list = array();
        $usluga_list = array();
        foreach ($tmp as $row) {
            $id = $row['UslugaComplex_id'];
            $usluga_complex_list[] = $id;
            $usluga_list[$id] = $row;
        }
        $data['UslugaComplex_id'] = null;
        $data['in_UslugaComplex_list'] = implode(', ', $usluga_complex_list);
        $this->load->model('Usluga_model', 'Usluga_model');
        $tariffList = $this->Usluga_model->loadUslugaComplexTariffList($data);
        if (!is_array($tariffList)) {
            return false;
        }
        foreach ($tariffList as $row) {
            $id = $row['UslugaComplex_id'];
            $usluga_list[$id]['UslugaComplexTariff_Count']++;
            $usluga_list[$id]['UslugaComplexTariff_id'] = $row['UslugaComplexTariff_id'];
            $usluga_list[$id]['UslugaComplexTariff_UED'] = $row['UslugaComplexTariff_UED'];
            $usluga_list[$id]['UslugaComplexTariff_UEM'] = $row['UslugaComplexTariff_UEM'];
            $usluga_list[$id]['UslugaComplexTariff_Tariff'] = $row['UslugaComplexTariff_Tariff'];
        }
        $response = array();
        foreach ($usluga_list as $row) {
            if ($row['UslugaComplexTariff_Count'] > 1) {
                $row['UslugaComplexTariff_id'] = null;
                $row['UslugaComplexTariff_UED'] = 0;
                $row['UslugaComplexTariff_UEM'] = 0;
                $row['UslugaComplexTariff_Tariff'] = 0;
            }
            $response[] = $row;
        }
        return $response;
    }

	/**
	 *  Получение списка тарифов по услуге
	 */
	function loadUslugaComplexTariffGrid($data) {
		$query = "
			with eu (
				 EvnUsluga_setDT
				,UslugaComplexTariff_id
			) as (
				select max(EvnUsluga_setDT) as EvnUsluga_setDT, UslugaComplexTariff_id
				from v_EvnUsluga with (nolock)
				where UslugaComplexTariff_id in (
						select UslugaComplexTariff_id
						from v_UslugaComplexTariff with (nolock)
						where UslugaComplex_id = :UslugaComplex_id
							" . (( !isSuperadmin() && $data['LpuEditFlag'] == 1) ? "and Lpu_id = :Lpu_id" : "") . "
					)
				group by UslugaComplexTariff_id
			)

			select
				 uct.UslugaComplexTariff_id
				,1 as RecordStatus_Code
				,uct.Lpu_id
				,uct.LpuBuilding_id
				,uct.LpuUnit_id
				,uct.LpuSection_id
				,uct.MedService_id
				,uct.UslugaComplexTariffType_id
				,uct.PayType_id
				,ISNULL(l.Lpu_Nick, '')+ISNULL(', ' + lb.LpuBuilding_Name, '')+ISNULL(', ' + lu.LpuUnit_Name, '')+ISNULL(', ' + ls.LpuSection_Name, '')+ISNULL(', ' + ms.MedService_Name, '') as Lpu_Name
				,uctt.UslugaComplexTariffType_Name
				,pt.PayType_Name
				,uct.UslugaComplexTariff_Tariff
				,convert(varchar(10), uct.UslugaComplexTariff_begDate, 104) as UslugaComplexTariff_begDate
				,convert(varchar(10), uct.UslugaComplexTariff_endDate, 104) as UslugaComplexTariff_endDate
				,convert(varchar(10), eu.EvnUsluga_setDT, 104) as EvnUsluga_setDate
				,rtrim(ltrim(pu.pmUser_Name)) as pmUser_Name
				,uct.LpuLevel_id
				,uct.LpuSectionProfile_id
				,uct.LpuUnitType_id
				,uct.MesAgeGroup_id
				,uct.Sex_id
				,uct.UslugaComplexTariff_UED
				,uct.UslugaComplexTariff_UEM
				,uct.UslugaComplexTariff_Name
				,uct.UslugaComplexTariff_Code
				,s.Sex_Name
				,mag.MesAgeGroup_Name
				,lut.LpuUnitType_Name
				,lsp.LpuSectionProfile_Name
				,ll.LpuLevel_Name
			from v_UslugaComplexTariff uct with (nolock)
				inner join v_UslugaComplexTariffType uctt with (nolock) on uctt.UslugaComplexTariffType_id = uct.UslugaComplexTariffType_id
				inner join v_PayType pt with (nolock) on pt.PayType_id = uct.PayType_id
				left join v_Lpu l with (nolock) on l.Lpu_id = uct.Lpu_id
				left join v_LpuBuilding lb with (nolock) on lb.LpuBuilding_id = uct.LpuBuilding_id
				left join v_LpuUnit lu with (nolock) on lu.LpuUnit_id = uct.LpuUnit_id
				left join v_LpuSection ls with (nolock) on ls.LpuSection_id = uct.LpuSection_id
				left join v_MedService ms with (nolock) on ms.MedService_id = uct.MedService_id
				left join v_Sex s with (nolock) on s.Sex_id = uct.Sex_id
				left join v_MesAgeGroup mag with (nolock) on mag.MesAgeGroup_id = uct.MesAgeGroup_id
				left join v_LpuUnitType lut with (nolock) on lut.LpuUnitType_id = uct.LpuUnitType_id
				left join v_LpuSectionProfile lsp with (nolock) on lsp.LpuSectionProfile_id = uct.LpuSectionProfile_id
				left join v_LpuLevel ll with (nolock) on ll.LpuLevel_id = uct.LpuLevel_id
				left join pmUserCache pu with (nolock) on pu.pmUser_id = uct.pmUser_updID
				left join eu with(nolock) on eu.UslugaComplexTariff_id = uct.UslugaComplexTariff_id
			where
				uct.UslugaComplex_id = :UslugaComplex_id
				" . (( !isSuperadmin() && $data['LpuEditFlag'] == 1) ? " and uct.Lpu_id = :Lpu_id" : "") . "
		";

		$queryParams = array(
			'UslugaComplex_id' => $data['UslugaComplex_id'],
			'Lpu_id' => $data['Lpu_id']
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	*  Получение данных для формы редактирования услуги
	*/
	function loadUslugaComplexEditForm($data) {
		$query = "
			select top 1
				 'edit' as accessType
				,uc.UslugaComplex_id
				,uc.UslugaComplex_pid
				,uc.Lpu_id
				,uc.UslugaCategory_id
				,uc.UslugaComplex_Code
				,uc.UslugaComplex_Name
				,uc.UslugaComplex_Nick
				,uc.UslugaComplex_ACode
				,case when 9 = uc.UslugaComplexLevel_id then 'on' else 'off' end as UslugaComplex_isPackage
				,convert(varchar(10), uc.UslugaComplex_begDT, 104) as UslugaComplex_begDate
				,convert(varchar(10), uc.UslugaComplex_endDT, 104) as UslugaComplex_endDate
				,uc.UslugaComplex_UET
				,uc.XmlTemplate_id
				,ucat.UslugaCategory_SysNick
				,uci.UslugaComplexInfo_id
				,uci.UslugaComplexInfo_ImportantInfo
				,uci.UslugaComplexInfo_RecipientCat
				,uci.UslugaComplexInfo_DocumentUsluga
				,uci.UslugaComplexInfo_Limit
				,uci.UslugaComplexInfo_PayOrder
				,uci.UslugaComplexInfo_QueueType
				,uci.UslugaComplexInfo_ServiceOrder
				,uci.UslugaComplexInfo_Duration
				,uci.UslugaComplexInfo_Result
			from
				v_UslugaComplex uc with (nolock)
				inner join v_UslugaCategory ucat with (nolock) on ucat.UslugaCategory_id = uc.UslugaCategory_id
				left join v_UslugaComplexInfo uci with(nolock) on uci.UslugaComplex_id = uc.UslugaComplex_id
			where
				uc.UslugaComplex_id = :UslugaComplex_id
		";

		$queryParams = array(
			'UslugaComplex_id' => $data['UslugaComplex_id']
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Загрузка комбо групп услуг
	 */
	function loadUslugaComplexGroupList($data) {
		if (empty($data['filterByUslugaComplex_id'])) {
			return array();
		}

		$data['UslugaComplex_pid'] = null;
		$data['UslugaCategory_id'] = null;
		$query = "
			select
				UslugaComplex_pid,
				UslugaCategory_id
			from
				v_UslugaComplex (nolock)
			where
				UslugaComplex_id = :filterByUslugaComplex_id
		";

		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (!empty($resp[0]['UslugaCategory_id'])) {
				$data['UslugaComplex_pid'] = $resp[0]['UslugaComplex_pid'];
				$data['UslugaCategory_id'] = $resp[0]['UslugaCategory_id'];
			}
		}

		$query = "
			select top 100
				UslugaComplex_id
				,UslugaComplex_pid
				,uc.UslugaCategory_id
				,uc.UslugaComplexLevel_id
				,UslugaComplex_Code
				,UslugaComplex_Name
				,convert(varchar(10), UslugaComplex_begDT, 104) as UslugaComplex_begDT
				,convert(varchar(10), UslugaComplex_endDT, 104) as UslugaComplex_endDT
				,ucat.UslugaCategory_SysNick
			from
				v_UslugaComplex uc with (nolock)
				inner join v_UslugaCategory ucat with (nolock) on ucat.UslugaCategory_id = uc.UslugaCategory_id
			where
				uc.UslugaCategory_id = :UslugaCategory_id
				and uc.UslugaComplexLevel_id = 1
				and ISNULL(uc.UslugaComplex_pid, 0) = ISNULL(:UslugaComplex_pid, 0)
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
	 * Загрузка списка услуг по идентификатору/коду МЭС
	 */
	public function loadUslugaComplexListForMes($data) {
		if ( empty($data['Mes_id']) && empty($data['Mes_Code']) ) {
			return array();
		}

		$filterList = array();
		$joinList = array();
		$queryParams = array();

		if ( !empty($data['Mes_id']) ) {
			$filterList[] = "mu.Mes_id = :Mes_id";
			$queryParams['Mes_id'] = $data['Mes_id'];
		}

		if ( !empty($data['Mes_Code']) ) {
			$filterList[] = "mes.Mes_Code = :Mes_Code";
			$joinList[] = "inner join v_MesOld mes with (nolock) on mes.Mes_id = mu.Mes_id";
			$queryParams['Mes_Code'] = $data['Mes_Code'];
		}

		if ( !empty($data['requiredOnly']) ) {
			$filterList[] = "mu.MesUsluga_IsNeedUsluga = 2";
		}

		$query = "
			with MU_UC as (
				select mu.UslugaComplex_id
				from v_MesUsluga mu with (nolock)
					" . implode(' ', $joinList) . "
				where " . implode(' and ', $filterList) . "
			)

			select
				uc.UslugaComplex_id,
				uc.UslugaComplex_Code,
				uc.UslugaComplex_Name,
				ucat.UslugaCategory_id,
				ucat.UslugaCategory_Name,
				ucat.UslugaCategory_SysNick
			from
				v_UslugaComplex uc (nolock)
				inner join v_UslugaCategory ucat with (nolock) on ucat.UslugaCategory_id = uc.UslugaCategory_id
			where
				uc.UslugaComplex_id in (select UslugaComplex_id from MU_UC)
		";

		return $this->queryResult($query, $queryParams);
	}

	/**
	*  Получение данных для формы редактирования группы услуг
	*/
	function loadUslugaComplexGroupEditForm($data) {
		$query = "
			select top 1
				 'edit' as accessType
				,UslugaComplex_id
				,UslugaComplex_pid
				,Lpu_id
				,uc.UslugaCategory_id
				,UslugaComplex_Code
				,UslugaComplex_Name
				,UslugaComplex_Nick
				,UslugaComplex_ACode
				,case when 9 = uc.UslugaComplexLevel_id then 'on' else 'off' end as UslugaComplex_isPackage
				,convert(varchar(10), UslugaComplex_begDT, 104) as UslugaComplex_begDate
				,convert(varchar(10), UslugaComplex_endDT, 104) as UslugaComplex_endDate
				,UslugaComplex_UET
				,XmlTemplate_id
				,ucat.UslugaCategory_SysNick
			from
				v_UslugaComplex uc with (nolock)
				inner join v_UslugaCategory ucat with (nolock) on ucat.UslugaCategory_id = uc.UslugaCategory_id
			where
				UslugaComplex_id = :UslugaComplex_id
		";

		$queryParams = array(
			'UslugaComplex_id' => $data['UslugaComplex_id']
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	*  Получение информации об услуге
	*/
	function getUslugaComplexData($UslugaComplex_id) {
		$query = "
			select top 1
				 uc.UslugaComplex_id
				,uc.UslugaComplex_pid
				,uc.Lpu_id
				,uc.UslugaComplex_Code
				,uc.UslugaComplex_Name
				,uc.UslugaComplex_Nick
				,uc.UslugaComplex_ACode
				,uc.UslugaComplexLevel_id
				,convert(varchar(10), uc.UslugaComplex_begDT, 104) as UslugaComplex_begDate
				,convert(varchar(10), uc.UslugaComplex_endDT, 104) as UslugaComplex_endDate
				,uc.UslugaComplex_UET
				,ucat.UslugaCategory_id
				,ucat.UslugaCategory_SysNick
			from
				v_UslugaComplex uc with (nolock)
				inner join v_UslugaCategory ucat with (nolock) on ucat.UslugaCategory_id = uc.UslugaCategory_id
			where
				uc.UslugaComplex_id = :UslugaComplex_id
		";

		$queryParams = array(
			'UslugaComplex_id' => $UslugaComplex_id
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$response = $result->result('array');

			if ( is_array($response) && count($response) > 0 ) {
				return $response[0];
			}
			else {
				return false;
			}
		}
		else {
			return false;
		}
	}


	/**
	*  Читает дерево комплексных услуг
	*/
	function loadUslugaComplexTree($data) {
		$queryParams = array(
			 'Lpu_id' => $data['Lpu_id']
			,'Lpu_uid' => $data['Lpu_uid']
			,'UslugaCategory_id' => $data['UslugaCategory_id']
			,'UslugaComplex_id' => $data['UslugaComplex_id']
			,'UslugaComplexLevel_id' => $data['UslugaComplexLevel_id']
		);

		$data['UslugaCategory_SysNick'] = $this->getUslugaCategorySysNickById($data['UslugaCategory_id']);
		
		switch ( $data['level'] ) {
			case 0:
				$query = "
					select
						 'ucat' + cast(ucat.UslugaCategory_id as varchar(20)) as id
						,null as code
						,ucat.UslugaCategory_Name as name
						,'UslugaCategory' as object
						,ucat.UslugaCategory_id
						,ucat.UslugaCategory_SysNick
						,null as UslugaComplexLevel_id
						,case when ucat.UslugaCategory_SysNick in ('tfoms', 'pskov_foms', 'lpu') or uccgost.cnt > 0 then 0 else 1 end as leaf
					from
						v_UslugaCategory ucat with (nolock)
						outer apply (
							select count(UslugaComplex_id) as cnt
							from v_UslugaComplex with (nolock)
							where UslugaCategory_id = ucat.UslugaCategory_id
								and UslugaCategory_SysNick in ('gost2011', 'gost2011r', 'gost2004', 'Kod7', 'simple', 'lpusectiontree')
								and UslugaComplexLevel_id is not null
						) uccgost
					order by
						ucat.UslugaCategory_Code
				";
				if($this->getRegionNick() == 'kz'){
					$query = "
						select
							 'ucat' + cast(ucat.UslugaCategory_id as varchar(20)) as id
							,null as code
							,ucat.UslugaCategory_Name as name
							,'UslugaCategory' as object
							,ucat.UslugaCategory_id
							,ucat.UslugaCategory_SysNick
							,null as UslugaComplexLevel_id
							,case when ucat.UslugaCategory_SysNick in ('tfoms', 'pskov_foms', 'lpu') or uccgost.cnt > 0 then 0 else 1 end as leaf
						from
							v_UslugaCategory ucat with (nolock)
							outer apply (
								select count(UslugaComplex_id) as cnt
								from v_UslugaComplex with (nolock)
								where UslugaCategory_id = ucat.UslugaCategory_id
									and UslugaCategory_SysNick in ('gost2011', 'gost2011r', 'gost2004', 'Kod7', 'simple', 'lpusectiontree')
									and UslugaComplexLevel_id is not null
							) uccgost
						where
							ucat.UslugaCategory_SysNick not in ('promed', 'tfoms', 'gost2004', 'gost2011', 'ksg', 'amb', 'gost2011r')
						order by
							ucat.UslugaCategory_Code
					";
				}
			break;

			case 1:
				switch ( $data['UslugaCategory_SysNick'] ) {
					case 'tfoms':
					case 'pskov_foms':
						$query = "
							select
								 'ucom' + cast(uc.UslugaComplex_id as varchar(20)) as id
								,null as code
								,uc.UslugaComplex_Name as name
								,'UslugaComplex' as object
								,ucat.UslugaCategory_id
								,ucat.UslugaCategory_SysNick
								,null as UslugaComplexLevel_id
								,1 as leaf
							from
								v_UslugaComplex uc with (nolock)
								inner join v_UslugaCategory ucat with (nolock) on ucat.UslugaCategory_id = :UslugaCategory_id
							where
								uc.UslugaCategory_id = :UslugaCategory_id
								and uc.UslugaComplex_pid is null
							order by
								uc.UslugaComplex_Name
						";
					break;

					case 'lpu':
						$query = "
							select
								 'lpu' + cast(Lpu_id as varchar(20)) as id
								,null as code
								,Lpu_Nick as name
								,'Lpu' as object
								,ucat.UslugaCategory_id
								,ucat.UslugaCategory_SysNick
								,null as UslugaComplexLevel_id
								,1 as leaf
							from
								v_Lpu with (nolock)
								inner join v_UslugaCategory ucat with (nolock) on ucat.UslugaCategory_id = :UslugaCategory_id
							" . ( !isSuperadmin() ? "where Lpu_id = :Lpu_id" : "") . "
							order by
								Lpu_Nick
						";
					break;

					case 'gost2004':
					case 'gost2011':
					case 'lpusectiontree':
					case 'Kod7':
						$query = "
							select
								 'ucom' + cast(uc.UslugaComplex_id as varchar(20)) as id
								,cast(uc.UslugaComplex_Code as varchar(50)) as code
								,uc.UslugaComplex_Name as name
								,'UslugaComplex' as object
								,ucat.UslugaCategory_id
								,ucat.UslugaCategory_SysNick
								,uc.UslugaComplexLevel_id
								,case when uc.UslugaComplexLevel_id = 1 or ucc.cnt = 0 then 1 else 0 end as leaf
							from
								v_UslugaComplex uc with (nolock)
								inner join v_UslugaCategory ucat with (nolock) on ucat.UslugaCategory_id = :UslugaCategory_id
								outer apply (
									select count(UslugaComplex_id) as cnt
									from v_UslugaComplex with (nolock)
									where UslugaComplex_pid = uc.UslugaComplex_id
										and UslugaComplexLevel_id in (5, 6)
								) ucc
							where
								uc.UslugaComplexLevel_id in (1, 4)
								and uc.UslugaCategory_id = :UslugaCategory_id
							order by
								leaf,
								uc.UslugaComplex_Code,
								uc.UslugaComplex_Name
						";
					break;

					case 'gost2011r':
						$query = "
							select
								 'ucom' + cast(uc.UslugaComplex_id as varchar(20)) as id
								,cast(uc.UslugaComplex_Code as varchar(50)) as code
								,uc.UslugaComplex_Name as name
								,'UslugaComplex' as object
								,ucat.UslugaCategory_id
								,ucat.UslugaCategory_SysNick
								,uc.UslugaComplexLevel_id
								,case when uc.UslugaComplexLevel_id = 1 or ucc.cnt = 0 then 1 else 0 end as leaf
							from
								v_UslugaComplex uc with (nolock)
								inner join v_UslugaCategory ucat with (nolock) on ucat.UslugaCategory_id = :UslugaCategory_id
								outer apply (
									select count(UslugaComplex_id) as cnt
									from v_UslugaComplex with (nolock)
									where UslugaComplex_pid = uc.UslugaComplex_id
										and UslugaComplexLevel_id in (4, 5)
								) ucc
							where
								uc.UslugaComplexLevel_id in (1, 4)
								and uc.UslugaCategory_id = :UslugaCategory_id
							order by
								leaf,
								uc.UslugaComplex_Code,
								uc.UslugaComplex_Name
						";
					break;

					case 'simple':
						$query = "
							select
								 'ucom' + cast(uc.UslugaComplex_id as varchar(20)) as id
								,cast(uc.UslugaComplex_Code as varchar(50)) as code
								,uc.UslugaComplex_Name as name
								,'UslugaComplex' as object
								,ucat.UslugaCategory_id
								,ucat.UslugaCategory_SysNick
								,uc.UslugaComplexLevel_id
								,case when ucc.cnt = 0 then 1 else 0 end as leaf
							from
								v_UslugaComplex uc with (nolock)
								inner join v_UslugaCategory ucat with (nolock) on ucat.UslugaCategory_id = :UslugaCategory_id
								outer apply (
									select count(UslugaComplex_id) as cnt
									from v_UslugaComplex with (nolock)
									where UslugaComplex_pid = uc.UslugaComplex_id
										and UslugaComplexLevel_id = 1
								) ucc
							where
								uc.UslugaComplexLevel_id = 1
								and uc.UslugaCategory_id = :UslugaCategory_id
								and uc.UslugaComplex_pid IS NULL
							order by
								leaf,
								uc.UslugaComplex_Code,
								uc.UslugaComplex_Name
						";
					break;
				}
			break;

			default:
				switch ( $data['UslugaCategory_SysNick'] ) {
					case 'gost2004':
					case 'gost2011':
					case 'lpusectiontree':
					case 'Kod7':
						$query = "
							select
								 'ucom' + cast(uc.UslugaComplex_id as varchar(20)) as id
								,cast(uc.UslugaComplex_Code as varchar(50)) as code
								,uc.UslugaComplex_Name as name
								,'UslugaComplex' as object
								,ucat.UslugaCategory_id
								,ucat.UslugaCategory_SysNick
								,uc.UslugaComplexLevel_id
								,case when ucc.cnt = 0 then 1 else 0 end as leaf
							from
								v_UslugaComplex uc with (nolock)
								inner join v_UslugaComplex ucp with (nolock) on ucp.UslugaComplex_id = uc.UslugaComplex_pid
								inner join v_UslugaCategory ucat with (nolock) on ucat.UslugaCategory_id = :UslugaCategory_id
								outer apply (
									select count(UslugaComplex_id) as cnt
									from v_UslugaComplex with (nolock)
									where UslugaComplex_pid = uc.UslugaComplex_id
										and UslugaComplexLevel_id in (5, 6)
								) ucc
							where
								ucp.UslugaComplexLevel_id = :UslugaComplexLevel_id
								and ucp.UslugaComplex_id = :UslugaComplex_id
								and uc.UslugaCategory_id = :UslugaCategory_id
							order by
								leaf,
								uc.UslugaComplex_Code,
								uc.UslugaComplex_Name
						";
					break;
					case 'gost2011r':
						$query = "
							select
								 'ucom' + cast(uc.UslugaComplex_id as varchar(20)) as id
								,cast(uc.UslugaComplex_Code as varchar(50)) as code
								,uc.UslugaComplex_Name as name
								,'UslugaComplex' as object
								,ucat.UslugaCategory_id
								,ucat.UslugaCategory_SysNick
								,uc.UslugaComplexLevel_id
								,case when ucc.cnt = 0 then 1 else 0 end as leaf
							from
								v_UslugaComplex uc with (nolock)
								inner join v_UslugaComplex ucp with (nolock) on ucp.UslugaComplex_id = uc.UslugaComplex_pid
								inner join v_UslugaCategory ucat with (nolock) on ucat.UslugaCategory_id = :UslugaCategory_id
								outer apply (
									select count(UslugaComplex_id) as cnt
									from v_UslugaComplex with (nolock)
									where UslugaComplex_pid = uc.UslugaComplex_id
										and UslugaComplexLevel_id in (4, 5)
								) ucc
							where
								ucp.UslugaComplexLevel_id = :UslugaComplexLevel_id
								and ucp.UslugaComplex_id = :UslugaComplex_id
								and uc.UslugaCategory_id = :UslugaCategory_id
							order by
								leaf,
								uc.UslugaComplex_Code,
								uc.UslugaComplex_Name
						";
					break;
					case 'simple':
						$query = "
							select
								 'ucom' + cast(uc.UslugaComplex_id as varchar(20)) as id
								,cast(uc.UslugaComplex_Code as varchar(50)) as code
								,uc.UslugaComplex_Name as name
								,'UslugaComplex' as object
								,ucat.UslugaCategory_id
								,ucat.UslugaCategory_SysNick
								,uc.UslugaComplexLevel_id
								,case when ucc.cnt = 0 then 1 else 0 end as leaf
							from
								v_UslugaComplex uc with (nolock)
								inner join v_UslugaCategory ucat with (nolock) on ucat.UslugaCategory_id = :UslugaCategory_id
								outer apply (
									select count(UslugaComplex_id) as cnt
									from v_UslugaComplex with (nolock)
									where UslugaComplex_pid = uc.UslugaComplex_id
										and UslugaComplexLevel_id = 1
								) ucc
							where
								uc.UslugaComplexLevel_id = 1
								and uc.UslugaCategory_id = :UslugaCategory_id
								and uc.UslugaComplex_pid = :UslugaComplex_id
							order by
								leaf,
								uc.UslugaComplex_Code,
								uc.UslugaComplex_Name
						";
					break;
				}
			break;
		}

		//echo getDebugSql($query, $queryParams); exit();

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	*  Получение состава комплексной услуги для дерева
	*/
	function loadUslugaContentsTree($data) {
	
		$query = "
			select
				-- select
				 ucc.UslugaComplexComposition_id
				,uc.UslugaComplex_id
				,uc.Lpu_id
				,ucat.UslugaCategory_id
				,ucat.UslugaCategory_Name
				,ucat.UslugaCategory_SysNick
				,uc.UslugaComplex_Code
				,ISNULL(uc.UslugaComplex_Code,'') + ' ' + ISNULL(uc.UslugaComplex_Name,'') as UslugaComplex_Name
				,ISNULL(l.Lpu_Nick, '') as Lpu_Name
				,1 as RecordStatus_Code
				,UCCCount.count as CompositionCount
				,case when UCCCount.count > 0 then 0 else 1 end as leaf
				-- end select
			from
				-- from
				v_UslugaComplexComposition ucc with (nolock)
				inner join v_UslugaComplex uc with (nolock) on uc.UslugaComplex_id = ucc.UslugaComplex_id
				inner join v_UslugaCategory ucat with (nolock) on ucat.UslugaCategory_id = uc.UslugaCategory_id
				left join v_Lpu l with (nolock) on l.Lpu_id = uc.Lpu_id
				outer apply(
					select count(UslugaComplexComposition_id) as count from v_UslugaComplexComposition ucc with (nolock) where ucc.UslugaComplex_pid = uc.UslugaComplex_id
				) UCCCount
				-- end from
			where
				-- where
				ucc.UslugaComplex_pid = :UslugaComplex_pid
				-- end where
			order by
				-- order by
				uc.UslugaComplex_Code
				-- end order by
		";
			
		$queryParams = array(
			'UslugaComplex_pid' => $data['UslugaComplex_pid']
		);

		$response = array();
		
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$response = $result->result('array');
		}
		else {
			return false;
		}
			
		return $response;
	}
	
	/**
	*  Получение состава комплексной услуги на службе для дерева
	*/
	function loadUslugaComplexMedServiceTree($data) {
	
		$query = "
			select
				-- select
				 ucm.UslugaComplexMedService_id
				,uc.UslugaComplex_id
				,uc.Lpu_id
				,ucat.UslugaCategory_id
				,ucat.UslugaCategory_Name
				,ucat.UslugaCategory_SysNick
				,uc.UslugaComplex_Code
				,ISNULL(uc.UslugaComplex_Code,'') + ' ' + ISNULL(uc.UslugaComplex_Name,'') as UslugaComplex_Name
				,ISNULL(l.Lpu_Nick, '') as Lpu_Name
				,1 as RecordStatus_Code
				,UCCCount.count as CompositionCount
				,case when UCCCount.count > 0 then 0 else 1 end as leaf
				-- end select
			from
				-- from
				v_UslugaComplexMedService ucm with (nolock)
				inner join v_UslugaComplex uc with (nolock) on uc.UslugaComplex_id = ucm.UslugaComplex_id
				inner join v_UslugaCategory ucat with (nolock) on ucat.UslugaCategory_id = uc.UslugaCategory_id
				left join v_Lpu l with (nolock) on l.Lpu_id = uc.Lpu_id
				outer apply(
					select count(UslugaComplexMedService_id) as count from v_UslugaComplexMedService (nolock) where UslugaComplexMedService_pid = ucm.UslugaComplexMedService_id
				) UCCCount
				-- end from
			where
				-- where
				ucm.UslugaComplexMedService_pid = :UslugaComplexMedService_pid
				-- end where
			order by
				-- order by
				uc.UslugaComplex_Code
				-- end order by
		";
			
		$queryParams = array(
			'UslugaComplexMedService_pid' => $data['UslugaComplexMedService_pid']
		);

		$response = array();
		
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$response = $result->result('array');
		}
		else {
			return false;
		}
			
		return $response;
	}
	
	/**
	*  Получение состава комплексной услуги
	*/
	function loadUslugaContentsGrid($data) {
		$filters = "";
		
		$queryParams = array(
			 'Lpu_uid' => $data['Lpu_uid']
			,'UslugaCategory_id' => $data['UslugaCategory_id']
			,'UslugaComplex_pid' => $data['UslugaComplex_pid']
		);
		
		if (!empty($data['UslugaComplex_CodeName'])) {
			$filters .= " AND ISNULL(uc.UslugaComplex_Code,'') + ' ' + ISNULL(uc.UslugaComplex_Name,'') LIKE '%' + :UslugaComplex_CodeName + '%'";
			$queryParams['UslugaComplex_CodeName'] = $data['UslugaComplex_CodeName'];
		}

		if (!empty($data['isClose'])){
			if ($data['isClose'] == 1){ // открытые
				$filters .= " and (@curdate < uc.UslugaComplex_endDT or uc.UslugaComplex_endDT IS NULL)";
			} elseif ($data['isClose'] == 2){ // закрытые
				$filters .= " and (@curdate >= uc.UslugaComplex_endDT)";
			}
		}
		
		$data['UslugaCategory_SysNick'] = $this->getUslugaCategorySysNickById($data['UslugaCategory_id']);
		
		if ( $data['contents'] == 2 ) {
			$query = "
				-- variables
				declare @curdate datetime = dbo.tzGetDate();
				-- end variables
				
				select
					-- select
					 ucc.UslugaComplexComposition_id
					,uc.UslugaComplex_id
					,uc.Lpu_id
					,uc.UslugaComplexLevel_id
					,ucat.UslugaCategory_id
					,ucat.UslugaCategory_Name
					,ucat.UslugaCategory_SysNick
					,uc.UslugaComplex_Code
					,uc.UslugaComplex_Name
					,ISNULL(l.Lpu_Nick, '') as Lpu_Name
					,1 as RecordStatus_Code
					,convert(varchar(10), uc.UslugaComplex_begDT, 104) as UslugaComplex_begDT
					,convert(varchar(10), uc.UslugaComplex_endDT, 104) as UslugaComplex_endDT
					,UCCCount.count as CompositionCount
					-- end select
				from
					-- from
					v_UslugaComplexComposition ucc with (nolock)
					inner join v_UslugaComplex uc with (nolock) on uc.UslugaComplex_id = ucc.UslugaComplex_id
					inner join v_UslugaCategory ucat with (nolock) on ucat.UslugaCategory_id = uc.UslugaCategory_id
					left join v_Lpu l with (nolock) on l.Lpu_id = uc.Lpu_id
					outer apply(
						select count(UslugaComplexComposition_id) as count from v_UslugaComplexComposition ucc with (nolock) where ucc.UslugaComplex_pid = uc.UslugaComplex_id
					) UCCCount
					-- end from
				where
					-- where
					ucc.UslugaComplex_pid = :UslugaComplex_pid
					{$filters}
					-- end where
				order by
					-- order by
					uc.UslugaComplex_Code
					-- end order by
			";
		}
		else {
			$query = "
				-- variables
				declare @curdate datetime = dbo.tzGetDate();
				-- end variables
				
				select
					-- select
					 null as UslugaComplexComposition_id
					,uc.UslugaComplex_id
					,uc.Lpu_id
					,uc.UslugaComplexLevel_id
					,ucat.UslugaCategory_id
					,ucat.UslugaCategory_Name
					,ucat.UslugaCategory_SysNick
					,uc.UslugaComplex_Code
					,uc.UslugaComplex_Name
					,convert(varchar(10), uc.UslugaComplex_begDT, 104) as UslugaComplex_begDT
					,convert(varchar(10), uc.UslugaComplex_endDT, 104) as UslugaComplex_endDT
					,ISNULL(l.Lpu_Nick, '') as Lpu_Name
					,1 as RecordStatus_Code
					,UCCCount.count as CompositionCount
					-- end select
				from
					-- from
					v_UslugaComplex uc with (nolock)
					inner join v_UslugaCategory ucat with (nolock) on ucat.UslugaCategory_id = uc.UslugaCategory_id
					left join v_Lpu l with (nolock) on l.Lpu_id = uc.Lpu_id
					outer apply(
						select count(UslugaComplexComposition_id) as count from v_UslugaComplexComposition ucc with (nolock) where ucc.UslugaComplex_pid = uc.UslugaComplex_id
					) UCCCount
					-- end from
				where
					-- where
					uc.UslugaCategory_id = :UslugaCategory_id
					" . (in_array($data['UslugaCategory_SysNick'],array('pskov_foms', 'tfoms')) ? "and uc.UslugaComplex_pid = :UslugaComplex_pid" : "") . "
					" . (in_array($data['UslugaCategory_SysNick'], array('gost2004', 'gost2011', 'Kod7', 'lpusectiontree')) ? "and (uc.UslugaComplex_pid = :UslugaComplex_pid or UslugaComplex_pid in (select UslugaComplex_id from v_UslugaComplex with (nolock) where UslugaComplex_pid = :UslugaComplex_pid)" . ($this->regionNick == 'ufa' ? " or UslugaComplex_pid in (select t2.UslugaComplex_id from v_UslugaComplex t1 with (nolock) inner join v_UslugaComplex t2 with (nolock) on t2.UslugaComplex_pid = t1.UslugaComplex_id where t1.UslugaComplex_pid = :UslugaComplex_pid)" : "") . ")" : "") . "
					" . (in_array($data['UslugaCategory_SysNick'], array('gost2011r')) ? "and uc.UslugaComplex_pid = :UslugaComplex_pid /*and not exists (select top 1 UslugaComplex_id from v_UslugaComplex with (nolock) where UslugaComplex_pid = uc.UslugaComplex_id)*/" : "") . "
					" . (in_array($data['UslugaCategory_SysNick'], array('simple')) ? "and (ISNULL(uc.UslugaComplex_pid,0) = ISNULL(:UslugaComplex_pid,0))" : "") . "
					" . ($data['UslugaCategory_SysNick'] == 'lpu' ? "and uc.Lpu_id = :Lpu_uid" : "") . "
					{$filters}
					-- end where
				order by
					-- order by
					uc.UslugaComplex_Code
					-- end order by
			";
		}

		$response = array();

		if ( $data['paging'] == 2 ) {
			if ( $data['start'] >= 0 && $data['limit'] >= 0 ) {
				$limit_query = getLimitSQLPH($query, $data['start'], $data['limit']);
				$result = $this->db->query($limit_query, $queryParams);
			}
			else {
				$result = $this->db->query($query, $queryParams);
			}

			if ( is_object($result) ) {
				$res = $result->result('array');

				if ( is_array($res) ) {
					if ( $data['start'] == 0 && count($res) < $data['limit'] ) {
						$response['data'] = $res;
						$response['totalCount'] = count($res);
					}
					else {
						$response['data'] = $res;
						$get_count_query = getCountSQLPH($query);
						$get_count_result = $this->db->query($get_count_query, $queryParams);

						if ( is_object($get_count_result) ) {
							$count = $get_count_result->result('array');
							$response['totalCount'] = $count[0]['cnt'];
						}
						else {
							return false;
						}
					}
				}
				else {
					return false;
				}
			}
			else {
				return false;
			}
		}
		else {
			//echo getDebugSql($query, $queryParams); die();
			$result = $this->db->query($query, $queryParams);

			if ( is_object($result) ) {
				$response = $result->result('array');
			}
			else {
				return false;
			}
		}

		return $response;
	}

	/**
	 *	Сохранение комплексной услуги
	 */
	function saveUslugaComplex($data) {
		$response = array(
			 'UslugaComplex_id' => null
			,'Error_Code' => null
			,'Error_Msg' => null
		);

		$action = (!empty($data['UslugaComplex_id']) ? 'edit' : 'add');

		$this->beginTransaction();

		$additionalParams = "";
		$data['UslugaCategory_SysNick'] = $this->getUslugaCategorySysNickById($data['UslugaCategory_id']);
		
		if ( !empty($data['UslugaComplex_id']) ) {
			$query = "
				select top 1
					 uc.Server_id
					,uc.Lpu_id
					,uc.LpuSection_id
					,uc.Usluga_id
					,uc.RefValues_id
					,uc.UslugaGost_id
					,uc.UslugaComplex_BeamLoad
					,uc.UslugaComplex_Cost
					,uc.UslugaComplex_DailyLimit
					,uc.XmlTemplateSeparator_id
					,uc.UslugaComplex_isGenXml
					,uc.UslugaComplex_isAutoSum
					,uc.LpuSectionProfile_id
					,uc.UslugaComplexLevel_id
					,uc.UslugaComplex_SysNick
					,uc.UslugaComplex_TFOMSid
					,uc.UslugaComplex_2004id
					,uc.UslugaComplex_2011id
					,uc.UslugaComplex_slprofid
					,uc.UslugaComplex_llprofid
					,uc.UslugaKind_id
					,uc.Report_id
					,uc.Region_id
					,ucat.UslugaCategory_SysNick
				from
					v_UslugaComplex uc with (nolock)
					left join v_UslugaCategory ucat with (nolock) on ucat.UslugaCategory_id = uc.UslugaCategory_id
				where
					uc.UslugaComplex_id = :UslugaComplex_id
			";

			$queryParams = array(
				'UslugaComplex_id' => $data['UslugaComplex_id']
			);

			$result = $this->db->query($query, $queryParams);

			if ( !is_object($result) ) {
				$this->rollbackTransaction();
				$response['Error_Msg'] = 'Ошибка при выполнении запроса к базе данных (получение дополнительных сведений об услуге)';
				return $response;
			}

			$queryResponse = $result->result('array');

			if ( !is_array($queryResponse) || count($queryResponse) == 0 ) {
				$this->rollbackTransaction();
				$response['Error_Msg'] = 'Ошибка при получении дополнительных сведений об услуге';
				return $response;
			}

			$data = array_merge($data, $queryResponse[0]);

			$additionalParams .= "@Region_id = :Region_id, ";
		} else {
			// добавлять услугу
			switch (true) {
				case !empty($data['UslugaComplex_isPackage']):
					if ( !in_array($data['UslugaCategory_SysNick'], array('lpu')) ) {
						$this->rollbackTransaction();
						$response['Error_Msg'] = 'Пакет услуг может быть создан только в категории "Услуги ЛПУ".';
						return $response;
					}
					$data['UslugaComplexLevel_id'] = 9; // Пакет услуг
					break;
				case in_array($data['UslugaCategory_SysNick'], array('gost2004', 'gost2011', 'lpusectiontree')):
					$data['UslugaComplexLevel_id'] = 7; // Услуги ГОСТ - уровень 4
					break;
				default:
					$data['UslugaComplexLevel_id'] = 3; // Простая услуга
					break;

			}
		}
		
		if ( !isSuperAdmin() && !empty($data['UslugaComplex_id']) && !empty($data['UslugaCategory_SysNick']) && !in_array($data['UslugaCategory_SysNick'], array('lpu')) ) {
			$this->rollbackTransaction();
			$response['Error_Msg'] = 'Вам разрешено сохранять только услуги категории "Услуги ЛПУ".';
			return $response;
		}

		$this->_parentUslugaComplexLevel_id = $data['UslugaComplexLevel_id'];
		$this->_parentUslugaCategory_id = $data['UslugaCategory_id'];
		$this->_commonUslugaCategory_id = null;
		if (9 == $data['UslugaComplexLevel_id']) {
			// блокируются элементы: код подстановки в шаблон, шаблон, связанные услуги, атрибуты, тарифы
			$data['UslugaComplex_ACode'] = null;
			$data['XmlTemplate_id'] = null;
			$data['uslugaComplexAttributeData'] = null;
			$data['uslugaComplexProfileData'] = null;
			$data['linkedUslugaComplexData'] = null;
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @Res = :UslugaComplex_id;

			exec p_UslugaComplex_" . (!empty($data['UslugaComplex_id']) ? "upd" : "ins") . "
				@Server_id = :Server_id,
				@UslugaComplex_id = @Res output,
				@UslugaComplex_pid = :UslugaComplex_pid,
				@Lpu_id = :Lpu_id,
				@LpuSection_id = :LpuSection_id,
				@UslugaComplex_ACode = :UslugaComplex_ACode,
				@UslugaComplex_Code = :UslugaComplex_Code,
				@UslugaComplex_Name = :UslugaComplex_Name,
				@Usluga_id = :Usluga_id,
				@RefValues_id = :RefValues_id,
				@XmlTemplate_id = :XmlTemplate_id,
				@UslugaGost_id = :UslugaGost_id,
				@UslugaComplex_BeamLoad = :UslugaComplex_BeamLoad,
				@UslugaComplex_UET = :UslugaComplex_UET,
				@UslugaComplex_Cost = :UslugaComplex_Cost,
				@UslugaComplex_DailyLimit = :UslugaComplex_DailyLimit,
				@XmlTemplateSeparator_id = :XmlTemplateSeparator_id,
				@UslugaComplex_isGenXml = :UslugaComplex_isGenXml,
				@UslugaComplex_isAutoSum = :UslugaComplex_isAutoSum,
				@LpuSectionProfile_id = :LpuSectionProfile_id,
				@UslugaComplex_begDT = :UslugaComplex_begDT,
				@UslugaComplex_endDT = :UslugaComplex_endDT,
				@UslugaComplexLevel_id = :UslugaComplexLevel_id,
				@UslugaComplex_SysNick = :UslugaComplex_SysNick,
				@UslugaComplex_Nick = :UslugaComplex_Nick,
				@UslugaCategory_id = :UslugaCategory_id,
				@UslugaComplex_TFOMSid = :UslugaComplex_TFOMSid,
				@UslugaComplex_2004id = :UslugaComplex_2004id,
				@UslugaComplex_2011id = :UslugaComplex_2011id,
				@UslugaComplex_slprofid = :UslugaComplex_slprofid,
				@UslugaComplex_llprofid = :UslugaComplex_llprofid,
				@UslugaKind_id = :UslugaKind_id,
				@Report_id = :Report_id,
				@pmUser_id = :pmUser_id,
				{$additionalParams}
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as UslugaComplex_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'UslugaComplex_id' => $data['UslugaComplex_id'],
			'UslugaComplex_pid' => $data['UslugaComplex_pid'],
			'Lpu_id' => $data['Lpu_id'],
			'Server_id' => $data['Server_id'],
			'UslugaComplex_ACode' => $data['UslugaComplex_ACode'],
			'UslugaComplex_Code' => $data['UslugaComplex_Code'],
			'UslugaComplex_Name' => $data['UslugaComplex_Name'],
			'UslugaComplex_UET' => $data['UslugaComplex_UET'],
			'UslugaComplex_begDT' => $data['UslugaComplex_begDate'],
			'UslugaComplex_endDT' => $data['UslugaComplex_endDate'],
			'UslugaComplex_Nick' => $data['UslugaComplex_Nick'],
			'UslugaCategory_id' => $data['UslugaCategory_id'],
			'pmUser_id' => $data['pmUser_id'],
			// Параметры, которых нет на форме редактирования услуги
			'LpuSection_id' => (!empty($data['LpuSection_id']) ? $data['LpuSection_id'] : NULL),
			'Usluga_id' => (!empty($data['Usluga_id']) ? $data['Usluga_id'] : NULL),
			'RefValues_id' => (!empty($data['RefValues_id']) ? $data['RefValues_id'] : NULL),
			'XmlTemplate_id' => (!empty($data['XmlTemplate_id']) ? $data['XmlTemplate_id'] : NULL),
			'UslugaGost_id' => (!empty($data['UslugaGost_id']) ? $data['UslugaGost_id'] : NULL),
			'UslugaComplex_BeamLoad' => (!empty($data['UslugaComplex_BeamLoad']) ? $data['UslugaComplex_BeamLoad'] : NULL),
			'UslugaComplex_Cost' => (!empty($data['UslugaComplex_Cost']) ? $data['UslugaComplex_Cost'] : NULL),
			'UslugaComplex_DailyLimit' => (!empty($data['UslugaComplex_DailyLimit']) ? $data['UslugaComplex_DailyLimit'] : NULL),
			'XmlTemplateSeparator_id' => (!empty($data['XmlTemplateSeparator_id']) ? $data['XmlTemplateSeparator_id'] : NULL),
			'UslugaComplex_isGenXml' => (!empty($data['UslugaComplex_isGenXml']) ? $data['UslugaComplex_isGenXml'] : NULL),
			'UslugaComplex_isAutoSum' => (!empty($data['UslugaComplex_isAutoSum']) ? $data['UslugaComplex_isAutoSum'] : NULL),
			'LpuSectionProfile_id' => (!empty($data['LpuSectionProfile_id']) ? $data['LpuSectionProfile_id'] : NULL),
			'UslugaComplexLevel_id' => (!empty($data['UslugaComplexLevel_id']) ? $data['UslugaComplexLevel_id'] : NULL),
			'UslugaComplex_SysNick' => (!empty($data['UslugaComplex_SysNick']) ? $data['UslugaComplex_SysNick'] : NULL),
			'UslugaComplex_TFOMSid' => (!empty($data['UslugaComplex_TFOMSid']) ? $data['UslugaComplex_TFOMSid'] : NULL),
			'UslugaComplex_2004id' => (!empty($data['UslugaComplex_2004id']) ? $data['UslugaComplex_2004id'] : NULL),
			'UslugaComplex_2011id' => (!empty($data['UslugaComplex_2011id']) ? $data['UslugaComplex_2011id'] : NULL),
			'UslugaComplex_slprofid' => (!empty($data['UslugaComplex_slprofid']) ? $data['UslugaComplex_slprofid'] : NULL),
			'UslugaComplex_llprofid' => (!empty($data['UslugaComplex_llprofid']) ? $data['UslugaComplex_llprofid'] : NULL),
			'UslugaKind_id' => (!empty($data['UslugaKind_id']) ? $data['UslugaKind_id'] : NULL),
			'Report_id' => (!empty($data['Report_id']) ? $data['Report_id'] : NULL),
			'Region_id' => (!empty($data['Region_id']) ? $data['Region_id'] : NULL)
		);

		// echo getDebugSQL($query, $queryParams); exit();

		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			$this->rollbackTransaction();
			$response['Error_Msg'] = 'Ошибка при выполнении запроса к базе данных (сохранение услуги)';
			return $response;
		}

		$queryResponse = $result->result('array');

		if ( !is_array($queryResponse) ) {
			$this->rollbackTransaction();
			$response['Error_Msg'] = 'Ошибка при сохранение услуги';
			return $response;
		}
		else if ( !empty($queryResponse[0]['Error_Msg']) ) {
			$this->rollbackTransaction();
			return $queryResponse;
		}

		$response = $queryResponse[0];
		$data['UslugaComplex_id'] = $response['UslugaComplex_id'];

		//Сохранение информации об услуге
		$infoFields = array(
			'UslugaComplexInfo_id',
			'UslugaComplexInfo_ImportantInfo',
			'UslugaComplexInfo_RecipientCat',
			'UslugaComplexInfo_DocumentUsluga',
			'UslugaComplexInfo_Limit',
			'UslugaComplexInfo_PayOrder',
			'UslugaComplexInfo_QueueType',
			'UslugaComplexInfo_ServiceOrder',
			'UslugaComplexInfo_Duration',
			'UslugaComplexInfo_Result',
		);

		$needSaveInfo = false;
		foreach($infoFields as $field) {
			if (!empty($data[$field])) {
				$needSaveInfo = true;
				break;
			}
		}

		if ($needSaveInfo) {
			$resp = $this->saveUslugaComplexInfo($data);
			if (!$this->isSuccessful($resp)) {
				$this->rollbackTransaction();
				return $resp;
			}
			$response['UslugaComplexInfo_id'] = $resp[0]['UslugaComplexInfo_id'];
		}

		// Если услуга добавляется сразу в состав другой услуги...
		if ( !empty($data['UslugaComplex_cid']) ) {
			// ... добавляем связку в UslugaComplexComposition
			$queryResponse = $this->saveUslugaComplexComposition(array(
				 'UslugaComplexComposition_id' => null
				,'UslugaComplex_id' => $response['UslugaComplex_id']
				,'UslugaComplex_pid' => $data['UslugaComplex_cid']
				,'pmUser_id' => $data['pmUser_id']
			));

			if ( !is_array($queryResponse) ) {
				$this->rollbackTransaction();
				$response['Error_Msg'] = 'Ошибка при сохранении услуги в составе комплексной услуги';
				return array($response);
			}
			else if ( !empty($queryResponse[0]['Error_Msg']) ) {
				$this->rollbackTransaction();
				return $queryResponse[0];
			}
		}

		// Обрабатываем список связанных услуг
		if ( !empty($data['linkedUslugaComplexData']) ) {
			$linkedUslugaComplexData = json_decode($data['linkedUslugaComplexData'], true);

			if ( is_array($linkedUslugaComplexData) ) {
				$linkedUslugaComplex = array(
					 'pmUser_id' => $data['pmUser_id']
					,'UslugaComplex_pid' => $response['UslugaComplex_id']
				);

				// Сначала производим удаление связей между услугами
				for ( $i = 0; $i < count($linkedUslugaComplexData); $i++ ) {
					if ( empty($linkedUslugaComplexData[$i]['UslugaComplex_id']) || !is_numeric($linkedUslugaComplexData[$i]['UslugaComplex_id']) ) {
						continue;
					}

					if ( !isset($linkedUslugaComplexData[$i]['RecordStatus_Code']) || !is_numeric($linkedUslugaComplexData[$i]['RecordStatus_Code']) || $linkedUslugaComplexData[$i]['RecordStatus_Code'] != 3 ) {
						continue;
					}

					$linkedUslugaComplex['UslugaComplex_id'] = $linkedUslugaComplexData[$i]['UslugaComplex_id'];

					$queryResponse = $this->deleteLinkedUslugaComplex($linkedUslugaComplex);

					if ( !is_array($queryResponse) ) {
						$this->rollbackTransaction();
						$response['Error_Msg'] = 'Ошибка при удалении связи между услугами';
						return array($response);
					}
					else if ( !empty($queryResponse[0]['Error_Msg']) ) {
						$this->rollbackTransaction();
						return $queryResponse;
					}
				}

				// Затем связываем услуги
				for ( $i = 0; $i < count($linkedUslugaComplexData); $i++ ) {
					if ( empty($linkedUslugaComplexData[$i]['UslugaComplex_id']) || !is_numeric($linkedUslugaComplexData[$i]['UslugaComplex_id']) ) {
						continue;
					}

					if ( !isset($linkedUslugaComplexData[$i]['RecordStatus_Code']) || !is_numeric($linkedUslugaComplexData[$i]['RecordStatus_Code']) || $linkedUslugaComplexData[$i]['RecordStatus_Code'] != 0 ) {
						continue;
					}

					$linkedUslugaComplex['UslugaComplex_id'] = $linkedUslugaComplexData[$i]['UslugaComplex_id'];

					$queryResponse = $this->linkUslugaComplex($linkedUslugaComplex);

					if ( !is_array($queryResponse) ) {
						$this->rollbackTransaction();
						$response['Error_Msg'] = 'Ошибка при связывании услуг';
						return array($response);
					}
					else if ( !empty($queryResponse[0]['Error_Msg']) ) {
						$this->rollbackTransaction();
						return $queryResponse;
					}
				}
			}
		}

		// Обрабатываем список состава услуги
		if ( !empty($data['uslugaComplexCompositionData']) ) {
			$uslugaComplexCompositionData = json_decode($data['uslugaComplexCompositionData'], true);

			if ( is_array($uslugaComplexCompositionData) ) {
				$count = 0;

				for ( $i = 0; $i < count($uslugaComplexCompositionData); $i++ ) {
					$uslugaComplexComposition = array(
						 'pmUser_id' => $data['pmUser_id']
						,'UslugaComplex_pid' => $response['UslugaComplex_id']
					);

					if ( empty($uslugaComplexCompositionData[$i]['UslugaComplexComposition_id']) || !is_numeric($uslugaComplexCompositionData[$i]['UslugaComplexComposition_id']) ) {
						continue;
					}

					if ( !isset($uslugaComplexCompositionData[$i]['RecordStatus_Code']) || !is_numeric($uslugaComplexCompositionData[$i]['RecordStatus_Code']) || !in_array($uslugaComplexCompositionData[$i]['RecordStatus_Code'], array(0, 2, 3)) ) {
						continue;
					}

					if ( empty($uslugaComplexCompositionData[$i]['UslugaComplex_id']) || !is_numeric($uslugaComplexCompositionData[$i]['UslugaComplex_id']) ) {
						$this->rollbackTransaction();
						$response['Error_Msg'] = 'Не указана услуга, добавляемая в состав';
						return array($response);
					}

					$uslugaComplexComposition['UslugaComplexComposition_id'] = $uslugaComplexCompositionData[$i]['UslugaComplexComposition_id'];
					$uslugaComplexComposition['UslugaComplex_id'] = $uslugaComplexCompositionData[$i]['UslugaComplex_id'];

					switch ( $uslugaComplexCompositionData[$i]['RecordStatus_Code'] ) {
						case 0:
						case 2:
							$count++;
							$queryResponse = $this->saveUslugaComplexComposition($uslugaComplexComposition);
						break;

						case 3:
							$queryResponse = $this->deleteUslugaComplexComposition($uslugaComplexComposition);
						break;
					}

					if ( !is_array($queryResponse) ) {
						$this->rollbackTransaction();
						$response['Error_Msg'] = 'Ошибка при ' . ($uslugaComplexCompositionData[$i]['RecordStatus_Code'] == 3 ? 'удалении' : 'сохранении') . ' услуги из состава комплексной услуги';
						return array($response);
					}
					else if ( !empty($queryResponse[0]['Error_Msg']) ) {
						$this->rollbackTransaction();
						return $queryResponse[0];
					}
				}

				// Если были добавлены услуги в состав и до этого услуга значилась как простая, то меняем уровень услуги на комплексную
				if ( ($count > 0 && $data['UslugaComplexLevel_id'] == 3) || ($count == 0 && $data['UslugaComplexLevel_id'] == 2) ) {
					$queryResponse = $this->setUslugaComplexLevel(array(
						 'pmUser_id' => $data['pmUser_id']
						,'UslugaComplex_id' => $response['UslugaComplex_id']
						,'UslugaComplexLevel_id' => ($count > 0 ? 2 : 3)
					));

					if ( !is_array($queryResponse) || count($queryResponse) == 0 ) {
						$this->rollbackTransaction();
						$response['Error_Msg'] = 'Ошибка при обновлении уровня услуги';
						return $response;
					}
					else if ( !empty($queryResponse[0]['Error_Msg']) ) {
						$this->rollbackTransaction();
						return $queryResponse[0];
					}
				}

				if ('syslabprofile' == $data['UslugaCategory_SysNick']) {
					// обновляем связи со стандартами лечения
					$this->load->model('CureStandartUslugaComplexLink_model');
					try {
						$this->CureStandartUslugaComplexLink_model->isAllowTransaction = false;
						$this->CureStandartUslugaComplexLink_model->updateLinks(array(
							'session' => $data['session'],
							'pmUser_id' => $data['pmUser_id'],
							'UslugaComplex_sysprid' => $response['UslugaComplex_id']
						));
					} catch (Exception $e) {
						$this->rollbackTransaction();
						$response['Error_Msg'] = $e->getMessage();
						return $response;
					}
				}
			}
		}

		// Обрабатываем список атрибутов
		if ( !empty($data['uslugaComplexAttributeData']) ) {
			$uslugaComplexAttributeData = json_decode($data['uslugaComplexAttributeData'], true);

			if ( is_array($uslugaComplexAttributeData) ) {
				for ( $i = 0; $i < count($uslugaComplexAttributeData); $i++ ) {
					$uslugaComplexAttribute = array(
						 'pmUser_id' => $data['pmUser_id']
						,'UslugaComplex_id' => $response['UslugaComplex_id']
					);

					if ( empty($uslugaComplexAttributeData[$i]['UslugaComplexAttribute_id']) || !is_numeric($uslugaComplexAttributeData[$i]['UslugaComplexAttribute_id']) ) {
						continue;
					}

					if ( empty($uslugaComplexAttributeData[$i]['UslugaComplexAttributeType_id']) || !is_numeric($uslugaComplexAttributeData[$i]['UslugaComplexAttributeType_id']) ) {
						continue;
					}

					if ( !isset($uslugaComplexAttributeData[$i]['RecordStatus_Code']) || !is_numeric($uslugaComplexAttributeData[$i]['RecordStatus_Code']) || !in_array($uslugaComplexAttributeData[$i]['RecordStatus_Code'], array(0, 2, 3)) ) {
						continue;
					}

					$uslugaComplexAttribute['UslugaComplexAttribute_id'] = $uslugaComplexAttributeData[$i]['UslugaComplexAttribute_id'];
					$uslugaComplexAttribute['UslugaComplexAttributeType_id'] = $uslugaComplexAttributeData[$i]['UslugaComplexAttributeType_id'];
					$uslugaComplexAttribute['UslugaComplexAttribute_Float'] = $uslugaComplexAttributeData[$i]['UslugaComplexAttribute_Float'];
					$uslugaComplexAttribute['UslugaComplexAttribute_Int'] = $uslugaComplexAttributeData[$i]['UslugaComplexAttribute_Int'];
					$uslugaComplexAttribute['UslugaComplexAttribute_Text'] = $uslugaComplexAttributeData[$i]['UslugaComplexAttribute_Text'];
					$uslugaComplexAttribute['UslugaComplexAttribute_DBTableID'] = $uslugaComplexAttributeData[$i]['UslugaComplexAttribute_DBTableID'];
					$uslugaComplexAttribute['UslugaComplexAttribute_begDate'] = ConvertDateFormat($uslugaComplexAttributeData[$i]['UslugaComplexAttribute_begDate']);

					if ( !empty($uslugaComplexAttributeData[$i]['UslugaComplexAttribute_endDate']) ) {
						$uslugaComplexAttribute['UslugaComplexAttribute_endDate'] = ConvertDateFormat($uslugaComplexAttributeData[$i]['UslugaComplexAttribute_endDate']);
					}
					else {
						$uslugaComplexAttribute['UslugaComplexAttribute_endDate'] = NULL;
					}

					switch ( $uslugaComplexAttributeData[$i]['RecordStatus_Code'] ) {
						case 0:
						case 2:
							$queryResponse = $this->saveUslugaComplexAttribute($uslugaComplexAttribute);
						break;

						case 3:
							$queryResponse = $this->deleteUslugaComplexAttribute($uslugaComplexAttribute);
						break;
					}

					if ( !is_array($queryResponse) ) {
						$this->rollbackTransaction();
						$response['Error_Msg'] = 'Ошибка при ' . ($uslugaComplexAttributeData[$i]['RecordStatus_Code'] == 3 ? 'удалении' : 'сохранении') . ' атрибута услуги';
						return array($response);
					}
					else if ( !empty($queryResponse[0]['Error_Msg']) ) {
						$this->rollbackTransaction();
						return $queryResponse[0];
					}
				}
			}
		}

		// Обрабатываем список профилей
		if ( !empty($data['uslugaComplexProfileData']) ) {
			$uslugaComplexProfileData = json_decode($data['uslugaComplexProfileData'], true);

			if ( is_array($uslugaComplexProfileData) ) {
				for ( $i = 0; $i < count($uslugaComplexProfileData); $i++ ) {
					$uslugaComplexProfile = array(
						 'pmUser_id' => $data['pmUser_id']
						,'UslugaComplex_id' => $response['UslugaComplex_id']
					);

					if ( empty($uslugaComplexProfileData[$i]['UslugaComplexProfile_id']) || !is_numeric($uslugaComplexProfileData[$i]['UslugaComplexProfile_id']) ) {
						continue;
					}

					if ( empty($uslugaComplexProfileData[$i]['LpuSectionProfile_id']) || !is_numeric($uslugaComplexProfileData[$i]['LpuSectionProfile_id']) ) {
						continue;
					}

					if ( empty($uslugaComplexProfileData[$i]['UslugaComplexProfile_begDate']) ) {
						$this->rollbackTransaction();
						$response['Error_Msg'] = 'Не указана дата начала (профиль услуги)';
						return array($response);
					}
					else if ( CheckDateFormat($uslugaComplexProfileData[$i]['UslugaComplexProfile_begDate']) != 0 ) {
						$this->rollbackTransaction();
						$response['Error_Msg'] = 'Неверный формат даты начала (профиль услуги)';
						return array($response);
					}

					if ( !empty($uslugaComplexProfileData[$i]['UslugaComplexProfile_endDate']) && CheckDateFormat($uslugaComplexProfileData[$i]['UslugaComplexProfile_endDate']) != 0 ) {
						$this->rollbackTransaction();
						$response['Error_Msg'] = 'Неверный формат даты окончания (профиль услуги)';
						return array($response);
					}

					if ( !isset($uslugaComplexProfileData[$i]['RecordStatus_Code']) || !is_numeric($uslugaComplexProfileData[$i]['RecordStatus_Code']) || !in_array($uslugaComplexProfileData[$i]['RecordStatus_Code'], array(0, 2, 3)) ) {
						continue;
					}

					$uslugaComplexProfile['UslugaComplexProfile_id'] = $uslugaComplexProfileData[$i]['UslugaComplexProfile_id'];
					$uslugaComplexProfile['LpuSectionProfile_id'] = $uslugaComplexProfileData[$i]['LpuSectionProfile_id'];
					$uslugaComplexProfile['UslugaComplexProfile_begDate'] = ConvertDateFormat($uslugaComplexProfileData[$i]['UslugaComplexProfile_begDate']);

					if ( !empty($uslugaComplexProfileData[$i]['UslugaComplexProfile_endDate']) ) {
						$uslugaComplexProfile['UslugaComplexProfile_endDate'] = ConvertDateFormat($uslugaComplexProfileData[$i]['UslugaComplexProfile_endDate']);
					}
					else {
						$uslugaComplexProfile['UslugaComplexProfile_endDate'] = NULL;
					}

					switch ( $uslugaComplexProfileData[$i]['RecordStatus_Code'] ) {
						case 0:
						case 2:
							$queryResponse = $this->saveUslugaComplexProfile($uslugaComplexProfile);
						break;

						case 3:
							$queryResponse = $this->deleteUslugaComplexProfile($uslugaComplexProfile);
						break;
					}

					if ( !is_array($queryResponse) ) {
						$this->rollbackTransaction();
						$response['Error_Msg'] = 'Ошибка при ' . ($uslugaComplexProfileData[$i]['RecordStatus_Code'] == 3 ? 'удалении' : 'сохранении') . ' профиля услуги';
						return array($response);
					}
					else if ( !empty($queryResponse[0]['Error_Msg']) ) {
						$this->rollbackTransaction();
						return $queryResponse[0];
					}
				}
			}
		}

		// Обрабатываем список мест оказания услуги
		if ( !empty($data['uslugaComplexPlaceData']) ) {
			$uslugaComplexPlaceData = json_decode($data['uslugaComplexPlaceData'], true);

			if ( is_array($uslugaComplexPlaceData) ) {
				for ( $i = 0; $i < count($uslugaComplexPlaceData); $i++ ) {
					$uslugaComplexPlace = array(
						 'pmUser_id' => $data['pmUser_id']
						,'UslugaComplex_id' => $response['UslugaComplex_id']
					);

					if ( empty($uslugaComplexPlaceData[$i]['UslugaComplexPlace_id']) || !is_numeric($uslugaComplexPlaceData[$i]['UslugaComplexPlace_id']) ) {
						continue;
					}

					if ( empty($uslugaComplexPlaceData[$i]['Lpu_id']) || !is_numeric($uslugaComplexPlaceData[$i]['Lpu_id']) ) {
						$this->rollbackTransaction();
						$response['Error_Msg'] = 'Не указано ЛПУ (место оказания услуги)';
						return array($response);
					}

					if ( empty($uslugaComplexPlaceData[$i]['UslugaComplexPlace_begDate']) ) {
						$this->rollbackTransaction();
						$response['Error_Msg'] = 'Не указана дата начала (место оказания услуги)';
						return array($response);
					}
					else if ( CheckDateFormat($uslugaComplexPlaceData[$i]['UslugaComplexPlace_begDate']) != 0 ) {
						$this->rollbackTransaction();
						$response['Error_Msg'] = 'Неверный формат даты начала (место оказания услуги)';
						return array($response);
					}

					if ( !empty($uslugaComplexPlaceData[$i]['UslugaComplexPlace_endDate']) && CheckDateFormat($uslugaComplexPlaceData[$i]['UslugaComplexPlace_endDate']) != 0 ) {
						$this->rollbackTransaction();
						$response['Error_Msg'] = 'Неверный формат даты окончания (место оказания услуги)';
						return array($response);
					}

					if ( !isset($uslugaComplexPlaceData[$i]['RecordStatus_Code']) || !is_numeric($uslugaComplexPlaceData[$i]['RecordStatus_Code']) || !in_array($uslugaComplexPlaceData[$i]['RecordStatus_Code'], array(0, 2, 3)) ) {
						continue;
					}

					$uslugaComplexPlace['UslugaComplexPlace_id'] = $uslugaComplexPlaceData[$i]['UslugaComplexPlace_id'];
					$uslugaComplexPlace['Lpu_id'] = $uslugaComplexPlaceData[$i]['Lpu_id'];
					$uslugaComplexPlace['LpuBuilding_id'] = $uslugaComplexPlaceData[$i]['LpuBuilding_id'];
					$uslugaComplexPlace['LpuUnit_id'] = $uslugaComplexPlaceData[$i]['LpuUnit_id'];
					$uslugaComplexPlace['LpuSection_id'] = $uslugaComplexPlaceData[$i]['LpuSection_id'];
					$uslugaComplexPlace['UslugaComplexPlace_begDate'] = ConvertDateFormat($uslugaComplexPlaceData[$i]['UslugaComplexPlace_begDate']);

					if ( !empty($uslugaComplexPlaceData[$i]['UslugaComplexPlace_endDate']) ) {
						$uslugaComplexPlace['UslugaComplexPlace_endDate'] = ConvertDateFormat($uslugaComplexPlaceData[$i]['UslugaComplexPlace_endDate']);
					}
					else {
						$uslugaComplexPlace['UslugaComplexPlace_endDate'] = NULL;
					}

					if ( in_array($uslugaComplexPlaceData[$i]['RecordStatus_Code'], array(0, 2)) ) {
						// Проверяем даты услуги и даты мест оказания услуги
						// https://redmine.swan.perm.ru/issues/35896
						$compareResult = swCompareDates($data['UslugaComplex_begDate'], $uslugaComplexPlace['UslugaComplexPlace_begDate']);
						if ( $compareResult[0] == -1 ) {
							$this->rollbackTransaction();
							$response['Error_Msg'] = 'Дата начала действия места оказания услуги меньше даты начала действия услуги';
							return array($response);
						}

						if ( !empty($uslugaComplexPlace['UslugaComplexPlace_endDate']) ) {
							$compareResult = swCompareDates($data['UslugaComplex_begDate'], $uslugaComplexPlace['UslugaComplexPlace_endDate']);
							if ( $compareResult[0] == -1 ) {
								$this->rollbackTransaction();
								$response['Error_Msg'] = 'Дата окончания действия места оказания услуги меньше даты начала действия услуги';
								return array($response);
							}
						}

						if ( !empty($data['UslugaComplex_endDate']) ) {
							$compareResult = swCompareDates($uslugaComplexPlace['UslugaComplexPlace_begDate'], $data['UslugaComplex_endDate']);
							if ( $compareResult[0] == -1 ) {
								$this->rollbackTransaction();
								$response['Error_Msg'] = 'Дата начала действия места оказания услуги больше даты окончания действия услуги';
								return array($response);
							}

							if ( !empty($uslugaComplexPlace['UslugaComplexPlace_endDate']) ) {
								$compareResult = swCompareDates($uslugaComplexPlace['UslugaComplexPlace_endDate'], $data['UslugaComplex_endDate']);
								if ( $compareResult[0] == -1 ) {
									$this->rollbackTransaction();
									$response['Error_Msg'] = 'Дата окончания действия места оказания услуги больше даты окончания действия услуги';
									return array($response);
								}
							}
						}
					}

					switch ( $uslugaComplexPlaceData[$i]['RecordStatus_Code'] ) {
						case 0:
						case 2:
							$queryResponse = $this->saveUslugaComplexPlace($uslugaComplexPlace);
						break;

						case 3:
							$queryResponse = $this->deleteUslugaComplexPlace($uslugaComplexPlace);
						break;
					}

					if ( !is_array($queryResponse) ) {
						$this->rollbackTransaction();
						$response['Error_Msg'] = 'Ошибка при ' . ($uslugaComplexPlaceData[$i]['RecordStatus_Code'] == 3 ? 'удалении' : 'сохранении') . ' места оказания услуги';
						return array($response);
					}
					else if ( !empty($queryResponse[0]['Error_Msg']) ) {
						$this->rollbackTransaction();
						return $queryResponse[0];
					}
				}
			}
		}
		else if ( $action == 'edit' ) {
			$query = "
				select
					 count(case when UslugaComplexPlace_begDT is not null and UslugaComplexPlace_begDT < :UslugaComplex_begDate then UslugaComplexPlace_id else null end) as begDTCount1
					,count(case when UslugaComplexPlace_endDT is not null and UslugaComplexPlace_endDT < :UslugaComplex_begDate then UslugaComplexPlace_id else null end) as endDTCount1
					,count(case when :UslugaComplex_endDate is not null and UslugaComplexPlace_begDT is not null and UslugaComplexPlace_begDT > :UslugaComplex_endDate then UslugaComplexPlace_id else null end) as begDTCount2
					,count(case when :UslugaComplex_endDate is not null and UslugaComplexPlace_endDT is not null and UslugaComplexPlace_endDT > :UslugaComplex_endDate then UslugaComplexPlace_id else null end) as endDTCount2
				from v_UslugaComplexPlace with (nolock)
				where UslugaComplex_id = :UslugaComplex_id
			";
			$result = $this->db->query($query, array(
				 'UslugaComplex_id' => $data['UslugaComplex_id']
				,'UslugaComplex_begDate' => $data['UslugaComplex_begDate']
				,'UslugaComplex_endDate' => $data['UslugaComplex_endDate']
			));

			if ( !is_object($result) ) {
				$this->rollbackTransaction();
				$response['Error_Msg'] = 'Ошибка при выполнении запроса к базе данных (сравнение дат действия услуги и мест оказания услуги)';
				return $response;
			}

			$queryResponse = $result->result('array');

			if ( !is_array($queryResponse) ) {
				$this->rollbackTransaction();
				$response['Error_Msg'] = 'Ошибка при сравнении дат действия услуги и мест оказания услуги';
				return $response;
			}
			else if ( !empty($queryResponse[0]['begDTCount1']) ) {
				$this->rollbackTransaction();
				$response['Error_Msg'] = 'Дата начала действия места оказания услуги меньше даты начала действия услуги';
				return $response;
			}
			else if ( !empty($queryResponse[0]['endDTCount1']) ) {
				$this->rollbackTransaction();
				$response['Error_Msg'] = 'Дата окончания действия места оказания услуги меньше даты начала действия услуги';
				return $response;
			}
			else if ( !empty($queryResponse[0]['begDTCount2']) ) {
				$this->rollbackTransaction();
				$response['Error_Msg'] = 'Дата начала действия места оказания услуги больше даты окончания действия услуги';
				return $response;
			}
			else if ( !empty($queryResponse[0]['endDTCount2']) ) {
				$this->rollbackTransaction();
				$response['Error_Msg'] = 'Дата окончания действия места оказания услуги больше даты окончания действия услуги';
				return $response;
			}
		}

		// Обрабатываем список тарифов
		if ( !empty($data['uslugaComplexTariffData']) ) {
			$uslugaComplexTariffData = json_decode(toUtf($data['uslugaComplexTariffData']), true); // json_encode работает только с utf.

			if ( is_array($uslugaComplexTariffData) ) {
				for ( $i = 0; $i < count($uslugaComplexTariffData); $i++ ) {
					$uslugaComplexTariff = array(
						 'pmUser_id' => $data['pmUser_id']
						,'Server_id' => $data['Server_id']
						,'UslugaComplex_id' => $response['UslugaComplex_id']
					);

					if ( empty($uslugaComplexTariffData[$i]['UslugaComplexTariff_id']) || !is_numeric($uslugaComplexTariffData[$i]['UslugaComplexTariff_id']) ) {
						continue;
					}

					if ($uslugaComplexTariffData[$i]['UslugaComplexTariff_Tariff'] === '') { $uslugaComplexTariffData[$i]['UslugaComplexTariff_Tariff'] = null; }
					if ($uslugaComplexTariffData[$i]['UslugaComplexTariff_UED'] === '') { $uslugaComplexTariffData[$i]['UslugaComplexTariff_UED'] = null; }
					if ($uslugaComplexTariffData[$i]['UslugaComplexTariff_UEM'] === '') { $uslugaComplexTariffData[$i]['UslugaComplexTariff_UEM'] = null; }

					if (( !isset($uslugaComplexTariffData[$i]['UslugaComplexTariff_Tariff']) || !is_numeric($uslugaComplexTariffData[$i]['UslugaComplexTariff_Tariff']) )
						&& ( !isset($uslugaComplexTariffData[$i]['UslugaComplexTariff_UED']) || !is_numeric($uslugaComplexTariffData[$i]['UslugaComplexTariff_UED']) )
						&& ( !isset($uslugaComplexTariffData[$i]['UslugaComplexTariff_UEM']) || !is_numeric($uslugaComplexTariffData[$i]['UslugaComplexTariff_UEM']) ))
					{
						$this->rollbackTransaction();
						$response['Error_Msg'] = 'Не указан тариф (тариф)';
						return array($response);
					}

					if ( empty($uslugaComplexTariffData[$i]['UslugaComplexTariff_begDate']) ) {
						$this->rollbackTransaction();
						$response['Error_Msg'] = 'Не указана дата начала (тариф)';
						return array($response);
					}
					else if ( CheckDateFormat($uslugaComplexTariffData[$i]['UslugaComplexTariff_begDate']) != 0 ) {
						$this->rollbackTransaction();
						$response['Error_Msg'] = 'Неверный формат даты начала (тариф)';
						return array($response);
					}

					if ( !empty($uslugaComplexTariffData[$i]['UslugaComplexTariff_endDate']) && CheckDateFormat($uslugaComplexTariffData[$i]['UslugaComplexTariff_endDate']) != 0 ) {
						$this->rollbackTransaction();
						$response['Error_Msg'] = 'Неверный формат даты окончания (тариф)';
						return array($response);
					}

					if ( !isSuperAdmin() && (empty($uslugaComplexTariffData[$i]['Lpu_id']) || !is_numeric($uslugaComplexTariffData[$i]['Lpu_id'])) ) {
						$this->rollbackTransaction();
						$response['Error_Msg'] = 'Не указано ЛПУ (тариф)';
						return array($response);
					}

					if ( empty($uslugaComplexTariffData[$i]['UslugaComplexTariffType_id']) || !is_numeric($uslugaComplexTariffData[$i]['UslugaComplexTariffType_id']) ) {
						$this->rollbackTransaction();
						$response['Error_Msg'] = 'Не указан тип тарифа (тариф)';
						return array($response);
					}

					if ( empty($uslugaComplexTariffData[$i]['PayType_id']) || !is_numeric($uslugaComplexTariffData[$i]['PayType_id']) ) {
						$this->rollbackTransaction();
						$response['Error_Msg'] = 'Не указан вид оплаты (тариф)';
						return array($response);
					}

					if ( !isset($uslugaComplexTariffData[$i]['RecordStatus_Code']) || !is_numeric($uslugaComplexTariffData[$i]['RecordStatus_Code']) || !in_array($uslugaComplexTariffData[$i]['RecordStatus_Code'], array(0, 2, 3)) ) {
						continue;
					}

					$uslugaComplexTariff['UslugaComplexTariff_id'] = $uslugaComplexTariffData[$i]['UslugaComplexTariff_id'];
					$uslugaComplexTariff['UslugaComplexTariff_Tariff'] = $uslugaComplexTariffData[$i]['UslugaComplexTariff_Tariff'];
					$uslugaComplexTariff['UslugaComplexTariff_begDate'] = ConvertDateFormat($uslugaComplexTariffData[$i]['UslugaComplexTariff_begDate']);
					$uslugaComplexTariff['Lpu_id'] = $uslugaComplexTariffData[$i]['Lpu_id'];
					$uslugaComplexTariff['LpuBuilding_id'] = $uslugaComplexTariffData[$i]['LpuBuilding_id'];
					$uslugaComplexTariff['LpuUnit_id'] = $uslugaComplexTariffData[$i]['LpuUnit_id'];
					$uslugaComplexTariff['LpuSection_id'] = $uslugaComplexTariffData[$i]['LpuSection_id'];
					$uslugaComplexTariff['MedService_id'] = $uslugaComplexTariffData[$i]['MedService_id'];
					$uslugaComplexTariff['UslugaComplexTariffType_id'] = $uslugaComplexTariffData[$i]['UslugaComplexTariffType_id'];
					$uslugaComplexTariff['PayType_id'] = $uslugaComplexTariffData[$i]['PayType_id'];
					$uslugaComplexTariff['LpuLevel_id'] = $uslugaComplexTariffData[$i]['LpuLevel_id'];
					$uslugaComplexTariff['LpuSectionProfile_id'] = $uslugaComplexTariffData[$i]['LpuSectionProfile_id'];
					$uslugaComplexTariff['LpuUnitType_id'] = $uslugaComplexTariffData[$i]['LpuUnitType_id'];
					$uslugaComplexTariff['MesAgeGroup_id'] = $uslugaComplexTariffData[$i]['MesAgeGroup_id'];
					$uslugaComplexTariff['Sex_id'] = $uslugaComplexTariffData[$i]['Sex_id'];
					$uslugaComplexTariff['UslugaComplexTariff_UED'] = $uslugaComplexTariffData[$i]['UslugaComplexTariff_UED'];
					$uslugaComplexTariff['UslugaComplexTariff_UEM'] = $uslugaComplexTariffData[$i]['UslugaComplexTariff_UEM'];
					$uslugaComplexTariff['UslugaComplexTariff_Name'] = toAnsi($uslugaComplexTariffData[$i]['UslugaComplexTariff_Name']);
					$uslugaComplexTariff['UslugaComplexTariff_Code'] = $uslugaComplexTariffData[$i]['UslugaComplexTariff_Code'];
					
					if ( !empty($uslugaComplexTariffData[$i]['UslugaComplexTariff_endDate']) ) {
						$uslugaComplexTariff['UslugaComplexTariff_endDate'] = ConvertDateFormat($uslugaComplexTariffData[$i]['UslugaComplexTariff_endDate']);
					}
					else {
						$uslugaComplexTariff['UslugaComplexTariff_endDate'] = NULL;
					}

					if ( in_array($uslugaComplexTariffData[$i]['RecordStatus_Code'], array(0, 2)) ) {
						// Проверяем даты услуги и даты тарифов
						// https://redmine.swan.perm.ru/issues/35896
						$compareResult = swCompareDates($data['UslugaComplex_begDate'], $uslugaComplexTariff['UslugaComplexTariff_begDate']);
						if ( $compareResult[0] == -1 ) {
							$this->rollbackTransaction();
							$response['Error_Msg'] = 'Дата начала действия тарифа меньше даты начала действия услуги';
							return array($response);
						}

						if ( !empty($uslugaComplexTariff['UslugaComplexTariff_endDate']) ) {
							$compareResult = swCompareDates($data['UslugaComplex_begDate'], $uslugaComplexTariff['UslugaComplexTariff_endDate']);
							if ( $compareResult[0] == -1 ) {
								$this->rollbackTransaction();
								$response['Error_Msg'] = 'Дата окончания действия тарифа меньше даты начала действия услуги';
								return array($response);
							}
						}

						if ( !empty($data['UslugaComplex_endDate']) ) {
							$compareResult = swCompareDates($uslugaComplexTariff['UslugaComplexTariff_begDate'], $data['UslugaComplex_endDate']);
							if ( $compareResult[0] == -1 ) {
								$this->rollbackTransaction();
								$response['Error_Msg'] = 'Дата начала действия тарифа больше даты окончания действия услуги';
								return array($response);
							}

							if ( !empty($uslugaComplexTariff['UslugaComplexTariff_endDate']) ) {
								$compareResult = swCompareDates($uslugaComplexTariff['UslugaComplexTariff_endDate'], $data['UslugaComplex_endDate']);
								if ( $compareResult[0] == -1 ) {
									$this->rollbackTransaction();
									$response['Error_Msg'] = 'Дата окончания действия тарифа больше даты окончания действия услуги';
									return array($response);
								}
							}
						}
					}

					switch ( $uslugaComplexTariffData[$i]['RecordStatus_Code'] ) {
						case 0:
						case 2:
							$queryResponse = $this->saveUslugaComplexTariff($uslugaComplexTariff);
						break;

						case 3:
							$queryResponse = $this->deleteUslugaComplexTariff($uslugaComplexTariff);
						break;
					}

					if ( !is_array($queryResponse) ) {
						$this->rollbackTransaction();
						$response['Error_Msg'] = 'Ошибка при ' . ($uslugaComplexTariffData[$i]['RecordStatus_Code'] == 3 ? 'удалении' : 'сохранении') . ' тарифа';
						return array($response);
					}
					else if ( !empty($queryResponse[0]['Error_Msg']) ) {
						$this->rollbackTransaction();
						return $queryResponse[0];
					}
				}
			}
		}
		else if ( $action == 'edit' ) {
			$query = "
				select
					 count(case when UslugaComplexTariff_begDate is not null and UslugaComplexTariff_begDate < :UslugaComplex_begDate then UslugaComplexTariff_id else null end) as begDTCount1
					,count(case when UslugaComplexTariff_endDate is not null and UslugaComplexTariff_endDate < :UslugaComplex_begDate then UslugaComplexTariff_id else null end) as endDTCount1
					,count(case when :UslugaComplex_endDate is not null and UslugaComplexTariff_begDate is not null and UslugaComplexTariff_begDate > :UslugaComplex_endDate then UslugaComplexTariff_id else null end) as begDTCount2
					,count(case when :UslugaComplex_endDate is not null and UslugaComplexTariff_endDate is not null and UslugaComplexTariff_endDate > :UslugaComplex_endDate then UslugaComplexTariff_id else null end) as endDTCount2
				from v_UslugaComplexTariff with (nolock)
				where UslugaComplex_id = :UslugaComplex_id
			";
			$result = $this->db->query($query, array(
				 'UslugaComplex_id' => $data['UslugaComplex_id']
				,'UslugaComplex_begDate' => $data['UslugaComplex_begDate']
				,'UslugaComplex_endDate' => $data['UslugaComplex_endDate']
			));

			if ( !is_object($result) ) {
				$this->rollbackTransaction();
				$response['Error_Msg'] = 'Ошибка при выполнении запроса к базе данных (сравнение дат действия услуги и мест оказания услуги)';
				return $response;
			}

			$queryResponse = $result->result('array');

			if ( !is_array($queryResponse) ) {
				$this->rollbackTransaction();
				$response['Error_Msg'] = 'Ошибка при сравнении дат действия услуги и тарифа';
				return $response;
			}
			else if ( !empty($queryResponse[0]['begDTCount1']) ) {
				$this->rollbackTransaction();
				$response['Error_Msg'] = 'Дата начала действия тарифа меньше даты начала действия услуги';
				return $response;
			}
			else if ( !empty($queryResponse[0]['endDTCount1']) ) {
				$this->rollbackTransaction();
				$response['Error_Msg'] = 'Дата окончания действия тарифа меньше даты начала действия услуги';
				return $response;
			}
			else if ( !empty($queryResponse[0]['begDTCount2']) ) {
				$this->rollbackTransaction();
				$response['Error_Msg'] = 'Дата начала действиятарифа больше даты окончания действия услуги';
				return $response;
			}
			else if ( !empty($queryResponse[0]['endDTCount2']) ) {
				$this->rollbackTransaction();
				$response['Error_Msg'] = 'Дата окончания действия тарифа больше даты окончания действия услуги';
				return $response;
			}
		}

		$this->commitTransaction();

		return array($response);
	}

	/**
	 * Сохранение описания услуги
	 */
	function saveUslugaComplexInfo($data) {
		$params = array(
			'UslugaComplexInfo_id' => !empty($data['UslugaComplexInfo_id'])?$data['UslugaComplexInfo_id']:null,
			'UslugaComplex_id' => $data['UslugaComplex_id'],
			'UslugaComplexInfo_ImportantInfo' => !empty($data['UslugaComplexInfo_ImportantInfo'])?$data['UslugaComplexInfo_ImportantInfo']:null,
			'UslugaComplexInfo_RecipientCat' => !empty($data['UslugaComplexInfo_RecipientCat'])?$data['UslugaComplexInfo_RecipientCat']:null,
			'UslugaComplexInfo_DocumentUsluga' => !empty($data['UslugaComplexInfo_DocumentUsluga'])?$data['UslugaComplexInfo_DocumentUsluga']:null,
			'UslugaComplexInfo_Limit' => !empty($data['UslugaComplexInfo_Limit'])?$data['UslugaComplexInfo_Limit']:null,
			'UslugaComplexInfo_PayOrder' => !empty($data['UslugaComplexInfo_PayOrder'])?$data['UslugaComplexInfo_PayOrder']:null,
			'UslugaComplexInfo_QueueType' => !empty($data['UslugaComplexInfo_QueueType'])?$data['UslugaComplexInfo_QueueType']:null,
			'UslugaComplexInfo_ServiceOrder' => !empty($data['UslugaComplexInfo_ServiceOrder'])?$data['UslugaComplexInfo_ServiceOrder']:null,
			'UslugaComplexInfo_Duration' => !empty($data['UslugaComplexInfo_Duration'])?$data['UslugaComplexInfo_Duration']:null,
			'UslugaComplexInfo_Result' => !empty($data['UslugaComplexInfo_Result'])?$data['UslugaComplexInfo_Result']:null,
			'pmUser_id' => $data['pmUser_id'],
		);
		if (empty($params['UslugaComplexInfo_id'])) {
			$procedure = 'p_UslugaComplexInfo_ins';
		} else {
			$procedure = 'p_UslugaComplexInfo_upd';
		}
		$query = "
			declare
				@ErrMessage varchar(4000),
				@ErrCode int,
				@Res bigint = :UslugaComplexInfo_id;
			exec {$procedure}
				@UslugaComplexInfo_id = @Res output,
				@UslugaComplex_id = :UslugaComplex_id,
				@UslugaComplexInfo_ImportantInfo = :UslugaComplexInfo_ImportantInfo,
				@UslugaComplexInfo_RecipientCat = :UslugaComplexInfo_RecipientCat,
				@UslugaComplexInfo_DocumentUsluga = :UslugaComplexInfo_DocumentUsluga,
				@UslugaComplexInfo_Limit = :UslugaComplexInfo_Limit,
				@UslugaComplexInfo_PayOrder = :UslugaComplexInfo_PayOrder,
				@UslugaComplexInfo_QueueType = :UslugaComplexInfo_QueueType,
				@UslugaComplexInfo_ServiceOrder = :UslugaComplexInfo_ServiceOrder,
				@UslugaComplexInfo_Duration = :UslugaComplexInfo_Duration,
				@UslugaComplexInfo_Result = :UslugaComplexInfo_Result,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as UslugaComplexInfo_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			return $this->createError('','Ошибка при сохранении описания услуги');
		}
		return $response;
	}

	/**
	 *	Сохранение группы услуг
	 */
	function saveUslugaComplexGroup($data) {
		$response = array(
			 'UslugaComplex_id' => null
			,'Error_Code' => null
			,'Error_Msg' => null
		);

		$action = (!empty($data['UslugaComplex_id']) ? 'edit' : 'add');

		$data['UslugaCategory_SysNick'] = $this->getUslugaCategorySysNickById($data['UslugaCategory_id']);

		if ( !isSuperAdmin() && !empty($data['UslugaComplex_id']) && !empty($data['UslugaCategory_SysNick']) && !in_array($data['UslugaCategory_SysNick'], array('lpu')) ) {
			$response['Error_Msg'] = 'Вам разрешено сохранять только услуги категории "Услуги ЛПУ".';
			return $response;
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @Res = :UslugaComplex_id;

			exec p_UslugaComplex_" . (!empty($data['UslugaComplex_id']) ? "upd" : "ins") . "
				@Server_id = :Server_id,
				@UslugaComplex_id = @Res output,
				@UslugaComplex_pid = :UslugaComplex_pid,
				@Lpu_id = :Lpu_id,
				@UslugaComplex_Code = :UslugaComplex_Code,
				@UslugaComplex_Name = :UslugaComplex_Name,
				@UslugaComplex_begDT = :UslugaComplex_begDT,
				@UslugaComplex_endDT = :UslugaComplex_endDT,
				@UslugaComplexLevel_id = 1, -- группа услуг
				@UslugaComplex_Nick = :UslugaComplex_Name,
				@UslugaCategory_id = :UslugaCategory_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as UslugaComplex_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'UslugaComplex_id' => $data['UslugaComplex_id'],
			'UslugaComplex_pid' => $data['UslugaComplex_pid'],
			'Lpu_id' => $data['Lpu_id'],
			'UslugaComplex_Code' => $data['UslugaComplex_Code'],
			'UslugaComplex_Name' => $data['UslugaComplex_Name'],
			'UslugaComplex_begDT' => $data['UslugaComplex_begDate'],
			'UslugaComplex_endDT' => $data['UslugaComplex_endDate'],
			'UslugaCategory_id' => $data['UslugaCategory_id'],
			'Server_id' => $data['Server_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		// echo getDebugSQL($query, $queryParams); exit();

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}

		return false;
	}


	/**
	 *	Сохранение атрибута услуги
	 */
	function saveUslugaComplexAttribute($data) {
		$data['UslugaComplexAttribute_Value'] = null;
		if ( !empty($data['UslugaComplexAttribute_Float']) ) {
			$data['UslugaComplexAttribute_Value'] = $data['UslugaComplexAttribute_Float'];
		}
		else if ( !empty($data['UslugaComplexAttribute_Int']) ) {
			$data['UslugaComplexAttribute_Value'] = $data['UslugaComplexAttribute_Int'];
		}
		else if ( !empty($data['UslugaComplexAttribute_Text']) ) {
			$data['UslugaComplexAttribute_Value'] = $data['UslugaComplexAttribute_Text'];
		}
		else if ( !empty($data['UslugaComplexAttribute_DBTableID']) ) {
			$sprName = $this->getFirstResultFromQuery("
				select top 1
					UslugaComplexAttributeType_DBTable
				from
					v_UslugaComplexAttributeType (nolock)
				where
					UslugaComplexAttributeType_id = :UslugaComplexAttributeType_id
			", array('UslugaComplexAttributeType_id' => $data['UslugaComplexAttributeType_id']));

			if (!empty($sprName)) {
				$data['UslugaComplexAttribute_Value'] = $this->getFirstResultFromQuery("
					select top 1
						{$sprName}_Name
					from
						v_{$sprName} (nolock)
					where
						{$sprName}_id = :UslugaComplexAttribute_DBTableID
				", array('UslugaComplexAttribute_DBTableID' => $data['UslugaComplexAttribute_DBTableID']));

				if (empty($data['UslugaComplexAttribute_Value'])) {
					$data['UslugaComplexAttribute_Value'] = null;
				}
			}
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @Res = :UslugaComplexAttribute_id;

			exec p_UslugaComplexAttribute_" . (!empty($data['UslugaComplexAttribute_id']) && $data['UslugaComplexAttribute_id'] > 0 ? "upd" : "ins") . "
				@UslugaComplexAttribute_id = @Res output,
				@UslugaComplex_id = :UslugaComplex_id,
				@UslugaComplexAttributeType_id = :UslugaComplexAttributeType_id,
				@UslugaComplexAttribute_Float = :UslugaComplexAttribute_Float,
				@UslugaComplexAttribute_Int = :UslugaComplexAttribute_Int,
				@UslugaComplexAttribute_Text = :UslugaComplexAttribute_Text,
				@UslugaComplexAttribute_DBTableID = :UslugaComplexAttribute_DBTableID,
				@UslugaComplexAttribute_Value = :UslugaComplexAttribute_Value,
				@UslugaComplexAttribute_begDate = :UslugaComplexAttribute_begDate,
				@UslugaComplexAttribute_endDate = :UslugaComplexAttribute_endDate,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as UslugaComplexAttribute_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'UslugaComplexAttribute_id' => (!empty($data['UslugaComplexAttribute_id']) && $data['UslugaComplexAttribute_id'] > 0 ? $data['UslugaComplexAttribute_id'] : NULL),
			'UslugaComplex_id' => $data['UslugaComplex_id'],
			'UslugaComplexAttributeType_id' => $data['UslugaComplexAttributeType_id'],
			'UslugaComplexAttribute_Float' => $data['UslugaComplexAttribute_Float'],
			'UslugaComplexAttribute_Int' => $data['UslugaComplexAttribute_Int'],
			'UslugaComplexAttribute_Text' => $data['UslugaComplexAttribute_Text'],
			'UslugaComplexAttribute_DBTableID' => $data['UslugaComplexAttribute_DBTableID'],
			'UslugaComplexAttribute_Value' => $data['UslugaComplexAttribute_Value'],
			'UslugaComplexAttribute_begDate' => $data['UslugaComplexAttribute_begDate'],
			'UslugaComplexAttribute_endDate' => (!empty($data['UslugaComplexAttribute_endDate']) ? $data['UslugaComplexAttribute_endDate'] : NULL),
			'pmUser_id' => $data['pmUser_id']
		);

		// echo getDebugSQL($query, $queryParams); exit();

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 *	Сохранение профиля услуги
	 */
	function saveUslugaComplexProfile($data) {
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @Res = :UslugaComplexProfile_id;

			exec p_UslugaComplexProfile_" . (!empty($data['UslugaComplexProfile_id']) && $data['UslugaComplexProfile_id'] > 0 ? "upd" : "ins") . "
				@UslugaComplexProfile_id = @Res output,
				@UslugaComplex_id = :UslugaComplex_id,
				@LpuSectionProfile_id = :LpuSectionProfile_id,
				@UslugaComplexProfile_begDate = :UslugaComplexProfile_begDate,
				@UslugaComplexProfile_endDate = :UslugaComplexProfile_endDate,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as UslugaComplexAttribute_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'UslugaComplexProfile_id' => (!empty($data['UslugaComplexProfile_id']) && $data['UslugaComplexProfile_id'] > 0 ? $data['UslugaComplexProfile_id'] : NULL),
			'UslugaComplex_id' => $data['UslugaComplex_id'],
			'LpuSectionProfile_id' => $data['LpuSectionProfile_id'],
			'UslugaComplexProfile_begDate' => (!empty($data['UslugaComplexProfile_begDate']) ? $data['UslugaComplexProfile_begDate'] : NULL),
			'UslugaComplexProfile_endDate' => (!empty($data['UslugaComplexProfile_endDate']) ? $data['UslugaComplexProfile_endDate'] : NULL),
			'pmUser_id' => $data['pmUser_id']
		);

		// echo getDebugSQL($query, $queryParams); exit();

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 *	Сохранение места выполнения услуги
	 */
	function saveUslugaComplexPlace($data) {
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @Res = :UslugaComplexPlace_id;

			exec p_UslugaComplexPlace_" . (!empty($data['UslugaComplexPlace_id']) && $data['UslugaComplexPlace_id'] > 0 ? "upd" : "ins") . "
				@UslugaComplexPlace_id = @Res output,
				@UslugaComplex_id = :UslugaComplex_id,
				@Lpu_id = :Lpu_id,
				@LpuBuilding_id = :LpuBuilding_id,
				@LpuUnit_id = :LpuUnit_id,
				@LpuSection_id = :LpuSection_id,
				@UslugaComplexPlace_begDT = :UslugaComplexPlace_begDT,
				@UslugaComplexPlace_endDT = :UslugaComplexPlace_endDT,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as UslugaComplexPlace_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'UslugaComplexPlace_id' => (!empty($data['UslugaComplexPlace_id']) && $data['UslugaComplexPlace_id'] > 0 ? $data['UslugaComplexPlace_id'] : NULL),
			'UslugaComplex_id' => $data['UslugaComplex_id'],
			'Lpu_id' => $data['Lpu_id'],
			'LpuBuilding_id' => (!empty($data['LpuBuilding_id']) ? $data['LpuBuilding_id'] : NULL),
			'LpuUnit_id' => (!empty($data['LpuUnit_id']) ? $data['LpuUnit_id'] : NULL),
			'LpuSection_id' => (!empty($data['LpuSection_id']) ? $data['LpuSection_id'] : NULL),
			'UslugaComplexPlace_begDT' => $data['UslugaComplexPlace_begDate'],
			'UslugaComplexPlace_endDT' => (!empty($data['UslugaComplexPlace_endDate']) ? $data['UslugaComplexPlace_endDate'] : NULL),
			'pmUser_id' => $data['pmUser_id']
		);

		// echo getDebugSQL($query, $queryParams); return false;

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 *	Сохранение тарифа
	 */
	function saveUslugaComplexTariff($data) {
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @Res = :UslugaComplexTariff_id;

			exec p_UslugaComplexTariff_" . (!empty($data['UslugaComplexTariff_id']) && $data['UslugaComplexTariff_id'] > 0 ? "upd" : "ins") . "
				@Server_id = :Server_id,
				@UslugaComplexTariff_id = @Res output,
				@UslugaComplexTariff_Code = :UslugaComplexTariff_Code,
				@UslugaComplexTariff_Name = :UslugaComplexTariff_Name,
				@UslugaComplex_id = :UslugaComplex_id,
				@UslugaComplexTariff_Tariff = :UslugaComplexTariff_Tariff,
				@UslugaComplexTariff_begDate = :UslugaComplexTariff_begDate,
				@UslugaComplexTariff_endDate = :UslugaComplexTariff_endDate,
				@Lpu_id = :Lpu_id,
				@LpuBuilding_id = :LpuBuilding_id,
				@LpuUnit_id = :LpuUnit_id,
				@LpuSection_id = :LpuSection_id,
				@MedService_id = :MedService_id,
				@UslugaComplexTariffType_id = :UslugaComplexTariffType_id,
				@PayType_id = :PayType_id,
				@LpuLevel_id = :LpuLevel_id,
				@LpuSectionProfile_id = :LpuSectionProfile_id,
				@LpuUnitType_id = :LpuUnitType_id,
				@MesAgeGroup_id = :MesAgeGroup_id,
				@Sex_id = :Sex_id,
				@VizitClass_id = :VizitClass_id,
				@UslugaComplexTariff_UED = :UslugaComplexTariff_UED,
				@UslugaComplexTariff_UEM = :UslugaComplexTariff_UEM,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as UslugaComplexTariff_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'Server_id' => $data['Server_id'],
			'UslugaComplexTariff_id' => (!empty($data['UslugaComplexTariff_id']) && $data['UslugaComplexTariff_id'] > 0 ? $data['UslugaComplexTariff_id'] : NULL),
			'UslugaComplex_id' => $data['UslugaComplex_id'],
			'UslugaComplexTariff_Tariff' => (isset($data['UslugaComplexTariff_Tariff']) ? $data['UslugaComplexTariff_Tariff'] : NULL),
			'UslugaComplexTariff_begDate' => $data['UslugaComplexTariff_begDate'],
			'UslugaComplexTariff_endDate' => $data['UslugaComplexTariff_endDate'],
			'UslugaComplexTariff_UED' => (isset($data['UslugaComplexTariff_UED']) ? $data['UslugaComplexTariff_UED'] : NULL),
			'UslugaComplexTariff_UEM' => (isset($data['UslugaComplexTariff_UEM']) ? $data['UslugaComplexTariff_UEM'] : NULL),
			'UslugaComplexTariff_Code' => $data['UslugaComplexTariff_Code'],
			'UslugaComplexTariff_Name' => $data['UslugaComplexTariff_Name'],
			'Lpu_id' => (!empty($data['Lpu_id']) ? $data['Lpu_id'] : NULL),
			'LpuBuilding_id' => (!empty($data['LpuBuilding_id']) ? $data['LpuBuilding_id'] : NULL),
			'LpuUnit_id' => (!empty($data['LpuUnit_id']) ? $data['LpuUnit_id'] : NULL),
			'LpuSection_id' => (!empty($data['LpuSection_id']) ? $data['LpuSection_id'] : NULL),
			'MedService_id' => (!empty($data['MedService_id']) ? $data['MedService_id'] : NULL),
			'LpuLevel_id' => (!empty($data['LpuLevel_id']) ? $data['LpuLevel_id'] : NULL),
			'LpuSectionProfile_id' => (!empty($data['LpuSectionProfile_id']) ? $data['LpuSectionProfile_id'] : NULL),
			'LpuUnitType_id' => (!empty($data['LpuUnitType_id']) ? $data['LpuUnitType_id'] : NULL),
			'MesAgeGroup_id' => (!empty($data['MesAgeGroup_id']) ? $data['MesAgeGroup_id'] : NULL),
			'Sex_id' => (!empty($data['Sex_id']) ? $data['Sex_id'] : NULL),
			'VizitClass_id' => (!empty($data['VizitClass_id']) ? $data['VizitClass_id'] : NULL),
			'UslugaComplexTariffType_id' => $data['UslugaComplexTariffType_id'],
			'PayType_id' => $data['PayType_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		// echo getDebugSQL($query, $queryParams); exit();

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 *	Установка уровня услуги
	 */
	function setUslugaComplexLevel($data) {
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec p_UslugaComplex_setUslugaComplexLevel
				@UslugaComplex_id = :UslugaComplex_id,
				@UslugaComplexLevel_id = :UslugaComplexLevel_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'pmUser_id' => $data['pmUser_id']
		,'UslugaComplex_id' => $data['UslugaComplex_id']
		,'UslugaComplexLevel_id' => $data['UslugaComplexLevel_id']
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *	Получение состава услуги на службе
	 */
	function getUslugaComplexMedServiceCompositionList($data)
	{
		$query = "
			select
				ucm.UslugaComplexMedService_id,
				ucm.UslugaComplex_id,
				uc.UslugaComplex_Name,
				uc.UslugaComplex_Code
			from
				v_UslugaComplexMedService ucm with (nolock)
				inner join v_UslugaComplex uc with (nolock) on ucm.UslugaComplex_id = uc.UslugaComplex_id
				inner join lis.v_AnalyzerTest at (nolock) on at.UslugaComplexMedService_id = ucm.UslugaComplexMedService_id
				inner join lis.v_Analyzer a (nolock) on a.Analyzer_id = at.Analyzer_id 
			where
				ucm.UslugaComplexMedService_pid = :UslugaComplexMedService_pid
				and ISNULL(at.AnalyzerTest_IsNotActive, 1) = 1
				and ISNULL(a.Analyzer_IsNotActive, 1) = 1
		";

		$queryParams = array(
			'UslugaComplexMedService_pid' => $data['UslugaComplexMedService_pid']
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 *	Получение услуг в отделении/подразделении/лпу
	 */
	function loadUslugaComplexOnPlaceGrid($data) 
	{
		$filters = "";
		
		if (!empty($data['isClose']) && $data['isClose'] == 1) {
			$filters .= " and (@curdate < ucp.UslugaComplexPlace_endDT or ucp.UslugaComplexPlace_endDT IS NULL)";
		} elseif (!empty($data['isClose']) && $data['isClose'] == 2) {
			$filters .= " and (@curdate >= ucp.UslugaComplexPlace_endDT)";
		}
		
		if (!empty($data['UslugaComplex_CodeName'])) {
			$filters .= " AND ISNULL(uc.UslugaComplex_Code,'') + ' ' + ISNULL(uc.UslugaComplex_Name,'') LIKE '%' + :UslugaComplex_CodeName + '%'";
		}

		$query = "
			-- variables
			declare @curdate datetime = dbo.tzGetDate();
			-- end variables
			
			select
				-- select
				ucp.UslugaComplexPlace_id,
				uc.UslugaComplex_id,
				ucp.Lpu_id,
				ucp.LpuBuilding_id,
				ucp.LpuUnit_id,
				ucp.LpuSection_id,
				cat.UslugaCategory_Name,
				ISNULL(Lb.LpuBuilding_Name,'') + ISNULL(', '+ Lu.LpuUnit_Name,'') + ISNULL(', '+ LS.LpuSection_fullname,'') as UslugaComplexPlace_Name,
				-- ucp.UslugaComplex_id,
				uc.UslugaComplex_Code,
				uc.UslugaComplex_Name,
				convert(varchar(10), ucp.UslugaComplexPlace_begDT, 104) as UslugaComplexPlace_begDate,
				convert(varchar(10), ucp.UslugaComplexPlace_endDT, 104) as UslugaComplexPlace_endDate,
				case when isnull(tarcount.tarcount,0)=0 then 'Нет тарифа'
				when isnull(tarcount.tarcount,0)>1 then 'Несколько тарифов'
				when isnull(tarcount.tarcount,0)=1 then (
					select
						case when UslugaComplexTariff_Tariff IS not null then
						ltrim(cast(UslugaComplexTariff_Tariff AS varchar) + ' руб. ')
						else '' end +
						case
						when isnull(UslugaComplexTariff_UED,0.0)>0 then
						case when UslugaComplexTariff_Tariff IS not null then ', ' else '' end
						+ ltrim(cast(UslugaComplexTariff_UED AS varchar) + ' УЕТ врача ')
						else '' end +
						case when isnull(UslugaComplexTariff_UEM,0.0)>0 then
						case when UslugaComplexTariff_Tariff IS not null or UslugaComplexTariff_UED IS not null then ', ' else '' end
						+ltrim(cast(UslugaComplexTariff_UEM AS varchar) +' УЕТ ср. м/п ')
						else '' end
					from UslugaComplexTariff uct with (nolock)
					where 
						uct.UslugaComplexTariff_id = tarcount.UslugaComplexTariff_id
				) end as UslugaComplex_Tariff
				-- end select
			from 
				-- from
				UslugaComplexPlace ucp (nolock)
				inner join uslugacomplex uc (nolock) on uc.UslugaComplex_id=ucp.uslugacomplex_id
				inner join UslugaCategory cat (nolock) on cat.UslugaCategory_id=uc.UslugaCategory_id
				inner join v_Lpu l (nolock) on l.Lpu_id=ucp.Lpu_id
				left join v_LpuBuilding lb (nolock) on lb.LpuBuilding_id=ucp.LpuBuilding_id
				left join v_LpuUnit lu (nolock) on lu.LpuUnit_id=ucp.LpuUnit_id
				left join v_LpuSection ls (nolock) on ls.LpuSection_id=ucp.LpuSection_id
				outer apply (
					select
						COUNT (UslugaComplexTariff_id) as tarcount,
						MAX (UslugaComplexTariff_id) as UslugaComplexTariff_id
					from
						UslugaComplexTariff uct with (nolock)
					where
						uct.UslugaComplex_id=ucp.UslugaComplex_id
						and uct.PayType_id=1
						and isnull(uct.UslugaComplexTariff_endDate, @curdate) >=@curdate
						and (uct.Lpu_id = ucp.Lpu_id or uct.Lpu_id is null)
						-- в зависимости от уровня структуры
						and (uct.LpuBuilding_id =ucp.LpuBuilding_id
						or uct.LpuBuilding_id = :LpuBuilding_id
						or uct.LpuBuilding_id is null
						or :LpuBuilding_id is null)

						and (uct.LpuUnit_id =ucp.LpuUnit_id or uct.LpuUnit_id is null
						or uct.LpuUnit_id = :LpuUnit_id
						or :LpuUnit_id is null)

						and (uct.LpuSection_id =ucp.LpuSection_id or uct.LpuSection_id is null
						or uct.LpuSection_id = :LpuSection_id
						or :LpuSection_id is null)
				) tarcount
				-- end from
			where
				-- where
				--ЛПУ
				ucp.Lpu_id = :Lpu_id

				-- Подразделение
				and (ucp.LpuBuilding_id = :LpuBuilding_id or ucp.LpuBuilding_id is null or :LpuBuilding_id is null)
				-- Группа отделений
				and (ucp.LpuUnit_id = :LpuUnit_id or ucp.LpuUnit_id is null or :LpuUnit_id is null)
				-- Отделение
				and (ucp.LpuSection_id = :LpuSection_id or ucp.LpuSection_id is null or :LpuSection_id is null)
				{$filters}
				-- end where
			order by
				-- order by
				UslugaCategory_Code, UslugaComplex_Code
				-- end order by
		";
		
		// echo getDebugSql($query, $data); die();
		return $this->getPagingResponse($query, $data, $data['start'], $data['limit'], true);
	}
	
	/**
	* Загрузка услуг для комбобокса 110
	* Услуги в объеме с типом "Услуги СМП"
	*/	
	public function loadUslugaSMPCombo($data) {
		$filterList = array();
		$queryParams = array();

		if ( !empty($data['query']) ) {
			$filterList[] = "uc.UslugaComplex_Code + uc.UslugaComplex_Name like :query";
			$queryParams['query'] = '%' . $data['query'] . '%';
		}

		$query = "
			-- variables
			declare @curdate datetime = dbo.tzGetDate();
			-- end variables

			select
				-- select
				 uc.UslugaComplex_id
				,ucat.UslugaCategory_id
				,ucat.UslugaCategory_Name
				,uc.UslugaComplex_pid
				,convert(varchar(10), uc.UslugaComplex_begDT, 104) as UslugaComplex_begDT
				,convert(varchar(10), uc.UslugaComplex_endDT, 104) as UslugaComplex_endDT
				,uc.UslugaComplex_Code
				,rtrim(isnull(uc.UslugaComplex_Name, '')) as UslugaComplex_Name
				,uc.UslugaComplex_UET
				-- end select
			from
				-- from
				v_AttributeVision avis (nolock)
				inner join v_AttributeValue av (nolock) on av.AttributeVision_id = avis.AttributeVision_id
				inner join v_Attribute a (nolock) on a.Attribute_id = av.Attribute_id
				left join v_UslugaComplex uc with (nolock) on uc.UslugaComplex_id = av.AttributeValue_ValueIdent
                left join v_UslugaCategory ucat with (nolock) on ucat.UslugaCategory_id = uc.UslugaCategory_id
				-- end from
			where
				-- where
				avis.AttributeVision_TableName = 'dbo.VolumeType'
				and avis.AttributeVision_TablePKey = '89'
				and avis.AttributeVision_IsKeyValue = 2
				and ISNULL(av.AttributeValue_endDate, @curdate) >= @curdate
				and uc.UslugaComplex_id is not null
				" . (count($filterList) > 0 ? "and " . implode(" and ", $filterList) : "") . "
				-- end where
			order by
				-- order by
				uc.UslugaComplex_Code
				-- end order by
		";
		
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Поиск атрибута услуги по услуге
	 */
	function findOrCreateUslugaComplexAttribute($data) {
		$UslugaComplexAttribute_id = null;

		$query = "
			declare @UslugaComplexAttributeType_id bigint;
			set @UslugaComplexAttributeType_id = (
				select top 1 UslugaComplexAttributeType_id 
				from v_UslugaComplexAttributeType (nolock) 
				where UslugaComplexAttributeType_SysNick = :UslugaComplexAttributeType_SysNick
			);
			
			select
				uca.UslugaComplexAttribute_id
			from
				v_UslugaComplexAttribute uca (nolock)
			where
				uca.UslugaComplexAttributeType_id = @UslugaComplexAttributeType_id
				and uca.UslugaComplex_id = :UslugaComplex_id
		";

		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) > 0 && !empty($resp[0]['UslugaComplexAttribute_id'])) {
				$UslugaComplexAttribute_id = $resp[0]['UslugaComplexAttribute_id'];
			}
		}
		if (empty($UslugaComplexAttribute_id))
		{
			$query = "
				declare
					@Res bigint,
					@ErrCode int,
					@ErrMessage varchar(4000),
					@UslugaComplexAttributeType_id bigint;
					
				set @Res = null;
				set @UslugaComplexAttributeType_id = (
					select top 1 UslugaComplexAttributeType_id 
					from v_UslugaComplexAttributeType (nolock) 
					where UslugaComplexAttributeType_SysNick = :UslugaComplexAttributeType_SysNick
				);

				exec p_UslugaComplexAttribute_ins
					@UslugaComplexAttribute_id = @Res output,
					@UslugaComplex_id = :UslugaComplex_id,
					@UslugaComplexAttributeType_id = @UslugaComplexAttributeType_id,
					@UslugaComplexAttribute_Float = null,
					@UslugaComplexAttribute_Int = null,
					@UslugaComplexAttribute_Text = null,
					@UslugaComplexAttribute_Value = null,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;

				select @Res as UslugaComplexAttribute_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			$result = $this->db->query($query, $data);
			if ( is_object($result) ) {
				$resp = $result->result('array');
				if (count($resp) > 0 && !empty($resp[0]['UslugaComplexAttribute_id'])) {
					$UslugaComplexAttribute_id = $resp[0]['UslugaComplexAttribute_id'];
				}
			}
		}

		return [
			'UslugaComplexAttribute_id' => $UslugaComplexAttribute_id
		];
	}

	/**
	 * Получение состава услуги
	 */
	function getUslugaComplexComposition($data) {
		$UslugaComplexMedService_id = $this->getFirstResultFromQuery("
			select top 1 UslugaComplexMedService_id
			from v_UslugaComplexMedService (nolock)
			where UslugaComplex_id = :UslugaComplex_id and MedService_id = :MedService_id
			order by UslugaComplexMedService_id desc
		",
			array(
				'MedService_id' => $data['MedService_id'],
				'UslugaComplex_id' => $data['UslugaComplex_id']
			));

		$this->load->model('MedService_model');
		$uslugaComposition = $this->MedService_model->loadCompositionMenu(array(
			'UslugaComplexMedService_pid' => !empty($UslugaComplexMedService_id) ? $UslugaComplexMedService_id : null,
			'UslugaComplex_pid' => $data['UslugaComplex_id'],
			'Lpu_id' => $data['Lpu_id']
		));

		return $uslugaComposition;
	}
	
	function getUslugaComplexAttributeTypeSysNickById($data) {
		$query = "
			select
				UslugaComplexAttributeType_SysNick
			from 
				v_UslugaComplexAttributeType (nolock)
			where 
				UslugaComplexAttributeType_id in (:UslugaComplexAttributeType_id)
		";
		return $this->dbmodel->queryResult($query, $data);
	}

	/**
	 * Получение списка аттрибутов комплесной услуги
	 * 192492
	 */
	function getUslugaComplexAttributes($data) {
		$res =  $this->queryResult("
			select 
			 	ucat.UslugaComplexAttributeType_SysNick
			from v_UslugaComplexAttributeType ucat (nolock)
				inner join v_UslugaComplexAttribute uca (nolock) on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
			where 
				uca.UslugaComplex_id = :UslugaComplex_id  
				or uca.UslugaComplex_id in (select UslugaComplex_id from v_UslugaComplex where UslugaComplex_pid = :UslugaComplex_pid)
		", [
			'UslugaComplex_id' => $data['UslugaComplex_id'],
			'UslugaComplex_pid' => $data['UslugaComplex_id'],
		]);
		
		return $res;
	}
}
