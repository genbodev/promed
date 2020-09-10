<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Контроллер для объектов Услуги по МЭСам
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2011 Swan Ltd.
 * @author       gabdushev
 * @version
 * @property MesUsluga_model MesUsluga_model
 */
class MesUsluga extends swController
{
	/**
	 * Конструктор
	 */
	function __construct(){
		parent::__construct();
		$this->inputRules = array(
			'save' => array(
				array(
					'field' => 'MesUsluga_id',
					'label' => 'MesUsluga_id',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'Usluga_id',
					'label' => 'Услуга',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'UslugaComplex_id',
					'label' => 'Комплексная услуга',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Mes_id',
					'label' => 'МЕС',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'MesUsluga_UslugaCount',
					'label' => 'Количество услуг',
					'rules' => 'required',
					'type' => 'float'
				),
				array(
					'field' => 'MesUsluga_begDT',
					'label' => 'Дата начала действия',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'MesUsluga_endDT',
					'label' => 'Дата окончания действия',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'curARMType',
					'label' => 'ткущий АРМ пользователя',
					'rules' => 'trim',
					'type' => 'string'
				)
			),
			'load' => array(
				array(
					'field' => 'MesUsluga_id',
					'label' => 'MesUsluga_id',
					'rules' => 'required',
					'type' => 'int'
				),
			),
			'loadList' => array(
				array(
					'field' => 'MesUsluga_id',
					'label' => 'MesUsluga_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Usluga_id',
					'label' => 'Услуга',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'UslugaComplex_id',
					'label' => 'Комплексная услуга',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Mes_id',
					'label' => 'МЕС',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'MesUsluga_UslugaCount',
					'label' => 'Количество услуг',
					'rules' => '',
					'type' => 'float'
				),
				array(
					'field' => 'MesUsluga_begDT',
					'label' => 'Дата начала действия',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'MesUsluga_endDT',
					'label' => 'Дата окончания действия',
					'rules' => 'trim',
					'type' => 'date'
				),
			),
			'delete' => array(
				array(
					'field' => 'MesUsluga_id',
					'label' => 'MesUsluga_id',
					'rules' => 'required',
					'type' => 'int'
				),
			),
		);
		$this->load->database();
		$this->load->model('MesUsluga_model', 'MesUsluga_model');
	}

	/**
	 * Сохранение
	 */
	function save() {
		if (!isset($_SESSION['groups_array'])){
			$_SESSION['groups_array'] = explode('|', $_SESSION['groups']);
		}
		$data = $this->ProcessInputData('save', true);
		if ( $data === false ) { return false; }
		
		$region = getRegionNick();
		$save_access = false;
		$curARMType = (!empty($data['curARMType'])) ? $data['curARMType'] : $_SESSION['CurArmType'];
		//Доступ для добавления / редактирования МЭС имеют пользователи с указанными группами:
		if($region=='vologda' || $region == 'buryatiya'){
			if( ($curARMType == 'superadmin' && havingGroup('OuzChief')) || ($curARMType == 'mstat' && havingGroup('EditingMES')) ) {
				$save_access = true;
			}
		}else if( havingGroup('OuzChief') || havingGroup('OuzUser') || havingGroup('OuzAdmin') ){
			//- Руководитель ОУЗ, Пользователь ОУЗ, Администратор ОУЗ
			$save_access = true;
		}
		
		if ($save_access) {
			if ($data){
				if (isset($data['MesUsluga_id'])) {
					$this->MesUsluga_model->setMesUsluga_id($data['MesUsluga_id']);
				}
				if (isset($data['Usluga_id'])) {
					$this->MesUsluga_model->setUsluga_id($data['Usluga_id']);
				}
				if (isset($data['UslugaComplex_id'])) {
					$this->MesUsluga_model->setUslugaComplex_id($data['UslugaComplex_id']);
				}
				if (isset($data['Mes_id'])) {
					$this->MesUsluga_model->setMes_id($data['Mes_id']);
				}
				if (isset($data['MesUsluga_UslugaCount'])) {
					$this->MesUsluga_model->setMesUsluga_UslugaCount($data['MesUsluga_UslugaCount']);
				}
				if (isset($data['MesUsluga_begDT'])) {
					$this->MesUsluga_model->setMesUsluga_begDT($data['MesUsluga_begDT']);
				}
				if (isset($data['MesUsluga_endDT'])) {
					$this->MesUsluga_model->setMesUsluga_endDT($data['MesUsluga_endDT']);
				}
				$response = $this->MesUsluga_model->save();
				$this->ProcessModelSave($response, true, 'Ошибка при сохранении Услуги по МЭСам')->ReturnData();
				return true;
			} else {
				return false;
			}
		} else {
			throw new Exception('Access denied');
		}


	}

	/**
	 * Загрузка
	 */
	function load() {
		$data = $this->ProcessInputData('load', true);
		if ($data){
			$this->MesUsluga_model->setMesUsluga_id($data['MesUsluga_id']);
			$response = $this->MesUsluga_model->load();
			$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка списка
	 */
	function loadList() {
		$data = $this->ProcessInputData('loadList', true);
		if ($data) {
			$filter = $data;
			$response = $this->MesUsluga_model->loadList($filter);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Удаление
	 */
	function delete() {
		if (!isset($_SESSION['groups_array'])){
			$_SESSION['groups_array'] = explode('|', $_SESSION['groups']);
		}
		if (                                                            //Доступ для добавления / редактирования МЭС имеют пользователи с указанными группами:
			in_array('OuzChief', $_SESSION['groups_array']) ||          //- Руководитель ОУЗ
			in_array('OuzUser', $_SESSION['groups_array']) ||           //- Пользователь ОУЗ
			in_array('OuzAdmin', $_SESSION['groups_array'])             //- Администратор ОУЗ
		) {
			$data = $this->ProcessInputData('delete', true, true);
			if ($data) {
				$this->MesUsluga_model->setMesUsluga_id($data['MesUsluga_id']);
				$response = $this->MesUsluga_model->Delete();
				$this->ProcessModelSave($response, true, $response)->ReturnData();
				return true;
			} else {
				return false;
			}
		} else {
			throw new Exception('Access denied');
		}
	}
}