<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Template - контроллер работы с шаблонами и референтными значениями
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @author       Марков Андрей Александрович
* @version      декабрь 2010 года
 *
 *
 * @property Template_model $Template_model
 * @property EvnXmlBase_model $EvnXmlBase_model
 *
 */

class Template extends swController {
	var $model_name = "Template_model";

	/**
	 * construct
	 */
	function __construct() {
		parent::__construct();
		$this->load->library('textlog', array('file'=>'Template_getEvnForm.log'));
		$this->inputRules = array(
			'getSectionContentForAdd' => array(
				array(
					'field' => 'object',
					'label' => 'Код объекта',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'object_id',
					'label' => 'Идентификатор объекта',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'object_key',
					'label' => 'Имя столбца с идентификатором объекта',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'parent_code',
					'label' => 'Код родителя объекта',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'parent_id',
					'label' => 'Идентификатор родителя объекта',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'parent_key',
					'label' => 'Имя столбца с идентификатором родителя объекта',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'section_code',
					'label' => 'Код новой секции документа',
					'rules' => 'required',
					'type' => 'string'
				)
			),
			'getSectionContentForUpdate' => array(
				array(
					'field' => 'object',
					'label' => 'Имя объекта',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'object_id',
					'label' => 'Идентификатор объекта',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'object_key',
					'label' => 'Имя столбца с идентификатором объекта',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'section_code',
					'label' => 'Код секции документа',
					'rules' => 'required',
					'type' => 'string'
				)
			),
			'getEvnForm' => array(
				array(
					'field' => 'user_MedStaffFact_id',
					'label' => 'Идентификатор рабочего места пользователя',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'is_reload_one_section',
					'default'=>0,
					'label' => 'Флаг, что требуется обновление одной секции',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'view_section',
					'default'=>'list',
					'label' => 'Тип секции для отображения (list,main...)',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'object',
					'label' => 'Имя объекта',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'object_id',
					'label' => 'Имя идентификатора объекта',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'object_value',
					'label' => 'Идентификатор объекта',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'parent_object',
					'label' => 'Имя объекта-родителя',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'parent_object_id',
					'label' => 'Имя идентификатора объекта-родителя',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'parent_object_value',
					'label' => 'Идентификатор объекта-родителя',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'param_name',
					'label' => 'Имя дополнительного параметра',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'field' => 'param_value',
					'label' => 'Значение дополнительного параметра',
					'rules' => 'trim',
					'type' => 'string'
				),
				array(
					'default'=>0,
					'field' => 'Person_id',
					'label' => 'Идентификатор человека (обязателен для сигнальной информации и документов',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'default'=>0,
					'field' => 'PersonChild_id',
					'label' => 'Идентификатор человека (обязателен для сигнальной информации и документов',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'accessType',
					'label' => 'Доступ',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'ARMType',
					'label' => 'Тип АРМа',//Необходим для определения template-ов для мобильного арма СМП
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'load_empty',
					'label' => 'Пустой документ',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'isOnlyLast',
					'label' => 'Отображать только последние',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'countDiagConfs',
					'label' => 'подсчет полей',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'MorbusType_id',
					'label' => 'Идентификатор типа заболевания',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'forRightPanel',
					'label' => 'Для правой панели назначений',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'PrescriptionType_Code',
					'label' => 'Тип назначения',
					'rules' => '',
					'type' => 'string'
				),
				array('field' => 'MorbusOnkoVizitPLDop_id','label' => '','rules' => '','type' => 'id'),
				array('field' => 'MorbusOnkoLeave_id','label' => '','rules' => '','type' => 'id'),
				array('field' => 'MorbusOnkoDiagPLStom_id','label' => '','rules' => '','type' => 'id'),
				array('field' => 'EvnDiagPLStomSop_id','label' => '','rules' => '','type' => 'id'),
				array('field' => 'EvnDiagPLSop_id','label' => '','rules' => '','type' => 'id'),
				array( //https://redmine.swan.perm.ru/issues/104824
				'default'	=> '1',
				'field'		=> 'from_MZ',
				'label'		=> 'Запуск из АРМ МЗ',
				'rules'		=> '',
				'type'		=> 'int'
				),
				array(
					'default'	=> '1',
					'field'		=> 'from_MSE',
					'label'		=> 'Запуск из АРМ МСЭ',
					'rules'		=> '',
					'type'		=> 'int'
				)
			),
			'loadRefValues' => array(
				array(
						'field' => 'RefValues_id',
						'label' => 'Идентификатор',
						'rules' => '',
						'type' => 'id'
					),
				array(
						'field' => 'RefValues_OPMUCode',
						'label' => 'Код ОПМУ',
						'rules' => '',
						'type' => 'string'
					),
				array(
						'field' => 'RefValues_Name',
						'label' => 'Наименование',
						'rules' => '',
						'type' => 'string'
					),
				array(
						'field' => 'RefValues_Nick',
						'label' => 'Краткое наименование',
						'rules' => '',
						'type' => 'string'
					),
				array(
						'field' => 'RefMaterial_id',
						'label' => 'Материал',
						'rules' => '',
						'type' => 'string'
					),
				array(
						'field' => 'RefValuesType_id',
						'label' => 'Тип',
						'rules' => '',
						'type' => 'string'
					),
				array(
						'field' => 'RefCategory_id',
						'label' => 'Категория',
						'rules' => '',
						'type' => 'string'
					),
				array(
					'default' => 0,
					'field' => 'start',
					'label' => 'Начальный номер записи',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'default' => 100,
					'field' => 'limit',
					'label' => 'Количество возвращаемых записей',
					'rules' => '',
					'type' => 'int'
				)
			),
			'loadRefValuesList' => array(
				array(
						'field' => 'RefValues_id',
						'label' => 'Идентификатор',
						'rules' => '',
						'type' => 'id'
					),
				array(
						'field' => 'query',
						'label' => 'Запрос референтного значения',
						'rules' => '',
						'type' => 'string'
					),
				array(
						'field' => 'RefValuesType_id',
						'label' => 'Тип',
						'rules' => '',
						'type' => 'string'
					),
				array(
						'field' => 'RefCategory_id',
						'label' => 'Категория',
						'rules' => '',
						'type' => 'string'
					),
				array(
						'field' => 'RefValues_Code',
						'label' => 'Код HL7',
						'rules' => '',
						'type' => 'string'
					),
				array(
						'field' => 'RefValues_Name',
						'label' => 'Наименование',
						'rules' => '',
						'type' => 'string'
					),
				array(
						'field' => 'RefValues_Nick',
						'label' => 'Клиническое название',
						'rules' => '',
						'type' => 'string'
					)
			),
			'editRefValues' => array(
				array(
						'field' => 'RefValues_id',
						'label' => 'Идентификатор',
						'rules' => 'required',
						'type' => 'id'
					)
			),
			'saveRefValues' => array(
				array(
						'field' => 'RefValues_id',
						'label' => 'Идентификатор',
						'rules' => '',
						'type' => 'id'
					),
				array(
						'field' => 'RefValues_Code',
						'label' => 'Код HL7',
						'rules' => '',
						'type' => 'string'
					),
				array(
						'field' => 'RefValues_OPMUCode',
						'label' => 'Код ОПМУ',
						'rules' => '',
						'type' => 'string'
					),
				array(
						'field' => 'RefValues_LocalCode',
						'label' => 'Код ФОМС',
						'rules' => '',
						'type' => 'string'
					),
				array(
						'field' => 'RefValues_Name',
						'label' => 'Наименование',
						'rules' => 'required',
						'type' => 'string'
					),
				array(
						'field' => 'RefValues_Nick',
						'label' => 'Клиническое название',
						'rules' => 'required',
						'type' => 'string'
					),
				array(
						'field' => 'RefValuesType_id',
						'label' => 'Тип',
						'rules' => 'required',
						'type' => 'id'
					),
				array(
						'field' => 'RefValuesUnit_id',
						'label' => 'Единица измерения',
						'rules' => '',
						'type' => 'id'
					),
				array(
						'field' => 'RefValues_LowerLimit',
						'label' => 'Нижняя граница нормы',
						'rules' => '',
						'type' => 'float'
					),
				array(
						'field' => 'RefValues_UpperLimit',
						'label' => 'Верхняя граница нормы',
						'rules' => '',
						'type' => 'float'
					),
				array(
						'field' => 'RefValuesGroup_id',
						'label' => 'Актуальная группа',
						'rules' => '',
						'type' => 'id'
					),
				array(
						'field' => 'RefValues_LowerAge',
						'label' => ' Минимальный возраст',
						'rules' => '',
						'type' => 'int'
					),
				array(
						'field' => 'RefValues_UpperAge',
						'label' => 'Максимальный возраст',
						'rules' => '',
						'type' => 'int'
					),
				array(
						'field' => 'AgeUnit_id',
						'label' => 'Единицы возраста',
						'rules' => '',
						'type' => 'id'
					),
				array(
						'field' => 'RefCategory_id',
						'label' => 'Категория',
						'rules' => '',
						'type' => 'id'
					),
				array(
						'field' => 'HormonalPhaseType_id',
						'label' => 'Гормональная фаза',
						'rules' => '',
						'type' => 'id'
					),
				array(
						'field' => 'TimeOfDay_id',
						'label' => 'Время суток',
						'rules' => '',
						'type' => 'id'
					),
				array(
						'field' => 'RefMaterial_id',
						'label' => 'Материал',
						'rules' => '',
						'type' => 'id'
					),
				array(
						'field' => 'RefValues_Cost',
						'label' => 'Стоимость исследования',
						'rules' => '',
						'type' => 'float'
					),
				array(
						'field' => 'RefValues_UET',
						'label' => 'УЕТ исследования',
						'rules' => '',
						'type' => 'float'
					),
				array(
						'field' => 'RefValues_Method',
						'label' => ' Название метода исследования',
						'rules' => '',
						'type' => 'string'
					),
				array(
						'field' => 'RefValues_Description',
						'label' => 'Описание',
						'rules' => '',
						'type' => 'string'
					)
			),
			'loadEvnXmlList' => array (
				array('field' => 'filterDoc','label' => 'Фильтр','rules' => 'trim|mb_strtolower','type' => 'string', 'default' => 'evn'),
				array('field' => 'Person_id','label' => 'Идентификатор человека','rules' => '','type' => 'id'),
				array('field' => 'Evn_rid','label' => 'Идентификатор события','rules' => '','type' => 'id'),
				array('field' => 'EvnXml_id','label' => 'Идентификатор документа','rules' => '','type' => 'id')
			),
			'loadEvnXmlViewData' => array (
				array('field' => 'EvnXml_id','label' => 'Идентификатор документа','rules' => 'required','type' => 'id'),
				array('field' => 'instance_id','label' => 'instance_id','rules' => '', 'type' => 'string'),
			),
		);
	}
	/**
	 * Функция чтения списка референтных значений
	 * На выходе: JSON-строка
	 * Используется: форма отбражения списка референтных значений
	 */
	function loadRefValues() {
		$this->load->database();
		$this->load->model($this->model_name, 'Template_model');

		$data = $this->ProcessInputData('loadRefValues', true);
		if ($data) {
			$response = $this->Template_model->loadRefValues($data);
			$this->ProcessModelMultiList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Функция чтения списка референтных значений (для комбобокса, не больше 50)
	 * На выходе: JSON-строка
	 * Используется: формы на которых используется комбобокс выбора референтных значений
	 */
	function loadRefValuesList() {
		$this->load->database();
		$this->load->model($this->model_name, 'Template_model');

		$data = $this->ProcessInputData('loadRefValuesList', true);
		if ($data) {
			$response = $this->Template_model->loadRefValuesList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}


	/**
	 * Функция чтения одной записи из таблицы референтных значений
	 * На выходе: JSON-строка
	 * Используется: форма редактирования референтного значения
	 */
	function editRefValues() {
		$this->load->database();
		$this->load->model($this->model_name, 'Template_model');

		$data = $this->ProcessInputData('editRefValues', true);
		if ($data) {
			$response = $this->Template_model->editRefValues($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}


	/**
	 * Функция записи одной записи в таблицу референтных значений
	 * На выходе: JSON-строка
	 * Используется: форма редактирования референтного значения
	 */
	function saveRefValues() {
		$this->load->database();
		$this->load->model($this->model_name, 'Template_model');

		$data = $this->ProcessInputData('saveRefValues', true);
		if ($data) {
			$response = $this->Template_model->saveRefValues($data);
			$this->ProcessModelSave($response, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	private $is_reload_one_section = false;
	private $map = array();
	public $document = '';

	/**
	 * Возвращает секцию $data['section_code']
	 * На выходе: JSON-строка
	 * Используется: форма редактирования события swEmkEditWindow.js
	 * Это надо будет немного переделать, чтобыв не обращаться к методам getEvnData и getEvnDocument
	 */
	function getSectionContentForAdd() {
		$data = $this->ProcessInputData('getSectionContentForAdd', true);
		if ($data === false)
		{
			return false;
		}
		$this->load->database();
		$this->load->model($this->model_name, 'Template_model');
		$this->load->helper("Xml");
		$this->load->library('parser');
		// приводим параметры к виду понятному методу getEvnData($data);
		$evn_data = $data;
		$evn_data['object_value'] = $data['object_id'];
		$evn_data['object_id'] = (empty($data['object_key']))?($data['object'].'_id'):$data['object_key'];
		/* так будут получены все дочки, без них только одно
		$evn_data['parent_object_value'] = $data['parent_id'];
		$evn_data['parent_object_id'] = (empty($data['parent_key']))?($data['parent_code'].'_id'):$data['parent_key'];
		*/
		$this->is_reload_one_section = false;
		$this->map = $this->Template_model->getEvnData($evn_data);
		$this->document = '';
		if (!empty($data['object_key']) AND $data['object_key'] == 'Person_id')
		{
			$this->document = $this->Template_model->getEvnDocument($this->map,$data['object'],$data['object_id']);
		}
		else
		{
			$this->document = $this->Template_model->getEvnDocument($this->map);
		}
		$this->Template_model->addHyperLinks();
		ConvertFromWin1251ToUTF8($this->document);
		array_walk_recursive($this->map,'ConvertFromWin1251ToUTF8');
		$result = array('success'=>true, 'html' => $this->document,'map'=>$this->map);
		$this->ReturnData($result);
		return true;
	}

	/**
	 * Создает форму отображения события для редактирования
	 * На выходе: JSON-строка
	 * Используется: форма редактирования события swEmkEditWindow.js
	 */
	function getEvnForm() {
		/*
		ob_start();
		$html = ob_get_clean();
		$html = addcslashes($html, '\\');
		$result = array('success'=>true, 'html' => $html,'map'=>array());
		$this->ReturnData($result);
		*/
		$data = $this->ProcessInputData('getEvnForm', true);
		//$this->textlog->add('getEvnForm: Старт! ');
		//$this->textlog->add('getEvnForm: Параметры: '.serialize($data));
		if ($data === false)
		{
			return false;
		}
		// todo: Запутался в отправке-передаче-получении полей section_code, object, object_key, object_value и прочих. Надо будет разобраться и сделать правильно
		if (empty($data['accessType'])) { unset($data['accessType']); }

		$this->load->helper("Xml");
		$this->load->library('parser');
		$this->load->library('swPrescription');
		$this->load->database();
		$this->load->model($this->model_name, 'Template_model');

		if ($data['object'] == 'EvnPrescrCustom') {
			$data['object'] = swPrescription::getEvnClassSysNickByType($data['PrescriptionType_Code']);
			if ($data['object'] === false) {
				throw new Exception('Не удалось определить тип назначения');
			}
		}

		//$this->testmap = array();
		$this->map = $this->Template_model->getEvnData($data);

		//echo var_dump($this->map); ;
		//die();

		//$this->textlog->add('getEvnForm: getEvnData выполнен');
		//var_dump($this->map); exit;
		$this->is_reload_one_section = (!empty($data['is_reload_one_section']));
		$this->document = '';

		if ( !empty($data['param_name']) && !array_key_exists($data['param_name'], $data) )
		{
			$data[$data['param_name']] = $data['param_value'];
		}

		if (
			!empty($data['Person_id'])
			&& !(!empty($data['param_name']) && $data['param_name'] == 'MorbusOnko_pid' && $data['param_value'] == $data['Person_id'])
		) {
			$this->document = $this->Template_model->getEvnDocument($this->map,$data['object'],$data['Person_id']);
			//$this->textlog->add('getEvnForm: document: person_id:'.$data['Person_id']);
		}
		else if (!empty($data['parent_object_value']))
		{
			$this->document = $this->Template_model->getEvnDocument($this->map,$data['object'],$data['parent_object_value'],$data);
			//$this->textlog->add('getEvnForm: document: parent_object_value:'.$data['parent_object_value']);
		}
		else
		{
			$this->document = $this->Template_model->getEvnDocument($this->map);
			//$this->textlog->add('getEvnForm: document: free');
		}
		//$this->textlog->add('getEvnForm: !!!'.$this->document);

		//$this->textlog->add('getEvnForm: getEvnDocument выполнен');
		/*
		if (empty($data['is_reload_one_section']))
		{
			$this->document = '<div style="font-size: 12px; font-family: arial,helvetica,sans-serif;">'.$this->document.'</div>';
		}
		*/
		//$this->textlog->add('getEvnForm: addHyperLinks выполнен');
		if (!((isset($data['ARMType']))||($data['ARMType']=='headBrigMobile'))) {
			$this->Template_model->addHyperLinks();
		}

		ConvertFromWin1251ToUTF8($this->document);
		if (is_array($this->map)) {
			array_walk_recursive($this->map,'ConvertFromWin1251ToUTF8');
		}
		if (class_exists('swEvnXml', false)) {
			$this->document = swEvnXml::cleaningHtml($this->document, array(
				'userLocalFiles' => 1,
			)); // #52118
		}
		$result = array('success'=>true, 'html' => $this->document,'map'=>$this->map);//,'testmap'=>$this->testmap
		$this->ReturnData($result);
		//print_r($result);
		//$this->textlog->add('getEvnForm: Финиш');
		return true;
	}

	/**
	 * Определение необходимости отображения кнопки добавления извещения
	 */
	function getAddEvnOnkoNotifyVisibility() {
		$this->load->database();
		if(isset($_POST['Person_id']))
			$Person_id = $_POST['Person_id'];
		$this->load->model('EvnOnkoNotify_model');
		$res = $this->EvnOnkoNotify_model->checkNotifyExists($Person_id, 'onko');


		$response = array('AddEvnOnkoNotifyVisibility' => !$res );//Если кнопка видима то true
		$this->ReturnData($response);
		return true;
	}

	/**
	 * Обработка текста с маркерами
	 * @param $text
	 * @param $evn_id
	 * @return string
	 */
	function prepareMarkerText($text, $evn_id) {
		$this->load->library('swMarker');
		return swMarker::processingTextWithMarkers($text, $evn_id, array('isPrint'=>false));
	}

	/**
	 * Функция чтения списка документов
	 * На выходе: JSON-строка
	 * Используется: swEmkDocumentsListWindow
	 */
	function loadEvnXmlList() {
		$data = $this->ProcessInputData('loadEvnXmlList', true);
		if ($data) {
			$this->load->database();
			$this->load->library('swFilterResponse');
			$this->load->model($this->model_name, 'Template_model');
			$response = $this->Template_model->loadEvnXmlList($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Функция чтения документа для формы просмотра
	 * На выходе: JSON-строка
	 * Используется: swEmkDocumentsListWindow
	 */
	function loadEvnXmlViewData() {
		$data = $this->ProcessInputData('loadEvnXmlViewData', true);
		if ($data) {
			$this->load->database();
			$this->load->library('swEvnXml');
			$this->load->library('swXmlTemplate');
			$parse_data = array();
			$object_data = array();
			$this->load->model('EvnXmlBase_model');
			$result = array('success' => true);
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
			$this->ReturnData($result);
			return true;
		} else {
			return false;
		}
	}
	/**
	 * Метод грузит данные в сторы назначений
	 */
	function getPrescrLabDiag() {
		$data = $this->ProcessInputData('getEvnForm', true);
		if ($data === false)
		{
			return false;
		}
		// todo: Запутался в отправке-передаче-получении полей section_code, object, object_key, object_value и прочих. Надо будет разобраться и сделать правильно
		if (empty($data['accessType'])) { unset($data['accessType']); }

		$this->load->helper("Xml");
		$this->load->library('parser');
		$this->load->library('swPrescription');
		$this->load->database();
		$this->load->model($this->model_name, 'Template_model');

		/**
		 * EvnPrescrLabDiag - object
		 * PrescriptionType_Code -
		 * accessType - unseted
		 * this->Template_model - Template_model
		 */

		if ($data['object'] == 'EvnPrescrCustom') {
			$data['object'] = swPrescription::getEvnClassSysNickByType($data['PrescriptionType_Code']);
			if ($data['object'] === false) {
				throw new Exception('Не удалось определить тип назначения');
			}
		}

		$res = $this->Template_model->getEvnPrescrData($data);
		if(!empty($res))
			array_walk_recursive($res,'ConvertFromWin1251ToUTF8');
		else $res = array();

		$this->ReturnData($res);
		return true;
	}
}

	/**
	 * Форма динамики результатов тестов
	 * Используется: ЭМК вкладка исследования
	 */
	function getDynamicsOfTestResultsForm() {

		$data = $this->ProcessInputData('getDynamicsOfTestResultsFormData', true);
		if ($data === false)
		{
			return false;
		}

		if (empty($data['accessType'])) { unset($data['accessType']); }

		$this->load->helper("Xml");
		$this->load->library('parser');
		$this->load->library('swPrescription');
		$this->load->database();
		$this->load->model($this->model_name, 'Template_model');

		if ($data['object'] == 'EvnPrescrCustom') {
			$data['object'] = swPrescription::getEvnClassSysNickByType($data['PrescriptionType_Code']);
			if ($data['object'] === false) {
				throw new Exception('Не удалось определить тип назначения');
			}
		}

		$this->map = $this->Template_model->getEvnData($data);

		$this->is_reload_one_section = (!empty($data['is_reload_one_section']));
		$this->document = '';

		if ( !empty($data['param_name']) && !array_key_exists($data['param_name'], $data) )
		{
			$data[$data['param_name']] = $data['param_value'];
		}

		if (
			!empty($data['Person_id']) 
			&& !(!empty($data['param_name']) && $data['param_name'] == 'MorbusOnko_pid' && $data['param_value'] == $data['Person_id']) 
		) {
			$this->document = $this->Template_model->getEvnDocument($this->map,$data['object'],$data['Person_id']);
		}
		else if (!empty($data['parent_object_value']))
		{
			$this->document = $this->Template_model->getEvnDocument($this->map,$data['object'],$data['parent_object_value'],$data);
		}
		else
		{
			$this->document = $this->editRefValues();
		}

		if (!((isset($data['ARMType']))||($data['ARMType']=='headBrigMobile'))) {
			$this->Template_model->addHyperLinks();
		}

		ConvertFromWin1251ToUTF8($this->document);
		if (is_array($this->map)) {
			array_walk_recursive($this->map,'ConvertFromWin1251ToUTF8');
		}
		if (class_exists('swEvnXml', false)) {
			$this->document = swEvnXml::cleaningHtml($this->document, array(
				'userLocalFiles' => 1,
			));
		}
		$result = array('success'=>true, 'html' => $this->document,'map'=>$this->map);
		$this->ReturnData($result);
		//print_r($result);
		return true;
	}

	/**
	 * Функция чтения записи из таблицы референтных значений
	 * Используется: форма просмотра динамики тестов
	 */
	function editRefValues() {
		$this->load->database();
		$this->load->model($this->model_name, 'Template_model');

		$data = $this->ProcessInputData('editRefValues', true);
		if ($data) {
			$response = $this->Template_model->editRefValues($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
			return true;
		} else {
			return false;
		}
	}