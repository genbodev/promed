<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnPLDispMigrant - контроллер для управления талонами диспансеризации мигрантов
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Polka
 * @access			public
 * @copyright		Copyright (c) 2009 - 2016 Swan Ltd.
 */

class EvnPLDispMigrant extends swController
{
	/**
	 * Description
	 */
    function __construct()
    {
        parent::__construct();
		
        $this->load->database();
		$this->load->model('EvnPLDispMigrant_model', 'dbmodel');
		
        $this->inputRules = array(
            'deleteEvnPLDispMigrant' => array(
                array(
                    'field' => 'EvnPLDispMigrant_id',
                    'label' => 'Идентификатор талона освидетельствования',
                    'rules' => 'trim|required',
                    'type' => 'id'
                )
            ),
			'saveDopDispInfoConsent' => array(
				array(
					'field' => 'EvnPLDispMigrant_id',
					'label' => 'Идентификатор талона по доп. диспансеризации',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DispClass_id',
					'label' => 'Идентификатор этапа',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'PayType_id',
					'label' => 'Вид оплаты',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispMigrant_fid',
					'label' => 'Идентификатор карты предыдущего этапа',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_mid',
					'label' => 'МО мобильной бригады',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispMigrant_IsMobile',
					'label' => 'Обслужен мобильной бригадой',
					'rules' => '',
					'type' => 'checkbox'
				),
				array(
					'field' => 'EvnPLDispMigrant_consDate',
					'label' => 'Дата подписания согласия/отказа',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'PersonEvn_id',
					'label' => 'Идентификатор человека в событии',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор человека',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Server_id',
					'label' => 'Server_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'DopDispInfoConsentData',
					'label' => 'Данные грида по информир. добр. согласию',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'EvnPLDispMigran_RFDateRange',
					'label' => 'Планируемый период пребывания в РФ',
					'rules' => 'trim',
					'type' => 'daterange'
				)
			),
			'updateDopDispInfoConsent' => array(
				array(
					'field' => 'EvnPLDispMigrant_id',
					'label' => 'Идентификатор талона',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'DopDispInfoConsent_id',
					'label' => 'Идентификатор согласия',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'DopDispInfoConsent_IsAgree',
					'label' => 'Согласен/нет',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadEvnPLDispMigrantEditForm' => array(
				array(
					'field' => 'EvnPLDispMigrant_id',
					'label' => 'Идентификатор талона',
					'rules' => 'required',
					'type' => 'id'
				)
			),	
			'loadDopDispInfoConsent' => array(
				array(
					'field' => 'EvnPLDispMigrant_id',
					'label' => 'Идентификатор талона',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор человека',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DispClass_id',
					'label' => 'Идентификатор этапа',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispMigrant_consDate',
					'label' => 'Дата согласия/отказа',
					'rules' => 'required',
					'type' => 'date'
				)
			),
			'loadEvnUslugaDispDopGrid' => array(
				array(
					'field' => 'EvnPLDispMigrant_id',
					'label' => 'Идентификатор талона',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'getInfectData' => array(
				array(
					'field' => 'EvnPLDispMigrant_id',
					'label' => 'Идентификатор талона',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadMigrantContactGrid' => array(
				array(
					'field' => 'EvnPLDispMigrant_id',
					'label' => 'Идентификатор талона',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'saveMigrantContact' => array(
				array(
					'field' => 'EvnPLDispMigrant_id',
					'label' => 'Идентификатор талона',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'MigrantContact_id',
					'label' => 'Идентификатор контакта',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Person_cid',
					'label' => 'Идентификатор человека',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'deleteMigrantContact' => array(
				array(
					'field' => 'MigrantContact_id',
					'label' => 'Идентификатор контакта',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'getUslugaResult' => array(
				array(
					'field' => 'DopDispInfoConsent_id',
					'label' => 'Идентификатор согласия',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'saveEvnPLDispMigrant' => array(
				array(
					'field' => 'EvnPLDispMigrant_id',
					'label' => 'Идентификатор карты',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDispMigrant_Num',
					'label' => 'Номер карты',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'EvnPLDispMigrant_IndexRep',
					'label' => 'Признак повторной подачи',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'EvnPLDispMigrant_IndexRepInReg',
					'label' => 'Признак повторной подачи в реестре',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'EvnPLDispMigrant_IndexRep',
					'label' => 'Признак повторной подачи',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'EvnPLDispMigrant_IndexRepInReg',
					'label' => 'Признак повторной подачи в реестре',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'PayType_id',
					'label' => 'Вид оплаты',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DispClass_id',
					'label' => 'Идентификатор этапа',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор пациента',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'PersonEvn_id',
					'label' => 'Идентификатор пациента',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Server_id',
					'label' => 'Server_id',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'EvnPLDispMigrant_consDate',
					'label' => 'Дата подписания согласия',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'EvnPLDispMigran_RFDateRange',
					'label' => 'Планируемый период пребывания в РФ',
					'rules' => 'trim',
					'type' => 'daterange'
				),
				array(
					'field' => 'EvnPLDispMigrant_IsFinish',
					'label' => 'Медицинское обследование закончено',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'ResultDispMigrant_id',
					'label' => 'Результат',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field'	=> 'EvnPLDispMigran_SertHIVNumber',
					'label'	=> 'Сертификат ВИЧ - номер',
					'rules'	=> '',
					'type'	=> 'int'
				),
				array(
					'field'	=> 'EvnPLDispMigran_SertHIVDate',
					'label'	=> 'Сертификат ВИЧ - дата',
					'rules'	=> '',
					'type'	=> 'date'
				),
				array(
					'field'	=> 'EvnPLDispMigran_SertInfectNumber',
					'label'	=> 'Заключение об инфекционных заболеваниях - номер',
					'rules'	=> '',
					'type'	=> 'int'
				),
				array(
					'field'	=> 'EvnPLDispMigran_SertInfectDate',
					'label'	=> 'Заключение об инфекционных заболеваниях - дата',
					'rules'	=> '',
					'type'	=> 'date'
				),
				array(
					'field'	=> 'EvnPLDispMigran_SertNarcoNumber',
					'label'	=> 'Заключение о наркомании - номер',
					'rules'	=> '',
					'type'	=> 'int'
				),
				array(
					'field'	=> 'EvnPLDispMigran_SertNarcoDate',
					'label'	=> 'Заключение о наркомании - дата',
					'rules'	=> '',
					'type'	=> 'date'
				),
				array(
					'field'	=> 'MigrantContactJSON',
					'label'	=> 'Контактные лица',
					'rules'	=> '',
					'type'	=> 'json_array'
				),
			),
        );
    }

    /**
     * Удаление талона освидетельствования
     */
    function deleteEvnPLDispMigrant() {
        $data = $this->ProcessInputData('deleteEvnPLDispMigrant', true);
        if ($data === false) { return false; }

        $response = $this->dbmodel->deleteEvnPLDispMigrant($data);
        $this->ProcessModelSave($response,true,'При удалении талона освидетельствования возникли ошибки')->ReturnData();

        return true;
    }
	
	/**
	 * Сохранение данных по информир. добр. согласию
	 */
	function saveDopDispInfoConsent() {
		$data = $this->ProcessInputData('saveDopDispInfoConsent', true);
		if ($data === false) { return false; }

		$this->load->library('swFilterResponse'); 
		$response = $this->dbmodel->saveDopDispInfoConsent($data);
		$this->ProcessModelSave($response, true, 'При сохранении возникли ошибки')->ReturnData();

		return true;
	}
	
	/**
	 * Обновление данных по информир. добр. согласию (штучно)
	 */
	function updateDopDispInfoConsent() {
		$data = $this->ProcessInputData('updateDopDispInfoConsent', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->updateDopDispInfoConsent($data);
		$this->ProcessModelSave($response, true, 'При сохранении возникли ошибки')->ReturnData();

		return true;
	}
	
	/**
	 * Получение данных для формы редактирования талона
	 * Входящие данные: $_POST['EvnPLDispMigrant_id']
	 * На выходе: JSON-строка
	 * Используется: форма редактирования талона
	 */
	function loadEvnPLDispMigrantEditForm() {
		$data = $this->ProcessInputData('loadEvnPLDispMigrantEditForm', true);
		if ($data === false) { return false; }
	
		$response = $this->dbmodel->loadEvnPLDispMigrantEditForm($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	*  Получение грида "информированное добровольное согласие"
	*  Входящие данные: EvnPLDispMigrant_id
	*/	
	function loadDopDispInfoConsent() {
		$data = $this->ProcessInputData('loadDopDispInfoConsent', true);
		if ($data === false) { return false; }
	
		$response = $this->dbmodel->loadDopDispInfoConsent($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	*  Получение грида "информированное добровольное согласие"
	*  Входящие данные: EvnPLDispMigrant_id
	*/	
	function loadEvnUslugaDispDopGrid() {
		$data = $this->ProcessInputData('loadEvnUslugaDispDopGrid', true);
		if ($data === false) { return false; }
	
		$response = $this->dbmodel->loadEvnUslugaDispDopGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	*  Получение данных по инфекциям
	*  Входящие данные: EvnPLDispMigrant_id
	*/	
	function getInfectData() {
		$data = $this->ProcessInputData('getInfectData', true);
		if ($data === false) { return false; }
	
		$response = $this->dbmodel->getInfectData($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	*  Получение грида "контактные лица"
	*  Входящие данные: EvnPLDispMigrant_id
	*/	
	function loadMigrantContactGrid() {
		$data = $this->ProcessInputData('loadMigrantContactGrid', true);
		if ($data === false) { return false; }
	
		$response = $this->dbmodel->loadMigrantContactGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	*  Сохранение карты
	*/	
	function saveEvnPLDispMigrant() {
		$data = $this->ProcessInputData('saveEvnPLDispMigrant', true);
		if ($data === false) { return false; }
	
		$response = $this->dbmodel->saveEvnPLDispMigrant($data);
		$this->ProcessModelSave($response, true, 'При сохранении возникли ошибки')->ReturnData();
	}
	
	/**
	*  Сохранение контактного лица
	*/	
	function saveMigrantContact() {
		$data = $this->ProcessInputData('saveMigrantContact', true);
		if ($data === false) { return false; }
	
		$response = $this->dbmodel->saveMigrantContact($data);
		$this->ProcessModelSave($response, true, 'При сохранении возникли ошибки')->ReturnData();
	}
	
	/**
	*  Удаление контактного лица
	*/	
	function deleteMigrantContact() {
		$data = $this->ProcessInputData('deleteMigrantContact', true);
		if ($data === false) { return false; }
	
		$response = $this->dbmodel->deleteMigrantContact($data);
		$this->ProcessModelSave($response, true, 'При сохранении возникли ошибки')->ReturnData();
	}
	
	/**
	* Результаты услуги (если есть)
	*/	
	function getUslugaResult() {
		$data = $this->ProcessInputData('getUslugaResult', true);
		if ($data === false) { return false; }
	
		$response = $this->dbmodel->getUslugaResult($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
}