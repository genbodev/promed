<?php defined('BASEPATH') or die ('No direct script access allowed');

require(APPPATH.'libraries/SwREST_Controller.php');

/**
 * Class EvnLabSample
 * @OA\Tag(
 *     name="EvnLabSample",
 *     description="Пробы, функционал работы с пробами на портале если на регионе не включен ЛИС"
 * )
 *
 * @property EvnLabSample_model dbmodel
 * @property AsMlo_model AsMlo_model
 */
class EvnLabSample extends SwREST_Controller {
	protected  $inputRules = array(
		'saveEvnLabSampleBarcodeAndNum' => array(
			array(
				'field' => 'EvnLabSample_id',
				'label' => 'Идентификатор пробы',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnLabSample_BarCode',
				'label' => 'Номер штрих-кода',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'updateEvnLabSampleNum',
				'label' => 'признак что номер пробы тоже нужно сохранить',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'pmUser_id',
				'label' => 'идентификатор пользователя',
				'rules' => '',
				'type' => 'id'
			),
		)
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->checkAuth();

		$this->load->database();
		$this->load->model('EvnLabSample_model', 'dbmodel');
	}

	/**
	 * сохраним навый штрих-код
	 */
	function saveEvnLabSampleBarcodeAndNum_post() {

		$data = $this->ProcessInputData('saveEvnLabSampleBarcodeAndNum', null, false, false);
		if (empty($_SESSION['pmuser_id'])) {
			$_SESSION['pmuser_id'] = $data['pmUser_id'];
		}

		if (empty($data['session']['medpersonal_id'])) {
			$data['session']['medpersonal_id'] = NULL;
		}

		$resp = $this->dbmodel->saveNewEvnLabSampleBarCode($data);
		if (!empty($resp['Error_Msg'])) {
			$this->response(array(
				'error_msg' => $resp['Error_Msg'],
				'error_code' => '6'
			));
		}

		if (!empty($data['updateEvnLabSampleNum'])) {

			$data['EvnLabSample_ShortNum'] = substr($data['EvnLabSample_BarCode'], -4);
			$resp = $this->dbmodel->saveNewEvnLabSampleNum($data);

			if (!empty($resp['Error_Msg'])) {
				$this->response(array(
					'error_msg' => $resp['Error_Msg'],
					'error_code' => '6'
				));
			}
		}

		$this->response(array('error_code' => 0));
	}
}

