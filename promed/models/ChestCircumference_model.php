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
class ChestCircumference_model extends swModel
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
		$query =
			"declare
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec p_ChestCircumference_del
				@ChestCircumference_id = :ChestCircumference_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";

		$result =
			$this->db->query($query, array('ChestCircumference_id' => $data['ChestCircumference_id'],
											'pmUser_id' => $data['pmUser_id']));

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
		$query =
			"select
				PC.Person_id,
				HC.PersonChild_id,
				HC.ChestCircumference_id,
				HC.ChestCircumference_id as Anthropometry_id,
				convert(varchar(10), HC.ChestCircumference_insDT, 104) as ChestCircumference_setDate,
				ISNULL(HMT.HeightMeasureType_Code, '') as HeightMeasureType_Code,
				ISNULL(HMT.HeightMeasureType_Name, '') as HeightMeasureType_Name,
				HC.ChestCircumference_Chest,
				ISNULL(HC.pmUser_updID, HC.pmUser_insID) as pmUser_insID,
				ISNULL(PU.pmUser_Name, '') as pmUser_Name
			from
				v_ChestCircumference HC with (nolock)
				inner join PersonChild PC with (nolock) on PC.PersonChild_id = HC.PersonChild_id
				inner join HeightMeasureType HMT with (nolock) on HMT.HeightMeasureType_id = HC.HeightMeasureType_id
				left join v_pmUser PU with (nolock) on PU.pmUser_id = ISNULL(HC.pmUser_updID, HC.pmUser_insID)
			where
				PC.Person_id = :Person_id
			order by
				HC.ChestCircumference_insDT
			";

		$params = array('Person_id' => $data['Person_id']);

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
		$query =
			"select top 1
				PC.Person_id,
				HC.PersonChild_id,
				HC.ChestCircumference_id,
				HC.HeightMeasureType_id,
				convert(varchar(10), HC.ChestCircumference_insDT, 104) as ChestCircumference_setDate,
				ISNULL(HMT.HeightMeasureType_Name, '') as HeightMeasureType_Name,
				HC.ChestCircumference_Chest
			from
				v_ChestCircumference HC with (nolock)
				inner join PersonChild PC with (nolock) on PC.PersonChild_id = HC.PersonChild_id
				inner join HeightMeasureType HMT with (nolock) on HMT.HeightMeasureType_id = HC.HeightMeasureType_id
			where
				HC.ChestCircumference_id = :ChestCircumference_id
			";

		$params = array('ChestCircumference_id' => $data['ChestCircumference_id']);

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

		$query =
			"select
				PC.Person_id,
				CC.PersonChild_id,
				CC.ChestCircumference_id,
				convert(varchar(10), CC.ChestCircumference_insDT, 104) as ChestCircumference_setDate,
				ISNULL(HMT.HeightMeasureType_Name, '') as HeightMeasureType_Name,
				ISNULL(HMT.HeightMeasureType_Code, '') as HeightMeasureType_Code,
				CC.ChestCircumference_Chest
			from
				v_ChestCircumference CC (nolock)
				left join v_HeightMeasureType HMT (nolock) on HMT.HeightMeasureType_id = CC.HeightMeasureType_id
				inner join PersonChild PC with (nolock) on PC.PersonChild_id = CC.PersonChild_id
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
	function saveChestCircumference($data)
	{
		if (isset($data['ChestCircumference_id']))
			$procedure = "p_ChestCircumference_upd";
		else
			$procedure = "p_ChestCircumference_ins";


		$query =
			"declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @Res = :ChestCircumference_id;

			exec " . $procedure . "
				@ChestCircumference_id = @Res output,
				@PersonChild_id = :PersonChild_id,
				@ChestCircumference_Chest = :ChestCircumference_Chest,
				@HeightMeasureType_id = :HeightMeasureType_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as ChestCircumference_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";

		$params =
			array(
				'ChestCircumference_id' => (isset($data['ChestCircumference_id']) ? $data['ChestCircumference_id'] : NULL),
				'PersonChild_id' => $data['PersonChild_id'],
				'ChestCircumference_Chest' => $data['ChestCircumference_Chest'],
				'HeightMeasureType_id' => $data['HeightMeasureType_id'],
				'pmUser_id' => $data['pmUser_id']
			);

		$result = $this->db->query($query, $params);

		if (is_object($result))
			return $result->result('array');
		else
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение результатов измерения окружности головы)'));

	}

	function getIdByPersonChild($data)
	{
		return $this->db->query("
			select
				ChestCircumference_id as \"ChestCircumference_id\"
			from
				v_ChestCircumference with(nolock)
			where
				HeightMeasureType_id = 1
				and PersonChild_id = :PersonChild_id
		", $data);
	}
}
