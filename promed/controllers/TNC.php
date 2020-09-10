<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * TNC - контроллер для работы с сервисом геолокации ТНЦ
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2015 Swan Ltd.
 * @author			Miyusov Aleksandr (miyusov@swan.perm.ru)
 * @version			02.06.2015
 *
 */

class TNC extends swController {

	/**
	* @var array Правила
	*/
	public $inputRules = array(
		'getTNCCredentialsByLpuDepartment' => array(
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор МО',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Sub_SysNick',
				'label' => 'Тип подразделения',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'LpuDepartment_id',
				'label' => 'Идентификатор подразделения ЛПУ',
				'rules' => '',
				'type' => 'int'
			)
		)
	);
	
	/**
	 * Метод-конструктор
	 */
	public function __construct() {

		parent::__construct();

		$this->load->database();
		$this->load->model( 'TNC_model', 'dbmodel' );

		$authByLoggedUser = isset($_POST['GlonassAuthByDepartment'])
			? ($_POST['GlonassAuthByDepartment'])
				? false
				: true
			: true;

		if ($authByLoggedUser)
			$result = $this->TNCloginPass();
		else {
			$data = $this->ProcessInputData('getTNCCredentialsByLpuDepartment', true);
			$result = $this->dbmodel->authByDepartment($data);
		}
	}

	/**
	 * Метод получения списка транспортных средств ТНЦ
	 */
	public function getTransportList($output = true, $filerIds = null) {

		$result = $this->dbmodel->getTransportList($output, $filerIds);

		if ($output) {
			$this->ProcessModelList($result, true, true)->ReturnData() ;
			return true;
		} else {
			return $result;
		}
	}
	
	/**
	 * Метод получения списка транспортных средств ТНЦ
	 */
	public function getTransportGroupList($output = true) {
		$result = $this->dbmodel->getTransportGroupList();

		if ($output) {
			$this->ProcessModelList( $result, true, true)->ReturnData() ;
			return true;
		} else {
			return $result;
		}
	}

	/**
	 * Авторизация
	 *
	 * @return output JSON
	 */
	public function TNCloginPass($credentials = array()){

		$this->dbmodel->TNCloginPass($credentials);
	}
}