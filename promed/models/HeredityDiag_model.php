<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* HeredityDiag_model - модель для работы с записями в 'Наследственность по заболеваниям'
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2013 Swan Ltd.
* @author       Dmitry Vlasenko
* @version      02.07.2013
*/

class HeredityDiag_model extends CI_Model
{
	/**
	 *	Конструктор
	 */	
    function __construct()
    {
        parent::__construct();
    }
	
	/**
	 *	Загрузка грида
	 */	
	function loadHeredityDiagGrid($data) 
	{
		$filter = " and HD.EvnPLDisp_id = :EvnPLDisp_id";
		
		if (!empty($data['HeredityDiag_id'])) {
			$filter = " and HD.HeredityDiag_id = :HeredityDiag_id";
		}
		
		$query = "
			select 
				HD.EvnPLDisp_id,
				HD.HeredityDiag_id,
				HD.Diag_id,
				D.Diag_Name,
				HD.HeredityType_id,
				HT.HeredityType_Name
			from
				v_HeredityDiag HD (nolock)
				left join v_Diag D (nolock) on D.Diag_id = HD.Diag_id
				left join v_HeredityType HT (nolock) on HT.HeredityType_id = HD.HeredityType_id
			where
				(1=1) {$filter}
			order by
				HD.HeredityDiag_id
		";
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Получение данных для ЭМК
	 */
	function getHeredityDiagViewData($data) {
		if (empty($data['HeredityDiag_pid'])) {
			return array();
		}

		$filter = "";
		$queryParams = array(
			'EvnPLDisp_id' => $data['HeredityDiag_pid']
		);

		$query = "
			select
				HD.HeredityDiag_id,
				HD.EvnPLDisp_id,
				D.Diag_id,
				D.Diag_Code,
				D.Diag_Name,
				HT.HeredityType_Name
			from
				v_HeredityDiag HD with(nolock)
				left join v_Diag D with(nolock) on D.Diag_id = HD.Diag_id
				left join v_HeredityType HT (nolock) on HT.HeredityType_id = HD.HeredityType_id
			where
				HD.EvnPLDisp_id = :EvnPLDisp_id
				{$filter}
		";

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return swFilterResponse::filterNotViewDiag($result->result('array'), $data);
		}
		else {
			return false;
		}
	}
	
	/**
	 *	Проверка существования
	 */	
	function checkHeredityDiagExists($data) {
		$query = "
			select top 1
				HeredityDiag_id
			from
				v_HeredityDiag (nolock)
			where
				EvnPLDisp_id = :EvnPLDisp_id
				and Diag_id = :Diag_id
				and HeredityDiag_id <> ISNULL(:HeredityDiag_id,0)
		";
		
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) > 0) {
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 *	Сохранение
	 */	
	function saveHeredityDiag($data) {
		if (!empty($data['HeredityDiag_id']) && $data['HeredityDiag_id'] > 0) {
			$proc = "p_HeredityDiag_upd";
		} else {
			$proc = "p_HeredityDiag_ins";
		}
		
		$sql = "
			declare
				@HeredityDiag_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @HeredityDiag_id = :HeredityDiag_id;
			exec {$proc}
				@HeredityDiag_id = @HeredityDiag_id output,
				@EvnPLDisp_id = :EvnPLDisp_id,
				@Diag_id = :Diag_id,
				@HeredityType_id = :HeredityType_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
            select @HeredityDiag_id as HeredityDiag_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
   		$res = $this->db->query($sql, $data);
		if ( is_object($res) ) {
 	    	return $res->result('array');
		} else {
 	    	return false;
		}
	}
	
	/**
	 *	Добавление
	 */	
	function addHeredityDiag($data, $diag_id, $hereditytype_id) {
		// проверяем есть ли такой диагноз уже, если нет, то добавляем
		if (!empty($diag_id)) {
			$data['Diag_id'] = $diag_id;
			$query = "
				select top 1
					HeredityDiag_id
				from v_HeredityDiag (nolock)
				where EvnPLDisp_id = :EvnPLDisp_id and Diag_id = :Diag_id
			";
			
			$result = $this->db->query($query, $data);

			if (is_object($result))
			{
				$resp = $result->result('array');
				if (count($resp) > 0) {
					return false;
				}
			}

			$params = array(
				'HeredityDiag_id' => null,
				'EvnPLDisp_id' => $data['EvnPLDisp_id'],
				'Diag_id' => $data['Diag_id'],
				'HeredityType_id' => $hereditytype_id,
				'pmUser_id' => $data['pmUser_id']
			);
			$this->saveHeredityDiag($params);		
		}
	}
	
	/**
	 *	Удаление
	 */	
	function delHeredityDiag($data) {
		$sql = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_HeredityDiag_del
				@HeredityDiag_id = :HeredityDiag_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
            select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
   		$res = $this->db->query($sql, $data);
		if ( is_object($res) ) {
 	    	return $res->result('array');
		} else {
 	    	return false;
		}
	}
	
	/**
	 *	Удаление по диагнозу
	 */	
	function delHeredityDiagByDiag($data, $diag_id) {
		// проверяем есть ли такой диагноз уже, если есть, то удаляем
		if (!empty($diag_id)) {
			$data['Diag_id'] = $diag_id;
			$query = "
				select top 1
					HeredityDiag_id
				from v_HeredityDiag (nolock)
				where EvnPLDisp_id = :EvnPLDisp_id and Diag_id = :Diag_id
			";
			
			$result = $this->db->query($query, $data);

			if (is_object($result))
			{
				$resp = $result->result('array');
				if (count($resp) > 0 && !empty($resp[0]['HeredityDiag_id'])) {
					$params = array(
						'HeredityDiag_id' => $resp[0]['HeredityDiag_id'],
						'pmUser_id' => $data['pmUser_id']
					);
					$this->delHeredityDiag($params);
				}
			}
		}
	}
}
?>