<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Модель для объектов привязки норм расхода реагента к определенной модели анализатора и услуге
 * 
 * @package      common
 * @access       public
 * @author       Arslanov Azat
 */

class ReagentNormRate_model extends swModel {
	private $ReagentNormRate_id;//
	private $UslugaComplex_Code; //Код услуги
	private $AnalyzerModel_id; //Модель анализатора
	private $DrugNomen_id; //Реагент
	private $ReagentNormRate_RateValue;//величина расхода реактива
	private $unit_id;        //ед изм. расходуемого реактива
	private $RefMaterial_id; //биоматериал
	private $pmUser_id;      //Идентификатор пользователя системы Промед

	/**
	 * ReagentNormRate_id
	 */
	public function getReagentNormRate_id() { return $this->ReagentNormRate_id;}
	/**
	 * ReagentNormRate_id
	 */
	public function setReagentNormRate_id($value) { $this->ReagentNormRate_id = $value; }

	/**
	 * Модель анализатора
	 */
	public function getAnalyzerModel_id() { return $this->AnalyzerModel_id;}
	/**
	 * Модель анализатора
	 */
	public function setAnalyzerModel_id($value) { $this->AnalyzerModel_id = $value; }
	/**
	 * Реагент
	 */
	public function getDrugNomen_id() { return $this->DrugNomen_id;}
	/**
	 * Реагент
	 */
	public function setDrugNomen_id($value) { $this->DrugNomen_id = $value; }
	/**
	 * Код услуги (текстовое значение)
	 */
	public function getUslugaComplex_Code() { return $this->UslugaComplex_Code;}
	/**
	 * Код услуги (текстовое значение)
	 */
	public function setUslugaComplex_Code($value) { $this->UslugaComplex_Code = $value; }

	/**
	 * Расход реактива (числовое значение)
	 */
	public function getReagentNormRate_RateValue() { return $this->ReagentNormRate_RateValue;}
	/**
	 * Расход реактива (числовое значение)
	 */
	public function setReagentNormRate_RateValue($value) { $this->ReagentNormRate_RateValue = $value; }
	/**
	 * Ед. измерения расходуемого реактива
	 */
	public function getunit_id() { return $this->unit_id;}
	/**
	 * Ед. измерения расходуемого реактива
	 */
	public function setunit_id($value) { $this->unit_id = $value; }
	/**
	 * Биоматериал
	 */
	public function getRefMaterial_id() { return $this->RefMaterial_id;}
	/**
	 * Биоматериал
	 */
	public function setRefMaterial_id($value) { $this->RefMaterial_id = $value; }
	/**
	 * кто выполнил операцию с нормативом
	 */
	public function getpmUser_id() { return $this->pmUser_id;}
	/**
	 * кто выполнил операцию с нормативом
	 */
	public function setpmUser_id($value) { $this->pmUser_id = $value; }

	/**
	 * конструктор
	 */
	function __construct(){
		if (isset($_SESSION['pmuser_id'])) {
			$this->setpmUser_id($_SESSION['pmuser_id']);
		} else {
			throw new Exception('Значение pmuser_id не установлено в текущей сессии (не выполнен вход в Промед?)');
		}
	}

	/**
	 * Сохранение норматива расхода
	 */
	function saveReagentNormRate() {
		$procedure = 'p_ReagentNormRate_ins';
		if ( !empty($this->ReagentNormRate_id) ) {//если задан id записи
			$q = '
			SELECT TOP 1 ts.TestStat_id
			FROM [lis].[TestStat] ts with (nolock)
			WHERE ts.ReagentNormRate_id = :ReagentNormRate_id
			';
			$p = array(
				'ReagentNormRate_id' => $this->ReagentNormRate_id,
			);
			$testStat = $this->getFirstResultFromQuery( $q, $p );
			
			if (!$testStat) {//ссылка на ReagentNormRate_id в TestStat не используется
				$procedure = 'p_ReagentNormRate_upd';
			}
		}

		$q = "
			declare
				@ReagentNormRate_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @ReagentNormRate_id = :ReagentNormRate_id;
			exec lis." . $procedure . "
				@ReagentNormRate_id = @ReagentNormRate_id output,
				@DrugNomen_id = :DrugNomen_id,
				@AnalyzerModel_id = :AnalyzerModel_id,
				@UslugaComplex_Code = :UslugaComplex_Code,
				@ReagentNormRate_RateValue = :ReagentNormRate_RateValue,
				@unit_id = :unit_id,
				@RefMaterial_id = :RefMaterial_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ReagentNormRate_id as ReagentNormRate_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$p = array(
			'ReagentNormRate_id' => $this->ReagentNormRate_id,
			'AnalyzerModel_id' => $this->AnalyzerModel_id,
			'DrugNomen_id' => $this->DrugNomen_id,
			'UslugaComplex_Code' => $this->UslugaComplex_Code,
			'ReagentNormRate_RateValue' => $this->ReagentNormRate_RateValue,
			'unit_id' => $this->unit_id,
			'RefMaterial_id' => $this->RefMaterial_id,
			'pmUser_id' => $this->pmUser_id,
		);

		//echo getDebugSQL($q, $p);         exit();
		$r = $this->db->query($q, $p);
		if ( is_object($r) ) {
			$result = $r->result('array');
			$this->ReagentNormRate_id = $result[0]['ReagentNormRate_id'];
		}
		else {
			//log_message('error', var_export(array('q' => $q, 'p' => $p, 'e' => sqlsrv_errors()), true));
			$result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}
		return $result;
	}

	/**
	 * Удаление норматива расхода
	 */
	function delete() {
		$q = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec lis.p_ReagentNormRate_del
				@ReagentNormRate_id = :ReagentNormRate_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$r = $this->db->query($q, array(
			'ReagentNormRate_id' => $this->ReagentNormRate_id,
			'pmUser_id' => $this->pmUser_id
		));
		if ( is_object($r) ) {
			return $r->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 *  получение списка всех реактивов из номенклатурного справочника
	 */
	function loadReagentList($data) {
		$queryParams = array();
		$where       = '';
		
		if ( strlen($data['query']) > 0 ) {
			$queryParams['query'] = "%" . $data['query'] . "%";
			$where .= " AND (DN.DrugNomen_Code + ' ' + DN.DrugNomen_Nick) like replace(ltrim(rtrim(:query)),' ', '%') + '%'";
		}
		
		$q = "
		select top 100
			-- select
			DN.DrugNomen_id Drug_id,
			DN.DrugNomen_Code Drug_Code,
			DN.DrugNomen_Code + ' ' + DN.DrugNomen_Nick AS Drug_Name
			-- end select
		from
			-- from
			rls.v_DrugNomen DN with(nolock)
			inner join rls.v_Drug D with(nolock) on D.Drug_id = DN.Drug_id
			-- end from
		where
			-- where
			DN.PrepClass_id = 10
			".$where."
			-- end where
		order by
			-- order by
			DN.DrugNomen_Nick, DN.DrugNomen_Code
			-- end order by
		";
		$result = $this->db->query($q, $queryParams);
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 * Получение списка реактивов, допустимых для данного анализатора (по модели) и теста
	 * @param array $data Фильтры
	 * @return array Ассоциативный массив с названиями реагентов
	 */
	function loadReagentListForTest($data) {
		$params = array();
		$params['Analyzer_id'] = $data['Analyzer_id'];
		$params['UslugaComplex_Code'] = $data['UslugaComplex_Code'];
		
		$q = "
		SELECT
			rnr.ReagentNormRate_id
			, dn.DrugNomen_Name
		FROM [lis].[ReagentNormRate] rnr with(nolock)
		JOIN [lis].[Analyzer] a with(nolock) ON a.AnalyzerModel_id = rnr.AnalyzerModel_id 
			AND a.Analyzer_id = :Analyzer_id 
		JOIN rls.v_DrugNomen dn with(nolock) ON dn.DrugNomen_id = rnr.DrugNomen_id
		WHERE
			rnr.UslugaComplex_Code = :UslugaComplex_Code
			AND ISNULL(rnr.ReagentNormRate_Deleted, 1) = 1
		";
		$result = $this->db->query($q, $params);
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 *  получение данных реактива модели анализатора
	 */
	function loadReagentNormRate($filter) {
		$params['ReagentNormRate_id'] = $filter['ReagentNormRate_id'];
		$q = "
			SELECT 
				-- select
				rnr.unit_id, rnr.*, DN.DrugNomen_Name
				-- end select
			FROM 
				-- from
				lis.ReagentNormRate rnr (nolock)
				LEFT JOIN rls.v_DrugNomen DN (nolock) on DN.DrugNomen_id = rnr.DrugNomen_id
				-- end from
			WHERE 
				-- where
				rnr.ReagentNormRate_id = :ReagentNormRate_id
				-- end where
		";
		$result = $this->db->query($q, $params);
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 *  Получение списка реактивов модели-теста анализатора
	 */
	function loadReagentNormRateGrid($filter) {
		$params['AnalyzerModel_id'] = $filter['AnalyzerModel_id'];
		$params['UslugaComplex_Code'] = $filter['UslugaComplex_Code'];
		$q = "
			SELECT 
				-- select
				rnr.*, DN.DrugNomen_Name
				-- end select
			FROM 
				-- from
				lis.ReagentNormRate rnr (nolock)
				LEFT JOIN rls.v_DrugNomen DN (nolock) on DN.DrugNomen_id = rnr.DrugNomen_id
				-- end from
			WHERE 
				-- where
				rnr.AnalyzerModel_id = :AnalyzerModel_id
				AND rnr.UslugaComplex_Code = :UslugaComplex_Code
				AND (rnr.ReagentNormRate_Deleted is null OR rnr.ReagentNormRate_Deleted != 2) -- неудаленные
				-- end where
			order by
					-- order by
					DN.DrugNomen_Name
					-- end order by
		";

		$result = $this->db->query(getLimitSQLPH($q, $filter['start'], $filter['limit']), $filter);
		$result_count = $this->db->query(getCountSQLPH($q), $filter);

		if (is_object($result_count))
		{
			$cnt_arr = $result_count->result('array');
			$count = $cnt_arr[0]['cnt'];
			unset($cnt_arr);
		} else {
			$count = 0;
		}

		if (is_object($result))
		{
			$response = array();
			$response['data'] = $result->result('array');
			$response['totalCount'] = $count;
			return $response;
		} else {
			return false;
		}
	}
}
