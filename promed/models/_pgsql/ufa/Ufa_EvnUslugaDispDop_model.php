<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Ufa_EvnUslugaDispDop_model - модель для работы с услугами дд (Уфа)
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Polka
 * @access       public
 * @copyright    Copyright (c) 2020 Swan Ltd.
 * @author       Valery Bondarev
 * @version      01.2020
 */
require_once(APPPATH . 'models/_pgsql/EvnUslugaDispDop_model.php');

class Ufa_EvnUslugaDispDop_model extends EvnUslugaDispDop_model
{
	/**
	 *    Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}


	/**
	 *    Некоторые проверки, выполняемые до сохранения услуги по доп. диспансеризации
	 *    Возвращает текст ошибки или true, если все корректно
	 */
	function beforeSaveEvnUslugaDispDop($data)
	{
		if (!empty($data['UslugaComplex_id'])) {
			// получаем категорию услуги
			$UslugaCategory_SysNick = $this->getFirstResultFromQuery("
				select
					ucat.UslugaCategory_SysNick as \"UslugaCategory_SysNick\"
				from
					v_UslugaComplex uc
					inner join v_UslugaCategory ucat on ucat.UslugaCategory_id = uc.UslugaCategory_id
				where
					uc.UslugaComplex_id = :UslugaComplex_id
				limit 1
			", array(
				'UslugaComplex_id' => $data['UslugaComplex_id']
			));

			if (!empty($UslugaCategory_SysNick) && $UslugaCategory_SysNick == 'lpusection' && empty($data['Lpu_uid']) && !empty($data['EvnUslugaDispDop_didDate'])) {
				if (empty($data['MedPersonal_id'])) {
					return 'Поле "Врач" обязательно для заполнения';
				}

				if (empty($data['LpuSection_id'])) {
					return 'Поле "Отделение" обязательно для заполнения';
				}
			}
		}

		return true;
	}
}
