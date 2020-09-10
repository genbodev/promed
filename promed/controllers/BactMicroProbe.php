<?php defined('BASEPATH') or die('No direct script access allowed');
/**
 * BactMicroProbe
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      common
 * @access       public
 * @copyright    Copyright (c) 2009-2010 Swan Ltd.
 * @author       Qusijue
 * @version      Сентябрь 2019

 * @property BactMicroProbe_model $dbmodel
 */
class BactMicroProbe extends swController {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
		
		$dbName = 'default';
		$modelName = 'BactMicroProbe_model';
		if ($this->usePostgreLis) {
			$dbName = 'lis';
			$this->usePostgre = true;
		}
		$this->db = $this->load->database($dbName, true);
		$this->load->model($modelName, 'dbmodel');

		$this->inputRules = [
			'getWorkList' => [
				[
					'field' => 'EvnLabSample_id',
					'label' => 'Идентификатор Пробы',
					'rules' => '',
					'type' => 'id'
				], [
					'field' => 'EvnDirection_IsCito',
					'label' => 'Cito!',
					'rules' => '',
					'type' => 'id',
					'default' => null
				], [
					'field' => 'EvnLabSample_IsOutNorm',
					'label' => 'Отклонение',
					'rules' => '',
					'type' => 'id',
					'default' => null
				], [
					'field' => 'begDate',
					'label' => 'Начало периода',
					'rules' => '',
					'type' => 'date',
					'default' => null
				], [
					'field' => 'endDate',
					'label' => 'Конец периода',
					'rules' => '',
					'type' => 'date',
					'default' => null
				], [
					'field' => 'LpuSection_id',
					'label' => 'Отделение',
					'rules' => '',
					'type' => 'id'
				], [
					'field' => 'MedPersonal_id',
					'label' => 'Врач',
					'rules' => '',
					'type' => 'id'
				], [
					'field' => 'LabSampleStatus_id',
					'label' => 'Статус пробы',
					'rules' => '',
					'type' => 'id'
				], [
					'field' => 'MedService_id',
					'label' => 'Служба',
					'rules' => 'required',
					'type' => 'id'
				], [
					'field' => 'Person_ShortFio',
					'label' => 'ФИО',
					'rules' => '',
					'type' => 'string'
				], [
					'field' => 'EvnDirection_Num',
					'label' => 'Номер направления',
					'rules' => '',
					'type' => 'string'
				], [
					'field' => 'EvnLabSample_BarCode',
					'label' => 'Штрих-код',
					'rules' => '',
					'type' => 'string'
				], [
					'field' => 'MedServiceType_SysNick',
					'label' => 'Тип службы',
					'rules' => '',
					'type' => 'string'
				], [
					'field' => 'EvnLabSample_ShortNum',
					'label' => 'Номер пробы',
					'rules' => '',
					'type' => 'string'
				], [
					'field' => 'filterNewELSByDate',
					'label' => 'Фильтровать новые пробы по дате',
					'rules' => '',
					'type' => 'int'
				], [
					'field' => 'filterWorkELSByDate',
					'label' => 'Фильтровать пробы в работе по дате',
					'rules' => '',
					'type' => 'int'
				], [
					'field' => 'filterDoneELSByDate',
					'label' => 'Фильтровать пробы с результатами по дате',
					'rules' => '',
					'type' => 'int'
				], [
					'field' => 'UslugaComplex_id',
					'label' => 'Идентификатор комплексной услуги',
					'rules' => '',
					'type' => 'int'
				], [
					'field' => 'Lpu_sid',
					'label' => 'Медицинская организация',
					'rules' => '',
					'type' => 'int'
				], [
					'field' => 'LpuSection_sid',
					'label' => 'Отделение',
					'rules' => 'MedService_id',
					'type' => 'int'
				], [
					'field' => 'MedStaffFact_id',
					'label' => 'Врач',
					'rules' => '',
					'type' => 'int'
				], [
					'field' => 'EvnLabRequest_RegNum',
					'label' => 'Регистрационный номер',
					'rules' => '',
					'type' => 'string'
				]
			], 'getBactMicroProbeList' => [
				[
					'field' => 'EvnLabSample_id',
					'label' => 'Идентификатор Пробы',
					'rules' => '',
					'type' => 'id'
				], [
					'field' => 'BactMicroProbe_id',
					'label' => 'Идентификатор микроорганизма в пробе',
					'rules' => '',
					'type' => 'id'
				]
			], 'addMicro' => [
				[
					'field' => 'EvnLabRequest_id',
					'label' => 'Идентификатор заявки',
					'rules' => '',
					'type' => 'id'
				], [
					'field' => 'EvnLabSample_id',
					'label' => 'Идентификатор Пробы',
					'rules' => '',
					'type' => 'id'
				], [
					'field' => 'MicroList',
					'label' => 'Список микроорганизмов',
					'rules' => '',
					'type' => 'string'
				], [
					'field' => 'BactMicroProbe_IsNotShown',
					'label' => 'Микроорганизмы не выявлены',
					'rules' => '',
					'type' => 'string'
				], [
					'field' => 'EvnUslugaPar_id',
					'label' => 'Параклиническая услуга',
					'rules' => 'required|trim',
					'type' => 'string'
				]
			], 'cancelMicro' => [
				[
					'field' => 'MicroList',
					'label' => 'Список микроорганизмов',
					'rules' => '',
					'type' => 'string'
				],
				[
					'field' => 'EvnLabSample_id',
					'label' => 'Проба',
					'rules' => 'required',
					'type' => 'id'
				],
				[
					'field' => 'EvnLabRequest_id',
					'label' => 'Заявка',
					'rules' => 'required',
					'type' => 'id'
				]
			], 'updateOne' => [
				[
					'field' => 'UslugaTest_ResultValue',
					'label' => 'Значение результата',
					'rules' => '',
					'type' => 'string'
				], [
					'field' => 'UslugaTest_Comment',
					'label' => 'Комментарий',
					'rules' => '',
					'type' => 'string'
				], [
					'field' => 'BactMicroProbe_id',
					'label' => 'Чувствительность',
					'rules' => '',
					'type' => 'string'
				], [
					'field' => 'UslugaTest_setDT',
					'label' => 'Время выполнения',
					'rules' => '',
					'type' => 'string'
				]
			], 'getAntibioticList' => [
				[
					'field' => 'BactMicroProbe_id',
					'label' => 'BactMicroProbe_id',
					'rules' => '',
					'type' => 'string'
				]
			], 'getResearchList' => [
				[
					'field' => 'EvnDirection_id',
					'label' => 'Идентификатор направления',
					'rules' => 'trim|required',
					'type' => 'id'
				], [
					'field' => 'EvnLabSample_id',
					'label' => 'Идентификатор пробы',
					'rules' => 'trim|required',
					'type' => 'id'
				]
			]
		];
	}

	/**
	 * Функция получения рабочего списка проб
	 * @return bool
	 */
	public function getWorkList() {
		$data = $this->ProcessInputData('getWorkList', true);
		if ($data === false) return false;

		$response = $this->dbmodel->getWorkList($data);
		$sampleList = $this->ProcessModelList($response, true, true);
		$sampleList->formatDatetimeFields('d.m.Y H:i')->ReturnData();
		return true;
	}

	/**
	 * Функция получения списка микроорганизмов в выбранной пробе
	 * @return bool
	 */
	public function getBactMicroProbeList() {
		$data = $this->ProcessInputData('getBactMicroProbeList', true);
		if ($data === false) return false;

		$response = $this->dbmodel->getBactMicroProbeList($data);
		$sampleList = $this->ProcessModelList($response, true, true);
		$sampleList->formatDatetimeFields('d.m.Y H:i')->ReturnData();
		return true;
	}

	/**
	 * Добавление микоорганизмов в пробу
	 */
	public function addMicro() {
		$data = $this->ProcessInputData('addMicro', true);
		if ($data === false) return false;

		$microList = explode(',', $data['MicroList']);
		$this->load->model('EvnLabSample_model', 'EvnLabSample_model');
		$this->load->model('EvnUslugaPar_model', 'EvnUslugaPar_model');
		//$EvnUslugaPars = $this->EvnLabSample_model->getEvnUslugasRoot(['EvnLabSample_id' => $data['EvnLabSample_id']]);
		$labRequestData = $this->EvnLabSample_model->getDataFromEvnLabRequest(['EvnLabSample_id' => $data['EvnLabSample_id']]);
		$usedMicro = $this->dbmodel->getBactMicroProbeList($data);
		$EvnUslugaPar = $this->EvnUslugaPar_model->getUslugaParDataForNotice(['EvnUslugaPar_id' => $data['EvnUslugaPar_id']]);

		$response = [
			'add' => [],
			'skip' => []
		];

		if ($data['BactMicroProbe_IsNotShown'] == 2) $microList = [ null ];

		foreach ($microList as $microId) {
			if ($this->checkMicroUsed($microId, $usedMicro)) {
				$response['skip'][] = $microId;
				continue;
			}
			$utParams = [
				'UslugaTest_id' => null,
				'UslugaTest_pid' => $data['EvnUslugaPar_id'],
				'UslugaTest_rid' => $data['EvnUslugaPar_id'],
				'UslugaTest_setDT' => null,
				'Lpu_id' => $labRequestData['Lpu_id'],
				'Server_id' => $labRequestData['Server_id'],
				'PersonEvn_id' => $labRequestData['PersonEvn_id'],
				'PayType_id' => $labRequestData['PayType_id'],
				'UslugaTest_ResultUnit' => 'КОЕ/мл',
				'UslugaTest_ResultValue' => ($data['BactMicroProbe_IsNotShown'] == 2) ? '-' : null,
				'Unit_id' => 272787,
				'EvnLabSample_id' => $data['EvnLabSample_id'],
				'pmUser_id' => $data['pmUser_id']
			];
	
			$utResponse = $this->dbmodel->addUslugaTest($utParams);
	
			$microParams = [
				'EvnLabSample_id' => $data['EvnLabSample_id'],
				'BactMicro_id' => $microId,
				'Lpu_id' => $labRequestData['Lpu_id'],
				'UslugaTest_id' => $utResponse[0]['UslugaTest_id'],
				'BactMicroProbe_IsNotShown' => $data['BactMicroProbe_IsNotShown'],
				'pmUser_id' => $data['pmUser_id']
			];
	
			$bmResponse = $this->dbmodel->execCommonSP('p_BactMicroProbe_ins', $microParams);

			$response['add'][] = $microId;
		}

		$this->EvnLabSample_model->ReCacheLabSampleStatus([
			'EvnLabSample_id' => $data['EvnLabSample_id'],
			'MedServiceType_SysNick' => 'microbiolab'
		]);

		$this->load->model('EvnLabRequest_model', 'EvnLabRequest_model');
		$this->EvnLabRequest_model->ReCacheLabRequestStatus([
			'EvnLabRequest_id' => $data['EvnLabRequest_id'],
			'pmUser_id' => $data['pmUser_id']
		]);

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode([
				'add' => $response['add'],
				'skip' => $response['skip']
		]));
	}

	/**
	 * Удаление микроорганизмов из пробы
	 */
	public function cancelMicro() {
		$data = $this->ProcessInputData('cancelMicro', true);
		if ($data === false) return false;

		$microList = json_decode($data['MicroList'], true);

		$flag = true;
		$this->dbmodel->beginTransaction();
		foreach ($microList as $micro) {
			$response = $this->dbmodel->execCommonSP('p_BactMicroProbe_del', [
				'BactMicroProbe_id' => $micro['BactMicroProbe_id']
			]);
			$flag &= $response['success'];

			$response = $this->dbmodel->execCommonSP('p_UslugaTest_del', [
				'UslugaTest_id' => $micro['UslugaTest_id']
			]);
			$flag &= $response['success'];

			$response = $this->deleteAntibiotics([
				'BactMicroProbe_id' => $micro['BactMicroProbe_id']
			]);
			$flag &= $response['success'];
		}

		if ($flag) {
			$this->load->model('EvnLabSample_model', 'EvnLabSample_model');
			$this->EvnLabSample_model->ReCacheLabSampleStatus([
				'EvnLabSample_id' => $data['EvnLabSample_id'],
				'MedServiceType_SysNick' => 'microbiolab'
			]);

			$this->load->model('EvnLabRequest_model', 'EvnLabRequest_model');
			$this->EvnLabRequest_model->ReCacheLabRequestStatus([
				'EvnLabRequest_id' => $data['EvnLabRequest_id'],
				'pmUser_id' => $data['pmUser_id']
			]);
			$this->dbmodel->commitTransaction();
		} else {
			$this->dbmodel->rollbackTransaction();
			$this->ReturnError('Не удалось выполнить удаление микроорганизмов');
		}
		return true;
	}

	/**
	 * Удаление антибиотиков микроорганизма
	 */
	private function deleteAntibiotics($params) {
		$antibioticList = $this->dbmodel->getAntibioticList($params);
		$flag = true;
		foreach ($antibioticList as $antibiotic) {
			$response = $this->dbmodel->execCommonSP('p_BactMicroProbeAntibiotic_del', [
				'BactMicroProbeAntibiotic_id' => $antibiotic['BactMicroProbeAntibiotic_id']
			]);
			$flag &= $response['success'];
			$response = $this->dbmodel->execCommonSP('p_UslugaTest_del', [
				'UslugaTest_id' => $antibiotic['UslugaTest_id']
			]);
			$flag &= $response['success'];
			if (!$flag) break;
		}
		return [
			'success' => $flag
		];
	}

	/**
	 * Обновление записи проба-микроорганизм
	 */
	public function updateOne() {
		$data = $this->ProcessInputData('updateOne', true);
		if ($data === false) return false;
		
		$this->load->model('EvnLabSample_model', 'EvnLabSample_model');
		$micro = $this->dbmodel->getBactMicroProbeList([
			'BactMicroProbe_id' => $data['BactMicroProbe_id']
		]);

		$params['UslugaTest_id'] = $micro[0]['UslugaTest_id'];
		$params['pmUser_id'] = $data['pmUser_id'];
		$params['session'] = $data['session'];
		
		$params['updateType'] = 'value';
		$params['UslugaTest_ResultValue'] = $data['UslugaTest_ResultValue'];
		$response = $this->EvnLabSample_model->updateResult($params);

		$params['updateType'] = 'comment';
		$params['UslugaTest_Comment'] = $data['UslugaTest_Comment'];
		unset($params['UslugaTest_ResultValue']);
		$response = $this->EvnLabSample_model->updateResult($params);

		$response[0]['BactMicroProbe_id'] = $data['BactMicroProbe_id'];
		$response['success'] = true;
		$this->ReturnData($response);
		return true;
	}

	/**
	 * Функция получения списка антибиотиков
	 * @return bool
	 */
	public function getAntibioticList() {
		$data = $this->ProcessInputData('getAntibioticList', true);
		if ($data === false) return false;

		$response = $this->dbmodel->getAntibioticList($data);
		$antibioticList = $this->ProcessModelList($response, true, true);
		$antibioticList->ReturnData();
		return true;
	}
	
	/**
	 * Функция получения списка исследований направления
	 * @return bool
	 */
	public function getResearchList() {
		$data = $this->ProcessInputData('getResearchList', true);
		if ($data === false) return false;
		
		$response = $this->dbmodel->getResearchList($data);
		$researchList = $this->ProcessModelList($response, true, true);
		$researchList->ReturnData();
		return true;
	}

	/**
	 * Проверка на наличие микроорганизма в пробе
	 */
	private function checkMicroUsed($microId, $usedMicrogetBactMicroProbeList) {
		$flag = false;
		foreach ($usedMicrogetBactMicroProbeList as $micro) {
			if ($microId == $micro['BactMicro_id']) $flag = true;
		}
		return $flag;
	}
}
