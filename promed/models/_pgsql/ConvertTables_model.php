<?php	defined('BASEPATH') or die ('No direct script access allowed');

/**
 * ConvertTables_model - модель для работы с таблицами - просмотр, список полей, конвертирование значений в другую кодировку
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2012 Swan Ltd.
 * @author       Khorev Sergey (ipshon@rambler.ru)
 * @version      03.02.2012
 */
class ConvertTables_model extends swPgModel
{
    function __construct()
    {
        parent::__construct();
    }

    /**
     * @return bool|mixed
     */

    function LoadSchemes()
    {
        $sql = "
                SELECT DISTINCT (T.TABLE_SCHEMA) AS \"TABLE_SCHEME\"
                FROM INFORMATION_SCHEMA.TABLES T
                ORDER BY 1
               ";
        $result = $this->db->query($sql);
        if (!is_object($result)) {return false;}
        return $result->result('array');
    }

    /**
     * @param $data
     * @return bool|mixed
     * Получение всех таблиц в выбранной схеме
     */
    function LoadTables($data)
    {
        $query_params = array();
        $query_params['TABLE_SCHEME'] = $data['TABLE_SCHEME'];
        $sql = "
                select distinct table_name 
                from information_schema.\"columns\" 
                where table_schema=lower(:TABLE_SCHEME) and data_type in ('char','varchar','text','character varying')
               ";
        $result = $this->db->query($sql,$query_params);
        if (!is_object($result)) {return false;}
        return $result->result('array');
    }

    /**
     * @param $data
     * @return bool|mixed
     * Получение ТЕКСТОВЫХ полей выбранной таблицы
     */
    function LoadFields($data)
    {
        $table_name = "".$data['TABLE_SCHEME'].".".$data['TABLE_NAME']."";
        $sql = "
               select column_name, data_type   
               from information_schema.\"columns\" 
               where table_schema||'.'||table_name=lower('".$table_name."') and data_type in ('char','varchar','text','character varying')
                ";
        $result = $this->db->query($sql);
        if (!is_object($result)) {return false;}
        return $result->result('array');
    }

    /**
     * @param $data
     * @return bool|mixed
     * Получение ВСЕХ записей выбранного поля таблицы
     */
    function GetFieldsData($data)
    {
        $table_name = "".$data['TABLE_SCHEME'].".".$data['TABLE_NAME']."";
        $sql = "SELECT ".$data["FIELD_NAME"]." AS \"FIELD_VALUE\", ".$data['TABLE_NAME']."_id AS \"FIELD_ID\" FROM ".$table_name;

        $result = $this->db->query($sql);
        if (!is_object($result)) {return false;}
        return $result->result('array');
    }

    /**
     * @param $data
     * @param $id
     * @param $value
     * @return bool|CI_DB_sqlsrv_result|void
     * Обновление (в WINDOWS-1251) выбранного значения поля таблицы
     */
    function ConvertField($data,$id,$value)
    {
        $table_name = "".$data['TABLE_SCHEME'].".".$data['TABLE_NAME']."";

        $sql = "UPDATE ".$table_name." SET ".$data["FIELD_NAME"]." = '".$value."' WHERE ".$data['TABLE_NAME']."_id = ".$id;

        $result = $this->db->query($sql);
        return $result;
    }
}
?>