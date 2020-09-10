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
require_once(APPPATH.'controllers/Person.php');

class Person4E extends Person {
	/**
	 * Конструктор
	 */
	public function init(){
		// Инициализируем модель до вызова родительского метода
		// Чтобы загрузилась нужная нам модель
		$this->load->model('Person_model4E','dbmodel',true);

		$this->inputRules = array_merge($this->inputRules, array(
			'getPersonCallsHistory' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор человека',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'getCountCallByPersonId' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор человека',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'getPersonByAddress' => array(
				array('field' => 'Town_id','label' => 'Город',	'rules' => '','type' => 'int'),
				array('field' => 'Area_pid','label' => 'Регион',	'rules' => '','type' => 'int'),
				array('field' => 'KLStreet_id','label' => 'Улица',	'rules' => '','type' => 'int'),
				array('field' => 'Address_House','label' => 'Дом',	'rules' => '','type' => 'string'),
				array('field' => 'Address_Flat','label' => 'Квартира',	'rules' => '','type' => 'int'),
				array('field' => 'Address_Corpus','label' => 'Корпус',	'rules' => '','type' => 'string'),
				array('field'=>'isNotDead', 'label'=>'isNotDead', 'rules'=>'', 'type'=>'boolean')
			)
		));

		parent::init();
	}

	/**
	 * Получение списка подстанций СМП
	 * @return boolean
	 */
	public function loadSocStatusList() {
		$data = $this->ProcessInputData('loadSocStatusList',true);
		if ( $data === false ) {
			return false;
		}
		$response = $this->dbmodel->loadSocStatusList( $data );
		$this->ProcessModelList( $response, true, true )->ReturnData();
	}
	
	/**
	 * Получение истории вызовов по указанному пациенту
	 * @return boolean
	 */
	public function getPersonByAddress() {
		$data = $this->ProcessInputData('getPersonByAddress',true);
		if ( $data === false ) {
			return false;
		}
		$response = $this->dbmodel->getPersonByAddress( $data );
		$this->ProcessModelList( $response, true, true )->ReturnData();
	}
	
	/**
	 * Получение истории вызовов по указанному пациенту
	 * @return boolean
	 */
	public function getPersonCallsHistory() {
		$data = $this->ProcessInputData('getPersonCallsHistory',true);
		if ( $data === false ) {
			return false;
		}
		$response = $this->dbmodel->getPersonCallsHistory( $data );
		$this->ProcessModelList( $response, true, true )->ReturnData();
	}

	/**
	 * Получение количества вызовов по указанному пациенту
	 * @return boolean
	 */
	public function getCountCallByPersonId() {
		$data = $this->ProcessInputData('getCountCallByPersonId',true);
		if ( $data === false ) {
			return false;
		}
		$response = $this->dbmodel->getCountCallByPersonId( $data );
		$this->ProcessModelList( $response, true, true )->ReturnData();
	}

}
