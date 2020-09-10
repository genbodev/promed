<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * LisScenario - Контроллер включает общие методы сохранения и загрузки
 * 
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @author       Magafurov Salavat (emsis.magafurov@gmail.com)
 * @version      01.07.2019
 */
require_once('Scenario.php');
abstract class LisScenario extends Scenario {
	public $auto_load_model = false;
	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		if (empty($this->model_name)) {
			return new Exception('Не задана модель ($model_name) в контроллере');
		}
	}

	/**
	 * Инициализация модели
	 */
	protected function initModel()
	{
		if ($this->usePostgreLis) {
			$this->usePostgre = true;
			$this->db = $this->load->database('lis', true);
		} else {
			$this->load->database();
		}

		$this->load->model($this->model_name, 'dbmodel');
	}
}