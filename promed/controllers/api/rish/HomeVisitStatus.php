<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Api - контроллер API для работы с расписанием
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			API
 * @access			public
 * @copyright		Copyright (c) 2016 Swan Ltd.
 * @author			Alexander Kurakin
 * @version			11.2016
 */

require(APPPATH.'libraries/SwREST_Controller.php');

class HomeVisitStatus extends SwREST_Controller {
	protected  $inputRules = array(
		'HomeVisitStatus' => array(
			array('field' => 'HomeVisitStatus_id', 'label' => 'Идентификатор статуса вызова', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'MedStaffFact_id', 'label' => 'Идентификатор места работы врача', 'rules' => '', 'type' => 'id'),
			array('field' => 'HomeVisit_id', 'label' => 'Идентификатор вызова на дом', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'HomeVisit_LpuComment', 'label' => 'Причина отмены', 'rules' => '', 'type' => 'string'),
			array('field' => 'platform', 'label' => 'Источник записи', 'rules' => '', 'type' => 'id')
		)
	);

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('HomeVisit_model', 'dbmodel');
	}

	/**
	 * Изменение статуса записи в листе ожидания
	 *
	 * @desсription
	 * {
	 * 		"output_params": {
	 * 			"error_code": "Код ошибки"
	 * 		},
	 * 		"example": {
	 * 			"error_code": 0
	 * 		}
	 * }
	 */
	function HomeVisitStatus_put($mobile = false) {

		$data = $this->ProcessInputData('HomeVisitStatus', null, true);

		if($mobile){// вызыван из mSetHomeVisitStatus_post
			if(empty($data['platform'])){
				$data['HomeVisitSource_id'] = 2;// Мобильное приложение: не определено
			}elseif(in_array((int)$data['platform'], array(6, 7))){// 6 - iOS, 7 - Android
				$data['HomeVisitSource_id'] = $data['platform'];
			}
		}
		else{
			$data['HomeVisitSource_id'] = 12;// источник записи - РИШ, справочник HomeVisitSource
		}

		$resp = $this->dbmodel->setHomeVisitStatus($data);
		if ($resp) $this->dbmodel->saveHomeVisitStatusHist($data);

		$this->response(array('error_code' => 0));
	}

	/**
	 *  Изменение статуса вызова
	 */
	function mSetHomeVisitStatus_post(){
		$this->HomeVisitStatus_put(true);
	}
}