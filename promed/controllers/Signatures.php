<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Signatures - контроллер для работы с подписями
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
class Signatures extends swController {
	public $inputRules = array(
		'loadSignaturesHistoryList' => array(
			array(
				'field' => 'Signatures_id',
				'label' => 'Идентификатор подписи',
				'rules' => 'required',
				'type' => 'int'
			)
		),
		'getDocHash' => array(
			array(
				'field' => 'Doc_Type',
				'label' => 'Doc_Type',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'Doc_id',
				'label' => 'Doc_id',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'SignedToken',
				'label' => 'SignedToken',
				'rules' => 'required',
				'type' => 'string'
			)
		),
		'signDoc' => array(
			array(
				'field' => 'Doc_Type',
				'label' => 'Doc_Type',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'Doc_id',
				'label' => 'Doc_id',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'SignedData',
				'label' => 'SignedData',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'Hash',
				'label' => 'Hash',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'SignedToken',
				'label' => 'SignedToken',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'signType',
				'label' => 'signType',
				'rules' => 'required',
				'type' => 'string'
			)
		),
		'verifySign' => array(
			array(
				'field' => 'Doc_Type',
				'label' => 'Doc_Type',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'Doc_id',
				'label' => 'Doc_id',
				'rules' => 'required',
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
		$this->load->model('Signatures_model', 'dbmodel');
	}

	/**
	 * Список версий
	 */
	function loadSignaturesHistoryList() {
		$data = $this->ProcessInputData('loadSignaturesHistoryList', true);
		if ($data) {
			$response = $this->dbmodel->loadSignaturesHistoryList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Получение данных для подписи
	 */
	function getDocHash() {
		$data = $this->ProcessInputData('getDocHash', true);
		if ($data) {
			$response = $this->dbmodel->getDocHash($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Подписание документа
	 */
	function signDoc() {
		$data = $this->ProcessInputData('signDoc', true);
		if ($data) {
			$response = $this->dbmodel->signDoc($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Верификация подписи документа
	 */
	function verifySign() {
		$data = $this->ProcessInputData('verifySign', true);
		if ($data) {
			$response = $this->dbmodel->verifySign($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
}
