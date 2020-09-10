<?php
defined("BASEPATH") or die ("No direct script access allowed");
/**
 * EvnPLDispDop13 - контроллер для управления талонами по диспансеризации взрослого населения
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			DLO
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Dmitry Vlasenko
 * @originalauthor	Petukhov Ivan aka Lich (megatherion@list.ru) / Stas Bykov aka Savage (savage1981@gmail.com)
 * @version			16.05.2013
 *
 * @property EvnPLDispDop13_model $dbmodel
 * @property EvnUslugaDispDop_model $euddmodel
*/

class EvnPLDispDop13 extends swController
{
	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->load->database();
		$this->load->model('EvnPLDispDop13_model', 'dbmodel');
		$this->inputRules = $this->dbmodel->getInputRulesAdv();
	}
	
	/**
	 *	Проверка наличия объёма
	 */
	function checkDispClass2Volume() {
		$data = $this->ProcessInputData('checkDispClass2Volume', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->checkDispClass2Volume($data);
		$this->ProcessModelSave($response, true, 'Ошибка проверки наличия объёма ДВН2')->ReturnData();
		
		return true;
	}

	/**
	 * Получение данных из объёма 2018_ДВН1_85
	 * @throws Exception
	 */
	function get2018Dvn185Volume() {
		$data = $this->ProcessInputData('get2018Dvn185Volume', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->get2018Dvn185Volume($data);
		$this->ProcessModelSave($response, true, 'Ошибка при получении данных из объёма 2018_ДВН1_85')->ReturnData();

		return true;
	}

	/**
	 * Получение доступных МО прикрепления
	 */
	function getAllowedLpuAttachIds() {
		$data = $this->ProcessInputData('getAllowedLpuAttachIds', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getAllowedLpuAttachIds($data);
		$this->ProcessModelSave($response, true, 'Ошибка получения списка доступных МО')->ReturnData();

		return true;
	}

	/**
	 * Метод возвращает список доступных для выбора МО, учитывая наличие объема ДВН_Б_ПРИК
	 */
	function getLpuIdsIfVolumeIsDvn_B_PrikOrNot()
	{
		$sessionParams = getSessionParams();
		if (empty($sessionParams['Lpu_id'])) {
			// на случай если в сесии нет, чтоб не взрывалось
			$sessionParams['Lpu_id'] = null;
		}

		$data = array('Lpu_id' => $sessionParams['Lpu_id']);

		$response = $this->dbmodel->getLpuIdsIfVolumeIsDvn_B_PrikOrNot($data);

		$this->ProcessModelSave($response)->ReturnData();

		return true;
	}

	/**
	 *	Проверка на ДВН
	 */
	function checkPersons() {
		$data = $this->ProcessInputData('checkPersons', true);
		if ($data === false) { return false; }

		$this->load->library('parser');

		$response = $this->dbmodel->checkPersons($data);
		$this->parser->parse('check_evnpldispdop', $response);

		return true;
	}

	/**
	 *  Получение грида "информированное добровольное согласие по ДД 2013"
	 *  Входящие данные: EvnPLDispDop13_id
	 * @throws Exception
	 */	
	function loadDopDispInfoConsent() {
		$data = $this->ProcessInputData('loadDopDispInfoConsent', true);
		if ($data === false) { return false; }
	
		$response = $this->dbmodel->loadDopDispInfoConsent($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	*  Получение грида "информированное добровольное согласие по ДД 2013" для Ext6
	*  Входящие данные: EvnPLDispDop13_id
	*/	
	function loadDopDispInfoConsentWithUsluga() {
		$data = $this->ProcessInputData('loadDopDispInfoConsent', true);
		if ($data === false) { return false; }
	
		$response = $this->dbmodel->loadDopDispInfoConsentWithUsluga($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	*  Получение списка изменившихся согласий
	*  Входящие данные: EvnPLDispDop13_id
	*/
	function getDopDispInfoConsentChanges() {
		$data = $this->ProcessInputData('getDopDispInfoConsentChanges', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getDopDispInfoConsentChanges($data);
		$this->ProcessModelSave($response, true, 'Ошибка получения списка изменившихся согласий')->ReturnData();
	}

	/**
	*  Проверка пациент в регистре ли ВОВ
	*/
	function checkPersonInWowRegistry() {
		$data = $this->ProcessInputData('checkPersonInWowRegistry', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->checkPersonInWowRegistry($data);
		$this->ProcessModelSave($response, true, 'Ошибка получения списка изменившихся согласий')->ReturnData();
	}

	/**
	*  Получение списка исследований невозможных для переноса в профосмотр
	*  Входящие данные: EvnPLDispDop13_id
	*/
	function loadEvnUslugaDispDopTransferFailGrid() {
		$data = $this->ProcessInputData('loadEvnUslugaDispDopTransferFailGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadEvnUslugaDispDopTransferFailGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	*  Получение списка исследований возможных для переноса в профосмотр
	*  Входящие данные: EvnPLDispDop13_id
	*/
	function loadEvnUslugaDispDopTransferSuccessGrid() {
		$data = $this->ProcessInputData('loadEvnUslugaDispDopTransferSuccessGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadEvnUslugaDispDopTransferSuccessGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	*  Печать талона ДД
	*  Входящие данные: $_GET['EvnPLDispDop13_id']
	*  На выходе: форма для печати талона ДД
	*  Используется: форма редактирования талона ДД
	*/
	function printEvnPLDispDop13() {
		$this->load->helper('Options');
		$this->load->library('parser');

		$data = $this->ProcessInputData('printEvnPLDispDop13', true);
		if ($data === false) { return false; }

		if ( isset($data['blank_only']) && $data['blank_only'] == 2 ) {
			$data['blank_only'] = true;
		} else {
			$data['blank_only'] = false;
		}

		//// Получаем настройки
		//$options = getOptions();

		// Получаем данные по талону ДД
		$response = $this->dbmodel->getEvnPLDispDop13Fields($data);

		if ( !is_array($response) || count($response) == 0 ) {
			echo 'Ошибка при получении данных по талону ДД';
			return true;
		}
		
		$evn_vizit_pl_dd_data = array();
		$evn_usluga_pl_dd_data = array();

		$evn_vizit_pl_dd_data = array('1' => array(), '2' => array(), '3' => array(), '4' => array(), '5' => array(), '6' => array());
		foreach ( $evn_vizit_pl_dd_data as $key => $val)
		{
			$evn_vizit_pl_dd_data[$key] = array('', '', '', '', '', '', '', '', '', '', '', '');
		}
		$response_temp = $this->dbmodel->loadEvnVizitDispDopData($data);
		if ( is_array($response_temp) ) {
			foreach ($response_temp as $row)
			{
				switch ($row['DopDispSpec_id'])
				{
					case 1: 
						$key = '1';
					break;
					case 2: 
						$key = '2';
					break;
					case 3: 
						$key = '3';
					break;
					case 5: 
						$key = '4';
					break;
					case 6: 
						$key = '5';
					break;
					default: 
						$key = '6';					
				}
				
				$evn_vizit_pl_dd_data[$key][0] = $row['MedPersonal_TabCode'];
				$evn_vizit_pl_dd_data[$key][1] = $row['EvnVizitDispDop_setDate'];
				if ( $row['DopDispDiagType_id'] == 1 )
				{
					$evn_vizit_pl_dd_data[$key][2]	= $row['Diag_Code'];
				}
				else
				{
					$evn_vizit_pl_dd_data[$key][3]	= $row['Diag_Code'];
				}
				if ( $row['DeseaseStage_id'] == 2 )
					$evn_vizit_pl_dd_data[$key][4]	= $row['Diag_Code'];
				switch ( $row['HealthKind_id'] )
				{
					case 1: 
						$evn_vizit_pl_dd_data[$key][5] = '+';
					break;
					case 2: 
						$evn_vizit_pl_dd_data[$key][6] = '+';
					break;
					case 3: 
						$evn_vizit_pl_dd_data[$key][7] = '+';
						if ( $row['DopDispDiagType_id'] == 2 )
							$evn_vizit_pl_dd_data[$key][8] = '+';
					break;
					case 4: 
						$evn_vizit_pl_dd_data[$key][9] = '+';
					break;
					case 5: 
						$evn_vizit_pl_dd_data[$key][10] = '+';
					break;						
				}
				if ( $row['EvnVizitDispDop_IsSanKur'] == 2 )
					$evn_vizit_pl_dd_data[$key][11] = '+';
			}
		}
		
		$evn_usluga_pl_dd_data = array('1' => array(), '2' => array(), '3' => array(), '4' => array(), '5' => array(), '6' => array(), '7' => array(), '8' => array(), '9' => array(), '10' => array(), '11' => array(), '12' => array(), '13' => array(), '14' => array(), '15' => array(), '16' => array(), '17' => array(), '18' => array(), '19' => array());
		foreach ( $evn_usluga_pl_dd_data as $key => $val)
		{
			$evn_usluga_pl_dd_data[$key] = array('', '');
		}
		
		$this->load->model('EvnUslugaDispDop_model', 'euddmodel');
		$data['EvnPLDisp_id'] = $data['EvnPLDispDop13_id'];
		$response_temp = $this->euddmodel->loadEvnUslugaDispDopData($data);
		if ( is_array($response_temp) ) {
			foreach ($response_temp as $row)
			{
				switch ($row['DopDispUslugaType_id'])
				{
					case 1: 
						$key = '4';
					break;
					case 2: 
						$key = '11';
					break;
					case 3: 
						$key = '1';
					break;
					case 4: 
						$key = '12';
					break;
					case 5: 
						$key = '17';
					break;
					case 6: 
						$key = '16';
					break;
					case 7: 
						$key = '15';
					break;
					case 8: 
						$key = '19';
					break;
					case 9: 
						$key = '5';
					break;
					case 10: 
						$key = '6';
					break;
					case 11: 
						$key = '13';
					break;
					case 12: 
						$key = '14';
					break;
					case 13: 
						$key = '3';
					break;
					case 14: 
						$key = '7';
					break;
					case 15: 
						$key = '8';
					break;
					case 16: 
						$key = '9';
					break;
					case 17: 
						$key = '10';
					break;
					case 18: 
						$key = '18';
					break;
				}				
				$evn_usluga_pl_dd_data[$key][0] = !empty($row['EvnUslugaDispDop_setDate'])?$row['EvnUslugaDispDop_setDate']:'';
				$evn_usluga_pl_dd_data[$key][1] = !empty($row['EvnUslugaDispDop_didDate'])?$row['EvnUslugaDispDop_didDate']:'';
			}
		}

		$template = 'evn_pl_disp_dop_template_list_a4';
		if ( $data['blank_only'] === true )
			$template = 'evn_pl_disp_dop_template_list_a4_empty';

		$print_data = $response[0];
		$print_data['evn_vizit_pl_dd_data'] = $evn_vizit_pl_dd_data;
		$print_data['evn_usluga_pl_dd_data'] = $evn_usluga_pl_dd_data;

		return $this->parser->parse($template, $print_data);
	}
	
	
	/**
	*  Печать паспорта здоровья
	*  Входящие данные: $_GET['EvnPLDispDop13_id']
	*  На выходе: форма для печати паспорта здоровья
	*  Используется: форма редактирования/просмотра/поиска/поточного ввода талонов ДД
	*/
	function printEvnPLDispDop13Passport() {
		$this->load->helper('Options');
		$this->load->library('parser');

		$template = 'evn_pl_passport_template_2013';
		$default_val = '&nbsp;';
		$yrdt = array();
		$years = array();
		$print_data = array();
		$data = array();

		$start_year = 2013;
		$data['start_year'] = $start_year;
		
		$data = $this->ProcessInputData('printEvnPLDispDop13', true);
		if ($data === false) { return false; }
		
		// Получаем данные		
		//года
		$now = getdate();
		for ($i = 1; $i <= 5; $i++) {
			$yr = $start_year+$i-1;
			$print_data["yr$i"] = $yr;
			$print_data["year$i"] = $yr <= $now['year'] ? $yr : '20_____г.';
			$yrdt[$yr] = ($yr <= $now['year']);
			$years[] = $yr;
		}

		//данные по талону
		$passport_data = $this->dbmodel->getEvnPLDispDop13PassportFields($data);
        $print_data['IIIa'] = "
                                III группа состояния здоровья - граждане, не имеющие хронические неинфекционные заболевания, но требующие установления диспансерного наблюдения или
                                оказания специализированной, в том числе высокотехнологичной, медицинской помощи по поводу иных заболеваний, а также граждане с подозрением на наличие
                                этих заболеваний, нуждающиеся в дополнительном обследовании.
                                ";
        $print_data['IIIb'] = "&nbsp;";
        if($passport_data['is_new_event'] == 1) //Если дата случая - позднее 01 апреля 2015
        {
            $print_data['IIIa'] = "
                                    IIIа группа состояния здоровья - граждане, имеющие хронические неинфекционные заболевания, требующие установления диспансерного наблюдения или оказания
                                    специализированной, в том числе высокотехнологичной, медицинской помощи, а также граждане с подозрением на наличие этих заболеваний (состояний), нуждающиеся
                                    в дополнительном обследовании.
                                    ";
            $print_data['IIIb'] = "
                                    IIIб группа состояния здоровья - граждане, не имеющие хронические неинфекционные заболевания, но требующие установления диспансерного наблюдения или оказания
                                    специализированной, в том числе высокотехнологичной, медицинской помощи по поводу иных заболеваний, а также граждане с подозрением на наличие этих заболеваний,
                                    нуждающиеся в дополнительном обследовании.
                                    ";
        }
		//Получаем название месяца

		switch ($passport_data['Person_BirthDay_Month'])
		{
			case 1:
				$print_data['p_bd_m'] = 'января';
				break;
			case 2:
				$print_data['p_bd_m'] = 'февраля';
				break;
			case 3:
				$print_data['p_bd_m'] = 'марта';
				break;
			case 4:
				$print_data['p_bd_m'] = 'апреля';
				break;
			case 5:
				$print_data['p_bd_m'] = 'мая';
				break;
			case 6:
				$print_data['p_bd_m'] = 'июня';
				break;
			case 7:
				$print_data['p_bd_m'] = 'июля';
				break;
			case 8:
				$print_data['p_bd_m'] = 'августа';
				break;
			case 9:
				$print_data['p_bd_m'] = 'сентября';
				break;
			case 10:
				$print_data['p_bd_m'] = 'октября';
				break;
			case 11:
				$print_data['p_bd_m'] = 'ноября';
				break;
			case 12:
				$print_data['p_bd_m'] = 'декабря';
				break;
			default:
				$print_data['p_bd_m'] = '';
		}

		$print_data['person_surname'] = $passport_data['Person_SurName'];
		$print_data['person_firname'] = $passport_data['Person_FirName'];
		$print_data['person_secname'] = $passport_data['Person_SecName'];
		$print_data['Sex_id'] = $passport_data['Sex_id'];
		$print_data['person_phone'] = $passport_data['Person_Phone'];
		$print_data['p_bd_d'] = $passport_data['Person_BirthDay_Day'];
		$print_data['p_bd_y'] = $passport_data['Person_BirthDay_Year'];
		$print_data['p_a'] = $passport_data['Address_Info'];
		$print_data['p_a_st'] = $passport_data['KLStreet_Name'];
		$print_data['p_a_h'] = $passport_data['Address_House'];
		$print_data['p_a_c'] = $passport_data['Address_Corpus'];
		$print_data['p_a_fl'] = $passport_data['Address_Flat'];		
		$print_data['dd_lpu'] = $passport_data['Lpu_Name'];
		$print_data['l_address'] = $passport_data['l_address'];
		$print_data['dd_lpu_phone'] = $passport_data['Org_Phone'];
		$print_data['personcard_code'] = $passport_data['PersonCard_Code'];
		$print_data['lpuregion_name'] = $passport_data['LpuRegion_Name'];
		$print_data['medperson_fio'] = $passport_data['MedPerson_Fio'];
		$print_data['polis_ser'] = $passport_data['Polis_Ser'];
		$print_data['polis_num'] = $passport_data['Polis_Num'];
		$print_data['polis_orgnick'] = $passport_data['polis_orgnick'];
		$print_data['person_snils'] = $passport_data['Person_Snils'];

		//Установленные заболевания
		for ($i=0;$i<10;$i++){
			$print_data['Diag_Name_'.$i] = '&nbsp;';
			$print_data['Diag_Code_'.$i] = '&nbsp;';
			$print_data['Diag_date_'.$i] = '&nbsp;';
		}

		if(isset($passport_data['diags']) && isset($_GET['printDiag']) && $_GET['printDiag'] == '1') {
			for ($i=0;$i<10;$i++){
				if(isset($passport_data['diags'][$i])){
					$diag_date = '';
					if(isset($passport_data['diags'][$i]['Diag_date']))
						$diag_date = $passport_data['diags'][$i]['Diag_date'];
					else
					{
						if(isset($passport_data['diddate_19']))
							$diag_date = $passport_data['diddate_19'];
					}
					$print_data['Diag_Name_'.$i] = $passport_data['diags'][$i]['Diag_Name'];
					$print_data['Diag_Code_'.$i] = $passport_data['diags'][$i]['Diag_Code'];
					$print_data['Diag_date_'.$i] = $diag_date;
				}
			}
		}
		//Факторы риска
		for ($i=1; $i <= 5; $i++)
		{
			$print_data['dd_date_'.$i] = '&nbsp;';
			$print_data['person_height_'.$i] = '&nbsp;';
			$print_data['person_weight_'.$i] = '&nbsp;';
			$print_data['body_mass_index_'.$i] = '&nbsp;';
			$print_data['risk_overweight_'.$i] = '&nbsp;';
			$print_data['total_cholesterol_'.$i] = '&nbsp;';
			$print_data['risk_dyslipidemia_'.$i] = '&nbsp;';
			$print_data['glucose_'.$i] = '&nbsp;';
			$print_data['risk_gluk_'.$i] = '&nbsp;';
			$print_data['person_pressure_'.$i] = '&nbsp;';
			$print_data['risk_high_pressure_'.$i] = '&nbsp;';
			$print_data['IsSmoking_'.$i] = '&nbsp;';
			$print_data['IsLowActiv_'.$i] = '&nbsp;';
			$print_data['IsIrrational_'.$i] = '&nbsp;';
			$print_data['IsRiskAlco_'.$i] = '&nbsp;';
			$print_data['risk_narco_'.$i] = '&nbsp;';
			$print_data['summ_risk_'.$i] = '&nbsp;';
			$print_data['her_diag_'.$i] = '&nbsp;';
			$print_data['dd_medpersonal_'.$i] = '&nbsp;';
			$print_data['hk_'.$i] = '&nbsp;';
		}
		//case when (CAST(systolic_blood_pressure.systolic_blood_pressure as numeric) > 140 or cast(diastolic_blood_pressure.diastolic_blood_pressure as numeric) > 90) then 'Да' else 'Нет' end as risk_high_pressure,
				//case when CAST(body_mass_index.body_mass_index as numeric) >= 25 then 'Да' else 'Нет' end as risk_overweight,
				//case when cast(glucose.glucose as numeric) > 6 then 'Да' else 'Нет' end as risk_gluk,
				//case when cast(total_cholesterol.total_cholesterol as numeric) > 5 then 'Да' else 'Нет' end as risk_dyslipidemia,
		//$risks = $this->dbmodel->getRiskFactorsForPassport($data);
		$risks = $this->dbmodel->getRiskFactorsForPassport(array('Person_id' => $passport_data['Person_id']));
		//var_dump($risks);die;
		for($i=4; $i>=0; $i--)
		{
			if(isset($risks[$i])){
				$print_data['dd_date_'.($i+1)] 				= $risks[$i]['dd_date'];
				$print_data['person_height_'.($i+1)] 		= ($risks[$i]['person_height']=='')?'':(float)$risks[$i]['person_height'];
				$print_data['person_weight_'.($i+1)] 		= ($risks[$i]['person_weight']=='')?'':(float)$risks[$i]['person_weight'];
				$print_data['body_mass_index_'.($i+1)] 		= ($risks[$i]['body_mass_index']=='')?'':(float)$risks[$i]['body_mass_index'];

				$risk_overweight 							= $risks[$i]['body_mass_index'] > 25 ? 'Да' : 'Нет';
				$print_data['risk_overweight_'.($i+1)] 		= $risk_overweight;// $risks[$i]['risk_overweight'];

				$print_data['total_cholesterol_'.($i+1)] 	= ($risks[$i]['total_cholesterol']=='')?'':(float)$risks[$i]['total_cholesterol'];
				$risk_dyslipidemia 							= $risks[$i]['total_cholesterol'] > 5 ? 'Да' : 'Нет';;
				$print_data['risk_dyslipidemia_'.($i+1)] 	= $risk_dyslipidemia;//isset($risks[$i]['total_cholesterol'])?$risks[$i]['risk_dyslipidemia']:'';

				$print_data['glucose_'.($i+1)] 				= ($risks[$i]['glucose']=='')?'':(float)$risks[$i]['glucose'];
				$risk_gluk 									= $risks[$i]['glucose'] > 6 ? 'Да' : 'Нет';
				$print_data['risk_gluk_'.($i+1)] 			= $risk_gluk;//isset($risks[$i]['glucose'])?$risks[$i]['risk_gluk']:'';

				$print_data['person_pressure_'.($i+1)] 		= (($risks[$i]['systolic_blood_pressure']!='')&&($risks[$i]['diastolic_blood_pressure']!=''))?$risks[$i]['person_pressure']:'';
				$risk_high_pressure 						= ($risks[$i]['systolic_blood_pressure'] > 140 || $risks[$i]['diastolic_blood_pressure'] > 90) ? 'Да' : 'Нет';
				$print_data['risk_high_pressure_'.($i+1)] 	= $risk_high_pressure;//(isset($risks[$i]['systolic_blood_pressure'])&&isset($risks[$i]['diastolic_blood_pressure']))?$risks[$i]['risk_high_pressure']:'';

				$print_data['IsSmoking_'.($i+1)] 			= $risks[$i]['IsSmoking'];
				$print_data['IsLowActiv_'.($i+1)] 			= $risks[$i]['IsLowActiv'];
				$print_data['IsIrrational_'.($i+1)] 		= $risks[$i]['IsIrrational'];
				$print_data['IsRiskAlco_'.($i+1)] 			= $risks[$i]['IsRiskAlco'];
				$print_data['risk_narco_'.($i+1)] 			= $risks[$i]['risk_narco'];
				$print_data['summ_risk_'.($i+1)] 			= ($risks[$i]['summ_risk']=='; ')?'':$risks[$i]['summ_risk'];
				$print_data['her_diag_'.($i+1)] 			= $risks[$i]['her_diag'];
				$print_data['dd_medpersonal_'.($i+1)] 		= $risks[$i]['dd_medpersonal'];
				$print_data['hk_'.($i+1)]					= $risks[$i]['HealthKind_Name'];

			}
		}
		//группы здоровья	
		$ddyears = array(0,0,0,0);
		$cnt = 0;
		/*foreach($passport_data['health_groups'] as $y=>$v) {
			if (in_array($y, $years))
				$ddyears[$cnt++] = $y;
		}
		
		for ($i = 0; $i < 4; $i++) {
			$y = $ddyears[$i];
			$print_data['ddyr'.($i+1)] = $y > 0 ? $y : '&nbsp;';
			$print_data['dd1v'.($i+1)] = isset($passport_data['health_groups'][$y]) && $yrdt[$y] ? $passport_data['health_groups'][$y]['date'] : '&nbsp;';
			$print_data['dd2v'.($i+1)] = isset($passport_data['health_groups'][$y]) && $yrdt[$y] ? $passport_data['health_groups'][$y]['value'] : '&nbsp;';
		}*/
		
		//print('<pre>'); print_r($passport_data['diseases']); print('</pre>'); die;

		return $this->parser->parse($template, $print_data);
	}

	/**
	 * Сохранение данных по анкетированию
	 * @throws Exception
	 */
	function saveDopDispQuestionGrid() {
		$data = $this->ProcessInputData('saveDopDispQuestionGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveDopDispQuestionGrid($data);
		$this->ProcessModelSave($response, true, 'При сохранении возникли ошибки')->ReturnData();

		return true;
	}
	
	/**
	 * Сохранение данных по информир. добр. согласию
	 */
	function saveDopDispInfoConsent() {
		$data = $this->ProcessInputData('saveDopDispInfoConsent', true);
		if ($data === false) { return false; }

		if (date('Y',strtotime($data['EvnPLDispDop13_consDate'])) < 2013) {
			$this->ReturnError('Дата информированного согласия должна быть 01.01.2013 и позже');
			return false;
		}
		
		$this->load->library('swFilterResponse'); 
		$response = $this->dbmodel->saveDopDispInfoConsent($data);
		$this->ProcessModelSave($response, true, 'При сохранении возникли ошибки')->ReturnData();

		return true;
	}
	
	/**
	 * Удаление посещения по дополнительной диспансеризации
	 */
	function deleteEvnPLDispDop13() {
		$data = $this->ProcessInputData('deleteEvnPLDispDop13', true);
		if ($data === false) { return false; }

		$this->load->model('Registry' . (getRegionNick() == 'ufa' ? 'Ufa' : '') . '_model', 'Reg_model');
        $registryData = $this->Reg_model->checkEvnAccessInRegistry($data);

        if ( is_array($registryData) ) {
            $response = $registryData;
        } else {
            $response = $this->dbmodel->deleteEvnPLDispDop13($data);
        }

		$this->ProcessModelSave($response, true, 'При удалении талона ДД возникли ошибки')->ReturnData();

		return true;
	}

	/**
	*  Проверка на наличие талона на этого человека в этом году
	*  Входящие данные: $_POST['Person_id']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования талона по ДД
	*/
	function checkIfEvnPLDispDop13Exists()
	{
		$data = $this->ProcessInputData('checkIfEvnPLDispDop13Exists', true);
		if ($data === false) { return false; }
	
		$response = $this->dbmodel->checkIfEvnPLDispDop13Exists($data);
		$this->ProcessModelSave($response, true)->ReturnData();
	}

	
	/**
	 *  Получение талонов ДД для человека
	 *  Входящие данные: $_POST['Person_id'],
	 *  На выходе: JSON-строка
	 *  Используется: окно истории лечения
	 */
	 
	function loadEvnPLDispDop13ForPerson()
	{
		$data = $this->ProcessInputData('loadEvnPLDispDop13ForPerson', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadEvnPLDispDop13ForPerson($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	
	/**
	 * Получение данных для формы редактирования талона по ДД
	 * Входящие данные: $_POST['EvnPLDispDop13_id']
	 * На выходе: JSON-строка
	 * Используется: форма редактирования талона по ДД
	 */
	function loadEvnPLDispDop13EditForm()
	{
		$data = $this->ProcessInputData('loadEvnPLDispDop13EditForm', true);
		if ($data === false) { return false; }
	
		$response = $this->dbmodel->loadEvnPLDispDop13EditForm($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}


	/**
	 * Получение списка талонов по ДД для потокового ввода
	 * Входящие данные: $_POST['begDate'],
	 *                 $_POST['begTime']
	 * На выходе: JSON-строка
	 * Используется: форма потокового ввода талонов по ДД
	 */
	function loadEvnPLDispDop13StreamList()
	{
		$data = $this->ProcessInputData('loadEvnPLDispDop13StreamList', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->loadEvnPLDispDop13StreamList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Получение данных формы редактирования посещений в талоне по ДД
	 * Входящие данные: $_POST['EvnVizitDispDop_id']
	 * На выходе: JSON-строка
	 * Используется: форма редактирования посещения по ДД
	 */
	function loadEvnVizitDispDopEditForm()
	{
		$data = $this->ProcessInputData('loadEvnVizitDispDopEditForm', true, true);
		if ($data === false) { return false; }

		if ($data['Lpu_id'] == 0)
		{
			$this->ReturnData(array('success' => false));
			return true;
		}
		$response = $this->dbmodel->loadEvnVizitDispDopEditForm($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}
	
	/**
	 * Получение списка посещений в талоне по ДД
	 * Входящие данные: $_POST['EvnPLDispDop13_id']
	 * На выходе: JSON-строка
	 * Используется: форма редактирования талона по ДД
	 */
	function loadEvnVizitDispDopGrid()
	{
		$data = $this->ProcessInputData('loadEvnVizitDispDopGrid', true, true);
		if ($data === false) { return false; }
		
		if ($data['Lpu_id'] == 0)
		{
			$this->ReturnData(array('success' => false));
			return true;
		}
		$response = $this->dbmodel->loadEvnVizitDispDopGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		
		return true;
	}
	
	/**
	 * Получение списка исследований в талоне по ДД
	 * Входящие данные: $_POST['EvnPLDispDop13_id']
	 * На выходе: JSON-строка
	 * Используется: форма редактирования талона по ДД
	 */
	function loadEvnUslugaDispDopGrid()
	{
		$data = $this->ProcessInputData('loadEvnUslugaDispDopGrid', true, true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->loadEvnUslugaDispDopGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		
		return true;
	}

	/**
	 * Сохранение талона амбулаторного пациента
	 * Входящие данные: ...
	 * На выходе: JSON-строка
	 * Используется: форма редактирования талона амбулаторного пациента
	 * @throws Exception
	 */
	function saveEvnPLDispDop13()
	{
		$data = $this->ProcessInputData('saveEvnPLDispDop13', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->saveEvnPLDispDop13($data);
		$this->ProcessModelSave($response, true, 'При сохранении возникли ошибки')->ReturnData();
	}

	function saveEvnPLDispDop13Ext6()
	{
		$data = $this->ProcessInputData('saveEvnPLDispDop13Ext6', true);
		if ($data === false) { return false; }
		
		if($data['ignoreCheckDesease']!='2') {
			$alertDesease = $this->dbmodel->checkDesease($data);
			
			if(count($alertDesease)>0) {
				$this->ReturnData(array('success' => false, 'AlertDesease' => 'Внимание! У пациента есть:<br>'.implode(',<br>', $alertDesease).'.<br>Вы действительно хотите завершить диспансеризацию?'));
				return true;
			}
		}
		
		$response = $this->dbmodel->saveEvnPLDispDop13Ext6($data);
		
		$this->ProcessModelSave($response, true, 'При сохранении возникли ошибки')->ReturnData();
	}

	/**
	 * Получение числа талонов с распределением по годам, для формирования списка на клиенте
	 * Входящие данные: ...
	 * На выходе: JSON-строка
	 * Используется: форма поиска/потокового ввода талонов по ДД
	 */
	function getEvnPLDispDop13Years()
	{
		$data = getSessionParams();

		$year = date('Y');
		$info = $this->dbmodel->getEvnPLDispDop13Years($data);
		$outdata = $this->ProcessModelList($info, true, true)->GetOutData();
		
		$flag = false;
		foreach ($outdata as $row) {
			if ( $row['EvnPLDispDop13_Year'] == $year ) { $flag = true; }
		}
		if (!$flag) { $outdata[] = array('EvnPLDispDop13_Year'=>$year, 'count'=>0); }
		
		$this->ReturnData($outdata);
	}

	/**
	 * Получение числа талонов с распределением по годам, для формирования списка на клиенте
	 * Входящие данные: ...
	 * На выходе: JSON-строка
	 * Используется: форма поиска/потокового ввода талонов по ДД
	 */
	function getEvnPLDispDop13YearsSec()
	{
		$data = getSessionParams();

		$year = date('Y');
		$info = $this->dbmodel->getEvnPLDispDop13YearsSec($data);
		$outdata = $this->ProcessModelList($info, true, true)->GetOutData();

		$flag = false;
		foreach ($outdata as $row) {
			if ( $row['EvnPLDispDop13_Year'] == $year ) { $flag = true; }
		}
		if (!$flag) { $outdata[] = array('EvnPLDispDop13_Year'=>$year, 'count'=>0); }

		$this->ReturnData($outdata);
	}


	/**
	 * Получение даты формирования списка
	 */
	function getDopDispInfoConsentFormDate()
	{
		$data = $this->ProcessInputData('getDopDispInfoConsentFormDate', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->getDopDispInfoConsentFormDate($data);
		$this->ProcessModelList($response, true, 'При получении даты формирования возникли ошибки')->ReturnData();
	}
	
	/**
	*  Проверка наличия указанного диагноза в карте ДВН пациента
	*/
	function CheckDiag() {
		$data = $this->ProcessInputData('CheckDiag', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->CheckDiag($data);
		$this->ReturnData($response);
	}

	/**
	 * Загрузка списка назначений в двн
	 */
	function loadEvnPLDispDop13PrescrList() {
		$data = $this->ProcessInputData('loadEvnPLDispDop13PrescrList', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadEvnPLDispDop13PrescrList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	 * Загрузка граничных значений для АД
	 */
	function getArteriaPressGroundValues() {
		$response = $this->dbmodel->GetArteriaPressGroundValues();
		$this->ReturnData($response);
	}

	/**
	 * Загрузка граничных значений для ИВД
	 */
	function getEyePressGroundValues() {
		$response = $this->dbmodel->GetEyePressGroundValues();
		$this->ReturnData($response);
	}

	/**
	 * Получение формализованных параметров по SurveyType_id
	 * @throws Exception
	 */
	function getFormalizedInspectionParamsBySurveyType()
	{
		$data = $this->ProcessInputData("getFormalizedInspectionParamsBySurveyType", true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->getFormalizedInspectionParamsBySurveyType($data);
		echo @json_encode($response);
		return true;
	}

	/**
	 * Сохранение формализованных параметров (Text)
	 * @throws Exception
	 */
	function saveFormalizedInspectionParamsText()
	{
		$data = $this->ProcessInputData("saveFormalizedInspectionParamsText", true);
		if ($data === false) {
			return false;
		}
		$this->dbmodel->saveFormalizedInspectionParamsText($data);
		return true;
	}

	/**
	 * Сохранение формализованных параметров (Checkbox)
	 * @throws Exception
	 */
	function saveFormalizedInspectionParamsCheck()
	{
		$data = $this->ProcessInputData("saveFormalizedInspectionParamsCheck", true);
		if ($data === false) {
			return false;
		}
		$this->dbmodel->saveFormalizedInspectionParamsCheck($data);
		return true;
	}

	/**
	 * Форма ДВН(Панели) Получение данных о последнем изменившем и время изменения
	 * @throws Exception
	 */
	function getDVNPanelsLastUpdater()
	{
		$data = $this->ProcessInputData("getDVNPanelsLastUpdater", true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->getDVNPanelsLastUpdater($data);
		echo @json_encode($response);
		return true;
	}

	/**
	 * Проверка доступа текущего пользователя на изменения указанной ДВП(по EvnPLDispDop13_id)
	 * @throws Exception
	 */
	function checkEvnPLDispDop13Access()
	{
		$data = $this->ProcessInputData("checkEvnPLDispDop13Access", true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->checkEvnPLDispDop13Access($data);
		echo @json_encode($response);
		return true;
	}

	/**
	 * Форма ДВН(Окно выполнения услуги) Получение справочников
	 * @throws Exception
	 */
	function getDVNExecuteWindowRDS()
	{
		$data = $this->ProcessInputData("getDVNExecuteWindowRDS", true);
		if ($data === false) {
			return false;
		}
		$response = $this->dbmodel->getDVNExecuteWindowRDS($data);
		echo @json_encode($response);
		return true;
	}

	/**
	 *  Осмотр фельдшером (акушеркой) или врачом акушером-гинекологом 31
	 */
	function getEvnPLDispDop13EvnUslugaDispDopId() {//yl:найти EvnUslugaDispDopId
		$data = $this->ProcessInputData("saveEvnPLDispDop13GynecologistText", true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->getEvnUslugaDispDop_id($data);
		$this->ReturnData([["EvnUslugaDispDop_id" => $response]]);//yl:это внутренняя ф-я
	}
	function loadEvnPLDispDop13GynecologistText() {//yl:загрузить
		$data = $this->ProcessInputData("saveEvnPLDispDop13GynecologistText", true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->loadEvnPLDispDop13GynecologistText($data);
		$this->ReturnData($response);
	}
	function saveEvnPLDispDop13GynecologistText() {//yl:обновить
		$data = $this->ProcessInputData("saveEvnPLDispDop13GynecologistText", true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->saveEvnPLDispDop13GynecologistText($data);
		$this->ReturnData($response);
	}
	
	/**
	 * Получение справочника и имеющейся информации по указанной карте ДВН для Индивидуального профилактического консультирования
	 */
	function getIndiProfConsult() {
		$data = $this->ProcessInputData('getIndiProfConsult', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getIndiProfConsult($data);
		
		if (is_array($response)) {
			$this->ReturnData(array('success' => true, 'Error_Msg' => '', 'data' => $response));
			return true;
		} else {
			$this->ReturnData(array('success' => false, 'Error_Msg' => 'Ошибка при чтении данных индивидуального профилактического консультирования'));
			return false;
		}
	}

	/**
	 * Факторы риска
	 */
	function loadEvnPLDispDop13FactorType() {//yl:меню
		$data = $this->ProcessInputData("loadEvnPLDispDop13FactorType", true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->loadEvnPLDispDop13FactorType($data);
		$this->ReturnData($response);
	}
	function loadEvnPLDispDop13FactorRisk() {//yl:грид
		$data = $this->ProcessInputData("loadEvnPLDispDop13FactorRisk", true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->loadEvnPLDispDop13FactorRisk($data);
		$this->ReturnData($response);
	}
	function addEvnPLDispDop13FactorRisk() {//yl:добавить
		$data = $this->ProcessInputData("addEvnPLDispDop13FactorRisk", true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->addEvnPLDispDop13FactorRisk($data);
		$this->ReturnData($response);
	}
	function delEvnPLDispDop13FactorRisk(){//yl:удалить
		$data = $this->ProcessInputData("delEvnPLDispDop13FactorRisk", true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->delEvnPLDispDop13FactorRisk($data);
		$this->ReturnData($response);
	}

	/**
	 *  Подозрения
	 */
	function loadEvnPLDispDop13DispDeseaseSuspType() {//yl:меню
		$data = $this->ProcessInputData("loadEvnPLDispDop13DispDeseaseSuspType", true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->loadEvnPLDispDop13DispDeseaseSuspType($data);
		$this->ReturnData($response);
	}
	function loadEvnPLDispDop13Desease() {//yl:грид
		$data = $this->ProcessInputData("loadEvnPLDispDop13Desease", true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->loadEvnPLDispDop13Desease($data);
		$this->ReturnData($response);
	}
	function addEvnPLDispDop13DispDeseaseSuspType() {//yl:добавить
		$data = $this->ProcessInputData("addEvnPLDispDop13DispDeseaseSuspType", true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->addEvnPLDispDop13DispDeseaseSuspType($data);
		$this->ReturnData($response);
	}
	function delEvnPLDispDop13DispDeseaseSusp(){//yl:удалить
		$data = $this->ProcessInputData("delEvnPLDispDop13DispDeseaseSusp", true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->delEvnPLDispDop13DispDeseaseSusp($data);
		$this->ReturnData($response);
	}
	/**
	 *  Заболевания
	 */
	function addEvnPLDispDop13EvnDiagDopDisp() {//yl:добавить диагноз
		$data = $this->ProcessInputData("addEvnPLDispDop13EvnDiagDopDisp", true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->addEvnPLDispDop13EvnDiagDopDisp($data);
		$this->ReturnData($response);
	}
	function delEvnPLDispDop13EvnDiagDopDisp(){//yl:удалить диагноз
		$data = $this->ProcessInputData("delEvnPLDispDop13EvnDiagDopDisp", true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->delEvnPLDispDop13EvnDiagDopDisp($data);
		$this->ReturnData($response);
	}
	function loadEvnPLDispDop13DiagSetClass() {//yl:комбо типов диагноза грида
		$response = $this->dbmodel->loadEvnPLDispDop13DiagSetClass();
		$this->ReturnData($response);
	}
	function updEvnPLDispDop13DeseaseDiagSetClass() {//yl:изменить тип диагноза
		$data = $this->ProcessInputData("updEvnPLDispDop13DeseaseDiagSetClass", true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->updEvnPLDispDop13DeseaseDiagSetClass($data);
		$this->ReturnData($response);
	}
	/**
	 * Сохранение EvnPLDispDop13_SumRick в EvnPLDispDop13
	 */
	function saveEvnPLDispDop13_SumRick() {
		$data = $this->ProcessInputData("saveEvnPLDispDop13_SumRick", true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->saveEvnPLDispDop13_SumRick($data);
		$this->ReturnData($response);
	}
	
	/**
	 * Сохранение подозрения на ЗНО и диагноза подозрения
	 */
	function saveEvnPLDispDop13_SuspectZNO() {
		$data = $this->ProcessInputData("saveEvnPLDispDop13_SuspectZNO", true);
		if ($data === false) { return false; }
		$response = $this->dbmodel->saveEvnPLDispDop13_SuspectZNO($data);
		$this->ReturnData($response);
	}
}
?>