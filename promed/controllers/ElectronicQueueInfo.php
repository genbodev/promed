<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * ElectronicQueueInfo - контроллер для работы с электронной очередью
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 *
 * @property ElectronicQueueInfo_model dbmodel
 */

class ElectronicQueueInfo extends swController {
	protected  $inputRules = array(
		'delete' => array(
			array(
				'field' => 'ElectronicQueueInfo_id',
				'label' => 'Идентификатор очереди',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'setElectronicQueueInfoIsOff' => array(
			array(
				'field' => 'ElectronicQueueInfo_id',
				'label' => 'Идентификатор очереди',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'ElectronicQueueInfo_IsOff',
				'label' => 'Признак выключенной очереди',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadList' => array(
			array(
				'field' => 'f_Lpu_id',
				'label' => 'Фильтр: ЛПУ',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuBuilding_id',
				'label' => 'Фильтр: Подразделение',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedService_id',
				'label' => 'Фильтр: Служба',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ElectronicQueueInfo_Code',
				'label' => 'Фильтр: Код',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'ElectronicQueueInfo_Name',
				'label' => 'Фильтр: Наименование',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'ElectronicQueueInfo_Nick',
				'label' => 'Фильтр: Краткое наименование',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'ElectronicQueueInfo_WorkRange',
				'label' => 'Фильтр: Период работы',
				'rules' => '',
				'type' => 'string'
			),
			array('default' => 0, 'field' => 'start','label' => 'Начальная запись','rules' => '','type' => 'int'),
			array('default' => 100, 'field' => 'limit','label' => 'Лимит записей','rules' => '','type' => 'int'),
		),
		'load' => array(
			array(
				'field' => 'ElectronicQueueInfo_id',
				'label' => 'Идентификатор очереди',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'getElectronicQueueGrid' => array(
			array(
				'field' => 'ElectronicService_id',
				'label' => 'Идентификатор пункта обслуживания',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'begDate',
				'label' => 'Дата начала',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'endDate',
				'label' => 'Дата окончания',
				'rules' => '',
				'type' => 'date'
			),
		),
		'loadAllRelatedLpu'=> array(
		),
		'save' => array(
			array(
				'field' => 'ElectronicQueueInfo_id',
				'label' => 'Идентификатор очереди',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор МО',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'ElectronicQueueAssign',
				'label' => 'Назначение',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'ElectronicQueueInfo_Code',
				'label' => 'Код',
				'rules' => 'trim|required',
				'type' => 'string'
			),
			array(
				'field' => 'ElectronicQueueInfo_Name',
				'label' => 'Наименование',
				'rules' => 'trim|required',
				'type' => 'string'
			),
			array(
				'field' => 'ElectronicQueueInfo_Nick',
				'label' => 'Краткое наименование',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'ElectronicQueueInfo_begDate',
				'label' => 'Дата начала',
				'rules' => 'required',
				'type' => 'date'
			),
			array(
				'field' => 'ElectronicQueueInfo_endDate',
				'label' => 'Дата окончания',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'ElectronicQueueInfo_CallTimeSec',
				'label' => 'Продолжительность вызова (сек.)',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'ElectronicQueueInfo_QueueTimeMin',
				'label' => 'Время, за которое возможна регистрация в очереди (мин.)',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'ElectronicQueueInfo_LateTimeMin',
				'label' => 'Время опоздания при регистрации в очереди (мин.)',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'IgnoreEQCodeDuplicate',
				'label' => 'IgnoreEQCodeDuplicate',
				'rules' => '',
				'type' => 'boolean'
			),
			array(
				'field' => 'MedService_id',
				'label' => 'Служба',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuBuilding_id',
				'label' => 'Подразделение',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_id',
				'label' => 'Отделение',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'ElectronicQueueInfo_IsOn',
				'label' => 'ЭО включена',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'hiddenSave',
				'label' => '',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'ElectronicQueueInfo_CallCount',
				'label' => 'Количество вызовов (до отмены пациента)',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'ElectronicQueueInfo_IsIdent',
				'label' => 'Идентифицикация пациента',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'ElectronicQueueInfo_IsCurDay',
				'label' => 'Запись на текущий день',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'ElectronicQueueInfo_IsAutoReg',
				'label' => 'Автоматическая регистрация в ЭО',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'servicesData',
				'label' => 'набор данных очередь-пункты обслуживания',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'ElectronicQueueInfo_PersCallDelTimeMin',
				'label' => 'Время отсрочки вызова пациента после регистрации',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'ElectronicQueueInfo_IsNoTTGInfo',
				'label' => 'Скрывать дату и время бирки при печати талона',
				'rules' => '',
				'type' => 'checkbox'
			)
		),
	);

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model('ElectronicQueueInfo_model', 'dbmodel');
	}

	/**
	 * Получить список талон электронной очереди
	 */
	function getElectronicQueueGrid()
	{
		$data = $this->ProcessInputData('getElectronicQueueGrid');
		if ($data === false) { return false; }

		$response = $this->dbmodel->getElectronicQueueGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Удаление очереди
	 */
	function delete()
	{
		$data = $this->ProcessInputData('delete');
		if ($data === false) { return false; }

		$response = $this->dbmodel->delete($data);
		$this->ProcessModelSave($response, true, 'Ошибка при удалении очереди')->ReturnData();
		return true;
	}

	/**
	 * Включение/выключение очереди
	 */
	function setElectronicQueueInfoIsOff()
	{
		$data = $this->ProcessInputData('setElectronicQueueInfoIsOff');
		if ($data === false) { return false; }

		$response = $this->dbmodel->setElectronicQueueInfoIsOff($data);
		$this->ProcessModelSave($response, true, 'Ошибка при включении/выключении очереди')->ReturnData();
		return true;
	}

	/**
	 * Возвращает список всех связанных c очередями ЛПУ
	 */
	function loadAllRelatedLpu()
	{
		$data = $this->ProcessInputData('loadAllRelatedLpu');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadAllRelatedLpu($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Возвращает список очередей
	 */
	function loadList()
	{
		$data = $this->ProcessInputData('loadList');
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadList($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Возвращает очередь
	 */
	function load()
	{
		$data = $this->ProcessInputData('load');
		if ($data === false) { return false; }

		$response = $this->dbmodel->load($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Сохранение очереди
	 */
	function save()
	{
		$data = $this->ProcessInputData('save');
		if ($data === false) { return false; }

		if ( !empty($data['ElectronicQueueInfo_begDate']) && !empty($data['ElectronicQueueInfo_endDate']) && $data['ElectronicQueueInfo_begDate'] > $data['ElectronicQueueInfo_endDate'] ) {
			$this->ReturnData(array(
				'Error_Msg' => 'Дата начала не может быть больше даты окончания',
				'Error_Code' => 146,
				'success' => false
			));
			return false;
		}
		
		if ( empty($data['MedService_id']) && empty($data['LpuBuilding_id']) && empty($data['LpuSection_id'])) {
			$this->ReturnData(array(
				'Error_Msg' => 'Нужно указать службу, подразделение или отделенеие',
				'Error_Code' => 149,
				'success' => false
			));
			return false;
		}

		// установим признак
		if ($data['ElectronicQueueInfo_IsOn']) {
			$data['ElectronicQueueInfo_IsOff'] = 1;
		} else {
			$data['ElectronicQueueInfo_IsOff'] = 2;
		}

		// начнем транзакцию
		$this->dbmodel->beginTransaction();
		// сохраним нашу очередь
		$response = $this->dbmodel->save($data);

		// откатим если ошибки
		if ( !$this->dbmodel->isSuccessful( $response ) || !empty($response[0]['Error_Code']) ){
			$this->dbmodel->rollbackTransaction() ;

			if(!empty($response[0]['Alert_Msg'])) {
				$this->ProcessModelSave($response)->ReturnData();
			} else {
				$this->ReturnError($response[0]['Error_Msg']);
			}
			return false;
		}

		// получим айди сущности из выполненного запроса
		if (empty($data['ElectronicQueueInfo_id'])) {
			$data['ElectronicQueueInfo_id'] = $response[0]['ElectronicQueueInfo_id'];
		}

		// если связанные пункты обслуживания есть, обновим\создадим их
		if (isset($data['servicesData'])) {

			// сформируем доп. параметры для передачи
			$linkedServicesParams = array(
				'ElectronicQueueInfo_id' => $data['ElectronicQueueInfo_id'],
				'jsonData' => $data['servicesData'],
				'pmUser_id' => $data['pmUser_id'],
				'Server_id' => $data['Server_id']
			);

			$saveLinkedServicesRes = $this->dbmodel->updateQueueElectronicServices($linkedServicesParams);

			if (!$this->dbmodel->isSuccessful($saveLinkedServicesRes)) {
				$this->dbmodel->rollbackTransaction() ;
				if (!empty($saveLinkedServicesRes[0]['Error_Msg'])) {
					$this->ReturnError($saveLinkedServicesRes[0]['Error_Msg']);
					return false;
				}
			}
		}

		// завершаем
		$this->dbmodel->commitTransaction();

		if(empty($data['hiddenSave'])) {
			// отправим сообщение ноду
			$nodeParams = array(
				'action' => 'setElectronicQueueInfoIsOff',
				'ElectronicQueueInfo_id' => $data['ElectronicQueueInfo_id'],
				'ElectronicQueueInfo_IsOff' => $data['ElectronicQueueInfo_IsOff']
			);

			$nodeResponse = $this->dbmodel->sendNodeRequest($nodeParams);

			if (!empty($nodeResponse)) {
				$response = $nodeResponse;
				if(!$nodeResponse['success']){// какая-то ошибка при отправке в Node.js
					$response['Error_Code'] = 104;// возвращаем код, чтобы форма показала уведомление и закрыла форму
				}
			}
		}

		$this->ProcessModelSave($response)->ReturnData();

		return true;
	}
}