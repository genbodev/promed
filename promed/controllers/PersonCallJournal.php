<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 *	PersonCallJournal - контроллер для работы с сервисом обзвона
 *
 * 	PromedWeb - The New Generation of Medical Statistic Software
 * 	http://swan.perm.ru/PromedWeb
 *
 *
 * 	@package		unknown
 * 	@access			public
 * 	@copyright		Copyright (c) 2009-2013 Swan Ltd.
 * 	@author			Bykov Stanislav
 * 	@version		29.08.2013
 */

class PersonCallJournal extends swController {
	var $NeedCheckLogin = false; // Отключаем промедовскую авторизацию

	protected $error_codes = array(
		'200' => 'ok',
		'201' => 'session created',
		'304' => 'not modified',
		'400' => 'empty token',
		'401' => 'unauthorized access',
		'403' => 'access forbidden'
	);

	protected $secret_token = '34b42b386a6f5ec463189e0783f275a7';

	/**
	 * PersonCallJournal constructor.
	 */
	function __construct() {
		parent::__construct();

		$this->load->library('textlog', array('file' => 'PersonCallJournal.log'));

		$this->load->database();
		$this->load->model('PersonCallJournal_model', 'dbmodel');
	}


	/**
	 *	Аутентицикация
	 */
	public function authenticate() {
		$auth_token = (!empty($_REQUEST['auth_token']) ? $_REQUEST['auth_token'] : '');

		if ( empty($auth_token) ) {
			$data = $this->getStatus(400);
		}
		else if ( $auth_token == $this->secret_token ) {
			$data = array(
				'data' => array(
					'token' => md5($auth_token . date('d.m.Y'))
				),
				'status' => array(
					'msg' => "authorized",
					'code' => 200
				)
			);
		}
		else {
			$data = getStatus(401);
		}

		echo json_encode($data);
	}


	/**
	 *	Получение списка пациентов для обзвона
	 */
	public function getPersonCallJournal() {
		$limit = (!empty($_REQUEST['limit']) && is_numeric($_REQUEST['limit']) && $_REQUEST['limit'] > 0 ? intval($_REQUEST['limit']) : 500);
		$token = (!empty($_REQUEST['token']) ? $_REQUEST['token'] : '');

		if ( !$this->checkToken($token) ) {
			$data = array(
				'status' => $this->getStatus(403)
			);
		}
		else {
			$records = $this->dbmodel->getPersonCallJournal($limit);

			if ( $records === false ) {
				$data = array(
					'status' => $this->getStatus(304)
				);
			}
			else {
				$data = array(
					'data' => array(
						'records' => $records
					),
					'status' => $this->getStatus(200)
				);
			}
		}

		echo json_encode($data);
	}


	/**
	 *	Проверка токена
	 */
	private function checkToken($token) {
		return ($token == md5($this->secret_token . date('d.m.Y')));
	}


	/**
	 *	Получение статуса по коду
	 */
	private function getStatus($code) {
		return array(
			"msg" => $this->error_codes[$code],
			"code" => $code
		);
	}
}
