<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
* PersonMedHistory_model - Model to work with PersonMedHistory
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @author       A. Permyakov
* @version      02 2012
*/

class PersonMedHistory_model extends swPgModel {
	var $scheme = 'dbo';
    /**
     * Doc
     */
	function __construct()
	{
		parent::__construct();

	}

	/**
	 * Определение доступа 
	 *
	 * Просмотр - всем, редактирование - только участковому терапевту.
	 * 
	 * @param array $data
	 * @return string
	 */
	private function getAccessType($data)
	{
		if ( isSuperadmin() )
			return 'edit';

		$this->load->helper('MedStaffFactLink');
		$med_personal_list = getMedPersonalListWithLinks();

		//являтся ли данный врач участковым у данного пациента?
		$query = "
			select
				 MSR.MedPersonal_id as \"MedPersonal_id\"
				,PC.Lpu_id as \"Lpu_id\"
			from
				v_PersonCard PC 
				left join v_MedStaffRegion MSR  on MSR.LpuRegion_id = PC.LpuRegion_id
			where
				PC.Person_id = :Person_id
				and PC.LpuAttachType_id = 1
				and PC.PersonCard_endDate is null
				and MSR.MedPersonal_id in (".implode(',',$med_personal_list).")
		";
		$result = $this->db->query($query, array(
			'Person_id'=>$data['Person_id']
			//,'MedPersonal_id'=>$data['session']['medpersonal_id']
		));
		if ( is_object($result) ) {
			$response = $result->result('array');
			if(is_array($response) && count($response) > 0)
			{
				return 'edit';
			}
		}
		return 'view';
	}

	/**
	 * Получение данных для панели просмотра ЭМК   
	 * 
	 * @param array $data
	 * @return array|boolean
	 */
	function getPersonMedHistoryViewData($data)
	{
		$query = "
			SELECT  
				PersonMedHistory_id as \"PersonMedHistory_id\"
				,Person_id as \"Person_id\"
				,to_char(PersonMedHistory_setDT, 'DD.MM.YYYY') as \"PersonMedHistory_setDT\"
				,PersonMedHistory_Descr as \"PersonMedHistory_Descr\"
			FROM
				{$this->scheme}.v_PersonMedHistory
			where
				Person_id = :Person_id
			order by
				PersonMedHistory_setDT desc
			limit 1
		";
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			$response = $result->result('array');
			if(empty($response))
				$response = array(array(
					'PersonMedHistory_id' => 0,
					'Person_id' => $data['Person_id'],
					'PersonMedHistory_setDT' => 'нет данных',
					'PersonMedHistory_Descr' => ''
				));
			if(is_array($response) && count($response) == 1)
			{
				$response[0]['accessType'] = 'edit';//$this->getAccessType($data);  http://redmine.swan.perm.ru/issues/25171
			}
			return $response;
		}
		else {
			return false;
		}
	}

	/**
	 * Загрузка формы редактирования   
	 * 
	 * @param array $data
	 * @return array|boolean
	 */
	function loadPersonMedHistoryEditForm($data)
	{
		$query = "
			SELECT  
				PersonMedHistory_id as \"PersonMedHistory_id\"
				,Person_id as \"Person_id\"
				,to_char(PersonMedHistory_setDT, 'DD.MM.YYYY') as \"PersonMedHistory_setDT\"
				,PersonMedHistory_Descr as \"PersonMedHistory_Descr\"
			FROM
				{$this->scheme}.v_PersonMedHistory
			where
				PersonMedHistory_id = :PersonMedHistory_id
			limit 1
		";
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			$response = $result->result('array');
			if(is_array($response) && count($response) == 1)
			{
				$data['Person_id'] = $response[0]['Person_id'];
				$response[0]['accessType'] = $this->getAccessType($data);
			}
			return $response;
		}
		else {
			return false;
		}
	}


	/**
	 *  Saves some data to database 
	 * 
	 * @param $data
	 * @return array|bool
	 */
	function savePersonMedHistory($data)
	{
		$params = array(
			'PersonMedHistory_id' => array(
				'value' => null,
				'out' => true,
				'type' => 'bigint',
			),
			'Person_id' => $data['Person_id'],
			'PersonMedHistory_setDT' => $data['PersonMedHistory_setDT'],
			'PersonMedHistory_Descr' => $data['PersonMedHistory_Descr'],
			'PersonMedHistory_Text' => strip_tags($data['PersonMedHistory_Descr']),
			'pmUser_id' => $data['pmUser_id'],
		);
		if ( empty($data['PersonMedHistory_id']) ) {
			$proc = 'p_PersonMedHistory_ins';
		} else {
			$proc = 'p_PersonMedHistory_upd';
			$params['PersonMedHistory_id']['value'] = $data['PersonMedHistory_id'];
		}
		return $this->execCommonSP("{$this->scheme}.{$proc}", $params);
	}

	/**
	 *  Удаление
	 */
	function deletePersonMedHistory($data)
	{
		$params = array(
			'PersonMedHistory_id' => $data['PersonMedHistory_id']
		);
		$proc = 'p_PersonMedHistory_del';
		return $this->execCommonSP("{$this->scheme}.{$proc}", $params);
	}

	/**
	 * Получение списка анамнеза жизни пациента для ЭМК
	 */
	function loadPersonMedHistoryPanel($data) {
		return $this->queryResult("
			select
				pmh.PersonMedHistory_id as \"PersonMedHistory_id\",
				to_char(pmh.PersonMedHistory_setDT, 'DD.MM.YYYY') as \"PersonMedHistory_setDate\",
				pmh.PersonMedHistory_Descr as \"PersonMedHistory_Descr\",
				pmh.PersonMedHistory_Text as \"PersonMedHistory_Text\",
				pu.pmUser_Name as \"pmUser_Name\"
			from
				v_PersonMedHistory pmh 
				left join v_pmUser pu  on pu.pmUser_id = pmh.pmUser_updID
			where
				pmh.Person_id = :Person_id
		", array(
			'Person_id' => $data['Person_id']
		));
	}
	
	/**
	 * Метод для API. Получение списка анамнеза жизни пациента для ЭМК 
	 */
	function loadPersonMedHistoryPanelForAPI($data) {

		$filter = " pmh.Person_id = :Person_id "; $select = "";

		// для оффлайн режима
		if (!empty($data['person_in'])) {
			$filter = " pmh.Person_id in ({$data['person_in']}) ";
			$select = " ,pmh.Person_id  as \"Person_id\"";
		}

		return $this->queryResult("
			select
				pmh.PersonMedHistory_id as \"PersonMedHistory_id\",
				to_char(pmh.PersonMedHistory_setDT, 'DD.MM.YYYY') as \"PersonMedHistory_setDate\",
				pmh.PersonMedHistory_Descr as \"PersonMedHistory_Descr\",
				RTRIM(pu.pmUser_Name) as \"pmUser_Name\"
				{$select}
			from
				v_PersonMedHistory pmh 
				left join v_pmUser pu  on pu.pmUser_id = pmh.pmUser_updID
			where {$filter}
		", array(
			'Person_id' => $data['Person_id']
		));
	}
}