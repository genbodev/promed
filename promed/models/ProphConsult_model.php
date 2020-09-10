<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* ProphConsult_model - модель для работы с записями в 'Показания к углубленному профилактическому консультированию'
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

class ProphConsult_model extends CI_Model
{
	/**
	 * ProphConsult_model constructor.
	 */
    function __construct()
    {
        parent::__construct();
    }

	/**
	 * @param $data
	 * @return bool|mixed
	 */
	function loadProphConsultGrid($data)
	{
		$filter = " and PC.EvnPLDisp_id = :EvnPLDisp_id";
		
		if (!empty($data['ProphConsult_id'])) {
			$filter = " and PC.ProphConsult_id = :ProphConsult_id";
		}
		
		$query = "
			select 
				PC.EvnPLDisp_id,
				PC.ProphConsult_id,
				PC.RiskFactorType_id,
				RFT.RiskFactorType_Name
			from
				v_ProphConsult PC (nolock)
				left join v_RiskFactorType RFT (nolock) on RFT.RiskFactorType_id = PC.RiskFactorType_id
			where
				(1=1) {$filter}
			order by
				PC.ProphConsult_id
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
	function getProphConsultViewData($data) {
		if (empty($data['ProphConsult_pid'])) {
			return array();
		}

		$filter = "";
		$queryParams = array(
			'EvnPLDisp_id' => $data['ProphConsult_pid']
		);

		$query = "
			select
				PC.ProphConsult_id,
				PC.EvnPLDisp_id,
				RFT.RiskFactorType_Name
			from
				v_ProphConsult PC with(nolock)
				left join v_RiskFactorType RFT (nolock) on RFT.RiskFactorType_id = PC.RiskFactorType_id
			where
				PC.EvnPLDisp_id = :EvnPLDisp_id
				{$filter}
		";

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function checkProphConsultExists($data) {
		$query = "
			select top 1
				ProphConsult_id
			from
				v_ProphConsult (nolock)
			where
				EvnPLDisp_id = :EvnPLDisp_id
				and RiskFactorType_id = :RiskFactorType_id
				and ProphConsult_id <> ISNULL(:ProphConsult_id,0)
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
	 * @param $data
	 * @return bool|mixed
	 */
	function saveProphConsult($data) {
		if (!empty($data['ProphConsult_id']) && $data['ProphConsult_id'] > 0) {
			$proc = "p_ProphConsult_upd";
		} else {
			$proc = "p_ProphConsult_ins";
		}
		
		$sql = "
			declare
				@ProphConsult_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @ProphConsult_id = :ProphConsult_id;
			exec {$proc}
				@ProphConsult_id = @ProphConsult_id output,
				@EvnPLDisp_id = :EvnPLDisp_id,
				@RiskFactorType_id = :RiskFactorType_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
            select @ProphConsult_id as ProphConsult_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
	function addProphConsult($data, $RiskFactorType_id) {
		// проверяем есть ли такой, если нет, то добавляем
		if (!empty($RiskFactorType_id)) {
			$data['RiskFactorType_id'] = $RiskFactorType_id;
			$query = "
				select top 1
					ProphConsult_id
				from v_ProphConsult (nolock)
				where EvnPLDisp_id = :EvnPLDisp_id and RiskFactorType_id = :RiskFactorType_id
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
				'ProphConsult_id' => null,
				'EvnPLDisp_id' => $data['EvnPLDisp_id'],
				'RiskFactorType_id' => $data['RiskFactorType_id'],
				'pmUser_id' => $data['pmUser_id']
			);
			$this->saveProphConsult($params);
		}
	}

	/**
	 *	Удаление
	 */
	function delProphConsult($data, $RiskFactorType_id) {
		// проверяем есть ли такой, если есть, то удаляем
		if (!empty($RiskFactorType_id)) {
			$data['RiskFactorType_id'] = $RiskFactorType_id;
			$query = "
				select top 1
					ProphConsult_id
				from v_ProphConsult (nolock)
				where EvnPLDisp_id = :EvnPLDisp_id and RiskFactorType_id = :RiskFactorType_id
			";

			$result = $this->db->query($query, $data);

			if (is_object($result))
			{
				$resp = $result->result('array');
				if (count($resp) > 0 && !empty($resp[0]['ProphConsult_id'])) {
					$params = array(
						'ProphConsult_id' => $resp[0]['ProphConsult_id'],
						'pmUser_id' => $data['pmUser_id']
					);

					$sql = "
						declare
							@ErrCode int,
							@ErrMessage varchar(4000);
						exec p_ProphConsult_del
							@ProphConsult_id = :ProphConsult_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output;
						select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
					";
					$res = $this->db->query($sql, $params);
					if ( is_object($res) ) {
						return $res->result('array');
					} else {
						return false;
					}
				}
			}
		}
	}
}
?>