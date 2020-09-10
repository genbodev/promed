<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnPLDisp - контроллер API для работы с осмотрами
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			API
 * @author			brotherhood of swan developers
 * @access			public
 */

require(APPPATH.'libraries/SwREST_Controller.php');

class EvnPLDisp extends SwREST_Controller {

	protected  $inputRules = array(
		'createEvnPLDispAndAgreeConsent' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'DispClass_id',
				'label' => 'Идентификатор класса диспансеризации',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'AgeGroupDisp_id',
				'label' => 'Возрастная группа',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirection_id',
				'label' => 'Идентификатор направления',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор ЛПУ',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'pmUser_id',
				'label' => 'Идентификатор пользователя',
				'rules' => 'required',
				'type' => 'id'
			)
		)
	);

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model('EvnPLDisp_model', 'dbmodel');
	}

	/**
	 * Создадим профосмотры
	 */
	function createEvnPLDispAndAgreeConsent_post() {

		$data = $this->ProcessInputData('createEvnPLDispAndAgreeConsent', null, false, false);
		$data['session'] = null;

		$this->load->model('ElectronicQueue_model');
		$result = $this->dbmodel->createEvnPLDispAndAgreeConsent(array(
			'Person_id' => $data['Person_id'],
			'DispClass_id' => $data['DispClass_id'],
			'AgeGroupDisp_id' => $data['AgeGroupDisp_id'], // возрастная группа
			'EvnDirection_id' => $data['EvnDirection_id'],
			'Lpu_id' => $data['Lpu_id'],
			'session' => $data['session'],
			'pmUser_id' => $data['pmUser_id']
		));

		if (!empty($result['Error_Msg'])) {
			$this->response(array(
				'error_msg' => $result['Error_Msg'],
				'error_code' => '6'
			));
		}

		$response_result = array('error_code' => 0, 'data' => array());
		if (!empty($result['EvnPLDispTeenInspection_id'])) {
			$response_result['data']['EvnPLDispTeenInspection_id'] = $result['EvnPLDispTeenInspection_id'];
		}

		$this->response($response_result);
	}
}