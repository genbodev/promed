<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Модель для объектов привязки реагента к модели анализатора
 * 
 * @package      common
 * @access       public
 * @author       Arslanov Azat
 */
class ReagentModel_model extends swModel {
	private $ReagentModel_id;//
	private $AnalyzerModel_id;//Модель анализатора
	private $DrugNomen_id;//Реагент
	private $pmUser_id;//Идентификатор пользователя системы Промед
	
	/**
	 * id 
	 */
	public function getReagentModel_id() { return $this->ReagentModel_id;}
	/**
	 * id
	 */
	public function setReagentModel_id($value) { $this->ReagentModel_id = $value; }
	/**
	 * модель анализатора
	 */
	public function getAnalyzerModel_id() { return $this->AnalyzerModel_id;}
	/**
	 * модель анализатора
	 */
	public function setAnalyzerModel_id($value) { $this->AnalyzerModel_id = $value; }
	/**
	 * id лек-ва номенклатурного справочника
	 */
	public function getDrugNomen_id() { return $this->DrugNomen_id;}
	/**
	 * id лек-ва номенклатурного справочника
	 */
	public function setDrugNomen_id($value) { $this->DrugNomen_id = $value; }
	/**
	 * кто виноват
	 */
	public function getpmUser_id() { return $this->pmUser_id;}
	/**
	 * кто виноват
	 */
	public function setpmUser_id($value) { $this->pmUser_id = $value; }

	/**
	 * TO-DO: описать
	 */
	function __construct(){
		if (isset($_SESSION['pmuser_id'])) {
			$this->setpmUser_id($_SESSION['pmuser_id']);
		} else {
			throw new Exception('Значение pmuser_id не установлено в текущей сессии (не выполнен вход в Промед?)');
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
	 *  получение реактива модели анализатора
	 */
	function loadReagentModel($filter) {
		$params['ReagentModel_id'] = $filter['ReagentModel_id'];
		$q = "
			SELECT 
				-- select
				rm.*, DN.DrugNomen_Name
				-- end select
			FROM 
				-- from
				lis.ReagentModel rm (nolock)
				LEFT JOIN rls.v_DrugNomen DN (nolock) on DN.DrugNomen_id = rm.DrugNomen_id
				-- end from
			WHERE 
				-- where
				rm.ReagentModel_id = :ReagentModel_id
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
	 *  Получение списка реактивов модели анализатора
	 */
	function loadReagentModelGrid($filter) {
		$params['AnalyzerModel_id'] = $filter['AnalyzerModel_id'];
		$q = "
			SELECT 
				-- select
				rm.*, DN.DrugNomen_Name
				-- end select
			FROM 
				-- from
				lis.ReagentModel rm (nolock)
				LEFT JOIN rls.v_DrugNomen DN (nolock) on DN.DrugNomen_id = rm.DrugNomen_id
				-- end from
			WHERE 
				-- where
				rm.AnalyzerModel_id = :AnalyzerModel_id
				AND (rm.ReagentModel_Deleted is null OR rm.ReagentModel_Deleted != 2) -- неудаленные
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
	
	/**
	 * Сохранение реагента для модели анализатора
	 */
	function saveReagentModel() {
		$procedure = 'p_ReagentModel_ins';
		if ( $this->ReagentModel_id > 0 ) {
			$procedure = 'p_ReagentModel_upd';
		}
		$q = "
			declare
				@ReagentModel_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @ReagentModel_id = :ReagentModel_id;
			exec lis." . $procedure . "
				@ReagentModel_id = @ReagentModel_id output,
				@AnalyzerModel_id = :AnalyzerModel_id,
				@DrugNomen_id = :DrugNomen_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ReagentModel_id as ReagentModel_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$p = array(
			'ReagentModel_id' => $this->ReagentModel_id,
			'AnalyzerModel_id' => $this->AnalyzerModel_id,
			'DrugNomen_id' => $this->DrugNomen_id,
			'pmUser_id' => $this->pmUser_id,
		);
		$r = $this->db->query($q, $p);
		if ( is_object($r) ) {
			$result = $r->result('array');
			$this->ReagentModel_id = $result[0]['ReagentModel_id'];
		}
		else {
			log_message('error', var_export(array('q' => $q, 'p' => $p, 'e' => sqlsrv_errors()), true));
			$result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}
		return $result;
	}

	/**
	 * Удаление реагента модели анализатора
	 */
	function delete() {
		$tests_count = $this->getFirstResultFromQuery(
			'SELECT COUNT(1)
				FROM lis.v_AnalyzerTest at with(nolock)
				LEFT JOIN lis.ReagentNormRate rnr with(nolock) ON at.AnalyzerTest_id = rnr.AnalyzerTest_id
				LEFT JOIN lis.ReagentModel rm with(nolock) ON rnr.ReagentModel_id = rm.ReagentModel_id
				WHERE rm.ReagentModel_id = :ReagentModel_id',
			array('ReagentModel_id' => $this->ReagentModel_id)
		);
		if ($tests_count > 0) {
			throw new Exception("Нельзя удалить данный реактив, так как на него уже заведены тесты");
		}

		$q = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec lis.p_ReagentModel_del
				@ReagentModel_id = :ReagentModel_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$r = $this->db->query($q, array(
			'ReagentModel_id' => $this->ReagentModel_id
		));
		if ( is_object($r) ) {
			return $r->result('array');
		}
		else {
			return false;
		}
	}
}
