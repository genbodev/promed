<?php

defined('BASEPATH') or die('No direct script access allowed');

/**
 * comment
 */
class PersonAmbulatCard_model extends swModel {

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 *
	 * @param type $data
	 * @return type 
	 */
	function checkPersonAmbulatCard($data){
		$query = "declare @curDT date = dbo.tzGetDate();
			select pac.PersonAmbulatCard_id,pac.PersonAmbulatCard_Num as PersonCard_Code
			from v_PersonAmbulatCard pac with(nolock) 
			where pac.Lpu_id = :Lpu_id and pac.Person_id = :Person_id
			and @curDT BETWEEN cast(pac.PersonAmbulatCard_begDate AS date) and isnull(cast(PersonAmbulatCard_endDate as DATE),@curDT)";
		//echo getDebugSQL($query, $data); exit();
		$result = $this->db->query($query, $data);
		$result = $result->result('array');
		if($data['getCount']){
			return array(array('count'=>count($result)));
		}
		if(count($result)==1){
			return array(array('PersonAmbulatCard_id'=>$result[0]['PersonAmbulatCard_id'],'PersonCard_Code'=>$result[0]['PersonCard_Code'], 'PersonAmbulatCard_Count'=>1));
		}else if(count($result)>1){
			//return array(array());
			return array(array('PersonAmbulatCard_id'=>$result[0]['PersonAmbulatCard_id'],'PersonCard_Code'=>$result[0]['PersonCard_Code'], 'PersonAmbulatCard_Count'=>count($result)));
		}else if(!in_array(getRegionNick(), array('ufa','pskov','hakasiya','kaluga'))){
			$this->load->model('Polka_PersonCard_model');

			if (empty($data['PersonAmbulatCard_Num'])) {
				$resp = $this->Polka_PersonCard_model->getPersonCardCode($data);

				$data['PersonAmbulatCard_Num']=$resp[0]['PersonCard_Code'];
			}

			if(getRegionNick() == 'ufa'){
				$data['firstAdd']=1;
			}
			$resp = $this->savePersonAmbulatCard($data);
			if ( !is_array($resp) || count($resp) == 0 ) {
				return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение амбулаторной карты)'));
			}
			else if ( !empty($resp[0]['Error_Msg']) ) {
				return $resp;
			}

			$data['PersonAmbulatCard_id'] = $resp[0]['PersonAmbulatCard_id'];

			$this->savePersonAmbulatCardLocat($data);
			return array(array('PersonAmbulatCard_id'=>$data['PersonAmbulatCard_id'],'PersonCard_Code'=>$data['PersonAmbulatCard_Num'], 'PersonAmbulatCard_Count'=>1,'newPersonAmbulatCard_id'=>$data['PersonAmbulatCard_id']));
		} else {
			return array(array('PersonAmbulatCard_id'=>null,'PersonCard_Code'=>'', 'PersonAmbulatCard_Count'=>0,'newPersonAmbulatCard_id'=>null));
		}
	}
	
	/**
	 *
	 * @param type $data
	 * @return type 
	 */
	function deletePersonAmbulatCard($data){
		
		$query = "select COUNT(*) as cnt from v_PersonAmbulatCardLink with(nolock) where PersonAmbulatCard_id = :PersonAmbulatCard_id";
		$result = $this->db->query($query, $data);
		$res = $result->result('array');
		if($res[0]['cnt']>0){
			return array(array('Error_Msg' => 'Оригинал АК имеет связь с прикреплением'));
		}
		
		//удаляем прикрепления амбулаторной карты к картохранилищу
		$res = $this->deleteAttachmentAmbulatoryCardToCardStore($data);
		
		$query ="select PersonAmbulatCardLocat_id from PersonAmbulatCardLocat with(nolock) where PersonAmbulatCard_id = :PersonAmbulatCard_id";
		$result = $this->db->query($query, $data);
		$res = $result->result('array');
		if(count($res)>0){
			foreach ($res as $item){
				$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec p_PersonAmbulatCardLocat_del
				@PersonAmbulatCardLocat_id = :PersonAmbulatCardLocat_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";
				$result = $this->db->query($query, array('PersonAmbulatCardLocat_id'=>$item['PersonAmbulatCardLocat_id']));
			}
		}
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec p_PersonAmbulatCard_del
				@PersonAmbulatCard_id = :PersonAmbulatCard_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$result = $this->db->query($query, $data);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение результатов измерения массы пациента)'));
		}
	}
	
	/**
	 *
	 * @param type $data
	 * @return type 
	 */
	function loadPersonCard($data) {
		$params = array();
		$filter = '';

		if (isset($data['Person_id'])) {
			$params['Person_id'] = $data['Person_id'];
			$filter.=" and Person_id=:Person_id";
		}
		if (isset($data['Lpu_id'])) {
			$params['Lpu_id'] = $data['Lpu_id'];
			$filter.=" and Lpu_id=:Lpu_id";
		}
		/*if (isset($data['AmbulatCardType_id'])) {
			$params['AmbulatCardType_id'] = $data['AmbulatCardType_id'];
			$filter.=" and AmbulatCardType_id=:AmbulatCardType_id";
		}*/

		$query = "
		declare @curDT date = dbo.tzGetDate();
		select
		PersonAmbulatCard_Num,
		PersonAmbulatCard_id
		from v_PersonAmbulatCard s with(nolock) where (1=1)
		and @curDT BETWEEN cast(s.PersonAmbulatCard_begDate AS date) and isnull(cast(s.PersonAmbulatCard_endDate as DATE),@curDT)
		" . $filter;
		//echo getDebugSQL($query, $params);
		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * 
	 * @param type $data
	 * @return boolean
	 */
	function getPersonAmbulatCardList($data, $ad = 'ASC') {

		$sql = "
		select 
			PAC.PersonAmbulatCard_id,
			PAC.Person_id,
			PACLink.PersonCard_id,
			PAC.PersonAmbulatCard_Num,
			CONVERT(varchar(10),PACL.PersonAmbulatCardLocat_begDate,104) as PersonAmbulatCardLocat_begDate,
			ACLT.AmbulatCardLocatType_Name,
			ACLT.AmbulatCardLocatType_id,
			PACL.LpuBuilding_Name,
			ACLB_LB.LpuBuilding_Name as AttachmentLpuBuilding_Name,
			convert(varchar(10), PAC.PersonAmbulatCard_endDate, 104) as PersonAmbulatCard_endDate,
			PACL.MedStaffFact_id as CardLocationMedStaffFact_id,
			isnull(AmbulatCardLocatType_Name, '') + isnull(', '+PACL.LpuBuilding_Name, '') +  isnull(', '+PACL.FIO, '') as MapLocation,
			PAC.PersonAmbulatCard_CloseCause,
			case when PACLink.PersonAmbulatCardLink_id is not null then 'true' else 'false' end as isAttach
		from v_PersonAmbulatCard PAC with(nolock)
			outer apply (select top 1 PersonAmbulatCardLink_id, PersonCard_id from v_PersonAmbulatCardLink PACLink with(nolock) where PAC.PersonAmbulatCard_id=PACLink.PersonAmbulatCard_id) PACLink
			outer apply(
				select top 1 vPACL.PersonAmbulatCardLocat_begDate, vPACL.PersonAmbulatCardLocat_id, vPACL.AmbulatCardLocatType_id, LB.LpuBuilding_Name, MSF.MedStaffFact_id, MSF.Person_Fio as FIO
				from 
					v_PersonAmbulatCardLocat vPACL with(nolock) 
					left join v_LpuBuilding LB with (nolock) on LB.LpuBuilding_id = vPACL.LpuBuilding_id
					left join v_MedStaffFact MSF with(nolock) on MSF.MedStaffFact_id = vPACL.MedStaffFact_id
				where PersonAmbulatCard_id = PAC.PersonAmbulatCard_id
				order by PersonAmbulatCardLocat_begDate desc) PACL
			left join AmbulatCardLocatType ACLT with(nolock) on PACL.AmbulatCardLocatType_id = ACLT.AmbulatCardLocatType_id
			left join v_AmbulatCardLpuBuilding ACLB with(nolock) on ACLB.PersonAmbulatCard_id = PAC.PersonAmbulatCard_id
			left join v_LpuBuilding ACLB_LB with (nolock) on ACLB_LB.LpuBuilding_id = ACLB.LpuBuilding_id
		where PAC.Person_id = :Person_id and PAC.Lpu_id = :Lpu_id
			AND dbo.tzGetDate() between PAC.PersonAmbulatCard_begDate and isnull(PAC.PersonAmbulatCard_endDate, dbo.tzGetDate())
		ORDER BY PAC.PersonAmbulatCard_id {$ad}
		";

		$result = $this->db->query($sql, array("Person_id" => $data['Person_id'],"Lpu_id" => $data['Lpu_id']));

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * 
	 * @param type $data
	 * @return type
	 */
	function savePersonAmbulatCard($data) {
		/*if(!$this->checkPersonSex($data)){
			return array(array('Error_Msg' => 'Не верный тип АК.'));
		}*/
		if (getRegionNick() != 'astra') {
			$checkResult = $this->checkUniqCard($data);
			if ($checkResult != false && isset($checkResult[0]['PersonAmbulatCard_id']) && !isset($data['ignoreUniq']))
			{
				if(getRegionNick() == 'ufa')
				{
					return array(array('Error_Msg' => '', 'Alert_Msg' => 'Карта с таким номером уже существует. Продолжить сохранение?'));
				}
				else
				{
					$Error_Msg = "Амбулаторная карта с номером ".$checkResult[0]['PersonAmbulatCard_Num'].
						" уже создана для пациента ".$checkResult[0]['Person_FIO']." (д/р ".$checkResult[0]['Person_BirthDay']."). Для сохранения необходимо указать уникальный номер карты.";
					return array(array('Error_Msg' => $Error_Msg));
				}
			}
		}
		/*if(!$this->checkUniqCard($data)&&!isset($data['ignoreUniq'])){
			if(getRegionNick() == 'ufa'&&!isset($data['firstAdd'])){
				return array(array('Error_Msg' => '', 'Alert_Msg' => 'Оригинал АК совпадает с существующим в базе. Продолжить сохранение?'));
			}else{
				return array(array('Error_Msg' => 'Оригинал АК совпадает с существующим в базе'));
			}
			
		}*/
		$procedure = "p_PersonAmbulatCard_ins";

		if (isset($data['PersonAmbulatCard_id'])&&$data['PersonAmbulatCard_id'] > 0) {
			$procedure = "p_PersonAmbulatCard_upd";
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@time datetime,
				@ErrMessage varchar(4000);

			set @Res = :PersonAmbulatCard_id;
			set @time = (select dbo.tzGetDate());
			exec " . $procedure . "
				@Server_id = :Server_id,
				@PersonAmbulatCard_id = @Res output,
				@Person_id = :Person_id,
				@PersonAmbulatCard_Num = :PersonAmbulatCard_Num,
				@Lpu_id = :Lpu_id,
				@PersonAmbulatCard_CloseCause =:PersonAmbulatCard_CloseCause,
				@PersonAmbulatCard_endDate = :PersonAmbulatCard_endDate,
				@PersonAmbulatCard_begDate = @time,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as PersonAmbulatCard_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'Server_id' => (isset($data['Server_id']) ? $data['Server_id'] : $data['Lpu_id']),
			'PersonAmbulatCard_id' => ((isset($data['PersonAmbulatCard_id'])&&$data['PersonAmbulatCard_id'] > 0) ? $data['PersonAmbulatCard_id'] : NULL),
			'Person_id' => $data['Person_id'],
			'Lpu_id' => $data['Lpu_id'],
			//'AmbulatCardType_id' => $data['AmbulatCardType_id'],
			'PersonAmbulatCard_Num' => $data['PersonAmbulatCard_Num'],
			'PersonAmbulatCard_CloseCause'=>(isset($data['PersonAmbulatCard_CloseCause']))?$data['PersonAmbulatCard_CloseCause']:NULL,
			'PersonAmbulatCard_endDate'=>(isset($data['PersonAmbulatCard_endDate']))?$data['PersonAmbulatCard_endDate']:NULL,
			'pmUser_id' => $data['pmUser_id']
		);

		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			$resp = $result->result('array');
			if( !empty($res[0]['Error_Msg']) ){
				return array(
					array('Error_Msg' => $res[0]['Error_Msg'], 'Error_Code' => $res[0]['Error_Code'])
				);
			}
			if (!empty($resp[0]['PersonAmbulatCard_id'])) {
				$data['LpuAttachType_id'] = $this->getFirstResultFromQuery("
					SELECT top 1
						pc.LpuAttachType_id
					FROM
						PersonCard pc (nolock)
						inner join v_PersonAmbulatCardLink PACL (nolock) on PACL.PersonCard_id = pc.PersonCard_id
					WHERE
						pc.Person_id = :Person_id
						and PACL.PersonAmbulatCard_id = :PersonAmbulatCard_id;
				", array(
					'PersonAmbulatCard_id' => $resp[0]['PersonAmbulatCard_id'],
					'Person_id' => $data['Person_id']
				));

				if (empty($data['LpuAttachType_id'])) {
					$data['LpuAttachType_id'] = 1;
				}

				// надо обновить номер в прикреплении
				$this->db->query("
					UPDATE
						pc with (rowlock)
					SET
						pc.PersonCard_Code = :PersonCard_Code
					FROM
						PersonCard pc
						inner join v_PersonAmbulatCardLink PACL (nolock) on PACL.PersonCard_id = pc.PersonCard_id
					WHERE
						pc.Person_id = :Person_id
						and PACL.PersonAmbulatCard_id = :PersonAmbulatCard_id;

					exec xp_Update_PersonCardState @Person_id = :Person_id, @LpuAttachType_id = :LpuAttachType_id;
				", array(
					'PersonAmbulatCard_id' => $resp[0]['PersonAmbulatCard_id'],
					'LpuAttachType_id' => $data['LpuAttachType_id'],
					'PersonCard_Code' => $data['PersonAmbulatCard_Num'],
					'Person_id' => $data['Person_id']
				));		
				
			}
			return $resp;
		} else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение результатов измерения массы пациента)'));
		}
	}
	/**
	 * @ds
	 */
	private function checkPersonSex($data){
		if($data['AmbulatCardType_id']==2){
			$query = "select top 1 Sex_id from v_PersonState with(nolock) where Person_id=:Person_id";
			$params = array('Person_id'=>$data['Person_id']);
			$response = $this->getFirstRowFromQuery($query, $params);
			if($response['Sex_id']!=2){
				return false;
			}
		}
		return true;
	}
	/**
	 * @ds
	 */
	private function checkUniqCard($data){
		$filter='';
		if(isset($data['PersonAmbulatCard_id'])&&$data['PersonAmbulatCard_id']>0){
			$filter.=' and PAC.PersonAmbulatCard_id != :PersonAmbulatCard_id';
		}
		if(getRegionNick() != 'ufa')
		{
			$filter .= ' and (PAC.PersonAmbulatCard_endDate is null or PAC.PersonAmbulatCard_endDate <= dbo.tzGetDate())';
		}
		$query = "
			select top 1
				PAC.PersonAmbulatCard_id,
				PAC.PersonAmbulatCard_Num,
				ISNULL(PS.Person_Surname,'') + ' ' + ISNULL(PS.Person_FirName,'') + ' ' + ISNULL(PS.Person_SecName,'') as Person_FIO,
				convert(varchar, PS.Person_BirthDay, 104) as Person_BirthDay
			from v_PersonAmbulatCard PAC with(nolock)
			left join v_PersonState PS on PS.Person_id = PAC.Person_id
			where
				PAC.PersonAmbulatCard_Num = :PersonAmbulatCard_Num
			and
				PAC.Lpu_id = :Lpu_id
		".$filter;
		$response = $this->db->query($query, $data);
		if(is_object($response))
		{
			$res = $response->result('array');
			if(is_array($res) && count($res) > 0)
				return $res;
			else
				return false;
		}
		return false;
	}
	/**
	 * 
	 * @param type $data
	 * @return boolean
	 */
	function getPersonAmbulatCardLocatList($data) {
		$query = "
		select 
		-- select
		PACL.PersonAmbulatCard_id,
		PACL.PersonAmbulatCardLocat_id,
		convert(varchar, PACL.PersonAmbulatCardLocat_begDate, 120) as PersonAmbulatCardLocat_begDate,
		ACLT.AmbulatCardLocatType_Name as AmbulatCardLocatType,
		MSF.Person_Fio as FIO,
		post.PostMed_Name as MedStaffFact,
		PACL.PersonAmbulatCardLocat_Desc,
		PACL.LpuBuilding_id,
		LB.LpuBuilding_Name,
		0 as isSave
		-- end select
		from 
		-- from
			v_PersonAmbulatCardLocat PACL with(nolock)
			left join AmbulatCardLocattype ACLT with(nolock) on PACL.AmbulatCardLocatType_id =ACLT.AmbulatCardLocatType_id
			left join v_MedStaffFact MSF with(nolock) on MSF.MedStaffFact_id = PACL.MedStaffFact_id
			left join v_PostMed post with(nolock) on MSF.Post_id = post.PostMed_id
			left join v_LpuBuilding LB with (nolock) on LB.LpuBuilding_id = PACL.LpuBuilding_id
		-- end from
		where 
		-- where
		PACL.PersonAmbulatCard_id = :PersonAmbulatCard_id
		-- end where
		order by
		-- order by
		PACL.PersonAmbulatCardLocat_id
		-- end order by
		";

		$queryParams = array('PersonAmbulatCard_id' => $data['PersonAmbulatCard_id']);
		
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
	 * 
	 * @param type $data
	 * @return type
	 */
	function savePersonAmbulatCardLocat($data) {
		$procedure = "p_PersonAmbulatCardLocat_ins";

		if (isset($data['PersonAmbulatCardLocat_id'])&&$data['PersonAmbulatCardLocat_id'] > 0) {
			$procedure = "p_PersonAmbulatCardLocat_upd";
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @Res = :PersonAmbulatCardLocat_id;
			exec " . $procedure . "
				@Server_id = :Server_id,
				@PersonAmbulatCardLocat_id = @Res output,
				@PersonAmbulatCard_id = :PersonAmbulatCard_id,
				@AmbulatCardLocatType_id = :AmbulatCardLocatType_id,
				@MedStaffFact_id = :MedStaffFact_id,
				@PersonAmbulatCardLocat_begDate = :PersonAmbulatCardLocat_begDate,
				@PersonAmbulatCardLocat_Desc = :PersonAmbulatCardLocat_Desc,
				@PersonAmbulatCardLocat_OtherLocat =:PersonAmbulatCardLocat_OtherLocat,
				@LpuBuilding_id =:LpuBuilding_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as PersonAmbulatCardLocat_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		
		$queryParams = array(
			'Server_id' => $data['Server_id'],
			'PersonAmbulatCardLocat_id' => ((isset($data['PersonAmbulatCardLocat_id'])&&$data['PersonAmbulatCardLocat_id'] > 0) ? $data['PersonAmbulatCardLocat_id'] : NULL),
			'PersonAmbulatCard_id' => $data['PersonAmbulatCard_id'],
			'AmbulatCardLocatType_id' => (isset($data['AmbulatCardLocatType_id'])?$data['AmbulatCardLocatType_id']:1),
			'MedStaffFact_id' => ((isset($data['MedStaffFact_id'])&&$data['MedStaffFact_id'] > 0) ? $data['MedStaffFact_id'] : NULL),
			'PersonAmbulatCardLocat_begDate' =>((isset($data['PersonAmbulatCardLocat_begD'])&&isset($data['PersonAmbulatCardLocat_begT']))? $data['PersonAmbulatCardLocat_begD'] . " " . $data['PersonAmbulatCardLocat_begT']:date('Y-m-d H:i')),
			'PersonAmbulatCardLocat_Desc' => (isset($data['PersonAmbulatCardLocat_Desc'])?$data['PersonAmbulatCardLocat_Desc']:NULL),
			'pmUser_id' => $data['pmUser_id'],
			'PersonAmbulatCardLocat_OtherLocat'=>(isset($data['PersonAmbulatCardLocat_OtherLocat'])?$data['PersonAmbulatCardLocat_OtherLocat']:NULL),
			'LpuBuilding_id' => (!empty($data['LpuBuilding_id'])?$data['LpuBuilding_id']:NULL)
			//'MedPersonal_id' => (isset($data['MedPersonal_id'])?$data['MedPersonal_id']:NULL)
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
	 * 
	 * @param type $data
	 * @return type
	 */
	function deletePersonAmbulatCardLocat($data) {
		if(empty($data['PersonAmbulatCardLocat_id'])) return false;
		$procedure = "p_PersonAmbulatCardLocat_del";
		
		$query = "
			declare
				@PersonAmbulatCardLocat_id bigint = :PersonAmbulatCardLocat_id,
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec {$procedure}
				@PersonAmbulatCardLocat_id = @PersonAmbulatCardLocat_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
            select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			$result = $result->result('array');
			if (isset($result[0]) && empty($result[0]['Error_Msg'])) {
				return array('success' => true);
			}
		}

		return array('success' => false);		
	}

	/**
	 * 
	 * @param type $data
	 * @return boolean
	 */
	function loadPersonAmbulatCard($data) {
		$sql = "
	    Select 
	    PAC.PersonAmbulatCard_id,
	    PAC.Person_id,
	    PAC.Server_id,
	    --PAC.AmbulatCardType_id,
	    PAC.Lpu_id,
		convert(varchar ,PAC.PersonAmbulatCard_endDate,104) as PersonAmbulatCard_endDate,
	    PAC.PersonAmbulatCard_Num,
		PAC.PersonAmbulatCard_CloseCause,
	    ps.Person_Surname+' '+Left(ps.Person_FirName,1)+' '+Left(ps.Person_secName,1) as PersonFIO
	    from v_PersonAmbulatCard PAC with(nolock)
	    left join v_PersonState ps with(nolock) on ps.Person_id=PAC.Person_id
	    where PersonAmbulatCard_id = :PersonAmbulatCard_id
	    ";
		$params = array(
			'PersonAmbulatCard_id' => $data['PersonAmbulatCard_id']
		);
		//echo getDebugSQL($sql, $params);
		$result = $this->db->query($sql, $params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * 
	 * @param type $data
	 * @return boolean
	 */
	function loadPersonAmbulatCardLocat($data) {
		$sql = "
	    select 
			ACL.PersonAmbulatCardLocat_id,
			ACL.PersonAmbulatCard_id,
			convert(varchar ,ACL.PersonAmbulatCardLocat_begDate,104) as PersonAmbulatCardLocat_begD,
			convert(varchar(5) ,ACL.PersonAmbulatCardLocat_begDate,108) as PersonAmbulatCardLocat_begT,
			ACL.AmbulatCardLocatType_id,
			ACL.PersonAmbulatCardLocat_OtherLocat,
			ACL.MedStaffFact_id,
			MSF.MedPersonal_id,
			ACL.LpuBuilding_id,
			ACL.PersonAmbulatCardLocat_Desc
		from PersonAmbulatCardLocat ACL with(nolock)
		left join v_MedStaffFact MSF with(nolock) on MSF.MedStaffFact_id=ACL.MedStaffFact_id
	    where ACL.PersonAmbulatCardLocat_id=:PersonAmbulatCardLocat_id
	    ";
		$params = array(
			'PersonAmbulatCardLocat_id' => $data['PersonAmbulatCardLocat_id']
		);
		$result = $this->db->query($sql, $params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение данных об амбулаторных картах человека. Метод для API.
	 */
	function loadPersonAmbulatCardListForAPI($data) {
		$filter = "";
		$queryParams = array(
			'Person_id' => $data['Person_id']
		);

		if (!empty($data['Lpu_id'])) {
			$filter .= " and PAC.Lpu_id = :Lpu_id";
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}

		return $this->queryResult("
			select
				PAC.Lpu_id,
				PAC.PersonAmbulatCard_id,
				PAC.PersonAmbulatCard_Num,
				convert(varchar(10), PAC.PersonAmbulatCard_begDate, 120) as PersonAmbulatCard_begDate,
				convert(varchar(10), PAC.PersonAmbulatCard_endDate, 120) as PersonAmbulatCard_endDate,
				PC.LpuAttachType_id
			from
				v_PersonAmbulatCard PAC with (nolock)
				left join v_PersonAmbulatCardLink PACL with (nolock) on PACL.PersonAmbulatCard_id = PAC.PersonAmbulatCard_id
				left join v_PersonCard PC with (nolock) on PC.PersonCard_id = PACL.PersonCard_id 
			where
				PAC.Person_id = :Person_id
				{$filter}
		", $queryParams);
	}
	
	/**
	 * Получение данных об амбулаторных картах человека. Метод для API.
	 */
	function getPersonAmbulatCardForAPI($data) {
		$filter = "";

		if (!empty($data['Lpu_id'])) {
			$filter .= " and PAC.Lpu_id = :Lpu_id";
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}
		if (!empty($data['Person_id'])) {
			$filter .= " and PAC.Person_id = :Person_id";
			$queryParams['Person_id'] = $data['Person_id'];
		}
		if (!empty($data['PersonAmbulatCard_Num'])) {
			$filter .= " and PAC.PersonAmbulatCard_Num = :PersonAmbulatCard_Num";
			$queryParams['PersonAmbulatCard_Num'] = $data['PersonAmbulatCard_Num'];
		}
		if (!empty($data['Date_DT'])) {
			$filter .= " and (
				(CAST(:Date_DT as date) BETWEEN CAST(PAC.PersonAmbulatCard_begDate AS DATE) AND CAST(PAC.PersonAmbulatCard_endDate AS DATE) )
				or 
				(CAST(PAC.PersonAmbulatCard_begDate AS DATE) <= CAST(:Date_DT as datetime) and PAC.PersonAmbulatCard_endDate is null)
			)";
			$queryParams['Date_DT'] = $data['Date_DT'];
		}
		
		return $this->queryResult("
			select
				PAC.Lpu_id,
				PAC.Person_id,
				PAC.PersonAmbulatCard_id,
				PAC.PersonAmbulatCard_Num,
				convert(varchar(10), PAC.PersonAmbulatCard_begDate, 120) as PersonAmbulatCard_begDate,
				convert(varchar(10), PAC.PersonAmbulatCard_endDate, 120) as PersonAmbulatCard_endDate,
				PAC.PersonAmbulatCard_CloseCause 
			from
				v_PersonAmbulatCard PAC with (nolock)
			where (1=1)
				{$filter}
		", $queryParams);
	}

	/**
	 * сохранение движения карты из рабочего места сотрудника картохранилища (доставить карту)
	 */
	function savePersonAmbulatDeliverCard($data){
		//создаем движение
		$data['Server_id'] = $data['session']['server_id'];
		$data['PersonAmbulatCardLocat_Desc'] = 'отметка о доставке АК сотрудником картохранилища';
		$res = $this->savePersonAmbulatCardLocat($data);
		if( !empty($res[0]['Error_Msg']) ){
			return array(
				array('Error_Msg' => $res[0]['Error_Msg'], 'Error_Code' => $res[0]['Error_Code'])
			);
		}
		if(empty($res[0]['PersonAmbulatCardLocat_id'])){
			return array(
				array('Error_Msg' => 'Ошибка при создании движения карты')
			);
		}
		if(!empty($data['AmbulatCardRequest_id']) && !empty($data['AmbulatCardRequestStatus_id']) && $data['AmbulatCardRequestStatus_id'] == 1){
			// если был запрос от врача, то убираем запрос
			$queryParams = array(
				'AmbulatCardRequestStatus_id' => 2,
				'AmbulatCardRequest_id' => $data['AmbulatCardRequest_id'],
				'PersonAmbulatCard_id' => $data['PersonAmbulatCard_id'],
				'pmUser_id' => $data['pmUser_id']
			);
			$query = "
				declare
					@Res bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);

				set @Res = :AmbulatCardRequest_id;
				exec p_AmbulatCardRequest_upd
					@AmbulatCardRequest_id = @Res output,
					@PersonAmbulatCard_id = :PersonAmbulatCard_id,
					@AmbulatCardRequestStatus_id = :AmbulatCardRequestStatus_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;

				select @Res as AmbulatCardRequest_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";	

			//echo getDebugSQL($query, $queryParams); die();
			$result = $this->db->query($query, $queryParams);
			if (!is_object($result)) {			
				return array(array('Error_Msg' => 'Ошибка при запросе амб. карты у картохранилища'));
			}
		}
		return $res;
		/*if(empty($data['TimetableGraf_id'])) return false;
		$result = $this->savePersonAmbulatCardInTimetableGraf($data);
		if(!empty($result[0]['Error_Msg'])){
			return $result;
		}else{
			$result[0]['PersonAmbulatCardLocat_id'] = $res[0]['PersonAmbulatCardLocat_id'];
			$result[0]['TimetableGraf_id'] = $data['TimetableGraf_id'];
			$result[0]['PersonAmbulatCard_id'] = $data['PersonAmbulatCard_id'];
			return $result;
		}*/
	}
	
	/**
	 * Привязываем амбулаторную карту к бирке
	 */
	function savePersonAmbulatCardInTimetableGraf($data){
		if( empty($data['TimetableGraf_id']) ) return false;
		// записываем в TimeTableGraf ИД амбулаторной карты без использования p_TimetableGraf_upd, т.к. нам не нужно изменять историю бирки
		$res = $this->swUpdate('TimetableGraf', array(
			'TimetableGraf_id' => $data['TimetableGraf_id'],
			'pmUser_id' => $data['pmUser_id'],
			'PersonAmbulatCard_id' => (!empty($data['PersonAmbulatCard_id'])) ? $data['PersonAmbulatCard_id'] : null
		), true);
		
		return $res;
	}

	/**
	 * Прикрепление амбулаторной карты к картохранилищу
	 */
	function saveAttachmentAmbulatoryCardToCardStore($data){
		if(empty($data['PersonAmbulatCard_id']) /*|| empty($data['session']['CurARM']['MedService_id'])*/) return false;
		$currentDate = date('Y-m-d H:i:s');
		$procedure = 'p_AmbulatCardLpuBuilding_ins';
		$queryParams = array(
			'Lpu_id' => $data['Lpu_id'],
			'PersonAmbulatCard_id' => $data['PersonAmbulatCard_id'],
			'pmUser_id' => $data['pmUser_id']
		);	
		//получим полседнюю запись прикрепления
		$lastRecord = $this->getAttachmentAmbulatoryCardToCardStore($data, true);
		//узнаем расположение службы пользователя
		$serviceLocation = $this->getServiceLocationsUser(array(
			'MedPersonal_id' => $data['session']['medpersonal_id'],
			'pmUser_id' => $data['pmUser_id'],
			'Lpu_id' => $data['Lpu_id']
		));		
		if(!empty($serviceLocation[0]['LpuBuilding_id'])) $queryParams['LpuBuilding_id'] = $serviceLocation[0]['LpuBuilding_id'];
		
		if($lastRecord && is_array($lastRecord) && count($lastRecord)>0){
			if($serviceLocation && is_array($serviceLocation) && count($serviceLocation)>0){
				if($lastRecord[0]['LpuBuilding_id'] == $serviceLocation[0]['LpuBuilding_id'] && $serviceLocation[0]['Lpu_id'] == $lastRecord[0]['Lpu_id']){
					//если данные совпадают, то ничего не меняем
					return true;
				}else{
					//иначе закрываем предыдущее прикрепление
					$params = $queryParams;
					$params['AmbulatCardLpuBuilding_id'] = $lastRecord[0]['AmbulatCardLpuBuilding_id'];
					$params['AmbulatCardLpuBuilding_endDate'] = $currentDate;
					//$res = $this->addAttachmentAmbulatoryCardToCardStore($params);
					$res = $this->closeAttachmentAmbulatoryCardToCardStore($params);
				}
			}
		}
		
		$result = $this->addAttachmentAmbulatoryCardToCardStore($queryParams);
		return $result;
	}
	
	/**
	 * Получение прикреплений амбулаторной карты к картохранилищу
	 */
	function getAttachmentAmbulatoryCardToCardStore($data, $lastRecord = false){
		if( empty($data['PersonAmbulatCard_id']) || empty($data['Lpu_id'])) return false;
		$top1 = ($lastRecord) ? ' TOP 1 ' : '';
		$result = $this->queryResult("
			SELECT {$top1}
				ACLB.AmbulatCardLpuBuilding_id,
				isnull(ACLB.LpuBuilding_id, '') as LpuBuilding_id,
				ACLB.Lpu_id,
				ACLB.AmbulatCardLpuBuilding_begDate,
				ACLB.AmbulatCardLpuBuilding_endDate
			FROM v_AmbulatCardLpuBuilding ACLB with (NOLOCK)
			WHERE
				ACLB.Lpu_id = :Lpu_id
				AND ACLB.PersonAmbulatCard_id = :PersonAmbulatCard_id
			ORDER BY ACLB.AmbulatCardLpuBuilding_begDate DESC
		", $data);
		
		return $result;
	}
	
	/**
	 * Удаление прикреплений амбулаторной карты к картохранилищу по PersonAmbulatCard_id
	 */
	function deleteAttachmentAmbulatoryCardToCardStore($data){
		if( empty($data['PersonAmbulatCard_id']) ) return false;
		$PersonID = null;
		$query = "select top 1 Person_id from v_PersonAmbulatCard with(nolock) where PersonAmbulatCard_id = :PersonAmbulatCard_id";
		$result = $this->db->query($query, $data);
		$res = $result->result('array');
		if(!empty($res[0]['Person_id'])){
			$PersonID = $res[0]['Person_id'];
		}
		
		$res = $this->getAttachmentAmbulatoryCardToCardStore($data);
		if(count($res) == 0) return true;
		foreach ($res as $val) {
			if(!empty($val['AmbulatCardLpuBuilding_id'])){
				$params = array('AmbulatCardLpuBuilding_id' => $val['AmbulatCardLpuBuilding_id']);
				$this->deleteAmbulatCardLpuBuildingID($params);
			}
		}
		if($PersonID){
			// смотрим предшествующую карту
			$res = $this->queryResult("
				declare @curDT date = dbo.tzGetDate();
				select top 1
					PAC.PersonAmbulatCard_id,
					PAC.Person_id,
					PAC.PersonAmbulatCard_endDate,
					PAC.PersonAmbulatCard_Num,
					ACLB.AmbulatCardLpuBuilding_id,
					ACLB.AmbulatCardLpuBuilding_endDate
				from v_PersonAmbulatCard PAC with(nolock)
					left join v_AmbulatCardLpuBuilding ACLB with(nolock) on ACLB.PersonAmbulatCard_id = PAC.PersonAmbulatCard_id
				where 1=1
					and @curDT BETWEEN cast(PAC.PersonAmbulatCard_begDate AS date) and isnull(cast(PAC.PersonAmbulatCard_endDate as DATE),@curDT)
					AND PAC.Person_id = :Person_id and PAC.Lpu_id = :Lpu_id
					AND PAC.PersonAmbulatCard_id <> :PersonAmbulatCard_id
				ORDER BY PAC.PersonAmbulatCard_id DESC
			", array('Lpu_id' => $data['Lpu_id'], 'Person_id' => $PersonID, 'PersonAmbulatCard_id' => $data['PersonAmbulatCard_id']));
			if(!empty($res[0]['PersonAmbulatCard_id'])){
				//если существует прикреплене этой АК и она закрыта, то открываем ее
				$attachmentAC = $this->getAttachmentAmbulatoryCardToCardStore(array(
					'Lpu_id' => $data['Lpu_id'], 
					'Person_id' => $PersonID,
					'PersonAmbulatCard_id' => $res[0]['PersonAmbulatCard_id']
				), true);
				if(!empty($attachmentAC[0]['AmbulatCardLpuBuilding_id']) && !empty($data['AmbulatCardLpuBuilding_endDate'])){
					$data['AmbulatCardLpuBuilding_id'] = $attachmentAC[0]['AmbulatCardLpuBuilding_id'];
					$data['PersonAmbulatCard_id'] = $res[0]['PersonAmbulatCard_id'];
					$res = $this->saveAttachmentAmbulatoryCardToCardStore($data);
				}
			}
		}
	}
	
	/**
	 * Удаление прикрепления амбулаторной карты к картохранилищу по AmbulatCardLpuBuilding_id
	 */
	function deleteAmbulatCardLpuBuildingID($data){
		if( empty($data['AmbulatCardLpuBuilding_id']) ) return false;
		
		$proc = 'dbo.p_AmbulatCardLpuBuilding_del';

		$query = "
			declare
				@AmbulatCardLpuBuilding_id bigint = :AmbulatCardLpuBuilding_id,
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec {$proc}
				@AmbulatCardLpuBuilding_id = @AmbulatCardLpuBuilding_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
            select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			$result = $result->result('array');
			if (isset($result[0]) && empty($result[0]['Error_Msg'])) {
				return array('success' => true);
			}
		}

		return array('success' => false);
	}
	
	/**
	 * Добавление записи прикрепления амбулаторной карты к картохранилищу
	 */
	function addAttachmentAmbulatoryCardToCardStore($data){
		if( empty($data['PersonAmbulatCard_id']) || empty($data['Lpu_id']) || empty($data['pmUser_id'])) return false;
		$currentDate = date('Y-m-d H:i:s');
		$procedure = (empty($data['AmbulatCardLpuBuilding_id'])) ? 'p_AmbulatCardLpuBuilding_ins' : 'p_AmbulatCardLpuBuilding_upd';
		
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @Res = :AmbulatCardLpuBuilding_id;
			exec ".$procedure."
				@AmbulatCardLpuBuilding_id = @Res output,
				@PersonAmbulatCard_id = :PersonAmbulatCard_id,
				@LpuBuilding_id = :LpuBuilding_id,
				@Lpu_id = :Lpu_id,
				@AmbulatCardLpuBuilding_begDate = :AmbulatCardLpuBuilding_begDate,
				@AmbulatCardLpuBuilding_endDate = :AmbulatCardLpuBuilding_endDate,
				
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as AmbulatCardLpuBuilding_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";	
		$queryParams = array(
			'AmbulatCardLpuBuilding_id' => (!empty($data['AmbulatCardLpuBuilding_id'])) ? $data['AmbulatCardLpuBuilding_id'] : null,
			'PersonAmbulatCard_id' => $data['PersonAmbulatCard_id'],
			'LpuBuilding_id' => (!empty($data['LpuBuilding_id'])) ? $data['LpuBuilding_id'] : null,
			'Lpu_id' => $data['Lpu_id'],
			'pmUser_id' => $data['pmUser_id'],
			'AmbulatCardLpuBuilding_begDate' => (empty($data['AmbulatCardLpuBuilding_begDate'])) ? $currentDate : $data['AmbulatCardLpuBuilding_begDate'],
			'AmbulatCardLpuBuilding_endDate' => (empty($data['AmbulatCardLpuBuilding_endDate'])) ? null : $data['AmbulatCardLpuBuilding_endDate']
		);
		
		//echo getDebugSQL($query, $queryParams); die();
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {			
			return array(array('Error_Msg' => 'Ошибка при сохранении прикрепления амб. карты к картохранилищу'));
		}
		
		return $result->result('array');
	}
	
	/**
	 * закрыть прикрепление амбулаторной карты к картохранилищу
	 */
	function closeAttachmentAmbulatoryCardToCardStore($data){
		if(empty($data['AmbulatCardLpuBuilding_id'])) return false;
		$currentDate = date('Y-m-d H:i:s');
		$procedure = 'p_AmbulatCardLpuBuilding_upd';
		
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @Res = :AmbulatCardLpuBuilding_id;
			exec ".$procedure."
				@AmbulatCardLpuBuilding_id = @Res output,
				@AmbulatCardLpuBuilding_endDate = :AmbulatCardLpuBuilding_endDate,				
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as AmbulatCardLpuBuilding_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";	
		$queryParams = array(
			'AmbulatCardLpuBuilding_id' => (!empty($data['AmbulatCardLpuBuilding_id'])) ? $data['AmbulatCardLpuBuilding_id'] : null,
			//'PersonAmbulatCard_id' => $data['PersonAmbulatCard_id'],
			//'LpuBuilding_id' => (!empty($data['LpuBuilding_id'])) ? $data['LpuBuilding_id'] : null,
			//'Lpu_id' => $data['Lpu_id'],
			'pmUser_id' => $data['pmUser_id'],
			'AmbulatCardLpuBuilding_endDate' => (empty($data['AmbulatCardLpuBuilding_endDate'])) ? null : $data['AmbulatCardLpuBuilding_endDate']
		);
		
		//echo getDebugSQL($query, $queryParams); die();
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {			
			return array(array('Error_Msg' => 'Ошибка при закрытии прикрепления амб. карты к картохранилищу'));
		}
		
		return $result->result('array');
	}
	
	/**
	 * Получение идентификатора подразделения LpuBuilding_id по идентификатору службы(MedService_id)
	 */
	public function getLpuBuildingByMedServiceId($data) {
		if(!empty($data['MedService_id'])){
			$MedService_id = $data['MedService_id'];
		}else{
			$MedService_id = (!empty($data[ 'session' ][ 'CurMedService_id' ])) ? $data[ 'session' ][ 'CurMedService_id' ] : false;
		}
		if(!$MedService_id){
			return array( array( 'Err_Msg' => 'Не определен идентификатор службы' ) );
		}
		$queryParams = array('MedService_id' => $MedService_id);
		$sql = "
			SELECT
				ISNULL( MS.LpuBuilding_id, 0 ) as LpuBuilding_id
			FROM
				v_MedService MS with (nolock)
			WHERE
				MS.MedService_id = :MedService_id
		";
		$result = $this->queryResult($sql, $queryParams);
		return $result;

	}
	
	/**
	 * форма поиска амбулаторных карт
	 */
	function loadInformationAmbulatoryCards($data){
		if(empty($data['Lpu_id'])) return false;
		$params = array('Lpu_id' => $data['Lpu_id']);
		$filter = "";
		
		if ( !empty( $data['Person_SurName'] ) ) {
			$filter .= " and p.Person_SurName like (:Person_SurName+'%')";
			$params['Person_SurName'] = rtrim( $data['Person_SurName'] );
		}	
		if ( !empty( $data['Person_id'] ) ) {
			$filter .= " and p.Person_id = :Person_id";
			$params['Person_id'] = rtrim( $data['Person_id'] );
		}
		if ( !empty( $data['PersonAmbulatCard_id'] ) ) {
			$filter .= " and PAC.PersonAmbulatCard_id = :PersonAmbulatCard_id";
			$params['PersonAmbulatCard_id'] = rtrim( $data['PersonAmbulatCard_id'] );
		}
		
		if ( !empty( $data['Person_FirName'] ) ) {
			$filter .= " and p.Person_FirName like (:Person_FirName+'%')";
			$params['Person_FirName'] = rtrim( $data['Person_FirName'] );
		}
		if ( !empty( $data['Person_SecName'] ) ) {
			$filter .= " and p.Person_SecName like (:Person_SecName+'%')";
			$params['Person_SecName'] = rtrim( $data['Person_SecName'] );
		}
		if ( !empty( $data['Person_Birthday'] ) ) {
			$filter .= " and p.Person_BirthDay = :Person_BirthDay";
			$params['Person_BirthDay'] = $data['Person_Birthday'];
		}
		
		if (!empty($data['LpuBuilding_id'])) {
			$filter .= " and ACLB_LB.LpuBuilding_id = :LpuBuilding_id";
			$params['LpuBuilding_id'] = $data['LpuBuilding_id'];
		}
		
		if ( ! empty($data['MedStaffFact_id'])){
			$filter .= " and ambulatCard.MedStaffFact_id = :MedStaffFact_id";
			$params['MedStaffFact_id'] = $data['MedStaffFact_id'];
		}
		
		if ( ! empty($data['MedPersonal_id'])){
			$filter .= " and MSF.MedPersonal_id = :MedPersonal_id";
			$params['MedPersonal_id'] = $data['MedPersonal_id'];
		}
		
		if( ! empty($data['AmbulatCardLocatType_id'])){
			$filter .= " and ambulatCard.AmbulatCardLocatType_id = :AmbulatCardLocatType_id";
			$params['AmbulatCardLocatType_id'] = $data['AmbulatCardLocatType_id'];
		}
		
		if( !empty($data['field_numberCard'])){
			$filter .= " and PAC.PersonAmbulatCard_Num = :PersonAmbulatCard_Num";
			$params['PersonAmbulatCard_Num'] = $data['field_numberCard'];
		}
		
		if( !empty($data['CardAttachment'])){
			$filter .= " AND ACLB.Lpu_id = :Lpu_id ";
			if(!empty($data['AttachmentLpuBuilding_id'])){
				$params['AttachmentLpuBuilding_id'] = $data['AttachmentLpuBuilding_id'];
				
				switch ($data['CardAttachment']) {
					case 'currentStorage':
						//Текущее картохранилище (карты, прикреплённых к текущему картохранилищу)
						$filter .= " AND ACLB.LpuBuilding_id = :AttachmentLpuBuilding_id ";
						break;
					case 'otherStorage':
						//Другие картохранилища 
						//	карт, которые прикреплены к другому картохранилищу текущей МО, 
						//но в качестве местонахождения имеют текущее картохранилище (подразделение) либо врача текущего подразделения
						$filter .= " AND ACLB.LpuBuilding_id <> :AttachmentLpuBuilding_id ";
						$filter .= " AND (ambulatCard.LpuBuilding_id = :AttachmentLpuBuilding_id OR ambulatCard.MSF_LpuBuilding_id = :AttachmentLpuBuilding_id) ";
						
						break;
					case 'allStorage':
						//Все (карты, прикреплённых к текущему картохранилищу)
						//	карт, прикреплённых к текущему картохранилищу;
						//	карт, которые прикреплены к другому картохранилищу текущей МО, 
						//но в качестве местонахождения имеют текущее картохранилище (подразделение) либо врача текущего подразделения.
						$filter .= " AND (ACLB.LpuBuilding_id = :AttachmentLpuBuilding_id OR ambulatCard.MSF_LpuBuilding_id = :AttachmentLpuBuilding_id) ";
						break;
				}
			}else{
				// служба находится на верхнем уровне
			}
		}
		
		if( !empty($data['CardIsOpenClosed'])){
			switch ($data['CardIsOpenClosed']) {
				case 'openCard':
					//Открытые карты
					$filter .= " AND dbo.tzGetDate() BETWEEN PAC.PersonAmbulatCard_begDate AND isnull(PAC.PersonAmbulatCard_endDate, dbo.tzGetDate()) ";
					break;
				case 'closeCard':
					//Закрытые карты
					$filter .= " AND dbo.tzGetDate() NOT BETWEEN PAC.PersonAmbulatCard_begDate AND isnull(PAC.PersonAmbulatCard_endDate, dbo.tzGetDate()) ";
					break;
				case 'allCard':
					//$filter .= "";
					break;
			}
		}
		
		$query = "
			SELECT 
				-- select
				PAC.PersonAmbulatCard_id,
				rtrim(rtrim(p.Person_Surname) + ' ' + isnull(rtrim(p.Person_Firname),'') + ' ' + isnull(rtrim(p.Person_Secname),'')) as Person_FIO,
				rtrim(p.Person_Surname) as Person_SurName,
				rtrim(p.Person_Firname) as Person_FirName,
				rtrim(p.Person_Secname) as Person_SecName,
				convert(varchar(10), p.Person_BirthDay, 104) as Person_Birthday, -- Дата рождения пациента
				isnull(pcMain.Lpu_Nick, '') as MainLpu_Nick, -- МО прикрепления (осн.)
				isnull(pcMain.LpuRegion_Name, '') as LpuRegion_Name, -- участок
				isnull(pcGin.Lpu_Nick, '') as GinLpu_Nick,   -- МО прикрепления (гинек.)
				isnull(pcStom.Lpu_Nick, '') as StomLpu_Nick, -- МО прикрепления (стомат.)
				ACLB.LpuBuilding_id,
				ACLB_LB.LpuBuilding_Name as AttachmentLpuBuilding_Name,
				isnull(RTrim(PAC.PersonAmbulatCard_Num), '') as PersonAmbulatCard_Num, --№ амб. карты
				ambulatCard.MapLocation as Location_Amb_Cards, -- Местонахождение амб. карты 
				CONVERT(varchar(10),ambulatCard.PersonAmbulatCardLocat_begDate,104) as PersonAmbulatCardLocat_begDate, --Дата и время движения
				ambulatCard.FIO as EmployeeFIO,		--сотрудник
				ambulatCard.MedStaffFact,	--должность
				ambulatCard.PersonAmbulatCardLocat_Desc, --пояснение из последнего движения карты
				cast(dbo.tzGetDate() as date) as curDT 
				-- end select
			FROM 
				-- from
				v_PersonAmbulatCard PAC with(nolock)
				left join v_AmbulatCardLpuBuilding ACLB with(nolock) on ACLB.PersonAmbulatCard_id = PAC.PersonAmbulatCard_id
				left join v_LpuBuilding ACLB_LB with (nolock) on ACLB_LB.LpuBuilding_id = ACLB.LpuBuilding_id --прикрепление к подразделению
				--left join v_PersonState_all p with (nolock) on p.Person_id = PAC.Person_id	
				left join v_PersonState p with (nolock) on p.Person_id = PAC.Person_id
				outer apply(
					select top 1 
						vpc.PersonCard_id, 
						vpc.PersonCard_Code, 
						vpc.LpuAttachType_id, 
						vpc.LpuRegion_id, 
						RTrim(l.Lpu_Nick) as Lpu_Nick,
						LR.LpuRegion_Name + isnull(' ('+LpuRegion_Descr+')', '') as LpuRegion_Name
					from v_PersonCard VPC with(nolock)
						inner join v_PersonAmbulatCardLink PACLink with(nolock) on PACLink.PersonAmbulatCard_id =PAC.PersonAmbulatCard_id AND VPC.PersonCard_id = PACLink.PersonCard_id
						left join v_LpuRegion LR with(nolock) on LR.LpuRegion_id = vpc.LpuRegion_id
						left join v_Lpu l with (nolock) on l.Lpu_id = vpc.Lpu_id
					where LpuAttachType_id=1
						AND Person_id = p.Person_id
					ORDER BY VPC.PersonCard_id DESC
				) pcMain --МО прикрепления (осн.)
				outer apply(
					select top 1 VPC.PersonCard_id, LpuAttachType_id, RTrim(l.Lpu_Nick) as Lpu_Nick
					from v_PersonCard VPC with(nolock)
						inner join v_PersonAmbulatCardLink PACLink with(nolock) on PACLink.PersonAmbulatCard_id =PAC.PersonAmbulatCard_id AND VPC.PersonCard_id = PACLink.PersonCard_id
						left join v_Lpu l with (nolock) on l.Lpu_id = vpc.Lpu_id
					where LpuAttachType_id=2 AND VPC.PersonCard_id = PACLink.PersonCard_id AND Person_id = p.Person_id
					ORDER BY VPC.PersonCard_id DESC
				) pcGin --МО прикрепления (гин.)
				outer apply(
					select top 1 VPC.PersonCard_id, LpuAttachType_id, RTrim(l.Lpu_Nick) as Lpu_Nick
					from v_PersonCard VPC with(nolock)
						inner join v_PersonAmbulatCardLink PACLink with(nolock) on PACLink.PersonAmbulatCard_id =PAC.PersonAmbulatCard_id AND VPC.PersonCard_id = PACLink.PersonCard_id
						left join v_Lpu l with (nolock) on l.Lpu_id = vpc.Lpu_id
					where LpuAttachType_id=3 AND VPC.PersonCard_id = PACLink.PersonCard_id AND Person_id = p.Person_id
					ORDER BY VPC.PersonCard_id DESC
				) pcStom ----МО прикрепления (стом.)	
				outer apply(
					select top 1
						PAC.PersonAmbulatCard_id,
						PAC.PersonAmbulatCard_Num,
						PACL.LpuBuilding_id,
						LB.LpuBuilding_Name,
						PACL.PersonAmbulatCardLocat_begDate,
						MSF.MedStaffFact_id,
						MSF.Person_Fio as FIO,
						MSF.LpuBuilding_id AS MSF_LpuBuilding_id,
						post.PostMed_Name as MedStaffFact,
						PACL.AmbulatCardLocatType_id,
						PACL.PersonAmbulatCardLocat_Desc,
						isnull(ACLT.AmbulatCardLocatType_Name, '') + isnull(', '+LB.LpuBuilding_Name, '') +  isnull(', '+MSF.Person_Fio, '') as MapLocation
					from v_PersonAmbulatCardLocat PACL with(nolock)
						left join AmbulatCardLocatType ACLT with(nolock) on PACL.AmbulatCardLocatType_id = ACLT.AmbulatCardLocatType_id
						left join v_LpuBuilding LB with (nolock) on LB.LpuBuilding_id = PACL.LpuBuilding_id
						left join v_MedStaffFact MSF with(nolock) on MSF.MedStaffFact_id = PACL.MedStaffFact_id
						left join v_PostMed post with(nolock) on MSF.Post_id = post.PostMed_id
					where 1=1
						AND PAC.PersonAmbulatCard_id = PACL.PersonAmbulatCard_id
						--AND isnull(ACLB.AmbulatCardLpuBuilding_begDate, dbo.tzGetDate()) <= dbo.tzGetDate() AND isnull(ACLB.AmbulatCardLpuBuilding_endDate, dbo.tzGetDate()) >= dbo.tzGetDate()
					ORDER BY PACL.PersonAmbulatCardLocat_begDate DESC
				) ambulatCard -- амбулаторная карта
				-- end from
			WHERE
				-- where
				1=1
				AND PAC.Person_id is not null
				AND p.Person_Surname is not null
				AND PAC.Lpu_id = :Lpu_id
				{$filter}
				-- end where
			order by
				-- order by
				p.Person_Surname
				-- end order by
		";
		//echo getDebugSQL($query, $params); die();
		return $this->getPagingResponse($query, $params, $data['start'], $data['limit'], true);		
	}
	
	/**
	 * Запросить или отклонить врачем амб. карту у картохранилища 
	 */
	function setAmbulatCardRequest($data){
		if(empty($data['TimetableGraf_id']) || empty($data['PersonAmbulatCard_id'])) return false;
		$procedure = "p_AmbulatCardRequest_ins";
		$queryParams = array();
		if(!empty($data['AmbulatCardRequest_id'])) {
			$procedure = "p_AmbulatCardRequest_upd";
			$queryParams['AmbulatCardRequest_id'] = $data['AmbulatCardRequest_id'];
		}else{
			$queryParams['AmbulatCardRequest_id'] = null;
		}
		$queryParams['TimetableGraf_id'] = $data['TimetableGraf_id'];
		$queryParams['PersonAmbulatCard_id'] = $data['PersonAmbulatCard_id'];
		$queryParams['AmbulatCardRequestStatus_id'] = (!empty($data['AmbulatCardRequestStatus_id'])) ? $data['AmbulatCardRequestStatus_id'] : null;
		$queryParams['MedStaffFact_id'] = (!empty($data['MedStaffFact_id'])) ? $data['MedStaffFact_id'] : null;
		$queryParams['pmUser_id'] = $data['pmUser_id'];
		
		if($queryParams['AmbulatCardRequestStatus_id'] == 2){
			if(empty($queryParams['MedStaffFact_id'])){
				return array(array('Error_Msg' => 'Не указан врач'));
			}
			//если пришел запрос врача, что карта на приеме, то проверим последнее движение карты
			//и если в последнем движении нет этого врача, то создадим движение амб.карты
			$sql = "
				SELECT top 1
					PACL.PersonAmbulatCardLocat_id,
					PACL.PersonAmbulatCard_id,
					PACL.MedStaffFact_id,
					PACL.LpuBuilding_id,
					PACL.AmbulatCardLocatType_id,
					PACL.PersonAmbulatCardLocat_begDate,
					PACL.PersonAmbulatCardLocat_Desc,
					PACL.PersonAmbulatCardLocat_OtherLocat
				FROM v_PersonAmbulatCardLocat PACL with(nolock)
				WHERE 1=1
					AND PACL.PersonAmbulatCard_id = :PersonAmbulatCard_id
				ORDER BY PACL.PersonAmbulatCardLocat_begDate DESC
			";
			$resLocatCard = $this->queryResult($sql, $queryParams);
			$locatCard = (is_array($resLocatCard) && count($resLocatCard)>0) ? $resLocatCard[0] : false;
			//AmbulatCardLocatType_id
			if(!$locatCard || ($locatCard['AmbulatCardLocatType_id'] != 2 || $locatCard['MedStaffFact_id'] != $queryParams['MedStaffFact_id']) ){
				//создаем движение амбулаторной карты
				$locatCard['pmUser_id'] = $data['pmUser_id'];
				$locatCard['Server_id'] = $data['Server_id'];
				$locatCard['MedStaffFact_id'] = $queryParams['MedStaffFact_id'];
				$locatCard['PersonAmbulatCard_id'] = $queryParams['PersonAmbulatCard_id'];
				$locatCard['PersonAmbulatCardLocat_id'] = (!empty($locatCard['PersonAmbulatCardLocat_id'])) ? $locatCard['PersonAmbulatCardLocat_id'] : null;
				$locatCard['AmbulatCardLocatType_id'] = 2;
				$locatCard['PersonAmbulatCardLocat_Desc'] = 'врач отметил, что карта у него на приёме ';
				$this->savePersonAmbulatCardLocat($locatCard);
			}
			
			//проверим статус запроса
			$sql = "
				SELECT top 1
					ACR.AmbulatCardRequestStatus_id,
					ACR.TimeTableGraf_id
				FROM v_AmbulatCardRequest ACR with(nolock)
				WHERE 1=1
					AND ACR.PersonAmbulatCard_id = :PersonAmbulatCard_id
					AND ACR.TimetableGraf_id = :TimetableGraf_id
				ORDER BY ACR.AmbulatCardRequest_updDT DESC
			";
			$resStatusCard = $this->queryResult($sql, $queryParams);
			$statusCard = (is_array($resStatusCard) && count($resStatusCard)>0) ? $resStatusCard[0] : false;
			
			if(!$statusCard || $statusCard['AmbulatCardRequestStatus_id'] != 1){
				//если запроса не было, то ничего дальше не делаем
				return array(array('success' => true));
			}
		}
		
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @Res = :AmbulatCardRequest_id;
			exec ".$procedure."
				@AmbulatCardRequest_id = @Res output,
				@TimetableGraf_id = :TimetableGraf_id,
				@PersonAmbulatCard_id = :PersonAmbulatCard_id,
				@AmbulatCardRequestStatus_id = :AmbulatCardRequestStatus_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as AmbulatCardRequest_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";	
		
		//echo getDebugSQL($query, $queryParams); die();
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {			
			return array(array('Error_Msg' => 'Ошибка при запросе амб. карты у картохранилища'));
		}
		
		if($queryParams['AmbulatCardRequestStatus_id'] == 1){
			//привязываем амбулаторную карту к бирке
			$res = $this->savePersonAmbulatCardInTimetableGraf($data);
		}
		
		return $result->result('array');
	}
	
	/**
	 * расположения службы регистратуры пол-ки в МО, к которому имеет доступ пользователь, выполнивший действие
	 */
	function getServiceLocationsUser($data){
		//if(empty($data['MedPersonal_id']) || empty($data['Lpu_id'])) return false;
		if(empty($data['pmUser_id']) || empty($data['Lpu_id'])) return false;
		$queryParams = array(
			'MedPersonal_id' => $data['MedPersonal_id'],
			'pmUser_id' => $data['pmUser_id'],
			'Lpu_id' => $data['Lpu_id'],
			'MedServiceType_SysNick' => 'regpol',
			'pmUserCacheGroup_Code' => 'StorageCard'
		);
		$sql = "
			SELECT distinct
				MS.MedService_id,
				MS.Lpu_id,
				isnull(MS.LpuBuilding_id, '') as LpuBuilding_id
			FROM v_MedService MS with(nolock)
				left join v_MedServiceType MST with(nolock) on MS.MedServiceType_id = MST.MedServiceType_id
				left join v_MedServiceMedPersonal MSMP with(nolock) on MS.MedService_id = MSMP.MedService_id
				left join pmUserCache puc with (nolock) on puc.MedPersonal_id = MSMP.MedPersonal_id
				left join pmUserCacheGroupLink PCGL with (nolock) on PCGL.pmUserCache_id = puc.PMUser_id
				left join pmUserCacheGroup PCG WITH(nolock) on PCG.pmUserCacheGroup_id = PCGL.pmUserCacheGroup_id
			WHERE 1=1
				AND MST.MedServiceType_SysNick = :MedServiceType_SysNick
				AND MS.Lpu_id = :Lpu_id
				AND puc.Lpu_id = :Lpu_id
				AND MSMP.MedPersonal_id = :MedPersonal_id
				AND PCG.pmUserCacheGroup_Code = :pmUserCacheGroup_Code
			ORDER BY LpuBuilding_id ASC";
		//--AND MSMP.MedPersonal_id = :MedPersonal_id
		// ND поменять потом на ASC
		
		$result = $this->queryResult($sql, $queryParams);
		return $result;
	}
	
	/**
	 * Получаем информацию об прикреплении амбулаторной карты последнего существующего прикрепления пациента
	 */
	function getAttachmentAmbulatoryCardToPersonCard($data){
		if(empty($data['Lpu_id']) && (empty($data['PersonCard_id']) || empty($data['Person_id'])) ) return false;
		$where = '';
		$queryParams = array(
			'Lpu_id' => $data['Lpu_id']
		);
		if(!empty($data['PersonCard_id'])){
			$where .= ' AND PC.PersonCard_id = :PersonCard_id ';
			$queryParams['PersonCard_id'] = $data['PersonCard_id'];
		}
		if(!empty($data['Person_id'])){
			$where .= ' AND PC.Person_id = :Person_id ';
			$queryParams['Person_id'] = $data['Person_id'];
		}
		if(!empty($data['LpuAttachType_id'])){
			$where .= ' AND PC.LpuAttachType_id = :LpuAttachType_id ';
			$queryParams['LpuAttachType_id'] = $data['LpuAttachType_id'];
		}else{
			$where .= " and LAT.LpuAttachType_SysNick in ('main', 'gin', 'stom') ";
		}
		$sql = "
			SELECT top 1
				PC.PersonCard_id,
				PC.PersonCard_Code,
				PC.LpuAttachType_id,
				PC.PersonCard_begDate,
				PC.PersonCard_endDate,
				isnull(PAC.PersonAmbulatCard_Num, PACnum.PersonAmbulatCard_Num) as PersonAmbulatCard_Num,
				isnull(PAC.PersonAmbulatCard_id, PACnum.PersonAmbulatCard_id) as PersonAmbulatCard_id,
				ACLB.AmbulatCardLpuBuilding_id,
				ACLB.AmbulatCardLpuBuilding_begDate,
				ACLB.AmbulatCardLpuBuilding_endDate,
				PC.Person_id
			FROM v_PersonCard PC with(nolock)
				left join LpuAttachType LAT with (nolock) on PC.LpuAttachType_id = LAT.LpuAttachType_id
				left join PersonAmbulatCardLink PACL with(nolock) on PACL.PersonCard_id = PC.PersonCard_id
				left join PersonAmbulatCard PAC with(nolock) on PAC.PersonAmbulatCard_id = PACL.PersonAmbulatCard_id
				left join PersonAmbulatCard PACnum with(nolock) on PACnum.PersonAmbulatCard_Num = PC.PersonCard_Code and PC.Lpu_id = PACnum.Lpu_id and PACnum.Person_id = PC.Person_id
				outer apply(
					SELECT TOP 1 AmbulatCardLpuBuilding_id, ACLB.AmbulatCardLpuBuilding_begDate, ACLB.AmbulatCardLpuBuilding_endDate
					FROM v_AmbulatCardLpuBuilding ACLB with(NOLOCK)
					WHERE ACLB.PersonAmbulatCard_id = COALESCE(PAC.PersonAmbulatCard_id, PACnum.PersonAmbulatCard_id) 
					ORDER BY ACLB.AmbulatCardLpuBuilding_begDate DESC
				) ACLB
			WHERE 1=1
				--AND PC.LpuAttachType_id
				--AND PC.Person_id = 142375
				AND PC.Lpu_id = :Lpu_id
				AND PC.PersonCard_endDate is null
				{$where}
			ORDER BY PC.PersonCard_begDate DESC";
				
		$result = $this->queryResult($sql, $queryParams);
		return $result;
	}
}

?>
