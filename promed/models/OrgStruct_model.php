<?php
class OrgStruct_model extends swModel {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}
	
	/**
	 * Читает список расчётных счетов организации
	 */	
	function loadOrgRSchetGrid($data) 
	{
		$query = "
			SELECT
				-- select
				ORS.OrgRSchet_id,
				ORS.OrgRSchet_RSchet,
				ORST.OrgRSchetType_Name,
				OB.OrgBank_Name,
				convert(varchar(10), ORS.OrgRSchet_begDate, 104) as OrgRSchet_begDate,
				convert(varchar(10), ORS.OrgRSchet_endDate, 104) as OrgRSchet_endDate,
				OKV.Okv_Nick,
				ORS.OrgRSchet_Name
				-- end select
			FROM 
				-- from
				v_OrgRSchet ORS with (NOLOCK)
				left join v_OrgBank OB with (NOLOCK) on OB.OrgBank_id = ORS.OrgBank_id
				left join v_OrgRSchetType ORST with (NOLOCK) on ORST.OrgRSchetType_id = ORS.OrgRSchetType_id
				left join v_Okv OKV with (NOLOCK) on OKV.Okv_id = ORS.Okv_id
				-- end from
			where
				-- where
				ORS.Org_id = :Org_id
				-- end where
			order by
				-- order by
				ORS.OrgRSchet_RSchet
				-- end order by
		";
		
		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $data);
		$result_count = $this->db->query(getCountSQLPH($query), $data);

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
	 * Читает список контактных лиц организации
	 */	
	function loadOrgHeadGrid($data) 
	{
		$query = "
			SELECT
				-- select
				OH.OrgHead_id,
				RTRIM(LTRIM(ISNULL(PS.Person_Surname, '') + ' ' + ISNULL(PS.Person_Firname, '') + ' ' + ISNULL(PS.Person_Secname, ''))) as OrgHead_Fio,
				OHP.OrgHeadPost_Name,
				OH.OrgHead_Phone,
				OH.OrgHead_Mobile
				-- end select
			FROM 
				-- from
				v_OrgHead OH with (NOLOCK)
				left join v_PersonState PS with (NOLOCK) on PS.Person_id = OH.Person_id
				left join v_OrgHeadPost OHP with (NOLOCK) on OHP.OrgHeadPost_id = OH.OrgHeadPost_id
				-- end from
			where
				-- where
				OH.Org_id = :Org_id
				-- end where
			order by
				-- order by
				OHP.OrgHeadPost_Name
				-- end order by
		";
		
		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $data);
		$result_count = $this->db->query(getCountSQLPH($query), $data);

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
	 * Читает список лицензий организации
	 */	
	function loadOrgLicenceGrid($data) 
	{
		$query = "
			SELECT
				-- select
				OL.OrgLicence_id,
				OL.OrgLicence_Num,
				convert(varchar(10), OL.OrgLicence_setDate, 104) as OrgLicence_setDate,
				OL.OrgLicence_RegNum,
				convert(varchar(10), OL.OrgLicence_begDate, 104) as OrgLicence_begDate,
				convert(varchar(10), OL.OrgLicence_endDate, 104) as OrgLicence_endDate
				-- end select
			FROM 
				-- from
				v_OrgLicence OL with (NOLOCK)
				-- end from
			where
				-- where
				OL.Org_id = :Org_id
				-- end where
			order by
				-- order by
				OL.OrgLicence_Num
				-- end order by
		";
		
		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $data);
		$result_count = $this->db->query(getCountSQLPH($query), $data);

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
	 * Проверка является ли организация филиалом другой организации
	 */	
	function checkOrgFilialExist($data) {
		$query = "
			SELECT top 1
				O.Org_id
			FROM 
				v_Org O with (NOLOCK)
			where
				O.Org_id = :OrgFilial_id AND (O.Org_pid = :Org_id OR O.Org_pid IS NULL)
		";
		
		$result = $this->db->query($query, $data);

		if (is_object($result))
		{
			$resp = $result->result('array');
			if (count($resp) > 0) {
				return false; // Org_pid заполнено редактируемой организацией или пустое.
			}
		}
		
		return true; // Org_pid заполнено не редактируемой организацией
	}
	
	/**
	 * Сохранение филиала организации
	 */	
	function saveOrgFilial($data) {
		$query = "
			UPDATE
				Org
			SET 
				Org_pid = :Org_id
			WHERE 
				Org_id = :OrgFilial_id
		";
		
		// echo getDebugSql($query, $queryParams); exit();
		$result = $this->db->query($query, $data);

		return array(array('OrgFilial_id' => $data['OrgFilial_id'], 'Error_Code' => '', 'Error_Msg' => ''));
	}
	
	/**
	 * Читает список филиалов организации
	 */	
	function loadOrgFilialGrid($data)
	{
		$query = "
			SELECT
				-- select
				O.Org_id as OrgFilial_id,
				O.Org_Name as OrgFilial_Name
				-- end select
			FROM 
				-- from
				v_Org O with (NOLOCK)
				-- end from
			where
				-- where
				O.Org_pid = :Org_id
				-- end where
			order by
				-- order by
				O.Org_Name
				-- end order by
		";
		
		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $data);
		$result_count = $this->db->query(getCountSQLPH($query), $data);

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
	 * Сохранение лицензии организации
	 */	
	function saveOrgLicence($data)
	{
		if (!empty($data['OrgLicence_id']))
		{
			$proc = 'p_OrgLicence_upd';
		}
		else
		{
			$proc = 'p_OrgLicence_ins';
		}
		
		$query = '
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000),
				@OrgLicence_id bigint = :OrgLicence_id;
				
			exec ' .$proc.'
				@OrgLicence_id = @OrgLicence_id output, 
				@Org_id = :Org_id, 
				@Org_did = :Org_did,
				@OrgLicence_Ser = :OrgLicence_Ser,
				@OrgLicence_Num = :OrgLicence_Num,
				@OrgLicence_RegNum = :OrgLicence_RegNum,
				@OrgLicence_setDate = :OrgLicence_setDate,
				@OrgLicence_begDate = :OrgLicence_begDate,
				@OrgLicence_endDate = :OrgLicence_endDate,
				@Server_id = :Server_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
				select @OrgLicence_id as OrgLicence_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		';

		$result = $this->db->query($query, $data);
		
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 * Сохранение контактного лица
	 */
	function saveOrgHead($data)
	{
		if (!empty($data['OrgHead_id']))
		{
			$proc = 'p_OrgHead_upd';
		}
		else
		{
			$proc = 'p_OrgHead_ins';
		}
		
		$query = '
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000),
				@OrgHead_id bigint = :OrgHead_id;
				
			exec ' .$proc.'
				@OrgHead_id = @OrgHead_id output, 
				@Org_id = :Org_id, 
				@Person_id = :Person_id, 
				@OrgHeadPost_id = :OrgHeadPost_id,
				@OrgHead_Phone = :OrgHead_Phone,
				@OrgHead_Mobile = :OrgHead_Mobile,
				@OrgHead_Fax = :OrgHead_Fax, 
				@OrgHead_Email = :OrgHead_Email,
				@OrgHead_CommissDate = :OrgHead_CommissDate,
				@OrgHead_CommissNum = :OrgHead_CommissNum,
				@OrgHead_Address = :OrgHead_Address,
				@Lpu_id = NULL,
				@Server_id = :Server_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
				select @OrgHead_id as OrgHead_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		';

		$result = $this->db->query($query, $data);
		
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 * Сохранение структурного уровня
	 */	
	function saveOrgStruct($data) 
	{
		if (!empty($data['OrgStruct_id']))
		{
			$proc = 'p_OrgStruct_upd';
		}
		else
		{
			$proc = 'p_OrgStruct_ins';
		}
		
		$query = '
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000),
				@OrgStruct_id bigint = :OrgStruct_id;
				
			exec ' .$proc.'
				@OrgStruct_id = @OrgStruct_id output, 
				@Org_id = :Org_id, 
				@OrgStruct_pid = :OrgStruct_pid, 
				@OrgStruct_NumLevel = :OrgStruct_NumLevel,
				@OrgStruct_LeftNum = :OrgStruct_LeftNum,
				@OrgStruct_RightNum = :OrgStruct_RightNum,
				@OrgStruct_Code = :OrgStruct_Code, 
				@OrgStruct_Name = :OrgStruct_Name,
				@OrgStruct_Nick = :OrgStruct_Nick,
				@OrgStruct_begDT = :OrgStruct_begDT,
				@OrgStruct_endDT = :OrgStruct_endDT,
				@OrgStructLevelType_id = :OrgStructLevelType_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
				select @OrgStruct_id as OrgStruct_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		';

		$result = $this->db->query($query, $data);
		
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 * Чтение формы контактного лица
	 */	
	function loadOrgHeadEditForm($data)
	{
		$query = "
			SELECT TOP 1
				OH.OrgHead_id,
				OH.Person_id,
				OH.OrgHeadPost_id,
				OH.OrgHead_Phone,
				OH.OrgHead_Mobile,
				OH.OrgHead_Fax,
				OH.OrgHead_Email,
				OH.OrgHead_CommissNum,
				OH.OrgHead_Address,
				OH.Org_id,
				convert(varchar(10), OH.OrgHead_CommissDate, 104) as OrgHead_CommissDate
			FROM
				v_OrgHead OH with (NOLOCK)
			WHERE OH.OrgHead_id = :OrgHead_id
		";
		$result = $this->db->query($query, array(
			'OrgHead_id' => $data['OrgHead_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 * Чтение формы лицензии организации
	 */		
	function loadOrgLicenceEditForm($data)
	{
		$query = "
			SELECT TOP 1
				OL.OrgLicence_id,
				OL.Org_id,
				OL.OrgLicence_Ser,
				OL.OrgLicence_Num,
				OL.OrgLicence_RegNum,
				OL.Org_did,
				convert(varchar(10), OL.OrgLicence_setDate, 104) as OrgLicence_setDate,
				convert(varchar(10), OL.OrgLicence_begDate, 104) as OrgLicence_begDate,
				convert(varchar(10), OL.OrgLicence_endDate, 104) as OrgLicence_endDate
			FROM
				v_OrgLicence OL with (NOLOCK)
			WHERE OL.OrgLicence_id = :OrgLicence_id
		";
		$result = $this->db->query($query, array(
			'OrgLicence_id' => $data['OrgLicence_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 * Чтение формы структурного уровня
	 */		
	function loadOrgStructEditForm($data)
	{
		$query = "
			SELECT TOP 1
				OS.OrgStruct_id,
				OS.Org_id,
				OS.OrgStruct_pid,
				OS.OrgStruct_NumLevel,
				OS.OrgStruct_Name,
				OS.OrgStruct_Nick,
				OS.OrgStruct_Code,
				OS.OrgStructLevelType_id,
				OS.OrgStruct_LeftNum,
				OS.OrgStruct_RightNum,
				convert(varchar(10), OS.OrgStruct_begDT, 104) as OrgStruct_begDT,
				convert(varchar(10), OS.OrgStruct_endDT, 104) as OrgStruct_endDT
			FROM
				v_OrgStruct OS with (NOLOCK)
			WHERE OS.OrgStruct_id = :OrgStruct_id
		";
		$result = $this->db->query($query, array(
			'OrgStruct_id' => $data['OrgStruct_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 * Чтение формы типа службы
	 */
	function loadOrgServiceTypeEditForm($data) 
	{
		$query = "
			SELECT TOP 1
				OST.OrgServiceType_id,
				OST.OrgServiceType_Name,
				OST.OrgServiceType_Nick,
				OST.OrgServiceType_Code,
				OST.OrgServiceType_SysNick,
				OST.MedServiceType_id,
				OST.OrgType_id,
				convert(varchar(10), OST.OrgServiceType_begDT, 104) as OrgServiceType_begDT,
				convert(varchar(10), OST.OrgServiceType_endDT, 104) as OrgServiceType_endDT
			FROM
				v_OrgServiceType OST with (NOLOCK)
			WHERE OST.OrgServiceType_id = :OrgServiceType_id
		";
		$result = $this->db->query($query, array(
			'OrgServiceType_id' => $data['OrgServiceType_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 * Сохранение типа службы
	 */	
	function saveOrgServiceType($data) 
	{
		if (!empty($data['OrgServiceType_id']))
		{
			$proc = 'p_OrgServiceType_upd';
		}
		else
		{
			$proc = 'p_OrgServiceType_ins';
		}
		
		$query = '
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000),
				@OrgServiceType_id bigint = :OrgServiceType_id;
				
			exec ' .$proc.'
				@OrgServiceType_id = @OrgServiceType_id output, 
				@OrgType_id = :OrgType_id, 
				@OrgServiceType_Code = :OrgServiceType_Code, 
				@OrgServiceType_Name = :OrgServiceType_Name,
				@OrgServiceType_Nick = :OrgServiceType_Nick,
				@OrgServiceType_SysNick = :OrgServiceType_SysNick,
				@OrgServiceType_begDT = :OrgServiceType_begDT,
				@OrgServiceType_endDT = :OrgServiceType_endDT,
				@MedServiceType_id = :MedServiceType_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
				select @OrgServiceType_id as OrgServiceType_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		';

		$result = $this->db->query($query, $data);
		
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 * Чтение формы типа структурного уровня
	 */
	function loadOrgStructLevelTypeEditForm($data) 
	{
		$query = "
			SELECT TOP 1
				OSLT.OrgStructLevelType_id,
				OSLT.OrgType_id,
				OSLT.OrgStructLevelType_Name,
				OSLT.OrgStructLevelType_Nick,
				OSLT.OrgStructLevelType_SysNick,
				OSLT.OrgStructLevelType_Code,
				convert(varchar(10), OSLT.OrgStructLevelType_begDT, 104) as OrgStructLevelType_begDT,
				convert(varchar(10), OSLT.OrgStructLevelType_endDT, 104) as OrgStructLevelType_endDT,
				OSLT.OrgStructLevelType_LevelNumber
			FROM
				v_OrgStructLevelType OSLT with (NOLOCK)
			WHERE OSLT.OrgStructLevelType_id = :OrgStructLevelType_id
		";
		$result = $this->db->query($query, array(
			'OrgStructLevelType_id' => $data['OrgStructLevelType_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 * Чтение формы типа организации
	 */
	function loadOrgTypeEditForm($data) 
	{
		$query = "
			SELECT TOP 1
				OT.OrgType_id,
				OT.OrgType_Name,
				OT.OrgType_Nick,
				OT.OrgType_SysNick,
				OT.OrgType_Code,
				convert(varchar(10), OT.OrgType_begDT, 104) as OrgType_begDT,
				convert(varchar(10), OT.OrgType_endDT, 104) as OrgType_endDT
			FROM
				v_OrgType OT with (NOLOCK)
			WHERE OT.OrgType_id = :OrgType_id
		";
		$result = $this->db->query($query, array(
			'OrgType_id' => $data['OrgType_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 * Сохранение типа организации
	 */	
	function saveOrgType($data) 
	{
		if (!empty($data['OrgType_id']))
		{
			$proc = 'p_OrgType_upd';
		}
		else
		{
			$proc = 'p_OrgType_ins';
		}
		
		$query = '
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000),
				@OrgType_id bigint = :OrgType_id;
				
			exec ' .$proc.'
				@OrgType_id = @OrgType_id output, 
				@OrgType_Code = :OrgType_Code, 
				@OrgType_Name = :OrgType_Name,
				@OrgType_Nick = :OrgType_Nick,
				@OrgType_SysNick = :OrgType_SysNick,
				@OrgType_begDT = :OrgType_begDT,
				@OrgType_endDT = :OrgType_endDT,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
				select @OrgType_id as OrgType_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		';

		$result = $this->db->query($query, $data);
		
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 * Сохранение типа структурного уровня
	 */	
	function saveOrgStructLevelType($data) 
	{
		if (!empty($data['OrgStructLevelType_id']))
		{
			$proc = 'p_OrgStructLevelType_upd';
		}
		else
		{
			$proc = 'p_OrgStructLevelType_ins';
		}
		
		$query = '
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000),
				@OrgStructLevelType_id bigint = :OrgStructLevelType_id;
				
			exec ' .$proc.'
				@OrgStructLevelType_id = @OrgStructLevelType_id output, 
				@OrgType_id = :OrgType_id, 
				@OrgStructLevelType_Code = :OrgStructLevelType_Code, 
				@OrgStructLevelType_Name = :OrgStructLevelType_Name,
				@OrgStructLevelType_Nick = :OrgStructLevelType_Nick,
				@OrgStructLevelType_SysNick = :OrgStructLevelType_SysNick,
				@OrgStructLevelType_LevelNumber = :OrgStructLevelType_LevelNumber,
				@OrgStructLevelType_begDT = :OrgStructLevelType_begDT,
				@OrgStructLevelType_endDT = :OrgStructLevelType_endDT,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
				select @OrgStructLevelType_id as OrgStructLevelType_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		';

		$result = $this->db->query($query, $data);
		
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 * Читает грид структурных уровней
	 */	
	function loadOrgStructGrid($data) 
	{
		$filters = "";
		
		if (!empty($data['OrgStruct_pid'])) {
			$filters .= " AND OS.OrgStruct_pid = :OrgStruct_pid";
		} else {
			$filters .= " AND OS.OrgStruct_pid IS NULL";
		}
		
		$query = "
			SELECT
				-- select
				OS.OrgStruct_id,
				OS.OrgStruct_Code,
				OS.OrgStruct_Name,
				OS.OrgStruct_Nick,
				convert(varchar(10), OS.OrgStruct_begDT, 104) as OrgStruct_begDT,
				convert(varchar(10), OS.OrgStruct_endDT, 104) as OrgStruct_endDT,
				OSLT.OrgStructLevelType_Name
				-- end select
			FROM 
				-- from
				v_OrgStruct OS with (NOLOCK)
				left join v_OrgStructLevelType OSLT with (NOLOCK) on OSLT.OrgStructLevelType_id = OS.OrgStructLevelType_id
				-- end from
			where
				-- where
				OS.Org_id = :Org_id
				{$filters}
				-- end where
			order by
				-- order by
				OS.OrgStruct_Code
				-- end order by
		";
		
		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $data);
		$result_count = $this->db->query(getCountSQLPH($query), $data);

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
	 * Читает грид типов организаций
	 */	
	function loadOrgTypeGrid($data) 
	{
		$query = "
			SELECT
				-- select
				OT.OrgType_id,
				OT.OrgType_Name,
				OT.OrgType_Nick,
				OT.OrgType_Code,
				convert(varchar(10), OT.OrgType_begDT, 104) as OrgType_begDT,
				convert(varchar(10), OT.OrgType_endDT, 104) as OrgType_endDT
				-- end select
			FROM 
				-- from
				v_OrgType OT with (NOLOCK)
				-- end from
			where
				-- where
				(1=1)
				-- end where
			order by
				-- order by
				OT.OrgType_Code
				-- end order by
		";
		
		$result = $this->db->query($query, $data);

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
	 * Читает грид типов служб
	 */	
	function loadOrgServiceTypeGrid($data) 
	{
		$query = "
			SELECT
				-- select
				OST.OrgServiceType_id,
				OST.OrgServiceType_Name,
				OST.OrgServiceType_Nick,
				OST.OrgServiceType_Code,
				convert(varchar(10), OST.OrgServiceType_begDT, 104) as OrgServiceType_begDT,
				convert(varchar(10), OST.OrgServiceType_endDT, 104) as OrgServiceType_endDT
				-- end select
			FROM 
				-- from
				v_OrgServiceType OST with (NOLOCK)
				-- end from
			where
				-- where
				OST.OrgType_id = :OrgType_id
				-- end where
			order by
				-- order by
				OST.OrgServiceType_Code
				-- end order by
		";
		
		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $data);
		$result_count = $this->db->query(getCountSQLPH($query), $data);

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
	 * Читает комбо типов структурных уровней
	 */	
	function loadOrgStructLevelTypeList($data) {
		$filter = "(1=1)";
		
		if (!empty($data['OrgStructLevelType_id'])) {
			$filter .= " and OSLT.OrgStructLevelType_id = :OrgStructLevelType_id";
		}
		else
		{
			$filter .= " and OSLT.OrgType_id = O.OrgType_id";
			
			if (!empty($data['OrgStructLevelType_LevelNumber'])) {
				$filter .= " and OSLT.OrgStructLevelType_LevelNumber = :OrgStructLevelType_LevelNumber";
			}
		}
		
		$query = "
			SELECT
				-- select
				OSLT.OrgStructLevelType_id,
				OSLT.OrgStructLevelType_Name,
				OSLT.OrgStructLevelType_Nick,
				OSLT.OrgStructLevelType_Code,
				convert(varchar(10), OSLT.OrgStructLevelType_begDT, 104) as OrgStructLevelType_begDT,
				convert(varchar(10), OSLT.OrgStructLevelType_endDT, 104) as OrgStructLevelType_endDT,
				OSLT.OrgStructLevelType_LevelNumber
				-- end select
			FROM 
				-- from
				v_OrgStructLevelType OSLT with (NOLOCK)
				outer apply(
					select top 1 OrgType_id from v_Org with (nolock) where Org_id = :Org_id
				) O
				-- end from
			where
				-- where
				{$filter}
				-- end where
			order by
				-- order by
				OSLT.OrgStructLevelType_LevelNumber, OSLT.OrgStructLevelType_Code
				-- end order by
		";
		// echo getDebugSql($query, $data); exit();
		$result = $this->db->query($query, $data);

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
	 * Читает грид типов структурных уровней
	 */	
	function loadOrgStructLevelTypeGrid($data) 
	{
		$query = "
			SELECT
				-- select
				OSLT.OrgStructLevelType_id,
				OSLT.OrgStructLevelType_Name,
				OSLT.OrgStructLevelType_Nick,
				OSLT.OrgStructLevelType_Code,
				convert(varchar(10), OSLT.OrgStructLevelType_begDT, 104) as OrgStructLevelType_begDT,
				convert(varchar(10), OSLT.OrgStructLevelType_endDT, 104) as OrgStructLevelType_endDT,
				OSLT.OrgStructLevelType_LevelNumber
				-- end select
			FROM 
				-- from
				v_OrgStructLevelType OSLT with (NOLOCK)
				-- end from
			where
				-- where
				OSLT.OrgType_id = :OrgType_id
				-- end where
			order by
				-- order by
				OSLT.OrgStructLevelType_LevelNumber, OSLT.OrgStructLevelType_Code
				-- end order by
		";
		
		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $data);
		$result_count = $this->db->query(getCountSQLPH($query), $data);

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
	 * Получение доступных типов служб для организации
	 */		
	function getAllowedMedServiceTypes($data) 
	{
		$query = "
			select
				 MST.MedServiceType_id
			from
				v_OrgServiceType OST with (nolock)
				inner join MedServiceType MST with (nolock) on MST.MedServiceType_id = OST.MedServiceType_id
				outer apply(
					select top 1 OrgType_id from v_Org with (nolock) where Org_id = :Org_id
				) O
			where 
				OST.OrgType_id = O.OrgType_id
		";

		// echo getDebugSql($query, $queryParams); exit();

		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			$resp = $result->result('array');
			$ret = array();
			// избавляемся от ключей, чтобы отдать просто список значений.
			foreach ($resp as $oneresp) {
				$ret[] = $oneresp['MedServiceType_id'];
			}
			return $ret;
		}
		else {
			return false;
		}
	}
	
	/**
	 * Читает грид служб
	 */	
	function loadMedServiceGrid($data) 
	{
		$filters = "";
		
		if (!empty($data['OrgStruct_pid'])) {
			$filters .= " AND MS.OrgStruct_id = :OrgStruct_pid";
		} else {
			$filters .= " AND MS.OrgStruct_id IS NULL";
		}
		
		$query = "
			SELECT
				-- select
				MS.MedService_id,
				MST.MedServiceType_Name,
				MS.MedService_Name,
				MS.MedService_Nick,
				convert(varchar(10), MS.MedService_begDT, 104) as MedService_begDT,
				convert(varchar(10), MS.MedService_endDT, 104) as MedService_endDT
				-- end select
			FROM 
				-- from
				v_MedService MS with (NOLOCK)
				left join v_MedServiceType MST with (NOLOCK) on MST.MedServiceType_id = MS.MedServiceType_id
				-- end from
			where
				-- where
				MS.Org_id = :Org_id
				{$filters}
				-- end where
			order by
				-- order by
				MS.MedService_Nick
				-- end order by
		";
		
		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $data);
		$result_count = $this->db->query(getCountSQLPH($query), $data);

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
	*  Читает дерево стркутуры организации
	*/
	function loadOrgStructureTree($data) 
	{
		$queryParams = array(
			 'Org_id' => $data['Org_id'],
			 'OrgStruct_pid' => $data['OrgStruct_pid']
		);

		$filter = "";
		if (!empty($data['OrgStruct_pid'])) {
			$filter .= " and os.OrgStruct_pid = :OrgStruct_pid";
		} else {
			$filter .= " and os.OrgStruct_pid IS NULL";
		}
		
		switch ( $data['level'] ) {
			case 0:
				$query = "
					select
						 'org' + cast(o.Org_id as varchar(20)) as id
						,null as code
						,o.Org_Nick as name
						,'Org' as object
						,0 as OrgStruct_NumLevel
						,case when ucc.cnt = 0 then 1 else 0 end as leaf
					from
						v_Org o with (nolock)
						outer apply (
							select count(OrgStruct_id) as cnt
							from v_OrgStruct with (nolock)
							where Org_id = o.Org_id and OrgStruct_pid IS NULL
						) ucc
					where o.Org_id = :Org_id
					order by
						o.Org_Nick
				";
			break;

			default:
				$query = "
					select
						 'orgstruct' + cast(os.OrgStruct_id as varchar(20)) as id
						,null as code
						,os.OrgStruct_Nick as name
						,'OrgStruct' as object
						,os.OrgStruct_NumLevel as OrgStruct_NumLevel
						,case when ucc.cnt = 0 then 1 else 0 end as leaf
					from
						v_OrgStruct os with (nolock)
						outer apply (
							select count(OrgStruct_id) as cnt
							from v_OrgStruct with (nolock)
							where OrgStruct_pid = os.OrgStruct_id
						) ucc
					where 
						os.Org_id = :Org_id
						{$filter}
					order by
						os.OrgStruct_Nick
				";
			break;
		}

		// echo getDebugSql($query, $queryParams); exit();

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	*  Удаление типа структурного уровня
	*/
	function deleteOrgStructLevelType($data) 
	{
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_OrgStructLevelType_del
				@OrgStructLevelType_id = :OrgStructLevelType_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		
		$result = $this->db->query($query, array(
			'OrgStructLevelType_id' => $data['OrgStructLevelType_id']
		));
		
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	*  Удаление типа службы
	*/
	function deleteOrgServiceType($data) 
	{
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_OrgServiceType_del
				@OrgServiceType_id = :OrgServiceType_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		
		$result = $this->db->query($query, array(
			'OrgServiceType_id' => $data['OrgServiceType_id']
		));
		
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	*  Проверка, используется ли тип структурного уровня
	*/	
	function checkOrgStructLevelTypeIsUsed($data) 
	{
		$query = "
			SELECT top 1
				OS.OrgStruct_id
			FROM 
				v_OrgStruct OS with (NOLOCK)
			where
				OS.OrgStructLevelType_id = :OrgStructLevelType_id
		";
		
		$result = $this->db->query($query, $data);

		if (is_object($result))
		{
			$resp = $result->result('array');
			if (count($resp) > 0) {
				return true; // используется
			}
		}
		
		return false; // не используется
	}
	
	/**
	*  Проверка, есть ли уже с таким кодом
	*/	
	function checkCodeExist($field, $data) 
	{
		$query = "
			SELECT top 1
				F.{$field}_id
			FROM 
				v_{$field} F with (NOLOCK)
			where
				F.{$field}_Code = :{$field}_Code AND (F.{$field}_id != :{$field}_id OR :{$field}_id IS NULL)
		";
		
		$result = $this->db->query($query, $data);

		if (is_object($result))
		{
			$resp = $result->result('array');
			if (count($resp) > 0) {
				return true; // используется
			}
		}
		
		return false; // не используется
	}
	
	/**
	*  Проверка, есть ли уже с таким номером уровня
	*/	
	function checkOrgStructLevelTypeNumber($data) 
	{
		$query = "
			SELECT top 1
				OSLT.OrgStructLevelType_id
			FROM 
				v_OrgStructLevelType OSLT with (NOLOCK)
			where
				OSLT.OrgStructLevelType_LevelNumber = :OrgStructLevelType_LevelNumber AND OSLT.OrgType_id = :OrgType_id AND OSLT.OrgStructLevelType_id != :OrgStructLevelType_id
		";
		
		$result = $this->db->query($query, $data);

		if (is_object($result))
		{
			$resp = $result->result('array');
			if (count($resp) > 0) {
				return true; // используется
			}
		}
		
		return false; // не используется
	}
	
	/**
	*  Проверка, используется ли тип службы // TO-DO, пока неясно.
	*/	
	function checkOrgServiceTypeIsUsed($data) 
	{
		return false; // не используется
	}
	
	/**
	 *  Проверка, используется ли тип организации
	 */
	function checkOrgTypeIsUsed($data)
	{
		return true; // используется (добавление/удаление типов организаций только через бд)
	}

	/**
	 * Получение списка структурных уровней организации
	 */
	function loadOrgStructList($data)
	{
		$params = array('Org_id' => $data['Org_id']);

		$query = "
			select
				OS.Org_id,
				OS.OrgStruct_id,
				OS.OrgStruct_Code,
				OS.OrgStruct_Nick,
				OS.OrgStruct_Name
			from
				v_OrgStruct OS with(nolock)
			where OS.Org_id = :Org_id
		";

		$result = $this->db->query($query, $params);

		if (!is_object($result)) {
			return false;
		}
		return $result->result('array');
	}
}
?>