<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Person - контроллер для управления людьми
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package		Common
* @access		public
* @copyright		Copyright (c) 2009 Swan Ltd.
* @author		Pshenitcyn Ivan aka IvP (ipshon@rambler.ru)
* @version		12.07.2009
 * @property Person_model dbmodel
*/
class Person6E extends swController {

	public $inputRules = array(
		'getPersonGrid' => array(
			array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => '', 'type' => 'id'),
			array('field' =>'Double_ids', 'label' => 'Идентификаторы двойников', 'rules' => '', 'type' => 'string'),
			array('field' => 'Person_Surname', 'label' => 'Фамилия', 'rules' => 'ban_percent|trim', 'type' => 'string'),
			array('field' => 'Person_Firname', 'label' => 'Имя', 'rules' => 'ban_percent|trim', 'type' => 'string'),
			array('field' => 'Person_Secname', 'label' => 'Отчество', 'rules' => 'ban_percent|trim', 'type' => 'string'),
			array('field' => 'Person_Birthday', 'label' => 'Дата рождения', 'rules' => 'trim', 'type' => 'date'),
			array('field' => 'Address_Street', 'label' => 'Улица', 'rules' => 'ban_percent|trim', 'type' => 'string'),
			array('field' => 'Address_House', 'label' => 'Дом', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'PersonCard_Code', 'label' => 'Номер амбулаторной карты', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'Person_Inn', 'label' => 'ИИН', 'rules' => 'trim|is_numeric', 'type' => 'string'),
			array('field' => 'Polis_Ser', 'label' => 'Серия полиса', 'rules' => 'trim', 'type' => 'string'),
			array(
				'field' => 'PolisFormType_id',
				'label' => 'Форма полиса',
				'rules' => 'trim',
				'type' => 'id'
			),
			array('field' => 'Polis_Num', 'label' => 'Номер полиса', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'Person_Code', 'label' => 'Ед. номер', 'rules' => 'trim|is_numeric', 'type' => 'string'),
			array(
				'field' => 'PartMatchSearch',
				'label' => 'Поиск по частичному совпадению',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'showAll',
				'label' => 'Показывать всех',
				'rules' => '',
				'type' => 'id'
			),
			array('field' => 'dontShowUnknowns', 'label' => '', 'rules' => '', 'type' => 'int'),
			array('default' => 0, 'field' => 'start', 'label' => 'Начальный номер записи', 'rules' => 'trim', 'type' => 'int'),
			array('default' => 100, 'field' => 'limit', 'label' => 'Количество возвращаемых записей', 'rules' => 'trim', 'type' => 'int')
		)
	);

	/**
	 * Конструктор
	 */
	public function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model("Person_model6E", "dbmodel");
	}

	/**
	 * Поиск людей
	 * Используется: АРМ регистратора поликлиники (swWorkPlacePolkaRegWindow)
	 */
	function getPersonGrid() {
		$data = $this->ProcessInputData('getPersonGrid', true);
		if ( $data === false ) { return true; }

		$response = $this->dbmodel->getPersonGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}
}
