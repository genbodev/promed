
<?php	defined('BASEPATH') or die ('No direct script access allowed');

class ReceptUpload_model extends swPgModel {

	var $objectName = "";
	var $objectKey = "";
	var $scheme = "dbo";

	protected $schema = "dbo";  //региональная схема

	/**
	 * Конструктор.
	 */
	function __construct() {
		parent::__construct();

		//установка региональной схемы
		$config = get_config();
		$this->schema = $config['regions'][getRegionNumber()]['schema'];
	}
	
	/**
	 *	Устанавливает значение схемы
	 */
	function setScheme($scheme) {
		$this->scheme = $scheme;
		return $this;
	}
	
	/**
	 *	Устанавливает значение объекта БД и значение ключа строки с которой работаем в дальнейшем
	 */
	function setObject($objectName) {
		$this->objectName = $objectName;
		return $this;
	}
	
	/**
	 *	Устанавливает значение ключа строки с которой работаем в дальнейшем
	 */
	function setRow($objectKey) {
		$this->objectKey = $objectKey;
		return $this;
	}
	
	/**
	 *	Получение данных записи таблицы по идентификатору
	 */
	private function getRecordById($id) {
		if( !isset($id) || empty($id) || $id < 0 ) {
			return null;
		}
		
		$query = "
			select
				table_name as name
			from INFORMATION_SCHEMA.views
			where table_name ilike 'v_{$this->objectName}'
		";
		$result = $this->db->query($query);
		if ( !is_object($result) ) {
			return false;
		}
		$result = $result->result('array');
		if( count($result) > 0 ) {
			$from = $this->scheme.".v_{$this->objectName}";
		} else {
			$from = $this->scheme.".{$this->objectName}";
		}
		
		$query = "
			select
				*
			from
				{$from}
			where
				{$this->objectName}_id = {$id}
			limit 1
		";
		$result = $this->db->query($query);
		if ( is_object($result) ) {
			$result = $result->result('array');
			return isset($result[0]) ? $result[0] : null;
		} else {
			return false;
		}
	}

	/**
	 *	Получает данные накладных для экспорта в DBF
	 */	
	function getInvoiceData($data) {
		$query = "
			select
				I.Invoice_MethodType as \"METHOD\",
				I.Invoice_id as \"DOC_CODE\", -- возможно нужно хранить и DOC_CODE в бд и его выгружать, но поля под DOC_CODE нет.
				I.Invoice_DocN as \"DOC_N\",
				I.Invoice_DTypeCode as \"DTYPE_CODE\",
				to_char(I.Invoice_DateDoc, 'YYYYMMDD') as \"DATE_DOC\",
				I.Invoice_CFinl as \"C_FINL\",
				I.Invoice_gk as \"GK\",
				I.Invoice_SuplCode as \"SUPL_COD\",
				I.Invoice_SuplOgrn as \"SUPL_OGRN\",
				I.Invoice_RecipCode as \"RECIP_COD\",
				I.Invoice_RecipOgrn as \"RECIP_OGRN\",
				'' as \"FACTUR\", -- тоже нет в бд, но есть в дбф..
				I.Invoice_AktNum as \"AKT\",
				IST.InvoiceStatus_Name as \"STATUS\",
				IC.InvoiceCause_Name as \"CAUSE\"
			from
				raw.v_Invoice I
				left join raw.v_InvoiceCause IC on IC.InvoiceCause_id = I.InvoiceCause_id
				left join raw.v_InvoiceStatus IST on IST.InvoiceStatus_id = I.InvoiceStatus_id
			where
				I.ReceptUploadLog_id = :ReceptUploadLog_id
		";
		
		$result = $this->db->query($query, $data);
		
		if ( is_object($result) ) {
			return $result->result('array');
		}
		
		return array();
	}
	
	/**
	 *	Получает данные медикаментов накладных для экспорта в DBF
	 */	
	function getInvoiceDrugData($data) {
		$query = "
			select
				ID.Invoice_id as \"DOC_CODE\", -- возможно нужно хранить и DOC_CODE в бд и его выгружать, но поля под DOC_CODE нет.
				ID.InvoiceDrug_NomkLs as \"NOMK_LS\",
				ID.InvoiceDrug_KoAll as \"KO_ALL\",
				ID.InvoiceDrug_Price as \"PRICE\",
				ID.InvoiceDrug_Sum as \"SUM\",
				ID.InvoiceDrug_NDS as \"NDS\",
				ID.InvoiceDrug_SumNDS as \"SUM_NDS\",
				ID.InvoiceDrug_Series as \"SERIES\",
				ID.InvoiceDrug_SertReg as \"REGNUM\",
				ID.InvoiceDrug_EAN13 as \"EAN\",
				IST.InvoiceStatus_Name as \"STATUS\",
				IC.InvoiceCause_Name as \"CAUSE\"
			from
				raw.v_Invoice I
				left join raw.v_InvoiceDrug ID on ID.Invoice_Id = I.Invoice_id
				left join raw.v_InvoiceCause IC on IC.InvoiceCause_id = ID.InvoiceCause_id
				left join raw.v_InvoiceStatus IST on IST.InvoiceStatus_id = ID.InvoiceStatus_id
			where
				I.ReceptUploadLog_id = :ReceptUploadLog_id
		";
		
		$result = $this->db->query($query, $data);
		
		if ( is_object($result) ) {
			return $result->result('array');
		}
		
		return array();
	}
	
	/**
	 *	Возвращает параметры процедуры
	 */
	function getParamsByProcedure($data) {
		$query = "
                select 
                  pg_catalog.pg_get_function_arguments(p.oid) as arguments
                from 
                  pg_catalog.pg_proc p
                  left join pg_catalog.pg_namespace n on n.oid = p.pronamespace
                where 
                  n.nspname = lower(:scheme)
                and 
                  p.proname = lower(:proc)
                limit 1
	      
	    ";
		$result = $this->db->query($query, array(
			'proc' => strtolower($data['proc']),
			'scheme' => strtolower($data['scheme'])
		));

		if ( is_object($result) ) {
			$arguments = explode( ',',$result->result('array')[0]['arguments']);
			$params = [];
			foreach ($arguments as $argument) {
				$argument = explode(' ', $argument);
				if($argument[1] === 'OUT') continue;
				if($argument[1] === 'INOUT') {
					$params[]['name'] = $argument[2];
					continue;
				}

				$params[]['name'] = $argument[1];
			}

			return $params;
		} else {
			return false;
		}
	}
	
	/**
	 *	Устанавливает значение поля объекта БД
	 */
	function setValue($field, $value) {
		if( empty($this->objectName) || empty($this->objectKey) )
			return false;
		
		$procedure = "p_" . $this->objectName . "_upd";
		$params = $this->getParamsByProcedure(array('scheme' => $this->scheme, 'proc' => $procedure));

		$query = "
            select 
                Error_Code as \"Error_Code\", 
                Error_Message as \"Error_Msg\"
             from {$this->scheme}." . $procedure ."(
        ";

		foreach($params as $k=>$param) {
			$query .= "\t\t\t\t" . $param['name'] . " := :".$param['name'];
			$query .= ( count($params) == ++$k ? "" : "," ) . "\n";
		}
		$query .= ")";

		$record = $this->getRecordById($this->objectKey);
		if( !is_array($record) ) {
			return false;
		}

		$record[$this->objectName.'_id'] = $this->objectKey;
		$sp = getSessionParams();
		$record['pmUser_id'] = $sp['pmUser_id'];

		if( array_key_exists($field, $record) ) {
			$record[$field] = $value;
			$result = $this->db->query($query, $record);
			if ( is_object($result) ) {
				return $result->result('array');
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 *	Получает значение поля объекта БД
	 */
	function getValue($field) {
		if( empty($this->objectName) || empty($this->objectKey) )
			return false;

		$record = $this->getRecordById($this->objectKey);
		if( !is_array($record) ) {
			return false;
		}

		if( array_key_exists($field, $record) ) {
			return $record[$field];
		} else {
			return false;
		}
	}
	
	/**
	 * Получение данных о загрузке.
	 */
	function getReceptUploadLogData($data) {
		$query = "
			select
				RUL.ReceptUploadLog_id as \"ReceptUploadLog_id\",
				RUS.ReceptUploadStatus_Code as \"ReceptUploadStatus_Code\",
				RUL.ReceptUploadLog_InFail as \"ReceptUploadLog_InFail\",
				Ct.Contragent_Name as \"Contragent_Name\",
				Ct.Org_id as \"Org_id\",
				RUT.ReceptUploadType_Code as \"ReceptUploadType_Code\",
				to_char(dbo.tzGetDate(), 'dd.mm.yyyy') as \"curDT\",
				to_char(cast(RUL.ReceptUploadLog_setDT as date), 'dd.mm.yyyy') as \"ReceptUploadLog_setDT\"
			from
				v_ReceptUploadLog RUL
				left join v_ReceptUploadStatus RUS on RUS.ReceptUploadStatus_id = RUL.ReceptUploadStatus_id
				left join v_ReceptUploadType RUT on RUT.ReceptUploadType_id = RUL.ReceptUploadType_id
				left join v_Contragent Ct on Ct.Contragent_id = RUL.Contragent_id
			where
				RUL.ReceptUploadLog_id = :ReceptUploadLog_id
		";
		
		$result = $this->db->query($query, $data);
		
		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) > 0) {
				return $resp[0];
			}
		}
		
		return false;
	}

	/**
	 * Получение различных суммы для таблицы "Результаты экспертизы данных".
	 */
	function getInvoiceResultData($data) {
		$response = array(
			'countinvoices_all' => '',
			'countinvoicedrugs_all' => '',
			'countsum_all' => '',
			'countinvoices_accepted' => '',
			'countinvoicedrugs_accepted' => '',
			'countsum_accepted' => '',
			'countinvoices_notaccepted' => '',
			'countinvoicedrugs_notaccepted' => '',
			'countsum_notaccepted' => ''
		);
		
		$query = "
			select
				COUNT(I.Invoice_id) as \"countinvoices_all\",
				SUM(case when I.InvoiceCause_id IS NULL then 1 else 0 end) as \"countinvoices_accepted\",
				SUM(case when I.InvoiceCause_id IS NULL then 0 else 1 end) as \"countinvoices_notaccepted\"
			from
				raw.v_Invoice I
			where
				I.ReceptUploadLog_id = :ReceptUploadLog_id
		";
		
		$result = $this->db->query($query, $data);
		
		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) > 0) {
				$response = array_merge($response,$resp[0]);
			}
		}
		
		$query = "
			select
				COUNT(ID.InvoiceDrug_id) as \"countinvoicedrugs_all\",
				SUM(ID.InvoiceDrug_SumNDS) as \"countsum_all\",
				SUM(case when I.InvoiceCause_id IS NULL then 1 else 0 end) as \"countinvoicedrugs_accepted\",
				SUM(case when I.InvoiceCause_id IS NULL then ID.InvoiceDrug_SumNDS else 0 end) as \"countsum_accepted\",
				SUM(case when I.InvoiceCause_id IS NULL then 0 else 1 end) as \"countinvoicedrugs_notaccepted\",
				SUM(case when I.InvoiceCause_id IS NULL then 0 else ID.InvoiceDrug_SumNDS end) as \"countsum_notaccepted\"
			from
				raw.v_Invoice I
				inner join raw.v_InvoiceDrug ID on ID.Invoice_id = I.Invoice_id
			where
				I.ReceptUploadLog_id = :ReceptUploadLog_id
		";
		
		$result = $this->db->query($query, $data);
		
		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) > 0) {
				$response = array_merge($response,$resp[0]);
			}
		}
		
		$query = "
			select
				rul.ReceptUploadLog_Act as \"ReceptUploadLog_Act\"
			from
				v_ReceptUploadLog rul
			where
				rul.ReceptUploadLog_id = :ReceptUploadLog_id
		";
		
		$result = $this->db->query($query, $data);
		
		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) > 0) {
				$response = array_merge($response,$resp[0]);
			}
		}
		
		return $response;
	}

	/**
	 * Получение результатов.
	 */
	function getReceptUploadLogExpertResults($data) {
		$query = "
			select
				coalesce(MAX(WDCIT.WhsDocumentCostItemType_Name), '') as \"programm\",
				COUNT(RR.RegistryRecept_id) as \"count_all\",
				cast(SUM(cast(coalesce(RR.RegistryRecept_DrugKolvo, 0) * coalesce(RR.RegistryRecept_Price, 0) as decimal(12,2))) as varchar) as \"count_all_sum\",
				COUNT(case when RSF.ReceptStatusFLKMEK_Code in (3)
					then 1
					else null
				end) as \"count_accepted\",
				cast(SUM(case when RSF.ReceptStatusFLKMEK_Code in (3)
					then cast(coalesce(RR.RegistryRecept_DrugKolvo, 0) * coalesce(RR.RegistryRecept_Price, 0) as decimal(12,2))
					else 0
				end) as varchar) as \"count_accepted_sum\",
				COUNT(case when RSF.ReceptStatusFLKMEK_Code in (3) then null else 1 end) as \"count_notaccepted\",
				cast(SUM(case when RSF.ReceptStatusFLKMEK_Code in (3)
					then 0
					else cast(coalesce(RR.RegistryRecept_DrugKolvo, 0) * coalesce(RR.RegistryRecept_Price, 0) as decimal(12,2))
				end) as varchar) as \"count_notaccepted_sum\"
			from
				{$this->schema}.v_RegistryRecept RR
				left join {$this->schema}.ReceptStatusFLKMEK RSF on RSF.ReceptStatusFLKMEK_id = RR.ReceptStatusFLKMEK_id
				left join lateral(
					select WhsDocumentCostItemType_id from v_WhsDocumentSupply where WhsDocumentUc_Num = RR.RegistryRecept_SupplyNum limit 1
				) WDS on true
				left join v_WhsDocumentCostItemType WDCIT on WDCIT.WhsDocumentCostItemType_id = WDS.WhsDocumentCostItemType_id
			where
				RR.ReceptUploadLog_id = :ReceptUploadLog_id
			group by 
				WDCIT.WhsDocumentCostItemType_id
		";
		
		$result = $this->db->query($query, $data);
		
		if ( is_object($result) ) {
			return $result->result('array');
		}
		
		return array();
	}

	/**
	 * Чтение ошибок.
	 */
	function getReceptUploadLogErrors($data) {
		$query = "
			select
				MAX(RRET.RegistryReceptErrorType_Type) as \"RegistryReceptErrorType_Type\",
				MAX(RRET.RegistryReceptErrorType_Name) as \"RegistryReceptErrorType_Name\",
				COUNT(distinct RR.RegistryRecept_id) as \"quantity\"
			from
				{$this->schema}.v_RegistryRecept RR
				inner join {$this->schema}.v_RegistryReceptError RRE on RRE.RegistryRecept_id = RR.RegistryRecept_id
				inner join {$this->schema}.v_RegistryReceptErrorType RRET on RRET.RegistryReceptErrorType_id = RRE.RegistryReceptErrorType_id
			where
				RR.ReceptUploadLog_id = :ReceptUploadLog_id
			group by 
				RRET.RegistryReceptErrorType_id
		";
		
		$result = $this->db->query($query, $data);
		
		if ( is_object($result) ) {
			return $result->result('array');
		}
		
		return array();
	}

	/**
	 * Чтение данных об созданых реестрах на оплату.
	 */
	function getReceptUploadLogPaymentData($data) {
		$query = "
			select
				Registry_Num as \"Registry_Num\",
				Registry_RecordCount as \"Registry_RecordCount\",
				Registry_Sum as \"Registry_Sum\",
				to_char(Registry_insDT, 'dd.mm.yyyy') as \"Registry_Date\",
				'' as \"File_Name\",
				'' as \"File_Size\",
				'' as \"File_CRC\"
			from
				r64.Registry r
				left join r64.RegistryDataRecept rdr on rdr.Registry_id = r.Registry_id
			where
				r.Registry_id in (
					select
						rdr.Registry_id
					from
						r64.v_RegistryRecept rr
						left join r64.RegistryDataRecept rdr on rdr.RegistryRecept_id = rr.RegistryRecept_id
					where
						rr.ReceptUploadLog_id = :ReceptUploadLog_id
				)
			limit 1
		";

		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}

		return array();
	}

	/**
	 * Чтение загруженных данных "Результаты ФЛК".
	 */
	function loadReceptUploadLogList($data) {
        $select = "";
        $join = "";
		$filter = "1=1";
		$params = array(
			'start' => $data['start'],
			'limit' => $data['limit'],
			'begDate' => $data['begDate'],
			'endDate' => $data['endDate']
		);

		if (!empty($data['ReceptUploadLog_setDT_Range'])) {
			$params['begDate'] = $data['ReceptUploadLog_setDT_Range'][0];
			$params['endDate'] = $data['ReceptUploadLog_setDT_Range'][1];
		}

		// фильтрация по организации пользователя.
		$params['Org_id'] = isset($data['session']['org_id']) ? $data['session']['org_id'] : null;
		$filter .= " and RUL.Org_id = :Org_id";

        if (in_array($data['session']['region']['nick'],array('khak','saratov'/*,'ufa'*/))) {
            $select .= ",RLLO.RegistryLLO_id as \"RegistryLLO_id\" ";
            $join .= " left join {$this->schema}.RegistryLLO RLLO on RLLO.ReceptUploadLog_id = RUL.ReceptUploadLog_id ";
        } else {
            $select .= ",null as \"RegistryLLO_id\" ";
        }
		
		$filter .= " and ( cast(RUL.ReceptUploadLog_setDT as date) >= cast(:begDate as date) and cast(RUL.ReceptUploadLog_setDT as date) <= cast(:endDate as date))";
		
		if( !empty($data['Contragent_id']) ) {
			$filter .= " and RUL.Contragent_id = :Contragent_id";
			$params['Contragent_id'] = $data['Contragent_id'];
		}
		
		if( !empty($data['ReceptUploadType_id']) ) {
			$filter .= " and RUL.ReceptUploadType_id = :ReceptUploadType_id";
			$params['ReceptUploadType_id'] = $data['ReceptUploadType_id'];
		}
		
		$query = "
			select
				-- select
				RUL.ReceptUploadLog_id as \"ReceptUploadLog_id\"
				,Ct.Contragent_id as \"Contragent_id\"
				,Ct.Contragent_Name as \"Contragent_Name\"
				,to_char(cast(RUL.ReceptUploadLog_setDT as date), 'dd.mm.yyyy') as \"ReceptUploadLog_setDT\"
				,RUT.ReceptUploadType_id as \"ReceptUploadType_id\"
				,RUT.ReceptUploadType_Name as \"ReceptUploadType_Name\"
				,RUS.ReceptUploadStatus_id as \"ReceptUploadStatus_id\"
				,RUS.ReceptUploadStatus_Code as \"ReceptUploadStatus_Code\"
				,RUS.ReceptUploadStatus_Name as \"ReceptUploadStatus_Name\"
				,coalesce(RUL.ReceptUploadLog_OutFail, RUL.ReceptUploadLog_InFail) as \"ReceptUploadLog_InFail\"
				,RUL.ReceptUploadLog_Act as \"ReceptUploadLog_Act\"
				,RUL.ReceptUploadLog_OutFail as \"ReceptUploadLog_OutFail\"
				{$select}
				-- end select
			from
				-- from
				v_ReceptUploadLog RUL
				left join v_Contragent Ct on Ct.Contragent_id = RUL.Contragent_id
				left join ReceptUploadType RUT on RUT.ReceptUploadType_id = RUL.ReceptUploadType_id
				left join ReceptUploadStatus RUS on RUS.ReceptUploadStatus_id = RUL.ReceptUploadStatus_id
				{$join}
				-- end from
			where
				-- where
				{$filter}
				-- end where
			order by
				-- order by
				RUL.ReceptUploadLog_id desc
				-- end order by
		";

		$get_count_result = $this->db->query(getCountSQLPH($query), $params);
		if( !is_object($get_count_result) ) {
			return false;
		}
		$get_count_result = $get_count_result->result('array');
		
		$result = $this->db->query(getLimitSQLPH($query, $params['start'], $params['limit']), $params);
		if( !is_object($result) ) {
			return false;
		}
		$result = $result->result('array');
		return array(
			'data' => $result,
			'totalCount' => $get_count_result[0]['cnt']
		);
	}
	
	/**
	 *	Сохранение данных в журнал загрузок
	 */
	function saveReceptUploadLog($data) {
		$action = empty($data['ReceptUploadLog_id']) ? 'ins' : 'upd';
		$params = array(
			'ReceptUploadLog_id' => $data['ReceptUploadLog_id'],
			'ReceptUploadDeliveryType_id' => 1,
			'ReceptUploadType_id' => $data['ReceptUploadType_id'],
			'Contragent_id' => $data['Contragent_id'],
			'ReceptUploadLog_InFail' => $data['ReceptUploadLog_InFail'],
			'ReceptUploadStatus_id' => 1, // получены
			'ReceptUploadLog_Act' => $data['ReceptUploadLog_Act'],
			'ReceptUploadLog_OutFail' => $data['ReceptUploadLog_OutFail'],
			'pmUser_id' => $data['pmUser_id']
		);
		
		$query = "
			select
				ReceptUploadLog_id as \"ReceptUploadLog_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from dbo.p_ReceptUploadLog_{$action}(
				ReceptUploadLog_id := :ReceptUploadLog_id,
				ReceptUploadLog_setDT := dbo.tzGetDate(),
				ReceptUploadDeliveryType_id := :ReceptUploadDeliveryType_id,
				ReceptUploadType_id := :ReceptUploadType_id,
				Contragent_id := :Contragent_id,
				ReceptUploadLog_InFail := :ReceptUploadLog_InFail,
				ReceptUploadStatus_id := :ReceptUploadStatus_id,
				ReceptUploadLog_Act := :ReceptUploadLog_Act,
				ReceptUploadLog_OutFail := :ReceptUploadLog_OutFail,
				pmUser_id := :pmUser_id
			)
		";
		//echo getDebugSql($query, $params); die();
		$res = $this->db->query($query, $params);
		if ( !is_object($res) ) {
			return false;
		}
		return $res->result('array');
	}
	
	/**
	 *	Удаление записи из журнала загрузок
	 */
	function deleteReceptUploadLog($data) {
		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from dbo.p_ReceptUploadLog_del(
				ReceptUploadLog_id := :ReceptUploadLog_id
			)
		";
		//echo getDebugSql($query, $params); die();
		$res = $this->db->query($query, array(
			'ReceptUploadLog_id' => $data['ReceptUploadLog_id']
		));
		if ( !is_object($res) ) {
			return false;
		}
		return $res->result('array');
	}
	
	/**
	 *	Возвращает значение поля конкретной записи
	 */
	function getFieldValue($field, $id) {
		if( !$field ) return false;
		$query = "
			select
				{$field} as \"{$field}\"
			from v_ReceptUploadLog
			where ReceptUploadLog_id = {$id}
		";
		$res = $this->db->query($query);
		if ( !is_object($res) ) {
			return false;
		}
		$res = $res->result('array');
		if( isset($res[0]) ) {
			return $res[0][$field];
		} else {
			return false;
		}
	}
	
	/**
	 *	Сохранение записи реестра рецептов
	 */
	function saveRegistryRecept($data) {
		$action = "ins";
		$query = "
			select
				RegistryRecept_id as \"RegistryRecept_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$this->schema}.p_RegistryRecept_{$action}(
				RegistryRecept_id := :RegistryRecept_id,
				RegistryRecept_Snils := :RegistryRecept_Snils,
				RegistryRecept_UAddOKATO := :RegistryRecept_UAddOKATO,
				RegistryRecept_LpuOGRN := :RegistryRecept_LpuOGRN,
				RegistryRecept_LpuMod := :RegistryRecept_LpuMod,
				RegistryRecept_MedPersonalCode := :RegistryRecept_MedPersonalCode,
				RegistryRecept_Diag := :RegistryRecept_Diag,
				RegistryRecept_Recent := :RegistryRecept_Recent,
				RegistryRecept_setDT := :RegistryRecept_setDT,
				RegistryRecept_RecentFinance := :RegistryRecept_RecentFinance,
				RegistryRecept_Persent := :RegistryRecept_Persent,
				RegistryRecept_FarmacyACode := :RegistryRecept_FarmacyACode,
				RegistryRecept_DrugNomCode := :RegistryRecept_DrugNomCode,
				RegistryRecept_DrugKolvo := :RegistryRecept_DrugKolvo,
				RegistryRecept_DrugDose := :RegistryRecept_DrugDose,
				RegistryRecept_DrugCode := :RegistryRecept_DrugCode,
				RegistryRecept_obrDate := :RegistryRecept_obrDate,
				RegistryRecept_otpDate := :RegistryRecept_otpDate,
				RegistryRecept_Price := :RegistryRecept_Price,
				RegistryRecept_SchetType := :RegistryRecept_SchetType,
				RegistryRecept_FarmacyOGRN := :RegistryRecept_FarmacyOGRN,
				RegistryRecept_ProtoKEK := :RegistryRecept_ProtoKEK,
				RegistryRecept_SpecialCase := :RegistryRecept_SpecialCase,
				RegistryRecept_ReceptId := :RegistryRecept_ReceptId,
				RegistryRecept_SupplyNum := :RegistryRecept_SupplyNum,
				RegistryReceptType_id := :RegistryReceptType_id,
				ReceptUploadLog_id := :ReceptUploadLog_id,
				RegistryRecept_IsDiscard := :RegistryRecept_IsDiscard,
				ReceptStatusFLKMEK_id := :ReceptStatusFLKMEK_id,
				pmUser_id := :pmUser_id
			)
		";
		
		$res = $this->db->query($query, $data);
		if ( !is_object($res) ) {
			return false;
		}
		return $res->result('array');
	}
	
	/**
	 * Сохранение данных о человеке в реестре рецептов.
	 */
	function saveRegistryReceptPerson($data) {
		$action = "ins";
		$query = "
			select
				RegistryReceptPerson_id as \"RegistryReceptPerson_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$this->schema}.p_RegistryReceptPerson_{$action}(
				RegistryReceptPerson_id := :RegistryReceptPerson_id,
				RegistryReceptPerson_Snils := :RegistryReceptPerson_Snils,
				RegistryReceptPerson_Polis := :RegistryReceptPerson_Polis,
				RegistryReceptPerson_SurName := :RegistryReceptPerson_SurName,
				RegistryReceptPerson_FirName := :RegistryReceptPerson_FirName,
				RegistryReceptPerson_SecName := :RegistryReceptPerson_SecName,
				RegistryReceptPerson_Sex := :RegistryReceptPerson_Sex,
				RegistryReceptPerson_BirthDay := :RegistryReceptPerson_BirthDay,
				RegistryReceptPerson_Privilege := :RegistryReceptPerson_Privilege,
				RegistryReceptPerson_Document := :RegistryReceptPerson_Document,
				RegistryReceptPerson_DocumentType := :RegistryReceptPerson_DocumentType,
				RegistryReceptPerson_OmsSprTerrOKATO := :RegistryReceptPerson_OmsSprTerrOKATO,
				RegistryReceptPerson_SmoOGRN := :RegistryReceptPerson_SmoOGRN,
				RegistryReceptPerson_UAddOKATO := :RegistryReceptPerson_UAddOKATO,
				RegistryReceptPerson_SpecialCase := :RegistryReceptPerson_SpecialCase,
				RegistryReceptType_id := :RegistryReceptType_id,
				ReceptUploadLog_id := :ReceptUploadLog_id,
				pmUser_id := :pmUser_id
			)
		";
		
		//echo getDebugSql($query, $data); die();
		
		$res = $this->db->query($query, $data);
		if ( !is_object($res) ) {
			return false;
		}
		return $res->result('array');
	}
	
	/**
	 *	Сохранение записи о поставке
	 */
	function saveInvoice($data) {
		$action = "ins";
		$query = "
			select
				Invoice_id as \"Invoice_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from raw.p_Invoice_{$action}(
				Invoice_id := :Invoice_id,
				Invoice_MethodType := :Invoice_MethodType,
				Invoice_DocN := :Invoice_DocN,
				Invoice_DTypeCode := :Invoice_DTypeCode,
				Invoice_DateDoc := :Invoice_DateDoc,
				Invoice_CFinl := :Invoice_CFinl,
				Invoice_gk := :Invoice_gk,
				Invoice_SuplCode := :Invoice_SuplCode,
				Invoice_SuplOgrn := :Invoice_SuplOgrn,
				Invoice_RecipCode := :Invoice_RecipCode,
				Invoice_RecipOgrn := :Invoice_RecipOgrn,
				Invoice_AktNum := :Invoice_AktNum,
				ReceptUploadLog_id := :ReceptUploadLog_id,
				InvoiceStatus_id := :InvoiceStatus_id,
				InvoiceCause_id := :InvoiceCause_id,
				pmUser_id := :pmUser_id
			)
		";
		
		$res = $this->db->query($query, $data);
		if ( !is_object($res) ) {
			return false;
		}
		return $res->result('array');
	}
	
	/**
	 * Сохранение медикамента накладной.
	 */
	function saveInvoiceDrug($data) {
		$action = "ins";
		$query = "
			select
				InvoiceDrug_id as \"InvoiceDrug_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from raw.p_InvoiceDrug_{$action}(
				InvoiceDrug_id := :InvoiceDrug_id,
				Invoice_id := :Invoice_id,
				InvoiceDrug_NomkLs := :InvoiceDrug_NomkLs,
				InvoiceDrug_SertReg := :InvoiceDrug_SertReg,
				InvoiceDrug_Series := :InvoiceDrug_Series,
				InvoiceDrug_EAN13 := :InvoiceDrug_EAN13,
				InvoiceDrug_KoAll := :InvoiceDrug_KoAll,
				InvoiceDrug_Price := :InvoiceDrug_Price,
				InvoiceDrug_NDS := :InvoiceDrug_NDS,
				InvoiceDrug_PriceNDS := :InvoiceDrug_PriceNDS,
				InvoiceDrug_SumNDS := :InvoiceDrug_SumNDS,
				InvoiceDrug_Sum := :InvoiceDrug_Sum,
				InvoiceDrug_SertN := :InvoiceDrug_SertN,
				InvoiceDrug_SertD := :InvoiceDrug_SertD,
				InvoiceDrug_CMnn := :InvoiceDrug_CMnn,
				InvoiceDrug_NameMnn := :InvoiceDrug_NameMnn,
				InvoiceDrug_Product := :InvoiceDrug_Product,
				InvoiceDrug_Producer := :InvoiceDrug_Producer,
				InvoiceDrug_SrokS := :InvoiceDrug_SrokS,
				InvoiceDrug_packNx := :InvoiceDrug_packNx,
				ReceptUploadLog_id := :ReceptUploadLog_id,
				InvoiceStatus_id := :InvoiceStatus_id,
				InvoiceCause_id := :InvoiceCause_id,
				pmUser_id := :pmUser_id
			)
		";
		
		//echo getDebugSql($query, $data); die();
		
		$res = $this->db->query($query, $data);
		if ( !is_object($res) ) {
			return false;
		}
		return $res->result('array');
	}
	
	/**
	 *	Возвращает все данные по рецептам относящиеся к конкретной загрузке
	 */
	function getRegistryReceptOnReceptUploadLog($data) {
		$query = "
			select
				RegistryRecept_id as \"RegistryRecept_id\"
			from {$this->schema}.RegistryRecept
			where ReceptUploadLog_id = :ReceptUploadLog_id
		";
		$res = $this->db->query($query, array(
			'ReceptUploadLog_id' => $data['ReceptUploadLog_id']
		));
		if ( !is_object($res) ) {
			return false;
		}
		return $res->result('array');
	}
	
	/**
	 *	Возвращает все персональные данные по отоваренным рецептам относящиеся к конкретной загрузке
	 */
	function getRegistryReceptPersonOnReceptUploadLog($data) {
		$query = "
			select
				RegistryReceptPerson_id as \"RegistryReceptPerson_id\"
			from {$this->schema}.RegistryReceptPerson
			where ReceptUploadLog_id = :ReceptUploadLog_id
		";
		$res = $this->db->query($query, array(
			'ReceptUploadLog_id' => $data['ReceptUploadLog_id']
		));
		if ( !is_object($res) ) {
			return false;
		}
		return $res->result('array');
	}
	
	/**
	 *	Удаление данных по рецептам
	 */
	function deleteRegistryRecept($data) {
		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$this->schema}.p_RegistryRecept_del(
				RegistryRecept_id := :RegistryRecept_id
			)
		";
		$res = $this->db->query($query, array(
			'RegistryRecept_id' => $data['RegistryRecept_id']
		));
		if ( !is_object($res) ) {
			return false;
		}
		return $res->result('array');
	}
	
	/**
	 *	Удаление персональных данных по отоваренным рецептам
	 */
	function deleteRegistryReceptPerson($data) {
		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$this->schema}.p_RegistryReceptPerson_del(
				RegistryReceptPerson_id := :RegistryReceptPerson_id
			)
		";
		$res = $this->db->query($query, array(
			'RegistryReceptPerson_id' => $data['RegistryReceptPerson_id']
		));
		if ( !is_object($res) ) {
			return false;
		}
		return $res->result('array');
	}
	
	/**
	 *	Возвращает идентификатор ошибки ФЛК/МЭК по коду
	 */
	function getReceptStatusFLKMEKIdByCode($code) {
		$query = "
			select
				ReceptStatusFLKMEK_id as \"ReceptStatusFLKMEK_id\"
			from {$this->schema}.ReceptStatusFLKMEK
			where ReceptStatusFLKMEK_Code = :ReceptStatusFLKMEK_Code
		";
		$res = $this->db->query($query, array(
			'ReceptStatusFLKMEK_Code' => $code
		));
		if ( !is_object($res) ) {
			return false;
		}
		$res = $res->result('array');
		return $res[0]['ReceptStatusFLKMEK_id'];

		//return 2;
	}
	
	/**
	 *	Возвращает идентификатор ошибки рецепта по коду
	 */
	function getRegistryReceptErrorTypeIdByCode($code) {
		$query = "
			select
				RegistryReceptErrorType_id as \"RegistryReceptErrorType_id\"
			from {$this->schema}.RegistryReceptErrorType
			where RegistryReceptErrorType_Type ilike :RegistryReceptErrorType_Type
		";
		$res = $this->db->query($query, array(
			'RegistryReceptErrorType_Type' => $code
		));
		if ( !is_object($res) ) {
			return false;
		}
		$res = $res->result('array');
		return $res[0]['RegistryReceptErrorType_id'];
	}
	
	/**
	 *	Возвращает идентификатор ошибки накладной по коду
	 */
	function getInvoiceCauseIdByCode($code) {
		$query = "
			select
				InvoiceCause_id as \"InvoiceCause_id\"
			from raw.InvoiceCause
			where InvoiceCause_Code = :InvoiceCause_Code
		";
		$res = $this->db->query($query, array(
			'InvoiceCause_Code' => $code
		));
		if ( is_object($res) ) {
			$res = $res->result('array');
			if (count($res) > 0) {
				return $res[0]['InvoiceCause_id'];
			}
		}
		return false;
	}

	/**
	 *	Возвращает статус рецепта по коду
	 */
	function getRegistryCheckStatusByCode($code) {
		$query = "
			select
				RegistryCheckStatus_id as \"RegistryCheckStatus_id\"
			from dbo.v_RegistryCheckStatus
			where RegistryCheckStatus_Code = :RegistryCheckStatus_Code
			limit 1
		";
		$res = $this->db->query($query, array(
			'RegistryCheckStatus_Code' => $code
		));
		if ( is_object($res) ) {
			$res = $res->result('array');
			if (count($res) > 0) {
				return $res[0]['RegistryCheckStatus_id'];
			}
		}
		return false;
	}

	/**
	 *	Возвращает список ошибок накладных в формате $array[id] = name
	 */
	function getInvoiceCauseList() {
		$array = array();

		$query = "
			select
				InvoiceCause_id as \"InvoiceCause_id\"
				InvoiceCause_Name as \"InvoiceCause_Name\"
			from raw.InvoiceCause
		";
		$res = $this->db->query($query, array());
		if ( is_object($res) ) {
			$res = $res->result('array');
			foreach($res as $record) {
				$array[$record['InvoiceCause_id']] = $record['InvoiceCause_Name'];
			}
		}
		return $array;
	}
	
	/**
	 *	ФЛК: проверка на дублирование предоставления данных
	 */
	private function checkDoubleData($record) {
		// 1. Поиск рецепта по серии, номеру и дате выдачи среди оплаченных (в реестрах на оплату)
		$query = "
			select
				count(*) as cnt
			from
				{$this->schema}.RegistryDataRecept
			where
				RegistryDataRecept_Ser = :RegistryDataRecept_Ser
				and RegistryDataRecept_Num = :RegistryDataRecept_Num
				and cast(RegistryDataRecept_setDT as date) = cast(:RegistryDataRecept_setDT as date)
		";
		$res = $this->db->query($query, array(
			'RegistryDataRecept_Ser' => $record['RegistryRecept_Ser'],
			'RegistryDataRecept_Num' => $record['RegistryRecept_Num'],
			'RegistryDataRecept_setDT' => $record['RegistryRecept_setDT']
		));
		
		if( !is_object($res) ) {
			return false;
		}
		$res = $res->result('array');
		if( $res[0]['cnt'] > 0 ) {
			return array('ReceptStatusFLKMEK_id' => $this->getReceptStatusFLKMEKIdByCode(1), 'RegistryReceptErrorType_id' => $this->getRegistryReceptErrorTypeIdByCode('Л03'));
		}
		
		// 2. Поиск рецепта по серии, номеру и дате выдачи среди обеспеченных рецептов
		$query = "
			select
				count(*) as cnt
			from
				ReceptOtov
			where
				EvnRecept_Ser = :EvnRecept_Ser
				and EvnRecept_Num = :EvnRecept_Num
				and cast(EvnRecept_setDT as date) = cast(:EvnRecept_setDT as date)
		";
		$res = $this->db->query($query, array(
			'EvnRecept_Ser' => $record['RegistryRecept_Ser'],
			'EvnRecept_Num' => $record['RegistryRecept_Num'],
			'EvnRecept_setDT' => $record['RegistryRecept_setDT']
		));
		if( !is_object($res) ) {
			return false;
		}
		$res = $res->result('array');
		if( $res[0]['cnt'] > 0 ) {
			return array('ReceptStatusFLKMEK_id' => $this->getReceptStatusFLKMEKIdByCode(1), 'RegistryReceptErrorType_id' => $this->getRegistryReceptErrorTypeIdByCode('Л04'));
		}
		return true;
	}
	
	/**
	 *	ФЛК: проверка персональных данных
	 */
	private function checkPersonData(&$record) {
		// 3. Поиск гражданина, получившего ЛС в БД по ФИО + ДР + Пол + СНИЛС
		$query = "
			select distinct
				PS.Person_id as \"Person_id\",
				PS.UAddress_id as \"UAddress_id\"
			from
				v_PersonState PS
			where
				PS.Person_SurName ilike :Person_SurName
				and PS.Person_FirName ilike :Person_FirName
				and PS.Person_SecName ilike :Person_SecName
				and cast(PS.Person_BirthDay as date) = cast(:Person_BirthDay as date)
				and PS.Sex_id = :Sex_id
				and PS.Person_Snils = :Person_Snils
				and Person_id in (
					select
						Person_id
					from
						v_PersonPrivilege
					where
						PrivilegeType_Code = :PrivilegeType_Code
				)
		";
		$res = $this->db->query($query, array(
			'Person_SurName' => $record['RegistryReceptPerson_SurName'],
			'Person_FirName' => $record['RegistryReceptPerson_FirName'],
			'Person_SecName' => $record['RegistryReceptPerson_SecName'],
			'Person_BirthDay' => $record['RegistryReceptPerson_BirthDay'],
			'Sex_id' => $record['RegistryReceptPerson_Sex'] == 'М' ? 1 : 2,
			'Person_Snils' => $record['RegistryReceptPerson_Snils'],
			'PrivilegeType_Code' => $record['RegistryReceptPerson_Privilege']
		));
		if( !is_object($res) ) {
			return false;
		}
		$res = $res->result('array');
		if( count($res) == 0 ) {
			return array('ReceptStatusFLKMEK_id' => $this->getReceptStatusFLKMEKIdByCode(1), 'RegistryReceptErrorType_id' => $this->getRegistryReceptErrorTypeIdByCode('П01'));
		}
		// ВАЖНО для дальнейшей проверки!!!
		$record['Person_id'] = $res[0]['Person_id'];
		
		// 4. Проверка наличия у человека адреса регистрации
		if( empty($res[0]['UAddress_id']) ) {
			return array('ReceptStatusFLKMEK_id' => $this->getReceptStatusFLKMEKIdByCode(1), 'RegistryReceptErrorType_id' => $this->getRegistryReceptErrorTypeIdByCode('П04'));
		}
		
		return true;
	}
	
	/**
	 *	ФЛК: проверка на выписку рецепта
	 */
	private function checkOnExtractRecept(&$record) {
		// 5. Поиск рецепта среди выписанных рецептов: поиск по серии, номеру, дате выписки и льготе.
		$query = "
			select
				 ER.EvnRecept_id as \"EvnRecept_id\"
				,PS.Person_SurName as \"Person_SurName\"
				,PS.Person_FirName as \"Person_FirName\"
				,PS.Person_SecName as \"Person_SecName\"
				,to_char(cast(PS.Person_BirthDay as date), 'yyyy-mm-dd') as \"Person_BirthDay\"
				,PS.Sex_id as \"Sex_id\"
				,PS.Person_Snils as \"Person_Snils\"
			from
				v_EvnRecept ER
				left join v_PersonState PS on PS.Person_id = ER.Person_id
					and PS.PersonEvn_id = ER.PersonEvn_id
				inner join WhsDocumentCostItemType WDCIT on WDCIT.WhsDocumentCostItemType_id = ER.WhsDocumentCostItemType_id
			where
				ER.EvnRecept_Ser = :EvnRecept_Ser
				and ER.EvnRecept_Num = :EvnRecept_Num
				and cast(ER.EvnRecept_setDate as date) = cast(:EvnRecept_setDate as date)
				and WDCIT.DrugFinance_id in (
					select
						DrugFinance_id
					from
						v_PrivilegeType
					where
						PrivilegeType_Code = :PrivilegeType_Code
				)
			limit 1
		";
		$res = $this->db->query($query, array(
			'EvnRecept_Ser' => $record['RegistryRecept_Ser'],
			'EvnRecept_Num' => $record['RegistryRecept_Num'],
			'EvnRecept_setDate' => $record['RegistryRecept_setDT'],
			'PrivilegeType_Code' => $record['RegistryReceptPerson_Privilege']
		));
		if( !is_object($res) ) {
			return false;
		}
		$res = $res->result('array');
		if( count($res) == 0 ) {
			return array('ReceptStatusFLKMEK_id' => $this->getReceptStatusFLKMEKIdByCode(1), 'RegistryReceptErrorType_id' => $this->getRegistryReceptErrorTypeIdByCode('Р02'));
		}
		$record['EvnRecept_id'] = $res[0]['EvnRecept_id'];
		
		// 6. ФИО+ДР+Пол+Снилс человека, которому был выписан рецепт = ФИО+ДР+Пол+Снилс человека, указанному в рецепте Поставщиком
		// Данная проверка отключена для рецептов снятых с обслуживания
		if(
			$record['RegistryRecept_IsDiscard'] != 2 && (
				$record['RegistryReceptPerson_SurName'] != $res[0]['Person_SurName'] ||
				$record['RegistryReceptPerson_FirName'] != $res[0]['Person_FirName'] ||
				$record['RegistryReceptPerson_SecName'] != $res[0]['Person_SecName'] ||
				$record['RegistryReceptPerson_BirthDay'] != $res[0]['Person_BirthDay'] ||
				($record['RegistryReceptPerson_Sex'] == 'М' ? 1 : 2) != $res[0]['Sex_id'] ||
				$record['RegistryReceptPerson_Snils'] != $res[0]['Person_Snils']
			)
		) {
			return array('ReceptStatusFLKMEK_id' => $this->getReceptStatusFLKMEKIdByCode(1), 'RegistryReceptErrorType_id' => $this->getRegistryReceptErrorTypeIdByCode('Р01'));
		}
		return true;
	}
	
	/**
	 *	ФЛК: проверка реквизитов рецепта
	 */
	private function checkDetailsRecept($record) {
		// 7. Поиск кода врача рецепта, участвующего в системе ЛЛО
		$query = "
			select
				pMW.CodeDLO as \"CodeDLO\"
			from
				v_MedPersonal MP
				left join persis.Medworker pMW on pMW.id = MP.MedPersonal_id
			where
				pMW.CodeDLO = :CodeDLO
				and MP.Lpu_id in ( select Lpu_id from lpu where Lpu_Ouz = :Lpu_Ouz )
				and (cast(MP.WorkData_begDate as date) <= cast(:RegistryRecept_setDT as date)
				and coalesce(cast(MP.WorkData_endDate as date),cast(:RegistryRecept_setDT as date)) >= cast(:RegistryRecept_setDT as date))
		";

		$res = $this->db->query($query, array(
			'CodeDLO' => trim($this->getCode($record['RegistryRecept_MedPersonalCode'], 'mp')),
			'Lpu_Ouz' => trim($record['RegistryRecept_LpuMod']),
			'RegistryRecept_setDT' => $record['RegistryRecept_setDT']
		));
		if( !is_object($res) ) {
			return false;
		}
		$res = $res->result('array');
		if( count($res) == 0 ) {
			return array('ReceptStatusFLKMEK_id' => $this->getReceptStatusFLKMEKIdByCode(1), 'RegistryReceptErrorType_id' => $this->getRegistryReceptErrorTypeIdByCode('Р03'));
		}
		
		// 8. Поиск кода ЛПУ в списке ЛПУ, участвующих в системе ЛЛО
		$query = "
			select
				l.Lpu_Ouz as \"Lpu_Ouz\"
			from
				v_LpuPeriodDLO ldlo
				inner join v_Lpu l on l.Lpu_id = ldlo.Lpu_id
			where
				l.Lpu_Ouz = :Lpu_Ouz
				and l.Lpu_OGRN = :Lpu_OGRN
				and cast(ldlo.LpuPeriodDLO_begDate as date) <= cast(:RegistryRecept_setDT as date)
				and coalesce(cast(ldlo.LpuPeriodDLO_endDate as date), cast(:RegistryRecept_setDT as date)) >= cast(:RegistryRecept_setDT as date)
		";
		$res = $this->db->query($query, array(
			'Lpu_Ouz' => trim($record['RegistryRecept_LpuMod']),
			'Lpu_OGRN' => trim($record['RegistryRecept_LpuOGRN']),
			'RegistryRecept_setDT' => $record['RegistryRecept_setDT']
		));
		if( !is_object($res) ) {
			return false;
		}
		$res = $res->result('array');
		if( count($res) == 0 ) {
			return array('ReceptStatusFLKMEK_id' => $this->getReceptStatusFLKMEKIdByCode(1), 'RegistryReceptErrorType_id' => $this->getRegistryReceptErrorTypeIdByCode('Р04'));
		}

		// 9. Поиск Аптеки по указанному коду в справочнике Контрагентов
		$query = "
			select
				c.Contragent_id as \"Contragent_id\"
			from
				Contragent c
				left join Org o on o.Org_id = c.Org_id
			where
				c.Contragent_Code = :Contragent_Code and
				o.Org_OGRN = :Org_OGRN;
		";

		$res = $this->db->query($query, array(
			'Contragent_Code' => $this->getCode($record['RegistryRecept_FarmacyACode'], 'con'),
			'Org_OGRN' => $this->getCode($record['RegistryRecept_FarmacyACode'], 'orgn'),
		));
		if( !is_object($res) ) {
			return false;
		}
		$res = $res->result('array');
		if( count($res) == 0 ) {
			return array('ReceptStatusFLKMEK_id' => $this->getReceptStatusFLKMEKIdByCode(1), 'RegistryReceptErrorType_id' => $this->getRegistryReceptErrorTypeIdByCode('Р12'));
		}
		return true;
	}
	
	/**
	 *	ФЛК: проверка на наличие рецепта среди рецептов «на отсроченном обслуживании»
	 */
	private function checkIsDelayedRecept(&$record) {
		// 10. Поиск по серии номеру и дате выписки рецепта среди рецептов на отсроченном обслуживании
		$query = "
			select
				RO.ReceptOtov_id as \"ReceptOtov_id\",
				RO.EvnRecept_id as \"EvnRecept_id\",
				RO.Person_id as \"Person_id\"
			from
				ReceptOtov RO
				left join ReceptDelayType RDT on RDT.ReceptDelayType_id = RO.ReceptDelayType_id
			where
				EvnRecept_Ser = :EvnRecept_Ser
				and EvnRecept_Num = :EvnRecept_Num
				and cast(EvnRecept_setDT as date) = cast(:EvnRecept_setDT as date)
				and RDT.ReceptDelayType_Code = 1
		";
		$res = $this->db->query($query, array(
			'EvnRecept_Ser' => $record['RegistryRecept_Ser'],
			'EvnRecept_Num' => $record['RegistryRecept_Num'],
			'EvnRecept_setDT' => $record['RegistryRecept_setDT']
		));
		if( !is_object($res) ) {
			return false;
		}
		$res = $res->result('array');
		if( count($res) == 0 ) {
			return array('ReceptStatusFLKMEK_id' => $this->getReceptStatusFLKMEKIdByCode(1), 'RegistryReceptErrorType_id' => $this->getRegistryReceptErrorTypeIdByCode('Р06'));
		} elseif (count($res) > 0) {
			// сохраняем данные в $record для дальнейшей обработки данных рецепта
			$record['ReceptOtov_id'] = $res[0]['ReceptOtov_id'];
			$record['EvnRecept_id'] = $res[0]['EvnRecept_id'];
			$record['Person_id'] = $res[0]['Person_id'];
			// прописать в RegistryRecept ссылку на ReceptOtov_id
			$this->setScheme($this->schema)->setObject('RegistryRecept')->setRow($record['RegistryRecept_id'])->setValue('ReceptOtov_id', $res[0]['ReceptOtov_id']);
		}
		return true;
	}
	
	/**
	 *	ФЛК: проверка на наличие рецепта среди рецептов, обеспеченных ЛС со статусом по экспертизе – «отказ: ошибки МЭК»
	 */
	private function checkIsOtovRecept(&$record) {
		// 11. Поиск по серии номеру и дате выписки рецепта среди рецептов, обеспеченных ЛС, которые не годны к оплате
		$query = "
			select
				RO.ReceptOtov_id as \"ReceptOtov_id\",
				RO.Person_id as \"Person_id\",
				RO.EvnRecept_id as \"EvnRecept_id\"
			from
				v_ReceptOtovUnSub RO
				left join v_ReceptStatusType RST on RST.ReceptStatusType_id = RO.ReceptStatusType_id
			where	
				RO.EvnRecept_Ser = :EvnRecept_Ser
				and RO.EvnRecept_Num = :EvnRecept_Num
				and cast(RO.EvnRecept_setDT as date) = cast(:EvnRecept_setDT as date)
				and RO.ReceptStatusType_id is not NULL
				and RST.ReceptStatusType_Code <> 0
		";
		$res = $this->db->query($query, array(
			'EvnRecept_Ser' => $record['RegistryRecept_Ser'],
			'EvnRecept_Num' => $record['RegistryRecept_Num'],
			'EvnRecept_setDT' => $record['RegistryRecept_setDT']
		));
		if( !is_object($res) ) {
			return false;
		}
		$res = $res->result('array');
		if( count($res) == 0 ) {
			return array('ReceptStatusFLKMEK_id' => $this->getReceptStatusFLKMEKIdByCode(1), 'RegistryReceptErrorType_id' => $this->getRegistryReceptErrorTypeIdByCode('Р11'));
		} elseif (count($res) > 0) {
			// ВАЖНО для дальнейшей проверки!!!
			$record['ReceptOtov_id'] = $res[0]['ReceptOtov_id'];
			$record['Person_id'] = $res[0]['Person_id'];
			$record['EvnRecept_id'] = $res[0]['EvnRecept_id'];
			// прописать в RegistryRecept ссылку на ReceptOtov_id
			$this->setScheme($this->schema)->setObject('RegistryRecept')->setRow($record['RegistryRecept_id'])->setValue('ReceptOtov_id', $res[0]['ReceptOtov_id']);
		}
		return true;
	}
	
	/**
	 *	ФЛК: проверка на соответствие данных поставщика данным рецепта из ReceptOtov
	 */
	private function checkIsConformityData($record) {
		// 12. по «полям» (ФИО+ДР+Пол+СНИЛС)
		$query = "
			select
				 RO.EvnRecept_Ser as \"EvnRecept_Ser\"
				,RO.EvnRecept_Num as \"EvnRecept_Num\"
				,PS.Person_SurName as \"Person_SurName\"
				,PS.Person_FirName as \"Person_FirName\"
				,PS.Person_SecName as \"Person_SecName\"
				,PS.Person_Snils as \"Person_Snils\"
				,PS.Person_BirthDay as \"Person_BirthDay\"
				,PS.Sex_id as \"Sex_id\"
				,RO.EvnRecept_setDT as \"EvnRecept_setDT\"
			from
				ReceptOtov RO
				left join v_PersonState PS on PS.Person_id = RO.Person_id
			where
				lower(PS.Person_SurName) like lower(:Person_SurName)
				and lower(PS.Person_FirName) like lower(:Person_FirName)
				and lower(PS.Person_SecName) like lower(:Person_SecName)
				and PS.Person_Snils = :Person_Snils
				and cast(PS.Person_BirthDay as date) = cast(:Person_BirthDay as date)
				and PS.Sex_id = :Sex_id
				and RO.EvnRecept_Ser = :EvnRecept_Ser
				and RO.EvnRecept_Num = :EvnRecept_Num
		";
		$res = $this->db->query($query, array(
			'EvnRecept_Ser' => $record['RegistryRecept_Ser'],
			'EvnRecept_Num' => $record['RegistryRecept_Num'],
			'Sex_id' => $record['RegistryReceptPerson_Sex'] == 'М' ? 1 : 2,
			'Person_BirthDay' => $record['RegistryReceptPerson_BirthDay'],
			'Person_Snils' => $record['RegistryReceptPerson_Snils'],
			'Person_SurName' => $record['RegistryReceptPerson_SurName'],
			'Person_FirName' => $record['RegistryReceptPerson_FirName'],
			'Person_SecName' => $record['RegistryReceptPerson_SecName']
		));
		if( !is_object($res) ) {
			return false;
		}
		$res = $res->result('array');
		if( count($res) == 0 ) {
			return array('ReceptStatusFLKMEK_id' => $this->getReceptStatusFLKMEKIdByCode(1), 'RegistryReceptErrorType_id' => $this->getRegistryReceptErrorTypeIdByCode('Р07'));
		}
		
		// 13. по «полям» льгота
		$query = "
			select
				RO.ReceptOtov_id as \"ReceptOtov_id\"
			from
				ReceptOtov RO
			where
				RO.EvnRecept_Ser = :EvnRecept_Ser
				and RO.EvnRecept_Num = :EvnRecept_Num
				and RO.PrivilegeType_id IN (SELECT PrivilegeType_id FROM v_PrivilegeType WHERE PrivilegeType_Code = :RegistryReceptPerson_Privilege)
		";
		$res = $this->db->query($query, array(
			'EvnRecept_Ser' => $record['RegistryRecept_Ser'],
			'EvnRecept_Num' => $record['RegistryRecept_Num'],
			'RegistryReceptPerson_Privilege' => $record['RegistryReceptPerson_Privilege']
		));
		if( !is_object($res) ) {
			return false;
		}
		$res = $res->result('array');
		if( count($res) == 0 ) {
			return array('ReceptStatusFLKMEK_id' => $this->getReceptStatusFLKMEKIdByCode(1), 'RegistryReceptErrorType_id' => $this->getRegistryReceptErrorTypeIdByCode('Р08'));
		}
		
		// 14. по «полям»  код врача + код МО
		$query = "
			select
				RO.ReceptOtov_id as \"ReceptOtov_id\"
			from
				v_ReceptOtovUnSub RO
				left join v_Lpu Lpu on Lpu.Lpu_id = RO.Lpu_id
				left join v_MedPersonal MP on MP.MedPersonal_id = RO.MedPersonalRec_id
				left join persis.Medworker pMW on pMW.id = MP.MedPersonal_id
			where
				RO.EvnRecept_Ser = :EvnRecept_Ser
				and RO.EvnRecept_Num = :EvnRecept_Num
				and Lpu.Lpu_Ouz = :Lpu_Ouz
				and pMW.CodeDLO = :MedPersonalRec_Code
		";
		$res = $this->db->query($query, array(
			'EvnRecept_Ser' => $record['RegistryRecept_Ser'],
			'EvnRecept_Num' => $record['RegistryRecept_Num'],
			'Lpu_Ouz' => $record['RegistryRecept_LpuMod'],
			'MedPersonalRec_Code' => $this->getCode($record['RegistryRecept_MedPersonalCode'], 'mp')
		));
		if( !is_object($res) ) {
			return false;
		}
		$res = $res->result('array');
		if( count($res) == 0 ) {
			return array('ReceptStatusFLKMEK_id' => $this->getReceptStatusFLKMEKIdByCode(1), 'RegistryReceptErrorType_id' => $this->getRegistryReceptErrorTypeIdByCode('Р09'));
		}
		
		// 15. по «полям» наименование выписанного ЛС
		$query = "
			select
				RO.ReceptOtov_id as \"ReceptOtov_id\"
			from
				v_ReceptOtovUnSub RO
				left join rls.Drug d on d.Drug_id = RO.Drug_cid
				left join rls.v_DrugNomen dn on d.Drug_id = dn.Drug_id
			where
				RO.EvnRecept_Ser = :EvnRecept_Ser
				and RO.EvnRecept_Num = :EvnRecept_Num
				and dn.DrugNomen_Code = :Drug_Code
		";
		$res = $this->db->query($query, array(
			'EvnRecept_Ser' => $record['RegistryRecept_Ser'],
			'EvnRecept_Num' => $record['RegistryRecept_Num'],
			'Drug_Code' => $record['RegistryRecept_DrugNomCode']
		));
		if( !is_object($res) ) {
			return false;
		}
		$res = $res->result('array');
		if( count($res) == 0 ) {
			return array('ReceptStatusFLKMEK_id' => $this->getReceptStatusFLKMEKIdByCode(1), 'RegistryReceptErrorType_id' => $this->getRegistryReceptErrorTypeIdByCode('Р10'));
		}
		
		return true;
	}

	/**
	 * Получение данных об импортированных рецептах.
	 */
	function getRegistryReceptExpertData($params) {
		$query = "
			select
				RSFM.ReceptStatusFLKMEK_Name as \"STATUS\",
				RR.ReceptOtov_id as \"RECEPTOTOV\",
				RST.ReceptStatusType_Name as \"STPROVIDE\"
			from
				{$this->schema}.v_RegistryRecept RR
				left join v_ReceptOtovUnSub RO on RO.ReceptOtov_id = RR.ReceptOtov_id
				left join v_ReceptStatusType RST on RST.ReceptStatusType_id = RO.ReceptStatusType_id
				left join {$this->schema}.v_ReceptStatusFLKMEK RSFM on RSFM.ReceptStatusFLKMEK_id = RR.ReceptStatusFLKMEK_id
			where
				RR.ReceptUploadLog_id = :ReceptUploadLog_id
				and RR.RegistryRecept_ReceptId = :RegistryRecept_ReceptId
			limit 1
		";
		$res = $this->db->query($query, array(
			'RegistryRecept_ReceptId' => $params['RegistryRecept_ReceptId'],
			'ReceptUploadLog_id' => $params['ReceptUploadLog_id']
		));
		
		if ( is_object($res) ) {
			$resp = $res->result('array');
			if (count($resp) > 0) {
				return $resp[0];
			}
		}
		
		return false;
	}
	
	/**
	 *	Сохранение рецепта в списке обеспеченных
	 */
	function saveReceptOtov($record) {
		if (!isset($record['ReceptOtov_id']) || $record['ReceptOtov_id'] <= 0) {
			$query = "
				with mv as (
					select
						MP.MedPersonal_id as MedPersonal_id
					from
						v_MedPersonal MP
						left join persis.Medworker pMW on pMW.id = MP.MedPersonal_id
						left join Lpu on Lpu.Lpu_id = MP.Lpu_id
					where
						pMW.CodeDLO = :CodeDLO
						and MP.Lpu_id in (select Lpu_id from lpu where Lpu_Ouz = :Lpu_Ouz)
						and (MP.WorkData_begDate is null or MP.WorkData_begDate <= coalesce(cast(:EvnRecept_setDT as date), dbo.tzGetDate()))
						and (MP.WorkData_endDate is null or MP.WorkData_endDate >= coalesce(cast(:EvnRecept_setDT as date), dbo.tzGetDate()))
					order by
						MP.MedPersonal_id
					limit 1
				)

				select
					RO.ReceptOtov_id as \"ReceptOtov_id\"
				from
					v_ReceptOtovUnSub RO
					left join v_Diag Diag on Diag.Diag_id = RO.Diag_id
					left join v_Lpu Lpu on Lpu.Lpu_id = RO.Lpu_id
				where
					RO.EvnRecept_Ser = :EvnRecept_Ser
					and RO.EvnRecept_Num = :EvnRecept_Num
					and RO.EvnRecept_setDT = cast(:EvnRecept_setDT as date)
					and RO.Drug_cid = (
						select
							d.Drug_id
						from rls.v_DrugNomen dn
							inner join rls.v_Drug d on d.Drug_id = dn.Drug_id
						where dn.DrugNomen_Code = :DrugNomen_Code
						limit 1
					)
					and Diag.Diag_Code = :Diag_Code
					and RO.Person_id = :Person_id
					and Lpu.Lpu_Ouz = :Lpu_Ouz
					and RO.MedPersonalRec_id = (select MedPersonal_id from mv)
				limit 1
			";
			$res = $this->db->query($query, array(
				'EvnRecept_Ser' => $record['RegistryRecept_Ser'],
				'EvnRecept_Num' => $record['RegistryRecept_Num'],
				'EvnRecept_setDT' => $record['RegistryRecept_setDT'],
				'DrugNomen_Code' => $record['RegistryRecept_DrugNomCode'],
				'Diag_Code' => $record['RegistryRecept_Diag'],
				'Person_id' => $record['Person_id'],
				'Lpu_Ouz' => $record['RegistryRecept_LpuMod'],
				'CodeDLO' => $this->getCode($record['RegistryRecept_MedPersonalCode'], 'mp')
			));
			if ( !is_object($res) ) {
				return false;
			}
			$res = $res->result('array');
			if( count($res) == 1 ) {
				$record['ReceptOtov_id'] = $res[0]['ReceptOtov_id'];
			} else {
				$record['ReceptOtov_id'] = null;
			}
		}

		$action = $record['ReceptOtov_id'] > 0 ? "upd" : "ins";
		
		$query = "
			select
				ReceptOtov_id as \"ReceptOtov_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_ReceptOtov_{$action}(
				ReceptOtov_id := :ReceptOtov_id,
				EvnRecept_Guid := :EvnRecept_Guid,
				Person_id := :Person_id,
				Person_Snils := :Person_Snils,
				PrivilegeType_id := (
					select
						PrivilegeType_id
					from dbo.v_PrivilegeType
					where PrivilegeType_Code = :PrivilegeType_Code
					limit 1
				),
				Lpu_id := (
					select
						Lpu_id
					from v_Lpu
					where Lpu_OGRN = :Lpu_Ogrn
						and Lpu_Ouz = :Lpu_Ouz
					limit 1
				),
				Lpu_Ogrn := :Lpu_Ogrn,
				MedPersonalRec_id := (
					select
						MP.MedPersonal_id
					from
						v_MedPersonal MP
						left join persis.Medworker pMW on pMW.id = MP.MedPersonal_id
						left join Lpu on Lpu.Lpu_id = MP.Lpu_id
					where
						pMW.CodeDLO = :CodeDLO and
						MP.Lpu_id in (select Lpu_id from lpu where Lpu_Ouz = :Lpu_Ouz)
						and (MP.WorkData_begDate is null or MP.WorkData_begDate <= coalesce(CAST(:EvnRecept_setDT as date), dbo.tzGetDate()))
						and (MP.WorkData_endDate is null or MP.WorkData_endDate >= coalesce(CAST(:EvnRecept_setDT as date), dbo.tzGetDate()))
					order by
						MP.MedPersonal_id
					limit 1
				),
				Diag_id := (
					select
						Diag_id
					from Diag
					where Diag_Code = :Diag_Code
					limit 1
				),
				EvnRecept_Ser := :EvnRecept_Ser,
				EvnRecept_Num := :EvnRecept_Num,
				EvnRecept_setDT := :EvnRecept_setDT,
				ReceptFinance_id := (
					select
						ReceptFinance_id
					from ReceptFinance
					where ReceptFinance_Code = :ReceptFinance_Code
					limit 1
				),
				ReceptValid_id := :ReceptValid_id,
				OrgFarmacy_id := (
					select
						orf.OrgFarmacy_id
					from Contragent c
						inner join v_OrgFarmacy orf on c.Org_id = orf.Org_id
					where c.Contragent_Code = :Contragent_Code
						and c.ContragentType_id = 3
					limit 1
				),
				Drug_cid := (
					select
						d.Drug_id
					from rls.v_DrugNomen dn
						inner join rls.v_Drug d on d.Drug_id = dn.Drug_id
					where dn.DrugNomen_Code = :DrugNomen_Code
					limit 1
				),
				Drug_Code := :DrugNomen_Code,
				EvnRecept_Kolvo := :EvnRecept_Kolvo,
				EvnRecept_obrDate := :EvnRecept_obrDate,
				EvnRecept_otpDate := :EvnRecept_otpDate,
				EvnRecept_Price := :EvnRecept_Price,
				ReceptDelayType_id := :ReceptDelayType_id,
				ReceptOtdel_id := :ReceptOtdel_id,
				EvnRecept_id := :EvnRecept_id,
				EvnRecept_Is7Noz := :EvnRecept_Is7Noz,
				DrugFinance_id := (
					select
						WDS.DrugFinance_id
					from WhsDocumentSupply WDS
						left join WhsDocumentUc WDU on WDU.WhsDocumentUc_id = WDS.WhsDocumentUc_id
					where WDU.WhsDocumentUc_Num = :WhsDocumentUc_Num
					limit 1
				),
				WhsDocumentCostItemType_id := (
					select
						WDS.WhsDocumentCostItemType_id
					from WhsDocumentSupply WDS
						left join WhsDocumentUc WDU on WDU.WhsDocumentUc_id = WDS.WhsDocumentUc_id
					where WDU.WhsDocumentUc_Num = :WhsDocumentUc_Num
					limit 1
				),
				ReceptStatusType_id := :ReceptStatusType_id,
				pmUser_id := :pmUser_id
			)
		";
		
		$record['ReceptDelayType_id'] = null;
		
		switch ($record['RegistryRecept_SchetType']) {
			case 0:
			case 1:
			case 3:
				if (!empty($record['RegistryRecept_obrDate']) && empty($record['RegistryRecept_otpDate'])) {
					$record['ReceptDelayType_id'] = 2; // отложен
				}
				if (!empty($record['RegistryRecept_obrDate']) && !empty($record['RegistryRecept_otpDate'])) {
					$record['ReceptDelayType_id'] = 1; // обслужен
				}
				if (!empty($record['RegistryRecept_IsDiscard']) && $record['RegistryRecept_IsDiscard'] == 2) {
					$record['ReceptDelayType_id'] = 3; // отказ
				}
			break;
			
			case 2:
				if (!empty($record['RegistryRecept_obrDate']) && !empty($record['RegistryRecept_otpDate'])) {
					$record['ReceptDelayType_id'] = 1; // обслужен
				}
				if (!empty($record['RegistryRecept_IsDiscard']) && $record['RegistryRecept_IsDiscard'] == 2) {
					$record['ReceptDelayType_id'] = 3; // отказ
				}
			break;
		}
		
		$res = $this->db->query($query, array(
			'ReceptOtov_id' => $record['ReceptOtov_id'],
			'EvnRecept_Guid' => null, // $record['RegistryRecept_ReceptId'], пока не заполнять https://redmine.swan.perm.ru/issues/21675
			'Person_id' => $record['Person_id'],
			'Person_Snils' => $record['RegistryReceptPerson_Snils'],
			'PrivilegeType_Code' => $record['RegistryReceptPerson_Privilege'],
			'Lpu_Ogrn' => trim($record['RegistryRecept_LpuOGRN']),
			'Lpu_Ouz' => trim($record['RegistryRecept_LpuMod']),
			'CodeDLO' => $this->getCode($record['RegistryRecept_MedPersonalCode'], 'mp'),
			'Diag_Code' => $record['RegistryRecept_Diag'],
			'EvnRecept_Ser' => $record['RegistryRecept_Ser'],
			'EvnRecept_Num' => $record['RegistryRecept_Num'],
			'EvnRecept_setDT' => $record['RegistryRecept_setDT'],
			'ReceptFinance_Code' => $record['RegistryRecept_RecentFinance'],
			'ReceptValid_id' => null,
			'Contragent_Code' => $this->getCode($record['RegistryRecept_FarmacyACode'], 'con'),
			'DrugNomen_Code' => $record['RegistryRecept_DrugNomCode'],
			'EvnRecept_Kolvo' => $record['RegistryRecept_DrugKolvo'],
			'EvnRecept_obrDate' => $record['RegistryRecept_obrDate'],
			'EvnRecept_otpDate' => $record['RegistryRecept_otpDate'],
			'EvnRecept_Price' => $record['RegistryRecept_Price'],
			'ReceptDelayType_id' => $record['ReceptDelayType_id'],
			'ReceptOtdel_id' => null,
			'EvnRecept_id' => $record['EvnRecept_id'],
			'EvnRecept_Is7Noz' => null,
			'WhsDocumentUc_Num' => $record['RegistryRecept_SupplyNum'],
			'ReceptStatusType_id' => 1,
			'pmUser_id' => $record['pmUser_id']
		));
		if ( !is_object($res) ) {
			return false;
		}
		$res = $res->result('array');
		if( count($res) == 1 ) {
			$record['ReceptOtov_id'] = $res[0]['ReceptOtov_id'];

			// синхронизация данных таблицы о выписанных рецептах (EvnRecept) с данными из таблицы об отоваренных рецептах (ReceptOtov)
			if (!empty($record['EvnRecept_id'])) {
				$query = "
					with mv as (
						select
							EvnRecept_obrDate,
							EvnRecept_otpDate,
							ReceptDelayType_id,
							OrgFarmacy_id,
							Drug_cid,
							EvnRecept_Kolvo,
							EvnRecept_Price
						from
							ReceptOtov
						where
							ReceptOtov_id = :ReceptOtov_id
					)

					update
						EvnRecept
					set
						EvnRecept_obrDT = (select EvnRecept_obrDT from mv),
						EvnRecept_otpDT = (select EvnRecept_otpDT from mv),
						ReceptDelayType_id = (select ReceptDelayType_id from mv),
						OrgFarmacy_oid = (select OrgFarmacy_oid from mv),
						Drug_rlsid = (select Drug_rlsid from mv),
						EvnRecept_oKolvo = (select EvnRecept_oKolvo from mv),
						EvnRecept_oPrice = (select EvnRecept_oPrice from mv)
					where
						Evn_id = :EvnRecept_id;
				";
				$this->db->query($query, array(
					'ReceptOtov_id' => $record['ReceptOtov_id'],
					'EvnRecept_id' => $record['EvnRecept_id']
				));
			}
		}
		
		return $res;
	}
	
	/**
	 *	Возвращает импортированные в систему реестры рецептов
	 */
	function getImportedRegistryRecepts($data) {
		$query = "
			select
				 RR.RegistryRecept_id as \"RegistryRecept_id\"
				,RR.RegistryRecept_Recent as \"RegistryRecept_Recent\"
				,substring(RegistryRecept_Recent, 1, 5) as \"RegistryRecept_Ser\"
				,rtrim(substring(RegistryRecept_Recent, 6, length(RegistryRecept_Recent))) as \"RegistryRecept_Num\"
				,to_char(cast(RR.RegistryRecept_setDT as date), 'yyyy-mm-dd') as \"RegistryRecept_setDT\"
				,to_char(cast(RR.RegistryRecept_obrDate as date), 'yyyy-mm-dd') as \"RegistryRecept_obrDate\"
				,to_char(cast(RR.RegistryRecept_otpDate as date), 'yyyy-mm-dd') as \"RegistryRecept_otpDate\"
				,rtrim(RRP.RegistryReceptPerson_SurName) as \"RegistryReceptPerson_SurName\"
				,rtrim(RRP.RegistryReceptPerson_FirName) as \"RegistryReceptPerson_FirName\"
				,rtrim(RRP.RegistryReceptPerson_SecName) as \"RegistryReceptPerson_SecName\"
				,to_char(cast(RRP.RegistryReceptPerson_BirthDay as date), 'yyyy-mm-dd') as \"RegistryReceptPerson_BirthDay\"
				,RRP.RegistryReceptPerson_Sex as \"RegistryReceptPerson_Sex\"
				,REPLACE(REPLACE(RegistryReceptPerson_Snils, '-', ''), ' ', '') as \"RegistryReceptPerson_Snils\"
				,RR.RegistryRecept_MedPersonalCode as \"RegistryRecept_MedPersonalCode\"
				,rtrim(RR.RegistryRecept_LpuMod) as \"RegistryRecept_LpuMod\"
				,RR.RegistryRecept_FarmacyACode as \"RegistryRecept_FarmacyACode\"
				,RRP.RegistryReceptPerson_Privilege as \"RegistryReceptPerson_Privilege\"
				,RR.RegistryRecept_DrugNomCode as \"RegistryRecept_DrugNomCode\"
				,RR.RegistryRecept_SchetType as \"RegistryRecept_SchetType\"
				,RR.RegistryRecept_RecentFinance as \"RegistryRecept_RecentFinance\"
				,RR.RegistryRecept_LpuOGRN as \"RegistryRecept_LpuOGRN\"
				,RR.RegistryRecept_ReceptId as \"RegistryRecept_ReceptId\"
				,RR.RegistryRecept_Diag as \"RegistryRecept_Diag\"
				,RR.RegistryRecept_Persent as \"RegistryRecept_Persent\"
				,RR.RegistryRecept_DrugKolvo as \"RegistryRecept_DrugKolvo\"
				,RR.RegistryRecept_Price as \"RegistryRecept_Price\"
				,RR.RegistryRecept_SupplyNum as \"RegistryRecept_SupplyNum\"
				,RR.RegistryRecept_IsDiscard as \"RegistryRecept_IsDiscard\"
			from
				{$this->schema}.RegistryRecept RR
				left join lateral(
					select
						*
					from
						{$this->schema}.RegistryReceptPerson
					where
						ReceptUploadLog_id = RR.ReceptUploadLog_id and
						RegistryReceptPerson_Snils = RR.RegistryRecept_Snils
					limit 1
				) RRP on true
			where
				RR.ReceptUploadLog_id = :ReceptUploadLog_id
		";
		$res = $this->db->query($query, array('ReceptUploadLog_id' => $data['ReceptUploadLog_id']));
		if ( !is_object($res) ) {
			return false;
		}
		return $res->result('array');
	}

	/**
	 *	Возвращает импортированные в систему накладные
	 *  + все данные для проведения по ним экспертизы..
	 */
	function getImportedInvoices($data) {
		$query = "
			select
				I.Invoice_id as \"Invoice_id\",
				ID.InvoiceDrug_id as \"InvoiceDrug_id\",
				-- Проверка 1 – «по методу»
				I.Invoice_MethodType as \"Invoice_MethodType\",
				-- Проверка 2 – «по государственному контракту»
				supply.WhsDocumentUc_id as \"WhsDocumentUc_id\",
				-- Проверка 3 – «по текущей дате»
				to_char(I.Invoice_DateDoc, 'dd.mm.yyyy') as \"Invoice_DateDoc\",
				-- Проверка 4 – «по рабочему периоду»
				to_char(tekperiod.DrugRequestPeriod_begDate, 'dd.mm.yyyy') as \"DrugRequestPeriod_begDate\",
				to_char(tekperiod.DrugRequestPeriod_endDate, 'dd.mm.yyyy') as \"DrugRequestPeriod_endDate\",
				-- Проверка 5 – «по Поставщику»
				supplier.Contragent_id as \"Contragent_sid\",
				-- Проверка 6 – «по аптекам»
				farmacy.Contragent_id as \"Contragent_tid\",
				-- Проверка 7 – «на наличие данных о серии выпуска»
				ID.InvoiceDrug_Series as \"InvoiceDrug_Series\",
				-- Проверка 8 – «идентификация ЛС»
				rlsdrug.Drug_id as \"Drug_id\",
				rlsdrug.Prep_id as \"Prep_id\",
				rlsprepseries.PrepSeries_id as \"PrepSeries_id\",
				-- Проверка 9 – «по наличию ЛС в номенклатурном справочнике системы ЛЛО» (в rls.DrugNomen нет периода действия), Шуршалова: 'ок. актуальный опускаем'
				-- Проверка 10 – «по забраковке серии выпуска»
				rlsprepseries.PrepSeries_IsDefect as \"PrepSeries_IsDefect\",
				-- Проверка 11 – «по сроку годности ЛС», Шуршалова: 'сделай пометку, что контроль не включен - пока не делаем, чтобы не тормозить. или давай пока сделаем минимально:  дата поставки < дата срока годности
				to_char(rlsprepseries.PrepSeries_GodnDate, 'dd.mm.yyyy') as \"PrepSeries_GodnDate\",
				-- Проверка 12 – «по цене»
				ID.InvoiceDrug_Price * (1 + ID.InvoiceDrug_NDS/100) as \"InvoiceDrug_PriceNDS\", -- цена с ндс, т.к. поля PriceNDS в dbf нет
				supplyspec.WhsDocumentSupplySpec_Price * (1 + supplyspec.WhsDocumentSupplySpec_NDS/100) as \"WhsDocumentSupplySpec_PriceNDS\",
				-- Для МЭК и создания документов учета
				ID.InvoiceDrug_KoAll as \"InvoiceDrug_KoAll\",
				supply.WhsDocumentSupply_id as \"WhsDocumentSupply_id\",
				supply.Org_rid as \"SupplyOrg_rid\",
				supply.DrugFinance_id as \"DrugFinance_id\",
				supply.WhsDocumentCostItemType_id as \"WhsDocumentCostItemType_id\",
				supplier.Org_id as \"SupplierOrg_id\",
				farmacy.Org_id as \"FarmacyOrg_id\",
				to_char(supply.WhsDocumentUc_Date, 'YYYYMMDD') as \"WhsDocumentUc_Date\",
				-- Данные для описания контекста ошибки при ФЛК
				ltrim(rtrim(I.Invoice_DocN)) as \"Invoice_DocN\",
				farmacy.Org_Name as \"FaramacyName\",
				I.Invoice_gk as \"Invoice_gk\",
				(case
					when I.Invoice_CFinl = 1 then 'Федеральный'
					when I.Invoice_CFinl = 2 then 'Региональный'
					else null
				end) as \"Invoice_CFinl_Name\",
				I.Invoice_RecipCode as \"Invoice_RecipCode\",
				I.Invoice_SuplCode as \"Invoice_SuplCode\",
				ID.InvoiceDrug_NomkLs as \"InvoiceDrug_NomkLs\",
				rlsdrug.DrugNomen_Name as \"DrugNomen_Name\",
				accepted.Invoice_id as \"InvoiceAccepted_id\"
			from
				raw.v_Invoice I
				inner join v_ReceptUploadLog RUL on RUL.ReceptUploadLog_id = I.ReceptUploadLog_id
				left join v_Contragent C on C.Contragent_id = RUL.Contragent_id
				inner join raw.v_InvoiceDrug ID on ID.Invoice_id = I.Invoice_id
				left join lateral(
					-- ищем контракт с указанным поставщиком, номером и источником финансирования
					select
						wds.WhsDocumentUc_id,
						wds.WhsDocumentUc_Date,
						wds.WhsDocumentSupply_id,
						wds.Org_rid,
						wds.WhsDocumentCostItemType_id,
						DF.DrugFinance_id
					from
						v_WhsDocumentSupply wds
						inner join v_DrugFinance DF on DF.DrugFinance_id = wds.DrugFinance_id
					where
						df.DrugFinance_SysNick = case when I.Invoice_CFinl = 2 then 'reg' else 'fed' end -- источник финансирования госконтракта
						and wds.WhsDocumentUc_Num = I.Invoice_gk -- номер госконтракта
						and wds.Org_sid = C.Org_id -- поставщик указанный в загрузке
					limit 1
				) supply on true
				left join lateral(
					-- 1. В шапке документе прихода указан номер государственного контракта - по этому номеру нему нужно найти данные об этом ГК в системе.
					-- 2. данный ГК должен быть дочерним документом к Лоту на закупку.
					-- 3. медикаменты лота связаны с медикаментами сводной заявки
					-- 4. Сводная заявка составлена из заявок региона. 
					-- 5. У каждой из заявок региона есть рабочий период.
					-- 6. Текущий период = [минимальная дата из дат начала рабочих периодов заявок ; максимальная дата из дат окончания рабочих периодов заявок]
					select
						MIN(drpr.DrugRequestPeriod_begDate) as DrugRequestPeriod_begDate,
						MAX(drpr.DrugRequestPeriod_endDate) as DrugRequestPeriod_endDate
					from
						WhsDocumentUc wdu
						inner join WhsDocumentType wdt on wdu.WhsDocumentType_id = wdt.WhsDocumentType_id 
						inner join v_DrugRequestPurchaseSpec drps on drps.WhsDocumentUc_id = wdu.WhsDocumentUc_id -- медикаменты сводной заявки для лота (Шуршалова: 'WhsDocumentUc_id в DrugRequestPurchaseSpec ссылается на лот')
						inner join v_DrugRequest drsvod on drsvod.DrugRequest_id = drps.DrugRequest_id and drsvod.DrugRequestCategory_id = 3 -- сводная заявка
						inner join v_DrugRequestPurchase drp on drp.DrugRequest_id  = drsvod.DrugRequest_id -- связь заявок региона со сводными
						inner join v_DrugRequest dr on dr.DrugRequest_id = drp.DrugRequest_lid -- заявки региона
						inner join v_DrugRequestPeriod drpr on drpr.DrugRequestPeriod_id = dr.DrugRequestPeriod_id -- периоды заявок
					where wdt.WhsDocumentType_Code = 5 and wdu.WhsDocumentUc_pid = supply.WhsDocumentUc_id -- лот дочерний к ГК
				) tekperiod on true
				left join lateral(
					-- для пары – ОГРН + код Поставщика накладной, в системе должна быть найдена Организация с указанным ОГРН, период действия которой в системе не закрыт, и код этого контрагента равен указанному коду Поставщика.
					select
						c.Contragent_id, o.Org_id, o.Org_Name
					from
						v_Contragent c
						inner join v_Org o on o.Org_id = c.Org_id
					where c.Contragent_Code = I.Invoice_SuplCode and o.Org_OGRN = I.Invoice_SuplOgrn
					limit 1
				) supplier on true
				left join lateral(
					-- для пары – ОГРН + код Получателя накладной, в системе должна быть найдена Организация с указанным ОГРН, период действия которой в системе не закрыт, и, которая внесена в справочник контрагентов с типом «Аптека», и код этого контрагента равен указанному коду Получателя.
					select
						c.Contragent_id, o.Org_id, o.Org_Name
					from
						v_Contragent c
						inner join v_Org o on o.Org_id = c.Org_id
					where c.Contragent_Code = I.Invoice_RecipCode
						and o.Org_OGRN = I.Invoice_RecipOgrn
					limit 1
				) farmacy on true
				left join lateral(
					-- Для каждой записи из списка медикаментов накладных должна быть запись в справочнике РЛС о ЛС (rls.drug_id), у которого: номенклатурный код в номенклатурном справочнике равен указанному в накладной, и  есть серия выпуска равная указанной в накладной (в rls.prepseries). 
					select
						dn.Drug_id,
						dn.DrugNomen_Name,
						d.DrugPrep_id as Prep_id
					from
						rls.v_DrugNomen dn
						left join rls.v_Drug d on d.Drug_id = dn.Drug_id
					where
						dn.DrugNomen_Code = ID.InvoiceDrug_NomkLs
					limit 1
				) rlsdrug on true
				left join lateral(
					select
						ps.PrepSeries_id,
						ps.PrepSeries_IsDefect,
						ps.PrepSeries_GodnDate
					from
						rls.PrepSeries ps
					where
						PrepSeries_Ser = ID.InvoiceDrug_Series
					limit 1
				) rlsprepseries on true
				left join lateral(
					select
						wdss.WhsDocumentSupplySpec_Price,
						wdss.WhsDocumentSupplySpec_NDS
					from
						v_WhsDocumentSupplySpec wdss
					where
						wdss.WhsDocumentSupply_id = supply.WhsDocumentSupply_id and wdss.Drug_id = rlsdrug.Drug_id
					order by
						wdss.WhsDocumentSupplySpec_Price desc
					limit 1
				) supplyspec on true
				left join lateral(
					-- ищем исполненную накладную
					select
						rawi.Invoice_id
					from
						raw.v_Invoice rawi
					where
						rawi.Invoice_DocN = I.Invoice_DocN
						and rawi.Invoice_DateDoc = I.Invoice_DateDoc 
						and rawi.Invoice_CFinl = I.Invoice_CFinl
						and rawi.Invoice_gk = I.Invoice_gk
						and rawi.Invoice_SuplCode = I.Invoice_SuplCode
						and rawi.Invoice_RecipOgrn = I.Invoice_RecipOgrn
						and rawi.Invoice_RecipCode = I.Invoice_RecipCode
						and rawi.Invoice_RecipOgrn = I.Invoice_RecipOgrn
						and rawi.InvoiceStatus_id = 3
					limit 1
				) accepted on true
			where
				I.ReceptUploadLog_id = :ReceptUploadLog_id
			order by
				I.Invoice_DateDoc desc, I.Invoice_id
		";
		$res = $this->db->query($query, array('ReceptUploadLog_id' => $data['ReceptUploadLog_id']));
		if ( !is_object($res) ) {
			return false;
		}
		return $res->result('array');
	}
	
	/**
	 *	Проведение экспертизы ФЛК
	 */
	function execFLK(&$record, $allowed_methods) {
		// continuecheck если метод вернул ошибку продолжать ФЛК? (1-да 0-нет)
		switch( $record['RegistryRecept_SchetType'] ) {
			case 0: // основной
				$actions = array(
					array('method' => 'checkDoubleData', 'continuecheck' => 1 ),
					array('method' => 'checkPersonData', 'continuecheck' => 1 ),
					array('method' => 'checkOnExtractRecept', 'continuecheck' => 1 ),
					array('method' => 'checkDetailsRecept', 'continuecheck' => 1 )
				);
				break;
			case 1: // дополнительный
				$actions = array(
					array('method' => 'checkIsDelayedRecept', 'continuecheck' => 1 ),
					array('method' => 'checkIsConformityData', 'continuecheck' => 1 )
				);
				break;
			case 3: // скорректированный
				$actions = array(
					array('method' => 'checkIsOtovRecept', 'continuecheck' => 1 ),
					array('method' => 'checkIsConformityData', 'continuecheck' => 1 )
				);
				break;
			default:
				return array('Error_Msg' => "Недопустимый тип реестра. Экспертиза прервана.");
				break;
		}

		if ($record['RegistryRecept_IsDiscard'] == 2) { // Для рецептов снятых с обслуживания свой ФЛК
			$actions = array(
				array('method' => 'checkOnExtractRecept', 'continuecheck' => 1 )
			);
		}

		//$ans[$k] = array();
		$record['success'] = true;
		foreach( $actions as $action ) {
			if (in_array($action['method'], $allowed_methods)) {
				$result = $this->$action['method']($record);
				if( $result === false ) {
					$record['success'] = false;
					return array('Error_Msg' => 'Ошибка БД!');
					break;
				}

				if( is_array($result) && isset($result['ReceptStatusFLKMEK_id']) ) {
					$this->setScheme($this->schema)->setObject('RegistryRecept')->setRow($record['RegistryRecept_id'])->setValue('ReceptStatusFLKMEK_id', $result['ReceptStatusFLKMEK_id']);
					$record['success'] = false;
					// Записываем в журнал ошибок
					$res = $this->saveRegistryReceptError(array_merge($record, $result));
					if( $res === false ) {
						return array('Error_Msg' => 'Ошибка БД!');
						break;
					}
					if( is_array($res) && !empty($res[0]['Error_Msg']) ) {
						return array('Error_Msg' => $res[0]['Error_Msg']);
					}
					if( !$action['continuecheck'] ) {
						break;
					}
				}
			}
		}
		if( $record['success'] ) {
			if ( $record['RegistryRecept_IsDiscard'] != 2 ) { //Данные в ReceptOtov не сохраняются для рецептов снятых с обслуживания
				$res = $this->saveReceptOtov($record);
				if( $res === false ) {
					return array('Error_Msg' => 'Ошибка БД!');
				}
				if( is_array($res) && !empty($res[0]['Error_Msg']) ) {
					return array('Error_Msg' => $res[0]['Error_Msg']);
				}
				$record['ReceptOtov_id'] = $res[0]['ReceptOtov_id'];
				$this->setScheme($this->schema)->setObject('RegistryRecept')->setRow($record['RegistryRecept_id'])->setValue('ReceptOtov_id', $res[0]['ReceptOtov_id']);
			}
			//всем рецептам пролшедшим ФЛК проставляется статус "Принят" (ReceptStatusFLKMEK_code = 2)
			$this->setScheme($this->schema)->setObject('RegistryRecept')->setRow($record['RegistryRecept_id'])->setValue('ReceptStatusFLKMEK_id', $this->getReceptStatusFLKMEKIdByCode(2));
		}
		
		return true;
	}
	
	
	/**
	 *	Проведение экспертизы ФЛК для накладных
	 */
	function execInvoiceFLK(&$record) {
		// continuecheck если метод вернул ошибку продолжать ФЛК? (1-да 0-нет)
		
		$response = array(
			'success' => true
		);
		$actions = array();
		
		$processedInvoiceIds = array();
		$processedInvoiceDrugIds = array();
		
		switch( $record['Invoice_MethodType'] ) {
			case 'I': // добавление
				$actions = array(
					// 0 – «в списке загруженных накладных есть хотя бы одна накладная со статусом принята»;
					array('method' => 'checkDocumentUc', 'continuecheck' => 0 ),
					// 2 – «по государственному контракту»;
					array('method' => 'checkInvoiceGk', 'continuecheck' => 0 ),
					// 3 – «по текущей дате»
					array('method' => 'checkInvoiceDate', 'continuecheck' => 0 ),
					// 4 – «по рабочему периоду»
					//array('method' => 'checkInvoiceDrugRequsetPeriod', 'continuecheck' => 0 ),
					// 5 – «по Поставщику»
					array('method' => 'checkInvoiceOGRN', 'continuecheck' => 0 ),
					// 6 – «по аптекам»
					array('method' => 'checkInvoiceFarmacy', 'continuecheck' => 0 ),
					// 7 – «на наличие данных о серии выпуска»
					//array('method' => 'checkInvoiceDrugSeries', 'continuecheck' => 0 ),
					// 8 – «идентификация ЛС»
					array('method' => 'checkInvoiceDrugIdentification', 'continuecheck' => 0 ),
					// 9 – «по наличию ЛС в номенклатурном справочнике системы ЛЛО»
					array('method' => 'checkInvoiceDrugNomenLLO', 'continuecheck' => 0 ),
					// 10 – «по забраковке серии выпуска»
					//array('method' => 'checkInvoiceDefect', 'continuecheck' => 0 ),
					// 11 – «по сроку годности ЛС»
					//array('method' => 'checkInvoiceGodnDate', 'continuecheck' => 0 ),
					// 12 – «по цене»
					array('method' => 'checkInvoicePrice', 'continuecheck' => 0 )
				);
				break;
			default:
				$actions = array( 
					// 1 - по методу
					array('method' => 'checkInvoiceMethodType', 'continuecheck' => 0 ),
				);
				break;
		}
		$record['success'] = true;
		
		foreach( $actions as $action ) {
			$result = $this->$action['method']($record);
			if( $result === false ) {
				$record['success'] = false;
				return array('Error_Msg' => 'Ошибка БД!');
				break;
			}
			if( is_array($result) && isset($result['InvoiceStatus_id']) ) {
				if (!empty($result['logmessage'])) {
					$response['logmessage'] = $result['logmessage'];
					$response['InvoiceCause_id'] = $result['InvoiceCause_id'];
				}

				if (!in_array($record['Invoice_id'], $processedInvoiceIds)) { // если ещё не обновляли статус данной строке
					$query = "update raw.Invoice set InvoiceStatus_id = :InvoiceStatus_id, InvoiceCause_id = :InvoiceCause_id where Invoice_id = :Invoice_id"; // пока так
					$this->db->query($query, array(
						'InvoiceStatus_id' => $result['InvoiceStatus_id'],
						'InvoiceCause_id' => $result['InvoiceCause_id'],
						'Invoice_id' => $record['Invoice_id']
					));
					$processedInvoiceIds[] = $record['Invoice_id'];
				}
				
				if (!in_array($record['InvoiceDrug_id'], $processedInvoiceDrugIds)) { // если ещё не обновляли статус данной строке
					$query = "update raw.InvoiceDrug set InvoiceStatus_id = :InvoiceStatus_id, InvoiceCause_id = :InvoiceCause_id where InvoiceDrug_id = :InvoiceDrug_id"; // пока так
					$this->db->query($query, array(
						'InvoiceStatus_id' => $result['InvoiceStatus_id'],
						'InvoiceCause_id' => $result['InvoiceCause_id'],
						'InvoiceDrug_id' => $record['InvoiceDrug_id']
					));
					$processedInvoiceDrugIds[] = $record['InvoiceDrug_id'];
				}
				
				$record['success'] = false;
				if( !$action['continuecheck'] ) {
					break;
				}
			}
		}
		
		return $response;
	}
	
	/**
	 * ФЛК: по методу
	 */
	private function checkInvoiceMethodType($record) {
		// В поле Method шапки накладной должен быть указан метод обработки данных: один из I, U, D, пока только I.
		if (!in_array($record['Invoice_MethodType'], array('I'))) {
			return array(
				'InvoiceStatus_id' => 2,
				'InvoiceCause_id' => 8,
				// № накладной, № госконтракта, Источника финансирования
				'logmessage' => "№ накладной: {$record['Invoice_DocN']}, № госконтракта: {$record['Invoice_gk']}, Источник финансирования: {$record['Invoice_CFinl_Name']}"
			);
		}
		
		return true;
	}

	/**
	 * Проверка накладной: в списке загруженных накладных есть хотя бы одна накладная со статусом принята.
	 */
	private function checkDocumentUc($record) {
		// если найден уже созданный документ учёта
		if (!empty($record['InvoiceAccepted_id'])) {
			return array(
				'InvoiceStatus_id' => 2,
				'InvoiceCause_id' => 21,
				// № накладной, Аптека, № госконтракта, Источника финансирования
				'logmessage' => "№ накладной: {$record['Invoice_DocN']}, № госконтракта: {$record['Invoice_gk']}, Источник финансирования: {$record['Invoice_CFinl_Name']}"

			);
		}
		
		return true;
	}
	
	/**
	 * ФЛК: по государственному контракту
	 */
	private function checkInvoiceGk($record) {
		// если не найден ГК
		if (empty($record['WhsDocumentUc_id'])) {
			return array(
				'InvoiceStatus_id' => 2,
				'InvoiceCause_id' => 9,
				// № накладной, Аптека, № госконтракта, Источника финансирования
				'logmessage' => "№ накладной: {$record['Invoice_DocN']}, Аптека: {$record['FaramacyName']}, № госконтракта: {$record['Invoice_gk']}, Источник финансирования: {$record['Invoice_CFinl_Name']}"

			);
		}
		
		return true;
	}

	/**
	 * ФЛК: по дате
	 */
	private function checkInvoiceDate($record) {
		// дата «накладной» должна быть меньше или равна текущей дате
		if (strtotime($record['Invoice_DateDoc']) > time()) {
			return array(
				'InvoiceStatus_id' => 2,
				'InvoiceCause_id' => 10,
				// № накладной, Аптека, дата  накладной
				'logmessage' => "№ накладной: {$record['Invoice_DocN']}, Аптека: {$record['FaramacyName']}, Дата накладной: {$record['Invoice_DateDoc']}"
			);
		}
		
		return true;
	}
	
	/**
	 * ФЛК: по рабочему периоду
	 */
	private function checkInvoiceDrugRequsetPeriod($record) {
		// дата документа должна входить в текущий рабочий период «заявки»
		if (strtotime($record['Invoice_DateDoc']) < strtotime($record['DrugRequestPeriod_begDate']) || strtotime($record['Invoice_DateDoc']) > strtotime($record['DrugRequestPeriod_endDate'])) {
			return array(
				'InvoiceStatus_id' => 2,
				'InvoiceCause_id' => 11,
				// № накладной, дата  накладной, Источник финансирования, Аптека
				'logmessage' => "№ накладной: {$record['Invoice_DocN']}, Дата накладной: {$record['Invoice_DateDoc']}, Источник финансирования: {$record['Invoice_CFinl_Name']}, Аптека: {$record['FaramacyName']}"
			);
		}
		
		return true;
	}
		
	/**
	 * ФЛК: по поставщику
	 */
	private function checkInvoiceOGRN($record) {
		// если не найден контрагент поставщика
		if (empty($record['Contragent_sid'])) {
			return array(
				'InvoiceStatus_id' => 2,
				'InvoiceCause_id' => 12,
				// № накладной, дата  накладной, Источник финансирования, код поставщика.
				'logmessage' => "№ накладной: {$record['Invoice_DocN']}, Дата накладной: {$record['Invoice_DateDoc']}, Источник финансирования: {$record['Invoice_CFinl_Name']}, Код поставщика: {$record['Invoice_SuplCode']}}"
			);
		}

		return true;
	}
	
	/**
	 * ФЛК: по аптекам
	 */
	private function checkInvoiceFarmacy($record) {
		// если не найден контрагент аптеки
		if (empty($record['Contragent_tid'])) {
			return array(
				'InvoiceStatus_id' => 2,
				'InvoiceCause_id' => 13,
				// № накладной, дата  накладной, Источник финансирования, код аптеки.
				'logmessage' => "№ накладной: {$record['Invoice_DocN']}, Дата накладной: {$record['Invoice_DateDoc']}, Источник финансирования: {$record['Invoice_CFinl_Name']}, Код аптеки: {$record['Invoice_RecipCode']}}"
			);
		}
		
		return true;
	}
		
	/**
	 * ФЛК: на наличие данных о серии выпуска
	 */
	private function checkInvoiceDrugSeries($record) {
		// если не указана серия выпуска
		if (empty($record['InvoiceDrug_Series'])) {
			return array(
				'InvoiceStatus_id' => 2,
				'InvoiceCause_id' => 14,
				// № накладной, дата  накладной, Источник финансирования, код аптеки, Аптека, номенклатурный код ЛС, наименование ЛС.
				'logmessage' => "№ накладной: {$record['Invoice_DocN']}, Дата накладной: {$record['Invoice_DateDoc']}, Источник финансирования: {$record['Invoice_CFinl_Name']}, Код аптеки: {$record['Invoice_RecipCode']}, Аптека: {$record['FaramacyName']}, Номенклатурный код ЛС: {$record['InvoiceDrug_NomkLs']}, Наименование ЛС: {$record['DrugNomen_Name']}"
			);
		}
		
		return true;
	}	
	
	/**
	 * ФЛК: идентификация ЛС
	 */
	private function checkInvoiceDrugIdentification(&$record) {
		// если указана серия и не найдена в PrepSeries то добавляем новую серию
		if (!empty($record['InvoiceDrug_Series']) && !empty($record['Prep_id']) && empty($record['PrepSeries_id'])) {
			// пробуем найти серию
			$query = "
				select
					ps.PrepSeries_id as \"PrepSeries_id\",
					ps.PrepSeries_IsDefect as \"PrepSeries_IsDefect\",
					ps.PrepSeries_GodnDate as \"PrepSeries_GodnDate\"
				from
					rls.PrepSeries ps
				where
					PrepSeries_Ser = :PrepSeries_Ser
				limit 1
			";
			
			$res = $this->db->query($query, array(
				'PrepSeries_Ser' => trim($record['InvoiceDrug_Series'])
			));
			
			// если нашли
			if ( is_object($res) ) {
				$resp = $res->result('array');
				if (count($resp) > 0) {
					$record['PrepSeries_id'] = $resp[0]['PrepSeries_id'];
				}
			}
			
			// если не нашли - добавляем
			if (empty($record['PrepSeries_id'])) {
				$query = "
					select
						PrepSeries_id as \"PrepSeries_id\",
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Msg\"
					from rls.p_PrepSeries_ins(
						Prep_id := :Prep_id,
						PrepSeries_Ser := :PrepSeries_Ser,
						PrepSeries_GodnDate := null,
						PackNx_Code := null,
						PrepSeries_IsDefect := null,
						pmUser_id := :pmUser_id
					)
				";
				
				$res = $this->db->query($query, array(
					'PrepSeries_Ser' => trim($record['InvoiceDrug_Series']),
					'Prep_id' => $record['Prep_id'],
					'pmUser_id' => $record['pmUser_id']
				));
				
				if ( is_object($res) ) {
					$resp = $res->result('array');
					if (count($resp) > 0) {
						$record['PrepSeries_id'] = $resp[0]['PrepSeries_id'];
					}
				}
			}
		}
		
		// если не удалось идентифицировать ЛС и серию
		if (empty($record['Drug_id']) || empty($record['PrepSeries_id'])) {
			return array(
				'InvoiceStatus_id' => 2,
				'InvoiceCause_id' => 15,
				// № накладной, дата  накладной, Источник финансирования, код аптеки, Аптека, номенклатурный код ЛС, серия выпуска, наименование ЛС
				'logmessage' => "№ накладной: {$record['Invoice_DocN']}, Дата накладной: {$record['Invoice_DateDoc']}, Источник финансирования: {$record['Invoice_CFinl_Name']}, Код аптеки: {$record['Invoice_RecipCode']}, Аптека: {$record['FaramacyName']}, Номенклатурный код ЛС: {$record['InvoiceDrug_NomkLs']}, Серия выпуска: {$record['InvoiceDrug_Series']}, Наименование ЛС: {$record['DrugNomen_Name']}"
			);
		}
		
		return true;
	}	
	
	/**
	 * ФЛК: по наличию ЛС в номенклатурном справочнике системы ЛЛО
	 */
	private function checkInvoiceDrugNomenLLO($record) {
		// если не удалось идентифицировать ЛС в номенклатурном справочнике
		if (empty($record['Drug_id'])) {
			return array(
				'InvoiceStatus_id' => 2,
				'InvoiceCause_id' => 16,
				// № накладной, дата  накладной, Источник финансирования, код аптеки, Аптека, номенклатурный код ЛС, серия выпуска, наименование ЛС.
				'logmessage' => "№ накладной: {$record['Invoice_DocN']}, Дата накладной: {$record['Invoice_DateDoc']}, Источник финансирования: {$record['Invoice_CFinl_Name']}, Код аптеки: {$record['Invoice_RecipCode']}, Аптека: {$record['FaramacyName']}, Номенклатурный код ЛС: {$record['InvoiceDrug_NomkLs']}, Серия выпуска: {$record['InvoiceDrug_Series']}, Наименование ЛС: {$record['DrugNomen_Name']}"
			);
		}
		
		return true;
	}	
	
	/**
	 * ФЛК: по забраковке серии выпуска
	 */
	private function checkInvoiceDefect($record) {
		// если не удалось идентифицировать ЛС
		if (!empty($record['PrepSeries_IsDefect']) && $record['PrepSeries_IsDefect'] == 2) {
			return array(
				'InvoiceStatus_id' => 2,
				'InvoiceCause_id' => 17,
				// № накладной, дата  накладной, Источник финансирования, код аптеки, Аптека, номенклатурный код ЛС, серия выпуска, наименование ЛС.
				'logmessage' => "№ накладной: {$record['Invoice_DocN']}, Дата накладной: {$record['Invoice_DateDoc']}, Источник финансирования: {$record['Invoice_CFinl_Name']}, Код аптеки: {$record['Invoice_RecipCode']}, Аптека: {$record['FaramacyName']}, Номенклатурный код ЛС: {$record['InvoiceDrug_NomkLs']}, Серия выпуска: {$record['InvoiceDrug_Series']}, Наименование ЛС: {$record['DrugNomen_Name']}"
			);
		}
		
		return true;
	}
	
	/**
	 * ФЛК: по сроку годности
	 */
	private function checkInvoiceGodnDate($record) {
		// Шуршалова: 'пока сделаем минимально:  дата поставки < дата срока годности
		if (strtotime($record['Invoice_DateDoc']) > strtotime($record['PrepSeries_GodnDate'])) {
			return array(
				'InvoiceStatus_id' => 2, 
				'InvoiceCause_id' => 18,
				// № накладной, дата  накладной, Источник финансирования, код аптеки, Аптека, номенклатурный код ЛС, серия выпуска, наименование ЛС срок годности, остаточный срок годности. 
				'logmessage' => "№ накладной: {$record['Invoice_DocN']}, Дата накладной: {$record['Invoice_DateDoc']}, Источник финансирования: {$record['Invoice_CFinl_Name']}, Код аптеки: {$record['Invoice_RecipCode']}, Аптека: {$record['FaramacyName']}, Номенклатурный код ЛС: {$record['InvoiceDrug_NomkLs']}, Серия выпуска: {$record['InvoiceDrug_Series']}, Наименование ЛС: {$record['DrugNomen_Name']}, Срок годности: {$record['PrepSeries_GodnDate']}"
			);
		}
		
		return true;
	}
	
	/**
	 * ФЛК: по цене
	 */
	private function checkInvoicePrice($record) {
		// цена с НДС должна быть меньше или равна цене с НДС в госконтракте. 
		if ($record['InvoiceDrug_PriceNDS'] > $record['WhsDocumentSupplySpec_PriceNDS']) {
			return array(
				'InvoiceStatus_id' => 2, 
				'InvoiceCause_id' => 19,
				// Источник финансирования, код аптеки, Аптека, номенклатурный код ЛС, наименование ЛС, цена ЛС, цена ГК. 
				'logmessage' => "Источник финансирования: {$record['Invoice_CFinl_Name']}, Код аптеки: {$record['Invoice_RecipCode']}, Аптека: {$record['FaramacyName']}, Номенклатурный код ЛС: {$record['InvoiceDrug_NomkLs']}, Наименование ЛС: {$record['DrugNomen_Name']}, Цена ЛС: {$record['InvoiceDrug_PriceNDS']}, Цена ГК: {$record['WhsDocumentSupplySpec_PriceNDS']}"
			);
		}
		
		return true;
	}

	/**
	 *	МЭК: проверка наличия медикамента на остатках поставщика
	 */
	function checkInvoiceSupplierDrugOstatRegistry($record) {
		// На остатках постовщика должно быть необходимое количество медикаментов
		$query = "
			select
				coalesce(sum(dor.DrugOstatRegistry_Kolvo), 0) as cnt
			from
				DrugOstatRegistry dor
				left join DrugShipment ds on ds.DrugShipment_id = dor.DrugShipment_id
			where
				dor.SubAccountType_id = 1 and
				dor.Drug_id = :Drug_id and
				dor.Org_id = :Org_id and
				ds.WhsDocumentSupply_id = :WhsDocumentSupply_id;
		";
		$res = $this->db->query($query, array(
			'Drug_id' => $record['Drug_id'],
			'Org_id' => $record['SupplierOrg_id'],
			'WhsDocumentSupply_id' => $record['WhsDocumentSupply_id']
		));
		if ( !is_object($res) ) {
			return false;
		}
		$res = $res->result('array');
		if( $res[0]['cnt'] < $record['InvoiceDrug_KoAll'] ) {
			return array(
				'InvoiceStatus_id' => 2,
				'InvoiceCause_id' => 6,
				// № накладной, дата  накладной, Источник финансирования, код аптеки, Аптека, номенклатурный код ЛС, наименование ЛС
				'logmessage' => "№ накладной: {$record['Invoice_DocN']}, Дата накладной: {$record['Invoice_DateDoc']}, Источник финансирования: {$record['Invoice_CFinl_Name']}, Код аптеки: {$record['Invoice_RecipCode']}, Аптека: {$record['FaramacyName']}, Номенклатурный код ЛС: {$record['InvoiceDrug_NomkLs']}, Наименование ЛС: {$record['DrugNomen_Name']}"
			);
		}
		return true;
	}
	
	/**
	 *	МЭК: проверка льготы
	 */
	function checkPersonPrivilege($record) {
		// 1. Льгота, указанная у Человека в обеспеченном рецепте, должна быть актуальной на дату выписки рецепта
		$query = "
			select
				COUNT(*) as cnt
			from
				PersonPrivilege PP
				left join ReceptOtov RO on RO.Person_id = PP.Person_id	and RO.PrivilegeType_id = PP.PrivilegeType_id
			where
				PP.Person_id = :Person_id
				and cast(PP.PersonPrivilege_begDate as date) <= cast(:RegistryRecept_setDT as date)
				and (
					cast(PP.PersonPrivilege_endDate as date) >= cast(:RegistryRecept_setDT as date)
					or PP.PersonPrivilege_endDate is null
				)
		";
		// 				--RO.ReceptOtov_id = :ReceptOtov_id
		$res = $this->db->query($query, array(
			//'ReceptOtov_id' => $record['ReceptOtov_id'],
			'Person_id' => $record['Person_id'],
			'RegistryRecept_setDT' => $record['RegistryRecept_setDT']
		));
		if ( !is_object($res) ) {
			return false;
		}
		$res = $res->result('array');
		if( $res[0]['cnt'] == 0 ) {
			return array('ReceptStatusFLKMEK_id' => $this->getReceptStatusFLKMEKIdByCode(1), 'RegistryReceptErrorType_id' => $this->getRegistryReceptErrorTypeIdByCode('П03'));
		}
		return true;
	}

	/**
	 *	МЭК: Проверки по ЛС
	 */
	function checkOnDrug($record) {
		// 3. должно соответствовать выписанному в рецепте
		$query = "			
			with mv1 as  ( 
				select
					Actmatters_id as a
				from
					rls.DrugComplexMnn DCM
					left join rls.Drug Drug on Drug.DrugComplexMnn_id = DCM.DrugComplexMnn_id
				where
					Drug.Drug_id = :Drug_id
			), mv2 as (
				select
					Actmatters_id as a
				from
					rls.DrugComplexMnn DCM
					left join rls.Drug Drug on Drug.DrugComplexMnn_id = DCM.DrugComplexMnn_id
					left join EvnRecept ER on ER.Drug_rlsid = Drug.Drug_id
				where
					ER.Evn_id = :EvnRecept_id
			)

			select
				case when (select a from mv1) = (select a from mv2)
					then 1
					else 0
				end as \"isSuccess\"
		";
		$res = $this->db->query($query, array(
			'Drug_id' => $record['Drug_id'],
			'EvnRecept_id' => $record['EvnRecept_id']
		));
		if ( !is_object($res) ) {
			return false;
		}
		$res = $res->result('array');
		if( $res[0]['isSuccess'] == 0 ) {
			return array('ReceptStatusFLKMEK_id' => $this->getReceptStatusFLKMEKIdByCode(1), 'RegistryReceptErrorType_id' => $this->getRegistryReceptErrorTypeIdByCode('Р05'));
		}
		
		// 4. цена ЛС должна соответствовать цене ЛС в указанном ГК
		$query = "
			select
				WDSS.WhsDocumentSupplySpec_PriceNDS as \"WhsDocumentSupplySpec_PriceNDS\"
			from
				dbo.WhsDocumentSupplySpec WDSS
				left join WhsDocumentSupply WDS on WDS.WhsDocumentSupply_id = WDSS.WhsDocumentSupply_id
				left join WhsDocumentUc WDU on WDU.WhsDocumentUc_id = WDS.WhsDocumentUc_id
			where
				WDU.WhsDocumentUc_Num = :WhsDocumentUc_Num
			order by
				WDSS.WhsDocumentSupplySpec_PriceNDS desc
			limit 1
		";
		$res = $this->db->query($query, array(
			'WhsDocumentUc_Num' => $record['RegistryRecept_SupplyNum']
		));
		if ( !is_object($res) ) {
			return false;
		}
		$res = $res->result('array');
		if( count($res) == 0 ) {
			return false;
		}
		$WhsDocumentSupplySpec_PriceNDS = (float) $res[0]['WhsDocumentSupplySpec_PriceNDS'];
		if( $record['RegistryRecept_Price']*(100/$record['RegistryRecept_Persent'])/$record['RegistryRecept_DrugKolvo'] > $WhsDocumentSupplySpec_PriceNDS ) {
			return array('ReceptStatusFLKMEK_id' => $this->getReceptStatusFLKMEKIdByCode(1), 'RegistryReceptErrorType_id' => $this->getRegistryReceptErrorTypeIdByCode('Л02'));
		}
		
		return true;
	}
	
	
	/**
	 *	МЭК: проверка остатков Аптеки
	 */
	function checkBalancesPharmacy($record) {
		// 5. По каждой серии ЛС, отпущенного по рецепту на остатках аптеки должно быть достаточно кол-ва ЛС, необходимого для отпуска по рецепту
		
		// Серии ЛС в рецептах пока не предоставляются, даже поля нет такого (пока).
		// Можно сделать типа заглушки: переменную, которая определяет наличие данных о партии выпуска в реестре рецептов и присвоить ее значение  =  по умолчанию ложное.
		// (c) со слов Шуршаловой
		$isReleaseParty = false;
		
		$query = "
			select
				coalesce(sum(DOR.DrugOstatRegistry_Kolvo), 0) as \"DrugOstatSum\"
			from
				dbo.DrugOstatRegistry DOR
			where
				DOR.Org_id = ( select Org_id from Contragent where Contragent_Code = :Contragent_Code and ContragentType_id = 3 )
				and DOR.SubAccountType_id = 1
				and DOR.DrugFinance_id = :DrugFinance_id
				and DOR.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id
				and DOR.Drug_id = :Drug_id
				and DOR.DrugShipment_id in (
					select
						DS.DrugShipment_id
					from
						DrugShipment DS
						left join WhsDocumentSupply WDS on WDS.WhsDocumentSupply_id = DS.WhsDocumentSupply_id
						left join WhsDocumentUc WDU on WDU.WhsDocumentUc_id = WDS.WhsDocumentUc_id
					where
						WDU.WhsDocumentUc_Num = :WhsDocumentUc_Num
				)
		";

		$res = $this->db->query($query, array(
			'WhsDocumentUc_Num' => $record['RegistryRecept_SupplyNum'],
			'Contragent_Code' => $this->getCode($record['RegistryRecept_FarmacyACode'], 'con'),
			'DrugFinance_id' => $record['DrugFinance_id'],
			'WhsDocumentCostItemType_id' => $record['WhsDocumentCostItemType_id'],
			'Drug_id' => $record['Drug_id']
		));
		if ( !is_object($res) ) {
			return false;
		}
		$res = $res->result('array');
		if( $res[0]['DrugOstatSum'] < $record['RegistryRecept_DrugKolvo'] ) {
			return array('ReceptStatusFLKMEK_id' => $this->getReceptStatusFLKMEKIdByCode(1), 'RegistryReceptErrorType_id' => $this->getRegistryReceptErrorTypeIdByCode('Р13'));
		}
		
		return true;
	}


	/**
	 *	МЭК (сводных реестров рецептов): проверка на наличии копии реццепта, среди ранее загруженных
	 */
	function checkSvodFindRecept($record) {
		//по номеру и серии текущего рецепта найти в таблице загрузки запись с максимальной датой приема данных, тип которых "сведения о рецептах".
		//если такая запись не найдена: ошибка МЭК, данные о рецепте в системе отсутствуют;
		//если такая запись найдена, выполнить проверку: все реквизиты текущего рецепта, предоставленные поставщиком,  должны совпадать с аналогичными реквизитами найденного рецепта;

		$query = "
			with mv as (
				select
					RegistryRecept_id as OldRecept_id
				from
					{$this->schema}.RegistryRecept
				where
					RegistryRecept_Recent = :RegistryRecept_Recent 
                    and
					RegistryReceptType_id = 1 and --Реестр рецептов
					ReceptStatusFLKMEK_id = :ReceptStatusFLKMEK_id
				order by
					RegistryRecept_id desc
				limit 1
			), mv2 as ( 
				select
					count(rr1.RegistryRecept_id) as e
				from
					{$this->schema}.RegistryRecept rr1
					inner join 
                    {$this->schema}.RegistryRecept rr2 on rr2.RegistryRecept_id = :RegistryRecept_id
				where
					rr1.RegistryRecept_id = (select OldRecept_id from mv) and
					coalesce(rr1.RegistryRecept_Snils, '') = coalesce(rr2.RegistryRecept_Snils, '') and
					coalesce(CAST(rr1.RegistryRecept_UAddOKATO as varchar), '') = coalesce(CAST(rr2.RegistryRecept_UAddOKATO as varchar), '') and
					coalesce(rr1.RegistryRecept_LpuOGRN, '') = coalesce(rr2.RegistryRecept_LpuOGRN, '') and
					coalesce(rr1.RegistryRecept_LpuMod, '') = coalesce(rr2.RegistryRecept_LpuMod, '') and
					coalesce(rr1.RegistryRecept_MedPersonalCode, '') = coalesce(rr2.RegistryRecept_MedPersonalCode, '') and
					coalesce(rr1.RegistryRecept_Diag, '') = coalesce(rr2.RegistryRecept_Diag, '') and
					coalesce(CAST(rr1.RegistryRecept_setDT as varchar), '') = coalesce(CAST(rr2.RegistryRecept_setDT as varchar), '') and
					coalesce(CAST(rr1.RegistryRecept_RecentFinance as varchar), '') = coalesce(CAST(rr2.RegistryRecept_RecentFinance as varchar), '') and
					coalesce(CAST(rr1.RegistryRecept_Persent as varchar), '') = coalesce(CAST(rr2.RegistryRecept_Persent as varchar), '') and
					coalesce(rr1.RegistryRecept_FarmacyACode, '') = coalesce(rr2.RegistryRecept_FarmacyACode, '') and
					coalesce(CAST(rr1.RegistryRecept_DrugNomCode as varchar), '') = coalesce(CAST(rr2.RegistryRecept_DrugNomCode as varchar), '') and
					coalesce(CAST(rr1.RegistryRecept_DrugKolvo as varchar), '') = coalesce(CAST(rr2.RegistryRecept_DrugKolvo as varchar), '') and
					coalesce(CAST(rr1.RegistryRecept_DrugDose as varchar), '') = coalesce(CAST(rr2.RegistryRecept_DrugDose as varchar), '') and
					coalesce(CAST(rr1.RegistryRecept_DrugCode as varchar), '') = coalesce(CAST(rr2.RegistryRecept_DrugCode as varchar), '') and
					coalesce(CAST(rr1.RegistryRecept_obrDate as varchar), '') = coalesce(CAST(rr2.RegistryRecept_obrDate as varchar), '') and
					coalesce(CAST(rr1.RegistryRecept_otpDate as varchar), '') = coalesce(CAST(rr2.RegistryRecept_otpDate as varchar), '') and
					coalesce(CAST(rr1.RegistryRecept_Price as varchar), '') = coalesce(CAST(rr2.RegistryRecept_Price as varchar), '') and
					coalesce(rr1.RegistryRecept_FarmacyOGRN, '') = coalesce(rr2.RegistryRecept_FarmacyOGRN, '') and
					coalesce(CAST(rr1.RegistryRecept_ProtoKEK as varchar), '') = coalesce(CAST(rr2.RegistryRecept_ProtoKEK as varchar), '') and
					coalesce(rr1.RegistryRecept_SpecialCase, '') = coalesce(rr2.RegistryRecept_SpecialCase, '') and
					coalesce(rr1.RegistryRecept_ReceptId, '') = coalesce(rr2.RegistryRecept_ReceptId, '') and
					coalesce(rr1.RegistryRecept_SupplyNum, '') = coalesce(rr2.RegistryRecept_SupplyNum, '')
			)

			select
				case when (select OldRecept_id from mv) is not null
					then (select OldRecept_id from mv)
					else null
				end as \"OldRecept_id\",
				case when (select OldRecept_id from mv) is not null
					then (select e from mv2)
					else 0
				end as \"equivalent_cnt\"
		";
		$res = $this->db->query($query, array(
			'RegistryRecept_id' => $record['RegistryRecept_id'],
			'RegistryRecept_Recent' => $record['RegistryRecept_Recent'],
			'ReceptStatusFLKMEK_id' => $this->getReceptStatusFLKMEKIdByCode(3) //3 - Годен к оплате
		));
		if ( !is_object($res) ) {
			return false;
		}
		$res = $res->result('array');
		if (empty($res[0]['OldRecept_id'])) {
			//Статус: отказ; Ошибка: Данные о рецепте не найдены в системе;
			return array('ReceptStatusFLKMEK_id' => $this->getReceptStatusFLKMEKIdByCode(1), 'RegistryReceptErrorType_id' => $this->getRegistryReceptErrorTypeIdByCode('С01'));
		} else {
			if ($res[0]['equivalent_cnt'] > 0) {
				//Статус: годен к оплате;
				return array('ReceptStatusFLKMEK_id' => $this->getReceptStatusFLKMEKIdByCode(3), 'OldRegistryRecept_id' => $res[0]['OldRecept_id']);
			} else {
				//Статус: отказ; Ошибка: Данные о рецепте в сводном реестре не совпадают с данными в системе;
				return array('ReceptStatusFLKMEK_id' => $this->getReceptStatusFLKMEKIdByCode(1), 'OldRegistryRecept_id' => $res[0]['OldRecept_id'], 'RegistryReceptErrorType_id' => $this->getRegistryReceptErrorTypeIdByCode('С02'));
			}
		}
	}
	
	/**
	 * Сохранение документа учета.
	 */
	function saveDocumentUc($data) {
		$action = "ins";
		$query = "
			with mv as (
				select
					Org_id
				from v_OrgFarmacy
				where OrgFarmacy_id = :OrgFarmacy_id
				limit 1
			)

			select
				DocumentUc_id as \"DocumentUc_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from dbo.p_DocumentUc_{$action}(
				DocumentUc_id := :DocumentUc_id,
				DocumentUc_pid := null,
				DocumentUc_Num := :DocumentUc_Num,
				DocumentUc_setDate := :DocumentUc_setDate,
				DocumentUc_didDate := null,
				DocumentUc_DogNum := :DocumentUc_DogNum,
				DocumentUc_DogDate := (select WhsDocumentUc_Date from WhsDocumentUc where WhsDocumentUc_Num = :DocumentUc_DogNum limit 1),
				DocumentUc_InvNum := null,
				DocumentUc_InvDate := null,
				DocumentUc_Sum := :DocumentUc_Sum,
				DocumentUc_SumR := :DocumentUc_SumR,
				DocumentUc_SumNds := :DocumentUc_SumNds,
				DocumentUc_SumNdsR := :DocumentUc_SumNdsR,
				Lpu_id := null,
				Contragent_id := (select Contragent_id from Contragent where Org_id = (select Org_id from mv) and ContragentType_id = 3 limit 1),
				Contragent_sid := (select Contragent_id from Contragent where Org_id = (select Org_id from mv) and ContragentType_id = 3 limit 1),
				Mol_sid := null,
				Contragent_tid := null,
				Mol_tid := null,
				DrugFinance_id := :DrugFinance_id,
				DrugDocumentType_id := (select DrugDocumentType_id from v_DrugDocumentType where DrugDocumentType_SysNick = 'DocReal' limit 1),
				DrugDocumentStatus_id := :DrugDocumentStatus_id,
				Org_id := (select Org_id from mv),
				Storage_sid := null,
				SubAccountType_sid := :SubAccountType_sid,
				Storage_tid := null,
				SubAccountType_tid := null,
				WhsDocumentCostItemType_id := :WhsDocumentCostItemType_id,
				pmUser_id := :pmUser_id
			)
		";
		$res = $this->db->query($query, array(
			'DocumentUc_id' => null,
			'DocumentUc_Num' => $data['RegistryRecept_Ser'].' '.$data['RegistryRecept_Num'],
			'DocumentUc_setDate' => $data['EvnRecept_otpDate'],
			'DocumentUc_DogNum' => $data['RegistryRecept_SupplyNum'],
			'DocumentUc_Sum' => null,
			'DocumentUc_SumR' => null,
			'DocumentUc_SumNds' => null,
			'DocumentUc_SumNdsR' => null,
			'DrugFinance_id' => $data['DrugFinance_id'],
			'DrugDocumentType_id' => 2,
			'DrugDocumentStatus_id' => 1,
			'OrgFarmacy_id' => $data['OrgFarmacy_id'],
			'SubAccountType_sid' => 1,
			'WhsDocumentCostItemType_id' => $data['WhsDocumentCostItemType_id'],
			'pmUser_id' => $data['pmUser_id']
		));
		if ( !is_object($res) ) {
			return false;
		}
		return $res->result('array');
	}

	/**
	 *  Сохранение ошибок по реестрам.
	 */
	function saveRegistryReceptError($data) {
		$action = "ins";
		$query = "
			select
				RegistryReceptError_id as \"RegistryReceptError_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$this->schema}.p_RegistryReceptError_{$action}(
				RegistryReceptError_id := :RegistryReceptError_id,
				RegistryRecept_id := :RegistryRecept_id,
				EvnRecept_id := :EvnRecept_id,
				RegistryReceptErrorType_id := :RegistryReceptErrorType_id,
				pmUser_id := :pmUser_id
			)
		";
		$res = $this->db->query($query, array(
			'RegistryReceptError_id' => null,
			'RegistryRecept_id' => $data['RegistryRecept_id'],
			'EvnRecept_id' => isset($data['EvnRecept_id']) ? $data['EvnRecept_id'] : null,
			'RegistryReceptErrorType_id' => $data['RegistryReceptErrorType_id'],
			'pmUser_id' => $data['pmUser_id']
		));
		if ( !is_object($res) ) {
			return false;
		}
		return $res->result('array');
	}
	
	/**
	 *	Проведение экспертизы МЭК
	 */
	function execMEK(&$record, $allowed_methods) {
		$actions = array(
			array('method' => 'checkPersonPrivilege'),
			array('method' => 'checkOnDrug'),
			array('method' => 'checkBalancesPharmacy')
		);
		
		// Находим недостающую информацию об обеспеченном рецепте
		$query = "
			select
				 ReceptOtov_id as \"ReceptOtov_id\"
				,DrugFinance_id as \"DrugFinance_id\"
				,WhsDocumentCostItemType_id as \"WhsDocumentCostItemType_id\"
				,Drug_cid as \"Drug_id\"
				,Drug_cid as \"Drug_id\"
				,to_char(cast(EvnRecept_otpDate as date), 'yyyy-mm-dd') as \"EvnRecept_otpDate\"
				,OrgFarmacy_id as \"OrgFarmacy_id\"
			from
				ReceptOtov
			where
				ReceptOtov_id = :ReceptOtov_id
			limit 1
		";
		$result = $this->db->query($query, array('ReceptOtov_id' => $record['ReceptOtov_id']));
		if ( !is_object($result) ) {
			return array('Error_Msg' => 'Ошибка БД!');
		}
		$res1 = $result->result('array');
		if( count($res1) == 0 ) {
			// Если нет записи в ReceptOtov, значит в процессе ФЛК рецепт не сохранился
			return array('Error_Msg' => 'Ошибка БД!');
		} else {
			$record = array_merge($record, $res1[0]);
		}
		
		$record['success'] = true;
		foreach($actions as $action) {
			if (in_array($action['method'], $allowed_methods)) {
				$response = $this->$action['method']($record);
				if( $response === false ) {
					$record['success'] = false;
					return array('Error_Msg' => 'Ошибка БД!');
					break;
				}
				if( is_array($response) && isset($response['RegistryReceptErrorType_id']) ) {
					$this->setScheme($this->schema)->setObject('RegistryRecept')->setRow($record['RegistryRecept_id'])->setValue('ReceptStatusFLKMEK_id', $response['ReceptStatusFLKMEK_id']);
					$record['success'] = false;
					// Записываем в журнал ошибок
					$res = $this->saveRegistryReceptError(array_merge($record, $response));
					if( $res === false ) {
						return array('Error_Msg' => 'Ошибка БД!');
						break;
					}
					if( is_array($res) && !empty($res[0]['Error_Msg']) ) {
						return array('Error_Msg' => $res[0]['Error_Msg']);
					}
				}
			}
		}
		if( $record['success'] ) {
			//всем рецептам пролшедшим МЭК проставляется статус "Годен к оплате" (ReceptStatusFLKMEK_code = 3)
			$this->setScheme($this->schema)->setObject('RegistryRecept')->setRow($record['RegistryRecept_id'])->setValue('ReceptStatusFLKMEK_id', $this->getReceptStatusFLKMEKIdByCode(3));
		}
		return true;
	}

	/**
	 *	Проведение экспертизы МЭК для сводных реестров рецептов
	 */
	function execSvodMEK(&$record) {
		$actions = array(
			array('method' => 'checkSvodFindRecept')
		);

		$record['to_payment'] = false; //рецепт годен к оплате
		$record['WhsDocumentSupply_id'] = null;
		$record['DrugFinance_id'] = null;
		$record['WhsDocumentCostItemType_id'] = null;
		$record['OldRegistryRecept_id'] = null;

		// Находим информацию о ГК
		$query = "
			select
				WhsDocumentSupply_id as \"WhsDocumentSupply_id\",
				DrugFinance_id as \"DrugFinance_id\",
				WhsDocumentCostItemType_id as \"WhsDocumentCostItemType_id\"
			from
				v_WhsDocumentSupply
			where
				WhsDocumentUc_Num = :RegistryRecept_SupplyNum
			limit 1
		";

		$result = $this->db->query($query, array('RegistryRecept_SupplyNum' => $record['RegistryRecept_SupplyNum']));

		if (!is_object($result)) {
			return array('Error_Msg' => 'Ошибка БД!');
		}
		$res = $result->result('array');
		if(count($res) > 0 && isset($res[0]['WhsDocumentSupply_id']) && $res[0]['WhsDocumentSupply_id'] > 0 && isset($res[0]['DrugFinance_id']) && $res[0]['DrugFinance_id'] > 0 && isset($res[0]['WhsDocumentCostItemType_id']) && $res[0]['WhsDocumentCostItemType_id'] > 0) {
			$record['WhsDocumentSupply_id'] = $res[0]['WhsDocumentSupply_id'];
			$record['DrugFinance_id'] = $res[0]['DrugFinance_id'];
			$record['WhsDocumentCostItemType_id'] = $res[0]['WhsDocumentCostItemType_id'];
		}

		$success = true;
		foreach($actions as $action) {
			$response = $this->$action['method']($record);
			if( $response === false ) {
				$success = false;
				return array('Error_Msg' => 'Ошибка БД!');
				break;
			}
			if(is_array($response)) {
				if (isset($response['OldRegistryRecept_id'])) {
					$record['OldRegistryRecept_id'] = $response['OldRegistryRecept_id'];
				}
				if (isset($response['ReceptStatusFLKMEK_id'])) {
					// Обновляем статус рецепта
					$this->setScheme($this->schema)->setObject('RegistryRecept')->setRow($record['RegistryRecept_id'])->setValue('ReceptStatusFLKMEK_id', $response['ReceptStatusFLKMEK_id']);
				}
				if (isset($response['RegistryReceptErrorType_id'])) {
					// Записываем в журнал ошибок
					$res = $this->saveRegistryReceptError(array_merge($record, $response));
					if( $res === false ) {
						return array('Error_Msg' => 'Ошибка БД!');
						break;
					}
					if( is_array($res) && !empty($res[0]['Error_Msg']) ) {
						return array('Error_Msg' => $res[0]['Error_Msg']);
					}
				} else {
					$record['to_payment'] = true;
				}
			}
		}
		return true;
	}

	/**
	 *	Проведение экспертизы МЭК для накладных
	 */
	function execInvoiceMEK(&$record) {
		// continuecheck если метод вернул ошибку продолжать ФЛК? (1-да 0-нет)

		$response = array(
			'success' => true
		);
		$actions = array();

		$processedInvoiceIds = array();
		$processedInvoiceDrugIds = array();

		switch( $record['Invoice_MethodType'] ) {
			case 'I': // добавление
				$actions = array(
					// на наличие медикамента на остатках поставщика;
					array('method' => 'checkInvoiceSupplierDrugOstatRegistry', 'continuecheck' => 0 )
				);
				break;
			default:
				break;
		}
		if (!isset($record['success'])) { // не надо перезаписываться success записи, если уже был определён в ФЛК.
			$record['success'] = true;
		}

		foreach( $actions as $action ) {
			$result = $this->$action['method']($record);
			if( $result === false ) {
				$record['success'] = false;
				return array('Error_Msg' => 'Ошибка БД!');
				break;
			}

			if( is_array($result) && isset($result['InvoiceStatus_id']) ) {
				if (!empty($result['logmessage'])) {
					$response['logmessage'] = $result['logmessage'];
					$response['InvoiceCause_id'] = $result['InvoiceCause_id'];
				}

				if (!in_array($record['Invoice_id'], $processedInvoiceIds)) { // если ещё не обновляли статус данной строке
					$query = "update raw.Invoice set InvoiceStatus_id = :InvoiceStatus_id, InvoiceCause_id = :InvoiceCause_id where Invoice_id = :Invoice_id"; // пока так
					$this->db->query($query, array(
						'InvoiceStatus_id' => $result['InvoiceStatus_id'],
						'InvoiceCause_id' => $result['InvoiceCause_id'],
						'Invoice_id' => $record['Invoice_id']
					));
					$processedInvoiceIds[] = $record['Invoice_id'];
				}

				if (!in_array($record['InvoiceDrug_id'], $processedInvoiceDrugIds)) { // если ещё не обновляли статус данной строке
					$query = "update raw.InvoiceDrug set InvoiceStatus_id = :InvoiceStatus_id, InvoiceCause_id = :InvoiceCause_id where InvoiceDrug_id = :InvoiceDrug_id"; // пока так
					$this->db->query($query, array(
						'InvoiceStatus_id' => $result['InvoiceStatus_id'],
						'InvoiceCause_id' => $result['InvoiceCause_id'],
						'InvoiceDrug_id' => $record['InvoiceDrug_id']
					));
					$processedInvoiceDrugIds[] = $record['InvoiceDrug_id'];
				}

				$record['success'] = false;
				if( !$action['continuecheck'] ) {
					break;
				}
			}
		}

		return $response;
	}

	/**
	 *  При идентификации контрагентов и медикаментов, вносим соответсвующие записи в таблицы
	 */
	function updateInvoiceData($record, &$last_id) {
		//сохраняем данные в накладной
		if (isset($record['Contragent_sid']) && isset($record['Contragent_tid']) && ($record['Contragent_sid'] > 0 || $record['Contragent_tid'] > 0) && isset($record['Invoice_id']) && $record['Invoice_id'] > 0 && $record['Invoice_id'] != $last_id) {
			$query = "update raw.Invoice set Contragent_sid = :Contragent_sid, Contragent_tid = :Contragent_tid where Invoice_id = :Invoice_id";
			$this->db->query($query, array(
				'Contragent_sid' => !empty($record['Contragent_sid'])?$record['Contragent_sid']:null,
				'Contragent_tid' => !empty($record['Contragent_tid'])?$record['Contragent_tid']:null,
				'Invoice_id' => $record['Invoice_id']
			));
		}

		//сохраняем данные в списке медикаментов накладной
		if (isset($record['Drug_id']) && isset($record['PrepSeries_id']) && ($record['Drug_id'] > 0 || $record['PrepSeries_id'] > 0) && isset($record['InvoiceDrug_id']) && $record['InvoiceDrug_id'] > 0) {
			$query = "update raw.InvoiceDrug set Drug_id = :Drug_id, PrepSeries_id = :PrepSeries_id where InvoiceDrug_id = :InvoiceDrug_id";
			$this->db->query($query, array(
				'Drug_id' => !empty($record['Drug_id'])?$record['Drug_id']:null,
				'PrepSeries_id' => !empty($record['PrepSeries_id'])?$record['PrepSeries_id']:null,
				'InvoiceDrug_id' => $record['InvoiceDrug_id']
			));
		}

		//сохраняем ссылку на документ учета в накладной + устаналиваем статус принят
		if (isset($record['DocumentUc_id']) && $record['DocumentUc_id'] > 0  && isset($record['Invoice_id']) && $record['Invoice_id'] > 0) {
			$query = "update raw.Invoice set DocumentUc_id = :DocumentUc_id, InvoiceStatus_id = 3, InvoiceCause_id = NULL where Invoice_id = :Invoice_id";
			$this->db->query($query, array(
				'DocumentUc_id' => $record['DocumentUc_id'],
				'Invoice_id' => $record['Invoice_id']
			));
		}

		//чтобы не апдейтить дважды одну накладную, запомининаем id прошлой накладной
		$last_id = $record['Invoice_id'];
	}

	/**
	 *  Получаем данные для сохранения спецификации документов учета
	 */
	function getInvoiceDocumentUcStrData($data)
	{
		$query = "
			select
				ID.InvoiceDrug_id as \"InvoiceDrug_id\",
				ID.Drug_id as \"Drug_id\",
				ID.InvoiceDrug_Price as \"InvoiceDrug_Price\",
				ID.InvoiceDrug_KoAll as \"InvoiceDrug_KoAll\",
				DN.DrugNds_id as \"DrugNds_id\",
				ID.InvoiceDrug_Sum as \"InvoiceDrug_Sum\",
				ID.PrepSeries_id as \"PrepSeries_id\",
				to_char(ID.InvoiceDrug_SrokS, 'dd.mm.yyyy') as \"InvoiceDrug_SrokS\",
				rtrim(ltrim(ID.InvoiceDrug_Series)) as \"InvoiceDrug_Series\",
				rtrim(ltrim(ID.InvoiceDrug_SertN)) as \"InvoiceDrug_SertN\"
			from
				raw.v_InvoiceDrug ID
				left join v_DrugNds DN on DN.DrugNds_Code = ID.InvoiceDrug_NDS
			where
				ID.Invoice_id = :Invoice_id;
		";

		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}

		return array();
	}

	/**
	 *  Производим перерасчет остатков в соответствии с данными накладных
	 */
	function recalculateInvoiceDrugOstat($data, $drug_array) {
		$invoice_drug = array();
		$use_invoice_series = false; //признак зачисления остатков по сериям указанным в накладной

		//проверяем наличие получателя по документу в списке пунктов отпуска
		$query = "
			select
				sum(case when Org_id = :Org_id then 1 else 0 end) as cnt,
				count(wdt.WhsDocumentTitle_id) as doc_cnt,
			from
				v_WhsDocumentTitle wdt
				left join v_WhsDocumentTitleType wdtt on wdtt.WhsDocumentTitleType_id = wdt.WhsDocumentTitleType_id
				left join v_WhsDocumentRightRecipient wdrr on wdrr.WhsDocumentTitle_id = wdt.WhsDocumentTitle_id
			where
				wdt.WhsDocumentUc_id = :WhsDocumentSupply_id and
				wdtt.WhsDocumentTitleType_Code = 3; --Приложение к ГК: список пунктов отпуска
		";
		$res = $this->getFirstRowFromQuery($query, array(
			'Org_id' => $data['FarmacyOrg_id'],
			'WhsDocumentSupply_id' => $data['WhsDocumentSupply_id']
		));
		if (is_array($res) && count($res) > 0) {
			if ($res['doc_cnt']) {
				$use_invoice_series = ($res['cnt'] > 0);
			} else {
				$use_invoice_series = ($data['FarmacyOrg_id'] == $data['SupplyOrg_rid']);
			}
		}

		$query = "
				with dor as (
					select
						dor.DrugShipment_id,
						dor.Drug_id,
						dor.SubAccountType_id,
						dor.Okei_id,
						dor.DrugOstatRegistry_Kolvo,
						dor.DrugOstatRegistry_Sum,
						dor.Org_id,
						dor.Contragent_id,
						dor.PrepSeries_id
					from
						DrugOstatRegistry dor
						left join DrugShipment ds on ds.DrugShipment_id = dor.DrugShipment_id
					where
						dor.SubAccountType_id = 1 and
						dor.Org_id = :SupplierOrg_id and
						ds.WhsDocumentSupply_id = :WhsDocumentSupply_id
				)

				select
					id.InvoiceDrug_id as \"InvoiceDrug_id\",
					dor.Drug_id as \"Drug_id\",
					dor.DrugShipment_id as \"DrugShipment_id\",
					dor.SubAccountType_id as \"SubAccountType_id\",
					dor.Okei_id as \"Okei_id\",
					dor.Contragent_id as \"Contragent_id\",
					dor.PrepSeries_id as \"RegistryPrepSeries_id\",
					id.PrepSeries_id as \"InvoicePrepSeries_id\",
					id.InvoiceDrug_KoAll as \"kolvo\",
					((id.InvoiceDrug_KoAll/dor.DrugOstatRegistry_Kolvo)*dor.DrugOstatRegistry_Sum) as \"sum\"
				from
					raw.v_InvoiceDrug id
					inner join dor on dor.Drug_id = id.Drug_id and dor.DrugOstatRegistry_Kolvo >= id.InvoiceDrug_KoAll
				where
					id.Invoice_id = :Invoice_id;
		";
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			$invoice_drug = $result->result('array');
		}

		//для каждого медикамента дважды вызываем хранимку для перерасчета остатков
		foreach($invoice_drug as $drug) {
			if (in_array($drug['InvoiceDrug_id'], $drug_array)) {
				//зачисляем ЛС на счет аптеки
				$query = "
					select
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Msg\"
					from xp_DrugOstatRegistry_count(
						Contragent_id := :Contragent_id,
						Org_id := :Org_id,
						DrugShipment_id := :DrugShipment_id,
						Drug_id := :Drug_id,
						PrepSeries_id := :PrepSeries_id,
						SubAccountType_id := :SubAccountType_id, -- субсчёт доступно
						Okei_id := :Okei_id,
						DrugOstatRegistry_Kolvo := :DrugOstatRegistry_Kolvo,
						DrugOstatRegistry_Sum := :DrugOstatRegistry_Sum,
						pmUser_id := :pmUser_id
					)
				";

				$queryParams = array(
					'Contragent_id' => $use_invoice_series || $drug['Contragent_id'] > 0 ? $data['Contragent_tid'] : null,
					'Org_id' => $data['FarmacyOrg_id'],
					'DrugShipment_id' => $drug['DrugShipment_id'],
					'Drug_id' => $drug['Drug_id'],
					'PrepSeries_id' => $use_invoice_series ? $drug['InvoicePrepSeries_id'] : ($drug['Contragent_id'] > 0 ? $drug['RegistryPrepSeries_id'] : null),
					'SubAccountType_id' => $drug['SubAccountType_id'],
					'Okei_id' => $drug['Okei_id'],
					'DrugOstatRegistry_Kolvo' => $drug['kolvo'],
					'DrugOstatRegistry_Sum' => $drug['sum'],
					'pmUser_id' => $data['pmUser_id']
				);
				$result = $this->db->query($query, $queryParams);

				if ( is_object($result) ) {
					$res = $result->result('array');
					if (!empty($res[0]['Error_Msg'])) {
						return array(0 => array('Error_Msg' => 'Ошибка редактирования регистра остатков'));
					} else {
						//списываем ЛС со счета поставщика
						//запрос тотже, меняем только часть параметров
						$queryParams['Contragent_id'] = $drug['Contragent_id'] > 0 ? $drug['Contragent_id'] : null;
						$queryParams['PrepSeries_id'] = $drug['Contragent_id'] > 0 ? $drug['RegistryPrepSeries_id'] : null;
						$queryParams['Org_id'] = $data['SupplierOrg_id'];
						$queryParams['DrugOstatRegistry_Kolvo'] = $drug['kolvo']*(-1);
						$queryParams['DrugOstatRegistry_Sum'] = $drug['sum']*(-1);
						$result = $this->db->query($query, $queryParams);
						if ( is_object($result) ) {
							$res = $result->result('array');
							if (!empty($res[0]['Error_Msg'])) {
								return array(0 => array('Error_Msg' => 'Ошибка редактирования регистра остатков'));
							}
						} else {
							return array(0 => array('Error_Msg' => 'Ошибка запроса редактирования регистра остатков'));
						}
					}
				} else {
					return array(0 => array('Error_Msg' => 'Ошибка запроса редактирования регистра остатков'));
				}
			}
		}

		return true;
	}

	/**
	 * Получаем данные для DBF.
	 */
	function getReceptErrorDataForDBF($data) {
		$query = "
			select
				rr.RegistryRecept_Recent as \"SN_LR\",
				rre.RegistryReceptError_id as \"ERR_ID\",
				rret.RegistryReceptErrorType_id as \"ERRTYPE_ID\",
				rret.RegistryReceptErrorType_Type as \"ERRTYPE_C\",
				rret.RegistryReceptErrorType_Name as \"ERRTYPE_N\",
				rret.RegistryReceptErrorType_Descr as \"ERRTYPE_D\"
			from
				{$this->schema}.RegistryRecept rr
				inner join {$this->schema}.RegistryReceptError rre on rre.RegistryRecept_id = rr.RegistryRecept_id
				inner join {$this->schema}.RegistryReceptErrorType rret on rret.RegistryReceptErrorType_id = rre.RegistryReceptErrorType_id
			where
				ReceptUploadLog_id = :ReceptUploadLog_id;
		";

		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}

		return false;
	}

	/**
	 * Вспомогательная функция для извлечения кодов из некоторых полей.
	 */
	function getCode($value, $type) {
		$res = $value;
		if (!empty($value) && strpos($value, ' ') > 0) {
			if ($type == 'mp' || $type == 'con')
				$res = substr($value, strpos($value, ' ')+1);
			if ($type == 'orgn')
				$res = substr($value, 0, strpos($value, ' '));
		}
		return $res;
	}

	/**
	 *  Функция для списания медикамента с остатков аптеки
	 * 	Используется в экспертизе реестров рецептов после создания документа учета
	 */
	function unsetFarmacyDrugOstat($data) {
		$query = "
			select
				dor.DrugShipment_id as \"DrugShipment_id\",
				dor.Drug_id as \"Drug_id\",
				dor.SubAccountType_id as \"SubAccountType_id\",
				dor.Okei_id as \"Okei_id\",
				dor.DrugOstatRegistry_Kolvo as \"DrugOstatRegistry_Kolvo\",
				dor.DrugOstatRegistry_Sum as \"DrugOstatRegistry_Sum\",
				:Drug_Kolvo as \"kolvo\",
				((:Drug_Kolvo/dor.DrugOstatRegistry_Kolvo)*dor.DrugOstatRegistry_Sum) as \"sum\",
				dor.Org_id as \"Org_id\"
			from
				DrugOstatRegistry dor
			where
				dor.Org_id = ( select Org_id from Contragent where Contragent_Code = :Contragent_Code and ContragentType_id = 3 )
				and dor.SubAccountType_id = 1
				and dor.DrugFinance_id = :DrugFinance_id
				and dor.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id
				and dor.Drug_id = :Drug_id
				and dor.DrugShipment_id in (
					select
						ds.DrugShipment_id
					from
						DrugShipment ds
						left join WhsDocumentSupply wds on wds.WhsDocumentSupply_id = ds.WhsDocumentSupply_id
						left join WhsDocumentUc wdu on wdu.WhsDocumentUc_id = wds.WhsDocumentUc_id
					where
						wdu.WhsDocumentUc_Num = :WhsDocumentUc_Num
				)
				and dor.DrugOstatRegistry_Kolvo >= :Drug_Kolvo
		";
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			$res = $result->result('array');
			if (!empty($res[0]['Error_Msg']) || !is_array($res) || count($res) < 1) {
				return array(0 => array('Error_Msg' => 'Ошибка при получении остатков ЛС'));
			}

			$drug = $res[0];

			$query = "
				select
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from xp_DrugOstatRegistry_count(
					Contragent_id := NULL,
					Org_id := :Org_id,
					DrugShipment_id := :DrugShipment_id,
					Drug_id := :Drug_id,
					PrepSeries_id := NULL,
					SubAccountType_id := :SubAccountType_id, -- субсчёт доступно
					Okei_id := :Okei_id,
					DrugOstatRegistry_Kolvo := :DrugOstatRegistry_Kolvo,
					DrugOstatRegistry_Sum := :DrugOstatRegistry_Sum,
					pmUser_id := :pmUser_id
				)
			";

			$queryParams = array(
				'Org_id' => $drug['Org_id'],
				'DrugShipment_id' => $drug['DrugShipment_id'],
				'Drug_id' => $drug['Drug_id'],
				'SubAccountType_id' => $drug['SubAccountType_id'],
				'Okei_id' => $drug['Okei_id'],
				'DrugOstatRegistry_Kolvo' => $drug['kolvo']*(-1),
				'DrugOstatRegistry_Sum' => $drug['sum']*(-1),
				'pmUser_id' => $data['pmUser_id']
			);

			$result = $this->db->query($query, $queryParams);
			if ( is_object($result) ) {
				$res = $result->result('array');
				if (!empty($res[0]['Error_Msg'])) {
					return array(0 => array('Error_Msg' => 'Ошибка редактирования регистра остатков'));
				}
			}
		}

		return true;
	}

	/**
	 * Получить список доступных проверок для экспертизы
	 */
	function getExpertiseAllowedMethods() {
		$methods = array();

		$query = "
			select
				rret.RegistryReceptExpertiseType_SysNick as \"RegistryReceptExpertiseType_SysNick\"
			from
				v_RegistryReceptExpertiseType rret
				left join v_YesNo yn on yn.YesNo_id = rret.RegistryReceptExpertiseType_IsActive
			where
				rret.RegistryReceptExpertiseType_IsActive is null or
				yn.YesNo_Code = 1; -- Да
		";

		$result = $this->db->query($query, array());

		if ( is_object($result) ) {
			$result = $result->result('array');
			foreach($result as $record) {
				$methods[] = $record['RegistryReceptExpertiseType_SysNick'];
			}
		}

		return $methods;
	}

	/**
	 * Получение кода для произвольной таблицы с числовыми кодами
	 */
	function getNextCode($table_name, $field_name) {
		$code = $this->getFirstResultFromQuery("
			select
				max($field_name) as code
			from
				$table_name;
		");
		return $code > 0 ? $code+1 : 1;
	}

	/**
	 * Функция сохранения произвольных данных
	 */
	function saveObjectData($object, $data) {
		$data = array_change_key_case($data, CASE_LOWER );
		$schema = isset($data['schema']) ? $data['schema'] : 'dbo';
		$id = isset($data[$object.'_id']) && $data[$object.'_id'] > 0 ? $data[$object.'_id'] : null;
		$action = $id > 0 ? 'upd' : 'ins';
		$params = array();

		if ($id <= 0) {
			$data[$object.'_id'] = null;
		} else {
			//получаем текущие данные обьекта, чтобы при апдейте изменились только те поля, которые были присланы в массиве $data
			$query = "
				select
					*
				from
					{$schema}.{$object}
				where
					{$object}_id = :id;
			";
			$saved_data = $this->getFirstRowFromQuery($query, array('id' => $id));
			if (is_array($saved_data)) {
				$haystack = [
					'pmUser_insID', 'pmUser_updID', $object.'_insDT', $object.'_insDate', $object.'_updDT', $object.'_updDate'
				];
				foreach ($haystack as $key => $value) {
					$haystack[$key] = strtolower($value);
				}
				foreach($saved_data as $key => $value) {
					if (!in_array($key, array_keys($data)) && !in_array($key, $haystack)) {
						$data[$key] = $value;
					}
				}
			}
		}

			$query_part = "";
		foreach($data as $key=>$value) {
			$params[$key] = $value;
			if (!in_array($key, array($object.'_id', 'pmUser_id', 'schema'))) {
				$query_part .= " {$key} := :{$key},";
			}
		};

		$query = "
			select
				{$object}_id as \"{$object}_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$schema}.p_{$object}_{$action}(
				{$object}_id := :{$object}_id,
				{$query_part}
				pmUser_id := :pmUser_id
			)
		";
		$res = $this->db->query($query, $params);
		if ( !is_object($res) ) {
			return false;
		}
		return $res->result('array');
	}
}
