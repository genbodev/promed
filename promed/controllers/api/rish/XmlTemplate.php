<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * XmlTemplate - контроллер работы с Xml-шаблонами
*/

require(APPPATH.'libraries/SwREST_Controller.php');

class XmlTemplate extends SwREST_Controller
{
	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->inputRules = array(
			'mLoadGrid' => array(
				array('field' => 'EvnClass_id', 'label' => 'Идентификатор класса события', 'rules' => 'trim', 'type' => 'id'),
				array('field' => 'XmlType_id', 'label' => 'Идентификатор типа шаблона', 'rules' => 'trim', 'type' => 'id'),
				array('field' => 'XmlTypeKind_id', 'label' => 'Идентификатор вида документа', 'rules' => 'trim', 'type' => 'id'),
				array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения', 'rules' => 'trim', 'type' => 'id'),
				array('field' => 'XmlTemplateCat_id', 'label' => 'Идентификатор категории шаблонов отображения xml данных', 'rules' => 'trim', 'type' => 'id'),
				array('field' => 'showXmlTemplate_id', 'label' => 'Идентификатор шаблона', 'rules' => 'trim', 'type' => 'id'),
				array('field' => 'templName', 'label' => 'Строка поиска', 'rules' => 'ban_percent|trim', 'type' => 'string'),
				array('field' => 'templType', 'label' => 'Тип Поиска', 'rules' => 'trim', 'type' => 'int'),
				array('field' => 'start', 'label' => 'Начальный номер записи', 'rules' => 'trim', 'default' => 0, 'type' => 'int'),
				array('field' => 'limit', 'label' => 'Количество возвращаемых записей', 'rules' => 'trim', 'default' => 50, 'type' => 'int'),
				array('field' => 'XmlTemplate_onlyOld', 'label' => 'Признак того, что нужно отобразить только шаблоны, которые нуждаются в пересоздании вручную', 'rules' => 'trim', 'type' => 'id'),
				array('field' => 'UslugaComplex_id', 'label' => 'Услуга', 'rules' => 'trim', 'type' => 'id'),
			),
			'mPreview' => array(
				array('field' => 'XmlTemplate_id', 'label' => 'Идентификатор шаблона', 'rules' => 'trim|required', 'type' => 'id'),
				array('field' => 'Evn_id', 'label' => 'Идентификатор события', 'rules' => 'trim', 'type' => 'id'),
				array('field' => 'EvnXml_id', 'label' => 'Идентификатор документа', 'rules' => 'trim', 'type' => 'id')
			)
		);
	}


	/**
	 *
	 * @OA\post(
	path="/api/XmlTemplate/mPreview",
	tags={"XmlTemplate"},
	summary="Функция предварительного просмотра шаблона",

	@OA\Parameter(
	name="XmlTemplate_id",
	in="query",
	description="Идентификатор шаблона",
	required=true,
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
	name="EvnXml_id",
	in="query",
	description="Идентификатор документа",
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
	property="XmlTemplate_HtmlTemplate",
	description="HTML разметка",
	type="string",

	)

	)
	)

	)
	 */
	function mPreview_post()
	{
		$this->load->model('XmlTemplateBase_model');
		$data = $this->ProcessInputData('mPreview', false,true);

		$output = array(array('XmlTemplate_HtmlTemplate' => ''));
		try {
			// получаем данные
			$response = $this->XmlTemplateBase_model->doLoadEditForm($data);
			// получаем HTML-шаблон
			array_walk($response[0], 'toUTF');
			$output[0]['XmlTemplate_HtmlTemplate'] = swXmlTemplate::getHtmlTemplate($response[0], false);
			if (empty($output[0]['XmlTemplate_HtmlTemplate'])) {
				throw new Exception('Не удалось получить HTML-шаблон', 500);
			}
			$this->load->library('swMarker');
			//это нужно для печати объектов с типом Параметр и список значений
			$output[0]['XmlTemplate_HtmlTemplate'] = swMarker::createParameterValueFields($output[0]['XmlTemplate_HtmlTemplate'], 0, array(), true);
			// если задан Evn_id при предпросмотре шаблона, то обрабатываем текст маркерами. #10610 #10006
			if (!empty($data['Evn_id'])) {
				$output[0]['XmlTemplate_HtmlTemplate'] = swMarker::processingTextWithMarkers($output[0]['XmlTemplate_HtmlTemplate'], $data['Evn_id'], array(
					'isPrint' => true, 'EvnXml_id' => $data['EvnXml_id'],
				));
			}
			$this->load->library('swEvnXml');
			$output[0]['XmlTemplate_HtmlTemplate'] = swEvnXml::cleaningHtml($output[0]['XmlTemplate_HtmlTemplate'], array(
				'userLocalFiles' => 1,
			)); // #52118
		} catch (Exception $e) {
			$output[0]['XmlTemplate_HtmlTemplate'] = $e->getCode() . ' ' . $e->getMessage();
		}
		$this->response($output);
	}

	/**
	 *
	 *@OA\post(
	path="/api/XmlTemplate/mLoadGrid",
	tags={"XmlTemplate"},
	summary="Чтение списка шаблонов и папок для грида, из которого производится редактирование и выбор шаблонов для документа",

	@OA\Parameter(
	name="EvnClass_id",
	in="query",
	description="Идентификатор класса события",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="XmlType_id",
	in="query",
	description="Идентификатор типа шаблона",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="XmlTypeKind_id",
	in="query",
	description="Идентификатор вида документа",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="LpuSection_id",
	in="query",
	description="Идентификатор отделения",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="XmlTemplateCat_id",
	in="query",
	description="Идентификатор категории шаблонов отображения xml данных",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="showXmlTemplate_id",
	in="query",
	description="Идентификатор шаблона",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="templName",
	in="query",
	description="Строка поиска",
	required=false,
	@OA\Schema(type="string")
	)
	,
	@OA\Parameter(
	name="templType",
	in="query",
	description="Тип Поиска",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="start",
	in="query",
	description="Начальный номер записи",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="limit",
	in="query",
	description="Количество возвращаемых записей",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="XmlTemplate_onlyOld",
	in="query",
	description="Признак того, что нужно отобразить только шаблоны, которые нуждаются в пересоздании вручную",
	required=false,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="UslugaComplex_id",
	in="query",
	description="Идентификатор услуги",
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
	property="data",
	description="Данные",
	type="array",

	@OA\Items(
	type="object",

	@OA\Property(
	property="Item_Key",
	description="Идентифкатор шаблона",
	type="string",

	)
	,
	@OA\Property(
	property="XmlTemplate_id",
	description="Шаблоны отображения xml данных, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="XmlTemplateType_id",
	description="Тип шаблона, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="accessType",
	description="Уровень доступа",
	type="string",

	)
	,
	@OA\Property(
	property="EvnClass_Name",
	description="Класс события, наименование",
	type="string",

	)
	,
	@OA\Property(
	property="Item_Name",
	description="Название шаблона",
	type="string",

	)
	,
	@OA\Property(
	property="pmUser_Name",
	description="Имя автора",
	type="string",

	)
	,
	@OA\Property(
	property="XmlTemplateScope_Name",
	description="Область видимости шаблона, наименование",
	type="string",

	)
	,
	@OA\Property(
	property="Item_updDate",
	description="Дата обновления шаблона",
	type="string",

	)
	,
	@OA\Property(
	property="XmlTemplate_Settings",
	description="Настройки шаблона",
	type="string",

	)
	,
	@OA\Property(
	property="EvnClass_id",
	description="класс события, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="XmlType_id",
	description="Тип произвольного документа, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="XmlTemplateScope_id",
	description="Область видимости шаблона, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="XmlTemplateScope_eid",
	description="Доступно для редактрования
	 *     1 - администраторы системы
	 *     2 - Все
	 *     3 - ЛПУ автора
	 *     4 - Отделение автора
	 *     5 - Автор  ",
	type="string",

	)
	,
	@OA\Property(
	property="Lpu_id",
	description="справочник ЛПУ, ЛПУ",
	type="integer",

	)
	,
	@OA\Property(
	property="LpuSection_id",
	description="Справочник ЛПУ: отделения, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="pmUser_insID",
	description="Пользователь создаший шаблон",
	type="string",

	)
	,
	@OA\Property(
	property="XmlTemplateCat_id",
	description="Категории шаблонов отображения xml данных, идентификатор",
	type="integer",

	)
	,
	@OA\Property(
	property="XmlTemplate_Default",
	description="Признак категории шаблона(если есть XmlTemplateCatDefault_id то 1 иначе 2)",
	type="string",

	)
	,
	@OA\Property(
	property="Item_Type",
	description="Тип шаблона",
	type="string",

	)
	,
	@OA\Property(
	property="XmlTemplate_Preview",
	description="Информация о шаблоне",
	type="string",

	)
	,
	@OA\Property(
	property="Item_Path",
	description="Путь",
	type="string",

	)

	)

	)
	,
	@OA\Property(
	property="totalCount",
	description="Общее количество шаблонов",
	type="string",

	)

	)
	)

	)
	 */
	function mLoadGrid_post()
	{
		$this->load->model('XmlTemplateBase_model');
		$data = $this->ProcessInputData('mLoadGrid', false,true);
		if ($data) {
			$response = $this->XmlTemplateBase_model->mLoadGrid($data);
			try {
				if (!empty($response['Error_Msg'])) {
					throw new Exception($response['Error_Msg'], 400);
				}
				$response = array('error_code' => 0, 'data'=>$response);
			} catch (Exception $e) {
				$response = array('error_code' => 777, 'error_msg' => toUtf($e->getMessage()));
			}
			$this->response($response);
		}
	}
}
