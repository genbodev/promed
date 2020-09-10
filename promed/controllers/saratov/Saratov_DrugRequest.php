<?php	defined('BASEPATH') or die ('No direct script access allowed');
require_once(APPPATH.'controllers/DrugRequest.php');

class Saratov_DrugRequest extends DrugRequest {
	/**
	 * Saratov_DrugRequest constructor.
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Проверка на наличие открытой заявки региона и заявки МО для сохраняемой заявки врача
	 * $data['MorbusType_id'] - тип (регистра) заявки
	 * $data['DrugRequestPeriod_id'] - раборчий период заявки
	 * $data['Lpu_id'] - ЛПУ заявки
	 */
	function checkExistParentDrugRequest($model, $data) {
		$err_msg = null;
		$result = $model->checkExistParentDrugRequest($data);
		if (is_array($result) && (count($result)>0)) {
			if ($result[0]['region_request_count'] <= 0) {
				$err_msg = "Сохранение заявки врача невозможно, так как отсутствует заявка региона.";
			} elseif ($result[0]['region_correct_status'] <= 0) {
				$err_msg = "Статус заявки региона не допускает сохранения заявки врача.";
			}
			if ($result[0]['mo_request_count'] <= 0) {
				$err_msg = "Сохранение заявки врача невозможно, так как отсутствует заявка МО.";
			} elseif ($result[0]['mo_correct_status'] <= 0) {
				$err_msg = "Статус заявки МО не допускает сохранения заявки врача.";
			}
		} else {
			$err_msg = "При выполнении проверки на возможность сохранения<br/>текущей записи сервер базы данных вернул ошибку!";
		}

		return $err_msg;
	}
}
