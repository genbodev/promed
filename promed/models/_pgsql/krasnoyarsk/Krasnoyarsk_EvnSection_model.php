<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* EvnSection
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package			Common
* @access			public
* @copyright		Copyright (c) 2013 Swan Ltd.
* @author			Stanislav Bykov (stanislav.bykov@rtmis.ru)
* @version			krasnoyarsk
*/

require_once(APPPATH.'models/_pgsql/EvnSection_model.php');

class Krasnoyarsk_EvnSection_model extends EvnSection_model {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * поиск ксг/кпг/коэф
	 */
	public function loadKSGKPGKOEF($data = []) {
		if (empty($data['EvnSection_setDate'])) {
			$data['EvnSection_setDate'] = date('Y-m-d');
		}

		if (empty($data['EvnSection_disDate'])) {
			$data['EvnSection_disDate'] = $data['EvnSection_setDate'];
		}

		// Используем общий алгоритм
		$this->load->model('MesOldUslugaComplex_model');
		$response = [
			'KSG' => '',
			'KPG' => '',
			'KOEF' => '',
			'Mes_tid' => null,
			'Mes_sid' => null,
			'Mes_kid' => null,
			'MesTariff_id' => null,
			'MesTariff_sid' => null,
			'Mes_Code' => '',
			'success' => true
		];

		$resp = $this->MesOldUslugaComplex_model->getKSGKPGKOEFF($data);

		if (!empty($resp['MesTariff_id'])) {
			$response['KSG'] = $resp['Mes_Code'] . '. ' . $resp['MesOld_Num'] . '. ' . $resp['Mes_Name'];
			$response['KPG'] = $resp['KPG'];
			$response['KOEF'] = round($resp['MesTariff_Value'], 3);
			$response['Mes_tid'] = $resp['Mes_tid'];
			$response['Mes_sid'] = $resp['Mes_sid'];
			$response['Mes_kid'] = $resp['Mes_kid'];
			$response['MesTariff_id'] = $resp['MesTariff_id'];
			$response['Mes_Code'] = $resp['Mes_Code'];
			$response['MesOldUslugaComplex_id'] = $resp['MesOldUslugaComplex_id'];
		}

		return $response;
	}

	/**
	 * поиск ксг/кпг/коэф
	 */
	public function loadKSGKPGKOEFCombo($data) {
		// Используем общий алгоритм
		$this->load->model('MesOldUslugaComplex_model');
		return $this->MesOldUslugaComplex_model->getKSGKPGKOEFF($data);
	}
}
