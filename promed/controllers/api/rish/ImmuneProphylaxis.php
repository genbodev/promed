<?php defined('BASEPATH') or die ('No direct script access allowed');

require(APPPATH . 'libraries/SwREST_Controller.php');

/**
 * ImmuneProphylaxis - контроллер для работы со журналами прививок
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      Common
 * @access       public
 *
 */

/**
 *
 * @property ImmuneProphylaxis_model dbmodel
 */
class ImmuneProphylaxis extends SwREST_Controller
{
	protected $inputRules = array(
		'VacJournalAccount_post' => array(
			array('field' => 'vacJournalAccount_id', 'label' => 'Уникальный идентификатор исполненной приивки', 'rules' => '', 'type' => 'id'),
			array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'vacJournalAccount_DatePurpose', 'label' => 'Дата назначения прививки', 'rules' => '', 'type' => 'string'),
			array('field' => 'vacJournalAccount_Purpose_MedPersonal_id', 'label' => 'ID врача, назначившего прививку', 'rules' => '', 'type' => 'id'),
			array('field' => 'Vaccine_id', 'label' => 'Идентификатор вакцины', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'vacJournalAccount_Seria', 'label' => 'Серия препарата', 'rules' => '', 'type' => 'int'),
			array('field' => 'vacJournalAccount_Period', 'label' => 'срок годности', 'rules' => '', 'type' => 'date'),
			array('field' => 'vacJournalAccount_Dose', 'label' => 'Доза препарата', 'rules' => '', 'type' => 'string'),
			array('field' => 'VaccineWay_id', 'label' => 'Способ введения вакцины', 'rules' => '', 'type' => 'int'),
			array('field' => 'VaccinePlace_id', 'label' => 'Место введения вакцины', 'rules' => '', 'type' => 'int'),
			array('field' => 'vacJournalAccount_DateVac', 'label' => 'Дата вакцинации', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'vacJournalAccount_Lpu_id', 'label' => 'МО, где была исполнена прививка', 'rules' => '', 'type' => 'int'),
			array('field' => 'vacJournalAccount_Vac_MedPersonal_id', 'label' => 'ID мед. сотрудника, исполнившего прививку', 'rules' => '', 'type' => 'int')
		),
		'VacJournalAccount_put' => array(
			array('field' => 'vacJournalAccount_id', 'label' => 'Уникальный идентификатор исполненной приивки', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => '', 'type' => 'id'),
			array('field' => 'vacJournalAccount_DatePurpose', 'label' => 'Дата назначения прививки', 'rules' => '', 'type' => 'string'),
			array('field' => 'vacJournalAccount_Purpose_MedPersonal_id', 'label' => 'ID врача, назначившего прививку', 'rules' => '', 'type' => 'id'),
			array('field' => 'Vaccine_id', 'label' => 'Идентификатор вакцины', 'rules' => '', 'type' => 'id'),
			array('field' => 'vacJournalAccount_Seria', 'label' => 'Серия препарата', 'rules' => '', 'type' => 'int'),
			array('field' => 'vacJournalAccount_Period', 'label' => 'срок годности', 'rules' => '', 'type' => 'string'),
			array('field' => 'vacJournalAccount_Dose', 'label' => 'Доза препарата', 'rules' => '', 'type' => 'string'),
			array('field' => 'VaccineWay_id', 'label' => 'Способ введения вакцины', 'rules' => '', 'type' => 'int'),
			array('field' => 'VaccinePlace_id', 'label' => 'Место введения вакцины', 'rules' => '', 'type' => 'int'),
			array('field' => 'vacJournalAccount_DateVac', 'label' => 'Дата вакцинации', 'rules' => '', 'type' => 'string'),
			array('field' => 'vacJournalAccount_Lpu_id', 'label' => 'МО, где была исполнена прививка', 'rules' => '', 'type' => 'int'),
			array('field' => 'vacJournalAccount_Vac_MedPersonal_id', 'label' => 'ID мед. сотрудника, исполнившего прививку', 'rules' => '', 'type' => 'int')
		),
		'VacJournalAccount_get' => array(
			array('field' => 'vacJournalAccount_id', 'label' => 'Уникальный идентификатор исполненной приивки', 'rules' => '', 'type' => 'id'),
			array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => '', 'type' => 'id')
		),
		'Vac_JournalMantu_post' => array(
			array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'JournalMantu_Dose', 'label' => 'Доза препарата', 'rules' => '', 'type' => 'string'),
			array('field' => 'VaccineWay_id', 'label' => 'Способ введения вакцины', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'VaccinePlace_id', 'label' => 'Место введения вакцины', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'MantuReactionType_id', 'label' => 'Тип реакции манту', 'rules' => '', 'type' => 'int'),
			array('field' => 'JournalMantu_DateVac', 'label' => 'Дата пробы', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'JournalMantu_Lpu_id', 'label' => 'ID МО, в котором брали пробу', 'rules' => '', 'type' => 'id'),
			array('field' => 'JournalMantu_vacMedPersonal_id', 'label' => 'ID мед. сотрудника, котрыл брал пробу', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'TubDiagnosisType_id', 'label' => 'Тип туберкулезной пробы', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'DiaskinTestReactionType_id', 'label' => 'Реакция диаскин тест', 'rules' => '', 'type' => 'int'),
			array('field' => 'JournalMantu_ReactDescription', 'label' => 'Описание реакции', 'rules' => '', 'type' => 'string'),
			array('field' => 'JournalMantu_Seria', 'label' => 'Серия вакцины', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'JournalMantu_Period', 'label' => 'Срок годности вакцины', 'rules' => 'required', 'type' => 'date'),
		),
		'Vac_JournalMantu_put' => array(
			array('field' => 'JournalMantu_id', 'label' => '', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => '', 'type' => 'id'),
			array('field' => 'JournalMantu_Dose', 'label' => 'Доза препарата', 'rules' => '', 'type' => 'string'),
			array('field' => 'VaccineWay_id', 'label' => 'Способ введения вакцины', 'rules' => '', 'type' => 'int'),
			array('field' => 'VaccinePlace_id', 'label' => 'Место введения вакцины', 'rules' => '', 'type' => 'int'),
			array('field' => 'MantuReactionType_id', 'label' => 'Тип реакции манту', 'rules' => '', 'type' => 'int'),
			array('field' => 'JournalMantu_DateVac', 'label' => 'Дата пробы', 'rules' => '', 'type' => 'string'),
			array('field' => 'JournalMantu_Lpu_id', 'label' => 'ID МО, в котором брали пробу', 'rules' => '', 'type' => 'id'),
			array('field' => 'JournalMantu_vacMedPersonal_id', 'label' => 'ID мед. сотрудника, котрыл брал пробу', 'rules' => '', 'type' => 'id'),
			array('field' => 'TubDiagnosisType_id', 'label' => 'Тип туберкулезной пробы', 'rules' => '', 'type' => 'int'),
			array('field' => 'DiaskinTestReactionType_id', 'label' => 'Реакция диаскин тест', 'rules' => '', 'type' => 'int'),
			array('field' => 'JournalMantu_ReactDescription', 'label' => 'Описание реакции', 'rules' => '', 'type' => 'string')
		),
		'Vac_JournalMantu_get' => array(
			array('field' => 'JournalMantu_id', 'label' => '', 'rules' => '', 'type' => 'id'),
			array('field' => 'Person_id', 'label' => 'Идентификатор пациента', 'rules' => '', 'type' => 'id')
		)
	);

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('ImmuneProphylaxis_model', 'dbmodel');
	}

	/**
	 * post-запрос на создание новой записи в журнале вакцинаций
	*/
	function VacJournalAccount_post()
	{
		$data = $this->ProcessInputData('VacJournalAccount_post', null, true);
		$data['vacJournalAccount_StatusType_id'] = 1; // исполненная вакцина
		
		if ($this->checkPersonId($data['Person_id']) === false) {
			$this->response(array('error_code' => 6, 'error_msg' =>'Пациент не найден в системе'));
		}

		$chk = $this->dbmodel->getFirstRowFromQuery("
			select vacJournalAccount_id 
			from vac.vac_JournalAccount (nolock)
			where 
				Person_id = :Person_id and 
				vacJournalAccount_DateVac = :vacJournalAccount_DateVac and 
				Vaccine_id = :Vaccine_id
		", $data);
		if ($chk !== false) {
			$this->response(array('error_code' => 6, 'error_msg' =>'Данные прививки не прошли проверку на дублирование'));
		}

		$resp = $this->dbmodel->addTo_VacJournalAccount($data);

		if (empty($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'vacJournalAccount_id' => $resp[0]['vacJournalAccount_id']
		));
	}

	/**
	 * put-запрос на редактирование записи в журнале вакцинаций
	 */
	function VacJournalAccount_put()
	{
		$data = $this->ProcessInputData('VacJournalAccount_put', null, true);

		$chk = $this->dbmodel->getFirstResultFromQuery("
			select vacJournalAccount_id from vac.vac_JournalAccount (nolock) where vacJournalAccount_id = :vacJournalAccount_id
		", $data);
		if (!$chk) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Прививка не найдена в системе'
			));
		}
		
		if (!empty($data['Person_id'])) {
			$chk = $this->dbmodel->getFirstResultFromQuery("
				select vacJournalAccount_id from vac.vac_JournalAccount (nolock) where vacJournalAccount_id = :vacJournalAccount_id and Person_id = :Person_id
			", $data);
			if (!$chk) {
				$this->response(array(
					'error_code' => 6,
					'error_msg' => 'Данная прививка для указанного пациента не найдена в системе'
				));
			}
		}

		$chk = $this->dbmodel->getFirstRowFromQuery("
			select vacJournalAccount_id 
			from vac.vac_JournalAccount (nolock)
			where 
				vacJournalAccount_id != :vacJournalAccount_id and
				Person_id = :Person_id and 
				vacJournalAccount_DateVac = :vacJournalAccount_DateVac and 
				Vaccine_id = :Vaccine_id
		", $data);
		if ($chk !== false) {
			$this->response(array('error_code' => 6, 'error_msg' =>'Данные прививки не прошли проверку на дублирование'));
		}

		$resp = $this->dbmodel->update_VacJournalAccount($data);

		if (!$resp) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 * get-запрос на получение данных из журнала вакцинации
	 */
	function VacJournalAccount_get()
	{
		$data = $this->ProcessInputData('VacJournalAccount_get', null, true);

		$resp = $this->dbmodel->selectFrom_VacJournalAccount($data);

		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * post-запрос на создание новой записи в журнале прививок Манту
	 */
	function Vac_JournalMantu_post()
	{
		$data = $this->ProcessInputData('Vac_JournalMantu_post', null, true);
		$data['JournalMantu_StatusType_id'] = 1; //Манту нельзя назначить, т.е. статус всегда необходимо ставить "исполнено" (JournalMantu_StatusType_id=1)
		
		if ($this->checkPersonId($data['Person_id']) === false) {
			$this->response(array('error_code' => 6, 'error_msg' =>'Пациент не найден в системе'));
		}

		$chk = $this->dbmodel->getFirstRowFromQuery("
			select JournalMantu_id 
			from vac.vac_JournalMantu (nolock)
			where 
				Person_id = :Person_id and 
				JournalMantu_DateVac = :JournalMantu_DateVac and 
				TubDiagnosisType_id = :TubDiagnosisType_id
		", $data);
		if ($chk !== false) {
			$this->response(array('error_code' => 6, 'error_msg' =>'Данные прививки не прошли проверку на дублирование'));
		}

		$resp = $this->dbmodel->addTo_Vac_JournalMantu($data);

		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'JournalMantu_id' => $resp[0]['JournalMantu_id']
		));
	}

	/**
	 * put-запрос на редактирование записи в журнале прививок Манту
	 */
	function Vac_JournalMantu_put()
	{
		$data = $this->ProcessInputData('Vac_JournalMantu_put', null, true);

		$chk = $this->dbmodel->getFirstResultFromQuery("
			select JournalMantu_id from vac.vac_JournalMantu (nolock) where JournalMantu_id = :JournalMantu_id
		", $data);
		if (!$chk) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Прививка не найдена в системе'
			));
		}
		
		if (!empty($data['Person_id'])) {
			$chk = $this->dbmodel->getFirstResultFromQuery("
				select JournalMantu_id from vac.vac_JournalMantu (nolock) where JournalMantu_id = :JournalMantu_id and Person_id = :Person_id
			", $data);
			if (!$chk) {
				$this->response(array(
					'error_code' => 6,
					'error_msg' => 'Данная прививка для указанного пациента не найдена в системе'
				));
			}
		}

		$chk = $this->dbmodel->getFirstRowFromQuery("
			select JournalMantu_id 
			from vac.vac_JournalMantu (nolock)
			where 
				JournalMantu_id != :JournalMantu_id and
				Person_id = :Person_id and 
				JournalMantu_DateVac = :JournalMantu_DateVac and 
				TubDiagnosisType_id = :TubDiagnosisType_id
		", $data);
		if ($chk !== false) {
			$this->response(array('error_code' => 6, 'error_msg' =>'Данные прививки не прошли проверку на дублирование'));
		}

		$resp = $this->dbmodel->update_Vac_JournalMantu($data);

		if (!$resp) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 * get-запрос на получение данных из журнала прививок Манту
	 */
	function Vac_JournalMantu_get()
	{
		$data = $this->ProcessInputData('Vac_JournalMantu_get', null, true);

		$resp = $this->dbmodel->selectFrom_Vac_JournalMantu($data);

		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}
}
