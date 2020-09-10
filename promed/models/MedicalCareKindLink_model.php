<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * swMedicalCareKindLinkViewWindow - модель для работы с настройками кодов видов медицинской помощи
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			20.11.2013
 */

class MedicalCareKindLink_model extends swModel {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Возвращает список настроек кодов видов медицинской помощи
	 */
	function loadMedicalCareKindLinkGrid($data)
	{
		$params = array();

		$query = "
			select
				MCKL.MedicalCareKindLink_id,
				MCK.MedicalCareKind_id,
				LUT.LpuUnitType_id,
				PT.PayType_id,
				LSP.LpuSectionProfile_id,
				EC.EvnClass_id,
				MCK.MedicalCareKind_Code,
				MCK.MedicalCareKind_Name,
				LUT.LpuUnitType_Name,
				PT.PayType_Name,
				LSP.LpuSectionProfile_Name,
				EC.EvnClass_Name
			from
				v_MedicalCareKindLink MCKL with(nolock)
				left join nsi.MedicalCareKind MCK with(nolock) on MCK.MedicalCareKind_id = MCKL.MedicalCareKind_id
				left join v_PayType PT with(nolock) on PT.PayType_id = MCKL.PayType_id
				left join v_LpuUnitType LUT with(nolock) on LUT.LpuUnitType_id = MCKL.LpuUnitType_id
				left join v_LpuSectionProfile LSP with(nolock) on LSP.LpuSectionProfile_id = MCKL.LpuSectionProfile_id
				left join EvnClass EC with(nolock) on EC.EvnClass_id = MCKL.EvnClass_id
			order by
				MCK.MedicalCareKind_Code,
				MCK.MedicalCareKind_Name,
				LSP.LpuSectionProfile_Name,
				EC.EvnClass_Name,
				PT.PayType_Name,
				LUT.LpuUnitType_Name
		";

		$result = $this->db->query($query, $params);

		if (is_object($result))
		{
			$response['data'] = $result->result('array');
			return $response;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Возвращает данные для формы настроек кодов видов медицинской помощи
	 */
	function loadMedicalCareKindLinkForm($data)
	{
		$params = array('MedicalCareKindLink_id' => $data['MedicalCareKindLink_id']);

		$query = "
			select top 1
				MCKL.MedicalCareKindLink_id,
				MCKL.MedicalCareKind_id,
				MCKL.LpuSectionProfile_id,
				MCKL.EvnClass_id,
				MCKL.PayType_id,
				MCKL.LpuUnitType_id
			from
				v_MedicalCareKindLink MCKL with(nolock)
			where MCKL.MedicalCareKindLink_id = :MedicalCareKindLink_id
		";

		$result = $this->db->query($query, $params);

		if (is_object($result))
		{
			$response = $result->result('array');
			return $response;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Сохраняет настройку кода вида медицинской помощи
	 */
	function saveMedicalCareKindLink($data)
	{
		$params = array(
			'MedicalCareKindLink_id' => (isset($data['MedicalCareKindLink_id']) ? $data['MedicalCareKindLink_id'] : null),
			'MedicalCareKind_id' => $data['MedicalCareKind_id'],
			'LpuSectionProfile_id' => $data['LpuSectionProfile_id'],
			'EvnClass_id' => $data['EvnClass_id'],
			'PayType_id' => $data['PayType_id'],
			'LpuUnitType_id' => $data['LpuUnitType_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		$query = "
			select
				MCKL.MedicalCareKindLink_id
			from
				v_MedicalCareKindLink MCKL with(nolock)
			where
				MCKL.MedicalCareKind_id = :MedicalCareKind_id
				and MCKL.LpuSectionProfile_id = :LpuSectionProfile_id
				and MCKL.EvnClass_id = :EvnClass_id
				and MCKL.PayType_id = :PayType_id
				and ISNULL(MCKL.LpuUnitType_id, 0) = ISNULL(:LpuUnitType_id, 0)
		";

		$result = $this->db->query($query, $params);

		if ( !is_object($result) ) {
			$response['Error_Msg'] = 'Ошибка при выполнении запроса к базе данных (проверка дублей)';
			return array($response);
		}

		$queryResponse = $result->result('array');

		if ( !is_array($queryResponse) ) {
			$response['Error_Msg'] = 'Ошибка при проверке дублей настройки кода вида медицинской помощи';
			return array($response);
		}
		else if ( count($queryResponse) > 0 ) {
			$response['Error_Msg'] = 'Обнаружены дубли настройки кода вида медицинской помощи';
			return array($response);
		}

		$query = "
			declare
				@MedicalCareKindLink_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @MedicalCareKindLink_id = :MedicalCareKindLink_id;
			exec p_MedicalCareKindLink_" . (!empty($data['MedicalCareKindLink_id']) && $data['MedicalCareKindLink_id'] > 0 ? "upd" : "ins") . "
				@MedicalCareKindLink_id = @MedicalCareKindLink_id output,
				@MedicalCareKind_id = :MedicalCareKind_id,
				@LpuSectionProfile_id = :LpuSectionProfile_id,
				@EvnClass_id = :EvnClass_id,
				@PayType_id = :PayType_id,
				@LpuUnitType_id = :LpuUnitType_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @MedicalCareKindLink_id as MedicalCareKindLink_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
}