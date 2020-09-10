<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Контроллер для работы с очередью на проверку СНИЛС
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2011 Swan Ltd.
 * @author       Dmitry Vlasenko
 * @version
 */

class PersonSnilsQueue extends swController
{
	function __construct(){
		parent::__construct();
		$this->inputRules = array(
			'runValidation' => array(
			)
		);
		$this->load->database();
		$this->load->model('PersonSnilsQueue_model', 'dbmodel');
	}

	function runValidation() {
		$data = $this->ProcessInputData('runValidation', true);
		if ($data === false) { return false; }

		if (!isSuperAdmin()) {
			$this->ReturnError('Недостаточно прав для запуска');
			return false;
		}

		$this->dbmodel->runValidation($data);
	}


}
