<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * DopDispQuestion - контроллер для управления талонами по диспансеризации взрослого населения
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			DLO
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Dmitry Vlasenko
 * @originalauthor	Petukhov Ivan aka Lich (megatherion@list.ru) / Stas Bykov aka Savage (savage1981@gmail.com)
 * @version			16.05.2013
 */

class DopDispQuestion extends swController
{
	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->load->database();
		$this->load->model('DopDispQuestion_model', 'dbmodel');

		$this->inputRules = array(
			'loadDopDispQuestionGrid' => array(
				array(
					'field' => 'EvnPLDisp_id',
					'label' => 'Идентификатор талона по доп. диспансеризации',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'getTemplateForPrint' => array(
				array(
					'field' => 'EvnPLDisp_id',
					'label' => 'Идентификатор талона по доп. диспансеризации',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadDopDispQuestionEditWindow' => array(
				array(
					'field' => 'EvnUslugaDispDop_id',
					'label' => 'Идентификатор услуги по доп. диспансеризации',
					'rules' => 'required',
					'type' => 'id'
				)
			)
		);
	}

	/**
	 *  Получение списка вопросов для анкетирования
	 *  Входящие данные: EvnPLDisp_id
	 */
	function loadDopDispQuestionGrid() {
		$data = $this->ProcessInputData('loadDopDispQuestionGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadDopDispQuestionGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 *  Получение шаблона для печати
	 *  Входящие данные: EvnPLDisp_id
	 */
	function getTemplateForPrint() {
		$data = $this->ProcessInputData('getTemplateForPrint', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getTemplateForPrint($data);
		$this->ProcessModelSave($response, true, 'Ошибка получения шаблона для печати')->ReturnData();
	}

	/**
	 *  Получение списка формы редактирования анкетирования
	 *  Входящие данные: EvnUslugaDispDop_id
	 */
	function loadDopDispQuestionEditWindow() {
		$data = $this->ProcessInputData('loadDopDispQuestionEditWindow', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadDopDispQuestionEditWindow($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
}
?>