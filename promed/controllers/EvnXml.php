<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnXml - контроллер для работы с Xml-документами
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2015 Swan Ltd.
 * @author       Пермяков Александр Михайлович
 * @version      04.2015
 *
 * @property EvnXmlBase_model $dbmodel
 */

class EvnXml extends swController
{
	/**
	 * Конструктор
	 *
	 * Определяем правила, выбираем БД
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model('EvnXmlBase_model', 'dbmodel');
		$this->inputRules = array();
	}

	/**
	 * Загрузка комбо документов для события
	 */
	function loadEvnXmlCombo()
	{
		$this->inputRules['loadEvnXmlCombo'] = $this->dbmodel->getInputRules('loadEvnXmlCombo');
		$data = $this->ProcessInputData('loadEvnXmlCombo', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadEvnXmlCombo($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 *  Получение списка документов для панели направлений в ЭМК
	 */
	function loadEvnXmlPanel()
	{
		$this->inputRules['loadEvnXmlPanel'] = $this->dbmodel->getInputRules('loadEvnXmlPanel');
		$data = $this->ProcessInputData('loadEvnXmlPanel', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadEvnXmlPanel($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 * Копирование указанного XML-документа
	 * в указанный учетный документ
	 *
	 * @return bool
	 */
	function doCopy()
	{
		$this->inputRules['doCopy'] = $this->dbmodel->getInputRules('doCopy');
		$data = $this->ProcessInputData('doCopy', true);
		if (!$data) { return false; }
		$response = $this->dbmodel->doCopy($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}

	/**
	 *  Запрос данных документа для просмотра или редактирования в панели sw.Promed.EvnXmlPanel
	 *  На выходе: JSON-строка
	 *
	 * @return bool
	 * Используется: дофига где
	 */
	function doLoadData()
	{
		$this->inputRules['doLoadData'] = $this->dbmodel->getInputRules('doLoadEvnXmlPanel');
		$data = $this->ProcessInputData('doLoadData', true);
		if (!$data) { return false; }
		$this->OutData = array(
			'html' => '',
			'data' => null,
			'success' => true,
			'Error_Msg' => null
		);
		try {
			$xml_data = $this->dbmodel->doLoadEvnXmlPanel($data);
			if (empty($xml_data) || !is_array($xml_data)) {
				$this->ReturnData();
				return true;
			}
			$parse_data = array();
			$this->OutData['data'] = array(
				'XmlTemplate_id' => $xml_data[0]['XmlTemplate_id'],
				'XmlTemplateType_id' => $xml_data[0]['XmlTemplateType_id'],
				'EvnXml_Name' => toUtf($xml_data[0]['EvnXml_Name']),
				'EvnXml_id' => $xml_data[0]['EvnXml_id'],
				'EvnXml_IsSigned' => $xml_data[0]['EvnXml_IsSigned']
			);
			$this->OutData['html'] = swEvnXml::doHtmlView(
				$xml_data,
				$parse_data,
				$this->OutData['data']
			);
			$this->OutData['html'] = toUtf($this->OutData['html']);
		} catch (Exception $e) {
			$this->OutData['success'] = false;
			$this->OutData['Error_Msg'] = toUtf($e->getMessage());
		}
		$this->ReturnData();
		return true;
	}
	
	/**
	 *  Печать документа
	 *  На выходе: HTML-строка в кодировке Win1251 или PDF документ
	 *
	 * @return bool
	 * Используется: дофига где
	 */
	function doPrint()
	{
		$this->inputRules['doPrint'] = $this->dbmodel->getInputRules('doLoadPrintData');
		$data = $this->ProcessInputData('doPrint', true);
		if (!$data) { return false; }
		try {
			if (defined('USE_POSTGRESQL_LIS') && USE_POSTGRESQL_LIS) {
				$this->load->swapi('common');
				$res = $this->common->GET('EvnXml/PrintData', $data, 'single');
				if (!$this->isSuccessful($res)) {
					return $res;
				}
				$xml_data[0] = $res;
			} else {
				$xml_data = $this->dbmodel->doLoadPrintData($data);
			}
			return swEvnXml::doPrint(
				$xml_data[0], 
				$data['session']['region']['nick'], 
				($data['printHtml']==2), 
				(!empty($data['useWkhtmltopdf'])),
				false,
				$data['doHalf']
			);
		} catch (Exception $e) {
			echo $e->getMessage();
			return false;
		}
	}
	
	/**
	 *  Печать шаблона без сохранения документа
	 *  На выходе: HTML-строка в кодировке Win1251 или PDF документ
	 *
	 * @return bool
	 */
	function doDirectPrint()
	{
		$this->inputRules['doDirectPrint'] = $this->dbmodel->getInputRules('doDirectPrint');
		$data = $this->ProcessInputData('doDirectPrint', true);
		if (!$data) { return false; }
		try {
			$xml_data = $this->dbmodel->doDirectPrint($data);
			return swEvnXml::doPrint(
				$xml_data,
				$data['session']['region']['nick'],
				($data['printHtml']==2),
				(!empty($data['useWkhtmltopdf']))
			);
		} catch (Exception $e) {
			echo $e->getMessage();
			return false;
		}
	}


	/**
	 * @return bool
	 * @throws Exception
	 */
	function checkIsMarkeryBezDannix(){
		$this->inputRules['checkIsMarkeryBezDannix'] = $this->dbmodel->getInputRules('checkIsMarkeryBezDannix');
		$data = $this->ProcessInputData('checkIsMarkeryBezDannix', true);
		if ($data === false) {return false;}

		$this->OutData = array(
			'markers' => null,
			'success' => true,
			'Error_Msg' => null
		);


		$markers = $this->dbmodel->checkIsMarkeryBezDannix($data);

		if( ! empty($markers)){
			$this->OutData['markers'] = $markers;
		}

		$this->ReturnData();
	}

	/**
	 * Создание нового документа из шаблона
	 * На выходе: JSON-строка
	 * Используется: формa ЭМК
	 */
	function createEmpty()
	{
		$this->load->library('swXmlTemplate');
		$instance = swXmlTemplate::getEvnXmlModelInstance();
		$this->inputRules['createEmpty'] = $instance->getInputRules('createEmpty');
		$data = $this->ProcessInputData('createEmpty',true);
		if ($data === false) {return false;}
		$response = $instance->createEmpty($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}

	/**
	 * Получение полей шаблона для генерации полей на клиенте
	 * @return boolean
	 */
	public function loadEvnXmlForm()
	{
		$this->inputRules['loadEvnXmlForm'] = $this->dbmodel->getInputRules(swModel::SCENARIO_LOAD_EDIT_FORM);
		$data = $this->ProcessInputData('loadEvnXmlForm', true);
		if (!$data) { return false; }
		$response = $this->dbmodel->loadEvnXmlForm($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}

	/**
	 * Восстановление исходного состояния документа
	 * На выходе: JSON-строка
	 * Используется: формa ЭМК
	 */
	function restore()
	{
		$this->load->library('swXmlTemplate');
		$instance = swXmlTemplate::getEvnXmlModelInstance();
		$this->inputRules['restore'] = $instance->getInputRules('restore');
		$data = $this->ProcessInputData('restore',true);
		if ($data === false) {return false;}
		$response = $instance->restore($data);
		$this->ProcessModelSave($response,true)->ReturnData();
		return true;
	}

	/**
	 * сохранение документа как шаблона
	 * На выходе: JSON-строка
	 */
	function saveAsTemplate()
	{
		$this->load->library('swXmlTemplate');
		$instance = swXmlTemplate::getEvnXmlModelInstance();
		$this->inputRules['saveAsTemplate'] = $instance->getInputRules('saveAsTemplate');
		$data = $this->ProcessInputData('saveAsTemplate',true);
		if ($data === false) {return false;}
		$response = $instance->saveAsTemplate($data);
		$this->ProcessModelSave($response,true)->ReturnData();
		return true;
	}

	/**
	 * Удаление документа
	 * На выходе: JSON-строка
	 * Используется: формa ЭМК
	 */
	public function destroy()
	{
		$this->load->library('swXmlTemplate');
		$instance = swXmlTemplate::getEvnXmlModelInstance();
		$this->inputRules['destroy'] = $instance->getInputRules(swModel::SCENARIO_DELETE);
		$data = $this->ProcessInputData('destroy',true);
		if ($data === false) {return false;}
		$response = $instance->doDelete($data);
		$this->ProcessModelSave($response,true)->ReturnData();
		return true;
	}

	/**
	 * Функция сохранения Xml-данных
	 * На выходе: JSON-строка
	 * Используется: форма ЭМК
	 */
	function updateContent()
	{
		$this->load->library('swXmlTemplate');
		$instance = swXmlTemplate::getEvnXmlModelInstance();
		$this->inputRules['updateContent'] = $instance->getInputRules('updateSectionContent');
		$data = $this->ProcessInputData('updateContent', true);
		if ($data === false) { return false; }
		$response = $instance->updateSectionContent($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}

	/**
	 * Функция удаления Xml-данных
	 * На выходе: JSON-строка
	 * Используется: форма ЭМК
	 */
	function destroySection()
	{
		$this->load->library('swXmlTemplate');
		$instance = swXmlTemplate::getEvnXmlModelInstance();
		$this->inputRules['destroySection'] = $instance->getInputRules('destroySection');
		$data = $this->ProcessInputData('destroySection', true);
		if ($data === false) { return false; }
		$response = $instance->destroySection($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}

	/**
	 * Функция "Восстановить раздел"
	 * На выходе: JSON-строка
	 * Используется: форма ЭМК
	 */
	function resetSection()
	{
		$this->load->library('swXmlTemplate');
		$instance = swXmlTemplate::getEvnXmlModelInstance();
		$this->inputRules['resetSection'] = $instance->getInputRules('resetSection');
		$data = $this->ProcessInputData('resetSection', true);
		if ($data === false) { return false; }
		$response = $instance->resetSection($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}

	/**
	 * Если указан документ, то определяем его шаблон
	 * На выходе: JSON-строка
	 *
	 * @return bool
	 */
	function getXmlTemplateId()
	{
		$this->inputRules['getXmlTemplateId'] = $this->dbmodel->getInputRules('getXmlTemplateInfo');
		$data = $this->ProcessInputData('getXmlTemplateId', true);
		if ($data === false) { return false; }
		$this->OutData = array(
			'XmlTemplate_id' => null,
			'success' => true,
			'Error_Msg' => null
		);
		$response = $this->dbmodel->getXmlTemplateInfo($data);
		if (is_array($response) && count($response) > 0) {
			foreach ($response[0] as $k => $v) {
				$this->OutData[$k] = toUtf($v);
			}
		} else {
			$this->OutData['success'] = false;
			$this->OutData['Error_Msg'] = toUtf('Не удалось определить шаблон!');
		}
		$this->ReturnData();
		return true;
	}

	/**
	 * Получаем EvnXml_id протокола последнего посещения в рамках указанного талона
	 */
	public function getLastEvnProtocolId()
	{
		$this->inputRules['getLastEvnProtocolId'] = $this->dbmodel->getInputRules('getLastEvnProtocolId');
		$data = $this->ProcessInputData('getLastEvnProtocolId', false);
		if ($data === false) { return false; }
		$response = $this->dbmodel->getLastEvnProtocolId($data);
		$this->ProcessModelList($response,true,true)->ReturnData();
		return true;
	}

	/**
	 * Получаем список разделов
	 */
	public function loadXmlDataSectionList()
	{
		$this->inputRules['loadXmlDataSectionList'] = $this->dbmodel->getInputRules('loadXmlDataSectionList');
		$data = $this->ProcessInputData('loadXmlDataSectionList', false);
		if ($data === false) { return false; }
		$response = $this->dbmodel->loadXmlDataSectionList($data);
		$this->ProcessModelList($response,true)->ReturnData();
		return true;
	}

	/**
	 * Получаем список типов маркеров документов
	 */
	public function loadXmlMarkerTypeList()
	{
		$this->inputRules['loadXmlMarkerTypeList'] = $this->dbmodel->getInputRules('loadXmlMarkerTypeList');
		$data = $this->ProcessInputData('loadXmlMarkerTypeList', false);
		if ($data === false) { return false; }
		$response = $this->dbmodel->loadXmlMarkerTypeList($data);
		$this->ProcessModelList($response,true)->ReturnData();
		return true;
	}

	/**
	 * Создание связи между направлением и документом в таблице EvnXmlDirectionLink
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function createEvnXmlDirectionLink()
	{
		$this->inputRules['createEvnXmlDirectionLink'] = $this->dbmodel->getInputRules('createEvnXmlDirectionLink');
		$data = $this->ProcessInputData('createEvnXmlDirectionLink', true);

		if ($data === false) { return false; }
		$response = $this->dbmodel->createEvnXmlDirectionLink($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}

	/**
	 * Удаление связи между направлением и документом в таблице EvnXmlDirectionLink
	 *
	 * @return bool
	 */
	public function deleteEvnXmlDirectionLink()
	{
		$this->inputRules['deleteEvnXmlDirectionLink'] = $this->dbmodel->getInputRules('deleteEvnXmlDirectionLink');
		$data = $this->ProcessInputData('deleteEvnXmlDirectionLink',false);
		if ($data === false) {return false;}
		$response = $this->dbmodel->deleteEvnXmlDirectionLink($data);
		$this->ProcessModelSave($response,true)->ReturnData();
		return true;
	}
}