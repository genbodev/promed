<?php defined('BASEPATH') or die ('No direct script access allowed');
/*
 * Контроллер для двига отчетов
*/

/**
 * @author yunitsky
 * @property ReportEngine_model model
 */
class ReportEngine extends swController {
	/**
	 * Инпут Рулы
	 */
	function __construct() {
		parent::__construct();

		$this->load->database( 'reports' );
		$this->load->model('ReportEngine_model','model');

		$this->inputRules = array(
			'checkParamId' => array(
				array(
					'field' => 'serverId',
					'label' => 'Идентификатор сервера',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'field',
					'label' => 'Имя поля',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'value',
					'label' => 'Значение поля',
					'rules' => '',
					'default' => '',
					'type' => 'string'
				),
				array(
					'field' => 'original',
					'label' => 'Старое значение',
					'type' => 'string'
				)
			),
			'RunDBF' => array(
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'NumRep',
					'label' => 'Идетификатор формы',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'NumTab',
					'label' => 'Идетификатор таблицы',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'YearRep',
					'label' => 'Год',
					'rules' => '',
					'type' => 'int'
				)
			),
			'ajaxcheckParamId' => array(
				array(
					'field' => 'ParamId',
					'label' => 'Идентификатор параметра',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'RegionId',
					'label' => 'Регион',
					'rules' => '',
					'type' => 'string'
				)
			),
			'checkUniqueReportCaption' => array(
				array(
					'field' => 'Report_Caption',
					'label' => 'Заголовок',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'ReportCatalog_id',
					'label' => 'Идентификатор каталога',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'Report_id',
					'label' => 'Идентификатор отчета',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'serverId',
					'label' => 'Идентификатор сервера',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'field',
					'label' => 'Имя поля',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'value',
					'label' => 'Значение поля',
					'rules' => '',
					'default' => '',
					'type' => 'string'
				),
				array(
					'field' => 'original',
					'label' => 'Старое значение',
					'type' => 'string'
				),
				array(
					'field' => 'type',
					'label' => 'type',
					'type'  => 'string'
				)
			),
			'checkReportDescriptionLength' => array(
				array(
					'field' => 'value',
					'label' => 'Значение поля',
					'rules' => '',
					'default' => '',
					'type' => 'string'
				)
			),
			'checkReportTitleLength' => array(
				array(
					'field' => 'value',
					'label' => 'Значение поля',
					'rules' => '',
					'default' => '',
					'type' => 'string'
				)
			),
			'getServerTree' => array(
				array(
					'field' => 'serverId',
					'label' => 'Идентификатор сервера',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'objectType',
					'label' => 'Тип обьекта',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'node',
					'label' => 'Код узла',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'mode',
					'label' => 'режим',
					'rules' => '',
					'type' => 'string'
				),
			),
			'disableReportFormat' => array(
				array(
					'field' => 'serverId',
					'label' => 'Идентификатор сервера',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => '__mode',
					'label' => 'Вид действия',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'ownerId',
					'label' => 'Код каталога',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'reportId',
					'label' => 'Идентификатор отчета',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'formatId',
					'label' => 'Идентификатор формата',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'disableflag',
					'label' => 'Флаг отключения формата',
					'rules' => '',
					'type' => 'int'
				)
			),
			'CheckIfReportInQueue' => array(
				array(
					'field' => 'Report_id',
					'label'	=> 'Идентификатор отчета',
					'rules'	=> 'required',
					'type'	=> 'string'
				),
				array(
					'field' => 'ReportParams',
					'label' => 'Параметры отчёта',
					'rules' => '',
					'type' => 'string'
				)
			),
			'changePositionFormat' => array(
				array(
					'field' => 'serverId',
					'label' => 'Идентификатор сервера',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => '__mode',
					'label' => 'Вид действия',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'ownerId',
					'label' => 'Код каталога',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'reportId',
					'label' => 'Идентификатор отчета',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'formatId',
					'label' => 'Идентификатор формата',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'changePositionflag',
					'label' => 'Флаг изменения позиции формата',
					'rules' => '',
					'type' => 'int'
				)
			),
			'serverRule' => array(
				array(
					'field' => 'serverId',
					'label' => 'Идентификатор сервера',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => '__mode',
					'label' => 'Вид действия',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'ownerId',
					'label' => 'Код каталога',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'reportId',
					'label' => 'Идентификатор отчета',
					'rules' => '',
					'type' => 'int'
				)
			),
			'checkSql' => array(
				array(
					'field' => 'serverId',
					'label' => 'Идентификатор сервера',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'Region_id',
					'label' => 'Регион',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'sql',
					'label' => 'Запрос',
					'rules' => 'required',
					'type' => 'string'
				)
			),
			'getReportContent' => array(
				array(
					'field' => 'serverId',
					'label' => 'Идентификатор сервера',
					'rules' => 'required',
					'type' => 'int'
				),
				array(
					'field' => 'reportId',
					'label' => 'Идентификатор отчета',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'ownerId',
					'label' => 'Код каталога',
					'rules' => '',
					'type' => 'string'
				)
			),
			'setReportCatalog' => array(
				array('field' => 'Report_id', 'label' => 'Ид. отчета', 'rules' => 'required', 'type' => 'id' ),
				array('field' => 'ReportCatalog_id', 'label' => 'Ид. нового каталога', 'rules' => 'required', 'type' => 'id' )
			),
			'copyReport' => array(
				array('field' => 'Report_id', 'label' => 'Ид. отчета', 'rules' => 'required', 'type' => 'id' ),
				array('field' => 'ReportCatalog_id', 'label' => 'Ид. нового каталога', 'rules' => 'required', 'type' => 'id' ),
				array('field' => 'Region_id', 'label' => 'Регион', 'rules' => '', 'type' => 'int' )
			),
			'createReportUrl' => array(
				array('field' => 'param_RegionCode', 'label' => 'Код региона', 'type' => 'id' ),
				array('field' => 'param_pmuser_id', 'label' => 'Ид. пользователя', 'type' => 'id' ),
				array('field' => 'param_pmuser_org_id', 'label' => 'Ид. организации', 'type' => 'int' )
			),
		);
	}

	/**
	 *getServersList
	 */
	function getServersList() {
		$responce = new JsonResponce();
		$result = $this->model->getServersList();
		echo $responce->toStore($result)->success(true)->json();
	}

	/**
	 *getServerTree
	 */
	function getServerTree() {
		echo $this->model->getServerTree($this->ProcessInputData('getServerTree', true, true));
	}

	/**
	 *getReport
	 */
	public function getReport() {
		echo $this->model->getReport($this->ProcessInputData('serverRule', true, true));
	}

	/**
	 *getReportCatalog
	 */
	public function getReportCatalog() {
		echo $this->model->getReportCatalog($this->ProcessInputData('serverRule', true, true));
	}

	/**
	 *getReportContnent
	 */
	public function getReportContent() {
		echo $this->model->getReportContent($this->ProcessInputData('getReportContent', true, true));
	}

	/**
	 *getreportContentFieldSet
	 */
	public function getReportContentFieldset() {
		echo $this->model->getReportContentFieldset($this->ProcessInputData('serverRule', true, true));
	}

	/**
	 *getReportContentParameter
	 */
	public function getReportContentParameter() {
		echo $this->model->getReportContentParameter($this->ProcessInputData('serverRule', true, true));
	}

	/**
	 *getReportParameter
	 */
	public function getReportParameter() {
		echo $this->model->getReportParameter($this->ProcessInputData('serverRule', true, true));
	}

	/**
	 *getReportParameterCatalog
	 */
	public function getReportParameterCatalog() {
		echo $this->model->getReportParameterCatalog($this->ProcessInputData('serverRule', true, true));
	}

	/**
	 *getParamterCombo
	 */
	public function getParametersCombo() {
		echo $this->model->getParametersCombo($this->ProcessInputData('serverRule', true, true));
	}

	/**
	 * getFormatsAll
	 */
	public function getFormatsAll() {
		$data = $this->ProcessInputData('serverRule', true, true);
		$data['getall'] = true;
		echo $this->model->getFormats($data);
	}

	/**
	 * getFormats
	 */
	public function getFormats() {
		$data = $this->ProcessInputData('serverRule', true, true);
		$data['getall'] = false;
		echo $this->model->getFormats($data);
	}

	/**
	 * disableReportFormat
	 */
	public function disableReportFormat() {
		echo $this->model->disableReportFormat($this->ProcessInputData('disableReportFormat', true, true));
	}

	/**
	 * changePositionFormat
	 */
	public function changePositionFormat(){
		echo $this->model->changePositionFormat($this->ProcessInputData('changePositionFormat', true, true));
	}

	/**
	*	Проверка нахождения отчета в очереди.
	*/
	function CheckIfReportInQueue()
	{
		$onlyParams = true;
		$data = $this->ProcessInputData('CheckIfReportInQueue', true, true);
		$params = $this->model->createReportProxyUrl($onlyParams);
		$data['Report_Params'] = urldecode($params);
		$data['ReportParamsLen'] = mb_strlen($data['Report_Params']);
		$this->load->database( 'reports' );
		$this->load->model('ReportRun_model', 'rr_model');
		$ReportRun_id = $this->rr_model->getReportQueueId($data);
		if(isset($ReportRun_id))
			echo $ReportRun_id;
		else
			echo 0;
	}

	/**
	 * Запуск нового отчёта
	 */
	function RunDBF()
	{
		$data = $this->ProcessInputData('RunDBF', true, true);

		switch($data['NumRep']){
			case '12' : $data['NumRep'] .= substr($data['NumTab'],0,1); break;
			case '14' : $data['NumRep'] .= ($data['NumTab'] == "2000") ? ''  : '0'; break;
			case '141': $data['NumTab'] = null; break;
			case '16' : $data['NumTab'] = null; break;
			case '30' :
				switch($data['NumTab']){
					case '306' : $data['NumRep'] = '306'; $data['NumTab'] = null; break;
					case '2100': $data['NumRep'] = '303'; break;
					case '3100': $data['NumRep'] = '304'; break;
				}
				break;
			case '57' : $data['NumTab'] = null; break;
		}

		$filename =  $this->model->RunReportDBF($data);

		echo '{"success":"true","Link":"'.$filename.'"}';
	}

	/**
	 * createReportUrl
	 */
	public function createReportUrl() {
		$data = $this->ProcessInputData('createReportUrl', true, true);
		if(isset($_REQUEST['onlyParams']))
			$onlyParams = $_REQUEST['onlyParams'];
		else
			$onlyParams = false;
		echo $this->model->createReportProxyUrl($onlyParams, $data);
	}

	/**
	 *checkSQL
	 */
	public function checkSql() {
		echo $this->model->checkSql($this->ProcessInputData('checkSql', true, true));
	}

	/**
	 *getReportContentEngine
	 */
	public function getReportContentEngine() {
		echo $this->model->getReportContentEngine($this->ProcessInputData('getReportContent', true, true));
	}

	/**
	 * getParameterContentEngine
	 */
	public function getParameterContentEngine() {
		echo $this->model->getParameterContentEngine($_REQUEST);
	}

	/**
	 *catalogCRUD
	 */
	public function catalogCRUD() {
		$ReportCatalog_id = $_REQUEST['ReportCatalog_id'];
		if($_REQUEST['__mode'] != 'delete') {
			if(!$_REQUEST['ReportCatalog_pid'] || in_array($_REQUEST['ReportCatalog_pid'], ['catalog', 'catalogroot']))
				$_REQUEST['ReportCatalog_pid'] = null;
			$this->model->moveReportCatalogDown($ReportCatalog_id,$_REQUEST['__mode'],$_REQUEST['ReportCatalog_Position'],$_REQUEST['ReportCatalog_pid']);
		}
		else{
			$this->model->moveReportCatalogUp($ReportCatalog_id);
		}
		echo $this->model->executeSQL(self::E_CATALOG);
	}

	const E_CATALOG = 'ReportCatalog';
	const E_REPORT  = 'Report';
	const E_PARAMETER  = 'ReportParameter';
	const E_PARAMETERCATALOG  = 'ReportParameterCatalog';
	const E_CONTENTPARAMETER  = 'ReportContentParameter';
	const E_CONTENT  = 'ReportContent';

	/**
	 *parameterCatalogCRUD
	 */
	public function parameterCatalogCRUD() {
		if(!isset($_REQUEST['ReportParameterCatalog_pid']) || $_REQUEST['ReportParameterCatalog_pid'] == 'params')
			$_REQUEST['ReportParameterCatalog_pid'] = null;
		echo $this->model->executeSQL(self::E_PARAMETERCATALOG);
	}

	/**
	 * parameterCRUD
	 */
	public function parameterCRUD() {
		if($_REQUEST['__mode'] != 'delete') {
			if($_REQUEST['ReportParameter_MaxLength'] !== 0 && !$_REQUEST['ReportParameter_MaxLength']) {
				$_REQUEST['ReportParameter_MaxLength'] = null;
			}
			if($_REQUEST['ReportParameter_Length'] !== 0 && !$_REQUEST['ReportParameter_Length']) {
				$_REQUEST['ReportParameter_Length'] = null;
			}
			if(!$_REQUEST['ReportParameter_Default']) {
				$_REQUEST['ReportParameter_Default'] = null;
			}
			/* $checkParams = array(
				'ParamId' => $_REQUEST['ReportParameter_Name'],
				'RegionId' => $_REQUEST['Region_id']
			);
			$ans = $this->model->ajaxcheckParamId($checkParams);
			if(strpos($ans,'true') == false){
				$resp = new JsonResponce();
				echo $resp->error('Параметр с таким идентификатором уже существует в выбранном Вами регионе')->utf()->json();
			}*/
		}
		if(isset($_REQUEST['ReportParameter_Label'])){
			$tempLabel = toAnsi($_REQUEST['ReportParameter_Label']);
			if(strpos((toAnsi($_REQUEST['ReportParameter_Label'])),'Пермь') > 0)
				$_REQUEST['ReportParameter_Label'] = toUTF(str_replace(' (Пермь)','',$tempLabel));
			else if(strpos((toAnsi($_REQUEST['ReportParameter_Label'])),'Екатеринбург') > 0)
				$_REQUEST['ReportParameter_Label'] = toUTF(str_replace(' (Екатеринбург)','',$tempLabel));
			else if(strpos((toAnsi($_REQUEST['ReportParameter_Label'])),'Казахстан') > 0)
				$_REQUEST['ReportParameter_Label'] = toUTF(str_replace(' (Казахстан)','',$tempLabel));
			else if(strpos((toAnsi($_REQUEST['ReportParameter_Label'])),'Беларусь') > 0)
				$_REQUEST['ReportParameter_Label'] = toUTF(str_replace(' (Беларусь)','',$tempLabel));
			else if(strpos((toAnsi($_REQUEST['ReportParameter_Label'])),'Москва') > 0)
				$_REQUEST['ReportParameter_Label'] = toUTF(str_replace(' (Москва)','',$tempLabel));
			else if(strpos((toAnsi($_REQUEST['ReportParameter_Label'])),'Саратов') > 0)
				$_REQUEST['ReportParameter_Label'] = toUTF(str_replace(' (Саратов)','',$tempLabel));
			else if(strpos((toAnsi($_REQUEST['ReportParameter_Label'])),'Псков') > 0)
				$_REQUEST['ReportParameter_Label'] = toUTF(str_replace(' (Псков)','',$tempLabel));
			else if(strpos((toAnsi($_REQUEST['ReportParameter_Label'])),'Астрахань') > 0)
				$_REQUEST['ReportParameter_Label'] = toUTF(str_replace(' (Астрахань)','',$tempLabel));
			else if(strpos((toAnsi($_REQUEST['ReportParameter_Label'])),'Хакасия') > 0)
				$_REQUEST['ReportParameter_Label'] = toUTF(str_replace(' (Хакасия)','',$tempLabel));
			else if(strpos((toAnsi($_REQUEST['ReportParameter_Label'])),'Карелия') > 0)
				$_REQUEST['ReportParameter_Label'] = toUTF(str_replace(' (Карелия)','',$tempLabel));
			else if(strpos((toAnsi($_REQUEST['ReportParameter_Label'])),'Уфа') > 0)
				$_REQUEST['ReportParameter_Label'] = toUTF(str_replace(' (Уфа)','',$tempLabel));
			else if(strpos((toAnsi($_REQUEST['ReportParameter_Label'])),'Бурятия') > 0)
				$_REQUEST['ReportParameter_Label'] = toUTF(str_replace(' (Бурятия)','',$tempLabel));
			else if(strpos((toAnsi($_REQUEST['ReportParameter_Label'])),'Пенза') > 0)
				$_REQUEST['ReportParameter_Label'] = toUTF(str_replace(' (Пенза)','',$tempLabel));
			else if(strpos((toAnsi($_REQUEST['ReportParameter_Label'])),'Калуга') > 0)
				$_REQUEST['ReportParameter_Label'] = toUTF(str_replace(' (Калуга)','',$tempLabel));
			else if(strpos((toAnsi($_REQUEST['ReportParameter_Label'])),'Крым') > 0)
				$_REQUEST['ReportParameter_Label'] = toUTF(str_replace(' (Крым)','',$tempLabel));
			else if(strpos((toAnsi($_REQUEST['ReportParameter_Label'])),'Вологда') > 0)
				$_REQUEST['ReportParameter_Label'] = toUTF(str_replace(' (Вологда)','',$tempLabel));
		}
		echo $this->model->executeSQL(self::E_PARAMETER);
	}

	/**
	 *contentCRUD
	 */
	public function contentCRUD() {
		$mode = $_REQUEST['__mode'];
		if ($mode != 'delete' ) {
			if (isset($_REQUEST['ReportContent_Position'])) {
				$position = $_REQUEST['ReportContent_Position'];
				$Report_id = $_REQUEST['Report_id'];
				$this->model->moveDatasetDown($position, $Report_id, $mode);
			}

		} else {
			$ReportContent_id = $_REQUEST['ReportContent_id'];
			$this->model->moveDatasetUp($ReportContent_id);
		}
		echo $this->model->executeSQL(self::E_CONTENT);
	}

	/**
	 * @return string
	 */
	public function reportCRUD() {
		$mode = $_REQUEST['__mode'];

		if($mode == 'add' &&  $_REQUEST['ReportCatalog_id'] == 'root' ) {
			$responce = new JsonResponce();
			return $responce->error('Нельзя добавлять отчеты без папки')->json();
		}

		if ($mode != 'delete') {
			// все нижеследующие позиции необходимо автоматически сдвигать на +1. (refs #2737)
			if(isset($_REQUEST['Report_Position'])){
				$position = $_REQUEST['Report_Position'];
				$ReportCatalog_id = $_REQUEST['ReportCatalog_id'];
				$this->model->moveReportDown($position, $ReportCatalog_id, $mode);
			}
		} else {
			// все нижеследующие позиции необходимо автоматически сдвигать на -1. (refs #2737)
			$Report_id=$_REQUEST['Report_id'];
			$this->model->moveReportUp($Report_id);
		}

		echo $this->model->executeSQL(self::E_REPORT);
	}

	/**
	 * contentParameterCRUD
	 */
	public function contentParameterCRUD() {
		$mode = $_REQUEST['__mode'];

		if ($mode != 'delete') {
			if(isset($_REQUEST['ReportContentParameter_Required']) &&
					($_REQUEST['ReportContentParameter_Required'])) {
				$_REQUEST['ReportContentParameter_Required'] = 1;
			} else $_REQUEST['ReportContentParameter_Required'] = 0;
			if(!$_REQUEST['ReportContentParameter_Default']) {
				$_REQUEST['ReportContentParameter_Default'] = null;
			}
			if(!$_REQUEST['ReportContentParameter_ReportLabel']) {
				$_REQUEST['ReportContentParameter_ReportLabel'] = null;
			}
			if(!$_REQUEST['ReportContentParameter_PrefixId']) {
				$_REQUEST['ReportContentParameter_PrefixId'] = null;
			}
			if(!$_REQUEST['ReportContentParameter_PrefixText']) {
				$_REQUEST['ReportContentParameter_PrefixText'] = null;
			}
			if(!$_REQUEST['ReportContentParameter_ReportId']) {
				$_REQUEST['ReportContentParameter_ReportId'] = null;
			}

			$Report_id = null;
			if ( !empty($_REQUEST['Report_id']) ) {
				$Report_id = $_REQUEST['Report_id'];
			}
			
			if (empty($Report_id) && !empty($_REQUEST['ReportContent_id'])) {
				$resp = $this->model->getReport(array('ReportContent_id' => $_REQUEST['ReportContent_id']));

				if (!empty($resp[0]['Report_id'])) {
					$Report_id = $resp[0]['Report_id'];
				}
			}

			// все нижеследующие позиции необходимо автоматически сдвигать на +1. (refs #2737)
			if(!empty($Report_id) && isset($_REQUEST['ReportContentParameter_Position'])){
				$position = $_REQUEST['ReportContentParameter_Position'];
				$this->model->moveReportParametersDown($position, $Report_id, $mode);
			}

		} else {

			// все нижеследующие позиции необходимо автоматически сдвигать на -1. (refs #2737)
			$ReportContentParameter_id=$_REQUEST['ReportContentParameter_id'];
			$this->model->moveReportParametersUp($ReportContentParameter_id);

		}


		echo $this->model->executeSQL(self::E_CONTENTPARAMETER);
	}

	/**
	 * getAllTree
	 */
	public function getAllTree(){
		$responce = new JsonResponce($this->model->getAllTree($_REQUEST));
		echo $responce->utf()->json();
	}

	/**
	 * checkParamId
	 */
	public function checkParamId(){
		echo $this->model->checkParamId($this->ProcessInputData('checkParamId', true, true));
	}

	/**
	 * Проверка идентификатора параметра на уникальность в рамках выбранного региона
	 */
	public function ajaxcheckParamId(){
		echo $this->model->ajaxcheckParamId($this->ProcessInputData('ajaxcheckParamId', true, true));
	}
	/**
	 * Проверка заголовка на уникальность
	 */
	public function checkUniqueReportCaption()
	{
		echo $this->model->checkUniqueReportCaption($this->ProcessInputData('checkUniqueReportCaption', true, true));
	}

	/**
	 * Проверка описания отчета на длину
	 */
	public function checkReportDescriptionLength()
	{
		echo $this->model->checkReportDescriptionLength($this->ProcessInputData('checkReportDescriptionLength', true, true));
	}

	/**
	 * Проверка наименования отчета на длину
	 */
	public function checkReportTitleLength(){
		echo $this->model->checkReportTitleLength($this->ProcessInputData('checkReportTitleLength', true, true));
	}

	/**
	 * Меняет каталог расположения отчета
	 */
	function setReportCatalog() {
		$data = $this->ProcessInputData('setReportCatalog', true);
		if ( $data === false ) { return false; }
		$response = $this->model->setReportCatalog($data);
		$this->ProcessModelSave($response, true)->ReturnData();
	}

	/**
	 * Меняет каталог расположения отчета
	 */
	function copyReport() {
		$data = $this->ProcessInputData('copyReport', true);
		if ( $data === false ) { return false; }
		$response = $this->model->copyReport($data);
		$this->ProcessModelSave($response, true)->ReturnData();
	}
}
?>
