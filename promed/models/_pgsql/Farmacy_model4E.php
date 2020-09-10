<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb
 *
 * Класс модели для общих операций используемых во всех модулях
 *
 * The New Generation of Medical Statistic Software
 *
 * @package				Common
 * @copyright			Copyright (c) 2020 Swan Ltd.
 * @author				Valery Bondarev
 * @link				http://swan.perm.ru/PromedWeb
 * @version				?
 */

class Farmacy_model4E extends swModel {

	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct();
	}


	/**
	 *  Функция получаения упаковок
	 * Допилена 04.10.2013 в рамках задачи https://redmine.swan.perm.ru/issues/25631 - добавлена проверка на тип поставщика: если это организация, то DocumentUcStr_Ost не подключаем
	 */
	function loadDrugList($data) {
		$queryParams = array();
		$filter = "(1=1) ";
		$filter_all = "(1=1) ";
		$join = "";
		$and = "and (1=1)";
		$ContragentType_is_1 = false; //Тип поставщика - НЕ организация

		$query_contragenttype = "
			select 
					Con.server_id as \"Server_id\", 
					Con.contragent_id as \"Contragent_id\", 
					Con.lpu_id as \"Lpu_id\", 
					Con.contragenttype_id as \"ContragentType_id\", 
					Con.contragent_code as \"Contragent_code\", 
					Con.contragent_name as \"Contragent_name\", 
					Con.org_id as \"Org_id\", 
					Con.orgfarmacy_id as \"OrgFarmacy_id\", 
					Con.lpusection_id as \"LpuSection_id\", 
					Con.medservice_id as \"MedService_id\", 
					Con.org_pid as \"Org_pid\", 
					Con.org_uname as \"Org_uname\", 
					Con.org_unick as \"Org_unick\", 
					Con.pmuser_insid as \"pmUser_InsId\", 
					Con.pmuser_updid as \"pmUser_UpdId\", 
					Con.contragent_insdt as \"Contragent_InsDT\", 
					Con.contragent_upddt as \"Contragent_UpdDT\"
			from v_Contragent Con
			left join v_ContragentType ConT on ConT.ContragentType_id = Con.ContragentType_id
			where Con.Contragent_id = :Contragent_id
			and ConT.ContragentType_SysNick = 'org'
		";
		$result_contragenttype = $this->db->query($query_contragenttype,array('Contragent_id' => $data['Contragent_id']));
		if(is_object($result_contragenttype)){
			$response_contragenttype = $result_contragenttype->result('array');
			if (count($response_contragenttype) > 0){
				$ContragentType_is_1 = true;
			}
		}
		// Фильтрация по первому кобмбобоксу
		if ($data['DrugPrepFas_id']>0)
		{
			$filter_all .= "and Drug.DrugPrepFas_id = :DrugPrepFas_id ";
			$queryParams['DrugPrepFas_id'] = $data['DrugPrepFas_id'];
		}


		switch ( $data['mode'] ) {
			case 'expenditure':
				if(!$ContragentType_is_1){ //Если это НЕ организация, то цепляем DocumentUcStr_Ost
					$uc_ost = "v_DocumentUcOst_Lite";

					if (isset($data['DocumentUcStr_id']) && $data['DocumentUcStr_id'] != '') {
						$uc_ost = "dbo.DocumentUcOst_Lite(:DocumentUcStr_id)";
						$queryParams['DocumentUcStr_id'] = $data['DocumentUcStr_id'];
					}

					$join .= "inner join ".$uc_ost." DUO on Drug.Drug_id = DUO.Drug_id ";
					$and = "and DUO.DocumentUcStr_Ost > 0";
					if ($data['Contragent_id']>0)
					{
						$filter .= "and DUO.Contragent_tid = :Contragent_id ";
						$queryParams['Contragent_id'] = $data['Contragent_id'];
					}

					if ($data['LpuSection_id']>0)
					{
						$filter .= "and C.LpuSection_id = :LpuSection_id ";
						$join .= "inner join Contragent C on C.Contragent_id = DUO.Contragent_tid ";
						$queryParams['LpuSection_id'] = $data['LpuSection_id'];
						// По дате
						$filter .= " and (DocumentUc_didDate <= :date or :date Is Null) ";
						$queryParams['date'] = $data['date'];
					}

					if (isset($data['WhsDocumentCostItemType_id']) && $data['WhsDocumentCostItemType_id'] > 0) {
						$filter .= " and (DUO.WhsDocumentCostItemType_id is null or DUO.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id)";
						$queryParams['WhsDocumentCostItemType_id'] = $data['WhsDocumentCostItemType_id'];
					}

					if (isset($data['DrugFinance_id']) && $data['DrugFinance_id'] > 0) {
						$filter .= " and (DUO.DrugFinance_id is null or DUO.DrugFinance_id = :DrugFinance_id)";
						$queryParams['DrugFinance_id'] = $data['DrugFinance_id'];
					}

					/*if ($data['checking_exp_date'] == 'true')
					{
						$filter .= " and dbo.CheckExpDate(@CreateDate, @ExpDate, @RateLess2Year, @RateMore2Year) > 0 ";
					}*/
				}
				$query = "
					select distinct
						RTRIM(COALESCE(Drug.Drug_Nomen, '')) as \"Drug_Name\",
						RTRIM(COALESCE(Drug.Drug_Name, '')) as \"Drug_FullName\",
						Drug.Drug_id as \"Drug_id\",
						Drug.Drug_Code as \"Drug_Code\",
						Drug.Drug_Fas as \"Drug_Fas\",
						RTRIM(COALESCE(Drug.DrugForm_Name, '')) as \"DrugForm_Name\",
						RTRIM(COALESCE(Drug.Drug_PackName, '')) as \"DrugUnit_Name\"
					from rls.v_Drug Drug
						{$join}
						--left join DrugForm on DrugForm.DrugForm_id = Drug.DrugForm_id
						--left join DrugUnit on DrugUnit.DrugUnit_id = Drug.DrugUnit_id
					where {$filter} and {$filter_all} {$and}
					limit 1000
				";
				//--and (DUO.DocumentUcStr_Ost > 0 or :Drug_id is not null)
				//$queryParams['Drug_id'] = $data['Drug_id'];
				break;

			case 'income':
				$query = "
					select distinct  -- здесь был top 50, но я его убрал, согласно задаче #. Спустя год: добавил top 1000, ибо падало от нехватки памяти.
						RTRIM(COALESCE(Drug.Drug_Nomen, '')) as \"Drug_Name\",
						RTRIM(COALESCE(Drug.Drug_Name, '')) as \"Drug_FullName\",
						Drug.Drug_id as \"Drug_id\",
						Drug.Drug_Code as \"Drug_Code\",
						Drug.Drug_Fas as \"Drug_Fas\",
						RTRIM(COALESCE(Drug.DrugForm_Name, '')) as \"DrugForm_Name\",
						RTRIM(COALESCE(Drug.Drug_PackName, '')) as \"DrugUnit_Name\"
					from rls.v_Drug Drug
						--left join DrugForm on DrugForm.DrugForm_id = Drug.DrugForm_id
						--left join DrugUnit on DrugUnit.DrugUnit_id = Drug.DrugUnit_id
					where {$filter_all}
					limit 1000
				";
				break;
		}

		if ( isset($data['Drug_id']) ) {
			$query .= " and Drug.Drug_id = :Drug_id";
			$queryParams['Drug_id'] = $data['Drug_id'];
		}

		if ( isset($data['query']) ) {
			$query .= " and LOWER(Drug.Drug_Name) like LOWER(:Drug_Name)";
			$queryParams['Drug_Name'] = "".$data['query'] . "%";
		}
		elseif ( isset($data['Drug_Name']) ) {
			$query .= " and LOWER(Drug.Drug_Name) like LOWER(:Drug_Name)";
			$queryParams['Drug_Name'] = "%".$data['Drug_Name'] . "%";
		}

		if ( isset($data['Drug_Code']) ) {
			$query .= " and LOWER(Drug.Drug_Code) like LOWER(:Drug_Code)";
			$queryParams['Drug_Code'] = "%".$data['Drug_Code'] . "%";
		}

		//$query .= " order by Drug.Drug_Name";
		/*
		echo getDebugSql($query, $queryParams);
		exit;
		*/
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *  Функция возвращает список медикаментов для первого комбобокса (для аптеки)
	 */
	function loadDrugPrepList($data) {
		$queryParams = array();
		$filter = "(1=1) ";
		$join = "";

		if (($data['load']=='torg') && (($data['Drug_id']>0) || ($data['DrugPrepFas_id']>0)))
		{
			// Если выбор при нажатии клавиши распахнуть или F2 (стрелка вниз)
			// Читаем по торговому
			if ($data['DrugPrepFas_id']>0)
			{
				$join .="
				left join lateral
				(
					Select DrugTorg_Name from rls.v_DrugPrep D
					where D.DrugPrepFas_id = :DrugPrepFas_id
				) DrugTorg on true
				";
				$queryParams['DrugPrepFas_id'] = $data['DrugPrepFas_id'];
				$filter .= " and LOWER(DrugPrep.DrugTorg_Name) like ('%'||LOWER(DrugTorg.DrugTorg_Name))";
			}
			elseif ($data['Drug_id']>0)
			{
				$join .="
				left join lateral
				(
					Select DrugTorg_Name from rls.v_Drug D
					where D.Drug_id = :Drug_id
				) DrugTorg on true
				";
				$queryParams['Drug_id'] = $data['Drug_id'];
				$filter .= " and LOWER(DrugPrep.DrugTorg_Name) like ('%'||LOWER(DrugTorg.DrugTorg_Name))";
			}
		}
		else
		{
			// Если передается конкретное значение, то включаем фильтрацию сразу по этому значению
			if ($data['DrugPrepFas_id']>0)
			{
				$filter .= "and DrugPrep.DrugPrepFas_id = :DrugPrepFas_id ";
				$queryParams['DrugPrepFas_id'] = $data['DrugPrepFas_id'];
			}
			elseif ($data['Drug_id']>0)
			{
				$filter .= " and Drug.Drug_id = :Drug_id ";
				$queryParams['Drug_id'] = $data['Drug_id'];
			}
			else
			{
				// Если выполняется поиск
				if ((isset($data['query'])) && (strlen($data['query'])>=3))
				{
					$filter .= " and LOWER(DrugPrep.DrugPrep_Name) like LOWER(:query)";
					$queryParams['query'] = "".$data['query'] . "%";
				}
				elseif ( isset($data['DrugPrep_Name']) )
				{
					$filter .= " and LOWER(DrugPrep.DrugPrep_Name) like LOWER(:DrugPrep_Name)";
					$queryParams['DrugPrep_Name'] = "%".$data['DrugPrep_Name'] . "%";
				}
				elseif (strlen($data['query'])<1)
				{
					//return false;
				}
			}
		}
		// другие фильтры

		switch ( $data['mode'] )
		{
			// если режим внутри ЛПУ, то учитываем контагентов
			case 'expenditure':
				$uc_ost = "v_DocumentUcOst_Lite";

				if (isset($data['DocumentUcStr_id']) && $data['DocumentUcStr_id'] != '') {
					$uc_ost = "dbo.DocumentUcOst_Lite(:DocumentUcStr_id)";
					$queryParams['DocumentUcStr_id'] = $data['DocumentUcStr_id'];
				}

				$join .= "inner join rls.v_Drug Drug on Drug.DrugPrepFas_id = DrugPrep.DrugPrepFas_id ";
				$join .= "inner join ".$uc_ost." DUO on Drug.Drug_id = DUO.Drug_id ";
				if ($data['Contragent_id']>0)
				{
					$filter .= " and DUO.Contragent_tid = :Contragent_id ";
					$queryParams['Contragent_id'] = $data['Contragent_id'];
				}
				if ($data['LpuSection_id']>0)
				{
					$filter .= " and C.LpuSection_id = :LpuSection_id ";
					$filter .= " and DUO.DocumentUcStr_Ost > :Drug_Kolvo";

					$queryParams['Drug_Kolvo'] = (!empty($data['Drug_Kolvo']) ? $data['Drug_Kolvo'] : 0);

					// По дате
					//$filter .= " and (DocumentUc_didDate <= :date or :date Is Null) ";
					//$queryParams['date'] = $data['date'];

					$join .= "inner join Contragent C on C.Contragent_id = DUO.Contragent_tid ";
					/*
					$join .= "outer apply (
						select isnull(sum(DocumentUcStr_Ost),0) as cnt
						from ".$uc_ost." DUOL left join Contragent TC with (nolock) on TC.Contragent_id = DUOL.Contragent_tid
						where Drug_id = Drug.Drug_id and TC.LpuSection_id = :LpuSection_id
					) as ost";
					*/
					$queryParams['LpuSection_id'] = $data['LpuSection_id'];
				}
				else
				{
					$filter .= " and DUO.DocumentUcStr_Ost > 0";
				}

				if (isset($data['WhsDocumentCostItemType_id']) && $data['WhsDocumentCostItemType_id'] > 0) {
					$filter .= " and (DUO.WhsDocumentCostItemType_id is null or DUO.WhsDocumentCostItemType_id = :WhsDocumentCostItemType_id)";
					$queryParams['WhsDocumentCostItemType_id'] = $data['WhsDocumentCostItemType_id'];
				}

				if (isset($data['DrugFinance_id']) && $data['DrugFinance_id'] > 0) {
					$filter .= " and (DUO.DrugFinance_id is null or DUO.DrugFinance_id = :DrugFinance_id)";
					$queryParams['DrugFinance_id'] = $data['DrugFinance_id'];
				}

				break;

			case 'income':
				if ($data['Drug_id']>0)
				{
					$join .= "inner join rls.v_Drug Drug on Drug.DrugPrepFas_id = DrugPrep.DrugPrepFas_id ";
				}

				break;
		}



		$query = "
			select distinct -- я какбэ сомневаюсь, но чтожжж...
				RTRIM(COALESCE(DrugPrep.DrugPrep_Name, '')) as \"DrugPrep_Name\",
				DrugPrep.DrugPrep_id as \"DrugPrep_id\",
				DrugPrep.DrugPrepFas_id as \"DrugPrepFas_id\"
				
			from rls.v_DrugPrep DrugPrep
			{$join}
			where {$filter}
			limit 500
		";

		//$query .= " order by DrugPrep.DrugPrep_Name";
		/*print_r($data);*/
		/*
		echo getDebugSql($query, $queryParams);
		exit;
		*/

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *  Функция возвращает список медикаментов с постраничным выводом
	 *  Используется на форме поиска медикаментов
	 */
	function loadDrugMultiList($data) {
		$queryParams = array();
		$filter = "(1=1) ";
		$filter_all = "(1=1) ";
		$join = "left join rls.v_DrugComplexMnn dMnn on dMnn.DrugComplexMnn_id=Drug.DrugComplexMnn_id 
			";


		// Фильтрация по первому кобмбобоксу
		if ($data['DrugPrepFas_id']>0)
		{
			$filter_all .= "and Drug.DrugPrepFas_id = :DrugPrepFas_id ";
			$queryParams['DrugPrepFas_id'] = $data['DrugPrepFas_id'];
		}


		switch ( $data['mode'] ) {
			case 'expenditure':
				$join .= "inner join v_DocumentUcOst_Lite DUO on Drug.Drug_id = DUO.Drug_id 
					";
				if ($data['Contragent_id']>0)
				{
					$filter .= "and DUO.Contragent_tid = :Contragent_id ";
					$queryParams['Contragent_id'] = $data['Contragent_id'];
				}

				if ($data['LpuSection_id']>0)
				{
					$filter .= "and C.LpuSection_id = :LpuSection_id ";
					$filter .= " and ost.cnt > 0 ";
					$join .= "inner join Contragent C on C.Contragent_id = DUO.Contragent_tid ";
					$join .= " left join lateral (
						select COALESCE(sum(DocumentUcStr_Ost),0) as cnt 
						from v_DocumentUcOst_Lite DUOL
						left join Contragent TC on TC.Contragent_id = DUOL.Contragent_tid
						where Drug_id = Drug.Drug_id and TC.LpuSection_id = :LpuSection_id
					) as ost on true";
					$queryParams['LpuSection_id'] = $data['LpuSection_id'];
				}
				$query = "
					select 
						-- select
						RTRIM(COALESCE(Drug.DrugTorg_Name, '')) as \"DrugTorg_Name\",
						Drug.Drug_id as \"Drug_id\",
						Drug.DrugPrepFas_id as \"DrugPrepFas_id\",
						Drug.Drug_Nomen as \"Drug_Nomen\",
						RTRIM(COALESCE(Drug.Drug_Nomen, '')) as \"Drug_Name\",
						RTRIM(COALESCE(Drug.DrugForm_Name, '')) as \"DrugForm_Name\",
						Drug.Drug_Dose as \"Drug_Dose\",
						Drug.Drug_Fas as \"Drug_Fas\",
						Drug.Drug_PackName as \"Drug_PackName\",
						Drug.Drug_Firm as \"Drug_Firm\",
						Drug.Drug_Ean as \"Drug_Ean\",
						Drug.Drug_RegNum as \"Drug_RegNum\",
						dMnn.DrugComplexMnn_RusName as \"DrugMnn\"
						-- end select
					from 
						-- from 
						rls.v_Drug Drug
						{$join}
						-- end from
					where 
						-- where
						{$filter} and {$filter_all} --and DUO.DocumentUcStr_Ost > 0
				";
				break;

			case 'income':
				$query = "
					select -- здесь был top 50, но я его убрал, согласно задаче # 
						-- select
						RTRIM(COALESCE(Drug.DrugTorg_Name, '')) as \"DrugTorg_Name\",
						Drug.Drug_id as \"Drug_id\",
						Drug.DrugPrepFas_id as \"DrugPrepFas_id\",
						Drug.Drug_Nomen as \"Drug_Nomen\",
						RTRIM(COALESCE(Drug.Drug_Nomen, '')) as \"Drug_Name\",
						RTRIM(COALESCE(Drug.DrugForm_Name, '')) as \"DrugForm_Name\",
						Drug.Drug_Dose as \"Drug_Dose\",
						Drug.Drug_Fas as \"Drug_Fas\",
						Drug.Drug_PackName as \"Drug_PackName\",
						Drug.Drug_Firm as \"Drug_Firm\",
						Drug.Drug_Ean as \"Drug_Ean\",
						Drug.Drug_RegNum as \"Drug_RegNum\",
						dMnn.DrugComplexMnn_RusName as \"DrugMnn\"
						-- end select
					from 
						-- from 
						rls.v_Drug Drug
						{$join}
						-- end from
					where 
						-- where 
						{$filter_all}
				";
				break;
		}

		/*
		if ( isset($data['Drug_id']) ) {
			$query .= " and Drug.Drug_id = :Drug_id";
			$queryParams['Drug_id'] = $data['Drug_id'];
		}
		*/

		if ( isset($data['DrugTorg_Name']) ) {
			$query .= " and (LOWER(Drug.DrugTorg_Name) like LOWER(:DrugTorg_Name) or LOWER(dMnn.DrugComplexMnn_RusName) like LOWER(:DrugTorg_Name))";
			$queryParams['DrugTorg_Name'] = "%".$data['DrugTorg_Name'] . "%";
		}

		if ( isset($data['DrugForm_Name']) ) {
			$query .= " and LOWER(Drug.DrugForm_Name) like LOWER(:DrugForm_Name)";
			$queryParams['DrugForm_Name'] = "%".$data['DrugForm_Name'] . "%";
		}

		if ( isset($data['Drug_PackName']) ) {
			$query .= " and LOWER(Drug.Drug_PackName) like LOWER(:Drug_PackName)";
			$queryParams['Drug_PackName'] = "%".$data['Drug_PackName'] . "%";
		}

		if ( isset($data['Drug_Dose']) ) {
			$query .= " and LOWER(Drug.Drug_Dose) like LOWER(:Drug_Dose)";
			$queryParams['Drug_Dose'] = "%".$data['Drug_Dose'] . "%";
		}

		if ( isset($data['Drug_Firm']) ) {
			$query .= " and LOWER(Drug.Drug_Firm) like LOWER(:Drug_Firm)";
			$queryParams['Drug_Firm'] = "%".$data['Drug_Firm'] . "%";
		}

		$query .= "
						-- end where
					order by 
					-- order by 
					Drug.Drug_Nomen
					-- end order by 	
		";
		/*
					order by
					-- order by
					Drug.Drug_Name
					-- end order by
		*/
		/*
		echo getDebugSql($query, $queryParams);
		exit;
		*/

		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit'], 'distinct'), $queryParams);
		$result_count = $this->db->query(getCountSQLPH($query, 'Drug.Drug_id', 'distinct'), $queryParams);

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
		return $response;
	}

}