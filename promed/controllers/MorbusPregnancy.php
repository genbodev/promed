<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
* Morbus - Контроллер простых заболеваний.
*  - организует базовый контроль входных данных (проверка на тип и наличие обязательных),
*  - вызывает затребованные интерфейсом метода модели,
*  - вызывает вывод полученных от модели данных
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* @package      common
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @author       gabdushev
* @version      12 2011
*/

/**
 * @property Morbus_model $dbmodel 
*/
class MorbusPregnancy extends swController {
	
	/**
	 * sd
	 */
	function __construct() {
		parent::__construct();
		$this->load->database();                      //If needed, move these
		$this->load->model('MorbusPregnancy_model', 'dbmodel');  //      two lines to exact method
		$this->inputRules = array(
			
			'load' => array(
				array(
					'field' => 'MorbusPregnancy_id',
					'label' => 'Идентификатор заболевания',
					'rules' => '',
					'type' => 'id'
				)
			),
            'delete' => array(
                array(
                    'field' => 'Morbus_id',
                    'label' => 'Идентификатор заболевания',
                    'rules' => 'required',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Evn_id',
                    'label' => 'Идентификатор учетного документа',
                    'rules' => 'required',
                    'type' => 'id'
                )
            )

		);
	}

	/**
	 *
	 * @return type 
	 */
	function load(){
		$data = $this->ProcessInputData('load', true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->load($data);
		$this->ProcessModelSave($response, true, 'При загрузке специфики возникли ошибки')->ReturnData();
		return true;
	}

	/**
	 *
	 * @return type 
	 */
	function saveMorbusPregnancy()
	{
		//getMorbusOnkoSpecificsRules()['save'] не подходит, т.к. там есть данные, которые с клиента не приходят и могут быть затерты
		$this->inputRules['save'] = array(
			array(
			'field' => 'MorbusPregnancy_OutcomT',
			'label' => 'Время',
			'rules' => 'trim',
			'type' => 'string'
		),
			array(
			'field' => 'MorbusPregnancy_OutcomD',
			'label' => 'Дата исхода беременности',
			'rules' => 'trim',
			'type' => 'date'
		),array(
			'field' => 'MorbusPregnancy_CountPreg',
			'label' => 'Которая беременность',
			'rules' => 'trim',
			'type' => 'int'
		),array(
			'field' => 'MorbusPregnancy_BloodLoss',
			'label' => 'Кровопотери (мл)',
			'rules' => 'trim',
			'type' => 'int'
		),array(
			'field' => 'MorbusPregnancy_OutcomPeriod',
			'label' => 'Срок, недель',
			'rules' => 'trim',
			'type' => 'int'
		),array(
			'field' => 'MorbusPregnancyPresent',
			'label' => 'MorbusPregnancyPresent',
			'rules' => 'trim',
			'type' => 'id'
		),array(
			'field' => 'MorbusPregnancy_IsHIVtest',
			'label' => 'Обследована на ВИЧ',
			'rules' => 'trim',
			'type' => 'id'
		),array(
			'field' => 'MorbusPregnancy_IsMedicalAbort',
			'label' => 'Медикаментозный',
			'rules' => 'trim',
			'type' => 'id'
		),array(
			'field' => 'MorbusPregnancy_IsHIV',
			'label' => 'Наличие ВИЧ-инфекции',
			'rules' => 'trim',
			'type' => 'id'
		),array(
			'field' => 'BirthResult_id',
			'label' => 'Исход беременности',
			'rules' => 'trim',
			'type' => 'id'
		),array(
			'field' => 'Diag_id',
			'label' => 'Diag_id',
			'rules' => 'trim',
			'type' => 'id'
		),array(
			'field' => 'Person_id',
			'label' => 'Person_id',
			'rules' => 'trim',
			'type' => 'id'
		),array(
			'field' => 'Morbus_id',
			'label' => 'morbus_id',
			'rules' => 'trim',
			'type' => 'id'
		),array(
			'field' => 'MorbusPregnancy_id',
			'label' => 'MorbusPregnancy_id',
			'rules' => 'trim',
			'type' => 'id'
		),array(
			'field' => 'AbortType_id',
			'label' => 'Тип аборта',
			'rules' => 'trim',
			'type' => 'id'
		),array(
			'field' => 'EvnVizitPL_setDate',
			'label' => 'EvnVizitPL_setDate',
			'rules' => 'trim',
			'type' => 'date'
		),array(
			'field' => 'Evn_id',
			'label' => 'Evn_id',
			'rules' => 'trim',
			'type' => 'id'
		)
			
		);
		
		$data = $this->ProcessInputData('save', true);
		if ($data) {
			$response = $this->dbmodel->saveMorbusPregnancy($data);
			$this->ProcessModelSave($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
}