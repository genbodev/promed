<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * MobileBrig - контроллер мобильного арма старшего бригады, загрузка необходимых скриптов и стилей
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       SWAN Developers
 * @version      ?
 */
class MobileBrig extends swController {
	
	protected $inputRules = array(
		'getJSFile' => array(
			array(
				'field' => 'wnd',
				'label' => 'Файл',
				'rules' => 'required',
				'type' => 'string'
			)
		), 
		'loadFiles' => array(
			array(
				'field' => 'group',
				'label' => 'Группа',
				'rules' => '',
				'type' => 'string'
			)
		),
		'callAccepted' =>array(
			array(
				'field'=>'CmpCallCard_id',
				'label'=>'Ид вызова',
				'rules'=>'required',
				'type'=>'string'
			)
		),
		'getBrigInfo' => array(
		),
		'getEmergencyTeamData' => array(
		),
		'setOnlineStatus'=> array(
			array(
				'field'=>'EmergencyTeam_id',
				'label'=>'Ид бригады',
				'rules'=>'required',
				'type'=>'string'
			),
			array(
				'field'=>'isOnline',
				'label'=>'Код статуса ОнЛайн',
				'rules'=>'required',
				'type'=>'string'
			)
		),
		'setBrigStatus' => array(
			array(
				'field' => 'EmergencyTeamStatus_id',
				'label' => 'Код статуса',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'EmergencyTeam_id',
				'label' => 'Ид бригады',
				'rules' => 'required',
				'type' => 'string'
			)
		),
		'closeCallCard' => array(
			array(
				'field' => 'CmpCallCard_id',
				'label' => 'ID краты вызова',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Fam',
				'label' => 'Фамилия пациента',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Name',
				'label' => 'Имя пациента',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Middle',
				'label' => 'Отчество пациента',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Age',
				'label' => 'Возраст пациента',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Sex_id',
				'label' => 'ID пола пациента',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'GoTime',
				'label' => 'Время выезда на вызов',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'ArriveTime',
				'label' => 'Время прибытия на вызов',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'TransportTime',
				'label' => 'Время начала транспортировки',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'ToHospitalTime',
				'label' => 'Время прибытия в медицинскую организацию',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'BackTime',
				'label' => 'Время окончания вызова',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'SummTime',
				'label' => 'Время, затраченное на вызов',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'EndTime',
				'label' => 'Время окончания вызова',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'Work',
				'label' => 'Место работы',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'DocumentNum',
				'label' => 'Серия и номер документа, удостоверяющего',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'isAlco',
				'label' => 'Наличие клиники опьянения',
				'rules' => 'trim',
				'type' => 'int'
			),
			array(
				'field' => 'Complaints',
				'label' => 'Жалобы',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Anamnez',
				'label' => 'Анамнез',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Urine',
				'label' => 'Мочеиспускание',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Shit',
				'label' => 'Стул',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'OtherSympt',
				'label' => 'Другие симптомы',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'AD',
				'label' => 'АД до оказания медицинской помощи',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Chss',
				'label' => 'ЧСС до оказания медицинской помощи',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Pulse',
				'label' => 'Пульс до оказания медицинской помощи',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Temperature',
				'label' => 'Температура до оказания медицинской помощи',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Chd',
				'label' => 'ЧД до оказания медицинской помощи',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Pulsks',
				'label' => 'Пульсоксиметрия до оказания медицинской помощи',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Gluck',
				'label' => 'Глюкометрия до оказания медицинской помощи',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'WorkAD',
				'label' => 'Рабочее АД',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Gluck',
				'label' => 'Глюкометрия до оказания медицинской помощи',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'LocalStatus',
				'label' => 'Локальный статус',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Ekg1',
				'label' => 'ЭКГ до оказания медицинской помощи',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Ekg2',
				'label' => 'ЭКГ после оказания медицинской помощи',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Ekg1Time',
				'label' => 'Время проведения ЭКГ до оказания мед. помощи',
				'rules' => 'trim',
				'type' => 'datetime'
			),
			array(
				'field' => 'Ekg2Time',
				'label' => 'Время проведения ЭКГ после оказания мед. помощи',
				'rules' => 'trim',
				'type' => 'datetime'
			),
			array(
				'field' => 'EfAD',
				'label' => 'АД после оказания помощи',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'EfChss',
				'label' => 'ЧСС после оказания помощи',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'EfPulse',
				'label' => 'Пульс после оказания помощи',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'EfPulsks',
				'label' => 'Пульсоксиметрия после оказания помощи',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'EfGluck',
				'label' => 'Глюкометрия после оказания помощи',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'EfTemperature',
				'label' => 'Температура после оказания помощи',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'EfChd',
				'label' => 'ЧД после оказания помощи',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Kilo',
				'label' => 'Километраж',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'DescText',
				'label' => 'Примечание',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Diag_id',
				'label' => 'Диагноз',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'HelpPlace',
				'label' => 'Помощь, оказанная на месте',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'HelpAuto',
				'label' => 'Помощь, оказанная в автомобиле скорой помощи',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'isMenen',
				'label' => 'Менингеальные знаки',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'isNist',
				'label' => 'Нистагм',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'isLight',
				'label' => 'Реакция на свет',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'isAcro',
				'label' => 'Акроцианоз',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'isMramor',
				'label' => 'Мраморность',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'isAnis',
				'label' => 'Анизокория',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'isHale',
				'label' => 'Живот участвует в акте дыхания',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'isPerit',
				'label' => 'Симптомы раздражения брюшины',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'ComboValue',
				'label' => 'Текстовые поля',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'combos',
				'label' => 'Комбо',
				'rules' => '',
				'type' => 'string'
			)
		)
	);
	/**
	 * default desc
	 */
	function __construct() {
		parent::__construct();
		$this->load->database();
		$this->load->model('MobileBrig_model', 'dbmodel');
	}
	/**
	 * default desc
	 */
	function callAccepted() {
		$data = $this->ProcessInputData('callAccepted', true);
		if ( $data === false ) { return false; }
		$response = $this->dbmodel->callAccepted($data);
		
		$this->ProcessModelSave($response, true)->ReturnData();
		
		return true;
	}
	/**
	 * default desc
	 */
	function closeCallCard() {
		$data = $this->ProcessInputData('closeCallCard',true,true,false,false,false);
		if ( $data === false ) { return false; }
		$this->load->model('CmpCallCard_model','cccmodel');
		
		$isSaved = $this->dbmodel->isSavedCallCard($data);
		if ((isset($isSaved[0]['count']))&&($isSaved[0]['count']>0)) {
			$isSaved[0]['success']=true;
			$isSaved[0]['Error_Code']=0;
			$isSaved[0]['Error_Msg']='';
			$isSaved[0]['CmpCallCard_id']=$data['CmpCallCard_id'];
			$this->ProcessModelSave($isSaved, true)->ReturnData();
			return true;
		}
		
		$timeFiledsNames = array(
			'GoTime',
			'ArriveTime',
			'TransportTime',
			'ToHospitalTime',
			'BackTime',
			'EndTime'
		);
		
		foreach ($timeFiledsNames as $key => $timeFieldName) {
			if (!empty($data[$timeFieldName])) {
				$data[$timeFieldName] = preg_replace('/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2})$/', '$3.$2.$1 $4:$5', $data[$timeFieldName]);
			}
		}
		
		$existingData = $this->cccmodel->loadCmpCloseCardEditForm(array('CmpCallCard_id'=>$data['CmpCallCard_id']));
		$combos = json_decode($data['combos'], true);
		$ComboValue = json_decode($data['ComboValue'], true);
		foreach ($data as $key => $value) {
			$data[$key] = str_replace('_', '', $data[$key]);
		}
		array_walk($data, 'ConvertFromUTF8ToWin1251');
		array_walk($ComboValue, 'ConvertFromUTF8ToWin1251');
		$data = array_merge($existingData[0],$data,$combos,array('ComboValue'=>$ComboValue));
		$response = $this->cccmodel->saveCmpCloseCard110($data);
		if ((isset($response[0]['CmpCloseCard_id']))&&($response[0]['CmpCloseCard_id'])) {
			$setStatusResponse = $this->cccmodel->setStatusCmpCallCard(array('CmpCallCard_id'=>$data['CmpCallCard_id'],'CmpCallCardStatusType_id'=>2,'pmUser_id'=>$data['pmUser_id']));
			$setStatusResponse = $this->cccmodel->setIsOpenCmpCallCard(array('CmpCallCard_id'=>$data['CmpCallCard_id'],'CmpCallCard_IsOpen'=>1,'pmUser_id'=>$data['pmUser_id']));
			$this->ProcessModelSave($setStatusResponse, true)->ReturnData();
		} else {
			$this->ProcessModelSave($response, true)->ReturnData();
		}
		
		return true;
	}
	/**
	 * default desc
	 */
	function setBrigStatus() {
		$data = $this->ProcessInputData('setBrigStatus', true);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->setBrigStatus($data);
		
		$this->ProcessModelSave($response, true)->ReturnData();
		
		return true;
	}
	/**
	 * default desc
	 */
	function getEmergencyTeamData() {
		$data = $this->ProcessInputData('getEmergencyTeamData', true);
		if ( $data === false ) { return false; }
		$response = $this->dbmodel->getEmergencyTeamData($data);
		$this->ProcessModelSave($response, true)->ReturnData();	
		return true;
	}
	/**
	 * default desc
	 */
	function makeHtmlCombo($data,$type,$legend, $name, $required){
		$loc = '';
		$result = '';
		
		if ($name == 'ResultUfa_id') {
			return $this->makeHtmlComboWithSeveralFields($data,$legend, $name, $required);
		}
		
		if ($type == 'c') {
			$result = '<fieldset data-role="controlgroup" data-type="vertical" data-mini="true">';
			$result .= "<legend> {$legend} </legend>";
			foreach ($data as $value) {
				if ($value['loc'] == '1') {
					$result .=	"<input id='{$value['id']}' name='{$name}' type='checkbox'/>
					<label for='{$value['id']}'>
						{$value['name']}
					</label>";					
				}
				else {
					$loc = "<p><label class='width30'>{$value['name']} </label><input id='InputOther{$value['id']}' name='' type='text'/><p>";
				};
			}
			$result .= $loc.'</fieldset>';
		} else {			
			$label = ($name=='AgeType_id')?'':$legend;
			$additionalParams = ($name=='AgeType_id')?'data-inline="true"':'';
			$result = "<label> {$label} </label> <select class='combo' name='{$name}' {$additionalParams}>";
			$result .= ((!isset($required))||(!$required))? "<option value='0' selected='selected'></option>":'';
			
			foreach ($data as $value) {
				if ($value['loc'] == '1') {
					$result .=	"<option value='{$value['id']}'>{$value['name']}</option>";					
				}
				else {					
					$result .=	"<option id='{$value['id']}' value='other'>{$value['name']}</option>";
					if ($name == 'Patient_id' && $value['id'] == '111') {						
						$input = "<select id='InputOther{$value['id']}' name='InputOther{$value['id']}' type='text'/>
						<option value='0'></option>";
						$lpus = $this->dbmodel->loadLpu();
						foreach ($lpus as $lpu) {
							$input .= "<option value='".$lpu['Lpu_id']."'>".$lpu['Lpu_Nick']."</option>";
						}						
						$input .= "</select>";
					} else {
						$input = "<input id='InputOther{$value['id']}' name='' type='text'/>";
					}
					$loc .= "<div class='{$name}_other' id='OtherDiv{$value['id']}' data-role='fieldcontain' style='display:none;'><p><label class='width30'>{$value['name']} </label>".$input."</p></div>";
				};
			}
			$result .= '</select>'.$loc;
			
		}
		return $result;
	}
	/**
	 * default desc
	 */
	function makeHtmlComboWithSeveralFields($data,$legend, $name, $required) {
		$result = "<label> {$legend} </label> <select class='combo' name='{$name}'>";
		$result .= ((!isset($required))||(!$required))? "<option value='0' selected='selected'></option>":'';
		
		$alreadyUsedIds = array();
		$additionalFieldsDivs = array();
		foreach ($data as $value) {
			if (!in_array($value['id'],$alreadyUsedIds)) {
				$result .= "<option id='{$value['id']}' value='{$value['id']}'>{$value['name']}</option>";
				$alreadyUsedIds[] = $value['id'];
				$additionalFieldsDivs[] = $this->makeDivWithSeveralAddFields($data,$value['id'],$name);
			}
		}
		
		$result .= '</select>';
		foreach ($additionalFieldsDivs as $value) {
			$result .= $value;
		}
		return $result;
		
	}
	/**
	 * default desc
	 */
	function makeDivWithSeveralAddFields($data,$id,$name) {
		$count = 0;
		$resultDiv = "<div class='{$name}_other' id='OtherDiv{$id}' data-role='fieldcontain' style='display:none;'>";
		foreach ($data as $value) {
			if ($value['id']==$id && $value['secondLevelId']!=NULL) {
				$count++;
				$resultDiv .="<p><label class='width30'>{$value['secondLevelComboName']} </label><input id='InputOther{$value['secondLevelId']}' type='text'/></p>";
			}
		}
		$resultDiv .= '</div>';
		$resultDiv = ($count==0)?'':$resultDiv;
		return $resultDiv;
	}
	
	/**
	 * default desc
	 */
	function getFormFieldLabels() {
		$response = $this->dbmodel->getFormFieldLabels();
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	/**
	 * default desc
	 */
	function Index() {
		global $_USER;
		if ( !isset($_SESSION['login']) ) {
			session_destroy();
			header("Status: 301");
			header("Location: /?c=main&from=promed");
		}
		else {
			// Если залогинены, то сохраняем данные о пользователе в глобальной переменной $_USER
			$_USER = pmAuthUser::find($_SESSION['login']);
			$this->load->helper('Main');
			$this->load->helper('Date');
			
			$BrigInfoData = $this->ProcessInputData('getBrigInfo', true);
			if ( $BrigInfoData === false ) { return false; }
			$BrigInfo = $this->dbmodel->getBrigInfo($BrigInfoData);
			//TODO: Вернуть на главную, если не нашли инфу о бригаде
			if ($BrigInfo == false) {
				header("Status: 301");
				header("Location: /?c=main&from=promed");
				return;
			}
			$data = $BrigInfo[0];
			
			$unclosedCallCards = $this->dbmodel->getUnclosedCards($data);
			$unclosedCallHTML = '';
			$data['unclosedCallCardsCount'] = count($unclosedCallCards);
			foreach ($unclosedCallCards as $value) {
				$time = substr($value['CmpCallCard_prmTime'], 0, 5);
				$date = str_replace('/', '.', $value['CmpCallCard_prmDate']);
				
				$unclosedCallHTML .=
					"<div class='callcard unclosed' id={$value['CmpCallCard_id']}>
						<h3 class='unclosedPersonName'>{$value['Person_FIO']}</h3>
						<p class='unclosedPersonId' style='display:none;'>{$value['Person_id']}</p>
						<p class='unclosedCallerInfo' style='display:none;'>{$value['CallerInfo']}</p>
						<p class='unclosedCallType' style='display:none;'>{$value['CmpCallType_Name']}</p>
						<p class='unclosedPersonFir' style='display:none;'>{$value['Person_Firname']}</p>
						<p class='unclosedPersonSec' style='display:none;'>{$value['Person_Secname']}</p>
						<p class='unclosedPersonSur' style='display:none;'>{$value['Person_Surname']}</p>
						<p class='unclosedPersonSex' style='display:none;'>{$value['Sex_id']}</p>
						<p class='unclosedPersonAgeTypeVal' style='display:none;'>{$value['AgeType_value']}</p>
						<p class='unclosedPersonAge' style='display:none;'>{$value['Age']}</p>
						<p class='unclosedBirthDayAndReason'><em class='bd'>{$value['Person_Birthday']}</em> <em><span class='ds'>{$value['CmpReason_Name']}</span></em></p>
						<p class='unclosedAddr'>{$value['Adress_Name']}</p>
						<p class='approve'><span class='timestamp'>{$time} <em>|</em> {$date}</span><button onclick='acceptCallCard({$value['CmpCallCard_id']},{$value['Person_id']});'>Принять</button></p>
					</div>
					";
			}
			
			$data['unclosedCallCards'] = $unclosedCallHTML;

			$closedCallCards = $this->dbmodel->getClosedCards($data);
			$closedCallHTML = '';
			$data['closedCallCardsCount'] = count($closedCallCards);
			foreach ($closedCallCards as $value) {
				$time = substr($value['CmpCallCard_prmTime'], 0, 5);
				$date = str_replace('/', '.', $value['CmpCallCard_prmDate']);
				
				$closedCallHTML .=
					"<div class='callcard closed' id={$value['CmpCallCard_id']}>
						<h3 class='closedPersonName'>{$value['Person_FIO']}</h3>
						<p class='closedPersonId' style='display:none;'>{$value['Person_id']}</p>
						<p class='closedCallerInfo' style='display:none;'>{$value['CallerInfo']}</p>
						<p class='closedCallType' style='display:none;'>{$value['CmpCallType_Name']}</p>
						<p class='closedPersonFir' style='display:none;'>{$value['Person_Firname']}</p>
						<p class='closedPersonSec' style='display:none;'>{$value['Person_Secname']}</p>
						<p class='closedPersonSur' style='display:none;'>{$value['Person_Surname']}</p>
						<p class='closedPersonSex' style='display:none;'>{$value['Sex_id']}</p>
						<p class='closedPersonAgeTypeVal' style='display:none;'>{$value['AgeType_value']}</p>
						<p class='closedPersonAge' style='display:none;'>{$value['Age']}</p>
						<p class='closedBirthDayAndReason'><em class='bd'>{$value['Person_Birthday']}</em> <em><span class='ds'>{$value['CmpReason_Name']}</span></em></p>
						<p class='closedAddr'>{$value['Adress_Name']}</p>
						<p class='approve'><span class='timestamp'>{$time} <em>|</em> {$date}</span><button onclick='acceptCallCard({$value['CmpCallCard_id']},{$value['Person_id']});'>Принять</button></p>
					</div>
					";
			}
			
			$data['closedCallCards'] = $closedCallHTML;
			
			
			$SectionProfiles = $this->dbmodel->getProfileList();
			$data['SectionProfiles'] = $SectionProfiles[0]['data'];
			foreach ($data['SectionProfiles'] as $k=>$v) {
				array_walk($data['SectionProfiles'][$k], 'ConvertFromUTF8ToWin1251');
			}
		
			$checkBoxGroupName = array('Delay_id','AccidentReason_id','Complicat_id','','','','','','','','','');
			$requiredFields = array('Condition_id','Cons_id','Result_id','CallPovod_id','AgeType_id','DeportClose_id','DeportFail_id','ResultUfa_id','','','');
			
			
			$combo = $this->dbmodel->getFormFieldLabels();
			$comboHtml = array();
			foreach ($combo as $key => $value) {
				$type=(in_array($key, $checkBoxGroupName))?'c':'r';
				$required = in_array($key, $requiredFields);
				$legend = $value[0]['legend'];
				$comboHtml["{$key}"] = $this->makeHtmlCombo($value, $type, $legend, $key,$required);
			}
			
						
			$data['combo'] = $comboHtml;
			$statuses = $this->dbmodel->getStatuses();
			$data['stats'] = $statuses;
			$data += Array("css_files" => $this->GetCSSFiles(), "js_files" => $this->GetJSFiles());
				
			$this->load->view('mobilebrig_view',$data);
		}
		
		
	}
	/**
	 * default desc
	 */
	function getDiagsControlNumber(){
		$response = $this->dbmodel->getDiagsControlNumber();
		$this->ProcessModelSave($response, true)->ReturnData();
		return $response;
	}
	/**
	 * default desc
	 */
	function getDiags() {
		$response = $this->dbmodel->getDiags();
		$this->ProcessModelList($response, true)->ReturnData();
		return $response;
	}
	/**
	 * default desc
	 */
	function setOnlineStatus() {
		$data = $this->ProcessInputData('setOnlineStatus', true);
		if ( $data === false ) { return false; }
		$response = $this->dbmodel->setOnlineStatus($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}
	
	/**
	 * Возвращает список необходимых к загрузке CSS файлов
	 * @return array Массив CSS файлов для загрузки
	 */
	function GetCSSFiles() {
		$retArray = Array();

		//<!-- основные стили -->
		$retArray[] = "/css/Mobile/main.css";
		$retArray[] = "/css/Mobile/theme.css";
		//<!-- стили для компонента uidesigner -->
		//$retArray[] = "/uidesigner/css/Ext.ux.guid.plugin.Designer.css";
		return $retArray;
	}

	/**
	 * Возвращает список необходимых к загрузке JS файлов
	 * @return array Массив JS файлов для загрузки
	 */
	function GetJSFiles() {
		$retArray = Array();
		//$config = & get_config();

		$retArray[] = "/?c=JsMobileConstants"; 
		$retArray[] = "/jscore/Mobile/jquery.min.js";
		$retArray[] = "/jscore/Mobile/jquery.mobile-1.1.1.min.js";
		$retArray[] = "/jscore/Mobile/jquery.maskedinput-1.3.min.js";
		$retArray[] = "/jscore/Mobile/my.js";
		$retArray[] = "/jscore/Mobile/base.js";
		$retArray[] = "/jscore/Mobile/connection.js";
		$retArray[] = "/jscore/Mobile/jquery.autocomplete.js";
		$retArray[] = '/jscore/Mobile/socket.io-1.2.js';
		
		return $retArray;
	}
	
	/**
	 * Получаем имя файла и отдаёт его минимизированное содержимое
	 * Данные берутся из кэша
	 * @param $file Имя JS файла с путём
	 * @return Минимизированное содержимое JS файла
	 */
	function GetMinifiedJS($file) {
		
		// На всякий случай определяем константу снова, если она не была определена
		if (!defined('JSCACHE_PATH')) {
			define('JSCACHE_PATH', $_SERVER['DOCUMENT_ROOT'].'/jscache');
		}
		
		// Берём дату изменения скрипта
		$ts = filemtime($_SERVER['DOCUMENT_ROOT'].$file);
		
		//Проверяем есть ли файл в кэше с такой датой изменения
		if (file_exists(JSCACHE_PATH.$file.$ts)) {
			// если есть, то просто отдаём его содержимое
			$contents = file_get_contents(JSCACHE_PATH.$file.$ts);
		} else {
			//если нет, то берем содержимое исходного js файла, минимизируем
			//записываем в кэш и отдаём. Из кэша удаляем старый кэш файла, если он есть
			$contents = file_get_contents($_SERVER['DOCUMENT_ROOT'].$file);
			$this->load->library('jsmin');
			// Берем минимизированное содержимое
			$contents = $this->jsmin->minify($contents);
			
			// Получаем путь до кэшированного файла
			$path_parts = pathinfo(JSCACHE_PATH.$file.$ts);
			$dir = $path_parts['dirname'];
			if (!is_dir($dir)) {
				//если папка еще не создана то создаём
				mkdir($dir, 0777, true);
			}
			
			// Ищем и удаляем старые кэши данного файла
			$files = sdir($dir, basename(JSCACHE_PATH.$file).'*');
			foreach($files as $old_file) {
				unlink($dir.'/'.$old_file);
			}
			
			// Записываем в кэш новую версию минимизированного содержимое
			file_put_contents(JSCACHE_PATH.$file.$ts, $contents);
		}
		
		return $contents;
	}
}
?>