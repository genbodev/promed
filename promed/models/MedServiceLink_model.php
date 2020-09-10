<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * TODO: complete explanation, preamble and describing
 * Модель для объектов Связь между службами
 *
 * @package
 * @access       public
 * @copyright    Copyright (c) 2010-2011 Swan Ltd.
 * @author       gabdushev
 * @version
 */
class MedServiceLink_model extends swModel {
	private $MedServiceLink_id;//MedServiceLink_id
	private $MedServiceLinkType_id;//Тип связи служб
	private $MedService_id;//MedService_id
	private $MedService_lid;//MedService_lid
	private $pmUser_id;//Идентификатор пользователя системы Промед
	/**
	 * function
	 * @return type
	 */
	public function getMedServiceLink_id() { return $this->MedServiceLink_id;}
	/**
	 * function
	 * @return type
	 */
	public function setMedServiceLink_id($value) { $this->MedServiceLink_id = $value; }
	/**
	 * function
	 * @return type
	 */
	public function getMedServiceLinkType_id() { return $this->MedServiceLinkType_id;}
	/**
	 * function
	 * @return type
	 */
	public function setMedServiceLinkType_id($value) { $this->MedServiceLinkType_id = $value; }
	/**
	 * function
	 * @return type
	 */
	public function getMedService_id() { return $this->MedService_id;}
	/**
	 * function
	 * @return type
	 */
	public function setMedService_id($value) { $this->MedService_id = $value; }
	/**
	 * function
	 * @return type
	 */
	public function getMedService_lid() { return $this->MedService_lid;}
	/**
	 * function
	 * @return type
	 */
	public function setMedService_lid($value) { $this->MedService_lid = $value; }
	/**
	 * function
	 * @return type
	 */
	public function getpmUser_id() { return $this->pmUser_id;}
	/**
	 * function
	 * @return type
	 */
	public function setpmUser_id($value) { $this->pmUser_id = $value; }
	/**
	 * function
	 * @return type
	 */
	function __construct(){
		if (isset($_SESSION['pmuser_id'])) {
			$this->setpmUser_id($_SESSION['pmuser_id']);
		} else {
			throw new Exception('Значение pmuser_id не установлено в текущей сессии (не выполнен вход в Промед?)');
		}
	}
	/**
	 * function
	 * @return type
	 */
	function load() {
		$q = "
			select
				MedServiceLink_id, MedServiceLinkType_id, MedService_id, MedService_lid
			from
				dbo.v_MedServiceLink with(nolock)
			where
				MedServiceLink_id = :MedServiceLink_id
		";
		$r = $this->db->query($q, array('MedServiceLink_id' => $this->MedServiceLink_id));
		if ( is_object($r) ) {
			$r = $r->result('array');
			if (isset($r[0])) {
				$this->MedServiceLink_id = $r[0]['MedServiceLink_id'];
				$this->MedServiceLinkType_id = $r[0]['MedServiceLinkType_id'];
				$this->MedService_id = $r[0]['MedService_id'];
				$this->MedService_lid = $r[0]['MedService_lid'];
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
	 * function
	 * @return type
	 */
	function loadList($filter) {
		$where = array();
		$p = array();
		if (isset($filter['MedServiceLink_id']) && $filter['MedServiceLink_id']) {
			$where[] = 'msl.MedServiceLink_id = :MedServiceLink_id';
			$p['MedServiceLink_id'] = $filter['MedServiceLink_id'];
		}
		if (isset($filter['MedServiceLinkType_id']) && $filter['MedServiceLinkType_id']) {
			$where[] = 'msl.MedServiceLinkType_id = :MedServiceLinkType_id';
			$p['MedServiceLinkType_id'] = $filter['MedServiceLinkType_id'];
		}
		if (isset($filter['MedService_id']) && $filter['MedService_id']) {
			$where[] = 'msl.MedService_id = :MedService_id';
			$p['MedService_id'] = $filter['MedService_id'];
		}
		
		// нужно отображать все пункты забора связанные со связанными службами %) (refs #15921)
		if (isset($filter['MedServiceType_SysNick']) && isset($filter['MedServiceLinkType_id']) && $filter['MedServiceType_SysNick'] == 'reglab' && $filter['MedServiceLinkType_id'] == '1') {
			if (isset($filter['MedService_lid']) && $filter['MedService_lid']) {
				$where[] = 'exists(select top 1 MedServiceLink_id from v_MedServiceLink msl2 with (nolock) where msl2.MedService_lid = msl.MedService_lid and msl2.MedService_id = :MedService_lid and msl2.MedServiceLinkType_id = 2)';
				$p['MedService_lid'] = $filter['MedService_lid'];
			}			
		} else {
			// иначе просто связанные с теущей службой.
			if (isset($filter['MedService_lid']) && $filter['MedService_lid']) {
				$where[] = 'msl.MedService_lid = :MedService_lid';
				$p['MedService_lid'] = $filter['MedService_lid'];
			}
		}
		
		$top1 = '';
		if (isset($filter['top1']) && $filter['top1']) {
			$top1 = 'TOP 1';
		}
				
		$where_clause = implode(' AND ', $where);
		if (strlen($where_clause)) {
			$where_clause = 'WHERE '.$where_clause;
		}
		$q = "
			SELECT {$top1}
				msl.MedServiceLink_id,
				msl.MedServiceLinkType_id,
				msl.MedService_id,
				msl.MedService_lid,
				MedServiceLinkType_id_ref.MedServiceLinkType_Name MedServiceLinkType_id_Name,
				MedService_id_ref.MedService_Name MedService_id_Name,
				MedService_lid_ref.MedService_Name MedService_lid_Name,
				lab_lpu.Lpu_Nick AS lab_lpu_Lpu_Nick,
				pz_lpu.Lpu_Nick AS pz_lpu_Lpu_Nick,
				lab_type.MedServiceType_Name AS lab_MedServiceType_Name,
				pz_type.MedServiceType_Name AS pz_MedServiceType_Name,
				lab_Address.Address_Address AS lab_Address_Address,
				pz_Address.Address_Address AS pz_Address_Address
			FROM
				dbo.v_MedServiceLink msl WITH ( NOLOCK )
				INNER JOIN dbo.v_MedServiceLinkType MedServiceLinkType_id_ref WITH ( NOLOCK ) ON MedServiceLinkType_id_ref.MedServiceLinkType_id = msl.MedServiceLinkType_id

				INNER JOIN dbo.v_MedService MedService_id_ref WITH ( NOLOCK ) ON MedService_id_ref.MedService_id = msl.MedService_id
				INNER JOIN dbo.MedServiceType pz_type WITH ( NOLOCK ) ON pz_type.MedServiceType_id = MedService_id_ref.MedServiceType_id
				INNER JOIN dbo.v_Lpu pz_lpu WITH ( NOLOCK ) ON pz_lpu.Lpu_id = MedService_id_ref.Lpu_id
				LEFT JOIN dbo.v_LpuBuilding pz_build with(nolock) ON pz_build.LpuBuilding_id = MedService_id_ref.LpuBuilding_id
				LEFT JOIN dbo.v_Address pz_Address with(nolock) ON pz_Address.Address_id = pz_build.Address_id

				INNER JOIN dbo.v_MedService MedService_lid_ref WITH ( NOLOCK ) ON MedService_lid_ref.MedService_id = msl.MedService_lid
				INNER JOIN dbo.MedServiceType lab_type WITH ( NOLOCK ) ON lab_type.MedServiceType_id = MedService_lid_ref.MedServiceType_id
				INNER JOIN dbo.v_Lpu lab_lpu WITH ( NOLOCK ) ON lab_lpu.Lpu_id = MedService_lid_ref.Lpu_id
				LEFT JOIN dbo.v_LpuBuilding lab_build with(nolock) ON lab_build.LpuBuilding_id = MedService_lid_ref.LpuBuilding_id
				LEFT JOIN dbo.v_Address lab_Address with(nolock) ON lab_Address.Address_id = lab_build.Address_id
			$where_clause
		";
		/*
			echo getDebugSql($q, $filter);
			exit;
		*/
		$result = $this->db->query($q, $filter);
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	/**
	 * function
	 * @return type
	 */
	function save() {
		$procedure = 'p_MedServiceLink_ins';
		if ( $this->MedServiceLink_id > 0 ) {
			$procedure = 'p_MedServiceLink_upd';
		}
		$q = "
			declare
				@MedServiceLink_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @MedServiceLink_id = :MedServiceLink_id;
			exec dbo." . $procedure . "
				@MedServiceLink_id = @MedServiceLink_id output,
				@MedServiceLinkType_id = :MedServiceLinkType_id,
				@MedService_id = :MedService_id,
				@MedService_lid = :MedService_lid,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @MedServiceLink_id as MedServiceLink_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$p = array(
			'MedServiceLink_id' => $this->MedServiceLink_id,
			'MedServiceLinkType_id' => $this->MedServiceLinkType_id,
			'MedService_id' => $this->MedService_id,
			'MedService_lid' => $this->MedService_lid,
			'pmUser_id' => $this->pmUser_id,
		);
		$r = $this->db->query($q, $p);
		if ( is_object($r) ) {
			$result = $r->result('array');
			$this->MedServiceLink_id = $result[0]['MedServiceLink_id'];
		}
		else {
			log_message('error', var_export(array('q' => $q, 'p' => $p, 'e' => sqlsrv_errors()), true));
			$result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}
		return $result;
	}
	/**
	 * function
	 * @return type
	 */
	function delete() {
		$q = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec dbo.p_MedServiceLink_del
				@MedServiceLink_id = :MedServiceLink_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$r = $this->db->query($q, array(
			'MedServiceLink_id' => $this->MedServiceLink_id
		));
		if ( is_object($r) ) {
			return $r->result('array');
		}
		else {
			return false;
		}
	}
}