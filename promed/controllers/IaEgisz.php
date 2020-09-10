<?php
defined('BASEPATH') or die ('No direct script access allowed');
/**
 * IaEgisz - контроллер для авторизации через ИА ЕГИСЗ
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 * @author       Dmitry Vlasenko
 * @version      24.12.2018
 *
 * @property IaEgisz_model $dbmodel
 */
class IaEgisz extends swController {
	var $NeedCheckLogin = false; // авторизация не нужна

	/**
	 * Описание правил для входящих параметров
	 * @var array
	 */
	var $inputRules = array(
		'login' => array(
			array(
				'field' => 'SAMLResponse',
				'label' => 'Ответ от ИА ЕГИСЗ',
				'rules' => '',
				'type' => 'string'
			)
		),
		'logout' => array(
			array(
				'field' => 'SAMLResponse',
				'label' => 'Ответ от ИА ЕГИСЗ',
				'rules' => '',
				'type' => 'string'
			)
		)
	);

	/**
	 * Конструктор
	 */
    function __construct() {
		parent::__construct();
		$this->load->database();
		$this->load->model('IaEgisz_model', 'dbmodel');
	}

	/**
	 * Логин через ИА ЕГИСЗ
	 */
	function login() {
		$data = $this->ProcessInputData('login', false, false);
		if ($data === false) { return false; }

		$this->dbmodel->login($data);
	}

	/**
	 * Логаут через ИА ЕГИСЗ
	 */
	function logout() {
		$data = $this->ProcessInputData('logout', false, false);
		if ($data === false) { return false; }

		$this->dbmodel->logout($data);
	}
}
