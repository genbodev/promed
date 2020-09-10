<?php	defined('BASEPATH') or die ('No direct script access allowed');

/**
 * @package	  CmpWorkPlan_model
 * @author	  Salavat Magafurov
 * @version	  12 2017
 */

class CmpWorkPlan_model extends swModel {

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Выполняет запрос к БД
	 */
	function execQuery($query,$data=null) {
		$result;
		if($data==null) {
			$result = $this->db->query($query);
		} else {
			$result = $this->db->query($query,$data);
		}

		if(is_object($result)) {
			return $result->result('array');
		} else {
			return null;
		}
	}

	/**
	 * Получаем план
	 */
	function getWorkPlan($data) {
		$params = array(
			'CmpWorkPlan_id' => $data['CmpWorkPlan_id']
		);
		$query="SELECT
					CWP.CmpWorkPlanData_id,
					CWP.CmpPlanType_id,
					CWP.CmpWorkTime_id,
					CWP.CmpWorkPlanData_BrigadeCount as BrigadeCount,
					CWP.CmpPlanType_Name as PlanType_Name,
					CWP.CmpWorkTime_Name as WorkTime_Name,
					LB.Lpu_id
				FROM
					r2.v_CmpWorkPlan CWP with(nolock)
					left join v_LpuBuilding LB with(nolock) on LB.LpuBuilding_id = CWP.LpuBuilding_id
				WHERE
					CmpWorkPlan_id = :CmpWorkPlan_id
				ORDER BY
					CmpPlanType_id ASC,
					CmpWorkTime_id ASC
		";
		return $this->execQuery($query,$params);
	}

	/**
	 * Получаем список последних планов
	 */
	function getWorkPlans($data) {

		$params = array();
		$filters = "";

		if($data['Lpu_id']) {
			$params['Lpu_id'] = $data['Lpu_id'];
			$filters .= " AND LB.Lpu_id = :Lpu_id";
		}

		if($data['LpuBuilding_id']) {
			$filters .= " AND CWP.LpuBuilding_id = :LpuBuilding_id";
			$params['LpuBuilding_id'] = $data['LpuBuilding_id'];
		}

		if($data['CmpWorkPlan_BegDT_Range'][0] && $data['CmpWorkPlan_BegDT_Range'][1]) {
			$filters .= " AND CWP.CmpWorkPlan_BegDT between :CmpWorkPlan_BegDT_0 and :CmpWorkPlan_BegDT_1";
			$params['CmpWorkPlan_BegDT_0'] = $data['CmpWorkPlan_BegDT_Range'][0];
			$params['CmpWorkPlan_BegDT_1'] = $data['CmpWorkPlan_BegDT_Range'][1];
		}

		if($data['CmpWorkPlan_EndDT_Range'][0] && $data['CmpWorkPlan_EndDT_Range'][1]) {
			$filters .= " AND CWP.CmpWorkPlan_EndDT between :CmpWorkPlan_EndDT_0 and :CmpWorkPlan_EndDT_1";
			$params['CmpWorkPlan_EndDT_0'] = $data['CmpWorkPlan_EndDT_Range'][0];
			$params['CmpWorkPlan_EndDT_1'] = $data['CmpWorkPlan_EndDT_Range'][1];
		}

		if(!$filters)
			$filters .= " AND CWP.CmpWorkPlan_BegDT = (SELECT MAX(CmpWorkPlan_BegDT) FROM r2.v_CmpWorkPlan with(nolock) WHERE LpuBuilding_id = CWP.LpuBuilding_id)";

		$query="SELECT
					CWP.CmpWorkPlan_id,
					CWP.LpuBuilding_id,
					CWP.LpuBuilding_Name,
					convert(varchar(10),CmpWorkPlan_BegDT,104) as CmpWorkPlan_BegDT,
					convert(varchar(10),CmpWorkPlan_EndDT,104) as CmpWorkPlan_EndDT,
					LB.Lpu_id,
					L.Lpu_Nick
				FROM
					r2.v_CmpWorkPlan CWP with(nolock)
					left join v_LpuBuilding LB with(nolock) on LB.LpuBuilding_id = CWP.LpuBuilding_id
					left join v_Lpu L with(nolock) on L.Lpu_id = LB.Lpu_id
				WHERE CmpWorkPlan_delDT is null and CWP.CmpPlanType_id = 1 and CWP.CmpWorkTime_id = 1
					$filters
				ORDER BY CWP.CmpWorkPlan_BegDT DESC";
		return $this->execQuery($query,$params);
	}

	/**
	 * Получаем список планов для конкретной подстанции
	 */
	function getSubstationPlans($data){
		$params = array(
			'LpuBuilding_id' => $data['LpuBuilding_id']
		);
		$query = '	SELECT DISTINCT
						CmpWorkPlan_id,
						LpuBuilding_id,
						convert(varchar(10),CWP.CmpWorkPlan_BegDT,104) as CmpWorkPlan_BegDT,
						convert(varchar(10),CWP.CmpWorkPlan_EndDT,104) as CmpWorkPlan_EndDT
					FROM
						r2.v_CmpWorkPlan CWP with(nolock)
					WHERE
							CmpWorkPlan_delDT is null
						AND
							LpuBuilding_id = :LpuBuilding_id
		';
		return $this->execQuery($query,$data);
	}

	/**
	 * Добавляем новый план
	 */
	function addWorkPlan($data) {
		try {
			if ( !$this->beginTransaction() ) {
				$this->isAllowTransaction = false;
				throw new Exception('Ошибка при попытке запустить транзакцию');
			}

			$cmpPlan = json_decode($data['Data'], true);

			$params = array(
				'CmpWorkPlan_id' => 0,
				'LpuBuilding_id' => $cmpPlan['LpuBuilding_id'],
				'CmpWorkPlan_BegDT' 	=> $cmpPlan['CmpWorkPlan_BegDT'],
				'CmpWorkPlan_EndDT'		=> $cmpPlan['CmpWorkPlan_EndDT'],
				'pmUser_id' 	=> $data['pmUser_id']
			);

			if(!$this->isValidDate($params))
				throw new Exception("На текущую дату план уже существует");

			$query="DECLARE
						@Error_Code int,
						@Error_Message varchar(4000),
						@CmpWorkPlan_id bigint;
					EXEC r2.p_CmpWorkPlan_ins
						@LpuBuilding_id = :LpuBuilding_id, 
						@CmpWorkPlan_BegDT = :CmpWorkPlan_BegDT,
						@CmpWorkPlan_EndDT = :CmpWorkPlan_EndDT,
						@pmUser_insID = :pmUser_id,
						@CmpWorkPlan_id = @CmpWorkPlan_id output,
						@Error_Code =@Error_Code output,
						@Error_Message = @Error_Message output
					SELECT 
						@Error_Code as Error_Code,
						@Error_Message as Error_Message,
						@CmpWorkPlan_id as CmpWorkPlan_id;";


			$result = $this->execQuery($query,$params);

			if(empty($result)) {
				throw new Exception("Не удалось создать план, попробуйте снова");
			}

			if(!empty($result[0]['Error_Message']) || !empty($result[0]['Error_Code'])) {
				throw new Exception("Не удалось создать план, попробуйте снова");
			}

			foreach($cmpPlan['Data'] as $plan) {

				$params = array(
					'pmUser_id'=>		$data['pmUser_id'],
					'CmpWorkPlan_id'=> 	$result[0]['CmpWorkPlan_id'],
					'CmpPlanType_id'=>	$plan['CmpPlanType_id'],
					'CmpWorkTime_id'=>	$plan['CmpWorkTime_id'],
					'CmpWorkPlanData_BrigadeCount'=>	$plan['BrigadeCount']
				);

				$query ="DECLARE
							@Error_Code int,
							@Error_Message varchar(4000);
						EXEC r2.p_CmpWorkPlanData_ins
							@CmpWorkPlan_id = :CmpWorkPlan_id, 
							@CmpPlanType_id = :CmpPlanType_id,
							@CmpWorkTime_id = :CmpWorkTime_id,
							@CmpWorkPlanData_BrigadeCount = :CmpWorkPlanData_BrigadeCount,
							@pmUser_insID = :pmUser_id,
							@Error_Code =@Error_Code output,
							@Error_Message = @Error_Message output
						SELECT 
							@Error_Code as Error_Code,
							@Error_Message as Error_Message;
				";
				$res = $this->execQuery($query,$params);

				if(empty($res)) {
					throw new Exception("Не удалось создать план, попробуйте снова");
				}

				if(!empty($res[0]['Error_Message']) || !empty($res[0]['Error_Code'])) {
					throw new Exception("Не удалось создать план, попробуйте снова");
				}
			}

			$this->commitTransaction();
			return $result;

		} catch(Exception $e) {

			$this->rollbackTransaction();
			return [array('Error_Message'=>$e->getMessage(),'Error_Code'=>$e->getCode())];

		}
	}

	/**
	 * Проверка на существование плана
	 */
	function isValidDate($params) {

		$query = "
			select top 1
				CmpWorkPlan_id
			from
				r2.CmpWorkPlan with(nolock)
			where
				LpuBuilding_id = :LpuBuilding_id 
				and (:CmpWorkPlan_BegDT between CmpWorkPlan_BegDT and CmpWorkPlan_EndDT
				  or CmpWorkPlan_BegDT between  :CmpWorkPlan_BegDT and :CmpWorkPlan_EndDT)
				and CmpWorkPlan_id <> :CmpWorkPlan_id
				and CmpWorkPlan_delDT is null
		";
		$result = $this->execQuery($query,$params);

		if($result) {
			if(!empty($result[0]['CmpWorkPlan_id']))
				return false;
		}
		
		return true;
	}

	/**
	 * Обновляем существующий план
	 */
	function updWorkPlan($data) {
		try {
			if ( !$this->beginTransaction() ) {
				$this->isAllowTransaction = false;
				throw new Exception('Ошибка при попытке запустить транзакцию');
			}

			$cmpPlan = json_decode($data['Data'], true);


			$params = array(
				'CmpWorkPlan_id'	=> $cmpPlan['CmpWorkPlan_id'],
				'LpuBuilding_id' => $cmpPlan['LpuBuilding_id'],
				'CmpWorkPlan_BegDT' 	=> $cmpPlan['CmpWorkPlan_BegDT'],
				'CmpWorkPlan_EndDT'		=> $cmpPlan['CmpWorkPlan_EndDT'],
				'pmUser_id' 	=> $data['pmUser_id']
			);

			if(!$this->isValidDate($params))
				throw new Exception("На текущую дату план уже существует");

			$query = "DECLARE
						@Error_Code int,
						@Error_Message varchar(4000);
					EXEC r2.p_CmpWorkPlan_upd
						@CmpWorkPlan_id = :CmpWorkPlan_id,
						@LpuBuilding_id = :LpuBuilding_id, 
						@CmpWorkPlan_BegDT =:CmpWorkPlan_BegDT,
						@CmpWorkPlan_EndDT = :CmpWorkPlan_EndDT,
						@pmUser_updID = :pmUser_id,
						@Error_Code =@Error_Code output,
						@Error_Message = @Error_Message output
					SELECT 
						@Error_Code as Error_Code,
						@Error_Message as Error_Message;
		
			";

			$result = $this->execQuery($query,$params);

			if(empty($result)) {
				throw new Exception("Не удалось создать план, попробуйте снова");
			}

			if(!empty($result[0]['Error_Message']) || !empty($result[0]['Error_Code'])) {
				throw new Exception("Не удалось создать план, попробуйте снова");
			}

			foreach($cmpPlan['Data'] as $plan) {

				$params = array(
					'pmUser_id'=>		$data['pmUser_id'],
					'CmpWorkPlanData_id'=> 	$plan['CmpWorkPlanData_id'],
					'CmpPlanType_id'=>	$plan['CmpPlanType_id'],
					'CmpWorkTime_id'=>	$plan['CmpWorkTime_id'],
					'CmpWorkPlanData_BrigadeCount'=>	$plan['BrigadeCount']
				);

				$query ="DECLARE
							@Error_Code int,
							@Error_Message varchar(4000);
						EXEC r2.p_CmpWorkPlanData_upd
							@CmpWorkPlanData_id = :CmpWorkPlanData_id,
							@CmpPlanType_id = :CmpPlanType_id,
							@CmpWorkTime_id = :CmpWorkTime_id,
							@CmpWorkPlanData_BrigadeCount = :CmpWorkPlanData_BrigadeCount,
							@pmUser_updID = :pmUser_id,
							@Error_Code =@Error_Code output,
							@Error_Message = @Error_Message output
						SELECT 
							@Error_Code as Error_Code,
							@Error_Message as Error_Message;
				";

				$res = $this->execQuery($query,$params);

				if(empty($res)) {
					throw new Exception("Не удалось создать план, попробуйте снова");
				}

				if(!empty($res[0]['Error_Message']) || !empty($res[0]['Error_Code'])) {
					throw new Exception("Не удалось создать план, попробуйте снова");
				}

			}

			$this->commitTransaction();
			return $result;

		} catch(Exception $e) {

			$this->rollbackTransaction();
			return [array('Error_Message'=>$e->getMessage(),'Error_Code'=>$e->getCode())];

		}

	}

	/**
	 * Удаление плана
	 */
	function delWorkPlan($data) {
		$params = array(
			'CmpWorkPlan_id' => $data['CmpWorkPlan_id'],
			'pmUser_id' => $data['pmUser_id']
		);
		$query = "	DECLARE
						@Error_Code int,
						@Error_Message varchar(4000);
					EXEC r2.p_CmpWorkPlan_del
						@CmpWorkPlan_id = :CmpWorkPlan_id,
						@pmUser_delID = :pmUser_id,
						@Error_Code =@Error_Code output,
						@Error_Message = @Error_Message output
					SELECT
						@Error_Code as Error_Code,
						@Error_Message as Error_Message;
		";
		return $this->execQuery($query,$params);
	}

	/**
	 * получение списка подстанций
	 */
	function getSubstationList($data) {
		$params = array();
		$filters = '';


		if(!empty($data['Lpu_id'])) {
			$params['Lpu_id'] = $data['Lpu_id'];
			$filters .= " AND Lpu_id = :Lpu_id";
		}

		$query = "	SELECT 
						LpuBuilding_id as id,
						LpuBuilding_Name as name 
					FROM
						v_LpuBuilding WITH (NOLOCK) 
					WHERE
						LpuBuildingType_id = 27
						$filters
		";
		return $this->execQuery($query,$params);
	}

	/**
	 * получение списка МО имеющих подразделение СМП
	 */
	function getLpuList() {
		$query="SELECT L.Lpu_id,L.Lpu_Nick
				FROM v_Lpu L
				inner join v_LpuBuilding LB on LB.Lpu_id = L.Lpu_id and LB.LpuBuildingType_id = 27
		";
		return $this->execQuery($query);
	}
}