<?php defined('BASEPATH') or die ('No direct script access allowed');

/**
 * Модель отправки данных в ЛИС
 *
 * Kukuzapa - forever
 */

class Kz_LIS_model extends swModel
{
	function __construct() {
		parent::__construct();

		set_time_limit(86400);

		ini_set("default_socket_timeout", 600);

		$this->load->library('textlog', array('file'=>'LIS_'.date('Y-m-d').'.log'));

		$this->_config = $this->config->item('LIS');
	}

	/**
	 * Выполнение запросов к сервису и обработка ошибок, которые возвращает сервис
	 */
	function exec($method, $type = 'get', $data = null) {
		$this->load->library('swServiceKZ', $this->config->item('LIS'), 'swServiceLIS');
		$this->textlog->add("exec method: $method, type: $type, data: ".print_r($data,true));

		$auth_result = $this->swServiceLIS->getAuthResult();
		if (empty($auth_result)) throw new Exception('Ошибка связи с сервисом');
		if (empty($auth_result->responseCode) || $auth_result->responseCode != 200) 
			throw new Exception(empty($auth_result->error)?'Ошибка авторизации, нет ответа от сервиса':$auth_result->error);
		$token = 'Token '.$auth_result->token;

		$result = $this->swServiceLIS->data($method, $type, $data, [
			"Content-Type: application/json; charset=utf-8",
			"Cache-Control: no-cache",
			"Pragma: no-cache",
			"Accept-Charset: UTF-8",
			"Accept: application/json",
			"Authorization: ".$token
		]);
		$this->textlog->add("result: ".print_r($result,true));
		if (is_object($result) && !empty($result->Message)) {
			$result = array(
				'success' => false,
				'errorMsg' => 'Ошибка в работе сервиса: '.$result->Message
			);
		}
		if (is_object($result) && !empty($result->ExceptionMessage)) {
			$result = array(
				'success' => false,
				'errorMsg' => 'Ошибка в работе сервиса: '.$result->ExceptionMessage
			);
		}
		return $result;
	}

	function sendDirectionToLis() {

		$this->load->model('EvnDirectionCVI_model');

		$result = $this->EvnDirectionCVI_model->load('sendDirectionToLis');

		foreach ($result as $res) {
			$params = array(
				'numm' => $res['EvnDirectionCVI_RegNumber'],
				'date' => !empty($res['date'])?$res['date']:null,
				'name_sample' => $res['CVIBiomaterial_id'],
				'work_place' => $res['EvnDirectionCVILink_WorkPlace'],
				'type_order' => $res['CVIOrderType_id'],
				'SenderMoID' => $res['SenderMoID'],
				'ReceiverMoID' => $res['ReceiverMoID'],
				'personId' => ($res['KLCountry_id'] == 398)?$res['personId']:null,
				'specialistId' => $res['specialistId'],
				'address_fact' => $res['EvnDirectionCVILink_Address'],
				'contact' => $res['EvnDirectionCVILink_Phone'],
				'survey_status' => $res['CVISampleStatus_id'],
				'date_selection' => $res['date_selection'],
				'status_of_patient' => $res['CVIPurposeSurvey_id'],
				'covid_simptom' => ($res['EvnDirectionCVILink_IsSymptom'] == '2')?'1':'0',
				'tel_specialist' => $res['EvnDirectionCVILink_PhonePersonal']
			);

			switch ($res['CVIPurposeSurvey_id']) {
				case 1: $params['epid_indications'] = $res['CVIStatus_Code']; break;
				case 2: $params['prevention_goal'] = $res['CVIStatus_Code']; break;
				case 3: $params['epid_surveillance'] = $res['CVIStatus_Code']; break;
			}

			if ($res['KLCountry_id'] != 398) {
				$params['noresident'] = array(
					'doc_num' => $res['Document_Num'],
					'fio' => $res['fio'],
					'sex' => $res['Sex_id'],
					'birthday' => $res['Person_BirthDay'],
					'age' => (string)$res['age'],
					'address' => $res['EvnDirectionCVILink_Address']
				);
			}
			
			$params = json_encode($params);
			$lis_result = $this->exec('createDoc', 'POST', $params);

			if (!empty($lis_result->documentID) && !empty($lis_result->dataID)) {
				$this->db->query("
					update r101.EvnDirectionCVILink
					set 
						EvnDirectionCVILink_lisIsSuccess = 2,
						EvnDirectionCVILink_lisID = ?,
						EvnDirectionCVILink_lisNum = ?
					where EvnDirectionCVILink_id = ?
				", [$lis_result->documentID, $lis_result->dataID, $res['EvnDirectionCVILink_id']]);
			} else {
				$this->db->query("
					update r101.EvnDirectionCVILink
					set EvnDirectionCVILink_lisIsSuccess = 1					
					where EvnDirectionCVILink_id = ?
				", [$res['EvnDirectionCVILink_id']]);
			}
		}

		return ['success' => true];
	}
}