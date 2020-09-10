<?php
/**
 * MorfoHistologicCorpseReciept_model - модель для работы со сведениями о поступлениях тел умерших
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @access       public
 * @author       Shekunov Dmitriy
 */

class MorfoHistologicCorpseReciept_model extends swModel {
	/**
	 * constructor
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Получение данных для формы редактирования сведений о поступлении тел умерших
	 * @param $data
	 * @return array | bool
	 * Используется: форма редактирования сведений о поступлении тел умерших
	 */
	function loadMorfoHistologicCorpseRecieptEditForm($data) {
		
		$query = "
			SELECT TOP 1
			MHCR.MorfoHistologicCorpseReciept_id
			,MHCR.EvnDirectionMorfoHistologic_id
			,convert(varchar(10), MHCR.MorfoHistologicCorpseReciept_setDT, 104) as MorfoHistologicCorpse_recieptDate
			,MHCR.MedPersonal_id
			FROM
				v_MorfoHistologicCorpseReciept MHCR with (nolock)
			WHERE (1 = 1)
			AND MHCR.MorfoHistologicCorpseReciept_id = :MorfoHistologicCorpseReciept_id
		";
		$result = $this->db->query($query, array(
			'MorfoHistologicCorpseReciept_id' => $data['MorfoHistologicCorpseReciept_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Сохранение сведений о поступлении тела умершего по патоморфогистологическому направлению
	 * @param $data
	 * @return array | bool
	 * Используется: форма редактирования сведений о поступлении тел умерших
	 */
	function saveMorfoHistologicCorpseReciept($data) {
		$procedure = '';

		if ( (!isset($data['MorfoHistologicCorpseReciept_id'])) || ($data['MorfoHistologicCorpseReciept_id'] <= 0) ) {
			$procedure = 'p_MorfoHistologicCorpseReciept_ins';
		}
		else {
			$procedure = 'p_MorfoHistologicCorpseReciept_upd';
		}

		if ( isset($data['MorfoHistologicCorpse_recieptDate']) ) {
			$data['MorfoHistologicCorpse_recieptDate'] .= ' 00:00:00.000';
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :MorfoHistologicCorpseReciept_id;
			exec " . $procedure . "
				@MorfoHistologicCorpseReciept_id = @Res output,
 				@EvnDirectionMorfoHistologic_id = :EvnDirectionMorfoHistologic_id,
 				@MorfoHistologicCorpseReciept_setDT = :MorfoHistologicCorpseReciept_setDT,
 				@MedPersonal_id = :MedPersonal_id,
 				@pmUser_id = :pmUser_id,
 				@Error_Code = @ErrCode output,
 				@Error_Message = @ErrMessage output
			select @Res as MorfoHistologicCorpseReciept_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'MorfoHistologicCorpseReciept_id' =>  $data['MorfoHistologicCorpseReciept_id'],
			'EvnDirectionMorfoHistologic_id' => $data['EvnDirectionMorfoHistologic_id'],
			'MorfoHistologicCorpseReciept_setDT' => $data['MorfoHistologicCorpse_recieptDate'],
			'MedPersonal_id' => $data['MedPersonal_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		$result = $this->db->query($query, $queryParams);

		if (!is_object($result)) {
			return false;
		}

		return $result->result("array");
	}

	/**
	 * Удаление сведений о поступлении тела умершего по патоморфогистологическому направлению
	 * @param $data
	 * @return array
	 * Используется: журнал рабочего места патологоанатома
	 */
	function deleteMorfoHistologicCorpseReciept($data) {
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_MorfoHistologicCorpseReciept_del
				@MorfoHistologicCorpseReciept_id = :MorfoHistologicCorpseReciept_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$result = $this->db->query($query, array(
			'MorfoHistologicCorpseReciept_id' => $data['MorfoHistologicCorpseReciept_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}
	}

	/**
	 * Получение даты поступления тела
	 * @param $data
	 * @return array | bool
	 * Используется: форма редактирования протокола патоморфогистологического исследования
	 */
	function getMorfoHistologicCorpseRecieptDate($data) {

		$query = "
			SELECT TOP 1
			convert(varchar(10), MorfoHistologicCorpseReciept_setDT, 104) as MorfoHistologicCorpse_recieptDate
			FROM
				v_MorfoHistologicCorpseReciept with (nolock)
			WHERE (1 = 1)
			AND EvnDirectionMorfoHistologic_id = :EvnDirectionMorfoHistologic_id
		";
		$result = $this->db->query($query, array(
			'EvnDirectionMorfoHistologic_id' => $data['EvnDirectionMorfoHistologic_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
}
?>