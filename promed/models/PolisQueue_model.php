<?php
class PolisQueue_model extends CI_Model {
	/**
	 * PolisQueue_model constructor.
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * @param $regNomC
	 * @param $regNomN
	 * @return array
	 */
	function getOrgSmoId($regNomC, $regNomN) {
		$response = array(
			'OrgSmo_id' => 0,
			'Error_Msg' => ''
		);

		$query = "
			select top 1
				OrgSmo_id
			from v_OrgSMO with (nolock)
			where
				OrgSMO_RegNomC = :OrgSMO_RegNomC
				and OrgSMO_RegNomN = :OrgSMO_RegNomN
		";

		$queryParams = array(
			'OrgSMO_RegNomC' => $regNomC,
			'OrgSMO_RegNomN' => $regNomN
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$record = $result->result('array');

			if ( is_array($record) && count($record) > 0 ) {
				if ( array_key_exists('OrgSmo_id', $record[0]) && is_numeric($record[0]['OrgSmo_id']) && $record[0]['OrgSmo_id'] > 0 ) {
					$response['OrgSmo_id'] = $record[0]['OrgSmo_id'];
				}
				else {
					$response['Error_Msg'] = 'Ошибка при получении идентификатора СМО';
				}
			}
			else {
				$response['Error_Msg'] = 'Ошибка при получении идентификатора СМО';
			}
		}
		else {
			$response['Error_Msg'] = 'Ошибка при выполнении запроса к базе данных (получение идентификатора СМО)';
		}

		return $response;
	}

	/**
	 * @param $data
	 * @return array
	 */
	function savePolisQueue($data) {
		$response = array(
			'success' => true,
			'Error_Msg' => ''
		);

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = null;

			exec p_PolisQueue_ins
				@PolisQueue_id = @Res output,
				@Polis_id = :Polis_id,
				@OrgSMO_id = :OrgSmo_id,
				@PolisQueue_setDT = :PolisQueue_setDT,
				@pmUser_id = 1,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as PolisQueue_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$this->db->trans_begin();

		foreach ( $data as $record ) {
			$DT = explode(' ', $record['polisSetDT']);

			$queryParams = array(
				'Polis_id' => $record['polisID'],
				'OrgSmo_id' => $record['OrgSmo_id'],
				'PolisQueue_setDT' => ConvertDateFormat($DT[0]) . ' ' . $DT[1]
			);

			$result = $this->db->query($query, $queryParams);

			if ( is_object($result) ) {
				$saveResponse = $result->result('array');

				if ( is_array($saveResponse) && count($saveResponse) > 0 ) {
					if ( array_key_exists('Error_Msg', $saveResponse[0]) && !empty($saveResponse[0]['Error_Msg']) ) {
						$response['success'] = false;
						$response['Error_Msg'] = $saveResponse[0]['Error_Msg'];
						break;
					}
				}
				else {
					$response['Error_Msg'] = 'Ошибка при добавлении идентификатора полиса в очередь';
				}
			}
			else {
				$response['success'] = false;
				$response['Error_Msg'] = 'Ошибка при выполнении запроса к базе данных (добавление идентификатора полиса в очередь)';
				break;
			}
		}

		if ( $response['success'] === true ) {
			$this->db->trans_commit();
		}
		else {
			$this->db->trans_rollback();
		}

		return $response;
	}
}
