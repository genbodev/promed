<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Mes_model - модель для работы с МЕСами.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009 - 2010 Swan Ltd.
* @author       Pshenitcyn Ivan aka IvP (ipshon@rambler.ru)
* @version      16.02.2010
*/
class Mes_model extends swModel
{
	/**
	 * @dsf
	 */
	function __construct()
	{
		parent::__construct();
	}
	/**
	 * @dsf
	 */
	function saveMes($data) 
	{
		
		if ( isset($data['Mes_id']) && $data['Mes_id'] > 0 )
		{
			$proc = 'p_Mes_upd';
		}
		else
		{
			$proc = 'p_Mes_ins';
			$data['Mes_id'] = NULL;
			// генерируем код
			$query = <<<Q
				select
					mp.MesProf_Code,
					mag.MesAgeGroup_Code,
					olut.OmsLpuUnitType_Code,
					ml.MesLevel_Code,
					dg.Diag_Code,
					mes1.cnt as cnt,
					mes1.mx as mx
				from
					MesProf mp with(nolock),
					MesAgeGroup mag with(nolock),
					OmsLpuUnitType olut with(nolock),
					MesLevel ml with(nolock),
					Diag dg with(nolock)
					outer apply (
						select 
							COUNT(Mes_id) as cnt, max(CAST(RIGHT(Mes_Code, 2) as int)) + 1 as mx
						from
							Mes with(nolock)
						where
							MesProf_id = :MesProf_id and
							MesAgeGroup_id = :MesAgeGroup_id and
							OmsLpuUnitType_id = :OmsLpuUnitType_id and
							MesLevel_id = :MesLevel_id and
							Diag_id = :Diag_id
							
					) as mes1
				where
					mp.MesProf_id = :MesProf_id and
					mag.MesAgeGroup_id = :MesAgeGroup_id and
					olut.OmsLpuUnitType_id = :OmsLpuUnitType_id and
					ml.MesLevel_id = :MesLevel_id and
					dg.Diag_id = :Diag_id
Q;
			$result = $this->db->query($query, array(
				'MesProf_id' => $data['MesProf_id'],
				'MesAgeGroup_id' => $data['MesAgeGroup_id'],
				'OmsLpuUnitType_id' => $data['OmsLpuUnitType_id'],
				'MesLevel_id' => $data['MesLevel_id'],
				'Diag_id' => $data['Diag_id']
			));
			
			if (is_object($result)) 
			{
				$res = $result->result('array');
				if ( count($res) > 0 )
				{
					$data['Mes_Code'] = '';
					$data['Mes_Code'] .= $res[0]['MesProf_Code'];
					$data['Mes_Code'] .= '.';
					$data['Mes_Code'] .= $res[0]['MesAgeGroup_Code'];
					$data['Mes_Code'] .= '.';
					$data['Mes_Code'] .= $res[0]['OmsLpuUnitType_Code'];
					$data['Mes_Code'] .= '.';
					$data['Mes_Code'] .= $res[0]['MesLevel_Code'];
					$data['Mes_Code'] .= '.';
					$data['Mes_Code'] .= str_replace('.', '', $res[0]['Diag_Code']);
					if ( strlen($data['Mes_Code']) <= 3 )
						$data['Mes_Code'] .= 'X';
					if ( $res[0]['cnt'] == 0 )
						$data['Mes_Code'] .= '00';
					else
					{
						if ( $res[0]['mx'] <= 9 )
							$data['Mes_Code'] .= '0';
						$data['Mes_Code'] .= $res[0]['mx'];
					}
				}
				else
				{
					return array('success' => false, 'Error_Msg' => 'Не удалось сгенерировать код ' . getMESAlias());
				}
			}
			else 
			{
				return array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных');
			}
		}
		$query = "
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000),
				@Mes_id bigint = ?;
			exec " .$proc. "
				@Mes_id = @Mes_id output,
				@Mes_Code = ?,
				@Mes_KoikoDni = ?,
				@Mes_begDT = ?,
				@Mes_endDT = ?,
				@MesAgeGroup_id = ?,
				@MesLevel_id = ?,
				@MesProf_id = ?,
				@Diag_id = ?,
				@OmsLpuUnitType_id = ?,
				@Mes_DiagClinical = ?,
				@Mes_DiagVolume = ?,
				@Mes_Consulting = ?,
				@Mes_CureVolume = ?,
				@Mes_QualityMeasure = ?,
				@Mes_ResultClass = ?,
				@Mes_ComplRisk = ?,
				@pmUser_id = ?,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @Mes_id as Mes_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		$result = $this->db->query($query, array(
			$data['Mes_id'],
			$data['Mes_Code'],
			$data['Mes_KoikoDni'],
			$data['Mes_begDT'],
			$data['Mes_endDT'],
			$data['MesAgeGroup_id'],
			$data['MesLevel_id'],
			$data['MesProf_id'],
			$data['Diag_id'],
			$data['OmsLpuUnitType_id'],
			$data['Mes_DiagClinical'],
			$data['Mes_DiagVolume'],
			$data['Mes_Consulting'],
			$data['Mes_CureVolume'],
			$data['Mes_QualityMeasure'],
			$data['Mes_ResultClass'],
			$data['Mes_ComplRisk'],
			$data['pmUser_id']
		));
		
		
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
	 * @sdafsdf
	 */
	function loadMes($data)
	{
		$sql = "
			SELECT
				Mes_id,
				Mes_Code,
				MesProf_id,
				MesAgeGroup_id,
				MesLevel_id,
				Diag_id,
				OmsLpuUnitType_id,
				Mes_KoikoDni,
				convert(varchar,cast(Mes_begDT as datetime),104) as Mes_begDT,
				convert(varchar,cast(Mes_endDT as datetime),104) as Mes_endDT,
				Mes_DiagClinical,
				Mes_DiagVolume,
				Mes_Consulting,
				Mes_CureVolume,
				Mes_QualityMeasure,
				Mes_ResultClass,
				Mes_ComplRisk				
			FROM
				Mes  with(nolock)
			WHERE
				Mes_id = ?
		";
        $result = $this->db->query($sql, array($data['Mes_id']));

        if (is_object($result))
        {
            return $result->result('array');
        }
        else
        {
        	return false;
        }
	}
	
	/**
	* Метод для получения списка записей для поиска по МЭСам
	*/
	function loadMesListForDbf($data)
	{
	
		$filter = " ( 1 = 1 ) ";
		$queryParams = array();
	
		if ( isset($data['MesStatus_id']) )
		{
			switch ( $data['MesStatus_id'] )
			{
				case 1:
					$filter .= " and ms.Mes_endDT is null";
				break;
				case 2:
					$filter .= " and ms.Mes_begDT <= dbo.tzGetDate() and ms.Mes_endDT is null ";
				break;
				case 3:
					$filter .= " and ms.Mes_endDT is not null and ms.Mes_endDT <= dbo.tzGetDate() ";
				break;
				case 4:
					$filter .= " and ms.Mes_begDT > dbo.tzGetDate() and ms.Mes_endDT is null ";
				break;				
			}					
		}
		
		if ( isset($data['MesProf_id']) )
		{
			$filter .= "
			and ms.MesProf_id = :MesProf_id 
			";
			$queryParams['MesProf_id'] = $data['MesProf_id'];
		}
		
		if ( isset($data['MesAgeGroup_id']) )
		{
			$filter .= "
			and ms.MesAgeGroup_id = :MesAgeGroup_id 
			";
			$queryParams['MesAgeGroup_id'] = $data['MesAgeGroup_id'];
		}
		
		if ( isset($data['MesLevel_id']) )
		{
			$filter .= "
			and ms.MesLevel_id = :MesLevel_id 
			";
			$queryParams['MesLevel_id'] = $data['MesLevel_id'];
		}
		
		if ( isset($data['OmsLpuUnitType_id']) )
		{
			$filter .= "
			and ms.OmsLpuUnitType_id = :OmsLpuUnitType_id 
			";
			$queryParams['OmsLpuUnitType_id'] = $data['OmsLpuUnitType_id'];
		}
		
		if ( isset($data['Diag_id']) )
		{
			$filter .= "
			and ms.Diag_id = :Diag_id 
			";
			$queryParams['Diag_id'] = $data['Diag_id'];
		}
		
		if ( isset($data['Mes_KoikoDni_From']) || isset($data['Mes_KoikoDni_To']) )
		{
			if ( isset($data['Mes_KoikoDni_From']) )
			{
				$filter .= "
				and ms.Mes_KoikoDni >= :Mes_KoikoDni_From 
				";
				$queryParams['Mes_KoikoDni_From'] = $data['Mes_KoikoDni_From'];
			}
			
			if ( isset($data['Mes_KoikoDni_To']) )
			{
				$filter .= "
				and ms.Mes_KoikoDni <= :Mes_KoikoDni_To 
				";
				$queryParams['Mes_KoikoDni_To'] = $data['Mes_KoikoDni_To'];
			}
		}
				
		if ( isset($data['Mes_begDT_Range'][0]) ) {
			$filter .= " and ms.Mes_begDT >= cast(:Mes_begDT_Range_0 as datetime)";
			$queryParams['Mes_begDT_Range_0'] = $data['Mes_begDT_Range'][0];
		}

		if ( isset($data['Mes_begDT_Range'][1]) ) {
			$filter .= " and ms.Mes_begDT <= cast(:Mes_begDT_Range_1 as datetime)";
			$queryParams['Mes_begDT_Range_1'] = $data['Mes_begDT_Range'][1];
		}
		
		if ( isset($data['Mes_endDT_Range'][0]) ) {
			$filter .= " and ms.Mes_endDT >= cast(:Mes_endDT_Range_0 as datetime)";
			$queryParams['Mes_endDT_Range_0'] = $data['Mes_endDT_Range'][0];
		}

		if ( isset($data['Mes_endDT_Range'][1]) ) {
			$filter .= " and ms.Mes_endDT <= cast(:Mes_endDT_Range_1 as datetime)";
			$queryParams['Mes_endDT_Range_1'] = $data['Mes_endDT_Range'][1];
		}
	
		$sql = "
			select
				case when mp.MesProf_id is null then '' else cast(mp.MesProf_Code as varchar) end as codespec,
				case when mag.MesAgeGroup_id is null then '' else mag.MesAgeGroup_Code end as vzdet,
				case when mp.MesProf_id is null then '' else cast(mp.MesProf_Name as varchar) end as namespec,
				case when dg.Diag_id is null then '' else cast(dg.Diag_Code as varchar)end as codemkb,
				case when ml.MesLevel_id is null then '' else ml.MesLevel_Code end as [level],
				case when olut.OmsLpuUnitType_id is null then '' else olut.OmsLpuUnitType_Code end as stactype,
				ms.Mes_KoikoDni as stac,
				ms.Mes_id as mes_id,
				ms.Mes_Code as codemes,
				case when dg.Diag_id is null then '' else cast(dg.Diag_Name as varchar(200))end as namemkb,
				RTrim(IsNull(convert(varchar,cast(ms.Mes_begDT as datetime),104),'')) as datebeg,
				RTrim(IsNull(convert(varchar,cast(ms.Mes_endDT as datetime),104),'')) as dateend,
				-- Mes_DiagClinical as ,
				Mes_DiagClinical as diagclin,
				Mes_DiagVolume as diag_vol,
				Mes_Consulting as consult,
				Mes_CureVolume as cure_vol,
				Mes_QualityMeasure as qual_cri,
				Mes_ResultClass as s_result,
				Mes_ComplRisk as agg_risk
			from
				Mes ms with(nolock)
				left join MesProf mp with(nolock) on mp.MesProf_id = ms.MesProf_id
				left join MesAgeGroup mag with(nolock) on mag.MesAgeGroup_id = ms.MesAgeGroup_id
				left join OmsLpuUnitType olut with(nolock) on olut.OmsLpuUnitType_id = ms.OmsLpuUnitType_id
				left join MesLevel ml with(nolock) on ml.MesLevel_id = ms.MesLevel_id
				left join Diag dg with(nolock) on dg.Diag_id = ms.Diag_id
			where
			" . $filter . "
			order by
				ms.Mes_updDT desc
		";
		
		$result = $this->db->query($sql, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}	
	
	/**
	* Метод для получения списка записей для поиска по МЭСам
	*/	
	function loadMesOldComboSearchList($data)
	{
		$filter = " ( 1 = 1 ) ";
		$queryParams = array();

		if (!isset($data['Diag_Name'])) return false;
		
		$sql = "
			select
			-- select
				ms.Mes_id,
				ms.Mes_Code,
				dg.Diag_Name
			-- end select
			from
			-- from
				MesOld ms with(nolock)
				left join Diag dg with(nolock) on dg.Diag_id = ms.Diag_id
			-- end from
			where
			-- where
				Diag_Name Like '%".$data['Diag_Name']."%'
			-- end where
			order by
			-- order by
				ms.MesOld_updDT desc
			-- end order by
		";

		$result = $this->db->query($sql, $queryParams);
		if ( is_object($result) ) {
			$res = $result->result('array');
			if ( is_array($res) ) {
				$response['data'] = $res;
			}
		} else {
			return false;
		}
		
		return $response;
	}
	
	/**
	 * @sdfsd
	 */
	function loadMesOldVizit($data){
		
		$filter = " ( 1 = 1 ) ";
		$queryParams = array();
		
		if (isset($data['UslugaComplex_id'])) {
			$queryParams['UslugaComplex_id'] = $data['UslugaComplex_id'];
			$filter .= " and mu.Uslugacomplex_id = :UslugaComplex_id";
		}

		if (!empty($data['Mes_Codes'])) {
			$mes_codes = json_decode($data['Mes_Codes'], true);
			$mes_codes_str = "'".implode("','", $mes_codes)."'";
			$filter .= " and mo.Mes_Code in ({$mes_codes_str})";
		}

		if ($data['MesType_id'] === '0' || $data['MesType_id'] === 0) {
			$filter .= " and mo.MesType_id is null";	
		} elseif (!empty($data['MesType_id'])) {
			$queryParams['MesType_id'] = $data['MesType_id'];
			$filter .= " and mo.MesType_id = :MesType_id";
		}
		if(isset($data['EvnDate']))
		{
			$filter .= " and (mo.Mes_begDT <= :EvnDate and (mo.Mes_endDT is null or mo.Mes_endDT >= :EvnDate))";
			$queryParams['EvnDate'] = $data['EvnDate'];
		}
		if (!empty($data['UslugaComplexPartition_CodeList'])) {
			$UslugaComplexPartition_CodeList = json_decode($data['UslugaComplexPartition_CodeList'], true);
			$UslugaComplexPartition_CodeList_str = "'".implode("','", $UslugaComplexPartition_CodeList)."'";
			$filter .= " and exists(
				select top 1 UC.UslugaComplex_id
				from v_UslugaComplex UC with (nolock)
				inner join r66.v_UslugaComplexPartitionLink ucpl (nolock) on ucpl.UslugaComplex_id = uc.UslugaComplex_id
				inner join r66.v_UslugaComplexPartition ucp (nolock) on ucp.UslugaComplexPartition_id = ucpl.UslugaComplexPartition_id
					and ucp.UslugaComplexPartition_Code in ({$UslugaComplexPartition_CodeList_str})
				where UC.UslugaComplex_id = mu.UslugaComplex_id
			)";
		}
		
		$sql = "
			select distinct 
				mo.Mes_id as MesOldVizit_id,
				mo.Mes_Code as MesOldVizit_Code,
				ISNULL(mo.Mes_Name,'') as MesOldVizit_Name
			from
				v_MesOld mo (nolock)
				inner join v_MesUsluga mu with (nolock) on mu.Mes_id = mo.Mes_id and mu.MesUslugaLinkType_id = 5
			where $filter
		";
		//echo getDebugSQL($sql, $queryParams);exit();
		$result = $this->db->query($sql, $queryParams);
		$response = array();
		if ( is_object($result) ) {
			$res = $result->result('array');
			if ( is_array($res) ) {
				$response['data'] = $res;
			}
		} else {
			return false;
		}
		
		return $response;
		
	}
	
	/**
	* Метод для получения списка кодов старых МЭС
	*/	
	function searchFullMesOldCodeList($data){
		$filter = " ( 1 = 1 ) ";
		$queryParams = array();
		
		if (!isset($data['query']) && !isset($data['Mes_id'])) return false;
		
		if (isset($data['Mes_id']) && $data['Mes_id'] > 0) {
			$filter .= " and Mes_id = ".$data['Mes_id']."";
		} else {
			$filter .= " and Mes_Code Like '".$data['query']."%'";
		}
		
		
		
		$sql = "
			select
				ms.Mes_id,
				ms.Mes_Code
			from
				MesOld ms with(nolock)
			where
				$filter
			order by
				Mes_Code desc
		";

		$result = $this->db->query($sql, $queryParams);
		if ( is_object($result) ) {
			$res = $result->result('array');
			if ( is_array($res) ) {
				$response['data'] = $res;
			}
		} else {
			return false;
		}
		
		return $response;
	}

	/**
	* Метод для получения списка записей для поиска по МЭСам
	*/	
	function loadMesSearchList($data)
	{
		$filter = " ( 1 = 1 ) ";
		$queryParams = array();
	
		if ( isset($data['MesStatus_id']) )
		{
			switch ( $data['MesStatus_id'] )
			{
				case 1:
					$filter .= " and ms.Mes_endDT is null";
				break;
				case 2:
					$filter .= " and ms.Mes_begDT <= dbo.tzGetDate() and ms.Mes_endDT is null ";
				break;
				case 3:
					$filter .= " and ms.Mes_endDT is not null and ms.Mes_endDT <= dbo.tzGetDate() ";
				break;
				case 4:
					$filter .= " and ms.Mes_begDT > dbo.tzGetDate() and ms.Mes_endDT is null ";
				break;				
			}					
		}
		
		if ( isset($data['MesProf_id']) )
		{
			$filter .= "
			and ms.MesProf_id = :MesProf_id 
			";
			$queryParams['MesProf_id'] = $data['MesProf_id'];
		}
		
		if ( isset($data['MesAgeGroup_id']) )
		{
			$filter .= "
			and ms.MesAgeGroup_id = :MesAgeGroup_id 
			";
			$queryParams['MesAgeGroup_id'] = $data['MesAgeGroup_id'];
		}
		
		if ( isset($data['MesLevel_id']) )
		{
			$filter .= "
			and ms.MesLevel_id = :MesLevel_id 
			";
			$queryParams['MesLevel_id'] = $data['MesLevel_id'];
		}
		
		if ( isset($data['OmsLpuUnitType_id']) )
		{
			$filter .= "
			and ms.OmsLpuUnitType_id = :OmsLpuUnitType_id 
			";
			$queryParams['OmsLpuUnitType_id'] = $data['OmsLpuUnitType_id'];
		}
		
		if ( isset($data['Diag_id']) )
		{
			$filter .= "
			and ms.Diag_id = :Diag_id 
			";
			$queryParams['Diag_id'] = $data['Diag_id'];
		}
		
		if ( isset($data['Mes_KoikoDni_From']) || isset($data['Mes_KoikoDni_To']) )
		{
			if ( isset($data['Mes_KoikoDni_From']) )
			{
				$filter .= "
				and ms.Mes_KoikoDni >= :Mes_KoikoDni_From 
				";
				$queryParams['Mes_KoikoDni_From'] = $data['Mes_KoikoDni_From'];
			}
			
			if ( isset($data['Mes_KoikoDni_To']) )
			{
				$filter .= "
				and ms.Mes_KoikoDni <= :Mes_KoikoDni_To 
				";
				$queryParams['Mes_KoikoDni_To'] = $data['Mes_KoikoDni_To'];
			}
		}
				
		if ( isset($data['Mes_begDT_Range'][0]) ) {
			$filter .= " and ms.Mes_begDT >= cast(:Mes_begDT_Range_0 as datetime)";
			$queryParams['Mes_begDT_Range_0'] = $data['Mes_begDT_Range'][0];
		}

		if ( isset($data['Mes_begDT_Range'][1]) ) {
			$filter .= " and ms.Mes_begDT <= cast(:Mes_begDT_Range_1 as datetime)";
			$queryParams['Mes_begDT_Range_1'] = $data['Mes_begDT_Range'][1];
		}
		
		if ( isset($data['Mes_endDT_Range'][0]) ) {
			$filter .= " and ms.Mes_endDT >= cast(:Mes_endDT_Range_0 as datetime)";
			$queryParams['Mes_endDT_Range_0'] = $data['Mes_endDT_Range'][0];
		}

		if ( isset($data['Mes_endDT_Range'][1]) ) {
			$filter .= " and ms.Mes_endDT <= cast(:Mes_endDT_Range_1 as datetime)";
			$queryParams['Mes_endDT_Range_1'] = $data['Mes_endDT_Range'][1];
		}
		
		$sql = "
			select
			-- select
				ms.Mes_id,
				ms.Mes_Code,
				case when mp.MesProf_id is null then '' else cast(mp.MesProf_Code as varchar) + '. ' + mp.MesProf_Name end as MesProf_CodeName,
				case when mag.MesAgeGroup_id is null then '' else cast(mag.MesAgeGroup_Code as varchar) + '. ' + mag.MesAgeGroup_Name end as MesAgeGroup_CodeName,
				ms.Mes_KoikoDni,
				case when olut.OmsLpuUnitType_id is null then '' else cast(olut.OmsLpuUnitType_Code as varchar) + '. ' + olut.OmsLpuUnitType_Name end as OmsLpuUnitType_CodeName,
				--case when ml.MesLevel_id is null then '' else cast(ml.MesLevel_Code as varchar) + '. ' + ml.MesLevel_Name end as MesLevel_CodeName,
				case when ml.MesLevel_id is null then '' else cast(ml.MesLevel_Code as varchar) end as MesLevel_CodeName,
				case when dg.Diag_id is null then '' else cast(dg.Diag_Code as varchar) + '. ' + dg.Diag_Name end as Diag_CodeName,
				convert(varchar,cast(ms.Mes_begDT as datetime),104) as Mes_begDT,
				convert(varchar,cast(ms.Mes_endDT as datetime),104) as Mes_endDT,
				Mes_DiagClinical,
				Mes_DiagVolume,
				Mes_Consulting,
				Mes_CureVolume,
				Mes_QualityMeasure,
				Mes_ResultClass,
				Mes_ComplRisk,
				case when ms.Mes_endDT is not null and ms.Mes_endDT <= dbo.tzGetDate() then 3 else case when ms.Mes_begDT > dbo.tzGetDate() and ms.Mes_endDT is null then 4 else 1 end end as MesStatus
			-- end select
			from
			-- from
				Mes ms with(nolock)
				left join MesProf mp with(nolock) on mp.MesProf_id = ms.MesProf_id
				left join MesAgeGroup mag with(nolock) on mag.MesAgeGroup_id = ms.MesAgeGroup_id
				left join OmsLpuUnitType olut with(nolock) on olut.OmsLpuUnitType_id = ms.OmsLpuUnitType_id
				left join MesLevel ml with(nolock) on ml.MesLevel_id = ms.MesLevel_id
				left join Diag dg with(nolock) on dg.Diag_id = ms.Diag_id
			-- end from
			where
			-- where
			" . $filter . "
			-- end where
			order by
			-- order by
				ms.Mes_updDT desc
			-- end order by
		";

		return $this->getPagingResponse($sql, $queryParams, $data['start'], $data['limit'], true);
	}
}