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
class ConvertTables_model extends CI_Model
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
                SELECT DISTINCT (T.TABLE_SCHEMA) AS TABLE_SCHEME
                FROM INFORMATION_SCHEMA.TABLES T with(nolock)
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
                SELECT DISTINCT T.TABLE_NAME
                FROM INFORMATION_SCHEMA.TABLES T with(nolock)
                INNER JOIN SYSCOLUMNS SC with(nolock) ON SC.id = OBJECT_ID(T.TABLE_NAME)
                INNER JOIN SYSTYPES ST with(nolock) ON ST.xtype = SC.xtype AND ST.usertype = SC.usertype
                WHERE T.TABLE_TYPE = 'BASE TABLE'
                AND   (ST.name = 'char' or ST.name = 'varchar' or ST.name = 'nvarchar')
                AND   T.TABLE_SCHEMA = :TABLE_SCHEME
                ORDER BY 1
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
        $table_name = "[".$data['TABLE_SCHEME']."].[".$data['TABLE_NAME']."]";
        $sql = "
                SELECT SC.name AS FIELD_NAME,
                       ST.name AS FIELD_TYPE
                FROM   SYSCOLUMNS SC with(nolock)
                INNER  JOIN SYSTYPES ST with(nolock) ON ST.xtype = SC.xtype AND ST.usertype = SC.usertype
                WHERE  SC.id = OBJECT_ID('".$table_name."')
                AND    (ST.name = 'char' or ST.name = 'varchar' or ST.name = 'nvarchar')
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
        $table_name = "[".$data['TABLE_SCHEME']."].[".$data['TABLE_NAME']."]";
        $sql = "SELECT [".$data["FIELD_NAME"]."] AS FIELD_VALUE, [".$data['TABLE_NAME']."_id] AS FIELD_ID FROM ".$table_name;

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
        $table_name = "[".$data['TABLE_SCHEME']."].[".$data['TABLE_NAME']."]";

        $sql = "UPDATE ".$table_name." SET [".$data["FIELD_NAME"]."] = '".$value."' WHERE [".$data['TABLE_NAME']."_id] = ".$id;

        $result = $this->db->query($sql);
        return $result;
    }
}
?>