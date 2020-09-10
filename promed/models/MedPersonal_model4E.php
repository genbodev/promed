<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb
 *
 * The New Generation of Medical Statistic Software
 *
 * @package				Common
 * @copyright			Copyright (c) 2009 Swan Ltd.
 * @author				Petukhov Ivan aka Lich (megatherion@list.ru)
 *						Pshenitcyn Ivan aka IvP (ipshon@rambler.ru)
 *						Bykov Stas aka Savage (savage@swan.perm.ru)
 * @link				http://swan.perm.ru/PromedWeb
 * @version				16.07.2009
 */

/**
 * Класс модели для работы с медицинским персоналом
 *
 * @package		Common
 * @author		Petukhov Ivan aka Lich (megatherion@list.ru)
 *				Pshenitcyn Ivan aka IvP (ipshon@rambler.ru)
 */
class MedPersonal_model4E extends CI_Model {

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Получение каких-то данных по врачу, где-то используется. Автор в курсе
	 */
	function getMedPersonInfo($data) 
	{
		$params = array(
			$data['MedStaffFact_id']
		);
		$sql = "
			select top 1
				MSF.MedPersonal_id,
				--MSF.MedSpec_id,
				--MS.MedSpec_Name,
				MP.Dolgnost_Name as Dolgnost_Name,
				--MP.Dolgnost_id,
				--MP.Person_Fio,
				LS.LpuSection_id,
				--LS.LpuSection_Name,
				LS.LpuSectionProfile_id,
				LS.LpuSectionProfile_Name,
				MSF.LpuUnit_id
			from 
				v_MedStaffFact MSF with (NOLOCK)
				left join v_MedPersonal MP with (NOLOCK) on MSF.MedPersonal_id = MP.MedPersonal_id AND MSF.Lpu_id = MP.Lpu_id
				left join v_LpuSection LS with (NOLOCK) on LS.LpuSection_id = MSF.LpuSection_id
				--left join v_MedSpec MS with (NOLOCK) on MSF.MedSpec_id = MS.MedSpec_id
			where
				MSF.MedStaffFact_id = ?
		";
		//echo getDebugSQL($sql, $params);
		$result = $this->db->query($sql, $params);
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
	 * Получение по идентификатору врача данных по его специальности по справочнику ОМС
	 */
	function getMedStaffFactMedSpecOmsInfo($MedStaffFact_id) 
	{
		
		$sql = "
			select top 1
				mso.MedSpecOms_Code
			from 
				v_MedStaffFact msf with (NOLOCK)
				left join v_MedSpecOms mso with (NOLOCK) on msf.MedSpecOms_id = mso.MedSpecOms_id
			where
				MSF.MedStaffFact_id = :MedStaffFact_id
		";
		//echo getDebugSQL($sql, $data);
		$result = $this->db->query($sql, array('MedStaffFact_id' => $MedStaffFact_id));
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
	 * Получение данных по врачу для регистратуры
	 */
	function getMedPersonInfoForReg($data) 
	{
		$params = array(
			'MedStaffFact_id' => $data['MedStaffFact_id']
		);
		$sql = "
			select TOP 1
				msf.MedstaffFact_id,
				MedPersonal_FIO,
				ls.LpuSectionProfile_Name,
				ls.LpuSectionProfile_Code,
				lr.LpuRegion_Name,
				ls.LpuSectionProfile_id,
				MedStaffFact_IsQueueOnFree,
				RecType_id,
				lu.LpuUnit_Name,
				a.Address_Address,
				l.Lpu_Nick as Lpu_Nick,
				lu.LpuUnit_id,
				msf.Lpu_id
			from v_MedstaffFact_ER msf with(nolock)
			left join v_MedStaffRegion msr with(nolock) on msr.MedPersonal_id = msf.MedPersonal_id
			left outer join v_LpuRegion lr with(nolock) on msr.LpuRegion_Id = lr.LpuRegion_Id
			left outer join v_LpuSection_ER ls with(nolock) on msf.LpuSection_Id = ls.LpuSection_Id
			left join v_LpuUnit_ER lu with(nolock) on lu.LpuUnit_id = msf.LpuUnit_id
			left outer join Address a with(nolock) on lu.Address_id = a.Address_id
			left join v_Lpu l with(nolock) on l.lpu_id = lu.lpu_id
			where 
				msf.MedStaffFact_id = :MedStaffFact_id
		";
		$result = $this->db->query($sql, $params);
		if (is_object($result))
		{
			$res = $result->result('array');
			return $res[0];
		}
		else
		{
			return false;
		}
	}

	/**
	 * Поиск медперсонала по ФИО и ДР
	 */
	function searchDoctorByFioBirthday($data) 
	{
		$params = array(
			$data['Person_BirthDay'],
			$data['Person_SurName'],
			$data['Person_FirName'],
			$data['Lpu_id']
		);
		if (empty($data['Person_SecName']))
		{
			$filter = 'and Person_SecName is null';
		}
		else
		{
			$params[] = $data['Person_SecName'];
			$filter = 'and Person_SecName = ?';
		}
		$sql = "
			select top 1
				MedPersonal_id
			from
				MedPersonalCache with (nolock)
			where
				convert(varchar(10),Person_BirthDay,104) = ?
				and Person_SurName = ?
				and Person_FirName = ?
				and Lpu_id = ?
				{$filter}
		";
		$result = $this->db->query($sql, $params);
		if (is_object($result))
		{
			$response = $result->result('array');
			if (count($response) > 0) 
			{
				$response[0]['found'] = true;
			}
			else
			{
				$response[0]['found'] = false;
			}
			return $response;
			
		}
		else
		{
			return false;
		}
	}

	/**
	 * Загрузка данных формы редактирования медперсонала
	 */
	function loadMedPersonal($data)
	{
		$fromtable = "v_MedPersonal ";
		if ($data['session']['region']['nick'] == 'ufa') $fromtable = "v_MedPersonal_old ";
		$sql = "
			select
				MedPersonal_id,
				MedPersonal_Code,
				MedPersonal_TabCode,
				Person_SurName,
				Person_FirName,
				Person_SecName,
				convert(varchar,cast(Person_BirthDay as datetime),104) as Person_BirthDay,
				convert(varchar,cast(WorkData_begDate as datetime),104) as WorkData_begDate,
				convert(varchar,cast(WorkData_endDate as datetime),104) as WorkData_endDate,
				WorkData_IsDlo,
				Person_Snils
			from
				--MedPersonalCache
				--v_MedPersonal
				".$fromtable." with(nolock)
			where
				Lpu_id = ? and
				MedPersonal_id = ?
		";
        $result = $this->db->query($sql, array($data['Lpu_id'], $data['MedPersonal_id']));

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
	 * Сохранение медперсонала
	 */
	function saveMedPersonal($data) 
	{
		
		$set_mp_id = "";
		
		//проверка табельного кода на уникальность
		$query = "
			select
				count(MedPersonal_id) cnt
			from
				MedPersonalCache
			where
				MedPersonal_TabCode = :MedPersonal_TabCode and
				Lpu_id = :Lpu_id and
				MedPersonal_id <> :MedPersonal_id
		";
		
		$result = $this->db->query($query, array(
			'MedPersonal_TabCode' => $data['MedPersonal_TabCode'],
			'Lpu_id' => $data['Lpu_id'],
			'MedPersonal_id' => $data['MedPersonal_id']
		));
		
		if (is_object($result)) {
			$res = $result->result('array');			
			if ($res[0]['cnt'] > 0) 
				return array(array('success' => false, 'Error_Msg' => 'Данный табельный номер уже используется')); 
		}
		
		if ( isset($data['action']) && $data['action'] == 'edit' )
		{
			$proc = 'p_MedPersonalCache_upd';
		}
		if ( isset($data['action']) && $data['action'] == 'add' )
		{
			$proc = 'p_MedPersonalCache_ins';
			$data['MedPersonal_id'] = NULL;
		}
		$query = "
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000),
				@MedPersonal_id bigint = ?;
			exec " .$proc. "
				@MedPersonal_id = @MedPersonal_id output,
				@MedPersonal_Code = ?,
				@MedPersonal_TabCode = ?,
				@Person_SurName = ?,
				@Person_FirName = ?,
				@Person_SecName = ?,
				@Person_BirthDay = ?,
				@Lpu_id  = ?,
				@WorkData_begDate = ?,
				@WorkData_endDate = ?,
				@WorkData_IsDlo = ?,
				@Person_Snils = ?,
				@pmUser_id = ?,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
			select @MedPersonal_id as MedPersonal_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		$result = $this->db->query($query, array(
			$data['MedPersonal_id'],
			$data['MedPersonal_Code'],
			$data['MedPersonal_TabCode'],
			strtoupper($data['Person_SurName']),
			strtoupper($data['Person_FirName']),
			!empty($data['Person_SecName']) ? strtoupper($data['Person_SecName']) : '- - -',
			$data['Person_BirthDay'],
			$data['Lpu_id'],
			$data['WorkData_begDate'],
			$data['WorkData_endDate'],
			$data['WorkData_IsDlo'],
			$data['Person_Snils'],
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
	 * Получение списка медперсонала
	 * Используется: окно поиска мед. персонала.
	 */
	public function loadMedPersonalSearchList($data) {
		$filter = " ( 1 = 1 ) ";
		$filter .= " and Lpu_id = :Lpu_id ";
		$queryParams = array();
		$queryParams['start'] = $data['start'];
		$queryParams['limit'] = $data['limit'];
		$queryParams['Lpu_id'] = $data['Lpu_id'];
		
		if ( isset($data['Person_SurName']) )
		{
			$filter .= "
			and Person_SurName like :Person_SurName 
			";
			$queryParams['Person_SurName'] = $data['Person_SurName']."%";
		}
		
		if ( isset($data['Person_FirName']) )
		{
			$filter .= "
			and Person_FirName like :Person_FirName 
			";
			$queryParams['Person_FirName'] = $data['Person_FirName']."%";
		}
		
		if ( isset($data['Person_SecName']) )
		{
			$filter .= "
			and Person_SecName like :Person_SecName 
			";
			$queryParams['Person_SecName'] = $data['Person_SecName']."%";
		}
			
		$sql = "
			select
			-- select
				MedPersonal_id,
				MedPersonal_Code,
				MedPersonal_TabCode,
				Person_SurName,
				Person_FirName,
				Person_SecName,
				convert(varchar,cast(Person_BirthDay as datetime),104) as Person_BirthDay,
				convert(varchar,cast(WorkData_begDate as datetime),104) as WorkData_begDate,
				convert(varchar,cast(WorkData_endDate as datetime),104) as WorkData_endDate,
				case when isnull(WorkData_IsDlo, 1) = 1 then 'false' else 'true' end as WorkData_IsDlo,
				Person_Snils
			-- end select
			from
			-- from
				v_MedPersonal with(nolock)
			-- end from
			where
			-- where
			" . $filter . "
			-- end where
			order by
			-- order by
				Person_SurName
			-- end order by
		";
		
		// замена функции преобразования запроса в запрос для получения количества записей
		$count_sql = getCountSQLPH($sql);
		if ( isset($data['start']) && $data['start'] >= 0 && isset($data['limit']) && $data['limit'] >= 0 )
		{
			$sql = getLimitSQLPH($sql, $data['start'], $data['limit']);
		}
		
		$result = $this->db->query($sql, $queryParams);
			
		if ( is_object($result) )
		{
			$res = $result->result('array');

			if ( is_array($res) )
			{
				if ( $data['start'] == 0 && count($res) < 100 )
				{
					$response['data'] = $res;
					$response['totalCount'] = count($res);
				}
				else
				{
					$response['data'] = $res;

					$get_count_query = getCountSQLPH($count_sql);
					$get_count_result = $this->db->query($get_count_query, $queryParams);

					if ( is_object($get_count_result) )
					{
						$response['totalCount'] = $get_count_result->result('array');
						$response['totalCount'] = $response['totalCount'][0]['cnt'];
					}
					else
					{
						return false;
					}
				}
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
		
		return $response;
	 }

	/**
	 * Список врачей для Уфы из старого ЕРМП
	 */
	public function loadMedPersonalSearchList_Ufa_Old_ERMP($data) {
		$filter = " ( 1 = 1 ) ";
		$filter .= " and Lpu_id = :Lpu_id ";
		$queryParams = array();
		$queryParams['start'] = $data['start'];
		$queryParams['limit'] = $data['limit'];
		$queryParams['Lpu_id'] = $data['Lpu_id'];

		if ( isset($data['Person_SurName']) )
		{
			$filter .= "
			and Person_SurName like :Person_SurName
			";
			$queryParams['Person_SurName'] = rtrim($data['Person_SurName'])."%";
		}

		if ( isset($data['Person_FirName']) )
		{
			$filter .= "
			and Person_FirName like :Person_FirName
			";
			$queryParams['Person_FirName'] = rtrim($data['Person_FirName'])."%";
		}

		if ( isset($data['Person_SecName']) )
		{
			$filter .= "
			and Person_SecName like :Person_SecName
			";
			$queryParams['Person_SecName'] = rtrim($data['Person_SecName'])."%";
		}

		$sql = "
			select
			-- select
				MedPersonal_id,
				MedPersonal_Code,
				MedPersonal_TabCode,
				Person_SurName,
				Person_FirName,
				Person_SecName,
				convert(varchar,cast(Person_BirthDay as datetime),104) as Person_BirthDay,
				convert(varchar,cast(WorkData_begDate as datetime),104) as WorkData_begDate,
				convert(varchar,cast(WorkData_endDate as datetime),104) as WorkData_endDate,
				case when isnull(WorkData_IsDlo, 1) = 1 then 'false' else 'true' end as WorkData_IsDlo,
				Person_Snils
			-- end select
			from
			-- from
				v_MedPersonal_old with(nolock)
			-- end from
			where
			-- where
			" . $filter . "
			-- end where
			order by
			-- order by
				Person_SurName
			-- end order by
		";

		// замена функции преобразования запроса в запрос для получения количества записей
		$count_sql = getCountSQLPH($sql);
		if ( isset($data['start']) && $data['start'] >= 0 && isset($data['limit']) && $data['limit'] >= 0 )
		{
			$sql = getLimitSQLPH($sql, $data['start'], $data['limit']);
		}

		$result = $this->db->query($sql, $queryParams);

		if ( is_object($result) )
		{
			$res = $result->result('array');

			if ( is_array($res) )
			{
				if ( $data['start'] == 0 && count($res) < 100 )
				{
					$response['data'] = $res;
					$response['totalCount'] = count($res);
				}
				else
				{
					$response['data'] = $res;

					$get_count_query = getCountSQLPH($count_sql);
					$get_count_result = $this->db->query($get_count_query, $queryParams);

					if ( is_object($get_count_result) )
					{
						$response['totalCount'] = $get_count_result->result('array');
						$response['totalCount'] = $response['totalCount'][0]['cnt'];
					}
					else
					{
						return false;
					}
				}
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}

		return $response;
	}

	/**
	 * Получение списка мест работы врача
	 * Используется: окно просмотра и редактирования мед. персонала.
	 */
	public function getMedStaffFactEditWindow($data) {
		$sql = "
			SELECT
				Lpu_id as Lpu_idEdit,
				LpuUnit_id as LpuUnit_idEdit,
				LpuSection_id as LpuSection_idEdit,
				Post_id as PostMed_idEdit,
				MedStaffFact_Stavka as MedStaffFact_StavkaEdit,
				--MedSpec_id as MedSpec_idEdit,
				PostKind_id as PostMedType_idEdit,
				PostMedClass_id as PostMedClass_idEdit,
				--PostMedCat_id as PostMedCat_idEdit,
				MedStaffFact_IsOMS as MedStaffFact_IsOMSEdit,
				MedSpecOms_id,
				MedStaffFact_IsSpecialist as MedStaffFact_IsSpecialistEdit,
				convert(varchar,cast(WorkData_begDate as datetime),104) as MedStaffFact_setDateEdit,
				convert(varchar,cast(WorkData_endDate as datetime),104) as MedStaffFact_disDateEdit,
				MSF.RecType_id,
				isnull(MSF.MedStaffFact_PriemTime, '') as MedStaffFact_PriemTime,
				MSF.MedStatus_id,
				CASE WHEN isnull(MSF.MedStaffFact_IsDirRec, 1) = 1 THEN '' ELSE 'true' END as MedStaffFact_IsDirRec,
				CASE WHEN isnull(MSF.MedStaffFact_IsQueueOnFree, 1) = 1 THEN '' ELSE 'true' END as MedStaffFact_IsQueueOnFree,
				isnull(MSF.MedStaffFact_Descr, '') as MedStaffFact_Descr,
				isnull(MSF.MedStaffFact_Contacts, '') as MedStaffFact_Contacts
			from v_MedStaffFact MSF with (nolock)
			where MedStaffFact_id = ? and Lpu_id = ?
		";
		if ($data['session']['region']['nick'] == 'ufa')
			$sql = "
			SELECT
				Lpu_id as Lpu_idEdit,
				LpuUnit_id as LpuUnit_idEdit,
				LpuSection_id as LpuSection_idEdit,
				Post_id as PostMed_idEdit,
				MedStaffFact_Stavka as MedStaffFact_StavkaEdit,
				MedSpec_id as MedSpec_idEdit,
				PostKind_id as PostMedType_idEdit,
				PostMedClass_id as PostMedClass_idEdit,
				PostMedCat_id as PostMedCat_idEdit,
				MedStaffFact_IsOMS as MedStaffFact_IsOMSEdit,
				MedSpecOms_id,
				MedStaffFact_IsSpecialist as MedStaffFact_IsSpecialistEdit,
				convert(varchar,cast(WorkData_begDate as datetime),104) as MedStaffFact_setDateEdit,
				convert(varchar,cast(WorkData_endDate as datetime),104) as MedStaffFact_disDateEdit,
				MSF.RecType_id,
				isnull(MSF.MedStaffFact_PriemTime, '') as MedStaffFact_PriemTime,
				MSF.MedStatus_id,
				CASE WHEN isnull(MSF.MedStaffFact_IsDirRec, 1) = 1 THEN '' ELSE 'true' END as MedStaffFact_IsDirRec,
				CASE WHEN isnull(MSF.MedStaffFact_IsQueueOnFree, 1) = 1 THEN '' ELSE 'true' END as MedStaffFact_IsQueueOnFree,
				isnull(MSF.MedStaffFact_Descr, '') as MedStaffFact_Descr,
				isnull(MSF.MedStaffFact_Contacts, '') as MedStaffFact_Contacts
			from v_MedStaffFact_old MSF with (nolock)
			where MedStaffFact_id = ? and Lpu_id = ?
		";
		//end if

		$res = $this->db->query($sql, array($data['MedStaffFact_id'], $data['Lpu_id']));
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	} //end getMedStaffFactEditWindow()


	/**
	 * Получение списка медицинского персонала для SwMedPersonalAllCombo
	 * Используется: комбобокс
	 */
	public function getMedPersonalCombo($data) {
		
		$queryParams = array();
		// Фильтры
		$f = "";
		$where = "WHERE msf.Lpu_id = :Lpu_id";
		if ((isset($data['Org_id'])) and ($data['Org_id']>0)) {
			$queryParams['Org_id'] = $data['Org_id'];
			$where = "WHERE msf.Lpu_id = (select top 1 Lpu_id from v_Lpu with (nolock) where Org_id = :Org_id)";
		}
		elseif ((isset($data['Org_ids'])) and (!empty($data['Org_ids']))) {
			$queryParams['Org_ids'] = json_decode($data['Org_ids']);
			if (count($queryParams['Org_ids']) > 1) {
				$where = "WHERE msf.Lpu_id IN (select Lpu_id from v_Lpu with (nolock) where Org_id IN (".implode(',', $queryParams['Org_ids'])."))";
			} elseif (count($queryParams['Org_ids']) == 1) {
				$queryParams['Org_id'] = $queryParams['Org_ids'][0];
				$where = "WHERE msf.Lpu_id = (select top 1 Lpu_id from v_Lpu with (nolock) where Org_id = :Org_id)";
			} else {
				$where = "WHERE (1=0) ";
			}
		}
		elseif ((isset($data['Lpu_id'])) and ($data['Lpu_id']>0)) {
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}
		elseif (isset($data['session']['lpu_id'])) {
			$queryParams['Lpu_id'] = $data['session']['lpu_id'];
		}
		else {
			$queryParams['Lpu_id'] = 0;
		}
		if ((isset($data['MedPersonal_id'])) and ($data['MedPersonal_id']>0)) {
			$f = " or (msf.MedPersonal_id = :MedPersonal_id )";
			$queryParams['MedPersonal_id'] = $data['MedPersonal_id'];
		}
		if(!empty($data['LpuBuildingType_id'])){
			$where .= " and lpub.LpuBuildingType_id = :LpuBuildingType_id";
			$queryParams['LpuBuildingType_id'] = $data['LpuBuildingType_id'];
		}
		if(!empty($data['LpuUnitType_Code']) && ($data['session']['region']['nick'] == 'kareliya')){
			$implLpuBuildingType = '';
			if($data['LpuUnitType_Code'] == 12){
				$implLpuBuildingType = 'or lpub.LpuBuildingType_id = 27';
			}
			$where .= " and (LUT.LpuUnitType_Code = :LpuUnitType_Code {$implLpuBuildingType})";
			$queryParams['LpuUnitType_Code'] = $data['LpuUnitType_Code'];
		}

		//		$sql = "
		//			SELECT DISTINCT	
		//				MP.MedPersonal_id,
		//				isnull(MP.MedPersonal_TabCode, '') as MedPersonal_Code,
		//				ltrim(rtrim(MP.Person_SurName)) + ' ' + ltrim(rtrim(MP.Person_FirName)) + ' ' + ltrim(rtrim(isnull(MP.Person_SecName,''))) as MedPersonal_Fio,
		//				convert(varchar,cast(MP.WorkData_begDate as datetime),104) as WorkData_begDate,
		//				convert(varchar,cast(MP.WorkData_endDate as datetime),104) as WorkData_endDate
		//			FROM v_MedPersonal MP
		//			cross apply (
		//				select distinct MedPersonal_id, MedPersonal_Code
		//				from v_MedStaffFact with (nolock)
		//				where (WorkData_endDate is null or WorkData_endDate > dbo.tzGetDate() {$f}) 
		//					and	Lpu_id = MP.Lpu_id 
		//					and MedPersonal_id = MP.MedPersonal_id
		//			) MSF
		//			{$where}
		//			ORDER BY MedPersonal_FIO, MedPersonal_Code
		//		";
				//print $sql;
		
		$sql = "
			SELECT
				msf.MedPersonal_id,
				msf.MedStaffFact_id,
				isnull(msf.MedPersonal_TabCode, '') as MedPersonal_Code,
				rtrim(msf.Person_FIO) as MedPersonal_Fio,
				ps.PostMed_Name as PostMed_Name,
				convert(varchar,cast(WorkData_begDate as datetime),104) as WorkData_begDate,
				convert(varchar,cast(WorkData_endDate as datetime),104) as WorkData_endDate,
				isnull(msf.MedStaffFact_Stavka, '') as MedStaffFact_Stavka,
				lpub.LpuBuilding_Name,
				LUT.LpuUnitType_Code

			FROM v_MedStaffFact msf with (nolock)
			LEFT JOIN v_PostMed ps with (nolock) on ps.PostMed_id=msf.Post_id
			LEFT JOIN v_LpuBuilding lpub with (nolock) on msf.LpuBuilding_id=lpub.LpuBuilding_id
			LEFT join v_LpuSection LS with (nolock) on LS.LpuSection_id = msf.LpuSection_id
			LEFT join v_LpuUnit LU WITH (NOLOCK) on LU.LpuUnit_id = msf.LpuUnit_id
			LEFT join v_LpuUnitType LUT WITH (NOLOCK) on LUT.LpuUnitType_id = LU.LpuUnitType_id
			{$where}
			AND (msf.WorkData_endDate is null or msf.WorkData_endDate > dbo.tzGetDate())
			ORDER BY MedPersonal_Fio
		";
		//		echo getDebugSql($sql, $queryParams);
		//		exit;
		$res = $this->db->query($sql, $queryParams);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	} //end getMedPersonalCombo()

	
	/**
	 * Получение списка медицинского персонала (только участковых врачей)
	 * Используется: комбобокс
	 */
	public function getMedPersonalWithLpuRegionCombo($data) {
		
		$queryParams = array();
		// Фильтры
		$f = "";
		if ((isset($data['Lpu_id'])) and ($data['Lpu_id']>0)) {
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}
		elseif (isset($data['session']['lpu_id'])) {
			$queryParams['Lpu_id'] = $data['session']['lpu_id'];
		}
		else {
			$queryParams['Lpu_id'] = 0;
		}
		if ((isset($data['MedPersonal_id'])) and ($data['MedPersonal_id']>0)) {
			$f = " or (MedPersonal_id = :MedPersonal_id )";
			$queryParams['MedPersonal_id'] = $data['MedPersonal_id'];
		}
		
		if ( isset($data['LpuRegion_id']) ) {
			$f = " and (LpuRegion_id = :LpuRegion_id )";
			$queryParams['LpuRegion_id'] = $data['LpuRegion_id'];
		}

		$sql = "
			SELECT
				MedPersonal_id,
				isnull(MedPersonal_TabCode, '') as MedPersonal_Code,
				ltrim(rtrim(Person_SurName)) + ' ' + ltrim(rtrim(Person_FirName)) + ' ' + ltrim(rtrim(Person_SecName)) as MedPersonal_FIO,
				convert(varchar,cast(WorkData_begDate as datetime),104) as WorkData_begDate,
				convert(varchar,cast(WorkData_endDate as datetime),104) as WorkData_endDate,
				MedStaffRegion.LpuRegion_id
			FROM v_MedPersonal MP with(nolock)
			cross apply
			(
				select top 1 LpuRegion_id from v_MedStaffRegion with(nolock) where MedPersonal_id = MP.MedPersonal_Id
			) as MedStaffRegion
			WHERE ( (WorkData_endDate is null or WorkData_endDate > dbo.tzGetDate()) {$f})
				and (Lpu_id = :Lpu_id)
			ORDER BY MedPersonal_FIO, MedPersonal_TabCode
		";
		/*
		echo getDebugSql($sql, $queryParams);
		exit;
		*/

		$res = $this->db->query($sql, $queryParams);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	} //end getMedPersonalCombo()

	/**
	 * Получение списка медицинского персонала. Для гридов и комбобоксов
	 * Используется: окно просмотра и редактирования мед. персонала
	 */
	public function getMedPersonalGrid($data) {
		$fromtable = "v_MedPersonal ";
		if ($data['session']['region']['nick'] == 'ufa') $fromtable = "v_MedPersonal_old ";
		$sql = "
			SELECT
				MedPersonal_id,
				isnull(MedPersonal_TabCode, '') as MedPersonal_TabCode,
				isnull(MedPersonal_Code, '') as MedPersonal_Code,
				ltrim(rtrim(Person_SurName)) + ' ' + ltrim(rtrim(Person_FirName)) + ' ' + ltrim(rtrim(Person_SecName)) as MedPersonal_FIO,
				CASE WHEN WorkData_IsDlo = 1 THEN 'false' ELSE 'true' END as MedPersonal_IsDlo
			FROM ".$fromtable." with(nolock)
			WHERE Lpu_id = ?
			ORDER BY MedPersonal_FIO, MedPersonal_TabCode
		";
		$res = $this->db->query($sql, array($data['session']['lpu_id']));
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	} //end getMedPersonalGrid()
	
	/**
	 * Получение списка медицинского персонала.
	 * Используется: списки мед персонала ЛПУ
	 */
	public function getMedPersonalList($data) {
		$dop_where = '';
		if ( isset($data['view_one_doctor']) AND isset($data['session']['medpersonal_id']) )
		{
			$dop_where = 'AND MedPersonal_id = ' . $data['session']['medpersonal_id'];
		}
		$sql = "
			SELECT
				MedPersonal_id,
				isnull(MedPersonal_TabCode, '') as MedPersonal_TabCode,
				isnull(MedPersonal_Code, '') as MedPersonal_Code,
				ltrim(rtrim(Person_SurName)) + ' ' + ltrim(rtrim(Person_FirName)) + ' ' + ltrim(rtrim(Person_SecName)) as MedPersonal_FIO,
				CASE WHEN WorkData_IsDlo = 1 THEN 'false' ELSE 'true' END as MedPersonal_IsDlo
			FROM v_MedPersonal with(nolock)
			WHERE (WorkData_endDate is null or WorkData_endDate > dbo.tzGetDate())
				and Lpu_id = ? {$dop_where}
			ORDER BY MedPersonal_FIO
		";
		$res = $this->db->query($sql, array($data['Lpu_id']));
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	} //end getMedPersonalGrid()

	/**
	 * Получение детальной информации о местах работы врача
	 * Используется: окно просмотра и редактирования мед. персонала
	 */
	public function getMedPersonalGridDetail($data) {
		$filters = array();
		$queryParams = array();
		if (!empty($data['Lpu_id'])) {
			$queryParams['Lpu_id'] = $data['Lpu_id'];
			$filters[] = "msf.Lpu_id = :Lpu_id";
		}

		if (isset($data['LpuUnit_id']) and ($data['LpuUnit_id']>0)) {
			$queryParams['LpuUnit_id'] = $data['LpuUnit_id'];
			$filters[] = "msf.LpuUnit_id = :LpuUnit_id";
			
		}
		if (isset($data['LpuSection_id']) and ($data['LpuSection_id']>0)) {
			$queryParams['LpuSection_id'] = $data['LpuSection_id'];
			$filters[] = "msf.LpuSection_id = :LpuSection_id";
		}
		
		if (isset($data['LpuBuilding_id']) and ($data['LpuBuilding_id']>0)) {
			$queryParams['LpuBuilding_id'] = $data['LpuBuilding_id'];
			$filters[] = "lb.LpuBuilding_id = :LpuBuilding_id";
		}
		
		if (isset($data['MedPersonal_id']) and ($data['MedPersonal_id']>0)) {
			$queryParams['MedPersonal_id'] = $data['MedPersonal_id'];
			$filters[] = "msf.MedPersonal_id = :MedPersonal_id";
		}		
		
		$sql = "
			SELECT
				msf.MedPersonal_id,
				MedStaffFact_id,
				lb.LpuBuilding_id,
				isnull(msf.MedPersonal_TabCode, '') as MedPersonal_TabCode,
				rtrim(msf.Person_FIO) as MedPersonal_FIO,
				ps.PostMed_Name as PostMed_Name,
				-- структурное подразделение https://redmine.swan.perm.ru/issues/5424#note-9
				coalesce(LpuSection_Name,LpuUnit_Name, lb.LpuBuilding_Name, Lpu_Nick) as LpuSection_Name,
				MedStaffFact_Stavka,
				lu.LpuUnit_Name,
				convert(varchar,cast(WorkData_begDate as datetime),104) as MedStaffFact_setDate,
				convert(varchar,cast(WorkData_endDate as datetime),104) as MedStaffFact_disDate
				
			FROM v_MedStaffFact msf with (nolock)
			LEFT JOIN v_Lpu Lpu with (nolock) on Lpu.Lpu_id=msf.Lpu_id
			
			LEFT JOIN v_LpuBuilding lb with (nolock) on lb.LpuBuilding_id=msf.LpuBuilding_id
			LEFT JOIN v_LpuUnit lu with (nolock) on lu.LpuUnit_id=msf.LpuUnit_id
			LEFT JOIN v_LpuSection ls with (nolock) on ls.LpuSection_id=msf.LpuSection_id
			LEFT JOIN v_PostMed ps with (nolock) on ps.PostMed_id=msf.Post_id
			".ImplodeWhere($filters);
			/*
			echo getDebugSql($sql, $queryParams);
			exit;
			*/
		$res = $this->db->query($sql, $queryParams);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	} //end getMedPersonalGridDetail()
	

	/**
	 * Получение постраничной информации о местах работы врача
	 * Используется: АРМ кадровика
	 */
	public function getMedPersonalGridPaged($data) {
		if (!(($data['start'] >= 0) && ($data['limit'] >= 0))) 
		{
			return false;
		}
		
		$filters = array();
		$filters[] = "(1=1) ";
		$queryParams = array();
		if ($data['Lpu_id']>0) {
			$queryParams['Lpu_id'] = $data['Lpu_id'];
			$filters[] = "msf.Lpu_id = :Lpu_id";
		}

		if (isset($data['LpuUnit_id']) and ($data['LpuUnit_id']>0)) {
			$queryParams['LpuUnit_id'] = $data['LpuUnit_id'];
			$filters[] = "msf.LpuUnit_id = :LpuUnit_id";
			
		}
		if (isset($data['LpuSection_id']) and ($data['LpuSection_id']>0)) {
			$queryParams['LpuSection_id'] = $data['LpuSection_id'];
			$filters[] = "msf.LpuSection_id = :LpuSection_id";
		}
		
		if (isset($data['LpuBuilding_id']) and ($data['LpuBuilding_id']>0)) {
			$queryParams['LpuBuilding_id'] = $data['LpuBuilding_id'];
			$filters[] = "lu.LpuBuilding_id = :LpuBuilding_id";
		}
		
		if (isset($data['MedPersonal_id']) and ($data['MedPersonal_id']>0)) {
			$queryParams['MedPersonal_id'] = $data['MedPersonal_id'];
			$filters[] = "msf.MedPersonal_id = :MedPersonal_id";
		}

		if (isset($data['Search_BirthDay']) and !empty($data['Search_BirthDay'])) {
			$queryParams['Search_BirthDay'] = $data['Search_BirthDay'];
			$filters[] = "msf.Person_BirthDay = :Search_BirthDay";
		}
				
		if (isset($data['Search_FirName']) and !empty($data['Search_FirName'])) {
			$queryParams['Search_FirName'] = rtrim($data['Search_FirName']);
			$filters[] = "msf.Person_FirName LIKE (:Search_FirName+'%')";
		}
				
		if (isset($data['Search_SecName']) and !empty($data['Search_SecName'])) {
			$queryParams['Search_SecName'] = rtrim($data['Search_SecName']);
			$filters[] = "msf.Person_SecName LIKE (:Search_SecName+'%')";
		}
				
		if (isset($data['Search_SurName']) and !empty($data['Search_SurName'])) {
			$queryParams['Search_SurName'] = rtrim($data['Search_SurName']);
			$filters[] = "msf.Person_SurName LIKE (:Search_SurName+'%')";
		}
		
		$orderby = "";
		if ( !empty($data['sort']) && !empty($data['dir']) ) {
			switch ($data['sort']) {
				case 'MedPersonal_TabCode':
					$data['sort'] = "isnull(msf.MedPersonal_TabCode, '')";
				break;
				
				case 'MedPersonal_FIO':
					$data['sort'] = "rtrim(msf.Person_FIO)";
				break;
				
				case 'LpuSection_Name':
					$data['sort'] = "coalesce(LpuSection_Name,LpuUnit_Name, Lpu_Nick)";
				break;
				
				case 'PostMed_Name':
					$data['sort'] = "ps.PostMed_Name";
				break;
				
				case 'MedStaffFact_Stavka':
					$data['sort'] = "MedStaffFact_Stavka";
				break;
				
				case 'MedStaffFact_setDate':
					$data['sort'] = "WorkData_begDate";
				break;
				
				case 'MedStaffFact_disDate':
					$data['sort'] = "WorkData_endDate";
				break;
				
				default:
					$data['sort'] = "ms.{$data['sort']}";
			}
			$orderby = "{$data['sort']} {$data['dir']},";
		}
		
		$sql = "
			SELECT
			-- select
				msf.MedPersonal_id,
				MedStaffFact_id,
				isnull(msf.MedPersonal_TabCode, '') as MedPersonal_TabCode,
				rtrim(msf.Person_FIO) as MedPersonal_FIO,
				ps.PostMed_Name as PostMed_Name,
				-- структурное подразделение https://redmine.swan.perm.ru/issues/5424#note-9
				coalesce(LpuSection_Name,LpuUnit_Name, Lpu_Nick) as LpuSection_Name,
				MedStaffFact_Stavka,
				lu.LpuUnit_Name,
				convert(varchar,cast(WorkData_begDate as datetime),104) as MedStaffFact_setDate,
				convert(varchar,cast(WorkData_endDate as datetime),104) as MedStaffFact_disDate
			-- end select
			FROM
			-- from 
				v_MedStaffFact msf with (nolock)
				LEFT JOIN v_Lpu Lpu with (nolock) on Lpu.Lpu_id=msf.Lpu_id
				LEFT JOIN v_LpuUnit lu with (nolock) on lu.LpuUnit_id=msf.LpuUnit_id
				LEFT JOIN v_LpuSection ls with (nolock) on ls.LpuSection_id=msf.LpuSection_id
				LEFT JOIN v_PostMed ps with (nolock) on ps.PostMed_id=msf.Post_id
			-- end from
			where 
			-- where 
				".implode(' and ', $filters)."
			-- end where
			order by 
			-- order by 
				{$orderby} msf.MedStaffFact_id
			-- end order by";
			/*
			echo getDebugSql($sql, $queryParams);
			exit;
			*/
		$res = $this->db->query(getLimitSQLPH($sql, $data['start'], $data['limit']), $queryParams);
		$res_count = $this->db->query(getCountSQLPH($sql), $queryParams);
		
		if (is_object($res_count))
		{
			$cnt_arr = $res_count->result('array');
			$count = $cnt_arr[0]['cnt'];
			unset($cnt_arr);
		}
		else
		{
			$count = 0;
		}		
		
		if ( is_object($res) ) {
			$response = array();
			$response['data'] = $res->result('array');
			$response['totalCount'] = $count;
			return $response;
		} else {
			return false;
		}
	} //end getMedPersonalGridPaged()
	
	
	/**
	 * Получение детальной информации о местах работы врача
	 * Используется: окно просмотра и редактирования мест работы мед. персонала
	 */
	public function getMedStaffGridDetail($data) {
		$filters = array();
		$queryParams = array();
		if ($data['session']['lpu_id']>0) {
			$queryParams['Lpu_id'] = $data['session']['lpu_id'];
			$filters[] = "msf.Lpu_id = :Lpu_id";
		}

		if (isset($data['LpuUnit_id']) and ($data['LpuUnit_id']>0)) {
			$queryParams['LpuUnit_id'] = $data['LpuUnit_id'];
			$filters[] = "msf.LpuUnit_id = :LpuUnit_id";
			
		}
		if (isset($data['LpuSection_id']) and ($data['LpuSection_id']>0)) {
			$queryParams['LpuSection_id'] = $data['LpuSection_id'];
			$filters[] = "msf.LpuSection_id = :LpuSection_id";
		}
		
		if (isset($data['LpuBuilding_id']) and ($data['LpuBuilding_id']>0)) {
			$queryParams['LpuBuilding_id'] = $data['LpuBuilding_id'];
			$filters[] = "lu.LpuBuilding_id = :LpuBuilding_id";
		}
		
		if (isset($data['MedPersonal_id']) and ($data['MedPersonal_id']>0)) {
			$queryParams['MedPersonal_id'] = $data['MedPersonal_id'];
			$filters[] = "msf.MedPersonal_id = :MedPersonal_id";
		}

		$sql = "
			SELECT
				msf.MedPersonal_id,
				MedStaffFact_id,
				isnull(msf.MedPersonal_TabCode, '') as MedPersonal_TabCode,
				rtrim(msf.Person_FIO) as MedPersonal_FIO,
				ps.PostMed_Name as PostMed_Name,
				-- структурное подразделение https://redmine.swan.perm.ru/issues/5424#note-9
				coalesce(LpuSection_Name,LpuUnit_Name, Lpu_Nick) as LpuSection_Name,
				MedStaffFact_Stavka,
				lu.LpuUnit_Name,
				convert(varchar,cast(WorkData_begDate as datetime),104) as setDate,
				convert(varchar,cast(WorkData_endDate as datetime),104) as disDate
			FROM v_MedStaffFact msf with (nolock)
			LEFT JOIN v_Lpu Lpu with (nolock) on Lpu.Lpu_id=msf.Lpu_id
			LEFT JOIN v_LpuUnit lu with (nolock) on lu.LpuUnit_id=msf.LpuUnit_id
			LEFT JOIN v_LpuSection ls with (nolock) on ls.LpuSection_id=msf.LpuSection_id
			LEFT JOIN v_PostMed ps with (nolock) on ps.PostMed_id=msf.Post_id
			--LEFT JOIN persis.post ps with (nolock) on ps.id=msf.Post_id
			".ImplodeWhere($filters);
			/*
			echo getDebugSql($sql, $queryParams);
			exit;
			*/
		$res = $this->db->query($sql, $queryParams);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	} //end getMedStaffGridDetail()

	/**
	 * Список мест работы для Уфы из старого ЕРМП
	 */
	public function getMedStaffGridDetail_Ufa_Old_ERMP($data) {
		$filters = array();
		$queryParams = array();
		if ($data['session']['lpu_id']>0) {
			$queryParams['Lpu_id'] = $data['session']['lpu_id'];
			$filters[] = "msf.Lpu_id = :Lpu_id";
		}

		if (isset($data['LpuUnit_id']) and ($data['LpuUnit_id']>0)) {
			$queryParams['LpuUnit_id'] = $data['LpuUnit_id'];
			$filters[] = "msf.LpuUnit_id = :LpuUnit_id";

		}
		if (isset($data['LpuSection_id']) and ($data['LpuSection_id']>0)) {
			$queryParams['LpuSection_id'] = $data['LpuSection_id'];
			$filters[] = "msf.LpuSection_id = :LpuSection_id";
		}

		if (isset($data['LpuBuilding_id']) and ($data['LpuBuilding_id']>0)) {
			$queryParams['LpuBuilding_id'] = $data['LpuBuilding_id'];
			$filters[] = "lu.LpuBuilding_id = :LpuBuilding_id";
		}

		if (isset($data['MedPersonal_id']) and ($data['MedPersonal_id']>0)) {
			$queryParams['MedPersonal_id'] = $data['MedPersonal_id'];
			$filters[] = "msf.MedPersonal_id = :MedPersonal_id";
		}

		$sql = "
			SELECT
				msf.MedPersonal_id,
				MedStaffFact_id,
				isnull(msf.MedPersonal_TabCode, '') as MedPersonal_TabCode,
				rtrim(msf.Person_FIO) as MedPersonal_FIO,
				ps.PostMed_Name as PostMed_Name,
				-- структурное подразделение https://redmine.swan.perm.ru/issues/5424#note-9
				coalesce(LpuSection_Name,LpuUnit_Name, Lpu_Nick) as LpuSection_Name,
				MedStaffFact_Stavka,
				lu.LpuUnit_Name,
				convert(varchar,cast(WorkData_begDate as datetime),104) as setDate,
				convert(varchar,cast(WorkData_endDate as datetime),104) as disDate
			FROM v_MedStaffFact_old msf with (nolock)
			LEFT JOIN v_Lpu Lpu with (nolock) on Lpu.Lpu_id=msf.Lpu_id
			LEFT JOIN v_LpuUnit lu with (nolock) on lu.LpuUnit_id=msf.LpuUnit_id
			LEFT JOIN v_LpuSection ls with (nolock) on ls.LpuSection_id=msf.LpuSection_id
			LEFT JOIN v_PostMed ps with (nolock) on ps.PostMed_id=msf.Post_id
			--LEFT JOIN PostMed ps with (nolock) on ps.PostMed_id=msf.Post_id
			--LEFT JOIN persis.post ps with (nolock) on ps.id=msf.Post_id
			".ImplodeWhere($filters);
		/*
		   echo getDebugSql($sql, $queryParams);
		   exit;
		   */
		$res = $this->db->query($sql, $queryParams);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	} //end getMedStaffGridDetail()

	/**
	 * Получение детальной информации о строках штатного расписания
	 * Используется: окно просмотра и редактирования строк шатного расписания
	 */
	public function getStaffTTGridDetail($data) {
		$filters = array();
		$queryParams = array();
		
		if ($data['Lpu_id']>0) {
			$queryParams['Lpu_id'] = $data['Lpu_id'];
			$filters[] = "st.Lpu_id = :Lpu_id";
		} 

		if (isset($data['LpuUnit_id']) and ($data['LpuUnit_id']>0)) {
			$queryParams['LpuUnit_id'] = $data['LpuUnit_id'];
			$filters[] = "st.LpuUnit_id = :LpuUnit_id";
			
		}
		if (isset($data['LpuSection_id']) and ($data['LpuSection_id']>0)) {
			$queryParams['LpuSection_id'] = $data['LpuSection_id'];
			$filters[] = "st.LpuSection_id = :LpuSection_id";
		}
		
		if (isset($data['LpuBuilding_id']) and ($data['LpuBuilding_id']>0)) {
			$queryParams['LpuBuilding_id'] = $data['LpuBuilding_id'];
			$filters[] = "st.LpuBuilding_id = :LpuBuilding_id";
		}				

		$sql = "
			select
				-- идентификатор строки штатки
				st.id as Staff_id,
				-- Структурный элемент ЛПУ
				CASE
					WHEN ls.LpuSection_id IS NOT NULL THEN rtrim(ls.LpuSection_Name)
					WHEN lu.LpuUnit_id IS NOT NULL THEN rtrim(lu.LpuUnit_Name)
					WHEN lb.LpuBuilding_id IS NOT NULL THEN rtrim(lb.LpuBuilding_Name)
					WHEN l.Lpu_id IS NOT NULL THEN rtrim(l.Lpu_Nick)
				END as StructElement_Name,
				-- Должность
				rtrim(pst.name) as Post_Name,
				-- Вид Мп
				mck.name as MedicalCareKind_Name,
				-- Дата создания	
				convert(varchar,cast(st.BeginDate as datetime),104) as BeginDate,
				-- Комментарий
				rtrim(st.Comments) as Staff_Comment,
				-- Количество ставок
				st.Rate as Staff_Rate,
				-- Из них занято
				ISNULL(RateTotal.RateSum, 0) as Staff_RateSum,
				-- Количество сотрудников
				RateTotal.RateCount as Staff_RateCount,
				st.Lpu_id,
				st.LpuBuilding_id,
				st.LpuUnit_id,
				st.LpuSection_id
			from
				persis.v_Staff st with(nolock)
				left join persis.Post as pst with(nolock) on pst.id = st.Post_id
				left join v_Lpu as l with(nolock) on l.Lpu_id = st.Lpu_id
				left join v_LpuBuilding as lb with(nolock) on lb.LpuBuilding_id = st.LpuBuilding_id
				left join v_LpuUnit as lu with(nolock) on lu.LpuUnit_id = st.LpuUnit_id
				left join v_LpuSection as ls with(nolock) on ls.LpuSection_id = st.LpuSection_id
				left join persis.MedicalCareKind as mck with(nolock) on mck.id = st.MedicalCareKind_id
				outer apply (
					select
					    sum(Rate) as RateSum, COUNT(wp.id) as RateCount
					from
					    persis.WorkPlace wp with(nolock)
					where
					    wp.Staff_id = st.id
					    and (wp.EndDate is null or wp.EndDate >= dbo.tzGetDate())
					    and not exists (
							select top 1 sp.id
							from persis.SkipPayment sp with (nolock)
								inner join persis.SkipPaymentReason spr on spr.id = sp.SkipPaymentReason_id
							where sp.WorkPlace_id = wp.id
								and dbo.tzGetDate() between sp.StartDate and sp.EndDate
								and spr.code in (1,2,3)
						)
				) as RateTotal
			".ImplodeWhere($filters);
		//echo getDebugSql($sql, $queryParams);exit;
		$res = $this->db->query($sql, $queryParams);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	} //end getStaffTTGridDetail()

	/**
	 * Удаление места работы врача
	 * Используется: окно просмотра и редактирования мед. персонала
	 */
	public function dropMedStaffFact($data) {
		$sql = "
			declare
				@ErrCode bigint,
				@ErrMsg varchar(4000);
				
			exec p_MedStaffFact_del
				@MedStaffFact_id = ?,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output;
			select @ErrCode as Error_Code, @ErrMsg as Error_Msg;
			";		
		$res = $this->db->query($sql, array($data['MedStaffFact_id']));
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	} //end dropMedStaffFact()
	
	/**
	 * Проверка привязан ли врач к отделению
	 */
	public function checkIfLpuSectionExists($data)
	{
		if ( !isset($data['LpuSection_idEdit']) || !isset($data['Lpu_id']) || !isset($data['MedPersonal_idEdit']) )
			return false;
			
		$params = array($data['Lpu_id'], $data['LpuSection_idEdit'], $data['MedPersonal_idEdit']);

		$filter_for_edit_action = "";
		if ( isset($data['MedStaffFact_idEdit']) && $data['MedStaffFact_idEdit'] > 0 )
		{
			$filter_for_edit_action = " and MedStaffFact_id <> ? ";
			$params[] = $data['MedStaffFact_idEdit'];
		}
			
		$sql = "
			SELECT 
				count(*) as cnt
			FROM
				MedStaffFact with(nolock)
			WHERE
				Lpu_id = ?
				and LpuSection_id = ?
				and MedPersonal_id = ?
				" . $filter_for_edit_action . "
		";
		$res = $this->db->query($sql, $params );
		if ( is_object($res) )
		{
			$result = $res->result('array');
			if ( $result[0]['cnt'] > 0 )
				return true;
			else
				return false;
		}
		else
			return false;
	}

	/**
	 * Добавление новой записи о месте работы врача
	 * Используется: окно просмотра и редактирования мед. персонала
	 */
	public function insertMedStaffFact($data) {
		$params = array();
		$params[] = $data['Lpu_id'];
		$params[] = $data['Server_id'];
		$params[] = $data['LpuSection_idEdit'];
		$params[] = $data['MedPersonal_idEdit'];
		$params[] = $data['MedStaffFact_StavkaEdit'];
		$params[] = $data['MedStaffFact_IsSpecialistEdit'];
		$params[] = $data['MedStaffFact_IsOMSEdit'];
		$params[] = $data['MedSpecOms_id'];
		$params[] = $data['MedStaffFact_setDateEdit'];
		$params[] = $data['MedStaffFact_disDateEdit'];
		$params[] = $data['MedSpec_idEdit'];
		$params[] = $data['PostMed_idEdit'];
		$params[] = $data['PostMedClass_idEdit'];
		$params[] = $data['PostMedType_idEdit'];
		$params[] = $data['PostMedCat_idEdit'];
		$params[] = $data['RecType_id'];
		$params[] = isset($data['MedStaffFact_PriemTime'])?$data['MedStaffFact_PriemTime']:null;
		$params[] = $data['MedStatus_id'];
		$params[] = ($data['MedStaffFact_PriemTime'] == "") ? 1 : 2;
		$params[] = ($data['MedStaffFact_IsQueueOnFree'] == "") ? 1 : 2;
		$params[] = $data['MedStaffFact_Descr'];
		$params[] = $data['MedStaffFact_Contacts'];
		$params[] = $data['pmUser_id'];
		getSQLParams($params);
		$sql = "
			declare
				@Res bigint,
				@ErrCode bigint,
				@ErrMsg varchar(4000);

			exec p_MedStaffFact_ins
				@MedStaffFact_id = @Res output,
				@Lpu_id = ?,
				@Server_id = ?,
				@LpuSection_id = ?,
				@MedPersonal_id = ?,
				@MedStaffFact_Stavka = ?,
				@MedStaffFact_IsSpecialist = ?,
				@MedStaffFact_IsOMS = ?,
				@MedSpecOms_id = ?,
				@MedStaffFact_setDate = ?,
				@MedStaffFact_disDate = ?,
				@MedSpec_id = ?,
				@PostMed_id = ?,
				@PostMedClass_id = ?,
				@PostMedType_id = ?,
				@PostMedCat_id = ?,
				@RecType_id = ?,
				@MedStaffFact_PriemTime = ?,
				@MedStatus_id = ?,
				@MedStaffFact_IsDirRec = ?,
				@MedStaffFact_IsQueueOnFree = ?,
				@MedStaffFact_Descr = ?,
				@MedStaffFact_Contacts = ?,
				@pmUser_id = ?,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output;
			select @Res as MedStaffFact_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
			";
		$result = $this->db->query($sql, $params);
		if ( is_object($result) )
			return $result->result('array');
		else
			return false;
	} //end insertMedStaffFact()


	/**
	 * Изменение записи о месте работы врача
	 * Используется: окно просмотра и редактирования мед. персонала
	 */
	public function updateMedStaffFact($data) {
		$params = array();
		$params[] = $data['MedStaffFact_idEdit'];
		$params[] = $data['Lpu_id'];
		$params[] = $data['Server_id'];
		$params[] = $data['LpuSection_idEdit'];
		$params[] = $data['MedPersonal_idEdit'];
		$params[] = $data['MedStaffFact_StavkaEdit'];
		$params[] = $data['MedStaffFact_IsSpecialistEdit'];
		$params[] = $data['MedStaffFact_IsOMSEdit'];
		$params[] = $data['MedSpecOms_id'];
		$params[] = $data['MedStaffFact_setDateEdit'];
		$params[] = $data['MedStaffFact_disDateEdit'];
		$params[] = $data['MedSpec_idEdit'];
		$params[] = $data['PostMed_idEdit'];
		$params[] = $data['PostMedClass_idEdit'];
		$params[] = $data['PostMedType_idEdit'];
		$params[] = $data['PostMedCat_idEdit'];
		$params[] = $data['RecType_id'];
		$params[] = isset($data['MedStaffFact_PriemTime'])?$data['MedStaffFact_PriemTime']:null;
		$params[] = $data['MedStatus_id'];
		$params[] = ($data['MedStaffFact_IsDirRec'] == "") ? 1 : 2;
		$params[] = ($data['MedStaffFact_IsQueueOnFree'] == "") ? 1 : 2;
		$params[] = $data['MedStaffFact_Descr'];
		$params[] = $data['MedStaffFact_Contacts'];
		$params[] = $data['pmUser_id'];
		getSQLParams($params);
		$sql = "
			declare
				@Res bigint,
				@ErrCode bigint,
				@ErrMsg varchar(4000);

			exec p_MedStaffFact_upd
				@MedStaffFact_id = ?,
				@Lpu_id = ?,
				@Server_id = ?,
				@LpuSection_id = ?,
				@MedPersonal_id = ?,
				@MedStaffFact_Stavka = ?,
				@MedStaffFact_IsSpecialist = ?,
				@MedStaffFact_IsOMS = ?,
				@MedSpecOms_id = ?,
				@MedStaffFact_setDate = ?,
				@MedStaffFact_disDate = ?,
				@MedSpec_id = ?,
				@PostMed_id = ?,
				@PostMedClass_id = ?,
				@PostMedType_id = ?,
				@PostMedCat_id = ?,
				@RecType_id = ?,
				@MedStaffFact_PriemTime = ?,
				@MedStatus_id = ?,
				@MedStaffFact_IsDirRec = ?,
				@MedStaffFact_IsQueueOnFree = ?,
				@MedStaffFact_Descr = ?,
				@MedStaffFact_Contacts = ?,
				@pmUser_id = ?,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output;
			select @Res as MedStaffFact_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg";
		$result = $this->db->query($sql, $params);
		if ( is_object($result) )
			return $result->result('array');
		else
			return false;
	} //end updateMedStaffFact()


	/**
	 * Проверка на существование врача с заданным $medpersonal_id в БД. Используется при перекэшировании данных пользователей
	 */
	function checkMedPersonalExist($medpersonal_id) {
		$sql = "select top 1 MedPersonal_id from v_MedPersonal with(nolock) where MedPersonal_id = :MedPersonal_id";
		$params = array('MedPersonal_id' => $medpersonal_id);
		
		$result = $this->db->query($sql, $params);
		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) > 0) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Загрузка списка медицинского персонала
	 *
	 * @param string $data Фильтры
	 * @param boolean $dloonly Загружать только врачей ЛЛО?
	 */
	public function loadMedPersonalList( $data, $dloonly = false ) {
		$is_pg = $this->db->dbdriver == 'postgre' ? true : false;

		$filters = array();
		$queryParams = array();

		if ( $data[ 'Lpu_id' ] > 0 && !isFarmacy() ) {
			if ( $is_pg ) {
				$filters[] = "MSF.\"Lpu_id\" = :Lpu_id";
			} else {
				$filters[] = "[MSF].[Lpu_id] = :Lpu_id";
			}
			$queryParams[ 'Lpu_id' ] = $data[ 'Lpu_id' ];			
		}

		if ( $data[ 'LpuSection_id' ] > 0 ) {
			if ( $is_pg ) {
				$filters[] = "MSF.\"LpuSection_id\" = :LpuSection_id";
			} else {
				$filters[] = "[MSF].[LpuSection_id] = :LpuSection_id";
			}
			$queryParams[ 'LpuSection_id' ] = $data[ 'LpuSection_id' ];
		} else if ( isFarmacy() && isset( $data[ 'session' ][ 'OrgFarmacy_id' ] ) ) {
			//TODO: выборка отделений конечно некорректная, в будущем необходимо уточнить критерии
			if ( $is_pg ) {
				$filters[] = "MSF.\"LpuSection_id\" in (select \"LpuSection_id\" from dbo.\"Contragent\" where \"OrgFarmacy_id\" = :OrgFarmacy_id or \"OrgFarmacy_id\" is null)";
			} else {
				$filters[] = "[MSF].[LpuSection_id] in (select LpuSection_id from Contragent with(nolock) where OrgFarmacy_id = :OrgFarmacy_id or OrgFarmacy_id is null)";
			}
			$queryParams[ 'OrgFarmacy_id' ] = $data[ 'session' ][ 'OrgFarmacy_id' ];
		}

		if ( $data[ 'LpuUnit_id' ] > 0 ) {
			if ( $is_pg ) {
				$filters[] = "MSF.\"LpuUnit_id\" = :LpuUnit_id";
			} else {
				$filters[] = "[MSF].[LpuUnit_id] = :LpuUnit_id";
			}
			$queryParams[ 'LpuUnit_id' ] = $data[ 'LpuUnit_id' ];
		}

		if ( $data[ 'MedPersonal_id' ] > 0 ) {
			if ( $is_pg ) {
				$filters[] = "MSF.\"MedPersonal_id\" = :MedPersonal_id";
			} else {
				$filters[] = "[MSF].[MedPersonal_id] = :MedPersonal_id";
			}
			$queryParams[ 'MedPersonal_id' ] = $data[ 'MedPersonal_id' ];
		}

		if ( $dloonly ) {
			// @todo Для Уфы пока не переводим запросы
			if ( $data[ 'session' ][ 'region' ][ 'nick' ] == 'ufa' ) {
				$filters[] = "isnull([MSF].[MedPersonal_TabCode], '0') != '0'";
			} else {
				if ( $is_pg ) {
					$filters[] = "COALESCE(MSF.\"MedPersonal_Code\", '0') != '0'";
				} else {
					$filters[] = "isnull([MSF].[MedPersonal_Code], '0') != '0'";
				}
			}
		}

		if ( $data[ 'onlyWorkInLpu' ] ) {
			if ( $is_pg ) {
				$filters[] = "MSF.\"WorkData_begDate\" is not null and MSF.\"WorkData_begDate\" <= dbo.\"tzGetDate\"()";
				$filters[] = "(MSF.\"WorkData_endDate\" is null or MSF.\"WorkData_endDate\" > dbo.\"tzGetDate\"())";
			} else {
				$filters[] = "[MSF].[WorkData_begDate] is not null and [MSF].[WorkData_begDate] <= dbo.tzGetDate()";
				$filters[] = "([MSF].[WorkData_endDate] is null or [MSF].[WorkData_endDate] > dbo.tzGetDate())";
			}
		}
		
		if ( $is_pg ) {
			$sql = "
				SELECT DISTINCT
					MSF.\"MedPersonal_id\",
					ltrim(rtrim(COALESCE(MSF.\"MedPersonal_TabCode\",'0'))) AS \"MedPersonal_Code\",
					ltrim(rtrim(MSF.\"Person_Fio\")) AS \"MedPersonal_Fio\",
					MSF.\"LpuSection_id\"
				FROM
					dbo.\"v_MedStaffFact\" MSF
					".ImplodeWhere( $filters )."
				ORDER BY
					ltrim(rtrim(MSF.\"Person_Fio\"))
			";
		} else {
			$sql = "
				SELECT distinct
					[MSF].[MedPersonal_id] AS [MedPersonal_id],
					ltrim(rtrim(isnull([MSF].[MedPersonal_TabCode],0))) AS [MedPersonal_Code],
					ltrim(rtrim([MSF].[Person_FIO])) AS [MedPersonal_Fio],
					[MSF].[LpuSection_id] AS [LpuSection_id]
				FROM [v_MedStaffFact] [MSF] WITH (NOLOCK)
				".ImplodeWhere( $filters )."
				ORDER BY ltrim(rtrim([MSF].[Person_FIO]))
			";
		}
		$query = $this->db->query( $sql, $queryParams );

		if ( is_object( $query ) ) {
			return $query->result_array();
		}

		return false;
	}

	/**
	 * Получение списка медицинского персонала по заданными фильтрами
	 * @param array $data Фильтры
	 * @return array Ассоциативный массив с данными медицинского персонала
	 */
	function loadMedStaffFactList($data) {
		$filter = "(1 = 1)";
		$filter_st = "(1 = 1)";
		$queryParams = array();

        //http://redmine.swan.perm.ru/issues/14521
        if (!empty($data['ignoreDisableInDocParam'])) {
            $IsDisableInDocFilter = '';
        } else {
            $IsDisableInDocFilter = 'and ISNULL(MSF.MedStaffFactCache_IsDisableInDoc, 1) = 1';
        }

		$queryParams['Lpu_id'] = $data['Lpu_id'];

		if (isset($data['Lpu_id']) && $data['Lpu_id'] > 0) {
			if (isFarmacy() && isset($data['session']['OrgFarmacy_id'])) {
				//TODO: выборка отделений конечно некорректная, в будущем необходимо уточнить критерии
				$filter .= " and LS.LpuSection_id in (select LpuSection_id from Contragent with(nolock) where OrgFarmacy_id = :OrgFarmacy_id or OrgFarmacy_id is null)";
				$queryParams['OrgFarmacy_id'] = $data['session']['OrgFarmacy_id'];
			} else if ($data['MedPersonal_id'] == 0) {
				$filter .= " and MSF.Lpu_id = :Lpu_id";
			}
		}

		if (!empty($data['onDate'])) {
			$filter .= " and (LS.LpuSection_setDate IS NULL OR LS.LpuSection_setDate <= :onDate) and (LS.LpuSection_disDate IS NULL OR LS.LpuSection_disDate >= :onDate)";
			$filter .= " and (MSF.WorkData_begDate IS NULL OR MSF.WorkData_begDate <= :onDate) and (MSF.WorkData_endDate IS NULL OR MSF.WorkData_endDate >= :onDate)";
			$queryParams['onDate'] = date( 'Y-m-d H:i:s' , strtotime($data['onDate']));
		}
		
		if ( $data['LpuSection_id'] > 0 ) {
			$filter .= " and MSF.LpuSection_id = :LpuSection_id";
			$queryParams['LpuSection_id'] = $data['LpuSection_id'];
		}

		if ( $data['LpuUnit_id'] > 0 ) {
			$filter .= " and LU.LpuUnit_id = :LpuUnit_id";
			$queryParams['LpuUnit_id'] = $data['LpuUnit_id'];
		}

		if ( $data['MedPersonal_id'] > 0 ) {
			$filter .= " and MSF.MedPersonal_id = :MedPersonal_id";
			$queryParams['MedPersonal_id'] = $data['MedPersonal_id'];
		}

		if ( $data['MedStaffFact_id'] > 0 ) {
			$filter .= " and MSF.MedStaffFact_id = :MedStaffFact_id";
			$queryParams['MedStaffFact_id'] = $data['MedStaffFact_id'];
		}

		if ( $data['PostMedType_Code'] > 0 ) {
			$filter .= " and (PK.code is null or isnull(PK.code, 0) = :PostMedType_Code)";
			$queryParams['PostMedType_Code'] = $data['PostMedType_Code'];
		}
		
		if ( !empty($data['MedSpecOms_id']) ) {
			$filter .= " and MSF.MedSpecOms_id = :MedSpecOms_id";
			$filter_st .= " and MSF.MedSpecOms_id = :MedSpecOms_id";
			$queryParams['MedSpecOms_id'] = $data['MedSpecOms_id'];
		}

		$fields = '';

		// Добавил в список Астрахань
		// https://redmine.swan.perm.ru/issues/17450
		if ( in_array($data['session']['region']['nick'], array('astra', 'msk', 'perm', 'pskov', 'khak', 'buryatiya', 'kz')) ) {
			$fields = "
				,convert(varchar(10), MSF.WorkData_dlobegDate, 104) as WorkData_dloBegDate
				,convert(varchar(10), MSF.WorkData_dloendDate, 104) as WorkData_dloEndDate
			";
		}
		else {
			$fields = "
				,null as WorkData_dloBegDate
				,null as WorkData_dloEndDate
			";
		}
		
		$adminpersonal = "";
		
		if ( !empty($data['loadAdminPersonal']) ) {
			$adminpersonal = "
				union all
				-- административный персонал
				select distinct
					MSF.MedStaffFact_id AS MedStaffFact_id,
					MSF.MedPersonal_id AS MedPersonal_id,
					ISNULL(LTRIM(RTRIM(MSF.MedPersonal_TabCode)), '') AS MedPersonal_TabCode,
					ISNULL(LTRIM(RTRIM(MSF.MedPersonal_Code)), '') as MedPersonal_DloCode,
					LTRIM(RTRIM(MSF.Person_FIO)) AS MedPersonal_Fio,
					'[' + LTRIM(RTRIM(LS.LpuSection_Code)) + '. ' + LTRIM(RTRIM(LS.LpuSection_Name)) + ']' as LpuSection_Name,
					ISNULL(LSP.LpuSectionProfile_Code, '') as LpuSectionProfile_Code,
					ISNULL(LSP.LpuSectionProfile_SysNick, '') as LpuSectionProfile_SysNick,
					ISNULL(LUT.LpuUnitType_Code, '') as LpuUnitType_Code,
					ISNULL(LUT.LpuUnitType_SysNick, '') as LpuUnitType_SysNick,
					MSF.Lpu_id,
					LU.LpuBuilding_id,
					LU.LpuUnit_id,
					LU.LpuUnitSet_id,
					LS.LpuSection_id,
					convert(varchar(10), LS.LpuSection_disDate, 104) as LpuSection_disDate,
					convert(varchar(10), LS.LpuSection_setDate, 104) as LpuSection_setDate,
					convert(varchar(10), MSF.WorkData_begDate, 104) as WorkData_begDate,
					convert(varchar(10), MSF.WorkData_endDate, 104) as WorkData_endDate,
					PK.id as PostKind_id,
					p.code as PostMed_Code
					" . $fields . "
				from v_MedStaffFact MSF with (nolock) 
					LEFT join v_LpuSection LS with (nolock) on LS.LpuSection_id = MSF.LpuSection_id
					LEFT join v_LpuSectionProfile LSP WITH (NOLOCK) on LSP.LpuSectionProfile_id = LS.LpuSectionProfile_id 
					LEFT join v_LpuUnit LU WITH (NOLOCK) on LU.LpuUnit_id = LS.LpuUnit_id 
					LEFT join v_LpuUnitType LUT WITH (NOLOCK) on LUT.LpuUnitType_id = LU.LpuUnitType_id 
					left join persis.PostKind PK WITH (NOLOCK) on PK.id = MSF.PostKind_id
					LEFT JOIN persis.Post p with(nolock) on p.id = msf.Post_id
					inner join persis.FRMPPost fp with(nolock) on fp.id = p.frmpEntry_id
				where " . $filter . "
					/*and MSF.Lpu_id = :Lpu_id*/
					and MSF.WorkData_begDate is not null
					and fp.parent in (1,5)
					$IsDisableInDocFilter
			";
		}
		
		$query = "
			select * from (
				select distinct
					 MSF.MedStaffFact_id AS MedStaffFact_id
					,MSF.MedPersonal_id AS MedPersonal_id
					,ISNULL(LTRIM(RTRIM(MSF.MedPersonal_TabCode)), '') AS MedPersonal_TabCode
					,ISNULL(LTRIM(RTRIM(MSF.MedPersonal_Code)), '') as MedPersonal_DloCode
					,LTRIM(RTRIM(MSF.Person_FIO)) AS MedPersonal_Fio
					,'[' + LTRIM(RTRIM(LS.LpuSection_Code)) + '. ' + LTRIM(RTRIM(LS.LpuSection_Name)) + ']' as LpuSection_Name
					,ISNULL(LSP.LpuSectionProfile_Code, '') as LpuSectionProfile_Code
					,ISNULL(LSP.LpuSectionProfile_SysNick, '') as LpuSectionProfile_SysNick
					,ISNULL(LUT.LpuUnitType_Code, '') as LpuUnitType_Code
					,ISNULL(LUT.LpuUnitType_SysNick, '') as LpuUnitType_SysNick
					,MSF.Lpu_id
					,LU.LpuBuilding_id
					,LU.LpuUnit_id
					,LU.LpuUnitSet_id
					,LS.LpuSection_id
					,convert(varchar(10), LS.LpuSection_disDate, 104) as LpuSection_disDate
					,convert(varchar(10), LS.LpuSection_setDate, 104) as LpuSection_setDate
					,convert(varchar(10), MSF.WorkData_begDate, 104) as WorkData_begDate
					,convert(varchar(10), MSF.WorkData_endDate, 104) as WorkData_endDate
					,PK.id as PostKind_id
					,p.code as PostMed_Code
					" . $fields . "
				from v_MedStaffFact MSF with (nolock)
					inner join v_LpuSection LS with (nolock) on LS.LpuSection_id = MSF.LpuSection_id
						and LS.LpuSection_setDate is not null
					inner join v_LpuSectionProfile LSP WITH (NOLOCK) on LSP.LpuSectionProfile_id = LS.LpuSectionProfile_id
					inner join v_LpuUnit LU WITH (NOLOCK) on LU.LpuUnit_id = LS.LpuUnit_id
					inner join v_LpuUnitType LUT WITH (NOLOCK) on LUT.LpuUnitType_id = LU.LpuUnitType_id
					left join persis.PostKind PK WITH (NOLOCK) on PK.id = MSF.PostKind_id
					left join persis.Post p with (nolock) on p.id = MSF.Post_id
				where " . $filter . "
					and MSF.WorkData_begDate is not null
					$IsDisableInDocFilter
		";

		// Нужны ли сторонние специалисты, если явно указан идентификатор отделения?
		if ( empty($data['LpuSection_id']) ) {
			$query .= "
				union all
				-- сторонние специалисты (пока по ним нет даты окончания договора, поэтому если появится - то просто добавить в последний join) 
				select distinct
					 MSF.MedStaffFact_id AS MedStaffFact_id
					,MSF.MedPersonal_id AS MedPersonal_id
					,ISNULL(LTRIM(RTRIM(MSF.MedPersonal_TabCode)), '') AS MedPersonal_TabCode
					,ISNULL(LTRIM(RTRIM(MSF.MedPersonal_Code)), '') as MedPersonal_DloCode
					,LTRIM(RTRIM(MSF.Person_FIO)) AS MedPersonal_Fio
					,'[' + LTRIM(RTRIM(LS.LpuSection_Code)) + '. ' + LTRIM(RTRIM(LS.LpuSection_Name)) + ']' as LpuSection_Name
					,ISNULL(LSP.LpuSectionProfile_Code, '') as LpuSectionProfile_Code
					,ISNULL(LSP.LpuSectionProfile_SysNick, '') as LpuSectionProfile_SysNick
					,ISNULL(LUT.LpuUnitType_Code, '') as LpuUnitType_Code
					,ISNULL(LUT.LpuUnitType_SysNick, '') as LpuUnitType_SysNick
					,MSF.Lpu_id
					,LU.LpuBuilding_id
					,LU.LpuUnit_id
					,LU.LpuUnitSet_id
					,LS.LpuSection_id
					,convert(varchar(10), LS.LpuSection_disDate, 104) as LpuSection_disDate
					,convert(varchar(10), LS.LpuSection_setDate, 104) as LpuSection_setDate
					,convert(varchar(10), MSF.WorkData_begDate, 104) as WorkData_begDate
					,convert(varchar(10), MSF.WorkData_endDate, 104) as WorkData_endDate
					,PK.id as PostKind_id
					,p.code as PostMed_Code
					" . $fields . "
				from v_MedStaffFact MSF WITH (NOLOCK)
					inner join v_LpuSection LS WITH (NOLOCK) on LS.LpuSection_id = MSF.LpuSection_id
						and LS.LpuSection_setDate is not null
					inner join v_LpuSectionProfile LSP WITH (NOLOCK) on LSP.LpuSectionProfile_id = LS.LpuSectionProfile_id
					inner join v_LpuUnit LU WITH (NOLOCK) on LU.LpuUnit_id = LS.LpuUnit_id
					inner join v_LpuUnitType LUT WITH (NOLOCK) on LUT.LpuUnitType_id = LU.LpuUnitType_id
					left join persis.PostKind PK WITH (NOLOCK) on PK.id = MSF.PostKind_id
					left join persis.Post p with (nolock) on p.id = MSF.Post_id
					inner join LpuDispContract LDC WITH (NOLOCK) on LDC.Lpu_id = :Lpu_id
					cross apply (
						select top 1 t.MedStaffFact_id 
						from v_MedStaffFact t WITH (NOLOCK) 
						where t.MedPersonal_id = MSF.MedPersonal_id 
							and t.LpuSection_id = LS.LpuSection_id 
							and t.Lpu_id = LDC.Lpu_oid
						order by
							Isnull(WorkData_endDate, dbo.tzGetDate()) desc
					) as t
				where " . $filter_st . "
					and MSF.Lpu_id = LDC.Lpu_oid
					and MSF.Lpu_id != :Lpu_id
					and (MSF.LpuSection_id = LDC.LpuSection_id or LDC.LpuSection_id is null)
					and LSP.LpuSectionProfile_id = LDC.LpuSectionProfile_id
					and MSF.WorkData_begDate is not null
					and MSF.MedStaffFact_id = t.MedStaffFact_id
					$IsDisableInDocFilter
				 " . $adminpersonal . " 
			";
		}

		$query .= "
				) as MedPersonal
				order by
					 MedPersonal.MedPersonal_Fio
					,MedPersonal.LpuSection_Name
		";				
		//echo getDebugSql($query, $queryParams);exit;
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	} // end loadMedStaffFactList()
	
	
	/**
	 * Получение комментария места работы врача (и типа записи)
	 */
	public function getMedStaffFactComment($data) {
		$sql = "
			SELECT
				rtrim(msf.MedStaffFact_Descr) as MedStaffFact_Descr,
				msf.MedStaffFact_updDT,
				rtrim(u.pmUser_Name) as pmUser_Name,
				RecType_id,
				MSF.Lpu_id,
				isnull(msf.MedStaffFact_IsDirRec, 1) as MedStaffFact_IsDirRec
			from v_MedStaffFact MSF with (nolock)
			left join v_pmUser u with(nolock) on u.pmUser_id = msf.pmUser_updID
			where MedStaffFact_id = :MedStaffFact_id
		";
		$res = $this->db->query(
			$sql,
			array(
				'MedStaffFact_id' => $data['MedStaffFact_id']
			)
		);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	} //end getMedStaffFactEditWindow()
	
	
	/**
	 * Сохранение комментария места работы врача
	 */
	public function saveMedStaffFactComment($data) {
		
		//Редактируем схему persis. Правильно ли?
		$sql = "
			update persis.WorkPlace
			set
				Descr = :MedStaffFact_Descr,
				updDT = dbo.tzGetDate(),
				pmUser_updID = :pmUser_id
			where
				id = :MedStaffFact_id;
			exec persis.p_WorkPlace_upd @WorkPlace_id = :MedStaffFact_id, @IsReload = 0
		";
		
		$res = $this->db->query(
			$sql,
			array(
				'MedStaffFact_id' => $data['MedStaffFact_id'],
				'MedStaffFact_Descr' => $data['MedStaffFact_Descr'],
				'pmUser_id' => $data['pmUser_id']
			)
		);
		
		return array(
			0 => array( 'Error_Msg' => '')
		);
	} //end getMedStaffFactEditWindow()
	
	
	/**
	 * Получение длительности времени приёма врача
	 */
	public function getMedStaffFactDuration($data) {
		$sql = "
			SELECT
				MedStaffFact_PriemTime
			from v_MedStaffFact MSF with (nolock)
			left join v_pmUser u with(nolock) on u.pmUser_id = msf.pmUser_updID
			where MedStaffFact_id = :MedStaffFact_id
		";
		$res = $this->db->query(
			$sql,
			array(
				'MedStaffFact_id' => $data['MedStaffFact_id']
			)
		);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	} //end getMedStaffFactEditWindow()
	
	
	/**
	 * Получение ФИО врача, к которому привязан текущий пользователь
	 */
	public function getUserMedPersonalFio($id) {
		if ( empty($id) || !is_numeric($id) ) {
			return '';
		}

		$sql = "
			select top 1 Person_Fio
			from v_MedPersonal with (nolock)
			where MedPersonal_id = :MedPersonal_id
		";
		$res = $this->db->query($sql, array('MedPersonal_id' => $id));

		if ( !is_object($res) ) {
			return '';
		}

		$response = $res->result('array');

		if ( is_array($response) && count($response) == 1 && !empty($response[0]['Person_Fio']) ) {
			return trim($response[0]['Person_Fio']);
		}
		else {
			return '';
		}

	} //end getUserMedPersonalFio()
	
	
	/**
	 * Получение списка мест работы доступных для регистратуры
	 */
	public function getMedStaffFactListForReg($data) {
		
		$queryParams = array();
		
		if (isset($data['LpuUnit_id'])) {
			$queryParams['LpuUnit_id'] = $data['LpuUnit_id'];
			$filters[] = "msf.LpuUnit_id = :LpuUnit_id";
			
		}
		if (isset($data['LpuSection_id'])) {
			$queryParams['LpuSection_id'] = $data['LpuSection_id'];
			$filters[] = "msf.LpuSection_id = :LpuSection_id";
		}
		
		if (isset($data['LpuSectionPid_id'])) {
			$queryParams['LpuSectionPid_id'] = $data['LpuSectionPid_id'];
			$filters[] = "msf.LpuSection_id = :LpuSectionPid_id";
		}
		
		$filters[] = "msf.Lpu_id = :Lpu_id";
		$queryParams['Lpu_id'] = $data['Lpu_id'];
		
		$filters[] = "(isnull(cast(msf.Medstafffact_disDate as date), '2030-01-01') >= dbo.tzGetDate())
				and isnull(MedStatus_id, 1) = 1 
				and (isnull(msf.RecType_id, 6) != 6)";
		
		$sql = "
			SELECT
				MedStaffFact_id,
				ltrim(rtrim(Person_SurName)) + ' ' + ltrim(rtrim(Person_FirName)) + ' ' + ltrim(rtrim(Person_SecName)) as MedPersonal_FIO,
				rtrim(LpuSection_Name) as LpuSection_Name,
				rtrim(LpuSectionProfile_Name) as LpuSectionProfile_Name
			FROM v_MedStaffFact msf with (nolock)
			left join v_LpuSection ls with (nolock) on msf.LpuSection_id = ls.LpuSection_id
		
			".ImplodeWhere($filters)."
			ORDER BY MedPersonal_FIO
		";
		$res = $this->db->query($sql, $queryParams);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	} //end getMedStaffFactListForReg()
	
}
// END MedPersonal_model class
