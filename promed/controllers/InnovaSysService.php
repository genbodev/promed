<?php defined('BASEPATH') or die ('No direct script access allowed');

/**
 * MedService - контроллер работы со службами
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 *
 */
/**
 * @property InnovaSysService_Model $dbmodel
 */
class InnovaSysService extends swController
{
	/**
	 *constructor
	 */
	function __construct()
	{
		parent::__construct();

		if ($this->usePostgreLis) {
			$this->load->swapi('lis');
		} else {
			$this->load->database();
			$this->load->model('InnovaSysService_Model', 'dbmodel');
		}

		$this->inputRules = array(
			'makeRequests' => array(
				array('field' => 'EvnLabRequests', 'label' => 'ид заявок', 'rules' => '', 'type' => 'json_array')
			),
			'makeRequest' => array(
				array('field' => 'EvnLabRequest_id', 'label' => 'ид заявки', 'rules' => '', 'type' => 'string')
			),
			'makeUnloadRequest' => array(
				array('field' => 'EvnLabRequest_id', 'label' => 'ид заявки', 'rules' => '', 'type' => 'string')
			),
			'makeUnloadRequests' => array(
				array('field' => 'EvnLabRequest_ids', 'label' => 'ид заявок', 'rules' => '', 'type' => 'json_array')
			),
			'parseRequest' => array(
				array('field' => 'xml', 'label' => 'путь', 'rules' => '', 'type' => 'string')
			),
			'checkForUpdates' => array(
			)
		);
	}

	/**
	 * создание одного запроса на выгрузку(из формы редактирвоания заявки)
	 */
	function makeUnloadRequest()
	{
		$data = $this->ProcessInputData('makeUnloadRequest', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$this->lis->POST('InnovaSysService/makeUnloadRequest', $data);
		} else {
			$xmls = $this->dbmodel->makeUnloadRequest($data);
			foreach($xmls as $xml) {
				$this->sendXML($xml->RequestFilter, $xml->RequestFilter->RequestCodes->String);
			}
		}
	}

	/**
	 * создание многих запросов на выгрузку(из арм лаборанта)
	 */
	function makeUnloadRequests()
	{
		$data = $this->ProcessInputData('makeUnloadRequests', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$this->lis->POST('InnovaSysService/makeUnloadRequests', $data);
		} else {
			foreach ($data['EvnLabRequest_ids'] as $id) {
				$request = array(
					'EvnLabRequest_id' => $id
				);
				$xmls = $this->dbmodel->makeUnloadRequest($request);
				foreach($xmls as $xml) {
					$this->sendXML($xml->RequestFilter, $xml->RequestFilter->RequestCodes->String);
				}
			}
		}
	}

	/**
	 * подготовка к отправке многих новых заявок(из арм лаборанта)
	 */
	function makeRequests()
	{
		$data = $this->ProcessInputData('makeRequests', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$this->lis->POST('InnovaSysService/makeRequests', $data);
		} else {
			foreach ($data['EvnLabRequests'] as $id) {
				$data['EvnLabRequest_id'] = $id;
				$xmls = $this->dbmodel->makeRequest($data);
				foreach($xmls as $xml) {
					$this->sendXML($xml->Request, $xml->Request->RequestCode);
				}
			}
		}
	}

	/**
	 * подготовка к отправке одной новой заявки(из формы редактирвоания заявки)
	 */
	function makeRequest()
	{
		$data = $this->ProcessInputData('makeRequest', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$this->lis->POST('InnovaSysService/makeRequest', $data);
		} else {
			$xmls = $this->dbmodel->makeRequest($data);
			foreach($xmls as $xml) {
				$this->sendXML($xml->Request, $xml->Request->RequestCode);
			}
		}
	}

	/**
	 * считывание нового результата по заявке от ЛИС
	 */
	function parseRequest()
	{
		$data = $this->ProcessInputData('parseRequest', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$this->lis->POST('InnovaSysService/parseRequest', $data);
		} else {
			$this->dbmodel->parseRequest($data);
		}
	}

	/**
	 * скрипт автоматической проверки обновлений папки ЛИС-МИС
	 */
	function checkForUpdates($dirName = LIS_MIS_FOLDER)
	{
		$data = $this->ProcessInputData('checkForUpdates', true);
		if ($data === false) return;

		if ($this->usePostgreLis) {
			$data['dirName'] = LIS_MIS_FOLDER;
			$response = $this->lis->POST('InnovaSysService/checkForUpdates', $data);
			$this->ProcessRestResponse($response)->ReturnData();
		} else {
			if (is_dir($dirName)) {
				$files = scandir($dirName, SCANDIR_SORT_DESCENDING);

				foreach ($files as $file) {
					if (is_dir($dirName.'/'.$file) && !in_array($file, ['.', '..'])) {
						$this->checkForUpdates($dirName.'/'.$file);
					} elseif (mb_substr($file, -4) == '.xml') {
						$data['xml'] = $dirName.'/'.$file;
						$this->dbmodel->parseRequest($data);
					}
				}

				if ($dirName === LIS_MIS_FOLDER) {
					$this->ReturnData(array('success' => true));
				}
			} else {
				$this->ReturnError('Путь в LIS_MIS_FOLDER не являятеся папкой: '.$dirName);
			}
		}
	}

	/**
	 * отправка файла в папку МИС-ЛИС
	 */
	function sendXML($data, $name)
	{
		$xml = new DOMDocument('1.0', 'utf-16');
		$xmlData = dom_import_simplexml($data);
		$xmlData = $xml->importNode($xmlData, true);
		$xmlData = $xml->appendChild($xmlData);
		$xml = $xml->saveXML();

		if (is_dir(MIS_LIS_FOLDER)) {
			file_put_contents(MIS_LIS_FOLDER . $name . '.xml', $xml);
			return true;
		} else {
			return false;
		}
	}
}
