<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @copyright    Copyright (c) 2009-2013 Swan Ltd.
 * @version      06.2013
 */

/**
 * EvnXmlConvert - контроллер для конвертации Xml-документов
 *
 * Доступ к функционалу только у суперадминистратора
 *
 * @package      XmlTemplate
 * @author       Пермяков Александр Михайлович
 *
 * @property EvnXmlConvert_model dbModel
 */

class EvnXmlConvert extends swController {

	function __construct() {
		parent::__construct();
		if (!isSuperadmin()) {
			throw new Exception('Запрещено');
		}
		$this->load->database();
		$this->load->model('EvnXmlConvert_model', 'dbModel');
		$this->inputRules = array(
			'index' => array(
				array(
					'field' => 'action',
					'label' => 'Метод исправления',
					'rules' => 'trim',
					'type' => 'string'
				),
			),
		);
	}

	/**
	 *  Запрос сводной информации
	 *  На выходе: HTML-строка
	 * @param string $message
	 * @return void
	 */
	private function showInfo($message) {
		$info = $this->dbModel->index();
		$info[0]['message'] = $message;
		$this->load->library('parser');
		$this->parser->parse('form_fix_xml_template', $info[0]);
	}

	/**
	 *  Запрос сводной информации или выполнение выбранного действия
	 *  На выходе: HTML-строка
	 *
	 * @return bool
	 */
	function index() {
		$data = $this->ProcessInputData('index', true);
		if (!$data) {
			return false;
		}
		if (empty($data['action'])) {
			$this->showInfo(null);
			return true;
		}
		try {
			switch($data['action']) {
				case 'fixXmlTemplateNotCorrectDeleted':
					$result = $this->dbModel->fixXmlTemplateNotCorrectDeleted();
					$message = ($result)?'Запрос выполнен успешно':'Ошибка выполнения запроса';
					break;
				case 'fixXmlTemplateWithUndefinedType':
					$result = $this->dbModel->fixXmlTemplateWithUndefinedType();
					$message = ($result)?'Запрос выполнен успешно':'Ошибка выполнения запроса';
					break;
				case 'fixEvnXmlWithUndefinedXmlTemplateType':
					$result = $this->dbModel->fixEvnXmlWithUndefinedXmlTemplateType();
					$message = ($result)?'Запрос выполнен успешно':'Ошибка выполнения запроса';
					break;
				case 'fixXmlType':
					$result = $this->dbModel->fixXmlType();
					$message = ($result)?'Запрос выполнен успешно':'Ошибка выполнения запроса';
					break;
				case 'copyXmlTemplateDataToEvnXml':
					$result = $this->dbModel->copyXmlTemplateDataToEvnXml();
					$message = ($result)?'Запрос выполнен успешно':'Ошибка выполнения запроса';
					break;
				case 'autoConvertXmlTemplate':
					$result = $this->dbModel->autoConvertXmlTemplate();
					$message = ($result)?'Запрос выполнен успешно':'Ошибка выполнения запроса';
					break;
				default:
					$message = 'Данный метод не поддерживается';
					break;
			}
		} catch (Exception $e) {
			$message = $e->getMessage();
		}
		$this->showInfo($message);
		return true;
	}
}
