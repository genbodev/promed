<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
* HeadCircumference_model - модель для работы с данными об окружности головы
* человека
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2019 Swan Ltd.
* @author
* @version      12.2019
*/
class HeadCircumference_model extends SwPgModel
{
	/**
	* Конструктор
	*/
    function __construct()
    {
		parent::__construct();
	}

	/**
	 * Удаление записи с измерением окружности головы человека
	 */
   function deleteHeadCircumference($data)
	{

		$query = "
		select
			Error_Code as \"Error_Code\",
			Error_Message as \"Error_Msg\"
		 from p_HeadCircumference_del
			(
				HeadCircumference_id := :HeadCircumference_id
			)";


		$result =
			$this->db->query($query, array('HeadCircumference_id' => $data['HeadCircumference_id'],
											'pmUser_id' => $data['pmUser_id']));

		if (is_object($result))
			return $result->result('array');
		else
			return false;
	}


	/**
     * Получение данных об измерениях окружности головы человека
     */
	function getHeadCircumferenceViewData($data)
	{
		$query =
			"select
				PC.Person_id as \"Person_id\",
				HC.PersonChild_id as \"PersonChild_id\",
				HC.HeadCircumference_id as \"HeadCircumference_id\",
				HC.HeadCircumference_id as \"Anthropometry_id\",
				to_char(HC.HeadCircumference_insDT, 'dd.mm.yyyy') as \"HeadCircumference_setDate\",
				coalesce(HMT.HeightMeasureType_Code, 0) as \"HeightMeasureType_Code\",
				coalesce(HMT.HeightMeasureType_Name, '') as \"HeightMeasureType_Name\",
				HC.HeadCircumference_Head as \"HeadCircumference_Head\",
				coalesce(HC.pmUser_updID, HC.pmUser_insID) as \"pmUser_insID\",
				coalesce(PU.pmUser_Name, '') as \"pmUser_Name\"
			from
				v_HeadCircumference HC
				inner join PersonChild PC  on PC.PersonChild_id = HC.PersonChild_id
				inner join HeightMeasureType HMT  on HMT.HeightMeasureType_id = HC.HeightMeasureType_id
				left join v_pmUser PU  on PU.pmUser_id = coalesce(HC.pmUser_updID, HC.pmUser_insID)
			where
				PC.Person_id = :Person_id
			order by
				\"HeadCircumference_setDate\"
			";

		$params = array('Person_id' => $data['Person_id']);

		$result = $this->db->query($query, $params);

		if (is_object($result))
			return $result->result('array');
		else
			return false;
	}

	/**
     * Получение данных для просмотра/редактирования данных об измерениях окружности головы человека
     */
	function loadHeadCircumferenceEditForm($data)
	{
		$query =
			"select
				PC.Person_id as \"Person_id\",
				HC.PersonChild_id as \"PersonChild_id\",
				HC.HeadCircumference_id as \"HeadCircumference_id\",
				HC.HeightMeasureType_id as \"HeightMeasureType_id\",
				HMT.HeightMeasureType_Code as \"HeightMeasureType_Code\",
				to_char(HC.HeadCircumference_insDT, 'dd.mm.yyyy') as \"HeadCircumference_setDate\",
				coalesce(HMT.HeightMeasureType_Name, '') as \"HeightMeasureType_Name\",
				HC.HeadCircumference_Head as \"HeadCircumference_Head\"
			from
				v_HeadCircumference HC
				inner join PersonChild PC  on PC.PersonChild_id = HC.PersonChild_id
				inner join HeightMeasureType HMT  on HMT.HeightMeasureType_id = HC.HeightMeasureType_id
			where
				HC.HeadCircumference_id = :HeadCircumference_id
			limit 1
			";

		$params = array('HeadCircumference_id' => $data['HeadCircumference_id']);

		$result = $this->db->query($query, $params);

		if (is_object($result))
			return $result->result('array');
		else
			return false;
	}

	/**
     * Получение списка измерений окружности головы человека для ЭМК
     */
	function loadHeadCircumferencePanel($data)
	{
		if (!empty($data['person_in']))
			$filter = " PC.Person_id in ({$data['person_in']}) "; // для оффлайн режима
		else
			$filter = " PC.Person_id = :Person_id ";

		$query =
			"select
				PC.Person_id as \"Person_id\",
				HC.PersonChild_id as \"PersonChild_id\",
				HC.HeadCircumference_id as \"HeadCircumference_id\",
				to_char(HC.HeadCircumference_insDT, 'dd.mm.yyyy') as \"HeadCircumference_setDate\",
				coalesce(HMT.HeightMeasureType_Name, '') as \"HeightMeasureType_Name\",
				coalesce(HMT.HeightMeasureType_Code, 0) as \"HeightMeasureType_Code\",
				HC.HeadCircumference_Head as \"HeadCircumference_Head\"
			from
				v_HeadCircumference HC
				left join v_HeightMeasureType HMT  on HMT.HeightMeasureType_id = HC.HeightMeasureType_id
				inner join PersonChild PC  on PC.PersonChild_id = HC.PersonChild_id
			where " . $filter;

		$params = array('Person_id' => $data['Person_id']);

		$result = $this->db->query($query, $params);

		if (is_object($result))
			return $result->result('array');
		else
			return false;
	}

	/**
     * Сохранение измерения окружности головы человека
     */
	function saveHeadCircumference($data)
	{
		if (isset($data['HeadCircumference_id']))
			$procedure = "p_HeadCircumference_upd";
		else
			$procedure = "p_HeadCircumference_ins";

		$query = "
		select
			Error_Code as \"Error_Code\",
			Error_Message as \"Error_Msg\",
			HeadCircumference_id as \"HeadCircumference_id\"
		from {$procedure}
			(
				HeadCircumference_id := :HeadCircumference_id,
				PersonChild_id := :PersonChild_id,
				HeadCircumference_Head := :HeadCircumference_Head,
				HeightMeasureType_id := :HeightMeasureType_id,
				pmUser_id := :pmUser_id
			)";


		$params =
			array(
				'HeadCircumference_id' => (isset($data['HeadCircumference_id']) ? $data['HeadCircumference_id'] : NULL),
				'PersonChild_id' => $data['PersonChild_id'],
				'HeadCircumference_Head' => $data['HeadCircumference_Head'],
				'HeightMeasureType_id' => $data['HeightMeasureType_id'],
				'pmUser_id' => $data['pmUser_id']
			);

		$result = $this->db->query($query, $params);

		if (is_object($result))
			return $result->result('array');
		else
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение результатов измерения окружности головы)'));

	}

	function getHeadCircumferenceId($data)
	{
		return $this->db->query("
			select
				HC.HeadCircumference_id as \"HeadCircumference_id\"
			from
				v_HeadCircumference HC
				inner join PersonChild PC on PC.PersonChild_id = HC.PersonChild_id
			where
				HC.HeightMeasureType_id = 1
				and PC.Person_id = :Person_id
		", $data);
	}

	function getIdByPersonChild($data)
	{
		return $this->db->query("
			select
				HeadCircumference_id as \"HeadCircumference_id\"
			from
				v_HeadCircumference
			where
				HeightMeasureType_id = 1
				and PersonChild_id = :PersonChild_id
		", $data);
	}
}
