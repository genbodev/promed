<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* WebService_model - модель для работы с данными
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      ?
*/


class WebService_model extends CI_Model {
	/**
	 * WebService_model constructor.
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * @return bool
	 */
	function beginTransaction() {
		$this->db->trans_begin();
		return true;
	}

	/**
	 * @return bool
	 */
	function commitTransaction() {
		$this->db->trans_commit();
		return true;
	}

	/**
	 * @return bool
	 */
	function rollbackTransaction() {
		$this->db->trans_rollback();
		return true;
	}

	/**
	 * @return array|bool
	 */
	function getPersonCardQueueList() {
		$query = "
			select
				ISNULL(P.BDZ_id, 0) as bdzID,
				Lpu.Lpu_Ouz as Lpu_Ouz,
				Lpu.Lpu_RegNomC as Lpu_RegNomC,
				Lpu.Lpu_RegNomN as Lpu_RegNomN,
				PS.Person_SurName as SurName,
				PS.Person_FirName as FirName,
				PS.Person_SecName as SecName,
				CONVERT(varchar, PS.Person_BirthDay, 121) as BirthDay,
				PS.Polis_Ser as polisSer,
				PS.Polis_Num as polisNum,
				cast(ISNULL(PCQ.PersonCardQueue_id, 0) as varchar(10)) as transactCode
			from PersonCardQueue PCQ with(nolock)
				inner join Person P with(nolock) on P.Person_id = PCQ.Person_id
				inner join v_Lpu Lpu with(nolock) on Lpu.Lpu_id = PCQ.Lpu_id
				inner join v_PersonState PS with(nolock) on PS.Person_id = PCQ.Person_id
			where PCQ.PersonCardQueue_Status is null
				-- and P.BDZ_id is not null
		";
		$result = $this->db->query($query);

		if ( is_object($result) ) {
			$response = $result->result('array');
			$resp = array();
			if ( is_array($response) && count($response) > 0 ) {
				foreach ( $response as $row ) {
					if ( isset($row['BirthDay']) ) {
						$row['BirthDay'] = str_replace(' ', 'T', $row['BirthDay']);
					}
					$resp[] = $row;
				}
			}
			return $resp;
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool|mixed
	 */
	function putPersonCardState($data) {
		return $this->setPersonCardQueueStatus($data);
	}

	/**
	 * @param $data
	 * @return bool|mixed
	 */
	function setPersonCardQueueStatus($data) {
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec p_PersonCardQueue_status
				@pmUser_id = 1,
				@PersonCardQueue_Status = :PersonCardQueue_Status,
				@PersonCardQueue_id = :PersonCardQueue_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'PersonCardQueue_id' => $data['transactCode'],
			'PersonCardQueue_Status' => $data['status']
		);

		$result = $this->db->query($query, $queryParams);


		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
}
