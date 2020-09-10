<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * LisUpdater - тестовый контроллер для получения данных их XML ЛИС-системы
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @property Textlog textlog
 * @property LisUpdater_model dbmodel
 */
class LisUpdater extends swController {
	public $inputRules = array(
		'getDirectoryFromXML' => array(
			array(
				'field' => 'name',
				'label' => 'Справочник',
				'rules' => 'required',
				'type' => 'string'
			)
		)
	);
	public $sessionid = null;
	public $server = array();
	
	//private $inputData = array();
	/**
	 * __construct
	 */
	function __construct() {
		parent::__construct();
		$this->load->model('LisUpdater_model', 'dbmodel');
		$this->load->helper('Xml');
		$this->load->library('textlog', array('file'=>'LisUpdater.log'));
		$this->load->model('Options_model', 'Options_model');
		try {
			$dbres = $this->Options_model->getDataStorageValues(array('DataStorageGroup_SysNick'=>'lis'), array());
			$options = array();
			foreach($dbres as $value) {
				$options[$value['DataStorage_Name']] = $value['DataStorage_Value'];
			}
			$this->server = array(
				'address'     => $options['lis_address'],
				'server'      => $options['lis_server'],
				'port'        => $options['lis_port'],
				'path'        => $options['lis_path'],
				'version'     => $options['lis_version'],
				'buildnumber' => $options['lis_buildnumber'],
				'login'       => defined('LIS_LOGIN')?LIS_LOGIN:null, // $options['lis_login'],
				'password'    => defined('LIS_PASSWORD')?LIS_PASSWORD:null, // $options['lis_password'],
				'clientid'    => defined('LIS_CLIENTID')?LIS_CLIENTID:null// $options['lis_clientid'] 
			);
		} catch (Exception $e) {
			throw new Exception('Не удалось получить настройки для ЛИС',0,$e);
		}

		if ($this->usePostgreLis)
			$this->load->swapi('lis');
	}
	/**
	 * toRec
	 */
	function toRec($m) {
		foreach ($m as $k=>$v) {
			if (is_array($v)) {
				if (isset($v['i'])) {
					if (isset($v['n'])) {
						$m[$v['n']] = $v['i'];
					} else {
						$m[$k] = $v['i'];
					}
				} else {
					$m[$k] = $this->toRec($v);
				}
			} else {
				// print_r($m);
			}
		}
		return $m;
	}
	
	/** Функция преобразования XML в нормальный массив
	 * @param $xml
	 * @return array
	 */
	function toArray($xml) {
		// функция 
		$x = new SimpleXMLElement($xml);
		//return $this->toRec(objectToArray($x));
		// todo: Чудо преобразование с помощью simpleXMLToArray: по идее вроде как можно просто преобразовать с помощью objectToArray и в дальнейшем обработать полученный массив
		return $this->toRec(simpleXMLToArray($x));
	}

	/** Функция обработки полученного XML-ответка и преобразование его в JSON (при необходимости)
	 * @param $xml
	 * @param bool $tojson
	 * @return array|string
	 */
	function getXmlResponse($xml, $tojson = true) {
		$r = $this->toArray($xml);
		if (is_array($r)) {
			if (isset($r['error'])) {
				$err = array('success' => false, 'Error_Code' => $r['error']['code'], 'Error_Msg' => $r['error']['description']);
				if ($tojson) {
					return json_encode($err);
				} else {
					return $err;
				}
			}
		}
		$r['content']['o']['success'] = true;
		$r['content']['o']['buildnumber'] = $r['buildnumber'];
		$r['content']['o']['sessionid'] = $r['sessionid'];
		if ($tojson) {
			return  $r['content']['o']; // TODO: тут надо сделать преобразование в JSON-формат
		} else {
			return  $r['content']['o']; 
		}
	}
	/**
	 * getLinked
	 */
	function getLinked($sp, $val, $id, $spr) {
		$s = array();
		//if ($val['n']==$spr) { // справочник
		if (isset($val['r'])) { // есть значение (связь с таблицей)
			if (is_array($val['r'])) {
				if (isset($val['r']['n']) && isset($val['r']['i']) && $val['r']['n']==$spr) {
					$s[] = array($sp.'_id'=>$id, $spr.'_id'=>$val['r']['i']);
				} else {
					foreach ($val['r'] as $ii => $iid) {
						$s[] = array($sp.'_id'=>$id, $spr.'_id'=>$iid);
					}
				}
			} else {
				$s[] = array($sp.'_id'=>$id, $spr.'_id'=>$val['r']);
			}
		} elseif (isset($val['f']) && is_array($val['f'])) { // для сборки связи справочника в справочнике (numericRanges)
			foreach ($val['f'] as $ii => $iid) {
				If (is_array($iid) && isset($iid['v']) && isset($iid['n']) && $iid['n']=='id')
				$s[] = array($sp.'_id'=>$id, $spr.'_id'=>$iid['v']);
			}
		}
		//}
		return $s;
	}
	/**
	 * getRackLinked
	 */
	function getRackLinked($sp, $val, $id, $spr) {
		$s = array();
		
		//if ($val['n']==$spr) { // справочник
		if (isset($val['f'])) { // есть значение (связь с таблицей)
			//print_r($val['f']);
			if (is_array($val['f'])) {
				$s[$sp.'_id'] = $id;
				foreach ($val['f'] as $ii => $row) {
					$s[$row['n']] = toAnsi($row['v']);
				}
			}
			$s = array($s);
		}
		//}
		return $s;
	}
	
	/**
	 * Получение данных определенных справочников
	 */
	function getDirectories() {
		if ($this->usePostgreLis) {
			$response = $this->lis->GET('LisUpdater/Directories');
			$this->ProcessRestResponse($response, 'single')->ReturnData();
		} else {
			// только определенные справочники
			$spr = array('test', 'target', 'workPlace', 'organization', 'equipment', 'department', 'bioMaterial', 'requestForm', 'patientGroup', 'defectType'/*, 'numericRanges'*/);
			$sprfields = array(
				'test' => array('resultType', 'duration', 'format', 'needPrint', 'autoApproval', 'hotKey', 'rank', 'exponential', 'showAllNorms', 'workingDays', 'useDefaultNormComments', 'normsText', 'viewPriority', 'useReagent', 'engName', 'name', 'code', 'mnemonics', 'removed', 'id', 'initValueToDefaultOnManualAdd', 'sortByRank', 'oldStyleResultsAdd'),
				'target' => array('needPrint', 'doctorLoad', 'labAssistLoad', 'registLoad', 'cito', 'cancelled', 'engName', 'name', 'code', 'mnemonics', 'removed', 'id'),
				'workPlace' => array('name', 'code', 'mnemonics', 'removed', 'id', 'organization_id'),
				'equipment' => array('useDepartmentNr', 'skipShowInProcessView', 'lotCount', 'positionCount', 'lotNumeringType', 'positionNumeringType', 'saveAlgorithm', 'needReverseProcess', 'autoWorkAdd', 'oldDriver', 'allowWorkLists', 'allowWorkJournal', 'queryMode', 'resultsMode', 'sendPositionAsCoordinates', 'autoChangeWorkStateOnQuery', 'driverSettings', 'driverId', 'name', 'code', 'mnemonics', 'removed', 'id'),
				'department' => array('useDepartnmentNrPrefix', 'prefixNrTemlate', 'defaultNormLowCriticalComment', 'defaultNormLowComment', 'defaultNormNormalComment', 'defaultNormHighComment', 'defaultNormHighCriticalComment', 'micro', 'useMyelogram', 'useSampleJournal', 'allowDepartmentNr', 'skipShowInProcessView', 'allBatchWorklists', 'departmentNrPeriod', 'useExternalNr', 'externalNrTemplate',
					'externalNrOffset', 'publishReportOnRequestApprove', 'requestApproveReportNameTemplate', 'publishReportOnRequestCancel', 'requestCancelReportNameTemplate', 'publishReportOnResultApprove',
					'resultApproveReportNameTemplate', 'publishReportOnResultCancel', 'resultCancelReportNameTemplate', 'resultApproveWorkStatesInt', 'resultCancelWorkStatesInt', 'defaultSampleCardPage', 'minDepartmentNrValue',
					'maxDepartmentNrValue', 'sampleDepartmentNrCanEdit', 'name', 'code', 'mnemonics', 'removed', 'id', 'organization_id'),
				'bioMaterial' => array('name', 'code', 'mnemonics', 'removed', 'id'),
				'organization' => array('name', 'code', 'mnemonics', 'removed', 'id'),
				'requestForm' => array('gridRows','needBiomaterial','needPassportData','requestNrTemplate','batchMode','printBatchSampleBarcodes','printBatchWorklistBarcodes','canSelectTargets','showBatchButton','showGoToButton','showPrintBarcodeButton',
					'showNewButton','showCopyButton','showResetButton','showSource','showEstimatedTime','priorityMode','showSamples','saveHospital','showPriority','fullNamePatientSearch','name','code','mnemonics','removed','id','createPatientAndCardIfNotFound','showSinglePatient','showSinglePatientCard','orderForm','controlsFormLayout','description'),
				'patientGroup' => array('sex', 'cyclePeriod', 'engName', 'ageStart', 'ageEnd', 'pregnancyStart', 'pregnancyEnd', 'ageUnit','name', 'code', 'mnemonics','removed', 'id'),
				'defectType' => array('workComment', 'allBiomaterials', 'skipService', 'name', 'code', 'pregnancyStart', 'removed', 'id'),
				'numericRanges' => array('point1', 'point2', 'point3', 'point4', 'point5', 'name1', 'name2', 'name3', 'name4', 'name5', 'engName1', 'engName2', 'engName3', 'engName4', 'engName5', 'normName', 'engNormName', 'id', 'patientGroup_id'),
				'testMappings' => array('code', 'test_id', 'equipment_id', 'qcTestCode', 'id')
			);
			$this->load->model('Lis_model', 'Lis_model');

			$data = $this->ProcessInputData(null, true);
			// Первоначально устанавливаем данные от кого должен выполняться запрос к ЛИС-серверу (берем данные из конфига)
			$logindata = array(
				'login'=>$this->server['login'],
				'password'=>$this->server['password'],
				'clientId'=>$this->server['clientid']
			);
			if (!isset($logindata['login'])) {
				echo json_encode(array('success'=>false,'Error_Msg'=>toUtf('Не заполнены настройки подключения к ЛИС (логин, пароль, client id)<br/> для синхронизации справочников.<br/>Пожалуйста, обратитесь к системным администраторам.')));
				return false;
			}
			// проверяем связь с ЛИС
			if (!$this->Lis_model->isLogon()) {
				// логинимся, если все хорошо
				$this->Lis_model->login($logindata);
			}
			if ($this->Lis_model->isLogon()) {
				// получаем набор предопределенных справочников из ЛИС
				foreach ($spr as $name) {
					$this->getDirectoryFromXMLFile($this->getDirectory($name), $name, $sprfields[$name]);
				}
				echo json_encode(array('success'=>true));
			} else {
				echo json_encode(array('success'=>false,'Error_Msg'=>toUtf('Не удалось авторизироваться в ЛИМС-системе. <br/>Пожалуйста, обратитесь к разработчикам.')));
			}
		}
	}
	
	/** 
	 * Функция создает справочники lis в Промед по описанию
	 */
	function testDirectories() {
		if ($this->usePostgreLis) {
			$response = $this->lis->POST('LisUpdater/Directories');
			$this->ProcessRestResponse($response, 'single')->ReturnData();
		} else {
			// только определенные справочники
			$spr = array('test', 'target', 'workPlace', 'organization', 'equipment', 'department', 'bioMaterial', 'requestForm', 'patientGroup', 'defectType', 'numericRanges');
			$sprfields = array(
				'test' => array('resultType', 'duration', 'format', 'needPrint', 'autoApproval', 'hotKey', 'rank', 'exponential', 'showAllNorms', 'workingDays', 'useDefaultNormComments', 'normsText', 'viewPriority', 'useReagent', 'engName', 'name', 'code', 'mnemonics', 'removed', 'id', 'initValueToDefaultOnManualAdd', 'sortByRank', 'oldStyleResultsAdd'),
				'target' => array('needPrint', 'doctorLoad', 'labAssistLoad', 'registLoad', 'cito', 'cancelled', 'engName', 'name', 'code', 'mnemonics', 'removed', 'id'),
				'workPlace' => array('name', 'code', 'mnemonics', 'removed', 'id', 'organization_id'),
				'equipment' => array('useDepartmentNr', 'skipShowInProcessView', 'lotCount', 'positionCount', 'lotNumeringType', 'positionNumeringType', 'saveAlgorithm', 'needReverseProcess', 'autoWorkAdd', 'oldDriver', 'allowWorkLists', 'allowWorkJournal', 'queryMode', 'resultsMode', 'sendPositionAsCoordinates', 'autoChangeWorkStateOnQuery', 'driverSettings', 'driverId', 'name', 'code', 'mnemonics', 'removed', 'id'),
				'department' => array('useDepartnmentNrPrefix', 'prefixNrTemlate', 'defaultNormLowCriticalComment', 'defaultNormLowComment', 'defaultNormNormalComment', 'defaultNormHighComment', 'defaultNormHighCriticalComment', 'micro', 'useMyelogram', 'useSampleJournal', 'allowDepartmentNr', 'skipShowInProcessView', 'allBatchWorklists', 'departmentNrPeriod', 'useExternalNr', 'externalNrTemplate',
					'externalNrOffset', 'publishReportOnRequestApprove', 'requestApproveReportNameTemplate', 'publishReportOnRequestCancel', 'requestCancelReportNameTemplate', 'publishReportOnResultApprove',
					'resultApproveReportNameTemplate', 'publishReportOnResultCancel', 'resultCancelReportNameTemplate', 'resultApproveWorkStatesInt', 'resultCancelWorkStatesInt', 'defaultSampleCardPage', 'minDepartmentNrValue',
					'maxDepartmentNrValue', 'sampleDepartmentNrCanEdit', 'name', 'code', 'mnemonics', 'removed', 'id', 'organization_id'),
				'bioMaterial' => array('name', 'code', 'mnemonics', 'removed', 'id'),
				'organization' => array('name', 'code', 'mnemonics', 'removed', 'id'),
				'requestForm' => array('gridRows','needBiomaterial','needPassportData','requestNrTemplate','batchMode','printBatchSampleBarcodes','printBatchWorklistBarcodes','canSelectTargets','showBatchButton','showGoToButton','showPrintBarcodeButton',
					'showNewButton','showCopyButton','showResetButton','showSource','showEstimatedTime','priorityMode','showSamples','saveHospital','showPriority','fullNamePatientSearch','name','code','mnemonics','removed','id','createPatientAndCardIfNotFound','showSinglePatient','showSinglePatientCard','orderForm','controlsFormLayout','description'),
				'patientGroup' => array('sex', 'cyclePeriod', 'engName', 'ageStart', 'ageEnd', 'pregnancyStart', 'pregnancyEnd', 'ageUnit','name', 'code', 'mnemonics','removed', 'id'),
				'defectType' => array('workComment', 'allBiomaterials', 'skipService', 'name', 'code', 'pregnancyStart', 'removed', 'id'),
				'numericRanges' => array('point1', 'point2', 'point3', 'point4', 'point5', 'name1', 'name2', 'name3', 'name4', 'name5', 'engName1', 'engName2', 'engName3', 'engName4', 'engName5', 'normName', 'engNormName', 'id', 'patientGroup_id'),
				'testMappings' => array('code', 'test_id', 'equipment_id', 'qcTestCode', 'id')
			);
			$this->load->model('Lis_model', 'Lis_model');

			$data = $this->ProcessInputData(null, true);
			foreach ($spr as $name) {
				$s = array(0=>array());
				foreach ($sprfields[$name] as $key) {
					$s[0][$key] = null;
				}
				$this->dbmodel->createTableFromArray($s, "_".$name);
			}
		}
	}
	
	/**
	 * Тест из ранее сохраненного файла
	 */
	function testDir() {
		$f = fopen("d:\\\\lis\\requestForm.xml", 'r');
		$body='';
		while (!feof($f)) {
			$body.=fread($f,8192);
		}
		fclose($f);
		$this->getDirectoryFromXMLFile($body, 'requestForm', array('gridRows','needBiomaterial','needPassportData','requestNrTemplate','batchMode','printBatchSampleBarcodes','printBatchWorklistBarcodes','canSelectTargets','showBatchButton','showGoToButton','showPrintBarcodeButton','showNewButton','showCopyButton','showResetButton','showSource','showEstimatedTime','priorityMode','showSamples','saveHospital','showPriority','fullNamePatientSearch','name','code','mnemonics','removed','id','createPatientAndCardIfNotFound','showSinglePatient','showSinglePatientCard','orderForm','controlsFormLayout','description'));
	}
	
	/** 
	 * Функция получения данных из полученного справочника XML
	 */
	function getDirectoryFromXMLFile(&$xml, $xmlname, $headers, $data_array = array()){
		// раскладываем XML в массивы, если не передан сам массив
		$result_array = (count($data_array)==0)?$this->getXmlResponse($xml, false):$data_array;//die();
		// сохраняем в XML файл для проверки
		/*$f = fopen("d:\\\\lis\\".$xmlname.".xml", 'w+');
		fwrite($f, $xml);
		fclose($f);
		*/
		
		$main = array();
		$targets = array();
		$numericRanges = array();
		$numericRangesSpr = array(); // сам справочник отдельно, так как он собирается из test
		$testMappings = array();
		$testMappingsSpr = array(); // сам справочник отдельно, так как он собирается из test
		$equipments = array();
		$tests = array();
		$biomaterials = array();
		$departments = array();
		$racks = array();
		if (is_array($result_array)) {
			
			$i = 0;
			$rows = ((isset($result_array['s'])) && (isset($result_array['s']['o'])))?$result_array['s']['o']:null;
			
			// Если справочник возвращает одну запись, то содержит ее не в массиве 
			$count_rows = 0; 
			if (count($rows)>0) {
				$count_rows = (isset($rows[0]))?count($rows):1;
			} 
			for($i=0; $i<$count_rows ; $i++) {
				//foreach ($rows as $key => $row) {
				if (isset($rows[$i])) {
					$row = $rows[$i];
				} else {
					$row = $rows;
				}
				$main[$i] = array();
				//print " ".$i." ";
				if (is_array($row)) {
				
					$id = null;
					if (isset($row['f']) && is_array($row['f'])) { // поля 
						$vv = ''; $nn = '';
						foreach ($row['f'] as $k => $field) {
							if (!is_array($field)) { // это глюк разборщика, который еще надо побороть, и по факту это - один элемент
								if ($k == 'n')
									$nn =  $field;
								if ($k == 'v')
									$vv =  $field;
							}
							if (is_array($field) && isset($field['n'])) { // заполняем основной массив 
								$main[$i][$field['n']] = @toAnsi($field['v']); 
							}
						}
						if (!empty($nn)) { // добавляем "не получившийся" элемент
							$main[$i][$nn] = @toAnsi($vv); 
						}
						// определяем Id текущей строки для основного массива
						$id = (isset($main[$i]['id']))?$main[$i]['id']:null;
					}
					
					/* конкретно для workPlace */
					if ($xmlname=='workPlace' || $xmlname=='department') {
						/*if (isset($row['r'])) { // идентификаторы Оргов
							if (isset($row['r']['n'])) {
								if (isset($row['r']['n']['organization'])) {
									$main[$i]['organization_id'] = $row['r']['n']['organization'];
								}
								if (isset($row['r']['n']['layout'])) {
									$main[$i]['layout_id'] = $row['r']['n']['layout'];
								}
								if (isset($row['r']['n']['laboratory'])) {
									$main[$i]['laboratory_id'] = $row['r']['laboratory'];
								}
								if (isset($row['r']['n']['test'])) {
									$main[$i]['test_id'] = $row['r']['n']['test'];
								}
							}
						}*/
						// получаем данные по справочникам по-новому
						if (isset($row['organization'])) {
							$main[$i]['organization_id'] = $row['organization'];
						}
						if (isset($row['layout'])) {
							$main[$i]['layout_id'] = $row['layout'];
						}
						if (isset($row['laboratory'])) {
							$main[$i]['laboratory_id'] = $row['laboratory'];
						}
						if (isset($row['test'])) {
							$main[$i]['test_id'] = $row['test'];
						}
					}
					/* конкретно для numericRanges */
					if ($xmlname=='numericRanges') {
						/*if (isset($row['r']) && $row['r']['n'] == 'patientGroup') {
							$main[$i]['patientGroup_id'] = $row['r']['i'];
						}*/
						if (isset($row['patientGroup'])) {
							$main[$i]['patientGroup_id'] = $row['patientGroup'];
						}
						if (isset($row['r']) && is_array($row['r'])) {
							if (isset($row['r']['patientGroup'])) {
								$main[$i]['patientGroup_id'] = $row['r']['patientGroup'];
							}
						}
					}
					/* конкретно для testMappings */
					if ($xmlname=='testMappings' && isset($row['r'])) {
						/*if (isset($row['r']) && $row['r']['n'] == 'test') {
							$main[$i]['test_id'] = $row['r']['i'];
						}
						if (isset($row['r']) && $row['r']['n'] == 'equipment') {
							$main[$i]['equipment_id'] = $row['r']['i'];
						}*/
						if (isset($row['r']) && is_array($row['r'])) {
							if (isset($row['r']['test'])) {
								$main[$i]['test_id'] = $row['r']['test'];
							}
							if (isset($row['r']['equipment'])) {
								$main[$i]['equipment_id'] = $row['r']['equipment'];
							}
						}
					}
					if (isset($row['s']) && is_array($row['s'])) { // справочники 
						$row_in = $row['s'];
						//print_r($row_in);die();
						if (isset($row['s']['o']) && is_array($row['s']['o'])) { // если объекты
							$row_in = $row['s']['o'];
							/*if (isset($row_in['n']) && $row_in['n']=='testMappings') {
								print_r($row_in);die();
							}*/
						}
						foreach ($row['s'] as $k => $val) {
							if (isset($val['s']) && is_array($val['s'])/* && isset($val['s']['o']) && is_array($val['s']['o'])*/) {
								if (isset($val['s']['n']) && in_array($val['s']['n'], array('targets')) && isset($val['s']['o']) && is_array($val['s']['o'])) {
									foreach ($val['s']['o'] as $kt => $vt) {
										if ($val['s']['n']=='targets') {
											$targets = array_merge($targets, $this->getLinked($xmlname, $vt, $id, 'target'));
										}
										if ($val['s']['n']=='numericRanges') { // это не работает
											$numericRanges = array_merge($numericRanges, $this->getLinked($xmlname, $vt, $id, 'numericRanges'));
											if ($xmlname=='test') {
												$numericRangesSpr[] = $vt['f'];
											}
										}
										if ($val['s']['n']=='testMappings') { // это не работает
											$testMappings = array_merge($testMappings, $this->getLinked($xmlname, $vt, $id, 'testMappings'));
											if ($xmlname=='test') {
												$testMappingsSpr[] = $vt['f'];
											}
										}
										if ($val['s']['n']=='equipments') {
											$equipments = array_merge($equipments, $this->getLinked($xmlname, $vt, $id, 'equipment'));
										}
										if ($val['s']['n']=='tests') {
											$tests = array_merge($tests, $this->getLinked($xmlname, $vt, $id, 'tests'));
										}
										if ($val['s']['n']=='biomaterials') {
											$biomaterials = array_merge($biomaterials, $this->getLinked($xmlname, $vt, $id, 'biomaterial'));
										}
										if ($val['s']['n']=='departments') {
											$departments = array_merge($departments, $this->getLinked($xmlname, $vt, $id, 'department'));
										}
										if ($val['s']['n']=='racks') {
											$racks = array_merge($racks, $this->getLinked($xmlname, $vt, $id, 'racks'));
										}
									}
								}
							}
							/*
							if (isset($val['o']) && is_array($val['o'])) {
								if (isset($val['o']['s']) && isset($val['o']['s']['o']) && is_array($val['o']['s']['o'])) { // справочники 
									foreach ($val['o']['s']['o'] as $kt => $vt) {
										// для test надо вычленять numericRanges и складывать отдельно 
										
										if (isset($vt['n'])) {
											if ($vt['n']=='targets') {
												$targets = array_merge($targets, $this->getLinked($xmlname, $vt, $id, 'target'));
											}
											if ($vt['n']=='numericRanges') { // это не работает
												$numericRanges = array_merge($numericRanges, $this->getLinked($xmlname, $vt, $id, 'numericRanges'));
												if ($xmlname=='test') {
													$numericRangesSpr[] = $vt['f'];
												}
											}
											if ($vt['n']=='testMappings') { // это не работает
												$testMappings = array_merge($testMappings, $this->getLinked($xmlname, $vt, $id, 'testMappings'));
												if ($xmlname=='test') {
													$testMappingsSpr[] = $vt['f'];
												}
											}
											if ($vt['n']=='equipments') {
												$equipments = array_merge($equipments, $this->getLinked($xmlname, $vt, $id, 'equipment'));
											}
											if ($vt['n']=='tests') {
												$tests = array_merge($tests, $this->getLinked($xmlname, $vt, $id, 'tests'));
											}
											if ($vt['n']=='biomaterials') {
												$biomaterials = array_merge($biomaterials, $this->getLinked($xmlname, $vt, $id, 'biomaterial'));
											}
											if ($vt['n']=='departments') {
												$departments = array_merge($departments, $this->getLinked($xmlname, $vt, $id, 'department'));
											}
											if ($vt['n']=='racks') {
												$racks = array_merge($racks, $this->getLinked($xmlname, $vt, $id, 'racks'));
											}
										}
									}
								}
							}
							*/
							if (isset($val['n'])) {
								// связи со справочниками
								//print $val['n']."<br/>";
								/*if ($val['n']=='targets') { // исследования 
									$target = array_merge($target, getLinked($val, 'targets'));
								}*/
								if ($val['n']=='targets') {
									$targets = array_merge($targets, $this->getLinked($xmlname, $val, $id, 'target'));
								}
								if ($val['n']=='numericRanges') { // это работает
									$numericRanges = array_merge($numericRanges, $this->getLinked($xmlname, $val, $id, 'numericRanges'));
									// помимо связи нужно сохранить и сам справочник numericRanges, так как он хранится в тестах
									if ($xmlname=='test') {
										if (isset($val['o'])) {
											foreach ($val['o'] as $k=>$v) {
												$numericRangesSpr[] = $v;
											}
										} else {
											$numericRangesSpr[] = $val;
										}
									}
									//print_r($val);echo " ... ";die();
								}
								if ($val['n']=='testMappings') { // это работает
									$testMappings = array_merge($testMappings, $this->getLinked($xmlname, $val, $id, 'testMappings'));
									if ($xmlname=='test') {
										if (isset($val['o'])) {
											$tMaps = array();
											if (isset($val['o']['f'])) { // если объект содержит 1 элемент
												$tMaps[0] = $val['o'];
											} elseif (isset($val['o'][0])) {
												$tMaps = $val['o'];
											}
											foreach ($tMaps as $k=>$v) {
												if (is_array($v)) {
													$testMappingsSpr[] = $v;
												}
											}
										} else {
											if (is_array($val) && count($val)>1) { // все, кроме пустых
												$testMappingsSpr[] = $val;
											}
										}
									}
								}
								if ($val['n']=='equipments') {
									$equipments = array_merge($equipments, $this->getLinked($xmlname, $val, $id, 'equipment'));
								}
								if ($val['n']=='tests') {
									$tests = array_merge($tests, $this->getLinked($xmlname, $val, $id, 'tests'));
								}
								if ($val['n']=='biomaterials') {
									$biomaterials = array_merge($biomaterials, $this->getLinked($xmlname, $val, $id, 'biomaterial'));
								}
								if ($val['n']=='departments') {
									$departments = array_merge($departments, $this->getLinked($xmlname, $val, $id, 'department'));
								}
								if ($val['n']=='racks') {
									$racks = array_merge($racks, $this->getRackLinked($xmlname, $val['o'], $id, 'racks'));
								}
								/*if ($val['n']=='departments') {
									$departments = array_merge($departments, $this->getLinked($xmlname, $val, $id, 'departments'));
								}*/
							}
						}
					}
				}
			}
		}
		// для тестов сохраняем numericRangesSpr, если есть.
		if ($xmlname=='test' && count($numericRangesSpr)>0) {
			//print_r($numericRangesSpr);die();
			$this->getDirectoryFromXMLFile($xml, 'numericRanges', array('point1', 'point2', 'point3', 'point4', 'point5', 'name1', 'name2', 'name3', 'name4', 'name5', 'engName1', 'engName2', 'engName3', 'engName4', 'engName5', 'normName', 'engNormName', 'id', 'patientGroup_id'), array('s'=>array('o'=>$numericRangesSpr)));
		}
		if ($xmlname=='test' && count($testMappingsSpr)>0) {
			//print_r($testMappingsSpr);die();
			$this->getDirectoryFromXMLFile($xml, 'testMappings', array('code', 'test_id', 'equipment_id', 'qcTestCode', 'id'), array('s'=>array('o'=>$testMappingsSpr)));
		}
		
		if (count($main)>0) {
			// определяем заголовки полей основного файла, если заголовки не определены заранее
			if (!isset($headers) || !is_array($headers)) {
				// старый вариант, когда заголовки определялись сами
				$headers = array();
				for($i=0; $i<count($main); $i++) {
					foreach ($main[$i] as $k => $v) {
						if (!in_array($k, $headers)) {
							$headers[] = $k;
						}
					}
				}
				
			} else {
				// новый вариант, заголовки в таблице известны
			}
			
			// дозаполняем пустые поля массива
			for($i=0; $i<count($main); $i++) {
				foreach ($headers as $k => $v) {
					if (!isset($main[$i][$v])) {
						$main[$i][$v] = '';
					}
				}
			}
			// составляем новый массив в котором поля в нужном порядке 
			$new = array();
			for($i=0; $i<count($main); $i++) {
				$new[$i] = array();
				foreach ($headers as $k => $v) {
					if (isset($main[$i][$v])) {
						$new[$i][$v] = $main[$i][$v];
					}
				}
			}
			//при желании здесь можно расскоментить для записи в csv
			//$f = fopen("d:\\\\lis\\".$xmlname.".csv", 'w+');
			//fputcsv($f, $headers, ";");
			foreach ($new as $line) {
				if (is_array($line)) {
					foreach ($line as $k=>$v) { // для проверки на возможность сохранить данные
						if (is_array($v)) {
							// если одна из переменных является массивом, то нужно сообщить об этом 
							$this->textlog->add('getDirectoryFromXMLFile: Ошибка данных справочника '.$xmlname.' - один из элементов является массивом: '.var_export($line, true));
							break;
						}
					}
				}
				//
				//fputcsv($f, $line, ";");
				
			}
			//fclose($f);
			// Сохраняем в таблицу
			$this->dbmodel->createTableFromArray($new, "_".$xmlname);
		}
		
		$this->exportLinked($targets, $xmlname, 'targets');
		$this->exportLinked($numericRanges, $xmlname, 'numericRanges');
		$this->exportLinked($testMappings, $xmlname, 'testMappings');
		$this->exportLinked($equipments, $xmlname, 'equipments');
		$this->exportLinked($tests, $xmlname, 'tests');
		$this->exportLinked($biomaterials, $xmlname, 'biomaterials');
		$this->exportLinked($departments, $xmlname, 'departments');
		$this->exportLinked($racks, $xmlname, 'racks');
	}
	

	/** Функция получения данных из сохраненного справочника XML
	 */
	function getDirectoryFromXML(&$xml){
		// читаем XML файл из определенного пути 
		// $this->textlog->add('getDirectoryVersions: Формируем запрос в XML ');
		$data = $this->ProcessInputData('getDirectoryFromXML', true);
		$xml = file_get_contents('d:\\\\lis\\'.$data['name'].'.xml');
		// раскладываем XML в массивы
		$result_array = $this->getXmlResponse($xml, false);
		$main = array();
		$targets = array();
		$equipments = array();
		$tests = array();
		$biomaterials = array();
		$departments = array();
		$racks = array();
		if (is_array($result_array)) {
			
			$i = 0;
			$rows = ((isset($result_array['s'])) && (isset($result_array['s']['o'])))?$result_array['s']['o']:null;
			
			for($i=0; $i<count($rows); $i++) {
				//foreach ($rows as $key => $row) {
				$row = $rows[$i];
				$main[$i] = array();
				//print " ".$i." ";
				if (is_array($row)) {
					$id = null;
					if (isset($row['f']) && is_array($row['f'])) { // поля 
						foreach ($row['f'] as $k => $field) {
							
							if (isset($field['n'])) { // заполняем основной массив 
								
								$main[$i][$field['n']] = toAnsi($field['v']);
							}
						}
						// определяем Id текущей строки для основного массива
						$id = (isset($main[$i]['id']))?$main[$i]['id']:null;
					}
					
					/* конкретно для workPlace & department */
					if ($data['name']=='workPlace' || $data['name']=='department') {
						if (isset($row['r'])) { // идентификаторы
							$main[$i]['organization_id'] = $row['r'];
						}
					}
					/*if (isset($row['r']) && is_array($row['r'])) { // идентификаторы
						foreach ($row['r'] as $k => $field) {
							
							if (isset($field['n'])) { // заполняем основной массив 
								
								$main[$i][$field['n']] = $field['i'];
							}
						}
					}*/
					
					if (isset($row['s']) && is_array($row['s'])) { // справочники 
						$row_in = $row['s'];
						//print_r($row_in);die();
						if (isset($row['s']['o']) && is_array($row['s']['o'])) { // если объекты
							$row_in = $row['s']['o'];
						}
						foreach ($row['s'] as $k => $val) {
							if (isset($val['o'])) {
								if (isset($val['o']['s']) && isset($val['o']['s']['o']) && is_array($val['o']['s']['o'])) { // справочники 
									foreach ($val['o']['s']['o'] as $kt => $vt) {
										if ($vt['n']=='targets') {
											$targets = array_merge($targets, $this->getLinked($data['name'], $vt, $id, 'target'));
										}
										if ($vt['n']=='equipments') {
											$equipments = array_merge($equipments, $this->getLinked($data['name'], $vt, $id, 'equipment'));
										}
										if ($vt['n']=='tests') {
											$tests = array_merge($tests, $this->getLinked($data['name'], $vt, $id, 'tests'));
										}
										if ($vt['n']=='biomaterials') {
											$biomaterials = array_merge($biomaterials, $this->getLinked($data['name'], $vt, $id, 'biomaterial'));
										}
										if ($vt['n']=='departments') {
											$departments = array_merge($departments, $this->getLinked($data['name'], $vt, $id, 'department'));
										}
										if ($vt['n']=='racks') {
											$racks = array_merge($racks, $this->getLinked($data['name'], $vt, $id, 'racks'));
										}
									}
								}
							}
						
							
							if (isset($val['n'])) {
								// связи со справочниками
								//print $val['n']."<br/>";
								/*if ($val['n']=='targets') { // исследования 
									$target = array_merge($target, getLinked($val, 'targets'));
								}*/
								if ($val['n']=='targets') {
									$targets = array_merge($targets, $this->getLinked($data['name'], $val, $id, 'target'));
								}
								if ($val['n']=='equipments') {
									$equipments = array_merge($equipments, $this->getLinked($data['name'], $val, $id, 'equipment'));
								}
								if ($val['n']=='tests') {
									$tests = array_merge($tests, $this->getLinked($data['name'], $val, $id, 'tests'));
								}
								if ($val['n']=='biomaterials') {
									$biomaterials = array_merge($biomaterials, $this->getLinked($data['name'], $val, $id, 'biomaterial'));
								}
								if ($val['n']=='departments') {
									$departments = array_merge($departments, $this->getLinked($data['name'], $val, $id, 'department'));
								}
								if ($val['n']=='racks') {
									$racks = array_merge($racks, $this->getRackLinked($data['name'], $val['o'], $id, 'racks'));
								}
								/*if ($val['n']=='departments') {
									$departments = array_merge($departments, $this->getLinked($data['name'], $val, $id, 'departments'));
								}*/
								
								/*
								if ($val['n']=='targets') { // исследования 
									if (isset($val['r'])) { // есть значение 
										if (is_array($val['r'])) {
											foreach ($val['r'] as $ii => $iid) {
												$targets[] = array('id'=>$id, 'target_id'=>$iid);
											}
										} else {
											$targets[] = array('id'=>$id, 'target_id'=>$val['r']);
										}
									} 
								}*/
							}
							
							/*if (isset($field['n'])) { // заполняем основной массив 
								$main[$i][$field['n']] = toAnsi($field['v']);
							}*/
						}
					}
				
					/*foreach ($rows[$i] as $k => $v) {
						if (is_array($v)) {
							print " 4=".$k." <br/>";
							
							if ($k == 's') { // fields 
								foreach ($v as $kf => $field) {
									print_r($field);
								}
							}
							if ($k == 'f') { // fields 
								foreach ($v as $kf => $field) {
									print $kf;
									if (isset($field['n'])) { // заполняем основной массив 
										$main[$i][$field['n']] = toAnsi($field['v']);
									}
								}
								// определяем Id текущей строки для основного массива
								$id = (isset($main[$i]['id']))?$main[$i]['id']:null;
							}
							
							
							
							//print_r($v);
							if ($i>=15) {
								print_r($main);
								die('alles');
							}
							
						} else {
							print " != ".$v;
						}
					}*/
				}
			}
		}
		//print_r($main);
		$f = fopen("d:\\\\lis\\".$data['name'].".csv", 'w+');
		/*foreach ($main as $line) {
			fputcsv($f, $line, ";");
		}
		fclose($f);
		die();
		*/
		
		if (count($main)>0) {
			// определяем заголовки полей основного файла 
			$headers = array();
			for($i=0; $i<count($main); $i++) {
				foreach ($main[$i] as $k => $v) {
					if (!in_array($k, $headers)) {
						$headers[] = $k;
					}
				}
			}
			// дозаполняем пустые поля массива
			for($i=0; $i<count($main); $i++) {
				foreach ($headers as $k => $v) {
					if (!isset($main[$i][$v])) {
						$main[$i][$v] = '';
					}
				}
			}
			// составляем новый массив в котором поля в нужном порядке 
			$new = array();
			for($i=0; $i<count($main); $i++) {
				$new[$i] = array();
				foreach ($headers as $k => $v) {
					if (isset($main[$i][$v])) {
						$new[$i][$v] = $main[$i][$v];
					}
				}
			}
			// Сохраняем в таблицу
			$this->dbmodel->createTableFromArray($main, "_".$data['name']);
			
			/*foreach($main[0] as $key => $value){
				$header[] = $key;
			}*/
			fputcsv($f, $headers, ";");
			foreach ($new as $line) {
				fputcsv($f, $line, ";");
			}
		}
		fclose($f);
		
		// Сохраняем массивы в файлы (в таблицы)
		/*if (count($targets)>0) {
			foreach($targets[0] as $key => $value){
				$header[] = $key;
			}
			$f = fopen($data['name']."_targets.csv", 'w+');
			fputcsv($f, $header, ";");
			foreach ($targets as $line) {
				fputcsv($f, $line, ";");
			}
			fclose($f);
		}*/
		
		$this->exportLinked($targets, $data['name'], 'targets');
		$this->exportLinked($equipments, $data['name'], 'equipments');
		$this->exportLinked($tests, $data['name'], 'tests');
		$this->exportLinked($biomaterials, $data['name'], 'biomaterials');
		$this->exportLinked($departments, $data['name'], 'departments');
		$this->exportLinked($racks, $data['name'], 'racks');
	}
	/**
	 * exportLinked
	 */
	function exportLinked($spr, $xmlname, $name, $isCreateXml = false) {
		// Сохраняем массив в таблицу 
		$this->dbmodel->createTableFromArray($spr, "_".$xmlname."_".$name);
		if ($isCreateXml) {
			// Сохраняем массивы в файлы
			$header = array();
			if (count($spr)>0) {
				foreach($spr[0] as $key => $value){
					$header[] = $key;
					// создаем таблицу для вставки
					
				}
				$f = fopen("d:\\\\lis\\".$xmlname."_".$name.".csv", 'w+');
				fputcsv($f, $header, ";");
				foreach ($spr as $line) {
					//fputcsv($f, $line, ";");
					// вставка в таблицу в БД 
				}
				fclose($f);
			}
		}
	}
	
	
	/** 
	 * Запрос directory-versions. Предназначен для получения от сервера информации о текущих версиях используемых справочников.
	 * @return bool
	 */
	function getDirectoryVersions() {
		if ($this->usePostgreLis) {
			$response = $this->lis->GET('LisUpdater/DirectoryVersions');
			$this->ProcessRestResponse($response, 'single')->ReturnData();
		} else {
			$this->textlog->add('getDirectoryVersions: Запуск');
			$this->ProcessInputData('getDirectoryVersions', true, false);
			if (!$this->isLogon()) {
				$this->login();
			}
			if ($this->isLogon()) {

				// Данные запроса
				$request = array();
				$this->textlog->add('getDirectoryVersions: Формируем запрос в XML ');
				// Формируем запрос в XML
				$xml = $this->setXmlRequest('directory-versions', $request);
				$response = $this->request($xml);
				$this->textlog->add('getDirectoryVersions: Получили ответ ');
				if (strlen($response)>0) {
					$this->textlog->add('getDirectoryVersions: Ответ не пустой ');
					$arr = $this->getXmlResponse($response, false);
					$this->textlog->add('getDirectoryVersions: Распарсили ответ ');
					// Обрабатываем ответ
					if (is_array($arr)) {
						if ($arr['success'] === true) {
							$this->textlog->add('getDirectoryVersions: Ответ хороший, список справочников получен ');
							// дальше разбираем полученный ответ и получаем список справочников
							// Если количество справочников больше нуля
							//$d = array();
							$s = array();
							$d = $arr['s']['o'];
							if (count($d)>0) {
								$this->textlog->add('getDirectoryVersions: Всего справочников получено '.count($d));
								for($i=0; $i<count($d); $i++) {
									$s[$i] = array();
									$s[$i]['name'] = $d[$i]['f'][0]['v'];
									$s[$i]['version'] = $d[$i]['f'][1]['v'];
									//print $s[$i]['name'].' - '.$s[$i]['version'];

									// получаем все справочники в XML формате
									$this->textlog->add('getDirectoryVersions: Справочник '.$s[$i]['name'].' (версия '.$s[$i]['version'].') готов к загрузке в XML-формате');
									$records = $this->getDirectory($s[$i]);

									if (in_array(strtolower($s[$i]['name']),$this->dirs, true)) {
										$this->textlog->add('getDirectoryVersions: Справочник '.$s[$i]['name'].' (версия '.$s[$i]['version'].') готов к загрузке в БД');
										$records = $this->getDirectory($s[$i]);
										$this->saveDirectory($s[$i]['name'], $records);
									} else {
										$this->textlog->add('getDirectoryVersions: Справочник '.$s[$i]['name'].' (версия '.$s[$i]['version'].') не входит в список разрешенных к загрузке в БД справочников');
									}
								}
							}
						} else {
							// ошибка
							$this->textlog->add('getDirectoryVersions: Ошибка '.$arr['Error_Code'].''.toAnsi($arr['Error_Msg']));
						}
						//print_r($s);
					}
				} else {
					$this->textlog->add('getDirectoryVersions: Ответ пустой ');
				}
			}
			echo json_encode(array('success'=>true));
			$this->textlog->add('getDirectoryVersions: Финиш ');
		}
	}

	/** Запрос directory. Предназначен для получения от сервера содержимого справочников.
	 *
	 * @param $data
	 * @return array|bool|string
	 */
	function getDirectory($xmlname) {
		$this->load->model('Lis_model', 'Lis_model');
		$this->textlog->add('getDirectory: Запуск');
		$request = array();
		$request['name'] = $xmlname;
		// Формируем запрос в XML 
		$this->textlog->add('getDirectory: Хотим забрать справочник '.$xmlname);
		$xml = $this->Lis_model->setXmlRequest('directory', $request);
		$this->textlog->add('getDirectory: Формируем запрос в XML ');
		$response = $this->Lis_model->request($xml);
		if (strlen($response)>0) {
			$this->textlog->add('getDirectory: Успешно вернули данные справочника '.$xmlname);
			return $response;
		} else {
			$this->textlog->add('getDirectory: При получении данных справочника '.$xmlname.' произошла ошибка');
			return false;
		}
	}

	/** Сохранение справочника
	 *
	 * @param $name
	 * @param $data
	 * @return bool
	 */
	function saveDirectory($name, $data) {
		$this->textlog->add('saveDirectory: Сохраняем справочник '.$name);
		if (is_array($data)) {
			if (isset($data['Error_Msg'])) {
				$this->textlog->add('saveDirectory: Ошибка при сохранении справочника "'.$name.'": '.$data['Error_Code'].' - '.$data['Error_Msg']);
				return false;
			}
			for($i=0; $i<count($data); $i++) {
				$data[$i]['pmUser_id'] = $_SESSION['pmuser_id'];
				try {
					$response = $this->dbmodel->saveDirectory($name, $this->map, $data[$i]);
					$this->ProcessModelSave($response, true);
				} catch (Exception $e) {
					$this->textlog->add('ошибка сохранения элемента справочника '.$name.' '.var_export($data[$i], true));
				}
			}
			$this->textlog->add('saveDirectory: Сохранили все записи');
			return true;
		}
		return false;
	}
	
	/*###################Xml-помощники#########################*/
	/**
	 * Создает XMLWriter, который можно наполнить данными запроса. Будет включать всю необходимю информацию кроме тела запроса
	 * Для корректной работы требуется перед вызовом метода убедится что произведен вход в ЛИС
	 * Чтобы после добавления в документ нужной иформации корректно закончить его и получить XML, надо вызвать endXmlRequestWriterDocument
	 *
	 * @param string $type
	 * @return XMLWriter
	 */
	function xml_startXmlRequestWriterDocument($type){
		/*
		//Запрос к серверу имеет следующий вид:
		<?xml version="1.0" encoding="Windows-1251"?>
		<!DOCTYPE request SYSTEM "lims.dtd">
		<request type="Тип запроса" sessionid=" ID сессии" version="3.8" buildnumber="Версия релиза клиентского ПО">
			<content>
				Тело запроса
			</content>
		</request>
		*/
		if (is_null($this->sessionid)) {
			throw new Exception('Вход в ЛИС не выполнен');
		}
		$w = new XMLWriter();
		$w->openMemory();
		$w->setIndent(true);
		$w->setIndentString("\t");
		$w->startDocument("1.0", "Windows-1251");
		$w->startDTD('request', null, 'lims.dtd');
		$w->endDtd();
		$w->startElement('request');
		$w->writeAttribute('type', $type);
		$w->writeAttribute('sessionid', $this->sessionid);
		$w->writeAttribute('version', $this->server['version']);
		$w->writeAttribute('buildnumber', $this->server['buildnumber']);
		$w->startElement('content');
		return $w;
	}

	/**
	 * Закрывает XML-документ, ранее открытый методом startXmlRequestWriterDocument
	 * Возвращает сформированный документ в виде строки.
	 * @param XMLWriter $writer
	 * @return string
	 */
	function xml_endXmlRequestWriterDocument($writer){
		$writer->endElement();//закрываем content
		$writer->endElement();//закрываем request
		$writer->endDocument();//
		return $writer->outputMemory();
	}

	/**
	 * Добавит в XML вот примерно такое: <f t="i" n="id" v="11721537" />.
	 * Определяет тип переменной и устанавливает (c $include_type = true - по умолчнию) атрибут "t" (i - целое, d - дата, b - булево, s - строка)
	 * Дату необходимо передавать как объект Datetime.
	 *
	 * @param XMLWriter $w
	 * @param string $f_name
	 * @param mixed $f_value
	 * @param bool $include_type
	 * @throws Exception
	 * @return bool
	 */
	function xml_add_f($w, $f_name, $f_value, $include_type = true){
		$result = true;
		$result = $result && $w->startElement('f');
		$var_type = gettype($f_value);
		$t = '';
		$v = '';
		switch ($var_type) {
			case 'double':
			case 'integer':
				$t = 'i';
				$v = (string)$f_value;
				break;
			case 'object':
				if ($f_value instanceof Datetime) {
					/** @var Datetime $f_value */
					$t = 'd';
					$v = $f_value->format('d.m.Y H:i:s');
				} else {
					throw new Exception("Запись объекта класса в XML".get_class($f_value)." не поддерживается ($f_name = $f_value)");
				}
				break;
			case 'string':
				$t = 's';
				$v = $f_value;
				break;
			case 'boolean':
				$t = 'b';
				$v = $f_value?'true':'false';
				break;
			default:
				throw new Exception("Неизвестный тип переменной $var_type ($f_name = $f_value)");
		}
		if ($include_type) {
			$result = $result && $w->writeAttribute('t', $t);
		}
		if (!is_null($f_name)) {
			$result = $result && $w->writeAttribute('n', $f_name);
		}
		$result = $result && $w->writeAttribute('v', $v);
		$result = $result && $w->endElement();//f
		return $result;
	}

	/**
	 * @param XMLWriter $w
	 * @param string $userField_i
	 * @param mixed $value
	 */
	function xml_add_userValues_entry($w, $userField_i, $value) {
		$w->startElement('o');
		$w->startElement('r');
		$w->writeAttribute('n','userField');
		$w->writeAttribute('i', $userField_i);
		$w->endElement();//r
		$this->xml_add_f($w, 'value', $value);
		$w->endElement();
	}

	/**
	 * @param XMLWriter $w
	 * @param Array $userValues
	 * @throws Exception
	 */
	private function xml_add_userValues_block($w, $userValues)
	{
		$w->startElement('s');
		$w->writeAttribute('n', 'userValues');
		foreach ($userValues as $key => $value) {
			$this->xml_add_userValues_entry($w, $key, $value);
		}
		$w->endElement();//s
	}

	/**
	 * @param XMLWriter $w
	 * @param string $name
	 * @param array $values
	 * @param string $tag
	 * @throws Exception
	 */
	function xml_add_s($w, $name, $values, $tag = 'f') {
		$w->startElement('s');
		$w->writeAttribute('n', $name);
		if (is_array($values)) {
			foreach($values as $key => $value) {
				if (is_array($value)) {
					throw new Exception("xml_add_s: wrong type of '$name'['$key'] value - array");
				} else {
					switch ($tag) {
						case 'f':
							$this->xml_add_f($w, null, $value, true);
							break;
						case 'r':
							$w->startElement('r');
							$w->writeAttribute('i',$value);
							$w->endElement();//r
							break;
						default:
							throw new Exception('xml_add_s: tag "'.$tag.'" not supported');
							break;
					}
				}
			}
		} else {
			throw new Exception("xml_add_s: Элемент $name не является массивом");
		}
		$w->endElement();//s
	}

	/**
	 * @param XMLWriter $w
	 * @param string $name
	 * @param string $operator
	 * @param array $idList
	 * @param string $tag
	 */
	function xml_add_filter_o($w, $name, $operator, $idList, $tag = 'r'){
		$w->startElement('o');
		$w->writeAttribute('n', $name);
		if (!is_null($operator)) {
			$this->xml_add_f($w, 'operator', $operator, false);
			$this->xml_add_s($w, 'idList', $idList, $tag);
		}
		$w->endElement();//o
	}

	/**
	 * @param string $type
	 * @param array $filter
	 * @param $andOrListR
	 * @param $andOrListF
	 * @param array $collectionsR
	 * @param $collectionsF
	 * @param array $fieldsR
	 * @throws Exception
	 * @return string
	 */
	function xml_composeFilter($type, $filter, $andOrListR, $andOrListF, $collectionsR, $collectionsF, $fieldsR){
		$w = $this->xml_startXmlRequestWriterDocument($type);
		$w->startElement('o');
		$w->writeAttribute('n', 'filter');
		foreach($filter as $filter_name => $filter_value){
			if (in_array($filter_name, $andOrListR)) {
				if (isset($filter_value['operator']) && isset($filter_value['idList'])) {
					$this->xml_add_filter_o($w, $filter_name, $filter_value['operator'], $filter_value['idList']);
				} else {
					if (count($filter_value) === 0) {
						$this->xml_add_filter_o($w, $filter_name, null, array());
					} else {
						throw new Exception("Поскольку '$filter_name' ожидается как объект типа 'AndOrList', то элементы 'operator' и 'idList' в нем являются обязательными");
					}
				}
			} else {
				if (in_array($filter_name, $andOrListF)) {
					if (isset($filter_value['operator']) && isset($filter_value['idList'])) {
						$this->xml_add_filter_o($w, $filter_name, $filter_value['operator'], $filter_value['idList'], 'f');
					} else {
						if (count($filter_value) === 0) {
							$this->xml_add_filter_o($w, $filter_name, null, array(), 'f');
						} else {
							throw new Exception("Поскольку '$filter_name' ожидается как объект типа 'AndOrList', то элементы 'operator' и 'idList' в нем являются обязательными");
						}
					}
				} else {
					if (in_array($filter_name, $collectionsR)) {
						$this->xml_add_s($w, $filter_name, $filter_value, 'r');
					} else {
						if (in_array($filter_name, $fieldsR)) {
							$w->startElement('r');
							$w->writeAttribute('n', $filter_name);
							$w->writeAttribute('i', $filter_value);
							$w->endElement();//r
						} else {
							if (is_array($filter_value)) {
								$this->xml_add_s($w, $filter_name, $filter_value);
							} else {
								$this->xml_add_f($w, $filter_name, $filter_value, false);
							}
						}
					}

				}
			}
		}
		$w->endElement(); //o
		return $this->xml_endXmlRequestWriterDocument($w);
	}

	/**
	 * Ищет и перемещает курсор XML-считывателя на искомый элемент $element в пределах текущего элемента.
	 * Если указаны $n и $v, будет произведен поиск с учетом значений атрибутов 'n' и 'v'.
	 * В случае успеха возвращает true, иначе false.
	 * Регистр переменных $element и $n значения не имеет, в отличие от $v.
	 *
	 * @param XMLReader $r
	 * @param string $element
	 * @param string $n
	 * @param string $v
	 * @return bool
	 */
	function xml_read_moveTo($r, $element, $n = null, $v = null){
		$cur_el = $r->name;
		$cur_depth = $r->depth;
		if ($r->nodeType === XMLReader::ATTRIBUTE) {
			//если курсор на атрибуте - переходим на элемент
			$r->moveToElement();
		} else {
			//если курсор на другом элементе, переходим к первому элементу, который найдется при последовательном чтении
			while ($r->nodeType !== XMLReader::ELEMENT) {
				if (!$r->read()) {
					//на случай если окажемся в конце документа
					break;
				}
			}
		}
		do {
			$result = (($r->nodeType === XMLReader::ELEMENT) && (strtolower($r->name) === strtolower($element)));//является ли этот элемент искомым
			if ($result) {
				if (!is_null($n)) {//Нужно ли проверить значение атрибута n?
					$result = ($result && ($r->moveToAttribute('n') && (strtolower($r->value) === strtolower($n))));
				}
				if (!is_null($v)) {
					$result = ($result && ($r->moveToAttribute('v') && ($r->value === $v)));
				}
			}
			if ($result) {
				$r->moveToElement();
				break;
			} else {
				if (($r->nodeType === XMLReader::END_ELEMENT) && ($r->name === $cur_el) && ($cur_depth === $r->depth)) {
					//достигли конца текущего элемента
					break;
				}
			}
		} while ($r->read());
		return $result;
	}

	/**
	 * @param XMLReader $r
	 * @param $result
	 * @throws Exception
	 */
	private function xml_read_collect_f($r, &$result)
	{
		$t = 's';
		$v = null;
		$n = null;
		if ($r->moveToAttribute('n')) {
			$n = $r->value;
		} else {
			throw new Exception('У элемента f не задан атрибут n');
		}
		if ($r->moveToAttribute('t')) {
			$t = strtolower($r->value);
		}
		if ($r->moveToAttribute('v')) {
			switch($t){
				case 'b':
					if (strtolower($r->value) === 'true') {
						$v = true;
					} else {
						$v = false;
					}
					break;
				case 'i':
					$v = (int) $r->value;
					break;
				case 's':
					$v = (string) $r->value;
					break;
				case 'r':
					$v = (int) $r->value;
					break;
				case 'f':
					$v = (float) $r->value;
					break;
				case 'l':
					$v = (int) $r->value;
					break;
				case 'd':
					$v = Datetime::createFromFormat('d.m.Y H:i:s', $r->value);
					if ($v === false) {
						throw new Exception('Не удалось распознать дату: - '.$r->value);
					}
					break;
				default:
					throw new Exception('Неизвестное обозначение типа переменной - '.$t);
			}
		}
		if (!is_null($n)){
			$result[$n] = $v;
		}
	}

	/**
	 * @param XMLReader $r
	 * @throws Exception
	 * @return array
	 */
	private function xml_read_collect_userValues($r){
		//проверяем правильность установки позиции
		$ok =	(($r::ELEMENT === $r->nodeType)//курсор должен быть установлен на элемент
			&& ('s' === $r->name)// это должен быть элемент s
			&& $r->moveToAttribute('n') // у него должен быть атрибут n
			&& ('userValues' === $r->value));//значение атрибута должно быть = userValues
		if (!$ok) {
			throw new Exception('Парсер блока пользовательских данных: Курсор xml-считываля находится в неправильной позиции');
		} else {
			$r->moveToElement();
		}
		$result = array();
		if (!$r->isEmptyElement) {
			while ($r->read()){
				switch ($r->nodeType) {
					case $r::ELEMENT:
						if ('o' === $r->name) {
							if ($r->moveToAttribute('n')){
								if ('userValues' === $r->value) {
									$tmp = array();
									$key = null;
									while ($r->read()){
										if (($r->nodeType === XMLReader::ELEMENT) && ($r->name === 'f')) {
											$this->xml_read_collect_f($r, $tmp);
										}
										if (($r->nodeType === XMLReader::ELEMENT) && ($r->name === 'r')) {
											if ($r->moveToAttribute('n')) {
												switch ($r->value) {
													case 'userField':
														if ($r->moveToAttribute('i')){
															$key = $r->value;
														} else {
															throw new Exception('не найдено i у элемента r');
														}
														break;
													default:
														$n = $r->value;
														if ($r->moveToAttribute('i')) {
															$tmp[$n] = (int)$r->value;
														} else {
															$tmp[$n] = null;
														}
														break;
												}
											} else {
												throw new Exception('не найдено n у элемента r');
											}
										}
										if (($r->nodeType === XMLReader::END_ELEMENT) && ($r->name === 'o')) {
											//элемент o, содержаций userValues закончился
											break;
										}
									}
									if (!is_null($key)) {
										$result[$key] = $tmp;
									} else {
										throw new Exception('не указан userField у userValues');
									}
								}
							} else {
								throw new Exception('не найден атрибут "n" у элемента "o" в множестве userValues');
							}
						}
						break;
					case $r::END_ELEMENT:
						if ('s' === $r->name) {
							break 2;//выходим из switch и while
						}
				}
			}
		}
		return $result;
	}

	/**
	 * @param XMLReader $r
	 * @param string $s_name
	 * @throws Exception
	 * @return array
	 */
	function xml_read_collect_r($r, $s_name = 's'){
		$result = array();
		if (!$r->isEmptyElement) {
			while ($r->read()) {
				switch ($r->nodeType) {
					case $r::ELEMENT:
						if ('r' === $r->name) {
							if ($r->moveToAttribute('i')) {
								$result[] = (int)$r->value;
							} else {
								throw new Exception("Не найден атрибут i элемента r в $s_name");
							}
						} else {
							throw new Exception('Неожиданный элемент '.$r->name.' в '.$s_name);
						}
						break;
					case $r::END_ELEMENT:
						if ('s' === $r->name) {
							break 2;
						}
				}
			}
		}
		return $result;
	}

	/**
	 * @param XMLReader $r
	 * @throws Exception
	 * @return array
	 */
	public function xml_read_collect_works($r)	{
		$result = array();
		while ($r->read()) {
			switch ($r->nodeType) {
				case $r::ELEMENT:
					if ('o' === $r->name) {
						if ($r->moveToAttribute('n')) {
							if ('works' === $r->value) {
								$tmp = array();
								while ($r->read()) {
									switch ($r->nodeType) {
										case $r::ELEMENT:
											switch ($r->name) {
												case 'f':
													$this->xml_read_collect_f($r, $tmp);
													break;
												case 'r':
													if ($r->moveToAttribute('n')) {
														$key = $r->value;
														if ($r->moveToAttribute('i')) {
															$tmp[$key] = (int)$r->value;
														} else  {
															throw new Exception('Не найден атрибут i у элемента r');
														}
													} else {
														throw new Exception('Не найден атрибут n у элемента r');
													}
													break;
												case 's':
													if ($r->moveToAttribute('n')) {
														$s_name = $r->value;
														$r->moveToElement();
														$tmp[$s_name] = $this->xml_read_collect_r($r, $s_name);
													} else {
														throw new Exception('Не найден атрибут n у элемента s');
													}
													break;
												default:
													throw new Exception('Неожиданный элемент ' . $r->name . ' в <o n="works">');
													break;
											}
											break;
										case $r::END_ELEMENT:
											if ('o' === $r->name) {
												break 2;
											} else {
												throw new Exception('Неожиданный конец элемента ' . $r->name . 'в <o n="works">');
											}
									}
								}
								$result[] = $tmp;
							}
						} else {
							throw new Exception("Не найден атрибут n в элементе o");
						}
					} else {
						throw new Exception('Неожиданный элемент ' . $r->name . 'в <s n="works">');
					}
					break;
				case $r::END_ELEMENT:
					if ('s' === $r->name) {
						break 2;
					}
			}
		}
		return $result;
	}

	
}
