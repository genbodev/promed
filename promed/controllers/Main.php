<?php

/**
 * main - контроллер, загружаемый по умолчанию на стартовой странице
 * а также функции логина и логаута
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      All
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       SWAN быдлокодерс (с)
 * @version      23.07.2009
 * @property 		Mongo_db mongo_db
 * @property 		User_model usermodel
 */
class Main extends CI_Controller {

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->usePostgre = defined('USE_POSTGRESQL') && USE_POSTGRESQL;
		$this->usePostgreLis = defined('USE_POSTGRESQL_LIS') && USE_POSTGRESQL_LIS;
		$this->usePostgreRegistry = defined('USE_POSTGRESQL_REGISTRY') && USE_POSTGRESQL_REGISTRY;
		
		//$this->load->database();
		$this->lang->load('common');
		$lang = isset($_SESSION['lang'])?$_SESSION['lang']:'ru';
		$this->lang->load('portal', $lang);

		if (isset($_REQUEST['SAMLResponse'])) {
			// пришёл ответ на разлогинивание от ИА ЕГИСЗ
			$esia_error = null;
			if (isset($_SESSION['esia_error'])) {
				$esia_error = $_SESSION['esia_error'];
			}
			session_destroy();
			session_start();
			if (!empty($esia_error)) {
				$_SESSION['esia_error'] = $esia_error;
			}
			header("Location: /?c=portal&m=promed", TRUE, 302);
			die();
		}
	}

	/**
	 * Ремапинг
	 */
	function _remap( $method ) {
		if ( ( isset($_REQUEST['m'])) && ('test' == $_REQUEST['m']) ) {
			$this->test();
		}
		elseif ( ( isset($_REQUEST['m'])) && ('Login' == $_REQUEST['m']) ) {
			$this->Login();
		}
		elseif ( ( isset($_REQUEST['m'])) && ('getToken' == $_REQUEST['m']) ) {
			$this->getToken();
		}
		elseif ( ( isset($_REQUEST['m'])) && ('Logout' == $_REQUEST['m']) ) {
			$this->Logout();
		}
		elseif ( ( isset($_REQUEST['m'])) && ('LogoutEMIAS' == $_REQUEST['m']) ) {
			$this->LogoutLoginEMIAS();
		}
		elseif ( ( isset($_REQUEST['m'])) && ('LogoutWithError' == $_REQUEST['m']) ) {
			$this->LogoutWithError();
		} else {
			if ( (!isset($_SESSION['login'])) || (( isset($_REQUEST['method'])) && ('Logon' == $_REQUEST['method'])) ) {
				if ( ( isset($_REQUEST['method'])) && ('Logon' == $_REQUEST['method']) ) {
					$this->IsLogon();
				} else {
					if ( isset($_REQUEST['method']) ) {
						checkLogin();
					} else {
						// Выводим форму логирования (логично же)
						$this->loginview();
					}
				}
			} else {
				$this->$method();
			}
		}
	}

	/**
	 * Получение нового токена
	 */
	function getToken() {
		if ( (!empty($_REQUEST['login']) && (!empty($_REQUEST['password'])) ) ) {
			$user = pmAuthUser::find($_REQUEST['login']);
			if ( $user && $result = $user->login($_REQUEST['login'], $_REQUEST['password']) ) {
				if ( is_array($result) && !empty($result['Error_Msg']) ) {
					session_destroy();
					die(json_encode(array('success' => false, 'Error_Msg' => $result['Error_Msg'])));
				} else {
					$swtoken = sha1($_REQUEST['login'] . rand(10000, 99999));
					$swtoken_enddate = date('d.m.Y', time() + 3 * 24 * 60 * 60);
					$_SESSION['swtoken'] = $swtoken;
					$_SESSION['swtoken_enddate'] = $swtoken_enddate;
					$user->swtoken = $swtoken;
					$user->swtoken_enddate = $swtoken_enddate;
					$user->post();
					die(json_encode(array('success' => true, 'swtoken' => $_SESSION['swtoken'], 'swtoken_enddate' => $_SESSION['swtoken_enddate'], 'Error_Msg' => '')));
				}
			}

			die(json_encode(array('success' => false, 'Error_Msg' => 'Invalid login data')));
		}

		die(json_encode(array('success' => false, 'Error_Msg' => 'Empty login data')));
	}

	/**
	 * Страница логина
	 */
	function loginview() {
		if ( isset( $_GET['from'] ) && $_GET['from'] == 'promed' ) {
			header( "Location: /?c=portal&from=promed" );
		} else {
			header( "Location: /?c=portal" );
		}
	}

	/**
	 * Главная страница
	 */
	function index() {
		if ( !isset($_SESSION['login']) ) {
			// тут перекидываем на форму логина
		} else
		if ( !isset( $_REQUEST['method'] ) ) {
			header("Location: /?c=promed");
		}
		header("Location: /?c=portal");
	}

	/**
	 * Обновляет запись пользователе в монгоДБ при логине
	 * Используется при проверке логина
	 */
	function setMongoUserLoginFail($login, $userLoginFailExists, $last_fail_conn_dt, $last_fail_conn_count) {

		if ($userLoginFailExists){
			$this->mongo_db->where(array('_id' => $login))->update('users_login_fail', array('last_fail_conn_dt' => $last_fail_conn_dt, 'last_fail_conn_count' => $last_fail_conn_count));
		} else {
			$this->mongo_db->insert('users_login_fail', array('_id' => $login, 'last_fail_conn_dt' => $last_fail_conn_dt, 'last_fail_conn_count' => $last_fail_conn_count));
		}
	}

	/**
	 * Проверка логина
	 */
	function IsLogon() {
		$data = $_POST;
		$val = array();

		$this->load->database();
		$this->load->model("Options_model");
		$esiaOnly = $this->Options_model->checkEsiaOnly();
		if ($esiaOnly) {
			echo json_encode(array('Error_Msg' => 'Разрешена авторизация только через ЕСИА'));
			return false;
		}

		if ( (!empty($data['login']) && (!empty($data['psw'])) ) ) {
			$user = pmAuthUser::find($data['login']);

			$this->load->database();
			$this->load->model( "User_model", "usermodel" );
			switch (checkMongoDb()) {
				case 'mongo':
					$this->load->library('swMongodb', null, 'mongo_db');
					break;
				case 'mongodb':
					$this->load->library('swMongodbPHP7', null, 'mongo_db');
					break;
			}
			$this->load->helper('MongoDB');

			//определяем организации пользователя
			$org_list = array();
			if ($user && is_array($user->org)){
				foreach ($user->org as $k => $v) {
					if (!empty($v['org_id'])){
						array_push($org_list, $v['org_id']);
					}
				}
			}

			//Проверяем количество неудачных попыток входа если хотя бы у одной организации стоит требование проверки
			$org_list = implode(',', $org_list);

			//Определяем требуется ли проверка для текущего пользователя
			$checkFailLoginCounter = $this->usermodel->getCheckFailLoginCounter();

			$checkFailLogin = !empty($checkFailLoginCounter) && $checkFailLoginCounter === '1';

			$str = '';
			$blockTimeFailLogin = '';
			$countBadFailLogin = '';

			if ($checkFailLogin) {
				$blockTimeFailLogin = $this->usermodel->getBlockTimeFailLogin();
				$countBadFailLogin = $this->usermodel->getCountBadFailLogin();
				$str = 'byla_zablokirovana_v_svyazi_s_ogranichennym_kolichestvom_neudachnyh_popytok_avtorizacii_za_opredelennyj_promezhutok_vremeni_Vasha_uchetnaya_zapis_budet_avtomaticheski_razblokirovana_cherez';
			}

			//Тянем из монго запись для текущего пользователя
			$mongoUserLoginFail = $this->mongo_db->get_where('users_login_fail', array('_id' => $data['login']));

			//Существует ли запись с текущим пользователем в mongoDB
			$userLoginFailExists = $mongoUserLoginFail && !empty($mongoUserLoginFail[0]) && !empty($mongoUserLoginFail[0]['_id']);

			$last_fail_conn_dt = !empty($mongoUserLoginFail[0]['last_fail_conn_dt'])?$mongoUserLoginFail[0]['last_fail_conn_dt']:null;
			$last_fail_conn_count = !empty($mongoUserLoginFail[0]['last_fail_conn_count'])?$mongoUserLoginFail[0]['last_fail_conn_count']:null;

			if (empty($data['newpsw'])) {
				// если новый пароль не задан, то null.
				$data['newpsw'] = null;
			}

			if ($checkFailLogin && !empty($last_fail_conn_dt) && (time() - $last_fail_conn_dt)/60 < $blockTimeFailLogin && $last_fail_conn_count >= $countBadFailLogin) {
				$val['success'] = false;
				$p_1 = lang('Uchetnaya_zapis');
				$p_2 = lang($str);
				$p_3 = $blockTimeFailLogin.' '.lang('minutes');
				$val['Error_Msg'] = toUtf("{$p_1} {$data['login']} {$p_2} {$p_3}");

			} else if ( $user && $result = $user->login($data['login'], $data['psw'], $data['newpsw']) ) {
				if ( is_array($result) && !empty($result['Error_Msg']) ) {
					$val['success'] = false;
					$val['Error_Msg'] = $result['Error_Msg'];
					$val['Error_Code'] = $result['Error_Code'];
					session_destroy();
				} else {
					$blocked_from_db = $this->usermodel->getBlockedFromUserCache($data['login']);
					if ($blocked_from_db == 1) {
						$val['success'] = false;
						$val['blocked'] = 1;
						session_destroy();
					} else {
						if ($checkFailLogin) {
							$this->setMongoUserLoginFail($data['login'], $userLoginFailExists, null, 0);
						}
						$val['success'] = true;
					}
				}
			} else if ($user) {

				$val['success'] = false;

				//проверяем возможность попытки авторизации для данной учётки
				if ($checkFailLogin) {
					if (!empty($last_fail_conn_dt) && (time() - $last_fail_conn_dt)/60 < $blockTimeFailLogin && !empty($last_fail_conn_count)) {
						$attempt = $last_fail_conn_count + 1;
						//увеличиваем каунтер и обновляем последнее время попытки логина
						$this->setMongoUserLoginFail($data['login'], $userLoginFailExists,  time(), $attempt);
						if($attempt >= $countBadFailLogin){
							//возвращаем ошибку
							$p_1 = lang('Uchetnaya_zapis');
							$p_2 = lang($str);
							$p_3 = $blockTimeFailLogin.' '.lang('minutes');
							$val['Error_Msg'] = toUtf("{$p_1} {$data['login']} {$p_2} {$p_3}");
						}
						/*
						switch($last_fail_conn_count){
							case 1:
								//увеличиваем каунтер и обновляем последнее время попытки логина
								$this->setMongoUserLoginFail($data['login'], $userLoginFailExists,  time(), 2);
								break;
							case 2:
								//возвращаем ошибку, увеличиваем каунтер и обновляем последнее время попытки логина
								$this->setMongoUserLoginFail($data['login'], $userLoginFailExists,  time(), 3);
								//$val['Error_Msg'] = toUtf("Учетная запись {$data['login']} была заблокирована, в связи с ограниченным количеством неудачных попыток авторизации за определенный промежуток времени. Ваша учетная запись будет автоматически разблокирована через 10 минут");
								$p_1 = lang('Uchetnaya_zapis');
								$p_2 = lang('byla_zablokirovana_v_svyazi_s_ogranichennym_kolichestvom_neudachnyh_popytok_avtorizacii_za_opredelennyj_promezhutok_vremeni_Vasha_uchetnaya_zapis_budet_avtomaticheski_razblokirovana_cherez_10_minut');
								$val['Error_Msg'] = toUtf("{$p_1} {$data['login']} {$p_2}");
								break;
							default:
								//$val['Error_Msg'] = toUtf("Учетная запись {$data['login']} была заблокирована, в связи с ограниченным количеством неудачных попыток авторизации за определенный промежуток времени. Ваша учетная запись будет автоматически разблокирована через 10 минут");
								$p_1 = lang('Uchetnaya_zapis');
								$p_2 = lang('byla_zablokirovana_v_svyazi_s_ogranichennym_kolichestvom_neudachnyh_popytok_avtorizacii_za_opredelennyj_promezhutok_vremeni_Vasha_uchetnaya_zapis_budet_avtomaticheski_razblokirovana_cherez_10_minut');
								$val['Error_Msg'] = toUtf("{$p_1} {$data['login']} {$p_2}");
								break;
						}
						*/
					} else {
						$this->setMongoUserLoginFail($data['login'], $userLoginFailExists,  time(), 1);
					}
				}
			} else {
				// логируем несуществующие логины
				if ( defined('USERAUDIT_LOGINS') && USERAUDIT_LOGINS ) {
					require_once(APPPATH . 'libraries/UserAudit.php');
					UserAudit::LoginFail($data['login'], session_id(), 1);
				}

				$val['success'] = false;
			}
		} else
		// логинимся по карте
		if ( isset( $data['authType'] ) ) {
			$val['success'] = false;
			switch ( $data['authType'] ) {
				case 'soc_card_auth':
					if ( isset( $data['soccard_id'] ) && strlen( $data['soccard_id'] ) >= 25 ) {
						$this->load->database();
						$this->load->model( "User_model",
							"usermodel" );
						$mp = $this->usermodel->getMedPersonalBySocCardNum( $data );
						if ( is_array( $mp ) && count( $mp ) == 1 ) {
							$user = pmAuthUser::findByMedPersonalId( $mp[0]['MedPersonal_id'] );
							if (!empty($user['Error_Msg'])) {
								$val['Error_Msg'] = $user['Error_Msg'];
								$val['success'] = false;
							} else if ( $user && $user->loginTheUser( 2 ) ) {
								$val['success'] = true;
							}
						}
					}
					break;
				case 'ecp':
					$tokenType = null;
					if (!empty($data['tokenType'])) {
						$tokenType = $data['tokenType'];
					}

					if (getRegionNick() == 'kz' && !empty($tokenType) && in_array($tokenType, array('nca1', 'nca2'))) { // для этих своя логика
						// ищем юзера по ИИН и авторизуем
						$this->load->database();
						$this->load->model("User_model",
							"usermodel");

						if (!empty($data['iin'])) {
							$data['Person_Inn'] = preg_replace('/[^0-9]*/uis', '', $data['iin']);
						}
						if (!empty($data['Person_Inn'])) {
							$mp = $this->usermodel->getMedPersonalByIin($data);
							if (!empty($mp[0]['MedPersonal_id'])) {
								if (isset($data['login']) && strlen($data['login']) > 0) {
									$user = pmAuthUser::find($data['login']);
									if ($user && $user->medpersonal_id == $mp[0]['MedPersonal_id']) {
										// авторизуем
										if ($user->loginTheUser(2)) {
											$val['success'] = true;
										}
									}
								} else {
									$user = pmAuthUser::findByMedPersonalId($mp[0]['MedPersonal_id']);
									if (!empty($user['Error_Msg'])) {
										$val['Error_Msg'] = $user['Error_Msg'];
										$val['success'] = false;
									} else if ($user && $user->loginTheUser(2)) {
										$val['success'] = true;
									}
								}
							}
						} else {
							$val['Error_Msg'] = toUtf("Не указан ИИН.");
							$val['success'] = false;
						}
					} else if (isset($data['login']) && strlen($data['login']) >= 0 && isset($data['encmessage']) && isset($_SESSION['ecp_message'])) {
						// Ищем пользователя
						$auth = false;
						$user = pmAuthUser::find($data['login']);
						if ($user) {
							if (!empty($tokenType) && in_array($tokenType, array('11', '12', '13'))) { // для этих своя логика
								// с помощью swSSL:
								$swssl_path = $this->config->item('SWSSL_PATH');

								// создаём временную папку
								$path = EXPORTPATH_ROOT . "swssl_temp";
								if (!file_exists($path)) {
									mkdir($path);
								}

								$filename = time() . "_" . rand(1000, 9999);
								$certfile = realpath($path) . "/" . $filename . '_cert.cer';
								$datafile = realpath($path) . "/" . $filename . '_data.dat';
								$outfile = realpath($path) . "/" . $filename . '_out.txt';
								$signfile = realpath($path) . "/" . $filename . '_sign.sig';

								foreach ($user->certs as $cert) {
									// проверка подписи с помощью swSSL: swssl.exe verifyok openkey.file /OUTFILE:out.txt /INFILE:gost\input.txt /SIGNFILE:gost\output.txt /GOST
									$cert_string = "-----BEGIN CERTIFICATE-----" . PHP_EOL . implode(PHP_EOL, str_split($cert->cert_base64, 76)) . PHP_EOL . "-----END CERTIFICATE-----";
									file_put_contents($certfile, $cert_string);
									file_put_contents($datafile, $_SESSION['ecp_message']);
									file_put_contents($signfile, base64_decode($data['encmessage']));

									$exec = "\"{$swssl_path}\" verifyok {$certfile} /OUTFILE:{$outfile} /INFILE:{$datafile} /SIGNFILE:{$signfile}";
									// var_dump($exec);
									$swssl_result = exec($exec, $output, $return_var);
									// var_dump($swssl_result);

									if (!empty($swssl_result) && $swssl_result == 'Verify succeed') {
										$auth = true;
									}

									$exec = "\"{$swssl_path}\" verifyok {$certfile} /OUTFILE:{$outfile} /INFILE:{$datafile} /SIGNFILE:{$signfile} /GOST";
									// var_dump($exec);
									$swssl_result = exec($exec, $output, $return_var);
									// var_dump($swssl_result);

									if (!empty($swssl_result) && $swssl_result == 'Verify succeed') {
										$auth = true;
									}

									// удаляем за собой следы
									@unlink($certfile);
									@unlink($datafile);
									@unlink($outfile);
									@unlink($signfile);

									if ($auth) {
										break; // прерываем foreach
									}
								}

								if ($auth && $user->loginTheUser(4)) {
									$val['success'] = true;
								}
							} else {
								// с помощью OpenSSL:
								if (!empty($tokenType) && in_array($tokenType, array('cc'))) {
									$hex = $data['encmessage'];
									// HEX надо развернуть, криптопро зачем то делает повёрнутую подпись %)
									$newhex = '';
									while(strlen($hex) > 0) {
										$newhex = substr($hex, 0, 2) . $newhex;
										$hex = substr($hex, 2);
									}
									$signature = pack("H*", $newhex);
								} else {
									$signature = base64_decode($data['encmessage']);
								}

								$validCMS = false;
								$certCMS = null;
								$this->load->helper('openssl');
								if (!empty($tokenType) && in_array($tokenType, array('vn'))) {
									// с помощью OpenSSL:
									$openssl_path = $this->config->item('OPENSSL_PATH');
									$openssl_conf = $this->config->item('OPENSSL_CONF');
									if (!empty($openssl_conf)) {
										putenv("OPENSSL_CONF={$openssl_conf}");
									}

									// создаём временную папку
									$path = EXPORTPATH_ROOT . "openssl_temp";
									if (!file_exists($path)) {
										mkdir($path);
									}

									$filename = time() . "_" . rand(1000, 9999);
									$certfile = realpath($path) . "/" . $filename . '_cert.cer';
									$pubkeyfile = realpath($path) . "/" . $filename . '_pubkey.key';
									$datafile = realpath($path) . "/" . $filename . '_data.dat';
									$signfile = realpath($path) . "/" . $filename . '_sign.sig';

									// проверяем подпись CMS
									file_put_contents($datafile, $_SESSION['ecp_message']);
									file_put_contents($signfile, $signature);
									$exec = "\"{$openssl_path}\" cms -verify -verify_retcode -binary -in {$signfile} -inform DER -content {$datafile} -noverify -out {$pubkeyfile} -certsout {$certfile}"; // для cades, $pubkeyfile используем в качестве временного для вывода всего лишнего
									$openssl_result = exec($exec, $output, $return_var);
									if ($return_var === 0 && file_exists($certfile)) {
										$validCMS = true;
										$certCMS = getCertificateFromString(file_get_contents($certfile));
									}

									// удаляем за собой следы
									@unlink($certfile);
									@unlink($pubkeyfile);
									@unlink($datafile);
									@unlink($signfile);
								}

								foreach ($user->certs as $cert) {
									if (!empty($tokenType) && in_array($tokenType, array('vn'))) {
										// проверям, что сертификат из CMS прикреплен к пользователю
										$cert_string = getCertificateFromString($cert->cert_base64);
										if ($validCMS && $cert_string == $certCMS) {
											$auth = true;
											break;
										}
									} else {
										$verified = checkSignature($cert->cert_base64, $_SESSION['ecp_message'], $signature);
										if ($verified) {
											if (!checkCertificateCenter($cert->cert_base64)) {
												continue;
											}
											$auth = true;
											break; // прерываем foreach
										}
									}
								}

								if ($auth && $user->loginTheUser(4)) {
									$val['success'] = true;
								}
							}
						}
					}
					break;
				case 'marsh':
					if ( isset( $data['serial'] ) && strlen( $data['serial'] ) >= 0 ) {
						// Ищем пользователя с полученным серийником МАРШа.
						$user = pmAuthUser::findByMarshSerial( $data['serial'] );

						if ( $user && $user->loginTheUser( 4 ) ) {
							$val['success'] = true;
						}
					}
					break;
			}
		} else {
			$val['success'] = false;
		}

		if ( $val['success'] ) {
			// проверяем имеет ли организация доступ в систему..
			if ( !($user && $user->havingGroup('SuperAdmin')) ) {
				if ( empty($_SESSION['org_id']) ) {
					session_destroy();
					$val['Error_Msg'] = toUtf("Доступ пользователям без организации запрещен.");
					$val['success'] = false;
				} else {
					$orgdata = $this->Org_model->getOrgData(array('Org_id' => $_SESSION['org_id']));
					if ( empty($orgdata[0]['Org_IsAccess']) || $orgdata[0]['Org_IsAccess'] == 1 ) {
						session_destroy();

						$OrgName = !empty($orgdata[0]['Org_Nick']) ? $orgdata[0]['Org_Nick'] : '';

						$val['Error_Msg'] = toUtf("Доступ пользователям {$OrgName} запрещен. Свяжитесь со службой поддержки пользователей");
						$val['success'] = false;
					}
				}
			}
			// для следующих групп доступ в промед не выдаем, если она одна у пользователя
			if ($user && count($user->groups) == 1) {
				if (
					$user->havingGroup('APIUser')
					|| $user->havingGroup('EduUser')
				) {
					session_destroy();
					$val['Error_Msg'] = toUtf("Доступ невозможен. Ваша учетная запись включена в группу для которой закрыт доступ в Систему.");
					$val['success'] = false;
				}
			}
		}

		$json = json_encode($val);
		echo $json;
	}

	/**
	 * Логин?
	 */
	function Login() {
		$data = $_POST;
		$val = array();

		if ( (!empty($data['login']) && (!empty($data['password'])) ) ) {
			$user = pmAuthUser::find($data['login']);
			if ( $user && $result = $user->login($data['login'], $data['password']) ) {
				if ( is_array($result) && !empty($result['Error_Msg']) ) {
					$val['success'] = false;
					$val['Error_Msg'] = $result['Error_Msg'];
					$val['Error_Code'] = $result['Error_Code'];
					session_destroy();
				} else {
					$val['success'] = true;
				}
			} else {
				$val['success'] = false;
			}
		} else
		// логинимся по карте
		if ( isset($data['authType']) ) {
			$val['success'] = false;
			switch ( $data['authType'] ) {
				case 'soc_card_auth':
					if ( isset($data['soccard_id']) && strlen($data['soccard_id']) >= 25 ) {
						$this->load->database();
						$this->load->model("User_model", "usermodel");
						$mp = $this->usermodel->getMedPersonalBySocCardNum($data);
						if ( is_array($mp) && count($mp) == 1 ) {
							$user = pmAuthUser::findByMedPersonalId($mp[0]['MedPersonal_id']);
							if (!empty($user['Error_Msg'])) {
								$val['Error_Msg'] = $user['Error_Msg'];
								$val['success'] = false;
							} else if ( $user && $user->loginTheUser(2) ) {
								$val['success'] = true;
							}
						}
					}
					break;
				case 'uec':
					if ( isset($data['surName']) && strlen($data['surName']) >= 0 ) {
						$this->load->database();
						$this->load->model("User_model", "usermodel");
						array_walk($data, 'ConvertFromUTF8ToWin1251');
						$mp = $this->usermodel->getMedPersonalByFIODR($data);
						if ( is_array($mp) && count($mp) == 1 ) {
							$user = pmAuthUser::findByMedPersonalId($mp[0]['MedPersonal_id']);
							if (!empty($user['Error_Msg'])) {
								$val['Error_Msg'] = $user['Error_Msg'];
								$val['success'] = false;
							} else if ( $user && $user->loginTheUser(3) ) {
								$val['success'] = true;
							}
						}
					}
					break;
			}
		} else if (!empty($_SESSION['error_emias'])) {
			$val['success'] = false;
			$val['Error_Msg'] = $_SESSION['error_emias'];
		} else if (!empty($_SESSION['UserEMIAS'])) {
			$userEMIAS = $_SESSION['UserEMIAS']['UserInformation'];
			unset($_SESSION['UserEMIAS']);
			
			$this->load->database();
			$this->load->model("User_model", "usermodel");
			array_walk($data, 'ConvertFromUTF8ToWin1251');
			$user = pmAuthUser::findByEMIASData($userEMIAS['Login'], $userEMIAS['OGRN']);
			if (is_array($user) && !empty($user['Error_Msg'])) {
				$val['Error_Msg'] = $user['Error_Msg'];
				$_SESSION['error_emias'] = $user['Error_Msg'];
				$val['success'] = false;
			} else if ( $user && $user->loginTheUser(6) ) {
				$val['success'] = true;
			}
		} else {
			$val['success'] = false;
		}

		if ( $val['success'] ) {
			header("Location: /?c=promed");
		} else {
			header("Location: /?c=portal&m=promed");
		}
	}

	/**
	 * Разлогинирование
	 */
	function Logout() {
		if (isset($_SESSION['egisz_data'])) {
			header("Location: /?c=IaEgisz&m=logout", TRUE, 302);
		} else {
			if (defined('USERAUDIT_LOGINS') && USERAUDIT_LOGINS) {
				require_once(APPPATH . 'libraries/UserAudit.php');
				UserAudit::Logout(session_id());
			}
			session_destroy();
			header("Location: /");
		}
	}

	/**
	 * Разлогинирование и повторный логин по данным ЕМИАС
	 */
	function LogoutLoginEMIAS() {
		if (defined('USERAUDIT_LOGINS') && USERAUDIT_LOGINS) {
			require_once(APPPATH . 'libraries/UserAudit.php');
			UserAudit::Logout(session_id());
		}
		$dataEmias = array(
			'UserEMIAS' => $_SESSION['UserEMIAS'],
			'openLLOFromEMIASData' => $_SESSION['openLLOFromEMIASData'],
			'error_emias' => $_SESSION['error_emias']
		);
		session_destroy();
		session_start();
		$_SESSION = $dataEmias;
		$this->Login();
	}

	/**
	 * Разлогинирование с ошибкой
	 */
	function LogoutWithError() {
		session_destroy();
		header("Location: /?c=portal&m=promed&from=promed");
	}
	
		
	/**
	 * SpeedTracing Трассировка скорости выполнения
	 */
	function _st($msg = '',$action = '') {
		$debug = true; // todo: брать из настроек
		if ($debug) {
			if ($action=='start') {
				echo '<pre>'.date('d.m.Y H:i:s').' | Старт трассировки скорости выполнения</pre>'; 
			} elseif ($action=='stop') {
				echo '<pre>'.date('d.m.Y H:i:s').' | Завершение трассировки скорости выполнения, всего '.((isset($this->maxTime))?$this->maxTime:'X').' сек.</pre>'; 
			} elseif (!empty($this->startTime)) {
				$t = (microtime(true) - $this->startTime);
				echo '<pre>'.$msg.': '.$t.' сек.'.'</pre>';
				if (empty($this->maxTime)) {
					$this->maxTime = $t;
				} else {
					$this->maxTime += $t;
				}
			}
			$this->startTime = microtime(true);
		}
	}
	/**
	 * Метод для тестирования работы промеда
	 */
	function test() {
		// фиксируем время входа в метод тест, IP адрес сервера на котором проверяем, версию пхп и окружения
		echo '<pre>Сервер: '.$_SERVER['SERVER_ADDR'] .', remote: '.$_SERVER['REMOTE_ADDR'] .'</pre>'; 
		echo '<pre>Задействовано памяти в начале: '.round(memory_get_peak_usage()/1024,2).'Kb, real usage: '.round(memory_get_usage(true)/1024,2).'Kb </pre>'; 
		
		$this->_st('','start');
		// чтение конфигов
		$config = & get_config();
		$this->_st('get_config | Чтение конфигов');
		// Тест чтения сессии - монго
		$sp = $_SESSION;
		$this->_st('SESSION | Чтение сессии');
		// Тест авторизации - обращение в ldap
		$_POST['login'] = (isset($_GET['psw'])?$_GET['psw']:(isset($sp['login'])?$sp['login']:'test')); // эмулируем данные для входа
		$_POST['psw'] = (isset($_GET['psw'])?$_GET['psw']:'test'); // если не передать пароль, то проверка выполнится, но сессия разорвется (если она была)
		$this->IsLogon();
		$this->_st('IsLogon | Проверка залогиненности');
		/*$this->Login();
		$this->_st('Login | Вход');*/
		// Тест чтения справочников - монго
		$this->config->load('mongodb');
		$this->_st('mongodb | Чтение конфига mongodb');
		$this->load->model('MongoDBWork_model', 'dbmodel');
		$this->_st('mongodb | Чтение модели MongoDBWork_model');
		switch (checkMongoDb()) {
			case 'mongo':
				$this->load->library('swMongodb', null, 'mongo_db');
				break;
			case 'mongodb':
				$this->load->library('swMongodbPHP7', null, 'mongo_db');
				break;
		}
		$this->_st('checkMongoDb | Чтение библиотеки swMongodb');
		$r = $this->mongo_db->get('Diag');
		$this->_st('mongo_db->get | Получение спровочника Diag');
		// Обращение к БД рабочей, реестровой // Инициализация БД
		$this->db = $this->load->database('default', true);
		$this->_st('load->database | default | Обращение к рабочей БД');
		$this->db = null;
		$this->db = $this->load->database('reports', true);
		$this->_st('load->database | reports | Обращение к отчетной БД');
		$this->db = null;
		$this->db = $this->load->database('registry', true);
		$this->_st('load->database | registry | Обращение к реестровой БД');
		$this->db = null;
		$this->db = $this->load->database('phplog', true);
		$this->_st('load->database | phplog | Обращение к БД логов');
		$this->_st('', 'stop');
		//$this->db = $this->load->database('smp', true);
		//$this->_st('load->database | smp | Обращение к БД СМП');
		
		echo '<pre>Задействовано памяти в конце: '.round(memory_get_peak_usage()/1024,2).'Kb, real usage: '.round(memory_get_usage(true)/1024,2).'Kb </pre>'; 
		
		// Вывести основные конфиги (под допзапросу)
		if ((!empty($_GET['config'])) && ($_GET['config']=="show")) {
			echo "<pre>";print_r($config);echo "</pre>";
		}
		die();
	}

}
