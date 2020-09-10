<?php
class MesTariff_model extends swModel {
	/**
	 * MesTariff_model constructor.
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Получение атрибутов МЭС (КЗГ). Метод для API.
	 */
	function getMesTariffForAPI($data) {
		$queryParams = array();
		$filter = "";

		if (!empty($data['Mes_id'])) {
			$filter .= " and mt.Mes_id = :Mes_id";
			$queryParams['Mes_id'] = $data['Mes_id'];
		}

		if (empty($filter)) {
			return array();
		}

		return $this->queryResult("
			select
				mt.Mes_id,
				mt.MesTariff_id,
				mt.MesTariff_Value,
				mt.MesPayType_id
			from
				v_MesTariff mt (nolock)
			where
				1=1
				{$filter}
		", $queryParams);
	}
}
