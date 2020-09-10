<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Модель для объектов Модели анализаторов
 *
 * @package
 * @access       public
 * @copyright    Copyright (c) 2010-2011 Swan Ltd.
 * @author       gabdushev
 * @version
 *
 * @property AnalyzerRack_model AnalyzerRack_model
 */
class AnalyzerModel_model extends swModel
{
	private $AnalyzerModel_id;//
	private $AnalyzerModel_Name;//
	private $AnalyzerModel_SysNick;//
	private $FRMOEquipment_id; //тип оборудования
	private $AnalyzerClass_id;//Класс анализатора
	private $AnalyzerInteractionType_id;//Тип взаимодействия
	private $AnalyzerModel_IsScaner;//Наличие сканера
	private $AnalyzerWorksheetInteractionType_id;//Тип взаимодействия с рабочими списками
	private $pmUser_id;//Идентификатор пользователя системы Промед

	/**
	 * TO-DO: описать
	 */
	public function getAnalyzerModel_id() { return $this->AnalyzerModel_id;}
	/**
	 * TO-DO: описать
	 */
	public function setAnalyzerModel_id($value) { $this->AnalyzerModel_id = $value; }

	/**
	 * TO-DO: описать
	 */
	public function getAnalyzerModel_Name() { return $this->AnalyzerModel_Name;}
	/**
	 * TO-DO: описать
	 */
	public function setAnalyzerModel_Name($value) { $this->AnalyzerModel_Name = $value; }

	/**
	 * TO-DO: описать
	 */
	public function getAnalyzerModel_SysNick() { return $this->AnalyzerModel_SysNick;}
	/**
	 * TO-DO: описать
	 */
	public function setAnalyzerModel_SysNick($value) { $this->AnalyzerModel_SysNick = $value; }

	/**
	 * "геттер" типа оборудования
	*/
	public function getFRMOEquipment_id() { return $this->FRMOEquipment_id;}
	/**
	 * "сеттер" типа оборудования
	 */
	public function setFRMOEquipment_id($value) { $this->FRMOEquipment_id = $value; }

	/**
	 * TO-DO: описать
	 */
	public function getAnalyzerClass_id() { return $this->AnalyzerClass_id;}
	/**
	 * TO-DO: описать
	 */
	public function setAnalyzerClass_id($value) { $this->AnalyzerClass_id = $value; }

	/**
	 * TO-DO: описать
	 */
	public function getAnalyzerInteractionType_id() { return $this->AnalyzerInteractionType_id;}
	/**
	 * TO-DO: описать
	 */
	public function setAnalyzerInteractionType_id($value) { $this->AnalyzerInteractionType_id = $value; }

	/**
	 * TO-DO: описать
	 */
	public function getAnalyzerModel_IsScaner() { return $this->AnalyzerModel_IsScaner;}
	/**
	 * TO-DO: описать
	 */
	public function setAnalyzerModel_IsScaner($value) { $this->AnalyzerModel_IsScaner = $value; }

	/**
	 * TO-DO: описать
	 */
	public function getAnalyzerWorksheetInteractionType_id() { return $this->AnalyzerWorksheetInteractionType_id;}
	/**
	 * TO-DO: описать
	 */
	public function setAnalyzerWorksheetInteractionType_id($value) { $this->AnalyzerWorksheetInteractionType_id = $value; }

	/**
	 * TO-DO: описать
	 */
	public function getpmUser_id() { return $this->pmUser_id;}
	/**
	 * TO-DO: описать
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
	 * TO-DO: описать
	 */
	function load() {
		$q = "
			select
				AnalyzerModel_id,
				AnalyzerModel_Name,
				AnalyzerModel_SysNick,
				FRMOEquipment_id,
				AnalyzerClass_id,
				AnalyzerInteractionType_id,
				AnalyzerModel_IsScaner,
				AnalyzerWorksheetInteractionType_id
			from
				lis.v_AnalyzerModel with (nolock)
			where
				AnalyzerModel_id = :AnalyzerModel_id
		";
		$r = $this->db->query($q, array('AnalyzerModel_id' => $this->AnalyzerModel_id));
		if ( is_object($r) ) {
			$r = $r->result('array');
			if (isset($r[0])) {
				$this->AnalyzerModel_id = $r[0]['AnalyzerModel_id'];
				$this->AnalyzerModel_Name = $r[0]['AnalyzerModel_Name'];
				$this->AnalyzerModel_SysNick = $r[0]['AnalyzerModel_SysNick'];
				$this->FRMOEquipment_id = $r[0]['FRMOEquipment_id'];
				$this->AnalyzerClass_id = $r[0]['AnalyzerClass_id'];
				$this->AnalyzerInteractionType_id = $r[0]['AnalyzerInteractionType_id'];
				$this->AnalyzerModel_IsScaner = $r[0]['AnalyzerModel_IsScaner'];
				$this->AnalyzerWorksheetInteractionType_id = $r[0]['AnalyzerWorksheetInteractionType_id'];
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
	 * TO-DO: описать
	 */
	function loadList($filter) {
		$where[] = '(1=1)';
		$p = array();
		if (isset($filter['AnalyzerModel_id']) && $filter['AnalyzerModel_id']) {
			$where[] = 'AnalyzerModel_id = :AnalyzerModel_id';
			$p['AnalyzerModel_id'] = $filter['AnalyzerModel_id'];
		}
		if (isset($filter['AnalyzerModel_Name']) && $filter['AnalyzerModel_Name']) {
			$where[] = 'AnalyzerModel_Name = :AnalyzerModel_Name';
			$p['AnalyzerModel_Name'] = $filter['AnalyzerModel_Name'];
		}
		if (isset($filter['AnalyzerModel_SysNick']) && $filter['AnalyzerModel_SysNick']) {
			$where[] = 'AnalyzerModel_SysNick = :AnalyzerModel_SysNick';
			$p['AnalyzerModel_SysNick'] = $filter['AnalyzerModel_SysNick'];
		}
		if (isset($filter['FRMOEquipment_id']) && $filter['FRMOEquipment_id']) {
			$where[] = 'AM.FRMOEquipment_id = :FRMOEquipment_id';
			$p['FRMOEquipment_id'] = $filter['FRMOEquipment_id'];
		}
		if (isset($filter['AnalyzerClass_id']) && $filter['AnalyzerClass_id']) {
			$where[] = 'AnalyzerClass_id = :AnalyzerClass_id';
			$p['AnalyzerClass_id'] = $filter['AnalyzerClass_id'];
		}
		if (isset($filter['AnalyzerInteractionType_id']) && $filter['AnalyzerInteractionType_id']) {
			$where[] = 'AnalyzerInteractionType_id = :AnalyzerInteractionType_id';
			$p['AnalyzerInteractionType_id'] = $filter['AnalyzerInteractionType_id'];
		}
		if (isset($filter['AnalyzerModel_IsScaner']) && $filter['AnalyzerModel_IsScaner']) {
			$where[] = 'AnalyzerModel_IsScaner = :AnalyzerModel_IsScaner';
			$p['AnalyzerModel_IsScaner'] = $filter['AnalyzerModel_IsScaner'];
		}
		if (isset($filter['AnalyzerWorksheetInteractionType_id']) && $filter['AnalyzerWorksheetInteractionType_id']) {
			$where[] = 'AnalyzerWorksheetInteractionType_id = :AnalyzerWorksheetInteractionType_id';
			$p['AnalyzerWorksheetInteractionType_id'] = $filter['AnalyzerWorksheetInteractionType_id'];
		}
		$where_clause = implode(' AND ', $where);

		$q = "
			SELECT
				AM.AnalyzerModel_id,
				AM.AnalyzerModel_Name,
				AM.AnalyzerModel_SysNick,
				isnull(FRMO.FRMOEquipment_Name, '') as FRMOEquipment_Name,
				AM.AnalyzerClass_id,
				AM.AnalyzerInteractionType_id,
				case when ISNULL(AM.AnalyzerModel_IsScaner,1) = 2 then 'true' else 'false' end as AnalyzerModel_IsScaner,
				AM.AnalyzerWorksheetInteractionType_id,
				AC.AnalyzerClass_Name,
				AIT.AnalyzerInteractionType_Name,
				YN.YesNo_Name,
				AWIT.AnalyzerWorksheetInteractionType_Name
			FROM
				lis.v_AnalyzerModel AM WITH (NOLOCK)
				LEFT JOIN lis.v_AnalyzerClass AC WITH (NOLOCK) ON AC.AnalyzerClass_id = AM.AnalyzerClass_id
				LEFT JOIN lis.v_AnalyzerInteractionType AIT WITH (NOLOCK) ON AIT.AnalyzerInteractionType_id = AM.AnalyzerInteractionType_id
				LEFT JOIN dbo.v_YesNo YN WITH (NOLOCK) ON YN.YesNo_id = AM.AnalyzerModel_IsScaner
				LEFT JOIN lis.v_AnalyzerWorksheetInteractionType AWIT WITH (NOLOCK) ON AWIT.AnalyzerWorksheetInteractionType_id = AM.AnalyzerWorksheetInteractionType_id
				LEFT JOIN passport.v_FRMOEquipment FRMO WITH (NOLOCK) ON FRMO.FRMOEquipment_id = AM.FRMOEquipment_id
			WHERE
				{$where_clause}
			ORDER BY AM.AnalyzerModel_Name
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
	 * TO-DO: описать
	 */
	function save() {
		$procedure = 'p_AnalyzerModel_ins';
		if ( $this->AnalyzerModel_id > 0 ) {
			$procedure = 'p_AnalyzerModel_upd';
		}
		$q = "
			declare
				@AnalyzerModel_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @AnalyzerModel_id = :AnalyzerModel_id;
			exec lis." . $procedure . "
				@AnalyzerModel_id = @AnalyzerModel_id output,
				@AnalyzerModel_Name = :AnalyzerModel_Name,
				@AnalyzerModel_SysNick = :AnalyzerModel_SysNick,
				@FRMOEquipment_id = :FRMOEquipment_id,
				@AnalyzerClass_id = :AnalyzerClass_id,
				@AnalyzerInteractionType_id = :AnalyzerInteractionType_id,
				@AnalyzerModel_IsScaner = :AnalyzerModel_IsScaner,
				@AnalyzerWorksheetInteractionType_id = :AnalyzerWorksheetInteractionType_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @AnalyzerModel_id as AnalyzerModel_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$p = array(
			'AnalyzerModel_id' => $this->AnalyzerModel_id,
			'AnalyzerModel_Name' => $this->AnalyzerModel_Name,
			'AnalyzerModel_SysNick' => $this->AnalyzerModel_SysNick,
			'FRMOEquipment_id' => $this->FRMOEquipment_id,
			'AnalyzerClass_id' => $this->AnalyzerClass_id,
			'AnalyzerInteractionType_id' => $this->AnalyzerInteractionType_id,
			'AnalyzerModel_IsScaner' => $this->AnalyzerModel_IsScaner,
			'AnalyzerWorksheetInteractionType_id' => $this->AnalyzerWorksheetInteractionType_id,
			'pmUser_id' => $this->pmUser_id,
		);
		$r = $this->db->query($q, $p);
		if ( is_object($r) ) {
			$result = $r->result('array');
			$this->AnalyzerModel_id = $result[0]['AnalyzerModel_id'];
		}
		else {
			log_message('error', var_export(array('q' => $q, 'p' => $p, 'e' => sqlsrv_errors()), true));
			$result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}
		return $result;
	}

	/**
	 * TO-DO: описать
	 */
	function delete() {
		$tests_count = $this->getFirstResultFromQuery('SELECT COUNT(*) FROM lis.v_AnalyzerTest with (nolock) WHERE AnalyzerModel_id = :AnalyzerModel_id', array('AnalyzerModel_id' => $this->AnalyzerModel_id));
		if ($tests_count > 0) {
			throw new Exception("Нельзя удалить данную модель анализатора, так как у нее есть тесты");
		}
		$analyzers_count = $this->getFirstResultFromQuery('SELECT COUNT(*) FROM lis.v_Analyzer with (nolock) WHERE AnalyzerModel_id = :AnalyzerModel_id', array('AnalyzerModel_id' => $this->AnalyzerModel_id));
		if ($analyzers_count > 0) {
			throw new Exception("Нельзя удалить данную модель анализатора, так как есть анализаторы с данной моделью");
		}
		$racks = $this->db->query('SELECT AnalyzerRack_id FROM lis.v_AnalyzerRack with (nolock) WHERE AnalyzerModel_id = :AnalyzerModel_id', array('AnalyzerModel_id' => $this->AnalyzerModel_id));
		if ( is_object($racks) ) {
			$racks = $racks->result('array');
			$this->load->model('AnalyzerRack_model', 'AnalyzerRack_model');
			foreach ($racks as  $rack) {
				$this->AnalyzerRack_model->setAnalyzerRack_id($rack['AnalyzerRack_id']);
				if (!$this->AnalyzerRack_model->delete()) {
					throw new Exception("Ошибка при удалении штатива {$rack['AnalyzerRack_id']} модели анализатора {$this->AnalyzerModel_id}");
				}
			}
		}
		$q = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec lis.p_AnalyzerModel_del
				@AnalyzerModel_id = :AnalyzerModel_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$r = $this->db->query($q, array(
			'AnalyzerModel_id' => $this->AnalyzerModel_id
		));
		if ( is_object($r) ) {
			return $r->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Проверка, что у модели анализатора указан тип оборудования
	*/
	function AModelHasType($data)
	{
		$res = $this->getFirstResultFromQuery("
			select
				FRMOEquipment_id
			from lis.v_AnalyzerModel with(nolock)
			where AnalyzerModel_id = :AnalyzerModel_id
		", $data, true);
		
		return $res ?? false;
	}
}

		