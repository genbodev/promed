<?php
defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Esia - контроллер для авторизации через ЕСИА
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 * @author       Dmitry Vlasenko
 * @version      28.11.2018
 *
 * @property Esia_model $dbmodel
 */
class Esia extends swController {
	var $NeedCheckLogin = false; // авторизация не нужна

	/**
	 * Описание правил для входящих параметров
	 * @var array
	 */
	var $inputRules = array(
		'login' => array(
			array(
				'field' => 'code',
				'label' => 'Код авторизации',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'error',
				'label' => 'Ошибка',
				'rules' => '',
				'type' => 'string'
			)
		),
		'user_info' => array(
			array(
				'field' => 'code',
				'label' => 'Код авторизации',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'error',
				'label' => 'Ошибка',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'user_id',
				'label' => 'Идентификатор пользователя',
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
		$this->load->model('Esia_model', 'dbmodel');
	}

	/**
	 * Логин через ЕСИА
	 */
	function login() {
		$data = $this->ProcessInputData('login', false, false);
		if ($data === false) { return false; }

		$this->dbmodel->login($data);
	}

	/**
	 * Получение данных пользователя от ЕСИА
	 */
	function user_info() {
		$data = $this->ProcessInputData('user_info', false, false);
		if ($data === false) { return false; }

		$this->dbmodel->user_info($data);
	}
}
