<?php
/**
 * Signatures_Model - модель для работы с подписями
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package		Polka
 * @access		public
 * @copyright	Copyright (c) 2010-2017 Swan Ltd.
 * @author		Dmitry Vlasenko
 */
class Signatures_Model extends swModel {
	/**
	 *	Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Список версий
	 */
	function loadSignaturesHistoryList($data) {
		$query = "
			SELECT
				SH.Signatures_id,
				SH.SignaturesHistory_id,
				SH.Signatures_Version,
				convert(varchar(10), SH.SignaturesHistory_insDT, 104) + ' ' + convert(varchar(5), SH.SignaturesHistory_insDT, 108) as SignaturesHistory_insDT,
				SU.PMUser_Name
			FROM
				v_SignaturesHistory SH WITH (nolock)
				INNER JOIN v_pmUserCache SU with (nolock) on SU.PMUser_id = SH.pmUser_insID
			WHERE
				SH.Signatures_id = :Signatures_id
			order by
				SignaturesHistory_insDT asc
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
	 * Получение данных для подписи
	 */
	function getDocData($data) {
		// формируем запрос набора полей для подписи
		switch($data['Doc_Type']) {
			case 'EvnMse':
				$query = "
					select
						EvnMse_id,
						EvnMse_pid,
						EvnMse_rid,
						Lpu_id,
						Server_id,
						PersonEvn_id,
						EvnMse_setDT,
						EvnMse_disDT,
						EvnMse_didDT,
						Person_id,
						Morbus_id,
						EvnStatus_id,
						EvnMse_statusDate,
						EvnMse_IsTransit,
						EvnPrescrMse_id,
						EvnMse_NumAct,
						Diag_id,
						Diag_sid,
						Diag_aid,
						HealthAbnorm_id,
						HealthAbnormDegree_id,
						CategoryLifeType_id,
						CategoryLifeDegreeType_id,
						InvalidGroupType_id,
						EvnMse_InvalidCause,
						EvnMse_InvalidPercent,
						EvnMse_ReExamDate,
						EvnMse_InvalidCauseDeni,
						EvnMse_SendStickDate,
						EvnMse_MedRecomm,
						EvnMse_ProfRecomm,
						MedService_id,
						MedServiceMedPersonal_id,
						EvnVK_id,
						InvalidRefuseType_id,
						InvalidCouseType_id
					from
						v_EvnMse (nolock)
					where
						EvnMse_id = :Doc_id
				";
				break;
			case 'EvnReceptOtv':
				$query = "
					select
                    	otv.ReceptOtov_id,
						otv.Person_id,
						otv.Person_Snils,
						otv.PrivilegeType_id,
						otv.Lpu_id,
						otv.MedPersonalRec_id,
						otv.Diag_id,
						otv.EvnRecept_Ser,
						otv.EvnRecept_Num,
						otv.EvnRecept_setDT,
						otv.ReceptFinance_id,
						otv.ReceptValid_id,
						otv.OrgFarmacy_id,
						otv.Drug_id,
						otv.EvnRecept_Kolvo,
						otv.EvnRecept_obrDate,
						otv.EvnRecept_otpDate,
						otv.EvnRecept_Price,
						otv.ReceptDelayType_id,
						otv.EvnRecept_id,
						otv.EvnRecept_Is7Noz,
						otv.DrugFinance_id,
						otv.WhsDocumentCostItemType_id,
						otv.ReceptStatusType_id,
						otv.Drug_cid,
						otv.ReceptOtov_IsKEK,
						otv.Polis_Ser,
						otv.Polis_Num,
						dus.DocumentUc_id,
						dus.DocumentUcStr_Price,
						dus.DocumentUcStr_PriceR,
						dus.DocumentUcStr_EdCount,
						dus.DocumentUcStr_Count,
						dus.DocumentUcStr_Sum,
						dus.DocumentUcStr_SumR,
						dus.DocumentUcStr_SumNds,
						dus.DocumentUcStr_SumNdsR,
						dus.DocumentUcStr_Ser,
						dus.DocumentUcStr_CertNum,
						dus.DocumentUcStr_CertDate,
						dus.DocumentUcStr_CertGodnDate,
						dus.DocumentUcStr_CertOrg,
						dus.DocumentUcStr_IsLab,
						dus.DrugLabResult_Name,
						dus.DocumentUcStr_RashCount,
						dus.DocumentUcStr_RegDate,
						dus.DocumentUcStr_RegPrice,
						dus.DocumentUcStr_godnDate,
						dus.DocumentUcStr_setDate,
						dus.DocumentUcStr_Decl,
						dus.DocumentUcStr_Barcod,
						dus.DocumentUcStr_CertNM,
						dus.DocumentUcStr_CertDM,
						dus.DocumentUcStr_NTU,
						dus.DocumentUcStr_NZU,
						dus.DocumentUcStr_Reason
					from v_ReceptOtov otv with (nolock)
						left join v_DocumentUcStr DUS with (nolock) on otv.ReceptOtov_id = dus.ReceptOtov_id
						left join v_DocumentUc du with (nolock) on du.DocumentUc_id = dus.DocumentUc_id
					where
						otv.EvnRecept_id = :Doc_id
				";
				break;
		}

		if (!empty($query)) {
			$result = $this->db->query($query, $data);
			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					$doc = $resp[0];
					foreach ($doc as $key => $value) {
						if ($value instanceof DateTime) {
							$doc[$key] = $value->format('Y-m-d H:i:s');
						}
					}
					return array('DocData' => json_encode($doc));
				}
			}
		}

		return array('Error_Msg' => 'Ошибка получения данных документа');
	}

	/**
	 * Получение хэша для подписи
	 */
	function getDocHash($data) {
		$docData = $this->getDocData($data);
		if (!empty($docData['Error_Msg'])) {
			return $docData;
		}

		$toHash = $docData['DocData'];
		$base64ToSign = base64_encode($toHash);

		$this->load->helper('openssl');
		// считаем хэш
		$cryptoProHash = getCryptCpHash($toHash, $data['SignedToken']);

		return array('Error_Msg' => '', 'Base64ToSign' => $base64ToSign, 'Hash' => $cryptoProHash);
	}

	/**
	 * Подписание документа
	 */
	function signDoc($data) {
		switch ($data['Doc_Type']) {
			case 'EvnMse':
				$SignObject = 'Signatures_id';
				$object = 'EvnMse';
				break;
			case 'EvnReceptOtv':
				$SignObject = 'Signatures_id';
				$object = 'EvnRecept';
				break;
		}

		if (empty($SignObject)) {
			return array('Error_Msg' => 'Ошибка сохранения подписи');
		}

		$queryParams = array(
			'Doc_id' => $data['Doc_id']
		);
		$query = "
			SELECT TOP 1
				S.Signatures_id,
				S.Signatures_Version
			FROM v_{$object} ESWR WITH (nolock)
			INNER JOIN v_Signatures S WITH (nolock) on ESWR.{$SignObject} = S.Signatures_id
			WHERE ESWR.{$object}_id = :Doc_id
		";
		$signatures = $this->getFirstRowFromQuery($query, $queryParams);
		$signatures['Signatures_id'] = isset($signatures['Signatures_id']) && $signatures['Signatures_id'] > 0 ? $signatures['Signatures_id'] : NULL;
		$signatures['Signatures_Version'] = isset($signatures['Signatures_Version']) && $signatures['Signatures_Version'] > 0 ? $signatures['Signatures_Version'] + 1 : 1;

		if (!empty($data['signType']) && $data['signType'] == 'cryptopro') {
			$hex = $data['SignedData'];
			// HEX надо развернуть, криптопро зачем то делает повёрнутую подпись %)
			$newhex = '';
			while(strlen($hex) > 0) {
				$newhex = substr($hex, 0, 2) . $newhex;
				$hex = substr($hex, 2);
			}
			$data['SignedData'] = base64_encode(pack("H*", $newhex));
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @Res = :Signatures_id;

			exec p_Signatures_" . ($signatures['Signatures_id'] > 0 ? "upd" : "ins") . "
				@Signatures_id = @Res output,
				@Signatures_Version = :Signatures_Version,
				@SignaturesStatus_id = :SignaturesStatus_id,
				@Signatures_Hash = :Signatures_Hash,
				@Signatures_SignedData = :Signatures_SignedData,
				@Signatures_Token = :Signatures_Token,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as Signatures_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'Signatures_id' => $signatures['Signatures_id'],
			'Signatures_Version' => $signatures['Signatures_Version'],
			'SignaturesStatus_id' => 1,
			'Signatures_Hash' => $data['Hash'],
			'Signatures_SignedData' => $data['SignedData'],
			'Signatures_Token' => $data['SignedToken'],
			'pmUser_id' => $data['pmUser_id']
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (!empty($resp[0]['Signatures_id'])) {
				if (empty($signatures['Signatures_id'])) {
					$query = "update {$object} with (rowlock) set {$SignObject} = :Signatures_id where {$object}_id = :Doc_id";
					$this->db->query($query, array(
						'Doc_id' => $data['Doc_id'],
						'Signatures_id' => $resp[0]['Signatures_id']
					));
				}
				$signatures['Signatures_id'] = $resp[0]['Signatures_id'];
			} else {
				return false;
			}
		} else {
			return false;
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @Res = :SignaturesHistory_id;

			exec p_SignaturesHistory_ins
				@SignaturesHistory_id = @Res output,
				@Signatures_id = :Signatures_id,
				@Signatures_Version = :Signatures_Version,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as SignaturesHistory_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'SignaturesHistory_id' => NULL,
			'Signatures_id' => $signatures['Signatures_id'],
			'Signatures_Version' => $signatures['Signatures_Version'],
			'pmUser_id' => $data['pmUser_id']
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$resp = $result->result('array');

			if (!empty($resp[0])) {
				$resp[0]['Signatures_id'] = $signatures['Signatures_id'];
			}

			return $resp;
		}
		else {
			return false;
		}
	}

	/**
	 * Обновление статуса подписи
	 */
	function setSignaturesStatus($data) {
		$query = "update Signatures with (rowlock) set SignaturesStatus_id = :SignaturesStatus_id where Signatures_id = :Signatures_id";
		$this->db->query($query, array('Signatures_id' => $data['Signatures_id'], 'SignaturesStatus_id' => $data['SignaturesStatus_id']));

		return array('success' => true, 'Error_Msg' => '');
	}

	/**
	 * Верификация подписи документа
	 */
	function verifySign($data) {
		$docData = $this->getDocData($data);
		if (!empty($docData['Error_Msg'])) {
			return $docData;
		}

		switch ($data['Doc_Type']) {
			case 'EvnMse':
				$SignObject = 'Signatures_id';
				$object = 'EvnMse';
				break;
			case 'EvnReceptOtv':
				$SignObject = 'Signatures_id';
				$object = 'EvnRecept';
				break;
		}

		if (empty($SignObject)) {
			return array('Error_Msg' => 'Ошибка верификации подписи');
		}

		$queryParams = array(
			'Doc_id' => $data['Doc_id']
		);
		$resp = $this->queryResult("
			SELECT TOP 1
				S.Signatures_id,
				S.Signatures_Version,
				S.Signatures_Hash,
				S.Signatures_SignedData,
				S.Signatures_Token
			FROM v_{$object} ESWR WITH (nolock)
			INNER JOIN v_Signatures S WITH (nolock) on ESWR.{$SignObject} = S.Signatures_id
			WHERE ESWR.{$object}_id = :Doc_id
		", $queryParams);

		if (empty($resp[0]['Signatures_id'])) {
			return array('Error_Msg' => '', 'valid' => 0); // не подписывался
		}

		// получили данные, проверим подпись с помощью OpenSSL.
		// с помощью OpenSSL:
		$this->load->helper('openssl');
		$verified = checkSignature($resp[0]['Signatures_Token'], $docData, base64_decode($resp[0]['Signatures_SignedData']));

		// обновляем статус
		$this->setSignaturesStatus(array(
			'Signatures_id' => $resp[0]['Signatures_id'],
			'SignaturesStatus_id' => $verified ? 1 : 3
		));

		return array('Error_Msg' => '', 'valid' => $verified ? 2 : 1);
	}
}
?>