<?php

/**
 * 
 */
class SkladOstat_model extends swModel {

	/**
	 *  constructor
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 *
	 * @param type $data
	 * @return type 
	 */
	function run($data) {
		$DBF = $this->DBFtoArray($data);
		switch ($data['typeAction']) {
			case 'SkladOst':
				$this->db->query("delete from raw.SkladOstat;"); //Перед каждой загрузкой очищаем таблицу.
				foreach ($DBF as $val) {
					$this->importSkladOst($val);
				}
				break;
			case 'LpuSectionOTD':
				//print_r($DBF);exit();
				foreach ($DBF as $val) {
					$this->importMed($val);
				}
				break;
		}

		return array('success' => true);
	}

	/**
	 *
	 * @param type $val
	 * @return type 
	 */
	function importMed($val) {
		$query = "
			

		declare @Contragent_id bigint
		declare @Contragent_tid bigint
		declare @DrugFinance_id bigint
		declare @WhsDocumentCostItemType_id bigint
		declare @DocUc_id bigint
		declare @DocUcStr_id bigint
		declare @SKLAD_NAME varchar(30)
		declare @DocumentUcStr_godnDate datetime
		declare @DocumentUcStr_Ser varchar(20)
		declare @DocumentUc_Num varchar(30)
		declare @Lpu_id bigint
		declare @EvnDrug_setDT date 
		declare @Mol_tid bigint
		declare @Error_Code bigint
		declare @Error_Message varchar(4000)
		declare @Drug_id bigint
		declare @pmUser_id bigint
		declare @GDMD_RN varchar(10)
		declare @KOL bigint

		set @DocUc_id = null
		set @DocUcStr_id  = null
		set @DrugFinance_id = null
		set @DocumentUcStr_godnDate = null
		set @DocumentUc_Num  = null
		set @DocumentUcStr_Ser = ''
		set @DocumentUc_Num = ''
		set @EvnDrug_setDT = dbo.tzGetDate()
		set @pmUser_id = :pmUser_id
		set @Lpu_id = :Lpu_id
		set @SKLAD_NAME = :SKLAD+'-'+cast(:SKLAD_RN as varchar)
		set @GDMD_RN = :GDMD_RN
		set @KOL = :KOL
		set @EvnDrug_setDT = cast(:DOC_DATE as date)
		
		set @Contragent_id  = (select top 1 Contragent_id from v_contragent with(nolock) where contragent_name = @SKLAD_NAME) 
		if (@Contragent_id is null)
		exec p_Contragent_ins
		 @Server_id  = @Lpu_id,
		 @Contragent_id  = @Contragent_id output,
		 @Lpu_id = @Lpu_id,
		 @ContragentType_id  = 1,
		 @Contragent_Code =1001,
		 @Contragent_Name =@SKLAD_NAME,
		 @Org_id  = null,
		 @OrgFarmacy_id  = null,
		 @LpuSection_id  = null,
		 @pmUser_id  = @pmUser_id,
		 @Error_Code = @Error_Code output,
		 @Error_Message = @Error_Message output

select @Contragent_id, @Error_Code , @Error_Message



		set @Contragent_tid = (
		Select top 1 Contragent_id from Contragent c with (nolock)
		left join LpuSectionOTD lp with(nolock) on lp.LpuSection_id=c.LpuSection_id 
		where lp.LpuSectionOTD_OTDRN=:OTD_RN
		);
		set @Mol_tid = (
		select top 1 mol_id from v_mol with(nolock) where Contragent_id = @Contragent_tid
		)
		set @DocUc_id = null
		set @DocUcStr_id  = null
		set @DrugFinance_id = null
		set @DocumentUcStr_godnDate = null
		set @DocumentUc_Num  = null
		set @DocumentUcStr_Ser = ''
		set @DocumentUc_Num = ''

	
	-- Генерация номера документа 
	set @DocumentUc_Num = 'Склад/'+:SKLAD
	-- Если в самом EvnDrug ссылка на DocumentUc расхода еще отсутствует, то проверяем
	-- Проверка, может уже на сегодня создан по данному контаргенту документ учета - тогда будем дописывать в него
	set @DrugFinance_id = 1
	set @WhsDocumentCostItemType_id = 14
	Set @DocUc_id = (Select top 1 DocumentUc_id from DocumentUc with(nolock) where DocumentUc_setDate = @EvnDrug_setDT and Contragent_sid = @Contragent_id and Lpu_id = @Lpu_id and DrugFinance_id = @DrugFinance_id and WhsDocumentCostItemType_id = @WhsDocumentCostItemType_id)
	set @Drug_id = (select top 1 d.drug_id from rls.v_Drug d with(nolock) inner join drugLink dl with(nolock) on dl.drug_id=d.Drug_id where dl.DrugLink_RN = @GDMD_RN)
	

	if (@DocUc_id is null)
		-- Создаем документ учета первый раз за сегодняшний день по этому контрагенту (отделению)
		exec p_DocumentUc_ins 
		 @DocumentUc_id = @DocUc_id output, 
		 @DocumentUc_Num = @DocumentUc_Num,
		 @DocumentUc_setDate = @EvnDrug_setDT,
		 @DocumentUc_didDate = @EvnDrug_setDT,
		 @DocumentUc_DogNum = null,
		 @DocumentUc_DogDate = null,
		 @DocumentUc_InvNum = null,
		 @DocumentUc_InvDate = null,
		 @DocumentUc_Sum = null,
		 @DocumentUc_SumR = null,
		 @DocumentUc_SumNds = null,
		 @DocumentUc_SumNdsR = null,
		 @Lpu_id = @Lpu_id,
		 @Contragent_id = @Contragent_id,
		 @Contragent_sid = @Contragent_id,
		 @Mol_sid = null,
		 @Contragent_tid = @Contragent_tid,
		 @Mol_tid = @Mol_tid,
		 @DrugFinance_id = @DrugFinance_id,
		 @WhsDocumentCostItemType_id = @WhsDocumentCostItemType_id,
		 @DrugDocumentType_id = 1,
		 @DrugDocumentStatus_id = null,
		 @pmUser_id = @pmUser_id,
		 @Error_Code = @Error_Code output,
		 @Error_Message = @Error_Message output

	select @DocUc_id, @Error_Code , @Error_Message

	--select * from v_documentUc  with(nolock)


		exec p_DocumentUcStr_ins 
		 @DocumentUcStr_id = @DocUcStr_id output,
		 --@DocumentUcStr_oid = @DocumentUcStr_oid,
		 @DocumentUc_id = @DocUc_id,
		-- @EvnDrug_id = @EvnDrug_id,
		 @Drug_id = @Drug_id,
		 @DrugFinance_id = @DrugFinance_id,
		 @DrugNds_id = null,
		 @DrugProducer_id = null,
		 @DocumentUcStr_Price = null,
		 @DocumentUcStr_PriceR = 0,-- @EvnDrug_Price,
		 --@DocumentUcStr_EdCount =  --@EvnDrug_KolvoEd,
		 @DocumentUcStr_Count = @KOL,--@EvnDrug_Kolvo,
		 @DocumentUcStr_Sum = null,
		 --@DocumentUcStr_SumR = @EvnDrug_Sum,
		 @DocumentUcStr_SumNds = null,
		 @DocumentUcStr_SumNdsR = null,
		 @DocumentUcStr_Ser = null,
		 @DocumentUcStr_CertNum = null,
		 @DocumentUcStr_CertDate = null,
		 @DocumentUcStr_CertGodnDate = null,
		 @DocumentUcStr_CertOrg = null,
		 @DocumentUcStr_IsLab = null,
		 @DrugLabResult_Name = null,
		 @DocumentUcStr_RashCount = null,
		 @DocumentUcStr_RegDate = null,
		 @DocumentUcStr_RegPrice = null,
		 @DocumentUcStr_godnDate = @DocumentUcStr_godnDate,
		 @DocumentUcStr_setDate = @EvnDrug_setDT,
		 @DocumentUcStr_Decl = null,
		 @DocumentUcStr_Barcod = null,
		 @DocumentUcStr_CertNM = null,
		 @DocumentUcStr_CertDM = null,
		 @DocumentUcStr_NTU = null,
		 @DocumentUcStr_NZU = null,
		 @DocumentUcStr_Reason = null,
		 @EvnRecept_id = null,
		 @pmUser_id = @pmUser_id,
		 @Error_Code = @Error_Code output,
		 @Error_Message = @Error_Message output
			";
		$queryParams = array(
			'DOC_RN' => $val['DOC_RN'],
			'Lpu_id' => $val['Lpu_id'],
			'pmUser_id' => $val['pmUser_id'],
			'SKLAD' => $val['SKLAD'],
			'GDMD_RN' => $val['GDMD_RN'],
			'KOL' => $val['KOL'],
			'SKLAD_RN' => $val['SKLAD_RN'],
			'OTD_RN' => $val['OTD_RN'],
			'DOC_DATE' => $val['DOC_DATE']
		);
		//echo getDebugSQL($query, $queryParams); eixt();
		$result = $this->db->query($query, $queryParams);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *
	 * @param type $data
	 * @return type 
	 */
	function DBFtoArray($data) {
		$fields_mapping = array();
		if (!is_file($data['file'])) {
			throw new Exception($data['file'] . " не найден");
		}
		$dbf = dbase_open($data['file'], 0);
		if ($dbf === FALSE) {
			throw new Exception("Не удалось открыть файл " . $data['file']);
		}
		$dbf_header = dbase_get_header_info($dbf);
		if ($dbf_header === FALSE) {
			throw new Exception("Информация в заголовке базы данных " . $data['file'] . " не может быть прочитана");
		}
		//$this->db->query("delete from raw.SkladOstat;");//Перед каждой загрузкой очищаем таблицу.
		$ddl = array();
		$conv = array();
		if (0 == count($fields_mapping)) {
			$fields_mapping_empty = true;
		} else {
			$fields_mapping_empty = false;
		}
		foreach ($dbf_header as $dbf_field) {
			switch ($dbf_field['type']) {
				case 'character':
					$dbf_field['type'] = 'VARCHAR(' . $dbf_field['length'] . ')';
					$conv[] = $dbf_field['name'];
					break;
				default:
					$dbf_field['type'] = 'VARCHAR(4000)';
					break;
			}
			$ddl[] = '[' . $dbf_field['name'] . '] ' . $dbf_field['type'];
			if ($fields_mapping_empty) {
				$fields_mapping[$dbf_field['name']] = $dbf_field['name'];
			}
		}
		$cnt = dbase_numrecords($dbf);
		$values = array();
		for ($i = 1; $i <= $cnt; $i++) {
			$row = dbase_get_record_with_names($dbf, $i);
			/* foreach ($conv as $f_idx) {
			  //ConvertFromWin866ToCp1251($row[$f_idx]);
			  } */
			foreach ($fields_mapping as $source_field => $destination_field) {
				$values[$i][$source_field] = $row[$source_field];
			}
			$values[$i]['pmUser_id'] = $data['pmUser_id'];
			$values[$i]['Lpu_id'] = $data['Lpu_id'];
		}
		dbase_close($dbf);
		return $values;
	}

	/**
	 *
	 * @param type $value 
	 */
	function importLpuSecOTD($value) {
		$q = "	DECLARE
	@id BIGINT = NULL,
	@Error_Code BIGINT,
	@Error_Message VARCHAR(4000);
EXEC p_LpuSectionOTD_ins
	@SkladOstat_id		 = @id OUTPUT,
	@SkladOstat_Sklad	 = :SKLAD,
	@SkladOstat_SkladRn	 = :SKLAD_RN,
	@SkladOstat_Gdmd	 = :GDMD,
	@SkladOstat_GdmdRn	 = :GDMD_RN,
	@SkladOstat_Rls		 = :RLS,
	@SkladOstat_Mea		 = :MEA,
	@SkladOstat_Kol		 = :KOL,
	@pmUser_id			 = :pmUser_id,
	@Error_Code   		 = @Error_Code OUTPUT,
	@Error_Message   	 = @Error_Message OUTPUT
SELECT @id AS id, @Error_Code AS Error_Code, @Error_Message AS Error_Message;";
		$p = array(
			'SKLAD' => $value['SKLAD'],
			'SKLAD_RN' => $value['SKLAD_RN'],
			'GDMD' => $value['GDMD'],
			'GDMD_RN' => $value['GDMD_RN'],
			'RLS' => $value['RLS'],
			'MEA' => $value['MEA'],
			'KOL' => $value['KOL'],
			'pmUser_id' => $value['pmUser_id'],
		);
		$res = $this->getFirstRowFromQuery($q, $p);
		if (empty($res['id']) || !empty($res['Error_Message'])) {
			throw new Exception('Не удалось сохранить запись в таблицу.' . getDebugSQL($q, $p));
		}
	}

	/**
	 *
	 * @param type $value 
	 */
	function importSkladOst($value) {
		$q = "	DECLARE
	@id BIGINT = NULL,
	@Error_Code BIGINT,
	@Error_Message VARCHAR(4000);
EXEC raw.p_SkladOstat_ins
	@SkladOstat_id		 = @id OUTPUT,
	@SkladOstat_Sklad	 = :SKLAD,
	@SkladOstat_SkladRn	 = :SKLAD_RN,
	@SkladOstat_Gdmd	 = :GDMD,
	@SkladOstat_GdmdRn	 = :GDMD_RN,
	@SkladOstat_Rls		 = :RLS,
	@SkladOstat_Mea		 = :MEA,
	@SkladOstat_Kol		 = :KOL,
	@pmUser_id			 = :pmUser_id,
	@Error_Code   		 = @Error_Code OUTPUT,
	@Error_Message   	 = @Error_Message OUTPUT
SELECT @id AS id, @Error_Code AS Error_Code, @Error_Message AS Error_Message;";
		$p = array(
			'SKLAD' => $value['STORE'],
			'SKLAD_RN' => $value['SKLAD_RN'],
			'GDMD' => $value['GDMD'],
			'GDMD_RN' => $value['GDMD_RN'],
			'RLS' => $value['RLS'],
			'MEA' => $value['MEA'],
			'KOL' => $value['KOL'],
			'pmUser_id' => $value['pmUser_id'],
		);
		$res = $this->getFirstRowFromQuery($q, $p);
		if (empty($res['id']) || !empty($res['Error_Message'])) {
			throw new Exception('Не удалось сохранить запись в таблицу.' . getDebugSQL($q, $p));
		}
	}

	/**
	 *
	 * @param type $data
	 * @return type 
	 */
	function loadOstatGrid($data) {
		
		$queryFilter = "Where (1=1)
			";
		$queryParams = array();
		if (!empty($data['SkladOstat_Sklad'])) {
			$queryFilter.="and SkladOstat_Sklad like :SkladOstat_Sklad
				";
			$queryParams['SkladOstat_Sklad'] = $data['SkladOstat_Sklad'] . '%';
		}
		if (!empty($data['SkladOstat_Gdmd'])) {
			$queryFilter.="and SkladOstat_Gdmd like :SkladOstat_Gdmd
				";
			$queryParams['SkladOstat_Gdmd'] = $data['SkladOstat_Gdmd'] . '%';
		}
		$query = "
SELECT
	SkladOstat_Sklad,
	SkladOstat_SkladRn,
	SkladOstat_Gdmd,
	SkladOstat_GdmdRn,
	rls.DrugLink_Name as SkladOstat_Rls,
	SkladOstat_Mea,
	SkladOstat_Kol
FROM raw.SkladOstat
		outer apply(
		select top 1 DrugLink_Name 
		from  drugLink  with(nolock)
		where DrugLink_RN = SkladOstat_GdmdRn
		) as rls
{$queryFilter}
";
		//echo getDebugSQL($query, $queryParams);
		$result = $this->db->query($query, $queryParams);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

}

?>
