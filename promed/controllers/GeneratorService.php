<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Сервис для получения идентификаторов
 */
class GeneratorService extends swController {
	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
        $this->load->database();
		$this->load->model("Generator_model");
	}

	/**
	 * Запуск сервиса
	 */
	function exec() {
		if (!isSuperAdmin()) {
			$this->ReturnError('Функционал только для суперадмина');
			return false;
		}

		$resp = $this->Generator_model->exec();

		$this->ReturnData($resp);
	}
}
