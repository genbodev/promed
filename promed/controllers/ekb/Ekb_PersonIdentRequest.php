<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * @property Options_model opmodel
 * @property Person_model persmodel
 * @property PersonIdentRequest_model identmodel
 */
 
require_once(APPPATH.'controllers/PersonIdentRequest.php');

class Ekb_PersonIdentRequest extends PersonIdentRequest {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}


	/**
	*  Выполнение запроса на идентификацию пациента в базе данных застрахованных Республики Башкортостан
	*  Входящие данные: $_POST['Person_id']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования человека
	*/
	function doPersonIdentRequest() {
		$data = array();
		// Получаем сессионные переменные
		$data = array_merge($data, getSessionParams());
		
		$this->load->model("Options_model", "opmodel");
		$this->load->model("PersonIdentRequest_model", "identmodel");
		$globalOptions = $this->opmodel->getOptionsGlobals($data);
	
		$this->load->library('swPersonIdentEkb');
		$identObject = new swPersonIdentEkb(
			$this->config->item('IDENTIFY_SERVICE_URI')
		);

		$val  = array();

		$err = getInputParams($data, $this->inputRules['doPersonIdentRequest']);

		if ( !empty($err) ) {
			$this->ReturnError($err);
			return false;
		}

		$identDT = time();

		$response = $this->identmodel->getOrgSmoCode($data['OrgSmo_id']);
		if ( is_array($response) && count($response) > 0 && !empty($response[0]['OrgSmo_Code'])) {
			$data['OrgSmo_Code'] = $response[0]['OrgSmo_Code'];
		} else {
			$data['OrgSmo_Code'] = '';
		}

		$response = $this->identmodel->getKladrCode($data['KLArea_id'], $data['KLStreet_id']);
		if ( is_array($response) && count($response) > 0 && !empty($response[0]['Kladr_Code'])) {
			$data['Kladr_Code'] = $response[0]['Kladr_Code'];
		} else {
			$data['Kladr_Code'] = '';
		}
		$polis_type = '';
		if(!empty($data['PolisType_id'])){
			switch ($data['PolisType_id']) {
				case '1':
					$polis_type = '1';
					break;
				case '3':
					$polis_type = '2';
					break;
				case '4':
					$polis_type = '3';
					break;
			}
		}
		$birth_day = '';
		if(!empty($data['Person_Birthday'])){
			$date = new DateTime($data['Person_Birthday']);
			$birth_day = $date->format('Y-m-d');
		}

		if ( empty($data['Person_Surname']) ) {
			$data['Person_Surname'] = '-';
		}
		if ( empty($data['Person_Firname']) ) {
			$data['Person_Firname'] = '-';
		}
		if ( empty($data['Person_Secname']) ) {
			$data['Person_Secname'] = '-';
		}

		if (!empty($data['Person_SNILS']) && strlen($data['Person_SNILS']) == 11) {
			$data['Person_SNILS'] = substr($data['Person_SNILS'], 0, 3).'-'.substr($data['Person_SNILS'], 3, 3).'-'.substr($data['Person_SNILS'], 6, 3).' '.substr($data['Person_SNILS'], 9, 2);
		}

		if ( $data['Person_Surname'] == '-' && $data['Person_Firname'] == '-' ) {
			$this->ReturnError('Одно из полей "Фамилия", "Имя" должно быть заполнено');
			return false;
		}

		// @task https://redmine.swan.perm.ru/issues/111753
		if ( !empty($data['DocumentType_Code']) && $data['DocumentType_Code'] == 14 && !empty($data['Document_Ser']) && strlen($data['Document_Ser']) == 4 ) {
			$data['Document_Ser'] = substr($data['Document_Ser'], 0, 2) . ' ' . substr($data['Document_Ser'], -2);
		}

		$requestData = array(
			'query' => array(
				'login' => array(
					'user' => $globalOptions['globals']['ident_login'],
					'password' => $globalOptions['globals']['ident_password'],
				),
				'patient' => array(
					'date1' => date('Y-m-d'),
					'date2' => date('Y-m-d'),
					'fam' => mb_ucfirst(mb_strtolower($data['Person_Surname'])),
					'im' => mb_ucfirst(mb_strtolower($data['Person_Firname'])),
					'ot' => mb_ucfirst(mb_strtolower($data['Person_Secname'])),
					'w' => (string)$data['Sex_id'],
					'dr' => (string)$birth_day,
					'vpolis' => (string)$polis_type,
					'npolis' => (string)$data['Polis_Num'],
					'doctype' => (string)$data['DocumentType_Code'],
					'docser' => (string)$data['Document_Ser'],
					'docnum' => (string)$data['Document_Num'],
					'snils' => (string)$data['Person_SNILS'],
					'mr' => (string)$data['BAddress_Address'],
				)
			)
		);
		
		// Выполнение запроса к сервису БДЗ
		$requestResponse = $identObject->doPersonIdentRequest($requestData);
		
		if ( $requestResponse['success'] === false ) {
			echo json_return_errors($requestResponse['errorMsg']);
			return false;
		}

		// Полученные данные
		$personData = $requestResponse['identData'];
		// Если идентифицирован...
		if ( is_array($personData) ) {
			if (!empty($personData['errors']) || !empty($personData['data'])) {
				$val = $personData;
				if (!empty($personData['data']['SMO_Code'])) {
					// по коду смо получаем OrgSMO_id
					$data['OrgSMO_id'] = $this->identmodel->getFirstResultFromQuery("
						select top 1 OrgSmo_id from v_OrgSMO (nolock) where RIGHT(Orgsmo_f002smocod, 2) = :SMO and KLRgn_id = 66
					", array(
						'SMO' => intval($personData['data']['SMO_Code'])
					));
					if (!empty($data['OrgSMO_id'])) {
						$val['data']['OrgSMO_id'] = $data['OrgSMO_id'];
						$val['data']['PersonIdentState_id'] = 1;
						$val['data']['Person_identDT'] = time();
					}

					$OMSSprTerr_id = $this->identmodel->getFirstResultFromQuery("
						select OMSSprTerr_id from v_OMSSprTerr with(nolock) where KLRgn_id = 66
					");
					if (!empty($OMSSprTerr_id)) {
						$val['data']['OMSSprTerr_id'] = $OMSSprTerr_id;
					}
				}
				if (!empty($personData['data']['Lpu_Code'])) {
					$data['Lpu_Nick'] = $this->identmodel->getFirstResultFromQuery("
						select top 1 Lpu_Nick from v_Lpu (nolock) where Lpu_RegNomN2 = :LPU
					", array(
						'LPU' => intval($personData['data']['Lpu_Code'])
					));
					if (!empty($data['Lpu_Nick'])) {
						$val['data']['Lpu_Nick'] = $data['Lpu_Nick'];
					}
				}
				if (!empty($personData['data']['vpolis'])) {
					$polis_type = '';
					switch ($personData['data']['vpolis']) {
						case '1':
							$polis_type = '1';
							break;
						case '2':
							$polis_type = '3';
							break;
						case '3':
							$polis_type = '4';
							break;
					}
					$val['data']['PolisType_id'] = $polis_type;

					if (!empty($personData['data']['Polis_Num']) && $polis_type == 4) {
						$val['data']['Federal_Num'] = str_pad($personData['data']['Polis_Num'], 16, '0', STR_PAD_LEFT);
						unset($val['data']['Polis_Num']);
					}
				}
				if (!empty($personData['data']['fpolis'])) {
					$PolisFormType_id = $this->identmodel->getFirstResultFromQuery("
						select PolisFormType_id from v_PolisFormType with(nolock) where PolisFormType_Code = :PolisFormType_Code
					", array(
						'PolisFormType_Code' => $personData['data']['fpolis']
					));
					if (!empty($PolisFormType_id)) {
						$val['data']['PolisFormType_id'] = $PolisFormType_id;
					}
				}
			} else {
				$val['Error_Msg'] = 'Неверный ответ сервиса';
			}
		}
		else {
			$val['Error_Msg'] = 'Неверный ответ сервиса';
		}
		
		if (!empty($val['KLAdr_Index'])) {
			$val['RAddress_Name'] = $val['KLAdr_Index'] . (!empty($val['RAddress_Name'])?', '.$val['RAddress_Name']:'');
		}

		array_walk($val, 'ConvertFromWin1251ToUTF8');

		$this->ReturnData($val);

		return true;
	}
}
