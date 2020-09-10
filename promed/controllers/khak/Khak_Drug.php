<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Drug - операции с медикаментами
* Заодно тут же работа с аптеками
* Вынесено из dlo_ivp.php
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      DlO
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Petukhov Ivan aka Lich (megatherion@list.ru)
* @originalauthor       Pshenitcyn Ivan aka IvP (ipshon@rambler.ru)
* @version      23.07.2009
*/
require_once(APPPATH.'controllers/Drug.php');

class Khak_Drug extends Drug {
	/**
	 * Khak_Drug constructor.
	 */
	function __construct() {
		parent::__construct();
	}
	
	/**
	* Включение и выключение аптек
	*/
	function vklOrgFarmacy() {
		$data = $this->ProcessInputData('vklOrgFarmacy', true);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->vklOrgFarmacy($data);
		$this->ProcessModelSave($response, true)->ReturnData();
	}
}
