<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Foobar - This is a controller to make many foo and to control some bar
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *-TODO: Do some explanation, preamble and describing
 * @package      Foobaring
 * @access       public
 * @copyright    Copyright (c) 2009-2010 Swan Ltd.
 * @author       IGabdushev
 * @version      12 2011
 */
/**
 * @property MorbusCrazySpecifics_model $dbmodel
 */
class MorbusCrazySpecifics extends swController
{
    var $model_name = "MorbusCrazySpecifics_model";

	/**
	 * MorbusCrazySpecifics constructor.
	 */
    function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model($this->model_name, 'dbmodel');
        $this->inputRules = array(
            'load' => array(
                array('field' => 'Morbus_id'                          ,'label' => 'Идентификатор заболевания'                                                  ,'rules' => 'required', 'type' => 'id'),
                array('field' => 'Evn_id'                             ,'label' => 'Идентификатор учетного документа'                                           ,'rules' => 'required', 'type' => 'id'),
            ),
            'save' => array(
                //Психоспецифика движения
                array('field' => 'Morbus_id'                          ,'label' => 'Идентификатор заболевания'                                                  ,'rules' => 'required', 'type' => 'id'),
                array('field' => 'Evn_id'                             ,'label' => 'Идентификатор Движения КВС'                                                 ,'rules' => 'required', 'type' => 'id'),
                array('field' => 'CrazyHospType_id'                   ,'label' => 'Госпитализирован'                                                           ,'rules' => ''        , 'type' => 'id'),
                array('field' => 'CrazySupplyType_id'                 ,'label' => 'Поступление'                                                                ,'rules' => ''        , 'type' => 'id'),
                array('field' => 'CrazyDirectType_id'                 ,'label' => 'Кем направлен'                                                              ,'rules' => ''        , 'type' => 'id'),
                array('field' => 'CrazySupplyOrderType_id'            ,'label' => 'Порядок поступления'                                                        ,'rules' => ''        , 'type' => 'id'),
                array('field' => 'CrazyDirectFromType_id'             ,'label' => 'Откуда поступил'                                                            ,'rules' => ''        , 'type' => 'id'),
                array('field' => 'CrazyPurposeDirectType_id'          ,'label' => 'Цель направления                                                           ','rules' => ''        , 'type' => 'id'),
                array('field' => 'CrazyJudgeDecisionArt35Type_id'     ,'label' => 'Решение судьи по ст. 35'                                                    ,'rules' => ''        , 'type' => 'id'),
                array('field' => 'CrazyLeaveInvalidType_id'           ,'label' => 'Инвалидность при выписке по псих. заболеванию'                              ,'rules' => ''        , 'type' => 'id'),
                array('field' => 'CrazySurveyHIVType_id'              ,'label' => 'Обследование больного на ВИЧ'                                               ,'rules' => ''        , 'type' => 'id'),
                array('field' => 'CrazyLeaveType_id'                  ,'label' => 'Выбыл'                                                                      ,'rules' => ''        , 'type' => 'id'),
                array('field' => 'MorbusCrazySection_NumCard'         ,'label' => 'Медицинская карта стационарного больного'                                   ,'rules' => 'trim'    , 'type' => 'string'),
                array('field' => 'MorbusCrazySection_JudgeSetDT'      ,'label' => 'Дата решения суда о начале принудительного лечения'                         ,'rules' => 'trim'    , 'type' => 'date'),
                array('field' => 'MorbusCrazySection_JudgeDisDT'      ,'label' => 'Дата решения суда об окончании принудительного лечения'                     ,'rules' => 'trim'    , 'type' => 'date'),
                array('field' => 'CrazyForceTreatResultType_id'       ,'label' => 'В случае окончания принудительного лечения'                                 ,'rules' => ''        , 'type' => 'id'),
                array('field' => 'MorbusCrazySection_LastForceDisDT'  ,'label' => 'Дата окончания предыдущего принудительного лечения'                         ,'rules' => ''        , 'type' => 'date'),
                array('field' => 'MorbusCrazySection_LastLeaveDisDT'  ,'label' => 'Дата предыдущей выписки из психиатрического или наркологического стационара','rules' => 'trim'    , 'type' => 'date'),
                array('field' => 'MorbusCrazySection_LTMDayCount'     ,'label' => 'Число дней работы в ЛТМ'                                                    ,'rules' => ''        , 'type' => 'int'),
                array('field' => 'MorbusCrazySection_HolidayDayCount' ,'label' => 'Число дней лечебных отпусков'                                               ,'rules' => ''        , 'type' => 'int'),
                array('field' => 'MorbusCrazySection_HolidayCount'    ,'label' => 'Число лечебных отпусков'                                                    ,'rules' => ''        , 'type' => 'int'),
                array('field' => 'MorbusCrazySection_IsAnotherSyringe','label' => 'Использование чужих шприцов, игл, приспособлений в течение последнего года' ,'rules' => ''        , 'type' => 'id'),
                array('field' => 'MorbusCrazySection_IsLiveWithJunkie','label' => 'Проживание с потребителем психоактивных средств'                            ,'rules' => ''        , 'type' => 'id'),
                array('field' => 'CrazyDrugVolumeType_id'             ,'label' => 'Полученный объем наркологической помощи в данном учреждении'                ,'rules' => ''        , 'type' => 'id'),
                //ПсихоСпецифика заболевания
                array('field' => 'CrazyAmbulMonitoringType_id'             ,'label' => 'Вид амбулаторного наблюдения'                                                   ,'rules' => ''        , 'type' => 'id'),
                array('field' => 'Diag_nid'                                ,'label' => 'Сопутствующее психическое (наркологическое) заболевание'                        ,'rules' => ''        , 'type' => 'id'),
                array('field' => 'Diag_sid'                                ,'label' => 'Сопутствующее соматическое (в т.ч. неврологическое) заболевание'                ,'rules' => ''        , 'type' => 'id'),
                array('field' => 'CrazyResultDeseaseType_id'               ,'label' => 'Исход заболевания'                                                              ,'rules' => ''        , 'type' => 'id'),
                //ПсихоСпецифика общего заболевания
                array('field' => 'CrazyDeathCauseType_id'        ,'label' => 'Причина смерти'                                          ,'rules' => ''        , 'type' => 'id'),
                array('field' => 'MorbusCrazyBase_EarlyCareCount','label' => 'Ранее находился на принудительном долечивании, число раз','rules' => ''        , 'type' => 'id'),
                //ПсихоСпецифика на человеке
                array('field' =>  'Person_id'                            ,'label' => 'Человек'                                                    ,'rules' => '', 'type' => 'id'),
                array('field' =>  'MorbusCrazyPerson_firstDT'            ,'label' => 'Дата обращения к психиатру (наркологу) впервые в жизни'     ,'rules' => '', 'type' => 'date'),
                array('field' =>  'InvalidGroupType_id'                  ,'label' => 'Инвалидность по общему заболеванию'                         ,'rules' => '', 'type' => 'id'),
                array('field' =>  'MorbusCrazyPerson_IsWowInvalid'       ,'label' => 'Инвалид ВОВ'                                                ,'rules' => '', 'type' => 'id'),
                array('field' =>  'MorbusCrazyPerson_IsWowMember'        ,'label' => 'Участник ВОВ'                                               ,'rules' => '', 'type' => 'id'),
                array('field' =>  'CrazyEducationType_id'                ,'label' => 'Образование'                                                ,'rules' => '', 'type' => 'id'),
                array('field' =>  'MorbusCrazyPerson_CompleteClassCount' ,'label' => 'Число законченных классов среднеобразовательного учреждения','rules' => '', 'type' => 'id'),
                array('field' =>  'MorbusCrazyPerson_IsEducation'        ,'label' => 'Учится'                                                     ,'rules' => '', 'type' => 'id'),
                array('field' =>  'CrazySourceLivelihoodType_id'         ,'label' => 'Источник средств существования'                             ,'rules' => '', 'type' => 'id'),
                array('field' =>  'CrazyResideType_id'                   ,'label' => 'Проживает'                                                  ,'rules' => '', 'type' => 'id'),
                array('field' =>  'CrazyResideConditionsType_id'         ,'label' => 'Условия проживания'                                         ,'rules' => '', 'type' => 'id'),
                //гриды
                array('field' =>  'MorbusCrazyForceTreat','label' => 'Принудительное лечение','rules' => '', 'type' => 'string'),
                array('field' =>  'MorbusCrazyBaseDrugStart'    ,'label' => 'Возраст начала употребления психоактивных средств','rules' => '', 'type' => 'string'),
                array('field' =>  'MorbusCrazyDrug'      ,'label' => 'Употребление психоактивных веществ на момент госпитализации','rules' => '', 'type' => 'string'),
            ),
        );
    }

	/**
	 * @return bool
	 */
    function save()
    {
        $data = $this->ProcessInputData('save', true);
        if ($data) {
            $this->dbmodel->assign($data);
            $response = $this->dbmodel->save();
            $this->ProcessModelSave($response, true, true)->ReturnData();
            return true;
        } else {
            return false;
        }
    }

	/**
	 * @return bool
	 */
    function load() {
   		$data = $this->ProcessInputData('load', true);
   		if ($data) {
            $response = $this->dbmodel->load($data['Morbus_id'], $data['Evn_id']);
   			$this->ProcessModelList(array($response), true, true)->formatDatetimeFields()->ReturnData();
   			return true;
   		} else {
   			return false;
   		}
   	}

	/**
	 * @return mixed
	 */
    function test()
    {
        return $this->dbmodel->test();
    }

}