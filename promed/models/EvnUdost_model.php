<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Udost_model - модель для работы с удостоверениями
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      DLO
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      14.08.2009
*/

class EvnUdost_model extends CI_Model {
	/**
	 *	Конструктор
	 */
	function __construct() {
		parent::__construct();
	}


	/**
	 *	Method description
	 */
	function CheckEvnUdost($data) {
		/*
		$query = "
			select
				COUNT(case when EvnUdost_Ser = :EvnUdost_Ser and EvnUdost_Num = :EvnUdost_Num then EvnUdost_id else null end) as val1,
				COUNT(case when PrivilegeType_id = :PrivilegeType_id and Person_id = :Person_id and Evn_disDT is null then EvnUdost_id else null end) as val2
			from EvnUdost with (nolock)
			inner join Evn with (nolock) on EvnUdost.EvnUdost_id = Evn.Evn_id and isnull(Evn.Evn_deleted,1) = 1
			where Lpu_id = :Lpu_id
				and EvnUdost_id <> ISNULL(:EvnUdost_id, 0)
		";
		*/
		// В качестве эксперимента по задаче https://redmine.swan.perm.ru/issues/4422 (с) Night
		// Сделал двумя разными запросами с top 1, посмотрим по скорости что будет 
		$query = "
			Select 
			(
				select top 1 1 as val
					from EvnUdost with (nolock)
					inner join Evn with (nolock) on EvnUdost.EvnUdost_id = Evn.Evn_id and isnull(Evn.Evn_deleted,1) = 1
					where 
						Lpu_id = :Lpu_id and 
						EvnUdost_Ser = :EvnUdost_Ser and EvnUdost_Num = :EvnUdost_Num and 
						EvnUdost_id <> ISNULL(:EvnUdost_id, 0)
			) as val1,
			(
				select top 1 1 as val
					from EvnUdost with (nolock)
					inner join Evn with (nolock) on EvnUdost.EvnUdost_id = Evn.Evn_id and isnull(Evn.Evn_deleted,1) = 1
					where 
						Lpu_id = :Lpu_id and 
						PrivilegeType_id = :PrivilegeType_id and Person_id = :Person_id and Evn_disDT is null and 
						EvnUdost_id <> ISNULL(:EvnUdost_id, 0)
			) as val2
		";

		$result = $this->db->query($query, array(
			'EvnUdost_id' => $data['EvnUdost_id'],
			'EvnUdost_Num' => $data['EvnUdost_Num'],
			'EvnUdost_Ser' => $data['EvnUdost_Ser'],
			'Lpu_id' => $data['Lpu_id'],
			'Person_id' => $data['Person_id'],
			'PrivilegeType_id' => $data['PrivilegeType_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 *	Method description
	 */
	function deleteEvnUdost($data) {
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_EvnUdost_del
				@EvnUdost_id = :EvnUdost_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$result = $this->db->query($query, array(
			'EvnUdost_id' => $data['EvnUdost_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление осложнения)'));
		}
	}


	/**
	 *	Method description
	 */
	function getUdostFields($data) {
		$queryParams = array();

		if ( !isMinZdrav() ) {
			$lpu_filter = "and EU.Lpu_id = :Lpu_id";
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}
		else {
			$lpu_filter = "";
		}

		$query = "
			select top 10
				[eu].[EvnUdost_Num],
				[pt].[PrivilegeType_Code],
				ltrim(rtrim([ps].[Person_SurName])) + ' ' + ltrim(rtrim([ps].[Person_FirName])) + ' ' + ltrim(rtrim([ps].[Person_SecName])) as [Person_FIO],
				convert(varchar(10), [ps].[Person_BirthDay], 104) as [Person_BirthDay],
				[ps].[Person_Snils],
				[og].[Org_OGRN],
				convert(varchar(10), cast([eu].[EvnUdost_setDT] as datetime), 104) as [EvnUdost_setDT]
			from [v_EvnUdost] [eu] with (nolock)
				left join [v_Person_All] [ps] with (nolock) on [eu].[PersonEvn_id] = [ps].[PersonEvn_id]
					and [eu].[Server_id] = [ps].[Server_id]
				left join [PrivilegeType] [pt] with (nolock) on [pt].[PrivilegeType_id] = [eu].[PrivilegeType_id]
				left join [Lpu] [lp] with (nolock) on [lp].[Lpu_id] = [eu].[Lpu_id]
				left join [Org] [og] with (nolock) on [og].[Org_id] = [lp].[Org_id]
			where (1 = 1)
				and [eu].[EvnUdost_id] = :EvnUdost_id
				" . $lpu_filter . "
		";

		$queryParams['EvnUdost_id'] = $data['EvnUdost_id'];

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			 return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 *	Method description
	 */
	function loadEvnUdostEditForm($data) {
		$filter = "(1 = 1)";
		$queryParams = array();

		$filter .= " and EvnUdost_id = :EvnUdost_id";
		$queryParams['EvnUdost_id'] = $data['EvnUdost_id'];

		if ( $data['Lpu_id'] > 0 ) {
			$filter .= " and Lpu_id = :Lpu_id";
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}

		$query = "
			select
				convert(varchar(10), EvnUdost_setDT, 104) as EvnUdost_setDate,
				convert(varchar(10), EvnUdost_disDT, 104) as EvnUdost_disDate,
				RTRIM(EvnUdost_Ser) as EvnUdost_Ser,
				RTRIM(EvnUdost_Num) as EvnUdost_Num,
				isnull(Lpu_id, 0) as Lpu_id,
				isnull(PersonEvn_id, 0) as PersonEvn_id,
				Server_id,
				isnull(DeseaseGroup_id, '') as DeseaseGroup_id,
				isnull(PrivilegeType_id, '') as PrivilegeType_id
			from v_EvnUdost with (nolock)
			where " . $filter . "
		;";
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (получение данных по удостоверению)');
		}
	}


	/**
	 *	Method description
	 */
	function loadEvnUdostList($data) {
		$filter = "(1 = 1)";
		$queryParams = array();

		if ( isset($data['Person_id']) ) {
			$filter .= " and EU.Person_id = :Person_id";
			$queryParams['Person_id'] = $data['Person_id'];
		}

		if ( $data['Lpu_id'] > 0 ) {
			$filter .= " and EU.Lpu_id = :Lpu_id";
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}

		if ( isset($data['PrivilegeType_id']) ) {
			$filter .= " and PT.PrivilegeType_id = :PrivilegeType_id";
			$queryParams['PrivilegeType_id'] = $data['PrivilegeType_id'];
		}
		
		$query = "
			select
				EU.Server_id,
				EU.Person_id,
				EU.PersonEvn_id,
				PT.PrivilegeType_id,
				EU.EvnUdost_id,
				convert(varchar(10), EU.EvnUdost_setDT, 104) as EvnUdost_setDate,
				convert(varchar(10), EU.EvnUdost_disDT, 104) as EvnUdost_disDate,
				RTrim(EU.EvnUdost_Ser) as EvnUdost_Ser,
				RTrim(EU.EvnUdost_Num) as EvnUdost_Num
			from v_EvnUdost EU with (nolock)
				inner join PrivilegeType PT with (nolock) on PT.PrivilegeType_id = EU.PrivilegeType_id
					and isnumeric(PT.PrivilegeType_Code) = 1
			where " . $filter . "
			order by EU.EvnUdost_setDT
		;";
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 *	Method description
	 */
	function loadUdostList($data, $getCount = false) {
		$filter = "(1 = 1)";
		$queryParams = array();

		if ( $data['Lpu_id'] > 0 ) {
			$filter .= " and [EU].[Lpu_id] = :Lpu_id";
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}

		if ( isset($data['Person_Surname']) ) {
			$filter .= " and [PS].[Person_SurName] like :Person_Surname";
			$queryParams['Person_Surname'] = $data['Person_Surname'] . "%";
		}

		if ( isset($data['Work_Period'][0]) ) {
			$filter .= " and [EU].[EvnUdost_setDate] >= :WorkDate_From";
			$queryParams['WorkDate_From'] = $data['Work_Period'][0];
		}

		if ( isset($data['Work_Period'][1]) ) {
			$filter .= " and [EU].[EvnUdost_setDate] <= :WorkDate_To";
			$queryParams['WorkDate_To'] = $data['Work_Period'][1];
		}
		
		if ( isset($data['soc_card_id']) && strlen($data['soc_card_id']) >= 25  )
		{	
			$filter .= " and LEFT(ps.Person_SocCardNum, 19) = :SocCardNum ";
			$queryParams['SocCardNum'] = substr($data['soc_card_id'], 0, 19);
		}

		$query = "
			select
				-- select
				[EU].[EvnUdost_id],
				[EU].[Person_id],
				[EU].[PersonEvn_id],
				[EU].[Server_id],
				RTRIM([PS].[Person_SurName]) as [Person_Surname],
				RTRIM([PS].[Person_FirName]) as [Person_Firname],
				RTRIM([PS].[Person_SecName]) as [Person_Secname],
				convert(varchar(10), [PS].[Person_BirthDay], 104) as [Person_Birthday],
				convert(varchar(10), [PS].[Person_DeadDT], 104) as [Person_deadDT],
				[PT].[PrivilegeType_Code],
				convert(varchar(10), [EU].[EvnUdost_setDT], 104) as [EvnUdost_setDate],
				convert(varchar(10), [EU].[EvnUdost_disDT], 104) as [EvnUdost_disDate],
				ISNULL([YesNo].[YesNo_Name], 'Нет') as [Privilege_Refuse],
				RTRIM([EU].[EvnUdost_Ser]) as [EvnUdost_Ser],
				RTRIM([EU].[EvnUdost_Num]) as [EvnUdost_Num]
				-- end select
			from
				-- from
				[v_EvnUdost] [EU] with (NOLOCK)
				inner join [v_PersonState] [PS] with (nolock) on [PS].[Person_id] = [EU].[Person_id]

				inner join [PrivilegeType] [PT] with (nolock) on [PT].[PrivilegeType_id] = [EU].[PrivilegeType_id]
					and [PT].[PrivilegeType_Code] < 500
				left join v_WhsDocumentCostItemType WDCIT with(nolock) on WDCIT.WhsDocumentCostItemType_id = PT.WhsDocumentCostItemType_id
				left join [v_PersonRefuse] [PR] with (nolock) on [PR].[Person_id] = [PS].[Person_id]
					and WDCIT.WhsDocumentCostItemType_Nick = 'fl' and [PT].[PrivilegeType_Code] < 500
				left join [YesNo] with (nolock) on [YesNo].[YesNo_id] = [PR].[PersonRefuse_IsRefuse]
				-- end from
			where
				-- where
				" . $filter . "
				-- end where
		";

		if ( $getCount === true) {
			$query = getCountSQLPH($query);
		}

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 *	Method description
	 */
	function saveEvnUdost($data) {
		$procedure = '';

		if ( !isset($data['EvnUdost_id']) || $data['EvnUdost_id'] <= 0 ) {
			$procedure = 'p_EvnUdost_ins';
		}
		else {
			$procedure = 'p_EvnUdost_upd';
		}

		//Хитрый финт ушами для поиска нужного Server_id
		$query_get_Server_id = "
			select Server_id
			from v_PersonEvn where PersonEvn_id = :PersonEvn_id
		";
		$result_get_Server_id = $this->db->query($query_get_Server_id, array('PersonEvn_id' => $data['PersonEvn_id']));
		if(is_object($result_get_Server_id))
		{
			$result_get_Server_id = $result_get_Server_id->result('array');
			if(is_array($result_get_Server_id) && count($result_get_Server_id) > 0)
				$data['Server_id'] = $result_get_Server_id[0]['Server_id'];
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :EvnUdost_id;
			exec " . $procedure . "
				@EvnUdost_id = @Res output,
				@Lpu_id = :Lpu_id,
				@Server_id = :Server_id,
				@PersonEvn_id = :PersonEvn_id,
				@EvnUdost_setDT = :EvnUdost_setDate,
				@EvnUdost_disDT = :EvnUdost_disDate,
				@EvnUdost_Ser = :EvnUdost_Ser,
				@EvnUdost_Num = :EvnUdost_Num,
				@PrivilegeType_id = :PrivilegeType_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as EvnUdost_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'EvnUdost_disDate' => $data['EvnUdost_disDate'],
			'EvnUdost_id' => ( !isset($data['EvnUdost_id']) || $data['EvnUdost_id'] <= 0 ? NULL : $data['EvnUdost_id'] ),
			'EvnUdost_Num' => $data['EvnUdost_Num'],
			'EvnUdost_Ser' => $data['EvnUdost_Ser'],
			'EvnUdost_setDate' => $data['EvnUdost_setDate'],
			'Lpu_id' => $data['Lpu_id'],
			'PersonEvn_id' => $data['PersonEvn_id'],
			'pmUser_id' => $data['pmUser_id'],
			'PrivilegeType_id' => $data['PrivilegeType_id'],
			'Server_id' => $data['Server_id']
		);

		//echo getDebugSQL($query, $queryParams);die;
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
}
