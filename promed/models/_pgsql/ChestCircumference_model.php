<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
* ChestCircumference_model - модель для работы с данными об окружности груди
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
class ChestCircumference_model extends swPgModel
{
	/**
	* Конструктор
	*/
    function __construct()
    {
		parent::__construct();
	}

	/**
	* Удаление записи с измерением окружности груди человека
	*/
    function deleteChestCircumference($data)
    {
		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_ChestCircumference_del(
				ChestCircumference_id := :ChestCircumference_id
			)
		";

		$result = $this->db->query($query, [
			'ChestCircumference_id' => $data['ChestCircumference_id'],
			'pmUser_id' => $data['pmUser_id']
		]);

		if (is_object($result))
			return $result->result('array');
		else
			return false;
	}


	/**
	* Получение данных об измерениях окружности груди человека
	*/
	function getChestCircumferenceViewData($data)
	{
		$query = "
			select
				PC.Person_id as \"Person_id\",
				HC.PersonChild_id as \"PersonChild_id\",
				HC.ChestCircumference_id as \"ChestCircumference_id\",
				HC.ChestCircumference_id as \"Anthropometry_id\",
				to_char(HC.ChestCircumference_insDT, 'dd.mm.yyyy') as \"ChestCircumference_setDate\",
				coalesce(HMT.HeightMeasureType_Code, 0) as \"HeightMeasureType_Code\",
				coalesce(HMT.HeightMeasureType_Name, '') as \"HeightMeasureType_Name\",
				HC.ChestCircumference_Chest as \"ChestCircumference_Chest\",
				coalesce(HC.pmUser_updID, HC.pmUser_insID) as \"pmUser_insID\",
				coalesce(PU.pmUser_Name, '') as \"pmUser_Name\"
			from
				v_ChestCircumference HC
				inner join PersonChild PC on PC.PersonChild_id = HC.PersonChild_id
				inner join HeightMeasureType HMT on HMT.HeightMeasureType_id = HC.HeightMeasureType_id
				left join v_pmUser PU on PU.pmUser_id = coalesce(HC.pmUser_updID, HC.pmUser_insID)
			where
				PC.Person_id = :Person_id
			order by
				HC.ChestCircumference_insDT
		";

		$params = ['Person_id' => $data['Person_id']];

		$result = $this->db->query($query, $params);

		if (is_object($result))
			return $result->result('array');
		else
			return false;
	}

	/**
	* Получение данных для просмотра/редактирования данных об измерениях окружности груди человека
	*/
	function loadChestCircumferenceEditForm($data)
	{
		$query = "
			select
				PC.Person_id as \"Person_id\",
				HC.PersonChild_id as \"PersonChild_id\",
				HC.ChestCircumference_id as \"ChestCircumference_id\",
				HC.HeightMeasureType_id as \"HeightMeasureType_id\",
				to_char(HC.ChestCircumference_insDT, 'dd.mm.yyyy') as \"ChestCircumference_setDate\",
				coalesce(HMT.HeightMeasureType_Name, '') as \"HeightMeasureType_Name\",
				HC.ChestCircumference_Chest as \"ChestCircumference_Chest\"
			from
				v_ChestCircumference HC
				inner join PersonChild PC on PC.PersonChild_id = HC.PersonChild_id
				inner join HeightMeasureType HMT on HMT.HeightMeasureType_id = HC.HeightMeasureType_id
			where
				HC.ChestCircumference_id = :ChestCircumference_id
			limit 1
		";

		$params = ['ChestCircumference_id' => $data['ChestCircumference_id']];

		$result = $this->db->query($query, $params);

		if (is_object($result))
			return $result->result('array');
		else
			return false;
	}

	/**
	* Получение списка измерений окружности груди человека для ЭМК
	*/
	function loadChestCircumferencePanel($data)
	{
		if (!empty($data['person_in']))
			$filter = " PC.Person_id in ({$data['person_in']}) "; // для оффлайн режима
		else
			$filter = " PC.Person_id = :Person_id ";

		$query = "
			select
				PC.Person_id as \"Person_id\",
				CC.PersonChild_id as \"PersonChild_id\",
				CC.ChestCircumference_id as \"ChestCircumference_id\",
				to_char(CC.ChestCircumference_insDT, 'dd.mm.yyyy') as \"ChestCircumference_setDate\",
				coalesce(HMT.HeightMeasureType_Name, '') as \"HeightMeasureType_Name\",
				coalesce(HMT.HeightMeasureType_Code, 0) as \"HeightMeasureType_Code\",
				CC.ChestCircumference_Chest as \"ChestCircumference_Chest\"
			from
				v_ChestCircumference CC
				left join v_HeightMeasureType HMT on HMT.HeightMeasureType_id = CC.HeightMeasureType_id
				inner join PersonChild PC on PC.PersonChild_id = CC.PersonChild_id
			where " . $filter;

		$params = ['Person_id' => $data['Person_id']];

		$result = $this->db->query($query, $params);

		if (is_object($result))
			return $result->result('array');
		else
			return false;
	}

	/**
	* Сохранение измерения окружности головы человека
	*/
	function saveChestCircumference($data)
	{
		if (isset($data['ChestCircumference_id']))
			$procedure = "p_ChestCircumference_upd";
		else
			$procedure = "p_ChestCircumference_ins";


		$query = "
			select
				ChestCircumference_id as \"ChestCircumference_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from " . $procedure . "(
				ChestCircumference_id := :ChestCircumference_id,
				PersonChild_id := :PersonChild_id,
				ChestCircumference_Chest := :ChestCircumference_Chest,
				HeightMeasureType_id := :HeightMeasureType_id,
				pmUser_id := :pmUser_id
			)";

		$params =
			[
				'ChestCircumference_id' => (isset($data['ChestCircumference_id']) ? $data['ChestCircumference_id'] : NULL),
				'PersonChild_id' => $data['PersonChild_id'],
				'ChestCircumference_Chest' => $data['ChestCircumference_Chest'],
				'HeightMeasureType_id' => $data['HeightMeasureType_id'],
				'pmUser_id' => $data['pmUser_id']
			];

		$result = $this->db->query($query, $params);

		if (is_object($result))
			return $result->result('array');
		else
			return [
				[
					'Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение результатов измерения окружности головы)'
				]
			];

	}

	function getIdByPersonChild($data)
	{
		return $this->db->query("
			select
				ChestCircumference_id as \"ChestCircumference_id\"
			from
				v_ChestCircumference
			where
				HeightMeasureType_id = 1
				and PersonChild_id = :PersonChild_id
		", $data);
	}
}
