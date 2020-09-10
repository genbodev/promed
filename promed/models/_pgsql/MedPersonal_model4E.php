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
class MedPersonal_model4E extends SwPgModel {

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
			select
				MSF.MedPersonal_id as \"MedPersonal_id\",
				MP.Dolgnost_Name as \"Dolgnost_Name\",
				LS.LpuSection_id as \"LpuSection_id\",
				LS.LpuSectionProfile_id as \"LpuSectionProfile_id\",
				LS.LpuSectionProfile_Name as \"LpuSectionProfile_Name\",
				MSF.LpuUnit_id as \"LpuUnit_id\"
			from 
				v_MedStaffFact MSF
				left join v_MedPersonal MP on MSF.MedPersonal_id = MP.MedPersonal_id AND MSF.Lpu_id = MP.Lpu_id
				left join v_LpuSection LS on LS.LpuSection_id = MSF.LpuSection_id
			where
				MSF.MedStaffFact_id = ?
			limit 1
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
			select
				mso.MedSpecOms_Code
			from 
				v_MedStaffFact msf
				left join v_MedSpecOms mso on msf.MedSpecOms_id = mso.MedSpecOms_id
			where
				MSF.MedStaffFact_id = :MedStaffFact_id
			limit 1
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
			select
				msf.MedstaffFact_id as \"MedstaffFact_id\",
				MedPersonal_FIO as \"MedPersonal_FIO\",
				ls.LpuSectionProfile_Name as \"LpuSectionProfile_Name\",
				ls.LpuSectionProfile_Code as \"LpuSectionProfile_Code\",
				lr.LpuRegion_Name as \"LpuRegion_Name\",
				ls.LpuSectionProfile_id as \"LpuSectionProfile_id\",
				MedStaffFact_IsQueueOnFree as \"MedStaffFact_IsQueueOnFree\",
				RecType_id as \"RecType_id\",
				lu.LpuUnit_Name as \"LpuUnit_Name\",
				a.Address_Address as \"Address_Address\",
				l.Lpu_Nick as \"Lpu_Nick\",
				lu.LpuUnit_id as \"LpuUnit_id\",
				msf.Lpu_id as \"Lpu_id\"
			from v_MedstaffFact_ER msf
			left join v_MedStaffRegion msr on msr.MedPersonal_id = msf.MedPersonal_id
			left outer join v_LpuRegion lr on msr.LpuRegion_Id = lr.LpuRegion_Id
			left outer join v_LpuSection_ER ls on msf.LpuSection_Id = ls.LpuSection_Id
			left join v_LpuUnit_ER lu on lu.LpuUnit_id = msf.LpuUnit_id
			left outer join Address a on lu.Address_id = a.Address_id
			left join v_Lpu l on l.lpu_id = lu.lpu_id
			where 
				msf.MedStaffFact_id = :MedStaffFact_id
			limit 1
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
		$params = [
			'Person_BirthDay' => $data['Person_BirthDay'],
			'Person_SurName' => $data['Person_SurName'],
			'Person_FirName' => $data['Person_FirName'],
			'Lpu_id' => $data['Lpu_id']
        ];
		if (empty($data['Person_SecName']))
		{
			$filter = 'and Person_SecName is null';
		}
		else
		{
			$params['Person_SecName'] = $data['Person_SecName'];
			$filter = 'and Person_SecName = :Person_SecName';
		}
		$sql = "
			select
				MedPersonal_id as \"MedPersonal_id\"
			from
				MedPersonalCache
			where
				to_char(Person_BirthDay, 'dd.mm.yyyy') = :Person_BirthDay
				and Person_SurName = :Person_SurName
				and Person_FirName = :Person_FirName
				and Lpu_id = :Lpu_id
				{$filter}
			limit 1
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
				MedPersonal_id as \"MedPersonal_id\",
				MedPersonal_Code as \"MedPersonal_Code\",
				MedPersonal_TabCode as \"MedPersonal_TabCode\",
				Person_SurName as \"Person_SurName\",
				Person_FirName as \"Person_FirName\",
				Person_SecName as \"Person_SecName\",
				to_char(cast(Person_BirthDay as timestamp), 'dd.mm.yyyy') as \"Person_BirthDay\",
				to_char(cast(WorkData_begDate as timestamp), 'dd.mm.yyyy') as \"WorkData_begDate\",
				to_char(cast(WorkData_endDate as timestamp), 'dd.mm.yyyy') as \"WorkData_endDate\",
				WorkData_IsDlo as \"WorkData_IsDlo\",
				Person_Snils as \"Person_Snils\"
			from
				".$fromtable."
			where
				Lpu_id = :Lpu_id and
				MedPersonal_id = :MedPersonal_id
		";
        $result = $this->db->query($sql, ['Lpu_id' => $data['Lpu_id'], 'MedPersonal_id' => $data['MedPersonal_id']]);

        if (!is_object($result))
            return false;

        return $result->result('array');
	}

	/**
	 * Сохранение медперсонала
	 */
	function saveMedPersonal($data) 
	{
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
		
		$result = $this->db->query($query, [
			'MedPersonal_TabCode' => $data['MedPersonal_TabCode'],
			'Lpu_id' => $data['Lpu_id'],
			'MedPersonal_id' => $data['MedPersonal_id']
        ]);
		
		if (is_object($result)) {
			$res = $result->result('array');			
			if ($res[0]['cnt'] > 0) 
				throw new Exception('Данный табельный номер уже используется');
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
			select
			    MedPersonal_id as \"MedPersonal_id\",
			    Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\"
			from " .$proc. "
			(
				MedPersonal_id := :MedPersonal_id,
				MedPersonal_Code := :MedPersonal_Code,
				MedPersonal_TabCode := :MedPersonal_TabCode,
				Person_SurName := :Person_SurName,
				Person_FirName := :Person_FirName,
				Person_SecName := :Person_SecName,
				Person_BirthDay := :Person_BirthDay,
				Lpu_id := :Lpu_id,
				WorkData_begDate := :WorkData_begDate,
				WorkData_endDate := :WorkData_endDate,
				WorkData_IsDlo := :WorkData_IsDlo,
				Person_Snils := :Person_Snils,
				pmUser_id := :pmUser_id
            )
		";

		$params = [
            'MedPersonal_id' => $data['MedPersonal_id'],
            'MedPersonal_Code' => $data['MedPersonal_Code'],
            'MedPersonal_TabCode' => $data['MedPersonal_TabCode'],
            'Person_SurName' => strtoupper($data['Person_SurName']),
            'Person_FirName' => strtoupper($data['Person_FirName']),
            'Person_SecName' => !empty($data['Person_SecName']) ? strtoupper($data['Person_SecName']) : '- - -',
            'Person_BirthDay' => $data['Person_BirthDay'],
            'Lpu_id' => $data['Lpu_id'],
            'WorkData_begDate' => $data['WorkData_begDate'],
            'WorkData_endDate' => $data['WorkData_endDate'],
            'WorkData_IsDlo' => $data['WorkData_IsDlo'],
            'Person_Snils' => $data['Person_Snils'],
            'pmUser_id' => $data['pmUser_id']
        ];
		$result = $this->db->query($query, $params);
		
		
		if (!is_object($result))
		{
            throw new Exception('Ошибка при выполнении запроса к базе данных');
		}
        return $result->result('array');
	}	
	
	/**
	 * Получение списка медперсонала
	 * Используется: окно поиска мед. персонала.
	 */
	public function loadMedPersonalSearchList($data)
    {
		$filter = " ( 1 = 1 ) ";
		$filter .= " and Lpu_id = :Lpu_id ";
		$queryParams = [];
		$queryParams['start'] = $data['start'];
		$queryParams['limit'] = $data['limit'];
		$queryParams['Lpu_id'] = $data['Lpu_id'];
		
		if ( isset($data['Person_SurName']) )
		{
			$filter .= " and Person_SurName ilike :Person_SurName ";
			$queryParams['Person_SurName'] = $data['Person_SurName']."%";
		}
		
		if ( isset($data['Person_FirName']) )
		{
			$filter .= " and Person_FirName ilike :Person_FirName ";
			$queryParams['Person_FirName'] = $data['Person_FirName']."%";
		}
		
		if ( isset($data['Person_SecName']) )
		{
			$filter .= " and Person_SecName ilike :Person_SecName ";
			$queryParams['Person_SecName'] = $data['Person_SecName']."%";
		}
			
		$sql = "
			select
			-- select
				MedPersonal_id as \"MedPersonal_id\",
				MedPersonal_Code as \"MedPersonal_Code\",
				MedPersonal_TabCode as \"MedPersonal_TabCode\",
				Person_SurName as \"Person_SurName\",
				Person_FirName as \"Person_FirName\",
				Person_SecName as \"Person_SecName\",
				to_char(cast(Person_BirthDay as timestamp), 'dd.mm.yyyy') as \"Person_BirthDay\",
				to_char(cast(WorkData_begDate as timestamp), 'dd.mm.yyyy') as \"WorkData_begDate\",
				to_char(cast(WorkData_endDate as timestamp), 'dd.mm.yyyy') as \"WorkData_endDate\",
				case when coalesce(WorkData_IsDlo, 1) = 1 then 'false' else 'true' end as \"WorkData_IsDlo\",
				Person_Snils as \"Person_Snils\"
			-- end select
			from
			-- from
				v_MedPersonal
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
			
		if ( is_object($result) ) {
			$res = $result->result('array');

			if ( is_array($res) ) {
				if ( $data['start'] == 0 && count($res) < 100 ) {
					$response['data'] = $res;
					$response['totalCount'] = count($res);
				} else {
					$response['data'] = $res;

					$get_count_query = getCountSQLPH($count_sql);
					$get_count_result = $this->db->query($get_count_query, $queryParams);

					if ( is_object($get_count_result) ) {
						$response['totalCount'] = $get_count_result->result('array');
						$response['totalCount'] = $response['totalCount'][0]['cnt'];
					} else {
						return false;
					}
				}
			} else {
				return false;
			}
		} else {
			return false;
		}
		
		return $response;
	 }

	/**
	 * Список врачей для Уфы из старого ЕРМП
	 */
	public function loadMedPersonalSearchList_Ufa_Old_ERMP($data)
    {
		$filter = " ( 1 = 1 ) ";
		$filter .= " and Lpu_id = :Lpu_id ";
		$queryParams = array();
		$queryParams['start'] = $data['start'];
		$queryParams['limit'] = $data['limit'];
		$queryParams['Lpu_id'] = $data['Lpu_id'];

		if ( isset($data['Person_SurName']) )
		{
			$filter .= " and Person_SurName ilike :Person_SurName ";
			$queryParams['Person_SurName'] = rtrim($data['Person_SurName'])."%";
		}

		if ( isset($data['Person_FirName']) )
		{
			$filter .= " and Person_FirName ilike :Person_FirName ";
			$queryParams['Person_FirName'] = rtrim($data['Person_FirName'])."%";
		}

		if ( isset($data['Person_SecName']) )
		{
			$filter .= " and Person_SecName ilike :Person_SecName ";
			$queryParams['Person_SecName'] = rtrim($data['Person_SecName'])."%";
		}

		$sql = "
			select
			-- select
				MedPersonal_id as \"MedPersonal_id\",
				MedPersonal_Code as \"MedPersonal_Code\",
				MedPersonal_TabCode as \"MedPersonal_TabCode\",
				Person_SurName as \"Person_SurName\",
				Person_FirName as \"Person_FirName\",
				Person_SecName as \"Person_SecName\",
				to_char(cast(Person_BirthDay as timestamp), 'dd.mm.yyyy') as \"Person_BirthDay\",
				to_char(cast(WorkData_begDate as timestamp), 'dd.mm.yyyy') as \"WorkData_begDate\",
				to_char(cast(WorkData_endDate as timestamp), 'dd.mm.yyyy') as \"WorkData_endDate\",
				case when coalesce(WorkData_IsDlo, 1) = 1 then 'false' else 'true' end as \"WorkData_IsDlo\",
				Person_Snils as \"Person_Snils\"
			-- end select
			from
			-- from
				v_MedPersonal_old
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

		if ( !is_object($result) )
            return false;
        $res = $result->result('array');

        if (!is_array($res) )
            return false;

        if ( $data['start'] == 0 && count($res) < 100 ) {
            $response['data'] = $res;
            $response['totalCount'] = count($res);
        } else {
            $response['data'] = $res;

            $get_count_query = getCountSQLPH($count_sql);
            $get_count_result = $this->db->query($get_count_query, $queryParams);

            if ( !is_object($get_count_result) ) {
                return false;
            }

            $response['totalCount'] = $get_count_result->result('array');
            $response['totalCount'] = $response['totalCount'][0]['cnt'];
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
				Lpu_id as \"Lpu_idEdit\",
				LpuUnit_id as \"LpuUnit_idEdit\",
				LpuSection_id as \"LpuSection_idEdit\",
				Post_id as \"PostMed_idEdit\",
				MedStaffFact_Stavka as \"MedStaffFact_StavkaEdit\",
				PostKind_id as \"PostMedType_idEdit\",
				PostMedClass_id as \"PostMedClass_idEdit\",
				MedStaffFact_IsOMS as \"MedStaffFact_IsOMSEdit\",
				MedSpecOms_id as \"MedSpecOms_id\",
				MedStaffFact_IsSpecialist as \"MedStaffFact_IsSpecialistEdit\",
				to_char(cast(WorkData_begDate as timestamp), 'dd.mm.yyyy') as \"MedStaffFact_setDateEdit\",
				to_char(cast(WorkData_endDate as timestamp), 'dd.mm.yyyy') as \"MedStaffFact_disDateEdit\",
				MSF.RecType_id as \"RecType_id\",
				coalesce (MSF.MedStaffFact_PriemTime, '') as \"MedStaffFact_PriemTime\",
				MSF.MedStatus_id as \"MedStatus_id\",
				CASE WHEN coalesce(MSF.MedStaffFact_IsDirRec, 1) = 1 THEN '' ELSE 'true' END as \"MedStaffFact_IsDirRec\",
				CASE WHEN coalesce(MSF.MedStaffFact_IsQueueOnFree, 1) = 1 THEN '' ELSE 'true' END as \"MedStaffFact_IsQueueOnFree\",
				coalesce(MSF.MedStaffFact_Descr, '') as \"MedStaffFact_Descr\",
				coalesce(MSF.MedStaffFact_Contacts, '') as \"MedStaffFact_Contacts\"
			from
			    v_MedStaffFact MSF
			where
			    MedStaffFact_id = :MedStaffFact_id 
			and
			    Lpu_id = :Lpu_id
		";
		if ($data['session']['region']['nick'] == 'ufa')
			$sql = "
			SELECT
				Lpu_id as \"Lpu_idEdit\",
				LpuUnit_id as \"LpuUnit_idEdit\",
				LpuSection_id as \"LpuSection_idEdit\",
				Post_id as \"PostMed_idEdit\",
				MedStaffFact_Stavka as \"MedStaffFact_StavkaEdit\",
				MedSpec_id as \"MedSpec_idEdit\",
				PostKind_id as \"PostMedType_idEdit\",
				PostMedClass_id as \"PostMedClass_idEdit\",
				PostMedCat_id as \"PostMedCat_idEdit\",
				MedStaffFact_IsOMS as \"MedStaffFact_IsOMSEdit\",
				MedSpecOms_id as \"MedSpecOms_id\",
				MedStaffFact_IsSpecialist as \"MedStaffFact_IsSpecialistEdit\",
				to_char(cast(WorkData_begDate as timestamp), 'dd.mm.yyyy') as \"MedStaffFact_setDateEdit\",
				to_char(cast(WorkData_endDate as timestamp), 'dd.mm.yyyy') as \"MedStaffFact_disDateEdit\",
				MSF.RecType_id as \"RecType_id\",
				coalesce(MSF.MedStaffFact_PriemTime, '') as \"MedStaffFact_PriemTime\",
				MSF.MedStatus_id as \"MedStatus_id\",
				CASE WHEN coalesce (MSF.MedStaffFact_IsDirRec, 1) = 1 THEN '' ELSE 'true' END as \"MedStaffFact_IsDirRec\",
				CASE WHEN coalesce (MSF.MedStaffFact_IsQueueOnFree, 1) = 1 THEN '' ELSE 'true' END as \"MedStaffFact_IsQueueOnFree\",
				coalesce(MSF.MedStaffFact_Descr, '') as \"MedStaffFact_Descr\",
				coalesce(MSF.MedStaffFact_Contacts, '') as \"MedStaffFact_Contacts\"
			from
			    v_MedStaffFact_old MSF
			where
			    MedStaffFact_id = :MedStaffFact_id 
			and
			    Lpu_id = :Lpu_id
		";
		//end if

		$res = $this->db->query($sql, [
		    'MedStaffFact_id' => $data['MedStaffFact_id'],
            'Lpu_id' => $data['Lpu_id']
        ]);

		if (!is_object($res))
			return false;

        return $res->result('array');
	} //end getMedStaffFactEditWindow()


	/**
	 * Получение списка медицинского персонала для SwMedPersonalAllCombo
	 * Используется: комбобокс
	 */
	public function getMedPersonalCombo($data)
    {
		
		$queryParams = [];
		// Фильтры
		$f = "";
		$where = "WHERE msf.Lpu_id = :Lpu_id";
		if ((isset($data['Org_id'])) and ($data['Org_id']>0)) {
			$queryParams['Org_id'] = $data['Org_id'];
			$where = "WHERE msf.Lpu_id = (select Lpu_id from v_Lpu where Org_id = :Org_id limit 1)";
		} else if ((isset($data['Org_ids'])) and (!empty($data['Org_ids']))) {
			$queryParams['Org_ids'] = json_decode($data['Org_ids']);
			if (count($queryParams['Org_ids']) > 1) {
				$where = "WHERE msf.Lpu_id IN (select Lpu_id from v_Lpu where Org_id IN (".implode(',', $queryParams['Org_ids'])."))";
			} elseif (count($queryParams['Org_ids']) == 1) {
				$queryParams['Org_id'] = $queryParams['Org_ids'][0];
				$where = "WHERE msf.Lpu_id = (select Lpu_id from v_Lpu where Org_id = :Org_id limit 1)";
			} else {
				$where = "WHERE (1=0) ";
			}
		} else if ((isset($data['Lpu_id'])) and ($data['Lpu_id']>0)) {
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		} else if (isset($data['session']['lpu_id'])) {
			$queryParams['Lpu_id'] = $data['session']['lpu_id'];
		} else {
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
		
		$sql = "
			SELECT
				msf.MedPersonal_id as \"MedPersonal_id\",
				msf.MedStaffFact_id as \"MedStaffFact_id\",
				coalesce(msf.MedPersonal_TabCode, '') as \"MedPersonal_Code\",
				rtrim(msf.Person_FIO) as \"MedPersonal_Fio\",
				ps.PostMed_Name as \"PostMed_Name\",
				to_char(cast(WorkData_begDate as timestamp), 'dd.mm.yyyy') as \"WorkData_begDate\",
				to_char(cast(WorkData_endDate as timestamp), 'dd.mm.yyyy') as \"WorkData_endDate\",
				coalesce(msf.MedStaffFact_Stavka, '') as \"MedStaffFact_Stavka\",
				lpub.LpuBuilding_Name as \"LpuBuilding_Name\",
				LUT.LpuUnitType_Code as \"LpuUnitType_Code\"

			FROM
			    v_MedStaffFact msf
                LEFT JOIN v_PostMed ps on ps.PostMed_id=msf.Post_id
                LEFT JOIN v_LpuBuilding lpub on msf.LpuBuilding_id=lpub.LpuBuilding_id
                LEFT join v_LpuSection LS on LS.LpuSection_id = msf.LpuSection_id
                LEFT join v_LpuUnit LU on LU.LpuUnit_id = msf.LpuUnit_id
                LEFT join v_LpuUnitType LUT on LUT.LpuUnitType_id = LU.LpuUnitType_id
			{$where}
			AND
				(msf.WorkData_endDate is null or msf.WorkData_endDate > dbo.tzGetDate())
			ORDER BY MedPersonal_Fio
		";
		//		echo getDebugSql($sql, $queryParams);
		//		exit;
		$res = $this->db->query($sql, $queryParams);
		if ( !is_object($res) )
			return false;

        return $res->result('array');
	} //end getMedPersonalCombo()

	
	/**
	 * Получение списка медицинского персонала (только участковых врачей)
	 * Используется: комбобокс
	 */
	public function getMedPersonalWithLpuRegionCombo($data)
    {
		
		$queryParams = [];
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
				MedPersonal_id as \"MedPersonal_id\",
				coalesce (MedPersonal_TabCode, '') as \"MedPersonal_Code\",
				trim(Person_SurName) || ' ' || trim(Person_FirName) || ' ' || trim(Person_SecName) as \"MedPersonal_FIO\",
				to_char(cast(WorkData_begDate as timestamp), 'dd.mm.yyyy') as \"WorkData_begDate\",
				to_char(cast(WorkData_endDate as timestamp), 'dd.mm.yyyy') as \"WorkData_endDate\",
				MedStaffRegion.LpuRegion_id as \"LpuRegion_id\"
			FROM
			    v_MedPersonal MP
                inner join lateral (
                        select  LpuRegion_id from v_MedStaffRegion where MedPersonal_id = MP.MedPersonal_Id limit 1
                ) as MedStaffRegion
			WHERE 
			    ( (WorkData_endDate is null or WorkData_endDate > dbo.tzGetDate()) {$f})
				and (Lpu_id = :Lpu_id)
			ORDER BY MedPersonal_FIO, MedPersonal_TabCode
		";
		/*
		echo getDebugSql($sql, $queryParams);
		exit;
		*/

		$res = $this->db->query($sql, $queryParams);
		if (!is_object($res) )
			return false;

        return $res->result('array');
	} //end getMedPersonalCombo()

	/**
	 * Получение списка медицинского персонала. Для гридов и комбобоксов
	 * Используется: окно просмотра и редактирования мед. персонала
	 */
	public function getMedPersonalGrid($data)
    {
		$fromtable = "v_MedPersonal ";
		if ($data['session']['region']['nick'] == 'ufa') $fromtable = "v_MedPersonal_old ";
		$sql = "
			SELECT
				MedPersonal_id,
				coalesce(MedPersonal_TabCode, '') as \"MedPersonal_TabCode\",
				coalesce (MedPersonal_Code, '') as \"MedPersonal_Code\",
				trim(Person_SurName) || ' ' || trim(Person_FirName) || ' ' || trim(Person_SecName) as \"MedPersonal_FIO\",
				CASE WHEN WorkData_IsDlo = 1 THEN 'false' ELSE 'true' END as \"MedPersonal_IsDlo\"
			FROM
			    ".$fromtable."
			WHERE
			    Lpu_id = :Lpu_id
			ORDER BY MedPersonal_FIO, MedPersonal_TabCode
		";
		$res = $this->db->query($sql, ['Lpu_id' => $data['session']['lpu_id']]);
		if (!is_object($res))
			return false;

        return $res->result('array');
	} //end getMedPersonalGrid()
	
	/**
	 * Получение списка медицинского персонала.
	 * Используется: списки мед персонала ЛПУ
	 */
	public function getMedPersonalList($data)
    {
		$dop_where = '';
		if ( isset($data['view_one_doctor']) AND isset($data['session']['medpersonal_id']) )
		{
			$dop_where = 'AND MedPersonal_id = ' . $data['session']['medpersonal_id'];
		}
		$sql = "
			SELECT
				MedPersonal_id as \"MedPersonal_id\",
				coalesce(MedPersonal_TabCode, '') as \"MedPersonal_TabCode\",
				coalesce(MedPersonal_Code, '') as \"MedPersonal_Code\",
				trim(Person_SurName) || ' ' || trim(Person_FirName) || ' ' || trim(Person_SecName) as \"MedPersonal_FIO\",
				CASE WHEN WorkData_IsDlo = 1 THEN 'false' ELSE 'true' END as \"MedPersonal_IsDlo\"
			FROM v_MedPersonal
			WHERE (WorkData_endDate is null or WorkData_endDate > dbo.tzGetDate())
				and Lpu_id = :Lpu_id 
				{$dop_where}
		    ORDER BY MedPersonal_FIO
		";
		$res = $this->db->query($sql, ['Lpu_id' => $data['Lpu_id']]);
		if ( !is_object($res) )
			return false;

        return $res->result('array');
	} //end getMedPersonalGrid()

	/**
	 * Получение детальной информации о местах работы врача
	 * Используется: окно просмотра и редактирования мед. персонала
	 */
	public function getMedPersonalGridDetail($data)
    {
		$filters = [];
		$queryParams = [];
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
				msf.MedPersonal_id as \"MedPersonal_id\",
				MedStaffFact_id as \"MedStaffFact_id\",
				lb.LpuBuilding_id as \"LpuBuilding_id\",
				coalesce(msf.MedPersonal_TabCode, '') as \"MedPersonal_TabCode\",
				rtrim(msf.Person_FIO) as \"MedPersonal_FIO\",
				ps.PostMed_Name as \"PostMed_Name\",
				-- структурное подразделение https://redmine.swan.perm.ru/issues/5424#note-9
				coalesce(LpuSection_Name,LpuUnit_Name, lb.LpuBuilding_Name, Lpu_Nick) as \"LpuSection_Name\",
				MedStaffFact_Stavka as \"MedStaffFact_Stavka\",
				lu.LpuUnit_Name as \"LpuUnit_Name\",
				to_char(cast(WorkData_begDate as timestamp), 'dd.mm.yyyy') as \"MedStaffFact_setDate\",
				to_char(cast(WorkData_endDate as timestamp), 'dd.mm.yyyy') as \"MedStaffFact_disDate\"
				
			FROM
			    v_MedStaffFact msf
                LEFT JOIN v_Lpu Lpu on Lpu.Lpu_id=msf.Lpu_id
                
                LEFT JOIN v_LpuBuilding lb on lb.LpuBuilding_id=msf.LpuBuilding_id
                LEFT JOIN v_LpuUnit lu on lu.LpuUnit_id=msf.LpuUnit_id
                LEFT JOIN v_LpuSection ls on ls.LpuSection_id=msf.LpuSection_id
			    LEFT JOIN v_PostMed ps on ps.PostMed_id=msf.Post_id
			".ImplodeWhere($filters);

		$res = $this->db->query($sql, $queryParams);
		if (! is_object($res) )
			return false;

        return $res->result('array');
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
		
		$filters = [];
		$filters[] = "(1=1) ";
		$queryParams = [];
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
			$filters[] = "msf.Person_FirName iLIKE (:Search_FirName||'%')";
		}
				
		if (isset($data['Search_SecName']) and !empty($data['Search_SecName'])) {
			$queryParams['Search_SecName'] = rtrim($data['Search_SecName']);
			$filters[] = "msf.Person_SecName iLIKE (:Search_SecName||'%')";
		}
				
		if (isset($data['Search_SurName']) and !empty($data['Search_SurName'])) {
			$queryParams['Search_SurName'] = rtrim($data['Search_SurName']);
			$filters[] = "msf.Person_SurName iLIKE (:Search_SurName||'%')";
		}
		
		$orderby = "";
		if ( !empty($data['sort']) && !empty($data['dir']) ) {
			switch ($data['sort']) {
				case 'MedPersonal_TabCode':
					$data['sort'] = "coalesce(msf.MedPersonal_TabCode::varchar, '')";
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
				msf.MedPersonal_id as \"MedPersonal_id\",
				MedStaffFact_id as \"MedStaffFact_id\",
				coalesce(msf.MedPersonal_TabCode::varchar, '') as \"MedPersonal_TabCode\",
				rtrim(msf.Person_FIO) as \"MedPersonal_FIO\",
				ps.PostMed_Name as \"PostMed_Name\",
				-- структурное подразделение https://redmine.swan.perm.ru/issues/5424#note-9
				coalesce(LpuSection_Name,LpuUnit_Name, Lpu_Nick) as \"LpuSection_Name\",
				MedStaffFact_Stavka as \"MedStaffFact_Stavka\",
				lu.LpuUnit_Name as \"LpuUnit_Name\",
				to_char(cast(WorkData_begDate as timestamp), 'dd.mm.yyyy') as \"MedStaffFact_setDate\",
				to_char(cast(WorkData_endDate as timestamp), 'dd.mm.yyyy') as \"MedStaffFact_disDate\"
			-- end select
			FROM
			-- from 
				v_MedStaffFact msf
				LEFT JOIN v_Lpu Lpu on Lpu.Lpu_id=msf.Lpu_id
				LEFT JOIN v_LpuUnit lu on lu.LpuUnit_id=msf.LpuUnit_id
				LEFT JOIN v_LpuSection ls on ls.LpuSection_id=msf.LpuSection_id
				LEFT JOIN v_PostMed ps on ps.PostMed_id=msf.Post_id
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
		
		if (is_object($res_count)) {
			$cnt_arr = $res_count->result('array');
			$count = $cnt_arr[0]['cnt'];
			unset($cnt_arr);
		} else {
			$count = 0;
		}		
		
		if ( !is_object($res) )
			return false;

        $response = [];
        $response['data'] = $res->result('array');
        $response['totalCount'] = $count;
        return $response;
	} //end getMedPersonalGridPaged()
	
	
	/**
	 * Получение детальной информации о местах работы врача
	 * Используется: окно просмотра и редактирования мест работы мед. персонала
	 */
	public function getMedStaffGridDetail($data)
    {
		$filters = [];
		$queryParams = [];
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
				msf.MedPersonal_id as \"MedPersonal_id\",
				MedStaffFact_id as \"MedStaffFact_id\",
				coalesce (msf.MedPersonal_TabCode, '') as \"MedPersonal_TabCode\",
				rtrim(msf.Person_FIO) as \"MedPersonal_FIO\",
				ps.PostMed_Name as \"PostMed_Name\",
				-- структурное подразделение https://redmine.swan.perm.ru/issues/5424#note-9
				coalesce(LpuSection_Name,LpuUnit_Name, Lpu_Nick) as LpuSection_Name as \"LpuSection_Name\",
				MedStaffFact_Stavka as \"MedStaffFact_Stavka\",
				lu.LpuUnit_Name as \"LpuUnit_Name\",
				to_char(cast(WorkData_begDate as timestamp), 'dd.mm.yyyy') as \"setDate\",
				to_char(cast(WorkData_endDate as timestamp), 'dd.mm.yyyy') as \"disDate\"
			FROM 
		        v_MedStaffFact msf
                LEFT JOIN v_Lpu Lpu on Lpu.Lpu_id=msf.Lpu_id
                LEFT JOIN v_LpuUnit lu on lu.LpuUnit_id=msf.LpuUnit_id
                LEFT JOIN v_LpuSection ls on ls.LpuSection_id=msf.LpuSection_id
                LEFT JOIN v_PostMed ps on ps.PostMed_id=msf.Post_id
			--LEFT JOIN persis.post ps with (nolock) on ps.id=msf.Post_id
			".ImplodeWhere($filters);
			/*
			echo getDebugSql($sql, $queryParams);
			exit;
			*/
		$res = $this->db->query($sql, $queryParams);
		if ( !is_object($res) )
			return false;

        return $res->result('array');
	} //end getMedStaffGridDetail()

	/**
	 * Список мест работы для Уфы из старого ЕРМП
	 */
	public function getMedStaffGridDetail_Ufa_Old_ERMP($data)
    {
		$filters = [];
		$queryParams = [];
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
				msf.MedPersonal_id as \"MedPersonal_id\",
				MedStaffFact_id as \"MedStaffFact_id\",
				coalesce (msf.MedPersonal_TabCode, '') as \"MedPersonal_TabCode\",
				rtrim(msf.Person_FIO) as \"MedPersonal_FIO\",
				ps.PostMed_Name as \"PostMed_Name\",
				-- структурное подразделение https://redmine.swan.perm.ru/issues/5424#note-9
				coalesce(LpuSection_Name,LpuUnit_Name, Lpu_Nick) as \"LpuSection_Name\",
				MedStaffFact_Stavka as \"MedStaffFact_Stavka\",
				lu.LpuUnit_Name as \"LpuUnit_Name\",
				to_char(cast(WorkData_begDate as timestamp), 'dd.mm.yyyy') as \"setDate\",
				to_char(cast(WorkData_endDate as timestamp), 'dd.mm.yyyy') as \"disDate\"
			FROM
			    v_MedStaffFact_old msf
                LEFT JOIN v_Lpu Lpu on Lpu.Lpu_id=msf.Lpu_id
                LEFT JOIN v_LpuUnit lu on lu.LpuUnit_id=msf.LpuUnit_id
                LEFT JOIN v_LpuSection ls on ls.LpuSection_id=msf.LpuSection_id
                LEFT JOIN v_PostMed ps on ps.PostMed_id=msf.Post_id
			".ImplodeWhere($filters);
		/*
		   echo getDebugSql($sql, $queryParams);
		   exit;
		   */
		$res = $this->db->query($sql, $queryParams);
		if (!is_object($res))
			return false;

        return $res->result('array');
	} //end getMedStaffGridDetail()

	/**
	 * Получение детальной информации о строках штатного расписания
	 * Используется: окно просмотра и редактирования строк шатного расписания
	 */
	public function getStaffTTGridDetail($data)
    {
		$filters = [];
		$queryParams = [];
		
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
				st.id as \"Staff_id\",
				-- Структурный элемент ЛПУ
				CASE
					WHEN ls.LpuSection_id IS NOT NULL THEN rtrim(ls.LpuSection_Name)
					WHEN lu.LpuUnit_id IS NOT NULL THEN rtrim(lu.LpuUnit_Name)
					WHEN lb.LpuBuilding_id IS NOT NULL THEN rtrim(lb.LpuBuilding_Name)
					WHEN l.Lpu_id IS NOT NULL THEN rtrim(l.Lpu_Nick)
				END as \"StructElement_Name\",
				-- Должность
				rtrim(pst.name) as \"Post_Name\",
				-- Вид Мп
				mck.name as \"MedicalCareKind_Name\",
				-- Дата создания	
				to_char(cast(st.BeginDate as timestamp), 'dd.mm.yyyy') as \"BeginDate\",
				-- Комментарий
				rtrim(st.Comments) as \"Staff_Comment\",
				-- Количество ставок
				st.Rate as \"Staff_Rate\",
				-- Из них занято
				coalesce(RateTotal.RateSum, 0) as \"Staff_RateSum\",
				-- Количество сотрудников
				RateTotal.RateCount as \"Staff_RateCount\",
				st.Lpu_id as \"Lpu_id\",
				st.LpuBuilding_id as \"LpuBuilding_id\",
				st.LpuUnit_id as \"LpuUnit_id\",
				st.LpuSection_id as \"LpuSection_id\"
			from
				persis.v_Staff st
				left join persis.Post as pst on pst.id = st.Post_id
				left join v_Lpu as l on l.Lpu_id = st.Lpu_id
				left join v_LpuBuilding as lb on lb.LpuBuilding_id = st.LpuBuilding_id
				left join v_LpuUnit as lu on lu.LpuUnit_id = st.LpuUnit_id
				left join v_LpuSection as ls on ls.LpuSection_id = st.LpuSection_id
				left join persis.MedicalCareKind as mck on mck.id = st.MedicalCareKind_id
				left join lateral (
					select
					    sum(Rate) as RateSum,
					    COUNT(wp.id) as RateCount
					from
					    persis.WorkPlace wp
					where
					    wp.Staff_id = st.id
					    and (wp.EndDate is null or wp.EndDate >= dbo.tzGetDate())
					    and not exists (
							select
							    sp.id
							from
							    persis.SkipPayment sp
								inner join persis.SkipPaymentReason spr on spr.id = sp.SkipPaymentReason_id
							where sp.WorkPlace_id = wp.id
								and dbo.tzGetDate() between sp.StartDate and sp.EndDate
								and spr.code in (1,2,3)
							limit 1
						)
				) RateTotal on true
			".ImplodeWhere($filters);
		//echo getDebugSql($sql, $queryParams);exit;
		$res = $this->db->query($sql, $queryParams);
		if ( !is_object($res) )
			return false;
        return $res->result('array');
	} //end getStaffTTGridDetail()

	/**
	 * Удаление места работы врача
	 * Используется: окно просмотра и редактирования мед. персонала
	 */
	public function dropMedStaffFact($data) {
		$sql = "
			select
			    Error_Code as \"Error_Code\", 
			    Error_Message as \"Error_Msg\"
			from p_MedStaffFact_del
			(
				MedStaffFact_id = :MedStaffFact_id			
			)
			";

		$res = $this->db->query($sql, ['MedStaffFact_id' => $data['MedStaffFact_id']]);
		if ( !is_object($res) )
			return false;

        return $res->result('array');
	} //end dropMedStaffFact()
	
	/**
	 * Проверка привязан ли врач к отделению
	 */
	public function checkIfLpuSectionExists($data)
	{
		if ( !isset($data['LpuSection_idEdit']) || !isset($data['Lpu_id']) || !isset($data['MedPersonal_idEdit']) )
			return false;
			
		$params = [
		    'Lpu_id' => $data['Lpu_id'],
            'LpuSection_id' => $data['LpuSection_idEdit'],
            'MedPersonal_id' => $data['MedPersonal_idEdit']
        ];

		$filter_for_edit_action = "";
		if ( isset($data['MedStaffFact_idEdit']) && $data['MedStaffFact_idEdit'] > 0 )
		{
			$filter_for_edit_action = " and MedStaffFact_id <> :MedStaffFact_idEdit ";
			$params['MedStaffFact_idEdit'] = $data['MedStaffFact_idEdit'];
		}
			
		$sql = "
			SELECT 
				count(*) as cnt
			FROM
				MedStaffFact
			WHERE
				Lpu_id = :Lpu_id
            and
                LpuSection_id = :LpuSection_id
            and
                MedPersonal_id = :MedPersonal_id
				" . $filter_for_edit_action . "
		";
		$res = $this->db->query($sql, $params );
		if ( !is_object($res) )
			return false;

        $result = $res->result('array');

        return  $result[0]['cnt'] > 0;
	}

	/**
	 * Добавление новой записи о месте работы врача
	 * Используется: окно просмотра и редактирования мед. персонала
	 */
	public function insertMedStaffFact($data)
    {
		$params = [];
		$params['Lpu_id'] = $data['Lpu_id'];
		$params['Server_id'] = $data['Server_id'];
		$params['LpuSection_id'] = $data['LpuSection_idEdit'];
		$params['MedPersonal_id'] = $data['MedPersonal_idEdit'];
		$params['MedStaffFact_Stavka'] = $data['MedStaffFact_StavkaEdit'];
		$params['MedStaffFact_IsSpecialist'] = $data['MedStaffFact_IsSpecialistEdit'];
		$params['MedStaffFact_IsOMS'] = $data['MedStaffFact_IsOMSEdit'];
		$params['MedSpecOms_id'] = $data['MedSpecOms_id'];
		$params['MedStaffFact_setDate'] = $data['MedStaffFact_setDateEdit'];
		$params['MedStaffFact_disDate'] = $data['MedStaffFact_disDateEdit'];
		$params['MedSpec_id'] = $data['MedSpec_idEdit'];
		$params['PostMed_id'] = $data['PostMed_idEdit'];
		$params['PostMedClass_id'] = $data['PostMedClass_idEdit'];
		$params['PostMedType_id'] = $data['PostMedType_idEdit'];
		$params['PostMedCat_id'] = $data['PostMedCat_idEdit'];
		$params['RecType_id'] = $data['RecType_id'];
		$params['MedStaffFact_PriemTime'] = isset($data['MedStaffFact_PriemTime'])?$data['MedStaffFact_PriemTime']:null;
		$params['MedStatus_id'] = $data['MedStatus_id'];
		$params['MedStaffFact_PriemTime'] = ($data['MedStaffFact_PriemTime'] == "") ? 1 : 2;
		$params['MedStaffFact_IsQueueOnFree'] = ($data['MedStaffFact_IsQueueOnFree'] == "") ? 1 : 2;
		$params['MedStaffFact_Descr'] = $data['MedStaffFact_Descr'];
		$params['MedStaffFact_Contacts'] = $data['MedStaffFact_Contacts'];
		$params['pmUser_id'] = $data['pmUser_id'];

		getSQLParams($params);
		$sql = "
            select
                MedStaffFact_id as \"MedStaffFact_id\",
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\"
			from p_MedStaffFact_ins
			(
				Lpu_id := :Lpu_id,
				Server_id := :Server_id,
				LpuSection_id := :LpuSection_id,
				MedPersonal_id := :MedPersonal_id,
				MedStaffFact_Stavka := :MedStaffFact_Stavka,
				MedStaffFact_IsSpecialist := :MedStaffFact_IsSpecialist,
				MedStaffFact_IsOMS := :MedStaffFact_IsOMS,
				MedSpecOms_id := :MedSpecOms_id,
				MedStaffFact_setDate := :MedStaffFact_setDate,
				MedStaffFact_disDate := :MedStaffFact_disDate,
				MedSpec_id := :MedSpec_id,
				PostMed_id := :PostMed_id,
				PostMedClass_id := :PostMedClass_id,
				PostMedType_id := :PostMedType_id,
				PostMedCat_id := :PostMedCat_id,
				RecType_id := :RecType_id,
				MedStaffFact_PriemTime := :MedStaffFact_PriemTime,
				MedStatus_id := :MedStatus_id,
				MedStaffFact_IsDirRec := :MedStaffFact_IsDirRec,
				MedStaffFact_IsQueueOnFree := :MedStaffFact_IsQueueOnFree,
				MedStaffFact_Descr := :MedStaffFact_Descr,
				MedStaffFact_Contacts := :MedStaffFact_Contacts,
				pmUser_id := :pmUser_id
			)
			";
		$result = $this->db->query($sql, $params);

		if (! is_object($result) )
			return false;

        return $result->result('array');
	} //end insertMedStaffFact()


	/**
	 * Изменение записи о месте работы врача
	 * Используется: окно просмотра и редактирования мед. персонала
	 */
	public function updateMedStaffFact($data)
    {
		$params = [];
		$params['MedStaffFact_id'] = $data['MedStaffFact_idEdit'];
		$params['Lpu_id'] = $data['Lpu_id'];
		$params['Server_id'] = $data['Server_id'];
		$params['LpuSection_id'] = $data['LpuSection_idEdit'];
		$params['MedPersonal_id'] = $data['MedPersonal_idEdit'];
		$params['MedStaffFact_Stavka'] = $data['MedStaffFact_StavkaEdit'];
		$params['MedStaffFact_IsSpecialist'] = $data['MedStaffFact_IsSpecialistEdit'];
		$params['MedStaffFact_IsOMS'] = $data['MedStaffFact_IsOMSEdit'];
		$params['MedSpecOms_id'] = $data['MedSpecOms_id'];
		$params['MedStaffFact_setDate'] = $data['MedStaffFact_setDateEdit'];
		$params['MedStaffFact_disDate'] = $data['MedStaffFact_disDateEdit'];
		$params['MedSpec_id'] = $data['MedSpec_idEdit'];
		$params['PostMed_id'] = $data['PostMed_idEdit'];
		$params['PostMedClass_id'] = $data['PostMedClass_idEdit'];
		$params['PostMedType_id'] = $data['PostMedType_idEdit'];
		$params['PostMedCat_id'] = $data['PostMedCat_idEdit'];
		$params['RecType_id'] = $data['RecType_id'];
		$params['MedStaffFact_PriemTime'] = isset($data['MedStaffFact_PriemTime'])?$data['MedStaffFact_PriemTime']:null;
		$params['MedStatus_id'] = $data['MedStatus_id'];
		$params['MedStaffFact_IsDirRec'] = ($data['MedStaffFact_IsDirRec'] == "") ? 1 : 2;
		$params['MedStaffFact_IsQueueOnFree'] = ($data['MedStaffFact_IsQueueOnFree'] == "") ? 1 : 2;
		$params['MedStaffFact_Descr'] = $data['MedStaffFact_Descr'];
		$params['MedStaffFact_Contacts'] = $data['MedStaffFact_Contacts'];
		$params['pmUser_id'] = $data['pmUser_id'];

		getSQLParams($params);
		$sql = "
            select 
             MedStaffFact_id as \"MedStaffFact_id\",
             Error_Code as \"Error_Code\",
             Error_Message as \"Error_Msg\"
			from p_MedStaffFact_upd
			(
				MedStaffFact_id := :MedStaffFact_id,
				Lpu_id := :Lpu_id,
				Server_id := :Server_id,
				LpuSection_id := :LpuSection_id,
				MedPersonal_id := :MedPersonal_id,
				MedStaffFact_Stavka := :MedStaffFact_Stavka,
				MedStaffFact_IsSpecialist := :MedStaffFact_IsSpecialist,
				MedStaffFact_IsOMS := :MedStaffFact_IsOMS,
				MedSpecOms_id := :MedSpecOms_id,
				MedStaffFact_setDate := :MedStaffFact_setDate,
				MedStaffFact_disDate := :MedStaffFact_disDate,
				MedSpec_id := :MedSpec_id,
				PostMed_id := :PostMed_id,
				PostMedClass_id := :PostMedClass_id,
				PostMedType_id := :PostMedType_id,
				PostMedCat_id := :PostMedCat_id,
				RecType_id := :RecType_id,
				MedStaffFact_PriemTime := :MedStaffFact_PriemTime,
				MedStatus_id := :MedStatus_id,
				MedStaffFact_IsDirRec := :MedStaffFact_IsDirRec,
				MedStaffFact_IsQueueOnFree := :MedStaffFact_IsQueueOnFree,
				MedStaffFact_Descr := :MedStaffFact_Descr,
				MedStaffFact_Contacts := :MedStaffFact_Contacts,
				pmUser_id := :pmUser_id
			)
			";
		$result = $this->db->query($sql, $params);
		if ( !is_object($result) )
			return false;

        return $result->result('array');
	} //end updateMedStaffFact()


	/**
	 * Проверка на существование врача с заданным $medpersonal_id в БД. Используется при перекэшировании данных пользователей
	 */
	function checkMedPersonalExist($medpersonal_id)
    {
		$sql = "
            select
                MedPersonal_id as \"MedPersonal_id\"
            from
                v_MedPersonal
            where
                MedPersonal_id = :MedPersonal_id
            limit 1
        ";
		$params = ['MedPersonal_id' => $medpersonal_id];
		
		$result = $this->db->query($sql, $params);
		if ( !is_object($result) )
		    return false;

        $resp = $result->result('array');
        return count($resp) > 0;
	}

    /**
     * Загрузка списка медицинского персонала
     *
     * @param array $data Фильтры
     * @param boolean $dloonly Загружать только врачей ЛЛО?
     * @return bool
     */
	public function loadMedPersonalList( $data, $dloonly = false )
    {
		$is_pg = $this->db->dbdriver == 'postgre' ? true : false;

		$filters = [];
		$queryParams = [];

		if ( $data['Lpu_id'] > 0 && !isFarmacy() ) {
		    $filters[] = "MSF.Lpu_id = :Lpu_id";
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}

		if ($data['LpuSection_id'] > 0 ) {
		    $filters[] = "MSF.LpuSection_id = :LpuSection_id";
			$queryParams[ 'LpuSection_id' ] = $data[ 'LpuSection_id' ];
		} else if ( isFarmacy() && isset( $data[ 'session' ][ 'OrgFarmacy_id' ] ) ) {
			//TODO: выборка отделений конечно некорректная, в будущем необходимо уточнить критерии

			$filters[] = "MSF.LpuSection_id in (
			        select
			            LpuSection_id
			        from
			            dbo.Contragent
			        where
			            OrgFarmacy_id = :OrgFarmacy_id
			        or
			            OrgFarmacy_id is null
			)";
			$queryParams[ 'OrgFarmacy_id' ] = $data[ 'session' ][ 'OrgFarmacy_id' ];
		}

		if ( $data[ 'LpuUnit_id' ] > 0 ) {
		    $filters[] = "MSF.LpuUnit_id = :LpuUnit_id";
			$queryParams[ 'LpuUnit_id' ] = $data[ 'LpuUnit_id' ];
		}

		if ( $data[ 'MedPersonal_id' ] > 0 ) {
		    $filters[] = "MSF.MedPersonal_id = :MedPersonal_id";
			$queryParams[ 'MedPersonal_id' ] = $data[ 'MedPersonal_id' ];
		}

		if ( $dloonly ) {
			// @todo Для Уфы пока не переводим запросы
			if ( $data[ 'session' ][ 'region' ][ 'nick' ] == 'ufa' ) {
				$filters[] = "coalesce(MSF.MedPersonal_TabCode, '0') != '0'";
			} else {
				$filters[] = "COALESCE(MSF.MedPersonal_Code, '0') != '0'";
			}
		}

		if ( $data[ 'onlyWorkInLpu' ] ) {
		    $filters[] = "MSF.WorkData_begDate is not null and MSF.WorkData_begDate <= dbo.tzGetDate()";
            $filters[] = "(MSF.WorkData_endDate is null or MSF.WorkData_endDate > dbo.tzGetDate())";
		}
		
        $sql = "
            SELECT DISTINCT
                MSF.MedPersonal_id as \"MedPersonal_id\",
                trim(COALESCE(MSF.MedPersonal_TabCode, '0')) as \"MedPersonal_Code\",
                trim(MSF.Person_Fio) as \"MedPersonal_Fio\",
                MSF.LpuSection_id as \"LpuSection_id\"
            FROM
                dbo.v_MedStaffFact MSF
                ".ImplodeWhere( $filters )."
            ORDER BY
               MSF.Person_Fio
        ";

		$query = $this->db->query( $sql, $queryParams );

		if ( !is_object( $query ) )
		    return false;

		return $query->result_array();
	}

	/**
	 * Получение списка медицинского персонала по заданными фильтрами
	 * @param array $data Фильтры
	 * @return array Ассоциативный массив с данными медицинского персонала
	 */
	function loadMedStaffFactList($data)
    {
		$filter = "(1 = 1)";
		$filter_st = "(1 = 1)";
		$queryParams = [];

        //http://redmine.swan.perm.ru/issues/14521
        if (!empty($data['ignoreDisableInDocParam'])) {
            $IsDisableInDocFilter = '';
        } else {
            $IsDisableInDocFilter = 'and coalesce(MSF.MedStaffFactCache_IsDisableInDoc, 1) = 1';
        }

		$queryParams['Lpu_id'] = $data['Lpu_id'];

		if (isset($data['Lpu_id']) && $data['Lpu_id'] > 0) {
			if (isFarmacy() && isset($data['session']['OrgFarmacy_id'])) {
				//TODO: выборка отделений конечно некорректная, в будущем необходимо уточнить критерии
				$filter .= " and LS.LpuSection_id in (select LpuSection_id from Contragent where OrgFarmacy_id = :OrgFarmacy_id or OrgFarmacy_id is null)";
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
			$filter .= " and (PK.code is null or coalesce(PK.code, 0) = :PostMedType_Code)";
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
				,to_char(MSF.WorkData_dlobegDate, 'dd.mm.yyyy') as \"WorkData_dloBegDate\"
				,to_char(MSF.WorkData_dloendDate, 'dd.mm.yyyy') as \"WorkData_dloEndDate\"
			";
		} else {
			$fields = "
				,null as \"WorkData_dloBegDate\"
				,null as \"WorkData_dloEndDate\"
			";
		}
		
		$adminpersonal = "";
		
		if ( !empty($data['loadAdminPersonal']) ) {
			$adminpersonal = "
				union all
				-- административный персонал
				select distinct
					MSF.MedStaffFact_id as \"MedStaffFact_id\",
					MSF.MedPersonal_id as \"MedPersonal_id\",
					coalesce(trim(MSF.MedPersonal_TabCode), '') as \"MedPersonal_TabCode\",
					coalesce(trim(MSF.MedPersonal_Code), '') as \"MedPersonal_DloCode\",
					trim(MSF.Person_FIO) as \"MedPersonal_Fio\",
					'[' || trim(LS.LpuSection_Code) || '. ' || trim(LS.LpuSection_Name) || ']' as \"LpuSection_Name\",
					coalesce(LSP.LpuSectionProfile_Code, '') as \"LpuSectionProfile_Code\",
					coalesce(LSP.LpuSectionProfile_SysNick, '') as \"LpuSectionProfile_SysNick\",
					coalesce(LUT.LpuUnitType_Code, '') as \"LpuUnitType_Code\",
					coalesce(LUT.LpuUnitType_SysNick, '') as \"LpuUnitType_SysNick\",
					MSF.Lpu_id as \"Lpu_id\",
					LU.LpuBuilding_id as \"LpuBuilding_id\",
					LU.LpuUnit_id as \"LpuUnit_id\",
					LU.LpuUnitSet_id as \"LpuUnitSet_id\",
					LS.LpuSection_id as \"LpuSection_id\",
					to_char(LS.LpuSection_disDate, 'dd.mm.yyyy') as \"LpuSection_disDate\",
					to_char(LS.LpuSection_setDate, 'dd.mm.yyyy') as \"LpuSection_setDate\",
					to_char(MSF.WorkData_begDate, 'dd.mm.yyyy') as \"WorkData_begDate\",
					to_char(MSF.WorkData_endDate, 'dd.mm.yyyy') as \"WorkData_endDate\",
					PK.id as \"PostKind_id\",
					p.code as \"PostMed_Code\"
					" . $fields . "
				from v_MedStaffFact MSF 
					LEFT join v_LpuSection LS on LS.LpuSection_id = MSF.LpuSection_id
					LEFT join v_LpuSectionProfile LSP on LSP.LpuSectionProfile_id = LS.LpuSectionProfile_id 
					LEFT join v_LpuUnit LU on LU.LpuUnit_id = LS.LpuUnit_id 
					LEFT join v_LpuUnitType LUT on LUT.LpuUnitType_id = LU.LpuUnitType_id 
					left join persis.PostKind PK on PK.id = MSF.PostKind_id
					LEFT JOIN persis.Post p on p.id = msf.Post_id
					inner join persis.FRMPPost fp on fp.id = p.frmpEntry_id
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
					 MSF.MedStaffFact_id as \"MedStaffFact_id\",
					 MSF.MedPersonal_id as \"MedPersonal_id\",
					 coalesce(trim(MSF.MedPersonal_TabCode), '') as \"MedPersonal_TabCode\",
					 coalesce(trim(MSF.MedPersonal_Code), '') as \"MedPersonal_DloCode\",
					 trim(MSF.Person_FIO) as \"MedPersonal_Fio\",
					 '[' || trim(LS.LpuSection_Code) || '. ' || trim(LS.LpuSection_Name) || ']' as \"LpuSection_Name\",
					 coalesce (LSP.LpuSectionProfile_Code, '') as \"LpuSectionProfile_Code\",
					 coalesce (LSP.LpuSectionProfile_SysNick, '') as \"LpuSectionProfile_SysNick\",
					 coalesce (LUT.LpuUnitType_Code, '') as \"LpuUnitType_Code\",
					 coalesce (LUT.LpuUnitType_SysNick, '') as \"LpuUnitType_SysNick\",
					 MSF.Lpu_id as \"Lpu_id\",
					 LU.LpuBuilding_id as \"LpuBuilding_id\",
					 LU.LpuUnit_id as \"LpuUnit_id\",
					 LU.LpuUnitSet_id as \"LpuUnitSet_id\",
					 LS.LpuSection_id as \"LpuSection_id\",
					 to_char(LS.LpuSection_disDate, 'dd.mm.yyyy') as \"LpuSection_disDate\",
					 to_char(LS.LpuSection_setDate, 'dd.mm.yyyy') as \"LpuSection_setDate\",
					 to_char(MSF.WorkData_begDate, 'dd.mm.yyyy') as \"WorkData_begDate\",
					 to_char(MSF.WorkData_endDate, 'dd.mm.yyyy') as \"WorkData_endDate\",
					 PK.id as \"PostKind_id\",
					 p.code as \"PostMed_Code\"
					" . $fields . "
				from v_MedStaffFact MSF
					inner join v_LpuSection LS on LS.LpuSection_id = MSF.LpuSection_id
						and LS.LpuSection_setDate is not null
					inner join v_LpuSectionProfile LSP on LSP.LpuSectionProfile_id = LS.LpuSectionProfile_id
					inner join v_LpuUnit LU on LU.LpuUnit_id = LS.LpuUnit_id
					inner join v_LpuUnitType LUT on LUT.LpuUnitType_id = LU.LpuUnitType_id
					left join persis.PostKind PK on PK.id = MSF.PostKind_id
					left join persis.Post p on p.id = MSF.Post_id
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
					 MSF.MedStaffFact_id as \"MedStaffFact_id\",
					 MSF.MedPersonal_id as \"MedPersonal_id\",
					 coalesce(trim(MSF.MedPersonal_TabCode), '') as \"MedPersonal_TabCode\",
					 coalesce(trim(MSF.MedPersonal_Code), '') as \"MedPersonal_DloCode\",
					 trim(MSF.Person_FIO) as \"MedPersonal_Fio\",
					 '[' || trim(LS.LpuSection_Code) || '. ' || trim(LS.LpuSection_Name) || ']' as \"LpuSection_Name\",
					 coalesce(LSP.LpuSectionProfile_Code, '') as \"LpuSectionProfile_Code\",
					 coalesce(LSP.LpuSectionProfile_SysNick, '') as \"LpuSectionProfile_SysNick\",
					 coalesce(LUT.LpuUnitType_Code, '') as \"LpuUnitType_Code\",
					 coalesce(LUT.LpuUnitType_SysNick, '') as \"LpuUnitType_SysNick\",
					 MSF.Lpu_id as \"Lpu_id\",
					 LU.LpuBuilding_id as \"LpuBuilding_id\",
					 LU.LpuUnit_id as \"LpuUnit_id\",
					 LU.LpuUnitSet_id as \"LpuUnitSet_id\",
					 LS.LpuSection_id as \"LpuSection_id\",
					 to_char(LS.LpuSection_disDate, 'dd.mm.yyyy') as \"LpuSection_disDate\",
					 to_char(LS.LpuSection_setDate, 'dd.mm.yyyy') as \"LpuSection_setDate\",
					 to_char(MSF.WorkData_begDate, 'dd.mm.yyyy') as \"WorkData_begDate\",
					 to_char(MSF.WorkData_endDate, 'dd.mm.yyyy') as \"WorkData_endDate\",
					 PK.id as \"PostKind_id\",
					 p.code as \"PostMed_Code\"
					" . $fields . "
				from
				    v_MedStaffFact MSF
					inner join v_LpuSection LS on LS.LpuSection_id = MSF.LpuSection_id
						and LS.LpuSection_setDate is not null
					inner join v_LpuSectionProfile LSP on LSP.LpuSectionProfile_id = LS.LpuSectionProfile_id
					inner join v_LpuUnit LU on LU.LpuUnit_id = LS.LpuUnit_id
					inner join v_LpuUnitType LUT on LUT.LpuUnitType_id = LU.LpuUnitType_id
					left join persis.PostKind PK on PK.id = MSF.PostKind_id
					left join persis.Post p on p.id = MSF.Post_id
					inner join LpuDispContract LDC on LDC.Lpu_id = :Lpu_id
					inner join lateral (
						select
						    t.MedStaffFact_id 
						from
						    v_MedStaffFact t 
						where t.MedPersonal_id = MSF.MedPersonal_id 
							and t.LpuSection_id = LS.LpuSection_id 
							and t.Lpu_id = LDC.Lpu_oid
						order by
							coalesce(WorkData_endDate, dbo.tzGetDate()) desc
						limit 1
					) t on true
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

		if (! is_object($result) )
			return false;

        return $result->result('array');
	} // end loadMedStaffFactList()
	
	
	/**
	 * Получение комментария места работы врача (и типа записи)
	 */
	public function getMedStaffFactComment($data)
    {
		$sql = "
			select
				trim(msf.MedStaffFact_Descr) as \"MedStaffFact_Descr\",
				msf.MedStaffFact_updDT as \"MedStaffFact_updDT\",
				rtrim(u.pmUser_Name) as \"pmUser_Name\",
				RecType_id as \"RecType_id\",
				MSF.Lpu_id as \"Lpu_id\",
				coalesce(msf.MedStaffFact_IsDirRec, 1) as \"MedStaffFact_IsDirRec\"
			from
			    v_MedStaffFact MSF
			    left join v_pmUser u on u.pmUser_id = msf.pmUser_updID
			where MedStaffFact_id = :MedStaffFact_id
		";
		$res = $this->db->query(
			$sql, [
				'MedStaffFact_id' => $data['MedStaffFact_id']
			]
		);
		if (!is_object($res) )
			return false;

        return $res->result('array');
    } //end getMedStaffFactEditWindow()
	
	
	/**
	 * Сохранение комментария места работы врача
	 */
	public function saveMedStaffFactComment($data)
    {
		
		//Редактируем схему persis. Правильно ли?
		$sql = "
			update persis.WorkPlace
			set
				Descr = :MedStaffFact_Descr,
				updDT = dbo.tzGetDate(),
				pmUser_updID = :pmUser_id
			where
				id = :MedStaffFact_id;
				
			select
			    persis.p_WorkPlace_upd
                (
                    WorkPlace_id := :MedStaffFact_id,
                    IsReload := 0
                )
		";

        $res = $this->db->query($sql, [
                'MedStaffFact_id' => $data['MedStaffFact_id'],
                'MedStaffFact_Descr' => $data['MedStaffFact_Descr'],
                'pmUser_id' => $data['pmUser_id']
            ]);
		
		return [
			0 => ['Error_Msg' => '']
		];
	} //end getMedStaffFactEditWindow()
	
	
	/**
	 * Получение длительности времени приёма врача
	 */
	public function getMedStaffFactDuration($data)
    {
		$sql = "
			SELECT
				MedStaffFact_PriemTime as \"MedStaffFact_PriemTime\"
			from
			    v_MedStaffFact MSF
			    left join v_pmUser u on u.pmUser_id = msf.pmUser_updID
			where
			    MedStaffFact_id = :MedStaffFact_id
		";
		$res = $this->db->query($sql, [
				'MedStaffFact_id' => $data['MedStaffFact_id']
        ]);

		if (! is_object($res))
			return false;

        return $res->result('array');
	} //end getMedStaffFactEditWindow()
	
	
	/**
	 * Получение ФИО врача, к которому привязан текущий пользователь
	 */
	public function getUserMedPersonalFio($id)
    {
		if ( empty($id) || !is_numeric($id) ) {
			return '';
		}

		$sql = "
			select
			    Person_Fio as \"Person_Fio\"
			from
			    v_MedPersonal
			where
			    MedPersonal_id = :MedPersonal_id
			limti 1
		";
		$res = $this->db->query($sql, ['MedPersonal_id' => $id]);

		if ( !is_object($res) ) {
			return '';
		}

		$response = $res->result('array');

		if (is_array($response) && count($response) == 1 && !empty($response[0]['Person_Fio']) ) {
			return trim($response[0]['Person_Fio']);
		} else {
			return '';
		}

	} //end getUserMedPersonalFio()
	
	
	/**
	 * Получение списка мест работы доступных для регистратуры
	 */
	public function getMedStaffFactListForReg($data)
    {
		
		$queryParams = [];
		
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
		
		$filters[] = "(coalesce(cast(msf.Medstafffact_disDate as date), '2030-01-01') >= dbo.tzGetDate())
				and coalesce(MedStatus_id, 1) = 1 
				and (coalesce(msf.RecType_id, 6) != 6)";
		
		$sql = "
			SELECT
				MedStaffFact_id as \"MedStaffFact_id\",
				trim(Person_SurName) || ' ' || trim(Person_FirName) || ' ' || trim(Person_SecName) as \"MedPersonal_FIO\",
				rtrim(LpuSection_Name) as \"LpuSection_Name\",
				rtrim(LpuSectionProfile_Name) as \"LpuSectionProfile_Name\"
			FROM
			    v_MedStaffFact msf
			    left join v_LpuSection ls on msf.LpuSection_id = ls.LpuSection_id
            ".ImplodeWhere($filters)."
			ORDER BY MedPersonal_FIO
		";
		$res = $this->db->query($sql, $queryParams);
		if (! is_object($res) )
			return false;

        return $res->result('array');
	} //end getMedStaffFactListForReg()
	
}
// END MedPersonal_model class
