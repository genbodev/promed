<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * DeviationType - модель для работы с типами отклонений
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			EMK
 * @access			public
 * @copyright		Copyright (c) 2020 Swan Ltd.
 * @author			Vaschenko Pavel (pavel.vaschenko@rtmis.ru)
 * @version			14.05.2020
 */

class DeviationType_model extends swModel {

	/**
	 * Конструктор
	 */
	function DeviationType() {
		parent::__construct();
	}

	/**
	 * Получение записи по ID
	 */
	function getDeviationTypeById ($params) {
		return $this->getFirstRowFromQuery("
			select top 1
				DeviationType_id,
				DeviationType_Code,
				DeviationType_Name
			from DeviationType with (nolock)
			where DeviationType_id = :DeviationType_id
		", $params);
	}
}
