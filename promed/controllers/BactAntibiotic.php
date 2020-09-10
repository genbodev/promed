<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
* BactAntibiotic
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* @package      common
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @author       Qusijue
* @version      Сентябрь 2019

 * @property BactAntibiotic_model $dbmodel
*/
class BactAntibiotic extends swController {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
		
		$dbName = 'default';
		$modelName = 'BactAntibiotic_model';
		if ($this->usePostgreLis) {
			$dbName = 'lis';
			$this->usePostgre = true;
		}
		$this->db = $this->load->database($dbName, true);
		$this->load->model($modelName, 'dbmodel');

		$this->inputRules = [
			'getAntibioticList' => [
				[
					'field' => 'BactAntibioticLev_id',
					'label' => 'BactAntibioticLev_id',
					'rules' => '',
					'type' => 'int'
				], [
					'field' => 'MedService_id',
					'label' => 'MedService_id',
					'rules' => '',
					'type' => 'string'
				], [
					'field' => 'BactGuideline_Code',
					'label' => 'BactGuideline_Code',
					'rules' => '',
					'type' => 'string'
				], [
					'field' => 'BactGramColor_Code',
					'label' => 'BactGramColor_Code',
					'rules' => '',
					'type' => 'string'
				], [
					'field' => 'BactAntibiotic_Name',
					'label' => 'BactAntibiotic_Name',
					'rules' => '',
					'type' => 'string'
				], [
					'field' => 'mode',
					'label' => 'mode',
					'rules' => '',
					'type' => 'string'
				]
			], 'insertAntibioticToLab' => [
				[
					'field' => 'AntibioticList',
					'label' => 'AntibioticList',
					'rules' => '',
					'type' => 'string'
				], [
					'field' => 'MedService_id',
					'label' => 'MedService_id',
					'rules' => '',
					'type' => 'string'
				]
			], 'deleteAntibioticFromLab' => [
				[
					'field' => 'AntibioticList',
					'label' => 'AntibioticList',
					'rules' => '',
					'type' => 'string'
				], [
					'field' => 'MedService_id',
					'label' => 'MedService_id',
					'rules' => '',
					'type' => 'string'
				]
			], 'deleteAntibioticFromMicro' => [
				[
					'field' => 'AntibioticList',
					'label' => 'AntibioticList',
					'rules' => '',
					'type' => 'string'
				]
			], 'getMicroAntibioticList' => [
				[
					'field' => 'BactMicroProbe_id',
					'label' => 'BactMicroProbe_id',
					'rules' => '',
					'type' => 'string'
				]
			], 'insertAntibioticToMicro' => [
				[
					'field' => 'AntibioticList',
					'label' => 'AntibioticList',
					'rules' => '',
					'type' => 'string'
				], [
					'field' => 'BactMicroProbe_id',
					'label' => 'BactMicroProbe_id',
					'rules' => '',
					'type' => 'string'
				], [
					'field' => 'EvnLabSample_id',
					'label' => 'EvnLabSample_id',
					'rules' => '',
					'type' => 'string'
				], [
					'field' => 'BactMethod_id',
					'label' => 'BactMethod_id',
					'rules' => '',
					'type' => 'string'
				], [
					'field' => 'BactMicro_id',
					'label' => 'BactMicro_id',
					'rules' => '',
					'type' => 'string'
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
					'field' => 'BactMicroProbeAntibiotic_id',
					'label' => 'BactMicroProbeAntibiotic_id',
					'rules' => '',
					'type' => 'string'
				], [
					'field' => 'BactMicroABPSens_id',
					'label' => 'Чувствительность',
					'rules' => '',
					'type' => 'string'
				]
			], 'getSampleAllowAntibioticList' => [
				[
					'field' => 'EvnLabSample_id',
					'label' => 'Идентификатор пробы',
					'rules' => '',
					'type' => 'string'
				], [
					'field' => 'MedService_id',
					'label' => 'Идентификатор службы',
					'rules' => '',
					'type' => 'string'
				], [
					'field' => 'mode',
					'label' => 'mode',
					'rules' => '',
					'type' => 'string'
				], [
					'field' => 'BactMicroProbe_id',
					'label' => 'BactMicroProbe_id',
					'rules' => '',
					'type' => 'string'
				], [
					'field' => 'BactAntibiotic_Name',
					'label' => 'BactAntibiotic_Name',
					'rules' => '',
					'type' => 'string'
				], [
					'field' => 'BactGuideline_Code',
					'label' => 'BactGuideline_Code',
					'rules' => '',
					'type' => 'string'
				]
			], 'update' => [
				[
					'field' => 'data',
					'label' => 'data',
					'rules' => '',
					'type' => 'string'
				]
			], 'approveResult' => [
				[
					'field' => 'AntibioticList',
					'label' => 'AntibioticList',
					'rules' => '',
					'type' => 'string'
				]
			], 'unapproveResult' => [
				[
					'field' => 'AntibioticList',
					'label' => 'AntibioticList',
					'rules' => '',
					'type' => 'string'
				]
			]
		];
	}

	/**
	 * Возвращает срисок антибиотиков
	 */
	public function getAntibioticList() {
		$data = $this->ProcessInputData('getAntibioticList', true);
		if ($data === false) return false;
		//BactGramColor_Code
		$classList = $this->dbmodel->getAntibioticList([
			'mode' => $data['mode'],
			'MedService_id' => $data['MedService_id'],
			'BactAntibioticLev_id' => 1
		]);
		$subclassList = $this->dbmodel->getAntibioticList([
			'mode' => $data['mode'],
			'MedService_id' => $data['MedService_id'],
			'BactAntibioticLev_id' => 2
		]);
		$antibioticList = $this->dbmodel->getAntibioticList([
			'mode' => $data['mode'],
			'MedService_id' => $data['MedService_id'],
			'BactAntibioticLev_id' => 3,
			'BactAntibiotic_Name' => $data['BactAntibiotic_Name'],
			'BactGuideline_Code' => $data['BactGuideline_Code']
		]);

		$treeData = [];
		foreach ($classList as $class) {
			$node = [
				'text' => toUTF(trim($class['BactAntibiotic_Name'])),
				'id' => "BactAntibiotic_1_{$class['BactAntibiotic_id']}",
				'leaf' => false,
				'checked' => false,
				'level' => $class['BactAntibioticLev_id'],
				'children' => []
			];
			foreach ($subclassList as $subclass) {
				if ($subclass['BactAntibiotic_pid'] != $class['BactAntibiotic_id']) continue;
				$subNode = [
					'text' => toUTF(trim($subclass['BactAntibiotic_Name'])),
					'id' => "BactAntibiotic_2_{$subclass['BactAntibiotic_id']}",
					'checked' => false,
					'level' => $subclass['BactAntibioticLev_id'],
					'leaf' => false,
					'children' => []
				];
				foreach ($antibioticList as $antibiotic) {
					if ($antibiotic['BactAntibiotic_pid'] != $subclass['BactAntibiotic_id']) continue;
					$name = empty($antibiotic['BactAntibiotic_Name']) ? "" : trim($antibiotic['BactAntibiotic_Name']);
					$potency = empty($antibiotic['BactAntibiotic_POTENCY']) ? "" : trim($antibiotic['BactAntibiotic_POTENCY']);
					$guideline = empty($antibiotic['BactGuideline_Name']) ? "" : trim($antibiotic['BactGuideline_Name']);


					$fullName = join(" ", [
						$name,
						"[".$potency,
						$guideline."]"
					]);
					$leaf = [
						'BactAntibiotic_id' => $antibiotic['BactAntibiotic_id'],
						'text' => toUTF($fullName),
						'checked' => false,
						'level' => $antibiotic['BactAntibioticLev_id'],
						'id' => "BactAntibiotic_3_{$antibiotic['BactAntibiotic_id']}",
						'leaf' => true
					];
					$subNode['children'][] = $leaf;
				}
				if (count($subNode['children']) == 0) continue;
				$node['children'][] = $subNode;
			}
			if (count($node['children']) == 0) continue;
			$treeData[] = $node;
		}
		$this->output
			->set_content_type('application/json')
			->set_output(json_encode($treeData));
	}

	/**
	 * Возвращает дерево антибиотиков доступных для микроорганизма в пробе
	 */
	public function getSampleAllowAntibioticList() {
		$data = $this->ProcessInputData('getSampleAllowAntibioticList', true);
		if ($data === false) return false;

		$classList = $this->dbmodel->getAntibioticList([
			'mode' => $data['mode'],
			'MedService_id' => $data['MedService_id'],
			'BactAntibioticLev_id' => 1
		]);
		$data['BactAntibioticLev_id'] = 2; $subclassList = $this->dbmodel->getAntibioticList([
			'mode' => $data['mode'],
			'MedService_id' => $data['MedService_id'],
			'BactAntibioticLev_id' => 2
		]);
		$data['BactAntibioticLev_id'] = 3; $antibioticList = $this->dbmodel->getAntibioticList([
			'mode' => $data['mode'],
			'MedService_id' => $data['MedService_id'],
			'BactAntibioticLev_id' => 3,
			'BactAntibiotic_Name' => $data['BactAntibiotic_Name'],
			'BactGuideline_Code' => $data['BactGuideline_Code']
		]);
		$usedList = $this->dbmodel->getUsedAntibiotic([
			'BactMicroProbe_id' => $data['BactMicroProbe_id']
		]);

		$treeData = [];
		foreach ($classList as $class) {
			$node = [
				'text' => toUTF(trim($class['BactAntibiotic_Name'])),
				'id' => "BactAntibiotic_1_{$class['BactAntibiotic_id']}",
				'leaf' => false,
				'checked' => false,
				'level' => $class['BactAntibioticLev_id'],
				'children' => []
			];
			foreach ($subclassList as $subclass) {
				if ($subclass['BactAntibiotic_pid'] != $class['BactAntibiotic_id']) continue;
				$subNode = [
					'text' => toUTF(trim($subclass['BactAntibiotic_Name'])),
					'id' => "BactAntibiotic_2_{$subclass['BactAntibiotic_id']}",
					'checked' => false,
					'level' => $subclass['BactAntibioticLev_id'],
					'leaf' => false,
					'children' => []
				];
				foreach ($antibioticList as $antibiotic) {
					if ($antibiotic['BactAntibiotic_pid'] != $subclass['BactAntibiotic_id']) continue;
					$name = empty($antibiotic['BactAntibiotic_Name']) ? "" : trim($antibiotic['BactAntibiotic_Name']);
					$potency = empty($antibiotic['BactAntibiotic_POTENCY']) ? "" : trim($antibiotic['BactAntibiotic_POTENCY']);
					$guideline = empty($antibiotic['BactGuideline_Name']) ? "" : trim($antibiotic['BactGuideline_Name']);

					$fullName = join(" ", [
						$name,
						"[".$potency,
						$guideline."]"
					]);
					$leaf = [
						'BactAntibiotic_id' => $antibiotic['BactAntibiotic_id'],
						'text' => toUTF($fullName),
						'checked' => false,
						'level' => $antibiotic['BactAntibioticLev_id'],
						'id' => "BactAntibiotic_3_{$antibiotic['BactAntibiotic_id']}",
						'leaf' => true
					];

					$elements = $this->getAllowAntibiotic($leaf, $usedList);
					foreach ($elements as $temp) {
						$subNode['children'][] = $temp;
					}
				}
				if (count($subNode['children']) == 0) continue;
				$node['children'][] = $subNode;
			}
			if (count($node['children']) == 0) continue;
			$treeData[] = $node;
		}

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode($treeData));
	}

	/**
	 * Добавление антибиотика в лабораторию
	 */
	public function insertAntibioticToLab() {
		$data = $this->ProcessInputData('insertAntibioticToLab', true);
		if ($data === false) return false;

		$antibioticList = explode(',', $data['AntibioticList']);
		$this->db->trans_begin();

		try {
			foreach ($antibioticList as $antibiotic) {
				if (empty($antibiotic)) continue;
				$params = [
					'BactAntibiotic_id' => $antibiotic,
					'MedService_id' => $data['MedService_id'],
					'pmUser_id' => $data['session']['pmuser_id']
				];
				$resp = $this->dbmodel->execCommonSP('p_BactAntibioticLab_ins', $params);
				if (!empty($resp[0]['Error_Msg'])) {
					throw new Exception("Не удалось добавить антибиотик в лабораторию");
				}
			}
		} catch (Exception $e) {
			$this->db->trans_rollback();
			$val['success'] = false;
			$val['Error_Msg'] = toUTF($e->getMessage());
			$this->ReturnData($val);
			return false;
		}

		$this->db->trans_commit();
		$this->output
			->set_content_type('application/json')
			->set_output(json_encode([
				'mode' => 'Antibiotic'
			]));
	}

	/**
	 * Добавление антибиотика в микроорганизм
	 */
	public function insertAntibioticToMicro() {
		$data = $this->ProcessInputData('insertAntibioticToMicro', true);
		if ($data === false) return false;

		$antibioticList = json_decode($data['AntibioticList'], true);
		
		$this->load->model('EvnLabSample_model', 'EvnLabSample_model');
		$EvnUslugaPars = $this->EvnLabSample_model->getEvnUslugasRoot(['EvnLabSample_id' => $data['EvnLabSample_id']]);
		$labRequestData = $this->EvnLabSample_model->getDataFromEvnLabRequest(['EvnLabSample_id' => $data['EvnLabSample_id']]);
		$usedList = $this->dbmodel->getMicroAntibioticList($data);
		
		$this->db->trans_begin();
		try {
			foreach ($antibioticList as $antibiotic) {
			
				if ($this->checkAntibioticUsed($antibiotic, $usedList)) continue;
	
				$sens = $this->dbmodel->getABPSense([
					'BactMicro_id' => $antibiotic['BactMicro_id'],
					'BactMethod_id' => $antibiotic['BactMethod_id'],
					'BactAntibiotic_id' => $antibiotic['BactAntibiotic_id']
				]);
	
				$sens = !empty($sens[0]) ? $sens[0] : [
					'BactMicroAntibioticSens_min' => null,
					'BactMicroAntibioticSens_max' => null,
					'BactMicroAntibioticSens_id' => null
				];
	
				$utParams = [
					'UslugaTest_id' => null,
					'UslugaTest_pid' => $EvnUslugaPars[0]['EvnUslugaPar_id'],
					'UslugaTest_rid' => $EvnUslugaPars[0]['EvnUslugaPar_id'],
					'Lpu_id' => $labRequestData['Lpu_id'],
					'Server_id' => $labRequestData['Server_id'],
					'PersonEvn_id' => $labRequestData['PersonEvn_id'],
					'PayType_id' => $labRequestData['PayType_id'],
					'UslugaTest_ResultUnit' => $antibiotic['BactMethod_id'] == 1 ? 'мг/л' : 'мм',
					'Unit_id' => $antibiotic['BactMethod_id'] == 1 ? 10138 : 50,
					'EvnLabSample_id' => $data['EvnLabSample_id'],
					'UslugaTest_ResultLower' => $sens['BactMicroAntibioticSens_min'],
					'UslugaTest_ResultUpper' => $sens['BactMicroAntibioticSens_max'],
					'pmUser_id' => $data['pmUser_id']
				];
		
				$utResponse = $this->dbmodel->addUslugaTest($utParams);
		
				$antibioticParams = [
					'BactMicroProbe_id' => $data['BactMicroProbe_id'],
					'BactMethod_id' => $antibiotic['BactMethod_id'],
					'BactAntibiotic_id' => $antibiotic['BactAntibiotic_id'],
					'BactMicroABPSens_id' => null,
					'UslugaTest_id' => $utResponse[0]['UslugaTest_id'],
					'pmUser_id' => $data['pmUser_id']
				];
		
				$antibioticResponse = $this->dbmodel->execCommonSP('p_BactMicroProbeAntibiotic_ins', $antibioticParams);
				
				if ($utResponse[0]['Error_Code'] != null || $antibioticResponse[0]['Error_Code'] != null) {
					throw new Exception('Не удалось назначить антибиотик для микроорганизма');
				}
			}
		} catch (Exception $e) {
			$this->db->trans_rollback();
			$val['success'] = false;
			$val['Error_Msg'] = toUTF($e->getMessage());
			$this->ReturnData($val);
			return false;
		}
	
		$this->db->trans_commit();
		$this->output
			->set_content_type('application/json')
			->set_output(json_encode(true));
	}

	/**
	 * Удаление антибиотика из лаборатории
	 */
	public function deleteAntibioticFromLab() {
		$data = $this->ProcessInputData('deleteAntibioticFromLab', true);
		if ($data === false) return false;

		$this->db->trans_begin();
		try {
			$antibioticList = $this->dbmodel->getLabAntibioticList([
			'BactAntibiotic_id' => $data['AntibioticList'],
			'MedService_id' => $data['MedService_id']
			]);
			foreach ($antibioticList as $antibiotic) {
				$params = [
					'BactAntibioticLab_id' => $antibiotic['BactAntibioticLab_id'],
					'pmUser_id' => $data['session']['pmuser_id']
				];
				$resp = $this->dbmodel->execCommonSP('p_BactAntibioticLab_del', $params);
				if (!empty($resp[0]['Error_Msg'])) {
					throw new Exception('Не удалось удалить антибиотик из лаборатории');
				}
			}
		} catch (Exception $e) {
			$this->db->trans_rollback();
			$val['success'] = false;
			$val['Error_Msg'] = toUTF($e->getMessage());
			$this->ReturnData($val);
			return false;
		}
	
		$this->db->trans_commit();
		
		$this->output
			->set_content_type('application/json')
			->set_output(json_encode([
				'mode' => 'Antibiotic'
			]));
	}

	/**
	 * Удаление антибиотика из микроорганизма
	 */
	public function deleteAntibioticFromMicro() {
		$data = $this->ProcessInputData('deleteAntibioticFromMicro', true);
		if ($data === false) return false;

		$this->db->trans_begin();
		try {
			$antibioticList = json_decode($data['AntibioticList'], true);
			
			foreach ($antibioticList as $antibiotic) {
				$bmpaID = array_key_exists('BactMicroProbeAntibiotic_id', $antibiotic) ? $antibiotic['BactMicroProbeAntibiotic_id'] : null;
				$utID = array_key_exists('UslugaTest_id', $antibiotic) ? $antibiotic['UslugaTest_id'] : null;
				if (empty($bmpaID) || empty($utID)) continue;
				
				$bmpaResp = $this->dbmodel->execCommonSP('p_BactMicroProbeAntibiotic_del', [
					'BactMicroProbeAntibiotic_id' => $antibiotic['BactMicroProbeAntibiotic_id'],
					'pmUser_id' => $data['session']['pmuser_id']
				]);

				$utResp = $this->dbmodel->execCommonSP('p_UslugaTest_del', [
					'UslugaTest_id' => $antibiotic['UslugaTest_id'],
					'pmUser_id' => $data['session']['pmuser_id']
				]);

				if (!empty($bmpaResp[0]['Error_Msg']) || !empty($utResp[0]['Error_Msg'])) {
					throw new Exception("Не удалось удалить назначенный антибиотик");
				}
			}
		} catch (Exception $e) {
			$this->db->trans_rollback();
			$val['success'] = false;
			$val['Error_Msg'] = toUTF($e->getMessage());
			$this->ReturnData($val);
			return false;
		}
		$this->db->trans_commit();
		$this->output
			->set_content_type('application/json')
			->set_output(json_encode(true));
	}

	/**
	 * Возвращает список антибиотиков в микроорганизме
	 */
	public function getMicroAntibioticList() {
		$data = $this->ProcessInputData('getMicroAntibioticList', true);
		if ($data === false) return false;

		$antibioticList = $this->dbmodel->getMicroAntibioticList($data);

		$sampleList = $this->ProcessModelList($antibioticList, true, true);
		$sampleList->formatDatetimeFields('d.m.Y H:i')->ReturnData();
	}

	/**
	 * Обновление записи антибиотик-услуга тест
	 */
	public function update() {
		$data = $this->ProcessInputData('update', true);
		if ($data === false) return false;

		$paramList = json_decode($data['data'], true);

		$this->db->trans_begin();
		try {
			foreach ($paramList as $key => $params) {
				$micro = $this->dbmodel->getMicroAntibioticList([
					'BactMicroProbeAntibiotic_id' => $params['BactMicroProbeAntibiotic_id']
				]);
	
				$utParams['UslugaTest_id'] = $micro[0]['UslugaTest_id'];
				$utParams['UslugaTest_pid'] = $micro[0]['UslugaTest_pid'];
				$utParams['UslugaTest_rid'] = $micro[0]['UslugaTest_rid'];
				$utParams['UslugaTest_setDT'] = $micro[0]['UslugaTest_setDT'];
				$utParams['UslugaTest_disDT'] = $micro[0]['UslugaTest_disDT'];
				$utParams['Lpu_id'] = $micro[0]['Lpu_id'];
				$utParams['Server_id'] = $micro[0]['Server_id'];
				$utParams['Usluga_id'] = $micro[0]['Usluga_id'];
				$utParams['PayType_id'] = $micro[0]['PayType_id'];
				$utParams['UslugaPlace_id'] = $micro[0]['UslugaPlace_id'];
				$utParams['UslugaTest_ResultUnit'] = $micro[0]['UslugaTest_ResultUnit'];
				$utParams['UslugaTest_ResultApproved'] = 1;
				$utParams['UslugaTest_ResultAppDate'] = null;
				$utParams['UslugaTest_ResultCancelReason'] = $micro[0]['UslugaTest_ResultCancelReason'];
				$utParams['Unit_id'] = $micro[0]['Unit_id'];
				$utParams['UslugaTest_Kolvo'] = $micro[0]['UslugaTest_Kolvo'];
				$utParams['UslugaTest_Result'] = $micro[0]['UslugaTest_Result'];
				$utParams['EvnLabSample_id'] = $micro[0]['EvnLabSample_id'];
				$utParams['EvnLabRequest_id'] = $micro[0]['EvnLabRequest_id'];
				$utParams['UslugaTest_CheckDT'] = $micro[0]['UslugaTest_CheckDT'];
				$utParams['UslugaTest_ResultLower'] = $micro[0]['UslugaTest_ResultLower'];
				$utParams['UslugaTest_ResultUpper'] = $micro[0]['UslugaTest_ResultUpper'];
				$utParams['UslugaTest_ResultValue'] = $params['UslugaTest_ResultValue'];
				$utParams['UslugaTest_Comment'] = $params['UslugaTest_Comment'];
				$utParams['pmUser_id'] = $data['pmUser_id'];
	
				$bmpaParams['BactMicroProbe_id'] = $micro[0]['BactMicroProbe_id'];
				$bmpaParams['BactAntibiotic_id'] = $micro[0]['BactAntibiotic_id'];
				$bmpaParams['BactMicroABPSens_id'] = $params['BactMicroABPSens_id'];
				$bmpaParams['BactMethod_id'] = $micro[0]['BactMethod_id'];
				$bmpaParams['UslugaTest_id'] = $micro[0]['UslugaTest_id'];
				$bmpaParams['BactMicroProbeAntibiotic_id'] = $params['BactMicroProbeAntibiotic_id'];
				$bmpaParams['pmUser_id'] = $data['pmUser_id'];
	
				$utResponse = $this->dbmodel->execCommonSP('p_UslugaTest_upd', $utParams);
				$bmpaResponse = $this->dbmodel->execCommonSP('p_BactMicroProbeAntibiotic_upd', $bmpaParams);
			}
		} catch (Exception $e) {
			$this->db->trans_rollback();
			$val['success'] = false;
			$val['Error_Msg'] = toUTF($e->getMessage());
			$this->ReturnData($val);
			return false;
		}
		$this->db->trans_commit();
		$this->ReturnData(['success' => true]);
		return true;
	}

	public function updateOne() {
		$data = $this->ProcessInputData('updateOne', true);
		if ($data === false) return false;

		$utParamList = [
			'UslugaTest_ResultValue',
			'UslugaTest_Comment'
			//'UslugaTest_Status'
		];
		$baParamList = [
			'BactMicroABPSens_id'
		];
		$newValue = "";
		$tableName = "";
		$whereClause = "";
		$changedField = "";

		$this->db->trans_begin();
		try {
			foreach ($data as $key => $value) {
				if (empty($value)) continue;
				if (in_array($key, $utParamList)) {
					$tableName = 'UslugaTest';
					$whereClause = 'UslugaTest_id = @ut_id';
				}
				else if (in_array($key, $baParamList)) {
					$tableName = 'BactMicroProbeAntibiotic';
					$whereClause = 'BactMicroProbeAntibiotic_id = :BactMicroProbeAntibiotic_id';
				}
				else continue;

				$changedField = "{$key} = '{$value}',";
				break;
			}
			if (empty($tableName)) {
				throw new Exception('При сохранении результатов произошла ошибка');
			}

			$params['pmUser_id'] = $data['pmUser_id'];
			$params['BactMicroProbeAntibiotic_id'] = $data['BactMicroProbeAntibiotic_id'];
			$params['changedField'] = $changedField;

			if ($tableName == "BactMicroProbeAntibiotic") {
				$this->dbmodel->updateAntibiotic($params);
				$params['changedField'] = '';
			}

			$this->dbmodel->updateUslugaTest($params);

		} catch (Exception $e) {
			$this->db->trans_rollback();
			$val['success'] = false;
			$val['Error_Msg'] = toUTF($e->getMessage());
			$this->ReturnData($val);
			return false;
		}

		$this->db->trans_commit();
		$this->ReturnData([
			'success' => true
		]);
	}

	/**
	 * Проверка на использование антибиотика
	 */
	private function checkAntibioticUsed($antibiotic, $usedList) {
		$flag = false;
		foreach ($usedList as $used) {
			$tempFlag = $antibiotic['BactAntibiotic_id'] == $used['BactAntibiotic_id'];
			$tempFlag &= $antibiotic['BactMethod_id'] == $used['BactMethod_id'];
			$flag |= $tempFlag;
		}
		return $flag;
	}

	/**
	 * Возвращает список доступных антибиотиков
	 */
	private function getAllowAntibiotic($antibiotic, $usedList) {
		$elements = [];
		if ($antibiotic['level'] != 3) {
			$elements[] = $antibiotic;
			return $elements;
		}
		$methodCodes = [
			1 => 'MIC',
			2 => 'DISC'
		];
		foreach ($methodCodes as $code => $name) {
			$temp = $antibiotic;
			$temp['BactMethod_id'] = $code;
			$temp['text'] .= ' ' . $name;
			$temp['id'] .= $name;
			$flag = false;
			foreach ($usedList as $used) {
				$tempflag = $temp['BactAntibiotic_id'] == $used['BactAntibiotic_id'];
				$tempflag &= $temp['BactMethod_id'] == $used['BactMethod_id'];

				$flag |= $tempflag;
			}
			if ($flag) continue;
			$elements[] = $temp;
		}
		return $elements;
	}

	/**
	 * Одобрение результата
	 */
	public function approveResult() {
		$data = $this->ProcessInputData('approveResult', true);
		if ($data === false) return false;

		$antibioticList = json_decode($data['AntibioticList'], true);

		foreach ($antibioticList as $antibiotic) {
			if (empty($antibiotic['UslugaTest_ResultValue'])) continue;

			$bmpa = $this->dbmodel->getMicroAntibioticList([
				'BactMicroProbeAntibiotic_id' => $antibiotic['BactMicroProbeAntibiotic_id']
			]);

			$params = [
				'UslugaTest_id' => $bmpa[0]['UslugaTest_id'],
				'UslugaTest_pid' => $bmpa[0]['UslugaTest_pid'],
				'UslugaTest_rid' => $bmpa[0]['UslugaTest_rid'],
				'UslugaTest_setDT' => $bmpa[0]['UslugaTest_setDT'],
				'UslugaTest_disDT' => $bmpa[0]['UslugaTest_disDT'],
				'Lpu_id' => $bmpa[0]['Lpu_id'],
				'Server_id' => $bmpa[0]['Server_id'],
				'Usluga_id' => $bmpa[0]['Usluga_id'],
				'PayType_id' => $bmpa[0]['PayType_id'],
				'UslugaPlace_id' => $bmpa[0]['UslugaPlace_id'],
				'UslugaTest_ResultUnit' => $bmpa[0]['UslugaTest_ResultUnit'],
				'UslugaTest_ResultApproved' => 2,
				'UslugaTest_ResultAppDate' => $this->dbmodel->getCurrentDT(),
				'UslugaTest_ResultCancelReason' => $bmpa[0]['UslugaTest_ResultCancelReason'],
				'Unit_id' => $bmpa[0]['Unit_id'],
				'UslugaTest_Kolvo' => $bmpa[0]['UslugaTest_Kolvo'],
				'UslugaTest_Result' => $bmpa[0]['UslugaTest_Result'],
				'EvnLabSample_id' => $bmpa[0]['EvnLabSample_id'],
				'EvnLabRequest_id' => $bmpa[0]['EvnLabRequest_id'],
				'UslugaTest_CheckDT' => $bmpa[0]['UslugaTest_CheckDT'],
				'UslugaTest_ResultLower' => $bmpa[0]['UslugaTest_ResultLower'],
				'UslugaTest_ResultUpper' => $bmpa[0]['UslugaTest_ResultUpper'],
				'UslugaTest_ResultValue' => $bmpa[0]['UslugaTest_ResultValue'],
				'UslugaTest_Comment' => $bmpa[0]['UslugaTest_Comment'],
				'pmUser_id' => $data['pmUser_id']
			];
			$this->dbmodel->execCommonSP('p_UslugaTest_upd', $params);
		}

		$response['success'] = true;
		$this->ReturnData($response);
		return true;
	}

	public function unapproveResult() {
		$data = $this->ProcessInputData('unapproveResult', true);
		if ($data === false) return false;

		$antibioticList = json_decode($data['AntibioticList'], true);

		foreach ($antibioticList as $antibiotic) {
			$bmpa = $this->dbmodel->getMicroAntibioticList([
				'BactMicroProbeAntibiotic_id' => $antibiotic['BactMicroProbeAntibiotic_id']
			]);

			$params = [
				'UslugaTest_id' => $bmpa[0]['UslugaTest_id'],
				'UslugaTest_pid' => $bmpa[0]['UslugaTest_pid'],
				'UslugaTest_rid' => $bmpa[0]['UslugaTest_rid'],
				'UslugaTest_setDT' => $bmpa[0]['UslugaTest_setDT'],
				'UslugaTest_disDT' => $bmpa[0]['UslugaTest_disDT'],
				'Lpu_id' => $bmpa[0]['Lpu_id'],
				'Server_id' => $bmpa[0]['Server_id'],
				'Usluga_id' => $bmpa[0]['Usluga_id'],
				'PayType_id' => $bmpa[0]['PayType_id'],
				'UslugaPlace_id' => $bmpa[0]['UslugaPlace_id'],
				'UslugaTest_ResultUnit' => $bmpa[0]['UslugaTest_ResultUnit'],
				'UslugaTest_ResultApproved' => 1,
				'UslugaTest_ResultAppDate' => $this->dbmodel->getCurrentDT(),
				'UslugaTest_ResultCancelReason' => $bmpa[0]['UslugaTest_ResultCancelReason'],
				'Unit_id' => $bmpa[0]['Unit_id'],
				'UslugaTest_Kolvo' => $bmpa[0]['UslugaTest_Kolvo'],
				'UslugaTest_Result' => $bmpa[0]['UslugaTest_Result'],
				'EvnLabSample_id' => $bmpa[0]['EvnLabSample_id'],
				'EvnLabRequest_id' => $bmpa[0]['EvnLabRequest_id'],
				'UslugaTest_CheckDT' => $bmpa[0]['UslugaTest_CheckDT'],
				'UslugaTest_ResultLower' => $bmpa[0]['UslugaTest_ResultLower'],
				'UslugaTest_ResultUpper' => $bmpa[0]['UslugaTest_ResultUpper'],
				'UslugaTest_ResultValue' => $bmpa[0]['UslugaTest_ResultValue'],
				'UslugaTest_Comment' => $bmpa[0]['UslugaTest_Comment'],
				'pmUser_id' => $data['pmUser_id']
			];
			$this->dbmodel->execCommonSP('p_UslugaTest_upd', $params);
		}

		$response['success'] = true;
		$this->ReturnData($response);
		return true;
	}
}
