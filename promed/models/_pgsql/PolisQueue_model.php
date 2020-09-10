<?php

/**
 * Class PolisQueue_model
 * @property-read CI_DB_driver $db
 */
class PolisQueue_model extends SwPgModel
{
	/**
	 * @param $regNomC
	 * @param $regNomN
	 * @return array
	 */
	public function getOrgSmoId($regNomC, $regNomN)
    {
		$response = [
			'OrgSmo_id' => 0,
			'Error_Msg' => ''
		];

		$query = "
			select
				OrgSmo_id as \"OrgSmo_id\"
 			from
 			    v_OrgSMO
			where
				OrgSMO_RegNomC = :OrgSMO_RegNomC
            and
                OrgSMO_RegNomN = :OrgSMO_RegNomN
			limit 1
		";

		$params = [
			'OrgSMO_RegNomC' => $regNomC,
			'OrgSMO_RegNomN' => $regNomN
		];

		$result = $this->db->query($query, $params);

		if (!is_object($result) ) {
			$response['Error_Msg'] = 'Ошибка при выполнении запроса к базе данных (получение идентификатора СМО)';
			return $response;
		}

        $record = $result->result('array');

        if (count($record)) {
            $record = $record[0];
            if (isset($record['OrgSmo_id']) && $record['OrgSmo_id'] > 0 ) {
                $response['OrgSmo_id'] = $record['OrgSmo_id'];
            } else {
                $response['Error_Msg'] = 'Ошибка при получении идентификатора СМО';
            }
        } else {
            $response['Error_Msg'] = 'Ошибка при получении идентификатора СМО';
        }
		return $response;
	}

    /**
     * @param array $data
     * @return array
     * @throws Exception
     */
	public function savePolisQueue($data)
    {
		$response = [
			'success' => true,
			'Error_Msg' => ''
		];

		$query = "
            select
                PolisQueue_id as \"PolisQueue_id\",
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\"
			from p_PolisQueue_ins
			(
				PolisQueue_id := :PolisQueue_id,
				Polis_id := :Polis_id,
				OrgSMO_id := :OrgSmo_id,
				PolisQueue_setDT := :PolisQueue_setDT,
				pmUser_id := 1
			)
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
