<?php defined('BASEPATH') or die ('No direct script access allowed');
require_once(APPPATH . 'controllers/ExchangeBL.php');

/**
 * Kz_ExchangeBL - контроллер
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @property Kz_ExchangeBL_model $dbmodel
 * @property Options_model $Options_model
 *
 * @package            Common
 * @access            public
 * @copyright        Copyright (c) 2019 Swan Ltd.
 */
class Kz_ExchangeBL extends ExchangeBL
{

    /**
     * @inheritdoc
     */
    public $NeedCheckLogin = false;

    /**
     * @var array
     */
    public $inputRules = [
        'syncAll' => [],
        'getPayType' => [
			['field' => 'Person_id', 'label' => 'Пациент', 'rules' => 'required', 'type'  => 'id'],
			['field' => 'EvnDirection_setDate', 'label' => 'Дата направления', 'rules' => 'required', 'type'  => 'date'],
			['field' => 'EvnDirection_desDT', 'label' => 'Желаемая дата', 'rules' => '', 'type'  => 'date'],
			['field' => 'bookingDateReserveId', 'label' => 'Идентификатор зарезервированной ранее желаемой даты', 'rules' => '', 'type'  => 'string'],
			['field' => 'TreatmentClass_id', 'label' => 'Повод обращения', 'rules' => '', 'type'  => 'id'],
			['field' => 'Lpu_id', 'label' => 'Направившее МО', 'rules' => '', 'type'  => 'id'],
			['field' => 'Org_oid', 'label' => 'МО направления', 'rules' => '', 'type'  => 'id'],
			['field' => 'Lpu_did', 'label' => 'МО направления', 'rules' => '', 'type'  => 'id'],
			['field' => 'LpuSectionProfile_id', 'label' => 'Профиль', 'rules' => '', 'type'  => 'id'],
			['field' => 'Diag_id', 'label' => 'Диагноз', 'rules' => '', 'type'  => 'id'],
			['field' => 'PayTypeKAZ_id', 'label' => 'Диагноз', 'rules' => '', 'type'  => 'id'],
			['field' => 'GetBed_id', 'label' => 'Диагноз', 'rules' => '', 'type'  => 'id'],
			['field' => 'isStac', 'label' => '', 'rules' => '', 'type'  => 'id'],
			array('field' => 'UslugaComplex_id', 'label' => '', 'rules' => '', 'type'  => 'id'),
			array('field' => 'PurposeHospital_id', 'label' => '', 'rules' => '', 'type'  => 'id'),
			array('field' => 'EvnLinkAPP_StageRecovery', 'label' => '', 'rules' => '', 'type'  => 'id'),
			array('field' => 'LpuUnitType_id', 'label' => '', 'rules' => '', 'type'  => 'id'),
			array('field' => 'Diag_cid', 'label' => '', 'rules' => '', 'type'  => 'id'),
			array('field' => 'DirType_id', 'label' => '', 'rules' => '', 'type'  => 'id'),
			array('field' => 'PrehospType_id', 'label' => '', 'rules' => '', 'type'  => 'id'),
			array('field' => 'EvnPS_id', 'label' => '', 'rules' => '', 'type'  => 'id')
		],
        'getRefferalByPerson' => [
			['field' => 'Person_id', 'label' => 'Пациент', 'rules' => 'required', 'type'  => 'id'],
		],
		'sendEvnPLDispScreenAPP' => [
			array(
				'field' => 'EvnPLDispScreen_id',
				'label' => 'Идентификатор карты скрининга взрослых',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPLDispScreenChild_id',
				'label' => 'Идентификатор карты скрининга детей',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPLDispScreen_IsEndStage',
				'label' => 'Признак окончания',
				'rules' => 'required',
				'type' => 'id'
			)
		]
    ];

    /**
     * Передача перечня выполненных медицинских услуг
     * в ПС АПП посредством сервиса ExchangeBL
     */
    public function syncAll()
    {
        $data = $this->ProcessInputData('syncAll', true);
        if ($data === false) {
            return false;
        }

        $this->load->model('Options_model');
        $ais_reporting_period = $this->Options_model->getOptionsGlobals($data, 'ais_reporting_period');
        $this->dbmodel->setAisReportPeriodStartDate($ais_reporting_period);

        $this->dbmodel->syncAll();
    }

    /**
     * Получение направлений
     */
    public function GetReferral()
    {
        $this->dbmodel->GetReferral();
    }

    /**
     * Получение направлений
     */
    public function getRefferalByPerson()
    {
        $data = $this->ProcessInputData('getRefferalByPerson', true);
        if ($data === false) return false;
		
		$response = $this->dbmodel->getRefferalByPerson($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
    }

    /**
     * Получение типа оплаты
     */
    public function getPayType()
    {
        $data = $this->ProcessInputData('getPayType', true);
        if ($data === false) return false;

		$response = $this->dbmodel->getPayType($data);
		$this->ProcessModelSave($response, true)->ReturnData();
    }

    /**
     * ---
     */
    public function loadProfileUslugaLink()
    {		
		$response = $this->dbmodel->loadProfileUslugaLink();
		$this->ProcessModelList($response, true, true)->ReturnData();
    }
    
    /**
	 * Отправка из МИС в ПС АПП информации о начале скрининга
	 */
    public function sendEvnPLDispScreenAPP() {
		
    	$data = $this->ProcessInputData('sendEvnPLDispScreenAPP', true);
		if ($data === false) return false;

		$response = $this->dbmodel->sendEvnPLDispScreenAPP($data);
		$this->ProcessModelSave($response, true)->ReturnData();
	}
}