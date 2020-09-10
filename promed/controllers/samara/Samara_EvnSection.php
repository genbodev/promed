<?php defined('BASEPATH') or die ('No direct script access allowed');


require_once(APPPATH.'controllers/EvnSection.php');

class Samara_EvnSection extends EvnSection {
  
	/**
	 * __construct
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('Samara_EvnSection_model', 'samara_dbmodel');
		$this->load->helper('Text');
		
		$this->inputRules['printEmkForm'] = array(
			array(
				'field' => 'EvnPS_id',
				'label' => 'Идентификатор События',
				'rules' => '',
				'type' => 'id'
			)
		);
        
        $this->inputRules['loadMorbusOnko'] = array(  
			array(
				'field' => 'MorbusOnko_pid',
				'label' => 'Идентификатор движения',
				'rules' => 'required',
				'type' => 'id'
			)
		);
        
        $this->inputRules['loadEvnUsluga'] =  array(
            array(
				'field' => 'pid',
				'label' => 'Родительский идентификатор',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_id',
				'label' => 'ЛПУ',
				'rules' => '',
				'type' => 'id'
			),            
            array(
				'field' => 'class',
				'label' => 'Класс',
				'rules' => '',
				'type' => 'string'
			),            
            array(
				'field' => 'byMorbus',
				'label' => '',
				'rules' => '',
				'type' => 'int'
			),            
            array(
				'field' => 'Morbus_id',
				'label' => 'Идентификатор онкологии',
				'rules' => '',
				'type' => 'id'
			),            
            array(
				'field' => 'EvnEdit_id',
				'label' => '',
				'rules' => '',
				'type' => 'id'
			)
		);
        
        $this->inputRules['loadBasePS'] = array(  
			array(
				'field' => 'Morbus_id',
				'label' => 'Идентификатор онко',
				'rules' => 'required',
				'type' => 'id'
			),
            array(
				'field' => 'Evn_id',
				'label' => 'Идентификатор случая',
				'rules' => 'required',
				'type' => 'id'
			)
		);        
        
        $this->inputRules['saveMorbusOnko'] = array(
            array(
					'field' => 'Evn_pid',
					'label' => '',
					'rules' => '',
					'type' => 'id'
			),
            array(
					'field' => 'Person_id',
					'label' => '',
					'rules' => '',
					'type' => 'id'
			),
			array(
					'field' => 'Mode',
					'label' => 'Режим сохранения',
					'rules' => 'trim', // убрал required
					'type' => 'string'
			),
			array(
					'field' => 'Diag_id',
					'label' => 'Диагноз',
					'rules' => '',
					'type' => 'id'
			),
			array(
					'field' => 'Evn_pid',
					'label' => 'Движение/Посещение',
					'rules' => '',
					'type' => 'id'
			),
			array('field' => 'Person_id',
					'label' => 'Человек',
					'rules' => '',
					'type' => 'id'
			),
			//ОнкоСпецифика заболевания
			array(
					'field' => 'Morbus_id',
					'label' => 'Идентификатор заболевания',
					'rules' => '', // ipavelpetrov // 'required',
					'type' => 'id'
			),
			array(
					'field' => 'MorbusOnko_id',
					'label' => 'Идентификатор специфики заболевания',
					'rules' => '', // ipavelpetrov // 'required',
					'type' => 'id'
			),
			array(
					'field' => 'MorbusOnko_firstSignDT',
					'label' => 'Дата появления первых признаков заболевания',
					'rules' => '',
					'type' => 'date'
			),
			array(
					'field' => 'MorbusOnko_firstVizitDT',
					'label' => 'Дата первого обращения',
					'rules' => '',
					'type' => 'date'
			),
			array(
					'field' => 'Lpu_foid',
					'label' => 'В какое медицинское учреждение',
					'rules' => '',
					'type' => 'id'
			),
			array(
					'field' => 'OnkoRegType_id',
					'label' => 'Взят на учет в ОД',
					'rules' => '',
					'type' => 'id'
			),
			array(
					'field' => 'OnkoRegOutType_id',
					'label' => 'Причина снятия с учета',
					'rules' => '',
					'type' => 'id'
			),
			array(
					'field' => 'OnkoLesionSide_id',
					'label' => 'Сторона поражения',
					'rules' => '',
					'type' => 'id'
			),
			array(
					'field' => 'OnkoDiag_mid',
					'label' => 'Морфологический тип опухоли. (Гистология опухоли)',
					'rules' => '',
					'type' => 'id'
			),
			array(
					'field' => 'MorbusOnko_NumHisto',
					'label' => 'Номер гистологического исследования',
					'rules' => '',
					'type' => 'string'
			),
			array(
					'field' => 'OnkoT_id',
					'label' => 'T',
					'rules' => '',
					'type' => 'id'
			),
			array(
					'field' => 'OnkoN_id',
					'label' => 'N',
                    'rules' => '',
                    'type' => 'id'
            ),
			array(
                'field' => 'OnkoM_id',
                'label' => 'M',
                'rules' => '',
                'type' => 'id'
            ),
			array(
                'field' => 'TumorStage_id', 
                'label' => 'Стадия опухолевого процесса',
                'rules' => '',
                'type' => 'id'
            ),
			array(
                'field' => 
                'MorbusOnko_IsTumorDepoUnknown', 
                'label' => 'Локализация отдаленных метастазов: Неизвестна', 
                'rules' => '', 
                'type' => 'id'
            ),
			array(
				'field' => 'MorbusOnko_IsTumorDepoLympha', 
				'label' => 'Локализация отдаленных метастазов: Отдаленные лимфатические узлы', 
				'rules' => '', 
				'type' => 'id'
			),
			array(
				'field' => 'MorbusOnko_IsTumorDepoBones', 
				'label' => 'Локализация отдаленных метастазов: Кости', 
				'rules' => '', 
				'type' => 'id'
			),
			array(
				'field' => 'MorbusOnko_IsTumorDepoLiver', 
				'label' => 'Локализация отдаленных метастазов: Печень', 
				'rules' => '', 
				'type' => 'id'
			),
			array(
				'field' => 'MorbusOnko_IsTumorDepoLungs', 
				'label' => 'Локализация отдаленных метастазов: Легкие и/или плевра', 
				'rules' => '', 
				'type' => 'id'
			),
			array(
				'field' => 'MorbusOnko_IsTumorDepoBrain', 
				'label' => 'Локализация отдаленных метастазов: Головной мозг', 
				'rules' => '', 
				'type' => 'id'
			),
			array(
				'field' => 'MorbusOnko_IsTumorDepoSkin', 
				'label' => 'Локализация отдаленных метастазов: Кожа', 
				'rules' => '', 
				'type' => 'id'
			),
			array(
				'field' => 'MorbusOnko_IsTumorDepoKidney', 
				'label' => 'Локализация отдаленных метастазов: Почки', 
				'rules' => '', 
				'type' => 'id'
			),
			array(
				'field' => 'MorbusOnko_IsTumorDepoOvary', 
				'label' => 'Локализация отдаленных метастазов: Яичники', 
				'rules' => '', 
				'type' => 'id'
			),
			array(
				'field' => 'MorbusOnko_IsTumorDepoPerito', 
				'label' => 'Локализация отдаленных метастазов: Брюшина', 
				'rules' => '', 
				'type' => 'id'
			),
			array(
				'field' => 'MorbusOnko_IsTumorDepoMarrow', 
				'label' => 'Локализация отдаленных метастазов: Костный мозг', 
				'rules' => '', 
				'type' => 'id'
			),
			array(
				'field' => 'MorbusOnko_IsTumorDepoOther', 
				'label' => 'Локализация отдаленных метастазов: Другие органы', 
				'rules' => '', 
				'type' => 'id'
			),
			array(
				'field' => 'MorbusOnko_IsTumorDepoMulti', 
				'label' => 'Множественные', 
				'rules' => '', 
				'type' => 'id'
			),
			array(
				'field' => 'TumorCircumIdentType_id', 
				'label' => 'Обстоятельства выявления опухоли', 
				'rules' => '', 
				'type' => 'id'
			),
			array(
				'field' => 'OnkoLateDiagCause_id', 
				'label' => 'Причины поздней диагностики', 
				'rules' => '', 
				'type' => 'id'
			),
			array(
				'field' => 'TumorAutopsyResultType_id', 
				'label' => 'Результат аутопсии применительно к данной опухоли', 
				'rules' => '', 
				'type' => 'id'
			),
			array(
				'field' => 'TumorPrimaryTreatType_id', 
				'label' => 'Проведенное лечение первичной опухоли', 
				'rules' => '', 
				'type' => 'id'
			),
			array(
				'field' => 'TumorRadicalTreatIncomplType_id', 
				'label' => 'Причины незавершенности радикального лечения', 
				'rules' => '', 
				'type' => 'id'
			),
			array(
					'field' => 'MorbusOnko_specSetDT', 
				'label' => 'Дата начала специального лечения', 
				'rules' => '', 
				'type' => 'date'
				),
			array(
				'field' => 'MorbusOnko_specDisDT', 
				'label' => 'Дата окончания специального лечения', 
				'rules' => '', 
				'type' => 'date'
			),
			array(
				'field' => 'MorbusOnko_IsMainTumor', 
				'label' => 'Призак основной опухоли', 
				'rules' => '', 
				'type' => 'int'
			),
			array(
				'field' => 'MorbusOnko_setDiagDT', 
				'label' => 'Дата установления дигноза', 
				'rules' => '', 
				'type' => 'date'
			),
			//ОнкоСпецифика общего заболевания
			array(
				'field' => 'MorbusOnkoBase_id',
				'label' => 'Идентификатор онкоспецифики общего заболевания',
				'rules' => '', // ipavelpetrov // 'required',
				'type' => 'id'
			),
			array(
				'field' => 'MorbusBase_id',
				'label' => 'Общее заболевание',
				'rules' => '', // ipavelpetrov // 'required',
				'type' => 'id'
			),
			array(
				'field' => 'MorbusOnkoBase_NumCard', 
				'label' => 'Порядковый номер регистрационной карты', 
				'rules' => 'trim', 
				'type' => 'string'
			),
			// с клиента не приходит array('field' => 'OnkoInvalidType_id', 'label' => 'Инвалидность по основному (онкологическому) заболеванию', 'rules' => '', 'type' => 'id'),
			array(
				'field' => 'MorbusOnkoBase_deadDT', 
				'label' => 'Дата смерти', 
				'rules' => '', 
				'type' => 'date'
			),
			array(
				'field' => 'Diag_did', 
				'label' => 'Диагноз причины смерти', 
				'rules' => '', 
				'type' => 'id'
			),
			// с клиента не приходит array('field' => 'MorbusOnkoBase_deathCause', 'label' => 'Причина смерти', 'rules' => '', 'type' => 'id'),
			// с клиента не приходит array('field' => 'AutopsyPerformType_id', 'label' => 'Аутопсия', 'rules' => '', 'type' => 'id'),
			array(
				'field' => 'TumorPrimaryMultipleType_id', 
				'label' => 'Первично-множественная опухоль', 
				'rules' => '', 
				'type' => 'id'
			),
			array(
				'field' => 'OnkoStatusYearEndType_id', 
				'label' => 'клиническая группа', 
				'rules' => '', 
				'type' => 'id'
			),
			array(
				'field' => 'OnkoRegOutType_id', 
				'label' => 'Причина снятия с учета', 
				'rules' => '', 
				'type' => 'id'
			),
			array(
				'field' => 'OnkoRegType_id', 
				'label' => 'взят на учет в ОД', 
				'rules' => '', 
				'type' => 'id'
			),
			array(
				'field' => 'TumorPrimaryMultipleType_id', 
				'label' => 'Первично', 
				'rules' => '', 
				'type' => 'id'
			),
			array(
				'field' => 'OnkoDiagConfType_id', 
				'label' => 'Первично', 
				'rules' => '', 
				'type' => 'id'
			),
			array(
				'field' => 'OnkoPostType_id', 
				'label' => 'Первично', 
				'rules' => '', 
				'type' => 'id'
			),
			array(
				'field' => 'OnkoCombiTreatType_id', 
				'label' => 'Первично', 
				'rules' => '', 
				'type' => 'id'
			),
			array(
				'field' => 'OnkoLateComplTreatType_id', 
				'label' => 'Первично', 
				'rules' => '', 
				'type' => 'id'
			),
			array(
				'field' => 'OnkoStatusYearEndType_id', 
				'label' => 'Первично', 
				'rules' => '', 
				'type' => 'id'
			),
			//Атрибуты общего заболевания
			array(
				'field' => 'MorbusBase_setDT', 
				'label' => 'Дата взятия на учет в ОД', 
				'rules' => '', 
				'type' => 'date'
			),
			array(
				'field' => 'MorbusBase_disDT', 
				'label' => 'Дата снятия с учета в ОД', 
				'rules' => '', 
				'type' => 'date'
			)
		);
        
	}    

	/**
	 * printEmkForm
	 */
	function printEmkForm() {
		$data = $this->ProcessInputData('printEmkForm', true);
		if ($data === false) { return false; }
		$this->load->library('parser');
		$response = $this->samara_dbmodel->printEmkForm($data);
		//$this->ProcessModelList($response, true, true)->ReturnData();
		//var_dump($response[0]['EvnPS_TimeDesease']);die;
        if (isset($response[0]['EvnPS_TimeDesease']) && isset($response[0]['Okei_Name'])){
            $TimeDeseaseType_Name = 'через '.$response[0]['EvnPS_TimeDesease'].' '.$response[0]['Okei_Name'];
        }
        else
            $TimeDeseaseType_Name = '&nbsp;';
		if ( !is_array($response) || count($response) == 0 ) {
			echo 'ЭМК не найдено';
			return true;
		}
				
		$template = 'samara_EmkFormReport';
		if ($data["Lpu_id"] == "1602")  {
			$template = 'samara_EmkFormReport_1602';
		}
		
 		$this->load->model('Privilege_model', 'privilegy_dbmodel');

		$data['Person_id'] = $response[0]['Person_id'];		
		/*$response_privilegy = $this->privilegy_dbmodel->loadPersonPrivilegeList($data);

		for ( $i = 0; $i < count($response_privilegy); $i++ ) {
					$person_privilegies += ($i == 0 ? '&nbsp;' : ',&nbsp;')+ $response_privilegy[$i];
				}
		*/
		$print_data = array(
			'EvnPSTemplateTitle' => 'Печать ЭМК',
			'Lpu_id' => returnValidHTMLString($data['Lpu_id']),
			'Lpu_Name' => returnValidHTMLString($response[0]['Lpu_Name']),
			'addressLpu' => returnValidHTMLString($response[0]['addressLpu']),
			'EvnPS_NumCard' => returnValidHTMLString($response[0]['EvnPS_NumCard']),
			'EvnPS_setDT' => returnValidHTMLString($response[0]['EvnPS_setDT']->format('d.m.Y H:i')),
			'EvnPS_disDT' => returnValidHTMLString($response[0]['EvnPS_disDT']),
			'LpuSection_Name' => returnValidHTMLString($response[0]['LpuSection_Name']),
			'LpuSectionWard_Name' => returnValidHTMLString($response[0]['LpuSectionWard_Name']),
			'EvnPS_EntranceMode' => returnValidHTMLString($response[0]['EvnPS_EntranceMode']),
			'EvnPS_DrugActions' => returnValidHTMLString($response[0]['EvnPS_DrugActions']),
			'Lsection_name' => returnValidHTMLString($response[0]['Lsection_name']),
			'koikodni' => returnValidHTMLString($response[0]['koikodni']),
			'PrehospArrive_Name' => returnValidHTMLString($response[0]['PrehospArrive_Name']),
			'fio' => returnValidHTMLString($response[0]['fio']),
			'sex_name' => returnValidHTMLString($response[0]['sex_name']),
			'age' => returnValidHTMLString($response[0]['age']),
			'Address_Address' => returnValidHTMLString($response[0]['Address_Address']),
			'PersonPhone_Phone' => returnValidHTMLString($response[0]['PersonPhone_Phone']),
			'DeputyKind_Name' => returnValidHTMLString($response[0]['DeputyKind_Name']),
			'EvnPs_DeputyFIO' => returnValidHTMLString($response[0]['EvnPs_DeputyFIO']),
			'EvnPs_DeputyContact' => returnValidHTMLString($response[0]['EvnPs_DeputyContact']),
			'Org_Name' => returnValidHTMLString($response[0]['Org_Name']),
			'Post_Name' => returnValidHTMLString($response[0]['Post_Name']),
			'WhoOrgDirected' => returnValidHTMLString($response[0]['WhoOrgDirected']),
			'WhoMedPersonalDirected' => returnValidHTMLString($response[0]['WhoMedPersonalDirected']),
			'extr' => returnValidHTMLString($response[0]['extr']),
			'EvnPS_TimeDesease' => returnValidHTMLString($response[0]['EvnPS_TimeDesease']),
			'WhoMedPersonalDirected' => returnValidHTMLString($response[0]['WhoMedPersonalDirected']),
			'pl' => returnValidHTMLString($response[0]['pl']),
			'diagdir_name' => returnValidHTMLString($response[0]['diagdir_name']),
			'EvnPS_PhaseDescr_pid' => returnValidHTMLString($response[0]['EvnPS_PhaseDescr_pid']),
			'EvnPS_PhaseDescr_did' => returnValidHTMLString($response[0]['EvnPS_PhaseDescr_did']),
			'diagpriem_name' => returnValidHTMLString($response[0]['diagpriem_name']),
			'EvnPS_setDate' => returnValidHTMLString($response[0]['EvnPS_setDate']),
			'diag_name' => returnValidHTMLString($response[0]['diag_name']),
			'EvnSection_setDate' => returnValidHTMLString($response[0]['EvnSection_setDate']),
			'diagOsl_name' => returnValidHTMLString($response[0]['diagOsl_name']),
			'DiagSop_name' => returnValidHTMLString($response[0]['DiagSop_name']),
			'LeaveType_Name' => returnValidHTMLString($response[0]['LeaveType_Name']),
			'ResultDesease_Name' => returnValidHTMLString($response[0]['ResultDesease_Name']),
			'Person_BirthDay' => returnValidHTMLString($response[0]['Person_BirthDay']),
			'KLAreaType_Name' => returnValidHTMLString($response[0]['KLAreaType_Name']),
			'KLAreaType_SysNick' => returnValidHTMLString($response[0]['KLAreaType_SysNick']),
			'PrivilegeType_Name' => returnValidHTMLString($response[0]['PrivilegeType_Name']),
			'PersonPrivilege_Serie' => returnValidHTMLString($response[0]['PersonPrivilege_Serie']),
			'PersonPrivilege_Number' => returnValidHTMLString($response[0]['PersonPrivilege_Number']),
			'PrivilegeType_Code' => returnValidHTMLString($response[0]['PrivilegeType_Code']),			
			'PersonPrivilege_Group' => returnValidHTMLString($response[0]['PersonPrivilege_Group']),
			'Polis_Ser' => returnValidHTMLString($response[0]['Polis_Ser']),
			'Polis_Num' => (!empty($response[0]['Polis_Num']))? returnValidHTMLString($response[0]['Polis_Num']) : returnValidHTMLString($response[0]['Person_edNum']),
			'OSM_Name' => returnValidHTMLString($response[0]['OSM_Name']),
			'SocStatus_Name' => returnValidHTMLString($response[0]['SocStatus_Name']),
			'TimeDeseaseType_Name' => returnValidHTMLString($response[0]['TimeDeseaseType_Name']),
			'Document_Ser' => returnValidHTMLString($response[0]['Document_Ser']),
			'Document_Num' => (!empty($response[0]['Document_Num']))? returnValidHTMLString($response[0]['Document_Num']) : returnValidHTMLString($response[0]['Document_Num']),
			'Document_Org_Nick' => returnValidHTMLString($response[0]['Document_Org_Nick'])

		);					
			
		return $this->parser->parse($template, $print_data);
	}
	/**
	 * saveMorbusOnko
	 */ 
	function saveMorbusOnko(){
		$data = $this->ProcessInputData('saveMorbusOnko', true);
		$data['Mode'] = 'personregister_viewform';

        $this->load->model('MorbusOnkoSpecifics_model','onko');
		if (empty($data['Morbus_id']) || empty($data['MorbusOnko_id'])){
			$saved = $this->onko->createMorbusSpecific($data);
			if ( !is_array($saved) || count($saved) == 0 ) {
				throw new Exception('Ошибка при сохранении данных об онкологии');
			} else if ( !empty($saved[0]['Error_Msg']) ) {
				throw new Exception($saved[0]['Error_Msg']);
			}
		} else {
			// update
			$saved = $this->onko->saveMorbusSpecific($data);
		}
	}    
    
	/**
	 * loadMorbusOnko
	 */ 
    function loadMorbusOnko(){ 
		$data = $this->ProcessInputData('loadMorbusOnko', true);
		
        if ($data === false) {
			return false;
		}

        $this->load->model('MorbusOnkoSpecifics_model','onko_model');
		$response = $this->onko_model->getViewData($data);

        $outData = $this->ProcessModelSave($response, true, true)->formatDatetimeFields()->OutData;
        $this->OutData = array();
        $this->OutData['success'] = true;
        $this->OutData['data'] = $outData;
        $this->ReturnData();
        
		return true;    
    }
	
	/**
	 * loadEvnUsluga
	 */ 
    function loadEvnUsluga(){
        $data = $this->ProcessInputData('loadEvnUsluga', true);
		$this->load->model('EvnUsluga_model', 'EvnUsluga');
		$object_data = $this->EvnUsluga->loadEvnUslugaGrid($data);
        
		$this->ProcessModelList($object_data, true, true)->ReturnData();
    }
    
    /**
	 * loadBasePS
	 */ 
    function loadBasePS(){
        $data = $this->ProcessInputData('loadBasePS', true);
        $this->load->model('MorbusOnkoBasePS_model', 'MorbusOnkoBasePS');
		$object_data = $this->MorbusOnkoBasePS->getViewData(
            array(
                'Morbus_id'=>$data['Morbus_id'],
                'Evn_id'=>$data['Evn_id']
            ));
        $this->ProcessModelList($object_data, true, true)->ReturnData();            
    }
}
