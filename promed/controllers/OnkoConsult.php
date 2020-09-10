<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * @package			MorbusOnko
 * @access			public
 * @copyright		Copyright (c) 2018 Swan Ltd.
 *
 * @property OnkoConsult_model dbmodel
 */

class OnkoConsult extends swController {
	protected  $inputRules = array(
		'delete' => array(
			array(
				'field' => 'OnkoConsult_id',
				'label' => 'Идентификатор консилиума',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'loadList' => array(
			array(
				'field' => 'MorbusOnko_id',
				'label' => 'Идентификатор специфики',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MorbusOnkoLeave_id',
				'label' => 'Идентификатор специфики',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MorbusOnkoVizitPLDop_id',
				'label' => 'Идентификатор специфики',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MorbusOnkoDiagPLStom_id',
				'label' => 'Идентификатор специфики',
				'rules' => '',
				'type' => 'id'
			),
		),
		'load' => array(
			array(
				'field' => 'OnkoConsult_id',
				'label' => 'Идентификатор консилиума',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'save' => array(
			array(
				'field' => 'OnkoConsult_id',
				'label' => 'Идентификатор консилиума',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MorbusOnko_id',
				'label' => 'Идентификатор специфики',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MorbusOnkoVizitPLDop_id',
				'label' => 'Идентификатор специфики',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MorbusOnkoLeave_id',
				'label' => 'Идентификатор специфики',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MorbusOnkoDiagPLStom_id',
				'label' => 'Идентификатор специфики',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'OnkoConsult_consDate',
				'label' => 'Дата проведения',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'OnkoConsult_PlanDT',
				'label' => 'Планируемая дата начала лечения',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'OnkoHealType_id',
				'label' => 'Тип лечения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'OnkoConsultResult_id',
				'label' => 'Результат проведения',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'UslugaComplex_id',
				'label' => 'Услуга',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DrugTherapyScheme_id',
				'label' => 'Схема лекарственной терапии',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'OnkoChemForm_id',
				'label' => 'Вид лекарственной терапии',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'OnkoConsult_CouseCount',
				'label' => 'Количество курсов',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'OnkoConsult_Commentary',
				'label' => 'Комментарий',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Lpu_hid',
				'label' => 'МО',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedStaffFact_id',
				'label' => 'Первый врач консилиума',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedStaffFact_pid',
				'label' => 'Второй врач консилиума',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedStaffFact_rid',
				'label' => 'Третий врач консилиума',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'UslugaList',
				'label' => 'Перечень услуг',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'DrugTherapySchemeList',
				'label' => 'Перечень схем лекарственной терапии',
				'rules' => '',
				'type' => 'string'
			)
			
		),
	);

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model('OnkoConsult_model', 'dbmodel');
	}

	/**
	 * Удаление
	 */
	function delete()
	{
		$data = $this->ProcessInputData('delete');
		if ($data === false) { return false; }
		//А.И.Г. 05.12.2019 #169863
		if(getRegionNick() === 'ufa')
		{
			$response = $this->dbmodel->delete_new($data);
		}
		else
		{
			$response = $this->dbmodel->delete($data);
		}
		
		$this->ProcessModelSave($response, true, 'Ошибка при удалении')->ReturnData();
		return true;
	}

	/**
	 * Возвращает список
	 */
	function loadList()
	{
		$data = $this->ProcessInputData('loadList');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Загрузка
	 */
	function load()
	{
		$data = $this->ProcessInputData('load');
		if ($data === false) { return false; }

		$response = $this->dbmodel->load($data);

		//А.И.Г. 02.12.2019 #169863
		if($data['session']['region']['nick'] =='ufa')
		{
			//готовим массив услуг
			$arrUsluga = [];
			$result = $this->dbmodel->loadUslugaList($data);
			if(count($result) > 0)
			{
				for ($num = 0; $num < count($result); $num++) {
					$dd = $result[$num]; 
					array_push($arrUsluga, $dd['UslugaComplex_id']);
				};

				if (isset($response[0]['UslugaComplex_id']))
				{
					array_push($arrUsluga, [$response[0]['UslugaComplex_id']]);
				} 
				$response[0]['ListUsluga'] = array_unique($arrUsluga);
			}
			else
			{
				if (isset($response[0]['UslugaComplex_id']))
				{
					$response[0]['ListUsluga'] = [$response[0]['UslugaComplex_id']];
				} 
				else
				{
					$response[0]['ListUsluga'] = [];
				}
				
			}

			//готовим массив лекарственных схем
			$arrListDrugTherapyScheme = [];
			$result1 = $this->dbmodel->loadListDrugTherapySchemeList($data);
			if(count($result1) > 0)
			{
				for ($num = 0; $num < count($result1); $num++) {
					$dd = $result1[$num];
					array_push($arrListDrugTherapyScheme, $dd['DrugTherapyScheme_id']); 
				};

				if (isset($response[0]['DrugTherapyScheme_id']))
				{
					array_push($arrListDrugTherapyScheme, [$response[0]['DrugTherapyScheme_id']]);
				} 
				$response[0]['ListDrugTherapyScheme'] = array_unique($arrListDrugTherapyScheme);
			}
			else
			{
				if (isset($response[0]['DrugTherapyScheme_id']))
				{
					$response[0]['ListDrugTherapyScheme'] = [$response[0]['DrugTherapyScheme_id']];
				} 
				else
				{
					$response[0]['ListDrugTherapyScheme'] = [];
				}
				
			}
		}

		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Сохранение
	 */
	function save()
	{
		$data = $this->ProcessInputData('save');
		if ($data === false) { return false; }


		//А.И.Г. 27.11.2019 #169863
		if($data['session']['region']['nick'] =='ufa')
		{
			$data['ListUsluga'] = json_decode($data['UslugaList'], true);
			$data['ListDrugTherapyScheme'] = json_decode($data['DrugTherapySchemeList'], true);
			
			if(count($data['ListUsluga']) > 0) 
			{
				$data['ListUsluga'] = implode(',',array_unique($data['ListUsluga']));
			}
			else
			{
				$data['ListUsluga'] = '';
			}
			if(count($data['ListDrugTherapyScheme']) > 0) 
			{
				$data['ListDrugTherapyScheme'] = implode(',',array_unique($data['ListDrugTherapyScheme']));
			}
			else
			{
				$data['ListDrugTherapyScheme'] = '';
			}
			$response = $this->dbmodel->save_new($data);
		}
		else
		{
			$response = $this->dbmodel->save($data);
		}

		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}
}
