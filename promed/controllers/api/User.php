<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * User - контроллер API для работы с пользователями
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			API
 * @access			public
 * @copyright		Copyright (c) 2016 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			11.10.2016
 */

require(APPPATH.'libraries/SwREST_Controller.php');

class User extends SwREST_Controller {
	protected  $inputRules = array(
		'login' => array(
			array('field' => 'login', 'label' => 'Логин', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'password', 'label' => 'Пароль', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'apiKey', 'label' => 'Ключ', 'rules' => '', 'type' => 'string'),
		),
		'setCurrentLpu' => array(
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id')
		),
		'mGetMedstaffFactList' => array(
			array('field' => 'ARMType', 'label' => 'Тип арма', 'rules' => '', 'type' => 'string')
		),
		'getSessionParam' => array(
			array('field' => 'PHPSESSID', 'label' => 'Идентификатор сессии', 'rules' => 'required', 'type' => 'string')
		),
		'mSetCurrentARM' => array(
			array(
				'field' => 'MedStaffFact_id',
				'label' => 'Идентификатор врача',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedService_id',
				'label' => 'Служба',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_id',
				'label' => 'Отделение',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonal_id',
				'label' => 'Врач',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_id',
				'label' => 'ЛПУ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ARMType',
				'label' => 'Тип АРМа',
				'rules' => '',
				'type' => 'string'
		)
		),
		'mSetDefaultWorkPlace' => array(
			array('field' => 'ARMName', 'label' => 'Название АРМа', 'rules' => '', 'type' => 'string'),
			array('field' => 'ARMType', 'label' => 'Тип АРМа', 'rules' => '', 'type' => 'string'),
			array('field' => 'ARMForm', 'label' => 'Форма', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuSection_id', 'label' => 'Отделение', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedPersonal_id', 'label' => 'Врач', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedStaffFact_id', 'label' => 'Место работы', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedService_id', 'label' => 'Служба', 'rules' => '', 'type' => 'id'),
			array('field' => 'Org_id', 'label' => 'Организация', 'rules' => '', 'type' => 'id')
		),
	);

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	/**
	 * @param pmAuthUser $user
	 * @param null|string $apiKey
	 * @return bool
	 */
	function isAllowAccess($user, $apiKey = null) {
		$accessMap = array(
			//apiKey => groups
			'default' => array('APIUser'),
			'0d848827-92df-4eb3-bfde-cd7180b77394' => array('SCUser', 'SCUserMZ'),	//Ситуационный центр
		);

		if (empty($apiKey) || !isset($accessMap[$apiKey])) {
			$apiKey = 'default';
		}

		return $user->hasOneOfGroup($accessMap[$apiKey]);
	}

	/**
	 * Проверка логина
	 */
	function login_get() {
		$data = $this->ProcessInputData('login', null, false, false);

		$user = pmAuthUser::find($data['login']);
		if ($user && $result = $user->login($data['login'], $data['password'])) {
			if (is_array($result) && !empty($result['Error_Msg'])) {
				$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
				session_destroy();
			}
			if ($this->isAllowAccess($user, $data['apiKey'])) {
				$this->response(array(
					'error_code' => 0,
					'sess_id' => session_id()
				));
			} else {
				$this->response(array(
					'error_code' => 100,
					'error_msg' => 'Нет прав доступа'
				));
				session_destroy();
			}
		} else {
			$this->response(array(
				'error_code' => 100,
				'error_msg' => 'Неверный логин или пароль'
			));
		}
	}

	/**
	 * регенерация сессии и идентификатора сессии
	 */
	function regenerateSession($reload = false) {

		if(!isset($_SESSION['nonce']) || $reload) $_SESSION['nonce'] = md5(microtime(true));
		if(!isset($_SESSION['IPaddress']) || $reload) $_SESSION['IPaddress'] = $_SERVER['REMOTE_ADDR'];
		if(!isset($_SESSION['userAgent']) || $reload) $_SESSION['userAgent'] = $_SERVER['HTTP_USER_AGENT'];

		$_SESSION['OBSOLETE'] = true;
		$_SESSION['EXPIRES'] = time() + 60;

		session_regenerate_id(false); // перегенерим ID сессии, без ее удаления
		$newSession = session_id(); // получим новый ID сессии
		session_write_close(); // закроем обе сессии

		session_id($newSession); // присвоим идентификатор новой сессии
		session_start(); // запустим сессию

		unset($_SESSION['OBSOLETE']);
		unset($_SESSION['EXPIRES']);
	}

	/**
	 * Проверка логина
	 */
	function mlogin_post() {

		$this->regenerateSession(true); // перезапустим сессиию
		$data = $this->ProcessInputData('login', null, false, false);

		$user = pmAuthUser::find($data['login']);
		if ($user && $result = $user->login($data['login'], $data['password'])) {
			if (is_array($result) && !empty($result['Error_Msg'])) {
				$this->response(array(
					'error_code' => 100,
					'error_msg' => $result['Error_Msg']
				));
				session_destroy();
			}

			$resp = array(
				'error_code' => 0,
				'sess_id' => session_id(),
				'pmUser_id' => $user->pmuser_id,
				'pmUser_Login' => $user->login,
				'MedStaffFact_id' => null,
				'Person_Fio' => null,
				'LpuSection_id' => null,
				'LpuSection_Name' => null,
				'Lpu_Nick' => null,
				'Region_Code' => getRegionNumber(),
				'Region_Nick' => getRegionNick(),
			);

			if (!empty($user->settings)) {
				$settings = unserialize($user->settings);

				//echo '<pre>',print_r($settings['defaultARM']),'</pre>'; die();

				if (!empty($settings['defaultARM']['MedStaffFact_id'])) {

					$this->load->model('MedStaffFact_model');
					$info = $this->MedStaffFact_model->getMedStaffFactInfoForAPI(array(
						'MedStaffFact_id' => $settings['defaultARM']['MedStaffFact_id']
					));

					if (!empty($info[0]['MedStaffFact_id'])) {
						$resp['MedStaffFact_id'] = $info[0]['MedStaffFact_id'];
						$resp['MedPersonal_id'] = $info[0]['MedPersonal_id'];
						$resp['Person_Fio'] = $info[0]['Person_Fio'];
						$resp['LpuSection_id'] = $info[0]['LpuSection_id'];
						$resp['LpuSection_Name'] = $info[0]['LpuSection_Name'];
						$resp['Lpu_Nick'] = $info[0]['Lpu_Nick'];
						$resp['Lpu_id'] = $info[0]['Lpu_id'];

						$_SESSION['CurMedStaffFact_id'] = $info[0]['MedStaffFact_id'];
					}
				}
			}

			$this->response($resp);
		} else {
			$this->response(array(
				'error_code' => 100,
				'error_msg' => 'Неверный логин или пароль'
			));
		}
	}

	/**
	 * Получение списка мест работы
	 */
	function mGetMedstaffFactList_get() {
		$data = $this->ProcessInputData('mGetMedstaffFactList', array(), true);

		$this->checkAuth(array('securityLevel' => 'high'));

		$data['MedPersonal_id'] = $data['session']['medpersonal_id'];
		$this->load->model('User_model');
		$data['MedService_id'] = null;
		$result = $this->User_model->getUserMedStaffFactList($data);
		$result = $this->User_model->getArmsForMedStaffFactList($data, $result);

		$resp = array();
		foreach($result as $one) {
			if (!empty($data['ARMType']) && $one['ARMType'] != $data['ARMType']) {
				continue;
			}
			$resp[] = array(
				'MedStaffFact_id' => $one['MedStaffFact_id'],
				'Person_Fio' => $one['MedPersonal_FIO'],
				'LpuSection_id' => $one['LpuSection_id'],
				'LpuSection_Name' => $one['LpuSection_Name'],
				'Lpu_Nick' => $one['Lpu_Nick'],
				'ARMType' => $one['ARMType']
			);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение списка МО пользователя
	 */
	function MO_get() {
		$this->checkAuth();

		$session_data = getSessionParams();
		if (!empty($session_data['session']['orgs'])) {
			$this->load->model('LpuPassport_model');
			$this->response(array(
				'error_code' => 0,
				'data' => $this->LpuPassport_model->getLpuListForAPI(array(
					'Org_ids' => $session_data['session']['orgs']
				))
			));
		} else {
			$this->response(array(
				'error_code' => 0,
				'data' => array()
			));
		}
	}

	/**
	 * Установка текущего МО пользователя
	 */
	function ReviewMO_put() {
		$data = $this->ProcessInputData('setCurrentLpu', null, false, false);

		$this->checkAuth();

		$session_data = getSessionParams();
		if (!empty($session_data['session']['orgs'])) {
			$this->load->model('LpuPassport_model');
			$resp = $this->LpuPassport_model->getLpuListForAPI(array(
				'Org_ids' => $session_data['session']['orgs'],
				'Lpu_id' => $data['Lpu_id']
			));

			if (!empty($resp[0]['Lpu_id'])) {
				// устанавилваем текущую МО пользователя
				$_SESSION['lpu_id'] = $resp[0]['Lpu_id'];

				$this->response(array(
					'error_code' => 0
				));
			}
		}

		$this->response(array(
			'error_msg' => 'Пользователь не имеет права на просмотр данных по указанной МО',
			'error_code' => '6',
		));
	}

	/**
	 *  Получение параметров сессии
	 */
	function SessionParams_get() {
		$this->checkAuth();
		$data = $this->ProcessInputData('getSessionParam');
		if(!empty($data['PHPSESSID']) && $data['PHPSESSID'] === session_id()){
			$sp = getSessionParams();
			$response = array();
			$response['PMUser_id'] = $sp['session']['pmuser_id'];
			$response['PMUser_Name'] = $sp['session']['user'];
			$response['PMUser_Login'] = $sp['session']['login'];
			$response['MedPersonal_id'] = $sp['session']['medpersonal_id'];
			$response['Email'] = $sp['session']['email'];
			$response['Region_id'] = $sp['session']['region']['number'];
			$response['Lpu_id'] = $sp['session']['lpu_id'];
			$response['Org_id'] = $sp['session']['org_id'];
			$response['OrgType_SysNick'] = $sp['session']['orgtype'];
			$response['Language'] = $sp['session']['language'];
			if (havingGroup(array('SCUser','SCUserMZ'))) {
				$response['Groups'] = $sp['session']['groups'];
			}
			$this->response(array(
				'error_code' => '0',
				'data' => $response
			));
		} else {
			$this->response(array(
				'error_msg' => 'Неверный идентификатор сессии',
				'error_code' => '6',
			));
		}
	}

	/**
	 * Разлогирование
	 */
	function logout_get() {
		$this->checkAuth();

		if ( defined('USERAUDIT_LOGINS') && USERAUDIT_LOGINS ) {
			require_once(APPPATH . 'libraries/UserAudit.php');
			UserAudit::Logout(session_id());
		}
		session_destroy();

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 * Сохранение АРМа по умолчанию
	 */
	function mSetDefaultWorkPlace_post() {

		$this->checkAuth(array('securityLevel' => 'high'));

		$data = $this->ProcessInputData('mSetDefaultWorkPlace', null, true);
		if ( $data === false ) return false;


		if (!empty($data['session']) && !empty($data['session']['medpersonal_id'])) {
			$data['MedPersonal_id'] = $data['session']['medpersonal_id'];
			$data['ARMType'] = "common";
		}

		$selectedArm = $this->getArmData($data);
		if (empty($selectedArm)) $this->response(array('success' => false,'error_code' => 6,'Error_Msg' => 'Не удалось найти рабочее место пользователя.'));

		$user = pmAuthUser::find($_SESSION['login']);

		if (!$user) $this->response(array('success' => false,'error_code' => 6,'Error_Msg' => 'Не удалось найти пользователя.'));
		else {

			$options = @unserialize($_SESSION['settings']);

			// Массив настроек
			$options['defaultARM'] = $selectedArm[0];
			$options['defaultARM']['Lpu_id'] = $_SESSION['lpu_id'];

			// Сохраняем-пересохраняем настройки
			$user->settings = @serialize($options);
			$_SESSION['settings'] = $user->settings;

			$user->post();
			$this->response(array('success' => true,'error_code' => 0, 'data' => array(
				'MedStaffFact_id' => !empty($selectedArm[0]['MedStaffFact_id']) ? $selectedArm[0]['MedStaffFact_id'] : "",
				'Person_Fio' =>  !empty($selectedArm[0]['MedPersonal_FIO']) ? $selectedArm[0]['MedPersonal_FIO'] : "",
				'LpuSection_id' =>  !empty($selectedArm[0]['LpuSection_id']) ? $selectedArm[0]['LpuSection_id'] : "",
				'LpuSection_Name' =>  !empty($selectedArm[0]['LpuSection_Name']) ? $selectedArm[0]['LpuSection_Name'] : "",
				'Lpu_Nick' =>  !empty($selectedArm[0]['Lpu_Nick']) ? $selectedArm[0]['Lpu_Nick'] : "",
				'MedPersonal_id' =>  !empty($selectedArm[0]['MedPersonal_id']) ? $selectedArm[0]['MedPersonal_id'] : "",
			)));
		}
	}

	/**
	 * получение данных по арму
	 */
	function getArmData($data) {

		$this->load->database();
		$this->load->model("User_model");

		$res = $this->User_model->getUserMedStaffFactList($data); // фильтруем места работы по MedStaffFact_id / MedService_id с целью найти только выбранное место работы
		$res = $this->User_model->getArmsForMedStaffFactList($data, $res); // получаем армы для мест работы + групп пользователя

		if (!empty($res)) {

			array_walk($res, 'ConvertFromWin1251ToUTF8');

			// оставляем только АРМ выбранного типа
			foreach ($res as $key => $record) { if ($record['ARMType'] !== strtolower($data['ARMType'])) unset($res[$key]); }

			$res = array_values($res); // ресетим нумерацию, т.к. могла нарушиться после unset'ов
			return $res;
		};

		return array();
	}

	/**
	 * Cохранение текущего АРМа (места работы врача ) в сессии
	 */
	function mSetCurrentARM_post() {

		$this->checkAuth(array('securityLevel' => 'high'));

		$data = $this->ProcessInputData('mSetCurrentARM', null, true);
		$data['lpu'] = null;

		$data['MedPersonal_id'] = $_SESSION['medpersonal_id']; // Врача всегда берем из сессии
		$data['Lpu_id'] = (empty($data['Lpu_id'])) ? $_SESSION['lpu_id'] : $data['Lpu_id'];

		if ($data['Lpu_id'] != $_SESSION['lpu_id']) { // Поменялось ЛПУ

			$result = $this->changeLpu($data);
			if ($result !== false) $data['lpu'] = $result;
		}

		$curArm = $this->getArmData($data);
		if (!empty($curArm)) {
			$_SESSION['CurARM'] = $curArm[0];
			if (!empty($data['MedStaffFact_id'])) {
				$data['MedStaffFact_id'] = preg_replace('/[^0-9]*/', '', $data['MedStaffFact_id']);
				$_SESSION['CurMedStaffFact_id'] = !empty($data['MedStaffFact_id']) ? $data['MedStaffFact_id'] : null;
			}
			$this->response(array('success' => true,'error_code' => 0,'data' => $curArm[0]));
		}

		$this->response(array('success' => false,'error_code' => 6,'Error_Msg' => 'Ошибка выбора места работы'));
	}

	/**
	 * changeLpu
	 */
	function changeLpu($data) {

		if (!isset($data['Lpu_id'])) return false;

		$this->load->model("User_model", "dbmodel");
		$this->load->model("Org_model", "Org_model");

		$info = $this->dbmodel->getCurrentLpuData($data);

		if ( isset($info[0])
			&& (
				havingOrg($info[0]['Org_id'])
				|| isSuperadmin()
				|| havingGroup('RosZdrNadzorView')
				|| havingGroup('MedPersView')
				|| havingGroup('OuzUser')
				|| havingGroup('OuzAdmin')
				|| havingGroup('OuzChief')
				|| havingGroup('OuzSpec')
				|| havingGroup('OuzSpecMPC'))
		) {

			$_SESSION['lpu_id'] = $data['Lpu_id'];
			$_SESSION['server_id'] = $data['Lpu_id'];
			$_SESSION['org_id'] = $info[0]['Org_id'];
			$_SESSION['Org_Name'] = $info[0]['Org_Name'];

			$orgtype = $this->Org_model->getOrgType(array('Org_id' => $info[0]['Org_id']));

			if (!empty($orgtype['OrgType_SysNick'])) {
				$_SESSION['orgtype'] = $orgtype['OrgType_SysNick'];
				$_SESSION['orgtype_id'] = $orgtype['OrgType_id'];
			} else {
				$_SESSION['orgtype'] = '';
				$_SESSION['orgtype_id'] = null;
		}

			$_SESSION['isMedStatUser'] = $this->dbmodel->isMedStatUser();
			$_SESSION['isPathoMorphoUser'] = $this->dbmodel->isPathoMorphoUser();
			$_SESSION['linkedLpuIdList'] = $this->Org_model->getLinkedLpuIdList(array('Lpu_id' => $data['Lpu_id']));
			$_SESSION['lpuIsTransit'] = $this->Org_model->getLpuIsTransit(array('Lpu_id' => $data['Lpu_id']));

			$val['success'] = true;
			$val['Lpu_id'] = $_SESSION['lpu_id'];
			$val['Org_id'] = $_SESSION['org_id'];
			$val['pmuser_id'] = $_SESSION['pmuser_id'];
			$val['linkedLpuIdList'] = $_SESSION['linkedLpuIdList'];
			$val['lpuIsTransit'] = $_SESSION['lpuIsTransit'];

			$val['Lpu_Nick'] = $info[0]['Lpu_Nick'];
			$val['Lpu_SysNick'] = $info[0]['Lpu_SysNick'];
			$val['Lpu_Name'] = $info[0]['Lpu_Name'];
			$val['Lpu_Email'] = $info[0]['Lpu_Email'];
			$val["LpuLevel_id"] = $info[0]['LpuLevel_id'];
			$val["LpuLevel_Code"] = $info[0]['LpuLevel_Code'];
			$val["Lpu_IsDMS"] = $info[0]['Lpu_IsDMS'];

			// Здесь выбрать medstafffact_id (или несклько для этого врача)
			// нужна для того чтобы выбрать места работы согласно выбранному ЛПУ
			$data['MedPersonal_id'] = $_SESSION['medpersonal_id'];
			$ms = $this->dbmodel->getMedStaffFact($data);

			$_SESSION['MedStaffFact'] = array();

			if (is_array($ms)) {
				foreach ($ms as $i => $row) { $_SESSION['MedStaffFact'][$i] = $row['MedStaffFact_id']; }
		}

			if (isset($_SESSION['MedStaffFact'])) $val['medstafffact'] = $_SESSION['MedStaffFact'];
			else $val['medstafffact'] = array();

			//Обновляется расчет прав доступа при смене ЛПУ
			$this->load->model('AccessRights_model', 'armodel');
			$_SESSION['access_rights'] = $this->armodel->getAccessRightsForUser(
				array(
					'pmUser_id' => $_SESSION['pmuser_id'],
					'MedPersonal_id' => $_SESSION['medpersonal_id'],
					'Lpus' => array($_SESSION['lpu_id']),
					'UserGroups' => explode('|', $_SESSION['groups']
				)
			));

			return $val;

		} else return false;
	}
}