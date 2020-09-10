<?php
defined('BASEPATH') or die ('No direct script access allowed');
/**
 * SearchEvnPLDispDop13 - поиск карт ДВН
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 * @author       Dmitry Vlasenko
 * @version      10.04.2018
 *
 **/
require_once('SearchBase.php');
class SearchEvnPLDispDop13 extends SearchBase {
	/**
	 * Название модели для поиска
	 */
	protected $model_name = 'SearchEvnPLDispDop13_model';
	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		// добавляем недостающие inputRules
		$this->inputRules['searchData'] = array_merge($this->inputRules['searchData'], array(
			array(
				'field' => 'EvnPLDispDop13_setDate',
				'label' => 'Дата',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'EvnPLDispDop13_setDate_Range',
				'label' => 'Диапазон дат',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'EvnPLDispDop13_disDate',
				'label' => 'Дата',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'EvnPLDispDop13_disDate_Range',
				'label' => 'Диапазон дат',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'EvnPLDispDop13_IsFinish',
				'label' => '1 этап закончен',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPLDispDop13_IsRefusal',
				'label' => 'Отказ от диспансерзации',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPLDispDop13_IsTwoStage',
				'label' => 'Направлен на 2 этап',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPLDispDop13_HealthKind_id',
				'label' => 'Группа здоровья',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPLDispDop13_isPaid',
				'label' => 'Случай оплачен',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPLDispDop13_isMobile',
				'label' => 'Случай обслужен мобильной бригадой',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'PersonDopDisp_Year',
				'label' => 'Год',
				'rules' => 'trim',
				'type' => 'int'
			),
			array(
				'field' => 'Disp_MedStaffFact_id',
				'label' => 'Врач',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Disp_LpuSection_id',
				'label' => 'Отделение',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Disp_LpuBuilding_id',
				'label' => 'Подразделение',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field'	=> 'EvnPLDisp_UslugaComplex',
				'label'	=> 'EvnPLDisp_UslugaComplex',
				'rules'	=> '',
				'type'	=> 'int'
			),
			array(
				'field' => 'FarRegistered',
				'label' => 'FarRegistered',
				'rooles' => '',
				'type' => 'boolean'
			),
			array(
				'field' => 'Person_isNotDispDopOnTime',
				'label' => 'Person_isNotDispDopOnTime',
				'rooles' => '',
				'type' => 'boolean'
			)
		));
	}
}