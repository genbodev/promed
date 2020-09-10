<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Контроллер для сервиса Lis
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @author       Dmitriy Vlasenko
 * @version
 */
class LisService extends swController
{
	var $NeedCheckLogin = false; // авторизация не нужна
	
	/**
	 *	Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->inputRules = array(
		);
		$this->load->helper('Xml');
		$this->load->model('Options_model', 'Options_model');
		$dbres = $this->Options_model->getDataStorageValues(array('DataStorageGroup_SysNick'=>'lis'), array());
		$options = array();
		foreach($dbres as $value) {
			$options[$value['DataStorage_Name']] = $value['DataStorage_Value'];
		}
		$this->server = array(
			'address'     => $options['lis_address'    ],
			'server'      => $options['lis_server'     ],
			'port'        => $options['lis_port'       ],
			'path'        => $options['lis_path'       ],
			'version'     => $options['lis_version'    ],
			'buildnumber' => $options['lis_buildnumber'],
		);

		if ($this->usePostgreLis) {
			$this->load->swapi('lis');
		} else {
			$this->load->database();
			$this->load->model('Lis_model', 'dbmodel');
		}
	}
	
	/**
	 * Получение результатов из ЛИС по всем отправленным пробам без результата
	 */
	function checkLisLabSamples()
	{
		if ($this->usePostgreLis) {
			$response = $this->lis->GET('Lis/checkLabSamples');
			$this->ProcessRestResponse($response, 'single')->ReturnData();
		} else {
			$this->dbmodel->checkLisLabSamples();
		}
	}
}