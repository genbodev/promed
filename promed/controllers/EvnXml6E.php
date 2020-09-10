<?php

/**
 * Class EvnXml6E
 *
 * @property EvnXml6E_model $dbmodel
 */
class EvnXml6E extends swController
{
	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model('EvnXml6E_model', 'dbmodel');

		$this->inputRules = array(
			'saveEvnXml' => array(
				array('field' => 'EvnXml_id', 'label' => 'Идентификатор документа', 'rules' => '', 'type' => 'id'),
				array('field' => 'Evn_id', 'label' => 'Идентификатор события', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'XmlType_id', 'label' => 'Идентификатор типа документа', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'EvnXml_Data', 'label' => 'Данные документа', 'rules' => 'required|trim', 'type' => 'string'),
				array('field' => 'EvnXml_DataSettings', 'label' => 'Настройки отображения данных документа', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'XmlTemplate_id', 'label' => 'Идентификатор шаблона', 'rules' => '', 'type' => 'id'),
				array('field' => 'XmlTemplate_HtmlTemplate', 'label' => 'Содержание документа', 'rules' => 'trim', 'type' => 'string'),
			),
			'createEmptyEvnXml' => array(
				array('field' => 'Evn_id', 'label' => 'Идентификатор события', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'XmlType_id', 'label' => 'Идентификатор типа документа', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'XmlTemplate_id', 'label' => 'Идентификатор шаблона', 'rules' => '', 'type' => 'id'),
			),
			'printEvnXml' => array(
				array('field' => 'EvnXml_id', 'label' => 'Идентификатор документа', 'rules' => '', 'type' => 'id'),
				array('field' => 'Evn_id', 'label' => 'Идентификатор события', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'XmlType_id', 'label' => 'Идентификатор типа документа', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'EvnXml_Data', 'label' => 'Данные документа', 'rules' => 'required', 'type' => 'string'),
				array('field' => 'EvnXml_DataSettings', 'label' => 'Настройки отображения данных документа', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'XmlTemplate_id', 'label' => 'Идентификатор шаблона', 'rules' => '', 'type' => 'id'),
				array('field' => 'XmlTemplate_HtmlTemplate', 'label' => 'Содержание документа', 'rules' => 'trim', 'type' => 'string'),
				array('field' => 'printMode', 'label' => 'Режим печати', 'rules' => 'trim', 'type' => 'string'),
			),
			'loadEvnXmlList' => array(
				array('field' => 'Evn_id', 'label' => 'Идентификатор текущего события', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'EvnXml_insDT', 'label' => 'Период', 'rules' => '','type' => 'daterange'),
				array('field' => 'Diag_id', 'label' => 'Диагноз', 'rules' => '', 'type' => 'id'),
				array('field' => 'cnt', 'label' => '', 'rules' => '', 'type' => 'id'),
			),
			'copyEvnXml' => array(
				array('field' => 'Evn_id', 'label' => 'Идентификатор события', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'XmlType_id', 'label' => 'Идентификатор типа документа', 'rules' => 'required', 'type' => 'id'),
				array('field' => 'XmlTemplate_id', 'label' => 'Идентификатор шаблона', 'rules' => '', 'type' => 'id'),
				array('field' => 'EvnXml_id', 'label' => 'Идентификатор документа', 'rules' => '', 'type' => 'id'),
			)
		);
	}

	/**
	 * @return bool
	 */
	function saveEvnXml() {
		$data = $this->ProcessInputData('saveEvnXml', true);
		if ($data === false) return false;

		$response = $this->dbmodel->saveEvnXml($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * @return bool
	 */
	function createEmptyEvnXml() {
		$data = $this->ProcessInputData('createEmptyEvnXml', true);
		if ($data === false) return false;

		$response = $this->dbmodel->createEmptyEvnXml($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * @return bool
	 */
	function printEvnXml() {
		$data = $this->ProcessInputData('printEvnXml', true);
		if ($data === false) return false;

		$this->load->model('XmlTemplate6E_model', 'xtmodel');
		$this->load->library('swEvnXml');

		try {
			$resp = $this->xtmodel->getParamsByXmlTemplateOrEvnXml($data);
			if (!$this->xtmodel->isSuccessful($resp)) {
				throw new Exception($resp[0]['Error_Msg']);
			}

			swEvnXml::doPrint(
				$resp[0]['params'],
				getRegionNick(),
				false,
				false,
				false,
				$data['printMode'] == 'bottom'
			);
		} catch (Exception $e) {
			echo $e->getMessage();
			return false;
		}
	}

	/**
	 * @return bool
	 */
	function loadEvnXmlList() {
		$data = $this->ProcessInputData('loadEvnXmlList', true);
		if ($data === false) return false;

		$response = $this->dbmodel->loadEvnXmlList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * @return bool
	 */
	function copyEvnXml() {
		$data = $this->ProcessInputData('copyEvnXml', true);
		if ($data === false) return false;

		$response = $this->dbmodel->copyEvnXml($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}
}