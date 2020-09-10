<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Class EvnDiagPLStom_model Стоматология
 */

class EvnDiagPLStom_model extends swModel {
	protected $_evnDiagPLStomData = array();

	/**
	 * construct
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Сохранение КСГ и апдейт услуг
	 */
	function updateMesId($data) {
		$query = "
			update EvnDiagPLStom with (rowlock) set Mes_id = :Mes_id where EvnDiagPLStom_id = :EvnDiagPLStom_id
		";
		$this->db->query($query, array(
			'Mes_id' => $data['Mes_id'],
			'EvnDiagPLStom_id' => $data['EvnDiagPLStom_id']
		));

		// обновляем услуги
		$query = "
			update
				eus with (rowlock)
			set
				eus.EvnUslugaStom_IsMes = case when mu.MesUsluga_id is not null then 2 else 1 end
			from
				EvnUslugaStom eus
				inner join v_EvnUslugaStom eu (nolock) on eu.EvnUslugaStom_id = eus.EvnUslugaStom_id
				outer apply (
					select top 1
						MesUsluga_id
					from
						v_MesUsluga with (nolock)
					where
						Mes_id = :Mes_id and UslugaComplex_id = eu.UslugaComplex_id
				) MU
			where
				eus.EvnDiagPLStom_id = :EvnDiagPLStom_id
		";
		$this->db->query($query, array(
			'Mes_id' => $data['Mes_id'],
			'EvnDiagPLStom_id' => $data['EvnDiagPLStom_id']
		));

		return array('Error_Msg' => '');
	}

	/**
	 * deleteEvnDiagPLStom
	 * @param $data
	 * @return array
	 */
	function deleteEvnDiagPLStom($data) {
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_EvnDiagPLStom_del
				@EvnDiagPLStom_id = :EvnDiagPLStom_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$result = $this->db->query($query, array(
			'EvnDiagPLStom_id' => $data['EvnDiagPLStom_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		if ( is_object($result) ) {
			$resp = $result->result('array');
			$this->updateDiagInEvnVizitPLStom(array(
				'EvnDiagPLStom_id' => $data['EvnDiagPLStom_id']
			));
			return $resp;
		}
		else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление стоматологического диагноза)'));
		}
	}

	/**
	 * loadEvnDiagPLStomEditForm
	 * @param $data
	 * @return bool
	 */
	function loadEvnDiagPLStomEditForm($data) {
		$this->load->helper('MedStaffFactLink');
		//$med_personal_list = getMedPersonalListWithLinks();
		$addToQuery = "";
		$access_type = '
			case
				when EDPLS.Lpu_id = :Lpu_id then 1
				' . (count($data['session']['linkedLpuIdList']) > 1 ? 'when EDPLS.Lpu_id in (' . implode(',', $data['session']['linkedLpuIdList']) . ') and ISNULL(EDPLS.EvnDiagPLStom_IsTransit, 1) = 2 then 1' : '') . '
				else 0
			end = 1
		';

		/*if ( $data['session']['isMedStatUser'] == false && count($med_personal_list) > 0 ) {
			$queryWith = "
				with LpuSectionList (
					LpuSection_id,
					WorkData_begDate,
					WorkData_endDate
				) as (
					select
						LpuSection_id,
						cast(WorkData_begDate as date) as WorkData_begDate,
						cast(WorkData_endDate as date) as WorkData_endDate
					from v_MedStaffFact with (nolock)
					where MedPersonal_id in (" . implode(',', $med_personal_list) . ")
						and LpuSection_id is not null
				)
			";
			$access_type .= "
				and exists (
					select top 1 LpuSection_id
					from LpuSectionList
					where LpuSection_id = EVPLS.LpuSection_id
						and WorkData_begDate <= EVPLS.EvnVizitPLStom_setDate
						and (WorkData_endDate is null or WorkData_endDate >= EVPLS.EvnVizitPLStom_setDate)
				)
			";
		}*/
		if(getRegionNick() == 'ekb') {
			$addToQuery = "
				,EDPLS.EvnDiagPLStom_IsZNORemove
				,convert(varchar(10), EDPLS.EvnDiagPLStom_BiopsyDate, 104) as EvnDiagPLStom_BiopsyDate
			";
		}
		$query = "
			" . (!empty($queryWith) ? $queryWith : "") ."
			select top 1
				case when {$access_type} then 'edit' else 'view' end as accessType,
				EDPLS.EvnDiagPLStom_id,
				EDPLS.EvnDiagPLStom_rid,
				EDPLS.EvnDiagPLStom_pid,
				EDPLS.EvnDiagPLStom_KPU,
				EDPLS.EvnDiagPLStom_CarriesTeethCount,
				EDPLS.BlackClass_id,
				EDPLS.Person_id,
				EDPLS.Server_id,
				EDPLS.Lpu_id,
				EDPLS.PersonEvn_id,
				EDPLS.Server_id,
				EDPLS.Mes_id,
				EDPLS.EvnDiagPLStom_KSKP,
				EDPLS.Diag_id,
				EDPLS.DeseaseType_id,
				convert(varchar(10), EDPLS.EvnDiagPLStom_setDate, 104) as EvnDiagPLStom_setDate,
				convert(varchar(10), EDPLS.EvnDiagPLStom_disDate, 104) as EvnDiagPLStom_disDate,
				Tooth.Tooth_Code,
				EDPLS.Tooth_id,
				case when EDPLS.EvnDiagPLStom_IsClosed = 2 then 1 else 0 end as EvnDiagPLStom_IsClosed,
				case when EDPLS.EvnDiagPLStom_IsZNO = 2 then 1 else 0 end as EvnDiagPLStom_IsZNO,
				EDPLS.Diag_spid,
				EDPLS.PainIntensity_id,
				case when EDPLS.EvnDiagPLStom_HalfTooth = 2 then 1 else 0 end as EvnDiagPLStom_HalfTooth,
				EDPLS.EvnDiagPLStom_ToothSurface as json_data,
				convert(varchar(10), EVPLS.EvnVizitPLStom_setDate, 104) as EvnVizitPLStom_setDate
				{$addToQuery}
			from
				v_EvnDiagPLStom EDPLS with (nolock)
				inner join v_EvnVizitPLStom EVPLS with (nolock) on EVPLS.EvnVizitPLStom_id = EDPLS.EvnDiagPLStom_pid
				left join v_Tooth Tooth with (nolock) on Tooth.Tooth_id = EDPLS.Tooth_id
			where
				EDPLS.EvnDiagPLStom_id = :EvnDiagPLStom_id
				and (EDPLS.Lpu_id " . getLpuIdFilter($data) . " or " . (isset($data['session']['medpersonal_id']) && $data['session']['medpersonal_id'] > 0 ? 1 : 0) . " = 1)
		";
		$result = $this->db->query($query, array(
			'EvnDiagPLStom_id' => $data['EvnDiagPLStom_id'],
			'Lpu_id' => $data['Lpu_id']
		));

		if ( is_object($result) ) {
			$response = $result->result('array');
			if (isset($response[0]) && isset($response[0]['json_data'])) {
				$json_data = json_decode($response[0]['json_data'], true);
				if (isset($json_data['ToothSurfaceTypeIdList']) && is_array($json_data['ToothSurfaceTypeIdList'])) {
					$response[0]['ToothSurfaceType_id_list'] = implode(',', $json_data['ToothSurfaceTypeIdList']);
				}
			}
			return $response;
		}
		else {
			return false;
		}
	}

	/**
	 * loadEvnDiagPLStomGrid
	 * @param $data
	 * @return bool
	 */
	function loadEvnDiagPLStomGrid($data) {
		if ( empty($data['pid']) && empty($data['rid']) ) {
			return false;
		}

		$this->load->helper('MedStaffFactLink');
		$med_personal_list = getMedPersonalListWithLinks();

		$access_type = '
			case
				when EDPLS.Lpu_id = :Lpu_id then 1
				' . (count($data['session']['linkedLpuIdList']) > 1 ? 'when EDPLS.Lpu_id in (' . implode(',', $data['session']['linkedLpuIdList']) . ') and ISNULL(EDPLS.EvnDiagPLStom_IsTransit, 1) = 2 then 1' : '') . '
				else 0
			end = 1
		';

		if ( $data['session']['isMedStatUser'] == false && count($med_personal_list) > 0 ) {
			$queryWith = "
				with LpuSectionList (
					LpuSection_id,
					WorkData_begDate,
					WorkData_endDate
				) as (
					select
						LpuSection_id,
						cast(WorkData_begDate as date) as WorkData_begDate,
						cast(WorkData_endDate as date) as WorkData_endDate
					from v_MedStaffFact with (nolock)
					where MedPersonal_id in (" . implode(',', $med_personal_list) . ")
						and LpuSection_id is not null
				)
			";
			$access_type .= "
				and exists (
					select top 1 LpuSection_id
					from LpuSectionList with(nolock)
					where LpuSection_id = EVPLS.LpuSection_id
						and WorkData_begDate <= EVPLS.EvnVizitPLStom_setDate
						and (WorkData_endDate is null or WorkData_endDate >= EVPLS.EvnVizitPLStom_setDate)
				)
			";
		}

		$join = array();
		$select = array();

		if ( $this->regionNick == 'penza' ) {
			$join[] = "left join v_ServiceType ST on ST.ServiceType_id = EVPLS.ServiceType_id";
			$join[] = "
				outer apply (
					select top 1 1 as hasUslugaType03
					from v_EvnUslugaStom eus with (nolock)
						inner join v_UslugaComplexAttribute uca on uca.UslugaComplex_id = eus.UslugaComplex_id
						inner join v_UslugaComplexAttributeType ucat on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
					where eus.EvnDiagPLStom_id = EDPLS.EvnDiagPLStom_id
						and uca.UslugaComplexAttribute_Value = '03'
				) uc03
			";
			$select[] = "uc03.hasUslugaType03";
			$select[] = "convert(varchar(10), EVPLS.EvnVizitPLStom_setDate, 104) as EvnVizitPLStom_setDate";
			$select[] = "ST.ServiceType_SysNick";
		}

		$query = "
			" . (!empty($queryWith) ? $queryWith : "") ."
			select
				case when {$access_type} then 'edit' else 'view' end as accessType,
				EDPLS.EvnDiagPLStom_id as EvnDiagPLStom_id,
				EDPLS.EvnDiagPLStom_pid as EvnDiagPLStom_pid,
				EDPLS.Person_id as Person_id,
				EDPLS.PersonEvn_id as PersonEvn_id,
				EDPLS.Server_id as Server_id,
				RTRIM(DT.DeseaseType_Name) as DeseaseType_Name,
				convert(varchar(10), EDPLS.EvnDiagPLStom_setDate, 104) as EvnDiagPLStom_setDate,
				case
					when EDPLS.EvnDiagPLStom_IsClosed = 2 then convert(varchar(10), EDPLS.EvnDiagPLStom_disDate, 104)
					else null
				end as EvnDiagPLStom_disDate,
				ISNULL(EDPLS.EvnDiagPLStom_IsClosed, 1) as EvnDiagPLStom_IsClosed,
				ISNULL(EDPLS.EvnDiagPLStom_HalfTooth, 1) as EvnDiagPLStom_HalfTooth,
				EDPLS.EvnDiagPLStom_NumGroup,
				EDPLS.Diag_id,
				RTrim(Diag.Diag_Code) as Diag_Code,
				RTrim(Diag.Diag_Name) as Diag_Name,
				Tooth.Tooth_Code,
				mes.Mes_Code,
				mes.Mes_Name,
				ROUND(cast(case
					when ISNULL(EDPLS.EvnDiagPLStom_IsClosed, 1) = 1 then 0
					when EDPLS.Mes_id is not null then mes.Mes_KoikoDni -- УЕТ (норматив по КСГ)
					else ISNULL(UET1.EvnUslugaStom_Summa, 0) + ISNULL(UET2.EvnUslugaStom_Summa, 0) -- УЕТ (факт по ОМС)
				end as float), 2) EvnDiagPLStom_Uet
				" . (count($select) > 0 ? "," . implode(",", $select) : "") . "
			from v_EvnDiagPLStom EDPLS with (nolock)
				inner join v_EvnVizitPLStom EVPLS with (nolock) on EVPLS.EvnVizitPLStom_id = EDPLS.EvnDiagPLStom_pid
				outer apply (
					select
						SUM(eus.EvnUslugaStom_Summa) as EvnUslugaStom_Summa
					from
						v_EvnUslugaStom eus (nolock)
						inner join v_PayType pt (nolock) on pt.PayType_id = eus.PayType_id and pt.PayType_SysNick = 'oms'
					where
						eus.EvnDiagPLStom_id = EDPLS.EvnDiagPLStom_id
						and ISNULL(eus.EvnUslugaStom_IsAllMorbus, 1) = 1
						and ISNULL(eus.EvnUslugaStom_IsVizitCode, 1) = 1
				) UET1
				outer apply (
					select
						SUM(eus.EvnUslugaStom_Summa) as EvnUslugaStom_Summa
					from
						v_EvnUslugaStom eus (nolock)
						inner join v_PayType pt (nolock) on pt.PayType_id = eus.PayType_id and pt.PayType_SysNick = 'oms'
					where
						eus.EvnUslugaStom_rid = EDPLS.EvnDiagPLStom_rid
						and eus.EvnUslugaStom_IsAllMorbus = 2 
						and ISNULL(eus.EvnUslugaStom_IsVizitCode, 1) = 1
				) UET2
				left join Diag with (nolock) on Diag.Diag_id = EDPLS.Diag_id
				left join DeseaseType DT with (nolock) on DT.DeseaseType_id = EDPLS.DeseaseType_id
				left join v_Tooth Tooth with (nolock) on Tooth.Tooth_id = EDPLS.Tooth_id
				left join v_MesOld mes with (nolock) on mes.Mes_id = EDPLS.Mes_id
				" . implode(" ", $join) . "
			where EDPLS.Lpu_id " . getLpuIdFilter($data) . "
				" . (!empty($data['pid']) ? "and EDPLS.EvnDiagPLStom_pid = :EvnDiagPLStom_pid" : "") . "
				" . (!empty($data['rid']) ? "and EDPLS.EvnDiagPLStom_rid = :EvnDiagPLStom_rid" : "") . "
		";
		$result = $this->db->query($query, array(
			'EvnDiagPLStom_pid' => $data['pid'],
			'EvnDiagPLStom_rid' => $data['rid'],
			'Lpu_id' => $data['Lpu_id'],
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 * loadEvnDiagPLStomCombo
	 * @param $data
	 * @return bool
	 */
	function loadEvnDiagPLStomCombo($data) {
		$this->load->helper('MedStaffFactLink');
		$med_personal_list = getMedPersonalListWithLinks();

		$query = "
			select
				EDPLS.EvnDiagPLStom_id,
				EDPLS.EvnDiagPLStom_pid,
				EDPLS.EvnDiagPLStom_rid,
				convert(varchar(10), EDPLS.EvnDiagPLStom_setDT, 104) as EvnDiagPLStom_setDate,
				convert(varchar(10), EDPLS.EvnDiagPLStom_disDT, 104) as EvnDiagPLStom_disDate,
				D.Diag_Code,
				T.Tooth_Code,
				M.Mes_Code,
				M.Mes_Name,
				convert(varchar(10), EDPLS.EvnDiagPLStom_setDT, 104) + ' / Диагноз ' + ISNULL(D.Diag_Code,'') + ' / Номер зуба ' + cast(ISNULL(T.Tooth_Code,'') as varchar(2)) as EvnDiagPLStom_Title
			from v_EvnDiagPLStom EDPLS with (nolock)
				left join v_Diag D with (nolock) on D.Diag_id = EDPLS.Diag_id
				left join v_Tooth T with (nolock) on T.Tooth_id = EDPLS.Tooth_id
				left join v_MesOld M with (nolock) on M.Mes_id = EDPLS.Mes_id
			where
				EDPLS.EvnDiagPLStom_rid = :EvnDiagPLStom_rid
		";
		$result = $this->db->query($query, array(
			'EvnDiagPLStom_rid' => $data['EvnDiagPLStom_rid']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 *	Сохранение основного диагноза в стоматологии
	 */
	function saveEvnDiagPLStom($data)
	{
		if (!empty($data['EvnDiagPLStom_IsClosed']) && $data['EvnDiagPLStom_IsClosed']) {
			$data['EvnDiagPLStom_IsClosed'] = 2;
		} else {
			$data['EvnDiagPLStom_IsClosed'] = 1;
		}

		if (!empty($data['EvnDiagPLStom_IsZNO']) && $data['EvnDiagPLStom_IsZNO']) {
			$data['EvnDiagPLStom_IsZNO'] = 2;
		} else {
			$data['EvnDiagPLStom_IsZNO'] = 1;
			$data['Diag_spid'] = null;
		}

		if (!empty($data['EvnDiagPLStom_HalfTooth']) && $data['EvnDiagPLStom_HalfTooth']) {
			$data['EvnDiagPLStom_HalfTooth'] = 2;
		} else {
			$data['EvnDiagPLStom_HalfTooth'] = 1;
		}

		if ( !empty($data['EvnDiagPLStom_disDate']) ) {
			// Дата окончания не может быть больше максимальной (самой поздней) даты посещения текущего ТАП
			$query = "
					select case when max(evpls.EvnVizitPLStom_setDate) < :EvnDiagPLStom_disDate then 1 else 0 end as checkResult
					from v_EvnVizitPLStom evpls with (nolock)
					where evpls.EvnVizitPLStom_rid = (select top 1 Evn_rid from v_Evn with (nolock) where Evn_id = :EvnDiagPLStom_pid)
				";
			$result = $this->db->query($query, array(
				'EvnDiagPLStom_disDate' => $data['EvnDiagPLStom_disDate'],
				'EvnDiagPLStom_pid' => $data['EvnDiagPLStom_pid']
			));

			if ( !is_object($result) ) {
				return false;
			}

			$response = $result->result('array');

			if ( is_array($response) && count($response) > 0 && !empty($response[0]['EvnVizitPLStom_id']) ) {
				return array(array('Error_Msg' => 'Дата окончания не может быть больше максимальной (самой поздней) даты посещения текущего ТАП'));
			}
		}

		//yl:176490 запрет добавлять заболевания в закрытый случай и случай другого врача
		if (empty($data["EvnDiagPLStom_id"]) || $data["EvnDiagPLStom_id"] <= 0) {//это add
			$CurMedStaffFact_id = !empty($data["CurMedStaffFact_id"]) ? $data["CurMedStaffFact_id"] : $data["session"]["CurMedStaffFact_id"];
			if (!empty($CurMedStaffFact_id)) {//не факт что есть даже на сервере
				$result = $this->checkDiagPLStom(array("EvnDiagPLStom_pid" => $data["EvnDiagPLStom_pid"]));
				if (!is_array($result) || count($result) != 1) {
					return array(array("Error_Msg" => "Ошибка БД при добавлении заболевания в checkDiagPLStom"));
				};
				if ($result[0]["EvnPLStom_IsFinish"] == 2) {//случай закрыт
					return array(array("Error_Msg" => "Случай стоматологического лечения закрыт!"));
				};
				if ($result[0]["MedStaffFact_id"] != $CurMedStaffFact_id) {//случай другого врача
					return array(array("Error_Msg" => "Нельзя добавлять заболевание в случай другого врача!"));
				};
			};
		};

		if ( $data['EvnDiagPLStom_IsClosed'] == 1 ) {
			// Заболевание не может быть незакрыто, если случай лечения закончен
			$query = "
					select ISNULL(EPLS.EvnPLStom_IsFinish, 1) as EvnPLStom_IsFinish
					from v_EvnPLStom EPLS with (nolock)
					where EPLS.EvnPLStom_id = (select top 1 Evn_rid from v_Evn with (nolock) where Evn_id = :EvnDiagPLStom_pid)
				";
			$result = $this->db->query($query, array(
				'EvnDiagPLStom_pid' => $data['EvnDiagPLStom_pid']
			));

			if ( !is_object($result) ) {
				return false;
			}

			$response = $result->result('array');

			if ( is_array($response) && count($response) > 0 && $response[0]['EvnPLStom_IsFinish'] == 2 ) {
				return array(array('Error_Msg' => 'Заболевание должно быть закрыто, т.к. случай лечения закончен.'));
			}
		}
		
		if (!empty($data['EvnDiagPLStom_id']) && empty($data['ignoreCheckMorbusOnko']) ) {
			$tmp = $this->getFirstResultFromQuery("
				select top 1 MorbusOnkoDiagPLStom_id
				from v_MorbusOnkoDiagPLStom with (nolock)
				where 
					EvnDiagPLStom_id = :Evn_id and 
					Diag_id != :Diag_id and 
					EvnDiagPLStomSop_id is null
			", array(
				'Evn_id' => $data['EvnDiagPLStom_id'],
				'Diag_id' => $data['Diag_id']
			));
			if ($tmp > 0) { 
				return array('Error_Code' => '289', 'ignoreParam' => 'ignoreCheckMorbusOnko', 'Error_Msg' => '', 'Alert_Msg' => 'При изменении диагноза данные раздела «Специфика (онкология)», связанные с текущим диагнозом, будут удалены. Продолжить сохранение?');
			}
		}

		$checkMorbusOnkoDiagPLStom = $this->_checkMorbusOnkoDiagPLStom($data);

		if ( is_array($checkMorbusOnkoDiagPLStom) ) {
			return $checkMorbusOnkoDiagPLStom;
		}

		// @task https://redmine.swan.perm.ru/issues/143747
		// @task https://redmine.swan.perm.ru/issues/144888
		// @task https://redmine.swan.perm.ru/issues/145682
		// @task https://redmine.swan-it.ru/issues/152044
		/*if (
			$this->regionNick != 'ekb'
			&& $this->regionNick != 'kz'
			&& $this->regionNick != 'astra'
			&& !empty($data['EvnDiagPLStom_id'])
			&& $data['EvnDiagPLStom_IsClosed'] == 2
			&& (
				!in_array($this->regionNick, array('kareliya', 'krym'))
				|| empty($data['ignoreCheckTNM'])
			)
		) {
			$checkList = array(
				'MainDiagCode' => null,
				'SopDiagCodes' => array(),
			);

			// Проверяем основной диагноз
			$diagHasLink = $this->getFirstRowFromQuery("
				select top 1
					lnk.OnkoTNMDiag_id,
					d.Diag_Code
				from fed.v_OnkoTNMDiag lnk with (nolock)
					inner join v_Diag d on d.Diag_id = lnk.Diag_id
				where lnk.Diag_id = :Diag_id
					and (lnk.OnkoTNMDiag_begDate is null or lnk.OnkoTNMDiag_begDate <= :Date)
					and (lnk.OnkoTNMDiag_endDate is null or lnk.OnkoTNMDiag_endDate >= :Date)
			", array(
				'Date' => (!empty($data['EvnDiagPLStom_disDate']) ? $data['EvnDiagPLStom_disDate'] : date('Y-m-d')),
				'Diag_id' => $data['Diag_id'],
			));

			if ( is_array($diagHasLink) && count($diagHasLink) > 0 ) {
				$checkResult = $this->getFirstRowFromQuery("
					select top 1 modps.MorbusOnkoDiagPLStom_id, lnkTab.OnkoTNMDiag_id
					from v_MorbusOnkoDiagPLStom modps with (nolock)
						outer apply (
							select top 1 OnkoTNMDiag_id
							from fed.v_OnkoTNMDiag with (nolock)
							where Diag_id = :Diag_id
								and TumorStage_id = modps.TumorStage_id
								and OnkoT_id = modps.OnkoT_id
								and OnkoN_id = modps.OnkoN_id
								and OnkoM_id = modps.OnkoM_id
								and (OnkoTNMDiag_begDate is null or OnkoTNMDiag_begDate <= :Date)
								and (OnkoTNMDiag_endDate is null or OnkoTNMDiag_endDate >= :Date)
						) lnkTab
					where modps.EvnDiagPLStom_id = :EvnDiagPLStom_id
				", array(
					'EvnDiagPLStom_id' => $data['EvnDiagPLStom_id'],
					'Date' => (!empty($data['EvnDiagPLStom_disDate']) ? $data['EvnDiagPLStom_disDate'] : date('Y-m-d')),
					'Diag_id' => $data['Diag_id'],
				), true);

				if ( $checkResult !== false && is_array($checkResult) && count($checkResult) > 0 && empty($checkResult['OnkoTNMDiag_id']) ) {
					$checkList['MainDiagCode'] = $diagHasLink['Diag_Code'];
				}
			}

			// Проверяем сопутствующие диагнозы
			$diagHasLink = $this->getFirstRowFromQuery("
				with SopDiagList as (
					select Diag_id
					from v_EvnDiagPLStomSop with (nolock)
					where EvnDiagPLStomSop_pid = :EvnDiagPLStom_id
				)

				select
					lnk.OnkoTNMDiag_id,
					d.Diag_Code
				from fed.v_OnkoTNMDiag lnk with (nolock)
					inner join v_Diag d on d.Diag_id = lnk.Diag_id
				where lnk.Diag_id in (select Diag_id from SopDiagList)
					and (lnk.OnkoTNMDiag_begDate is null or lnk.OnkoTNMDiag_begDate <= :Date)
					and (lnk.OnkoTNMDiag_endDate is null or lnk.OnkoTNMDiag_endDate >= :Date)
			", array(
				'Date' => (!empty($data['EvnDiagPLStom_disDate']) ? $data['EvnDiagPLStom_disDate'] : date('Y-m-d')),
				'EvnDiagPLStom_id' => $data['EvnDiagPLStom_id']
			));

			if ( is_array($diagHasLink) && count($diagHasLink) > 0 ) {
				$checkResult = $this->queryResult("
					select modps.MorbusOnkoDiagPLStom_id, lnkTab.OnkoTNMDiag_id, d.Diag_Code
					from v_EvnDiagPLStomSop edplss with (nolock)
						inner join v_MorbusOnkoDiagPLStom modps on modps.EvnDiagPLStomSop_id = edplss.EvnDiagPLStomSop_id
						inner join v_Diag d on d.Diag_id = edplss.Diag_id
						outer apply (
							select top 1 OnkoTNMDiag_id
							from fed.v_OnkoTNMDiag with (nolock)
							where Diag_id = edplss.Diag_id
								and TumorStage_id = modps.TumorStage_id
								and OnkoT_id = modps.OnkoT_id
								and OnkoN_id = modps.OnkoN_id
								and OnkoM_id = modps.OnkoM_id
								and (OnkoTNMDiag_begDate is null or OnkoTNMDiag_begDate <= :Date)
								and (OnkoTNMDiag_endDate is null or OnkoTNMDiag_endDate >= :Date)
						) lnkTab
					where modps.EvnDiagPLStom_id = :EvnDiagPLStom_id
				", array(
					'EvnDiagPLStom_id' => $data['EvnDiagPLStom_id'],
					'Date' => (!empty($data['EvnDiagPLStom_disDate']) ? $data['EvnDiagPLStom_disDate'] : date('Y-m-d')),
					'Diag_id' => $data['Diag_id'],
				), true);

				if ( $checkResult !== false && is_array($checkResult) && count($checkResult) > 0 ) {
					foreach ( $checkResult as $row ) {
						if ( empty($row['OnkoTNMDiag_id']) ) {
							$checkList['SopDiagCodes'][] = $row['Diag_Code'];
						}
					}
				}
			}

			if ( !empty($checkList['MainDiagCode']) && count($checkList['SopDiagCodes']) > 0 ) {
				if ( in_array($this->regionNick, array('astra', 'kareliya', 'krym')) ) {
					return array('Error_Code' => '181', 'ignoreParam' => 'ignoreCheckTNM', 'Error_Msg' => '', 'Alert_Msg' => 'Стадии опухолевого процесса специфик по основному и сопутствующему диагнозам заболевания не соответствуют возможным значениям справочника соответствия стадий TNM (N006) для диагнозов ' . $checkList['MainDiagCode'] . ', ' . implode(', ', $checkList['SopDiagCodes']) . '. Продолжить сохранение?');
				}
				else {
					// @original Стадии опухолевого процесса специфик по основному и сопутствующему диагнозам заболевания не соответствуют возможным значениям справочника соответствия стадий TNM (N006) для диагнозов <Список диагнозов>. Проверьте корректность заполнения стадий опухолевого процесса
					throw new Exception('Стадии опухолевого процесса специфик по основному и сопутствующему диагнозам заболевания не соответствуют возможным значениям справочника соответствия стадий TNM (N006) для диагнозов ' . $checkList['MainDiagCode'] . ', ' . implode(', ', $checkList['SopDiagCodes']) . '. Проверьте корректность заполнения стадий опухолевого процесса.');
				}
			}

			if ( !empty($checkList['MainDiagCode']) ) {
				if ( in_array($this->regionNick, array('astra', 'kareliya', 'krym')) ) {
					return array('Error_Code' => '181', 'ignoreParam' => 'ignoreCheckTNM',, 'Error_Msg' => '', 'Alert_Msg' => 'Стадии опухолевого процесса специфики по основному диагнозу не соответствуют возможным значениям справочника соответствия стадий TNM (N006) для диагноза ' . $checkList['MainDiagCode'] . '. Продолжить сохранение?');
				}
				else {
					// @original Стадии опухолевого процесса специфики основного заболевания движения не соответствуют возможным значениям справочника соответствия стадий TNM (N006) для диагноза <Диагноз>. Проверьте корректность заполнения стадий опухолевого процесса
					throw new Exception('Стадии опухолевого процесса специфики по основному диагнозу не соответствуют возможным значениям справочника соответствия стадий TNM (N006) для диагноза ' . $checkList['MainDiagCode'] . '. Проверьте корректность заполнения стадий опухолевого процесса.');
				}
			}

			if ( count($checkList['SopDiagCodes']) > 0 ) {
				if ( in_array($this->regionNick, array('astra', 'kareliya', 'krym')) ) {
					return array('Error_Code' => '181', 'ignoreParam' => 'ignoreCheckTNM',, 'Error_Msg' => '', 'Alert_Msg' => 'Стадии опухолевого процесса специфики по сопутствующему диагнозу заболевания не соответствуют возможным значениям справочника соответствия стадий TNM (N006) для диагноз' . (count($checkList['SopDiagCodes']) == 1 ? 'а' : 'ов') . ' ' . implode(', ', $checkList['SopDiagCodes']) . '. Продолжить сохранение?');
				}
				else {
					// @original Стадии опухолевого процесса специфики по сопутствующему диагнозу заболевания не соответствуют возможным значениям справочника соответствия стадий TNM (N006) для диагноза <Диагноз>. Проверьте корректность заполнения стадий опухолевого процесса
					throw new Exception('Стадии опухолевого процесса специфики по сопутствующему диагнозу заболевания не соответствуют возможным значениям справочника соответствия стадий TNM (N006) для диагноз' . (count($checkList['SopDiagCodes']) == 1 ? 'а' : 'ов') . ' ' . implode(', ', $checkList['SopDiagCodes']) . '. Проверьте корректность заполнения стадий опухолевого процесса.');
				}
			}
		}*/

		if ( $this->regionNick == 'buryatiya' ) {
			/**
			 * @task https://redmine.swan-it.ru//issues/163363
			 */
			if ( $data['EvnDiagPLStom_IsClosed'] == 2 && !empty($data['EvnDiagPLStom_id']) ) {
				$uslugaCount = $this->getFirstResultFromQuery("
					select top 1 EvnUslugaStom_id
					from v_EvnUslugaStom with (nolock)
					where EvnDiagPLStom_id = :EvnDiagPLStom_id
				", $data, true);

				if ( empty($uslugaCount) ) {
					return array('Error_Code' => __LINE__, 'Error_Msg' => 'Закрытое заболевание должно содержать хотя бы одну услугу');
				}
			}
		}

		if ( $this->regionNick == 'perm' ) {
			// @task https://redmine.swan.perm.ru/issues/78034
			if (empty($data['ignoreUetSumInNonMorbusCheck']) && $data['EvnDiagPLStom_IsClosed'] == 2 && !empty($data['Diag_id']) && empty($data['Mes_id']) && !empty($data['EvnDiagPLStom_id'])) {
				$query = "
					declare @Person_Age int;

					set @Person_Age = (
						select top 1 dbo.Age2(Person_BirthDay, cast(:EvnDiagPLStom_setDate as date))
						from v_PersonState with (nolock)
						where Person_id = :Person_id
					)

					select top 1
						d.Diag_Code
					from v_MesOld mo with (nolock)
						inner join v_Diag d with (nolock) on d.Diag_id = mo.Diag_id
							and d.Diag_id = :Diag_id
						outer apply (
							select SUM(eus.EvnUslugaStom_UED) as EvnUslugaStom_UED
							from v_EvnUslugaStom eus with (nolock)
								inner join v_PayType pt with (nolock) on pt.PayType_id = eus.PayType_id
									and pt.PayType_SysNick = 'oms'
							where eus.EvnDiagPLStom_id = :EvnDiagPLStom_id
								and ISNULL(eus.EvnUslugaStom_IsAllMorbus, 1) = 1
						) uet
					where 
						mo.MesType_id = 7
						and mo.Lpu_id is null
						and mo.Mes_KoikoDni is not null
						and (
							mo.MesAgeGroup_id is null
							or (mo.MesAgeGroup_id = 1 and @Person_Age >= 18)
							or (mo.MesAgeGroup_id = 2 and @Person_Age < 18)
						)
						and mo.Mes_begDT <= cast(:EvnDiagPLStom_setDate as datetime)
						and (mo.Mes_endDT is null or mo.Mes_endDT >= cast(:EvnDiagPLStom_setDate as datetime))
						and uet.EvnUslugaStom_UED is not null
						and mo.Mes_KoikoDni < uet.EvnUslugaStom_UED
				";
				$resp = $this->queryResult($query, array(
					'Diag_id' => $data['Diag_id'],
					'EvnDiagPLStom_id' => $data['EvnDiagPLStom_id'],
					'EvnDiagPLStom_setDate' => $data['EvnDiagPLStom_setDate'],
					'Person_id' => $data['Person_id']
				));

				if ( is_array($resp) && count($resp) == 1 ) {
					return array('Error_Code' => '129', 'ignoreParam' => 'ignoreUetSumInNonMorbusCheck', 'Error_Msg' => '', 'Alert_Msg' => 'Суммарное количество УЕТ заболевания не должно превышать максимального значения УЕТ по любой КСГ с диагнозом заболевания. Продолжить сохранение?');
				}
			}

			if (empty($data['ignoreEmptyKsg']) && $data['EvnDiagPLStom_IsClosed'] == 2 && empty($data['Mes_id']) && !empty($data['EvnDiagPLStom_id']) && !empty($data['KSGlist'])) {
				// Если в заболевании указана дата закрытия и сохранена хотя бы одна услуга и не указан КСГ, но для указанных Диагноза и номера зуба он может быть выбран, то выводить предупреждение
				// В заболевании сохранены %Х из %%У обязательных услуг по % код КСГ. Выбрать КСГ / Сохранить
				// Примечание. КСГ определять по максимальной расчетной величине:
				// кол-во заведенных услуг по КСГ / общее кол-во обязательных услуг в КСГ
				$query = "
					select top 1
						mo.Mes_Code,
						mneed.cnt as CountNeed,
						mexist.cnt as CountExists
					from
						v_MesOld mo (nolock)
						outer apply(
							select
								count(*) as cnt
							from
								v_MesUsluga mu (nolock)
							where
								mu.Mes_id = mo.Mes_id
								and mu.MesUsluga_IsNeedUsluga = 2
						) mneed
						outer apply(
							select
								count(*) as cnt
							from
								v_MesUsluga mu (nolock)
								cross apply(
									select top 1
										eus.EvnUslugaStom_id
									from
										v_EvnUslugaStom eus (nolock)
									where
										eus.UslugaComplex_id = mu.UslugaComplex_id
										and eus.EvnDiagPLStom_id = :EvnDiagPLStom_id
								) EUSLUGA
							where
								mu.Mes_id = mo.Mes_id
								and mu.MesUsluga_IsNeedUsluga = 2
						) mexist
					where
						mo.Mes_id in ('".implode("','", $data['KSGlist'])."')
						and mneed.cnt > 0
					order by
						mexist.cnt/mneed.cnt
				";
				$resp = $this->queryResult($query, array(
					'EvnDiagPLStom_id' => $data['EvnDiagPLStom_id']
				));

				if (!empty($resp[0]['CountExists'])) {
					return array('Error_Msg' => '', 'Alert_Msg' => 'В заболевании сохранены '.$resp[0]['CountExists'].' из '.$resp[0]['CountNeed'].' обязательных услуг по '.$resp[0]['Mes_Code'].' КСГ');
				}
			}

			if ( $data['EvnDiagPLStom_IsClosed'] == 2 && !empty($data['Mes_id']) && empty($data['isAutoCreate']) ) {
				// Если в заболевании указана дата закрытия и выбрано КСГ, то реализовать контроль на заведение всех обязательных услуг по выбранному КСГ. При
				// невыполнении данного контроля выводить предупреждение: «Сохранены не все обязательные услуги по КСГ. ОК». При нажатии «ОК» сообщение закрыть,
				// сохранение заболевания отменить
				$query = "
					-- Если у услуги указан атрибут «обязательность» и НЕ указан атрибут «заменяемость», то  данная услуга должна быть сохранена в заболевании
					select top 1 Mes_id
					from v_MesUsluga with (nolock)
					where Mes_id = :Mes_id
						and ISNULL(MesUsluga_IsNeedUsluga, 1) = 2
						and MesUsluga_GroupNum is null
						and UslugaComplex_id not in (
							select UslugaComplex_id
							from v_EvnUslugaStom with (nolock)
							where EvnDiagPLStom_id = ISNULL(:EvnDiagPLStom_id, 0)
						)
						and ISNULL(MesUsluga_begDT, :EvnDiagPLStom_setDate) <= :EvnDiagPLStom_setDate
						and ISNULL(MesUsluga_endDT, :EvnDiagPLStom_setDate) >= :EvnDiagPLStom_setDate

					union

					-- Если у услуги проставлен атрибут «обязательность» и указан атрибут «заменяемость», то в заболевании должно быть сохранено такое количество услуг из данной группы
					-- которое указано в атрибуте «кол-во для выбора». Если атрибут «кол-во для выбора» не указан, то в заболевании должна быть сохранена хотя бы одна услуга из группы.

					-- Смена концепции
					-- https://redmine.swan.perm.ru/issues/87959

					-- Если у услуги проставлен атрибут «обязательность» и указан атрибут «заменяемость», то в заболевании должна быть сохранена хотя бы одна услуга из
					-- группы.
					select top 1 me1.Mes_id
					from v_MesUsluga me1 with (nolock)
					where me1.Mes_id = :Mes_id
						and ISNULL(me1.MesUsluga_IsNeedUsluga, 1) = 2
						and me1.MesUsluga_GroupNum is not null
						and ISNULL(me1.MesUsluga_begDT, :EvnDiagPLStom_setDate) <= :EvnDiagPLStom_setDate
						and ISNULL(me1.MesUsluga_endDT, :EvnDiagPLStom_setDate) >= :EvnDiagPLStom_setDate
						and not exists (
							select top 1 eus.EvnUslugaStom_id
							from
								v_EvnUslugaStom eus (nolock)
								inner join v_MesUsluga mu (nolock) on mu.UslugaComplex_id = eus.UslugaComplex_id
									and mu.Mes_id = :Mes_id
									and mu.MesUsluga_GroupNum = me1.MesUsluga_GroupNum
							where
								eus.EvnDiagPLStom_id = ISNULL(:EvnDiagPLStom_id, 0)
						)
				";
				$result = $this->db->query($query, array(
					'EvnDiagPLStom_id' => $data['EvnDiagPLStom_id'],
					'EvnDiagPLStom_setDate' => $data['EvnDiagPLStom_setDate'],
					'Mes_id' => $data['Mes_id']
				));

				if ( !is_object($result) ) {
					return false;
				}

				$response = $result->result('array');

				if ( is_array($response) && count($response) > 0 && !empty($response[0]['Mes_id']) ) {
					return array(array('Error_Msg' => 'Сохранены не все обязательные услуги по КСГ'));
				}

				// Если в заболевании проставлено значение «Да» в поле «Заболевание закрыто» и выбрано КСГ, то реализовать контроль на количество услуг с указанным
				// одинаковым атрибутом «заменяемость» – количество услуг с таким атрибутом должно быть таким, какое указано в атрибуте «Кол-во для выбора». Если
				// количество услуг с одинаковым атрибутом «заменяемость» не соответствует значению атрибута  «Кол-во для выбора» то выводить предупреждение:
				// «Сохранены взаимоисключающие (заменяемые) услуги по указанной КСГ. ОК». При нажатии «ОК» сообщение закрыть, сохранение заболевания отменить.
				/*$query = "
					select top 1 me1.Mes_id
					from v_MesUsluga me1 with (nolock)
						outer apply (
							select
								count(distinct eus.UslugaComplex_id) as cnt
							from
								v_EvnUslugaStom eus (nolock)
								inner join v_MesUsluga mu (nolock) on mu.UslugaComplex_id = eus.UslugaComplex_id
									and mu.Mes_id = :Mes_id
									and mu.MesUsluga_GroupNum = me1.MesUsluga_GroupNum
									and mu.MesUsluga_VarietyCount is not null
							where
								eus.EvnDiagPLStom_id = ISNULL(:EvnDiagPLStom_id, 0)
						) EUCount
					where me1.Mes_id = :Mes_id
						and ISNULL(me1.MesUsluga_IsNeedUsluga, 1) = 2
						and me1.MesUsluga_GroupNum > 0
						and me1.MesUsluga_VarietyCount > 0
						and EUCount.cnt > me1.MesUsluga_VarietyCount
				";*/

				// Смена концепции
				// @task https://redmine.swan.perm.ru/issues/87959

				// Если в заболевании проставлено значение «Да» в поле «Заболевание закрыто» и выбрано КСГ, то реализовать контроль на количество услуг с указанным
				// одинаковым атрибутом «заменяемость». Количество услуг с таким атрибутом не должно превосходить значение, которое указано в атрибуте «Кол-во для
				// выбора» у указанных в заболевании услуг (услуг выбранных в заболевании). Если количество услуг с одинаковым атрибутом «заменяемость» больше
				// значения в атрибуте «Кол-во для выбора» хотя бы одной из указанных услуг, то выводить предупреждение: «Сохранены взаимоисключающие (заменяемые)
				// услуги по указанной КСГ. ОК». При нажатии «ОК» сообщение закрыть, сохранение заболевания отменить. Если у услуги в «Кол-во для выбора» указано
				// null, то считаем что эта услуга не ограничивает выбор других услуг. Например, одновременно может быть указано несколько услуг с null
				$query = "
					select top 1 mu.MesUsluga_GroupNum
					from v_EvnUslugaStom eus (nolock)
						inner join v_MesUsluga mu (nolock) on mu.UslugaComplex_id = eus.UslugaComplex_id
					where 
						eus.EvnDiagPLStom_id = ISNULL(:EvnDiagPLStom_id, 0)
						and mu.Mes_id = :Mes_id
						and mu.MesUsluga_GroupNum is not null
						and mu.MesUsluga_VarietyCount is not null
						and ISNULL(mu.MesUsluga_IsNeedUsluga, 1) = 2
					group by mu.MesUsluga_GroupNum
					having count(distinct eus.UslugaComplex_id) > min(mu.MesUsluga_VarietyCount)
				";
				$result = $this->db->query($query, array(
					'EvnDiagPLStom_id' => $data['EvnDiagPLStom_id'],
					'Mes_id' => $data['Mes_id']
				));

				if ( !is_object($result) ) {
					return false;
				}

				$response = $result->result('array');

				if ( is_array($response) && count($response) > 0 && !empty($response[0]['MesUsluga_GroupNum']) ) {
					return array(array('Error_Msg' => 'Сохранены взаимоисключающие (заменяемые) услуги по указанной КСГ'));
				}

				// Если в заболевании указана дата закрытия и выбрано КСГ и на дату окончания заболевания нет тарифа на КСГ, то выводить предупреждение: «У выбранного
				// КСГ не заведен тариф. Выберете другой КСГ или обратитесь к разработчикам. ОК». При нажатии «ОК» сообщение закрыть,  сохранение заболевания отменить
				$query = "
					select top 1 MesTariff_id
					from v_MesTariff with (nolock)
					where Mes_id = :Mes_id
						and (MesTariff_begDT is null or MesTariff_begDT <= :EvnDiagPLStom_disDate)
						and (MesTariff_endDT is null or MesTariff_endDT >= :EvnDiagPLStom_disDate)
				";
				$result = $this->db->query($query, array(
					'EvnDiagPLStom_disDate' => (!empty($data['EvnDiagPLStom_disDate']) ? $data['EvnDiagPLStom_disDate'] : $data['EvnDiagPLStom_setDate']),
					'Mes_id' => $data['Mes_id']
				));

				if ( !is_object($result) ) {
					return false;
				}

				$response = $result->result('array');

				if ( !is_array($response) || count($response) == 0 || empty($response[0]['MesTariff_id']) ) {
					return array(array('Error_Msg' => 'У выбранной КСГ не заведен тариф. Выберите другой КСГ или обратитесь к разработчикам.'));
				}
			}

			// У заболеваний с одинаковыми атрибутами «КСГ» и «Номер зуба» (в рамках текущего ТАП) не должны пресекаться периоды (дата начала – дата окончания).
			// При невыполнении данного контроля выводить сообщение «Период (дата начала – дата окончания) заболеваний с одинаковыми атрибутами «КСГ» и «Номер зуба»
			// не могут пересекаться. ОК». При нажатии «ОК» сообщение закрыть,  сохранение заболевания отменить.

			$query = "
				with ParentEvn (
					Evn_rid
				) as (
					select top 1 Evn_rid
					from v_Evn with (nolock)
					where Evn_id = :EvnDiagPLStom_pid
				),
				Mes (
					Mes_Code
				) as (
					select top 1 Mes_Code
					from v_MesOld with (nolock)
					where Mes_id = :Mes_id
				)

				select
					edpls.EvnDiagPLStom_id,
					edpls.EvnDiagPLStom_setDT,
					edpls.EvnDiagPLStom_disDT
				from v_EvnDiagPLStom edpls with (nolock)
					inner join ParentEvn pe on pe.Evn_rid = edpls.EvnDiagPLStom_rid
					inner join v_Tooth t with (nolock) on t.Tooth_id = edpls.Tooth_id
					inner join v_MesOld m with (nolock) on m.Mes_id = edpls.Mes_id
				where m.Mes_Code = (select top 1 Mes_Code from Mes with(nolock))
					and t.Tooth_Code = :Tooth_Code
					and edpls.EvnDiagPLStom_id != ISNULL(:EvnDiagPLStom_id, 0)
			";
			$result = $this->db->query($query, array(
				'EvnDiagPLStom_id' => $data['EvnDiagPLStom_id'],
				'EvnDiagPLStom_pid' => $data['EvnDiagPLStom_pid'],
				'Mes_id' => $data['Mes_id'],
				'Tooth_Code' => $data['Tooth_Code']
			));

			if ( !is_object($result) ) {
				return false;
			}

			$response = $result->result('array');

			if ( is_array($response) && count($response) > 0 ) {
				foreach ( $response as $row ) {
					if (
						(empty($row['EvnDiagPLStom_disDT']) || strtotime($data['EvnDiagPLStom_setDate']) <= $row['EvnDiagPLStom_disDT']->getTimestamp())
						&& (empty($data['EvnDiagPLStom_disDate']) || empty($row['EvnDiagPLStom_setDT']) || $row['EvnDiagPLStom_setDT']->getTimestamp() <= strtotime($data['EvnDiagPLStom_disDate']))
					) {
						$type1 = 'Error_Msg';
						$type2 = 'Alert_Msg';
						if (!empty($data['EvnDiagPLStom_IsClosed']) && $data['EvnDiagPLStom_IsClosed'] == 2) {
							$type1 = 'Alert_Msg';
							$type2 = 'Error_Msg';
						}

						if ($type2 == 'Error_Msg' || empty($data['ignoreCheckKSGPeriod'])) {
							return array(array('Error_Code' => '130', $type1 => '', $type2 => 'Период (дата начала – дата окончания) заболеваний с одинаковыми атрибутами «КСГ» и «Номер зуба» не могут пересекаться.'.(($type2=='Alert_Msg')?' Продолжить сохранение?':'')));
						}
					}
				}
			}
		} else {
			// Реализовать запрет на сохранение заболеваний с одинаковыми значениями полей «Диагноз» и «Зуб». аналогично Перми, с периодом
			$query = "
				with ParentEvn (
					Evn_rid
				) as (
					select top 1 Evn_rid
					from v_Evn with (nolock)
					where Evn_id = :EvnDiagPLStom_pid
				)

				select top 1
					edpls.EvnDiagPLStom_id,
					edpls.EvnDiagPLStom_setDT,
					edpls.EvnDiagPLStom_disDT
				from v_EvnDiagPLStom edpls with (nolock)
					inner join ParentEvn pe on pe.Evn_rid = edpls.EvnDiagPLStom_rid
					left join v_Tooth t with (nolock) on t.Tooth_id = edpls.Tooth_id
				where ISNULL(t.Tooth_Code,'') = ISNULL(:Tooth_Code, '')
					and ISNULL(edpls.Diag_id, 0) = ISNULL(:Diag_id, 0)
					and edpls.EvnDiagPLStom_id != ISNULL(:EvnDiagPLStom_id, 0)
			";
			$result = $this->db->query($query, array(
				'EvnDiagPLStom_id' => $data['EvnDiagPLStom_id'],
				'EvnDiagPLStom_pid' => $data['EvnDiagPLStom_pid'],
				'Diag_id' => $data['Diag_id'],
				'Tooth_Code' => $data['Tooth_Code']
			));

			if ( !is_object($result) ) {
				return false;
			}

			$response = $result->result('array');

			if ( is_array($response) && count($response) > 0 ) {
				foreach ( $response as $row ) {
					if (
						(empty($row['EvnDiagPLStom_disDT']) || strtotime($data['EvnDiagPLStom_setDate']) <= $row['EvnDiagPLStom_disDT']->getTimestamp())
						&& (empty($data['EvnDiagPLStom_disDate']) || empty($row['EvnDiagPLStom_setDT']) || $row['EvnDiagPLStom_setDT']->getTimestamp() <= strtotime($data['EvnDiagPLStom_disDate']))
					) {
						return array(array('Error_Msg' => 'Период (дата начала – дата окончания) заболеваний с одинаковыми атрибутами «Диагноз» и «Номер зуба» не могут пересекаться.'));
					}
				}
			}
		}

		if (empty($data['Tooth_id']) && !empty($data['Tooth_Code'])) {
			$data['Tooth_id'] = $this->getFirstResultFromQuery("
				select top 1 Tooth_id from v_Tooth with(nolock) where Tooth_Code = :Tooth_Code
			", $data, true);
			if ($data['Tooth_id'] === false) {
				return array(array('Error_Msg' => 'Ошибка при поиске идентификатора зуба.'));
			}
		}

		$data['EvnDiagPLStom_KSKP'] = null;
		if (getRegionNick() == 'perm' && $data['EvnDiagPLStom_IsClosed'] == 2) {
			// вычисляем КСКП
			$data['EvnDiagPLStom_KSKP'] = 1;

			if (!empty($data['Mes_id']) && !empty($data['EvnDiagPLStom_id'])) {
				$data['EvnDiagPLStom_KSKP'] = $this->calcKSKP(array(
					'EvnDiagPLStom_id' => $data['EvnDiagPLStom_id'],
					'Mes_id' => $data['Mes_id']
				));
			}
		}

		if ( empty($data['EvnDiagPLStom_id']) || $data['EvnDiagPLStom_id'] <= 0 ) {
			$procedure = 'p_EvnDiagPLStom_ins';
		}
		else {
			$procedure = 'p_EvnDiagPLStom_upd';
		}
		$fields = "";
		if(getRegionNick() == 'ekb') {
			$fields = "
				@EvnDiagPLStom_IsZNORemove = :EvnDiagPLStom_IsZNORemove,
				@EvnDiagPLStom_BiopsyDate = :EvnDiagPLStom_BiopsyDate,";
		}

		$query = "
			declare
				@CountVizit int,
				@IsTransit bigint,
				@NumGroup bigint,
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @Res = :EvnDiagPLStom_id;

			if ( @Res is not null )
				select top 1
					@CountVizit = EvnDiagPLStom_CountVizit,
					@NumGroup = EvnDiagPLStom_NumGroup
				from v_EvnDiagPLStom with (nolock)
				where EvnDiagPLStom_id = @Res

			exec " . $procedure . "
				@EvnDiagPLStom_id = @Res output,
				@EvnDiagPLStom_pid = :EvnDiagPLStom_pid,
				@EvnDiagPLStom_rid = :EvnDiagPLStom_rid,
				@Lpu_id = :Lpu_id,
				@Server_id = :Server_id,
				@PersonEvn_id = :PersonEvn_id,
				@EvnDiagPLStom_setDT = :EvnDiagPLStom_setDate,
				@EvnDiagPLStom_disDT = :EvnDiagPLStom_disDate,
				@Diag_id = :Diag_id,
				@DiagSetClass_id = 1,
				@DeseaseType_id = :DeseaseType_id,
				@Tooth_id = :Tooth_id,
				@Mes_id = :Mes_id,
				@EvnDiagPLStom_ToothSurface = :EvnDiagPLStom_ToothSurface,
				@EvnDiagPLStom_IsClosed = :EvnDiagPLStom_IsClosed,
				@EvnDiagPLStom_KSKP = :EvnDiagPLStom_KSKP,
				@EvnDiagPLStom_CountVizit = @CountVizit,
				@EvnDiagPLStom_KPU = :EvnDiagPLStom_KPU,
				@EvnDiagPLStom_CarriesTeethCount = :EvnDiagPLStom_CarriesTeethCount,
				@BlackClass_id = :BlackClass_id,
				@EvnDiagPLStom_HalfTooth = :EvnDiagPLStom_HalfTooth,
				@EvnDiagPLStom_IsZNO = :EvnDiagPLStom_IsZNO,
				{$fields}
				@Diag_spid = :Diag_spid,
				@PainIntensity_id = :PainIntensity_id,
				@EvnDiagPLStom_NumGroup = @NumGroup,
				
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as EvnDiagPLStom_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		try {
			$data = $this->beforeSaveEvnDiagPLStom($data);
			$queryParams = array(
				'EvnDiagPLStom_id' => $data['EvnDiagPLStom_id'],
				'EvnDiagPLStom_pid' => $data['EvnDiagPLStom_pid'],
				'EvnDiagPLStom_rid' => empty($data['EvnDiagPLStom_rid']) ? null : $data['EvnDiagPLStom_rid'],
				'Lpu_id' => $data['Lpu_id'],
				'Server_id' => $data['Server_id'],
				'PersonEvn_id' => $data['PersonEvn_id'],
				'EvnDiagPLStom_setDate' => $data['EvnDiagPLStom_setDate'],
				'EvnDiagPLStom_disDate' => $data['EvnDiagPLStom_disDate'],
				'Diag_id' => $data['Diag_id'],
				'DeseaseType_id' => $data['DeseaseType_id'],
				'Tooth_id' => empty($data['Tooth_id']) ? null : $data['Tooth_id'],
				'Mes_id' => empty($data['Mes_id']) ? null : $data['Mes_id'],
				'EvnDiagPLStom_ToothSurface' => empty($data['json']) ? null : $data['json'],
				'EvnDiagPLStom_IsClosed' => $data['EvnDiagPLStom_IsClosed'],
				'EvnDiagPLStom_KSKP' => $data['EvnDiagPLStom_KSKP'],
				'pmUser_id' => $data['pmUser_id'],
				'EvnDiagPLStom_KPU' => isset($data['EvnDiagPLStom_KPU'])?$data['EvnDiagPLStom_KPU']:null,
				'EvnDiagPLStom_CarriesTeethCount' => isset($data['EvnDiagPLStom_CarriesTeethCount'])?$data['EvnDiagPLStom_CarriesTeethCount']:null,
				'BlackClass_id' => !empty($data['BlackClass_id'])?$data['BlackClass_id']:null,
				'EvnDiagPLStom_HalfTooth' => $data['EvnDiagPLStom_HalfTooth'],
				'EvnDiagPLStom_IsZNO' => $data['EvnDiagPLStom_IsZNO'],
				'Diag_spid' => empty($data['Diag_spid']) ? null : $data['Diag_spid'],
				'PainIntensity_id' => empty($data['PainIntensity_id']) ? null : $data['PainIntensity_id'],
				'EvnDiagPLStom_IsZNORemove' => empty($data['EvnDiagPLStom_IsZNORemove']) ? null : $data['EvnDiagPLStom_IsZNORemove'],
				'EvnDiagPLStom_BiopsyDate' => empty($data['EvnDiagPLStom_BiopsyDate']) ? null : $data['EvnDiagPLStom_BiopsyDate']
			);
			$result = $this->db->query($query, $queryParams);
			if ( is_object($result) ) {
				return $this->afterSaveEvnDiagPLStom('EvnDiagPLStom', $result->result('array'), $data);
			} else {
				throw new Exception('Ошибка запроса к БД при сохранении основного диагноза в стоматологии!');
			}
		} catch (Exception $e) {
			return array(array('Error_Msg' => $e->getMessage()));
		}
	}

	/**
	 * yl:проверки при добавлении заболевания в стоматологическом случае
	 * 1) запрет в закрытый случай (закрыт IsFinish=2)
	 * 2) запрет на случай другого врача
	 */
	function checkDiagPLStom($data) {
		return $this->queryResult("
			SELECT
				COALESCE(evn.EvnPLStom_IsFinish, 1) as EvnPLStom_IsFinish,
				vizit.MedStaffFact_id
			FROM
				v_EvnPLStom evn WITH (NOLOCK),
				v_EvnVizitPLStom vizit WITH (NOLOCK)
			WHERE
				evn.EvnPLStom_id=vizit.EvnVizitPLStom_rid
				and
				vizit.EvnVizitPLStom_id = :EvnDiagPLStom_pid
		", $data);
	}
	
	/**
	 * Расчёт КСКП
	 */
	function calcKSKP($data) {
		$KSKP = 1;
		// При закрытии заболевания вычисляться коэффициент КСКП.
		// Коэффициент смотрится по тарифу “2016-05СтоматКСКП” (модуль тарифов и объемов) с учетом выбранной КСГ и количества посещений, связанных с заболеванием.
		// Значение выбираем на дату окончания заболевания.
		// Если значение не найдено, указываем 1.
		if (!empty($data['Mes_id']) && !empty($data['EvnDiagPLStom_id'])) {
			$resp_kskp = $this->queryResult("
				declare @AttributeVision_TablePKey bigint = (select TariffClass_id from v_TariffClass (nolock) where TariffClass_Code = '2016-05СтоматКСКП');

				declare @EvnPLStom_disDate date, @EvnPLStom_id bigint, @EvnDiagPLStom_setDate date, @EvnDiagPLStom_disDate date;

				select top 1
					@EvnPLStom_id = edpls.EvnDiagPLStom_rid,
					@EvnPLStom_disDate = e.Evn_disDate,
					@EvnDiagPLStom_setDate = edpls.EvnDiagPLStom_setDate,
					@EvnDiagPLStom_disDate = edpls.EvnDiagPLStom_disDate
				from v_EvnDiagPLStom edpls with (nolock)
					inner join v_Evn e with (nolock) on e.Evn_id = edpls.EvnDiagPLStom_rid
				where EvnDiagPLStom_id = :EvnDiagPLStom_id
			
				declare @VizitCount int = (
					select
						count(evpls.EvnVizitPLStom_id)
					from
						v_EvnVizitPLStom evpls (nolock)
					where
						evpls.EvnVizitPLStom_pid = @EvnPLStom_id
						and COALESCE(@EvnDiagPLStom_setDate, evpls.EvnVizitPLStom_setDate) <= evpls.EvnVizitPLStom_setDate
						and COALESCE(@EvnDiagPLStom_disDate, evpls.EvnVizitPLStom_setDate) >= evpls.EvnVizitPLStom_setDate 
				);
	
				SELECT
					av.AttributeValue_ValueFloat
				FROM
					v_AttributeVision avis (nolock)
					inner join v_AttributeValue av (nolock) on av.AttributeVision_id = avis.AttributeVision_id
					cross apply(
						select top 1
							av2.AttributeValue_ValueIdent
						from
							v_AttributeValue av2 (nolock)
							inner join v_Attribute a2 (nolock) on a2.Attribute_id = av2.Attribute_id
						where
							av2.AttributeValue_rid = av.AttributeValue_id
							and a2.Attribute_SysNick = 'PlCount'
							and ISNULL(av2.AttributeValue_ValueInt, @VizitCount) = @VizitCount
					) VCFILTER
					cross apply(
						select top 1
							av2.AttributeValue_ValueIdent
						from
							v_AttributeValue av2 (nolock)
							inner join v_Attribute a2 (nolock) on a2.Attribute_id = av2.Attribute_id
							inner join MesOld mo (nolock) on mo.Mes_id = av2.AttributeValue_ValueIdent
							inner join MesOld mo2 (nolock) on mo2.MesType_id = mo.MesType_id and mo.Mes_Code = mo2.Mes_Code and mo.Mes_Name = mo2.Mes_Name -- надо искать по сопадению кода и названия %)
						where
							av2.AttributeValue_rid = av.AttributeValue_id
							and a2.Attribute_SysNick = 'StomatMesOld'
							and mo2.Mes_id = :Mes_id
					) KSGFILTER
				WHERE
					avis.AttributeVision_TableName = 'dbo.TariffClass'
					and avis.AttributeVision_TablePKey = @AttributeVision_TablePKey
					and avis.AttributeVision_IsKeyValue = 2
					and ISNULL(av.AttributeValue_begDate, @EvnPLStom_disDate) <= @EvnPLStom_disDate
					and ISNULL(av.AttributeValue_endDate, @EvnPLStom_disDate) >= @EvnPLStom_disDate
			", array(
				'Mes_id' => $data['Mes_id'],
				'EvnDiagPLStom_id' => $data['EvnDiagPLStom_id']
			));

			if (!empty($resp_kskp[0]['AttributeValue_ValueFloat'])) {
				$KSKP = $resp_kskp[0]['AttributeValue_ValueFloat'];
			}
		}

		return $KSKP;
	}

	/**
	 *	Загрузка списка диагнозов
	 */
	function loadEvnDiagPLStomSopGrid($data) {
		$this->load->helper('MedStaffFactLink');
		$med_personal_list = getMedPersonalListWithLinks();

		$access_type = '
			case
				when EDPLSS.Lpu_id = :Lpu_id then 1
				' . (count($data['session']['linkedLpuIdList']) > 1 ? 'when EDPLSS.Lpu_id in (' . implode(',', $data['session']['linkedLpuIdList']) . ') and ISNULL(EDPLSS.EvnDiagPLStomSop_IsTransit, 1) = 2 then 1' : '') . '
				else 0
			end = 1
		';

		$query = "
			select
				 case when {$access_type} " . ($data['session']['isMedStatUser'] == false && count($med_personal_list)>0 ? "and EVPLS.MedPersonal_id in (".implode(',',$med_personal_list).")" : "") . " then 'edit' else 'view' end as accessType
				,EDPLSS.EvnDiagPLStomSop_id
				,EDPLSS.EvnDiagPLStomSop_pid
				,EDPLSS.Person_id
				,EDPLSS.PersonEvn_id
				,EDPLSS.Server_id
				,RTRIM(DT.DeseaseType_Name) as DeseaseType_Name
				,convert(varchar(10), EDPLSS.EvnDiagPLStomSop_setDate, 104) as EvnDiagPLStomSop_setDate
				,Diag.Diag_id
				,RTrim(Diag.Diag_Code) as Diag_Code
				,RTrim(Diag.Diag_Name) as Diag_Name
			from v_EvnDiagPLStomSop EDPLSS with (nolock)
				left join v_EvnDiagPLStom EDPLS with (nolock) on EDPLS.EvnDiagPLStom_id = EDPLSS.EvnDiagPLStomSop_pid
				inner join v_EvnVizitPLStom EVPLS with (nolock) on EVPLS.EvnVizitPLStom_id = ISNULL(EDPLS.EvnDiagPLStom_pid, EDPLSS.EvnDiagPLStomSop_pid)
				left join v_Diag Diag with (nolock) on Diag.Diag_id = EDPLSS.Diag_id
				left join v_DeseaseType DT with (nolock) on DT.DeseaseType_id = EDPLSS.DeseaseType_id
			where EDPLSS.EvnDiagPLStomSop_pid = :EvnDiagPLStomSop_pid
		";
		$result = $this->db->query($query, array(
			'EvnDiagPLStomSop_pid' => $data['EvnDiagPLStomSop_pid'],
			'Lpu_id' => $data['Lpu_id'],
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *	Получение данных для формы редактирования
	 */
	function loadEvnDiagPLStomSopEditForm($data) {
		$this->load->helper('MedStaffFactLink');
		$med_personal_list = getMedPersonalListWithLinks();

		$access_type = '
			case
				when EDPLSS.Lpu_id = :Lpu_id then 1
				' . (count($data['session']['linkedLpuIdList']) > 1 ? 'when EDPLSS.Lpu_id in (' . implode(',', $data['session']['linkedLpuIdList']) . ') and ISNULL(EDPLSS.EvnDiagPLStomSop_IsTransit, 1) = 2 then 1' : '') . '
				else 0
			end = 1
		';

		$query = "
			select top 1
				 case when {$access_type} " . ($data['session']['isMedStatUser'] == false && count($med_personal_list)>0 ? "and EVPLS.MedPersonal_id in (".implode(',',$med_personal_list).")" : "") . " then 'edit' else 'view' end as accessType
				,EDPLSS.EvnDiagPLStomSop_id
				,EDPLSS.EvnDiagPLStomSop_pid
				,EDPLSS.Person_id
				,EDPLSS.PersonEvn_id
				,EDPLSS.Server_id
				,EDPLSS.Lpu_id
				,EDPLSS.Diag_id
				,EDPLSS.DeseaseType_id
				,convert(varchar(10), EDPLSS.EvnDiagPLStomSop_setDate, 104) as EvnDiagPLStomSop_setDate
				,Tooth.Tooth_Code
				,EDPLSS.Tooth_id
				,EDPLSS.EvnDiagPLStomSop_ToothSurface as json_data
			from
				v_EvnDiagPLStomSop EDPLSS with (nolock)
				left join v_EvnDiagPLStom EDPLS with (nolock) on EDPLS.EvnDiagPLStom_id = EDPLSS.EvnDiagPLStomSop_pid
				inner join v_EvnVizitPLStom EVPLS with (nolock) on EVPLS.EvnVizitPLStom_id = ISNULL(EDPLS.EvnDiagPLStom_pid, EDPLSS.EvnDiagPLStomSop_pid)
				left join v_Tooth Tooth with (nolock) on Tooth.Tooth_id = EDPLSS.Tooth_id
			where
				EDPLSS.EvnDiagPLStomSop_id = :EvnDiagPLStomSop_id
				and (EDPLSS.Lpu_id = :Lpu_id or " . (!empty($data['session']['medpersonal_id']) ? 1 : 0) . " = 1)
		";
		$result = $this->db->query($query, array(
			'EvnDiagPLStomSop_id' => $data['EvnDiagPLStomSop_id'],
			'Lpu_id' => $data['Lpu_id']
		));

		if ( is_object($result) ) {
			$response = $result->result('array');
			if (isset($response[0]) && isset($response[0]['json_data'])) {
				$json_data = json_decode($response[0]['json_data'], true);
				if (isset($json_data['ToothSurfaceTypeIdList']) && is_array($json_data['ToothSurfaceTypeIdList'])) {
					$response[0]['ToothSurfaceType_id_list'] = implode(',', $json_data['ToothSurfaceTypeIdList']);
				}
			}
			return $response;
		}
		else {
			return false;
		}
	}

	/**
	 *	Сохранение сопутствующего диагноза в стоматологии
	 */
	function saveEvnDiagPLStomSop($data)
	{
		if (!empty($data['EvnDiagPLStomSop_id']) && empty($data['ignoreCheckMorbusOnko']) ) {
			$tmp = $this->getFirstResultFromQuery("
				select top 1 MorbusOnkoDiagPLStom_id
				from v_MorbusOnkoDiagPLStom with (nolock)
				where 
					Diag_id != :Diag_id and 
					EvnDiagPLStomSop_id = :EvnDiagPLStomSop_id
			", array(
				'EvnDiagPLStomSop_id' => $data['EvnDiagPLStomSop_id'],
				'Diag_id' => $data['Diag_id']
			));
			if ($tmp > 0) { 
				return array('Error_Code' => '289', 'ignoreParam' => 'ignoreCheckMorbusOnko', 'Error_Msg' => '', 'Alert_Msg' => 'При изменении диагноза данные раздела «Специфика (онкология)», связанные с текущим диагнозом, будут удалены. Продолжить сохранение?');
			}
		}
		
		if ( empty($data['EvnDiagPLStomSop_id']) || $data['EvnDiagPLStomSop_id'] <= 0 ) {
			$procedure = 'p_EvnDiagPLStomSop_ins';
		}
		else {
			$procedure = 'p_EvnDiagPLStomSop_upd';
		}
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @Res = :EvnDiagPLStomSop_id;

			exec " . $procedure . "
				@EvnDiagPLStomSop_id = @Res output,
				@EvnDiagPLStomSop_pid = :EvnDiagPLStomSop_pid,
				@Lpu_id = :Lpu_id,
				@Server_id = :Server_id,
				@PersonEvn_id = :PersonEvn_id,
				@EvnDiagPLStomSop_setDT = :EvnDiagPLStomSop_setDate,
				@Diag_id = :Diag_id,
				@DiagSetClass_id = 3,
				@DeseaseType_id = :DeseaseType_id,
				@Tooth_id = :Tooth_id,
				@EvnDiagPLStomSop_ToothSurface = :EvnDiagPLStomSop_ToothSurface,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as EvnDiagPLStomSop_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		try {
			$data = $this->beforeSaveEvnDiagPLStom($data);
			$queryParams = array(
				'EvnDiagPLStomSop_id' => $data['EvnDiagPLStomSop_id'],
				'EvnDiagPLStomSop_pid' => $data['EvnDiagPLStomSop_pid'],
				'Lpu_id' => $data['Lpu_id'],
				'Server_id' => $data['Server_id'],
				'PersonEvn_id' => $data['PersonEvn_id'],
				'EvnDiagPLStomSop_setDate' => $data['EvnDiagPLStomSop_setDate'],
				'Diag_id' => $data['Diag_id'],
				'DeseaseType_id' => $data['DeseaseType_id'],
				'Tooth_id' => empty($data['Tooth_id']) ? null : $data['Tooth_id'],
				'EvnDiagPLStomSop_ToothSurface' => empty($data['json']) ? null : $data['json'],
				'pmUser_id' => $data['pmUser_id'],
			);
			$result = $this->db->query($query, $queryParams);
			if ( is_object($result) ) {
				$response = $this->afterSaveEvnDiagPLStom('EvnDiagPLStomSop', $result->result('array'), $data);
			} else {
				throw new Exception('Ошибка запроса к БД при сохранении сопутствующего диагноза в стоматологии!');
			}
		} catch (Exception $e) {
			$response = array(array('Error_Msg' => $e->getMessage()));
		}
		return $response;
	}

	/**
	 * Апдейт диагнозов в посещениях (диагноз обязательно должен быть одним из заболеваний).
	 */
	public function updateDiagInEvnVizitPLStom($data) {
		// надо найти посещения где диагноз не соответствует заболеваниям и проапдейтить %)
		$evnPLData = $this->queryResult("
			select
				EvnPLStom_id,
				convert(varchar(10), EvnPLStom_setDate, 104) as EvnPLStom_setDate
			from
				v_EvnPLStom epls (nolock)
				inner join Evn e (nolock) on e.Evn_rid = epls.EvnPLStom_id
			where
				e.Evn_id = :EvnDiagPLStom_id
		", array(
			'EvnDiagPLStom_id' => $data['EvnDiagPLStom_id']
		));

		if (!empty($evnPLData[0]['EvnPLStom_id'])) {
			$is_morbus = (strtotime($evnPLData[0]['EvnPLStom_setDate']) >= getEvnPLStomNewBegDate());

			if ($is_morbus) {
				// берём диагнозы всех заболеваний ТАП
				$diagArray = array();
				$Diag_id = null;
				$DeseaseType_id = null;
				$diags = $this->queryResult("
					select
						Diag_id,
						DeseaseType_id
					from
						v_EvnDiagPLStom (nolock)
					where
						EvnDiagPLStom_rid = :EvnPLStom_id
				", array(
					'EvnPLStom_id' => $evnPLData[0]['EvnPLStom_id']
				));
				foreach ($diags as $diag) {
					$Diag_id = $diag['Diag_id'];
					$DeseaseType_id = $diag['DeseaseType_id'];

					if (!isset($diagArray[$diag['Diag_id']])) {
						$diagArray[$diag['Diag_id']] = array();
					}
					$diagArray[$diag['Diag_id']][] = $diag['DeseaseType_id'];
				}

				// достаём посещения
				$evnvizitpls = $this->queryResult("
					select
						EvnVizitPLStom_id,
						Diag_id,
						DeseaseType_id
					from
						v_EvnVizitPLStom (nolock)
					where
						EvnVizitPLStom_pid = :EvnPLStom_id
				", array(
					'EvnPLStom_id' => $evnPLData[0]['EvnPLStom_id']
				));
				foreach ($evnvizitpls as $evnvizitpl) {
					if (!isset($diagArray[$evnvizitpl['Diag_id']])) {
						// если такого диагноза нет в заболеваниях, то ставим первый попавшийся из заболеваний
						$this->db->query("update EvnVizitPL with (rowlock) set Diag_id = :Diag_id, DeseaseType_id = :DeseaseType_id where EvnVizitPL_id = :EvnVizitPL_id", array(
							'EvnVizitPL_id' => $evnvizitpl['EvnVizitPLStom_id'],
							'Diag_id' => $Diag_id,
							'DeseaseType_id' => $DeseaseType_id
						));
					}
				}
			}
		}
	}

	/**
	 * @param string $evn_class
	 * @param array $result_arr
	 * @return array
	 * @throws Exception
	 */
	public function afterSaveEvnDiagPLStom($evn_class, $result_arr, $data = array())
	{
		if (!empty($result_arr[0]['EvnDiagPLStom_id'])) {
			$this->updateDiagInEvnVizitPLStom(array(
				'EvnDiagPLStom_id' => $result_arr[0]['EvnDiagPLStom_id']
			));
		}
		
		if (!empty($result_arr[0]['EvnDiagPLStomSop_id'])) {
			$data['Person_id'] = $this->getFirstResultFromQuery("SELECT top 1 Person_id FROM v_EvnDiagPLStomSop with (nolock) where EvnDiagPLStomSop_id = ? ", array($result_arr[0]['EvnDiagPLStomSop_id']));
		}

		if (!empty($result_arr[0]['EvnDiagPLStom_id'])) {
			$EvnPLStom_setDate = $this->getFirstResultFromQuery("
				select top 1 convert(varchar(10), EvnPLStom_setDate, 104) as EvnPLStom_setDate
				from v_EvnPLStom epls (nolock)
					inner join Evn e (nolock) on e.Evn_rid = epls.EvnPLStom_id
				where e.Evn_id = :EvnDiagPLStom_id
			", array(
				'EvnDiagPLStom_id' => $result_arr[0]['EvnDiagPLStom_id']
			));
		}
		else {
			$EvnPLStom_setDate = false;
		}

		// @task https://redmine.swan.perm.ru/issues/136169
		// Группировка заболеваний
		// Проверки
		if (
			$this->regionNick == 'penza' && $evn_class == 'EvnDiagPLStom' && $EvnPLStom_setDate !== false
			&& strtotime($EvnPLStom_setDate) < strtotime('01.06.2019')
		) {
			if ( !empty($result_arr[0]['EvnDiagPLStom_id']) ) {
				$data['EvnPLStom_id'] = $this->getFirstResultFromQuery(
					"select top 1 Evn_rid from v_Evn with (nolock) where Evn_id = :EvnDiagPLStom_id",
					array(
						'EvnDiagPLStom_id' => $result_arr[0]['EvnDiagPLStom_id']
					)
				);
				$data['EvnDiagPLStom_id'] = $result_arr[0]['EvnDiagPLStom_id'];
			}

			if ( !empty($data['EvnPLStom_id']) && $data['EvnPLStom_id'] !== false ) {
				// Чистим номера групп
				$resp = $this->getFirstRowFromQuery("
					declare
						@ErrCode int,
						@ErrMessage varchar(4000);

					set nocount on;

					begin try
						update
							edpls with (ROWLOCK)
						set
							EvnDiagPLStom_NumGroup = null
						from EvnDiagPLStom edpls
							inner join v_Evn e on e.Evn_id = edpls.EvnDiagPLStom_id
						where
							e.Evn_rid = :EvnPLStom_id
					end try

					begin catch
						set @ErrCode = error_number()
						set @ErrMessage = error_message()
					end catch

					set nocount off;

					select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				", array(
					'EvnPLStom_id' => $data['EvnPLStom_id'],
				));

				if ( !empty($resp['Error_Msg']) ) {
					throw new Exception('Группировка заболеваний: ' . $resp['Error_Msg']);
				}

				// Выбираем данные по услугам, заболеваниям и посещениям в рамках стомат. ТАП
				$dataSet = $this->getEvnDiagPLStomData($data['EvnPLStom_id']);

				if ( !is_array($dataSet) ) {
					throw new Exception('Группировка заболеваний: ошибка при получении данных по услугам, заболеваниям и посещениям');
				}

				// При сохранении заболевания, если добавлены услуги по профилактике (услуги со значением атрибута «Вид услуги»=«02») и не установлен флаг
				// «Заболевание закрыто», то открывается сообщение: «Услуги по профилактике должны быть выполнены одним специалистом в рамках одного дня, поэтому
				// заболевание должно быть закрыто. Для этого заполните поле «Заболевание закрыто»». Кнопка ОК. При нажатии на кнопку сообщение закрывается,
				// сохранение заболевания не выполняется.
				if ( array_key_exists('EvnDiagPLStom_IsClosed', $data) && $data['EvnDiagPLStom_IsClosed'] != 2 && !empty($data['EvnDiagPLStom_id']) && strtotime($data['EvnDiagPLStom_setDate']) >= strtotime('2018-08-01') ) {
					foreach ( $dataSet as $key => $row ) {
						if ( $row['UslugaComplexAttribute_Value'] == '02' && $row['EvnDiagPLStom_id'] == $data['EvnDiagPLStom_id'] ) {
							throw new Exception('Услуги по профилактике должны быть выполнены одним специалистом в рамках одного дня, поэтому заболевание должно быть закрыто. Для этого заполните поле «Заболевание закрыто»');
						}
					}
				}

				$groupList = array();
				$i = 0;

				// 1. В первую очередь группируются заболевания:
				// - которые содержат только услуги по заболеванию. Услуги по заболеванию – услуги:
				//   - со значением атрибута «Вид услуги» = «01»;
				//   - со значением атрибута «Вид услуги» = «03», если значение в поле «Вид обращения», из которого добавлена услуга,
				//     отлично от «На дому: НМП» или  «Поликлиника: НМП» (см. правило определения значения атрибута «Вид услуги», описанное в ТЗ
				//     выполнение стоматологической услуги);
				// - в которых диагнозы относятся к одному классу МКБ-10.
				$group01 = array();

				// 2. Далее группируются заболевания:
				// - которые содержат только услуги по профилактике. Услуги по профилактике – услуги со значением атрибута «Вид услуги» = «02»;
				// - где в посещениях, из которых они созданы, указан один и тот же врач и даты посещений равны.
				$group02 = array();

				// 3. Далее группируются заболевания:
				// - которые содержат только услуги по неотложной помощи. Услуги по неотложной помощи – услуги со значением атрибута «Вид услуги» = «03»,
				//   при этом значение в поле «Вид обращения», из которого добавлена услуга «На дому: НМП» или  «Поликлиника: НМП» (см. правило определения
				//   значения атрибута «Вид услуги», описанное в ТЗ выполнение стоматологической услуги);
				// - где даты посещений, из которых они созданы, равны.
				$group03 = array();

				foreach ( $dataSet as $key => $row ) {
					if ( !empty($row['EvnDiagPLStom_NumGroup']) || $row['EvnDiagPLStom_IsClosed'] != 2 ) {
						continue;
					}

					if (
						$row['UslugaComplexAttribute_Value'] == '01'
						|| (
							$row['UslugaComplexAttribute_Value'] == '03'
							&& $row['ServiceType_SysNick'] != 'neotl'
							&& $row['ServiceType_SysNick'] != 'polnmp'
						)
					) {
						if ( !isset($group01[$row['Diag_pid']]) ) {
							$i++;
							$group01[$row['Diag_pid']] = $i;
						}

						$dataSet[$key]['EvnDiagPLStom_NumGroup'] = $group01[$row['Diag_pid']];
					}
					else if ( $row['UslugaComplexAttribute_Value'] == '02' ) {
						if ( !isset($group02[$row['EvnVizitPLStom_setDate'] . '_' . $row['MedStaffFact_id']]) ) {
							$i++;
							$group02[$row['EvnVizitPLStom_setDate'] . '_' . $row['MedStaffFact_id']] = $i;
						}

						$dataSet[$key]['EvnDiagPLStom_NumGroup'] = $group02[$row['EvnVizitPLStom_setDate'] . '_' . $row['MedStaffFact_id']];
					}
					else if (
						$row['UslugaComplexAttribute_Value'] == '03'
						&& ($row['ServiceType_SysNick'] == 'neotl' || $row['ServiceType_SysNick'] == 'polnmp')
					) {
						if ( !isset($group03[$row['EvnVizitPLStom_setDate']]) ) {
							$i++;
							$group03[$row['EvnVizitPLStom_setDate']] = $i;
						}

						$dataSet[$key]['EvnDiagPLStom_NumGroup'] = $group03[$row['EvnVizitPLStom_setDate']];
					}
				}

				// Нумеруем группы для всех оставшихся заболеваний
				foreach ( $dataSet as $key => $row ) {
					if ( !empty($row['EvnDiagPLStom_NumGroup']) || $row['EvnDiagPLStom_IsClosed'] != 2 ) {
						continue;
					}

					$i++;
					$dataSet[$key]['EvnDiagPLStom_NumGroup'] = $i;
				}

				$groupList = array();

				// Группируем заболевания
				foreach ( $dataSet as $row ) {
					if ( !isset($groupList[$row['EvnDiagPLStom_NumGroup']]) ) {
						$groupList[$row['EvnDiagPLStom_NumGroup']] = array();
					}

					if ( !in_array($row['EvnDiagPLStom_id'], $groupList[$row['EvnDiagPLStom_NumGroup']]) ) {
						$groupList[$row['EvnDiagPLStom_NumGroup']][] = $row['EvnDiagPLStom_id'];
					}
				}

				// Обновляем номера групп в БД
				foreach ( $groupList as $key => $diagList ) {
					$resp = $this->getFirstRowFromQuery("
						declare
							@ErrCode int,
							@ErrMessage varchar(4000);

						set nocount on;

						begin try
							update
								EvnDiagPLStom with (ROWLOCK)
							set
								EvnDiagPLStom_NumGroup = :EvnDiagPLStom_NumGroup
							where
								EvnDiagPLStom_id in (" . implode(",", $diagList) . ")
						end try

						begin catch
							set @ErrCode = error_number()
							set @ErrMessage = error_message()
						end catch

						set nocount off;

						select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
					", array(
						'EvnDiagPLStom_NumGroup' => $key,
					));

					if ( !empty($resp['Error_Msg']) ) {
						throw new Exception('Группировка заболеваний: ' . $resp['Error_Msg']);
					}
				}
			}
		}
		
		// чистим лишние специфики
		// это надо куда-то вынести, пока пусть тут побудет
		if ( !empty($result_arr[0]['EvnDiagPLStom_id']) ) {
			$this->load->model('MorbusOnkoSpecTreat_model', 'MorbusOnkoSpecTreat');
			$MorbusOnkoDiagPLStom_id = $this->getFirstResultFromQuery("
				select top 1 MorbusOnkoDiagPLStom_id
				from v_MorbusOnkoDiagPLStom with (nolock)
				where 
					EvnDiagPLStom_id = :Evn_id and 
					Diag_id != :Diag_id and 
					EvnDiagPLStomSop_id is null
			", array(
				'Evn_id' => $result_arr[0]['EvnDiagPLStom_id'],
				'Diag_id' => $data['Diag_id']
			));
			if(!empty($MorbusOnkoDiagPLStom_id)) {
				// Методы подтверждения диагноза удаляем
				$mol_list = $this->queryList("select MorbusOnkoLink_id from MorbusOnkoLink (nolock) where MorbusOnkoDiagPLStom_id = :MorbusOnkoDiagPLStom_id", 
					array('MorbusOnkoDiagPLStom_id' => $MorbusOnkoDiagPLStom_id)
				);
				foreach($mol_list as $ml) {
					$this->execCommonSP('dbo.p_MorbusOnkoLink_del', array('MorbusOnkoLink_id' => $ml, 'pmUser_id' => $data['pmUser_id']));
				}
				// Спецлечение удаляем
				$mol_list = $this->queryList("select MorbusOnkoSpecTreat_id from v_MorbusOnkoSpecTreat (nolock) where MorbusOnkoDiagPLStom_id = :MorbusOnkoDiagPLStom_id",
					array('MorbusOnkoDiagPLStom_id' => $MorbusOnkoDiagPLStom_id)
				);
				foreach($mol_list as $ml) {
					$this->MorbusOnkoSpecTreat->deleteMorbusOnkoSpecTreat(array('MorbusOnkoSpecTreat_id' => $ml));
				}
				// Привязку консультаций зануляем, они останутся на MorbusOnko_id
				$this->db->query("update OnkoConsult with (rowlock) set MorbusOnkoDiagPLStom_id = null where MorbusOnkoDiagPLStom_id = :MorbusOnkoDiagPLStom_id", 
					array('MorbusOnkoDiagPLStom_id' => $MorbusOnkoDiagPLStom_id)
				);
				// И удаляем саму специфику
				$delParams = array(
					'MorbusOnkoDiagPLStom_id' => $MorbusOnkoDiagPLStom_id,
					'pmUser_id' => $data['pmUser_id']
				)
				;
				$res = $this->execCommonSP('dbo.p_MorbusOnkoDiagPLStom_del', $delParams);
			}
		}
		
		if ( !empty($result_arr[0]['EvnDiagPLStomSop_id']) ) {
			$MorbusOnkoDiagPLStom_id = $this->getFirstResultFromQuery("
				select top 1 MorbusOnkoDiagPLStom_id
				from v_MorbusOnkoDiagPLStom with (nolock)
				where 
					EvnDiagPLStomSop_id = :Evn_id and 
					Diag_id != :Diag_id
			", array(
				'Evn_id' => $result_arr[0]['EvnDiagPLStomSop_id'],
				'Diag_id' => $data['Diag_id']
			));
			if(!empty($MorbusOnkoDiagPLStom_id)) {
				// Методы подтверждения диагноза удаляем
				$mol_list = $this->queryList("select MorbusOnkoLink_id from MorbusOnkoLink (nolock) where MorbusOnkoDiagPLStom_id = :MorbusOnkoDiagPLStom_id", 
					array('MorbusOnkoDiagPLStom_id' => $MorbusOnkoDiagPLStom_id)
				);
				foreach($mol_list as $ml) {
					$this->execCommonSP('dbo.p_MorbusOnkoLink_del', array('MorbusOnkoLink_id' => $ml, 'pmUser_id' => $data['pmUser_id']));
				}
				// Спецлечение удаляем
				$mol_list = $this->queryList("select MorbusOnkoSpecTreat_id from v_MorbusOnkoSpecTreat (nolock) where MorbusOnkoDiagPLStom_id = :MorbusOnkoDiagPLStom_id",
					array('MorbusOnkoDiagPLStom_id' => $MorbusOnkoDiagPLStom_id)
				);
				foreach($mol_list as $ml) {
					$this->MorbusOnkoSpecTreat->deleteMorbusOnkoSpecTreat(array('MorbusOnkoSpecTreat_id' => $ml));
				}
				// Привязку консультаций зануляем, они останутся на MorbusOnko_id
				$this->db->query("update OnkoConsult with (rowlock) set MorbusOnkoDiagPLStom_id = null where MorbusOnkoDiagPLStom_id = :MorbusOnkoDiagPLStom_id", 
					array('MorbusOnkoDiagPLStom_id' => $MorbusOnkoDiagPLStom_id)
				);
				// И удаляем саму специфику
				$delParams = array('MorbusOnkoDiagPLStom_id' => $MorbusOnkoDiagPLStom_id);
				$res = $this->execCommonSP('dbo.p_MorbusOnkoDiagPLStom_del', $delParams);
			}
		}
		
		
		// чистим лишние заболевания
		if ( !empty($data['Person_id']) ) {
			$this->load->model('MorbusOnkoSpecifics_model');
			$this->MorbusOnkoSpecifics_model->clearMorbusOnkoSpecifics($data);
		}
			
		// группируем посещения
		if ( !empty($result_arr[0]['EvnDiagPLStomSop_id']) && !empty($data['EvnDiagPLStomSop_rid'])) {
			$this->load->model('EvnVizitPLStom_model');
			$this->EvnVizitPLStom_model->updateEvnVizitNumGroup($data['EvnDiagPLStomSop_rid']);
		}

		return $result_arr;
	}

	/**
	 * Получение данных об услугах, заболеваниях и посещениях стомат. ТАП
	 */
	public function getEvnDiagPLStomData($EvnPLStom_id, $forceLoad = false) {
		if ( !is_array($this->_evnDiagPLStomData) || count($this->_evnDiagPLStomData) == 0 || $forceLoad === true ) {
			$this->_evnDiagPLStomData = $this->queryResult("
				declare @rid bigint = :EvnPLStom_id;

				select
					eus.EvnUslugaStom_rid as EvnPLStom_id,
					eus.EvnUslugaStom_pid as EvnVizitPLStom_id,
					edpls.EvnDiagPLStom_id,
					edpls.Diag_id,
					d.Diag_pid,
					pt.PayType_SysNick,
					st.ServiceType_SysNick,
					uca.UslugaComplexAttribute_Value,
					convert(varchar(10), evpls.EvnVizitPLStom_setDT, 104) as EvnVizitPLStom_setDate,
					convert(varchar(10), edpls.EvnDiagPLStom_setDT, 104) as EvnDiagPLStom_setDate,
					convert(varchar(10), eus.EvnUslugaStom_setDT, 104) as EvnUslugaStom_setDate,
					evpls.MedStaffFact_id,
					edpls.EvnDiagPLStom_NumGroup,
					edpls.EvnDiagPLStom_IsClosed,
					edpls.EvnDiagPLStom_IsZNO,
					edpls.Diag_spid,
					edpls.PainIntensity_id
				from v_EvnUslugaStom eus with (nolock)
					inner join v_EvnDiagPLStom edpls with (nolock) on edpls.EvnDiagPLStom_id = eus.EvnDiagPLStom_id
					inner join v_EvnVizitPLStom evpls with (nolock) on evpls.EvnVizitPLStom_id = eus.EvnUslugaStom_pid
					inner join v_ServiceType st with (nolock) on st.ServiceType_id = evpls.ServiceType_id
					inner join v_PayType pt with (nolock) on pt.PayType_id = evpls.PayType_id
					inner join v_Diag d with (nolock) on d.Diag_id = edpls.Diag_id
					outer apply (
						select top 1 t1.UslugaComplexAttribute_Value
						from v_UslugaComplexAttribute t1 with (nolock)
							inner join v_UslugaComplexAttributeType t2 with (nolock) on t2.UslugaComplexAttributeType_id = t1.UslugaComplexAttributeType_id
						where t1.UslugaComplex_id = eus.UslugaComplex_id
							and t2.UslugaComplexAttributeType_SysNick = 'uslugatype'
							and t1.UslugaComplexAttribute_Value in ('01', '02', '03')
					) uca
				where eus.EvnUslugaStom_rid = @rid
					and uca.UslugaComplexAttribute_Value is not null
					and pt.PayType_SysNick = 'oms'
			", array(
				'EvnPLStom_id' => $EvnPLStom_id
			));
		}

		return $this->_evnDiagPLStomData;
	}

	/**
	 * Проверки зуба и обработка ToothSurfaceType_id_list
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public function beforeSaveEvnDiagPLStom($data)
	{
		if ( empty($data['Tooth_id']) ) {
			$data['json'] = null;
			return $data;
		}
		if ( empty($data['Tooth_Code']) ) {
			throw new Exception('Не указан номер зуба!');
		}
		$allowedToothCodes = array(
			// Постоянные
			11, 12, 13, 14, 15, 16, 17, 18,
			21, 22, 23, 24, 25, 26, 27, 28,
			31, 32, 33, 34, 35, 36, 37, 38,
			41, 42, 43, 44, 45, 46, 47, 48,
			// Молочные
			51, 52, 53, 54, 55,
			61, 62, 63, 64, 65,
			71, 72, 73, 74, 75,
			81, 82, 83, 84, 85
		);
		if ( !in_array($data['Tooth_Code'], $allowedToothCodes) ) {
			throw new Exception('Значение поля "Зуб" должно быть из диапазонов 11-18, 21-28, 31-38, 41-48, 51-55, 61-65, 71-75, 81-85');
		}
		$allowedToothSurfaceType = array(
			// Резцы, клыки
			'1','2','3','4',
		);
		$num = substr($data['Tooth_Code'], 1, 1);
		if ($num > 3) {
			$allowedToothSurfaceType[] = '5';
		}
		$ToothSurfaceType_arr = array();
		if ( !empty($data['ToothSurfaceType_id_list']) ) {
			$tmp = explode(',', $data['ToothSurfaceType_id_list']);
			foreach ($tmp as $value) {
				if (in_array($value, $allowedToothSurfaceType)) {
					$ToothSurfaceType_arr[] = $value;
				}
			}
			if ( empty($ToothSurfaceType_arr) ) {
				throw new Exception('Неправильный формат списка поверхностей зуба!');
			}
		}
		unset($data['ToothSurfaceType_id_list']);
		$data['json'] =json_encode(array(
			'ToothSurfaceTypeIdList'=>$ToothSurfaceType_arr,
		));
		return $data;
	}

	/**
	 * @param array $ToothSurfaceTypeIdList
	 * @return array
	 * @throws Exception
	 */
	private function loadToothSurfaceType($ToothSurfaceTypeIdList)
	{
		$query = "
			select
				ToothSurfaceType_Name
			from
				v_ToothSurfaceType with (nolock)
			where
				ToothSurfaceType_id in (" . implode(',', $ToothSurfaceTypeIdList) . ")
		";
		$result = $this->db->query($query);
		if ( !is_object($result) ) {
			throw new Exception('Ошибка при выполнении запроса к базе данных');
		}
		return $result->result('array');
	}

	/**
	 * @param string $json_data
	 * @param bool $withToothSurfaceTypeName
	 * @return array
	 * @throws Exception
	 */
	public function processingToothSurface($json_data, $withToothSurfaceTypeName = false)
	{
		$response = array(
			'ToothSurfaceTypeIdList'=>array(),
			'ToothSurfaceTypeNameList'=>array(),
		);
		if (empty($json_data)) {
			return $response;
		}
		if (false === strpos($json_data, 'ToothSurfaceTypeIdList')) {
			$json_data = explode(',', $json_data);
			$isOk = true;
			foreach($json_data as $id) {
				if (false == is_numeric($id)) {
					$isOk = false;
				}
			}
			if ($isOk) {
				$response['ToothSurfaceTypeIdList'] = $json_data;
			}
		} else {
			$json_data = json_decode($json_data, true);
			if (isset($json_data['ToothSurfaceTypeIdList']) && is_array($json_data['ToothSurfaceTypeIdList'])) {
				$response['ToothSurfaceTypeIdList'] = $json_data['ToothSurfaceTypeIdList'];
			}
		}
		if ($withToothSurfaceTypeName && count($response['ToothSurfaceTypeIdList']) > 0) {
			$rows = $this->loadToothSurfaceType($response['ToothSurfaceTypeIdList']);
			foreach ($rows as $row) {
				$response['ToothSurfaceTypeNameList'][] = $row['ToothSurfaceType_Name'];
			}
		}
		return $response;
	}

	/**
	 * Проверка возможности удаления стомат. заболевания
	 * Условие: не должно быть услуг в рамках удаляемого заболевания
	 */
	function checkEvnUslugaStomCount($data, $action = 'delete') {
		if ( empty($data['EvnDiagPLStom_id']) ) {
			return 'Не указан идентификатор удаляемого события';
		}

		$query = "
			select top 1 EvnUslugaStom_id
			from v_EvnUslugaStom with (nolock)
			where EvnDiagPLStom_id = :EvnDiagPLStom_id
		";
		$result = $this->db->query($query, $data);

		if ( !is_object($result) ) {
			return 'Ошибка при выполнении запроса к базе данных';
		}

		$response = $result->result('array');

		if ( is_array($response) && count($response) > 0 && !empty($response[0]['EvnUslugaStom_id']) ) {
			return 'В рамках заболевания добавлены услуги, ' . ($action == 'edit' ? 'редактирование' : 'удаление') . ' невозможно';
		}

		return '';
	}

	/**
	 *  Получение списка заболеваний для панели направлений в ЭМК
	 */
	function loadEvnDiagPLStomPanel($data)
	{
		$this->load->helper('MedStaffFactLink');
		$med_personal_list = getMedPersonalListWithLinks();

		$resp = $this->queryResult("
			select
				case when
					EDPLS.Lpu_id = :Lpu_id
					" . ($data['session']['isMedStatUser'] == false && count($med_personal_list) > 0 ? "and exists (select top 1 EvnVizitPLStom_id from v_EvnVizitPLStom with (nolock) where EvnVizitPLStom_rid = EDPLS.EvnDiagPLStom_rid and MedPersonal_id in (" . implode(',', $med_personal_list) . "))" : "") . "
				then
					'edit'
				else
					'view'
				end as accessType,
				edpls.EvnDiagPLStom_id,
				convert(varchar(10), edpls.EvnDiagPLStom_setDT, 104) as EvnDiagPLStom_setDate,
				edpls.EvnDiagPLStom_IsClosed,
				d.Diag_Code,
				d.Diag_Name,
				edpls.PersonEvn_id,
				edpls.Person_id,
				edpls.Server_id,
				Tooth.Tooth_Code,
				mes.Mes_Name
			from
				v_EvnDiagPLStom edpls (nolock)
				left join v_Diag d (nolock) on d.Diag_id = edpls.Diag_id
				left join v_Tooth Tooth with (nolock) on Tooth.Tooth_id = EDPLS.Tooth_id
				left join v_MesOld mes with (nolock) on mes.Mes_id = EDPLS.Mes_id
			where
				edpls.EvnDiagPLStom_rid = (select top 1 Evn_rid from v_Evn with (nolock) where Evn_id = :EvnDiagPLStom_pid)
		", $data);

		$this->load->library('swFilterResponse');
		return swFilterResponse::filterNotViewDiag($resp, $data);
	}

	/**
	 * @throws Exception
	 */
	protected function _checkMorbusOnkoDiagPLStom($data) {
		if ( $this->regionNick == 'kz' || empty($data['Diag_id']) ) {
			return true;
		}

		// если движение не сохранялось, значит, и специфики точно нет, проверяем только диагноз
		if ( empty($data['EvnDiagPLStom_id']) && !empty($data['EvnDiagPLStom_IsClosed']) && $data['EvnDiagPLStom_IsClosed'] == 2 && !(getRegionNick() == 'krym' && $data['EvnDiagPLStom_IsZNO'] == 2)) {
			$mo_chk = $this->getFirstResultFromQuery("
				select top 1 Diag.Diag_id
				from v_Diag Diag (nolock)
				where Diag.Diag_id = :Diag_id
					and (
						(Diag.Diag_Code >= 'C00' AND Diag.Diag_Code <= 'C97')
						or (Diag.Diag_Code >= 'D00' AND Diag.Diag_Code <= 'D09')
					)
			", array('Diag_id' => $data['Diag_id']));

			if ( !(getRegionNick() == 'krym' && $data['EvnDiagPLStom_IsZNO'] == 2) && !empty($mo_chk) ) {
				return array('Error_Code' => __LINE__, 'Error_Msg' => 'В случае лечения установлен диагноз из диапазона С00-C97 или D00-D09. Заполните раздел "Специфика (онкология)" или проверьте корректность заполнения обязательных полей данного раздела: «Повод обращения», «Стадия опухолевого процесса», «Т», «N», «M» (Стадия опухолевого процесса по системе TNM) в блоках ФОМС и Канцер регистр. Обязательные поля раздела отмечены символом *.');
			}
		}

		if ( !empty($data['EvnDiagPLStom_id']) && !empty($data['EvnDiagPLStom_IsClosed']) && $data['EvnDiagPLStom_IsClosed'] == 2 ) {
			if ( $this->regionNick == 'kareliya' ) {
				$OnkoConsultField = 'OC.OnkoConsult_id';
				$OnkoConsultJoin = "
					outer apply (
						select top 1 OnkoConsult_id
						from v_OnkoConsult with (nolock)
						where MorbusOnkoDiagPLStom_id = modps.MorbusOnkoDiagPLStom_id
					) OC
				";
			}
			else {
				$OnkoConsultField = 'null as OnkoConsult_id';
				$OnkoConsultJoin = "";
			}

			$query = "
				select top 1
					edpls.EvnDiagPLStom_id,
					ISNULL(edpls.EvnDiagPLStom_disDate, edpls.EvnDiagPLStom_setDate) as filterDate,
					Diag.Diag_id,
					modps.*,
					convert(varchar(10), modps.MorbusOnkoDiagPLStom_takeDT, 104) as MorbusOnko_takeDT,
					OT.OnkoTreatment_id,
					OT.OnkoTreatment_Code,
					dbo.Age2(PS.Person_Birthday, ISNULL(edpls.EvnDiagPLStom_disDate, edpls.EvnDiagPLStom_setDate)) as Person_Age,
					MorbusOnkoLink.MorbusOnkoLink_id,
					{$OnkoConsultField}
				from 
					v_EvnDiagPLStom edpls (nolock)
					inner join v_Diag Diag (nolock) on Diag.Diag_id = isnull(:Diag_id, edpls.Diag_id)
					inner join v_Person_all PS with (nolock) on PS.PersonEvn_id = edpls.PersonEvn_id and PS.Server_id = edpls.Server_id
					left join v_MorbusOnkoDiagPLStom modps (nolock) on modps.EvnDiagPLStom_id = edpls.EvnDiagPLStom_id
					left join v_OnkoTreatment OT with (nolock) on OT.OnkoTreatment_id = modps.OnkoTreatment_id
					outer apply(
							SELECT top 1
								MorbusOnkoLink_id
							FROM
								v_MorbusOnkoLink WITH (nolock)
							WHERE
								MorbusOnkoVizitPLDop_id = modps.MorbusOnkoDiagPLStom_id
					) as MorbusOnkoLink
					{$OnkoConsultJoin}
				where 
					edpls.EvnDiagPLStom_id = :EvnDiagPLStom_id
					and ((Diag.Diag_Code >= 'C00' AND Diag.Diag_Code <= 'C97') or (Diag.Diag_Code >= 'D00' AND Diag.Diag_Code <= 'D09'))
			";
			$mo_chk = $this->getFirstRowFromQuery($query, array(
				'EvnDiagPLStom_id' => $data['EvnDiagPLStom_id'],
				'Diag_id' => $data['Diag_id'],
			));

			if (!empty($mo_chk)) {
				if ( $this->regionNick == 'kareliya' && empty($mo_chk['OnkoConsult_id']) ) {
					return array('Error_Code' => __LINE__, 'Error_Msg' => 'В специфике по онкологии заполните раздел "Сведения о проведении консилиума".');
				}

				if (
					$this->regionNick == 'ufa' && !empty($mo_chk['OnkoTreatment_id']) && ($mo_chk['OnkoTreatment_Code'] == 1 || $mo_chk['OnkoTreatment_Code'] == 2)
					&& empty($mo_chk['MorbusOnkoDiagPLStom_IsTumorDepoUnknown']) && empty($mo_chk['MorbusOnkoDiagPLStom_IsTumorDepoLympha'])
					&& empty($mo_chk['MorbusOnkoDiagPLStom_IsTumorDepoBones']) && empty($mo_chk['MorbusOnkoDiagPLStom_IsTumorDepoLiver'])
					&& empty($mo_chk['MorbusOnkoDiagPLStom_IsTumorDepoLungs']) && empty($mo_chk['MorbusOnkoDiagPLStom_IsTumorDepoBrain'])
					&& empty($mo_chk['MorbusOnkoDiagPLStom_IsTumorDepoSkin']) && empty($mo_chk['MorbusOnkoDiagPLStom_IsTumorDepoKidney'])
					&& empty($mo_chk['MorbusOnkoDiagPLStom_IsTumorDepoOvary']) && empty($mo_chk['MorbusOnkoDiagPLStom_IsTumorDepoPerito'])
					&& empty($mo_chk['MorbusOnkoDiagPLStom_IsTumorDepoMarrow']) && empty($mo_chk['MorbusOnkoDiagPLStom_IsTumorDepoOther'])
					&& empty($mo_chk['MorbusOnkoDiagPLStom_IsTumorDepoMulti'])
				) {
					return array('Error_Code' => __LINE__, 'Error_Msg' => 'В специфике по онкологии необходимо заполнить раздел "Локализация отдаленных метастазов", обязательный при поводе обращения "1. Лечение при рецидиве" или "2. Лечение при прогрессировании".');
				}

				//если стоит чекбокс подозрение на ЗНО, то не проверяем заполнение полей спецфики (регион Крым)
				if ( !(getRegionNick() == 'krym' && $data['EvnDiagPLStom_IsZNO'] == 2) ) {
					if (
						empty($mo_chk['OnkoTreatment_id'])
						/*#192967
						|| (
							empty($mo_chk['MorbusOnkoLink_id']) && empty($mo_chk['HistologicReasonType_id'])
						)*/
						|| (
							empty($mo_chk['TumorStage_fid']) && !empty($mo_chk['OnkoTreatment_id']) && $mo_chk['OnkoTreatment_Code'] != 5 && $mo_chk['OnkoTreatment_Code'] != 6
						)
						|| (
							empty($mo_chk['TumorStage_id'])
						)
					) {
						return array('Error_Code' => __LINE__, 'Error_Msg' => 'В случае лечения установлен диагноз из диапазона С00-C97 или D00-D09. Заполните раздел "Специфика (онкология)" или проверьте корректность заполнения обязательных полей данного раздела: «Повод обращения», «Стадия опухолевого процесса», «Т», «N», «M» (Стадия опухолевого процесса по системе TNM) в блоках ФОМС и Канцер регистр. Обязательные поля раздела отмечены символом *.');
					}

					$onkoFields = array('OnkoT', 'OnkoN', 'OnkoM');
					foreach ( $onkoFields as $field ) {
						if ( empty($mo_chk[$field . '_id']) ) {
							throw new Exception('В случае лечения установлен диагноз из диапазона С00-C97 или D00-D09. Заполните раздел "Специфика (онкология)" или проверьте корректность заполнения обязательных полей данного раздела: «Повод обращения», «Стадия опухолевого процесса», «Т», «N», «M» (Стадия опухолевого процесса по системе TNM) в блоках ФОМС и Канцер регистр. Обязательные поля раздела отмечены символом *.');
						}
					}

					$onkoFields = array();

					if ( $mo_chk['OnkoTreatment_Code'] === 0 && $mo_chk['Person_Age'] >= 18 ) {
						$onkoFields[] = 'OnkoT';
						$onkoFields[] = 'OnkoN';
						$onkoFields[] = 'OnkoM';
					}

					foreach ( $onkoFields as $field ) {
						if ( !empty($mo_chk[$field . '_fid']) ) {
							continue;
						}

						$param1 = false; // Есть связка с диагнозом и OnkoT_id is not null
						$param2 = false; // Есть связка с диагнозом и OnkoT_id is null
						$param3 = false; // Нет связки с диагнозом и есть записи с Diag_id is null

						$LinkData = $this->queryResult("
							select Diag_id, {$field}_fid, {$field}Link_begDate, {$field}Link_endDate from dbo.v_{$field}Link with (nolock) where Diag_id = :Diag_id
							union all
							select Diag_id, {$field}_fid, {$field}Link_begDate, {$field}Link_endDate from dbo.v_{$field}Link with (nolock) where Diag_id is null
						", array('Diag_id' => $mo_chk['Diag_id']));

						if ( $LinkData !== false ) {
							foreach ( $LinkData as $row ) {
								if (
									(empty($row[$field . 'Link_begDate']) || $row[$field . 'Link_begDate'] <= $mo_chk['filterDate'])
									&& (empty($row[$field . 'Link_endDate']) || $row[$field . 'Link_endDate'] >= $mo_chk['filterDate'])
								) {
									if ( !empty($row['Diag_id']) && $row['Diag_id'] == $mo_chk['Diag_id'] ) {
										if ( !empty($row[$field . '_fid']) ) {
											$param1 = true;
										}
										else {
											$param2 = true;
										}
									}
									else if ( empty($row['Diag_id']) ) {
										$param3 = true;
									}
								}
							}
						}

						if ( $param1 == true || ($param3 == true && $param2 == false) ) {
							return array('Error_Code' => __LINE__, 'Error_Msg' => 'В случае лечения установлен диагноз из диапазона С00-C97 или D00-D09. Заполните раздел "Специфика (онкология)" или проверьте корректность заполнения обязательных полей данного раздела: «Повод обращения», «Стадия опухолевого процесса», «Т», «N», «M» (Стадия опухолевого процесса по системе TNM) в блоках ФОМС и Канцер регистр. Обязательные поля раздела отмечены символом *.');
						}
					}
				}
			}

			if ( empty($data['ignoreMorbusOnkoDrugCheck']) ) {
				$rslt = $this->getFirstResultFromQuery("
					select top 1 MorbusOnkoDrug_id
					from v_MorbusOnkoDrug with (nolock)
					where Evn_id = :EvnDiagPLStom_id
				", array('EvnDiagPLStom_id' => $data['EvnDiagPLStom_id']), true);

				if ( !empty($rslt) ) {
					return array('Error_Code' => 106, 'ignoreParam' => 'ignoreMorbusOnkoDrugCheck', 'Error_Msg' => '', 'Alert_Msg' => 'В разделе «Данные о препаратах» остались препараты, не связанные с лечением. Продолжить сохранение?');
				}
			}
		}

		return true;
	}
}
