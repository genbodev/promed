<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Контроллер для объектов Вид медицинской помощи на отделении
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2015 Swan Ltd.
 * @author       Aleksandr Chebukin
 * @version
 */

class LpuSectionMedicalCareKind extends swController
{
	/**
	 * Конструктор
	 */
	function __construct(){
		parent::__construct();
		$this->inputRules = array(
			'loadList' => array(
				array(
					'field' => 'LpuSection_id',
					'label' => 'Идентификатор отделения',
					'rules' => 'required',
					'type' => 'int'
				)
			),
		);
		$this->load->database();
		$this->load->model('LpuSectionMedicalCareKind_model', 'model');
	}

	/**
	 * Получение списка
	 */
	function loadList() {
		$data = $this->ProcessInputData('loadList', true);
		if ($data) {
			$response = $this->model->loadList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

}