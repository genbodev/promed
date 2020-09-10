<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * DeviationWeight - модель для работы с отклонениями веса
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			EMK
 * @access			public
 * @copyright		Copyright (c) 2020 Swan Ltd.
 * @author			Vaschenko Pavel (pavel.vaschenko@rtmis.ru)
 * @version			15.05.2020
 */

class DeviationWeight_model extends swModel {

	/**
	 * Конструктор
	 */
	function DeviationWeight() {
		parent::__construct();
	}

	/**
	 * Получение записи по ID
	 */
	function getDeviationWeightByAge ($params) {
		$filter = '';
		$filterAge = '';

		if (isset($params['weight'])) {
			$filter .= ' and COALESCE(DeviationWeight_minWeight,0) < :weight and COALESCE(DeviationWeight_maxWeight,999) > :weight';
		}

		if (isset($params['years']) && isset($params['months'])) {
			if ($params['years'] >= 5 && $params['years'] <= 6 ) {
				if ($params['months'] >= 6) {
					$filterAge .= ' and DeviationWeight_ageYears = :years and DeviationWeight_ageMonths = 6';
				} else {
					$filterAge .= ' and DeviationWeight_ageYears = :years and DeviationWeight_ageMonths = 0';
				}
			} else {
				$filterAge .= ' and DeviationWeight_ageYears = :years and DeviationWeight_ageMonths = :months';
			}
		}

		if (isset($params['sex'])) {
			$filterAge .= ' and Sex_id = :sex';
		}

		$result = $this->getFirstRowFromQuery("
			select
				DeviationWeight_id as \"DeviationWeight_id\",
				DeviationType_id as \"DeviationType_id\"
			from DeviationWeight
			where DeviationType_id <> 4".$filter.$filterAge." order by DeviationWeight_ageYears DESC, DeviationWeight_ageMonths DESC limit 1"
			, $params);

		if ($result) {
			$avgDeviation = $this->getFirstRowFromQuery("
				select
					DeviationWeight_id,
					COALESCE(DeviationWeight_minWeight,0) as \"DeviationWeight_minWeight\",
					COALESCE(DeviationWeight_maxWeight,999) as \"DeviationWeight_maxWeight\"
				from DeviationWeight
				where DeviationType_id = 4".$filterAge." order by DeviationWeight_ageYears DESC, DeviationWeight_ageMonths DESC limit 1"
				, $params);

			$result["DeviationWeight_minWeight"] = $avgDeviation["DeviationWeight_minWeight"];
			$result["DeviationWeight_maxWeight"] = $avgDeviation["DeviationWeight_maxWeight"];
		}

		return $result;
	}
}
