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

class Lis_Ufa_AnalyzerTestFormula_model extends SwPgModel
{
    /**
     * конструктор
     */
    function __construct()
    {
        parent::__construct();
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
        		error_code as \"Error_Code\",
        		error_message as \"Error_Msg\"
        	from lis.p_AnalyzerTestFormulaAll_del(
        		Analyzer_id_ := :Analyzer_id,
                AnalyzerTest_id_ := :AnalyzerTest_id,
                AnalyzerTest_pid_ := :AnalyzerTest_pid
        	)	
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
}    
