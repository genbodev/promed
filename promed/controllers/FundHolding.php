<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* FundHolding - контроллер для фондодержания.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2009 - 2011 Swan Ltd.
* @author       Pshenitcyn Ivan aka IvP (ipshon@rambler.ru)
* @version      05.06.2011
*/

class FundHolding extends swController {
	/**
	 * Description
	 */
	function __construct()
	{
		parent::__construct();
		$this->inputRules = array(
			'getTotalsCalculation' => array(
				array(
					'default' => 'lpu',
					'field' => 'CalcType',
					'label' => 'Тип расчета',
					'rules' => 'trim|required',
					'type' => 'string'
				),
				array(
					'default' => '[]',
					'field' => 'RegionTypes',
					'label' => 'Выбранные типы регионов',
					'rules' => 'trim|required',
					'type' => 'string'
				),
				array(
					'default' => '[]',
					'field' => 'Regions',
					'label' => 'Выбранные участки',
					'rules' => 'trim|required',
					'type' => 'string'
				),
				array(
					'field' => 'Month',
					'label' => 'Отчетный месяц',
					'rules' => 'trim|required',
					'type' => 'int'
				),
				array(
					'field' => 'Year',
					'label' => 'Отчетный год',
					'rules' => 'trim|required',
					'type' => 'int'
				)
			),
			'getFundHoldingGrid' => array(
				array(
					'default' => 'lpu',
					'field' => 'CalcType',
					'label' => 'Тип расчета',
					'rules' => 'trim|required',
					'type' => 'string'
				),
				array(
					'default' => '[]',
					'field' => 'RegionTypes',
					'label' => 'Выбранные типы регионов',
					'rules' => 'trim|required',
					'type' => 'string'
				),
				array(
					'default' => '[]',
					'field' => 'Regions',
					'label' => 'Выбранные участки',
					'rules' => 'trim|required',
					'type' => 'string'
				),
				array(
					'field' => 'Month',
					'label' => 'Отчетный месяц',
					'rules' => 'trim|required',
					'type' => 'int'
				),
				array(
					'field' => 'Year',
					'label' => 'Отчетный год',
					'rules' => 'trim|required',
					'type' => 'int'
				),
				array(
					'field' => 'EvnIsFinish',
					'label' => '',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'EvnType',
					'label' => '',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'FundHolder',
					'label' => '',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'Person_Surname',
					'label' => '',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Person_Firname',
					'label' => '',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Person_Secname',
					'label' => '',
					'rules' => 'trim',
					'type' => 'string'
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
			)
		);
	}
	
	/**
	 * Функция для проверки существования данных для выбранных условий расчета
	 */
	function checkIfReestrDataExists() {
		$this->load->database('bdreports', false);
		$this->load->model("FundHolding_model", "pcmodel");
		$data = array();
		$this->load->helper('Text');
		$err = getInputParams($data, $this->inputRules['getTotalsCalculation']);
		if ($err != "")
		{
			echo json_return_errors($err);
			return false;
		}
		$data = array_merge($data, getSessionParams());
		$info = $this->pcmodel->checkIfReestrDataExists($data);
		if ( $info === false )
			$response = array(
				'success' => false, 
				'data' => toUTF($info),
				'Error_Msg' => toUTF('Не проверить наличие данных реестров. Обратитесь к администратору или повторите попытку позже.'),
				'cancelErrorHandle' => true
			);
		else
			$response = array(
				'success' => true, 
				'data' => $info,
				'Error_Msg' => null,
				'cancelErrorHandle' => true
			);
		echo json_encode($response);
	}
	
	/**
	 *  Метод получения данных для построения графика
	 */
	function getChartData() {
		$this->load->database('bdreports', false);
		$this->load->model("FundHolding_model", "pcmodel");
		$data = array();
		$this->load->helper('Text');
		$err = getInputParams($data, $this->inputRules['getTotalsCalculation']);
		if ($err != "")
		{
			echo json_return_errors($err);
			return false;
		}
		$data = array_merge($data, getSessionParams());
		$info = $this->pcmodel->getChartData($data);
		if ( $info === false )
			$response = array(
				'success' => false, 
				'data' => toUTF($info),
				'Error_Msg' => toUTF('Не elfkjcполучить данные реестров. Обратитесь к администратору или повторите попытку позже.'),
				'cancelErrorHandle' => true
			);
		else
			$response = array(
				'success' => true, 
				'data' => $info,
				'Error_Msg' => null,
				'cancelErrorHandle' => true
			);
		echo json_encode($response);
	}
	
	/**
	 *  Метод получения данных для построения годового графика
	 */
	function getYearChartData() {
		$this->load->database('bdreports', false);
		$this->load->model("FundHolding_model", "pcmodel");
		$data = array();
		$this->load->helper('Text');
		$err = getInputParams($data, $this->inputRules['getTotalsCalculation']);
		if ($err != "")
		{
			echo json_return_errors($err);
			return false;
		}
		$data = array_merge($data, getSessionParams());
		$info = $this->pcmodel->getYearChartData($data);
		if ( $info === false )
			$response = array(
				'success' => false, 
				'data' => toUTF($info),
				'Error_Msg' => toUTF('Не elfkjcполучить данные реестров. Обратитесь к администратору или повторите попытку позже.'),
				'cancelErrorHandle' => true
			);
		else
			$response = array(
				'success' => true, 
				'data' => $info,
				'Error_Msg' => null,
				'cancelErrorHandle' => true
			);
		echo json_encode($response);
	}
	
	/**
	*  Функция для расчета Фин. результата
	*  Входящие данные: $_POST с фильтрами
	*  На выходе: JSON-строка
	*  Используется: форма фондодержания
	*/
	function getTotalsCalculation()
	{		
		//$this->load->database();
		$this->load->database('bdreports', false);
		$this->load->model("FundHolding_model", "pcmodel");
		$data = array();
		$this->load->helper('Text');
		$err = getInputParams($data, $this->inputRules['getTotalsCalculation']);
		if ($err != "")
		{
			echo json_return_errors($err);
			return false;
		}
		$data = array_merge($data, getSessionParams());
		$info = $this->pcmodel->getTotalsCalculation($data);
		$val = array();
		$count = 0;
   		
		if ( $info === false )
			$response = array(
				'success' => false, 
				'data' => null,
				'Error_Msg' => toUTF('Не удалось расчитать сводку. Обратитесь к администратору или повторите попытку позже.'),
				'cancelErrorHandle' => true 
			);
		else
		{
			$response = array(
				'success' => false, 
				'data' => toUTF($info),
				'Error_Msg' => toUTF('Не удалось расчитать сводку. Обратитесь к администратору или повторите попытку позже.'),
				'cancelErrorHandle' => true
			);
		}
		
		echo json_encode($response);
	}
	
	/**
	*  Функция для расчета Фин. результата
	*  Входящие данные: $_POST с фильтрами
	*  На выходе: JSON-строка
	*  Используется: форма фондодержания
	*/
	function getTotalsCalculationReestr()
	{		
		//$this->load->database();
		$this->load->database('bdreports', false);
		$this->load->model("FundHolding_model", "pcmodel");
		$data = array();
		$this->load->helper('Text');
		$err = getInputParams($data, $this->inputRules['getTotalsCalculation']);
		if ($err != "")
		{
			echo json_return_errors($err);
			return false;
		}
		$data = array_merge($data, getSessionParams());
		$info = $this->pcmodel->getTotalsCalculationReestr($data);
		$val = array();
		$count = 0;
   		
		if ( $info === false )
			$response = array(
				'success' => false, 
				'data' => null,
				'Error_Msg' => toUTF('Не удалось расчитать сводку. Обратитесь к администратору или повторите попытку позже.'),
				'cancelErrorHandle' => true 
			);
		else
		{
			$response = array(
				'success' => false, 
				'data' => toUTF($info),
				'Error_Msg' => toUTF('Не удалось расчитать сводку. Обратитесь к администратору или повторите попытку позже.'),
				'cancelErrorHandle' => true
			);
		}
		
		echo json_encode($response);
	}
	
	/**
	 * Description
	 */
	function getFundHoldingGrid()
	{
		
		//$this->load->database();
		$this->load->database('bdreports', false);
		$this->load->model("FundHolding_model", "pcmodel");
		$data = array();
		$this->load->helper('Text');
		$err = getInputParams($data, $this->inputRules['getFundHoldingGrid']);
		if ($err != "")
		{
			echo json_return_errors($err);
			return false;
		}
		$data = array_merge($data, getSessionParams());
		$response = $this->pcmodel->getFundHoldingGrid($data);

		if ( is_array($response) ) {
			if ( (isset($response['Error_Msg'])) && (strlen($response['Error_Msg']) > 0) ) {
				$val = $response;
				array_walk($val, 'ConvertFromWin1251ToUTF8');
			}
			else if ( isset($response['data']) ) {
				$val['data'] = array();
				foreach ( $response['data'] as $row ) {
					array_walk($row, 'ConvertFromWin1251ToUTF8');
					$val['data'][] = $row;
				}
				$val['totalCount'] = $response['totalCount'];
			}
		}
		else
		{
			echo json_return_errors('Проблема выполнения запроса к БД.');
			return false;
		}
		$this->ReturnData($val);

		return true;		
	}
	
	/**
	 * Description
	 */
	function getFundHoldingGridReestr()
	{
		
		//$this->load->database();
		$this->load->database('bdreports', false);
		$this->load->model("FundHolding_model", "pcmodel");
		$data = array();
		$this->load->helper('Text');
		$err = getInputParams($data, $this->inputRules['getFundHoldingGrid']);
		if ($err != "")
		{
			echo json_return_errors($err);
			return false;
		}
		$data = array_merge($data, getSessionParams());
		$response = $this->pcmodel->getFundHoldingGridReestr($data);

		if ( is_array($response) ) {
			if ( (isset($response['Error_Msg'])) && (strlen($response['Error_Msg']) > 0) ) {
				$val = $response;
				array_walk($val, 'ConvertFromWin1251ToUTF8');
			}
			else if ( isset($response['data']) ) {
				$val['data'] = array();
				foreach ( $response['data'] as $row ) {
					array_walk($row, 'ConvertFromWin1251ToUTF8');
					$val['data'][] = $row;
				}
				$val['totalCount'] = $response['totalCount'];
			}
		}
		else
		{
			echo json_return_errors('Проблема выполнения запроса к БД.');
			return false;
		}
		$this->ReturnData($val);

		return true;		
	}
}

?>