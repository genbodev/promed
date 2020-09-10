<?php
/**
 * Person_model - модель, для работы с таблицей Person (Казахстан)
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2016 Swan Ltd.
 * @author       Stanislav Bykov (savage@swan.perm.ru)
 * @version      27.01.2016
 */
require_once(APPPATH.'models/_pgsql/Person_model.php');

class Kz_Person_model extends Person_model {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Проверка активности территории полиса
	 * Для Казахстана проверка не предусмотрена
	 */
	function checkOMSSprTerrDate($data) {
		return true;
	}
}