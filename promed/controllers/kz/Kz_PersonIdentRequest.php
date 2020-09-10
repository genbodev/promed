<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * @property Options_model opmodel
 * @property Person_model persmodel
 */

require_once(APPPATH.'controllers/PersonIdentRequest.php');

class Kz_PersonIdentRequest extends PersonIdentRequest {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 *  Выполнение запроса на идентификацию пациента для Казахстана
	 *  Входящие данные: $_POST['Person_id']
	 *  На выходе: JSON-строка
	 *  Используется: форма редактирования человека
	 */
	function doPersonIdentRequest() {
		$data = $this->ProcessInputData('doPersonIdentRequest', true);

		$this->load->model('ServiceRPN_model');
		$identResponse = $this->ServiceRPN_model->doPersonIdentRequest(array(
			'Person_id' => $data['Person_id'],
			'Person_SurName' => $data['Person_Surname'],
			'Person_FirName' => $data['Person_Firname'],
			'Person_SecName' => $data['Person_Secname'],
			'PersonInn_Inn' => $data['Person_Inn'],
			'Person_BirthDay' => $data['Person_Birthday'],
		));
		if (!empty($identResponse['Error_Msg'])) {
			$this->ReturnData($identResponse);
			return false;
		}

		if (!empty($identResponse['BDZ_id'])) {
			//Загрузка истории прикреплений
			$resp = $this->ServiceRPN_model->importGetAttachmentList(array(
				'rpnPerson_id' => $identResponse['BDZ_id'],
				'pmUser_id' => $data['pmUser_id']
			));
			if (!empty($resp[0]['Error_Msg'])) {
				$this->ReturnData($resp[0]);
				return false;
			}
		}

		$this->ReturnData($identResponse);
		return true;
	}
}
