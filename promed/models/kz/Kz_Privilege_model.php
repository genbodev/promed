<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* kz_Privilege - модель для работы со льготами людей (Казахстан)
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package			Common
* @access			public
* @copyright		Copyright (c) 2017 Swan Ltd.
* @author			Stas Bykov aka Savage (savage1981@gmail.com)
*/
require_once(APPPATH.'models/Privilege_model.php');

class Kz_Privilege_model extends Privilege_model {
	/**
	 *	Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Сохранение льготы у человека
	 */
	public function savePersonPrivilege($data) {
		$response = parent::savePersonPrivilege($data);

		if ( empty($response[0]['Error_Msg']) && !empty($data['SubCategoryPrivType_id']) ) {
			$data['PersonPrivilege_id'] = $response[0]['PersonPrivilege_id'];
			$PersonPrivilegeSubCategoryPrivType_id = $this->getFirstResultFromQuery("
				select top 1 PersonPrivilegeSubCategoryPrivType_id 
				from r101.v_PersonPrivilegeSubCategoryPrivType (nolock) 
				where PersonPrivilege_id = :PersonPrivilege_id
			", $data);
			$procedure = empty($PersonPrivilegeSubCategoryPrivType_id) ? "p_PersonPrivilegeSubCategoryPrivType_ins" : "p_PersonPrivilegeSubCategoryPrivType_upd";
			$data['PersonPrivilegeSubCategoryPrivType_id'] = $PersonPrivilegeSubCategoryPrivType_id;
			$query = "
				declare
					@Res bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);
				set @Res = :PersonPrivilegeSubCategoryPrivType_id;
				exec r101.{$procedure}
					@PersonPrivilegeSubCategoryPrivType_id = @Res output,
					@PersonPrivilege_id = :PersonPrivilege_id,
					@SubCategoryPrivType_id = :SubCategoryPrivType_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @Res as PersonPrivilegeSubCategoryPrivType_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			$this->queryResult($query, $data);
		}

		return $response;
	}

	/**
	* Получение списка льгот человека
	*/
	public function loadPersonPrivilegeList($data) {
		$filter = null;

		$privilegeFilter = getAccessRightsPrivilegeTypeFilter("PT.PrivilegeType_id");
		if (!empty($privilegeFilter)) {
			$filter .= " and $privilegeFilter";
		}
		
		return $this->queryResult("
			select
				[Lpu].[Lpu_id],
				[PP].[Person_id],
				[PP].[PersonEvn_id],
				[PP].[PersonPrivilege_id],
				[PT].[ReceptFinance_id],
				[PP].[Server_id],
				[PP].[PrivilegeType_id],
				[PP].[PrivilegeType_Code],
				RTRIM(PP.PrivilegeType_Name) + isnull('<br>' + SCPT.SubCategoryPrivType_Name, '') as PrivilegeType_Name,
				convert(varchar(10), [PP].[PersonPrivilege_begDate], 104) as [Privilege_begDate],
				convert(varchar(10), [PP].[PersonPrivilege_endDate], 104) as [Privilege_endDate],
				-- использовать ID не очень хорошо, но приходится - но лучше добавить во вьюху ReceptFinance_id
				case when [PT].[ReceptFinance_id] = 1 then case when [PR].PersonRefuse_IsRefuse = 2 then 'true' else 'false' end else '' end as [Privilege_Refuse],
				case when [PT].[ReceptFinance_id] = 1 then case when [PR2].PersonRefuse_IsRefuse = 2 then 'true' else 'false' end else '' end as [Privilege_RefuseNextYear],
				case
					when PPs.Server_id = 3 then 'ПФР'
					when PPs.Server_id = 7 then 'Минздрав'
					else ISNULL(Lpu.Lpu_Nick,'') 
				end as Lpu_Name,
				PPs.Server_id as Server_id
			from [v_PersonPrivilege] [PP] with (nolock) -- здесь во вьюху надо добавить ReceptFinance_id 
				inner join PersonPrivilege PPs with(nolock) on PPs.PersonPrivilege_id = PP.PersonPrivilege_id
				inner join v_PrivilegeType [PT] with(nolock) on [PT].[PrivilegeType_id] = [PP].[PrivilegeType_id]
				left join [v_PersonRefuse] [PR] with (nolock) on [PR].[Person_id] = [PP].[Person_id]
					and [PR].[PersonRefuse_Year] = year(dbo.tzGetDate())
				left join [v_PersonRefuse] [PR2] with (nolock) on [PR2].[Person_id] = [PP].[Person_id]
					and [PR2].[PersonRefuse_Year] = year(dbo.tzGetDate()) +1 
				left join [v_Lpu] [Lpu] with (nolock) on [Lpu].[Lpu_id] = [PP].[Lpu_id]
				left join r101.v_PersonPrivilegeSubCategoryPrivType PPSCPT (nolock) on PPSCPT.PersonPrivilege_id = PP.PersonPrivilege_id
				left join r101.SubCategoryPrivType SCPT (nolock) on SCPT.SubCategoryPrivType_id = PPSCPT.SubCategoryPrivType_id
			where (1 = 1)
				and [PP].[Person_id] = :Person_id
				{$filter}
		", array(
			'Person_id' => $data['Person_id'],
			'Lpu_id' => $data['Lpu_id']
		));
	}

	/**
	* Получение данных для формы редактирования льготы
	*/
	public function loadPrivilegeEditForm($data) {
		$params = array(
			'PersonPrivilege_id' => $data['PersonPrivilege_id']
		);
		$query = "
			select top 1
				pp.PrivilegeType_id,
				pp.PersonPrivilege_id,
				pp.PersonPrivilege_IsAddMZ,
				convert(varchar(10), pp.PersonPrivilege_begDate, 104) as Privilege_begDate,
				convert(varchar(10), pp.PersonPrivilege_endDate, 104) as Privilege_endDate,
				ppscpt.SubCategoryPrivType_id,
				case when er.EvnRecept_Count > 0 then 1 else 0 end as hasRecepts
			from 
				PersonPrivilege pp with (nolock)
				left join r101.v_PersonPrivilegeSubCategoryPrivType ppscpt (nolock) on ppscpt.PersonPrivilege_id = pp.PersonPrivilege_id
				outer apply (
					select top 1 count(*) as EvnRecept_Count
					from v_EvnRecept er with(nolock)
					where er.PersonPrivilege_id = pp.PersonPrivilege_id
				) er
			where
				pp.PersonPrivilege_id = ISNULL(:PersonPrivilege_id, 0)
			order by 
				pp.PersonPrivilege_id desc
		";
		return $this->queryResult($query, $params);
	}

	/**
	* Получение списка льгот человека для просмотра в ЭМК
	*/
	public function getPersonPrivilegeViewData($data) {
		$filter = "";

		$privilegeFilter = getAccessRightsPrivilegeTypeFilter("PT.PrivilegeType_id");
		if (!empty($privilegeFilter)) {
			$filter .= " and $privilegeFilter";
		}
		
		return $this->queryResult("
			select
				PP.Person_id,
				PP.PersonPrivilege_id,
				PP.PersonPrivilege_id as ExpertHistory_id,
				convert(varchar(10), PP.PersonPrivilege_begDate, 104) as PersonPrivilege_begDate,
				convert(varchar(10), PP.PersonPrivilege_endDate, 104) as PersonPrivilege_endDate,
				ISNULL(PT.PrivilegeType_Name, '') as PrivilegeType_Name,
				PP.pmUser_insID,
				PP.Lpu_id,
				'' as PersonPrivilege_IsActual,
				SubCategoryPrivType_Name,
				isnull(PCT.PrivilegeCloseType_Name, '') as PrivilegeCloseType_Name
			from v_PersonPrivilege PP with (nolock)
				inner join PrivilegeType PT with (nolock) on PT.PrivilegeType_id = PP.PrivilegeType_id
				left join v_PrivilegeCloseType PCT with (nolock) on PCT.PrivilegeCloseType_id = PP.PrivilegeCloseType_id
				left join r101.v_PersonPrivilegeSubCategoryPrivType PPSCPT (nolock) on PPSCPT.PersonPrivilege_id = PP.PersonPrivilege_id
				left join r101.v_SubCategoryPrivType SCPT (nolock) on SCPT.SubCategoryPrivType_id = PPSCPT.SubCategoryPrivType_id
			where 
				PP.Person_id = ISNULL(:Person_id, 0)
				{$filter}
			order by
				PP.PersonPrivilege_begDate
		", array(
			'Person_id' => $data['Person_id'],
			'Lpu_id' => $data['Lpu_id']
		));
	}
}
