<?php defined('BASEPATH') or die ('No direct script access allowed');

class DrugListRequest_model extends swModel {
	private $DrugListRequest_id;//DrugListRequest_id
	private $DrugRequestProperty_id;//Список медикаментов для заявки
	private $DrugComplexMnn_id;//Комплексное МНН
	private $DrugListRequest_Price;//Цена
	private $DrugTorgUse_id;//Способ использования торгового наименования
	private $DrugListRequest_Code;//DrugListRequest_Code
	private $pmUser_id;//Идентификатор пользователя системы Промед

	/**
	 * Comment
	 */
	public function getDrugListRequest_id() { return $this->DrugListRequest_id;}
	/**
	 * Comment
	 */
	public function setDrugListRequest_id($value) { $this->DrugListRequest_id = $value; }

	/**
	 * Comment
	 */
	public function getDrugRequestProperty_id() { return $this->DrugRequestProperty_id;}
	/**
	 * Comment
	 */
	public function setDrugRequestProperty_id($value) { $this->DrugRequestProperty_id = $value; }

	/**
	 * Comment
	 */
	public function getDrugComplexMnn_id() { return $this->DrugComplexMnn_id;}
	/**
	 * Comment
	 */
	public function setDrugComplexMnn_id($value) { $this->DrugComplexMnn_id = $value; }

	/**
	 * Comment
	 */
	public function getDrugListRequest_Price() { return $this->DrugListRequest_Price;}
	/**
	 * Comment
	 */
	public function setDrugListRequest_Price($value) { $this->DrugListRequest_Price = $value; }

	/**
	 * Comment
	 */
	public function getDrugTorgUse_id() { return $this->DrugTorgUse_id;}
	/**
	 * Comment
	 */
	public function setDrugTorgUse_id($value) { $this->DrugTorgUse_id = $value; }

	/**
	 * Comment
	 */
	public function getDrugListRequest_Code() { return $this->DrugListRequest_Code;}
	/**
	 * Comment
	 */
	public function setDrugListRequest_Code($value) { $this->DrugListRequest_Code = $value; }

	/**
	 * Comment
	 */
	public function getpmUser_id() { return $this->pmUser_id;}
	/**
	 * Comment
	 */
	public function setpmUser_id($value) { $this->pmUser_id = $value; }

	/**
	 * Comment
	 */
	function __construct(){
		if (isset($_SESSION['pmuser_id'])) {
			$this->setpmUser_id($_SESSION['pmuser_id']);
		} else {
			throw new Exception('Значение pmuser_id не установлено в текущей сессии (не выполнен вход в Промед?)');
		}
	}

	/**
	 * Comment
	 */
	function load() {
		$q = "
			select
				DrugListRequest_id, DrugRequestProperty_id, DrugComplexMnn_id, DrugListRequest_Price, DrugTorgUse_id, DrugListRequest_Code
			from
				dbo.v_DrugListRequest with (nolock)
			where
				DrugListRequest_id = :DrugListRequest_id
		";
		$r = $this->db->query($q, array('DrugListRequest_id' => $this->DrugListRequest_id));
		if ( is_object($r) ) {
			$r = $r->result('array');
			if (isset($r[0])) {
				$this->DrugListRequest_id = $r[0]['DrugListRequest_id'];
				$this->DrugRequestProperty_id = $r[0]['DrugRequestProperty_id'];
				$this->DrugComplexMnn_id = $r[0]['DrugComplexMnn_id'];
				$this->DrugListRequest_Price = $r[0]['DrugListRequest_Price'];
				$this->DrugTorgUse_id = $r[0]['DrugTorgUse_id'];
				$this->DrugListRequest_Code = $r[0]['DrugListRequest_Code'];
				return $r;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Comment
	 */
	function loadList($filter) {
		$where = array();
		$p = array();
		if (isset($filter['DrugListRequest_id']) && $filter['DrugListRequest_id']) {
			$where[] = 'v_DrugListRequest.DrugListRequest_id = :DrugListRequest_id';
			$p['DrugListRequest_id'] = $filter['DrugListRequest_id'];
		}
		if (isset($filter['DrugRequestProperty_id']) && $filter['DrugRequestProperty_id']) {
			$where[] = 'v_DrugListRequest.DrugRequestProperty_id = :DrugRequestProperty_id';
			$p['DrugRequestProperty_id'] = $filter['DrugRequestProperty_id'];
		}
		if (isset($filter['DrugComplexMnn_id']) && $filter['DrugComplexMnn_id']) {
			$where[] = 'v_DrugListRequest.DrugComplexMnn_id = :DrugComplexMnn_id';
			$p['DrugComplexMnn_id'] = $filter['DrugComplexMnn_id'];
		}
		if (isset($filter['DrugListRequest_Price']) && $filter['DrugListRequest_Price']) {
			$where[] = 'v_DrugListRequest.DrugListRequest_Price = :DrugListRequest_Price';
			$p['DrugListRequest_Price'] = $filter['DrugListRequest_Price'];
		}
		if (isset($filter['DrugTorgUse_id']) && $filter['DrugTorgUse_id']) {
			$where[] = 'v_DrugListRequest.DrugTorgUse_id = :DrugTorgUse_id';
			$p['DrugTorgUse_id'] = $filter['DrugTorgUse_id'];
		}
		if (isset($filter['DrugListRequest_Code']) && $filter['DrugListRequest_Code']) {
			$where[] = 'v_DrugListRequest.DrugListRequest_Code = :DrugListRequest_Code';
			$p['DrugListRequest_Code'] = $filter['DrugListRequest_Code'];
		}
		$where_clause = implode(' AND ', $where);
		if (strlen($where_clause)) {
			$where_clause = 'WHERE '.$where_clause;
		}
		$q = "
			SELECT
				v_DrugListRequest.DrugListRequest_id, v_DrugListRequest.DrugRequestProperty_id, v_DrugListRequest.DrugComplexMnn_id, v_DrugListRequest.DrugListRequest_Price, v_DrugListRequest.DrugTorgUse_id, v_DrugListRequest.DrugListRequest_Code
				,DrugRequestProperty_id_ref.DrugRequestProperty_Name DrugRequestProperty_id_Name, DrugTorgUse_id_ref.DrugTorgUse_Name DrugTorgUse_id_Name
			FROM
				dbo.v_DrugListRequest WITH (NOLOCK)
				LEFT JOIN dbo.v_DrugRequestProperty DrugRequestProperty_id_ref WITH (NOLOCK) ON DrugRequestProperty_id_ref.DrugRequestProperty_id = v_DrugListRequest.DrugRequestProperty_id
				LEFT JOIN rls.v_DrugComplexMnn DrugComplexMnn_id_ref WITH (NOLOCK) ON DrugComplexMnn_id_ref.DrugComplexMnn_id = v_DrugListRequest.DrugComplexMnn_id
				LEFT JOIN dbo.v_DrugTorgUse DrugTorgUse_id_ref WITH (NOLOCK) ON DrugTorgUse_id_ref.DrugTorgUse_id = v_DrugListRequest.DrugTorgUse_id
			$where_clause
		";
		$result = $this->db->query($q, $filter);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Comment
	 */
	function save() {
		$procedure = 'p_DrugListRequest_ins';
		if ( $this->DrugListRequest_id > 0 ) {
			$procedure = 'p_DrugListRequest_upd';
		}
		$q = "
			declare
				@DrugListRequest_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @DrugListRequest_id = :DrugListRequest_id;
			exec dbo." . $procedure . "
				@DrugListRequest_id = @DrugListRequest_id output,
				@DrugRequestProperty_id = :DrugRequestProperty_id,
				@DrugComplexMnn_id = :DrugComplexMnn_id,
				@DrugListRequest_Price = :DrugListRequest_Price,
				@DrugTorgUse_id = :DrugTorgUse_id,
				@DrugListRequest_Code = :DrugListRequest_Code,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @DrugListRequest_id as DrugListRequest_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$p = array(
			'DrugListRequest_id' => $this->DrugListRequest_id,
			'DrugRequestProperty_id' => $this->DrugRequestProperty_id,
			'DrugComplexMnn_id' => $this->DrugComplexMnn_id,
			'DrugListRequest_Price' => $this->DrugListRequest_Price,
			'DrugTorgUse_id' => $this->DrugTorgUse_id,
			'DrugListRequest_Code' => $this->DrugListRequest_Code,
			'pmUser_id' => $this->pmUser_id
		);
		$r = $this->db->query($q, $p);
		if ( is_object($r) ) {
		    $result = $r->result('array');
		    $this->DrugListRequest_id = $result[0]['DrugListRequest_id'];
		} else {
			log_message('error', var_export(array('q' => $q, 'p' => $p, 'e' => sqlsrv_errors()), true));
			$result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}
		return $result;
	}

	/**
	 * Comment
	 */
	function delete() {
		$q = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec dbo.p_DrugListRequest_del
				@DrugListRequest_id = :DrugListRequest_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$r = $this->db->query($q, array(
			'DrugListRequest_id' => $this->DrugListRequest_id
		));
		if ( is_object($r) ) {
			return $r->result('array');
		} else {
			return false;
		}
	}
}