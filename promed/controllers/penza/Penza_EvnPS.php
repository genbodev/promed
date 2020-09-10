<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * penza_EvnPS - контроллер для работы с картами выбывшего из стационара (КВС) (Пенза)
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Stac
 * @access			public
 * @copyright		Copyright (c) 2009 Swan Ltd.
 * @author			Stanislav Bykov (savage@swan.perm.ru)
 * @version      	04.09.2017
 * @region       	Пенза
 *
 * @property User_model dbmodel
 */

require_once(APPPATH.'controllers/EvnPS.php');

class Penza_EvnPS extends EvnPS {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->inputRules['exportHospDataForTfomsToXml'][] = array(
			'field' => 'Period',
			'label' => 'Отчетный период',
			'rules' => 'required',
			'type' => 'daterange'
		);
	}

	/**
	 * Выгрузка данных для ТФОМС и СМО
	 */
	public function exportHospDataForTfomsToXml() {
		set_time_limit(0);

		$data = $this->ProcessInputData('exportHospDataForTfomsToXml', true);
		if ($data === false) { return false; }

		if ( !isSuperAdmin() ) {
			$data['ExportLpu_id'] = $data['session']['lpu_id'];
		}

		$this->load->library('parser');

		$response = $this->dbmodel->exportHospDataForTfomsToXml($data);

		$this->ReturnData($response);

		return true;
	}
}