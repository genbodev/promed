<?php
/**
* Astra_Polka_PersonDisp_model - модель для работы с картами дисп. учета (Астрахань)
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stanislav Bykov (savage@swan.perm.ru)
* @version      25.08.2018
*/

require_once(APPPATH.'models/_pgsql/Polka_PersonDisp_model.php');

class Astra_Polka_PersonDisp_model extends Polka_PersonDisp_model {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Выгрузка списка карт диспансерного наблюдения за период
	 */
	public function exportPersonDispForPeriod($data) {
		$filterList = array(
			'pd.PersonDisp_begDate <= cast(:ExportDateRange_1 as timestamp)',
			'(pd.PersonDisp_endDate is null or cast(:ExportDateRange_0 as timestamp) <= pd.PersonDisp_endDate)',
			'pd.Lpu_id = :Lpu_id',
		);
		$queryParams = array(
			'ExportDateRange_0' => $data['ExportDateRange'][0],
			'ExportDateRange_1' => $data['ExportDateRange'][1],
			'Lpu_id' => $data['Lpu_id'],
		);

		if ( !empty($data['OrgSMO_id']) ) {
			$filterList[] = "pls.OrgSMO_id = :OrgSMO_id";
			$queryParams['OrgSMO_id'] = $data['OrgSMO_id'];
		}

		$query = "
			select
				ps.Person_id as \"Person_id\",
				ps.Person_Surname as \"FAM\",
				ps.Person_Firname as \"IM\",
				ps.Person_Secname as \"OT\",
				ps.Person_Birthday as \"DR\",
				case
					when NULLIF(ps.Person_Phone, '') is not null
						then ps.Person_Phone
						else ps.PersonPhone_Comment
				end as \"TEL\",
				dg.Diag_Code as \"DS\",
				ls.LpuSectionProfile_Code as \"PROFIL\",
				pd.PersonDisp_begDate as \"D_BEG\",
				pd.PersonDisp_endDate as \"D_END\",
				case
					when dot.DispOutType_Code = 1 then 1
					when dot.DispOutType_Code = 2 then 5
					when dot.DispOutType_Code = 3 then 4
					when dot.DispOutType_Code = 4 then 3
					when dot.DispOutType_Code = 5 then 6
					when dot.DispOutType_Code = 6 then 5
					when dot.DispOutType_Code = 7 then 5
				end as \"END_RES\"
			from dbo.v_PersonDisp pd
				inner join dbo.v_PersonState ps on ps.Person_id = pd.Person_id
				inner join dbo.v_Sex sx on sx.Sex_id = ps.Sex_id
				inner join dbo.v_Diag dg on dg.Diag_id = pd.Diag_id
				inner join dbo.v_LpuSection ls on ls.LpuSection_id = pd.LpuSection_id
				inner join dbo.v_Polis pls on pls.Polis_id = ps.Polis_id
				left join dbo.v_DispOutType dot on dot.DispOutType_id = pd.DispOutType_id
			where " . implode(' and ', $filterList) . "
			order by ps.Person_id
		";

		return $this->db->query($query, $queryParams);
	}
}