<?php defined('BASEPATH') or die ('No direct script access allowed');

/**
 * BleedingCard - контроллер для работы с картами наблюдений для оценки кровотечений
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package          Stac
 * @access           public
 * @copyright        Copyright (c) 2019 Swan Ltd.
 * @author           Ivan Drachyov (i.drachev@swan-it.ru)
 * @version          07.12.2019
 *
 * @property BleedingCard_model dbmodel
 *
 */
class BleedingCard extends swController {
	public $inputRules = [
		'deleteBleedingCard' => [
			[ 'field' => 'BleedingCard_id', 'label' => 'Идентификатор карты', 'rules' => 'required', 'type' => 'id' ]
		],
		'saveBleedingCard' => [
			[ 'field' => 'BleedingCard_id', 'label' => 'Идентификатор карты', 'rules' => '', 'type' => 'id' ],
			[ 'field' => 'EvnSection_id', 'label' => 'Идентификатор движения', 'rules' => 'required', 'type' => 'id' ],
			[ 'field' => 'BleedingCardConditionData', 'label' => 'Оценка состояния', 'rules' => 'required', 'type' => 'json_array', 'assoc' => true ],
			[ 'field' => 'BleedingCardDrugData', 'label' => 'Лекарственные средства', 'rules' => 'required', 'type' => 'json_array', 'assoc' => true ],
			[ 'field' => 'BleedingCardSolutionData', 'label' => 'Растворы', 'rules' => 'required', 'type' => 'json_array', 'assoc' => true ],
		],
		'loadBleedingCardEditForm' => [
			[ 'field' => 'BleedingCard_id', 'label' => 'Идентификатор карты', 'rules' => 'required', 'type' => 'id' ]
		],
		'loadBleedingCardSolutionGrid' => [
			[ 'field' => 'BleedingCard_id', 'label' => 'Идентификатор карты', 'rules' => 'required', 'type' => 'id' ]
		],
		'loadBleedingCardConditionGrid' => [
			[ 'field' => 'BleedingCard_id', 'label' => 'Идентификатор карты', 'rules' => 'required', 'type' => 'id' ]
		],
		'loadBleedingCardDrugGrid' => [
			[ 'field' => 'BleedingCard_id', 'label' => 'Идентификатор карты', 'rules' => 'required', 'type' => 'id' ]
		],
	];

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('BleedingCard_model', 'dbmodel');
	}

	/**
	 * Получение данных для формы редактирования карты наблюдений за кровотечением
	 * Входящие данные: $_POST['BleedingCard_id']
	 * На выходе: JSON-строка
	 * @return bool
	 */
	public function loadBleedingCardEditForm() {
		$data = $this->ProcessInputData('loadBleedingCardEditForm', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadBleedingCardEditForm($data);
		$this->ProcessModelList($response, true, true)->formatDatetimeFields()->ReturnData();

		return true;
	}

	/**
	 * Получение списка растворов
	 * Входящие данные: $_POST['BleedingCard_id']
	 * На выходе: JSON-строка
	 */
	public function loadBleedingCardSolutionGrid() {
		$data = $this->ProcessInputData('loadBleedingCardSolutionGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadBleedingCardSolutionGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Получение списка оценок состояний
	 * Входящие данные: $_POST['BleedingCard_id']
	 * На выходе: JSON-строка
	 */
	public function loadBleedingCardConditionGrid() {
		$data = $this->ProcessInputData('loadBleedingCardConditionGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadBleedingCardConditionGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Получение списка оценок состояний
	 * Входящие данные: $_POST['BleedingCard_id']
	 * На выходе: JSON-строка
	 */
	public function loadBleedingCardDrugGrid() {
		$data = $this->ProcessInputData('loadBleedingCardDrugGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadBleedingCardDrugGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 *  Удаление карты
	 *  Входящие данные: $_POST['BleedingCard_id']
	 *  На выходе: JSON-строка
	 */
	public function deleteBleedingCard() {
		$data = $this->ProcessInputData('deleteBleedingCard', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->deleteBleedingCard($data);
		$this->ProcessModelSave($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Сохранение карты
	 */
	public function saveBleedingCard() {
		$data = $this->ProcessInputData('saveBleedingCard', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveBleedingCard($data);
		$this->ProcessModelSave($response, true, true)->ReturnData();

		return true;
	}
}