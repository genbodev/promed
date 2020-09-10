<?php

/**
 * Class BDZData_model
 */
class BDZData_model extends SwPgModel
{
    protected $dateTimeForm104 = "'dd.mm.yyyy'";
    protected $dateTimeForm108 = "'HH24:MI:SS'";
	/**
	 * @return bool|mixed
	 */
	public function getLpuAttachmentData()
    {
		$query = "
			select
				PCQ.PersonCardQueue_id as \"PersonCardQueue_id\",
				coalesce(P.BDZ_id, 0) as \"BDZ_id\",
				coalesce(L.Lpu_RegNomC, '0') as \"Lpu_RegNomC\",
				coalesce(L.Lpu_RegNomN, '0') as \"Lpu_RegNomN\",
				to_char(PCQ.PersonCard_begDate, {$this->dateTimeForm104}) as \"PersonCard_begDate\",
				to_char(PCQ.PersonCard_endDate, {$this->dateTimeForm104}) as \"PersonCard_endDate\",
				coalesce(CCC.CardCloseCause_Code, 0) as \"CardCloseCause_Code\"
			from
			    v_PersonCardQueue PCQ 
				inner join v_Person P on P.Person_id = PCQ.Person_id
				inner join v_Lpu L on L.Lpu_id = PCQ.Lpu_id
				left join CardCloseCause CCC on CCC.CardCloseCause_id = PCQ.CardCloseCause_id
			where
			    PCQ.PersonCardQueue_sendDT is null
				and PCQ.PersonCardQueue_recvDT is null
				-- Не нужны записи об откреплении при смене участка внутри одной ЛПУ
				and coalesce(CCC.CardCloseCause_Code, 0) <> 4
				-- Только люди из БДЗ
				and P.BDZ_id is not null
			order by
				P.BDZ_id,
				PCQ.PersonCard_begDate
	    ";
		$result = $this->db->query($query);

		if ( !is_object($result) )
            return false;

        return $result->result('array');
    }

	/**
	 * @return array
	 */
	public function getOrgSmoAccountData()
    {
		return [
			[
				'OrgSmo_id' => NULL,
				'Login' => 'PKF_PKMIAC',
				'Password' => '8f20962cf18c731b8b4c422798945229f81beb15'
			]
        ];
	}

	/**
	 * @param $regNomC
	 * @param $regNomN
	 * @return array
	 */
	public function getOrgSmoId($regNomC, $regNomN) {
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

		$queryParams = [
			'OrgSMO_RegNomC' => $regNomC,
			'OrgSMO_RegNomN' => $regNomN
		];

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$record = $result->result('array');

			if ( is_array($record) && count($record) > 0 ) {
				if ( array_key_exists('OrgSmo_id', $record[0]) && is_numeric($record[0]['OrgSmo_id']) && $record[0]['OrgSmo_id'] > 0 ) {
					$response['OrgSmo_id'] = $record[0]['OrgSmo_id'];
				} else {
					$response['Error_Msg'] = 'Ошибка при получении идентификатора СМО';
				}
			} else {
				$response['Error_Msg'] = 'Ошибка при получении идентификатора СМО';
			}
		} else {
			$response['Error_Msg'] = 'Ошибка при выполнении запроса к базе данных (получение идентификатора СМО)';
		}

		return $response;
	}

	/**
	 * @param $orgSmoId
	 * @return bool|mixed
	 */
	public function getPolisQueueList($orgSmoId)
    {
		$filter = "";
		$queryParams = array();

		if ( !empty($orgSmoId) ) {
			$filter .= "and OrgSMO_id = :OrgSMO_id";
			$queryParams['OrgSMO_id'] = $orgSmoId;
		}

		$query = "
			select
				PolisQueue_id as \"PolisQueue_id\",
				Polis_id as \"Polis_id\",
				OrgSMO_id as \"OrgSMO_id\"
			from
			    PolisQueue
			where
			    PolisQueue_IsLoad is null
			and
			    PolisQueue_ErrorCode is null
        " . $filter;
		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) )
            return false;

        return $result->result('array');
    }

	/**
	 * @return bool|mixed
	 */
	public function getStartDT()
    {
		$query = "
			select
				to_char(max(PolisQueue_endDT), 'dd.mm.yyyy hh24:mi:ss') as \"endDT\"
			from
				PolisQueue
		";
		$result = $this->db->query($query);

		if ( !is_object($result) ) {
            return false;
		}

        return $result->result('array');
	}

	/**
	 * @param $BDZ_id
	 * @return bool|mixed
	 */
	public function loadBDZData($BDZ_id)
    {
		$query = "
		    select xp_BDZData_load (
		        LoadBDZ_id := :LoadBDZ_id
		    )";
		$result = $this->db->query($query, ['LoadBDZ_id' => !empty($BDZ_id) ? $BDZ_id : NULL]);

		if (! is_object($result) )
            return false;

        return $result->result('array');
	}

	/**
	 * @return bool|mixed
	 */
	public function parseBDZData()
    {
		$query = "select xp_BDZData_parse()";
		$result = $this->db->query($query);

		if ( !is_object($result) )
            return false;

        return $result->result('array');
	}

	/**
	 * @param $data
	 * @return bool|mixed
	 */
	public function saveBDZDataRecord($data)
    {
		if ( !is_array($data) || count($data) == 0 || !(empty($data['Polis_endDate']) || $data['Polis_endDate']>=$data['Polis_begDate']) ) {
			return false;
		}

		$query = "
            select
                BDZData_id as \"BDZData_id\",
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\"
			from p_BDZData_ins
			(
				BDZ_id := :BDZ_id,
				Person_SurName := :Person_SurName,
				Person_FirName := :Person_FirName,
				Person_SecName := :Person_SecName,
				Person_BirthDay := :Person_BirthDay,
				Sex_Code := :Sex_Code,
				SocStatus_Code := :SocStatus_Code,
				Person_Snils := :Person_Snils,
				Person_EdNum := :Person_EdNum,
				DocumentType_Code := :DocumentType_Code,
				Document_Ser := :Document_Ser,
				Document_Num := :Document_Num,
				Document_begDate := :Document_begDate,
				UKLAdr_Code := :UKLAdr_Code,
				UAddress_House := :UAddress_House,
				UAddress_Flat := :UAddress_Flat,
				PKLAdr_Code := :PKLAdr_Code,
				PAddress_House := :PAddress_House,
				PAddress_Flat := :PAddress_Flat,
				OrgSMO_RegNomC := :OrgSMO_RegNomC,
				OrgSMO_RegNomN := :OrgSMO_RegNomN,
				OmsSprTerr_Code := :OmsSprTerr_Code,
				Polis_Ser := :Polis_Ser,
				Polis_Num := :Polis_Num,
				Polis_begDate := :Polis_begDate,
				Polis_endDate := :Polis_endDate,
				Polis_planDate := :Polis_planDate,
				Polis_changeDate := :Polis_changeDate,
				PolisCloseCause_Code := :PolisCloseCause_Code
			)
		";

		$queryParams = [
			'BDZ_id' => $data['BDZ_id'],
			'Person_SurName' => $data['Person_SurName'],
			'Person_FirName' => $data['Person_FirName'],
			'Person_SecName' => $data['Person_SecName'],
			'Person_BirthDay' => $data['Person_BirthDay'],
			'Sex_Code' => $data['Sex_Code'],
			'SocStatus_Code' => $data['SocStatus_Code'],
			'Person_Snils' => $data['Person_Snils'],
			'Person_EdNum' => $data['Person_EdNum'],
			'DocumentType_Code' => $data['DocumentType_Code'],
			'Document_Ser' => $data['Document_Ser'],
			'Document_Num' => $data['Document_Num'],
			'Document_begDate' => $data['Document_begDate'],
			'UKLAdr_Code' => $data['UKLAdr_Code'],
			'UAddress_House' => $data['UAddress_House'],
			'UAddress_Flat' => $data['UAddress_Flat'],
			'PKLAdr_Code' => $data['PKLAdr_Code'],
			'PAddress_House' => $data['PAddress_House'],
			'PAddress_Flat' => $data['PAddress_Flat'],
			'OrgSMO_RegNomC' => $data['OrgSMO_RegNomC'],
			'OrgSMO_RegNomN' => $data['OrgSMO_RegNomN'],
			'OmsSprTerr_Code' => $data['OmsSprTerr_Code'],
			'Polis_Ser' => $data['Polis_Ser'],
			'Polis_Num' => $data['Polis_Num'],
			'Polis_begDate' => $data['Polis_begDate'],
			'Polis_endDate' => $data['Polis_endDate'],
			'Polis_planDate' => $data['Polis_planDate'],
			'Polis_changeDate' => $data['Polis_changeDate'],
			'PolisCloseCause_Code' => $data['PolisCloseCause_Code']
		];

		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) )
            return false;

        return $result->result('array');
	}

	/**
	 * @param $data
	 * @param $startDT
	 * @param $finalDT
	 * @return array
	 */
	public function savePolisQueue($data, $startDT, $finalDT)
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
				Polis_id := :Polis_id,
				OrgSMO_id := :OrgSmo_id,
				PolisQueue_setDT := :PolisQueue_setDT,
				PolisQueue_begDT := :PolisQueue_begDT,
				PolisQueue_endDT := :PolisQueue_endDT,
				PolisQueue_ErrorCode := :PolisQueue_ErrorCode,
				PolisQueue_ErrorMessage := :PolisQueue_ErrorMessage,
				pmUser_id := 1
			)
		";

		$this->beginTransaction();

		foreach ( $data as $record ) {
			$begDT = explode(' ', $startDT);
			$endDT = explode(' ', $finalDT);

			$queryParams = [
				'Polis_id' => $record['Polis_ID'],
				'OrgSmo_id' => (isset($record['OrgSmo_id']) ? $record['OrgSmo_id'] : NULL),
				'PolisQueue_begDT' => ConvertDateFormat($begDT[0]) . ' ' . $begDT[1],
				'PolisQueue_endDT' => ConvertDateFormat($endDT[0]) . ' ' . $endDT[1],
				'PolisQueue_setDT' => NULL,
				'PolisQueue_ErrorCode' => ($record['Error_Code'] > 0 ? $record['Error_Code'] : NULL),
				'PolisQueue_ErrorMessage' => (strlen($record['Error_Msg']) > 0 ? $record['Error_Msg'] : NULL)
			];

			if ( isset($record['polisSetDT']) && strlen($record['polisSetDT']) > 0 ) {
				$setDT = explode(' ', $record['polisSetDT']);
				$queryParams['PolisQueue_setDT'] = ConvertDateFormat($setDT[0]) . ' ' . $setDT[1];
			}

			$result = $this->db->query($query, $queryParams);

			if ( !is_object($result) ) {
                $response['success'] = false;
                $response['Error_Msg'] = 'Ошибка при выполнении запроса к базе данных (добавление идентификатора полиса в очередь)';
                break;
			}

            $saveResponse = $result->result('array');

			if(!is_array($saveResponse) || count($saveResponse) == 0) {
                $response['success'] = false;
                $response['Error_Msg'] = 'Ошибка при добавлении идентификатора полиса в очередь';
                break;
            }

            if (!empty($saveResponse[0]['Error_Msg'])) {
                $response['success'] = false;
                $response['Error_Msg'] = $saveResponse[0]['Error_Msg'];
                break;
            }
		}

		if ($response['success']) {
			$this->commitTransaction();
		} else {
			$this->rollbackTransaction();
		}

		return $response;
	}

	/**
	 * @param $data
	 * @return bool|mixed
	 */
	public function setPolisQueueStatus($data)
    {
		$query = "
		    select
		        Error_Code as \"Error_Code\",
		        Error_Message as \"Error_Msg\"
		    from p_PolisQueue_status
		    (
				PolisQueue_id := :PolisQueue_id,
				PolisQueue_IsLoad := :PolisQueue_IsLoad,
				PolisQueue_ErrorCode := :PolisQueue_ErrorCode,
				PolisQueue_ErrorMessage := :PolisQueue_ErrorMessage,
				pmUser_id := 1
			)
		";

		$queryParams = [
			'PolisQueue_id' => $data['PolisQueue_id'],
			'PolisQueue_IsLoad' => (!empty($data['PolisQueue_IsLoad']) ? $data['PolisQueue_IsLoad'] : NULL),
			'PolisQueue_ErrorCode' => (!empty($data['PolisQueue_ErrorCode']) ? $data['PolisQueue_ErrorCode'] : NULL),
			'PolisQueue_ErrorMessage' => (!empty($data['PolisQueue_ErrorMessage']) ? $data['PolisQueue_ErrorMessage'] : NULL)
		];

		$result = $this->db->query($query, $queryParams);

		if (! is_object($result) )
            return false;

        return $result->result('array');
    }

	/**
	 * @param $data
	 * @return bool|mixed
	 */
	public function setPersonCardQueueRecord($data) {
		$query = "
			select
			    Error_Code as \"Error_Code\",
			    Error_Message as \"Error_Msg\"
			from p_PersonCardQueue_status
			(
				PersonCardQueue_id := :PersonCardQueue_id,
				PersonCardQueue_Status := :PersonCardQueue_Status,
				pmUser_id := 1
            )
		";

		$queryParams = [
			'PersonCardQueue_id' => $data['PersonCardQueue_id'],
			'PersonCardQueue_Status' => $data['PersonCardQueue_Status']
		];

		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) )
			return false;

        return $result->result('array');
	}
}