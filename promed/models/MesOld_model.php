<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* MesOld_model - модель для работы с МЕСами.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009 - 2010 Swan Ltd.
* @author       Pshenicyn Ivan aka IVP (ipshon@rambler.ru)
* @author       Markoff A.A. <markov@swan.perm.ru>
* @version      08.08.2011
*/
class MesOld_model extends swModel
{
    /**
     * comment
     */
    function __construct()
	{
		parent::__construct();
	}

	/**
	 * Получение списка КСГ
	 */
	function loadKsgList($data) {
		$queryParams = array();
		$filters = " (1=1) ";

		if ( !empty($data['Mes_id']) ) {
			$queryParams['Mes_id'] = $data['Mes_id'];
			$filters .= " and MO.Mes_id = :Mes_id";
		}
		else {
			if ( !empty($data['query']) ) {
				$queryParams['queryCode'] = $data['query'] . '%';
				$queryParams['queryName'] = '%'. $data['query'] . '%';

				$filters .= " and (
					Mes_Code like :queryCode
					or cast(MesOld_Num as varchar) like :queryCode
					or Mes_Name like :queryName
				)";
			}


			$data['mesTypeList'] = json_decode($data['mesTypeList']);
			if ( !empty($data['mesTypeList']) ) {
				$queryParams['mesTypeList'] = $data['mesTypeList'];
				$filters .= " and MO.MesType_id in (".implode(', ', $data['mesTypeList']).")";
			} else {
				$filters .= " and MO.MesType_id in (2,3,4,5,7)";
			}

			// @task https://redmine.swan.perm.ru/issues/81634
			if ( !empty($data['startYear']) ) {
				$filters .= " and YEAR(MO.Mes_begDT) >= :startYear";
				$queryParams['startYear'] = $data['startYear'];
			}
		}

		if (!empty($data['groupByCode'])) {
			$query = "
				select top 100
					MIN(MO.Mes_id) as Mes_id,
					MO.Mes_Code,
					MO.MesOld_Num,
					MO.Mes_Name,
					MIN(MT.MesType_Code) as MesType_Code,
					ISNULL(convert(varchar(10), MO.Mes_begDT, 104),'') as Mes_begDT,
					CASE WHEN MAX(CASE WHEN MO.Mes_endDT IS NULL THEN 1 ELSE 0 END) = 0 THEN ISNULL(convert(varchar(10), MAX(MO.Mes_endDT), 104),'') ELSE '' end as Mes_endDT,
					MIN(MT.MesType_Name) as MesType_Name
				from
					v_MesOld MO (nolock)
					left join v_MesType MT with (nolock) on MO.MesType_id = MT.MesType_id
				where
					{$filters}
				group by
					mo.Mes_Code, mo.MesOld_Num, mo.Mes_Name, mo.Mes_begDT
				order by
					MIN(mo.Mes_id)
			";
		} else {
			$query = "
				select top 100
					MO.Mes_id,
					MO.Mes_Code,
					MO.MesOld_Num,
					MO.Mes_Name,
					MT.MesType_Code,
					ISNULL(convert(varchar(10), MO.Mes_begDT, 104),'') as Mes_begDT,
					ISNULL(convert(varchar(10), MO.Mes_endDT, 104),'') as Mes_endDT,
					MT.MesType_Name
				from
					v_MesOld MO (nolock)
					left join v_MesType MT with (nolock) on MO.MesType_id = MT.MesType_id
				where
					{$filters}
				order by
					MO.Mes_begDT DESC
			";
		}

		$result = $this->db->query($query, $queryParams);
		if (is_object($result)) {
			return $result->result('array');
		}

		return false;
	}
	/*
	function saveMesOld($data) 
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
			$query = "
				select
					mp.MesProf_Code,
					mag.MesAgeGroup_Code,
					olut.OmsLpuUnitType_Code,
					ml.MesLevel_Code,
					dg.Diag_Code,
					mes1.cnt as cnt,
					mes1.mx as mx
				from
					MesProf mp,
					MesAgeGroup mag,
					OmsLpuUnitType olut,
					MesLevel ml,
					Diag dg
					outer apply (
						select 
							COUNT(Mes_id) as cnt, max(CAST(RIGHT(Mes_Code, 2) as int)) + 1 as mx
						from
							MesOld
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
			";
			
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
					return array('success' => false, 'Error_Msg' => 'Не удалось сгенерировать код МЭС');
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
	*/
    /**
     * @param $data
     * @return bool
     *
     */
    function loadMesOld($data)
	{
		$sql = "
			SELECT
				Mes_id,
				Mes_Code,
				MesProf_id,
				MesAgeGroup_id,
				MesLevel_id,
				Diag_id,
				Mes_KoikoDniMin,
				Mes_VizitNumber,
				Mes_KoikoDni,
				convert(varchar,cast(Mes_begDT as datetime),104) as Mes_begDT,
				convert(varchar,cast(Mes_endDT as datetime),104) as Mes_endDT,
				MedicalCareKind_id
			FROM
				v_MesOld with (nolock)
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
	/*
	function loadMesOldListForDbf($data)
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
				ms.Mes_KoikoDni as stac,
				ms.Mes_id as Mes_id,
				ms.Mes_Code as codemes,
				case when dg.Diag_id is null then '' else cast(dg.Diag_Name as varchar(200))end as namemkb,
				RTrim(IsNull(convert(varchar,cast(ms.Mes_begDT as datetime),104),'')) as datebeg,
				RTrim(IsNull(convert(varchar,cast(ms.Mes_endDT as datetime),104),'')) as dateend
				
			from
				MesOld ms
				left join MesProf mp with(nolock) on mp.MesProf_id = ms.MesProf_id
				left join MesAgeGroup mag with(nolock) on mag.MesAgeGroup_id = ms.MesAgeGroup_id
				left join MesLevel ml with(nolock) on ml.MesLevel_id = ms.MesLevel_id
				left join Diag dg with(nolock) on dg.Diag_id = ms.Diag_id
			where
			" . $filter . "
			order by
				ms.MesOld_updDT desc
		";
		
		$result = $this->db->query($sql, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}	
	*/
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
				MesOld ms with (nolock)
				left join Diag dg with (nolock) on dg.Diag_id = ms.Diag_id
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
				MesOld ms with (nolock)
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
	function loadMesOldSearchList($data)
	{
		$filter = " ( 1 = 1 ) ";
		$queryParams = array();
		$queryParams['start'] = $data['start'];
		$queryParams['limit'] = $data['limit'];
	
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

		// Диагноз с
		if ( isset($data['Diag_Code_From']) ) {
			$filter .= " and dg.Diag_Code >= :Diag_Code_From";
			$queryParams['Diag_Code_From'] = $data['Diag_Code_From'];
		}

		// Диагноз по
		if ( isset($data['Diag_Code_To']) ) {
			$filter .= " and dg.Diag_Code <= :Diag_Code_To";
			$queryParams['Diag_Code_To'] = $data['Diag_Code_To'];
		}

		if ( isset($data['MedicalCareKind_id']) )
		{
			$filter .= "
			and ms.MedicalCareKind_id = :MedicalCareKind_id
			";
			$queryParams['MedicalCareKind_id'] = $data['MedicalCareKind_id'];
			if ('47' == $data['MedicalCareKind_id']) {
				$filter .= " and ms.mesProf_id IN ( 63,64,65,66,67,68) ";
			}
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
		
		$orderby = "";
		if ( !empty($data['sort']) && !empty($data['dir']) ) {
			switch ($data['sort']) {
				case 'MesProf_CodeName':
					$data['sort'] = "mp.MesProf_Code";
				break;
				
				case 'MesAgeGroup_CodeName':
					$data['sort'] = "mag.MesAgeGroup_Code";
				break;
				
				case 'MesLevel_CodeName':
					$data['sort'] = "ml.MesLevel_Code";
				break;
				
				case 'Diag_CodeName':
					$data['sort'] = "dg.Diag_Code";
				break;
				
				default:
					$data['sort'] = "ms.{$data['sort']}";
			}
			$orderby = "{$data['sort']} {$data['dir']},";
		}
		
		$sql = "
			select
			-- select
				ms.Mes_id,
				ms.Mes_Code,
				case when mp.MesProf_id is null then '' else cast(mp.MesProf_Code as varchar) + '. ' + mp.MesProf_Name end as MesProf_CodeName,
				case when mag.MesAgeGroup_id is null then '' else cast(mag.MesAgeGroup_Code as varchar) + '. ' + mag.MesAgeGroup_Name end as MesAgeGroup_CodeName,
				--case when ml.MesLevel_id is null then '' else cast(ml.MesLevel_Code as varchar) + '. ' + ml.MesLevel_Name end as MesLevel_CodeName,
				case when ml.MesLevel_id is null then '' else cast(ml.MesLevel_Code as varchar) end as MesLevel_CodeName,
				case when dg.Diag_id is null then '' else cast(dg.Diag_Code as varchar) + '. ' + dg.Diag_Name end as Diag_CodeName,
				ms.Mes_KoikoDni,
				ms.Mes_VizitNumber,
				ms.Mes_KoikoDniMin,
				convert(varchar,cast(ms.Mes_begDT as datetime),104) as Mes_begDT,
				convert(varchar,cast(ms.Mes_endDT as datetime),104) as Mes_endDT,
				case when ms.Mes_endDT is not null and ms.Mes_endDT <= dbo.tzGetDate() then 3 else case when ms.Mes_begDT > dbo.tzGetDate() and ms.Mes_endDT is null then 4 else 1 end end as MesStatus
			-- end select
			from
			-- from
				v_MesOld ms with (nolock)
				left join MesProf mp with (nolock) on mp.MesProf_id = ms.MesProf_id
				left join MesAgeGroup mag with (nolock) on mag.MesAgeGroup_id = ms.MesAgeGroup_id
				left join MesLevel ml with (nolock) on ml.MesLevel_id = ms.MesLevel_id
				left join Diag dg with (nolock) on dg.Diag_id = ms.Diag_id
			-- end from
			where
			-- where
			" . $filter . "
			-- end where
			order by
			-- order by
				{$orderby} ms.Mes_updDT desc, Mes_id desc
			-- end order by
		";

		return $this->getPagingResponse($sql, $queryParams, $data['start'], $data['limit'], true);
	}

    /**
     * @param $data
     * @return array
     * @throws Exception
     *
     */
    function save($data) {
        $this->load->helper('Text');
		$procedure = 'p_MesOld_ins';
		if ( $data['Mes_id'] > 0 ) {
			$procedure = 'p_MesOld_upd';
		} else {
			$data['Mes_id'] = NULL;
        }
        // генерируем код
        $query = <<<Q
				select
					mp.MesProf_Code,
					mag.MesAgeGroup_Code,
					ml.MesLevel_Code,
					dg.Diag_Code,
					mes1.maxletter as maxletter
				from
					MesProf mp,
					MesAgeGroup mag,
					MesLevel ml,
					Diag dg
					outer apply (
						select
							MAX(SUBSTRING(Mes_Code, LEN(Mes_Code), 1)) AS maxletter
						from
							MesOld
						where
							MesProf_id = :MesProf_id and
							MesAgeGroup_id = :MesAgeGroup_id and
							MesLevel_id = :MesLevel_id and
							Diag_id = :Diag_id and
							(Mes_id = :Mes_id or (:Mes_id is null))
					) as mes1
				where
					mp.MesProf_id = :MesProf_id and
					mag.MesAgeGroup_id = :MesAgeGroup_id and
					ml.MesLevel_id = :MesLevel_id and
					dg.Diag_id = :Diag_id
Q;
        $parameters = array(
            'MesProf_id' => $data['MesProf_id'],
            'MesAgeGroup_id' => $data['MesAgeGroup_id'],
            'MesLevel_id' => $data['MesLevel_id'],
            'Diag_id' => $data['Diag_id'],
            'Mes_id' => $data['Mes_id']
        );
        $res = $this->getFirstRowFromQuery($query, $parameters);
        if ( $res !== false ) {
            $data['Mes_Code'] = '';
            $data['Mes_Code'] .= $res['MesProf_Code'];
            $data['Mes_Code'] .= '.';
            $data['Mes_Code'] .= $res['MesAgeGroup_Code'];
            $data['Mes_Code'] .= '.';
            $data['Mes_Code'] .= $res['MesLevel_Code'];
            $data['Mes_Code'] .= '.';
            $Diag_Code = str_replace('.', '', $res['Diag_Code']);
            $data['Mes_Code'] .= $Diag_Code;
            if ( strlen($Diag_Code) <= 3 ) {
                $data['Mes_Code'] .= 'X';
            }
            if (null === $data['Mes_id']) {
                if (null === $res['maxletter']) {
                    $letter = '0';
                } else {
	                $maxletter = base35toInt($res['maxletter']);
	                if ($maxletter === false) {
		                throw new Exception('Неправильный код МЭС в БД');
	                } else {
	                    $letter = int2base35($maxletter + 1);
	                }
                }
            } else {
                $letter = $res['maxletter'];
            }
            if ($letter === false) {
                throw new exception ('Переполнение кода МЭС');
            } else {
                $data['Mes_Code'] .= $letter;
            }
        } else {
            return array(array('success' => false, 'Error_Msg' => 'Не удалось сгенерировать код ' . getMESAlias()));
        }

		$q = "
			declare
				@Mes_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Mes_id = :Mes_id;
			exec dbo." . $procedure . "
				@Mes_id = @Mes_id OUTPUT,
				@Mes_Code = :Mes_Code,
				@Mes_KoikoDni = :Mes_KoikoDni,
				@Mes_VizitNumber = :Mes_VizitNumber,
				@Mes_begDT = :Mes_begDT,
				@Mes_endDT = :Mes_endDT,
				@MesAgeGroup_id = :MesAgeGroup_id,
				@MesLevel_id = :MesLevel_id,
				@MesProf_id = :MesProf_id,
				@Diag_id = :Diag_id,
				@Lpu_id = :Lpu_id,
				@Mes_HStac = :Mes_HStac,
				@Region_id = :Region_id,
				@LpuSectionProfile_id = :LpuSectionProfile_id,
				@MesOperType_id = :MesOperType_id,
				@MedicalCareKind_id = :MedicalCareKind_id,
				@PayMedType_id = :PayMedType_id,
				@Mes_IsModern = :Mes_IsModern,
				@Mes_KoikoDniMin = :Mes_KoikoDniMin,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Mes_id as Mes_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg, :Mes_Code as Mes_Code;
		";
		$p = array(
			'Mes_id' => $data['Mes_id'],
			'Mes_Code' => $data['Mes_Code'],
			'Mes_KoikoDni' => $data['Mes_KoikoDni'],
			'Mes_VizitNumber' => $data['Mes_VizitNumber'],
			'Mes_begDT' => $data['Mes_begDT'],
			'Mes_endDT' => $data['Mes_endDT'],
			'MesAgeGroup_id' => $data['MesAgeGroup_id'],
			'MesLevel_id' => $data['MesLevel_id'],
			'MesProf_id' => $data['MesProf_id'],
			'Diag_id' => $data['Diag_id'],
			'Lpu_id' => $data['Lpu_id'],
			'Mes_HStac' => $data['Mes_HStac'],
			'Region_id' => $data['Region_id'],
			'LpuSectionProfile_id' => $data['LpuSectionProfile_id'],
			'MesOperType_id' => $data['MesOperType_id'],
			'MedicalCareKind_id' => $data['MedicalCareKind_id'],
			'PayMedType_id' => $data['PayMedType_id'],
			'Mes_IsModern' => $data['Mes_IsModern'],
			'Mes_KoikoDniMin' => $data['Mes_KoikoDniMin'],
			'pmUser_id' => $data['pmUser_id'],
		);
		$r = $this->db->query($q, $p);
		if ( is_object($r) ) {
			$result = $r->result('array');
		}
		else {
			log_message('error', var_export(array('q' => $q, 'p' => $p, 'e' => sqlsrv_errors()), true));
			$result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}
		return $result;
	}


	/**
	 * Метод для получения списка кодов старых МЭС
	 */
	function loadKsgCombo($data) {
		$filterList = array(
			'MO.MesType_id in (2,3,5,9,10)',
			'YEAR(MO.Mes_begDT) <= :begYear'
		);
		$queryParams = array();

		if ( !empty($data['year']) ) {
			$filterList[] = '(MO.Mes_endDT is null or YEAR(MO.Mes_endDT) >= :endYear)';
			$queryParams['begYear'] = $data['year'];
			$queryParams['endYear'] = $data['year'];
		}
		else {
			$queryParams['begYear'] = 2014;
		}

		$sql = "
			select
				MO.Mes_id as Ksg_id,
				MO.Mes_Code as Ksg_Code,
				MO.MesOld_Num as MesOld_Num,
				MO.Mes_Name as Ksg_Name,
				MT.MesType_Name as MesType_Name
			from
				v_MesOld MO with (nolock)
				left join v_MesType MT with (nolock) on MT.MesType_id = MO.MesType_id
			where
				" . implode(" and ", $filterList) . "
			order by
				MO.Mes_Code
		";
		//echo getDebugSql($sql, $queryParams);exit;
		$result = $this->db->query($sql, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Метод для получения списка кодов КПГ
	 */
	function loadKpgCombo($data) {
		$filterList = array(
			'MO.MesType_id = 4',
			'YEAR(MO.Mes_begDT) <= :begYear'
		);
		$queryParams = array();

		if ( !empty($data['year']) ) {
			$filterList[] = '(MO.Mes_endDT is null or YEAR(MO.Mes_endDT) >= :endYear)';
			$queryParams['begYear'] = $data['year'];
			$queryParams['endYear'] = $data['year'];
		}
		else {
			$queryParams['begYear'] = 2014;
		}

		$sql = "
			select
				MO.Mes_id as Kpg_id,
				MO.Mes_Code as Kpg_Code,
				MO.Mes_Name as Kpg_Name,
				MT.MesType_Name as MesType_Name
			from
				v_MesOld MO with (nolock)
				left join v_MesType MT with (nolock) on MT.MesType_id = MO.MesType_id
			where
				" . implode(" and ", $filterList) . "
			order by
				MO.Mes_Code
		";
		//echo getDebugSql($sql, $queryParams);exit;
		$result = $this->db->query($sql, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
}