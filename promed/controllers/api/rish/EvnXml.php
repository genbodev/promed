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

class EvnXml extends SwREST_Controller {

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('EvnXmlBase_model', 'dbmodel');
		$this->inputRules = array(
			'mloadEvnXmlPanel' => array(
				array('field' => 'Evn_id', 'label' => 'Идентификатор случая', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'XmlType_id', 'label' => 'Идентификатор типа документа', 'rules' => 'required', 'type' => 'id')
			),
			'mdoLoadData' => array(
				array('field' => 'EvnXml_id', 'label' => 'Идентификатор исходного документа', 'rules' => '', 'type' => 'id'),
				array('field' => 'Evn_id', 'label' => 'Идентификатор родительского документа', 'rules' => '', 'type' => 'id'),
				array('field' => 'forEdit', 'label' => 'Признак редактирования', 'rules' => '', 'type' => 'id'),
			),
			'mCreateEmpty' => array(
				array('field' => 'EvnXml_id', 'label' => 'Идентификатор документа', 'rules' => 'trim', 'type' => 'id'),
				array('field' => 'Evn_id', 'label' => 'Идентификатор события', 'rules' => 'trim', 'type' => 'id'),
				array('field' => 'XmlTemplate_id', 'label' => 'Идентификатор шаблона', 'rules' => 'trim', 'type' => 'id'),
				array('field' => 'XmlType_id', 'label' => 'Тип документа', 'rules' => 'trim|required', 'type' => 'id'),
				array('field' => 'EvnClass_id', 'label' => 'Категория документа', 'rules' => 'trim', 'type' => 'id'),
				array('field' => 'MedStaffFact_id', 'label' => 'Рабочее место врача', 'rules' => 'trim', 'type' => 'id'),
			),
			'mUpdateContent' => array(
				array('field' => 'EvnXml_id', 'label' => 'Идентификатор документа', 'rules' => 'trim|required', 'type' => 'id'),
				array('field' => 'name', 'label' => 'Имя раздела', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'value', 'label' => 'Содержание раздела', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'XmlData', 'label' => 'Имена разделов со значениями', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'isHTML', 'label' => 'Флаг сохраняем с разметкой', 'rules' => 'trim', 'type' => 'int', 'default' => 0),
				array('field' => 'save_data', 'label' => 'данные для сохранения области документа', 'rules' => 'trim', 'type' => 'string')
			),
			'mDestroy' => array(
				array('field' => 'EvnXml_id', 'label' => 'Идентификатор документа', 'rules' => 'trim|required', 'type' => 'id')
			)
		);
	}

	/**
	 * Загрузка списка направлений для мобильного приложения
	 */
	function mloadEvnXmlPanel_get() {
		$data = $this->ProcessInputData('mloadEvnXmlPanel');

		$resp = $this->dbmodel->loadEvnXmlPanel($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}
	
	/**
	 * метод получения данных документа по EvnXml_id
	 */
	function mdoLoadData_get() {
		$data = $this->ProcessInputData('mdoLoadData');

		if (empty($data['EvnXml_id']) && empty($data['Evn_id'])) {
			$this->response(array(
				'error_code' => 3,
				'error_msg' => "Отсутствует один из обязательных параметров: 'EvnXml_id' или 'Evn_id'"
			));
		}
		
		$resp = $this->dbmodel->doLoadEvnXmlPanel($data);
		if (!is_array($resp)) $this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);

		//echo '<pre>',print_r(json_encode(array("data"=>"<p>&nbsp;</p>"))),'</pre>'; die();

		if (!empty($resp)) {
			if (!empty($resp[0]['EvnXml_id']) && isset($resp[0]['XmlTemplate_HtmlTemplate'])) {
				if (empty($data['forEdit'])) {
					$this->load->model('XmlTemplate6E_model', 'xtmodel');
					$this->load->library('swEvnXml');
					$resp_ex = $this->xtmodel->getParamsByXmlTemplateOrEvnXml(array(
						'EvnXml_id' => $resp[0]['EvnXml_id']
					));
					if (!$this->xtmodel->isSuccessful($resp_ex)) {
						throw new Exception($resp_ex[0]['Error_Msg']);
					}

					$resp[0]['XmlTemplate_HtmlTemplate'] = swEvnXml::doPrint(
						$resp_ex[0]['params'],
						getRegionNick(),
						false,
						false,
						true,
						false
					);
				}

				// уберем всю лишнюю хрень
				$resp[0]['XmlTemplate_HtmlTemplate'] = str_replace(array("\r", "\n", "\t"), '', $resp[0]['XmlTemplate_HtmlTemplate']);
				// декодируем символы
				$resp[0]['XmlTemplate_HtmlTemplate'] = html_entity_decode($resp[0]['XmlTemplate_HtmlTemplate'], ENT_NOQUOTES | ENT_HTML5, 'UTF-8');
				$resp[0]['XmlTemplate_TextTemplate'] = strip_tags($resp[0]['XmlTemplate_HtmlTemplate']);
			}
		}

		$this->response(array('error_code' => 0,'data' => $resp));
	}
	/**
	 *
	 * @OA\post(
	path="/api/EvnXml/mCreateEmpty",
	tags={"EvnXml"},
	summary="Создание нового документа из шаблона",

	@OA\Parameter(
	name="EvnXml_id",
	in="query",
	description="Идентификатор документа",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="Evn_id",
	in="query",
	description="Идентификатор события",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="XmlTemplate_id",
	in="query",
	description="Идентификатор шаблона",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="XmlType_id",
	in="query",
	description="Тип документа",
	required=true,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="EvnClass_id",
	in="query",
	description="Категория документа",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="MedStaffFact_id",
	in="query",
	description="Рабочее место врача",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,

	@OA\Response(
	response="200",
	description="JSON response",
	@OA\JsonContent(
	type="object",

	@OA\Property(
	property="error_code",
	description="Код ошибки",
	type="string",

	)
	,
	@OA\Property(
	property="success",
	description="Результат выполенения",
	type="string",

	)

	)
	)

	)
	 */
	function mCreateEmpty_post() {
		$this->load->library('swXmlTemplate');
		$instance = swXmlTemplate::getEvnXmlModelInstance();
		$data = $this->ProcessInputData('mCreateEmpty',false, true);
		$response = $instance->mCreateEmpty($data);
		try {
			if (!empty($response['Error_Msg'])) {
				throw new Exception($response['Error_Msg'], 400);
			}
			$response = array('error_code' => 0, 'success' => true);
		} catch (Exception $e) {
			$response = array('error_code' => 777, 'error_msg' => toUtf($e->getMessage()));
		}
		$this->response($response);
	}

	/**
	 * @OA\post(
	path="/api/EvnXml/mUpdateContent",
	tags={"EvnXml"},
	summary="Функция сохранения Xml-данных. Дополнительные параметры передать в массиве save_data",

	@OA\Parameter(
	name="EvnXml_id",
	in="query",
	description="Идентификатор документа",
	required=true,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="name",
	in="query",
	description="Имя раздела",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="value",
	in="query",
	description="Содержание раздела",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="XmlData",
	in="query",
	description="Имена разделов со значениями",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="isHTML",
	in="query",
	description="Флаг сохраняем с разметкой",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,

	@OA\Response(
	response="200",
	description="JSON response",
	@OA\JsonContent(
	type="object",

	@OA\Property(
	property="error_code",
	description="Код ошибки",
	type="string",

	)
	,
	@OA\Property(
	property="success",
	description="Результат выполнения",
	type="string",

	)

	)
	)

	)
	 */
	function mUpdateContent_post()
	{
		$this->load->library('swXmlTemplate');
		$instance = swXmlTemplate::getEvnXmlModelInstance();
		$data = $this->ProcessInputData('mUpdateContent',false, true);
		if (!empty($data['save_data'])) {

			$data['template'] = json_decode($data['save_data'], true);
			if (is_array($data['template'])) {
				$data = array_merge($data, $data['template']);
			}
		} else {
			$data['template'] = [];
		}
		$response = $instance->mUpdateSectionContent($data);
		try {
			if (!empty($response['Error_Msg'])) {
				throw new Exception($response['Error_Msg'], 400);
			}
			$response = array('error_code' => 0, 'success' => true);
		} catch (Exception $e) {
			$response = array('error_code' => 777, 'error_msg' => toUtf($e->getMessage()));
		}
		$this->response($response);
	}
	/**
	 *
	 * @OA\post(
	path="/api/EvnXml/mDestroy",
	tags={"EvnXml"},
	summary="Удаление документа",

	@OA\Parameter(
	name="EvnXml_id",
	in="query",
	description="Идентификатор документа",
	required=true,
	@OA\Schema(type="integer", format="int64")
	)
	,

	@OA\Response(
	response="200",
	description="JSON response",
	@OA\JsonContent(
	type="object",

	@OA\Property(
	property="error_code",
	description="Код ошибки",
	type="string",

	)
	,
	@OA\Property(
	property="success",
	description="Результат выполнения",
	type="string",

	)
	,
	@OA\Property(
	property="error_msg",
	description="Текст ошибки",
	type="string",

	)

	)
	)

	)

	 */
	public function mDestroy_post()
	{
		$this->load->library('swXmlTemplate');
		$instance = swXmlTemplate::getEvnXmlModelInstance();
		$this->inputRules['destroy'] = $instance->getInputRules(swModel::SCENARIO_DELETE);
		$data = $this->ProcessInputData('destroy', false,true);
		$response = $instance->doDelete($data);
		try {
			if (!empty($response['Error_Msg'])) {
				throw new Exception($response['Error_Msg'], 400);
			}
			$response = array('error_code' => 0, 'success' => true);
		} catch (Exception $e) {
			$response = array('error_code' => 777, 'error_msg' => toUtf($e->getMessage()));
		}
		$this->response($response);
	}
}