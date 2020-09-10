<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* EvnDie - контроллер для работы со случаем смерти пациента в стационаре
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package			Stac
* @access			public
* @copyright		Copyright (c) 2009 Swan Ltd.
* @author			Stas Bykov aka Savage (savage1981@gmail.com)
* @version			13.01.2012
*/


class EvnDie extends swController {
	public $inputRules = array(
		'deleteEvnDie' => array(
			array(
				'field' => 'EvnDie_id',
				'label' => 'Идентификатор случая смерти пациента',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadEvnDieEditForm' => array(
			array(
				'field' => 'EvnDie_id',
				'label' => 'Идентификатор случая смерти пациента',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'saveEvnDie' => array(
			array(
				'field' => 'from',
				'label' => 'откуда была открыта форма',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'EvnSection_id',
				'label' => 'Идентификатор движения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'AnatomWhere_id',
				'label' => 'Место выполнения патологоанатомической экспертизы',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Diag_aid',
				'label' => 'Основной диагноз (паталого-анатомический)',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDie_expDate',
				'label' => 'Дата проведения экспертизы',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'EvnDie_expTime',
				'label' => 'Время проведения экспертизы',
				'rules' => 'trim',
				'type' => 'time'
			),
			array(
				'field' => 'EvnDie_id',
				'label' => 'Идентификатор случая смерти пациента',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDie_IsWait',
				'label' => 'Умер в приемном покое',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDie_IsAnatom',
				'label' => 'Необходимость экспертизы',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDie_pid',
				'label' => 'Идентификатор родительского события',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDie_setDate',
				'label' => 'Дата смерти',
				'rules' => 'trim|required',
				'type' => 'date'
			),
			array(
				'field' => 'EvnDie_setTime',
				'label' => 'Время смерти',
				'rules' => 'trim',
				'type' => 'time'
			),
			array(
				'field' => 'EvnDie_UKL',
				'label' => 'Уровень качества лечения',
				'rules' => 'required',
				'type' => 'float'
			),
			array(
				'field' => 'Lpu_aid',
				'label' => 'ЛПУ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_aid',
				'label' => 'Отделение',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonal_aid',
				'label' => 'Врач, проведший экспертизу',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonal_id',
				'label' => 'Врач, установивший смерть',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Org_aid',
				'label' => 'Организация',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PersonEvn_id',
				'label' => 'Идентификатор состояния пациента',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Server_id',
				'label' => 'Идентификатор сервера',
				'rules' => 'required',
				'type' => 'int'
			)
		)
	);

	/**
	 * EvnDie constructor.
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('EvnDie_model', 'dbmodel');
	}


	/**
	*  Удаление случая смерти пациента
	*  Входящие данные: $_POST['EvnDie_id']
	*  На выходе: JSON-строка
	*  Используется: ???
	*/
	function deleteEvnDie() {
		$data = array();

        $data = $this->ProcessInputData('deleteEvnDie',true);
        if ($data === false) {return false;}

		$response = $this->dbmodel->deleteEvnDie($data);
        $this->ProcessModelSave($response,true,'При удалении случая смерти пациента возникли ошибки')->ReturnData();

		return true;
	}


	/**
	*  Получение данных для формы редактирования случая смерти пациента
	*  Входящие данные: $_POST['EvnDie_id']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования случая смерти пациента
	*/
	function loadEvnDieEditForm() {
		$data = array();

        $data = $this->ProcessInputData('loadEvnDieEditForm',true);
        if ($data === false) {return false;}

		$response = $this->dbmodel->loadEvnDieEditForm($data);
        $this->ProcessModelList($response,true,true)->ReturnData();

		return true;
	}


	/**
	*  Сохранение случая смерти пациента
	*  Входящие данные: ...
	*  На выходе: JSON-строка
	*  Используется: форма редактирования случая смерти пациента
	*/
	function saveEvnDie() {
		$this->load->model('EvnSection_model', 'esmodel');

		$data = array();

        $data = $this->ProcessInputData('saveEvnDie',true);
        if ($data === false) {return false;}
		
		$this->load->model("Org_model", "orgmodel");
		
		if ( (isset($data['AnatomWhere_id'])) && ($data['AnatomWhere_id'] == 2) ) {
			$data['Lpu_aid'] = $data['Org_aid'];
			$response = $this->orgmodel->getLpuData(array('Org_id'=>$data['Org_aid']));
			if (!empty($response[0]) && !empty($response[0]['Lpu_id'])) {
				$data['Lpu_aid'] = $response[0]['Lpu_id'];
			}
			$data['Org_aid'] = NULL;
		}

		$response = $this->dbmodel->saveEvnDie($data);
        $this->ProcessModelSave($response,true,'Ошибка при сохранении случая смерти пациента')->ReturnData();

		return true;
	}
}
