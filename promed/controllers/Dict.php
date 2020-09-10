<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Dict - получение справочников для обертки их в SOAP для взаимодействия со
 * сторонним ПО
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2009 Swan Ltd.
 * @author			Ivan Petukhov aka Lich (ethereallich@gmail.com)
 * @version			1
 */
 
/**
 * Наследуемся от Controller, так как авторизация не нужна
 */
class Dict extends swController {
	
	var $NeedCheckLogin = false;
	
	public $inputRules = array(
		'dictList' => array(
		),
		'dictContent' => array(
			array(
				'field' => 'name',
				'label' => 'Наименование справочника',
				'rules' => 'required',
				'type' => 'string'
			)
		)
	);
	
	/**
	 * Применение правил
	 */
	function applyRules($name) {
		$data = array();
		$err = getInputParams($data, $this->inputRules[$name]);
		if ($err != "") {
			echo json_return_errors($err);
		} else {
			return $data;
		}
	}

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
		
		$this->load->database( 'default' );
        $this->load->model('Dict_model','dictmodel');
	}

	/**
	 * Description
	 */
	function index() {
		$this->dictList();
	}

	/**
	 * Получение списка справочников
	 */
	function dictList() {
		$response = $this->dictmodel->dictList($this->applyRules('dictList'));
		if (is_array($response) && count($response) > 0) {
			foreach ($response as $row) {
				array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val[] = $row;
			}
			$json=json_encode($val);
			echo $json;
		} else {
			$this->ReturnData($val);
		}
	}
	
	/**
	 * Получение содержимого справочника
	 */
	function dictContent() {
		$val = array();
		$response = $this->dictmodel->dictContent($this->applyRules('dictContent'));
		//print_r($response['data']);
		if (is_array($response) && count($response) > 0) {
			foreach ($response['data'] as $row) {
				array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val['data'][] = $row;
			}
			$val['desc'] = toUTF($response['desc']);
			$json = json_encode($val);
			echo $json;
		} else {
			$val = array(
				'success' => false,
				'Error_Msg' => toUTF('Справочник не существует.')
			);
			$this->ReturnData($val);
		}
	}

}
?>