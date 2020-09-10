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
class Signatures_Model extends SwPgModel {
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
				SH.Signatures_id as \"Signatures_id\",
				SH.SignaturesHistory_id as \"SignaturesHistory_id\",
				SH.Signatures_Version as \"Signatures_Version\",
				to_char (SH.SignaturesHistory_insDT, 'dd.mm.yyyy hh24:mm') as \"SignaturesHistory_insDT\",
				SU.PMUser_Name as \"PMUser_Name\"
			FROM
				v_SignaturesHistory SH
				INNER JOIN v_pmUserCache SU on SU.PMUser_id = SH.pmUser_insID
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
						EvnMse_id as \"EvnMse_id\",
						EvnMse_pid as \"EvnMse_pid\",
						EvnMse_rid as \"EvnMse_rid\",
						Lpu_id as \"Lpu_id\",
						Server_id as \"Server_id\",
						PersonEvn_id as \"PersonEvn_id\",
						EvnMse_setDT as \"EvnMse_setDT\",
						EvnMse_disDT as \"EvnMse_disDT\",
						EvnMse_didDT as \"EvnMse_didDT\",
						Person_id as \"Person_id\",
						Morbus_id as \"Morbus_id\",
						EvnStatus_id as \"EvnStatus_id\",
						EvnMse_statusDate as \"EvnMse_statusDate\",
						EvnMse_IsTransit as \"EvnMse_IsTransit\",
						EvnPrescrMse_id as \"EvnPrescrMse_id\",
						EvnMse_NumAct as \"EvnMse_NumAct\",
						Diag_id as \"Diag_id\",
						Diag_sid as \"Diag_sid\",
						Diag_aid as \"Diag_aid\",
						HealthAbnorm_id as \"HealthAbnorm_id\",
						HealthAbnormDegree_id as \"HealthAbnormDegree_id\",
						CategoryLifeType_id as \"CategoryLifeType_id\",
						CategoryLifeDegreeType_id as \"CategoryLifeDegreeType_id\",
						InvalidGroupType_id as \"InvalidGroupType_id\",
						EvnMse_InvalidCause as \"EvnMse_InvalidCause\",
						EvnMse_InvalidPercent as \"EvnMse_InvalidPercent\",
						EvnMse_ReExamDate as \"EvnMse_ReExamDate\",
						EvnMse_InvalidCauseDeni as \"EvnMse_InvalidCauseDeni\",
						EvnMse_SendStickDate as \"EvnMse_SendStickDate\",
						EvnMse_MedRecomm as \"EvnMse_MedRecomm\",
						EvnMse_ProfRecomm as \"EvnMse_ProfRecomm\",
						MedService_id as \"MedService_id\",
						MedServiceMedPersonal_id as \"MedServiceMedPersonal_id\",
						EvnVK_id as \"EvnVK_id\",
						InvalidRefuseType_id as \"InvalidRefuseType_id\",
						InvalidCouseType_id as \"InvalidCouseType_id\"
					from
						v_EvnMse
					where
						EvnMse_id = :Doc_id
				";
				break;
			case 'EvnReceptOtv':
				$query = "
					select
                    	otv.ReceptOtov_id as \"ReceptOtov_id\",
						otv.Person_id as \"Person_id\",
						otv.Person_Snils as \"Person_Snils\",
						otv.PrivilegeType_id as \"PrivilegeType_id\",
						otv.Lpu_id as \"Lpu_id\",
						otv.MedPersonalRec_id as \"MedPersonalRec_id\",
						otv.Diag_id as \"Diag_id\",
						otv.EvnRecept_Ser as \"EvnRecept_Ser\",
						otv.EvnRecept_Num as \"EvnRecept_Num\",
						otv.EvnRecept_setDT as \"EvnRecept_setDT\",
						otv.ReceptFinance_id as \"ReceptFinance_id\",
						otv.ReceptValid_id as \"ReceptValid_id\",
						otv.OrgFarmacy_id as \"OrgFarmacy_id\",
						otv.Drug_id as \"Drug_id\",
						otv.EvnRecept_Kolvo as \"EvnRecept_Kolvo\",
						otv.EvnRecept_obrDate as \"EvnRecept_obrDate\",
						otv.EvnRecept_otpDate as \"EvnRecept_otpDate\",
						otv.EvnRecept_Price as \"EvnRecept_Price\",
						otv.ReceptDelayType_id as \"ReceptDelayType_id\",
						otv.EvnRecept_id as \"EvnRecept_id\",
						otv.EvnRecept_Is7Noz as \"EvnRecept_Is7Noz\",
						otv.DrugFinance_id as \"DrugFinance_id\",
						otv.WhsDocumentCostItemType_id as \"WhsDocumentCostItemType_id\",
						otv.ReceptStatusType_id as \"ReceptStatusType_id\",
						otv.Drug_cid as \"Drug_cid\",
						otv.ReceptOtov_IsKEK as \"ReceptOtov_IsKEK\",
						otv.Polis_Ser as \"Polis_Ser\",
						otv.Polis_Num as \"Polis_Num\",
						dus.DocumentUc_id as \"DocumentUc_id\",
						dus.DocumentUcStr_Price as \"DocumentUcStr_Price\",
						dus.DocumentUcStr_PriceR as \"DocumentUcStr_PriceR\",
						dus.DocumentUcStr_EdCount as \"DocumentUcStr_EdCount\",
						dus.DocumentUcStr_Count as \"DocumentUcStr_Count\",
						dus.DocumentUcStr_Sum as \"DocumentUcStr_Sum\",
						dus.DocumentUcStr_SumR as \"DocumentUcStr_SumR\",
						dus.DocumentUcStr_SumNds as \"DocumentUcStr_SumNds\",
						dus.DocumentUcStr_SumNdsR as \"DocumentUcStr_SumNdsR\",
						dus.DocumentUcStr_Ser as \"DocumentUcStr_Ser\",
						dus.DocumentUcStr_CertNum as \"DocumentUcStr_CertNum\",
						dus.DocumentUcStr_CertDate as \"DocumentUcStr_CertDate\",
						dus.DocumentUcStr_CertGodnDate as \"DocumentUcStr_CertGodnDate\",
						dus.DocumentUcStr_CertOrg as \"DocumentUcStr_CertOrg\",
						dus.DocumentUcStr_IsLab as \"DocumentUcStr_IsLab\",
						dus.DrugLabResult_Name as \"DrugLabResult_Name\",
						dus.DocumentUcStr_RashCount as \"DocumentUcStr_RashCount\",
						dus.DocumentUcStr_RegDate as \"DocumentUcStr_RegDate\",
						dus.DocumentUcStr_RegPrice as \"DocumentUcStr_RegPrice\",
						dus.DocumentUcStr_godnDate as \"DocumentUcStr_godnDate\",
						dus.DocumentUcStr_setDate as \"DocumentUcStr_setDate\",
						dus.DocumentUcStr_Decl as \"DocumentUcStr_Decl\",
						dus.DocumentUcStr_Barcod as \"DocumentUcStr_Barcod\",
						dus.DocumentUcStr_CertNM as \"DocumentUcStr_CertNM\",
						dus.DocumentUcStr_CertDM as \"DocumentUcStr_CertDM\",
						dus.DocumentUcStr_NTU as \"DocumentUcStr_NTU\",
						dus.DocumentUcStr_NZU as \"DocumentUcStr_NZU\",
						dus.DocumentUcStr_Reason as \"DocumentUcStr_Reason\"
					from v_ReceptOtov otv
						left join v_DocumentUcStr DUS on otv.ReceptOtov_id = dus.ReceptOtov_id
						left join v_DocumentUc du on du.DocumentUc_id = dus.DocumentUc_id
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
			SELECT
				S.Signatures_id as \"Signatures_id\",
				S.Signatures_Version as \"Signatures_Version\"
			FROM v_{$object} ESWR
			INNER JOIN v_Signatures S on ESWR.{$SignObject} = S.Signatures_id
			WHERE ESWR.{$object}_id = :Doc_id
			LIMIT 1
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
			select 
			    Signatures_id as \"Signatures_id\",
			    Error_Code as \"Error_Code\",
			    Error_Message as \"Error_Msg\"
			from p_Signatures_" . ($signatures['Signatures_id'] > 0 ? "upd" : "ins") . " (
				Signatures_id := :Signatures_id,
				Signatures_Version := :Signatures_Version,
				SignaturesStatus_id := :SignaturesStatus_id,
				Signatures_Hash := :Signatures_Hash,
				Signatures_SignedData := :Signatures_SignedData,
				Signatures_Token := :Signatures_Token,
				pmUser_id := :pmUser_id
				)
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
					$query = "update {$object} set {$SignObject} = :Signatures_id where {$object}_id = :Doc_id";
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
            select 
                SignaturesHistory_id as \"SignaturesHistory_id\",
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\"
			from p_SignaturesHistory_ins (
				SignaturesHistory_id := :SignaturesHistory_id,
				Signatures_id := :Signatures_id,
				Signatures_Version := :Signatures_Version,
				pmUser_id := :pmUser_id
				)
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
		$query = "update Signatures set SignaturesStatus_id = :SignaturesStatus_id where Signatures_id = :Signatures_id";
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
			SELECT
				S.Signatures_id as \"Signatures_id\",
				S.Signatures_Version as \"Signatures_Version\",
				S.Signatures_Hash as \"Signatures_Hash\",
				S.Signatures_SignedData as \"Signatures_SignedData\",
				S.Signatures_Token as \"Signatures_Token\"
			FROM v_{$object} ESWR
			INNER JOIN v_Signatures S on ESWR.{$SignObject} = S.Signatures_id
			WHERE ESWR.{$object}_id = :Doc_id
			LIMIT 1
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
