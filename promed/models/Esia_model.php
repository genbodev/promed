<?php defined('BASEPATH') or die ('No direct script access allowed');

/**
 * Esia_model - Модель для авторизации через ЕСИА
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 * @author       Dmitry Vlasenko
 * @version      28.11.2018
 */
class Esia_model extends swModel
{
	var $esia_config = array(); // конфиг

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->library('textlog', array('file' => 'esia_' . date('Y-m-d') . '.log'));
		$this->config->load('esia');
		$this->esia_config = $this->config->item('esia');
	}

	/**
	 * Логин через ЕСИА
	 */
	function login($data)
	{
		$this->textlog->add('Попытка входа через esia');
		// Генерация параметров
		$timestamp = date('Y.m.d H:i:s O');
		$guid = GUID();
		$client_id = $this->esia_config['client_id'];
		$scope = 'openid';
		$redirect_uri = $this->esia_config['redirect_uri'];

		// Подписываем строку
		if (($sign = $this->sign($scope . $timestamp . $client_id . $guid)) === false) {
			$this->textlog->add('Ошибка подписи запроса в ЕСИА');
			$_SESSION['esia_error'] = 'Ошибка подписи запроса в ЕСИА';
			$this->redirect('/?c=portal&m=promed');
		}

		if (!isset($data['code'])) {
			// получение авторизационного кода
			$get_query = array(
				'client_id' => $client_id,
				'client_secret' => $sign,
				'redirect_uri' => $redirect_uri,
				'scope' => $scope,
				'response_type' => 'code',
				'state' => $guid,
				'timestamp' => $timestamp,
				'access_type' => 'offline'
			);

			// Перенаправляем в ЕСИА, чтобы пользователь дал разрешение на аутентификацию
			$this->textlog->add('Перенаправляем в ЕСИА, чтобы пользователь дал разрешение на аутентификацию');
			$this->redirect($this->esia_config['ac_path'] . '?' . http_build_query($get_query));
		} else {
			$url = $this->esia_config['te_path'];
			$post_query = array(
				'client_id' => $client_id,
				'code' => $data['code'],
				'grant_type' => 'authorization_code',
				'client_secret' => $sign,
				'redirect_uri' => $redirect_uri,
				'scope' => $scope,
				'state' => $guid,
				'timestamp' => $timestamp,
				'token_type' => 'Bearer'
			);

			// Делаем запрос на получение маркера идентификации
			// Если не получается, спим и пытаемся еще.
			$attempts = 4;
			for ($i = 1; ($i <= $attempts); $i++) {
				if (($res = $this->httpRequest($url, $post_query)) === false) {
					$this->textlog->add('Не удалось выполнить запрос на получение маркера идентификации');
					$_SESSION['esia_error'] = 'Не удалось выполнить запрос на получение маркера идентификации';
					$this->redirect('/?c=portal&m=promed');
				}
				if ($res['http_code'] == 200) {
					break;
				}
				$this->textlog->add('Не удалось получить маркер идентификации, попытка #' . $i . ': код ответа: ' . (int)$res['http_code']);
				usleep(500000);
			}

			if ($res['http_code'] == 200) {
				try {

					// Лог ЕСИА
					$this->textlog->add('Ответ на запроса получения маркера идентификации: ' . $res['out']);

					// Разбираем токен идентификации и проверяем что он верный
					$resdata = json_decode($res['out']);
					$id_token_parts = explode('.', $resdata->id_token);
					$id_token_payload = json_decode(base64_decode($id_token_parts[1]));

					if (!($id_token_payload->{'urn:esia:sbj'}->{'urn:esia:sbj:typ'} == 'P')) {
						$this->textlog->add('Данные физического лица не доступны или не подтверждены. Пожалуйста, попробуйте другую учетную запись Госуслуг.');
						$_SESSION['esia_error'] = 'Данные физического лица не доступны или не подтверждены. Пожалуйста, попробуйте другую учетную запись Госуслуг.';
						$this->redirect('/?c=portal&m=promed');
						return;
					}
					if ($id_token_payload->iss != 'http://esia.gosuslugi.ru/' || $id_token_payload->aud != $client_id || $id_token_payload->exp < time()) {
						$this->textlog->add('Ошибка в данных полученных от ЕСИА.');
						$_SESSION['esia_error'] = 'Ошибка в данных полученных от ЕСИА.';
						$this->redirect('/?c=portal&m=promed');
						return;
					}
				} catch (Exception $e) {
					$this->textlog->add('Ошибка авторизации через ЕСИА: ' . $e->getMessage());
					$_SESSION['esia_error'] = 'Ошибка авторизации через ЕСИА: ' . $e->getMessage();
					$this->redirect('/?c=portal&m=promed');
				}

				$user_id = $id_token_payload->sub;

				// Аутентификация закончена, теперь формируем запрос для получения данных пользователя
				$scope = str_replace(':user_id', $user_id, $this->esia_config['usr_scope']);

				// Снова создаем подпись, scope изменился
				if (($sign = $this->sign($scope . $timestamp . $client_id . $guid)) === false) {
					$this->textlog->add('Ошибка подписи запроса в ЕСИА');
					$_SESSION['esia_error'] = 'Ошибка подписи запроса в ЕСИА';
					$this->redirect('/?c=portal&m=promed');
				}

				$get_query = array(
					'client_id' => $client_id,
					'client_secret' => $sign,
					'redirect_uri' => str_replace(':user_id', $user_id, $this->esia_config['redirect_uri_info']),
					'scope' => $scope,
					'response_type' => 'code',
					'state' => $guid,
					'timestamp' => $timestamp,
					'access_type' => 'offline'
				);
				
				// Перенаправляем в ЕСИА, чтобы пользователь дал разрешение на получение данных по нему
				$this->redirect($this->esia_config['ac_path'] . '?' . http_build_query($get_query));
			} else {
				$this->textlog->add('Ошибка входа через ЕСИА: ' . $res['out']);
				$_SESSION['esia_error'] = 'Ошибка входа через ЕСИА, код ответа: ' . (int)$res['http_code'];
				$this->redirect('/?c=portal&m=promed');
			}
		}
	}

	/**
	 * Получение данных пользователя от ЕСИА
	 */
	function user_info($data) {
		if (isset($data['code'])) {
			// Генерация параметров
			$timestamp = date('Y.m.d H:i:s O');
			$guid = GUID();
			$client_id = $this->esia_config['client_id'];
			$scope = str_replace(':user_id', $data['user_id'], $this->esia_config['usr_scope']);

			if (($sign = $this->sign($scope . $timestamp . $client_id . $guid)) === false) {
				$this->textlog->add('Ошибка подписи запроса в ЕСИА');
				$_SESSION['esia_error'] = 'Ошибка подписи запроса в ЕСИА';
				$this->redirect('/?c=portal&m=promed');
			}

			$url = $this->esia_config['te_path'];
			$post_query = array(
				'client_id' => $client_id,
				'code' => $data['code'],
				'grant_type' => 'authorization_code',
				'client_secret' => $sign,
				'redirect_uri' => str_replace(':user_id', $data['user_id'], $this->esia_config['redirect_uri']),
				'scope' => $scope,
				'state' => $guid,
				'timestamp' => $timestamp,
				'token_type' => 'Bearer'
			);

			// Делаем запрос на получение маркера доступа
			// Если не получается, спим и пытаемся еще.
			$attempts = 3;
			for ($i = 1; ($i <= $attempts); $i++) {
				if (($res = $this->httpRequest($url, $post_query)) === false) {
					$this->textlog->add('Не удалось выполнить запрос на получение маркера доступа');
					$_SESSION['esia_error'] = 'Не удалось выполнить запрос на получение маркера доступа';
					$this->redirect('/?c=portal&m=promed');
				}
				if ($res['http_code'] == 200) {
					break;
				}
				$this->textlog->add('Не удалось получить маркер доступа, попытка #' . $i . ': код ответа: ' . (int)$res['http_code']);
				usleep(500000);
			}

			if ($res['http_code'] == 200) {
				try {

					// Лог ЕСИА
					$this->textlog->add('action_user_info. запрос на получение маркера доступа, ' . $res['out']);

					$resdata = json_decode($res['out']);
					$access_token = $resdata->access_token;

					// Токен доступа получен, делаем запросы для получения данных по пользователю, используя его
					$url = str_replace(':user_id', $data['user_id'], $this->esia_config['usr_data_path']);
					$headers = array(
						'Authorization: Bearer ' . $access_token
					);
					if (($res = $this->httpRequest($url, null, $headers)) === false) {
						$_SESSION['esia_error'] = 'Ошибка подключения к ЕСИА. Пожалуйста, попробуйте позже.';
						$this->redirect('/?c=portal&m=promed');
					}

					if ($res['http_code'] == 200) {
						try {
							// Лог ЕСИА
							$this->textlog->add('action_user_info. Токен доступа получен, делаем запрос для получения данных по пользователю, используя его, ' . $res['out']);

							$person_data = json_decode($res['out']);

							$url = str_replace(':user_id', $data['user_id'], $this->esia_config['usr_data_path'] . '/docs');

							if (($res = $this->httpRequest($url, null, $headers)) === false) {
								$this->textlog->add('Не удалось выполнить запрос на получение документов пользователя');
								$_SESSION['esia_error'] = 'Не удалось выполнить запрос на получение документов пользователя';
								$this->redirect('/?c=portal&m=promed');
							}

							if ($res['http_code'] == 200) {
								try {
									// Лог ЕСИА
									$this->textlog->add('action_user_info. Получаем документов пользователя: ' . $data['user_id'] . ', ' . $res['out']);

									$documents_data = json_decode($res['out']);
								} catch (Exception $e) {
									$_SESSION['esia_error'] = 'Произошла ошибка при авторизации через ЕСИА (код ошибки 101)';
									$this->textlog->add('ESIA ERROR: 101 Exception: ' . $e->getMessage());
									$this->redirect('/?c=portal&m=promed');
								}
							} else {
								$_SESSION['esia_error'] = 'Произошла ошибка при авторизации через ЕСИА (код ошибки 102)';
								$this->textlog->add('ESIA ERROR: 102. http responce not 200, but: ' . (int)$res['http_code']);
								$this->redirect('/?c=portal&m=promed');
							}
						} catch (Exception $e) {
							$_SESSION['esia_error'] = 'Произошла ошибка при авторизации через ЕСИА (код ошибки 103)';
							$this->textlog->add('ESIA ERROR: 103 Exception: ' . $e->getMessage());
							$this->redirect('/?c=portal&m=promed');
						}

					} else {
						$_SESSION['esia_error'] = 'Произошла ошибка при авторизации через ЕСИА (код ошибки 104)';
						$this->textlog->add('ESIA ERROR: 104. http responce not 200, but: ' . (int)$res['http_code']);
						$this->redirect('/?c=portal&m=promed');
					}

				} catch (Exception $e) {
					$_SESSION['esia_error'] = 'Произошла ошибка при авторизации через ЕСИА (код ошибки 105)';
					$this->textlog->add('ESIA ERROR: 105 Exception: ' . $e->getMessage());
					$this->redirect('/?c=portal&m=promed');
				}

			} else {
				$_SESSION['esia_error'] = 'Произошла ошибка при авторизации через ЕСИА (код ошибки 106)';
				$this->textlog->add('ESIA ERROR: 106. http responce not 200, but: ' . (int)$res['http_code'] . ' OUT: ' . $res['out']);
				$this->redirect('/?c=portal&m=promed');
			}

		} else {
			if (isset($data['error'])) {
				if ($data['error'] == 'access_denied') {
					$_SESSION['esia_error'] = 'Вы не предоставили доступ к своим данным, поэтому авторизация через ЕСИА невозможна';
					$this->redirect('/?c=portal&m=promed');
				}
			} else {
				$_SESSION['esia_error'] = 'Ошибка авторизациии через ЕСИА';
				$this->redirect('/?c=portal&m=promed');
			}
		}

		if (!(isset($person_data->trusted) && $person_data->trusted === true)) {
			$_SESSION['esia_error'] = 'Недостаточно прав для входа в Систему. Вход в Систему доступен только для Пользователей, прошедших процедуру подтверждения учетной записи в ЕСИА.';
			$this->redirect('/?c=portal&m=promed');
			return;
		}

		if (empty($documents_data)) {
			$_SESSION['esia_error'] = 'Ошибка авторизациии через ЕСИА';
			$this->redirect('/?c=portal&m=promed');
		}

		$person_data->polisNum = null;
		foreach ($documents_data->elements as $doc_link) {
			if (isset($headers)) {
				$doc_resp = $this->httpRequest($doc_link, null, $headers);
				if (!empty($doc_resp['out'])) {
					$doc_resp_out = json_decode($doc_resp['out']);
					if (!empty($doc_resp_out->type) && $doc_resp_out->type == "MDCL_PLCY" && !empty($doc_resp_out->number)) {
						$person_data->polisNum = $doc_resp_out->number;
						break;
					}
				}
			}
		}

		// Если вернулась подтвержденная запись, то  выполняется поиск человека  по параметрам (или):
		// СНИЛС
		$mp = $this->queryResult("
			select distinct top 2
				mp.MedPersonal_id
			from
				v_MedPersonal mp (nolock)
				inner join v_PersonState ps (nolock) on ps.Person_id = mp.Person_id
			where
				ps.Person_Snils = :Person_Snils
		", array(
			'Person_Snils' => str_replace(array('-', ' '), '', $person_data->snils)
		));

		if (empty($mp[0]['MedPersonal_id'])) {
			// ФИО и ДР.
			$mp = $this->queryResult("
				select distinct top 2
					mp.MedPersonal_id
				from
					v_MedPersonal mp (nolock)
					inner join v_PersonState ps (nolock) on ps.Person_id = mp.Person_id
				where
					ps.Person_SurName = :Person_SurName
					and ps.Person_FirName = :Person_FirName
					and ps.Person_SecName = :Person_SecName
					and ps.Person_BirthDay = :Person_BirthDay
			", array(
				'Person_SurName' => $person_data->lastName,
				'Person_FirName' => $person_data->firstName,
				'Person_SecName' => $person_data->middleName,
				'Person_BirthDay' => date('Y-m-d', strtotime($person_data->birthDate))
			));
		}

		if (empty($mp[0]['MedPersonal_id']) && !empty($person_data->polisNum)) {
			// ФИ и ДР и полис.
			$mp = $this->queryResult("
				select distinct top 2
					mp.MedPersonal_id
				from
					v_MedPersonal mp (nolock)
					inner join v_PersonState ps (nolock) on ps.Person_id = mp.Person_id
				where
					ps.Person_SurName = :Person_SurName
					and ps.Person_FirName = :Person_FirName
					and ps.Person_BirthDay = :Person_BirthDay
					and ps.Polis_Num = :Polis_Num
			", array(
				'Person_SurName' => $person_data->lastName,
				'Person_FirName' => $person_data->firstName,
				'Person_SecName' => $person_data->middleName,
				'Person_BirthDay' => date('Y-m-d', strtotime($person_data->birthDate)),
				'Polis_Num' => $person_data->polisNum
			));
		}

		if (!empty($mp[0]['MedPersonal_id'])) {
			if (count($mp) > 1) {
				$_SESSION['esia_error'] = 'Не удалось однозначно определить Пользователя: найдено более одного человека с указанными данными. Обратитесь в службу технической поддержки для проверки данных';
				$this->redirect('/?c=portal&m=promed');
			} else {
				$user = pmAuthUser::findByMedPersonalId($mp[0]['MedPersonal_id'], true);
				if (is_array($user) && !empty($user['Error_Msg'])) {
					$_SESSION['esia_error'] = $user['Error_Msg'];
					$this->redirect('/?c=portal&m=promed');
				} else if ($user && $user->loginTheUser(5)) {
					$this->redirect('/?c=promed');
				} else {
					$_SESSION['esia_error'] = 'Авторизация пользователя не выполнена. Учетные записи пользователя не  найдены. Обратитесь к администратору системы для уточнения данных учетных записей пользователя';
					$this->redirect('/?c=portal&m=promed');
				}
			}
		} else {
			$_SESSION['esia_error'] = 'Не выполнена идентификация пользователя. Обратитесь к администратору системы для уточнения персональных данных пользователя';
			$this->redirect('/?c=portal&m=promed');
		}
	}

	/**
	 * Подписывает строку подписью PKCS7
	 */
	function sign($str)
	{
		try {
			$path = EXPORTPATH_ROOT . "openssl_temp";
			if (!file_exists($path)) {
				mkdir($path);
			}
			$presignfile = $path . '/' . rand(100000, 99999) . '_' . time() . '.pre';
			$signfile = $presignfile . '.sig';
			file_put_contents($presignfile, $str);
			if (isset($this->esia_config['key_pass'])) {
				openssl_pkcs7_sign($presignfile, $signfile, $this->esia_config['crt'], array($this->esia_config['key'], $this->esia_config['key_pass']), array());
			} else {
				openssl_pkcs7_sign($presignfile, $signfile, $this->esia_config['crt'], $this->esia_config['key'], array());
			}
			$sign = file_get_contents($signfile);
			unlink($presignfile);
			unlink($signfile);
			$sign = $this->url_safe(substr($sign, strpos($sign, "MII"), strrpos($sign, "\n\n----") - strpos($sign, "MII")));
			return $sign;
		} catch (Exception $e) {
			die();
			return false;
		}
	}

	/**
	 * Заменяем символы в Base64 на подходящие для передачи в URL
	 */
	function url_safe($input)
	{
		return str_replace('=', '', strtr($input, '+/', '-_'));
	}

	/**
	 * Перенаправление
	 */
	function redirect($url)
	{
		header("Location: {$url}", TRUE, 302);
	}

	/**
	 * HTTP запрос
	 * Возвращает false в случае если запрос не удался или массив с возвращенными данными
	 */
	static function httpRequest($url, $post = null, $headers = null, $responseHeaders = false)
	{
		try {
			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
			curl_setopt($ch, CURLOPT_TIMEOUT, 5);

			if (isset($post)) {
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
			}

			if (isset($headers)) curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			if (!empty($responseHeaders)) {
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($ch, CURLOPT_HEADER, 1);
			}

			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

			$str = curl_exec($ch);
			$res = curl_getinfo($ch);

			if (!empty($responseHeaders)) {
				$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
				$header = substr($str, 0, $header_size);
				$str = substr($str, $header_size);
			}

			curl_close($ch);

		} catch (Exception $e) {
			// $this->textlog->add('CURL ERROR: ' . $e->getMessage() . ' | URL: ' . $url);
			return false;
		}

		$res['out'] = $str;
		if (!empty($header)) $res['header'] = $header;

		return $res;
	}
}