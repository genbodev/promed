<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * RlsDrug - контроллер API для работы с лекарствами
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			API
 * @access			public
 * @copyright		Copyright (c) 2018 Swan Ltd.
 * @author			Maksim Sysolin
 */

require(APPPATH.'libraries/SwREST_Controller.php');

class RlsDrug extends SwREST_Controller
{
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('RlsDrug_model', 'dbmodel');
	}

	protected  $inputRules = array(
		'mGetDrugComplexMnnList' => array(
			array('default' => 0, 'field' => 'start', 'label' => 'Номер стартовой записи', 'rules' => '', 'type' => 'int'),
			array('default' => 100, 'field' => 'limit', 'label' => 'Количество записей', 'rules' => '', 'type' => 'id'),
			array('field' => 'DrugComplexMnn_id', 'label' => 'Идентификатор МНН', 'rules' => '', 'type' => 'id'),
			array('field' => 'Tradenames_id', 'label' => 'Идентификатор торгового наименования', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения', 'rules' => '', 'type' => 'id'),
			array('field' => 'query', 'label' => 'Строка поиска медикамента по МНН', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'isFromDocumentUcOst', 'label' => 'Только на остатках отделения', 'rules' => 'trim', 'type' => 'checkbox'),
			array('field' => 'hasDrugComplexMnnCode', 'label' => 'Имеет код комплексного МНН', 'rules' => 'trim', 'type' => 'checkbox'),
			array('field' => 'needFas', 'label' => 'Нужна ли фасовка', 'rules' => 'trim', 'type' => 'checkbox'),
		),
		'loadDrugList' => array(
			array('field' => 'Drug_id', 'label' => 'Идентификатор медикамента', 'rules' => '', 'type' => 'id'),
			array('field' => 'DrugMnn_id', 'label' => 'Идентификатор МНН', 'rules' => '', 'type' => 'id'),
			array('field' => 'DrugComplexMnn_id', 'label' => 'Идентификатор комплексного МНН', 'rules' => '', 'type' => 'id'),
			array('field' => 'query', 'label' => 'Наименование медикамента', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'Drug_Ean', 'label' => 'Код ЕАН', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'onlyMnn', 'label' => 'Только по МНН', 'rules' => 'trim', 'type' => 'checkbox', 'default' => true),
			array('field' => 'findByLatName', 'label' => 'Поиск по LatName', 'rules' => 'trim', 'type' => 'checkbox', 'default' => false)
		),
	);

	/**
	 *	Получение списка комплексных МНН
	 */
	function mGetDrugComplexMnnList_get() {

		$data = $this->ProcessInputData('mGetDrugComplexMnnList', false, true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadDrugComplexMnnList($data);
		$this->response(array('error_code' => 0, 'data' => $response));
	}

	/**
	 * Получение справочника торговых наименований медикаментов
	 *
	 * @desсription
	 * {
		"input_params": {
			"DrugComplexMnn_id": "Идентификатор комплексного МНН",
			"DrugMnn_id": "Идентификатор МНН",
			"Drug_Ean": "Код ЕАН",
			"Drug_id": "Идентификатор медикамента",
			"findByLatName": "Поиск по LatName",
			"onlyMnn": "Только по МНН",
			"query": "Наименование медикамента"
		},
		"example": {
			"error_code": 0,
			"data": [
				{
					"Drug_id": "17444",
					"Drug_Code": "17444",
					"Drug_Name": "Валокордин®, капли для приема внутрь, №1, 20 мл (1), фл.-кап. темн. стекл., в пач. картон., EAN: 4030031898303, РУ П N012893/01 с 11.01.2010 Рег.: Krewel Meuselbach GmbH(Германия), Пр.: Krewel Meuselbach GmbH (Германия)",
					"DrugMnn_id": null,
					"DrugComplexMnn_id": "524845"
				},
				{
					"Drug_id": "17445",
					"Drug_Code": "17445",
					"Drug_Name": "Валокордин®, капли для приема внутрь, №1, 50 мл (1), фл.-кап. темн. стекл., в пач. картон., EAN: 4030031898310, РУ П N012893/01 с 11.01.2010 Рег.: Krewel Meuselbach GmbH(Германия), Пр.: Krewel Meuselbach GmbH (Германия)",
					"DrugMnn_id": null,
					"DrugComplexMnn_id": "524847"
				},
				{
					"Drug_id": "280782",
					"Drug_Code": "280782",
					"Drug_Name": "Валокордин®-Доксиламин, капли для приема внутрь, 25 мг/мл, №1, 20 мл (1), фл.-кап. темн. стекл., в пач. картон., EAN: 4030031898341, РУ ЛП-000013 с 15.10.2010, перерег. 16.10.2015 Рег.: Krewel Meuselbach GmbH(Германия), Пр.: Krewel Meuselbach GmbH (Германия)",
					"DrugMnn_id": null,
					"DrugComplexMnn_id": "594281"
				},
				{
					"Drug_id": "272884",
					"Drug_Code": "272884",
					"Drug_Name": "Валокордин®-Доксиламин, капли для приема внутрь, 25 мг/мл, №1, 50 мл (1), фл.-кап. темн. стекл., в пач. картон., РУ ЛП-000013 с 15.10.2010, перерег. 16.10.2015 Рег.: Krewel Meuselbach GmbH(Германия), Пр.: Krewel Meuselbach GmbH (Германия)",
					"DrugMnn_id": null,
					"DrugComplexMnn_id": "600768"
				}
			]
		}
	 */
	function mLoadDrugList_get(){
		$data = $this->ProcessInputData('loadDrugList', null, true);
		if($data === false) $this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);

		$response = $this->dbmodel->searchFullDrugList($data);

		$this->response(array('error_code' => 0, 'data' => $response));
	}
}

