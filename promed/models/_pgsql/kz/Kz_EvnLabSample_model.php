<?php
/**
 * Модель Пробы на лабораторное исследование (Казахстан)
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       Stanislav Bykov
 * @version      июль 2014
*/
require_once(APPPATH.'models/_pgsql/EvnLabSample_model.php');

class Kz_EvnLabSample_model extends EvnLabSample_model {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Получение вида оплаты по-умолчанию
	 */
	function getPayTypeSysNick() {
		return 'Resp';
	}
}
