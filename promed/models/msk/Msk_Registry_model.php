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
require_once(APPPATH.'models/Registry_model.php');

class Msk_Registry_model extends Registry_model
{
	public $scheme = "r50";
	
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
		return array(
			array('RegistryType_id' => 1, 'RegistryType_Name' => 'Стационар'),
			array('RegistryType_id' => 2, 'RegistryType_Name' => 'Поликлиника'),
			array('RegistryType_id' => 6, 'RegistryType_Name' => 'Скорая помощь'),
			array('RegistryType_id' => 7, 'RegistryType_Name' => 'Дисп-ция взр. населения'),
			array('RegistryType_id' => 9, 'RegistryType_Name' => 'Дисп-ция детей-сирот'),
			array('RegistryType_id' => 11, 'RegistryType_Name' => 'Проф.осмотры взр. населения'),
			array('RegistryType_id' => 12, 'RegistryType_Name' => 'Медосмотры несовершеннолетних')
		);
		
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
			array('RegistryStatus_id' => 6, 'RegistryStatus_Name' => 'Удаленные')
		);
	}
	
	/**
	 * Удаление объединённого реестра
	 */
	function deleteUnionRegistry($data)
	{
		// 1. удаляем все связи
		$query = "
			delete {$this->scheme}.RegistryGroupLink with (ROWLOCK)
			where Registry_pid = :Registry_id
		";
		$this->db->query($query, array(
			'Registry_id' => $data['id']
		));
		
		// 2. удаляем сам реестр
		$query = "
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec {$this->scheme}.p_Registry_del
				@Registry_id = :Registry_id,
				@pmUser_delID = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";
		
		$result = $this->db->query($query, array(
			'Registry_id' => $data['id']
		,'pmUser_id' => $data['pmUser_id']
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
			select top 1
				Registry_id
			from
				{$this->scheme}.v_Registry (nolock)
			where
				RegistryType_id = 13
				and Lpu_id = :Lpu_id
				and Registry_Num = :Registry_Num
				and year(Registry_accDate) = year(:Registry_accDate)
				and (Registry_id <> :Registry_id OR :Registry_id IS NULL)
		";
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			$resp = $result->result('array');
			if (!empty($resp[0]['Registry_id'])) {
				return array('Error_Msg' => 'Номер счета не должен повторяться в году');
			}
		}
		
		$registryFileNumCheck = $this->getFirstResultFromQuery("
			select top 1 Registry_id
			from {$this->scheme}.v_Registry (nolock)
			where
				RegistryType_id = 13
				and Lpu_id = :Lpu_id
				and KatNasel_id = :KatNasel_id
				and OrgSMO_id = :OrgSMO_id
				and year(Registry_endDate) = year(:Registry_endDate)
				and month(Registry_endDate) = month(:Registry_endDate)
				and Registry_id != ISNULL(:Registry_id, 0)
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
			declare
				@Error_Code bigint,
				@KatNasel_Code bigint = (select top 1 KatNasel_Code from v_KatNasel (nolock) where KatNasel_id = :KatNasel_id),
				@Error_Message varchar(4000),
				@Registry_id bigint = :Registry_id,
				@curdate datetime = dbo.tzGetDate();
			exec {$this->scheme}.{$proc}
				@Registry_id = @Registry_id output,
				@RegistryType_id = 13,
				@RegistryStatus_id = 1,
				@Registry_Sum = NULL,
				@Registry_IsActive = 2,
				@Registry_Num = :Registry_Num,
				@Registry_accDate = :Registry_accDate,
				@Registry_begDate = :Registry_begDate,
				@Registry_endDate = :Registry_endDate,
				@KatNasel_id = :KatNasel_id,
				@OrgSMO_id = :OrgSMO_id,
				@Lpu_id = :Lpu_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @Registry_id as Registry_id, @KatNasel_Code as KatNasel_Code, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";
		
		$result = $this->db->query($query, $data);
		
		if (is_object($result))
		{
			$resp = $result->result('array');
			if (count($resp) > 0 && !empty($resp[0]['Registry_id'])) {
				// 2. удаляем все связи
				$query = "
					delete {$this->scheme}.RegistryGroupLink with (ROWLOCK)
					where Registry_pid = :Registry_id
				";
				$this->db->query($query, array(
					'Registry_id' => $resp[0]['Registry_id']
				));
				
				$filterList = array();
				if ($resp[0]['KatNasel_Code'] == 3) {
					$filterList[] = "R.RegistryType_id <> 6";
					$filterList[] = "kn.KatNasel_Code = 1";
					$filterList[] = "R.RegistryType_id in (1, 2, 7, 5, 11, 12)";
				} else {
					$filterList[] = "ISNULL(R.OrgSMO_id, 1) = :OrgSMO_id";
					$filterList[] = "R.KatNasel_id = :KatNasel_id";
				}
				
				// 3. выполняем поиск реестров которые войдут в объединённый
				$query = "
					select
						R.Registry_id,
						ISNULL(R.Registry_Sum, 0) as Registry_Sum
					from
						{$this->scheme}.v_Registry R (nolock)
						left join v_KatNasel kn (nolock) on kn.KatNasel_id = R.KatNasel_id
						left join v_PayType PT (nolock) on PT.PayType_id = R.PayType_id
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
							declare
								@Error_Code bigint,
								@Error_Message varchar(4000),
								@RegistryGroupLink_id bigint = null;
							exec {$this->scheme}.p_RegistryGroupLink_ins
								@RegistryGroupLink_id = @RegistryGroupLink_id output,
								@Registry_pid = :Registry_pid,
								@Registry_id = :Registry_id,
								@pmUser_id = :pmUser_id,
								@Error_Code = @Error_Code output,
								@Error_Message = @Error_Message output;
							select @RegistryGroupLink_id as RegistryGroupLink_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
						";
						
						$this->db->query($query, array(
							'Registry_pid' => $resp[0]['Registry_id'],
							'Registry_id' => $one_reg['Registry_id'],
							'pmUser_id' => $data['pmUser_id']
						));
					}
					
					$query = "
						declare
							@Error_Code bigint,
							@Error_Message varchar(4000) = '';

						set nocount on;

						begin try
							update {$this->scheme}.Registry with (updlock)
							set Registry_Sum = :Registry_Sum
							where Registry_id = :Registry_id
						end try

						begin catch
							set @Error_Code = error_number()
							set @Error_Message = error_message()
						end catch

						set nocount off;

						select @Error_Code as Error_Code, @Error_Message as Error_Msg
					";
					
					$result = $this->db->query($query, array(
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
				R.Registry_id,
				R.Registry_Num,
				R.Registry_FileNum,
				R.Registry_xmlExportPath,
				convert(varchar(10), R.Registry_accDate, 104) as Registry_accDate,
				convert(varchar(10), R.Registry_begDate, 104) as Registry_begDate,
				convert(varchar(10), R.Registry_endDate, 104) as Registry_endDate,
				R.KatNasel_id,
				KN.KatNasel_Name,
				KN.KatNasel_SysNick,
				ISNULL(RS.Registry_SumPaid, 0.00) as Registry_SumPaid,
				OS.OrgSMO_Nick
				-- end select
			from
				-- from
				{$this->scheme}.v_Registry R (nolock) -- объединённый реестр
				left join v_KatNasel KN (nolock) on KN.KatNasel_id = R.KatNasel_id
				left join v_OrgSMO OS with (nolock) on OS.OrgSMO_id = R.OrgSMO_id
				outer apply(
					select
						SUM(ISNULL(R2.Registry_SumPaid,0)) as Registry_SumPaid
					from {$this->scheme}.v_Registry R2 (nolock)
						inner join {$this->scheme}.v_RegistryGroupLink RGL (nolock) on R2.Registry_id = RGL.Registry_id
					where
						RGL.Registry_pid = R.Registry_id
				) RS
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
		/*
		echo getDebugSql($query, $data);
		exit;
		*/
		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $data);
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
			$response = array();
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
				R.Registry_id,
				R.Registry_Num,
				convert(varchar(10), R.Registry_accDate, 104) as Registry_accDate,
				convert(varchar(10), R.Registry_begDate, 104) as Registry_begDate,
				convert(varchar(10), R.Registry_endDate, 104) as Registry_endDate,
				R.KatNasel_id,
				R.OrgSMO_id,
				R.Lpu_id
			from
				{$this->scheme}.v_Registry R (nolock)
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
			R.Registry_id,
			R.Registry_Num,
			convert(varchar,R.Registry_accDate,104) as Registry_accDate,
			convert(varchar,R.Registry_begDate,104) as Registry_begDate,
			convert(varchar,R.Registry_endDate,104) as Registry_endDate,
			KN.KatNasel_Name,
			RT.RegistryType_Name,
			ISNULL(R.Registry_Sum, 0.00) as Registry_Sum,
			ISNULL(R.Registry_SumPaid, 0.00) as Registry_SumPaid,
			PT.PayType_Name,
			LB.LpuBuilding_Name,
			convert(varchar,R.Registry_updDT,104) as Registry_updDate
			-- end select
		from
			-- from
			{$this->scheme}.v_RegistryGroupLink RGL (nolock)
			inner join {$this->scheme}.v_Registry R (nolock) on R.Registry_id = RGL.Registry_id -- обычный реестр
			left join v_KatNasel KN (nolock) on KN.KatNasel_id = R.KatNasel_id
			left join v_RegistryType RT (nolock) on RT.RegistryType_id = R.RegistryType_id
			left join v_PayType PT (nolock) on PT.PayType_id = R.PayType_id
			left join v_LpuBuilding LB (nolock) on LB.LpuBuilding_id = R.LpuBuilding_id
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
		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $data);
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
			$response = array();
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
					R.RegistryQueue_id as Registry_id,
					R.Lpu_id,
					R.RegistryType_id,
					R.PayType_id,
					PT.PayType_Name,
					'' as Diag_Code,
					5 as RegistryStatus_id,
					R.RegistryStacType_id,
					R.RegistryEventType_id,
					2 as Registry_IsActive,
					1 as Registry_IsProgress,
					1 as Registry_IsNeedReform,
					RTrim(R.Registry_Num) + ' / в очереди: ' + LTrim(cast(RegistryQueue_Position as varchar)) as Registry_Num,
					null as ReformTime,
					convert(varchar(10), R.Registry_accDate, 104) as Registry_accDate,
					convert(varchar(10), R.Registry_begDate, 104) as Registry_begDate,
					convert(varchar(10), R.Registry_endDate, 104) as Registry_endDate,
					0 as Registry_Count,
					0 as Registry_RecordPaidCount,
					0 as Registry_KdCount,
					0 as Registry_KdPaidCount,
					0 as Registry_Sum,
					0 as Registry_SumPaid,
					LpuBuilding.LpuBuilding_Name,
					RTrim(RegistryStacType.RegistryStacType_Name) as RegistryStacType_Name,
					RTrim(RegistryEventType.RegistryEventType_Name) as RegistryEventType_Name,
					R.Registry_IsRepeated,
					case when R.Registry_IsFLK = 2 then 'true' else 'false' end as Registry_IsFLK,
					'' as Registry_updDate,
					'' as Mes_Code_KSG,
					-- количество записей в таблицах RegistryErrorCom, RegistryError, RegistryPerson, RegistryNoPolis, RegistryNoPay
					0 as RegistryErrorCom_IsData,
					0 as RegistryError_IsData,
					0 as RegistryPerson_IsData,
					0 as RegistryNoPolis_IsData,
					0 as RegistryNoPay_IsData,
					0 as RegistryErrorTFOMS_IsData,
					0 as RegistryNoPaid_Count,
					0 as RegistryNoPay_UKLSum,
					-1 as RegistryCheckStatus_Code,
					0 as RegistryErrorTFOMSType_id
					--'' as RegistryCheckStatus_Name
				from {$this->scheme}.v_RegistryQueue R with (NOLOCK)
					left join v_PayType PT (nolock) on PT.PayType_id = R.PayType_id
					left join v_LpuBuilding LpuBuilding with (nolock) on LpuBuilding.LpuBuilding_id = R.LpuBuilding_id
					left join v_RegistryStacType RegistryStacType with (nolock) on RegistryStacType.RegistryStacType_id = R.RegistryStacType_id
					left join {$this->scheme}.v_RegistryEventType RegistryEventType with (nolock) on RegistryEventType.RegistryEventType_id = R.RegistryEventType_id
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
						$filter .= ' and convert(varchar(4), cast(R.Registry_begDate as date), 112) <= :Registry_accYear';
						$filter .= ' and convert(varchar(4), cast(R.Registry_endDate as date), 112) >= :Registry_accYear';
						$params['Registry_accYear'] = $data['Registry_accYear'];
					}
				}
			}
			
			$query = "
				Select
					R.Registry_id,
					R.Lpu_id,
					R.RegistryType_id,
					R.PayType_id,
					PT.PayType_Name,
					" . (!empty($data['RegistryStatus_id']) && 6 == (int)$data['RegistryStatus_id'] ? "6 as RegistryStatus_id" : "R.RegistryStatus_id") . ",
					R.OrgRSchet_id,
					R.KatNasel_id,
					KN.KatNasel_Name,
					KN.KatNasel_SysNick,
					OS.OrgSMO_Nick,
					R.RegistryStacType_id,
					R.RegistryEventType_id,
					R.Registry_IsActive,
					case when RQ.RegistryQueue_id is not null then 1 else 0 end as Registry_IsProgress,
					isnull(R.Registry_IsNeedReform, 1) as Registry_IsNeedReform,
					RTrim(R.Registry_Num) as Registry_Num,
					convert(varchar, RQH.RegistryQueueHistory_endDT, 104) + ' ' + convert(varchar, RQH.RegistryQueueHistory_endDT, 108) as ReformTime,
					convert(varchar(10), R.Registry_accDate, 104) as Registry_accDate,
					convert(varchar(10), R.Registry_begDate, 104) as Registry_begDate,
					convert(varchar(10), R.Registry_endDate, 104) as Registry_endDate,
					isnull(R.Registry_RecordCount, 0) as Registry_Count,
					isnull(R.Registry_RecordPaidCount, 0) as Registry_RecordPaidCount,
					isnull(R.Registry_KdCount, 0) as Registry_KdCount,
					isnull(R.Registry_KdPaidCount, 0) as Registry_KdPaidCount,
					isnull(R.Registry_Sum, 0.00) as Registry_Sum,
					0 as Registry_SumPaid,
					LpuBuilding.LpuBuilding_Name,
					R.Registry_IsRepeated,
					case when R.Registry_IsFLK = 2 then 'true' else 'false' end as Registry_IsFLK,
					RTrim(RegistryStacType.RegistryStacType_Name) as RegistryStacType_Name,
					RTrim(RegistryEventType.RegistryEventType_Name) as RegistryEventType_Name,
					RTrim(IsNull(convert(varchar,cast(R.Registry_updDT as datetime),104),''))+' '+
						RTrim(IsNull(convert(varchar,cast(R.Registry_updDT as datetime),108),'')) as Registry_updDate,
					-- количество записей в таблицах RegistryErrorCom, RegistryError, RegistryPerson, RegistryNoPolis, RegistryNoPay, RegistryDouble
					RegistryErrorCom.RegistryErrorCom_IsData,
					RegistryError.RegistryError_IsData,
					RegistryPerson.RegistryPerson_IsData,
					RegistryNoPolis.RegistryNoPolis_IsData,
					RegistryDouble.RegistryDouble_IsData,
					case when RegistryNoPaid_Count>0 then 1 else 0 end as RegistryNoPaid_IsData,
					RegistryErrorTFOMS.RegistryErrorTFOMS_IsData,
					RegistryNoPaid.RegistryNoPaid_Count as RegistryNoPaid_Count,
					0 as RegistryNoPay_UKLSum,
					ISNULL(RegistryCheckStatus.RegistryCheckStatus_Code, -1) as RegistryCheckStatus_Code,
					RegistryErrorTFOMS.RegistryErrorTFOMSType_id
					--RegistryCheckStatus.RegistryCheckStatus_Name
				from {$this->scheme}.{$source_table} R with (NOLOCK)
					left join v_PayType PT (nolock) on PT.PayType_id = R.PayType_id
					left join v_RegistryStacType RegistryStacType with (NOLOCK) on RegistryStacType.RegistryStacType_id = R.RegistryStacType_id
					left join {$this->scheme}.v_RegistryEventType RegistryEventType with (NOLOCK) on RegistryEventType.RegistryEventType_id = R.RegistryEventType_id
					left join v_LpuBuilding LpuBuilding with (nolock)on LpuBuilding.LpuBuilding_id = R.LpuBuilding_id
					left join v_RegistryCheckStatus RegistryCheckStatus with (NOLOCK) on RegistryCheckStatus.RegistryCheckStatus_id = R.RegistryCheckStatus_id
					left join v_KatNasel KN (nolock) on KN.KatNasel_id = R.KatNasel_id
					left join v_OrgSMO OS with (nolock) on OS.OrgSMO_id = R.OrgSMO_id
					outer apply(
						select top 1 RegistryQueue_id
						from {$this->scheme}.v_RegistryQueue with (NOLOCK)
						where Registry_id = R.Registry_id
					) RQ
					outer apply(
						select top 1 RegistryQueueHistory_endDT
						from {$this->scheme}.RegistryQueueHistory with (NOLOCK)
						where Registry_id = R.Registry_id
							and RegistryQueueHistory_endDT is not null
						order by RegistryQueueHistory_id desc
					) RQH
					outer apply(select top 1 case when RE.Registry_id is not null then 1 else 0 end as RegistryErrorCom_IsData from {$this->scheme}.v_{$this->RegistryErrorComObject} RE with (NOLOCK) where RE.Registry_id = R.Registry_id) RegistryErrorCom
					outer apply(select top 1 case when RE.Registry_id is not null then 1 else 0 end as RegistryError_IsData from {$this->scheme}.v_{$this->RegistryErrorObject} RE with (NOLOCK) where RE.Registry_id = R.Registry_id) RegistryError
					outer apply(select top 1 case when RE.Registry_id is not null then 1 else 0 end as RegistryPerson_IsData from {$this->scheme}.v_{$this->RegistryPersonObject} RE with (NOLOCK) where RE.Registry_id = R.Registry_id and ISNULL(RE.{$this->RegistryPersonObject}_IsDifferent, 1) = 1) RegistryPerson
					outer apply(select top 1 case when RE.Registry_id is not null then 1 else 0 end as RegistryNoPolis_IsData from {$this->scheme}.v_RegistryNoPolis RE with (NOLOCK) where RE.Registry_id = R.Registry_id) RegistryNoPolis
					outer apply(select top 1 case when RE.Registry_id is not null then 1 else 0 end as RegistryErrorTFOMS_IsData, RegistryErrorTFOMSType_id from {$this->scheme}.v_RegistryErrorTFOMS RE with (NOLOCK) where RE.Registry_id = R.Registry_id) RegistryErrorTFOMS
					outer apply(select top 1 case when RE.Registry_id is not null then 1 else 0 end as RegistryDouble_IsData from {$this->scheme}.v_{$this->RegistryDoubleObject} RE with (NOLOCK) where RE.Registry_id = R.Registry_id) RegistryDouble
					outer apply(
						select
							count(RDnoPaid.Evn_id) as RegistryNoPaid_Count
						from {$this->scheme}.v_{$this->RegistryDataObject} RDnoPaid with (NOLOCK)
						where RDnoPaid.Registry_id = R.Registry_id and ISNULL(RDnoPaid.RegistryData_isPaid, 1) = 1
					) RegistryNoPaid
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
				$filter .= " and ISNULL(RD.RegistryData_IsPaid,1) = 2";
			} elseif ($data['filterRecords'] == 3) {
				$filter .= " and ISNULL(RD.RegistryData_IsPaid,1) = 1";
			}
		}
		
		$join = "";
		$fields = "";
		
		if ( in_array($data['RegistryType_id'], array(7, 9, 12)) ) {
			$join .= "left join v_EvnPLDisp epd with (nolock) on epd.EvnPLDisp_id = RD.Evn_rid ";
			$fields .= "epd.DispClass_id, ";
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
					from v_PersonEvn t1 with (nolock)
						inner join {$this->scheme}.v_{$this->RegistryDataObject} t2 with (nolock) on t2.Person_id = t1.Person_id
					where t2.Registry_id = :Registry_id
				)
				-- end addit with

				Select
					-- select
					RD.Evn_id,
					RD.Evn_rid,
					RD.EvnClass_id,
					RD.Registry_id,
					RD.RegistryType_id,
					RD.RegistryData_RowNum,
					RD.Person_id,
					RD.Server_id,
					PersonEvn.PersonEvn_id,
					{$fields}
					case when RDL.Person_id is null then 0 else 1 end as IsRDL,
					RD.needReform, RD.checkReform, RD.timeReform,
					case when RD.needReform=2 and RegistryQueue.RegistryQueue_id is not null then 2 else 1 end isNoEdit,
					RD.RegistryData_deleted,
					RTrim(RD.NumCard) as EvnPL_NumCard,
					RTrim(RD.Person_FIO) as Person_FIO,
					RTrim(IsNull(convert(varchar,cast(RD.Person_BirthDay as datetime),104),'')) as Person_BirthDay,
					CASE WHEN RD.Person_IsBDZ = 1 THEN 'true' ELSE 'false' END as Person_IsBDZ,
					RD.LpuSection_id,
					RTrim(RD.LpuSection_Name) as LpuSection_Name,
					RTrim(RD.MedPersonal_Fio) as MedPersonal_Fio,
					RTrim(IsNull(convert(varchar,cast(RD.Evn_setDate as datetime),104),'')) as EvnVizitPL_setDate,
					RTrim(IsNull(convert(varchar,cast(RD.Evn_disDate as datetime),104),'')) as Evn_disDate,
					RD.RegistryData_Tariff RegistryData_Tariff,
					RD.RegistryData_KdFact as RegistryData_Uet,
					RD.RegistryData_KdPay as RegistryData_KdPay,
					RD.RegistryData_KdPlan as RegistryData_KdPlan,
					RD.RegistryData_ItogSum as RegistryData_ItogSum,
					ISNULL(RegistryError.Err_Count, 0) + ISNULL(RegistryErrorTFOMS.Err_Count, 0) as Err_Count,
					PMT.PayMedType_Code,
					D.Diag_Code,
					MOLD.Mes_Code + ISNULL(' ' + MOLD.MesOld_Num, '') as Mes_Code
					-- end select
				from
					-- from
					{$this->scheme}.{$source_table} RD with (NOLOCK)
					{$join}
					left join v_PayMedType PMT (nolock) on PMT.PayMedType_id = RD.PayMedType_id
					left join {$this->scheme}.RegistryQueue with (nolock) on RegistryQueue.Registry_id = RD.Registry_id
					left join v_Diag D with(nolock) on D.Diag_id = RD.Diag_id
					left join v_MesOld MOLD (nolock) on MOLD.Mes_id = RD.Mes_id
					outer apply (
						select top 1 RDLT.Person_id from {$this->scheme}.RegistryDataLgot RDLT with (NOLOCK) where RD.Person_id = RDLT.Person_id and (RD.Evn_id = RDLT.Evn_id or RDLT.Evn_id is null)
					) RDL
					outer apply (
						Select count(*) as Err_Count
						from {$this->scheme}.v_{$this->RegistryErrorObject} RE with (NOLOCK)
						where RD.Evn_id = RE.Evn_id
							and RD.Registry_id = RE.Registry_id
					) RegistryError
					outer apply (
						Select count(*) as Err_Count
						from {$this->scheme}.v_RegistryErrorTFOMS RET with (NOLOCK)
						where RD.Evn_id = RET.Evn_id
							and RD.Registry_id = RET.Registry_id
					) RegistryErrorTFOMS
					outer apply (
						select top 1 PersonEvn_id
						from PE with (NOLOCK)
						where Person_id = RD.Person_id
							and PersonEvn_insDT <= isnull(RD.Evn_disDate, RD.Evn_setDate)
						order by PersonEvn_insDT desc
					) PersonEvn
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
		
		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
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
			$response = array();
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
	function setRegistryParamsByType($data = array(), $force = false) {
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
			$filter .= " and rtrim(ps.Person_SurName) + ' ' + rtrim(ps.Person_FirName) + ' ' + rtrim(isnull(ps.Person_SecName, '')) like :Person_FIO ";
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
			$leftjoin .= " left join v_EvnPLDisp epd with (nolock) on epd.EvnPLDisp_id = RD.Evn_rid ";
			$addToSelect .= ", epd.DispClass_id";
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
			,case when Evn.{$evn_object}_updDT < RE.RegistryErrorTFOMS_insDT then 1 else 2 end as ErrorEdited
		";
		
		$diagFilter = getAccessRightsDiagFilter('D.Diag_Code');
		if (!empty($diagFilter)) {
			$leftjoin .= "left join v_Diag D with (nolock) on D.Diag_id = RD.Diag_id ";
			$filter .= " and {$diagFilter}";
		}
		
		$query = "
		Select
			-- select
			RegistryErrorTFOMS_id,
			RE.Registry_id,
			RD.Evn_id,
			{$evn_field}
			ret.RegistryErrorType_Code,
			RegistryErrorType_Name as RegistryError_FieldName,
			rtrim(isnull(isnull(ps.Person_SurName,pst.Person_SurName),'')) + ' ' + rtrim(isnull(isnull(ps.Person_FirName,pst.Person_FirName),'')) + ' ' + rtrim(isnull(isnull(ps.Person_SecName, pst.Person_SecName), '')) as Person_FIO,
			ISNULL(ps.Person_id, evn.Person_id) as Person_id,
			ps.PersonEvn_id,
			ps.Server_id,
			RTrim(IsNull(convert(varchar,cast(isnull(ps.Person_BirthDay, pst.Person_BirthDay) as datetime),104),'')) as Person_BirthDay,
			RegistryErrorTFOMS_FieldName,
			RegistryErrorTFOMS_BaseElement,
			RegistryErrorTFOMS_Comment,
			retl.RegistryErrorTFOMSLevel_Name,
			RTRIM(RD.MedPersonal_Fio) as MedPersonal_Fio,
			LB.LpuBuilding_Name,
			LS.LpuSection_Name,
			ISNULL(RD.RegistryData_deleted, 1) as RegistryData_deleted,
			case when RD.Evn_id IS NOT NULL then 1 else 2 end as RegistryData_notexist
			{$addToSelect}
			-- end select
		from
			-- from
			{$this->scheme}.v_RegistryErrorTFOMS RE with (nolock)
			left join {$this->scheme}.v_{$this->RegistryDataObject} RD with (nolock) on RD.Registry_id = RE.Registry_id and RD.Evn_id = RE.{$evn_object}_id
			left join v_{$evn_object} Evn with (nolock) on Evn.{$evn_object}_id = RE.{$evn_object}_id
			left join v_LpuSection LS (nolock) on LS.LpuSection_id = RD.LpuSection_id
			left join v_LpuUnit LU (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
			left join v_LpuBuilding LB (nolock) on LB.LpuBuilding_id = LU.LpuBuilding_id
			left join v_RegistryErrorTFOMSLevel retl with (nolock) on retl.RegistryErrorTFOMSLevel_id = RE.RegistryErrorTFOMSLevel_id
			left join v_Person_all ps with (nolock) on ps.PersonEvn_id = RD.PersonEvn_id and ps.Server_id = RD.Server_id
			left join v_PersonState pst with (nolock) on Evn.Person_id = pst.Person_id
			left join {$this->scheme}.v_RegistryErrorType ret with (nolock) on ret.RegistryErrorType_id = RE.RegistryErrorType_id
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
		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
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
			$response = array();
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
		$filterList = array();
		
		if ( !empty($data['MedPersonal_id']) ) {
			$filterList[] = "MP.MedPersonal_id = :MedPersonal_id";
			$params['MedPersonal_id'] = $data['MedPersonal_id'];
		}
		
		$diagFilter = getAccessRightsDiagFilter('D.Diag_Code');
		if (!empty($diagFilter)) {
			$join .= "left join {$this->scheme}.v_{$this->RegistryDataObject} RData with(nolock) on RData.Registry_id = RD.Registry_id and RData.Evn_id = RD.Evn_id ";
			$join .= "left join v_Diag D with (nolock) on D.Diag_id = RData.Diag_id ";
			$filterList[] = $diagFilter;
		}
		switch ( $this->RegistryType_id ) {
			case 6:
				$query = "
					select
						-- select
						 RD.Registry_id
						,RD.Evn_id
						,null as Evn_rid
						,RD.Person_id
						,rtrim(IsNull(RD.Person_SurName,'')) + ' ' + rtrim(IsNull(RD.Person_FirName,'')) + ' ' + rtrim(isnull(RD.Person_SecName, '')) as Person_FIO
						,RTrim(IsNull(convert(varchar,cast(RD.Person_BirthDay as datetime),104),'')) as Person_BirthDay
						,CCC.Year_num as Evn_Num
						,ETS.EmergencyTeamSpec_Name as LpuSection_FullName
						,MP.Person_Fio as MedPersonal_Fio
						,convert(varchar(10), CCC.AcceptTime, 104) as Evn_setDate
						,CCC.CmpCallCard_id
						-- end select
					from
						-- from
						{$this->scheme}.v_{$this->RegistryDoubleObject} RD with (NOLOCK)
						".$join."
						left join v_CmpCloseCard CCC with (nolock) on CCC.CmpCloseCard_id = RD.Evn_id
						left join v_EmergencyTeamSpec ETS with (nolock) on ETS.EmergencyTeamSpec_id = CCC.EmergencyTeamSpec_id
						outer apply(
							select top 1 Person_Fio, MedPersonal_id from v_MedPersonal with(nolock) where MedPersonal_id = CCC.MedPersonal_id
						) as MP
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
						 RD.Registry_id
						,RD.Evn_id
						,RD.Evn_id as Evn_rid
						,RD.Person_id
						,rtrim(IsNull(RD.Person_SurName,'')) + ' ' + rtrim(IsNull(RD.Person_FirName,'')) + ' ' + rtrim(isnull(RD.Person_SecName, '')) as Person_FIO
						,convert(varchar(10), RD.Person_BirthDay, 104) as Person_BirthDay
						,ISNULL(EPL.EvnPL_NumCard, EPS.EvnPS_NumCard) as Evn_NumCard
						,R.RegistryType_id
						,LS.LpuSection_FullName
						,MP.Person_Fio as MedPersonal_Fio
						,convert(varchar(10), ISNULL(EVPL.EvnVizitPL_setDT, EPS.EvnPS_setDT), 104) as Evn_setDate
						,convert(varchar(10), ISNULL(EVPL.EvnVizitPL_setDT, EPS.EvnPS_disDT), 104) as Evn_disDate
						,null as CmpCallCard_id
						-- end select
					from
						-- from
						{$this->scheme}.v_{$this->RegistryDoubleObject} RD with (NOLOCK)
						left join v_EvnPL EPL with (nolock) on EPL.EvnPL_id = RD.Evn_id
						left join v_EvnPS EPS with (nolock) on EPS.EvnPS_id = RD.Evn_id
						left join v_EvnVizitPL EVPL with (nolock) on EVPL.EvnVizitPL_pid = EPL.EvnPL_id and EVPL.EvnVizitPL_Index = EVPL.EvnVizitPL_Count - 1
						left join v_EvnSection ES with (nolock) on ES.EvnSection_pid = EPS.EvnPS_id and ES.EvnSection_Index = ES.EvnSection_Count - 1
						left join v_LpuSection LS with (nolock) on LS.LpuSection_id = ISNULL(EVPL.LpuSection_id, ES.LpuSection_id)
						left join {$this->scheme}.v_Registry R with (nolock) on R.Registry_id = RD.Registry_id
						outer apply(
							select top 1 Person_Fio, MedPersonal_id from v_MedPersonal with (nolock) where MedPersonal_id = ISNULL(EVPL.MedPersonal_id, ES.MedPersonal_id)
						) as MP
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
			$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $data);
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
				$response = array();
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
					{$this->scheme}.v_{$this->RegistryDataObject} RDE (nolock)
					inner join {$this->scheme}.v_RegistryGroupLink RGL (nolock) on RGL.Registry_id = RDE.Registry_id
				where
					RGL.Registry_pid = :Registry_id
			)

			select
				RTrim(R.Registry_xmlExportPath) as Registry_xmlExportPath,
				R.Registry_FileNum,
				R.RegistryType_id,
				R.RegistryStatus_id,
				ISNULL(R.Registry_IsNeedReform, 1) as Registry_IsNeedReform,
				ISNULL(R.Registry_Sum,0) - round(RDSum.RegistryData_ItogSum,2) as Registry_SumDifference,
				RDSum.RegistryData_Count as RegistryData_Count,
				R.Registry_endDate,
				L.Lpu_RegNomN2 as Lpu_Code,
				rcs.RegistryCheckStatus_Code,
				R.KatNasel_id,
				KN.KatNasel_SysNick,
				OS.Orgsmo_f002smocod
			from {$this->scheme}.v_Registry R with (nolock)
				inner join v_Lpu L with (nolock) on L.Lpu_id = R.Lpu_id
				outer apply(
					select
						COUNT(RD.Evn_id) as RegistryData_Count,
						SUM(ISNULL(RD.RegistryData_ItogSum,0)) as RegistryData_ItogSum
					from RD with (nolock)
				) RDSum
				left join v_RegistryCheckStatus rcs with (nolock) on rcs.RegistryCheckStatus_id = R.RegistryCheckStatus_id
				left join v_KatNasel KN (nolock) on KN.KatNasel_id = R.KatNasel_id
				left join v_OrgSMO OS with (nolock) on OS.OrgSMO_id = R.OrgSMO_id
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
		
		$p_pers = $this->scheme . ".p_registry_evnps_exppac_f";
		$p_sl = $this->scheme . ".p_registry_evnps_expvizit_f";
		
		$p_napr = $this->scheme  . ".p_registry_evnps_expnapr_f";
		$p_onkousl = $this->scheme  . ".p_registry_evnps_expONKO_f";
		
		$PERS = array();
		if (!empty($p_pers)) {
			$result = $this->db->query("
				select * from {$p_pers} (:Registry_id)
			", array('Registry_id' => $Registry_id));
			if (!is_object($result)) {
				return false;
			}
			while ($one_rec = $result->_fetch_assoc()) {
				array_walk_recursive($one_rec, 'ConvertFromUTF8ToWin1251', true);
				$PERS[$one_rec['Evn_id']][] = $one_rec;
			}
		}
		
		$SL = array();
		if (!empty($p_sl)) {
			$result = $this->db->query("
				select * from {$p_sl} (:Registry_id)
			", array('Registry_id' => $Registry_id));
			if (!is_object($result)) {
				return false;
			}
			while ($one_rec = $result->_fetch_assoc()) {
				array_walk_recursive($one_rec, 'ConvertFromUTF8ToWin1251', true);
				$SL[$one_rec['Evn_id']][] = $one_rec;
			}
		}
		
		$NAPR = array();
		if (!empty($p_napr)) {
			$result = $this->db->query("
				select * from {$p_napr} (:Registry_id)
			", array('Registry_id' => $Registry_id));
			if (!is_object($result)) {
				return false;
			}
			while ($one_rec = $result->_fetch_assoc()) {
				array_walk_recursive($one_rec, 'ConvertFromUTF8ToWin1251', true);
				$NAPR[$one_rec['Evn_id']][] = $one_rec;
			}
		}
		
		$ONKOUSL = array();
		if (!empty($p_onkousl)) {
			$result = $this->db->query("
				select * from {$p_onkousl} (:Registry_id)
			", array('Registry_id' => $Registry_id));
			if (!is_object($result)) {
				return false;
			}
			while ($one_rec = $result->_fetch_assoc()) {
				array_walk_recursive($one_rec, 'ConvertFromUTF8ToWin1251', true);
				$one_rec['LEK_PR_DATA'] = array();
				$ONKOUSL[$one_rec['Evn_id']][] = $one_rec;
			}
		}
		
		
		return array(
			'pers' => $PERS,
			'sl' => $SL,
			'napr' => $NAPR,
			'ONKOUSL' => $ONKOUSL
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
				{$this->scheme}.Registry with (rowlock)
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
}