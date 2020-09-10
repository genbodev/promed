<?php defined('BASEPATH') or die ('No direct script access allowed');
class MobileBrig_model extends CI_Model {
	
	/**
	 * desc
	 */
	function __construct() {
		parent::__construct();
	}
	

	/**
	 * Получение информации о бригаде: ФИО состава, номер бригады
	 */
	function setOnlineStatus($data) {
		$query = '
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :EmergencyTeam_id;
			exec p_EmergencyTeam_setOnline
				@EmergencyTeam_id = @Res,
				@EmergencyTeam_IsOnline = :isOnline,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as CmpCallCard_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
	 * desc
	 */
	function  getEmergencyTeamData($data) {
		$queryParams = array();
		if ( !empty($data['session']['medpersonal_id']) ) {
			$queryParams['MedPersonal_id']=$data['session']['medpersonal_id'];
		}
		$query="
			select top 1
				ET.EmergencyTeam_id,
				MSF.MedStaffFact_id
			from
				v_EmergencyTeam ET with(nolock),
				v_MedStaffFact MSF with (nolock)
			where
				EmergencyTeam_HeadShift = :MedPersonal_id and
				MSF.MedPersonal_id = :MedPersonal_id
			order by EmergencyTeam_insDT desc";
		$result = $this->db->query($query, $queryParams);
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
		
	}
	
	/**
	 * desc
	 */
	function isSavedCallCard($data) {
		$query='
			select 
				count(CCC.CmpCloseCard_id) as count
			from
				v_CmpCloseCard CCC with(nolock)
			where
				CCC.CmpCallCard_id = :CmpCallCard_id
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
	 * desc
	 */
	function getBrigInfo($data) {
		$queryParams = array();
		if ( !empty($data['session']['medpersonal_id']) ) {
			$queryParams['MedPersonal_id']=$data['session']['medpersonal_id'];
		}
		if (!isset($queryParams['MedPersonal_id'])||$queryParams['MedPersonal_id']==null) {
			return false;
		}
		$query = "
			select top 1
				-- select
				ET.EmergencyTeam_id,ET.EmergencyTeamStatus_id, ET.EmergencyTeam_Driver, ET.EmergencyTeam_Assistant1, ET.EmergencyTeam_Assistant2, ET.EmergencyTeam_Num,
				HeadBrig_FIO.Person_Fio as HeadBrig_FIO,
				HeadBrig_FIO.Person_ShortFio as HeadBrig_ShortFIO,
				Assistant1_FIO.Person_Fio as Assistant1_FIO,
				Assistant2_FIO.Person_Fio as Assistant2_FIO,
				Driver_FIO.Person_Fio as Driver_FIO
				-- end select
			from
				-- from
				v_EmergencyTeam ET with(nolock)

				outer apply(
					select
						Person_FirName + ' ' +Person_SecName+ ' ' +	Person_SurName as Person_Fio,
						Person_SurName+ ' ' +SUBSTRING(Person_FirName,1,1) + '.' + SUBSTRING(Person_SecName,1,1) + '.' as Person_ShortFio
					from
						v_MedPersonal MP with (nolock)
					where
						MP.MedPersonal_id = :MedPersonal_id
				) as HeadBrig_FIO
				outer apply(
					select
						Person_Fio
					from
						v_MedPersonal MP with (nolock)
					where
						MP.MedPersonal_id = ET.EmergencyTeam_Assistant1
				) as Assistant1_FIO
				
				outer apply(
					select
						Person_Fio
					from
						v_MedPersonal MP with (nolock)
					where
						MP.MedPersonal_id = ET.EmergencyTeam_Assistant2
				) as Assistant2_FIO
				
				outer apply(
					select
						Person_Fio
					from
						v_MedPersonal MP with (nolock)
					where
						MP.MedPersonal_id = ET.EmergencyTeam_Driver
				) as Driver_FIO
				-- end from
				where
					EmergencyTeam_HeadShift = :MedPersonal_id
				order by EmergencyTeam_insDT desc
		";
		
		$result = $this->db->query($query, $queryParams);
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 * desc
	 */
	function getDiags() {
		$query = '
			select
				Diag_code as code,
				Diag_name as name,
				Diag_id as id
			from
				v_Diag with(nolock)
			where
				DiagLevel_id = 4
			';
		$result = $this->db->query($query);
		if ( is_object($result) ) {
			$result = $result->result('array');
			return $result;
		}
		else {
			return false;
		}
	}
	
	/**
	 * desc
	 */
	function getDiagsControlNumber() {
		$query = '
			select
				count(Diag_id) as controlNumber
			from
				v_Diag with(nolock)
			where
				DiagLevel_id = 4
			';
		$result = $this->db->query($query);
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 * desc
	 */
	function getFormFieldLabels() {
		
		$query = "						
			SELECT 
				CCCG.ComboName AS GroupName,
				CCCG.ComboSys,
				CCCF.ComboName,
				CCCF.isLoc,
				CCCF.CmpCloseCardCombo_id AS id,
				CCCE.ComboName as secondLevelComboName,
				CCCE.CmpCloseCardCombo_id as secondLevelId
			FROM
				CmpCloseCardCombo CCCG WITH (nolock)
				LEFT OUTER JOIN CmpCloseCardCombo CCCF with(nolock) ON (CCCG.CmpCloseCardCombo_id = CCCF.Parent_id)
				LEFT OUTER JOIN CmpCloseCardCombo CCCE with(nolock) ON (CCCF.CmpCloseCardCombo_id = CCCE.Parent_id)
			WHERE
				CCCG.Parent_id = '0'
		";
		
		
		$result = $this->db->query($query);
		
		if ( is_object($result) ) {
			$result = $result->result('array');
			$res = array();
			//var_dump($result);
			foreach ($result as /*$key =>*/ $value) {
				$res["{$value['ComboSys']}"][] = array(
					'name' => $value['ComboName'], 
					'loc'=> $value['isLoc'],
					'id'=>$value['id'],
					'legend' =>$value['GroupName'],
					'secondLevelComboName'=>$value['secondLevelComboName'],
					'secondLevelId'=>$value['secondLevelId']
				);
			}
			return $res;
		} else {
			return false;
		}
	}
	
	
	/**
	 * Установка статуса для бригады
	 */
	function setBrigStatus($data) {
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :EmergencyTeam_id;
			exec p_EmergencyTeam_setStatus
				@EmergencyTeam_id = @Res,
				@EmergencyTeamStatus_id = :EmergencyTeamStatus_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as EmergencyTeam_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result("array");
		} else {
			return false;
		}
	}	
	
	/**
	 * desc
	 */
	function getUnclosedCards($data) {
		$query = "
			select
				CCC.CmpCallCard_id
				,ISNULL(PS.Person_id,0) as Person_id
				,convert(varchar(20), cast(CCC.CmpCallCard_prmDT as datetime), 113) as CmpCallCard_prmDate
				,COALESCE(PS.Person_Surname, CCC.Person_SurName, '') + ' ' + COALESCE(PS.Person_Firname, case when rtrim(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end, '') + ' ' + COALESCE(PS.Person_Secname, case when rtrim(CCC.Person_SecName) = 'null' then '' else CCC.Person_SecName end, '') as Person_FIO
				,ISNULL(PS.Person_Surname, CCC.Person_SurName) as Person_Surname
				,ISNULL(PS.Person_Firname, CCC.Person_FirName) as Person_Firname
				,ISNULL(PS.Person_Secname, CCC.Person_SecName) as Person_Secname
				,convert(varchar(10), ISNULL(PS.Person_BirthDay, CCC.Person_BirthDay), 104) as Person_Birthday
				--,RTRIM(case when CR.CmpReason_id is not null then CR.CmpReason_Code+'. ' else '' end + ISNULL(CR.CmpReason_Name, '')) as CmpReason_Name
				,RTRIM(ISNULL(CR.CmpReason_Name, '')) as CmpReason_Name
				,RTRIM(case when CCT.CmpCallType_id is not null then CCT.CmpCallType_Code+'. ' else '' end + ISNULL(CCT.CmpCallType_Name, '')) as CmpCallType_Name
				,convert(varchar(10), cast(CCC.CmpCallCard_prmDT as datetime), 103) as CmpCallCard_prmDate
				,convert(varchar(10), cast(CCC.CmpCallCard_prmDT as datetime), 108) as CmpCallCard_prmTime
				,ISNULL(CCC.Sex_id,0) as Sex_id
				,case when DATEDIFF(yy,ISNULL(PS.Person_BirthDay, ISNULL(CCC.Person_BirthDay,'01.01.2000')),dbo.tzGetDate())>1 then AgeTypeValue.CmpCloseCardCombo_id
				else case when DATEDIFF(mm,ISNULL(PS.Person_BirthDay, CCC.Person_BirthDay),dbo.tzGetDate())>1 then AgeTypeValue.CmpCloseCardCombo_id+1
					else AgeTypeValue.CmpCloseCardCombo_id+2 end
				end as AgeType_value
				,case when DATEDIFF(yy,ISNULL(PS.Person_BirthDay, ISNULL(CCC.Person_BirthDay,'01.01.2000')),dbo.tzGetDate())>1 then 
					case when ISNULL(PS.Person_BirthDay, ISNULL(CCC.Person_BirthDay,0))=0 then ''
					else DATEDIFF(yy,ISNULL(PS.Person_BirthDay, CCC.Person_BirthDay),dbo.tzGetDate()) end
				else case when DATEDIFF(mm,ISNULL(PS.Person_BirthDay, CCC.Person_BirthDay),dbo.tzGetDate())>1 then DATEDIFF(mm,ISNULL(PS.Person_BirthDay, CCC.Person_BirthDay),dbo.tzGetDate())
							else DATEDIFF(dd,ISNULL(PS.Person_BirthDay, CCC.Person_BirthDay),dbo.tzGetDate()) end
				end as Age,
				
				ISNULL( RGN.KLRgn_FullName,'')
					+ case when SRGN.KLSubRgn_FullName is not null then ', '+SRGN.KLSubRgn_FullName else ', г.'+City.KLCity_Name end
					+ case when Town.KLTown_FullName is not null then ', '+Town.KLTown_FullName else '' end
					+ case when Street.KLStreet_FullName is not null then ', ул.'+Street.KLStreet_Name else '' end
					+ case when CCC.CmpCallCard_Dom is not null then ', д.'+CCC.CmpCallCard_Dom else '' end
					+ case when CCC.CmpCallCard_Kvar is not null then ', кв.'+CCC.CmpCallCard_Kvar else ''
				end as Adress_Name,

				CASE
					WHEN CCrT.CmpCallerType_id IS NOT NULL THEN 'Вызывает: ' + CCrT.CmpCallerType_Name
					WHEN CCC.CmpCallCard_Ktov IS NOT NULL THEN 'Вызывает: ' + CCC.CmpCallCard_Ktov
					ELSE ''
				END
				+ CASE WHEN CCC.CmpCallCard_Telf IS NOT NULL THEN 'Телефон: ' + CCC.CmpCallCard_Telf ELSE '' END as CallerInfo
			FROM
				-- from
				v_CmpCallCard CCC with (nolock)

				left join v_PersonState PS with (nolock) on PS.Person_id = CCC.Person_id
				left join v_CmpReason CR with (nolock) on CR.CmpReason_id = CCC.CmpReason_id
				--left join CmpReasonNew CR with (nolock) on CR.CmpReasonNew_id = CCC.CmpReasonNew_id
				left join v_CmpCallType CCT with (nolock) on CCT.CmpCallType_id = CCC.CmpCallType_id
				LEFT JOIN v_CmpCallerType CCrT with (nolock) on CCrT.CmpCallerType_id=CCC.CmpCallerType_id
				
				left join v_EmergencyTeam ET with (nolock) on CCC.EmergencyTeam_id = ET.EmergencyTeam_id
				left join v_KLRgn RGN with(nolock) on RGN.KLRgn_id = CCC.KLRgn_id
				
				left join v_KLSubRgn SRGN with(nolock) on SRGN.KLSubRgn_id = CCC.KLSubRgn_id
				left join v_KLCity City with(nolock) on City.KLCity_id = CCC.KLCity_id
				left join v_KLTown Town with(nolock) on Town.KLTown_id = CCC.KLTown_id
				left join v_KLStreet Street with(nolock) on Street.KLStreet_id = CCC.KLStreet_id
				outer apply (
					select top 1
						CCCC.CmpCloseCardCombo_id
					from
						v_CmpCloseCardCombo CCCC with (nolock) 
					where
						CCCC.Parent_id = 218
					order by
						CCCC.CmpCloseCardCombo_id asc
				) as AgeTypeValue
				-- end from
			where
				-- where
					CCC.EmergencyTeam_id = :EmergencyTeam_id
					and DATEDIFF(hh,cast(CCC.CmpCallCard_prmDT as date),dbo.tzGetDate()) <25
					and CCC.CmpCallCardStatusType_id = 2
					and CCC.CmpCallCard_IsOpen != 1
				-- end where
			order by
				-- order by
	
				CCC.CmpCallCard_prmDT desc
				-- end order by
			";
			
		$result = $this->db->query($query,$data);
		if ( !is_object($result) ) {
			return false;
		}
		$val = $result->result('array');
		return $val;
	}
	/**
	 * Получение профилей стационаров с экстренными койками
	 */
	
	function getClosedCards($data) {
		$query = "
			select
				CCC.CmpCallCard_id
				,ISNULL(PS.Person_id,0) as Person_id
				,convert(varchar(20), cast(CCC.CmpCallCard_prmDT as datetime), 113) as CmpCallCard_prmDate
				,COALESCE(PS.Person_Surname, CCC.Person_SurName, '') + ' ' + COALESCE(PS.Person_Firname, case when rtrim(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end, '') + ' ' + COALESCE(PS.Person_Secname, case when rtrim(CCC.Person_SecName) = 'null' then '' else CCC.Person_SecName end, '') as Person_FIO
				,ISNULL(PS.Person_Surname, CCC.Person_SurName) as Person_Surname
				,ISNULL(PS.Person_Firname, CCC.Person_FirName) as Person_Firname
				,ISNULL(PS.Person_Secname, CCC.Person_SecName) as Person_Secname
				,convert(varchar(10), ISNULL(PS.Person_BirthDay, CCC.Person_BirthDay), 104) as Person_Birthday
				,RTRIM(case when CR.CmpReason_id is not null then CR.CmpReason_Code+'. ' else '' end + ISNULL(CR.CmpReason_Name, '')) as CmpReason_Name
				,RTRIM(case when CCT.CmpCallType_id is not null then CCT.CmpCallType_Code+'. ' else '' end + ISNULL(CCT.CmpCallType_Name, '')) as CmpCallType_Name
				,convert(varchar(10), cast(CCC.CmpCallCard_prmDT as datetime), 103) as CmpCallCard_prmDate
				,convert(varchar(10), cast(CCC.CmpCallCard_prmDT as datetime), 108) as CmpCallCard_prmTime
				,ISNULL(CCC.Sex_id,0) as Sex_id
				,case when DATEDIFF(yy,ISNULL(PS.Person_BirthDay, ISNULL(CCC.Person_BirthDay,'01.01.2000')),dbo.tzGetDate())>1 then AgeTypeValue.CmpCloseCardCombo_id
				else case when DATEDIFF(mm,ISNULL(PS.Person_BirthDay, CCC.Person_BirthDay),dbo.tzGetDate())>1 then AgeTypeValue.CmpCloseCardCombo_id+1
					else AgeTypeValue.CmpCloseCardCombo_id+2 end
				end as AgeType_value
				,case when DATEDIFF(yy,ISNULL(PS.Person_BirthDay, ISNULL(CCC.Person_BirthDay,'01.01.2000')),dbo.tzGetDate())>1 then 
					case when ISNULL(PS.Person_BirthDay, ISNULL(CCC.Person_BirthDay,0))=0 then ''
					else DATEDIFF(yy,ISNULL(PS.Person_BirthDay, CCC.Person_BirthDay),dbo.tzGetDate()) end
				else case when DATEDIFF(mm,ISNULL(PS.Person_BirthDay, CCC.Person_BirthDay),dbo.tzGetDate())>1 then DATEDIFF(mm,ISNULL(PS.Person_BirthDay, CCC.Person_BirthDay),dbo.tzGetDate())
							else DATEDIFF(dd,ISNULL(PS.Person_BirthDay, CCC.Person_BirthDay),dbo.tzGetDate()) end
				end as Age,
				
				ISNULL( RGN.KLRgn_FullName,'')
					+ case when SRGN.KLSubRgn_FullName is not null then ', '+SRGN.KLSubRgn_FullName else ', г.'+City.KLCity_Name end
					+ case when Town.KLTown_FullName is not null then ', '+Town.KLTown_FullName else '' end
					+ case when Street.KLStreet_FullName is not null then ', ул.'+Street.KLStreet_Name else '' end
					+ case when CCC.CmpCallCard_Dom is not null then ', д.'+CCC.CmpCallCard_Dom else '' end
					+ case when CCC.CmpCallCard_Kvar is not null then ', кв.'+CCC.CmpCallCard_Kvar else ''
				end as Adress_Name,

				CASE
					WHEN CCrT.CmpCallerType_id IS NOT NULL THEN 'Вызывает: ' + CCrT.CmpCallerType_Name
					WHEN CCC.CmpCallCard_Ktov IS NOT NULL THEN 'Вызывает: ' + CCC.CmpCallCard_Ktov
					ELSE ''
				END
				+ CASE WHEN CCC.CmpCallCard_Telf IS NOT NULL THEN 'Телефон: ' + CCC.CmpCallCard_Telf ELSE '' END as CallerInfo
			from
				-- from
				v_CmpCallCard CCC with (nolock)

				left join v_PersonState PS with (nolock) on PS.Person_id = CCC.Person_id
				left join v_CmpReason CR with (nolock) on CR.CmpReason_id = CCC.CmpReason_id
				left join v_CmpCallType CCT with (nolock) on CCT.CmpCallType_id = CCC.CmpCallType_id
				LEFT JOIN v_CmpCallerType CCrT with (nolock) on CCrT.CmpCallerType_id=CCC.CmpCallerType_id
				
				left join v_EmergencyTeam ET with (nolock) on CCC.EmergencyTeam_id = ET.EmergencyTeam_id
				left join v_KLRgn RGN with(nolock) on RGN.KLRgn_id = CCC.KLRgn_id
				
				left join v_KLSubRgn SRGN with(nolock) on SRGN.KLSubRgn_id = CCC.KLSubRgn_id
				left join v_KLCity City with(nolock) on City.KLCity_id = CCC.KLCity_id
				left join v_KLTown Town with(nolock) on Town.KLTown_id = CCC.KLTown_id
				left join v_KLStreet Street with(nolock) on Street.KLStreet_id = CCC.KLStreet_id
				outer apply (
					select top 1
						CCCC.CmpCloseCardCombo_id
					from
						v_CmpCloseCardCombo CCCC with (nolock) 
					where
						CCCC.Parent_id = 218
					order by
						CCCC.CmpCloseCardCombo_id asc
				) as AgeTypeValue
				-- end from
			where
				-- where
					CCC.EmergencyTeam_id = :EmergencyTeam_id
					and DATEDIFF(hh,cast(CCC.CmpCallCard_prmDT as date),dbo.tzGetDate()) <25
					and CCC.CmpCallCardStatusType_id = 6
				-- end where
			order by
				-- order by
	
				CCC.CmpCallCard_prmDT desc
				-- end order by
			";
			
		$result = $this->db->query($query,$data);
		if ( !is_object($result) ) {
			return false;
		}
		$val = $result->result('array');
		return $val;
	}
	
	
	/**
	 * Получение списка подстанций СМП
	 *
	 * @return false or array
	 */
	public function loadLpu() {
		

		if ( $this->db->dbdriver == 'postgre' ) {
			$sql = "
				SELECT
					L.\"Lpu_id\",
					L.\"Lpu_Nick\"
				FROM
					dbo.\"v_Lpu\" L
				ORDER BY 
					L.\"Lpu_Nick\" asc
			";
		} else {
			$sql = "
				SELECT
					L.Lpu_id,					
					L.Lpu_Nick
				FROM
					v_Lpu L	with(nolock)
				ORDER BY Lpu_Nick asc
			";
		}
		
		$query = $this->db->query($sql,array());
		if ( is_object( $query ) ) {
			return $query->result_array();
		} else {
			return false;
		}
	}
	
	/**
	*Получение профилей стационаров с экстренными койками
	*/
	
	function getProfileList() {
		$queryParams = array();
		$sql = "
			SELECT DISTINCT 
				LS.LpuSectionProfile_Name,
				LS.LpuSectionProfile_OMSCode
			FROM
				v_LpuSection LS WITH (nolock)
			WHERE
				isnull(LS.LpuSectionHospType_id, 1) IN (6,7)
		";
		$result = $this->db->query($sql);
		$val = array(
			'success' => false,
			'identity' => 0,
			'Error_Code' => '',
			'Error_Msg' => 'Ошибка при получении списка профилей'
		);
		if (is_object($result)) {
			$res = $result->result('array');
			if (count($res)>0) {
				$r = array();
				foreach ($res as $row) {
					if (is_array($row))
						array_walk($row, 'ConvertFromWin1251ToUTF8');
					$r[] = $row;
				}
				$val = array(
					'success' => true,
					'Error_Code' => 0,
					'Error_Msg' => '',
					'data' => $r
				);
			} else {
				$val = array(
					'success' => true,
					'Error_Code' => 0,
					'Error_Msg' => '',
					'data' => array()
				);
			}
		}
		return array($val);
	}
	
	/**
	 * Получение всех возможных статусов
	 */
	function getStatuses() {
		$query = "
			select 
				EmergencyTeamStatus_Name,
				EmergencyTeamStatus_Code,
				EmergencyTeamStatus_Id
			from v_EmergencyTeamStatus with(nolock)
			";
		$result = $this->db->query($query);
		if ( !is_object($result) ) {
			return false;
		}
		$val = $result->result('array');
		return $val;
	}
	
	
	/**
	 * desc
	 */	
	function callAccepted($data) {
		$queryParams['CmpCallCard_id'] = $data['CmpCallCard_id'];
		//Здесь будет запрос на установку времени в таблицу закртыия карты вызова
		$query = "
				select 1
			";
		$result = $this->db->query($query,$queryParams);
		if ( !is_object($result) ) {
			return false;
		}
		$val = $result->result('array');
		return $val;
	}
}