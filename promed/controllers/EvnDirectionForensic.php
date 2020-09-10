<?php

defined( 'BASEPATH' ) or die( 'No direct script access allowed' );

/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Контроллер для поручений
 *
 * @package      BSME
 * @access       public
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @author       Dmitriy Vlasenko
 * @version
 * @property EvnDirectionForensic_model EvnDirectionForensic_model
 */
class EvnDirectionForensic extends swController {

	public $inputRules = array(
		'getNextNumber' => array(
		),
		'saveEvnDirectionForensic' => array(
			array(
				'field' => 'EvnDirectionForensic_id',
				'label' => 'Поручение',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirectionForensic_Num',
				'label' => 'Номер поручения',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'EvnDirectionForensic_begDate',
				'label' => 'Дата начала экспертизы',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'EvnDirectionForensic_endDate',
				'label' => 'Дата окончания экспертизы',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'MedPersonal_id',
				'label' => 'ФИО эксперта',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnForensicType_id',
				'label' => 'Тип экспертизы',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirectionForensic_Goal',
				'label' => 'Цель экспертизы',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EvnForensic_id',
				'label' => 'Идентификатор заявки',
				'rules' => 'required',
				'type' => 'id'
			)
		)
	);

	/**
	 * 	Конструктор контроллера EvnDirectionForensic
	 */
	public function __construct() {
		parent::__construct();
		$this->load->database();
		$this->load->model( 'EvnDirectionForensic_model', 'EvnDirectionForensic_model' );
	}

	/**
	 * 	Получение номера поручения
	 */
	function getNextNumber() {
		$data = $this->ProcessInputData( 'getNextNumber', true );
		if ( $data === false ) {
			return false;
		}

		$response = $this->EvnDirectionForensic_model->getNextNumber( $data );
		$this->ProcessModelSave( $response, true )->ReturnData();

		return true;
	}

	/**
	 * 	Сохранение поручения
	 */
	function saveEvnDirectionForensic() {
		$data = $this->ProcessInputData( 'saveEvnDirectionForensic', true );
		if ( $data === false ) {
			return false;
		}

		$response = $this->EvnDirectionForensic_model->saveEvnDirectionForensic( $data );
		$this->ProcessModelSave( $response, true )->ReturnData();

		return true;
	}

}
