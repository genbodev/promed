<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Kz_EvnOnkoNotifyNeglected - корректировка контроллера формы "Протокол запущенной формы онкозаболевания" для Кз
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      MorbusOnko
 * @author       Куракин Александр
 * @version      03.2017
 * 
 * @property EvnOnkoNotifyNeglected_model EvnOnkoNotifyNeglected
 */
require_once(APPPATH.'controllers/EvnOnkoNotifyNeglected.php');

class Kz_EvnOnkoNotifyNeglected extends EvnOnkoNotifyNeglected 
{
	/**
	 * construct
	 */
	function __construct ()
	{
		parent::__construct();
		// Переопределяем обязательность полей в правилах сохранения
		foreach($this->inputRules['save'] as &$item) {
			if (in_array($item['field'], array('OnkoLateDiagCause_id'))) {
				$item['rules'] = '';
			}
			if (
				in_array(
					$item['field'],
					array(
						'EvnOnkoNotifyNeglected_setFirstDT',
						'EvnOnkoNotifyNeglected_setFirstTreatmentDT',
						'Lpu_fid',
						'EvnOnkoNotifyNeglected_setFirstZODT',
						'Lpu_zid',
						'MedPersonal_id'
					)
				)
			) 
			{
				$item['rules'] = 'required';
			}
		}
	}
}