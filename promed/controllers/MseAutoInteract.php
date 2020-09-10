<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* MseAutoInteract - контроллер для работы с медико-социальной экспертизой
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* @package      Mse
* @access       public
* @copyright    Copyright (c) 2019 Swan Ltd.
*/

class MseAutoInteract extends swController {

	/**
	 *	Method description
	 */
	function __construct() {
		parent::__construct();
		$this->load->model('MseAutoInteract_model', 'dbmodel');
	}
	
	/**
	 *	Method description
	 */
	function runService() {
		$data = getSessionParams();
		$data['pmUser_id'] = !empty($data['pmUser_id']) ? $data['pmUser_id'] : 1;
		$this->dbmodel->runService($data);
	}
}