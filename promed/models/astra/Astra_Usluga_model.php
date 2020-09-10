<?php
require_once(APPPATH.'models/Usluga_model.php');

class Astra_Usluga_model extends Usluga_model {
	/**
	 * Дополнительное условие для фильтрации списка тарифов
	 * @task https://redmine.swan.perm.ru/issues/29969
	 */
	function additionalUslugaComplexTariffCondition($record) {
		return false;
	}
}
