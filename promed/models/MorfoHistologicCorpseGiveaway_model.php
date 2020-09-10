<?php
/**
 * MorfoHistologicCorpseGiveaway_model - модель для работы со сведениями о выдаче тел умерших
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @access       public
 * @author       Shekunov Dmitriy
 */

class MorfoHistologicCorpseGiveaway_model extends swModel {
	/**
	 * constructor
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Получение данных для формы редактирования сведений о выдаче тел умерших
	 * @param $data
	 * @return array | bool
	 * Используется: форма редактирования сведений о выдаче тел умерших
	 */
	function loadMorfoHistologicCorpseGiveawayEditForm($data) {
		
		$query = "
			SELECT TOP 1
			MHCG.MorfoHistologicCorpseGiveaway_id
			,MHCG.EvnDirectionMorfoHistologic_id
			,convert(varchar(10), MHCG.MorfoHistologicCorpseGiveaway_setDT, 104) as MorfoHistologicCorpse_giveawayDate
			,MHCG.MedPersonal_id
			,MHCG.Person_id
			FROM
				v_MorfoHistologicCorpseGiveaway MHCG with (nolock)
			WHERE (1 = 1)
			AND MHCG.MorfoHistologicCorpseGiveaway_id = :MorfoHistologicCorpseGiveaway_id
		";
		$result = $this->db->query($query, array(
			'MorfoHistologicCorpseGiveaway_id' => $data['MorfoHistologicCorpseGiveaway_id']
		));
		
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Сохранение сведений о выдаче тела умершего по патоморфогистологическому направлению
	 * @param $data
	 * @return array | bool
	 * Используется: форма редактирования сведений о выдаче тел умерших
	 */
	function saveMorfoHistologicCorpseGiveaway($data) {
		$procedure = '';

		if ( (!isset($data['MorfoHistologicCorpseGiveaway_id'])) || ($data['MorfoHistologicCorpseGiveaway_id'] <= 0) ) {
			$procedure = 'p_MorfoHistologicCorpseGiveaway_ins';
		}
		else {
			$procedure = 'p_MorfoHistologicCorpseGiveaway_upd';
		}

		if ( isset($data['MorfoHistologicCorpse_GiveawayDate']) ) {
			$data['MorfoHistologicCorpseGiveaway_GiveawayDate'] .= ' 00:00:00.000';
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :MorfoHistologicCorpseGiveaway_id;
			exec " . $procedure . "
				@MorfoHistologicCorpseGiveaway_id = @Res output,
 				@EvnDirectionMorfoHistologic_id = :EvnDirectionMorfoHistologic_id,
 				@MorfoHistologicCorpseGiveaway_setDT = :MorfoHistologicCorpseGiveaway_setDT,
 				@MedPersonal_id = :MedPersonal_id,
 				@Person_id = :Person_id,
 				@pmUser_id = :pmUser_id,
 				@Error_Code = @ErrCode output,
 				@Error_Message = @ErrMessage output
			select @Res as MorfoHistologicCorpseGiveaway_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'MorfoHistologicCorpseGiveaway_id' =>  $data['MorfoHistologicCorpseGiveaway_id'],
			'EvnDirectionMorfoHistologic_id' => $data['EvnDirectionMorfoHistologic_id'],
			'MorfoHistologicCorpseGiveaway_setDT' => $data['MorfoHistologicCorpse_giveawayDate'],
			'MedPersonal_id' => $data['MedPersonal_id'],
			'Person_id' => $data['Person_id'],
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
	function deleteMorfoHistologicCorpseGiveaway($data) {
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_MorfoHistologicCorpseGiveaway_del
				@MorfoHistologicCorpseGiveaway_id = :MorfoHistologicCorpseGiveaway_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$result = $this->db->query($query, array(
			'MorfoHistologicCorpseGiveaway_id' => $data['MorfoHistologicCorpseGiveaway_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}
	}
}
?>