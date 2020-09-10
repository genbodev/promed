<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Glossary - контроллер работы с глоссарием 
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009-2011 Swan Ltd.
* @author       Пермяков Александр
* @version      июнь 2011 года
*/

class Glossary extends swController {

	/**
	 * Description
	 */
	function __construct() {
		parent::__construct();

		$this->inputRules = array(
			'getRecord' => array(
				array('field' => 'Glossary_id','label' => 'Идентификатор термина глоссария','rules' => 'required','type' => 'id')
			),
			'deleteRecord' => array(
				array('field' => 'Glossary_id','label' => 'Идентификатор термина глоссария','rules' => 'required','type' => 'id')
			),
			'getSynonymList' => array(
				array('field' => 'GlossarySynonym_id','label' => 'Идентификатор синонима','rules' => '','type' => 'id'),
				array('field' => 'Glossary_id','label' => 'Идентификатор термина','rules' => '','type' => 'id'),
				array('field' => 'GlossaryTagType_id','label' => 'Идентификатор контекста','rules' => '','type' => 'id'),
				array('field' => 'pmUser_did','label' => 'Идентификатор пользователя-владельца словаря','rules' => '','type' => 'id'),
				array('field' => 'GlossarySynonym_Name','label' => 'Синоним','rules' => 'ban_percent|trim','type' => 'string')
			),
			'loadRecordGrid' => array(
				array('field' => 'Glossary_Word','label' => 'Термин','rules' => 'ban_percent|trim','type' => 'string'),
				array('field' => 'pmUser_did','label' => 'Идентификатор пользователя-владельца словаря','rules' => '','type' => 'id'),
				array('field' => 'GlossarySynonym_id','label' => 'Идентификатор синонима','rules' => '','type' => 'id'),
				array('field' => 'GlossaryTagType_id','label' => 'Идентификатор контекста','rules' => '','type' => 'id'),
				array('field' => 'GlossaryType_id','label' => 'Тип словаря','rules' => '','type' => 'id'),
				array('field' => 'start','default' => 0,'label' => 'Начальный номер записи','rules' => '','type' => 'int'),
				array('field' => 'limit','default' => 100,'label' => 'Количество возвращаемых записей','rules' => '','type' => 'int')
			),
			'getGlossaryTagTypeBySysNick' => array(
				array('field' => 'GlossaryTagType_SysNick','label' => 'GlossaryTagType_SysNick','rules' => 'required','type' => 'string')
			),
			'loadRecordStore' => array(
				array('field' => 'text','label' => 'Термин','rules' => 'ban_percent|trim|required','type' => 'string'),
				array('field' => 'isEnableBaseGlossary','label' => 'isEnableBaseGlossary','rules' => '','type' => 'id'),
				array('field' => 'isEnablePersGlossary','label' => 'isEnablePersGlossary','rules' => '','type' => 'id'),
				array('field' => 'isEnableContextSearch','label' => 'isEnableContextSearch','rules' => '','type' => 'id'),
				array('field' => 'GlossaryTagType_SysNick','label' => 'GlossaryTagType_SysNick','rules' => '','type' => 'string')
			),
			'loadSynonymMenu' => array(
				array('field' => 'Synonym_list','label' => 'Идентификаторы для поиска синонимов','rules' => 'required','type' => 'string'),
				array('field' => 'isEnableBaseGlossary','label' => 'isEnableBaseGlossary','rules' => '','type' => 'id'),
				array('field' => 'isEnablePersGlossary','label' => 'isEnablePersGlossary','rules' => '','type' => 'id')
			),
			'saveRecord' => array(
				array('field' => 'Glossary_id','default' => 0,'label' => 'Идентификатор термина','rules' => '','type' => 'id'),
				array('field' => 'Glossary_Word','label' => 'Термин','rules' => 'trim|required','type' => 'string'),
				array('field' => 'Glossary_Descr','default' => '','label' => 'Толкование термина','rules' => 'trim','type' => 'string'),
				array('field' => 'pmUser_did','label' => 'Идентификатор пользователя-владельца словаря','rules' => '','type' => 'id'),
				array('field' => 'GlossarySynonym_id','label' => 'Идентификатор синонима','rules' => '','type' => 'id'),
				array('field' => 'GlossaryTagType_id','label' => 'Идентификатор контекста','rules' => '','type' => 'id')
			)
		);
	}

	/**
	*  Функция чтения списка синонимов для меню глоссария
	*  На выходе: JSON-строка
	*/
	function loadSynonymMenu() {
		$data = $this->ProcessInputData('loadSynonymMenu', true);
		if ($data)
		{
			$this->load->database();
			$this->load->model('Glossary_model', 'Glossary_model');
			$response = $this->Glossary_model->loadSynonymMenu($data);
			$this->OutData = array();
			foreach ($response as $k => $sl) {
				$this->OutData[$k] = array();
				foreach ($sl as $row) {
					array_walk($row, 'ConvertFromWin1251ToUTF8');
					$this->OutData[$k][] = $row;
				}
			}
			$this->ReturnData();
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	*  Функция получения записи GlossaryTagType 
	*  На выходе: JSON-строка
	*/
	function getGlossaryTagTypeBySysNick() {
		$data = $this->ProcessInputData('getGlossaryTagTypeBySysNick', true);
		if ($data)
		{
			$this->load->database();
			$this->load->model('Glossary_model', 'Glossary_model');
			$response = $this->Glossary_model->getGlossaryTagTypeBySysNick($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		}
		else
		{
			return false;
		}
	}
	
	/**
	*  Функция чтения списка записей для меню глоссария
	*  На выходе: JSON-строка
	*/
	function loadRecordStore() {
		$data = $this->ProcessInputData('loadRecordStore', true);
		if ($data)
		{
			$this->load->database();
			$this->load->model('Glossary_model', 'Glossary_model');
			$response = $this->Glossary_model->loadRecordStore($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		}
		else
		{
			return false;
		}
	}
	
	/**
	*  Функция чтения списка записей для грида
	*  На выходе: JSON-строка
	*  Используется: форма swGlossarySearchWindow
	*/
	function loadRecordGrid() {
		$data = $this->ProcessInputData('loadRecordGrid', true);
		if ($data)
		{
			$this->load->database();
			$this->load->model('Glossary_model', 'Glossary_model');
			$response = $this->Glossary_model->loadRecordGrid($data);
			$this->ProcessModelMultiList($response, true, true)->ReturnData();
			return true;
		}
		else
		{
			return false;
		}
	}
	
	/**
	*  Функция чтения списка записей (для комбобокса, не больше 50)
	*  На выходе: JSON-строка
	*  Используется: формы с комбобоксом выбора синонима (swglossarysynonymcombo)
	*/
	function getSynonymList() {
		$data = $this->ProcessInputData('getSynonymList', true);
		if ($data)
		{
			$this->load->database();
			$this->load->model('Glossary_model', 'Glossary_model');
			$response = $this->Glossary_model->getSynonymList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	*  Функция сохранения одной записи
	*  На выходе: JSON-строка
	*  Используется: форма редактирования swGlossaryEditWindow
	*/
	function saveRecord() {
		$data = $this->ProcessInputData('saveRecord', true);
		if ($data)
		{
			$this->load->database();
			$this->load->model('Glossary_model', 'Glossary_model');
			// проверяем дублирование термина в базовом и личном словаре
			$response = $this->Glossary_model->checkDouble($data);
			if (is_array($response))
			{
				if (count($response) > 0)
				{
					$this->ReturnData(array('success' => false,'Error_Msg' => toUTF('Данный термин/фраза уже имеется в базовом или вашем личном словаре!'), 'Glossary_id' => $response[0]['Glossary_id'], 'GlossarySynonym_id' => $response[0]['GlossarySynonym_id'], 'pmUser_did' => $response[0]['pmUser_did']));
					return false;
				}
				else if($data['Glossary_id'] > 0 && empty($data['pmUser_did']) && !$this->Glossary_model->allowBaseEdit($data))
				{
					$this->ReturnData(array('success' => false,'Error_Msg' => toUTF('Вы не можете сохранить термин базового словаря!')));
					return false;
				}
				else
				{
					$response = $this->Glossary_model->saveRecord($data);
					$this->ProcessModelSave($response, true)->ReturnData();
					return true;
				}
			}
			else
			{
				$this->ReturnData(array('success' => false,'Error_Msg' => toUTF('Ошибка БД при проверке дублирования!')));
				return false;
			}
		}
		else
		{
			return false;
		}
	}

	/**
	*  Функция чтения одной записи
	*  На выходе: JSON-строка
	*  Используется: форма редактирования swGlossaryEditWindow
	*/
	function getRecord() {
		$data = $this->ProcessInputData('getRecord', true);
		if ($data)
		{
			$this->load->database();
			$this->load->model('Glossary_model', 'Glossary_model');
			$response = $this->Glossary_model->getRecord($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		}
		else
		{
			return false;
		}
	}
}
