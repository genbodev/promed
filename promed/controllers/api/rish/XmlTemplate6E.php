<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Api - контроллер API для работы с документами
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			API
 * @access			public
 * @copyright		Copyright (c) 2016 Swan Ltd.
 * @author			Dmitriy Vlasenko
 * @version			12.2016
 */

require(APPPATH.'libraries/SwREST_Controller.php');

class XmlTemplate6E extends SwREST_Controller {

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('XmlTemplate6E_model', 'dbmodel');
		$this->inputRules = array(
			'mgetXmlTemplateForEvnXml' => array(
				array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'Evn_id', 'label' => 'Идентификатор события', 'rules' => '', 'type' => 'id'),
				array('field' => 'EvnXml_id', 'label' => 'Идентификатор документа', 'rules' => '', 'type' => 'id'),
				array('field' => 'XmlTemplate_id', 'label' => 'Идентфикатор шаблона', 'rules' => '', 'type' => 'id'),
			),
			'mLoadXmlTemplateList' => array(
				array('field' => 'query', 'label' => 'Запрос', 'rules' => '', 'type' => 'string'),
				array('field' => 'mode', 'label' => 'Режим', 'rules' => 'required', 'type' => 'string'),
				array('field' => 'XmlType_id', 'label' => 'Идентификатор типа документа', 'rules' => '', 'type' => 'id'),
				array('field' => 'EvnClass_id', 'label' => 'Идентификатор класса события', 'rules' => '', 'type' => 'id'),
				array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения', 'rules' => '', 'type' => 'id'),
				array('field' => 'MedPersonal_id', 'label' => 'Идентификатор врача', 'rules' => '', 'type' => 'id'),
				array('field' => 'MedStaffFact_id', 'label' => 'Идентификатор рабочего места врача', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'MedService_id', 'label' => 'Идентификатор службы', 'rules' => '', 'type' => 'id'),
			),
			'mSaveEvnXml' => array(
				array('field' => 'EvnXml_id', 'label' => 'Идентификатор шаблона (протокол, в который сохраняется)', 'rules' => '', 'type' => 'id'),
				array('field' => 'Evn_id', 'label' => 'Идентификатор события', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'XmlType_id', 'label' => 'Идентификатор типа документа', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'XmlTemplate_id', 'label' => 'Идентификатор шаблона (шаблон, откуда берется)', 'rules' => 'required', 'type' => 'id'),
			),
			'mLoadXmlTemplateTree' => array(
				array('field' => 'node', 'label' => 'Тип выборки дерева (own - свои | root - все | shared - общие | base - базовые)', 'rules' => 'required', 'type' => 'string'),
				array('field' => 'query', 'label' => 'Строка запроса', 'rules' => '', 'type' => 'string'),
				array('field' => 'id', 'label' => 'Идентификатор папки', 'rules' => '', 'type' => 'string'),
				array('field' => 'XmlTemplateCat_id', 'label' => 'Идентификатор папки', 'rules' => '', 'type' => 'id'),
				array('field' => 'XmlType_id', 'label' => 'Идентификатор типа документа', 'rules' => '', 'type' => 'id'),
				array('field' => 'EvnClass_id', 'label' => 'Идентификатор класса события', 'rules' => '', 'type' => 'id'),
				array('field' => 'MedStaffFact_id', 'label' => 'Идентификатор рабочего места врача', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'LpuSection_sid', 'label' => 'Идентификатор отделения', 'rules' => '', 'type' => 'id'),
			),
			'mLoadPMUserForShareList' => array(
				array('field' => 'XmlTemplate_id', 'label' => 'Идентификатор шаблона', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'query', 'label' => 'Запрос', 'rules' => 'trim', 'type' => 'string'),
			),
			'mShareXmlTemplate' => array(
				array('field' => 'XmlTemplate_id', 'label' => 'Идентификатор шаблона', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'shareTo', 'label' => 'JSON-строка', 'rules' => 'required|trim', 'type' => 'string'),
			),
			'mSearchXmlTemplate' => array(
				array('field' => 'XmlType_id', 'label' => 'Идентификатор типа документа', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'EvnClass_id', 'label' => 'Идентификатор класса события', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'query', 'label' => 'Слово для поиска', 'rules' => 'required', 'type' => 'string'),
				array('field' => 'search', 'label' => 'Место для поиска(шаблоны или папки)', 'rules' => '', 'type' => 'string'),
			)
		);
	}



	/**
	 * Получение шаблона для отображения в документе
	 */
	function mgetXmlTemplateForEvnXml_get() {

		$data = $this->ProcessInputData('mgetXmlTemplateForEvnXml', null, true);
		if ($data === false) return false;

		$resp = $this->dbmodel->getXmlTemplateForEvnXml($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array('error_code' => 0,'data' => $resp));
	}

	/**
	 * Получение списка шаблонов
	 *
	 * @desсription
	 * {
	 * 		"output_params": {
	 * 			"error_code": "Код ошибки",
				"XmlTemplate_id": "Идентификатор шаблона",
				"XmlTemplate_Caption": "Наименование шаблона",
				"XmlTemplate_Descr": "Описание шаблона",
				"XmlTemplateCat_id": "Идентификатор",
				"XmlTemplate_IsFavorite": "Признак избранного шаблона",
				"Author_id": "Идентификатор пользователя создавшего шаблон",
				"Author_Fin": "ФИО пользователя создавшего шаблон",
				"XmlType_id": "Тип шаблона",
				"XmlType_Name": "Название типа шаблона",
				"EvnClass_id": "Идентификатор класса события шаблона",
				"EvnClass_SysNick": "Системное имя класса события шаблона",
				"EvnClass_Name": "Название класса события шаблона",
				"XmlTemplateScope_id": "Идентификатор области видимости",
				"XmlTemplateScope_Name": "Область видимости",
				"XmlTemplateShared_id": "Идентификатор журнала передач шаблона",
				"XmlTemplateShared_IsReaded": "Признак \"Прочитано\""
	 * 		},
	 * 		"example": {
	 * 			"error_code": 0,
	 * 			"data": {
					"XmlTemplate_id": "200569",
					"XmlTemplate_Caption": "ТестАПИ2",
					"XmlTemplate_Descr": null,
					"XmlTemplateCat_id": null,
					"XmlTemplate_IsFavorite": 2,
					"Author_id": "282679510085",
					"Author_Fin": "ТЕСТ А. М.",
					"XmlType_id": "3",
					"XmlType_Name": "Протокол осмотра",
					"EvnClass_id": "11",
					"EvnClass_SysNick": "EvnVizitPL",
					"EvnClass_Name": "Посещение поликлиники",
					"XmlTemplateScope_id": "5",
					"XmlTemplateScope_Name": "Автор",
					"XmlTemplateShared_id": null,
					"XmlTemplateShared_IsReaded": null
	 * 			}
	 * 		}
	 * }
	 */
	function mLoadXmlTemplateList_get() {

		$data = $this->ProcessInputData('mLoadXmlTemplateList', null, true);
		if ($data === false) return false;

		$data['MedPersonal_id'] = $this->dbmodel->getFirstResultFromQuery("
			select top 1 msf.MedPersonal_id from v_MedStaffFact msf (nolock) where msf.MedStaffFact_id = :MedStaffFact_id
		", $data);

		if (empty($data['MedPersonal_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$resp = $this->dbmodel->loadXmlTemplateList($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array('error_code' => 0,'data' => $resp));
	}


	/**
	 * Установка шаблона протокола
	 *
	 * @desсription
	 * {
	 * 		"output_params": {
	 * 			"error_code": "Код ошибки",
				"EvnXml_id": "Идентификатор заменяемого протокола",
				"XmlTemplate_id": "Идентификатор подставляемого шаблона"
	 * 		},
	 * 		"example": {
	 * 			"error_code": 0,
	 * 			"data": {
					"EvnXml_id": "27023",
					"XmlTemplate_id": "200569"
	 * 			}
	 * 		}
	 * }
	 */
	function mSaveEvnXml_post() {

		$data = $this->ProcessInputData('mSaveEvnXml', null, true);
		if ($data === false) return false;

		$this->load->library('swXmlTemplate');

		// чтобы получить правильный html шаблон
		if (!empty($data['EvnXml_id'])) {
			$stored['EvnXml_id'] = $data['EvnXml_id'];
			unset($data['EvnXml_id']);
		}

		$res = $this->dbmodel->getXmlTemplateForEvnXml($data);

		if (!empty($res[0]['EvnXml_Name']) && empty($data['EvnXml_Name']) && empty($data['EvnXml_id'])) {
			$data['EvnXml_Name'] = $res[0]['EvnXml_Name'];
		}
		// данные
		$data['EvnXml_Data'] = json_encode($res[0]['xmlData']);

		// шаблон
		$data['XmlTemplate_HtmlTemplate'] = $res[0]['template'];

		// получаем дата-лэйблы
		$data['EvnXml_DataLabel'] = (!empty($res[0]['xmlDataLabel']) ? json_encode($res[0]['xmlDataLabel']) : null);

		$data['EvnXml_DataSettings'] = (!empty($res[0]['xmlDataSettings']) ? json_encode($res[0]['xmlDataSettings']) : null);

		if (!empty($stored['EvnXml_id'])) $data['EvnXml_id'] = $stored['EvnXml_id'];


		$this->load->model('EvnXml6E_model', 'EvnXml6E_model');
		//echo '<pre>',print_r($data['EvnXml_Name']),'</pre>'; die();
		$resp = $this->EvnXml6E_model->saveEvnXml($data);

		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		if (!empty($resp[0])) $resp = $resp[0];
		$this->response(array('error_code' => 0,'data' => $resp));
	}

	/**
	 * Получение уровня в дереве шаблонов
	 *
	 * @desсription
	 * {
	 * 		"output_params": {
	 * 			"error_code": "Код ошибки",
	 * 			"Объект Шаблон": "------------------------------------------",
				"XmlTemplate_id": "Идентификатор шаблона",
				"XmlTemplate_Caption": "Название шаблона",
				"XmlTemplate_Descr": "Описание шаблона",
				"XmlTemplate_IsFavorite": "Шаблон избранный",
				"Author_id": "Идентификатор автора",
				"Author_Fin": "ФИО автора",
				"XmlType_id": "ФИО автора",
	 			"XmlType_Name": "ФИО автора",
	 			"EvnClass_id": "ФИО автора",
				"EvnClass_SysNick": "ФИО автора",
	 			"EvnClass_Name": "ФИО автора",
				"XmlTemplateScope_id": "ФИО автора",
				"XmlTemplateScope_Name": "ФИО автора",
				"XmlTemplateShared_id": "ФИО автора",
				"XmlTemplateShared_IsReaded": "ФИО автора",
				"Объект Каталог": "------------------------------------------",
				"XmlTemplateCat_id": "Идентификатор каталога-потомка",
				"XmlTemplateCat_pid": "Идентификатор каталога-родителя",
				"XmlTemplateCat_Name": "Имя каталога",
				"childrenFoldersCount": "Количество подпапок",
				"childrenTemplatesCount": "Количество шаблонов",
				"Общие поля": "------------------------------------------",
				"id": "Идентификатор в дереве",
				"nodeType": "Тип объекта - (FolderNode | SharedNode | TemplateNode)",
				"node": "Тип выборки дерева"
	 * 		},
	 * 		"example": {
	 * 			"error_code": 0,
	 * 			"data": [{
						"XmlTemplateCat_id": "24638",
						"XmlTemplateCat_pid": null,
						"XmlTemplateCat_Name": "Test Shutoff",
						"childrenFoldersCount": 0,
						"childrenTemplatesCount": 1,
						"id": "folder-24638",
						"nodeType": "FolderNode",
						"node": "own"
		 * 			},
	 * 				{
						"XmlTemplate_id": "200568",
						"XmlTemplate_Caption": "ТестАПИ1",
						"XmlTemplate_Descr": null,
						"XmlTemplateCat_id": null,
						"XmlTemplate_IsFavorite": 2,
						"Author_id": "282679510085",
						"Author_Fin": "ТЕСТ А. М.",
						"XmlType_id": "3",
						"XmlType_Name": "Протокол осмотра",
						"EvnClass_id": "11",
						"EvnClass_SysNick": "EvnVizitPL",
						"EvnClass_Name": "Посещение поликлиники",
						"XmlTemplateScope_id": "5",
						"XmlTemplateScope_Name": "Автор",
						"XmlTemplateShared_id": null,
						"XmlTemplateShared_IsReaded": null,
						"id": "template-200568",
						"nodeType": "TemplateNode",
						"node": "own"
						}
	 * 			]
	 * 		}
	 * }
	 */
	function mLoadXmlTemplateTree_get() {

		$data = $this->ProcessInputData('mLoadXmlTemplateTree', null, true);
		if ($data === false) return false;

		$msf_data = $this->dbmodel->getFirstRowFromQuery("
			select top 1
			 msf.MedPersonal_id,
			 msf.LpuSection_id
			 from v_MedStaffFact msf (nolock)
			 where msf.MedStaffFact_id = :MedStaffFact_id
		", $data);

		if (empty($msf_data['MedPersonal_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$data= array_merge($data, $msf_data);

		$resp = $this->dbmodel->loadXmlTemplateTree($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array('error_code' => 0,'data' => $resp));
	}

	/**
	 * Получение пользователей для расшаривания шаблона
	 *
	 * @desсription
	 * {
	 * 		"output_params": {
	 * 			"error_code": "Код ошибки",
				"pmUser_id": "Идентификатор пользователя",
				"pmUser_Login": "Логин пользователя",
				"pmUser_Name": "Имя пользователя",
				"Lpu_id": "Идентификатор ЛПУ",
				"Lpu_Nick": "Наименование ЛПУ"
	 * 		},
	 * 		"example": {
	 * 			"error_code": 0,
	 * 			"data": {
					"pmUser_id": "229285618825",
					"pmUser_Login": "uccall",
					"pmUser_Name": "1 2",
					"Lpu_id": "13002533",
					"Lpu_Nick": "ТЕСТ 3"
	 * 			}
	 * 		}
	 * }
	 */
	function mLoadPMUserForShareList_get() {

		$data = $this->ProcessInputData('mLoadPMUserForShareList', null, true);
		if ($data === false) return false;

		$resp = $this->dbmodel->LoadPMUserForShareList($data);
		if (!empty($resp)) {
			foreach ($resp as &$item) {
				unset($item['compareField']);
				unset($item['id']);
			}
		}

		$this->response(array('error_code' => 0,'data' => $resp));
	}

	/**
	 * Отправка расшареного шаблона выбранному пользователю
	 *
	 * @desсription
	 * {
	 * 		"output_params": {
	 * 			"error_code": "Код ошибки"
	 * 		},
	 * 		"example": {
	 * 			"error_code": 0,
	 * 			"success": true
	 * 		}
	 * }
	 */
	function mShareXmlTemplate_post() {

		$data = $this->ProcessInputData('mShareXmlTemplate', null, true);
		if ($data === false) return false;

		$resp = $this->dbmodel->shareXmlTemplate($data);
		$this->response(array('error_code' => 0,'data' => $resp));
	}
	
	function mSearchXmlTemplate_get() {
		$data = $this->ProcessInputData('mSearchXmlTemplate', null, true);
		if ($data === false) return false;
		
		$resp = $this->dbmodel->mSearchXmlTemplate($data);
		$this->response(array('error_code' => 0,'data' => $resp));
	}
}