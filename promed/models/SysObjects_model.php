<?php defined('BASEPATH') or die ('No direct script access allowed');

class SysObjects_model extends swModel {
	/**
	 * Идентификатор получаемой записи
	 */
	protected $_id = null;

	/**
	 * Таблица выборки
	 */
	protected $_table = null;

	/**
	 * Схема таблицы выборки
	 */
	protected $_schema = 'dbo';

	/**
	 * Таблица выборки
	 */
	protected $_convertDates = false;

	/**
	 * Информация о таблице выборки
	 */
	protected $_tableInfo = null;

	/**
	 * Результат выполнения запроса
	 */
	protected $_result = array();

	/**
	 * Информация о строке
	 */
	protected $_resultRow = array();

	/**
	 * Конструктор
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Сброс параметров
	 */
	public function reset() {
		$this->_id = null;
		$this->_table = null;
		$this->_schema = 'dbo';
		$this->_convertDates = false;
		$this->_tableInfo = null;
		$this->_result = array();
		$this->_resultRow = array();
		return $this;
	}

	/**
	 * Получение результата выполнения запроса
	 */
	public function result() {
		return $this->_result;
	}

	/**
	 * Признак необходимости конвертировать даты из DateTime в строку
	 */
	public function convertDates($convertDates = false) {
		$this->_convertDates = $convertDates;
		return $this;
	}

	/**
	 * Указание идентификатора записи
	 */
	public function id($id = null) {
		$this->_id = $id;
		return $this;
	}

	/**
	 * Указание схемы для таблицы выборки
	 */
	public function schema($schema = 'dbo') {
		$this->_schema = $schema;
		return $this;
	}

	/**
	 * Указание таблицы выборки
	 */
	public function table($table) {
		$this->_table = $table;
		return $this;
	}

	/**
	 * Основной метод - получение данных из таблицы
	 */
	public function processData() {
		try {
			if ( empty($this->_table) ) {
				throw new Exception('Не указана таблица выборки');
			}

			if ( empty($this->_schema) ) {
				$this->_schema = 'dbo';
			}

			$join = array();
			$params = array();
			$response = array();
			$select = array();
			$where = array();

			// Получаем список полей таблицы
			$this->_tableInfo = $this->queryResult("
				select
					c.name as column_name,
					case when c.is_identity = 1 then 1 else 0 end as isPrimaryKey,
					cast(ep.value as varchar) as column_description,
					rs.name as reference_schema,
					rt.name as reference_table,
					rv.name as reference_view,
					rc.name as reference_column,
					rcn.name as reference_name_column
				from sys.columns c with (nolock)
					inner join sys.tables t on t.object_id = c.object_id
					inner join sys.schemas s on s.schema_id = t.schema_id
					/*outer apply (
						select top 1 object_id
						from sys.key_constraints with (nolock)
						where parent_object_id = t.object_id
							and schema_id = t.schema_id
							and type = 'PK'
							and unique_index_id = c.column_id
					) pk*/
					left join sys.extended_properties ep on ep.minor_id = c.column_id
						and ep.major_id = t.object_id
						and ep.name = 'MS_Description'
					outer apply (
						select top 1 referenced_object_id, referenced_column_id
						from sys.foreign_key_columns with (nolock)
						where parent_object_id = t.object_id
							and parent_column_id = c.column_id
					) fk
					left join sys.tables rt on rt.object_id = fk.referenced_object_id
					left join sys.views rv on rv.name = 'v_' + rt.name
						and rv.schema_id = rt.schema_id
					left join sys.schemas rs on rs.schema_id = rt.schema_id
					left join sys.columns rc on rc.column_id = fk.referenced_column_id
						and rc.object_id = fk.referenced_object_id
					outer apply (
						select top 1 name
						from sys.columns with (nolock)
						where object_id = rv.object_id
							and name = rt.name + '_Name'
					) rcn
				where t.name = :table
					and s.name = :schema
				order by c.column_id
			", array(
				'table' => $this->_table,
				'schema' => $this->_schema,
			));

			if ( $this->_tableInfo === false ) {
				throw new Exception('Не удалось получить данные о таблице выборки');
			}

			// Формируем данные для запроса
			foreach ( $this->_tableInfo as $row ) {
				$this->_resultRow[$row['column_name']] = array(
					'field' => $row['column_name'],
					'isPrimaryKey' => $row['isPrimaryKey'],
					'description' => (!empty($row['column_description']) ? mb_strtoupper(mb_substr($row['column_description'], 0, 1)) . mb_substr($row['column_description'], 1) : null),
					'value' => null,
					'nameValue' => null,
				);

				$select[] = 't0.' . $row['column_name'];

				if ( $row['isPrimaryKey'] == 1 && !empty($this->_id) ) {
					$params['id'] = $this->_id;
					$where[] = "t0." . $row['column_name'] . " = :id";
				}

				if ( !empty($row['reference_view']) && !empty($row['reference_name_column']) ) {
					$i = count($join) + 1;
					$join[] = "left join {$row['reference_schema']}.{$row['reference_view']} t{$i} on t{$i}.{$row['reference_column']} = t0.{$row['column_name']}";
					$select[] = "rtrim(t{$i}.{$row['reference_name_column']}) as {$row['column_name']}_Name";
				}
				else if ( !empty($row['reference_table']) && !empty($row['reference_name_column']) ) {
					$i = count($join) + 1;
					$join[] = "left join {$row['reference_schema']}.{$row['reference_table']} t{$i} on t{$i}.{$row['reference_column']} = t0.{$row['column_name']}";
					$select[] = "rtrim(t{$i}.{$row['reference_name_column']}) as {$row['column_name']}_Name";
				}
				else if ( in_array($row['column_name'], array('pmUser_delID', 'pmUser_insID', 'pmUser_signID', 'pmUser_updID')) ) {
					$i = count($join) + 1;
					$join[] = "left join dbo.pmUserCache t{$i} on t{$i}.pmUser_id = t0.{$row['column_name']}";
					$select[] = "rtrim(t{$i}.pmUser_Login) as {$row['column_name']}_Name";
				}
			}

			$queryResult = $this->queryResult("
				select " . implode(",", $select) . "
				from {$this->_schema}.{$this->_table} t0 with (nolock)
					" . implode(" ", $join) . "
				" . (count($where) > 0 ? "where " . implode(" and ", $where) : "") . "
			", $params);

			if ( $queryResult === false || !is_array($queryResult) || count($queryResult) == 0 ) {
				throw new Exception('Ошибка при получении данных');
			}

			foreach ( $queryResult as $row ) {
				$resultRow = $this->_resultRow;

				foreach ( $row as $key => $value ) {
					if ( $this->_convertDates === true && $value instanceof DateTime ) {
						$value = $value->format('d.m.Y H:i:s');
					}

					if ( array_key_exists($key, $resultRow) ) {
						$resultRow[$key]['value'] = $value;
					}
					else {
						$tempKey = str_replace("_Name", "", $key);

						if ( array_key_exists($tempKey, $resultRow) ) {
							$resultRow[$tempKey]['nameValue'] = $value;
						}
					}
				}

				$this->_result[] = $resultRow;
			}
		}
		catch ( Exception $e ) {
			$this->_result = $e->getMessage();
		}

		return $this;
	}
}