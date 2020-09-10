<?php defined('BASEPATH') or die('No direct script access allowed');
/**
* Person - контроллер для работы с рецептами. Версия для Уфы
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      DLO
* @access		public
* @copyright	Copyright (c) 2013 Swan Ltd.
* @author		Petukhov Ivan aka Lich (ethereallich@gmail.com)
* @version		31.05.2013
*/

require_once(APPPATH.'controllers/EvnRecept.php');
 
class Ufa_EvnRecept extends EvnRecept {

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
		
		$this->inputRules['getReceptNumber'] = array(
			array(
				'field' => 'ReceptFinance_id',
				'label' => 'Тип финансирования',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'DrugFinance_id',
				'label' => 'Тип финансирования',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnRecept_setDate',
				'label' => 'Дата выписки рецепта',
				'rules' => 'trim|required',
				'type' => 'date'
			),
            array(
                'field' => 'is_mi_1',
                'label' => 'МИ-1',
                'rules' => '',
                'type'  => 'string'
            ),
            array(
                'field' => 'EvnRecept_Ser',
                'label' => 'Серия рецепта',
                'rules' => '',
                'type'  => 'string'
            ),
			array(
				'field' => 'MinValue',
				'label' => 'MinValue',
				'rules' => '',
				'type' => 'id'
			),
                array(
				'field' => 'MaxValue',
				'label' => 'MaxValue',
				'rules' => '',
				'type' => 'id'
			)
		);
		
		$this->inputRules['getEvnReceptList4Provider'] = array(     
               array(
				'field' => 'EvnRecept_Num',
				'label' => 'Номер рецепта ("Рецепт")',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'EvnRecept_Ser',
				'label' => 'Серия рецепта ("Рецепт")',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'EvnRecept_setDate',
				'label' => 'Дата выписки ("Рецепт")',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
						'field' => 'EvnRecept_otpDate',
						'label' => 'Дата отпуска',
						'rules' => 'trim',
						'type' => 'daterange'
					),
			array(
				'field' => 'EvnRecept_setDate_Range',
				'label' => 'Диапазон дат выписки ("Рецепт")',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			 array(
				'field' => 'OrgFarmacyIndex_OrgFarmacy_id',
				'label' => 'Аптека ("Рецепт")',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Person_Firname',
				'label' => 'Имя ("Пациент")',
				'rules' => 'ban_percent|trim',
				'type' => 'russtring'
			),
			array(
				'field' => 'Person_Secname',
				'label' => 'Отчество ("Пациент")',
				'rules' => 'ban_percent|trim',
				'type' => 'russtring'
			),
			array(
				'field' => 'Person_Surname',
				'label' => 'Фамилия ("Пациент")',
				'rules' => 'ban_percent|trim',
				'type' => 'russtring'
			),
			array(
				'field' => 'Person_Birthday',
				'label' => 'Дата рождения ("Пациент")',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'default' => 0,
				'field' => 'ER_MedPersonal_id',
				'label' => 'Врач ("Рецепт")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'Drug_id',
				'label' => 'Торговое наименование ("Рецепт")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор ЛПУ',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 0,
				'field' => 'ReceptDelayType_id',
				'label' => 'Статус рецепта',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'ReceptDateType_id',
				'label' => 'Тип даты: выписка/обеспечение',
				'rules' => '',
				'type' => 'int'
			),
		   array(
				'default' => 0,
				'field' => 'start',
				'label' => 'Номер стартовой записи',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 50,
				'field' => 'limit',
				'label' => 'Количество записей',
				'rules' => '',
				'type' => 'id'
			)
        );
	}
	
	/**
	*  Сохранение рецепта для Уфы
	*  Входящие данные: ...
	*  На выходе: JSON-строка
	*  Используется: форма редактирования рецепта
	*/
	function saveEvnRecept($recept_number = NULL, $convertFromUTF8 = true) {
		$data = $this->ProcessInputData('saveEvnRecept', true);
		if ($data === false) { return false; }

		$recept_number = $this->getReceptNumber(true, false);

		if ( !empty($recept_number) ) {
			// сохраняем рецепт
			return parent::saveEvnRecept($recept_number, false);
		}
		else {
			return false;
		}
	}

	/**
	*  Получение номера рецепта для Уфы
	*  Входящие данные: нет
	*  На выходе: JSON-строка
	*  Используется: форма редактирования рецепта
	*/
	function getReceptNumber($returnValue = false, $convertFromUTF8 = true) {
		$data = $this->ProcessInputData('getReceptNumber', true, true, false, false, $convertFromUTF8);
		if ( $data === false ) { return false; }

		$this->load->helper('Options');
		$this->load->model('Options_model', 'opmodel');

		$options = $this->opmodel->getOptionsAll($data);

		$data['MaxValue'] = 0;
		$data['MinValue'] = 0;
		$val = array();

		if (!empty($data['DrugFinance_id'])) {
			$drug_finance_sysnick = $this->dbmodel->getDrugFinanceSysNick($data);

			$data['ReceptFinance_id'] = $drug_finance_sysnick == 'fed' ? 1 : 2;
		}

		if ( $data['ReceptFinance_id'] == 1 ) {
			$data['MinValue'] = $options['recepts']['evn_recept_fed_num_min'];
			$data['MaxValue'] = $options['recepts']['evn_recept_fed_num_max'];
			$data['EvnRecept_Ser'] = toAnsi($options['recepts']['evn_recept_fed_ser']);
			$data['prefix'] = "Fed";
		}
		else {
			$data['MinValue'] = $options['recepts']['evn_recept_reg_num'];
			$data['MaxValue'] = 9999999999999;
			$data['EvnRecept_Ser'] = toAnsi($options['recepts']['evn_recept_reg_ser']);
			$data['prefix'] = "Reg";
		}

		$result = $this->dbmodel->getReceptNumber($data);

		if ( is_array($result) && count($result) > 0 ) {
			$val['EvnRecept_Num'] = $result[0]['rnumber'];

			if ( (double)$val['EvnRecept_Num'] < (double)$data['MinValue'] ) {
				$val['EvnRecept_Num'] = $data['MinValue'];
			}
		}
		else {
			$val['EvnRecept_Num'] = $data['MinValue'];
		}

		if ( (double)$val['EvnRecept_Num'] > (double)$data['MaxValue'] ) {
			$this->ReturnError('Закончился диапазон номеров рецептов');
			return false;
		}
		//#123178. Новый приказ уже не требует дополнять номера региональных рецептов нулями 
		//if ( $data['ReceptFinance_id'] == 2 ) {
		//	$val['EvnRecept_Num'] = str_pad($val['EvnRecept_Num'], 13, '0', STR_PAD_LEFT);
		//}

		if ( $returnValue === true ) {
			return $val['EvnRecept_Num'];
		}
		else {
			$this->ReturnData($val);
		}

		return true;
	}
	
	/**
	 * Получить дозу из ответа
	 */
	function getDrugDose($row) {
		return (string)$row['EvnRecept_Kolvo']; // EvnRecept_Kolvo
	}
	
	/**
	 * Получить название шаблона
	 */
	function getReceptTemplateName($row, $data) {

		return parent::getReceptTemplateName($row, $data) . (($row['ReceptForm_Code']=='1-МИ')?'':'_ufa');
	}
	
	/**
	 * Получить полную дозу из ответа
	 */
	function getDrugFullDose($row) {
		return (string)$row['Drug_DoseFull'] . " №" . (string)$row['Drug_Fas'];
	}
	
	/**
	 * Получить список рецептов
	 */
	function getEvnReceptList4Provider() {
           
		$data = $this->ProcessInputData('getEvnReceptList4Provider', true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->getEvnReceptList4Provider($data, false, false);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	
	}
}
