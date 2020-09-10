<?php
defined('BASEPATH') or die('No direct script access allowed');
/**
 * BSK_Register_User - контроллер для БСК (Башкирия)
 *  пользовательская часть
 *
 *
 * @package			BSK
 * @author			Васинский Игорь 
 * @version			01.12.2014
 */

class BSK_Register_User extends swController
{
    var $model = "BSK_Register_User_model";
    
    
    /**
     * comment
     */
    function __construct()
    {
        $this->result = array();
        $this->start  = true;
        
        parent::__construct();
        
        
        
        $this->load->database();
        $this->load->model($this->model, 'dbmodel');
        
        $this->inputRules = array(
            'getCompare' => array(
                array(
                    'field' => 'Person_id',
                    'label' => '',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'MorbusType_id',
                    'label' => '',
                    'rules' => '',
                    'type' => 'id'
                )
            ),
            'getDrugs' => array(
                array(
                    'field' => 'Person_id',
                    'label' => '',
                    'rules' => '',
                    'type' => 'id'
                )
            ),
            'getRecomendationByDate' => array(
                array(
                    'field' => 'MorbusType_id',
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
                    'field' => 'Sex_id',
                    'label' => '',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'BSKRegistry_setDate',
                    'label' => '',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'BSKObservRecomendationType_id',
                    'label' => '',
                    'rules' => '',
                    'type' => 'id'
                )
            ),
            'getTreeDatesRecomendations' => array(
                array(
                    'field' => 'MorbusType_id',
                    'label' => '',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Person_id',
                    'label' => '',
                    'rules' => '',
                    'type' => 'id'
                )
            ),
            'getInfoForPacientOnBSKRegistry' => array(
                array(
                    'field' => 'Person_id',
                    'label' => '',
                    'rules' => '',
                    'type' => 'id'
                )
            ),            
            /* 
            'getRecomendationsOnDate'=>array(
            array(
            'field' => 'MorbusType_id',
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
            ),
            */
            'getComboUnits' => array(
                array(
                    'field' => 'BSKObservElement_id',
                    'rules' => '',
                    'type' => 'id'
                )
            ),
            
            'updateRegistry' => array(
                array(
                    'field' => 'BSKRegistryData',
                    'label' => '',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'BSKRegistry_id',
                    'label' => '',
                    'rules' => '',
                    'type' => 'id'
                )
            ),
            'checkRegisterDate' => array(
                array(
                    'field' => 'MorbusType_id',
                    'label' => '',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Person_id',
                    'label' => '',
                    'rules' => '',
                    'type' => 'id'
                )
            ),
            'getLastVizitRegistry' => array(
                array(
                    'field' => 'BSKRegistry_id',
                    'label' => '',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'MorbusType_id',
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
                    'field' => 'setDate',
                    'label' => '',
                    'rules' => '',
                    'type' => 'string'
                )
            ),
            'saveRegistry' => array(
                array(
                    'field' => 'RegistryData',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'riskGroup',
                    'rules' => '',
                    'type' => 'string'
                )
            ),
            'getRegistryDates' => array(
                array(
                    'field' => 'MorbusType_id',
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
                    'field' => 'setDate',
                    'label' => '',
                    'rules' => '',
                    'type' => 'string'
                )
            ),
            'preSaveRegistryData' => array(
                array(
                    'field' => 'BSKRegistry_id',
                    'label' => '',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'BSKObject_id',
                    'label' => '',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'PersonData',
                    'label' => '',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'ListAnswers',
                    'label' => '',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'questions_ids',
                    'label' => '',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'riskGroup',
                    'label' => '',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'MorbusType_id',
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
                    'field' => 'setDate',
                    'label' => '',
                    'rules' => '',
                    'type' => 'string'
                )
            ),
            'havePrivilege' => array(
                array(
                    'field' => 'Person_id',
                    'label' => 'Person_id',
                    'rules' => '',
                    'type' => 'id'
                )
            ),
            'checkDiabetes' => array(
                array(
                    'field' => 'Person_id',
                    'label' => 'Person_id',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Evn_insDT',
                    'label' => 'Evn_insDT',
                    'rules' => '',
                    'type' => 'string'
                )                
            ),
            'checkDisease' => array(
                array(
                    'field' => 'Person_id',
                    'label' => 'Person_id',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Evn_insDT',
                    'label' => 'Evn_insDT',
                    'rules' => '',
                    'type' => 'string'
                )  
            ),
            'checkGypofunction' => array(
                array(
                    'field' => 'Person_id',
                    'label' => 'Person_id',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Evn_insDT',
                    'label' => 'Evn_insDT',
                    'rules' => '',
                    'type' => 'string'
                )  
            ),
            'checkAutoimmune' => array(
                array(
                    'field' => 'Person_id',
                    'label' => 'Person_id',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Evn_insDT',
                    'label' => 'Evn_insDT',
                    'rules' => '',
                    'type' => 'string'
                )  
            ),
            'checkFattyLiver' => array(
                array(
                    'field' => 'Person_id',
                    'label' => 'Person_id',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Evn_insDT',
                    'label' => 'Evn_insDT',
                    'rules' => '',
                    'type' => 'string'
                )  
            ),
            'checkStonesInBubble' => array(
                array(
                    'field' => 'Person_id',
                    'label' => 'Person_id',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Evn_insDT',
                    'label' => 'Evn_insDT',
                    'rules' => '',
                    'type' => 'string'
                )  
            ),
            'checkSnoring' => array(
                array(
                    'field' => 'Person_id',
                    'label' => 'Person_id',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Evn_insDT',
                    'label' => 'Evn_insDT',
                    'rules' => '',
                    'type' => 'string'
                )  
            ),
            'checkDysfunction' => array(
                array(
                    'field' => 'Person_id',
                    'label' => 'Person_id',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Evn_insDT',
                    'label' => 'Evn_insDT',
                    'rules' => '',
                    'type' => 'string'
                )  
            ),
            'checkBadHear' => array(
                array(
                    'field' => 'Person_id',
                    'label' => 'Person_id',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Evn_insDT',
                    'label' => 'Evn_insDT',
                    'rules' => '',
                    'type' => 'string'
                )  
            ),
            'checkPolycystic' => array(
                array(
                    'field' => 'Person_id',
                    'label' => 'Person_id',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Evn_insDT',
                    'label' => 'Evn_insDT',
                    'rules' => '',
                    'type' => 'string'
                )  
            ),
            'checkGout' => array(
                array(
                    'field' => 'Person_id',
                    'label' => 'Person_id',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Evn_insDT',
                    'label' => 'Evn_insDT',
                    'rules' => '',
                    'type' => 'string'
                ) 
            ),
            'checkLipodystrophy' => array(
                array(
                    'field' => 'Person_id',
                    'label' => 'Person_id',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Evn_insDT',
                    'label' => 'Evn_insDT',
                    'rules' => '',
                    'type' => 'string'
                )  
            ),
            'checkGlycogen' => array(
                array(
                    'field' => 'Person_id',
                    'label' => 'Person_id',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Evn_insDT',
                    'label' => 'Evn_insDT',
                    'rules' => '',
                    'type' => 'string'
                )  
            ),
            'addRegistryData' => array(
                array(
                    'field' => 'Person_id',
                    'label' => 'Person_id',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'BSKObject_id',
                    'label' => 'BSKObject_id',
                    'rules' => '',
                    'type' => 'id'
                )
            ),
            'getAnketsData' => array(
                array(
                    'field' => 'BSKObject_id',
                    'label' => 'BSKObject_id',
                    'rules' => '',
                    'type' => 'id'
                )
            ),
            'getListBSKObjects' => array(),
            'getBSKObjects' => array(),
            'getListObjectsCurrentUser' => array(
                array(
                    'field' => 'Person_id',
                    'label' => 'Person_id',
                    'rules' => '',
                    'type' => 'id'
                )
            ),
            'loadPersonData' => array(
                array(
                    'field' => 'Person_id',
                    'label' => 'Person_id',
                    'rules' => '',
                    'type' => 'id'
                )
            ),
            'checkPersonInRegister' => array(
                array(
                    'field' => 'Person_id',
                    'label' => 'Person_id',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'MorbusType_id',
                    'label' => 'MorbusType_id',
                    'rules' => '',
                    'type' => 'id'
                )
            ),
            'checkPersonInRegisterforEMK' => array(
                array(
                    'field' => 'Person_id',
                    'label' => 'Person_id',
                    'rules' => '',
                    'type' => 'id'
                )
            ),
            'saveInPersonRegister' => array(
                array(
                    'field' => 'PersonRegister_id',
                    'label' => 'PersonRegister_id',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Mode',
                    'label' => 'Mode',
                    'rules' => 'trim',
                    'type' => 'string'
                ),
                array(
                    'field' => 'Person_id',
                    'label' => 'Человек',
                    'rules' => 'required',
                    'type' => 'id'
                ),
                array(
                    'field' => 'MorbusType_id',
                    'label' => 'Тип регистра',
                    'rules' => 'required',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Morbus_id',
                    'label' => 'Morbus_id',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Diag_id',
                    'label' => 'Диагноз',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'PersonRegister_Code',
                    'label' => 'Код записи',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'PersonRegister_setDate',
                    'label' => 'PersonRegister_setDate',
                    'rules' => 'required',
                    'type' => 'date'
                ),
                array(
                    'field' => 'PersonRegister_disDate',
                    'label' => 'PersonRegister_disDate',
                    'rules' => '',
                    'type' => 'date'
                ),
                array(
                    'field' => 'PersonRegisterOutCause_id',
                    'label' => 'Причина исключения из регистра',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'MedPersonal_iid',
                    'label' => 'Добавил человека в регистр - врач',
                    'rules' => 'required',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Lpu_iid',
                    'label' => 'Добавил человека в регистр - ЛПУ',
                    'rules' => 'required',
                    'type' => 'id'
                ),
                array(
                    'field' => 'MedPersonal_did',
                    'label' => 'Кто исключил человека из регистра - врач',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Lpu_did',
                    'label' => 'Кто исключил человека из регистра - ЛПУ',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'EvnNotifyBase_id',
                    'label' => 'EvnNotifyBase_id',
                    'rules' => '',
                    'type' => 'id'
                )
            ),
            'getListEvents'=>array(
                array(
                    'field' => 'MorbusType_id',
                    'label' => 'Предмет наблюдения',
                    'rules' => '',
                    'type' => 'id'
                ),            
                array(
                    'field' => 'Person_id',
                    'label' => 'Пациент',
                    'rules' => '',
                    'type' => 'id'
                )                             
            ),
            'addEvent'=>array(
                array(
                    'field' => 'BSKEvents_Type',
                    'label' => 'Тип события', //1-Диагноз 2-Услуга
                    'rules' => 'required',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Person_id',
                    'label' => 'Пациент',
                    'rules' => 'required',
                    'type' => 'id'
                ),
                array(
                    'field' => 'BSKEvents_setDT',
                    'label' => 'Дата события',
                    'rules' => 'required',
                    'type' => 'string'
                ),   
                array(
                    'field' => 'BSKEvents_Code',
                    'label' => 'Код',
                    'rules' => 'required',
                    'type' => 'string'
                ),  
                array(
                    'field' => 'BSKEvents_Name',
                    'label' => 'Наименование собятия',
                    'rules' => 'required',
                    'type' => 'string'
                ), 
                array(
                    'field' => 'BSKEvents_Description',
                    'label' => 'Примечание',
                    'rules' => '',
                    'type' => 'string'
                ),                                                                            
            ),
            'saveEvent'=>array(
                array(
                    'field' => 'BSKEvents_Type',
                    'label' => 'Тип события', //1-Диагноз 2-Услуга
                    'rules' => 'required',
                    'type' => 'id'
                ),
                array(
                    'field' => 'BSKEvents_id',
                    'label' => 'Событие',
                    'rules' => 'required',
                    'type' => 'id'
                ),
                array(
                    'field' => 'BSKEvents_setDT',
                    'label' => 'Дата события',
                    'rules' => 'required',
                    'type' => 'string'
                ),   
                array(
                    'field' => 'BSKEvents_Code',
                    'label' => 'Код',
                    'rules' => '',
                    'type' => 'string'
                ),  
                array(
                    'field' => 'BSKEvents_Name',
                    'label' => 'Наименование собятия',
                    'rules' => '',
                    'type' => 'string'
                ), 
                array(
                    'field' => 'BSKEvents_Description',
                    'label' => 'Примечание',
                    'rules' => '',
                    'type' => 'string'
                ),                                                                            
            ),
            'deleteEvent'=>array(
                array(
                    'field' => 'BSKEvents_id',
                    'label' => 'Событие',
                    'rules' => 'required',
                    'type' => 'id'
                )                                                                           
            ),
            'getPersonInfo'=>array(
                array(
                    'field' => 'Person_id',
                    'label' => 'Человек',
                    'rules' => 'required',
                    'type' => 'id'
                ),            
            ),
            /**
             * Легочная гипертензия
             */
            /**
             *  ВИЧ
             */  
            'checkHIV' => array(
                array(
                    'field' => 'Person_id',
                    'label' => 'Person_id',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Evn_insDT',
                    'label' => 'Evn_insDT',
                    'rules' => '',
                    'type' => 'string'
                )                
            ),   
            /**
             *  Портальная гипертензия
             */  
            'checkPortalHypertension' => array(
                array(
                    'field' => 'Person_id',
                    'label' => 'Person_id',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Evn_insDT',
                    'label' => 'Evn_insDT',
                    'rules' => '',
                    'type' => 'string'
                )                
            ),     
            /**
             *  Патология легких
             */  
            'checkLungPathology' => array(
                array(
                    'field' => 'Person_id',
                    'label' => 'Person_id',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Evn_insDT',
                    'label' => 'Evn_insDT',
                    'rules' => '',
                    'type' => 'string'
                )                
            ),  
            /**
             *  Пороки сердца
             */  
            'checkHeartDefects' => array(
                array(
                    'field' => 'Person_id',
                    'label' => 'Person_id',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Evn_insDT',
                    'label' => 'Evn_insDT',
                    'rules' => '',
                    'type' => 'string'
                )                
            ),       
            /**
             *  Заболевания соединительной ткани
             */  
            'checkTissueDiseases' => array(
                array(
                    'field' => 'Person_id',
                    'label' => 'Person_id',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Evn_insDT',
                    'label' => 'Evn_insDT',
                    'rules' => '',
                    'type' => 'string'
                )                
            ),    
            /**
             *  Синдром абструктивного апноэ сна
             */  
            'checkSnoringDiag' => array(
                array(
                    'field' => 'Person_id',
                    'label' => 'Person_id',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Evn_insDT',
                    'label' => 'Evn_insDT',
                    'rules' => '',
                    'type' => 'string'
                )                
            ), 
            /**
             *  Саркоидоз
             */  
            'checkSarcoidosis' => array(
                array(
                    'field' => 'Person_id',
                    'label' => 'Person_id',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Evn_insDT',
                    'label' => 'Evn_insDT',
                    'rules' => '',
                    'type' => 'string'
                )                
            ),  
            /**
             *  Гистиоцитоз
             */  
            'checkHistiocytosis' => array(
                array(
                    'field' => 'Person_id',
                    'label' => 'Person_id',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Evn_insDT',
                    'label' => 'Evn_insDT',
                    'rules' => '',
                    'type' => 'string'
                )                
            ), 
            /**
             *  Шистосомоз
             */  
            'checkSchistosomiasis' => array(
                array(
                    'field' => 'Person_id',
                    'label' => 'Person_id',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Evn_insDT',
                    'label' => 'Evn_insDT',
                    'rules' => '',
                    'type' => 'string'
                )                
            ),    
            /**
             *  Диабет с диагнозом
             */  
            'checkDiabetesDiag' => array(
                array(
                    'field' => 'Person_id',
                    'label' => 'Person_id',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Evn_insDT',
                    'label' => 'Evn_insDT',
                    'rules' => '',
                    'type' => 'string'
                )                
            ),   
            /**
             *  ИБС с диагнозом
             */  
            'checkIBS' => array(
                array(
                    'field' => 'Person_id',
                    'label' => 'Person_id',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Evn_insDT',
                    'label' => 'Evn_insDT',
                    'rules' => '',
                    'type' => 'string'
                )                
            ),   
            /**
             *  Цереброваскулярная болезнь с диагнозом
             */  
            'checkCerebrovascular' => array(
                array(
                    'field' => 'Person_id',
                    'label' => 'Person_id',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Evn_insDT',
                    'label' => 'Evn_insDT',
                    'rules' => '',
                    'type' => 'string'
                )                
            ),   
            /**
             *  Хроническая болезнь почек с диагнозом
             */  
            'checkDiseaseDiag' => array(
                array(
                    'field' => 'Person_id',
                    'label' => 'Person_id',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Evn_insDT',
                    'label' => 'Evn_insDT',
                    'rules' => '',
                    'type' => 'string'
                )                
            ),                                                                                                                                         
             /**
              * конец
              */
             /**
              * Результаты ЭКГ - чтение справичника https://redmine.swan.perm.ru/issues/79693
              */
            'getReferenceECGResult'=>array(
                array(
                    'field' => 'KLrgn_id',
                    'label' => 'Код региона по OMSSprTerr',
                    'rules' => '',
                    'type' => 'int'
                )             
            ),  
            /**
             *  Список абсолютных и относительных противопоказаний для проведения ТЛТ
             */
            'getContraindicationsTLT'=>array(
                array(
                    'field' => 'BSKObservRecomendationType_id',
                    'label' => 'Тип противопоказаний', //3-абсолютные 4-относительные
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'Person_id',
                    'label' => 'Person_id',
                    'rules' => '',
                    'type' => 'int'
                )                                 
            ),
            'saveKvsInOKS' => array(
                array(
                    'field' => 'MorbusType_id',
                    'label' => 'Тип заболевания',
                    'rules' => '',
                    'type' => 'int'
                ),  
                array(
                    'field' => 'Diag_id',
                    'label' => 'Код диагноза',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'Registry_method',
                    'label' => 'Метод',
                    'rules' => 'trim',
                    'type' => 'string'
                ),
                array(
                    'field' => 'Person_id',
                    'label' => 'Person_id',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'EvnPS_NumCard',
                    'label' => '№ медицинской карты',
                    'rules' => '',
                    'type' => 'string'
                ), 
                 array(
                    'field' => 'PainDT',
                    'label' => 'Время начала болевых симптомов',
                    'rules' => '',
                    'type' => 'string',
                ),
                 array(
                    'field' => 'ECGDT',
                    'label' => 'Время проведения ЭКГ',
                    'rules' => '',
                    'type' => 'string',
                ),
                array(
                    'field' => 'EcgUsluga_id',
                    'label' => 'Идентификатор услуги экг',
                    'rules' => '',
                    'type' => 'string',
                ),
                 array(
                    'field' => 'ResultECG',
                    'label' => 'Результат ЭКГ',
                    'rules' => '',
                    'type' => 'string',
                ),  
                 array(
                    'field' => 'TLTDT',
                    'label' => 'Время проведения ТЛТ',
                    'rules' => '',
                    'type' => 'string',
                ),
                 array(
                    'field' => 'LpuDT',
                    'label' => 'Время прибытия в медицинскую организацию',
                    'rules' => '',
                    'type' => 'string',
                ),
                array(
                    'field' => 'DiagOKS',
                    'label' => 'код+диагноз',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'MOHospital',
                    'label' => 'МО госпитализации',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'ZonaCHKV',
                    'label' => 'Зона ответственности проведения ЧКВ',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'TimeFromEnterToChkv',
                    'label' => 'Время от начала болевого синдрома до ЧКВ, мин',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'LeaveType_Name',
                    'label' => 'Исход госпитализации',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'CmpCallCard_id',
                    'label' => 'Карта вызова',
                    'rules' => '',
                    'type' => 'string'
                ),
				array(
					'field' => 'diagDir',
					'label' => 'Диагноз направившего учреждения',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'diagPriem',
					'label' => 'Диагноз приемного отделения',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'LpuSection',
					'label' => 'Отделение госпитализации',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'KAGDT',
					'label' => 'Дата/время проведения КАГ',
					'rules' => '',
					'type' => 'string'
				)
            ),
             /**
              * Получение Person_id по CmpCallCard_id
              */
            'getPerson_id'=>array(
                array(
                    'field' => 'CmpCallCard_id',
                    'label' => 'id Карты вызова СМП',
                    'rules' => '',
                    'type' => 'int'
                )             
            ), 
            /**
             *  Рекомендации по лечению пациентов с ОКС
             */                            
             'getRecomendationOKS'=>array(
                array(
                    'field' => 'BSKObservRecomendation_id',
                    'label' => 'id рекомендации',
                    'rules' => '',
                    'type' => 'int'
                )             
            ),  
            /**
             *  Метод получения Lpu_id по улицы и номеру дома - для Уфы
             */            
            'getLpu_id'=>array(
                array(
                    'field' => 'KLStreet_id',
                    'label' => 'Улица',
                    'rules' => '',
                    'type' => 'string'
                ),  
                array(
                    'field' => 'LpuRegionStreet_HouseSet',
                    'label' => 'номер дома',
                    'rules' => '',
                    'type' => 'string'
                ),                                 
            ),  
            /**
             * Получение наименований МО для зоны ответственности при ОКС
             */           
             'getResponsibilityMOZone'=>array(
                array(
                    'field' => 'KLStreet_id',
                    'label' => 'Улица',
                    'rules' => '',
                    'type' => 'id'
                ),  
                array(
                    'field' => 'LpuRegionStreet_HouseSet',
                    'label' => 'номер дома',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'KLArea_id',
                    'label' => 'Город',
                    'rules' => '',
                    'type' => 'int'
                ),   
                array(
                    'field' => 'KLSubRgn_id',
                    'label' => 'район',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'EvnEKG_rezEKG',
                    'label' => 'Результат ЭКГ',
                    'rules' => '',
                    'type' => 'string'
                ),                                                       
             ),
             'getDolgnost'=>array(
                array(
                    'field' => 'MedPersonal_id',
                    'label' => 'MedPersonal_id',
                    'rules' => '',
                    'type' => 'id'
                ),
             ),
             'checkInOKS'=>array(
                array(
                    'field' => 'Person_id',
                    'label' => 'Person_id',
                    'rules' => '',
                    'type' => 'id'
                ),  
                array(
                    'field' => 'BSKRegistry_setDate',
                    'label' => 'BSKRegistry_setDate',
                    'rules' => '',
                    'type' => 'string'
                )
             ),   
             'getOksId'=>array(
                array(
                    'field' => 'Person_id',
                    'label' => 'Person_id',
                    'rules' => '',
                    'type' => 'int'
                ),
                array(
                    'field' => 'EvnPS_NumCard',
                    'label' => 'EvnPS_NumCard',
                    'rules' => '',
                    'type' => 'string'
                )
             ),
             /**
             *  Данные из регистра БСК на конкретную карту вызова СМП
             */                 
             'getOKSdata'=>array(
                array(
                    'field' => 'CmpCallCard_id',
                    'label' => 'CmpCallCard_id',
                    'rules' => '',
                    'type' => 'id'
                )
             ),  
            /**
             *  Список возможных МО для маршрутизации при ОКС
             */
            'getMOforOKS'=>array(
                                
            ),     
            'getLastAnketData' => array(
                array(
                    'field' => 'Person_id',
                    'label' => 'Person_id',
                    'rules' => '',
                    'type' => 'id'
                )     
            ),
            //Данные для ЭМК
            'getDataForEMK' => array(
                array(
                    'field' => 'Person_id',
                    'label' => '',
                    'rules' => '',
                    'type' => 'id'
                )
            ),            
            'setIsBrowsed' => array(
                array(
                    'field' => 'BSKRegistry_id',
                    'label' => '',
                    'rules' => '',
                    'type' => 'int'
                )
            ),
			/**
			*  Основной диагноз из КВС
			*/
			'getDiagFromEvnPS' => array(
				array(
					'field' => 'EvnPS_id',
					'label' => '',
					'rules' => '',
					'type' => 'id'
				)
            ),
            'getListUslugforEvents' => array(
                array(
                    'field' => 'Person_id',
                    'label' => 'Идентификатор пациента',
                    'rules' => '',
                    'type' => 'int'
                )
            ),
            'getListPersonCureHistoryPL' => array(
                array(
                    'field' => 'Person_id',
                    'label' => 'Идентификатор пациента',
                    'rules' => '',
                    'type' => 'int'
                )
            ),
            'getListPersonCureHistoryPS' => array(
                array(
                    'field' => 'Person_id',
                    'label' => 'Идентификатор пациента',
                    'rules' => '',
                    'type' => 'int'
                )
            ),
            'getListPersonCureHistoryDiagSop' => array(
                array(
                    'field' => 'Person_id',
                    'label' => 'Идентификатор пациента',
                    'rules' => '',
                    'type' => 'int'
                )
            ),
            'getListPersonCureHistoryDiagKardio' => array(
                array(
                    'field' => 'Person_id',
                    'label' => 'Идентификатор пациента',
                    'rules' => '',
                    'type' => 'int'
                )
            ),
            'getLabResearch' => array(
                array(
                    'field' => 'Person_id',
                    'label' => 'Идентификатор пациента',
                    'rules' => '',
                    'type' => 'int'
                )
            ),
            'getLabSurveys' => array(
                array(
                    'field' => 'Person_id',
                    'label' => 'Идентификатор пациента',
                    'rules' => '',
                    'type' => 'int'
                )
			),
			'getBSKObjectWithoutAnket' => array(
				array(
					'field' => 'MorbusType_id',
					'label' => 'Предмет наблюдения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Person_id',
					'label' => '',
					'rules' => '',
					'type' => 'id'
				)
			),
			'loadBSKEvnGrid' => array(
				array(
					'field' => 'MorbusType_id',
					'label' => 'Предмет наблюдения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Person_id',
					'label' => '',
					'rules' => '',
					'type' => 'id'
				)
			),
			'getListDispViewData' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Person_id',
					'rules' => '',
					'type' => 'id'
				)
			),
			'checkBSKforScreening'=> array(
				array(
					'field' => 'Person_id',
					'label' => 'Person_id',
					'rules' => '',
					'type' => 'id'
				)
			),
			'getListOperUslug' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Person_id',
					'rules' => '',
					'type' => 'id'
				)
			),
			'getListHospOKS' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Person_id',
					'rules' => '',
					'type' => 'id'
				)
			),
			'savePrognosDiseases'=>array(
				array(
					'field' => 'PrognosOslDiagList',
					'label' => 'Осложнения основного заболевания',
					'rules' => '',
					'type' => 'json_array'
				),
				array(
					'field' => 'BSKDiagPrognos_DescriptDiag',
					'label' => 'Уточнение основного заболевания по МКБ',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Person_id',
					'rules' => '',
					'type' => 'int'
				)
				),
			'loadPrognosDiseases' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Person_id',
					'rules' => '',
					'type' => 'id'
				)
			)
        );
        
    }
    //https://redmine.swan.perm.ru/issues/88153
    /**
     * данные для ЭМК
     */
    function getDataForEMK(){
        $data = $this->ProcessInputData('getDataForEMK', true);
        if ( $data === false ) { return false; }

        $response = $this->dbmodel->getDataForEMK($data);

        $this->ReturnData($response);             
    }
    /**
     * Получение краткой информации о всех ПН пациента в ергистре БСК
     */
    function getInfoForPacientOnBSKRegistry(){
         $data = $this->ProcessInputData('getInfoForPacientOnBSKRegistry', true);
        if ( $data === false ) { return false; }

        $response = $this->dbmodel->getInfoForPacientOnBSKRegistry($data);

        $this->ReturnData($response);          
    }
    
    /**
     * Получение роста, веса, имт с последней анкеты
     */
    function getLastAnketData(){
        $data = $this->ProcessInputData('getLastAnketData', true);
        if ( $data === false ) { return false; }

        $response = $this->dbmodel->getLastAnketData($data);

        $this->ReturnData($response);          
    }
    
    /**
     *  Список возможных МО для маршрутизации при ОКС
     */    
    function getMOforOKS(){
        $data = $this->ProcessInputData('getMOforOKS', true);
        if ( $data === false ) { return false; }

        $response = $this->dbmodel->getMOforOKS($data);

        $this->ReturnData($response);            
    }
        
    /**
     *  Данные из регистра БСК на конкретную карту вызова СМП
     */ 
    function getOKSdata(){
 		$data = $this->ProcessInputData('getOKSdata', true);
		if ( $data === false ) { return false; }

        $response = $this->dbmodel->getOKSdata($data);

        $this->ReturnData($response);            
    }
        
    /**
     *  Проверка наличия данных по ОКС для пациента на дату
     */ 
    function checkInOKS(){
 		$data = $this->ProcessInputData('checkInOKS', true);
		if ( $data === false ) { return false; }

        $response = $this->dbmodel->checkInOKS($data);

        //return $response;
        $this->ReturnData($response);            
    }
    /**
     *  Отличить врача от фельдшера смп
     */ 
    function getDolgnost(){
 		$data = $this->ProcessInputData('getDolgnost', true);
		if ( $data === false ) { return false; }

        $response = $this->dbmodel->getDolgnost($data);

        $this->ReturnData($response);    
    }
    
     /**
      * Получение Person_id по CmpCallCard_id
      */
    function getRecomendationOKS(){
		$data = $this->ProcessInputData('getRecomendationOKS', true);
		if ( $data === false ) { return false; }

        $list = $this->dbmodel->getRecomendationOKS($data);

        $this->ReturnData($list);            
    }
         
    /**
     *  Метод получения Lpu_id по улицы и номеру дома - для Уфы
     */
    function getLpu_id(){
		$data = $this->ProcessInputData('getLpu_id', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->getLpu_id($data);
		$this->ProcessModelList($response, true, true)->ReturnData();           
    }   
       
    /**
     * Получение наименований МО для зоны ответственности при ОКС
     */       
    function getResponsibilityMOZone(){
		$data = $this->ProcessInputData('getResponsibilityMOZone', true);
		if ( $data === false ) { return false; }

		$list = $this->dbmodel->getResponsibilityMOZone($data);

		$this->ReturnData($list);          
    }
    
    /**
     * Получение Person_id по CmpCallCard_id
     */       
    function getPerson_id(){
		$data = $this->ProcessInputData('getPerson_id', true);
		if ( $data === false ) { return false; }

        $response = $this->dbmodel->getPerson_id($data);

        $this->ProcessModelList($response, true, true)->ReturnData();    
        //$this->ReturnData($list);            
    }
            
    /**
     *  Запись в регистр БСК в ПН ОКС с АРМ Админситратора СМП / ... / Подстанции СМП
     */        
    function saveInOKS(){
        $this->inputRules['saveInOKS'] = $this->dbmodel->getInputRules('saveInOKS');
		$data = $this->ProcessInputData('saveInOKS', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->saveInOKS($data);

        $this->ReturnData($response);  
		//$this->ProcessModelSave($response, true, 'Ошибка сохранения ОКС!')->ReturnData();
     }
    
     /**
      * Список абсолютных и относительных противопоказаний для проведения ТЛТ
      */    
    function getContraindicationsTLT(){
		$data = $this->ProcessInputData('getContraindicationsTLT', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->getContraindicationsTLT($data);
		$this->ProcessModelList($response, true, true)->ReturnData();        
    }      
     /**
      * Результаты ЭКГ - чтение справичника https://redmine.swan.perm.ru/issues/79693
      */    
    function getReferenceECGResult(){
		$data = $this->ProcessInputData('getReferenceECGResult', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->getReferenceECGResult($data);
		$this->ProcessModelList($response, true, true)->ReturnData();        
    }  
    /**
     *  Добавление нового события в жизни пациента
     */ 
	function addEvent()
	{
		$data = $this->ProcessInputData('addEvent', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->addEvent($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
    /**
     *  Редактирование события 
     */ 
	function saveEvent()
	{
		$data = $this->ProcessInputData('saveEvent', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->saveEvent($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
    /**
     *  Удаления события 
     */ 
	function deleteEvent()
	{
		$data = $this->ProcessInputData('deleteEvent', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->deleteEvent($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}    
    /**
     *  Информация о событиях, связанных с БСК по пациенту
     */ 
    function getListEvents()
     {
        $data = $this->ProcessInputData('getListEvents', true);
        
        if ($data === false) {
            return false;
        }
        
        $list = $this->dbmodel->getListEvents($data);
        
        $this->ReturnData($list);        
     }   
    
    /**
     * Для вывода на печать лекарственного лечения - необходимы данные о пациенте 
     */  
    function getPersonInfo(){
		$data = $this->ProcessInputData('getPersonInfo', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->getPersonInfo($data);
		return isset($response[0]) ? $response[0] : array();
    }
    /**
     *  Получение сведений о лекарственном лечении
     */
    function getDrugs()
    {
        $data = $this->ProcessInputData('getDrugs', true);
        
        if ($data === false) {
            return false;
        }
        
        $list = $this->dbmodel->getDrugs($data);
        
        //Скрининг
        $drugs = array();
        
        //Легочная гипертензия
        $drugs88 = array();
        
        //Артериальная гипертензия
        $drugs89 = array();

        //ИБС
        $drugs50 = array();
        
        foreach ($list as $k => $v) {
            $id      = $v['BSKObservElement_id'];
            $name    = $v['BSKObservElement_name'];
            $data    = $v['BSKRegistryData_data'];
            $date    = $v['BSKRegistryData_insDT']; //->format('Y-m-d');
            $setDate = $v['BSKRegistry_setDate']; //->format('Y-m-d');
            $unit = $v['BSKUnits_name'];
            switch ($id) {
                case 111:
                case 112:
                    $typeDrugs = 'Статины';
                    break;
                case 116:
                case 117:
                    $typeDrugs = 'Эзетемибы';
                    break;
                case 121:
                case 112:
                    $typeDrugs = 'Фибраты';
                    break;
                case 126:
                case 127:
                    $typeDrugs = 'Секверстанты';
                    break;
                case 131:
                case 132:
                    $typeDrugs = 'Никотиновая килота';
                    break;
                default:
                    $typeDrugs = 'Группа не определена';
            }
            
            //Определение элеменов в строке
            //Приеём на кануне 
            if (in_array($id, array(
                111,
                116,
                121,
                126,
                131,
                113,
                118,
                123,
                128,
                133
            ))) {
                
                $data_array = array();
                if (in_array($id, array(
                    111,
                    116,
                    121,
                    126,
                    131
                ))) {
                    
                    
                    $data_array = array(
                        'Накануне осмотра',
                        $date,
                        $typeDrugs,
                        $data,
                        '-',
                        '-',
                        $setDate
                    );
                    $index      = $k;
                    $drugs[$k]  = $data_array;
                } else if (isset($index)) {
                    $drugs[$index - 1][5] = $data;
                }
                //echo '<pre>' . print_r($drugs[$index], 1) . '</pre>';
            }
            //Текущий приём : группа препарат дозировка
            elseif (in_array($id, array(
                112,
                117,
                122,
                127,
                132,
                115,
                120,
                125,
                130,
                137,
                114,
                119,
                124,
                129,
                136
            ))) {
                $data_array = array();
                
                if (in_array($id, array(
                    112,
                    117,
                    122,
                    127,
                    132
                ))) {
                    $data_array = array(
                        'Текущий осмотр',
                        $date,
                        $typeDrugs,
                        $data,
                        '-',
                        '-',
                        $setDate
                    );
                    $index      = $k;
                    $drugs[$k]  = $data_array;
                }
                //Причина отмены/смены 
                elseif (isset($index) && in_array($id, array(
                    114,
                    119,
                    124,
                    129,
                    136
                ))) {
                    $drugs[$index][5] = $data;
                }
                //Дозировка
                else if (isset($index)) {
                    $drugs[$index][4] = $data;
                    
                }
            }
                
            //Легочная гипертензия
            if(in_array($id, range(185,204))){
                $unit = ($data == '-') ? '' : $unit;
                $drugs88[$id] = array($date,$name, $setDate, $data.' '.$unit);
            
                //echo $id.' : '.$name.' : '.$data.'<br/>';
            }
            
            //Легочная гипертензия
            
            if(in_array($id, range(251,268))){
                $unit = ($data == '-') ? '' : $unit;
                $drugs89[$id] = array($date,$name, $setDate, $data.' '.$unit);
            
                //echo $id.' : '.$name.' : '.$data.'<br/>';
            }        
 
            //ИБС
            if(in_array($id, range(365,382))){
                $unit = ($data == '-') ? '' : $unit;
                $drugs50[$id] = array($date,$name, $setDate, $data.' '.$unit);
            
                //echo $id.' : '.$name.' : '.$data.'<br/>';
            }            
           
        }
        
        //echo '<pre>' . print_r($drugs89, 1) . '</pre>';    
        
        //88 Легочная гипертензия

        foreach($drugs88 as $k=>$v){
            switch($k){
                case 185: 
                     $drugs88[185][4] = isset($drugs88[186]) ? $drugs88[186][3] : '-';
                     unset($drugs88[186]);
                break;
                case 187: 
                    $drugs88[187][4] = isset($drugs88[192]) ? $drugs88[192][3] : '-';
                    unset($drugs88[192]);
                break;
                case 193: 
                    $drugs88[193][4] = isset($drugs88[194]) ? $drugs88[194][3] : '-';
                    unset($drugs88[194]);
                break;
                case 195: 
                    $drugs88[195][4] = isset($drugs88[196]) ? $drugs88[196][3] : '-';
                    unset($drugs88[196]);
                break;   
                case 197: 
                    $drugs88[197][4] = isset($drugs88[198]) ? $drugs88[198][3] : '-';
                    unset($drugs88[198]);
                break;  
                case 199: 
                    $drugs88[199][4] = isset($drugs88[200]) ? $drugs88[200][3] : '-';
                    unset($drugs88[200]);
                break;  
                case 201: 
                    $drugs88[201][4] = isset($drugs88[202]) ? $drugs88[202][3] : '-';
                    unset($drugs88[202]);
                break;   
                case 203: 
                    $drugs88[203][4] = isset($drugs88[204]) ? $drugs88[204][3] : '-';
                    unset($drugs88[204]);
                break;                                                                                                                              
            }
            
        }
  
        //89 Артериальная гипертензия
        foreach($drugs89 as $k=>$v){
            switch($k){
                case 251: 
                     $drugs89[251][4] = isset($drugs89[260]) ? $drugs89[260][3] : '-';
                     unset($drugs89[260]);
                break;
                case 252: 
                    $drugs89[252][4] = isset($drugs89[261]) ? $drugs89[261][3] : '-';
                    unset($drugs89[261]);
                break;
                case 253: 
                    $drugs89[253][4] = isset($drugs89[262]) ? $drugs89[262][3] : '-';
                    unset($drugs89[262]);
                break;
                case 254: 
                    $drugs89[254][4] = isset($drugs89[263]) ? $drugs89[263][3] : '-';
                    unset($drugs89[263]);
                break;   
                case 255: 
                    $drugs89[255][4] = isset($drugs89[264]) ? $drugs89[264][3] : '-';
                    unset($drugs89[264]);
                break;  
                case 256: 
                    $drugs89[256][4] = isset($drugs89[265]) ? $drugs89[265][3] : '-';
                    unset($drugs89[265]);
                break;  
                case 257: 
                    $drugs89[257][4] = isset($drugs89[266]) ? $drugs89[266][3] : '-';
                    unset($drugs89[266]);
                break;   
                case 258: 
                    $drugs89[258][4] = isset($drugs89[267]) ? $drugs89[267][3] : '-';
                    unset($drugs89[267]);
                break;        
                case 259: 
                    $drugs89[259][4] = isset($drugs89[268]) ? $drugs89[268][3] : '-';
                    unset($drugs89[268]);
                break;                                                                                                                                               
            }

        }  

        //50 ИБС
        
        foreach($drugs50 as $k=>$v){
            switch($k){
                case 365: 
                     $drugs50[365][4] = isset($drugs50[366]) ? $drugs50[366][3] : '-';
                     unset($drugs50[366]);
                break;
                case 367: 
                    $drugs50[367][4] = isset($drugs50[368]) ? $drugs50[368][3] : '-';
                    unset($drugs50[368]);
                break;
                case 369: 
                    $drugs50[369][4] = isset($drugs50[370]) ? $drugs50[370][3] : '-';
                    unset($drugs50[370]);
                break;
                case 371: 
                    $drugs50[371][4] = isset($drugs50[372]) ? $drugs50[372][3] : '-';
                    unset($drugs50[372]);
                break;   
                case 373: 
                    $drugs50[373][4] = isset($drugs50[374]) ? $drugs50[374][3] : '-';
                    unset($drugs50[374]);
                break;  
                case 375: 
                    $drugs50[375][4] = isset($drugs50[376]) ? $drugs50[376][3] : '-';
                    unset($drugs50[376]);
                break;  
                case 377: 
                    $drugs50[377][4] = isset($drugs50[378]) ? $drugs50[378][3] : '-';
                    unset($drugs50[378]);
                break;   
                case 379: 
                    $drugs50[379][4] = isset($drugs50[380]) ? $drugs50[380][3] : '-';
                    unset($drugs50[380]);
                break;        
                case 381: 
                    $drugs50[381][4] = isset($drugs50[382]) ? $drugs50[382][3] : '-';
                    unset($drugs50[382]);
                break;                                                                                                                                               
            }        

        }      
        //echo '<pre>' . print_r($drugs50, 1) . '</pre>';
        //echo '<pre>' . print_r($_GET, 1) . '</pre>';
        if(isset($_GET['Person_id'])){
            $personData = $this->getPersonInfo((int)$_GET['Person_id']);
            
            //echo '<pre>' . print_r($personData, 1) . '</pre>';
            
            if(!empty($personData)){
                $personDataString = '<br/><b>Пациент: </b>'.$personData['Person_FIO'].'<br/>'
                                   .'<b>Пол: </b>'.$personData['Sex_Name'].'<br/>' 
                                   .'<b>Дата рождения: </b>'.$personData['Person_BirthDay'].'<p>&nbsp;</p>' 
                                   
                ;
            }
            else{
                $personDataString = 'Ошибка определения данных пациента';    
            }
            
            echo 
                $personDataString
                .(isset($drugs89) ? $this->createDrugTable89('Артериальная гипертензия', $drugs89) : '')  
                .(isset($drugs88) ? $this->createDrugTable88('Лёгочная гипертензия', $drugs88) : '')  
                .(isset($drugs50) ? $this->createDrugTable50('Ишемическая болезнь сердца', $drugs50) : '')      
                .$this->createDrugTable('Скрининг', $drugs);
        }
        else{
            echo json_encode(
                              (isset($drugs89) ? $this->createDrugTable89('Артериальная гипертензия', $drugs89) : '')  
                             .(isset($drugs88) ? $this->createDrugTable88('Лёгочная гипертензия', $drugs88) : '')  
                             .(isset($drugs50) ? $this->createDrugTable50('Ишемическая болезнь сердца', $drugs50) : '')     
                             .$this->createDrugTable('Скрининг', $drugs)
                            
            );            
        }
        //echo '<pre>' . print_r($drugs88,1) . '</pre>';
        //echo '<pre>' . print_r($drugs,1) . '</pre>';
    }
    
    /**
     * Таблица сведений о лекарстванном лечении
     */
    function createDrugTable($pn, $array)
    {
        $style = "border:1px solid gray; padding:11px; font-weight:bold; text-align:center";
        $tds   = "border:1px solid gray; padding:11px;text-align:center";
        $table = "<table width='98%' style='border-collapse:collapse'>
                    <tr>
                     <th style='text-align:left; padding:10px' colspan='7'><h1 style='font-size:16px; margin:4px;'>{$pn}</h1></th> 
                    </tr>
                    <tr>
                     <th style='{$style}'>Дата анкетирования</th>
                     <th style='{$style}'>Дата назначения</th>
                     <th style='{$style}'>Вид приёма</th>
                     <th style='{$style}'>Группа</th>
                     <th style='{$style}'>МНН</th>
                     <th style='{$style}'>Дозировка</th>
                     <th style='{$style}'>Причина отмены/смены</th>
                    </tr>";
        
        foreach ($array as $k => $v) {
            $table .= "<tr>
                        <td width='100px' bgcolor='" . ($v['0'] == 'Накануне осмотра' ? '#F0FFF0' : 'white') . "' style='{$tds}'>{$v[6]}</td> 
                        <td width='100px'  bgcolor='" . ($v['0'] == 'Накануне осмотра' ? '#F0FFF0' : 'white') . "' style='{$tds}'>{$v[1]}</td>
                        <td bgcolor='" . ($v['0'] == 'Накануне осмотра' ? '#F0FFF0' : 'white') . "' style='{$tds}'>{$v[0]}</td>
                        <td bgcolor='" . ($v['0'] == 'Накануне осмотра' ? '#F0FFF0' : 'white') . "' style='{$tds}'>{$v[2]}</td>
                        <td bgcolor='" . ($v['0'] == 'Накануне осмотра' ? '#F0FFF0' : 'white') . "' style='{$tds}'>{$v[3]}</td>
                        <td bgcolor='" . ($v['0'] == 'Накануне осмотра' ? '#F0FFF0' : 'white') . "' style='{$tds}'>{$v[5]}</td>
                        <td bgcolor='" . ($v['0'] == 'Накануне осмотра' ? '#F0FFF0' : 'white') . "' style='{$tds}'>{$v[4]}</td>
                       </tr>";
        }
        
        return $table.'</table><br/>';
        
    }
    
    /**
     * Таблица сведений о лекарстванном лечении по Легочной гипертензии
     */
    function createDrugTable88($pn, $array)
    {
        $style = "border:1px solid gray; padding:11px; font-weight:bold; text-align:center";
        $tds   = "border:1px solid gray; padding:11px;text-align:center";
        $table = "<table width='98%' style='border-collapse:collapse'>
                    <tr>
                     <th style='text-align:left; padding:10px'  colspan='5'><h1 style='font-size:16px; margin:4px;'>{$pn}</h1></th> 
                    </tr>
                    <tr>
                     <th width='100px' style='{$style}'>Дата анкетирования</th>
                     <th width='100px' style='{$style}'>Дата назначения</th>
                     <th style='{$style}'>Группа</th>
                     <th style='{$style}'>МНН</th>
                     <th style='{$style}'>Дозировка</th>
                    </tr>";
        $i =0;
        foreach ($array as $k => $v) {
            $i++;
            $bgcolor = ($i%2!=0) ? '#F0FFF0' : 'white';
            
            $table .= "<tr>
                        <td bgcolor='{$bgcolor}' style='{$tds}'>{$v[0]}</td> 
                        <td bgcolor='{$bgcolor}' style='{$tds}'>{$v[2]}</td>
                        <td bgcolor='{$bgcolor}' style='{$tds}'>{$v[1]}</td>
                        <td bgcolor='{$bgcolor}' style='{$tds}'>{$v[3]}</td>
                        <td bgcolor='{$bgcolor}' style='{$tds}'>{$v[4]}</td>
                       </tr>";
        }
        
        return $table.'</table><br/>';
        
    }        
    
    /**
     * Таблица сведений о лекарстванном лечении по ИБС
     */
    function createDrugTable50($pn, $array)
    { 
        //echo '<per>' . print_r($array, 1) . '</pre>';        
        $style = "border:1px solid gray; padding:11px; font-weight:bold; text-align:center";
        $tds   = "border:1px solid gray; padding:11px;text-align:center";
        $table = "<table width='98%' style='border-collapse:collapse'>
                    <tr>
                     <th style='text-align:left; padding:10px'  colspan='5'><h1 style='font-size:16px; margin:4px;'>{$pn}</h1></th> 
                    </tr>
                    <tr>
                     <th width='100px' style='{$style}'>Дата анкетирования</th>
                     <th width='100px' style='{$style}'>Дата назначения</th>
                     <th style='{$style}'>Группа</th>
                     <th style='{$style}'>МНН</th>
                     <th style='{$style}'>Дозировка</th>
                    </tr>";
        $i =0;
        foreach ($array as $k => $v) {
            $i++;
            $bgcolor = ($i%2!=0) ? '#F0FFF0' : 'white';
            
            $table .= "<tr>
                        <td bgcolor='{$bgcolor}' style='{$tds}'>{$v[0]}</td> 
                        <td bgcolor='{$bgcolor}' style='{$tds}'>{$v[2]}</td>
                        <td bgcolor='{$bgcolor}' style='{$tds}'>{$v[1]}</td>
                        <td bgcolor='{$bgcolor}' style='{$tds}'>{$v[3]}</td>
                        <td bgcolor='{$bgcolor}' style='{$tds}'>{$v[4]}</td>
                       </tr>";
        }
        
        return $table.'</table><br/>';
        
    }    
    
    /**
     * Таблица сведений о лекарстванном лечении по Артериальной гипертензии
     */
    function createDrugTable89($pn, $array)
    {
        $style = "border:1px solid gray; padding:11px; font-weight:bold; text-align:center";
        $tds   = "border:1px solid gray; padding:11px;text-align:center";
        $table = "<table width='98%' style='border-collapse:collapse'>
                    <tr>
                     <th style='text-align:left; padding:10px' colspan='5'><h1 style='font-size:16px; margin:4px;'>{$pn}</h1></th> 
                    </tr>
                    <tr>
                     <th width='100px' style='{$style}'>Дата анкетирования</th>
                     <th width='100px' style='{$style}'>Дата назначения</th>
                     <th style='{$style}'>Группа</th>
                     <th style='{$style}'>МНН</th>
                     <th style='{$style}'>Дозировка</th>
                    </tr>";
        $i =0;
        foreach ($array as $k => $v) {
            $i++;
            $bgcolor = ($i%2!=0) ? '#F0FFF0' : 'white';
            
            $table .= "<tr>
                        <td bgcolor='{$bgcolor}' style='{$tds}'>{$v[0]}</td> 
                        <td bgcolor='{$bgcolor}' style='{$tds}'>{$v[2]}</td>
                        <td bgcolor='{$bgcolor}' style='{$tds}'>{$v[1]}</td>
                        <td bgcolor='{$bgcolor}' style='{$tds}'>{$v[3]}</td>
                        <td bgcolor='{$bgcolor}' style='{$tds}'>{$v[4]}</td>
                       </tr>";
        }
        
        return $table.'</table><br/>';
        
    }        
    /**
     * Получение рекомендаций по регистрам
     */
    function getRecomendationByDate()
    {
        $data = $this->ProcessInputData('getRecomendationByDate', true);
        
        if ($data === false) {
            return false;
        }
        
        $list = $this->dbmodel->getRecomendationByDate($data);
        
        $this->ReturnData($list);
    }
    
    /**
     * Получение данных регистра для сравнения
     */ 
    function getCompare()
    {
        $data = $this->ProcessInputData('getCompare', true);
        
        if ($data === false) {
            return false;
        }
        
        $list = $this->dbmodel->getCompare($data);
        
        $this->ReturnData($list);
    }
    
    /**
     *  Построение древа рекомендаций по датам, относительно предмета наблюдения
     */
    function getTreeDatesRecomendations()
    {
        $data = $this->ProcessInputData('getTreeDatesRecomendations', true);
        
        if ($data === false) {
            return false;
        }
        
        $list = $this->dbmodel->getTreeDatesRecomendations($data);
        
        //echo '<pre>' . print_r($list, 1) . '</pre>';
        
        $dates = array();
        
        foreach ($list as $k => $v) {
            $dates[] = $v;
        }
        
        $this->ReturnData($dates);
    }
    
    
    /**
     * Получение сведений об инвалидности пациента
     */
    function havePrivilege()
    {
        $data = $this->ProcessInputData('havePrivilege', true);
        
        if ($data === false) {
            return false;
        }
        
        $list = $this->dbmodel->havePrivilege($data);
        
        $this->ReturnData($list);
    }
    
    /**
     *  Проверка наличия регистра по заданному предмету наблюдения, дате и пациенту
     */
    function checkRegisterDate()
    {
        $data = $this->ProcessInputData('checkRegisterDate', true);
        
        if ($data === false) {
            return false;
        }
        
        $result = $this->dbmodel->checkRegisterDate($data);
        
        $this->ReturnData($result);
    }
    
    /**
     *  Обновление данных регистра
     */
    function updateRegistry($registryData)
    {
        $data = $this->ProcessInputData('updateRegistry', true);
        
        if ($data === false) {
            return false;
        }
        
        $result = $this->dbmodel->updateRegistry($data, $registryData);
        
        $this->ReturnData($result);
    }
    
    /**
     *  Получение последнего свежего регистра по данному ПН для пациента
     */
    function getLastVizitRegistry()
    {
        $data = $this->ProcessInputData('getLastVizitRegistry', true);
        
        if ($data === false) {
            return false;
        }
        
        $result = $this->dbmodel->getLastVizitRegistry($data);

		if ($data['MorbusType_id'] == 19) {
			$resultOKS = $result;
			$result = array();
			$groups = array();
			foreach ($resultOKS as $k => $v) {
				if (isset($v['Person_id'])) {
					$result['BSKRegistry_setDateFormat'] = $v['BSKRegistry_setDateFormat'];
					$result['BSKRegistry_setDate']       = $v['BSKRegistry_setDate'];
					$result['BSKRegistry_id']            = $v['BSKRegistry_id'];
					$result['BSKRegistry_isBrowsed']     = $v['BSKRegistry_isBrowsed'];
					if($v['BSKObservElementGroup_id'] != 29) {
						$groups[$v['num']] = array(
							'id' => $v['BSKObservElementGroup_id'],
							'group' => $v['BSKObservElementGroup_name']
						);
					}
				}
			}
			$result['questions'] = $resultOKS;
			$result['groups'] = $groups;

		}

        $this->ReturnData($result);
    }
    
    /**
     *  Получение сведений о регистрах по заданному MorbusType_id
     */
    function getRegistryDates()
    {
        $data = $this->ProcessInputData('getRegistryDates', true);
        
        if ($data === false) {
            return false;
        }
        
        $result = $this->dbmodel->getRegistryDates($data);
        
        $this->ReturnData($result);
    }
    
    /**
     *  Предварительная подготовка данных при добавление регистра
     */
    function preSaveRegistryData()
    {
        $data = $this->ProcessInputData('preSaveRegistryData', true);
        
        if ($data === false) {
            return false;
        }
        
        $preResult = $this->dbmodel->preSaveRegistryData($data);
        
        $preSavedData = $this->CalculateSigns($preResult);
        
        //echo '<pre>' . print_r($preResult, 1) . '</pre>';
        //exit;
    }
    
    /**
     *  Подсчёт признаков влияния на группу
     */
    function CalculateSigns($preResult)
    {
        //echo '<pre>' . print_r($preResult['ListAnswers']) . '</pre>';
        
        //Сбор признаков
        $signs = array();
        $signs = array(
            'ПП' => 0,
            'КЗ' => 0,
            'КФ' => 0,
            'PN' => 0,
            '' => 0
        );
        
        
        $answersDB      = $preResult['answersDB'];
        $ListAnswers    = array();
        $PersonData     = $preResult['PersonData'];
        $MorbusType_id  = $preResult['MorbusType_id'];
        $Person_id      = $preResult['Person_id'];
        $setDate        = $preResult['setDate'];
        $BSKRegistry_id = $preResult['BSKRegistry_id'];
        
        foreach ($preResult['ListAnswers'] as $k => $v) {
            $ListAnswers[$v[0]] = array(
                'value' => $v[1],
                'unit' => $v[2],
                'question' => $v[3],
                'format_id' => $v[4],
                'BSKRegistryData_id' => isset($v[5]) ? $v[5] : null
                //'BSKRegistryData_id'=>$v[5]
            );
        }
        //        echo 'ListAnswers11=';
        //        echo '<pre>' . print_r($preResult['ListAnswers'], 1) . '</pre>';
        //        
        //        echo 'ListAnswers=';
        //        echo '<pre>' . print_r($ListAnswers, 1) . '</pre>';
        
        
        $age    = $PersonData->age;
        $sex_id = $PersonData->Sex_id;
        
        //echo '<hr/><pre>' . print_r($answersDB, 1) . '<pre><hr/>';
        
        $result = array();
        
        //        echo '$answersDB=';
        //        echo '<pre>' . print_r($answersDB, 1) . '</pre>';
        
        foreach ($answersDB as $k => $v) {
            $value     = $ListAnswers[$k]['value'];
            $unit      = $ListAnswers[$k]['unit'];
            $question  = $ListAnswers[$k]['question'];
            $format_id = $v[0]['BSKObservElementFormat_id'];
            
            
            
            if (isset($ListAnswers[$k])) {
                //echo '<pre>' . print_r($ListAnswers[$k], 1) . '</p>';
                //Проверяем комбобоксы
                if ($format_id == 1) {
                    //Найдём наш ответ из нескольких
                    foreach ($v as $key => $val) {
                        //Поймали наш ответ
                        if ($value == $val['BSKObservElementValues_data']) {
                            $signs[$val['BSKObservElementValues_sign']] += 1;
                            
                            
                            if (!in_array($k, array(
                                46,
                                47,
                                48
                            ))) {
                                $data = array(
                                    'sign' => $val['BSKObservElementValues_sign'],
                                    'BSKObservElementValues_id' => $val['BSKObservElementValues_id'],
                                    'BSKObservElement_name' => $val['BSKObservElement_name'],
                                    'BSKObservElementValues_data' => $val['BSKObservElementValues_data']
                                );
                                
                                if ( is_array( $val['BSKObservElementValues_sign'] ) && sizeof($val['BSKObservElementValues_sign']) > 0) {
                                    $this->getResult($data);
                                }
                            } else {
                                if ($val['BSKObservElementValues_sign'] == 'ПП' && $this->start != false) {
                                    $this->start = false;
                                    $data        = array(
                                        'sign' => $val['BSKObservElementValues_sign'],
                                        'BSKObservElementValues_id' => $val['BSKObservElementValues_id'],
                                        'BSKObservElement_name' => $val['BSKObservElement_name'],
                                        'BSKObservElementValues_data' => $val['BSKObservElementValues_data']
                                    );
                                    
                                    if (is_array($val['BSKObservElementValues_sign']) && sizeof($val['BSKObservElementValues_sign']) > 0) {
                                        $this->getResult($data);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } //end foreach
        
        //        echo '$signs11 =';    
        //        echo '<pre>' . print_r($signs, 1) . '</pre>'; 
        //        
        //        echo '$data11 =';    
        //        echo '<pre>' . print_r($data, 1) . '</pre>';  
        
        
        //Проверкка textfields & db data
        foreach ($ListAnswers as $k => $v) {
            $format_id = $v['format_id'];
            $value     = $v['value'];
            $unit      = $v['unit'];
            $question  = $v['question'];
            
            $data = array(
                'sign' => '',
                'BSKObservElementValues_id' => $k,
                'BSKObservElement_name' => $question,
                'BSKObservElementValues_data' => $value
            );
            
            //           echo '$format_id =';    
            //           echo '<pre>' . print_r($format_id, 1) . '</pre>';  
            if (in_array($format_id, array(
                1
            ))) {
                
                //                //if($format_id == 7){
                //var_dump($k);
                //                echo 'aaaaa'; 
                //                echo '(int) $k='; 
                //                echo (int) $k;
                
                switch ((int) $k) {
                    //Пол и возраст
                    case 25:
                        if ($sex_id == 1 && $age > 30) {
                            $signs['ПП'] += 1;
                            $data['sign'] = 'ПП';
                            $this->getResult($data);
                            
                        } elseif ($sex_id == 2 && $age > 40) {
                            $signs['ПП'] += 1;
                            $data['sign'] = 'ПП';
                            $this->getResult($data);
                        }
                        break;
                    //Диабет
                    case 34:
                        $diag  = $this->checkDiabetes(true);
                        $value = isset($diag[0]) ? $diag[0]['diagstring'] : '';
                        
                        $p = "#^(E10\.0|E10\.1|E10\.8|E10\.9|E11\.0|E11\.1|E11\.8|E14\.0|E14\.8|E12\.0|E13\.0)#i";
                        if (preg_match($p, $value)) {
                            $signs['КЗ'] += 1;
                            $data['sign'] = 'КЗ';
                            $this->getResult($data);
                            
                        }
                        $p = "#^(E10\.2|E10\.3|E10\.4|E10\.6|E11\.2|E11\.4|E11\.6|E14\.2|E14\.2|E14\.3|E14\.4|E14\.5|E14\.7|N18\.0|N18\.9|N19)#i";
                        if (preg_match($p, $value)) {
                            $signs['КФ'] += 1;
                            $data['sign'] = 'КФ';
                            $this->getResult($data);
                            
                        }
                        break;
                    //Хроническое заболевание почек
                    case 35:
                        $diag  = $this->checkDisease(true);
                        $value = isset($diag[0]) ? $diag[0]['diagstring'] : '';
                        
                        $p = "#^(N18\.8|N00\.2|N00\.4|N00\.5|N01\.2|N01\.5|N02\.2|N02\.4|N02\.7|N03\.3|N03\.5|N03\.7|N04\.3|N04\.5|N05\.2|N05\.4|N05\.7|N11\.8|N11\.9|N11\.0|E14\.4|E14\.5|E14\.7|N18\.0|N18\.9|N19)#i";
                        if (preg_match($p, $value)) {
                            $signs['КЗ'] += 1;
                            $data['sign'] = 'КЗ';
                            $this->getResult($data);
                            
                        }
                        $p = "#^(N18\.0)#";
                        if (preg_match($p, $value)) {
                            $signs['КФ'] += 1;
                            $data['sign'] = 'КФ';
                            $this->getResult($data);
                            
                        }
                        break;
                    //Щитовидка 
                    case 36:
                        $diag  = $this->checkGypofunction(true);
                        $value = isset($diag[0]) ? $diag[0]['diagstring'] : '';
                        
                        $p = "#^(E02|E03\.0|E03\.1|E03\.3|E03\.8)#i";
                        if (preg_match($p, $value)) {
                            $signs['ПП'] += 1;
                            $data['sign'] = 'ПП';
                            $this->getResult($data);
                            
                        }
                        break;
                    //аутоиммунные заболевания                    
                    case 37:
                        $diag  = $this->checkAutoimmune(true);
                        $value = isset($diag[0]) ? $diag[0]['diagstring'] : '';
                        
                        $p = "#^(С90\.0|С88\.0|М32\.0|М32\.8|М32\.9|L10\.1|L10\.2|L10\.3|L10\.4|L10\.5|L10\.8|L10\.9|L40\.0|L40\.1|L40\.2|L40\.3|L40\.4|L40\.5|L40\.8|L40\.9)#i";
                        if (preg_match($p, $value)) {
                            $signs['ПП'] += 1;
                            $data['sign'] = 'ПП';
                            $this->getResult($data);
                            
                        }
                        break;

                    //Неалкогольная жировая болезнь печени
                    case 43:
                        //Наличие у пациента камней в желчном пузыре
                    case 44:
                        $diag  = $this->checkStonesInBubble(true);
                        $value = isset($diag[0]) ? $diag[0]['diagstring'] : '';
                        
                        $p = "#^(K76\.0|К80\.2|К80\.4|К80\.8)#i";
                        if (preg_match($p, $value)) {
                            $signs['ПП'] += 1;
                            $data['sign'] = 'ПП';
                            $this->getResult($data);
                            
                        }
                        break;
                    
                    case 79:
                        
                        //Болезнь накопления гликогена
                        $diag  = $this->checkGlycogen(true);
                        $value = isset($diag[0]) ? $diag[0]['diagstring'] : '';
                        
                        if (strlen($value) > 0) {
                            
                            $signs['ПП'] += 1;
                            $data['sign'] = 'ПП';
                            $this->getResult($data);
                            
                        }
                        break;
                    
                    default:
                        
                        break;
                }
            }
            
            //Обходим textfields
            elseif ($format_id == 3) {
                
                switch ($k) {
                    //Объём талии
                    case 109:
                        //м ж до 16 лет в процентелях                             
                        if ($age < 16) {
                            $data = array(
                                'age' => $age,
                                'waist' => $value,
                                'Sex_id' => $sex_id
                            );
                            
                            $prc = $this->getWaistPercentel($data);
                            
                            if ((isset($prc[0]['prc']) ? $prc[0]['prc'] : 95) > 90) {
                                $signs['ПП'] += 1;
                                $data['sign'] = 'ПП';
                                $this->getResult($data);
                                
                            }
                        }
                        //старше 16 лет в см                             
                        elseif ($age >= 16) {
                            if ($sex_id == 1 && $value >= 94) {
                                $signs['ПП'] += 1;
                                $data['sign'] = 'ПП';
                                $this->getResult($data);
                                
                            } elseif ($sex_id == 2 && $value >= 80) {
                                $signs['ПП'] += 1;
                                $data['sign'] = 'ПП';
                                $this->getResult($data);
                                
                            }
                        }
                        break;
                    //Индекс массы тела
                    case 110:
                        //Не учитываем возраст пациента
                        //if($age < 16){
                        //    
                        //} 
                        //else{
                        if ($value > 30) { // ???? 25-30
                            $signs['ПП'] += 1;
                            $data['sign'] = 'ПП';
                            $this->getResult($data);
                            
                        }
                        //}
                        break;
                    //Липопротеины низкой плотности
                    case 88:
                        $value = strtr($value, array(
                            ',' => '.'
                        ));
                        if ($value > 4.9) {
                            $signs['КФ'] += 1;
                            $data['sign'] = 'КФ';
                            $this->getResult($data);
                            
                        } elseif ($value > 4) {
                            $signs['КФ'] += 1;
                            $data['sign'] = 'КФ';
                            $this->getResult($data);
                            
                        }
                        if ($value > 3) {
                            $signs['КЗ'] += 1;
                            $data['sign'] = 'КЗ';
                            $this->getResult($data);
                            
                        } elseif ($value >= 2.85) {
                            $signs['КЗ'] += 1;
                            $data['sign'] = 'КЗ';
                            $this->getResult($data);
                            
                        }
                        break;
                    //Общий холестерин
                    case 89:
                        $data['BSKObservElement_name'] = $answersDB[89][0]['BSKObservElement_name'];
                        
                        $value = strtr($value, array(
                            ',' => '.'
                        ));
                        if ($value > 7.5) {
                            $signs['КФ'] += 1;
                            $data['sign'] = 'КФ';
                            $this->getResult($data);
                            
                        } elseif ($value > 6.7) {
                            $signs['КФ'] += 1;
                            $data['sign'] = 'КФ';
                            $this->getResult($data);
                            
                        } elseif ($value > 5) {
                            $signs['КЗ'] += 1;
                            $data['sign'] = 'КЗ';
                            $this->getResult($data);
                            
                        } elseif ($value >= 4.4) {
                            $signs['КЗ'] += 1;
                            $data['sign'] = 'КЗ';
                            $this->getResult($data);
                            
                        }
                        break;
                    //Липопротеины высокой плотности
                    case 90:
                        $value = strtr($value, array(
                            ',' => '.'
                        ));
                        if ($age >= 2 && $age <= 16) {
                            if ($value < 0.9 || $value >= 1.6) {
                                $signs['КЗ'] += 1;
                                $data['sign'] = 'КЗ';
                                $this->getResult($data);
                                
                            }
                        } elseif ($age > 16 && $sex_id == 1) {
                            if ($value < 0.7 || $value >= 1.6) {
                                $signs['КЗ'] += 1;
                                $data['sign'] = 'КЗ';
                                $this->getResult($data);
                                
                            }
                        } elseif ($age > 16 && $sex_id == 2) {
                            if ($value < 0.9 || $value >= 1.6) {
                                $signs['КЗ'] += 1;
                                $data['sign'] = 'КЗ';
                                $this->getResult($data);
                                
                            }
                        }
                        break;
                    //Триглицериды
                    case 91:
                        $value = strtr($value, array(
                            ',' => '.'
                        ));
                        if ($age >= 2 && $age <= 16) {
                            if ($value >= 0.85) {
                                $signs['КЗ'] += 1;
                                $data['sign'] = 'КЗ';
                                $this->getResult($data);
                                
                            }
                        } elseif ($age > 16) {
                            if ($value > 1.7) {
                                $signs['КЗ'] += 1;
                                $data['sign'] = 'КЗ';
                                $this->getResult($data);
                                
                            }
                        }
                        break;
                    //АпоВ-100 (вредный холестерин)
                    //используем только ммоль/л
                    case 92:
                        $value = strtr($value, array(
                            ',' => '.'
                        ));
                        $koef  = ($unit == 'ммоль/л') ? 1 : 40;
                        //echo '<h1>' . $value . '</h1>';
                        if ($age >= 2 && $age <= 16) {
                            if ($value >= 90 * $koef) {
                                $signs['КЗ'] += 1;
                                $data['sign'] = 'КЗ';
                                $this->getResult($data);
                                
                            }
                        } elseif ($age > 16) {
                            if ($value > 100 / $koef) {
                                $signs['КЗ'] += 1;
                                $data['sign'] = 'КЗ';
                                $this->getResult($data);
                                
                            }
                        }
                        break;
                    //Апо А1 (полезный холестерин)
                    //используем только ммоль/л
                    case 93:
                        $value = strtr($value, array(
                            ',' => '.'
                        ));
                        $koef  = ($unit == 'ммоль/л') ? 1 : 40;
                        
                        if ($value <= 110 * $koef) {
                            $signs['КЗ'] += 1;
                            $data['sign'] = 'КЗ';
                            $this->getResult($data);
                            
                        }
                        
                        break;
                    //Липопротеин (а)
                    //используем только ммоль/л
                    case 94:
                        $value = strtr($value, array(
                            ',' => '.'
                        ));
                        
                        if ($value <= 50) {
                            $signs['КЗ'] += 1;
                            $data['sign'] = 'КЗ';
                            $this->getResult($data);
                            
                        }
                        
                        break;
                    //СРБ вч (Ц-реактивный белок высокочувствительным методом)
                    case 95:
                        $value = strtr($value, array(
                            ',' => '.'
                        ));
                        
                        if ($value >= 2) {
                            $signs['КЗ'] += 1;
                            $data['sign'] = 'КЗ';
                            $this->getResult($data);
                            
                        }
                        
                        break;
                    //Почечные пробы.Скорость клубочковой фильтрации.
                    case 96:
                        $value = strtr($value, array(
                            ',' => '.'
                        ));
                        
                        if ($age >= 2 && $age <= 16) {
                            if ($value < 60) {
                                $signs['КЗ'] += 1;
                                $data['sign'] = 'КЗ';
                                $this->getResult($data);
                                
                            }
                        } elseif ($age > 16) {
                            if ($value < 30) {
                                $signs['КЗ'] += 1;
                                $data['sign'] = 'КЗ';
                                $this->getResult($data);
                                
                            }
                        }
                        break;
                    //Почечные пробы.Микроальбуминурия)/протеинурия (МАУ).
                    case 97:
                        $value = strtr($value, array(
                            ',' => '.'
                        ));
                        if ($unit == 'кв. м.') {
                            if ($age >= 2 && $age <= 16) {
                                if ($value >= 30) {
                                    $signs['КФ'] += 1;
                                    $data['sign'] = 'КФ';
                                    $this->getResult($data);
                                    
                                }
                            } elseif ($age > 16) {
                                if ($value > 300) {
                                    $signs['КФ'] += 1;
                                    $this->getResult($data);
                                    
                                }
                            }
                        }
                        if ($unit == 'мл/мин/1,73 кв.м') {
                            if ($age >= 2 && $age <= 16) {
                                if ($value >= 3.4 && $value <= 34) {
                                    $signs['КЗ'] += 1;
                                    $data['sign'] = 'КЗ';
                                    $this->getResult($data);
                                    
                                }
                            } elseif ($age > 16) {
                                if ($value > 34) {
                                    $signs['КФ'] += 1;
                                    $data['sign'] = 'КЗ';
                                    $this->getResult($data);
                                    
                                }
                            }
                        }
                        break;
                    //Почечные пробы. Соотношение микроальбумина/ креатинина в моче
                    case 98:
                        $value = strtr($value, array(
                            ',' => '.'
                        ));
                        
                        if ($age >= 2 && $age <= 16) {
                            if ($value > 30 && $value < 300) {
                                $signs['КЗ'] += 1;
                                $data['sign'] = 'КЗ';
                                $this->getResult($data);
                            }
                        } elseif ($age > 16) {
                            if ($value > 300) {
                                $signs['КФ'] += 1;
                                $data['sign'] = 'КФ';
                                $this->getResult($data);
                            }
                        }
                        break;
                    //Глюкоза в крови (капиллярная)
                    case 99:
                        $value = strtr($value, array(
                            ',' => '.'
                        ));
                        
                        if ($age >= 2 && $age <= 16) {
                            if ($value >= 5.6 && $value < 7) {
                                $signs['КЗ'] += 1;
                                $data['sign'] = 'КЗ';
                                $this->getResult($data);
                            }
                        } elseif ($age > 16) {
                            if ($value >= 7) {
                                $signs['КФ'] += 1;
                                $data['sign'] = 'КФ';
                                $this->getResult($data);
                            }
                        }
                        break;
                    //Глюкоза в крови (венозная)
                    case 100:
                        $value = strtr($value, array(
                            ',' => '.'
                        ));
                        
                        if ($age >= 2 && $age <= 16) {
                            if ($value >= 6.1) {
                                $signs['КЗ'] += 1;
                                $data['sign'] = 'КЗ';
                                $this->getResult($data);
                            }
                        } elseif ($age > 16) {
                            if ($value >= 7) {
                                $signs['КФ'] += 1;
                                $data['sign'] = 'КФ';
                                $this->getResult($data);
                            }
                        }
                        break;
                    //Через 2 часа после перорального глюкозо –толерантного теста
                    case 101:
                        $value = strtr($value, array(
                            ',' => '.'
                        ));
                        
                        if ($age >= 2 && $age <= 16) {
                            if ($value >= 7.8) {
                                $signs['КЗ'] += 1;
                                $data['sign'] = 'КЗ';
                                $this->getResult($data);
                            }
                        } elseif ($age > 16) {
                            if ($value >= 11.1) {
                                $signs['КФ'] += 1;
                                $data['sign'] = 'КФ';
                                $this->getResult($data);
                            }
                        }
                        break;
                    //Ветви дуги аорты. Толщина комплекса интима-медиа
                    // Активация ПН "АВДА"
                    case 102:
                        if ($age >= 16 && $age < 40) {
                            if ($value > 1.3) {
                                $signs['PN'] += 1;
                                $data['sign'] = 'PN';
                                $this->getResult($data);
                            } elseif ($value > 0.7) {
                                $signs['КЗ'] += 1;
                                $data['sign'] = 'КЗ';
                                $this->getResult($data);
                            }
                        } elseif ($age >= 40 && $age < 50 && $sex_id == 1) {
                            if ($value > 1.3) {
                                $signs['PN'] += 1;
                                $data['sign'] = 'PN';
                                $this->getResult($data);
                            } elseif ($value > 0.8) {
                                $signs['КЗ'] += 1;
                                $data['sign'] = 'КЗ';
                                $this->getResult($data);
                            }
                        } elseif ($age >= 50 && $sex_id == 1) {
                            if ($value > 1.3) {
                                $signs['PN'] += 1;
                                $data['sign'] = 'PN';
                                $this->getResult($data);
                            } elseif ($value > 0.9) {
                                $signs['КЗ'] += 1;
                                $data['sign'] = 'КЗ';
                                $this->getResult($data);
                            }
                        } elseif ($age >= 40 && $age < 60 && $sex_id == 2) {
                            if ($value > 1.3) {
                                $signs['PN'] += 1;
                                $data['sign'] = 'PN';
                                $this->getResult($data);
                            } elseif ($value > 0.8) {
                                $signs['КЗ'] += 1;
                                $data['sign'] = 'КЗ';
                                $this->getResult($data);
                            }
                        } elseif ($age >= 60 && $sex_id == 2) {
                            if ($value > 1.3) {
                                $signs['PN'] += 1;
                                $data['sign'] = 'PN';
                                $this->getResult($data);
                            } elseif ($value > 0.9) {
                                $signs['КЗ'] += 1;
                                $data['sign'] = 'КЗ';
                                $this->getResult($data);
                            }
                        }
                        break;
                    
                    //Артерии нижних конечностей. Толщина комплекса интим
                    case 104:
                        if ($value > 1.2) {
                            $signs['КЗ'] += 1;
                            $data['sign'] = 'КЗ';
                            $this->getResult($data);
                        } elseif ($value >= 1.1) {
                            $signs['КЗ'] += 1;
                            $data['sign'] = 'КЗ';
                            $this->getResult($data);
                        }
                        break;
                    //Индекс кальцификации коронарных артерий (индекса
                    //Активация активируется ПН "ИБС"
                    case 106:
                        if ($age >= 16) {
                            if ($value >= 400) {
                                $signs['PN'] += 1;
                                $data['sign'] = 'PN';
                                $this->getResult($data);
                            } elseif ($value >= 100 && $value < 400) {
                                $signs['КФ'] += 1;
                                $data['sign'] = 'КФ';
                                $this->getResult($data);
                            } elseif ($value >= 11 && $value < 99) {
                                $signs['КЗ'] += 1;
                                $data['sign'] = 'КЗ';
                                $this->getResult($data);
                            }
                        }
                        break;
                    
                    case 110:
                        if ($age >= 2) {
                            if ($value < 19 || $value >= 30) {
                                $signs['ПП'] += 1;
                                $data['sign'] = 'ПП';
                                $this->getResult($data);
                            }
                            
                        }
                        break;
                }
            }
            }
        //Определение группы риска
        $riskGroup = $this->getRiskGroup();
        //       echo '$riskGroup=';   
        //      echo '<pre>' . print_r($riskGroup, 1) . '</pre>';  
      
        //echo json_encode($ListAnswers);
        
        $registryData = array(
            'ListAnswers' => $ListAnswers,
            'riskGroup' => $riskGroup,
            'MorbusType_id' => $MorbusType_id,
            'setDate' => $setDate,
            'Person_id' => $Person_id,
            'BSKRegistry_id' => $BSKRegistry_id
        );
        
        if ($BSKRegistry_id == null) {
            $this->saveRegistry($registryData);
        } else {
            $this->updateRegistry($registryData);
        }
        
        
        /*    
        foreach($this->result as $k=>$v){
        
        echo '<h1>' . $k . '</h1>';
        
        foreach($v as $j=>$val){
        
        echo 'Вопрос: '.$val['name'].'<br/>';
        echo 'Ответ: '.$val['value'].'<br/>';
        }
        }
        */
        
        
    }
    
    /**
     * Сохранение скрининга в БД
     */
    function saveRegistry($registryData)
    {
        $data = $this->ProcessInputData('saveRegistry', true);
        
        if ($data === false) {
            return false;
        }
        
        $result = $this->dbmodel->saveRegistry($data, $registryData);
        
        //echo '<pre>' . print_r($result, 1) . '</pre>';
        $this->ReturnData($result);
    }
    
    /**
     *  Определение группы риска пациента
     */
    function getRiskGroup()
    {   
        if (isset($this->result['PN']) && sizeof($this->result['PN']) > 0)
            return 3;
        else {
            $kzPP = isset($this->result['КЗ']) && sizeof($this->result['КЗ'] > 0) ? sizeof($this->result['КЗ']) * 3.1 : 0;
            $kfPP = isset($this->result['КФ']) && sizeof($this->result['КФ']) > 0 ? sizeof($this->result['КФ']) * 8 : 0;
            $PP   = isset($this->result['ПП']) && sizeof($this->result['ПП']) > 0 ? sizeof($this->result['ПП']) : 0;
            
            $allPP = $kzPP + $kfPP + $PP;
            
            if ($allPP < 3.5)
                return 1;
            elseif ($allPP >= 3.5 && $allPP < 7.5) {
                return 2;
            } else {
                return 3;
            }
        }
    }
    /**
     *  Получение пары вопрос-ответ = признак
     */
    function getResult($data)
    {
        
        if (isset($data['BSKObservElementValues_id'])) {
            $this->result[$data['sign']][$data['BSKObservElementValues_id']] = array(
                
                'id' => $data['BSKObservElementValues_id'],
                'name' => $data['BSKObservElement_name'],
                'value' => $data['BSKObservElementValues_data']
                
            );
        }
        
        //return $result;
        //echo '<hr/>'. $data['sign'].'='.$data['BSKObservElementValues_id'].'-'.$data['BSKObservElement_name'].':'.$data['BSKObservElementValues_data'];
    }
    
    /**
     *  Объём талии в перцентелях
     */
    function getWaistPercentel($data)
    {
        return $this->dbmodel->getWaistPercentel($data);
    }
    
    /**
     * Наличие диабета у пациента 
     */
    function checkDiabetes($return = false)
    {
        $data = $this->ProcessInputData('checkDiabetes', true);
        
        if ($data === false) {
            return false;
        }
        
        $list = $this->dbmodel->checkDiabetes($data);
        
        if ($return === true)
            return $list;
        else
            $this->ReturnData($list);
        
        /*
        Array
        (
        [0] => Array
        (
        [diagstring] => C20 Злокачественное новообразование прямой кишки
        )
        
        )
        */
    }
    
    /**
     * Хроническое заболевание почек
     */
    function checkDisease($return = false)
    {
        $data = $this->ProcessInputData('checkDisease', true);
        
        if ($data === false) {
            return false;
        }
        
        $list = $this->dbmodel->checkDisease($data);
        
        if ($return === true)
            return $list;
        else
            $this->ReturnData($list);
    }
    
    /**
     * Аутоиммунные заболевания
     */
    function checkAutoimmune($return = false)
    {
        $data = $this->ProcessInputData('checkAutoimmune', true);
        
        if ($data === false) {
            return false;
        }
        
        $list = $this->dbmodel->checkAutoimmune($data);
        
        if ($return === true)
            return $list;
        else
            $this->ReturnData($list);
    }
    
    /**
     *   Гипофункция щитовидной железы
     */
    function checkGypofunction($return = false)
    {
        $data = $this->ProcessInputData('checkGypofunction', true);
        
        if ($data === false) {
            return false;
        }
        
        $list = $this->dbmodel->checkGypofunction($data);
        
        if ($return === true)
            return $list;
        else
            $this->ReturnData($list);
    }
    
    /**
     * Неалкогольная жировая болезнь печени 
     */
    function checkFattyLiver($return = false)
    {
        $data = $this->ProcessInputData('checkFattyLiver', true);
        
        if ($data === false) {
            return false;
        }
        
        $list = $this->dbmodel->checkFattyLiver($data);
        
        if ($return === true)
            return $list;
        else
            $this->ReturnData($list);
    }
    
    /**
     * Наличие у пациента камней в желчном пузыре
     */
    function checkStonesInBubble($return = false)
    {
        $data = $this->ProcessInputData('checkStonesInBubble', true);
        
        if ($data === false) {
            return false;
        }
        
        $list = $this->dbmodel->checkStonesInBubble($data);
        
        if ($return === true)
            return $list;
        else
            $this->ReturnData($list);
    }
    
    /**
     * Синдром обструктивного апное сна (храп)
     */
    function checkSnoring($return = false)
    {
        $data = $this->ProcessInputData('checkSnoring', true);
        
        if ($data === false) {
            return false;
        }
        
        $list = $this->dbmodel->checkSnoring($data);
        
        if ($return === true)
            return $list;
        else
            $this->ReturnData($list);
    }
    
    /**
     *  ухудшение слуха
     */
    function checkBadHear($return = false)
    {
        $data = $this->ProcessInputData('checkBadHear', true);
        
        if ($data === false) {
            return false;
        }
        
        $list = $this->dbmodel->checkBadHear($data);
        
        if ($return === true)
            return $list;
        else
            $this->ReturnData($list);
    }
    
    /**
     *  Эректильная дисфункция
     */
    function checkDysfunction($return = false)
    {
        $data = $this->ProcessInputData('checkDysfunction', true);
        
        if ($data === false) {
            return false;
        }
        
        $list = $this->dbmodel->checkDysfunction($data);
        
        if ($return === true)
            return $list;
        else
            $this->ReturnData($list);
    }
    
    
    /**
     *  Поликистоз яичников
     */
    function checkPolycystic($return = false)
    {
        $data = $this->ProcessInputData('checkPolycystic', true);
        
        if ($data === false) {
            return false;
        }
        
        $list = $this->dbmodel->checkPolycystic($data);
        
        if ($return === true)
            return $list;
        else
            $this->ReturnData($list);
    }
    
    /**
     *  подагра
     */
    function checkGout($return = false)
    {
        $data = $this->ProcessInputData('checkGout', true);
        
        if ($data === false) {
            return false;
        }
        
        $list = $this->dbmodel->checkGout($data);
        
        if ($return === true)
            return $list;
        else
            $this->ReturnData($list);
    }
    
    /**
     *  Липодистрофия
     */
    function checkLipodystrophy($return = false)
    {
        $data = $this->ProcessInputData('checkLipodystrophy', true);
        
        if ($data === false) {
            return false;
        }
        
        $list = $this->dbmodel->checkLipodystrophy($data);
        
        if ($return === true)
            return $list;
        else
            $this->ReturnData($list);
    }
    
    /**
     *  Болезнь накопления гликогена
     */
    function checkGlycogen($return = false)
    {
        $data = $this->ProcessInputData('checkGlycogen', true);
        
        if ($data === false) {
            return false;
        }
        
        $list = $this->dbmodel->checkGlycogen($data);
        
        if ($return === true)
            return $list;
        else
            $this->ReturnData($list);
    }
 
    /**
     * Наличие ВИЧ у пациента 
     */
    function checkHIV($return = false)
    {
        $data = $this->ProcessInputData('checkHIV', true);
        
        if ($data === false) {
            return false;
        }
        
        $list = $this->dbmodel->checkHIV($data);
        
        if ($return === true)
            return $list;
        else
            $this->ReturnData($list);
    } 

    /**
     * Портальная гипертензия
     */
    function checkPortalHypertension($return = false)
    {
        $data = $this->ProcessInputData('checkPortalHypertension', true);
        
        if ($data === false) {
            return false;
        }
        
        $list = $this->dbmodel->checkPortalHypertension($data);
        
        if ($return === true)
            return $list;
        else
            $this->ReturnData($list);
    }   
    
    /**
     * Патология легких
     */
    function checkLungPathology($return = false)
    {
        $data = $this->ProcessInputData('checkLungPathology', true);
        
        if ($data === false) {
            return false;
        }
        
        $list = $this->dbmodel->checkLungPathology($data);
        
        if ($return === true)
            return $list;
        else
            $this->ReturnData($list);
    }         
    
    /**
     * Пороки сердца
     */
    function checkHeartDefects($return = false)
    {
        $data = $this->ProcessInputData('checkHeartDefects', true);
        
        if ($data === false) {
            return false;
        }
        
        $list = $this->dbmodel->checkHeartDefects($data);
        
        if ($return === true)
            return $list;
        else
            $this->ReturnData($list);
    }       

    /**
     * Заболевания соединительной ткани
     */
    function checkTissueDiseases($return = false)
    {
        $data = $this->ProcessInputData('checkTissueDiseases', true);
        
        if ($data === false) {
            return false;
        }
        
        $list = $this->dbmodel->checkTissueDiseases($data);
        
        if ($return === true)
            return $list;
        else
            $this->ReturnData($list);
    }  
    
    /**
     * Синдром абструктивного апноэ сна
     */
    function checkSnoringDiag($return = false)
    {
        $data = $this->ProcessInputData('checkSnoringDiag', true);
        
        if ($data === false) {
            return false;
        }
        
        $list = $this->dbmodel->checkSnoringDiag($data);
        
        if ($return === true)
            return $list;
        else
            $this->ReturnData($list);
    }    
    
    /**
     * Саркоидоз
     */
    function checkSarcoidosis($return = false)
    {
        $data = $this->ProcessInputData('checkSarcoidosis', true);
        
        if ($data === false) {
            return false;
        }
        
        $list = $this->dbmodel->checkSarcoidosis($data);
        
        if ($return === true)
            return $list;
        else
            $this->ReturnData($list);
    }    
    
    /**
     * Гистиоцитоз
     */
    function checkHistiocytosis($return = false)
    {
        $data = $this->ProcessInputData('checkHistiocytosis', true);
        
        if ($data === false) {
            return false;
        }
        
        $list = $this->dbmodel->checkHistiocytosis($data);
        
        if ($return === true)
            return $list;
        else
            $this->ReturnData($list);
    }   
    
    /**
     * Шистосомоз
     */
    function checkSchistosomiasis($return = false)
    {
        $data = $this->ProcessInputData('checkSchistosomiasis', true);
        
        if ($data === false) {
            return false;
        }
        
        $list = $this->dbmodel->checkSchistosomiasis($data);
        
        if ($return === true)
            return $list;
        else
            $this->ReturnData($list);
    }                 
    
 
    /**
     * Диабет с диагнозом
     */
    function checkDiabetesDiag($return = false)
    {
        $data = $this->ProcessInputData('checkDiabetesDiag', true);
        
        if ($data === false) {
            return false;
        }
        
        $list = $this->dbmodel->checkDiabetesDiag($data);
        
        if ($return === true)
            return $list;
        else
            $this->ReturnData($list);
    }     
    
    /**
     * ИБС с диагнозом
     */
    function checkIBS($return = false)
    {
        $data = $this->ProcessInputData('checkIBS', true);
        
        if ($data === false) {
            return false;
        }
        
        $list = $this->dbmodel->checkIBS($data);
        
        if ($return === true)
            return $list;
        else
            $this->ReturnData($list);
    }         
 
    /**
     * Цереброваскулярная болезнь с диагнозом
     */
    function checkCerebrovascular($return = false)
    {
        $data = $this->ProcessInputData('checkCerebrovascular', true);
        
        if ($data === false) {
            return false;
        }
        
        $list = $this->dbmodel->checkCerebrovascular($data);
        
        if ($return === true)
            return $list;
        else
            $this->ReturnData($list);
    }         

    /**
     * Хроническая болезнь почек с диагнозом
     */
    function checkDiseaseDiag($return = false)
    {
        $data = $this->ProcessInputData('checkDiseaseDiag', true);
        
        if ($data === false) {
            return false;
        }
        
        $list = $this->dbmodel->checkDiseaseDiag($data);
        
        if ($return === true)
            return $list;
        else
            $this->ReturnData($list);
    }   
            
    /**
     *  Получение вопросов для анкеты
     */
    function getAnketsData()
    {
        $data = $this->ProcessInputData('getAnketsData', true);
        
        if ($data === false) {
            return false;
        }
        
        $list = $this->dbmodel->getAnketsData($data);
        
        //echo '<pre>' . print_r($list, 1) . '</pre>';
        
        //return $this->ReturnData($list);       
    }
    /**
     * Список едениц измернеия 
     */
    function getComboUnits()
    {
        $data = $this->ProcessInputData('getComboUnits', true);
        
        if ($data === false) {
            return false;
        }
        
        $list = $this->dbmodel->getComboUnits($data);
        
        return $this->ReturnData($list);
    }
    /**
     *  Построение опроснига для предметов наблюдения
     */
    function addRegistryData()
    {
        $data = $this->ProcessInputData('addRegistryData', true);
        
        if ($data === false) {
            return false;
        }
        
        $list = $this->dbmodel->addRegistryData($data);
        
        //echo '<pre>' . print_r($list, 1) . '</pre>';
        
        $groups = [];
        $questions = array();
        
        foreach ($list as $k => $v) {
            $answers['k_' . $v['BSKObservElement_id']][$v['BSKObservElementValues_id']] = $v['BSKObservElementValues_data'];
            
            $groups[$v['BSKObservElementGroup_id']] = array(
                'id' => $v['BSKObservElementGroup_id'],
                'group' => $v['BSKObservElementGroup_name']
            );
            
            //echo '<pre>' . print_r($groups, 1) . '</pre>';
            
            $questions['k_' . $v['BSKObservElement_id']] = array(
                'BSKObservElement_id' => $v['BSKObservElement_id'],
                'BSKObservElement_name' => $v['BSKObservElement_name'],
                'BSKObservElementGroup_id' => $v['BSKObservElementGroup_id'],
                'BSKObservElementFormat_id' => $v['BSKObservElementFormat_id'],
                
                'BSKObservElement_stage' => $v['BSKObservElement_stage'],
                'BSKObservElement_minAge' => $v['BSKObservElement_minAge'],
                'BSKObservElement_maxAge' => $v['BSKObservElement_maxAge'],
                'BSKObservElement_Sex_id' => $v['BSKObservElement_Sex_id']
            );
        }
        
        foreach ($questions as $k => $v) {
            $questions[$k]['answer'] = $answers[$k];
        }
        
        //}
        //echo '<pre>' . print_r($questions, 1) . '</pre>';
        //echo '<pre>' . print_r($groups, 1) . '</pre>';
        //exit;
        return $this->ReturnData(array(
            'questions' => $questions,
            'groups' => $groups
        ));
    }
    
    /**
     *  Список предметов наблюдения
     */
    function getBskObjects()
    {
        $data = $this->ProcessInputData('getBSKObjects', true);
        
        if ($data === false) {
            return false;
        }
        
        $list = $this->dbmodel->getBSKObjects($data);
        
        return $this->ReturnData($list);
    }
    
    /**
     * Получение списка предметов наблюдения для пациента
     */
    function getListBSKObjects()
    {
        $data = $this->ProcessInputData('getListBSKObjects', true);
        
        if ($data === false) {
            return false;
        }
        
        $list = $this->dbmodel->getListBSKObjects($data);
        
        return $this->ReturnData($list);
    }
    
    /**
     * Получение списка предметов наблюдения для конкретного пациента
     */
    function getListObjectsCurrentUser()
    {
        $data = $this->ProcessInputData('getListObjectsCurrentUser', true);
        
        if ($data === false) {
            return false;
        }
        
        $list = $this->dbmodel->getListObjectsCurrentUser($data);
        
        return $this->ReturnData($list);
    }
    
    /**
     *  Проверка наличия пациента в регистре по предмету наблюдения
     */
    function checkPersonInRegister()
    {
        $data = $this->ProcessInputData('checkPersonInRegister', true);
        
        if ($data === false) {
            return false;
        }
        
        $response = $this->dbmodel->checkPersonInRegister($data);
        
        return $response;
    }

	/**
	 *  Проверка наличия пациента в регистре БСК
	 */
	function checkPersonInRegisterforEMK()
	{
		$data = $this->ProcessInputData('checkPersonInRegisterforEMK', true);

		if ($data === false) {
			return false;
		}

		$result = $this->dbmodel->checkPersonInRegisterforEMK($data);

		$this->ReturnData($result);
	}

    /**
     * Подсветка текста голубым
     */
    function blue($text)
    {
        return '<font style="color: blue;">' . $text . '</font>';
    }
    /**
     * Получение сведений о пациенте
     */
    function loadPersonData()
    {
        $data = $this->ProcessInputData('loadPersonData', true);
        if ($data === false) {
            return false;
        }
        
        $dbAnswer = $this->dbmodel->loadPersonData($data);
        
        //$personData = 
        
        //echo '<pre>' . print_r($dbAnswer, 1 ) .'</pre>';    
        
        $titleInfo = $dbAnswer[0]['Person_Surname'] . ' ' . $dbAnswer[0]['Person_Firname'] . ' ' . $dbAnswer[0]['Person_Secname'] . ', ' . $dbAnswer[0]['Person_Birthday'];
        //BSKRegistry_riskGroup
        $textInfo  = 'ФИО: <b>' . $this->blue($dbAnswer[0]['Person_Surname'] . ' ' . $dbAnswer[0]['Person_Firname'] . ' ' . $dbAnswer[0]['Person_Secname']) . '</b> 
        Д/р: ' . $this->blue($dbAnswer[0]['Person_Birthday']) . ' Пол: ' . $this->blue($dbAnswer[0]['Sex_Name']) . '<br/> 
        Соц. статус: ' . $this->blue($dbAnswer[0]['SocStatus_Name']) . ' СНИЛС: ' . $this->blue($dbAnswer[0]['Person_Snils']) . '<br/>
        Регистрация: ' . $this->blue($dbAnswer[0]['Person_RAddress']) . '<br/>
        Проживает: ' . $this->blue($dbAnswer[0]['Person_PAddress']) . '<br/>
        Полис: ' . $this->blue($dbAnswer[0]['Polis_Num']) . ' Выдан: ' . $this->blue($dbAnswer[0]['Polis_begDate'] . ', ' . $dbAnswer[0]['OrgSmo_Name']) . ' Закрыт: ' . $this->blue($dbAnswer[0]['Polis_endDate']) . '<br/>
        Документ: ' . $this->blue($dbAnswer[0]['Document_Num'] . ' ' . $dbAnswer[0]['Document_Ser']) . ' Выдан: ' . $this->blue($dbAnswer[0]['Document_begDate']) . '<br/>
        Работа: ' . $this->blue($dbAnswer[0]['Person_Job']) . ' Должность: ' . $this->blue($dbAnswer[0]['Person_Post']) . '<br/>
        МО: ' . $this->blue($dbAnswer[0]['Lpu_Nick']) . ' Участок: ' . $this->blue($dbAnswer[0]['LpuRegion_Name']) . ' Дата прикрепления: ' . $this->blue($dbAnswer[0]['PersonCard_begDate']) . '
        ';
        
        $personInfo = $dbAnswer[0];
        
        $personData = json_encode(array(
            'title' => $titleInfo,
            'text' => $textInfo,
            'personInfo' => $personInfo
        ));
        
        echo $personData;
        
    }
    
    /**
     * Добавления пациента в PersonRegister
     */
    function saveInPersonRegister()
    {
        
        $checkPersonInRegister = $this->checkPersonInRegister();
        
        if ($checkPersonInRegister === false) {
            
            return $this->ReturnData(array(
                'success' => false,
                'Error_Msg' => toUTF('Данный пациент уже присутствует в регистре БСК по данному предмету наблюдения!')
            ));
        }
        
        
        $data = $this->ProcessInputData('saveInPersonRegister', true);
        if ($data === false) {
            return false;
        }
        
        $response = $this->dbmodel->saveInPersonRegister($data);
        $this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
    }
    
    /**
     * Добавления пациента в PersonRegister при ОКС
     */
    function saveInPersonRegisterOKS()
    {       
        $data = $this->ProcessInputData('saveInPersonRegister', true);
        if ($data === false) {
            return false;
        }
        
        $response = $this->dbmodel->saveInPersonRegisterOKS($data);
        $this->ProcessModelSave($response, true, 'Ошибка при выполнении запроса к базе данных')->ReturnData();
    }
    /**
     * Получение списка пользователей (только врачей)
     */
    function getCurrentOrgUsersList() {
        $data = getSessionParams();

        if ( !empty($data['session']['lpu_id']) ) {
                $response = $this->dbmodel->getCurrentOrgUsersList($data['session']['lpu_id']);
                $this->ProcessModelList($response, true, true)->ReturnData();
        }
        else {
                $this->ReturnData(array());
        }

        return true;
    }        

    /**
     * Запись в регистр БСК в ПН ОКС с форм "Поступление пациента в приемное отделение" и "Карта выбывшего из стационара"
     */
    function saveKvsInOKS(){
        $data = $this->ProcessInputData('saveKvsInOKS', true);
        if ( $data === false ) { return false; }
        $response = $this->dbmodel->saveKvsInOKS($data);
        $this->ReturnData($response);
    }
    
    /**
     * Проставление признака просмотра анкеты
     */
    function setIsBrowsed() {

        $data = $this->ProcessInputData('setIsBrowsed', true);
        if ($data === false) return false;
        $result = $this->dbmodel->setIsBrowsed($data);
        $this->ReturnData($result);
    }
	/**
	* Основной диагноз из КВС
	*/
	function getDiagFromEvnPS() {

		$data = $this->ProcessInputData('getDiagFromEvnPS', true);

		if ($data === false) {
			return false;
		}
		$list = $this->dbmodel->getDiagFromEvnPS($data);

		return $this->ReturnData($list);
	}
    /**
    * Таблица «Услуги». Отображаются операционные и общие услуги, проведённые пациенту
    */
    function getListUslugforEvents() {

        $data = $this->ProcessInputData('getListUslugforEvents', true);

        if ($data === false) {
            return false;
        }
        $list = $this->dbmodel->getListUslugforEvents($data);

        return $this->ReturnData($list);
    }
    /**
    * Таблица «Случаи оказания амбулаторно-поликлинической медицинской помощи»
    */
    function getListPersonCureHistoryPL() {

        $data = $this->ProcessInputData('getListPersonCureHistoryPL', true);

        if ($data === false) {
            return false;
        }
        $list = $this->dbmodel->getListPersonCureHistoryPL($data);

        return $this->ReturnData($list);
    }
    /**
    * Таблица «Случаи оказания стационарной медицинской помощи»
    */
    function getListPersonCureHistoryPS() {

        $data = $this->ProcessInputData('getListPersonCureHistoryPS', true);

        if ($data === false) {
            return false;
        }
        $list = $this->dbmodel->getListPersonCureHistoryPS($data);

        return $this->ReturnData($list);
    }
    /**
    * Таблица «Сопутствующие диагнозы»
    */
    function getListPersonCureHistoryDiagSop() {

        $data = $this->ProcessInputData('getListPersonCureHistoryDiagSop', true);

        if ($data === false) {
            return false;
        }
        $list = $this->dbmodel->getListPersonCureHistoryDiagSop($data);

        return $this->ReturnData($list);
    }
    /**
    * Таблица «Постинфарктный кардиосклероз»
    */
    function getListPersonCureHistoryDiagKardio() {

        $data = $this->ProcessInputData('getListPersonCureHistoryDiagKardio', true);

        if ($data === false) {
            return false;
        }
        $list = $this->dbmodel->getListPersonCureHistoryDiagKardio($data);

        return $this->ReturnData($list);
    }
    /**
    * Вкладка «Исследования»
    */
    function getLabResearch() {

        $data = $this->ProcessInputData('getLabResearch', true);

        if ($data === false) {
            return false;
        }
        $list = $this->dbmodel->getLabResearch($data);

        return $this->ReturnData($list);
    }
     /**
    * Вкладка «Обследования»
    */
    function getLabSurveys() {

        $data = $this->ProcessInputData('getLabSurveys', true);

        if ($data === false) {
            return false;
        }
        $list = $this->dbmodel->getLabSurveys($data);

        return $this->ReturnData($list);
    }

	/**
	 * getBSKObjectWithoutAnket
	 *
	 * @return void
	 */
	function getBSKObjectWithoutAnket() {

		$data = $this->ProcessInputData('getBSKObjectWithoutAnket', true);

		if ($data === false) {
			return false;
		}
		$list = $this->dbmodel->getBSKObjectWithoutAnket($data);

		return $this->ReturnData($list);
	}

	/**
	 * loadBSKEvnGrid
	 *
	 * @return void
	 */
	function loadBSKEvnGrid() {

		$data = $this->ProcessInputData('loadBSKEvnGrid', true);

		if ($data === false) {
			return false;
		}
		$list = $this->dbmodel->loadBSKEvnGrid($data);

		return $this->ReturnData($list);
	}
		/**
	* Получение Операции, услуги (ЧКВ, КАГ, АКШ) за предыдущие три года
	*/
	function getListOperUslug() {

		$data = $this->ProcessInputData('getListOperUslug', true);

		if ($data === false) {
			return false;
		}
		$list = $this->dbmodel->getListOperUslug($data);

		return $this->ReturnData($list);
	}
	/**
	* Случаи госпитализации с ОКС за предыдущие три года
	*/
	function getListHospOKS() {

		$data = $this->ProcessInputData('getListHospOKS', true);

		if ($data === false) {
			return false;
		}
		$list = $this->dbmodel->getListHospOKS($data);

		return $this->ReturnData($list);
	}
	/**
	* Диспансерное наблюдение
	*/
	function getListDispViewData() {

		$data = $this->ProcessInputData('getListDispViewData', true);
		if ($data === false) {
			return false;
		}
		$list = $this->dbmodel->getListDispViewData($data);

		return $this->ReturnData($list);
	}
	/**
     * Для предмета наблюдения «Скрининг» исключить возможность добавления пациентов при следующих условиях:
	 * у пациентов в случаях лечения установлены диагнозы МКБ10 болезней системы кровообращения I00-I99;
	 * пациент уже состоит в одном из предмете наблюдений Регистра БСК.
     */
	function checkBSKforScreening() {

		$data = $this->ProcessInputData('checkBSKforScreening', true);
		if ($data === false) {
			return false;
		}
		$list = $this->dbmodel->checkBSKforScreening($data);
		
		return $this->ReturnData($list);
	}
	/**
	 *	Чтение данных для прогнозируемых осложнений основного заболевания
	*/
	function loadPrognosDiseases()
	{
		$data = $this->ProcessInputData('loadPrognosDiseases', true);
		if($data){
			$response = $this->dbmodel->loadPrognosDiseases($data);
			$this->ProcessModelList($response, true, true)->ReturnData();
		}
	}
	/**
	 * Сохранение наличие прогнозируемых осложнений основного заболевания
	 */
	function savePrognosDiseases()
	{
		$data = $this->ProcessInputData('savePrognosDiseases', true);
		
		if ($data === false) {
			return false;
		}
		
		$result = $this->dbmodel->savePrognosDiseases($data);
		
		$this->ReturnData($result);
	}
}
