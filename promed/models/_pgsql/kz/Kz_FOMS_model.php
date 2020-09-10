<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * FOMS_model - модель
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2019 Swan Ltd.
 *
 */
require_once(APPPATH.'models/_pgsql/FOMS_model.php');

class Kz_FOMS_model extends FOMS_model {
	
	protected $_config = array();

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		set_time_limit(86400);

		ini_set("default_socket_timeout", 600);

		$this->load->library('textlog', array('file'=>'FOMS_'.date('Y-m-d').'.log'));

		$this->_config = $this->config->item('FOMS');
	}

	/**
	 * Выполнение запросов к сервису и обработка ошибок, которые возвращает сервис
	 */
	function exec($method, $type = 'get', $data = null) {
		$this->load->library('swServiceKZ', $this->config->item('FOMS'), 'swServiceFOMS');
		$this->textlog->add("exec method: $method, type: $type, data: ".print_r($data,true));
		$result = $this->swServiceFOMS->data($method, $type, $data, [
			"Cache-Control: no-cache", 
			"Pragma: no-cache",
			"Accept-Charset: UTF-8",
		]);
		$this->textlog->add("result: ".print_r($result,true));
		if (is_object($result) && !empty($result->Message)) {
			$result = array(
				'success' => false,
				'errorMsg' => 'Ошибка в работе сервиса: '.$result->Message
			);
		}
		if (is_object($result) && !empty($result->ExceptionMessage)) {
			$result = array(
				'success' => false,
				'errorMsg' => 'Ошибка в работе сервиса: '.$result->ExceptionMessage
			);
		}
		return $result;
	}

	/**
	 * Запрос данных
	 */
	function doRequest($data) {
			
		$params = [
			'requestId' => $data['Person_id'],
			'requestDate' => date('Y-m-d H:i:s'),
			'iin' => $data['Person_Inn']
		];
		
		try {
			
			$result = $this->exec('?'.http_build_query($params));
			
		} catch (Exception $e) {
			
			if (!empty($_REQUEST['getDebug'])) {
				var_dump($e);
			}
			$this->textlog->add("doRequest error: code: " . $e->getCode() . " message: " . $e->getMessage());
			return ['success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса о статусе застрахованности'];
		}
		
		if (empty($result->insuredData->insuredStatus)) {
			return ['success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса о статусе застрахованности'];
		}
		
		$Person_IsInFOMS = $result->insuredData->insuredStatus == 100 ? 2 : 1;
		
		$this->db->query("
			update Person
			set Person_IsInFOMS = :Person_IsInFOMS 
			where Person_id = :Person_id
		", [
			'Person_IsInFOMS' => $Person_IsInFOMS,
			'Person_id' => $data['Person_id']
		]);
		
		return ['success' => true, 'Person_IsInFOMS' => $Person_IsInFOMS];
	}

	/**
	 * Запрос данных пакетно
	 */
	function doRequestAuto() {
		
		$limit = $this->_config['limit']; //?? 360;
		$interval = $this->_config['interval']; //?? 5;
		
		if (!empty($_REQUEST['getDebug'])) {
			$limit = 100;
		}

		$response = $this->queryResult("
			select 
				P.Person_IsInFOMS,
				PS.Person_Inn as \"Person_Inn\",
				ttg.TimeTableGraf_id,
				ttg.TimeTableGraf_Day,
				P.Person_id as \"Person_id\"
			from Person P
				inner join v_PersonState PS on P.Person_id = PS.Person_id
				left join v_TimeTableGraf_lite ttg on P.Person_id = ttg.Person_id 
					and ttg.TimeTableGraf_Day = (select day_id from v_Day where day_date = dateadd('day', 1, cast(dbo.tzGetdate() as date))) + 1
			where 
				P.Person_IsInFOMS is null 
				and PS.Person_Inn is not null
			order by ttg.TimeTableGraf_id desc limit {$limit};
		");
		
		foreach($response as $res) {
			$this->doRequest([
				'Person_id' => $res['Person_id'],
				'Person_Inn' => $res['Person_Inn']
			]);
			
			sleep($interval);
		}
		
		return ['success' => true];
	}
}