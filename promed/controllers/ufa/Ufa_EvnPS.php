<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * ufa_EvnPS - контроллер для работы с картами выбывшего из стационара (КВС) (Башкирия)
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Stac
 * @access			public
 * @copyright		Copyright (c) 2009 Swan Ltd.
 * @author			Stas Bykov aka Savage (savage1981@gmail.com)
 * @version			11.02.2014
 * @property EvnPS_model dbmodel
 * @property EvnSection_model EvnSection
 */

require_once(APPPATH.'controllers/EvnPS.php');

class Ufa_EvnPS extends EvnPS {
	/**
	 * Получение результата услуги
	 */
	function getEcgResult() {
		$data = $this->ProcessInputData('getEcgResult', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getEcgResult($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}
}
