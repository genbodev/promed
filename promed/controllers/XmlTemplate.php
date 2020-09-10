<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * XmlTemplate - контроллер работы с Xml-шаблонами
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2010 Swan Ltd.
 * @author       Пермяков Александр Михайлович
 * @version      04.2015
 *
 * @property XmlTemplateFavorites_model XmlTemplateFavorites_model
 * @property XmlTemplateBase_model $XmlTemplateBase_model
 */

class XmlTemplate extends swController
{
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
		$this->load->database();
		//$this->load->library('textlog', array('file'=>'Template_getEvnForm.log'));
		$this->inputRules = array(
			'getFavorites' => array(
				array('field' => 'EvnClass_id','label' => 'Категория','rules' => 'required','type' => 'id'),
			),
			'loadCombo' => array(
				array(
					'field' => 'XmlTemplate_id',
					'label' => 'Идентификатор выбранного шаблона',
					'rules' => '',
					'type' => 'id'
				),
			),
			'loadXmlTemplateLinkList' => array(
				array(
					'field' => 'XmlTemplate_id',
					'label' => 'Идентификатор шаблона',
					'rules' => 'required',
					'type' => 'id'
				),
			),
		);
	}

	/**
	 * Копирование шаблона из формы редактирования
	 * На выходе: JSON-строка
	 * Используется формами:
	 * /jscore/Forms/Common/swXmlTemplateEditWindow.js
	 */
	function copy()
	{
		$this->load->library('swXmlTemplate');

		$newInstance = swXmlTemplate::getXmlTemplateModelInstance();
		$this->inputRules['doCopy'] = $newInstance->getInputRules('doCopy');
		$data = $this->ProcessInputData('doCopy',true);
		if ($data === false) {return false;}

		$data['scenario'] = 'create';
		$data['XmlTemplate_id'] = null;

		$this->load->library('swEvnXml');
		$data['XmlTemplate_HtmlTemplate'] = swEvnXml::cleaningHtml($data['XmlTemplate_HtmlTemplate'], array(
			'withSpecChars' => 1,
			'commentWithoutTag' => 1,
			'commentWithoutExclamation' => 1,
			'commentWithIf' => 1,
			//'styles' => 1,
			'styleMso' => 1,
			'userLocalFiles' => 1,
		)); // #52118
		swXmlTemplate::processingData($data, $data['pmUser_id']);
		$data['htmltemplate'] = $data['XmlTemplate_HtmlTemplate'];
		$data['xmldata'] = $data['XmlTemplate_Data'];
		
		$this->load->library('swXmlTemplateSettings');
		$data['printsettings'] = swXmlTemplateSettings::getJsonFromArr($data);
		
		$response = $newInstance->doSave($data);
		$this->ProcessModelSave($response,true)->ReturnData();
		return true;
	}

	/**
	 * Сохранение шаблона из формы редактирования
	 * На выходе: JSON-строка
	 * Используется формами:
	 * /jscore/Forms/Common/swXmlTemplateEditWindow.js
	 */
	function save()
	{
		$this->load->library('swXmlTemplate');
		$instance = swXmlTemplate::getXmlTemplateModelInstance();
		$this->inputRules['save'] = $instance->getInputRules(swModel::SCENARIO_DO_SAVE);
		$data = $this->ProcessInputData('save',true);
		if ($data === false) {return false;}
		$this->load->library('swEvnXml');
		$data['XmlTemplate_HtmlTemplate'] = swEvnXml::cleaningHtml($data['XmlTemplate_HtmlTemplate'], array(
			'withSpecChars' => 1,
			'commentWithoutTag' => 1,
			'commentWithoutExclamation' => 1,
			'commentWithIf' => 1,
			//'styles' => 1,
			'styleMso' => 1,
			'userLocalFiles' => 1,
		)); // #52118
		if (empty($data['XmlTemplate_id'])) {
			$data['scenario'] = 'create';
			$data['XmlTemplate_Caption'] = 'Новый шаблон';
			$this->load->library('swXmlTemplateSettings');
			$data['printsettings'] = swXmlTemplateSettings::getJsonFromArr(array());
		} else {
			$data['scenario'] = 'update';
		}
		swXmlTemplate::processingData($data, $data['pmUser_id']);
		$data['htmltemplate'] = $data['XmlTemplate_HtmlTemplate'];
		$data['xmldata'] = $data['XmlTemplate_Data'];
		// очищаем $data
		if (array_key_exists('Lpu_id', $data)) {
			unset($data['Lpu_id']);
		}
		if (array_key_exists('LpuSection_id', $data)) {
			unset($data['LpuSection_id']);
		}
		$response = $instance->doSave($data);
		$this->ProcessModelSave($response,true)->ReturnData();
		return true;
	}

	/**
	 * Сохранение настроек шаблона из формы редактирования
	 * На выходе: JSON-строка
	 * Используется формами:
	 * /jscore/Forms/Common/swXmlTemplateSettingsEditWindow.js
	 */
	function saveSettings()
	{
		$this->load->library('swXmlTemplate');
		$instance = swXmlTemplate::getXmlTemplateModelInstance();
		$this->inputRules['saveSettings'] = $instance->getInputRules('saveSettings');
		$data = $this->ProcessInputData('saveSettings',true);
		if ($data === false) {return false;}
		$this->load->library('swXmlTemplateSettings');
		$data['printsettings'] = swXmlTemplateSettings::getJsonFromArr($data);
		$data['scenario'] = 'saveSettings';
		// очищаем $data
		if (array_key_exists('Lpu_id', $data)) {
			unset($data['Lpu_id']);
		}
		if (array_key_exists('LpuSection_id', $data)) {
			unset($data['LpuSection_id']);
		}
		$response = $instance->doSave($data);
		$this->ProcessModelSave($response,true)->ReturnData();
		return true;
	}

	/**
	 * Чтение данных шаблона для формы редактирования
	 * На выходе: JSON-строка
	 * Используется формами:
	 * /jscore/Forms/Common/swXmlTemplateEditWindow.js
	 */
	function loadForm()
	{
		$this->load->model('XmlTemplateBase_model');
		$this->inputRules['loadForm'] = $this->XmlTemplateBase_model->getInputRules(swModel::SCENARIO_LOAD_EDIT_FORM);
		$data = $this->ProcessInputData('loadForm', true);
		if ($data === false) {return false;}
		try {
			$response = $this->XmlTemplateBase_model->doLoadEditForm($data);
			$this->ProcessModelList($response, true, false, 'При запросе возникла ошибка', function ($row, $controller) {
				// получаем HTML-шаблон
					$row['XmlTemplate_HtmlTemplate'] = swXmlTemplate::getHtmlTemplate($row);
				/*
				 Если будет редактирование настроек и шаблона в одной форме
				if (isset($row['XmlTemplate_Settings'])) {
					$controller->load->library('swXmlTemplateSettings');
					$settings = swXmlTemplateSettings::getArrFromJson($row['XmlTemplate_Settings']);
					if(empty($settings))
					{
						//если неправильный формат строки, то получим данные по умолчанию
						$settings = swXmlTemplateSettings::getArrSettingsDefault();
					}
					$row = array_merge($row,$settings);
					unset($row['XmlTemplate_Settings']);
				}*/
				return $row;
			})->ReturnData();
		} catch (Exception $e) {
			$this->OutData = array(
				'success' => false,
				'Error_Msg' => toUtf($e->getMessage())
			);
			$this->ReturnData();
			return false;
		}
		return true;
	}

	/**
	 * Чтение настроек шаблона для формы редактирования
	 * На выходе: JSON-строка
	 * Используется формами:
	 * /jscore/Forms/Common/swXmlTemplateSettingsEditWindow.js
	 */
	function getSettings()
	{
		$this->load->model('XmlTemplateBase_model');
		$this->load->library('swXmlTemplateSettings');
		$this->inputRules['getSettings'] = $this->XmlTemplateBase_model->getInputRules(swModel::SCENARIO_LOAD_EDIT_FORM);
		$data = $this->ProcessInputData('getSettings', true);
		if ($data === false) {return false;}
		$response = $this->XmlTemplateBase_model->doLoadEditForm($data);
		$this->ProcessModelList($response, true, false, 'При запросе возникла ошибка', function ($row, $controller) {
			if (isset($row['XmlTemplate_Settings'])) {
				$settings = swXmlTemplateSettings::getArrFromJson($row['XmlTemplate_Settings']);
				if (empty($settings)) {
					//если неправильный формат строки, то получим данные по умолчанию
					$settings = swXmlTemplateSettings::getArrSettingsDefault();
				}
				$row = array_merge($row,$settings);
				unset($row['XmlTemplate_Settings']);
			}
			return $row;
		})->ReturnData();
		return true;
	}

	/**
	 * Функция предварительного просмотра шаблона
	 * На выходе: JSON-строка
	 * Используется: формa /jscore/Forms/Common/swTemplSearchWindow.js
	 */
	function preview()
	{
		$this->load->model('XmlTemplateBase_model');
		$this->inputRules['preview'] = $this->XmlTemplateBase_model->getInputRules(swModel::SCENARIO_LOAD_EDIT_FORM);
		$this->inputRules['preview']['Evn_id'] = array(
			'field' => 'Evn_id',
			'label' => 'Идентификатор события',
			'rules' => 'trim',
			'type' => 'id'
		);
		$this->inputRules['preview']['EvnXml_id'] = array(
			'field' => 'EvnXml_id',
			'label' => 'Идентификатор документа',
			'rules' => 'trim',
			'type' => 'id'
		);
		$data = $this->ProcessInputData('preview', true);
		if ($data === false) { return false; }

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
			if ( !empty($data['Evn_id']) ) {
				$output[0]['XmlTemplate_HtmlTemplate'] = swMarker::processingTextWithMarkers($output[0]['XmlTemplate_HtmlTemplate'], $data['Evn_id'], array(
					'isPrint'=>true, 'EvnXml_id' => $data['EvnXml_id'],
				));
			}
			$this->load->library('swEvnXml');
			$output[0]['XmlTemplate_HtmlTemplate'] = swEvnXml::cleaningHtml($output[0]['XmlTemplate_HtmlTemplate'], array(
				'userLocalFiles' => 1,
			)); // #52118
		} catch (Exception $e) {
			$output[0]['XmlTemplate_HtmlTemplate'] = $e->getCode() . ' '. $e->getMessage();
		}
		$this->ProcessModelList($output, true, true)->ReturnData();
		return true;
	}

	/**
	 * Чтение списка шаблонов и папок для грида,
	 * из которого производится редактирование и выбор шаблонов для документа
	 * На выходе: JSON-строка
	 * Используется: формa /jscore/Forms/Common/swTemplSearchWindow.js
	 */
	function loadGrid()
	{
		$this->load->model('XmlTemplateBase_model');
		$this->inputRules['loadGrid'] = $this->XmlTemplateBase_model->getInputRules(swModel::SCENARIO_LOAD_GRID);
		$data = $this->ProcessInputData('loadGrid', true);
		if ($data) {
			$response = $this->XmlTemplateBase_model->loadGrid($data);
			$this->ProcessModelMultiList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Чтение списка шаблонов для грида печатных форм
	 * из которого производится редактирование и выбор шаблонов для документа
	 * На выходе: JSON-строка
	 * Используется: формa /jscore/Forms/Common/swEvnDirectionFreeForm.js
	 */
	function loadGridForPrint()
	{
		$this->load->model('XmlTemplateBase_model');
		$this->inputRules['loadGridForPrint'] = $this->XmlTemplateBase_model->getInputRules(swModel::SCENARIO_LOAD_GRID);
		$data = $this->ProcessInputData('loadGridForPrint', true);
		if ($data) {
			$response = $this->XmlTemplateBase_model->loadGridForPrint($data);
			$this->ProcessModelMultiList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Удаление шаблона
	 * На выходе: JSON-строка
	 * Используется: формa /jscore/Forms/Common/swTemplSearchWindow.js
	 */
	public function destroy()
	{
		$this->load->library('swXmlTemplate');
		$instance = swXmlTemplate::getXmlTemplateModelInstance();
		$this->inputRules['destroy'] = $instance->getInputRules(swModel::SCENARIO_DELETE);
		$data = $this->ProcessInputData('destroy',true);
		if ($data === false) {return false;}
		$response = $instance->doDelete($data);
		$this->ProcessModelSave($response,true)->ReturnData();
		return true;
	}

	/**
	 * Получение списка наиболее часто используемых шаблонов
	 * На выходе: JSON-строка
	 * Используется формами:
	 * /jscore/Forms/Polka/swTemplatesEvnVizitPLEditWindow.js
	 * /jscore/Forms/Polka/swEvnVizitPLDispSomeAdultEditWindow.js
	 */
	public function getFavorites()
	{
		$data = $this->ProcessInputData('getFavorites',true);
		if ($data === false) {return false;}
		$this->load->model('XmlTemplateFavorites_model', 'XmlTemplateFavorites_model');
		$response = $this->XmlTemplateFavorites_model->loadList($data);
		$this->ProcessModelList($response,true,false,'Ваш список избранных шаблонов пуст')->ReturnData();
		return true;
	}

	/**
	 * Возвращает для комбобокса список категорий,
	 * которые могут быть выбраны для папок и шаблонов документов
	 * На выходе: JSON-строка
	 * Используется формами:
	 * /jscore/Forms/Common/swTemplSearchWindow.js
	 * /jscore/Forms/Common/swXmlTemplateCatEditWindow.js
	 * /jscore/Forms/Common/swXmlTemplateSettingsEditWindow.js
	 * /jscore/Forms/Common/swMarkerSearchWindow.js
	 * /jscore/Forms/Common/swMarkerEditWindow.js
	 * @return bool
	 */
	function loadEvnClassList()
	{
		// Используется базовая модель шаблонов для документов
		$this->load->model('XmlTemplateBase_model');
		$this->inputRules['loadEvnClassList'] = array(
			array(
				'field' => 'withBase',
				'label' => 'Признак необходимости базовых событий',
				'rules' => '',
				'type' => 'id'
			),
		);
		$data = $this->ProcessInputData('loadEvnClassList', true);
		if (false === $data) { return false; }
		$response = $this->XmlTemplateBase_model->loadEvnClassList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Выборка шаблонов для комбобокса sw.Promed.SwTemplatesParCombo
	 */
	function loadCombo()
	{
		$data = $this->ProcessInputData('loadCombo', true);
		if ($data === false) {return false;}
		$this->load->model('XmlTemplateBase_model');
		$response = $this->XmlTemplateBase_model->loadCombo($data);
		$this->ProcessModelList($response,true,true)->ReturnData();
		return true;
	}

	/**
	 * Выборка для грида услуги шаблона
	 */
	function loadXmlTemplateLinkList()
	{
		$data = $this->ProcessInputData('loadXmlTemplateLinkList', true);
		if ($data === false) {return false;}
		$this->load->model('XmlTemplateBase_model');
		$response = $this->XmlTemplateBase_model->loadXmlTemplateLinkList($data);
		$this->ProcessModelList($response,true,true)->ReturnData();
		return true;
	}
}
