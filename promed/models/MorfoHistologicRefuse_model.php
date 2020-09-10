<?php
/**
 * MorfoHistologicRefuse_model - модель для работы с отказами от вскрытия тел умерших (АРМ Патологоанатома)
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @access       public
 * @author       Shekunov Dmitriy
 */

class MorfoHistologicRefuse_model extends swModel {
	/**
	 * constructor
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Получение данных списка типов отказа от вскрытия
	 * @return array | bool
	 * Используется: форма редактирования сведений об отказе от вскрытия тел умерших
	 */
	function getMorfoHistologicRefuseTypeList() {

		$query = "
			SELECT 
			MorfoHistologicRefuseType_id
			,MorfoHistologicRefuseType_code
			,MorfoHistologicRefuseType_name
			FROM
				v_MorfoHistologicRefuseType with (nolock)
		";
		$result = $this->db->query($query, array());

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Получение данных для формы редактирования сведений об отказе от вскрытия тел умерших
	 * @param $data
	 * @return array | bool
	 * Используется: форма редактирования сведений об отказе от вскрытия тел умерших
	 */
	function loadMorfoHistologicRefuseEditForm($data) {
		
		$query = "
			SELECT TOP 1
			MorfoHistologicRefuse_id
			,EvnDirectionMorfoHistologic_id
			,convert(varchar(10), MorfoHistologicRefuse_setDT, 104) as MorfoHistologic_refuseDate
			,MorfoHistologicRefuseType_id as RefuseType_id
			,Person_id
			FROM
				v_MorfoHistologicRefuse  with (nolock)
			WHERE (1 = 1)
			AND MorfoHistologicRefuse_id = :MorfoHistologicRefuse_id
		";
		$result = $this->db->query($query, array(
			'MorfoHistologicRefuse_id' => $data['MorfoHistologicRefuse_id']
		));
		
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Сохранение сведений об отказе от вскрытия тела умершего по патоморфогистологическому направлению
	 * @param $data
	 * @return array | bool
	 * Используется: форма редактирования сведений об отказе от вскрытия тел умерших
	 */
	function saveMorfoHistologicRefuse($data) {
		$procedure = '';

		if ( (!isset($data['MorfoHistologicRefuse_id'])) || ($data['MorfoHistologicRefuse_id'] <= 0) ) {
			$procedure = 'p_MorfoHistologicRefuse_ins';
		}
		else {
			$procedure = 'p_MorfoHistologicRefuse_upd';
		}

		if ( isset($data['MorfoHistologic_refuseDate']) ) {
			$data['MorfoHistologic_refuseDate'] .= ' 00:00:00.000';
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :MorfoHistologicRefuse_id;
			exec " . $procedure . "
				@MorfoHistologicRefuse_id = @Res output,
 				@EvnDirectionMorfoHistologic_id = :EvnDirectionMorfoHistologic_id,
 				@MorfoHistologicRefuse_setDT = :MorfoHistologicRefuse_setDT,
 				@MorfoHistologicRefuseType_id = :MorfoHistologicRefuseType_id,
 				@Person_id = :Person_id,
 				@pmUser_id = :pmUser_id,
 				@Error_Code = @ErrCode output,
 				@Error_Message = @ErrMessage output
			select @Res as MorfoHistologicRefuse_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'MorfoHistologicRefuse_id' =>  $data['MorfoHistologicRefuse_id'],
			'EvnDirectionMorfoHistologic_id' => $data['EvnDirectionMorfoHistologic_id'],
			'MorfoHistologicRefuse_setDT' => $data['MorfoHistologic_refuseDate'],
			'MorfoHistologicRefuseType_id' => $data['RefuseType_id'],
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
	 * Удаление  сведений об отказе от вскрытия тела умершего по патоморфогистологическому направлению
	 * @param $data
	 * @return array
	 * Используется: журнал рабочего места патологоанатома
	 */
	function deleteMorfoHistologicRefuse($data) {
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_MorfoHistologicRefuse_del
				@MorfoHistologicRefuse_id = :MorfoHistologicRefuse_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$result = $this->db->query($query, array(
			'MorfoHistologicRefuse_id' => $data['MorfoHistologicRefuse_id'],
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