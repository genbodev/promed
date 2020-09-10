<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * DeviationHeight - модель для работы с отклонениями роста
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			EMK
 * @access			public
 * @copyright		Copyright (c) 2020 Swan Ltd.
 * @author			Vaschenko Pavel (pavel.vaschenko@rtmis.ru)
 * @version			14.05.2020
 */

class DeviationHeight_model extends swModel {

	/**
	 * Конструктор
	 */
	function DeviationHeight() {
		parent::__construct();
	}

	/**
	 * Получение записи по ID
	 */
	function getDeviationHeightByAge ($params) {
		$filter = '';
		$filterAge = '';

		if (isset($params['height'])) {
			$filter .= ' and isnull(DeviationHeight_minHeight,0) < :height and isnull(DeviationHeight_maxHeight,999) > :height';
		}

		if (isset($params['years']) && isset($params['months'])) {
			if ($params['years'] >= 5 && $params['years'] <= 7 ) {
				if ($params['years'] < 7 && $params['months'] >= 6) {
					$filterAge .= ' and DeviationHeight_ageYears = :years and DeviationHeight_ageMonths = 6';
				} else {
					$filterAge .= ' and DeviationHeight_ageYears = :years and DeviationHeight_ageMonths = 0';
				}
			} else {
				$filterAge .= ' and DeviationHeight_ageYears = :years and DeviationHeight_ageMonths = :months';
			}
		}

		if (isset($params['sex'])) {
			$filterAge .= ' and Sex_id = :sex';
		}

		$result = $this->getFirstRowFromQuery("
			select top 1
				DeviationHeight_id,
				DeviationType_id
			from DeviationHeight with (nolock)
			where DeviationType_id <> 4".$filter.$filterAge." order by DeviationHeight_ageYears DESC, DeviationHeight_ageMonths DESC"
		, $params);

		if ($result) {
			$avgDeviation = $this->getFirstRowFromQuery("
				select top 1
					DeviationHeight_id,
					isnull(DeviationHeight_minHeight,0) as \"DeviationHeight_minHeight\",
					isnull(DeviationHeight_maxHeight,999) as \"DeviationHeight_maxHeight\"
				from DeviationHeight with (nolock)
				where DeviationType_id = 4".$filterAge." order by DeviationHeight_ageYears DESC, DeviationHeight_ageMonths DESC"
				, $params);

			$result["DeviationHeight_minHeight"] = $avgDeviation["DeviationHeight_minHeight"];
			$result["DeviationHeight_maxHeight"] = $avgDeviation["DeviationHeight_maxHeight"];
		}

		return $result;
	}
}
