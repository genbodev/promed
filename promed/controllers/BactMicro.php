<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
* BactMicro
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* @package      common
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @author       Qusijue
* @version      Сентябрь 2019

 * @property BactMicro_model $dbmodel
*/
class BactMicro extends swController {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
		
		$dbName = 'default';
		$modelName = 'BactMicro_model';
		if ($this->usePostgreLis) {
			$dbName = 'lis';
			$this->usePostgre = true;
		}
		$this->db = $this->load->database($dbName, true);
		$this->load->model($modelName, 'dbmodel');

		$this->inputRules = [
			'getMicroList' => [
				[
					'field' => 'BactMicro_Level',
					'label' => 'BactMicro_Level',
					'rules' => '',
					'type' => 'int'
				], [
					'field' => 'MedService_id',
					'label' => 'MedService_id',
					'rules' => '',
					'type' => 'string'
				], [
					'field' => 'BactGramColor_Code',
					'label' => 'BactGramColor_Code',
					'rules' => '',
					'type' => 'string'
				], [
					'field' => 'BactMicro_Name',
					'label' => 'BactMicro_Name',
					'rules' => '',
					'type' => 'string'
				], [
					'field' => 'mode',
					'label' => 'mode',
					'rules' => '',
					'type' => 'string'
				], [
					'field' => 'target',
					'label' => 'target',
					'rules' => '',
					'type' => 'string'
				]
			], 'insertMicroToLab' => [
				[
					'field' => 'MicroList',
					'label' => 'MicroList',
					'rules' => '',
					'type' => 'string'
				], [
					'field' => 'MedService_id',
					'label' => 'MedService_id',
					'rules' => '',
					'type' => 'string'
				], [
					'field' => 'target',
					'label' => 'target',
					'rules' => '',
					'type' => 'string'
				]
			], 'deleteMicroFromLab' => [
				[
					'field' => 'MicroList',
					'label' => 'MicroList',
					'rules' => '',
					'type' => 'string'
				], [
					'field' => 'MedService_id',
					'label' => 'MedService_id',
					'rules' => '',
					'type' => 'string'
				], [
					'field' => 'target',
					'label' => 'target',
					'rules' => '',
					'type' => 'string'
				]
			]
		];
	}

	/**
	 * Возвращает список грибов в виде вложенного дерева
	 * @return string
	 */
	public function getMushroomList() {
		$data = $this->ProcessInputData('getMicroList', true);
		if ($data === false) return false;

		$data['target'] = 'Mushroom';
		$this->getMicroList($data);
	}

	/**
	 * Возвращает список бактерий в виде вложенного дерева
	 * @return string
	 */
	public function getMicroList() {
		$data = $this->ProcessInputData('getMicroList', true);
		if ($data === false) return false;

		$bactList = $this->dbmodel->getMicroList($data);
		$labMicroList = $this->getLabMicroList($data);
		$lvl6 = []; $lvl5 = []; $lvl4 = []; $lvl3 = []; $lvl2 = []; $lvl1 = [];

		$treeData = [];
		foreach ($bactList as $bact) {
			$name = !empty($bact['BactMicro_Name']) ? trim($bact['BactMicro_Name']).' ' : '';
			$synonym = !empty($bact['BactMicro_Synonym']) ? '['.trim($bact['BactMicro_Synonym']).'] ' : '';
			$gram = !empty($bact['BactGramColor_Name']) ? trim($bact['BactGramColor_Name']) : '';

			$micro = [
				'BactMicro_id' => $bact['BactMicro_id'],
				'text' => toUTF($name.$synonym.$gram),
				'id' => 'BactMicro_' . $bact['BactMicro_Level'] .'_' . $bact['BactMicro_id'],
				'checked' => false,
				'level' => $bact['BactMicro_Level'],
				'leaf' => intval($bact['isLeaf']),
				'children' => []
			];
			$currentLvl = 'lvl' . $bact['BactMicro_Level'];
			$checkedLvl = 'lvl' . (intval($bact['BactMicro_Level']) + 1);

			$bactID = $bact['BactMicro_id'];
			$micro['children'] = isset($$checkedLvl[$bactID]) ? $$checkedLvl[$bactID] : [];

			if ($this->checkMicroInLab($data['mode'], $micro, $labMicroList)) continue;
			if (!$this->compareName($micro, $data['BactMicro_Name'])) continue;

			if ($bact['BactMicro_pid'] != "") $$currentLvl[$bact['BactMicro_pid']][] = $micro;
			else $$currentLvl[] = $micro;
		}
		
		$this->output
			->set_content_type('application/json')
			->set_output(json_encode($lvl1));
	}

	/**
	 * Добавление микроорганизмов в лабораторию
	 */
	public function insertMicroToLab() {
		$data = $this->ProcessInputData('insertMicroToLab', true);
		if ($data === false) return false;

		$microList = explode(',', $data['MicroList']);
		$insertedList = [];

		$this->db->trans_begin();
		try {
			foreach ($microList as $micro) {
				$parentList = $this->getElementParentList($micro);
				$insertedList = array_merge($insertedList, $parentList);
			}
			$insertedList = array_unique($insertedList);
	
			foreach ($insertedList as $micro) {
				$params = [
					'BactMicro_id' => $micro,
					'MedService_id' => $data['MedService_id'],
					'pmUser_id' => $data['session']['pmuser_id']
				];
				$this->dbmodel->execCommonSP('p_BactMicroLab_ins', $params);
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
				'mode' => $data['target']
			]));
	}

	/**
	 * Удаление микроорганизмов из лаборатории
	 */
	public function deleteMicroFromLab() {
		$data = $this->ProcessInputData('deleteMicroFromLab', true);
		if ($data === false) return false;

		try {
			$microList = $this->dbmodel->getLabMicroList([
				'BactMicro_id' => $data['MicroList'],
				'MedService_id' => $data['MedService_id']
			]);
	
			foreach ($microList as $micro) {
				$params = [
					'BactMicroLab_id' => $micro['BactMicroLab_id'],
					'pmUser_id' => $data['session']['pmuser_id']
				];
				$this->dbmodel->execCommonSP('p_BactMicroLab_del', $params);
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
				'mode' => $data['target']
			]));
	}

	private function getElementParentList($elementId) {
		$bactList = $this->dbmodel->getElementParentList(['BactMicro_id' => $elementId]);
		foreach ($bactList as $key => $value) {
			$bactList[$key] = $value['BactMicro_id'];
		}
		return $bactList;
	}

	/**
	 * Возвращает список микроорганизмов в лаборатории
	 */
	private function getLabMicroList($params) {
		$params['mode'] = 'lab';
		$bactList = $this->dbmodel->getMicroList($params);
		foreach ($bactList as $key => $value) {
			$bactList[$key] = $value['BactMicro_id'];
		}
		return $bactList;
	}

	/**
	 * Проверка на наличие микроорганизма в лаборатории
	 */
	private function checkMicroInLab($mode, $micro, $labMicroList) {
		if ($mode == 'lab') return false;
		$childEmpty = empty($micro['children']);
		$inLab = in_array($micro['BactMicro_id'], $labMicroList);
		if ($childEmpty && $inLab) return true;
		else return false;
	}

	/**
	 * Поиск микроорганизма по основному/альтернативному имени
	 */
	private function compareName($micro, $searchedName) {
		if (empty($searchedName)) return true;
		$childEmpty = empty($micro['children']);
		$isEqualName = strpos(strtolower($micro['text']), strtolower($searchedName)) !== false;
		if (!$childEmpty || $isEqualName) return true;
		else return false;
	}
}
