<?php	defined('BASEPATH') or die ('No direct script access allowed');
class PersonRefuse extends swController {
	/**
	*  Описание правил для входящих параметров
	*  @var array
	*/
	public $inputRules = array();

	/**
	 * PersonRefuse constructor.
	 */
	function __construct() {
		parent::__construct();
		
		$this->load->database();
		$this->load->model("PersonRefuse_model", "dbmodel");
		
		$this->inputRules = array(
			'deletePersonRefuse' => array(
				array(
					'field' => 'PersonRefuse_id',
					'label' => 'Идентификатор отказа от льготы',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'getPersonRefuseId' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор пользователя',
					'rules' => 'required',
					'type' => 'id'
				)
			)
		);
	}

	/**
	 * @return bool
	 */
	function deletePersonRefuse() {
		$data = $this->ProcessInputData('deletePersonRefuse', true, true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->deletePersonRefuse($data);
		$this->ReturnData($response);
	}

	/**
	 * @return bool
	 */
	function getPersonRefuseId(){
		$data = $this->ProcessInputData('getPersonRefuseId', true, true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->getPersonRefuseId($data);
		$this->ReturnData($response);
	}
}
?>
