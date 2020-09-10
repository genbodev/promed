<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Api - контроллер API для работы с логами
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			API
 * @access			public
 * @copyright		Copyright (c) 2016 Swan Ltd.
 * @author			Dmitriy Vlasenko
 * @version			12.2016
 */

require(APPPATH.'libraries/SwREST_Controller.php');

class Logger extends SwREST_Controller {

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->checkAuth();

		if ( in_array(strtolower($this->router->method), array('evnpl', 'person')) ) {
			$this->load->database('logger');
		}
		else {
			$this->load->database();
		}

		$this->load->model('Logger_model', 'dbmodel');
		$this->inputRules = array(
			'loadPersonHistory' => array(
				array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'LogPeriod_beg', 'label' => 'Дата и время начала периода', 'rules' => '', 'type' => 'datetime'),
				array('field' => 'LogPeriod_end', 'label' => 'Дата и время окончания периода', 'rules' => '', 'type' => 'datetime'),
			),
			'loadLoggerPMI' => array(
				array('field' => 'LogPeriod_beg', 'label' => 'Дата и время начала периода', 'rules' => '', 'type' => 'datetime'),
				array('field' => 'LogPeriod_end', 'label' => 'Дата и время окончания периода', 'rules' => '', 'type' => 'datetime'),
			),
			'loadLoggerPMU' => array(
				array('field' => 'LogPeriod_beg', 'label' => 'Дата и время начала периода', 'rules' => '', 'type' => 'datetime'),
				array('field' => 'LogPeriod_end', 'label' => 'Дата и время окончания периода', 'rules' => '', 'type' => 'datetime'),
			),
			'loadIntegrationServiceEventLog' => array(
				array('field' => 'eventId', 'label' => 'Идентификатор события', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'LogPeriod_beg', 'label' => 'Дата и время начала периода', 'rules' => '', 'type' => 'datetime'),
				array('field' => 'LogPeriod_end', 'label' => 'Дата и время окончания периода', 'rules' => '', 'type' => 'datetime'),
			),
		);
	}

	/**
	 * Получение лога по человеку и МО
	 */
	function Person_get() {
		$data = $this->ProcessInputData('loadPersonHistory');

		$sp = getSessionParams();
		$data['Lpu_id'] = $sp['Lpu_id'];

		$resp = $this->dbmodel->loadPersonHistory($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение лога по паспорту медицинского изделия
	 */
	function PMI_get() {
		$data = $this->ProcessInputData('loadLoggerPMI');

		$sp = getSessionParams();
		$data['Lpu_id'] = $sp['Lpu_id'];

		$resp = $this->dbmodel->loadLoggerPMI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение лога по паспорту МО
	 */
	function PMU_get() {
		$data = $this->ProcessInputData('loadLoggerPMU');

		$sp = getSessionParams();
		$data['Lpu_id'] = $sp['Lpu_id'];

		$resp = $this->dbmodel->loadLoggerPMU($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение лога отправки событий (КВС и ТАП)
	 */
	function EvnPL_get() {
		$data = $this->ProcessInputData('loadIntegrationServiceEventLog');

		$resp = $this->dbmodel->loadIntegrationServiceEventLog($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		if(!empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}
}