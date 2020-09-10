<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* FarmacyDrugOstat - методы для работы с остатками модуля "Аптека"
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Pshenicyn Ivan aka IVP (ipshon@rambler.ru)
* @version      14.01.2010
 *
 * @property Farmacy_model $fmodel
 * @property FarmacyDrugOstat_model $dbmodel
*/

class FarmacyDrugOstat extends swController {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->inputRules = array(
			'loadDrugOatatByDate' =>array(
				array(
					'field' => 'OstatDate',
					'label' => 'Дата остатков',
					'rules' => 'required',
					'type' => 'date'
				),
					// Параметры страничного вывода
				array(
					'default' => 0,
					'field' => 'start',
					'label' => 'Номер стартовой записи',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'default' => 100,
					'field' => 'limit',
					'label' => 'Количество записей',
					'rules' => '',
					'type' => 'id'
				)
			),
			'loadDrugOstatByFilters' =>array(
				array(
					'field' => 'Contragent_id',
					'label' => 'Дата остатков',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Drug_id',
					'label' => 'Медикамент',
					'rules' => '',
					'type' => 'id'
				),
				// Параметры страничного вывода
				array(
					'default' => 0,
					'field' => 'start',
					'label' => 'Номер стартовой записи',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'default' => 100,
					'field' => 'limit',
					'label' => 'Количество записей',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DrugFinance_id',
					'label' => 'Источник финансирования',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'WhsDocumentCostItemType_id',
					'label' => 'Статья расходов',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'CLSPHARMAGROUP_ID',
					'label' => 'Идентификатор фармгруппы',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'CLSATC_ID',
					'label' => 'Идентификатор анатомо-террапевтической группы',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'CLS_MZ_PHGROUP_ID',
					'label' => 'Идентификатор фармгруппы МЗ РФ',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'STRONGGROUPS_ID',
					'label' => 'Идентификатор группы сильнодейсвующих ЛС',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'NARCOGROUPS_ID',
					'label' => 'Идентификатор группы наркотических ЛС',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'searchByDrug',
					'label' => 'Поис по торг. наименованию',
					'rules' => '',
					'type' => 'checkbox'
				)
			),
			'saveDocumentUcStrFromArray'=> array(
				array(
					'field' => 'DocumentUc_id',
					'label' => 'Документ',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'save_data',
					'label' => 'Массив данных',
					'rules' => 'required',
					'type' => 'string'
				)
			),
		);
	}

	/**
	* Получение остатков на дату
	*/
	function loadDrugOatatByDate() {
		$this->load->database();
		$this->load->model("FarmacyDrugOstat_model", "dbmodel");

		$data = array();
		$val  = array();
		
		$data = $_POST;
		$data = array_merge($data, getSessionParams());
 
		$err = getInputParams($data, $this->inputRules['loadDrugOatatByDate']);

		$val['data'] = array();
		$val['totalCount'] = 0;
		if (strlen($err) > 0) 
		{
			echo json_return_errors($err);
			return false;
		}

		$response = $this->dbmodel->loadDrugOatatByDate($data);

		if (is_array($response['data']) && (count($response['data'])>0))
		{
			foreach ($response['data'] as $row)
			{
				array_walk($row, 'ConvertFromWin1251ToUTF8');
				$row['val3'] = ($row['val3']!='')?number_format($row['val3'], 2, '.',' '):'';
				$row['val4'] = ($row['val4']!='')?number_format($row['val4'], 2, '.',' '):'';
				$row['val5'] = ($row['val5']!='')?number_format($row['val5'], 2, '.',' '):'';
				$row['val6'] = ($row['val6']!='')?number_format($row['val6'], 2, '.',' '):'';
				$row['val7'] = ($row['val7']!='')?number_format($row['val7'], 2, '.',' '):'';
				$row['val8'] = ($row['val8']!='')?number_format($row['val8'], 2, '.',' '):'';
				$row['val9'] = ($row['val9']!='')?number_format($row['val9'], 2, '.',' '):'';
				$row['val10'] = ($row['val10']!='')?number_format($row['val10'], 2, '.',' '):'';
				$row['val11'] = ($row['val11']!='')?number_format($row['val11'], 2, '.',' '):'';
				$row['val12'] = ($row['val12']!='')?number_format($row['val12'], 2, '.',' '):'';
				$row['val13'] = ($row['val13']!='')?number_format($row['val13'], 2, '.',' '):'';
				$val['data'][] = $row;				
			}
			$val['totalCount'] = $response['totalCount'];
		}
		$this->ReturnData($val);
		return true;
	}	
	
	/**
	* Получение остатков по заданным фильтрам
	*/
	function loadDrugOstatByFilters() {
		$this->load->database();
		$this->load->model("FarmacyDrugOstat_model", "dbmodel");

		$data = array();
		$val  = array();
		
		$data = $_POST;
		$data = array_merge($data, getSessionParams());

		$err = getInputParams($data, $this->inputRules['loadDrugOstatByFilters']);

		$val['data'] = array();
		$val['totalCount'] = 0;
		if (strlen($err) > 0) 
		{
			echo json_return_errors($err);
			return false;
		}

		$response = $this->dbmodel->loadDrugOstatByFilters($data);

		if (is_array($response['data']) && (count($response['data'])>0))
		{
			foreach ($response['data'] as $row)
			{
				array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val['data'][] = $row;				
			}
			$val['totalCount'] = $response['totalCount'];
		}
		$this->ReturnData($val);
		return true;
	}
	
	/**
	* Сохранение массива строк документов для списания или передачи
	*/
	function saveDocumentUcStrFromArray() {
		$this->load->database();
		$this->load->model("Farmacy_model", "fmodel");
		$this->load->model("FarmacyDrugOstat_model", "dbmodel");

		$data = array();
		$vl  = array();
		
		$data = $_POST;
		$data = array_merge($data, getSessionParams());
		$save_data = array(); 

		$err = getInputParams($data, $this->inputRules['saveDocumentUcStrFromArray']);
		if (isset($data['save_data']) && $data['save_data'] != '')
			$save_data = json_decode(trim($data['save_data']), true);
		
		if (count($save_data) > 0) {
			$ucstr_arr = array();
			foreach($save_data as $save_row) {
				$val = array();
				$save_object = array();				
				$response = $this->fmodel->loadDocumentUcStrView(array('DocumentUcStr_id' => $save_row['DocumentUcStr_id']));
				if ( is_array($response) && count($response) > 0 ) {
					$row = $response[0];
					$k = 1;
					$cnt = (isset($save_row['quantity']) && $save_row['quantity'] > 0) ? $save_row['quantity'] : $row['DocumentUcStr_Count'];
					if (isset($row['DocumentUcStr_Count']) && isset($save_row['quantity']) && $save_row['quantity'] > 0) //коофицент перерасчета показателей
						$k = $cnt/$row['DocumentUcStr_Count'];
					$save_object = array(					
						'DocumentUcStr_id' => null,
						'DocumentUcStr_oid' => $save_row['DocumentUcStr_id'],
						'DocumentUc_id' => $data['DocumentUc_id'],
						'Drug_id' => $row['Drug_id'],
						'DrugFinance_id' => $row['DrugFinance_id'],
						'DocumentUcStr_Price' => $row['DocumentUcStr_Price'],
						'DocumentUcStr_PriceR' => $row['DocumentUcStr_PriceR'],
						'DrugNds_id' => $row['DrugNds_id'],
						'DocumentUcStr_Count' => $cnt,
						'DocumentUcStr_EdCount' => $row['DocumentUcStr_EdCount'] != '' ? $row['DocumentUcStr_EdCount']*$k : null,
						'DocumentUcStr_Sum' => $row['DocumentUcStr_Sum'] != '' ? $row['DocumentUcStr_Sum']*$k : '',
						'DocumentUcStr_SumR' => $row['DocumentUcStr_SumR'] != '' ? $row['DocumentUcStr_SumR']*$k : '',
						'DocumentUcStr_godnDate' => $row['DocumentUcStr_godnDate'] ? join(array_reverse(preg_split('/[.]/',$row['DocumentUcStr_godnDate'])),'-') : '',
						'DocumentUcStr_Ser' => $row['DocumentUcStr_Ser'],
						'DocumentUcStr_NZU' => $row['DocumentUcStr_NZU'],
						'DocumentUcStr_IsLab' => $row['DocumentUcStr_IsLab'],
						'DrugProducer_id' => $row['DrugProducer_id'],
						'DrugLabResult_Name' => $row['DrugLabResult_Name'],
						'DocumentUcStr_CertNum' => $row['DocumentUcStr_CertNum'],
						'pmUser_id' => $data['pmUser_id']
					);
					$response = $this->fmodel->saveDocumentUcStr($save_object);
					if (is_array($response) && (count($response) == 1)) {
						if ( strlen($response[0]['Error_Msg']) > 0 ) {
							$this->ReturnError($response[0]['Error_Msg']);
							return true;
						}
					} else {
						$this->ReturnData(array('success' => false));
						return true;
					}
				}
			}
			$this->ReturnData(array('success' => true));
			return true;
		}

		echo json_encode($vl);
		return true;
	}
}
