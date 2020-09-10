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
class MesOld_model extends swPgModel
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
					Mes_Code ilike :queryCode
					or cast(MesOld_Num as varchar) ilike :queryCode
					or Mes_Name ilike :queryName
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
				$filters .= " and date_part('year', MO.Mes_begDT) >= :startYear";
				$queryParams['startYear'] = $data['startYear'];
			}
		}

		if (!empty($data['groupByCode'])) {
			$query = "
				select
					MIN(MO.Mes_id) as \"Mes_id\",
					MO.Mes_Code as \"Mes_Code\",
					MO.MesOld_Num as \"MesOld_Num\",
					MO.Mes_Name as \"Mes_Name\",
					MIN(MT.MesType_Code) as \"MesType_Code\",
					coalesce(to_char(MO.Mes_begDT, 'dd.mm.yyyy'),'') as \"Mes_begDT\",
					CASE WHEN MAX(CASE WHEN MO.Mes_endDT IS NULL THEN 1 ELSE 0 END) = 0 THEN coalesce(to_char(MAX(MO.Mes_endDT), 'dd.mm.yyyy'),'') ELSE '' end as \"Mes_endDT\",
					MIN(MT.MesType_Name) as \"MesType_Name\"
				from
					v_MesOld MO
					left join v_MesType MT on MO.MesType_id = MT.MesType_id
				where
					{$filters}
				group by
					mo.Mes_Code, mo.MesOld_Num, mo.Mes_Name, mo.Mes_begDT
				order by
					MIN(mo.Mes_id)
				limit 100
			";
		} else {
			$query = "
				select
					MO.Mes_id as \"Mes_id\",
					MO.Mes_Code as \"Mes_Code\",
					MO.MesOld_Num as \"MesOld_Num\",
					MO.Mes_Name as \"Mes_Name\",
					MT.MesType_Code as \"MesType_Code\",
					coalesce(to_char(MO.Mes_begDT, 'dd.mm.yyyy'),'') as \"Mes_begDT\",
					coalesce(to_char(MO.Mes_endDT, 'dd.mm.yyyy'),'') as \"Mes_endDT\",
					MT.MesType_Name as \"MesType_Name\"
				from
					v_MesOld MO
					left join v_MesType MT on MO.MesType_id = MT.MesType_id
				where
					{$filters}
				order by
					MO.Mes_begDT DESC
				limit 100
			";
		}

		$result = $this->db->query($query, $queryParams);
		if (is_object($result)) {
			return $result->result('array');
		}

		return false;
	}

    /**
     * @param $data
     * @return bool
     *
     */
    function loadMesOld($data)
	{
		$sql = "
			SELECT
				Mes_id as \"Mes_id\",
				Mes_Code as \"Mes_Code\",
				MesProf_id as \"MesProf_id\",
				MesAgeGroup_id as \"MesAgeGroup_id\",
				MesLevel_id as \"MesLevel_id\",
				Diag_id as \"Diag_id\",
				Mes_KoikoDniMin as \"Mes_KoikoDniMin\",
				Mes_VizitNumber as \"Mes_VizitNumber\",
				Mes_KoikoDni as \"Mes_KoikoDni\",
				to_char(Mes_begDT, 'dd.mm.yyyy') as \"Mes_begDT\",
				to_char(Mes_endDT, 'dd.mm.yyyy') as \"Mes_endDT\",
				MedicalCareKind_id as \"MedicalCareKind_id\"
			FROM
				v_MesOld
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
	function loadMesOldComboSearchList($data)
	{
		$filter = " ( 1 = 1 ) ";
		$queryParams = array();

		if (!isset($data['Diag_Name'])) return false;
		
		$sql = "
			select
			-- select
				ms.Mes_id as \"Mes_id\",
				ms.Mes_Code as \"Mes_Code\",
				dg.Diag_Name as \"Diag_Name\"
			-- end select
			from
			-- from
				MesOld ms
				left join Diag dg on dg.Diag_id = ms.Diag_id
			-- end from
			where
			-- where
				Diag_Name ilike '%".$data['Diag_Name']."%'
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
			$filter .= " and Mes_Code ilike '".$data['query']."%'";
		}
		
		
		
		$sql = "
			select
				ms.Mes_id as \"Mes_id\",
				ms.Mes_Code as \"Mes_Code\"
			from
				MesOld ms
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
			$filter .= " and ms.Mes_begDT >= cast(:Mes_begDT_Range_0 as timestamp)";
			$queryParams['Mes_begDT_Range_0'] = $data['Mes_begDT_Range'][0];
		}

		if ( isset($data['Mes_begDT_Range'][1]) ) {
			$filter .= " and ms.Mes_begDT <= cast(:Mes_begDT_Range_1 as timestamp)";
			$queryParams['Mes_begDT_Range_1'] = $data['Mes_begDT_Range'][1];
		}
		
		if ( isset($data['Mes_endDT_Range'][0]) ) {
			$filter .= " and ms.Mes_endDT >= cast(:Mes_endDT_Range_0 as timestamp)";
			$queryParams['Mes_endDT_Range_0'] = $data['Mes_endDT_Range'][0];
		}

		if ( isset($data['Mes_endDT_Range'][1]) ) {
			$filter .= " and ms.Mes_endDT <= cast(:Mes_endDT_Range_1 as timestamp)";
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
				ms.Mes_id as \"Mes_id\",
				ms.Mes_Code as \"Mes_Code\",
				case when mp.MesProf_id is null then '' else cast(mp.MesProf_Code as varchar) || '. ' || mp.MesProf_Name end as \"MesProf_CodeName\",
				case when mag.MesAgeGroup_id is null then '' else cast(mag.MesAgeGroup_Code as varchar) || '. ' || mag.MesAgeGroup_Name end as \"MesAgeGroup_CodeName\",
				case when ml.MesLevel_id is null then '' else cast(ml.MesLevel_Code as varchar) end as \"MesLevel_CodeName\",
				case when dg.Diag_id is null then '' else cast(dg.Diag_Code as varchar) || '. ' || dg.Diag_Name end as \"Diag_CodeName\",
				ms.Mes_KoikoDni as \"Mes_KoikoDni\",
				ms.Mes_VizitNumber as \"Mes_VizitNumber\",
				ms.Mes_KoikoDniMin as \"Mes_KoikoDniMin\",
				to_char(cast(ms.Mes_begDT as timestamp), 'dd.mm.yyyy') as \"Mes_begDT\",
				to_char(cast(ms.Mes_endDT as timestamp), 'dd.mm.yyyy') as \"Mes_endDT\",
				case when ms.Mes_endDT is not null and ms.Mes_endDT <= dbo.tzGetDate()
					then 3
					else case when ms.Mes_begDT > dbo.tzGetDate() and ms.Mes_endDT is null
						then 4 else 1
					end
				end as \"MesStatus\"
			-- end select
			from
			-- from
				v_MesOld ms
				left join MesProf mp on mp.MesProf_id = ms.MesProf_id
				left join MesAgeGroup mag on mag.MesAgeGroup_id = ms.MesAgeGroup_id
				left join MesLevel ml on ml.MesLevel_id = ms.MesLevel_id
				left join Diag dg on dg.Diag_id = ms.Diag_id
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
        $query = "
				select
					mp.MesProf_Code as \"MesProf_Code\",
					mag.MesAgeGroup_Code as \"MesAgeGroup_Code\",
					ml.MesLevel_Code as \"MesLevel_Code\",
					dg.Diag_Code as \"Diag_Code\",
					mes1.maxletter as \"maxletter\"
				from
					MesProf mp,
					MesAgeGroup mag,
					MesLevel ml,
					Diag dg
					left join lateral(
						select
							MAX(SUBSTRING(Mes_Code, length(Mes_Code), 1)) AS maxletter
						from
							MesOld
						where
							MesProf_id = :MesProf_id and
							MesAgeGroup_id = :MesAgeGroup_id and
							MesLevel_id = :MesLevel_id and
							Diag_id = :Diag_id and
							(Mes_id = :Mes_id or (:Mes_id is null))
					) as mes1 on true
				where
					mp.MesProf_id = :MesProf_id and
					mag.MesAgeGroup_id = :MesAgeGroup_id and
					ml.MesLevel_id = :MesLevel_id and
					dg.Diag_id = :Diag_id
				";
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
			select
				Mes_id as \"Mes_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from dbo." . $procedure . "(
				Mes_id := :Mes_id,
				Mes_Code := :Mes_Code,
				Mes_KoikoDni := :Mes_KoikoDni,
				Mes_VizitNumber := :Mes_VizitNumber,
				Mes_begDT := :Mes_begDT,
				Mes_endDT := :Mes_endDT,
				MesAgeGroup_id := :MesAgeGroup_id,
				MesLevel_id := :MesLevel_id,
				MesProf_id := :MesProf_id,
				Diag_id := :Diag_id,
				Lpu_id := :Lpu_id,
				Mes_HStac := :Mes_HStac,
				Region_id := :Region_id,
				LpuSectionProfile_id := :LpuSectionProfile_id,
				MesOperType_id := :MesOperType_id,
				MedicalCareKind_id := :MedicalCareKind_id,
				PayMedType_id := :PayMedType_id,
				Mes_IsModern := :Mes_IsModern,
				Mes_KoikoDniMin := :Mes_KoikoDniMin,
				pmUser_id := :pmUser_id
			)
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
			'date_part(\'year\', MO.Mes_begDT) <= :begYear'
		);
		$queryParams = array();

		if ( !empty($data['year']) ) {
			$filterList[] = '(MO.Mes_endDT is null or date_part(\'year\', MO.Mes_endDT) >= :endYear)';
			$queryParams['begYear'] = $data['year'];
			$queryParams['endYear'] = $data['year'];
		}
		else {
			$queryParams['begYear'] = 2014;
		}

		$sql = "
			select
				MO.Mes_id as \"Ksg_id\",
				MO.Mes_Code as \"Ksg_Code\",
				MO.MesOld_Num as \"MesOld_Num\",
				MO.Mes_Name as \"Ksg_Name\",
				MT.MesType_Name as \"MesType_Name\"
			from
				v_MesOld MO
				left join v_MesType MT on MT.MesType_id = MO.MesType_id
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
			'date_part(\'year\', MO.Mes_begDT) <= :begYear'
		);
		$queryParams = array();

		if ( !empty($data['year']) ) {
			$filterList[] = '(MO.Mes_endDT is null or date_part(\'year\', MO.Mes_begDT) >= :endYear)';
			$queryParams['begYear'] = $data['year'];
			$queryParams['endYear'] = $data['year'];
		}
		else {
			$queryParams['begYear'] = 2014;
		}

		$sql = "
			select
				MO.Mes_id as \"Kpg_id\",
				MO.Mes_Code as \"Kpg_Code\",
				MO.Mes_Name as \"Kpg_Name\",
				MT.MesType_Name as \"MesType_Name\"
			from
				v_MesOld MO
				left join v_MesType MT on MT.MesType_id = MO.MesType_id
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