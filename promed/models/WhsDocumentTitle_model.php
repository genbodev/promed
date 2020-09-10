<?php
/**
 * Класс
 */
defined('BASEPATH') or die('No direct script access allowed');
/**
 * Класс
 */
class WhsDocumentTitle_model extends swModel {

	private $WhsDocumentTitle_id; //WhsDocumentTitle_id
	private $WhsDocumentTitle_Name; //Наименование документа
	private $WhsDocumentTitleType_id; //Тип правоустанавливающего документа
	private $WhsDocumentStatusType_id; //Статус документа
	private $WhsDocumentTitle_begDate; //Дата начала действия
	private $WhsDocumentTitle_endDate; //Дата окончания дийствия
	private $pmUser_id; //Идентификатор пользователя системы Промед
	private $WhsDocumentUc_id; //Идентификатор родительского ГК

	/**
	 * Функция
	 * @return type 
	 */

	public function getWhsDocumentTitle_id() {
		return $this->WhsDocumentTitle_id;
	}

	/**
	 * Функция
	 * @param type $value 
	 */
	public function setWhsDocumentTitle_id($value) {
		$this->WhsDocumentTitle_id = $value;
	}

	/**
	 * Функция
	 * @return type 
	 */
	public function getWhsDocumentTitle_Name() {
		return $this->WhsDocumentTitle_Name;
	}

	/**
	 * Функция
	 * @param type $value 
	 */
	public function setWhsDocumentTitle_Name($value) {
		$this->WhsDocumentTitle_Name = $value;
	}

	/**
	 * Функция
	 * @return type 
	 */
	public function getWhsDocumentTitleType_id() {
		return $this->WhsDocumentTitleType_id;
	}

	/**
	 * Функция
	 * @param type $value 
	 */
	public function setWhsDocumentTitleType_id($value) {
		$this->WhsDocumentTitleType_id = $value;
	}

	/**
	 * Функция
	 * @return type 
	 */
	public function getWhsDocumentStatusType_id() {
		return $this->WhsDocumentStatusType_id;
	}

	/**
	 * Функция
	 * @param type $value 
	 */
	public function setWhsDocumentStatusType_id($value) {
		$this->WhsDocumentStatusType_id = $value;
	}

	/**
	 * Функция
	 * @return type 
	 */
	public function getWhsDocumentTitle_begDate() {
		return $this->WhsDocumentTitle_begDate;
	}

	/**
	 * Функция
	 * @param type $value 
	 */
	public function setWhsDocumentTitle_begDate($value) {
		$this->WhsDocumentTitle_begDate = $value;
	}

	/**
	 * Функция
	 * @return type 
	 */
	public function getWhsDocumentTitle_endDate() {
		return $this->WhsDocumentTitle_endDate;
	}

	/**
	 * Функция
	 * @param type $value 
	 */
	public function setWhsDocumentTitle_endDate($value) {
		$this->WhsDocumentTitle_endDate = $value;
	}

	/**
	 * Функция
	 * @return type 
	 */
	public function getpmUser_id() {
		return $this->pmUser_id;
	}

	/**
	 * Функция
	 * @param type $value 
	 */
	public function setpmUser_id($value) {
		$this->pmUser_id = $value;
	}

	/**
	 * Функция
	 * @return type 
	 */
	public function getWhsDocumentUc_id() {
		return $this->WhsDocumentUc_id;
	}

	/**
	 * Функция
	 * @param type $value 
	 */
	public function setWhsDocumentUc_id($value) {
		$this->WhsDocumentUc_id = $value;
	}

	/**
	 * Функция 
	 */
	function __construct() {
		if (isset($_SESSION['pmuser_id'])) {
			$this->setpmUser_id($_SESSION['pmuser_id']);
		} else {
			throw new Exception('Значение pmuser_id не установлено в текущей сессии (не выполнен вход в Промед?)');
		}
	}

	/**
	 * Функция
	 * @return type 
	 */
	function load() {
		$q = "
			select
				WhsDocumentTitle_id, WhsDocumentTitle_Name, WhsDocumentTitleType_id, WhsDocumentStatusType_id, WhsDocumentTitle_begDate, WhsDocumentTitle_endDate, WhsDocumentUc_id
			from
				dbo.v_WhsDocumentTitle with(nolock)
			where
				WhsDocumentTitle_id = :WhsDocumentTitle_id
		";
		$r = $this->db->query($q, array('WhsDocumentTitle_id' => $this->WhsDocumentTitle_id));
		if (is_object($r)) {
			$r = $r->result('array');
			if (isset($r[0])) {
				$this->WhsDocumentTitle_id = $r[0]['WhsDocumentTitle_id'];
				$this->WhsDocumentTitle_Name = $r[0]['WhsDocumentTitle_Name'];
				$this->WhsDocumentTitleType_id = $r[0]['WhsDocumentTitleType_id'];
				$this->WhsDocumentStatusType_id = $r[0]['WhsDocumentStatusType_id'];
				$this->WhsDocumentTitle_begDate = $r[0]['WhsDocumentTitle_begDate'];
				$this->WhsDocumentTitle_endDate = $r[0]['WhsDocumentTitle_endDate'];
				$this->WhsDocumentUc_id = $r[0]['WhsDocumentUc_id'];
				return $r;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Функция
	 * @param type $filter
	 * @return type 
	 */
	function loadList($filter) {
		$where = array();
		$p = array();
		if (isset($filter['WhsDocumentTitle_id']) && $filter['WhsDocumentTitle_id']) {
			$where[] = 'v_WhsDocumentTitle.WhsDocumentTitle_id = :WhsDocumentTitle_id';
			$p['WhsDocumentTitle_id'] = $filter['WhsDocumentTitle_id'];
		}
		if (isset($filter['WhsDocumentSupply_id']) && $filter['WhsDocumentSupply_id']) {
			$where[] = 'v_WhsDocumentTitle.WhsDocumentUc_id = :WhsDocumentSupply_id';
			$p['WhsDocumentSupply_id'] = $filter['WhsDocumentSupply_id'];
		}
		if (isset($filter['WhsDocumentTitle_Name']) && $filter['WhsDocumentTitle_Name']) {
			$where[] = 'v_WhsDocumentTitle.WhsDocumentTitle_Name = :WhsDocumentTitle_Name';
			$p['WhsDocumentTitle_Name'] = $filter['WhsDocumentTitle_Name'];
		}
		if (isset($filter['WhsDocumentTitleType_id']) && $filter['WhsDocumentTitleType_id']) {
			$where[] = 'v_WhsDocumentTitle.WhsDocumentTitleType_id = :WhsDocumentTitleType_id';
			$p['WhsDocumentTitleType_id'] = $filter['WhsDocumentTitleType_id'];
		}
		if (isset($filter['WhsDocumentStatusType_id']) && $filter['WhsDocumentStatusType_id']) {
			$where[] = 'v_WhsDocumentTitle.WhsDocumentStatusType_id = :WhsDocumentStatusType_id';
			$p['WhsDocumentStatusType_id'] = $filter['WhsDocumentStatusType_id'];
		}
		if (isset($filter['WhsDocumentTitle_begDate']) && $filter['WhsDocumentTitle_begDate']) {
			$where[] = 'v_WhsDocumentTitle.WhsDocumentTitle_begDate = :WhsDocumentTitle_begDate';
			$p['WhsDocumentTitle_begDate'] = $filter['WhsDocumentTitle_begDate'];
		}
		if (isset($filter['WhsDocumentTitle_endDate']) && $filter['WhsDocumentTitle_endDate']) {
			$where[] = 'v_WhsDocumentTitle.WhsDocumentTitle_endDate = :WhsDocumentTitle_endDate';
			$p['WhsDocumentTitle_endDate'] = $filter['WhsDocumentTitle_endDate'];
		}
		if (isset($filter['Year']) && $filter['Year']) {
			$where[] = '(DATEPART(YEAR,v_WhsDocumentTitle.WhsDocumentTitle_begDate) <= :Year or v_WhsDocumentTitle.WhsDocumentTitle_begDate is null)';
			$where[] = '(DATEPART(YEAR,v_WhsDocumentTitle.WhsDocumentTitle_endDate) >= :Year or v_WhsDocumentTitle.WhsDocumentTitle_endDate is null)';
			$p['Year'] = $filter['Year'];
		}
		$where_clause = implode(' AND ', $where);
		if (strlen($where_clause)) {
			$where_clause = 'WHERE ' . $where_clause;
		}
		$q = "
			SELECT
				v_WhsDocumentTitle.WhsDocumentTitle_id, v_WhsDocumentTitle.WhsDocumentTitle_Name, v_WhsDocumentTitle.WhsDocumentTitleType_id, v_WhsDocumentTitle.WhsDocumentStatusType_id,
				CONVERT(varchar(10), v_WhsDocumentTitle.WhsDocumentTitle_begDate, 104) WhsDocumentTitle_begDate,
				CONVERT(varchar(10), v_WhsDocumentTitle.WhsDocumentTitle_endDate, 104) WhsDocumentTitle_endDate,				
				WhsDocumentTitleType_id_ref.WhsDocumentTitleType_Name WhsDocumentTitleType_id_Name,
				WhsDocumentStatusType_id_ref.WhsDocumentStatusType_Name WhsDocumentStatusType_id_Name,
				tariff.WhsDocumentTitleTariff_id,
				tariff.UslugaComplexTariff_Name
			FROM
				dbo.v_WhsDocumentTitle WITH (NOLOCK)
				LEFT JOIN dbo.v_WhsDocumentTitleType WhsDocumentTitleType_id_ref WITH (NOLOCK) ON WhsDocumentTitleType_id_ref.WhsDocumentTitleType_id = v_WhsDocumentTitle.WhsDocumentTitleType_id
				LEFT JOIN dbo.v_WhsDocumentStatusType WhsDocumentStatusType_id_ref WITH (NOLOCK) ON WhsDocumentStatusType_id_ref.WhsDocumentStatusType_id = v_WhsDocumentTitle.WhsDocumentStatusType_id
				outer apply (
					select top 1
						wdtt.WhsDocumentTitleTariff_id,
						(
							cast(uct.UslugaComplexTariff_Tariff as varchar) + ' руб. за ' +
							uc.UslugaComplex_Name + ' ' +
							convert(varchar(10), uct.UslugaComplexTariff_begDate, 104) +
							isnull(' - ' + convert(varchar(10), uct.UslugaComplexTariff_endDate, 104), '')
						) as UslugaComplexTariff_Name
					from
						v_WhsDocumentTitleTariff wdtt with (nolock)
						left join v_UslugaComplexTariff uct with (nolock) on uct.UslugaComplexTariff_id = wdtt.UslugaComplexTariff_id
						left join v_UslugaComplex uc with (nolock) on uc.UslugaComplex_id = uct.UslugaComplex_id
					where
						wdtt.WhsDocumentTitle_id = v_WhsDocumentTitle.WhsDocumentTitle_id
				) tariff
			$where_clause
		";
		$result = $this->db->query($q, $filter);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Функция
	 * @return array 
	 */
	function save() {
		$procedure = 'p_WhsDocumentTitle_ins';
		if ($this->WhsDocumentTitle_id > 0) {
			$procedure = 'p_WhsDocumentTitle_upd';
		}
		$q = "
			declare
				@WhsDocumentTitle_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @WhsDocumentTitle_id = :WhsDocumentTitle_id;
			exec dbo." . $procedure . "
				@WhsDocumentTitle_id = @WhsDocumentTitle_id output,
				@WhsDocumentTitle_Name = :WhsDocumentTitle_Name,
				@WhsDocumentTitleType_id = :WhsDocumentTitleType_id,
				@WhsDocumentStatusType_id = :WhsDocumentStatusType_id,
				@WhsDocumentTitle_begDate = :WhsDocumentTitle_begDate,
				@WhsDocumentTitle_endDate = :WhsDocumentTitle_endDate,
				@WhsDocumentUc_id = :WhsDocumentUc_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @WhsDocumentTitle_id as WhsDocumentTitle_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$p = array(
			'WhsDocumentTitle_id' => $this->WhsDocumentTitle_id,
			'WhsDocumentTitle_Name' => $this->WhsDocumentTitle_Name,
			'WhsDocumentTitleType_id' => $this->WhsDocumentTitleType_id,
			'WhsDocumentStatusType_id' => $this->WhsDocumentStatusType_id,
			'WhsDocumentTitle_begDate' => $this->WhsDocumentTitle_begDate,
			'WhsDocumentTitle_endDate' => $this->WhsDocumentTitle_endDate,
			'WhsDocumentUc_id' => $this->WhsDocumentUc_id,
			'pmUser_id' => $this->pmUser_id,
		);
		$r = $this->db->query($q, $p);
		if (is_object($r)) {
			$result = $r->result('array');
			$this->WhsDocumentTitle_id = $result[0]['WhsDocumentTitle_id'];
		} else {
			log_message('error', var_export(array('q' => $q, 'p' => $p, 'e' => sqlsrv_errors()), true));
			$result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}
		return $result;
	}

	/**
	 * Сохранение связи между документом и тарифом
	 */
	function saveWhsDocumentTitleTariff($data) {
		$procedure = 'p_WhsDocumentTitleTariff_ins';
		if ($data['WhsDocumentTitleTariff_id'] > 0) {
			$procedure = 'p_WhsDocumentTitleTariff_upd';
		}
		$q = "
			declare
				@WhsDocumentTitleTariff_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @WhsDocumentTitleTariff_id = :WhsDocumentTitleTariff_id;
			exec dbo.".$procedure."
				@WhsDocumentTitleTariff_id = @WhsDocumentTitleTariff_id output,
				@WhsDocumentTitle_id = :WhsDocumentTitle_id,
				@UslugaComplexTariff_id = :UslugaComplexTariff_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @WhsDocumentTitleTariff_id as WhsDocumentTitleTariff_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$p = array(
			'WhsDocumentTitleTariff_id' => $data['WhsDocumentTitleTariff_id'],
			'WhsDocumentTitle_id' => $data['WhsDocumentTitle_id'],
			'UslugaComplexTariff_id' => $data['UslugaComplexTariff_id'],
			'pmUser_id' => $this->pmUser_id,
		);
		$r = $this->db->query($q, $p);
		if (is_object($r)) {
			$result = $r->result('array');
		} else {
			$result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}
		return $result;
	}

	/**
	 * Функция
	 * @return type 
	 */
	function delete() {
		//поиск и удаление связей с тарифами
		$q = "
			select
				WhsDocumentTitleTariff_id
			from
				v_WhsDocumentTitleTariff with (nolock)
			where
				WhsDocumentTitle_id = :WhsDocumentTitle_id;
		";
		$r = $this->db->query($q, array(
			'WhsDocumentTitle_id' => $this->WhsDocumentTitle_id
		));
		if (is_object($r)) {
			$rec_array = $r->result('array');
			foreach($rec_array as $rec) {
				$response = $this->deleteWhsDocumentTitleTariff(array(
					'id' => $rec['WhsDocumentTitleTariff_id']
				));
			}
		}

		$q = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec dbo.p_WhsDocumentTitle_del
				@WhsDocumentTitle_id = :WhsDocumentTitle_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$r = $this->db->query($q, array(
			'WhsDocumentTitle_id' => $this->WhsDocumentTitle_id
		));
		if (is_object($r)) {
			return $r->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Удаление связи между документом и тарифом
	 */
	function deleteWhsDocumentTitleTariff($data) {
		$q = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec dbo.p_WhsDocumentTitleTariff_del
				@WhsDocumentTitleTariff_id = :WhsDocumentTitleTariff_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$r = $this->db->query($q, array(
			'WhsDocumentTitleTariff_id' => $data['id']
		));
		if (is_object($r)) {
			return $r->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Функция
	 * @param type $filter
	 * @return type 
	 */
	function loadWhsDocumentSupplyList($filter) {
		$where = array();
		$p = array();
		/*
		  if (isset($filter['WhsDocumentTitle_id']) && $filter['WhsDocumentTitle_id']) {
		  $where[] = 'v_WhsDocumentTitle.WhsDocumentTitle_id = :WhsDocumentTitle_id';
		  $p['WhsDocumentTitle_id'] = $filter['WhsDocumentTitle_id'];
		  } */
		$where[] = 'wdt.WhsDocumentType_Code = 6';
		$where[] = '(
			' . (isset($filter['WhsDocumentUc_id']) && $filter['WhsDocumentUc_id'] > 0 ? 'wds.WhsDocumentUc_id = :WhsDocumentUc_id or ' : '') . '
			wds.WhsDocumentUc_id not in (select WhsDocumentUc_id from v_WhsDocumentTitle with (nolock) where WhsDocumentUc_id is not null)
		)';
		$where_clause = implode(' AND ', $where);
		if (strlen($where_clause)) {
			$where_clause = 'WHERE ' . $where_clause;
		}
		$q = "
			select
				wds.WhsDocumentUc_id,
				wds.WhsDocumentUc_Name,
				wds.WhsDocumentUc_Num,
				wdst.WhsDocumentStatusType_Name
			from
				v_WhsDocumentSupply wds with (nolock)
				left join WhsDocumentStatusType wdst with(nolock) on wdst.WhsDocumentStatusType_id=wds.WhsDocumentStatusType_id
				left join v_WhsDocumentType wdt with (nolock) on wdt.WhsDocumentType_id = wds.WhsDocumentType_id
			$where_clause
		";
		$result = $this->db->query($q, $filter);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Функция
	 * @param type $data
	 * @return string 
	 */
	function executeCheck($data) {
		$err = null;

		if (isset($data['WhsDocumentUc_id']) && $data['WhsDocumentUc_id'] > 0) {
			$q = "
				select
					wds.WhsDocumentUc_Num,
					wdst.WhsDocumentStatusType_Code
				from
					v_WhsDocumentSupply wds with (nolock)
					left join v_WhsDocumentStatusType wdst with (nolock) on wdst.WhsDocumentStatusType_id = wds.WhsDocumentStatusType_id
				where
					wds.WhsDocumentUc_id = :WhsDocumentUc_id;
			";
			$result = $this->db->query($q, $data);
			if (is_object($result)) {
				$res = $result->result('array');
				if (!isset($res[0]['WhsDocumentStatusType_Code']) || $res[0]['WhsDocumentStatusType_Code'] != 2) {
					$err = 'Контракт № ' . $res[0]['WhsDocumentUc_Num'] . ' не подписан - исполнение текущего документа не возможно';
				}
			}
		}

		return $err;
	}

}