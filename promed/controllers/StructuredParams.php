<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Diag - контроллер для работы со справочником диагназов
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			18.12.2013
 *
 * @property StructuredParams_model $dbmodel
 */

class StructuredParams extends swController {
	public $inputRules = array(
		'getStructuredParamsTreeBranch' => array(
			array(
				'field' => 'node',
				'label' => 'node',
				'rules' => 'required',
				'type' => 'string'
			)
		),
		'getStructuredParamsGridBranch' => array(
			array(
				'field' => 'StructuredParams_pid',
				'label' => 'Идентификатор родительского уровня',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'StructuredParams_Name',
				'label' => 'Наименование параметра',
				'rules' => '',
				'type' => 'string'
			)
		),
		'getStructuredParam' => array(
			array(
				'field' => 'StructuredParams_id',
				'label' => 'Идентификатор параметра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'StructuredParams_pid',
				'label' => 'Идентификатор параметра',
				'rules' => '',
				'type' => 'string'
			)
		),	
		'deleteStructuredParamsType'=>array(
			array(
				'field' => 'object',
				'label' => 'Идентификатор параметра',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'Main_id',
				'label' => 'Тип параметра',
				'rules' => '',
				'type' => 'id'
			)
		),
		'addStructuredParamsType'=>array(
			array(
				'field' => 'object',
				'label' => 'Идентификатор параметра',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'Diag_id',
				'label' => 'Тип параметра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedSpecOms_id',
				'label' => 'Тип параметра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'UslugaComplex_id',
				'label' => 'Тип параметра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'AgeFrom',
				'label' => 'Наименование параметра',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'AgeTo',
				'label' => 'Наименование параметра',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'StructuredParams_id',
				'label' => 'Тип параметра',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'saveStructuredParam' => array(
			array(
				'field' => 'records',
				'label' => 'Список идентификаторов параметров',
				'rules' => '',
				'type' => 'array'
			),
			array(
				'field' => 'StructuredParams_Name',
				'label' => 'Наименование параметра',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'XmlDataSections',
				'label' => 'Разделы документа',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'XmlTypes',
				'label' => 'Типы документа',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'StructuredParams_SysNick',
				'label' => 'Метка параметра',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'StructuredParamsType_id',
				'label' => 'Тип параметра',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'StructuredParams_pid',
				'label' => 'Идентификатор родительского уровня',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'StructuredParams_rid',
				'label' => 'Идентификатор самого верхнего уровня',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'StructuredParamsPrintType_id',
				'label' => 'Тип печати параметра',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Sex_id',
				'label' => 'Пол',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'MedSpecOms_Text',
				'label' => 'Медицинские специальности',
				'rules' => '',
				'type' => 'string'
			),
		),
		'saveStructuredParamsGroup' => array(
			
			array(
				'field' => 'StructuredParams_Name',
				'label' => 'Наименование параметра',
				'rules' => 'dropifnotcome',
				'type' => 'string'
			),
			array(
				'field' => 'StructuredParams_SysNick',
				'label' => 'Метка параметра',
				'rules' => 'dropifnotcome',
				'type' => 'string'
			),
			array(
				'field' => 'StructuredParamsType_id',
				'label' => 'Тип параметра',
				'rules' => 'dropifnotcome',
				'type' => 'id'
			),
			array(
				'field' => 'StructuredParamsPrintType_id',
				'label' => 'Тип печати параметра',
				'rules' => 'dropifnotcome',
				'type' => 'id'
			),
			array(
				'field' => 'Sex_id',
				'label' => 'Пол',
				'rules' => 'dropifnotcome',
				'type' => 'id'
			),
			array(
				'field' => 'MedSpecOms_Text',
				'label' => 'Медицинские специальности',
				'rules' => 'dropifnotcome',
				'type' => 'string'
			),
		),
		'addStructuredParam' => array(
			array(
				'field' => 'StructuredParams_pid',
				'label' => 'Идентификатор родительского уровня',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'XmlDataSections',
				'label' => 'Разделы документа',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'XmlTypes',
				'label' => 'Типы документа',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'StructuredParams_id',
				'label' => 'Идентификатор родительского уровня',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'StructuredParams_rid',
				'label' => 'Идентификатор самого верхнего уровня',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'StructuredParams_Name',
				'label' => 'Наименование параметра',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'StructuredParams_SysNick',
				'label' => 'Метка параметра',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'StructuredParamsType_id',
				'label' => 'Тип параметра',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'StructuredParamsPrintType_id',
				'label' => 'Тип печати параметра',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Sex_id',
				'label' => 'Пол',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'MedSpecOms_Text',
				'label' => 'Медицинские специальности',
				'rules' => '',
				'type' => 'string'
			),
		),
		'getStructuredParamsByType'=>array(
			array(
				'field' => 'object',
				'label' => 'json массив изменяющихся параметров',
				'rules' => '',
				'type' => 'string'
			),array(
				'field' => 'StructuredParams_id',
				'label' => 'json массив изменяющихся параметров',
				'rules' => 'required',
				'type' => 'id'
			),
		),
		'saveStructuredParamsInline' => array(
			array(
				'field' => 'data',
				'label' => 'json массив изменяющихся параметров',
				'rules' => '',
				'type' => 'string'
			),
		),
		'deleteStructuredParam' => array(
			array(
				'field' => 'records',
				'label' => 'Список идентификаторов параметров',
				'rules' => '',
				'type' => 'array'
			)
		),
		'moveStructuredParam' => array(
			array(
				'field' => 'StructuredParams_id',
				'label' => 'Идентификатор параметра',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'position',
				'label' => 'Изменение позиции параметра',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'position_old',
				'label' => 'Текущая позиция параметра',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'pid',
				'label' => 'Идентификатор родительской ветви, задается при перемещении в другую ветвь',
				'rules' => '',
				'type' => 'string'
			),
		),
		'getStructuredParamsTree' => array (
			array('field' => 'branch','label' => 'Ветка дерева структурированных параметров','rules' => '','type' => 'string'),
			array('field' => 'Person_id','label' => 'Идентификатор текущего человека','rules' => '','type' => 'id'),
			array('field' => 'Person_Birthdate','label' => 'Дата рождения текущего человека','rules' => '','type' => 'date'),
			array('field' => 'EvnClass_id','label' => 'Идентификатор события','rules' => '','type' => 'id'),
			array('field' => 'EvnXml_id','label' => 'Идентификатор','rules' => '','type' => 'id'),
			array('field' => 'Evn_id','label' => 'Идентификатор','rules' => '','type' => 'id')
		),
		"getStructuredParamsExtJS6" => [
			["field" => "StructuredParams_pid", "label" => "Идентификатор родительского уровня", "rules" => "", "type" => "id"],
			["field" => "StructuredParams_Name", "label" => "Наименование параметра", "rules" => "", "type" => "string"]
		],
		"sendStructuredParamData" => [
			["field" => "data", "label" => "", "rules" => "", "type" => "string"],
		],
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('StructuredParams_model', 'dbmodel');
	}

	/**
	 * Возвращает данные для дерева структурированных параметров
	 * @return bool
	 */
	function getStructuredParamsTreeBranch()
	{
		$data = $this->ProcessInputData('getStructuredParamsTreeBranch',true);
		if ($data === false) {return false;}

		$response = $this->dbmodel->getStructuredParamsTreeBranch($data);

		$this->ProcessModelList($response,true,true)->ReturnData();
		return true;
	}
	/**
	 *
	 * @return type 
	 */
	function getStructuredParamsByType(){
		$data = $this->ProcessInputData('getStructuredParamsByType',true);
		if ($data === false) {return false;}

		$response = $this->dbmodel->getStructuredParamsByType($data);

		$this->ProcessModelMultiList($response,true,true)->ReturnData();
		return true;
	}
	/**
	 * Возвращает список структурированных параметров для грида
	 * @return bool
	 */
	function getStructuredParamsGridBranch()
	{
		$data = $this->ProcessInputData('getStructuredParamsGridBranch',true);
		if ($data === false) {return false;}

		$response = $this->dbmodel->getStructuredParamsGridBranch($data);

		$this->ProcessModelMultiList($response,true,true)->ReturnData();
		return true;
	}

	/**
	 * Возвращает список структурированных параметров для грида (ExtJS6)
	 * @return bool
	 */
	function getStructuredParamsExtJS6()
	{
		$data = $this->ProcessInputData("getStructuredParamsExtJS6", true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->getStructuredParamsExtJS6($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Отправка данных на сервер из Помощника (ExtJS6)
	 * @throws Exception
	 */
	function sendStructuredParamData()
	{
		$data = $this->ProcessInputData("sendStructuredParamData", true);
		$response = $this->dbmodel->sendStructuredParamData($data);
		echo(@json_encode($response));
	}

	/**
	 * Получение информации по одному структурированному параметру
	 */
	function getStructuredParam() {
		$data = $this->ProcessInputData('getStructuredParam',true);
		if ($data === false) {return false;}

		$response = $this->dbmodel->getStructuredParam($data);

		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
	/**
	 *
	 * @return type 
	 */
	function addStructuredParamsType() {
		$data = $this->ProcessInputData('addStructuredParamsType', true);
		
		if ($data === false) {return false;}

		$response = $this->dbmodel->addStructuredParamsType($data);

		$this->ProcessModelSave($response, true,  'При запросе возникла ошибка.')->ReturnData();
		return true;
	}
	/**
	 * Сохранение одного или нескольких параметров
	 */
	function saveStructuredParams() {
		$data = $this->ProcessInputData('addStructuredParam', true);
		/*$records = isset($_POST['records']) ? $_POST['records'] : null;

		if ( !isset($records) ) { // новый параметр 
			$data = $this->ProcessInputData('addStructuredParam', true);
		} else  if ( count($records) == 1 ) { // один параметр
			$data = $this->ProcessInputData('saveStructuredParam', true);
		} else { // редактирование группы параметров
			$data = $this->ProcessInputData('saveStructuredParamsGroup', true);
		}*/
		
		if ($data === false) {return false;}

		$response = $this->dbmodel->saveStructuredParams($data);

		$this->ProcessModelSave($response, true,  'При запросе возникла ошибка.')->ReturnData();
		return true;
	}
	
	/**
	 * Сохранение параметров при редактировании в гриде
	 */
	function saveStructuredParamsInline() {
		$data = $this->ProcessInputData('saveStructuredParamsInline', true);
		
		if ($data === false) {return false;}
		
		try {
			$params = json_decode(toUTF($data['data']), true);
		}
		catch (Exception $e) {
			$this->OutData = array(
				'success' => false,
				'Error_Msg' => toUtf('Неверно сформированные данные.')
			);
			return;
		}
		
		if (is_array($params) && count($params) > 0 ) {
			$data['records'] = $params;
			$response = $this->dbmodel->saveStructuredParamsInline($data);
			$this->ProcessModelSave($response, true,  'При запросе возникла ошибка.')->ReturnData();
		} else {
			$this->OutData = array(
				'success' => false,
				'Error_Msg' => toUtf('Неверно сформированные данные.')
			);
		}
		
		return true;
	}
	
		
	/**
	 *
	 * @return type 
	 */
	function deleteStructuredParamsType() {
		$data = $this->ProcessInputData('deleteStructuredParamsType',true);
		if ($data === false) {return false;}

		$response = $this->dbmodel->deleteStructuredParamsType($data);

		$this->ProcessModelSave($response, true,  'При запросе возникла ошибка.')->ReturnData();
		return true;
	}
	/**
	 * Удаление структурированного параметра
	 */
	function deleteStructuredParam() {
		$data = $this->ProcessInputData('deleteStructuredParam',true);
		if ($data === false) {return false;}

		$response = $this->dbmodel->deleteStructuredParam($data);

		$this->ProcessModelSave($response, true,  'При запросе возникла ошибка.')->ReturnData();
		return true;
	}
	
	/**
	 * Изменение порядка следования структурированного параметра
	 */
	function moveStructuredParam() {
		$data = $this->ProcessInputData('moveStructuredParam',true);
		if ($data === false) {return false;}

		$response = $this->dbmodel->moveStructuredParam($data);

		$this->ProcessModelSave($response, true,  'При запросе возникла ошибка.')->ReturnData();
		return true;
	}
	
	/**
	 * Получение дерева структурированных параметров для формы генерации
	 */
	function getStructuredParamsTree() {
		$data = $this->ProcessInputData('getStructuredParamsTree', true);
		if ($data) {
			$response = $this->dbmodel->getStructuredParamsTree($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}
}