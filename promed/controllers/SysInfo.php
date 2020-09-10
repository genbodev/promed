<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* SysInfo - контроллер для вывода какой-то информации для каких-то пользователей
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      MongoDB
* @access       public
* @copyright    Copyright (c) 2011 Swan Ltd.
* @author       Markoff A.A. <markov@swan.perm.ru>
* @version      ноябрь.2013
*/

class SysInfo extends swController {
	public $inputRules = array(
		'listSession' => array(
		)
	);
	
	private $inputData = array();
	
	/**
	 * Description
	 */
	function __construct() {
		parent::__construct();
		if ( !isSuperadmin() ) {
			/*$this->ReturnData(array(
				'success' => false,
				'Error_Msg' => toUTF('У вас недостаточно прав для доступа к секретным функциям')
			));
			*/
			return false;
		}
	}
	/**
	 * Разбирает сессию в массив
	 */
	function unserialize_session($s) {
		static $tt = array(
			/*                 0  1  2 */
			/*   |    */ array(3, 1, 1),
			/*   "    */ array(1, 0, 1),
			/*   \    */ array(0, 2, 1),
			/* (rest) */ array(0, 1, 1),
		);

		static $chars = '|"\\';
		$result = array();

		$len   = strlen($s);
		$name  = '';
		$state = 0;
		$value = '';
		$what  = 'name';

		for ($i=0; $i<$len; ++$i) {
			$row = strpos($chars, $s[$i]);
			if (false === $row) {
				$row = 3;
			}

			$state = $tt[$row][$state];
			if (3 == $state) {
				if ('value' == $what) {
					for ($j=strlen($value)-1; $j>0; --$j) {
						if (';' == $value[$j] || '}' == $value[$j])  {
							$result[$name] = substr($value, 0, $j+1);
							$name = substr($value, $j+1);
							$value = '';
							break;
						}
					}
				}
				else {
					$what = 'value';
				}

				$state = 0;
			}
			else {
				$$what .= $s[$i];
			}
		}

		if ('' != $name) {
			$result[$name] = $value;
		}

		foreach ($result as $name => $value) {
			$result[$name] = unserialize($value);
		}

		return $result;
	 }
	/**
	 * Выводит список сессий 
	 */
	function listSession() {
		$this->config->load('mongodb');
		$this->load->database();
		$this->load->model('MongoDBWork_model', 'dbmodel');
		switch (checkMongoDb()) {
			case 'mongo':
				$this->load->library('swMongodb', array('config_file'=>'mongodbsessions'), 'swmongodb');
				break;
			case 'mongodb':
				$this->load->library('swMongodbPHP7', array('config_file'=>'mongodbsessions'), 'swmongodb');
				break;
			default:
				echo 'The MongoDB PECL extension has not been installed or enabled.';
				return false;
		}
		global $config;
		$list = array();
		$table = (isset($config['mongodb_session_settings']) && isset($config['mongodb_session_settings']['table']))?$config['mongodb_session_settings']['table']:'Session';
		// Получаем список сессий
		$records = $this->swmongodb->where_ne('value','eJwDAAAAAAE=')->get($table); // только залогиненные
		$i = 0;
		foreach ($records as $record) {
			$data = $this->unserialize_session(gzuncompress(base64_decode($record['value'])));
			if (is_array($data) && isset($data['pmuser_id'])) {
				$i++;
				$list[$i] = array(
					'number'=>$i, 
					'pmuser_id'=>$data['pmuser_id'],
					'login'=>$data['login'],
					'surname'=>toAnsi($data['surname']),
					'firname'=>toAnsi($data['firname']),
					'secname'=>toAnsi($data['secname']),
					'about'=>toAnsi($data['about']),
					'groups'=>str_replace('|',', ',$data['groups']),
					'org_id'=>$data['org_id'],
					'time'=>date('j.m.Y H:i:s',$record['updated']),
				);
			} else {
				//print_r($data);
			}
		}
		$count_all = $this->swmongodb->count($table);
		
		$this->load->library('parser');
		if (count($list)==0) {
			echo "Нет сессий";
		} else {
			$html = $this->parser->parse('session_list', array('sessions' => $list, 'count'=>count($list), 'count_all'=>$count_all));
		}
	}
}