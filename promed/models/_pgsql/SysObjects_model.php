<?php defined('BASEPATH') or die ('No direct script access allowed');

class SysObjects_model extends SwPgModel {
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
			$select = array();
			$where = array();

			// Получаем список полей таблицы
			$this->_tableInfo = $this->queryResult("
			    select
			        c.column_name as \"column_name\",
			        case when ss.contype='p' then 1 else 0 end \"isPrimaryKey\",
			        col_description((c.Table_schema||'.'||c.table_name)::regclass, ordinal_position) as \"column_description\",
			        ss.reference_schema as \"reference_schema\",
                    ss.reference_table as \"reference_table\",
                    rv.table_name as \"reference_view\",
                    ss.reference_column as \"reference_column\",
                    rcn.column_name as \"reference_name_column\"
                from 
                    INFORMATION_SCHEMA.columns c
                left join LATERAL (select contype, ap.attname column_name, af.attname reference_column,sf.nspname reference_schema, tf.relname reference_table
                FROM (select contype, connamespace,conrelid,confrelid,conkey[i] as conkey, confkey[i] as confkey,confdeltype,confupdtype , conname
                from (select contype,connamespace,conrelid,confrelid,conkey,confkey,confdeltype,confupdtype ,
                generate_series(1,array_upper(conkey,1)) as i,conname
                from pg_constraint ) ss) con
                INNER JOIN pg_namespace nsp ON con.connamespace=nsp.oid
                inner JOIN pg_class tbl ON con.conrelid=tbl.oid
                inner join pg_attribute ap on ap.attnum = conkey and ap.attrelid = conrelid
                left join pg_attribute af on af.attnum = confkey and af.attrelid = confrelid
                left join pg_class tf on tf.oid=confrelid
                left join pg_namespace sf on sf.oid=tf.relnamespace
                WHERE LOWER(nsp.nspname)=c.TABLE_SCHEMA
                AND LOWER(tbl.relname)=c.TABLE_NAME
                and ap.attnum=c.ordinal_position
                limit 1) ss on true
                left join information_schema.views rv on rv.table_schema=ss.reference_schema and rv.table_name='v_'||ss.reference_table
                left join lateral ( select column_name from information_schema.columns rc where rc.TABLE_SCHEMA =ss.reference_schema
                and rc.TABLE_NAME= ss.reference_table and rc.column_name=ss.reference_table||'_name' limit 1) rcn on true
                where c.TABLE_SCHEMA=:schema
                and c.TABLE_NAME=:table
			", array(
				'table' => strtolower($this->_table),
				'schema' => strtolower($this->_schema),
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

				$select[] = 't0.' . $row['column_name'] . " as \"{$row['column_name']}\"";

				if ( $row['isPrimaryKey'] == 1 && !empty($this->_id) ) {
					$params['id'] = $this->_id;
					$where[] = "t0." . $row['column_name'] . " = :id";
				}

				if ( !empty($row['reference_view']) && !empty($row['reference_name_column']) ) {
					$i = count($join) + 1;
					$join[] = "left join {$row['reference_schema']}.{$row['reference_view']} t{$i} on t{$i}.{$row['reference_column']} = t0.{$row['column_name']}";
					$select[] = "rtrim(t{$i}.{$row['reference_name_column']}) as \"{$row['column_name']}_Name\"";
				}
				else if ( !empty($row['reference_table']) && !empty($row['reference_name_column']) ) {
					$i = count($join) + 1;
					$join[] = "left join {$row['reference_schema']}.{$row['reference_table']} t{$i} on t{$i}.{$row['reference_column']} = t0.{$row['column_name']}";
					$select[] = "rtrim(t{$i}.{$row['reference_name_column']}) as \"{$row['column_name']}_Name\"";
				}
				else if ( in_array($row['column_name'], array('pmUser_delid', 'pmUser_insid', 'pmUser_signid', 'pmUser_updid')) ) {
					$i = count($join) + 1;
					$join[] = "left join dbo.pmUserCache t{$i} on t{$i}.pmUser_id = t0.{$row['column_name']}";
					$select[] = "rtrim(t{$i}.pmUser_Login) as \"{$row['column_name']}_Name\"";
				}
			}

			$queryResult = $this->queryResult("
				select " . implode(",", $select) . "
				from {$this->_schema}.{$this->_table} t0
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
