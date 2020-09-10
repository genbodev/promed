<?php	defined('BASEPATH') or die ('No direct script access allowed');
class OftenCallers extends swController
{
	public $inputRules = array(
		'getOftenCallers' => array(
			array(
				'field' => 'Lpu_id',
				'label' => 'МО',
				'rules' => '',
				'type' => 'id'
			)
		),
		'deleteFromOftenCallers' => array(
			array(
				'field' => 'OftenCallers_ids',
				'label' => 'Ид. записей регистра',
				'rules' => '',
				'type' => 'string'
			)
		)
	);

	/**
	 * OftenCallers constructor.
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model('OftenCallers_model', 'dbmodel');
	}

	/**
	 * Некая функция
	 */
	function getOftenCallers()
	{
		$data = $this->ProcessInputData('getOftenCallers', true);
		$response = $this->dbmodel->getOftenCallers($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}

	/**
	 * @return bool
	 */
	function deleteFromOftenCallers()
	{
		$OftenCallers_ids = array();

		$data = $this->ProcessInputData('deleteFromOftenCallers', true);

		if ($data) {
			if (!empty($data['OftenCallers_ids'])) {
				$OftenCallers_ids = json_decode($data['OftenCallers_ids']);
				$data['OftenCallers_ids'] = $OftenCallers_ids;

				$response = $this->dbmodel->deleteFromOftenCallers($data);
				$this->ProcessModelSave($response, true, true)->ReturnData();

				return true;
			}
		}
	}
}

?>
