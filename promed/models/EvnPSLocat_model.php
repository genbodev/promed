<?php
/**
 * @comment
 */

class EvnPSLocat_model extends swModel
{
	/**
	 * @comment
	 */
	function loadPersonEvnPSLocat($data) {
		$sql = "
			select 
				PSL.PersonEvnPSLocat_id,
				PSL.EvnPS_id,
				convert(varchar ,PSL.PersonEvnPSLocat_begDate,104) as PersonEvnPSLocat_begD,
				convert(varchar(5) ,PSL.PersonEvnPSLocat_begDate,108) as PersonEvnPSLocat_begT,
				PSL.AmbulatCardLocatType_id,
				PSL.PersonEvnPSLocat_OtherLocat,
				PSL.MedStaffFact_id,
				MSF.MedPersonal_id,
				PSL.PersonEvnPSLocat_Desc
			from PersonEvnPSLocat PSL with(nolock)
			left join v_MedStaffFact MSF with(nolock) on MSF.MedStaffFact_id=PSL.MedStaffFact_id
			where PSL.PersonEvnPSLocat_id=:PersonEvnPSLocat_id
	    ";
		$params = array(
			'PersonEvnPSLocat_id' => $data['PersonEvnPSLocat_id']
		);
		$result = $this->db->query($sql, $params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 * @comment
	 */
	function savePersonEvnPSLocat($data) {
		$procedure = "p_PersonEvnPSLocat_ins";

		if (isset($data['PersonEvnPSLocat_id'])&&$data['PersonEvnPSLocat_id'] > 0) {
			$procedure = "p_PersonEvnPSLocat_upd";
		}


		$and = '';
		$params_check_doubles = array(
			'EvnPS_id'	=> $data['EvnPS_id'],
			'AmbulatCardLocatType_id' => (isset($data['AmbulatCardLocatType_id'])?$data['AmbulatCardLocatType_id']:1),
			'PersonEvnPSLocat_begDate' => ((isset($data['PersonEvnPSLocat_begD'])&&isset($data['PersonEvnPSLocat_begT']))? $data['PersonEvnPSLocat_begD'] . " " . $data['PersonEvnPSLocat_begT']:date('Y-m-d H:i'))
		);
		if(isset($data['PersonEvnPSLocat_id'])&&$data['PersonEvnPSLocat_id'] > 0)
		{
			$and = ' and PersonEvnPSLocat_id <> :PersonEvnPSLocat_id';
			$params_check_doubles['PersonEvnPSLocat_id'] = $data['PersonEvnPSLocat_id'];
		}
		$query_check_doubles = "
			select top 1 PersonEvnPSLocat_id 
			from v_PersonEvnPSLocat (nolock)
			where EvnPS_id = :EvnPS_id
			and AmbulatCardLocatType_id = :AmbulatCardLocatType_id
			and PersonEvnPSLocat_begDate = :PersonEvnPSLocat_begDate
			{$and}
		";
		$result_check_doubles = $this->db->query($query_check_doubles,$params_check_doubles);
		if(is_object($result_check_doubles))
		{
			$result_check_doubles = $result_check_doubles->result('array');
			if(is_array($result_check_doubles) && count($result_check_doubles) > 0)
			{
				return array(array('Error_Msg' => 'Запись с указанными местонахождением, датой и временем уже существует.'));
			}
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @Res = :PersonEvnPSLocat_id;
			exec " . $procedure . "
				@Server_id = :Server_id,
				@PersonEvnPSLocat_id = @Res output,
				@EvnPS_id = :EvnPS_id,
				@AmbulatCardLocatType_id = :AmbulatCardLocatType_id,
				@MedStaffFact_id = :MedStaffFact_id,
				@PersonEvnPSLocat_begDate = :PersonEvnPSLocat_begDate,
				@PersonEvnPSLocat_Desc = :PersonEvnPSLocat_Desc,
				@PersonEvnPSLocat_OtherLocat =:PersonEvnPSLocat_OtherLocat,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as PersonEvnPSLocat_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$queryParams = array(
			'Server_id' => $data['Server_id'],
			'PersonEvnPSLocat_id' => ((isset($data['PersonEvnPSLocat_id'])&&$data['PersonEvnPSLocat_id'] > 0) ? $data['PersonEvnPSLocat_id'] : NULL),
			'EvnPS_id' => $data['EvnPS_id'],
			'AmbulatCardLocatType_id' => (isset($data['AmbulatCardLocatType_id'])?$data['AmbulatCardLocatType_id']:1),
			'MedStaffFact_id' => ((isset($data['MedStaffFact_id'])&&$data['MedStaffFact_id'] > 0) ? $data['MedStaffFact_id'] : NULL),
			'PersonEvnPSLocat_begDate' =>((isset($data['PersonEvnPSLocat_begD'])&&isset($data['PersonEvnPSLocat_begT']))? $data['PersonEvnPSLocat_begD'] . " " . $data['PersonEvnPSLocat_begT']:date('Y-m-d H:i')),
			'PersonEvnPSLocat_Desc' => (isset($data['PersonEvnPSLocat_Desc'])?$data['PersonEvnPSLocat_Desc']:NULL),
			'pmUser_id' => $data['pmUser_id'],
			'PersonEvnPSLocat_OtherLocat'=>(isset($data['PersonEvnPSLocat_OtherLocat'])?$data['PersonEvnPSLocat_OtherLocat']:NULL)
		);
		//echo getDebugSQL($query, $queryParams);
		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return array(array('Error_Msg' => 'Ошибка.'));
		}
	}

	/**
	 * Проверка наличия предыдущего движения ИБ (получение врача)
	 */
	function checkPrevLocat($data){
		$sql = "
			select top 1 MSF.MedPersonal_id
			from PersonEvnPSLocat PSL with(nolock)
			left join v_MedStaffFact MSF with(nolock) on MSF.MedStaffFact_id=PSL.MedStaffFact_id
			where PSL.EvnPS_id = :EvnPS_id
			and PSL.PersonEvnPSLocat_id <> :PersonEvnPSLocat_id
		";
		$params = array(
			'EvnPS_id' => $data['EvnPS_id'],
			'PersonEvnPSLocat_id' => $data['PersonEvnPSLocat_id']
		);
		$result = $this->db->query($sql, $params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение списка юзеров, которым нужно отправить уведомление о смене местонахождения оригина на ИБ
	 */
	function getUsersForAmbulatCard($data){
		$query = "
		select
			PMUser_id
		from
			v_pmUserCache
		where
			MedPersonal_id = ?
		";
		$result = $this->db->query($query, array($data['MedPersonal_id']));
		$response = $result->result('array');
		if ( is_array($response) && count($response) > 0 ) {
			return $response;
		} else	{
			return false;
		}
	}

	/**
	 * Получение ФИО пациента, даты и нового местонахождения ИБ
	 */
	function getLocatInfo($data){
		$params = array(
			'PersonEvnPSLocat_id' => $data['PersonEvnPSLocat_id']
		);
		$query = "
			select ISNULL(PS.Person_SurName,'') + ' ' + ISNULL(PS.Person_FirName,'') + ' ' + ISNULL(PS.Person_SecName,'') as Person_FIO,
			convert(varchar ,PSL.PersonEvnPSLocat_begDate,104) as Change_Date,
			ACLT.AmbulatCardLocatType_Name as Locat_Name
			from v_PersonEvnPSLocat PSL
			inner join v_EvnPS EPS on EPS.EvnPS_id = PSL.EvnPS_id
			inner join v_PersonState PS on PS.Person_id = EPS.Person_id
			inner join v_AmbulatCardLocatType ACLT on ACLT.AmbulatCardLocatType_id = PSL.AmbulatCardLocatType_id
			where PSL.PersonEvnPSLocat_id = :PersonEvnPSLocat_id
		";
		$result = $this->db->query($query, $params);
		$response = $result->result('array');
		if ( is_array($response) && count($response) > 0 ) {
			return $response;
		} else	{
			return false;
		}
	}

	/**
	 * @comment
	 */
	function getEvnPSLocatList($data) {
		$query = "
			select 
			-- select
			PACL.PersonEvnPSLocat_id,
			convert(varchar, PACL.PersonEvnPSLocat_begDate, 120) as PersonEvnPSLocat_begDate,
			ACLT.AmbulatCardLocatType_Name as AmbulatCardLocatType,
			MSF.Person_Fio as FIO,
			post.PostMed_Name as MedStaffFact,
			PACL.PersonEvnPSLocat_Desc,
			0 as isSave
			-- end select
			from 
			-- from
			v_PersonEvnPSLocat PACL with(nolock)
			left join AmbulatCardLocattype ACLT with(nolock) on PACL.AmbulatCardLocatType_id =ACLT.AmbulatCardLocatType_id
			left join v_MedStaffFact MSF with(nolock) on MSF.MedStaffFact_id = PACL.MedStaffFact_id
			left join v_PostMed post with(nolock) on MSF.Post_id = post.PostMed_id
			-- end from
			where 
			-- where
			PACL.EvnPS_id = :EvnPS_id
			-- end where
			order by
			-- order by
			PACL.PersonEvnPSLocat_begDate desc, PACL.PersonEvnPSLocat_id
			-- end order by
		";

		$queryParams = array('EvnPS_id' => $data['EvnPS_id']);
		
		$response = array();
		
		$get_count_query = getCountSQLPH($query);						
					
		$get_count_result = $this->db->query($get_count_query, $queryParams);

		if ( is_object($get_count_result) ) {
			$response['totalCount'] = $get_count_result->result('array');
			$response['totalCount'] = $response['totalCount'][0]['cnt'];
		}
		else {
			return false;
		}
		
		if ($data['start'] >= 0 && $data['limit'] >= 0) {
			$limit_query = getLimitSQLPH($query, $data['start'], $data['limit']);
			//die(getDebugSQL($limit_query, $queryParams));
			$result = $this->db->query($limit_query, $queryParams);
		} else {
			$result = $this->db->query($query, $queryParams);
		}

		if (is_object($result)) {
			$res = $result->result('array');
			if (is_array($res)) {
				if ($data['start'] == 0 && count($res) < $data['limit']) {
					$response['data'] = $res;
					$response['totalCount'] = count($res);
				} else {
					$response['data'] = $res;
					$get_count_query = getCountSQLPH($query);

					$get_count_result = $this->db->query($get_count_query, $queryParams);


					if (is_object($get_count_result)) {
						$response['totalCount'] = $get_count_result->result('array');
						$response['totalCount'] = $response['totalCount'][0]['cnt'];
					} else {
						return false;
					}
			}
			} else {
				return false;
			}
		}
		else
			return false;
		return $response;
	}
	/**
	 * @comment
	 */
	function getEvnPSList($data) {
		$filter='(1=1)';
		$queryParams =array();
		
		if(isset($data['Person_Firname'])){
			$filter.=" and PS.Person_Firname like :Person_Firname";
			$queryParams['Person_Firname'] = $data['Person_Firname'].'%';
			//$queryParams =array('Person_Firname'=>$data['Person_Firname'].'%');
		}
		if(isset($data['Person_Secname'])){
			$filter.=" and PS.Person_Secname like :Person_Secname";
			$queryParams['Person_Secname'] = $data['Person_Secname'].'%';
			//$queryParams =array('Person_Secname'=>$data['Person_Secname'].'%');
		}
		if(isset($data['Person_Surname'])){
			$filter.=" and PS.Person_Surname like :Person_Surname";
			$queryParams['Person_Surname'] = $data['Person_Surname'].'%';
			//$queryParams =array('Person_Surname'=>$data['Person_Surname'].'%');
		}
		if(isset($data['Polis_Ser'])){
			$filter.=' and PS.Polis_Ser = :Polis_Ser';
			$queryParams['Polis_Ser'] = $data['Polis_Ser'];
			//$queryParams =array('Polis_Ser'=>$data['Polis_Ser']);
		}
		if(isset($data['Polis_Num'])){
			$filter.=' and PS.Polis_Num = :Polis_Num';
			$queryParams['Polis_Num'] = $data['Polis_Num'];
			//$queryParams =array('Polis_Num'=>$data['Polis_Num']);
		}
		/*if(isset($data['MedFIO'])){
			$filter.=" and EPSLT.Person_Fio like :MedFIO";
			$queryParams['MedFIO'] = $data['MedFIO'].'%';
			//$queryParams =array('MedFIO'=>$data['MedFIO'].'%');
		}*/
		if(isset($data['Person_BirthDay'])){
			$filter.=' and cast(PS.Person_BirthDay as date) = cast(:Person_BirthDay as date)';
			$queryParams['Person_BirthDay'] = $data['Person_BirthDay'];
			//$queryParams =array('Person_BirthDay'=>$data['Person_BirthDay']);
		}
		if(isset($data['AmbulatCardLocatType_id'])){
			$filter.=' and EPSLT.AmbulatCardLocatType_id=:AmbulatCardLocatType_id';
			$queryParams['AmbulatCardLocatType_id'] = $data['AmbulatCardLocatType_id'];
			//$queryParams =array('AmbulatCardLocatType_id'=>$data['AmbulatCardLocatType_id']);
		}
		/*if(isset($data['MedStaffFact_id'])){
			$filter.=' and EPSLT.MedStaffFact_id = :MedStaffFact_id';
			$queryParams['MedStaffFact_id'] = $data['MedStaffFact_id'];
			//$queryParams =array('MedStaffFact_id'=>$data['MedStaffFact_id']);
		}*/
		$and_filter = '';
		$apply = 'outer';
		$and_filter = 'and (1=1)';
		if($data['PEPSLW_date_range']){
			if(!empty($data['PEPSLW_date_range'][0]))
			{
				$and_filter .= ' and cast(PACL.PersonEvnPSLocat_begDate as date) >= :PersonEvnPSLocat_begDate_beg';
				$queryParams['PersonEvnPSLocat_begDate_beg'] = $data['PEPSLW_date_range'][0];
				$apply = 'cross';
			}
			if(!empty($data['PEPSLW_date_range'][1]))
			{
				$and_filter .= ' and cast(PACL.PersonEvnPSLocat_begDate as date) <= :PersonEvnPSLocat_begDate_end';
				$queryParams['PersonEvnPSLocat_begDate_end'] = $data['PEPSLW_date_range'][1];
				$apply = 'cross';
			}
		}
		/*if(isset($data['PostMed_id'])){
			$apply = 'cross';
			$and_filter .= ' and MSF.Post_id = :PostMed_id';
			$queryParams['PostMed_id'] = $data['PostMed_id'];
		}
		if(isset($data['MedFIO'])){
			$apply = 'cross';
			$filter.=" and EPSLT.Person_Fio like :MedFIO";
			$queryParams['MedFIO'] = $data['MedFIO'].'%';
			//$queryParams =array('MedFIO'=>$data['MedFIO'].'%');
		}*/
		if(isset($data['LpuSection_id']) || isset($data['MedStaffFact_id']))
		{
			$apply = 'cross';
			if(isset($data['LpuSection_id']))
			{
				$and_filter .= ' and MSF.LpuSection_id = :LpuSection_id';
				$queryParams['LpuSection_id'] = $data['LpuSection_id'];
			}
			if(isset($data['MedStaffFact_id']))
			{
				$and_filter .= ' and MSF.MedStaffFact_id = :MedStaffFact_id';
				$queryParams['MedStaffFact_id'] = $data['MedStaffFact_id'];
			}
		}
		$filter .= ' and EPS.Lpu_id = :Lpu_id';
		$queryParams['Lpu_id'] = $data['session']['lpu_id'];

		$query = "
			select 
			-- select
			ps.Person_Surname+' '+Left(ps.Person_FirName,1)+' '+Left(ps.Person_secName,1) as PersonFIO,
			convert(varchar, ps.Person_BirthDay, 104) as PersonBirthDay,
			EPS.EvnPS_id,
			EPS.EvnPS_NumCard,
			EPSLT.AmbulatCardLocatType_Name as AmbulatCardLocatType,
			/*EPSLT.Person_Fio as MedFIO*/
			(EPSLT.Person_Fio + '<br>' + EPSLT.PostMed_Name + '<br>' + EPSLT.LpuSection_Name) as MedFIO
			-- end select
			from 
			-- from
			v_EvnPS EPS with(nolock)
			inner join v_PersonState PS with(nolock) on PS.Person_id = EPS.Person_id
			{$apply} apply (
				select top 1
				PACL.MedStaffFact_id,
				ACLT.AmbulatCardLocatType_id,
				ACLT.AmbulatCardLocatType_Name,
				MSF.Person_Fio,
				MSF.Post_id,
				PM.PostMed_Name,
				LS.LpuSection_Name,
				PACL.PersonEvnPSLocat_begDate
				from v_PersonEvnPSLocat PACL with(nolock)
				left join AmbulatCardLocattype ACLT with(nolock) on PACL.AmbulatCardLocatType_id =ACLT.AmbulatCardLocatType_id
				left join v_MedStaffFact MSF with(nolock) on MSF.MedStaffFact_id = PACL.MedStaffFact_id
				left join v_PostMed PM (nolock) on PM.PostMed_id = MSF.Post_id
				left join v_LpuSection LS with (nolock) on LS.LpuSection_id = MSF.LpuSection_id
				where PACL.EvnPS_id = EPS.EvnPS_id
				{$and_filter}
				order by PACL.PersonEvnPSLocat_begDate desc, PACL.PersonEvnPSLocat_id desc
			) EPSLT
		
			-- end from
			where 
			-- where
			{$filter}
			-- end where
			order by
			-- order by
			EPS.EvnPS_id
			-- end order by
		";

	
		
		$response = array();
		
		$get_count_query = getCountSQLPH($query);						
					
		$get_count_result = $this->db->query($get_count_query, $queryParams);

		if ( is_object($get_count_result) ) {
			$response['totalCount'] = $get_count_result->result('array');
			$response['totalCount'] = $response['totalCount'][0]['cnt'];
		}
		else {
			return false;
		}
		
		if ($data['start'] >= 0 && $data['limit'] >= 0) {
			$limit_query = getLimitSQLPH($query, $data['start'], $data['limit']);
			//die(getDebugSQL($limit_query, $queryParams));
			$result = $this->db->query($limit_query, $queryParams);
		} else {
			$result = $this->db->query($query, $queryParams);
		}

		if (is_object($result)) {
			$res = $result->result('array');
			if (is_array($res)) {
				if ($data['start'] == 0 && count($res) < $data['limit']) {
					$response['data'] = $res;
					$response['totalCount'] = count($res);
				} else {
					$response['data'] = $res;
					$get_count_query = getCountSQLPH($query);

					$get_count_result = $this->db->query($get_count_query, $queryParams);


					if (is_object($get_count_result)) {
						$response['totalCount'] = $get_count_result->result('array');
						$response['totalCount'] = $response['totalCount'][0]['cnt'];
					} else {
						return false;
					}
			}
			} else {
				return false;
			}
		}
		else
			return false;
		return $response;
	}
	/**
	 * @comment
	 */
	function loadMedicalHistory($data){
		$query = "
			select 
				ps.Person_Surname+' '+Left(ps.Person_FirName,1)+' '+Left(ps.Person_secName,1) as PersonFIO,
				EPS.EvnPS_id,
				EPS.Person_id,
				EPS.Server_id,
				LS.LpuBuilding_id,
				EPS.EvnPS_NumCard
			from v_EvnPS EPS with(nolock)
			inner join v_PersonState PS with(nolock) on PS.Person_id = EPS.Person_id
			left join v_LpuSection LS with(nolock) on LS.LpuSection_id = EPS.LpuSection_id
			where EPS.EvnPS_id = ?
		";
		$res = $this->db->query($query, array($data['EvnPS_id']));
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;	
	}
}