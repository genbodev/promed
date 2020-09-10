<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * APPBN - контроллер
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 *
 * @property APPBN_model dbmodel
 */

class APPBN extends swController {
	protected $inputRules = array(
		'getDictionary' => array(
			array(
				'field' => 'list',
				'label' => 'Список получаемых справочников',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'tableView',
				'label' => 'Признак отображения результата в табличном виде',
				'rules' => '',
				'type' => 'int'
			),
		)
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
		$this->load->database();

		set_time_limit(0);
		ini_set("max_execution_time", "0");

		$this->load->model('APPBN_model', 'dbmodel');
	}

	/**
	 * Получение справочников
	 */
	public function getDictionary() {
		$data = $this->ProcessInputData('getDictionary', true);
		if ($data === false) { return false; }

		if (!isSuperadmin()) {
			$this->ReturnError('Функционал доступен только для суперадмина');
			return false;
		}

		$response = $this->dbmodel->getDictionary($data);

		if ( empty($data['tableView']) )  {
			var_dump($response);
		}
		else {
			foreach ( $response as $key => $dict ) {
				echo '<div style="margin-bottom: 2em;">';
				echo '<div style="font-weight: bold;">', $key, '</div>';
				echo '<table style="width: 100%; border-collapse: collapse;"><tr><td style="width: 20%; font-weight: bold; border: 1px solid black;">value</td><td style="width: 80%; font-weight: bold; border: 1px solid black;">label</td></tr>';

				foreach ( $dict as $record ) {
					echo '<tr><td style="border: 1px solid black;">', $record->value, '</td><td style="border: 1px solid black;">', $record->label, '</td></tr>';
				}

				echo '</table>';
				echo '</div>';
			}
		}

		return true;
	}
}
