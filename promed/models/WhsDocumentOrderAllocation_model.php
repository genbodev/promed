<?php defined('BASEPATH') or die ('No direct script access allowed');

class WhsDocumentOrderAllocation_model extends swModel {

	/**
	 *  Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 *  Загрузка списка распоряжений
	 */
	function loadWhsDocumentOrderAllocationGrid($data) {
		$params = array();
		$filter="(1=1)";
		
		if (!empty($data['DrugFinance_id'])) {
			$filter .= " and DF.DrugFinance_id = :DrugFinance_id";
			$params['DrugFinance_id'] = $data['DrugFinance_id'];
		}
		
		if (!empty($data['WhsDocumentCostItemType_id'])) {
			$filter .= " and wdcit.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id";
			$params['WhsDocumentCostItemType_id'] = $data['WhsDocumentCostItemType_id'];
		}
		
		if (!empty($data['Lpu_id'])) {
			$filter .= " and L.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		}
		
		if (!empty($data['WhsDocumentOrderAllocation_Range'])) {
			if(!empty($data['WhsDocumentOrderAllocation_Range'][0])) {
				$filter .= " and wdoa.WhsDocumentOrderAllocation_BegDate >= :WhsDocumentOrderAllocation_BegDate";
				$params['WhsDocumentOrderAllocation_BegDate'] = $data['WhsDocumentOrderAllocation_Range'][0];
			}
			if(!empty($data['WhsDocumentOrderAllocation_Range'][1])) {
				$filter .= " and wdoa.WhsDocumentOrderAllocation_EndDate <= :WhsDocumentOrderAllocation_EndDate";
				$params['WhsDocumentOrderAllocation_EndDate'] = $data['WhsDocumentOrderAllocation_Range'][1];
			}
		}
		
		if (!empty($data['KLAreaStat_id'])) {
			$filter .= " and A.KLAreaStat_id = :KLAreaStat_id";
			$params['KLAreaStat_id'] = $data['KLAreaStat_id'];
		}
		
		if (!empty($data['KLCountry_id'])) {
			$filter .= " and A.KLCountry_id = :KLCountry_id";
			$params['KLCountry_id'] = $data['KLCountry_id'];
		}
		
		if (!empty($data['KLRgn_id'])) {
			$filter .= " and A.KLRgn_id = :KLRgn_id";
			$params['KLRgn_id'] = $data['KLRgn_id'];
		}
		
		if (!empty($data['KLSubRgn_id'])) {
			$filter .= " and A.KLSubRgn_id = :KLSubRgn_id";
			$params['KLSubRgn_id'] = $data['KLSubRgn_id'];
		}
		
		if (!empty($data['KLCity_id'])) {
			$filter .= " and A.KLCity_id = :KLCity_id";
			$params['KLCity_id'] = $data['KLCity_id'];
		}
		
		if (!empty($data['KLTown_id'])) {
			$filter .= " and A.KLTown_id = :KLTown_id";
			$params['KLTown_id'] = $data['KLTown_id'];
		}

		if (!empty($data['WhsDocumentType_Code'])) {
			$filter .= " and wdt.WhsDocumentType_Code = :WhsDocumentType_Code";
			$params['WhsDocumentType_Code'] = $data['WhsDocumentType_Code'];
		}
		
		// если не минздрав то грузим только разнарядки своего МО // TODO раскомментить + ещё определять МУЗ – имеет доступ только к разнарядкам МО на подведомственной территории.
		/*if (!isMinZdrav()) {
			$filter .= " and L.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['session']['lpu_id'];			
		}*/
		
		$query = "
			Select
				-- select
				wdoa.WhsDocumentOrderAllocation_id,
				ISNULL(convert(varchar,wdoa.WhsDocumentOrderAllocation_BegDate,104),'...') + ' - ' + ISNULL(convert(varchar,wdoa.WhsDocumentOrderAllocation_EndDate,104),'...') as WhsDocumentOrderAllocation_Period,
				df.DrugFinance_Name,
				wdcit.WhsDocumentCostItemType_Name,
				STR(wdoa.WhsDocumentUc_Sum,10,2) as WhsDocumentUc_Sum,
				L.Lpu_Nick,
				L.UAddress_Address
				-- end select
			from
				-- from
				v_WhsDocumentOrderAllocation wdoa (nolock) -- сводные + разнарядки МО (дочерний к сводной разнарядке документ)
					left join v_DrugFinance df (nolock) on df.DrugFinance_id = wdoa.DrugFinance_id -- финансирование
					left join v_WhsDocumentCostItemType wdcit (nolock) on wdcit.WhsDocumentCostItemType_id = wdoa.WhsDocumentCostItemType_id -- статья расходов
					left join v_Lpu L (nolock) on L.Org_id = wdoa.Org_id -- МО
					left join v_Address_all A with (nolock) on A.Address_id = L.UAddress_id -- адрес МО
					left join v_WhsDocumentType wdt with (nolock) on wdt.WhsDocumentType_id = wdoa.WhsDocumentType_id -- тип документа
				-- end from
			where
				-- where
				{$filter}
				-- end where
			order by
				-- order by
				wdoa.WhsDocumentOrderAllocation_id
				-- end order by";
		/*
		 echo getDebugSql(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
		 echo getDebugSql(getCountSQLPH($query), $params);
		 exit;
		*/
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
	 *  Загрузка списка
	 */
	function loadList($filter) {
		$where = array();
		$p = array();
		
		$where[] = "(WDOA.WhsDocumentUc_pid IS NULL or WDOA.WhsDocumentType_id = 17)";
		
		if ( isset($filter['WhsDocumentUc_Date_Range'][0]) )
		{
			$where[] = "cast(WDOA.WhsDocumentUc_Date as date) >= cast(:WhsDocumentUc_Date_Beg as date)";
			$p['WhsDocumentUc_Date_Beg'] = $filter['WhsDocumentUc_Date_Range'][0];
		}
		if ( isset($filter['WhsDocumentUc_Date_Range'][1]) )
		{
			$where[] = "cast(WDOA.WhsDocumentUc_Date as date) <= cast(:WhsDocumentUc_Date_End as date)";
			$p['WhsDocumentUc_Date_End'] = $filter['WhsDocumentUc_Date_Range'][1];
		}
		if (isset($filter['WhsDocumentCostItemType_id']) && $filter['WhsDocumentCostItemType_id']) {
			$where[] = 'WDOA.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id';
			$p['WhsDocumentCostItemType_id'] = $filter['WhsDocumentCostItemType_id'];
		}
		if (isset($filter['DrugFinance_id']) && $filter['DrugFinance_id']) {
			$where[] = 'WDOA.DrugFinance_id = :DrugFinance_id';
			$p['DrugFinance_id'] = $filter['DrugFinance_id'];
		}
		if (isset($filter['WhsDocumentType_id']) && $filter['WhsDocumentType_id']) {
			$where[] = 'WDOA.WhsDocumentType_id = :WhsDocumentType_id';
			$p['WhsDocumentType_id'] = $filter['WhsDocumentType_id'];
		}
		$where_clause = implode(' AND ', $where);
		if (strlen($where_clause)) {
			$where_clause = 'WHERE '.$where_clause;
		}
		$q = "
			SELECT
				WDOA.WhsDocumentUc_id,
				WDOA.WhsDocumentOrderAllocation_id,
				WDT.WhsDocumentType_id,
				WDT.WhsDocumentType_Name,
				WDOA.WhsDocumentUc_Num,
				convert(varchar,cast(WDOA.WhsDocumentUc_Date as datetime),104) as WhsDocumentUc_Date,
				DF.DrugFinance_Name,
				WDCIT.WhsDocumentCostItemType_Name,
				STR(ISNULL(WDOA.WhsDocumentUc_Sum,0),10,2) as WhsDocumentUc_Sum,
				convert(varchar,cast(WDOA.WhsDocumentOrderAllocation_updDT as datetime),104) + ' ' + SUBSTRING(convert(varchar,cast(WhsDocumentOrderAllocation_updDT as datetime),108),1,5) as WhsDocumentUc_updDT,
				WDST.WhsDocumentStatusType_id,
				WDST.WhsDocumentStatusType_Name
			FROM
				v_WhsDocumentOrderAllocation WDOA WITH (NOLOCK)
				LEFT JOIN v_WhsDocumentType WDT WITH (NOLOCK) ON WDT.WhsDocumentType_id = WDOA.WhsDocumentType_id
				LEFT JOIN v_DrugFinance DF WITH (NOLOCK) ON DF.DrugFinance_id = WDOA.DrugFinance_id
				LEFT JOIN v_WhsDocumentCostItemType WDCIT WITH (NOLOCK) ON WDCIT.WhsDocumentCostItemType_id = WDOA.WhsDocumentCostItemType_id
				LEFT JOIN v_WhsDocumentStatusType WDST WITH (NOLOCK) ON WDST.WhsDocumentStatusType_id = isnull(WDOA.WhsDocumentStatusType_id, 1)
			$where_clause
		";
		//echo getDebugSql($q, $p); die();
		$result = $this->db->query($q, $p);
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *  Загрузка данных распоряжения
	 */
	function load($filter) {
		$q = "
			SELECT
				WDOR.WhsDocumentUc_id,
				WDOR.WhsDocumentUc_pid,
				WDOR.WhsDocumentOrderAllocation_id,
				WDOR.WhsDocumentType_id,
				WDOR.WhsDocumentUc_Num,
				WDOR.WhsDocumentUc_Date,
				WDOR.WhsDocumentUc_Name,
				WDOR.Org_id,
				WDOR.DrugFinance_id,
				WDOR.WhsDocumentCostItemType_id,
				WDOR.WhsDocumentOrderAllocation_Percent,
				WDOR.WhsDocumentUc_Sum,
				WDOR.DrugRequest_id,
				ISNULL(convert(varchar,WDOR.WhsDocumentOrderAllocation_BegDate,104),'') + ' - ' + ISNULL(convert(varchar,WDOR.WhsDocumentOrderAllocation_EndDate,104),'') as WhsDocumentUc_Date_Range,
				isnull(WDOR.WhsDocumentStatusType_id, 1) as WhsDocumentStatusType_id
			FROM
				v_WhsDocumentOrderAllocation WDOR WITH (NOLOCK)
			WHERE WhsDocumentUc_id = :WhsDocumentUc_id
		";
		$result = $this->db->query($q, array('WhsDocumentUc_id' => $filter['WhsDocumentUc_id']));
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *  Сохранение
	 */
	function save($data) {
		$procedure = 'p_WhsDocumentOrderAllocation_ins';
		if ( $data['WhsDocumentUc_id'] > 0 ) {
			$procedure = 'p_WhsDocumentOrderAllocation_upd';
		}
		$q = "
			declare
				@WhsDocumentUc_id bigint,
				@WhsDocumentOrderAllocation_id bigint,
				@WhsDocumentType_id bigint = :WhsDocumentType_id,
				@DrugRequest_id bigint = :DrugRequest_id,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @WhsDocumentUc_id = :WhsDocumentUc_id;

			if (:WhsDocumentUc_pid is not null and :WhsDocumentType_id is null) --если у распоряжения есть родитель, значит это разнарядка МО
			begin
				set @WhsDocumentType_id = (select top 1 WhsDocumentType_id from v_WhsDocumentType with(nolock) where WhsDocumentType_Code = 9)
			end;

			exec dbo." . $procedure . "
				@WhsDocumentUc_id = @WhsDocumentUc_id output,
				@WhsDocumentUc_pid = :WhsDocumentUc_pid,
				@WhsDocumentUc_Num = :WhsDocumentUc_Num,
				@WhsDocumentUc_Name = :WhsDocumentUc_Name,
				@WhsDocumentType_id = @WhsDocumentType_id,
				@WhsDocumentUc_Date = :WhsDocumentUc_Date,
				@WhsDocumentUc_Sum = :WhsDocumentUc_Sum,
				@WhsDocumentOrderAllocation_id = :WhsDocumentOrderAllocation_id,
				@DrugFinance_id = :DrugFinance_id,
				@Org_id = :Org_id,
				@WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id,
				@WhsDocumentOrderAllocation_Percent = :WhsDocumentOrderAllocation_Percent,
				@WhsDocumentOrderAllocation_BegDate = :WhsDocumentUc_Date_begDate,
				@WhsDocumentOrderAllocation_EndDate = :WhsDocumentUc_Date_endDate,
				@DrugRequest_id = :DrugRequest_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @WhsDocumentUc_id as WhsDocumentUc_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$p = array(
			'WhsDocumentUc_id' => $data['WhsDocumentUc_id'],
			'WhsDocumentOrderAllocation_id' => $data['WhsDocumentOrderAllocation_id'],
			'WhsDocumentUc_pid' => $data['WhsDocumentUc_pid'],
			'WhsDocumentUc_Num' => $data['WhsDocumentUc_Num'],
			'WhsDocumentUc_Name' => $data['WhsDocumentUc_Name'],
			'WhsDocumentType_id' => $data['WhsDocumentType_id'],
			'WhsDocumentUc_Date' => $data['WhsDocumentUc_Date'],						
			'DrugFinance_id' => $data['DrugFinance_id'],
			'Org_id' => $data['Org_id'],
			'WhsDocumentUc_Date_begDate' => (isset($data['WhsDocumentUc_Date_Range'][0])?$data['WhsDocumentUc_Date_Range'][0]:NULL),
			'WhsDocumentUc_Date_endDate' => (isset($data['WhsDocumentUc_Date_Range'][1])?$data['WhsDocumentUc_Date_Range'][1]:NULL),
			'WhsDocumentCostItemType_id' => $data['WhsDocumentCostItemType_id'],			
			'WhsDocumentOrderAllocation_Percent' => $data['WhsDocumentOrderAllocation_Percent'],
			'WhsDocumentUc_Sum' => $data['WhsDocumentUc_Sum'],
			'DrugRequest_id' => $data['DrugRequest_id'],
			'pmUser_id' => $data['pmUser_id']
		);
		//die(getDebugSQL($q, $p));
		
		$r = $this->db->query($q, $p);
		if ( is_object($r) ) {
		    $result = $r->result('array');
		} else {
			log_message('error', var_export(array('q' => $q, 'p' => $p, 'e' => sqlsrv_errors()), true));
			$result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}
		return $result;
	}

	/**
	 *  Генерация номера распоряжения
	 */
	function getWhsDocumentOrderAllocationNumber($data) {
		$query = "
			declare @WhsDocumentUc_Num bigint;
			exec xp_GenpmID @ObjectName = 'WhsDocumentOrderAllocation', @Lpu_id = :Lpu_id, @ObjectID = @WhsDocumentUc_Num output;
			select @WhsDocumentUc_Num as WhsDocumentUc_Num;
		";
		$result = $this->db->query($query, array(
			'Lpu_id' => $data['Lpu_id'] > 0 ? $data['Lpu_id'] : null
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *  Подписание распоряжения
	 */
	function sign($data) {
		// Стартуем транзакцию
		$this->db->trans_begin();

		// Проверяем текущий статус долкумента
		$query = "
			select
				WhsDocumentStatusType_id
			from
				WhsDocumentUc with(nolock)
			where
				WhsDocumentUc_id = :WhsDocumentUc_id;
		";
		$result = $this->db->query($query, array(
			'WhsDocumentUc_id' => $data['WhsDocumentOrderAllocation_id']
		));
		if ( is_object($result) ) {
			$res = $result->result('array');
			if (isset($res[0]['WhsDocumentStatusType_id']) && $res[0]['WhsDocumentStatusType_id'] == 2) {
				$this->db->trans_rollback();
				return array(0 => array('Error_Msg' => 'Документ уже подписан, повторное подписание невозможно'));
			}
		}

		// Обновляем статус документа
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_WhsDocumentUc_sign
				@WhsDocumentUc_id = :WhsDocumentUc_id,
				@WhsDocumentStatusType_id = 2,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		
		$queryParams = array(
			'WhsDocumentUc_id' => $data['WhsDocumentOrderAllocation_id'],
			'Org_id' => $data['session']['org_id'],
			'pmUser_id' => $data['pmUser_id']
		);
		
		$result = $this->db->query($query, $queryParams);
		if ( is_object($result) ) {
			$WhsDocumentUcStatus = $result->result('array');
			if (!empty($WhsDocumentUcStatus[0]['Error_Msg'])) {
				$this->db->trans_rollback();
				return array(0 => array('Error_Msg' => 'Ошибка обновления статуса документа'));
			}
		}
		else {
			$this->db->trans_rollback();
			return array(0 => array('Error_Msg' => 'Ошибка запроса обновления статуса документа'));
		}

		//делаем пересчет остатков
		if (in_array($data['WhsDocumentType_id'], array(7, 8))) {
			$response = $this->execute($data);
			if (isset($response[0]) && !empty($response[0]['Error_Msg'])) {
				$this->db->trans_rollback();
				return $response;
			}
		}
		
		if ($data['WhsDocumentType_id'] == 23) {
			$response = $this->executeOstat($data);
			if (isset($response[0]) && !empty($response[0]['Error_Msg'])) {
				$this->db->trans_rollback();
				return $response;
			}
		}

		$this->db->trans_commit();
		return array(0 => array('Error_Msg' => ''));
	}

	/**
	 *  Исполнение распоряжения на выдачу разнарядки (в данный момент - запись остатков на субсчет поставщиков)
	 */
	function execute($data) {
		$err = null;
		$mo_alloc = array();
        $suppliers_ostat_control = !empty($data['options']['drugcontrol']['suppliers_ostat_control']);

		//собираем список партий из которых можно списывать для каждой строки разнарядки мо
		$query = "
			declare
				@SubAccountType_id bigint,
				@MinzdravOrg_id bigint,
				@WhsDocumentOrderAllocation_id bigint = :WhsDocumentOrderAllocation_id;

			set @SubAccountType_id = (select top 1 SubAccountType_id from SubAccountType with(nolock) where SubAccountType_Code = 1);
			set @MinzdravOrg_id = dbo.GetMinzdravDloOrgId();

			with ostat as (
				select
					dor.Drug_id,
					dor.DrugOstatRegistry_Kolvo,
					dor.DrugOstatRegistry_Cost,
					dor.DrugOstatRegistry_id,
					wds.WhsDocumentSupply_id,
					wds.Org_sid as SupplierOrg_id
				from
					v_WhsDocumentOrderAllocationDrug wdord with (nolock)
					left join v_WhsDocumentOrderAllocation wdoa with (nolock) on wdoa.WhsDocumentOrderAllocation_id = wdord.WhsDocumentOrderAllocation_id
					left join rls.v_Drug d with (nolock) on d.Drug_id = WDORD.Drug_id
					left join v_WhsDocumentSupply wds with (nolock) on wds.WhsDocumentUc_id = wdord.WhsDocumentUc_pid
					left join v_DrugShipment ds with (nolock) on ds.WhsDocumentSupply_id = wds.WhsDocumentSupply_id
					inner join v_DrugOstatRegistry dor with (nolock) on
						dor.Drug_id = WDORD.Drug_id and
						dor.DrugShipment_id = ds.DrugShipment_id and
						dor.Org_id = @MinzdravOrg_id and
						dor.SubAccountType_id = @SubAccountType_id
				where
					wdord.WhsDocumentOrderAllocation_id = @WhsDocumentOrderAllocation_id
			)
			select
				wdoa.Org_id as org,
				wdoad.Drug_id,
				wdoad.WhsDocumentOrderAllocationDrug_Kolvo as kolvo,
				wdoad.WhsDocumentOrderAllocationDrug_Price as price,
				od.ost_data
			from
				v_WhsDocumentOrderAllocation wdoa with (nolock)
				left join v_WhsDocumentOrderAllocationDrug wdoad with (nolock) on wdoad.WhsDocumentOrderAllocation_id = wdoa.WhsDocumentOrderAllocation_id
				outer apply (
				    select
				        replace(replace((
                            select
                                cast(DrugOstatRegistry_id as varchar)+
                                '-'+
                                cast(SupplierOrg_id as varchar)+
                                ';' as 'data()'
                            from
                                ostat with(nolock)
                            where
                                ostat.Drug_id = wdoad.Drug_id and
                                isnull(ostat.DrugOstatRegistry_Cost, 0) = isnull(wdoad.WhsDocumentOrderAllocationDrug_Price, 0)  and
                                (ostat.WhsDocumentSupply_id = wdoad.WhsDocumentUc_pid or wdoad.WhsDocumentUc_pid is null)
                            for xml path('')
                        )+';;', ';;;', ''), ';;', '') as ost_data
				) od
			where
				wdoa.WhsDocumentUc_pid = @WhsDocumentOrderAllocation_id and
				wdoad.WhsDocumentOrderAllocationDrug_Kolvo > 0 and
				od.ost_data is not null;
		";
		$queryParams = array(
			'WhsDocumentOrderAllocation_id' => $data['WhsDocumentOrderAllocation_id']
		);
		$result = $this->db->query($query, $queryParams);
		if ( is_object($result) ) {
			$mo_alloc = $result->result('array');
		} else {
			$err = 'Ошибка запроса получения данных из разнарядки';
		}

		//вспомгательный массив, необходим для сбора информации для начисления остатков поставщикам
		$sup_arr = array();

		//перебираем массив разнарядки МО
		if (empty($err) && count($mo_alloc) > 0) {
			foreach($mo_alloc as &$record) {
				$record['current_kolvo'] = $record['kolvo'];
				$ost_arr = preg_split('/;/', $record['ost_data']);
				for($i = 0; $i < count($ost_arr); $i++) {
					list($ost_id, $sup_org_id) = preg_split('/-/', $ost_arr[$i]);

					//списываем часть медикаментов с конкретной строки регистра остатков
					$resp = $this->moveDrug($ost_id, $record['org'], $record['current_kolvo'], $data['pmUser_id']);

                    if (!empty($resp['Error_Msg'])) {
                        return array(0 => array('Error_Msg' => $resp['Error_Msg']));
                    } else if ($resp['cnt'] == 0) { //ничего списать не удалось
						return array(0 => array('Error_Msg' => 'При списании или начислении медикамента произошла ошибка'));
					}

					//сохраняем информацию о количестве медикамента списанного с конкретной строки регистра остатков
					if (!isset($sup_arr[$ost_id])) {
						$sup_arr[$ost_id] = array(
							'cnt' => 0,
							'sum' => 0,
							'sup_org_id' => $sup_org_id
						);
					}
					$sup_arr[$ost_id]['cnt'] += $resp['cnt'];
					$sup_arr[$ost_id]['sum'] += $resp['sum'];

					$record['current_kolvo'] -= $resp['cnt'];

					if ($record['current_kolvo'] <= 0) {
						break;
					}
				}
				if ($record['current_kolvo'] > 0) {
					return array(0 => array('Error_Msg' => 'Недостаточно медикаментов в регистре остатков'));
				}
			}

			//начисляем остатки на счет поставщиков если включена соответствующая настройка
            /*if ($suppliers_ostat_control) {
                foreach($sup_arr as $key=>$value) {
                    $cnt = $this->moveDrugToSupplier($key, $value['sup_org_id'], $value['cnt'], $value['sum'], $data['pmUser_id']);
                    if ($cnt == 0) {
                        return array(0 => array('Error_Msg' => 'При начислении медикамента произошла ошибка'));
                    }
                }
            }*/
		}

		return array(0 => array('Error_Msg' => $err));
	}

	/**
	 *  Исполнение распоряжения на ввод остатков по разнарядке
	 */
	function executeOstat($data) {
		
		$err = null;
		$svod_alloc = array();
		$mo_alloc = array();
		$drug_name = array();
		$drug_kolvo_svod = array();
		$drug_kolvo_mo = array();
		$drug_shipment = array(); // связь гк и партии

		//собираем список сводной разнарядки
		$query = "
			select 
				wdoad.Drug_id,
				d.DrugComplexMnn_id,
				wdoad.WhsDocumentOrderAllocationDrug_Kolvo as kolvo,
				wdoad.WhsDocumentOrderAllocationDrug_Price as price,
				wdoad.Okei_id,
				wds.WhsDocumentSupply_id,
				wds.WhsDocumentUc_Num,
				convert(varchar(10), wds.WhsDocumentUc_Date, 104) WhsDocumentUc_Date,
				wds.Org_sid, --Поставщик
				ds.DrugShipment_id, --Партия
				d.DrugTorg_Name
			from 
				v_WhsDocumentOrderAllocationDrug wdoad with (nolock)
				inner join v_WhsDocumentSupply wds with (nolock) on wds.WhsDocumentSupply_id = wdoad.WhsDocumentUc_pid
				inner join rls.v_Drug d (nolock) on d.Drug_id = wdoad.Drug_id
				left join v_DrugShipment ds with (nolock) on ds.WhsDocumentSupply_id = wds.WhsDocumentSupply_id
			where 
				wdoad.WhsDocumentOrderAllocation_id = :WhsDocumentOrderAllocation_id
		";
		$queryParams = array(
			'WhsDocumentOrderAllocation_id' => $data['WhsDocumentOrderAllocation_id']
		);
		$result = $this->db->query($query, $queryParams);
		if ( is_object($result) ) {
			$svod_alloc = $result->result('array');
		} else {
			$err = 'Ошибка запроса получения данных из разнарядки';
		}
		
		if (!count($svod_alloc)) {
			return array(0 => array('Error_Msg' => 'В разнарядке не указаны медикаменты. Распоряжение не может быть исполнено.'));
		}
		
		foreach($svod_alloc as $val) {
		
			// Проверка наличия партии, связанной с гк, если нет - создаём
			$drug_shipment[$val['WhsDocumentSupply_id']] = isset($drug_shipment[$val['WhsDocumentSupply_id']]) ? $drug_shipment[$val['WhsDocumentSupply_id']] : $val['DrugShipment_id'];
			if (empty($drug_shipment[$val['WhsDocumentSupply_id']])) {
				$query = "
					declare
						@Res bigint,
						@ErrCode int,
						@ErrMessage varchar(4000),
						@datetime datetime;
					set @datetime = dbo.tzGetDate();
					exec p_DrugShipment_ins
						@DrugShipment_id = @Res output,
						@DrugShipment_setDT = @datetime,
						@DrugShipment_Name = :DrugShipment_Name,
						@WhsDocumentSupply_id = :WhsDocumentSupply_id,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
					select @Res as DrugShipment_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";
				$queryParams = array(
					'WhsDocumentSupply_id' => $val['WhsDocumentSupply_id'],
					'DrugShipment_Name' => $val['WhsDocumentUc_Num'] + ' от ' + $val['WhsDocumentUc_Date'],
					'pmUser_id' => $data['pmUser_id']
				);
				$result = $this->getFirstRowFromQuery($query, $queryParams);
				if (is_array($result) && !empty($result['DrugShipment_id'])) {
					$drug_shipment[$val['WhsDocumentSupply_id']] = $result['DrugShipment_id'];
				}
			}
			
			$drug_kolvo_svod[$val['Drug_id']] = isset($drug_kolvo_svod[$val['Drug_id']]) ? $drug_kolvo_svod[$val['Drug_id']] + $val['kolvo'] : $val['kolvo'];
			$drug_name[$val['Drug_id']] = $val['DrugTorg_Name'];
			$val['sum'] = $val['kolvo'] * $val['price'];
			//зачисляем ЛС на счет поставщика
			/*$query = "
				declare
					@SubAccountType_id bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);
				set @SubAccountType_id = (select top 1 SubAccountType_id from SubAccountType with(nolock) where SubAccountType_Code = 1); -- субсчёт доступно
				exec xp_DrugOstatRegistry_count
					@Contragent_id = NULL,
					@Org_id = :Org_id,
					@DrugShipment_id = :DrugShipment_id,
					@Drug_id = :Drug_id,
					@PrepSeries_id = NULL, -- используется в складском учете. При учете обязательств по контрактам не используется.
					@SubAccountType_id = @SubAccountType_id,
					@Okei_id = :Okei_id,
					@DrugOstatRegistry_Kolvo = :DrugOstatRegistry_Kolvo,
					@DrugOstatRegistry_Sum = :DrugOstatRegistry_Sum,
					@DrugOstatRegistry_Cost = :DrugOstatRegistry_Cost,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";

			$queryParams = array(
				'Org_id' => $val['Org_sid'],
				'DrugShipment_id' => $drug_shipment[$val['WhsDocumentSupply_id']],
				'Drug_id' => $val['Drug_id'],
				'Okei_id' => $val['Okei_id'],
				'DrugOstatRegistry_Kolvo' => $val['kolvo'],
				'DrugOstatRegistry_Sum' => $val['sum'],
				'DrugOstatRegistry_Cost' => $val['price'],
				'pmUser_id' => $data['pmUser_id']
			);

			$result = $this->db->query($query, $queryParams);
			if ( is_object($result) ) {
				$res = $result->result('array');
				if (!empty($res[0]['Error_Msg'])) {
					return array(0 => array('Error_Msg' => 'При начислении медикамента произошла ошибка'));
				}
			}*/
			
			// вносим медикаменты в контракт
			$query = "
				declare
					@WhsDocumentSupplySpec_id bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);
				set @WhsDocumentSupplySpec_id = :WhsDocumentSupplySpec_id;
				exec dbo.p_WhsDocumentSupplySpec_ins
					@WhsDocumentSupplySpec_id = @WhsDocumentSupplySpec_id output,
					@WhsDocumentSupply_id = :WhsDocumentSupply_id,
					@Drug_id = :Drug_id,
					@DrugComplexMnn_id = :DrugComplexMnn_id,
					@Okei_id = :Okei_id,
					@WhsDocumentSupplySpec_KolvoUnit = :WhsDocumentSupplySpec_KolvoUnit,
					@WhsDocumentSupplySpec_Count = :WhsDocumentSupplySpec_Count,
					@WhsDocumentSupplySpec_Price = :WhsDocumentSupplySpec_Price,
					@WhsDocumentSupplySpec_NDS = :WhsDocumentSupplySpec_NDS,
					@WhsDocumentSupplySpec_SumNDS = :WhsDocumentSupplySpec_SumNDS,
					@WhsDocumentSupplySpec_PriceNDS = :WhsDocumentSupplySpec_PriceNDS,
					@WhsDocumentSupplySpec_ShelfLifePersent = :WhsDocumentSupplySpec_ShelfLifePersent,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @WhsDocumentSupplySpec_id as WhsDocumentSupplySpec_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			$queryParams = array(
				'WhsDocumentSupplySpec_id' => null,
				'WhsDocumentSupply_id' => $val['WhsDocumentSupply_id'],
				'Drug_id' => $val['Drug_id'],
				'DrugComplexMnn_id' => $val['DrugComplexMnn_id'],
				'Okei_id' => $val['Okei_id'],
				'WhsDocumentSupplySpec_KolvoUnit' => $val['kolvo'],
				'WhsDocumentSupplySpec_Count' => $val['kolvo'],
				'WhsDocumentSupplySpec_Price' => round(($val['price'] / 1.1), 2), // цена без НДС
				'WhsDocumentSupplySpec_NDS' => 10,
				'WhsDocumentSupplySpec_SumNDS' => $val['sum'],
				'WhsDocumentSupplySpec_PriceNDS' => $val['price'],
				'WhsDocumentSupplySpec_ShelfLifePersent' => 70,
				'pmUser_id' => $data['pmUser_id']
			);
			$result = $this->db->query($query, $queryParams);
			if ( is_object($result) ) {
				$res = $result->result('array');
				if (!empty($res[0]['Error_Msg'])) {
					return array(0 => array('Error_Msg' => 'При начислении медикамента произошла ошибка'));
				}
			}
			
			//сохраняем данные о медикаментах в номенклатурный справочник
			$this->addNomenData('Drug', $val['Drug_id'], array('pmUser_id' => $data['pmUser_id']));
			
			// пересчёт суммы контракта
			// криво, но нормальной хранимки похоже нет
			$query = "update WhsDocumentUc set WhsDocumentUc_Sum = WhsDocumentUc_Sum + :sum where WhsDocumentUc_id = :WhsDocumentUc_id";
			$result = $this->db->query($query, array(
				'sum' => $val['sum'],
				'WhsDocumentUc_id' => $val['WhsDocumentSupply_id']
			));
		}
		
		//собираем список разнарядки МО
		$query = "
			select 
				wdoad.Drug_id,
				wdoad.WhsDocumentOrderAllocationDrug_Kolvo as kolvo,
				wdoad.WhsDocumentOrderAllocationDrug_Price as price,
				wdoad.Okei_id,
				wds.WhsDocumentSupply_id,
				wdoa.Org_id --Получатель
			from 
				v_WhsDocumentOrderAllocationDrug wdoad with (nolock)
				inner join v_WhsDocumentOrderAllocation wdoa (nolock) on wdoa.WhsDocumentOrderAllocation_id = wdoad.WhsDocumentOrderAllocation_id
				inner join v_WhsDocumentSupply wds with (nolock) on wds.WhsDocumentSupply_id = wdoad.WhsDocumentUc_pid
			where 
				wdoad.WhsDocumentOrderAllocation_id IN (select WhsDocumentOrderAllocation_id from v_WhsDocumentOrderAllocation (nolock) where WhsDocumentUc_pid = :WhsDocumentOrderAllocation_id and WhsDocumentType_id = 9)
		";
		$queryParams = array(
			'WhsDocumentOrderAllocation_id' => $data['WhsDocumentOrderAllocation_id']
		);
		$result = $this->db->query($query, $queryParams);
		if ( is_object($result) ) {
			$mo_alloc = $result->result('array');
		} else {
			$err = 'Ошибка запроса получения данных из разнарядки';
		}
		
		foreach($mo_alloc as $val) {
			$drug_kolvo_mo[$val['Drug_id']] = isset($drug_kolvo_mo[$val['Drug_id']]) ? $drug_kolvo_mo[$val['Drug_id']] + $val['kolvo'] : $val['kolvo'];
			$val['sum'] = $val['kolvo'] * $val['price'];
			//зачисляем ЛС на счет МО
			$query = "
				declare
					@SubAccountType_id bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);
				set @SubAccountType_id = (select top 1 SubAccountType_id from SubAccountType with(nolock) where SubAccountType_Code = 1); -- субсчёт доступно
				exec xp_DrugOstatRegistry_count
					@Contragent_id = NULL,
					@Org_id = :Org_id,
					@DrugShipment_id = :DrugShipment_id,
					@Drug_id = :Drug_id,
					@PrepSeries_id = NULL, -- используется в складском учете. При учете обязательств по контрактам не используется.
					@SubAccountType_id = @SubAccountType_id,
					@Okei_id = :Okei_id,
					@DrugOstatRegistry_Kolvo = :DrugOstatRegistry_Kolvo,
					@DrugOstatRegistry_Sum = :DrugOstatRegistry_Sum,
					@DrugOstatRegistry_Cost = :DrugOstatRegistry_Cost,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";

			$queryParams = array(
				'Org_id' => $val['Org_id'],
				'DrugShipment_id' => isset($drug_shipment[$val['WhsDocumentSupply_id']]) ? $drug_shipment[$val['WhsDocumentSupply_id']] : null,
				'Drug_id' => $val['Drug_id'],
				'Okei_id' => $val['Okei_id'],
				'DrugOstatRegistry_Kolvo' => $val['kolvo'],
				'DrugOstatRegistry_Sum' => $val['sum'],
				'DrugOstatRegistry_Cost' => $val['price'],
				'pmUser_id' => $data['pmUser_id']
			);

			$result = $this->db->query($query, $queryParams);
			if ( is_object($result) ) {
				$res = $result->result('array');
				if (!empty($res[0]['Error_Msg'])) {
					return array(0 => array('Error_Msg' => 'При начислении медикамента произошла ошибка'));
				}
			}
			
		}
		
		foreach ($drug_kolvo_svod as $k => $v) {
			$m = isset($drug_kolvo_mo[$k]) ? $drug_kolvo_mo[$k] : 0;
			if ($v != $m) {
				$diff = abs($v - $m);
				return array(0 => array('Error_Msg' => "
					Распределение медикамента «{$drug_name[$k]}» между МО не завершено – разница между сводной разнарядкой и разнарядками МО равно {$diff}.<br>
					Распоряжение не может быть исполнено.
				"));
			}
		}
	}

	/**
	 *  Вспомогательная функция для переноса медикаментов, возвращает количество медикамента, которое удалось перенести, а также сумму
	 *
	 *  @param $ost_id - строка регистра остатков, с котрой будем списывать медикаменты
	 *  @param $org_id - организация на которую будем начислять медикаменты	 *
	 *  @param $kolvo - количество медикамента
	 *  @param $pmuser_id - ид пользователя
	 */
	function moveDrug($ost_id, $org_id, $kolvo, $pmuser_id) {
		$cnt = 0;
		$sum = 0;
		$data = array();
		$resp = array(
			'cnt' => 0,
			'sum' => 0
		);

		$query = "
			select
				dor.DrugOstatRegistry_Kolvo as kolvo,
				isnull(dor.DrugOstatRegistry_Cost, 0) as price,
				dor.DrugShipment_id,
				dor.Drug_id,
				dor.Org_id,
				dor.SubAccountType_id,
				dor.Okei_id,
				dor.DrugOstatRegistry_Cost
			from
				v_DrugOstatRegistry dor with(nolock)
			where
				dor.DrugOstatRegistry_Kolvo > 0 and
				dor.DrugOstatRegistry_id = :DrugOstatRegistry_id;
		";
		$result = $this->db->query($query, array(
			'DrugOstatRegistry_id' => $ost_id
		));
		if (is_object($result)) {
			$res = $result->result('array');
			if (count($res) > 0) {
				$data = $res[0];
				//корректируем колличество
				$cnt = $data['kolvo'] > $kolvo ? $kolvo : $data['kolvo'];
				$sum = $data['price']*$cnt;
			}
		}
		if ($cnt == 0) {
			return $resp;
		}

		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec xp_DrugOstatRegistry_count
				@Contragent_id = NULL,
				@Org_id = :Org_id,
				@DrugShipment_id = :DrugShipment_id,
				@Drug_id = :Drug_id,
				@PrepSeries_id = NULL, -- используется в складском учете. При учете обязательств по контрактам не используется.
				@SubAccountType_id = :SubAccountType_id, -- субсчёт доступно
				@Okei_id = :Okei_id,
				@DrugOstatRegistry_Kolvo = :DrugOstatRegistry_Kolvo,
				@DrugOstatRegistry_Sum = :DrugOstatRegistry_Sum,
				@DrugOstatRegistry_Cost = :DrugOstatRegistry_Cost,
				@InnerTransaction_Disabled = 1,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'DrugShipment_id' => $data['DrugShipment_id'],
			'Drug_id' => $data['Drug_id'],
			'SubAccountType_id' => $data['SubAccountType_id'],
			'Okei_id' => $data['Okei_id'],
			'DrugOstatRegistry_Cost' => $data['DrugOstatRegistry_Cost'],
			'pmUser_id' => $pmuser_id
		);

		//начисление на остатки
		$queryParams['Org_id'] = $org_id;
		$queryParams['DrugOstatRegistry_Kolvo'] = $cnt;
		$queryParams['DrugOstatRegistry_Sum'] = $sum;

		$result = $this->db->query($query, $queryParams);
		if ( is_object($result) ) {
			$res = $result->result('array');
			if (!empty($res[0]['Error_Msg'])) {
				return $resp;
			}
		}

		//списание с остатков
		$queryParams['Org_id'] = $data['Org_id'];
		$queryParams['DrugOstatRegistry_Kolvo'] = $cnt*(-1);
		$queryParams['DrugOstatRegistry_Sum'] = $sum*(-1);

		$result = $this->db->query($query, $queryParams);
		if ( is_object($result) ) {
			$res = $result->result('array');
			if (!empty($res[0]['Error_Msg'])) {
                $resp['Error_Msg'] = $res[0]['Error_Msg'];
				return $resp;
			}
		}

		$resp['cnt'] = $cnt;
		$resp['sum'] = $sum;

		return $resp;
	}

	/**
	 *  Вспомогательная функция для зачисления медикаментов на поставщика
	 *
	 *  @param $ost_id - строка регистра остатков, с котрой будем списывать медикаменты
	 *  @param $org_id - организация на которую будем начислять медикаменты	 *
	 *  @param $cnt - количество медикамента
	 *  @param $sum - сумма
	 *  @param $pmuser_id - ид пользователя
	 */
	function moveDrugToSupplier($ost_id, $org_id, $cnt, $sum, $pmuser_id) {
		$data = array();

		$query = "
			select
				dor.DrugShipment_id,
				dor.Drug_id,
				dor.Org_id,
				dor.SubAccountType_id,
				dor.Okei_id,
				dor.DrugOstatRegistry_Cost
			from
				v_DrugOstatRegistry dor with(nolock)
			where
				dor.DrugOstatRegistry_id = :DrugOstatRegistry_id;
		";
		$result = $this->db->query($query, array(
			'DrugOstatRegistry_id' => $ost_id
		));
		if (is_object($result)) {
			$res = $result->result('array');
			$data = $res[0];
		}

		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec xp_DrugOstatRegistry_count
				@Contragent_id = NULL,
				@Org_id = :Org_id,
				@DrugShipment_id = :DrugShipment_id,
				@Drug_id = :Drug_id,
				@PrepSeries_id = NULL, -- используется в складском учете. При учете обязательств по контрактам не используется.
				@SubAccountType_id = :SubAccountType_id, -- субсчёт доступно
				@Okei_id = :Okei_id,
				@DrugOstatRegistry_Kolvo = :DrugOstatRegistry_Kolvo,
				@DrugOstatRegistry_Sum = :DrugOstatRegistry_Sum,
				@DrugOstatRegistry_Cost = :DrugOstatRegistry_Cost,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'DrugShipment_id' => $data['DrugShipment_id'],
			'Drug_id' => $data['Drug_id'],
			'SubAccountType_id' => $data['SubAccountType_id'],
			'Org_id' => $org_id,
			'DrugOstatRegistry_Kolvo' => $cnt,
			'DrugOstatRegistry_Sum' => $sum,
			'Okei_id' => $data['Okei_id'],
			'DrugOstatRegistry_Cost' => $data['DrugOstatRegistry_Cost'],
			'pmUser_id' => $pmuser_id
		);

		$result = $this->db->query($query, $queryParams);
		if ( is_object($result) ) {
			$res = $result->result('array');
			if (!empty($res[0]['Error_Msg'])) {
				return 0;
			}
		}

		return $cnt;
	}

	/**
	 *  Исполнение распоряжения на выдачу разнарядки (в данный момент - запись остатков на субсчет поставщиков)
	 *  Если функция execute будет работать корректно, данную функцию необходимо удалить
	 */
	function execute_old($data) {
		//cтартуем транзакцию
		//$this->db->trans_begin();

		//получаем информацию о остатках
		$query = "
			declare
				@SubAccountType_id bigint,
				@MinzdravOrg_id bigint,
				@WhsDocumentOrderAllocation_id bigint = :WhsDocumentOrderAllocation_id;

			set @SubAccountType_id = (select top 1 SubAccountType_id from SubAccountType with(nolock) where SubAccountType_Code = 1);

			set @MinzdravOrg_id = (
				select top 1
					Org_id
				from
					v_Org o with(nolock)
					left join OrgType ot with (nolock)	on ot.OrgType_id = o.OrgType_id
				where
					o.Org_Nick = 'минздрав' and
					ot.OrgType_Code = 13 --ТОУЗ
			);

			with mo_allocation as (
				select
					wdoa.Org_id as org,
					wdoad.Drug_id,
					wdoad.WhsDocumentOrderAllocationDrug_Kolvo as kolvo
				from
					v_WhsDocumentOrderAllocation wdoa with (nolock)
					left join v_WhsDocumentOrderAllocationDrug wdoad with (nolock) on wdoad.WhsDocumentOrderAllocation_id = wdoa.WhsDocumentOrderAllocation_id
				where
					wdoa.WhsDocumentUc_pid = @WhsDocumentOrderAllocation_id
			)
			select
				dor.Org_id as MinzdravOrg_id, --ид организации с которой будем списывать остатки (минздрав)
				dor.DrugShipment_id,--Партия
				dor.Drug_id,
				dor.PrepSeries_id,
				dor.SubAccountType_id,--Субсчет
				dor.Okei_id,--Единица измерения
				dor.DrugOstatRegistry_Kolvo,
				dor.DrugOstatRegistry_Sum,
				wds.Org_sid as SupplierOrg_id, --ид организации на которую будем перечислять остатки (поставщик)
				wdord.WhsDocumentOrderAllocationDrug_Kolvo as kolvo,
				((wdord.WhsDocumentOrderAllocationDrug_Kolvo/dor.DrugOstatRegistry_Kolvo)*dor.DrugOstatRegistry_Sum) as [sum],
				replace(replace((
					select
						cast(org as varchar)+
						'-'+
						cast(kolvo as varchar)+
						'-'+
						cast((dor.DrugOstatRegistry_Sum/dor.DrugOstatRegistry_Kolvo)*kolvo as varchar)+
						';' as 'data()'
					from
						mo_allocation with(nolock)
					where
						mo_allocation.Drug_id = wdord.Drug_id
					for xml path('')
				)+';;', ';;;', ''), ';;', '') as lpu_data
			from
				v_WhsDocumentOrderAllocationDrug wdord with (nolock)
				left join v_WhsDocumentOrderAllocation wdoa with (nolock) on wdoa.WhsDocumentOrderAllocation_id = wdord.WhsDocumentOrderAllocation_id
				left join rls.v_Drug d with (nolock) on d.Drug_id = WDORD.Drug_id
				left join v_WhsDocumentSupply wds with (nolock) on wds.WhsDocumentUc_id = wdord.WhsDocumentUc_pid
				left join v_DrugShipment ds with (nolock) on ds.WhsDocumentSupply_id = wds.WhsDocumentSupply_id
				inner join v_DrugOstatRegistry dor with (nolock) on
					dor.Drug_id = WDORD.Drug_id and
					dor.DrugShipment_id = ds.DrugShipment_id and
					dor.Org_id = @MinzdravOrg_id and
					dor.SubAccountType_id = @SubAccountType_id
			where
				wdord.WhsDocumentOrderAllocation_id = @WhsDocumentOrderAllocation_id;
		";
		$queryParams = array(
			'WhsDocumentOrderAllocation_id' => $data['WhsDocumentOrderAllocation_id']
		);
		$result = $this->db->query($query, $queryParams);
		if ( is_object($result) ) {
			$ostat_array = $result->result('array');
		} else {
			//$this->db->trans_rollback();
			return array(0 => array('Error_Msg' => 'Ошибка запроса получения данных из разнарядки'));
		}

		//для каждой строки $ostat_array дважды вызываем xp_DrugOstatRegistry_count, для списания и зачисления
		foreach ($ostat_array as $ostat_row) {
			//зачисляем ЛС на счет поставщика
			$query = "
				declare
					@ErrCode int,
					@ErrMessage varchar(4000);
				exec xp_DrugOstatRegistry_count
					@Contragent_id = NULL,
					@Org_id = :Org_id,
					@DrugShipment_id = :DrugShipment_id,
					@Drug_id = :Drug_id,
					@PrepSeries_id = NULL, -- используется в складском учете. При учете обязательств по контрактам не используется.
					@SubAccountType_id = :SubAccountType_id, -- субсчёт доступно
					@Okei_id = :Okei_id,
					@DrugOstatRegistry_Kolvo = :DrugOstatRegistry_Kolvo,
					@DrugOstatRegistry_Sum = :DrugOstatRegistry_Sum,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";

			$queryParams = array(
				'Org_id' => $ostat_row['SupplierOrg_id'],
				'DrugShipment_id' => $ostat_row['DrugShipment_id'],
				'Drug_id' => $ostat_row['Drug_id'],
				'SubAccountType_id' => $ostat_row['SubAccountType_id'],
				'Okei_id' => $ostat_row['Okei_id'],
				'DrugOstatRegistry_Kolvo' => $ostat_row['kolvo'],
				'DrugOstatRegistry_Sum' => $ostat_row['sum'],
				'pmUser_id' => $data['pmUser_id']
			);
			$result = $this->db->query($query, $queryParams);

			if ( is_object($result) ) {
				$res = $result->result('array');
				if (!empty($res[0]['Error_Msg'])) {
					//$this->db->trans_rollback();
					return array(0 => array('Error_Msg' => 'Ошибка редактирования регистра остатков'));
				} else {
					//списываем ЛС со счета минздрава
					//запрос тотже, меняем только часть параметров
					$queryParams['Org_id'] = $ostat_row['MinzdravOrg_id'];
					$queryParams['DrugOstatRegistry_Kolvo'] = $ostat_row['kolvo']*(-1);
					$queryParams['DrugOstatRegistry_Sum'] = $ostat_row['sum']*(-1);
					$result = $this->db->query($query, $queryParams);
					if ( is_object($result) ) {
						$res = $result->result('array');
						if (!empty($res[0]['Error_Msg'])) {
							//$this->db->trans_rollback();
							return array(0 => array('Error_Msg' => 'Ошибка редактирования регистра остатков'));
						}
					} else {
						//$this->db->trans_rollback();
						return array(0 => array('Error_Msg' => 'Ошибка запроса редактирования регистра остатков'));
					}

					//зачисляем остатки на счет МО
					if (!empty($ostat_row['lpu_data'])) {
						$lpu_arr = preg_split('/;/', $ostat_row['lpu_data']);
						foreach($lpu_arr as $lpu_data) {
							$lpu_data = preg_split('/-/', $lpu_data);
							//запрос тотже, меняем только часть параметров
							$queryParams['Org_id'] = $lpu_data[0];
							$queryParams['DrugOstatRegistry_Kolvo'] = $lpu_data[1];
							$queryParams['DrugOstatRegistry_Sum'] = $lpu_data[2];
							$result = $this->db->query($query, $queryParams);
							if ( is_object($result) ) {
								$res = $result->result('array');
								if (!empty($res[0]['Error_Msg'])) {
									//$this->db->trans_rollback();
									return array(0 => array('Error_Msg' => 'Ошибка редактирования регистра остатков'));
								}
							} else {
								//$this->db->trans_rollback();
								return array(0 => array('Error_Msg' => 'Ошибка запроса редактирования регистра остатков'));
							}
						}
					}
				}
			} else {
				//$this->db->trans_rollback();
				return array(0 => array('Error_Msg' => 'Ошибка запроса редактирования регистра остатков'));
			}
		}

		//$this->db->trans_commit();
		return array(0 => array('Error_Msg' => ''));
	}

	/**
	 *  Удаление
	 */
	function delete($data) {
		//удаляем потомков
		$q = "
			select
				WhsDocumentUc_id
			from
				v_WhsDocumentOrderAllocation with(nolock)
			where
				WhsDocumentUc_pid = :WhsDocumentUc_id;
		";
		$r = $this->db->query($q, array(
			'WhsDocumentUc_id' => $data['WhsDocumentUc_id']
		));
		if ( is_object($r) ) {
			$doc_array = $r->result('array');
			foreach($doc_array as $doc) {
				$result = $this->delete(array(
					'pmUser_id' => $data['pmUser_id'],
					'WhsDocumentUc_id' => $doc['WhsDocumentUc_id']
				));
				if (isset($result[0]) && isset($result[0]['Error_Msg']) && !empty($result[0]['Error_Msg'])) {
					return $result;
				}
			}
		} else {
			return array(array('Error_Msg' => 'Ошибка при получении списка потомков.'));
		}

		$q = "
			delete from --удаление медикаментов сводной разнарядки
				WhsDocumentOrderAllocationDrug
			where
				WhsDocumentOrderAllocation_id = :WhsDocumentOrderAllocation_id;
		";
		$r = $this->db->query($q, array(
			'WhsDocumentOrderAllocation_id' => $data['WhsDocumentUc_id']
		));

		$q = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec dbo.p_WhsDocumentOrderAllocation_del
				@WhsDocumentOrderAllocation_id = :WhsDocumentOrderAllocation_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$r = $this->db->query($q, array(
			'pmUser_id' => $data['pmUser_id'],
			'WhsDocumentOrderAllocation_id' => $data['WhsDocumentUc_id']
		));
		if ( is_object($r) ) {
			return $r->result('array');
		} else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса на удаление.'));
		}
	}

	/**
	 *  Получение списка сводных заявок для распоряжения на выдачу разнарядки.
	 */
	function loadSvodDrugRequestList($filter) {
		$where = array();
		if (isset($filter['DrugRequest_id']) && $filter['DrugRequest_id'] > 0) {
			$q = "
				select
					dr.DrugRequest_id,
					dr.DrugRequest_Name
				from
					DrugRequest dr with (nolock)
				where
					DrugRequest_id = :DrugRequest_id;
			";
		} else {
			$q = "
				select
					dr.DrugRequest_id,
					dr.DrugRequest_Name
				from
					DrugRequest dr with (nolock)
					left join DrugRequestCategory drc with (nolock) on drc.DrugRequestCategory_id = dr.DrugRequestCategory_id
					left join DrugRequestPurchase drp with (nolock) on drp.DrugRequest_id = dr.DrugRequest_id
					left join DrugRequest dr2 with (nolock) on dr2.DrugRequest_id = drp.DrugRequest_lid
					left join DrugRequestPeriod drpr with (nolock) on drpr.DrugRequestPeriod_id = dr2.DrugRequestPeriod_id
				where
					drc.DrugRequestCategory_SysNick = 'svod'
					--Временно отключил фильтр по дате https://redmine.swan.perm.ru/issues/33432
					--and drpr.DrugRequestPeriod_endDate >= cast(dbo.tzGetDate() as date)
				group by
					dr.DrugRequest_id, dr.DrugRequest_Name;
			";
		}
		$result = $this->db->query($q, $filter);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *  Получение списка распоряжений на выдачу разнарядки МО для документа распределения ЛС по аптекам.
	 */
	function loadSourceWhsDocumentUcCombo($filter) {
		$where = '';

		if (!empty($filter['WhsDocumentUc_id'])) {
			$where .= " and wdoa.WhsDocumentUc_id = :WhsDocumentUc_id";
		} else if (!empty($filter['query'])) {
			$where .= " and wdoa.WhsDocumentUc_Name like '%'+:query+'%'";
		}

		$q = "
			select
				wdoa.WhsDocumentUc_id,
				wdoa.WhsDocumentUc_Name,
				wdoa.DrugFinance_id,
				wdoa.WhsDocumentCostItemType_id
			from
				v_WhsDocumentOrderAllocation wdoa with (nolock)
				left join v_WhsDocumentType wdt with(nolock) on wdt.WhsDocumentType_id = wdoa.WhsDocumentType_id
 			where
				wdt.WhsDocumentType_Code = 7 --Распоряжение на выдачу разнарядки на выписку рецептов
				{$where};
		";
		$result = $this->db->query($q, $filter);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *  Получение списка получателей для определенного распоряжения на выдачу разнарядки МО.
	 */
	function loadSourceWhsDocumentUcOrgCombo($filter) {
		if (isset($filter['Org_id']) && $filter['Org_id'] > 0) {
			$q = "
				select
					Org_id,
					Org_Name
				from
					v_Org with(nolock)
				where
					Org_id = :Org_id;
			";
		} else {
			$q = "
				select distinct
					o.Org_id,
					o.Org_Name
				from
					v_WhsDocumentOrderAllocationDrug wdoad with (nolock)
					left join v_WhsDocumentSupply wds with (nolock) on wds.WhsDocumentUc_id = wdoad.WhsDocumentUc_pid
					inner join v_Org o with (nolock) on o.Org_id = wds.Org_sid --Поставщик
				where
					wdoad.WhsDocumentOrderAllocation_id = :WhsDocumentUc_id;
			";
		}

		$result = $this->db->query($q, $filter);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	
    /**
     * Добавление данных в номенклатурный справочник
     * $object - наименование сущности
     * $id - идентификатор сущности
     *
     * возвращает id записи из таблицы справочника
     */
    function addNomenData($object, $id, $data) {
        $this->load->model('RlsDrug_model', 'RlsDrug_model');

        if (empty($object) || $id <= 0) {
            return null;
        }

        $code_tbl = null;
        $code_id = null;

        $object_array = array(
            'Drug' => array('code_tbl' => 'DrugNomen'),
            'TRADENAMES' => array('code_tbl' => 'DrugTorgCode'),
            'ACTMATTERS' => array('code_tbl' => 'DrugMnnCode'),
            'DrugComplexMnn' => array('code_tbl' => 'DrugComplexMnnCode')
        );

        if (!empty($object_array[$object])) {
            $code_tbl = $object_array[$object]['code_tbl'];
			
			$mz_id = $this->getFirstRowFromQuery("select dbo.GetMinzdravDloOrgId() as Org_id");
			$mz_id = count($mz_id) ? $mz_id['Org_id'] : null;

            if ($object == 'Drug') { //для медикамента нужно предварительно добавить код группировочного торгового, так как этот код участвует в формировании кода медикамента
                $dpfc_id = $this->addDrugPrepFasCodeByDrugId(array(
                    'Drug_id' => $id,
                    'Org_id' => $mz_id,
                    'pmUser_id' => $data['pmUser_id']
                ));
            }

            // Ищем запись в таблице номенклатурного справочника
            $query = "
                select
                    {$code_tbl}_id as code_id
                from
                    rls.v_{$code_tbl}
                where
                    {$object}_id = :id;
            ";
            $code_id = $this->getFirstResultFromQuery($query, array('id' => $id));

            if (empty($code_id)) { //добавляем запись в номенклатурный справочник
                //получаем новый код
                $new_code_data = $this->generateCodeForObject(array(
                    'Object' => $code_tbl,
                    'Drug_id' => $object == 'Drug' ? $id : null
                ));
                $new_code = !empty($new_code_data[0]) && !empty($new_code_data[0][$code_tbl.'_Code']) ? $new_code_data[0][$code_tbl.'_Code'] : null;

                if (!empty($new_code)) {
                    if ($object == 'Drug') {
                        //получаем информацию о медикаменте
						$query = "
							select
								d.Drug_Name,
								d.DrugTorg_Name,
								d.DrugTorg_id as Tradenames_id,
								dcmn.ActMatters_id as Actmatters_id,
								dcm.DrugComplexMnn_id,
								A.STRONGGROUPID,
								A.NARCOGROUPID,
								P.NTFRID as CLSNTFR_ID,
								d.PrepType_id
							from
								rls.v_Drug d with (nolock)
								left join rls.v_DrugComplexMnn dcm with (nolock) on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
								left join rls.DrugComplexMnnName dcmn with (nolock) on dcmn.DrugComplexMnnName_id = dcm.DrugComplexMnnName_id
								left join rls.v_ACTMATTERS A with (nolock) on A.Actmatters_id = dcmn.ActMatters_id
								left join rls.Prep P with (nolock) on P.Prep_id = d.DrugPrep_id
							where
								Drug_id = :id
						";

                        $drug_data = $this->getFirstRowFromQuery($query, array('id' => $id));

                        if (is_array($drug_data)) {
                            //добавляем запись в таблицу
                            $query = "
								declare
                                    @{$code_tbl}_id bigint,
									@ErrCode int,
									@ErrMessage varchar(4000);

								exec rls.p_DrugNomen_ins
                                    @{$code_tbl}_id = @{$code_tbl}_id output,
                                    @{$object}_id = :{$object}_id,
                                    @{$code_tbl}_Code = :{$code_tbl}_Code,
									@DrugNomen_Name = :DrugNomen_Name,
									@DrugNomen_Nick = :DrugNomen_Nick,
									@DrugTorgCode_id = :DrugTorgCode_id,
									@DrugMnnCode_id = :DrugMnnCode_id,
									@DrugComplexMnnCode_id = :DrugComplexMnnCode_id,
									@PrepClass_id = :PrepClass_id,
									@Region_id = null,
									@pmUser_id = :pmUser_id,
									@Error_Code = @ErrCode output,
									@Error_Message = @ErrMessage output;
									select @DrugNomen_id as DrugNomen_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
                            ";

                            $params = array();
                            $params['DrugNomen_Name'] = $drug_data['Drug_Name'];
                            $params['DrugNomen_Nick'] = $drug_data['DrugTorg_Name'];
                            $params['DrugTorgCode_id'] = $drug_data['Tradenames_id'] > 0 ? $this->addNomenData('TRADENAMES', $drug_data['Tradenames_id'], $data) : null;
                            $params['DrugMnnCode_id'] = $drug_data['Actmatters_id'] > 0 ? $this->addNomenData('ACTMATTERS', $drug_data['Actmatters_id'], $data) : null;
                            $params['DrugComplexMnnCode_id'] = $drug_data['DrugComplexMnn_id'] > 0 ? $this->addNomenData('DrugComplexMnn', $drug_data['DrugComplexMnn_id'], $data) : null;
							$params['PrepClass_id'] = $this->RlsDrug_model->getDrugPrepClassId($drug_data);
                            $params[$object.'_id'] = $id;
                            $params[$code_tbl.'_Code'] = $new_code;
                            $params['pmUser_id'] = $data['pmUser_id'];

                            $result = $this->getFirstRowFromQuery($query, $params);
                            if (!empty($result)) {
                                $code_id = $result[$code_tbl.'_id'];
								
								// Создаём связь позиций номенклатуры и организации
								$query = "
									declare
										@DrugNomenOrgLink_id bigint = :DrugNomenOrgLink_id,
										@ErrCode int,
										@ErrMessage varchar(4000);

									exec rls.p_DrugNomenOrgLink_ins
										@DrugNomenOrgLink_id = @DrugNomenOrgLink_id output,
										@Org_id = :Org_id,
										@DrugNomen_id = :DrugNomen_id,
										@DrugNomenOrgLink_Code = :DrugNomenOrgLink_Code,
										@pmUser_id = :pmUser_id,
										@Error_Code = @ErrCode output,
										@Error_Message = @ErrMessage output;
									select @DrugNomenOrgLink_id as DrugNomenOrgLink_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
								";
								
								$dnol_code = $this->generateCodeForObject(array(
									'Object' => 'DrugNomenOrgLink',
									'Drug_id' => $id,
									'Org_id' => $mz_id
								));
								$dnol_code = $dnol_code && isset($dnol_code[0]['DrugNomenOrgLink_Code']) ? $dnol_code[0]['DrugNomenOrgLink_Code'] : null;
								$params = array();
								$params['DrugNomenOrgLink_id'] = null;
								$params['DrugNomen_id'] = $code_id;
								$params['Org_id'] = $mz_id;
								$params['DrugNomenOrgLink_Code'] = $dnol_code;
								$params['pmUser_id'] = $data['pmUser_id'];
								$this->db->query($query, $params);
                            }
                        }
                    } else {
                        //добавляем запись в таблицу
                        $query = "
                            declare
                                @{$code_tbl}_id bigint,
                                @ErrCode int,
                                @ErrMessage varchar(4000);
                            exec rls.p_{$code_tbl}_ins
                                @{$code_tbl}_id = @{$code_tbl}_id output,
                                @{$object}_id = :{$object}_id,
                                @{$code_tbl}_Code = :{$code_tbl}_Code,
                                @Region_id = null,
                                @pmUser_id = :pmUser_id,
                                @Error_Code = @ErrCode output,
                                @Error_Message = @ErrMessage output;
                                select @{$code_tbl}_id as {$code_tbl}_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
                        ";

                        $params = array();
                        $params[$object.'_id'] = $id;
                        $params[$code_tbl.'_Code'] = $new_code;
                        $params['pmUser_id'] = $data['pmUser_id'];

                        $result = $this->getFirstRowFromQuery($query, $params);
                        if (!empty($result)) {
                            $code_id = $result[$code_tbl.'_id'];
                        }

                        if ($object == 'DrugComplexMnn') { //При добавлении в справочник комплексного МНН необходимо позаботится и о добавлении действующего вещества
                            //получаем информацию о комплексном МНН
                            $query = "
                                select
                                    DrugComplexMnnName.ActMatters_id as Actmatters_id
                                from
                                    rls.v_DrugComplexMnn with (nolock)
                                    left join rls.DrugComplexMnnName with (nolock) on DrugComplexMnnName.DrugComplexMnnName_id = v_DrugComplexMnn.DrugComplexMnnName_id
                                where
                                    DrugComplexMnn_id = :id;
                            ";
                            $dcm_data = $this->getFirstRowFromQuery($query, array('id' => $id));

                            if (!empty($dcm_data['Actmatters_id'])) {
                                $this->addNomenData('ACTMATTERS', $dcm_data['Actmatters_id'], $data);
                            }
                        }
                    }
                }
            }
        }
		//echo $code_id . ' ';
        return $code_id;
    }

	/**
	 * Добавление кода группировочного торгового наименования по идентификатору медикамента
     * Если кода для заданого медикамента еще нет, то добавляется новый
	 * Возвращает идентификатор кода
	 */
	function addDrugPrepFasCodeByDrugId($data) {
		$code_id = null;

		if (empty($data['Org_id'])) {
			$data['Org_id'] = null;
		}

        if (empty($data['DrugPrepFas_id'])) {
            $query = "
                select
                    DrugPrepFas_id
                from
                    rls.v_Drug
                where
                    Drug_id = :Drug_id;
            ";
			$data['DrugPrepFas_id'] = $this->getFirstResultFromQuery($query, array(
                'Drug_id' => $data['Drug_id']
            ));
		}

        //ищем существующую запись с кодом
        $query = "
            select top 1
                dpfc.DrugPrepFasCode_id
            from
                rls.v_DrugPrepFasCode dpfc with (nolock)
            where
                dpfc.DrugPrepFas_id = :DrugPrepFas_id and
                isnull(dpfc.Org_id, 0) = isnull(:Org_id, 0)
            order by
                dpfc.DrugPrepFasCode_id
        ";
        $result = $this->getFirstRowFromQuery($query, array(
            'DrugPrepFas_id' => $data['DrugPrepFas_id'],
            'Org_id' => $data['Org_id']
        ));
        $code_id = !empty($result['DrugPrepFasCode_id']) ? $result['DrugPrepFasCode_id'] : null;
        if (empty($code_id)) { //если код не найден, добавляем его
			$data['DrugPrepFasCode_id'] = null;
			$dpfc_code = $this->generateCodeForObject(array(
				'Object' => 'DrugPrepFasCode',
				'Drug_id' => $data['Drug_id']
			));
			$data['DrugPrepFasCode_Code'] = $dpfc_code && isset($dpfc_code[0]['DrugPrepFasCode_Code']) ? $dpfc_code[0]['DrugPrepFasCode_Code'] : null;
            $query = "
                declare
                    @DrugPrepFasCode_id bigint,
                    @ErrCode int,
                    @ErrMessage varchar(4000);
                set @DrugPrepFasCode_id = :DrugPrepFasCode_id;
                exec rls.p_DrugPrepFasCode_ins
                    @DrugPrepFasCode_id = @DrugPrepFasCode_id output,
                    @DrugPrepFas_id = :DrugPrepFas_id,
                    @Org_id = :Org_id,
                    @DrugPrepFasCode_Code = :DrugPrepFasCode_Code,
                    @pmUser_id = :pmUser_id,
                    @Error_Code = @ErrCode output,
                    @Error_Message = @ErrMessage output;
                select @DrugPrepFasCode_id as DrugPrepFasCode_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
            ";
            $result = $this->getFirstRowFromQuery($query, $data);
            if (!empty($result['DrugPrepFasCode_id'])) {
                $code_id = $result['DrugPrepFasCode_id'];
            }
        }
        return $code_id;
	}
	
	/**
	 * Получение кода
	 */
	function generateCodeForObject($data) {
		$object = $data['Object'];
        $query = "";
        $params = array();

        if (empty($data['Org_id'])) {
            $data['Org_id'] = null;
        }

        switch($object) {
            case 'DrugNomen':
            case 'DrugNomenOrgLink':
                $code = 0;
                $dpf_id = null;

                if (!empty($data['Drug_id'])) {
                    $query = "
                        select
                            d.DrugPrepFas_id,
                            dpfc.DrugPrepFasCode_Code
                        from
                            rls.v_Drug d with(nolock)
                            left join rls.DrugPrepFasCode dpfc with(nolock) on dpfc.DrugPrepFas_id = d.DrugPrepFas_id
                        where
                            d.Drug_id = :Drug_id and
                            isnull(dpfc.Org_id, 0) = isnull(:Org_id, 0)
                    ";
                    $result = $this->getFirstRowFromQuery($query, $data);

                    if (!empty($result['DrugPrepFasCode_Code'])) {
                        $code = $result['DrugPrepFasCode_Code'];
                    }
                    if (!empty($result['DrugPrepFas_id'])) {
                        $dpf_id = $result['DrugPrepFas_id'];
                    }
                }

                
                if ($object == 'DrugNomen') {
                    /*$query = "
                        select
                            '{$code}.'+cast(max(isnull(cast(p.num as bigint),0))+1 as varchar) as DrugNomen_Code
                        from
                        (
                            select
                                substring(DN.DrugNomen_Code, charindex('.', DN.DrugNomen_Code)+1, len(DN.DrugNomen_Code)) as num
                            from
                                rls.v_DrugNomen DN with(nolock)
                                left join rls.v_Drug D with(nolock) on D.Drug_id = DN.Drug_id
                            where
                                DN.DrugNomen_Code like '{$code}.%'
                            union select '0'
                        ) p
                        where
                            isnumeric(p.num) = 1
                    ";*/
                    $query = "
                        select
                            cast(max(isnull(cast(p.num as bigint),0))+1 as varchar) as DrugNomen_Code
                        from
                        (
                            select
                                substring(DN.DrugNomen_Code, charindex('.', DN.DrugNomen_Code)+1, len(DN.DrugNomen_Code)) as num
                            from
                                rls.v_DrugNomen DN with(nolock)
                                left join rls.v_Drug D with(nolock) on D.Drug_id = DN.Drug_id
                            union select '0'
                        ) p
                        where
                            isnumeric(p.num) = 1
                    ";
                }
                
                if ($object == 'DrugNomenOrgLink') {
                    $query = "
                        select
                            '{$code}.'+cast(max(isnull(cast(p.num as bigint),0))+1 as varchar) as DrugNomenOrgLink_Code
                        from
                        (
                            select
                                substring(DNOL.DrugNomenOrgLink_Code, charindex('.', DNOL.DrugNomenOrgLink_Code)+1, len(DNOL.DrugNomenOrgLink_Code)) as num
                            from
                                rls.v_DrugNomenOrgLink DNOL with(nolock)
                                left join rls.v_DrugNomen DN with (nolock) on DN.DrugNomen_id = DNOL.DrugNomen_id
                                left join rls.v_Drug D with(nolock) on D.Drug_id = DN.Drug_id
                            where
                                DNOL.DrugNomenOrgLink_Code like '{$code}.%' and
                                (
                                    :Org_id is null or
                                    DNOL.Org_id = :Org_id
                                ) and
                                (
                                    :DrugPrepFas_id is null or
                                    D.DrugPrepFas_id = :DrugPrepFas_id
                                )
                            union select '0'
                        ) p
                        where
                            isnumeric(p.num) = 1
                    ";
                }

                $params['DrugPrepFas_id'] = $dpf_id;
                $params['Org_id'] = $data['Org_id'];
                break;
            case 'DrugPrepFasCode':
                $dpf_id = null;

                if (!empty($data['Drug_id'])) {
                    $query = "
                        select
                            d.DrugPrepFas_id,
                            dpfc.DrugPrepFasCode_Code
                        from
                            rls.v_Drug d with(nolock)
                            left join rls.DrugPrepFasCode dpfc with(nolock) on dpfc.DrugPrepFas_id = d.DrugPrepFas_id
                        where
                            d.Drug_id = :Drug_id
                    ";
                    $result = $this->getFirstRowFromQuery($query, $data);

                    if (!empty($result['DrugPrepFas_id'])) {
                        $dpf_id = $result['DrugPrepFas_id'];
                    }
                }

                $query = "
                    select top 1
                        (max(isnull(cast(p.DrugPrepFasCode_Code as bigint),0))+1) as {$object}_Code
                    from (
                        select
                            dpfc.DrugPrepFasCode_Code
                        from
                            rls.v_DrugPrepFasCode dpfc with(nolock)
                        where
                            dpfc.DrugPrepFas_id = :DrugPrepFas_id and
                            isnull(dpfc.Org_id, 0) = isnull(:Org_id, 0)
                        union select '0'
                    ) p
                    where
                        len(p.DrugPrepFasCode_Code) <= 18 and
                        IsNull((
                            Select Case When CharIndex('.', p.DrugPrepFasCode_Code) > 0 Then 0 Else 1 End
                            Where IsNumeric(p.DrugPrepFasCode_Code + 'e0') = 1
                        ), 0) = 1
                ";

                $params['DrugPrepFas_id'] = $dpf_id;
                $params['Org_id'] = $data['Org_id'];
                break;
            default:
                $query = "
                    select top 1
                        (max(isnull(cast(DN.{$object}_Code as bigint),0))+1) as {$object}_Code
                    from
                        rls.v_{$object} DN with(nolock)
                    where
                        len(DN.{$object}_Code) <= 18 and
                        IsNull((
                            Select Case When CharIndex('.', DN.{$object}_Code) > 0 Then 0 Else 1 End
                            Where IsNumeric(DN.{$object}_Code + 'e0') = 1
                        ), 0) = 1
                ";
                break;
        }

		$result = $this->db->query($query, $params);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}
}