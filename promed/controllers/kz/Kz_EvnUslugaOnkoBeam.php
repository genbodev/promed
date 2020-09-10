<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Kz_EvnUslugaOnkoBeam - корректировка контроллера EvnUslugaOnkoBeam для Кз
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      MorbusOnko
 * @author       Куракин Александр
 * @version      03.2017
 * 
 */
require_once(APPPATH.'controllers/EvnUslugaOnkoBeam.php');

class Kz_EvnUslugaOnkoBeam extends EvnUslugaOnkoBeam 
{
	/**
	 * construct
	 */
	function __construct ()
	{
		parent::__construct();
		// Переопределяем обязательность полей в правилах сохранения
		foreach($this->inputRules['save'] as &$item) {
			if (in_array($item['field'], array('OnkoUslugaBeamFocusType_id'))) {
				$item['rules'] = '';
			}
		}
	}
}