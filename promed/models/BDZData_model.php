<?php
class BDZData_model extends CI_Model {
	/**
	 * BDZData_model constructor.
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * @return bool|mixed
	 */
	function getLpuAttachmentData() {
		$query = "
			select
				PCQ.PersonCardQueue_id,
				ISNULL(P.BDZ_id, 0) as BDZ_id,
				ISNULL(L.Lpu_RegNomC, 0) as Lpu_RegNomC,
				ISNULL(L.Lpu_RegNomN, 0) as Lpu_RegNomN,
				convert(varchar(10), PCQ.PersonCard_begDate, 104) as PersonCard_begDate,
				convert(varchar(10), PCQ.PersonCard_endDate, 104) as PersonCard_endDate,
				ISNULL(CCC.CardCloseCause_Code, 0) as CardCloseCause_Code
			from v_PersonCardQueue PCQ with (nolock)
				inner join v_Person P with (nolock) on P.Person_id = PCQ.Person_id
				inner join v_Lpu L with (nolock) on L.Lpu_id = PCQ.Lpu_id
				left join CardCloseCause CCC with (nolock) on CCC.CardCloseCause_id = PCQ.CardCloseCause_id
			where PCQ.PersonCardQueue_sendDT is null
				and PCQ.PersonCardQueue_recvDT is null
				-- Не нужны записи об откреплении при смене участка внутри одной ЛПУ
				and ISNULL(CCC.CardCloseCause_Code, 0) <> 4
				-- Только люди из БДЗ
				and P.BDZ_id is not null
			order by
				P.BDZ_id,
				PCQ.PersonCard_begDate
	";
		$result = $this->db->query($query);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @return array
	 */
	function getOrgSmoAccountData() {
		/*
		return array(
			array(
				'OrgSmo_id' => 153001,
				'Login' => 'URALAIL_BZI',
				'Password' => '6aec56a26ba4a7697f8925dd1ac0190ed775ffc2'
			)
		);
		*/
		return array(
			array(
				'OrgSmo_id' => NULL,
				'Login' => 'PKF_PKMIAC',
				'Password' => '8f20962cf18c731b8b4c422798945229f81beb15'
			)
		);
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
	 * @param $orgSmoId
	 * @return bool|mixed
	 */
	function getPolisQueueList($orgSmoId) {
		$filter = "";
		$queryParams = array();

		if ( !empty($orgSmoId) ) {
			$filter .= "and OrgSMO_id = :OrgSMO_id";
			$queryParams['OrgSMO_id'] = $orgSmoId;
		}

		$query = "
			select
				PolisQueue_id,
				Polis_id,
				OrgSMO_id
			from PolisQueue with (nolock)
			where PolisQueue_IsLoad is null
				and PolisQueue_ErrorCode is null
				" . $filter . "
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
	 * @return bool|mixed
	 */
	function getStartDT() {
		$query = "
			select
				convert(varchar(10), max(PolisQueue_endDT), 104) + ' ' + convert(varchar(10), max(PolisQueue_endDT), 108) as endDT
			from
				PolisQueue with (nolock)
		";
		$result = $this->db->query($query);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $BDZ_id
	 * @return bool|mixed
	 */
	function loadBDZData($BDZ_id) {
		$query = "exec xp_BDZData_load @LoadBDZ_id = :LoadBDZ_id";
		$result = $this->db->query($query, array('LoadBDZ_id' => !empty($BDZ_id) ? $BDZ_id : NULL));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @return bool|mixed
	 */
	function parseBDZData() {
		$query = "exec xp_BDZData_parse";
		$result = $this->db->query($query);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool|mixed
	 */
	function saveBDZDataRecord($data) {
		if ( !is_array($data) || count($data) == 0 || !(empty($data['Polis_endDate']) || $data['Polis_endDate']>=$data['Polis_begDate']) ) {
			return false;
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = null;

			exec p_BDZData_ins
				@BDZData_id = @Res output,
				@BDZ_id = :BDZ_id,
				@Person_SurName = :Person_SurName,
				@Person_FirName = :Person_FirName,
				@Person_SecName = :Person_SecName,
				@Person_BirthDay = :Person_BirthDay,
				@Sex_Code = :Sex_Code,
				@SocStatus_Code = :SocStatus_Code,
				@Person_Snils = :Person_Snils,
				@Person_EdNum = :Person_EdNum,
				@DocumentType_Code = :DocumentType_Code,
				@Document_Ser = :Document_Ser,
				@Document_Num = :Document_Num,
				@Document_begDate = :Document_begDate,
				@UKLAdr_Code = :UKLAdr_Code,
				@UAddress_House = :UAddress_House,
				@UAddress_Flat = :UAddress_Flat,
				@PKLAdr_Code = :PKLAdr_Code,
				@PAddress_House = :PAddress_House,
				@PAddress_Flat = :PAddress_Flat,
				@OrgSMO_RegNomC = :OrgSMO_RegNomC,
				@OrgSMO_RegNomN = :OrgSMO_RegNomN,
				@OmsSprTerr_Code = :OmsSprTerr_Code,
				@Polis_Ser = :Polis_Ser,
				@Polis_Num = :Polis_Num,
				@Polis_begDate = :Polis_begDate,
				@Polis_endDate = :Polis_endDate,
				@Polis_planDate = :Polis_planDate,
				@Polis_changeDate = :Polis_changeDate,
				@PolisCloseCause_Code = :PolisCloseCause_Code,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as BDZData_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
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
		);

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
	 * @param $startDT
	 * @param $finalDT
	 * @return array
	 */
	function savePolisQueue($data, $startDT, $finalDT) {
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
				@PolisQueue_begDT = :PolisQueue_begDT,
				@PolisQueue_endDT = :PolisQueue_endDT,
				@PolisQueue_ErrorCode = :PolisQueue_ErrorCode,
				@PolisQueue_ErrorMessage = :PolisQueue_ErrorMessage,
				@pmUser_id = 1,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as PolisQueue_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$this->db->trans_begin();

		foreach ( $data as $record ) {
			$begDT = explode(' ', $startDT);
			$endDT = explode(' ', $finalDT);

			$queryParams = array(
				'Polis_id' => $record['Polis_ID'],
				'OrgSmo_id' => (isset($record['OrgSmo_id']) ? $record['OrgSmo_id'] : NULL),
				'PolisQueue_begDT' => ConvertDateFormat($begDT[0]) . ' ' . $begDT[1],
				'PolisQueue_endDT' => ConvertDateFormat($endDT[0]) . ' ' . $endDT[1],
				'PolisQueue_setDT' => NULL,
				'PolisQueue_ErrorCode' => ($record['Error_Code'] > 0 ? $record['Error_Code'] : NULL),
				'PolisQueue_ErrorMessage' => (strlen($record['Error_Msg']) > 0 ? $record['Error_Msg'] : NULL)
			);

			if ( isset($record['polisSetDT']) && strlen($record['polisSetDT']) > 0 ) {
				$setDT = explode(' ', $record['polisSetDT']);
				$queryParams['PolisQueue_setDT'] = ConvertDateFormat($setDT[0]) . ' ' . $setDT[1];
			}

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
					$response['success'] = false;
					$response['Error_Msg'] = 'Ошибка при добавлении идентификатора полиса в очередь';
					break;
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

	/**
	 * @param $data
	 * @return bool|mixed
	 */
	function setPolisQueueStatus($data) {
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec p_PolisQueue_status
				@PolisQueue_id = :PolisQueue_id,
				@PolisQueue_IsLoad = :PolisQueue_IsLoad,
				@PolisQueue_ErrorCode = :PolisQueue_ErrorCode,
				@PolisQueue_ErrorMessage = :PolisQueue_ErrorMessage,
				@pmUser_id = 1,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'PolisQueue_id' => $data['PolisQueue_id'],
			'PolisQueue_IsLoad' => (!empty($data['PolisQueue_IsLoad']) ? $data['PolisQueue_IsLoad'] : NULL),
			'PolisQueue_ErrorCode' => (!empty($data['PolisQueue_ErrorCode']) ? $data['PolisQueue_ErrorCode'] : NULL),
			'PolisQueue_ErrorMessage' => (!empty($data['PolisQueue_ErrorMessage']) ? $data['PolisQueue_ErrorMessage'] : NULL)
		);

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
	 * @return bool|mixed
	 */
	function setPersonCardQueueRecord($data) {
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec p_PersonCardQueue_status
				@PersonCardQueue_id = :PersonCardQueue_id,
				@PersonCardQueue_Status = :PersonCardQueue_Status,
				@pmUser_id = 1,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'PersonCardQueue_id' => $data['PersonCardQueue_id'],
			'PersonCardQueue_Status' => $data['PersonCardQueue_Status']
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
?>