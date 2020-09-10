<?php

defined('BASEPATH') or die('No direct script access allowed');

/**
 * comment
 */
class PersonAmbulatCard extends swController {

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
		$this->load->database();
		$this->load->model('PersonAmbulatCard_model', 'dbmodel');
		$this->inputRules = array(
			'getPersonAmbulatCardList' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор человека',
					'rules' => 'required|trim',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => 'required|trim',
					'type' => 'id'
				)
			),
			'checkPersonAmbulatCard'=>array(
				array(
					'field'=>'Person_id',
					'label'=>'Person_id',
					'rules'=>'required',
					'type'=>'id'
				),
				array(
					'default'=>false,
					'field'=>'getCount',
					'label'=>'getCount',
					'rules'=>'',
					'type'=>'checkbox'
				)
			),
			'deletePersonAmbulatCard'=>array(
				array(
					'field' => 'PersonAmbulatCard_id',
					'label' => 'Идентификатор человека',
					'rules' => 'required|trim',
					'type' => 'id'
				)
			),
			'loadPersonAmbulatCard' => array(
				array(
					'field' => 'PersonAmbulatCard_id',
					'label' => 'Идентификатор человека',
					'rules' => 'required|trim',
					'type' => 'id'
				)
			),
			'loadPersonCard' => array(
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор человека',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор человека',
					'rules' => '',
					'type' => 'id'
				),
				/*array(
					'field' => 'AmbulatCardType_id',
					'label' => 'AmbulatCardType_id',
					'rules' => '',
					'type' => 'id'
				),*/
				array(
					'field' => 'query',
					'label' => 'Идентификатор человека',
					'rules' => 'trim',
					'type' => 'string'
				)
			),
			'loadPersonAmbulatCardLocat' => array(
				array(
					'field' => 'PersonAmbulatCardLocat_id',
					'label' => 'Идентификатор человека',
					'rules' => 'required|trim',
					'type' => 'id'
				)
			),
			'savePersonAmbulatCardLocat' => array(
				array(
					'field' => 'PersonAmbulatCardLocat_id',
					'label' => 'Идентификатор движения АК',
					'rules' => 'required|trim',
					'type' => 'id'
				),
				array(
					'field' => 'AmbulatCardLocatType_id',
					'label' => 'Идентификатор типа',
					'rules' => 'required|trim',
					'type' => 'id'
				),
				array(
					'field' => 'MedStaffFact_id',
					'label' => 'Идентификатор врача',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'PersonAmbulatCardLocat_Desc',
					'label' => 'Комментарий',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'PersonAmbulatCardLocat_OtherLocat',
					'label' => 'Местонахождение',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'PersonAmbulatCardLocat_begD',
					'label' => 'Дата',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'PersonAmbulatCardLocat_begT',
					'label' => 'Время',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'PersonAmbulatCard_id',
					'label' => 'Идентификатор АК',
					'rules' => 'required|trim',
					'type' => 'id'
				),
				array(
					'field' => 'Server_id',
					'label' => 'Идентификатор сервера',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'LpuBuilding_id',
					'label' => 'Картохранилище (подразделение)',
					'rules' => '',
					'type' => 'int'
				)
			),
			'savePersonAmbulatCard' => array(
				array(
					'field' => 'ignoreUniq',
					'label' => 'ignoreUniq',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'PersonAmbulatCard_id',
					'label' => 'Идентификатор АК',
					'rules' => 'required|trim',
					'type' => 'id'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор человека',
					'rules' => 'required|trim',
					'type' => 'id'
				),
				/*array(
					'field' => 'AmbulatCardType_id',
					'label' => 'Идентификатор человека',
					'rules' => 'required|trim',
					'type' => 'id'
				),*/
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор ЛПУ',
					'rules' => 'required|trim',
					'type' => 'id'
				),
				array(
					'field' => 'Server_id',
					'label' => 'Идентификатор сервера',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'PersonAmbulatCard_Num',
					'label' => 'Номер АК',
					'rules' => 'required|trim',
					'type' => 'string'
				),
				array(
					'field' => 'PersonFIO',
					'label' => 'ФИО',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'PersonAmbulatCard_CloseCause',
					'label' => 'Причина закрытия',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'PersonAmbulatCard_endDate',
					'label' => 'Дата закрытия',
					'rules' => '',
					'type' => 'date'
				),
			),
			'getPersonAmbulatCardLocatList' => array(
				array(
					'default' => 0,
					'field' => 'start',
					'label' => 'Начальный номер записи',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'default' => 5,
					'field' => 'limit',
					'label' => 'Количество возвращаемых записей',
					'rules' => 'trim',
					'type' => 'int'
				),
				array(
					'field' => 'PersonAmbulatCard_id',
					'label' => 'Тип арма, вызвавшего метод',
					'rules' => '',
					'type' => 'id'
				),
			),
			'savePersonAmbulatDeliverCard' => array(
				array(
					'field' => 'PersonAmbulatCardLocat_id',
					'label' => 'Идентификатор движения АК',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'AmbulatCardLocatType_id',
					'label' => 'Идентификатор типа',
					'rules' => 'required|trim',
					'type' => 'id'
				),
				array(
					'field' => 'MedStaffFact_id',
					'label' => 'должность',
					'rules' => 'required|trim',
					'type' => 'id'
				),
				array(
					'field' => 'MedPersonal_id',
					'label' => 'сотрудник МО',
					'rules' => 'required|trim',
					'type' => 'id'
				),
				array(
					'field' => 'PersonAmbulatCard_id',
					'label' => 'Идентификатор АК',
					'rules' => 'required|trim',
					'type' => 'id'
				),
				array(
					'field' => 'TimetableGraf_id',
					'label' => 'Идентификатор бирки',
					'rules' => 'required|trim',
					'type' => 'id'
				),
				array(
					'field' => 'AmbulatCardRequestStatus_id',
					'label' => 'Идентификатор статуса запроса',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'AmbulatCardRequest_id',
					'label' => 'Идентификатор запроса',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Server_id',
					'label' => 'Идентификатор сервера',
					'rules' => '',
					'type' => 'int'
				)
			),
			'savePersonAmbulatCardInTimetableGraf' => array(
				array(
					'field' => 'PersonAmbulatCard_id',
					'label' => 'Идентификатор АК',
					'rules' => 'required|trim',
					'type' => 'id'
				),
				array(
					'field' => 'TimetableGraf_id',
					'label' => 'Идентификатор бирки',
					'rules' => 'required|trim',
					'type' => 'id'
				)
			),
			'getLpuBuildingByMedServiceId' => array(
				array(
					'field' => 'MedService_id',
					'label' => 'Идентификатор службы',
					'rules' => '',
					'type' => 'id'
				),
			),
			'loadInformationAmbulatoryCards' => array(
				array(
					'field' => 'Lpu_id',
					'label' => '',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор пациента',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'PersonAmbulatCard_id',
					'label' => 'Идентификатор АК',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'LpuBuilding_id',
					'label' => 'Картохранилище (подразделение)',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'MedStaffFact_id',
					'label' => 'Врач',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'AmbulatCardLocatType_id',
					'label' => 'Местонахождение мбулаторной карты',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Person_SurName',
					'label' => 'Фамилия',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Person_FirName',
					'label' => 'Имя',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Person_SecName',
					'label' => 'Отчество',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'Person_Birthday',
					'label' => 'Дата рождения',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'CardAttachment',
					'label' => 'Прикрепление карты',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'CardIsOpenClosed',
					'label' => 'Карта открыта/закрыта',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'field_numberCard',
					'label' => '№ амб. карты',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'AttachmentLpuBuilding_id',
					'label' => 'подразделение прикрепления службы',
					'rules' => '',
					'type' => 'int'
				),
				array('default' => 0, 'field' => 'start', 'label' => 'Начальный номер записи', 'rules' => 'trim', 'type' => 'int'),
				array('default' => 100, 'field' => 'limit', 'label' => 'Количество возвращаемых записей', 'rules' => 'trim', 'type' => 'int')
			),
			'setAmbulatCardRequest' => array(
				array(
					'field' => 'PersonAmbulatCard_id',
					'label' => 'Идентификатор АК',
					'rules' => 'required|trim',
					'type' => 'id'
				),
				array(
					'field' => 'TimetableGraf_id',
					'label' => 'Идентификатор бирки',
					'rules' => 'required|trim',
					'type' => 'id'
				),
				array(
					'field' => 'AmbulatCardRequest_id',
					'label' => 'Идентификатор запроса амб.карты у картохранилища',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'AmbulatCardRequestStatus_id',
					'label' => 'Статус запроса амб.карты у картохранилища',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'MedStaffFact_id',
					'label' => 'Врач',
					'rules' => '',
					'type' => 'int'
				),
			),
			'deletePersonAmbulatCardLocat' => array(
				array(
					'field' => 'PersonAmbulatCardLocat_id',
					'label' => 'идентификатор движения АК',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'searchCardAtTheReception' => array(
				array(
					'field' => 'PersonAmbulatCard_id',
					'label' => 'Идентификатор АК',
					'rules' => 'required|trim',
					'type' => 'id'
				),
				array(
					'field' => 'Person_id',
					'label' => 'пациент',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'attachmentLpuBuilding_id',
					'label' => 'подразделение прикрепления службы',
					'rules' => '',
					'type' => 'int'
				),
			)
		);
	}

	/**
	 * 
	 * @return boolean
	 */
	function getPersonAmbulatCardList() {
		$data = $this->ProcessInputData('getPersonAmbulatCardList', true);
		if ($data === false)
			return false;

		$response = $this->dbmodel->getPersonAmbulatCardList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 *
	 * @return type 
	 */
	function deletePersonAmbulatCard(){
		$data = $this->ProcessInputData('deletePersonAmbulatCard', true);
		if ($data === false)
			return false;

		$response = $this->dbmodel->deletePersonAmbulatCard($data);
		$this->ProcessModelSave($response, true, 'Ошибка при удалении оригинала АК')->ReturnData();
	}
	
	/**
	 *
	 * @return type 
	 */
	function checkPersonAmbulatCard(){
		$data = $this->ProcessInputData('checkPersonAmbulatCard', true);
		if ($data === false)
			return false;

		$response = $this->dbmodel->checkPersonAmbulatCard($data);
		$this->ProcessModelList($response, true)->ReturnData();
	}
	/**
	 * 
	 * @return boolean
	 */
	function savePersonAmbulatCard() {
		$data = $this->ProcessInputData('savePersonAmbulatCard', true);
		if ($data === false)
			return false;
		
		$action = (isset($data['PersonAmbulatCard_id']) && $data['PersonAmbulatCard_id'] > 0) ? 'edit' : 'add';
		
		$response = $this->dbmodel->savePersonAmbulatCard($data);
		if(!isset($response[0]['Error_Msg'])){
			//print_r($response);
			if(isset($_POST['AmbulatCardLoactArr'])){
				ConvertFromWin1251ToUTF8($_POST['AmbulatCardLoactArr']);
				$AmbulatCardLoactArr = json_decode($_POST['AmbulatCardLoactArr'], true);
				//print_r($AmbulatCardLoactArr);
				foreach ($AmbulatCardLoactArr as $val) {
					if (isset($response[0]['PersonAmbulatCard_id'])) {
						$val['PersonAmbulatCard_id']=$response[0]['PersonAmbulatCard_id'];
						$val['pmUser_id']=$data['pmUser_id'];
						$res = $this->dbmodel->savePersonAmbulatCardLocat($val);
						if ( isset($val['MedPersonal_id'])&&$val['MedPersonal_id'] >0) {
							$text = 'Амбулаторная карта пациента - ' .$data['PersonFIO']. ', с номером ' .$data['PersonAmbulatCard_Num']. ', передана вам. ';
							$noticeData = array(
								'autotype' => 1,
								'Lpu_rid' => $data['Lpu_id'],
								'pmUser_id' => $data['pmUser_id'],
								'MedPersonal_rid' => $val['MedPersonal_id'],
								'type' => 1,
								'title' => 'Амбулаторная карта',
								'text' => $text
							);
							$this->load->model('Messages_model');
							$this->Messages_model->autoMessage($noticeData);
						}

					}else{
						return false;
					}
				}
			}
		}
		
		if( $action == 'add' && !empty($response[0]['PersonAmbulatCard_id']) ){
			//при добавлении амбулаторной карты прикрепляем ее к картохранилищу
			$data['PersonAmbulatCard_id'] = $response[0]['PersonAmbulatCard_id'];
			$res = $this->dbmodel->saveAttachmentAmbulatoryCardToCardStore($data);
		}
		
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении АК')->ReturnData();
	}

	/**
	 * 
	 * @return boolean
	 */
	function savePersonAmbulatCardLocat() {
		$data = $this->ProcessInputData('savePersonAmbulatCardLocat', true);
		if ($data === false)
			return false;
		if(empty($data['Server_id'])) $data['Server_id'] = $data['session']['server_id'];
		$response = $this->dbmodel->savePersonAmbulatCardLocat($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении Движения АК')->ReturnData();
	}
	
	/**
	 * 
	 * @return boolean
	 */
	function deletePersonAmbulatCardLocat() {
		$data = $this->ProcessInputData('deletePersonAmbulatCardLocat', true);
		if ($data === false) return false;
		if(empty($data['Server_id'])) $data['Server_id'] = $data['session']['server_id'];
		$response = $this->dbmodel->deletePersonAmbulatCardLocat($data);
		$this->ProcessModelSave($response, true, 'Ошибка при удалении движения АК')->ReturnData();
	}

	/**
	 * 
	 * @return boolean
	 */
	function loadPersonAmbulatCard() {
		$data = $this->ProcessInputData('loadPersonAmbulatCard', false);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->loadPersonAmbulatCard($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * @df
	 */
	function loadPersonCard() {
		$data = $this->ProcessInputData('loadPersonCard', false);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->loadPersonCard($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * 
	 * @return boolean
	 */
	function loadPersonAmbulatCardLocat() {
		$data = $this->ProcessInputData('loadPersonAmbulatCardLocat', false);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->loadPersonAmbulatCardLocat($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * 
	 * @return boolean
	 */
	function getPersonAmbulatCardLocatList() {
		$data = $this->ProcessInputData('getPersonAmbulatCardLocatList', true);
		if ($data === false) {
			return true;
		}
		$response = $this->dbmodel->getPersonAmbulatCardLocatList($data);
		if ( is_array($response) ) {
			if ( (isset($response['Error_Msg'])) && (strlen($response['Error_Msg']) > 0) ) {
				$val = $response;
				array_walk($val, 'ConvertFromWin1251ToUTF8');
			}
			else if ( isset($response['data']) ) {
				$val['data'] = array();
				foreach ( $response['data'] as $row ) {
					array_walk($row, 'ConvertFromWin1251ToUTF8');
					$val['data'][] = $row;
				}
				$val['totalCount'] = $response['totalCount'];
			}
		}
		else
		{
			echo json_return_errors('Проблема выполнения запроса к БД.');
			return false;
		}
		$this->ReturnData($val);

		return true;	
	}
	
	/**
	 * сохранение движения карты из рабочего места сотрудника картохранилища (доставить карту)
	 * @return boolean
	 */
	function savePersonAmbulatDeliverCard() {
		//$data = $this->ProcessInputData('savePersonAmbulatCardLocat', true);
		$data = $this->ProcessInputData('savePersonAmbulatDeliverCard', true);
		if ($data === false)
			return false;
		
		$response = $this->dbmodel->savePersonAmbulatDeliverCard($data);
		$this->ProcessModelSave($response, true, 'Ошибка при создании движения карты')->ReturnData();
		
	}

	/**
	 * Привязываем амбулаторную карту к бирке
	 */
	function savePersonAmbulatCardInTimetableGraf(){
		$data = $this->ProcessInputData('savePersonAmbulatCardInTimetableGraf', true);
		if ($data === false){
			return false;
		}
		$response = $this->dbmodel->savePersonAmbulatCardInTimetableGraf($data);
		$this->ProcessModelSave($response, true, 'Ошибка')->ReturnData();
	}
	
	/**
	 * Получение идентификатора подразделения LpuBuilding_id по идентификатору службы(MedService_id)
	 */
	function getLpuBuildingByMedServiceId() {
		$data = $this->ProcessInputData('getLpuBuildingByMedServiceId', true);
		if ($data === false){
			return false;
		}
		$response = $this->dbmodel->getLpuBuildingByMedServiceId($data);
		$this->ReturnData($response);
		//$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	 * форма поиска амбулаторных карт
	 */
	function loadInformationAmbulatoryCards(){
		$data = $this->ProcessInputData('loadInformationAmbulatoryCards', false);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->loadInformationAmbulatoryCards($data);
		//$this->ProcessModelList($response, true, true)->ReturnData();
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}
	
	/**
	 * Запросить амб. карту у картохранилища
	 */
	function setAmbulatCardRequest(){
		$data = $this->ProcessInputData('setAmbulatCardRequest', true);
		if ($data === false) {
			return false;
		}

		$response = $this->dbmodel->setAmbulatCardRequest($data);
		//$this->ProcessModelList($response, true, true)->ReturnData();
		$this->ProcessModelSave($response, true, 'Ошибка')->ReturnData();
	}
}

?>
