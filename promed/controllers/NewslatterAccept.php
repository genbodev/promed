<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * NewslatterAccept - контроллер для работы с согласиями
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2015 Swan Ltd.
 * @author			Aleksandr Chebukin
 * @version			16.12.2015
 *
 * @property NewslatterAccept_model dbmodel
 */

class NewslatterAccept extends swController {
	protected  $inputRules = array(
		'delete' => array(
			array(
				'field' => 'NewslatterAccept_id',
				'label' => 'Идентификатор согласия',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'loadList' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => '',
				'type' => 'id'
			)
		),
		'load' => array(
			array(
				'field' => 'NewslatterAccept_id',
				'label' => 'Идентификатор согласия',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'check' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'save' => array(
			array(
				'field' => 'NewslatterAccept_id',
				'label' => 'Идентификатор согласия',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_id',
				'label' => 'МО',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Человек',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'NewslatterAccept_Phone',
				'label' => 'Номер телефона',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'NewslatterAccept_IsSMS',
				'label' => 'СМС рассылка',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'NewslatterAccept_Email',
				'label' => 'E-mail',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'NewslatterAccept_IsEmail',
				'label' => 'E-mail рассылка',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'NewslatterAccept_begDate',
				'label' => 'Период действия',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'NewslatterAccept_endDate',
				'label' => 'Период действия',
				'rules' => '',
				'type' => 'date'
			)
		),
		'printAccept' => array(
			array(
				'field' => 'NewslatterAccept_id',
				'label' => 'Идентификатор согласия',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'printDenial' => array(
			array(
				'field' => 'NewslatterAccept_id',
				'label' => 'Идентификатор согласия',
				'rules' => 'required',
				'type' => 'id'
			),
		)
	);

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model('NewslatterAccept_model', 'dbmodel');
	}

	/**
	 * Удаление согласия
	 */
	function delete()
	{
		$data = $this->ProcessInputData('delete');
		if ($data === false) { return false; }

		$response = $this->dbmodel->delete($data);
		$this->ProcessModelSave($response, true, 'Ошибка при удалении согласия')->ReturnData();
		return true;
	}

	/**
	 * Возвращает список согласий
	 */
	function loadList()
	{
		$data = $this->ProcessInputData('loadList');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Возвращает согласие
	 */
	function load()
	{
		$data = $this->ProcessInputData('load');
		if ($data === false) { return false; }

		$response = $this->dbmodel->load($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Проверка наличия активного согласия на рассылку
	 */
	function check()
	{
		$data = $this->ProcessInputData('check');
		if ($data === false) { return false; }

		$response = $this->dbmodel->check($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Сохранение примечания
	 */
	function save()
	{
		$data = $this->ProcessInputData('save');
		if ($data === false) { return false; }

		if ( empty($data['NewslatterAccept_IsEmail']) && empty($data['NewslatterAccept_IsSMS']) && empty($data['NewslatterAccept_endDate']) ) {
			$this->ReturnData(array(
				'Error_Msg' => 'Один из флагов (СМС или E-mail) должен быть обязательно выбран',
				'success' => false
			));
			return false;
		}

		if ( !empty($data['NewslatterAccept_begDate']) && !empty($data['NewslatterAccept_endDate']) && $data['NewslatterAccept_begDate'] > $data['NewslatterAccept_endDate'] ) {
			$this->ReturnData(array(
				'Error_Msg' => 'Дата начала не может быть больше даты окончания',
				'success' => false
			));
			return false;
		}

		if ( !empty($data['NewslatterAccept_endDate']) && date('Y-m-d') > $data['NewslatterAccept_endDate'] ) {
			$this->ReturnData(array(
				'Error_Msg' => 'Дата окончания не может быть меньше текущей даты',
				'success' => false
			));
			return false;
		}
		
		if ( !empty($data['NewslatterAccept_Email']) && !filter_var($data['NewslatterAccept_Email'], FILTER_VALIDATE_EMAIL) ) {
			throw new Exception('Введенный e-mail некорректен');
		}

		// Проверка дубликатов управляющих примечаний
		$response = $this->dbmodel->checkNewslatterAcceptDoubles($data);
		if ( !is_array($response) ) {
			throw new Exception('Ошибка при проверке дублей согласий');
		}
		if( count($response) > 0 ) {
			throw new Exception('Для одного человека нельзя добавить более одного согласия по конкретной МО в период');
		}

		$response = $this->dbmodel->save($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Печать согласия
	 */
	function printAccept() {
		$this->load->library('parser');

		$data = $this->ProcessInputData('printAccept', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->printAccept($data);
		$template = 'print_newslatter_accept';

		if ( !is_array($response) || count($response) == 0 ) {
			echo 'Ошибка при получении данных по согласию';
			return true;
		}

		$print_data = array(
			'Person_SurName' => returnValidHTMLString($response[0]['Person_SurName']),
			'Person_FirName' => returnValidHTMLString($response[0]['Person_FirName']),
			'Person_SecName' => returnValidHTMLString($response[0]['Person_SecName']),
			'UAddress_Name' => returnValidHTMLString($response[0]['UAddress_Name']),
			'Document_begDate' => returnValidHTMLString($response[0]['Document_begDate']),
			'Document_Num' => returnValidHTMLString($response[0]['Document_Num']),
			'Document_Ser' => returnValidHTMLString($response[0]['Document_Ser']),
			'DocumentType_Name' => returnValidHTMLString($response[0]['DocumentType_Name']),
			'OrgDep_Name' => returnValidHTMLString($response[0]['OrgDep_Name']),
			'NewslatterAccept_Phone' => returnValidHTMLString($response[0]['NewslatterAccept_Phone']),
			'NewslatterAccept_IsSMS' => $response[0]['NewslatterAccept_IsSMS'] == 'true' ? 'V' : '&nbsp;&nbsp;&nbsp;',
			'NewslatterAccept_Email' => returnValidHTMLString($response[0]['NewslatterAccept_Email']),
			'NewslatterAccept_IsEmail' => $response[0]['NewslatterAccept_IsEmail'] == 'true' ? 'V' : '&nbsp;&nbsp;&nbsp;',
			'Lpu_Name' => returnValidHTMLString($response[0]['Lpu_Name']),
			'UAddress_Address' => returnValidHTMLString($response[0]['UAddress_Address'])
		);

		return $this->parser->parse($template, $print_data);
	}

	/**
	 * Печать отказа
	 */
	function printDenial() {
		$this->load->library('parser');

		$data = $this->ProcessInputData('printDenial', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->printDenial($data);
		$template = 'print_newslatter_denial';

		if ( !is_array($response) || count($response) == 0 ) {
			echo 'Ошибка при получении данных по согласию';
			return true;
		}

		$print_data = array(
			'Person_SurName' => returnValidHTMLString($response[0]['Person_SurName']),
			'Person_FirName' => returnValidHTMLString($response[0]['Person_FirName']),
			'Person_SecName' => returnValidHTMLString($response[0]['Person_SecName']),
			'UAddress_Name' => returnValidHTMLString($response[0]['UAddress_Name']),
			'Document_begDate' => returnValidHTMLString($response[0]['Document_begDate']),
			'Document_Num' => returnValidHTMLString($response[0]['Document_Num']),
			'Document_Ser' => returnValidHTMLString($response[0]['Document_Ser']),
			'DocumentType_Name' => returnValidHTMLString($response[0]['DocumentType_Name']),
			'OrgDep_Name' => returnValidHTMLString($response[0]['OrgDep_Name']),
			'NewslatterAccept_Phone' => returnValidHTMLString($response[0]['NewslatterAccept_Phone']),
			'NewslatterAccept_Email' => returnValidHTMLString($response[0]['NewslatterAccept_Email']),
			'Lpu_Name' => returnValidHTMLString($response[0]['Lpu_Name']),
			'UAddress_Address' => returnValidHTMLString($response[0]['UAddress_Address'])
		);

		return $this->parser->parse($template, $print_data);
	}

}