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
class HeadCircumference_model extends swModel
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
		$query =
			"declare
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec p_HeadCircumference_del
				@HeadCircumference_id = :HeadCircumference_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";

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
				PC.Person_id,
				HC.PersonChild_id,
				HC.HeadCircumference_id,
				HC.HeadCircumference_id as Anthropometry_id,
				convert(varchar(10), HC.HeadCircumference_insDT, 104) as HeadCircumference_setDate,
				ISNULL(HMT.HeightMeasureType_Code, '') as HeightMeasureType_Code,
				ISNULL(HMT.HeightMeasureType_Name, '') as HeightMeasureType_Name,
				HC.HeadCircumference_Head,
				ISNULL(HC.pmUser_updID, HC.pmUser_insID) as pmUser_insID,
				ISNULL(PU.pmUser_Name, '') as pmUser_Name
			from
				v_HeadCircumference HC with (nolock)
				inner join PersonChild PC with (nolock) on PC.PersonChild_id = HC.PersonChild_id
				inner join HeightMeasureType HMT with (nolock) on HMT.HeightMeasureType_id = HC.HeightMeasureType_id
				left join v_pmUser PU with (nolock) on PU.pmUser_id = ISNULL(HC.pmUser_updID, HC.pmUser_insID)
			where
				PC.Person_id = :Person_id
			order by
				HeadCircumference_setDate
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
			"select top 1
				PC.Person_id,
				HC.PersonChild_id,
				HC.HeadCircumference_id,
				HC.HeightMeasureType_id,
				HMT.HeightMeasureType_Code,
				convert(varchar(10), HC.HeadCircumference_insDT, 104) as HeadCircumference_setDate,
				ISNULL(HMT.HeightMeasureType_Name, '') as HeightMeasureType_Name,
				HC.HeadCircumference_Head
			from
				v_HeadCircumference HC with (nolock)
				inner join PersonChild PC with (nolock) on PC.PersonChild_id = HC.PersonChild_id
				inner join HeightMeasureType HMT with (nolock) on HMT.HeightMeasureType_id = HC.HeightMeasureType_id
			where
				HC.HeadCircumference_id = :HeadCircumference_id
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
				PC.Person_id,
				HC.PersonChild_id,
				HC.HeadCircumference_id,
				convert(varchar(10), HC.HeadCircumference_insDT, 104) as HeadCircumference_setDate,
				ISNULL(HMT.HeightMeasureType_Name, '') as HeightMeasureType_Name,
				ISNULL(HMT.HeightMeasureType_Code, '') as HeightMeasureType_Code,
				HC.HeadCircumference_Head
			from
				v_HeadCircumference HC (nolock)
				left join v_HeightMeasureType HMT (nolock) on HMT.HeightMeasureType_id = HC.HeightMeasureType_id
				inner join PersonChild PC with (nolock) on PC.PersonChild_id = HC.PersonChild_id
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


		$query =
			"declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @Res = :HeadCircumference_id;

			exec " . $procedure . "
				@HeadCircumference_id = @Res output,
				@PersonChild_id = :PersonChild_id,
				@HeadCircumference_Head = :HeadCircumference_Head,
				@HeightMeasureType_id = :HeightMeasureType_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as HeadCircumference_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";

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
				HC.HeadCircumference_id
			from
				v_HeadCircumference HC with(nolock)
				inner join PersonChild PC with(nolock) on PC.PersonChild_id = HC.PersonChild_id
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
				v_HeadCircumference with(nolock)
			where
				HeightMeasureType_id = 1
				and PersonChild_id = :PersonChild_id
		", $data);
	}
}
