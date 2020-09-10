<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Модель для объектов статистики принятых тестов из АСМЛО
 * 
 * @package      common
 * @access       public
 * @author       Arslanov Azat
 */
class TestStat_Model extends swModel {
	private $testStat_id;
	private $labCode;	// Код лаборатории
	private $analyzerCode;	// Код анализатора
	private $testDate;	// Дата выполнения теста (без времени)
	private $testCode;	// Код теста
	private $testCount;	// Количество тестов
	
	/**
	 * id 
	 */
	public function getTestStat_id() { return $this->testStat_id;}
	/**
	 * id
	 */
	public function setTestStat_id($value) { $this->testStat_id = $value; }
	/**
	 * Код лаборатории
	 */
	public function getLabCode() { return $this->labCode;}
	/**
	 * Код лаборатории
	 */
	public function setLabCode($value) { $this->labCode = $value; }
	/**
	 * Код анализатора
	 */
	public function getAnalyzerCode() { return $this->analyzerCode;}
	/**
	 * Код анализатора
	 */
	public function setAnalyzerCode($value) { $this->analyzerCode = $value; }
	/**
	 * Дата выполнения теста (без времени)
	 */
	public function getTestDate() { return $this->testDate;}
	/**
	 * Дата выполнения теста (без времени)
	 */
	public function setTestDate($value) { $this->testDate = $value; }
	/**
	 * Код теста
	 */
	public function getTestCode() { return $this->testCode;}
	/**
	 * Код теста
	 */
	public function setTestCode($value) { $this->testCode = $value; }
	/**
	 * Количество тестов
	 */
	public function getTestCount() { return $this->testCount;}
	/**
	 * Количество тестов
	 */
	public function setTestCount($value) { $this->testCount = $value; }

	/**
	 * Конструктор
	 */
	function __construct(){
	}

	/**
	 * Обновление таблицы статистики в соответствии с принятым тестом
	 * (наращивается количество на 1)
	 */
	function saveTestStat() {
		$q = 
			'SELECT [TestStat_id]
			FROM [lis].[TestStat] ts with (nolock)
			WHERE ts.TestStat_analyzerCode = :analyzerCode
				AND ts.TestStat_labCode = :labCode
				AND ts.TestStat_testCode = :testCode
				AND ts.TestStat_testDate = CAST(:testDate AS date)';
		$p = array(
			'labCode' => $this->labCode,
			'analyzerCode' => $this->analyzerCode,
			'testDate' => $this->testDate,
			'testCode' => $this->testCode,
		);
		$testStat = $this->getFirstResultFromQuery( $q, $p );
		//sql_log_message('error', 'saveTestStat SELECT SQL: ', getDebugSql($q, $p));
		
		if ($testStat > 0) {//найдена запись для данного теста
			$this->testStat_id = $testStat;
		}
		
		log_message('error', "TestStat_id: " . $this->testStat_id);
		if ( $this->testStat_id > 0 ) { //update
			//set @TestStat_id = :testStat_id;
			//@TestStat_id = @TestStat_id output,
			$q = '
				declare
					@TestStat_id bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);
				exec lis.p_TestStat_upd
					@TestStat_id = :testStat_id output,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @TestStat_id as TestStat_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			';
			//@TestCount = :testCount,
			$p = array(
				'testStat_id' => $this->testStat_id
			);
			//'testCount' => $this->testCount
		} else { //INSERT
			$q = '
				declare
					@TestStat_id bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);
				set @TestStat_id = :testStat_id;
				exec lis.p_TestStat_ins
					@TestStat_id = @TestStat_id output,
					@LabCode = :labCode,
					@AnalyzerCode = :analyzerCode,
					@TestDate = :testDate,
					@TestCode = :testCode,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @TestStat_id as TestStat_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			';
			$p = array(
				'testStat_id' => $this->testStat_id,
				'labCode' => $this->labCode,
				'analyzerCode' => $this->analyzerCode,
				'testDate' => $this->testDate,
				'testCode' => $this->testCode
				//'testCount' => $this->testCount
			);
		}
		//sql_log_message('error', 'saveTestStat SQL: ', getDebugSql($q, $p));
		$r = $this->db->query($q, $p);
		if ( is_object($r) ) {
			$result = $r->result('array');
			$this->testStat_id = $result[0]['TestStat_id'];
		}
		else {
			log_message('error', var_export(array('q' => $q, 'p' => $p, 'e' => sqlsrv_errors()), true));
			$result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}
		return $result;
	}
}
