<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnPLDispTeen14 - контроллер для управления талонами диспансеризации 14ти летних подростков
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Polka
 * @access			public
 * @copyright		Copyright (c) 2009 - 2011 Swan Ltd.
 * @author			Pshenitcyn Ivan aka IVP (ipshon@gmail.com)
 * @version			01.08.2011
 */

class EvnPLDispTeen14 extends swController
{
	/**
	 * Description
	 */
    function __construct()
    {
        parent::__construct();
		
        $this->load->database();
		$this->load->model('Polka_EvnPLDispTeen14_model', 'dbmodel');
		
        $this->inputRules = array(
            'loadEvnPLDispTeen14EditForm' => array(
                array(
                    'field' => 'EvnPLDispTeen14_id',
                    'label' => 'Идентификатор талона по доп. диспансеризации',
                    'rules' => 'trim|required',
                    'type' => 'id'
                )
            ),
            'loadEvnVizitDispTeen14EditForm' => array(
                array(
                    'field' => 'EvnVizitDispTeen14_id',
                    'label' => 'Идентификатор посещения по доп. диспансеризации',
                    'rules' => 'trim|required',
                    'type' => 'id'
                )
            ),
            'deleteEvnPLDispTeen14' => array(
                array(
                    'field' => 'EvnPLDispTeen14_id',
                    'label' => 'Идентификатор талона по доп. диспансеризации',
                    'rules' => 'trim|required',
                    'type' => 'id'
                )
            ),
            'checkIfEvnPLDispTeen14Exists' => array(
                array(
                    'field' => 'Person_id',
                    'label' => 'Идентификатор человека',
                    'rules' => 'trim|required',
                    'type' => 'id'
                )
            ),
            'loadEvnVizitDispTeen14Grid' => array(
                array(
                    'field' => 'EvnPLDispTeen14_id',
                    'label' => 'Идентификатор талона по доп. диспансеризации',
                    'rules' => 'trim|required',
                    'type' => 'id'
                )
            ),
            'loadEvnUslugaDispTeen14Grid' => array(
                array(
                    'field' => 'EvnPLDispTeen14_id',
                    'label' => 'Идентификатор талона по доп. диспансеризации',
                    'rules' => 'trim|required',
                    'type' => 'id'
                )
            ),
            'searchEvnPLDispTeen14' => array(
                array(
                    'field' => 'DocumentType_id',
                    'label' => 'Тип документа удостовряющего личность',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'EvnPLDispTeen14_disDate',
                    'label' => 'Дата завершения случая',
                    'rules' => 'trim',
                    'type' => 'daterange'
                ),
                array(
                    'field' => 'EvnPLDispTeen14_IsFinish',
                    'label' => 'Случай завершен',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'EvnPLDispTeen14_setDate',
                    'label' => 'Дата начала случая',
                    'rules' => 'trim',
                    'type' => 'daterange'
                ),
                array(
                    'field' => 'OMSSprTerr_id',
                    'label' => 'Территория страхования',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Org_id',
                    'label' => 'Место работы',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'OrgDep_id',
                    'label' => 'Организация выдавшая документ',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'OrgSmo_id',
                    'label' => 'Страховая компания',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'PersonAge_Min',
                    'label' => 'Возраст с',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'field' => 'PersonAge_Max',
                    'label' => 'Возраст по',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'field' => 'PersonCard_Code',
                    'label' => 'Номер амб. карты',
                    'rules' => 'trim',
                    'type' => 'string'
                ),
                array(
                    'field' => 'LpuRegion_id',
                    'label' => 'Участок',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Person_Birthday',
                    'label' => 'Дата рождения',
                    'rules' => 'trim',
                    'type' => 'daterange'
                ),
                array(
                    'field' => 'Person_Surname',
                    'label' => 'Фамилия',
                    'rules' => 'trim',
                    'type' => 'russtring'
                ),
                array(
                    'field' => 'Person_Firname',
                    'label' => 'Имя',
                    'rules' => 'trim',
                    'type' => 'russtring'
                ),
                array(
                    'field' => 'Person_Secname',
                    'label' => 'Отчество',
                    'rules' => 'trim',
                    'type' => 'russtring'
                ),
                array(
                    'field' => 'Person_Snils',
                    'label' => 'СНИЛС',
                    'rules' => 'trim',
                    'type' => 'snils'
                ),
                array(
                    'field' => 'PolisType_id',
                    'label' => 'Тип полиса',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Post_id',
                    'label' => 'Должность',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'PrivilegeType_id',
                    'label' => 'Категория льготы',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Sex_id',
                    'label' => 'Пол',
                    'rules' => 'trim',
                    'type' => 'id',
                    'default' => -1
                ),
                array(
                    'field' => 'SocStatus_id',
                    'label' => 'Социальный статус',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
            ),
            'saveEvnPLDispTeen14' => array(
                array(
                    'field' => 'EvnPLDispTeen14_id',
                    'label' => 'Идентификатор талона по ДД',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'PersonEvn_id',
                    'label' => 'Идентификатор человека в событии',
                    'rules' => 'trim|required',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Person_id',
                    'label' => 'Идентификатор человека',
                    'rules' => 'trim|required',
                    'type' => 'id'
                ),
                array(
                    'field' => 'EvnPLDispTeen14_IsFinish',
                    'label' => 'Случай закончен',
                    'rules' => 'trim|required',
                    'type' => 'id'
                ),
                array(
                    'field' => 'PersonWeight_Weight',
                    'label' => 'Масса',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
				array(
					'default' => 37,
					'field' => 'Okei_id',
					'label' => '',
					'rules' => 'trim',
					'type' => 'id'
				),
                array(
                    'default' => 1,
                    'field' => 'PersonWeight_IsWeightAbnorm',
                    'label' => 'Отклонение',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'WeightAbnormType_id',
                    'label' => 'Тип отклонения',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'PersonChild_Height',
                    'label' => 'Рост',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'default' => 1,
                    'field' => 'PersonChild_IsHeightAbnorm',
                    'label' => 'Отклонение',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'HeightAbnormType_id',
                    'label' => 'Тип отклонения',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'PsychicalConditionType_id',
                    'label' => 'Психическое развитие',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'SexualConditionType_id',
                    'label' => 'Физическое развитие',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'DopDispResType_id',
                    'label' => '',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'DispResMedicalMeasureType_id',
                    'label' => '',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'EvnPLDispTeen14_isHTAid',
                    'label' => '',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'EvnPLDispTeen14_HTAidDT',
                    'label' => '',
                    'rules' => 'trim',
                    'type' => 'date'
                ),
                array(
                    'default' => 1,
                    'field' => 'InvalidType_id',
                    'label' => 'Инвалидность',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Server_id',
                    'label' => 'Идентификатор сервера',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'EvnVizitDispTeen14',
                    'label' => 'Осмотры',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'EvnUslugaDispTeen14',
                    'label' => 'Исследования',
                    'rules' => '',
                    'type' => 'string'
                ),
				array(
					'field' => 'PediatrDate',
					'label' => 'Дата осмотра педиатра',
					'rules' => '',
					'type'  => 'string'
				)
            ),
            'loadEvnPLDispTeen14StreamList' => array(
                array(
                    'field' => 'begDate',
                    'label' => 'Дата',
                    'rules' => 'trim|required',
                    'type' => 'date'
                ),
                array(
                    'field' => 'begTime',
                    'label' => 'Время',
                    'rules' => 'trim|required',
                    'type' => 'string'
                )
            ),
			'printEvnPLDispTeen14' => array(
                array(
                    'field' => 'EvnPLDispTeen14_id',
                    'label' => 'Идентификатор талона по ДД',
                    'rules' => 'trim|required',
                    'type' => 'id'
                ),
                array(
                    'field' => 'blank_only',
                    'label' => 'Флаг бланка',
                    'rules' => 'trim',
                    'type' => 'string'
                )
			),
			'printEvnPLDispTeen14Passport' => array(
                array(
                    'field' => 'EvnPLDispTeen14_id',
                    'label' => 'Идентификатор талона по ДД',
                    'rules' => 'trim|required',
                    'type' => 'id'
                )
			)
        );
        $this->inputRules['searchEvnPLDispTeen14'] = array_merge($this->inputRules['searchEvnPLDispTeen14'],getAddressSearchFilter());
    }

    /**
     *  Печать талона ДД
     *  Входящие данные: $_GET['EvnPLDispTeen14_id']
     *  На выходе: форма для печати талона ДД
     *  Используется: форма редактирования талона ДД
     */
    function printEvnPLDispTeen14() {
        $this->load->helper('Options');
        $this->load->library('parser');

        $data = $this->ProcessInputData('printEvnPLDispTeen14', true);
        if ($data === false) { return false; }

        if ( isset($data['blank_only']) && $data['blank_only'] == 2 )
        {
            $data['blank_only'] = true;
        } else {
			$data['blank_only'] = false;
		}

        //// Получаем настройки
        //$options = getOptions();

        // Получаем данные по талону ДД
        $response = $this->dbmodel->getEvnPLDispTeen14Fields($data);
        $this->ProcessModelList($response,true,true,'Ошибка при получении данных по талону ДД')->ReturnData();

        $evn_vizit_pl_dt14_data = array();
        $evn_usluga_pl_dt14_data = array();

        $evn_vizit_pl_dt14_data = array('1' => array(), '2' => array(), '3' => array(), '4' => array(), '5' => array(), '6' => array());
        foreach ( $evn_vizit_pl_dt14_data as $key => $val)
        {
            $evn_vizit_pl_dt14_data[$key] = array('', '', '', '', '', '', '', '', '', '', '', '');
        }
        $response_temp = $this->dbmodel->loadEvnVizitDispTeen14Data($data);
        if ( is_array($response_temp) ) {
            foreach ($response_temp as $row)
            {
                switch ($row['Teen14DispSpecType_id'])
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

                $evn_vizit_pl_dt14_data[$key][0] = $row['MedPersonal_TabCode'];
                $evn_vizit_pl_dt14_data[$key][1] = $row['EvnVizitDispTeen14_setDate'];
                if ( $row['DopDispDiagType_id'] == 1 )
                {
                    $evn_vizit_pl_dt14_data[$key][2]	= $row['Diag_Code'];
                }
                else
                {
                    $evn_vizit_pl_dt14_data[$key][3]	= $row['Diag_Code'];
                }
                if ( $row['DeseaseStage_id'] == 2 )
                    $evn_vizit_pl_dt14_data[$key][4]	= $row['Diag_Code'];
                switch ( $row['HealthKind_id'] )
                {
                    case 1:
                        $evn_vizit_pl_dt14_data[$key][5] = '+';
                        break;
                    case 2:
                        $evn_vizit_pl_dt14_data[$key][6] = '+';
                        break;
                    case 3:
                        $evn_vizit_pl_dt14_data[$key][7] = '+';
                        if ( $row['DopDispDiagType_id'] == 2 )
                            $evn_vizit_pl_dt14_data[$key][8] = '+';
                        break;
                    case 4:
                        $evn_vizit_pl_dt14_data[$key][9] = '+';
                        break;
                    case 5:
                        $evn_vizit_pl_dt14_data[$key][10] = '+';
                        break;
                }
                if ( $row['EvnVizitDispTeen14_IsSanKur'] == 2 )
                    $evn_vizit_pl_dt14_data[$key][11] = '+';
            }
        }

        $evn_usluga_pl_dt14_data = array('1' => array(), '2' => array(), '3' => array(), '4' => array(), '5' => array(), '6' => array(), '7' => array(), '8' => array(), '9' => array(), '10' => array(), '11' => array(), '12' => array(), '13' => array(), '14' => array(), '15' => array(), '16' => array(), '17' => array(), '18' => array(), '19' => array());
        foreach ( $evn_usluga_pl_dt14_data as $key => $val)
        {
            $evn_usluga_pl_dt14_data[$key] = array('', '');
        }
        $response_temp = $this->dbmodel->loadEvnUslugaDispTeen14Data($data);
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
                $evn_usluga_pl_dt14_data[$key][0] = !empty($row['EvnUslugaDispTeen14_setDate'])?$row['EvnUslugaDispTeen14_setDate']:'';
                $evn_usluga_pl_dt14_data[$key][1] = !empty($row['EvnUslugaDispTeen14_didDate'])?$row['EvnUslugaDispTeen14_didDate']:'';
            }
        }

        $template = 'evn_pl_disp_dop_template_list_a4';
        if ( $data['blank_only'] === true )
            $template = 'evn_pl_disp_dop_template_list_a4_empty';

        $print_data = $response[0];
        $print_data['evn_vizit_pl_dt14_data'] = $evn_vizit_pl_dt14_data;
        $print_data['evn_usluga_pl_dt14_data'] = $evn_usluga_pl_dt14_data;

        return $this->parser->parse($template, $print_data);
    }


    /**
     *  Печать паспорта здоровья
     *  Входящие данные: $_GET['EvnPLDispTeen14_id']
     *  На выходе: форма для печати паспорта здоровья
     *  Используется: форма редактирования/просмотра/поиска/поточного ввода талонов ДД
     */
    function printEvnPLDispTeen14Passport() {
        $this->load->helper('Options');
        $this->load->library('parser');

        $template = 'evn_pl_passport_template';
        $default_val = '&nbsp;';
        $yrdt = array();
        $years = array();
        $print_data = array();
        $data = array();

        $start_year = 2009;
        $data['start_year'] = $start_year;

        $data = $this->ProcessInputData('printEvnPLDispTeen14', true);
        if ($data === false) { return false; }

        // Получаем данные
        //года
        $now = getdate();
        for ($i = 1; $i <= 4; $i++) {
            $yr = $start_year+$i-1;
            $print_data["yr$i"] = $yr <= $now['year'] ? $yr : '&nbsp;';
            $print_data["year$i"] = $yr <= $now['year'] ? $yr : '20_____г.';
            $yrdt[$yr] = ($yr <= $now['year']);
            $years[] = $yr;
        }

        //данные по талону
        $passport_data = $this->dbmodel->getEvnPLDispTeen14PassportFields($data);

        $print_data['person_surname'] = $passport_data['Person_SurName'];
        $print_data['person_firname'] = $passport_data['Person_FirName'];
        $print_data['person_secname'] = $passport_data['Person_SecName'];
        $print_data['p_bd_d'] = $passport_data['Person_BirthDay_Day'];
        $print_data['p_bd_m'] = $passport_data['Person_BirthDay_Month'];
        $print_data['p_bd_y'] = $passport_data['Person_BirthDay_Year'];
        $print_data['p_a'] = $passport_data['Address_Info'];
        $print_data['p_a_st'] = $passport_data['KLStreet_Name'];
        $print_data['p_a_h'] = $passport_data['Address_House'];
        $print_data['p_a_c'] = $passport_data['Address_Corpus'];
        $print_data['p_a_fl'] = $passport_data['Address_Flat'];
        $print_data['dd_lpu'] = $passport_data['Lpu_Name'];
        $print_data['dd_lpu_phone'] = $passport_data['Org_Phone'];

        //данные по показателям связанным с услугами
        $usluga_rate_code = array(
            'm1' => 'cln_blood_gem',
            'm2' => 'cln_blood_leyck',
            'm3' => 'cln_blood_trom',
            'm4' => 'cln_blood_soe',
            'm5' => 'bio_blood_sugar',
            'm6' => 'bio_blood_bili',
            'm7' => 'bio_blood_common_protein',
            'm8' => 'bio_blood_holesterin',
            'm9' => 'bio_blood_amilaza',
            'm10' => 'bio_blood_kreatinin',
            'm11' => 'bio_blood_lipoproteid',
            'm12' => 'bio_blood_triglycerid',
            'm13' => 'bio_blood_acid',
            'm14' => 'cln_urine_protein',
            'm15' => 'cln_urine_sugar',
            'm16' => 'cln_urine_leyck',
            'm17' => 'cln_urine_erit',
            'm18' => 'onck_marker_ca125',
            'm19' => 'onck_marker_psa',
            'm20' => 'cito_cercval_channel',
            'm21' => 'electro_cardio_graph',
            'm22' => 'flyro_graph',
            'm23' => 'mammo_graph'
        );

        $data_exist = (count($passport_data['usluga_rate']) > 0);
        foreach($usluga_rate_code as $code => $nick) {
            if ($data_exist && isset($passport_data['usluga_rate'][$nick])) {
                $rate_data = $passport_data['usluga_rate'][$nick];
                for ($i = 0; $i < 4; $i++) {
                    $print_data[$code.'d'.($i+1)] = isset($rate_data[$i]) ? $rate_data[$i]['date'] : '&nbsp;';
                    $print_data[$code.'v'.($i+1)] = isset($rate_data[$i]) ? $rate_data[$i]['value'] : '&nbsp;';
                }
            } else {
                for ($i = 0; $i < 4; $i++) {
                    $print_data[$code.'d'.($i+1)] = '&nbsp;';
                    $print_data[$code.'v'.($i+1)] = '&nbsp;';
                }
            }
        }


        //данные по показателя связанным с человеком
        $print_data['p_blood'] = isset($passport_data['person_rate']['sign_blood_group']) ? $passport_data['person_rate']['sign_blood_group']['last_value'] : '&nbsp;';
        $print_data['p_blood_rh'] = isset($passport_data['person_rate']['sign_blood_rh']) ? $passport_data['person_rate']['sign_blood_rh']['last_value'] : '&nbsp;';
        $print_data['p_dead_drug'] = isset($passport_data['person_rate']['sign_danger_drug']) ? $passport_data['person_rate']['sign_danger_drug']['last_value'] : '&nbsp;';
        $print_data['p_allerg'] = isset($passport_data['person_rate']['sign_allergy']) ? $passport_data['person_rate']['sign_allergy']['last_value'] : '&nbsp;';

        ////ИМТ
        if (isset($passport_data['person_rate']['person_height']) && isset($passport_data['person_rate']['person_weight'])) {
            $h = $passport_data['person_rate']['person_height']['last_value'];
            $w = $passport_data['person_rate']['person_weight']['last_value'];
            if ($h > 0 && $w > 0)
                $print_data['imt'] = round($w/($h*$h),2);
            else
                $print_data['imt'] = '&nbsp;';
        } else
            $print_data['imt'] = '&nbsp;';

        $person_rate_code = array(
            'inf1' => 'person_height',
            'inf2' => 'person_weight',
            'inf3' => 'person_heart_beating',
            'inf4' => 'person_arterial_pressure',
            'inf5' => 'risk_heredity',
            'inf6' => 'risk_smoking',
            'inf7' => 'risk_overweight',
            'inf8' => 'risk_gippodinamy',
            'inf9' => 'risk_stress',
            'inf10' => 'risk_high_pressure',
            'inf11' => 'risk_wrong_feeding'
        );

        $data_exist = (count($passport_data['person_rate']) > 0);
        foreach($person_rate_code as $code => $nick) {
            if ($data_exist && isset($passport_data['person_rate'][$nick])) {
                $rate_data = $passport_data['person_rate'][$nick];
                for ($i = 0; $i < 4; $i++) {
                    $y = $years[$i];
                    $print_data[$code.'v'.($i+1)] = isset($rate_data[$y]) && $yrdt[$y] ? $rate_data[$y] : '&nbsp;';
                }
            } else {
                for ($i = 0; $i < 4; $i++) {
                    $print_data[$code.'v'.($i+1)] = '&nbsp;';
                }
            }
        }


        //группы здоровья
        $dt14years = array(0,0,0,0);
        $cnt = 0;
		if ( is_array($passport_data['health_groups']) ) {
			foreach($passport_data['health_groups'] as $y=>$v) {
				if (in_array($y, $years))
					$dt14years[$cnt++] = $y;
			}
        }

        for ($i = 0; $i < 4; $i++) {
            $y = $dt14years[$i];
            $print_data['ddyr'.($i+1)] = $y > 0 ? $y : '&nbsp;';
            $print_data['dd1v'.($i+1)] = isset($passport_data['health_groups'][$y]) && $yrdt[$y] ? $passport_data['health_groups'][$y]['date'] : '&nbsp;';
            $print_data['dd2v'.($i+1)] = isset($passport_data['health_groups'][$y]) && $yrdt[$y] ? $passport_data['health_groups'][$y]['value'] : '&nbsp;';
        }

        //рекоомендации специалистов
        $spec_code = array(
            'spec1' => '2',//акушер-гинеколог
            'spec2' => '3',//невролог
            'spec3' => '6',//офтальмолог
            'spec4' => '5',//хирург
            'spec5' => '1' //терапевт
        );
        $data_exist = (count($passport_data['recommendations']) > 0);
        foreach($spec_code as $code => $spec) {
            if ($data_exist && isset($passport_data['recommendations'][$spec])) {
                $rec_data = $passport_data['recommendations'][$spec];
                for ($i = 0; $i < 4; $i++) {
                    $y = $years[$i];
                    $print_data['recommendations_'.($i+1).'_'.$code] = isset($rec_data[$y]) && $yrdt[$y] ? $rec_data[$y] : '&nbsp;';
                }
            } else {
                for ($i = 0; $i < 4; $i++) {
                    $print_data['recommendations_'.($i+1).'_'.$code] = '&nbsp;';
                }
            }
        }

        //заболевания
        $data_exist = (count($passport_data['diseases']) > 0);
        for ($i = 0; $i < 4; $i++) {
            $y = $years[$i];
            if ($data_exist && isset($passport_data['diseases'][$y])) {
                $row_cnt = 0;
                foreach($passport_data['diseases'][$y] as $row) {
                    if ($row_cnt < 5) {
                        $print_data['ds'.($i+1).'_date'.($row_cnt+1)] = $row['date'];
                        $print_data['ds'.($i+1).'_name'.($row_cnt+1)] = $row['name'];
                        $print_data['ds'.($i+1).'_cd'.($row_cnt+1)] = $row['code'];
                    }
                    $row_cnt++;
                }
                for ($j = $row_cnt; $j < 5; $j++) {
                    $print_data['ds'.($i+1).'_date'.($j+1)] = '&nbsp;';
                    $print_data['ds'.($i+1).'_name'.($j+1)] = '&nbsp;';
                    $print_data['ds'.($i+1).'_cd'.($j+1)] = '&nbsp;';
                }
            } else {
                for ($j = 0; $j < 5; $j++) {
                    $print_data['ds'.($i+1).'_date'.($j+1)] = '&nbsp;';
                    $print_data['ds'.($i+1).'_name'.($j+1)] = '&nbsp;';
                    $print_data['ds'.($i+1).'_cd'.($j+1)] = '&nbsp;';
                }
            }
        }

        //print('<pre>'); print_r($passport_data['diseases']); print('</pre>'); die;

        return $this->parser->parse($template, $print_data);
    }

    /**
     * Удаление талона диспансеризации 14летних
     */
    function deleteEvnPLDispTeen14() {
        $data = $this->ProcessInputData('deleteEvnPLDispTeen14', true);
        if ($data === false) { return false; }

        $response = $this->dbmodel->deleteEvnPLDispTeen14($data);
        $this->ProcessModelSave($response,true,'При удалении талона ДД возникли ошибки')->ReturnData();

        return true;
    }


    /**
     * Проверка на наличие талона на этого человека в этом году
     * Входящие данные: $_POST['Person_id']
     * На выходе: JSON-строка
     * Используется: форма редактирования талона по ДД
     */
    function checkIfEvnPLDispTeen14Exists()
    {
        $data = $this->ProcessInputData('checkIfEvnPLDispTeen14Exists', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->checkIfEvnPLDispTeen14Exists($data);
		$this->ProcessModelSave($response, true)->ReturnData();
    }

    /**
     * Получение данных для формы редактирования талона по ДД
     * Входящие данные: $_POST['EvnPLDispTeen14_id']
     * На выходе: JSON-строка
     * Используется: форма редактирования талона по ДД
     */
    function loadEvnPLDispTeen14EditForm()
    {
        $data = $this->ProcessInputData('loadEvnPLDispTeen14EditForm', true);
        if ($data) {
            // признаки
            // невозможность взятия мазка
            // невозможность проведения маммографии

            $response = $this->dbmodel->loadEvnPLDispTeen14EditForm($data);
            $this->ProcessModelList($response,true,true)->ReturnData();
        }
    }


    /**
     * Получение списка талонов по ДД для потокового ввода
     * Входящие данные: $_POST['begDate'],
     *                  $_POST['begTime']
     * На выходе: JSON-строка
     * Используется: форма потокового ввода талонов по ДД
     */
    function loadEvnPLDispTeen14StreamList()
    {
        $data = $this->ProcessInputData('loadEvnPLDispTeen14StreamList', true);
        if ($data === false) { return false; }

        $response = $this->dbmodel->loadEvnPLDispTeen14StreamList($data);
        $this->ProcessModelList($response,true,true)->ReturnData();

    }

    /**
     * Получение данных формы редактирования посещений в талоне по ДД
     * Входящие данные: $_POST['EvnVizitDispTeen14_id']
     * На выходе: JSON-строка
     * Используется: форма редактирования посещения по ДД
     */
    function loadEvnVizitDispTeen14EditForm()
    {
        $data = $this->ProcessInputData('loadEvnVizitDispTeen14EditForm', true, true);
        if ($data) {
            if ($data['Lpu_id'] == 0)
            {
                $this->ReturnData(array('success' => false));
                return true;
            }
            $response = $this->dbmodel->loadEvnVizitDispTeen14EditForm($data);
            $this->ProcessModelList($response)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Получение списка посещений в талоне по ДД
     * Входящие данные: $_POST['EvnPLDispTeen14_id']
     * На выходе: JSON-строка
     * Используется: форма редактирования талона по ДД
     */
    function loadEvnVizitDispTeen14Grid()
    {
        $data = $this->ProcessInputData('loadEvnVizitDispTeen14Grid', true, true);
        if ($data) {
            if ($data['Lpu_id'] == 0)
            {
                $this->ReturnData(array('success' => false));
                return true;
            }
            $response = $this->dbmodel->loadEvnVizitDispTeen14Grid($data);
            $this->ProcessModelList($response, true, true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }


    /**
     * Получение списка исследований в талоне по ДД
     * Входящие данные: $_POST['EvnPLDispTeen14_id']
     * На выходе: JSON-строка
     * Используется: форма редактирования талона по ДД
     */
    function loadEvnUslugaDispTeen14Grid()
    {
        $data = $this->ProcessInputData('loadEvnUslugaDispTeen14Grid', true, true);
        if ($data) {
            if ($data['Lpu_id'] == 0)
            {
                $this->ReturnData(array('success' => false));
                return true;
            }
            $response = $this->dbmodel->loadEvnUslugaDispTeen14Grid($data);
            $this->ProcessModelList($response, true, true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Сохранение талона амбулаторного пациента
     * Входящие данные: ...
     * На выходе: JSON-строка
     * Используется: форма редактирования талона амбулаторного пациента
     */
    function saveEvnPLDispTeen14()
    {
        //$this->load->model('Rate_model', 'ratemodel');

        $val  = array();
        $data = $this->ProcessInputData('saveEvnPLDispTeen14', true);

		$checkDate = $this->dbmodel->checkPediatrVizitDate($data);
		if (!$checkDate)
		{
			echo json_return_errors('Дата осмотра педиатра не может быть меньше даты осмотра других специалистов!');
			return false;
		}

        if ($data) {

            // Осмотры специалиста
            if ((isset($data['EvnVizitDispTeen14'])) && (strlen(trim($data['EvnVizitDispTeen14'])) > 0) && (trim($data['EvnVizitDispTeen14']) != '[]'))
            {
                ConvertFromWin1251ToUTF8($data['EvnVizitDispTeen14']);// необходимо, так как json_decode не работает с Win1251
                $data['EvnVizitDispTeen14'] = json_decode(trim($data['EvnVizitDispTeen14']), true);

                if ( !(count($data['EvnVizitDispTeen14']) == 1 && $data['EvnVizitDispTeen14'][0]['EvnVizitDispTeen14_id'] == '') )
                {
                    for ($i = 0; $i < count($data['EvnVizitDispTeen14']); $i++) // обработка посещений в цикле
                    {
                        array_walk($data['EvnVizitDispTeen14'][$i], 'ConvertFromUTF8ToWin1251');

                        if ( is_numeric($data['EvnVizitDispTeen14'][$i]['EvnVizitDispTeen14_id']) ) {
                            if ((!isset($data['EvnVizitDispTeen14'][$i]['EvnVizitDispTeen14_setDate'])) || (strlen(trim($data['EvnVizitDispTeen14'][$i]['EvnVizitDispTeen14_setDate'])) == 0))
                            {
                                echo json_encode(array('success' => false, 'Error_Msg' => toUTF('Ошибка при сохранении осмотра (не задано поле "Дата осмотра")')));
                                return false;
                            }

                            $data['EvnVizitDispTeen14'][$i]['EvnVizitDispTeen14_setDate'] = ConvertDateFormat(trim($data['EvnVizitDispTeen14'][$i]['EvnVizitDispTeen14_setDate']));
                        }
                    }
                }
                else
                    $data['EvnVizitDispTeen14'] = array();
            } else {
				$data['EvnVizitDispTeen14'] = array();
			}

            // Лабораторные исследования
            if ((isset($data['EvnUslugaDispTeen14'])) && (strlen(trim($data['EvnUslugaDispTeen14'])) > 0) && (trim($data['EvnUslugaDispTeen14']) != '[]'))
            {
                ConvertFromWin1251ToUTF8($data['EvnUslugaDispTeen14']);// необходимо, так как json_decode не работает с Win1251
                $data['EvnUslugaDispTeen14'] = json_decode(trim($data['EvnUslugaDispTeen14']), true);

                if ( !(count($data['EvnUslugaDispTeen14']) == 1 && $data['EvnUslugaDispTeen14'][0]['EvnUslugaDispTeen14_id'] == '') )
                {
                    for ($i = 0; $i < count($data['EvnUslugaDispTeen14']); $i++) // обработка услуг в цикле
                    {
                        array_walk($data['EvnUslugaDispTeen14'][$i], 'ConvertFromUTF8ToWin1251');

                        if ((!isset($data['EvnUslugaDispTeen14'][$i]['EvnUslugaDispTeen14_setDate'])) || (strlen(trim($data['EvnUslugaDispTeen14'][$i]['EvnUslugaDispTeen14_setDate'])) == 0))
                        {
                            echo json_encode(array('success' => false, 'Error_Msg' => 'Ошибка при сохранении лабораторного исследования (не задано поле "Дата исследования")'));
                            return false;
                        }

                        $data['EvnUslugaDispTeen14'][$i]['EvnUslugaDispTeen14_setDate'] = ConvertDateFormat(trim($data['EvnUslugaDispTeen14'][$i]['EvnUslugaDispTeen14_setDate']));
                        $data['EvnUslugaDispTeen14'][$i]['EvnUslugaDispTeen14_didDate'] = ConvertDateFormat(trim($data['EvnUslugaDispTeen14'][$i]['EvnUslugaDispTeen14_didDate']));

                    }
                }
                else
                    $data['EvnUslugaDispTeen14'] = array();
            } else {
				$data['EvnUslugaDispTeen14'] = array();
			}

            $server_id = $data['Server_id'];

            $data['Server_id'] = $server_id;

            $response = $this->dbmodel->saveEvnPLDispTeen14($data);

            if (is_array($response) && count($response) > 0)
            {
                if ((isset($response[0]['Error_Msg'])) && (strlen($response[0]['Error_Msg']) == 0))
                {
                    $val['success'] = true;
                    // в ДД сохранялись какие-то показаели. Здесь убрал пока.
                    //if (isset($response[0]['usluga_array'])) $this->ratemodel->saveEvnUsluga($response[0]['usluga_array'], array('Server_id' => $data['Server_id'], 'pmUser_id' => $data['pmUser_id'])); // если в наличии соотв. данные -  сохраняем показатели для услуги
                }
                else
                {
                    $val = $response[0];
                    $val['success'] = false;
                    $val['Cancel_Error_Handle'] = true;
                    $val['Error_Code'] = 10;
                }
            }
            else
            {
                $val = array('success' => false, 'Error_Msg' => 'В какой-то момент времени что-то пошло не так [2]');
            }

            array_walk($val, 'ConvertFromWin1251ToUTF8');

            $this->ReturnData($val);
        }
    }

    /**
     * Получение числа талонов с распределением по годам, для формирования списка на клиенте
     * Входящие данные: ...
     * На выходе: JSON-строка
     * Используется: форма поиска/потокового ввода талонов по ДД
     */
    function getEvnPLDispTeen14Years()
    {
        $data = array();
        $data=array_merge($data, getSessionParams());
        $this->load->helper('Text');
        $year = intval(date('Y'));
        $maxYear = $year + (date('m') >= 10 ? 1 : 0);
        $info = $this->dbmodel->getEvnPLDispTeen14Years($data);
        if ( is_array($info) && count($info) > 0 ) {
            $val = array();
            $flag = false;
            foreach ($info as $row) {
                array_walk($row, 'ConvertFromWin1251ToUTF8');
                if ( $row['EvnPLDispTeen14_Year'] == $year )
                    $flag = true;
                $val[] = $row;
            }
            if (!$flag)
                $val[] = array('EvnPLDispTeen14_Year'=>$year, 'count'=>0);
			if ($maxYear != $year)
                $val[] = array('EvnPLDispTeen14_Year'=>$maxYear, 'count'=>0);
            $this->ReturnData($val);

        }
        else {
            $val = array();
            $val[] = array('EvnPLDispTeen14_Year'=>$year, 'count'=>0);
			if ($maxYear != $year)
                $val[] = array('EvnPLDispTeen14_Year'=>$maxYear, 'count'=>0);
            $this->ReturnData($val);
        }
    }
}
?>