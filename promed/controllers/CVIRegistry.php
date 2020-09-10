<?php
defined('BASEPATH') or die ('No direct script access allowed');
/**
 * CVIRegistry - Реестр КВИ
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2020 Swan Ltd.
 * @author
 * @version
 *
 * @property CVIRegistry_model $dbmodel
 */
class CVIRegistry extends swController {
	public $inputRules = [
		'loadGrid' => [
			[
				'field' => 'mode',
				'label' => 'Вкладка',
				'rules' => 'required',
				'type' => 'string'
			],
			[
				'field' => 'RegistryRecordType_id',
				'label' => 'Тип записи регистра',
				'rules' => '',
				'type' => 'int'
			],
			[
				'field' => 'PersonRegister_setDateRange',
				'label' => 'Дата включения в регистр',
				'rules' => '',
				'type' => 'daterange'
			],
			[
				'field' => 'CVIRegistry_setDTRange',
				'label' => 'Дата начала случая',
				'rules' => '',
				'type' => 'daterange'
			],
			[
				'field' => 'CVIRegistry_disDTRange',
				'label' => 'Дата окончания случая',
				'rules' => '',
				'type' => 'daterange'
			],
			[
				'field' => 'Lpu_id',
				'label' => 'МО',
				'rules' => '',
				'type' => 'int'
			],
			[
				'field' => 'Diag_id',
				'label' => 'Диагноз',
				'rules' => '',
				'type' => 'string'
			],
			[
				'field' => 'Diag_Code_From',
				'label' => 'Основной диагноз с',
				'rules' => '',
				'type' => 'string'
			],
			[
				'field' => 'Diag_Code_To',
				'label' => 'Основной диагноз по',
				'rules' => '',
				'type' => 'string'
			],
			[
				'field' => 'ControlCard_Type',
				'label' => 'Признак наличия контрольной карты',
				'rules' => '',
				'type' => 'int'
			],
			[
				'field' => 'TreatmentPlace',
				'label' => 'Место лечения',
				'rules' => '',
				'type' => 'int'
			],
			[
				'field' => 'ControlCard_OpenDateRange',
				'label' => 'Контрольная карта открыта на',
				'rules' => '',
				'type' => 'daterange'
			],
			[
				'field' => 'RegistryIncludeOnMSS',
				'label' => 'Признак включения в регистр на основе МСС',
				'rules' => '',
				'type' => 'int'
			],
			[
				'field' => 'ResultClass_id',
				'label' => 'Результат лечения',
				'rules' => '',
				'type' => 'int'
			],
			[
				'field' => 'LeaveType_id',
				'label' => 'Исход госпитализации',
				'rules' => '',
				'type' => 'int'
			],
			[
				'field' => 'LpuRegion_id',
				'label' => 'LpuRegion_id',
				'rules' => '',
				'type' => 'int'
			],
			[
				'field' => 'Status_id',
				'label' => 'Статус записи',
				'rules' => '',
				'type' => 'int'
			],
			[
				'field' => 'returnEmpty',
				'label' => 'returnEmpty',
				'rules' => '',
				'type' => 'int'
			],
			[
				'field' => 'pmuser_id',
				'label' => 'pmuser_id',
				'rules' => '',
				'type' => 'int'
			],
			[
				'field' => 'Person_SurName',
				'label' => 'Фамилия',
				'rules' => '',
				'type' => 'string'
			],
			[
				'field' => 'Person_FirName',
				'label' => 'Имя',
				'rules' => '',
				'type' => 'string'
			],
			[
				'field' => 'Person_SecName',
				'label' => 'Отчество',
				'rules' => '',
				'type' => 'string'
			]
		],
		'loadDiagList' => [
			[
				'field' => 'EvnPS_id',
				'label' => 'EvnPS_id',
				'rules' => '',
				'type' => 'id'
			],
			[
				'field' => 'EvnPL_id',
				'label' => 'EvnPL_id',
				'rules' => '',
				'type' => 'id'
			]
		],
		'loadResearch' => [
			[
				'field' => 'EvnDirection_id',
				'label' => 'EvnDirection_id',
				'rules' => 'required',
				'type' => 'id'
			]
		],
		'processOpenRecords' => []
	];

	function __construct() {
		parent::__construct();
		$this->load->database();
		$this->load->model('CVIRegistry_model', 'dbmodel');
	}

	public function loadDiagList() {
		$data = $this->ProcessInputData('loadDiagList', false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadDiagList($data);
		$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
		return true;
	}

	public function loadContactedGrid() {
		$data = $this->ProcessInputData('loadGrid', false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadContactedGrid($data);
		$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
		return true;
	}

	public function loadGrid() {
		$data = $this->ProcessInputData('loadGrid', false);
		if ($data === false) { return false; }

		if (!empty($data['returnEmpty']) && $data['returnEmpty'] == 2) {
			$this->ProcessModelMultiList(['data' => [], 'totalCount' => 0], true, true)->ReturnData();
			return true;
		}

		$DiedList = $this->dbmodel->loadDied($data);
		$CCList = $this->dbmodel->loadControlCard($data);
		$EvnPSList = $this->dbmodel->loadEvnPS($data);
		$EvnPLList = $this->dbmodel->loadEvnPL($data);

		$response = [];
		$tempArray = array_merge($CCList, $EvnPLList, $EvnPSList, $DiedList);

		if ($data['mode'] == 'suspicion') {
			foreach ($tempArray as $i => $outer) {
				if (array_key_exists('skip', $tempArray[$i]) && $tempArray[$i]['skip']) continue;
				$curEl = $outer;
				$tempArray[$i]['skip'] = true;
				foreach ($tempArray as $j => $inner) {
					if ($i == $j) continue;
					if (array_key_exists('skip', $tempArray[$j]) && $tempArray[$j]['skip']) continue;
					if ($curEl['Person_id'] == $inner['Person_id']) {
						if (($curEl['begDT'] < $inner['begDT']) || ($curEl['begDT'] <= $inner['begDT'] && $inner['RecType'] != 'pq')) {
							$curEl = $inner;
							$tempArray[$j]['skip'] = true;
						}
					}
				}
				$response[] = $curEl;
			}
		} else $response = $tempArray;

		uasort($response, function ($a, $b) {
			if ($a['begDT'] == $b['begDT']) return 0;
			return ($a['begDT'] > $b['begDT']) ? -1 : 1;
		});
		$result = [
			'data' => $response,
			'totalCount' => count($response)
		];
		$this->ProcessModelMultiList($result, true, true)->formatDatetimeFields()->ReturnData();
		return true;
	}

	public function loadMainDiagCombo() {
		$response = $this->dbmodel->loadMainDiagCombo();
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	public function loadResearch() {
		$data = $this->ProcessInputData('loadResearch', false);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadResearch($data);
		$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();
		return true;
	}

	public function processOpenRecords() {
		$data = $this->ProcessInputData('processOpenRecords', true);
		$response = $this->dbmodel->processOpenRecords();

		$this->ProcessModelList(['success' => true], true, true)->ReturnData();
		return true;
	}
}
