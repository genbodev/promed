<?php defined('BASEPATH') or die ('No direct script access allowed');

class DrugMarkup_model extends swPgModel {
	private $DrugMarkup_id;//идентификатор
	private $DrugMarkup_begDT;//дата начала действия
	private $DrugMarkup_endDT;//дата окончания действия
	private $DrugMarkup_MinPrice;//минимальная отпускная цена
	private $DrugMarkup_MaxPrice;//максимальная отпускная цена
	private $DrugMarkup_Wholesale;//размер предельной оптовой надбавки в %
	private $DrugMarkup_Retail;//размер предельной розничной надбавки в %
	private $DrugMarkup_IsNarkoDrug;//признак наркотического препарата
	private $Drugmarkup_Delivery;//зона доставки
	private $pmUser_id;//Идентификатор пользователя системы Промед

	/**
	 * Получение свойства
	 */
	public function getDrugMarkup_id() { return $this->DrugMarkup_id;}

	/**
	 * Установка свойства
	 */
	public function setDrugMarkup_id($value) { $this->DrugMarkup_id = $value; }

	/**
	 * Получение свойства
	 */
	public function getDrugMarkup_begDT() { return $this->DrugMarkup_begDT;}

	/**
	 * Установка свойства
	 */
	public function setDrugMarkup_begDT($value) { $this->DrugMarkup_begDT = $value; }

	/**
	 * Получение свойства
	 */
	public function getDrugMarkup_endDT() { return $this->DrugMarkup_endDT;}

	/**
	 * Установка свойства
	 */
	public function setDrugMarkup_endDT($value) { $this->DrugMarkup_endDT = $value; }

	/**
	 * Получение свойства
	 */
	public function getDrugMarkup_MinPrice() { return $this->DrugMarkup_MinPrice;}

	/**
	 * Установка свойства
	 */
	public function setDrugMarkup_MinPrice($value) { $this->DrugMarkup_MinPrice = $value; }

	/**
	 * Получение свойства
	 */
	public function getDrugMarkup_MaxPrice() { return $this->DrugMarkup_MaxPrice;}

	/**
	 * Установка свойства
	 */
	public function setDrugMarkup_MaxPrice($value) { $this->DrugMarkup_MaxPrice = $value; }

	/**
	 * Получение свойства
	 */
	public function getDrugMarkup_Wholesale() { return $this->DrugMarkup_Wholesale;}

	/**
	 * Установка свойства
	 */
	public function setDrugMarkup_Wholesale($value) { $this->DrugMarkup_Wholesale = $value; }

	/**
	 * Получение свойства
	 */
	public function getDrugMarkup_Retail() { return $this->DrugMarkup_Retail;}

	/**
	 * Установка свойства
	 */
	public function setDrugMarkup_Retail($value) { $this->DrugMarkup_Retail = $value; }

	/**
	 * Получение свойства
	 */
	public function getDrugMarkup_IsNarkoDrug() { return $this->DrugMarkup_IsNarkoDrug;}

	/**
	 * Установка свойства
	 */
	public function setDrugMarkup_IsNarkoDrug($value) { $this->DrugMarkup_IsNarkoDrug = $value; }

	/**
	 * Получение свойства
	 */
	public function getDrugmarkup_Delivery() { return $this->Drugmarkup_Delivery;}

	/**
	 * Установка свойства
	 */
	public function setDrugmarkup_Delivery($value) { $this->Drugmarkup_Delivery = $value; }

	/**
	 * Получение свойства
	 */
	public function getpmUser_id() { return $this->pmUser_id;}

	/**
	 * Установка свойства
	 */
	public function setpmUser_id($value) { $this->pmUser_id = $value; }

	/**
	 * Конструктор
	 */
	function __construct(){
		if (isset($_SESSION['pmuser_id'])) {
			$this->setpmUser_id($_SESSION['pmuser_id']);
		} else {
			throw new Exception('Значение pmuser_id не установлено в текущей сессии (не выполнен вход в Промед?)');
		}
	}

	/**
	 * Загрузка
	 */
	function load() {
		$q = "
			select
				DrugMarkup_id as \"DrugMarkup_id\",
				DrugMarkup_begDT as \"DrugMarkup_begDT\", 
				DrugMarkup_endDT as \"DrugMarkup_endDT\", 
				DrugMarkup_MinPrice as \"DrugMarkup_MinPrice\", 
				DrugMarkup_MaxPrice as \"DrugMarkup_MaxPrice\", 
				DrugMarkup_Wholesale as \"DrugMarkup_Wholesale\", 
				DrugMarkup_Retail as \"DrugMarkup_Retail\", 
				DrugMarkup_IsNarkoDrug as \"DrugMarkup_IsNarkoDrug\", 
				Drugmarkup_Delivery as \"Drugmarkup_Delivery\"
			from
				dbo.v_DrugMarkup
			where
				DrugMarkup_id = :DrugMarkup_id
		";
		$r = $this->db->query($q, array('DrugMarkup_id' => $this->DrugMarkup_id));
		if ( is_object($r) ) {
			$r = $r->result('array');
			if (isset($r[0])) {
				$this->DrugMarkup_id = $r[0]['DrugMarkup_id'];
				$this->DrugMarkup_begDT = $r[0]['DrugMarkup_begDT'];
				$this->DrugMarkup_endDT = $r[0]['DrugMarkup_endDT'];
				$this->DrugMarkup_MinPrice = $r[0]['DrugMarkup_MinPrice'];
				$this->DrugMarkup_MaxPrice = $r[0]['DrugMarkup_MaxPrice'];
				$this->DrugMarkup_Wholesale = $r[0]['DrugMarkup_Wholesale'];
				$this->DrugMarkup_Retail = $r[0]['DrugMarkup_Retail'];
				$this->DrugMarkup_IsNarkoDrug = $r[0]['DrugMarkup_IsNarkoDrug'];
				$this->Drugmarkup_Delivery = $r[0]['Drugmarkup_Delivery'];
				return $r;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка
	 */
	function loadList($filter) {
		$where = array();
		$p = array();
		if (isset($filter['DrugMarkup_id']) && $filter['DrugMarkup_id']) {
			$where[] = 'v_DrugMarkup.DrugMarkup_id = :DrugMarkup_id';
			$p['DrugMarkup_id'] = $filter['DrugMarkup_id'];
		}
		if (isset($filter['DrugMarkup_begDT']) && $filter['DrugMarkup_begDT']) {
			$where[] = 'v_DrugMarkup.DrugMarkup_begDT = :DrugMarkup_begDT';
			$p['DrugMarkup_begDT'] = $filter['DrugMarkup_begDT'];
		}
		if (isset($filter['DrugMarkup_endDT']) && $filter['DrugMarkup_endDT']) {
			$where[] = 'v_DrugMarkup.DrugMarkup_endDT = :DrugMarkup_endDT';
			$p['DrugMarkup_endDT'] = $filter['DrugMarkup_endDT'];
		}
		if (isset($filter['DrugMarkup_MinPrice']) && $filter['DrugMarkup_MinPrice']) {
			$where[] = 'v_DrugMarkup.DrugMarkup_MinPrice = :DrugMarkup_MinPrice';
			$p['DrugMarkup_MinPrice'] = $filter['DrugMarkup_MinPrice'];
		}
		if (isset($filter['DrugMarkup_MaxPrice']) && $filter['DrugMarkup_MaxPrice']) {
			$where[] = 'v_DrugMarkup.DrugMarkup_MaxPrice = :DrugMarkup_MaxPrice';
			$p['DrugMarkup_MaxPrice'] = $filter['DrugMarkup_MaxPrice'];
		}
		if (isset($filter['DrugMarkup_Wholesale']) && $filter['DrugMarkup_Wholesale']) {
			$where[] = 'v_DrugMarkup.DrugMarkup_Wholesale = :DrugMarkup_Wholesale';
			$p['DrugMarkup_Wholesale'] = $filter['DrugMarkup_Wholesale'];
		}
		if (isset($filter['DrugMarkup_Retail']) && $filter['DrugMarkup_Retail']) {
			$where[] = 'v_DrugMarkup.DrugMarkup_Retail = :DrugMarkup_Retail';
			$p['DrugMarkup_Retail'] = $filter['DrugMarkup_Retail'];
		}
		if (isset($filter['DrugMarkup_IsNarkoDrug']) && $filter['DrugMarkup_IsNarkoDrug']) {
			$where[] = 'v_DrugMarkup.DrugMarkup_IsNarkoDrug = :DrugMarkup_IsNarkoDrug';
			$p['DrugMarkup_IsNarkoDrug'] = $filter['DrugMarkup_IsNarkoDrug'];
		}
		if (isset($filter['Drugmarkup_Delivery']) && $filter['Drugmarkup_Delivery']) {
			$where[] = 'v_DrugMarkup.Drugmarkup_Delivery = :Drugmarkup_Delivery';
			$p['Drugmarkup_Delivery'] = $filter['Drugmarkup_Delivery'];
		}
		$where_clause = implode(' and ', $where);
		if (strlen($where_clause)) {
			$where_clause = 'where '.$where_clause;
		}
		$q = "
			select
				v_DrugMarkup.DrugMarkup_id as \"DrugMarkup_id\",
				to_char(v_DrugMarkup.DrugMarkup_begDT, 'dd.mm.yyyy') as \"DrugMarkup_begDT\",
				to_char(v_DrugMarkup.DrugMarkup_endDT, 'dd.mm.yyyy') as \"DrugMarkup_endDT\",
				v_DrugMarkup.DrugMarkup_MinPrice as \"DrugMarkup_MinPrice\", 
				v_DrugMarkup.DrugMarkup_MaxPrice as \"DrugMarkup_MaxPrice\", 
				v_DrugMarkup.DrugMarkup_Wholesale as \"DrugMarkup_Wholesale\", 
				v_DrugMarkup.DrugMarkup_Retail as \"DrugMarkup_Retail\", 
				v_DrugMarkup.DrugMarkup_IsNarkoDrug as \"DrugMarkup_IsNarkoDrug\", 
				v_DrugMarkup.Drugmarkup_Delivery as \"Drugmarkup_Delivery\",
				DrugMarkup_IsNarkoDrug_ref.YesNo_Name as \"DrugMarkup_IsNarkoDrug_Name\",
				pmMediaData.pmMediaData_Comment as \"File_Name\"
			from
				dbo.v_DrugMarkup
				LEFT JOIN dbo.v_YesNo DrugMarkup_IsNarkoDrug_ref  ON DrugMarkup_IsNarkoDrug_ref.YesNo_id = v_DrugMarkup.DrugMarkup_IsNarkoDrug
				LEFT JOIN LATERAL (
					select
						pmMediaData_Comment as pmMediaData_Comment
					from
						pmMediaData 
					where
						pmMediaData_ObjectName = 'DrugMarkup' and
						pmMediaData_ObjectID = v_DrugMarkup.DrugMarkup_id
					order by
						pmMediaData_id
					limit 1
				) pmMediaData ON TRUE
			{$where_clause}
		";
		$result = $this->db->query($q, $filter);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Сохранение
	 */
	function save() {
		$procedure = 'p_DrugMarkup_ins';
		if ( $this->DrugMarkup_id > 0 ) {
			$procedure = 'p_DrugMarkup_upd';
		}
		$q = "
			select
				DrugMarkup_id as \"DrugMarkup_id\",
			    Error_Code as \"Error_Code\",
			    Error_Message as \"Error_Msg\"
			from dbo." . $procedure . "(
				DrugMarkup_id := :DrugMarkup_id,
				DrugMarkup_begDT := :DrugMarkup_begDT,
				DrugMarkup_endDT := :DrugMarkup_endDT,
				DrugMarkup_MinPrice := :DrugMarkup_MinPrice,
				DrugMarkup_MaxPrice := :DrugMarkup_MaxPrice,
				DrugMarkup_Wholesale := :DrugMarkup_Wholesale,
				DrugMarkup_Retail := :DrugMarkup_Retail,
				DrugMarkup_IsNarkoDrug := :DrugMarkup_IsNarkoDrug,
				Drugmarkup_Delivery := :Drugmarkup_Delivery,
				pmUser_id := :pmUser_id
			)
		";
		$p = array(
			'DrugMarkup_id' => $this->DrugMarkup_id,
			'DrugMarkup_begDT' => $this->DrugMarkup_begDT,
			'DrugMarkup_endDT' => $this->DrugMarkup_endDT,
			'DrugMarkup_MinPrice' => $this->DrugMarkup_MinPrice,
			'DrugMarkup_MaxPrice' => $this->DrugMarkup_MaxPrice,
			'DrugMarkup_Wholesale' => $this->DrugMarkup_Wholesale,
			'DrugMarkup_Retail' => $this->DrugMarkup_Retail,
			'DrugMarkup_IsNarkoDrug' => $this->DrugMarkup_IsNarkoDrug,
			'Drugmarkup_Delivery' => $this->Drugmarkup_Delivery,
			'pmUser_id' => $this->pmUser_id
		);
		$r = $this->db->query($q, $p);
		if ( is_object($r) ) {
			$result = $r->result('array');
			$this->DrugMarkup_id = $result[0]['DrugMarkup_id'];
		} else {
			log_message('error', var_export(array('q' => $q, 'p' => $p, 'e' => sqlsrv_errors()), true));
			$result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}
		return $result;
	}

	/**
	 * Удаление
	 */
	function delete() {
		$q = "
			delete from
				pmMediaData
			where
				pmMediaData_ObjectName = 'DrugMarkup' and
				pmMediaData_ObjectID = :DrugMarkup_id;
		";
		$r = $this->db->query($q, array(
			'DrugMarkup_id' => $this->DrugMarkup_id
		));

        $q = "
			select
				Error_Code as \"Error_Code\",
			    Error_Message as \"Error_Msg\"
			from dbo.p_DrugMarkup_del(
			    DrugMarkup_id := :DrugMarkup_id	
		)";
		$r = $this->db->query($q, array(
			'DrugMarkup_id' => $this->DrugMarkup_id
		));
		if ( is_object($r) ) {
			return $r->result('array');
		} else {
			return false;
		}
	}
}
