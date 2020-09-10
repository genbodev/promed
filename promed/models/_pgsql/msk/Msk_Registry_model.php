<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Msk_Registry_model - модель для работы с таблицей Registry
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Admin
 * @region       Msk
 * @access       public
 * @copyright    Copyright (c) 2019 Swan Ltd.
 * @author       Shayahmetov
 * @version      23.11.2019
 */
require_once(APPPATH.'models/_pgsql/Registry_model.php');

class Msk_Registry_model extends Registry_model
{
	public $scheme = "r50";
	private $_registryTypeList = array(
		1=> array('RegistryType_id' => 1, 'RegistryType_Name' => 'Стационар', 'SP_Object' => 'EvnPS'),
		2 => array('RegistryType_id' => 2, 'RegistryType_Name' => 'Поликлиника', 'SP_Object' => 'EvnPL'),
		6 => array('RegistryType_id' => 6, 'RegistryType_Name' => 'Скорая помощь', 'SP_Object' => 'SMP'),
		7 => array('RegistryType_id' => 7, 'RegistryType_Name' => 'Дисп-ция взр. населения', 'SP_Object' => 'EvnPLDD13'),
		9 => array('RegistryType_id' => 9, 'RegistryType_Name' => 'Дисп-ция детей-сирот', 'SP_Object' => 'EvnPLOrp13'),
		11 => array('RegistryType_id' => 11, 'RegistryType_Name' => 'Проф.осмотры взр. населения', 'SP_Object' => 'EvnPLProf'),
		12 => array('RegistryType_id' => 12, 'RegistryType_Name' => 'Медосмотры несовершеннолетних', 'SP_Object' => 'EvnPLProfTeen')
	);
	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * Функция возвращает набор данных для дерева реестра 1-го уровня (тип реестра)
	 */
	public function loadRegistryTypeNode($data) {
		$result = [];
		
		foreach ( $this->_registryTypeList as $row ) {
			$result[] = $row;
		}
		
		return $result;
		
	}
	
	/**
	 * comment
	 */
	public function loadRegistryStatusNode($data) {
		
		return $result = array(
			array('RegistryStatus_id' => 5, 'RegistryStatus_Name' => 'В очереди'),
			array('RegistryStatus_id' => 3, 'RegistryStatus_Name' => 'В работе'),
			array('RegistryStatus_id' => 2, 'RegistryStatus_Name' => 'К оплате'),
			array('RegistryStatus_id' => 4, 'RegistryStatus_Name' => 'Оплаченные'),
			array('RegistryStatus_id' => 6, 'RegistryStatus_Name' => 'Удаленные'),
		);
	}
	
	/**
	 * Удаление объединённого реестра
	 */
	function deleteUnionRegistry($data)
	{
		// 1. удаляем все связи
		$query = "
			delete from {$this->scheme}.RegistryGroupLink
			where Registry_pid = :Registry_id
		";
		$this->db->query($query, array(
			'Registry_id' => $data['id']
		));
		
		// 2. удаляем сам реестр
		$query = "
			SELECT
				p_error_code as \"Error_Code\",
				p_error_message as \"Error_Msg\"
			FROM {$this->scheme}.p_Registry_del(
				:Registry_id,
				:pmUser_id);
		";
		
		$result = $this->db->query($query, array(
			'Registry_id' => $data['id'],
			'pmUser_id' => $data['pmUser_id']
		));
		
		if (is_object($result))
		{
			return $result->result('array');
		}
		
		return false;
	}
	
	/**
	 * Сохранение объединённого реестра
	 */
	public function saveUnionRegistry($data)
	{
		// проверка уникальности номера реестра по лпу в одном году
		$query = "
			select
				Registry_id as \"Registry_id\"
			from
				{$this->scheme}.v_Registry
			where
				RegistryType_id = 13
				and Lpu_id = :Lpu_id
				and Registry_Num = :Registry_Num
				and date_part('year', Registry_accDate) = date_part('year', cast(:Registry_accDate as date))
				and (Registry_id <> :Registry_id OR :Registry_id IS NULL)
			limit 1
		";
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			$resp = $result->result('array');
			if (!empty($resp[0]['Registry_id'])) {
				return array('Error_Msg' => 'Номер счета не должен повторяться в году');
			}
		}
		
		$registryFileNumCheck = $this->getFirstResultFromQuery("
			select Registry_id as \"Registry_id\"
			from {$this->scheme}.v_Registry
			where
				RegistryType_id = 13
				and Lpu_id = :Lpu_id
				and KatNasel_id = :KatNasel_id
				and OrgSMO_id = :OrgSMO_id
				and date_part('year', Registry_accDate) = date_part('year', cast(:Registry_accDate as date))
				and date_part('month', Registry_endDate) = date_part('month', cast(:Registry_endDate as date))
				and Registry_id != COALESCE(:Registry_id, 0)
				limit 1
		", $data, true);
		
		if ( !empty($registryFileNumCheck) ) {
			return array('Error_Msg' => 'Указанный номер пакета уже используется для данного отчётного периода и типа реестров. Необходимо указать неиспользуемый номер.');
		}
		
		// 1. сохраняем объединённый реестр
		$proc = 'p_Registry_ins';
		if (!empty($data['Registry_id'])) {
			$proc = 'p_Registry_upd';
		}
		$query = "
			select
				p_Registry_id as \"Registry_id\",
				p_Error_Code as \"Error_Code\",
				p_Error_Message as \"Error_Msg\"
			from {$this->scheme}.{$proc} (
				:Registry_id,
				:RegistryType_id,
				:Lpu_id,
				:OrgRSchet_id,
				:Registry_begDate,
				:Registry_endDate,
				:KatNasel_id,
				:OrgSMO_id,
				:RegistryStacType_id,
				:Registry_Num,
				:Registry_accDate,
				:RegistryStatus_id,
				:Registry_Sum,
				:Registry_IsActive,
				:Registry_ErrorCount,
				:Registry_ErrorCommonCount,
				:Registry_RecordCount,
				:Registry_RecordPaidCount,
				:Registry_kdCount,
				:Registry_kdPaidCount,
				:RegistryCheckStatus_id,
				:Registry_Task,
				:LpuBuilding_id,
				:Registry_isNeedReform,
				:Org_mid,
				:OrgRSchet_mid,
				:Registry_fileNum,
				:PayType_id,
				:Registry_IsRepeated,
				:RegistryGroupType_id,
				:Registry_isFlk,
				:RegistryEventType_id,
				:RegistryQueue_id,
				:Registry_isTest,
				:pmUser_id,
				:reform
			)
			
		";
		
		$params = array
		(
			'Registry_id' => !empty($data['Registry_id']) ? $data['Registry_id'] : null,
			'RegistryType_id' => 13,
			'Lpu_id' => $data['Lpu_id'],
			'OrgRSchet_id' => !empty($data['OrgRSchet_id']) ? $data['OrgRSchet_id'] : null,
			'Registry_begDate' => $data['Registry_begDate'],
			'Registry_endDate' => $data['Registry_endDate'],
			'KatNasel_id' => $data['KatNasel_id'],
			'OrgSMO_id' => $data['OrgSMO_id'],
			'RegistryStacType_id' => !empty($data['RegistryStacType_id']) ? $data['RegistryStacType_id'] : null,
			'Registry_Num' => $data['Registry_Num'],
			'Registry_accDate' => $data['Registry_accDate'],
			'RegistryStatus_id' => 1,
			'Registry_Sum' => null,
			'Registry_IsActive' => 2,
			'Registry_ErrorCount' => !empty($data['Registry_ErrorCount']) ? $data['Registry_ErrorCount'] : null,
			'Registry_ErrorCommonCount' => !empty($data['Registry_ErrorCommonCount']) ? $data['Registry_ErrorCommonCount'] : null,
			'Registry_RecordCount' => !empty($data['Registry_RecordCount']) ? $data['Registry_RecordCount'] : null,
			'Registry_RecordPaidCount' => !empty($data['Registry_RecordPaidCount']) ? $data['Registry_RecordPaidCount'] : null,
			'Registry_kdCount' => !empty($data['Registry_kdCount']) ? $data['Registry_kdCount'] : null,
			'Registry_kdPaidCount' => !empty($data['Registry_kdPaidCount']) ? $data['Registry_kdPaidCount'] : null,
			'RegistryCheckStatus_id' => !empty($data['RegistryCheckStatus_id']) ? $data['RegistryCheckStatus_id'] : null,
			'Registry_Task' => !empty($data['Registry_Task']) ? $data['Registry_Task'] : null,
			'LpuBuilding_id' => !empty($data['LpuBuilding_id']) ? $data['LpuBuilding_id'] : null,
			'Registry_isNeedReform' => !empty($data['Registry_isNeedReform']) ? $data['Registry_isNeedReform'] : null,
			'Org_mid' => !empty($data['Org_mid']) ? $data['Org_mid'] : null,
			'OrgRSchet_mid' => !empty($data['OrgRSchet_mid']) ? $data['OrgRSchet_mid'] : null,
			'Registry_fileNum' => !empty($data['Registry_fileNum']) ? $data['Registry_fileNum'] : null,
			'PayType_id' => !empty($data['PayType_id']) ? $data['PayType_id'] : null,
			'Registry_IsRepeated' => !empty($data['Registry_IsRepeated']) ? $data['Registry_IsRepeated'] : null,
			'RegistryGroupType_id' => !empty($data['RegistryGroupType_id']) ? $data['RegistryGroupType_id'] : null,
			'Registry_isFlk' => !empty($data['Registry_isFlk']) ? $data['Registry_isFlk'] : null,
			'RegistryEventType_id' => !empty($data['RegistryEventType_id']) ? $data['RegistryEventType_id'] : null,
			'RegistryQueue_id' => !empty($data['RegistryQueue_id']) ? $data['RegistryQueue_id'] : null,
			'Registry_isTest' => !empty($data['Registry_isTest']) ? $data['Registry_isTest'] : null,
			'pmUser_id' => $data['pmUser_id'],
			'reform' => !empty($data['reform']) ? $data['reform'] : null
		
		);
		
		$result = $this->db->query($query, $params);
		
		if (is_object($result))
		{
			$resp = $result->result('array');
			if (count($resp) > 0 && !empty($resp[0]['Registry_id'])) {
				// 2. удаляем все связи
				$query = "
					delete from {$this->scheme}.RegistryGroupLink
					where Registry_pid = :Registry_id
				";
				$this->db->query($query, array(
					'Registry_id' => $resp[0]['Registry_id']
				));
				
				$filterList = [];
				$KatNasel_Code = $this->getFirstResultFromQuery("select KatNasel_Code as \"KatNasel_Code\" from v_KatNasel where KatNasel_id = :KatNasel_id", $data);
				
				if ($KatNasel_Code == 3) {
					$filterList[] = "R.RegistryType_id <> 6";
					$filterList[] = "kn.KatNasel_Code = 1";
					$filterList[] = "R.RegistryType_id in (1, 2, 7, 5, 11, 12)";
				} else {
					if(!empty($data['OrgSMO_id'])){
						$filterList[] = "COALESCE(R.OrgSMO_id, 1) = :OrgSMO_id";
					}else{
						$filterList[] = "R.OrgSMO_id is null";
					}
					$filterList[] = "R.KatNasel_id = :KatNasel_id";
				}
				
				// 3. выполняем поиск реестров которые войдут в объединённый
				$query = "
					select
						R.Registry_id as \"Registry_id\",
						COALESCE(R.Registry_Sum, 0) as \"Registry_Sum\"
					from
						{$this->scheme}.v_Registry R
						left join v_KatNasel kn  on kn.KatNasel_id = R.KatNasel_id
						left join v_PayType PT  on PT.PayType_id = R.PayType_id
					where
						R.RegistryType_id <> 13
						and R.RegistryStatus_id = 2 -- к оплате
						and R.Lpu_id = :Lpu_id
						and R.Registry_begDate >= :Registry_begDate
						and R.Registry_endDate <= :Registry_endDate
						" . (count($filterList) > 0 ? "and " . implode(" and ", $filterList) : "");
				
				$result_reg = $this->db->query($query, array(
					'KatNasel_id' => $data['KatNasel_id'],
					'Lpu_id' => $data['Lpu_id'],
					'Registry_begDate' => $data['Registry_begDate'],
					'Registry_endDate' => $data['Registry_endDate'],
					'OrgSMO_id' => $data['OrgSMO_id']
				));
				
				if (is_object($result_reg))
				{
					$UnionRegistrySumma = 0;
					
					$resp_reg = $result_reg->result('array');
					// 4. сохраняем новые связи
					foreach($resp_reg as $one_reg) {
						$UnionRegistrySumma += $one_reg['Registry_Sum'];
						$query = "
							select * from {$this->scheme}.p_RegistryGroupLink_ins(
								Registry_pid := :Registry_pid,
								Registry_id := :Registry_id,
								pmUser_id := :pmUser_id
							)
						";
						
						$this->db->query($query, array(
							'Registry_pid' => $resp[0]['Registry_id'],
							'Registry_id' => $one_reg['Registry_id'],
							'pmUser_id' => $data['pmUser_id']
						));
					}
					
					$query = "
						update {$this->scheme}.Registry
						set Registry_Sum = :Registry_Sum
						where Registry_id = :Registry_id
						returning 0 as \"Error_Code\", '' as \"Error_Msg\"
					";
					
					$this->db->query($query, array(
						'Registry_id' => $resp[0]['Registry_id'],
						'Registry_Sum' => $UnionRegistrySumma
					));
				}
				
				// пишем информацию о формировании реестра в историю
				$this->dumpRegistryInformation(array(
					'Registry_id' => $resp[0]['Registry_id']
				), 1);
			}
			
			return $resp;
		}
		
		return false;
	}
	
	/**
	 * Загрузка списка объединённых реестров
	 */
	function loadUnionRegistryGrid($data)
	{
		$query = "
			Select
				-- select
				R.Registry_id as \"Registry_id\",
				R.Registry_Num as \"Registry_Num\",
				R.Registry_FileNum as \"Registry_FileNum\",
				R.Registry_xmlExportPath as \"Registry_xmlExportPath\",
				RTrim(coalesce(to_char(R.Registry_accDate, 'dd.mm.yyyy'),'')) as \"Registry_accDate\",
				RTrim(coalesce(to_char(R.Registry_begDate, 'dd.mm.yyyy'),'')) as \"Registry_begDate\",
				RTrim(coalesce(to_char(R.Registry_endDate, 'dd.mm.yyyy'),'')) as \"Registry_endDate\",
				R.KatNasel_id as \"KatNasel_id\",
				KN.KatNasel_Name as \"KatNasel_Name\",
				KN.KatNasel_SysNick as \"KatNasel_SysNick\",
				COALESCE(RS.Registry_SumPaid, 0.00) as \"Registry_SumPaid\",
				OS.OrgSMO_Nick as \"OrgSMO_Nick\"
				-- end select
			from
				-- from
				{$this->scheme}.v_Registry R  -- объединённый реестр
				left join v_KatNasel KN  on KN.KatNasel_id = R.KatNasel_id
				left join v_OrgSMO OS on OS.OrgSMO_id = R.OrgSMO_id
				LEFT JOIN LATERAL(
					select
						SUM(COALESCE(R2.Registry_SumPaid,0)) as Registry_SumPaid
					from {$this->scheme}.v_Registry R2
						inner join {$this->scheme}.v_RegistryGroupLink RGL  on R2.Registry_id = RGL.Registry_id
					where
						RGL.Registry_pid = R.Registry_id
				) RS on true
				-- end from
			where
				-- where
				R.Lpu_id = :Lpu_id
				and R.RegistryType_id = 13
				-- end where
			order by
				-- order by
				R.Registry_id desc
				-- end order by
		";
		
		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit'],'', '', '', true), $data);
		$result_count = $this->db->query(getCountSQLPH($query), $data);
		
		if (is_object($result_count))
		{
			$cnt_arr = $result_count->result('array');
			$count = $cnt_arr[0]['cnt'];
			unset($cnt_arr);
		}
		else
		{
			$count = 0;
		}
		if (is_object($result))
		{
			$response = [];
			$response['data'] = $result->result('array');
			$response['totalCount'] = $count;
			return $response;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Загрузка формы редактирования объединённого реестра
	 */
	function loadUnionRegistryEditForm($data)
	{
		$query = "
			select
				R.Registry_id as \"Registry_id\",
				R.Registry_Num as \"Registry_Num\",
				RTrim(coalesce(to_char(R.Registry_accDate, 'dd.mm.yyyy'),'')) as \"Registry_accDate\",
				RTrim(coalesce(to_char(R.Registry_begDate, 'dd.mm.yyyy'),'')) as \"Registry_begDate\",
				RTrim(coalesce(to_char(R.Registry_endDate, 'dd.mm.yyyy'),'')) as \"Registry_endDate\",
				R.KatNasel_id as \"KatNasel_id\",
				R.OrgSMO_id as \"OrgSMO_id\",
				R.Lpu_id as \"Lpu_id\"
			from
				{$this->scheme}.v_Registry R
			where
				R.Registry_id = :Registry_id
		";
		
		$result = $this->db->query($query, $data);
		
		if (is_object($result))
		{
			return $result->result('array');
		}
		
		return false;
	}
	
	/**
	 * Загрузка списка обычных реестров, входящих в объединённый
	 */
	function loadUnionRegistryChildGrid($data)
	{
		$query = "
			Select
				-- select
				R.Registry_id as \"Registry_id\",
				R.Registry_Num as \"Registry_Num\",
				RTrim(coalesce(to_char(R.Registry_accDate, 'dd.mm.yyyy'),'')) as \"Registry_accDate\",
				RTrim(coalesce(to_char(R.Registry_begDate, 'dd.mm.yyyy'),'')) as \"Registry_begDate\",
				RTrim(coalesce(to_char(R.Registry_endDate, 'dd.mm.yyyy'),'')) as \"Registry_endDate\",
				KN.KatNasel_Name as \"KatNasel_Name\",
				RT.RegistryType_Name as \"RegistryType_Name\",
				COALESCE(R.Registry_Sum, 0.00) as \"Registry_Sum\",
				COALESCE(R.Registry_SumPaid, 0.00) as \"Registry_SumPaid\",
				PT.PayType_Name as \"PayType_Name\",
				LB.LpuBuilding_Name as \"LpuBuilding_Name\",
				to_char(R.Registry_updDT,'YYYY-MM-DD') as \"Registry_updDate\"
				-- end select
			from
				-- from
				{$this->scheme}.v_RegistryGroupLink RGL
				inner join {$this->scheme}.v_Registry R  on R.Registry_id = RGL.Registry_id -- обычный реестр
				left join v_KatNasel KN  on KN.KatNasel_id = R.KatNasel_id
				left join v_RegistryType RT  on RT.RegistryType_id = R.RegistryType_id
				left join v_PayType PT  on PT.PayType_id = R.PayType_id
				left join v_LpuBuilding LB  on LB.LpuBuilding_id = R.LpuBuilding_id
				-- end from
			where
				-- where
				RGL.Registry_pid = :Registry_pid
				-- end where
			order by
				-- order by
				R.Registry_id
				-- end order by";
		/*
		echo getDebugSql($query, $data);
		exit;
		*/
		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit'], '', '', '', true), $data);
		$result_count = $this->db->query(getCountSQLPH($query), $data);
		
		if (is_object($result_count))
		{
			$cnt_arr = $result_count->result('array');
			$count = $cnt_arr[0]['cnt'];
			unset($cnt_arr);
		}
		else
		{
			$count = 0;
		}
		if (is_object($result))
		{
			$response = [];
			$response['data'] = $result->result('array');
			$response['totalCount'] = $count;
			return $response;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 *	Загрузка списка предварительных
	 */
	function loadRegistry($data) {
		$filter = "(1=1)";
		$params = array('Lpu_id' => (!empty($data['Lpu_id']) ? $data['Lpu_id'] : $data['session']['lpu_id']));
		$filter .= ' and R.Lpu_id = :Lpu_id';
		
		if ( !empty($data['Registry_id']) ) {
			$filter .= ' and R.Registry_id = :Registry_id';
			$params['Registry_id'] = $data['Registry_id'];
		}
		
		if ( !empty($data['RegistryType_id']) ) {
			$filter .= ' and R.RegistryType_id = :RegistryType_id';
			$params['RegistryType_id'] = $data['RegistryType_id'];
		}
		
		$this->setRegistryParamsByType($data);
		
		//запрос для реестров в очереди
		if ( (isset($data['RegistryStatus_id'])) && ($data['RegistryStatus_id'] == 5) ) {
			$query = "
				Select
					R.RegistryQueue_id as \"Registry_id\",
					R.Lpu_id as \"Lpu_id\",
					R.RegistryType_id as \"RegistryType_id\",
					R.PayType_id as \"PayType_id\",
					PT.PayType_Name as \"PayType_Name\",
					'' as \"Diag_Code\",
					5 as \"RegistryStatus_id\",
					R.RegistryStacType_id as \"RegistryStacType_id\",
					R.RegistryEventType_id as \"RegistryEventType_id\",
					2 as \"Registry_IsActive\",
					1 as \"Registry_IsProgress\",
					1 as \"Registry_IsNeedReform\",
					RTrim(R.Registry_Num) || ' / в очереди: ' || LTrim(cast(RegistryQueue_Position as varchar)) as \"Registry_Num\",
					null as \"ReformTime\",
					RTrim(coalesce(to_char(R.Registry_accDate, 'dd.mm.yyyy'),'')) as \"Registry_accDate\",
					RTrim(coalesce(to_char(R.Registry_begDate, 'dd.mm.yyyy'),'')) as \"Registry_begDate\",
					RTrim(coalesce(to_char(R.Registry_endDate, 'dd.mm.yyyy'),'')) as \"Registry_endDate\",
					0 as \"Registry_Count\",
					0 as \"Registry_RecordPaidCount\",
					0 as \"Registry_KdCount\",
					0 as \"Registry_KdPaidCount\",
					0 as \"Registry_Sum\",
					0 as \"Registry_SumPaid\",
					LpuBuilding.LpuBuilding_Name as \"LpuBuilding_Name\",
					RTrim(RegistryStacType.RegistryStacType_Name) as \"RegistryStacType_Name\",
					RTrim(RegistryEventType.RegistryEventType_Name) as \"RegistryEventType_Name\",
					R.Registry_IsRepeated as \"Registry_IsRepeated\",
					case when R.Registry_IsFLK = 2 then 'true' else 'false' end as \"Registry_IsFLK\",
					'' as \"Registry_updDate\",
					'' as \"Mes_Code_KSG\",
					-- количество записей в таблицах RegistryErrorCom, RegistryError, RegistryPerson, RegistryNoPolis, RegistryNoPay
					0 as \"RegistryErrorCom_IsData\",
					0 as \"RegistryError_IsData\",
					0 as \"RegistryPerson_IsData\",
					0 as \"RegistryNoPolis_IsData\",
					0 as \"RegistryNoPay_IsData\",
					0 as \"RegistryErrorTFOMS_IsData\",
					0 as \"RegistryNoPaid_Count\",
					0 as \"RegistryNoPay_UKLSum\",
					-1 as \"RegistryCheckStatus_Code\",
					0 as \"RegistryErrorTFOMSType_id\"
					--'' as RegistryCheckStatus_Name
				from {$this->scheme}.v_RegistryQueue R
					left join v_PayType PT  on PT.PayType_id = R.PayType_id
					left join v_LpuBuilding LpuBuilding on LpuBuilding.LpuBuilding_id = R.LpuBuilding_id
					left join v_RegistryStacType RegistryStacType on RegistryStacType.RegistryStacType_id = R.RegistryStacType_id
					left join {$this->scheme}.v_RegistryEventType RegistryEventType on RegistryEventType.RegistryEventType_id = R.RegistryEventType_id
				where {$filter}
				order by R.RegistryQueue_id desc
			";
		}
		// для всех реестров, кроме тех что в очереди
		else {
			$source_table = 'v_Registry';
			
			if ( !empty($data['RegistryStatus_id']) ) {
				if ( 6 == (int)$data['RegistryStatus_id'] ) {
					//6 - если запрошены удаленные реестры
					$source_table = 'v_Registry_deleted';
					//т.к. для удаленных реестров статус не важен - не накладываем никаких условий на статус реестра.
				}
				else {
					$filter .= ' and R.RegistryStatus_id = :RegistryStatus_id';
					$params['RegistryStatus_id'] = $data['RegistryStatus_id'];
				}
				
				// только если оплаченные!!!
				if ( 4 == (int)$data['RegistryStatus_id'] ) {
					if( $data['Registry_accYear'] > 0 ) {
						$filter .= " and date_part('year', R.Registry_begDate) <= :Registry_accYear";
						$filter .= " and date_part('year', R.Registry_endDate) >= :Registry_accYear";
						$params['Registry_accYear'] = $data['Registry_accYear'];
					}
				}
			}
			
			$query = "
				Select
					R.Registry_id as \"Registry_id\",
					R.Lpu_id as \"Lpu_id\",
					R.RegistryType_id as \"RegistryType_id\",
					R.PayType_id as \"PayType_id\",
					PT.PayType_Name as \"PayType_Name\",
					" . (!empty($data['RegistryStatus_id']) && 6 == (int)$data['RegistryStatus_id'] ? "6 as \"RegistryStatus_id\"" : "R.RegistryStatus_id as \"RegistryStatus_id\"") . ",
					R.OrgRSchet_id as \"OrgRSchet_id\",
					R.KatNasel_id as \"KatNasel_id\",
					KN.KatNasel_Name as \"KatNasel_Name\",
					KN.KatNasel_SysNick as \"KatNasel_SysNick\",
					OS.OrgSMO_Nick as \"OrgSMO_Nick\",
					OS.OrgSMO_id as \"OrgSMO_id\",
					R.RegistryStacType_id as \"RegistryStacType_id\",
					R.RegistryEventType_id as \"RegistryEventType_id\",
					R.Registry_IsActive as \"Registry_IsActive\",
					case when RQ.RegistryQueue_id is not null then 1 else 0 end as \"Registry_IsProgress\",
					COALESCE(R.Registry_IsNeedReform, 1) as \"Registry_IsNeedReform\",
					RTrim(R.Registry_Num) as \"Registry_Num\",
					to_char(RQH.RegistryQueueHistory_endDT, 'DD.MM.YYYY HH24:MI:SS') as \"ReformTime\",
					RTrim(coalesce(to_char(R.Registry_accDate, 'dd.mm.yyyy'),'')) as \"Registry_accDate\",
					RTrim(coalesce(to_char(R.Registry_begDate, 'dd.mm.yyyy'),'')) as \"Registry_begDate\",
					RTrim(coalesce(to_char(R.Registry_endDate, 'dd.mm.yyyy'),'')) as \"Registry_endDate\",
					COALESCE(R.Registry_RecordCount, 0) as \"Registry_Count\",
					COALESCE(R.Registry_RecordPaidCount, 0) as \"Registry_RecordPaidCount\",
					COALESCE(R.Registry_KdCount, 0) as \"Registry_KdCount\",
					COALESCE(R.Registry_KdPaidCount, 0) as \"Registry_KdPaidCount\",
					COALESCE(R.Registry_Sum, 0.00) as \"Registry_Sum\",
					0 as \"Registry_SumPaid\",
					LpuBuilding.LpuBuilding_Name as \"LpuBuilding_Name\",
					R.Registry_IsRepeated as \"Registry_IsRepeated\",
					case when R.Registry_IsFLK = 2 then 'true' else 'false' end as \"Registry_IsFLK\",
					RTrim(RegistryStacType.RegistryStacType_Name) as \"RegistryStacType_Name\",
					RTrim(RegistryEventType.RegistryEventType_Name) as \"RegistryEventType_Name\",
					RTrim(COALESCE(to_char(R.Registry_updDT,'DD.MM.YYYY HH24:MI:SS'),'')) as \"Registry_updDate\",
					-- количество записей в таблицах RegistryErrorCom, RegistryError, RegistryPerson, RegistryNoPolis, RegistryNoPay, RegistryDouble
					RegistryErrorCom.RegistryErrorCom_IsData as \"RegistryErrorCom_IsData\",
					RegistryError.RegistryError_IsData as \"RegistryError_IsData\",
					RegistryPerson.RegistryPerson_IsData as \"RegistryPerson_IsData\",
					RegistryNoPolis.RegistryNoPolis_IsData as \"RegistryNoPolis_IsData\",
					RegistryDouble.RegistryDouble_IsData as \"RegistryDouble_IsData\",
					case when RegistryNoPaid_Count>0 then 1 else 0 end as \"RegistryNoPaid_IsData\",
					RegistryErrorTFOMS.RegistryErrorTFOMS_IsData as \"RegistryErrorTFOMS_IsData\",
					RegistryNoPaid.RegistryNoPaid_Count as \"RegistryNoPaid_Count\",
					0 as \"RegistryNoPay_UKLSum\",
					COALESCE(RegistryCheckStatus.RegistryCheckStatus_Code, -1) as \"RegistryCheckStatus_Code\",
					RegistryErrorTFOMS.RegistryErrorTFOMSType_id as \"RegistryErrorTFOMSType_id\"
				from {$this->scheme}.{$source_table} R
					left join v_PayType PT  on PT.PayType_id = R.PayType_id
					left join v_RegistryStacType RegistryStacType on RegistryStacType.RegistryStacType_id = R.RegistryStacType_id
					left join {$this->scheme}.v_RegistryEventType RegistryEventType on RegistryEventType.RegistryEventType_id = R.RegistryEventType_id
					left join v_LpuBuilding LpuBuilding on LpuBuilding.LpuBuilding_id = R.LpuBuilding_id
					left join v_RegistryCheckStatus RegistryCheckStatus on RegistryCheckStatus.RegistryCheckStatus_id = R.RegistryCheckStatus_id
					left join v_KatNasel KN  on KN.KatNasel_id = R.KatNasel_id
					left join v_OrgSMO OS on OS.OrgSMO_id = R.OrgSMO_id
					LEFT JOIN LATERAL(
						select RegistryQueue_id
						from {$this->scheme}.v_RegistryQueue
						where Registry_id = R.Registry_id
						limit 1
					) RQ on true
					LEFT JOIN LATERAL(
						select RegistryQueueHistory_endDT
						from {$this->scheme}.RegistryQueueHistory
						where Registry_id = R.Registry_id
							and RegistryQueueHistory_endDT is not null
						order by RegistryQueueHistory_id desc
						limit 1
					) RQH on true
					LEFT JOIN LATERAL(select case when RE.Registry_id is not null then 1 else 0 end as RegistryErrorCom_IsData from {$this->scheme}.v_{$this->RegistryErrorComObject} RE where RE.Registry_id = R.Registry_id limit 1) RegistryErrorCom on true
					LEFT JOIN LATERAL(select case when RE.Registry_id is not null then 1 else 0 end as RegistryError_IsData from {$this->scheme}.v_{$this->RegistryErrorObject} RE where RE.Registry_id = R.Registry_id limit 1) RegistryError on true
					LEFT JOIN LATERAL(select case when RE.Registry_id is not null then 1 else 0 end as RegistryPerson_IsData from {$this->scheme}.v_{$this->RegistryPersonObject} RE where RE.Registry_id = R.Registry_id and COALESCE(RE.{$this->RegistryPersonObject}_IsDifferent, 1) = 1 limit 1) RegistryPerson on true
					LEFT JOIN LATERAL(select case when RE.Registry_id is not null then 1 else 0 end as RegistryNoPolis_IsData from {$this->scheme}.v_RegistryNoPolis RE where RE.Registry_id = R.Registry_id limit 1) RegistryNoPolis on true
					LEFT JOIN LATERAL(select case when RE.Registry_id is not null then 1 else 0 end as RegistryErrorTFOMS_IsData, RegistryErrorTFOMSType_id from {$this->scheme}.v_RegistryErrorTFOMS RE where RE.Registry_id = R.Registry_id limit 1) RegistryErrorTFOMS on true
					LEFT JOIN LATERAL(select case when RE.Registry_id is not null then 1 else 0 end as RegistryDouble_IsData from {$this->scheme}.v_{$this->RegistryDoubleObject} RE where RE.Registry_id = R.Registry_id limit 1) RegistryDouble on true
					LEFT JOIN LATERAL(
						select
							count(RDnoPaid.Evn_id) as RegistryNoPaid_Count
						from {$this->scheme}.v_RegistryNoPay RDnoPaid
						where RDnoPaid.Registry_id = R.Registry_id
					) RegistryNoPaid on true
				where
					{$filter}
				order by
					R.Registry_id desc
			";
		}
		//echo getDebugSQL($query, $params);
		$result = $this->db->query($query, $params);
		
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 *	Загрузка данных по реестру
	 */
	function loadRegistryData($data)
	{
		if ($data['Registry_id']==0)
		{
			return false;
		}
		if ($data['RegistryType_id']==0)
		{
			return false;
		}
		
		if (!(($data['start'] >= 0) && ($data['limit'] >= 0)))
		{
			return false;
		}
		
		$this->setRegistryParamsByType($data);
		
		// Взависимости от типа реестра возвращаем разные наборы данных
		$params = array(
			'Registry_id' => $data['Registry_id'],
			'Lpu_id'=>$data['session']['lpu_id']
		);
		$filter="(1=1)";
		if (isset($data['Person_SurName']))
		{
			$filter .= " and RD.Person_SurName like :Person_SurName ";
			$params['Person_SurName'] = rtrim($data['Person_SurName'])."%";
		}
		if (isset($data['Person_FirName']))
		{
			$filter .= " and RD.Person_FirName like :Person_FirName ";
			$params['Person_FirName'] = rtrim($data['Person_FirName'])."%";
		}
		if (isset($data['Person_SecName']))
		{
			$filter .= " and RD.Person_SecName like :Person_SecName ";
			$params['Person_SecName'] = rtrim($data['Person_SecName'])."%";
		}
		if(!empty($data['Polis_Num'])) {
			$filter .= " and RD.Polis_Num = :Polis_Num";
			$params['Polis_Num'] = $data['Polis_Num'];
		}
		
		if(!empty($data['MedPersonal_id'])) {
			$filter .= " and RD.MedPersonal_id = :MedPersonal_id";
			$params['MedPersonal_id'] = $data['MedPersonal_id'];
		}
		
		if(!empty($data['Evn_id'])) {
			$filter .= " and RD.Evn_id = :Evn_id";
			$params['Evn_id'] = $data['Evn_id'];
		}
		
		if(!empty($data['RegistryData_RowNum'])) {
			$filter .= " and RD.RegistryData_RowNum = :RegistryData_RowNum";
			$params['RegistryData_RowNum'] = $data['RegistryData_RowNum'];
		}
		
		if ( !empty($data['filterRecords']) ) {
			if ($data['filterRecords'] == 2) {
				$filter .= " and COALESCE(RD.RegistryData_IsPaid,1) = 2";
			} elseif ($data['filterRecords'] == 3) {
				$filter .= " and COALESCE(RD.RegistryData_IsPaid,1) = 1";
			}
		}
		
		$join = "";
		$fields = "";
		
		if ( in_array($data['RegistryType_id'], array(7, 9, 12)) ) {
			$join .= "left join v_EvnPLDisp epd on epd.EvnPLDisp_id = RD.Evn_rid ";
			$fields .= "epd.DispClass_id as \"DispClass_id\", ";
		}
		
		
		$diagFilter = getAccessRightsDiagFilter('D.Diag_Code');
		if (!empty($diagFilter)) {
			$filter .= " and {$diagFilter}";
		}
		
		if ( in_array($data['RegistryType_id'], $this->getAllowedRegistryTypes()) )
		{
			if (isset($data['RegistryStatus_id']) && (6==$data['RegistryStatus_id'])) {
				$source_table = 'v_RegistryDeleted_Data';
			} else {
				$source_table = 'v_' . $this->RegistryDataObject;
			}
			$query = "
				-- addit with
				with PE (
					Person_id,
					PersonEvn_id,
					PersonEvn_insDT
				) as (
					select
						t1.Person_id,
						t1.PersonEvn_id,
						t1.PersonEvn_insDT
					from v_PersonEvn t1
						inner join {$this->scheme}.v_{$this->RegistryDataObject} t2 on t2.Person_id = t1.Person_id
					where t2.Registry_id = :Registry_id
				)
				-- end addit with
	
				Select
					-- select
					RD.Evn_id as \"Evn_id\",
					RD.Evn_rid as \"Evn_rid\",
					RD.EvnClass_id as \"EvnClass_id\",
					RD.Registry_id as \"Registry_id\",
					RD.RegistryType_id as \"RegistryType_id\",
					RD.RegistryData_RowNum as \"RegistryData_RowNum\",
					RD.Person_id as \"Person_id\",
					RD.Server_id as \"Server_id\",
					PersonEvn.PersonEvn_id as \"PersonEvn_id\",
					{$fields}
					case when RDL.Person_id is null then 0 else 1 end as \"IsRDL\",
					RD.needReform as \"needReform\", RD.checkReform as \"checkReform\", RD.timeReform as \"timeReform\",
					case when RD.needReform=2 and RegistryQueue.RegistryQueue_id is not null then 2 else 1 end \"isNoEdit\",
					RD.RegistryData_deleted as \"RegistryData_deleted\",
					RTrim(RD.NumCard) as \"EvnPL_NumCard\",
					RTrim(RD.Person_FIO) as \"Person_FIO\",
					RTrim(coalesce(to_char(RD.Person_BirthDay, 'dd.mm.yyyy'),'')) as \"Person_BirthDay\",
					CASE WHEN RD.Person_IsBDZ = 1 THEN 'true' ELSE 'false' END as \"Person_IsBDZ\",
					RD.LpuSection_id as \"LpuSection_id\",
					RTrim(RD.LpuSection_Name) as \"LpuSection_Name\",
					RTrim(RD.MedPersonal_Fio) as \"MedPersonal_Fio\",
					RTrim(coalesce(to_char(RD.Evn_setDate, 'dd.mm.yyyy'),'')) as \"EvnVizitPL_setDate\",
					RTrim(coalesce(to_char(RD.Evn_disDate, 'dd.mm.yyyy'),'')) as \"Evn_disDate\",
					RD.RegistryData_Tariff as \"RegistryData_Tariff\",
					RD.RegistryData_KdFact as \"RegistryData_Uet\",
					RD.RegistryData_KdPay as \"RegistryData_KdPay\",
					RD.RegistryData_KdPlan as \"RegistryData_KdPlan\",
					RD.RegistryData_ItogSum as \"RegistryData_ItogSum\",
					COALESCE(RegistryError.Err_Count,RegistryErrorTFOMS.Err_Count, 0) as \"Err_Count\",
					PMT.PayMedType_Code as \"PayMedType_Code\",
					D.Diag_Code as \"Diag_Code\",
					MOLD.Mes_Code || COALESCE(' ' || MOLD.MesOld_Num, '') as \"Mes_Code\"
					-- end select
				from
					-- from
					{$this->scheme}.{$source_table} RD
					{$join}
					left join v_PayMedType PMT  on PMT.PayMedType_id = RD.PayMedType_id
					left join {$this->scheme}.RegistryQueue on RegistryQueue.Registry_id = RD.Registry_id
					left join v_Diag D on D.Diag_id = RD.Diag_id
					left join v_MesOld MOLD  on MOLD.Mes_id = RD.Mes_id
					LEFT JOIN LATERAL (
						select RDLT.Person_id from {$this->scheme}.RegistryDataLgot RDLT where RD.Person_id = RDLT.Person_id and (RD.Evn_id = RDLT.Evn_id or RDLT.Evn_id is null) limit 1
					) RDL on true
					LEFT JOIN LATERAL (
						Select count(*) as Err_Count
						from {$this->scheme}.v_{$this->RegistryErrorObject} RE
						where RD.Evn_id = RE.Evn_id
							and RD.Registry_id = RE.Registry_id
					) RegistryError on true
					LEFT JOIN LATERAL (
						Select count(*) as Err_Count
						from {$this->scheme}.v_RegistryErrorTFOMS RET
						where RD.Evn_id = RET.Evn_id
							and RD.Registry_id = RET.Registry_id
					) RegistryErrorTFOMS on true
					LEFT JOIN LATERAL (
						select PersonEvn_id
						from PE
						where Person_id = RD.Person_id
							and PersonEvn_insDT <= COALESCE(RD.Evn_disDate, RD.Evn_setDate)
						order by PersonEvn_insDT desc
						limit 1
					) PersonEvn on true
				-- end from
				where
					-- where
					RD.Registry_id=:Registry_id
					and
					{$filter}
					-- end where
				order by
					-- order by
					RD.Person_FIO
					-- end order by
			";
		}
		
		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit'], '', '', '', true), $params);
		$result_count = $this->db->query(getCountSQLPH($query), $params);
		
		if (is_object($result_count))
		{
			$cnt_arr = $result_count->result('array');
			$count = $cnt_arr[0]['cnt'];
			unset($cnt_arr);
		}
		else
		{
			$count = 0;
		}
		if (is_object($result))
		{
			$response = [];
			$response['data'] = $result->result('array');
			$response['totalCount'] = $count;
			return $response;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Кэширование некоторых параметров реестра в зависимости от его типа
	 */
	function setRegistryParamsByType($data = [], $force = false) {
		parent::setRegistryParamsByType($data, $force);
		
		switch ( $this->RegistryType_id ) {
			case 1:
				$this->RegistryDataObject = 'RegistryDataEvnPS';
				$this->RegistryErrorObject = 'RegistryErrorEvnPS';
				$this->RegistryDoubleObject = 'RegistryDoubleEvnPS';
				break;
			
			case 2:
				$this->RegistryDataObject = 'RegistryData';
				$this->RegistryErrorObject = 'RegistryError';
				break;
			
			case 6:
				$this->RegistryDataObject = 'RegistryDataCmp';
				$this->RegistryErrorObject = 'RegistryErrorCmp';
				$this->RegistryDataEvnField = 'CmpCallCard_id';
				$this->RegistryDoubleObject = 'RegistryCmpDouble';
				break;
			
			case 7:
			case 9:
				$this->RegistryDataObject = 'RegistryDataDisp';
				$this->RegistryErrorObject = 'RegistryErrorDisp';
				break;
			
			case 11:
			case 12:
				$this->RegistryDataObject = 'RegistryDataProf';
				$this->RegistryErrorObject = 'RegistryErrorProf';
				break;
			case 13:
				//Пока только стационар, потом сделать по типам
				$this->RegistryDataObject = 'RegistryDataEvnPS';
				break;
			
		}
	}
	
	/**
	 *	Получение списка ошибок ТФОМС
	 */
	function loadRegistryErrorTFOMS($data)
	{
		if ($data['Registry_id']<=0)
		{
			return false;
		}
		$data['RegistryType_id'] = $this->getFirstResultFromQuery("SELECT RegistryType_id FROM {$this->scheme}.v_Registry WHERE Registry_id = :Registry_id", array('Registry_id'=>$data['Registry_id']));
		
		if (!(($data['start'] >= 0) && ($data['limit'] >= 0)))
		{
			return false;
		}
		
		$this->setRegistryParamsByType($data);
		
		$params = array(
			'Registry_id' => $data['Registry_id']
		);
		$filter="(1=1)";
		if (isset($data['Person_SurName']))
		{
			$filter .= " and ps.Person_SurName like :Person_SurName ";
			$params['Person_SurName'] = $data['Person_SurName']."%";
		}
		if (isset($data['Person_FirName']))
		{
			$filter .= " and ps.Person_FirName like :Person_FirName ";
			$params['Person_FirName'] = $data['Person_FirName']."%";
		}
		if (isset($data['Person_SecName']))
		{
			$filter .= " and ps.Person_SecName like :Person_SecName ";
			$params['Person_SecName'] = $data['Person_SecName']."%";
		}
		if (isset($data['RegistryErrorType_Code']))
		{
			$filter .= " and RE.RegistryErrorType_Code = :RegistryErrorType_Code ";
			$params['RegistryErrorType_Code'] = $data['RegistryErrorType_Code'];
		}
		if (isset($data['Person_FIO']))
		{
			$filter .= " and rtrim(ps.Person_SurName) || ' ' || rtrim(ps.Person_FirName) || ' ' || rtrim(COALESCE(ps.Person_SecName, '')) like :Person_FIO ";
			$params['Person_FIO'] = $data['Person_FIO']."%";
		}
		if (isset($data['RegistryErrorClass_id']))
		{
			$filter .= " and RE.RegistryErrorClass_id = :RegistryErrorClass_id ";
			$params['RegistryErrorClass_id'] = $data['RegistryErrorClass_id'];
		}
		if (!empty($data['Evn_id']))
		{
			$filter .= " and RE.Evn_id = :Evn_id ";
			$params['Evn_id'] = $data['Evn_id'];
		}
		
		$addToSelect = "";
		$leftjoin = "";
		
		if ( !empty($data['RegistryType_id']) && in_array($data['RegistryType_id'], array(7, 9, 12)) ) {
			$leftjoin .= " left join v_EvnPLDisp epd on epd.EvnPLDisp_id = RD.Evn_rid ";
			$addToSelect .= ", epd.DispClass_id as \"DispClass_id\"";
		}
		
		if ($data['RegistryType_id'] == 6) {
			$evn_object = 'CmpCallCard';
			$evn_field = "
			null as Evn_rid,
			111 as EvnClass_id,
		";
		} else {
			$evn_object = 'Evn';
			$evn_field = "
			Evn.Evn_rid,
			Evn.EvnClass_id,
		";
		}
		
		$addToSelect .= "
		,case when Evn.{$evn_object}_updDT < RE.RegistryErrorTFOMS_insDT then 1 else 2 end as \"ErrorEdited\"
	";
		
		$diagFilter = getAccessRightsDiagFilter('D.Diag_Code');
		if (!empty($diagFilter)) {
			$leftjoin .= "left join v_Diag D on D.Diag_id = RD.Diag_id ";
			$filter .= " and {$diagFilter}";
		}
		
		$query = "
			Select
				-- select
				RegistryErrorTFOMS_id as \"RegistryErrorTFOMS_id\",
				RE.Registry_id as \"Registry_id\",
				RD.Evn_id as \"Evn_id\",
				{$evn_field}
				ret.RegistryErrorType_Code as \"RegistryErrorType_Code\",
				RegistryErrorType_Name as \"RegistryError_FieldName\",
				rtrim(COALESCE(COALESCE(ps.Person_SurName,pst.Person_SurName),'')) || ' ' || rtrim(COALESCE(COALESCE(ps.Person_FirName,pst.Person_FirName),'')) || ' ' || rtrim(COALESCE(COALESCE(ps.Person_SecName, pst.Person_SecName), '')) as \"Person_FIO\",
				COALESCE(ps.Person_id, evn.Person_id) as \"Person_id\",
				ps.PersonEvn_id as \"PersonEvn_id\",
				ps.Server_id as \"Server_id\",
				RTrim(coalesce(to_char(coalesce(ps.Person_BirthDay,pst.Person_BirthDay), 'dd.mm.yyyy'),'')) as \"Person_BirthDay\",
				RegistryErrorTFOMS_FieldName as \"RegistryErrorTFOMS_FieldName\",
				RegistryErrorTFOMS_BaseElement as \"RegistryErrorTFOMS_BaseElement\",
				RegistryErrorTFOMS_Comment as \"RegistryErrorTFOMS_Comment\",
				retl.RegistryErrorTFOMSLevel_Name as \"RegistryErrorTFOMSLevel_Name\",
				RTRIM(RD.MedPersonal_Fio) as \"MedPersonal_Fio\",
				LB.LpuBuilding_Name as \"LpuBuilding_Name\",
				LS.LpuSection_Name as \"LpuSection_Name\",
				COALESCE(RD.RegistryData_deleted, 1) as \"RegistryData_deleted\",
				case when RD.Evn_id IS NOT NULL then 1 else 2 end as \"RegistryData_notexist\"
				{$addToSelect}
				-- end select
			from
				-- from
				{$this->scheme}.v_RegistryErrorTFOMS RE
				left join {$this->scheme}.v_{$this->RegistryDataObject} RD on RD.Registry_id = RE.Registry_id and RD.Evn_id = RE.{$evn_object}_id
				left join v_{$evn_object} Evn on Evn.{$evn_object}_id = RE.{$evn_object}_id
				left join v_LpuSection LS  on LS.LpuSection_id = RD.LpuSection_id
				left join v_LpuUnit LU  on LU.LpuUnit_id = LS.LpuUnit_id
				left join v_LpuBuilding LB  on LB.LpuBuilding_id = LU.LpuBuilding_id
				left join v_RegistryErrorTFOMSLevel retl on retl.RegistryErrorTFOMSLevel_id = RE.RegistryErrorTFOMSLevel_id
				left join v_Person_all ps on ps.PersonEvn_id = RD.PersonEvn_id and ps.Server_id = RD.Server_id
				left join v_PersonState pst on Evn.Person_id = pst.Person_id
				left join {$this->scheme}.v_RegistryErrorType ret on ret.RegistryErrorType_id = RE.RegistryErrorType_id
				{$leftjoin}
				-- end from
			where
				-- where
				RE.Registry_id=:Registry_id
				and
				{$filter}
				-- end where
			order by
				-- order by
				RE.RegistryErrorType_Code
				-- end order by";
		
		//echo getDebugSql($query, $params);exit;
		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit'], '', '', '', true), $params);
		$result_count = $this->db->query(getCountSQLPH($query), $params);
		
		if (is_object($result_count))
		{
			$cnt_arr = $result_count->result('array');
			$count = $cnt_arr[0]['cnt'];
			unset($cnt_arr);
		}
		else
		{
			$count = 0;
		}
		if (is_object($result))
		{
			$response = [];
			$response['data'] = $result->result('array');
			$response['totalCount'] = $count;
			return $response;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Получение данных Дубли посещений (RegistryDouble) для поликлин. и стац. реестров
	 */
	function loadRegistryDouble($data) {
		$this->setRegistryParamsByType($data);
		
		$join = '';
		$filterList = [];
		
		if ( !empty($data['MedPersonal_id']) ) {
			$filterList[] = "MP.MedPersonal_id = :MedPersonal_id";
			$params['MedPersonal_id'] = $data['MedPersonal_id'];
		}
		
		$diagFilter = getAccessRightsDiagFilter('D.Diag_Code');
		if (!empty($diagFilter)) {
			$join .= "left join {$this->scheme}.v_{$this->RegistryDataObject} RData on RData.Registry_id = RD.Registry_id and RData.Evn_id = RD.Evn_id ";
			$join .= "left join v_Diag D on D.Diag_id = RData.Diag_id ";
			$filterList[] = $diagFilter;
		}
		switch ( $this->RegistryType_id ) {
			case 6:
				$query = "
					select
						-- select
						 RD.Registry_id as \"Registry_id\"
						,RD.Evn_id as \"Evn_id\"
						,null as \"Evn_rid\"
						,RD.Person_id as \"Person_id\"
						,rtrim(COALESCE(RD.Person_SurName,'')) || ' ' || rtrim(COALESCE(RD.Person_FirName,'')) || ' ' || rtrim(COALESCE(RD.Person_SecName, '')) as \"Person_FIO\"
						,RTrim(coalesce(to_char(RD.Person_BirthDay, 'dd.mm.yyyy'),'')) as \"Person_BirthDay\"
						,CCC.Year_num as \"Evn_Num\"
						,ETS.EmergencyTeamSpec_Name as \"LpuSection_FullName\"
						,MP.Person_Fio as \"MedPersonal_Fio\"
						,RTrim(coalesce(to_char(CCC.AcceptTime, 'dd.mm.yyyy'),'')) as \"Evn_setDate\"
						,CCC.CmpCallCard_id as \"CmpCallCard_id\"
						-- end select
					from
						-- from
						{$this->scheme}.v_{$this->RegistryDoubleObject} RD
						".$join."
						left join v_CmpCloseCard CCC on CCC.CmpCloseCard_id = RD.Evn_id
						left join v_EmergencyTeamSpec ETS on ETS.EmergencyTeamSpec_id = CCC.EmergencyTeamSpec_id
						LEFT JOIN LATERAL(
							select Person_Fio, MedPersonal_id from v_MedPersonal where MedPersonal_id = CCC.MedPersonal_id limit 1
						) as MP on true
						-- end from
					where
						-- where
						RD.Registry_id = :Registry_id
						" . (count($filterList) > 0 ? "and " . implode(" and ", $filterList) : "") . "
						-- end where
					order by
						-- order by
						RD.Person_SurName,
						RD.Person_FirName,
						RD.Person_SecName
						-- end order by
				";
				break;
			
			default:
				$query = "
					select
						-- select
						 RD.Registry_id as \"Registry_id\"
						,RD.Evn_id as \"Evn_id\"
						,RD.Evn_id as \"Evn_rid\"
						,RD.Person_id as \"Person_id\"
						,rtrim(COALESCE(RD.Person_SurName,'')) || ' ' || rtrim(COALESCE(RD.Person_FirName,'')) || ' ' || rtrim(COALESCE(RD.Person_SecName, '')) as \"Person_FIO\"
						,to_char(RD.Person_BirthDay, 'YYYY-MM-DD') as \"Person_BirthDay\"
						,COALESCE(EPL.EvnPL_NumCard, EPS.EvnPS_NumCard) as \"Evn_NumCard\"
						,R.RegistryType_id as \"RegistryType_id\"
						,LS.LpuSection_FullName as \"LpuSection_FullName\"
						,MP.Person_Fio as \"MedPersonal_Fio\"
						,RTrim(coalesce(to_char(EVPL.EvnVizitPL_setDT, 'dd.mm.yyyy'),'')) as \"Evn_setDate\"
						,RTrim(coalesce(to_char(EVPL.EvnVizitPL_setDT, 'dd.mm.yyyy'),'')) as \"Evn_disDate\"
						,null as \"CmpCallCard_id\"
						-- end select
					from
						-- from
						{$this->scheme}.v_{$this->RegistryDoubleObject} RD
						left join v_EvnPL EPL on EPL.EvnPL_id = RD.Evn_id
						left join v_EvnPS EPS on EPS.EvnPS_id = RD.Evn_id
						left join v_EvnVizitPL EVPL on EVPL.EvnVizitPL_pid = EPL.EvnPL_id and EVPL.EvnVizitPL_Index = EVPL.EvnVizitPL_Count - 1
						left join v_EvnSection ES on ES.EvnSection_pid = EPS.EvnPS_id and ES.EvnSection_Index = ES.EvnSection_Count - 1
						left join v_LpuSection LS on LS.LpuSection_id = COALESCE(EVPL.LpuSection_id, ES.LpuSection_id)
						left join {$this->scheme}.v_Registry R on R.Registry_id = RD.Registry_id
						LEFT JOIN LATERAL(
							select Person_Fio, MedPersonal_id from v_MedPersonal where MedPersonal_id = COALESCE(EVPL.MedPersonal_id, ES.MedPersonal_id) limit 1
						) as MP on true
						{$join}
						-- end from
					where
						-- where
						RD.Registry_id = :Registry_id
						" . (count($filterList) > 0 ? "and " . implode(" and ", $filterList) : "") . "
						-- end where
					order by
						-- order by
						RD.Person_SurName,
						RD.Person_FirName,
						RD.Person_SecName
						-- end order by
				";
				break;
		}
		if ( !empty($data['withoutPaging']) ) {
			$res = $this->db->query($query, $data);
			
			if ( is_object($res) ) {
				return $res->result('array');
			}
			else {
				return false;
			}
		}
		else {
			$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit'], '', '', '', true), $data);
			$result_count = $this->db->query(getCountSQLPH($query), $data);
			
			if ( is_object($result_count) ) {
				$cnt_arr = $result_count->result('array');
				$count = $cnt_arr[0]['cnt'];
				unset($cnt_arr);
			}
			else {
				$count = 0;
			}
			
			if ( is_object($result) ) {
				$response = [];
				$response['data'] = $result->result('array');
				$response['totalCount'] = $count;
				return $response;
			}
			else {
				return false;
			}
		}
	}
	
	/**
	 * Получаем состояние реестра в данный момент и тип реестра
	 */
	function GetUnionRegistryDBFExport($data) {
		if ( empty($data['Registry_id']) ) {
			return array('success' => false, 'Error_Msg' => 'Пустые значения входных параметров');
		}
		
		$this->setRegistryParamsByType($data);
		
		$query = "
			with RD (
				Evn_id,
				Evn_rid,
				RegistryData_ItogSum
			) as (
				select
					RDE.Evn_id,
					RDE.Evn_rid,
					RDE.RegistryData_ItogSum
				from
					{$this->scheme}.v_{$this->RegistryDataObject} RDE
					inner join {$this->scheme}.v_RegistryGroupLink RGL  on RGL.Registry_id = RDE.Registry_id
				where
					RGL.Registry_pid = :Registry_id
			)
	
			select
				RTrim(R.Registry_xmlExportPath) as \"Registry_xmlExportPath\",
				R.Registry_FileNum as \"Registry_FileNum\",
				R.RegistryType_id as \"RegistryType_id\",
				R.RegistryStatus_id as \"RegistryStatus_id\",
				COALESCE(R.Registry_IsNeedReform, 1) as \"Registry_IsNeedReform\",
				COALESCE(R.Registry_Sum,0) - RDSum.RegistryData_ItogSum as \"Registry_SumDifference\",
				RDSum.RegistryData_Count as \"RegistryData_Count\",
				R.Registry_endDate as \"Registry_endDate\",
				L.Lpu_RegNomN2 as \"Lpu_Code\",
				rcs.RegistryCheckStatus_Code as \"RegistryCheckStatus_Code\",
				R.KatNasel_id as \"KatNasel_id\",
				KN.KatNasel_SysNick as \"KatNasel_SysNick\",
				OS.Orgsmo_f002smocod as \"Orgsmo_f002smocod\"
			from {$this->scheme}.v_Registry R
				inner join v_Lpu L on L.Lpu_id = R.Lpu_id
				LEFT JOIN LATERAL(
					select
						COUNT(RD.Evn_id) as RegistryData_Count,
						SUM(COALESCE(RD.RegistryData_ItogSum,0)) as RegistryData_ItogSum
					from RD
				) RDSum on true
				left join v_RegistryCheckStatus rcs on rcs.RegistryCheckStatus_id = R.RegistryCheckStatus_id
				left join v_KatNasel KN  on KN.KatNasel_id = R.KatNasel_id
				left join v_OrgSMO OS on OS.OrgSMO_id = R.OrgSMO_id
			where
				Registry_id = :Registry_id
		";
		
		$result = $this->db->query($query,
			array(
				'Registry_id' => $data['Registry_id']
			)
		);
		
		if ( !is_object($result) ) {
			return array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных');
		}
		
		return $result->result('array');
	}
	
	/**
	 * Получение данных для экспорта объединенного реестра в DBF
	 */
	function getDataForDbfExport($Registry_id) {
		if ( empty($Registry_id) ) {
			return false;
		}
		
		$fn_pers = $this->scheme . ".p_registry_evnps_exppac_f";
		$fn_sl = $this->scheme . ".p_registry_evnps_expvizit_f";
		
		$fn_napr = $this->scheme  . ".p_registry_evnps_expnapr_f";
		$fn_onkousl = $this->scheme  . ".p_registry_evnps_expONKO_f";
		
		$PERS = [];
		if (!empty($fn_pers)) {
			$result = $this->db->query("
				select * from {$fn_pers} (:Registry_id)
			", array('Registry_id' => $Registry_id));
			if (!is_object($result)) {
				return false;
			}
			while ($one_rec = $result->_fetch_assoc()) {
				array_walk_recursive($one_rec, 'ConvertFromUTF8ToWin1251', true);
				$PERS[] = $one_rec;
			}
		}
		
		$SL = [];
		if (!empty($fn_sl)) {
			$result = $this->db->query("
				select * from {$fn_sl} (:Registry_id, 0)
			", array('Registry_id' => $Registry_id));
			if (!is_object($result)) {
				return false;
			}
			while ($one_rec = $result->_fetch_assoc()) {
				array_walk_recursive($one_rec, 'ConvertFromUTF8ToWin1251', true);
				$SL[] = $one_rec;
			}
		}
		
		$ONKOUSL = [];
		if (!empty($fn_onkousl)) {
			$result = $this->db->query("
				select * from {$fn_onkousl} (:Registry_id)
			", array('Registry_id' => $Registry_id));
			if (!is_object($result)) {
				return false;
			}
			while ($one_rec = $result->_fetch_assoc()) {
				array_walk_recursive($one_rec, 'ConvertFromUTF8ToWin1251', true);
				$one_rec['LEK_PR_DATA'] = [];
				$ONKOUSL[$one_rec['Evn_id']] = $one_rec;
			}
		}
		
		if (!empty($fn_napr)) {
			$result = $this->db->query("
				select * from {$fn_napr} (:Registry_id)
			", array('Registry_id' => $Registry_id));
			if (!is_object($result)) {
				return false;
			}
			while ($one_rec = $result->_fetch_assoc()) {
				array_walk_recursive($one_rec, 'ConvertFromUTF8ToWin1251', true);
				$ONKOUSL[$one_rec['Evn_id']]['NAPR_DATE'] = $one_rec['NAPR_DATE'];
				$ONKOUSL[$one_rec['Evn_id']]['NAPR_V'] = $one_rec['NAPR_V'];
				$ONKOUSL[$one_rec['Evn_id']]['MET_ISSL'] = $one_rec['MET_ISSL'];
				$ONKOUSL[$one_rec['Evn_id']]['NAPR_USL'] = $one_rec['NAPR_USL'];
			}
		}
		
		return array(
			'person' => $PERS,
			'sl' => $SL,
			'zno' => $ONKOUSL
		);
	}
	
	/**
	 *	Установка статуса экспорта реестра
	 */
	function SetExportStatus($data) {
		if ( empty($data['Registry_EvnNum']) ) {
			$data['Registry_EvnNum'] = null;
		}
		
		if ( empty($data['Registry_id']) ) {
			return array('success' => false, 'Error_Msg' => 'Пустые значения входных параметров');
		}
		
		$query = "
			update
				{$this->scheme}.Registry
			set
				Registry_xmlExportPath = :Status,
				Registry_EvnNum = :Registry_EvnNum,
				Registry_xmlExpDT = dbo.tzGetDate()
			where
				Registry_id = :Registry_id
		";
		
		$result = $this->db->query($query,
			array(
				'Registry_id' => $data['Registry_id'],
				'Registry_EvnNum' => $data['Registry_EvnNum'],
				'Status' => $data['Status']
			)
		);
		
		if ( is_object($result) ) {
			return true;
		}
		else {
			return array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных');
		}
	}
	
	/**
	 * Общее удаление записи из таблицы
	 */
	function ObjectRecordDelete($data, $object, $obj_isEvn, $id, $scheme = "dbo")
	{
		$params = [];
		if ($id <= 0) {
			return false;
		}
		$params['id'] = $id;
		$obj_isEvn = (strpos(mb_strtoupper($obj_isEvn), "TRUE") !== false) ? true : false;
		if ((strpos(mb_strtoupper($object), "EVN") !== false && $obj_isEvn) || (in_array(mb_strtolower($object), array('registry')))) {
			$fields = ",:pmUser_id";
			$params['pmUser_id'] = $data['session']['pmuser_id'];
		} else {
			$fields = "";
		}
		
		if (substr($fields, -2) == ', ') {
			$fields = substr($fields, 0, -2);
		} elseif (substr($fields, -1) == ',') {
			$fields = substr($fields, 0, -1);
		}
		$query = "
			SELECT
				p_error_code as \"Error_Code\",
				p_error_message as \"Error_Message\"
			FROM
				{$scheme}.p_{$object}_del(
					:id
				{$fields}
				) ";
		$res = $this->db->query($query, $params);
		if (is_object($res)) {
			return $res->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 * Удаление помеченных на удаление записей и пересчет реестра
	 */
	public function refreshRegistry($data) {
		$query = "
			select
				p_Error_Code as \"Error_Code\",
				p_Error_Message as \"Error_Msg\"
			from {$this->scheme}.p_RegistryData_Refresh(
				:Registry_id,
				:pmUser_id
			)
		";
		//echo getDebugSql($query, $data);exit;
		$res = $this->db->query($query, $data);
		
		if (is_object($res)) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 *	Сохранение реестра
	 */
	function saveRegistry($data)
	{
		if (0 == $data['Registry_id'])
		{
			$data['Registry_IsActive']=2;
			$proc = 'p_Registry_ins';
		}
		else
		{
			$proc = 'p_Registry_upd';
		}
		$params = array
		(
			'Registry_id' => !empty($data['Registry_id']) ? $data['Registry_id'] : null,
			'RegistryType_id' => $data['RegistryType_id'],
			'Lpu_id' => $data['Lpu_id'],
			'OrgRSchet_id' => !empty($data['OrgRSchet_id']) ? $data['OrgRSchet_id'] : null,
			'Registry_begDate' => $data['Registry_begDate'],
			'Registry_endDate' => $data['Registry_endDate'],
			'KatNasel_id' => $data['KatNasel_id'],
			'OrgSMO_id' => $data['OrgSMO_id'],
			'RegistryStacType_id' => !empty($data['RegistryStacType_id']) ? $data['RegistryStacType_id'] : null,
			'Registry_Num' => $data['Registry_Num'],
			'Registry_accDate' => $data['Registry_accDate'],
			'RegistryStatus_id' => !empty($data['RegistryStatus_id']) ? $data['RegistryStatus_id'] : null,
			'Registry_Sum' => !empty($data['Registry_Sum']) ? $data['Registry_Sum'] : null,
			'Registry_IsActive' => !empty($data['Registry_IsActive']) ? $data['Registry_IsActive'] : null,
			'Registry_ErrorCount' => !empty($data['Registry_ErrorCount']) ? $data['Registry_ErrorCount'] : null,
			'Registry_ErrorCommonCount' => !empty($data['Registry_ErrorCommonCount']) ? $data['Registry_ErrorCommonCount'] : null,
			'Registry_RecordCount' => !empty($data['Registry_RecordCount']) ? $data['Registry_RecordCount'] : null,
			'Registry_RecordPaidCount' => !empty($data['Registry_RecordPaidCount']) ? $data['Registry_RecordPaidCount'] : null,
			'Registry_kdCount' => !empty($data['Registry_kdCount']) ? $data['Registry_kdCount'] : null,
			'Registry_kdPaidCount' => !empty($data['Registry_kdPaidCount']) ? $data['Registry_kdPaidCount'] : null,
			'RegistryCheckStatus_id' => !empty($data['RegistryCheckStatus_id']) ? $data['RegistryCheckStatus_id'] : null,
			'Registry_Task' => !empty($data['Registry_Task']) ? $data['Registry_Task'] : null,
			'LpuBuilding_id' => !empty($data['LpuBuilding_id']) ? $data['LpuBuilding_id'] : null,
			'Registry_isNeedReform' => !empty($data['Registry_isNeedReform']) ? $data['Registry_isNeedReform'] : null,
			'Org_mid' => !empty($data['Org_mid']) ? $data['Org_mid'] : null,
			'OrgRSchet_mid' => !empty($data['OrgRSchet_mid']) ? $data['OrgRSchet_mid'] : null,
			'Registry_fileNum' => !empty($data['Registry_fileNum']) ? $data['Registry_fileNum'] : null,
			'PayType_id' => $data['PayType_id'],
			'Registry_IsRepeated' => !empty($data['Registry_IsRepeated']) ? $data['Registry_IsRepeated'] : null,
			'RegistryGroupType_id' => !empty($data['RegistryGroupType_id']) ? $data['RegistryGroupType_id'] : null,
			'Registry_isFlk' => !empty($data['Registry_isFlk']) ? $data['Registry_isFlk'] : null,
			'RegistryEventType_id' => !empty($data['RegistryEventType_id']) ? $data['RegistryEventType_id'] : null,
			'RegistryQueue_id' => !empty($data['RegistryQueue_id']) ? $data['RegistryQueue_id'] : null,
			'Registry_isTest' => !empty($data['Registry_isTest']) ? $data['Registry_isTest'] : null,
			'pmUser_id' => $data['pmUser_id'],
			'reform' => $data['reform']
	
		);
		
		$query = "
			select
				p_Registry_id as \"Registry_id\",
				p_Error_Code as \"Error_Code\",
				p_Error_Message as \"Error_Msg\"
			from ".$this->scheme.".".$proc. "(
				:Registry_id,
				:RegistryType_id,
				:Lpu_id,
				:OrgRSchet_id,
				:Registry_begDate,
				:Registry_endDate,
				:KatNasel_id,
				:OrgSMO_id,
				:registrystactype_id,
				:registry_num,
				:Registry_accDate,
				:RegistryStatus_id,
				:Registry_Sum,
				:Registry_IsActive,
				:Registry_ErrorCount,
				:Registry_ErrorCommonCount,
				:Registry_RecordCount,
				:Registry_RecordPaidCount,
				:Registry_kdCount,
				:Registry_kdPaidCount,
				:RegistryCheckStatus_id,
				:Registry_Task,
				:LpuBuilding_id,
				:Registry_isNeedReform,
				:Org_mid,
				:OrgrSchet_mid,
				:Registry_fileNum,
				:PayType_id,
				:Registry_isRepeated,
				:RegistryGroupType_id,
				:Registry_isFlk,
				:RegistryEventType_id,
				:RegistryQueue_id,
				:Registry_isTest,
				:pmUser_id,
				:reform
			)
		";
		
		$result = $this->db->query($query, $params);
		
		if (is_object($result))
		{
			return $result->result('array');
		}
		else
		{
			return array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных');
		}
	}
	
	/**
	 *	saveRegistryQueue
	 *	Установка реестра в очередь на формирование
	 *	Возвращает номер в очереди
	 */
	function  saveRegistryQueue($data)
	{
		if ( !in_array($data['RegistryType_id'], $this->getAllowedRegistryTypes()) )
		{
			return array(array('success' => false, 'Error_Msg' => 'Данный функционал пока не доступен!'));
		}
		
		// Сохранение нового реестра
		if (0 == $data['Registry_id'])
		{
			$data['Registry_IsActive']=2;
		}
		
		// Возвращает строку
		$checkRegistryQueueDoubles = $this->checkRegistryQueueDoubles($data);
		
		if ( !empty($checkRegistryQueueDoubles) ) {
			return array(array('success' => false, 'Error_Msg' => $checkRegistryQueueDoubles));
		}
		
		if (!empty($data['Registry_id'])) {
			$resp = $this->checkBeforeSaveRegistryQueue($data);
			if (is_array($resp)) {
				return $resp;
			}
			
			$re = $this->loadRegistryQueue($data);
			if (is_array($re) && (count($re) > 0))
			{
				if ($re[0]['RegistryQueue_Position']>0)
				{
					return array(array('success' => false, 'Error_Msg' => '<b>Запрос ЛПУ по данному реестру уже находится в очереди на формирование.</b><br/>Для запуска формирования или переформирования реестра,<br/>дождитесь окончания текущего формирования реестра.'));
				}
			}
		}
		
		$params = array
		(
			'Registry_id' => $data['Registry_id'],
			'Lpu_id' => $data['Lpu_id'],
			'RegistryType_id' => $data['RegistryType_id'],
			'RegistryStatus_id' => $data['RegistryStatus_id'],
			'RegistryStacType_id' => $data['RegistryStacType_id'],
			'Registry_begDate' => $data['Registry_begDate'],
			'Registry_endDate' => $data['Registry_endDate'],
			'Registry_Num' => $data['Registry_Num'],
			'Registry_IsActive' => $data['Registry_IsActive'],
			'OrgRSchet_id' => $data['OrgRSchet_id'],
			'PayType_id' => $data['PayType_id'],
			'OrgSMO_id' => !empty($data['OrgSMO_id']) ? $data['OrgSMO_id'] : null,
			'Registry_accDate' => $data['Registry_accDate'],
			'KatNasel_id' => (!empty($data['KatNasel_id']) ? $data['KatNasel_id'] : null),
			'LpuFilial_id' => (!empty($data['LpuFilial_id']) ? $data['LpuFilial_id'] : null),
			'LpuBuilding_id' => (!empty($data['LpuBuilding_id']) ? $data['LpuBuilding_id'] : null),
			'pmUser_id' => $data['pmUser_id']
		);
		$fields = "";
		
		switch ( $data['RegistryType_id'] ) {
			case 2:
			case 16:
				if (isset($data['reform']))
				{
					$params['reform'] = $data['reform'];
					$fields .= "reform := :reform,";
				}
				break;
		}
		
		$query = "
			select
				RegistryQueue_id as \"RegistryQueue_id\",
				RegistryQueue_Position as \"RegistryQueue_Position\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$this->scheme}.p_RegistryQueue_ins(
				RegistryStacType_id := :RegistryStacType_id,
				Registry_id := :Registry_id,
				RegistryType_id := :RegistryType_id,
				Lpu_id := :Lpu_id,
				LpuBuilding_id := :LpuBuilding_id,
				OrgRSchet_id := :OrgRSchet_id,
				PayType_id := :PayType_id,
				KatNasel_id := :KatNasel_id,
				OrgSMO_id := :OrgSMO_id,
				Registry_begDate := :Registry_begDate,
				Registry_endDate := :Registry_endDate,
				{$fields}
				Registry_Num := :Registry_Num,
				Registry_accDate := dbo.tzGetDate(),
				RegistryStatus_id := :RegistryStatus_id,
				pmUser_id := :pmUser_id
			)
		";
		
		$result = $this->db->query($query, $params);
		
		if (is_object($result))
		{
			return $result->result('array');
		}
		else
		{
			return array(array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}
	}
	
	/**
	 * Получение данных для выгрузки реестров в XML
	 */
	function loadRegistryDataForXmlUsing($type, $data, &$nznumber, &$Registry_EvnNum){
		
		$object = $this->_getRegistryObjectName($type);
		
		$fn_pers = $this->scheme . ".p_registry_{$object}_exppac_f";
		$fn_zsl = $this->scheme . ".p_registry_{$object}_expSL_f";
		$fn_sl = $this->scheme . ".p_registry_{$object}_expvizit_f";
		$fn_usl = $this->scheme . ".p_registry_{$object}_expusl_f";
		$fn_ds = $this->scheme . ".p_registry_{$object}_expds_f";
		
		
		$PERS = [];
		if (!empty($fn_pers)) {
			$result = $this->db->query("
				select * from {$fn_pers} (:Registry_id)
			", array('Registry_id' => $data['Registry_id']));
			if (!is_object($result)) {
				return false;
			}
			while ($one_rec = $result->_fetch_assoc()) {
				array_walk_recursive($one_rec, 'ConvertFromUTF8ToWin1251', true);
				$PERS[$one_rec['MaxEvn_id']] = $one_rec;
			}
		}
		
		$ZSL = [];
		if (!empty($fn_zsl)) {
			$result = $this->db->query("
				select * from {$fn_zsl} (:Registry_id)
			", array('Registry_id' => $data['Registry_id']));
			if (!is_object($result)) {
				return false;
			}
			while ($one_rec = $result->_fetch_assoc()) {
				array_walk_recursive($one_rec, 'ConvertFromUTF8ToWin1251', true);
				$ZSL[$one_rec['MaxEvn_id']] = $one_rec;
			}
		}
		
		$SL = [];
		if (!empty($fn_sl)) {
			$result = $this->db->query("
				select * from {$fn_sl} (:Registry_id, 1)
			", array('Registry_id' => $data['Registry_id']));
			if (!is_object($result)) {
				return false;
			}
			while ($one_rec = $result->_fetch_assoc()) {
				array_walk_recursive($one_rec, 'ConvertFromUTF8ToWin1251', true);
				if(!isset($SL[$one_rec['MaxEvn_id']])){
					$SL[$one_rec['MaxEvn_id']] = [];
				}
				$SL[$one_rec['MaxEvn_id']][] = $one_rec;
			}
		}
		
		$USL = [];
		if (!empty($fn_usl)) {
			$result = $this->db->query("
				select * from {$fn_usl} (:Registry_id)
			", array('Registry_id' => $data['Registry_id']));
			if (!is_object($result)) {
				return false;
			}
			while ($one_rec = $result->_fetch_assoc()) {
				array_walk_recursive($one_rec, 'ConvertFromUTF8ToWin1251', true);
				if(!isset($USL[$one_rec['Evn_id']])){
					$USL[$one_rec['Evn_id']] = [];
				}
				$USL[$one_rec['Evn_id']][] = $one_rec;
			}
		}
		
		$DS = [];
		if (!empty($fn_ds)) {
			$result = $this->db->query("
				select * from {$fn_ds} (:Registry_id)
			", array('Registry_id' => $data['Registry_id']));
			if (!is_object($result)) {
				return false;
			}
			while ($one_rec = $result->_fetch_assoc()) {
				array_walk_recursive($one_rec, 'ConvertFromUTF8ToWin1251', true);
				if(!isset($USL[$one_rec['Evn_id']])){
					$DS[$one_rec['Evn_id']] = [];
				}
				$DS[$one_rec['Evn_id']][] = $one_rec;
			}
		}
		
		$ZAP_ARRAY = [];
		foreach($ZSL as $zap){
			
			$rec = [];
			
			$rec['Patient'] = [];
			if(isset($PERS[$zap['MaxEvn_id']])){
				
				$rec['Patient'][] = array_merge($PERS[$zap['MaxEvn_id']], array(
					'Deliv_MO' => $zap['Deliv_MO'],
					'Deliv_Ext' => $zap['Deliv_Ext'],
					'Work_Abil' => $zap['Work_Abil'],
					'Inf_Consent_Data' => $zap['Inf_Consent_Data'],
					'Inf_Cons_Med_Int' => $zap['Inf_Cons_Med_Int'],
					'Refusal' => $zap['Refusal'],
					'Anest_Cons' => $zap['Anest_Cons']
				));
			}
			
			$zap['Diagnosis'] = [];
			if(isset($DS[$zap['Evn_id']]) && $DS[$zap['Evn_id']][0]['diag_priem'] == 2){
				$zap['Diagnosis'] = $DS[$zap['Evn_id']];
			}
			
			$zap['Phys_Spec'] = [];
			$zap['Anamnesis'] = [];
			$zap['Phys_Rec'] = [];
			$zap['Surgery'] = [];
			$zap['Epicrisis'] = [];
			$zap['Drugs_Presc'] = [];
			$zap['Recp_Serv'] = [];
			$zap['Depart_Mov'] = [];
			if(isset($SL[$zap['MaxEvn_id']])){
				foreach($SL[$zap['MaxEvn_id']] as $key => $sluch){
					
					$sluch['Phys_Spec'] = [];
					$sluch['Diagnosis'] = [];
					if(isset($DS[$sluch['Evn_id']]) && $DS[$sluch['Evn_id']][0]['diag_priem'] == 1){
						$sluch['Diagnosis'] = $DS[$sluch['Evn_id']];
					}
					
					if(isset($USL[$sluch['Evn_id']])){
						$zap['Surgery'] = $USL[$sluch['Evn_id']];
						$zap['Recp_Serv'] = $USL[$sluch['Evn_id']];
					}
					
					$zap['Depart_Mov'][] = $sluch;
				}
			
			}
			
			$rec['Medical_Record'][] = $zap;
			
			$rec['Ins_Case_ID'] = array([
				'Rep_Per_ID' => $zap['Rep_Per_ID'],
				'MO_ID' => $zap['MO_ID'],
				'Ins_Comp_ID' => $zap['Ins_Comp_ID'],
				'Pat_UID' => $zap['Pat_UID'],
				'Med_Card_Num' => $zap['Med_Card_Num'],
				'Ins_Case_Date_Beg' => $zap['Ins_Case_Date_Beg'],
				'Ins_Case_Date_End' => $zap['Ins_Case_Date_End'],
				'Med_Care_Type' => $zap['Med_Care_Type'],
				'Med_Care_Cond' => $zap['Med_Care_Cond']
			]);
			
			if(!isset($ZAP_ARRAY[$zap['Ins_Comp_ID']])){
				$ZAP_ARRAY[$zap['Ins_Comp_ID']] = [];
			}
			$nznumber++;
			$rec['N_ZAP'] = $nznumber;
			$ZAP_ARRAY[$zap['Ins_Comp_ID']][] = $rec;
			
			$Registry_EvnNum[$zap['Evn_id']] = array(
				'Evn_id' => $zap['Evn_id'],
				'N_ZAP' => $nznumber
			);
			
		}
		
		return $ZAP_ARRAY;
	}
	
	/**
	 * Возвращает наименование объекта для хранимых процедур в зависимости от типа реестра
	 */
	private function _getRegistryObjectName($type) {
		$result = '';
		
		if ( array_key_exists($type, $this->_registryTypeList) ) {
			$result = $this->_registryTypeList[$type]['SP_Object'];
		}
		
		return $result;
	}
}