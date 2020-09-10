<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Person - контроллер для управления людьми
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package		Common
 * @access		public
 * @copyright		Copyright (c) 2009 Swan Ltd.
 * @author		Pshenitcyn Ivan aka IvP (ipshon@rambler.ru)
 * @version		12.07.2009
 * @property Person_model dbmodel
*/
require_once(APPPATH.'controllers/Person.php');

class Saratov_Person extends Person {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Дополнительные проверки при сохранении, вынесены отдельно шаблонным методом, для переопределения в каждом регионе
	 */
	function validatePersonSaveRegional($data) {
		$this->load->model("Options_model", "opmodel");

		$snils_not_empty = $this->opmodel->getOptionsGlobals($data,'correct_data_snils_not_empty');
		$snils_empty_for_baby = $this->opmodel->getOptionsGlobals($data,'correct_data_snils_empty_for_baby');
		$snils_check_copy = $this->opmodel->getOptionsGlobals($data,'correct_data_snils_check_copy');

		// Не пустой СНИЛС и не пустой СНИЛС для детей младше 3-х лет
		$birthday = strtotime($data['Person_BirthDay']);
		if (
			$snils_not_empty == 1 && empty($data['Person_SNILS']) &&
			!($snils_empty_for_baby == 1 && !empty($data['Person_BirthDay']) && strtotime("+3 year", $birthday) > time())
		) {
			return "Поле СНИЛС обязательно для заполнения.";
		}

		// Проверка на дубли по СНИЛС в бд
		if (
			$snils_check_copy == 1  && !empty($data['Person_SNILS']) && !$this->dbmodel->checkPersonSnilsDoubles($data)
		) {
			return "Человек с введённым номером СНИЛС уже есть в базе.";
		}

		return true;
	}
}
