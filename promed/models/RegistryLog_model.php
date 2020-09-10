<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb
 *
 * Класс для логгирования операций с реестрами
 *
 * @package				CostPrint
 * @copyright			Copyright (c) 2014 Swan Ltd.
 * @author				Dmitriy Vlasenko
 * @link				http://swan.perm.ru/PromedWeb
 */
class RegistryLog_model extends swModel {
	/**
	 * Запись данных
	 */
	function saveRegistryLog($data) {
		$proc = "p_RegistryLog_ins";
		if (!empty($data['RegistryLog_id'])) {
			$proc = "p_RegistryLog_upd";
		}

		$RegistryLog_begDate = ':RegistryLog_begDate';
		if (!empty($data['RegistryLog_begDate']) && $data['RegistryLog_begDate'] == '@curDate') {
			$RegistryLog_begDate = '@curDate';
		}

		$RegistryLog_endDate = ':RegistryLog_endDate';
		if (!empty($data['RegistryLog_endDate']) && $data['RegistryLog_endDate'] == '@curDate') {
			$RegistryLog_endDate = '@curDate';
		}

		$query = "
			declare
				@curDate datetime,
				@RegistryLog_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000)

			set @curDate = dbo.tzGetDate();
			set @RegistryLog_id = :RegistryLog_id;

			exec {$proc}
				@RegistryLog_id = @RegistryLog_id output,
				@Registry_id = :Registry_id,
				@RegistryLog_begDate = {$RegistryLog_begDate},
				@RegistryLog_endDate = {$RegistryLog_endDate},
				@RegistryActionType_id = :RegistryActionType_id,
				@RegistryErrorTFOMSType_id = :RegistryErrorTFOMSType_id,
				@RegistryLog_CountEvn = :RegistryLog_CountEvn,
				@RegistryLog_CountEvnErr = :RegistryLog_CountEvnErr,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select convert(varchar, @curDate, 121) as curDate, @RegistryLog_id as RegistryLog_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		return $this->queryResult($query, array(
			'RegistryLog_id' => !empty($data['RegistryLog_id'])?$data['RegistryLog_id']:null,
			'Registry_id' => $data['Registry_id'],
			'RegistryLog_begDate' => !empty($data['RegistryLog_begDate'])?$data['RegistryLog_begDate']:null,
			'RegistryLog_endDate' => !empty($data['RegistryLog_endDate'])?$data['RegistryLog_endDate']:null,
			'RegistryActionType_id' => !empty($data['RegistryActionType_id'])?$data['RegistryActionType_id']:null,
			'RegistryErrorTFOMSType_id' => !empty($data['RegistryErrorTFOMSType_id'])?$data['RegistryErrorTFOMSType_id']:null,
			'RegistryLog_CountEvn' => !empty($data['RegistryLog_CountEvn'])?$data['RegistryLog_CountEvn']:null,
			'RegistryLog_CountEvnErr' => !empty($data['RegistryLog_CountEvnErr'])?$data['RegistryLog_CountEvnErr']:null,
			'pmUser_id' => $data['pmUser_id']
		));
	}
}