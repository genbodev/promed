<?php
require_once('Collection_model.php');
/**
 * BactAntibiotic_model
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2010 Swan Ltd.
 * @author       Qusijue
 * @version      Сентябрь 2019
*/
class BactAntibiotic_model extends Collection_model {
	protected $fields = [];

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
		$this->setInputRules([
			['field'=>'EvnLabSample_id'            ,'label' => 'EvnLabSample_id'                       ,'rules' => '', 'type' => 'int'],
			['field'=>'EvnLabSample_pid'           ,'label' => 'EvnLabSample_pid'                      ,'rules' => '', 'type' => 'id'],
			['field'=>'EvnLabSample_rid'           ,'label' => 'EvnLabSample_rid'                      ,'rules' => '', 'type' => 'id'],
			['field'=>'Lpu_id'                     ,'label' => 'Lpu_id'                                ,'rules' => '', 'type' => 'id'],
			['field'=>'Server_id'                  ,'label' => 'Server_id'                             ,'rules' => '', 'type' => 'int'],
			['field'=>'PersonEvn_id'               ,'label' => 'PersonEvn_id'                          ,'rules' => '', 'type' => 'id'],
			['field'=>'EvnLabSample_setDT'         ,'label' => 'EvnLabSample_setDT'                    ,'rules' => '', 'type' => 'datetime'],
			['field'=>'EvnLabSample_disDT'         ,'label' => 'EvnLabSample_disDT'                    ,'rules' => '', 'type' => 'datetime'],
			['field'=>'EvnLabSample_didDT'         ,'label' => 'EvnLabSample_didDT'                    ,'rules' => '', 'type' => 'datetime'],
			['field'=>'EvnLabSample_insDT'         ,'label' => 'EvnLabSample_insDT'                    ,'rules' => '', 'type' => 'datetime'],
			['field'=>'EvnLabSample_updDT'         ,'label' => 'EvnLabSample_updDT'                    ,'rules' => '', 'type' => 'datetime'],
			['field'=>'EvnLabSample_Index'         ,'label' => 'EvnLabSample_Index'                    ,'rules' => '', 'type' => 'int'],
			['field'=>'EvnLabSample_Count'         ,'label' => 'EvnLabSample_Count'                    ,'rules' => '', 'type' => 'int'],
			['field'=>'Morbus_id'                  ,'label' => 'Morbus_id'                             ,'rules' => '', 'type' => 'id'],
			['field'=>'EvnLabSample_IsSigned'      ,'label' => 'EvnLabSample_IsSigned'                 ,'rules' => '', 'type' => 'id'],
			['field'=>'pmUser_signID'              ,'label' => 'pmUser_signID'                         ,'rules' => '', 'type' => 'id'],
			['field'=>'EvnLabSample_signDT'        ,'label' => 'EvnLabSample_signDT'                   ,'rules' => '', 'type' => 'datetime'],
			['field'=>'EvnLabRequest_id'           ,'label' => 'Заявка на лабораторное исследование'   ,'rules' => '', 'type' => 'id'],
			['field'=>'EvnLabSample_Num'           ,'label' => 'Номер пробы'                           ,'rules' => '', 'type' => 'string'],
			['field'=>'EvnLabSample_BarCode'       ,'label' => 'Штрих-код пробы'                       ,'rules' => '', 'type' => 'string'],
			['field'=>'EvnLabSample_Comment'       ,'label' => 'Комментарий'                           ,'rules' => '', 'type' => 'string'],
			['field'=>'RefSample_id'               ,'label' => 'Справочник проб'                       ,'rules' => '', 'type' => 'id'],
			['field'=>'Lpu_did'                    ,'label' => 'ЛПУ взявшее пробу'                     ,'rules' => '', 'type' => 'id'],
			['field'=>'LpuSection_did'             ,'label' => 'Отделение взявшее пробу'               ,'rules' => '', 'type' => 'id'],
			['field'=>'MedPersonal_did'            ,'label' => 'Врач взявший пробу'                    ,'rules' => '', 'type' => 'id'],
			['field'=>'MedPersonal_sdid'           ,'label' => 'Средний медперсонал взявший пробу'     ,'rules' => '', 'type' => 'id'],
			['field'=>'MedService_id'              ,'label' => 'Служба заявки'                         ,'rules' => '', 'type' => 'id'],
			['field'=>'MedService_did'             ,'label' => 'Служба взявшая пробу'                  ,'rules' => '', 'type' => 'id'],
			['field'=>'MedService_sid'             ,'label' => 'Текущая служба'                        ,'rules' => '', 'type' => 'id'],
			['field'=>'EvnLabSample_DelivDT'       ,'label' => 'Дата и время доставки пробы'           ,'rules' => '', 'type' => 'datetime'],
			['field'=>'Lpu_aid'                    ,'label' => 'ЛПУ выполнившее анализ'                ,'rules' => '', 'type' => 'id'],
			['field'=>'LpuSection_aid'             ,'label' => 'Отделение выполнившее анализ'          ,'rules' => '', 'type' => 'id'],
			['field'=>'MedPersonal_aid'            ,'label' => 'Врач выполнивший анализ'               ,'rules' => '', 'type' => 'id'],
			['field'=>'MedPersonal_said'           ,'label' => 'Средний медперсонал выполнивший анализ','rules' => '', 'type' => 'id'],
			['field'=>'EvnLabSample_StudyDT'       ,'label' => 'Дата и время выполнения исследования'  ,'rules' => '', 'type' => 'datetime'],
			['field'=>'LabSampleDefectiveType_id'  ,'label' => 'Брак пробы'                            ,'rules' => '', 'type' => 'id'],
			['field'=>'DefectCauseType_id'         ,'label' => 'Брак пробы'                            ,'rules' => '', 'type' => 'id'],
			['field'=>'Analyzer_id'                ,'label' => 'Анализатор'                            ,'rules' => '', 'type' => 'id'],
			['field'=>'pmUser_id'                  ,'label' => 'идентификатор пользователя Промед'     ,'rules' => '', 'type' => 'id'],
			['field'=>'RecordStatus_Code'          ,'label' => 'идентификатор состояния записи'        ,'rules' => '', 'type' => 'int'],
			['field'=>'LabSample_Results'          ,'label' => 'Результаты пробы'                      ,'rules' => '', 'type' => 'string', 'onlyRule' => true]
		]);
	}

	function getAntibioticList($data) {
		$additional = ""; $whereClause = "1=1";
		
		$emptyMode = empty($data['mode']);
		$emptyMS = empty($data['MedService_id']);
		$emptyLvl = empty($data['BactAntibioticLev_id']);
		$isLeaf = $data['BactAntibioticLev_id'] == 3;
		if (!$emptyMode && !$emptyMS && !$emptyLvl && $isLeaf) {
			$additional = "with Lab as (
			select
				BactAntibiotic_id as \"BactAntibiotic_id\"
			from v_BactAntibioticLab
			where MedService_id = :MedService_id
			)";
			
			if ($data['mode'] == 'available') {
				$whereClause .= " and BactAntibiotic_id not in (select \"BactAntibiotic_id\" from Lab)";
			} else if ($data['mode'] == 'lab') {
				$whereClause .= " and BactAntibiotic_id in (select \"BactAntibiotic_id\" from Lab)";
			}
		}
		if (!$emptyLvl && $isLeaf && !empty($data['BactAntibiotic_Name'])) {
			$whereClause .= " and BactAntibiotic_Name ilike '%{$data['BactAntibiotic_Name']}%'";
		}
		if (!$emptyLvl && $isLeaf && !empty($data['BactGuideline_Code'])) {
			$whereClause .= " and BactGuideline_Code = :BactGuideline_Code";
		}
		if (!empty($data['BactAntibioticLev_id'])) $whereClause .= " and BactAntibioticLev_id = :BactAntibioticLev_id";
		if (!empty($data['BactAntibiotic_id'])) $whereClause .= " and BactAntibiotic_id in ({$data['BactAntibiotic_id']})";
		if (!empty($data['IgnoreIdList'])) $whereClause .= " and BactAntibiotic_id not in ({$data['IgnoreIdList']})";
		try {
			$query = "{$additional}
			select
			ba.BactAntibiotic_id as \"BactAntibiotic_id\",
			ba.BactAntibiotic_pid as \"BactAntibiotic_pid\",
			ba.BactAntibiotic_Code as \"BactAntibiotic_Code\",
			ba.BactAntibiotic_Name as \"BactAntibiotic_Name\",
			ba.BactAntibioticLev_id as \"BactAntibioticLev_id\",
			ba.BactAntibiotic_POTENCY as \"BactAntibiotic_POTENCY\",
			ba.BactAntibiotic_begDT as \"BactAntibiotic_begDT\",
			ba.BactAntibiotic_endDT as \"BactAntibiotic_endDT\"
			from v_BactAntibiotic ba
			left join v_BactGuideline bg on bg.BactGuideline_id = ba.BactGuideline_id
			where {$whereClause}
			order by BactAntibioticLev_id desc";
			return $this->queryResult($query, $data);
		} catch (Exception $e) {
			log_message('error', $e->getMessage());
			throw $e;
		}
	}

	function getUsedAntibiotic($params) {
		$query = "select
				bmpa.BactAntibiotic_id as \"BactAntibiotic_id\",
				bmpa.BactMethod_id as \"BactMethod_id\"
			from v_BactMicroProbeAntibiotic bmpa
			inner join v_UslugaTest ut on ut.UslugaTest_id = bmpa.UslugaTest_id
			where bmpa.BactMicroProbe_id = :BactMicroProbe_id";
		try {
			return $this->queryResult($query, $params);
		} catch (Exception $e) {
			log_message('error', $e->getMessage());
			throw $e;
		}
	}

	function getLabAntibioticList($params) {
		$whereClause = "1=1";

		if (!empty($params['MedService_id'])) $whereClause .= " and MedService_id = :MedService_id";
		if (!empty($params['BactAntibiotic_id'])) $whereClause .= " and BactAntibiotic_id in ($params[BactAntibiotic_id])";

		try {
		$query = "select
			BactAntibioticLab_id as \"BactAntibioticLab_id\",
			MedService_id as \"MedService_id\",
			BactAntibiotic_id as \"BactAntibiotic_id\"
			from v_BactAntibioticLab
			where {$whereClause}";
		return $this->queryResult($query, $params);
		} catch (Exception $e) {
			log_message('error', $e->getMessage());
			throw $e;
		}
	}

	public function getMicroAntibioticList($params) {
		$whereClause = "";
		if (!empty($params['BactMicroProbe_id'])) {
			$whereClause .= ' and bmpa.BactMicroProbe_id = :BactMicroProbe_id';
		} if (!empty($params['BactMicroProbeAntibiotic_id'])) {
			$whereClause .= ' and bmpa.BactMicroProbeAntibiotic_id = :BactMicroProbeAntibiotic_id';
		}
		$query = "select
				ut.UslugaTest_id as \"UslugaTest_id\",
				ut.UslugaTest_pid as \"UslugaTest_pid\",
				ut.UslugaTest_rid as \"UslugaTest_rid\",
				ut.UslugaTest_setDT as \"UslugaTest_setDT\",
				ut.UslugaTest_disDT as \"UslugaTest_disDT\",
				ut.Lpu_id as \"Lpu_id\",
				ut.Server_id as \"Server_id\",
				ut.PersonEvn_id as \"PersonEvn_id\",
				ut.UslugaComplex_id as \"UslugaComplex_id\",
				ut.EvnDirection_id as \"EvnDirection_id\",
				ut.Usluga_id as \"Usluga_id\",
				ut.PayType_id as \"PayType_id\",
				ut.UslugaPlace_id as \"UslugaPlace_id\",
				ut.UslugaTest_ResultValue as \"UslugaTest_ResultValue\",
				ut.UslugaTest_ResultUnit as \"UslugaTest_ResultUnit\",
				ut.UslugaTest_ResultApproved as \"UslugaTest_ResultApproved\",
				ut.UslugaTest_ResultAppDate as \"UslugaTest_ResultAppDate\",
				ut.UslugaTest_ResultCancelReason as \"UslugaTest_ResultCancelReason\",
				ut.UslugaTest_Comment as \"UslugaTest_Comment\",
				ut.Unit_id as \"Unit_id\",
				ut.UslugaTest_Kolvo as \"UslugaTest_Kolvo\",
				ut.UslugaTest_Result as \"UslugaTest_Result\",
				ut.EvnLabSample_id as \"EvnLabSample_id\",
				ut.EvnLabRequest_id as \"EvnLabRequest_id\",
				ut.UslugaTest_CheckDT as \"UslugaTest_CheckDT\",
				bmpa.BactAntibiotic_id as \"BactAntibiotic_id\",
				bmpa.BactMicroProbe_id as \"BactMicroProbe_id\",
				bmpa.BactMicroProbeAntibiotic_id as \"BactMicroProbeAntibiotic_id\",
				ba.BactAntibiotic_Name || ' ' || COALESCE(ba.BactAntibiotic_POTENCY, '') || ' ' || COALESCE(bg.BactGuideline_Name, '') as \"BactAntibiotic_Name\",
				sens.BactMicroABPSens_id as \"BactMicroABPSens_id\",
				sens.BactMicroABPSens_ShortName as \"BactMicroABPSens_ShortName\",
				bmas.BactMicroAntibioticSens_id as \"BactMicroAntibioticSens_id\",
				ut.UslugaTest_ResultLower as \"UslugaTest_ResultLower\",
				ut.UslugaTest_ResultUpper as \"UslugaTest_ResultUpper\",
				bm.BactMethod_Code as \"BactMethod_Code\",
				bm.BactMethod_Name as \"BactMethod_Name\",
				bmpa.BactMethod_id as \"BactMethod_id\",
				case
				when ut.UslugaTest_ResultApproved = 2 then 'Одобрен'
				when ut.UslugaTest_ResultValue is not null and rtrim(ut.UslugaTest_ResultValue) <> '' then 'Выполнен'
				when (ut.UslugaTest_id is not null) then 'Назначен'
				else 'Не назначен'
			end as \"UslugaTest_Status\"
			from v_UslugaTest ut
			inner join v_BactMicroProbeAntibiotic bmpa on bmpa.UslugaTest_id = ut.UslugaTest_id
			inner join v_BactAntibiotic ba on ba.BactAntibiotic_id = bmpa.BactAntibiotic_id
			left join v_BactMicroAntibioticSens bmas on bmas.BactMicroAntibioticSens_id = bmpa.BactMicroABPSens_id
			left join v_BactMethod bm on bm.BactMethod_id = bmpa.BactMethod_id
			left join v_BactMicroABPSens sens on sens.BactMicroABPSens_id = bmpa.BactMicroABPSens_id
			left join v_BactGuideline bg on bg.BactGuideline_id = ba.BactGuideline_id
			where 1=1 {$whereClause}";
		try {
			return $this->queryResult($query, $params);
		} catch (Exception $e) {
			log_message('error', $e->getMessage());
			throw $e;
		}
	}

	function addUslugaTest($params) {
		$query = "select
			UslugaTest_id as \"UslugaTest_id\",
			Error_Code as \"Error_Code\",
			Error_Message AS \"Error_Msg\"
		  from dbo.p_UslugaTest_ins(
			 UslugaTest_pid := :UslugaTest_pid,
			 UslugaTest_rid := :UslugaTest_rid,
			 UslugaTest_setDT := dbo.tzGetDate(),
			 UslugaTest_ResultLower := :UslugaTest_ResultLower,
			 UslugaTest_ResultUpper := :UslugaTest_ResultUpper,
			 Lpu_id := :Lpu_id,
			 Server_id := :Server_id,
			 PersonEvn_id := :PersonEvn_id,
			 PayType_id := :PayType_id,
			 UslugaTest_Kolvo := 1,
			 UslugaTest_ResultUnit := :UslugaTest_ResultUnit,
			 EvnLabSample_id := :EvnLabSample_id,
			 pmUser_id := :pmUser_id )
		";
		try {
			return $this->queryResult($query, $params);
		} catch (Exception $e) {
			log_message('error', $e->getMessage());
			throw $e;
		}
	}

	function getABPSense($params) {
		$query = "
			with recursive rec (BactMicro_id, BactMicro_pid) as (
			select bm1.BactMicro_id, bm1.BactMicro_pid
			from v_BactMicro bm1
			where  bm1.BactMicro_id = :BactMicro_id
			
			union all
			
			select bm2.BactMicro_id, bm2.BactMicro_pid
			from rec, v_BactMicro bm2
			where rec.BactMicro_pid = bm2.BactMicro_id
			)
			
			select
				sens.BactMicroAntibioticSens_id as \"BactMicroAntibioticSens_id\",
				sens.BactMicroAntibioticSens_min as \"BactMicroAntibioticSens_min\",
				sens.BactMicroAntibioticSens_max as \"BactMicroAntibioticSens_max\"
			from rec
			inner join v_BactMicroAntibioticSens sens on sens.BactMicro_id = rec.BactMicro_id
				and BactAntibiotic_id = :BactAntibiotic_id
				and BactMethod_id = :BactMethod_id";
		try {
			return $this->queryResult($query, $params);
		} catch (Exception $e) {
			log_message('error', $e->getMessage());
			throw $e;
		}
	}

	function updateAntibiotic($params) {
		$query = "
			update BactMicroProbeAntibiotic
				set
					{$params['changedField']}
					BactMicroProbeAntibiotic_updDT = dbo.tzGetDate(),
					pmUser_updID = :pmUser_id
				where BactMicroProbeAntibiotic_id = :BactMicroProbeAntibiotic_id";
		try {
			return $this->db->query($query, $params);
		} catch (Exception $e) {
			log_message('error', $e->getMessage());
			throw $e;
		}
	}

	function updateUslugaTest($params) {
		$query = "
			update UslugaTest
				set
					{$params['changedField']}
					UslugaTest_updDT = dbo.tzGetDate(),
					UslugaTest_setDT = dbo.tzGetDate(),
					pmUser_updID = :pmUser_id,
					UslugaTest_ResultApproved = 1
					
				where UslugaTest_id =
					(select bmpa.UslugaTest_id
					from v_BactMicroProbeAntibiotic bmpa
					where bmpa.BactMicroProbeAntibiotic_id = :BactMicroProbeAntibiotic_id)";
		try {
			return $this->db->query($query, $params);
		} catch (Exception $e) {
			log_message('error', $e->getMessage());
			throw $e;
		}
	}
}
