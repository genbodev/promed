<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * XmlDocument - контроллер API для работы с xml-документами
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			API
 * @access			public
 * @copyright		Copyright (c) 2016 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			10.11.2016
 */

require(APPPATH.'libraries/SwREST_Controller.php');

class XmlDocument extends SwREST_Controller
{
	protected $inputRules = array(
		'loadEvnXmlList' => array(
			array('field' => 'Evn_id', 'label' => 'Идентификатор случая', 'rules' => 'required', 'type' => 'id'),
		),
		'getEvnXml' => array(
			array('field' => 'Evn_id', 'label' => 'Идентификатор случая', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnXml_id', 'label' => 'Идентификатор документа', 'rules' => 'required', 'type' => 'id'),
		),
		'createEvnXml' => array(
			array('field' => 'Evn_id', 'label' => 'Идентификатор случая', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'XmlType_id', 'label' => 'Тип документа', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnXml_Data', 'label' => 'Текст документа в xml', 'rules' => '', 'type' => 'string'),
			array('field' => 'XmlTemplateHtml_HtmlTemplate', 'label' => 'Текст документа в html', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnXml_Data64', 'label' => 'Текст документа в xml', 'rules' => '', 'type' => 'string'),
			array('field' => 'XmlTemplateHtml_HtmlTemplate64', 'label' => 'Текст документа в html', 'rules' => '', 'type' => 'string'),
			array('field' => 'XmlTemplate_id', 'label' => 'Идентификатор шаблона документа', 'rules' => 'required', 'type' => 'id'),
		),
		'updateEvnXml' => array(
			array('field' => 'Evn_id', 'label' => 'Идентификатор случая', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnXml_id', 'label' => 'Идентификатор случая', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'XmlType_id', 'label' => 'Тип документа', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnXml_Data', 'label' => 'Текст документа в xml', 'rules' => '', 'type' => 'string'),
			array('field' => 'XmlTemplateHtml_HtmlTemplate', 'label' => 'Текст документа в html', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnXml_Data64', 'label' => 'Текст документа в xml', 'rules' => '', 'type' => 'string'),
			array('field' => 'XmlTemplateHtml_HtmlTemplate64', 'label' => 'Текст документа в html', 'rules' => '', 'type' => 'string'),
			array('field' => 'XmlTemplate_id', 'label' => 'Идентификатор шаблона документа', 'rules' => '', 'type' => 'id'),
		),
		'loadXmlTemplateList' => array(
			array('field' => 'XmlTemplate_id', 'label' => 'Идентификатор шаблона', 'rules' => '', 'type' => 'id'),
			array('field' => 'XmlTemplateBaseFlag', 'label' => 'Признак для получения только базовых шаблонов', 'rules' => '', 'type' => 'int'),
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnClass_id', 'label' => 'Идентификатор класса события', 'rules' => '', 'type' => 'id'),
			array('field' => 'XmlType_id', 'label' => 'Идентификатор типа документа', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedPersonal_id', 'label' => 'Идентификатор пользователя', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedStaffFact_id', 'label' => 'Идентификатор места работы врача', 'rules' => '', 'type' => 'id'),
		)
	);

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('EvnXmlBase_model', 'dbmodel');
		$this->load->model('XmlTemplateBase_model', 'xtmodel');
	}

	/**
	 * Получение документа
	 */
	function index_get() {
		$data = $this->ProcessInputData('getEvnXml');

		$sp = getSessionParams();
		$data['Lpu_id'] = $sp['Lpu_id'];
		$resp = $this->dbmodel->getEvnXmlForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение данных события
	 */
	function getEvnInfo($data) {
		$object = $this->dbmodel->getFirstResultFromQuery("
			select top 1 EvnClass_SysNick from v_Evn with(nolock) where Evn_id = :Evn_id
		", $data);
		if (!$object) {
			return false;
		}

		return $this->dbmodel->getFirstRowFromQuery("
			select top 1 * from v_{$object} with(nolock) where {$object}_id = :Evn_id
		", $data);
	}

	/**
	 * Получение данных шаблона
	 */
	function getXmlTemplateInfo($data) {
		return $this->dbmodel->getFirstRowFromQuery("
			select top 1 * from v_XmlTemplate with(nolock) where XmlTemplate_id = :XmlTemplate_id
		", $data);
	}

	/**
	 * Сохранение данных в документе
	 * @param array $data
	 */
	function saveXmlData($data) {
		if (empty($data['EvnXml_Data'])) return;

		$data['EvnXml_Data'] = stripcslashes($data['EvnXml_Data']);
		$data['EvnXml_Data'] = preg_replace('/&(?!(quot|amp|pos|lt|gt);)/', '&amp;', $data['EvnXml_Data']);

		libxml_use_internal_errors(true);
		$XmlData = simplexml_load_string($data['EvnXml_Data']);
		foreach (libxml_get_errors() as $error) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Неверный формат в XmlData'
			));
			break;
		}

		$XmlData1 = array();
		foreach($XmlData as $key => $value) {
			$value = $value->asXML();
			$value = str_replace("<{$key}>", '', $value);
			$value = str_replace("<{$key}/>", '', $value);
			$value = str_replace("</{$key}>", '', $value);
			$XmlData1[$key] = htmlentities($value, null, null, false);
		}

		//Сохранение данных в документе
		$params = array(
			'EvnXml_id' => $data['EvnXml_id'],
			'XmlData' => json_encode($XmlData1),
			'session' => $data['session'],
			'pmUser_id' => $data['pmUser_id']
		);
		$resp = $this->dbmodel->updateSectionContent($params);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (!empty($resp['Error_Msg'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $resp['Error_Msg']
			));
		}
	}

	/**
	 * Добавление данных документа
	 */
	function index_post() {
		$data = $this->ProcessInputData('createEvnXml', null, true, true, true);

		if (!empty($data['EvnXml_Data64'])) {
			$data['EvnXml_Data'] = base64_decode($data['EvnXml_Data64']);
		}
		if (!empty($data['XmlTemplateHtml_HtmlTemplate64'])) {
			$data['XmlTemplateHtml_HtmlTemplate'] = base64_decode($data['XmlTemplateHtml_HtmlTemplate64']);
		}

		$EvnInfo = $this->getEvnInfo($data);
		if (!is_array($EvnInfo)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$XmlTemplateInfo = $this->getXmlTemplateInfo($data);
		if (!is_array($XmlTemplateInfo)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		if ($XmlTemplateInfo['XmlType_id'] != $data['XmlType_id']) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Переданный тип документ не совпадает с типом шаблона'
			));
		}

		if ($XmlTemplateInfo['EvnClass_id'] != $EvnInfo['EvnClass_id']) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Переданный щаблон не подходит для случая лечения'
			));
		}

		$evnClassList = $this->xtmodel->getEvnClassListForXmlType($data['XmlType_id']);
		if (empty($evnClassList) || !in_array($EvnInfo['EvnClass_id'], $evnClassList)) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Не возможно создать документ по переданному типу документа'
			));
		}

		$evnClassList = $this->xtmodel->getEvnClassListForXmlType($XmlTemplateInfo['XmlType_id']);
		if (empty($evnClassList) || !in_array($EvnInfo['EvnClass_id'], $evnClassList)) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Не возможно создать документ по переданному шаблону'
			));
		}

		//Создание нового пустого документа
		$params = array(
			'EvnXml_id' => null,
			'Evn_id' => $data['Evn_id'],
			'XmlType_id' => $data['XmlType_id'],
			'XmlTemplateHtml_HtmlTemplate' => $data['XmlTemplateHtml_HtmlTemplate'],
			'XmlTemplate_id' => $data['XmlTemplate_id'],
			'MedStaffFact_id' => !empty($EvnInfo['MedStaffFact_id'])?$EvnInfo['MedStaffFact_id']:null,
			'session' => $data['session'],
			'pmUser_id' => $data['pmUser_id']
		);
		$resp = $this->dbmodel->createEmpty($params);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (!empty($resp['Error_Msg'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $resp['Error_Msg']
			));
		}
		$data['EvnXml_id'] = $resp['EvnXml_id'];

		if (!empty($data['EvnXml_Data'])) {
			$this->saveXmlData($data);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array(
				'Evn_id' => $data['Evn_id'],
				'EvnXml_id' => $data['EvnXml_id']
			)
		));
	}

	/**
	 * Изменение данных документа
	 */
	function index_put() {
		$data = $this->ProcessInputData('updateEvnXml', null, true, true, true);

		if (!empty($data['EvnXml_Data64'])) {
			$data['EvnXml_Data'] = base64_decode($data['EvnXml_Data64']);
		}
		if (!empty($data['XmlTemplateHtml_HtmlTemplate64'])) {
			$data['XmlTemplateHtml_HtmlTemplate'] = base64_decode($data['XmlTemplateHtml_HtmlTemplate64']);
		}

		//Изменение типа документа или шаблона или текста документа
		if (!empty($data['XmlType_id']) || !empty($data['XmlTamplate_id']) || !empty($data['XmlTemplateHtml_HtmlTemplate'])) {
			$info = $this->dbmodel->getFirstRowFromQuery("
				select top 1
					EX.XmlType_id,
					EX.XmlTemplate_id,
					XTH.XmlTemplateHtml_HtmlTemplate
				from
					v_EvnXml EX with(nolock)
					left join v_XmlTemplateHtml XTH with(nolock) on XTH.XmlTemplateHtml_id = EX.XmlTemplateHtml_id
				where EX.EvnXml_id = :EvnXml_id
			", $data);
			if (!is_array($info)) {
				$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
			}

			$EvnInfo = $this->getEvnInfo($data);
			if (!is_array($EvnInfo)) {
				$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
			}

			if (!empty($data['XmlType_id'])) {
				$evnClassList = $this->xtmodel->getEvnClassListForXmlType($data['XmlType_id']);
				if (empty($evnClassList) || !in_array($EvnInfo['EvnClass_id'], $evnClassList)) {
					$this->response(array(
						'error_code' => 6,
						'error_msg' => 'Не возможно сохранить документ с переданным типом документа'
					));
				}
			}

			if (!empty($data['XmlTemplate_id'])) {
				$XmlTemplateInfo = $this->getXmlTemplateInfo($data);
				if (!is_array($XmlTemplateInfo)) {
					$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
				}

				if (!empty($data['XmlType_id']) && $XmlTemplateInfo['XmlType_id'] != $data['XmlType_id']) {
					$this->response(array(
						'error_code' => 6,
						'error_msg' => 'Переданный тип докумнта не совпадает с типом шаблона'
					));
				}

				if ($XmlTemplateInfo['EvnClass_id'] != $EvnInfo['EvnClass_id']) {
					$this->response(array(
						'error_code' => 6,
						'error_msg' => 'Переданный щаблон не подходит для случая лечения'
					));
				}

				$evnClassList = $this->xtmodel->getEvnClassListForXmlType($XmlTemplateInfo['XmlType_id']);
				if (empty($evnClassList) || !in_array($EvnInfo['EvnClass_id'], $evnClassList)) {
					$this->response(array(
						'error_code' => 6,
						'error_msg' => 'Не возможно сохранить документ с переданным шаблоном'
					));
				}
			}

			$params = array(
				'Evn_id' => $data['Evn_id'],
				'EvnXml_id' => $data['EvnXml_id'],
				'XmlTemplate_id' => $info['XmlTemplate_id'],
				'XmlType_id' => $info['XmlType_id'],
				'XmlTemplateHtml_HtmlTemplate' => $info['XmlTemplateHtml_HtmlTemplate'],
				'MedStaffFact_id' => !empty($EvnInfo['MedStaffFact_id'])?$EvnInfo['MedStaffFact_id']:null,
				'session' => $data['session'],
				'pmUser_id' => $data['pmUser_id']
			);
			if (!empty($data['XmlType_id'])) {
				$params['XmlType_id'] = $data['XmlType_id'];
			}
			if (!empty($data['XmlTemplate_id'])) {
				$params['XmlTemplate_id'] = $data['XmlTemplate_id'];
			}
			if (!empty($data['XmlTemplateHtml_HtmlTemplate'])) {
				$params['XmlTemplateHtml_HtmlTemplate'] = $data['XmlTemplateHtml_HtmlTemplate'];
			}

			$resp = $this->dbmodel->createEmpty($params);
			if (!is_array($resp)) {
				$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
			}
			if (!empty($resp['Error_Msg'])) {
				$this->response(array(
					'error_code' => 6,
					'error_msg' => $resp['Error_Msg']
				));
			}
		}

		//Изменение данных в документе
		if (!empty($data['EvnXml_Data'])) {
			$this->saveXmlData($data);
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 * Получение списка документов случая
	 */
	function XmlDocumentList_get() {
		$data = $this->ProcessInputData('loadEvnXmlList');

		$sp = getSessionParams();
		$data['Lpu_id'] = $sp['Lpu_id'];
		$resp = $this->dbmodel->loadEvnXmlListForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение списка шаблонов
	 */
	function XmlTemplate_get() {
		$data = $this->ProcessInputData('loadXmlTemplateList');

		$sp = getSessionParams();
		$data['Lpu_oid'] = $sp['Lpu_id'];

		$resp = $this->xtmodel->loadXmlTamplateListForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}
}