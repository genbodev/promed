<?php
defined('BASEPATH') or die('No direct script access allowed');
/**
 * Ufa_AnalyzerTestFormula_model.php - модель для работы с формулами при вычислении  расчитываемых тестов АРМ лаборанта
 * https://redmine.swan.perm.ru/issues/62598
 *
 *
 * @package			AnalyzerTestFormula
 * @author			Васинский Игорь 
 * @version			11.07.2016
 */

class Ufa_AnalyzerTestFormula_model extends SwPgModel
{
    /**
     * конструктор
     */
    function __construct()
    {
        parent::__construct();
    }
 
    /**
     * Сохранение формулы, создание группы аргументов, сохранение аргументов (кодов услуг)
     */
   function AnalyzerTestFormula_ins($data){
        $params = array(
            'Analyzer_id'=>$data['Analyzer_id'],
            'AnalyzerTest_id'=>$data['AnalyzerTest_id'],
            'AnalyzerTest_pid'=>$data['AnalyzerTest_pid'],
            'Usluga_ids'=>$data['Usluga_ids'],
            'AnalyzerTestFormula_Formula'=>$data['AnalyzerTestFormula_Formula'],
            'AnalyzerTestFormula_Comment'=>$data['AnalyzerTestFormula_Comment'],
            'AnalyzerTestFormula_Code'=>$data['AnalyzerTestFormula_Code'],
            'AnalyzerTestFormula_ResultUnit'=>$data['AnalyzerTestFormula_ResultUnit'],
            'pmUser_id'=>$data['pmUser_id'],
            'Lpu_id'=>$data['Lpu_id'],
        );

        $Ulsuga_ids = json_decode($params['Usluga_ids'], 1);
        $Ulsuga_ids = array_map('trim', $Ulsuga_ids);
        $Ulsuga_ids = implode(",", $Ulsuga_ids);

        $params['Usluga_ids'] = $Ulsuga_ids;

        $query = "
        select
            Error_Code as \"Error_Code\",
            Error_Message as \"Error_Msg\"
        from lis.p_AnalyzerTestFormula_ins
            (
                Analyzer_id := :Analyzer_id,
                AnalyzerTest_id := :AnalyzerTest_id,
                AnalyzerTest_pid := :AnalyzerTest_pid,
                Usluga_ids := :Usluga_ids,
                AnalyzerTestFormula_Code := :AnalyzerTestFormula_Code,
                AnalyzerTestFormula_Formula := :AnalyzerTestFormula_Formula,
                AnalyzerTestFormula_Comment := :AnalyzerTestFormula_Comment,
                AnalyzerTestFormula_ResultUnit := :AnalyzerTestFormula_ResultUnit,
                pmUser_id := :pmUser_id,
                Lpu_id := :Lpu_id
            )";



        //echo getDebugSQL($query, $params);
        $result = $this->db->query($query, $params);
        if ( is_object($result) )
        {
            return $result->result('array');
        }
        else
        {
            return array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных');
        }
    }
    /**
     * Редактирование формулы, создание группы аргументов, сохранение аргументов (кодов услуг)
     */
   function AnalyzerTestFormula_upd($data){
        $params = array(
            'AnalyzerTestFormula_id'=>$data['AnalyzerTestFormula_id'],
            'Analyzer_id'=>$data['Analyzer_id'],
            'AnalyzerTest_id'=>$data['AnalyzerTest_id'],
            'AnalyzerTest_pid'=>$data['AnalyzerTest_pid'],
            'Usluga_ids'=>$data['Usluga_ids'],
            'AnalyzerTestFormula_Code'=>$data['AnalyzerTestFormula_Code'],
            'AnalyzerTestFormula_Formula'=>$data['AnalyzerTestFormula_Formula'],
            'AnalyzerTestFormula_Comment'=>$data['AnalyzerTestFormula_Comment'],
            'AnalyzerTestFormula_ResultUnit'=>$data['AnalyzerTestFormula_ResultUnit'],
            'pmUser_id'=>$data['pmUser_id'],
            'Lpu_id'=>$data['Lpu_id'],
        );

        $Ulsuga_ids = json_decode($params['Usluga_ids'], 1);
        $Ulsuga_ids = array_map('trim', $Ulsuga_ids);
        $Ulsuga_ids = implode(",", $Ulsuga_ids);

        $params['Usluga_ids'] = $Ulsuga_ids;

 
        $query = "
        select
            Error_Code as \"Error_Code\",
            Error_Message as \"Error_Msg\"
        from lis.p_AnalyzerTestFormula_upd
            (
                AnalyzerTestFormula_id := :AnalyzerTestFormula_id,
                Analyzer_id := :Analyzer_id,
                AnalyzerTest_id := :AnalyzerTest_id,
                AnalyzerTest_pid := :AnalyzerTest_pid,
                Usluga_ids := :Usluga_ids,
                AnalyzerTestFormula_Code := :AnalyzerTestFormula_Code,
                AnalyzerTestFormula_Formula := :AnalyzerTestFormula_Formula,
                AnalyzerTestFormula_Comment := :AnalyzerTestFormula_Comment,
                AnalyzerTestFormula_ResultUnit := :AnalyzerTestFormula_ResultUnit,
                pmUser_id := :pmUser_id,
                Lpu_id := :Lpu_id
            )";



        //echo getDebugSQL($query, $params);
        $result = $this->db->query($query, $params);
        if ( is_object($result) )
        {
            return $result->result('array');
        }
        else
        {
            return array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных');
        }
    }
    
    /**
     * Сохранение формулы, создание группы аргументов, сохранение аргументов (кодов услуг)
     */
   function AnalyzerTestFormula_del($data){
        $params = array(
            'AnalyzerTestFormula_id'=>$data['AnalyzerTestFormula_id']
        );

        $query = "
        select
            Error_Code as \"Error_Code\",
            Error_Message as \"Error_Msg\"
        from lis.p_AnalyzerTestFormula_del
            (
                AnalyzerTestFormula_id := :AnalyzerTestFormula_id
            )";


        //echo getDebugSQL($query, $params);
        $result = $this->db->query($query, $params);
        if ( is_object($result) )
        {
            return $result->result('array');
        }
        else
        {
            return array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных');
        }
    } 
    
    /**
     * Удаление всех формул теста
     */
   function AnalyzerTestFormulaAll_del($data){
        $params = array(
            'Analyzer_id'=>$data['Analyzer_id'],
            'AnalyzerTest_id'=>$data['AnalyzerTest_id'],
            'AnalyzerTest_pid'=>$data['AnalyzerTest_pid'],
        );


        $query = "
        select
            Error_Code as \"Error_Code\",
            Error_Message as \"Error_Msg\"
        from lis.p_AnalyzerTestFormulaAll_del
            (
                Analyzer_id := :Analyzer_id,
                AnalyzerTest_id := :AnalyzerTest_id,
                AnalyzerTest_pid := :AnalyzerTest_pid
            )";


        //echo getDebugSQL($query, $params);
        $result = $this->db->query($query, $params);
        if ( is_object($result) )
        {
            return $result->result('array');
        }
        else
        {
            return array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных');
        }
    }
    
    /**
     * Получение списка формул для теста, исследования, экземпляра анализатора
     */
   function getAnalyzerTestFormula($data){
        $params = array(
            'Analyzer_id'=>$data['Analyzer_id'],
            'AnalyzerTest_id'=>$data['AnalyzerTest_id'],
            'AnalyzerTest_pid'=>$data['AnalyzerTest_pid'],
        );

        /*
        $query = "select
        [AnalyzerTestFormula_id]
        ,[Analyzer_id]
        ,[AnalyzerTest_id]
        ,[AnalyzerTest_pid]
        ,[AnalyzerTestFormula_Code]
        ,[AnalyzerTestFormula_Formula]
        ,[AnalyzerTestFormula_Comment]
        ,[AnalyzerTestFormula_insDT]
        ,[AnalyzerTestFormula_updDT]
        ,[AnalyzerTestFormula_Deleted]
        ,[pmUser_insID]
        ,[pmUser_updID]
        from
        lis.AnalyzerTestFormula
        where
        Analyzer_id = :Analyzer_id
        and AnalyzerTest_id = :AnalyzerTest_id
        and AnalyzerTest_pid = :AnalyzerTest_pid
        ";
         */
        $query = "
        	select
				ATF.AnalyzerTestFormula_id as \"AnalyzerTestFormula_id\"
				,ATF.Analyzer_id as \"Analyzer_id\"
				,ATF.AnalyzerTest_id as \"AnalyzerTest_id\"
				,ATF.AnalyzerTest_pid as \"AnalyzerTest_pid\"
				,ATF.AnalyzerTestFormula_Code as \"AnalyzerTestFormula_Code\"
				,ATF.AnalyzerTestFormula_Formula as \"AnalyzerTestFormula_Formula\"
				,ATF.AnalyzerTestFormula_Comment as \"AnalyzerTestFormula_Comment\"
				,ATF.AnalyzerTestFormula_insDT as \"AnalyzerTestFormula_insDT\"
				,ATF.AnalyzerTestFormula_updDT as \"AnalyzerTestFormula_updDT\"
				,ATF.AnalyzerTestFormula_Deleted as \"AnalyzerTestFormula_Deleted\"
				,pmUser_insID as \"pmUser_insID\"
				,pmUser_updID as \"pmUser_updID\"
				,U.list as \"list\"
			from
				lis.AnalyzerTestFormula ATF
				LEFT JOIN LATERAL  (
					SELECT (
						select STRING_AGG((
							select '{' || cast(UslugaComplex_Code as varchar(120)) || '}[delout]' || UslugaComplex_Name
							from dbo.UslugaComplex
							where UslugaComplex_id = temp.Usluga_id and Lpu_id = 21
							limit 1
						), ' [delin]')
						FROM (
							select Usluga_id
							from lis.AnalyzerTestFormulaArguments ATFA 
							where ATFA.AnalyzerTestFormulaGroups_code in (
								select ATFG.AnalyzerTestFormulaGroups_code
								FROM lis.AnalyzerTestFormulaGroups ATFG
								where ATFG.AnalyzerTestFormula_id  =  ATF.AnalyzerTestFormula_id
							)
						) as temp
					) list
				) as U on true
			where
				Analyzer_id = :Analyzer_id
				 and AnalyzerTest_id = :AnalyzerTest_id
				 and AnalyzerTest_pid = :AnalyzerTest_pid
        ";
        //echo getDebugSQL($query, $params);
        $result = $this->db->query($query, $params);
        if ( is_object($result) )
        {
            return $result->result('array');
        }
        else
        {
            return array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных');
        }
    }

    /**
     * Загрузка списка тестов
     */
    function loadAnalyzerTestGrid($filter) {
        $where = array();
        $p = array();
        $select = "";
        $join = "";

        if (isset($filter['ReagentModel_id']) && !empty($filter['ReagentModel_id'])) {
            $where[] = 'rnr.ReagentModel_id = :ReagentModel_id';
            $p['ReagentModel_id'] = $filter['ReagentModel_id'];
            $join .= 'JOIN lis.ReagentNormRate rnr  ON rnr.AnalyzerTest_id = at.AnalyzerTest_id';
        } else {
            $join .= 'LEFT JOIN lis.ReagentNormRate rnr  ON rnr.AnalyzerTest_id = at.AnalyzerTest_id';
            $where[] = 'rnr.ReagentNormRate_id IS NULL';
        }
        if (isset($filter['AnalyzerTest_id']) && $filter['AnalyzerTest_id']) {
            $where[] = 'at.AnalyzerTest_id != :AnalyzerTest_id';
            $p['AnalyzerTest_id'] = $filter['AnalyzerTest_id'];
        }
        if (isset($filter['AnalyzerTest_pid']) && $filter['AnalyzerTest_pid']) {
            $where[] = 'at.AnalyzerTest_pid = :AnalyzerTest_pid';
            $p['AnalyzerTest_pid'] = $filter['AnalyzerTest_pid'];
        } else {
            $where[] = 'at.AnalyzerTest_pid IS NULL';
        }
        if (isset($filter['AnalyzerModel_id']) && $filter['AnalyzerModel_id']) {
            $where[] = 'at.AnalyzerModel_id = :AnalyzerModel_id';
            $p['AnalyzerModel_id'] = $filter['AnalyzerModel_id'];
        }
        if (isset($filter['Analyzer_id']) && $filter['Analyzer_id']) {
            $where[] = 'at.Analyzer_id = :Analyzer_id';
            $p['Analyzer_id'] = $filter['Analyzer_id'];
        }
        if (isset($filter['AnalyzerTestType_id']) && $filter['AnalyzerTestType_id']) {
            $where[] = 'at.AnalyzerTestType_id = :AnalyzerTestType_id';
            $p['AnalyzerTestType_id'] = $filter['AnalyzerTestType_id'];
        }
        if (isset($filter['UslugaComplex_id']) && $filter['UslugaComplex_id']) {
            $where[] = 'at.UslugaComplex_id = :UslugaComplex_id';
            $p['UslugaComplex_id'] = $filter['UslugaComplex_id'];
        }
        if (isset($filter['Unit_id']) && $filter['Unit_id']) {
            $where[] = 'at.Unit_id = :Unit_id';
            $p['Unit_id'] = $filter['Unit_id'];
        }
        if (isset($filter['AnalyzerWorksheetType_id']) && $filter['AnalyzerWorksheetType_id']) {
            $where[] = 'at.AnalyzerTest_id IN (SELECT AnalyzerTest_id
                                                FROM lis.v_AnalyzerTest at
                                                WHERE at.AnalyzerModel_id in (
                                                        SELECT AnalyzerModel_id
                                                        FROM lis.v_AnalyzerWorksheetType
                                                        WHERE AnalyzerWorksheetType_id = :AnalyzerWorksheetType_id)
                                                                                )';
            $p['AnalyzerWorksheetType_id'] = $filter['AnalyzerWorksheetType_id'];
        }

        $where[] = 'atf.AnalyzerTest_id IS NULL';
        $where_clause = implode(' AND ', $where);
        if (empty($where_clause)) {
            $where_clause = "(1=1)";
        }

        $q = "
            SELECT
                -- select
                at.AnalyzerTest_id as \"AnalyzerTest_id\",
                at.AnalyzerTest_pid as \"AnalyzerTest_pid\",
                at.AnalyzerModel_id as \"AnalyzerModel_id\",
                at.AnalyzerTestType_id as \"AnalyzerTestType_id\",
                uc.UslugaComplex_Code as \"AnalyzerTest_Code\",
                Coalesce(at.AnalyzerTest_Name, uc.UslugaComplex_Name) as \"AnalyzerTest_Name\",
                at.AnalyzerTest_SysNick as \"AnalyzerTest_SysNick\",
                AnalyzerTest_pid_ref.AnalyzerTest_Name as \"AnalyzerTest_pid_Name\",
                AnalyzerModel_id_ref.AnalyzerModel_Name as \"AnalyzerModel_id_Name\",
                AnalyzerTestType_id_ref.AnalyzerTestType_Name as \"AnalyzerTestType_id_Name\",
                Coalesce(at.AnalyzerTest_isTest, 1) as \"AnalyzerTest_isTest\",
                to_char(AT.AnalyzerTest_begDT,'dd.mm.yyyy') as \"AnalyzerTest_begDT\",
                to_char(AT.AnalyzerTest_endDT,'dd.mm.yyyy') as \"AnalyzerTest_endDT\",
                at.AnalyzerTest_SortCode as \"AnalyzerTest_SortCode\",
                case when at.AnalyzerTest_IsNotActive = 2 then 1 else 0 end as \"AnalyzerTest_IsNotActive\",
                un.Unit_Name as \"Unit_Name\"
                {$select}
                -- end select
            FROM
                -- from
                lis.v_AnalyzerTest at
                LEFT JOIN lis.v_AnalyzerTest AnalyzerTest_pid_ref  ON AnalyzerTest_pid_ref.AnalyzerTest_id = at.AnalyzerTest_pid
                LEFT JOIN lis.v_AnalyzerModel AnalyzerModel_id_ref  ON AnalyzerModel_id_ref.AnalyzerModel_id = at.AnalyzerModel_id
                LEFT JOIN lis.v_AnalyzerTestType AnalyzerTestType_id_ref  ON AnalyzerTestType_id_ref.AnalyzerTestType_id = at.AnalyzerTestType_id
                LEFT JOIN v_UslugaComplex  uc on uc.UslugaComplex_id = at.UslugaComplex_id
                LEFT JOIN lis.AnalyzerTestFormulaArguments atf on atf.AnalyzerTest_id = at.AnalyzerTest_id
                LEFT JOIN LATERAL (
                    select
                        u.Unit_Name
                    from lis.v_QuantitativeTestUnit qtu
                        inner join lis.v_Unit u  on u.Unit_id = qtu.Unit_id
                    where
                        qtu.QuantitativeTestUnit_IsBase = 2
                        and qtu.AnalyzerTest_id = at.AnalyzerTest_id
                    limit 1
                ) un on true
                {$join}
                -- end from
            WHERE
                -- where
                {$where_clause}
                -- end where
            ORDER BY
                -- order by
                Coalesce(at.AnalyzerTest_isTest, 1), at.AnalyzerTest_Code
                -- end order by
        ";

 
        //echo getDebugSQL($q, array());         exit();
        return $this->getPagingResponse($q, $filter, $filter['start'], $filter['limit'], true);
    }

    /**
     * Проверка, может ли редактируемый тест быть расчетным
     */
   function checkAnalyzerTestFormula($data) {
        $query = "
            SELECT 
                AnalyzerTestFormula_Code as \"AnalyzerTestFormula_Code\", 
                AnalyzerTestFormula_Formula as \"AnalyzerTestFormula_Formula\"
            FROM lis.AnalyzerTestFormula  
            WHERE AnalyzerTest_pid = :AnalyzerTest_pid
        ";

        $result = $this->db->query($query, $data);

        if (is_object($result)) {
            $results = $result->result('array');

            $formula = array('responseText' => array('formula' => true));
            array_walk($results, function($v, $k) use(&$formula, $data) {
                if(stripos($v['AnalyzerTestFormula_Formula'], $data['AnalyzerTestFormula_Code'])
                    && $v['AnalyzerTestFormula_Code'] !== $data['AnalyzerTestFormula_Code']) {
                    $formula['responseText']['formula'] = false;
                    $formula['responseText']['code'] = $v['AnalyzerTestFormula_Code'];
                }
            });

            return $formula;
        } else {
            return array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных');
        }
    }

}    
