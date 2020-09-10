<?php	defined('BASEPATH') or die ('No direct script access allowed');
class Specifics extends swController {
	public $inputRules = array(
		'getSpecificsTree' => array(
			array(
				'field' => 'node',
				'label' => 'Нода дерева',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'object',
				'label' => 'Объект',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'PersonRegister_id',
				'label' => 'Идентификатор записи в регистре беременных',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'EvnSection_id',
				'label' => 'Идентификатор движения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnPS_id',
				'label' => 'Идентификатор КВС',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDiagPLStom_id',
				'label' => 'Идентификатор посещения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnVizitPL_id',
				'label' => 'Идентификатор посещения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnVizit_id',
				'label' => 'Идентификатор посещения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnSection_setDate',
				'label' => 'Дата начала движения',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'EvnSection_disDate',
				'label' => 'Дата окончания движения',
				'rules' => '',
				'type' => 'date'
			),
			array(
				'field' => 'createCategoryMethod',
				'label' => 'Метод для создания категорий',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'deleteCategoryMethod',
				'label' => 'Метод для удаления категорий',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'allowCreateButton',
				'label' => '',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'allowDeleteButton',
				'label' => '',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'Diag_ids',
				'label' => '',
				'rules' => '',
				'type' => 'json_array'
			),
		),
	);

	/**
	 *	Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		// $this->load->model('Specifics_model', 'dbmodel');
	}


	/**
	*  Получение дерева с возможными вариантами по специфике
	*  Входящие данные: -
	*  На выходе: JSON-строка
	*  Используется: форма редактирования КВС
	*/
	function getSpecificsTree() {
		$data = $this->ProcessInputData('getSpecificsTree', true);
		if ($data === false) { return false; }

		$this->load->helper('Options');
		$options = getOptions();

		$this->load->model('Specifica_model');
		$this->load->model('PersonPregnancy_model');
		$this->load->model('MorbusOnkoSpecifics_model');

		$EvnSection_id = !empty($data['EvnSection_id'])?$data['EvnSection_id']:null;
		$PersonRegister_id = !empty($data['PersonRegister_id'])?$data['PersonRegister_id']:null;
		$person_pregnancy_nodes = array();

		$val = array();

		if ($data['node'] == 'specifics_tree_root') {
			$val[] = array(
				'id' => 'born_data',
				'value' => 'born_data',
				'text' => toUTF('Сведения о новорожденном'),
				'leaf' => true
			);

			$person_pregnancy_nodes = $this->PersonPregnancy_model->loadPersonPregnancyTree(array(
				'node' => 'root',
				'PersonRegister_id' => $PersonRegister_id
			));
		} else {
			$BirthSpecStac_id = null;
			if (empty($PersonRegister_id) && !empty($EvnSection_id)) {
				$resp = $this->Specifica_model->getBirthSpecStacId(array('EvnSection_id' => $EvnSection_id));
				if (isset($resp[0]) && !empty($resp[0]['BirthSpecStac_id'])) {
					$BirthSpecStac_id = $resp[0]['BirthSpecStac_id'];
				}
			}

			/*$params = array(
				'node' => $data['node'],
				'object' => $data['object'],
				'Lpu_id' => $data['Lpu_id'],
				'Evn_id' => $EvnSection_id,
				'PersonRegister_id' => $PersonRegister_id,
				'createCategoryMethod' => $data['createCategoryMethod'],
				'deleteCategoryMethod' => $data['deleteCategoryMethod'],
				'allowCreateButton' => $data['allowCreateButton'],
				'allowDeleteButton' => $data['allowDeleteButton'],
				'allowCreateCategories' => array('Anketa','ScreenList','Certificate','Result'),
			);*/
			$params = array(
				'node' => $data['node'],
				'object' => $data['object'],
				'Lpu_id' => $data['Lpu_id'],
				'Evn_id' => $EvnSection_id,
				'PersonRegister_id' => $PersonRegister_id,
				'createCategoryMethod' => $data['createCategoryMethod'],
				'deleteCategoryMethod' => $data['deleteCategoryMethod'],
				'allowCreateButton' => $data['allowCreateButton'],
				'allowDeleteButton' => $data['allowDeleteButton'],
				'allowCategories' => array('Result'),
				'allowCreateCategories' => array('Result'),
			);
			//Если записи регистра беременности нет, но есть исход беременности, то выводится только исход
			if (empty($PersonRegister_id) && !empty($BirthSpecStac_id)) {
				//$params['allowCategories'] = array('Result', 'DeathMother');
				$params['BirthSpecStac_id'] = $BirthSpecStac_id;
			}
			$person_pregnancy_nodes = $this->PersonPregnancy_model->loadPersonPregnancyTree($params);
			
			if ($data['node'] == 'MorbusOnko') {
				$person_pregnancy_nodes = $this->MorbusOnkoSpecifics_model->loadMorbusOnkoTree($params);
				
				if (is_array($data['Diag_ids']) && is_array($person_pregnancy_nodes)) {
					$diag_ids = array();
					foreach($data['Diag_ids'] as $el) {
						if (!isset($diag_ids[$el[0]])) {
							$diag_ids[$el[0]] = array($el[1],$el[2],$el[3]);
						}
					} 
				
					foreach($person_pregnancy_nodes as $k => $el) {
						if (!isset($diag_ids[$el['Diag_id']])) { // нет в списке - удаляем
							unset($person_pregnancy_nodes[$k]);
						} else{
							if($diag_ids[$el['Diag_id']][0] == 1) { // основное - подсвечиваем
								$person_pregnancy_nodes[$k]['text'] = '<b>'.$el['text'].'</b>';
							}
							unset($diag_ids[$el['Diag_id']]);
						}
					}
				}
				
				// имитируем те, которых ещё нет в БД
				foreach($diag_ids as $key => $diag) {
					if (mb_substr($diag[1], 0, 1) == 'C' || mb_substr($diag[1], 0, 2) == 'D0') {
						$person_pregnancy_nodes[] = array(
							'EvnSection_id' => $data['EvnSection_id'],
							'EvnDiagPLSop_id' => $diag[2],
							'Morbus_id' => null,
							'Diag_id' => $key,
							'Diag_Code' => $diag[1],
							'value' => !empty($data['EvnDiagPLStom_id']) ? $data['EvnDiagPLStom_id'] : $data['EvnVizitPL_id'],
							'text' => $diag[0] == 1
								? "<b>Специфика (онкология) {$diag[1]}</b>"
								: "Специфика (онкология) {$diag[1]}",
							'leaf' => true
						);
					}
				}				
			}
		}
		
		foreach($person_pregnancy_nodes as &$node) {
			$node['value'] = ($data['node'] == 'MorbusOnko') ? 'MorbusOnko' : 'PersonPregnancy';
		}

		$val = array_merge($val, $person_pregnancy_nodes);
		
		if ($data['node'] == 'specifics_tree_root') {
			$val[] = array(
				'id' => 'MorbusOnko',
				'value' => 'MorbusOnko',
				'object' => 'MorbusOnko',
				'text' => 'Специфика (онкология)',
				'leaf' => false,
			);
		}

		$this->ReturnData($val);

		return true;
	}


	/**
	*  Получение дерева с возможными вариантами по специфике
	*  Входящие данные: -
	*  На выходе: JSON-строка
	*/
	function getStomSpecificsTree() {
		$data = $this->ProcessInputData('getSpecificsTree', true);
		if ($data === false) { return false; }
		
		$this->load->model('MorbusOnkoSpecifics_model');

		$val = array();

		if ($data['node'] == 'specifics_tree_root') {
			
			$mo_list = $this->MorbusOnkoSpecifics_model->loadMorbusOnkoTree(array(
				'EvnDiagPLStom_id' => !empty($data['EvnDiagPLStom_id'])?$data['EvnDiagPLStom_id']:null,
				'EvnVizitPL_id' => !empty($data['EvnVizitPL_id'])?$data['EvnVizitPL_id']:null,
				'EvnVizit_id' => !empty($data['EvnVizit_id'])?$data['EvnVizit_id']:null,
			));
			
			if (is_array($data['Diag_ids']) && is_array($mo_list)) {
				$diag_ids = array();
				foreach($data['Diag_ids'] as $el) {
					if (!isset($diag_ids[$el[0]])) {
						$diag_ids[$el[0]] = array($el[1],$el[2],$el[3]);
					}
				} 
			
				foreach($mo_list as $k => $el) {
					if (!isset($diag_ids[$el['Diag_id']])) { // нет в списке - удаляем
						unset($mo_list[$k]);
					} else{
						if($diag_ids[$el['Diag_id']][0] == 1) { // основное - подсвечиваем
							$mo_list[$k]['text'] = '<b>'.$el['text'].'</b>';
						}
						unset($diag_ids[$el['Diag_id']]);
					}
				}
			}
			$value = !empty($data['EvnDiagPLStom_id']) ? $data['EvnDiagPLStom_id'] : $data['EvnVizitPL_id'];
			if(empty($value) && !empty($data['EvnVizit_id'])){
				$value = $data['EvnVizit_id'];
			}
			// имитируем те, которых ещё нет в БД
			foreach($diag_ids as $key => $diag) {
				if (mb_substr($diag[1], 0, 1) == 'C' || mb_substr($diag[1], 0, 2) == 'D0') {
					$mo_list[] = array(
						'EvnVizitPL_id' => $data['EvnVizitPL_id'],
						'EvnDiagPLStom_id' => $data['EvnDiagPLStom_id'],
						'EvnVizit_id' => $data['EvnVizit_id'],
						'EvnDiagPLStomSop_id' => $diag[2],
						'EvnDiagPLSop_id' => $diag[2],
						'Morbus_id' => null,
						'Diag_id' => $key,
						'Diag_Code' => $diag[1],
						'value' => $value,
						'text' => $diag[0] == 1
							? "<b>Специфика (онкология) {$diag[1]}</b>"
							: "Специфика (онкология) {$diag[1]}",
						'leaf' => true
					);
				}
			}
			
			$mo_list = array_values($mo_list);
			
			$val[] = array(
				'id' => 'MorbusOnko',
				'value' => 'MorbusOnko',
				'expanded' => true,
				'text' => 'Специфика (онкология)',
				'leaf' => false,
				'children' => $mo_list
			);
		}

		$this->ReturnData($val);

		return true;
	}

	/**
	*  Получение дерева с возможными вариантами по специфике (для приемного)
	*  Входящие данные: -
	*  На выходе: JSON-строка
	*/
	function getPriemSpecificsTree() {
		$data = $this->ProcessInputData('getSpecificsTree', true);
		if ($data === false) { return false; }
		
		$this->load->model('MorbusOnkoSpecifics_model');

		$val = array();

		if ($data['node'] == 'specifics_tree_root') {
			
			$mo_list = $this->MorbusOnkoSpecifics_model->loadMorbusOnkoTree(array(
				'EvnPS_id' => !empty($data['EvnPS_id'])?$data['EvnPS_id']:null
			));
			
			$data['EvnSection_id'] = $this->MorbusOnkoSpecifics_model->getPriemEvnSectionId($data);
			
			if (is_array($data['Diag_ids']) && is_array($mo_list)) {
				$diag_ids = array();
				foreach($data['Diag_ids'] as $el) {
					if (!isset($diag_ids[$el[0]])) {
						$diag_ids[$el[0]] = array($el[1],$el[2],$el[3]);
					}
				} 
			
				foreach($mo_list as $k => $el) {
					if (!isset($diag_ids[$el['Diag_id']])) { // нет в списке - удаляем
						unset($mo_list[$k]);
					} else{
						unset($diag_ids[$el['Diag_id']]);
					}
				}
			}
			
			// имитируем те, которых ещё нет в БД
			foreach($diag_ids as $key => $diag) {
				if (mb_substr($diag[1], 0, 1) == 'C' || mb_substr($diag[1], 0, 2) == 'D0') {
					$mo_list[] = array(
						'EvnSection_id' => $data['EvnSection_id'],
						'Morbus_id' => null,
						'Diag_id' => $key,
						'Diag_Code' => $diag[1],
						'value' => $data['EvnSection_id'],
						'text' => "Специфика (онкология) {$diag[1]}",
						'leaf' => true
					);
				}
			}
			
			$mo_list = array_values($mo_list);
			$val = $mo_list;
		}

		$this->ReturnData($val);

		return true;
	}
}
