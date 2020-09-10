<?php
/**
 * LpuRegionStreets_model - модель, для работы с отчетами по зонам обслуживания
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Pshenitcyn Ivan aka IvP (ipshon@rambler.ru)
 * @version      11.12.2009
 *
 * @property CI_DB_driver $db
 */
class LpuRegionStreets_model extends SwPgModel
{
	private $dateTimeForm104 = "DD.MM.YYYY";
	
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function getLpuRegionMedPersonal($data)
	{
		$query = "
			select distinct
				LpuRegion_id as \"LpuRegion_id\",
			    Person_fio as \"Person_fio\"
			from
				MedStaffRegion msr
				inner join v_MedPersonal mp on msr.Lpu_id = :Lpu_id and mp.MedPersonal_id = msr.MedPersonal_id
			order by LpuRegion_id
		";
		$queryParams = ["Lpu_id" => $data["Lpu_id"]];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function getLpuRegionStreetsReport($data)
	{
		$query = "
		with AttachedRegionsCounts as (
			select
				LpuRegion_id,
				count(distinct Person_id) as attached_count
			from PersonCardState
			where Lpu_id = :Lpu_id
			  and LpuRegion_id is not null
			group by LpuRegion_id
		)
		select
			lr.LpuRegion_Name as \"LpuRegion_Name\",
			lr.LpuRegion_id as \"LpuRegion_id\",
			lr.LpuRegionType_Name as \"LpuRegionType_Name\",
			arc.attached_count as \"attached_count\",
			case when klstr.KLStreet_Name is null
				then
					case when kltown.KLArea_Name is null
						then klcity.KLArea_Name
						else kltown.KLArea_Name
					end
				else klstr.KLStreet_Name
			end as \"KLArea_Name\",
			lrs.LpuRegionStreet_HouseSet as \"LpuRegionStreet_HouseSet\",
			lp.Lpu_Nick as \"Lpu_Nick\",
			klstr.KLStreet_id as \"KLStreet_id\",
			kltown.KLArea_id as \"KLTown_id\",
			klcity.KLArea_id as \"KLCity_id\"
		from
			LpuRegionStreet lrs
			inner join v_LpuRegion lr on lrs.LpuRegion_id = lr.LpuRegion_id and lr.Lpu_id = :Lpu_id
			inner join v_Lpu lp on lp.Lpu_id = lr.Lpu_id
			inner join AttachedRegionsCounts arc on arc.LpuRegion_id = lrs.LpuRegion_id
			left join KLStreet klstr on klstr.KLStreet_id = lrs.KLStreet_id
			left join KLArea kltown on kltown.KLArea_id = lrs.KLTown_id
			left join KLArea klcity on klcity.KLArea_id = lrs.KLCity_id
		order by 
			lr.LpuRegionType_id,
			lr.LpuRegion_Name, 
			coalesce(klcity.KLArea_Name, ''), 
			coalesce(kltown.KLArea_Name, ''), 
			coalesce(klstr.KLStreet_Name, ''), 
			lrs.LpuRegionStreet_HouseSet
		";
		$queryParams = ["Lpu_id" => $data["Lpu_id"]];
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * Получить список врачей, работающих на участке
	 *
	 * @param int $LpuRegion_id Идентификатор участка
	 */
	public function getLpuRegionMedStaffFactList($LpuRegion_id) {
		$result = $this->queryResult("
		select
            msf.MedStaffFact_id as \"MedStaffFact_id\",
            (RTrim(msf.Person_surname)||' '||RTrim(msf.Person_firname)||' '||coalesce(RTrim(msf.Person_secname),'')) as \"FullName\",
            lsp.LpuSectionProfile_id as \"LpuSectionProfile_id\",
            msf.Person_surname as \"SurName\",
            RTrim(msf.Person_firname) as \"FirName\",
            coalesce(RTrim(msf.Person_secname),'') as \"SecName\",
            lb.LpuBuilding_id as \"LpuBuilding_id\",
			lb.LpuBuilding_Name as \"LpuBuilding_Name\",
			coalesce(lbs.KLStreet_Name || ', ', '') || coalesce(lba.Address_House, '') || coalesce(', корп. ' || lba.Address_Corpus, '') as \"LpuBuilding_Address\",
            ProfileSpec_Name as \"ProfileSpec_Name\",
            LpuSection_Name as \"LpuSection_Name\",
            msf.Lpu_id as \"Lpu_id\",
            msf.LpuUnit_id as \"LpuUnit_id\",
            msf.RecType_id as \"RecType_id\",
            lu.LpuUnit_Name as \"LpuUnit_Name\",
            lu.Address_id as \"Address_id\",
            lr.LpuRegion_Name as \"LpuRegion_Name\",
            lr.LpuRegion_id as \"LpuRegion_id\",
            a.Address_Nick as \"LpuUnit_Address\",
            rtrim(msf.Person_FIO) as \"Person_FIO\"
        from v_LpuRegion lr
        inner join v_MedStaffRegion msr on lr.LpuRegion_id = msr.LpuRegion_id
        inner join v_MedStaffFact msf  on msf.MedStaffFact_id = msr.MedStaffFact_id and cast(coalesce(msr.MedStaffRegion_endDate, '2030-01-01')  as date) > cast(getdate() as date)
        inner join v_LpuSection ls  on ls.LpuSection_id = msf.LpuSection_id
        left join v_LpuBuilding lb  on lb.LpuBuilding_id = ls.LpuBuilding_id
        left join v_Address lba  on lba.Address_id = lb.Address_id
        left join v_KLStreet lbs  on lbs.KLStreet_id = lba.KLStreet_id
        inner join v_LpuUnit lu  on ls.LpuUnit_id = lu.LpuUnit_id
        inner join v_Address a  on lu.Address_id = a.Address_id
        inner join v_LpuSectionProfile lsp  on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id and LpuSectionProfile_IsArea = 2
        where
            coalesce(msf.RecType_id, 6) not in (2,5,6,8)
            and cast(coalesce(msf.WorkData_endDate, '2030-01-01') as date) > cast(dbo.tzGetDate() as date)
            and lr.LpuRegion_id = :LpuRegion_id
        ", array('LpuRegion_id' => $LpuRegion_id));

		foreach($result as $doctor) {

			// Конвертируем первую букву в заглавную, остальные в строчные
			$doctor['FullName'] = ucwords($doctor['FullName']);
			$doctor['SurName'] = ucwords($doctor['SurName']);
			$doctor['FirName'] = ucwords($doctor['FirName']);
			$doctor['SecName'] = ucwords($doctor['SecName']);

			if ( empty($doctor['LpuUnit_Address']) && isset($doctor['Address_id']) ) {
				$this->load->model( 'Address_model');
				$doctor['LpuUnit_Address'] = $this->Address_model->getAddressTextBrief($doctor['Address_id']);

			}
		}

		return $result;
	}
}