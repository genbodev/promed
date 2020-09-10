<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Модель для объектов Услуги по МЭСам
 *
 * @package
 * @access       public
 * @copyright    Copyright (c) 2010-2011 Swan Ltd.
 * @author       gabdushev
 * @version
 */
class MesUsluga_model extends swModel {
	private $MesUsluga_id;//MesUsluga_id
	private $Usluga_id;//Услуга
	private $UslugaComplex_id;//Комплексная услуга
	private $Mes_id;//МЕС
	private $MesUsluga_UslugaCount;//Количество услуг
	private $MesUsluga_begDT;//Дата начала действия
	private $MesUsluga_endDT;//Дата окончания действия
	private $pmUser_id;//Идентификатор пользователя системы Промед

	/**
	 * Comment
	 */
	public function getMesUsluga_id() { return $this->MesUsluga_id;}
	/**
	 * Comment
	 */
	public function setMesUsluga_id($value) { $this->MesUsluga_id = $value; }

	/**
	 * Comment
	 */
	public function getUsluga_id() { return $this->Usluga_id;}
	/**
	 * Comment
	 */
	public function setUsluga_id($value) { $this->Usluga_id = $value; }

	/**
	 * Comment
	 */
	public function getUslugaComplex_id() { return $this->UslugaComplex_id;}
	/**
	 * Comment
	 */
	public function setUslugaComplex_id($value) { $this->UslugaComplex_id = $value; }

	/**
	 * Comment
	 */
	public function getMes_id() { return $this->Mes_id;}
	/**
	 * Comment
	 */
	public function setMes_id($value) { $this->Mes_id = $value; }

	/**
	 * Comment
	 */
	public function getMesUsluga_UslugaCount() { return $this->MesUsluga_UslugaCount;}
	/**
	 * Comment
	 */
	public function setMesUsluga_UslugaCount($value) { $this->MesUsluga_UslugaCount = $value; }

	/**
	 * Comment
	 */
	public function getMesUsluga_begDT() { return $this->MesUsluga_begDT;}
	/**
	 * Comment
	 */
	public function setMesUsluga_begDT($value) { $this->MesUsluga_begDT = $value; }

	/**
	 * Comment
	 */
	public function getMesUsluga_endDT() { return $this->MesUsluga_endDT;}
	/**
	 * Comment
	 */
	public function setMesUsluga_endDT($value) { $this->MesUsluga_endDT = $value; }

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
			select top 1
				MesUsluga_id,
				Usluga_id,
				UslugaComplex_id,
				Mes_id,
				MesUsluga_UslugaCount,
				convert(varchar(10), MesUsluga_begDT, 104) as MesUsluga_begDT,
				convert(varchar(10), MesUsluga_endDT, 104) as MesUsluga_endDT
			from
				dbo.v_MesUsluga with (nolock)
			where
				MesUsluga_id = :MesUsluga_id
		";
		$r = $this->db->query($q, array('MesUsluga_id' => $this->MesUsluga_id));
		if ( is_object($r) ) {
			$r = $r->result('array');
			if (isset($r[0])) {
				$this->MesUsluga_id = $r[0]['MesUsluga_id'];
				$this->Usluga_id = $r[0]['Usluga_id'];
				$this->UslugaComplex_id = $r[0]['UslugaComplex_id'];
				$this->Mes_id = $r[0]['Mes_id'];
				$this->MesUsluga_UslugaCount = $r[0]['MesUsluga_UslugaCount'];
				$this->MesUsluga_begDT = $r[0]['MesUsluga_begDT'];
				$this->MesUsluga_endDT = $r[0]['MesUsluga_endDT'];
				return $r;
			} else {
				return false;
			}
		}
		else {
			return false;
		}
	}

	/**
	 * Comment
	 */
	function loadList($filter) {
		$where = array();
		$p = array();
		if (isset($filter['MesUsluga_id']) && $filter['MesUsluga_id']) {
			$where[] = 'mu.MesUsluga_id = :MesUsluga_id';
			$p['MesUsluga_id'] = $filter['MesUsluga_id'];
		}
		if (isset($filter['Usluga_id']) && $filter['Usluga_id']) {
			$where[] = 'mu.Usluga_id = :Usluga_id';
			$p['Usluga_id'] = $filter['Usluga_id'];
		}
		if (isset($filter['UslugaComplex_id']) && $filter['UslugaComplex_id']) {
			$where[] = 'mu.UslugaComplex_id = :UslugaComplex_id';
			$p['UslugaComplex_id'] = $filter['UslugaComplex_id'];
		}
		if (isset($filter['Mes_id']) && $filter['Mes_id']) {
			$where[] = 'mu.Mes_id = :Mes_id';
			$p['Mes_id'] = $filter['Mes_id'];
		}
		if (isset($filter['MesUsluga_UslugaCount']) && $filter['MesUsluga_UslugaCount']) {
			$where[] = 'mu.MesUsluga_UslugaCount = :MesUsluga_UslugaCount';
			$p['MesUsluga_UslugaCount'] = $filter['MesUsluga_UslugaCount'];
		}
		$where_clause = implode(' AND ', $where);
		if (strlen($where_clause)) {
			$where_clause = 'WHERE '.$where_clause;
		}
		$q = "
			SELECT
				mu.MesUsluga_id,
				mu.Usluga_id,
				mu.UslugaComplex_id,
				mu.Mes_id,
				mu.MesUsluga_UslugaCount,
				CASE Mes_id_ref.MedicalCareKind_id
					WHEN 47 THEN UslugaComplex_id_ref.UslugaComplex_Name
					ELSE Usluga_id_ref.Usluga_Name
				END AS Usluga_id_Name,
				convert(varchar(10), mu.MesUsluga_begDT, 104) as MesUsluga_begDT,
				convert(varchar(10), mu.MesUsluga_endDT, 104) as MesUsluga_endDT
			FROM
				dbo.v_MesUsluga mu WITH (NOLOCK)
				LEFT JOIN dbo.v_Usluga Usluga_id_ref WITH (NOLOCK) ON Usluga_id_ref.Usluga_id = mu.Usluga_id
				LEFT JOIN dbo.v_UslugaComplex UslugaComplex_id_ref WITH (NOLOCK) ON UslugaComplex_id_ref.UslugaComplex_id = mu.UslugaComplex_id
				LEFT JOIN dbo.v_MesOld Mes_id_ref WITH (NOLOCK) ON Mes_id_ref.Mes_id = mu.Mes_id
			$where_clause
		";
		$result = $this->db->query($q, $filter);
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Comment
	 */
	function save() {
		$procedure = 'p_MesUsluga_ins';
		if ( $this->MesUsluga_id > 0 ) {
			$procedure = 'p_MesUsluga_upd';
		}
		$q = "
			declare
				@MesUsluga_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @MesUsluga_id = :MesUsluga_id;
			exec dbo." . $procedure . "
				@MesUsluga_id = @MesUsluga_id output,
				@Usluga_id = :Usluga_id,
				@UslugaComplex_id = :UslugaComplex_id,
				@Mes_id = :Mes_id,
				@MesUsluga_UslugaCount = :MesUsluga_UslugaCount,
				@MesUsluga_begDT = :MesUsluga_begDT,
				@MesUsluga_endDT = :MesUsluga_endDT,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @MesUsluga_id as MesUsluga_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$p = array(
			'MesUsluga_id' => $this->MesUsluga_id,
			'Usluga_id' => $this->Usluga_id,
			'UslugaComplex_id' => $this->UslugaComplex_id,
			'Mes_id' => $this->Mes_id,
			'MesUsluga_UslugaCount' => $this->MesUsluga_UslugaCount,
			'MesUsluga_begDT' => $this->MesUsluga_begDT,
			'MesUsluga_endDT' => $this->MesUsluga_endDT,
			'pmUser_id' => $this->pmUser_id,
		);
		$r = $this->db->query($q, $p);
		if ( is_object($r) ) {
			$result = $r->result('array');
			$this->MesUsluga_id = $result[0]['MesUsluga_id'];
		}
		else {
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
			exec dbo.p_MesUsluga_del
				@MesUsluga_id = :MesUsluga_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$r = $this->db->query($q, array(
			'MesUsluga_id' => $this->MesUsluga_id
		));
		if ( is_object($r) ) {
			return $r->result('array');
		}
		else {
			return false;
		}
	}
}