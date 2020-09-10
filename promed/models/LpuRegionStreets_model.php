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
*/

class LpuRegionStreets_model extends CI_Model {
	/**
	 * LpuRegionStreets_model constructor.
	 */
    function __construct()
    {
        parent::__construct();
    }

	/**
	 * @param $data
	 * @return bool|mixed
	 */
	function getLpuRegionMedPersonal($data)
	{
		$sql = "
			SELECT
				DISTINCT LpuRegion_id, Person_fio
			FROM
				MedStaffRegion msr with(nolock)
				inner join v_MedPersonal mp with(nolock) on msr.Lpu_id = ? and mp.MedPersonal_id = msr.MedPersonal_id
			ORDER BY
				LpuRegion_id
		";
		$res = $this->db->query($sql, array($data['Lpu_id']));
		if ( is_object($res) )
		{
			return $res->result('array');
		}
		else
			return false;
	}

	/**
	 * @param $data
	 * @return bool|mixed
	 */
	function getLpuRegionStreetsReport($data)
	{
		$sql = "
		WITH AttachedRegionsCounts AS (
			SELECT
				LpuRegion_id,
				count(distinct Person_id) as attached_count
			FROM
				PersonCardState with(nolock)
			WHERE
				Lpu_id = ?
				and LpuRegion_id is not null
			GROUP BY
				LpuRegion_id
		)
		SELECT
			lr.LpuRegion_Name,
			lr.LpuRegion_id,
			lr.LpuRegionType_Name,
			arc.attached_count,
			CASE WHEN 
				klstr.KLStreet_Name is null
			THEN
				CASE WHEN
					kltown.KLArea_Name is null
				THEN
					klcity.KLArea_Name
				ELSE
					kltown.KLArea_Name
				END
			ELSE
				klstr.KLStreet_Name
			END as KLArea_Name,
			lrs.LpuRegionStreet_HouseSet,
			lp.Lpu_Nick,
			klstr.KLStreet_id,
			kltown.KLArea_id as KLTown_id,
			klcity.KLArea_id as KLCity_id
		FROM
			LpuRegionStreet lrs with(nolock)
			inner join v_LpuRegion lr with(nolock) on lrs.LpuRegion_id = lr.LpuRegion_id and lr.Lpu_id = ?
			inner join v_Lpu lp with(nolock) on lp.Lpu_id = lr.Lpu_id
			inner join AttachedRegionsCounts arc with(nolock) on arc.LpuRegion_id = lrs.LpuRegion_id
			left join KLStreet klstr with(nolock) on klstr.KLStreet_id = lrs.KLStreet_id
			left join KLArea kltown with(nolock) on kltown.KLArea_id = lrs.KLTown_id
			left join KLArea klcity with(nolock) on klcity.KLArea_id = lrs.KLCity_id
		ORDER BY 
			lr.LpuRegionType_id,
			lr.LpuRegion_Name, 
			isnull(klcity.KLArea_Name, ''), 
			isnull(kltown.KLArea_Name, ''), 
			isnull(klstr.KLStreet_Name, ''), 
			lrs.LpuRegionStreet_HouseSet
		";
		$res = $this->db->query($sql, array($data['Lpu_id'], $data['Lpu_id']));
		if ( is_object($res) )
		{
			return $res->result('array');
		}
		else
			return false;
	}

	/**
	 * Получить список врачей, работающих на участке
	 *
	 * @param int $LpuRegion_id Идентификатор участка
	 */
	public function getLpuRegionMedStaffFactList($LpuRegion_id) {
		$result = $this->db->query("
		select
            msf.MedStaffFact_id,
            (RTrim(msf.Person_surname)+' '+RTrim(msf.Person_firname)+' '+isnull(RTrim(msf.Person_secname),'')) as FullName,
            lsp.LpuSectionProfile_id,
            msf.Person_surname as SurName,
            RTrim(msf.Person_firname) as FirName,
            isnull(RTrim(msf.Person_secname),'') as SecName,
            lb.LpuBuilding_id,
			lb.LpuBuilding_Name,
			isnull(lbs.KLStreet_Name + ', ', '') + isnull(lba.Address_House, '') + isnull(', корп. ' + lba.Address_Corpus, '') as LpuBuilding_Address,
            ProfileSpec_Name,
            LpuSection_Name,
            msf.Lpu_id,
            msf.LpuUnit_id,
            msf.RecType_id,
            lu.LpuUnit_Name,
            lu.Address_id,
            lr.LpuRegion_Name,
            lr.LpuRegion_id,
            a.Address_Nick as LpuUnit_Address,
            rtrim(msf.Person_FIO) as Person_FIO
        from v_LpuRegion lr with (nolock)
        inner join v_MedStaffRegion msr with (nolock) on lr.LpuRegion_id = msr.LpuRegion_id
        inner join v_MedStaffFact msf (nolock) on msf.MedStaffFact_id = msr.MedStaffFact_id and isnull(msr.MedStaffRegion_endDate, '2030-01-01') > getdate()
        inner join v_LpuSection ls (nolock) on ls.LpuSection_id = msf.LpuSection_id
        left join v_LpuBuilding lb with (nolock) on lb.LpuBuilding_id = ls.LpuBuilding_id
        left join v_Address lba with (nolock) on lba.Address_id = lb.Address_id
        left join v_KLStreet lbs with (nolock) on lbs.KLStreet_id = lba.KLStreet_id
        inner join v_LpuUnit lu (nolock) on ls.LpuUnit_id = lu.LpuUnit_id
        inner join v_Address a (nolock) on lu.Address_id = a.Address_id
        inner join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id and LpuSectionProfile_IsArea = 2
        where
            isnull(msf.RecType_id, 6) not in (2,5,6,8)
            and isnull(msf.WorkData_endDate, '2030-01-01') > dbo.tzGetDate()
            and lr.LpuRegion_id = :LpuRegion_id
        ",array('LpuRegion_id' => $LpuRegion_id))->result('array');

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