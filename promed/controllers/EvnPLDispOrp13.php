<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* EvnPLDispOrp - контроллер для управления талонами диспансеризации детей-сирот
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Марков Андрей
* @version      май 2010
 * @property EvnPLDispOrp13_model $dbmodel
*/

class EvnPLDispOrp13 extends swController
{
    /**
     * Конструктор
     */
    function __construct()
	{
		parent::__construct();

		$this->load->database();
		$this->load->model("EvnPLDispOrp13_model", "dbmodel");
        $this->load->model("EvnDiagDopDisp_model", "edddmodel");
		$this->inputRules = $this->dbmodel->getInputRulesAdv();
	}
	
	/**
	*  Получение грида "информированное добровольное согласие по ДД 2013"
	*  Входящие данные: EvnPLDispOrp_id
	*/	
	function loadDopDispInfoConsent() {
		$data = $this->ProcessInputData('loadDopDispInfoConsent', true);
		if ($data === false) { return false; }
	
		$response = $this->dbmodel->loadDopDispInfoConsent($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	 * Сохранение данных по информир. добр. согласию
	 */
	function saveDopDispInfoConsent() {
		$data = $this->ProcessInputData('saveDopDispInfoConsent', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveDopDispInfoConsent($data);
		$this->ProcessModelSave($response, true, 'При сохранении возникли ошибки')->ReturnData();

		return true;
	}
	
	/**
	 * Печать талона ДД
	 * Входящие данные: $_GET['EvnPLDispOrp_id']
	 * На выходе: форма для печати талона ДД
	 * Используется: форма редактирования талона ДД
	 */
	function printEvnPLDispOrp() {
		$this->load->helper('Options');
		$this->load->library('parser');

		// Получаем сессионные переменные
		$data = getSessionParams();
		$data['EvnPLDispOrp_id'] = NULL;

		if ( (isset($_GET['EvnPLDispOrp_id'])) && (is_numeric($_GET['EvnPLDispOrp_id'])) && ($_GET['EvnPLDispOrp_id'] > 0) ) {
			$data['EvnPLDispOrp_id'] = $_GET['EvnPLDispOrp_id'];
		}

		if ( !isset($data['EvnPLDispOrp_id']) ) {
			echo 'Неверный параметр: EvnPLDispOrp_id';
			return true;
		}

		// Получаем данные по талону ДД
		$response = $this->dbmodel->getEvnPLDispOrpFields($data);

		if ( !is_array($response) || count($response) == 0 ) {
			echo 'Ошибка при получении данных по талону ДД';
			return true;
		}

		$template = 'evn_pl_disp_orp_template_list_a4';

		$print_data = $response[0];
		$print_data['EvnPLTemplateTitle'] = 'Печать карты диспансеризации';

        $datediff = $this->real_date_diff($print_data['EvnPLDispOrp_setDate2'],$print_data['Person_BirthDay2']);
        $print_data['year_diff'] = $datediff[0];
        $print_data['month_diff'] = $datediff[1];
        $print_data['days_diff'] = $datediff[2];

        $print_data['weight_condition'] = "дефицит массы тела, избыток массы тела";
        $print_data['height_condition'] = "низкий рост, высокий рост";
        if(($print_data['WeightAbnormType_id']==0)&&($print_data['HeightAbnormType_id']==0)){
            $print_data['condition'] = "<u>нормальное</u>, с отклонениями";
        }
        else{
            $print_data['condition'] = "нормальное, <u>с отклонениями</u>";

            if($print_data['WeightAbnormType_id']!=0){
                if($print_data['WeightAbnormType_id']==1)
                    $print_data['weight_condition'] = "<u>дефицит массы тела</u>, избыток массы тела";
                else
                    $print_data['weight_condition'] = "дефицит массы тела, <u>избыток массы тела</u>";
            }

            if($print_data['HeightAbnormType_id']!=0){
                if($print_data['HeightAbnormType_id']==1)
                    $print_data['height_condition'] = " <u>низкий рост</u>, высокий рост";
                else
                    $print_data['height_condition'] = "низкий рост, <u>высокий рост</u>";
            }
        }

        if($print_data['year_diff'] > 4){
            $print_data['AssesmentHealth_Weight_0'] = '____';
            $print_data['AssesmentHealth_Height_0'] = '____';
            $print_data['AssessmentHealth_Head_0']   = '____';
            $print_data['condition_0'] = "нормальное, с отклонениями";
            $print_data['weight_condition_0'] = "дефицит массы тела, избыток массы тела";
            $print_data['height_condition_0'] = "низкий рост, высокий рост";
            $print_data['AssesmentHealth_Weight_1'] = $print_data['AssesmentHealth_Weight'];
            $print_data['AssesmentHealth_Height_1'] = $print_data['AssesmentHealth_Height'];
            $print_data['AssessmentHealth_Head_1']   = $print_data['AssessmentHealth_Head'];
            $print_data['condition_1'] = $print_data['condition'];
            $print_data['weight_condition_1'] = $print_data['weight_condition'];
            $print_data['height_condition_1'] = $print_data['height_condition'];
            $print_data['AssessmentHealth_Gnostic'] = '____';
            $print_data['AssessmentHealth_Motion'] = '____';
            $print_data['AssessmentHealth_Social'] = '____';
            $print_data['AssessmentHealth_Speech'] = '____';
        }
        else{
            $print_data['AssesmentHealth_Weight_1'] = '____';
            $print_data['AssesmentHealth_Height_1'] = '____';
            $print_data['AssessmentHealth_Head_1']   = '____';
            $print_data['condition_1'] = "нормальное, с отклонениями";
            $print_data['weight_condition_1'] = "дефицит массы тела, избыток массы тела";
            $print_data['height_condition_1'] = "низкий рост, высокий рост";
            $print_data['AssesmentHealth_Weight_0'] = $print_data['AssesmentHealth_Weight'];
            $print_data['AssesmentHealth_Height_0'] = $print_data['AssesmentHealth_Height'];
            $print_data['AssessmentHealth_Head_0']   = $print_data['AssessmentHealth_Head'];
            $print_data['condition_0'] = $print_data['condition'];
            $print_data['weight_condition_0'] = $print_data['weight_condition'];
            $print_data['height_condition_0'] = $print_data['height_condition'];
            $print_data['NormaDisturbanceType_id'] = 0;
            $print_data['NormaDisturbanceType_uid'] = 0;
            $print_data['NormaDisturbanceType_eid'] = 0;
        }

        $print_data['AssessmentHealth_P_g'] = '__';
        $print_data['AssessmentHealth_Ax_g'] = '__';
        $print_data['AssessmentHealth_P_b'] = '__';
        $print_data['AssessmentHealth_Ax_b'] = '__';
        $print_data['AssessmentHealth_Fa_b'] = '__';
        $print_data['AssessmentHealth_Ma_g'] = '__';
        $print_data['AssessmentHealth_Me_g'] = '__';
        $print_data['AssessmentHealth_Years_g'] = '';
        $print_data['AssessmentHealth_Month_g'] = '';
        $print_data['IsRegular'] = 'регулярные';
        $print_data['IsIrregular'] = 'нерегулярные';
        $print_data['IsAbundant'] = 'обильные';
        $print_data['IsModerate'] = 'умеренные';
        $print_data['IsScanty'] = 'скудные';
        $print_data['IsPainful'] = 'болезненные';
        $print_data['IsPainless'] = 'безболезненные';

        if($print_data['year_diff'] > 10){
            if($print_data['Sex_id'] == 1){
                $print_data['AssessmentHealth_P_g'] = '__';
                $print_data['AssessmentHealth_Ax_g'] = '__';
                $print_data['AssessmentHealth_P_b'] = $print_data['AssessmentHealth_P'];
                $print_data['AssessmentHealth_Ax_b'] = $print_data['AssessmentHealth_Ax'];
                $print_data['AssessmentHealth_Fa_b'] = $print_data['AssessmentHealth_Fa'];
            }
            else{
                $print_data['AssessmentHealth_P_b'] = '__';
                $print_data['AssessmentHealth_Ax_b'] = '__';
                $print_data['AssessmentHealth_Fa_b'] = '__';
                $print_data['AssessmentHealth_P_g'] = $print_data['AssessmentHealth_P'];
                $print_data['AssessmentHealth_Ax_g'] = $print_data['AssessmentHealth_Ax'];
                $print_data['AssessmentHealth_Me_g'] = $print_data['AssessmentHealth_Me'];
                $print_data['AssessmentHealth_Ma_g'] = $print_data['AssessmentHealth_Ma'];
                $print_data['AssessmentHealth_Years_g'] = $print_data['AssessmentHealth_Years'];
                $print_data['AssessmentHealth_Month_g'] = $print_data['AssessmentHealth_Month'];
                if($print_data['AssessmentHealth_IsRegular'] == '2')
                    $print_data['IsRegular'] = '<u>регулярные</u>';
                if($print_data['AssessmentHealth_IsIrregular'] == '2')
                    $print_data['IsIrregular'] = '<u>нерегулярные</u>';
                if($print_data['AssessmentHealth_IsAbundant'] == '2')
                    $print_data['IsAbundant'] = '<u>обильные</u>';
                if($print_data['AssessmentHealth_IsModerate'] == '2')
                    $print_data['IsModerate'] = '<u>умеренные</u>';
                if($print_data['AssessmentHealth_IsScanty'] == '2')
                    $print_data['IsScanty'] = '<u>скудные</u>';
                if($print_data['AssessmentHealth_IsPainful'] == '2')
                    $print_data['IsPainful'] = '<u>болезненные</u>';
                if($print_data['AssessmentHealth_IsPainless'] == '2')
                    $print_data['IsPainless'] = '<u>безболезненные</u>';
            }
        }
        $data['EvnPLDisp_id'] = $data['EvnPLDispOrp_id'];
        $recomendation_resp = $this->edddmodel->loadEvnDiagDopDispAndRecomendationGrid($data);
        $recomendation_after_resp = $this->dbmodel->loadEvnDiagAndRecomendation($data);


        for ($i=0;$i<5;$i++){
            $print_data['diag_'.$i.'_before'] = '';
            $print_data['DispSurveilType_id_1_'.$i.'_before'] = 'установлено  ранее';
            $print_data['DispSurveilType_id_2_'.$i.'_before'] = 'установлено впервые';
            $print_data['DispSurveilType_id_3_'.$i.'_before'] = 'не установлено';

            $print_data['ConditMedCareType2_nid_1_'.$i.'_before'] = 'нет';
            $print_data['ConditMedCareType2_nid_2_'.$i.'_before'] = 'в   амбулаторных   условиях';
            $print_data['ConditMedCareType2_nid_3_'.$i.'_before'] = 'в  условиях  дневного  стационара';
            $print_data['ConditMedCareType2_nid_4_'.$i.'_before'] = 'в стационарных  условиях';
            $print_data['PlaceMedCareType2_nid_1_'.$i.'_before'] = 'в муниципальных медицинских организациях';
            $print_data['PlaceMedCareType2_nid_2_'.$i.'_before'] = 'в   государственных   медицинских   организациях   субъекта Российской  Федерации';
            $print_data['PlaceMedCareType2_nid_3_'.$i.'_before'] = 'в  федеральных  медицинских  организациях';
            $print_data['PlaceMedCareType2_nid_4_'.$i.'_before'] = 'частных медицинских организациях';

            $print_data['ConditMedCareType2_id_2_'.$i.'_before'] = $print_data['ConditMedCareType2_nid_2_'.$i.'_before'];
            $print_data['ConditMedCareType2_id_3_'.$i.'_before'] = $print_data['ConditMedCareType2_nid_3_'.$i.'_before'];
            $print_data['ConditMedCareType2_id_4_'.$i.'_before'] = $print_data['ConditMedCareType2_nid_4_'.$i.'_before'];
            $print_data['PlaceMedCareType2_id_1_'.$i.'_before'] = $print_data['PlaceMedCareType2_nid_1_'.$i.'_before'];
            $print_data['PlaceMedCareType2_id_2_'.$i.'_before'] = $print_data['PlaceMedCareType2_nid_2_'.$i.'_before'];
            $print_data['PlaceMedCareType2_id_3_'.$i.'_before'] = $print_data['PlaceMedCareType2_nid_3_'.$i.'_before'];
            $print_data['PlaceMedCareType2_id_4_'.$i.'_before'] = $print_data['PlaceMedCareType2_nid_4_'.$i.'_before'];

            $print_data['ConditMedCareType3_nid_1_'.$i.'_before'] = 'нет';
            $print_data['ConditMedCareType3_nid_2_'.$i.'_before'] = $print_data['ConditMedCareType2_nid_2_'.$i.'_before'];
            $print_data['ConditMedCareType3_nid_3_'.$i.'_before'] = $print_data['ConditMedCareType2_nid_3_'.$i.'_before'];
            $print_data['ConditMedCareType3_nid_4_'.$i.'_before'] = $print_data['ConditMedCareType2_nid_4_'.$i.'_before'];
            $print_data['PlaceMedCareType3_nid_1_'.$i.'_before'] = $print_data['PlaceMedCareType2_nid_1_'.$i.'_before'];
            $print_data['PlaceMedCareType3_nid_2_'.$i.'_before'] = $print_data['PlaceMedCareType2_nid_2_'.$i.'_before'];
            $print_data['PlaceMedCareType3_nid_3_'.$i.'_before'] = $print_data['PlaceMedCareType2_nid_3_'.$i.'_before'];
            $print_data['PlaceMedCareType3_nid_4_'.$i.'_before'] = $print_data['PlaceMedCareType2_nid_4_'.$i.'_before'];
            $print_data['PlaceMedCareType3_nid_5_'.$i.'_before'] = 'санаторно-курортных организациях ';

            $print_data['ConditMedCareType3_id_2_'.$i.'_before'] = $print_data['ConditMedCareType2_nid_2_'.$i.'_before'];
            $print_data['ConditMedCareType3_id_3_'.$i.'_before'] = $print_data['ConditMedCareType2_nid_3_'.$i.'_before'];
            $print_data['ConditMedCareType3_id_4_'.$i.'_before'] = $print_data['ConditMedCareType2_nid_4_'.$i.'_before'];
            $print_data['PlaceMedCareType3_id_1_'.$i.'_before'] = $print_data['PlaceMedCareType2_nid_1_'.$i.'_before'];
            $print_data['PlaceMedCareType3_id_2_'.$i.'_before'] = $print_data['PlaceMedCareType2_nid_2_'.$i.'_before'];
            $print_data['PlaceMedCareType3_id_3_'.$i.'_before'] = $print_data['PlaceMedCareType2_nid_3_'.$i.'_before'];
            $print_data['PlaceMedCareType3_id_4_'.$i.'_before'] = $print_data['PlaceMedCareType2_nid_4_'.$i.'_before'];
            $print_data['PlaceMedCareType3_id_5_'.$i.'_before'] = $print_data['PlaceMedCareType3_nid_5_'.$i.'_before'];

            $print_data['EvnDiagDopDisp_IsVMP_2'.$i.'_before'] = 'да';
            $print_data['EvnDiagDopDisp_IsVMP_1'.$i.'_before'] = 'нет';


            $print_data['diag_'.$i.'_after'] = '';
            $print_data['DispSurveilType_id_1_'.$i.'_after'] = 'установлено  ранее';
            $print_data['DispSurveilType_id_2_'.$i.'_after'] = 'установлено впервые';
            $print_data['DispSurveilType_id_3_'.$i.'_after'] = 'не установлено';

            $print_data['ConditMedCareType2_nid_1_'.$i.'_after'] = 'нет';
            $print_data['ConditMedCareType2_nid_2_'.$i.'_after'] = 'в   амбулаторных   условиях';
            $print_data['ConditMedCareType2_nid_3_'.$i.'_after'] = 'в  условиях  дневного  стационара';
            $print_data['ConditMedCareType2_nid_4_'.$i.'_after'] = 'в стационарных  условиях';
            $print_data['PlaceMedCareType2_nid_1_'.$i.'_after'] = 'в муниципальных медицинских организациях';
            $print_data['PlaceMedCareType2_nid_2_'.$i.'_after'] = 'в   государственных   медицинских   организациях   субъекта Российской  Федерации';
            $print_data['PlaceMedCareType2_nid_3_'.$i.'_after'] = 'в  федеральных  медицинских  организациях';
            $print_data['PlaceMedCareType2_nid_4_'.$i.'_after'] = 'частных медицинских организациях';
            $print_data['PlaceMedCareType2_nid_5_'.$i.'_after'] = 'санаторно-курортных организациях ';

            $print_data['ConditMedCareType1_nid_1_'.$i.'_after'] = 'нет';
            $print_data['ConditMedCareType1_nid_2_'.$i.'_after'] = $print_data['ConditMedCareType2_nid_2_'.$i.'_after'];
            $print_data['ConditMedCareType1_nid_3_'.$i.'_after'] = $print_data['ConditMedCareType2_nid_3_'.$i.'_after'];
            $print_data['ConditMedCareType1_nid_4_'.$i.'_after'] = $print_data['ConditMedCareType2_nid_4_'.$i.'_after'];
            $print_data['PlaceMedCareType1_nid_1_'.$i.'_after'] = $print_data['PlaceMedCareType2_nid_1_'.$i.'_after'];
            $print_data['PlaceMedCareType1_nid_2_'.$i.'_after'] = $print_data['PlaceMedCareType2_nid_2_'.$i.'_after'];
            $print_data['PlaceMedCareType1_nid_3_'.$i.'_after'] = $print_data['PlaceMedCareType2_nid_3_'.$i.'_after'];
            $print_data['PlaceMedCareType1_nid_4_'.$i.'_after'] = $print_data['PlaceMedCareType2_nid_4_'.$i.'_after'];
            $print_data['PlaceMedCareType1_nid_5_'.$i.'_after'] = 'санаторно-курортных организациях ';

            $print_data['ConditMedCareType1_id_1_'.$i.'_after'] = 'нет';
            $print_data['ConditMedCareType1_id_2_'.$i.'_after'] = $print_data['ConditMedCareType2_nid_2_'.$i.'_after'];
            $print_data['ConditMedCareType1_id_3_'.$i.'_after'] = $print_data['ConditMedCareType2_nid_3_'.$i.'_after'];
            $print_data['ConditMedCareType1_id_4_'.$i.'_after'] = $print_data['ConditMedCareType2_nid_4_'.$i.'_after'];
            $print_data['PlaceMedCareType1_id_1_'.$i.'_after'] = $print_data['PlaceMedCareType2_nid_1_'.$i.'_after'];
            $print_data['PlaceMedCareType1_id_2_'.$i.'_after'] = $print_data['PlaceMedCareType2_nid_2_'.$i.'_after'];
            $print_data['PlaceMedCareType1_id_3_'.$i.'_after'] = $print_data['PlaceMedCareType2_nid_3_'.$i.'_after'];
            $print_data['PlaceMedCareType1_id_4_'.$i.'_after'] = $print_data['PlaceMedCareType2_nid_4_'.$i.'_after'];
            $print_data['PlaceMedCareType1_id_5_'.$i.'_after'] = 'санаторно-курортных организациях ';

            $print_data['ConditMedCareType2_id_1_'.$i.'_after'] = $print_data['ConditMedCareType2_nid_1_'.$i.'_after'];
            $print_data['ConditMedCareType2_id_2_'.$i.'_after'] = $print_data['ConditMedCareType2_nid_2_'.$i.'_after'];
            $print_data['ConditMedCareType2_id_3_'.$i.'_after'] = $print_data['ConditMedCareType2_nid_3_'.$i.'_after'];
            $print_data['ConditMedCareType2_id_4_'.$i.'_after'] = $print_data['ConditMedCareType2_nid_4_'.$i.'_after'];
            $print_data['PlaceMedCareType2_id_1_'.$i.'_after'] = $print_data['PlaceMedCareType2_nid_1_'.$i.'_after'];
            $print_data['PlaceMedCareType2_id_2_'.$i.'_after'] = $print_data['PlaceMedCareType2_nid_2_'.$i.'_after'];
            $print_data['PlaceMedCareType2_id_3_'.$i.'_after'] = $print_data['PlaceMedCareType2_nid_3_'.$i.'_after'];
            $print_data['PlaceMedCareType2_id_4_'.$i.'_after'] = $print_data['PlaceMedCareType2_nid_4_'.$i.'_after'];

            $print_data['ConditMedCareType3_nid_1_'.$i.'_after'] = 'нет';
            $print_data['ConditMedCareType3_nid_2_'.$i.'_after'] = $print_data['ConditMedCareType2_nid_2_'.$i.'_after'];
            $print_data['ConditMedCareType3_nid_3_'.$i.'_after'] = $print_data['ConditMedCareType2_nid_3_'.$i.'_after'];
            $print_data['ConditMedCareType3_nid_4_'.$i.'_after'] = $print_data['ConditMedCareType2_nid_4_'.$i.'_after'];
            $print_data['PlaceMedCareType3_nid_1_'.$i.'_after'] = $print_data['PlaceMedCareType2_nid_1_'.$i.'_after'];
            $print_data['PlaceMedCareType3_nid_2_'.$i.'_after'] = $print_data['PlaceMedCareType2_nid_2_'.$i.'_after'];
            $print_data['PlaceMedCareType3_nid_3_'.$i.'_after'] = $print_data['PlaceMedCareType2_nid_3_'.$i.'_after'];
            $print_data['PlaceMedCareType3_nid_4_'.$i.'_after'] = $print_data['PlaceMedCareType2_nid_4_'.$i.'_after'];
            $print_data['PlaceMedCareType3_nid_5_'.$i.'_after'] = 'санаторно-курортных организациях ';

            $print_data['ConditMedCareType3_id_1_'.$i.'_after'] = $print_data['ConditMedCareType2_nid_1_'.$i.'_after'];
            $print_data['ConditMedCareType3_id_2_'.$i.'_after'] = $print_data['ConditMedCareType2_nid_2_'.$i.'_after'];
            $print_data['ConditMedCareType3_id_3_'.$i.'_after'] = $print_data['ConditMedCareType2_nid_3_'.$i.'_after'];
            $print_data['ConditMedCareType3_id_4_'.$i.'_after'] = $print_data['ConditMedCareType2_nid_4_'.$i.'_after'];
            $print_data['PlaceMedCareType3_id_1_'.$i.'_after'] = $print_data['PlaceMedCareType2_nid_1_'.$i.'_after'];
            $print_data['PlaceMedCareType3_id_2_'.$i.'_after'] = $print_data['PlaceMedCareType2_nid_2_'.$i.'_after'];
            $print_data['PlaceMedCareType3_id_3_'.$i.'_after'] = $print_data['PlaceMedCareType2_nid_3_'.$i.'_after'];
            $print_data['PlaceMedCareType3_id_4_'.$i.'_after'] = $print_data['PlaceMedCareType2_nid_4_'.$i.'_after'];
            $print_data['PlaceMedCareType3_id_5_'.$i.'_after'] = $print_data['PlaceMedCareType3_nid_5_'.$i.'_after'];

            $print_data['EvnVizitDisp_IsVMP_2_'.$i.'_after'] = 'да';
            $print_data['EvnVizitDisp_IsVMP_1_'.$i.'_after'] = 'нет';
            $print_data['EvnVizitDisp_IsFirstTime_1_'.$i.'_after'] = 'нет';
            $print_data['EvnVizitDisp_IsFirstTime_2_'.$i.'_after'] = 'да';


            if(isset($recomendation_resp[$i])){
                $print_data['diag_'.$i.'_before'] = $recomendation_resp[$i]['Diag_Code'];
                if($recomendation_resp[$i]['DispSurveilType_id']==1)
                    $print_data['DispSurveilType_id_1_'.$i.'_before'] = '<u>'.$print_data['DispSurveilType_id_1_'.$i.'_before'].'</u>';
                if($recomendation_resp[$i]['DispSurveilType_id']==2)
                    $print_data['DispSurveilType_id_2_'.$i.'_before'] = '<u>'.$print_data['DispSurveilType_id_2_'.$i.'_before'].'</u>';
                if($recomendation_resp[$i]['DispSurveilType_id']==3)
                    $print_data['DispSurveilType_id_3_'.$i.'_before'] = '<u>'.$print_data['DispSurveilType_id_3_'.$i.'_before'].'</u>';

                if($recomendation_resp[$i]['ConditMedCareType2_nid']==1)
                    $print_data['ConditMedCareType2_nid_1_'.$i.'_before'] = '<u>'.$print_data['ConditMedCareType2_nid_1_'.$i.'_before'].'</u>';
                if($recomendation_resp[$i]['ConditMedCareType2_nid']==2)
                    $print_data['ConditMedCareType2_nid_2_'.$i.'_before'] = '<u>'.$print_data['ConditMedCareType2_nid_2_'.$i.'_before'].'</u>';
                if($recomendation_resp[$i]['ConditMedCareType2_nid']==3)
                    $print_data['ConditMedCareType2_nid_3_'.$i.'_before'] = '<u>'.$print_data['ConditMedCareType2_nid_3_'.$i.'_before'].'</u>';
                if($recomendation_resp[$i]['ConditMedCareType2_nid']==4)
                    $print_data['ConditMedCareType2_nid_4_'.$i.'_before'] = '<u>'.$print_data['ConditMedCareType2_nid_4_'.$i.'_before'].'</u>';

                if($recomendation_resp[$i]['PlaceMedCareType2_nid']==1)
                    $print_data['PlaceMedCareType2_nid_1_'.$i.'_before'] = '<u>'.$print_data['PlaceMedCareType2_nid_1_'.$i.'_before'].'</u>';
                if($recomendation_resp[$i]['PlaceMedCareType2_nid']==2)
                    $print_data['PlaceMedCareType2_nid_2_'.$i.'_before'] = '<u>'.$print_data['PlaceMedCareType2_nid_2_'.$i.'_before'].'</u>';
                if($recomendation_resp[$i]['PlaceMedCareType2_nid']==3)
                    $print_data['PlaceMedCareType2_nid_3_'.$i.'_before'] = '<u>'.$print_data['PlaceMedCareType2_nid_3_'.$i.'_before'].'</u>';
                if($recomendation_resp[$i]['PlaceMedCareType2_nid']==4)
                    $print_data['PlaceMedCareType2_nid_4_'.$i.'_before'] = '<u>'.$print_data['PlaceMedCareType2_nid_4_'.$i.'_before'].'</u>';

                if($recomendation_resp[$i]['ConditMedCareType2_id']==2)
                    $print_data['ConditMedCareType2_id_2_'.$i.'_before'] = '<u>'.$print_data['ConditMedCareType2_id_2_'.$i.'_before'].'</u>';
                if($recomendation_resp[$i]['ConditMedCareType2_id']==3)
                    $print_data['ConditMedCareType2_id_3_'.$i.'_before'] = '<u>'.$print_data['ConditMedCareType2_id_3_'.$i.'_before'].'</u>';
                if($recomendation_resp[$i]['ConditMedCareType2_id']==4)
                    $print_data['ConditMedCareType2_id_4_'.$i.'_before'] = '<u>'.$print_data['ConditMedCareType2_id_4_'.$i.'_before'].'</u>';

                if($recomendation_resp[$i]['PlaceMedCareType2_id']==1)
                    $print_data['PlaceMedCareType2_id_1_'.$i.'_before'] = '<u>'.$print_data['PlaceMedCareType2_id_1_'.$i.'_before'].'</u>';
                if($recomendation_resp[$i]['PlaceMedCareType2_id']==2)
                    $print_data['PlaceMedCareType2_id_2_'.$i.'_before'] = '<u>'.$print_data['PlaceMedCareType2_id_2_'.$i.'_before'].'</u>';
                if($recomendation_resp[$i]['PlaceMedCareType2_id']==3)
                    $print_data['PlaceMedCareType2_id_3_'.$i.'_before'] = '<u>'.$print_data['PlaceMedCareType2_id_3_'.$i.'_before'].'</u>';
                if($recomendation_resp[$i]['PlaceMedCareType2_id']==4)
                    $print_data['PlaceMedCareType2_id_4_'.$i.'_before'] = '<u>'.$print_data['PlaceMedCareType2_id_4_'.$i.'_before'].'</u>';

                if($recomendation_resp[$i]['ConditMedCareType3_nid']==1)
                    $print_data['ConditMedCareType3_nid_1_'.$i.'_before'] = '<u>'.$print_data['ConditMedCareType3_nid_1_'.$i.'_before'].'</u>';
                if($recomendation_resp[$i]['ConditMedCareType3_nid']==2)
                    $print_data['ConditMedCareType3_nid_2_'.$i.'_before'] = '<u>'.$print_data['ConditMedCareType3_nid_2_'.$i.'_before'].'</u>';
                if($recomendation_resp[$i]['ConditMedCareType3_nid']==3)
                    $print_data['ConditMedCareType3_nid_3_'.$i.'_before'] = '<u>'.$print_data['ConditMedCareType3_nid_3_'.$i.'_before'].'</u>';
                if($recomendation_resp[$i]['ConditMedCareType3_nid']==4)
                    $print_data['ConditMedCareType3_nid_4_'.$i.'_before'] = '<u>'.$print_data['ConditMedCareType3_nid_4_'.$i.'_before'].'</u>';

                if($recomendation_resp[$i]['PlaceMedCareType3_nid']==1)
                    $print_data['PlaceMedCareType3_nid_1_'.$i.'_before'] = '<u>'.$print_data['PlaceMedCareType3_nid_1_'.$i.'_before'].'</u>';
                if($recomendation_resp[$i]['PlaceMedCareType3_nid']==2)
                    $print_data['PlaceMedCareType3_nid_2_'.$i.'_before'] = '<u>'.$print_data['PlaceMedCareType3_nid_2_'.$i.'_before'].'</u>';
                if($recomendation_resp[$i]['PlaceMedCareType3_nid']==3)
                    $print_data['PlaceMedCareType3_nid_3_'.$i.'_before'] = '<u>'.$print_data['PlaceMedCareType3_nid_3_'.$i.'_before'].'</u>';
                if($recomendation_resp[$i]['PlaceMedCareType3_nid']==4)
                    $print_data['PlaceMedCareType3_nid_4_'.$i.'_before'] = '<u>'.$print_data['PlaceMedCareType3_nid_4_'.$i.'_before'].'</u>';
                if($recomendation_resp[$i]['PlaceMedCareType3_nid']==5)
                    $print_data['PlaceMedCareType3_nid_5_'.$i.'_before'] = '<u>'.$print_data['PlaceMedCareType3_nid_5_'.$i.'_before'].'</u>';

                if($recomendation_resp[$i]['ConditMedCareType3_id']==2)
                    $print_data['ConditMedCareType3_id_2_'.$i.'_before'] = '<u>'.$print_data['ConditMedCareType3_id_2_'.$i.'_before'].'</u>';
                if($recomendation_resp[$i]['ConditMedCareType3_id']==3)
                    $print_data['ConditMedCareType3_id_3_'.$i.'_before'] = '<u>'.$print_data['ConditMedCareType3_id_3_'.$i.'_before'].'</u>';
                if($recomendation_resp[$i]['ConditMedCareType3_id']==4)
                    $print_data['ConditMedCareType3_id_4_'.$i.'_before'] = '<u>'.$print_data['ConditMedCareType3_id_4_'.$i.'_before'].'</u>';

                if($recomendation_resp[$i]['PlaceMedCareType3_id']==1)
                    $print_data['PlaceMedCareType3_id_1_'.$i.'_before'] = '<u>'.$print_data['PlaceMedCareType3_id_1_'.$i.'_before'].'</u>';
                if($recomendation_resp[$i]['PlaceMedCareType3_id']==2)
                    $print_data['PlaceMedCareType3_id_2_'.$i.'_before'] = '<u>'.$print_data['PlaceMedCareType3_id_2_'.$i.'_before'].'</u>';
                if($recomendation_resp[$i]['PlaceMedCareType3_id']==3)
                    $print_data['PlaceMedCareType3_id_3_'.$i.'_before'] = '<u>'.$print_data['PlaceMedCareType3_id_3_'.$i.'_before'].'</u>';
                if($recomendation_resp[$i]['PlaceMedCareType3_id']==4)
                    $print_data['PlaceMedCareType3_id_4_'.$i.'_before'] = '<u>'.$print_data['PlaceMedCareType3_id_4_'.$i.'_before'].'</u>';
                if($recomendation_resp[$i]['PlaceMedCareType3_id']==5)
                    $print_data['PlaceMedCareType3_id_5_'.$i.'_before'] = '<u>'.$print_data['PlaceMedCareType3_id_5_'.$i.'_before'].'</u>';

                if($recomendation_resp[$i]['EvnDiagDopDisp_IsVMP']==1)
                    $print_data['EvnDiagDopDisp_IsVMP_1'.$i.'_before'] = '<u>'.$print_data['EvnDiagDopDisp_IsVMP_1'.$i.'_before'].'</u>';
                if($recomendation_resp[$i]['EvnDiagDopDisp_IsVMP']==2)
                    $print_data['EvnDiagDopDisp_IsVMP_2'.$i.'_before'] = '<u>'.$print_data['EvnDiagDopDisp_IsVMP_2'.$i.'_before'].'</u>';

            }

            if(isset($recomendation_after_resp[$i])){

                $print_data['diag_'.$i.'_after'] = $recomendation_after_resp[$i]['Diag_Code'];
                if($recomendation_after_resp[$i]['DispSurveilType_id']==1)
                    $print_data['DispSurveilType_id_1_'.$i.'_after'] = '<u>'.$print_data['DispSurveilType_id_1_'.$i.'_after'].'</u>';
                if($recomendation_after_resp[$i]['DispSurveilType_id']==2)
                    $print_data['DispSurveilType_id_2_'.$i.'_after'] = '<u>'.$print_data['DispSurveilType_id_2_'.$i.'_after'].'</u>';
                if($recomendation_after_resp[$i]['DispSurveilType_id']==3)
                    $print_data['DispSurveilType_id_3_'.$i.'_after'] = '<u>'.$print_data['DispSurveilType_id_3_'.$i.'_after'].'</u>';

                if($recomendation_after_resp[$i]['ConditMedCareType1_nid']==1)
                    $print_data['ConditMedCareType1_nid_1_'.$i.'_after'] = '<u>'.$print_data['ConditMedCareType1_nid_1_'.$i.'_after'].'</u>';
                if($recomendation_after_resp[$i]['ConditMedCareType1_nid']==2)
                    $print_data['ConditMedCareType1_nid_2_'.$i.'_after'] = '<u>'.$print_data['ConditMedCareType1_nid_2_'.$i.'_after'].'</u>';
                if($recomendation_after_resp[$i]['ConditMedCareType1_nid']==3)
                    $print_data['ConditMedCareType1_nid_3_'.$i.'_after'] = '<u>'.$print_data['ConditMedCareType1_nid_3_'.$i.'_after'].'</u>';
                if($recomendation_after_resp[$i]['ConditMedCareType1_nid']==4)
                    $print_data['ConditMedCareType1_nid_4_'.$i.'_after'] = '<u>'.$print_data['ConditMedCareType1_nid_4_'.$i.'_after'].'</u>';

                if($recomendation_after_resp[$i]['ConditMedCareType2_nid']==1)
                    $print_data['ConditMedCareType2_nid_1_'.$i.'_after'] = '<u>'.$print_data['ConditMedCareType2_nid_1_'.$i.'_after'].'</u>';
                if($recomendation_after_resp[$i]['ConditMedCareType2_nid']==2)
                    $print_data['ConditMedCareType2_nid_2_'.$i.'_after'] = '<u>'.$print_data['ConditMedCareType2_nid_2_'.$i.'_after'].'</u>';
                if($recomendation_after_resp[$i]['ConditMedCareType2_nid']==3)
                    $print_data['ConditMedCareType2_nid_3_'.$i.'_after'] = '<u>'.$print_data['ConditMedCareType2_nid_3_'.$i.'_after'].'</u>';
                if($recomendation_after_resp[$i]['ConditMedCareType2_nid']==4)
                    $print_data['ConditMedCareType2_nid_4_'.$i.'_after'] = '<u>'.$print_data['ConditMedCareType2_nid_4_'.$i.'_after'].'</u>';

                if($recomendation_after_resp[$i]['PlaceMedCareType2_nid']==1)
                    $print_data['PlaceMedCareType2_nid_1_'.$i.'_after'] = '<u>'.$print_data['PlaceMedCareType2_nid_1_'.$i.'_after'].'</u>';
                if($recomendation_after_resp[$i]['PlaceMedCareType2_nid']==2)
                    $print_data['PlaceMedCareType2_nid_2_'.$i.'_after'] = '<u>'.$print_data['PlaceMedCareType2_nid_2_'.$i.'_after'].'</u>';
                if($recomendation_after_resp[$i]['PlaceMedCareType2_nid']==3)
                    $print_data['PlaceMedCareType2_nid_3_'.$i.'_after'] = '<u>'.$print_data['PlaceMedCareType2_nid_3_'.$i.'_after'].'</u>';
                if($recomendation_after_resp[$i]['PlaceMedCareType2_nid']==4)
                    $print_data['PlaceMedCareType2_nid_4_'.$i.'_after'] = '<u>'.$print_data['PlaceMedCareType2_nid_4_'.$i.'_after'].'</u>';

                if($recomendation_after_resp[$i]['ConditMedCareType2_id']==1)
                    $print_data['ConditMedCareType2_id_1_'.$i.'_after'] = '<u>'.$print_data['ConditMedCareType2_id_1_'.$i.'_after'].'</u>';
                if($recomendation_after_resp[$i]['ConditMedCareType2_id']==2)
                    $print_data['ConditMedCareType2_id_2_'.$i.'_after'] = '<u>'.$print_data['ConditMedCareType2_id_2_'.$i.'_after'].'</u>';
                if($recomendation_after_resp[$i]['ConditMedCareType2_id']==3)
                    $print_data['ConditMedCareType2_id_3_'.$i.'_after'] = '<u>'.$print_data['ConditMedCareType2_id_3_'.$i.'_after'].'</u>';
                if($recomendation_after_resp[$i]['ConditMedCareType2_id']==4)
                    $print_data['ConditMedCareType2_id_4_'.$i.'_after'] = '<u>'.$print_data['ConditMedCareType2_id_4_'.$i.'_after'].'</u>';

                if($recomendation_after_resp[$i]['PlaceMedCareType2_id']==1)
                    $print_data['PlaceMedCareType2_id_1_'.$i.'_after'] = '<u>'.$print_data['PlaceMedCareType2_id_1_'.$i.'_after'].'</u>';
                if($recomendation_after_resp[$i]['PlaceMedCareType2_id']==2)
                    $print_data['PlaceMedCareType2_id_2_'.$i.'_after'] = '<u>'.$print_data['PlaceMedCareType2_id_2_'.$i.'_after'].'</u>';
                if($recomendation_after_resp[$i]['PlaceMedCareType2_id']==3)
                    $print_data['PlaceMedCareType2_id_3_'.$i.'_after'] = '<u>'.$print_data['PlaceMedCareType2_id_3_'.$i.'_after'].'</u>';
                if($recomendation_after_resp[$i]['PlaceMedCareType2_id']==4)
                    $print_data['PlaceMedCareType2_id_4_'.$i.'_after'] = '<u>'.$print_data['PlaceMedCareType2_id_4_'.$i.'_after'].'</u>';

                if($recomendation_after_resp[$i]['ConditMedCareType3_nid']==1)
                    $print_data['ConditMedCareType3_nid_1_'.$i.'_after'] = '<u>'.$print_data['ConditMedCareType3_nid_1_'.$i.'_after'].'</u>';
                if($recomendation_after_resp[$i]['ConditMedCareType3_nid']==2)
                    $print_data['ConditMedCareType3_nid_2_'.$i.'_after'] = '<u>'.$print_data['ConditMedCareType3_nid_2_'.$i.'_after'].'</u>';
                if($recomendation_after_resp[$i]['ConditMedCareType3_nid']==3)
                    $print_data['ConditMedCareType3_nid_3_'.$i.'_after'] = '<u>'.$print_data['ConditMedCareType3_nid_3_'.$i.'_after'].'</u>';
                if($recomendation_after_resp[$i]['ConditMedCareType3_nid']==4)
                    $print_data['ConditMedCareType3_nid_4_'.$i.'_after'] = '<u>'.$print_data['ConditMedCareType3_nid_4_'.$i.'_after'].'</u>';

                if($recomendation_after_resp[$i]['PlaceMedCareType3_nid']==1)
                    $print_data['PlaceMedCareType3_nid_1_'.$i.'_after'] = '<u>'.$print_data['PlaceMedCareType3_nid_1_'.$i.'_after'].'</u>';
                if($recomendation_after_resp[$i]['PlaceMedCareType3_nid']==2)
                    $print_data['PlaceMedCareType3_nid_2_'.$i.'_after'] = '<u>'.$print_data['PlaceMedCareType3_nid_2_'.$i.'_after'].'</u>';
                if($recomendation_after_resp[$i]['PlaceMedCareType3_nid']==3)
                    $print_data['PlaceMedCareType3_nid_3_'.$i.'_after'] = '<u>'.$print_data['PlaceMedCareType3_nid_3_'.$i.'_after'].'</u>';
                if($recomendation_after_resp[$i]['PlaceMedCareType3_nid']==4)
                    $print_data['PlaceMedCareType3_nid_4_'.$i.'_after'] = '<u>'.$print_data['PlaceMedCareType3_nid_4_'.$i.'_after'].'</u>';
                if($recomendation_after_resp[$i]['PlaceMedCareType3_nid']==5)
                    $print_data['PlaceMedCareType3_nid_5_'.$i.'_after'] = '<u>'.$print_data['PlaceMedCareType3_nid_5_'.$i.'_after'].'</u>';

                if($recomendation_after_resp[$i]['ConditMedCareType3_id']==1)
                    $print_data['ConditMedCareType3_id_1_'.$i.'_after'] = '<u>'.$print_data['ConditMedCareType3_id_1_'.$i.'_after'].'</u>';
                if($recomendation_after_resp[$i]['ConditMedCareType3_id']==2)
                    $print_data['ConditMedCareType3_id_2_'.$i.'_after'] = '<u>'.$print_data['ConditMedCareType3_id_2_'.$i.'_after'].'</u>';
                if($recomendation_after_resp[$i]['ConditMedCareType3_id']==3)
                    $print_data['ConditMedCareType3_id_3_'.$i.'_after'] = '<u>'.$print_data['ConditMedCareType3_id_3_'.$i.'_after'].'</u>';
                if($recomendation_after_resp[$i]['ConditMedCareType3_id']==4)
                    $print_data['ConditMedCareType3_id_4_'.$i.'_after'] = '<u>'.$print_data['ConditMedCareType3_id_4_'.$i.'_after'].'</u>';

                if($recomendation_after_resp[$i]['PlaceMedCareType3_id']==1)
                    $print_data['PlaceMedCareType3_id_1_'.$i.'_after'] = '<u>'.$print_data['PlaceMedCareType3_id_1_'.$i.'_after'].'</u>';
                if($recomendation_after_resp[$i]['PlaceMedCareType3_id']==2)
                    $print_data['PlaceMedCareType3_id_2_'.$i.'_after'] = '<u>'.$print_data['PlaceMedCareType3_id_2_'.$i.'_after'].'</u>';
                if($recomendation_after_resp[$i]['PlaceMedCareType3_id']==3)
                    $print_data['PlaceMedCareType3_id_3_'.$i.'_after'] = '<u>'.$print_data['PlaceMedCareType3_id_3_'.$i.'_after'].'</u>';
                if($recomendation_after_resp[$i]['PlaceMedCareType3_id']==4)
                    $print_data['PlaceMedCareType3_id_4_'.$i.'_after'] = '<u>'.$print_data['PlaceMedCareType3_id_4_'.$i.'_after'].'</u>';
                if($recomendation_after_resp[$i]['PlaceMedCareType3_id']==5)
                    $print_data['PlaceMedCareType3_id_5_'.$i.'_after'] = '<u>'.$print_data['PlaceMedCareType3_id_5_'.$i.'_after'].'</u>';

                if($recomendation_after_resp[$i]['EvnVizitDisp_IsVMP']==1)
                    $print_data['EvnVizitDisp_IsVMP_1_'.$i.'_after'] = '<u>'.$print_data['EvnVizitDisp_IsVMP_1_'.$i.'_after'].'</u>';
                if($recomendation_after_resp[$i]['EvnVizitDisp_IsVMP']==2)
                    $print_data['EvnVizitDisp_IsVMP_2_'.$i.'_after'] = '<u>'.$print_data['EvnVizitDisp_IsVMP_2_'.$i.'_after'].'</u>';

                if($recomendation_after_resp[$i]['EvnVizitDisp_IsFirstTime'] == 1)
                    $print_data['EvnVizitDisp_IsFirstTime_1_'.$i.'_after'] = '<u>'.$print_data['EvnVizitDisp_IsFirstTime_1_'.$i.'_after'].'</u>';
                if($recomendation_after_resp[$i]['EvnVizitDisp_IsFirstTime'] == 2)
                    $print_data['EvnVizitDisp_IsFirstTime_2_'.$i.'_after'] = '<u>'.$print_data['EvnVizitDisp_IsFirstTime_2_'.$i.'_after'].'</u>';
            }

            $print_data['before_disp_'.$i] = '<tr><td style="padding-left: 25px; padding-top: 3px;">
                15.'.($i+2).'. Диагноз ____________________<u>'.$print_data['diag_'.$i.'_before'].'</u>___________________ (код по МКБ).
            </td></tr>
            <tr><td style="padding-left: 25px; padding-top: 3px;">
                15.'.($i+2).'.1.   Диспансерное   наблюдение:
                '.$print_data['DispSurveilType_id_1_'.$i.'_before'].',
                '.$print_data['DispSurveilType_id_2_'.$i.'_before'].',
                '.$print_data['DispSurveilType_id_3_'.$i.'_before'].',
                (нужное подчеркнуть).
            </td></tr>
            <tr><td style="padding-left: 25px; padding-top: 3px;">
                15.'.($i+2).'.2.  Лечение  было  назначено:
                да,
                '.$print_data['ConditMedCareType2_nid_1_'.$i.'_before'].' (нужное подчеркнуть);
                если "да":
                '.$print_data['ConditMedCareType2_nid_2_'.$i.'_before'].',
                '.$print_data['ConditMedCareType2_nid_3_'.$i.'_before'].',
                '.$print_data['ConditMedCareType2_nid_4_'.$i.'_before'].'  (нужное  подчеркнуть);
                '.$print_data['PlaceMedCareType2_nid_1_'.$i.'_before'].',
                '.$print_data['PlaceMedCareType2_nid_2_'.$i.'_before'].',
                '.$print_data['PlaceMedCareType2_nid_3_'.$i.'_before'].',
                '.$print_data['PlaceMedCareType2_nid_4_'.$i.'_before'].'(нужное подчеркнуть).
            </td></tr>
            <tr><td style="padding-left: 25px; padding-top: 3px;">
                15.'.($i+2).'.3.  Лечение  было  выполнено:
                '.$print_data['ConditMedCareType2_id_2_'.$i.'_before'].',
                '.$print_data['ConditMedCareType2_id_3_'.$i.'_before'].',
                '.$print_data['ConditMedCareType2_id_4_'.$i.'_before'].'  (нужное  подчеркнуть);
                '.$print_data['PlaceMedCareType2_id_1_'.$i.'_before'].',
                '.$print_data['PlaceMedCareType2_id_2_'.$i.'_before'].',
                '.$print_data['PlaceMedCareType2_id_3_'.$i.'_before'].',
                '.$print_data['PlaceMedCareType2_id_4_'.$i.'_before'].'(нужное подчеркнуть).
            </td></tr>
            <tr><td style="padding-left: 25px; padding-top: 3px;">
                15.'.($i+2).'.4.  Медицинская  реабилитация  и (или) санаторно-курортное лечение были  назначены:
                да,
                '.$print_data['ConditMedCareType3_nid_1_'.$i.'_before'].' (нужное подчеркнуть);
                если "да":
                '.$print_data['ConditMedCareType3_nid_2_'.$i.'_before'].',
                '.$print_data['ConditMedCareType3_nid_3_'.$i.'_before'].',
                '.$print_data['ConditMedCareType3_nid_4_'.$i.'_before'].'  (нужное  подчеркнуть);
                '.$print_data['PlaceMedCareType3_nid_1_'.$i.'_before'].',
                '.$print_data['PlaceMedCareType3_nid_2_'.$i.'_before'].',
                '.$print_data['PlaceMedCareType3_nid_3_'.$i.'_before'].',
                '.$print_data['PlaceMedCareType3_nid_4_'.$i.'_before'].',
                '.$print_data['PlaceMedCareType3_nid_5_'.$i.'_before'].'
                (нужное подчеркнуть).
            </td></tr>
            <tr><td style="padding-left: 25px; padding-top: 3px;">
                15.'.($i+2).'.5.  Медицинская  реабилитация  и (или) санаторно-курортное лечение были  выполнены:
                '.$print_data['ConditMedCareType3_id_2_'.$i.'_before'].',
                '.$print_data['ConditMedCareType3_id_3_'.$i.'_before'].',
                '.$print_data['ConditMedCareType3_id_4_'.$i.'_before'].'  (нужное  подчеркнуть);
                '.$print_data['PlaceMedCareType3_id_1_'.$i.'_before'].',
                '.$print_data['PlaceMedCareType3_id_2_'.$i.'_before'].',
                '.$print_data['PlaceMedCareType3_id_3_'.$i.'_before'].',
                '.$print_data['PlaceMedCareType3_id_4_'.$i.'_before'].',
                '.$print_data['PlaceMedCareType3_id_5_'.$i.'_before'].'
                (нужное подчеркнуть).
            </td></tr>
            <tr><td style="padding-left: 25px; padding-top: 3px;">
                15.'.($i+2).'.6.  Высокотехнологичная медицинская помощь была рекомендована:
                '.$print_data['EvnDiagDopDisp_IsVMP_2'.$i.'_before'].',
                '.$print_data['EvnDiagDopDisp_IsVMP_1'.$i.'_before'].',
                (нужное   подчеркнуть);   если  "да":  оказана,  не  оказана  (нужное подчеркнуть).
            </td></tr>
            ';

            $print_data['after_disp_'.$i] = '<tr><td style="padding-left: 25px; padding-top: 3px;">
                16.'.($i+2).'. Диагноз ____________________<u>'.$print_data['diag_'.$i.'_after'].'</u>___________________ (код по МКБ).
            </td></tr>
            <tr><td style="padding-left: 25px; padding-top: 3px;">
                16.'.($i+2).'.1.   Диагноз установлен впервые:
                '.$print_data['EvnVizitDisp_IsFirstTime_2_'.$i.'_after'].',
                '.$print_data['EvnVizitDisp_IsFirstTime_1_'.$i.'_after'].',
                (нужное подчеркнуть).
            </td></tr>
            <tr><td style="padding-left: 25px; padding-top: 3px;">
                16.'.($i+2).'.2.   Диспансерное   наблюдение:
                '.$print_data['DispSurveilType_id_1_'.$i.'_after'].',
                '.$print_data['DispSurveilType_id_2_'.$i.'_after'].',
                '.$print_data['DispSurveilType_id_3_'.$i.'_after'].',
                (нужное подчеркнуть).
            </td></tr>
            <tr><td style="padding-left: 25px; padding-top: 3px;">
                16.'.($i+2).'.3.  Дополнительные  консультации и исследования назначены:
                да,
                '.$print_data['ConditMedCareType1_nid_1_'.$i.'_after'].' (нужное подчеркнуть);
                если "да":
                '.$print_data['ConditMedCareType1_nid_2_'.$i.'_after'].',
                '.$print_data['ConditMedCareType1_nid_3_'.$i.'_after'].',
                '.$print_data['ConditMedCareType1_nid_4_'.$i.'_after'].'  (нужное  подчеркнуть);
                '.$print_data['PlaceMedCareType1_nid_1_'.$i.'_after'].',
                '.$print_data['PlaceMedCareType1_nid_2_'.$i.'_after'].',
                '.$print_data['PlaceMedCareType1_nid_3_'.$i.'_after'].',
                '.$print_data['PlaceMedCareType1_nid_4_'.$i.'_after'].'(нужное подчеркнуть).
            </td></tr>
            <tr><td style="padding-left: 25px; padding-top: 3px;">
                16.'.($i+2).'.4.  Дополнительные  консультации и исследования выполнены:
                да,
                '.$print_data['ConditMedCareType1_id_1_'.$i.'_after'].' (нужное подчеркнуть);
                если "да":
                '.$print_data['ConditMedCareType1_id_2_'.$i.'_after'].',
                '.$print_data['ConditMedCareType1_id_3_'.$i.'_after'].',
                '.$print_data['ConditMedCareType1_id_4_'.$i.'_after'].'  (нужное  подчеркнуть);
                '.$print_data['PlaceMedCareType1_id_1_'.$i.'_after'].',
                '.$print_data['PlaceMedCareType1_id_2_'.$i.'_after'].',
                '.$print_data['PlaceMedCareType1_id_3_'.$i.'_after'].',
                '.$print_data['PlaceMedCareType1_id_4_'.$i.'_after'].'(нужное подчеркнуть).
            </td></tr>
            <tr><td style="padding-left: 25px; padding-top: 3px;">
                16.'.($i+2).'.5.  Лечение  назначено:
                да,
                '.$print_data['ConditMedCareType2_nid_1_'.$i.'_after'].' (нужное подчеркнуть);
                если "да":
                '.$print_data['ConditMedCareType2_nid_2_'.$i.'_after'].',
                '.$print_data['ConditMedCareType2_nid_3_'.$i.'_after'].',
                '.$print_data['ConditMedCareType2_nid_4_'.$i.'_after'].'  (нужное  подчеркнуть);
                '.$print_data['PlaceMedCareType2_nid_1_'.$i.'_after'].',
                '.$print_data['PlaceMedCareType2_nid_2_'.$i.'_after'].',
                '.$print_data['PlaceMedCareType2_nid_3_'.$i.'_after'].',
                '.$print_data['PlaceMedCareType2_nid_4_'.$i.'_after'].',
                '.$print_data['PlaceMedCareType2_nid_5_'.$i.'_after'].'
                (нужное подчеркнуть).
            </td></tr>
            <tr><td style="padding-left: 25px; padding-top: 3px;">
                16.'.($i+2).'.6.  Медицинская  реабилитация  и (или) санаторно-курортное лечение назначены:
                да,
                '.$print_data['ConditMedCareType3_nid_1_'.$i.'_after'].' (нужное подчеркнуть);
                если "да":
                '.$print_data['ConditMedCareType3_nid_2_'.$i.'_after'].',
                '.$print_data['ConditMedCareType3_nid_3_'.$i.'_after'].',
                '.$print_data['ConditMedCareType3_nid_4_'.$i.'_after'].'  (нужное  подчеркнуть);
                '.$print_data['PlaceMedCareType3_nid_1_'.$i.'_after'].',
                '.$print_data['PlaceMedCareType3_nid_2_'.$i.'_after'].',
                '.$print_data['PlaceMedCareType3_nid_3_'.$i.'_after'].',
                '.$print_data['PlaceMedCareType3_nid_4_'.$i.'_after'].',
                '.$print_data['PlaceMedCareType3_nid_5_'.$i.'_after'].'
                (нужное подчеркнуть).
            </td></tr>
            <tr><td style="padding-left: 25px; padding-top: 3px;">
                16.'.($i+2).'.7.   Высокотехнологичная медицинская помощь была рекомендована:
                '.$print_data['EvnVizitDisp_IsVMP_2_'.$i.'_after'].',
                '.$print_data['EvnVizitDisp_IsVMP_1_'.$i.'_after'].',
                (нужное   подчеркнуть).
            </td></tr>
            ';

        }
        $print_data['Is_Invalid'] = 1;
        if($print_data['InvalidType_id'] <> 1){
            $print_data['Is_Invalid'] = 2;
            $print_data['InvalidType_id'] += 1;
        }

        if($print_data['AssessmentHealth_IsMental'] == 1)
            $print_data['IsMental'] = 'умственные';
        else
            $print_data['IsMental'] = '<u>умственные</u>';

        if($print_data['AssessmentHealth_IsOtherPsych'] == 1)
            $print_data['IsOtherPsych'] = 'другие  психологические';
        else
            $print_data['IsOtherPsych'] = '<u>другие  психологические</u>';

        if($print_data['AssessmentHealth_IsLanguage'] == 1)
            $print_data['IsLanguage'] = 'языковые  и  речевые';
        else
            $print_data['IsLanguage'] = '<u>языковые  и  речевые</u>';

        if($print_data['AssessmentHealth_IsVestibular'] == 1)
            $print_data['IsVestibular'] = 'слуховые и вестибулярные';
        else
            $print_data['IsVestibular'] = '<u>слуховые и вестибулярные</u>';

        if($print_data['AssessmentHealth_IsVisual'] == 1)
            $print_data['IsVisual'] = 'зрительные';
        else
            $print_data['IsVisual'] = '<u>зрительные</u>';

        if($print_data['AssessmentHealth_IsMeals'] == 1)
            $print_data['IsMeals'] = 'висцеральные  и  метаболические  расстройства питания';
        else
            $print_data['IsMeals'] = '<u>висцеральные  и  метаболические  расстройства питания</u>';

        if($print_data['AssessmentHealth_IsMotor'] == 1)
            $print_data['IsMotor'] = 'двигательные';
        else
            $print_data['IsMotor'] = '<u>двигательные</u>';

        if($print_data['AssessmentHealth_IsDeform'] == 1)
            $print_data['IsDeform'] = 'уродующие';
        else
            $print_data['IsDeform'] = '<u>уродующие</u>';

        if($print_data['AssessmentHealth_IsGeneral'] == 1)
            $print_data['IsGeneral'] = 'общие  и  генерализованные';
        else
            $print_data['IsGeneral'] = '<u>общие  и  генерализованные</u>';

        $print_data['vizitDispOrp13_string'] = '<tr><td style="padding-left: 25px; padding-top: 3px; border-bottom: 1px solid;">
                &nbsp;
            </td></tr>
            <tr><td style="padding-left: 25px; padding-top: 3px; border-bottom: 1px solid;">
                &nbsp;
            </td></tr>
            <tr><td style="padding-left: 25px; padding-top: 3px; border-bottom: 1px solid;">
                &nbsp;
            </td></tr>
            <tr><td style="padding-left: 25px; padding-top: 3px; border-bottom: 1px solid;">
                &nbsp;
            </td></tr>';
        $print_data['uslugaDispOrp_string'] = $print_data['vizitDispOrp13_string'];
		
		$print_data['AssessmentHealth_HealthRecom'] = ($print_data['AssessmentHealth_HealthRecom']) ? '<div style="margin-left: 40px;">'.$print_data['AssessmentHealth_HealthRecom'].'</div>' : '&nbsp;';
		$print_data['AssessmentHealth_DispRecom'] = ($print_data['AssessmentHealth_DispRecom']) ? '<div style="margin-left: 40px;">'.$print_data['AssessmentHealth_DispRecom'].'</div>' : '&nbsp;';

        $vizitDispOrp13_array = $this->dbmodel->loadEvnVizitDispOrpGrid($data);
        if(count($vizitDispOrp13_array)>0){
            $print_data['vizitDispOrp13_string'] = '';
            for($i=0;$i<count($vizitDispOrp13_array);$i++){
                $print_data['vizitDispOrp13_string'] .= $vizitDispOrp13_array[$i]['OrpDispSpec_Name'].' - '.$vizitDispOrp13_array[$i]['EvnVizitDispOrp_setDate'].';&nbsp;&nbsp;&nbsp;';
            }
        }

        $uslugaDispOrp_array = $this->dbmodel->loadEvnUslugaDispOrpGrid($data);
        if(count($uslugaDispOrp_array)>0){
            $print_data['uslugaDispOrp_string'] = '';
            for($i=0;$i<count($uslugaDispOrp_array);$i++){
                $print_data['uslugaDispOrp_string'] .= $uslugaDispOrp_array[$i]['UslugaComplex_Name'].' - '.$uslugaDispOrp_array[$i]['EvnUslugaDispOrp_setDate'].';&nbsp;&nbsp;&nbsp;';
            }
        }

		return $this->parser->parse($template, $print_data);
	}

    /**
     * Разница в датах
     */
    function real_date_diff($date1, $date2 = NULL){
        $diff = array();

        //Если вторая дата не задана принимаем ее как текущую
        if(!$date2) {
            $cd = getdate();
            $date2 = $cd['year'].'-'.$cd['mon'].'-'.$cd['mday'].' '.$cd['hours'].':'.$cd['minutes'].':'.$cd['seconds'];
        }

        //Преобразуем даты в массив
        $pattern = '/(\d+)-(\d+)-(\d+)(\s+(\d+):(\d+):(\d+))?/';
        preg_match($pattern, $date1, $matches);

        $d1 = array((int)$matches[1], (int)$matches[2], (int)$matches[3], (int)$matches[5], (int)$matches[6], (int)$matches[7]);
        preg_match($pattern, $date2, $matches);
        $d2 = array((int)$matches[1], (int)$matches[2], (int)$matches[3], (int)$matches[5], (int)$matches[6], (int)$matches[7]);

        //Если вторая дата меньше чем первая, меняем их местами
        for($i=0; $i<count($d2); $i++) {
            if($d2[$i]>$d1[$i]) break;
            if($d2[$i]<$d1[$i]) {
                $t = $d1;
                $d1 = $d2;
                $d2 = $t;
                break;
            }
        }

        //Вычисляем разность между датами (как в столбик)
        $md1 = array(31, $d1[0]%4||(!($d1[0]%100)&&$d1[0]%400)?28:29, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
        $md2 = array(31, $d2[0]%4||(!($d2[0]%100)&&$d2[0]%400)?28:29, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
        $min_v = array(NULL, 1, 1, 0, 0, 0);
        $max_v = array(NULL, 12, $d2[1]==1?$md2[11]:$md2[$d2[1]-2], 23, 59, 59);
        for($i=5; $i>=0; $i--) {
            if($d2[$i]<$min_v[$i]) {
                $d2[$i-1]--;
                $d2[$i]=$max_v[$i];
            }
            $diff[$i] = $d2[$i]-$d1[$i];
            if($diff[$i]<0) {
                $d2[$i-1]--;
                $i==2 ? $diff[$i] += $md1[$d1[1]-1] : $diff[$i] += $max_v[$i]-$min_v[$i]+1;
            }
        }

        //Возвращаем результат
        return $diff;
    }


	/**
	 * Удаление талона по доп диспансеризации детей-сирот
	 */
	function deleteEvnPLDispOrp() {
		$data = $this->ProcessInputData('deleteEvnPLDispOrp', true);
		if ($data === false) return false;

		$this->load->model('Registry' . (getRegionNick() == 'ufa' ? 'Ufa' : '') . '_model', 'Reg_model');
        $registryData = $this->Reg_model->checkEvnAccessInRegistry($data);

        if ( is_array($registryData) ) {
            $response = $registryData;
        } else {
		    $response = $this->dbmodel->deleteEvnPLDispOrp($data);
        }

		$this->ProcessModelSave($response, true, 'При удалении талона ДД возникли ошибки')->ReturnData();

		return true;
	}


	/**
	 * Проверка на наличие талона на этого человека в этом году
	 * Входящие данные: $_POST['Person_id']
	 * На выходе: JSON-строка
	 * Используется: форма редактирования талона по ДД
	 */
	function checkIfEvnPLDispOrpExists()
	{
		$data = $this->ProcessInputData('checkIfEvnPLDispOrpExists', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->checkIfEvnPLDispOrpExists($data);
		$this->ProcessModelSave($response, true)->ReturnData();
	}
	
	/**
	 * Получение данных для формы редактирования талона по ДД
	 * Входящие данные: $_POST['EvnPLDispOrp_id']
	 * На выходе: JSON-строка
	 * Используется: форма редактирования талона по ДД
	 */
	function loadEvnPLDispOrpEditForm()
	{
		$this->load->helper('Text');
		$this->load->helper('Main');
		
		$val  = array();

		$data = $this->ProcessInputData('loadEvnPLDispOrpEditForm', true);
		if ($data) 
		{
			$response = $this->dbmodel->loadEvnPLDispOrpEditForm($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
		}
	}


	/**
	 * Получение списка посещений в талоне по ДД
	 * Входящие данные: $_POST['EvnPLDispOrp_id']
	 * На выходе: JSON-строка
	 * Используется: форма редактирования талона по ДД
	 */
	function loadEvnVizitDispOrpGrid()
	{
		$data = $this->ProcessInputData('loadEvnVizitDispOrpGrid', true);
		if ($data) 
		{
			if ($data['Lpu_id'] == 0)
			{
				echo json_encode(array('success' => false));
				return true;
			}

			$response = $this->dbmodel->loadEvnVizitDispOrpGrid($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
		}
	}
	
	/**
	 * Получение списка посещений в талоне по ДД
	 * Входящие данные: $_POST['EvnPLDispOrp_id']
	 * На выходе: JSON-строка
	 * Используется: форма редактирования талона по ДД
	 */
	function loadEvnVizitDispOrpSecGrid()
	{
		$data = $this->ProcessInputData('loadEvnVizitDispOrpSecGrid', true);
		if ($data) 
		{
			if ($data['Lpu_id'] == 0)
			{
				echo json_encode(array('success' => false));
				return true;
			}

			$response = $this->dbmodel->loadEvnVizitDispOrpSecGrid($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
		}
	}
	
	/**
	 * Получение списка диагнозы и рекомендации в талоне по ДД
	 * Входящие данные: $_POST['EvnPLDispOrp_id']
	 * На выходе: JSON-строка
	 * Используется: форма редактирования талона по ДД
	 */
	function loadEvnDiagAndRecomendationGrid()
	{
		$data = $this->ProcessInputData('loadEvnDiagAndRecomendationGrid', true);
		if ($data) 
		{
			if ($data['Lpu_id'] == 0)
			{
				echo json_encode(array('success' => false));
				return true;
			}

			$response = $this->dbmodel->loadEvnDiagAndRecomendationGrid($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
		}
	}	
	
	/**
	 * Получение списка диагнозы и рекомендации в талоне по ДД
	 * Входящие данные: $_POST['EvnPLDispOrp_id']
	 * На выходе: JSON-строка
	 * Используется: форма редактирования талона по ДД
	 */
	function loadEvnDiagAndRecomendationSecGrid()
	{
		$data = $this->ProcessInputData('loadEvnDiagAndRecomendationSecGrid', true);
		if ($data) 
		{
			if ($data['Lpu_id'] == 0)
			{
				echo json_encode(array('success' => false));
				return true;
			}

			$response = $this->dbmodel->loadEvnDiagAndRecomendationSecGrid($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
		}
	}


	/**
	 * Получение списка исследований в талоне по ДД
	 * Входящие данные: $_POST['EvnPLDispOrp_id']
	 * На выходе: JSON-строка
	 * Используется: форма редактирования талона по ДД
	 */
	function loadEvnUslugaDispOrpGrid()
	{
		$this->load->helper('Text');
		$this->load->helper('Main');

		$data = $this->ProcessInputData('loadEvnUslugaDispOrpGrid', true);
		if ($data) 
		{
			if ($data['Lpu_id'] == 0)
			{
				echo json_encode(array('success' => false));
				return true;
			}

			$response = $this->dbmodel->loadEvnUslugaDispOrpGrid($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
		}
	}
	
	/**
	 * Получение списка исследований в талоне по ДД
	 * Входящие данные: $_POST['EvnPLDispOrp_id']
	 * На выходе: JSON-строка
	 * Используется: форма редактирования талона по ДД
	 */
	function loadEvnUslugaDispOrpSecGrid()
	{
		$this->load->helper('Text');
		$this->load->helper('Main');

		$data = $this->ProcessInputData('loadEvnUslugaDispOrpSecGrid', true);
		if ($data) 
		{
			if ($data['Lpu_id'] == 0)
			{
				echo json_encode(array('success' => false));
				return true;
			}

			$response = $this->dbmodel->loadEvnUslugaDispOrpSecGrid($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
		}
	}

	/**
	 * Сохранение талона амбулаторного пациента
	 * Входящие данные: ...
	 * На выходе: JSON-строка
	 * Используется: форма редактирования талона амбулаторного пациента
	 */
	function saveEvnPLDispOrp()
	{
		$this->load->model('AssessmentHealth_model');
		$ahInputRules = $this->AssessmentHealth_model->getInputRules(swModel::SCENARIO_DO_SAVE);
		unset($ahInputRules['EvnPLDisp_id']); // определится в процессе сохранения.
		$this->inputRules['saveEvnPLDispOrp'] = array_merge($this->inputRules['saveEvnPLDispOrp'], $ahInputRules);

		$data = $this->ProcessInputData('saveEvnPLDispOrp', true, true);
		if ($data) {
			$this->load->model('LpuStructure_model', 'lsmodel');
		
			if (!empty($data['DispClass_id']) && in_array($data['DispClass_id'], array(4,8)) && empty($data['EvnPLDispOrp_fid']) ) {
				$this->ReturnError('Не определён идентификатор карты предыдущего этапа');
				return false;
			}

			// Осмотры специалиста
			if ((isset($data['EvnVizitDispOrp'])) && (strlen(trim($data['EvnVizitDispOrp'])) > 0) && (trim($data['EvnVizitDispOrp']) != '[]'))
			{
				$data['EvnVizitDispOrp'] = json_decode(trim($data['EvnVizitDispOrp']), true);
				
				if ( !(count($data['EvnVizitDispOrp']) == 1 && $data['EvnVizitDispOrp'][0]['EvnVizitDispOrp_id'] == '') )
				{
					for ($i = 0; $i < count($data['EvnVizitDispOrp']); $i++) // обработка посещений в цикле
					{
						array_walk($data['EvnVizitDispOrp'][$i], 'ConvertFromUTF8ToWin1251');

						if ($data['session']['region']['nick'] == 'ufa') {
							$response = $this->lsmodel->getLpuUnitIsOMS(array(
								'LpuSection_id' => $data['EvnVizitDispOrp'][$i]['LpuSection_id']
							));
							if (!$response[0]['LpuUnit_IsOMS']) {
								echo json_encode(array('success' => false, 'Error_Msg' => toUtf('Отделение не работает по ОМС')));
								return false;
							}
						}

						if ((!isset($data['EvnVizitDispOrp'][$i]['EvnVizitDispOrp_setDate'])) || (strlen(trim($data['EvnVizitDispOrp'][$i]['EvnVizitDispOrp_setDate'])) == 0))
						{
							echo json_encode(array('success' => false, 'Error_Msg' => 'Ошибка при сохранении осмотра (не задано поле "Дата осмотра")'));
							return false;
						}
						
						if (empty($data['EvnVizitDispOrp'][$i]['MedPersonal_id']) && (empty($data['EvnVizitDispOrp'][$i]['DopDispAlien_id']) || $data['EvnVizitDispOrp'][$i]['DopDispAlien_id'] != 2))
						{
							echo json_encode(array('success' => false, 'Error_Msg' => 'Ошибка при сохранении осмотра (не задано поле "Врач")'));
							return false;
						}

						$data['EvnVizitDispOrp'][$i]['EvnVizitDispOrp_setDate'] = ConvertDateFormat(trim($data['EvnVizitDispOrp'][$i]['EvnVizitDispOrp_setDate']));
						$data['EvnVizitDispOrp'][$i]['EvnVizitDispOrp_disDate'] = ConvertDateFormat(trim($data['EvnVizitDispOrp'][$i]['EvnVizitDispOrp_disDate']));

					}
				}
				else
					$data['EvnVizitDispOrp'] = array();
			} else {
				$data['EvnVizitDispOrp'] = array();
			}

			// Лабораторные исследования
			if ((isset($data['EvnUslugaDispOrp'])) && (strlen(trim($data['EvnUslugaDispOrp'])) > 0) && (trim($data['EvnUslugaDispOrp']) != '[]'))
			{
				$data['EvnUslugaDispOrp'] = json_decode(trim($data['EvnUslugaDispOrp']), true);

				if ( !(count($data['EvnUslugaDispOrp']) == 1 && $data['EvnUslugaDispOrp'][0]['EvnUslugaDispOrp_id'] == '') )
				{
					for ($i = 0; $i < count($data['EvnUslugaDispOrp']); $i++) // обработка услуг в цикле
					{
						array_walk($data['EvnUslugaDispOrp'][$i], 'ConvertFromUTF8ToWin1251');

						if ($data['session']['region']['nick'] == 'ufa') {
							$response = $this->lsmodel->getLpuUnitIsOMS(array(
								'LpuSection_id' => $data['EvnUslugaDispOrp'][$i]['LpuSection_id']
							));
							if (!$response[0]['LpuUnit_IsOMS']) {
								echo json_encode(array('success' => false, 'Error_Msg' => toUtf('Отделение не работает по ОМС')));
								return false;
							}
						}

						if ((!isset($data['EvnUslugaDispOrp'][$i]['EvnUslugaDispOrp_setDate'])) || (strlen(trim($data['EvnUslugaDispOrp'][$i]['EvnUslugaDispOrp_setDate'])) == 0))
						{
							echo json_encode(array('success' => false, 'Error_Msg' => 'Ошибка при сохранении лабораторного исследования (не задано поле "Дата исследования")'));
							return false;
						}

						/*if (empty($data['EvnUslugaDispOrp'][$i]['MedPersonal_id']) && (empty($data['EvnUslugaDispOrp'][$i]['ExaminationPlace_id']) || $data['EvnUslugaDispOrp'][$i]['ExaminationPlace_id'] != 3))
						{
							echo json_encode(array('success' => false, 'Error_Msg' => 'Ошибка при сохранении лабораторного исследования (не задано поле "Врач")'));
							return false;
						}*/
						
						if ((!isset($data['EvnUslugaDispOrp'][$i]['UslugaComplex_id'])) || (!($data['EvnUslugaDispOrp'][$i]['UslugaComplex_id'] > 0)))
						{
							echo json_encode(array('success' => false, 'cancelErrorHandle'=>true, 'Error_Msg' => toUTF('Ошибка при сохранении лабораторного исследования (не задана услуга)')));
							return false;
						}

						$data['EvnUslugaDispOrp'][$i]['EvnUslugaDispOrp_setDate'] = ConvertDateFormat(trim($data['EvnUslugaDispOrp'][$i]['EvnUslugaDispOrp_setDate']));
						$data['EvnUslugaDispOrp'][$i]['EvnUslugaDispOrp_disDate'] = ConvertDateFormat(trim($data['EvnUslugaDispOrp'][$i]['EvnUslugaDispOrp_disDate']));
						$data['EvnUslugaDispOrp'][$i]['EvnUslugaDispOrp_didDate'] = ConvertDateFormat(trim($data['EvnUslugaDispOrp'][$i]['EvnUslugaDispOrp_didDate']));

					}
				}
				else
					$data['EvnUslugaDispOrp'] = array();
			} else {
				$data['EvnUslugaDispOrp'] = array();
			}
			
			// грид диагнозы и рекомендации
			$data['EvnDiagAndRecomendation'] = toUtf($data['EvnDiagAndRecomendation']);
			if ((isset($data['EvnDiagAndRecomendation'])) && (strlen(trim($data['EvnDiagAndRecomendation'])) > 0) && (trim($data['EvnDiagAndRecomendation']) != '[]'))
			{
				$data['EvnDiagAndRecomendation'] = json_decode(trim($data['EvnDiagAndRecomendation']), true);
			} else {
				$data['EvnDiagAndRecomendation'] = array();
			}
			
			
			$server_id = $data['Server_id'];

			$data = array_merge($data, getSessionParams());
			
			$data['Server_id'] = $server_id;

			$response = $this->dbmodel->saveEvnPLDispOrp($data);
			$this->ProcessModelSave($response, true)->ReturnData();
		}
	}

	/**
	 * Сохранение талона амбулаторного пациента
	 * Входящие данные: ...
	 * На выходе: JSON-строка
	 * Используется: форма редактирования талона амбулаторного пациента
	 */
	function saveEvnPLDispOrpSec()
	{
		$this->load->model('AssessmentHealth_model');
		$ahInputRules = $this->AssessmentHealth_model->getInputRules(swModel::SCENARIO_DO_SAVE);
		unset($ahInputRules['EvnPLDisp_id']); // определится в процессе сохранения.
		$this->inputRules['saveEvnPLDispOrpSec'] = array_merge($this->inputRules['saveEvnPLDispOrpSec'], $ahInputRules);

		$data = $this->ProcessInputData('saveEvnPLDispOrpSec', true, true);
		if ($data === false) return false;// ProcessInputData выведет ошибки в случае false
		$this->load->model('LpuStructure_model', 'lsmodel');

		if (!empty($data['DispClass_id']) && in_array($data['DispClass_id'], array(4,8)) && empty($data['EvnPLDispOrp_fid']) ) {
			$this->ReturnError('Не определён идентификатор карты предыдущего этапа');
			return false;
		}

		// Осмотры специалиста
		if ((isset($data['EvnVizitDispOrp'])) && (strlen(trim($data['EvnVizitDispOrp'])) > 0) && (trim($data['EvnVizitDispOrp']) != '[]'))
		{
			$data['EvnVizitDispOrp'] = json_decode(trim($data['EvnVizitDispOrp']), true);

			if ( !(count($data['EvnVizitDispOrp']) == 1 && $data['EvnVizitDispOrp'][0]['EvnVizitDispOrp_id'] == '') )
			{
				for ($i = 0; $i < count($data['EvnVizitDispOrp']); $i++) // обработка посещений в цикле
				{
					array_walk($data['EvnVizitDispOrp'][$i], 'ConvertFromUTF8ToWin1251');

					if ($data['session']['region']['nick'] == 'ufa') {
						$response = $this->lsmodel->getLpuUnitIsOMS(array(
							'LpuSection_id' => $data['EvnVizitDispOrp'][$i]['LpuSection_id']
						));
						if (!$response[0]['LpuUnit_IsOMS']) {
							$this->ReturnError('Отделение не работает по ОМС');
							return false;
						}
					}

					if ((!isset($data['EvnVizitDispOrp'][$i]['EvnVizitDispOrp_setDate'])) || (strlen(trim($data['EvnVizitDispOrp'][$i]['EvnVizitDispOrp_setDate'])) == 0))
					{
						echo json_encode(array('success' => false, 'Error_Msg' => 'Ошибка при сохранении осмотра (не задано поле "Дата осмотра")'));
						return false;
					}

					if (empty($data['EvnVizitDispOrp'][$i]['MedPersonal_id']) && (empty($data['EvnVizitDispOrp'][$i]['DopDispAlien_id']) || $data['EvnVizitDispOrp'][$i]['DopDispAlien_id'] != 2))
					{
						echo json_encode(array('success' => false, 'Error_Msg' => 'Ошибка при сохранении осмотра (не задано поле "Врач")'));
						return false;
					}

					$data['EvnVizitDispOrp'][$i]['EvnVizitDispOrp_setDate'] = ConvertDateFormat(trim($data['EvnVizitDispOrp'][$i]['EvnVizitDispOrp_setDate']));
					$data['EvnVizitDispOrp'][$i]['EvnVizitDispOrp_disDate'] = ConvertDateFormat(trim($data['EvnVizitDispOrp'][$i]['EvnVizitDispOrp_disDate']));

				}
			}
			else
				$data['EvnVizitDispOrp'] = array();
		} else {
			$data['EvnVizitDispOrp'] = array();
		}

		// Лабораторные исследования
		if ((isset($data['EvnUslugaDispOrp'])) && (strlen(trim($data['EvnUslugaDispOrp'])) > 0) && (trim($data['EvnUslugaDispOrp']) != '[]'))
		{
			$data['EvnUslugaDispOrp'] = json_decode(trim($data['EvnUslugaDispOrp']), true);

			if ( !(count($data['EvnUslugaDispOrp']) == 1 && $data['EvnUslugaDispOrp'][0]['EvnUslugaDispOrp_id'] == '') )
			{
				for ($i = 0; $i < count($data['EvnUslugaDispOrp']); $i++) // обработка услуг в цикле
				{
					array_walk($data['EvnUslugaDispOrp'][$i], 'ConvertFromUTF8ToWin1251');

					if ($data['session']['region']['nick'] == 'ufa') {
						$response = $this->lsmodel->getLpuUnitIsOMS(array(
							'LpuSection_id' => $data['EvnUslugaDispOrp'][$i]['LpuSection_id']
						));
						if (!$response[0]['LpuUnit_IsOMS']) {
							echo json_encode(array('success' => false, 'Error_Msg' => toUtf('Отделение не работает по ОМС')));
							return false;
						}
					}

					if ((!isset($data['EvnUslugaDispOrp'][$i]['EvnUslugaDispOrp_setDate'])) || (strlen(trim($data['EvnUslugaDispOrp'][$i]['EvnUslugaDispOrp_setDate'])) == 0))
					{
						echo json_encode(array('success' => false, 'Error_Msg' => 'Ошибка при сохранении лабораторного исследования (не задано поле "Дата исследования")'));
						return false;
					}

					/*if (empty($data['EvnUslugaDispOrp'][$i]['MedPersonal_id']) && (empty($data['EvnUslugaDispOrp'][$i]['ExaminationPlace_id']) || $data['EvnUslugaDispOrp'][$i]['ExaminationPlace_id'] != 3))
					{
						echo json_encode(array('success' => false, 'Error_Msg' => 'Ошибка при сохранении лабораторного исследования (не задано поле "Врач")'));
						return false;
					}*/

					if ((!isset($data['EvnUslugaDispOrp'][$i]['UslugaComplex_id'])) || (!($data['EvnUslugaDispOrp'][$i]['UslugaComplex_id'] > 0)))
					{
						echo json_encode(array('success' => false, 'cancelErrorHandle'=>true, 'Error_Msg' => toUTF('Ошибка при сохранении лабораторного исследования (не задана услуга)')));
						return false;
					}

					$data['EvnUslugaDispOrp'][$i]['EvnUslugaDispOrp_setDate'] = ConvertDateFormat(trim($data['EvnUslugaDispOrp'][$i]['EvnUslugaDispOrp_setDate']));
					$data['EvnUslugaDispOrp'][$i]['EvnUslugaDispOrp_disDate'] = ConvertDateFormat(trim($data['EvnUslugaDispOrp'][$i]['EvnUslugaDispOrp_disDate']));
					$data['EvnUslugaDispOrp'][$i]['EvnUslugaDispOrp_didDate'] = ConvertDateFormat(trim($data['EvnUslugaDispOrp'][$i]['EvnUslugaDispOrp_didDate']));

				}
			}
			else
				$data['EvnUslugaDispOrp'] = array();
		} else {
			$data['EvnUslugaDispOrp'] = array();
		}

		// грид диагнозы и рекомендации
		$data['EvnDiagAndRecomendation'] = toUtf($data['EvnDiagAndRecomendation']);
		if ((isset($data['EvnDiagAndRecomendation'])) && (strlen(trim($data['EvnDiagAndRecomendation'])) > 0) && (trim($data['EvnDiagAndRecomendation']) != '[]'))
		{
			$data['EvnDiagAndRecomendation'] = json_decode(trim($data['EvnDiagAndRecomendation']), true);
		} else {
			$data['EvnDiagAndRecomendation'] = array();
		}


		$server_id = $data['Server_id'];

		$data = array_merge($data, getSessionParams());

		$data['Server_id'] = $server_id;

		$response = $this->dbmodel->saveEvnPLDispOrpSec($data);
		$this->ProcessModelSave($response, true)->ReturnData();

	}


	/**
	 * Получение числа талонов 1-го этапа ДДН с распределением по годам, для формирования списка на клиенте
	 * Входящие данные: ...
	 * На выходе: JSON-строка
	 * Используется: форма поиска/потокового ввода талонов по ДД
	 */
	function getEvnPLDispOrpYears()
	{
		$this->load->helper('Text');
		
		$data = getSessionParams();
		$year = date('Y');
		$info = $this->dbmodel->getEvnPLDispOrpYears($data);
   		if ( is_array($info) && count($info) > 0 ) {
			$val = array();
			$flag = false;
	   		foreach ($info as $row) {
				array_walk($row, 'ConvertFromWin1251ToUTF8');
				if ( $row['EvnPLDispOrp_Year'] == $year )
					$flag = true;
				$val[] = $row;
	        }
			if (!$flag)
				$val[] = array('EvnPLDispOrp_Year'=>$year, 'count'=>0);
	        $this->ReturnData($val);

        }
        else {
        	$val = array();
			$val[] = array('EvnPLDispOrp_Year'=>$year, 'count'=>0);
			$this->ReturnData($val);
		}
	}

	/**
	 * Получение числа талонов 2-го этапа ДДН с распределением по годам, для формирования списка на клиенте
	 * Входящие данные: ...
	 * На выходе: JSON-строка
	 * Используется: форма поиска/потокового ввода талонов по ДД
	 */
	function getEvnPLDispOrpYearsSec()
	{
		$this->load->helper('Text');

		$data = getSessionParams();
		$year = date('Y');
		$info = $this->dbmodel->getEvnPLDispOrpYearsSec($data);
   		if ( is_array($info) && count($info) > 0 ) {
			$val = array();
			$flag = false;
	   		foreach ($info as $row) {
				array_walk($row, 'ConvertFromWin1251ToUTF8');
				if ( $row['EvnPLDispOrp_Year'] == $year )
					$flag = true;
				$val[] = $row;
	        }
			if (!$flag)
				$val[] = array('EvnPLDispOrp_Year'=>$year, 'count'=>0);
	        $this->ReturnData($val);

        }
        else {
        	$val = array();
			$val[] = array('EvnPLDispOrp_Year'=>$year, 'count'=>0);
			$this->ReturnData($val);
		}
	}

	/**
	 * Сохранение осмотра
	 */
	function saveEvnVizitDispOrp()
	{
		$data = $this->ProcessInputData('saveEvnVizitDispOrp', true, true);		
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveEvnVizitDispOrp($data);
		$this->ProcessModelSave($response, true, 'При сохранении возникли ошибки')->ReturnData();

		return true;
	}

	/**
	 * Удаление осмотра
	 * Общее для 1 и 2 этапа
	 */
	function deleteEvnVizitDispOrp()
	{
		$data = $this->ProcessInputData('deleteEvnVizitDispOrp', true, true);		
		if ($data === false) { return false; }

		$response = $this->dbmodel->deleteEvnVizitDispOrp($data);
		$this->ProcessModelSave($response, true, 'При сохранении возникли ошибки')->ReturnData();

		return true;
	}

	/**
	 * Сохранение осмотра
	 */
	function saveEvnVizitDispOrpSec()
	{
		$data = $this->ProcessInputData('saveEvnVizitDispOrp', true, true);	// набор полей как в 1
		if ($data === false) { return false; }

		$response = $this->dbmodel->saveEvnVizitDispOrpSec($data);
		$this->ProcessModelSave($response, true, 'При сохранении возникли ошибки')->ReturnData();

		return true;
	}
}
?>