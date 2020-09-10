<?php
/**
 * Class ModelGenerator_model
 *
 * @property CI_DB_driver $db
 */
class ModelGenerator_model extends swPgModel
{
	private $table_desc;
	private $schema_name;

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	/**
	 * @param $db_tablename
	 * @return array
	 */
	public function getTableInfo($db_tablename)
	{
		//TODO 111
		$query = "
			select
				s.name AS schema_name,
				c.column_name AS column_name,
				c1.type_name AS type_name,
				c1.column_description as column_description,
				c1.is_nullable,
				c1.table_description AS table_desc,
				fk.ref_table,
				fk.ref_col,
				fk.ref_name,
				fk.ref_schema
			from
				sys.tables t
				LEFT JOIN sys.schemas s with(nolock) ON t.schema_id = s.schema_id
				LEFT JOIN dbo.v_columns c with(nolock) ON c.table_name = 'v_' + t.name
				LEFT JOIN dbo.v_columns c1 with(nolock) ON c1.table_id = t.object_id AND c1.column_name = c.column_name
				LEFT JOIN (
					SELECT cc.parent_column_id, ref_table.name AS ref_table, ref_col.name AS ref_col, ISNULL(ref_name.name, ref_name_alt.name) AS ref_name, k.parent_object_id, s.name AS ref_schema FROM sys.foreign_keys k with(nolock)
						LEFT JOIN sys.tables ref_table with(nolock) ON k.referenced_object_id = ref_table.object_id
						LEFT JOIN sys.columns ref_name with(nolock) ON ref_name.object_id = (SELECT object_id FROM sys.views with(nolock) WHERE name = 'v_' + ref_table.name) AND ref_name.name = ref_table.name + '_Name'
						LEFT JOIN sys.columns ref_name_alt with(nolock) ON ref_name_alt.object_id = (SELECT object_id FROM sys.views with(nolock) WHERE name = 'v_' + ref_table.name) AND ref_name_alt.name = ref_table.name + '_Nick'
						LEFT JOIN sys.foreign_key_columns cc with(nolock) ON cc.constraint_object_id = k.object_id
						LEFT JOIN sys.columns ref_col with(nolock) ON ref_table.object_id = ref_col.object_id AND cc.referenced_column_id = ref_col.column_id
						LEFT JOIN sys.schemas s with(nolock) ON ref_table.schema_id = s.schema_id
				) fk ON fk.parent_object_id = t.object_id AND fk.parent_column_id = c1.column_order
			where
				t.name = :table_name
				AND ( '@' + c.column_name ) IN (
				SELECT name FROM sys.all_parameters with(nolock)
				WHERE
					object_id = ( SELECT
									object_id
								  FROM
									sys.all_objects with(nolock)
								  WHERE
									name = 'p_' + t.name + '_ins'
								) )
			order by c1.column_order
		";
		$queryParams = ["table_name" => $db_tablename];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		$result = $result->result_array();
		$resultArray = [];
		foreach ($result as $field) {
			$resultArray[$field["column_name"]] = $field;
		}
		$isEvn = ("0" !== $this->getFirstResultFromQuery("select count(*) from EvnClass where EvnClass_SysNick = :table_name", $queryParams));
		if ($isEvn) {
			//значит есть поля, доставшиеся этой таблице "по наследству" - информацию о них надо получать другим запросом
			$inheritedFields = $this->getEvnInheritedFieldsInfo($db_tablename);
			foreach ($resultArray as $field) {
				//убедимся, что инфа о поле действительно отсутствует
				if (empty($field["type_name"])) {
					//дополняем имеющуюся информацию о поле
					$resultArray[$field["column_name"]]["type_name"] = $inheritedFields[$field["column_name"]]["type_name"];
					$resultArray[$field["column_name"]]["column_description"] = $inheritedFields[$field["column_name"]]["column_description"];
					$resultArray[$field["column_name"]]["is_nullable"] = $inheritedFields[$field["column_name"]]["is_nullable"];
					$resultArray[$field["column_name"]]["ref_table"] = $inheritedFields[$field["column_name"]]["ref_table"];
					$resultArray[$field["column_name"]]["ref_col"] = $inheritedFields[$field["column_name"]]["ref_col"];
					$resultArray[$field["column_name"]]["ref_name"] = $inheritedFields[$field["column_name"]]["ref_name"];
					$resultArray[$field["column_name"]]["ref_schema"] = $inheritedFields[$field["column_name"]]["ref_schema"];
				}
			}
		}
		$isMorbus = ("0" !== $this->getFirstResultFromQuery("select count(*) from MorbusClass where MorbusClass_SysNick = :table_name", $queryParams));
		if ($isMorbus) {
			$inheritedFields = $this->getMorbusInheritedFieldsInfo($db_tablename);
			foreach ($resultArray as $field) {
				//убедимся, что инфа о поле действительно отсутствует
				if (empty($field["type_name"])) {
					//дополняем имеющуюся информацию о поле
					$resultArray[$field["column_name"]]["type_name"] = $inheritedFields[$field["column_name"]]["type_name"];
					$resultArray[$field["column_name"]]["column_description"] = $inheritedFields[$field["column_name"]]["column_description"];
					$resultArray[$field["column_name"]]["is_nullable"] = $inheritedFields[$field["column_name"]]["is_nullable"];
					$resultArray[$field["column_name"]]["ref_table"] = $inheritedFields[$field["column_name"]]["ref_table"];
					$resultArray[$field["column_name"]]["ref_col"] = $inheritedFields[$field["column_name"]]["ref_col"];
					$resultArray[$field["column_name"]]["ref_name"] = $inheritedFields[$field["column_name"]]["ref_name"];
					$resultArray[$field["column_name"]]["ref_schema"] = $inheritedFields[$field["column_name"]]["ref_schema"];
				}
			}
		}
		foreach ($resultArray as $field) {
			if ($field["table_desc"]) {
				$this->table_desc = $field["table_desc"];
			}
			if ($field["schema_name"]) {
				$this->schema_name = $field["schema_name"];
			}
			switch ($field["type_name"]) {
				case "bigint":
					$field["type_name"] = "int";
					break;
				case "varchar":
					$field["type_name"] = "string";
					break;
				case "numeric":
					$field["type_name"] = "float";
					break;
			}
			if (empty($field["column_description"])) {
				$field["column_description"] = $field["column_name"];
			}
			$resultArray[$field["column_name"]] = $field;
		}
		return $resultArray;
	}

	/**
	 * @return mixed
	 */
	public function getSchemaName()
	{
		return $this->schema_name;
	}

	/**
	 * @return mixed
	 */
	public function getTableDesc()
	{
		return $this->table_desc;
	}

	/**
	 * @param $table_name
	 * @return array
	 */
	public function getEvnInheritedFieldsInfo($table_name)
	{
		//TODO 111
		$q = <<<Q
WITH    class_hierarhy ( EvnClass_Sysnick, Class_id, ParentClass_id )
          AS ( SELECT
                EvnClass_SysNick,
                EvnClass_id AS Class_id,
                EvnClass_pid AS ParentClass_id
               FROM
                EvnClass with(nolock)
               WHERE
                EvnClass_SysNick = :table_name
               UNION ALL
               SELECT
                EvnClass.EvnClass_SysNick,
                EvnClass_id AS Class_id,
                EvnClass_pid AS ParentClass_id
               FROM
                EvnClass with(nolock)
                INNER JOIN class_hierarhy ch with(nolock) ON ch.ParentClass_id = EvnClass_id
             )
    SELECT
        c.schema_name,
        CASE c.table_name
			WHEN :table_name THEN c.column_name
			ELSE CASE WHEN c.column_name = 'PersonEvn_id' THEN c.column_name ELSE REPLACE(c.column_name, c.table_name + '_', :table_name + '_') END
		END AS column_name,
        c.type_name,
        c.column_description,
        c.is_nullable,
        c.column_order,
        fk.ref_table,
        fk.ref_col,
        fk.ref_name,
        fk.ref_schema
    FROM
        v_columns c with(nolock)
        LEFT JOIN sys.tables t with(nolock) ON t.object_id = c.table_id
        LEFT JOIN ( SELECT
                        cc.parent_column_id,
                        ref_table.name AS ref_table,
                        ref_col.name AS ref_col,
                        ISNULL(ref_name.name, ref_name_alt.name) AS ref_name,
                        k.parent_object_id,
                        s.name AS ref_schema
                    FROM
                        sys.foreign_keys k with(nolock)
                        LEFT JOIN sys.tables ref_table with(nolock) ON k.referenced_object_id = ref_table.object_id
                        LEFT JOIN sys.columns ref_name with(nolock) ON ref_name.object_id = (SELECT object_id FROM sys.views with(nolock) WHERE name = 'v_' + ref_table.name)
                                                          AND ref_name.name  = ref_table.name + '_Name'
                        LEFT JOIN sys.columns ref_name_alt with(nolock) ON ref_name_alt.object_id = (SELECT object_id FROM sys.views with(nolock) WHERE name = 'v_' + ref_table.name)
                                                          AND ref_name_alt.name = ref_table.name + '_Nick'
                        LEFT JOIN sys.foreign_key_columns cc with(nolock) ON cc.constraint_object_id = k.object_id
                        LEFT JOIN sys.columns ref_col with(nolock) ON ref_table.object_id = ref_col.object_id
                                                         AND cc.referenced_column_id = ref_col.column_id
                        LEFT JOIN sys.schemas s with(nolock) ON ref_table.schema_id = s.schema_id
                  ) fk ON fk.parent_object_id = c.table_id
                          AND fk.parent_column_id = c.column_order
    WHERE
        c.table_name IN ( SELECT
                            EvnClass_Sysnick
                          FROM
                            class_hierarhy with(nolock) )
Q;
		$p = array('table_name' => $table_name);
		$r = $this->db->query($q, $p);
		$r = $r->result_array();
		$result = array();
		//чтобы легко было находить запись, положим инфу о поле в массив с ключом-именем столбца
		foreach ($r as $field) {
			$result[$field['column_name']] = $field;
		}
		return $result;
	}

	/**
	 * @param $table_name
	 * @return array
	 */
	public function getMorbusInheritedFieldsInfo($table_name)
	{
		//TODO 111
		$q = <<<Q
WITH    class_hierarhy ( MorbusClass_Sysnick, Class_id, ParentClass_id )
          AS ( SELECT
                MorbusClass_SysNick,
                MorbusClass_id AS Class_id,
                MorbusClass_pid AS ParentClass_id
               FROM
                MorbusClass with(nolock)
               WHERE
                MorbusClass_SysNick = :table_name
               UNION ALL
               SELECT
                MorbusClass.MorbusClass_SysNick,
                MorbusClass_id AS Class_id,
                MorbusClass_pid AS ParentClass_id
               FROM
                MorbusClass with(nolock)
                INNER JOIN class_hierarhy ch with(nolock) ON ch.ParentClass_id = MorbusClass_id
             )
    SELECT
        c.schema_name,
		c.column_name,
        c.type_name,
        c.column_description,
        c.is_nullable,
        c.column_order,
        fk.ref_table,
        fk.ref_col,
        fk.ref_name,
        fk.ref_schema
    FROM
        v_columns c with(nolock)
        LEFT JOIN sys.tables t with(nolock) ON t.object_id = c.table_id
        LEFT JOIN ( SELECT
                        cc.parent_column_id,
                        ref_table.name AS ref_table,
                        ref_col.name AS ref_col,
                        ISNULL(ref_name.name, ref_name_alt.name) AS ref_name,
                        k.parent_object_id,
                        s.name AS ref_schema
                    FROM
                        sys.foreign_keys k with(nolock)
                        LEFT JOIN sys.tables ref_table with(nolock) ON k.referenced_object_id = ref_table.object_id
                        LEFT JOIN sys.columns ref_name with(nolock) ON ref_name.object_id = (SELECT object_id FROM sys.views with(nolock) WHERE name = 'v_' + ref_table.name)
                                                          AND ref_name.name  = ref_table.name + '_Name'
                        LEFT JOIN sys.columns ref_name_alt with(nolock) ON ref_name_alt.object_id = (SELECT object_id FROM sys.views with(nolock) WHERE name = 'v_' + ref_table.name)
                                                          AND ref_name_alt.name = ref_table.name + '_Nick'
                        LEFT JOIN sys.foreign_key_columns cc with(nolock) ON cc.constraint_object_id = k.object_id
                        LEFT JOIN sys.columns ref_col with(nolock) ON ref_table.object_id = ref_col.object_id
                                                         AND cc.referenced_column_id = ref_col.column_id
                        LEFT JOIN sys.schemas s with(nolock) ON ref_table.schema_id = s.schema_id
                  ) fk ON fk.parent_object_id = c.table_id
                          AND fk.parent_column_id = c.column_order
    WHERE
        c.table_name IN ( SELECT
                            MorbusClass_Sysnick
                          FROM
                            class_hierarhy with(nolock) )
Q;
		$p = array('table_name' => $table_name);
		$r = $this->db->query($q, $p);
		$r = $r->result_array();
		$result = array();
		//чтобы легко было находить запись, положим инфу о поле в массив с ключом-именем столбца
		foreach ($r as $field) {
			$result[$field['column_name']] = $field;
		}
		return $result;
	}
}