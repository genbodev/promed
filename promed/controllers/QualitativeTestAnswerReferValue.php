<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Контроллер для объектов Соответствия конкретных ответов конкретному референсному значению качественного теста
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2013 Swan Ltd.
 * @author       Dmitry Vlasenko
 * @version
 * @property QualitativeTestAnswerReferValue_model QualitativeTestAnswerReferValue_model
 */

class QualitativeTestAnswerReferValue extends swController
{
	/**
	 * Конструктор
	 */
	function __construct(){
		parent::__construct();
		
		$this->inputRules = array(
			'save' => array(
				array(
					'field' => 'QualitativeTestAnswerReferValue_id',
					'label' => 'QualitativeTestAnswerReferValue_id',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'AnalyzerTestRefValues_id',
					'label' => 'Референсное значение теста',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'QualitativeTestAnswerAnalyzerTest_id',
					'label' => 'Вариант ответа',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'load' => array(
				array(
					'field' => 'QualitativeTestAnswerReferValue_id',
					'label' => 'QualitativeTestAnswerReferValue_id',
					'rules' => 'required',
					'type' => 'id'
				),
			),
			'loadList' => array(
				array(
					'field' => 'QualitativeTestAnswerReferValue_id',
					'label' => 'QualitativeTestAnswerReferValue_id',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'AnalyzerTestRefValues_id',
					'label' => 'Референсное значение теста',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'delete' => array(
				array(
					'field' => 'QualitativeTestAnswerReferValue_id',
					'label' => 'QualitativeTestAnswerReferValue_id',
					'rules' => 'required',
					'type' => 'id'
				),
			),
		);
		$this->load->database();
		$this->load->model('QualitativeTestAnswerReferValue_model', 'dbmodel');
	}

	/**
	 * Сохранение
	 */
	function save() {
		$data = $this->ProcessInputData('save', true);
		if ($data === false) return;

		$response = $this->dbmodel->save($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении Соответствия конкретных ответов конкретному качественному тесту')->ReturnData();
	}

	/**
	 * Загрузка
	 */
	function load() {
		$data = $this->ProcessInputData('load', true);
		if ($data === false) return;

		$response = $this->dbmodel->load($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Загрузка списка
	 */
	function loadList() {
		$data = $this->ProcessInputData('loadList', true);
		if ($data === false) return;

		$response = $this->dbmodel->loadList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Удаление
	 */
	function delete() {
		$data = $this->ProcessInputData('delete', true);
		if ($data === false) return;

		$response = $this->dbmodel->Delete($data);
		$this->ProcessModelSave($response, true, $response)->ReturnData();
	}
}