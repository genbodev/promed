<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * DrugDocument_model - модель для работы со справочниками для документов по медикаментам
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Farmacy
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			26.12.2013
 *
 */

class DrugDocument_model extends swModel {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Получение списка видов заявки на медикаменты
	 */
	function loadDrugDocumentClassList($data)
	{
		$params = array();

		$query = "
			select
				DDC.DrugDocumentClass_id,
				DDC.DrugDocumentClass_Code,
				DDC.DrugDocumentClass_Name,
				DDC.DrugDocumentClass_Nick
			from
				v_DrugDocumentClass DDC with(nolock)
			order by
				DDC.DrugDocumentClass_Code
		";

		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $response = $result->result('array');;
		}
		return false;
	}

	/**
	 * Получение списка статусов заявки на медикаменты
	 */
	function loadDrugDocumentStatusList($data)
	{
		$params = array();
		$filter = '';

		if (!empty($data['DrugDocumentType_id'])) {
			$params['DrugDocumentType_id'] = $data['DrugDocumentType_id'];
			$filter .= ' and DDS.DrugDocumentType_id = :DrugDocumentType_id';
		}

		$query = "
			select
				DDS.DrugDocumentStatus_id,
				DDS.DrugDocumentStatus_Code,
				DDS.DrugDocumentStatus_Name,
				DDS.DrugDocumentType_id
			from
				v_DrugDocumentStatus DDS with(nolock)
			where
				(1=1)
				{$filter}
			order by
				DDS.DrugDocumentStatus_Code
		";

		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $response = $result->result('array');;
		}
		return false;
	}

	/**
	 * Получение списка видов заявки на медикаменты
	 */
	function loadDrugDocumentClassGrid($data)
	{
		$params = array();

		$query = "
			select
				DDC.DrugDocumentClass_id,
				DDC.DrugDocumentClass_Code,
				DDC.DrugDocumentClass_Name,
				DDC.DrugDocumentClass_Nick
			from
				v_DrugDocumentClass DDC with(nolock)
			order by
				DDC.DrugDocumentClass_Code
		";

		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			$response = $result->result('array');
			return array('data' => $response);
		}
		return false;
	}

	/**
	 * Получение списка статусов заявки на медикаменты
	 */
	function loadDrugDocumentStatusGrid($data)
	{
		$params = array();
		$filter = '';

		if (!empty($data['DrugDocumentType_id'])) {
			$filter = ' and DDS.DrugDocumentType_id = :DrugDocumentType_id';
			$params['DrugDocumentType_id'] = $data['DrugDocumentType_id'];
		}

		$query = "
			select
				DDS.DrugDocumentStatus_id,
				DDS.DrugDocumentStatus_Code,
				DDS.DrugDocumentStatus_Name,
				DDS.DrugDocumentType_id
			from
				v_DrugDocumentStatus DDS with(nolock)
			where
				(1 = 1)
				{$filter}
			order by
				DDS.DrugDocumentStatus_Code
		";

		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			$response = $result->result('array');
			return array('data' => $response);
		}
		return false;
	}

	/**
	 * Возвращает данные для редактирования вида заявки на медикаменты
	 */
	function loadDrugDocumentClassForm($data)
	{
		$params = array('DrugDocumentClass_id' => $data['DrugDocumentClass_id']);

		$query = "
			select top 1
				DDC.DrugDocumentClass_id,
				DDC.DrugDocumentClass_Code,
				DDC.DrugDocumentClass_Name,
				DDC.DrugDocumentClass_Nick
			from
				v_DrugDocumentClass DDC with(nolock)
			where
				DDC.DrugDocumentClass_id = :DrugDocumentClass_id
		";

		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		}
		return false;
	}

	/**
	 * Возвращает данные для редактирования статуса заявки на медикаменты
	 */
	function loadDrugDocumentStatusForm($data)
	{
		$params = array('DrugDocumentStatus_id' => $data['DrugDocumentStatus_id']);

		$query = "
			select top 1
				DDS.DrugDocumentStatus_id,
				DDS.DrugDocumentStatus_Code,
				DDS.DrugDocumentStatus_Name
			from
				v_DrugDocumentStatus DDS with(nolock)
			where
				DDS.DrugDocumentStatus_id = :DrugDocumentStatus_id
		";

		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		}
		return false;
	}

	/**
	 * Сохранение вида заявки на медикаменты
	 */
	function saveDrugDocumentClass($data)
	{
		$procedure = 'p_DrugDocumentClass_ins';
		if (!empty($data['DrugDocumentClass_id'])) {
			$procedure = 'p_DrugDocumentClass_upd';
		}

		$params = array(
			'DrugDocumentClass_id' => $data['DrugDocumentClass_id'],
			'DrugDocumentClass_Code' => $data['DrugDocumentClass_Code'],
			'DrugDocumentClass_Name' => $data['DrugDocumentClass_Name'],
			'DrugDocumentClass_Nick' => $data['DrugDocumentClass_Nick'],
			'pmUser_id' => $data['pmUser_id']
		);

		$query = "
			declare
				@DrugDocumentClass_id bigint,
				@Error_Code int,
				@Error_Message varchar(4000);
			set @DrugDocumentClass_id = :DrugDocumentClass_id;
			exec ".$procedure."
				@DrugDocumentClass_id = @DrugDocumentClass_id output,
				@DrugDocumentClass_Code = :DrugDocumentClass_Code,
				@DrugDocumentClass_Name = :DrugDocumentClass_Name,
				@DrugDocumentClass_Nick = :DrugDocumentClass_Nick,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @DrugDocumentClass_id as DrugDocumentClass_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		}
		return false;
	}

	/**
	 * Сохранение статуса заявки на медикаменты
	 */
	function saveDrugDocumentStatus($data)
	{
		$procedure = 'p_DrugDocumentStatus_ins';
		if (!empty($data['DrugDocumentStatus_id'])) {
			$procedure = 'p_DrugDocumentStatus_upd';
		}

		$params = array(
			'DrugDocumentStatus_id' => $data['DrugDocumentStatus_id'],
			'DrugDocumentStatus_Code' => $data['DrugDocumentStatus_Code'],
			'DrugDocumentStatus_Name' => $data['DrugDocumentStatus_Name'],
			'DrugDocumentType_id' => $data['DrugDocumentType_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		$query = "
			declare
				@DrugDocumentStatus_id bigint,
				@Error_Code int,
				@Error_Message varchar(4000);
			set @DrugDocumentStatus_id = :DrugDocumentStatus_id;
			exec ".$procedure."
				@DrugDocumentStatus_id = @DrugDocumentStatus_id output,
				@DrugDocumentStatus_Code = :DrugDocumentStatus_Code,
				@DrugDocumentStatus_Name = :DrugDocumentStatus_Name,
				@DrugDocumentType_id = :DrugDocumentType_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @DrugDocumentStatus_id as DrugDocumentStatus_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		}
		return false;
	}

	/**
	 * Удаление вида заявки на медикаменты
	 */
	function deleteDrugDocumentClass($data)
	{
		$params = array('DrugDocumentClass_id' => $data['DrugDocumentClass_id']);

		$query = "
			select top 1
				count(DocumentUc_id) as Count
			from
				v_DocumentUc DU with(nolock)
			where
				DU.DrugDocumentClass_id = :DrugDocumentClass_id
		";

		$result = $this->db->query($query, $params);

		if (!is_object($result)) {
			return array('Error_Msg' => 'Ошибка при проверке вида заявок на медикаменты', 'success' => false);
		}
		$resp_arr = $result->result('array');
		if ($resp_arr[0]['Count'] > 0) {
			return array(
				'Error_Msg' => 'Удаление невозможено! Существуют заявки на медикаменты данного вида!',
				'success' => false
			);
		}

		$query = "
			declare
				@Error_Code int,
				@Error_Message varchar(4000);
			exec p_DrugDocumentClass_del
				@DrugDocumentClass_id = :DrugDocumentClass_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		}
		return false;
	}

	/**
	 * Удаление статуса заявки на медикаменты
	 */
	function deleteDrugDocumentStatus($data)
	{
		$params = array('DrugDocumentStatus_id' => $data['DrugDocumentStatus_id']);

		$query = "
			select top 1
				count(DocumentUc_id) as Count
			from
				v_DocumentUc DU with(nolock)
			where
				DU.DrugDocumentStatus_id = :DrugDocumentStatus_id
		";

		$result = $this->db->query($query, $params);

		if (!is_object($result)) {
			return array('Error_Msg' => 'Ошибка при проверке использования статусов', 'success' => false);
		}
		$resp_arr = $result->result('array');
		if ($resp_arr[0]['Count'] > 0) {
			return array(
				'Error_Msg' => 'Удаление невозможено! Существуют заявки на медикаменты с данным статусом!',
				'success' => false
			);
		}

		$query = "
			declare
				@Error_Code int,
				@Error_Message varchar(4000);
			exec p_DrugDocumentStatus_del
				@DrugDocumentStatus_id = :DrugDocumentStatus_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		}
		return false;
	}
}