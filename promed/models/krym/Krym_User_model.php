<?php
/**
* Krym_User_model - модель для работы с учетными записями пользователей (Крым)
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stanislav Bykov (savage@swan.perm.ru)
* @version      17.01.2014
*/

require_once(APPPATH.'models/User_model.php');

class Krym_User_model extends User_model {
    /**
     * Конструктор
     */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Дополнительное условие для отображения АРМ приемного отделения
	 * @task https://redmine.swan.perm.ru/issues/30589
	 */
	function getStacPriemAdditionalCondition($data = array()) {
		return (is_array($data) && !empty($data['LpuSectionProfile_Code']) && $data['LpuSectionProfile_Code'] == 160);
	}
}