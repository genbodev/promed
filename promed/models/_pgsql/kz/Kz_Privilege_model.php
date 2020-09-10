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
require_once(APPPATH.'models/_pgsql/Privilege_model.php');

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
				select PersonPrivilegeSubCategoryPrivType_id  as \"PersonPrivilegeSubCategoryPrivType_id\"
				from r101.v_PersonPrivilegeSubCategoryPrivType  
				where PersonPrivilege_id = :PersonPrivilege_id
				limit 1
			", $data);
			$procedure = empty($PersonPrivilegeSubCategoryPrivType_id) ? "p_PersonPrivilegeSubCategoryPrivType_ins" : "p_PersonPrivilegeSubCategoryPrivType_upd";
			$data['PersonPrivilegeSubCategoryPrivType_id'] = $PersonPrivilegeSubCategoryPrivType_id;
			$query = "
				select PersonPrivilegeSubCategoryPrivType_id as \"PersonPrivilegeSubCategoryPrivType_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"                
				from r101.{$procedure}(
					PersonPrivilegeSubCategoryPrivType_id := :PersonPrivilegeSubCategoryPrivType_id,
					PersonPrivilege_id := :PersonPrivilege_id,
					SubCategoryPrivType_id := :SubCategoryPrivType_id,
					pmUser_id := :pmUser_id);
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
				Lpu.Lpu_id as \"Lpu_id\",
				PP.Person_id as \"Person_id\",
				PP.PersonEvn_id as \"PersonEvn_id\",
				PP.PersonPrivilege_id as \"PersonPrivilege_id\",
				PT.ReceptFinance_id as \"ReceptFinance_id\",
				PP.Server_id as \"Server_id\",
				PP.PrivilegeType_id as \"PrivilegeType_id\",
				PP.PrivilegeType_Code as \"PrivilegeType_Code\",
				RTRIM(PP.PrivilegeType_Name) || COALESCE('<br>' || SCPT.SubCategoryPrivType_Name, '') as \"PrivilegeType_Name\",
				to_char(PP.PersonPrivilege_begDate, 'DD.MM.YYYY') as \"Privilege_begDate\",
				to_char(PP.PersonPrivilege_endDate, 'DD.MM.YYYY') as \"Privilege_endDate\",
				-- использовать ID не очень хорошо, но приходится - но лучше добавить во вьюху ReceptFinance_id
				case when PT.ReceptFinance_id = 1 then case when PR.PersonRefuse_IsRefuse = 2 then 'true' else 'false' end else '' end as \"Privilege_Refuse\",
				case when PT.ReceptFinance_id = 1 then case when PR2.PersonRefuse_IsRefuse = 2 then 'true' else 'false' end else '' end as \"Privilege_RefuseNextYear\",
				case
					when PPs.Server_id = 3 then 'ПФР'
					when PPs.Server_id = 7 then 'Минздрав'
					else COALESCE(Lpu.Lpu_Nick,'') 
				end as \"Lpu_Name\",
				PPs.Server_id as \"Server_id\"
			from v_PersonPrivilege PP  -- здесь во вьюху надо добавить ReceptFinance_id 
				inner join PersonPrivilege PPs  on PPs.PersonPrivilege_id = PP.PersonPrivilege_id
				inner join v_PrivilegeType PT  on PT.PrivilegeType_id = PP.PrivilegeType_id
				left join v_PersonRefuse PR  on PR.Person_id = PP.Person_id
					and PR.PersonRefuse_Year = date_part('year', dbo.tzGetDate())
				left join v_PersonRefuse PR2  on PR2.Person_id = PP.Person_id
					and PR2.PersonRefuse_Year = date_part('year', dbo.tzGetDate()) +1 
				left join v_Lpu Lpu  on Lpu.Lpu_id = PP.Lpu_id
				left join r101.v_PersonPrivilegeSubCategoryPrivType PPSCPT  on PPSCPT.PersonPrivilege_id = PP.PersonPrivilege_id
				left join r101.SubCategoryPrivType SCPT  on SCPT.SubCategoryPrivType_id = PPSCPT.SubCategoryPrivType_id
			where (1 = 1)
				and PP.Person_id = :Person_id
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
			select 
				pp.PrivilegeType_id as \"PrivilegeType_id\",
				pp.PersonPrivilege_id as \"PersonPrivilege_id\",
				pp.PersonPrivilege_IsAddMZ as \"PersonPrivilege_IsAddMZ\",
				to_char(pp.PersonPrivilege_begDate, 'DD.MM.YYYY') as \"Privilege_begDate\",
				to_char(pp.PersonPrivilege_endDate, 'DD.MM.YYYY') as \"Privilege_endDate\",
				ppscpt.SubCategoryPrivType_id as \"SubCategoryPrivType_id\",
				case when er.EvnRecept_Count > 0 then 1 else 0 end as \"hasRecepts\"
			from 
				PersonPrivilege pp 
				left join r101.v_PersonPrivilegeSubCategoryPrivType ppscpt  on ppscpt.PersonPrivilege_id = pp.PersonPrivilege_id
				LEFT JOIN LATERAL (
					select count(*) as EvnRecept_Count
					from v_EvnRecept er 
					where er.PersonPrivilege_id = pp.PersonPrivilege_id
                    limit 1
				) er ON true
			where
				pp.PersonPrivilege_id = COALESCE(CAST(:PersonPrivilege_id as bigint), 0)
			order by 
				pp.PersonPrivilege_id desc
            limit 1
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
				PP.Person_id as \"Person_id\",
				PP.PersonPrivilege_id as \"PersonPrivilege_id\",
				PP.PersonPrivilege_id as \"ExpertHistory_id\",
				to_char(PP.PersonPrivilege_begDate, 'DD.MM.YYYY') as \"PersonPrivilege_begDate\",
				to_char(PP.PersonPrivilege_endDate, 'DD.MM.YYYY') as \"PersonPrivilege_endDate\",
				COALESCE(PT.PrivilegeType_Name, '') as \"PrivilegeType_Name\",
				PP.pmUser_insID as \"pmUser_insID\",
				PP.Lpu_id as \"Lpu_id\",
				'' as \"PersonPrivilege_IsActual\",
				SubCategoryPrivType_Name as \"SubCategoryPrivType_Name\",
				COALESCE(PCT.PrivilegeCloseType_Name, '') as \"PrivilegeCloseType_Name\"
			from v_PersonPrivilege PP 
				inner join PrivilegeType PT  on PT.PrivilegeType_id = PP.PrivilegeType_id
				left join v_PrivilegeCloseType PCT  on PCT.PrivilegeCloseType_id = PP.PrivilegeCloseType_id
				left join r101.v_PersonPrivilegeSubCategoryPrivType PPSCPT  on PPSCPT.PersonPrivilege_id = PP.PersonPrivilege_id
				left join r101.v_SubCategoryPrivType SCPT  on SCPT.SubCategoryPrivType_id = PPSCPT.SubCategoryPrivType_id
			where 
				PP.Person_id = COALESCE(CAST(:Person_id as bigint), 0)
				{$filter}
			order by
				PP.PersonPrivilege_begDate
		", array(
			'Person_id' => $data['Person_id'],
			'Lpu_id' => $data['Lpu_id']
		));
	}
}
