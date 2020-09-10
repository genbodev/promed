<?php
/**
 * Модель Заявки на лабораторное обследование
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 */

require_once(APPPATH.'models/EvnLabRequest_model.php');
 
class Kz_EvnLabRequest_model extends EvnLabRequest_model {

	/**
	 * Список услуг
	 * @param $data
	 * @return bool|mixed
	 * @throws Exception
	 */
	function getNewEvnLabRequests($data) {
		
		$response = parent::getNewEvnLabRequests($data);
		
        $this->load->model('ExchangeBL_model');
		
		$response = array_merge($response, $this->ExchangeBL_model->getRefferalByPerson($data));
		
		return $response;
	}
}
