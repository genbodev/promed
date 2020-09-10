<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnPrescr - контроллер API для работы с назначениями
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

class Template extends SwREST_Controller
{
	protected $inputRules = array(
		'mgetEvnData' => array(
			array(
				'field' => 'MedStaffFact_id',
				'label' => 'Идентификатор рабочего места врача',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnClass_SysNick',
				'label' => 'Имя класса объекта',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'Evn_id',
				'label' => 'Идентификатор объекта',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'isChild',
				'label' => 'Признак вложенного события',
				'default' => false,
				'rules' => '',
				'type' => 'boolean'
			)
		),
		'mGetEvnDocument' => array(
			array(
				'field' => 'MedStaffFact_id',
				'label' => 'Идентификатор рабочего места врача',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnClass_SysNick',
				'label' => 'Имя класса объекта',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'Evn_id',
				'label' => 'Идентификатор объекта',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'isChild',
				'label' => 'Признак вложенного события',
				'default' => false,
				'rules' => '',
				'type' => 'boolean'
			),
			array(
				'field' => 'rawHtmlDocument',
				'label' => 'Признак отмены очистки документа',
				'rules' => '',
				'type' => 'boolean'
			),
		),
		'mLoadEvnXmlViewData' => array (
			array('field' => 'EvnXml_id','label' => 'Идентификатор документа','rules' => 'required','type' => 'id'),
			array('field' => 'instance_id','label' => 'Идентификатор экземпляра','rules' => '', 'type' => 'string'),
		),
	);

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('Template_model', 'dbmodel');
	}

	/**
	 * Убрать атрибуты по списку
	 */
	function removeHtmlAttributes($element, $attributeList)
	{
		foreach ($attributeList as $a) {
			$element->removeAttribute($a);
		}
	}

	/**
	 * Убрать классы по списку
	 */
	function removeDocumentNodes($classList, $finder)
	{
		foreach ($classList as $c) {
			$classname = $c;
			$removeNode = $finder->query("//*[contains(@class, '$classname')]")->item(0);
			if (!empty($removeNode)) $removeNode->parentNode->removeChild($removeNode);

		}
	}

	/**
	 * Очистка документа от лишних атрибутов и элементов
	 */
	function clearEvnDocument($document, $data) {

		// преобразуем поле дата в div чтобы loadHTML не ругался
		$document = str_replace("<data ", '<div ', $document);
		$document = str_replace("</data>", '</div>', $document);

		$dom = new DOMDocument;
		$dom->loadHTML('<?xml encoding="utf-8" ?>' . $document);

		// уберем лишниее элементы по имени класса
		$this->removeDocumentNodes(array('right', 'noprint'), new DomXPath($dom));

		// уберем лишние атрибуты
		$elements = $dom->getElementsByTagName('*');
		$attributeList = array(
			'class',
			'id',
			'onmouseout',
			'onmouseover',
			'style',
			'title',
			'align'
		);

		foreach ($elements as $element) {
			$this->removeHtmlAttributes($element, $attributeList);
		}

		// особое форматирование для типа события EvnUslugaPar
		if (!empty($data['EvnClass_SysNick']) && $data['EvnClass_SysNick'] === "EvnUslugaPar") {

			$mainDiv = $dom->getElementsByTagName('body')->item(0)->childNodes->item(0);
			$divCnt = 1;

			foreach ($mainDiv->childNodes as $node) {
				if (isset($node->tagName) && $node->tagName === 'div') {
					switch ($divCnt) {
						case 1:
							$node->setAttribute('class', 'info');
							break;
						case 2:
							$node->setAttribute('class', 'protocol');
							break;
					}
					$divCnt++;
				}
				if ($divCnt > 2) break;
			}
		}

		$document = $dom->saveHTML($dom->documentElement);
		$document = str_replace(array("\r", "\n", "\t"), '', $document); // очистим символы переноса строки

		return $document;
	}

	/**
	 * Показать назначения для случая лечения
	 */
	function mgetEvnData_get()
	{
		$data = $this->ProcessInputData('mgetEvnData', null, true);

		$mapping = array(
			'user_MedStaffFact_id' => $data['MedStaffFact_id'],
			'object' => $data['EvnClass_SysNick'],
			'object_value' => $data['Evn_id'],
			'object_id' => $data['EvnClass_SysNick'] . '_id',
			'view_section' => 'list',
			'from_MSE' => 1,
			'section' => 'section'
		);

		if (!empty($data['isChild'])) $mapping[$data['EvnClass_SysNick'] . '_pid'] = $data['Evn_id'];

		$data = array_merge($data, $mapping);
		$resp = $this->dbmodel->getEvnData($data);

		if (!is_array($resp)) $this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		$this->response(array('error_code' => 0, 'data' => $resp));
	}

	/**
	 * Показать результаты выполнения услуги и саму услугу
	 */
	function mGetEvnDocument_get()
	{
		$data = $this->ProcessInputData('mGetEvnDocument', null, true);

		$mapping = array(
			'user_MedStaffFact_id' => $data['MedStaffFact_id'],
			'object' => $data['EvnClass_SysNick'],
			'object_value' => $data['Evn_id'],
			'object_id' => $data['EvnClass_SysNick'] . '_id',
			'view_section' => 'list',
			'from_MSE' => 1,
			'section' => 'section'
		);

		if (!empty($data['isChild'])) $mapping[$data['EvnClass_SysNick'] . '_pid'] = $data['Evn_id'];

		$data = array_merge($data, $mapping);
		$resp = $this->dbmodel->getEvnData($data);

		if (!is_array($resp)) $this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);

		$success = false;

		if (!empty($resp)) {
			$this->load->helper("Xml");
			$this->load->library('parser');
			$document = $this->dbmodel->getEvnDocument($resp);

			// если не указан признак "грязного документа" чистим документ от лишних тегов
			if (empty($data['rawHtmlDocument'])) $document = $this->clearEvnDocument($document, $data);
			if (!empty($document)) $success = true;
		}

		if ($success) {
			$this->response(array('error_code' => 0, 'data' => $document));
		} else {
			$this->response(array('error_code' => 6, 'error_msg' => "Не удалось сформировать документ!"));
		}
	}
	/**
	 * @OA\post(
	path="/api/Template/mLoadEvnXmlViewData",
	tags={"Template"},
	summary="Функция чтения документа для формы просмотра",

	@OA\Parameter(
	name="EvnXml_id",
	in="query",
	description="Идентификатор документа",
	required=true,
	@OA\Schema(type="integer", format="int64")
	)
	,
	@OA\Parameter(
	name="instance_id",
	in="query",
	description="Идентификатор экземпляра",
	required=false,
	@OA\Schema(type="string")
	)
	,

	@OA\Response(
	response="200",
	description="JSON response",
	@OA\JsonContent(
	type="object",

	@OA\Property(
	property="error_code",
	description="Описание",
	type="string",

	)
	,
	@OA\Property(
	property="data",
	description="Описание",
	type="array",

	@OA\Items(
	type="object",

	@OA\Property(
	property="Evn_id",
	description="Событие, Идентификатор события",
	type="integer",

	)
	,
	@OA\Property(
	property="Evn_pid",
	description="Событие, Учетный документ, в рамках которого добавлено заболевание",
	type="string",

	)
	,
	@OA\Property(
	property="Evn_rid",
	description="Событие, получатель документа",
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
	property="EvnXml_id",
	description="Ненормализованные данные для событий , Идентификатор",
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
	property="xml_data",
	description="Дополнительная информация",
	type="array",

	@OA\Items(
	type="object",

	@OA\Property(
	property="parameter",
	description="Информация о параметре",
	type="string",

	)

	)

	)
	,
	@OA\Property(
	property="html",
	description="Строка с html версткой",
	type="string",

	)

	)

	)

	)
	)

	)
	 */
	function mLoadEvnXmlViewData_post() {
		$data = $this->ProcessInputData('mLoadEvnXmlViewData', false,true);
		if ($data) {
			$this->load->database();
			$this->load->library('swEvnXml');
			$this->load->library('swXmlTemplate');
			$parse_data = array();
			$object_data = array();
			$this->load->model('EvnXmlBase_model');
			try {
				$xml_data = $this->EvnXmlBase_model->doLoadEvnXmlPanel($data);
				if (isset($xml_data[0]['EvnXml_id'])) {
					$result['Evn_id'] = $xml_data[0]['Evn_id'];
					$result['Evn_pid'] = $xml_data[0]['Evn_pid'];
					$result['Evn_rid'] = $xml_data[0]['Evn_rid'];
					$result['EvnClass_id'] = $xml_data[0]['EvnClass_id'];
					$result['EvnXml_id'] = $xml_data[0]['EvnXml_id'];
					$result['XmlType_id'] = $xml_data[0]['XmlType_id'];
					$result['xml_data'] = swXmlTemplate::transformEvnXmlDataToArr(toUTF($xml_data[0]['EvnXml_Data']));
				}

				$html_from_xml = swEvnXml::doHtmlView(
					$xml_data,
					$parse_data,
					$object_data
				);
				$result['xml_data'] = $object_data['xml_data'];
			} catch (Exception $e) {
				$html_from_xml = '<div>'. $e->getMessage() .'</div>';
			}
			foreach($result['xml_data'] as &$xd) {
				$xd = str_replace('&lt;', '&amp;lt;', $xd);
				$xd = str_replace('&gt;', '&amp;gt;', $xd);
			}
			ConvertFromWin1251ToUTF8($html_from_xml);
			$result['html'] = $html_from_xml;
			$this->response(array('error_code'=>0,'data'=>$result));
		} else {
			return false;
		}
	}
}