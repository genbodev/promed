<?php
/**
* Модель - Электронный паспорт здоровья
* 
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* @package      All
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Andrew Markoff 
* @version      10.09.2009
*/
require_once(APPPATH.'models/EPH_model.php');

class Kz_EPH_model extends EPH_model
{
	
	
	/**
	 * Возваращает список нод
	 */
	function GetCmpCloseCard($data)
	{
		// Фильтры: Lpu_id, Person_id, Server_id
		$filter = "(1=1) ";
		$params = array();
		$addQuery = '';

		$archive_database_enable = $this->config->item('archive_database_enable');
		if (!empty($archive_database_enable)) {
			$addQuery .= "
				, case when 1=1/*ISNULL(CLC.CmpCloseCard_IsArchive, 1) = 1*/ then 0 else 1 end as archiveRecord
			";

			if (empty($_REQUEST['useArchive'])) {
				// только актуальные
				$filter .= " and 1=1/*ISNULL(CLC.CmpCloseCard_IsArchive, 1) = 1*/";
			} elseif (!empty($_REQUEST['useArchive']) && $_REQUEST['useArchive'] == 1) {
				// только архивные
				$filter .= " and 1=0/*ISNULL(CLC.CmpCloseCard_IsArchive, 1) = 2*/";
			} else {
				// все из архивной
				$filter .= "";
			}
		}

		if ((isset($data['Lpu_id'])) && ($data['Lpu_id']>0))
		{
			//$filter .= " and EvnPL.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		}
		elseif (isset($data['session']['lpu_id']))
		{
			//$filter .= " and EvnPL.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['session']['lpu_id'];
		}
		else {
			$params['Lpu_id'] = 0;
		}
		
		if ((isset($data['Person_id'])) && ($data['Person_id']>0))
		{
			$filter .= " and CC.Person_id = :Person_id";
			$params['Person_id'] = $data['Person_id'];
		}
		
		if ( isset($data['EvnDate_Range'][0]) )
		{
			$filter .= " and CLC.AcceptTime >= :Beg_EvnDate";
			$params['Beg_EvnDate'] = $data['EvnDate_Range'][0];
		}
		if ( isset($data['EvnDate_Range'][1]) )
		{
			$filter .= " and CLC.AcceptTime <= :End_EvnDate";
			$params['End_EvnDate'] = $data['EvnDate_Range'][1];
		}
	
		$accessType = 'CLC.Lpu_id = :Lpu_id';
		$diagFilter = getAccessRightsDiagFilter('Diag.Diag_Code');
		if (!empty($diagFilter)) {
			$filter .= " and $diagFilter";
		}
		$lpuFilter = getAccessRightsLpuFilter('CLC.Lpu_id');
		if (!empty($lpuFilter)) {
			$filter .= " and $lpuFilter";
		}
		$lpuBuildingFilter = getAccessRightsLpuBuildingFilter('CLC.LpuBuilding_id');
		if (!empty($lpuBuildingFilter)) {
			$filter .= " and $lpuBuildingFilter";
		}
		
		$sql = "
		select 
			case when {$accessType} then 'edit' else 'view' end as accessType,
			CLC.CmpCloseCard_id, 
			'Вызов скорой помощи' as Name, 
			CC.Lpu_id,
			CLC.CmpCallCard_id,			
			CLC.Day_num,			
			CLC.Year_num,			
			RTrim(IsNull(convert(varchar,cast(CLC.AcceptTime as datetime),104),'')) as AcceptTime,
			RTrim(Lpu.Lpu_Nick) as Lpu_Nick
			{$addQuery}
		from r101.v_CmpCloseCard CLC with (nolock)
		left join v_CmpCallCard CC with (nolock) on CC.CmpCallCard_id=CLC.CmpCallCard_id
		left join v_Lpu Lpu with (nolock) on Lpu.Lpu_id=CC.Lpu_id
		left join v_Diag Diag with (nolock) on Diag.Diag_id = CLC.Diag_id
		where {$filter}
		--order by CLC.AcceptTime";
		//echo getDebugSQL($sql, $params);
		$res = $this->db->query($sql, $params);
		if ( is_object($res) )
		{
			return swFilterResponse::filterNotViewDiag($res->result('array'), $data);
		}
		else
			return false;
	}
	
	 

	/**
	 * Возвращает список нод
	 */
	function GetCmpCallCardCostPrintNodeList($data) {
		$params = array(
			'Lpu_id' => $data['session']['lpu_id']
		);
		$filter = '1=1';

		if ( isset($data['CmpCallCard_id'] ) && $data['CmpCallCard_id'] > 0 ) {
			$params['CmpCallCard_id'] = $data['CmpCallCard_id'];
		}

		if ( isset($data['CmpCloseCard_id']) && $data['CmpCloseCard_id'] > 0 ) {
			$params['CmpCallCard_id'] = $this->getFirstResultFromQuery("select CmpCallCard_id from r101.v_CmpCloseCard (nolock) where CmpCloseCard_id = :CmpCloseCard_id", $data);
		}

		if(empty($params['CmpCallCard_id']))
		{
			$filter .= ' and CCP.Person_id = :Person_id';
			$params['Person_id'] = $data['Person_id'];
		}
		else
		{
			$filter .= ' and CCP.CmpCallCard_id = :CmpCallCard_id';
		}

		$sql = "
			select
				'edit' as accessType,
				CCP.CmpCallCardCostPrint_id,
				convert(varchar(10), CCP.CmpCallCardCostPrint_insDT, 104) as sortdate,
				convert(varchar(10), CCP.CmpCallCardCostPrint_setDT, 104) as CmpCallCardCostPrint_setDate, -- Дата выдачи
				ISNULL(CmpCallCardCostPrint_IsNoPrint, 1) as CmpCallCardCostPrint_IsNoPrint
			from v_CmpCallCardCostPrint CCP with (nolock)
			where
				{$filter}
			--order by CCP.CmpCallCardCostPrint_insDT desc
		";

		$res = $this->db->query($sql, $params);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

}